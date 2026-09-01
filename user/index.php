<?php

require_once '../init.php';

$sta_cache = $CACHE->readCache('sta');
$user_cache = $CACHE->readCache('user');
$action = Input::getStrVar('action');

// 移动端个人中心首页允许未登录访问（像真实 App 一样）
if (empty($action) && isMobile()) {
    // 不强制登录，模板内自行区分登录态
    global $userData;
    $uc_app_mode = true;

    include View::getUserView('_adaptive_header');
    require_once(View::getUserView('adaptive_index_mobile'));
    include View::getUserView('_adaptive_footer');

    View::output();
    exit;
}

// 其余场景需要登录
loginAuth::checkLogin(NULL, 'user');

if(isset($user_cache[UID]['role'])){
    $role = $user_cache[UID]['role'];
    $role_name = User::getRoleName($role, UID);
}

if (empty($action)) {
    global $userData;
    $uc_app_mode = isMobile();

    // 直接使用自适应布局，自动适配当前模板
    include View::getUserView('_adaptive_header');
    if (isMobile()) {
        require_once(View::getUserView('adaptive_index_mobile'));
    } else {
        require_once(View::getUserView('adaptive_index_pc'));
    }
    include View::getUserView('_adaptive_footer');
    
    View::output();
}

if ($action == 'upload_cover') {
    $Media_Model = new Media_Model();
    $ret = uploadCropImg();
    $Media_Model->addMedia($ret['file_info']);
    Output::ok($ret['file_info']);
}