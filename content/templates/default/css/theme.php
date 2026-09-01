<?php
/**
 * 动态主题配色CSS
 * 根据模板配置生成CSS变量
 */
header('Content-Type: text/css; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// 加载系统核心
$dc_root = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/';
if (!defined('DC_ROOT')) {
    define('DC_ROOT', $dc_root);
}
require_once $dc_root . 'config.php';
require_once $dc_root . 'base.php';
require_once $dc_root . 'include/lib/common.php';

// 注册自动加载
spl_autoload_register("emAutoload");

// 直接从数据库获取模板配置（支持分站隔离）
try {
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;

    // 检测分站上下文：域名匹配 → cookie slug 回落
    $_station_id = 0;
    $_current_domain = getDomain();
    $_stRow = $db->once_fetch_array("SELECT id FROM {$db_prefix}station WHERE (domain = '{$_current_domain}' OR domain_2 = '{$_current_domain}') AND delete_time IS NULL LIMIT 1");
    if (!empty($_stRow['id'])) {
        $_station_id = (int)$_stRow['id'];
    }
    if ($_station_id <= 0 && !empty($_COOKIE['dc_station_slug'])) {
        $_optRow = $db->once_fetch_array("SELECT option_value FROM {$db_prefix}options WHERE option_name = 'station_slug_mode' LIMIT 1");
        if (!empty($_optRow['option_value']) && $_optRow['option_value'] === '1') {
            $_slug = addslashes(trim($_COOKIE['dc_station_slug']));
            if ($_slug !== '') {
                $_stRow2 = $db->once_fetch_array("SELECT id FROM {$db_prefix}station WHERE slug = '{$_slug}' AND delete_time IS NULL LIMIT 1");
                if (!empty($_stRow2['id'])) {
                    $_station_id = (int)$_stRow2['id'];
                }
            }
        }
    }

    $config = [];
    if ($_station_id > 0) {
        // 分站：从 dc_station_storage 读取
        // ORDER BY plugin_name DESC: 'd'<'f'，DESC 使 front_default 排前、default 排后
        // while 循环后写入者胜出，所以 'default'（新代码规范写入目标）的值最终生效
        $sql = "SELECT option_name, option_value FROM {$db_prefix}station_storage
                WHERE station_id = {$_station_id}
                AND type = 'tpl'
                AND plugin_name IN ('front_default', 'default')
                AND option_name IN ('theme_primary', 'theme_price', 'theme_button', 'theme_accent')
                ORDER BY plugin_name DESC";
        $result = $db->query($sql);
        while ($row = $db->fetch_array($result)) {
            $config[$row['option_name']] = @unserialize($row['option_value']);
        }
    } else {
        // 主站：从 dc_tpl_options_data 读取
        $template = 'front_default';
        $table = $db_prefix . 'tpl_options_data';
        $sql = "SELECT name, data FROM {$table} WHERE template = '{$template}' AND name IN ('theme_primary', 'theme_price', 'theme_button', 'theme_accent') ORDER BY id ASC";
        $result = $db->query($sql);
        while ($row = $db->fetch_array($result)) {
            $config[$row['name']] = @unserialize($row['data']);
        }
    }

    $theme_primary = !empty($config['theme_primary']) ? $config['theme_primary'] : '#2196F3';
    $theme_price = !empty($config['theme_price']) ? $config['theme_price'] : '#ff6600';
    $theme_button = !empty($config['theme_button']) ? $config['theme_button'] : '#2f69d9';
    $theme_accent = !empty($config['theme_accent']) ? $config['theme_accent'] : '#ff9800';
    $_debug_source = ($_station_id > 0) ? 'station_storage(id=' . $_station_id . ')' : 'tpl_options_data(main)';
    $_debug_config_count = count($config);
} catch (Exception $e) {
    // 使用默认值
    $theme_primary = '#2196F3';
    $theme_price = '#ff6600';
    $theme_button = '#2f69d9';
    $theme_accent = '#ff9800';
    $_debug_source = 'exception: ' . $e->getMessage();
    $_debug_config_count = 0;
}

// 计算衍生颜色
function hexToRgb($hex) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) !== 6) return ['r' => 33, 'g' => 150, 'b' => 243];
    return [
        'r' => hexdec(substr($hex, 0, 2)),
        'g' => hexdec(substr($hex, 2, 2)),
        'b' => hexdec(substr($hex, 4, 2))
    ];
}

