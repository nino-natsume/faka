<?php

defined('DC_ROOT') || exit('access denied!');
require_once View::getView('module');

$version = '1720327727';

$q = Input::getStrVar('q');

// 获取头部按钮显示设置（PC端）
$_header_menu_show = _g('header_menu_show') ?: 'y';
$_header_search_show = _g('header_search_show') ?: 'y';
$_header_user_show = _g('header_user_show') ?: 'y';
$_header_order_show = _g('header_order_show') ?: 'y';
$_header_help_show = _g('header_help_show') ?: 'y';

// 获取头部按钮显示设置（移动端）
$_mobile_menu_show = _g('mobile_menu_show') ?: 'y';
$_mobile_search_show = _g('mobile_search_show') ?: 'y';
$_mobile_user_show = _g('mobile_user_show') ?: 'n';
$_mobile_help_show = _g('mobile_help_show') ?: 'y';

$_footer_show = _g('footer_show') ?: 'y';
$_single_page_mode = _g('single_page_mode');
$_current_template_slug = basename(rtrim(str_replace('\\', '/', TEMPLATE_PATH), '/'));
$_is_fakacopy_template = $_current_template_slug === 'default';
$_site_subtitle = !empty($GLOBALS['stationData']['site_subtitle']) ? $GLOBALS['stationData']['site_subtitle'] : (Option::get('site_subtitle') ?: '');

