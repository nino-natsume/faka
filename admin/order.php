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

$orderModel = new Order_Model();
$Sort_Model = new Sort_Model();
$User_Model = new User_Model();
$MediaSort_Model = new MediaSort_Model();
$Template_Model = new Template_Model();

function admin_order_table_exists($db, $table) {
    static $cache = [];
    $table = trim((string)$table);
    if ($table === '') return false;
    if (isset($cache[$table])) return $cache[$table];
    $row = $db->once_fetch_array("SHOW TABLES LIKE '" . $db->escape_string($table) . "'");
    $cache[$table] = !empty($row);
    return $cache[$table];
}

function admin_order_mapping_exists($db, $db_prefix, $table, $goodsId) {
    if (!admin_order_table_exists($db, $db_prefix . $table)) return false;
    $row = $db->once_fetch_array("select id from {$db_prefix}{$table} where goods_id=" . (int)$goodsId . " limit 1");
    return !empty($row);
}

function admin_order_sale_exists($db, $db_prefix, $table, $orderListId) {
    if (!admin_order_table_exists($db, $db_prefix . $table)) return false;
    $row = $db->once_fetch_array("select id from {$db_prefix}{$table} where order_list_id=" . (int)$orderListId . " limit 1");
    return !empty($row);
}

function admin_order_goods_type($db, $db_prefix, $goodsId, $goods = [], $childOrder = []) {
    $goodsId = (int)$goodsId;
    if (($goods['type'] ?? '') === 'physical') return 'physical';
    if ($goodsId > 0) {
        if (admin_order_mapping_exists($db, $db_prefix, 'qingjiu_goods', $goodsId)) return 'qingjiu';
        if (admin_order_mapping_exists($db, $db_prefix, 'xiaoqing_goods', $goodsId)) return 'xiaoqing';
        if (admin_order_mapping_exists($db, $db_prefix, 'docking_goods', $goodsId)) return 'docking';
        if (admin_order_mapping_exists($db, $db_prefix, 'yiciyuan_goods', $goodsId)) return 'yiciyuan';
        if (admin_order_mapping_exists($db, $db_prefix, 'mcy_goods', $goodsId)) return 'mcy';
    }
    if (!empty($childOrder['id'])) {
        $orderListId = (int)$childOrder['id'];
        if (admin_order_sale_exists($db, $db_prefix, 'qingjiu_sale', $orderListId)) return 'qingjiu';
        if (admin_order_sale_exists($db, $db_prefix, 'xiaoqing_sale', $orderListId)) return 'xiaoqing';
        if (admin_order_sale_exists($db, $db_prefix, 'docking_sale', $orderListId)) return 'docking';
        if (admin_order_sale_exists($db, $db_prefix, 'yiciyuan_sale', $orderListId)) return 'yiciyuan';
        if (admin_order_sale_exists($db, $db_prefix, 'mcy_sale', $orderListId)) return 'mcy';
    }
    if (!empty($childOrder['order_id'])) {
        $orderId = (int)$childOrder['order_id'];
        $order = $db->once_fetch_array("select * from {$db_prefix}order where id={$orderId} limit 1");
        if (!empty($order['qingjiu_err_msg'])) return 'qingjiu';
        if (!empty($order['xiaoqing_err_msg'])) return 'xiaoqing';
        if (!empty($order['docking_err_msg'])) return 'docking';
        if (!empty($order['yiciyuan_err_msg'])) return 'yiciyuan';
        if (!empty($order['mcy_err_msg'])) return 'mcy';
    }
    return $goods['type'] ?? '';
}

// 订单列表
if (empty($action)) {
    $page = Input::getIntVar('page', 1);
    $popup = !empty($_GET['popup']);

    if ($popup) {
        include View::getAdmView('open_head');
        require_once View::getAdmView(User::haveEditPermission() ? 'order' : 'uc_order');
        include View::getAdmView('open_foot');
    } else {
        $br = '<a href="./">数据中心</a><a href="./order.php">订单管理</a><a><cite>商品订单</cite></a>';
        include View::getAdmView(User::haveEditPermission() ? 'header' : 'uc_header');
        require_once View::getAdmView(User::haveEditPermission() ? 'order' : 'uc_order');
        include View::getAdmView(User::haveEditPermission() ? 'footer' : 'uc_footer');
    }
    View::output();
}



