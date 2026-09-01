<?php
/**
 * 售后订单管理
 */

require_once 'globals.php';

$db = Database::getInstance();
$db_prefix = DB_PREFIX;

// 售后订单列表
if (empty($action)) {
    $br = '<a href="./">数据中心</a><a href="./order.php">订单管理</a><a><cite>售后订单</cite></a>';
    
    include View::getAdmView('header');
    require_once View::getAdmView('aftersale');
    include View::getAdmView('footer');
    View::output();
}

// 获取售后订单列表数据
if ($action == 'index') {
    $page = Input::getIntVar('page', 1);
    $limit = Input::getIntVar('limit', 10);
    $start = ($page - 1) * $limit;
    
    $where = "1=1";
    
    // 搜索条件
    $out_trade_no = Input::getStrVar('out_trade_no');
    $status = Input::getStrVar('status');
    
    if (!empty($out_trade_no)) {
        $where .= " AND out_trade_no LIKE '%{$out_trade_no}%'";
    }
    if ($status !== '' && $status !== null) {
        // 支持多状态筛选，如 "0,1"
        if (strpos($status, ',') !== false) {
            $where .= " AND status IN ({$status})";
        } else {
            $where .= " AND status = {$status}";
        }
    }
    
    // 重开申请筛选
    $reopen_status = Input::getStrVar('reopen_status');
    if ($reopen_status !== '' && $reopen_status !== null) {
        $where .= " AND reopen_status = " . intval($reopen_status);
    }
    
    // 获取总数
    $countSql = "SELECT COUNT(*) as total FROM {$db_prefix}aftersale WHERE {$where}";
    $countRes = $db->once_fetch_array($countSql);
    $total = $countRes['total'];
    
    // 获取列表
    $sql = "SELECT * FROM {$db_prefix}aftersale WHERE {$where} ORDER BY id DESC LIMIT {$start}, {$limit}";
    $list = $db->fetch_all($sql);
    
    foreach ($list as $key => $val) {
        // 状态映射
        $statusMap = [0 => '待处理', 1 => '处理中', 2 => '已完成', 3 => '用户已关闭', 4 => '已拒绝'];
        $typeMap = ['cant_use' => '不会使用', 'invalid' => '无效商品', 'fraud' => '欺诈骗钱', 'kami_error' => '卡密错误', 'other' => '其他问题'];
        $list[$key]['status_text'] = $statusMap[$val['status']] ?? '未知';
        $list[$key]['type_text'] = $typeMap[$val['type']] ?? $val['type'];
        $list[$key]['create_time_text'] = date('Y-m-d H:i:s', $val['create_time']);
        $list[$key]['handle_time_text'] = $val['handle_time'] ? date('Y-m-d H:i:s', $val['handle_time']) : '-';
        $reopenStatusMap = [0 => '', 1 => '待审核', 2 => '已批准', 3 => '已拒绝'];
        $list[$key]['reopen_status'] = intval($val['reopen_status'] ?? 0);
        $list[$key]['reopen_status_text'] = $reopenStatusMap[$list[$key]['reopen_status']] ?? '';
        $list[$key]['reopen_reason'] = $val['reopen_reason'] ?? '';
        $list[$key]['reopen_time_text'] = !empty($val['reopen_time']) ? date('Y-m-d H:i:s', $val['reopen_time']) : '-';
    }
    
    output::data($list, $total);
}

// 获取各状态数量
if ($action == 'get_counts') {
    $counts = [
        'all' => 0,
        'pending' => 0,    // 待处理 + 处理中
        'completed' => 0,  // 已完成
        'rejected' => 0,   // 已拒绝
        'closed' => 0      // 用户关闭
    ];
    
    // 获取全部数量
    $res = $db->once_fetch_array("SELECT COUNT(*) as cnt FROM {$db_prefix}aftersale");
    $counts['all'] = intval($res['cnt']);
    
    // 获取待处理+处理中数量
    $res = $db->once_fetch_array("SELECT COUNT(*) as cnt FROM {$db_prefix}aftersale WHERE status IN (0, 1)");
    $counts['pending'] = intval($res['cnt']);
    
    // 获取已完成数量
    $res = $db->once_fetch_array("SELECT COUNT(*) as cnt FROM {$db_prefix}aftersale WHERE status = 2");
    $counts['completed'] = intval($res['cnt']);
    
    // 获取已拒绝数量
    $res = $db->once_fetch_array("SELECT COUNT(*) as cnt FROM {$db_prefix}aftersale WHERE status = 4");
    $counts['rejected'] = intval($res['cnt']);
    
    // 获取用户关闭数量
    $res = $db->once_fetch_array("SELECT COUNT(*) as cnt FROM {$db_prefix}aftersale WHERE status = 3");
    $counts['closed'] = intval($res['cnt']);
    
    // 获取重开申请数量（待审核）
    $res = $db->once_fetch_array("SELECT COUNT(*) as cnt FROM {$db_prefix}aftersale WHERE reopen_status = 1");
    $counts['reopen'] = intval($res['cnt']);
    
    output::ok($counts);
}

