<?php
/**
 * 规格模板回收站
 * 列出已软删的规格模板/属性/属性值，支持恢复与彻底删除
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
        require_once View::getAdmView('sku_recycle');
        include View::getAdmView('open_foot');
    } else {
        $br = '<a href="./">数据中心</a><a href="./sku.php">规格模板</a><a><cite>规格回收站</cite></a>';
        include View::getAdmView(User::haveEditPermission() ? 'header' : 'uc_header');
        require_once View::getAdmView('sku_recycle');
        include View::getAdmView(User::haveEditPermission() ? 'footer' : 'uc_footer');
    }
    View::output();
}

// ================= 列表数据（只展示规格模板） =================
if ($action == 'list') {
    $keyword = isset($_GET['keyword']) ? addslashes(trim($_GET['keyword'])) : '';
    $page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit   = Input::getIntVar('limit', 10);
    $start   = ($page - 1) * $limit;

    $where = "delete_time IS NOT NULL";
    if (!empty($keyword)) {
        $where .= " AND name LIKE '%{$keyword}%'";
    }

    $sql = "SELECT id, name, delete_time FROM {$db_prefix}goods_type WHERE {$where} ORDER BY delete_time DESC LIMIT {$start}, {$limit}";
    $res = $db->query($sql);
    $list = [];
    while ($row = $db->fetch_array($res)) {
        $row['delete_time_text'] = date('Y-m-d H:i', $row['delete_time']);
        $row['name'] = htmlspecialchars($row['name'] ?? '');
        // 统计子属性/属性值数量
        $attrCnt = $db->once_fetch_array("SELECT COUNT(*) AS c FROM {$db_prefix}sku_attr WHERE type_id = {$row['id']}");
        $row['attr_count'] = (int)($attrCnt['c'] ?? 0);
        $list[] = $row;
    }
    $cnt = $db->once_fetch_array("SELECT COUNT(*) AS c FROM {$db_prefix}goods_type WHERE {$where}");
    $total = (int)($cnt['c'] ?? 0);

    output::data($list, $total);
}

// ================= 恢复（级联恢复子属性和属性值） =================
if ($action == 'restore') {
    if (ob_get_level()) ob_clean();
    header('Content-Type: application/json; charset=UTF-8');

    try {
        if (!User::haveEditPermission()) {
            die(json_encode(['code' => 1, 'msg' => '权限不足'], JSON_UNESCAPED_UNICODE));
        }
        $ids = Input::postStrVar('ids');
        if (empty($ids)) {
            die(json_encode(['code' => 1, 'msg' => '请选择要恢复的规格模板'], JSON_UNESCAPED_UNICODE));
        }

        $ids = array_map('intval', explode(',', $ids));
        $count = 0;
        foreach ($ids as $id) {
            if ($id <= 0) continue;
            $db->query("UPDATE {$db_prefix}goods_type SET delete_time = NULL WHERE id = {$id} AND delete_time IS NOT NULL");
            if ($db->affected_rows()) {
                $count++;
                // 级联恢复子属性和属性值
                $attrs = $db->fetch_all("SELECT id FROM {$db_prefix}sku_attr WHERE type_id = {$id}");
                if ($attrs) {
                    $attrIds = implode(',', array_column($attrs, 'id'));
                    $db->query("UPDATE {$db_prefix}sku_attr SET delete_time = NULL WHERE type_id = {$id}");
                    $db->query("UPDATE {$db_prefix}sku_value SET delete_time = NULL WHERE attr_id IN ({$attrIds})");
                }
            }
        }

        die(json_encode(['code' => $count > 0 ? 0 : 1, 'msg' => $count > 0 ? "成功恢复 {$count} 个规格模板（含子属性）" : '没有找到可恢复的项目'], JSON_UNESCAPED_UNICODE));
    } catch (Exception $e) {
        die(json_encode(['code' => 1, 'msg' => '恢复失败：' . $e->getMessage()], JSON_UNESCAPED_UNICODE));
    }
}

// ================= 彻底删除（级联删除子属性和属性值） =================
if ($action == 'permanent_delete') {
    if (ob_get_level()) ob_clean();
    header('Content-Type: application/json; charset=UTF-8');

    try {
        if (!User::haveEditPermission()) {
            die(json_encode(['code' => 1, 'msg' => '权限不足'], JSON_UNESCAPED_UNICODE));
        }
        $ids = Input::postStrVar('ids');
        if (empty($ids)) {
            die(json_encode(['code' => 1, 'msg' => '请选择要删除的规格模板'], JSON_UNESCAPED_UNICODE));
        }

        $ids = array_map('intval', explode(',', $ids));
        $count = 0;
        foreach ($ids as $id) {
            if ($id <= 0) continue;
            // 级联删除子属性和属性值
            $attrs = $db->fetch_all("SELECT id FROM {$db_prefix}sku_attr WHERE type_id = {$id}");
            if ($attrs) {
                $attrIds = implode(',', array_column($attrs, 'id'));
                $db->query("DELETE FROM {$db_prefix}sku_value WHERE attr_id IN ({$attrIds})");
                $db->query("DELETE FROM {$db_prefix}sku_attr WHERE type_id = {$id}");
            }
            $db->query("DELETE FROM {$db_prefix}goods_type WHERE id = {$id} AND delete_time IS NOT NULL");
            if ($db->affected_rows()) $count++;
        }

        die(json_encode(['code' => $count > 0 ? 0 : 1, 'msg' => $count > 0 ? "成功删除 {$count} 个规格模板（含子属性）" : '没有找到可删除的项目'], JSON_UNESCAPED_UNICODE));
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

        // 统计已删除的规格模板数
        $c1 = $db->once_fetch_array("SELECT COUNT(*) AS c FROM {$db_prefix}goods_type WHERE delete_time IS NOT NULL");
        $total = (int)($c1['c'] ?? 0);

        // 级联删除：对每个已删模板清理子属性值
        $deletedTypes = $db->fetch_all("SELECT id FROM {$db_prefix}goods_type WHERE delete_time IS NOT NULL");
        if ($deletedTypes) {
            $typeIds = implode(',', array_column($deletedTypes, 'id'));
            $attrRows = $db->fetch_all("SELECT id FROM {$db_prefix}sku_attr WHERE type_id IN ({$typeIds})");
            if ($attrRows) {
                $attrIds = implode(',', array_column($attrRows, 'id'));
                $db->query("DELETE FROM {$db_prefix}sku_value WHERE attr_id IN ({$attrIds})");
            }
            $db->query("DELETE FROM {$db_prefix}sku_attr WHERE type_id IN ({$typeIds})");
        }
        $db->query("DELETE FROM {$db_prefix}goods_type WHERE delete_time IS NOT NULL");

        die(json_encode(['code' => 0, 'msg' => "已清空 {$total} 个规格模板"], JSON_UNESCAPED_UNICODE));
    } catch (Exception $e) {
        die(json_encode(['code' => 1, 'msg' => '清空失败：' . $e->getMessage()], JSON_UNESCAPED_UNICODE));
    }
}
