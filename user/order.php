<?php
/**
 * The productf management
 *
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

/**
 * @var string $action
 * @var object $CACHE
 */

//require_once 'globals.php';
require_once '../init.php';

$orderModel = new Order_Model();
$Sort_Model = new Sort_Model();
$User_Model = new User_Model();
$MediaSort_Model = new MediaSort_Model();
$Template_Model = new Template_Model();

$sta_cache = $CACHE->readCache('sta');
$user_cache = $CACHE->readCache('user');
$action = Input::getStrVar('action');

// 订单列表
if (empty($action)) {
    loginAuth::checkLogin(NULL, 'user');

    // 自动取消超时未支付的订单（时间可在后台商城配置中设置）
    $db = Database::getInstance();
    $_cpt = intval(Option::get('continue_pay_timeout'));
    $_cpt = $_cpt > 0 ? $_cpt : 30;
    $expire_time = time() - $_cpt * 60;
    $db->query("UPDATE " . DB_PREFIX . "order SET status = 3 WHERE status = 0 AND create_time < {$expire_time} AND delete_time IS NULL");

    require_once './api.php';

    $res = api_get_user_order(['user_id' => UID]);
    if($res['code'] == 200){
        $list = $res['data'];
    }

    $uc_app_mode = isMobile();
    $uc_page_title = '订单列表';

//    d($list);die;

    include View::getUserView('_adaptive_header');
    require_once View::getUserView(isMobile() ? 'adaptive_order_mobile' : 'adaptive_order');
    include View::getUserView('_adaptive_footer');
    View::output();
}
// 订单列表
if ($action == 'search') {
    $tab = 'search';
    $pwd = Input::getStrVar('pwd');
    if(empty($pwd)){
        include View::getUserView('open_head');
        require_once View::getUserView('order');
        include View::getUserView('open_foot');
        View::output();
    }
    $page = Input::getIntVar('page', 1);
    $orderNum = $orderModel->getYoukeOrderNum($pwd);
    $order = $orderModel->getYoukeOrderForHome($page, $pwd);
    $subPage = '';
    foreach ($_GET as $key => $val) {
        $subPage .= $key != 'page' ? "&$key=$val" : '';
    }
    $pageurl = pagination($orderNum, Option::get('admin_article_perpage_num'), $page, "order.php?{$subPage}&page=");
    $GLOBALS['mode_payment'] = [];
    doAction('mode_payment');
    if(isset($GLOBALS['mode_payment'][0])){
        $GLOBALS['mode_payment'][0]['active'] = true;
    }
    $mode_payment = $GLOBALS['mode_payment'];
    include View::getUserView('open_head');
    require_once View::getUserView('order');
    include View::getUserView('open_foot');
    View::output();
}

if($action == 'download'){
    $db = Database::getInstance();
    $id = Input::getIntVar('order_list_id'); // 子订单ID
    $sql = "SELECT * from " . DB_PREFIX . "deliver WHERE order_list_id={$id} order by id asc";
    $res = $db->query($sql);
    $content = "";
    while ($row = $db->fetch_array($res)) {
        $content .= $row['content'] . "\r\n";
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


if($action == 'get_order_serect'){
    require_once './api.php';
    $post = [
        'out_trade_no' => Input::postStrVar('out_trade_no'),
        'order_list_id' => Input::postIntVar('order_list_id'),
        'limit' => Input::postIntVar('limit')
    ];
    $res =  api_get_order_serect($post);
    echo json_encode($res);die;
}

