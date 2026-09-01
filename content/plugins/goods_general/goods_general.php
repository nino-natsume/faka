<?php
/*
Plugin Name: 商品类型「通用卡密」
Version: 1.0.5
Plugin URL:
Description: 同一卡密可无限次发放，适合销售共享账号、网盘资源、教程链接、通用激活码密钥等不限使用次数的商品
Author: DCSHOP
Author URL:
Ui: Layui
*/

defined('DC_ROOT') || exit('access denied!');

function plugin_goods_general_is_docking_goods($goodsId, $db = null, $db_prefix = null){
    $goodsId = (int)$goodsId;
    if ($goodsId <= 0) return false;
    if ($db === null) $db = Database::getInstance();
    if ($db_prefix === null) $db_prefix = DB_PREFIX;
    $docking_table = $db_prefix . 'docking_goods';
    if (empty($db->once_fetch_array("SHOW TABLES LIKE '" . $db->escape_string($docking_table) . "'"))) return false;
    $chk_dock = $db->once_fetch_array("SELECT id FROM {$docking_table} WHERE goods_id = {$goodsId} LIMIT 1");
    return !empty($chk_dock);
}

function plugin_goods_general_is_xiaoqing_goods($goodsId, $db = null, $db_prefix = null){
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
function plugin_goods_general_type($goods, &$result){
    $goods['goods_type_all'][] = ['name' => '通用卡密', 'value' => 'general'];
    $result = $goods;
}

// 展示商品列表的商品类型徽章
function plugin_goods_list_type_general($type){
    echo <<<html
{{#  if(d.type == 'general'){ }}
<span class="goods-type-tag type-general">通用卡密</span>
{{#  } }}
html;
}

// 发货
function plugin_goods_general_deliver($db, $db_prefix, $goods, $order, $order_child){
    if($goods['type'] == 'general'){
        // 对接商品由对接插件处理发货，跳过
        if (plugin_goods_general_is_docking_goods($order_child['goods_id'], $db, $db_prefix)) return;
        if (plugin_goods_general_is_xiaoqing_goods($order_child['goods_id'], $db, $db_prefix)) return;
        $sku = empty($order_child['sku']) ? '0' : $order_child['sku'];
        // 查询出库存表里的卡密
        $stock = $db->once_fetch_array("SELECT * FROM {$db_prefix}goods_general WHERE goods_id = {$order_child['goods_id']} AND sku = '{$sku}'");
        // 将查询出的卡密写入到发货表里
        $timestamp = time();
        $db->query("INSERT INTO {$db_prefix}goods_general_sale 
    (goods_id, order_list_id, sku, content, num, create_time) 
VALUES ({$order_child['goods_id']}, {$order_child['id']}, '{$sku}', '{$stock['content']}', {$order_child['quantity']}, $timestamp)");

        // 更新订单状态
        $db->query("UPDATE {$db_prefix}order SET status = 2 WHERE id = '{$order['id']}'");
    }
}

// 未售库存页面
function plugin_goods_general_stock_ws($goods, &$result){
    if($goods['type'] == 'general' && !plugin_goods_general_is_xiaoqing_goods($goods['id'] ?? 0)){
        $goods['stock_page'] = "../../content/plugins/goods_general/goods_general_show";
        $result = $goods;
    }

}
// 已售库存页面
function plugin_goods_general_stock_ys($goods, &$result){
    if($goods['type'] == 'general' && !plugin_goods_general_is_xiaoqing_goods($goods['id'] ?? 0)){
        $goods['stock_page'] = "../../content/plugins/goods_general/goods_general_show";
        $result = $goods;
    }

}

// 订单列表按钮
function plugin_goods_general_user_order_list_btn($order, $child_order){
    if($child_order['type'] == 'general' && !plugin_goods_general_is_xiaoqing_goods($child_order['goods_id'] ?? 0)){
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

function plugin_goods_general_get_order_serect($db, $db_prefix, $goods, $order, $child_order, $limit){
    if (plugin_goods_general_is_xiaoqing_goods($child_order['goods_id'] ?? ($goods['id'] ?? 0), $db, $db_prefix)) return ['count' => 0, 'list' => []];
    $sql = "select * from {$db_prefix}goods_general_sale where order_list_id = {$child_order['id']} order by id asc";
    if(!empty($limit)){
        $sql .= " limit {$limit}";
    }
    $kami = $db->fetch_all($sql);
    $sql = "select count(id) res_count from {$db_prefix}goods_general_sale where order_list_id = {$child_order['id']}";
    $res = $db->once_fetch_array($sql);
    return [
        'count' => $res['res_count'],
        'list' => $kami
    ];
}


// 显示订单详情页
function plugin_goods_general_view_order_detail($db, $db_prefix, $goods, $order, $child_order){
    if($goods['type'] == 'general' && !plugin_goods_general_is_xiaoqing_goods($child_order['goods_id'] ?? ($goods['id'] ?? 0), $db, $db_prefix)){
        $kami = $db->once_fetch_array("select * from {$db_prefix}goods_general_sale where order_list_id = {$child_order['id']}");

        include View::getUserView('open_head');
        include DC_ROOT . "/content/plugins/goods_general/order_detail.php";
        include View::getUserView('open_foot');
        View::output();
    }
}


// 获取发货内容
function plugin_goods_general_adm_order_detail($db, $db_prefix, $goods, $child_order){

    if($goods['type'] == 'general' && !plugin_goods_general_is_xiaoqing_goods($child_order['goods_id'] ?? ($goods['id'] ?? 0), $db, $db_prefix)){
        $sale = $db->once_fetch_array("select * from {$db_prefix}goods_general_sale where order_list_id = {$child_order['id']}");
        return  empty($sale) ? '无' : $sale['content'];
    }
}

// 下载发货内容
function adm_download_deliver_content_general($db, $db_prefix, $order_list_id, $goods_id = 0){
    $goods_filter = $goods_id > 0 ? "AND goods_id = {$goods_id}" : '';
    $kami = $db->fetch_all("select * from {$db_prefix}goods_general_sale where order_list_id = {$order_list_id} {$goods_filter} order by id asc");

    $content = "";
    foreach($kami as $val){
        $content .= $val['content'] . "\r\n";
    }
    $date = date('YmdHis');
    $filename = '卡密-' . $date . '.txt';
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($content));
    echo $content;
    exit;
}

// 获取发货内容（用于邮件）
function getDeliverContentgeneral($db, $db_prefix, $order_id){

    // 获取该订单的所有general类型商品的子订单
    $order_list_items = $db->fetch_all("
        SELECT ol.* 
        FROM {$db_prefix}order_list ol 
        LEFT JOIN {$db_prefix}goods g ON ol.goods_id = g.id 
        WHERE ol.order_id = {$order_id} AND g.type = 'general'
    ");

    $content = "";
    
    foreach ($order_list_items as $order_list) {
        if (plugin_goods_general_is_xiaoqing_goods($order_list['goods_id'] ?? 0, $db, $db_prefix)) continue;
        // 获取每个子订单的发货内容
        $list = $db->fetch_all("select * from {$db_prefix}goods_general_sale where order_list_id = {$order_list['id']} order by id asc");
        
        foreach($list as $val){
            $content .= $val['content'] . "\n";
        }
    }

    return $content;
}

function plugin_goods_general_home_goods_list($goods, &$result){
    foreach($goods as $key => $val){
        if($val['type'] == 'general' && !plugin_goods_general_is_xiaoqing_goods($val['id'] ?? 0)){
            $goods[$key]['type_text_badge'] = '<span class="badge badge-success">自动发货</span>';
            $goods[$key]['is_auto'] = true;
        }
    }
    $result = $goods;
}

function plugin_goods_general_goods_content_echo($goods, &$result){
    if($goods['type'] == 'general' && !plugin_goods_general_is_xiaoqing_goods($goods['id'] ?? 0)){
        $goods['type_text_badge'] = '<span class="badge badge-success">自动发货</span>';
        $goods['is_auto'] = true;
    }
    $result = $goods;
}

addAction('adm_add_goods_goodsinfo', 'plugin_goods_general_type');
addAction('adm_goods_list_type', 'plugin_goods_list_type_general');
addAction('adm_stock_page_ws', 'plugin_goods_general_stock_ws');
addAction('adm_stock_page_ys', 'plugin_goods_general_stock_ys');
addAction('deliver', 'plugin_goods_general_deliver');
addAction('user_order_list_btn', 'plugin_goods_general_user_order_list_btn');
addAction('view_order_detail', 'plugin_goods_general_view_order_detail');
addAction('home_goods_list', 'plugin_goods_general_home_goods_list');
addAction('goods_content_echo', 'plugin_goods_general_goods_content_echo');
addAction('copy_product', 'plugin_goods_general_copy_product');

// 复制商品时同步复制通用卡密模板（dc_goods_general），sale 表数据不复制
function plugin_goods_general_copy_product($newGoodsId, $originalGoodsId){
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $rows = $db->fetch_all("SELECT sku, content FROM {$db_prefix}goods_general WHERE goods_id = {$originalGoodsId}");
    if (empty($rows)) return;
    $timestamp = time();
    foreach ($rows as $r) {
        $skuEsc     = addslashes($r['sku']);
        $contentEsc = addslashes($r['content']);
        $db->query("INSERT INTO {$db_prefix}goods_general (goods_id, sku, content, create_time) VALUES ({$newGoodsId}, '{$skuEsc}', '{$contentEsc}', {$timestamp})");
    }
}