<?php

ob_start();
header('Content-Type: text/html; charset=UTF-8');

const DC_ROOT = __DIR__;

// 检测是否已安装（config.php 是否存在）
if (!file_exists(DC_ROOT . '/config.php')) {
    // 未安装，跳转到安装页面
    $install_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                   . '://' . $_SERVER['HTTP_HOST']
                   . dirname($_SERVER['SCRIPT_NAME'])
                   . '/install.php';
    $install_url = str_replace('//', '/', $install_url);
    $install_url = str_replace(':/', '://', $install_url);
    header('Location: ' . $install_url);
    exit;
}

require_once DC_ROOT . '/config.php';
require_once DC_ROOT . '/base.php';
require_once DC_ROOT . '/include/lib/common.php';



if (getenv('EM_ENV') === 'develop' || (defined('ENVIRONMENT') && ENVIRONMENT === 'develop')) {
    // 显示所有错误（包括警告、通知等）
    error_reporting(E_ALL);
} else {
    error_reporting(1);
}

if (extension_loaded('mbstring')) {
    mb_internal_encoding('UTF-8');
}

spl_autoload_register("emAutoload");

$CACHE = Cache::getInstance();

// 检查缓存目录是否存在，不存在则创建并初始化缓存
$cacheDir = DC_ROOT . '/content/cache/';
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}
// 检查核心缓存文件是否存在，不存在则初始化
if (!file_exists($cacheDir . 'options.php')) {
    $CACHE->updateCache();
}

$userData = [];

define('ISLOGIN', LoginAuth::isLogin());

date_default_timezone_set(Option::get('timezone'));

const ROLE_ADMIN = 'admin';
const ROLE_EDITOR = 'editor';
const ROLE_WRITER = 'writer';
const ROLE_VISITOR = 'visitor';

//d($userData);die;

$runtimeLevel = -1;
if (ISLOGIN === true) {
    $runtimeLevel = class_exists('Level_Service') ? Level_Service::getActiveLevelId($userData) : null;
    if ($runtimeLevel === null) {
        try {
            $memberModel = new Member_Model();
            $runtimeLevel = (int)$memberModel->getDefaultLevelId();
        } catch (Throwable $e) {
            $runtimeLevel = null;
        }
    }
    if ($runtimeLevel === null || (int)$runtimeLevel <= 0) {
        $runtimeLevel = -1;
    }
}

define('ROLE', ISLOGIN === true ? $userData['role'] : User::ROLE_VISITOR);
define('UID', ISLOGIN === true ? (int)$userData['uid'] : 0);
define('LEVEL', $runtimeLevel); // 用户等级

define('DC_URL', realUrl()); // 当前网址
define('EM_DOMAIN', getDomain()); // 当前域名
define('IS_BLOG_DOMAIN', function_exists('dcIsBlogIndependentDomain') ? dcIsBlogIndependentDomain(EM_DOMAIN) : false); // 是否为博客独立域名
define('TIMESTAMP', time()); // 当前时间戳





// 邀请码全局捕获：任意页面带 invite 或 invite_code 参数时写入 cookie
if (!empty($_GET['invite']) || !empty($_GET['invite_code'])) {
    $_inviteRaw = !empty($_GET['invite']) ? $_GET['invite'] : $_GET['invite_code'];
    $_inviteClean = preg_replace('/[^A-Za-z0-9]/', '', $_inviteRaw);
    if ($_inviteClean !== '') {
        setcookie('dc_invite_code', $_inviteClean, time() + 30 * 86400, '/');
        $_COOKIE['dc_invite_code'] = $_inviteClean;
    }
    unset($_inviteRaw, $_inviteClean);
}

// 保存本地身份标识
if(isset($_COOKIE['EB_LOCAL'])){
    define('EB_LOCAL', strip_tags($_COOKIE['EB_LOCAL']));
}else{
    define('EB_LOCAL', md5(time()));
    setcookie('EB_LOCAL', EB_LOCAL, time() + 3600*24*365);
}




const TPLS_PATH = DC_ROOT . '/content/templates/';
const USER_TPLS_PATH = DC_ROOT . '/content/user_templates/';
const BOTTOM_NAV_TPLS_PATH = DC_ROOT . '/content/bottom_nav_templates/';
const BLOG_TPLS_PATH = DC_ROOT . '/content/blog_templates/';
const PLUGIN_URL = DC_URL . 'content/plugins/';
const PLUGIN_PATH = DC_ROOT . '/content/plugins/';
const LOG_PATH = DC_ROOT . '/content/log/';

$stationModel = new Station_Model();
$stationData = $stationModel->getStationInfo();

//d($stationData);die;

