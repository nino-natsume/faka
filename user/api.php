<?php



$resp = 'local';

if (!defined('DC_ROOT')) {
    require_once '../init.php';
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $action = Input::getStrVar('action');
    $_func = 'api_' . $action;
    $resp = 'api';
    
    if (function_exists($_func)) {
        $_func();
    } else {
        Ret::error('不支持的请求');
    }
    exit;
}

$db = Database::getInstance();
$db_prefix = DB_PREFIX;

function api_table_exists($table) {
    global $db;
    $table = trim((string)$table);
    if ($table === '' || !preg_match('/^[a-zA-Z0-9_]+$/', $table)) return false;
    $row = $db->once_fetch_array("SHOW TABLES LIKE '" . $db->escape_string($table) . "'");
    return !empty($row);
}

function api_goods_mapping_exists($table, $goodsId) {
    global $db, $db_prefix;
    $goodsId = (int)$goodsId;
    if ($goodsId <= 0 || !preg_match('/^[a-zA-Z0-9_]+$/', (string)$table)) return false;
    $tableName = $db_prefix . $table;
    if (!api_table_exists($tableName)) return false;
    return (bool)$db->once_fetch_array("select id from `{$tableName}` where goods_id={$goodsId} limit 1");
}

function api_resolve_goods_type($goodsId, $goods = []) {
    $goodsId = (int)$goodsId;
    if ($goodsId <= 0) return $goods['type'] ?? 'general';
    if (api_goods_mapping_exists('qingjiu_goods', $goodsId)) return 'qingjiu';
    if (api_goods_mapping_exists('xiaoqing_goods', $goodsId)) return 'xiaoqing';
    if (api_goods_mapping_exists('docking_goods', $goodsId)) return 'docking';
    if (api_goods_mapping_exists('yiciyuan_goods', $goodsId)) return 'yiciyuan';
    return $goods['type'] ?? 'general';
}







/**
 * 获取订单的发货内容
 * @post.out_trade_no 订单编号
 * @post.order_list_id 订单子项ID（可选，用于获取特定商品的卡密）
 * @post.limit 获取数量，为空时获取全部
 */
function api_get_order_serect($post = []){
    global $db, $db_prefix, $resp;
    if(empty($post)){
        $out_trade_no = Input::postStrVar('out_trade_no');
        $order_list_id = Input::postIntVar('order_list_id');
        $limit = Input::postIntVar('limit');
    }else{
        $out_trade_no = $post['out_trade_no'];
        $order_list_id = isset($post['order_list_id']) ? intval($post['order_list_id']) : 0;
        $limit = $post['limit'];
    }
    $order = $db->once_fetch_array("select * from {$db_prefix}order where out_trade_no = '{$out_trade_no}'");
    
    // 如果指定了order_list_id，则获取特定的子订单；否则获取第一个
    if ($order_list_id > 0) {
        $child_order = $db->once_fetch_array("select * from {$db_prefix}order_list where id = {$order_list_id} and order_id = '{$order['id']}'");
    } else {
        $child_order = $db->once_fetch_array("select * from {$db_prefix}order_list where order_id = '{$order['id']}'");
    }
    
    if (empty($child_order)) {
        if($resp == 'api'){
            Ret::error('订单不存在');
        }else{
            return ['code' => 400, 'msg' => '订单不存在'];
        }
    }
    
    $goods = $db->once_fetch_array("select * from {$db_prefix}goods where id={$child_order['goods_id']}");
    $goodsType = api_resolve_goods_type($child_order['goods_id'], $goods);
    $func = "plugin_goods_{$goodsType}_get_order_serect";
    $res = $func($db, $db_prefix, $goods, $order, $child_order, $limit);
    // 添加商品的使用说明(pay_content)到返回数据
    $res['pay_content'] = isset($goods['pay_content']) ? $goods['pay_content'] : '';
    if($resp == 'api'){
        Ret::success('success', $res);
    }else{
        return ['code' => 200, 'data' => $res];
    }
}

/**
 * 获取登录用户的订单列表
 */
function api_get_user_order($post = []){
    global $db, $db_prefix, $resp;
    if(empty($post)){

    }else{
        $user_id = $post['user_id'];
    }


    $sql = "SELECT DISTINCT o.id, ol.id as order_list_id, ol.goods_id, g.type, 
o.out_trade_no, o.up_no, g.title, o.create_time, o.pay_time, o.status, o.amount, ol.quantity, ol.attr_spec, 
ol.attach_user, ol.unit_price, ol.price as item_price, ol.cost_price, o.payment, o.pay_plugin, g.cover 
FROM {$db_prefix}order o 
INNER JOIN {$db_prefix}order_list ol ON o.id = ol.order_id 
LEFT JOIN {$db_prefix}goods g on ol.goods_id = g.id 
where o.user_id = {$user_id} and o.delete_time is null
";

    $sql .= " order by o.id desc";
//    echo $sql;die;
    $res = $db->fetch_all($sql);

    foreach($res as $key => $val){
        $_text = empty($val['attach_user']) ? [] : json_decode($val['attach_user']);
        $res[$key]['attach_user_text'] = '';
        foreach($_text as $k => $v){
            $res[$key]['attach_user_text'] .= $k . "：" . $v . "；";
        }
        $res[$key]['amount'] = number_format($val['amount'] / 100, 2);
        $res[$key]['goods_url'] = Url::goods($val['goods_id']);
        $res[$key]['url'] = Url::goods($val['goods_id']);
//        $res[$key]['status_text'] =
        $res[$key]['pay_time_text'] = empty($val['pay_time']) ? '未付款' : date('Y-m-d H:i:s', $val['pay_time']);
        $res[$key]['status_text'] = orderStatusText($val['status']);
        $res[$key]['is_gift'] = ($val['item_price'] == 0 && strpos((string)$val['attr_spec'], '[赠品]') !== false);
        $res[$key]['unit_price_yuan'] = number_format($val['unit_price'] / 100, 2);
        if (empty($val['payment']) && !empty($val['pay_plugin'])) {
            $res[$key]['payment'] = $val['pay_plugin'];
        }
        $res[$key]['is_refunding'] = false;
    }

    // 批量查询活跃售后，覆盖 status_text 和 is_refunding 标记
    $active_plugins = Option::get('active_plugins');
    $aftersale_enabled = is_array($active_plugins) && in_array('aftersale/aftersale.php', $active_plugins);
    if ($aftersale_enabled && !empty($res)) {
        $_olIds = array_filter(array_column($res, 'order_list_id'));
        if (!empty($_olIds)) {
            $_olIdsStr = implode(',', array_map('intval', $_olIds));
            $_refRows = $db->fetch_all("SELECT DISTINCT order_list_id FROM {$db_prefix}aftersale WHERE order_list_id IN ({$_olIdsStr}) AND status IN (0, 1)");
            $_refSet = [];
            foreach ($_refRows as $_rr) { $_refSet[(int)$_rr['order_list_id']] = true; }
            foreach ($res as $key => $val) {
                if (isset($_refSet[(int)$val['order_list_id']])) {
                    $res[$key]['is_refunding'] = true;
                    $res[$key]['status_text'] = '售后中';
                }
            }
        }
    }

    if($resp == 'api'){
        Ret::success('success', $res);
    }else{
        return ['code' => 200, 'data' => $res];
    }
}

function api_extract_physical_order_phones($attach_user) {
    if (empty($attach_user)) {
        return [];
    }
    $data = json_decode($attach_user, true);
    if (!is_array($data)) {
        return [];
    }
    $phone_keys = ['手机号', '手机号码', '收货手机号', '收货电话', '联系电话', 'phone', 'mobile'];
    $phones = [];
    foreach ($data as $key => $value) {
        if (!in_array((string)$key, $phone_keys, true)) {
            continue;
        }
        if (is_scalar($value)) {
            $phones[] = (string)$value;
        }
    }
    return $phones;
}

function api_normalize_physical_phone_query_value($value) {
    return preg_replace('/\D+/', '', (string)$value);
}

function api_is_physical_visitor_direct_number_query($row, $query) {
    $query = (string)$query;
    return $query !== '' && (
        (string)($row['out_trade_no'] ?? '') === $query ||
        (string)($row['up_no'] ?? '') === $query
    );
}

function api_is_physical_visitor_order_allowed($row, $query) {
    if (api_is_physical_visitor_direct_number_query($row, $query)) {
        return true;
    }
    $query_phone = api_normalize_physical_phone_query_value($query);
    if ($query_phone === '') {
        return false;
    }
    foreach (api_extract_physical_order_phones($row['attach_user'] ?? '') as $phone) {
        if (api_normalize_physical_phone_query_value($phone) === $query_phone) {
            return true;
        }
    }
    return false;
}

function api_filter_physical_visitor_order_rows($rows, $query) {
    if (empty($rows)) {
        return [];
    }
    $filtered = [];
    foreach ($rows as $row) {
        if (($row['type'] ?? '') === 'physical' && !api_is_physical_visitor_order_allowed($row, $query)) {
            continue;
        }
        $filtered[] = $row;
    }
    return $filtered;
}

