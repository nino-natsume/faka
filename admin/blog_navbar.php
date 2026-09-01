<?php
/**
 * 博客导航接口（已整合到博客模板配置页）
 * @package DCSHOP
 */

require_once 'globals.php';

$Blog_Navi_Model = new Blog_Navi_Model();

if (!function_exists('blogNavbarTypeName')) {
    function blogNavbarTypeName($type) {
        switch ((int)$type) {
            case Blog_Navi_Model::navitype_home:
            case Blog_Navi_Model::navitype_blog:
                return '系统';
            case Blog_Navi_Model::navitype_blogsort:
                return '文章分类';
            case Blog_Navi_Model::navitype_page:
                return '页面';
            case Blog_Navi_Model::navitype_custom:
            default:
                return '自定义';
        }
    }
}

if (empty($action)) {
    header('Location: ./template.php');
    exit;
}

// 获取导航原始列表（AJAX - 用于模板配置页列表式管理）
if ($action == 'list') {
    $navis = $Blog_Navi_Model->getAllNavisRaw();
    foreach ($navis as &$navi) {
        $navi['type_name'] = blogNavbarTypeName($navi['type']);
    }
    unset($navi);
    Output::data($navis, count($navis));
}

// 保存列表式导航配置
if ($action == 'save_list') {
    LoginAuth::checkToken();
    $ids = isset($_POST['nav_id']) && is_array($_POST['nav_id']) ? $_POST['nav_id'] : [];
    $names = isset($_POST['nav_name']) && is_array($_POST['nav_name']) ? $_POST['nav_name'] : [];
    $urls = isset($_POST['nav_url']) && is_array($_POST['nav_url']) ? $_POST['nav_url'] : [];
    $icons = isset($_POST['nav_ri']) && is_array($_POST['nav_ri']) ? $_POST['nav_ri'] : [];
    $pids = isset($_POST['nav_pid']) && is_array($_POST['nav_pid']) ? $_POST['nav_pid'] : [];
    $hides = isset($_POST['nav_hide']) && is_array($_POST['nav_hide']) ? $_POST['nav_hide'] : [];
    $newtabs = isset($_POST['nav_newtab']) && is_array($_POST['nav_newtab']) ? $_POST['nav_newtab'] : [];

    $currentNavis = $Blog_Navi_Model->getAllNavisRaw();
    $currentIds = [];
    $currentMap = [];
    foreach ($currentNavis as $navi) {
        $currentId = (int)$navi['id'];
        $currentIds[] = $currentId;
        $currentMap[$currentId] = $navi;
    }

    $postedExistingIds = [];
    $total = count($names);
    for ($i = 0; $i < $total; $i++) {
        $id = isset($ids[$i]) ? (int)$ids[$i] : 0;
        if ($id > 0) {
            if (!isset($currentMap[$id])) {
                Output::error('第 ' . ($i + 1) . ' 行导航不存在，请刷新后重试');
            }
            $postedExistingIds[$id] = true;
        }
    }

    $keepIds = [];
    for ($i = 0; $i < $total; $i++) {
        $id = isset($ids[$i]) ? (int)$ids[$i] : 0;
        $name = trim((string)($names[$i] ?? ''));
        $url = trim((string)($urls[$i] ?? ''));
        $pid = isset($pids[$i]) ? (int)$pids[$i] : 0;
        $naviicon = trim((string)($icons[$i] ?? ''));
        $hide = (($hides[$i] ?? 'n') === 'y') ? 'y' : 'n';
        $newtab = (($newtabs[$i] ?? 'n') === 'y') ? 'y' : 'n';
        $taxis = max(0, ($total - $i) * 10);

        if ($name === '') {
            Output::error('第 ' . ($i + 1) . ' 行导航名称不能为空');
        }
        if ($pid < 0 || $pid === $id || ($pid > 0 && (!isset($currentMap[$pid]) || !isset($postedExistingIds[$pid])))) {
            $pid = 0;
        }

        if ($id > 0) {
            $keepIds[] = $id;
            $Blog_Navi_Model->updateNavi([
                'naviname' => addslashes($name),
                'url' => addslashes($url),
                'pid' => $pid,
                'newtab' => $newtab,
                'hide' => $hide,
                'taxis' => $taxis,
                'naviicon' => addslashes($naviicon),
            ], $id);
        } else {
            $Blog_Navi_Model->addNavi(addslashes($name), addslashes($url), $taxis, $pid, $newtab, Blog_Navi_Model::navitype_custom, 0, addslashes($naviicon));
        }
    }

    foreach ($currentIds as $currentId) {
        if (!in_array($currentId, $keepIds, true)) {
            $Blog_Navi_Model->deleteNavi($currentId);
        }
    }

    $CACHE->updateCache('blog_navi');
    Output::ok();
}

