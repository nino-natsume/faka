<?php
/**
 * user
 */

/**
 * @var string $action
 * @var object $CACHE
 */

require_once 'globals.php';

$User_Model = new User_Model();

if (!function_exists('getUserSimpleRow')) {
    function getUserSimpleRow($uid) {
        $uid = (int)$uid;
        if ($uid <= 0) {
            return [];
        }
        $db = Database::getInstance();
        return $db->once_fetch_array("SELECT uid, username, nickname, superior, state, role FROM " . DB_PREFIX . "user WHERE uid={$uid} LIMIT 1") ?: [];
    }
}

if (!function_exists('willCreateSuperiorLoop')) {
    function willCreateSuperiorLoop($uid, $superiorUid) {
        $uid = (int)$uid;
        $superiorUid = (int)$superiorUid;
        if ($uid <= 0 || $superiorUid <= 0) {
            return false;
        }
        $visited = [];
        $current = $superiorUid;
        $step = 0;
        while ($current > 0 && $step < 200) {
            if ($current === $uid || isset($visited[$current])) {
                return true;
            }
            $visited[$current] = true;
            $row = getUserSimpleRow($current);
            if (empty($row)) {
                return false;
            }
            $current = (int)($row['superior'] ?? 0);
            $step++;
        }
        return $current > 0;
    }
}

if($action == 'money_ajax'){
    $user_id = Input::postIntVar('user_id');
    $type = Input::postStrVar('type');
    $money = Input::postStrVar('money');

    if(empty($type)){
        output::error('请选择操作类型');
    }
    if(empty($money) || $money <= 0){
        output::error('请填写正确的金额');
    }

    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;

    $user = $db->once_fetch_array("select * from {$db_prefix}user where uid={$user_id}");

    $blance_insert = [
        'user_id' => $user_id,
        'money' => $money,
        'update_before' => $user['money'],
        'create_time' => time()
    ];



    if($type == 'inc'){
        $blance_insert['plus'] = 'y';
        $blance_insert['description'] = '客服充值';
        $sql = "update " . DB_PREFIX . "user set money = money + {$money} where uid={$user_id}";
    }else{
        $blance_insert['plus'] = 'n';
        $blance_insert['description'] = '客服扣除';
        $sql = "update " . DB_PREFIX . "user set money = money - {$money} where uid={$user_id}";
    }
    $db->query($sql);
    $db->add('balance_log', $blance_insert);

    if($type == 'inc'){
        User_Log_Model::log($user_id, 'admin_money', '后台客服充值 +' . $money, $money);
    } else {
        User_Log_Model::log($user_id, 'admin_money', '后台客服扣除 -' . $money, -$money);
    }
    output::ok();
}
if ($action == 'money') {
    $uid = isset($_GET['uid']) ? (int)$_GET['uid'] : '';
    $data = $User_Model->getOneUser($uid);
    $money = $data['money'];
    include View::getAdmView('open_head');
    require_once View::getAdmView('user_money');
    include View::getAdmView('open_foot');
    View::output();
}

if($action == 'credits_ajax'){
    $user_id = Input::postIntVar('user_id');
    $type = Input::postStrVar('type');
    $credits = Input::postIntVar('credits');

    if(empty($type)){
        output::error('请选择操作类型');
    }
    if($credits <= 0){
        output::error('请填写正确的积分');
    }

    $user = $User_Model->getOneUser($user_id);
    if (empty($user)) {
        output::error('用户不存在');
    }

    if($type == 'inc'){
        $User_Model->addCredits($user_id, $credits);
        User_Log_Model::log($user_id, 'admin_credits', '后台赠送积分 +' . $credits, $credits);
    }else{
        $User_Model->reduceCredits($user_id, $credits);
        User_Log_Model::log($user_id, 'admin_credits', '后台扣减积分 -' . $credits, -$credits);
    }
    output::ok();
}
if ($action == 'credits') {
    $uid = isset($_GET['uid']) ? (int)$_GET['uid'] : '';
    $data = $User_Model->getOneUser($uid);
    if (empty($data)) {
        emMsg('用户不存在');
    }
    $credits = (int)($data['credits'] ?? 0);
    include View::getAdmView('open_head');
    require_once View::getAdmView('user_credits');
    include View::getAdmView('open_foot');
    View::output();
}

