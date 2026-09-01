<?php
/*
Plugin Name: 「易支付支付宝」支付通道
Version: 1.0.6
Plugin URL:
Description: 通过易支付接口实现微信扫码支付，需配置易支付商户ID和密钥，适用于无官方支付资质的商户，支持即时到账
Author: DCSHOP
Author URL:
Ui: Layui
*/

defined('DC_ROOT') || exit('access denied!');

/**
 * 初始化支付方式列表
 */
function init_epay_ali() {

    $plugin_storage = Storage::getInstance('epay_ali');

    $name = $plugin_storage->getValue('name');
    $name = empty($name) ? '易支付/支付宝' : $name;

    $GLOBALS['mode_payment'] = array_merge($GLOBALS['mode_payment'], [
        [
            'plugin_name' => 'epay_ali', // 插件名. 与插件目录名保持一致
            'icon' => './content/plugins/epay_ali/icon-btn.png',
            'title' => $name, // 当前支付方式名称
            'unique' => 'epay_ali', // 当前支付方式唯一标识，所有支付插件中此项禁止重复
            'name' => '易支付(支付宝)'
        ]
    ]);

}

addAction('mode_payment', 'init_epay_ali');

/**
 * 发起支付 (该方法命名规则：pay_插件名称)
 */
function pay_epay_ali($order_info, $order_list){




    $plugin_storage = Storage::getInstance('epay_ali');

    $url = $plugin_storage->getValue('url') . 'submit.php';
    $appid = $plugin_storage->getValue('appid');
    $private_key = $plugin_storage->getValue('private_key');
    $name = $plugin_storage->getValue('name');

    if($order_info['expire_time'] <= time()){
        emMsg('订单已过期，请重新发起支付', 'javascript:window.close();');
    }



    $data = [
        'pid' => $appid,
        'type' => 'alipay',
        'out_trade_no' => $order_info['out_trade_no'],
        'notify_url' => DC_URL . "action/notify/epay_ali",
        'return_url' => DC_URL . "action/return/epay_ali/" . $order_info['out_trade_no'],
        'name' => $order_info['out_trade_no'],
        'money' => $order_info['amount'] / 100,
        'sign_type' => 'MD5',
    ];



    $data['sign'] = getEpayAliSign($data, trim($private_key));

//    d($data);die;

    $html = "<p>提交支付中</p><form action='{$url}' method='get' id='alipay-form' style='display: none;'>";
    foreach($data as $key => $val){
        $html .= "<input type='text' name='" . $key . "' value='" . $val . "' />";
    }
    $html .= "</form><script>window.onload=function(){document.getElementById('alipay-form').submit()}</script>";

    echo $html; die;

}


/**
 * 生成签名
 */
function getEpayAliSign($data, $private_key) {
    $para_filter = [];
    
    // 使用foreach替代each()
    foreach ($data as $key => $val) {
        if($key == "sign" || $key == "sign_type" || $val == ""){
            continue;
        }else{
            $para_filter[$key] = $data[$key];
        }
    }
    
    ksort($para_filter);

    $arg  = "";
    // 使用foreach替代each()
    foreach ($para_filter as $key => $val) {
        $arg.=$key."=".$val."&";
    }
    
    // 修正字符串截取逻辑（原代码使用count($arg)-2有误）
    $arg = rtrim($arg, '&');


    $sign = $arg . $private_key;
    return md5($sign);
}


function epay_aliCheckSign($notify_type) {


    
    // d($notify_type);die;

    $plugin_storage = Storage::getInstance('epay_ali');

    $private_key = $plugin_storage->getValue('private_key');

    $data = [
        'pid' => Input::getStrVar('pid'),
        'trade_no' => Input::getStrVar('trade_no'),
        'out_trade_no' => Input::getStrVar('out_trade_no'),
        'type' => Input::getStrVar('type'),
        'name' => Input::getStrVar('name'),
        'money' => Input::getStrVar('money'),
        'trade_status' => Input::getStrVar('trade_status'),
        'sign' => Input::getStrVar('sign'),
        'sign_type' => Input::getStrVar('sign_type'),
    ];

    if($notify_type == 'return'){
        $url = $_SERVER['REQUEST_URI'];
        // 移除查询参数，只保留路径部分
        $path = parse_url($url, PHP_URL_PATH); // 返回：/action/notify/epay_ali
        // 按斜杠分割路径
        $parts = explode('/', trim($path, '/'));
        // 获取第三个部分（索引为2）
        $plugin = $parts[2] ?? '';
        $out_trade_no = empty($parts[3]) ? '' : $parts[3];
        if(empty($data['pid'])){
            $pay_redirect = Option::get('pay_redirect') ? Option::get('pay_redirect') : 'list';

            $orderModel = new Order_Model();
            $order = $orderModel->getOrderInfo($out_trade_no);

            $db = Database::getInstance();
            $sql = "SELECT * FROM `" . DB_PREFIX . "order_list` WHERE `order_id` = {$order['id']} LIMIT 1";
            $order_list = $db->once_fetch_array($sql);
            $sql = "SELECT * FROM `" . DB_PREFIX . "goods` WHERE `id` = {$order_list['goods_id']} LIMIT 1";
            $goods = $db->once_fetch_array($sql);





            if($pay_redirect == 'kami'){
                if($goods['type'] == 'duli' || $goods['type'] == 'guding'){
                    $url = Option::get('blogurl') . 'user/order.php?action=sdk&id=' . $order_list['id'] . '&out_trade_no=' . $order['out_trade_no'];
                }else{
                    if(ISLOGIN){
                        $url = Option::get('blogurl') . 'user/order.php';
                    }else{
                        $url = Option::get('blogurl') . 'user/order.php?action=search&type=out_trade_no&pwd=' . $order['out_trade_no'];
                    }
                }
            }else{
                // 检查是否使用模板，如果是则跳转到模板的订单查询页面
                $nonce_templet = isMobile() ? Option::get('nonce_templet_tel') : Option::get('nonce_templet');
                if (!empty($nonce_templet) && $nonce_templet !== 'default') {
                    // 使用模板，跳转到模板的订单查询页面，并传递订单号
                    $url = Option::get('blogurl') . "?action=order_query&contact=" . urlencode($order['out_trade_no']);
                } else {
                    // 使用默认页面
                    if(ISLOGIN){
                        $url = Option::get('blogurl') . 'user/order.php';
                    }else{
                        $url = Option::get('blogurl') . 'user/order.php?action=search&type=out_trade_no&pwd=' . $order['out_trade_no'];
                    }
                }
            }
            header("location: {$url}"); die;
        }
    }

    $param = Input::getStrVar('param', null);
    if($param){
        $data['param'] = $param;
    }

    $sign = getEpayAliSign($data, $private_key);

    if($data['trade_status'] == 'TRADE_SUCCESS' && $data['sign'] == $sign){
        return [
            'timestamp' => time(),
            'out_trade_no' => $data['out_trade_no'],
            'up_no' => $data['trade_no']
        ];
    }else{
        return false;
    }


    

}

/**
 * 校验$value是否非空
 *  if not set ,return true;
 *    if is null , return true;
 **/
function epayAliCheckEmpty($value) {
    if (!isset($value)) return true;
    if ($value === null) return true;
    if (trim($value) === "") return true;

    return false;
}
