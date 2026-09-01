<?php
/**
 * Service: Api
 */

class Api {

    private static $resp = '';
    private static $post = [];

    private static $db = null;
    private static $db_prefix = null;

    private static $user_id = 0;
    private static $user = [];

    private static $timestamp = null;

    public static function init(){
        self::$db = Database::getInstance();
        self::$db_prefix = DB_PREFIX;
        self::$timestamp = time();
    }

    public static function api_init(){
        self::init();
        self::$resp = 'api';
    }

    public static function local_init(){
        self::init();
        self::$resp = 'local';
        self::$user_id = UID;
        $db_prefix = self::$db_prefix;
        if(self::$user_id > 0){
            $sql = "select * from {$db_prefix}user where uid=" . UID . " limit 1";
            self::$user = self::$db->once_fetch_array($sql);
        }

    }

    /**
     * 商品下单
     */
    public static function xiadan($post){
        // 演示站也需要体验完整购买流程，这里不再拦截下单。
        // 高风险操作仍由 init.php/admin|user globals 的演示站保护策略统一限制。
//        Ret::error('测试');
        $goods_id = $post['goods_id'];

        $sku_ids = empty($post['sku_ids']) ? '0' : $post['sku_ids'];
        $quantity = $post['quantity'];
        $payment_plugin = $post['payment_plugin'];
        $payment_title = $post['payment_title'];

        $attach = $post['attach'];
        $required = $post['required'];

        // 判断是否为对接商品且拥有同步的 attach_user
        $db = Database::getInstance();
        $db_prefix = DB_PREFIX;
        $_isDockingGoods = false;
        $_hasDockingGoodsMapping = function($table, $goodsId) use ($db, $db_prefix) {
            $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $db_prefix . $table);
            if ($tableName === '') return false;
            $tableRow = $db->once_fetch_array("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$tableName}' LIMIT 1");
            if (empty($tableRow)) return false;
            return (bool)$db->once_fetch_array("SELECT id FROM `{$tableName}` WHERE goods_id = " . (int)$goodsId . " LIMIT 1");
        };
        $_dockingGoodsRow = $_hasDockingGoodsMapping('docking_goods', $goods_id);
        $_qingjiuGoodsRow = $_hasDockingGoodsMapping('qingjiu_goods', $goods_id);
        $_xiaoqingGoodsRow = $_hasDockingGoodsMapping('xiaoqing_goods', $goods_id);
        $_yiciyuanGoodsRow = $_hasDockingGoodsMapping('yiciyuan_goods', $goods_id);
        if ($_dockingGoodsRow || $_qingjiuGoodsRow || $_xiaoqingGoodsRow || $_yiciyuanGoodsRow) {
            $_isDockingGoods = true;
        }

        // 获取商品的 attach_user 配置，用于格式验证
        $_goodsRow = $db->once_fetch_array("SELECT type, attach_user FROM {$db_prefix}goods WHERE id = " . (int)$goods_id . " LIMIT 1");
        $_isPhysicalGoods = ($_goodsRow && (string)($_goodsRow['type'] ?? '') === 'physical');
        $_physicalOptionalRemarkNames = ['买家备注', '备注'];
        $_attachUserConfig = [];
        if ($_goodsRow && !empty($_goodsRow['attach_user'])) {
            $_attachUserConfig = json_decode($_goodsRow['attach_user'], true) ?: [];
            // 兼容旧格式
            if (!empty($_attachUserConfig) && !isset($_attachUserConfig[0])) {
                $converted = [];
                foreach ($_attachUserConfig as $k => $v) {
                    $converted[] = ['name' => $k, 'placeholder' => $v, 'type' => 'string', 'required' => true, 'tip' => ''];
                }
                $_attachUserConfig = $converted;
            }
        }
        // 构建字段名→类型/必填映射
        $_fieldTypeMap = [];
        $_fieldRequiredMap = [];
        foreach ($_attachUserConfig as $_auCfg) {
            if (!empty($_auCfg['name'])) {
                $_fieldName = (string)$_auCfg['name'];
                $_fieldTypeMap[$_fieldName] = $_auCfg['type'] ?? 'string';
                $_fieldRequiredMap[$_fieldName] = isset($_auCfg['required']) ? (bool)$_auCfg['required'] : true;
                if ($_isPhysicalGoods && in_array($_fieldName, $_physicalOptionalRemarkNames, true)) {
                    $_fieldRequiredMap[$_fieldName] = false;
                }
            }
        }

