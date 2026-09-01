<?php
/**

 */

/**
 * @var string $action
 * @var object $CACHE
 */

require_once '../init.php';

$sta_cache = $CACHE->readCache('sta');
$user_cache = $CACHE->readCache('user');
$action = Input::getStrVar('action');

require_once 'account_api_key.php';

$User_Model = new User_Model();

if (!function_exists('ensureUserWechatColumn')) {
    function ensureUserWechatColumn($db) {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;
        $table = DB_PREFIX . 'user';
        $col = $db->once_fetch_array("SHOW COLUMNS FROM `{$table}` LIKE 'wechat'");
        if (empty($col)) {
            $db->query("ALTER TABLE `{$table}` ADD COLUMN `wechat` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信号' AFTER `tel`", true);
        }
    }
}

if ($action == 'signin') {

    // 仅当用户名/邮箱/手机三种登录方式全部关闭时，才视为「系统已关闭登录」；
    // 只要任意一种登录方式开启，前端登录页仍可访问。
    if (Option::get('login_username_switch') !== 'y'
        && Option::get('login_email_switch') !== 'y'
        && Option::get('login_tel_switch') !== 'y') {
        emMsg('系统已关闭登录！');
    }

    $login_redirect_url = function_exists('dcGetSafeRedirectUrl') ? dcGetSafeRedirectUrl(Input::getStrVar('redirect'), '') : '';
    if (!isset($_SESSION)) {
        session_start();
    }
    $_SESSION['user_login_redirect'] = $login_redirect_url;
    if (ISLOGIN === true) {
        emDirect($login_redirect_url ?: './');
    }

    $login_code = Option::get('login_code') === 'y';
    $is_signup = Option::get('is_signup') === 'y';

    $default_type = Option::get('login_username_switch') == 'y' ? 'username' : (Option::get('login_tel_switch') == 'y' ? 'tel' : (Option::get('login_email_switch') == 'y' ? 'email' : 'username'));

    $page_title = '登录';
    if (isMobile() && file_exists(View::getUserView('signin_mobile'))) {
        require_once View::getUserView('signin_mobile');
    } else {
        require_once View::getUserView('user_head');
        require_once View::getUserView('signin');
    }
    View::output();
}

// 发送手机登录短信验证码
if ($action == 'send_login_sms_code') {
    // 三种登录方式全关才视为管理员关闭了登录功能
    if (Option::get('login_username_switch') !== 'y'
        && Option::get('login_email_switch') !== 'y'
        && Option::get('login_tel_switch') !== 'y') {
        Output::error('管理员已关闭登录功能');
    }
    if (Option::get('login_tel_switch') !== 'y') {
        Output::error('手机登录未开启');
    }

    if (!isset($_SESSION)) session_start();

    // 安全验证：极验启用时强制极验，否则图形验证码
    $geetest_active = function_exists('geetest_is_active') && geetest_is_active();
    if ($geetest_active) {
        geetest_server_validate();
    } else {
        $imgcode = strtoupper(trim(Input::postStrVar('imgcode')));
        if (empty($imgcode) || $imgcode !== ($_SESSION['code'] ?? '')) {
            Output::error('图形验证码错误');
        }
        $_SESSION['code'] = null;
    }

    // 每日发送次数限制
    $dailyLimit = (int)(Option::get('sms_login_daily_limit') ?: 10);
    if ($dailyLimit > 0) {
        $todayKey = 'sms_login_count_' . date('Ymd');
        $todayCount = $_SESSION[$todayKey] ?? 0;
        if ($todayCount >= $dailyLimit) {
            Output::error('今日短信登录验证码发送已达上限（' . $dailyLimit . '次）');
        }
    }

    // 频率限制 60s
    $lastTime = $_SESSION['login_sms_time'] ?? 0;
    if (time() - $lastTime < 60) {
        Output::error('请' . (60 - (time() - $lastTime)) . '秒后再试');
    }

    $tel = Input::postStrVar('tel');
    if (empty($tel) || !preg_match('/^1[3-9]\d{9}$/', $tel)) {
        Output::error('请输入正确的手机号');
    }

    // 生成验证码
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // 通过授权服务器发送短信
    try {
        $storeModel = new Store_Model();
        $res = $storeModel->sendSmsCode($tel, $code);
    } catch (\Throwable $e) {
        Output::error('短信服务内部错误：' . $e->getMessage());
    }
    if (!$res || $res['code'] != 200) {
        Output::error($res['msg'] ?? '短信发送失败');
    }

    $_SESSION['login_sms_code'] = $code;
    $_SESSION['login_sms_tel'] = $tel;
    $_SESSION['login_sms_time'] = time();
    // 累加每日计数
    $todayKey = 'sms_login_count_' . date('Ymd');
    $_SESSION[$todayKey] = ($_SESSION[$todayKey] ?? 0) + 1;

    Output::ok('验证码已发送');
}

// 发送注册手机短信验证码
if ($action == 'send_register_sms_code') {
    if (Option::get('register_switch') !== 'y') {
        Output::error('管理员已关闭注册功能');
    }
    if (Option::get('register_tel_switch') !== 'y') {
        Output::error('手机注册未开启');
    }

    if (!isset($_SESSION)) session_start();

    // 安全验证：极验启用时强制极验，否则图形验证码
    $geetest_active = function_exists('geetest_is_active') && geetest_is_active();
    if ($geetest_active) {
        geetest_server_validate();
    } else {
        $imgcode = strtoupper(trim(Input::postStrVar('imgcode')));
        if (empty($imgcode) || $imgcode !== ($_SESSION['code'] ?? '')) {
            Output::error('图形验证码错误');
        }
        $_SESSION['code'] = null;
    }

    // 每日发送次数限制
    $dailyLimit = (int)(Option::get('sms_login_daily_limit') ?: 10);
    if ($dailyLimit > 0) {
        $todayKey = 'sms_register_count_' . date('Ymd');
        $todayCount = $_SESSION[$todayKey] ?? 0;
        if ($todayCount >= $dailyLimit) {
            Output::error('今日短信验证码发送已达上限（' . $dailyLimit . '次）');
        }
    }

    // 频率限制 60s
    $lastTime = $_SESSION['register_sms_time'] ?? 0;
    if (time() - $lastTime < 60) {
        Output::error('请' . (60 - (time() - $lastTime)) . '秒后再试');
    }

    $tel = Input::postStrVar('tel');
    if (empty($tel) || !preg_match('/^1[3-9]\d{9}$/', $tel)) {
        Output::error('请输入正确的手机号');
    }

    // 校验手机号是否已注册
    if ($User_Model->isTelExist($tel)) {
        Output::error('该手机号已被注册');
    }

    // 生成验证码
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // 通过授权服务器发送短信
    $storeModel = new Store_Model();
    $res = $storeModel->sendSmsCode($tel, $code);
    if (!$res || $res['code'] != 200) {
        Output::error($res['msg'] ?? '短信发送失败');
    }

    $_SESSION['register_sms_code'] = $code;
    $_SESSION['register_sms_tel'] = $tel;
    $_SESSION['register_sms_time'] = time();
    // 累加每日计数
    $todayKey = 'sms_register_count_' . date('Ymd');
    $_SESSION[$todayKey] = ($_SESSION[$todayKey] ?? 0) + 1;

    Output::ok('验证码已发送');
}

// 发送注册邮箱验证码
if ($action == 'send_register_email_code') {
    if (Option::get('register_switch') !== 'y') {
        Output::error('管理员已关闭注册功能');
    }
    if (Option::get('register_email_switch') !== 'y') {
        Output::error('邮箱注册未开启');
    }

    if (!isset($_SESSION)) session_start();

    // 安全验证：极验启用时强制极验，否则图形验证码
    $geetest_active = function_exists('geetest_is_active') && geetest_is_active();
    if ($geetest_active) {
        geetest_server_validate();
    } else {
        $imgcode = strtoupper(trim(Input::postStrVar('imgcode')));
        if (empty($imgcode) || $imgcode !== ($_SESSION['code'] ?? '')) {
            Output::error('图形验证码错误');
        }
        $_SESSION['code'] = null;
    }

    // 频率限制 60s
    $lastTime = $_SESSION['register_email_time'] ?? 0;
    if (time() - $lastTime < 60) {
        Output::error('请' . (60 - (time() - $lastTime)) . '秒后再试');
    }

    $email = Input::postStrVar('mail');
    if (empty($email) || !checkMail($email)) {
        Output::error('请输入正确的邮箱地址');
    }

    // 校验邮箱是否已注册
    if ($User_Model->isMailExist($email)) {
        Output::error('该邮箱已被注册');
    }

    // 发送验证码邮件
    $ret = Notice::sendVerifyMailCode($email);
    if (!$ret) {
        Output::error('邮件发送失败，请检查邮箱地址或联系管理员');
    }

    $_SESSION['register_email_time'] = time();

    Output::ok('验证码已发送');
}

// 发送邮箱登录验证码
if ($action == 'send_login_email_code') {
    // 三种登录方式全关才视为管理员关闭了登录功能
    if (Option::get('login_username_switch') !== 'y'
        && Option::get('login_email_switch') !== 'y'
        && Option::get('login_tel_switch') !== 'y') {
        Output::error('管理员已关闭登录功能');
    }
    if (Option::get('login_email_switch') !== 'y') {
        Output::error('邮箱登录未开启');
    }

    if (!isset($_SESSION)) session_start();

    // 安全验证：极验启用时强制极验，否则图形验证码
    $geetest_active = function_exists('geetest_is_active') && geetest_is_active();
    if ($geetest_active) {
        geetest_server_validate();
    } else {
        $imgcode = strtoupper(trim(Input::postStrVar('imgcode')));
        if (empty($imgcode) || $imgcode !== ($_SESSION['code'] ?? '')) {
            Output::error('图形验证码错误');
        }
        $_SESSION['code'] = null;
    }

    // 频率限制 60s
    $lastTime = $_SESSION['login_email_time'] ?? 0;
    if (time() - $lastTime < 60) {
        Output::error('请' . (60 - (time() - $lastTime)) . '秒后再试');
    }

    $email = Input::postStrVar('email');
    if (empty($email) || !checkMail($email)) {
        Output::error('请输入正确的邮箱地址');
    }

    // 发送验证码邮件
    $ret = Notice::sendVerifyMailCode($email);
    if (!$ret) {
        Output::error('邮件发送失败，请检查邮箱地址或联系管理员');
    }

    // sendVerifyMailCode 已将验证码写入 $_SESSION['mail_code'] / $_SESSION['mail']
    $_SESSION['login_email_time'] = time();

    Output::ok('验证码已发送');
}

