<?php
/**
 * 商品详情页模板
 */
defined('DC_ROOT') || exit('access denied!');

$payment = getPayment();
$detail_cover_show = _g('detail_cover_show') ?: 'y';
$detail_title_show = _g('detail_title_show') ?: 'y';
$detail_sales_show = _g('detail_sales_show') ?: 'y';
$detail_stock_show = _g('detail_stock_show') ?: 'y';
$detail_price_show = _g('detail_price_show') ?: 'y';
$detail_float_help_show = _g('detail_float_help_show') ?: 'y';
$float_help_icon = _g('float_help_icon') ?: '';
$pay_type = _g('pay_type') ?: '2';

// 检测「订单卡密邮箱推送」插件是否启用
$_activePlugins = Option::get('active_plugins') ?: [];
$_emailPushEnabled = in_array('user_email/user_email.php', $_activePlugins);

// 分离邮箱字段、实物地址字段和普通字段
$isPhysicalGoods = (($goods['type'] ?? '') === 'physical');
$physicalAddressNames = ['收货人', '手机号', '所在地区', '详细地址', '买家备注'];
$physicalAddressFields = [];
$emailFields = [];
$normalFields = [];
if (!empty($goods['input'])) {
    foreach($goods['input'] as $key => $val) {
        foreach($val as $k => $v) {
            $fieldNameLower = strtolower($v['name']);
            $isEmail = strpos($fieldNameLower, '邮箱') !== false || strpos($fieldNameLower, '邮件') !== false || strpos($fieldNameLower, 'email') !== false || strpos($fieldNameLower, 'mail') !== false || (isset($v['type']) && $v['type'] === 'email');
            // 兆容旧数据：无 required 字段时，邮箱默认选填，其他默认必填
            $fieldRequired = isset($v['required']) ? (bool)$v['required'] : !$isEmail;
            if ($isPhysicalGoods && in_array((string)$v['name'], ['买家备注', '备注'], true)) {
                $fieldRequired = false;
            }
            if ($isPhysicalGoods && in_array((string)$v['name'], $physicalAddressNames, true)) {
                $physicalAddressFields[] = ['key' => $key, 'field' => $v, 'required' => $fieldRequired];
            } elseif ($isEmail) {
                $emailFields[] = ['key' => $key, 'field' => $v, 'required' => $fieldRequired];
            } else {
                $normalFields[] = ['key' => $key, 'field' => $v, 'required' => $fieldRequired];
            }
        }
    }
}
$_default_goods_price = (float)($goods['price'] ?? 0);
$_default_goods_market_price = (float)($goods['market_price'] ?? 0);
$_default_goods_unit_name = isset($goods['unit_name']) && trim((string)$goods['unit_name']) !== '' ? trim((string)$goods['unit_name']) : '/个';

// 获取商城头部自定义样式
$_shop_header_bg = _g('shop_header_bg') ?: '';
$_shop_title_color = _g('shop_title_color') ?: '';
$_shop_subtitle_color = _g('shop_subtitle_color') ?: '';
$_shop_nav_active_color = _g('shop_nav_active_color') ?: '';
$_shop_nav_active_bg = _g('shop_nav_active_bg') ?: '';
$_has_shop_header_setting = !empty($_shop_header_bg);

