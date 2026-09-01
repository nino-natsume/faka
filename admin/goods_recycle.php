<?php
/**
 * 商品回收站
 * 独立二级菜单页面，列出已软删的商品，支持恢复与彻底删除
 */

ob_start();

require_once 'globals.php';

$goodsModel = new Goods_Model();

// ================= 页面渲染 =================
if (empty($action)) {

    $sorts = $CACHE->readCache('sort');
    $sorts[] = [
        'sortname' => '未分类',
        'sid'      => -1,
    ];

    $popup = !empty($_GET['popup']);

    if ($popup) {
        include View::getAdmView('open_head');
        require_once View::getAdmView('goods_recycle');
        include View::getAdmView('open_foot');
    } else {
        $br = '<a href="./">数据中心</a><a href="./goods.php">商品管理</a><a><cite>商品回收站</cite></a>';
        include View::getAdmView(User::haveEditPermission() ? 'header' : 'uc_header');
        require_once View::getAdmView('goods_recycle');
        include View::getAdmView(User::haveEditPermission() ? 'footer' : 'uc_footer');
    }
    View::output();
}

// ================= 列表数据 =================
if ($action == 'list') {
    $keyword     = isset($_GET['keyword']) ? addslashes(trim($_GET['keyword'])) : '';
    $category_id = isset($_GET['category_id']) ? addslashes(trim($_GET['category_id'])) : 0;
    $page        = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perpage_num = Input::getIntVar('limit', 10);
    $start_limit = !empty($page) ? ($page - 1) * $perpage_num : 0;
    $limit       = "LIMIT $start_limit, " . $perpage_num;

    $db        = Database::getInstance();
    $db_prefix = DB_PREFIX;

    $where = "";
    if (!empty($keyword)) {
        $where .= " and title like '%{$keyword}%' ";
    }
    if ($category_id != 0) {
        $where .= " and sort_id={$category_id} ";
    }

    $order_by = "ORDER BY delete_time DESC";

    $sql = "SELECT id, cover, create_time, delete_time, index_top, is_on_shelf, is_sku, sales, sort_id, sort_top, sort_num, stock, title, type FROM {$db_prefix}goods where delete_time is not null {$where} {$order_by} $limit;";

    $sorts = $CACHE->readCache('sort');

    $res   = $db->query($sql);
    $goods = [];
    while ($row = $db->fetch_array($res)) {
        $row['timestamp']             = $row['create_time'];
        $row['create_time']           = date("Y-m-d H:i", $row['create_time']);
        $row['delete_time_formatted'] = date("Y-m-d H:i", $row['delete_time']);
        $row['title']                 = !empty($row['title']) ? htmlspecialchars($row['title']) : '无标题';
        $row['type_text']             = goodsTypeText($row['type']);
        $row['stock']                 = number_format($row['stock']);

        $sortName         = isset($sorts[$row['sort_id']]['sortname']) ? $sorts[$row['sort_id']]['sortname'] : '未知分类';
        $row['sort_name'] = $row['sort_id'] == -1 ? '未分类' : $sortName;

        $goods[] = $row;
    }

    $res = $db->once_fetch_array("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "goods g where g.delete_time is not null {$where}");

    output::data($goods, $res['total']);
}

// ================= 恢复商品 =================
if ($action == 'restore') {
    if (ob_get_level()) ob_clean();
    header('Content-Type: application/json; charset=UTF-8');

    try {
        if (!User::haveEditPermission()) {
            die(json_encode(['code' => 1, 'msg' => '权限不足'], JSON_UNESCAPED_UNICODE));
        }

        $ids = Input::postStrVar('ids');
        if (empty($ids)) {
            die(json_encode(['code' => 1, 'msg' => '请选择要恢复的商品'], JSON_UNESCAPED_UNICODE));
        }

        $ids       = explode(',', $ids);
        $db        = Database::getInstance();
        $db_prefix = DB_PREFIX;

        $restoredCount = 0;
        foreach ($ids as $goods_id) {
            $goods_id = intval($goods_id);
            if ($goods_id <= 0) continue;

            $result = $db->query("UPDATE {$db_prefix}goods SET delete_time = NULL WHERE id = {$goods_id} AND delete_time IS NOT NULL");
            if ($result) {
                $restoredCount++;
            }
        }

        if ($restoredCount > 0) {
            die(json_encode(['code' => 0, 'msg' => "成功恢复 {$restoredCount} 个商品"], JSON_UNESCAPED_UNICODE));
        } else {
            die(json_encode(['code' => 1, 'msg' => '没有找到可恢复的商品'], JSON_UNESCAPED_UNICODE));
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
            die(json_encode(['code' => 1, 'msg' => '请选择要删除的商品'], JSON_UNESCAPED_UNICODE));
        }

        $ids       = explode(',', $ids);
        $db        = Database::getInstance();
        $db_prefix = DB_PREFIX;

        $deletedCount      = 0;
        $stockCleanedCount = 0;

        foreach ($ids as $goods_id) {
            $goods_id = intval($goods_id);
            if ($goods_id <= 0) continue;

            // 确认商品在回收站
            $goods = $db->once_fetch_array("SELECT id, type FROM {$db_prefix}goods WHERE id = {$goods_id} AND delete_time IS NOT NULL");
            if (empty($goods)) continue;

            // 清理未售卡密库存
            $db->query("DELETE FROM {$db_prefix}goods_once WHERE goods_id = {$goods_id} AND sale_time IS NULL");
            $stockCleanedCount += $db->affected_rows();

            // 清理通用卡密库存
            $db->query("DELETE FROM {$db_prefix}goods_general WHERE goods_id = {$goods_id}");

            // 清理人工发货库存
            $db->query("DELETE FROM {$db_prefix}goods_service WHERE goods_id = {$goods_id}");

            // 清理规格、折扣、会员价格
            $db->query("DELETE FROM {$db_prefix}skus WHERE goods_id = {$goods_id}");
            $db->query("DELETE FROM {$db_prefix}discount WHERE goods_id = {$goods_id}");
            $db->query("DELETE FROM {$db_prefix}member_price WHERE goods_id = {$goods_id}");

            // 删除商品主表
            $db->query("DELETE FROM {$db_prefix}goods WHERE id = {$goods_id} AND delete_time IS NOT NULL");
            $deletedCount++;
        }

        if ($deletedCount > 0) {
            $msg = "成功删除 {$deletedCount} 个商品";
            if ($stockCleanedCount > 0) {
                $msg .= "，同时清理了 {$stockCleanedCount} 条未售卡密库存";
            }
            die(json_encode(['code' => 0, 'msg' => $msg], JSON_UNESCAPED_UNICODE));
        } else {
            die(json_encode(['code' => 1, 'msg' => '没有找到可删除的商品'], JSON_UNESCAPED_UNICODE));
        }
    } catch (Exception $e) {
        die(json_encode(['code' => 1, 'msg' => '删除失败：' . $e->getMessage()], JSON_UNESCAPED_UNICODE));
    }
}
