<?php
/**
 * 自适应个人中心头部
 * 根据当前使用的模板自动适配样式
 */
defined('DC_ROOT') || exit('access denied!');

// 获取当前模板
$shop_name = '';
if (!empty($stationData['id']) && !empty($stationData['name'])) {
    $shop_name = $stationData['name'];
} elseif (!empty($userData['station']['name'])) {
    $shop_name = $userData['station']['name'];
}
$shop_name = $shop_name ?: (Option::get('blogname') ?: 'DCSHOP');
$logo = Option::get('logo') ?: DC_URL . 'admin/views/images/logo.apng';

// 检测模板类型
$uc_app_mode = !empty($uc_app_mode);
$_is_mobile = isMobile();
$_use_mobile_shell = $_is_mobile && $uc_app_mode;

if (!function_exists('dcDefaultUserBottomNavHidden')) {
    function dcDefaultUserBottomNavHidden($ucAppMode, $ucShowBottomNav, $ucHideBottomNav, $ucPageTitle = null) {
        if (!empty($ucShowBottomNav)) {
            return false;
        }
        $hidden = !empty($ucHideBottomNav);
        if (isMobile() && !empty($ucAppMode)) {
            // default 用户模板的移动端 App 二级页通常带顶部返回栏，不再叠加底部导航。
            // 若个别页面仍需显示，可在 include header 前设置 $uc_show_bottom_nav = true。
            $_uc_mobile_page_title = trim((string)$ucPageTitle);
            $_uc_request_uri = $_SERVER['REQUEST_URI'] ?? '';
            $_uc_request_path = (string)(parse_url((string)$_uc_request_uri, PHP_URL_PATH) ?: '');
            $_uc_request_path = rtrim($_uc_request_path, '/');
            $_uc_request_path = $_uc_request_path === '' ? '/' : $_uc_request_path;
            $_uc_is_user_home = preg_match('#(?:^|/)user(?:/index\.php)?$#i', $_uc_request_path) === 1;
            if ($_uc_mobile_page_title !== '' && !$_uc_is_user_home) {
                $hidden = true;
            }
        }
        return $hidden;
    }
}

// 手机端自动检测页面标题
if ($_is_mobile && !isset($uc_page_title)) {
    $_uri = $_SERVER['REQUEST_URI'] ?? '';

    if (strpos($_uri, 'balance.php') !== false) {
        if (strpos($_uri, 'withdraw_index') !== false) $uc_page_title = '提现记录';
        else $uc_page_title = '我的钱包';
    } elseif (strpos($_uri, 'level.php') !== false) {
        $uc_page_title = '会员等级';
    } elseif (strpos($_uri, 'order.php') !== false) {
        $uc_page_title = '订单记录';
    } elseif (strpos($_uri, 'footprint.php') !== false) {
        $uc_page_title = '浏览足迹';
    } elseif (strpos($_uri, 'visitors.php') !== false) {
        if (strpos($_uri, 'get_visitors_order') !== false) $uc_page_title = '查询结果';
        else $uc_page_title = '游客查单';
    } elseif (strpos($_uri, 'station.php') !== false) {
        if (strpos($_uri, 'action=open') !== false) $uc_page_title = !empty($userData['station']) ? '升级分店' : '开通分店';
        elseif (strpos($_uri, 'action=setting') !== false) $uc_page_title = '店铺配置';
        elseif (strpos($_uri, 'action=master_goods') !== false) $uc_page_title = '商品管理';
        elseif (strpos($_uri, 'action=order') !== false) $uc_page_title = '商品订单';
        else $uc_page_title = '店铺概览';
    }
}

$uc_show_bottom_nav = !empty($uc_show_bottom_nav);
$uc_hide_bottom_nav = dcDefaultUserBottomNavHidden($uc_app_mode, $uc_show_bottom_nav, !empty($uc_hide_bottom_nav), $uc_page_title ?? '');
$uc_bottom_nav_rendered = false;

// 获取模板配置
$template_config = [];
if ($is_anime2d) {
    $tpl_storage = Storage::getInstance('anime2d');
    $template_config = [
        'theme_primary' => $tpl_storage->getValue('theme_primary') ?: '#ff6b9d',
        'theme_secondary' => $tpl_storage->getValue('theme_secondary') ?: '#c44569',
        'enable_particles' => $tpl_storage->getValue('enable_particles') ?: 'sparkle'
    ];
    $template_config['enable_theme_switcher'] = $tpl_storage->getValue('enable_theme_switcher') ?: 'n';
    $_gb = $tpl_storage->getValue('glass_blur');
    $_gb = ($_gb !== false && $_gb !== '') ? intval($_gb) : 70;
    $template_config['glass_blur'] = round($_gb * 20 / 100);
} elseif (!empty($is_default_tpl) || basename(rtrim(str_replace('\\', '/', TEMPLATE_PATH), '/')) === 'default') {
    $tpl_options = TplOptions::getInstance();
    $_default_tpl_config = $tpl_options->getTemplateOptions('front_default');
    $template_config = [
        'theme_primary' => $_default_tpl_config['theme_primary'] ?? '#2196F3',
        'theme_price' => $_default_tpl_config['theme_price'] ?? '#ff6600',
        'theme_button' => $_default_tpl_config['theme_button'] ?? '#2f69d9',
        'theme_accent' => $_default_tpl_config['theme_accent'] ?? '#ff9800'
    ];
}