// 获取主题配色
$_theme_primary = _g('theme_primary') ?: '#2196F3';
$_theme_price = _g('theme_price') ?: '#ff6600';
$_theme_button = _g('theme_button') ?: '#2f69d9';
$_theme_accent = _g('theme_accent') ?: '#ff9800';
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
<style>
<?php if($_has_shop_header_setting): ?>
/* 使用后台设置的商城头部样式 */
.h-fix { background: <?= htmlspecialchars($_shop_header_bg) ?> !important; }
<?php if (!empty($_shop_title_color)): ?>
.logo-brand .brand-title { color: <?= htmlspecialchars($_shop_title_color) ?> !important; }
<?php else: ?>
.logo-brand .brand-title { color: #fff !important; }
<?php endif; ?>
<?php if (!empty($_shop_subtitle_color)): ?>
.logo-brand .brand-subtitle { color: <?= htmlspecialchars($_shop_subtitle_color) ?> !important; }
<?php else: ?>
.logo-brand .brand-subtitle { color: rgba(255,255,255,0.8) !important; }
<?php endif; ?>
.header .nav-bar li a { color: #fff !important; }
.header .nav-bar li a:hover { background: rgba(255,255,255,0.15) !important; }
<?php if (!empty($_shop_nav_active_color)): ?>
.header .nav-bar li a.active { color: <?= htmlspecialchars($_shop_nav_active_color) ?> !important; }
<?php endif; ?>
<?php if (!empty($_shop_nav_active_bg)): ?>
.header .nav-bar li a.active { background: <?= htmlspecialchars($_shop_nav_active_bg) ?> !important; border-radius: 4px; }
<?php endif; ?>
.search i.fa, .header-user i.fa, .m-btn i.fa { color: #fff !important; }
.header-search-order-btn .a { background-color: rgba(255,255,255,0.2) !important; }
.header-help-btn { border-color: rgba(255,255,255,0.5) !important; color: #fff !important; }
<?php else: ?>
/* 默认蓝色主题（后台未设置时） */
.h-fix { background: #0c6be1 !important; }
.logo-text a span { color: #fff !important; }
.logo-brand .brand-title { color: #fff !important; }
.logo-brand .brand-subtitle { color: rgba(255,255,255,0.8) !important; }
.header .nav-bar li a { color: #fff !important; }
.header-help-btn { border-color: rgba(255,255,255,0.5) !important; color: #fff !important; }
<?php endif; ?>

/* 商品详情页容器 */
.goods-detail-container {
    width: 100%;
    max-width: 600px;
    margin: 30px auto;
    padding: 0 15px;
    box-sizing: border-box;
}

/* 主卡片容器 */
.main-card {
    width: 100%;
    background: rgba(248, 248, 248, 0.5);
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    padding: 20px;
    box-sizing: border-box;
    border: 2px solid #fff;
}

/* PC端左右分栏布局 */
.goods-layout {
    display: block;
}
.goods-left {
    width: 100%;
}
.goods-right {
    width: 100%;
}

/* 商品图（主图 + 缩略图） */
.goods-cover-section { margin-bottom: 20px; }
.goods-cover-main { position: relative; width: 100%; aspect-ratio: 1; background: #f0f0f0; border-radius: 12px; overflow: hidden; cursor: zoom-in; -webkit-mask-image: -webkit-radial-gradient(white, black); }
.goods-cover-main::before { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg, #f0f0f0 25%, #e8e8e8 37%, #f0f0f0 63%); background-size: 400% 100%; animation: goodsSkeleton 1.4s ease infinite; z-index: 0; }
.goods-cover-img { width: 100%; height: 100%; object-fit: cover; display: block; transition: opacity .4s ease; opacity: 0; position: relative; z-index: 1; }
.goods-cover-img.loaded { opacity: 1; }
/* PC 主图左右切换箭头 */
.cover-arrow { position: absolute; top: 50%; transform: translateY(-50%); width: 36px; height: 36px; border-radius: 50%; background: rgba(0,0,0,.25); color: #fff; font-size: 18px; line-height: 36px; text-align: center; cursor: pointer; user-select: none; transition: background .2s, opacity .2s; opacity: 0; z-index: 2; }
.goods-cover-main:hover .cover-arrow { opacity: 1; }
.cover-arrow:hover { background: rgba(0,0,0,.5); }
.cover-arrow-prev { left: 10px; }
.cover-arrow-next { right: 10px; }
.cover-arrows-hidden .cover-arrow { display: none; }
/* PC 缩略图 */
.goods-thumbs-wrap { position: relative; margin-top: 10px; }
.goods-thumbs { display: flex; gap: 8px; overflow: hidden; padding: 2px 0; }
.goods-thumb { flex-shrink: 0; width: 60px; height: 60px; border-radius: 8px; overflow: hidden; cursor: pointer; border: 2px solid transparent; transition: border-color .2s, transform .15s; background: #f0f0f0; -webkit-mask-image: -webkit-radial-gradient(white, black); position: relative; }
.goods-thumb::before { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg, #f0f0f0 25%, #e8e8e8 37%, #f0f0f0 63%); background-size: 400% 100%; animation: goodsSkeleton 1.4s ease infinite; z-index: 0; }
.goods-thumb:hover, .goods-thumb.active { border-color: var(--theme-primary, #2196F3); }
.goods-thumb.active { transform: translateY(-1px); }
.goods-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; pointer-events: none; opacity: 0; transition: opacity .35s ease; position: relative; z-index: 1; }
.goods-thumb img.loaded { opacity: 1; }
.thumb-arrow { position: absolute; top: 50%; transform: translateY(-50%); width: 24px; height: 24px; border-radius: 50%; background: rgba(0,0,0,.35); color: #fff; font-size: 13px; line-height: 24px; text-align: center; cursor: pointer; z-index: 2; transition: background .2s; user-select: none; }
.thumb-arrow:hover { background: rgba(0,0,0,.6); }
.thumb-arrow-prev { left: -4px; }
.thumb-arrow-next { right: -4px; }
/* 移动端：滑动画廊 + 计数器 */
.goods-swiper { position: relative; width: 100%; aspect-ratio: 1; overflow: hidden; border-radius: 12px; background: #f0f0f0; display: none; touch-action: pan-y; -webkit-mask-image: -webkit-radial-gradient(white, black); }
.goods-swiper-track { display: flex; height: 100%; transition: transform .3s ease; will-change: transform; }
.goods-swiper-track.no-transition { transition: none; }
.goods-swiper-slide { flex-shrink: 0; width: 100%; height: 100%; position: relative; background: #f0f0f0; }
.goods-swiper-slide::before { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg, #f0f0f0 25%, #e8e8e8 37%, #f0f0f0 63%); background-size: 400% 100%; animation: goodsSkeleton 1.4s ease infinite; z-index: 0; }
.goods-swiper-slide img { width: 100%; height: 100%; object-fit: cover; display: block; user-select: none; -webkit-user-drag: none; opacity: 0; transition: opacity .4s ease; position: relative; z-index: 1; }
.goods-swiper-slide img.loaded { opacity: 1; }
.goods-swiper-counter { position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,.45); color: #fff; font-size: 12px; padding: 2px 10px; border-radius: 10px; z-index: 2; pointer-events: none; letter-spacing: .5px; }
.goods-thumbs-empty { display: none; }
@keyframes goodsSkeleton { 0% { background-position: 100% 50%; } 100% { background-position: 0 50%; } }

/* 灯箱 */
.gimg-lightbox { position: fixed; inset: 0; z-index: 99999; background: rgba(0,0,0,.88); display: none; align-items: center; justify-content: center; }
.gimg-lightbox.show { display: flex; }
.gimg-lightbox img { max-width: 92vw; max-height: 88vh; border-radius: 6px; box-shadow: 0 10px 50px rgba(0,0,0,.5); user-select: none; }
.gimg-lightbox .lb-close { position: absolute; top: 18px; right: 22px; color: #fff; font-size: 32px; cursor: pointer; line-height: 1; opacity: .85; transition: opacity .15s; }
.gimg-lightbox .lb-close:hover { opacity: 1; }
.gimg-lightbox .lb-arrow { position: absolute; top: 50%; transform: translateY(-50%); width: 48px; height: 48px; border-radius: 50%; background: rgba(255,255,255,.15); color: #fff; font-size: 26px; line-height: 48px; text-align: center; cursor: pointer; user-select: none; transition: background .15s; }
.gimg-lightbox .lb-arrow:hover { background: rgba(255,255,255,.28); }
.gimg-lightbox .lb-prev { left: 24px; }
.gimg-lightbox .lb-next { right: 24px; }
.gimg-lightbox .lb-counter { position: absolute; bottom: 24px; left: 50%; transform: translateX(-50%); color: #fff; font-size: 13px; background: rgba(0,0,0,.4); padding: 4px 14px; border-radius: 14px; }
@media (max-width: 768px) {
    .gimg-lightbox .lb-arrow { width: 40px; height: 40px; line-height: 40px; font-size: 22px; }
    .gimg-lightbox .lb-prev { left: 8px; }
    .gimg-lightbox .lb-next { right: 8px; }
    /* 移动端：隐藏 PC 主图+缩略图，显示滑动画廊 */
    .goods-cover-main { display: none; }
    .goods-thumbs-wrap { display: none; }
    .goods-swiper { display: block; }
}

/* 商品标题 */
.goods-title-section { margin-bottom: 15px; }
.goods-title { font-size: 20px; font-weight: 600; color: #333; line-height: 1.4; margin: 0; }
.goods-meta { font-size: 13px; color: #999; margin-top: 8px; }
.goods-meta span { margin-right: 15px; }

/* 规格选择区域 */
.spec-section { margin-bottom: 20px; }
.spec-group { margin-bottom: 15px; }
.spec-group:last-child { margin-bottom: 0; }
.spec-group-title { font-size: 16px; font-weight: 600; color: #333; margin-bottom: 10px; }
.spec-options { display: flex; flex-wrap: wrap; gap: 10px; }
.spec-option {
    padding: 8px 15px;
    border: 1px solid transparent;
    background: #f8f9fa;
    border-radius: 8px;
    font-size: 13px;
    color: #333;
    cursor: pointer;
    transition: all 0.2s;
    overflow: hidden;
}
.spec-option:hover { background: rgba(var(--theme-primary-rgb, 33, 150, 243), 0.05); border-color: rgba(var(--theme-primary-rgb, 33, 150, 243), 0.3); }
.spec-option.active { background: rgba(var(--theme-primary-rgb, 33, 150, 243), 0.1); border-color: var(--theme-primary, #2196F3); color: var(--theme-primary-dark, #1976D2); font-weight: 500; position: relative; }
.spec-option.active::after { content: ""; position: absolute; bottom: 0; right: 0; width: 0; height: 0; border-style: solid; border-width: 0 0 12px 12px; border-color: transparent transparent var(--theme-primary, #2196F3) transparent; }
.spec-option.disabled { opacity: 0.5; cursor: not-allowed; background: #eee; text-decoration: line-through; }

/* 购买表单区域 */
.buy-form-section { display: none; }
.buy-form-section.show { display: block; }

/* 小标题 */
.section-label { font-size: 16px; color: #333; font-weight: 600; margin-bottom: 12px; }
.required-star { color: var(--theme-price, #ff6600); margin-right: 4px; }

/* 商品描述框 */
.goods-description-box { background: #fff; border-radius: 8px; padding: 15px; margin-bottom: 20px; width: 100%; box-sizing: border-box; border: 1px solid #eee; }
.goods-description-box .intro { font-size: 13px; line-height: 1.8; color: #555; padding: 0; margin: 0; }
.goods-description-box .intro img { max-width: 100%; border-radius: 8px; margin: 10px 0; }

/* 优惠券区域样式 */
.coupon-toggle-row { background: #fff; border-radius: 8px; padding: 10px 15px 10px 15px;border: 1px solid #eee; }
.coupon-toggle-header { display: flex; align-items: center; }
.coupon-checkbox-label { display: flex; align-items: center; cursor: pointer; user-select: none; }
.coupon-checkbox { position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none; }
.coupon-checkbox-box { width: 18px; height: 18px; border: 2px solid #ddd; border-radius: 4px; margin-right: 10px; display: flex; align-items: center; justify-content: center; transition: all 0.2s; background: #fff; flex-shrink: 0; }
.coupon-checkbox:checked + .coupon-checkbox-box { background: var(--theme-accent, #ff9800); border-color: var(--theme-accent, #ff9800); }
.coupon-checkbox:checked + .coupon-checkbox-box::after { content: "✓"; color: #fff; font-size: 12px; font-weight: bold; }
.coupon-toggle-text { font-size: 14px; color: #666; font-weight: 500; }
.coupon-input-area { margin-top: 15px; border-top: 1px dashed #eee; padding-top: 15px; }
.coupon-input-row { display: flex; gap: 10px; }
.coupon-input { flex: 1; height: 40px; border: 1px solid #ddd; border-radius: 6px; padding: 0 12px; font-size: 14px; background: #fff; outline: none; }
.coupon-input:focus { border-color: var(--theme-accent, #ff9800); }
.coupon-check-btn { height: 40px; padding: 0 20px; background: var(--theme-accent, #ff9800); color: #fff; border: none; border-radius: 6px; font-size: 14px; cursor: pointer; white-space: nowrap; }
.coupon-check-btn:hover { background: var(--theme-accent-dark, #f57c00); }
.coupon-result { margin-top: 10px; font-size: 13px; }
.coupon-result.success { color: #4caf50; }
.coupon-result.error { color: #f44336; }
.coupon-result .coupon-discount { color: #ff5722; font-weight: bold; font-size: 15px; }
.coupon-checkbox-label .layui-form-checkbox { display: none !important; }

/* 价格和数量同行布局 */
.price-quantity-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
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
.input-field-row { margin-bottom: 18px; }
.input-field-header { display: flex; align-items: center; gap: 12px; margin-bottom: 6px; }
.input-field-header .section-label { white-space: nowrap; flex-shrink: 0; margin-bottom: 0; font-size: 16px; }
.input-field-input { flex: 1; height: 40px; border: none; border-bottom: 1px solid #eee; outline: none; font-size: 15px; color: #f95926 !important; background: #fff; padding: 0 8px; }
.input-field-input:focus { border-bottom-color: var(--theme-primary, #2196F3); }
.input-field-input::placeholder { color: #bbb; }
.input-field-note { font-size: 12px; color: var(--theme-price, #ff6600); padding-left: 14px; margin-top: 4px; }

/* 邮箱字段开关样式 */
.email-field-row { margin-bottom: 0px; }
.email-field-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
.email-field-title { font-size: 16px; font-weight: 600; color: #333; }
.email-field-desc { font-size: 13px; color: #333; margin-top: 8px; }
.email-input-wrap { margin-top: 12px; }
.email-input-wrap .email-input { width: 100%; height: 42px; border: 1px solid #ddd; border-radius: 6px; padding: 0 12px; font-size: 14px; background: #fff; box-sizing: border-box; }
.email-input-wrap .email-input:focus { border-color: var(--theme-primary, #2196F3); outline: none; }
.email-input-wrap .email-input::placeholder { color: #999; }
.email-switch-label { position: relative; display: inline-block; width: 50px; height: 28px; cursor: pointer; }
.email-switch-input { opacity: 0; width: 0; height: 0; position: absolute; }
.email-switch-slider { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #ddd; border-radius: 28px; transition: 0.3s; }
.email-switch-slider:before { content: ""; position: absolute; height: 24px; width: 24px; left: 2px; bottom: 2px; background-color: white; border-radius: 50%; transition: 0.3s; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
.email-switch-input:checked + .email-switch-slider { background-color: var(--theme-primary, #2196F3); }
.email-switch-input:checked + .email-switch-slider:before { transform: translateX(22px); }

/* 支付方式 */
.payment-methods { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
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

.kami-page-header {
    background: rgba(255, 255, 255, 0.72);
    -webkit-backdrop-filter: saturate(180%) blur(20px);
    backdrop-filter: saturate(180%) blur(20px);
    border: 2px solid #fff;
    border-radius: 8px;
    padding: 15px 20px;
    margin-top: 20px;
    margin-bottom: 15px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.05);
    display: none;
    align-items: center;
    position: relative;
}
.kami-back-btn {
    display: flex;
    align-items: center;
    gap: 5px;
    color: var(--theme-primary, #0c6be1);
    font-size: 14px;
    cursor: pointer;
    padding: 8px 0;
    text-decoration: none;
    position: relative;
    z-index: 1;
}
.kami-back-btn:hover {
    color: var(--theme-primary-dark, #0a5bc4);
}
.kami-page-title {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    font-size: 17px;
    font-weight: bold;
    color: #333;
    white-space: nowrap;
}
.kami-share-btn {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 5px;
    color: #666;
    font-size: 12px;
    cursor: pointer;
    padding: 8px 0;
    text-decoration: none;
    position: relative;
    z-index: 1;
}
.kami-share-btn i {
    font-size: 14px;
}
.kami-share-btn:hover {
    color: var(--theme-primary, #0c6be1);
}

/* 优惠券倒计时条样式 - 悬浮在确认付款按钮上方 */
/* PC端默认样式 */
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

/* 批发优惠弹窗样式 */
.discount-layer { background: #fff; border-radius: 12px; overflow: hidden; }
.discount-layer .layui-layer-content { background: #fff; }
.discount-layer .layui-layer-btn { background: #fff; border-top: 1px solid #eee; padding: 15px; }
.discount-layer .layui-layer-btn a { background: var(--theme-primary, #0c6be1); color: #fff; border-radius: 8px; height: 40px; line-height: 40px; }

/* 隐藏底部信息 */
.main-footer { display: none; background: transparent !important; border-top: none !important; margin-top: 0 !important; }
.footer-nav { display: none; }

/* 右侧悬浮按钮 */
.float-help-btn { position: fixed; right: 15px; top: 50%; transform: translateY(-50%); z-index: 998; width: 45px; height: auto; cursor: pointer; transition: transform 0.3s ease; }
.float-help-btn:hover { transform: translateY(-50%) scale(1.1); }
.float-help-btn img { width: 100%; height: auto; }

/* PC端适配 */
@media (min-width: 769px) {
    .goods-detail-container { max-width: 1140px; margin: 30px auto; padding: 0 15px; }
    .main-card { padding: 40px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    
    /* PC端左右分栏布局 */
    .goods-layout { display: flex; gap: 40px; }
    .goods-left { width: 400px; flex-shrink: 0; position: sticky; top: 100px; align-self: flex-start; }
    .goods-right { flex: 1; min-width: 0; }
    .goods-cover-section { margin-bottom: 0; }
    
    .pc-share-btn {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 20px;
        padding: 10px 0;
        background: #f8f9fa;
        border-radius: 8px;
        color: #666;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        border: 1px solid #eee;
    }
    .pc-share-btn:hover {
        background: rgba(var(--theme-primary-rgb, 33, 150, 243), 0.05);
        border-color: rgba(var(--theme-primary-rgb, 33, 150, 243), 0.3);
        color: var(--theme-primary, #0c6be1);
    }
    
    .goods-title { font-size: 24px; }
    .spec-options { gap: 12px; }
    .spec-option { padding: 8px 20px; font-size: 14px; }
    .goods-description-box { padding: 20px; }
    .price-quantity-row { padding: 15px 0; margin-bottom: 25px; }
    .section-label { font-size: 16.5px; font-weight: bold; }
    .section-value { font-size: 23px; padding-left: 12px; }
    .section-value .unit { font-size: 14px; }
    .quantity-selector { border-radius: 8px; }
    .quantity-btn { width: 38px; height: 38px; font-size: 14px; }
    .quantity-input { width: 50px; height: 38px; font-size: 15px; }
    .stock-row { font-size: 14px; margin-bottom: 25px; }
    .input-field-row { margin-bottom: 20px; }
    .input-field-header { gap: 15px; margin-bottom: 8px; }
    .input-field-input { height: 40px; font-size: 14px; padding: 0 10px; }
    .input-field-note { font-size: 13px; padding-left: 14px; margin-top: 5px; }
    .payment-methods { gap: 15px; }
    .payment-item { padding: 15px; border-radius: 10px; }
    .payment-icon { width: 36px; height: 36px; margin-right: 12px; }
    .payment-icon img { width: 24px; height: 24px; }
    .payment-name { font-size: 14px; font-weight: 600; }
    .drawer-footer { margin-top: 30px; }
    .pay-bar { padding: 12px 15px; border-radius: 8px; }
    .pay-amount { font-size: 15px; }
    .pay-amount .dynamic-price { font-size: 20px; }
    .pay-btn { padding: 12px 35px; font-size: 16px; border-radius: 6px; }
    .float-help-btn { right: calc((100vw - 1140px) / 2 - 70px); width: 50px; }
    /* PC端倒计时条位置 - 确保与单页购买模式一致 */
    #coupon-countdown-bar { top: -25px; right: 15px; }
    #coupon-countdown { font-size: 14px; padding: 8px 25px; }
}
@media (min-width: 1400px) {
    .float-help-btn { right: calc((100vw - 1140px) / 2 - 80px); }
}

/* 移动端适配 */
@media (max-width: 768px) {
    .header { display: none !important; }
    .goods-detail-container { margin: 15px auto; }
    .payment-methods { grid-template-columns: <?= $pay_type == '2' ? 'repeat(2, 1fr)' : '1fr' ?>; }
    .price-quantity-row { gap: 20px; }
    .pc-share-btn { display: none; }
    .goods-swiper { border-radius: 8px; }
    .goods-cover-section { margin-bottom: 12px; }
    .kami-page-header { display: flex; margin-top: 0px; margin-bottom: 15px; padding: 12px 15px; position: relative; z-index: 1001; box-sizing: border-box; }
    .kami-page-header-placeholder { display: none; height: 0; }
    .kami-page-header-placeholder.is-active { display: block; }
    .kami-page-header.is-stuck { position: fixed; top: 0; left: 0; right: 0; margin-top: 0; border-radius: 0; }
    .goods-title-section { margin-top: 0; }
    .drawer-footer { position: fixed; bottom: 0; left: 0; right: 0; background: rgba(255, 255, 255, 0.72); -webkit-backdrop-filter: saturate(180%) blur(20px); backdrop-filter: saturate(180%) blur(20px); border-top: 0.5px solid rgba(0, 0, 0, 0.1); padding: 10px 15px; margin-top: 0; z-index: 999; box-shadow: 0 -4px 10px rgba(0,0,0,0.05); padding-bottom: max(10px, env(safe-area-inset-bottom)); border-radius: 0; }
    .pay-bar { padding: 8px 12px; }
    .pay-btn { padding: 10px 25px; font-size: 13px; }
    .buy-form-section.show { padding-bottom: 0px; }
    .main-footer { padding-bottom: 100px !important; }
}

/* 深色模式适配 */
html[data-theme="dark"] .main-card, html.dark-mode .main-card { background: #1e1e1e; }
html[data-theme="dark"] .goods-title, html.dark-mode .goods-title { color: #e0e0e0; }
html[data-theme="dark"] .goods-meta, html.dark-mode .goods-meta { color: #888; }
html[data-theme="dark"] .spec-group-title, html[data-theme="dark"] .section-label, html.dark-mode .spec-group-title, html.dark-mode .section-label { color: #e0e0e0; }
html[data-theme="dark"] .spec-option, html.dark-mode .spec-option { background: #2a2a2a; color: #e0e0e0; border-color: #333; }
html[data-theme="dark"] .spec-option:hover, html.dark-mode .spec-option:hover { background: #333; border-color: #555; }
html[data-theme="dark"] .spec-option.active, html.dark-mode .spec-option.active { background: rgba(var(--theme-primary-rgb), 0.2); border-color: var(--theme-primary); color: var(--theme-primary-light); }
html[data-theme="dark"] .goods-description-box, html.dark-mode .goods-description-box { background: #252525; }
html[data-theme="dark"] .goods-description-box .intro, html.dark-mode .goods-description-box .intro { color: #b0b0b0; }
html[data-theme="dark"] .coupon-toggle-row, html.dark-mode .coupon-toggle-row { background: #252525; }
html[data-theme="dark"] .coupon-checkbox-box, html.dark-mode .coupon-checkbox-box { background: #2a2a2a; border-color: #444; }
html[data-theme="dark"] .coupon-toggle-text, html.dark-mode .coupon-toggle-text { color: #b0b0b0; }
html[data-theme="dark"] .coupon-input-area, html.dark-mode .coupon-input-area { border-top-color: #444; }
html[data-theme="dark"] .coupon-input, html.dark-mode .coupon-input { background: #2a2a2a; border-color: #444; color: #e0e0e0; }
html[data-theme="dark"] .input-field-input, html.dark-mode .input-field-input { background: transparent; color: #e0e0e0 !important; border-bottom-color: #444; }
html[data-theme="dark"] .input-field-input::placeholder, html.dark-mode .input-field-input::placeholder { color: #666; }
html[data-theme="dark"] .email-field-row, html.dark-mode .email-field-row { background: #252525 !important; }
html[data-theme="dark"] .email-field-title, html[data-theme="dark"] .email-field-desc, html.dark-mode .email-field-title, html.dark-mode .email-field-desc { color: #b0b0b0; }
html[data-theme="dark"] .email-input, html.dark-mode .email-input { background: #2a2a2a !important; border-color: #444 !important; color: #e0e0e0 !important; }
html[data-theme="dark"] .payment-item, html.dark-mode .payment-item { background: #252525; border-color: #333; }
html[data-theme="dark"] .payment-item:hover, html.dark-mode .payment-item:hover { background: #2a2a2a; border-color: #555; }
html[data-theme="dark"] .payment-item.active, html.dark-mode .payment-item.active { background: rgba(var(--theme-primary-rgb), 0.2); border-color: var(--theme-primary); }
html[data-theme="dark"] .payment-name, html.dark-mode .payment-name { color: #e0e0e0; }
html[data-theme="dark"] .payment-icon, html.dark-mode .payment-icon { background: #333; }
html[data-theme="dark"] .pay-bar, html.dark-mode .pay-bar { background: #252525; }
html[data-theme="dark"] .pay-amount, html.dark-mode .pay-amount { color: #e0e0e0; }
html[data-theme="dark"] .quantity-selector, html.dark-mode .quantity-selector { border-color: #444; }
html[data-theme="dark"] .quantity-btn, html.dark-mode .quantity-btn { background: #2a2a2a; color: #e0e0e0; }
html[data-theme="dark"] .quantity-btn:hover, html.dark-mode .quantity-btn:hover { background: #333; }
html[data-theme="dark"] .quantity-input, html.dark-mode .quantity-input { background: #252525; color: #e0e0e0; border-color: #444; }
html[data-theme="dark"] .drawer-footer, html.dark-mode .drawer-footer { background: #1e1e1e; }
/* 公告栏样式 */
.roll-notice-bar { background: linear-gradient(135deg, var(--theme-primary, #2196F3), var(--theme-secondary, #1976D2)); border-radius: 8px; padding: 10px 15px; display: flex; align-items: center; margin-bottom: 15px; overflow: hidden; }
.roll-notice-bar i { color: #fff; font-size: 18px; margin-right: 10px; flex-shrink: 0; }
.roll-notice-content { flex: 1; overflow: hidden; white-space: nowrap; }
.roll-notice-content span { display: inline-block; color: #fff; font-size: 13px; animation: roll-scroll 15s linear infinite; }
@keyframes roll-scroll { 0% { transform: translateX(100%); } 100% { transform: translateX(-100%); } }
html[data-theme="dark"] .roll-notice-bar { background: linear-gradient(135deg, #1565C0, #0D47A1); }
.notice-bar { background: #fff; border-radius: 8px; padding: 12px 15px; display: flex; align-items: center; box-shadow: 0 1px 6px rgba(0,0,0,0.05); margin-bottom: 15px; border-left: 4px solid var(--theme-primary, #2196F3); }
.notice-bar i { color: var(--theme-primary, #2196F3); font-size: 18px; margin-right: 12px; flex-shrink: 0; }
.notice-content { color: #666; font-size: 13px; flex: 1; line-height: 1.6; }
html[data-theme="dark"] .notice-bar { background: #1e1e1e; box-shadow: 0 1px 6px rgba(0,0,0,0.2); }
html[data-theme="dark"] .notice-content { color: #b0b0b0; }

/* 底部独立的"商品详情"通栏板块 */
.goods-detail-section {
    width: 100%;
    max-width: 600px;
    margin: 20px auto 0;
    padding: 20px;
    box-sizing: border-box;
    background: rgba(248, 248, 248, 0.5);
    border: 2px solid #fff;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}
.goods-detail-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding-bottom: 14px;
    margin-bottom: 16px;
    border-bottom: 1px dashed #e8e8e8;
}
.goods-detail-bar {
    display: inline-block;
    width: 4px;
    height: 18px;
    background: var(--theme-primary, #2196F3);
    border-radius: 2px;
}
.goods-detail-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
}
.goods-detail-body .intro {
    font-size: 14px;
    line-height: 1.85;
    color: #555;
    word-break: break-word;
}
.goods-detail-body .intro img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 10px 0;
    display: block;
}
.goods-detail-body .intro p { margin: 0 0 10px; }
.goods-detail-body .intro:empty::before,
.goods-detail-body .intro:not(:has(*)):empty::before { content: '暂无说明'; color: #aaa; }

@media (min-width: 769px) {
    .goods-detail-section {
        max-width: 1140px;
        padding: 30px 40px;
        margin-top: 24px;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    .goods-detail-header { padding-bottom: 18px; margin-bottom: 22px; }
    .goods-detail-bar { width: 4px; height: 20px; }
    .goods-detail-title { font-size: 18px; }
    .goods-detail-body .intro { font-size: 15px; line-height: 1.9; }
}

@media (max-width: 768px) {
    .goods-detail-section {
        margin: 15px auto 30px;
        padding: 16px;
    }
    .goods-detail-title { font-size: 15px; }
    .goods-detail-body .intro { font-size: 13.5px; line-height: 1.8; }
}

html[data-theme="dark"] .goods-detail-section,
html.dark-mode .goods-detail-section {
    background: #1e1e1e;
    border-color: #2a2a2a;
    box-shadow: 0 4px 20px rgba(0,0,0,0.4);
}
html[data-theme="dark"] .goods-detail-header,
html.dark-mode .goods-detail-header { border-bottom-color: #333; }
html[data-theme="dark"] .goods-detail-title,
html.dark-mode .goods-detail-title { color: #e0e0e0; }
html[data-theme="dark"] .goods-detail-body .intro,
html.dark-mode .goods-detail-body .intro { color: #b0b0b0; }
</style>

<main class="goods-detail-container">
<div class="kami-page-header">
    <a href="javascript:history.back()" class="kami-back-btn">
        <i class="fa fa-chevron-left"></i>
    </a>
    <div class="kami-page-title">商品详情</div>
    <a href="javascript:;" class="kami-share-btn" id="goodsShareBtn" title="分享商品">
        <i class="fa fa-share-square-o"></i> 分享
    </a>
</div>
<div class="kami-page-header-placeholder"></div>
<div class="main-card">
<div class="goods-layout">
    <!-- 左侧：商品图片 -->
    <div class="goods-left">
        <?php
            // 商品图集：API 已返回 $goods['gallery'] 数组（cover 一定在首位）
            $_imgs = [];
            if (!empty($goods['gallery']) && is_array($goods['gallery'])) {
                $_imgs = $goods['gallery'];
            } elseif (!empty($goods['cover'])) {
                $_imgs = [$goods['cover']];
            }
        ?>
        <?php if($detail_cover_show == 'y' && !empty($_imgs)): ?>
        <div class="goods-cover-section" id="goodsGallerySection">
            <!-- PC 主图 -->
            <div class="goods-cover-main <?= count($_imgs) <= 1 ? 'cover-arrows-hidden' : '' ?>" id="goodsCoverMain">
                <img class="goods-cover-img" id="goodsCoverImg" src="<?= htmlspecialchars($_imgs[0]) ?>" alt="<?= htmlspecialchars($goods['title']) ?>" onload="this.classList.add('loaded')" onerror="this.src='<?= DC_URL ?>admin/views/images/cover.svg';this.classList.add('loaded')">
                <span class="cover-arrow cover-arrow-prev" id="coverPrev">&#10094;</span>
                <span class="cover-arrow cover-arrow-next" id="coverNext">&#10095;</span>
            </div>
            <!-- PC 缩略图 -->
            <?php if(count($_imgs) > 1): ?>
            <div class="goods-thumbs-wrap" id="goodsThumbsWrap">
                <div class="goods-thumbs" id="goodsThumbs">
                    <?php foreach($_imgs as $_i => $_u): ?>
                    <div class="goods-thumb <?= $_i === 0 ? 'active' : '' ?>" data-url="<?= htmlspecialchars($_u) ?>" data-idx="<?= $_i ?>">
                        <img src="<?= htmlspecialchars($_u) ?>" alt="" loading="lazy" onload="this.classList.add('loaded')">
                    </div>
                    <?php endforeach; ?>
                </div>
                <span class="thumb-arrow thumb-arrow-prev" id="thumbPrev">&#10094;</span>
                <span class="thumb-arrow thumb-arrow-next" id="thumbNext">&#10095;</span>
            </div>
            <?php endif; ?>
            <!-- 移动端滑动画廊 -->
            <?php if(count($_imgs) > 0): ?>
            <div class="goods-swiper" id="goodsSwiper">
                <div class="goods-swiper-track" id="goodsSwiperTrack">
                    <?php foreach($_imgs as $_u): ?>
                    <div class="goods-swiper-slide"><img src="<?= htmlspecialchars($_u) ?>" alt="<?= htmlspecialchars($goods['title']) ?>" loading="lazy" onload="this.classList.add('loaded')"></div>
                    <?php endforeach; ?>
                </div>
                <span class="goods-swiper-counter" id="goodsSwiperCounter">1/<?= count($_imgs) ?></span>
            </div>
            <?php endif; ?>
        </div>
        <script>window.__GOODS_GALLERY__ = <?= json_encode($_imgs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;</script>
        <?php endif; ?>
        
        <a href="javascript:;" class="pc-share-btn goodsShareAction" title="分享商品">
            <i class="fa fa-share-square-o" style="font-size: 16px;"></i> 分享商品
        </a>
    </div>
    
    <!-- 右侧：商品信息和购买表单 -->
    <div class="goods-right">
    <?php if($detail_title_show == 'y'): ?>
    <div class="goods-title-section">
        <h1 class="goods-title"><?= $goods['title'] ?></h1>
        <div class="goods-meta">
            <?php if($detail_sales_show == 'y'): ?><span>已售：<?= $goods['sales'] ?></span><?php endif; ?>
            <?php if($detail_stock_show == 'y'): ?><span>库存：<span id="goodsStock"><?= $goods['stock'] ?></span></span><?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if(!empty($goods['spec'])): ?>
    <div class="spec-section">
        <?php foreach($goods['spec'] as $idx => $spec): ?>
        <div class="spec-group">
            <div class="spec-group-title"><?= $spec['title'] ?></div>
            <div class="spec-options">
                <?php foreach($spec['sku_values'] as $sku): ?>
                <div class="spec-option" data-id="<?= $sku['id'] ?>" data-group="<?= $idx ?>"><?= $sku['name'] ?></div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form class="buy-form-section layui-form <?= empty($goods['spec']) ? 'show' : '' ?>" id="buyFormSection" data-dc-physical-goods="<?= $isPhysicalGoods ? '1' : '0' ?>">
        <!-- 优惠券区域 -->
        <div class="coupon-toggle-row" id="coupon-section" style="display:none;margin-bottom: 20px;">
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
            <?php if($detail_price_show == 'y'): ?>
            <div class="price-section">
                <div class="section-label"><span class="required-star">*</span> 商品单价</div>
                <div class="section-value"><span class="unit-price" id="goodsPrice"><?= number_format($_default_goods_price, 2) ?></span> <span class="unit"><?= htmlspecialchars($_default_goods_unit_name) ?></span><?php if($_default_goods_market_price > $_default_goods_price): ?><span class="market-price" id="goodsMarketPrice">¥<?= number_format($_default_goods_market_price, 2) ?></span><?php else: ?><span class="market-price" id="goodsMarketPrice" style="display:none;"></span><?php endif; ?><span id="discountTag" style="display:none;font-size:15px;font-weight:normal;margin-left:5px;cursor:pointer;">（<?= htmlspecialchars(!empty($goods['discount_title']) ? $goods['discount_title'] : '批发优惠') ?>）</span></div>
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

        <?php foreach($physicalAddressFields as $item): ?>
        <div class="input-field-row dc-physical-address-seed" style="display:none;">
            <input type="text" name="<?= $item['key'] ?>[<?= $item['field']['name'] ?>]" placeholder="<?= $item['field']['placeholder'] ?? '' ?>" class="input-field-input <?= $item['required'] ? 'required-input' : '' ?>" data-validate-type="<?= $item['field']['type'] ?? 'string' ?>">
        </div>
        <?php endforeach; ?>

        <?php foreach($normalFields as $item): ?>
        <?php
            $_ftype = $item['field']['type'] ?? 'string';
            $_htmlType = 'text';
            if ($_ftype === 'email') $_htmlType = 'email';
            elseif ($_ftype === 'tel') $_htmlType = 'tel';
            elseif ($_ftype === 'num') $_htmlType = 'number';
        ?>
        <div class="input-field-row">
            <div class="input-field-header">
                <div class="section-label"><?php if($item['required']): ?><span class="required-star">*</span><?php endif; ?> <?= $item['field']['name'] ?></div>
                <input type="<?= $_htmlType ?>" name="<?= $item['key'] ?>[<?= $item['field']['name'] ?>]" placeholder="<?= $item['field']['placeholder'] ?>" class="input-field-input <?= $item['required'] ? 'required-input' : '' ?>" data-validate-type="<?= $_ftype ?>">
            </div>
            <div class="input-field-note"><?= !empty($item['field']['tip']) ? htmlspecialchars($item['field']['tip']) : ($item['required'] ? '联系信息是查询订单的重要凭证（必填）' : '选填项，可留空') ?></div>
        </div>
        <?php endforeach; ?>

        <?php if(!$_emailPushEnabled): ?>
        <?php foreach($emailFields as $item): ?>
        <div class="input-field-row">
            <div class="input-field-header">
                <div class="section-label"><?php if($item['required']): ?><span class="required-star">*</span><?php endif; ?> <?= $item['field']['name'] ?></div>
                <input type="email" name="<?= $item['key'] ?>[<?= $item['field']['name'] ?>]" placeholder="<?= $item['field']['placeholder'] ?: '请输入邮箱地址' ?>" class="input-field-input <?= $item['required'] ? 'required-input' : '' ?>" data-validate-type="email">
            </div>
            <div class="input-field-note"><?= !empty($item['field']['tip']) ? htmlspecialchars($item['field']['tip']) : ($item['required'] ? '邮箱地址为必填项' : '选填项，可留空') ?></div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
        
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

        <?php if($_emailPushEnabled): ?>
        <?php foreach($emailFields as $item): ?>
        <?php if($item['required']): ?>
        <div class="input-field-row email-field-row" style="background:#fff;padding:15px;border-radius:8px;margin-top:20px;border: 1px solid #eee;">
            <div class="email-field-header">
                <div class="email-field-title">邮件服务</div>
                <span style="font-size:12px;padding:2px 10px;background:#ffebee;color:#c62828;border-radius:3px;">必填</span>
            </div>
            <div class="email-input-wrap" style="display:block;">
                <input type="text" name="<?= $item['key'] ?>[<?= $item['field']['name'] ?>]" placeholder="请输入邮箱地址" class="email-input required-input">
            </div>
            <div class="email-field-desc">支付成功后自动推送卡密到邮箱</div>
        </div>
        <?php else: ?>
        <div class="input-field-row email-field-row" style="background:#fff;padding:15px;border-radius:8px;margin-top:20px;border: 1px solid #eee;">
            <div class="email-field-header">
                <div class="email-field-title">邮件服务</div>
                <label class="email-switch-label"><input type="checkbox" class="email-switch-input" lay-ignore><span class="email-switch-slider"></span></label>
            </div>
            <div class="email-input-wrap" style="display:none;">
                <input type="text" data-field-name="<?= $item['key'] ?>[<?= $item['field']['name'] ?>]" placeholder="请输入邮箱地址，无需发送可不填" class="email-input">
            </div>
            <div class="email-field-desc">支付成功后自动推送卡密到邮箱（选填）</div>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="drawer-footer">
            <div class="pay-bar">
                <div class="pay-amount">应付：<span class="dynamic-price" id="totalPrice"><?= number_format($_default_goods_price, 2) ?> 元</span></div>
                <button type="button" class="pay-btn" id="submitPayBtn">确认付款</button>
            </div>
        </div>
    </form>
    </div><!-- /.goods-right -->
</div><!-- /.goods-layout -->
</div><!-- /.main-card -->

<section class="goods-detail-section">
    <div class="goods-detail-header">
        <span class="goods-detail-bar"></span>
        <span class="goods-detail-title">商品详情</span>
    </div>
    <div class="goods-detail-body">
        <div class="intro" id="goodsDesc"><?= $goods['content'] ?: '暂无说明' ?></div>
    </div>
</section>
<br><br>

<!-- 商品图灯箱 -->
<div class="gimg-lightbox" id="gimgLightbox">
    <span class="lb-close" id="gimgClose">&times;</span>
    <span class="lb-arrow lb-prev" id="gimgPrev">&#10094;</span>
    <img id="gimgLightboxImg" src="" alt="">
    <span class="lb-arrow lb-next" id="gimgNext">&#10095;</span>
    <span class="lb-counter" id="gimgCounter">1 / 1</span>
</div>
</main>
<?php doAction('tpl_footer'); ?>

<?php if($detail_float_help_show == 'y'): ?>
<a href="<?= DC_URL ?>?action=help" class="float-help-btn" title="买家帮助">
    <img src="<?= !empty($float_help_icon) ? $float_help_icon : TEMPLATE_URL . 'img/mjbzp.png' ?>" alt="买家帮助">
</a>
<?php endif; ?>

<script>
layui.use(['layer', 'form'], function() {
    var $ = layui.$, layer = layui.layer, form = layui.form;
    var goodsId = <?= $goods['id'] ?>;
    var totalSpecGroups = <?= count($goods['spec'] ?? []) ?>;
    var selectedSpecs = [];
    var currentDiscountList = [];
    var currentUnitPrice = <?= $_default_goods_price ?>;
    var originalTotalPrice = <?= $_default_goods_price ?>;
    var discountTitle = <?= json_encode(!empty($goods['discount_title']) ? $goods['discount_title'] : '批发优惠', JSON_UNESCAPED_UNICODE) ?>;
    var couponDiscount = 0, couponValid = false;
    var couponApiUrl = '<?= DC_URL ?>content/plugins/coupon/api.php';
    var couponCountdownTimer = null, couponExpireTimestamp = 0, couponTimeOffset = 0;
    
    // 页面加载时滚动到顶部
    if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
    $(window).scrollTop(0);

    /* ========== 商品图：PC 切换 + 缩略图 hover + 箭头 + 移动端 swipe ========== */
    var GIMG_LIST = (window.__GOODS_GALLERY__ || []).slice();
    var gimgIdx = 0;
    var isMobile = window.innerWidth <= 768;

    /* --- PC：主图切换 --- */
    function gimgSwitch(idx){
        if(!GIMG_LIST.length) return;
        if(idx < 0) idx = GIMG_LIST.length - 1;
        if(idx >= GIMG_LIST.length) idx = 0;
        gimgIdx = idx;
        var $img = $('#goodsCoverImg');
        $img.removeClass('loaded').attr('src', GIMG_LIST[idx]);
        // onload in HTML handles adding 'loaded' class back
        $('#goodsThumbs .goods-thumb').removeClass('active').eq(idx).addClass('active');
        thumbEnsureVisible(idx);
    }
    // 主图箭头
    $('#coverPrev').on('click', function(e){ e.stopPropagation(); gimgSwitch(gimgIdx - 1); });
    $('#coverNext').on('click', function(e){ e.stopPropagation(); gimgSwitch(gimgIdx + 1); });

    /* --- PC：缩略图 hover 切换（鼠标移入即选中，无需点击） --- */
    var thumbHoverTimer = null;
    $('#goodsThumbs').on('mouseenter', '.goods-thumb', function(){
        var el = this;
        clearTimeout(thumbHoverTimer);
        thumbHoverTimer = setTimeout(function(){
            gimgSwitch(parseInt($(el).data('idx'), 10) || 0);
        }, 60);
    }).on('click', '.goods-thumb', function(){
        gimgSwitch(parseInt($(this).data('idx'), 10) || 0);
    });

    /* --- PC：缩略图左右滚动箭头 --- */
    var thumbScrollStep = 200;
    var $thumbsContainer = $('#goodsThumbs');
    function thumbUpdateArrows(){
        var el = $thumbsContainer[0];
        if(!el) return;
        var scrollMax = el.scrollWidth - el.clientWidth;
        $('#thumbPrev').toggle(el.scrollLeft > 2);
        $('#thumbNext').toggle(el.scrollLeft < scrollMax - 2);
    }
    function thumbEnsureVisible(idx){
        var $t = $thumbsContainer.find('.goods-thumb').eq(idx);
        if(!$t.length) return;
        var container = $thumbsContainer[0];
        var tLeft = $t[0].offsetLeft, tW = $t[0].offsetWidth;
        var cLeft = container.scrollLeft, cW = container.clientWidth;
        if(tLeft < cLeft) container.scrollLeft = tLeft - 8;
        else if(tLeft + tW > cLeft + cW) container.scrollLeft = tLeft + tW - cW + 8;
        setTimeout(thumbUpdateArrows, 50);
    }
    $('#thumbPrev').on('click', function(){ $thumbsContainer[0].scrollLeft -= thumbScrollStep; setTimeout(thumbUpdateArrows, 300); });
    $('#thumbNext').on('click', function(){ $thumbsContainer[0].scrollLeft += thumbScrollStep; setTimeout(thumbUpdateArrows, 300); });
    $thumbsContainer.css('scroll-behavior', 'smooth');
    setTimeout(thumbUpdateArrows, 200);

    /* --- 灯箱 --- */
    var $lb = $('#gimgLightbox'), $lbImg = $('#gimgLightboxImg'), $lbCounter = $('#gimgCounter');
    function gimgShowLightbox(idx){
        if(!GIMG_LIST.length) return;
        if(idx < 0) idx = GIMG_LIST.length - 1;
        if(idx >= GIMG_LIST.length) idx = 0;
        gimgIdx = idx;
        $lbImg.attr('src', GIMG_LIST[idx]);
        $lbCounter.text((idx+1) + ' / ' + GIMG_LIST.length);
        $('#gimgPrev, #gimgNext').toggle(GIMG_LIST.length > 1);
        $lb.addClass('show');
    }
    function gimgHideLightbox(){ $lb.removeClass('show'); }
    $('#goodsCoverMain').on('click', function(e){
        if($(e.target).hasClass('cover-arrow')) return;
        gimgShowLightbox(gimgIdx);
    });
    $('#gimgClose').on('click', gimgHideLightbox);
    $('#gimgPrev').on('click', function(e){ e.stopPropagation(); gimgShowLightbox(gimgIdx - 1); gimgSwitch(gimgIdx); });
    $('#gimgNext').on('click', function(e){ e.stopPropagation(); gimgShowLightbox(gimgIdx + 1); gimgSwitch(gimgIdx); });
    $lb.on('click', function(e){ if(e.target === this) gimgHideLightbox(); });
    $(document).on('keydown.gimg', function(e){
        if(!$lb.hasClass('show')) return;
        if(e.key === 'Escape') gimgHideLightbox();
        else if(e.key === 'ArrowLeft') $('#gimgPrev').click();
        else if(e.key === 'ArrowRight') $('#gimgNext').click();
    });

    /* --- 移动端：触摸滑动画廊 --- */
    (function(){
        var $swiper = $('#goodsSwiper'), $track = $('#goodsSwiperTrack'), $counter = $('#goodsSwiperCounter');
        if(!$swiper.length || !GIMG_LIST.length) return;
        var total = GIMG_LIST.length, current = 0;
        var startX = 0, startY = 0, dx = 0, isScrolling = null, swiperW = 0;

        function swiperGo(idx, animate){
            // 循环：超出范围则绕回
            if(idx < 0) idx = total - 1;
            if(idx >= total) idx = 0;
            current = idx;
            if(animate === false) $track.addClass('no-transition'); else $track.removeClass('no-transition');
            $track.css('transform', 'translateX(' + (-current * 100) + '%)');
            $counter.text((current + 1) + '/' + total);
            // 同步 PC 状态
            gimgIdx = current;
        }

        $swiper.on('touchstart', function(e){
            var t = e.originalEvent.touches[0];
            startX = t.pageX; startY = t.pageY; dx = 0; isScrolling = null;
            swiperW = $swiper.width();
            $track.addClass('no-transition');
        });
        $swiper.on('touchmove', function(e){
            var t = e.originalEvent.touches[0];
            dx = t.pageX - startX;
            var dy = t.pageY - startY;
            if(isScrolling === null) isScrolling = Math.abs(dy) > Math.abs(dx);
            if(isScrolling) return;
            e.preventDefault();
            var pct = -current * 100 + (dx / swiperW) * 100;
            $track.css('transform', 'translateX(' + pct + '%)');
        });
        $swiper.on('touchend touchcancel', function(){
            if(isScrolling) return;
            var threshold = swiperW * 0.2;
            if(dx < -threshold) swiperGo(current + 1, true);
            else if(dx > threshold) swiperGo(current - 1, true);
            else swiperGo(current, true);
        });

        // 点击打开灯箱
        $swiper.on('click', function(e){
            if(Math.abs(dx) > 5) return;
            gimgShowLightbox(current);
        });
    })();

    function copyGoodsLink() {
        var tempInput = document.createElement("input");
        tempInput.value = window.location.href;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand("copy");
        document.body.removeChild(tempInput);
        layer.msg('商品链接已复制，去分享给好友吧！', {icon: 1});
    }

    $('.goodsShareAction').on('click', function(e) {
        e.preventDefault();
        copyGoodsLink();
    });

    $('.kami-share-btn').on('click', function(e) {
        e.preventDefault();
        var shareData = {
            title: <?= json_encode($goods['goods_name']) ?>,
            text: '发现了一个不错的商品，快来看看吧！',
            url: window.location.href
        };

        if (navigator.share) {
            navigator.share(shareData).catch(function(error) {
                console.log('分享失败或取消', error);
            });
        } else {
            copyGoodsLink();
        }
    });
    
    // 检查优惠券是否可用
    function checkCouponEnabled() {
        $.get(couponApiUrl + '?action=check_enabled&goods_id=' + goodsId, function(res) {
            if (res.code == 200 && res.data && res.data.enabled) {
                $('#coupon-section').show();
            } else {
                $('#coupon-section').hide();
                couponValid = false;
                couponDiscount = 0;
            }
        }, 'json');
    }
    
    // 单规格商品直接检查优惠券
    if (totalSpecGroups === 0) {
        checkCouponEnabled();
        updatePriceStock();
    }
    
    // 优惠券打勾展开/收起
    $('#coupon-toggle-check').on('change', function() {
        if ($(this).is(':checked')) {
            $('#coupon-input-area').slideDown(200);
        } else {
            $('#coupon-input-area').slideUp(200);
            $('#coupon-code-input').val('');
            $('#coupon-result').html('').removeClass('success error');
            couponValid = false;
            couponDiscount = 0;
            if (couponCountdownTimer) { cancelAnimationFrame(couponCountdownTimer); couponCountdownTimer = null; }
            $('#coupon-countdown-bar').remove();
        }
    });

    // 优惠码输入框变化时重置状态
    $('#coupon-code-input').on('input', function() {
        couponValid = false;
        couponDiscount = 0;
        if (couponCountdownTimer) { cancelAnimationFrame(couponCountdownTimer); couponCountdownTimer = null; }
        $('#coupon-countdown-bar').remove();
        $('#coupon-result').html('').removeClass('success error');
    });
    
    // 验证优惠券
    $('#check-coupon-btn').on('click', function() {
        var code = $.trim($('#coupon-code-input').val());
        if (!code) { layer.msg('请输入优惠码'); return; }
        
        var totalPrice = originalTotalPrice > 0 ? originalTotalPrice : (parseFloat($('#totalPrice').text()) || 0);
        var loadIdx = layer.load(2);
        
        $.post(couponApiUrl + '?action=check', { code: code, goods_id: goodsId, amount: totalPrice }, function(res) {
            layer.close(loadIdx);
            if (res.code == 200) {
                couponValid = true;
                couponDiscount = parseFloat(res.data.discount);
                var expireTimestamp = parseInt(res.data.expire_timestamp) || 0;
                var serverTime = parseInt(res.data.server_time) || Date.now();
                
                var html = '✓ ' + res.data.name + '，优惠 <span class="coupon-discount">' + couponDiscount.toFixed(2) + ' 元</span>';
                
                if (expireTimestamp > 0) {
                    $('#coupon-countdown-bar').remove();
                    var countdownHtml = '<div id="coupon-countdown-bar"><div id="coupon-countdown"><span class="countdown-text"></span></div></div>';
                    $('.drawer-footer').prepend(countdownHtml);
                    $('#coupon-result').html(html).removeClass('error').addClass('success');
                    startCouponCountdownMs(expireTimestamp, serverTime - Date.now());
                } else {
                    $('#coupon-result').html(html).removeClass('error').addClass('success');
                }
                updateTotalPriceWithCoupon();
            } else {
                couponValid = false;
                couponDiscount = 0;
                $('#coupon-result').html('✗ ' + res.msg).removeClass('success').addClass('error');
            }
        }, 'json').fail(function() { layer.close(loadIdx); layer.msg('网络错误'); });
    });

    // 优惠券倒计时
    function startCouponCountdownMs(expireTs, timeOffset) {
        if (couponCountdownTimer) { cancelAnimationFrame(couponCountdownTimer); couponCountdownTimer = null; }
        couponExpireTimestamp = expireTs;
        couponTimeOffset = timeOffset || 0;
        
        function updateCountdown() {
            var now = Date.now() + couponTimeOffset;
            var remaining = couponExpireTimestamp - now;
            
            if (remaining <= 0) {
                couponValid = false;
                couponDiscount = 0;
                $('#coupon-countdown-bar').remove();
                $('#coupon-result').html('<span class="error">✗ 优惠券已过期，请重新验证</span>').removeClass('success').addClass('error');
                if (originalTotalPrice > 0) $('#totalPrice').text(originalTotalPrice.toFixed(2) + ' 元');
                layer.msg('优惠券已过期');
                couponCountdownTimer = null;
                return;
            }
            
            var hours = Math.floor(remaining / 3600000);
            var minutes = Math.floor((remaining % 3600000) / 60000);
            var seconds = Math.floor((remaining % 60000) / 1000);
            var milliseconds = Math.floor((remaining % 1000) / 10);
            
            var text = '剩余 ';
            if (hours > 0) {
                text += String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0') + ':' + String(milliseconds).padStart(2, '0');
            } else {
                text += String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0') + ':' + String(milliseconds).padStart(2, '0');
            }
            text += ' 过期';
            $('#coupon-countdown .countdown-text').text(text);
            couponCountdownTimer = requestAnimationFrame(updateCountdown);
        }
        updateCountdown();
    }
    
    function updateTotalPriceWithCoupon() {
        if (couponValid && couponDiscount > 0 && originalTotalPrice > 0) {
            var finalPrice = Math.max(0.01, originalTotalPrice - couponDiscount);
            var html = finalPrice.toFixed(2) + ' 元';
            html += ' <span style="font-size:12px;color:#4caf50;">（优惠券-' + couponDiscount.toFixed(2) + '元）</span>';
            $('#totalPrice').html(html);
        }
    }

    function collectSelectedSpecs() {
        selectedSpecs = [];
        for (var i = 0; i < totalSpecGroups; i++) {
            var $active = $('.spec-option[data-group="' + i + '"].active');
            if ($active.length) selectedSpecs.push($active.data('id'));
        }
    }

    function toggleBuyFormBySpecs() {
        if (selectedSpecs.length >= totalSpecGroups) {
            $('#buyFormSection').addClass('show');
            checkCouponEnabled();
        } else {
            $('#buyFormSection').removeClass('show');
        }
    }

    // 规格选择
    $(document).on('click', '.spec-option:not(.disabled)', function() {
        var $this = $(this), group = $this.data('group');
        $('.spec-option[data-group="' + group + '"]').removeClass('active');
        $this.addClass('active');

        collectSelectedSpecs();
        updatePriceStock();

        if (selectedSpecs.length >= totalSpecGroups) {
            $('#buyFormSection').addClass('show');
            checkCouponEnabled();
        } else {
            $('#buyFormSection').removeClass('show');
        }
    });
    
    // HTML实体解码
    function decodeHtml(html) {
        var txt = document.createElement('textarea');
        txt.innerHTML = html;
        return txt.value;
    }
    
    // 处理商品说明中的域名变量
    function processGoodsContent(content) {
        if (!content) return '';
        var currentDomain = window.location.hostname;
        var domainParts = currentDomain.split('.');
        var mainDomain = domainParts.length >= 3 ? domainParts.slice(-2).join('.') : currentDomain;
        var domainPattern = /([a-zA-Z0-9_-]+)\.(\{DOMAIN\})/g;
        return content.replace(domainPattern, function(match, subdomain) {
            var fullDomain = subdomain + '.' + mainDomain;
            return '<a href="http://' + fullDomain + '" target="_blank" style="color:' + THEME.primary + ';text-decoration:underline;">' + fullDomain + '</a>';
        });
    }

    // 更新价格库存
    function updatePriceStock() {
        $.post('<?= DC_URL ?>user/shop.php?action=goods_price_stock', {
            goods_id: goodsId,
            quantity: parseInt($('#qtyInput').val()) || 1,
            sku_ids: selectedSpecs
        }, function(res) {
            if (res.code == 200) {
                var data = res.data;
                if (data.is_select_sku == 'y') {
                    currentUnitPrice = parseFloat(data.unit_price) || 0;
                    currentDiscountList = data.discount_list || [];
                    
                    var discountPerUnit = parseFloat(data.discount_per_unit) || 0;
                    var actualUnitPrice = (currentUnitPrice - discountPerUnit).toFixed(2);
                    $('#goodsPrice').text(actualUnitPrice);
                    
                    originalTotalPrice = parseFloat(data.price) || 0;
                    
                    if(data.discount > 0){
                        $('#totalPrice').html(data.price + ' 元 <span style="font-size:12px;color:#999;">（已优惠' + data.discount + '元）</span>');
                    } else {
                        $('#totalPrice').text(data.price + ' 元');
                    }
                    
                    if (couponValid && couponDiscount > 0) updateTotalPriceWithCoupon();
                    
                    if(data.has_discount) $('#discountTag').show(); else $('#discountTag').hide();
                    
                    var mp = parseFloat(data.market_price) || 0;
                    if(mp > 0 && mp > (parseFloat(data.unit_price) || 0)){
                        $('#goodsMarketPrice').text('¥' + mp.toFixed(2)).show();
                    } else {
                        $('#goodsMarketPrice').hide();
                    }
                    
                    if(data.sku_content && data.sku_content.trim() !== ''){
                        $('#goodsDesc').html(processGoodsContent(decodeHtml(data.sku_content)));
                    } else {
                        $('#goodsDesc').html(processGoodsContent(decodeHtml(<?= json_encode($goods['content'] ?: '暂无说明') ?>)));
                    }
                }
                $('#goodsStock').text(data.stock > 0 ? data.stock : '已售罄');
                
                if (data.stock <= 0 && data.is_select_sku == 'y') {
                    $('#submitPayBtn').addClass('btn-disabled').css({'background': '#ccc', 'cursor': 'not-allowed'});
                } else {
                    $('#submitPayBtn').removeClass('btn-disabled').css({'background': THEME.button, 'cursor': 'pointer'});
                }
                
                // 更新规格可选状态
                var specOpts = {};
                $('.spec-option').each(function() { specOpts[$(this).data('id').toString()] = { el: $(this), hasStock: false }; });
                if(data.skus) {
                    data.skus.forEach(function(sku) {
                        if (sku.stock !== "0") {
                            sku.sku.split('-').forEach(function(id) { if (specOpts[id]) specOpts[id].hasStock = true; });
                        }
                    });
                }
                Object.values(specOpts).forEach(function(item) { item.hasStock ? item.el.removeClass('disabled') : item.el.addClass('disabled'); });
            }
        }, 'json');
    }

    if (totalSpecGroups > 0) {
        $('.spec-group').each(function() {
            var $first = $(this).find('.spec-option').first();
            if ($first.length) {
                $first.addClass('active');
            }
        });
        collectSelectedSpecs();
        toggleBuyFormBySpecs();
        if (selectedSpecs.length >= totalSpecGroups) {
            updatePriceStock();
        }
    }

    // 批发优惠弹窗（三类：每件优惠 / 订单优惠 / 订单折扣）
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
        html += '<div style="font-size:18px;font-weight:bold;margin-bottom:16px;text-align:center;">' + discountTitle + '</div>';
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
        layer.open({ type: 1, title: false, closeBtn: 0, shadeClose: true, content: html, area: ['340px', 'auto'], skin: 'discount-layer', btn: ['关闭'], btnAlign: 'c' });
    });
    
    // 数量选择
    $('#qtyMinus').on('click', function() { var v = parseInt($('#qtyInput').val()); if (v > 1) { $('#qtyInput').val(v - 1); updatePriceStock(); } });
    $('#qtyPlus').on('click', function() { $('#qtyInput').val(parseInt($('#qtyInput').val()) + 1); updatePriceStock(); });
    $('#qtyInput').on('change', function() { var v = parseInt($(this).val()); if (isNaN(v) || v < 1) $(this).val(1); updatePriceStock(); });
    
    // 支付方式选择
    $('.payment-item').on('click', function() { $('.payment-item').removeClass('active'); $(this).addClass('active'); });
    
    // 邮箱开关
    $('.email-switch-input').on('change', function() {
        var $row = $(this).closest('.email-field-row');
        var $inputWrap = $row.find('.email-input-wrap');
        var $input = $row.find('.email-input');
        if ($(this).is(':checked')) { $inputWrap.slideDown(200); $input.attr('name', $input.data('field-name')); }
        else { $inputWrap.slideUp(200); $input.removeAttr('name').val(''); }
    });
    
    // 表单回车提交
    $('#buyFormSection').on('keypress', function(e) { if (e.which === 13) { e.preventDefault(); $('#submitPayBtn').click(); } });

    // 确认付款
    $('#submitPayBtn').on('click', function() {
        if ($(this).hasClass('btn-disabled')) { layer.msg('该商品库存不足，无法购买'); return false; }
        if (totalSpecGroups > 0 && selectedSpecs.length < totalSpecGroups) { layer.msg('请先选择规格'); return false; }
        
        var hasEmpty = false, emptyFieldName = '';
        $('#buyFormSection .required-input').each(function() {
            if ($.trim($(this).val()) === '') {
                hasEmpty = true;
                var $row = $(this).closest('.input-field-row');
                emptyFieldName = ($row.find('.section-label').text() || $row.find('.email-field-title').text()).replace('*', '').trim();
                return false;
            }
        });
        if (hasEmpty) { layer.msg('请填写' + emptyFieldName); return false; }
        
        // 格式验证：邮箱、手机号、数字
        var formatError = false;
        $('#buyFormSection .input-field-input').each(function() {
            var val = $.trim($(this).val());
            if (!val) return true; // 空值已被上面的 required 校验处理
            var vtype = $(this).data('validate-type') || '';
            var fieldName = $(this).closest('.input-field-row').find('.section-label').text().replace('*', '').trim();
            if (vtype === 'email' && !/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/.test(val)) {
                layer.msg('请输入正确的邮箱格式：' + fieldName); formatError = true; return false;
            }
            if (vtype === 'tel' && !/^1[3-9]\d{9}$/.test(val)) {
                layer.msg('请输入正确的手机号码：' + fieldName); formatError = true; return false;
            }
            if (vtype === 'num' && isNaN(val)) {
                layer.msg('请输入正确的数字：' + fieldName); formatError = true; return false;
            }
        });
        if (formatError) return false;
        
        if ($('.email-switch-input').is(':checked')) {
            var emailVal = $.trim($('.email-input').val());
            if (emailVal === '') { layer.msg('请填写邮箱地址'); return false; }
            var emailReg = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!emailReg.test(emailVal)) { layer.msg('请输入正确的邮箱格式'); return false; }
        }
        
        var formData = $('#buyFormSection').serialize();
        formData += '&goods_id=' + goodsId;
        formData += '&quantity=' + (parseInt($('#qtyInput').val()) || 1);
        if (selectedSpecs.length > 0) { selectedSpecs.forEach(function(spec) { formData += '&sku_ids[]=' + spec; }); }
        formData += '&payment_plugin=' + encodeURIComponent($('.payment-item.active').data('method') || '');
        if (couponValid) { var couponCode = $.trim($('#coupon-code-input').val()); if (couponCode) formData += '&coupon_code=' + encodeURIComponent(couponCode); }
        
        layer.open({
            type: 1, title: false, closeBtn: 0, shadeClose: false,
            area: [window.innerWidth > 500 ? '400px' : '90%', 'auto'],
            content: '<div style="padding:30px 25px;text-align:center;"><div style="font-size:20px;font-weight:bold;color:#333;margin-bottom:20px;">温馨提示</div><div style="font-size:15px;color:#333;line-height:1.8;margin-bottom:25px;">付款前请仔细阅读商品说明！联系信息是查单提卡的重要凭证，建议填写手机号或字母数字组合。</div><div style="display:flex;gap:15px;"><button class="layui-btn layui-btn-lg" style="flex:1;background:#e5e5e5;color:#333;border:none;border-radius:8px;height:46px;" onclick="layer.closeAll()">返回重填</button><button class="layui-btn layui-btn-lg" style="flex:1;background:' + THEME.button + ';color:#fff;border:none;border-radius:8px;height:46px;" id="confirmPayBtn">确认付款</button></div></div>',
            success: function(layero, index) { $('#confirmPayBtn').on('click', function() { layer.close(index); doSubmitOrder(formData); }); }
        });
    });
    
    function doSubmitOrder(formData) {
        var loadIdx = layer.load(2);
        $.ajax({
            url: '<?= DC_URL ?>user/shop.php?action=xiadan', type: 'POST', data: formData, dataType: 'json', timeout: 5000,
            success: function(res) {
                layer.close(loadIdx);
                if (res.code == 401 && res.login_url) { layer.msg(res.msg); setTimeout(function(){ location.href = res.login_url; }, 800); return; }
                if (res.code == 400) layer.msg(res.msg);
                else if (res.code == 200) {
                    // 保存联系方式到浏览器缓存用于查单
                    try {
                        var contactVal = '';
                        var $firstRequired = $('#buyFormSection .required-input').first();
                        if ($firstRequired.length && $firstRequired.val().trim()) {
                            contactVal = $firstRequired.val().trim();
                        }
                        if (contactVal) {
                            var cached = JSON.parse(localStorage.getItem('dc_order_contacts') || '[]');
                            if (cached.indexOf(contactVal) === -1) {
                                cached.unshift(contactVal);
                                if (cached.length > 10) cached = cached.slice(0, 10);
                                localStorage.setItem('dc_order_contacts', JSON.stringify(cached));
                            }
                        }
                    } catch(e) {}
                    layer.msg('正在跳转支付页面'); location.href = '<?= DC_URL ?>?action=pay&out_trade_no=' + res.data.out_trade_no;
                }
            },
            error: function(xhr, status, error) { layer.close(loadIdx); layer.msg(error == 'timeout' ? '请求超时，请重试' : '请求失败：' + error); }
        });
    }

    var $kamiPageHeader = $('.kami-page-header');
    var $kamiPageHeaderPlaceholder = $('.kami-page-header-placeholder');
    var kamiPageHeaderOffsetTop = 0;
    var kamiPageHeaderTicking = false;

    function measureKamiPageHeader() {
        if (!$kamiPageHeader.length) {
            return;
        }
        var wasStuck = $kamiPageHeader.hasClass('is-stuck');
        if (wasStuck) {
            $kamiPageHeader.removeClass('is-stuck');
            $kamiPageHeaderPlaceholder.removeClass('is-active').height(0);
        }
        kamiPageHeaderOffsetTop = $kamiPageHeader.offset().top;
        $kamiPageHeaderPlaceholder.height($kamiPageHeader.outerHeight());
        updateKamiPageHeaderStickyState();
    }

    function updateKamiPageHeaderStickyState() {
        if (!$kamiPageHeader.length) {
            return;
        }
        if (window.innerWidth > 768) {
            $kamiPageHeader.removeClass('is-stuck');
            $kamiPageHeaderPlaceholder.removeClass('is-active').height(0);
            return;
        }
        var shouldStick = $(window).scrollTop() >= kamiPageHeaderOffsetTop;
        $kamiPageHeader.toggleClass('is-stuck', shouldStick);
        $kamiPageHeaderPlaceholder.toggleClass('is-active', shouldStick).height(shouldStick ? $kamiPageHeader.outerHeight() : 0);
    }

    function requestKamiPageHeaderUpdate() {
        if (kamiPageHeaderTicking) {
            return;
        }
        kamiPageHeaderTicking = true;
        window.requestAnimationFrame(function() {
            updateKamiPageHeaderStickyState();
            kamiPageHeaderTicking = false;
        });
    }

    measureKamiPageHeader();
    window.addEventListener('scroll', requestKamiPageHeaderUpdate, { passive: true });
    window.addEventListener('resize', measureKamiPageHeader);
});
</script>

<?php include View::getCommonView('footer') ?>