// 处理售后
if ($action == 'handle') {
    $id = Input::postIntVar('id');
    $status = Input::postIntVar('status');
    $handle_remark = Input::postStrVar('handle_remark');
    
    if (empty($id)) {
        output::error('参数错误');
    }
    
    // 获取售后信息（用于钩子）
    $aftersale = $db->once_fetch_array("SELECT * FROM {$db_prefix}aftersale WHERE id = {$id}");
    $old_status = $aftersale ? $aftersale['status'] : null;
    
    $timestamp = time();
    $handle_remark = addslashes($handle_remark);
    
    $sql = "UPDATE {$db_prefix}aftersale SET status = {$status}, handle_remark = '{$handle_remark}', handle_time = {$timestamp} WHERE id = {$id}";
    $db->query($sql);
    
    // 触发售后状态变更钩子
    if ($aftersale) {
        doAction('aftersale_status_change', $id, [
            'old_status' => $old_status,
            'new_status' => $status,
            'handle_remark' => $handle_remark,
            'order_id' => $aftersale['order_id'],
            'out_trade_no' => $aftersale['out_trade_no']
        ]);
    }

    // 售后同意（status=2 已完成）时，反扣分销分成
    // 仅当状态真正切换到已完成时扣一次（防止重复点击）
    if ($status == 2 && intval($old_status) != 2 && $aftersale && !empty($aftersale['out_trade_no']) && class_exists('Commission_Model')) {
        try {
            $commissionModel = new Commission_Model();
            // 默认 0 保留（全部扣回），如需保留一定比例可接入业务规则
            $commissionModel->deductOrder((string)$aftersale['out_trade_no'], 0);
        } catch (\Throwable $e) {
            if (class_exists('User_Log_Model')) {
                User_Log_Model::log(
                    0,
                    'commission_refund_fail',
                    '退款反扣分成调用失败：' . $e->getMessage() . '，订单号：' . ($aftersale['out_trade_no'] ?? '')
                );
            }
        }
    }

    // 状态文字映射
    $statusTextMap = [1 => '处理中', 2 => '已完成', 4 => '已拒绝'];
    $statusText = $statusTextMap[$status] ?? '已更新';
    
    output::ok("售后状态已更新为「{$statusText}」");
}

// 获取售后详情
if ($action == 'detail') {
    $id = Input::getIntVar('id');
    
    $sql = "SELECT * FROM {$db_prefix}aftersale WHERE id = {$id}";
    $aftersale = $db->once_fetch_array($sql);
    
    if (empty($aftersale)) {
        output::error('售后订单不存在');
    }
    
    // 状态映射
    $statusMap = [0 => '待处理', 1 => '处理中', 2 => '已完成', 3 => '用户已关闭', 4 => '已拒绝'];
    $typeMap = ['cant_use' => '不会使用', 'invalid' => '无效商品', 'fraud' => '欺诈骗钱', 'kami_error' => '卡密错误', 'other' => '其他问题'];
    
    $aftersale['status_text'] = $statusMap[$aftersale['status']] ?? '未知';
    $aftersale['type_text'] = $typeMap[$aftersale['type']] ?? $aftersale['type'];
    $aftersale['create_time_text'] = date('Y-m-d H:i:s', $aftersale['create_time']);
    $aftersale['handle_time_text'] = $aftersale['handle_time'] ? date('Y-m-d H:i:s', $aftersale['handle_time']) : '-';
    $reopenStatusMap = [0 => '', 1 => '待审核', 2 => '已批准', 3 => '已拒绝'];
    $aftersale['reopen_status'] = intval($aftersale['reopen_status'] ?? 0);
    $aftersale['reopen_status_text'] = $reopenStatusMap[$aftersale['reopen_status']] ?? '';
    $aftersale['reopen_reason'] = $aftersale['reopen_reason'] ?? '';
    $aftersale['reopen_time_text'] = !empty($aftersale['reopen_time']) ? date('Y-m-d H:i:s', $aftersale['reopen_time']) : '-';
    
    output::ok($aftersale);
}

