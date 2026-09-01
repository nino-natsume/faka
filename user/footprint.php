<?php
/**
 * 用户浏览足迹
 */
require_once 'globals.php';

$footprintModel = new User_Goods_Footprint_Model();

if ($action === 'delete') {
    if (!LoginAuth::checkAjaxToken()) {
        Ret::error('安全校验失败，请刷新页面重试');
    }
    $id = Input::postIntVar('id');
    if ($id <= 0) {
        Ret::error('请选择要删除的足迹');
    }
    $footprintModel->deleteOne(UID, $id);
    Ret::success('已删除');
}

if ($action === 'clear') {
    if (!LoginAuth::checkAjaxToken()) {
        Ret::error('安全校验失败，请刷新页面重试');
    }
    $footprintModel->clear(UID);
    Ret::success('已清空');
}

$page = max(1, Input::getIntVar('page', 1));
$perPage = isMobile() ? 10 : 12;
$total = $footprintModel->getCount(UID);
$footprintSummary = $footprintModel->getSummary(UID);
$footprintList = $footprintModel->getList(UID, $page, $perPage);
$totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
$footprintToken = LoginAuth::genToken();

$uc_app_mode = isMobile();
$uc_page_title = '浏览足迹';

include View::getUserView('_adaptive_header');
require_once View::getUserView(isMobile() ? 'adaptive_footprint_mobile' : 'adaptive_footprint');
include View::getUserView('_adaptive_footer');
View::output();