if ($action == 'index') {
    // 自动取消超时未支付的订单（时间可在后台商城配置中设置）
    $db_auto = Database::getInstance();
    $_cpt = intval(Option::get('continue_pay_timeout'));
    $_cpt = $_cpt > 0 ? $_cpt : 30;
    $expire_time = time() - $_cpt * 60;
    $db_auto->query("UPDATE " . DB_PREFIX . "order SET status = 3 WHERE status = 0 AND create_time < {$expire_time} AND delete_time IS NULL");

    $page = Input::getIntVar('page', 1);
    $limit = Input::getIntVar('limit', 10);
    $start = ($page - 1) * $limit;
    $sort1 = Input::getStrVar('field', 'uid');
    $sort2 = Input::getStrVar('order', 'desc');
    $order_by = "order by {$sort1} {$sort2}";


    $where  = [];
    $where['email_username'] = Input::getStrVar('email_username');
    $where['out_trade_no'] = Input::getStrVar('out_trade_no');
    $where['goods_title'] = Input::getStrVar('goods_title');
    $where['client_ip'] = Input::getStrVar('client_ip');
    $where['order_required'] = Input::getStrVar('order_required');
    $where['kami_content'] = Input::getStrVar('kami_content'); // 卡密反查
    $where['filter_uid'] = Input::getIntVar('filter_uid'); // 按用户ID筛选
    $where['status'] = Input::getStrVar('status'); // 订单状态筛选
    // 特殊处理：如果传入的是 '0'，需要保留
    if (isset($_GET['status']) && $_GET['status'] !== '') {
        $where['status'] = $_GET['status'];
    }

    $orderNum = $orderModel->getOrderNum($where);
    $order = $orderModel->getOrderForAdmin($start, $limit, $where);
    
    // 支付插件名称映射
    $paymentNames = [
        'balance' => '余额支付',
        'epay_wx' => '易支付(微信)',
        'epay_ali' => '易支付(支付宝)',
        'epay_ali2' => '易支付(支付宝)',
        'epay_qq' => '易支付(QQ钱包)',
        'epay_jj' => '易支付(京东)',
        'epay_jj2' => '易支付(京东)',
        'alipay' => '支付宝',
        'wxpay' => '微信支付',
        'ynl_wx' => '微信支付',
        'ynl_ali' => '支付宝',
    ];
    
    // 动态获取支付插件的显示名称
    $payPlugins = ['manual_pay', 'ynl_wx', 'ynl_ali', 'epay'];
    foreach ($payPlugins as $pluginName) {
        $pluginStorage = Storage::getInstance($pluginName);
        $displayName = $pluginStorage->getValue('display_name');
        if (!empty($displayName)) {
            $paymentNames[$pluginName] = $displayName;
        }
    }
    
    foreach($order as $key => $val){
        $order[$key]['pay_time'] = empty($val['pay_time']) ? '' : date('Y-m-d H:i:s', $val['pay_time']);
        $order[$key]['amount'] = number_format($val['amount'], 2);
        // 处理支付方式显示
        if (empty($val['payment']) && !empty($val['pay_plugin'])) {
            $order[$key]['payment'] = $paymentNames[$val['pay_plugin']] ?? $val['pay_plugin'];
        }
    }
    output::data($order, $orderNum);
}

if($action == 'get_deliver'){
    $order_id = Input::getIntVar('order_id');
    $order_list_id = Input::getIntVar('order_list_id');
    $db = Database::getInstance();
    $sql = "select * from " . DB_PREFIX . "order where id={$order_id}";
    $order = $db->once_fetch_array($sql);
    $sql = "select * from " . DB_PREFIX . "deliver where order_list_id={$order_list_id} limit 5";
    $deliver = $db->fetch_all($sql);

    $order_deliver = "";
    if(empty($order['device'])){
        if (!empty($deliver)) {
            foreach($deliver as $val){
                if(!empty($order_deliver)){
                    $order_deliver .= "<hr>";
                }
                $order_deliver .= $val['content'];
            }
        } else {
            // 尝试读取同系统对接的卡密
            $docking_sale = $db->once_fetch_array("SELECT content FROM " . DB_PREFIX . "docking_sale WHERE order_list_id = {$order_list_id} LIMIT 1");
            if ($docking_sale && !empty($docking_sale['content'])) {
                $order_deliver = nl2br(htmlspecialchars($docking_sale['content']));
            }
        }
    }else{
        $order_deliver = $order['device'];
    }
    $order_deliver = empty($order_deliver) ? '无' : $order_deliver;

    $sql = "select * from " . DB_PREFIX . "order_required where order_id={$order_id}";
    $order_required = $db->fetch_all($sql);

    $data = [
        'order_deliver' => $order_deliver,
        'order_required' => $order_required
    ];

    output::ok($data);

}