// 删除售后
if ($action == 'del') {
    $ids = Input::postStrVar('ids');
    
    if (empty($ids)) {
        output::error('参数错误');
    }
    
    // 统计删除数量
    $idArr = explode(',', $ids);
    $count = count($idArr);
    
    $sql = "DELETE FROM {$db_prefix}aftersale WHERE id IN ({$ids})";
    $db->query($sql);
    
    // 同时删除相关聊天记录
    $db->query("DELETE FROM {$db_prefix}aftersale_chat WHERE aftersale_id IN ({$ids})");
    
    output::ok("已删除 {$count} 条售后记录");
}

// 重新开启售后
if ($action == 'reopen') {
    $id = Input::postIntVar('id');
    
    if (empty($id)) {
        output::error('参数错误');
    }
    
    // 获取售后信息
    $aftersale = $db->once_fetch_array("SELECT * FROM {$db_prefix}aftersale WHERE id = {$id}");
    if (empty($aftersale)) {
        output::error('售后订单不存在');
    }
    
    // 只有已完成、用户已关闭、已拒绝的售后才能重开
    if (!in_array($aftersale['status'], [2, 3, 4])) {
        output::error('当前状态不支持重开');
    }
    
    $timestamp = time();
    
    // 更新状态为处理中，清空处理备注和重开状态
    $sql = "UPDATE {$db_prefix}aftersale SET status = 1, handle_remark = '', handle_time = {$timestamp}, reopen_status = 2, reopen_reason = '' WHERE id = {$id}";
    $db->query($sql);
    
    // 添加系统消息
    $content = '【系统消息】商家已重新开启售后，请继续沟通';
    $db->query("INSERT INTO {$db_prefix}aftersale_chat (aftersale_id, order_id, order_list_id, out_trade_no, sender_type, content, create_time) 
                VALUES ({$id}, {$aftersale['order_id']}, {$aftersale['order_list_id']}, '{$aftersale['out_trade_no']}', 'system', '{$content}', {$timestamp})");
    
    output::ok('售后已重新开启');
}


// 拒绝重开申请
if ($action == 'reject_reopen') {
    $id = Input::postIntVar('id');
    $remark = Input::postStrVar('remark');
    
    if (empty($id)) {
        output::error('参数错误');
    }
    
    $aftersale = $db->once_fetch_array("SELECT * FROM {$db_prefix}aftersale WHERE id = {$id}");
    if (empty($aftersale)) {
        output::error('售后订单不存在');
    }
    
    if (intval($aftersale['reopen_status'] ?? 0) != 1) {
        output::error('该售后无待审核的重开申请');
    }
    
    $remark_safe = addslashes($remark);
    $timestamp = time();
    
    $db->query("UPDATE {$db_prefix}aftersale SET reopen_status = 3 WHERE id = {$id}");
    
    // 添加系统消息
    $content = '【系统消息】商家已拒绝重开售后申请';
    if (!empty($remark)) {
        $content .= '：' . $remark;
    }
    $content_safe = addslashes($content);
    $db->query("INSERT INTO {$db_prefix}aftersale_chat (aftersale_id, order_id, order_list_id, out_trade_no, sender_type, content, create_time) 
                VALUES ({$id}, {$aftersale['order_id']}, {$aftersale['order_list_id']}, '{$aftersale['out_trade_no']}', 'system', '{$content_safe}', {$timestamp})");
    
    output::ok('已拒绝重开申请');
}

// 获取聊天记录
if ($action == 'get_chat') {
    $id = Input::getIntVar('id');
    
    // 获取售后信息
    $aftersale = $db->once_fetch_array("SELECT * FROM {$db_prefix}aftersale WHERE id = {$id}");
    if (empty($aftersale)) {
        output::error('售后订单不存在');
    }
    
    // 获取聊天记录
    $sql = "SELECT * FROM {$db_prefix}aftersale_chat WHERE aftersale_id = {$id} ORDER BY id ASC";
    $messages = $db->fetch_all($sql);
    
    foreach ($messages as $key => $val) {
        $messages[$key]['create_time_text'] = date('Y-m-d H:i:s', $val['create_time']);
    }
    
    output::ok($messages);
}

// 发送聊天消息（管理员端）
if ($action == 'send_chat') {
    $id = Input::postIntVar('id');
    $content = Input::postStrVar('content');
    
    if (empty($id)) {
        output::error('参数错误');
    }
    if (empty($content)) {
        output::error('消息内容不能为空');
    }
    
    // 获取售后信息
    $aftersale = $db->once_fetch_array("SELECT * FROM {$db_prefix}aftersale WHERE id = {$id}");
    if (empty($aftersale)) {
        output::error('售后订单不存在');
    }
    
    $content_safe = addslashes($content);
    $timestamp = time();
    
    $sql = "INSERT INTO {$db_prefix}aftersale_chat (aftersale_id, order_id, order_list_id, out_trade_no, sender_type, content, create_time) 
            VALUES ({$id}, {$aftersale['order_id']}, {$aftersale['order_list_id']}, '{$aftersale['out_trade_no']}', 'admin', '{$content_safe}', {$timestamp})";
    
    $db->query($sql);
    
    // 如果状态是待处理，自动改为处理中
    if ($aftersale['status'] == 0) {
        $db->query("UPDATE {$db_prefix}aftersale SET status = 1, handle_time = {$timestamp} WHERE id = {$id}");
    }
    
    output::ok();
}

// 上传聊天图片（管理员端）
if ($action == 'upload_chat_image') {
    if (empty($_FILES['file'])) {
        output::error('请选择图片');
    }
    
    $file = $_FILES['file'];
    
    // 验证文件类型
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        output::error('只支持 JPG、PNG、GIF、WEBP 格式的图片');
    }
    
    // 验证文件大小（5MB）
    if ($file['size'] > 5 * 1024 * 1024) {
        output::error('图片大小不能超过 5MB');
    }
    
    // 生成文件名
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'aftersale_admin_' . date('YmdHis') . '_' . mt_rand(1000, 9999) . '.' . $ext;
    
    // 保存目录
    $uploadDir = DC_ROOT . '/content/uploadfile/aftersale/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $targetPath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $url = DC_URL . 'content/uploadfile/aftersale/' . $filename;
        output::ok(['url' => $url]);
    } else {
        output::error('上传失败');
    }
}

