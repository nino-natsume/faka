<?php

// 开启输出缓冲
ob_start();

require_once 'globals.php';

$goodsModel = new Goods_Model();
$Sort_Model = new Sort_Model();
$User_Model = new User_Model();
$Media_Model = new Media_Model();
$MediaSort_Model = new MediaSort_Model();
$Template_Model = new Template_Model();

function admin_goods_table_exists($db, $table) {
    static $cache = [];
    $table = trim((string)$table);
    if ($table === '') return false;
    if (isset($cache[$table])) return $cache[$table];
    $row = $db->once_fetch_array("SHOW TABLES LIKE '" . $db->escape_string($table) . "'");
    $cache[$table] = !empty($row);
    return $cache[$table];
}

function admin_goods_mapping_exists($db, $db_prefix, $table, $goodsId) {
    if (!admin_goods_table_exists($db, $db_prefix . $table)) return false;
    $row = $db->once_fetch_array("SELECT id FROM {$db_prefix}{$table} WHERE goods_id = " . (int)$goodsId . " LIMIT 1");
    return !empty($row);
}

if (empty($action)) {

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perpage_num = Option::get('admin_article_perpage_num');
    $start_limit = !empty($page) ? ($page - 1) * $perpage_num : 0;
    $limit = "LIMIT $start_limit, " . $perpage_num;

    $db = Database::getInstance();



    $sorts = $CACHE->readCache('sort');
    $sorts[] = [
        'sortname' => '未分类',
        'sid' => -1
    ];
    $category_json = [];
    foreach($sorts as $val){
        $category_json[] = [
            'text' => $val['sortname'],
            'value' => $val['sid']
        ];
    }
    $category_json[] = [
        'text' => '未分类',
        'value' => -1
    ];
    $category_json = json_encode($category_json);

    $tmpGoods = ['goods_type_all' => []];
    doMultiAction('adm_add_goods_goodsinfo', $tmpGoods, $tmpGoods);
    $goodsTypeList = array_filter($tmpGoods['goods_type_all'] ?? [], function($gt){ return !in_array($gt['value'], ['docking', 'qingjiu', 'xiaoqing', 'yiciyuan', 'mcy'], true); });

    $br = '<a href="./">数据中心</a><a href="./goods.php">商品管理</a><a><cite>商品列表</cite></a>';
    include View::getAdmView(User::haveEditPermission() ? 'header' : 'uc_header');
    require_once View::getAdmView('goods');
    include View::getAdmView(User::haveEditPermission() ? 'footer' : 'uc_footer');
    View::output();
}

