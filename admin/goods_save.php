<?php

require_once 'globals.php';

if (empty($_POST)) {
    exit;
}


if (!LoginAuth::checkAjaxToken()) {
    Ret::error('安全token校验失败，请刷新页面重试');
}
$db = Database::getInstance();
$db_prefix = DB_PREFIX;
$params = [
    'goods_id' => Input::postIntVar('goods_id', 0), // 商品id
    'sort_id' => Input::postStrVar('sort_id', -1), // 商品分类
    'title' => Input::postStrVar('title'), // 商品标题
    'unit_name' => Input::postStrVar('unit_name', ''), // 数量单位名称
    'type' => Input::postStrVar('type'), // 商品类型
    'is_sku' => Input::postStrVar('is_sku'), // 是否多规格
    'attr_id' => Input::postIntVar('attr_id', 0), // 商品规格id
    'skus' => Input::postStrArray('skus', []), // 商品规格信息
    'attach_user' => Input::postStrVar('attach_user', null), // 附加选项
    'content' => Input::postStrVar('content'), // 商品内容
    'pay_content' => Input::postStrVar('pay_content'), // 商品内容
    'cover' => Input::postStrVar('cover'), // 商品封面图（多图模式下=gallery[0]）
    'gallery' => Input::postStrArray('gallery', []), // 商品图集（多图URL数组，首张=主图）
    'is_on_shelf' => Input::postIntVar('is_on_shelf', 0), // 上架
    'sales' => Input::postIntVar('sales'), // 销量
    'index_top' => Input::postIntVar('index_top', 0), // 首页置顶
    'sort_top' => Input::postIntVar('sort_top', 0), // 分类置顶
    'sort_num' => Input::postIntVar('sort_num'), // 排序
    'des' => Input::postStrVar('des'), // 简介
    // 等级加价规则相关字段（Level_Price::ensureSchema 会自动补齐列）
    'profit_rule_id' => Input::postIntVar('profit_rule_id', 0), // 批量加价规则
    'single_rule_id' => Input::postIntVar('single_rule_id', 0), // 单商品加价规则
    'profit_ratio' => Input::postStrVar('profit_ratio', 100),   // 商品利润比 0-100
    'accuracy' => Input::postIntVar('accuracy', 2),             // 价格精度 0-8
    'discount_title' => Input::postStrVar('discount_title', ''),// 批量购买优惠自定义标题
    'allow_dock' => Input::postIntVar('allow_dock', 0), // 是否允许此商品被对接
];

//die;


$cmd = empty($params['goods_id']) ? 'add' : 'edit';


$discount = Input::postStrArray('discount');                // 每件优惠（type=1，单位：元）
$discount_order = Input::postStrArray('discount_order');    // 订单优惠（type=2，单位：元）
$discount_percent = Input::postStrArray('discount_percent');// 订单折扣（type=3，单位：折，如 9.5 = 9.5折）
$sku_content = Input::postStrArray('sku_content'); // SKU独立详情
//d($discount);die;

$goods_id = $params['goods_id'];
$params['attach_user'] = empty($params['attach_user']) ? null : $params['attach_user'];
$params['post_params'] = empty($params['post_params']) ? '' : $params['post_params'];

if(empty($params['type'])){
    Ret::error('请选择商品类型');

}
if ($cmd == 'edit' && in_array($params['type'], ['qingjiu', 'docking'], true)) {
    $oldGoodsTypeRow = $db->once_fetch_array("SELECT type FROM {$db_prefix}goods WHERE id=" . (int)$params['goods_id'] . " LIMIT 1");
    if (!empty($oldGoodsTypeRow['type']) && !in_array($oldGoodsTypeRow['type'], ['qingjiu', 'docking'], true)) {
        $params['type'] = $oldGoodsTypeRow['type'];
    } else {
        $params['type'] = 'general';
    }
}
if($params['sort_id'] == -1){
    Ret::error('请选择商品分类');
}

if(empty($params['title'])){
    Ret::error('请输入商品名称');
}

$params['unit_name'] = trim($params['unit_name']);