if (!function_exists('dcRenderStationDisabledPage')) {
    function dcRenderStationDisabledPage($stationName = '') {
        $stationName = trim(strip_tags((string)$stationName));
        $title = $stationName !== '' ? $stationName : '分店';
        $mainUrl = trim((string)Option::get('blogurl'));
        if ($mainUrl === '') {
            $mainUrl = DC_URL;
        }
        $titleEsc = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $mainUrlEsc = htmlspecialchars($mainUrl, ENT_QUOTES, 'UTF-8');
        header('HTTP/1.1 403 Forbidden');
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>分店已停用</title><style>body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f6f8fb;color:#172033;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Arial,"Microsoft YaHei",sans-serif}.box{width:min(92vw,460px);padding:42px 36px;text-align:center;background:#fff;border-radius:22px;box-shadow:0 22px 60px rgba(15,23,42,.10)}.icon{width:68px;height:68px;margin:0 auto 18px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#fff1f2;color:#e11d48;font-size:34px}h1{font-size:24px;margin:0 0 12px}p{margin:0;color:#64748b;line-height:1.8;font-size:15px}.btn{display:inline-flex;margin-top:26px;padding:10px 22px;border-radius:999px;background:#2563eb;color:#fff;text-decoration:none;font-size:14px;box-shadow:0 10px 24px rgba(37,99,235,.22)}</style></head><body><div class="box"><div class="icon">!</div><h1>' . $titleEsc . '已停用</h1><p>当前分店已由平台管理员暂停访问。<br>如有疑问，请联系平台客服或管理员。</p><a class="btn" href="' . $mainUrlEsc . '">返回主站</a></div></body></html>';
        exit;
    }
}

$_is_admin_path = (strpos(str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? ''), '/admin/') !== false);
// 博客独立域名只用于博客前台，不作为后台入口；后台统一回到主站域名，避免授权判断和后台入口混用。
if ($_is_admin_path && defined('IS_BLOG_DOMAIN') && IS_BLOG_DOMAIN) {
    $_admin_redirect_options = $CACHE->readCache('options');
    $_main_site_url = trim((string)($_admin_redirect_options['blogurl'] ?? ''));
    if ($_main_site_url !== '' && !preg_match('#^https?://#i', $_main_site_url)) {
        $_main_site_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://') . $_main_site_url;
    }
    $_main_site_parts = $_main_site_url !== '' ? parse_url($_main_site_url) : false;
    $_main_site_host = '';
    if (is_array($_main_site_parts) && !empty($_main_site_parts['host'])) {
        $_main_site_host = strtolower((string)$_main_site_parts['host']);
        if (isset($_main_site_parts['port'])) {
            $_main_site_host .= ':' . (int)$_main_site_parts['port'];
        }
    }
    $_current_admin_host = function_exists('dcNormalizeDomain') ? dcNormalizeDomain(EM_DOMAIN) : strtolower((string)EM_DOMAIN);

    if (
        $_main_site_host !== ''
        && strcasecmp($_main_site_host, $_current_admin_host) !== 0
        && (!function_exists('dcIsBlogIndependentDomain') || !dcIsBlogIndependentDomain($_main_site_host))
    ) {
        $_main_site_scheme = $_main_site_parts['scheme'] ?? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http');
        $_main_site_base_path = isset($_main_site_parts['path']) ? rtrim((string)$_main_site_parts['path'], '/') : '';
        if ($_main_site_base_path !== '' && preg_match('#\.php$#i', $_main_site_base_path)) {
            $_main_site_base_path = rtrim(str_replace('\\', '/', dirname($_main_site_base_path)), '/');
            if ($_main_site_base_path === '.') {
                $_main_site_base_path = '';
            }
        }

        $_script_name = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/admin/');
        $_current_base_path = rtrim((string)(parse_url(DC_URL, PHP_URL_PATH) ?: ''), '/');
        if ($_current_base_path !== '' && strpos($_script_name, $_current_base_path) === 0) {
            $_admin_target_path = substr($_script_name, strlen($_current_base_path));
        } elseif (preg_match('#/admin/.*$#', $_script_name, $_admin_path_match)) {
            $_admin_target_path = $_admin_path_match[0];
        } else {
            $_admin_target_path = '/admin/';
        }
        $_admin_target_path = '/' . ltrim($_admin_target_path, '/');
        $_admin_redirect_url = $_main_site_scheme . '://' . $_main_site_host . $_main_site_base_path . $_admin_target_path;
        if (!empty($_SERVER['QUERY_STRING'])) {
            $_admin_redirect_url .= '?' . $_SERVER['QUERY_STRING'];
        }
        header('Location: ' . $_admin_redirect_url, true, 302);
        exit;
    }

    header('HTTP/1.1 403 Forbidden');
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>后台入口受限</title><style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#f7f7f7;color:#333}.box{max-width:480px;padding:38px;background:#fff;border-radius:16px;box-shadow:0 12px 35px rgba(0,0,0,.08);text-align:center}h1{margin:0 0 14px;font-size:28px}p{line-height:1.8;color:#666}</style></head><body><div class="box"><h1>后台入口受限</h1><p>博客独立域名仅用于访问博客前台，请使用主站域名进入后台。</p></div></body></html>';
    exit;
}
$_dc_req_uri_for_station_status = $_SERVER['REQUEST_URI'] ?? '';
$_dc_base_path_for_station_status = parse_url(DC_URL, PHP_URL_PATH) ?: '/';
$_dc_rel_path_for_station_status = substr($_dc_req_uri_for_station_status, strlen(rtrim($_dc_base_path_for_station_status, '/')));
$_dc_is_station_slug_request = preg_match('#^/s/[a-zA-Z0-9_-]{1,50}(?:\?.*)?$#', $_dc_rel_path_for_station_status) === 1;
if (!$_is_admin_path && !$_dc_is_station_slug_request && !empty($stationData['id']) && array_key_exists('status', $stationData) && (int)$stationData['status'] === 0) {
    if (isset($_COOKIE['dc_station_slug'])) {
        setcookie('dc_station_slug', '', time() - 1, '/');
        unset($_COOKIE['dc_station_slug']);
    }
    dcRenderStationDisabledPage($stationData['name'] ?? '');
}