?>
<!doctype html>
<html lang="zh-cn" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, maximum-scale=1, user-scalable=no">
    <title><?= $site_title ?></title>
    <meta name="keywords" content="<?= $site_key ?>"/>
    <meta name="description" content="<?= $site_description ?>"/>
    <link href="<?= empty(_g('favicon')) ? getSiteFaviconUrl(DC_URL . 'favicon.ico') : _g('favicon'); ?>" rel="icon">
    <link rel="alternate" title="RSS" href="<?= DC_URL ?>rss.php" type="application/rss+xml"/>
    <!-- 头部按钮和底部信息显示控制 -->
    <style id="header-visibility-control">
    /* 全站背景 */
    body {
        background: radial-gradient(circle at 12% 55%, rgba(33, 150, 243, 0.15), rgba(255, 255, 255, 0) 25%), radial-gradient(circle at 85% 33%, rgba(108, 99, 255, 0.176), rgba(255, 255, 255, 0) 25%) rgb(255, 255, 255) !important;
        min-height: 100vh;
    }
    
    /* ========== PC端显示控制 (769px以上) ========== */
    @media (min-width: 769px) {
        <?php if($_header_menu_show != 'y'): ?>.m-btn, #m-btn { display: none !important; }<?php endif; ?>
        <?php if($_header_search_show != 'y'): ?>.search { display: none !important; }<?php endif; ?>
        <?php if($_header_user_show != 'y'): ?>.header-user { display: none !important; }<?php endif; ?>
        <?php if($_header_order_show != 'y'): ?>.header-search-order-btn { display: none !important; }<?php endif; ?>
        <?php if($_header_help_show != 'y'): ?>.header-help-mobile { display: none !important; }<?php endif; ?>
    }
    
    /* ========== 移动端显示控制 (768px以下) ========== */
    @media (max-width: 768px) {
        <?php if($_mobile_menu_show != 'y'): ?>.m-btn, #m-btn { display: none !important; }<?php endif; ?>
        <?php if($_mobile_search_show != 'y'): ?>.search { display: none !important; }<?php endif; ?>
        <?php if($_mobile_user_show != 'y'): ?>.header-user { display: none !important; }<?php endif; ?>
        <?php if($_mobile_help_show != 'y'): ?>.header-help-mobile { display: none !important; }<?php endif; ?>
    }
    
    /* ========== 底部显示控制 ========== */
    <?php if($_footer_show != 'y'): ?>.main-footer { display: none !important; }<?php endif; ?>
    </style>

    <!-- <link rel="stylesheet" href="../../../admin/views/css/bootstrap.min.css"> -->

    <script src="<?= DC_URL ?>admin/views/js/jquery.min.3.5.1.js"></script>
    <!-- <script src="<?= DC_URL ?>admin/views/js/bootstrap.bundle.min.4.6.js?t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script> -->
    <!-- 字体 -->
    <link rel="stylesheet" type="text/css" href="<?= DC_URL ?>admin/views/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?= DC_URL ?>admin/views/remixicon/remixicon.css">
    <link rel="stylesheet" href="<?= DC_URL ?>admin/views/layui-v2.11.6/layui/css/layui.css">
    <script src="<?= DC_URL ?>admin/views/layui-v2.11.6/layui/layui.js"></script>
    


    <!-- <script src="<?= TEMPLATE_URL ?>js/zoom.js?v=<?= $version ?>&t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script> -->

    <link rel="stylesheet" href="<?= DC_URL ?>content/common/header.css?t=<?= Option::DC_VERSION_TIMESTAMP ?>">
    <script src="<?= DC_URL ?>content/common/header.js?t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>


    <link rel="stylesheet" href="<?= DC_URL ?>content/static/css/em.css?t=<?= Option::DC_VERSION_TIMESTAMP ?>">
    <link href="<?= TEMPLATE_URL ?>css/style.css?t=<?= Option::DC_VERSION_TIMESTAMP ?>" rel="stylesheet"/>
    <!-- 主题配色CSS -->
    <link href="<?= TEMPLATE_URL ?>css/theme.php?v=<?= time() ?>" rel="stylesheet"/>


    <style>
        /* Flex Sticky Footer 实现 */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        /* 移动端适配 (992px以下 Header变更为Fixed定位，需要Padding占位) */
        @media (max-width: 768px) {
            body {
                /* padding-top: 56px; 移动端 Header 高度 */
            }
        }

        /* 主内容区域自动填充剩余空间 */
        main, .blog-container, .container {
            flex: 1 0 auto;
        }
        /* 页脚不伸缩 */
        footer, .main-footer {
            flex-shrink: 0;
        }
    </style>
    <?php doAction('index_head') ?>

    <?php if(Option::get('login_switch') == 'n' || Option::get('register_switch') == 'n'): ?>
    <style>
        .m-btn {
            right: 50px;
        }
    </style>
    <?php endif; ?>
    
    <?php if($_single_page_mode === 'y'): ?>
    <!-- 单页模式头部样式 - 蓝色背景白色文字 -->
    <style id="single-page-header-style">
        .h-fix { background: #0c6be1 !important; }
        .logo-brand .brand-title { color: #fff !important; }
        .logo-brand .brand-subtitle { color: rgba(255,255,255,0.8) !important; }
        .header .nav-bar li a { color: #fff !important; }
        .header .nav-bar li a:hover { color: #fff !important; background: rgba(255,255,255,0.15) !important; }
        .search i.fa, .header-user i.fa, .m-btn i.fa { color: #fff !important; }
        .header-search-order-btn .a { background-color: rgba(255,255,255,0.2) !important; }
        .header-help-btn { border-color: rgba(255,255,255,0.5) !important; color: #fff !important; }
    </style>
    <?php endif; ?>

    <?php
    // 商城头部自定义样式
    $shop_header_bg = _g('shop_header_bg') ?: '';
    $shop_title_color = _g('shop_title_color') ?: '';
    $shop_subtitle_color = _g('shop_subtitle_color') ?: '';
    $shop_nav_active_color = _g('shop_nav_active_color') ?: '';
    $shop_nav_active_bg = _g('shop_nav_active_bg') ?: '';
    if (!empty($shop_header_bg) || !empty($shop_title_color) || !empty($shop_subtitle_color) || !empty($shop_nav_active_color) || !empty($shop_nav_active_bg)):
    ?>
    <style id="shop-header-custom-style">
        <?php if (!empty($shop_header_bg)): ?>
        .h-fix { background: <?= htmlspecialchars($shop_header_bg) ?> !important; }
        .header .nav-bar li a { color: #fff !important; }
        .header .nav-bar li a:hover { background: rgba(255,255,255,0.15) !important; }
        .search i.fa, .header-user i.fa, .m-btn i.fa { color: #fff !important; }
        .header-search-order-btn .a { background-color: rgba(255,255,255,0.2) !important; }
        .header-help-btn { border-color: rgba(255,255,255,0.5) !important; color: #fff !important; }
        <?php endif; ?>
        <?php if (!empty($shop_title_color)): ?>
        .logo-brand .brand-title { color: <?= htmlspecialchars($shop_title_color) ?> !important; }
        <?php endif; ?>
        <?php if (!empty($shop_subtitle_color)): ?>
        .logo-brand .brand-subtitle { color: <?= htmlspecialchars($shop_subtitle_color) ?> !important; }
        <?php endif; ?>
        <?php if (!empty($shop_nav_active_color)): ?>
        .header .nav-bar li.active > a, .header .nav-bar li a.active { color: <?= htmlspecialchars($shop_nav_active_color) ?> !important; }
        <?php endif; ?>
        <?php if (!empty($shop_nav_active_bg)): ?>
        .header .nav-bar li.active > a, .header .nav-bar li a.active { background: <?= htmlspecialchars($shop_nav_active_bg) ?> !important; border-radius: 4px; }
        <?php endif; ?>
    </style>
    <?php endif; ?>
<?php $_fk_bg_video = _g('bg_video_url') ?: ''; ?>
<?php $_fk_bg_video_mobile_show = _g('bg_video_mobile_show') === 'y'; ?>
<?php $_fk_enable_bg_video = !empty($_fk_bg_video) && (!isMobile() || $_fk_bg_video_mobile_show); ?>
<?php if ($_fk_enable_bg_video): ?>
<style id="fk-video-bg-style">
body { background: transparent !important; }
#fk-bg-video { position: fixed; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: -1; pointer-events: none; }
</style>
<?php endif; ?>
<?php if ($_is_fakacopy_template): ?>
<script>
window.__fkPullRefreshBound = true;
</script>
<?php endif; ?>
<?php if (defined('DC_FORCE_MOBILE_TEMPLATE') && DC_FORCE_MOBILE_TEMPLATE): ?>
<style id="dc-mobile-shell-hide-scrollbar">
html,
body,
* {
    scrollbar-width: none !important;
    -ms-overflow-style: none !important;
}
html::-webkit-scrollbar,
body::-webkit-scrollbar,
*::-webkit-scrollbar {
    width: 0 !important;
    height: 0 !important;
    display: none !important;
    background: transparent !important;
}
html::-webkit-scrollbar-track,
body::-webkit-scrollbar-track,
*::-webkit-scrollbar-track,
html::-webkit-scrollbar-thumb,
body::-webkit-scrollbar-thumb,
*::-webkit-scrollbar-thumb,
html::-webkit-scrollbar-corner,
body::-webkit-scrollbar-corner,
*::-webkit-scrollbar-corner {
    display: none !important;
    background: transparent !important;
}
</style>
<?php endif; ?>
</head>
<body>
<?php if ($_current_template_slug === 'yifaka' || $_current_template_slug === 'cardstyle') { return; } ?>
<?php if ($_fk_enable_bg_video): ?>
<video id="fk-bg-video" autoplay muted loop playsinline preload="none" disablepictureinpicture>
    <source src="<?= htmlspecialchars($_fk_bg_video) ?>" type="video/mp4">
</video>
<?php endif; ?>

<div id="mask"></div>
<header class="header">
    <div class="h-fix">
        <div class="container">
            <h1 class="logo-brand">
                <a href="<?= DC_URL ?>" title="<?= $blogname ?>">
                    <?php 
                    $site_logo = Option::get('logo');
                    $logo_url = !empty($site_logo) ? $site_logo : DC_URL . 'admin/views/images/logo.apng';
                    ?>
                    <img class="brand-logo" src="<?= $logo_url ?>" alt="<?= $blogname ?>">
                    <div class="brand-text">
                        <span class="brand-title"><?= $blogname ?></span>
                        <?php if(!empty($_site_subtitle)): ?>
                        <span class="brand-subtitle"><?= $_site_subtitle ?></span>
                        <?php endif; ?>
                    </div>
                </a>
            </h1>
            <div class="nav-container">
                <nav class="nav-bar" id="nav-box" data-type="none" data-infoid="">
                    <ul class="nav"><?php blog_navi() ?></ul>
                </nav>
            </div>
            <div class="header-right">
                <div class="header-right-btn">
                    <div class="search">
                        <i class="s-btn off fa fa-search" onclick="toggleHeaderSearch()"></i>
                        <form class="header-search-expand" id="headerSearchExpand" action="" method="get">
                            <input type="text" name="q" class="header-search-input" placeholder="搜索商品关键词" id="headerSearchInput">
                            <button type="submit" class="header-search-submit"><i class="fa fa-search"></i></button>
                        </form>
                        <i class="fa fa-times header-search-close" onclick="toggleHeaderSearch()"></i>
                    </div>
                    <?php if(Option::get('login_switch') == 'y'): ?>
                    <div class="header-user">
                        <a href="<?= DC_URL ?>user/" class="">
                            <i class="fa fa-user"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                    <div class="header-search-order-btn">
                        <a href="<?= DC_URL ?>?action=order_query" class="a transition">查询订单</a>
                    </div>
                    <div class="header-help-mobile">
                        <a href="<?= DC_URL ?>?action=help" class="header-help-btn">买家帮助</a>
                    </div>
                    <div id="m-btn" class="m-btn"><i class="fa fa-bars"></i></div>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
function toggleHeaderSearch() {
    var header = document.querySelector('.header');
    var input = document.getElementById('headerSearchInput');
    if (header.classList.contains('search-active')) {
        header.classList.remove('search-active');
        input.value = '';
    } else {
        header.classList.add('search-active');
        setTimeout(function(){ input.focus(); }, 300);
    }
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var header = document.querySelector('.header');
        if (header && header.classList.contains('search-active')) toggleHeaderSearch();
    }
});
document.addEventListener('click', function(e) {
    var header = document.querySelector('.header');
    if (header && header.classList.contains('search-active')) {
        var box = document.getElementById('headerSearchExpand');
        var btn = document.querySelector('.search .s-btn');
        var close = document.querySelector('.header-search-close');
        if (!box.contains(e.target) && e.target !== btn && e.target !== close) toggleHeaderSearch();
    }
});
</script>

<style>
.header-search-expand {
    position: absolute;
    right: 0; top: 50%;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    gap: 6px;
    width: 0;
    opacity: 0;
    overflow: hidden;
    background: #fff;
    border-radius: 24px;
    box-shadow: 0 4px 16px rgba(0,0,0,.1);
    padding: 0;
    transition: width .3s ease, opacity .25s ease, padding .3s ease;
    white-space: nowrap;
    z-index: 10;
}
.search-active .header-search-expand {
    width: 340px;
    opacity: 1;
    padding: 5px 5px 5px 16px;
}
.search-active .s-btn { display: none; }
.header-search-input {
    flex: 1;
    min-width: 0;
    height: 34px;
    border: none;
    outline: none;
    background: transparent;
    font-size: 14px;
    color: #333;
}
.header-search-input::placeholder { color: #aaa; }
.header-search-submit {
    width: 34px; height: 34px;
    flex-shrink: 0;
    border: none;
    border-radius: 50%;
    background: var(--theme-primary, #4C7D71);
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: opacity .2s;
}
.header-search-submit:hover { opacity: .85; }
.header-search-expand i.fa {
    width: auto !important; height: auto !important;
    line-height: 1 !important; display: inline !important;
    opacity: 1 !important; padding: 0 !important;
    background: none !important; border-radius: 0 !important;
}
.header-search-submit i.fa { color: #fff !important; font-size: 14px !important; }
.header-search-close {
    display: none !important;
    width: 28px; height: 28px;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #999 !important;
    font-size: 14px !important;
    border-radius: 50%;
    transition: .15s;
    opacity: 1 !important;
    line-height: 1 !important;
    padding: 0 !important;
    background: none !important;
    margin-left: 2px;
}
.search-active .search .header-search-close { display: flex !important; }
.header-search-close:hover { background: rgba(0,0,0,.06) !important; color: #333 !important; }

.search-active .logo-brand,
.search-active .nav-container { transition: opacity .2s; }
@media (max-width: 768px) {
    .search-active .logo-brand { opacity: 0; pointer-events: none; position: absolute; }
    .search-active .nav-container { display: none; }
    .search-active .header-search-expand { width: calc(100vw - 180px); }
}
@media (max-width: 480px) {
    .search-active .header-search-expand { width: calc(100vw - 120px); }
}

html[data-theme="dark"] .header-search-expand { background: #1e1e1e; box-shadow: 0 4px 16px rgba(0,0,0,.3); }
html[data-theme="dark"] .header-search-input { color: #e0e0e0; }
html[data-theme="dark"] .header-search-input::placeholder { color: #888; }
html[data-theme="dark"] .header-search-close { color: #888; }
html[data-theme="dark"] .header-search-close:hover { background: #333; color: #e0e0e0; }
</style>

