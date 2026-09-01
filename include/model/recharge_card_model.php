<?php
class Recharge_Card_Model {

    private $db;
    private $table;
    private $db_prefix;
    private static $tableChecked = false;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->db_prefix = DB_PREFIX;
        $this->table = DB_PREFIX . 'recharge_card';
        $this->ensureTable();
    }

    private function ensureTable() {
        if (self::$tableChecked) {
            return;
        }
        self::$tableChecked = true;
        $tables = $this->db->listTables();
        if (!in_array($this->table, $tables)) {
            $sql = "CREATE TABLE `{$this->table}` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
                `card_key` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                `status` tinyint(1) NOT NULL DEFAULT '0',
                `user_id` int(10) unsigned NOT NULL DEFAULT '0',
                `batch_no` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                `admin_uid` int(10) unsigned NOT NULL DEFAULT '0',
                `use_time` bigint(16) NOT NULL DEFAULT '0',
                `create_time` bigint(16) NOT NULL DEFAULT '0',
                `update_time` bigint(16) NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`) USING BTREE,
                UNIQUE KEY `uniq_card_key` (`card_key`) USING BTREE,
                KEY `idx_status` (`status`) USING BTREE,
                KEY `idx_batch_no` (`batch_no`) USING BTREE,
                KEY `idx_admin_uid` (`admin_uid`) USING BTREE,
                KEY `idx_user_id` (`user_id`) USING BTREE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $this->db->query($sql);
        }
    }

    private function escape($value) {
        return addslashes(trim((string)$value));
    }

    private function buildWhere($filters = []) {
        $where = '';
        $keyword = $this->escape($filters['keyword'] ?? '');
        if ($keyword !== '') {
            $where .= " AND (rc.card_key LIKE CONCAT('%', '{$keyword}', '%') OR rc.title LIKE CONCAT('%', '{$keyword}', '%') OR rc.type LIKE CONCAT('%', '{$keyword}', '%') OR rc.batch_no LIKE CONCAT('%', '{$keyword}', '%') OR u.uid = '{$keyword}' OR u.username LIKE CONCAT('%', '{$keyword}', '%') OR u.nickname LIKE CONCAT('%', '{$keyword}', '%') OR u.email LIKE CONCAT('%', '{$keyword}', '%') OR u.tel LIKE CONCAT('%', '{$keyword}', '%'))";
        }
        $type = $this->escape($filters['type'] ?? '');
        if ($type !== '') {
            $where .= " AND rc.type = '{$type}'";
        }
        $batchNo = $this->escape($filters['batch_no'] ?? '');
        if ($batchNo !== '') {
            $where .= " AND rc.batch_no LIKE CONCAT('%', '{$batchNo}', '%')";
        }
        if (isset($filters['status']) && $filters['status'] !== '' && in_array((string)$filters['status'], ['0', '1'], true)) {
            $status = intval($filters['status']);
            $where .= " AND rc.status = {$status}";
        }
        return $where;
    }

    private function formatRow($row) {
        $row['amount_text'] = number_format((float)$row['amount'], 2);
        $row['create_time_text'] = empty($row['create_time']) ? '' : date('Y-m-d H:i:s', (int)$row['create_time']);
        $row['use_time_text'] = empty($row['use_time']) ? '' : date('Y-m-d H:i:s', (int)$row['use_time']);
        $row['user_display'] = '--';
        if (!empty($row['user_id'])) {
            $userNickname = $row['user_nickname'] ?: '未设置';
            $subText = $row['user_email'] ?: ($row['user_tel'] ?: ($row['user_username'] ?: 'UID:' . $row['user_id']));
            $row['user_display'] = $userNickname . ' / ' . $subText;
        }
        if ((int)$row['status'] === 1) {
            $row['status_text'] = '已使用';
        } else {
            $row['status_text'] = '未使用';
        }
        return $row;
    }

    public function getList($page = 1, $limit = 15, $filters = []) {
        $page = max(1, intval($page));
        $limit = max(1, intval($limit));
        $start = ($page - 1) * $limit;
        $where = $this->buildWhere($filters);
        $countSql = "SELECT COUNT(*) AS total FROM {$this->table} rc LEFT JOIN {$this->db_prefix}user u ON rc.user_id = u.uid WHERE 1=1 {$where}";
        $countRow = $this->db->once_fetch_array($countSql);
        $total = intval($countRow['total'] ?? 0);
        $sql = "SELECT rc.*, u.username AS user_username, u.nickname AS user_nickname, u.email AS user_email, u.tel AS user_tel
                FROM {$this->table} rc
                LEFT JOIN {$this->db_prefix}user u ON rc.user_id = u.uid
                WHERE 1=1 {$where}
                ORDER BY rc.id DESC
                LIMIT {$start}, {$limit}";
        $res = $this->db->query($sql);
        $list = [];
        while ($row = $this->db->fetch_array($res)) {
            $list[] = $this->formatRow($row);
        }
        return ['list' => $list, 'total' => $total];
    }

    public function getStats() {
        $sql = "SELECT
                    COUNT(*) AS total_count,
                    SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) AS unused_count,
                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS used_count,
                    COALESCE(SUM(amount), 0) AS amount_total
                FROM {$this->table}";
        $row = $this->db->once_fetch_array($sql);
        return [
            'total_count' => intval($row['total_count'] ?? 0),
            'unused_count' => intval($row['unused_count'] ?? 0),
            'used_count' => intval($row['used_count'] ?? 0),
            'amount_total' => number_format((float)($row['amount_total'] ?? 0), 2)
        ];
    }

    private function makeCardKey() {
        do {
            $cardKey = 'RC' . date('ymd') . strtoupper(getRandStr(10, false));
            $exists = $this->db->once_fetch_array("SELECT COUNT(*) AS total FROM {$this->table} WHERE card_key='" . addslashes($cardKey) . "'");
        } while (!empty($exists['total']));
        return $cardKey;
    }

    public function generate($type, $title, $amount, $num, $adminUid) {
        $type = $type === '' ? '通用卡' : $this->escape($type);
        $title = $title === '' ? number_format((float)$amount, 2) . '元充值卡' : $this->escape($title);
        $amount = number_format((float)$amount, 2, '.', '');
        $num = intval($num);
        $adminUid = intval($adminUid);
        $batchNo = 'B' . date('mdHi') . strtoupper(substr(md5($adminUid . '-' . microtime(true) . '-' . getRandStr(4, false)), 0, 4));
        $timestamp = time();
        $cards = [];
        for ($i = 0; $i < $num; $i++) {
            $cardKey = $this->makeCardKey();
            $insert = [
                'type' => $type,
                'title' => $title,
                'amount' => $amount,
                'card_key' => $cardKey,
                'status' => 0,
                'user_id' => 0,
                'batch_no' => $batchNo,
                'admin_uid' => $adminUid,
                'use_time' => 0,
                'create_time' => $timestamp,
                'update_time' => $timestamp
            ];
            $this->db->add('recharge_card', $insert);
            $cards[] = $cardKey;
        }
        return [
            'batch_no' => $batchNo,
            'count' => count($cards),
            'cards' => $cards,
            'copy_text' => implode("\r\n", $cards),
            'create_time_text' => date('Y-m-d H:i:s', $timestamp)
        ];
    }

    public function getLastBatch($adminUid) {
        $adminUid = intval($adminUid);
        $row = $this->db->once_fetch_array("SELECT batch_no, COUNT(*) AS total, MAX(create_time) AS create_time FROM {$this->table} WHERE admin_uid={$adminUid} GROUP BY batch_no ORDER BY MAX(id) DESC LIMIT 1");
        if (empty($row['batch_no'])) {
            return [
                'batch_no' => '',
                'count' => 0,
                'create_time_text' => '',
                'cards' => [],
                'copy_text' => ''
            ];
        }
        $batchNo = addslashes($row['batch_no']);
        $res = $this->db->query("SELECT card_key FROM {$this->table} WHERE admin_uid={$adminUid} AND batch_no='{$batchNo}' ORDER BY id ASC");
        $cards = [];
        while ($item = $this->db->fetch_array($res)) {
            $cards[] = $item['card_key'];
        }
        return [
            'batch_no' => $row['batch_no'],
            'count' => intval($row['total'] ?? count($cards)),
            'create_time_text' => empty($row['create_time']) ? '' : date('Y-m-d H:i:s', (int)$row['create_time']),
            'cards' => $cards,
            'copy_text' => implode("\r\n", $cards)
        ];
    }

    public function redeem($cardKey, $userId) {
        $cardKey = $this->escape($cardKey);
        $userId = intval($userId);
        if ($cardKey === '' || $userId <= 0) {
            return ['code' => 1, 'msg' => '参数错误'];
        }
        $row = $this->db->once_fetch_array("SELECT * FROM {$this->table} WHERE card_key='{$cardKey}' LIMIT 1");
        if (empty($row)) {
            return ['code' => 1, 'msg' => '卡密不存在，请检查输入'];
        }
        if ((int)$row['status'] === 1) {
            return ['code' => 1, 'msg' => '该卡密已被使用'];
        }
        $amount = (float)$row['amount'];
        if ($amount <= 0) {
            return ['code' => 1, 'msg' => '卡密面额异常'];
        }
        $timestamp = time();
        $this->db->query("UPDATE {$this->table} SET status=1, user_id={$userId}, use_time={$timestamp}, update_time={$timestamp} WHERE id={$row['id']} AND status=0 LIMIT 1");
        $affected = $this->db->affected_rows();
        if ($affected <= 0) {
            return ['code' => 1, 'msg' => '兑换失败，卡密可能已被使用'];
        }
        $userRow = $this->db->once_fetch_array("SELECT money FROM " . DB_PREFIX . "user WHERE uid={$userId} LIMIT 1");
        $balanceBefore = isset($userRow['money']) ? (float)$userRow['money'] : 0;
        $balanceModel = new Balance_Model();
        $balanceModel->inc($userId, $amount, '卡密充值（' . $row['title'] . '）');
        $userAfterRow = $this->db->once_fetch_array("SELECT money FROM " . DB_PREFIX . "user WHERE uid={$userId} LIMIT 1");
        $balanceAfter = isset($userAfterRow['money']) ? (float)$userAfterRow['money'] : ($balanceBefore + $amount);
        User_Log_Model::log($userId, User_Log_Model::TYPE_CARD_REDEEM, '使用充值卡 ' . $row['card_key'] . '，名称：' . $row['title'] . '，充值金额：¥' . number_format($amount, 2) . '，充值前余额：¥' . number_format($balanceBefore, 2) . '，充值后余额：¥' . number_format($balanceAfter, 2), $amount);
        return [
            'code' => 0,
            'msg' => '充值成功',
            'data' => [
                'amount' => number_format($amount, 2),
                'title' => $row['title'],
                'card_key' => $row['card_key']
            ]
        ];
    }

    public function deleteByIds($ids = []) {
        $safeIds = [];
        foreach ((array)$ids as $id) {
            $id = intval($id);
            if ($id > 0) {
                $safeIds[] = $id;
            }
        }
        if (empty($safeIds)) {
            return 0;
        }
        $idsStr = implode(',', $safeIds);
        $countRow = $this->db->once_fetch_array("SELECT COUNT(*) AS total FROM {$this->table} WHERE id IN ({$idsStr}) AND status != 1");
        $actualCount = intval($countRow['total'] ?? 0);
        if ($actualCount <= 0) {
            return 0;
        }
        $this->db->query("DELETE FROM {$this->table} WHERE id IN ({$idsStr}) AND status != 1");
        return $actualCount;
    }

    public function getRowsForExport($mode, $adminUid, $filters = [], $ids = []) {
        $adminUid = intval($adminUid);
        $where = '';
        if ($mode === 'last') {
            $lastBatch = $this->getLastBatch($adminUid);
            if (empty($lastBatch['batch_no'])) {
                return [];
            }
            $batchNo = addslashes($lastBatch['batch_no']);
            $where = " AND rc.admin_uid = {$adminUid} AND rc.batch_no = '{$batchNo}'";
        } elseif ($mode === 'selected') {
            $safeIds = [];
            foreach ((array)$ids as $id) {
                $id = intval($id);
                if ($id > 0) {
                    $safeIds[] = $id;
                }
            }
            if (empty($safeIds)) {
                return [];
            }
            $where = ' AND rc.id IN (' . implode(',', $safeIds) . ')';
        } else {
            $where = $this->buildWhere($filters);
        }
        $sql = "SELECT rc.*, u.username AS user_username, u.nickname AS user_nickname, u.email AS user_email, u.tel AS user_tel
                FROM {$this->table} rc
                LEFT JOIN {$this->db_prefix}user u ON rc.user_id = u.uid
                WHERE 1=1 {$where}
                ORDER BY rc.id DESC";
        $res = $this->db->query($sql);
        $list = [];
        while ($row = $this->db->fetch_array($res)) {
            $list[] = $this->formatRow($row);
        }
        return $list;
    }
}