/**
 * 获取游客订单列表
 */
function api_get_visitors_order($search = []){
    global $db, $db_prefix, $resp;
    if(empty($search)){
        $out_trade_no = Input::postStrVar('out_trade_no');
        $required = Input::postStrArray('required');
        $attach = Input::postStrVar('attach');
    }else{
        $out_trade_no = $search['out_trade_no'];
        $required = $search['required'];
        $attach = $search['attach'];
    }

    if(empty($out_trade_no)){
        if($resp == 'api'){
            Ret::error('请输入查询内容');
        }else{
            return ['code' => 400, 'msg' => '请输入查询内容'];
        }
    }

    $where = "where o.delete_time is null and (";
    if(!empty($out_trade_no)){
        $where .= " (o.out_trade_no = '{$out_trade_no}' or o.up_no = '{$out_trade_no}') or";
        $where .= " ol.attach_user like '%:\"{$out_trade_no}\"%' or";
        $where .= " orq.content = '{$out_trade_no}' or";
    }
    $where = trim($where, "or");
    $where .= ")";

    $sql = "SELECT DISTINCT o.id, ol.id as order_list_id, ol.goods_id, g.type, 
o.out_trade_no, o.up_no, g.title, o.create_time, o.pay_time, o.status, o.amount, ol.quantity, ol.attr_spec, 
ol.attach_user, ol.unit_price, ol.price as item_price, ol.cost_price, o.payment, o.pay_plugin, g.cover 
FROM {$db_prefix}order o 
INNER JOIN {$db_prefix}order_list ol ON o.id = ol.order_id 
LEFT JOIN {$db_prefix}goods g on ol.goods_id = g.id ";
        $sql .= " left JOIN {$db_prefix}order_required orq ON o.id = orq.order_id {$where}";

    $sql .= " order by o.id desc, ol.price desc ";
//    echo $sql;die;
    $res = $db->fetch_all($sql);
    $res = api_filter_physical_visitor_order_rows($res, $out_trade_no);

//    var_dump($orq_num);die;
//    d($res);die;

    foreach($res as $key => $val){
        $_text = empty($val['attach_user']) ? [] : json_decode($val['attach_user']);
        $res[$key]['attach_user_text'] = '';
        foreach($_text as $k => $v){
            $res[$key]['attach_user_text'] .= $k . "：" . $v . "；";
        }
        $res[$key]['amount'] = number_format($val['amount'] / 100, 2);
        $res[$key]['goods_url'] = Url::goods($val['goods_id']);
        $res[$key]['url'] = Url::goods($val['goods_id']);
//        $res[$key]['status_text'] =
        $res[$key]['pay_time_text'] = empty($val['pay_time']) ? '未付款' : date('Y-m-d H:i:s', $val['pay_time']);
        $res[$key]['status_text'] = orderStatusText($val['status']);
        // 判断是否为赠品（item_price 为 0 且 attr_spec 带 [赠品] 标记）
        $res[$key]['is_gift'] = ($val['item_price'] == 0 && strpos((string)$val['attr_spec'], '[赠品]') !== false);
        $res[$key]['unit_price_yuan'] = number_format($val['unit_price'] / 100, 2);
        if (empty($val['payment']) && !empty($val['pay_plugin'])) {
            $res[$key]['payment'] = $val['pay_plugin'];
        }
        $res[$key]['is_refunding'] = false;
    }

    // 批量查询活跃售后，覆盖 status_text 和 is_refunding 标记
    $active_plugins = Option::get('active_plugins');
    $aftersale_enabled = is_array($active_plugins) && in_array('aftersale/aftersale.php', $active_plugins);
    if ($aftersale_enabled && !empty($res)) {
        $_olIds = array_filter(array_column($res, 'order_list_id'));
        if (!empty($_olIds)) {
            $_olIdsStr = implode(',', array_map('intval', $_olIds));
            $_refRows = $db->fetch_all("SELECT DISTINCT order_list_id FROM {$db_prefix}aftersale WHERE order_list_id IN ({$_olIdsStr}) AND status IN (0, 1)");
            $_refSet = [];
            foreach ($_refRows as $_rr) { $_refSet[(int)$_rr['order_list_id']] = true; }
            foreach ($res as $key => $val) {
                if (isset($_refSet[(int)$val['order_list_id']])) {
                    $res[$key]['is_refunding'] = true;
                    $res[$key]['status_text'] = '售后中';
                }
            }
        }
    }

    if($resp == 'api'){
        Ret::success('查询完成', $res);
    }else{
//        d($res);die;
        return ['code' => 200, 'data' => $res];
    }
}

/**
 * 游客查询订单结果数量
 */
function api_visitors_search_order_count(){
    global $db, $db_prefix, $resp;
    $out_trade_no = Input::postStrVar('out_trade_no');
    $required = Input::postStrArray('required');
    $attach = Input::postStrVar('attach');

    $_search = [
        'out_trade_no' => $out_trade_no,
        'required' => $required,
        'attach' => $attach
    ];

    $where = "where o.delete_time is null and (";
    if(!empty($out_trade_no)){
        $where .= " (o.out_trade_no = '{$out_trade_no}' or o.up_no = '{$out_trade_no}') or";
        $where .= " ol.attach_user like '%:\"{$out_trade_no}\"%' or";
        $where .= " orq.content = '{$out_trade_no}' or";
    }
    $where = trim($where, "or");
    $where .= ")";
    if(empty($out_trade_no)){
        if($resp == 'api'){
            Ret::error('请输入查询内容');
        }else{
            return ['code' => 400, 'msg' => '请输入查询内容'];
        }
    }

//echo $where;die;
    $sql = "SELECT DISTINCT o.id, o.out_trade_no, o.up_no, g.type, ol.attach_user
FROM {$db_prefix}order o
INNER JOIN {$db_prefix}order_list ol ON o.id = ol.order_id
LEFT JOIN {$db_prefix}goods g on ol.goods_id = g.id";
        $sql .= " left JOIN {$db_prefix}order_required orq ON o.id = orq.order_id ";
    $sql .= "{$where}";

    $rows = $db->fetch_all($sql);
    $rows = api_filter_physical_visitor_order_rows($rows, $out_trade_no);
    $order_ids = [];
    foreach ($rows as $row) {
        $order_ids[(int)$row['id']] = true;
    }
    $order_count = count($order_ids);
    if($resp == 'api'){
        Ret::success('查询完成', [
            'order_count' => $order_count,
            '_search' => $_search
        ]);
    }else{
        return ['code' => 200, 'data' => [
            'order_count' => $order_count,
            '_search' => $_search
        ]];
    }

}




/**
 * 申请售后
 */
