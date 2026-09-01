<?php
/**
 * 充值订单管理
 */

require_once 'globals.php';

$db = Database::getInstance();
$prefix = DB_PREFIX;

// 充值订单列表页
if (empty($action)) {
    $br = '<a href="./">数据中心</a><a href="./order.php">订单管理</a><a><cite>充值订单</cite></a>';

    include View::getAdmView('header');
    require_once View::getAdmView('order_recharge');
    include View::getAdmView('footer');
    View::output();
}

// 充值订单数据接口
if ($action == 'index') {
    $page = Input::getIntVar('page', 1);
    $limit = Input::getIntVar('limit', 10);
    $start = ($page - 1) * $limit;

    $w = " AND o.device = 'balance_recharge'";

    // 搜索条件
    $email_username = Input::getStrVar('email_username');
    if (!empty($email_username)) {
        $email_username = addslashes($email_username);
        $w .= " AND (u.email LIKE CONCAT('%', '{$email_username}', '%') OR u.username LIKE CONCAT('%', '{$email_username}', '%') OR u.nickname LIKE CONCAT('%', '{$email_username}', '%') OR u.tel LIKE CONCAT('%', '{$email_username}', '%'))";
    }

    $out_trade_no = Input::getStrVar('out_trade_no');
    if (!empty($out_trade_no)) {
        $out_trade_no = addslashes($out_trade_no);
        $w .= " AND (o.out_trade_no LIKE CONCAT('%', '{$out_trade_no}', '%') OR o.up_no LIKE CONCAT('%', '{$out_trade_no}', '%'))";
    }

    $client_ip = Input::getStrVar('client_ip');
    if (!empty($client_ip)) {
        $client_ip = addslashes($client_ip);
        $w .= " AND o.client_ip LIKE CONCAT('%', '{$client_ip}', '%')";
    }

    // 支付状态筛选
    if (isset($_GET['status']) && $_GET['status'] !== '') {
        $status = intval($_GET['status']);
        $w .= " AND o.status = {$status}";
    }

    // 支付方式筛选
    $pay_plugin = Input::getStrVar('pay_plugin');
    if (!empty($pay_plugin)) {
        $pay_plugin = addslashes($pay_plugin);
        $w .= " AND o.pay_plugin = '{$pay_plugin}'";
    }

    // 总数
    $countSql = "SELECT COUNT(*) AS total FROM {$prefix}order AS o LEFT JOIN {$prefix}user AS u ON o.user_id = u.uid WHERE o.delete_time IS NULL {$w}";
    $countRow = $db->once_fetch_array($countSql);
    $total = intval($countRow['total']);

    // 列表
    $sql = "SELECT o.*, u.email AS user_email, u.tel AS user_tel, u.nickname AS user_nickname
            FROM {$prefix}order AS o
            LEFT JOIN {$prefix}user AS u ON o.user_id = u.uid
            WHERE o.delete_time IS NULL {$w}
            ORDER BY o.id DESC
            LIMIT {$start}, {$limit}";

    $res = $db->query($sql);
    $data = [];

    // 支付插件名称映射
    $paymentNames = [
        'balance' => '余额支付',
        'epay_wx' => '易支付(微信)',
        'epay_ali' => '易支付(支付宝)',
        'epay_qq' => '易支付(QQ钱包)',
        'alipay' => '支付宝',
        'wxpay' => '微信支付',
        'manual_pay' => '人工支付',
    ];
    $payPlugins = ['manual_pay', 'ynl_wx', 'ynl_ali', 'epay'];
    foreach ($payPlugins as $pluginName) {
        $pluginStorage = Storage::getInstance($pluginName);
        $displayName = $pluginStorage->getValue('display_name');
        if (!empty($displayName)) {
            $paymentNames[$pluginName] = $displayName;
        }
    }

    while ($row = $db->fetch_array($res)) {
        $row['amount_yuan'] = number_format($row['amount'] / 100, 2);
        $row['pay_time_text'] = empty($row['pay_time']) ? '' : date('Y-m-d H:i:s', $row['pay_time']);
        $row['create_time_text'] = empty($row['create_time']) ? '' : date('Y-m-d H:i:s', $row['create_time']);
        $row['user_nickname'] = $row['user_id'] == 0 ? '游客' : ($row['user_nickname'] ?: '未设置');
        if (empty($row['payment']) && !empty($row['pay_plugin'])) {
            $row['payment'] = $paymentNames[$row['pay_plugin']] ?? $row['pay_plugin'];
        }
        // 充值订单状态文字
        if ($row['status'] == 0) {
            $row['status_text'] = '未支付';
        } elseif ($row['status'] == 2) {
            $row['status_text'] = '已完成';
        } else {
            $row['status_text'] = '已支付';
        }
        $data[] = $row;
    }

    output::data($data, $total);
}