// 撤回聊天消息（管理员端）
if ($action == 'recall_chat') {
    $id = Input::postIntVar('id');
    
    if (empty($id)) {
        output::error('参数错误');
    }
    
    // 获取消息信息
    $msg = $db->once_fetch_array("SELECT * FROM {$db_prefix}aftersale_chat WHERE id = {$id}");
    if (empty($msg)) {
        output::error('消息不存在');
    }
    
    // 只能撤回管理员自己的消息
    if ($msg['sender_type'] != 'admin') {
        output::error('只能撤回自己发送的消息');
    }
    
    // 获取撤回时限配置（分钟）
    $recall_minutes = Option::get('aftersale_recall_minutes');
    $recall_minutes = ($recall_minutes === '' || $recall_minutes === null) ? 2 : intval($recall_minutes);
    
    // 如果设置为0，表示不允许撤回
    if ($recall_minutes <= 0) {
        output::error('撤回功能已关闭');
    }
    
    // 检查是否在时限内
    $recall_seconds = $recall_minutes * 60;
    if (time() - $msg['create_time'] > $recall_seconds) {
        output::error("消息发送超过{$recall_minutes}分钟，无法撤回");
    }
    
    // 检查是否已撤回
    if (!empty($msg['is_recalled'])) {
        output::error('该消息已撤回');
    }
    
    // 标记为已撤回
    $db->query("UPDATE {$db_prefix}aftersale_chat SET is_recalled = 1 WHERE id = {$id}");
    
    output::ok();
}