// 读取默认用户模板自身的配置（admin > 外观设置 > 用户后台模板 > 配置）
$_user_tpl_options = TplOptions::getInstance()->getTemplateOptions(userTplSettingKey('default'));
$_ut = [
    'theme_primary'         => !empty($_user_tpl_options['theme_primary'])   ? $_user_tpl_options['theme_primary']   : '#2196F3',
    'theme_secondary'       => !empty($_user_tpl_options['theme_secondary']) ? $_user_tpl_options['theme_secondary'] : '#2f69d9',
    'theme_accent'          => !empty($_user_tpl_options['theme_accent'])    ? $_user_tpl_options['theme_accent']    : '#ff9800',
    'header_bg'             => isset($_user_tpl_options['header_bg']) && $_user_tpl_options['header_bg'] !== '' ? $_user_tpl_options['header_bg'] : '',
    'header_title_color'    => isset($_user_tpl_options['header_title_color']) && $_user_tpl_options['header_title_color'] !== '' ? $_user_tpl_options['header_title_color'] : '',
    'header_subtitle_color' => isset($_user_tpl_options['header_subtitle_color']) && $_user_tpl_options['header_subtitle_color'] !== '' ? $_user_tpl_options['header_subtitle_color'] : '',
    'sidebar_mac_dots'      => !empty($_user_tpl_options['sidebar_mac_dots'])  ? $_user_tpl_options['sidebar_mac_dots']  : 'y',
    'show_order_icon'       => !empty($_user_tpl_options['show_order_icon'])   ? $_user_tpl_options['show_order_icon']   : 'y',
    'show_bell_icon'        => !empty($_user_tpl_options['show_bell_icon'])    ? $_user_tpl_options['show_bell_icon']    : 'y',
    'show_service_icon'     => !empty($_user_tpl_options['show_service_icon']) ? $_user_tpl_options['show_service_icon'] : 'y',
    'custom_css'            => isset($_user_tpl_options['custom_css']) ? $_user_tpl_options['custom_css'] : '',
    'pc_card_gradient'      => !empty($_user_tpl_options['pc_card_gradient']) ? $_user_tpl_options['pc_card_gradient'] : '',
    'mobile_card_gradient'  => !empty($_user_tpl_options['mobile_card_gradient']) ? $_user_tpl_options['mobile_card_gradient'] : '',
];

// 计算主题色 RGB 分量 & 暗色变体（供 CSS rgba / hover 使用）
$_tp_hex = ltrim($_ut['theme_primary'], '#');
if (strlen($_tp_hex) === 3) $_tp_hex = $_tp_hex[0].$_tp_hex[0].$_tp_hex[1].$_tp_hex[1].$_tp_hex[2].$_tp_hex[2];
$_tp_r = hexdec(substr($_tp_hex, 0, 2));
$_tp_g = hexdec(substr($_tp_hex, 2, 2));
$_tp_b = hexdec(substr($_tp_hex, 4, 2));
$_tp_rgb = "$_tp_r,$_tp_g,$_tp_b";
$_tp_dark = '#' . str_pad(dechex(max(0, (int)($_tp_r * 0.82))), 2, '0', STR_PAD_LEFT)
              . str_pad(dechex(max(0, (int)($_tp_g * 0.82))), 2, '0', STR_PAD_LEFT)
              . str_pad(dechex(max(0, (int)($_tp_b * 0.82))), 2, '0', STR_PAD_LEFT);
$_tp_light = '#' . str_pad(dechex(min(255, (int)($_tp_r * 0.75 + 255 * 0.25))), 2, '0', STR_PAD_LEFT)
               . str_pad(dechex(min(255, (int)($_tp_g * 0.75 + 255 * 0.25))), 2, '0', STR_PAD_LEFT)
               . str_pad(dechex(min(255, (int)($_tp_b * 0.75 + 255 * 0.25))), 2, '0', STR_PAD_LEFT);

// 头部栏业务数据
global $userData;
$_nav_avatar = class_exists('User') ? User::getAvatar($userData['photo'] ?? '') : (DC_URL . 'admin/views/images/avatar.svg');
$_nav_nickname = htmlspecialchars($userData['nickname'] ?? ($userData['username'] ?? '用户'));
$_nav_service_url = Option::get('contact_presale_url') ?: Option::get('contact_aftersale_url') ?: '';
$_nav_login_on = Option::get('login_switch') == 'y' && Option::get('register_switch') == 'y';
$_station_on = class_exists('Level_Service') ? ((int)Level_Service::getSetting(Level_Service::OPT_STATION_SWITCH, 1) === 1) : true;
// 与前端模板保持一致的 header 主题变量
$_nav_header_bg      = $_ut['header_bg'] ?: (Option::get('shop_header_bg') ?: '#0c6be1');
$_nav_title_color    = $_ut['header_title_color'] ?: (Option::get('shop_title_color') ?: '#ffffff');
$_nav_subtitle_color = $_ut['header_subtitle_color'] ?: (Option::get('shop_subtitle_color') ?: 'rgba(255,255,255,0.8)');
$_nav_site_subtitle  = !empty($userData['station']['site_subtitle']) ? $userData['station']['site_subtitle'] : (Option::get('site_subtitle') ?: '');
$_request_uri = $_SERVER['REQUEST_URI'] ?? '';

