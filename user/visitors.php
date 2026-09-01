<?php

require_once '../init.php';

$action = Input::getStrVar('action');

if (empty($action)) {
    $order_required = Option::get('order_required');
    $order_required = empty($order_required) ? [] : json_decode($order_required, true);
    $uc_app_mode = isMobile();
    $uc_page_title = '游客查单';
    include View::getUserView('_adaptive_header');
    require_once(View::getUserView(isMobile() ? 'adaptive_visitors_mobile' : 'adaptive_visitors'));
    include View::getUserView('_adaptive_footer');
    View::output();
}

if($action == 'get_visitors_order'){
    require_once './api.php';

    $_search = Input::getStrVar('_search');
    $_search = json_decode(base64_decode($_search), true);

//    d($_search);die;

    $res = api_get_visitors_order($_search);
    if($res['code'] == 200){
        $list = $res['data'];
    }

//    d($list);die;

    $uc_app_mode = isMobile();
    $uc_page_title = '游客查单结果';
    include View::getUserView('_adaptive_header');
    require_once(View::getUserView(isMobile() ? 'visitors_order_mobile' : 'visitors_order'));
    include View::getUserView('_adaptive_footer');
    View::output();
}

if($action == 'visitors_search_order_count'){
    require_once './api.php';
    $res =  api_visitors_search_order_count();

    echo json_encode($res);die;
}

if($action == 'sdk'){
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $out_trade_no = addslashes(Input::getStrVar('out_trade_no'));
    $order_list_id = Input::getIntVar('order_list_id');
    $order = $db->once_fetch_array("select * from {$db_prefix}order where out_trade_no = '{$out_trade_no}'");
    if (empty($order)) {
        emMsg('订单不存在');
    }
    if ($order_list_id > 0) {
        $child_order = $db->once_fetch_array("select * from {$db_prefix}order_list where id = {$order_list_id} and order_id = {$order['id']}");
    } else {
        $child_order = $db->once_fetch_array("select * from {$db_prefix}order_list where order_id = {$order['id']}");
    }
    if (empty($child_order)) {
        emMsg('订单商品不存在');
    }
    $goods = $db->once_fetch_array("select * from {$db_prefix}goods where id = {$child_order['goods_id']}");
    doAction('view_order_detail', $db, $db_prefix, $goods, $order, $child_order);
    die;
}
