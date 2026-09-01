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



require_once 'globals.php';

//require_once '../init.php';



$orderModel = new Order_Model();

$Sort_Model = new Sort_Model();

$User_Model = new User_Model();

$MediaSort_Model = new MediaSort_Model();

$Template_Model = new Template_Model();



$sta_cache = $CACHE->readCache('sta');

$user_cache = $CACHE->readCache('user');

$action = Input::getStrVar('action');

if (!function_exists('__balance_withdraw_method_map')) {
    function __balance_withdraw_method_map() {
        if (class_exists('Level_Service') && method_exists('Level_Service', 'getWithdrawMethodMap')) {
            return Level_Service::getWithdrawMethodMap();
        }
        return [
            'alipay' => '支付宝',
            'wechat' => '微信',
            'qq'     => 'QQ',
            'bank'   => '银行卡',
        ];
    }
}

if (!function_exists('__balance_enabled_withdraw_methods')) {
    function __balance_enabled_withdraw_methods() {
        if (class_exists('Level_Service') && method_exists('Level_Service', 'getWithdrawMethods')) {
            return Level_Service::getWithdrawMethods();
        }
        return array_keys(__balance_withdraw_method_map());
    }
}

if (!function_exists('__balance_withdraw_method_options')) {
    function __balance_withdraw_method_options() {
        $map = __balance_withdraw_method_map();
        $enabled = __balance_enabled_withdraw_methods();
        $options = [];
        foreach ($enabled as $key) {
            if (isset($map[$key])) {
                $options[] = ['value' => $key, 'label' => $map[$key]];
            }
        }
        if (empty($options)) {
            foreach ($map as $key => $label) {
                $options[] = ['value' => $key, 'label' => $label];
            }
        }
        return $options;
    }
}

if (!function_exists('__balance_normalize_withdraw_method')) {
    function __balance_normalize_withdraw_method($method, $options) {
        $method = strtolower(trim((string)$method));
        foreach ($options as $option) {
            if (($option['value'] ?? '') === $method) {
                return $method;
            }
        }
        return isset($options[0]['value']) ? (string)$options[0]['value'] : '';
    }
}



if (empty($action)) {



    $userModel = new User_Model();

    $user = $userModel->getOneUser(UID);

    $payment = array_values(array_filter(getPayment(), function($item){

        return isset($item['plugin_name']) && $item['plugin_name'] !== 'balance';

    }));

    $rechargeAmountOptions = [10, 50, 100, 200, 500, 1000];

    $savedWithdrawReceiptImage = trim((string)($user['withdraw_receipt_image'] ?? ''));

    $savedWithdrawReceiptImageUrl = $savedWithdrawReceiptImage !== '' ? DC_URL . ltrim($savedWithdrawReceiptImage, '/') : '';

    $savedWithdrawRealname = trim((string)($user['withdraw_realname'] ?? ''));

    $savedWithdrawAccount = trim((string)($user['withdraw_account'] ?? ''));

    $savedWithdrawMethod = trim((string)($user['withdraw_method'] ?? ''));

    $withdrawMethodOptions = __balance_withdraw_method_options();
    $savedWithdrawMethod = __balance_normalize_withdraw_method($savedWithdrawMethod, $withdrawMethodOptions);

    $withdrawMinAmount = class_exists('Level_Service') ? max(0, round((float)Level_Service::getSetting(Level_Service::OPT_WITHDRAW_MIN, 10), 2)) : 10.00;

    $withdrawFeeRate = class_exists('Level_Service') ? max(0, min(100, round((float)Level_Service::getSetting(Level_Service::OPT_WITHDRAW_FEE, 0), 2))) : 0.00;

    $uc_app_mode = isMobile();

    $uc_page_title = '我的余额';



    // 直接使用自适应布局，自动适配当前模板

    include View::getUserView('_adaptive_header');

    require_once View::getUserView(isMobile() ? 'adaptive_balance_mobile' : 'adaptive_balance');

    include View::getUserView('_adaptive_footer');

    

    View::output();

}



