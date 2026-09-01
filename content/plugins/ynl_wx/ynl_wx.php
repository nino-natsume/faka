<?php
/*
Plugin Name: 「DC码支付微信」支付通道
Version: 1.0.2
Plugin URL:
Description: 个人免签约支付,资金无中转秒到账,0费率，秒回调
Author: DCSHOP
Author URL:
Ui: Layui
*/

defined('DC_ROOT') || exit('access denied!');

/**
 * 初始化支付方式列表
 */
function init_ynl_wx() {

    $plugin_storage = Storage::getInstance('ynl_wx');

    $name = $plugin_storage->getValue('name');
    $name = empty($name) ? '码支付/支付宝' : $name;

    $GLOBALS['mode_payment'] = array_merge($GLOBALS['mode_payment'], [
        [
            'plugin_name' => 'ynl_wx', // 插件名. 与插件目录名保持一致
            'icon' => './content/plugins/ynl_wx/icon-btn.png',
            'title' => $name, // 当前支付方式名称
            'unique' => 'ynl_wx', // 当前支付方式唯一标识，所有支付插件中此项禁止重复
            'name' => 'DuoCai码支付支付宝'
        ]
    ]);

}

addAction('mode_payment', 'init_ynl_wx');

/**
 * 发起支付 (该方法命名规则：pay_插件名称)
 */
function pay_ynl_wx($order_info, $order_list){


    $plugin_storage = Storage::getInstance('ynl_wx');

    $url = 'https://pay.1kexiu.xyz/submit.php';
    $pid = $plugin_storage->getValue('pid');
    $key = $plugin_storage->getValue('key');
    $name = $plugin_storage->getValue('name');

    if($order_info['expire_time'] <= time()){
        emMsg('订单已过期，请重新发起支付', 'javascript:window.close();');
    }



    $data = [
        'pid' => $pid,
        'type' => 'wxpay',
        'out_trade_no' => $order_info['out_trade_no'],
        'notify_url' => DC_URL . "action/notify/ynl_wx",
        'return_url' => DC_URL . "action/return/ynl_wx",
        'name' => $order_info['out_trade_no'],
        'money' => $order_info['amount'] / 100,
        'sign_type' => 'MD5',
    ];



    $data['sign'] = getYnlWxSign($data, trim($key));

//    d($data);die;

    $html = "<p>提交支付中</p><form action='{$url}' method='get' id='wxpay-form' style='display: none;'>";
    foreach($data as $key => $val){
        $html .= "<input type='text' name='" . $key . "' value='" . $val . "' />";
    }
    $html .= "</form><script>window.onload=function(){document.getElementById('wxpay-form').submit()}</script>";

    echo $html; die;

}


/**
 * 生成签名
 */
function getYnlWxSign($data, $private_key) {
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


function ynl_wxCheckSign($notify_type) {
    
    // d($notify_type);die;

    $plugin_storage = Storage::getInstance('ynl_wx');

    $private_key = $plugin_storage->getValue('key');

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

    $param = Input::getStrVar('param', null);
    if($param){
        $data['param'] = $param;
    }

    $sign = getYnlWxSign($data, $private_key);

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

