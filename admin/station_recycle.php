<?php
/**
 * 分店回收站
 * 列出已软删的分店，支持恢复与彻底删除
 */

ob_start();
require_once 'globals.php';

$db = Database::getInstance();
$db_prefix = DB_PREFIX;

// ================= 页面渲染 =================
if (empty($action)) {
    $levels = $db->fetch_all("SELECT id, name FROM {$db_prefix}station_level ORDER BY sort ASC, id ASC");
    if (!$levels) $levels = [];

    $popup = !empty($_GET['popup']);
    if ($popup) {
        include View::getAdmView('open_head');
        require_once View::getAdmView('station/recycle');
        include View::getAdmView('open_foot');
    } else {
        $br = '<a href="./">数据中心</a><a href="./station.php">分店管理</a><a><cite>分店回收站</cite></a>';
        include View::getAdmView(User::haveEditPermission() ? 'header' : 'uc_header');
        require_once View::getAdmView('station/recycle');
        include View::getAdmView(User::haveEditPermission() ? 'footer' : 'uc_footer');
    }
    View::output();
}

// ================= 列表数据 =================
if ($action == 'list') {
    $keyword  = isset($_GET['keyword']) ? addslashes(trim($_GET['keyword'])) : '';
    $level_id = isset($_GET['level_id']) ? (int)$_GET['level_id'] : 0;
    $page     = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit    = Input::getIntVar('limit', 10);
    $start    = ($page - 1) * $limit;

    $where = "s.delete_time IS NOT NULL";
    if (!empty($keyword)) {
        $kw = addslashes($keyword);
        $where .= " AND (s.name LIKE '%{$kw}%' OR s.domain LIKE '%{$kw}%' OR s.domain_2 LIKE '%{$kw}%' OR u.username LIKE '%{$kw}%' OR s.id = " . (int)$kw . ")";
    }
    if ($level_id > 0) {
        $where .= " AND s.level_id = {$level_id}";
    }

    $sql = "SELECT s.*, u.username, u.nickname, sl.name AS level_name
            FROM {$db_prefix}station s
            LEFT JOIN {$db_prefix}user u ON s.user_id = u.uid
            LEFT JOIN {$db_prefix}station_level sl ON s.level_id = sl.id
            WHERE {$where}
            ORDER BY s.delete_time DESC
            LIMIT {$start}, {$limit}";

    $res  = $db->query($sql);
    $list = [];
    while ($row = $db->fetch_array($res)) {
        $row['create_time_text'] = date('Y-m-d H:i', $row['create_time']);
        $row['delete_time_text'] = date('Y-m-d H:i', $row['delete_time']);
        $row['username']         = htmlspecialchars($row['username'] ?? '');
        $row['nickname']         = htmlspecialchars($row['nickname'] ?? '');
        $row['name']             = htmlspecialchars($row['name'] ?? '');
        $row['level_name']       = htmlspecialchars($row['level_name'] ?? '未知等级');
        $row['domain']           = htmlspecialchars($row['domain'] ?? '');
        $row['domain_2']         = htmlspecialchars($row['domain_2'] ?? '');
        $list[] = $row;
    }

    $total = $db->once_fetch_array("SELECT COUNT(*) AS cnt FROM {$db_prefix}station s LEFT JOIN {$db_prefix}user u ON s.user_id=u.uid WHERE {$where}");
    output::data($list, (int)($total['cnt'] ?? 0));
}

