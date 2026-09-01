 <?php
/**
 * 首页模板 - 支持单页购买模式和普通模式
 */
defined('DC_ROOT') || exit('access denied!');

$_front_tpl_options = _g();
$_front_tpl_options = is_array($_front_tpl_options) ? $_front_tpl_options : [];
$_front_tpl_get = function ($name, $default = null) use ($_front_tpl_options) {
    return array_key_exists($name, $_front_tpl_options) ? $_front_tpl_options[$name] : $default;
};

$single_page_mode = $_front_tpl_get('single_page_mode', 'n');
$card_type_show = $_front_tpl_get('card_type_show', 'y');

// 获取主题配色
$_theme_primary = _g('theme_primary') ?: '#2196F3';
$_theme_price = _g('theme_price') ?: '#ff6600';
$_theme_button = _g('theme_button') ?: '#2f69d9';
$_theme_accent = _g('theme_accent') ?: '#ff9800';
?>

<?php if ($single_page_mode === 'y'):
// ========== 单页购买模式 ==========
$payment = getPayment();
$single_sales_show = _g('single_sales_show') ?: 'y';
$single_stock_show = _g('single_stock_show') ?: 'y';
$single_price_show = _g('single_price_show') ?: 'y';
$single_float_help_show = _g('single_float_help_show') ?: 'y';
$float_help_icon = _g('float_help_icon') ?: '';
$pay_type = _g('pay_type') ?: '2';

// 检测「订单卡密邮箱推送」插件是否启用
$_activePlugins = Option::get('active_plugins') ?: [];
$_emailPushEnabled = in_array('user_email/user_email.php', $_activePlugins);

