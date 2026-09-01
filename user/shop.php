<?php



require_once '../init.php';

$action = Input::getStrVar('action');

// 获取商品详情（单页购买模式用）
if($action == 'get_goods_detail'){
    // 尝试多种方式获取 goods_id
    $goods_id = 0;
    
    // 方式1: 标准 POST
    if(isset($_POST['goods_id'])){
        $goods_id = intval($_POST['goods_id']);
    }
    
    // 方式2: Input 类
    if($goods_id <= 0){
        $goods_id = Input::postIntVar('goods_id');
    }
    
    // 方式3: GET 参数
    if($goods_id <= 0 && isset($_GET['goods_id'])){
        $goods_id = intval($_GET['goods_id']);
    }
    
    if($goods_id <= 0){
        Ret::error('商品ID无效');
    }
    
    Api::local_init();
    $res = Api::getGoodsInfo(['goods_id' => $goods_id]);
    if (defined('ISLOGIN') && ISLOGIN && !empty($res)) {
        try {
            global $stationData;
            $footprintModel = new User_Goods_Footprint_Model();
            $footprintModel->record((int)UID, (int)$goods_id, (int)($stationData['id'] ?? 0));
        } catch (Throwable $e) {
            error_log('Record goods footprint failed: ' . $e->getMessage());
        }
    }
    Ret::success('', $res);
}

if($action == 'goods_price_stock'){
    $goods_id = Input::postIntVar('goods_id');
    $sku_ids = Input::postIntArray('sku_ids');
    $sku_ids = implode('-', $sku_ids);
    $quantity = Input::postIntVar('quantity');
    $quantity = $quantity <= 0 ? 1 : $quantity;
    $quantity = (int)ceil($quantity);
    Api::local_init();
    $res = Api::getPriceStock([
        'goods_id' => $goods_id,
        'skus' => $sku_ids,
        'quantity' => $quantity,
    ]);
    Ret::success('', $res);
}

if($action == 'xiadan'){
    // 强制登录插件检查（下单时强制登录）
    // 只有当插件启用时才检查
    if (function_exists('compel_login_xiadan')) {
        $compel_storage = Storage::getInstance('compel_login');
        $compel_type = $compel_storage->getValue('type');
        // type=2 表示下单时强制登录
        if ($compel_type !== false && $compel_type == 2 && ISLOGIN === false) {
            $compel_login_url = $compel_storage->getValue('login_url') ?: '';
            if (empty($compel_login_url)) {
                $compel_login_url = DC_URL . 'user/account.php?action=signin';
            }
            // 登录后跳回：将来源商品页作为 redirect 参数
            $compel_remember = $compel_storage->getValue('remember_url') ?: 'y';
            if ($compel_remember == 'y' && !empty($_SERVER['HTTP_REFERER'])) {
                $sep = strpos($compel_login_url, '?') !== false ? '&' : '?';
                $compel_login_url .= $sep . 'redirect=' . urlencode($_SERVER['HTTP_REFERER']);
            }
            // 返回 401 + login_url，前端据此跳转登录页
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode([
                'code' => 401,
                'msg'  => '请先登录后再下单',
                'login_url' => $compel_login_url
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
    }
    
    $goods_id = Input::postIntVar('goods_id');
    $sku_ids = Input::postIntArray('sku_ids');
    $sku_ids = implode('-', $sku_ids);
    $quantity = Input::postIntVar('quantity');
    $quantity = $quantity <= 0 ? 1 : $quantity;
    $quantity = (int)ceil($quantity);
    $payment_plugin = Input::postStrVar('payment_plugin');
    $payment_title = Input::postStrVar('payment_title');
    $attach = Input::postStrArray('attach');
    $required = Input::postStrArray('required');
    $coupon_code = Input::postStrVar('coupon_code'); // 获取优惠券码

    Api::local_init();
    $res = Api::xiadan([
        'goods_id' => $goods_id,
        'sku_ids' => $sku_ids,
        'quantity' => $quantity,
        'payment_plugin' => $payment_plugin,
        'payment_title' => $payment_title,
        'attach' => $attach,
        'required' => $required,
        'coupon_code' => $coupon_code, // 传递优惠券码
    ]);


    if($res['code'] == 200){
        Ret::success('ok', $res['data']);
    }else{
        Ret::error($res['msg']);
    }


}