function adjustBrightness($hex, $percent) {
    $rgb = hexToRgb($hex);
    $r = max(0, min(255, $rgb['r'] + ($percent * 255 / 100)));
    $g = max(0, min(255, $rgb['g'] + ($percent * 255 / 100)));
    $b = max(0, min(255, $rgb['b'] + ($percent * 255 / 100)));
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

$primary_rgb = hexToRgb($theme_primary);
$price_rgb = hexToRgb($theme_price);
$button_rgb = hexToRgb($theme_button);
$accent_rgb = hexToRgb($theme_accent);

$primary_light = adjustBrightness($theme_primary, 40);
$primary_dark = adjustBrightness($theme_primary, -15);
$button_dark = adjustBrightness($theme_button, -10);
$accent_dark = adjustBrightness($theme_accent, -15);
?>
/* [DEBUG] domain=<?= $_current_domain ?? 'N/A' ?> station_id=<?= $_station_id ?? 0 ?> source=<?= $_debug_source ?? 'unknown' ?> rows=<?= $_debug_config_count ?? 0 ?> primary=<?= $theme_primary ?> price=<?= $theme_price ?> button=<?= $theme_button ?> accent=<?= $theme_accent ?> */
/* ========== 主题配色CSS变量 ========== */
:root {
    --theme-primary: <?= $theme_primary ?>;
    --theme-primary-rgb: <?= $primary_rgb['r'] ?>, <?= $primary_rgb['g'] ?>, <?= $primary_rgb['b'] ?>;
    --theme-primary-light: <?= $primary_light ?>;
    --theme-primary-dark: <?= $primary_dark ?>;
    --theme-price: <?= $theme_price ?>;
    --theme-price-rgb: <?= $price_rgb['r'] ?>, <?= $price_rgb['g'] ?>, <?= $price_rgb['b'] ?>;
    --theme-button: <?= $theme_button ?>;
    --theme-button-rgb: <?= $button_rgb['r'] ?>, <?= $button_rgb['g'] ?>, <?= $button_rgb['b'] ?>;
    --theme-button-dark: <?= $button_dark ?>;
    --theme-accent: <?= $theme_accent ?>;
    --theme-accent-rgb: <?= $accent_rgb['r'] ?>, <?= $accent_rgb['g'] ?>, <?= $accent_rgb['b'] ?>;
    --theme-accent-dark: <?= $accent_dark ?>;
}

/* ========== 选中状态 ========== */
.goods-select-item.active,
.spec-option.active {
    background: rgba(var(--theme-primary-rgb), 0.1) !important;
    border-color: var(--theme-primary) !important;
    color: var(--theme-primary-dark) !important;
}
.goods-select-item.active::after,
.spec-option.active::after {
    border-color: transparent transparent var(--theme-primary) transparent !important;
}
.goods-select-item:hover,
.spec-option:hover {
    background: rgba(var(--theme-primary-rgb), 0.05) !important;
    border-color: rgba(var(--theme-primary-rgb), 0.3) !important;
}

/* ========== 支付方式选中 ========== */
.payment-item.active {
    border-color: var(--theme-primary) !important;
    background: rgba(var(--theme-primary-rgb), 0.1) !important;
}
.payment-item:hover {
    border-color: rgba(var(--theme-primary-rgb), 0.3) !important;
    background: rgba(var(--theme-primary-rgb), 0.03) !important;
}
.payment-checked {
    color: var(--theme-primary) !important;
}

/* ========== 价格颜色 ========== */
.section-value,
.unit-price,
.dynamic-price,
.goods-price,
.price,
.pay-amount .dynamic-price {
    color: var(--theme-price) !important;
}
.required-star,
.input-field-note {
    color: var(--theme-price) !important;
}

/* ========== 按钮颜色 ========== */
.pay-btn {
    background: var(--theme-button) !important;
}
.pay-btn:hover {
    background: var(--theme-button-dark) !important;
}

/* ========== 强调色/优惠券 ========== */
.coupon-checkbox:checked + .coupon-checkbox-box {
    background: var(--theme-accent) !important;
    border-color: var(--theme-accent) !important;
}
.coupon-input:focus {
    border-color: var(--theme-accent) !important;
}
.coupon-check-btn {
    background: var(--theme-accent) !important;
}
.coupon-check-btn:hover {
    background: var(--theme-accent-dark) !important;
}

/* ========== 输入框焦点 ========== */
.input-field-input:focus,
.email-input:focus,
.search-input:focus,
.captcha-input:focus {
    border-color: var(--theme-primary) !important;
}

/* ========== 开关选中 ========== */
.email-switch-input:checked + .email-switch-slider {
    background-color: var(--theme-primary) !important;
}

/* ========== 链接和图标 ========== */
.stock-row .stock-value {
    color: var(--theme-primary) !important;
}
.faq-item.active .faq-arrow {
    color: var(--theme-primary) !important;
}
.faq-answer a {
    color: var(--theme-primary) !important;
}
.section-title {
    border-left-color: var(--theme-primary) !important;
}
.shortcut-icon.blue {
    background: rgba(var(--theme-primary-rgb), 0.1) !important;
    color: var(--theme-primary) !important;
}

/* ========== 订单结果页 ========== */
.result-count span,
.goods-name a:hover,
.action-btn:hover,
.btn-rebuy,
.btn-copy-all,
.btn-export,
.kami-item-copy,
.chat-img-btn:hover,
.kami-info-text span,
.kami-pay-content a {
    color: var(--theme-primary) !important;
}
.action-btn:hover,
.btn-rebuy {
    border-color: var(--theme-primary) !important;
}
.action-btn.primary,
.btn-rebuy:hover,
.btn-copy-all:hover,
.chat-send-btn,
.chat-msg.user .chat-bubble,
.chat-avatar,
.chat-msg.admin .chat-avatar,
.kami-icon,
.search-btn,
.query-btn,
.empty-btn {
    background: var(--theme-primary) !important;
    background-color: var(--theme-primary) !important;
}
.action-btn.primary {
    border-color: var(--theme-primary) !important;
}

/* ========== 售后按钮（仅默认申请状态跟随强调色） ========== */
.btn-aftersale:not(.btn-chat-history):not(.btn-completed):not(.btn-rejected):not(.btn-closed):not(.btn-expired) {
    color: var(--theme-accent) !important;
    border-color: var(--theme-accent) !important;
}
.btn-aftersale:not(.btn-chat-history):not(.btn-completed):not(.btn-rejected):not(.btn-closed):not(.btn-expired):hover {
    background: var(--theme-accent) !important;
    color: #fff !important;
}

/* ========== 警告图标 ========== */
.disclaimer .icon {
    color: var(--theme-accent) !important;
}

/* ========== FAQ编号 ========== */
.faq-num {
    color: var(--theme-price) !important;
}

/* ========== 批发优惠标签 ========== */
#discountTag {
    color: var(--theme-primary) !important;
}