        foreach($attach as $key => $val){
            $fieldRequired = $_fieldRequiredMap[$key] ?? true;
            if($_isPhysicalGoods && in_array((string)$key, $_physicalOptionalRemarkNames, true)) $fieldRequired = false;
            if($fieldRequired && empty($val)) Ret::error('请填写' . $key);
            if(!$fieldRequired && empty($val)) continue;
            // 格式验证：邮箱、手机、数字
            $fieldType = $_fieldTypeMap[$key] ?? 'string';
            if ($fieldType === 'email' && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
                Ret::error('请输入正确的邮箱格式：' . $key);
            }
            if ($fieldType === 'tel' && !preg_match('/^1[3-9]\d{9}$/', $val)) {
                Ret::error('请输入正确的手机号码：' . $key);
            }
            if ($fieldType === 'num' && !is_numeric($val)) {
                Ret::error('请输入正确的数字：' . $key);
            }
        }

        // 对接商品且拥有同步的输入框时，跳过全局 order_required 验证
        if ($_isDockingGoods && !empty($_attachUserConfig)) {
            $required = []; // 清空全局必填项，避免重复验证
        }
        foreach($required as $key => $val){
            if($_isPhysicalGoods && in_array((string)$key, $_physicalOptionalRemarkNames, true)) continue;
            if(empty($val)) Ret::error('请填写' . $key);
        }



        $paymentListForEmpty = [];
        if(empty($payment_plugin)){
            $paymentListForEmpty = getPayment();
            if (empty($paymentListForEmpty)) {
                Ret::error(function_exists('dcPaymentUnavailableMessage') ? dcPaymentUnavailableMessage($payment_plugin) : '后台支付插件未配置完整，请联系管理员');
            }
            Ret::error('请选择支付方式');
        }

        if(self::$resp == 'local' && $payment_plugin == 'balance' && !ISLOGIN){
            Ret::error('未登录用户无法使用余额支付');
        }
        $paymentList = !empty($paymentListForEmpty) ? $paymentListForEmpty : getPayment();

        $selectedPayment = [];
        foreach ($paymentList as $paymentItem) {
            if (($paymentItem['plugin_name'] ?? '') === $payment_plugin) {
                $selectedPayment = $paymentItem;
                break;
            }
        }
        if (empty($selectedPayment)) {
            Ret::error(function_exists('dcPaymentUnavailableMessage') ? dcPaymentUnavailableMessage($payment_plugin) : '后台支付插件未配置完整，请联系管理员');
        }
        if (empty($payment_title)) {
            $payment_title = $selectedPayment['title'] ?? ($selectedPayment['name'] ?? $payment_plugin);
        }

        $stock = self::getStock([
            'goods_id' => $goods_id,
            'sku_ids' => $sku_ids
        ]);
        if($stock == -999){
            if(empty($sku_ids)){
                Ret::error('请选择商品规格');
            }else{
                Ret::error('请完整选择商品规格');
            }
        }
        if($quantity > $stock){
            if($stock == 0){
                Ret::error('该商品已售罄');
            }else{
                Ret::error('该商品库存不足，剩余库存：' . $stock);
            }

        }
        $amount = self::getGoodsPrice([
            'skus' => $sku_ids,
            'goods_id' => $goods_id,
            'quantity' => $quantity
        ]);
        
        // 处理优惠券
        $coupon_code = $post['coupon_code'] ?? '';
        $coupon_discount = 0;
        $coupon_id = 0;
        if (!empty($coupon_code)) {
            // 加载优惠券插件
            $coupon_plugin_file = DC_ROOT . 'content/plugins/coupon/coupon.php';
            if (file_exists($coupon_plugin_file)) {
                include_once $coupon_plugin_file;
            }
            if (function_exists('coupon_validate')) {
                $coupon_result = coupon_validate($coupon_code, $goods_id, self::$user_id, $amount['count_price']);
                if ($coupon_result['code'] == 200) {
                    $coupon_discount = floatval($coupon_result['data']['discount']);
                    $coupon_id = $coupon_result['data']['coupon_id'];
                }
            }
        }
        
