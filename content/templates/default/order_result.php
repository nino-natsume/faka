<?php
/**
 * 订单查询结果页面
 */
defined('DC_ROOT') || exit('access denied!');

// 获取模板配置
$footer_show = _g('footer_show') ?: 'y';
// 头部按钮显示开关
$header_menu_show = _g('header_menu_show') ?: 'y';
$header_search_show = _g('header_search_show') ?: 'y';
$header_user_show = _g('header_user_show') ?: 'y';
$header_order_show = _g('header_order_show') ?: 'n';

// 获取系统配置 - 订单列表商品图显示开关
$order_goods_img_switch = Option::get('order_goods_img_switch') ?: 'y';
// 检查售后服务插件是否启用
$active_plugins = Option::get('active_plugins');
$aftersale_plugin_enabled = is_array($active_plugins) && in_array('aftersale/aftersale.php', $active_plugins);

if (!function_exists('__payment_cn')) {
    function __payment_cn($raw) {
        static $map = [
            'alipay' => '支付宝', 'alipay2' => '支付宝', 'wechat' => '微信支付', 'wxpay' => '微信支付', 'weixin' => '微信支付',
            'wechatpay' => '微信支付', 'qq' => 'QQ钱包', 'qqpay' => 'QQ钱包', 'balance' => '余额支付',
            'bank' => '银行卡', 'paypal' => 'PayPal', 'usdt' => 'USDT',
            'epay_wx' => '微信支付', 'epay_ali' => '支付宝', 'epay_ali2' => '支付宝',
            'epay_qq' => 'QQ钱包', 'epay_jj' => '京东支付', 'epay_jj2' => '京东支付',
            'ynl_wx' => '微信支付', 'ynl_ali' => '支付宝', 'manual_pay' => '手动转账',
        ];
        $s = trim((string)$raw);
        if ($s === '') return '未知';
        $key = strtolower($s);
        if (isset($map[$key])) return $map[$key];
        if (stripos($s, 'alipay') !== false || stripos($s, '支付宝') !== false) return preg_match('/[\x{4e00}-\x{9fff}]/u', $s) ? $s : '支付宝';
        if (stripos($s, 'wechat') !== false || stripos($s, 'weixin') !== false || stripos($s, 'wxpay') !== false || stripos($s, '微信') !== false) return preg_match('/[\x{4e00}-\x{9fff}]/u', $s) ? $s : '微信支付';
        if (stripos($s, 'qq') !== false) return preg_match('/[\x{4e00}-\x{9fff}]/u', $s) ? $s : 'QQ钱包';
        return $s;
    }
}

// 获取商城头部自定义样式
$_shop_header_bg = _g('shop_header_bg') ?: '';
$_shop_title_color = _g('shop_title_color') ?: '';
$_shop_subtitle_color = _g('shop_subtitle_color') ?: '';
$_shop_nav_active_color = _g('shop_nav_active_color') ?: '';
$_shop_nav_active_bg = _g('shop_nav_active_bg') ?: '';
$_has_shop_header_setting = !empty($_shop_header_bg);
?>

