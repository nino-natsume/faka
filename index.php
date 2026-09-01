<?php

require_once 'init.php';

// 路径后缀模式：/s/{slug} → 设置分店 cookie 并重定向到首页
$_reqUri = $_SERVER['REQUEST_URI'] ?? '';
$_basePath = parse_url(DC_URL, PHP_URL_PATH) ?: '/';
$_relPath = substr($_reqUri, strlen(rtrim($_basePath, '/')));
if (preg_match('#^/s/([a-zA-Z0-9_-]{1,50})(?:\?.*)?$#', $_relPath, $_slugMatch)) {
    $_slug = $_slugMatch[1];
    if (Option::get('station_slug_mode') === '1') {
        $_db = Database::getInstance();
        $_stRow = $_db->once_fetch_array("SELECT * FROM " . DB_PREFIX . "station WHERE slug = '" . addslashes($_slug) . "' AND delete_time IS NULL LIMIT 1");
        if ($_stRow) {
            if (array_key_exists('status', $_stRow) && (int)$_stRow['status'] === 0) {
                if (isset($_COOKIE['dc_station_slug'])) {
                    setcookie('dc_station_slug', '', time() - 1, '/');
                }
                if (function_exists('dcRenderStationDisabledPage')) {
                    dcRenderStationDisabledPage($_stRow['name'] ?? '');
                }
                header('HTTP/1.1 403 Forbidden');
                exit('当前分店已停用');
            }
            setcookie('dc_station_slug', $_slug, time() + 3600 * 24 * 30, '/');
            header('Location: ' . rtrim(DC_URL, '/') . '/');
            exit;
        }
    }
    // slug 无效或功能未开启，清除 cookie 继续正常流程
    if (isset($_COOKIE['dc_station_slug'])) {
        setcookie('dc_station_slug', '', time() - 1, '/');
    }
}

// 博客独立域名：访问域名根目录时直接进入博客首页，/page/{n} 映射到博客分页
if (defined('IS_BLOG_DOMAIN') && IS_BLOG_DOMAIN) {
    $_blogReqUri = $_SERVER['REQUEST_URI'] ?? '/';
    $_blogBasePath = parse_url(DC_URL, PHP_URL_PATH) ?: '/';
    $_blogBasePrefix = rtrim($_blogBasePath, '/');
    $_blogRelUri = $_blogReqUri;
    if ($_blogBasePrefix !== '' && strpos($_blogRelUri, $_blogBasePrefix) === 0) {
        $_blogRelUri = substr($_blogRelUri, strlen($_blogBasePrefix));
    }
    if ($_blogRelUri === '') {
        $_blogRelUri = '/';
    }
    $_blogUriParts = parse_url($_blogRelUri);
    $_blogPath = $_blogUriParts['path'] ?? '/';
    $_blogQuery = isset($_blogUriParts['query']) && $_blogUriParts['query'] !== '' ? '?' . $_blogUriParts['query'] : '';
    if ($_blogPath === '' || $_blogPath === '/' || strcasecmp($_blogPath, '/index.php') === 0) {
        $_blogQueryParams = [];
        if ($_blogQuery !== '') {
            parse_str(ltrim($_blogQuery, '?'), $_blogQueryParams);
        }
        // 仅根目录访问、index.php 无参访问、或纯分页参数进入博客首页。
        // ?blog=、?keyword=、?action=cal 等动态路由必须保留原始请求，避免文章详情、搜索、日历 AJAX 被误导到博客列表。
        if ($_blogQuery === '' || (count($_blogQueryParams) === 1 && isset($_blogQueryParams['page']))) {
            $_SERVER['REQUEST_URI'] = $_blogBasePrefix . '/blog' . $_blogQuery;
        }
    } elseif (preg_match('#^/page/(\d+)/?$#', $_blogPath, $_blogPageMatch)) {
        $_SERVER['REQUEST_URI'] = $_blogBasePrefix . '/blog/page/' . $_blogPageMatch[1] . $_blogQuery;
    }
}


doAction('init');

$emDispatcher = Dispatcher::getInstance();


$emDispatcher->dispatch();



View::output();

