<?php
/**
 * 发货记录回收站
 * 列出已软删的发货记录，支持恢复与彻底删除
 */

ob_start();
require_once 'globals.php';

$db = Database::getInstance();
$db_prefix = DB_PREFIX;

// ================= 页面渲染 =================
if (empty($action)) {
    $popup = !empty($_GET['popup']);
    if ($popup) {
        include View::getAdmView('open_head');
        require_once View::getAdmView('deliver_recycle');
        include View::getAdmView('open_foot');
    } else {
        $br = '<a href="./">数据中心</a><a href="./stock.php?action=sales">已售出</a><a><cite>发货记录回收站</cite></a>';
        include View::getAdmView(User::haveEditPermission() ? 'header' : 'uc_header');
        require_once View::getAdmView('deliver_recycle');
        include View::getAdmView(User::haveEditPermission() ? 'footer' : 'uc_footer');
    }
    View::output();
}

// ================= 列表数据 =================
if ($action == 'list') {
    $keyword = isset($_GET['keyword']) ? addslashes(trim($_GET['keyword'])) : '';
    $page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit   = Input::getIntVar('limit', 10);
    $start   = ($page - 1) * $limit;

    $where = "d.delete_time IS NOT NULL";
    if (!empty($keyword)) {
        $kw = addslashes($keyword);
        $where .= " AND (d.content LIKE '%{$kw}%' OR g.title LIKE '%{$kw}%' OR d.id = " . (int)$kw . ")";
    }

    $sql = "SELECT d.id, d.content, d.create_time, d.delete_time, d.order_list_id,
                   g.title AS goods_title, g.type AS goods_type, ol.sku
            FROM {$db_prefix}deliver d
            LEFT JOIN {$db_prefix}order_list ol ON d.order_list_id = ol.id
            LEFT JOIN {$db_prefix}goods g ON ol.goods_id = g.id
            WHERE {$where}
            ORDER BY d.delete_time DESC
            LIMIT {$start}, {$limit}";

    $res  = $db->query($sql);
    $list = [];
    while ($row = $db->fetch_array($res)) {
        $row['create_time_text'] = $row['create_time'] ? date('Y-m-d H:i', $row['create_time']) : '';
        $row['delete_time_text'] = date('Y-m-d H:i', $row['delete_time']);
        $row['goods_title']      = htmlspecialchars($row['goods_title'] ?? '未知商品');
        $row['content_short']    = htmlspecialchars(mb_substr($row['content'] ?? '', 0, 60, 'UTF-8'));
        $list[] = $row;
    }

    $total = $db->once_fetch_array("SELECT COUNT(*) AS cnt FROM {$db_prefix}deliver d LEFT JOIN {$db_prefix}order_list ol ON d.order_list_id=ol.id LEFT JOIN {$db_prefix}goods g ON ol.goods_id=g.id WHERE {$where}");
    output::data($list, (int)($total['cnt'] ?? 0));
}

// ================= 恢复 =================
if ($action == 'restore') {
    if (ob_get_level()) ob_clean();
    header('Content-Type: application/json; charset=UTF-8');

    try {
        if (!User::haveEditPermission()) {
            die(json_encode(['code' => 1, 'msg' => '权限不足'], JSON_UNESCAPED_UNICODE));
        }
        $ids = Input::postStrVar('ids');
        if (empty($ids)) {
            die(json_encode(['code' => 1, 'msg' => '请选择要恢复的记录'], JSON_UNESCAPED_UNICODE));
        }

        $ids   = array_map('intval', explode(',', $ids));
        $count = 0;
        foreach ($ids as $id) {
            if ($id <= 0) continue;
            $db->query("UPDATE {$db_prefix}deliver SET delete_time = NULL WHERE id = {$id} AND delete_time IS NOT NULL");
            if ($db->affected_rows()) $count++;
        }

        die(json_encode(['code' => $count > 0 ? 0 : 1, 'msg' => $count > 0 ? "成功恢复 {$count} 条发货记录" : '没有找到可恢复的记录'], JSON_UNESCAPED_UNICODE));
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
            die(json_encode(['code' => 1, 'msg' => '请选择要删除的记录'], JSON_UNESCAPED_UNICODE));
        }

        $ids   = array_map('intval', explode(',', $ids));
        $count = 0;
        foreach ($ids as $id) {
            if ($id <= 0) continue;
            $db->query("DELETE FROM {$db_prefix}deliver WHERE id = {$id} AND delete_time IS NOT NULL");
            if ($db->affected_rows()) $count++;
        }

        die(json_encode(['code' => $count > 0 ? 0 : 1, 'msg' => $count > 0 ? "成功删除 {$count} 条记录" : '没有找到可删除的记录'], JSON_UNESCAPED_UNICODE));
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
        $total = $db->once_fetch_array("SELECT COUNT(*) AS cnt FROM {$db_prefix}deliver WHERE delete_time IS NOT NULL");
        $cnt   = (int)($total['cnt'] ?? 0);
        $db->query("DELETE FROM {$db_prefix}deliver WHERE delete_time IS NOT NULL");
        die(json_encode(['code' => 0, 'msg' => "已清空 {$cnt} 条发货记录"], JSON_UNESCAPED_UNICODE));
    } catch (Exception $e) {
        die(json_encode(['code' => 1, 'msg' => '清空失败：' . $e->getMessage()], JSON_UNESCAPED_UNICODE));
    }
}
