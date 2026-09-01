<?php
/*
Plugin Name: 商品类型「一卡一密」
Version: 1.1.1
Plugin URL:
Description: 一卡一密商品发货模式，卡密独立存储便于管理。适合激活码、充值卡、兑换码、CDK等一次性卡密类商品
Author: DCSHOP
Author URL:
Ui: Layui
*/

defined('DC_ROOT') || exit('access denied!');

function plugin_goods_once_is_docking_goods($goodsId, $db = null, $db_prefix = null){
    $goodsId = (int)$goodsId;
    if ($goodsId <= 0) return false;
    if ($db === null) $db = Database::getInstance();
    if ($db_prefix === null) $db_prefix = DB_PREFIX;
    $docking_table = $db_prefix . 'docking_goods';
    if (empty($db->once_fetch_array("SHOW TABLES LIKE '" . $db->escape_string($docking_table) . "'"))) return false;
    $chk_dock = $db->once_fetch_array("SELECT id FROM {$docking_table} WHERE goods_id = {$goodsId} LIMIT 1");
    return !empty($chk_dock);
}

function plugin_goods_once_is_xiaoqing_goods($goodsId, $db = null, $db_prefix = null){
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
function plugin_goods_once_type($goods, &$result){
    $goods['goods_type_all'][] = ['name' => '一卡一密', 'value' => 'once'];
    $result = $goods;
}

// 展示商品列表的商品类型徽章
function plugin_goods_list_type_once($type){
    echo <<<html
{{#  if(d.type == 'once'){ }}
<span class="goods-type-tag type-once">一卡一密</span>
{{#  } }}
html;
}

// 发货
function plugin_goods_once_deliver($db, $db_prefix, $goods, $order, $order_child){
    if($goods['type'] == 'once'){
        // 对接商品由对接插件处理发货，跳过
        if (plugin_goods_once_is_docking_goods($order_child['goods_id'], $db, $db_prefix)) return;
        if (plugin_goods_once_is_xiaoqing_goods($order_child['goods_id'], $db, $db_prefix)) return;
        $kami_order = Option::get('kami_order');
        $kami_order = empty($kami_order) ? 'asc' : $kami_order;
        $timestamp = time();
        $sku = empty($order_child['sku']) ? '0' : $order_child['sku'];
        // 构建查询排序条件
        switch($kami_order) {
            case 'asc':
                $orderSql = 'ORDER BY id asc';
                break;
            case 'desc':
                $orderSql = 'ORDER BY id desc';
                break;
            default:
                $orderSql = 'ORDER BY RAND()';
        }

        $db->query("
UPDATE {$db_prefix}goods_once 
SET 
    sale_time = {$timestamp}, order_list_id = {$order_child['id']} 
WHERE 
    goods_id = {$order_child['goods_id']} AND sku = '{$sku}' and sale_time is null 
    
    {$orderSql} LIMIT {$order_child['quantity']} 

");
        // 更新订单状态
        $db->query("UPDATE {$db_prefix}order SET status = 2 WHERE id = '{$order['id']}'");
    }
}

// 未售库存页面
function plugin_goods_once_stock_ws($goods, &$result){
    if($goods['type'] == 'once' && !plugin_goods_once_is_xiaoqing_goods($goods['id'] ?? 0)){
        $goods['stock_page'] = "../../content/plugins/goods_once/goods_once_show";
        $result = $goods;
    }

}
// 未售库存页面
function plugin_goods_once_stock_ys($goods, &$result){
    if($goods['type'] == 'once' && !plugin_goods_once_is_xiaoqing_goods($goods['id'] ?? 0)){
        $goods['stock_page'] = "../../content/plugins/goods_once/goods_once_show";
        $result = $goods;
    }

}

// 订单列表按钮
function plugin_goods_once_user_order_list_btn($order, $child_order){
    if($child_order['type'] == 'once' && !plugin_goods_once_is_xiaoqing_goods($child_order['goods_id'] ?? 0)){
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

function adm_download_deliver_content_once($db, $db_prefix, $order_list_id, $goods_id = 0){
    $goods_filter = $goods_id > 0 ? "AND goods_id = {$goods_id}" : '';
    $kami = $db->fetch_all("select * from {$db_prefix}goods_once where order_list_id = {$order_list_id} {$goods_filter} order by id asc");

    $content = "";

    foreach($kami as $val){
        $content .= $val['content'] . "\r\n";
    }
    $date = date('YmdHis');
    $filename = '卡密-' . $date . '.txt';
    // 设置HTTP头
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($content));
    // 输出内容
    echo $content;
    exit;
}

function plugin_goods_once_get_order_serect($db, $db_prefix, $goods, $order, $child_order, $limit){
    $gid = intval($child_order['goods_id']);
    $sql = "select * from {$db_prefix}goods_once where order_list_id = {$child_order['id']} AND goods_id = {$gid} order by id asc";
    if(!empty($limit)){
        $sql .= " limit {$limit}";
    }
    $kami = $db->fetch_all($sql);
    $sql = "select count(id) res_count from {$db_prefix}goods_once where order_list_id = {$child_order['id']} AND goods_id = {$gid}";
    $res = $db->once_fetch_array($sql);
    return [
        'count' => $res['res_count'],
        'list' => $kami
    ];
}

// 显示订单详情页
function plugin_goods_once_view_order_detail($db, $db_prefix, $goods, $order, $child_order){

    if($goods['type'] == 'once' && !plugin_goods_once_is_xiaoqing_goods($child_order['goods_id'] ?? ($goods['id'] ?? 0), $db, $db_prefix)){
        $cmd = Input::getStrVar('cmd');
        $gid = intval($child_order['goods_id']);
        $kami = $db->fetch_all("select * from {$db_prefix}goods_once where order_list_id = {$child_order['id']} AND goods_id = {$gid} order by id asc");

        if($cmd == 'download'){
            $content = "";

            foreach($kami as $val){
                $content .= $val['content'] . "\r\n";
            }
            $date = date('YmdHis');
            $filename = '卡密-' . $date . '.txt';
            // 设置HTTP头
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($content));
            // 输出内容
            echo $content;
            exit;
        }

        $kami = $db->fetch_all("select * from {$db_prefix}goods_once where order_list_id = {$child_order['id']} AND goods_id = {$gid} limit 500");

        $res = $db->once_fetch_array("select count(id) total from {$db_prefix}goods_once where order_list_id = {$child_order['id']} AND goods_id = {$gid}");
        $total = empty($res) ? 0 : $res['total'];

        include View::getUserView('open_head');
        include DC_ROOT . "/content/plugins/goods_once/order_detail.php";
        include View::getUserView('open_foot');
        View::output();
    }
}


// 获取发货内容
function plugin_goods_once_adm_order_detail($db, $db_prefix, $goods, $child_order){

    if($goods['type'] == 'once' && !plugin_goods_once_is_xiaoqing_goods($child_order['goods_id'] ?? ($goods['id'] ?? 0), $db, $db_prefix)){
        $gid = intval($child_order['goods_id']);
        $sale = $db->fetch_all("select * from {$db_prefix}goods_once where order_list_id = {$child_order['id']} AND goods_id = {$gid} limit 5");
        $str = "";
        if(!empty($sale)){
            foreach($sale as $val){
                $str .= $val['content'] . "<hr />";
            }
        }
        return $str;
    }
}

function plugin_goods_once_home_goods_list($goods, &$result){
    foreach($goods as $key => $val){
        if($val['type'] == 'once' && !plugin_goods_once_is_xiaoqing_goods($val['id'] ?? 0)){
            $goods[$key]['type_text_badge'] = '<span class="badge badge-success">自动发货</span>';
            $goods[$key]['is_auto'] = true;
        }
    }
    $result = $goods;
}

function plugin_goods_once_goods_content_echo($goods, &$result){
    if($goods['type'] == 'once' && !plugin_goods_once_is_xiaoqing_goods($goods['id'] ?? 0)){
        $goods['type_text_badge'] = '<span class="badge badge-success">自动发货</span>';
        $goods['is_auto'] = true;
    }
    $result = $goods;
}

// 获取发货内容
function getDeliverContentonce($db, $db_prefix, $order_id){

    // 获取该订单的所有once类型商品的子订单
    $order_list_items = $db->fetch_all("
        SELECT ol.* 
        FROM {$db_prefix}order_list ol 
        LEFT JOIN {$db_prefix}goods g ON ol.goods_id = g.id 
        WHERE ol.order_id = {$order_id} AND g.type = 'once'
    ");

    $content = "";
    
    foreach ($order_list_items as $order_list) {
        if (plugin_goods_once_is_xiaoqing_goods($order_list['goods_id'] ?? 0, $db, $db_prefix)) continue;
        // 获取每个子订单的发货内容
        $list = $db->fetch_all("select * from {$db_prefix}goods_once where order_list_id = {$order_list['id']} AND goods_id = {$order_list['goods_id']} order by id asc");
        
        foreach($list as $val){
            $content .= $val['content'] . "\n";
        }
    }

    return $content;
}

addAction('adm_add_goods_goodsinfo', 'plugin_goods_once_type');
addAction('adm_goods_list_type', 'plugin_goods_list_type_once');
addAction('adm_stock_page_ws', 'plugin_goods_once_stock_ws');
addAction('adm_stock_page_ys', 'plugin_goods_once_stock_ys');
addAction('deliver', 'plugin_goods_once_deliver');
addAction('user_order_list_btn', 'plugin_goods_once_user_order_list_btn');
addAction('view_order_detail', 'plugin_goods_once_view_order_detail');
addAction('home_goods_list', 'plugin_goods_once_home_goods_list');
addAction('goods_content_echo', 'plugin_goods_once_goods_content_echo');