// 导出充值订单
if ($action == 'export') {
    $w = " AND o.device = 'balance_recharge'";

    $email_username = Input::getStrVar('email_username');
    if (!empty($email_username)) {
        $email_username = addslashes($email_username);
        $w .= " AND (u.email LIKE CONCAT('%', '{$email_username}', '%') OR u.username LIKE CONCAT('%', '{$email_username}', '%') OR u.nickname LIKE CONCAT('%', '{$email_username}', '%') OR u.tel LIKE CONCAT('%', '{$email_username}', '%'))";
    }
    $out_trade_no = Input::getStrVar('out_trade_no');
    if (!empty($out_trade_no)) {
        $out_trade_no = addslashes($out_trade_no);
        $w .= " AND (o.out_trade_no LIKE CONCAT('%', '{$out_trade_no}', '%') OR o.up_no LIKE CONCAT('%', '{$out_trade_no}', '%'))";
    }
    $client_ip = Input::getStrVar('client_ip');
    if (!empty($client_ip)) {
        $client_ip = addslashes($client_ip);
        $w .= " AND o.client_ip LIKE CONCAT('%', '{$client_ip}', '%')";
    }
    if (isset($_GET['status']) && $_GET['status'] !== '') {
        $status = intval($_GET['status']);
        $w .= " AND o.status = {$status}";
    }
    $pay_plugin = Input::getStrVar('pay_plugin');
    if (!empty($pay_plugin)) {
        $pay_plugin = addslashes($pay_plugin);
        $w .= " AND o.pay_plugin = '{$pay_plugin}'";
    }

    $maxExport = 5000;
    $sql = "SELECT o.*, u.email AS user_email, u.tel AS user_tel, u.nickname AS user_nickname
            FROM {$prefix}order AS o
            LEFT JOIN {$prefix}user AS u ON o.user_id = u.uid
            WHERE o.delete_time IS NULL {$w}
            ORDER BY o.id DESC
            LIMIT {$maxExport}";
    $res = $db->query($sql);

    $paymentNames = [
        'balance' => '余额支付',
        'epay_wx' => '易支付(微信)', 'epay_ali' => '易支付(支付宝)', 'epay_qq' => '易支付(QQ钱包)',
        'alipay' => '支付宝', 'wxpay' => '微信支付', 'manual_pay' => '人工支付',
    ];
    $payPlugins = ['manual_pay', 'ynl_wx', 'ynl_ali', 'epay'];
    foreach ($payPlugins as $pluginName) {
        $pluginStorage = Storage::getInstance($pluginName);
        $displayName = $pluginStorage->getValue('display_name');
        if (!empty($displayName)) $paymentNames[$pluginName] = $displayName;
    }

    $filename = '充值订单_' . date('YmdHis') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $fp = fopen('php://output', 'w');
    fwrite($fp, "\xEF\xBB\xBF");
    fputcsv($fp, ['订单号', '商户订单号', '充值金额', '用户昵称', '用户邮箱', '用户手机', '状态', '支付方式', '下单IP', '下单时间', '支付时间']);

    while ($row = $db->fetch_array($res)) {
        $amountYuan = number_format($row['amount'] / 100, 2);
        if ($row['status'] == 0) { $statusText = '未支付'; }
        elseif ($row['status'] == 2) { $statusText = '已完成'; }
        else { $statusText = '已支付'; }
        $payment = '';
        if (!empty($row['payment'])) {
            $payment = $row['payment'];
        } elseif (!empty($row['pay_plugin'])) {
            $payment = $paymentNames[$row['pay_plugin']] ?? $row['pay_plugin'];
        }
        fputcsv($fp, [
            $row['out_trade_no'],
            $row['up_no'] ?: '',
            $amountYuan,
            $row['user_id'] == 0 ? '游客' : ($row['user_nickname'] ?: '未设置'),
            $row['user_email'] ?: '',
            $row['user_tel'] ?: '',
            $statusText,
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
if ($action == 'repay') {
    try {
        $out_trade_no = Input::postStrVar('out_trade_no');
        $db = Database::getInstance();
        $repayOrder = $db->once_fetch_array("SELECT * FROM " . DB_PREFIX . "order WHERE out_trade_no = '" . addslashes($out_trade_no) . "'");
        $payController = new Pay_Controller();
        $payController->repay($out_trade_no);
        if ($repayOrder) {
            User_Log_Model::log($repayOrder['user_id'], 'order_repay', '后台充值订单补单，订单号: ' . $out_trade_no . '，金额: ¥' . ($repayOrder['amount'] / 100), $repayOrder['amount'] / 100);
        }
        output::ok();
    } catch (\Throwable $e) {
        output::error('补单失败: ' . $e->getMessage());
    }
}

// 删除订单
if ($action == 'del') {
    $ids = Input::postStrVar('ids');
    // 安全：只允许数字和逗号
    $ids = preg_replace('/[^0-9,]/', '', $ids);
    if (empty($ids)) {
        output::error('参数错误');
    }
    $timestamp = time();
    $sql = "UPDATE {$prefix}order SET delete_time = {$timestamp} WHERE id IN ({$ids}) AND device = 'balance_recharge'";
    $db->query($sql);
    output::ok();
}

// 清理未支付充值订单
if ($action == 'clean_cancelled') {
    LoginAuth::checkToken();
    $days = Input::postIntVar('days', 180);
    if (!in_array($days, [30, 90, 180, 365])) $days = 180;
    $before = time() - $days * 86400;
    $timestamp = time();
    $sql = "UPDATE {$prefix}order SET delete_time = {$timestamp} WHERE status = 0 AND delete_time IS NULL AND create_time < {$before} AND device = 'balance_recharge'";
    $db->query($sql);
    $affected = $db->affected_rows();
    output::ok(['count' => $affected]);
}

// 充值订单详情
if ($action == 'detail') {
    $order_id = Input::getIntVar('order_id');
    $order = $db->once_fetch_array("SELECT * FROM {$prefix}order WHERE id = {$order_id} AND device = 'balance_recharge'");
    if (empty($order)) {
        echo '订单不存在';
        exit;
    }
    $order['amount_yuan'] = number_format($order['amount'] / 100, 2);
    $order['pay_time_text'] = empty($order['pay_time']) ? '未支付' : date('Y-m-d H:i:s', $order['pay_time']);
    $order['create_time_text'] = empty($order['create_time']) ? '' : date('Y-m-d H:i:s', $order['create_time']);
    $order['expire_time_text'] = empty($order['expire_time']) ? '' : date('Y-m-d H:i:s', $order['expire_time']);
    if ($order['status'] == 0) {
        $order['status_text'] = '未支付';
    } elseif ($order['status'] == 2) {
        $order['status_text'] = '已完成';
    } else {
        $order['status_text'] = '已支付';
    }

    $user = $db->once_fetch_array("SELECT * FROM {$prefix}user WHERE uid = {$order['user_id']}");

    include View::getAdmView('open_head');
    require_once View::getAdmView('order_recharge_detail');
    include View::getAdmView('open_foot');
    View::output();
}
