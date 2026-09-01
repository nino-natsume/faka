<?php

require_once 'globals.php';


$withdrawModel = new Withdraw_Model();

if (empty($action)) {

    $page = Input::getIntVar('page', 1);


    $br = '<a href="./">数据中心</a><a href="./order.php">订单管理</a><a><cite>提现申请</cite></a>';
    include View::getAdmView('header');
    require_once View::getAdmView('withdraw');
    include View::getAdmView('footer');
    View::output();
}

if($action == 'index'){
    $page = Input::getIntVar('page', 1);
    $limit = Input::getIntVar('limit', 10);
    $filters = [
        'user_id' => Input::getIntVar('user_id', 0),
        'status' => Input::getStrVar('status'),
        'method' => Input::getStrVar('method'),
        'keyword' => Input::getStrVar('keyword')
    ];
    $res = $withdrawModel->getWithdrawList($filters, $page, $limit);

    output::data($res['list'], $res['total']);
}

if($action == 'stats'){
    $filters = [
        'user_id' => Input::getIntVar('user_id', 0),
        'status' => Input::getStrVar('status'),
        'method' => Input::getStrVar('method'),
        'keyword' => Input::getStrVar('keyword')
    ];
    Ret::success('获取成功', $withdrawModel->getStats($filters));
}

if($action == 'cmd'){
    LoginAuth::checkToken();
    $type = trim((string)($_POST['type'] ?? Input::getStrVar('type')));
    $id = Input::postIntVar('id');
    if(empty($id)){
        $id = Input::postIntVar('ids');
    }
    $actualAmount = Input::postStrVar('actual_amount');
    $handleRemark = Input::postStrVar('handle_remark');
    $withdrawModel = new Withdraw_Model();
    if(empty($id)){
        Ret::error('参数错误');
    }
    if($type == 'finish'){
        $result = $withdrawModel->pass($id, $actualAmount, $handleRemark);
        if($result['code'] != 0){
            Ret::error($result['msg']);
        }
        Ret::success($result['msg']);
    }
    if($type == 'reject'){
        $result = $withdrawModel->reject($id, $handleRemark);
        if($result['code'] != 0){
            Ret::error($result['msg']);
        }
        Ret::success($result['msg']);
    }
    Ret::error('不支持的操作类型');
}

if($action == 'del'){
    LoginAuth::checkToken();
    $ids = Input::postStrVar('ids');
    if(empty($ids)){
        Ret::error('请选择要删除的记录');
    }
    $idArr = array_map('intval', explode(',', $ids));
    $idArr = array_filter($idArr, function($v){ return $v > 0; });
    if(empty($idArr)){
        Ret::error('参数错误');
    }
    $idStr = implode(',', $idArr);
    $db = Database::getInstance();
    $db->query("DELETE FROM " . DB_PREFIX . "withdraw WHERE id IN ({$idStr})");
    Ret::success('删除成功，共删除 ' . count($idArr) . ' 条记录');
}