function api_apply_aftersale($post = []){
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    
    $out_trade_no = Input::postStrVar('out_trade_no');
    $order_list_id = Input::postIntVar('order_list_id');
    $type = Input::postStrVar('type');
    $reason = Input::postStrVar('reason');
    $contact = Input::postStrVar('contact');
    // 直接从$_POST获取images，因为Input类可能会过滤特殊字符
    $images = isset($_POST['images']) ? $_POST['images'] : '';
    
    // 售后类型映射
    $typeMap = [
        'cant_use' => '不会使用',
        'invalid' => '无效商品',
        'fraud' => '欺诈骗钱',
        'kami_error' => '卡密错误',
        'other' => '其他问题'
    ];
    
    if (empty($out_trade_no)) {
        Ret::error('订单号不能为空');
    }
    if (empty($reason)) {
        Ret::error('请填写问题描述');
    }
    if (empty($contact)) {
        Ret::error('请填写联系方式');
    }
    
    // 查询订单
    $order = $db->once_fetch_array("SELECT * FROM {$db_prefix}order WHERE out_trade_no = '{$out_trade_no}'");
    if (empty($order)) {
        Ret::error('订单不存在');
    }
    
    // 检查售后申请时限
    $aftersale_expire_hours = Option::get('aftersale_expire_hours');
    $aftersale_expire_hours = ($aftersale_expire_hours === '' || $aftersale_expire_hours === null) ? 24 : intval($aftersale_expire_hours);
    if ($aftersale_expire_hours > 0 && !empty($order['pay_time'])) {
        $expire_seconds = $aftersale_expire_hours * 3600;
        if (time() - $order['pay_time'] > $expire_seconds) {
            Ret::error('该订单已超过售后申请时限（' . $aftersale_expire_hours . '小时）');
        }
    }
    
    // 检查是否已有进行中的售后
    $exists = $db->once_fetch_array("SELECT id FROM {$db_prefix}aftersale WHERE order_id = {$order['id']} AND order_list_id = {$order_list_id} AND status IN (0, 1)");
    if ($exists) {
        Ret::error('该订单已有售后申请在处理中');
    }
    
    // 检查重复申请开关：关闭时不允许已完成/已关闭/已拒绝的售后再次申请
    $aftersale_repeat_switch = Option::get('aftersale_repeat_switch') ?: 'n';
    if ($aftersale_repeat_switch !== 'y') {
        $any_exists = $db->once_fetch_array("SELECT id, status FROM {$db_prefix}aftersale WHERE order_id = {$order['id']} AND order_list_id = {$order_list_id} LIMIT 1");
        if ($any_exists) {
            $statusMap = [2 => '已完成', 3 => '已关闭', 4 => '已拒绝'];
            $statusText = $statusMap[intval($any_exists['status'])] ?? '已处理';
            Ret::error('该订单售后' . $statusText . '，不支持重复申请');
        }
    }
    
    // 获取订单子项信息
    $order_list = $db->once_fetch_array("SELECT ol.*, g.title as goods_title FROM {$db_prefix}order_list ol LEFT JOIN {$db_prefix}goods g ON ol.goods_id = g.id WHERE ol.id = {$order_list_id}");
    
    $goods_title = isset($order_list['goods_title']) ? addslashes($order_list['goods_title']) : '';
    $reason_safe = addslashes($reason);
    $contact_safe = addslashes($contact);
    $timestamp = time();
    
    // 解析图片
    $imageArr = [];
    if (!empty($images)) {
        $decoded = json_decode($images, true);
        if (is_array($decoded)) {
            $imageArr = $decoded;
        }
    }
    $images_str = !empty($imageArr) ? addslashes(json_encode($imageArr)) : '';
    
    $sql = "INSERT INTO {$db_prefix}aftersale (order_id, order_list_id, out_trade_no, goods_title, type, reason, contact, images, status, create_time) 
            VALUES ({$order['id']}, {$order_list_id}, '{$out_trade_no}', '{$goods_title}', '{$type}', '{$reason_safe}', '{$contact_safe}', '{$images_str}', 0, {$timestamp})";
    
    $db->query($sql);
    
    // 获取新插入的售后ID
    $aftersale_id = $db->insert_id();
    
    // 自动发送第一条消息（售后申请详情）
    $typeText = $typeMap[$type] ?? $type;
    $firstMsg = "【售后申请】\n";
    $firstMsg .= "售后类型：{$typeText}\n";
    $firstMsg .= "问题描述：{$reason}\n";
    $firstMsg .= "联系方式：{$contact}";
    
    // 添加图片
    if (!empty($imageArr)) {
        $firstMsg .= "\n补充图片：";
        foreach ($imageArr as $img) {
            $firstMsg .= "\n[图片]{$img}";
        }
    }
    
    $firstMsg .= "\n\n发起时间：" . date('Y-m-d H:i:s', $timestamp);
    
    $firstMsg_safe = addslashes($firstMsg);
    
    $sql = "INSERT INTO {$db_prefix}aftersale_chat (aftersale_id, order_id, order_list_id, out_trade_no, sender_type, content, create_time) 
            VALUES ({$aftersale_id}, {$order['id']}, {$order_list_id}, '{$out_trade_no}', 'user', '{$firstMsg_safe}', {$timestamp})";
    
    $db->query($sql);
    
    // 系统自动回复
    $autoReply = "我们已收到您的售后请求，并通知了商家，请耐心等待！";
    $autoReply_safe = addslashes($autoReply);
    $replyTime = $timestamp + 1; // 延迟1秒，确保排序正确
    
    $sql = "INSERT INTO {$db_prefix}aftersale_chat (aftersale_id, order_id, order_list_id, out_trade_no, sender_type, content, create_time) 
            VALUES ({$aftersale_id}, {$order['id']}, {$order_list_id}, '{$out_trade_no}', 'admin', '{$autoReply_safe}', {$replyTime})";
    
    $db->query($sql);
    
    // 发送售后通知（邮件和Webhook）
    try {
        // 引入售后通知服务
        require_once DC_ROOT . '/include/service/aftersale_notice.php';
        
        // 准备售后数据
        $notifyAftersaleData = [
            'id' => $aftersale_id,
            'order_id' => $order['id'],
            'order_list_id' => $order_list_id,
            'out_trade_no' => $out_trade_no,
            'goods_title' => $goods_title,
            'type' => $type,
            'reason' => $reason,
            'contact' => $contact,
            'images' => $images_str,
            'status' => 0,
            'create_time' => $timestamp
        ];
        
        // 发送通知
        AftersaleNotice::sendAftersaleNotification($notifyAftersaleData, $order);
    } catch (Exception $e) {
        // 通知发送失败不影响售后申请的提交
        // 可以记录日志，但不阻断流程
    }
    
    // 触发售后申请钩子
    doAction('aftersale_created', $aftersale_id, [
        'order_id' => $order['id'],
        'order_list_id' => $order_list_id,
        'out_trade_no' => $out_trade_no,
        'goods_title' => $goods_title,
        'type' => $type,
        'reason' => $reason,
        'contact' => $contact
    ]);
    
    // 返回成功信息，包含售后ID，让前端直接跳转到聊天窗口
    Ret::success('售后申请已提交', [
        'aftersale_id' => $aftersale_id,
        'order_id' => $order['id'],
        'order_list_id' => $order_list_id,
        'out_trade_no' => $out_trade_no,
        'redirect_to_chat' => true
    ]);
}

/**
 * 上传售后图片
 */
function api_upload_aftersale_image(){
    if (empty($_FILES['file'])) {
        Ret::error('请选择图片');
    }
    
    $file = $_FILES['file'];
    
    // 检查文件类型
    $allowTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowTypes)) {
        Ret::error('只支持 JPG/PNG/GIF/WEBP 格式');
    }
    
    // 检查文件大小（10MB，压缩后会变小）
    if ($file['size'] > 10 * 1024 * 1024) {
        Ret::error('图片大小不能超过10MB');
    }
    
    // 生成文件名
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'aftersale_' . date('YmdHis') . '_' . mt_rand(1000, 9999) . '.' . $ext;
    
    // 保存目录
    $uploadDir = DC_ROOT . '/content/uploadfile/aftersale/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $targetPath = $uploadDir . $filename;
    
    // 先上传原文件
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        Ret::error('上传失败');
    }
    
    // 检查是否启用图片压缩
    $compressSwitch = Option::get('image_compress_switch') ?: 'y';
    
    if ($compressSwitch === 'y') {
        // 引入图片压缩服务
        require_once DC_ROOT . '/include/service/image_compress.php';
        
        // 获取压缩配置
        $quality = (int)(Option::get('image_compress_quality') ?: 75);
        $maxWidth = (int)(Option::get('image_compress_max_width') ?: 800);
        $maxHeight = (int)(Option::get('image_compress_max_height') ?: 800);
        
        // 记录压缩前的文件大小
        $originalSize = filesize($targetPath);
        
        // 压缩图片
        $compressResult = ImageCompress::compressImage($targetPath, null, $quality, $maxWidth, $maxHeight);
        
        if ($compressResult) {
            // 压缩成功，记录压缩后的文件大小
            $compressedSize = filesize($targetPath);
            $compressionRatio = $originalSize > 0 ? round((1 - $compressedSize / $originalSize) * 100, 1) : 0;
            
            $url = DC_URL . 'content/uploadfile/aftersale/' . $filename;
            Ret::success('上传成功', [
                'url' => $url,
                'original_size' => ImageCompress::formatFileSize($originalSize),
                'compressed_size' => ImageCompress::formatFileSize($compressedSize),
                'compression_ratio' => $compressionRatio . '%',
                'compressed' => true
            ]);
        } else {
            // 压缩失败，但文件已上传，仍然可以使用
            $url = DC_URL . 'content/uploadfile/aftersale/' . $filename;
            Ret::success('上传成功（未压缩）', [
                'url' => $url,
                'size' => ImageCompress::formatFileSize($originalSize),
                'note' => '图片压缩失败，使用原图',
                'compressed' => false
            ]);
        }
    } else {
        // 未启用压缩，直接返回
        $originalSize = filesize($targetPath);
        $url = DC_URL . 'content/uploadfile/aftersale/' . $filename;
        Ret::success('上传成功', [
            'url' => $url,
            'size' => ImageCompress::formatFileSize($originalSize),
            'compressed' => false
        ]);
    }
}

function api_upload_withdraw_receipt_image(){
    if(!ISLOGIN){
        Ret::error('请先登录');
    }
    LoginAuth::checkToken();
    if (empty($_FILES['file'])) {
        Ret::error('请选择图片');
    }
    $file = $_FILES['file'];
    $allowTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowTypes)) {
        Ret::error('只支持 JPG/PNG/GIF/WEBP 格式');
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        Ret::error('图片大小不能超过2MB');
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext === '') {
        $ext = 'png';
    }
    $filename = 'withdraw_' . UID . '_' . date('YmdHis') . '_' . mt_rand(1000, 9999) . '.' . $ext;
    $relativePath = 'content/uploadfile/withdraw/' . $filename;
    $uploadDir = DC_ROOT . '/content/uploadfile/withdraw/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $targetPath = $uploadDir . $filename;
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        Ret::error('上传失败');
    }
    $compressSwitch = Option::get('image_compress_switch') ?: 'y';
    if ($compressSwitch === 'y') {
        require_once DC_ROOT . '/include/service/image_compress.php';
        $quality = (int)(Option::get('image_compress_quality') ?: 75);
        $maxWidth = (int)(Option::get('image_compress_max_width') ?: 1200);
        $maxHeight = (int)(Option::get('image_compress_max_height') ?: 1200);
        ImageCompress::compressImage($targetPath, null, $quality, $maxWidth, $maxHeight);
    }
    $db = Database::getInstance();
    $db->update('user', ['withdraw_receipt_image' => $relativePath], ['uid' => UID]);
    User_Log_Model::log(UID, 'withdraw_receipt_image_update', '更新默认提现收款码', 0);
    Ret::success('上传成功', [
        'path' => $relativePath,
        'url' => DC_URL . $relativePath,
        'name' => $filename
    ]);
}