<style>
    :root {
        --neon-pink: var(--theme-primary, #667eea);
        --neon-purple: var(--theme-primary-dark, #764ba2);
    }

    <?php if($footer_show != 'y'): ?>
    .main-footer {
        display: none !important;
    }
    <?php endif; ?>
    
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
    
    /* 隐藏买家帮助按钮，显示返回查询按钮 */
    .header-help-mobile { display: none !important; }
    .header-back-home {
        display: block !important;
        float: right;
        height: 72px;
        line-height: 72px;
        margin-left: 20px;
    }
    .header-back-btn {
        display: inline-block;
        height: 36px;
        line-height: 36px;
        text-align: center;
        border-radius: 4px;
        background-color: rgba(255,255,255,0.2);
        color: #fff;
        font-size: 14px;
        padding: 0 1rem;
        text-decoration: none;
        transition: all 0.3s ease;
        vertical-align: middle;
    }
    .header-back-btn:hover {
        background-color: rgba(255,255,255,0.3);
        text-decoration: none;
        color: #fff;
    }
    @media (max-width: 1200px) {
        .header-back-home {
            margin-left: 0;
            margin-right: 10px;
        }
    }
    
    /* logo和标题可点击跳转首页 */
    .logo-brand a {
        cursor: pointer;
    }
    
    /* 替换logo图片 */
    .logo-brand .brand-logo {
        content: url('<?= TEMPLATE_URL ?>img/chaxunxitong1.png');
    }
    
    /* 覆盖头部标题和副标题 */
    .logo-brand .brand-title {
        font-size: 17.5px !important;
    }
    .logo-brand .brand-subtitle {
        font-size: 12px !important;
    }

    /* 主体内容 */
    .result-body {
        flex: 1 0 auto;
        padding: 0 15px;
        max-width: 900px;
        margin: 0 auto;
        padding-bottom: 30px;
        width: 100%;
        box-sizing: border-box;
    }
    
    /* 结果头部 */
    .result-header {
        background: rgba(248, 248, 248, 0.5);
        border: 2px solid #fff;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .result-title {
        font-size: 18px;
        font-weight: bold;
        color: #333;
    }
    .result-count {
        font-size: 14px;
        color: #666;
    }
    .result-count span {
        color: var(--theme-primary, #0c6be1);
        font-weight: bold;
    }

    /* 订单列表 */
    .order-list {
        margin-top: 15px;
    }

    /* 订单卡片 */
    .order-card {
        background-color: rgba(248, 248, 248, 0.5);
        border: 2px solid #fff;
        border-radius: 8px;
        margin-bottom: 15px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
    }

    /* 订单头部信息 */
    .order-header-info {
        padding: 15px 20px;
        border-bottom: 1px solid #f2f2f2;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .order-no {
        color: #666;
        font-size: 13px;
    }

    .order-status {
        font-weight: 500;
        font-size: 14px;
    }
    .order-status.paid {
        color: #52c41a;
    }
    .order-status.unpaid {
        color: #ff7d00;
    }
    .order-status.pending {
        color: #ff7d00;
    }
    .order-status.closed {
        color: #999;
    }
    .order-status.refunding {
        color: #ef4444;
    }

    /* 订单商品列表 */
    .order-goods {
        padding: 15px 20px;
    }

    .goods-item {
        display: flex;
    }

    .goods-img {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        object-fit: cover;
        margin-right: 15px;
        background-color: #f5f5f5;
        flex-shrink: 0;
    }
    .goods-img-placeholder {
        width: 80px; height: 80px; border-radius: 8px; margin-right: 15px;
        background: #f3f4f6; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        color: #d1d5db; font-size: 28px;
    }

    .goods-info {
        flex: 1;
        min-width: 0;
    }

    .goods-name {
        font-size: 15px;
        color: #333;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 8px;
    }
    .goods-name a {
        color: #333;
        text-decoration: none;
    }
    .goods-name a:hover {
        color: var(--theme-primary, #0c6be1);
    }

    .goods-spec {
        font-size: 13px;
        color: #999;
        margin-bottom: 5px;
    }

    .goods-price-count {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 8px;
    }

    .goods-price {
        color: #f53f3f;
        font-weight: 500;
        font-size: 16px;
    }

    .goods-count {
        color: #666;
        font-size: 13px;
    }

    /* 订单金额信息 */
    .order-amount {
        padding: 12px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid #f2f2f2;
        background: #fafafa;
    }

    .pay-time {
        color: #999;
        font-size: 13px;
    }

    .amount-info {
        text-align: right;
    }
    .amount-text {
        color: #666;
        font-size: 13px;
    }

    .amount-value {
        color: #f53f3f;
        font-size: 18px;
        font-weight: bold;
    }

    /* 订单操作按钮区 */
    .order-actions {
        padding: 12px 20px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        border-top: 1px solid #f2f2f2;
    }

    .action-btn {
        padding: 8px 20px;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
        border: 1px solid #ddd;
        background-color: #fff;
        color: #666;
        text-decoration: none;
        transition: all 0.2s;
    }
    .action-btn:hover {
        border-color: var(--theme-primary, #0c6be1);
        color: var(--theme-primary, #0c6be1);
    }

    .action-btn.primary {
        background-color: var(--theme-primary, #0c6be1);
        color: #fff;
        border-color: var(--theme-primary, #0c6be1);
    }
    .action-btn.primary:hover {
        background-color: var(--theme-primary-dark, #0a5bc4);
    }
    
    /* 新按钮样式 */
    .order-btn-group {
        padding: 12px 20px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        border-top: 1px solid #f2f2f2;
    }
    .order-btn {
        padding: 10px 20px;
        border-radius: 20px;
        font-size: 14px;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .order-btn i {
        font-size: 14px;
    }
    .btn-view-kami {
        background: linear-gradient(135deg, var(--neon-pink), var(--neon-purple));
        color: #fff;
        border: none;
    }
    .btn-view-kami:hover {
        background: linear-gradient(135deg, var(--neon-purple), var(--neon-pink));
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
    .btn-view-service {
        background: linear-gradient(135deg, var(--neon-pink), var(--neon-purple));
        color: #fff;
        border: none;
    }
    .btn-view-service:hover {
        background: linear-gradient(135deg, var(--neon-purple), var(--neon-pink));
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
    .btn-rebuy {
        background: #fff;
        color: var(--theme-primary, #0c6be1);
        border: 1px solid var(--theme-primary, #0c6be1);
    }
    .btn-rebuy:hover {
        background: var(--theme-primary, #0c6be1);
        color: #fff;
    }
    /* ===== 待支付：取消 + 继续付款倒计时 ===== */
    .order-btn-group .btn-cancel-order {
        padding: 10px 20px; border-radius: 20px; font-size: 14px; font-weight: 500;
        cursor: pointer; text-decoration: none; transition: all .2s ease;
        display: inline-flex; align-items: center; gap: 6px;
        border: 1px solid #e5e7eb; background: #fff; color: #6b7280; white-space: nowrap;
    }
    .order-btn-group .btn-cancel-order:hover {
        border-color: #ef4444; color: #ef4444; background: #fef2f2;
        transform: translateY(-1px);
    }
    .order-btn-group .btn-continue-pay {
        padding: 10px 20px; border-radius: 20px; font-size: 14px; font-weight: 500;
        cursor: pointer; text-decoration: none; transition: all .2s ease;
        display: inline-flex; align-items: center; gap: 6px;
        border: 1px solid transparent; white-space: nowrap;
        background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%); color: #fff;
    }
    .order-btn-group .btn-continue-pay:hover {
        color: #fff; transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(245,158,11,0.32);
    }
    .btn-continue-pay .countdown { font-weight: 400; font-size: 12px; opacity: .85; margin-left: 2px; }
    
    /* ========== 卡密详情页样式 ========== */
    .kami-page {
        display: none;
    }
    .kami-page.show {
        display: block;
    }
    .order-list-page.hide {
        display: none;
    }
    
    /* ========== 虚拟服务详情页样式 ========== */
    .service-page {
        display: none;
    }
    .service-page.show {
        display: block;
    }
    
    /* 服务状态卡片 */
    .service-status-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 25px 20px;
        margin-top: 15px;
        color: #fff;
        text-align: center;
    }
    .service-status-icon {
        width: 60px;
        height: 60px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
    }
    .service-status-icon i {
        font-size: 28px;
        color: #fff;
    }
    .service-status-text {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 8px;
    }
    .service-status-desc {
        font-size: 14px;
        opacity: 0.9;
    }
    
    /* 服务信息卡片 */
    .service-info-card {
        background: #fff;
        border-radius: 12px;
        margin-top: 15px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
    }
    .service-info-header {
        padding: 15px 20px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .service-info-header i {
        color: #667eea;
        font-size: 16px;
    }
    .service-info-header span {
        font-size: 15px;
        font-weight: 500;
        color: #333;
    }
    .service-info-list {
        padding: 0;
    }
    .service-info-item {
        display: flex;
        padding: 15px 20px;
        border-bottom: 1px solid #f5f5f5;
    }
    .service-info-item:last-child {
        border-bottom: none;
    }
    .service-info-label {
        width: 80px;
        color: #999;
        font-size: 14px;
        flex-shrink: 0;
    }
    .service-info-value {
        flex: 1;
        color: #333;
        font-size: 14px;
        word-break: break-all;
    }
    .service-info-value.highlight {
        color: #667eea;
        font-weight: 500;
    }
    
    /* 商家留言卡片 */
    .service-message-card {
        background: #fff;
        border-radius: 12px;
        margin-top: 15px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
    }
    .service-message-header {
        background: #f0f5ff;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .service-message-header i {
        color: #667eea;
        font-size: 16px;
    }
    .service-message-header span {
        font-size: 15px;
        font-weight: 500;
        color: #333;
    }
    .service-message-content {
        padding: 15px 20px;
        font-size: 14px;
        color: #666;
        line-height: 1.8;
    }
    
    /* 深色模式 */
    html[data-theme="dark"] .service-status-card {
        background: linear-gradient(135deg, #5a6fd6 0%, #6a4190 100%);
    }
    html[data-theme="dark"] .service-info-card,
    html[data-theme="dark"] .service-message-card {
        background: #1e1e1e;
    }
    html[data-theme="dark"] .service-info-header,
    html[data-theme="dark"] .service-message-header {
        border-color: #333;
    }
    html[data-theme="dark"] .service-message-header {
        background: #2a2a2a;
    }
    html[data-theme="dark"] .service-info-header span,
    html[data-theme="dark"] .service-message-header span {
        color: #e0e0e0;
    }
    html[data-theme="dark"] .service-info-item {
        border-color: #333;
    }
    html[data-theme="dark"] .service-info-label {
        color: #888;
    }
    html[data-theme="dark"] .service-info-value {
        color: #e0e0e0;
    }
    html[data-theme="dark"] .service-message-content {
        color: #b0b0b0;
    }
    
    /* 卡密页头部 */
    .kami-page-header {
        background: rgba(255, 255, 255, 0.72);
        -webkit-backdrop-filter: saturate(180%) blur(20px);
        backdrop-filter: saturate(180%) blur(20px);
        border: 2px solid #fff;
        border-radius: 8px;
        padding: 15px 20px;
        margin-top: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        display: flex;
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
    
    /* 使用说明卡片 */
    .kami-usage-card {
        background: #fff;
        border-radius: 12px;
        margin-top: 15px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
    }
    .kami-usage-header {
        background: #f0f7ff;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .kami-usage-icon {
        width: 28px;
        height: 28px;
        background: var(--theme-primary, #0c6be1);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 14px;
        flex-shrink: 0;
    }
    .kami-usage-text {
        font-size: 15px;
        color: #333;
        font-weight: 500;
    }
    
    /* 使用说明内容 */
    .kami-pay-content {
        padding: 15px 20px;
        font-size: 14px;
        color: #666;
        line-height: 1.8;
    }
    .kami-pay-content img {
        max-width: 100%;
        height: auto;
    }
    .kami-pay-content a {
        color: var(--theme-primary, #0c6be1);
    }
    
    /* 卡密内容卡片 */
    .kami-content-card {
        background: rgba(248, 248, 248, 0.5);
        border-radius: 12px;
        margin-top: 15px;
        overflow: hidden;
        border-bottom: 2px solid #fff;
    }
    .kami-content-header {
        background: rgba(248, 248, 248, 0.5);
        border: 2px solid #fff;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        border-bottom: 1px solid #f0f0f0;
    }
    .kami-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .kami-info-icon {
        width: 28px;
        height: 28px;
        background: rgba(var(--theme-primary-rgb, 33, 150, 243), 0.1);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--theme-primary, #0c6be1);
        font-size: 14px;
    }
    .kami-info-text {
        font-size: 14px;
        color: #333;
        font-weight: bold;
    }
    .kami-info-text span {
        color: var(--theme-primary, #0c6be1);
        font-weight: bold;
    }
    .kami-header-actions {
        display: flex;
        gap: 8px;
    }
    .kami-header-btn {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .btn-copy-all {
        background: #fff;
        color: var(--theme-primary, #0c6be1);
        border: 1px solid var(--theme-primary, #0c6be1);
    }
    .btn-copy-all:hover {
        background: var(--theme-primary, #0c6be1);
        color: #fff;
    }
    .btn-export {
        background: transparent;
        color: var(--theme-primary, #0c6be1);
        border: 1px solid transparent;
    }
    .btn-export:hover {
        color: var(--theme-primary-dark, #0a5bc4);
        background: rgba(var(--theme-primary-rgb, 33, 150, 243), 0.05);
    }
    
    /* 卡密列表 */
    .kami-list {
        padding: 0;
    }
    .kami-item {
        background: rgba(248, 248, 248, 0.5);
        padding: 15px 20px;
        border-bottom: 1px solid #f5f5f5;
        border-left: 2px solid #fff;
        border-right: 2px solid #fff;
    }
    .kami-item:last-child {
        border-bottom: none;
    }
    .kami-item-header {
        margin-bottom: 10px;
    }
    .kami-item-num {
        font-size: 13px;
        color: #000;
        font-weight: bold;
    }
    .kami-item-content {
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }
    .kami-item-value {
        flex: 1;
        font-size: 14px;
        color: #000;
        font-weight: bold;
        word-break: break-all;
        line-height: 1.6;
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        background: #f8fafc;
        padding: 12px 15px;
        border-radius: 8px;
        border: 1px solid #eee;
    }
    .kami-item-copy {
        font-size: 13px;
        color: var(--theme-primary, #0c6be1);
        cursor: pointer;
        background: none;
        border: none;
        padding: 12px 0;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .kami-item-copy:hover {
        color: var(--theme-primary-dark, #0a5bc4);
    }
    
    /* 联系卖家卡片 */
    .kami-contact-card {
        background: rgba(248, 248, 248, 0.5);
        border: 2px solid #fff;
        border-radius: 12px;
        padding: 20px;
        margin-top: 15px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
    }
    .contact-title {
        font-size: 15px;
        color: #333;
        font-weight: 500;
        margin-bottom: 15px;
    }
    .contact-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 14px;
        background: #f5f5f5;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        color: #666;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        border: 1.5px solid #fff;
    }
    .contact-btn:hover {
        background: #eee;
        color: #333;
    }
    .contact-btn i {
        font-size: 18px;
    }
    
    /* 加载状态 */
    .kami-loading {
        padding: 60px 20px;
        text-align: center;
        color: #999;
        background: #fff;
        border-radius: 12px;
        margin-top: 15px;
    }
    .kami-loading i {
        font-size: 24px;
        margin-bottom: 10px;
        display: block;
    }

    /* 空状态 */
    .empty-order {
        text-align: center;
        padding: 80px 20px;
        background: #fff;
        border-radius: 12px;
        margin-top: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
    }

    .empty-icon {
        font-size: 60px;
        margin-bottom: 20px;
        color: #ddd;
    }
    .empty-text {
        font-size: 16px;
        color: #999;
        margin-bottom: 20px;
    }
    .empty-btn {
        display: inline-block;
        padding: 10px 30px;
        background: var(--theme-primary, #0c6be1);
        color: #fff;
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
    }
    .empty-btn:hover {
        background: var(--theme-primary-dark, #0a5bc4);
        color: #fff;
    }

    /* 移动端适配 */
    @media screen and (max-width: 768px) {
        /* 移动端头部隐藏footer */
        .header {
            display: block !important;
        }
        body.kami-page-open .header,
        body.service-page-open .header {
            display: none !important;
        }
        .result-body {
            padding: 0 10px;
            padding-bottom: 90px;
        }
        .result-header {
            margin-top: 15px;
            padding: 15px;
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }
        .result-title {
            font-size: 16px;
        }
        .order-header-info {
            padding: 12px 15px;
        }
        .order-goods {
            padding: 12px 15px;
        }
        .goods-img {
            width: 70px;
            height: 70px;
        }
        .goods-name {
            font-size: 14px;
        }
        .order-amount {
            padding: 10px 15px;
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }
        .amount-info {
            text-align: left;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .order-actions {
            padding: 12px 15px;
            flex-wrap: wrap;
        }
        .action-btn {
            flex: 1;
            min-width: 100px;
            text-align: center;
        }
        /* 移动端按钮组 */
        .order-btn-group {
            padding: 12px 15px;
        }
        .order-btn {
            padding: 10px 16px;
            font-size: 13px;
            flex: 1;
            justify-content: center;
        }
        /* 移动端卡密页面 */
        .kami-page-header {
            margin-top: 15px;
            padding: 12px 15px;
            position: fixed;
            top: 0;
            left: 10px;
            right: 10px;
            z-index: 1001;
            box-sizing: border-box;
        }
        #kamiPage .kami-page-header {
            margin-top: 15px;
            margin-bottom: 15px;
            position: relative;
            top: auto;
            left: auto;
            right: auto;
        }
        #kamiPage .kami-page-header-placeholder {
            display: none;
            height: 0;
        }
        #kamiPage .kami-page-header-placeholder.is-active {
            display: block;
        }
        #kamiPage .kami-page-header.is-stuck {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            margin-top: 0;
            margin-bottom: 0;
            border-radius: 0;
        }
        .kami-page-title {
            font-size: 16px;
        }
        .service-page.show {
            padding-top: 74px;
        }
        .kami-usage-card {
            padding: 12px 15px;
        }
        .kami-content-header {
            padding: 15px;
            flex-direction: column;
            align-items: flex-start;
        }
        .kami-header-actions {
            width: 100%;
        }
        .kami-header-btn {
            flex: 1;
            justify-content: center;
        }
        .kami-item {
            padding: 12px 15px;
        }
        .kami-item-value {
            padding: 10px 12px;
            font-size: 13px;
        }
        .kami-contact-card {
            padding: 15px;
        }
    }
</style>

<!-- 主体内容 -->
<div class="result-body">
    <!-- ========== 订单列表页 ========== -->
    <div class="order-list-page" id="orderListPage">
        <!-- 结果头部 -->
        <div class="result-header">
            <div class="result-title">订单查询结果</div>
            <div class="result-count">共找到 <span><?= count($list) ?></span> 个订单</div>
        </div>
        
        <?php if(empty($list)): ?>
        <!-- 空状态 -->
        <div class="empty-order">
            <div class="empty-icon">
                <i class="fa fa-inbox"></i>
            </div>
            <div class="empty-text">没有找到相关订单</div>
            <a href="<?= DC_URL ?>?action=order_query" class="empty-btn">重新查询</a>
        </div>
        <?php else: ?>
        <!-- 订单列表 -->
        <div class="order-list">
            <?php foreach($list as $index => $val): ?>
            <div class="order-card <?= !empty($val['is_gift']) ? 'gift-card' : '' ?>" data-order-no="<?= $val['out_trade_no'] ?>">
                <div class="order-header-info">
                    <div class="order-no">订单编号: <?= $val['out_trade_no'] ?></div>
                    <div class="order-status <?= !empty($val['is_refunding']) ? 'refunding' : ($val['status'] == 2 ? 'paid' : ($val['status'] == 0 ? 'unpaid' : ($val['status'] == 1 ? 'pending' : 'closed'))) ?>">
                        <?php if(!empty($val['is_gift'])): ?>
                        <span style="color:#52c41a;font-weight:bold;">🎁 赠品</span>
                        <?php else: ?>
                        <?= $val['status_text'] ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="order-goods">
                    <div class="goods-item">
                        <?php if($order_goods_img_switch == 'y'): ?>
                        <?php if (!empty($val['cover'])): ?>
                        <a href="<?= $val['url'] ?>" target="_blank">
                            <img src="<?= $val['cover'] ?>" class="goods-img" alt="<?= $val['title'] ?>" onerror="this.style.display='none';this.parentNode.insertAdjacentHTML('afterbegin','<div class=goods-img-placeholder><i class=&quot;fa fa-image&quot;></i></div>');">
                        </a>
                        <?php else: ?>
                        <a href="<?= $val['url'] ?>" target="_blank"><div class="goods-img-placeholder"><i class="fa fa-image"></i></div></a>
                        <?php endif; ?>
                        <?php endif; ?>
                        <div class="goods-info">
                            <div class="goods-name">
                                <?php if(!empty($val['is_gift'])): ?>
                                <span style="background:#52c41a;color:#fff;padding:1px 6px;border-radius:3px;font-size:12px;margin-right:5px;">赠品</span>
                                <?php endif; ?>
                                <a href="<?= $val['url'] ?>" target="_blank"><?= $val['title'] ?></a>
                            </div>
                            <?php if(!empty($val['attr_spec'])): ?>
                            <div class="goods-spec"><?= str_replace('[赠品] ', '', $val['attr_spec']) ?></div>
                            <?php endif; ?>
                            <?php if(!empty($val['attach_user_text'])): ?>
                            <div class="goods-spec"><?= $val['attach_user_text'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="order-amount">
                    <div class="pay-time">
                        支付方式：<?= htmlspecialchars(__payment_cn($val['payment'] ?? '')) ?>
                        <span style="margin:0 6px;color:#d1d5db;">|</span>
                        <?php if(!empty($val['pay_time'])): ?>
                        付款时间：<?= $val['pay_time_text'] ?>
                        <?php else: ?>
                        创建时间：<?= date('Y-m-d H:i:s', $val['create_time']) ?>
                        <?php endif; ?>
                    </div>
                    <div class="amount-info">
                        <?php if(!empty($val['is_gift'])): ?>
                        <span class="amount-text">共<?= $val['quantity'] ?>件商品</span>
                        <span class="amount-value" style="color:#52c41a;">免费赠送</span>
                        <span style="color:#999;font-size:12px;text-decoration:line-through;margin-left:5px;">原价￥<?= $val['unit_price_yuan'] ?></span>
                        <?php else: ?>
                        <span class="amount-text">共<?= $val['quantity'] ?>件商品 合计：</span>
                        <span class="amount-value">￥<?= $val['amount'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- 按钮组 -->
                <div class="order-btn-group">
                    <?php if($val['status'] == 0): ?>
                    <a href="javascript:;" class="order-btn btn-cancel-order" onclick="cancelOrder('<?= $val['out_trade_no'] ?>', this)">
                        <i class="fa fa-times-circle"></i> 取消订单
                    </a>
                    <?php if ((Option::get('continue_pay_switch') ?: 'y') === 'y'): ?>
                    <a href="<?= DC_URL ?>?action=pay&out_trade_no=<?= urlencode($val['out_trade_no']) ?>" class="order-btn btn-continue-pay" data-create-time="<?= (int)$val['create_time'] ?>">
                        <i class="fa fa-credit-card"></i> 继续付款 <span class="countdown"></span>
                    </a>
                    <?php endif; ?>
                    <?php else: ?>
                    <?php if($val['type'] != 'physical' && ($val['type'] == 'service' || (int)$val['status'] === 1)): ?>
                    <?php if($val['status'] == 1 || $val['status'] == 2): ?>
                    <a href="javascript:;" class="order-btn btn-view-service" onclick="showServicePage('<?= $val['out_trade_no'] ?>', '<?= $val['order_list_id'] ?>', '<?= $val['status'] ?>')">
                        <i class="fa fa-file-text-o"></i> 订单详情
                    </a>
                    <?php endif; ?>
                    <?php elseif($val['type'] != 'physical' && $val['status'] == 2): ?>
                    <a href="javascript:;" class="order-btn btn-view-kami" onclick="showKamiPage('<?= $val['out_trade_no'] ?>', <?= $val['order_list_id'] ?>)">
                        <i class="fa fa-eye"></i> 查看卡密
                    </a>
                    <?php endif; ?>
                    <?php if(empty($val['is_gift'])): ?>
                        <?php if(in_array((int)$val['status'], [1, 2, 4], true)): ?>
                            <?php doAction('order_result_item_buttons', $val); ?>
                            <?php if(!$aftersale_plugin_enabled): ?>
                            <a href="<?= DC_URL ?>" class="order-btn btn-rebuy">
                                <i class="fa fa-shopping-cart"></i> 再次购买
                            </a>
                            <?php endif; ?>
                        <?php else: ?>
                        <a href="<?= DC_URL ?>" class="order-btn btn-rebuy">
                            <i class="fa fa-shopping-cart"></i> 再次购买
                        </a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- ========== 卡密详情页 ========== -->
    <div class="kami-page" id="kamiPage">
        <!-- 页面头部 -->
        <div class="kami-page-header">
            <a href="javascript:;" class="kami-back-btn" onclick="backToOrderList()">
                <i class="fa fa-chevron-left"></i>
            </a>
            <div class="kami-page-title">订单卡密</div>
        </div>
        <div class="kami-page-header-placeholder"></div>
        
        <!-- 卡密内容容器 -->
        <div id="kamiContent">
            <div class="kami-loading">
                <i class="fa fa-spinner fa-spin"></i>
                加载中...
            </div>
        </div>
    </div>
    
    <!-- ========== 虚拟服务订单详情页 ========== -->
    <div class="service-page" id="servicePage">
        <!-- 页面头部 -->
        <div class="kami-page-header">
            <a href="javascript:;" class="kami-back-btn" onclick="backFromServicePage()">
                <i class="fa fa-chevron-left"></i>
            </a>
            <div class="kami-page-title">订单详情</div>
        </div>
        
        <!-- 服务详情内容容器 -->
        <div id="serviceContent">
            <div class="kami-loading">
                <i class="fa fa-spinner fa-spin"></i>
                加载中...
            </div>
        </div>
    </div>
    
</div>

<script>
// 修改头部标题和副标题
$(function() {
    // 修改标题
    $('.logo-brand .brand-title').text('订单查询结果');
    // 修改副标题
    $('.logo-brand .brand-subtitle').text('仅显示一个月内的购买记录');
    // 如果没有副标题元素，则添加
    if ($('.logo-brand .brand-subtitle').length === 0) {
        $('.logo-brand .brand-text').append('<span class="brand-subtitle">仅显示一个月内的购买记录</span>');
    }
    
    // 在头部右侧添加返回查询按钮
    var backBtn = '<div class="header-back-home"><a href="<?= DC_URL ?>?action=order_query" class="header-back-btn">返回查询</a></div>';
    $('.header-help-mobile').after(backBtn);
    
    // ========== 待支付倒计时 ==========
    var PAY_TIMEOUT = <?= max(1, intval(Option::get('continue_pay_timeout') ?: 30)) ?> * 60;
    function fmtCountdown(sec) {
        if (sec <= 0) return '已超时';
        var m = Math.floor(sec / 60), s = sec % 60;
        return (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
    }
    function _autoCancelExpired($card) {
        if ($card.data('auto-cancelled')) return;
        $card.data('auto-cancelled', true);
        var outTradeNo = $card.data('order-no');
        if (!outTradeNo) return;
        $.ajax({
            url: '<?= DC_URL ?>user/api.php?action=cancel_order',
            type: 'POST', data: { out_trade_no: outTradeNo }, dataType: 'json',
            success: function (res) {
                if (res.code == 200) {
                    $card.find('.order-status').text('已取消').css('color', '#999');
                    $card.find('.order-btn-group').html(
                        '<a href="<?= DC_URL ?>" class="order-btn btn-rebuy"><i class="fa fa-shopping-cart"></i> 再次购买</a>'
                    );
                }
            }
        });
    }
    function tickCountdown() {
        var now = Math.floor(Date.now() / 1000);
        $('.btn-continue-pay').each(function () {
            var $btn = $(this);
            var ct = parseInt($btn.data('create-time')) || 0;
            var remain = PAY_TIMEOUT - (now - ct);
            var $cd = $btn.find('.countdown');
            if (remain <= 0) {
                $cd.text('已超时');
                $btn.css({ opacity: .55, pointerEvents: 'none' });
                _autoCancelExpired($btn.closest('.order-card'));
            } else {
                $cd.text(fmtCountdown(remain));
            }
        });
    }
    tickCountdown();
    setInterval(tickCountdown, 1000);
});

// ========== 取消订单 ==========
function cancelOrder(outTradeNo, btnEl) {
    layer.confirm('确定要取消该订单吗？取消后将无法恢复。', {
        btn: ['确定取消', '再想想'], icon: 3, title: '取消订单'
    }, function (idx) {
        layer.close(idx);
        var li = layer.load(2);
        $.ajax({
            url: '<?= DC_URL ?>user/api.php?action=cancel_order',
            type: 'POST',
            data: { out_trade_no: outTradeNo },
            dataType: 'json',
            success: function (res) {
                layer.close(li);
                if (res.code == 200) {
                    layer.msg('订单已取消', { icon: 1, time: 1500 });
                    var $card = $(btnEl).closest('.order-card');
                    $card.find('.order-status').text('已取消').css('color', '#999');
                    $card.find('.order-btn-group').html(
                        '<a href="<?= DC_URL ?>" class="order-btn btn-rebuy"><i class="fa fa-shopping-cart"></i> 再次购买</a>'
                    );
                } else {
                    layer.msg(res.msg || '取消失败', { icon: 2 });
                }
            },
            error: function () { layer.close(li); layer.msg('请求失败', { icon: 2 }); }
        });
    });
}

// 当前订单号
var currentOrderNo = '';
var currentOrderListId = 0;
// 卡密数据缓存
var kamiCache = {};
var $kamiPageHeader = $('#kamiPage .kami-page-header');
var $kamiPageHeaderPlaceholder = $('#kamiPage .kami-page-header-placeholder');
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
    if (window.innerWidth > 768 || !$('#kamiPage').hasClass('show')) {
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

window.addEventListener('scroll', requestKamiPageHeaderUpdate, { passive: true });
window.addEventListener('resize', measureKamiPageHeader);

// 显示卡密页面
function showKamiPage(orderNo, orderListId) {
    currentOrderNo = orderNo;
    currentOrderListId = orderListId || 0;
    var cacheKey = orderNo + '_' + orderListId;
    
    // 切换页面
    $('body').addClass('kami-page-open');
    $('#orderListPage').addClass('hide');
    $('#kamiPage').addClass('show');
    
    // 滚动到顶部
    window.scrollTo(0, 0);
    window.requestAnimationFrame(function() {
        measureKamiPageHeader();
    });
    
    // 如果有缓存直接显示
    if (kamiCache[cacheKey]) {
        renderKamiPage(kamiCache[cacheKey]);
    } else {
        // 显示加载状态
        $('#kamiContent').html('<div class="kami-loading"><i class="fa fa-spinner fa-spin"></i> 加载中...</div>');
        // 加载卡密数据
        loadKamiData(orderNo, orderListId);
    }
}

// 返回订单列表
function backToOrderList() {
    $('body').removeClass('kami-page-open');
    $kamiPageHeader.removeClass('is-stuck');
    $kamiPageHeaderPlaceholder.removeClass('is-active').height(0);
    $('#kamiPage').removeClass('show');
    $('#orderListPage').removeClass('hide');
}

// ========== 虚拟服务详情页功能 ==========
var currentServiceOrderNo = '';
var currentServiceOrderListId = 0;
var currentServiceStatus = 0;
var serviceCache = {};

function showServicePage(orderNo, orderListId, status) {
    currentServiceOrderNo = orderNo;
    currentServiceOrderListId = parseInt(orderListId) || 0;
    currentServiceStatus = parseInt(status) || 1;
    var cacheKey = orderNo + '_' + orderListId;

    $('body').addClass('service-page-open');
    $('#orderListPage').addClass('hide');
    $('#servicePage').addClass('show');

    window.scrollTo(0, 0);

    delete serviceCache[cacheKey];

    $('#serviceContent').html('<div class="kami-loading"><i class="fa fa-spinner fa-spin"></i> 加载中...</div>');
    loadServiceData(orderNo, currentServiceOrderListId);
}

// 返回订单列表（从服务详情页）
function backFromServicePage() {
    $('body').removeClass('service-page-open');
    $('#servicePage').removeClass('show');
    $('#orderListPage').removeClass('hide');
}

// 加载服务详情数据
function loadServiceData(orderNo, orderListId) {
    var cacheKey = orderNo + '_' + orderListId;
    $.ajax({
        type: "POST",
        url: "<?= DC_URL ?>user/api.php?action=get_service_detail",
        data: { out_trade_no: orderNo, order_list_id: orderListId },
        dataType: "json",
        success: function(res) {
            if (res.code == 200) {
                serviceCache[cacheKey] = res.data;
                renderServicePage(res.data);
            } else {
                $('#serviceContent').html('<div class="kami-loading">' + (res.msg || '加载失败') + '</div>');
            }
        },
        error: function() {
            $('#serviceContent').html('<div class="kami-loading">加载失败，请重试</div>');
        }
    });
}

// 渲染服务详情页
function renderServicePage(data) {
    var html = '';
    var status = data.status !== undefined ? data.status : currentServiceStatus;
    var isCompleted = (status == 2);
    
    // 状态卡片
    if (isCompleted) {
        // 已完成状态 - 绿色
        html += '<div class="service-status-card" style="background:linear-gradient(135deg, #52c41a 0%, #389e0d 100%);">';
        html += '<div class="service-status-icon"><i class="fa fa-check-circle"></i></div>';
        html += '<div class="service-status-text">订单已完成</div>';
        html += '<div class="service-status-desc">商家已处理完成，感谢您的购买</div>';
        html += '</div>';
    } else {
        // 待发货状态 - 蓝紫色（与主题一致）
        html += '<div class="service-status-card">';
        html += '<div class="service-status-icon"><i class="fa fa-clock-o"></i></div>';
        html += '<div class="service-status-text">等待商家处理</div>';
        html += '<div class="service-status-desc">您的订单已支付成功，商家正在处理中</div>';
        html += '</div>';
    }
    
    // 订单信息卡片
    html += '<div class="service-info-card">';
    html += '<div class="service-info-header"><i class="fa fa-file-text-o"></i><span>订单信息</span></div>';
    html += '<div class="service-info-list">';
    html += '<div class="service-info-item"><div class="service-info-label">订单编号</div><div class="service-info-value">' + escapeHtml(data.out_trade_no || currentServiceOrderNo) + '</div></div>';
    html += '<div class="service-info-item"><div class="service-info-label">商品名称</div><div class="service-info-value">' + escapeHtml(data.goods_title || '-') + '</div></div>';
    if (data.attr_spec) {
        html += '<div class="service-info-item"><div class="service-info-label">商品规格</div><div class="service-info-value">' + escapeHtml(data.attr_spec) + '</div></div>';
    }
    html += '<div class="service-info-item"><div class="service-info-label">购买数量</div><div class="service-info-value">' + (data.quantity || 1) + ' 件</div></div>';
    html += '<div class="service-info-item"><div class="service-info-label">订单金额</div><div class="service-info-value highlight">￥' + (data.amount || '0.00') + '</div></div>';
    html += '<div class="service-info-item"><div class="service-info-label">支付时间</div><div class="service-info-value">' + escapeHtml(data.pay_time || '-') + '</div></div>';
    html += '<div class="service-info-item"><div class="service-info-label">订单状态</div><div class="service-info-value ' + (isCompleted ? '' : 'highlight') + '" style="' + (isCompleted ? 'color:#52c41a;' : '') + '">' + (isCompleted ? '已完成' : '待发货') + '</div></div>';
    html += '</div>';
    html += '</div>';
    
    // 发货内容卡片（已完成时显示）
    if (isCompleted && data.deliver_content) {
        html += '<div class="service-message-card">';
        html += '<div class="service-message-header" style="background:#e6f7ff;"><i class="fa fa-gift" style="color:#1890ff;"></i><span>发货内容</span></div>';
        html += '<div class="service-message-content" style="white-space:pre-wrap;">' + escapeHtml(data.deliver_content) + '</div>';
        html += '</div>';
    }
    
    // 商家留言卡片（如果有）
    if (data.message) {
        html += '<div class="service-message-card">';
        html += '<div class="service-message-header"><i class="fa fa-commenting-o"></i><span>商家留言</span></div>';
        html += '<div class="service-message-content">' + escapeHtml(data.message) + '</div>';
        html += '</div>';
    }
    
    // 温馨提示卡片
    html += '<div class="service-info-card" style="margin-top:15px;">';
    html += '<div class="service-info-header"><i class="fa fa-info-circle"></i><span>温馨提示</span></div>';
    html += '<div class="service-info-list">';
    html += '<div class="service-info-item" style="display:block;"><div class="service-info-value" style="color:#666;line-height:1.8;">';
    if (isCompleted) {
        html += '1. 您购买的人工发货商品已处理完成<br>';
        html += '2. 如有任何问题，请联系商家客服<br>';
        html += '3. 感谢您的支持与信任';
    } else {
        html += '1. 您购买的是人工发货商品，商家会尽快为您处理<br>';
        html += '2. 处理完成后，您可以在订单中查看发货信息<br>';
        html += '3. 如有疑问，请联系商家客服';
    }
    html += '</div></div>';
    html += '</div>';
    html += '</div>';
    
    // 联系卖家卡片
    html += '<div class="kami-contact-card">';
    html += '<div class="contact-title">遇到问题？</div>';
    html += '<a href="<?= DC_URL ?>?action=help" class="contact-btn">';
    html += '<i class="fa fa-commenting-o"></i> 联系卖家';
    html += '</a>';
    html += '</div>';
    
    $('#serviceContent').html(html);
}

// 加载卡密数据
function loadKamiData(orderNo, orderListId) {
    var cacheKey = orderNo + '_' + orderListId;
    $.ajax({
        type: "POST",
        url: "<?= DC_URL ?>user/order.php?action=get_order_serect",
        data: { out_trade_no: orderNo, order_list_id: orderListId, limit: 500 },
        dataType: "json",
        success: function(res) {
            if (res.code == 200) {
                kamiCache[cacheKey] = res.data;
                renderKamiPage(res.data);
            } else {
                $('#kamiContent').html('<div class="kami-loading">暂无卡密信息</div>');
            }
        },
        error: function() {
            $('#kamiContent').html('<div class="kami-loading">加载失败，请重试</div>');
        }
    });
}

// 渲染卡密页面
function renderKamiPage(data) {
    var list = data.list || [];
    var count = data.count || list.length;
    var payContent = data.pay_content || '';
    var html = '';
    
    // 卡密内容卡片
    html += '<div class="kami-content-card">';
    html += '<div class="kami-content-header">';
    html += '<div class="kami-info">';
    html += '<div class="kami-info-icon"><i class="fa fa-credit-card"></i></div>';
    html += '<div class="kami-info-text">您购买的卡密 <span>' + count + '</span> 张，已发货 <span>' + count + '</span> 张</div>';
    html += '</div>';
    html += '<div class="kami-header-actions">';
    html += '<a href="javascript:;" class="kami-header-btn btn-copy-all" onclick="copyAllKami()">一键复制</a>';
    html += '<a href="javascript:;" class="kami-header-btn btn-export" onclick="exportKami()">导出卡密</a>';
    html += '</div>';
    html += '</div>';
    
    // 卡密列表
    html += '<div class="kami-list">';
    if (list.length > 0) {
        for (var i = 0; i < list.length; i++) {
            var content = list[i].content || '';
            html += '<div class="kami-item">';
            html += '<div class="kami-item-header">';
            html += '<span class="kami-item-num">第' + (i + 1) + '张</span>';
            html += '</div>';
            html += '<div class="kami-item-content">';
            html += '<div class="kami-item-value">' + escapeHtml(content) + '</div>';
            html += '<button class="kami-item-copy" onclick="copySingleKami(' + i + ')">复制</button>';
            html += '</div>';
            html += '</div>';
        }
    } else {
        html += '<div class="kami-item"><div class="kami-item-value" style="text-align:center;color:#999;">暂无卡密数据</div></div>';
    }
    html += '</div>';
    html += '</div>';
    
    // 使用说明卡片 - 移到卡密下面，只有有内容时才显示
    if (payContent) {
        html += '<div class="kami-usage-card">';
        html += '<div class="kami-usage-header">';
        html += '<div class="kami-usage-icon"><i class="fa fa-file-text-o"></i></div>';
        html += '<div class="kami-usage-text">使用说明</div>';
        html += '</div>';
        html += '<div class="kami-pay-content">' + payContent + '</div>';
        html += '</div>';
    }
    
    // 联系卖家卡片
    html += '<div class="kami-contact-card">';
    html += '<div class="contact-title">遇到问题？</div>';
    html += '<a href="<?= DC_URL ?>?action=help" class="contact-btn">';
    html += '<i class="fa fa-commenting-o"></i> 联系卖家';
    html += '</a>';
    html += '</div>';
    
    $('#kamiContent').html(html);
}

// HTML转义
function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// 复制单个卡密
function copySingleKami(index) {
    var cacheKey = currentOrderNo + '_' + currentOrderListId;
    var data = kamiCache[cacheKey];
    if (!data || !data.list || !data.list[index]) {
        layer.msg('复制失败');
        return;
    }
    var content = data.list[index].content || '';
    copyToClipboard(content);
    layer.msg('复制成功');
}

// 一键复制所有卡密
function copyAllKami() {
    var cacheKey = currentOrderNo + '_' + currentOrderListId;
    var data = kamiCache[cacheKey];
    if (!data || !data.list || data.list.length == 0) {
        layer.msg('暂无卡密');
        return;
    }
    
    var text = '';
    for (var i = 0; i < data.list.length; i++) {
        text += (data.list[i].content || '') + '\n';
    }
    
    copyToClipboard(text.trim());
    layer.msg('复制成功');
}

// 导出卡密
function exportKami() {
    var cacheKey = currentOrderNo + '_' + currentOrderListId;
    var data = kamiCache[cacheKey];
    if (!data || !data.list || data.list.length == 0) {
        layer.msg('暂无卡密');
        return;
    }
    
    var text = '';
    for (var i = 0; i < data.list.length; i++) {
        text += (data.list[i].content || '') + '\n';
    }
    
    var blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = '卡密_' + currentOrderNo + '.txt';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    
    layer.msg('导出成功');
}

// 复制到剪贴板
function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text);
    } else {
        var textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
    }
}

// 自动打开详情页（支付成功跳转时 URL 带 show=kami 参数，支持卡密和人工发货）
$(function() {
    if (new URLSearchParams(window.location.search).get('show') === 'kami') {
        var $btn = $('.btn-view-kami, .btn-view-service').first();
        if ($btn.length) {
            $btn[0].click();
        }
    }
});
</script>
<?php doAction('tpl_footer'); ?>