if ($action == 'dosignin') {

//    loginAuth::checkLogged();

    // 三种登录方式全关才视为管理员关闭了登录功能。
    // 后续根据 $type 分支会再检查对应的细分开关 (login_username_switch/login_email_switch/login_tel_switch)。
    if (Option::get('login_username_switch') !== 'y'
        && Option::get('login_email_switch') !== 'y'
        && Option::get('login_tel_switch') !== 'y') {
        output::error('管理员已关闭登录功能');
    }


//    Output::error('验证错误0');
    doAction('user_login_submit');


    $tel = Input::postStrVar('tel');
    $email = Input::postStrVar('email');
    $username = Input::postStrVar('username');
    $password = Input::postStrVar('password');
    $persist = Input::postIntVar('persist');
    $type = Input::postStrVar('type');

    // 手机短信验证码登录
    if ($type == 'tel') {
        if (!isset($_SESSION)) session_start();

        $smsCode = trim(Input::postStrVar('sms_code'));
        if (empty($smsCode)) {
            Output::error('请输入短信验证码');
        }

        $sCode = $_SESSION['login_sms_code'] ?? '';
        $sTel  = $_SESSION['login_sms_tel'] ?? '';
        $sTime = $_SESSION['login_sms_time'] ?? 0;

        if (empty($sCode) || $sCode !== $smsCode) {
            Output::error('短信验证码错误');
        }
        if ($sTel !== $tel) {
            Output::error('手机号与验证码不匹配');
        }
        if (time() - $sTime > 600) {
            Output::error('验证码已过期，请重新获取');
        }

        // 通过手机号查找用户
        $userRow = Database::getInstance()->once_fetch_array(
            "SELECT uid, state FROM " . DB_PREFIX . "user WHERE tel='" . addslashes($tel) . "' LIMIT 1"
        );

        if ($userRow && !empty($userRow['uid'])) {
            // 已有账号
            if ((int)$userRow['state'] !== 0) {
                Output::error('账号已被禁用');
            }
            $uid = (int)$userRow['uid'];
        } else {
            // 手机号未注册，自动创建账号
            $PHPASS = new PasswordHash(8, true);
            $autoPasswd = $PHPASS->HashPassword(getRandStr(16));
            $User_Model->addUser('', $tel, $autoPasswd, getIp(), User::ROLE_WRITER, 'tel');

            $CACHE = Cache::getInstance();
            $CACHE->updateCache(['sta', 'user']);

            $newRow = Database::getInstance()->once_fetch_array(
                "SELECT uid FROM " . DB_PREFIX . "user WHERE tel='" . addslashes($tel) . "' ORDER BY uid DESC LIMIT 1"
            );
            if (!$newRow || empty($newRow['uid'])) {
                Output::error('自动注册失败，请稍后重试');
            }
            $uid = (int)$newRow['uid'];

            // 生成邀请码 + 分配默认等级
            $updates = [];
            $inviteCodeNew = User_Model::generateInviteCode($uid);
            if ($inviteCodeNew !== '') {
                $updates['invite_code'] = $inviteCodeNew;
            }
            $memberModel = new Member_Model();
            $defaultGrade = $memberModel->getDefaultLevelId();
            if ($defaultGrade > 0) {
                $updates['level'] = $defaultGrade;
                if (class_exists('Level_Service')) {
                    $updates['level_expire_time'] = Level_Service::calculateDefaultAssignExpireTime(0, $defaultGrade);
                }
            }
            // 绑定邀请码上级
            if (!empty($_COOKIE['dc_invite_code'])) {
                $_invCode = trim($_COOKIE['dc_invite_code']);
                $_supRow = Database::getInstance()->once_fetch_array(
                    "SELECT uid FROM " . DB_PREFIX . "user WHERE invite_code='" . addslashes($_invCode) . "' AND state=0 AND delete_time IS NULL LIMIT 1"
                );
                if ($_supRow && (int)$_supRow['uid'] !== $uid) {
                    $updates['superior'] = (int)$_supRow['uid'];
                }
                setcookie('dc_invite_code', '', time() - 3600, '/');
            }
            if (!empty($updates)) {
                try {
                    Database::getInstance()->update('user', $updates, ['uid' => $uid]);
                } catch (Throwable $e) {}
            }

            User_Log_Model::log($uid, 'register', '手机验证码自动注册（' . $tel . '）', 0, getIp());
        }
        $User_Model->updateUser(['ip' => getIp()], $uid);
        LoginAuth::setAuthCookie($tel, $persist);

        // 清除验证码
        unset($_SESSION['login_sms_code'], $_SESSION['login_sms_tel'], $_SESSION['login_sms_time']);

        doAction('user_login_success', $uid, [
            'type' => 'tel_sms',
            'ip' => getIp()
        ]);

        User_Log_Model::log($uid, 'login', '用户登录（短信验证码）');

        Output::ok();
    }

    // 邮箱验证码登录
    if ($type == 'email') {
        if (!isset($_SESSION)) session_start();

        $emailCode = trim(Input::postStrVar('email_code'));
        if (empty($emailCode)) {
            Output::error('请输入邮箱验证码');
        }

        $sCode  = $_SESSION['mail_code'] ?? '';
        $sMail  = $_SESSION['mail'] ?? '';
        $sTime  = $_SESSION['login_email_time'] ?? 0;

        if (empty($sCode) || strtoupper($sCode) !== strtoupper($emailCode)) {
            Output::error('邮箱验证码错误');
        }
        if ($sMail !== $email) {
            Output::error('邮箱与验证码不匹配');
        }
        if (time() - $sTime > 600) {
            Output::error('验证码已过期，请重新获取');
        }

        // 通过邮箱查找用户
        $userRow = Database::getInstance()->once_fetch_array(
            "SELECT uid, state FROM " . DB_PREFIX . "user WHERE email='" . addslashes($email) . "' LIMIT 1"
        );

        if ($userRow && !empty($userRow['uid'])) {
            if ((int)$userRow['state'] !== 0) {
                Output::error('账号已被禁用');
            }
            $uid = (int)$userRow['uid'];
        } else {
            // 邮箱未注册，自动创建账号
            $PHPASS = new PasswordHash(8, true);
            $autoPasswd = $PHPASS->HashPassword(getRandStr(16));
            $User_Model->addUser('', $email, $autoPasswd, getIp(), User::ROLE_WRITER, 'email');

            $CACHE = Cache::getInstance();
            $CACHE->updateCache(['sta', 'user']);

            $newRow = Database::getInstance()->once_fetch_array(
                "SELECT uid FROM " . DB_PREFIX . "user WHERE email='" . addslashes($email) . "' ORDER BY uid DESC LIMIT 1"
            );
            if (!$newRow || empty($newRow['uid'])) {
                Output::error('自动注册失败，请稍后重试');
            }
            $uid = (int)$newRow['uid'];

            // 生成邀请码 + 分配默认等级
            $updates = [];
            $inviteCodeNew = User_Model::generateInviteCode($uid);
            if ($inviteCodeNew !== '') {
                $updates['invite_code'] = $inviteCodeNew;
            }
            $memberModel = new Member_Model();
            $defaultGrade = $memberModel->getDefaultLevelId();
            if ($defaultGrade > 0) {
                $updates['level'] = $defaultGrade;
                if (class_exists('Level_Service')) {
                    $updates['level_expire_time'] = Level_Service::calculateDefaultAssignExpireTime(0, $defaultGrade);
                }
            }
            // 绑定邀请码上级
            if (!empty($_COOKIE['dc_invite_code'])) {
                $_invCode = trim($_COOKIE['dc_invite_code']);
                $_supRow = Database::getInstance()->once_fetch_array(
                    "SELECT uid FROM " . DB_PREFIX . "user WHERE invite_code='" . addslashes($_invCode) . "' AND state=0 AND delete_time IS NULL LIMIT 1"
                );
                if ($_supRow && (int)$_supRow['uid'] !== $uid) {
                    $updates['superior'] = (int)$_supRow['uid'];
                }
                setcookie('dc_invite_code', '', time() - 3600, '/');
            }
            if (!empty($updates)) {
                try {
                    Database::getInstance()->update('user', $updates, ['uid' => $uid]);
                } catch (Throwable $e) {}
            }

            User_Log_Model::log($uid, 'register', '邮箱验证码自动注册（' . $email . '）', 0, getIp());
        }

        $User_Model->updateUser(['ip' => getIp()], $uid);
        LoginAuth::setAuthCookie($email, $persist);

        // 清除验证码
        unset($_SESSION['mail_code'], $_SESSION['mail'], $_SESSION['login_email_time']);

        doAction('user_login_success', $uid, [
            'type' => 'email_code',
            'ip' => getIp()
        ]);

        User_Log_Model::log($uid, 'login', '用户登录（邮箱验证码）');

        Output::ok();
    }

    // 用户名密码登录
    // 极验已激活时跳过图形验证码（极验优先级 > 图形验证码）
    $geetest_active = function_exists('geetest_is_active') && geetest_is_active();
    if (!$geetest_active) {
        $login_code = Option::get('login_code') === 'y' && isset($_POST['login_code']) ? addslashes(strtoupper(trim($_POST['login_code']))) : '';
        if (!User::checkLoginCode($login_code)) {
            Output::error('图形验证码错误');
        }
    }

    if($type == 'username'){
        $uid = LoginAuth::checkUser($username, $password, $type);
    }
    if(empty($uid)){
        Output::error('账号或密码错误');
    }
    switch ($uid) {
        case $uid > 0:
            $User_Model->updateUser(['ip' => getIp()], $uid);
            LoginAuth::setAuthCookie($username, $persist);
            
            doAction('user_login_success', $uid, [
                'type' => $type,
                'ip' => getIp()
            ]);

            User_Log_Model::log($uid, 'login', '用户登录（' . $type . '）');

            Output::ok();
            break;
        case LoginAuth::LOGIN_ERROR_USER:
        case LoginAuth::LOGIN_ERROR_PASSWD:
            Output::error('用户名或密码错误');
            break;
    }
}