//d($params['skus']);die;
// 必填字段：游客价 + 成本价（必须>0）；固定价/划线价可选
$_requiredPriceKeys = ['guest_price', 'cost_price'];
$_optionalPriceKeys = ['market_price', 'user_price'];
$_priceLabels = ['guest_price' => '游客价', 'cost_price' => '成本价', 'user_price' => '固定价', 'market_price' => '划线价'];
// 固定价要求：可留空（=走等级算价），但若填写则不可为 0
if($params['is_sku'] == 'n'){
    foreach($params['skus'] as $key => $val){
        if($key === 'member') continue; // 会员独立定价单独处理
        if(in_array($key, $_optionalPriceKeys)){
            if(isEmpty($val)){
                $params['skus'][$key] = 0; // 选填项留空按 0 存储（=未设置）
            }else{
                if($key === 'user_price' && floatval($val) <= 0){
                    Ret::error('固定价可以留空，但填写时必须大于 0');
                }
            }
            continue;
        }
        if(in_array($key, $_requiredPriceKeys) && (isEmpty($val) || floatval($val) <= 0)){
            Ret::error('请填写' . ($_priceLabels[$key] ?? $key) . '，且必须大于0');
        }
    }
}
// 会员独立定价：过滤掉空值（留空 = 系统自动计算）
if(!empty($params['skus']['member'])){
    $params['skus']['member'] = array_filter($params['skus']['member'], function($val){
        return $val !== '' && $val !== null;
    });
}

if($params['is_sku'] == 'y'){
    if(empty($params['attr_id'])){
        Ret::error('请设置规格信息');
    }
    if(empty($params['skus'])){
        Ret::error('请设置规格信息');
    }
    foreach($params['skus'] as $key => $val){
        if($key === 'member') continue; // 会员独立定价已在上方处理
        foreach($val as $k => $v){
            if(in_array($k, $_optionalPriceKeys)){
                if(isEmpty($v)){
                    $params['skus'][$key][$k] = 0;
                }else{
                    if($k === 'user_price' && floatval($v) <= 0){
                        Ret::error('规格「' . $key . '」的固定价可以留空，但填写时必须大于 0');
                    }
                }
                continue;
            }
            if(in_array($k, $_requiredPriceKeys) && (isEmpty($v) || floatval($v) <= 0)){
                Ret::error('请填写' . ($_priceLabels[$k] ?? $k) . '，且必须大于0');
            }
        }
    }
}

$goodsModel = new Goods_Model();

// 规则字段安全化
$profitRatio = (float)$params['profit_ratio'];
if ($profitRatio < 0) $profitRatio = 0;
if ($profitRatio > 100) $profitRatio = 100;
$accuracy = (int)$params['accuracy'];
if ($accuracy < 0) $accuracy = 0;
if ($accuracy > 8) $accuracy = 8;