$_request_path = parse_url($_request_uri, PHP_URL_PATH) ?: '';
$_request_path = rtrim($_request_path, '/');
$_request_path = $_request_path === '' ? '/' : $_request_path;
$_is_user_index = $_request_path === '/user' || $_request_path === '/user/index.php';
$_default_user_tpl_viewport_bp = 768;
$_nav_group_open = [
    'account' => $_is_user_index || strpos($_request_uri, '/user/balance.php') !== false || strpos($_request_uri, '/user/account.php') !== false || strpos($_request_uri, '/user/level.php') !== false || strpos($_request_uri, '/user/footprint.php') !== false,
    'trade' => strpos($_request_uri, '/user/order.php') !== false || strpos($_request_uri, '/user/visitors.php') !== false,
    'station' => strpos($_request_uri, '/user/station.php') !== false,
];
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>个人中心 - <?= htmlspecialchars($shop_name) ?></title>
    <meta name="renderer" content="webkit|ie-comp|ie-stand">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <?php if (isMobile()): ?>
    <meta name="viewport" content="width=device-width,user-scalable=yes, minimum-scale=1, initial-scale=1">
    <?php else: ?>
    <meta name="viewport" content="width=device-width,user-scalable=yes, minimum-scale=0.4, initial-scale=0.8">
    <?php endif; ?>
    <meta http-equiv="Cache-Control" content="no-siteapp">
    <link href="<?= getSiteFaviconUrl(DC_URL . 'admin/views/images/favicon.ico'); ?>" rel="shortcut icon">

    <!-- 关闭浏览器滚动位置自动恢复：防止 layui 异步表格页面在刷新/切换时，
         浏览器先恢复旧滚动位置，再随 layui 表格渲染完成二次修正滚动位置，
         造成 sticky 左侧导航短暂上下跳动 -->
    <script>if ('scrollRestoration' in history) { history.scrollRestoration = 'manual'; }</script>
    <script>
    (function(){
        var KEY = 'dc_default_user_mobile_app';
        var BP = <?= (int)$_default_user_tpl_viewport_bp ?>;
        var path = window.location.pathname || '';
        if (!/\/user(?:\/|$)/i.test(path)) return;
        var doc = document;
        var root = doc.documentElement;
        var width = Math.min(window.innerWidth || root.clientWidth || 0, root.clientWidth || window.innerWidth || 0);
        if (!width) return;
        var shouldMobile = width <= BP;
        var currentMobile = <?= $_is_mobile ? 'true' : 'false' ?>;
        function setCookie(val) {
            doc.cookie = KEY + '=' + val + '; path=/; max-age=' + (val === '1' ? 2592000 : 0) + '; SameSite=Lax';
        }
        function removeParam(url, name) {
            url.searchParams.delete(name);
            var qs = url.searchParams.toString();
            return url.pathname + (qs ? '?' + qs : '') + url.hash;
        }
        if (shouldMobile && !currentMobile) {
            setCookie('1');
            var mobileUrl = new URL(window.location.href);
            mobileUrl.searchParams.set('__dc_user_mobile_app', '1');
            window.location.replace(mobileUrl.pathname + '?' + mobileUrl.searchParams.toString() + mobileUrl.hash);
            return;
        }
        if (!shouldMobile && currentMobile && /(?:^|;\s*)dc_default_user_mobile_app=1(?:;|$)/.test(doc.cookie)) {
            setCookie('0');
            window.location.replace(removeParam(new URL(window.location.href), '__dc_user_mobile_app'));
        }
    })();
    </script>

    <!-- 基础资源 -->
    <script src="<?= DC_URL ?>admin/views/js/jquery.min.3.5.1.js"></script>
    <link rel="stylesheet" href="<?= DC_URL ?>admin/views/layui-v2.11.6/layui/css/layui.css">
    <script src="<?= DC_URL ?>admin/views/layui-v2.11.6/layui/layui.js"></script>
    <script src="<?= DC_URL ?>admin/views/components/clipboard.min.js?t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>

    <!-- 图标库 -->
    
    <!-- Font Awesome for other templates -->
    <link rel="stylesheet" href="<?= DC_URL ?>admin/views/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?= DC_URL ?>admin/views/remixicon/remixicon.css">
    

    <style>
        /* 重置样式 */
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        /* 固定滚动条槽位：避免不同子页因内容多少导致滚动条出现/消失，引起整页左右跳动 */
        html {
            overflow-y: scroll;
            scrollbar-gutter: stable;
        }
        
        <?php if ($uc_app_mode): ?>
        .user-main {
            padding: 0 !important;
            max-width: none !important;
            margin: 0 !important;
        }

        .user-layout {
            grid-template-columns: 1fr !important;
            gap: 0 !important;
            height: auto !important;
            overflow: visible !important;
        }

        .user-content {
            overflow: visible !important;
            padding-top: 0 !important;
        }

        .user-sidebar {
            display: none !important;
        }

        .win-titlebar {
            display: none !important;
        }

        .content-card {
            display: contents !important;
        }

        .content-card::before {
            display: none !important;
            content: none !important;
        }
        <?php endif; ?>
        
        /* 全局变量 */
        
        /* ===== Default 现代化风格 ===== */
        :root {
            --theme-primary: <?= $template_config['theme_primary'] ?>;
            --theme-price: <?= $template_config['theme_price'] ?>;
            --theme-button: <?= $template_config['theme_button'] ?>;
            --theme-accent: <?= $template_config['theme_accent'] ?>;
            --card-bg: #ffffff;
            --card-border: rgba(33,150,243,0.12);
            --pc-card-bg: <?= !empty($_ut['pc_card_gradient']) ? htmlspecialchars($_ut['pc_card_gradient']) : 'linear-gradient(0deg, #f9fbff, #eff4fd)' ?>;
            --text-main: #1f2937;
            --text-sub: #6b7280;
            --text-muted: #9ca3af;
            --bg-main: #f8fafc;
            --bg-secondary: #f1f5f9;
            --shadow-primary: 0 4px 25px rgba(33,150,243,0.08);
            --shadow-secondary: 0 2px 15px rgba(0,0,0,0.06);
            --border-radius: 10px;
            --border-radius-sm: 10px;
            --transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        body {
            background: 
                radial-gradient(ellipse at top right, rgba(33,150,243,0.03) 0%, transparent 50%),
                radial-gradient(ellipse at bottom left, rgba(255,152,0,0.03) 0%, transparent 50%),
                linear-gradient(135deg, var(--bg-main) 0%, var(--bg-secondary) 100%);
            font-family: 'Inter', 'PingFang SC', 'Microsoft YaHei', sans-serif;
            color: var(--text-main);
            min-height: 100vh;
        }
        
        
        /* ===== 默认现代化风格 ===== */
        :root {
            --theme-primary: <?= htmlspecialchars($_ut['theme_primary']) ?>;
            --theme-secondary: <?= htmlspecialchars($_ut['theme_secondary']) ?>;
            --theme-accent: <?= htmlspecialchars($_ut['theme_accent']) ?>;
            --tp-rgb: <?= $_tp_rgb ?>;
            --tp-dark: <?= $_tp_dark ?>;
            --tp-light: <?= $_tp_light ?>;
            --card-bg: #ffffff;
            --card-border: <?= htmlspecialchars($_ut['theme_primary']) ?>1f;
            --pc-card-bg: <?= !empty($_ut['pc_card_gradient']) ? htmlspecialchars($_ut['pc_card_gradient']) : 'linear-gradient(0deg, #f9fbff, #eff4fd)' ?>;
            --text-main: #1f2937;
            --text-sub: #6b7280;
            --text-muted: #9ca3af;
            --bg-main: #f9fafb;
            --bg-secondary: #f3f4f6;
            --shadow-primary: 0 4px 25px <?= htmlspecialchars($_ut['theme_primary']) ?>14;
            --shadow-secondary: 0 2px 15px rgba(0,0,0,0.06);
            --border-radius: 10px;
            --border-radius-sm: 10px;
            --transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        body {
            background: 
                radial-gradient(ellipse at top right, <?= htmlspecialchars($_ut['theme_primary']) ?>08 0%, transparent 50%),
                radial-gradient(ellipse at bottom left, <?= htmlspecialchars($_ut['theme_accent']) ?>08 0%, transparent 50%),
                linear-gradient(135deg, var(--bg-main) 0%, var(--bg-secondary) 100%);
            font-family: 'Inter', 'PingFang SC', 'Microsoft YaHei', sans-serif;
            color: var(--text-main);
            min-height: 100vh;
        }

        <?php if ($_is_mobile): ?>
        /* iOS Safari：输入框字号小于 16px 时，聚焦会自动放大页面。移动端统一兜底到 16px。 */
        input:not([type="checkbox"]):not([type="radio"]):not([type="button"]):not([type="submit"]):not([type="reset"]):not([type="file"]):not([type="hidden"]),
        textarea,
        select,
        .layui-input,
        .layui-textarea,
        .layui-form-select,
        .layui-form-select .layui-input,
        .layui-form-select input,
        .layui-form-select dl dd {
            font-size: 16px !important;
        }

        input:not([type="checkbox"]):not([type="radio"]):not([type="button"]):not([type="submit"]):not([type="reset"]):not([type="file"]):not([type="hidden"])::placeholder,
        textarea::placeholder {
            font-size: 16px !important;
        }
        <?php endif; ?>
        
        
        /* 容器布局 */
        .user-container {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* 主内容区 */
        .user-main {
            flex: 1;
            max-width: 1500px;
            margin: 0 auto;
            width: 100%;
            padding: 20px;
        }

        .user-layout {
            display: block;
        }

        /* 侧边栏：基础样式，PC / 移动端各自在媒体查询中补充定位 */
        .user-sidebar {
            padding: 14px;
        }

        .sidebar-groups {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .sidebar-group {
            border: none;
            background: transparent;
            border-radius: 6px;
            overflow: hidden;
            transition: var(--transition);
        }

        .sidebar-group-toggle {
            list-style: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            width: 100%;
            padding: 14px 16px;
            cursor: pointer;
            user-select: none;
        }
        
        .sidebar-group-toggle::-webkit-details-marker {
            display: none;
        }
        
        .sidebar-group-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        
        .sidebar-group-text {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .sidebar-group-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-main);
            line-height: 1.2;
        }
        
        
        .sidebar-group-arrow {
            font-size: 18px;
            color: var(--text-muted);
            transition: transform .2s ease, color .2s ease;
        }
        
        .sidebar-group[open] .sidebar-group-arrow {
            transform: rotate(180deg);
            color: var(--theme-primary);
        }

        /* macOS 窗口标题栏：红黄绿点 + 居中标题 */
        .sidebar-mac-bar {
            position: absolute;
            top: 12px;
            left: 14px;
            right: 14px;
            display: flex;
            align-items: center;
            height: 16px;
            z-index: 2;
            pointer-events: none;
        }
        .sidebar-mac-dots {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
            pointer-events: auto;
            cursor: default;
        }
        .sidebar-mac-dots i {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0.5px rgba(0,0,0,0.08) inset;
        }
        .sidebar-mac-dots .dot-r { background: #ff5f57; }
        .sidebar-mac-dots .dot-y { background: #febc2e; }
        .sidebar-mac-dots .dot-g { background: #28c840; }
        .sidebar-mac-title {
            position: absolute;
            left: 0; right: 0;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            pointer-events: auto;
            letter-spacing: 0.3px;
            text-decoration: none;
        }
        .sidebar-mac-title:hover { color: #334155; }
         
        .sidebar-menu {
            list-style: none;
            padding: 0 8px 8px;
        }
         
        .sidebar-menu li {
            margin-bottom: 4px;
        }
         
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            color: var(--text-sub);
            text-decoration: none;
            border-radius: 8px;
            transition: var(--transition);
            font-weight: 500;
            font-size: 14px;
            position: relative;
            overflow: hidden;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active,
        .sidebar-menu a.menu-current {
            color: var(--theme-primary);
            background: rgba(var(--tp-rgb),0.06);
            border-radius: 8px;
            text-decoration: none;
        }
        
        .sidebar-menu a i {
            font-size: 18px;
            width: 20px;
            text-align: center;
            flex: 0 0 20px;
        }
        
        /* 内容卡片 */

        /* ===== 布局卡片：sidebar 底板向右延伸包裹内容区 ===== */
        .user-main { padding: 24px 24px 24px; }
        .user-layout {
            display: grid;
            grid-template-columns: 220px 1fr;
            grid-template-rows: 1fr;
            position: relative;
            background: #f7f9fc;
            border: 2px solid #fff;
            border-radius: 10px;
            height: calc(100vh - 48px);
            padding-top: 38px;
            overflow: hidden;
            box-shadow: 0 10px 20px 0 rgb(0 0 0 / 14%);
        }

        /* macOS 标题栏 */
        .win-titlebar {
            display: flex;
            align-items: center;
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 38px;
            padding: 0 16px;
            z-index: 60;
            user-select: none;
            border-radius: 10px 10px 0 0;
        }
        .win-dots { display: inline-flex; align-items: center; gap: 7px; }
        .win-dots i { width: 12px; height: 12px; border-radius: 50%; display: inline-block; box-shadow: 0 0 0 0.5px rgba(0,0,0,0.12) inset; }
        .win-dots .dot-r { background: #ff5f57; }
        .win-dots .dot-y { background: #febc2e; }
        .win-dots .dot-g { background: #28c840; }
        .win-title {
            position: absolute; left: 0; right: 0;
            text-align: center;
            font-size: 13px; font-weight: 600; color: #4b5563;
            letter-spacing: .3px;
            pointer-events: none;
        }
        a.win-title {
            pointer-events: auto;
            text-decoration: none; color: #4b5563;
            cursor: pointer; transition: color .18s;
        }
        a.win-title:hover { color: var(--theme-primary, #2563eb); }

        /* 侧边栏：grid 左列，固定高度 */
        .user-sidebar {
            grid-column: 1;
            grid-row: 1 / -1;
            display: flex;
            flex-direction: column;
            background: var(--pc-card-bg);
            border-right: 2px solid #fff;
            padding: 0 10px 14px;
            margin-top: -38px;
            padding-top: 46px;
            overflow-y: auto;
            box-shadow: 8px 0 10px -5px #12345b0f;
        }
        .sidebar-mac-bar { display: none; }

        /* 侧栏 Logo */
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 12px 16px;
            text-decoration: none;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            margin-bottom: 8px;
        }
        .sidebar-brand-logo {
            width: 32px; height: 32px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
        }
        .sidebar-brand-text {
            display: flex; flex-direction: column; min-width: 0;
        }
        .sidebar-brand-name {
            font-size: 14px; font-weight: 700; color: #1e293b;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .sidebar-brand-sub {
            font-size: 11px; color: #94a3b8; line-height: 1.3;
        }

        .sidebar-scroll {
            flex: 1;
            overflow-y: auto;
        }
        .sidebar-group-toggle { padding: 10px 12px; }
        .sidebar-menu { padding: 0 4px 6px; }
        .sidebar-menu a { padding: 9px 12px; font-size: 13px; border-radius: 6px; }

        /* 侧栏底部快捷操作 */
        .sidebar-actions {
            padding: 8px 4px 0;
            margin-top: 4px;
            border-top: 1px solid rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .sidebar-actions a {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 12px; border-radius: 6px;
            font-size: 13px; color: #64748b;
            text-decoration: none; transition: background .15s, color .15s;
        }
        .sidebar-actions a:hover { background: rgba(var(--tp-rgb),0.06); color: var(--theme-primary); }
        .sidebar-actions a i { width: 18px; text-align: center; font-size: 14px; }
        .sidebar-actions .sa-danger { color: #ef4444; }
        .sidebar-actions .sa-danger:hover { background: rgba(239,68,68,0.06); color: #dc2626; }

        /* 用户卡片退出按钮 */
        .sidebar-user-logout {
            display: flex; align-items: center; justify-content: center;
            width: 30px; height: 30px; flex-shrink: 0;
            border-radius: 8px; border: none; background: transparent;
            color: #94a3b8; cursor: pointer;
            transition: background .18s, color .18s;
            text-decoration: none;
        }
        .sidebar-user-logout:hover { background: rgba(239,68,68,0.08); color: #ef4444; }
        .sidebar-user-logout i { font-size: 16px; }

        /* 侧栏用户卡片 */
        .sidebar-user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 12px 0;
            margin-top: 8px;
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        .sidebar-user-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            border: 2px solid #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: box-shadow .18s, border-color .18s;
        }
        a.sidebar-avatar-link { flex-shrink: 0; line-height: 0; }
        a.sidebar-avatar-link:hover .sidebar-user-avatar {
            border-color: var(--theme-primary, #2563eb);
            box-shadow: 0 0 0 2px rgba(37,99,235,0.18);
        }
        .sidebar-user-info { min-width: 0; flex: 1; }
        a.sidebar-user-name-link {
            display: block; min-width: 0; text-decoration: none;
        }
        .sidebar-user-name {
            font-size: 13px; font-weight: 600; color: #1e293b;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            display: block; transition: color .18s;
        }
        a.sidebar-user-name-link:hover .sidebar-user-name { color: var(--theme-primary, #2563eb); }
        .sidebar-user-role {
            font-size: 11px; color: #94a3b8; line-height: 1.3;
        }

        /* 内容区：grid 右列，可滚动，flex 纵列让 footer 始终吸底 */
        .user-content {
            grid-column: 2;
            grid-row: 1 / -1;
            min-width: 0;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 0px 22px 0;
            display: flex;
            flex-direction: column;
        }

        /* 页面底部版权信息 — 始终吸附在内容区底部 */
        .page-footer-info {
            margin-top: auto;
            position: sticky;
            bottom: 0;
            z-index: 2;
            padding: 10px 4px;
            text-align: center;
            font-size: 12px;
            color: #c0c6ce;
            background: linear-gradient(to bottom, transparent, #f7f9fc 8px);
        }
        .page-footer-info a { color: #c0c6ce; text-decoration: none; transition: color .2s; }
        .page-footer-info a:hover { color: #94a3b8; }
        .page-footer-info .pfi-dot { margin: 0 4px; }
        
        /* 深色/浅色模式支持 */
        
        /* 侧栏导航滚动条视觉隐藏 */
        .user-sidebar { scrollbar-width: none; -ms-overflow-style: none; }
        .user-sidebar::-webkit-scrollbar { width: 0; height: 0; display: none; }

        /* 内容区滚动条 */
        .user-content { scrollbar-width: thin; scrollbar-color: rgba(0,0,0,.12) transparent; }
        .user-content::-webkit-scrollbar { width: 6px; }
        .user-content::-webkit-scrollbar-track { background: transparent; }
        .user-content::-webkit-scrollbar-thumb { background: rgba(0,0,0,.1); border-radius: 3px; }
        .user-content::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,.2); }

        <?php if ($_use_mobile_shell): ?>
        /* ===== Mobile App Shell ===== */
        body.m-app-body {
            background: var(--bg-main);
            min-height: 100vh;
            -webkit-overflow-scrolling: touch;
            overflow-x: hidden;
        }
        .m-content {
            min-height: 100vh;
        }
        .m-topbar ~ .m-content {
            padding-top: calc(50px + env(safe-area-inset-top, 0px));
        }
        .m-content .page-header {
            display: none;
        }
        <?php endif; ?>
    </style>
    <?php if (!empty($_ut['custom_css'])): ?>
    <style id="user-tpl-custom-css"><?= $_ut['custom_css'] ?></style>
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

    <?php doAction('open_head') ?>

</head>
<?php if ($_use_mobile_shell): ?>
<body class="m-app-body">
    <?php include __DIR__ . '/_mobile_topbar.php'; ?>
    <?php if (empty($uc_hide_bottom_nav)): ?>
        <?php $_bottom_nav_view = View::getBottomNavView('render'); ?>
        <?php if (is_file($_bottom_nav_view)): ?>
            <?php ob_start(); include $_bottom_nav_view; $_uc_bottom_nav_html = trim((string)ob_get_clean()); ?>
            <?php if ($_uc_bottom_nav_html !== ''): ?>
                <style id="uc-bottom-nav-fast-space">body.m-app-body { padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 70px); }</style>
                <?= $_uc_bottom_nav_html ?>
                <?php $uc_bottom_nav_rendered = true; ?>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
    <div class="m-content">
<?php else: ?>
<body>
    <div class="user-container">
        <?php if($_ut['show_bell_icon'] === 'y'): ?>
        <?php
        $_hdr_bulletin_raw = Option::get('home_bulletin');
        $_hdr_bulletin = html_entity_decode($_hdr_bulletin_raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $_hdr_has_bulletin = !empty(preg_replace('/[\s\x{00A0}]+/u', '', strip_tags($_hdr_bulletin)));
        ?>
        <div id="bulletinContent" style="display:none"><?= $_hdr_has_bulletin ? $_hdr_bulletin : '' ?></div>
        <style>
        .bp-overlay {
            display: none; position: fixed; inset: 0; z-index: 19999;
            background: rgba(15,23,42,.32);
            backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);
            justify-content: center; align-items: center;
        }
        .bp-overlay.is-open { display: flex; }
        .bp-dialog {
            position: relative; width: 460px; max-width: calc(100vw - 32px);
            max-height: 80vh; display: flex; flex-direction: column;
            border-radius: 10px; overflow: hidden;
            background: #f5f5f5;
            box-shadow: 0 25px 60px -12px rgba(0,0,0,.2);
            animation: bpIn .22s ease-out;
            border: 2px solid #fff;
        }
        @keyframes bpIn { from { opacity: 0; transform: translateY(18px) scale(.97); } to { opacity: 1; transform: none; } }
        .bp-close {
            position: absolute; top: 14px; right: 14px; z-index: 2;
            width: 32px; height: 32px; border: none; padding: 0;
            border-radius: 50%; cursor: pointer;
            background: rgba(255,255,255,.22);
            color: #fff; font-size: 20px; line-height: 32px;
            text-align: center; transition: background .2s;
            display: flex; align-items: center; justify-content: center;
        }
        .bp-close:hover { background: rgba(255,255,255,.4); }
        .bp-banner {
            background: linear-gradient(135deg, var(--tp-dark) 0%, var(--theme-primary) 40%, var(--tp-light) 100%);
            padding: 32px 30px 28px; position: relative; overflow: hidden; flex-shrink: 0;
        }
        .bp-banner::before {
            content: ''; position: absolute; top: -30px; right: -20px;
            width: 120px; height: 120px; border-radius: 50%;
            background: rgba(255,255,255,.08);
        }
        .bp-banner::after {
            content: ''; position: absolute; bottom: -40px; left: 30px;
            width: 80px; height: 80px; border-radius: 50%;
            background: rgba(255,255,255,.05);
        }
        .bp-banner-inner { position: relative; z-index: 1; display: flex; align-items: center; gap: 14px; }
        .bp-icon {
            width: 46px; height: 46px; border-radius: 12px;
            background: rgba(255,255,255,.18);
            backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 20px; flex-shrink: 0;
        }
        .bp-title { font-size: 18px; font-weight: 700; color: #fff; letter-spacing: .3px; }
        .bp-subtitle { font-size: 12px; color: rgba(255,255,255,.7); margin-top: 2px; }
        .bp-body-wrap { padding: 24px 28px 0; overflow-y: auto; flex: 1 1 auto; min-height: 0; }
        .bp-body { color: #374151; font-size: 14px; line-height: 1.9; word-break: break-word; }
        .bp-body-wrap::-webkit-scrollbar { width: 4px; }
        .bp-body-wrap::-webkit-scrollbar-track { background: transparent; }
        .bp-body-wrap::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
        .bp-body img { max-width: 100%; border-radius: 8px; margin: 8px 0; }
        .bp-body a { color: var(--theme-primary); text-decoration: none; border-bottom: 1px dashed rgba(var(--tp-rgb),.3); transition: border-color .2s; }
        .bp-body a:hover { border-bottom-color: var(--theme-primary); }
        .bp-empty { text-align: center; padding: 40px 0; color: #9ca3af; }
        .bp-empty i { font-size: 44px; display: block; margin-bottom: 14px; opacity: .35; }
        .bp-empty p { margin: 0; font-size: 14px; }
        .bp-footer { padding: 20px 28px 24px; text-align: right; flex-shrink: 0; }
        .bp-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            min-width: 110px; height: 38px; padding: 0 22px;
            border: none; border-radius: 10px;
            background: var(--theme-primary); color: #fff; font-size: 13px; font-weight: 600;
            cursor: pointer; transition: all .2s;
            box-shadow: 0 2px 8px rgba(var(--tp-rgb),.25);
        }
        .bp-btn:hover { background: var(--tp-dark); box-shadow: 0 4px 14px rgba(var(--tp-rgb),.35); transform: translateY(-1px); }
        .bp-btn:active { transform: translateY(0); }
        </style>
        <div id="bulletinOverlay" class="bp-overlay">
            <div class="bp-dialog">
                <button class="bp-close" onclick="closeBulletinPopup()" aria-label="关闭">&#215;</button>
                <div class="bp-banner">
                    <div class="bp-banner-inner">
                        <div class="bp-icon"><i class="fa fa-bullhorn"></i></div>
                        <div><div class="bp-title">站点公告</div><div class="bp-subtitle">ANNOUNCEMENT</div></div>
                    </div>
                </div>
                <div class="bp-body-wrap"><div id="bulletinBody" class="bp-body"></div></div>
                <div class="bp-footer"><button class="bp-btn" onclick="closeBulletinPopup()"><i class="fa fa-check"></i> 我知道了</button></div>
            </div>
        </div>
        <script>
        function showBulletinPopup(){
            var src = document.getElementById('bulletinContent').innerHTML;
            var body = document.getElementById('bulletinBody');
            if(src && src.trim()){
                body.innerHTML = src;
            } else {
                body.innerHTML = '<div class="bp-empty"><i class="fa fa-bullhorn"></i><p>暂无公告</p></div>';
            }
            document.getElementById('bulletinOverlay').classList.add('is-open');
        }
        function closeBulletinPopup(){
            document.getElementById('bulletinOverlay').classList.remove('is-open');
        }
        document.getElementById('bulletinOverlay').addEventListener('click', function(e){
            if(e.target === this) closeBulletinPopup();
        });
        </script>
        <?php endif; ?>

        <!-- 主内容区 -->
        <main class="user-main">
            <div class="user-layout">
                <!-- 侧边栏 -->
                <aside class="user-sidebar">
                    <!-- 侧栏 Logo -->
                    <a href="<?= DC_URL ?>" class="sidebar-brand" title="<?= htmlspecialchars($shop_name) ?>">
                        <img class="sidebar-brand-logo" src="<?= $logo ?>" alt="<?= htmlspecialchars($shop_name) ?>">
                        <div class="sidebar-brand-text">
                            <span class="sidebar-brand-name"><?= htmlspecialchars($shop_name) ?></span>
                            <span class="sidebar-brand-sub"><?= !empty($_nav_site_subtitle) ? htmlspecialchars($_nav_site_subtitle) : '个人中心' ?></span>
                        </div>
                    </a>
                    <?php if($_ut['sidebar_mac_dots'] === 'y'): ?>
                    <div class="sidebar-mac-bar">
                        <span class="sidebar-mac-dots"><i class="dot-r"></i><i class="dot-y"></i><i class="dot-g"></i></span>
                        <a href="/user/" class="sidebar-mac-title">个人中心</a>
                    </div>
                    <?php endif; ?>
                    <div class="sidebar-scroll"<?= $_ut['sidebar_mac_dots'] === 'y' ? ' style="margin-top:10px;"' : '' ?>>
                        <div class="sidebar-groups">
                            <details class="sidebar-group<?= $_nav_group_open['account'] ? ' is-current-group' : '' ?>" data-group="account"<?= $_nav_group_open['account'] ? ' open' : '' ?>>
                                <summary class="sidebar-group-toggle">
                                    <span class="sidebar-group-meta">
                                        <span class="sidebar-group-text">
                                            <span class="sidebar-group-title">账户设置</span>
                                        </span>
                                    </span>
                                    <i class="fa fa-angle-down sidebar-group-arrow"></i>
                                </summary>
                                <ul class="sidebar-menu">
                                    <li>
                                        <a href="/user" id="menu-index">
                                            <i class="fa fa-user"></i>
                                            个人中心
                                        </a>
                                    </li>
                                    <li>
                                        <a href="/user/balance.php" id="menu-balance">
                                            <i class="ri-wallet-3-line"></i>
                                            我的钱包
                                        </a>
                                    </li>
                                    <li>
                                        <a href="/user/balance.php?action=balance_log" id="menu-balance-log">
                                            <i class="fa fa-exchange"></i>
                                            收支明细
                                        </a>
                                    </li>
                                    <li>
                                        <a href="/user/balance.php?action=withdraw_index" id="menu-withdraw">
                                            <i class="fa fa-list-ul"></i>
                                            提现记录
                                        </a>
                                    </li>
                                    <li>
                                        <a href="/user/level.php" id="menu-level">
                                            <i class="fa fa-diamond"></i>
                                            会员中心
                                        </a>
                                    </li>
                                    <li>
                                        <a href="/user/account.php?action=invite" id="menu-invite">
                                            <i class="fa fa-share-alt"></i>
                                            邀请好友
                                        </a>
                                    </li>
                                    <li>
                                        <a href="/user/account.php?action=fans" id="menu-fans">
                                            <i class="fa fa-users"></i>
                                            我的粉丝
                                        </a>
                                    </li>
                                    <li>
                                        <a href="/user/footprint.php" id="menu-footprint">
                                            <i class="ri-footprint-line"></i>
                                            浏览足迹
                                        </a>
                                    </li>
                                    <li>
                                        <a href="/user/account.php?action=setting" id="menu-account-setting">
                                            <i class="fa fa-cog"></i>
                                            信息设置
                                        </a>
                                    </li>
                                </ul>
                            </details>
                            <details class="sidebar-group<?= $_nav_group_open['trade'] ? ' is-current-group' : '' ?>" data-group="trade"<?= $_nav_group_open['trade'] ? ' open' : '' ?>>
                                <summary class="sidebar-group-toggle">
                                    <span class="sidebar-group-meta">
                                        <span class="sidebar-group-text">
                                            <span class="sidebar-group-title">订单服务</span>
                                        </span>
                                    </span>
                                    <i class="fa fa-angle-down sidebar-group-arrow"></i>
                                </summary>
                                <ul class="sidebar-menu">
                                    <li>
                                        <a href="/user/order.php" id="menu-order">
                                            <i class="fa fa-list-ul"></i>
                                            订单列表
                                        </a>
                                    </li>
                                    <li>
                                        <a href="/user/visitors.php" id="menu-visitors">
                                            <i class="fa fa-search"></i>
                                            游客查单
                                        </a>
                                    </li>
                                </ul>
                            </details>
                            <?php if($_station_on): ?>
                            <details class="sidebar-group<?= $_nav_group_open['station'] ? ' is-current-group' : '' ?>" data-group="station"<?= $_nav_group_open['station'] ? ' open' : '' ?>>
                                <summary class="sidebar-group-toggle">
                                    <span class="sidebar-group-meta">
                                        <span class="sidebar-group-text">
                                            <span class="sidebar-group-title">分店管理</span>
                                        </span>
                                    </span>
                                    <i class="fa fa-angle-down sidebar-group-arrow"></i>
                                </summary>
                                <ul class="sidebar-menu">
                                    <li>
                                        <a href="/user/station.php?action=open" id="menu-open-station">
                                            <i class="fa fa-sitemap"></i>
                                            <?= !empty($userData['station']) ? '升级分店' : '开通分店' ?>
                                        </a>
                                    </li>
                                    <?php if(!empty($userData['station'])): ?>
                                    <li>
                                        <a href="/user/station.php" id="menu-station">
                                            <i class="fa fa-dashboard"></i>
                                            店铺概览
                                        </a>
                                    </li>
                                    <li>
                                        <a href="/user/station.php?action=setting" id="menu-station-setting">
                                            <i class="fa fa-cog"></i>
                                            店铺配置
                                        </a>
                                    </li>
                                    <li>
                                        <a href="/user/station.php?action=master_goods" id="menu-station-goods">
                                            <i class="fa fa-cube"></i>
                                            商品管理
                                        </a>
                                    </li>
                                    <li>
                                        <a href="/user/station.php?action=order" id="menu-station-order">
                                            <i class="fa fa-file-text"></i>
                                            分店订单
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </details>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- 侧栏底部：快捷操作 + 用户 -->
                    <div class="sidebar-actions">
                        <a href="<?= DC_URL ?>"><i class="fa fa-shopping-bag"></i> 商城首页</a>
                        <?php if($_ut['show_bell_icon'] === 'y'): ?>
                        <a href="javascript:void(0)" onclick="showBulletinPopup()"><i class="fa fa-bell-o"></i> 系统公告</a>
                        <?php endif; ?>
                        <?php if($_ut['show_service_icon'] === 'y' && !empty($_nav_service_url)): ?>
                        <a href="<?= htmlspecialchars($_nav_service_url) ?>" target="_blank" rel="noopener"><i class="fa fa-comments-o"></i> 联系客服</a>
                        <?php endif; ?>
                    </div>
                    <div class="sidebar-user-card">
                        <a href="/user/account.php?action=setting" class="sidebar-avatar-link" title="信息设置"><img src="<?= htmlspecialchars($_nav_avatar) ?>" alt="<?= $_nav_nickname ?>" class="sidebar-user-avatar"></a>
                        <div class="sidebar-user-info">
                            <a href="/user/account.php?action=setting#nickname" class="sidebar-user-name-link" title="信息设置"><span class="sidebar-user-name"><?= $_nav_nickname ?></span></a>
                            <span class="sidebar-user-role">个人中心</span>
                        </div>
                        <?php if($_nav_login_on): ?>
                        <a href="/user/account.php?action=logout" class="sidebar-user-logout" title="退出登录"><i class="fa fa-sign-out"></i></a>
                        <?php endif; ?>
                    </div>
                </aside>
                
                <script>
                !function(){
                    var groups = document.querySelectorAll('.sidebar-group');
                    if (!groups.length) return;
                    groups.forEach(function(group){
                        if (!group.classList.contains('is-current-group')) group.open = false;
                    });
                    groups.forEach(function(group){
                        group.addEventListener('toggle', function(){
                            if (group.open) {
                                groups.forEach(function(other){
                                    if (other !== group) other.open = false;
                                });
                            }
                        });
                    });
                }();
                </script>
                
                <div class="win-titlebar">
                    <span class="win-dots"><i class="dot-r"></i><i class="dot-y"></i><i class="dot-g"></i></span>
                    <a href="/user/" class="win-title" title="返回个人中心首页">个人中心</a>
                </div>
                <div class="user-content">
                <!-- 内容区 -->
<?php endif; ?>
