<?php

require_once '../init.php';

$sta_cache = $CACHE->readCache('sta');
$user_cache = $CACHE->readCache('user');
$action = Input::getStrVar('action');

if(isset($user_cache[UID]['role'])){
	$role = $user_cache[UID]['role'];
	$role_name = User::getRoleName($role, UID);

    $user = [
        'nickname' => $user_cache[UID]['name'],
        'avatar' => User::getAvatar($user_cache[UID]['avatar']),
    ];
}

//d($role_name);die;

loginAuth::checkLogin(NULL, 'admin');

User::checkRolePermission();

// 演示站检查：只拦截删除、插件模板上传安装、备份下载、安装升级、导入导出等高风险操作，避免过度限制演示体验.
if (Register::isDemoSite()) {
    $filename = Input::getStrVar('filename');
    if (function_exists('dcDemoRequestBlocked') && dcDemoRequestBlocked($action, $filename)) {
        $demoAction = function_exists('dcDemoNormalizeAction') ? dcDemoNormalizeAction($action) : strtolower(str_replace('-', '_', trim((string)$action)));
        $demoHttpCode = preg_match('/(^|_)upload(_|$)/', $demoAction) ? 200 : 400;
        Output::error('演示站点无法进行该操作！', $demoHttpCode);
    }
}