/**
 * 上传用户头像
 */
function api_upload_user_avatar(){
    if(!ISLOGIN){
        Ret::error('请先登录');
    }
    LoginAuth::checkToken();
    if (empty($_FILES['file'])) {
        Ret::error('请选择图片');
    }
    $file = $_FILES['file'];
    $allowTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowTypes)) {
        Ret::error('只支持 JPG/PNG/GIF/WEBP 格式');
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        Ret::error('图片大小不能超过2MB');
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext === '') {
        $ext = 'png';
    }
    $filename = 'avatar_' . UID . '_' . date('YmdHis') . '_' . mt_rand(1000, 9999) . '.' . $ext;
    $relativePath = 'content/uploadfile/avatar/' . $filename;
    $uploadDir = DC_ROOT . '/content/uploadfile/avatar/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $targetPath = $uploadDir . $filename;
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        Ret::error('上传失败');
    }
    $compressSwitch = Option::get('image_compress_switch') ?: 'y';
    if ($compressSwitch === 'y') {
        require_once DC_ROOT . '/include/service/image_compress.php';
        // 头像尺寸控制得更紧凑：最大 512px、质量 80
        ImageCompress::compressImage($targetPath, null, 80, 512, 512);
    }
    $db = Database::getInstance();
    $db->update('user', ['photo' => $relativePath], ['uid' => UID]);
    User_Log_Model::log(UID, 'avatar_update', '更新账户头像', 0);
    Ret::success('头像已更新', [
        'path' => $relativePath,
        'url' => User::getAvatar($relativePath),
        'name' => $filename
    ]);
}


/**
 * 获取聊天记录
 */
function api_get_chat_messages(){
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    
    $out_trade_no = Input::postStrVar('out_trade_no');
    $order_list_id = Input::postIntVar('order_list_id');
    
    if (empty($out_trade_no)) {
        Ret::error('订单号不能为空');
    }
    
    // 查询订单
    $order = $db->once_fetch_array("SELECT id FROM {$db_prefix}order WHERE out_trade_no = '{$out_trade_no}'");
    if (empty($order)) {
        Ret::error('订单不存在');
    }
    
    // 查询售后状态
    $aftersale = $db->once_fetch_array("SELECT id, status, handle_remark, handle_time, reopen_status FROM {$db_prefix}aftersale WHERE order_id = {$order['id']} AND order_list_id = {$order_list_id} ORDER BY id DESC LIMIT 1");
    $aftersale_status = $aftersale ? intval($aftersale['status']) : -1;
    $handle_remark = $aftersale ? ($aftersale['handle_remark'] ?: '') : '';
    
    // 查询聊天记录
    $sql = "SELECT * FROM {$db_prefix}aftersale_chat WHERE order_id = {$order['id']} AND order_list_id = {$order_list_id} ORDER BY id ASC";
    $messages = $db->fetch_all($sql);
    
    foreach ($messages as $key => $val) {
        $messages[$key]['create_time_text'] = date('Y-m-d H:i:s', $val['create_time']);
    }
    
    // 计算是否可以申请重开
    $can_reopen = false;
    $reopen_status = 0;
    $reopen_expire_text = '';
    if ($aftersale && in_array($aftersale_status, [2, 3, 4])) {
        $reopen_status = intval($aftersale['reopen_status'] ?? 0);
        $aftersale_reopen_hours = Option::get('aftersale_reopen_hours');
        $aftersale_reopen_hours = ($aftersale_reopen_hours === '' || $aftersale_reopen_hours === null) ? 72 : intval($aftersale_reopen_hours);
        if ($aftersale_reopen_hours > 0 && $reopen_status == 0 && !empty($aftersale['handle_time'])) {
            $reopen_expire = intval($aftersale['handle_time']) + $aftersale_reopen_hours * 3600;
            if (time() < $reopen_expire) {
                $can_reopen = true;
                $reopen_expire_text = date('Y-m-d H:i', $reopen_expire);
            }
        }
    }
    
    Ret::success('success', [
        'messages' => $messages, 
        'aftersale_status' => $aftersale_status, 
        'handle_remark' => $handle_remark,
        'can_reopen' => $can_reopen,
        'reopen_status' => $reopen_status,
        'reopen_expire_text' => $reopen_expire_text
    ]);
}

/**
 * 发送聊天消息（用户端）
 */
function api_send_chat_message(){
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    
    $out_trade_no = Input::postStrVar('out_trade_no');
    $order_list_id = Input::postIntVar('order_list_id');
    $content = Input::postStrVar('content');
    
    if (empty($out_trade_no)) {
        Ret::error('订单号不能为空');
    }
    if (empty($content)) {
        Ret::error('消息内容不能为空');
    }
    
    // 查询订单
    $order = $db->once_fetch_array("SELECT id FROM {$db_prefix}order WHERE out_trade_no = '{$out_trade_no}'");
    if (empty($order)) {
        Ret::error('订单不存在');
    }
    
    // 检查是否有售后申请
    $aftersale = $db->once_fetch_array("SELECT id, status FROM {$db_prefix}aftersale WHERE order_id = {$order['id']} AND order_list_id = {$order_list_id} ORDER BY id DESC LIMIT 1");
    if (empty($aftersale)) {
        Ret::error('请先提交售后申请');
    }
    // 已完成/已关闭/已拒绝的售后不允许发送消息
    if (in_array(intval($aftersale['status']), [2, 3, 4])) {
        $statusMap = [2 => '已完成', 3 => '已关闭', 4 => '已拒绝'];
        Ret::error('该售后' . ($statusMap[$aftersale['status']] ?? '已结束') . '，无法发送消息');
    }
    
    $content_safe = addslashes($content);
    $timestamp = time();
    
    $sql = "INSERT INTO {$db_prefix}aftersale_chat (aftersale_id, order_id, order_list_id, out_trade_no, sender_type, content, create_time) 
            VALUES ({$aftersale['id']}, {$order['id']}, {$order_list_id}, '{$out_trade_no}', 'user', '{$content_safe}', {$timestamp})";
    
    $db->query($sql);
    
    Ret::success('发送成功');
}


/**
 * 检查订单是否已申请售后
 */
function api_check_aftersale_status(){
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    
    $out_trade_no = Input::postStrVar('out_trade_no');
    $order_list_id = Input::postIntVar('order_list_id');
    
    if (empty($out_trade_no)) {
        Ret::error('订单号不能为空');
    }
    
    // 查询订单
    $order = $db->once_fetch_array("SELECT id FROM {$db_prefix}order WHERE out_trade_no = '{$out_trade_no}'");
    if (empty($order)) {
        Ret::success('success', ['has_aftersale' => false, 'status' => -1]);
    }
    
    // 获取是否允许重复发起售后的配置
    $aftersale_repeat_switch = Option::get('aftersale_repeat_switch') ?: 'n';
    
    // 检查是否有售后申请（获取最新的一条）
    $aftersale = $db->once_fetch_array("SELECT id, status, handle_remark, handle_time, reopen_status, reopen_time FROM {$db_prefix}aftersale WHERE order_id = {$order['id']} AND order_list_id = {$order_list_id} ORDER BY id DESC LIMIT 1");
    
    if ($aftersale) {
        // 状态：0-待处理，1-处理中，2-已完成，3-用户已关闭，4-已拒绝
        $status = intval($aftersale['status']);
        $handle_remark = $aftersale['handle_remark'] ?: '';
        $reopen_status = intval($aftersale['reopen_status'] ?? 0);
        
        // 如果是进行中的售后（0或1），显示协商历史
        if ($status == 0 || $status == 1) {
            Ret::success('success', [
                'has_aftersale' => true, 
                'status' => $status,
                'btn_type' => 'history'  // 显示协商历史按钮
            ]);
        }
        
        // 如果是已完成或已关闭的售后
        if ($status == 2 || $status == 3 || $status == 4) {
            if ($aftersale_repeat_switch == 'y') {
                // 允许重复申请，显示申请售后按钮
                Ret::success('success', [
                    'has_aftersale' => false, 
                    'status' => $status,
                    'btn_type' => 'apply'  // 显示申请售后按钮
                ]);
            } else {
                // 不允许重复申请，根据状态显示不同按钮
                $btn_type = 'completed';
                if ($status == 4) {
                    $btn_type = 'rejected';  // 已拒绝
                } elseif ($status == 3) {
                    $btn_type = 'closed';    // 用户关闭
                }
                // 计算是否可以申请重开
                $can_reopen = false;
                $reopen_expire_text = '';
                $aftersale_reopen_hours = Option::get('aftersale_reopen_hours');
                $aftersale_reopen_hours = ($aftersale_reopen_hours === '' || $aftersale_reopen_hours === null) ? 72 : intval($aftersale_reopen_hours);
                if ($aftersale_reopen_hours > 0 && $reopen_status == 0 && !empty($aftersale['handle_time'])) {
                    $reopen_expire = intval($aftersale['handle_time']) + $aftersale_reopen_hours * 3600;
                    if (time() < $reopen_expire) {
                        $can_reopen = true;
                        $reopen_expire_text = date('Y-m-d H:i', $reopen_expire);
                    }
                }
                Ret::success('success', [
                    'has_aftersale' => true, 
                    'status' => $status,
                    'handle_remark' => $handle_remark,
                    'btn_type' => $btn_type,
                    'can_reopen' => $can_reopen,
                    'reopen_status' => $reopen_status,
                    'reopen_expire_text' => $reopen_expire_text
                ]);
            }
        }
    }
    
    // 没有售后记录
    Ret::success('success', ['has_aftersale' => false, 'status' => -1, 'btn_type' => 'apply']);
}