if($action == 'download'){
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $goods_type = Input::getStrVar('goods_type');
    $order_list_id = Input::getIntVar('order_list_id');
    $func = "adm_download_deliver_content_{$goods_type}";


    if (function_exists($func)) {
        $func($db, $db_prefix, $order_list_id);
    }
}

// 导出订单
if ($action == 'export') {
    $where = [];
    $where['email_username'] = Input::getStrVar('email_username');
    $where['out_trade_no'] = Input::getStrVar('out_trade_no');
    $where['goods_title'] = Input::getStrVar('goods_title');
    $where['client_ip'] = Input::getStrVar('client_ip');
    $where['order_required'] = Input::getStrVar('order_required');
    $where['kami_content'] = Input::getStrVar('kami_content');
    $where['filter_uid'] = Input::getIntVar('filter_uid');
    $where['status'] = '';
    if (isset($_GET['status']) && $_GET['status'] !== '') {
        $where['status'] = $_GET['status'];
    }

    $total = $orderModel->getOrderNum($where);
    $maxExport = 5000;
    $exportLimit = min($total, $maxExport);
    $orders = $orderModel->getOrderForAdmin(0, $exportLimit, $where);

    $paymentNames = [
        'balance' => '余额支付',
        'epay_wx' => '易支付(微信)', 'epay_ali' => '易支付(支付宝)', 'epay_qq' => '易支付(QQ钱包)',
        'alipay' => '支付宝', 'wxpay' => '微信支付', 'ynl_wx' => '微信支付', 'ynl_ali' => '支付宝',
    ];
    $payPlugins = ['manual_pay', 'ynl_wx', 'ynl_ali', 'epay'];
    foreach ($payPlugins as $pluginName) {
        $pluginStorage = Storage::getInstance($pluginName);
        $displayName = $pluginStorage->getValue('display_name');
        if (!empty($displayName)) $paymentNames[$pluginName] = $displayName;
    }

    $filename = '商品订单_' . date('YmdHis') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $fp = fopen('php://output', 'w');
    fwrite($fp, "\xEF\xBB\xBF");
    fputcsv($fp, ['订单号', '商户订单号', '商品名称', '规格', '数量', '订单金额', '用户昵称', '用户邮箱', '用户手机', '订单状态', '支付方式', '下单IP', '下单时间', '支付时间']);

    foreach ($orders as $row) {
        $goodsTitles = [];
        $goodsSpecs = [];
        $goodsQtys = [];
        if (!empty($row['list'])) {
            foreach ($row['list'] as $item) {
                $goodsTitles[] = $item['title'] ?: '未知商品';
                $goodsSpecs[] = $item['attr_spec'] ?: '默认规格';
                $goodsQtys[] = $item['quantity'];
            }
        }
        $payment = '';
        if (!empty($row['payment'])) {
            $payment = $row['payment'];
        } elseif (!empty($row['pay_plugin'])) {
            $payment = $paymentNames[$row['pay_plugin']] ?? $row['pay_plugin'];
        }
        fputcsv($fp, [
            $row['out_trade_no'],
            $row['up_no'] ?: '',
            implode(' | ', $goodsTitles),
            implode(' | ', $goodsSpecs),
            implode(' | ', $goodsQtys),
            number_format($row['amount'], 2),
            $row['user_nickname'] ?: '',
            $row['user_email'] ?: '',
            $row['user_tel'] ?: '',
            $row['status_text'],
            $payment,
            $row['client_ip'] ?: '',
            empty($row['create_time']) ? '' : date('Y-m-d H:i:s', $row['create_time']),
            empty($row['pay_time']) ? '' : date('Y-m-d H:i:s', $row['pay_time']),
        ]);
    }
    fclose($fp);
    exit;
}

// 补单
if($action == 'repay'){
	
//	LoginAuth::checkToken();
    try {
        $out_trade_no = Input::postStrVar('out_trade_no');
        $db = Database::getInstance();
        $repayOrder = $db->once_fetch_array("SELECT * FROM " . DB_PREFIX . "order WHERE out_trade_no = '" . addslashes($out_trade_no) . "'");
        $payController = new Pay_Controller();
        $payController->repay($out_trade_no);
        if ($repayOrder) {
            User_Log_Model::log($repayOrder['user_id'], 'order_repay', '后台补单，订单号: ' . $out_trade_no . '，金额: ¥' . ($repayOrder['amount'] / 100), $repayOrder['amount'] / 100);
        }
        output::ok();
    } catch (\Throwable $e) {
        output::error('补单失败: ' . $e->getMessage());
    }
}