if ($action == 'superior') {
    $uid = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;
    $data = $User_Model->getOneUser($uid);
    if (empty($data)) {
        emMsg('用户不存在');
    }
    $target_label = trim((string)($data['nickname'] ?: $data['username'] ?: ('UID ' . $uid)));
    $current_superior_id = (int)($data['superior'] ?? 0);
    $current_superior_value = $current_superior_id > 0 ? (string)$current_superior_id : '';
    include View::getAdmView('open_head');
    require_once View::getAdmView('user_superior');
    include View::getAdmView('open_foot');
    View::output();
}

if ($action == 'detail') {
    $uid = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;
    $data = $User_Model->getOneUser($uid);
    if (empty($data)) {
        emMsg('用户不存在');
    }

    $memberModel = new Member_Model();
    $level_id = (int)($data['level'] ?? 0);
    $memberInfo = $level_id > 0 ? $memberModel->getById($level_id) : [];
    $default_member_id = $memberModel->getDefaultLevelId();
    if (empty($memberInfo) && $default_member_id > 0) {
        $memberInfo = $memberModel->getById($default_member_id);
    }

    $superior_uid = (int)($data['superior'] ?? 0);
    $superior_row = $superior_uid > 0 ? getUserSimpleRow($superior_uid) : [];
    $superior_name = '';
    if (!empty($superior_row)) {
        $superior_name = trim((string)($superior_row['nickname'] ?: $superior_row['username'] ?: ('UID ' . $superior_uid)));
    }

    $db = Database::getInstance();
    $order_total_row = $db->once_fetch_array("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order WHERE user_id={$uid}");
    $paid_order_row = $db->once_fetch_array("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order WHERE user_id={$uid} AND status IN (1,2,-1)");
    $withdraw_total_row = $db->once_fetch_array("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "withdraw WHERE user_id={$uid}");
    $pending_withdraw_row = $db->once_fetch_array("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "withdraw WHERE user_id={$uid} AND status=0");
    $last_log_row = $db->once_fetch_array("SELECT create_time FROM " . DB_PREFIX . "user_log WHERE uid={$uid} ORDER BY id DESC LIMIT 1");

    $withdraw_method_raw = strtolower(trim((string)($data['withdraw_method'] ?? '')));
    $withdraw_method_map = [
        'alipay' => '支付宝',
        'wechat' => '微信',
        'weixin' => '微信',
        'qq' => 'QQ',
        'bank' => '银行卡'
    ];
    $withdraw_method_text = isset($withdraw_method_map[$withdraw_method_raw]) ? $withdraw_method_map[$withdraw_method_raw] : (trim((string)($data['withdraw_method'] ?? '')) ?: '未设置');

    $withdraw_receipt_image = trim((string)($data['withdraw_receipt_image'] ?? ''));
    $withdraw_receipt_url = '';
    if ($withdraw_receipt_image !== '') {
        if (filter_var($withdraw_receipt_image, FILTER_VALIDATE_URL)) {
            $withdraw_receipt_url = $withdraw_receipt_image;
        } elseif (strpos($withdraw_receipt_image, '../') === 0) {
            $withdraw_receipt_url = getFileUrl($withdraw_receipt_image);
        } else {
            $withdraw_receipt_url = DC_URL . ltrim($withdraw_receipt_image, '/');
        }
    }

    $avatar_url = trim((string)User::getAvatar($data['photo']));
    $default_avatar_url = DC_URL . 'admin/views/images/avatar.svg';
    if ($avatar_url === '') {
        $avatar_url = $default_avatar_url;
    }

    $user_detail = [
        'uid' => (int)$data['uid'],
        'avatar_url' => htmlspecialchars($avatar_url),
        'default_avatar_url' => htmlspecialchars($default_avatar_url),
        'username' => htmlspecialchars((string)$data['username']),
        'nickname' => htmlspecialchars((string)$data['nickname']),
        'role_name' => htmlspecialchars((string)User::getRoleName($data['role'] ?? '', (int)$data['uid'])),
        'state_text' => (int)($data['state'] ?? 0) === 0 ? '正常' : '已封禁',
        'state_badge_class' => (int)($data['state'] ?? 0) === 0 ? 'is-normal' : 'is-disabled',
        'station_id' => (int)($data['station_id'] ?? 0),
        'invite_code' => htmlspecialchars(trim((string)($data['invite_code'] ?? '')) ?: '未生成'),
        'ischeck_text' => (string)($data['ischeck'] ?? 'n') === 'y' ? '需要审核' : '无需审核',
        'level_name' => !empty($memberInfo['name']) ? htmlspecialchars((string)$memberInfo['name']) : '未设置会员等级',
        'superior_text' => $superior_uid > 0 ? htmlspecialchars($superior_uid . (!empty($superior_name) ? '（' . $superior_name . '）' : '')) : '没上级',
        'money' => number_format((float)($data['money'] ?? 0), 2, '.', ''),
        'expend' => number_format((float)($data['expend'] ?? 0), 2, '.', ''),
        'credits' => (int)($data['credits'] ?? 0),
        'email' => htmlspecialchars(trim((string)($data['email'] ?? '')) ?: '未绑定'),
        'tel' => htmlspecialchars(trim((string)($data['tel'] ?? '')) ?: '未绑定'),
        'wechat' => htmlspecialchars(trim((string)($data['wechat'] ?? '')) ?: '未设置'),
        'current_ip' => htmlspecialchars(trim((string)($data['ip'] ?? '')) ?: '未知'),
        'reg_ip' => htmlspecialchars(trim((string)($data['reg_ip'] ?? '')) ?: '未知'),
        'create_time' => !empty($data['create_time']) ? date('Y-m-d H:i:s', (int)$data['create_time']) : '未知',
        'update_time' => !empty($data['update_time']) ? date('Y-m-d H:i:s', (int)$data['update_time']) : '未知',
        'last_log_time' => !empty($last_log_row['create_time']) ? date('Y-m-d H:i:s', (int)$last_log_row['create_time']) : '暂无日志',
        'level_expire_time' => !empty($data['level_expire_time']) ? date('Y-m-d H:i:s', (int)$data['level_expire_time']) : '长期有效',
        'withdraw_method_text' => htmlspecialchars($withdraw_method_text),
        'withdraw_realname' => htmlspecialchars(trim((string)($data['withdraw_realname'] ?? '')) ?: '未设置'),
        'withdraw_account' => htmlspecialchars(trim((string)($data['withdraw_account'] ?? '')) ?: '未设置'),
        'withdraw_receipt_url' => htmlspecialchars($withdraw_receipt_url),
        'order_total' => (int)($order_total_row['total'] ?? 0),
        'paid_order_total' => (int)($paid_order_row['total'] ?? 0),
        'withdraw_total' => (int)($withdraw_total_row['total'] ?? 0),
        'pending_withdraw_total' => (int)($pending_withdraw_row['total'] ?? 0),
        'description' => htmlspecialchars(trim((string)($data['description'] ?? '')) ?: '暂无备注说明')
    ];

    include View::getAdmView('open_head');
    require_once View::getAdmView('user_detail');
    include View::getAdmView('open_foot');
    View::output();
}

if (empty($action)) {
    $br = '<a href="./">数据中心</a><a><cite>用户管理</cite></a>';
    $memberModel = new Member_Model();
    $member_list = [];
    $member = $memberModel->getMembersAll();
    foreach($member as $val){
        $member_list[] = [
            'name' => $val['name'],
            'id' => $val['id']
        ];
    }
    $default_member_id = $memberModel->getDefaultLevelId();
    if ($default_member_id <= 0 && !empty($member_list[0]['id'])) {
        $default_member_id = (int)$member_list[0]['id'];
    }


    include View::getAdmView('header');
    require_once View::getAdmView('user');
    include View::getAdmView('footer');
    View::output();
}

if($action == 'index'){
    new Member_Model();
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $page = Input::getIntVar('page', 1);
    $limit = Input::getIntVar('limit', 10);
    $keyword = Input::getStrVar('keyword');
    $member_id = Input::getIntVar('member_id', null);
    $reg_ip = trim(Input::getStrVar('reg_ip', ''));
    $start = ($page - 1) * $limit;
    $where = "";
    $sort1 = Input::getStrVar('field', 'uid');
    $sort2 = Input::getStrVar('order', 'desc');
    $order_by = "order by {$sort1} {$sort2}";
    $where .= " and u.delete_time IS NULL and (u.role IS NULL OR u.role='' OR u.role='writer' OR u.role='visitor')";

    if($member_id !== null && $member_id > 0){
        $where .= " and u.level={$member_id}";
    }
    if(!empty($keyword)){
        $kw = addslashes($keyword);
        $ip_cond = filter_var($keyword, FILTER_VALIDATE_IP) ? " or u.reg_ip='{$kw}'" : " or u.reg_ip like '%{$kw}%'";
        $where .= " and (u.uid='{$kw}' or u.username like '%{$kw}%' or u.tel like '%{$kw}%' or u.nickname like '%{$kw}%' or u.email like '%{$kw}%'{$ip_cond})";
    }
    if($reg_ip !== '' && filter_var($reg_ip, FILTER_VALIDATE_IP)){
        $where .= " and u.reg_ip='" . addslashes($reg_ip) . "'";
    }

    $sql = "SELECT u.*, m.name level_name FROM {$db_prefix}user u left join " . DB_PREFIX . "member m on u.level=m.id  where 1=1 $where {$order_by} limit $start, {$limit}";
    $res = $db->fetch_all($sql);
    $users = [];
    foreach($res as $row){
        $row['name'] = htmlspecialchars($row['nickname']);
        $row['login'] = htmlspecialchars($row['username']);
        $row['email'] = htmlspecialchars($row['email']);
        $row['description'] = htmlspecialchars($row['description']);
        $row['superior'] = (int)($row['superior'] ?? 0);
        $row['create_time'] = smartDate($row['create_time']);
        $row['update_time'] = smartDate($row['update_time']);
        $row['level_name'] = empty($row['level_name']) ? '未设置会员等级' : $row['level_name'];
        $row['avatar_url'] = User::getAvatar($row['photo']);
        $users[] = $row;
    }

    $sql = "SELECT count(u.uid) total FROM {$db_prefix}user u left join " . DB_PREFIX . "member m on u.level=m.id  where 1=1 $where";
    $res = $db->once_fetch_array($sql);
    $userCount = $res['total'];

    output::data($users, $res['total']);
}

if ($action == 'new') {
    $username = Input::postStrVar('username');
    $nickname = Input::postStrVar('nickname');
    $email = Input::postStrVar('email');
    $tel = Input::postStrVar('tel');
    $password = Input::postStrVar('password');
    $money = Input::postStrVar('money');
    $level = Input::postIntVar('level');
    $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

    $fail = function ($msg, $redirect) use ($isAjax) {
        if ($isAjax) {
            output::error($msg);
        }
        emDirect($redirect);
    };

    LoginAuth::checkToken();

    if (User::isAdmin()) {
        $ischeck = 'n';
    }

    if ($username == '') {
        $fail('请填写登录账号', './user.php?error_username=1');
    }
    if ($User_Model->isUserExist($username)) {
        $fail('登录账号已存在', './user.php?error_exist_username=1');
    }
    if ($password == '' || strlen($password) < 6) {
        $fail('密码长度不能少于6位', './user.php?error_pwd_len=1');
    }
    if (!empty($email) && !checkMail($email)) {
        $fail('邮箱格式不正确', './user.php?error_email=1');
    }
    if (!empty($email) && $User_Model->isMailExist($email)) {
        $fail('邮箱已存在', './user.php?error_exist_email=1');
    }
    if (!empty($tel) && $User_Model->isTelExist($tel)) {
        $fail('手机号码已存在', './user.php?error_exist_tel=1');
    }
    if ($money === '') {
        $money = '0';
    }
    if (!is_numeric($money) || floatval($money) < 0) {
        $fail('余额格式不正确', './user.php?error_money=1');
    }

    $PHPASS = new PasswordHash(8, true);
    $password = $PHPASS->HashPassword($password);
    $reg_ip = function_exists('getIp') ? getIp() : ($_SERVER['REMOTE_ADDR'] ?? '');
    $nickname = $nickname === '' ? $username : $nickname;
    $memberModel = new Member_Model();
    if ($level <= 0 || empty($memberModel->getById($level))) {
        $level = $memberModel->getDefaultLevelId();
    }
    $money = number_format((float)$money, 2, '.', '');

    $db = Database::getInstance();
    $insertUser = [
        'username' => $username,
        'nickname' => $nickname,
        'email' => $email,
        'tel' => $tel,
        'password' => $password,
        'money' => $money,
        'level' => $level,
        'reg_ip' => $reg_ip,
        'role' => User::ROLE_WRITER,
        'create_time' => time(),
        'update_time' => time()
    ];
    $db->add('user', $insertUser);
    $newUid = intval($db->insert_id());
    $newUser = $newUid > 0 ? ['uid' => $newUid] : null;
    if ($newUser) {
        // 自动生成专属邀请码
        $inviteCodeNew = User_Model::generateInviteCode($newUid);
        if ($inviteCodeNew !== '') {
            try {
                $db->update('user', ['invite_code' => $inviteCodeNew], ['uid' => $newUid]);
            } catch (Throwable $e) {}
        }
        User_Log_Model::log($newUser['uid'], 'admin_create', '后台创建用户（邮箱：' . $email . '）');
        if ((float)$money > 0) {
            $db->add('balance_log', [
                'user_id' => $newUser['uid'],
                'money' => $money,
                'update_before' => 0,
                'plus' => 'y',
                'description' => '后台创建用户初始余额',
                'create_time' => time()
            ]);
            User_Log_Model::log($newUser['uid'], 'admin_money', '后台创建用户初始余额 +' . $money, (float)$money);
        }
    }
    $CACHE->updateCache(array('sta', 'user'));
    if ($isAjax) {
        output::ok([
            'uid' => intval($newUser['uid'] ?? 0),
            'email' => $email,
            'username' => $username
        ]);
    }
    emDirect('./user.php?active_add=1');
}

if ($action == 'bind_superior') {
    LoginAuth::checkToken();
    $uid = Input::postIntVar('uid');
    $superiorUid = Input::postIntVar('superior_uid');

    if ($uid <= 0 || $superiorUid <= 0) {
        output::error('请输入有效的用户ID');
    }
    if ($uid === $superiorUid) {
        output::error('上级ID不能填写自己');
    }

    $targetUser = getUserSimpleRow($uid);
    if (empty($targetUser)) {
        output::error('目标用户不存在');
    }
    if (in_array($targetUser['role'] ?? '', [User::ROLE_ADMIN, User::ROLE_EDITOR], true)) {
        output::error('后台账户请在系统管理 -> 后台账户页面处理');
    }
    $oldSuperiorUid = (int)($targetUser['superior'] ?? 0);
    if ($oldSuperiorUid === $superiorUid) {
        output::error('上级ID未发生变化');
    }

    $superiorUser = getUserSimpleRow($superiorUid);
    if (empty($superiorUser)) {
        output::error('上级用户不存在');
    }
    if ((int)($superiorUser['state'] ?? 1) !== 0) {
        output::error('上级用户已被禁用，无法绑定');
    }
    if (in_array($superiorUser['role'] ?? '', [User::ROLE_ADMIN, User::ROLE_EDITOR], true)) {
        output::error('不能绑定后台账户为上级');
    }
    if (willCreateSuperiorLoop($uid, $superiorUid)) {
        output::error('该绑定会形成循环上下级关系');
    }

    $User_Model->updateUser(['superior' => $superiorUid], $uid);
    $CACHE->updateCache('user');
    User_Log_Model::log($uid, 'superior_bind_manual', '后台手动修改上级，原上级UID：' . $oldSuperiorUid . '，新上级UID：' . $superiorUid);
    output::ok([
        'uid' => $uid,
        'superior' => $superiorUid
    ]);
}

if ($action == 'edit') {

    $uid = isset($_GET['uid']) ? (int)$_GET['uid'] : '';

    $data = $User_Model->getOneUser($uid);

    $username = $data['username'];
    $nickname = $data['nickname'];
    $description = $data['description'];
//    $username = $data['username'];
    $email = $data['email'];
    $level = $data['level'];
    $tel = $data['tel'];

    $memberModel = new Member_Model();
    $members = $memberModel->getMembersAll();
    $default_member_id = $memberModel->getDefaultLevelId();
    if ($default_member_id <= 0 && !empty($members[0]['id'])) {
        $default_member_id = (int)$members[0]['id'];
    }
    $selected_level = (int)$level;
    $level_field_tip = '会员等级决定会员价、功能门槛与分销参数，不影响后台权限。';
    $has_selected_level = false;
    foreach ($members as $memberItem) {
        if ((int)$memberItem['id'] === $selected_level) {
            $has_selected_level = true;
            break;
        }
    }
    if ($selected_level <= 0 || !$has_selected_level) {
        $selected_level = $default_member_id;
        $level_field_tip = '该用户当前未分配有效会员等级，保存后将按所选会员等级重新归位。';
    }


    include View::getAdmView('open_head');
    require_once View::getAdmView('user_edit');
    include View::getAdmView('open_foot');
    View::output();
}

if ($action == 'edit_ajax') {
    $username = Input::postStrVar('username');
    $nickname = isset($_POST['nickname']) ? addslashes(trim($_POST['nickname'])) : '';
    $password = isset($_POST['password']) ? addslashes(trim($_POST['password'])) : '';
    $password2 = isset($_POST['password2']) ? addslashes(trim($_POST['password2'])) : '';
    $email = isset($_POST['email']) ? addslashes(trim($_POST['email'])) : '';
    $description = isset($_POST['description']) ? addslashes(trim($_POST['description'])) : '';
    $uid = isset($_POST['uid']) ? (int)$_POST['uid'] : '';
    $tel = Input::postStrVar('tel');

    LoginAuth::checkToken();

    //创始人账户不能被他人编辑
    if (!User::isFounder() && User::isFounderUid($uid)) {
        emDirect('./user.php?error_del_b=1');
    }
    if (empty($nickname)) {
        output::error('请填写昵称');
    }
    if (empty($username)) {
        output::error('请填写登录账号');
    }

    if ($User_Model->isMailExist($email, $uid)) {
        output::error('该邮箱已被使用');
    }
    if ($User_Model->isTelExist($tel, $uid)) {
        output::error('该手机被使用');
    }
    if ($User_Model->isUserExist($username, $uid)) {
        output::error('该登录账号已被使用');
    }
    if (strlen($password) > 0 && strlen($password) < 6) {
        output::error('密码不能小于6位数');
    }

    $memberModel = new Member_Model();
    $selectedLevel = Input::postIntVar('level');
    if ($selectedLevel <= 0 || empty($memberModel->getById($selectedLevel))) {
        $selectedLevel = $memberModel->getDefaultLevelId();
    }

    $userData = [
        'username'    => $username,
        'nickname'    => $nickname,
        'email'       => $email,
        'tel'       => $tel,
        'description' => $description,
        'level'       => $selectedLevel
    ];

    if (!empty($password)) {
        $PHPASS = new PasswordHash(8, true);
        $password = $PHPASS->HashPassword($password);
        $userData['password'] = $password;
    }

    $db = Database::getInstance();
    $oldLevel = $db->once_fetch_array("SELECT level FROM " . DB_PREFIX . "user WHERE uid={$uid}");
    $User_Model->updateUser($userData, $uid);
    $CACHE->updateCache('user');

    User_Log_Model::log($uid, 'admin_edit', '后台编辑用户信息（昵称:' . $nickname . '）');
    if ($oldLevel && intval($oldLevel['level']) !== intval($selectedLevel)) {
        User_Log_Model::log($uid, 'level_change', '等级变更：' . intval($oldLevel['level']) . ' → ' . intval($selectedLevel));
    }
    if (!empty($password)) {
        User_Log_Model::log($uid, 'password_change', '后台修改用户密码');
    }
    output::ok();
}

if ($action == 'del') {
    LoginAuth::checkToken();
    $ids = Input::postStrVar('ids');
    $ids = explode(',', $ids);
    $ids_arr = [];
    foreach($ids as $key => $val){
        $val = intval($val);
        if($val > 0 && !User::isFounderUid($val)){
            $ids_arr[] = $val;
        }
    }
    if(empty($ids_arr)){
        output::ok();
    }
    // 安全限制：用户删除仅支持单个操作，避免后台被盗后一次性清空用户表
    if(count($ids_arr) > 1){
        output::error('出于安全限制，用户删除仅支持单个操作');
    }
    $ids = implode(',', $ids_arr);

    $db = Database::getInstance();
    $adminRows = $db->fetch_all("SELECT uid FROM " . DB_PREFIX . "user WHERE uid IN ({$ids}) AND role IN ('admin','editor')");
    if (!empty($adminRows)) {
        output::error('后台账户请在系统管理 -> 后台账户页面处理');
    }

    $sql = "UPDATE " . DB_PREFIX . "user SET delete_time=" . time() . " WHERE uid IN ({$ids}) AND delete_time IS NULL";
    $db->query($sql);

    foreach($ids_arr as $del_uid){
        User_Log_Model::log(intval($del_uid), 'admin_delete', '后台删除用户');
    }
    $CACHE->updateCache(array('sta', 'user'));
    output::ok();


}



if ($action == 'forbid') {
    LoginAuth::checkToken();
    $uid = Input::postStrVar('ids');
    if (UID == $uid) {
        output::ok();
    }
    $userInfo = $User_Model->getOneUser((int)$uid);
    if (!empty($userInfo) && in_array($userInfo['role'] ?? '', [User::ROLE_ADMIN, User::ROLE_EDITOR], true)) {
        output::error('后台账户请在系统管理 -> 后台账户页面处理');
    }
    //创始人账户不能被禁用
    if (User::isFounderUid($uid)) {
        output::ok();
    }
    $User_Model->forbidUser($uid);
    User_Log_Model::log(intval($uid), 'user_forbid', '后台禁用账户');
    output::ok();
}

if ($action == 'unforbid') {
    LoginAuth::checkToken();
    $uid = Input::postStrVar('ids');
    $userInfo = $User_Model->getOneUser((int)$uid);
    if (!empty($userInfo) && in_array($userInfo['role'] ?? '', [User::ROLE_ADMIN, User::ROLE_EDITOR], true)) {
        output::error('后台账户请在系统管理 -> 后台账户页面处理');
    }
    $User_Model->unforbidUser($uid);
    User_Log_Model::log(intval($uid), 'user_unforbid', '后台解禁账户');
    output::ok();
}

