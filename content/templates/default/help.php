<?php
/**
 * 买家帮助页面
 */
defined('DC_ROOT') || exit('access denied!');

$_front_tpl_options = _g();
$_front_tpl_options = is_array($_front_tpl_options) ? $_front_tpl_options : [];
$_front_tpl_get = function ($name, $default = null) use ($_front_tpl_options) {
    return array_key_exists($name, $_front_tpl_options) ? $_front_tpl_options[$name] : $default;
};

// 获取模板配置
$footer_show = _g('footer_show') ?: 'y';
// 头部按钮显示开关
$header_menu_show = _g('header_menu_show') ?: 'y';
$header_search_show = _g('header_search_show') ?: 'y';
$header_user_show = _g('header_user_show') ?: 'y';
$header_order_show = _g('header_order_show') ?: 'n';

// 读取客服信息
$service_qq = $_front_tpl_get('service_qq', '191955552');
$service_wechat = $_front_tpl_get('service_wechat', '191955552');

// 售后须知内容
$after_sale_notice_default = "1. 兑换码均为一次性商品，用码安装后自动绑定设备UDID，无法更改。\n2. 因联系信息泄露给他人、导致被他人查单提卡抢先安装的自己承担损失。\n3. 未使用的可联系客服补差升级或销码退款，已使用的不支持退款。";
$after_sale_notice = _g('after_sale_notice') ?: $after_sale_notice_default;
// 将换行转为HTML段落
$after_sale_notice_html = '';
$notice_lines = explode("\n", $after_sale_notice);
foreach ($notice_lines as $line) {
    $line = trim($line);
    if (!empty($line)) {
        $after_sale_notice_html .= '<p>' . htmlspecialchars($line) . '</p>';
    }
}
// 客服链接
$contact_presale_url = $_front_tpl_get('contact_presale_url', 'https://dcshop.xzsc.cc/');
$contact_aftersale_url = $_front_tpl_get('contact_aftersale_url', 'https://dcshop.xzsc.cc/');

// 判断是否有任何客服信息配置（全部留空则不显示联系客服入口）
$has_any_service = !empty($service_qq) || !empty($service_wechat) || !empty($contact_presale_url) || !empty($contact_aftersale_url);

// 常见问题
$default_faq = [
    ['q' => '如何购买商品', 'a' => '选择商品 > 填写联系信息 > 确认付款 > 一键复制卡密。'],
    ['q' => '如何提取卡密', 'a' => '支付成功后，系统会自动跳转到订单详情页面，您可以直接复制卡密信息。如果页面关闭，可以通过"查询订单"功能，输入您的联系信息查询订单。'],
    ['q' => '如何查看订单号', 'a' => '点击页面顶部的"查询订单"按钮，输入您购买时填写的联系信息（手机号/邮箱等），即可查看所有相关订单。'],
    ['q' => '如何使用证书兑换码', 'a' => '购买成功后获取兑换码，前往对应平台的兑换页面，输入兑换码即可完成兑换。具体兑换方式请参考商品详情说明。'],
    ['q' => '如何使用越狱工具定制码', 'a' => '购买定制码后，按照商品详情中的教程进行操作。如有疑问，请联系客服获取帮助。'],
    ['q' => '红包转账外币支付请联系客服', 'a' => '如需使用红包、转账或外币支付，请先联系客服确认支付方式和金额，避免支付错误导致的损失。'],
];
$faq_list = _g('faq_list') ?: $default_faq;
?>