if ($action == 'signup') {
    // 安全保险：cookie 有邀请码但 URL 没带时，重定向把邀请码加到 URL 上
    if (empty($_GET['invite']) && empty($_GET['invite_code']) && !empty($_COOKIE['dc_invite_code'])) {
        $__inv = preg_replace('/[^A-Za-z0-9]/', '', $_COOKIE['dc_invite_code']);
        if ($__inv !== '') {
            header('Location: ./account.php?action=signup&invite=' . $__inv);
            exit;
        }
    }

    loginAuth::checkLogged();
    $login_code = Option::get('login_code') === 'y';
    $email_code = Option::get('email_code') === 'y';
    $error_msg = '';

    if (Option::get('register_switch') !== 'y') {
        emMsg('系统已关闭注册！');
    }

    // 邀请码处理：从 URL 参数或 cookie 读取，传给注册表单
    $inviteCode = Input::getStrVar('invite', '');
    if ($inviteCode === '') $inviteCode = Input::getStrVar('invite_code', '');
    if ($inviteCode === '' && isset($_COOKIE['dc_invite_code'])) {
        $inviteCode = trim($_COOKIE['dc_invite_code']);
    }
    $inviteCode = preg_replace('/[^A-Za-z0-9]/', '', $inviteCode);
    if ($inviteCode !== '') {
        // 写 cookie 持续 30 天
        setcookie('dc_invite_code', $inviteCode, time() + 30 * 86400, '/');
    }

    // 强制邀请注册：无邀请码时提示
    if (class_exists('Level_Service') && (int)Level_Service::getSetting(Level_Service::OPT_INVITE_ONLY, 0) === 1 && $inviteCode === '') {
        emMsg('本站已开启邀请注册，请通过老用户分享的邀请链接进行注册。');
    }

    $default_type = Option::get('register_username_switch') == 'y' ? 'username' : (Option::get('register_tel_switch') == 'y' ? 'tel' : (Option::get('register_email_switch') == 'y' ? 'email' : 'username'));
    $register_bind_invite = Option::get('register_bind_invite') === 'y';

    $page_title = '注册账号';
    if (isMobile() && file_exists(View::getUserView('signup_mobile'))) {
        require_once View::getUserView('signup_mobile');
    } else {
        include View::getUserView('user_head');
        require_once View::getUserView('signup');
    }
    View::output();
}

if ($action == 'dosignup') {
    loginAuth::checkLogged();

    if (Option::get('register_switch') !== 'y') {
        output::ok('管理员已关闭注册功能');
    }

    // 强制邀请注册检查（提交时再次校验）
    $_inviteOnly = class_exists('Level_Service') && (int)Level_Service::getSetting(Level_Service::OPT_INVITE_ONLY, 0) === 1;
    if ($_inviteOnly) {
        $_checkCode = Input::postStrVar('invite_code', '');
        if ($_checkCode === '' && !empty($_COOKIE['dc_invite_code'])) {
            $_checkCode = trim($_COOKIE['dc_invite_code']);
        }
        if ($_checkCode === '') {
            Output::error('本站已开启邀请注册，请通过邀请链接注册');
        }
    }

    // 强制填写邀请码校验
    if (Option::get('register_bind_invite') === 'y') {
        $_invCode = Input::postStrVar('invite_code', '');
        if ($_invCode === '' && !empty($_COOKIE['dc_invite_code'])) {
            $_invCode = trim($_COOKIE['dc_invite_code']);
        }
        if ($_invCode === '') {
            Output::error('请填写邀请码');
        }
        // 校验邀请码是否对应有效用户
        $_invDb = Database::getInstance();
        $_invRow = $_invDb->once_fetch_array("SELECT uid FROM " . DB_PREFIX . "user WHERE invite_code='" . addslashes($_invCode) . "' AND state=0 LIMIT 1");
        if (!$_invRow) {
            Output::error('邀请码无效，请检查后重新输入');
        }
    }

    doAction('user_register_submit');

    $mail = Input::postStrVar('email');
    $tel = Input::postStrVar('tel');
    $reg_username = Input::postStrVar('username');
    $passwd = Input::postStrVar('password');
    $repasswd = Input::postStrVar('repassword');
    $login_code = strtoupper(Input::postStrVar('login_code'));
    $mail_code = Input::postStrVar('mail_code');
    $reg_ip = getIp();

    // 自动检测注册类型（优先级：username > tel > email）
    $type = Input::postStrVar('type');
    if (empty($type)) {
        if (!empty($reg_username) && Option::get('register_username_switch') == 'y') {
            $type = 'username';
        } elseif (!empty($tel) && Option::get('register_tel_switch') == 'y') {
            $type = 'tel';
        } elseif (!empty($mail) && Option::get('register_email_switch') == 'y') {
            $type = 'email';
        } else {
            Output::error('请填写注册信息');
        }
    }

    // ============================================================
    // 上级绑定（优先级：邀请码 > 分店域名 > 无）
    // ============================================================
    $inviteCode = Input::postStrVar('invite_code', '');
    if ($inviteCode === '' && !empty($_COOKIE['dc_invite_code'])) {
        $inviteCode = addslashes(trim($_COOKIE['dc_invite_code']));
    }
    $superiorUid = 0;
    $superiorSource = ''; // 绑定来源，用于日志
    // 1) 邀请码查找上级
    if ($inviteCode !== '') {
        if (class_exists('Level_Price')) { Level_Price::ensureSchema(); }
        $dbInvite = Database::getInstance();
        $superiorRow = $dbInvite->once_fetch_array("SELECT uid FROM " . DB_PREFIX . "user WHERE invite_code='" . $inviteCode . "' AND state=0 LIMIT 1");
        $superiorUid = intval($superiorRow['uid'] ?? 0);
        if ($superiorUid > 0) $superiorSource = 'invite';
    }
    // 2) 邀请码未找到上级：回落分店域名（在分店内注册自动绑定站长）
    if ($superiorUid <= 0 && !empty($stationData) && (int)($stationData['id'] ?? 0) > 0) {
        $_stOwnerUid = (int)($stationData['user_id'] ?? 0);
        if ($_stOwnerUid > 0) {
            // 校验站长账号有效（未禁用）
            $_stOwnerRow = Database::getInstance()->once_fetch_array(
                "SELECT uid, state FROM " . DB_PREFIX . "user WHERE uid={$_stOwnerUid} AND state=0 LIMIT 1"
            );
            if ($_stOwnerRow) {
                $superiorUid = $_stOwnerUid;
                $superiorSource = 'station';
            }
        }
    }

    if($type == 'email'){
        if (!checkMail($mail)) {
            Output::error('错误的邮箱格式');
        }
        if ($User_Model->isMailExist($mail)) {
            Output::error('该邮箱已被注册');
        }
    }
    if($type == 'tel'){
        if(strlen($tel) != 11){
            output::error('手机号码填写错误');
        }
        if ($User_Model->isTelExist($tel)) {
            Output::error('该手机号码已被注册');
        }
    }
    if($type == 'username'){
        if(empty($reg_username) || strlen($reg_username) < 3 || strlen($reg_username) > 20){
            Output::error('用户名长度为3-20个字符');
        }
        if(!preg_match('/^[a-zA-Z0-9_\x{4e00}-\x{9fa5}]+$/u', $reg_username)){
            Output::error('用户名只能包含中英文、数字和下划线');
        }
        if ($User_Model->isUserExist($reg_username)) {
            Output::error('该用户名已被注册');
        }
    }

    // 极验已激活时跳过图形验证码（极验优先级 > 图形验证码）
    $geetest_active = function_exists('geetest_is_active') && geetest_is_active();
    if (!$geetest_active) {
        if (!User::checkLoginCode($login_code)) {
            Output::error('图形验证码错误');
        }
    }
    if (Option::get('email_code') === 'y' && !User::checkMailCode($mail_code)) {
        Output::error('邮件验证码错误');
    }

    // 手机注册：验证短信验证码
    if ($type == 'tel' || (!empty($tel) && Option::get('register_tel_switch') == 'y')) {
        if (!isset($_SESSION)) session_start();
        $smsCode = trim(Input::postStrVar('sms_code'));
        if (empty($smsCode)) {
            Output::error('请输入短信验证码');
        }
        $rCode = $_SESSION['register_sms_code'] ?? '';
        $rTel  = $_SESSION['register_sms_tel'] ?? '';
        $rTime = $_SESSION['register_sms_time'] ?? 0;
        if (empty($rCode) || $rCode !== $smsCode) {
            Output::error('短信验证码错误');
        }
        if ($rTel !== $tel) {
            Output::error('手机号与验证码不匹配');
        }
        if (time() - $rTime > 600) {
            Output::error('短信验证码已过期，请重新获取');
        }
    }

    // 邮箱注册：验证邮箱验证码
    if ($type == 'email' || (!empty($mail) && Option::get('register_email_switch') == 'y')) {
        if (!isset($_SESSION)) session_start();
        $emailCode = trim(Input::postStrVar('email_code'));
        if (empty($emailCode)) {
            Output::error('请输入邮箱验证码');
        }
        $eCode = $_SESSION['mail_code'] ?? '';
        $eMail = $_SESSION['mail'] ?? '';
        if (empty($eCode) || strtoupper($eCode) !== strtoupper($emailCode)) {
            Output::error('邮箱验证码错误');
        }
        if ($eMail !== $mail) {
            Output::error('邮箱与验证码不匹配');
        }
    }

    if (strlen($passwd) < 6) {
        Output::error('密码不小于6位');
    }
    if ($passwd !== $repasswd) {
        Output::error('两次输入的密码不一致');
    }

    // 绑定手机号（主类型非 tel 时，若手机注册已开启则从 tel 字段取值并校验）
    // 强制绑定需同时满足：手机注册开启 + 强制绑定开关开启
    $bind_tel = '';
    if ($type !== 'tel' && Option::get('register_tel_switch') == 'y') {
        $_btVal = !empty($tel) ? $tel : Input::postStrVar('bind_tel');
        if (!empty($_btVal)) {
            if (strlen($_btVal) != 11) {
                Output::error('请填写正确的手机号码');
            }
            if ($User_Model->isTelExist($_btVal)) {
                Output::error('该手机号码已被注册');
            }
            $bind_tel = $_btVal;
        } elseif (Option::get('register_bind_tel') === 'y') {
            Output::error('请填写手机号码');
        }
    }

    // 绑定邮箱（主类型非 email 时，若邮箱注册已开启则从 email 字段取值并校验）
    // 强制绑定需同时满足：邮箱注册开启 + 强制绑定开关开启
    $bind_email = '';
    if ($type !== 'email' && Option::get('register_email_switch') == 'y') {
        $_beVal = !empty($mail) ? $mail : Input::postStrVar('bind_email');
        if (!empty($_beVal)) {
            if (!checkMail($_beVal)) {
                Output::error('请填写正确的邮箱地址');
            }
            if ($User_Model->isMailExist($_beVal)) {
                Output::error('该邮箱已被注册');
            }
            $bind_email = $_beVal;
        } elseif (Option::get('register_bind_email') === 'y') {
            Output::error('请填写邮箱地址');
        }
    }

    $PHPASS = new PasswordHash(8, true);
    $passwd = $PHPASS->HashPassword($passwd);
    if($type == 'tel'){
        $User_Model->addUser('', $tel, $passwd, $reg_ip,  User::ROLE_WRITER, 'tel');
        $account = $tel;
    }elseif($type == 'username'){
        $User_Model->addUser($reg_username, '', $passwd, $reg_ip, User::ROLE_WRITER, 'username');
        $account = $reg_username;
    }else{
        $User_Model->addUser('', $mail, $passwd, $reg_ip,  User::ROLE_WRITER, 'email');
        $account = $mail;
    }

    $CACHE->updateCache(['sta', 'user']);

    doAction('user_register_after', $account, $type);

    // 获取新注册用户的UID
    $regDb = Database::getInstance();
    if ($type == 'tel') {
        $regUser = $regDb->once_fetch_array("SELECT uid FROM " . DB_PREFIX . "user WHERE tel = '{$tel}' ORDER BY uid DESC LIMIT 1");
    } elseif ($type == 'username') {
        $regUser = $regDb->once_fetch_array("SELECT uid FROM " . DB_PREFIX . "user WHERE username = '{$reg_username}' ORDER BY uid DESC LIMIT 1");
    } else {
        $regUser = $regDb->once_fetch_array("SELECT uid FROM " . DB_PREFIX . "user WHERE email = '{$mail}' ORDER BY uid DESC LIMIT 1");
    }
    if ($regUser) {
        $newUid = intval($regUser['uid']);

        // 补写 superior / invite_code / 默认等级
        $updates = [];

        // 1) superior：禁止自绑、上级必须存在且非禁用
        if ($superiorUid > 0 && $superiorUid !== $newUid) {
            $updates['superior'] = $superiorUid;
        }

        // 2) 生成专属 invite_code（大写字母+数字，8位）
        $regDb2 = Database::getInstance();
        $inviteCodeNew = User_Model::generateInviteCode($newUid);
        if ($inviteCodeNew !== '') {
            $updates['invite_code'] = $inviteCodeNew;
        }

        // 3) 自动分配默认等级（每个新用户必定有等级）
        $memberModel = new Member_Model();
        $defaultGrade = $memberModel->getDefaultLevelId();
        if ($defaultGrade > 0) {
            $updates['level'] = $defaultGrade;
            // 若启用有效期则设置到期时间
            if (class_exists('Level_Service')) {
                $updates['level_expire_time'] = Level_Service::calculateDefaultAssignExpireTime(0, $defaultGrade);
            }
        }

        // 4) 强制绑定手机号/邮箱写入
        if ($bind_tel !== '') {
            $updates['tel'] = $bind_tel;
        }
        if ($bind_email !== '') {
            $updates['email'] = $bind_email;
        }

        if (!empty($updates)) {
            try {
                $regDb2->update('user', $updates, ['uid' => $newUid]);
            } catch (Throwable $e) {}
        }

        // 清理邀请码 cookie（已绑定成功）
        if ($superiorUid > 0 && !empty($_COOKIE['dc_invite_code'])) {
            setcookie('dc_invite_code', '', time() - 3600, '/');
        }

        $_supMsg = '';
        if ($superiorUid > 0) {
            $_sourceLabel = $superiorSource === 'invite' ? '邀请码' : ($superiorSource === 'station' ? '分店注册' : '');
            $_supMsg = '，上级 UID: ' . $superiorUid . '（来源：' . $_sourceLabel . '）';
        }
        $_regAccount = $type == 'tel' ? $tel : ($type == 'username' ? $reg_username : $mail);
        User_Log_Model::log($newUid, 'register', '用户注册（' . $type . '：' . $_regAccount . '）' . $_supMsg, 0, $reg_ip);

        // 自动升级检查：新用户注册后，上级的直推/团队人数变化，检查上级是否满足升级条件
        if ($superiorUid > 0) {
            try {
                $_memberModel = new Member_Model();
                $_memberModel->tryAutoUpgrade($superiorUid);
            } catch (\Throwable $_autoUpErr) {}
        }
    }

    // 注册成功后自动登录
    LoginAuth::setAuthCookie($account, 1);

    output::ok();
}