// 未绑定域名拦截：开启后仅放行主站域名 + 博客独立域名 + 白名单 + 已绑定分店域名
$_station_domain_strict = Option::get('station_domain_strict');
if ($_station_domain_strict === '1' && !$_is_admin_path && !defined('SKIP_DOMAIN_CHECK')) {
    $_current_domain = EM_DOMAIN;
    $_main_domain = function_exists('dcNormalizeDomain') ? dcNormalizeDomain(Option::get('blogurl')) : preg_replace('#^https?://#i', '', rtrim((string)Option::get('blogurl'), '/'));
    $_is_blog_domain = (defined('IS_BLOG_DOMAIN') && IS_BLOG_DOMAIN);
    // 非主站域名 + 非博客独立域名 + 非已匹配分店 → 需要检查白名单
    $_is_main = (!empty($_main_domain) && strcasecmp($_current_domain, $_main_domain) === 0);
    $_is_station = (!empty($stationData['id']) && (int)$stationData['id'] > 0);
    if (!$_is_main && !$_is_blog_domain && !$_is_station) {
        $_extra_str = trim((string)Option::get('station_extra_domains'));
        $_allowed = false;
        if (!empty($_extra_str)) {
            $_extra_list = array_map('trim', explode(',', strtolower($_extra_str)));
            if (in_array(strtolower($_current_domain), $_extra_list, true)) {
                $_allowed = true;
            }
        }
        if (!$_allowed) {
            header('HTTP/1.1 403 Forbidden');
            echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>访问受限</title><style>body{font-family:-apple-system,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#f5f5f5;color:#333}div{text-align:center;max-width:400px;padding:40px}h1{font-size:48px;margin:0 0 16px;color:#e74c3c}p{font-size:16px;line-height:1.6;color:#666}</style></head><body><div><h1>403</h1><p>当前域名 <b>' . htmlspecialchars($_current_domain) . '</b> 未授权访问本站，请联系管理员。</p></div></body></html>';
            exit;
        }
    }
}

// 分店自动绑定：已登录 + 在分店 + 无上级 → 绑定站长为上级（init.php 每次请求只执行一次，无需额外标记）
if (ISLOGIN && UID > 0 && !empty($stationData['id']) && (int)$stationData['id'] > 0) {
    $_stOwnerUid = (int)($stationData['user_id'] ?? 0);
    if ($_stOwnerUid > 0 && $_stOwnerUid !== UID) {
        $_curSup = (int)($userData['superior'] ?? 0);
        if ($_curSup <= 0) {
            $_db = Database::getInstance();
            $_ownerOk = $_db->once_fetch_array(
                "SELECT uid FROM " . DB_PREFIX . "user WHERE uid={$_stOwnerUid} AND state=0 LIMIT 1"
            );
            if ($_ownerOk) {
                $_db->query(
                    "UPDATE " . DB_PREFIX . "user SET superior={$_stOwnerUid} WHERE uid=" . UID . " AND (superior IS NULL OR superior <= 0)"
                );
                if ($_db->affected_rows() > 0) {
                    $userData['superior'] = $_stOwnerUid;
                    if (class_exists('User_Log_Model')) {
                        User_Log_Model::log(UID, 'superior_bind', '访问分店自动绑定上级，站长UID：' . $_stOwnerUid . '（来源：分店访问）');
                    }
                }
            }
        }
    }
}

if (!function_exists('dcTemplateSlugEnabled')) {
    function dcTemplateSlugEnabled($basePath, $slug) {
        $slug = trim((string)$slug);
        if ($slug === '' || $slug === 'em_null_tpl') {
            return false;
        }
        $safeSlug = preg_replace('/^([\w-]+)$/i', '$1', $slug);
        return $safeSlug === $slug
            && is_dir(rtrim($basePath, '/\\') . '/' . $safeSlug . '/')
            && checkTemplateBootstrap($basePath, $safeSlug);
    }
}

if (!function_exists('dcTemplateSlugDisabled')) {
    function dcTemplateSlugDisabled($slug) {
        $slug = trim((string)$slug);
        return $slug === '' || $slug === 'em_null_tpl';
    }
}

if (!function_exists('dcBuildMobileShellUrl')) {
    function dcBuildMobileShellUrl($uri) {
        $uri = (string)$uri;
        if ($uri === '') {
            $uri = '/';
        }
        $hash = '';
        $hashPos = strpos($uri, '#');
        if ($hashPos !== false) {
            $hash = substr($uri, $hashPos);
            $uri = substr($uri, 0, $hashPos);
        }
        if (preg_match('/(?:\?|&)__dc_mobile_shell=1(?:&|$)/', $uri)) {
            return $uri . $hash;
        }
        return $uri . (strpos($uri, '?') === false ? '?' : '&') . '__dc_mobile_shell=1' . $hash;
    }
}

if (!function_exists('dcRenderMobileTemplateShell')) {
    function dcRenderMobileTemplateShell($iframeUrl, $kind = 'front') {
        $title = trim(strip_tags((string)Option::get('blogname')));
        if ($title === '') {
            $title = trim(strip_tags((string)Option::get('site_title')));
        }
        if ($title === '') {
            $title = $kind === 'user' ? '用户中心' : '商城首页';
        }
        $tip = trim(strip_tags((string)Option::get('site_subtitle')));
        if ($tip === '') {
            $tip = trim(strip_tags((string)Option::get('bloginfo')));
        }
        if ($tip === '') {
            $tip = trim(strip_tags((string)Option::get('site_description')));
        }
        if ($tip === '') {
            $tip = '欢迎访问' . $title;
        }
        $kicker = $kind === 'user' ? '用户中心' : '移动商城';
        $_qr_target_raw = (string)$iframeUrl;
        if (preg_match('#^https?://#i', $_qr_target_raw)) {
            $_qr_absolute = $_qr_target_raw;
        } else {
            $_qr_absolute = rtrim(DC_URL, '/') . '/' . ltrim($_qr_target_raw, '/');
        }
        $_qr_parts = parse_url($_qr_absolute);
        $_qr_query = [];
        if (!empty($_qr_parts['query'])) {
            parse_str($_qr_parts['query'], $_qr_query);
        }
        unset($_qr_query['__dc_mobile_shell']);
        $_qr_scheme = $_qr_parts['scheme'] ?? ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
        $_qr_host = $_qr_parts['host'] ?? ($_SERVER['HTTP_HOST'] ?? '');
        $_qr_port = isset($_qr_parts['port']) ? ':' . $_qr_parts['port'] : '';
        $_qr_path = $_qr_parts['path'] ?? '/';
        $_qr_query_string = http_build_query($_qr_query);
        $_qr_fragment = isset($_qr_parts['fragment']) ? '#' . $_qr_parts['fragment'] : '';
        $qrUrl = $_qr_scheme . '://' . $_qr_host . $_qr_port . $_qr_path . ($_qr_query_string !== '' ? '?' . $_qr_query_string : '') . $_qr_fragment;
        $iframeUrl = htmlspecialchars($iframeUrl, ENT_QUOTES, 'UTF-8');
        $titleEsc = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $tipEsc = htmlspecialchars($tip, ENT_QUOTES, 'UTF-8');
        $kickerEsc = htmlspecialchars($kicker, ENT_QUOTES, 'UTF-8');
        $qrUrlEsc = htmlspecialchars($qrUrl, ENT_QUOTES, 'UTF-8');
        $qrUrlJson = json_encode($qrUrl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $qrLibUrl = htmlspecialchars(rtrim(DC_URL, '/') . '/content/static/js/qrcode.min.js', ENT_QUOTES, 'UTF-8');
        echo <<<HTML
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$titleEsc}</title>
    <style>
        * { box-sizing: border-box; }
        html, body { width: 100%; min-height: 100%; margin: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Microsoft YaHei", sans-serif;
            color: #172033;
            background:
                radial-gradient(circle at 18% 18%, rgba(33,150,243,.22), transparent 30%),
                radial-gradient(circle at 82% 12%, rgba(99,102,241,.18), transparent 28%),
                linear-gradient(135deg, #eef5ff 0%, #f8fbff 44%, #eef2f7 100%);
            overflow-x: hidden;
        }
        .dc-mobile-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 44px;
            padding: 34px 28px;
        }
        .dc-mobile-shell-info {
            width: min(360px, 34vw);
        }
        .dc-mobile-shell-kicker {
            display: inline-flex;
            align-items: center;
            height: 28px;
            padding: 0 12px;
            border-radius: 999px;
            background: rgba(33,150,243,.11);
            color: #0b7bd3;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 16px;
        }
        .dc-mobile-shell-info h1 {
            margin: 0 0 12px;
            font-size: 34px;
            line-height: 1.18;
            letter-spacing: -.6px;
        }
        .dc-mobile-shell-info p {
            margin: 0;
            color: #64748b;
            font-size: 15px;
            line-height: 1.9;
        }
        .dc-mobile-shell-qr {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 24px;
            padding: 12px;
            border: 1px solid rgba(148, 163, 184, .22);
            border-radius: 18px;
            background: rgba(255, 255, 255, .72);
            box-shadow: 0 14px 34px rgba(15, 23, 42, .08);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .dc-mobile-shell-qr-box {
            width: 118px;
            height: 118px;
            padding: 8px;
            border-radius: 14px;
            background: #fff;
            box-shadow: inset 0 0 0 1px rgba(15, 23, 42, .06);
            flex-shrink: 0;
        }
        .dc-mobile-shell-qr-box canvas,
        .dc-mobile-shell-qr-box img {
            display: block;
            width: 102px !important;
            height: 102px !important;
            border-radius: 8px;
        }
        .dc-mobile-shell-qr-text strong {
            display: block;
            font-size: 15px;
            color: #172033;
            margin-bottom: 6px;
        }
        .dc-mobile-shell-qr-text span {
            display: block;
            color: #64748b;
            font-size: 13px;
            line-height: 1.7;
        }
        .dc-mobile-shell-qr-url {
            display: block;
            max-width: 180px;
            margin-top: 7px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            color: #94a3b8;
            font-size: 12px;
        }
        .dc-mobile-shell-phone {
            position: relative;
            width: 430px;
            height: min(860px, calc(100vh - 64px));
            min-height: 680px;
            padding: 14px;
            border-radius: 46px;
            background: linear-gradient(145deg, #202838, #0e1420);
            box-shadow: 0 34px 80px rgba(15, 23, 42, .24), inset 0 0 0 1px rgba(255,255,255,.08);
        }
        .dc-mobile-shell-screen {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
            border-radius: 36px;
            background: #fff;
            box-shadow: inset 0 0 0 1px rgba(15, 23, 42, .08);
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .dc-mobile-shell-screen::-webkit-scrollbar {
            width: 0;
            height: 0;
            display: none;
        }
        .dc-mobile-shell-screen iframe {
            display: block;
            width: 100%;
            height: 100%;
            border: 0;
            background: #fff;
        }
        @media (max-width: 980px) {
            .dc-mobile-shell { padding: 0; min-height: 100vh; display: block; }
            .dc-mobile-shell-info { display: none; }
            .dc-mobile-shell-phone {
                width: 100vw;
                height: 100vh;
                min-height: 100vh;
                padding: 0;
                border-radius: 0;
                box-shadow: none;
                background: #fff;
            }
            .dc-mobile-shell-screen { border-radius: 0; }
        }
    </style>
</head>
<body>
    <div class="dc-mobile-shell">
        <section class="dc-mobile-shell-info">
            <div class="dc-mobile-shell-kicker">{$kickerEsc}</div>
            <h1>{$titleEsc}</h1>
            <p>{$tipEsc}</p>
            <div class="dc-mobile-shell-qr">
                <div class="dc-mobile-shell-qr-box" id="dcMobileShellQr" aria-label="网站访问二维码"></div>
                <div class="dc-mobile-shell-qr-text">
                    <strong>手机扫码访问</strong>
                    <span>用手机扫一扫，快速打开当前网站。</span>
                    <small class="dc-mobile-shell-qr-url" title="{$qrUrlEsc}">{$qrUrlEsc}</small>
                </div>
            </div>
        </section>
        <main class="dc-mobile-shell-phone" aria-label="手机外形预览">
            <div class="dc-mobile-shell-screen">
                <iframe id="dcMobileFrame" src="{$iframeUrl}" title="{$titleEsc}" referrerpolicy="same-origin"></iframe>
            </div>
        </main>
    </div>
    <script src="{$qrLibUrl}"></script>
    <script>
    (function(){
        var frame = document.getElementById('dcMobileFrame');
        var flag = '__dc_mobile_shell';
        var qrTarget = {$qrUrlJson};
        var qrBox = document.getElementById('dcMobileShellQr');
        if (qrBox && typeof QRCode !== 'undefined') {
            try {
                new QRCode(qrBox, {
                    text: qrTarget,
                    width: 102,
                    height: 102,
                    colorDark: '#172033',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });
            } catch (e) {
                qrBox.textContent = 'QR';
            }
        } else if (qrBox) {
            qrBox.textContent = 'QR';
        }
        function withFlag(raw) {
            try {
                var url = new URL(raw, window.location.href);
                if (url.origin !== window.location.origin) return raw;
                url.searchParams.set(flag, '1');
                return url.pathname + url.search + url.hash;
            } catch (e) {
                return raw;
            }
        }
        function patchFrameLinks(doc) {
            if (!doc) return;
            var links = doc.querySelectorAll('a[href]');
            links.forEach(function(a){
                var href = a.getAttribute('href') || '';
                if (!href || href.charAt(0) === '#' || /^(javascript:|mailto:|tel:)/i.test(href)) return;
                if ((a.getAttribute('target') || '').toLowerCase() === '_blank') return;
                a.setAttribute('href', withFlag(href));
            });
            var forms = doc.querySelectorAll('form');
            forms.forEach(function(form){
                if (!form.querySelector('input[name="' + flag + '"]')) {
                    var input = doc.createElement('input');
                    input.type = 'hidden';
                    input.name = flag;
                    input.value = '1';
                    form.appendChild(input);
                }
            });
        }
        function injectFrameScrollbarStyle(doc) {
            if (!doc || !doc.head) return;
            var oldStyle = doc.getElementById('dcMobileShellScrollbarStyle');
            if (oldStyle && oldStyle.parentNode) oldStyle.parentNode.removeChild(oldStyle);
            var style = doc.createElement('style');
            style.id = 'dcMobileShellScrollbarStyle';
            style.textContent = ''
                + 'html,body,*{scrollbar-width:none!important;-ms-overflow-style:none!important;}'
                + 'html::-webkit-scrollbar,body::-webkit-scrollbar,*::-webkit-scrollbar{width:0!important;height:0!important;display:none!important;background:transparent!important;}'
                + 'html::-webkit-scrollbar-track,body::-webkit-scrollbar-track,*::-webkit-scrollbar-track{display:none!important;background:transparent!important;}'
                + 'html::-webkit-scrollbar-thumb,body::-webkit-scrollbar-thumb,*::-webkit-scrollbar-thumb{display:none!important;background:transparent!important;}'
                + 'html::-webkit-scrollbar-corner,body::-webkit-scrollbar-corner,*::-webkit-scrollbar-corner{display:none!important;background:transparent!important;}';
            doc.head.appendChild(style);
        }
        frame.addEventListener('load', function(){
            try {
                var win = frame.contentWindow;
                if (!win || win.location.origin !== window.location.origin) return;
                if (win.location.search.indexOf(flag + '=1') === -1) {
                    win.location.replace(withFlag(win.location.href));
                    return;
                }
                patchFrameLinks(win.document);
                injectFrameScrollbarStyle(win.document);
                setTimeout(function(){ injectFrameScrollbarStyle(win.document); }, 80);
                setTimeout(function(){ injectFrameScrollbarStyle(win.document); }, 400);
            } catch (e) {}
        });
    })();
    </script>
</body>
</html>
HTML;
        exit;
    }
}

$_dc_script_path = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
$_dc_script_base = basename($_dc_script_path);
$_dc_is_admin_request = strpos($_dc_script_path, '/admin/') !== false;
$_dc_is_user_request = preg_match('#/user(?:/|$)#i', $_dc_script_path) === 1;
$_dc_is_user_internal = $_dc_is_user_request && in_array(strtolower($_dc_script_base), ['api.php', 'template.php'], true);
$_dc_request_method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$_dc_is_get_page = in_array($_dc_request_method, ['GET', 'HEAD'], true);
$_dc_is_ajax_request = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
$_dc_action_name = strtolower((string)($_GET['action'] ?? ''));
$_dc_is_ajax_action = $_dc_action_name !== '' && strpos($_dc_action_name, 'ajax') !== false;
$_dc_is_shell_param = isset($_REQUEST['__dc_mobile_shell']) && (string)$_REQUEST['__dc_mobile_shell'] === '1';
$_dc_is_iframe_request = strtolower((string)($_SERVER['HTTP_SEC_FETCH_DEST'] ?? '')) === 'iframe';
$_dc_real_mobile_client = isMobile();
$_dc_shell_kind = '';
$_dc_pc_tpl = '';
$_dc_tel_tpl = '';
$_dc_tpl_base = TPLS_PATH;

if (!$_dc_is_admin_request && !$_dc_is_user_internal) {
    if ($_dc_is_user_request) {
        $_dc_shell_kind = 'user';
        $_dc_pc_tpl = Option::get('nonce_user_tpl');
        $_dc_tel_tpl = Option::get('nonce_user_tpl_tel');
        $_dc_tpl_base = USER_TPLS_PATH;
    } elseif ($_dc_script_base === 'index.php') {
        $_dc_shell_kind = 'front';
        if (empty($stationData['id']) || (int)$stationData['id'] === 0) {
            $_dc_pc_tpl = Option::get('nonce_templet');
            $_dc_tel_tpl = Option::get('nonce_templet_tel');
        } else {
            $_dc_pc_tpl = $stationData['pc_tpl'] ?? '';
            $_dc_tel_tpl = $stationData['tel_tpl'] ?? '';
        }
        $_dc_tpl_base = TPLS_PATH;
    }
}

$_dc_pc_tpl_disabled = $_dc_shell_kind !== '' && dcTemplateSlugDisabled($_dc_pc_tpl);
$_dc_tel_tpl_enabled = $_dc_shell_kind !== '' && dcTemplateSlugEnabled($_dc_tpl_base, $_dc_tel_tpl);
$_dc_should_force_mobile_shell_content = ($_dc_is_shell_param || $_dc_is_iframe_request) && $_dc_pc_tpl_disabled && $_dc_tel_tpl_enabled;

if ($_dc_should_force_mobile_shell_content && !defined('DC_FORCE_MOBILE_TEMPLATE')) {
    $GLOBALS['__dc_force_mobile_template'] = true;
    define('DC_FORCE_MOBILE_TEMPLATE', true);
}

if (
    $_dc_should_force_mobile_shell_content
    && !$_dc_is_ajax_request
    && !$_dc_is_ajax_action
    && empty($GLOBALS['__dc_mobile_shell_hide_scrollbar_buffer'])
) {
    $GLOBALS['__dc_mobile_shell_hide_scrollbar_buffer'] = true;
    ob_start(function($buffer) {
        if (stripos((string)$buffer, '</head>') === false || stripos((string)$buffer, 'dc-mobile-shell-hide-scrollbar') !== false) {
            return $buffer;
        }
        $style = '<style id="dc-mobile-shell-hide-scrollbar">html,body,*{scrollbar-width:none!important;-ms-overflow-style:none!important;}html::-webkit-scrollbar,body::-webkit-scrollbar,*::-webkit-scrollbar{width:0!important;height:0!important;display:none!important;background:transparent!important;}html::-webkit-scrollbar-track,body::-webkit-scrollbar-track,*::-webkit-scrollbar-track,html::-webkit-scrollbar-thumb,body::-webkit-scrollbar-thumb,*::-webkit-scrollbar-thumb,html::-webkit-scrollbar-corner,body::-webkit-scrollbar-corner,*::-webkit-scrollbar-corner{display:none!important;background:transparent!important;}</style>';
        return preg_replace('/<\/head>/i', $style . '</head>', $buffer, 1);
    });
}

if (
    !$_dc_real_mobile_client
    && !$_dc_should_force_mobile_shell_content
    && $_dc_is_get_page
    && !$_dc_is_ajax_request
    && !$_dc_is_ajax_action
    && $_dc_pc_tpl_disabled
    && $_dc_tel_tpl_enabled
) {
    dcRenderMobileTemplateShell(dcBuildMobileShellUrl($_SERVER['REQUEST_URI'] ?? '/'), $_dc_shell_kind);
}

if($stationData['id'] == 0){
    $nonce_templet = isMobile() ? Option::get('nonce_templet_tel') : Option::get('nonce_templet');
    define("TPLS_URL", DC_URL . 'content/templates/');
    define('TEMPLATE_PATH', TPLS_PATH . $nonce_templet . '/');
}else{
    $nonce_templet = isMobile() ? $stationData['tel_tpl']: $stationData['pc_tpl'];
    define("TPLS_URL", DC_URL . 'content/templates/');
    define('TEMPLATE_PATH', TPLS_PATH . $nonce_templet . '/');
}


//echo TEMPLATE_PATH;die;

//站点URL
define('DYNAMIC_BLOGURL', Option::get('blogurl'));
//当前模板的URL
define('TEMPLATE_URL', TPLS_URL . $nonce_templet . '/');
$nonce_blog_tpl = trim((string)(isMobile() ? Option::get('nonce_blog_tpl_tel') : Option::get('nonce_blog_tpl')));
if ($nonce_blog_tpl === '') {
    $nonce_blog_tpl = trim((string)(Option::get('nonce_blog_tpl') ?: 'default'));
}
$safe_blog_tpl = preg_replace('/^([\w-]+)$/i', '$1', $nonce_blog_tpl);
if ($safe_blog_tpl !== $nonce_blog_tpl || !is_dir(BLOG_TPLS_PATH . $safe_blog_tpl . '/') || !checkTemplateBootstrap(BLOG_TPLS_PATH, $safe_blog_tpl)) {
    $safe_blog_tpl = 'default';
}
define('BLOG_TEMPLATE_URL', DC_URL . 'content/blog_templates/' . $safe_blog_tpl . '/');
//后台模板的绝对路径
define('ADMIN_TEMPLATE_PATH', DC_ROOT . '/admin/views/');
define('USER_TEMPLATE_PATH', DC_ROOT . '/user/views/');
//前台模板的绝对路径

define('BLOG_TEMPLATE_PATH', BLOG_TPLS_PATH . $safe_blog_tpl . '/');
define('COMMON_TEMPLATE_PATH', DC_ROOT . '/content/common/');

const MSGCODE_EMKEY_INVALID = 1001;
const MSGCODE_NO_UPUPDATE = 1002;
const MSGCODE_SUCCESS = 200;

define('DC_LINE', (function ($payload, $key) {
    $raw = base64_decode($payload, true);
    if ($raw === false || $key === '') {
        return [];
    }
    $out = '';
    $keyLen = strlen($key);
    for ($i = 0, $len = strlen($raw); $i < $len; $i++) {
        $out .= chr(ord($raw[$i]) ^ ord($key[$i % $keyLen]));
    }
    $data = json_decode($out, true);
    return is_array($data) ? $data : [];
})('bk9AC1VYV0QKG4DM/NX0i4aNjYqPzYanlRZOR0JUXhNVG19ADEcWQhINHU1cARdeWkRTS0xPQQUeWgZNRk5OSUNZUw9dQF4U0Jr6g6KM1dyP0dLNhqLDEE0VRANUFwEUDxYKEUBFQVwfFgEBF1sNQlMZShhLAUpVVhtAGBhOEAhRVABAXhGHnPnRpNvf2NvegpuA9JYXHkRGWAkXARFYEAlDRhJLWEsZUVcRDVtFHB5KSgZMB1BNEBxq', '54be452f09ebd3b2a72b8bd6'));

$options_cache = $CACHE->readCache('options');
$dc_line_index = isset($options_cache['dc_line']) ? (int)$options_cache['dc_line'] : 0;
define('CURRENT_LINE', isset(DC_LINE[$dc_line_index]) ? $dc_line_index : 0);

/**
 * 带线路故障转移的 curl 请求
 * 当前线路失败时自动尝试其他线路，成功后自动切换并保存
 *
 * @param string $path      相对路径（如 'api.php?action=checkDomain'）
 * @param string $postData  POST 数据（空字符串或 false 表示 GET）
 * @param bool   $isPost    是否 POST 请求
 * @param int    $timeout   单条线路超时秒数（建议 2-3，因为有备选线路）
 * @param array  $header    自定义请求头
 * @return string|false     响应内容，全部失败返回 false
 */
if (!function_exists('dcRequestWithFailover')) {
    function dcRequestWithFailover($path, $postData = '', $isPost = true, $timeout = 2, $header = []) {
        $path = '/' . ltrim($path, '/');

        // 先尝试当前线路
        $currentUrl = rtrim(DC_LINE[CURRENT_LINE]['value'], '/') . $path;
        $res = emCurl($currentUrl, $postData, $isPost, $header ?: false, $timeout);

        if (!empty($res)) {
            return $res; // 当前线路正常，直接返回
        }

        // 当前线路失败，遍历其他线路尝试
        foreach (DC_LINE as $key => $line) {
            if ($key === CURRENT_LINE) {
                continue;
            }

            $url = rtrim($line['value'], '/') . $path;
            $res = emCurl($url, $postData, $isPost, $header ?: false, $timeout);

            if (!empty($res)) {
                // 找到可用线路，静默切换并保存到数据库
                dcAutoSwitchLine($key);
                return $res;
            }
        }

        return false; // 所有线路都失败
    }
}

/**
 * 自动切换线路（静默保存到数据库 + 更新缓存）
 * @param int $lineKey 目标线路索引
 */
if (!function_exists('dcAutoSwitchLine')) {
    function dcAutoSwitchLine($lineKey) {
        try {
            if (class_exists('Option') && method_exists('Option', 'updateOption')) {
                Option::updateOption('dc_line', $lineKey);
            }
            global $CACHE;
            if ($CACHE && method_exists($CACHE, 'updateCache')) {
                $CACHE->updateCache('options');
            }
        } catch (Exception $e) {
            // 静默失败，不影响当前请求
        }
    }
}

$active_plugins = Option::get('active_plugins');
$emHooks = [];
$plugin_errors = []; // 记录插件加载错误

// 授权守护已移除
// require_once DC_ROOT . '/include/lib/license_guard.php';

// 批量预热插件授权缓存（授权已移除，直接加载所有插件）
if ($active_plugins && is_array($active_plugins)) {
    // PluginLicense::preWarm($active_plugins); // 授权已移除
}

if ($active_plugins && is_array($active_plugins)) {
    foreach ($active_plugins as $plugin) {
        if (true === checkPlugin($plugin)) {
            try {
                // 设置自定义错误处理，捕获 fatal error 之前的错误
                $plugin_file = DC_ROOT . '/content/plugins/' . $plugin;

                // 先检查文件语法（可选，PHP 7+）
                if (function_exists('opcache_compile_file')) {
                    @opcache_invalidate($plugin_file, true);
                }

                include_once($plugin_file);
            } catch (Throwable $e) {
                // 捕获所有错误（PHP 7+），包括 Error 和 Exception
                $plugin_errors[] = [
                    'plugin' => $plugin,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ];
                // 记录错误日志
                error_log("Plugin load error [{$plugin}]: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            }
        }
    }
}

// 所有插件加载完毕
doAction('plugins_loaded');

require_once DC_ROOT . '/include/lib/api_callback.php';

// 如果有插件加载错误，存储到全局变量供后台显示
if (!empty($plugin_errors)) {
    $GLOBALS['plugin_load_errors'] = $plugin_errors;
}


// 加载模板的系统调用文件
define('TEMPLATE_HOOK_PATH', TEMPLATE_PATH . 'plugins.php');
if (file_exists(TEMPLATE_HOOK_PATH)) {
    include_once(TEMPLATE_HOOK_PATH);
}


if (!function_exists('dcDemoModeEnabled')) {
    /**
     * 演示站全局保护开关。
     *
     * 这里只做“底线保护”，不再拦截所有 POST。演示站的意义是让用户可以真实体验
     * 配置、保存、下单等常规流程；全局层只拦截删除、插件/模板上传安装、
     * 应用商店购买/充值、备份文件下载、安装升级、导入导出等可能破坏演示环境
     * 或消耗真实授权账户余额的高风险操作。
     */
    function dcDemoModeEnabled() {
        return (defined('DEMO') && DEMO === true) || (defined('DEMO_MODE') && DEMO_MODE === true);
    }
}

if (!function_exists('dcDemoNormalizeAction')) {
    function dcDemoNormalizeAction($action) {
        return strtolower(str_replace('-', '_', trim((string)$action)));
    }
}

if (!function_exists('dcDemoRequestBlocked')) {
    function dcDemoRequestBlocked($action, $filename = '') {
        $action = dcDemoNormalizeAction($action);
        $filename = trim((string)$filename);
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');

        // 明确保留的体验/检测类接口。
        $safeActions = [
            'check_update',
            'update_line',
            'goods_price_stock',
            'delete_cache',
            'clear_cache'
        ];
        if (in_array($action, $safeActions, true)) {
            return false;
        }

        // 应用商店购买、试用、月付订阅、短信包购买、授权账户充值会调用授权中心并可能
        // 消耗真实余额/生成真实订单；只按 admin/store.php 入口精确拦截，避免误伤前台商品下单。
        $storeBlockedActions = [
            'buy',
            'buy_sms_pack',
            'create_recharge'
        ];
        if (preg_match('#/admin/store\.php$#i', $script) && in_array($action, $storeBlockedActions, true)) {
            return true;
        }

        $blockedExactActions = [
            'delete',
            'del',
            'remove',
            'destroy',
            'drop',
            'truncate',
            'permanent_delete',
            'empty_all',
            'upload',
            'export',
            'import',
            'backup',
            'install',
            'uninstall',
            'update',
            'reset_default'
        ];
        if (in_array($action, $blockedExactActions, true)) {
            return true;
        }

        $blockedPatterns = [
            '/(^|_)(delete|del|remove|destroy|drop|truncate)(_|$)/',
            '/(^|_)(permanent_delete|empty_all)(_|$)/',
            '/(^|_)(upload|export|import|backup|install|uninstall)(_|$)/',
            '/(^|_)(backup|database|db|system)_?restore(_|$)/',
            '/(^|_)restore_?(backup|database|db|system)(_|$)/',
            '/^reset_(default|data|system|config|setting|settings|member|template|tpl|plugin|goods|user|station)(_|$)/',
            '/^(system|core|db|database)_?(update|upgrade)$/',
            '/^(update|upgrade)_(system|core|db|database)$/',
            '/^calibrate(_|$)/'
        ];
        foreach ($blockedPatterns as $pattern) {
            if ($action !== '' && preg_match($pattern, $action)) {
                return true;
            }
        }

        // 管理后台临时文件下载没有 action，单独按入口和敏感扩展名兜底；
        // 商品订单卡密下载属于购买体验，不在这里按 download 动作一刀切拦截。
        if ($filename !== '') {
            if (preg_match('#/admin/download\.php$#i', $script)) {
                return true;
            }
            if (preg_match('/\.(sql|zip|rar|7z|tar|gz|bak|php|phtml|ini|env|log)$/i', $filename)) {
                return true;
            }
        }

        return false;
    }
}

if (dcDemoModeEnabled()) {
    $action = Input::getStrVar('action');
    $filename = Input::getStrVar('filename');
    if (dcDemoRequestBlocked($action, $filename)) {
        Ret::error('演示站点无法进行该操作！');
    }
}
if (!class_exists('TplOptions', false)) {
//    echo __DIR__;die;
    include __DIR__ . '/include/lib/tpl_options.php';

}
TplOptions::getInstance()->init();