/**
 * 用户申请重开售后
 */
function api_apply_reopen_aftersale(){
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    
    $out_trade_no = Input::postStrVar('out_trade_no');
    $order_list_id = Input::postIntVar('order_list_id');
    $reason = Input::postStrVar('reason');
    
    if (empty($out_trade_no)) {
        Ret::error('订单号不能为空');
    }
    if (empty($reason)) {
        Ret::error('请填写重开理由');
    }
    
    // 查询订单
    $order = $db->once_fetch_array("SELECT id FROM {$db_prefix}order WHERE out_trade_no = '{$out_trade_no}'");
    if (empty($order)) {
        Ret::error('订单不存在');
    }
    
    // 查询售后申请（最新一条）
    $aftersale = $db->once_fetch_array("SELECT * FROM {$db_prefix}aftersale WHERE order_id = {$order['id']} AND order_list_id = {$order_list_id} ORDER BY id DESC LIMIT 1");
    if (empty($aftersale)) {
        Ret::error('售后记录不存在');
    }
    
    $status = intval($aftersale['status']);
    if (!in_array($status, [2, 3, 4])) {
        Ret::error('当前售后状态不支持重开申请');
    }
    
    // 检查是否已有重开申请
    $reopen_status = intval($aftersale['reopen_status'] ?? 0);
    if ($reopen_status == 1) {
        Ret::error('您已提交过重开申请，请耐心等待处理');
    }
    
    // 检查重开时限
    $aftersale_reopen_hours = Option::get('aftersale_reopen_hours');
    $aftersale_reopen_hours = ($aftersale_reopen_hours === '' || $aftersale_reopen_hours === null) ? 72 : intval($aftersale_reopen_hours);
    if ($aftersale_reopen_hours <= 0) {
        Ret::error('重开功能未开启');
    }
    if (!empty($aftersale['handle_time'])) {
        $reopen_expire = intval($aftersale['handle_time']) + $aftersale_reopen_hours * 3600;
        if (time() > $reopen_expire) {
            Ret::error('已超过重开申请时限');
        }
    }
    
    $reason_safe = addslashes($reason);
    $timestamp = time();
    
    // 更新重开状态
    $db->query("UPDATE {$db_prefix}aftersale SET reopen_status = 1, reopen_reason = '{$reason_safe}', reopen_time = {$timestamp} WHERE id = {$aftersale['id']}");
    
    // 添加系统消息到聊天记录
    $sysMsg = '【系统消息】用户申请重开售后：' . $reason;
    $sysMsg_safe = addslashes($sysMsg);
    $db->query("INSERT INTO {$db_prefix}aftersale_chat (aftersale_id, order_id, order_list_id, out_trade_no, sender_type, content, create_time) 
                VALUES ({$aftersale['id']}, {$order['id']}, {$order_list_id}, '{$out_trade_no}', 'system', '{$sysMsg_safe}', {$timestamp})");
    
    // 发送通知
    try {
        require_once DC_ROOT . '/include/service/aftersale_notice.php';
        $notifyData = [
            'id' => $aftersale['id'],
            'order_id' => $order['id'],
            'order_list_id' => $order_list_id,
            'out_trade_no' => $out_trade_no,
            'goods_title' => $aftersale['goods_title'],
            'type' => 'reopen',
            'reason' => $reason,
            'contact' => $aftersale['contact'],
            'create_time' => $timestamp,
            'images' => ''
        ];
        AftersaleNotice::sendAftersaleNotification($notifyData, $order);
    } catch (Exception $e) {
        // 通知失败不影响主流程
    }
    
    Ret::success('重开申请已提交，请等待商家处理');
}

/**
 * 用户关闭/取消售后申请
 */
function api_close_aftersale(){
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    
    $out_trade_no = Input::postStrVar('out_trade_no');
    $order_list_id = Input::postIntVar('order_list_id');
    
    if (empty($out_trade_no)) {
        Ret::error('订单号不能为空');
    }
    
    // 查询订单
    $order = $db->once_fetch_array("SELECT id FROM {$db_prefix}order WHERE out_trade_no = '{$out_trade_no}'");
    if (empty($order)) {
        Ret::error('订单不存在');
    }
    
    // 查询售后申请
    $aftersale = $db->once_fetch_array("SELECT id, status FROM {$db_prefix}aftersale WHERE order_id = {$order['id']} AND order_list_id = {$order_list_id} AND status IN (0, 1) LIMIT 1");
    
    if (empty($aftersale)) {
        Ret::error('没有进行中的售后申请');
    }
    
    // 更新状态为已关闭（状态3表示用户主动关闭）
    $timestamp = time();
    $sql = "UPDATE {$db_prefix}aftersale SET status = 3, handle_time = {$timestamp} WHERE id = {$aftersale['id']}";
    $db->query($sql);
    
    // 触发售后关闭钩子
    doAction('aftersale_closed', $aftersale['id'], [
        'order_id' => $order['id'],
        'order_list_id' => $order_list_id,
        'out_trade_no' => $out_trade_no,
        'closed_by' => 'user'
    ]);
    
    // 添加系统消息
    $closeMsg = "【系统消息】用户已主动关闭此投诉";
    $closeMsg_safe = addslashes($closeMsg);
    
    $sql = "INSERT INTO {$db_prefix}aftersale_chat (aftersale_id, order_id, order_list_id, out_trade_no, sender_type, content, create_time) 
            VALUES ({$aftersale['id']}, {$order['id']}, {$order_list_id}, '{$out_trade_no}', 'system', '{$closeMsg_safe}', {$timestamp})";
    $db->query($sql);
    
    Ret::success('投诉已关闭');
}

/**
 * 获取虚拟服务商品订单详情
 */
function api_get_service_detail(){
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    
    $out_trade_no = Input::postStrVar('out_trade_no');
    $order_list_id = Input::postIntVar('order_list_id');
    
    if (empty($out_trade_no)) {
        Ret::error('订单号不能为空');
    }
    
    // 查询订单
    $order = $db->once_fetch_array("SELECT * FROM {$db_prefix}order WHERE out_trade_no = '{$out_trade_no}'");
    if (empty($order)) {
        Ret::error('订单不存在');
    }
    
    // 查询订单子项
    $order_list = $db->once_fetch_array("SELECT ol.*, g.title as goods_title, g.type, g.pay_content 
        FROM {$db_prefix}order_list ol 
        LEFT JOIN {$db_prefix}goods g ON ol.goods_id = g.id 
        WHERE ol.id = {$order_list_id} AND ol.order_id = {$order['id']}");
    
    if (empty($order_list)) {
        Ret::error('订单详情不存在');
    }
    
    // 查询商家留言（从 goods_service_sale 表获取，is_default='y' 是默认留言）
    $service_sale = $db->once_fetch_array("SELECT content FROM {$db_prefix}goods_service_sale 
        WHERE order_list_id = {$order_list_id} AND is_default = 'y'");
    
    // 查询发货内容（is_default='n' 是实际发货内容）
    $deliver_content = '';
    if ($order['status'] == 2) {
        $deliver_list = $db->fetch_all("SELECT content FROM {$db_prefix}goods_service_sale 
            WHERE order_list_id = {$order_list_id} AND is_default = 'n' ORDER BY id ASC");
        if (!empty($deliver_list)) {
            $contents = [];
            foreach ($deliver_list as $item) {
                $contents[] = $item['content'];
            }
            $deliver_content = implode("\n", $contents);
        }
    }
    
    // 构建返回数据
    $data = [
        'out_trade_no' => $order['out_trade_no'],
        'goods_title' => $order_list['goods_title'] ?? '',
        'attr_spec' => $order_list['attr_spec'] ?? '',
        'quantity' => $order_list['quantity'] ?? 1,
        'amount' => number_format($order['amount'] / 100, 2),
        'pay_time' => !empty($order['pay_time']) ? date('Y-m-d H:i:s', $order['pay_time']) : '-',
        'status' => $order['status'],
        'message' => $service_sale['content'] ?? '',
        'deliver_content' => $deliver_content,
        'pay_content' => $order_list['pay_content'] ?? ''
    ];
    
    Ret::success('success', $data);
}

/**
 * 取消未支付订单
 * @post.out_trade_no 订单编号
 */
function api_cancel_order($post = []){
    global $db, $db_prefix, $resp;
    $out_trade_no = !empty($post['out_trade_no']) ? $post['out_trade_no'] : Input::postStrVar('out_trade_no');
    if (empty($out_trade_no)) {
        Ret::error('缺少订单编号');
    }
    $order = $db->once_fetch_array("SELECT id, status, user_id FROM {$db_prefix}order WHERE out_trade_no = '{$out_trade_no}' AND delete_time IS NULL LIMIT 1");
    if (empty($order)) {
        Ret::error('订单不存在');
    }
    if ((int)$order['status'] !== 0) {
        Ret::error('仅未支付订单可取消');
    }
    // 登录用户需校验归属
    if (defined('UID') && UID > 0 && (int)$order['user_id'] > 0 && (int)$order['user_id'] !== UID) {
        Ret::error('无权操作此订单');
    }
    $db->query("UPDATE {$db_prefix}order SET status = 3 WHERE id = {$order['id']}");
    Ret::success('订单已取消');
}

/**
 * 删除已取消的订单（移入回收站）
 * @post.out_trade_no 订单编号
 */
function api_delete_order($post = []){
    global $db, $db_prefix;
    $out_trade_no = !empty($post['out_trade_no']) ? $post['out_trade_no'] : Input::postStrVar('out_trade_no');
    if (empty($out_trade_no)) {
        Ret::error('缺少订单编号');
    }
    $order = $db->once_fetch_array("SELECT id, status, user_id FROM {$db_prefix}order WHERE out_trade_no = '" . addslashes($out_trade_no) . "' AND delete_time IS NULL LIMIT 1");
    if (empty($order)) {
        Ret::error('订单不存在');
    }
    if ((int)$order['status'] !== 3) {
        Ret::error('仅已取消的订单可删除');
    }
    if (defined('UID') && UID > 0 && (int)$order['user_id'] > 0 && (int)$order['user_id'] !== UID) {
        Ret::error('无权操作此订单');
    }
    $timestamp = time();
    $db->query("UPDATE {$db_prefix}order SET delete_time = {$timestamp} WHERE id = {$order['id']}");
    if (defined('UID') && UID > 0) {
        User_Log_Model::log(UID, 'order_delete', '用户删除已取消订单，订单号: ' . $out_trade_no, 0);
    }
    Ret::success('订单已删除');
}

/**
 * ============================================================
 * 同系统商品对接（Docking）对外 API 接口（卖家端）
 * ============================================================
 */

/**
 * 统一鉴权函数：验证 api_key 及 IP白名单
 */
function api_auth() {
    global $db, $db_prefix;
    $apiKey = trim(Input::getStrVar('api_key'));
    if (empty($apiKey)) {
        $apiKey = trim(Input::postStrVar('api_key'));
    }

    if (empty($apiKey)) {
        Output::error('缺少API对接密钥 (api_key)');
    }

    $apiKeyHash = md5($apiKey);
    $user = $db->once_fetch_array("SELECT uid, api_key, state, money, level, api_whitelist, api_whitelist_enabled FROM {$db_prefix}user WHERE api_key = '" . addslashes($apiKey) . "' OR api_key_hash = '{$apiKeyHash}' LIMIT 1");
    
    if (empty($user)) {
        Output::error('API密钥无效');
    }

    if ((int)$user['state'] !== 0) {
        Output::error('对接账户已被禁用');
    }

    // IP 白名单验证
    if ((int)$user['api_whitelist_enabled'] === 1 && !empty($user['api_whitelist'])) {
        $clientIp = getIp();
        $whitelist = array_filter(array_map('trim', explode("\n", $user['api_whitelist'])));
        if (!in_array($clientIp, $whitelist, true)) {
            Output::error('请求IP [' . $clientIp . '] 未在白名单中');
        }
    }

    // Secure authentication (Signature & Timestamp) if sign is present
    $sign = Input::getStrVar('sign');
    if (empty($sign)) {
        $sign = Input::postStrVar('sign');
    }
    if (!empty($sign)) {
        $timestamp = Input::getStrVar('timestamp');
        if (empty($timestamp)) {
            $timestamp = Input::postStrVar('timestamp');
        }
        if (empty($timestamp) || abs(time() - (int)$timestamp) > 300) {
            Output::error('请求已过期或时间不同步');
        }

        // Verify sign
        $params = array_merge($_GET, $_POST);
        unset($params['sign']);
        
        ksort($params);
        $signStr = '';
        foreach ($params as $k => $v) {
            if ($v !== '' && $v !== null) {
                $signStr .= $k . '=' . trim($v) . '&';
            }
        }
        $signStr .= 'key=' . $user['api_key'];
        $computedSign = md5($signStr);
        if (strcasecmp($computedSign, $sign) !== 0) {
            Output::error('签名校验失败');
        }
    }

    return $user;
}

/**
 * 1. 获取商品分类列表
 */
function api_goods_category() {
    global $db, $db_prefix;
    $user = api_auth();

    $categories = $db->fetch_all("SELECT sid AS id, sortname AS title, taxis, description FROM {$db_prefix}sort WHERE type = 'goods' AND station_id = 0 ORDER BY taxis DESC");
    if (!is_array($categories)) $categories = [];

    Output::ok($categories);
}

/**
 * 2. 获取商品列表
 */
function api_goods_list() {
    global $db, $db_prefix;
    $user = api_auth();

    $cid = (int)Input::getStrVar('cid');
    $page = max(1, (int)Input::getStrVar('page'));
    $limit = max(1, min(100, (int)Input::getStrVar('limit')));
    if ($limit <= 0) $limit = 20;
    $offset = ($page - 1) * $limit;

    $dockingExclude = api_table_exists($db_prefix . 'docking_goods') ? " AND id NOT IN (SELECT goods_id FROM {$db_prefix}docking_goods)" : '';
    $where = "WHERE delete_time IS NULL AND is_on_shelf = 1 AND station_id = 0 AND allow_dock = 1{$dockingExclude}";
    if ($cid > 0) {
        $where .= " AND sort_id = {$cid}";
    }

    $sql = "SELECT id, sort_id, type, title, des, cover, sales, stock, is_sku, create_time, profit_rule_id, profit_ratio, single_rule_id FROM {$db_prefix}goods {$where} ORDER BY sort_num DESC, id DESC LIMIT {$offset}, {$limit}";
    $goods = $db->fetch_all($sql);
    if (!is_array($goods)) $goods = [];

    // 对接端需要的商品数据格式（含简单规格的价格与基本库存，按对接端会员等级定制价格）
    $userLevel = class_exists('Level_Service') ? Level_Service::getActiveLevelId($user) : (int)($user['level'] ?? -1);
    foreach ($goods as $key => $val) {
        $goodsId = $val['id'];
        $skuRow = $db->once_fetch_array("SELECT * FROM {$db_prefix}skus WHERE goods_id = {$goodsId} ORDER BY guest_price ASC LIMIT 1");
        if ($skuRow) {
            if (class_exists('Level_Price')) {
                $goods[$key]['guest_price'] = Level_Price::calculate($skuRow, $val, $userLevel) / 100;
            } else {
                $goods[$key]['guest_price'] = $skuRow['guest_price'] / 100;
            }
            $goods[$key]['user_price'] = $skuRow['user_price'] / 100;
            $goods[$key]['market_price'] = $skuRow['market_price'] / 100;
            $goods[$key]['cost_price'] = $skuRow['cost_price'] / 100;
        } else {
            $goods[$key]['guest_price'] = 0;
            $goods[$key]['user_price'] = 0;
            $goods[$key]['market_price'] = 0;
            $goods[$key]['cost_price'] = 0;
        }
    }

    Output::ok($goods);
}

/**
 * 3. 获取商品详情
 */
function api_goods_detail() {
    global $db, $db_prefix;
    $user = api_auth();

    $id = (int)Input::getStrVar('id');
    if ($id <= 0) {
        Output::error('缺少商品ID');
    }

    $goods = $db->once_fetch_array("SELECT g.*, gt.name as attr_name FROM {$db_prefix}goods g LEFT JOIN {$db_prefix}goods_type gt ON g.attr_id = gt.id WHERE g.id = {$id} AND g.delete_time IS NULL AND g.is_on_shelf = 1 AND g.station_id = 0 AND g.allow_dock = 1");
    if (empty($goods)) {
        Output::error('商品不存在或已下架');
    }

    $goodsId = $goods['id'];
    $skus = $db->fetch_all("SELECT * FROM {$db_prefix}skus WHERE goods_id = {$goodsId}");
    if (!is_array($skus)) $skus = [];

    // 格式化价格为元单位，并基于对接账号的会员等级计算专属折后购买价
    $userLevel = class_exists('Level_Service') ? Level_Service::getActiveLevelId($user) : (int)($user['level'] ?? -1);
    foreach ($skus as $key => $val) {
        if (class_exists('Level_Price')) {
            $skus[$key]['guest_price'] = Level_Price::calculate($val, $goods, $userLevel) / 100;
        } else {
            $skus[$key]['guest_price'] = $val['guest_price'] / 100;
        }
        $skus[$key]['user_price'] = $val['user_price'] / 100;
        $skus[$key]['market_price'] = $val['market_price'] / 100;
        $skus[$key]['cost_price'] = $val['cost_price'] / 100;
    }

    $goods['skus'] = $skus;

    // 填充规格数据供对接端解析
    if ($goods['is_sku'] === 'y') {
        $sku_value_ids = [];
        $sku_attr_ids = [];
        foreach ($skus as $val) {
            $ids = explode('-', $val['sku']);
            $sku_value_ids = array_merge($sku_value_ids, $ids);
        }
        $sku_value_ids = array_filter(array_unique($sku_value_ids));
        if (!empty($sku_value_ids)) {
            $sku_values = $db->fetch_all("SELECT * FROM {$db_prefix}sku_value WHERE id IN (" . implode(',', $sku_value_ids) . ") ORDER BY id ASC");
            foreach ($sku_values as $val) {
                $sku_attr_ids[] = $val['attr_id'];
            }
            $sku_attr_ids = array_filter(array_unique($sku_attr_ids));
            if (!empty($sku_attr_ids)) {
                $sku_attr = $db->fetch_all("SELECT * FROM {$db_prefix}sku_attr WHERE id IN (" . implode(',', $sku_attr_ids) . ") ORDER BY id ASC");
                $spec = [];
                foreach ($sku_attr as $key => $val) {
                    $spec[$key] = [
                        'sku_attr_id' => $val['id'],
                        'title' => $val['title'],
                        'sku_values' => []
                    ];
                    foreach ($sku_values as $v) {
                        if ($val['id'] == $v['attr_id']) {
                            $spec[$key]['sku_values'][] = [
                                'id' => $v['id'],
                                'name' => $v['name']
                            ];
                        }
                    }
                }
                $goods['spec'] = $spec;
            }
        }
    }

    // 附加全局下单必填项（供对接站同步输入框）；实物商品使用独立收货模型，不继承全局项
    if (($goods['type'] ?? '') === 'physical') {
        $goods['order_required'] = [];
    } else {
        $orderRequiredRaw = Option::get('order_required');
        $goods['order_required'] = json_decode($orderRequiredRaw ?? '[]', true) ?: [];
    }

    Output::ok($goods);
}

/**
 * 4. 接口购买下单发货 (API Order Purchase)
 */
function api_order_buy() {
    global $db, $db_prefix;
    $user = api_auth();

    // Check/create notify_url column
    ensure_notify_url_column();

    $goodsId = (int)Input::postStrVar('goods_id');
    $quantity = max(1, (int)Input::postStrVar('quantity'));
    $sku = trim((string)Input::postStrVar('sku'));
    if ($sku === '') $sku = '0';
    $outTradeNo = trim((string)Input::postStrVar('out_trade_no')); // 对接买家订单号

    $notifyUrl = trim(Input::getStrVar('notify_url'));
    if (empty($notifyUrl)) {
        $notifyUrl = trim(Input::postStrVar('notify_url'));
    }

    // Handle custom metadata input fields (input_value)
    $inputValues = [];
    if (isset($_POST['input_value'])) {
        $inputValues = $_POST['input_value'];
    } elseif (isset($_GET['input_value'])) {
        $inputValues = $_GET['input_value'];
    }
    if (is_string($inputValues)) {
        $decoded = json_decode($inputValues, true);
        if (is_array($decoded)) {
            $inputValues = $decoded;
        }
    }

    if ($goodsId <= 0) {
        Output::error('缺少商品ID');
    }
    if (empty($outTradeNo)) {
        Output::error('缺少商户订单号 (out_trade_no)');
    }

    // 查重：防止买家端因为超时重试导致扣款两次！
    $exists = $db->once_fetch_array("SELECT out_trade_no, status FROM {$db_prefix}order WHERE user_id = {$user['uid']} AND up_no = '" . addslashes($outTradeNo) . "' LIMIT 1");
    if ($exists) {
        // 如果订单存在，直接查出发货卡密返回
        $child = $db->once_fetch_array("SELECT id, goods_id FROM {$db_prefix}order_list ol JOIN {$db_prefix}order o ON ol.order_id = o.id WHERE o.out_trade_no = '{$exists['out_trade_no']}' LIMIT 1");
        if ($child) {
            $goods = $db->once_fetch_array("SELECT id, type FROM {$db_prefix}goods WHERE id = {$child['goods_id']}");
            $goodsType = api_resolve_goods_type($child['goods_id'], $goods);
            $cardList = [];
            $func = "plugin_goods_{$goodsType}_get_order_serect";
            if (function_exists($func)) {
                $serect = $func($db, $db_prefix, $goods, ['id' => $exists['id'], 'out_trade_no' => $exists['out_trade_no']], $child, 0);
                if (!empty($serect['list'])) {
                    foreach ($serect['list'] as $kItem) $cardList[] = $kItem['content'];
                }
            }
            if (empty($cardList)) {
                $delivers = $db->fetch_all("SELECT content FROM {$db_prefix}deliver WHERE order_list_id = {$child['id']}");
                foreach ($delivers as $d) $cardList[] = $d['content'];
            }
            Output::ok([
                'order_id' => $exists['out_trade_no'],
                'content' => implode("\n", $cardList)
            ]);
        }
        Output::error('订单处理失败');
    }

    // 查商品 (校验 allow_dock = 1)
    $goods = $db->once_fetch_array("SELECT * FROM {$db_prefix}goods WHERE id = {$goodsId} AND delete_time IS NULL AND is_on_shelf = 1 AND station_id = 0 AND allow_dock = 1");
    if (empty($goods)) {
        Output::error('商品不存在、已下架或不支持对接');
    }

    // Parse and map product-specific input fields (attach_user)
    $goodsAttach = json_decode($goods['attach_user'] ?? '[]', true);
    if (!empty($goodsAttach) && !isset($goodsAttach[0])) {
        $converted = [];
        foreach ($goodsAttach as $k => $v) {
            $converted[] = ['name' => $k, 'placeholder' => $v, 'type' => 'string', 'required' => true, 'tip' => ''];
        }
        $goodsAttach = $converted;
    }
    $attachData = [];
    if (!empty($goodsAttach)) {
        foreach ($goodsAttach as $index => $item) {
            $name = $item['name'];
            $val = '';
            if (isset($inputValues[$name])) {
                $val = $inputValues[$name];
            } elseif (isset($inputValues[$index])) {
                $val = $inputValues[$index];
            } elseif (isset($inputValues['input_value' . ($index + 1)])) {
                $val = $inputValues['input_value' . ($index + 1)];
            }
            if (!empty($item['required']) && empty($val)) {
                Output::error('缺少商品必要输入项: ' . $name);
            }
            $attachData[$name] = $val;
        }
    }

    // Parse and map global required order options (dc_order_required)
    // Note: These are designed for frontend checkout (end-user contact info like email/QQ).
    // For API docking orders (B2B), we accept but don't strictly require them,
    // since the docking site collects its own end-user info independently.
    $orderRequiredRaw = Option::get('order_required');
    $requiredFields = json_decode($orderRequiredRaw ?? '[]', true);
    $requiredData = [];
    if (!empty($requiredFields)) {
        foreach ($requiredFields as $item) {
            $name = $item['name'] ?? $item;
            $val = '';
            if (isset($inputValues[$name])) {
                $val = $inputValues[$name];
            } elseif (isset($_POST[$name])) {
                $val = $_POST[$name];
            } elseif (isset($_GET[$name])) {
                $val = $_GET[$name];
            }
            if (empty($val)) {
                if (strpos(strtolower($name), 'email') !== false || strpos($name, '邮箱') !== false) {
                    $val = Input::getStrVar('email') ?: Input::postStrVar('email');
                } elseif (strpos(strtolower($name), 'qq') !== false) {
                    $val = Input::getStrVar('qq') ?: Input::postStrVar('qq');
                }
            }
            // API orders: don't block on missing global required fields
            $requiredData[$name] = $val;
        }
    }

    // 查SKU
    $skuRow = $db->once_fetch_array("SELECT * FROM {$db_prefix}skus WHERE goods_id = {$goodsId} AND sku = '" . addslashes($sku) . "' LIMIT 1");
    if (empty($skuRow)) {
        Output::error('商品规格不存在');
    }

    // 库存校验
    $currentStock = ($goods['is_sku'] === 'y') ? (int)$skuRow['stock'] : (int)$goods['stock'];
    if ($currentStock < $quantity) {
        Output::error('商品库存不足，当前剩余：' . $currentStock);
    }

    // 价格计算 (根据对接用户的会员等级)
    $agentLevel = class_exists('Level_Service') ? Level_Service::getActiveLevelId($user) : (int)($user['level'] ?? -1);
    if (class_exists('Level_Price')) {
        $unitPrice = Level_Price::calculate($skuRow, $goods, $agentLevel);
    } else {
        $unitPrice = (int)$skuRow['guest_price'];
    }
    $totalPrice = $unitPrice * $quantity;

    // 余额校验 (扣除金额为分/100 -> 元)
    $deductYuan = $totalPrice / 100;
    $agentMoney = (float)$user['money'];
    if ($agentMoney < $deductYuan) {
        Output::error('扣款失败，货源账户余额不足，请前往对接站点充值。订单金额：¥' . number_format($deductYuan, 2) . '，当前余额：¥' . number_format($agentMoney, 2));
    }

    // 扣除余额并记录余额变动日志
    $afterMoney = $agentMoney - $deductYuan;
    $db->query("UPDATE {$db_prefix}user SET money = {$afterMoney} WHERE uid = " . $user['uid']);
    $db->query("INSERT INTO {$db_prefix}balance_log (user_id, plus, update_before, money, description, create_time) VALUES ({$user['uid']}, '-', {$agentMoney}, {$deductYuan}, '接口采购商品【" . addslashes($goods['title']) . "】，数量：{$quantity}，商户单号：" . addslashes($outTradeNo) . "', " . time() . ")");

    // 创建本地发货订单 (将支付方式写为“余额支付”，以直观反映是通过主站账户余额购买，并保存 notify_url)
    $localTradeNo = date('YmdHis') . mt_rand(100000, 999999);
    $clientIp = getIp();
    $now = time();
    $db->query("INSERT INTO {$db_prefix}order (client_ip, user_id, out_trade_no, tel, email, amount, create_time, payment, pay_plugin, pay_time, update_time, pay_status, status, pwd, up_no, notify_url) VALUES ('{$clientIp}', {$user['uid']}, '{$localTradeNo}', '', '', {$totalPrice}, {$now}, '余额支付', 'balance', {$now}, {$now}, 1, 1, '" . mt_rand(100000, 999999) . "', '" . addslashes($outTradeNo) . "', '" . addslashes($notifyUrl) . "')");
    $orderId = $db->insert_id();

    // 映射规格属性名称
    $attrSpec = '默认规格';
    if ($goods['is_sku'] == 'y' && $sku !== '0') {
        $valIds = explode('-', $sku);
        $specVals = [];
        foreach ($valIds as $vid) {
            $vRow = $db->once_fetch_array("SELECT name FROM {$db_prefix}sku_value WHERE id = " . (int)$vid);
            if ($vRow) $specVals[] = $vRow['name'];
        }
        if (!empty($specVals)) $attrSpec = implode(', ', $specVals);
    }

    // 插入订单详情子项 (注意：dc_order_list 结构中没有 goods_name 字段，商品名通过 goods_id 关联 dc_goods 获取)
    $db->query("INSERT INTO {$db_prefix}order_list (order_id, goods_id, sku, attr_spec, quantity, unit_price, price, status, cost_price, attach_user) VALUES ({$orderId}, {$goodsId}, '" . addslashes($sku) . "', '" . addslashes($attrSpec) . "', {$quantity}, {$unitPrice}, {$totalPrice}, 0, " . (int)$skuRow['cost_price'] . ", '" . addslashes(json_encode($attachData, JSON_UNESCAPED_UNICODE)) . "')");
    $childOrderId = $db->insert_id();

    // 插入订单必要联系信息
    foreach ($requiredData as $key => $val) {
        $type = (filter_var($val, FILTER_VALIDATE_EMAIL)) ? 'email' : 'string';
        $db->query("INSERT INTO {$db_prefix}order_required (order_id, name, content, type) VALUES ({$orderId}, '" . addslashes($key) . "', '" . addslashes($val) . "', '{$type}')");
    }

    // 核心发货调用
    require_once DC_ROOT . '/include/model/order_model.php';
    $orderModel = new Order_Model();
    $orderModel->deliver($orderId);

    // 获取并组合已发货卡密
    $childOrder = $db->once_fetch_array("SELECT * FROM {$db_prefix}order_list WHERE id = {$childOrderId} LIMIT 1");
    $cardList = [];
    $goodsType = api_resolve_goods_type($goods['id'], $goods);
    $func = "plugin_goods_{$goodsType}_get_order_serect";
    if (function_exists($func)) {
        $serect = $func($db, $db_prefix, $goods, ['id' => $orderId, 'out_trade_no' => $localTradeNo], $childOrder, 0);
        if (!empty($serect['list'])) {
            foreach ($serect['list'] as $kItem) {
                $cardList[] = $kItem['content'];
            }
        }
    }
    // 如果插件没有get_order_serect，从统一deliver表或自动发货内容函数获取
    if (empty($cardList)) {
        $delivers = $db->fetch_all("SELECT content FROM {$db_prefix}deliver WHERE order_list_id = {$childOrderId}");
        if (!empty($delivers)) {
            foreach ($delivers as $d) $cardList[] = $d['content'];
        }
    }
    $deliverContent = implode("\n", $cardList);
    if (empty($deliverContent)) {
        $getDeliverContentFun = "getDeliverContent{$goods['type']}";
        if (function_exists($getDeliverContentFun)) {
            $deliverContent = $getDeliverContentFun($db, $db_prefix, $orderId);
        }
    }
    // 重新获取订单最新状态
    $finalOrder = $db->once_fetch_array("SELECT status FROM {$db_prefix}order WHERE id = {$orderId} LIMIT 1");

    Output::ok([
        'order_id' => $localTradeNo,
        'content' => $deliverContent,
        'status' => (int)($finalOrder['status'] ?? 1)
    ]);
}

/**
 * 5. 接口订单状态与卡密查询
 */
function api_order_query() {
    global $db, $db_prefix;
    $user = api_auth();

    $orderId = trim(Input::getStrVar('order_id'));
    $outTradeNo = trim(Input::getStrVar('out_trade_no'));

    $where = "WHERE user_id = {$user['uid']} AND delete_time IS NULL";
    if (!empty($orderId)) {
        $where .= " AND out_trade_no = '" . addslashes($orderId) . "'";
    } elseif (!empty($outTradeNo)) {
        $where .= " AND up_no = '" . addslashes($outTradeNo) . "'";
    } else {
        Output::error('缺少本地单号 (order_id) 或商户单号 (out_trade_no)');
    }

    $order = $db->once_fetch_array("SELECT id, out_trade_no, up_no, amount, create_time, pay_time, status FROM {$db_prefix}order {$where} LIMIT 1");
    if (empty($order)) {
        Output::error('订单不存在');
    }

    $child = $db->once_fetch_array("SELECT * FROM {$db_prefix}order_list WHERE order_id = {$order['id']} LIMIT 1");
    if (empty($child)) {
        Output::error('订单明细不存在');
    }

    $goods = $db->once_fetch_array("SELECT id, type FROM {$db_prefix}goods WHERE id = {$child['goods_id']}");
    $goodsType = api_resolve_goods_type($child['goods_id'], $goods);

    $cardList = [];
    $func = "plugin_goods_{$goodsType}_get_order_serect";
    if (function_exists($func)) {
        $serect = $func($db, $db_prefix, $goods, $order, $child, 0);
        if (!empty($serect['list'])) {
            foreach ($serect['list'] as $kItem) {
                $cardList[] = $kItem['content'];
            }
        }
    }
    if (empty($cardList)) {
        $delivers = $db->fetch_all("SELECT content FROM {$db_prefix}deliver WHERE order_list_id = {$child['id']}");
        foreach ($delivers as $d) $cardList[] = $d['content'];
    }
    $deliverContent = implode("\n", $cardList);

    // Check if there is an approved aftersale refund for this order list item
    $aftersale = $db->once_fetch_array("SELECT status FROM {$db_prefix}aftersale WHERE order_list_id = {$child['id']} LIMIT 1");
    $orderStatus = (int)$order['status'];
    if ($aftersale && (int)$aftersale['status'] === 2) {
        $orderStatus = 3; // 3 represents refunded/closed
    }

    Output::ok([
        'order_id' => $order['out_trade_no'],
        'out_trade_no' => $order['up_no'],
        'amount' => $order['amount'] / 100,
        'status' => $orderStatus,
        'pay_time' => $order['pay_time'],
        'content' => $deliverContent
    ]);
}

/**
 * 6. 获取对接用户信息(余额、等级等)
 */
function api_user_info() {
    global $db, $db_prefix;
    $user = api_auth();
    
    $levelName = '普通会员';
    if (class_exists('Level_Service')) {
        $levelId = Level_Service::getActiveLevelId($user);
        if ($levelId > 0) {
            $memberModel = new Member_Model();
            $level = $memberModel->getById($levelId);
            if ($level) {
                $levelName = $level['name'];
            }
        }
    }
    
    Output::ok([
        'uid' => (int)$user['uid'],
        'money' => (float)$user['money'],
        'level_name' => $levelName
    ]);
}