// 添加自定义导航
if ($action == 'add') {
    LoginAuth::checkToken();
    $name = Input::postStrVar('naviname');
    $url = Input::postStrVar('url');
    $pid = Input::postIntVar('pid');
    $newtab = Input::postStrVar('newtab', 'n') === 'y' ? 'y' : 'n';
    $naviicon = Input::postStrVar('naviicon', '');
    $taxis = Input::postIntVar('taxis');

    if (empty($name)) {
        Output::error('导航名称不能为空');
    }
    if ($pid < 0 || ($pid > 0 && empty($Blog_Navi_Model->getOneNavi($pid)))) {
        $pid = 0;
    }

    $Blog_Navi_Model->addNavi(addslashes($name), addslashes($url), $taxis, $pid, $newtab, Blog_Navi_Model::navitype_custom, 0, addslashes($naviicon));
    $CACHE->updateCache('blog_navi');
    Output::ok();
}

// 添加文章分类到导航
if ($action == 'add_sort') {
    LoginAuth::checkToken();
    $sort_ids = isset($_POST['sort_ids']) ? $_POST['sort_ids'] : [];
    $blog_sorts = $CACHE->readCache('blog_sort');
    if (!is_array($sort_ids)) {
        $sort_ids = [];
    }
    if (!is_array($blog_sorts)) {
        $blog_sorts = [];
    }

    foreach ($sort_ids as $val) {
        $sort_id = (int)$val;
        if (isset($blog_sorts[$sort_id]) && !$Blog_Navi_Model->naviExists(Blog_Navi_Model::navitype_blogsort, $sort_id)) {
            $sorticon = isset($blog_sorts[$sort_id]['sorticon']) ? trim((string)$blog_sorts[$sort_id]['sorticon']) : '';
            $Blog_Navi_Model->addNavi(addslashes($blog_sorts[$sort_id]['sortname']), '', 0, 0, 'n', Blog_Navi_Model::navitype_blogsort, $sort_id, addslashes($sorticon));
        }
    }
    $CACHE->updateCache('blog_navi');
    Output::ok();
}

// 添加页面到导航
if ($action == 'add_page') {
    LoginAuth::checkToken();
    $pages_data = isset($_POST['pages']) ? $_POST['pages'] : [];
    if (!is_array($pages_data)) {
        $pages_data = [];
    }
    $Log_Model = new Log_Model();
    
    foreach ($pages_data as $page_id => $page_title) {
        $page_id = (int)$page_id;
        $page = $Log_Model->getOneLogForAdmin($page_id);
        if (!$page || ($page['type'] ?? '') !== 'page') {
            continue;
        }
        if (!$Blog_Navi_Model->naviExists(Blog_Navi_Model::navitype_page, $page_id)) {
            $Blog_Navi_Model->addNavi(addslashes($page['title']), '', 0, 0, 'n', Blog_Navi_Model::navitype_page, $page_id);
        }
    }
    $CACHE->updateCache('blog_navi');
    Output::ok();
}

Output::error('接口不存在');