if ($action == 'send_email_code') {
    // 安全验证：极验启用时强制极验，否则图形验证码
    if (!isset($_SESSION)) session_start();
    $geetest_active = function_exists('geetest_is_active') && geetest_is_active();
    if ($geetest_active) {
        geetest_server_validate();
    } else {
        $imgcode = strtoupper(trim(Input::postStrVar('imgcode')));
        if (empty($imgcode) || $imgcode !== ($_SESSION['code'] ?? '')) {
            Output::error('图形验证码错误');
        }
        $_SESSION['code'] = null;
    }

    $mail = Input::postStrVar('mail');

    if (!checkMail($mail)) {
        Output::error('错误的邮箱');
    }

    doAction('send_email_code', $mail);

    $ret = Notice::sendVerifyMailCode($mail);
    if ($ret) {
        Output::ok();
    } else {
        Output::error('发送邮件失败');
    }
}

if ($action == 'reset') {
    if (ISLOGIN === true) {
        emDirect("../admin/");
    }

    $login_code = Option::get('login_code') === 'y';
    $error_msg = '';

    $page_title = '找回密码';
    if (isMobile() && file_exists(View::getUserView('reset_mobile'))) {
        require_once View::getUserView('reset_mobile');
    } else {
        include View::getUserView('user_head');
        require_once View::getUserView('reset');
    }
    View::output();
}

if ($action == 'doreset') {
    loginAuth::checkLogged();

    $mail = Input::postStrVar('mail');
    $login_code = strtoupper(Input::postStrVar('login_code'));
    $resp = Input::postStrVar('resp'); // eg: json (only support json now)

    // 极验已激活时跳过图形验证码，改用极验服务端验证
    $geetest_active = function_exists('geetest_is_active') && geetest_is_active();
    if (!$geetest_active) {
        if (!User::checkLoginCode($login_code)) {
            if ($resp === 'json') {
                Output::error('图形验证码错误');
            }
            emDirect('./account.php?action=reset&err_ckcode=1');
        }
    } else {
        geetest_server_validate();
    }
    if (!$mail || !$User_Model->isMailExist($mail)) {
        if ($resp === 'json') {
            Output::error('错误的注册邮箱');
        }
        emDirect('./account.php?action=reset&error_mail=1');
    }

    $ret = Notice::sendResetMailCode($mail);
    if ($ret) {
        if ($resp === 'json') {
            Output::ok();
        }
        emDirect("./account.php?action=reset2&succ_mail=1");
    } else {
        if ($resp === 'json') {
            Output::error('邮件验证码发送失败，请检查邮件通知设置');
        }
        emDirect("./account.php?action=reset&error_sendmail=1");
    }
}

if ($action == 'reset2') {
    if (ISLOGIN === true) {
        emDirect("../admin/");
    }

    $login_code = Option::get('login_code') === 'y';
    $error_msg = '';

    $page_title = '找回密码';

    if (isMobile() && file_exists(View::getUserView('reset2_mobile'))) {
        require_once View::getUserView('reset2_mobile');
    } else {
        include View::getUserView('user_head');
        include View::getUserView('reset2');
    }
    View::output();
}

