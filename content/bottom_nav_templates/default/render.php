<?php
defined('DC_ROOT') || exit('access denied!');

require_once __DIR__ . '/default_setting.php';

if (!function_exists('bottomNavDefaultHexToRgb')) {
    function bottomNavDefaultHexToRgb($hex, $fallback = '33, 150, 243')
    {
        $hex = trim((string)$hex);
        if (!preg_match('/^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i', $hex, $matches)) {
            return $fallback;
        }
        return hexdec($matches[1]) . ', ' . hexdec($matches[2]) . ', ' . hexdec($matches[3]);
    }
}

if (!function_exists('bottomNavDefaultAlphaColor')) {
    function bottomNavDefaultAlphaColor($hex, $alpha, $fallbackRgb = '33, 150, 243')
    {
        $alpha = max(0, min(100, (int)$alpha));
        return 'rgba(' . bottomNavDefaultHexToRgb($hex, $fallbackRgb) . ', ' . ($alpha / 100) . ')';
    }
}

if (!function_exists('bottomNavDefaultSanitizeIconClass')) {
    function bottomNavDefaultSanitizeIconClass($icon)
    {
        $icon = preg_replace('/[^a-zA-Z0-9\s_-]/', '', (string)$icon);
        return trim($icon) !== '' ? trim($icon) : 'ri-apps-2-line';
    }
}

if (!function_exists('bottomNavDefaultBuildIconHtml')) {
    function bottomNavDefaultBuildIconHtml($item)
    {
        $iconImage = bottomNavDefaultSanitizeIconImage($item['icon_image'] ?? '');
        if ($iconImage !== '') {
            return '<img src="' . htmlspecialchars($iconImage, ENT_QUOTES) . '" class="nav-icon nav-icon-image" alt="' . htmlspecialchars((string)($item['title'] ?? '图标'), ENT_QUOTES) . '">';
        }
        return '<i class="' . htmlspecialchars(bottomNavDefaultSanitizeIconClass($item['icon'] ?? ''), ENT_QUOTES) . ' nav-icon"></i>';
    }
}

if (!function_exists('bottomNavDefaultResolveSortIdFromText')) {
    function bottomNavDefaultResolveSortIdFromText($text)
    {
        $text = trim((string)$text);
        if ($text === '') {
            return 0;
        }
        if (preg_match('/(?:^|[?&])sort_id=(\d+)/i', $text, $matches)) {
            return (int)$matches[1];
        }
        if (preg_match('/(?:^|[?&])sort=(\d+)/i', $text, $matches)) {
            return (int)$matches[1];
        }
        if (preg_match('/sort_id=(\d+)/i', $text, $matches)) {
            return (int)$matches[1];
        }
        $path = (string)(parse_url($text, PHP_URL_PATH) ?: '');
        if ($path === '' && strpos($text, 'sort/') === 0) {
            $path = $text;
        }
        $path = trim(strtolower(str_replace('\\', '/', $path)), '/');
        if ($path === '') {
            return 0;
        }
        $segments = array_values(array_filter(explode('/', $path), 'strlen'));
        $sortIndex = array_search('sort', $segments, true);
        if ($sortIndex === false) {
            return 0;
        }
        $sortKey = '';
        for ($i = $sortIndex + 1; $i < count($segments); $i++) {
            if ($segments[$i] === 'page') {
                break;
            }
            $sortKey = urldecode($segments[$i]);
        }
        if ($sortKey === '') {
            return 0;
        }
        if (ctype_digit($sortKey)) {
            return (int)$sortKey;
        }
        $CACHE = Cache::getInstance();
        $sortCache = $CACHE->readCache('sort');
        foreach ($sortCache as $sid => $sortInfo) {
            $alias = isset($sortInfo['alias']) ? strtolower((string)$sortInfo['alias']) : '';
            if ($alias !== '' && $alias === strtolower($sortKey)) {
                return (int)$sid;
            }
        }
        return 0;
    }
}

if (!function_exists('bottomNavDefaultResolveCurrentSortId')) {
    function bottomNavDefaultResolveCurrentSortId($currentUri, $localSortId = null)
    {
        if ((int)$localSortId > 0) {
            return (int)$localSortId;
        }
        if (isset($_GET['sort_id']) && (int)$_GET['sort_id'] > 0) {
            return (int)$_GET['sort_id'];
        }
        if (isset($_GET['sort']) && (int)$_GET['sort'] > 0) {
            return (int)$_GET['sort'];
        }
        return bottomNavDefaultResolveSortIdFromText($currentUri);
    }
}

