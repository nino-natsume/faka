<?php
/*
Plugin Name: 商品类型「虚拟服务」
Version: 1.0.2
Plugin URL:
Description: 人工发货模式。买家下单付款后需商家手动处理并发货，适合代充值、代下单、定制服务等非自动发货商品
Author: DCSHOP
Author URL:
Ui: Layui
*/

defined('DC_ROOT') || exit('access denied!');

function plugin_goods_service_mapping_exists($table, $goodsId, $db = null, $db_prefix = null){
    $goodsId = (int)$goodsId;
    $table = trim((string)$table);
    if ($goodsId <= 0 || $table === '' || !preg_match('/^[a-zA-Z0-9_]+$/', $table)) return false;
    if ($db === null) $db = Database::getInstance();
    if ($db_prefix === null) $db_prefix = DB_PREFIX;
    $tableName = $db_prefix . $table;
    if (empty($db->once_fetch_array("SHOW TABLES LIKE '" . $db->escape_string($tableName) . "'"))) return false;
    $row = $db->once_fetch_array("SELECT id FROM {$tableName} WHERE goods_id = {$goodsId} LIMIT 1");
    return !empty($row);
}

function plugin_goods_service_is_xiaoqing_goods($goodsId, $db = null, $db_prefix = null){
    $goodsId = (int)$goodsId;
    if ($goodsId <= 0) return false;
    if ($db === null) $db = Database::getInstance();
    if ($db_prefix === null) $db_prefix = DB_PREFIX;
    $xiaoqing_table = $db_prefix . 'xiaoqing_goods';
    if (empty($db->once_fetch_array("SHOW TABLES LIKE '" . $db->escape_string($xiaoqing_table) . "'"))) return false;
    $chk_xiaoqing = $db->once_fetch_array("SELECT id FROM {$xiaoqing_table} WHERE goods_id = {$goodsId} LIMIT 1");
    return !empty($chk_xiaoqing);
}

// 添加商品类型
function plugin_goods_service_type($goods, &$result){
    $goods['goods_type_all'][] = ['name' => '虚拟服务', 'value' => 'service'];
    $result = $goods;
}