// 归一化 gallery：去空、去重、保持顺序；cover 同步为第一张
$_gallery_clean = [];
if (is_array($params['gallery'])) {
    foreach ($params['gallery'] as $_u) {
        $_u = trim((string)$_u);
        if ($_u !== '' && !in_array($_u, $_gallery_clean, true)) {
            $_gallery_clean[] = $_u;
        }
    }
}
// 兼容老入口：如果 gallery 为空但 cover 不空，单图也合到 gallery
if (empty($_gallery_clean) && !empty($params['cover'])) {
    $_gallery_clean = [trim((string)$params['cover'])];
}
// cover 与 gallery 双向保持一致：cover = 首张
$params['cover'] = !empty($_gallery_clean) ? $_gallery_clean[0] : '';
$_gallery_json = !empty($_gallery_clean) ? json_encode($_gallery_clean, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';

$data = [
    'title' => $params['title'],
    'unit_name' => $params['unit_name'],
    'sort_id' => $params['sort_id'],
    'type' => $params['type'],
    'is_sku' => $params['is_sku'],
    'attr_id' => $params['attr_id'],
    'attach_user' => $params['attach_user'],
    'content' => $params['content'],
    'pay_content' => $params['pay_content'],
    'cover' => $params['cover'],
    'gallery' => $_gallery_json,
    'is_on_shelf' => $params['is_on_shelf'],
    'sales' => $params['sales'],
    'index_top' => $params['index_top'],
    'sort_top' => $params['sort_top'],
    'sort_num' => $params['sort_num'],
    'des' => $params['des'],
    'profit_rule_id' => (int)$params['profit_rule_id'],
    'single_rule_id' => (int)$params['single_rule_id'],
    'profit_ratio' => $profitRatio,
    'accuracy' => $accuracy,
    'discount_title' => mb_substr(trim((string)$params['discount_title']), 0, 32),
    'allow_dock' => $params['allow_dock'],
];

// 确保列存在（老环境自动补字段）
if (class_exists('Level_Price')) {
    Level_Price::ensureSchema();
}
// 老环境自动补 dc_goods.gallery 列
Goods_Model::ensureSchema();
if($cmd == 'add'){
    $old_skus = [];
}else{
    $old_skus = $db->fetch_all("select * from {$db_prefix}skus where goods_id={$goods_id}");
}

//d($old_skus);die;

try {
    $db->beginTransaction();

    $checkUnitName = $db->query("SHOW COLUMNS FROM `{$db_prefix}goods` LIKE 'unit_name'");
    if($db->num_rows($checkUnitName) == 0){
        $db->query("ALTER TABLE `{$db_prefix}goods` ADD `unit_name` VARCHAR(30) NOT NULL DEFAULT '个' AFTER `title`");
    }

    // 确保 dc_discount / dc_goods 相关字段存在（老环境自动补齐，必须在主表保存之前）
    if (class_exists('Goods_Discount')) {
        Goods_Discount::ensureSchema();
    }

    // 1，保存商品主表
    if ($params['goods_id'] > 0) {
        $goodsModel->updateProduct($data, $params['goods_id']);
        $goods_id = $params['goods_id'];
    } else {
        $data['create_time'] = time();
        $goods_id = $goodsModel->addProduct($data);
    }

    $sql = "DELETE FROM `{$db_prefix}discount` WHERE `goods_id` = {$goods_id}";
    $db->query($sql);

    // 确保 dc_discount 的 sku / type 字段存在（老环境自动补齐）
    if (class_exists('Goods_Discount')) {
        Goods_Discount::ensureSchema();
    } else {
        $checkSku = $db->query("SHOW COLUMNS FROM `{$db_prefix}discount` LIKE 'sku'");
        if($db->num_rows($checkSku) == 0){
            $db->query("ALTER TABLE `{$db_prefix}discount` ADD `sku` VARCHAR(100) NOT NULL DEFAULT '0' AFTER `goods_id`");
        }
        $checkType = $db->query("SHOW COLUMNS FROM `{$db_prefix}discount` LIKE 'type'");
        if($db->num_rows($checkType) == 0){
            $db->query("ALTER TABLE `{$db_prefix}discount` ADD `type` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '1=每件优惠(元) 2=订单优惠(元) 3=订单折扣(折,0-100)'");
        }
    }

    // 统一保存三类优惠
    // type=1 每件优惠(discount)、type=2 订单优惠(discount_order)：amount 输入单位为元，存分
    // type=3 订单折扣(discount_percent)：amount 输入单位为折（如 9.5），存为 95（即百分比 95%）
    $_discount_groups = [
        1 => ['data' => $discount,         'is_percent' => false],
        2 => ['data' => $discount_order,   'is_percent' => false],
        3 => ['data' => $discount_percent, 'is_percent' => true],
    ];
    foreach($_discount_groups as $_type => $_group){
        if(empty($_group['data']) || !is_array($_group['data'])) continue;
        foreach($_group['data'] as $sku_key => $sku_discount){
            if(!isset($sku_discount['number']) || !is_array($sku_discount['number'])){
                continue;
            }
            foreach($sku_discount['number'] as $key => $val){
                if(empty($val) || !isset($sku_discount['amount'][$key]) || $sku_discount['amount'][$key] === '') continue;
                $raw = (float)$sku_discount['amount'][$key];
                if($raw <= 0) continue;
                if($_type == 3){
                    // 折数：9.5 折 → 95（百分比）；9.99 保留 2 位小数 → 99.9
                    if($raw >= 10) continue; // >=10 折相当于不打折，忽略
                    $amount = round($raw * 10, 2);
                    if($amount <= 0 || $amount >= 100) continue;
                }else{
                    $amount = round($raw * 100, 0); // 元 → 分
                }
                $sku_value = $db->escape_string($sku_key);
                $sql = "INSERT INTO `{$db_prefix}discount` (`goods_id`, `sku`, `quantity`, `amount`, `type`) VALUES ({$goods_id}, '{$sku_value}', " . (int)$val . ", {$amount}, {$_type})";
                $db->query($sql);
            }
        }
    }

    // 2，保存规格主表
    if($params['is_sku'] == 'y'){
        // 删除会员价格
        $db->query("DELETE FROM {$db_prefix}member_price where goods_id={$goods_id}");
        // 写入独立会员定价（来自高级选项表单，sku='0' 表示适用所有规格）
        if(!empty($params['skus']['member'])){
            foreach($params['skus']['member'] as $_mk => $_mv){
                if($_mv === '' || $_mv === null) continue;
                $_mp = $_mv * 100;
                $db->query("INSERT INTO {$db_prefix}member_price (goods_id, sku, member_level, price) VALUES ({$goods_id}, '0', " . (int)$_mk . ", {$_mp})");
            }
        }
        // 删除商品规格
        $db->query("delete from {$db_prefix}skus where goods_id={$goods_id}");
        
        // 检查skus表是否有content字段，没有则添加（只检查一次）
        $checkContent = $db->query("SHOW COLUMNS FROM `{$db_prefix}skus` LIKE 'content'");
        if($db->num_rows($checkContent) == 0){
            $db->query("ALTER TABLE `{$db_prefix}skus` ADD `content` TEXT NULL AFTER `cost_price`");
        }
        
        $goods_stock = 0;
//        d($params['skus']);die;
        foreach($params['skus'] as $key => $val){
            if($key === 'member') continue; // 独立会员定价已在上方处理
            // 写入商品规格
            $old_stock = 0;
            $old_sales = 0;
            foreach($old_skus as $os){
                if($key == $os['sku']){
                    $old_stock = $os['stock'];
                    $old_sales = $os['sales'];
                }
            }
            $goods_stock+= $old_stock;
            $guest_price = empty($val['guest_price']) || $val['guest_price'] < 0 ? 0 : $val['guest_price'] * 100;
            $user_price = empty($val['user_price']) || $val['user_price'] < 0 ? 0 : $val['user_price'] * 100;
            $market_price = empty($val['market_price']) ? 0 : $val['market_price'] * 100;
            $cost_price = empty($val['cost_price']) ? 0 : $val['cost_price'] * 100;
            $sku_content_val = isset($sku_content[$key]) ? $db->escape_string($sku_content[$key]) : '';
            
            $db->query("INSERT INTO " . DB_PREFIX . "skus 
            (goods_id, sku, guest_price, user_price, market_price, cost_price, content, stock, sales) 
            VALUES 
            ({$goods_id}, '{$key}', {$guest_price}, {$user_price}, {$market_price}, {$cost_price}, '{$sku_content_val}', {$old_stock}, {$old_sales})");
        }
    }
    if($params['is_sku'] == 'n'){
        // 删除会员价格
        $db->query("DELETE FROM {$db_prefix}member_price where goods_id={$goods_id}");
        // 删除商品规格
        $db->query("delete from {$db_prefix}skus where goods_id={$goods_id}");
        $goods_stock = 0;
        // 写入会员价格
        if(!empty($params['skus']['member'])){
            foreach($params['skus']['member'] as $key => $val){
                $price = $val * 100;
                $db->query("INSERT INTO {$db_prefix}member_price (goods_id, sku, member_level, price) VALUES ({$goods_id}, '0', {$key}, {$price})");
            }
        }
//        d($old_skus);die;
        // 写入商品规格
        $old_stock = 0;
        $old_sales = 0;
        foreach($old_skus as $os){
            if('0' == $os['sku']){
                $old_stock = $os['stock'];
                $old_sales = $os['sales'];
            }
        }
        $goods_stock+= $old_stock;
        $guest_price = $params['skus']['guest_price'] * 100;
        $user_price = $params['skus']['user_price'] * 100;
        $market_price = $params['skus']['market_price'] * 100;
        $cost_price = empty($params['skus']['cost_price']) ? 0 : $params['skus']['cost_price'] * 100;
        $post_params = $params['post_params'];
        $db->query("INSERT INTO " . DB_PREFIX . "skus 
            (goods_id, sku, guest_price, user_price, market_price, cost_price, stock, sales) 
            VALUES 
            ({$goods_id}, '0', {$guest_price}, {$user_price}, {$market_price}, {$cost_price}, {$old_stock}, {$old_sales})");
    }
//    var_dump($goods_stock);die;
    // 3. 更新商品库存
    $db->query("update {$db_prefix}goods set stock = {$goods_stock} where id = {$goods_id}");

    // 触发商品保存钩子
    if($cmd == 'add'){
        doAction('goods_created', $goods_id, $data);
    } else {
        doAction('goods_updated', $goods_id, $data);
    }
    doAction('save_goods_after', $goods_id, $data, $cmd);



    $db->commit();
} catch (Exception $e) {
    $db->rollback();
    output::error($e->getMessage());
}

if(empty($params['goods_id'])){
    die(json_encode(['msg' => '商品已添加', 'type' => 'add']));
}else{
    die(json_encode(['msg' => '', 'type' => 'edit']));
}