/* ========== 通知栏 ========== */
.notice-bar {
    border-left-color: var(--theme-primary) !important;
}
.notice-bar .layui-icon {
    color: var(--theme-primary) !important;
}

/* ========== 底部导航 ========== */
.footer-nav:not([class*="dc-bottom-nav-"]) .nav-item.active {
    color: var(--theme-primary) !important;
}
@media (max-width: 1200px) {
    .footer-nav:not([class*="dc-bottom-nav-"]) {
        background: rgba(255, 255, 255, 0.72) !important;
        -webkit-backdrop-filter: saturate(180%) blur(20px) !important;
        backdrop-filter: saturate(180%) blur(20px) !important;
        border-top: 0.5px solid rgba(0, 0, 0, 0.1) !important;
        box-shadow: none !important;
    }
}

/* ========== 深色模式适配 ========== */
html[data-theme="dark"] .goods-select-item.active,
html[data-theme="dark"] .spec-option.active {
    background: rgba(var(--theme-primary-rgb), 0.2) !important;
    color: var(--theme-primary-light) !important;
}
html[data-theme="dark"] .payment-item.active {
    background: rgba(var(--theme-primary-rgb), 0.2) !important;
}
html[data-theme="dark"] .footer-nav:not([class*="dc-bottom-nav-"]) {
    background: rgba(18, 18, 18, 0.75) !important;
    -webkit-backdrop-filter: saturate(180%) blur(20px) !important;
    backdrop-filter: saturate(180%) blur(20px) !important;
    border-top-color: rgba(255, 255, 255, 0.08) !important;
}
html[data-theme="dark"] .footer-nav:not([class*="dc-bottom-nav-"]) .nav-item {
    color: #888 !important;
}
html[data-theme="dark"] .footer-nav:not([class*="dc-bottom-nav-"]) .nav-item.active {
    color: var(--theme-primary) !important;
}