if (!function_exists('bottomNavDefaultTokenMatches')) {
    function bottomNavDefaultTokenMatches($token, $currentAction, $currentUri, $currentSortId = null)
    {
        $token = strtolower(trim((string)$token));
        if ($token === '') {
            return false;
        }
        
        // 通配全部分类页
        if (in_array($token, ['sort', 'sort_id', 'sort=*', 'sort_id=*', 'category'], true)) {
            if ($currentSortId !== null && $currentSortId > 0) {
                return true;
            }
        }

        $currentAction = strtolower((string)$currentAction);
        $currentUri = strtolower((string)$currentUri);
        $currentSortId = $currentSortId === null ? bottomNavDefaultResolveCurrentSortId($currentUri) : (int)$currentSortId;
        
        $tokenSortId = bottomNavDefaultResolveSortIdFromText($token);
        if ($tokenSortId > 0 && $currentSortId > 0 && $tokenSortId === $currentSortId) {
            return true;
        }
        return $currentAction === $token || strpos($currentUri, $token) !== false;
    }
}

if (!function_exists('bottomNavDefaultIsCustomActive')) {
    function bottomNavDefaultIsCustomActive($item, $currentAction, $currentUri, $currentSortId = null)
    {
        $rules = preg_split('/\r\n|\r|\n/', (string)($item['match_rule'] ?? ''));
        $tokens = [];
        foreach ($rules as $rule) {
            $rule = trim($rule);
            if ($rule !== '') {
                $tokens[] = strtolower($rule);
            }
        }
        if (empty($tokens) && !empty($item['url'])) {
            $url = (string)$item['url'];
            $tokens[] = strtolower($url);
            $query = parse_url($url, PHP_URL_QUERY);
            $path = parse_url($url, PHP_URL_PATH);
            if (!empty($query)) {
                parse_str($query, $queryData);
                if (!empty($queryData['action'])) {
                    $tokens[] = strtolower((string)$queryData['action']);
                }
            }
            if (!empty($path)) {
                $path = trim(str_replace('\\', '/', $path), '/');
                if ($path !== '') {
                    $tokens[] = strtolower($path);
                }
            }
        }
        $currentAction = strtolower((string)$currentAction);
        $currentUri = strtolower((string)$currentUri);
        foreach ($tokens as $token) {
            if ($token === '') {
                continue;
            }
            if (bottomNavDefaultTokenMatches($token, $currentAction, $currentUri, $currentSortId)) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('bottomNavDefaultMatchLinkRules')) {
    function bottomNavDefaultMatchLinkRules($rulesText, $currentAction, $currentUri, $currentSortId = null)
    {
        $rules = preg_split('/\r\n|\r|\n/', (string)$rulesText);
        $currentAction = strtolower((string)$currentAction);
        $currentUri = strtolower((string)$currentUri);
        foreach ($rules as $rule) {
            $rule = strtolower(trim((string)$rule));
            if ($rule === '') {
                continue;
            }
            if (bottomNavDefaultTokenMatches($rule, $currentAction, $currentUri, $currentSortId)) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('bottomNavDefaultIsGoodsDetailPage')) {
    function bottomNavDefaultIsGoodsDetailPage($currentUri, $goodsData = null)
    {
        if (is_array($goodsData) && !empty($goodsData['id'])) {
            return true;
        }
        if (isset($_GET['post']) && (int)$_GET['post'] > 0) {
            return true;
        }
        $currentUri = strtolower((string)$currentUri);
        if ($currentUri === '') {
            return false;
        }
        if (preg_match('/[?&]post=\d+/i', $currentUri)) {
            return true;
        }
        $path = strtolower((string)(parse_url($currentUri, PHP_URL_PATH) ?: ''));
        if ($path !== '' && preg_match('#(?:^|/)(post-\d+\.html|post/\d+)(?:$|/)#i', $path)) {
            return true;
        }
        return false;
    }
}

if (!function_exists('bottomNavDefaultIsBlogPage')) {
    function bottomNavDefaultIsBlogPage($currentUri)
    {
        $currentUri = (string)$currentUri;
        if ($currentUri === '') {
            return false;
        }
        // Blog independent domain - all pages are blog pages
        if (defined('IS_BLOG_DOMAIN') && IS_BLOG_DOMAIN) {
            return true;
        }
        // ?blog=N (blog article detail)
        if (isset($_GET['blog']) && (int)$_GET['blog'] > 0) {
            return true;
        }
        // ?blogsort=N (blog category)
        if (isset($_GET['blogsort'])) {
            return true;
        }
        $path = strtolower((string)(parse_url($currentUri, PHP_URL_PATH) ?: ''));
        $path = rtrim($path, '/');
        // /blog or /blog/page/N (blog list)
        if (preg_match('#(?:^|/)blog(?:/page/\d+)?$#i', $path)) {
            return true;
        }
        // /blog-N.html (blog article rewrite mode 1)
        if (preg_match('#(?:^|/)blog-\d+\.html$#i', $path)) {
            return true;
        }
        // /blog/N (blog article rewrite mode 2)
        if (preg_match('#(?:^|/)blog/\d+$#i', $path)) {
            return true;
        }
        // /blogsort/X (blog category rewrite)
        if (preg_match('#(?:^|/)blogsort(?:/|$)#i', $path)) {
            return true;
        }
        // /tag/ pages within blog context
        if (preg_match('#(?:^|/)tag(?:/|$)#i', $path) && defined('BLOG_TEMPLATE_PATH')) {
            return true;
        }
        // /author/ pages within blog context
        if (preg_match('#(?:^|/)author(?:/|$)#i', $path) && defined('BLOG_TEMPLATE_PATH')) {
            return true;
        }
        // /record (archive) within blog context
        if (preg_match('#(?:^|/)record(?:/|$)#i', $path) && defined('BLOG_TEMPLATE_PATH')) {
            return true;
        }
        return false;
    }
}

$_config = bottomNavDefaultConfig('default');
$_current_action = isset($_GET['action']) ? $_GET['action'] : '';
$_current_uri = $_SERVER['REQUEST_URI'] ?? '';
$_local_sort_id = isset($sortid) ? $sortid : (isset($sort_id) ? $sort_id : (isset($current_sort) ? $current_sort : null));
$_current_sort_id = bottomNavDefaultResolveCurrentSortId($_current_uri, $_local_sort_id);
$_is_sort_page = $_current_sort_id > 0;
$_current_path = strtolower((string)(parse_url((string)$_current_uri, PHP_URL_PATH) ?: ''));
$_is_user_path = preg_match('#(?:^|/)user(?:/|$)#i', $_current_path) === 1;
$_has_search_query = (isset($_GET['q']) && trim((string)$_GET['q']) !== '') || preg_match('/(?:^|[?&])q=[^&]*/i', (string)$_current_uri);
$_has_goods_order = (((isset($_GET['order']) && trim((string)$_GET['order']) !== '') || preg_match('/(?:^|[?&])order=[^&]*/i', (string)$_current_uri)) && (empty($_current_action) || $_current_action === 'index') && !$_is_user_path);
$_is_order_query_contact = (($_current_action === 'order_query' || preg_match('/(?:^|[?&])action=order_query(?:&|$)/i', (string)$_current_uri)) && ((isset($_GET['contact']) && trim((string)$_GET['contact']) !== '') || preg_match('/(?:^|[?&])contact=[^&]+/i', (string)$_current_uri)));
$_is_blog = bottomNavDefaultIsBlogPage($_current_uri);
$_is_home = ((empty($_current_action) && !$_is_user_path && strpos($_current_uri, '?') === false && !$_is_sort_page && !$_is_blog) || $_current_action === 'index');
$_is_order_query = $_current_action === 'order_query';
$_is_user = $_is_user_path;
$_is_help = $_current_action === 'help';
$_is_goods_detail = bottomNavDefaultIsGoodsDetailPage($_current_uri, isset($goods) ? $goods : null);
$_show_user_center = Option::get('login_switch') == 'y' && Option::get('register_switch') == 'y';
$_active_bg_color = preg_match('/^#([0-9a-fA-F]{6})$/', (string)($_config['active_bg_color'] ?? '')) ? $_config['active_bg_color'] : '#2196F3';
$_active_bg_color_dark = preg_match('/^#([0-9a-fA-F]{6})$/', (string)($_config['active_bg_color_dark'] ?? '')) ? $_config['active_bg_color_dark'] : '#2196F3';
$_active_bg_color_rgb = bottomNavDefaultHexToRgb($_active_bg_color);
$_active_bg_color_dark_rgb = bottomNavDefaultHexToRgb($_active_bg_color_dark);
$_active_text_color = preg_match('/^#([0-9a-fA-F]{6})$/', (string)($_config['active_text_color'] ?? '')) ? $_config['active_text_color'] : '#2196F3';
$_active_text_color_dark = preg_match('/^#([0-9a-fA-F]{6})$/', (string)($_config['active_text_color_dark'] ?? '')) ? $_config['active_text_color_dark'] : '#2196F3';
$_desktop_breakpoint = max(480, min(2200, (int)$_config['desktop_breakpoint']));
$_safe_area = $_config['enable_safe_area'] === 'y' ? 'env(safe-area-inset-bottom, 0px)' : '0px';
$_max_width = max(0, (int)$_config['max_width']);
if ($_is_order_query_contact || bottomNavDefaultMatchLinkRules($_config['hide_on_links'] ?? '', $_current_action, $_current_uri, $_current_sort_id)) {
    return;
}
if ($_config['show_on_goods_detail'] !== 'y' && $_is_goods_detail) {
    return;
}
$_nav_items = [];
foreach (bottomNavDefaultNormalizeItems($_config['nav_items']) as $_item) {
    if (($_item['enabled'] ?? 'y') !== 'y') {
        continue;
    }
    if (($_item['visible'] ?? 'all') === 'user_center_enabled' && $_config['show_user_center_auto'] === 'y' && !$_show_user_center) {
        continue;
    }
    $_type = $_item['type'] ?? 'custom';
    $_default_title = $_item['title'] ?? '导航';
    $_default_url = $_item['url'] ?? '';
    $_default_icon = $_item['icon'] ?? 'ri-apps-2-line';
    $_default_active_icon = $_item['active_icon'] ?? '';
    $_is_active = false;

    if ($_type === 'home') {
        $_default_title = $_item['title'] ?: '首页';
        $_default_url = $_item['url'] ?: DC_URL;
        $_default_icon = $_item['icon'] ?: 'ri-home-5-line';
        $_default_active_icon = $_item['active_icon'] ?: 'ri-home-5-fill';
        $_is_active = !$_is_blog && ($_is_home || $_is_sort_page || $_has_search_query || $_has_goods_order);
        if (!$_is_active && !empty(trim((string)($_item['match_rule'] ?? '')))) {
            $_is_active = bottomNavDefaultIsCustomActive($_item, $_current_action, $_current_uri, $_current_sort_id);
        }
    } elseif ($_type === 'order_query') {
        $_default_title = $_item['title'] ?: '查单';
        $_default_url = $_item['url'] ?: (DC_URL . '?action=order_query');
        $_default_icon = $_item['icon'] ?: 'ri-search-eye-line';
        $_default_active_icon = $_item['active_icon'] ?: 'ri-search-eye-fill';
        $_is_active = $_is_order_query;
        if (!$_is_active && !empty(trim((string)($_item['match_rule'] ?? '')))) {
            $_is_active = bottomNavDefaultIsCustomActive($_item, $_current_action, $_current_uri, $_current_sort_id);
        }
    } elseif ($_type === 'user') {
        $_default_title = $_item['title'] ?: '我的';
        $_default_url = $_item['url'] ?: (DC_URL . 'user/');
        $_default_icon = $_item['icon'] ?: 'ri-user-3-line';
        $_default_active_icon = $_item['active_icon'] ?: 'ri-user-3-fill';
        $_is_active = $_is_user;
        if (!$_is_active && !empty(trim((string)($_item['match_rule'] ?? '')))) {
            $_is_active = bottomNavDefaultIsCustomActive($_item, $_current_action, $_current_uri, $_current_sort_id);
        }
        if ($_config['show_user_center_auto'] === 'y' && !$_show_user_center) {
            continue;
        }
    } elseif ($_type === 'help') {
        $_default_title = $_item['title'] ?: '帮助';
        $_default_url = $_item['url'] ?: (DC_URL . '?action=help');
        $_default_icon = $_item['icon'] ?: 'ri-customer-service-2-line';
        $_default_active_icon = $_item['active_icon'] ?: 'ri-customer-service-2-fill';
        $_is_active = $_is_help;
        if (!$_is_active && !empty(trim((string)($_item['match_rule'] ?? '')))) {
            $_is_active = bottomNavDefaultIsCustomActive($_item, $_current_action, $_current_uri, $_current_sort_id);
        }
    } elseif ($_type === 'blog') {
        $_default_title = $_item['title'] ?: '博客';
        $_default_url = $_item['url'] ?: (DC_URL . 'blog');
        $_default_icon = $_item['icon'] ?: 'ri-quill-pen-line';
        $_default_active_icon = $_item['active_icon'] ?: 'ri-quill-pen-fill';
        $_is_active = $_is_blog;
        if (!$_is_active && !empty(trim((string)($_item['match_rule'] ?? '')))) {
            $_is_active = bottomNavDefaultIsCustomActive($_item, $_current_action, $_current_uri, $_current_sort_id);
        }
    } else {
        $_default_icon = $_item['icon'] ?: 'ri-apps-2-line';
        $_default_active_icon = $_item['active_icon'] ?: $_default_icon;
        $_is_active = bottomNavDefaultIsCustomActive($_item, $_current_action, $_current_uri, $_current_sort_id);
    }

    $_nav_items[] = [
        'title' => $_default_title,
        'url' => $_default_url,
        'icon' => bottomNavDefaultSanitizeIconClass($_default_icon),
        'active_icon' => bottomNavDefaultSanitizeIconClass($_default_active_icon ?: $_default_icon),
        'icon_image' => bottomNavDefaultSanitizeIconImage($_item['icon_image'] ?? ''),
        'active_icon_image' => bottomNavDefaultSanitizeIconImage($_item['active_icon_image'] ?? '') ?: bottomNavDefaultSanitizeIconImage($_item['icon_image'] ?? ''),
        'target' => ($_item['target'] ?? '_self') === '_blank' ? '_blank' : '_self',
        'active' => $_is_active,
    ];
}

if (empty($_nav_items)) {
    return;
}
?>
<style>
.footer-nav.tel-footer.dc-bottom-nav-default {
    display: none !important;
    left: <?= (int)$_config['side_gap'] ?>px;
    right: <?= (int)$_config['side_gap'] ?>px;
    bottom: calc(<?= $_safe_area ?> + <?= (int)$_config['bottom_offset'] ?>px);
    padding: <?= (int)$_config['container_padding'] ?>px;
    gap: <?= (int)$_config['item_gap'] ?>px;
    border-radius: <?= (int)$_config['container_radius'] ?>px;
    background: <?= bottomNavDefaultAlphaColor($_config['background_color'], $_config['background_alpha'], '255, 255, 255') ?>;
    -webkit-backdrop-filter: saturate(180%) blur(<?= (int)$_config['blur_strength'] ?>px);
    backdrop-filter: saturate(180%) blur(<?= (int)$_config['blur_strength'] ?>px);
    box-shadow: 0 <?= (int)$_config['shadow_y'] ?>px <?= (int)$_config['shadow_blur'] ?>px <?= bottomNavDefaultAlphaColor($_config['shadow_color'], $_config['shadow_alpha'], '15, 23, 42') ?>, inset 0 1px 0 rgba(255,255,255,0.42);
    border: 1px solid <?= bottomNavDefaultAlphaColor($_config['border_color'], $_config['border_alpha'], '255, 255, 255') ?>;
    z-index: <?= (int)$_config['z_index'] ?>;
    width: auto;
    box-sizing: border-box;border: 2px solid #fff;
    <?php if ($_max_width > 0): ?>max-width: <?= $_max_width ?>px; margin-left: auto; margin-right: auto;<?php endif; ?>
}
@media (max-width: <?= $_desktop_breakpoint ?>px) {
    .footer-nav.tel-footer.dc-bottom-nav-default {
        position: fixed;
        display: flex !important;
        align-items: center;
        justify-content: space-around;
        left: calc(env(safe-area-inset-left, 0px) + <?= (int)$_config['side_gap'] ?>px);
        right: calc(env(safe-area-inset-right, 0px) + <?= (int)$_config['side_gap'] ?>px);
        width: auto !important;
        max-width: none !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
    .footer-nav.tel-footer.dc-bottom-nav-default .nav-item {
        flex: 1 1 0;
        min-width: 0;
        height: auto;
        min-height: <?= (int)$_config['item_height'] ?>px;
        border-radius: <?= (int)$_config['item_radius'] ?>px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: <?= $_config['show_label'] === 'y' ? '3px' : '0' ?>;
        padding: 0 4px;
        box-sizing: border-box;
        font-size: <?= (int)$_config['text_size'] ?>px;
        color: <?= htmlspecialchars($_config['text_color'], ENT_QUOTES) ?>;
        text-decoration: none;
        transition: all .22s ease;
        overflow: visible;
    }
    .footer-nav.tel-footer.dc-bottom-nav-default .nav-item .nav-icon {
        font-size: <?= (int)$_config['icon_size'] ?>px;
        line-height: 1;
        margin-bottom: 0;
        flex-shrink: 0;
        transform: translateY(<?= (float)$_config['icon_translate_y'] ?>px);
        transition: transform .22s ease;
    }
    .footer-nav.tel-footer.dc-bottom-nav-default .nav-item .nav-icon-image {
        width: <?= (int)$_config['icon_size'] ?>px;
        height: <?= (int)$_config['icon_size'] ?>px;
        object-fit: contain;
        display: block;
        margin-bottom: 0;
        flex-shrink: 0;
        transform: translateY(<?= (float)$_config['icon_translate_y'] ?>px);
        transition: transform .22s ease;
    }
    .footer-nav.tel-footer.dc-bottom-nav-default .nav-item .nav-text {
        <?= $_config['show_label'] === 'y' ? 'display:block;' : 'display:none;' ?>
        max-width: 100%;
        line-height: 1.3;
        text-align: center;
        box-sizing: border-box;
        padding-bottom: .14em;
        position: relative;
        top: <?= (float)$_config['text_translate_y'] ?>px;
        flex-shrink: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        transition: top .22s ease;
    }
    .footer-nav.tel-footer.dc-bottom-nav-default .nav-item.active {
        color: <?= htmlspecialchars($_active_text_color, ENT_QUOTES) ?>;
        background: rgba(<?= $_active_bg_color_rgb ?>, <?= max(0, min(100, (int)$_config['active_bg_alpha'])) / 100 ?>);
        box-shadow: inset 0 0 0 1px rgba(<?= $_active_bg_color_rgb ?>, <?= max(0, min(100, (int)$_config['active_bg_alpha'])) / 100 ?>);
        font-weight: <?= (int)$_config['font_weight'] ?>;
    }
    .footer-nav.tel-footer.dc-bottom-nav-default .nav-item.active .nav-icon {
        transform: translateY(<?= (float)$_config['icon_translate_y'] + (float)$_config['active_translate_y'] ?>px) scale(<?= (float)$_config['active_scale'] ?>);
    }
    .footer-nav.tel-footer.dc-bottom-nav-default .nav-item.active .nav-icon-image {
        transform: translateY(<?= (float)$_config['icon_translate_y'] + (float)$_config['active_translate_y'] ?>px) scale(<?= (float)$_config['active_scale'] ?>);
    }
}
<?php if ($_config['show_on_desktop'] === 'y'): ?>
@media (min-width: <?= $_desktop_breakpoint + 1 ?>px) {
    .footer-nav.tel-footer.dc-bottom-nav-default {
        position: fixed;
        display: flex !important;
        align-items: center;
        justify-content: space-around;
    }
    .footer-nav.tel-footer.dc-bottom-nav-default .nav-item {
        flex: 1;
        min-width: 0;
        height: auto;
        min-height: <?= (int)$_config['item_height'] ?>px;
        border-radius: <?= (int)$_config['item_radius'] ?>px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: <?= $_config['show_label'] === 'y' ? '3px' : '0' ?>;
        font-size: <?= (int)$_config['text_size'] ?>px;
        color: <?= htmlspecialchars($_config['text_color'], ENT_QUOTES) ?>;
        text-decoration: none;
        transition: all .22s ease;
        overflow: visible;
    }
    .footer-nav.tel-footer.dc-bottom-nav-default .nav-item .nav-icon {
        font-size: <?= (int)$_config['icon_size'] ?>px;
        line-height: 1;
        margin-bottom: 0;
        flex-shrink: 0;
        transform: translateY(<?= (float)$_config['icon_translate_y'] ?>px);
        transition: transform .22s ease;
    }
    .footer-nav.tel-footer.dc-bottom-nav-default .nav-item .nav-icon-image {
        width: <?= (int)$_config['icon_size'] ?>px;
        height: <?= (int)$_config['icon_size'] ?>px;
        object-fit: contain;
        display: block;
        margin-bottom: 0;
        flex-shrink: 0;
        transform: translateY(<?= (float)$_config['icon_translate_y'] ?>px);
        transition: transform .22s ease;
    }
    .footer-nav.tel-footer.dc-bottom-nav-default .nav-item .nav-text {
        <?= $_config['show_label'] === 'y' ? 'display:block;' : 'display:none;' ?>
        max-width: 100%;
        line-height: 1.3;
        text-align: center;
        box-sizing: border-box;
        padding-bottom: .14em;
        position: relative;
        top: <?= (float)$_config['text_translate_y'] ?>px;
        flex-shrink: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        transition: top .22s ease;
    }
    .footer-nav.tel-footer.dc-bottom-nav-default .nav-item.active {
        color: <?= htmlspecialchars($_active_text_color, ENT_QUOTES) ?>;
        background: rgba(<?= $_active_bg_color_rgb ?>, <?= max(0, min(100, (int)$_config['active_bg_alpha'])) / 100 ?>);
        box-shadow: inset 0 0 0 1px rgba(<?= $_active_bg_color_rgb ?>, <?= max(0, min(100, (int)$_config['active_bg_alpha'])) / 100 ?>);
        font-weight: <?= (int)$_config['font_weight'] ?>;
    }
    .footer-nav.tel-footer.dc-bottom-nav-default .nav-item.active .nav-icon {
        transform: translateY(<?= (float)$_config['icon_translate_y'] + (float)$_config['active_translate_y'] ?>px) scale(<?= (float)$_config['active_scale'] ?>);
    }
    .footer-nav.tel-footer.dc-bottom-nav-default .nav-item.active .nav-icon-image {
        transform: translateY(<?= (float)$_config['icon_translate_y'] + (float)$_config['active_translate_y'] ?>px) scale(<?= (float)$_config['active_scale'] ?>);
    }
}
<?php endif; ?>
html[data-theme="dark"] .footer-nav.tel-footer.dc-bottom-nav-default {
    background: <?= bottomNavDefaultAlphaColor($_config['background_color_dark'], $_config['background_alpha_dark'], '17, 24, 39') ?>;
    border-color: <?= bottomNavDefaultAlphaColor($_config['border_color_dark'], $_config['border_alpha_dark'], '255, 255, 255') ?>;
    box-shadow: 0 <?= (int)$_config['shadow_y'] ?>px <?= (int)$_config['shadow_blur'] ?>px <?= bottomNavDefaultAlphaColor($_config['shadow_color_dark'], $_config['shadow_alpha_dark'], '0, 0, 0') ?>, inset 0 1px 0 rgba(255,255,255,0.03);
}
html[data-theme="dark"] .footer-nav.tel-footer.dc-bottom-nav-default .nav-item {
    color: <?= htmlspecialchars($_config['text_color_dark'], ENT_QUOTES) ?>;
}
html[data-theme="dark"] .footer-nav.tel-footer.dc-bottom-nav-default .nav-item.active {
    color: <?= htmlspecialchars($_active_text_color_dark, ENT_QUOTES) ?>;
    background: rgba(<?= $_active_bg_color_dark_rgb ?>, <?= max(0, min(100, (int)$_config['active_bg_alpha_dark'])) / 100 ?>);
    box-shadow: inset 0 0 0 1px rgba(<?= $_active_bg_color_dark_rgb ?>, <?= max(0, min(100, (int)$_config['active_bg_alpha_dark'])) / 100 ?>);
}
</style>
<footer class="footer-nav tel-footer dc-bottom-nav-default">
    <?php foreach ($_nav_items as $_nav): ?>
    <a href="<?= htmlspecialchars($_nav['url']) ?>" class="nav-item <?= $_nav['active'] ? 'active' : '' ?>" target="<?= $_nav['target'] ?>" <?= $_nav['target'] === '_blank' ? 'rel="noopener noreferrer"' : '' ?>>
        <?= bottomNavDefaultBuildIconHtml([
            'title' => $_nav['title'],
            'icon' => $_nav['active'] ? $_nav['active_icon'] : $_nav['icon'],
            'icon_image' => $_nav['active'] ? $_nav['active_icon_image'] : $_nav['icon_image'],
        ]) ?>
        <span class="nav-text"><?= htmlspecialchars($_nav['title']) ?></span>
    </a>
    <?php endforeach; ?>
</footer>