if($action == 'index'){
    $draft = isset($_GET['draft']) ? (int)$_GET['draft'] : 0;
    $sid = isset($_GET['sid']) ? (int)$_GET['sid'] : '';
    $uid = isset($_GET['uid']) ? (int)$_GET['uid'] : '';
    $keyword = isset($_GET['keyword']) ? addslashes(trim($_GET['keyword'])) : '';
    $category_id = isset($_GET['category_id']) ? addslashes(trim($_GET['category_id'])) : 0;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perpage_num = Input::getIntVar('limit', 10);
//    $perpage_num = Option::get('admin_article_perpage_num');
    $start_limit = !empty($page) ? ($page - 1) * $perpage_num : 0;
    $limit = "LIMIT $start_limit, " . $perpage_num;

    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $dockingMappingTables = ['docking' => 'docking_goods', 'qingjiu' => 'qingjiu_goods', 'xiaoqing' => 'xiaoqing_goods', 'yiciyuan' => 'yiciyuan_goods', 'mcy' => 'mcy_goods'];
    $availableDockingTables = [];
    foreach ($dockingMappingTables as $dockKey => $dockTable) {
        if (admin_goods_table_exists($db, $db_prefix . $dockTable)) $availableDockingTables[$dockKey] = $dockTable;
    }

    $where = "";

    if(!empty($keyword)){
        $where .= " and title like '%{$keyword}%' ";
    }else{

    }
    if($category_id != 0){
        $where .= " and sort_id={$category_id} ";
    }
    $shelf_status = isset($_GET['is_on_shelf']) && $_GET['is_on_shelf'] !== '' ? (int)$_GET['is_on_shelf'] : -1;
    if($shelf_status >= 0){
        $where .= " and is_on_shelf={$shelf_status} ";
    }
    $goods_type = isset($_GET['goods_type']) ? addslashes(trim($_GET['goods_type'])) : '';
    if(!empty($goods_type)){
        $qingjiuServiceWhere = isset($availableDockingTables['qingjiu']) ? " or (type='qingjiu' and EXISTS (SELECT 1 FROM {$db_prefix}qingjiu_goods qg_type WHERE qg_type.goods_id = {$db_prefix}goods.id) and attach_user NOT LIKE '%收货人%' and attach_user NOT LIKE '%手机号%' and attach_user NOT LIKE '%详细地址%')" : '';
        $xiaoqingServiceWhere = isset($availableDockingTables['xiaoqing']) ? " or (EXISTS (SELECT 1 FROM {$db_prefix}xiaoqing_goods xq_type WHERE xq_type.goods_id = {$db_prefix}goods.id) and attach_user NOT LIKE '%收货人%' and attach_user NOT LIKE '%手机号%' and attach_user NOT LIKE '%详细地址%')" : '';
        $qingjiuPhysicalWhere = isset($availableDockingTables['qingjiu']) ? " or (type='qingjiu' and EXISTS (SELECT 1 FROM {$db_prefix}qingjiu_goods qg_type WHERE qg_type.goods_id = {$db_prefix}goods.id) and attach_user LIKE '%收货人%' and attach_user LIKE '%手机号%' and attach_user LIKE '%详细地址%')" : '';
        $xiaoqingPhysicalWhere = isset($availableDockingTables['xiaoqing']) ? " or (EXISTS (SELECT 1 FROM {$db_prefix}xiaoqing_goods xq_type WHERE xq_type.goods_id = {$db_prefix}goods.id) and attach_user LIKE '%收货人%' and attach_user LIKE '%手机号%' and attach_user LIKE '%详细地址%')" : '';
        if($goods_type === 'service'){
            $where .= " and (type='service'{$qingjiuServiceWhere}{$xiaoqingServiceWhere}) ";
        }elseif($goods_type === 'physical'){
            $where .= " and (type='physical'{$qingjiuPhysicalWhere}{$xiaoqingPhysicalWhere}) ";
        }else{
            $where .= " and type='{$goods_type}' ";
        }
    }
    $is_docking = isset($_GET['is_docking']) ? (int)$_GET['is_docking'] : 0;
    if($is_docking === 1){
        $dockExistsWhere = [];
        foreach ($availableDockingTables as $dockTable) {
            $dockExistsWhere[] = "EXISTS (SELECT 1 FROM {$db_prefix}{$dockTable} dock_filter WHERE dock_filter.goods_id = {$db_prefix}goods.id)";
        }
        $where .= !empty($dockExistsWhere) ? " and (" . implode(' OR ', $dockExistsWhere) . ") " : " and 1=0 ";
    }

    $order_by = "ORDER BY ";
    $order_field = Input::getStrVar('field');
    $order_type = Input::getStrVar('order');
    if($order_field && $order_type){
        $order_by .= "{$order_field} $order_type, ";
    }
    $order_by .= " id desc";

    $sql = "SELECT id, cover, create_time, delete_time, index_top, is_on_shelf, is_sku, sales, sort_id, sort_top, sort_num, stock, title, type, attach_user FROM {$db_prefix}goods where delete_time is null {$where} {$order_by} $limit;";

    $sorts = $CACHE->readCache('sort');

    $res = $db->query($sql);
    $tmpRows = [];
    $tmpIds = [];
    while ($r = $db->fetch_array($res)) { $tmpRows[] = $r; $tmpIds[] = (int)$r['id']; }
    $dockingIdsByType = [];
    if (!empty($tmpIds)) {
        $idsStr = implode(',', $tmpIds);
        foreach ($availableDockingTables as $dockKey => $dockTable) {
            $dockingIdsByType[$dockKey] = [];
            $dockRes = $db->query("SELECT goods_id FROM {$db_prefix}{$dockTable} WHERE goods_id IN ({$idsStr})");
            while ($dockRow = $db->fetch_array($dockRes)) { $dockingIdsByType[$dockKey][(int)$dockRow['goods_id']] = true; }
        }
    }
    $goods = [];
    foreach ($tmpRows as $row) {
        $goodsId = (int)$row['id'];
        $rowDockingType = '';
        foreach (['yiciyuan', 'mcy', 'xiaoqing', 'qingjiu', 'docking'] as $dockKey) {
            if (!empty($dockingIdsByType[$dockKey][$goodsId])) {
                $rowDockingType = $dockKey;
                break;
            }
        }
        $row['is_docking'] = $rowDockingType !== '' ? 1 : 0;
        $row['docking_type'] = $rowDockingType;
        $row['timestamp'] = $row['create_time'];
        $row['create_time'] = date("Y-m-d H:i", $row['create_time']);
        $row['title'] = !empty($row['title']) ? htmlspecialchars($row['title']) : '无标题';
        if ($row['type'] === 'qingjiu') {
            $row['type'] = 'service';
            $attach = json_decode((string)($row['attach_user'] ?? ''), true);
            $attachNames = [];
            if (is_array($attach)) {
                foreach ($attach as $item) {
                    if (is_array($item) && isset($item['name'])) $attachNames[] = (string)$item['name'];
                }
            }
            if (in_array('收货人', $attachNames, true) && in_array('手机号', $attachNames, true) && in_array('详细地址', $attachNames, true)) {
                $row['type'] = 'physical';
            }
        }
        $row['type_text'] = goodsTypeText($row['type']);
        // 根据规格类型计算实际库存
        if ($row['is_sku'] == 'y' || $row['is_sku'] == 1) {
            // 多规格商品：从skus表汇总库存
            $skuStock = $db->once_fetch_array("SELECT COALESCE(SUM(stock), 0) AS total_stock FROM {$db_prefix}skus WHERE goods_id = {$row['id']}");
            $row['stock'] = number_format($skuStock['total_stock']);
            // 获取各规格库存明细（用于鼠标悬停提示）
            $skuDetails = $db->fetch_all("SELECT s.sku, s.stock FROM {$db_prefix}skus s WHERE s.goods_id = {$row['id']}");
            $skuTips = [];
            foreach ($skuDetails as $sd) {
                $skuName = '默认';
                if ($sd['sku'] != '0' && !empty($sd['sku'])) {
                    $skuIds = explode('-', $sd['sku']);
                    $names = [];
                    foreach ($skuIds as $skuId) {
                        $sv = $db->once_fetch_array("SELECT name FROM {$db_prefix}sku_value WHERE id = " . (int)$skuId);
                        if ($sv) $names[] = $sv['name'];
                    }
                    if (!empty($names)) $skuName = implode('/', $names);
                }
                $skuTips[] = $skuName . '：' . (int)$sd['stock'];
            }
            $row['sku_tips'] = implode("\n", $skuTips);
        } else {
            $row['stock'] = number_format($row['stock']);
            $row['sku_tips'] = '';
        }

        $sortName = isset($sorts[$row['sort_id']]['sortname']) ? $sorts[$row['sort_id']]['sortname'] : '未知分类';
        if ($sortName === '未知分类' && (int)$row['sort_id'] > 0) {
            $sortRow = $db->once_fetch_array("SELECT sortname FROM {$db_prefix}sort WHERE sid=" . (int)$row['sort_id'] . " AND type='goods' LIMIT 1");
            if ($sortRow && isset($sortRow['sortname'])) {
                $sortName = htmlspecialchars($sortRow['sortname']);
                if (isset($CACHE) && is_object($CACHE) && method_exists($CACHE, 'updateCache')) {
                    $CACHE->updateCache('sort');
                }
            }
        }
        $row['sort_name'] = $row['sort_id'] == -1 ? '未分类' : $sortName;

        $goods[] = $row;
    }

    $res = $db->once_fetch_array("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "goods where delete_time is null {$where}");

    output::data($goods, $res['total']);
}


if($action == 'shelf'){
    $goods_id = Input::postIntVar('goods_id');
    $is_on_shelf = Input::postIntVar('status');
    $goodsModel->updateProduct(['is_on_shelf' => $is_on_shelf], $goods_id);
    output::ok();
}

// 快捷更新排序 - 数字越大越靠前
if($action == 'update_sort_num'){
    $goods_id = Input::postIntVar('goods_id');
    $sort_num = Input::postIntVar('sort_num', 0);
    if(empty($goods_id)){
        output::error('商品ID不能为空');
    }
    $goodsModel->updateProduct(['sort_num' => $sort_num], $goods_id);
    output::ok();
}


if($action == 'del'){
    $ids = Input::postStrVar('ids');
    $ids = explode(',', $ids);
    foreach($ids as $goods_id){
        $goodsModel->deleteGoods($goods_id);
        doAction('del_product', $goods_id);
    }
    output::ok();
}

if($action == 'copy'){
    $goods_id = Input::postIntVar('goods_id');
    
    if(empty($goods_id)){
        output::error('商品ID不能为空');
    }
    
    try {
        // 获取原商品信息
        $originalGoods = $goodsModel->getOneGoodsForAdmin($goods_id);
        if(empty($originalGoods)){
            output::error('商品不存在');
        }
        
        $db = Database::getInstance();
        $db_prefix = DB_PREFIX;
        $originalType = strtolower(trim((string)($originalGoods['type'] ?? '')));
        $copyType = $originalType !== '' ? $originalType : 'once';
        if ($copyType === 'docking') {
            $copyType = 'duli';
        } elseif (in_array($copyType, ['qingjiu', 'yiciyuan'], true)) {
            $copyType = 'once';
        } elseif (!in_array($copyType, ['once', 'service', 'general', 'physical', 'duli', 'guding', 'xuni', 'post'], true)) {
            $copyType = 'once';
        }
        
        // 准备复制的商品数据 - 只包含必要字段
        $copyData = [
            'title' => $originalGoods['title'] . ' - 副本',
            'content' => $originalGoods['content'] ?? '',
            'pay_content' => $originalGoods['pay_content'] ?? '',
            'sort_id' => $originalGoods['sort_id'] ?? -1,
            'cover' => $originalGoods['cover'] ?? '',
            'unit_name' => $originalGoods['unit_name'] ?? '/个',
            'type' => $copyType,
            'is_sku' => $originalGoods['is_sku'] ?? 'n',
            'attr_id' => $originalGoods['attr_id'] ?? 0,
            'attach_user' => $originalGoods['attach_user'],
            'is_on_shelf' => 0, // 复制的商品默认下架
            'sales' => 0, // 销量重置为0
            'index_top' => $originalGoods['index_top'] ?? 0,
            'sort_top' => $originalGoods['sort_top'] ?? 0,
            'sort_num' => $originalGoods['sort_num'] ?? 0,
            'des' => $originalGoods['des'] ?? '',
            'discount_title' => $originalGoods['discount_title'] ?? '',
            'create_time' => time()
        ];
        foreach ($copyData as $field => $value) {
            if ($value === null) {
                $copyData[$field] = '';
            } elseif (is_string($value)) {
                $copyData[$field] = $db->escape_string($value);
            }
        }
        
        // 创建新商品
        $newGoodsId = $goodsModel->addProduct($copyData);
        
        if($newGoodsId){
            
            // 复制SKU数据
            $skuSql = "SELECT * FROM {$db_prefix}skus WHERE goods_id = {$goods_id}";
            $skuResult = $db->query($skuSql);
            
            while($sku = $db->fetch_array($skuResult)){
                $skuInsertSql = "INSERT INTO {$db_prefix}skus 
                    (goods_id, sku, guest_price, user_price, market_price, cost_price, stock, sales) 
                    VALUES 
                    ({$newGoodsId}, '{$sku['sku']}', {$sku['guest_price']}, {$sku['user_price']}, {$sku['market_price']}, {$sku['cost_price']}, {$sku['stock']}, 0)";
                $db->query($skuInsertSql);
            }
            
            // 复制会员价格设置
            $memberPriceSql = "SELECT * FROM {$db_prefix}member_price WHERE goods_id = {$goods_id}";
            $memberPriceResult = $db->query($memberPriceSql);
            
            while($memberPrice = $db->fetch_array($memberPriceResult)){
                $sku_value = isset($memberPrice['sku']) ? $db->escape_string($memberPrice['sku']) : '0';
                $memberPriceInsertSql = "INSERT INTO {$db_prefix}member_price 
                    (goods_id, sku, member_level, price) 
                    VALUES 
                    ({$newGoodsId}, '{$sku_value}', {$memberPrice['member_level']}, {$memberPrice['price']})";
                $db->query($memberPriceInsertSql);
            }
            
            // 复制商品的折扣信息（含 type 字段）
            if (class_exists('Goods_Discount')) {
                Goods_Discount::ensureSchema();
            }
            $discountSql = "SELECT * FROM {$db_prefix}discount WHERE goods_id = {$goods_id}";
            $discountResult = $db->query($discountSql);

            while($discount = $db->fetch_array($discountResult)){
                $sku_value = isset($discount['sku']) ? $discount['sku'] : '0';
                $_dtype = isset($discount['type']) ? (int)$discount['type'] : 1;
                $discountInsertSql = "INSERT INTO {$db_prefix}discount 
                    (goods_id, sku, quantity, amount, type) 
                    VALUES 
                    ({$newGoodsId}, '{$sku_value}', {$discount['quantity']}, {$discount['amount']}, {$_dtype})";
                $db->query($discountInsertSql);
            }
            
            doAction('copy_product', $newGoodsId, $goods_id);
            output::ok(['new_goods_id' => $newGoodsId, 'message' => '商品复制成功']);
        } else {
            output::error('商品复制失败');
        }
        
    } catch (Exception $e) {
        output::error('复制商品时发生错误：' . $e->getMessage());
    }
}

if ($action == 'del') {
    $id = Input::getIntVar('id');
    $isRm = Input::getIntVar('rm');

    LoginAuth::checkToken();
    if ($isRm) {

    } else {
        $goodsModel->hideSwitch($id, 'y');
    }
    $CACHE->updateCache();
    emDirect("./goods.php?&active_del=1");
}

if ($action == 'operate_goods') {
    $operate = Input::requestStrVar('operate');
    $draft = Input::postIntVar('draft');
    $logs = Input::postIntArray('blog');
    $sort = Input::postIntVar('sort');
    $author = Input::postIntVar('author');
    $id = Input::requestNumVar('id');

    LoginAuth::checkToken();

    if (!$operate) {
        emDirect("./goods.php?draft=$draft&error_b=1");
    }
    if (empty($logs) && empty($id)) {
        emDirect("./goods.php?draft=$draft&error_a=1");
    }

    switch ($operate) {
        case 'del':
            foreach ($logs as $val) {
                doAction('before_del_product', $val);
                $goodsModel->deleteProduct($val);
                doAction('del_product', $val);
            }
            $CACHE->updateCache();
            emDirect("./goods.php?draft=1&active_del=1&draft=$draft");
            break;
        case 'top':
            foreach ($logs as $val) {
                $goodsModel->updateLog(array('top' => 'y'), $val);
            }
            emDirect("./goods.php?active_up=1&draft=$draft");
            break;
        case 'sortop':
            foreach ($logs as $val) {
                $goodsModel->updateLog(array('sortop' => 'y'), $val);
            }
            emDirect("./goods.php?active_up=1&draft=$draft");
            break;
        case 'notop':
            foreach ($logs as $val) {
                $goodsModel->updateLog(array('top' => 'n', 'sortop' => 'n'), $val);
            }
            emDirect("./goods.php?active_down=1&draft=$draft");
            break;
        case 'hide':
            foreach ($logs as $val) {
                $goodsModel->hideSwitch($val, 'y');
            }
            $CACHE->updateCache();
            emDirect("./goods.php?active_hide=1&draft=$draft");
            break;
        case 'pub':
            foreach ($logs as $val) {
                $goodsModel->hideSwitch($val, 'n');
                if (User::haveEditPermission()) {
                    $goodsModel->checkSwitch($val, 'y');
                }
            }
            $CACHE->updateCache();
            emDirect("./goods.php?draft=1&active_post=1&draft=$draft");
            break;
        case 'move':
            foreach ($logs as $val) {
                $goodsModel->checkEditable($val);
                $goodsModel->updateLog(array('sortid' => $sort), $val);
            }
            $CACHE->updateCache(array('sort', 'logsort'));
            emDirect("./goods.php?active_move=1&draft=$draft");
            break;
        case 'change_author':
            if (!User::haveEditPermission()) {
                emMsg('权限不足！', './');
            }
            foreach ($logs as $val) {
                $goodsModel->updateLog(array('author' => $author), $val);
            }
            $CACHE->updateCache('sta');
            emDirect("./goods.php?active_change_author=1&draft=$draft");
            break;
        case 'check':
            if (!User::haveEditPermission()) {
                emMsg('权限不足！', './');
            }
            if ($logs) {
                foreach ($logs as $id) {
                    $goodsModel->checkSwitch($id, 'y');
                }
            } else {
                $goodsModel->checkSwitch($id, 'y');
            }
            $CACHE->updateCache();
            emDirect("./goods.php?active_ck=1&draft=$draft");
            break;
        case 'uncheck':
            if (!User::haveEditPermission()) {
                emMsg('权限不足！', './');
            }
            if ($logs) {
                $feedback = '';
                foreach ($logs as $id) {
                    $goodsModel->unCheck($id, $feedback);
                }
            } else {
                $id = Input::postIntVar('id');
                $feedback = Input::postStrVar('feedback');
                $goodsModel->unCheck($id, $feedback);
            }
            $CACHE->updateCache();
            emDirect("./goods.php?active_unck=1&draft=$draft");
            break;
    }
}

if ($action === 'release') {
    $goods = [
        'attr_id' => 0,
        'type' => 'duli',
        'is_sku' => 'n',
        'id'    => 0,
        'title'    => '',
        'content'  => '',
        'pay_content'  => '',
        'excerpt'  => '',
        'alias'    => '',
        'sort_id'   => -1,
        'password' => '',
        'hide'     => '',
        'author'   => UID,
        'cover'    => '',
        'gallery_list' => [],
        'unit_name' => '/个',
        'link'     => '',
        'template' => '',
        'attach_user' => null,
        'is_on_shelf' => 1,
        'post_url' => '',
        'post_params' => '',
        'sales' => 0,
        'desc' => '',
        'index_top' => 'n',
        'sort_top' => 'n',
        'des' => '',
        'sort_num' => '',
        'profit_rule_id' => 0,
        'single_rule_id' => 0,
        'profit_ratio' => 100,
        'accuracy' => 2,
        'has_member_price' => 0,
        'discount_title' => '',
        'goods_type_all' => [
//            ['name' => '一次性卡密（版本废弃）', 'value' => 'duli'],
//            ['name' => '固定通用卡密', 'value' => 'guding'],
//            ['name' => '虚拟服务类型', 'value' => 'xuni'],
//            ['name' => '自定义接口URL/POST', 'value' => 'post'],
        ]
    ];

    doMultiAction('adm_add_goods_goodsinfo', $goods, $goods);

    // 加价规则选项
    $profitRuleModel = new Profit_Rule_Model();
    $profitRules = $profitRuleModel->getActiveOptions();
    $singleRuleModel = new Single_Rule_Model();
    $singleRules = $singleRuleModel->getActiveOptions();

    $sorts = $CACHE->readCache('sort');
    
    // 初始化折扣数据（新建商品时为空）
    $discount = [];         // type=1 每件优惠
    $discount_order = [];   // type=2 订单优惠
    $discount_percent = []; // type=3 订单折扣

    $memberModel = new Member_Model();
    $members = $memberModel->getMembersAll();

    $sku_table = [
        'head' => [
            ['title' => '游客价(元) <span style="color:#e53e3e;">*</span>', 'icon' => 'fa fa-edit'],
            ['title' => '成本价(元) <span style="color:#e53e3e;">*</span>', 'icon' => 'fa fa-edit'],
            ['title' => '固定价(元) <span style="color:#999;"></span>', 'icon' => 'fa fa-edit'],
            ['title' => '划线价(元)', 'icon' => 'fa fa-edit'],
        ],
        'body' => [
            ['type' => 'input', 'field' => 'guest_price', 'value' => '', 'type' => 'number', 'placeholder' => '必填，建议≥最低会员价'],
            ['type' => 'input', 'field' => 'cost_price', 'value' => '', 'type' => 'number', 'placeholder' => '必填，等级算价基础'],
            ['type' => 'input', 'field' => 'user_price', 'value' => '', 'type' => 'number', 'placeholder' => '选填，空=走等级算价（不可填0）'],
            ['type' => 'input', 'field' => 'market_price', 'value' => '', 'type' => 'number', 'placeholder' => '选填，默认0'],
        ]

    ];


    $br = '<a href="./">数据中心</a><a href="./goods.php">商品管理</a><a><cite>添加商品</cite></a>';

    $isPopup = Input::getIntVar('popup', 0);
    if ($isPopup) {
        include View::getAdmView('open_head');
        require_once(View::getAdmView('goods_release'));
        include View::getAdmView('open_foot');
    } else {
        include View::getAdmView(User::haveEditPermission() ? 'header' : 'uc_header');
        require_once(View::getAdmView('goods_release'));
        include View::getAdmView(User::haveEditPermission() ? 'footer' : 'uc_footer');
    }
    View::output();
}

if ($action === 'edit') {
    $goods_id = Input::getIntVar('id');

    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;

    // 检查skus表是否有content字段，没有则添加（需在查询前执行）
    $checkContent = $db->query("SHOW COLUMNS FROM `{$db_prefix}skus` LIKE 'content'");
    if($db->num_rows($checkContent) == 0){
        $db->query("ALTER TABLE `{$db_prefix}skus` ADD `content` TEXT NULL AFTER `cost_price`");
    }

    // 老库自动补齐 dc_goods.gallery 字段
    Goods_Model::ensureSchema();

    $goods = $goodsModel->getOneGoodsForAdmin($goods_id);

    $goods['is_docking'] = admin_goods_mapping_exists($db, $db_prefix, 'docking_goods', $goods_id) ? 1 : 0;
    $goods['is_qingjiu'] = admin_goods_mapping_exists($db, $db_prefix, 'qingjiu_goods', $goods_id) ? 1 : 0;
    $goods['is_xiaoqing'] = admin_goods_mapping_exists($db, $db_prefix, 'xiaoqing_goods', $goods_id) ? 1 : 0;
    $goods['is_yiciyuan'] = admin_goods_mapping_exists($db, $db_prefix, 'yiciyuan_goods', $goods_id) ? 1 : 0;
    $goods['is_mcy'] = admin_goods_mapping_exists($db, $db_prefix, 'mcy_goods', $goods_id) ? 1 : 0;
    if (!empty($goods['is_yiciyuan'])) {
        $goods['is_docking'] = 1;
        $goods['docking_type'] = 'yiciyuan';
    } elseif (!empty($goods['is_mcy'])) {
        $goods['is_docking'] = 1;
        $goods['docking_type'] = 'mcy';
    } elseif (!empty($goods['is_xiaoqing'])) {
        $goods['is_docking'] = 1;
        $goods['docking_type'] = 'xiaoqing';
    } elseif (!empty($goods['is_qingjiu'])) {
        $goods['is_docking'] = 1;
        $goods['docking_type'] = 'qingjiu';
    } elseif (!empty($goods['is_docking'])) {
        $goods['docking_type'] = 'docking';
    } else {
        $goods['docking_type'] = '';
    }

    // 解析商品图集（cover + gallery JSON 合并，cover 一定在首位）
    $goods['gallery_list'] = Goods_Model::parseGallery($goods);

    $goods['goods_type_all'] = [];
    doMultiAction('adm_add_goods_goodsinfo', $goods, $goods);
    
    // 获取SKU名称映射（用于多规格优惠设置显示）
    $goods['sku_names'] = [];
    if($goods['is_sku'] == 'y' && !empty($goods['skus'])) {
        // 收集所有规格值ID
        $all_sku_value_ids = [];
        foreach($goods['skus'] as $sku) {
            $sku_ids = explode('-', $sku['sku']);
            $all_sku_value_ids = array_merge($all_sku_value_ids, $sku_ids);
        }
        $all_sku_value_ids = array_unique(array_filter($all_sku_value_ids));
        
        // 查询规格值名称
        $sku_value_names = [];
        if(!empty($all_sku_value_ids)) {
            $ids_str = implode(',', $all_sku_value_ids);
            $sql = "SELECT id, name FROM `{$db_prefix}sku_value` WHERE id IN ({$ids_str})";
            $result = $db->query($sql);
            while($row = $db->fetch_array($result)) {
                $sku_value_names[$row['id']] = $row['name'];
            }
        }
        
        // 构建SKU名称映射
        foreach($goods['skus'] as $sku) {
            $sku_key = $sku['sku'];
            $sku_ids = explode('-', $sku_key);
            $names = [];
            foreach($sku_ids as $id) {
                if(isset($sku_value_names[$id])) {
                    $names[] = $sku_value_names[$id];
                }
            }
            $goods['sku_names'][$sku_key] = !empty($names) ? implode(' / ', $names) : '规格 ' . $sku_key;
        }
    }

    // 确保 dc_discount 有 type 字段（老环境自动补齐）
    if (class_exists('Goods_Discount')) {
        Goods_Discount::ensureSchema();
    }
    $sql = "SELECT * FROM `{$db_prefix}discount` WHERE `goods_id` = {$goods['id']}";
    $discount_all = $db->fetch_all($sql);
    // 按 type 分组：1=每件优惠 2=订单优惠 3=订单折扣
    $discount = [];         // type=1
    $discount_order = [];   // type=2
    $discount_percent = []; // type=3
    foreach($discount_all as $_d){
        $_t = isset($_d['type']) ? (int)$_d['type'] : 1;
        if($_t == 2){
            $discount_order[] = $_d;
        }elseif($_t == 3){
            $discount_percent[] = $_d;
        }else{
            $discount[] = $_d;
        }
    }


    $sorts = $CACHE->readCache('sort');
    $mediaSorts = $MediaSort_Model->getSorts();

    $memberModel = new Member_Model();
    $members = $memberModel->getMembersAll();

    // 加价规则选项
    $profitRuleModel = new Profit_Rule_Model();
    $profitRules = $profitRuleModel->getActiveOptions();
    $singleRuleModel = new Single_Rule_Model();
    $singleRules = $singleRuleModel->getActiveOptions();

    // 为旧数据补充默认值（Level_Price::ensureSchema 已补字段，SELECT 出来可能是 null）
    $goods['profit_rule_id'] = (int)($goods['profit_rule_id'] ?? 0);
    $goods['single_rule_id'] = (int)($goods['single_rule_id'] ?? 0);
    $goods['profit_ratio'] = (float)($goods['profit_ratio'] ?? 100);
    $goods['accuracy'] = (int)($goods['accuracy'] ?? 2);

    // 检测是否有独立会员定价
    $_mpCount = $db->once_fetch_array("SELECT COUNT(*) AS cnt FROM {$db_prefix}member_price WHERE goods_id=" . (int)$goods['id']);
    $goods['has_member_price'] = ($_mpCount && (int)$_mpCount['cnt'] > 0) ? 1 : 0;

    $sku_table = [
        'head' => [
            ['title' => '游客价(元) <span style="color:#e53e3e;">*</span>', 'icon' => 'fa fa-edit'],
            ['title' => '成本价(元) <span style="color:#e53e3e;">*</span>', 'icon' => 'fa fa-edit'],
            ['title' => '固定价(元)', 'icon' => 'fa fa-edit'],
            ['title' => '划线价(元)', 'icon' => 'fa fa-edit'],
        ],
        'body' => [
            ['type' => 'input', 'field' => 'guest_price', 'value' => '', 'type' => 'number', 'placeholder' => '必填，建议≥最低会员价'],
            ['type' => 'input', 'field' => 'cost_price', 'value' => '', 'type' => 'number', 'placeholder' => '必填，等级算价基础'],
            ['type' => 'input', 'field' => 'user_price', 'value' => '', 'type' => 'number', 'placeholder' => '选填，空=走等级算价（不可填0）'],
            ['type' => 'input', 'field' => 'market_price', 'value' => '', 'type' => 'number', 'placeholder' => '选填，默认0'],
        ]

    ];


    $br = '<a href="./">数据中心</a><a href="./goods.php">商品管理</a><a><cite>编辑商品</cite></a>';

    $isPopup = Input::getIntVar('popup', 0);
    if ($isPopup) {
        include View::getAdmView('open_head');
        require_once(View::getAdmView('goods_release'));
        include View::getAdmView('open_foot');
    } else {
        include View::getAdmView(User::haveEditPermission() ? 'header' : 'uc_header');
        require_once(View::getAdmView('goods_release'));
        include View::getAdmView(User::haveEditPermission() ? 'footer' : 'uc_footer');
    }
    View::output();
}

if ($action == 'upload_cover') {
    $ret = uploadCropImg();
    $Media_Model->addMedia($ret['file_info']);
    Output::ok($ret['file_info']['file_path']);
}

// 等级价预览（AJAX）
// 入参：cost (元) / profit_rule_id / single_rule_id / profit_ratio (0-100)
// 返回：[[level_id, level_name, price_yuan, source], ...]
// =======================================
if ($action === 'level_preview') {
    $cost = (float)Input::postStrVar('cost', 0);
    $profitRuleId = Input::postIntVar('profit_rule_id', 0);
    $singleRuleId = Input::postIntVar('single_rule_id', 0);
    $profitRatio = (float)Input::postStrVar('profit_ratio', 100);
    $accuracy = Input::postIntVar('accuracy', 2);
    if ($accuracy < 0) $accuracy = 0; if ($accuracy > 8) $accuracy = 8;

    if ($cost < 0) $cost = 0;
    if ($profitRatio < 0) $profitRatio = 0;
    if ($profitRatio > 100) $profitRatio = 100;

    $costCents = (int)round($cost * 100);
    $memberModel = new Member_Model();
    $levels = $memberModel->getActiveList();

    $rows = [];

    // 游客（未登录）
    $rows[] = [
        'level_id' => -1,
        'level_name' => '游客（未登录）',
        'price' => '--',
        'source' => '直接使用商品的游客价',
    ];

    // 用 Level_Price::calculate 算
    $goodsRow = [
        'id' => 0,
        'profit_rule_id' => $profitRuleId,
        'single_rule_id' => $singleRuleId,
        'profit_ratio' => $profitRatio,
    ];
    foreach ($levels as $lv) {
        $lvId = (int)$lv['id'];
        $skuRow = [
            'sku' => '0',
            'cost_price' => $costCents,
            'user_price' => 0,
            'guest_price' => 0,
        ];
        $source = '继承会员等级默认规则：默认加价比例 ' . (float)$lv['markup_ratio'] . '%';
        if ($singleRuleId > 0) {
            $source = '商品已覆盖：使用商品专属加价规则';
        } elseif (abs($profitRatio - 100) > 0.0001) {
            $source = '商品已覆盖：默认加价比例 × 商品加价力度折扣 ' . $profitRatio . '%';
        } elseif ($profitRuleId > 0) {
            $source = '商品已覆盖：使用商品成本自动调节规则';
        } elseif (!empty($lv['profit_rule_id'])) {
            $source = '继承会员等级默认规则：默认加价比例 ' . (float)$lv['markup_ratio'] . '% + 成本自动调节';
        }
        $priceCents = (int)Level_Price::calculate($skuRow, $goodsRow, $lvId);
        $priceYuan = number_format($priceCents / 100, $accuracy, '.', '');
        $rows[] = [
            'level_id' => $lvId,
            'level_name' => $lv['name'] . '（#' . $lvId . '）',
            'price' => '¥' . $priceYuan,
            'source' => $source,
        ];
    }

    output::ok($rows);
}

if ($action == 'media_images') {
    $page = max(1, Input::getIntVar('page', 1));
    $perpage = 12;
    $keyword = addslashes(trim(Input::getStrVar('keyword', '')));
    $total = $Media_Model->getMediaCount(null, null, '', $keyword);
    $list = $Media_Model->getMedias($page, $perpage, 0, 0, '', $keyword);
    $images = [];
    foreach ($list as $m) {
        if (!preg_match('/image\//i', $m['mimetype'] ?? '')) continue;
        $images[] = [
            'aid'      => $m['aid'],
            'url'      => $m['filepath'],
            'thumb'    => $m['filepath_thum'] ?: $m['filepath'],
            'filename' => $m['filename'],
            'addtime'  => $m['addtime'],
        ];
    }
    die(json_encode(['code' => 0, 'data' => $images, 'total' => (int)$total, 'pages' => ceil($total / $perpage)], JSON_UNESCAPED_UNICODE));
}

if ($action == 'media_delete') {
    $aid = Input::postIntVar('aid');
    if (empty($aid)) { output::error('参数错误'); }
    $ret = $Media_Model->deleteMedia($aid);
    if ($ret) { output::ok(); } else { output::error('删除失败'); }
}

if($action == 'sku_data'){
    $goods_id = Input::getIntVar('goods_id', -1);
    if($goods_id < 0){
        die(json_encode(['code' => 200, 'data' => [], 'msg' => 'ok'], JSON_UNESCAPED_UNICODE));
    }
    $skus = $goodsModel->getGoodsSkusForAdmin($goods_id);
    die(json_encode(['code' => 200, 'data' => $skus, 'msg' => 'ok'], JSON_UNESCAPED_UNICODE));
}

/**
 * 获取商品类型
 */
if($action == 'goods_type_data'){

    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;

    $sql = "SELECT id, name as title FROM `{$db_prefix}goods_type` where hide = 'n' and delete_time is null ORDER BY id DESC";

    $result = $db->query($sql);

    $data = [];

    while ($row = $db->fetch_array($result)) {
        $row['title'] = htmlspecialchars($row['title']);
        $data[] = $row;
    }

    die(json_encode(['code' => 200, 'data' => $data, 'msg' => 'ok'], JSON_UNESCAPED_UNICODE));
}

/**
 * 商品编辑页内快速创建规格模板
 */
if($action == 'create_goods_type'){
    $title = Input::postStrVar('title');
    if(empty($title)){
        die(json_encode(['code' => 400, 'msg' => '请输入规格模板名称', 'data' => []], JSON_UNESCAPED_UNICODE));
    }

    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $is = $db->once_fetch_array("select COUNT(*) AS total from {$db_prefix}goods_type where name='{$title}' and hide='n' and delete_time is null");
    if($is && $is['total'] > 0){
        die(json_encode(['code' => 400, 'msg' => '规格模板已存在', 'data' => []], JSON_UNESCAPED_UNICODE));
    }

    $db->query("INSERT INTO {$db_prefix}goods_type (`name`, `hide`) VALUES ('{$title}', 'n')");
    $type_id = $db->insert_id();

    die(json_encode(['code' => 200, 'data' => [
        'id' => $type_id,
        'title' => htmlspecialchars(stripslashes($title))
    ], 'msg' => 'ok'], JSON_UNESCAPED_UNICODE));
}
if($action == 'attr_spec_data'){


    $goods_type_id = Input::getIntVar('goods_type_id');

    $goods_id = Input::getIntVar('goods_id', -1);

    $db = Database::getInstance();
    $sql = "SELECT id, title FROM `" . DB_PREFIX . "sku_attr` where type_id='{$goods_type_id}' and delete_time is null ORDER BY id ASC";
    $result = $db->query($sql);
    $specification = [];

    while ($row = $db->fetch_array($result)) {
        $row['title'] = htmlspecialchars($row['title']);
        $specification[] = $row;
    }

    $sql = "SELECT sku FROM `" . DB_PREFIX . "skus` where goods_id={$goods_id}";
    $result = $db->query($sql);
    $product_specification = [];
    while ($row = $db->fetch_array($result)) {
        $product_specification[] = $row;
    }

    if(!empty($specification)){
        $specification_ids = array_column($specification, 'id');
        $sql = "SELECT id, attr_id, name FROM `" . DB_PREFIX . "sku_value` where attr_id in(" . implode(',', $specification_ids) . ") and delete_time is null ORDER BY id ASC";
        $result = $db->query($sql);
        $specification_value = [];
        while ($row = $db->fetch_array($result)) {
            $row['title'] = htmlspecialchars($row['name']);
            unset($row['name']); // 移除原始name字段，只保留处理后的title
            $specification_value[] = $row;
        }

        foreach($specification as $key => $val){
            $specification[$key]['options'] = [];
            $specification[$key]['value'] = [];
            foreach($specification_value as $v){
                foreach($product_specification as $vs){
                    $spec = explode('-', $vs['sku']);
                    if(in_array($v['id'], $spec)){
                        $specification[$key]['value'][] = $v['id'];
                    }
                }
                if($val['id'] == $v['attr_id']){
                    $specification[$key]['options'][] = [
                        'id' => $v['id'],
                        'title' => $v['title']
                    ];
                }
            }
        }
    }



    die(json_encode(['code' => 200, 'data' => [
        'attribute' => $specification,
        'spec' => $specification
    ], 'msg' => 'ok'], JSON_UNESCAPED_UNICODE));

}

/**
 * 创建规格
 */
if($action == 'create_spec'){

    $specification = Input::postStrVar('title');
    $goods_type_id = Input::postIntVar('goods_type_id');
    if(empty($specification)){
        die(json_encode(['code' => 400, 'msg' => '请输入规格名称', 'data' => []], JSON_UNESCAPED_UNICODE));
    }
    if(empty($goods_type_id)){
        die(json_encode(['code' => 400, 'msg' => '请先选择规格模板', 'data' => []], JSON_UNESCAPED_UNICODE));
    }

    $kItem = ['type_id', 'title'];
    $dItem = [$goods_type_id, $specification];
    $field = implode(',', $kItem);
    $values = "'" . implode("','", $dItem) . "'";

    $db = Database::getInstance();

    $sql = "select COUNT(*) AS total from " . DB_PREFIX . "sku_attr where type_id='{$goods_type_id}' and title='{$specification}' and delete_time is null";
    $is = $db->once_fetch_array($sql);
    if($is['total'] > 0){
        die(json_encode(['code' => 400, 'msg' => '规格已存在', 'data' => []], JSON_UNESCAPED_UNICODE));
    }

    $sql = "INSERT INTO " . DB_PREFIX . "sku_attr ($field) VALUES ($values)";
    $db->query($sql);
    $specification_id = $db->insert_id();

    die(json_encode(['code' => 200, 'data' => [
        'id' => $specification_id
    ], 'msg' => 'ok'], JSON_UNESCAPED_UNICODE));


}

if($action == 'create_spec_value'){
    $name = Input::postStrVar('title');
    $specification_id = Input::postIntVar('spec_id');
    if(empty($name)){
        die(json_encode(['code' => 400, 'msg' => '请输入规格值', 'data' => []], JSON_UNESCAPED_UNICODE));
    }
    if(empty($specification_id)){
        die(json_encode(['code' => 400, 'msg' => '请选择规格名称', 'data' => []], JSON_UNESCAPED_UNICODE));
    }

    $kItem = ['attr_id', 'name'];
    $dItem = [$specification_id, $name];
    $field = implode(',', $kItem);
    $values = "'" . implode("','", $dItem) . "'";

    $db = Database::getInstance();

    $sql = "select COUNT(*) AS total from " . DB_PREFIX . "sku_value where attr_id='{$specification_id}' and name='{$name}'";
    $is = $db->once_fetch_array($sql);
    if($is['total'] > 0){
        die(json_encode(['code' => 400, 'msg' => '规格值已存在', 'data' => []], JSON_UNESCAPED_UNICODE));
    }

    $sql = "INSERT INTO " . DB_PREFIX . "sku_value ($field) VALUES ($values)";
    $db->query($sql);
    $specification_value_id = $db->insert_id();

    die(json_encode(['code' => 200, 'data' => [
        'id' => $specification_value_id
    ], 'msg' => 'ok'], JSON_UNESCAPED_UNICODE));
}

// 回收站相关逻辑已迁移至 admin/goods_recycle.php 独立控制器
