<?php
/**
 * account administration
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

/**
 * @var string $action
 * @var object $CACHE
 */

require_once '../init.php';

$sta_cache = $CACHE->readCache('sta');
$user_cache = $CACHE->readCache('user');
$action = Input::getStrVar('action');
$User_Model = new User_Model();

/**
 * 登录页单条线路延迟检测（无需登录）
 * 前端并行调用此接口逐条检测，避免串行阻塞
 */
if ($action == 'ping_single_line_login') {
    header('Content-Type: application/json; charset=utf-8');
    $lineKey = isset($_GET['line']) ? (int)$_GET['line'] : -1;
    if (!isset(DC_LINE[$lineKey])) {
        echo json_encode(['code' => 400, 'msg' => '无效线路']);
        exit;
    }
    $line = DC_LINE[$lineKey];
    $url = rtrim($line['value'], '/') . '/api/dcshop.php?action=ping';
    $timeout = 3;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . '&t=' . microtime(true));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'DCShop-PingCheck');

    $start = microtime(true);
    $resp = curl_exec($ch);
    $elapsed = round((microtime(true) - $start) * 1000);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($resp === false || $httpCode === 0) {
        echo json_encode(['code' => 200, 'data' => ['ms' => -1, 'avg_ms' => -1, 'status' => 'timeout', 'text' => '超时']]);
    } else {
        echo json_encode(['code' => 200, 'data' => ['ms' => $elapsed, 'avg_ms' => $elapsed, 'status' => 'ok', 'text' => $elapsed . 'ms']]);
    }
    exit;
}

/**
 * 登录页线路延迟检测（无需登录）- 旧批量接口保留兼容
 */
if ($action == 'ping_lines_login') {
    header('Content-Type: application/json; charset=utf-8');
    $rounds = 3;
    $timeout = 5;
    $results = [];

    foreach (DC_LINE as $key => $line) {
        $url = rtrim($line['value'], '/') . '/api/dcshop.php?action=ping';
        $totalMs = 0;
        $failed = false;

        for ($r = 0; $r < $rounds; $r++) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . '&t=' . microtime(true) . '_' . $r);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'DCShop-PingCheck');

            $start = microtime(true);
            $resp = curl_exec($ch);
            $elapsed = (microtime(true) - $start) * 1000;
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($resp === false || $httpCode === 0) {
                $failed = true;
                break;
            }
            $totalMs += $elapsed;
        }

        if ($failed) {
            $results[$key] = ['ms' => -1, 'avg_ms' => -1, 'text' => '超时', 'status' => 'timeout'];
        } else {
            $avgMs = round($totalMs / $rounds);
            $results[$key] = [
                'ms'     => round($totalMs),
                'avg_ms' => $avgMs,
                'text'   => round($totalMs) . 'ms',
                'status' => 'ok',
            ];
        }
    }

    echo json_encode(['code' => 200, 'data' => $results]);
    exit;
}

/**
 * 登录页切换线路（无需登录）
 */
if ($action == 'update_line_login') {
    header('Content-Type: application/json; charset=utf-8');
    $line = isset($_POST['line']) ? (int)$_POST['line'] : -1;
    if (!isset(DC_LINE[$line])) {
        echo json_encode(['code' => 400, 'msg' => '无效线路']);
        exit;
    }
    Option::updateOption('dc_line', $line);
    $CACHE->updateCache('options');
    // 清除授权缓存
    if (class_exists('Register') && method_exists('Register', 'clearDemoCache')) {
        Register::clearDemoCache();
    }
    echo json_encode(['code' => 200, 'msg' => '切换成功']);
    exit;
}

if ($action == 'signin') {
    loginAuth::checkLogged();
    $login_code = Option::get('login_code') === 'y';
    $is_signup = Option::get('is_signup') === 'y';

    $page_title = '登录';
    require_once View::getAdmView('user_head');
    require_once View::getAdmView('signin');
    View::output();
}

if ($action == 'dosignin') {
    loginAuth::checkLogged();
    doAction('admin_login_submit');
    
    $username = Input::postStrVar('user');
    $password = Input::postStrVar('pw');
    $persist = Input::postIntVar('persist');
    $resp = Input::postStrVar('resp'); // eg: json (only support json now)

    // 极验已激活时跳过图形验证码（极验优先级 > 图形验证码）
    $geetest_active = function_exists('geetest_is_active') && geetest_is_active();
    if (!$geetest_active) {
        $login_code = Option::get('login_code') === 'y' && isset($_POST['login_code']) ? addslashes(strtoupper(trim($_POST['login_code']))) : '';
        if (!User::checkLoginCode($login_code)) {
            if ($resp === 'json') {
                Output::error('图形验证码错误');
            }
            emDirect('./account.php?action=signin&err_ckcode=1');
        }
    }
//    echo
    $uid = LoginAuth::checkUser($username, $password);
    switch ($uid) {
        case $uid > 0:
            // 移除授权检查
            // Register::isRegServer();
            $User_Model->updateUser(['ip' => getIp()], $uid);
            LoginAuth::setAuthCookie($username, $persist);
            doAction('admin_login_success', $uid);
            if ($resp === 'json') {
                Output::ok();
            }
            output::ok();
            break;
        case LoginAuth::LOGIN_ERROR_USER:
        case LoginAuth::LOGIN_ERROR_PASSWD:
            doAction('admin_login_failed', $username);
            Output::error('账号或密码错误');
            break;
    }
}



if ($action == 'send_email_code') {
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
    include View::getAdmView('user_head');
    require_once View::getAdmView('reset');
    View::output();
}

if ($action == 'doreset') {
    loginAuth::checkLogged();

    $mail = Input::postStrVar('mail');
    $login_code = strtoupper(Input::postStrVar('login_code'));
    $resp = Input::postStrVar('resp'); // eg: json (only support json now)

    // 极验已激活时跳过图形验证码
    $geetest_active = function_exists('geetest_is_active') && geetest_is_active();
    if (!$geetest_active) {
        if (!User::checkLoginCode($login_code)) {
            if ($resp === 'json') {
                Output::error('图形验证码错误');
            }
            emDirect('./account.php?action=reset&err_ckcode=1');
        }
    } else {
        // 极验服务端验证
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
    include View::getAdmView('user_head');
    require_once View::getAdmView('reset2');
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
    if ($resp === 'json') {
        Output::ok();
    }
    emDirect("./account.php?action=signin&succ_reset=1");
}

if ($action == 'logout') {
    setcookie(AUTH_COOKIE_NAME, ' ', time() - 31536000, '/');
    emDirect("./");
}