// 展示商品列表的商品类型徽章
function plugin_goods_list_type_service($type){
    echo <<<html
{{#  if(d.type == 'service'){ }}
<span class="goods-type-tag type-service">虚拟服务</span>
{{#  } }}
html;
}

function plugin_goods_service_default_content(){
    return '已收到您的订单，客服将会尽快为您处理！';
}

function plugin_goods_service_guess_templates($goodsId, $title){
    $goodsId = (int)$goodsId;
    $title = trim((string)$title);
    $baseTitle = preg_replace('/(?:\s*-\s*副本)+$/u', '', $title);
    if (empty($baseTitle) || $baseTitle === $title) {
        return [];
    }
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $baseTitleEsc = addslashes($baseTitle);
    $sourceGoods = $db->once_fetch_array("SELECT id FROM {$db_prefix}goods WHERE type = 'service' AND title = '{$baseTitleEsc}' AND id <> {$goodsId} ORDER BY id DESC LIMIT 1");
    if (empty($sourceGoods['id'])) {
        return [];
    }
    $rows = $db->fetch_all("SELECT sku, content FROM {$db_prefix}goods_service WHERE goods_id = {$sourceGoods['id']}");
    $map = [];
    foreach($rows as $row){
        $map[(string)$row['sku']] = (string)$row['content'];
    }
    return $map;
}

function plugin_goods_service_ensure_templates($goodsId, $goods = []){
    $goodsId = (int)$goodsId;
    if ($goodsId <= 0) {
        return;
    }
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    if (empty($goods) || !is_array($goods)) {
        $goods = $db->once_fetch_array("SELECT id, type, title, is_sku, stock FROM {$db_prefix}goods WHERE id = {$goodsId}");
    }
    if (empty($goods) || $goods['type'] != 'service') {
        return;
    }
    $skus = $db->fetch_all("SELECT sku, stock FROM {$db_prefix}skus WHERE goods_id = {$goodsId}");
    if (empty($skus) && (int)$goods['stock'] > 0) {
        $skus = [['sku' => '0', 'stock' => (int)$goods['stock']]];
    }
    if (empty($skus)) {
        return;
    }
    $guessTemplates = plugin_goods_service_guess_templates($goodsId, $goods['title'] ?? '');
    $defaultContent = plugin_goods_service_default_content();
    $timestamp = time();
    $lockName = 'goods_service_tpl_' . $goodsId;
    $lockNameEsc = addslashes($lockName);
    $lockRes = $db->once_fetch_array("SELECT GET_LOCK('{$lockNameEsc}', 5) AS lck");
    if (empty($lockRes) || (int)$lockRes['lck'] !== 1) {
        return;
    }
    try {
        foreach($skus as $skuRow){
            $sku = (string)$skuRow['sku'];
            $skuEsc = addslashes($sku);
            $rows = $db->fetch_all("SELECT id, content FROM {$db_prefix}goods_service WHERE goods_id = {$goodsId} AND sku = '{$skuEsc}' ORDER BY id ASC");
            if (!empty($rows)) {
                $keepId = (int)$rows[0]['id'];
                $keepContent = (string)$rows[0]['content'];
                if ($keepContent === '') {
                    $content = isset($guessTemplates[$sku]) && $guessTemplates[$sku] !== '' ? $guessTemplates[$sku] : $defaultContent;
                    $contentEsc = addslashes($content);
                    $db->query("UPDATE {$db_prefix}goods_service SET content = '{$contentEsc}' WHERE id = {$keepId}");
                }
                if (count($rows) > 1) {
                    $dupIds = [];
                    foreach($rows as $idx => $row){
                        if ($idx === 0) {
                            continue;
                        }
                        $dupIds[] = (int)$row['id'];
                    }
                    if (!empty($dupIds)) {
                        $db->query("DELETE FROM {$db_prefix}goods_service WHERE id IN (" . implode(',', $dupIds) . ")");
                    }
                }
                continue;
            }
            if ((int)$skuRow['stock'] <= 0) {
                continue;
            }
            $content = isset($guessTemplates[$sku]) && $guessTemplates[$sku] !== '' ? $guessTemplates[$sku] : $defaultContent;
            $contentEsc = addslashes($content);
            $db->query("INSERT INTO {$db_prefix}goods_service (goods_id, sku, content, create_time) VALUES ({$goodsId}, '{$skuEsc}', '{$contentEsc}', {$timestamp})");
        }
    } finally {
        $db->once_fetch_array("SELECT RELEASE_LOCK('{$lockNameEsc}') AS rel");
    }
}

// 发货
function plugin_goods_service_deliver($db, $db_prefix, $goods, $order, $order_child){
    if($goods['type'] == 'service'){
        if (plugin_goods_service_mapping_exists('docking_goods', $order_child['goods_id'], $db, $db_prefix)) return;
        if (plugin_goods_service_is_xiaoqing_goods($order_child['goods_id'], $db, $db_prefix)) return;
        if (plugin_goods_service_mapping_exists('qingjiu_goods', $order_child['goods_id'], $db, $db_prefix)) return;
        plugin_goods_service_ensure_templates($order_child['goods_id'], $goods);
        $sku = empty($order_child['sku']) ? '0' : $order_child['sku'];
        // 查询出库存表里的卡密
        $stock = $db->once_fetch_array("SELECT * FROM {$db_prefix}goods_service WHERE goods_id = {$order_child['goods_id']} AND sku = '{$sku}'");
        // 将查询出的卡密写入到发货表里
        $timestamp = time();
        $db->query("INSERT INTO {$db_prefix}goods_service_sale 
    (goods_id, order_list_id, sku, content, num, create_time) 
VALUES ({$order_child['goods_id']}, {$order_child['id']}, '{$sku}', '{$stock['content']}', {$order_child['quantity']}, $timestamp)");

        // 更新订单状态为待发货（人工发货商品需要等待商家处理）
        $db->query("UPDATE {$db_prefix}order SET status = 1 WHERE id = '{$order['id']}'");
    }
}

// 未售库存页面
function plugin_goods_service_stock_ws($goods, &$result){
    if($goods['type'] == 'service' && !plugin_goods_service_is_xiaoqing_goods($goods['id'] ?? 0)){
        $goods['stock_page'] = "../../content/plugins/goods_service/goods_service_show";
        $result = $goods;
    }

}
// 已售库存页面
function plugin_goods_service_stock_ys($goods, &$result){
    if($goods['type'] == 'service' && !plugin_goods_service_is_xiaoqing_goods($goods['id'] ?? 0)){
        $goods['stock_page'] = "../../content/plugins/goods_service/goods_service_show";
        $result = $goods;
    }

}

// 订单列表按钮
function plugin_goods_service_user_order_list_btn($order, $child_order){
    if($child_order['type'] == 'service' && !plugin_goods_service_is_xiaoqing_goods($child_order['goods_id'] ?? 0)){
        if(!empty($order['pay_time'])){
            echo <<<html
<a href="?action=sdk&out_trade_no={$order['out_trade_no']}" class="layui-btn">
    查看卡密
</a>
<a href="{$child_order['url']}" target="_blank" class="layui-btn layui-bg-cyan">再次购买</a>
html;

        }
    }
}
// 显示订单详情页
function plugin_goods_service_view_order_detail($db, $db_prefix, $goods, $order, $child_order){
    if($goods['type'] == 'service' && !plugin_goods_service_is_xiaoqing_goods($child_order['goods_id'] ?? ($goods['id'] ?? 0), $db, $db_prefix)){
        $msg = $db->once_fetch_array("select * from {$db_prefix}goods_service_sale where order_list_id = {$child_order['id']} and is_default = 'y'");
        $kami = $db->once_fetch_array("select * from {$db_prefix}goods_service_sale where order_list_id = {$child_order['id']} and is_default = 'n'");

        include View::getUserView('open_head');
        include DC_ROOT . "/content/plugins/goods_service/order_detail.php";
        include View::getUserView('open_foot');
        View::output();
    }
}

// 后台发货页面
function plugin_goods_service_adm_deliver_view($db, $db_prefix, $goods, $order, $child_order){
    if($goods['type'] == 'service'){
        if (plugin_goods_service_mapping_exists('docking_goods', $goods['id'], $db, $db_prefix)) return;
        if (plugin_goods_service_is_xiaoqing_goods($goods['id'], $db, $db_prefix)) return;
        include View::getAdmView('open_head');
        require_once DC_ROOT . "/content/plugins/goods_service/adm_deliver_view.php";
        include View::getAdmView('open_foot');
        View::output();
    }
}

function plugin_goods_service_get_order_serect($db, $db_prefix, $goods, $order, $child_order, $limit){
    if (plugin_goods_service_is_xiaoqing_goods($child_order['goods_id'] ?? ($goods['id'] ?? 0), $db, $db_prefix)) return ['count' => 0, 'list' => []];
    $sql = "select * from {$db_prefix}goods_service_sale where order_list_id = {$child_order['id']} and is_default = 'n' order by id asc";
    if(!empty($limit)){
        $sql .= " limit {$limit}";
    }
    $kami = $db->fetch_all($sql);
    $sql = "select count(id) res_count from {$db_prefix}goods_service_sale where order_list_id = {$child_order['id']} and is_default = 'n'";
    $res = $db->once_fetch_array($sql);
    return [
        'count' => $res['res_count'],
        'list' => $kami
    ];
}

// 获取发货内容
function plugin_goods_service_adm_order_detail($db, $db_prefix, $goods, $child_order){
//    d($data);die;
    if($goods['type'] == 'service' && !plugin_goods_service_is_xiaoqing_goods($child_order['goods_id'] ?? ($goods['id'] ?? 0), $db, $db_prefix)){
        $sale = $db->once_fetch_array("select * from {$db_prefix}goods_service_sale where order_list_id = {$child_order['id']} and is_default = 'n'");
        return empty($sale) ? '无' : $sale['content'];
    }
}

function plugin_goods_service_home_goods_list($goods, &$result){
    foreach($goods as $key => $val){
        if($val['type'] == 'service' && !plugin_goods_service_is_xiaoqing_goods($val['id'] ?? 0)){
            $goods[$key]['type_text_badge'] = '<span class="badge badge-danger">人工发货</span>';
        }
    }
    $result = $goods;
}

function plugin_goods_service_goods_content_echo($goods, &$result){
    if($goods['type'] == 'service' && !plugin_goods_service_is_xiaoqing_goods($goods['id'] ?? 0)){
        $goods['type_text_badge'] = '<span class="badge badge-danger">人工发货</span>';
    }
    $result = $goods;
}

// 复制商品时同步复制虚拟服务模板卡密（dc_goods_service）
function plugin_goods_service_copy_product($newGoodsId, $originalGoodsId){
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $rows = $db->fetch_all("SELECT sku, content FROM {$db_prefix}goods_service WHERE goods_id = {$originalGoodsId}");
    if (empty($rows)) return;
    $timestamp = time();
    foreach ($rows as $r) {
        $skuEsc     = addslashes($r['sku']);
        $contentEsc = addslashes($r['content']);
        $db->query("INSERT INTO {$db_prefix}goods_service (goods_id, sku, content, create_time) VALUES ({$newGoodsId}, '{$skuEsc}', '{$contentEsc}', {$timestamp})");
    }
}

// 获取发货内容
function getDeliverContentservice($db, $db_prefix, $order_id){

    // 获取该订单的所有service类型商品的子订单
    $order_list_items = $db->fetch_all("
        SELECT ol.* 
        FROM {$db_prefix}order_list ol 
        LEFT JOIN {$db_prefix}goods g ON ol.goods_id = g.id 
        WHERE ol.order_id = {$order_id} AND g.type = 'service'
    ");

    $content = "";
    
    foreach ($order_list_items as $order_list) {
        if (plugin_goods_service_is_xiaoqing_goods($order_list['goods_id'] ?? 0, $db, $db_prefix)) continue;
        // 获取每个子订单的发货内容
        $list = $db->fetch_all("select * from {$db_prefix}goods_service_sale where order_list_id = {$order_list['id']} order by id asc");
        
        foreach($list as $val){
            $content .= $val['content'] . "\n";
        }
    }

    return $content;
}

addAction('adm_add_goods_goodsinfo', 'plugin_goods_service_type');
addAction('adm_goods_list_type', 'plugin_goods_list_type_service');
addAction('adm_stock_page_ws', 'plugin_goods_service_stock_ws');
addAction('adm_stock_page_ys', 'plugin_goods_service_stock_ys');
addAction('deliver', 'plugin_goods_service_deliver');
addAction('user_order_list_btn', 'plugin_goods_service_user_order_list_btn');
addAction('view_order_detail', 'plugin_goods_service_view_order_detail');
addAction('adm_deliver_view', 'plugin_goods_service_adm_deliver_view');
addAction('home_goods_list', 'plugin_goods_service_home_goods_list');
addAction('goods_content_echo', 'plugin_goods_service_goods_content_echo');
addAction('copy_product', 'plugin_goods_service_copy_product');