if ($action == 'doreset2') {
    $mail_code = Input::postStrVar('mail_code');
    $passwd = Input::postStrVar('passwd');
    $repasswd = Input::postStrVar('repasswd');
    $resp = Input::postStrVar('resp'); // only json

    if (strlen($passwd) < 6) {
        if ($resp === 'json') {
            Output::error('密码长度不合规');
        }
        emDirect('./account.php?action=reset2&error_pwd_len=1');
    }
    if ($passwd !== $repasswd) {
        if ($resp === 'json') {
            Output::error('两次输入的密码不一致');
        }
        emDirect('./account.php?action=reset2&error_pwd2=1');
    }
    if (!$mail_code || !User::checkMailCode($mail_code)) {
        if ($resp === 'json') {
            Output::error('邮件验证码错误');
        }
        emDirect('./account.php?action=reset2&err_mail_code=1');
    }

    $PHPASS = new PasswordHash(8, true);
    $passwd = $PHPASS->HashPassword($passwd);
    if (!isset($_SESSION)) {
        session_start();
    }
    $mail = isset($_SESSION['mail']) ? $_SESSION['mail'] : '';
    $User_Model->updateUserByMail(['password' => $passwd], $mail);

    // 记录密码重置日志
    $resetUser = $regDb ?? Database::getInstance();
    $resetUserData = $resetUser->once_fetch_array("SELECT uid FROM " . DB_PREFIX . "user WHERE email = '{$mail}' LIMIT 1");
    if ($resetUserData) {
        User_Log_Model::log($resetUserData['uid'], 'password_reset', '通过邮箱重置密码');
    }

    if ($resp === 'json') {
        Output::ok();
    }
    emDirect("./account.php?action=signin&succ_reset=1");
}

if ($action == 'setting') {
    if (!ISLOGIN) {
        emDirect('./account.php?action=signin');
    }

    global $userData;
    $user = $User_Model->getOneUser(UID);
    $uc_app_mode = isMobile();
    $uc_page_title = '信息设置';
    $userTpl = View::getCurrentUserTemplateSlug();
    $safeUserTpl = preg_replace('/^([\w-]+)$/i', '$1', $userTpl);
    $settingFile = !empty($uc_app_mode) ? 'account_setting_mobile_app.php' : 'account_setting.php';
    $settingView = USER_TPLS_PATH . $safeUserTpl . '/' . $settingFile;
    if ($safeUserTpl !== $userTpl || !is_file($settingView)) {
        $settingFallback = USER_TPLS_PATH . $safeUserTpl . '/account_setting.php';
        if ($safeUserTpl === $userTpl && is_file($settingFallback)) {
            $settingView = $settingFallback;
        } else {
            $settingView = USER_TPLS_PATH . 'default/' . $settingFile;
            if (!is_file($settingView)) {
                $settingView = USER_TPLS_PATH . 'default/account_setting.php';
            }
        }
    }

    include View::getUserView('_adaptive_header');
    require_once $settingView;
    include View::getUserView('_adaptive_footer');
    View::output();
}

if ($action == 'setting_save') {
    if (!ISLOGIN) {
        Output::error('请先登录');
    }

    LoginAuth::checkToken();
    $nickname = Input::postStrVar('nickname');
    $username = Input::postStrVar('username');
    $new_password = Input::postStrVar('new_password');
    $hasWechatPost = array_key_exists('wechat', $_POST);
    $wechat = '';

    if (empty($nickname)) {
        Output::error('用户名称不能为空');
    }
    if (empty($username)) {
        Output::error('登录账号不能为空');
    }
    if ($User_Model->isNicknameExist($nickname, UID)) {
        Output::error('用户名称已被占用');
    }
    if ($User_Model->isUserExist($username, UID)) {
        Output::error('登录账号已被占用');
    }
    if ($hasWechatPost) {
        $wechat = trim((string)($_POST['wechat'] ?? ''));
        $wechat = preg_replace('/\s+/', '', $wechat);
        if (function_exists('mb_strlen') ? mb_strlen($wechat, 'UTF-8') > 40 : strlen($wechat) > 40) {
            Output::error('微信号最多40个字符');
        }
        if ($wechat !== '' && !preg_match('/^[A-Za-z0-9_\-]{3,40}$/', $wechat)) {
            Output::error('微信号仅支持3-40位字母、数字、下划线或中划线');
        }
        ensureUserWechatColumn(Database::getInstance());
    }

    $data = [
        'nickname' => $nickname,
        'username' => $username,
    ];
    if ($hasWechatPost) {
        $data['wechat'] = $wechat;
    }

    if (!empty($new_password)) {
        if (strlen($new_password) < 6) {
            Output::error('密码不得小于6位');
        }
        $PHPASS = new PasswordHash(8, true);
        $data['password'] = $PHPASS->HashPassword($new_password);
    }

    $User_Model->updateUser($data, UID);
    $CACHE->updateCache('user');

    // 记录修改资料日志
    $logParts = [];
    $logParts[] = '修改昵称: ' . $nickname;
    if ($hasWechatPost) {
        $logParts[] = $wechat === '' ? '清空微信号' : '设置微信号';
    }
    if (!empty($new_password)) {
        $logParts[] = '修改密码';
        User_Log_Model::log(UID, 'password_change', '用户修改密码');
    }
    User_Log_Model::log(UID, 'profile_update', implode('; ', $logParts));

    Output::ok('保存成功');
}

// 绑定邮箱
if ($action == 'bind_email') {
    if (!ISLOGIN) {
        Output::error('请先登录');
    }
    $token = isset($_REQUEST['token']) ? addslashes($_REQUEST['token']) : '';
    if (!empty(LoginAuth::getToken()) && $token !== LoginAuth::getToken()) {
        Output::error('安全校验失败，请刷新页面重试');
    }

    $email = Input::postStrVar('email');
    $mail_code = Input::postStrVar('mail_code');

    if (!checkMail($email)) {
        Output::error('请输入正确的邮箱地址');
    }

    $currentUser = $User_Model->getOneUser(UID);
    if (!empty($currentUser['email'])) {
        Output::error('当前账号已绑定邮箱，请先解绑');
    }

    if ($User_Model->isMailExist($email)) {
        Output::error('该邮箱已被其他账号使用');
    }

    if (!User::checkMailCode($mail_code)) {
        Output::error('验证码错误或已过期');
    }

    if (!isset($_SESSION)) session_start();
    if (empty($_SESSION['mail']) || $_SESSION['mail'] !== $email) {
        Output::error('验证码与邮箱不匹配，请重新获取');
    }

    $User_Model->updateUser(['email' => $email], UID);
    $CACHE->updateCache('user');
    User_Log_Model::log(UID, 'profile_update', '绑定邮箱: ' . $email);

    Output::ok('邮箱绑定成功');
}

// 解绑邮箱
if ($action == 'unbind_email') {
    if (!ISLOGIN) {
        Output::error('请先登录');
    }
    $token = isset($_REQUEST['token']) ? addslashes($_REQUEST['token']) : '';
    if (!empty(LoginAuth::getToken()) && $token !== LoginAuth::getToken()) {
        Output::error('安全校验失败，请刷新页面重试');
    }

    $mail_code = Input::postStrVar('mail_code');

    // 直接读数据库获取原始字段值
    $rawRow = Database::getInstance()->once_fetch_array("SELECT email, username, tel FROM " . DB_PREFIX . "user WHERE uid=" . intval(UID));
    $currentEmail = trim($rawRow['email'] ?? '');

    if (empty($currentEmail)) {
        Output::error('当前账号未绑定邮箱');
    }

    // 检查是否为唯一登录方式
    $hasUsername = !empty(trim($rawRow['username'] ?? ''));
    $hasTel = !empty(trim($rawRow['tel'] ?? ''));
    if (!$hasUsername && !$hasTel) {
        Output::error('邮箱是当前唯一登录方式，不支持解绑');
    }

    if (!User::checkMailCode($mail_code)) {
        Output::error('验证码错误或已过期');
    }

    if (!isset($_SESSION)) session_start();
    if (empty($_SESSION['mail']) || $_SESSION['mail'] !== $currentEmail) {
        Output::error('验证码与当前绑定邮箱不匹配');
    }

    $User_Model->updateUser(['email' => ''], UID);
    $CACHE->updateCache('user');
    User_Log_Model::log(UID, 'profile_update', '解绑邮箱: ' . $currentEmail);

    Output::ok('邮箱已解绑');
}

// 发送绑定手机验证码
if ($action == 'send_bind_phone_code') {
    if (!ISLOGIN) {
        Output::error('请先登录');
    }

    if (!isset($_SESSION)) session_start();

    // 安全验证：极验启用时强制极验，否则图形验证码
    $geetest_active = function_exists('geetest_is_active') && geetest_is_active();
    if ($geetest_active) {
        geetest_server_validate();
    } else {
        $imgcode = strtoupper(trim(Input::postStrVar('imgcode')));
        if (empty($imgcode) || $imgcode !== ($_SESSION['code'] ?? '')) {
            Output::error('图形验证码错误');
        }
        $_SESSION['code'] = null;
    }

    // 每日发送次数限制
    $dailyLimit = (int)(Option::get('sms_bind_phone_daily_limit') ?: 5);
    if ($dailyLimit > 0) {
        $todayKey = 'sms_bind_phone_count_' . date('Ymd');
        $todayCount = $_SESSION[$todayKey] ?? 0;
        if ($todayCount >= $dailyLimit) {
            Output::error('今日绑定手机短信发送已达上限（' . $dailyLimit . '次）');
        }
    }

    // 频率限制 60s
    $lastTime = $_SESSION['bind_phone_time'] ?? 0;
    if (time() - $lastTime < 60) {
        Output::error('请' . (60 - (time() - $lastTime)) . '秒后再试');
    }

    $phone = Input::postStrVar('phone');
    if (empty($phone) || !preg_match('/^1[3-9]\d{9}$/', $phone)) {
        Output::error('请输入正确的手机号');
    }

    $currentUser = $User_Model->getOneUser(UID);
    if (!empty($currentUser['tel'])) {
        Output::error('当前账号已绑定手机号，请先解绑');
    }

    if ($User_Model->isTelExist($phone)) {
        Output::error('该手机号已被其他账号使用');
    }

    // 生成验证码
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // 通过授权服务器发送短信
    $storeModel = new Store_Model();
    $res = $storeModel->sendSmsCode($phone, $code);
    if (!$res || $res['code'] != 200) {
        Output::error($res['msg'] ?? '短信发送失败');
    }

    $_SESSION['bind_phone_code'] = $code;
    $_SESSION['bind_phone_number'] = $phone;
    $_SESSION['bind_phone_time'] = time();
    // 累加每日计数
    $todayKey = 'sms_bind_phone_count_' . date('Ymd');
    $_SESSION[$todayKey] = ($_SESSION[$todayKey] ?? 0) + 1;

    Output::ok('验证码已发送');
}