if($action == 'recharge_ajax'){

    if(!ISLOGIN){

        Ret::error('请先登录');

    }



    LoginAuth::checkToken();



    $amount = round(floatval(Input::postStrVar('amount')), 2);

    $payment_plugin = Input::postStrVar('payment_plugin');

    $payment_title = Input::postStrVar('payment_title');



    $_rechargeMin = class_exists('Level_Service') ? (float)Level_Service::getSetting(Level_Service::OPT_RECHARGE_MIN, 1) : 1;
    $_rechargeMax = class_exists('Level_Service') ? (float)Level_Service::getSetting(Level_Service::OPT_RECHARGE_MAX, 10000) : 10000;
    if ($amount < $_rechargeMin || $amount > $_rechargeMax) {
        Ret::error('充值金额需在 ' . $_rechargeMin . ' - ' . $_rechargeMax . ' 元之间');
    }



    $paymentList = array_values(array_filter(getPayment(), function($item){

        return isset($item['plugin_name']) && $item['plugin_name'] !== 'balance';

    }));



    $selectedPayment = [];

    foreach($paymentList as $item){

        if($item['plugin_name'] === $payment_plugin){

            $selectedPayment = $item;

            break;

        }

    }



    if(empty($selectedPayment)){

        Ret::error($payment_plugin !== '' ? (function_exists('dcPaymentUnavailableMessage') ? dcPaymentUnavailableMessage($payment_plugin) : '后台支付插件未配置完整，请联系管理员') : '请选择支付方式');

    }



    $timestamp = time();

    global $stationData;

    $insertOrder = [

        'station_id' => isset($stationData['id']) ? intval($stationData['id']) : 0,

        'client_ip' => getClientIP(),

        'user_id' => UID,

        'out_trade_no' => date('YmdHis', $timestamp) . mt_rand(1000, 9999),

        'amount' => intval(round($amount * 100)),

        'payment' => empty($payment_title) ? $selectedPayment['title'] : $payment_title,

        'pay_name' => empty($selectedPayment['name']) ? $selectedPayment['title'] : $selectedPayment['name'],

        'pay_plugin' => $selectedPayment['plugin_name'],

        'expire_time' => $timestamp + 600,

        'create_time' => $timestamp,

        'device' => 'balance_recharge'

    ];



    $db = Database::getInstance();

    $db->add('order', $insertOrder);

    User_Log_Model::log(UID, 'order_create', '创建余额充值订单，订单号: ' . $insertOrder['out_trade_no'] . '，金额: ¥' . number_format($amount, 2), $amount);



    Ret::success('下单成功', ['out_trade_no' => $insertOrder['out_trade_no']]);

}



if($action == 'withdraw_ajax'){

    if (!ISLOGIN) {

        Ret::error('请先登录');

    }

    LoginAuth::checkToken();

    $db = Database::getInstance();

    if (class_exists('Level_Service') && (int)Level_Service::getSetting(Level_Service::OPT_WITHDRAW_SWITCH, 1) !== 1) {
        Ret::error('提现功能已关闭，如有疑问请联系客服');
    }
    // 提现权限：统一走会员等级门槛
    if (class_exists('Level_Service') && !Level_Service::meetGate(UID, Level_Service::OPT_DEPOSIT_GRADE)) {
        Ret::error(Level_Service::gateTipMsg(Level_Service::OPT_DEPOSIT_GRADE));
    }

    $userModel = new User_Model();

    $user = $userModel->getOneUser(UID);
 
    if (empty($user)) {
        Ret::error('用户不存在');
    }

    $pendingWithdraw = $db->once_fetch_array("SELECT id FROM " . DB_PREFIX . "withdraw WHERE user_id=" . UID . " AND status=0 LIMIT 1");
    if (!empty($pendingWithdraw)) {
        Ret::error('您有一笔提现申请正在审核中，请等待处理完成后再提交');
    }

    $amount = round((float)Input::postStrVar('amount'), 2);
    $method = trim((string)Input::postStrVar('method'));
    $account = trim((string)Input::postStrVar('account'));
    $realname = trim((string)Input::postStrVar('realname'));
    $remark = trim((string)Input::postStrVar('remark'));
    $receipt_image = trim((string)Input::postStrVar('receipt_image'));
    if ($receipt_image === '') {
        $receipt_image = trim((string)($user['withdraw_receipt_image'] ?? ''));
    }

    if ($amount <= 0) {
        Ret::error('请输入有效的提现金额');
    }
    $withdrawMinAmount = class_exists('Level_Service') ? max(0, round((float)Level_Service::getSetting(Level_Service::OPT_WITHDRAW_MIN, 10), 2)) : 10.00;
    if ($withdrawMinAmount > 0 && $amount < $withdrawMinAmount) {
        Ret::error('最低提现金额为 ¥' . number_format($withdrawMinAmount, 2, '.', ''));
    }
    $userMoney = round((float)($user['money'] ?? 0), 2);
    if ($amount > $userMoney) {
        Ret::error('提现金额不能大于当前可用余额');
    }
    $enabledWithdrawMethods = __balance_enabled_withdraw_methods();
    if (!in_array($method, $enabledWithdrawMethods, true)) {
        Ret::error('请选择可用的提现方式');
    }
    if ($account === '') {
        Ret::error('请填写提现账号');
    }
    if ($realname === '') {
        Ret::error('请填写真实姓名');
    }

    $_feeRate = class_exists('Level_Service') ? (float)Level_Service::getSetting(Level_Service::OPT_WITHDRAW_FEE, 0) : 0;
    $_feeRate = max(0, min(100, $_feeRate));
    $_fee = $_feeRate > 0 ? round($amount * $_feeRate / 100, 2) : 0;
    $_actualAmount = max(0, round($amount - $_fee, 2));

    $withdrawData = [
        'user_id' => UID,
        'amount' => $amount,
        'service_change' => $_fee,
        'actual_amount' => $_actualAmount,
        'method' => $method,
        'account' => $account,
        'realname' => $realname,
        'remark' => $remark,
        'receipt_image' => $receipt_image,
        'status' => 0,
        'create_time' => time()
    ];

    $balanceModel = new Balance_Model();
    $balanceModel->dec(UID, $amount, '余额提现');
    $db->add('withdraw', $withdrawData);
    $withdrawId = (int)$db->insert_id();
    if ($withdrawId <= 0) {
        $balanceModel->inc(UID, $amount, '提现申请失败，余额已退回账户');
        Ret::error('提现申请提交失败，请稍后重试');
    }

    $userUpdate = [
        'withdraw_method' => $method,
        'withdraw_account' => $account,
        'withdraw_realname' => $realname
    ];
    if ($receipt_image !== '') {
        $userUpdate['withdraw_receipt_image'] = $receipt_image;
    }
    $db->update('user', $userUpdate, ['uid' => UID]);

    $_feeMsg = $_fee > 0 ? '，手续费 ¥' . number_format($_fee, 2, '.', '') . '，实际到账 ¥' . number_format($_actualAmount, 2, '.', '') : '';
    User_Log_Model::log(UID, User_Log_Model::TYPE_WITHDRAW_APPLY, '申请提现 ¥' . number_format($amount, 2, '.', '') . '（' . $method . '）' . $_feeMsg, -$amount);

    Ret::success('提现申请已提交，请耐心等待处理', [
        'id' => $withdrawId,
        'amount' => number_format($amount, 2, '.', ''),
        'fee' => number_format($_fee, 2, '.', ''),
        'actual_amount' => number_format($_actualAmount, 2, '.', '')
    ]);

}



