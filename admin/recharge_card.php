<?php
require_once 'globals.php';

$RechargeCard_Model = new Recharge_Card_Model();

if (empty($action)) {
    $br = '<a href="./">数据中心</a><a href="./user.php">用户管理</a><a><cite>充值卡密</cite></a>';
    $stats = $RechargeCard_Model->getStats();
    include View::getAdmView('header');
    require_once View::getAdmView('recharge_card');
    include View::getAdmView('footer');
    View::output();
}

if ($action == 'index') {
    $page = Input::getIntVar('page', 1);
    $limit = Input::getIntVar('limit', 15);
    $filters = [
        'keyword' => Input::getStrVar('keyword'),
        'type' => Input::getStrVar('type'),
        'batch_no' => Input::getStrVar('batch_no'),
        'status' => isset($_GET['status']) ? trim($_GET['status']) : ''
    ];
    $result = $RechargeCard_Model->getList($page, $limit, $filters);
    output::data($result['list'], $result['total']);
}

if ($action == 'stats') {
    output::ok($RechargeCard_Model->getStats());
}

if ($action == 'last_batch') {
    output::ok($RechargeCard_Model->getLastBatch(UID));
}

if ($action == 'generate') {
    LoginAuth::checkToken();
    $type = Input::postStrVar('type');
    $title = Input::postStrVar('title');
    $amount = Input::postStrVar('amount');
    $num = Input::postIntVar('num');
    if (empty($type)) {
        output::error('请填写卡密类型');
    }
    if (empty($title)) {
        output::error('请填写充值卡名称');
    }
    if (!is_numeric($amount) || floatval($amount) <= 0) {
        output::error('请填写正确的充值卡面额');
    }
    if ($num <= 0 || $num > 500) {
        output::error('生成数量需在 1 - 500 之间');
    }
    $result = $RechargeCard_Model->generate($type, $title, $amount, $num, UID);
    output::ok([
        'batch' => $result,
        'stats' => $RechargeCard_Model->getStats()
    ]);
}

if ($action == 'toggle') {
    LoginAuth::checkToken();
    $id = Input::postIntVar('id');
    $status = Input::postIntVar('status');
    if (!$RechargeCard_Model->toggleStatus($id, $status)) {
        output::error('操作失败，已使用卡密不允许变更状态');
    }
    output::ok($RechargeCard_Model->getStats());
}

if ($action == 'del') {
    LoginAuth::checkToken();
    $ids = Input::postStrVar('ids');
    $ids = explode(',', $ids);
    $count = $RechargeCard_Model->deleteByIds($ids);
    if ($count <= 0) {
        output::error('请选择可删除的卡密，已使用卡密不允许删除');
    }
    output::ok($RechargeCard_Model->getStats());
}

if ($action == 'export') {
    LoginAuth::checkToken();
    $mode = Input::getStrVar('mode');
    if (!in_array($mode, ['current', 'last', 'selected'], true)) {
        $mode = 'current';
    }
    $filters = [
        'keyword' => Input::getStrVar('keyword'),
        'type' => Input::getStrVar('type'),
        'batch_no' => Input::getStrVar('batch_no'),
        'status' => isset($_GET['status']) ? trim($_GET['status']) : ''
    ];
    $ids = explode(',', Input::getStrVar('ids'));
    $rows = $RechargeCard_Model->getRowsForExport($mode, UID, $filters, $ids);
    if (empty($rows)) {
        exit('没有可导出的充值卡密');
    }
    $filename = 'recharge_cards_' . $mode . '_' . date('YmdHis') . '.txt';
    header('Content-Type: text/plain; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo "卡密\t名称\t类型\t面额\t状态\t批次\t使用用户\t生成时间\t使用时间\r\n";
    foreach ($rows as $row) {
        echo $row['card_key'] . "\t" . $row['title'] . "\t" . $row['type'] . "\t" . $row['amount_text'] . "\t" . $row['status_text'] . "\t" . $row['batch_no'] . "\t" . $row['user_display'] . "\t" . $row['create_time_text'] . "\t" . $row['use_time_text'] . "\r\n";
    }
    exit;
}