        // 计算优惠后的金额
        $final_price = $amount['count_price'] - $coupon_discount;
        if ($final_price < 0.01) {
            // 免费商品插件允许 0 元下单
            if (function_exists('free_claim_is_free_goods') && free_claim_is_free_goods($goods_id)) {
                $final_price = 0;
            } else {
                $final_price = 0.01; // 最低0.01元
            }
        }
        if (function_exists('dcPaymentPluginReady') && !dcPaymentPluginReady($payment_plugin, $final_price)) {
            Ret::error(function_exists('dcPaymentUnavailableMessage') ? dcPaymentUnavailableMessage($payment_plugin) : '后台支付插件未配置完整，请联系管理员');
        }
        
        // 触发下单前钩子（用于限制交易金额等插件）
        $order_list_data = [[
            'goods_id' => $goods_id,
            'sku' => $sku_ids,
            'quantity' => $quantity,
            'price' => $amount['count_price'] * 100,
            'unit_price' => $amount['unit_price'] * 100
        ]];
        doAction('before_submitting_the_payment', $order_list_data);
        
//        d($amount);die;
        // 验证通过后，开始写入订单

//        var_dump($sku_ids);die;
        if(empty($sku_ids)){
            $attr_spec = '';
        }else{
            $orderModel = new Order_Model();
            $specification_value_data = $orderModel->getSpecificationValue($sku_ids);
            $specification_value = array_column($specification_value_data, 'name');

            $specification_ids = array_column($specification_value_data, 'attr_id');
            $specification_data = $orderModel->getSpecification($specification_ids);
            $specification = array_column($specification_data, 'title');
            $attr_spec = '';
            foreach($specification as $k => $v){
                $attr_spec .= $v . '：' . $specification_value[$k] . '；';
            }
        }
        $goodsInfo = self::$db->once_fetch_array("select title from " . self::$db_prefix . "goods where id={$goods_id} limit 1");

        try {
            self::$db->beginTransaction();

            global $stationData;
//            d($stationData);die;

            $insert_order = [
                'station_id' => $stationData['id'],
                'client_ip' => getClientIP(),
                'user_id' => self::$user_id,
                'out_trade_no' => date('YmdHis', self::$timestamp) . mt_rand(1000, 9999),
                'amount' => $final_price * 100, // 使用优惠后的金额
                'payment' => $payment_title,
                'pay_name' => $payment_title,
                'pay_plugin' => $payment_plugin,
                'expire_time' => self::$timestamp + max(1, intval(Option::get('continue_pay_timeout') ?: 30)) * 60,
                'create_time' => self::$timestamp
            ];
            
            // 如果有优惠券，尝试添加优惠券字段（字段可能不存在）
            if (!empty($coupon_code) && $coupon_discount > 0) {
                // 检查字段是否存在
                $db_prefix = self::$db_prefix;
                $columns = self::$db->query("SHOW COLUMNS FROM `{$db_prefix}order` LIKE 'coupon_code'");
                if ($columns && $columns->num_rows > 0) {
                    $insert_order['coupon_code'] = $coupon_code;
                    $insert_order['coupon_discount'] = $coupon_discount * 100;
                }
            }
            self::$db->add('order', $insert_order);
            $order_id = self::$db->insert_id();
            User_Log_Model::log(self::$user_id, 'order_create', '创建商品订单，订单号: ' . $insert_order['out_trade_no'] . '，商品: ' . ($goodsInfo['title'] ?? ('ID:' . $goods_id)) . '，金额: ¥' . number_format($final_price, 2), $final_price);

//            var_dump($attr_spec);die;

            $insert_child_order = [
                'order_id' => $order_id,
                'goods_id' => $goods_id,
                'sku' => empty($sku_ids) ? '' : $sku_ids,
                'attr_spec' => $attr_spec,
                'attach_user' => json_encode($attach, JSON_UNESCAPED_UNICODE),
                'quantity' => $quantity,
                'unit_price' => $amount['unit_price'] * 100,
                'price' => $amount['count_price'] * 100,
                'cost_price' => $amount['cost_price'] * 100
            ];

//            d($insert_child_order);die;
            self::$db->add('order_list', $insert_child_order);

            foreach($required as $key => $val){
                $insert_required = [
                    'order_id' => $order_id,
                    'name' => $key,
                    'content' => $val,
                    'type' => isEmail($val) ? 'email' : 'string'
                ];
                self::$db->add('order_required', $insert_required);
            }
            
            // 记录优惠券使用日志
            if (!empty($coupon_code) && $coupon_discount > 0 && $coupon_id > 0) {
                $db_prefix = self::$db_prefix;
                // 更新优惠券使用次数
                self::$db->query("UPDATE `{$db_prefix}coupon` SET used_count = used_count + 1 WHERE id = {$coupon_id}");
                // 插入使用记录
                $coupon_log = [
                    'coupon_id' => $coupon_id,
                    'user_id' => self::$user_id,
                    'order_id' => $order_id,
                    'order_no' => $insert_order['out_trade_no'],
                    'discount_amount' => $coupon_discount,
                    'client_ip' => getClientIP(),
                    'create_time' => self::$timestamp
                ];
                self::$db->add('coupon_log', $coupon_log);
            }

            self::$db->commit();
        } catch (Exception $e) {
            self::$db->rollback();
            Ret::error($e->getMessage());
        }
        