// 检查是否有后台设置的头部样式
$_shop_header_bg = _g('shop_header_bg') ?: '';
?>
<script>
// 主题配色变量供JS使用
var THEME = {
    primary: '<?= $_theme_primary ?>',
    price: '<?= $_theme_price ?>',
    button: '<?= $_theme_button ?>',
    accent: '<?= $_theme_accent ?>',
    emailPushEnabled: <?= $_emailPushEnabled ? 'true' : 'false' ?>
};
</script>
<style>
<?php if (empty($_shop_header_bg)): ?>
/* 单页模式默认主题色（仅在后台未设置时生效） */
.h-fix { background: #0c6be1 !important; }
.logo-text a span { color: #fff !important; }
.logo-brand .brand-title { color: #fff !important; }
.logo-brand .brand-subtitle { color: rgba(255,255,255,0.8) !important; }
.header .nav-bar li a { color: #fff !important; }
.header-help-btn { border-color: rgba(255,255,255,0.5) !important; color: #fff !important; }
<?php endif; ?>

/* 单页购买模式容器 - 确保宽度 */
.single-page-container {
    width: 100%;
    max-width: 600px;
    margin: 30px auto;
    padding: 0 15px;
    box-sizing: border-box;
}

/* 主卡片容器 - 确保100%宽度 */
.main-card {
    width: 100%;
    background: rgba(248, 248, 248, 0.5);
    border: 2px solid #fff;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    padding: 20px;
    box-sizing: border-box;
}

/* 区块标题 */
.section-title { font-size: 16px; font-weight: 600; color: #333; margin-bottom: 15px; }

/* 商品选择列表 */
.goods-select-list { width: 100%; }

/* 商品选择按钮 - 确保100%宽度 */
.goods-select-item {
    display: block;
    width: 100%;
    background: #fff;

    border-radius: 8px;
    padding: 8px 15px;
    overflow: hidden;
    margin-bottom: 10px;
    cursor: pointer;
    font-size: 13px;
    color: #333;
    border: 1px solid #eee;
    transition: all 0.2s ease;
    box-sizing: border-box;
}
.goods-select-item:last-child { margin-bottom: 0; }
.goods-select-item:hover { background: rgba(var(--theme-primary-rgb, 33, 150, 243), 0.05); border-color: rgba(var(--theme-primary-rgb, 33, 150, 243), 0.3); }
.goods-select-item.active { background: rgba(var(--theme-primary-rgb, 33, 150, 243), 0.1); border-color: var(--theme-primary, #2196F3); color: var(--theme-primary-dark, #1976D2); font-weight: 500; position: relative; }
.goods-select-item.active::after { content: ""; position: absolute; bottom: 0; right: 0; width: 0; height: 0; border-style: solid; border-width: 0 0 12px 12px; border-color: transparent transparent var(--theme-primary, #2196F3) transparent; }

/* 规格选择区域 */
.spec-section { display: none; margin-top: 20px; width: 100%; }
.spec-section.show { display: block; }
.spec-group { margin-bottom: 15px; width: 100%; }
.spec-group:last-child { margin-bottom: 0; }
.spec-group-title { font-size: 16px; font-weight: 600; color: #333; margin-bottom: 15px; }
.spec-options { width: 100%; }
.spec-list { width: 100%; }

/* 规格选项 - 确保100%宽度 */
.spec-option {
    display: block;
    width: 100%;
    padding: 8px 15px;
    overflow: hidden;
    border: 1px solid transparent;
    background: #f8f9fa;
    border-radius: 8px;
    font-size: 13px;
    color: #333;
    cursor: pointer;
    transition: all 0.2s;
    box-sizing: border-box;
    margin-bottom: 10px;
}
.spec-option:last-child { margin-bottom: 0; }
.spec-option:hover { background: rgba(var(--theme-primary-rgb, 33, 150, 243), 0.05); border-color: rgba(var(--theme-primary-rgb, 33, 150, 243), 0.3); }
.spec-option.active { background: rgba(var(--theme-primary-rgb, 33, 150, 243), 0.1); border-color: var(--theme-primary, #2196F3); color: var(--theme-primary-dark, #1976D2); font-weight: 500; position: relative; }
.spec-option.active::after { content: ""; position: absolute; bottom: 0; right: 0; width: 0; height: 0; border-style: solid; border-width: 0 0 12px 12px; border-color: transparent transparent var(--theme-primary, #2196F3) transparent; }
.spec-option.disabled { opacity: 0.5; cursor: not-allowed; background: #eee; text-decoration: line-through; }
/* 购买表单区域 */
.buy-form-section { display: none; margin-top: 20px; width: 100%; }
.buy-form-section.show { display: block; }

/* 小标题 */
.section-label { font-size: 16px; color: #333; font-weight: 600; margin-bottom: 12px; }
.required-star { color: var(--theme-price, #ff6600); margin-right: 4px; }

/* 商品描述框 */
.goods-description-box { background: #fff; border-radius: 8px; padding: 15px; margin-bottom: 20px; width: 100%; box-sizing: border-box; border: 1px solid #eee;}
.goods-description-box .intro { font-size: 13px; line-height: 1.8; color: #555; padding: 0; margin: 0; }
.goods-description-box .intro img { max-width: 100%; border-radius: 8px; margin: 10px 0; }

/* 价格和数量同行布局 */
.price-quantity-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; padding: 0px 0; }
.price-section, .quantity-section { display: flex; flex-direction: column; gap: 8px; }
.section-value { font-size: 18px; color: var(--theme-price, #ff6600); font-weight: 700; padding-left: 10px; }
.section-value .unit { font-size: 13px; color: #999; font-weight: normal; }
.market-price { font-size: 13px; color: #bbb; font-weight: normal; text-decoration: line-through; margin-left: 8px; }

/* 数量选择器 */
.quantity-selector { display: flex; align-items: center; border: 1px solid #eee; border-radius: 8px; overflow: hidden; }
.quantity-btn { width: 28px; height: 28px; background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #666; border: none; font-size: 16px; }
.quantity-btn:hover { background: #f5f5f5; }
.quantity-input { width: 36px; height: 28px; border: none; border-left: 1px solid #eee; border-right: 1px solid #eee; text-align: center; font-size: 13px; font-weight: 600; color: #333; outline: none; }

/* 库存显示 */
.stock-row { margin-bottom: 20px; font-size: 13px; color: #666; }
.stock-row .stock-value { color: var(--theme-primary, #2196F3); font-weight: 500; }

/* 输入字段 */
.input-field-row { margin-bottom: 18px; width: 100%; }
.input-field-header { display: flex; align-items: center; gap: 12px; margin-bottom: 6px; }
.input-field-header .section-label { white-space: nowrap; flex-shrink: 0; margin-bottom: 0; font-size: 16px; }
.input-field-input { flex: 1; height: 40px; border: none; border-bottom: 1px solid #eee; outline: none; font-size: 15px; color: #f95926 !important; background: #fff; padding: 0 8px; }
.input-field-input:focus { border-bottom-color: var(--theme-primary, #2196F3); }
.input-field-input::placeholder { color: #bbb; }
.input-field-note { font-size: 12px; color: var(--theme-price, #ff6600); padding-left: 14px; margin-top: 4px; }

/* 邮箱字段开关样式 */
.email-field-row { margin-bottom: 18px; }
.email-field-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
.email-field-title { font-size: 16px; font-weight: 600; color: #333; }
.email-field-desc { font-size: 13px; color: #333; margin-top: 8px; }
.email-input-wrap { margin-top: 12px; }
.email-input-wrap .email-input { width: 100%; height: 42px; border: 1px solid #ddd; border-radius: 6px; padding: 0 12px; font-size: 14px; background: #fff; box-sizing: border-box; }
.email-input-wrap .email-input:focus { border-color: var(--theme-primary, #2196F3); outline: none; }
.email-input-wrap .email-input::placeholder { color: #999; }
/* 开关样式 */
.email-switch-label { position: relative; display: inline-block; width: 50px; height: 28px; cursor: pointer; }
.email-switch-input { opacity: 0; width: 0; height: 0; position: absolute; }
.email-switch-slider { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #ddd; border-radius: 28px; transition: 0.3s; }
.email-switch-slider:before { content: ""; position: absolute; height: 24px; width: 24px; left: 2px; bottom: 2px; background-color: white; border-radius: 50%; transition: 0.3s; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
.email-switch-input:checked + .email-switch-slider { background-color: var(--theme-primary, #2196F3); }
.email-switch-input:checked + .email-switch-slider:before { transform: translateX(22px); }

/* 支付方式 */
.payment-methods { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; width: 100%; }
.payment-item { padding: 12px; border: 1px solid #eee; border-radius: 10px; background: #fff; transition: all 0.2s; position: relative; display: flex; align-items: center; cursor: pointer; }
.payment-item:hover { border-color: rgba(var(--theme-primary-rgb, 33, 150, 243), 0.3); background: rgba(var(--theme-primary-rgb, 33, 150, 243), 0.03); }
.payment-item.active { border-color: var(--theme-primary, #2196F3); background: rgba(var(--theme-primary-rgb, 33, 150, 243), 0.1); }
.payment-icon { width: 32px; height: 32px; margin-right: 10px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background-color: #f5f5f5; }
.payment-icon img { width: 22px; height: 22px; }
.payment-name { font-size: 13px; font-weight: 500; color: #333; }
.payment-checked { width: 20px; height: 20px; color: var(--theme-primary, #2196F3); position: absolute; top: -6px; right: -6px; background: #fff; border-radius: 50%; font-size: 18px; line-height: 1; display: none; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
.payment-item.active .payment-checked { display: flex; }

/* 底部支付栏 */
.drawer-footer { margin-top: 20px; width: 100%; position: relative; }
.pay-bar { display: flex; justify-content: space-between; align-items: center; background: rgba(248, 248, 248, 0.8); border-radius: 8px; padding: 12px 15px; box-shadow: inset 0px 0px 4px rgb(255 255 255/0%), inset 3px 3px 10px rgb(55 84 170/10%), inset -3px -3px 10px rgb(55 84 170/8%), 0px 0px 0px rgb(255 255 255/20%) !important; }
.pay-amount { font-size: 13px; color: #333; }
.pay-amount .dynamic-price { font-size: 18px; font-weight: 700; color: var(--theme-price, #ff6600); }
.pay-btn { background: var(--theme-button, #2f69d9); color: #fff; border: none; border-radius: 8px; padding: 12px 35px; font-size: 13px; font-weight: 500; cursor: pointer; }
.pay-btn:hover { background: var(--theme-button-dark, #2558c4); }

/* 单页购买模式默认隐藏底部信息 */
.main-footer { display: none; background: transparent !important; border-top: none !important; margin-top: 0 !important; }
.footer-nav { display: none; }
/* 单页购买模式底部占位：移动端固定购买栏会脱离文档流，占位放在卡片外，避免卡片内容被遮住 */
.single-page-bottom-spacer { display: none; }

/* PC端适配 - 参考商品详情页样式 */
@media (min-width: 769px) {
    /* 容器使用与商品详情页相同的宽度 */
    .single-page-container { 
        max-width: 1140px; 
        margin: 30px auto; 
        padding: 0 15px;
    }
    .main-card { 
        padding: 40px; 
        border-radius: 16px; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    .section-title { font-size: 18px; margin-bottom: 20px; }
    
    /* 商品选择 - PC端横向排列 */
    .goods-select-list { display: flex; flex-wrap: wrap; gap: 12px; }
    .goods-select-item { 
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        width: auto !important;
        padding: 8px 20px; 
        font-size: 14px;
        margin-bottom: 0;
    }
    
    /* 规格区域 */
    .spec-section { margin-top: 25px; }
    .spec-group { margin-bottom: 20px; }
    
    /* 规格选项 - PC端横向排列 */
    .spec-options { display: flex; flex-wrap: wrap; gap: 12px; }
    .spec-option { 
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        width: auto !important;
        padding: 8px 20px; 
        font-size: 14px;
        margin-bottom: 0;
    }
    
    /* 购买表单区域 */
    .buy-form-section { margin-top: 25px; }
    
    /* 商品描述框 */
    .goods-description-box { padding: 20px; }
    
    /* 价格和数量 */
    .price-quantity-row { padding: 15px 0; margin-bottom: 25px; }
    .section-label { font-size: 16.5px; font-weight: bold; }
    .section-value { font-size: 23px; padding-left: 12px; }
    .section-value .unit { font-size: 14px; }
    
    /* 数量选择器 */
    .quantity-selector { border-radius: 8px; }
    .quantity-btn { width: 38px; height: 38px; font-size: 14px; }
    .quantity-input { width: 50px; height: 38px; font-size: 15px; }
    
    /* 库存显示 */
    .stock-row { font-size: 14px; margin-bottom: 25px; }
    
    /* 输入字段 */
    .input-field-row { margin-bottom: 20px; }
    .input-field-header { gap: 15px; margin-bottom: 8px; }
    .input-field-input { height: 40px; font-size: 14px; padding: 0 10px; }
    .input-field-note { font-size: 13px; padding-left: 14px; margin-top: 5px; }
    
    /* 支付方式 */
    .payment-methods { gap: 15px; }
    .payment-item { padding: 15px; border-radius: 10px; }
    .payment-icon { width: 36px; height: 36px; margin-right: 12px; }
    .payment-icon img { width: 24px; height: 24px; }
    .payment-name { font-size: 14px; font-weight: 600; }
    
    /* 底部支付栏 */
    .drawer-footer { margin-top: 30px; }
    .pay-bar { padding: 12px 15px; border-radius: 8px; }
    .pay-amount { font-size: 15px; }
    .pay-amount .dynamic-price { font-size: 20px; }
    .pay-btn { padding: 12px 35px; font-size: 16px; border-radius: 6px; }
    /* PC端倒计时条位置 - 确保与商品详情页一致 */
    #coupon-countdown-bar { top: -25px; right: 15px; }
    #coupon-countdown { font-size: 14px; padding: 8px 25px; }
}

/* 移动端适配 */
@media (max-width: 768px) {
    .single-page-container { margin: 15px auto; }
    .payment-methods { grid-template-columns: <?= $pay_type == '2' ? 'repeat(2, 1fr)' : '1fr' ?>; }
    .price-quantity-row { gap: 20px; }
    .drawer-footer { position: fixed; bottom: 0; left: 0; right: 0; background: rgba(255, 255, 255, 0.72); -webkit-backdrop-filter: saturate(180%) blur(20px); backdrop-filter: saturate(180%) blur(20px); border-top: 0.5px solid rgba(0, 0, 0, 0.1); padding: 10px 15px; margin-top: 0; z-index: 999; box-shadow: 0 -4px 10px rgba(0,0,0,0.05); padding-bottom: max(10px, env(safe-area-inset-bottom)); border-radius: 0; }
    .pay-bar { padding: 8px 12px; }
    .pay-btn { padding: 10px 25px; font-size: 13px; }
    .buy-form-section.show { padding-bottom: 0; }
    /* 移动端底部付款栏是 fixed，占位放在卡片外面，避免卡片内部出现大块空白 */
    .single-page-bottom-spacer { display: block; height: calc(96px + env(safe-area-inset-bottom)); }
    /* 移动端给底部信息留出空间 */
    .main-footer { padding-bottom: 100px !important; }
}

/* 批发优惠弹窗样式 */
.discount-layer { background: #fff; border-radius: 12px; overflow: hidden; }
.discount-layer .layui-layer-content { background: #fff; }
.discount-layer .layui-layer-btn { background: #fff; border-top: 1px solid #eee; padding: 15px; }
.discount-layer .layui-layer-btn a { background: var(--theme-primary, #0c6be1); color: #fff; border-radius: 8px; height: 40px; line-height: 40px; }

/* 右侧悬浮按钮 */
.float-help-btn {
    position: fixed;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 998;
    width: 45px;
    height: auto;
    cursor: pointer;
    transition: transform 0.3s ease;
}
.float-help-btn:hover {
    transform: translateY(-50%) scale(1.1);
}
.float-help-btn img {
    width: 100%;
    height: auto;
}
@media (min-width: 769px) {
    .float-help-btn {
        right: calc((100vw - 1140px) / 2 - 70px);
        width: 50px;
    }
}
@media (min-width: 1400px) {
    .float-help-btn {
        right: calc((100vw - 1140px) / 2 - 80px);
    }
}

/* 优惠券区域样式 */
.coupon-toggle-row { background: #fff; border-radius: 8px; padding: 10px 15px 10px 15px;border: 1px solid #eee; }
.coupon-toggle-header { display: flex; align-items: center; }
.coupon-checkbox-label { display: flex; align-items: center; cursor: pointer; user-select: none; }
.coupon-checkbox { position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none; }
.coupon-checkbox-box { width: 18px; height: 18px; border: 2px solid #ddd; border-radius: 4px; margin-right: 10px; display: flex; align-items: center; justify-content: center; transition: all 0.2s; background: #fff; flex-shrink: 0; }
.coupon-checkbox:checked + .coupon-checkbox-box { background: var(--theme-accent, #ff9800); border-color: var(--theme-accent, #ff9800); }
.coupon-checkbox:checked + .coupon-checkbox-box::after { content: "✓"; color: #fff; font-size: 12px; font-weight: bold; }
.coupon-toggle-text { font-size: 14px; color: #666; font-weight: 500; }
.coupon-input-area { margin-top: 15px; border-top: 1px dashed #eee; }
.coupon-input-row { display: flex; gap: 10px; margin-top: 15px;}
.coupon-input { flex: 1; height: 40px; border: 1px solid #ddd; border-radius: 6px; padding: 0 12px; font-size: 14px; background: #fff; outline: none; }
.coupon-input:focus { border-color: #ff9800; }
.coupon-check-btn { height: 40px; padding: 0 20px; background: #ff9800; color: #fff; border: none; border-radius: 6px; font-size: 14px; cursor: pointer; white-space: nowrap; }
.coupon-check-btn:hover { background: #f57c00; }
.coupon-result { margin-top: 10px; font-size: 13px; }
.coupon-result.success { color: #4caf50; }
.coupon-result.error { color: #f44336; }
.coupon-result .coupon-discount { color: #ff5722; font-weight: bold; font-size: 15px; }
/* 隐藏 layui 渲染的复选框 */
.coupon-checkbox-label .layui-form-checkbox { display: none !important; }

/* 优惠券倒计时条样式 - 悬浮在确认付款按钮上方 */
#coupon-countdown-bar {
    position: absolute;
    top: -25px;
    right: 15px;
    transform: scale(0.8);
    transform-origin: right center;
    z-index: 10;
}
#coupon-countdown {
    color: #fff;
    font-size: 14px;
    padding: 8px 25px;
    background: url(<?= DC_URL ?>content/plugins/coupon/qianggoubj.png) no-repeat center center;
    background-size: 100% 100%;
    text-align: center;
    white-space: nowrap;
}

/* 深色模式适配 */
html[data-theme="dark"] .main-card,
html.dark-mode .main-card { background: #1e1e1e; }
html[data-theme="dark"] .section-title,
html.dark-mode .section-title { color: #e0e0e0; }
html[data-theme="dark"] .goods-select-item,
html.dark-mode .goods-select-item { background: #2a2a2a; color: #e0e0e0; border-color: #333; }
html[data-theme="dark"] .goods-select-item:hover,
html.dark-mode .goods-select-item:hover { background: #333; border-color: #555; }
html[data-theme="dark"] .goods-select-item.active,
html.dark-mode .goods-select-item.active { background: rgba(var(--theme-primary-rgb), 0.2); border-color: var(--theme-primary); color: var(--theme-primary-light); }
html[data-theme="dark"] .spec-option,
html.dark-mode .spec-option { background: #2a2a2a; color: #e0e0e0; border-color: #333; }
html[data-theme="dark"] .spec-option:hover,
html.dark-mode .spec-option:hover { background: #333; border-color: #555; }
html[data-theme="dark"] .spec-option.active,
html.dark-mode .spec-option.active { background: rgba(var(--theme-primary-rgb), 0.2); border-color: var(--theme-primary); color: var(--theme-primary-light); }
html[data-theme="dark"] .spec-group-title,
html[data-theme="dark"] .section-label,
html.dark-mode .spec-group-title,
html.dark-mode .section-label { color: #e0e0e0; }
html[data-theme="dark"] .goods-description-box,
html.dark-mode .goods-description-box { background: #252525; }
html[data-theme="dark"] .goods-description-box .intro,
html.dark-mode .goods-description-box .intro { color: #b0b0b0; }
html[data-theme="dark"] .input-field-input,
html.dark-mode .input-field-input { background: transparent; color: #e0e0e0 !important; border-bottom-color: #444; }
html[data-theme="dark"] .input-field-input::placeholder,
html.dark-mode .input-field-input::placeholder { color: #666; }
html[data-theme="dark"] .email-field-row,
html.dark-mode .email-field-row { background: #252525 !important; }
html[data-theme="dark"] .email-field-title,
html[data-theme="dark"] .email-field-desc,
html.dark-mode .email-field-title,
html.dark-mode .email-field-desc { color: #b0b0b0; }
html[data-theme="dark"] .email-input,
html.dark-mode .email-input { background: #2a2a2a !important; border-color: #444 !important; color: #e0e0e0 !important; }
html[data-theme="dark"] .payment-item,
html.dark-mode .payment-item { background: #252525; border-color: #333; }
html[data-theme="dark"] .payment-item:hover,
html.dark-mode .payment-item:hover { background: #2a2a2a; border-color: #555; }
html[data-theme="dark"] .payment-item.active,
html.dark-mode .payment-item.active { background: rgba(var(--theme-primary-rgb), 0.2); border-color: var(--theme-primary); }
html[data-theme="dark"] .payment-name,
html.dark-mode .payment-name { color: #e0e0e0; }
html[data-theme="dark"] .payment-icon,
html.dark-mode .payment-icon { background: #333; }
html[data-theme="dark"] .pay-bar,
html.dark-mode .pay-bar { background: #252525; }
html[data-theme="dark"] .pay-amount,
html.dark-mode .pay-amount { color: #e0e0e0; }
html[data-theme="dark"] .quantity-selector,
html.dark-mode .quantity-selector { border-color: #444; }
html[data-theme="dark"] .quantity-btn,
html.dark-mode .quantity-btn { background: #2a2a2a; color: #e0e0e0; }
html[data-theme="dark"] .quantity-btn:hover,
html.dark-mode .quantity-btn:hover { background: #333; }
html[data-theme="dark"] .quantity-input,
html.dark-mode .quantity-input { background: #252525; color: #e0e0e0; border-color: #444; }
html[data-theme="dark"] .coupon-toggle-row,
html.dark-mode .coupon-toggle-row { background: #252525; }
html[data-theme="dark"] .coupon-checkbox-box,
html.dark-mode .coupon-checkbox-box { background: #2a2a2a; border-color: #444; }
html[data-theme="dark"] .coupon-toggle-text,
html.dark-mode .coupon-toggle-text { color: #b0b0b0; }
html[data-theme="dark"] .coupon-input-area,
html.dark-mode .coupon-input-area { border-top-color: #444; }
html[data-theme="dark"] .coupon-input,
html.dark-mode .coupon-input { background: #2a2a2a; border-color: #444; color: #e0e0e0; }
html[data-theme="dark"] .drawer-footer,
html.dark-mode .drawer-footer { background: #1e1e1e; }
/* 单页模式公告样式 */
.single-notice-card { 
    background: rgba(248, 248, 248, 0.5); 
    border: 2px solid #fff;
    border-radius: 8px; 
    padding: 20px 25px; 
    box-shadow: 0 4px 20px rgba(0,0,0,0.08); 
    margin-bottom: 20px; 
    border-left: 4px solid var(--theme-primary, #2196F3);
    max-width: 600px; 
    margin-left: auto; 
    margin-right: auto;
}
.single-notice-card-header { 
    display: flex; 
    align-items: center; 
    margin-bottom: 15px; 
    padding-bottom: 12px; 
    border-bottom: 1px dashed #eee; 
}
.single-notice-card-header i { 
    color: var(--theme-primary, #2196F3); 
    font-size: 20px; 
    margin-right: 10px; 
}
.single-notice-card-header span { 
    font-size: 16px; 
    font-weight: 600; 
    color: #333; 
}
.single-notice-card-content { 
    font-size: 14px; 
    color: #555; 
    line-height: 2; 
}
.single-notice-card-content a { 
    color: var(--theme-primary, #2196F3); 
    text-decoration: none; 
}
.single-notice-card-content a:hover { 
    text-decoration: underline; 
}

.single-roll-notice-bar { 
    background: rgba(var(--theme-primary-rgb, 33, 150, 243), 0.1); 
    border: 1px solid rgba(var(--theme-primary-rgb, 33, 150, 243), 0.2);
    border-radius: 6px;
    padding: 10px 15px; 
    display: flex; 
    align-items: center; 
    margin-bottom: 15px; 
    overflow: hidden; 
    max-width: 600px; 
    margin-left: auto; 
    margin-right: auto;
}
.single-roll-notice-bar i { 
    color: var(--theme-primary, #2196F3); 
    font-size: 16px; 
    margin-right: 10px; 
    flex-shrink: 0;
}
.single-roll-notice-content { 
    flex: 1; 
    overflow: hidden; 
    white-space: nowrap; 
}
.single-roll-notice-vticker {
    flex: 1;
    overflow: hidden;
    height: 20px;
    line-height: 20px;
}
.single-roll-notice-vitem {
    height: 20px;
    line-height: 20px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: var(--theme-primary, #2196F3);
    font-size: 14px;
}
.single-roll-notice-content span { 
    display: inline-block; 
    color: var(--theme-primary, #2196F3); 
    font-size: 14px; 
    animation: roll-scroll 15s linear infinite; 
}
@keyframes roll-scroll { 
    0% { transform: translateX(100%); } 
    100% { transform: translateX(-100%); } 
}

/* 深色模式适配 */
html[data-theme="dark"] .single-notice-card { 
    background: #1e1e1e; 
    box-shadow: 0 4px 20px rgba(0,0,0,0.3); 
}
html[data-theme="dark"] .single-notice-card-header { 
    border-bottom-color: #333; 
}
html[data-theme="dark"] .single-notice-card-header span { 
    color: #e0e0e0; 
}
html[data-theme="dark"] .single-notice-card-content { 
    color: #b0b0b0; 
}
html[data-theme="dark"] .single-roll-notice-bar { 
    background: rgba(var(--theme-primary-rgb, 33, 150, 243), 0.15); 
    border-color: rgba(var(--theme-primary-rgb, 33, 150, 243), 0.3);
}
html[data-theme="dark"] .single-roll-notice-bar i { 
    color: var(--theme-primary-light, #64B5F6); 
}
html[data-theme="dark"] .single-roll-notice-content span { 
    color: var(--theme-primary-light, #64B5F6); 
}

@media (min-width: 769px) { 
    .single-notice-card, .single-roll-notice-bar { 
        max-width: 1140px; 
    } 
}
</style>
<main class="single-page-container">
<?php doAction('index_sorts_top'); ?>
<?php $home_bulletin = Option::get('home_bulletin'); if (!empty(trim(strip_tags($home_bulletin)))): ?>
<div class="single-notice-card">
    <div class="single-notice-card-header"><i class="layui-icon">&#xe667;</i><span>网站公告</span></div>
    <div class="single-notice-card-content"><?= $home_bulletin ?></div>
</div>
<?php endif; ?>
<?php
$roll_bulletin = Option::get('roll_bulletin');
$roll_bulletin_nl = preg_replace('/<br\s*\/?\s*>/i', "\n", (string)$roll_bulletin);
$roll_items = array_values(array_filter(array_map('trim', preg_split("/\r\n|\r|\n/", $roll_bulletin_nl)), function ($v) {
    return $v !== '';
}));
?>
<?php if (count($roll_items) > 0): ?>
<div class="single-roll-notice-bar"><i class="layui-icon">&#xe667;</i>
    <?php if (count($roll_items) <= 1): ?>
        <div class="single-roll-notice-content"><span><?= htmlspecialchars($roll_items[0] ?? '') ?></span></div>
    <?php else: ?>
        <div class="single-roll-notice-vticker js-vticker"><div class="js-vtrack">
            <?php foreach ($roll_items as $it): ?><div class="single-roll-notice-vitem"><?= htmlspecialchars($it) ?></div><?php endforeach; ?>
            <div class="single-roll-notice-vitem"><?= htmlspecialchars($roll_items[0]) ?></div>
        </div></div>
    <?php endif; ?>
</div>
<?php endif; ?>
<script>
(function(){
    if (window.__dcVtInit) { window.__dcVtInit(); return; }
    window.__dcVtInit = function(){
        var tickers = document.querySelectorAll('.js-vticker');
        for (var i=0;i<tickers.length;i++) {
            (function(ticker){
                var track = ticker.querySelector('.js-vtrack');
                if (!track) return;
                var first = track.children && track.children[0];
                if (!first) return;
                var h = first.getBoundingClientRect().height || 20;
                var count = track.children.length;
                if (count <= 2) return;
                var step = 0;
                var maxStep = count - 1;
                function go(n, animate){
                    track.style.transition = animate ? 'transform 0.45s ease' : 'none';
                    track.style.transform = 'translateY(' + (-n * h) + 'px)';
                }
                go(0,false);
                track.addEventListener('transitionend', function(){
                    if (step >= maxStep) { step = 0; go(0,false); }
                });
                setInterval(function(){ step += 1; go(step,true); }, 2600);
            })(tickers[i]);
        }
    };
    window.__dcVtInit();
})();
</script>
<?php if (!empty($goods_list)): ?>

<div class="main-card">
    <!-- 商品选择区域 -->
    <div class="section-title">选择商品</div>
    <div class="goods-select-list">
    <?php foreach ($goods_list as $key => $val): ?>
        <div class="goods-select-item" data-gid="<?= $key ?>"><?= $val['title'] ?></div>
    <?php endforeach; ?>
    </div>

    <!-- 规格选择区域 -->
    <div class="spec-section" id="specSection">
        <div class="spec-list"></div>
    </div>

    <!-- 购买表单区域 -->
    <form class="buy-form-section layui-form" id="buyFormSection">
        <div class="section-label">商品说明</div>
        <div class="goods-description-box">
            <div class="intro" id="goodsDesc"></div>
        </div>
        
        <!-- 优惠券区域 - 打勾展开 -->
        <div class="coupon-toggle-row" id="coupon-section" style="display:none;margin-bottom:20px;">
            <div class="coupon-toggle-header">
                <label class="coupon-checkbox-label">
                    <input type="checkbox" class="coupon-checkbox" id="coupon-toggle-check" lay-ignore>
                    <span class="coupon-checkbox-box"></span>
                    <span class="coupon-toggle-text">使用优惠券</span>
                </label>
            </div>
            <div class="coupon-input-area" id="coupon-input-area" style="display:none;">
                <div class="coupon-input-row">
                    <input type="text" id="coupon-code-input" placeholder="请输入优惠码" class="coupon-input">
                    <button type="button" class="coupon-check-btn" id="check-coupon-btn">验证</button>
                </div>
                <div id="coupon-result" class="coupon-result"></div>
            </div>
        </div>
        
        <div class="price-quantity-row">
            <?php if($single_price_show == 'y'): ?>
            <div class="price-section">
                <div class="section-label"><span class="required-star">*</span> 商品单价</div>
                <div class="section-value"><span class="unit-price" id="goodsPrice">0.00</span> <span class="unit" id="goodsUnit">/个</span><span class="market-price" id="goodsMarketPrice" style="display:none;"></span><span id="discountTag" style="display:none;font-size:15px;font-weight:normal;margin-left:5px;cursor:pointer;">（<span id="discountTagText">批发优惠</span>）</span></div>
            </div>
            <?php endif; ?>
            <div class="quantity-section">
                <div class="section-label"><span class="required-star">*</span> 购买数量</div>
                <div class="quantity-selector">
                    <div class="quantity-btn" id="qtyMinus">－</div>
                    <input type="number" class="quantity-input" id="qtyInput" value="1" min="1">
                    <div class="quantity-btn" id="qtyPlus">＋</div>
                </div>
            </div>
        </div>
        
        <?php if($single_stock_show == 'y'): ?>
        <div class="stock-row"><span class="required-star">*</span> 库存数量：<span class="stock-value" id="goodsStock">0</span><?php if($single_sales_show == 'y'): ?> &nbsp;&nbsp; 已售：<span class="stock-value" id="goodsSales">0</span><?php endif; ?></div>
        <?php elseif($single_sales_show == 'y'): ?>
        <div class="stock-row"><span class="required-star">*</span> 已售：<span class="stock-value" id="goodsSales">0</span></div>
        <?php endif; ?>
        
        <div id="inputFields"></div>
        
        <div class="section-label" style="margin-top:20px;"><span class="required-star">*</span> 付款方式</div>
        <div class="payment-methods">
        <?php foreach($payment as $idx => $val): ?>
            <?php if (!ISLOGIN && $val['plugin_name'] === 'balance') continue; ?>
            <div class="payment-item <?= $idx == 0 ? 'active' : '' ?>" data-method="<?= $val['plugin_name'] ?>">
                <div class="payment-icon"><img src="<?= $val['icon'] ?>" alt="<?= $val['title'] ?>"></div>
                <div class="payment-info"><div class="payment-name"><?= $val['title'] ?></div></div>
                <div class="payment-checked layui-icon layui-icon-ok-circle"></div>
            </div>
        <?php endforeach; ?>
        </div>
        
        <div id="emailFields"></div>
        
        <div class="drawer-footer">
            <div class="pay-bar">
                <div class="pay-amount">应付：<span class="dynamic-price" id="totalPrice">0.00 元</span></div>
                <button type="button" class="pay-btn" id="submitPayBtn">确认付款</button>
            </div>
        </div>
    </form>
</div>
<div class="single-page-bottom-spacer" aria-hidden="true"></div>

<?php else: ?>
<div class="main-card" style="text-align:center;padding:60px 20px;color:#999;">
    <i class="layui-icon layui-icon-face-surprised" style="font-size:50px;color:#ddd;display:block;margin-bottom:15px;"></i>
    <?= !empty($_GET['q']) ? '未找到相关商品，换个关键词试试' : '暂无商品' ?>
</div>
<?php endif; ?>
</main>
<?php doAction('tpl_footer'); ?>

<?php if($single_float_help_show == 'y'): ?>
<!-- 右侧悬浮买家帮助按钮 -->
<a href="<?= DC_URL ?>?action=help" class="float-help-btn" title="买家帮助">
    <img src="<?= !empty($float_help_icon) ? $float_help_icon : TEMPLATE_URL . 'img/mjbzp.png' ?>" alt="买家帮助">
</a>
<?php endif; ?>

<script>
layui.use(['layer', 'form'], function() {
    var $ = layui.$, layer = layui.layer, form = layui.form;
    var currentGoods = null, selectedSpecs = [], totalSpecGroups = 0;
    
    // 页面加载时滚动到顶部
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
    $(window).scrollTop(0);
    var currentDiscountList = []; // 保存当前优惠列表
    var currentUnitPrice = 0; // 保存当前原始单价
    var currentDiscountTitle = '批发优惠'; // 当前商品的优惠标题
    
    // 优惠券相关变量
    var couponDiscount = 0; // 优惠券折扣金额
    var couponValid = false; // 优惠券是否有效
    var couponApiUrl = '<?= DC_URL ?>content/plugins/coupon/api.php';

    function getGoodsUnit(goods) {
        var unit = goods && goods.unit_name ? String(goods.unit_name).trim() : '';
        return unit || '/个';
    }
    
    // 检查优惠券是否可用（需要传商品ID）
    function checkCouponEnabled(goodsId) {
        $.get(couponApiUrl + '?action=check_enabled&goods_id=' + (goodsId || 0), function(res) {
            if (res.code == 200 && res.data && res.data.enabled) {
                $('#coupon-section').show();
            } else {
                $('#coupon-section').hide();
                // 隐藏时重置优惠券状态
                couponValid = false;
                couponDiscount = 0;
                $('#coupon-toggle-check').prop('checked', false);
                $('#coupon-input-area').hide();
                $('#coupon-code-input').val('');
                $('#coupon-result').html('');
            }
        }, 'json');
    }
    
    // 优惠券打勾展开/收起
    $('#coupon-toggle-check').on('change', function() {
        if ($(this).is(':checked')) {
            $('#coupon-input-area').slideDown(200);
        } else {
            $('#coupon-input-area').slideUp(200);
            // 收起时清空状态
            $('#coupon-code-input').val('');
            $('#coupon-result').html('').removeClass('success error');
            couponValid = false;
            couponDiscount = 0;
            // 清除倒计时
            if (couponCountdownTimer) {
                cancelAnimationFrame(couponCountdownTimer);
                couponCountdownTimer = null;
            }
            // 移除倒计时条
            $('#coupon-countdown-bar').remove();
        }
    });
    
    // 优惠券验证按钮点击
    $('#check-coupon-btn').on('click', function() {
        var code = $.trim($('#coupon-code-input').val());
        if (!code) {
            layer.msg('请输入优惠码');
            return;
        }
        if (!currentGoods) {
            layer.msg('请先选择商品');
            return;
        }
        
        // 使用原始总价验证（不含优惠券折扣）
        var totalPrice = originalTotalPrice > 0 ? originalTotalPrice : (parseFloat($('#totalPrice').text()) || 0);
        var loadIdx = layer.load(2);
        
        $.post(couponApiUrl + '?action=check', {
            code: code,
            goods_id: currentGoods.id,
            amount: totalPrice // 金额单位是元
        }, function(res) {
            layer.close(loadIdx);
            if (res.code == 200) {
                couponValid = true;
                couponDiscount = parseFloat(res.data.discount); // discount 已经是元
                var expireTimestamp = parseInt(res.data.expire_timestamp) || 0; // 过期时间戳（毫秒）
                var serverTime = parseInt(res.data.server_time) || Date.now(); // 服务器时间（毫秒）
                
                var html = '✓ ' + res.data.name + '，优惠 <span class="coupon-discount">' + couponDiscount.toFixed(2) + ' 元</span>';
                
                // 如果有限时，显示倒计时（悬浮在确认付款按钮上方）
                if (expireTimestamp > 0) {
                    // 先移除旧的倒计时
                    $('#coupon-countdown-bar').remove();
                    // 在 drawer-footer 内部插入倒计时，使用绝对定位悬浮在上方
                    var countdownHtml = '<div id="coupon-countdown-bar"><div id="coupon-countdown"><span class="countdown-text"></span></div></div>';
                    $('.drawer-footer').prepend(countdownHtml);
                    $('#coupon-result').html(html).removeClass('error').addClass('success');
                    // 计算时间差（服务器时间与本地时间的偏移）
                    var timeOffset = serverTime - Date.now();
                    startCouponCountdownMs(expireTimestamp, timeOffset);
                } else {
                    $('#coupon-result').html(html).removeClass('error').addClass('success');
                }
                
                // 更新总价显示
                updateTotalPriceWithCoupon();
            } else {
                couponValid = false;
                couponDiscount = 0;
                $('#coupon-result').html('✗ ' + res.msg).removeClass('success').addClass('error');
            }
        }, 'json').fail(function() {
            layer.close(loadIdx);
            layer.msg('网络错误');
        });
    });
    
    // 优惠券倒计时（毫秒精度）
    var couponCountdownTimer = null;
    var couponExpireTimestamp = 0; // 过期时间戳（毫秒）
    var couponTimeOffset = 0; // 服务器与本地时间偏移
    
    function startCouponCountdownMs(expireTs, timeOffset) {
        // 清除之前的倒计时
        if (couponCountdownTimer) {
            cancelAnimationFrame(couponCountdownTimer);
            couponCountdownTimer = null;
        }
        
        couponExpireTimestamp = expireTs;
        couponTimeOffset = timeOffset || 0;
        
        function updateCountdown() {
            var now = Date.now() + couponTimeOffset; // 校正后的当前时间
            var remaining = couponExpireTimestamp - now;
            
            if (remaining <= 0) {
                // 优惠券过期
                couponValid = false;
                couponDiscount = 0;
                $('#coupon-countdown-bar').remove();
                $('#coupon-result').html('<span class="error">✗ 优惠券已过期，请重新验证</span>').removeClass('success').addClass('error');
                // 恢复原价显示
                if (originalTotalPrice > 0) {
                    $('#totalPrice').text(originalTotalPrice.toFixed(2) + ' 元');
                }
                layer.msg('优惠券已过期');
                couponCountdownTimer = null;
                return;
            }
            
            updateCountdownDisplayMs(remaining);
            couponCountdownTimer = requestAnimationFrame(updateCountdown);
        }
        
        updateCountdown();
    }
    
    function updateCountdownDisplayMs(ms) {
        var hours = Math.floor(ms / 3600000);
        var minutes = Math.floor((ms % 3600000) / 60000);
        var seconds = Math.floor((ms % 60000) / 1000);
        var milliseconds = Math.floor((ms % 1000) / 10); // 显示2位毫秒
        
        var text = '剩余 ';
        if (hours > 0) {
            // 超过1小时，显示 时:分:秒:毫秒
            text += String(hours).padStart(2, '0') + ':' + 
                String(minutes).padStart(2, '0') + ':' + 
                String(seconds).padStart(2, '0') + ':' + 
                String(milliseconds).padStart(2, '0');
        } else {
            // 不足1小时，只显示 分:秒:毫秒
            text += String(minutes).padStart(2, '0') + ':' + 
                String(seconds).padStart(2, '0') + ':' + 
                String(milliseconds).padStart(2, '0');
        }
        text += ' 过期';
        $('#coupon-countdown .countdown-text').text(text);
    }
    
    // 优惠码输入框变化时重置状态
    $('#coupon-code-input').on('input', function() {
        couponValid = false;
        couponDiscount = 0;
        // 清除倒计时
        if (couponCountdownTimer) {
            cancelAnimationFrame(couponCountdownTimer);
            couponCountdownTimer = null;
        }
        // 移除倒计时条
        $('#coupon-countdown-bar').remove();
        $('#coupon-result').html('').removeClass('success error');
    });
    
    // 保存原始总价（不含优惠券）
    var originalTotalPrice = 0;
    
    // 更新总价（含优惠券）
    function updateTotalPriceWithCoupon() {
        if (couponValid && couponDiscount > 0 && originalTotalPrice > 0) {
            var finalPrice = Math.max(0.01, originalTotalPrice - couponDiscount);
            var html = finalPrice.toFixed(2) + ' 元';
            html += ' <span style="font-size:12px;color:#4caf50;">（优惠券-' + couponDiscount.toFixed(2) + '元）</span>';
            $('#totalPrice').html(html);
        }
    }
    
    // 点击批发优惠显示弹窗（三类：每件优惠 / 订单优惠 / 订单折扣）
    $('#discountTag').on('click', function() {
        if(currentDiscountList.length === 0) return;

        var g1 = [], g2 = [], g3 = [];
        currentDiscountList.forEach(function(it){
            var t = parseInt(it.type || 1);
            if(t === 2) g2.push(it);
            else if(t === 3) g3.push(it);
            else g1.push(it);
        });
        var sortFn = function(a, b) { return parseInt(a.quantity) - parseInt(b.quantity); };
        g1.sort(sortFn); g2.sort(sortFn); g3.sort(sortFn);

        var html = '<div style="padding:20px;">';
        html += '<div style="font-size:18px;font-weight:bold;margin-bottom:16px;text-align:center;">' + currentDiscountTitle + '</div>';
        if(g1.length){
            html += '<div style="font-size:13px;color:#888;margin:4px 0 6px;text-align:center;">每件优惠</div>';
            g1.forEach(function(it){
                var y = (parseFloat(it.amount) / 100).toFixed(2);
                html += '<div style="font-size:14px;color:#333;margin-bottom:6px;text-align:center;">满 <span style="color:' + THEME.primary + ';font-weight:bold;">' + it.quantity + '</span> 件，每件减 <span style="color:' + THEME.price + ';font-weight:bold;">' + y + '</span> 元</div>';
            });
        }
        if(g2.length){
            html += '<div style="font-size:13px;color:#888;margin:10px 0 6px;text-align:center;">订单优惠</div>';
            g2.forEach(function(it){
                var y = (parseFloat(it.amount) / 100).toFixed(2);
                html += '<div style="font-size:14px;color:#333;margin-bottom:6px;text-align:center;">满 <span style="color:' + THEME.primary + ';font-weight:bold;">' + it.quantity + '</span> 件，整单减 <span style="color:' + THEME.price + ';font-weight:bold;">' + y + '</span> 元</div>';
            });
        }
        if(g3.length){
            html += '<div style="font-size:13px;color:#888;margin:10px 0 6px;text-align:center;">订单折扣</div>';
            g3.forEach(function(it){
                var y = (parseFloat(it.amount) / 10).toFixed(1);
                html += '<div style="font-size:14px;color:#333;margin-bottom:6px;text-align:center;">满 <span style="color:' + THEME.primary + ';font-weight:bold;">' + it.quantity + '</span> 件，整单打 <span style="color:' + THEME.price + ';font-weight:bold;">' + y + '</span> 折</div>';
            });
        }
        html += '</div>';

        layer.open({
            type: 1,
            title: false,
            closeBtn: 0,
            shadeClose: true,
            content: html,
            area: ['340px', 'auto'],
            skin: 'discount-layer',
            btn: ['关闭'],
            btnAlign: 'c'
        });
    });
    
    $('.goods-select-item').on('click', function() {
        var $this = $(this), gid = $this.data('gid');
        if(!gid) { layer.msg('商品ID为空'); return; }
        
        $('.goods-select-item').removeClass('active');
        $this.addClass('active');
        $('#specSection').removeClass('show');
        hideBuyForm();
        selectedSpecs = [];
        
        var loadIdx = layer.load(2);
        $.post('<?= DC_URL ?>user/shop.php?action=get_goods_detail', {goods_id: gid}, function(res) {
            layer.close(loadIdx);
            if (res.code == 200) {
                currentGoods = res.data;
                renderGoods(res.data);
            } else {
                layer.msg(res.msg || '加载失败');
            }
        }, 'json').fail(function() {
            layer.close(loadIdx);
            layer.msg('网络错误');
        });
    });
    
    function renderGoods(goods) {
        if (goods.spec && goods.spec.length > 0) {
            totalSpecGroups = goods.spec.length;
            var html = '';
            goods.spec.forEach(function(spec, idx) {
                html += '<div class="spec-group">';
                html += '<div class="spec-group-title">' + spec.title + '</div>';
                html += '<div class="spec-options">';
                spec.sku_values.forEach(function(sku) {
                    html += '<div class="spec-option" data-id="' + sku.id + '" data-group="' + idx + '">' + sku.name + '</div>';
                });
                html += '</div>';
                html += '</div>';
            });
            $('.spec-list').html(html);
            $('#specSection').addClass('show');
        } else {
            totalSpecGroups = 0;
            showBuyForm(goods);
        }
    }
    
    $(document).on('click', '.spec-option:not(.disabled)', function() {
        var $this = $(this), group = $this.data('group');
        $('.spec-option[data-group="' + group + '"]').removeClass('active');
        $this.addClass('active');
        
        selectedSpecs = [];
        for (var i = 0; i < totalSpecGroups; i++) {
            var $active = $('.spec-option[data-group="' + i + '"].active');
            if ($active.length) selectedSpecs.push($active.data('id'));
        }
        
        updatePriceStock();
        
        if (selectedSpecs.length >= totalSpecGroups) {
            showBuyForm(currentGoods);
        } else {
            hideBuyForm();
        }
    });
    
    // HTML实体解码函数
    function decodeHtml(html) {
        var txt = document.createElement('textarea');
        txt.innerHTML = html;
        return txt.value;
    }
    
    // 处理商品说明中的域名变量和链接
    function processGoodsContent(content) {
        if (!content) return '';
        
        // 获取当前域名并提取主域名（去掉子域名）
        var currentDomain = window.location.hostname;
        var domainParts = currentDomain.split('.');
        var mainDomain = currentDomain;
        
        // 如果有3个或以上部分（如 shop.iosdk.cn），取后两部分作为主域名
        if (domainParts.length >= 3) {
            mainDomain = domainParts.slice(-2).join('.');
        }
        
        // 替换 xxx.{DOMAIN} 为 xxx.主域名，并转换为链接
        var domainPattern = /([a-zA-Z0-9_-]+)\.(\{DOMAIN\})/g;
        content = content.replace(domainPattern, function(match, subdomain, placeholder) {
            var fullDomain = subdomain + '.' + mainDomain;
            return '<a href="http://' + fullDomain + '" target="_blank" rel="noopener" style="color:' + THEME.primary + ';text-decoration:underline;">' + fullDomain + '</a>';
        });
        
        return content;
    }
    
    function showBuyForm(goods) {
        var processedContent = processGoodsContent(decodeHtml(goods.content) || '暂无说明');
        $('#goodsDesc').html(processedContent);
        var price = parseFloat(goods.price).toFixed(2);
        $('#goodsPrice').text(price);
        $('#goodsUnit').text(getGoodsUnit(goods));
        $('#totalPrice').text(price);
        $('#goodsStock').text(goods.stock);
        $('#goodsSales').text(goods.sales || 0);
        $('#qtyInput').val(1);
        // 自定义优惠标题（空则用默认）
        currentDiscountTitle = (goods.discount_title && goods.discount_title.trim()) ? goods.discount_title : '批发优惠';
        $('#discountTagText').text(currentDiscountTitle);
        $('#discountTag').hide(); // 初始隐藏优惠标识
        
        var mp = parseFloat(goods.market_price) || 0;
        if(mp > 0 && mp > parseFloat(goods.price)){
            $('#goodsMarketPrice').text('¥' + mp.toFixed(2)).show();
        } else {
            $('#goodsMarketPrice').hide();
        }
        
        // 检查当前商品是否有可用优惠券
        checkCouponEnabled(goods.id);
        
        var isPhysicalGoods = goods.type === 'physical';
        var physicalAddressNames = ['收货人', '手机号', '所在地区', '详细地址', '买家备注'];
        $('#buyFormSection').attr('data-dc-physical-goods', isPhysicalGoods ? '1' : '0');
        if (!isPhysicalGoods) {
            $('.dc-physical-address-card').remove();
            $('[data-dc-physical-address-modal="1"]').remove();
        }
        
        var html = '';
        var emailHtml = '';
        if (goods.input) {
            for (var key in goods.input) {
                if (!goods.input[key] || !Array.isArray(goods.input[key])) continue;
                goods.input[key].forEach(function(item) {
                    var itemNameLower = item.name.toLowerCase();
                    var isEmail = itemNameLower.indexOf('邮箱') > -1 || itemNameLower.indexOf('邮件') > -1 || itemNameLower.indexOf('email') > -1 || itemNameLower.indexOf('mail') > -1 || item.type === 'email';
                    // 兆容旧数据：无 required 字段时，邮箱默认选填，其他默认必填
                    var fieldRequired = (typeof item.required !== 'undefined') ? !!item.required : !isEmail;
                    var isPhysicalAddress = isPhysicalGoods && physicalAddressNames.indexOf(item.name) !== -1;
                    if (isPhysicalGoods && ['买家备注', '备注'].indexOf(item.name) !== -1) {
                        fieldRequired = false;
                    }
                    if (isPhysicalAddress) {
                        html += '<div class="input-field-row dc-physical-address-seed" style="display:none;">';
                        html += '<input type="text" name="' + key + '[' + item.name + ']" placeholder="' + (item.placeholder || '') + '" class="input-field-input ' + (fieldRequired ? 'required-input' : '') + '">';
                        html += '</div>';
                        return;
                    }
                    
                    if (isEmail && THEME.emailPushEnabled) {
                        if (fieldRequired) {
                            // 插件已启用 + 必填：显示必填标签，输入框直接展示
                            emailHtml += '<div class="input-field-row email-field-row" style="background:#fff;padding:15px;border-radius:8px;margin-top:20px;border: 1px solid #eee;">';
                            emailHtml += '<div class="email-field-header">';
                            emailHtml += '<div class="email-field-title">邮件服务</div>';
                            emailHtml += '<span style="font-size:12px;padding:2px 10px;background:#ffebee;color:#c62828;border-radius:3px;">必填</span>';
                            emailHtml += '</div>';
                            emailHtml += '<div class="email-input-wrap" style="display:block;">';
                            emailHtml += '<input type="text" name="' + key + '[' + item.name + ']" placeholder="请输入邮箱地址" class="input-field-input email-input required-input">';
                            emailHtml += '</div>';
                            emailHtml += '<div class="email-field-desc">支付成功后自动推送卡密到邮箱</div>';
                            emailHtml += '</div>';
                        } else {
                            // 插件已启用 + 选填：开关默认关闭
                            emailHtml += '<div class="input-field-row email-field-row" style="background:#fff;padding:15px;border-radius:8px;margin-top:20px;border: 1px solid #eee;">';
                            emailHtml += '<div class="email-field-header">';
                            emailHtml += '<div class="email-field-title">邮件服务</div>';
                            emailHtml += '<label class="email-switch-label"><input type="checkbox" class="email-switch-input" data-key="' + key + '" data-name="' + item.name + '"><span class="email-switch-slider"></span></label>';
                            emailHtml += '</div>';
                            emailHtml += '<div class="email-input-wrap" style="display:none;">';
                            emailHtml += '<input type="text" data-field-name="' + key + '[' + item.name + ']" placeholder="请输入邮箱地址，无需发送可不填" class="input-field-input email-input">';
                            emailHtml += '</div>';
                            emailHtml += '<div class="email-field-desc">支付成功后自动推送卡密到邮箱（选填）</div>';
                            emailHtml += '</div>';
                        }
                    } else {
                        // 普通字段 / 插件未启用时的邮箱字段：按 required 设置渲染
                        html += '<div class="input-field-row">';
                        html += '<div class="input-field-header">';
                        html += '<div class="section-label">' + (fieldRequired ? '<span class="required-star">*</span> ' : '') + item.name + '</div>';
                        html += '<input type="text" name="' + key + '[' + item.name + ']" placeholder="' + (item.placeholder || '') + '" class="input-field-input ' + (fieldRequired ? 'required-input' : '') + '">';
                        html += '</div>';
                        var noteText = item.tip ? item.tip : (fieldRequired ? '联系信息是查询订单的重要凭证（必填）' : '选填项，可留空');
                        html += '<div class="input-field-note">' + noteText + '</div>';
                        html += '</div>';
                    }
                });
            }
        }
        $('#inputFields').html(html);
        $('#emailFields').html(emailHtml);
        if (isPhysicalGoods && window.DC_PHYSICAL_ADDRESS_REFRESH) {
            window.DC_PHYSICAL_ADDRESS_REFRESH(document.getElementById('buyFormSection'));
        }
        
        // 绑定邮箱开关事件
        $('.email-switch-input').on('change', function() {
            var $row = $(this).closest('.email-field-row');
            var $inputWrap = $row.find('.email-input-wrap');
            var $input = $row.find('.email-input');
            
            if ($(this).is(':checked')) {
                $inputWrap.slideDown(200);
                // 开启时添加name属性
                $input.attr('name', $input.data('field-name'));
            } else {
                $inputWrap.slideUp(200);
                // 关闭时移除name属性并清空值
                $input.removeAttr('name').val('');
            }
        });
        
        $('#buyFormSection').addClass('show');
        
        // 调用价格更新获取优惠信息
        updatePriceStock();
    }
    
    function hideBuyForm() {
        $('#buyFormSection').removeClass('show');
    }
    
    function updatePriceStock() {
        if (!currentGoods) return;
        $.post('<?= DC_URL ?>user/shop.php?action=goods_price_stock', {
            goods_id: currentGoods.id,
            quantity: parseInt($('#qtyInput').val()) || 1,
            sku_ids: selectedSpecs
        }, function(res) {
            if (res.code == 200) {
                var data = res.data;
                if (data.is_select_sku == 'y') {
                    // 保存原始单价和优惠列表（用于弹窗显示）
                    currentUnitPrice = parseFloat(data.unit_price) || 0;
                    currentDiscountList = data.discount_list || [];
                    
                    // 计算实际单价（原价 - 每件优惠）
                    var originalPrice = parseFloat(data.unit_price) || 0;
                    var discountPerUnit = parseFloat(data.discount_per_unit) || 0;
                    var actualUnitPrice = (originalPrice - discountPerUnit).toFixed(2);
                    
                    // 显示单价（优惠后的单价，保留2位小数）
                    $('#goodsPrice').text(actualUnitPrice);
                    
                    // 保存原始总价（不含优惠券）
                    originalTotalPrice = parseFloat(data.price) || 0;
                    
                    // 显示总价（含优惠信息）
                    if(data.discount > 0){
                        $('#totalPrice').html(data.price + ' 元 <span style="font-size:12px;color:#999;">（已优惠' + data.discount + '元）</span>');
                    } else {
                        $('#totalPrice').text(data.price + ' 元');
                    }
                    
                    // 应用优惠券折扣
                    if (couponValid && couponDiscount > 0) {
                        updateTotalPriceWithCoupon();
                    }
                    
                    // 显示/隐藏批发优惠标识
                    if(data.has_discount){
                        $('#discountTag').show();
                    } else {
                        $('#discountTag').hide();
                    }
                    
                    var mp = parseFloat(data.market_price) || 0;
                    if(mp > 0 && mp > (parseFloat(data.unit_price) || 0)){
                        $('#goodsMarketPrice').text('¥' + mp.toFixed(2)).show();
                    } else {
                        $('#goodsMarketPrice').hide();
                    }
                    
                    // 更新商品说明（优先使用SKU独立详情，没有则使用通用详情）
                    if(data.sku_content && data.sku_content.trim() !== ''){
                        var processedSkuContent = processGoodsContent(decodeHtml(data.sku_content));
                        $('#goodsDesc').html(processedSkuContent);
                    } else {
                        var processedContent = processGoodsContent(decodeHtml(currentGoods.content) || '暂无说明');
                        $('#goodsDesc').html(processedContent);
                    }
                }
                $('#goodsStock').text(data.stock > 0 ? data.stock : '已售罄');
                $('#goodsSales').text(data.sales || currentGoods.sales || 0);
                
                // 库存为0时禁用按钮
                if (data.stock <= 0 && data.is_select_sku == 'y') {
                    $('#submitPayBtn').addClass('btn-disabled').css({'background': '#ccc', 'cursor': 'not-allowed'});
                } else {
                    $('#submitPayBtn').removeClass('btn-disabled').css({'background': '#2f69d9', 'cursor': 'pointer'});
                }
                
                var specOpts = {};
                $('.spec-option').each(function() {
                    var id = $(this).data('id').toString();
                    specOpts[id] = { el: $(this), hasStock: false };
                });
                if(data.skus) {
                    data.skus.forEach(function(sku) {
                        if (sku.stock !== "0") {
                            sku.sku.split('-').forEach(function(id) {
                                if (specOpts[id]) specOpts[id].hasStock = true;
                            });
                        }
                    });
                }
                Object.values(specOpts).forEach(function(item) {
                    item.hasStock ? item.el.removeClass('disabled') : item.el.addClass('disabled');
                });
            }
        }, 'json');
    }
    
    function updateTotalPrice() {
        // 总价由 updatePriceStock 计算，这里不再单独计算
        updatePriceStock();
    }
    
    $('#qtyMinus').on('click', function() {
        var v = parseInt($('#qtyInput').val());
        if (v > 1) { $('#qtyInput').val(v - 1); updatePriceStock(); updateTotalPrice(); }
    });
    $('#qtyPlus').on('click', function() {
        $('#qtyInput').val(parseInt($('#qtyInput').val()) + 1);
        updatePriceStock(); updateTotalPrice();
    });
    $('#qtyInput').on('change', function() {
        var v = parseInt($(this).val());
        if (isNaN(v) || v < 1) $(this).val(1);
        updatePriceStock(); updateTotalPrice();
    });
    
    $('.payment-item').on('click', function() {
        $('.payment-item').removeClass('active');
        $(this).addClass('active');
    });
    
    // 表单回车提交
    $('#buyFormSection').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#submitPayBtn').click();
        }
    });
    
    // 确认付款按钮点击
    $('#submitPayBtn').on('click', function() {
        // 检查库存
        if ($(this).hasClass('btn-disabled')) {
            layer.msg('该商品库存不足，无法购买');
            return false;
        }
        
        if (!currentGoods) { layer.msg('请先选择商品'); return false; }
        
        // 先验证必填项（只验证带 required-input 类的输入框）
        var hasEmpty = false;
        var emptyFieldName = '';
        $('#inputFields .required-input, #emailFields .required-input').each(function() {
            if ($.trim($(this).val()) === '') {
                hasEmpty = true;
                var $row = $(this).closest('.input-field-row');
                emptyFieldName = ($row.find('.section-label').text() || $row.find('.email-field-title').text()).replace('*', '').trim();
                return false;
            }
        });
        
        if (hasEmpty) {
            layer.msg('请填写' + emptyFieldName);
            return false;
        }
        
        // 如果邮箱开关开启但未填写邮箱，提示填写
        if ($('.email-switch-input').is(':checked')) {
            var emailVal = $.trim($('.email-input').val());
            if (emailVal === '') {
                layer.msg('请填写邮箱地址');
                return false;
            }
            // 验证邮箱格式
            var emailReg = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!emailReg.test(emailVal)) {
                layer.msg('请输入正确的邮箱格式');
                return false;
            }
        }
        
        // 获取表单数据 - 使用serialize()保持原始格式，然后转换为对象
        var formData = $('#buyFormSection').serialize();
        
        // 添加额外字段
        formData += '&goods_id=' + currentGoods.id;
        formData += '&quantity=' + (parseInt($('#qtyInput').val()) || 1);
        if (selectedSpecs && selectedSpecs.length > 0) {
            selectedSpecs.forEach(function(spec) {
                formData += '&sku_ids[]=' + spec;
            });
        }
        formData += '&payment_plugin=' + encodeURIComponent($('.payment-item.active').data('method') || '');
        formData += '&payment_title=' + encodeURIComponent($('.payment-item.active .payment_name').text() || '');
        
        // 添加优惠券码（如果已验证有效）
        if (couponValid) {
            var couponCode = $.trim($('#coupon-code-input').val());
            if (couponCode) {
                formData += '&coupon_code=' + encodeURIComponent(couponCode);
            }
        }
        
        // 二次确认弹窗
        layer.open({
            type: 1,
            title: false,
            closeBtn: 0,
            shadeClose: false,
            area: [window.innerWidth > 500 ? '400px' : '90%', 'auto'],
            content: '<div style="padding:30px 25px;text-align:center;">' +
                '<div style="font-size:20px;font-weight:bold;color:#333;margin-bottom:20px;">温馨提示</div>' +
                '<div style="font-size:15px;color:#333;line-height:1.8;margin-bottom:25px;">付款前请仔细阅读商品说明！联系信息是查单提卡的重要凭证，建议填写手机号或字母数字组合。</div>' +
                '<div style="display:flex;gap:15px;">' +
                '<button class="layui-btn layui-btn-lg" style="flex:1;background:#e5e5e5;color:#333;border:none;border-radius:8px;height:46px;" onclick="layer.closeAll()">返回重填</button>' +
                '<button class="layui-btn layui-btn-lg" style="flex:1;background:' + THEME.button + ';color:#fff;border:none;border-radius:8px;height:46px;" id="confirmPayBtn">确认付款</button>' +
                '</div></div>',
            success: function(layero, index) {
                $('#confirmPayBtn').on('click', function() {
                    layer.close(index);
                    // 提取联系方式保存到缓存
                    var _cacheContact = '';
                    $('#inputFields .required-input').each(function() {
                        if (!_cacheContact && $.trim($(this).val())) {
                            _cacheContact = $.trim($(this).val());
                        }
                    });
                    doSubmitOrder(formData, _cacheContact);
                });
            }
        });
    });
    
    // 实际提交订单
    function doSubmitOrder(formData, cacheContact) {
        // 下单时立即保存联系方式缓存
        if (cacheContact) {
            try {
                var cached = JSON.parse(localStorage.getItem('dc_order_contacts') || '[]');
                var existIdx = cached.indexOf(cacheContact);
                if (existIdx !== -1) cached.splice(existIdx, 1);
                cached.unshift(cacheContact);
                if (cached.length > 10) cached = cached.slice(0, 10);
                localStorage.setItem('dc_order_contacts', JSON.stringify(cached));
            } catch(e) {}
        }
        var loadIdx = layer.load(2);
        $.ajax({
            url: '<?= DC_URL ?>user/shop.php?action=xiadan',
            type: 'POST',
            data: formData,
            dataType: 'json',
            timeout: 5000,
            success: function(res) {
                layer.close(loadIdx);
                if (res.code == 401 && res.login_url) { layer.msg(res.msg); setTimeout(function(){ location.href = res.login_url; }, 800); return; }
                if (res.code == 400) {
                    layer.msg(res.msg);
                } else if (res.code == 200) {
                    layer.msg('正在跳转支付页面');
                    location.href = '<?= DC_URL ?>?action=pay&out_trade_no=' + res.data.out_trade_no;
                }
            },
            error: function(xhr, status, error) {
                layer.close(loadIdx);
                layer.msg(error == 'timeout' ? '请求超时，请重试' : '请求失败：' + error);
            }
        });
    }
});
</script>

<?php else: 
// ========== 普通模式 ==========
$legacy_list_columns = _g('goods_list_columns') ?: '5';
$list_columns_pc = _g('goods_list_columns_pc') ?: $legacy_list_columns;
$list_columns_mobile = _g('goods_list_columns_mobile') ?: '2';
$goods_list_layout = _g('goods_list_layout') === 'list' ? 'list' : 'grid';
$normal_float_help_show = _g('normal_float_help_show') ?: 'y';
$float_help_icon = _g('float_help_icon') ?: '';
$category_mobile_cols = _g('category_mobile_cols') ?: '5';
$category_mobile_rows = _g('category_mobile_rows') ?: '2';
$category_slide_mode    = $_front_tpl_get('category_slide_mode', 'y');
$category_pc_slide_mode = $_front_tpl_get('category_pc_slide_mode', 'y');
$category_pc_cols = _g('category_pc_cols') ?: '0';
$custom_category_icons = $_front_tpl_get('custom_category_icons', [
    ['ri' => 'ri-money-cny-circle-line', 'ri_color' => '#ff6600', 'name' => '充值中心', 'url' => '', 'img' => '', 'newtab' => 'n'],
    ['ri' => 'ri-gamepad-line',          'ri_color' => '#2196F3', 'name' => '游戏点卡', 'url' => '', 'img' => '', 'newtab' => 'n'],
    ['ri' => 'ri-vip-crown-line',        'ri_color' => '#ff9800', 'name' => '会员服务', 'url' => '', 'img' => '', 'newtab' => 'n'],
    ['ri' => 'ri-key-2-line',            'ri_color' => '#4caf50', 'name' => '软件激活', 'url' => '', 'img' => '', 'newtab' => 'n'],
    ['ri' => 'ri-movie-line',            'ri_color' => '#e91e63', 'name' => '影音会员', 'url' => '', 'img' => '', 'newtab' => 'n'],
    ['ri' => 'ri-gift-line',             'ri_color' => '#9c27b0', 'name' => '礼品卡券', 'url' => '', 'img' => '', 'newtab' => 'n'],
    ['ri' => 'ri-smartphone-line',       'ri_color' => '#00bcd4', 'name' => '数字商品', 'url' => '', 'img' => '', 'newtab' => 'n'],
    ['ri' => 'ri-coupon-line',           'ri_color' => '#ff5722', 'name' => '优惠券',   'url' => '', 'img' => '', 'newtab' => 'n'],
    ['ri' => 'ri-service-line',          'ri_color' => '#607d8b', 'name' => '生活服务', 'url' => '', 'img' => '', 'newtab' => 'n'],
    ['ri' => 'ri-book-open-line',        'ri_color' => '#795548', 'name' => '在线教育', 'url' => '', 'img' => '', 'newtab' => 'n'],
    ['ri' => 'ri-computer-line',         'ri_color' => '#3f51b5', 'name' => '办公软件', 'url' => '', 'img' => '', 'newtab' => 'n'],
    ['ri' => 'ri-apps-2-line',           'ri_color' => '#999999', 'name' => '更多分类', 'url' => '', 'img' => '', 'newtab' => 'n'],
]);
$custom_category_icons = is_array($custom_category_icons) ? $custom_category_icons : [];
$normal_stock_show = _g('normal_stock_show') ?: 'y';
$normal_sales_show = _g('normal_sales_show') ?: 'y';
$normal_des_show   = _g('normal_des_show')   ?: 'y';
// 商品卡片扩展设置
$card_soldout_show = _g('card_soldout_show') ?: 'y';
$card_buy_style = _g('card_buy_style') ?: 'icon_cart';
$card_buy_text = _g('card_buy_text') ?: '购买';
// 轮播图 & 公告设置
$banner_show      = $_front_tpl_get('banner_show', 'y');
$banner_items     = $_front_tpl_get('banner_items', [
    ['img' => 'content/templates/default/img/Banner.png',  'url' => '', 'newtab' => 'y'],
    ['img' => 'content/templates/default/img/Banner1.png', 'url' => '', 'newtab' => 'y'],
    ['img' => 'content/templates/default/img/Banner2.png', 'url' => '', 'newtab' => 'y'],
]);
$banner_items     = is_array($banner_items) ? $banner_items : [];
$banner_speed     = (int)(_g('banner_speed') ?: 3000);
$banner_height    = (int)($_front_tpl_get('banner_height', 270) ?: 270);
$banner_animation = _g('banner_animation') ?: 'slide';
$roll_notice_mobile_show = _g('roll_notice_mobile_show') ?: 'n';
// 商品筛选排序（含双向切换：sales↑↓, price↑↓, stock↑↓）
$_gl_valid_orders = ['default','sales','sales_asc','price_asc','price_desc','stock','stock_asc'];
$_gl_order = (isset($_GET['order']) && in_array($_GET['order'], $_gl_valid_orders)) ? $_GET['order'] : 'default';
if ($_gl_order !== 'default') {
    $goods_list = array_values((array)$goods_list);
    usort($goods_list, function($a, $b) use ($_gl_order) {
        switch ($_gl_order) {
            case 'sales':      return (int)($b['sales'] ?? 0) - (int)($a['sales'] ?? 0);
            case 'sales_asc':  return (int)($a['sales'] ?? 0) - (int)($b['sales'] ?? 0);
            case 'price_asc':  return (float)($a['price'] ?? 0) <=> (float)($b['price'] ?? 0);
            case 'price_desc': return (float)($b['price'] ?? 0) <=> (float)($a['price'] ?? 0);
            case 'stock':      return (int)($b['stock'] ?? 0) - (int)($a['stock'] ?? 0);
            case 'stock_asc':  return (int)($a['stock'] ?? 0) - (int)($b['stock'] ?? 0);
        }
        return 0;
    });
}
// 分页
$_gl_per_page = (int)(_g('list_per_page') ?: 12);
$_gl_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$_gl_total = count($goods_list);
$_gl_base_url = DC_URL . '?';
if (!empty($_GET['q'])) $_gl_base_url .= 'q=' . urlencode($_GET['q']) . '&';
if (!empty($_GET['sort_id'])) $_gl_base_url .= 'sort_id=' . (int)$_GET['sort_id'] . '&';
if ($_gl_order !== 'default') $_gl_base_url .= 'order=' . urlencode($_gl_order) . '&';
$_gl_base_url .= 'page=';
$page_url = $_gl_total > $_gl_per_page ? pagination($_gl_total, $_gl_per_page, $_gl_page, $_gl_base_url) : '';
$goods_list = array_slice(array_values($goods_list), ($_gl_page - 1) * $_gl_per_page, $_gl_per_page);
if (!function_exists('_fk_type_info')) {
    function _fk_type_info($t) {
        $m = [
            'duli'    => ['一卡一密', 'fk-tb-blue'],
            'guding'  => ['固定卡密', 'fk-tb-green'],
            'xuni'    => ['虚拟服务', 'fk-tb-orange'],
            'post'    => ['接口发货', 'fk-tb-purple'],
            'once'    => ['自动发货', 'fk-tb-blue'],
            'general' => ['自动发货', 'fk-tb-blue'],
            'service' => ['人工服务', 'fk-tb-orange'],
            'physical'=> ['实物发货', 'fk-tb-orange'],
        ];
        return $m[$t] ?? [goodsTypeText($t), 'fk-tb-gray'];
    }
}
?>
<script>
// 主题配色变量供JS使用
var THEME = {
    primary: '<?= $_theme_primary ?>',
    price: '<?= $_theme_price ?>',
    button: '<?= $_theme_button ?>',
    accent: '<?= $_theme_accent ?>'
};
</script>
<link rel="stylesheet" href="<?= TEMPLATE_URL ?>css/goods-layout.css">
<style>
.blog-container { max-width: 1200px; margin: 20px auto; padding: 0 15px; width: 100%; }
.notice-card { background: rgba(248, 248, 248, 0.5); border: 2px solid #fff;border-radius: 10px; padding: 20px 25px; box-shadow: 0 0 12px rgb(0 0 0 / 10%); margin-bottom: 20px; border-left: 4px solid var(--theme-primary, #2196F3); }
.notice-card-header { display: flex; align-items: center; margin-bottom: 15px; padding-bottom: 12px; border-bottom: 1px dashed #eee; }
.notice-card-header i { color: var(--theme-primary, #2196F3); font-size: 20px; margin-right: 10px; }
.notice-card-header span { font-size: 16px; font-weight: 600; color: #333; }
.notice-card-content { font-size: 14px; color: #555; line-height: 2; }
.notice-card-content a { color: var(--theme-primary, #2196F3); text-decoration: none; }
.notice-card-content a:hover { text-decoration: underline; }
html[data-theme="dark"] .notice-card { background: #1e1e1e; box-shadow: 0 2px 12px rgba(0,0,0,0.3); }
html[data-theme="dark"] .notice-card-header { border-bottom-color: #333; }
html[data-theme="dark"] .notice-card-header span { color: #e0e0e0; }
html[data-theme="dark"] .notice-card-content { color: #b0b0b0; }
.roll-notice-bar { 
    background: rgba(var(--theme-primary-rgb, 33, 150, 243), 0.1); 
    border: 1px solid rgba(var(--theme-primary-rgb, 33, 150, 243), 0.2);
    border-radius: 6px;
    padding: 10px 15px; 
    display: flex; 
    align-items: center; 
    margin-bottom: 15px; 
    overflow: hidden; 
}
.roll-notice-bar i { 
    color: var(--theme-primary, #2196F3); 
    font-size: 16px; 
    margin-right: 10px; 
    flex-shrink: 0;
}
.roll-notice-content { 
    flex: 1; 
    overflow: hidden; 
    white-space: nowrap; 
}
.roll-notice-vticker {
    flex: 1;
    overflow: hidden;
    height: 20px;
    line-height: 20px;
}
.roll-notice-vitem {
    height: 20px;
    line-height: 20px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: var(--theme-primary, #2196F3);
    font-size: 14px;
}
.roll-notice-content span { 
    display: inline-block; 
    color: var(--theme-primary, #2196F3); 
    font-size: 14px; 
    animation: roll-scroll 15s linear infinite; 
}
@keyframes roll-scroll { 
    0% { transform: translateX(100%); } 
    100% { transform: translateX(-100%); } 
}
html[data-theme="dark"] .roll-notice-bar { 
    background: rgba(var(--theme-primary-rgb, 33, 150, 243), 0.15); 
    border-color: rgba(var(--theme-primary-rgb, 33, 150, 243), 0.3);
}
html[data-theme="dark"] .roll-notice-bar i { 
    color: var(--theme-primary-light, #64B5F6); 
}
html[data-theme="dark"] .roll-notice-content span { 
    color: var(--theme-primary-light, #64B5F6); 
}
.category-section { background: rgba(248, 248, 248, 0.5); border: 2px solid #fff; border-radius: 10px; padding: 15px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); margin-bottom: 20px; }
.category-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(90px, 1fr)); gap: 20px 15px; justify-items: center; }
.category-item { display: flex; flex-direction: column; align-items: center; text-align: center; width: 100%; cursor: pointer; text-decoration: none; transition: transform .2s ease, opacity .2s ease; }
.category-item:hover { transform: translateY(-2px); }
.category-icon { width: 64px; height: 64px; border-radius: 22px; background: linear-gradient(180deg, #ffffff 0%, #f7f9ff 100%); display: flex; align-items: center; justify-content: center; margin-bottom: 12px; overflow: hidden; box-shadow: 0 5px 10px rgba(15, 23, 42, 0.08); border: 1px solid rgba(255,255,255,0.95); }
.category-icon img { width: 64px; height: 64px; object-fit: cover; border-radius: 16px; }
.category-icon i { font-size: 28px; color: var(--theme-primary,#2196F3); }
.category-item span { font-size: 13px; color: #444; font-weight: 500; width: 100%; max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
/* 滑动模式 */
.ci-slide-wrapper { position: relative; display: flex; align-items: center; gap: 0; padding: 0 18px; }
.ci-slide-arrow { display: none; }
@media (min-width: 769px) {
    .ci-slide-arrow { display: flex; align-items: center; justify-content: center; position: absolute; top: 50%; transform: translateY(-50%); width: 36px; height: 36px; padding: 0; background: rgba(255,255,255,0.96); border: 1px solid rgba(226,232,240,0.95); border-radius: 999px; cursor: pointer; font-size: 24px; line-height: 1; color: #64748b; transition: all 0.2s ease; z-index: 3; box-shadow: 0 8px 24px rgba(15,23,42,0.12); }
    .ci-slide-prev { left: -2px; }
    .ci-slide-next { right: -2px; }
    .ci-slide-arrow:hover { background: var(--theme-primary,#2196F3); color: #fff; border-color: var(--theme-primary,#2196F3); box-shadow: 0 12px 28px rgba(var(--theme-primary-rgb, 33, 150, 243), 0.32); }
    .ci-slide-wrapper .category-grid.slide-mode { flex: 1; min-width: 0; }
}
.category-grid.slide-mode { display: flex; overflow-x: auto; flex-wrap: nowrap; gap: 10px 18px; padding: 8px 10px 10px; scroll-snap-type: x proximity; scroll-padding-left: 10px; scroll-padding-right: 10px; -webkit-overflow-scrolling: touch; scrollbar-width: none; justify-content: flex-start; align-items: flex-start; }
.category-grid.slide-mode::-webkit-scrollbar { display: none; }
.category-grid.slide-mode .category-item { flex: 0 0 auto; width: 78px; padding: 4px 2px; scroll-snap-align: start; }
.category-grid.slide-mode .category-icon { width: 58px; height: 58px; border-radius: 18px; margin-bottom: 10px; box-shadow: 0 10px 22px rgba(15, 23, 42, 0.10); }
.category-grid.slide-mode .category-icon img { width: 58px; height: 58px; border-radius: 14px; }
.category-grid.slide-mode .category-icon i { font-size: 24px; }
.category-grid.slide-mode .category-item span { font-size: 12px; color: #475569; }
.ci-slide-wrapper::before, .ci-slide-wrapper::after { content: ''; position: absolute; top: 0; bottom: 8px; width: 28px; pointer-events: none; z-index: 2; }
.ci-slide-wrapper .category-grid.slide-mode:hover + .ci-slide-arrow,
.ci-slide-wrapper:hover .ci-slide-arrow { opacity: 1; }
.ci-slide-arrow { opacity: 0.92; }
.category-section .ci-slide-wrapper + .ci-mobile-pager { margin-top: 6px; }
html[data-theme="dark"] .category-section { background: linear-gradient(180deg, rgba(17,24,39,0.92) 0%, rgba(15,23,42,0.88) 100%); border-color: rgba(255,255,255,0.06); box-shadow: 0 14px 32px rgba(0,0,0,0.24); }
html[data-theme="dark"] .category-item span { color: #dbe4f0; }
html[data-theme="dark"] .category-icon { background: linear-gradient(180deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0.04) 100%); border-color: rgba(255,255,255,0.08); box-shadow: 0 10px 22px rgba(0,0,0,0.18); }
html[data-theme="dark"] .ci-slide-arrow { background: rgba(17,24,39,0.92); border-color: rgba(255,255,255,0.08); color: #cbd5e1; box-shadow: 0 10px 24px rgba(0,0,0,0.28); }
html[data-theme="dark"] .ci-slide-wrapper::before { background: linear-gradient(90deg, rgba(15,23,42,0.95) 0%, rgba(15,23,42,0) 100%); }
html[data-theme="dark"] .ci-slide-wrapper::after { background: linear-gradient(270deg, rgba(15,23,42,0.95) 0%, rgba(15,23,42,0) 100%); }
.empty-container { padding: 60px 0; text-align: center; }
/* 分类标题行 */
.category-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px; }
.category-header-title { font-size: 15px; font-weight: 700; color: #333; }
.category-all-btn { font-size: 12px; color: var(--theme-primary, #2196F3); text-decoration: none; padding: 4px 12px; border-radius: 20px; border: 1px solid var(--theme-primary, #2196F3); transition: all 0.2s; white-space: nowrap; }
.category-all-btn:hover { background: var(--theme-primary, #2196F3); color: #fff; text-decoration: none; }
/* 商品类型覆盖徽章（图片右上角） */
.fk-type-badge { position: absolute; top: 10px; right: 10px; z-index: 2; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 600; color: #fff; white-space: nowrap; }
.fk-tb-blue   { background: linear-gradient(135deg,#00c6ff,#0072ff); box-shadow: 0 2px 8px rgba(0,114,255,0.4); }
.fk-tb-green  { background: linear-gradient(135deg,#43e97b,#38f9d7); box-shadow: 0 2px 8px rgba(56,249,215,0.4); color: #1a5c45; }
.fk-tb-orange { background: linear-gradient(135deg, #ff9800, #f57c00); box-shadow: 0 2px 8px rgba(255,152,0,0.4); color: #fff; }
.fk-tb-purple { background: linear-gradient(135deg,#a18cd1,#fbc2eb); box-shadow: 0 2px 8px rgba(161,140,209,0.4); color: #4a1a6b; }
.fk-tb-gray   { background: linear-gradient(135deg,#b0bec5,#90a4ae); box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
/* 售空遮罩 */
.goods-soldout-mask { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; z-index: 3; }
.goods-soldout-mask span { color: #fff; font-size: 16px; font-weight: 600; padding: 6px 16px; background: rgba(0,0,0,0.5); border-radius: 20px; letter-spacing: 2px; }
.goods-card-soldout { opacity: 0.85; }
.goods-card-soldout:hover { opacity: 1; }
/* 价格后缀 */
.price-suffix { font-size: 15px; font-weight: normal; color: #ff4d4f; margin-left: 2px; }
.price-original { font-size: 12px; color: #bbb; font-weight: normal; text-decoration: line-through; margin-left: 6px; }
/* 购买按钮样式 */
.buy-btn { padding: 4px 10px; background: var(--theme-button, #2196F3); color: #fff; font-size: 12px; font-weight: 500; border-radius: 5px; white-space: nowrap; transition: all 0.2s; }
.goods-card:hover .buy-btn { background: var(--theme-button-dark, #1976D2); box-shadow: 0 3px 10px rgba(var(--theme-button-rgb, 33,150,243),0.3); }
/* ===== Hero区域（轮播图 + 公告同行） ===== */
.fk-hero-row { display: flex; gap: 14px; align-items: stretch; margin-bottom: 16px; }
.fk-banner-wrap { flex: 1 1 65%; min-width: 0; border-radius: 12px; overflow: hidden; position: relative; background: #f0f0f0; -webkit-mask-image: -webkit-radial-gradient(white, black); border: 2px solid #fff;box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);}
.fk-banner-wrap.fk-banner-empty { display: none; }
.fk-notice-col { flex: 0 0 auto; width: 35%; min-width: 180px; }
.fk-hero-row.no-banner .fk-notice-col { width: 100%; }
@media (max-width: 768px) {
    .fk-hero-row { flex-direction: column; gap: 10px; }
    .fk-banner-wrap { flex: none; width: 100%; border-radius: 10px; }
    .fk-notice-col { width: 100% !important; }
    .notice-hidden-mobile { display: none !important; } /* 网站公告(notice-card)手机端隐藏 */
    /* 移动端高度自适应，不跟随PC固定高度 */
    .fk-banner { height: auto !important; }
    .fk-banner-track { height: auto !important; }
    .fk-banner-slide img { height: auto !important; width: 100%; object-fit: contain; aspect-ratio: auto; }
}
/* 轮播图 */
.fk-banner { position: relative; width: 100%; overflow: hidden; border-radius: 12px; }
.fk-banner-track { display: flex; will-change: transform; }
.fk-banner-track.is-fade .fk-banner-slide { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; transition: opacity 0.6s ease; }
.fk-banner-track.is-fade .fk-banner-slide.active { opacity: 1; position: relative; }
.fk-banner-slide { flex-shrink: 0; width: 100%; background: #f0f0f0; position: relative; overflow: hidden; }
.fk-banner-slide a { display: block; width: 100%; height: 100%; }
.fk-banner-slide::before { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg, #f0f0f0 25%, #e8e8e8 37%, #f0f0f0 63%); background-size: 400% 100%; animation: bannerSkeleton 1.4s ease infinite; z-index: 0; }
.fk-banner-slide img { width: 100%; height: 100%; object-fit: cover; display: block; opacity: 0; transition: opacity 0.5s ease; position: relative; z-index: 1; }
.fk-banner-slide img.loaded { opacity: 1; }
@keyframes bannerSkeleton { 0% { background-position: 100% 50%; } 100% { background-position: 0 50%; } }
.fk-banner-dots { position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%); display: flex; gap: 6px; z-index: 3; }
.fk-banner-dot { width: 6px; height: 6px; border-radius: 3px; background: rgba(255,255,255,0.5); cursor: pointer; transition: all 0.25s; border: none; padding: 0; }
.fk-banner-dot.active { width: 20px; background: #fff; }
.fk-banner-arrow { position: absolute; top: 50%; transform: translateY(-50%); z-index: 3; background: rgba(0,0,0,0.25); color: #fff; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 18px; display: none; align-items: center; justify-content: center; transition: background 0.2s; }
.fk-banner-arrow:hover { background: rgba(0,0,0,0.55); }
.fk-banner-prev { left: 10px; }
.fk-banner-next { right: 10px; }
/* Hero右列：网站公告(notice-card)撑满高度 — ⚠️注意是 notice-card 非 roll-notice-bar */
.fk-notice-col .notice-card { height: 100%; margin-bottom: 0; box-sizing: border-box; overflow-y: auto; }
/* ===== 新版筛选栏 ===== */
.fk-filter-bar { display: flex; align-items: center; gap: 6px; overflow-x: auto; padding: 4px 0 12px; margin-bottom: 10px; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
.fk-filter-bar::-webkit-scrollbar { display: none; }
.fk-filter-label { flex-shrink: 0; font-size: 12px; color: #aaa; white-space: nowrap; padding-right: 4px; }
.fk-filter-chip { flex-shrink: 0; display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 5px; font-size: 13px; font-weight: 500; text-decoration: none !important; color: #555; background:rgba(248, 248, 248, 0.5); border: 1.5px solid #fff; box-shadow: 0 1px 4px rgba(0,0,0,0.06); transition: all 0.2s; white-space: nowrap; backdrop-filter: blur(4px); }
.fk-filter-chip:hover { color: var(--theme-primary,#2196F3); border-color: var(--theme-primary,#2196F3); box-shadow: 0 2px 8px rgba(33,150,243,0.15); text-decoration: none !important; }
.fk-filter-chip.active { color: #fff; background: var(--theme-primary,#2196F3); border-color: var(--theme-primary,#2196F3); }
.fk-chip-ri { font-size: 14px; line-height: 1; margin-right: 4px; vertical-align: -1px; }
.fk-chip-icon { font-size: 13px; line-height: 1; margin-left: 1px; vertical-align: -1px; opacity: 0.85; }
.goods-grid.goods-grid-layout-grid { grid-template-columns: repeat(<?= (int)$list_columns_pc ?>, 1fr); }
.goods-grid.goods-grid-layout-list { grid-template-columns: repeat(<?= (int)$list_columns_pc ?>, 1fr); }
@media (max-width: 768px) {
    .fk-filter-bar { justify-content: space-between; gap: 6px; }
    .fk-filter-chip { flex: 1; justify-content: center; padding-left: 0; padding-right: 0; flex-shrink: 1; }
    .fk-filter-chip:not(:first-child) .fk-chip-ri { display: none; }
}
@media (min-width: 769px) {
    .fk-filter-bar { padding-bottom: 2px; margin-bottom: 20px; flex-wrap: wrap; overflow-x: visible; gap: 8px; }
    .fk-filter-chip { padding: 5px 15px; font-size: 13px; }
    .fk-banner-arrow { display: flex; } /* 箭头仅PC显示，移动端默认none */
}
/* 商品简介 */
.goods-desc { font-size: 12px; color: #999; line-height: 1.5; margin-bottom: 7px; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; height: 18px; }
/* 商品 meta 信息 */
.goods-meta { display: flex; align-items: center; gap: 6px; margin-bottom: 6px; flex-wrap: wrap; }
.goods-meta-stock, .goods-meta-sales { font-size: 11px; color: #bbb; }
/* ===== 横向卡片布局（从0重构） ===== */
.goods-grid.goods-grid-list {
    gap: 16px;
}
.goods-grid.goods-grid-list .goods-grid-item {
    min-width: 0;
}
.goods-grid.goods-grid-list .goods-card {
    display: flex;
    flex-direction: row;
    align-items: stretch;
    overflow: hidden;
}
/* 左侧图片容器 - 固定1:1比例 */
.goods-grid.goods-grid-list .goods-img-box {
    position: relative;
    width: 140px;
    height: 140px;
    min-width: 140px;
    max-width: 140px;
    padding-top: 0;
    flex-shrink: 0;
    overflow: hidden;
}
/* 图片本身 - 填充满容器 */
.goods-grid.goods-grid-list .goods-img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}
/* 右侧信息区 */
.goods-grid.goods-grid-list .goods-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 10px 18px;
    box-sizing: border-box;
}
/* 标题 - 单行省略，不换行 */
.goods-grid.goods-grid-list .goods-title {
    height: auto;
    min-height: 0;
    margin-bottom: 8px;
    font-size: 15px;
    line-height: 1.5;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
}
/* 简介 */
.goods-grid.goods-grid-list .goods-desc {
    height: auto;
    margin-bottom: 8px;
    -webkit-line-clamp: 1;
}
/* meta信息 */
.goods-grid.goods-grid-list .goods-meta {
    margin-bottom: 10px;
}
/* 价格行 */
.goods-grid.goods-grid-list .goods-price-row {
    margin-top: auto;
}
.goods-grid.goods-grid-list .price-current {
    font-size: 18px;
}
.goods-grid.goods-grid-list .buy-icon {
    width: 30px;
    height: 30px;
}
/* 分页器 */
.goods-pagination { margin-top: 24px; text-align: center; padding-bottom: 10px; display: flex; justify-content: center; align-items: center; gap: 6px; flex-wrap: wrap; }
.goods-pagination .btn.ghost { display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 10px; border-radius: 8px; font-size: 13px; font-weight: 500; color: #555; background: rgba(255,255,255,0.85); border: 1.5px solid #e8e8e8; text-decoration: none; transition: all 0.2s; cursor: pointer; backdrop-filter: blur(4px); box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
.goods-pagination .btn.ghost:hover { color: var(--theme-primary,#2196F3); border-color: var(--theme-primary,#2196F3); box-shadow: 0 2px 8px rgba(33,150,243,0.15); }
.goods-pagination .btn.ghost.active { color: #fff; background: var(--theme-primary,#2196F3); border-color: var(--theme-primary,#2196F3); box-shadow: 0 3px 10px rgba(33,150,243,0.3); cursor: default; }
.goods-pagination em.btn.ghost { border-color: transparent; background: transparent; box-shadow: none; color: #aaa; cursor: default; font-style: normal; }
/* 分类移动端分页滑动 */
.ci-mobile-pager { display: none; }
@media (max-width: 768px) {
    .ci-has-pager .ci-desktop-grid { display: none; }
    .ci-has-pager .ci-mobile-pager { display: block; }
    .ci-mobile-pager .category-pager { overflow: hidden; }
    .ci-pages-track { display: flex; transition: transform 0.32s cubic-bezier(.4,0,.2,1); will-change: transform; }
    .ci-page { min-width: 100%; flex-shrink: 0; }
    .ci-dots { display: flex; justify-content: center; gap: 5px; padding: 10px 0 2px; }
    .ci-dot { width: 7px; height: 5px; border-radius: 1%; background: #ccc; cursor: pointer; transition: all 0.2s; flex-shrink: 0; }
    .ci-dot.active { background: var(--theme-primary, #2196F3); width: 18px; border-radius: 1px; }
}
/* 移动端分类列数 */
@media (max-width: 768px) {
    .goods-grid.goods-grid-layout-grid { grid-template-columns: repeat(<?= (int)$list_columns_mobile ?>, 1fr); }
    .goods-grid.goods-grid-layout-list { grid-template-columns: 1fr; }
    .category-grid { grid-template-columns: repeat(<?= $category_mobile_cols ?>, 1fr); gap: 15px 10px; }
    .category-icon { width: <?= $category_mobile_cols == '5' ? '48px' : ($category_mobile_cols == '4' ? '54px' : '60px') ?>; height: <?= $category_mobile_cols == '5' ? '48px' : ($category_mobile_cols == '4' ? '54px' : '60px') ?>; border-radius: 16px; margin-bottom: 8px; }
    .category-icon img { width: <?= $category_mobile_cols == '5' ? '43px' : ($category_mobile_cols == '4' ? '36px' : '40px') ?>; height: <?= $category_mobile_cols == '5' ? '43px' : ($category_mobile_cols == '4' ? '36px' : '40px') ?>; }
    .category-item span { font-size: <?= $category_mobile_cols == '5' ? '11px' : '12px' ?>; }
    /* 移动端横向卡片 */
    .goods-grid.goods-grid-list .goods-img-box {
        width: 120px;
        height: 120px;
        min-width: 120px;
        max-width: 120px;
    }
    .goods-grid.goods-grid-list .goods-info {
        padding: 8px 14px;
    }
    .goods-grid.goods-grid-list .goods-title {
        font-size: 14px;
        margin-bottom: 6px;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    .goods-grid.goods-grid-list .goods-desc {
        margin-bottom: 6px;
        font-size: 11px;
    }
    .goods-grid.goods-grid-list .goods-meta {
        margin-bottom: 6px;
    }
    .goods-grid.goods-grid-list .price-current {
        font-size: 16px;
    }
    .goods-grid.goods-grid-list .buy-icon {
        width: 26px;
        height: 26px;
    }
}
</style>
<main class="blog-container">
<?php doAction('index_sorts_top'); ?>
<?php
/*
 * =====================================================================
 * Hero 区域：轮播图（左） + 网站公告 notice-card（右）
 * -----------------------------------------------------------------------
 * - 轮播图：$banner_items，来自模板设置 banner_show / banner_items
 * - 网站公告：$home_bulletin，来自系统选项 home_bulletin，渲染为 .notice-card
 * -----------------------------------------------------------------------
 * ⚠️ 注意区分两种公告：
 *   ① notice-card   = 网站公告（home_bulletin），富文本，在此 Hero 右列展示
 *   ② roll-notice-bar = 滚动公告（roll_bulletin），纯文本滚动条，在 Hero 下方单独展示
 * =====================================================================
 */
$home_bulletin = Option::get('home_bulletin');
$_has_home_bulletin = !empty(trim(strip_tags((string)$home_bulletin)));
$_has_banner = $banner_show == 'y' && !empty($banner_items);

// Hero 行：有轮播图 或 有网站公告时显示
if ($_has_banner || $_has_home_bulletin):
    $_hero_cls = 'fk-hero-row' . (!$_has_banner ? ' no-banner' : '');
?>
<div class="<?= $_hero_cls ?>">
    <!-- ① 左列：轮播图（banner_items，模板设置） -->
    <?php if ($_has_banner): ?>
    <div class="fk-banner-wrap">
        <div class="fk-banner" id="fk-banner" style="height:<?= $banner_height ?>px;">
            <div class="fk-banner-track<?= $banner_animation == 'fade' ? ' is-fade' : '' ?>" id="fk-banner-track"
                 data-speed="<?= $banner_speed ?>" data-anim="<?= htmlspecialchars($banner_animation) ?>">
                <?php foreach ($banner_items as $bi_k => $bi): ?>
                <div class="fk-banner-slide<?= ($banner_animation == 'fade' && $bi_k === 0) ? ' active' : '' ?>">
                    <?php if (!empty($bi['url'])): ?>
                    <a href="<?= htmlspecialchars($bi['url']) ?>"<?= (($bi['newtab'] ?? 'y') === 'y') ? ' target="_blank" rel="noopener noreferrer"' : '' ?>>
                        <img src="<?= htmlspecialchars($bi['img']) ?>" alt="banner" loading="lazy" onload="this.classList.add('loaded')">
                    </a>
                    <?php else: ?>
                    <img src="<?= htmlspecialchars($bi['img']) ?>" alt="banner" style="width:100%;height:100%;object-fit:cover;display:block;" loading="lazy" onload="this.classList.add('loaded')">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($banner_items) > 1): ?>
            <div class="fk-banner-dots" id="fk-banner-dots">
                <?php foreach ($banner_items as $bi_i => $bi): ?>
                <button class="fk-banner-dot<?= $bi_i === 0 ? ' active' : '' ?>" data-idx="<?= $bi_i ?>"></button>
                <?php endforeach; ?>
            </div>
            <button class="fk-banner-arrow fk-banner-prev" id="fk-banner-prev">&#8249;</button>
            <button class="fk-banner-arrow fk-banner-next" id="fk-banner-next">&#8250;</button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ② 右列：网站公告 notice-card（home_bulletin，系统设置→网站公告）
         roll_notice_mobile_show='n' 时移动端隐藏此列，通过 .notice-hidden-mobile 实现 -->
    <?php if ($_has_home_bulletin): ?>
    <div class="fk-notice-col<?= $roll_notice_mobile_show == 'n' ? ' notice-hidden-mobile' : '' ?>">
        <div class="notice-card">
            <div class="notice-card-header"><i class="layui-icon">&#xe667;</i><span>网站公告</span></div>
            <div class="notice-card-content"><?= $home_bulletin ?></div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php
/*
 * 滚动公告（roll_bulletin）：纯文本滚动条，单独一行显示
 * 与上方 notice-card（网站公告）完全不同，请勿混淆
 */
$roll_bulletin = Option::get('roll_bulletin');
$roll_bulletin_nl = preg_replace('/<br\s*\/?\s*>/i', "\n", (string)$roll_bulletin);
$roll_items = array_values(array_filter(array_map('trim', preg_split("/\r\n|\r|\n/", $roll_bulletin_nl)), function($v){ return $v !== ''; }));
?>
<?php if (count($roll_items) > 0): ?>
<div class="roll-notice-bar"><i class="layui-icon">&#xe667;</i>
    <?php if (count($roll_items) <= 1): ?>
        <div class="roll-notice-content"><span><?= htmlspecialchars($roll_items[0] ?? '') ?></span></div>
    <?php else: ?>
        <div class="roll-notice-vticker js-vticker"><div class="js-vtrack">
            <?php foreach ($roll_items as $it): ?><div class="roll-notice-vitem"><?= htmlspecialchars($it) ?></div><?php endforeach; ?>
            <div class="roll-notice-vitem"><?= htmlspecialchars($roll_items[0]) ?></div>
        </div></div>
    <?php endif; ?>
</div>
<?php endif; ?>
<script>
(function(){
    var banner = document.getElementById('fk-banner');
    if (!banner) return;
    var track   = document.getElementById('fk-banner-track');
    var dotsWrap = document.getElementById('fk-banner-dots');
    var prevBtn = document.getElementById('fk-banner-prev');
    var nextBtn = document.getElementById('fk-banner-next');
    if (!track) return;
    var slides = track.querySelectorAll('.fk-banner-slide');
    var total  = slides.length;
    if (total < 1) return;
    var speed  = parseInt(track.getAttribute('data-speed')) || 3000;
    var anim   = track.getAttribute('data-anim') || 'slide';
    var cur    = 0;
    var dots   = dotsWrap ? dotsWrap.querySelectorAll('.fk-banner-dot') : [];
    var timer;

    function setSlide(idx, animate) {
        if (idx < 0) idx = total - 1;
        if (idx >= total) idx = 0;
        cur = idx;
        if (anim === 'fade') {
            for (var i = 0; i < slides.length; i++) {
                slides[i].classList.remove('active');
            }
            slides[cur].classList.add('active');
        } else {
            var dur = animate === false ? 'none' : 'transform 0.5s cubic-bezier(.4,0,.2,1)';
            track.style.transition = dur;
            track.style.transform = 'translateX(-' + (cur * 100) + '%)';
        }
        for (var d = 0; d < dots.length; d++) {
            dots[d].classList.toggle('active', d === cur);
        }
    }

    // 初始化fade模式
    if (anim === 'fade') {
        slides[0].classList.add('active');
    }

    function startAuto() {
        clearInterval(timer);
        timer = setInterval(function(){ setSlide(cur + 1, true); }, speed);
    }

    if (prevBtn) prevBtn.addEventListener('click', function(){ setSlide(cur - 1, true); startAuto(); });
    if (nextBtn) nextBtn.addEventListener('click', function(){ setSlide(cur + 1, true); startAuto(); });
    for (var d = 0; d < dots.length; d++) {
        (function(i){ dots[i].addEventListener('click', function(){ setSlide(i, true); startAuto(); }); })(d);
    }

    // 触摸滑动
    var tx = 0;
    banner.addEventListener('touchstart', function(e){ tx = e.touches[0].clientX; }, {passive:true});
    banner.addEventListener('touchend', function(e){
        var dx = e.changedTouches[0].clientX - tx;
        if (Math.abs(dx) > 40) { setSlide(dx < 0 ? cur + 1 : cur - 1, true); startAuto(); }
    }, {passive:true});

    if (total > 1) startAuto();

    // vticker
    (function(){
        if (window.__dcVtInit) return;
        window.__dcVtInit = function(){
            var tickers = document.querySelectorAll('.js-vticker');
            for (var i=0;i<tickers.length;i++){(function(ticker){
                var track2 = ticker.querySelector('.js-vtrack'); if (!track2) return;
                var first = track2.children[0]; if (!first) return;
                var h = first.getBoundingClientRect().height || 20;
                var count = track2.children.length;
                if (count <= 2 || ticker.__dc_vt_bound) return;
                ticker.__dc_vt_bound = true;
                var step = 0, maxStep = count - 1;
                function go(n, an){ track2.style.transition = an ? 'transform 0.45s ease' : 'none'; track2.style.transform = 'translateY('+(-n*h)+'px)'; }
                go(0,false);
                track2.addEventListener('transitionend', function(){ if (step >= maxStep){ step=0; go(0,false); } });
                setInterval(function(){ step++; go(step,true); }, 2600);
            })(tickers[i]);}
        };
        function run(){ if(window.__dcVtInit) window.__dcVtInit(); }
        if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',run); else run();
    })();
})();
</script>
<?php
$_ci_use_custom = !empty($custom_category_icons) && is_array($custom_category_icons);
$_ci_show = $_front_tpl_get('category_show', 'y') == 'y';
$_ci_has_db = !empty($sort);
if ($_ci_show && ($_ci_use_custom || $_ci_has_db)):
    // 合并分类数据为统一格式
    if ($_ci_use_custom) {
        $_ci_all = $custom_category_icons;
    } else {
        $_ci_all = [];
        foreach ($sort as $v) {
            $_ci_all[] = ['img' => $v['sortimg'], 'name' => $v['sortname'], 'url' => Url::sort($v['sid'])];
        }
    }
    // 移动端分页：滑动模式开启 + 行数 > 0
    $_ci_page_size = ($category_slide_mode == 'y' && (int)$category_mobile_rows > 0)
        ? max(1, (int)$category_mobile_rows * (int)$category_mobile_cols) : 0;
    $_ci_pages = $_ci_page_size > 0 ? array_chunk($_ci_all, $_ci_page_size) : [];
    $_ci_pages_count = count($_ci_pages);
    // 桌面端样式（独立PC滑动模式）
    $_ci_grid_class = $category_pc_slide_mode == 'y' ? 'category-grid slide-mode' : 'category-grid';
    $_ci_grid_style = '';
    if ($category_pc_slide_mode != 'y' && $category_pc_cols != '0') {
        $_ci_grid_style = ' style="grid-template-columns:repeat(' . (int)$category_pc_cols . ',1fr);"';
    }
    $_ci_section_class = 'category-section' . ($_ci_page_size > 0 ? ' ci-has-pager' : '');
?>
<div class="<?= $_ci_section_class ?>">
    <?php // 桌面端网格（PC滑动模式时包裹箭头） ?>
    <?php if ($category_pc_slide_mode == 'y'): ?>
    <div class="ci-slide-wrapper">
        <button type="button" class="ci-slide-arrow ci-slide-prev" onclick="ciSlideScroll(-1)" title="向左">&#8249;</button>
    <?php endif; ?>
    <div class="<?= $_ci_grid_class ?> ci-desktop-grid" id="ci-slide-track"<?= $_ci_grid_style ?>>
        <?php foreach ($_ci_all as $ci): ?>
        <a href="<?= htmlspecialchars($ci['url'] ?: DC_URL) ?>" class="category-item"<?= (($ci['newtab'] ?? 'n') === 'y') ? ' target="_blank" rel="noopener noreferrer"' : '' ?>>
            <div class="category-icon"><?php if (!empty($ci['img'])): ?><img src="<?= htmlspecialchars($ci['img']) ?>" alt="<?= htmlspecialchars($ci['name'] ?? '') ?>" onerror="this.src='<?= DC_URL ?>admin/views/images/cover.svg';"><?php elseif (!empty($ci['ri'])): ?><i class="<?= htmlspecialchars($ci['ri']) ?>"<?= !empty($ci['ri_color']) ? ' style="color:'.htmlspecialchars($ci['ri_color']).'"' : '' ?>></i><?php else: ?><img src="<?= DC_URL ?>admin/views/images/cover.svg" alt=""><?php endif; ?></div>
            <span><?= htmlspecialchars($ci['name'] ?? '') ?></span>
        </a>
        <?php endforeach; ?>
    </div>
    <?php if ($category_pc_slide_mode == 'y'): ?>
        <button type="button" class="ci-slide-arrow ci-slide-next" onclick="ciSlideScroll(1)" title="向右">&#8250;</button>
    </div>
    <?php endif; ?>
    <?php if ($_ci_page_size > 0): // 移动端分页滑动 ?>
    <div class="ci-mobile-pager">
        <div class="category-pager">
            <div class="ci-pages-track" id="ci-pages-track">
                <?php foreach ($_ci_pages as $_pg_items): ?>
                <div class="ci-page">
                    <div class="category-grid">
                        <?php foreach ($_pg_items as $ci): ?>
                        <a href="<?= htmlspecialchars($ci['url'] ?: DC_URL) ?>" class="category-item"<?= (($ci['newtab'] ?? 'n') === 'y') ? ' target="_blank" rel="noopener noreferrer"' : '' ?>>
                            <div class="category-icon"><?php if (!empty($ci['img'])): ?><img src="<?= htmlspecialchars($ci['img']) ?>" alt="<?= htmlspecialchars($ci['name'] ?? '') ?>" onerror="this.src='<?= DC_URL ?>admin/views/images/cover.svg';"><?php elseif (!empty($ci['ri'])): ?><i class="<?= htmlspecialchars($ci['ri']) ?>"<?= !empty($ci['ri_color']) ? ' style="color:'.htmlspecialchars($ci['ri_color']).'"' : '' ?>></i><?php else: ?><img src="<?= DC_URL ?>admin/views/images/cover.svg" alt=""><?php endif; ?></div>
                            <span><?= htmlspecialchars($ci['name'] ?? '') ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php if ($_ci_pages_count > 1): ?>
        <div class="ci-dots" id="ci-dots">
            <?php for ($d = 0; $d < $_ci_pages_count; $d++): ?>
            <span class="ci-dot<?= $d === 0 ? ' active' : '' ?>" onclick="ciGoPage(<?= $d ?>)"></span>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
    <script>
    (function(){
        var cur = 0, total = <?= $_ci_pages_count ?>;
        window.ciGoPage = function(idx) {
            cur = Math.max(0, Math.min(idx, total - 1));
            var t = document.getElementById('ci-pages-track');
            if (t) t.style.transform = 'translateX(-' + (cur * 100) + '%)';
            document.querySelectorAll('#ci-dots .ci-dot').forEach(function(d, i){ d.classList.toggle('active', i === cur); });
        };
        var track = document.getElementById('ci-pages-track');
        if (track) {
            var sx = 0;
            track.addEventListener('touchstart', function(e){ sx = e.touches[0].clientX; }, {passive: true});
            track.addEventListener('touchend', function(e){
                var dx = e.changedTouches[0].clientX - sx;
                if (Math.abs(dx) > 40) ciGoPage(dx < 0 ? cur + 1 : cur - 1);
            }, {passive: true});
        }
    })();
    </script>
    <?php endif; ?>
</div>
<?php if ($category_pc_slide_mode == 'y'): ?>
<script>
(function(){
    var track = document.getElementById('ci-slide-track');
    if (!track) return;
    window.ciSlideScroll = function(dir) {
        track.scrollBy({ left: dir * Math.round(track.offsetWidth * 0.75), behavior: 'smooth' });
    };
    track.addEventListener('wheel', function(e) {
        if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
            e.preventDefault();
            track.scrollLeft += e.deltaY * 1.5;
        }
    }, { passive: false });
})();
</script>
<?php endif; ?>
<?php endif; ?>
<?php
 // 新版筛选栏（综合/销量↕/价格↕/库存↕ 双向切换）
 $_fk_fbase = DC_URL . '?';
 if (!empty($_GET['q']))       $_fk_fbase .= 'q=' . urlencode($_GET['q']) . '&';
 if (!empty($_GET['sort_id'])) $_fk_fbase .= 'sort_id=' . (int)$_GET['sort_id'] . '&';
 $_fk_goods_anchor = '#fk-goods-list-section';
 $_fk_has_search_keyword = isset($_GET['q']) && trim((string)$_GET['q']) !== '';
 $_fk_should_auto_scroll = !empty($_GET['sort_id']) || ($_fk_has_search_keyword && !empty($goods_list));
 $_fk_empty_text = $_fk_has_search_keyword ? '未找到相关商品，换个关键词试试' : '暂无商品';
 // 每组定义：[默认方向, 反向]
 $_fk_groups = [
     'default' => [
         'label'   => '综合',
         'ri'      => 'ri-apps-2-line',
        'orders'  => ['default'],
        'icons'   => [''],
    ],
    'sales' => [
        'label'   => '销量',
        'ri'      => 'ri-fire-line',
        'orders'  => ['sales', 'sales_asc'],
        'icons'   => ['ri-arrow-down-s-fill', 'ri-arrow-up-s-fill'],
    ],
    'price' => [
        'label'   => '价格',
        'ri'      => 'ri-price-tag-3-line',
        'orders'  => ['price_asc', 'price_desc'],
        'icons'   => ['ri-arrow-up-s-fill', 'ri-arrow-down-s-fill'],
    ],
    'stock' => [
        'label'   => '库存',
        'ri'      => 'ri-stack-line',
        'orders'  => ['stock', 'stock_asc'],
        'icons'   => ['ri-arrow-down-s-fill', 'ri-arrow-up-s-fill'],
    ],
];
?>
<div class="fk-filter-bar">
    <?php foreach ($_fk_groups as $gk => $g):
        $orderA = $g['orders'][0];
        $orderB = isset($g['orders'][1]) ? $g['orders'][1] : $orderA;
        $isActiveA = ($_gl_order === $orderA);
        $isActiveB = ($_gl_order === $orderB);
        $isActive  = $isActiveA || $isActiveB;
        
        $curIcon = '';
        if (count($g['orders']) > 1) {
            if ($isActiveA) {
                $curIcon = $g['icons'][0];
            } elseif ($isActiveB) {
                $curIcon = $g['icons'][1];
            } else {
                $curIcon = 'ri-expand-up-down-fill';
            }
        }
        
        if ($isActiveA)      $href = $_fk_fbase . 'order=' . $orderB;
        elseif ($isActiveB)  $href = $_fk_fbase . 'order=' . $orderA;
        else                 $href = $_fk_fbase . ($orderA !== 'default' ? 'order=' . $orderA : '');
        $href = rtrim($href, '&?') . $_fk_goods_anchor;
     ?>
     <a href="<?= htmlspecialchars($href) ?>" class="fk-filter-chip<?= $isActive ? ' active' : '' ?>">
         <i class="<?= $g['ri'] ?> fk-chip-ri"></i><?= $g['label'] ?><?php if ($curIcon): ?><i class="<?= $curIcon ?> fk-chip-icon"></i><?php endif; ?>
     </a>
     <?php endforeach; ?>
 </div>
 <div class="goods-list-section" id="fk-goods-list-section">
 <?php doAction('index_goodslist_top'); ?>
 <?php if (!empty($goods_list)): ?>
<div class="goods-grid goods-grid-layout-<?= $goods_list_layout ?><?= $goods_list_layout === 'list' ? ' goods-grid-list' : '' ?>">
<?php foreach ($goods_list as $key => $val): ?>
<div class="goods-grid-item layui-anim layui-anim-scaleSpring" style="animation-delay: <?= $key * 0.05 ?>s">
<?php list($_fk_type_text, $_fk_type_cls) = _fk_type_info($val['type']); ?>
<?php $_is_soldout = ((int)$val['stock'] <= 0); ?>
<a class="goods-card<?= $_is_soldout ? ' goods-card-soldout' : '' ?>" href="<?= $val['url'] ?>" title="<?= $val['title'] ?>">
<div class="goods-img-box">
<img class="goods-img lazy" src="<?= !empty($val['cover']) ? $val['cover'] : DC_URL . 'admin/views/images/cover.svg' ?>" alt="<?= $val['title'] ?>" loading="lazy" onload="this.classList.add('loaded')" onerror="this.src='<?= DC_URL ?>admin/views/images/cover.svg';this.classList.add('loaded')">
<?php if ($card_type_show == 'y'): ?><span class="fk-type-badge <?= $_fk_type_cls ?>"><?= $_fk_type_text ?></span><?php endif; ?>
<?php if ($card_soldout_show == 'y' && $_is_soldout): ?><div class="goods-soldout-mask"><span>已售空</span></div><?php endif; ?>
</div>
<div class="goods-info">
<div class="goods-title row-2-hidden"><?= htmlspecialchars($val['title']) ?></div>
<?php if ($normal_des_show == 'y'): ?><div class="goods-desc"><?php if (!empty(trim(strip_tags($val['des'])))): ?><?= htmlspecialchars(mb_substr(strip_tags($val['des']), 0, 60)) ?><?php else: ?><span style="opacity:0.3;">暂无商品描述</span><?php endif; ?></div><?php endif; ?>
<?php if ($normal_stock_show == 'y' || $normal_sales_show == 'y'): ?>
<div class="goods-meta">
    <?php if ($normal_stock_show == 'y'): ?><span class="goods-meta-stock">库存 <?= number_format((int)$val['stock']) ?></span><?php endif; ?>
    <?php if ($normal_stock_show == 'y' && $normal_sales_show == 'y'): ?><span style="color:#ddd;">·</span><?php endif; ?>
    <?php if ($normal_sales_show == 'y'): ?><span class="goods-meta-sales">已售 <?= number_format((int)$val['sales']) ?></span><?php endif; ?>
</div>
<?php endif; ?>
<?php $_unit_name = isset($val['unit_name']) ? trim((string)$val['unit_name']) : ''; ?>
<div class="goods-price-row"><div class="price-wrap"><span class="price-current"><small>&yen;</small><?= $val['price'] ?><?php if ($_unit_name !== ''): ?><small class="price-suffix"><?= htmlspecialchars($_unit_name) ?></small><?php endif; ?></span><?php if (!empty($val['market_price']) && (float)$val['market_price'] > (float)$val['price']): ?><span class="price-original">&yen;<?= $val['market_price'] ?></span><?php endif; ?></div>
<?php if ($card_buy_style === 'btn_text'): ?>
<div class="buy-btn"><?= htmlspecialchars($card_buy_text) ?></div>
<?php else: ?>
<div class="buy-icon"><?php
    if ($card_buy_style === 'icon_add') echo '<i class="ri-add-line"></i>';
    elseif ($card_buy_style === 'icon_bag') echo '<i class="ri-shopping-bag-line"></i>';
    else echo '<i class="ri-shopping-cart-2-line"></i>';
?></div>
<?php endif; ?>
</div>
</div>
</a></div>
<?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty-container"><i class="layui-icon layui-icon-face-surprised" style="font-size:60px;color:#e0e0e0;margin-bottom:20px;"></i><div style="color:#999;font-size:16px;margin-bottom:25px;"><?= htmlspecialchars($_fk_empty_text) ?></div></div>
<?php endif; ?>
<?php if (!empty($page_url)): ?>
<div class="goods-pagination"><?= $page_url ?></div>
<?php endif; ?>
</div>
<?php if ($_fk_should_auto_scroll): ?>
<script>
(function() {
    var hasScrolled = false;

    function scrollToGoodsSection() {
        if (hasScrolled) {
            return;
        }
        var target = document.getElementById('fk-goods-list-section');
        if (!target) {
            return;
        }
        hasScrolled = true;
        var top = target.getBoundingClientRect().top + window.pageYOffset - 12;
        window.scrollTo({ top: top, behavior: 'smooth' });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scrollToGoodsSection);
    } else {
        scrollToGoodsSection();
    }

    window.addEventListener('load', scrollToGoodsSection);
})();
</script>
<?php endif; ?>
</main>
<?php doAction('tpl_footer'); ?>

<?php if($normal_float_help_show == 'y'):
$_fk_help_top = (int)(_g('float_help_top') ?: 60);
?>
<style>
.float-help-btn { position: fixed; right: 15px; top: <?= $_fk_help_top ?>%; transform: translateY(-50%); z-index: 998; width: 45px; height: auto; cursor: pointer; transition: transform 0.3s ease; }
.float-help-btn:hover { transform: translateY(-50%) scale(1.1); }
.float-help-btn img { width: 100%; height: auto; }
@media (min-width: 769px) { .float-help-btn { right: calc((100vw - 1200px) / 2 - 70px); width: 50px; } }
@media (min-width: 1400px) { .float-help-btn { right: calc((100vw - 1200px) / 2 - 80px); } }
</style>
<a href="<?= DC_URL ?>?action=help" class="float-help-btn" title="买家帮助">
    <img src="<?= !empty($float_help_icon) ? $float_help_icon : TEMPLATE_URL . 'img/mjbzp.png' ?>" alt="买家帮助">
</a>
<?php endif; ?>

<?php endif; ?>