// 绑定手机号
if ($action == 'bind_phone') {
    if (!ISLOGIN) {
        Output::error('请先登录');
    }
    $token = isset($_REQUEST['token']) ? addslashes($_REQUEST['token']) : '';
    if (!empty(LoginAuth::getToken()) && $token !== LoginAuth::getToken()) {
        Output::error('安全校验失败，请刷新页面重试');
    }

    if (!isset($_SESSION)) session_start();

    $phone = Input::postStrVar('phone');
    $code = Input::postStrVar('code');

    if (empty($phone) || !preg_match('/^1[3-9]\d{9}$/', $phone)) {
        Output::error('请输入正确的手机号');
    }
    if (empty($code)) {
        Output::error('请输入验证码');
    }

    $sCode = $_SESSION['bind_phone_code'] ?? '';
    $sPhone = $_SESSION['bind_phone_number'] ?? '';
    $sTime = $_SESSION['bind_phone_time'] ?? 0;

    if (empty($sCode) || $sCode !== $code) {
        Output::error('验证码错误');
    }
    if ($sPhone !== $phone) {
        Output::error('手机号与验证码不匹配');
    }
    if (time() - $sTime > 600) {
        Output::error('验证码已过期，请重新获取');
    }

    $currentUser = $User_Model->getOneUser(UID);
    if (!empty($currentUser['tel'])) {
        Output::error('当前账号已绑定手机号');
    }
    if ($User_Model->isTelExist($phone)) {
        Output::error('该手机号已被其他账号使用');
    }

    $User_Model->updateUser(['tel' => $phone], UID);
    $CACHE->updateCache('user');
    unset($_SESSION['bind_phone_code'], $_SESSION['bind_phone_number'], $_SESSION['bind_phone_time']);
    User_Log_Model::log(UID, 'profile_update', '绑定手机: ' . $phone);

    Output::ok('手机号绑定成功');
}

// 发送解绑手机验证码（发送到当前绑定手机）
if ($action == 'send_unbind_phone_code') {
    if (!ISLOGIN) {
        Output::error('请先登录');
    }

    if (!isset($_SESSION)) session_start();

    // 安全验证：极验启用时强制极验，否则图形验证码
    $geetest_active = function_exists('geetest_is_active') && geetest_is_active();
    if ($geetest_active) {
        geetest_server_validate();
    } else {
        $imgcode = strtoupper(trim(Input::postStrVar('imgcode')));
        if (empty($imgcode) || $imgcode !== ($_SESSION['code'] ?? '')) {
            Output::error('图形验证码错误');
        }
        $_SESSION['code'] = null;
    }

    // 每日发送次数限制（与绑定手机共享配额）
    $dailyLimit = (int)(Option::get('sms_bind_phone_daily_limit') ?: 5);
    if ($dailyLimit > 0) {
        $todayKey = 'sms_bind_phone_count_' . date('Ymd');
        $todayCount = $_SESSION[$todayKey] ?? 0;
        if ($todayCount >= $dailyLimit) {
            Output::error('今日手机短信发送已达上限（' . $dailyLimit . '次）');
        }
    }

    $lastTime = $_SESSION['unbind_phone_time'] ?? 0;
    if (time() - $lastTime < 60) {
        Output::error('请' . (60 - (time() - $lastTime)) . '秒后再试');
    }

    $rawRow = Database::getInstance()->once_fetch_array("SELECT tel, username, email FROM " . DB_PREFIX . "user WHERE uid=" . intval(UID));
    $currentTel = trim($rawRow['tel'] ?? '');

    if (empty($currentTel)) {
        Output::error('当前账号未绑定手机号');
    }

    // 检查是否为唯一登录方式
    $hasUsername = !empty(trim($rawRow['username'] ?? ''));
    $hasEmail = !empty(trim($rawRow['email'] ?? ''));
    if (!$hasUsername && !$hasEmail) {
        Output::error('手机号是当前唯一登录方式，不支持解绑');
    }

    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    $storeModel = new Store_Model();
    $res = $storeModel->sendSmsCode($currentTel, $code);
    if (!$res || $res['code'] != 200) {
        Output::error($res['msg'] ?? '短信发送失败');
    }

    $_SESSION['unbind_phone_code'] = $code;
    $_SESSION['unbind_phone_number'] = $currentTel;
    $_SESSION['unbind_phone_time'] = time();
    // 累加每日计数
    $todayKey = 'sms_bind_phone_count_' . date('Ymd');
    $_SESSION[$todayKey] = ($_SESSION[$todayKey] ?? 0) + 1;

    Output::ok('验证码已发送');
}

// 解绑手机号
if ($action == 'unbind_phone') {
    if (!ISLOGIN) {
        Output::error('请先登录');
    }
    $token = isset($_REQUEST['token']) ? addslashes($_REQUEST['token']) : '';
    if (!empty(LoginAuth::getToken()) && $token !== LoginAuth::getToken()) {
        Output::error('安全校验失败，请刷新页面重试');
    }

    if (!isset($_SESSION)) session_start();

    $code = Input::postStrVar('code');
    if (empty($code)) {
        Output::error('请输入验证码');
    }

    $sCode = $_SESSION['unbind_phone_code'] ?? '';
    $sTel = $_SESSION['unbind_phone_number'] ?? '';
    $sTime = $_SESSION['unbind_phone_time'] ?? 0;

    if (empty($sCode) || $sCode !== $code) {
        Output::error('验证码错误');
    }
    if (time() - $sTime > 600) {
        Output::error('验证码已过期，请重新获取');
    }

    $rawRow = Database::getInstance()->once_fetch_array("SELECT tel, username, email FROM " . DB_PREFIX . "user WHERE uid=" . intval(UID));
    $currentTel = trim($rawRow['tel'] ?? '');
    if (empty($currentTel) || $currentTel !== $sTel) {
        Output::error('手机号状态异常，请刷新页面重试');
    }

    $hasUsername = !empty(trim($rawRow['username'] ?? ''));
    $hasEmail = !empty(trim($rawRow['email'] ?? ''));
    if (!$hasUsername && !$hasEmail) {
        Output::error('手机号是当前唯一登录方式，不支持解绑');
    }

    $User_Model->updateUser(['tel' => ''], UID);
    $CACHE->updateCache('user');
    unset($_SESSION['unbind_phone_code'], $_SESSION['unbind_phone_number'], $_SESSION['unbind_phone_time']);
    User_Log_Model::log(UID, 'profile_update', '解绑手机: ' . $currentTel);

    Output::ok('手机号已解绑');
}

if ($action == 'invite') {
    if (!ISLOGIN) {
        emDirect('./account.php?action=signin');
    }
    // 直接从数据库读取邀请码和微信号（$userData 可能不含 invite_code / wechat 字段）
    $inviteDb = Database::getInstance();
    ensureUserWechatColumn($inviteDb);
    $_invRow = $inviteDb->once_fetch_array("SELECT invite_code, wechat FROM " . DB_PREFIX . "user WHERE uid=" . intval(UID));
    $myInviteCode = !empty($_invRow['invite_code']) ? $_invRow['invite_code'] : '';
    $myWechat = trim((string)($_invRow['wechat'] ?? ''));

    // 老用户可能没有邀请码，自动补生成
    if ($myInviteCode === '') {
        $myInviteCode = User_Model::generateInviteCode(UID);
        if ($myInviteCode !== '') {
            Database::getInstance()->update('user', ['invite_code' => $myInviteCode], ['uid' => UID]);
        }
    }

    // 收集所有可用的邀请链接域名选项
    $inviteDomainOptions = [];
    $defaultUrl = rtrim(DC_URL, '/');
    $inviteDomainOptions[] = ['label' => '主站默认域名', 'base' => $defaultUrl, 'suffix' => '/?invite='];
    if (!empty($userData['station'])) {
        $st = $userData['station'];
        if (!empty($st['domain_2'])) {
            $inviteDomainOptions[] = ['label' => '分店二级域名', 'base' => '//' . $st['domain_2'], 'suffix' => '/?invite='];
        }
        if (!empty($st['domain'])) {
            $inviteDomainOptions[] = ['label' => '分店独立域名', 'base' => '//' . $st['domain'], 'suffix' => '/?invite='];
        }
        $options_cache = $CACHE->readCache('options');
        $slug_mode = isset($options_cache['station_slug_mode']) ? $options_cache['station_slug_mode'] : '0';
        if ($slug_mode === '1' && !empty($st['slug']) && $st['slug'] !== 'NULL') {
            $inviteDomainOptions[] = ['label' => '分店路径标识', 'base' => $defaultUrl . '/s/' . $st['slug'], 'suffix' => '?invite='];
        }
    }
    $myInviteLink = $myInviteCode !== '' ? $inviteDomainOptions[0]['base'] . $inviteDomainOptions[0]['suffix'] . $myInviteCode : '';

    // 统计下级用户数
    $inviteCount = (int)($inviteDb->once_fetch_array("SELECT COUNT(*) AS cnt FROM " . DB_PREFIX . "user WHERE superior=" . intval(UID) . " AND delete_time IS NULL")['cnt'] ?? 0);

    // 统计所有粉丝总数（含推荐粉丝）—— 按层批量查询，防环
    $totalFansCount = 0;
    $_parentIds = [intval(UID)];
    $_visited = [intval(UID) => true];
    while (!empty($_parentIds)) {
        $_inList = implode(',', $_parentIds);
        $_childRows = $inviteDb->query("SELECT uid FROM " . DB_PREFIX . "user WHERE superior IN ({$_inList}) AND delete_time IS NULL");
        $_parentIds = [];
        while ($_cr = $inviteDb->fetch_array($_childRows)) {
            $_cuid = (int)$_cr['uid'];
            if (isset($_visited[$_cuid])) continue;
            $_visited[$_cuid] = true;
            $totalFansCount++;
            $_parentIds[] = $_cuid;
        }
    }

    $uc_app_mode = isMobile();
    $uc_page_title = '邀请好友';
    $page_title = '邀请好友';
    include View::getUserView('_adaptive_header');
    require_once View::getUserView(isMobile() ? 'adaptive_invite_mobile_app' : 'adaptive_invite');
    include View::getUserView('_adaptive_footer');
    View::output();
}

