<?php
/**
 * 用户操作日志管理
 */
require_once 'globals.php';

$UserLog_Model = new User_Log_Model();

if (empty($action)) {
    $popup = !empty($_GET['popup']);

    // 获取已有操作类型（用于筛选下拉）
    $logTypes = $UserLog_Model->getLogTypes();

    if ($popup) {
        include View::getAdmView('open_head');
        require_once View::getAdmView('user_log');
        include View::getAdmView('open_foot');
    } else {
        $br = '<a href="./">数据中心</a><a href="user.php">用户管理</a><a><cite>用户日志</cite></a>';
        include View::getAdmView('header');
        require_once View::getAdmView('user_log');
        include View::getAdmView('footer');
    }
    View::output();
}

if ($action == 'index') {
    $page = Input::getIntVar('page', 1);
    $limit = Input::getIntVar('limit', 10);
    $start = ($page - 1) * $limit;

    $uid = Input::getStrVar('uid');
    $type = Input::getStrVar('type');
    $keyword = Input::getStrVar('keyword');
    $ip = Input::getStrVar('ip');
    $date_start = Input::getStrVar('date_start');
    $date_end = Input::getStrVar('date_end');

    $where = '';

    if (!empty($uid)) {
        $uid = intval($uid);
        $where .= " AND l.uid = {$uid}";
    }
    if (!empty($type)) {
        $type = addslashes($type);
        $where .= " AND l.type = '{$type}'";
    }
    if (!empty($keyword)) {
        $keyword = addslashes($keyword);
        $where .= " AND l.content LIKE '%{$keyword}%'";
    }
    if (!empty($ip)) {
        $ip = addslashes($ip);
        $where .= " AND l.ip LIKE '%{$ip}%'";
    }
    if (!empty($date_start)) {
        $ts = strtotime($date_start);
        if ($ts) $where .= " AND l.create_time >= {$ts}";
    }
    if (!empty($date_end)) {
        $ts = strtotime($date_end . ' 23:59:59');
        if ($ts) $where .= " AND l.create_time <= {$ts}";
    }

    // 排序
    $sort_field = Input::getStrVar('field', 'id');
    $sort_order = Input::getStrVar('order', 'desc');
    $allowed_fields = ['id', 'type', 'amount', 'create_time'];
    if (!in_array($sort_field, $allowed_fields)) $sort_field = 'id';
    if (!in_array($sort_order, ['asc', 'desc'])) $sort_order = 'desc';
    $orderBy = "ORDER BY l.{$sort_field} {$sort_order}";

    $total = $UserLog_Model->getLogCount($where);
    $list = $UserLog_Model->getLogList($start, $limit, $where, $orderBy);

    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode([
        'code' => 0,
        'msg' => 'ok',
        'data' => $list,
        'count' => $total
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
}

if ($action == 'del') {
    LoginAuth::checkToken();
    $ids = Input::postStrVar('ids');
    if (empty($ids)) {
        Output::error('请选择要删除的日志');
    }
    $UserLog_Model->deleteLog($ids);
    Output::ok('删除成功');
}

if ($action == 'clean') {
    LoginAuth::checkToken();
    $days = Input::postIntVar('days', 180);
    if (!in_array($days, [30, 90, 180, 365])) $days = 180;
    $before = time() - $days * 86400;
    $db = Database::getInstance();
    $table = DB_PREFIX . 'user_log';
    $db->query("DELETE FROM `{$table}` WHERE create_time < {$before}");
    $affected = $db->affected_rows();
    output::ok(['count' => $affected]);
}

if ($action == 'types') {
    $types = $UserLog_Model->getLogTypes();
    echo json_encode(['code' => 0, 'data' => $types]);
    exit;
}
