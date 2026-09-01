<?php
require_once 'globals.php';

$User_Model = new User_Model();
$db = Database::getInstance();
$db_prefix = DB_PREFIX;

if (empty($action)) {
    $br = '<a href="./">数据中心</a><a><cite>用户回收站</cite></a>';
    $memberModel = new Member_Model();
    $member_list = [];
    $member = $memberModel->getMembersAll();
    foreach ($member as $val) {
        $member_list[] = [
            'name' => $val['name'],
            'id' => $val['id']
        ];
    }
    include View::getAdmView('header');
    require_once View::getAdmView('user_recycle');
    include View::getAdmView('footer');
    View::output();
}

if ($action == 'list') {
    $page = Input::getIntVar('page', 1);
    $limit = Input::getIntVar('limit', 10);
    $keyword = Input::getStrVar('keyword');
    $member_id = Input::getIntVar('member_id', null);
    $start = ($page - 1) * $limit;
    $where = " and u.delete_time IS NOT NULL and (u.role IS NULL OR u.role='' OR u.role='writer' OR u.role='visitor')";
    $sort1 = Input::getStrVar('field', 'delete_time');
    $sort2 = Input::getStrVar('order', 'desc');
    $allowSort = ['uid', 'money', 'credits', 'create_time', 'delete_time'];
    if (!in_array($sort1, $allowSort, true)) {
        $sort1 = 'delete_time';
    }
    $sort2 = strtolower($sort2) === 'asc' ? 'asc' : 'desc';
    $order_by = "order by u.{$sort1} {$sort2}";

    if ($member_id !== null && $member_id > 0) {
        $where .= " and u.level={$member_id}";
    }
    if (!empty($keyword)) {
        $kw = addslashes($keyword);
        $ip_cond = filter_var($keyword, FILTER_VALIDATE_IP) ? " or u.reg_ip='{$kw}'" : " or u.reg_ip like '%{$kw}%'";
        $where .= " and (u.uid='{$kw}' or u.username like '%{$kw}%' or u.tel like '%{$kw}%' or u.nickname like '%{$kw}%' or u.email like '%{$kw}%'{$ip_cond})";
    }

    $sql = "SELECT u.*, m.name level_name FROM {$db_prefix}user u left join {$db_prefix}member m on u.level=m.id where 1=1 {$where} {$order_by} limit {$start}, {$limit}";
    $res = $db->fetch_all($sql);
    $users = [];
    foreach ($res as $row) {
        $row['name'] = htmlspecialchars($row['nickname']);
        $row['login'] = htmlspecialchars($row['username']);
        $row['email'] = htmlspecialchars($row['email']);
        $row['tel'] = htmlspecialchars((string)($row['tel'] ?? ''));
        $row['reg_ip'] = htmlspecialchars((string)($row['reg_ip'] ?? ''));
        $row['superior'] = (int)($row['superior'] ?? 0);
        $row['create_time_text'] = !empty($row['create_time']) ? smartDate($row['create_time']) : '-';
        $row['delete_time_text'] = !empty($row['delete_time']) ? smartDate($row['delete_time']) : '-';
        $row['level_name'] = empty($row['level_name']) ? '未设置会员等级' : $row['level_name'];
        $row['avatar_url'] = User::getAvatar($row['photo']);
        $users[] = $row;
    }

    $countRow = $db->once_fetch_array("SELECT count(u.uid) total FROM {$db_prefix}user u left join {$db_prefix}member m on u.level=m.id where 1=1 {$where}");
    output::data($users, (int)($countRow['total'] ?? 0));
}

if ($action == 'restore') {
    LoginAuth::checkToken();
    $ids = Input::postStrVar('ids');
    if (empty($ids)) {
        output::error('请选择要恢复的用户');
    }
    $ids = array_map('intval', explode(',', $ids));
    $count = 0;
    foreach ($ids as $uid) {
        if ($uid <= 0) continue;
        $db->query("UPDATE {$db_prefix}user SET delete_time = NULL WHERE uid = {$uid} AND delete_time IS NOT NULL AND (role IS NULL OR role='' OR role='writer' OR role='visitor')");
        if ($db->affected_rows()) {
            $count++;
            User_Log_Model::log($uid, 'admin_restore', '后台恢复用户');
        }
    }
    $CACHE->updateCache(['sta', 'user']);
    if ($count > 0) {
        output::ok(['count' => $count, 'msg' => "已恢复 {$count} 个用户"]);
    }
    output::error('没有找到可恢复的用户');
}

if ($action == 'permanent_delete') {
    LoginAuth::checkToken();
    $ids = Input::postStrVar('ids');
    if (empty($ids)) {
        output::error('请选择要删除的用户');
    }
    $ids = array_map('intval', explode(',', $ids));
    $count = 0;
    foreach ($ids as $uid) {
        if ($uid <= 0 || User::isFounderUid($uid)) continue;
        $row = $db->once_fetch_array("SELECT uid FROM {$db_prefix}user WHERE uid={$uid} AND delete_time IS NOT NULL AND (role IS NULL OR role='' OR role='writer' OR role='visitor')");
        if (empty($row)) continue;
        $db->query("DELETE FROM {$db_prefix}user WHERE uid={$uid} AND delete_time IS NOT NULL");
        if ($db->affected_rows()) {
            $count++;
            User_Log_Model::log($uid, 'admin_permanent_delete', '后台彻底删除用户');
        }
    }
    $CACHE->updateCache(['sta', 'user']);
    if ($count > 0) {
        output::ok(['count' => $count, 'msg' => "已彻底删除 {$count} 个用户"]);
    }
    output::error('没有找到可删除的用户');
}

if ($action == 'empty_all') {
    LoginAuth::checkToken();
    $rows = $db->fetch_all("SELECT uid FROM {$db_prefix}user WHERE delete_time IS NOT NULL AND (role IS NULL OR role='' OR role='writer' OR role='visitor')");
    $count = 0;
    foreach ($rows as $row) {
        $uid = (int)$row['uid'];
        if ($uid <= 0 || User::isFounderUid($uid)) continue;
        $db->query("DELETE FROM {$db_prefix}user WHERE uid={$uid} AND delete_time IS NOT NULL");
        if ($db->affected_rows()) {
            $count++;
            User_Log_Model::log($uid, 'admin_permanent_delete', '后台清空回收站彻底删除用户');
        }
    }
    $CACHE->updateCache(['sta', 'user']);
    output::ok(['count' => $count, 'msg' => "已清空用户回收站，共删除 {$count} 个用户"]);
}