if ($action == 'fans_save_wechat') {
    if (!ISLOGIN) {
        Output::error('请先登录');
    }
    $token = isset($_REQUEST['token']) ? addslashes($_REQUEST['token']) : '';
    if (!empty(LoginAuth::getToken()) && $token !== LoginAuth::getToken()) {
        Output::error('安全校验失败，请刷新页面重试');
    }

    $wechat = trim((string)($_POST['wechat'] ?? ''));
    $wechat = preg_replace('/\s+/', '', $wechat);
    if (function_exists('mb_strlen') ? mb_strlen($wechat, 'UTF-8') > 40 : strlen($wechat) > 40) {
        Output::error('微信号最多40个字符');
    }
    if ($wechat !== '' && !preg_match('/^[A-Za-z0-9_\-]{3,40}$/', $wechat)) {
        Output::error('微信号仅支持3-40位字母、数字、下划线或中划线');
    }

    $fansDb = Database::getInstance();
    ensureUserWechatColumn($fansDb);
    $safeWechat = $fansDb->escape_string($wechat);
    $fansDb->query("UPDATE " . DB_PREFIX . "user SET wechat='{$safeWechat}', update_time=" . time() . " WHERE uid=" . intval(UID));
    $CACHE->updateCache('user');
    if (class_exists('User_Log_Model')) {
        User_Log_Model::log(UID, 'profile_update', $wechat === '' ? '清空微信号' : '设置微信号');
    }

    Output::ok($wechat === '' ? '微信号已清空' : '微信号已保存');
}

if ($action == 'fans') {
    if (!ISLOGIN) {
        emDirect('./account.php?action=signin');
    }
    $fansDb = Database::getInstance();
    ensureUserWechatColumn($fansDb);
    $today_start = strtotime('today');

    // 递归获取所有下级UID（推荐粉丝，不限层级）—— 按层批量查询，防环
    function getAllDescendantUids($db, $rootUid) {
        $all = [];
        $parentIds = [intval($rootUid)];
        $visited = [intval($rootUid) => true];
        while (!empty($parentIds)) {
            $inList = implode(',', $parentIds);
            $rows = $db->query("SELECT uid FROM " . DB_PREFIX . "user WHERE superior IN ({$inList}) AND delete_time IS NULL");
            $parentIds = [];
            while ($row = $db->fetch_array($rows)) {
                $uid = (int)$row['uid'];
                if (isset($visited[$uid])) continue;
                $visited[$uid] = true;
                $all[] = $uid;
                $parentIds[] = $uid;
            }
        }
        return $all;
    }

    // 直邀粉丝UID列表
    $directFansRows = $fansDb->query("SELECT uid FROM " . DB_PREFIX . "user WHERE superior=" . intval(UID) . " AND delete_time IS NULL");
    $directFanUids = [];
    while ($r = $fansDb->fetch_array($directFansRows)) {
        $directFanUids[] = (int)$r['uid'];
    }
    $directFansCount = count($directFanUids);

    // 推荐粉丝（所有下级，不含直邀）
    $allDescendants = getAllDescendantUids($fansDb, UID);
    $referralFanUids = array_diff($allDescendants, $directFanUids);
    $referralFansCount = count($referralFanUids);
    $totalFansCount = count($allDescendants);

    // 统计指定粉丝集合中有付款记录的用户数
    $countPaidFans = function($uids) use ($fansDb) {
        $uids = array_values(array_filter(array_map('intval', (array)$uids)));
        if (empty($uids)) return 0;
        $count = 0;
        $chunks = array_chunk($uids, 500);
        foreach ($chunks as $chunk) {
            $inStr = implode(',', $chunk);
            $row = $fansDb->once_fetch_array("SELECT COUNT(DISTINCT user_id) AS cnt FROM " . DB_PREFIX . "order WHERE user_id IN ($inStr) AND pay_time > 0 AND delete_time IS NULL");
            $count += (int)($row['cnt'] ?? 0);
        }
        return $count;
    };

    // 有效粉丝（有付款记录）与潜在粉丝（暂无付款记录）
    $directActiveFansCount = $countPaidFans($directFanUids);
    $referralActiveFansCount = $countPaidFans($referralFanUids);
    $activeFansCount = $directActiveFansCount + $referralActiveFansCount;
    $directPotentialFansCount = max(0, $directFansCount - $directActiveFansCount);
    $referralPotentialFansCount = max(0, $referralFansCount - $referralActiveFansCount);

    // 潜在粉丝（所有下级中没有下单记录的）
    $potentialFansCount = max(0, $totalFansCount - $activeFansCount);

    // 直邀粉丝今日统计
    $directTodayNew = 0;
    $directTodayActive = 0;
    $directTodayOrders = 0;
    if (!empty($directFanUids)) {
        $inDirect = implode(',', $directFanUids);
        $r = $fansDb->once_fetch_array("SELECT COUNT(*) AS cnt FROM " . DB_PREFIX . "user WHERE uid IN ($inDirect) AND create_time >= $today_start");
        $directTodayNew = (int)($r['cnt'] ?? 0);
        $r = $fansDb->once_fetch_array("SELECT COUNT(DISTINCT user_id) AS cnt FROM " . DB_PREFIX . "order WHERE user_id IN ($inDirect) AND create_time >= $today_start AND delete_time IS NULL");
        $directTodayOrders = (int)($r['cnt'] ?? 0);
        $r = $fansDb->once_fetch_array("SELECT COUNT(*) AS cnt FROM " . DB_PREFIX . "user WHERE uid IN ($inDirect) AND update_time >= $today_start");
        $directTodayActive = (int)($r['cnt'] ?? 0);
    }

    // 推荐粉丝今日统计
    $referralTodayNew = 0;
    $referralTodayActive = 0;
    $referralTodayOrders = 0;
    if (!empty($referralFanUids)) {
        $referralFanUidsArr = array_values($referralFanUids);
        $chunks = array_chunk($referralFanUidsArr, 500);
        foreach ($chunks as $chunk) {
            $inRef = implode(',', $chunk);
            $r = $fansDb->once_fetch_array("SELECT COUNT(*) AS cnt FROM " . DB_PREFIX . "user WHERE uid IN ($inRef) AND create_time >= $today_start");
            $referralTodayNew += (int)($r['cnt'] ?? 0);
            $r = $fansDb->once_fetch_array("SELECT COUNT(DISTINCT user_id) AS cnt FROM " . DB_PREFIX . "order WHERE user_id IN ($inRef) AND create_time >= $today_start AND delete_time IS NULL");
            $referralTodayOrders += (int)($r['cnt'] ?? 0);
            $r = $fansDb->once_fetch_array("SELECT COUNT(*) AS cnt FROM " . DB_PREFIX . "user WHERE uid IN ($inRef) AND update_time >= $today_start");
            $referralTodayActive += (int)($r['cnt'] ?? 0);
        }
    }

    // 我的推荐人（直接查库，$userData 可能不含 superior 字段）
    $mySuperior = null;
    $_myWechatRow = $fansDb->once_fetch_array("SELECT wechat FROM " . DB_PREFIX . "user WHERE uid=" . intval(UID));
    $myWechat = trim((string)($_myWechatRow['wechat'] ?? ''));

    $_supRow = $fansDb->once_fetch_array("SELECT superior FROM " . DB_PREFIX . "user WHERE uid=" . intval(UID));
    $mySuperiorUid = $_supRow ? (int)$_supRow['superior'] : 0;
    if ($mySuperiorUid > 0) {
        $mySuperior = $fansDb->once_fetch_array("SELECT uid, nickname, username, photo, create_time, level, wechat FROM " . DB_PREFIX . "user WHERE uid=$mySuperiorUid AND delete_time IS NULL");
        if ($mySuperior) {
            $supLid = (int)($mySuperior['level'] ?? 0);
            $mySuperior['level_name'] = '';
            if ($supLid > 0) {
                $supLvl = $fansDb->once_fetch_array("SELECT name FROM " . DB_PREFIX . "member WHERE id=$supLid");
                if ($supLvl) $mySuperior['level_name'] = $supLvl['name'];
            }
            $supSt = $fansDb->once_fetch_array("SELECT s.user_id, sl.name AS station_level_name FROM " . DB_PREFIX . "station s LEFT JOIN " . DB_PREFIX . "station_level sl ON s.level_id=sl.id WHERE s.user_id=$mySuperiorUid LIMIT 1");
            $mySuperior['has_station'] = !empty($supSt);
            $mySuperior['station_level_name'] = $supSt ? ($supSt['station_level_name'] ?: '') : '';
        }
    }

    $uc_app_mode = isMobile();
    $uc_page_title = '我的粉丝';
    $page_title = '我的粉丝';
    include View::getUserView('_adaptive_header');
    require_once View::getUserView(isMobile() ? 'adaptive_fans_mobile_app' : 'adaptive_fans');
    include View::getUserView('_adaptive_footer');
    View::output();
}