if ($action == 'withdraw') {

    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $user = $db->once_fetch_array("select * from " . DB_PREFIX . "user where uid=" . UID);
    if (empty($user)) {
        emMsg('用户不存在');
    }

    $savedWithdrawReceiptImage = trim((string)($user['withdraw_receipt_image'] ?? ''));
    $savedWithdrawReceiptImageUrl = $savedWithdrawReceiptImage !== '' ? DC_URL . ltrim($savedWithdrawReceiptImage, '/') : '';
    $savedWithdrawRealname = trim((string)($user['withdraw_realname'] ?? ''));
    $savedWithdrawAccount = trim((string)($user['withdraw_account'] ?? ''));
    $savedWithdrawMethod = trim((string)($user['withdraw_method'] ?? ''));
    $withdrawMethodOptions = __balance_withdraw_method_options();
    $savedWithdrawMethod = __balance_normalize_withdraw_method($savedWithdrawMethod, $withdrawMethodOptions);
    $withdrawMinAmount = class_exists('Level_Service') ? max(0, round((float)Level_Service::getSetting(Level_Service::OPT_WITHDRAW_MIN, 10), 2)) : 10.00;
    $withdrawFeeRate = class_exists('Level_Service') ? max(0, min(100, round((float)Level_Service::getSetting(Level_Service::OPT_WITHDRAW_FEE, 0), 2))) : 0.00;
    $hasPendingWithdraw = (bool)$db->once_fetch_array("SELECT id FROM {$db_prefix}withdraw WHERE user_id=" . UID . " AND status = 0 LIMIT 1");

    include View::getUserView('open_head');
    require_once View::getUserView(isMobile() ? 'adaptive_withdraw_mobile' : 'adaptive_withdraw');
    include View::getUserView('open_foot');
    View::output();

}



if ($action == 'balance_log') {

    $userModel = new User_Model();

    $user = $userModel->getOneUser(UID);

    $uc_app_mode = isMobile();

    $uc_page_title = '收支明细';



    include View::getUserView('_adaptive_header');

    require_once View::getUserView(isMobile() ? 'adaptive_balance_log_app' : 'adaptive_balance_log');

    include View::getUserView('_adaptive_footer');

    View::output();

}

if ($action == 'credits_log') {
    $userModel = new User_Model();
    $user = $userModel->getOneUser(UID);
    $uc_app_mode = isMobile();
    $uc_page_title = getVirtualCurrencyName() . '明细';
    include View::getUserView('_adaptive_header');
    require_once View::getUserView(isMobile() ? 'adaptive_credits_log_app' : 'adaptive_credits_log');
    include View::getUserView('_adaptive_footer');
    View::output();
}

