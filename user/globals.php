<?php
/**
 * global
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

if(isset($user_cache[UID]['role'])){
	$role = $user_cache[UID]['role'];
	$role_name = User::getRoleName($role, UID);
}


loginAuth::checkLogin(NULL, 'user');

// 演示站检查：用户中心保留常规体验，只拦截删除、插件模板上传安装、备份下载、安装升级、导入导出等高风险操作。
if (Register::isDemoSite()) {
    $filename = Input::getStrVar('filename');
    if (function_exists('dcDemoRequestBlocked') && dcDemoRequestBlocked($action, $filename)) {
        Output::error('当前演示站禁止此操作哦！');
    }
}