if ($action == 'fans_detail') {
    if (!ISLOGIN) {
        emDirect('./account.php?action=signin');
    }
    $fansTypeMap = [
        'total' => '总粉丝',
        'direct' => '直邀粉丝',
        'referral' => '推荐粉丝',
        'active' => '有效粉丝',
        'potential' => '潜在粉丝'
    ];
    $fansType = trim($_GET['type'] ?? 'total');
    if (!isset($fansTypeMap[$fansType])) {
        $fansType = 'total';
    }
    $uc_app_mode = isMobile();
    $uc_page_title = $fansTypeMap[$fansType];
    $page_title = $uc_page_title;
    include View::getUserView('_adaptive_header');
    require_once View::getUserView(isMobile() ? 'adaptive_fans_detail_mobile_app' : 'adaptive_fans_detail');
    include View::getUserView('_adaptive_footer');
    View::output();
}

if ($action == 'fans_bind_superior') {
    if (!ISLOGIN) {
        Output::error('请先登录');
    }
    $inviteCode = trim($_POST['invite_code'] ?? '');
    if ($inviteCode === '') {
        Output::error('请输入邀请码');
    }
    $currentSuperior = (int)($userData['superior'] ?? 0);
    if ($currentSuperior > 0) {
        Output::error('您已绑定推荐人，无法重复绑定');
    }
    $fansDb = Database::getInstance();
    $targetUser = $fansDb->once_fetch_array("SELECT uid FROM " . DB_PREFIX . "user WHERE invite_code='" . addslashes($inviteCode) . "' AND delete_time IS NULL AND state=0 LIMIT 1");
    if (!$targetUser) {
        Output::error('邀请码无效或用户不存在');
    }
    $targetUid = (int)$targetUser['uid'];
    if ($targetUid === UID) {
        Output::error('不能绑定自己为推荐人');
    }
    // 防止循环绑定
    $checkUid = $targetUid;
    $depth = 0;
    while ($checkUid > 0 && $depth < 100) {
        if ($checkUid === UID) {
            Output::error('不能绑定您的下级为推荐人');
        }
        $parentRow = $fansDb->once_fetch_array("SELECT superior FROM " . DB_PREFIX . "user WHERE uid=$checkUid");
        $checkUid = $parentRow ? (int)$parentRow['superior'] : 0;
        $depth++;
    }
    $fansDb->query("UPDATE " . DB_PREFIX . "user SET superior=$targetUid, update_time=" . time() . " WHERE uid=" . UID);
    if (class_exists('User_Log_Model')) {
        User_Log_Model::log(UID, 'superior_bind', '用户主动绑定推荐人，推荐人UID：' . $targetUid . '（邀请码：' . $inviteCode . '）');
    }
    Output::ok('已成功绑定推荐人（UID: ' . $targetUid . '）');
}

if ($action == 'fans_list') {
    if (!ISLOGIN) {
        Output::error('请先登录');
    }
    $type = $_GET['type'] ?? 'total';
    $page = max(1, intval($_GET['page'] ?? 1));
    $pageSize = 20;
    $offset = ($page - 1) * $pageSize;
    $fansDb = Database::getInstance();
    ensureUserWechatColumn($fansDb);

    $validTypes = ['total', 'direct', 'referral', 'active', 'potential'];
    if (!in_array($type, $validTypes, true)) {
        Output::error('无效类型');
    }

    // 直邀粉丝
    $directUids = [];
    $directRows = $fansDb->query("SELECT uid FROM " . DB_PREFIX . "user WHERE superior=" . intval(UID) . " AND delete_time IS NULL");
    while ($dr = $fansDb->fetch_array($directRows)) {
        $directUids[] = (int)$dr['uid'];
    }

    // 全部下级粉丝（含直邀 + 间接），使用 visited 防止异常循环关系导致加载卡住
    $getAllDescUids = function($rootUid) use ($fansDb) {
        $all = [];
        $queue = [intval($rootUid)];
        $visited = [intval($rootUid) => true];
        while (!empty($queue)) {
            $parentIds = array_values(array_unique(array_filter(array_map('intval', $queue))));
            $queue = [];
            if (empty($parentIds)) break;
            $inParents = implode(',', $parentIds);
            $rs = $fansDb->query("SELECT uid FROM " . DB_PREFIX . "user WHERE superior IN ($inParents) AND delete_time IS NULL");
            while ($rr = $fansDb->fetch_array($rs)) {
                $uid = (int)$rr['uid'];
                if ($uid <= 0 || isset($visited[$uid])) continue;
                $visited[$uid] = true;
                $all[] = $uid;
                $queue[] = $uid;
            }
        }
        return $all;
    };

    // 有付款记录的粉丝。兼容 pay_time 与 pay_status 两种成功标记，避免部分订单只有 pay_status 时有效粉丝加载为空。
    $getPaidUids = function($uids) use ($fansDb) {
        $uids = array_values(array_unique(array_filter(array_map('intval', (array)$uids))));
        if (empty($uids)) return [];
        $paidMap = [];
        foreach (array_chunk($uids, 500) as $chunk) {
            $inStr = implode(',', $chunk);
            $sql = "SELECT DISTINCT user_id FROM " . DB_PREFIX . "order WHERE user_id IN ($inStr) AND ((pay_time IS NOT NULL AND pay_time > 0) OR pay_status=1) AND delete_time IS NULL";
            $rs = $fansDb->query($sql);
            while ($row = $fansDb->fetch_array($rs)) {
                $uid = (int)$row['user_id'];
                if ($uid > 0) $paidMap[$uid] = true;
            }
        }
        return array_keys($paidMap);
    };

    $allDesc = $getAllDescUids(UID);
    $paidUids = ($type === 'active' || $type === 'potential') ? $getPaidUids($allDesc) : [];
    if ($type === 'total') {
        $targetUids = $allDesc;
    } elseif ($type === 'direct') {
        $targetUids = $directUids;
    } elseif ($type === 'referral') {
        $targetUids = array_values(array_diff($allDesc, $directUids));
    } elseif ($type === 'active') {
        $targetUids = array_values(array_intersect($allDesc, $paidUids));
    } else {
        $targetUids = array_values(array_diff($allDesc, $paidUids));
    }

    $total = count($targetUids);
    $rows = null;
    if (!empty($targetUids)) {
        $inTargets = implode(',', array_values(array_unique(array_map('intval', $targetUids))));
        $rows = $fansDb->query("SELECT uid, nickname, username, photo, create_time, expend, level, wechat FROM " . DB_PREFIX . "user WHERE uid IN ($inTargets) AND delete_time IS NULL ORDER BY create_time DESC LIMIT $offset,$pageSize");
    }

    $list = [];
    $levelIds = [];
    $fanUids = [];
    if ($rows) {
        while ($fan = $fansDb->fetch_array($rows)) {
            $lid = (int)($fan['level'] ?? 0);
            $list[] = [
                'uid' => (int)$fan['uid'],
                'nickname' => $fan['nickname'] ?: $fan['username'],
                'photo' => User::getAvatar($fan['photo']),
                'create_time' => date('Y-m-d', $fan['create_time']),
                'expend' => (float)$fan['expend'],
                'wechat' => trim((string)($fan['wechat'] ?? '')),
                'level_id' => $lid,
                'level_name' => '',
                'has_station' => false
            ];
            if ($lid > 0) $levelIds[$lid] = true;
            $fanUids[] = (int)$fan['uid'];
        }
    }
    // 批量查会员等级名称
    if (!empty($levelIds)) {
        $inLvl = implode(',', array_keys($levelIds));
        $lvlRows = $fansDb->query("SELECT id, name FROM " . DB_PREFIX . "member WHERE id IN ($inLvl)");
        $lvlMap = [];
        while ($lv = $fansDb->fetch_array($lvlRows)) {
            $lvlMap[(int)$lv['id']] = $lv['name'];
        }
        foreach ($list as &$item) {
            if ($item['level_id'] > 0 && isset($lvlMap[$item['level_id']])) {
                $item['level_name'] = $lvlMap[$item['level_id']];
            }
        }
        unset($item);
    }
    // 批量查分站及分站等级名称
    if (!empty($fanUids)) {
        $inUids = implode(',', $fanUids);
        $stRows = $fansDb->query("SELECT s.user_id, sl.name AS station_level_name FROM " . DB_PREFIX . "station s LEFT JOIN " . DB_PREFIX . "station_level sl ON s.level_id=sl.id WHERE s.user_id IN ($inUids)");
        $stMap = [];
        while ($st = $fansDb->fetch_array($stRows)) {
            $stMap[(int)$st['user_id']] = $st['station_level_name'] ?: '';
        }
        foreach ($list as &$item) {
            $item['has_station'] = isset($stMap[$item['uid']]);
            $item['station_level_name'] = $stMap[$item['uid']] ?? '';
        }
        unset($item);
    }
    // 清理内部字段
    foreach ($list as &$item) { unset($item['level_id']); }
    unset($item);
    header('Content-Type: application/json');
    echo json_encode(['code' => 0, 'total' => $total, 'page' => $page, 'list' => $list]);
    exit;
}

if ($action == 'logout') {
    if (ISLOGIN) {
        User_Log_Model::log(UID, 'logout', '用户退出登录');
    }
    setcookie(AUTH_COOKIE_NAME, ' ', time() - 31536000, '/');
    emDirect("../");
}

if ($action == 'agreement') {
    $agreement_content = Option::get('user_agreement');
    $page_title = '用户服务协议';
    require_once View::getUserView('user_head');
    require_once View::getUserView('agreement');
    View::output();
}

if ($action == 'privacy') {
    $privacy_content = Option::get('privacy_policy');
    $page_title = '隐私政策';
    require_once View::getUserView('user_head');
    require_once View::getUserView('privacy');
    View::output();
}