// 删除订单
if ($action == 'del') {

    $ids = Input::postStrVar('ids');

    $timestamp = time();
    $sql = "UPDATE " . DB_PREFIX . "order set delete_time={$timestamp} where id IN ({$ids})";

    $db = Database::getInstance();
    $db->query($sql);
    output::ok();

}

// 清理已取消订单
if ($action == 'clean_cancelled') {
    LoginAuth::checkToken();
    $days = Input::postIntVar('days', 180);
    if (!in_array($days, [30, 90, 180, 365])) $days = 180;
    $before = time() - $days * 86400;
    $timestamp = time();
    $db = Database::getInstance();
    $prefix = DB_PREFIX;
    $sql = "UPDATE {$prefix}order SET delete_time = {$timestamp} WHERE status = 3 AND delete_time IS NULL AND create_time < {$before} AND (device IS NULL OR device != 'balance_recharge')";
    $db->query($sql);
    $affected = $db->affected_rows();
    output::ok(['count' => $affected]);
}

// 手动发货
if ($action == 'deliver_ajax') {
    $id = Input::postIntVar('id');
    LoginAuth::checkToken();
    $remark = Input::postStrVar('remark');
    $orderModel->handDeliver($id, $remark);
    output::ok();
}

// 重新尝试自动对接下单发货
if ($action == 'retry_docking_ajax') {
    $id = Input::postIntVar('id');
    LoginAuth::checkToken();
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $order = $db->once_fetch_array("SELECT * FROM `{$db_prefix}order` WHERE id = {$id}");
    $child_order = $db->once_fetch_array("SELECT * FROM `{$db_prefix}order_list` WHERE order_id = {$id}");
    $goods = $db->once_fetch_array("SELECT * FROM `{$db_prefix}goods` WHERE id = " . (int)($child_order['goods_id'] ?? 0));
    
    if (empty($order) || empty($child_order) || empty($goods)) {
        output::error('订单或对接商品数据已丢失');
    }

    $retryType = Input::postStrVar('retry_type');
    $allowedRetryTypes = ['qingjiu', 'yiciyuan', 'mcy', 'docking'];
    if (!in_array($retryType, $allowedRetryTypes, true)) {
        output::error('重试入口参数错误');
    }

    $isQingjiu = null;
    try {
        $isQingjiu = $db->once_fetch_array("SELECT id FROM `{$db_prefix}qingjiu_goods` WHERE goods_id = " . (int)$child_order['goods_id'] . " LIMIT 1");
    } catch (Throwable $e) {
        $isQingjiu = null;
    }
    if ($retryType === 'docking' && !empty($isQingjiu)) {
        $retryType = 'qingjiu';
    }
    if ($retryType === 'qingjiu') {
        if (empty($isQingjiu)) {
            output::error('当前商品不是晴玖对接商品，不能使用晴玖重试入口');
        }
        try {
            $db->query("UPDATE `{$db_prefix}order` SET docking_err_msg = '', qingjiu_err_msg = '' WHERE id = {$id}");
        } catch (Throwable $e) {
            $db->query("UPDATE `{$db_prefix}order` SET docking_err_msg = '' WHERE id = {$id}");
        }
        if (function_exists('plugin_goods_qingjiu_deliver')) {
            plugin_goods_qingjiu_deliver($db, $db_prefix, $goods, $order, $child_order);
        } else {
            output::error('晴玖对接插件未启用或核心发货函数不存在');
        }
        $updatedOrder = $db->once_fetch_array("SELECT * FROM `{$db_prefix}order` WHERE id = {$id}");
        if (empty($isQingjiu) && empty($updatedOrder['qingjiu_err_msg']) && empty($updatedOrder['docking_err_msg'])) {
            output::error('当前商品未找到晴玖对接映射，不能使用晴玖重试入口');
        }
        if (!empty($updatedOrder['docking_err_msg']) || !empty($updatedOrder['qingjiu_err_msg'])) {
            output::error($updatedOrder['docking_err_msg'] ?: $updatedOrder['qingjiu_err_msg']);
        }
        output::ok(['message' => '已重新向晴玖提交对接下单请求']);
    }

    $isYiciyuan = null;
    try {
        $isYiciyuan = $db->once_fetch_array("SELECT id FROM `{$db_prefix}yiciyuan_goods` WHERE goods_id = " . (int)$child_order['goods_id'] . " LIMIT 1");
    } catch (Throwable $e) {
        $isYiciyuan = null;
    }
    if ($retryType === 'docking' && !empty($isYiciyuan)) {
        $retryType = 'yiciyuan';
    }
    if ($retryType === 'yiciyuan') {
        if (empty($isYiciyuan)) {
            output::error('当前商品不是异次元对接商品，不能使用异次元重试入口');
        }
        $oldStatus = (int)($order['status'] ?? 0);
        $db->query("DELETE FROM `{$db_prefix}yiciyuan_sale` WHERE order_list_id = " . (int)$child_order['id']);
        $child_order['_yiciyuan_retry_nonce'] = time() . '_' . mt_rand(1000, 9999);
        try {
            $db->query("UPDATE `{$db_prefix}order` SET docking_err_msg = '', yiciyuan_err_msg = '' WHERE id = {$id}");
        } catch (Throwable $e) {
            $db->query("UPDATE `{$db_prefix}order` SET docking_err_msg = '' WHERE id = {$id}");
        }
        if (function_exists('plugin_goods_yiciyuan_deliver')) {
            plugin_goods_yiciyuan_deliver($db, $db_prefix, $goods, $order, $child_order);
        } else {
            output::error('异次元对接插件未启用或核心发货函数不存在');
        }
        $updatedOrder = $db->once_fetch_array("SELECT * FROM `{$db_prefix}order` WHERE id = {$id}");
        if (!empty($updatedOrder['docking_err_msg']) || !empty($updatedOrder['yiciyuan_err_msg'])) {
            if ($oldStatus > 0 && (int)($updatedOrder['status'] ?? 0) !== $oldStatus) {
                $db->query("UPDATE `{$db_prefix}order` SET status = {$oldStatus} WHERE id = {$id}");
            }
            output::error($updatedOrder['docking_err_msg'] ?: $updatedOrder['yiciyuan_err_msg']);
        }
        output::ok(['message' => '已重新向异次元提交对接下单请求']);
    }

    $isDocking = null;
    try {
        $isDocking = $db->once_fetch_array("SELECT id FROM `{$db_prefix}docking_goods` WHERE goods_id = " . (int)$child_order['goods_id'] . " LIMIT 1");
    } catch (Throwable $e) {
        $isDocking = null;
    }
    if ($retryType !== 'docking') {
        output::error('重试入口参数错误');
    }
    if (empty($isDocking)) {
        output::error('当前商品不是同系统对接商品，不能使用同系统重试入口');
    }
    
    // 清除错误信息，防止干扰新一次的发货尝试
    $db->query("UPDATE `{$db_prefix}order` SET docking_err_msg = '' WHERE id = {$id}");
    
    // 动态执行对接插件的 deliver 方法
    if (function_exists('plugin_goods_docking_deliver')) {
        plugin_goods_docking_deliver($db, $db_prefix, $goods, $order, $child_order);
    } else {
        output::error('对接插件未启用或核心发货函数不存在');
    }
    
    // 获取最新的订单信息
    $updatedOrder = $db->once_fetch_array("SELECT * FROM `{$db_prefix}order` WHERE id = {$id}");
    if (!empty($updatedOrder['docking_err_msg'])) {
        output::error($updatedOrder['docking_err_msg']);
    }
    
    output::ok(['message' => '已重新向同系统货源提交对接下单请求']);
}

