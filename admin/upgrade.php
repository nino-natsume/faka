<?php
/**
 * 系统升级管理（已禁用 - 在线更新功能已关闭）
 */

require_once 'globals.php';

$action = $_GET['action'] ?? '';

// 所有更新检查接口始终返回"无更新"
if ($action === 'check_update' || $action === 'changelog') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => 200,
        'msg'  => '当前已是最新版本',
        'data' => [
            'has_update'    => false,
            'is_force'      => false,
            'is_authorized' => true,
            'latest_version' => Option::DC_VERSION,
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$br = '<a href="./">数据中心</a><a><cite>系统升级</cite></a>';
$currentVersion = Option::DC_VERSION;

include View::getAdmView('header');
require_once(View::getAdmView('upgrade'));
include View::getAdmView('footer');
View::output();
