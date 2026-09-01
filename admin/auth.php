<?php
/**
 * 正版授权管理
 * 简化版：只检查域名授权状态，不需要输入授权码
 */

require_once 'globals.php';

$br = '<a href="./">数据中心</a><a><cite>正版授权</cite></a>';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $licenseKey = trim($_POST['license_key'] ?? '');
    $activateResult = Register::activateDomain($licenseKey, getTopHost());
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'code' => !empty($activateResult['success']) ? 0 : 1,
            'msg' => $activateResult['msg'] ?? '未知错误'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $_SESSION['admin_auth_activate_result'] = [
        'msg' => $activateResult['msg'] ?? '',
        'success' => !empty($activateResult['success'])
    ];
    header('Location: auth.php');
    exit;
}

$activateMsg = '';
$activateOk = false;
if (!empty($_SESSION['admin_auth_activate_result'])) {
    $activateMsg = $_SESSION['admin_auth_activate_result']['msg'] ?? '';
    $activateOk = !empty($_SESSION['admin_auth_activate_result']['success']);
    unset($_SESSION['admin_auth_activate_result']);
}

// 检查当前域名的授权状态（强制刷新，确保用户在授权中心绑定后能立即看到结果）
$authResult = Register::checkDomain(true);

include View::getAdmView('header');
require_once(View::getAdmView('auth'));
include View::getAdmView('footer');
View::output();