if ($action == 'deliver') {
    $order_id = Input::getIntVar('order_id');
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $order = $db->once_fetch_array("select * from {$db_prefix}order where id = {$order_id}");
    $child_order = $db->once_fetch_array("select * from {$db_prefix}order_list where order_id = {$order_id}");
    $goods = $db->once_fetch_array("select * from {$db_prefix}goods where id = {$child_order['goods_id']}");
    include View::getAdmView('open_head');
    doAction('adm_deliver_view', $db, $db_prefix, $goods, $order, $child_order);
    include View::getAdmView('open_foot');
    View::output();
}

if ($action == 'detail') {
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;

    $order_id = Input::getIntVar('order_id');
    $sql = "SELECT * FROM `{$db_prefix}order` where id={$order_id}";
    $order = $db->once_fetch_array($sql);
    $order['amount'] = number_format($order['amount'] / 100, 2);
    $order['status'] = orderStatusText($order['status']);

    $sql = "SELECT * FROM `{$db_prefix}user` where uid={$order['user_id']}";
    $user = $db->once_fetch_array($sql);

    $sql = "SELECT * FROM `{$db_prefix}order_list` where order_id={$order['id']}";
    $order_lists = $db->fetch_all($sql);
    
    // 处理所有订单子项
    $all_order_lists = [];
    foreach ($order_lists as $ol) {
        $ol['unit_price'] = number_format($ol['unit_price'] / 100, 2);
        $ol['is_gift'] = ($ol['price'] == 0 && strpos($ol['attr_spec'], '[赠品]') !== false);
        $ol['attr_spec_display'] = $ol['is_gift'] ? str_replace('[赠品] ', '', $ol['attr_spec']) : $ol['attr_spec'];
        $ol['attach_user_display'] = '无';
        
        if (!empty($ol['attach_user'])) {
            $attach_user = json_decode($ol['attach_user'], true);
            if (!empty($attach_user)) {
                $ol['attach_user_display'] = '';
                foreach ($attach_user as $key => $val) {
                    $ol['attach_user_display'] .= $key . '：' . $val . '；';
                }
            }
        }
        
        // 获取商品信息
        $goods_id = (int)$ol['goods_id'];
        $ol['goods'] = $goods_id > 0 ? $db->once_fetch_array("SELECT * FROM `{$db_prefix}goods` where id={$goods_id}") : null;
        if (empty($ol['goods'])) {
            $ol['goods'] = ['id' => $ol['goods_id'], 'title' => '（商品已删除）', 'type' => '', 'is_sku' => 'n', 'stock' => 0];
        }
        
        // 获取发货信息
        $goodsType = admin_order_goods_type($db, $db_prefix, $goods_id, $ol['goods'], $ol);
        $ol['goods']['type'] = $goodsType ?: ($ol['goods']['type'] ?? '');
        $fun = "plugin_goods_{$goodsType}_adm_order_detail";
        if (!empty($goodsType) && function_exists($fun)) {
            $ol['deliver_content'] = $fun($db, $db_prefix, $ol['goods'], $ol);
        } else {
            $ol['deliver_content'] = '无';
        }
        
        $all_order_lists[] = $ol;
    }
    
    // 兼容旧代码，取第一个作为主商品
    $order_list = $order_lists[0];
    $order_list['unit_price'] = number_format($order_list['unit_price'] / 100, 2);
    $order_list['attach_user'] = empty($order_list['attach_user']) ? '无' : $order_list['attach_user'];
    if(empty($order_list['attach_user'])){
        $order_list['attach_user'] = '无';
    }else{
        $attach_user = json_decode($order_list['attach_user'], true);
        $order_list['attach_user'] = '';
        if(empty($attach_user)){
            $order_list['attach_user'] = '无';
        }else{
            foreach($attach_user as $key => $val){
                $order_list['attach_user'] .= $key . '：' . $val . '；';
            }
        }
    }

    $legacy_goods_id = (int)$order_list['goods_id'];
    $goods = $legacy_goods_id > 0 ? $db->once_fetch_array("SELECT * FROM `{$db_prefix}goods` where id={$legacy_goods_id}") : null;
    // 商品已被彻底删除时，构造基本信息以防报错
    if (empty($goods)) {
        $goods = [
            'id' => $order_list['goods_id'],
            'title' => '（商品已删除）',
            'type' => '',
            'is_sku' => 'n',
            'stock' => 0,
        ];
    }

    // 获取下单必填项
    $sql = "SELECT * FROM `{$db_prefix}order_required` where order_id={$order['id']}";
    $res = $db->fetch_all($sql);
    $order_required = '';
    if(empty($res)){
        $order_required = '无';
    }else{
        foreach($res as $val){
            $order_required .= $val['name'] . '：' . $val['content'] . '；';
        }
    }

    $data = [
        'db' => $db,
        'db_prefix' => $db_prefix,
        'goods' => $goods,
        'child_order' => $order_list,
    ];

    $goodsType = admin_order_goods_type($db, $db_prefix, $goods['id'], $goods, $order_list);
    $fun = "plugin_goods_{$goodsType}_adm_order_detail";
    if (!empty($goodsType) && function_exists($fun)) {
        $order_deliver = $fun($db, $db_prefix, $goods, $order_list);
    }


    include View::getAdmView('open_head');
    require_once View::getAdmView('order_detail');
    include View::getAdmView('open_foot');
    View::output();
}
