<?php
/**
 * homepage & article detail
 *
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

class Pay_Controller {

    /**
     * 发起支付
     */
    function index() {
        $out_trade_no = Input::getStrVar('out_trade_no');
        if(empty($out_trade_no)) emMsg('非法请求');

        $db = Database::getInstance();
        $db_prefix = DB_PREFIX;
        $timestamp = time();

        $sql = "select * from {$db_prefix}order where out_trade_no = '{$out_trade_no}' limit 1";
        $order = $db->once_fetch_array($sql);
        if(empty($order)) emMsg('非法请求');

        $sql = "select * from {$db_prefix}order_list where order_id={$order['id']}";
        $child_order = $db->fetch_all($sql);

        $pay_func = "pay_{$order['pay_plugin']}";

        if($order['pay_plugin'] == 'balance' || $order['amount'] == 0){
            // 余额支付
            if(ISLOGIN){
                if($this->isBalanceRechargeOrder($order)){
                    emMsg('余额不可用于钱包充值');
                }
                $sql = "SELECT * FROM {$db_prefix}user WHERE uid = " . UID;

                $user = $db->once_fetch_array($sql);
                $order_money = $order['amount'] / 100;
                if($user['money'] < $order_money){
                    emMsg('您的余额不足，请先充值', '/user/balance.php');
                }
                $sql = "update {$db_prefix}user set money = money - {$order_money} where uid=" . UID;
                $db->query($sql);

                // 拼接商品名称，使 balance_log 描述更详细
                $goodsTitles = [];
                if (!empty($child_order)) {
                    $goodsIds = [];
                    foreach ($child_order as $ci) {
                        if (!empty($ci['goods_id'])) $goodsIds[] = (int)$ci['goods_id'];
                    }
                    if ($goodsIds) {
                        $gRows = $db->fetch_all("select id, title from {$db_prefix}goods where id in (" . implode(',', $goodsIds) . ")");
                        $gMap = [];
                        foreach ($gRows as $gr) { $gMap[$gr['id']] = $gr['title']; }
                        foreach ($child_order as $ci) {
                            $gid = (int)($ci['goods_id'] ?? 0);
                            $t = $gMap[$gid] ?? '';
                            if ($t === '') continue;
                            $qty = (int)($ci['quantity'] ?? 1);
                            $goodsTitles[] = $qty > 1 ? ($t . ' ×' . $qty) : $t;
                        }
                    }
                }
                $descGoods = $goodsTitles ? implode('、', $goodsTitles) : '商品';
                if (mb_strlen($descGoods) > 40) $descGoods = mb_substr($descGoods, 0, 40) . '…';
                $payDesc = '购买' . $descGoods . '（订单号：' . $out_trade_no . '）';
                $blance_insert = [
                    'user_id' => UID,
                    'plus' => 'n',
                    'money' => $order_money,
                    'update_before' => $user['money'],
                    'description' => $payDesc,
                    'create_time' => $timestamp
                ];
                $db->add('balance_log', $blance_insert);
                $orderModel = new Order_Model();
                // 修改订单的支付状态
                $order_info = $orderModel->getOrderInfo($out_trade_no);

                // 等级开通订单：走 finishLevelUpgrade，不发货
                if ($this->isLevelUpgradeOrder($order_info)) {
                    $this->finishLevelUpgrade($out_trade_no, time(), 'balance');
                    header('location: ' . $this->getLevelUpgradeRedirectUrl());
                    die;
                }

                $order_update = [
                    'pay_status' => 1,
                    'status' => 1,
                ];
                $orderModel->updateOrderPayStatus($order_info['id'], $order_update); // 修改订单的支付状态和订单状态
                // 更新订单的支付时间
                $order_info['payment'] = $order_money == 0 ? '免费商品' : $order_info['payment'];
                $orderModel->updateOrderInfo($out_trade_no, ['pay_time' => time(), 'payment' => $order_info['payment']]);
                // 去发货
                $orderModel->deliver($order_info['id']);
                // 发货完成，订单流程结束。跳转到用户订单页面
                $pay_redirect = Option::get('pay_redirect') ? Option::get('pay_redirect') : 'list';
                $nonce_templet = isMobile() ? Option::get('nonce_templet_tel') : Option::get('nonce_templet');
                if (!empty($nonce_templet) && $nonce_templet !== 'default') {
                    $url = DC_URL . "?action=order_query&contact=" . urlencode($out_trade_no);
                    if ($pay_redirect == 'kami') $url .= '&show=kami';
                } elseif (ISLOGIN) {
                    $url = DC_URL . 'user/order.php';
                    if ($pay_redirect == 'kami') $url .= '?auto_show=' . urlencode($out_trade_no);
                } else {
                    $url = DC_URL . '?action=order_query&contact=' . urlencode($out_trade_no);
                    if ($pay_redirect == 'kami') $url .= '&show=kami';
                }
                header('location: ' . $url);
                die;

            }else{
                if($order['amount'] == 0){
                    emMsg('未登录用户无法购买免费商品，请先登录~');
                }else{
                    emMsg('未登录用户无法使用余额支付');
                }
            }
        }else{
            if (!function_exists($pay_func) || (function_exists('dcPaymentPluginReady') && !dcPaymentPluginReady($order['pay_plugin'], $order['amount'] / 100))) {
                emMsg(function_exists('dcPaymentUnavailableMessage') ? dcPaymentUnavailableMessage($order['pay_plugin']) : '后台支付插件未配置完整，请联系管理员', 'javascript:window.close();');
            }
            // 发起支付前，主动查询网关支付状态（解决网关侧补单不触发回调的问题）
            if ($order['pay_status'] != 1) {
                $gatewayResult = $this->queryGatewayPayStatus($order);
                if ($gatewayResult) {
                    $orderModel = new Order_Model();
                    $orderModel->updateOrderPayStatus($order['id'], ['pay_status' => 1, 'status' => 1]);
                    $updateData = ['pay_time' => time(), 'up_no' => $gatewayResult['trade_no']];
                    if ($this->isBalanceRechargeOrder($order)) {
                        $this->finishBalanceRecharge($out_trade_no, time(), $gatewayResult['trade_no']);
                    } else if ($this->isLevelUpgradeOrder($order)) {
                        $this->finishLevelUpgrade($out_trade_no, time(), $gatewayResult['trade_no']);
                    } else {
                        $orderModel->updateOrderInfo($out_trade_no, $updateData);
                        $orderModel->deliver($order['id']);
                    }
                    header('location: ' . $this->getPayRedirectUrl($out_trade_no, $order['id']));
                    die;
                }
            }
            $pay_func($order, $child_order);
        }

        die('发起支付中');

    }


    /**
     * 同步通知
     */
    public function _return(){


        $orderModel = new Order_Model();

        $url = $_SERVER['REQUEST_URI'];
        // 移除查询参数，只保留路径部分
        $path = parse_url($url, PHP_URL_PATH); // 返回：/action/notify/epay_ali
        // 按斜杠分割路径
        $parts = explode('/', trim($path, '/'));
        // 获取第三个部分（索引为2）
        $plugin = $parts[2] ?? '';
        $out_trade_no = empty($parts[3]) ? '' : $parts[3];

        $checkFunc = $plugin . "CheckSign";
        
        // 检查验签函数是否存在，防止函数不存在导致致命错误
        if(!function_exists($checkFunc)){
            Log::error("支付回调验签函数不存在: {$checkFunc}");
            echo '支付插件验签函数不存在';
            die;
        }
        
        $checkSign = $checkFunc('return');

        if($checkSign){ // 验签通过 - 支付成功
            $order_update = [
                'pay_status' => 1,
                'status' => 1,
            ];
            $order = $orderModel->getOrderInfo($checkSign['out_trade_no']);
            if($this->isBalanceRechargeOrder($order)){
                $this->finishBalanceRecharge($checkSign['out_trade_no'], $checkSign['timestamp'], $checkSign['up_no']);
                header("location: " . $this->getBalanceRechargeRedirectUrl());
                die;
            }
            if($this->isLevelUpgradeOrder($order)){
                $this->finishLevelUpgrade($checkSign['out_trade_no'], $checkSign['timestamp'], $checkSign['up_no']);
                header("location: " . $this->getLevelUpgradeRedirectUrl());
                die;
            }
            if($order['pay_status'] == 1){
                $db = Database::getInstance();
                $sql = "SELECT * FROM `" . DB_PREFIX . "order_list` WHERE `order_id` = {$order['id']} LIMIT 1";
                $order_list = $db->once_fetch_array($sql);
                $sql = "SELECT * FROM `" . DB_PREFIX . "goods` WHERE `id` = {$order_list['goods_id']} LIMIT 1";
                $goods = $db->once_fetch_array($sql);

                $pay_redirect = Option::get('pay_redirect') ? Option::get('pay_redirect') : 'list';
                $nonce_templet = isMobile() ? Option::get('nonce_templet_tel') : Option::get('nonce_templet');
                if (!empty($nonce_templet) && $nonce_templet !== 'default') {
                    $url = DC_URL . "?action=order_query&contact=" . urlencode($order['out_trade_no']);
                    if ($pay_redirect == 'kami') $url .= '&show=kami';
                } elseif (ISLOGIN) {
                    $url = DC_URL . 'user/order.php';
                    if ($pay_redirect == 'kami') $url .= '?auto_show=' . urlencode($order['out_trade_no']);
                } else {
                    $url = DC_URL . '?action=order_query&contact=' . urlencode($order['out_trade_no']);
                    if ($pay_redirect == 'kami') $url .= '&show=kami';
                }
                header("location: {$url}");
                die;
            }else{
                $res = $orderModel->updateOrderPayStatus($order['id'], $order_update); // 修改订单的支付状态
                if($res == false){
                    $db = Database::getInstance();
                    $sql = "SELECT * FROM `" . DB_PREFIX . "order_list` WHERE `order_id` = {$order['id']} LIMIT 1";
                    $order_list = $db->once_fetch_array($sql);
                    $sql = "SELECT * FROM `" . DB_PREFIX . "goods` WHERE `id` = {$order_list['goods_id']} LIMIT 1";
                    $goods = $db->once_fetch_array($sql);

                    $pay_redirect = Option::get('pay_redirect') ? Option::get('pay_redirect') : 'list';
                    $nonce_templet = isMobile() ? Option::get('nonce_templet_tel') : Option::get('nonce_templet');
                    if (!empty($nonce_templet) && $nonce_templet !== 'default') {
                        $url = DC_URL . "?action=order_query&contact=" . urlencode($order['out_trade_no']);
                        if ($pay_redirect == 'kami') $url .= '&show=kami';
                    } elseif (ISLOGIN) {
                        $url = DC_URL . 'user/order.php';
                        if ($pay_redirect == 'kami') $url .= '?auto_show=' . urlencode($order['out_trade_no']);
                    } else {
                        $url = DC_URL . '?action=order_query&contact=' . urlencode($order['out_trade_no']);
                        if ($pay_redirect == 'kami') $url .= '&show=kami';
                    }
                    header("location: {$url}");
                    die;
                }
            }

            Log::info("同步回调！订单号：{$checkSign['out_trade_no']}");

            // 更新订单的支付时间
            $orderModel->updateOrderInfo($checkSign['out_trade_no'], [
                'pay_time' => $checkSign['timestamp'],
                'up_no' => $checkSign['up_no']
            ]);
            // 去发货
            $orderModel->deliver($order['id']);

            $pay_redirect = Option::get('pay_redirect') ? Option::get('pay_redirect') : 'list';
            $nonce_templet = isMobile() ? Option::get('nonce_templet_tel') : Option::get('nonce_templet');
            if (!empty($nonce_templet) && $nonce_templet !== 'default') {
                $url = DC_URL . "?action=order_query&contact=" . urlencode($order['out_trade_no']);
                if ($pay_redirect == 'kami') $url .= '&show=kami';
            } elseif (ISLOGIN) {
                $url = DC_URL . 'user/order.php';
                if ($pay_redirect == 'kami') $url .= '?auto_show=' . urlencode($order['out_trade_no']);
            } else {
                $url = DC_URL . '?action=order_query&contact=' . urlencode($order['out_trade_no']);
                if ($pay_redirect == 'kami') $url .= '&show=kami';
            }
            header("location: {$url}");
            die;



        }else{ // 验签失败
            echo '验签失败';
        }
    }

    /**
     * 异步通知
     */
    public function notify(){

        $orderModel = new Order_Model();

        $url = $_SERVER['REQUEST_URI'];
        $path = parse_url($url, PHP_URL_PATH);
        $parts = explode('/', trim($path, '/'));
        $plugin = $parts[2] ?? '';
        $checkFunc = $plugin . "CheckSign";
        
        if(!function_exists($checkFunc)){
            Log::error("支付回调验签函数不存在: {$checkFunc}");
            echo '支付插件验签函数不存在';
            die;
        }
        
        $checkSign = $checkFunc('notify');

        if($checkSign){
            // 支持插件自定义回调响应文本（默认 success）
            $callbackResp = $checkSign['callback_response'] ?? 'success';

            $order_info = $orderModel->getOrderInfo($checkSign['out_trade_no']);
            if($this->isBalanceRechargeOrder($order_info)){
                $this->finishBalanceRecharge($checkSign['out_trade_no'], $checkSign['timestamp'], $checkSign['up_no']);
                echo $callbackResp; die;
            }
            if($this->isLevelUpgradeOrder($order_info)){
                $this->finishLevelUpgrade($checkSign['out_trade_no'], $checkSign['timestamp'], $checkSign['up_no']);
                echo $callbackResp; die;
            }
 
            $order_update = [
                'pay_status' => 1,
                'status' => 1,
            ];

            $order = $orderModel->getOrderInfo($checkSign['out_trade_no']);
            if($order['pay_status'] == 1){
                echo $callbackResp; die;
            }else{
                $res = $orderModel->updateOrderPayStatus($order_info['id'], $order_update);
                if($res == false){
                    echo $callbackResp; die;
                }
            }

            Log::info("异步回调！订单号：{$checkSign['out_trade_no']}");

            $orderModel->updateOrderInfo($checkSign['out_trade_no'], [
                'pay_time' => $checkSign['timestamp'],
                'up_no' => $checkSign['up_no']
            ]);
            $orderModel->deliver($order_info['id']);
            echo $callbackResp; die;

        }else{
            echo '验签失败';
        }

    }

    /**
     * 补单
     */
    public function repay($out_trade_no){
        if(!ISLOGIN || !User::isAdmin()){
            output::error('无权限操作，补单功能仅限管理员使用');
        }
        
        $orderModel = new Order_Model();
        $order_info = $orderModel->getOrderInfo($out_trade_no);
        
        if(empty($order_info)){
            output::error('订单不存在');
        }

        if($this->isBalanceRechargeOrder($order_info)){
            if(!$this->finishBalanceRecharge($out_trade_no, time(), 'admin_repay')){
                output::error('充值补单失败');
            }
            return;
        }

        if($this->isLevelUpgradeOrder($order_info)){
            if(!$this->finishLevelUpgrade($out_trade_no, time(), 'admin_repay')){
                output::error('等级开通补单失败');
            }
            return;
        }

        $order_update = [
            'pay_status' => 1,
            'status' => 1,
        ];
        $res = $orderModel->updateOrderPayStatus($order_info['id'], $order_update);
        if(!$res){
            output::error('请勿重复补单，该订单状态为已支付！');
        }
        $orderModel->updateOrderInfo($out_trade_no, ['pay_time' => time(), 'payment' => $order_info['payment'] . '(补单)']);
        $orderModel->deliver($order_info['id']);
    }

    /**
     * 验证订单支付状态
     */
    public function isPay(){
        $out_trade_no = Input::postStrVar('out_trade_no');
        $orderModel = new Order_Model();
        $order_info = $orderModel->getOrderInfo($out_trade_no);
        if(empty($order_info)){
            die(json_encode([
                'code' => 400, 'msg' => '订单不存在', 'data' => [
                    'is_pay' => false
                ]
            ]));
        }
        if($order_info['pay_time']){
            $url = $this->getPayRedirectUrl($out_trade_no, $order_info['id']);
            die(json_encode([
                'code' => 200, 'msg' => 'Paid', 'data' => [
                    'is_pay' => true,
                    'url' => $url
                ]
            ]));
        }

        $gatewayResult = $this->queryGatewayPayStatus($order_info);
        if($gatewayResult){
            if($this->isBalanceRechargeOrder($order_info)){
                $this->finishBalanceRecharge($out_trade_no, time(), $gatewayResult['trade_no']);
            } else if($this->isLevelUpgradeOrder($order_info)){
                $this->finishLevelUpgrade($out_trade_no, time(), $gatewayResult['trade_no']);
            } else {
                $orderModel->updateOrderPayStatus($order_info['id'], ['pay_status' => 1, 'status' => 1]);
                $orderModel->updateOrderInfo($out_trade_no, [
                    'pay_time' => time(),
                    'up_no' => $gatewayResult['trade_no']
                ]);
                $orderModel->deliver($order_info['id']);
            }
            $url = $this->getPayRedirectUrl($out_trade_no, $order_info['id']);
            die(json_encode([
                'code' => 200, 'msg' => 'Paid', 'data' => [
                    'is_pay' => true,
                    'url' => $url
                ]
            ]));
        }

        die(json_encode([
            'code' => 200, 'msg' => 'Unpaid', 'data' => [
                'is_pay' => false
            ]
        ]));
    }

    /**
     * 支付宝当面付 - 主动查询支付状态
     * 解决本地测试或回调失败时无法确认支付状态的问题
     */
    public function alipayCheckPay(){
        $out_trade_no = Input::postStrVar('out_trade_no');
        $orderModel = new Order_Model();
        $order_info = $orderModel->getOrderInfo($out_trade_no);
        
        if(empty($order_info)) {
            die(json_encode(['code' => 400, 'msg' => '订单不存在', 'data' => ['is_pay' => false]]));
        }
        
        // 如果订单已支付，直接返回
        if($order_info['pay_time']){
            $url = $this->getPayRedirectUrl($out_trade_no, $order_info['id']);
            die(json_encode(['code' => 200, 'msg' => 'Paid', 'data' => ['is_pay' => true, 'url' => $url]]));
        }
        
        if($this->isBalanceRechargeOrder($order_info)) {
            if(function_exists('alipay_query_order')) {
                $result = alipay_query_order($out_trade_no);
                if($result['success'] && $result['is_paid']) {
                    $this->finishBalanceRecharge($out_trade_no, time(), $result['trade_no']);
                    $url = $this->getPayRedirectUrl($out_trade_no, $order_info['id']);
                    die(json_encode(['code' => 200, 'msg' => 'Paid', 'data' => ['is_pay' => true, 'url' => $url]]));
                }
            }
            die(json_encode(['code' => 200, 'msg' => 'Unpaid', 'data' => ['is_pay' => false]]));
        }

        if($this->isLevelUpgradeOrder($order_info)) {
            if(function_exists('alipay_query_order')) {
                $result = alipay_query_order($out_trade_no);
                if($result['success'] && $result['is_paid']) {
                    $this->finishLevelUpgrade($out_trade_no, time(), $result['trade_no']);
                    $url = $this->getPayRedirectUrl($out_trade_no, $order_info['id']);
                    die(json_encode(['code' => 200, 'msg' => 'Paid', 'data' => ['is_pay' => true, 'url' => $url]]));
                }
            }
            die(json_encode(['code' => 200, 'msg' => 'Unpaid', 'data' => ['is_pay' => false]]));
        }
         
        // 主动查询支付宝订单状态
        if(function_exists('alipay_query_order')) {
            $result = alipay_query_order($out_trade_no);
            
            if($result['success'] && $result['is_paid']) {
                // 支付宝确认已支付，更新本地订单状态
                $order_update = ['pay_status' => 1, 'status' => 1];
                $res = $orderModel->updateOrderPayStatus($order_info['id'], $order_update);
                
                if($res !== false) {
                    // 更新订单的支付时间
                    $orderModel->updateOrderInfo($out_trade_no, [
                        'pay_time' => time(),
                        'up_no' => $result['trade_no']
                    ]);
                    // 去发货
                    $orderModel->deliver($order_info['id']);
                }
                
                $url = $this->getPayRedirectUrl($out_trade_no, $order_info['id']);
                die(json_encode(['code' => 200, 'msg' => 'Paid', 'data' => ['is_pay' => true, 'url' => $url]]));
            }
        }
        
        die(json_encode(['code' => 200, 'msg' => 'Unpaid', 'data' => ['is_pay' => false]]));
    }

    /**
     * 支付宝2当面付 - 主动查询支付状态
     */
    public function alipay2CheckPay(){
        $out_trade_no = Input::postStrVar('out_trade_no');
        $orderModel = new Order_Model();
        $order_info = $orderModel->getOrderInfo($out_trade_no);
        
        if(empty($order_info)) {
            die(json_encode(['code' => 400, 'msg' => '订单不存在', 'data' => ['is_pay' => false]]));
        }
        
        if($order_info['pay_time']){
            $url = $this->getPayRedirectUrl($out_trade_no, $order_info['id']);
            die(json_encode(['code' => 200, 'msg' => 'Paid', 'data' => ['is_pay' => true, 'url' => $url]]));
        }
        
        if($this->isBalanceRechargeOrder($order_info)) {
            if(function_exists('alipay2_query_order')) {
                $result = alipay2_query_order($out_trade_no);
                if($result['success'] && $result['is_paid']) {
                    $this->finishBalanceRecharge($out_trade_no, time(), $result['trade_no']);
                    $url = $this->getPayRedirectUrl($out_trade_no, $order_info['id']);
                    die(json_encode(['code' => 200, 'msg' => 'Paid', 'data' => ['is_pay' => true, 'url' => $url]]));
                }
            }
            die(json_encode(['code' => 200, 'msg' => 'Unpaid', 'data' => ['is_pay' => false]]));
        }

        if($this->isLevelUpgradeOrder($order_info)) {
            if(function_exists('alipay2_query_order')) {
                $result = alipay2_query_order($out_trade_no);
                if($result['success'] && $result['is_paid']) {
                    $this->finishLevelUpgrade($out_trade_no, time(), $result['trade_no']);
                    $url = $this->getPayRedirectUrl($out_trade_no, $order_info['id']);
                    die(json_encode(['code' => 200, 'msg' => 'Paid', 'data' => ['is_pay' => true, 'url' => $url]]));
                }
            }
            die(json_encode(['code' => 200, 'msg' => 'Unpaid', 'data' => ['is_pay' => false]]));
        }
         
        if(function_exists('alipay2_query_order')) {
            $result = alipay2_query_order($out_trade_no);
            
            if($result['success'] && $result['is_paid']) {
                $order_update = ['pay_status' => 1, 'status' => 1];
                $res = $orderModel->updateOrderPayStatus($order_info['id'], $order_update);
                
                if($res !== false) {
                    $orderModel->updateOrderInfo($out_trade_no, [
                        'pay_time' => time(),
                        'up_no' => $result['trade_no']
                    ]);
                    $orderModel->deliver($order_info['id']);
                }
                
                $url = $this->getPayRedirectUrl($out_trade_no, $order_info['id']);
                die(json_encode(['code' => 200, 'msg' => 'Paid', 'data' => ['is_pay' => true, 'url' => $url]]));
            }
        }
        
        die(json_encode(['code' => 200, 'msg' => 'Unpaid', 'data' => ['is_pay' => false]]));
    }
    
    /**
     * 获取支付成功后的跳转URL
     */
    private function getPayRedirectUrl($out_trade_no, $order_id) {
        $db = Database::getInstance();
        $order = $db->once_fetch_array("SELECT * FROM `" . DB_PREFIX . "order` WHERE out_trade_no = '{$out_trade_no}' LIMIT 1");
        if($this->isBalanceRechargeOrder($order)){
            return $this->getBalanceRechargeRedirectUrl();
        }
        if($this->isLevelUpgradeOrder($order)){
            return $this->getLevelUpgradeRedirectUrl();
        }
        $nonce_templet = isMobile() ? Option::get('nonce_templet_tel') : Option::get('nonce_templet');
        $pay_redirect = Option::get('pay_redirect') ? Option::get('pay_redirect') : 'list';

        // 如果使用了自定义模板（非default），统一跳转到模板的订单查询页面
        if (!empty($nonce_templet) && $nonce_templet !== 'default') {
            $url = DC_URL . "?action=order_query&contact=" . urlencode($out_trade_no);
            if ($pay_redirect == 'kami') $url .= '&show=kami';
            return $url;
        }
        
        // 默认模板的处理逻辑
        if(ISLOGIN){
            $url = DC_URL . 'user/order.php';
            if ($pay_redirect == 'kami') $url .= '?auto_show=' . urlencode($out_trade_no);
            return $url;
        } else {
            $url = DC_URL . '?action=order_query&contact=' . urlencode($out_trade_no);
            if ($pay_redirect == 'kami') $url .= '&show=kami';
            return $url;
        }
    }



    private function isBalanceRechargeOrder($order) {
        return !empty($order) && isset($order['device']) && $order['device'] === 'balance_recharge';
    }

    private function getBalanceRechargeRedirectUrl() {
        return DC_URL . 'user/balance.php';
    }

    /**
     * 判断是否为会员等级开通订单
     */
    private function isLevelUpgradeOrder($order) {
        return !empty($order) && isset($order['device']) && $order['device'] === 'level_upgrade';
    }

    /**
     * 等级开通支付成功后的跳转地址
     */
    private function getLevelUpgradeRedirectUrl() {
        return DC_URL . 'user/level.php?paid=1';
    }

    /**
     * 完成等级开通订单（幂等）
     * 与 finishBalanceRecharge 对称，但调用 Level_Order_Model::finish 更新用户等级
     */
    private function finishLevelUpgrade($out_trade_no, $timestamp = 0, $up_no = '') {
        $db = Database::getInstance();
        $prefix = DB_PREFIX;
        $safeTradeNo = addslashes($out_trade_no);
        $order = $db->once_fetch_array("SELECT * FROM {$prefix}order WHERE out_trade_no = '{$safeTradeNo}' LIMIT 1");
        if(empty($order) || !$this->isLevelUpgradeOrder($order)){
            return false;
        }
        $time = empty($timestamp) ? time() : intval($timestamp);
        $orderModel = new Order_Model();
        $levelOrderModel = new Level_Order_Model();
        $levelOrder = $levelOrderModel->getByTradeNo($out_trade_no);
        if($order['pay_status'] == 1 || !empty($order['pay_time'])){
            if(!empty($levelOrder) && intval($levelOrder['state']) === 1){
                return true;
            }
            $finishTime = !empty($order['pay_time']) ? intval($order['pay_time']) : $time;
            return $this->applyLevelFinishAndCommission($levelOrderModel, $out_trade_no, $finishTime);
        }

        try {
            $db->beginTransaction();
            $res = $orderModel->updateOrderPayStatus($order['id'], [
                'pay_status' => 1,
                'status' => 2,
                'service_status' => 1
            ]);
            if($res === false){
                $db->rollback();
                $latestOrder = $db->once_fetch_array("SELECT pay_status, pay_time FROM {$prefix}order WHERE id=" . intval($order['id']) . " LIMIT 1");
                if(!empty($latestOrder) && ((int)$latestOrder['pay_status'] === 1 || !empty($latestOrder['pay_time']))){
                    $finishTime = !empty($latestOrder['pay_time']) ? intval($latestOrder['pay_time']) : $time;
                    return $this->applyLevelFinishAndCommission($levelOrderModel, $out_trade_no, $finishTime);
                }
                return false;
            }
            $orderModel->updateOrderInfo($out_trade_no, [
                'pay_time' => $time,
                'up_no' => $up_no,
                'payment' => $order['payment']
            ]);
            $db->commit();

            // 交给 Level_Order_Model 处理等级变更 + 触发升级奖励分成
            return $this->applyLevelFinishAndCommission($levelOrderModel, $out_trade_no, $time);
        } catch (Exception $e) {
            $db->rollback();
            Log::error('等级开通入账失败：' . $e->getMessage());
            return false;
        }
    }

    /**
     * 等级订单完成 + 升级奖励分成（幂等）
     * 用 applyFinish 检测 already_done 防止重复发放，与 user/level.php 钱包支付路径对称
     */
    private function applyLevelFinishAndCommission($levelOrderModel, $out_trade_no, $timestamp) {
        $result = $levelOrderModel->applyFinish($out_trade_no, $timestamp, true);
        if ($result === false) return false;
        if (!empty($result['already_done'])) return true;

        // 首次完成：写日志
        $levelOrderModel->writeFinishLog($result);

        // 升级奖励分成（与 user/level.php 钱包支付路径对称）
        try {
            if (class_exists('Commission_Model') && class_exists('Level_Service')) {
                $rewardTypes = array_map('trim', explode(',', (string)Level_Service::getSetting(Level_Service::OPT_UPGRADE_REWARD_TYPES, 'open,upgrade,renew')));
                $purchaseType = $result['purchase_type'] ?? 'open';
                if (in_array($purchaseType, $rewardTypes)) {
                    $basePrice = !empty($result['base_price']) ? (float)$result['base_price'] : (float)$result['amount'];
                    $commissionModel = new Commission_Model();
                    $commissionModel->payUpgrade(
                        (int)$result['uid'],
                        $basePrice,
                        (int)$result['level_id'],
                        (string)$out_trade_no
                    );
                }
            }
        } catch (Throwable $e) {
            if (class_exists('Log')) {
                Log::error('在线支付等级升级奖励分成失败：' . $e->getMessage());
            }
        }

        return true;
    }

    private function finishBalanceRecharge($out_trade_no, $timestamp = 0, $up_no = '') {
        $db = Database::getInstance();
        $prefix = DB_PREFIX;
        $safeTradeNo = addslashes($out_trade_no);
        $order = $db->once_fetch_array("SELECT * FROM {$prefix}order WHERE out_trade_no = '{$safeTradeNo}' LIMIT 1");
        if(empty($order) || !$this->isBalanceRechargeOrder($order)){
            return false;
        }
        if($order['pay_status'] == 1 || !empty($order['pay_time'])){
            return true;
        }

        $time = empty($timestamp) ? time() : intval($timestamp);
        $orderModel = new Order_Model();
        $balanceModel = new Balance_Model();

        try {
            $db->beginTransaction();
            $res = $orderModel->updateOrderPayStatus($order['id'], [
                'pay_status' => 1,
                'status' => 2,
                'service_status' => 1
            ]);
            if($res === false){
                $db->rollback();
                return true;
            }
            $orderModel->updateOrderInfo($out_trade_no, [
                'pay_time' => $time,
                'up_no' => $up_no,
                'payment' => $order['payment']
            ]);
            $balanceModel->inc(intval($order['user_id']), $order['amount'] / 100, '余额在线充值');
            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollback();
            Log::error('余额充值入账失败：' . $e->getMessage());
            return false;
        }
    }

    /**
     * 主动查询网关支付状态（支持所有易支付协议的插件）
     * 用于解决网关侧补单不触发异步回调的问题
     * @return array|false 成功返回 ['trade_no' => '...']，未支付或查询失败返回 false
     */
    private function queryGatewayPayStatus($order) {
        $plugin = $order['pay_plugin'];
        $out_trade_no = $order['out_trade_no'];

        // 标准易支付插件：epay_wx, epay_ali, epay_ali2, epay_qq
        $standardEpay = ['epay_wx', 'epay_ali', 'epay_ali2', 'epay_qq'];
        if (in_array($plugin, $standardEpay)) {
            $storage = Storage::getInstance($plugin);
            $gatewayUrl = rtrim($storage->getValue('url'), '/') . '/api.php';
            $pid = $storage->getValue('appid');
            $key = $storage->getValue('private_key');
            return $this->queryEpayOrder($gatewayUrl, $pid, $key, $out_trade_no);
        }

        // 码支付插件：ynl_wx, ynl_ali
        $ynlPlugins = ['ynl_wx', 'ynl_ali'];
        if (in_array($plugin, $ynlPlugins)) {
            $storage = Storage::getInstance($plugin);
            $gatewayUrl = 'https://pay.1kexiu.xyz/api.php';
            $pid = $storage->getValue('pid');
            $key = $storage->getValue('key');
            return $this->queryEpayOrder($gatewayUrl, $pid, $key, $out_trade_no);
        }

        // 七相支付：qixiangpay_wx, qixiangpay_ali
        $qixiangpayPlugins = ['qixiangpay_wx', 'qixiangpay_ali'];
        if (in_array($plugin, $qixiangpayPlugins)) {
            $storage = Storage::getInstance('qixiangpay');
            $gatewayUrl = $this->normalizeGatewayUrl($storage->getValue('api_gateway') ?: 'https://api.payqixiang.cn/api.php', 'api.php');
            $pid = $storage->getValue('pid');
            $key = $storage->getValue('key');
            return $this->queryEpayOrder($gatewayUrl, $pid, $key, $out_trade_no);
        }

        // 多通道易支付插件：epay_jj, epay_jj2
        $jjPlugins = ['epay_jj', 'epay_jj2'];
        if (in_array($plugin, $jjPlugins)) {
            $getChannelsFunc = "get{$plugin}Channels";
            if (function_exists($getChannelsFunc)) {
                $channels = $getChannelsFunc();
                foreach ($channels as $ch) {
                    if (($ch['enabled'] ?? '1') !== '1') continue;
                    $gatewayUrl = rtrim($ch['url'], '/') . '/api.php';
                    $result = $this->queryEpayOrder($gatewayUrl, $ch['appid'], $ch['private_key'], $out_trade_no);
                    if ($result) return $result;
                }
            }
            return false;
        }

        return false;
    }

    /**
     * 通过易支付标准 API 查询订单状态
     */
    private function normalizeGatewayUrl($url, $defaultFile) {
        $url = trim((string)$url);
        if ($url === '') {
            return '';
        }
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }
        if (preg_match('#\.php(?:\?.*)?$#i', $url)) {
            return $url;
        }
        return rtrim($url, '/') . '/' . $defaultFile;
    }

    /**
     * 通过易支付标准 API 查询订单状态
     */
    private function queryEpayOrder($gatewayUrl, $pid, $key, $out_trade_no) {
        $queryUrl = $gatewayUrl . '?' . http_build_query([
            'act' => 'order',
            'pid' => $pid,
            'key' => $key,
            'out_trade_no' => $out_trade_no,
        ]);

        // 优先使用 cURL，fallback 到 file_get_contents
        $response = false;
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $queryUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);
            $response = curl_exec($ch);
            curl_close($ch);
        } else {
            $ctx = stream_context_create(['http' => ['timeout' => 5, 'method' => 'GET'], 'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
            $response = @file_get_contents($queryUrl, false, $ctx);
        }

        if (!$response) return false;

        $data = @json_decode($response, true);
        if (!$data || !isset($data['code'])) return false;

        // 兼容两种 API 格式：
        // 标准易支付：trade_status = 'TRADE_SUCCESS'
        // 码支付(xzsc.cc)：status = '1'
        $isPaid = false;
        if (isset($data['trade_status']) && $data['trade_status'] === 'TRADE_SUCCESS') {
            $isPaid = true;
        } elseif ($data['code'] == 1 && isset($data['status']) && $data['status'] == '1') {
            $isPaid = true;
        }

        if ($isPaid) {
            return ['trade_no' => $data['trade_no'] ?? ''];
        }
        return false;
    }

}