if ($action == 'withdraw_index') {



    $uc_app_mode = isMobile();

    $uc_page_title = '提现记录';



    include View::getUserView('_adaptive_header');

    require_once View::getUserView(isMobile() ? 'withdraw_index_mobile' : 'withdraw_index');

    include View::getUserView('_adaptive_footer');

    View::output();

}



if ($action == 'withdraw_list') {

    $page = Input::getIntVar('page', 1);

    $limit = Input::getIntVar('limit', 10);

    $withdrawModel = new Withdraw_Model();



    $res = $withdrawModel->getWithdrawList(UID, $page, $limit);

    output::data($res['list'], $res['total']);

}





if ($action == 'card_redeem_ajax') {

    if (!ISLOGIN) {

        Ret::error('请先登录');

    }

    LoginAuth::checkToken();



    $cardKey = trim(Input::postStrVar('card_key'));

    if ($cardKey === '') {

        Ret::error('请输入充值卡密');

    }



    $rechargeCardModel = new Recharge_Card_Model();

    $result = $rechargeCardModel->redeem($cardKey, UID);



    if (isset($result['code']) && $result['code'] === 0) {

        Ret::success($result['msg'] ?? '充值成功', $result['data'] ?? []);

    }



    Ret::error($result['msg'] ?? '充值失败');

}



if ($action == 'credits_log_list') {
    $page = Input::getIntVar('page', 1);
    $limit = Input::getIntVar('limit', 10);
    $start = ($page - 1) * $limit;
    $user_id = UID;
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $table = $db_prefix . 'user_log';

    $where = "uid={$user_id} AND type='admin_credits'";
    $list = $db->fetch_all("SELECT * FROM {$table} WHERE {$where} ORDER BY id DESC LIMIT {$start}, {$limit}");
    $res = $db->once_fetch_array("SELECT count(id) total FROM {$table} WHERE {$where}");
    $statInc  = $db->once_fetch_array("SELECT count(id) cnt, IFNULL(sum(amount),0) amt FROM {$table} WHERE {$where} AND amount>0");
    $statDec  = $db->once_fetch_array("SELECT count(id) cnt, IFNULL(sum(ABS(amount)),0) amt FROM {$table} WHERE {$where} AND amount<0");

    foreach ($list as &$val) {
        $val['create_time_text'] = date('Y-m-d H:i:s', $val['create_time']);
        $val['plus'] = ((float)$val['amount'] >= 0) ? 'y' : 'n';
        $val['abs_amount'] = abs((int)$val['amount']);
    }
    unset($val);

    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode([
        'code'  => 0,
        'msg'   => 'ok',
        'data'  => $list,
        'count' => (int)$res['total'],
        'stat_inc_cnt'  => (int)$statInc['cnt'],
        'stat_inc_amt'  => (int)$statInc['amt'],
        'stat_dec_cnt'  => (int)$statDec['cnt'],
        'stat_dec_amt'  => (int)$statDec['amt'],
    ], JSON_UNESCAPED_UNICODE));
}

if($action == 'index'){

    $page = Input::getIntVar('page', 1);

    $limit = Input::getIntVar('limit', 10);

    $start = ($page - 1) * $limit;



    $user_id = UID;



    $db = Database::getInstance();

    $db_prefix = DB_PREFIX;



    $list = $db->fetch_all("select * from {$db_prefix}balance_log where user_id={$user_id} order by id desc limit $start, $limit");

    $res = $db->once_fetch_array("select count(id) total from {$db_prefix}balance_log where user_id={$user_id}");

    $statPlus  = $db->once_fetch_array("select count(id) cnt, IFNULL(sum(money),0) amt from {$db_prefix}balance_log where user_id={$user_id} and plus='y'");
    $statMinus = $db->once_fetch_array("select count(id) cnt, IFNULL(sum(money),0) amt from {$db_prefix}balance_log where user_id={$user_id} and plus='n'");



    foreach($list as &$val){

        $val['create_time'] = date('Y-m-d H:i:s', $val['create_time']);

        $val['type'] = $val['plus'] == 'n' ? '减少' : '增加';

    }



    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode([
        'code'  => 0,
        'msg'   => 'ok',
        'data'  => $list,
        'count' => (int)$res['total'],
        'stat_plus_cnt'   => (int)$statPlus['cnt'],
        'stat_plus_amt'   => round((float)$statPlus['amt'], 2),
        'stat_minus_cnt'  => (int)$statMinus['cnt'],
        'stat_minus_amt'  => round((float)$statMinus['amt'], 2),
    ], JSON_UNESCAPED_UNICODE));

}