        // 触发订单创建钩子
        doAction('order_created', $order_id, $insert_order);
        
        return self::success([
            'msg' => 'ok',
            'data' => [
                'out_trade_no' => $insert_order['out_trade_no']
            ],
            'code' => 200
        ]);
    }


    /**
     * 获取价格与库存
     */
    public static function getPriceStock($post){
        $sku = $post['skus'];
        $goods_id = $post['goods_id'];
        $quantity = $post['quantity'];
        $db_prefix = self::$db_prefix;
        $sql = "select * from {$db_prefix}skus where goods_id={$goods_id}";
        $sku = explode('-', $sku);
        foreach($sku as $val){
            $sql .= " and find_in_set('{$val}', REPLACE(sku, '-', ',')) > 0 ";
        }
        $sql .= " order by user_price asc";
        $skus = self::$db->fetch_all($sql);
        $select_sku = empty($skus) ? false : true;

        $goods = self::$db->once_fetch_array("select * from {$db_prefix}goods where id={$goods_id}");

        if($goods['is_sku'] == 'n'){
            $skus = self::$db->fetch_all("select * from {$db_prefix}skus where goods_id={$goods_id} and sku='0'");
        }

        // 获取分店信息
        global $stationData;
        
        // 获取商品的分店加价信息
        $premium = 0.10; // 默认加价10%
        $isMasterGoods = isset($goods['station_id']) && $goods['station_id'] == 0; // 判断是否为主站商品
        
        // 如果是分店环境且商品来自主站，则获取加价比例
        if($stationData['id'] != 0 && $isMasterGoods) {
            $premiumSql = "SELECT premium FROM {$db_prefix}station_goods WHERE goods_id={$goods_id} AND station_id={$stationData['id']} LIMIT 1";
            $premiumData = self::$db->once_fetch_array($premiumSql);
            if($premiumData && isset($premiumData['premium'])){
                $premium = (float)$premiumData['premium'];
            }
        }
        $stationPremium = ($stationData['id'] != 0 && $isMasterGoods) ? $premium : 0.0;

        $min_price = 0;
        $max_price = 0;
        $stock = 0;
        $level = self::$user_id > 0 ? (int)LEVEL : -1;
        $isFreeClaimGoods = function_exists('free_claim_is_free_goods') && free_claim_is_free_goods($goods_id);
        foreach($skus as $key => $val){
            $stock += $val['stock'];
            $unit_price = $isFreeClaimGoods ? 0 : Level_Price::calculate($val, $goods, $level, $stationPremium);
            if($key == 0){
                $min_price = $unit_price;
            }
            $max_price = $unit_price;
        }
        $is_full = count($skus) == 1 ? 'y' : 'n'; // 规格是否选择完整
        $is_select_sku = empty($skus) ? 'n' : 'y'; // 是否选择规格

        // 获取当前选择的SKU字符串（用于查询优惠）
        // 如果规格选择完整，使用实际的SKU值；否则用传入的sku参数
        $sku_str = '';
        if($is_full == 'y' && !empty($skus)){
            $sku_str = $skus[0]['sku']; // 使用实际匹配到的SKU
        } else {
            $sku_str = implode('-', $sku); // 使用传入的规格ID组合
        }
        
        // 获取优惠信息（三类：每件优惠 / 订单优惠 / 订单折扣）
        $discount = Goods_Discount::fetchRules($goods_id, $sku_str);
        $best = Goods_Discount::pickBest($discount, $quantity);

        // 检查当前选择的规格是否有优惠设置（用于显示"批发优惠"标识）
        $has_current_sku_discount = !empty($discount);

        // 应用三类优惠计算最终价格（min_price 为单价，分）
        $applied_min = Goods_Discount::apply((int)$min_price, (int)$quantity, $best);
        $applied_max = Goods_Discount::apply((int)$max_price, (int)$quantity, $best);

        $dis_per_unit = (int)$best['item_cents']; // 每件优惠（分）

        $sql = "select * from {$db_prefix}skus where goods_id={$goods_id}";
        $all_skus = self::$db->fetch_all($sql);
        
        // 获取当前选中SKU的详情内容
        $sku_content = '';
        if($is_full == 'y' && !empty($skus) && isset($skus[0]['content'])){
            $sku_content = $skus[0]['content'];
        }

        // 获取当前选中SKU的划线价
        $sku_market_price = 0;
        if(!empty($skus)){
            $sku_market_price = (int)($skus[0]['market_price'] ?? 0);
        }

        return [
            'min_price' => bcdiv($applied_min['count_price'], 100, 2),
            'max_price' => bcdiv($applied_max['count_price'], 100, 2),
            'is_full' => $is_full,
            'is_select_sku' => $is_select_sku,
            'price' => bcdiv($applied_min['count_price'], 100, 2),
            'unit_price' => bcdiv($min_price, 100, 2), // 原始单价（未优惠）
            'discount' => bcdiv($applied_min['discount_total'], 100, 2), // 总优惠金额
            'discount_per_unit' => bcdiv($dis_per_unit, 100, 2), // 每件优惠金额（仅 type=1）
            'discount_order' => bcdiv((int)$best['order_cents'], 100, 2), // 订单优惠（元）
            'discount_percent' => (float)$best['percent'], // 订单折扣（0-100，100=不打折）
            'has_discount' => $has_current_sku_discount, // 当前规格是否有批发优惠
            'discount_list' => $discount, // 优惠阶梯列表（含三类）
            'sku_content' => $sku_content, // 当前SKU的独立详情
            'market_price' => bcdiv($sku_market_price, 100, 2), // 划线价（元）
            'stock' => empty($select_sku) ? $goods['stock'] : $stock,
            'skus' => $all_skus
        ];
    }
    public static function getGoodsPrice($post){
        $sku = $post['skus'];
        $goods_id = $post['goods_id'];
        $quantity = $post['quantity'];
        $db_prefix = self::$db_prefix;
        $sql = "select * from {$db_prefix}skus where goods_id={$goods_id} and sku='{$sku}' limit 1";
        $skus = self::$db->once_fetch_array($sql);

        // 免费商品插件覆盖：跳过所有定价逻辑，直接返回 0
        if (function_exists('free_claim_is_free_goods') && free_claim_is_free_goods($goods_id)) {
            return self::success(['count_price' => 0, 'unit_price' => 0, 'cost_price' => ($skus['cost_price'] ?? 0) / 100]);
        }

        // 获取分店信息
        global $stationData;

        // 获取商品信息（包含加价规则字段）
        $goodsData = self::$db->once_fetch_array("SELECT * FROM {$db_prefix}goods WHERE id={$goods_id}");
        $isMasterGoods = isset($goodsData['station_id']) && $goodsData['station_id'] == 0;

        // 分店加价比例（小数）
        $premium = 0.10;
        if($stationData['id'] != 0 && $isMasterGoods) {
            $premiumData = self::$db->once_fetch_array("SELECT premium FROM {$db_prefix}station_goods WHERE goods_id={$goods_id} AND station_id={$stationData['id']} LIMIT 1");
            if($premiumData && isset($premiumData['premium'])){
                $premium = (float)$premiumData['premium'];
            }
        }
        $stationPremium = ($stationData['id'] != 0 && $isMasterGoods) ? $premium : 0.0;

        // 五层价格计算（会员等级价 / 加价规则 / 加价比例 / 用户价 / 游客价）
        $level = self::$user_id > 0 ? (int)LEVEL : -1;
        $base = Level_Price::calculate($skus, $goodsData, $level, $stationPremium);

        // 获取三类优惠（每件优惠 / 订单优惠 / 订单折扣）
        $rules = Goods_Discount::fetchRules($goods_id, $sku);
        $best = Goods_Discount::pickBest($rules, $quantity);
        $applied = Goods_Discount::apply((int)$base, (int)$quantity, $best);

        $unit_price = $applied['unit_price_after_item'] / 100; // 仅扣除每件优惠后的单价
        $count_price = $applied['count_price'] / 100;          // 三类优惠全部应用后的订单总价

        $data = [
            'count_price' => $count_price,
            'unit_price' => $unit_price,
            'cost_price' => $skus['cost_price'] / 100
        ];

        return self::success($data);
    }

    /**
     * 获取商品库存
     */
    public static function getStock($post){
        $db_prefix = self::$db_prefix;
        $goods_id = $post['goods_id'];
        $sku = $post['sku_ids'];
        $sql = "select * from {$db_prefix}skus where goods_id={$goods_id} and sku='{$sku}' limit 1";
        $skus = self::$db->once_fetch_array($sql);
        if(empty($skus)){
            return self::success(-999);
        }else{
            return self::success($skus['stock']);
        }
    }

    /**
     * 获取商品批量优惠信息
     */
    public static function getGoodsDiscount($post){
        $db_prefix = self::$db_prefix;
        $goods_id = $post['goods_id'];
        if (class_exists('Goods_Discount')) {
            Goods_Discount::ensureSchema();
        }
        $sql = "select * from {$db_prefix}discount where goods_id={$goods_id} order by type asc, quantity asc";
        $list = self::$db->fetch_all($sql);
        foreach($list as &$val){
            $t = isset($val['type']) ? (int)$val['type'] : 1;
            if ($t == 3) {
                // 订单折扣：DB 存的是 0-100（百分比），展示为折数（9.5 折 等）
                $val['amount'] = round($val['amount'] / 10, 2);
            } else {
                // 每件优惠 / 订单优惠：DB 存的是分，展示为元
                $val['amount'] = round($val['amount'] / 100, 2);
            }
        }
        return self::success($list);


    }

    /**
     * 获取商品分类
     */
    public static function getSortAll() {
        $sortModel = new Sort_Model();
        $res = $sortModel->getHomeAllGoodsSort();
        return self::success($res);
//        d($res);
//        die('ok');

//        $db_prefix = self::$db_prefix;

//        global $stationData;
//        d($stationData);die;

        $field = "s.sid, s.sortname, COUNT(g.id) AS goods_count, s.sortimg";

        if($stationData['id'] == 0){
            $sql = "select {$field}
                from {$db_prefix}sort s
                LEFT JOIN {$db_prefix}goods g ON s.sid = g.sort_id and g.delete_time is null and g.is_on_shelf = 1 
                where s.type='goods' and station_id=0
                GROUP BY s.sid 
                order by s.taxis desc, sid asc";
        }else{
            if($stationData['master_sort'] == 1){

                $sql = "select {$field}
                from {$db_prefix}sort s
                LEFT JOIN {$db_prefix}goods g ON s.sid = g.sort_id and g.delete_time is null and g.is_on_shelf = 1 
                where s.type='goods' and (station_id=0 or station_id={$stationData['id']})
                GROUP BY s.sid 
                order by s.taxis desc, sid asc";
            }
            if($stationData['master_sort'] == 2){
                $sql = "select {$field}
                from {$db_prefix}sort s
                LEFT JOIN {$db_prefix}goods g ON s.sid = g.sort_id and g.delete_time is null and g.is_on_shelf = 1 
                where s.type='goods' and station_id={$stationData['id']}
                GROUP BY s.sid 
                order by s.taxis desc, sid asc";
            }
            if($stationData['master_sort'] == 3){
                $sql = "select {$field}
                from {$db_prefix}sort s
                LEFT JOIN {$db_prefix}goods g ON s.sid = g.sort_id and g.delete_time is null and g.is_on_shelf = 1 
                where s.type='goods' and station=0
                GROUP BY s.sid 
                order by s.taxis desc, sid asc";
            }

        }
//echo $sql;die;


        $list = self::$db->fetch_all($sql);



        foreach($list as &$val){
            $val['sort_url'] = Url::sort($val['sid']);
        }
        return self::success($list);
    }

    /**
     * 获取商品信息
     * @post.goods_id 商品ID
     */
    public static function getGoodsInfo($post){
        $db_prefix = DB_PREFIX;
        $db = Database::getInstance();

        $goods_id = $post['goods_id'];

        $sql = "select * from {$db_prefix}goods where id={$goods_id} limit 1";
        $goods = $db->once_fetch_array($sql);

        if($goods['is_on_shelf'] == 0){
            self::error('该商品已下架！');
        }

        // 解析商品图集（cover + gallery JSON 合并，cover 一定在首位）
        $goods['gallery'] = class_exists('Goods_Model') ? Goods_Model::parseGallery($goods) : (empty($goods['cover']) ? [] : [$goods['cover']]);

        $skuOrderSql = class_exists('Goods_Model') ? Goods_Model::getSkuOrderSql('sku') : 'sku.sku ASC';
        $sql = "select * from {$db_prefix}skus sku where goods_id={$goods_id} order by {$skuOrderSql}";
        $skus = $db->fetch_all($sql);
        $sql ="select * from {$db_prefix}member_price where goods_id={$goods_id}";
        $member_price = $db->fetch_all($sql);

        $goods['input'] = [];

        $goods['attach_user'] = json_decode($goods['attach_user'], true);
        // 兼容旧格式 {"key":"value"} → 新格式 [{name,placeholder,type,required,tip}]
        if (!empty($goods['attach_user']) && !isset($goods['attach_user'][0])) {
            $converted = [];
            foreach ($goods['attach_user'] as $k => $v) {
                $converted[] = ['name' => $k, 'placeholder' => $v, 'type' => 'string', 'required' => true, 'tip' => ''];
            }
            $goods['attach_user'] = $converted;
        }

        if (!empty($goods['attach_user'])) {
            foreach($goods['attach_user'] as $item){
                $goods['input']['attach'][] = [
                    'name' => $item['name'],
                    'placeholder' => $item['placeholder'] ?? '',
                    'type' => $item['type'] ?? 'string',
                    'required' => $item['required'] ?? true,
                    'tip' => $item['tip'] ?? '',
                ];
            }
        }

        // 对接商品且已有同步的 attach_user 输入框时，不加载本站全局 order_required
        // 避免对接商品重复显示输入框，同时不影响本站自有商品
        $_isDockingProduct = false;
        $_hasDockingProductMapping = function($table, $goodsId) use ($db, $db_prefix) {
            $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $db_prefix . $table);
            if ($tableName === '') return false;
            $tableRow = $db->once_fetch_array("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$tableName}' LIMIT 1");
            if (empty($tableRow)) return false;
            return (bool)$db->once_fetch_array("SELECT id FROM `{$tableName}` WHERE goods_id = " . (int)$goodsId . " LIMIT 1");
        };
        $_dockRow = $_hasDockingProductMapping('docking_goods', $goods_id);
        $_qingjiuRow = $_hasDockingProductMapping('qingjiu_goods', $goods_id);
        $_xiaoqingRow = $_hasDockingProductMapping('xiaoqing_goods', $goods_id);
        $_yiciyuanRow = $_hasDockingProductMapping('yiciyuan_goods', $goods_id);
        if ($_dockRow || $_qingjiuRow || $_xiaoqingRow || $_yiciyuanRow) {
            $_isDockingProduct = true;
        }
        if (($_isDockingProduct && !empty($goods['attach_user'])) || (($goods['type'] ?? '') === 'physical')) {
            // 对接商品有同步输入框、实物商品有独立收货模型时，跳过全局 order_required
            $goods['input']['required'] = [];
        } else {
            $order_required = Option::get('order_required');
            $goods['input']['required'] = json_decode($order_required, true) ?: [];
        }

        // 获取分店信息
        global $stationData;
        
        // 分店加价比例（小数）
        $premium = 0.10;
        $isMasterGoods = isset($goods['station_id']) && $goods['station_id'] == 0;
        
        if($stationData['id'] != 0 && $isMasterGoods) {
            $sid = (int)$stationData['id'];
            $premiumSql = "SELECT premium, custom_name FROM {$db_prefix}station_goods WHERE goods_id={$goods_id} AND station_id={$sid} LIMIT 1";
            $premiumData = $db->once_fetch_array($premiumSql);
            if($premiumData && isset($premiumData['premium'])){
                $premium = (float)$premiumData['premium'];
            }
            if(!empty($premiumData['custom_name'])){
                $goods['title'] = $premiumData['custom_name'];
            }
        }
        $stationPremium = ($stationData['id'] != 0 && $isMasterGoods) ? $premium : 0.0;

        if($goods['is_sku'] == 'y'){
            $sku_value_ids = [];
            $sku_attr_ids = [];
            $goods['have_stock_skus'] = [];
            $goods['sku_all'] = [];
            // 免费商品插件：覆盖所有 SKU 价格为 0
            $isFreeClaimGoods = function_exists('free_claim_is_free_goods') && free_claim_is_free_goods($goods_id);

            foreach($skus as $key => $val){

                $ids = explode('-', $val['sku']);
                $sku_value_ids = array_merge($sku_value_ids, $ids);

                // 五层价格计算（分站加价已整合在 Level_Price::calculate 内部）
                $base = $isFreeClaimGoods ? 0 : Level_Price::calculate($val, $goods, (int)LEVEL, $stationPremium);
                $price = $base / 100;

                $val['price'] = $price;

                $goods['sku_all'][$val['sku']] = $val;
                $goods['sku_all'][$val['sku']]['stock'] = $val['stock'];
                if($val['stock'] > 0){
                    $goods['have_stock_skus'][$val['sku']] = $val;
                }

                if(!isset($goods['price']) && $price != -1){
                    $goods['price'] = $price;
                }
                if(!isset($goods['market_price'])){
                    $goods['market_price'] = $val['market_price'] / 100;
                }
            }


            $sku_value_ids = array_unique($sku_value_ids);
            $sql = "SELECT * FROM {$db_prefix}sku_value WHERE id IN (" . implode(',', $sku_value_ids) . ")";
            $sku_values = $db->fetch_all($sql);
            foreach($sku_values as $val){
                $sku_attr_ids[] = $val['attr_id'];
            }
            $sku_attr_ids = array_unique($sku_attr_ids);
            $sql = "SELECT * FROM {$db_prefix}sku_attr WHERE id IN (" . implode(',', $sku_attr_ids) . ")";
            $sku_attr = $db->fetch_all($sql);

            $goods['spec'] = [];
            $goods['spec_attr'] = [];
            foreach($sku_attr as $key => $val){
                $goods['spec'][$key] = [
                    'sku_attr_id' => $val['id'],
                    'title' => $val['title'],
                    'sku_values' => []
                ];


                foreach($sku_values as $v){
                    if($val['id'] == $v['attr_id']){
                        $goods['spec'][$key]['sku_values'][] = [
                            'id' => $v['id'],
                            'name' => $v['name']
                        ];
                        $goods['spec_attr'][$key][] = $v['id'];
                    }

                }
            }

        }else{
            $price = 0;
            $isFreeClaimGoods = function_exists('free_claim_is_free_goods') && free_claim_is_free_goods($goods_id);
            foreach($skus as $val){
                // 五层价格计算（分站加价已整合在 Level_Price::calculate 内部）
                $base = $isFreeClaimGoods ? 0 : Level_Price::calculate($val, $goods, (int)LEVEL, $stationPremium);
                $price = $base / 100;

                if(!isset($goods['price'])){
                    $goods['price'] = $price;
                }
                if(!isset($goods['market_price'])){
                    $goods['market_price'] = $val['market_price'] / 100;
                }
            }
            $goods['spec'] = [];
            $goods['spec_attr_json'] = json_encode([]);
            if (!empty($val)) {
                $goods['have_stock_skus'][] = array_merge($val, ['price' => $price, 'stock' => $goods['stock']]);
            }
            $goods['have_stock_skus_json'] = json_encode($goods['have_stock_skus']);
            $goods['skus_all_json'] = json_encode([]);
        }


        return self::success($goods);
    }

    /**
     * 获取商品列表
     * @post.sort_id 分类id，为空时获取全部商品
     * @post.keyword 商品搜索
     */
    public static function getGoodsList($post) {
        $goodsModel = new Goods_Model();
        $goods = $goodsModel->getHomeAllGoods($post);
        return self::success($goods);
    }

    /**
     * 返回数据
     */
    private static function success($data){
        if(self::$resp == 'api'){
            Ret::success('success', $data);
        }else{
            return $data;
        }
    }
    private static function error($msg){
        if(self::$resp == 'api'){
            Ret::error($msg);
        }else{
            emMsg($msg);
        }
    }
}