// ================= 恢复分店 =================
if ($action == 'restore') {
    if (ob_get_level()) ob_clean();
    header('Content-Type: application/json; charset=UTF-8');

    try {
        if (!User::haveEditPermission()) {
            die(json_encode(['code' => 1, 'msg' => '权限不足'], JSON_UNESCAPED_UNICODE));
        }

        $ids = Input::postStrVar('ids');
        if (empty($ids)) {
            die(json_encode(['code' => 1, 'msg' => '请选择要恢复的分店'], JSON_UNESCAPED_UNICODE));
        }

        $ids = explode(',', $ids);
        $count = 0;
        foreach ($ids as $id) {
            $id = (int)$id;
            if ($id <= 0) continue;
            // 检查用户是否已有活跃分店
            $station = $db->once_fetch_array("SELECT user_id FROM {$db_prefix}station WHERE id = {$id} AND delete_time IS NOT NULL");
            if (empty($station)) continue;
            $exist = $db->once_fetch_array("SELECT id FROM {$db_prefix}station WHERE user_id = {$station['user_id']} AND delete_time IS NULL AND id != {$id}");
            if ($exist) {
                die(json_encode(['code' => 1, 'msg' => "用户(UID:{$station['user_id']})已拥有活跃分店(ID:{$exist['id']})，无法恢复"], JSON_UNESCAPED_UNICODE));
            }
            $db->query("UPDATE {$db_prefix}station SET delete_time = NULL WHERE id = {$id} AND delete_time IS NOT NULL");
            $count++;
        }

        if ($count > 0) {
            die(json_encode(['code' => 0, 'msg' => "成功恢复 {$count} 个分店"], JSON_UNESCAPED_UNICODE));
        } else {
            die(json_encode(['code' => 1, 'msg' => '没有找到可恢复的分店'], JSON_UNESCAPED_UNICODE));
        }
    } catch (Exception $e) {
        die(json_encode(['code' => 1, 'msg' => '恢复失败：' . $e->getMessage()], JSON_UNESCAPED_UNICODE));
    }
}

// ================= 彻底删除 =================
if ($action == 'permanent_delete') {
    if (ob_get_level()) ob_clean();
    header('Content-Type: application/json; charset=UTF-8');

    try {
        if (!User::haveEditPermission()) {
            die(json_encode(['code' => 1, 'msg' => '权限不足'], JSON_UNESCAPED_UNICODE));
        }

        $ids = Input::postStrVar('ids');
        if (empty($ids)) {
            die(json_encode(['code' => 1, 'msg' => '请选择要删除的分店'], JSON_UNESCAPED_UNICODE));
        }

        $ids   = explode(',', $ids);
        $count = 0;
        foreach ($ids as $id) {
            $id = (int)$id;
            if ($id <= 0) continue;
            $station = $db->once_fetch_array("SELECT id FROM {$db_prefix}station WHERE id = {$id} AND delete_time IS NOT NULL");
            if (empty($station)) continue;
            $db->query("DELETE FROM {$db_prefix}station WHERE id = {$id} AND delete_time IS NOT NULL");
            $count++;
        }

        if ($count > 0) {
            die(json_encode(['code' => 0, 'msg' => "成功删除 {$count} 个分店"], JSON_UNESCAPED_UNICODE));
        } else {
            die(json_encode(['code' => 1, 'msg' => '没有找到可删除的分店'], JSON_UNESCAPED_UNICODE));
        }
    } catch (Exception $e) {
        die(json_encode(['code' => 1, 'msg' => '删除失败：' . $e->getMessage()], JSON_UNESCAPED_UNICODE));
    }
}

// ================= 清空回收站 =================
if ($action == 'empty_all') {
    if (ob_get_level()) ob_clean();
    header('Content-Type: application/json; charset=UTF-8');

    try {
        if (!User::haveEditPermission()) {
            die(json_encode(['code' => 1, 'msg' => '权限不足'], JSON_UNESCAPED_UNICODE));
        }

        $total = $db->once_fetch_array("SELECT COUNT(*) AS cnt FROM {$db_prefix}station WHERE delete_time IS NOT NULL");
        $cnt   = (int)($total['cnt'] ?? 0);

        $db->query("DELETE FROM {$db_prefix}station WHERE delete_time IS NOT NULL");

        die(json_encode(['code' => 0, 'msg' => "已清空 {$cnt} 个分店"], JSON_UNESCAPED_UNICODE));
    } catch (Exception $e) {
        die(json_encode(['code' => 1, 'msg' => '清空失败：' . $e->getMessage()], JSON_UNESCAPED_UNICODE));
    }
}
