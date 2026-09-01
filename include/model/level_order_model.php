<?php
/**
 * 会员等级开通订单模型
 * 表结构见 install.sql `dc_level_order`
 */

class Level_Order_Model {

    private $db;
    private $table;
    private static $tableChecked = false;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->table = DB_PREFIX . 'level_order';
        $this->ensureTable();
    }

    /**
     * 自动建表（老环境升级时自动创建）
     */
    private function ensureTable() {
        if (self::$tableChecked) return;
        self::$tableChecked = true;
        try {
            $tables = $this->db->listTables();
            if (!in_array($this->table, $tables)) {
                $this->db->query("CREATE TABLE `{$this->table}` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `order_id` int(10) unsigned NOT NULL DEFAULT '0',
                    `out_trade_no` varchar(32) NOT NULL DEFAULT '',
                    `user_id` int(10) unsigned NOT NULL DEFAULT '0',
                    `level_id` int(10) unsigned NOT NULL DEFAULT '0',
                    `purchase_type` varchar(16) NOT NULL DEFAULT 'open',
                    `duration_days` int(10) NOT NULL DEFAULT '0',
                    `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
                    `base_price` decimal(10,2) NOT NULL DEFAULT '0.00',
                    `old_level_id` int(10) NOT NULL DEFAULT '0',
                    `old_expire_time` bigint(16) DEFAULT '0',
                    `new_expire_time` bigint(16) DEFAULT '0',
                    `state` tinyint(1) NOT NULL DEFAULT '0',
                    `create_time` bigint(16) DEFAULT NULL,
                    `complete_time` bigint(16) DEFAULT NULL,
                    PRIMARY KEY (`id`) USING BTREE,
                    KEY `out_trade_no` (`out_trade_no`) USING BTREE,
                    KEY `user_id` (`user_id`) USING BTREE,
                    KEY `state` (`state`) USING BTREE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='会员等级开通订单'");
            } else {
                // 表已存在，自动补新增列
                try {
                    $cols = $this->db->fetch_all("SHOW COLUMNS FROM `{$this->table}` LIKE 'base_price'");
                    if (empty($cols)) {
                        $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `base_price` decimal(10,2) NOT NULL DEFAULT '0.00' AFTER `amount`");
                    }
                } catch (Exception $ignore) {}
            }
        } catch (Exception $e) {}
    }

    /**
     * 新建一条待支付的等级订单
     * @param array $data [order_id, out_trade_no, user_id, level_id, purchase_type, duration_days, amount, old_level_id, old_expire_time]
     * @return int 插入ID
     */
    public function createPending($data) {
        // 注意：MySQL 严格模式下 bigint 列不接受空字符串，所以待支付阶段
        // 不要给 complete_time 字段赋 null（$db->add 会转成 ''），直接省略由 DB 默认值处理
        $row = [
            'order_id' => intval($data['order_id'] ?? 0),
            'out_trade_no' => isset($data['out_trade_no']) ? (string)$data['out_trade_no'] : '',
            'user_id' => intval($data['user_id'] ?? 0),
            'level_id' => intval($data['level_id'] ?? 0),
            'purchase_type' => isset($data['purchase_type']) ? (string)$data['purchase_type'] : 'open',
            'duration_days' => intval($data['duration_days'] ?? 0),
            'amount' => floatval($data['amount'] ?? 0),
            'base_price' => floatval($data['base_price'] ?? 0),
            'old_level_id' => intval($data['old_level_id'] ?? 0),
            'old_expire_time' => intval($data['old_expire_time'] ?? 0),
            'new_expire_time' => 0,
            'state' => 0,
            'create_time' => time(),
            'complete_time' => 0,
        ];
        $this->db->add('level_order', $row);
        return $this->db->insert_id();
    }

    /**
     * 查询等级订单（根据 out_trade_no）
     */
    public function getByTradeNo($out_trade_no) {
        $safe = addslashes((string)$out_trade_no);
        return $this->db->once_fetch_array("SELECT * FROM {$this->table} WHERE out_trade_no='{$safe}' LIMIT 1");
    }

    public function applyFinish($out_trade_no, $timestamp = 0, $useTransaction = true) {
        $ts = $timestamp > 0 ? intval($timestamp) : time();
        $row = $this->getByTradeNo($out_trade_no);
        if (empty($row)) return false;
        if (intval($row['state']) === 1) {
            return [
                'already_done' => true,
                'uid' => intval($row['user_id']),
                'level_id' => intval($row['level_id']),
                'amount' => (float)$row['amount'],
                'base_price' => !empty($row['base_price']) ? (float)$row['base_price'] : (float)$row['amount'],
                'purchase_type' => (string)($row['purchase_type'] ?? 'open'),
                'duration_days' => intval($row['duration_days']),
                'new_expire_time' => intval($row['new_expire_time'] ?? 0),
                'out_trade_no' => (string)$out_trade_no,
            ];
        }

        $uid = intval($row['user_id']);
        $levelId = intval($row['level_id']);
        $duration = intval($row['duration_days']);

        try {
            if ($useTransaction) {
                $this->db->beginTransaction();
            }

            $base = $ts;
            if ($row['purchase_type'] === 'renew' || $row['purchase_type'] === 'upgrade') {
                // 续费：在旧到期时间上叠加新时长
                // 升级：用户已为剩余天数支付了 carry 费用，需保留剩余天数
                $oldExpire = intval($row['old_expire_time']);
                if ($oldExpire > $ts) {
                    $base = $oldExpire;
                }
            }
            $newExpire = $duration > 0 ? $base + $duration * 86400 : 0;

            $userUpdate = ['level' => $levelId];
            if ($duration > 0) {
                $userUpdate['level_expire_time'] = $newExpire;
            } else {
                $userUpdate['level_expire_time'] = 0;
            }
            $this->db->update('user', $userUpdate, ['uid' => $uid]);

            $this->db->update('level_order', [
                'state' => 1,
                'new_expire_time' => $newExpire,
                'complete_time' => $ts,
            ], ['id' => intval($row['id'])]);

            if ($useTransaction) {
                $this->db->commit();
            }

            return [
                'already_done' => false,
                'uid' => $uid,
                'level_id' => $levelId,
                'amount' => (float)$row['amount'],
                'base_price' => !empty($row['base_price']) ? (float)$row['base_price'] : (float)$row['amount'],
                'purchase_type' => (string)($row['purchase_type'] ?? 'open'),
                'duration_days' => $duration,
                'new_expire_time' => $newExpire,
                'out_trade_no' => (string)$out_trade_no,
            ];
        } catch (Throwable $e) {
            if ($useTransaction) {
                $this->db->rollback();
            }
            if (class_exists('Log')) {
                Log::error('等级开通入账失败：' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * 完成等级开通
     * - 更新用户 level / level_expire_time
     * - 标记订单已完成
     * - 写用户日志
     * @param string $out_trade_no
     * @param int    $timestamp  完成时间
     * @return bool
     */
    public function finish($out_trade_no, $timestamp = 0) {
        $result = $this->applyFinish($out_trade_no, $timestamp, true);
        if ($result === false) return false;
        if (!empty($result['already_done'])) return true;
        $this->writeFinishLog($result);
        return true;
    }

    public function writeFinishLog($result) {
        try {
            if (!class_exists('User_Log_Model') || empty($result)) {
                return;
            }
            $uid = intval($result['uid'] ?? 0);
            $levelId = intval($result['level_id'] ?? 0);
            if ($uid <= 0 || $levelId <= 0) {
                return;
            }
            $memberModel = new Member_Model();
            $lv = $memberModel->getById($levelId);
            $name = $lv['name'] ?? ('ID:' . $levelId);
            $duration = intval($result['duration_days'] ?? 0);
            $newExpire = intval($result['new_expire_time'] ?? 0);
            $expireStr = $duration > 0 ? '，到期时间：' . date('Y-m-d', $newExpire) : '（永久）';
            User_Log_Model::log(
                $uid,
                'level_upgrade',
                '开通/续费会员等级: ' . $name . '，订单号：' . ($result['out_trade_no'] ?? '') . '，金额：¥' . number_format((float)($result['amount'] ?? 0), 2) . $expireStr,
                (float)($result['amount'] ?? 0)
            );
        } catch (Throwable $e) {
            if (class_exists('Log')) {
                Log::error('会员等级日志写入失败：' . $e->getMessage());
            }
        }
    }

    /**
     * 用户的等级订单历史
     */
    public function getByUser($uid, $limit = 20) {
        $uid = intval($uid);
        $limit = max(1, intval($limit));
        return $this->db->fetch_all("SELECT * FROM {$this->table} WHERE user_id={$uid} ORDER BY id DESC LIMIT {$limit}");
    }
}