<?php
// 获取商城头部自定义样式
$_shop_header_bg = _g('shop_header_bg') ?: '';
$_shop_title_color = _g('shop_title_color') ?: '';
$_shop_subtitle_color = _g('shop_subtitle_color') ?: '';
$_shop_nav_active_color = _g('shop_nav_active_color') ?: '';
$_shop_nav_active_bg = _g('shop_nav_active_bg') ?: '';
$_has_shop_header_setting = !empty($_shop_header_bg);
?>
<style>
    <?php if($footer_show != 'y'): ?>
    .main-footer, .footer-nav.tel-footer {
        display: none !important;
    }
    <?php endif; ?>
    
    /* 隐藏底部导航 */
    .footer-nav {
        display: none !important;
    }
    
    <?php if($_has_shop_header_setting): ?>
    /* 使用后台设置的商城头部样式 */
    .h-fix {
        background: <?= htmlspecialchars($_shop_header_bg) ?> !important;
    }
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
    .header-search-order-btn .a:hover { background-color: rgba(255,255,255,0.3) !important; }
    <?php else: ?>
    /* 默认蓝色主题（后台未设置时） */
    .h-fix {
        background: #0c6be1 !important;
    }
    .logo-text a span {
        color: #fff !important;
    }
    .logo-brand .brand-title {
        color: #fff !important;
    }
    .logo-brand .brand-subtitle {
        color: rgba(255,255,255,0.8) !important;
    }
    .header .nav-bar li a {
        color: #fff !important;
    }
    .header .nav-bar li a:hover {
        color: #fff !important;
        background: rgba(255,255,255,0.15) !important;
    }
    .header .nav-bar li.active > a {
        color: #fff !important;
        background: rgba(255,255,255,0.2) !important;
    }
    .search i.fa, .header-user i.fa, .m-btn i.fa {
        color: #fff !important;
    }
    .search i.fa:hover, .header-user i.fa:hover, .m-btn i.fa:hover {
        background: rgba(255,255,255,0.15) !important;
        color: #fff !important;
    }
    .header-search-order-btn .a {
        background-color: rgba(255,255,255,0.2) !important;
    }
    .header-search-order-btn .a:hover {
        background-color: rgba(255,255,255,0.3) !important;
    }
    <?php endif; ?>
    <?php if($header_menu_show != 'y'): ?>
    .m-btn { display: none !important; }
    <?php endif; ?>
    <?php if($header_search_show != 'y'): ?>
    .search { display: none !important; }
    <?php endif; ?>
    <?php if($header_user_show != 'y'): ?>
    .header-user { display: none !important; }
    <?php endif; ?>
    <?php if($header_order_show != 'y'): ?>
    .header-search-order-btn { display: none !important; }
    <?php endif; ?>
    
    /* 买家帮助页面隐藏买家帮助按钮，显示返回商品按钮 */
    .header-help-mobile { display: none !important; }
    .header-back-home {
        display: flex !important;
        align-items: center;
        height: 72px;
        margin-left: 10px;
    }
    .header-back-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 36px;
        border-radius: 4px;
        background-color: rgba(255,255,255,0.2);
        color: #fff;
        font-size: 14px;
        padding: 0 1rem;
        text-decoration: none;
        transition: all 0.3s ease;
        white-space: nowrap;
    }
    .header-back-btn:hover {
        background-color: rgba(255,255,255,0.3);
        text-decoration: none;
        color: #fff;
    }
    @media (max-width: 1200px) {
        .header-back-home {
            margin-left: 0;
            margin-right: 5px;
        }
    }
    
    /* logo和标题可点击跳转首页 */
    .logo-brand a {
        cursor: pointer;
    }
    
    /* 替换logo图片 */
    .logo-brand .brand-logo {
        content: url('<?= TEMPLATE_URL ?>img/mjbzlogo.png');
    }
    
    /* 覆盖头部标题和副标题 */
    .logo-brand .brand-title {
        font-size: 17.5px !important;
    }
    .logo-brand .brand-subtitle {
        font-size: 12px !important;
    }

    /* 页面内容区域 */
    .help-body {
        padding-top: 20px;
        padding-bottom: 30px;
        min-height: calc(100vh - 72px);
    }
    
    /* 页面容器 */
    .help-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 0 15px;
    }
    
    /* 快捷入口 */
    .help-shortcuts {
        background: rgba(248, 248, 248, 0.5);
        border-radius: 8px;
        padding: 10px 15px;
        margin-bottom: 10px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        border: 2px solid #fff;
    }
    .shortcuts-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
    }
    .shortcut-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        color: #333;
        padding: 10px;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .shortcut-item:hover {
        background: #f5f5f5;
        text-decoration: none;
        color: #333;
    }
    .shortcut-icon {
        width: 55px;
        height: 55px;
        border-radius: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
        font-size: 26px;
    }
    .shortcut-icon.blue { background: rgba(var(--theme-primary-rgb, 33, 150, 243), 0.1); color: var(--theme-primary, #2196F3); }
    .shortcut-icon.green { background: #e8f5e9; color: #4caf50; }
    .shortcut-icon.orange { background: rgba(var(--theme-accent-rgb, 255, 152, 0), 0.1); color: var(--theme-accent, #ff9800); }
    .shortcut-icon.red { background: #ffebee; color: #f44336; }
    .shortcut-text {
        font-size: 14px;
        font-weight: bold;
        color: #000;
    }
    
    /* 常见问题 */
    .help-section {
        background: rgba(248, 248, 248, 0.5);
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        border: 2px solid #fff;
    }
    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        margin-bottom: 15px;
        padding-left: 10px;
        border-left: 3px solid var(--theme-primary, #2196F3);
    }
    
    /* FAQ列表 */
    .faq-list {
        border-top: 1px solid #f0f0f0;
    }
    .faq-item {
        border-bottom: 1px solid #f0f0f0;
    }
    .faq-question {
        display: flex;
        align-items: center;
        padding: 15px 10px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .faq-question:hover {
        background: #fafafad4;
    }
    .faq-num {
        color: var(--theme-price, #ff6600);
        font-weight: 600;
        margin-right: 8px;
        font-size: 14px;
    }
    .faq-title {
        flex: 1;
        font-size: 15px;
        color: #333;
    }
    .faq-arrow {
        color: #ccc;
        font-size: 14px;
        transition: transform 0.3s;
    }
    .faq-item.active .faq-arrow {
        transform: rotate(180deg);
        color: var(--theme-primary, #2196F3);
    }
    .faq-answer {
        display: none;
        padding: 0 10px 15px 26px;
        font-size: 14px;
        color: #666;
        line-height: 1.8;
    }
    .faq-item.active .faq-answer {
        display: block;
    }
    .faq-answer a {
        color: var(--theme-primary, #2196F3);
        text-decoration: none;
    }
    .faq-answer a:hover {
        text-decoration: underline;
    }
    
    /* 售后须知弹窗样式 */
    .after-sale-layer {
        border-radius: 8px !important;
        overflow: hidden;
    }
    .after-sale-popup {
        padding: 30px 25px;
        background: rgba(248, 248, 248, 0.5);
        border: 2px solid #fff;
    }
    .popup-title {
        font-size: 20px;
        font-weight: bold;
        color: #333;
        text-align: center;
        margin-bottom: 25px;
    }
    .popup-content {
        margin-bottom: 25px;
    }
    .popup-content p {
        font-size: 14px;
        color: #333;
        line-height: 1.8;
        margin-bottom: 15px;
    }
    .popup-content p:last-child {
        margin-bottom: 0;
    }
    .popup-buttons {
        display: flex;
        gap: 15px;
    }
    .popup-btn {
        flex: 1;
        height: 46px;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
    }
    .btn-back {
        background: #e5e5e5;
        color: #333;
    }
    .btn-back:hover {
        background: #d5d5d5;
    }
    .btn-confirm {
        background: var(--theme-primary, #0c6be1);
        color: #fff;
    }
    .btn-confirm:hover {
        background: var(--theme-primary-dark, #0a5bc4);
    }
    
    /* 购前必读弹窗样式 */
    .before-buy-layer {
        border-radius: 12px !important;
        overflow: hidden;
    }
    .before-buy-popup {
        background: #fff;
    }
    .before-buy-popup .popup-header {
        padding: 20px;
        position: relative;
        min-height: 140px;
    }
    .before-buy-popup .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .before-buy-popup .header-text {
        flex: 1;
    }
    .before-buy-popup .header-text h2 {
        font-size: 21px;
        font-weight: bold;
        color: #fff;
        margin: 0;
    }
    .before-buy-popup .header-text p {
        font-size: 14px;
        color: rgba(255,255,255,0.9);
        margin: 0;
    }
    .before-buy-popup .header-text p strong {
        font-size: 17.5px;
    }
    .before-buy-popup .header-img {
        width: 90px;
        flex-shrink: 0;
    }
    .before-buy-popup .header-img img {
        width: 100%;
        height: auto;
    }
    .before-buy-popup .popup-body {
        padding: 0 25px;
    }
    .before-buy-popup .notice-item {
        padding: 10px 0;
        border-bottom: 1px solid #f5f5f5;
    }
    .before-buy-popup .notice-item:last-child {
        border-bottom: none;
    }
    .before-buy-popup .notice-title {
        display: flex;
        align-items: center;
        font-size: 16px;
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
    }
    .before-buy-popup .notice-icon {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        font-size: 14px;
    }
    .before-buy-popup .notice-icon-img {
        width: 28px;
        height: 28px;
        margin-right: 10px;
    }
    .before-buy-popup .notice-desc {
        font-size: 13px;
        color: #666;
        padding-left: 38px;
        line-height: 1.6;
    }
    .before-buy-popup .popup-footer {
        padding: 15px 25px 20px;
        text-align: center;
        border-top: 1px solid #f5f5f5;
        cursor: pointer;
    }
    .before-buy-popup .close-link {
        font-size: 16px;
        color: var(--theme-primary, #0c6be1);
        text-decoration: none;
        font-weight: bold;
        display: block;
        width: 100%;
    }
    .before-buy-popup .close-link:hover {
        color: var(--theme-primary-dark, #0a5bc4);
    }
    
    /* 联系客服弹窗额外样式 */
    .contact-popup .notice-title-with-btn {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .contact-popup .notice-title-left {
        display: flex;
        align-items: center;
    }
    .contact-popup .contact-btn {
        display: inline-block;
        padding: 4px 10px;
        background: #356dfe;
        border: none;
        border-radius: 4px;
        color: #fff;
        font-size: 12px;
        font-weight: normal;
        text-decoration: none;
        transition: all 0.2s;
    }
    .contact-popup .contact-btn:hover {
        background: #2a5be0;
        color: #fff;
    }
    
    /* QQ和微信客服信息样式 */
    .contact-info {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        margin-top: 8px;
    }
    .contact-info-text {
        flex: 1;
        font-size: 14px;
        color: #333;
        font-weight: 500;
    }
    .contact-copy-btn {
        padding: 4px 12px;
        background: var(--theme-primary, #2196F3);
        border: none;
        border-radius: 4px;
        color: #fff;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .contact-copy-btn:hover {
        background: var(--theme-primary-dark, #1976D2);
        transform: scale(1.05);
    }
    .contact-copy-btn.copied {
        background: #4caf50;
    }
    .contact-copy-btn.copied i {
        animation: checkmark 0.3s ease;
    }
    @keyframes checkmark {
        0% { transform: scale(0); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
    
    /* 移动端适配 */
    @media (max-width: 768px) {
        .help-body {
            padding-top: 15px;
            padding-bottom: 20px;
        }
        .help-container {
            padding: 0 10px;
        }
        .shortcuts-grid {
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }
        .shortcut-icon {
            width: 44px;
            height: 44px;
            font-size: 20px;
            margin-bottom: 6px;
        }
        .shortcut-text {
            font-size: 14px;
        }
        .help-section {
            padding: 15px;
        }
        .faq-title {
            font-size: 14px;
        }
        .faq-question {
            padding: 12px 8px;
        }
    }
</style>

<!-- 页面内容 -->
<div class="help-body">
    <div class="help-container">
        <!-- 快捷入口 -->
        <div class="help-shortcuts">
            <div class="shortcuts-grid">
                <a href="javascript:;" class="shortcut-item" onclick="showBeforeBuyNotice()">
                    <div class="shortcut-icon blue">
                        <i class="fa fa-book"></i>
                    </div>
                    <span class="shortcut-text">购前必读</span>
                </a>
                <?php if ($has_any_service): ?>
                <a href="javascript:;" class="shortcut-item" onclick="showContact()">
                    <div class="shortcut-icon green">
                        <i class="fa fa-headphones"></i>
                    </div>
                    <span class="shortcut-text">联系客服</span>
                </a>
                <?php endif; ?>
                <a href="<?= DC_URL ?>?action=order_query" class="shortcut-item">
                    <div class="shortcut-icon orange">
                        <i class="fa fa-search"></i>
                    </div>
                    <span class="shortcut-text">查询订单</span>
                </a>
                <a href="javascript:;" class="shortcut-item" onclick="showAfterSaleNotice()">
                    <div class="shortcut-icon red">
                        <i class="fa fa-exclamation-circle"></i>
                    </div>
                    <span class="shortcut-text">售后须知</span>
                </a>
            </div>
        </div>
        
        <!-- 常见问题 -->
        <div class="help-section">
            <div class="section-title">常见问题</div>
            <div class="faq-list">
                <?php foreach ($faq_list as $i => $faq): ?>
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(<?= $i ?>)">
                        <span class="faq-num"><?= $i + 1 ?>.</span>
                        <span class="faq-title"><?= htmlspecialchars($faq['q']) ?></span>
                        <i class="fa fa-angle-down faq-arrow"></i>
                    </div>
                    <div class="faq-answer">
                        <?= htmlspecialchars($faq['a']) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFaq(index) {
    var items = document.querySelectorAll('.faq-item');
    items.forEach(function(item, i) {
        if (i === index) {
            item.classList.toggle('active');
        } else {
            item.classList.remove('active');
        }
    });
}

function showContact() {
    var isMobile = window.innerWidth <= 768;
    
    // 构建客服信息HTML
    var serviceInfoHtml = '';
    
    // QQ客服信息
    <?php if (!empty($service_qq)): ?>
    serviceInfoHtml += `
        <div class="notice-item">
            <div class="notice-title">
                <img class="notice-icon-img" src="<?= TEMPLATE_URL ?>img/c4.png" alt="">
                <span>QQ客服</span>
            </div>
            <div class="notice-desc">
                <div class="contact-info">
                    <span class="contact-info-text"><?= htmlspecialchars($service_qq) ?></span>
                    <button class="contact-copy-btn" onclick="copyToClipboard('<?= htmlspecialchars($service_qq) ?>', this)">
                        <i class="fa fa-copy"></i> 复制
                    </button>
                </div>
            </div>
        </div>
    `;
    <?php endif; ?>
    
    // 微信客服信息
    <?php if (!empty($service_wechat)): ?>
    serviceInfoHtml += `
        <div class="notice-item">
            <div class="notice-title">
                <img class="notice-icon-img" src="<?= TEMPLATE_URL ?>img/c6.png" alt="">
                <span>微信客服</span>
            </div>
            <div class="notice-desc">
                <div class="contact-info">
                    <span class="contact-info-text"><?= htmlspecialchars($service_wechat) ?></span>
                    <button class="contact-copy-btn" onclick="copyToClipboard('<?= htmlspecialchars($service_wechat) ?>', this)">
                        <i class="fa fa-copy"></i> 复制
                    </button>
                </div>
            </div>
        </div>
    `;
    <?php endif; ?>
    
    layer.open({
        type: 1,
        title: false,
        closeBtn: 0,
        shadeClose: false,
        area: isMobile ? ['90%', 'auto'] : ['450px', 'auto'],
        skin: 'before-buy-layer',
        content: `
            <div class="before-buy-popup contact-popup">
                <div class="popup-header" style="background: url('<?= TEMPLATE_URL ?>img/bg.png') no-repeat center; background-size: cover;">
                    <div class="header-content">
                        <div class="header-text">
                            <h2>人工客服在线应答</h2>
                            <p>售前咨询：购买或支付问题</p>
                            <p>售后客服：已购订单问题</p>
                        </div>
                        <div class="header-img">
                            <img src="<?= TEMPLATE_URL ?>img/bgr03.png" alt="">
                        </div>
                    </div>
                </div>
                <div class="popup-body">
                    <div class="notice-item">
                        <div class="notice-title">
                            <img class="notice-icon-img" src="<?= TEMPLATE_URL ?>img/c0.png" alt="">
                            <span>工作时间</span>
                        </div>
                        <div class="notice-desc">上午10点-晚上22点，请在工作时间联系我们。</div>
                    </div>
                    ${serviceInfoHtml}
                    <?php if (!empty($contact_presale_url)): ?>
                    <div class="notice-item">
                        <div class="notice-title notice-title-with-btn">
                            <div class="notice-title-left">
                                <img class="notice-icon-img" src="<?= TEMPLATE_URL ?>img/c4.png" alt="">
                                <span>售前咨询</span>
                            </div>
                            <a href="<?= htmlspecialchars($contact_presale_url) ?>" target="_blank" class="contact-btn">点此联系</a>
                        </div>
                        <div class="notice-desc">向企业微信发送您想要咨询的问题。</div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($contact_aftersale_url)): ?>
                    <div class="notice-item">
                        <div class="notice-title notice-title-with-btn">
                            <div class="notice-title-left">
                                <img class="notice-icon-img" src="<?= TEMPLATE_URL ?>img/c6.png" alt="">
                                <span>售后客服</span>
                            </div>
                            <a href="<?= htmlspecialchars($contact_aftersale_url) ?>" target="_blank" class="contact-btn">点此联系</a>
                        </div>
                        <div class="notice-desc">提供下单时预留的联系方式，以便核查订单记录。</div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="popup-footer" onclick="layer.closeAll()">
                    <a href="javascript:;" class="close-link">关闭</a>
                </div>
            </div>
        `
    });
}

// 售后须知弹窗
function showAfterSaleNotice() {
    var isMobile = window.innerWidth <= 768;
    layer.open({
        type: 1,
        title: false,
        closeBtn: 0,
        shadeClose: false,
        area: isMobile ? ['90%', 'auto'] : ['450px', 'auto'],
        skin: 'after-sale-layer',
        content: `
            <div class="after-sale-popup">
                <div class="popup-title">售后须知</div>
                <div class="popup-content">
                    <?= $after_sale_notice_html ?>
                </div>
                <div class="popup-buttons">
                    <button class="popup-btn btn-back" onclick="setNoRemindToday()">今日不再提示</button>
                    <button class="popup-btn btn-confirm" onclick="layer.closeAll()">我已阅读</button>
                </div>
            </div>
        `
    });
}

// 设置今日不再提示
function setNoRemindToday() {
    var today = new Date().toDateString();
    localStorage.setItem('afterSaleNoticeHideDate', today);
    layer.closeAll();
}

// 检查是否需要自动弹出售后须知
function checkAutoShowAfterSaleNotice() {
    var today = new Date().toDateString();
    var hideDate = localStorage.getItem('afterSaleNoticeHideDate');
    if (hideDate !== today) {
        showAfterSaleNotice();
    }
}

// 购前必读弹窗
function showBeforeBuyNotice() {
    var isMobile = window.innerWidth <= 768;
    layer.open({
        type: 1,
        title: false,
        closeBtn: 0,
        shadeClose: false,
        area: isMobile ? ['90%', 'auto'] : ['450px', 'auto'],
        skin: 'before-buy-layer',
        content: `
            <div class="before-buy-popup">
                <div class="popup-header" style="background: url('<?= TEMPLATE_URL ?>img/bg.png') no-repeat center; background-size: cover;">
                    <div class="header-content">
                        <div class="header-text">
                            <h2>自助购买自动发卡</h2>
                            <p>第 <strong>1</strong> 步：选择分类和商品</p>
                            <p>第 <strong>2</strong> 步：在线支付提卡</p>
                        </div>
                        <div class="header-img">
                            <img src="<?= TEMPLATE_URL ?>img/bgr00.png" alt="">
                        </div>
                    </div>
                </div>
                <div class="popup-body">
                    <div class="notice-item">
                        <div class="notice-title">
                            <img class="notice-icon-img" src="<?= TEMPLATE_URL ?>img/t2.png" alt="">
                            <span>温馨提示</span>
                        </div>
                        <div class="notice-desc">付款前请仔细阅读商品说明。</div>
                    </div>
                    <div class="notice-item">
                        <div class="notice-title">
                            <img class="notice-icon-img" src="<?= TEMPLATE_URL ?>img/t5.png" alt="">
                            <span>特别提醒</span>
                        </div>
                        <div class="notice-desc">联系信息是查单提卡的重要凭证，切勿泄露给他人！</div>
                    </div>
                    <div class="notice-item">
                        <div class="notice-title">
                            <img class="notice-icon-img" src="<?= TEMPLATE_URL ?>img/t3.png" alt="">
                            <span>联系信息</span>
                        </div>
                        <div class="notice-desc">填写手机号或字母数字组合，如jfkj777。</div>
                    </div>
                </div>
                <div class="popup-footer" onclick="layer.closeAll()">
                    <a href="javascript:;" class="close-link">关闭</a>
                </div>
            </div>
        `
    });
}

// 复制到剪贴板功能
function copyToClipboard(text, button) {
    // 创建临时文本域
    var textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        // 执行复制命令
        var successful = document.execCommand('copy');
        if (successful) {
            // 更新按钮状态
            var icon = button.querySelector('i');
            var originalText = button.innerHTML;
            
            button.classList.add('copied');
            button.innerHTML = '<i class="fa fa-check"></i> 已复制';
            
            // 2秒后恢复原状
            setTimeout(function() {
                button.classList.remove('copied');
                button.innerHTML = originalText;
            }, 2000);
            
            // 显示成功提示
            if (typeof layer !== 'undefined') {
                layer.msg('已复制到剪贴板', {icon: 1, time: 1500});
            } else {
                alert('已复制到剪贴板');
            }
        } else {
            throw new Error('复制失败');
        }
    } catch (err) {
        // 复制失败的处理
        if (typeof layer !== 'undefined') {
            layer.msg('复制失败，请手动复制', {icon: 2, time: 2000});
        } else {
            alert('复制失败，请手动复制');
        }
        console.error('复制失败:', err);
    } finally {
        // 清理临时元素
        document.body.removeChild(textArea);
    }
}

// 修改头部标题和副标题
$(function() {
    // 修改标题
    $('.logo-brand .brand-title').text('买家帮助服务中心');
    // 修改副标题
    $('.logo-brand .brand-subtitle').text('如遇支付问题请联系客服');
    // 如果没有副标题元素，则添加
    if ($('.logo-brand .brand-subtitle').length === 0) {
        $('.logo-brand .brand-text').append('<span class="brand-subtitle">如遇支付问题请联系客服</span>');
    }
    
    // 在头部右侧添加返回商品按钮
    var backBtn = '<div class="header-back-home"><a href="javascript:;" class="header-back-btn" onclick="goBackToGoods()">返回商品</a></div>';
    $('.header-help-mobile').after(backBtn);
    
    // 页面加载时检查是否需要自动弹出售后须知
    checkAutoShowAfterSaleNotice();
});

// 智能返回：如果来源是商品详情页则返回，否则返回首页
function goBackToGoods() {
    var referrer = document.referrer;
    // 检查来源是否是商品详情页（包含 post= 或 /post/ 或 /post-）
    if (referrer && (referrer.indexOf('post=') > -1 || referrer.indexOf('/post/') > -1 || referrer.indexOf('/post-') > -1)) {
        window.location.href = referrer;
    } else {
        window.location.href = '<?= DC_URL ?>';
    }
}
</script>

