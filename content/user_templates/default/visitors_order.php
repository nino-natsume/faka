<?php
defined('DC_ROOT') || exit('access denied!');

$list = isset($list) && is_array($list) ? $list : [];
$orderCount = count($list);
$order_goods_img_switch = Option::get('order_goods_img_switch') ?: 'y';

// 检测售后服务插件是否启用
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
?>
<style>
    .vorder-page {
        display: flex;
        flex-direction: column;
        gap: 16px;
        padding: 8px 0 18px;
    }

    /* 结果头部 */
    .vorder-result-header {
        background: linear-gradient(0deg, #fff, #f3f5f8);
        border: 2px solid #fff;
        border-radius: 8px;
        padding: 18px 22px;
        box-shadow: 8px 8px 20px 0 rgba(55,99,170,.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }
    .vorder-result-title {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .vorder-result-title i { color: var(--theme-primary); }
    .vorder-result-count { font-size: 14px; color: #6b7280; }
    .vorder-result-count span { color: var(--theme-primary); font-weight: 700; margin: 0 2px; }
    .vorder-result-actions { display: inline-flex; gap: 8px; }
    .vorder-result-actions a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        color: #4b5563;
        font-size: 13px;
        text-decoration: none;
        transition: 0.2s;
    }
    .vorder-result-actions a:hover {
        color: var(--theme-primary);
        border-color: rgba(var(--tp-rgb),.3);
        background: rgba(var(--tp-rgb),.06);
    }

    /* 订单卡片 */
    .vorder-list { display: flex; flex-direction: column; gap: 14px; }
    .order-card {
        background-color:  #f3f5f8;
        border: 2px solid #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .order-card:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(var(--tp-rgb), 0.08); }

    /* 赠品卡片高亮 */
    .order-card.gift-card {
        background: linear-gradient(180deg, rgba(16,185,129,0.06) 0%, rgba(248,248,248,0.5) 40%);
        border-color: #d1fae5;
    }
    .order-card.gift-card .order-header-info { border-bottom-color: #d1fae5; }
    .gift-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: linear-gradient(135deg, #10b981, #34d399);
        color: #fff;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 700;
        margin-right: 6px;
        vertical-align: middle;
    }
    .original-price {
        color: #9ca3af;
        font-size: 12px;
        text-decoration: line-through;
        margin-left: 6px;
    }

    /* 订单头部 */
    .order-header-info {
        padding: 14px 20px;
        border-bottom: 1px solid #f2f2f2;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .order-no { color: #6b7280; font-size: 13px; }
    .order-no strong { color: #1f2937; font-weight: 700; margin-left: 4px; }
    .order-status {
        font-weight: 600;
        font-size: 13px;
        padding: 4px 12px;
        border-radius: 999px;
        background: #f3f4f6;
        color: #6b7280;
    }
    .order-status.paid { background: #ecfdf5; color: #10b981; }
    .order-status.pending, .order-status.unpaid { background: #fff7ed; color: #f59e0b; }
    .order-status.closed { background: #f3f4f6; color: #9ca3af; }
    .order-status.gift { background: linear-gradient(135deg,#10b981,#34d399); color: #fff; }
    .order-status.refunding { background: #fef2f2; color: #ef4444; }

    /* 订单商品 */
    .order-goods { padding: 15px 20px; }
    .goods-item { display: flex; }
    .goods-img {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        object-fit: cover;
        margin-right: 15px;
        background-color: #f5f5f5;
        flex-shrink: 0;
        border: 1px solid #eef2f7;
    }
    .goods-img-placeholder {
        width: 80px; height: 80px; border-radius: 8px; margin-right: 15px;
        background: #f3f4f6; border: 1px solid #eef2f7; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        color: #d1d5db; font-size: 28px;
    }
    .goods-info { flex: 1; min-width: 0; }
    .goods-name {
        font-size: 15px;
        color: #1f2937;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 8px;
        font-weight: 600;
    }
    .goods-name a { color: inherit; text-decoration: none; }
    .goods-name a:hover { color: var(--theme-primary); }
    .goods-spec { font-size: 13px; color: #9ca3af; margin-bottom: 5px; line-height: 1.7; }

    /* 订单金额 */
    .order-amount {
        padding: 12px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid #f2f2f2;
        background: #fafafa;
        flex-wrap: wrap;
        gap: 10px;
    }
    .pay-time { color: #9ca3af; font-size: 13px; }
    .amount-info { text-align: right; }
    .amount-text { color: #6b7280; font-size: 13px; }
    .amount-value { color: #ef4444; font-size: 18px; font-weight: 700; margin-left: 6px; }

    /* 订单操作按钮区 */
    .order-btn-group {
        padding: 12px 20px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        border-top: 1px solid #f2f2f2;
        flex-wrap: wrap;
        background: rgba(255,255,255,0.5);
    }
    .order-btn-group:empty { display: none; }

    /* 赠品卡片隐藏"再次购买"（赠品没价格）*/
    .order-card.gift-card .order-btn-group .layui-btn.layui-bg-cyan,
    .order-card.gift-card .order-btn-group .layui-btn.layui-btn-normal {
        display: none;
    }

    /* 插件 layui-btn 改造为胶囊按钮 */
    .order-btn-group .layui-btn,
    .order-btn-group a.layui-btn {
        height: auto;
        line-height: 1;
        padding: 10px 20px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .order-btn-group .layui-btn {
        background: linear-gradient(135deg, var(--theme-primary) 0%, var(--tp-light) 100%);
        color: #fff;
    }
    .order-btn-group .layui-btn:hover {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(var(--tp-rgb), 0.32);
    }
    .order-btn-group .layui-btn.layui-bg-cyan,
    .order-btn-group .layui-btn.layui-btn-primary,
    .order-btn-group .layui-btn.layui-btn-normal {
        background: #fff;
        color: var(--theme-primary);
        border: 1px solid var(--theme-primary);
    }
    .order-btn-group .layui-btn.layui-bg-cyan:hover,
    .order-btn-group .layui-btn.layui-btn-primary:hover,
    .order-btn-group .layui-btn.layui-btn-normal:hover {
        background: var(--theme-primary);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(255,152,0,0.22);
    }
    .order-btn-group .layui-btn i { font-size: 14px; }

    .order-btn-group .btn-cancel-order {
        padding: 10px 20px; border-radius: 20px; font-size: 14px; font-weight: 600;
        cursor: pointer; text-decoration: none; transition: all .2s ease;
        display: inline-flex; align-items: center; gap: 6px;
        border: 1px solid #e5e7eb; background: #fff; color: #6b7280; white-space: nowrap;
    }
    .order-btn-group .btn-cancel-order:hover {
        border-color: #ef4444; color: #ef4444; background: #fef2f2;
        transform: translateY(-1px);
    }
    .order-btn-group .btn-continue-pay {
        padding: 10px 20px; border-radius: 20px; font-size: 14px; font-weight: 600;
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

    /* 空状态 */
    .vorder-empty {
        background: rgba(248, 248, 248, 0.5);
        border: 2px solid #fff;
        border-radius: 10px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        padding: 70px 20px;
        text-align: center;
    }
    .vorder-empty-icon { font-size: 60px; color: #d1d5db; margin-bottom: 14px; }
    .vorder-empty-title { color: #1f2937; font-size: 18px; font-weight: 700; margin-bottom: 6px; }
    .vorder-empty-desc { color: #9ca3af; font-size: 14px; line-height: 1.7; margin-bottom: 18px; }
    .vorder-empty-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 22px;
        background: linear-gradient(135deg, var(--theme-primary), var(--tp-light));
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        border-radius: 8px;
        text-decoration: none;
        transition: 0.2s;
    }
    .vorder-empty-btn:hover { color: #fff; box-shadow: 0 10px 20px rgba(var(--tp-rgb), 0.25); }

    /* 移动端 */
    @media (max-width: 768px) {
        .vorder-result-header { padding: 14px 18px; flex-direction: column; align-items: flex-start; gap: 8px; }
        .vorder-result-actions { width: 100%; }
        .vorder-result-actions a { flex: 1; justify-content: center; }
        .order-header-info { padding: 12px 16px; }
        .order-goods { padding: 14px 16px; }
        .order-amount { padding: 10px 16px; flex-direction: column; align-items: flex-start; }
        .amount-info { text-align: left; }
        .order-btn-group { padding: 10px 16px; justify-content: stretch; }
        .goods-img { width: 70px; height: 70px; margin-right: 12px; }
    }

    /* ===================== 滑出页（卡密 / 虚拟服务） ===================== */
    body.vs-slide-open { overflow: hidden; }
    .vs-slide-page {
        position: fixed;
        inset: 0;
        background: #f5f7fa;
        z-index: 9998;
        display: none;
        overflow-y: auto;
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
    }
    .vs-slide-page.show {
        display: block;
        animation: vsSlideIn .22s ease-out;
    }
    @keyframes vsSlideIn {
        from { opacity: 0; transform: translateX(28px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    .vs-slide-header {
        position: sticky;
        top: 0;
        z-index: 5;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 20px;
        background: linear-gradient(135deg, var(--theme-primary), var(--tp-light));
        color: #fff;
        box-shadow: 0 2px 12px rgba(var(--tp-rgb),0.18);
    }
    .vs-slide-back {
        width: 36px; height: 36px;
        border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        background: rgba(255,255,255,0.18);
        color: #fff; text-decoration: none;
        transition: background .2s;
    }
    .vs-slide-back:hover { background: rgba(255,255,255,0.32); color: #fff; }
    .vs-slide-title { font-size: 16px; font-weight: 700; }
    .vs-slide-body {
        max-width: 880px;
        margin: 0 auto;
        padding: 18px;
    }
    .vs-slide-loading {
        padding: 80px 10px;
        text-align: center;
        color: #9ca3af;
        font-size: 14px;
    }
    .vs-slide-loading i { margin-right: 6px; }

    /* 卡密详情 */
    .kami-content-card,
    .kami-usage-card,
    .service-status-card,
    .service-info-card,
    .service-message-card,
    .kami-contact-card {
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        border-radius: 10px;
        box-shadow: 0 1px 18px #12345b0a;
        margin-bottom: 14px;
        overflow: hidden;
    }
    .kami-content-header {
        padding: 14px 18px;
        border-bottom: 1px solid #f3f4f6;
        background: linear-gradient(135deg, rgba(var(--tp-rgb), 0.06), rgba(var(--tp-rgb),0.04));
        display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap;
    }
    .kami-info { display: inline-flex; align-items: center; gap: 10px; }
    .kami-info-icon {
        width: 36px; height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--theme-primary), var(--tp-light));
        color: #fff;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 16px;
    }
    .kami-info-text { font-size: 14px; color: #1f2937; font-weight: 500; }
    .kami-info-text span { color: var(--theme-primary); font-weight: 700; margin: 0 2px; }
    .kami-header-actions { display: inline-flex; gap: 8px; }
    .kami-header-btn {
        padding: 6px 14px;
        border-radius: 999px;
        border: 1px solid var(--theme-primary);
        color: var(--theme-primary);
        font-size: 12px;
        text-decoration: none;
        transition: .2s;
    }
    .kami-header-btn:hover { background: var(--theme-primary); color: #fff; }

    .kami-list { padding: 10px 18px 18px; }
    .kami-item {
        padding: 12px 0;
        border-bottom: 1px dashed #f3f4f6;
    }
    .kami-item:last-child { border-bottom: 0; }
    .kami-item-header { margin-bottom: 6px; }
    .kami-item-num {
        display: inline-block;
        padding: 2px 10px;
        background: rgba(var(--tp-rgb),0.08);
        color: var(--theme-primary);
        font-size: 12px;
        border-radius: 999px;
        font-weight: 700;
    }
    .kami-item-content {
        display: flex; align-items: center; gap: 10px;
    }
    .kami-item-value {
        flex: 1;
        word-break: break-all;
        padding: 10px 14px;
        background: #f9fafb;
        border-radius: 8px;
        font-family: 'Menlo', Consolas, monospace;
        font-size: 13px;
        color: #1f2937;
        min-height: 44px;
        line-height: 1.7;
    }
    .kami-item-copy {
        flex-shrink: 0;
        padding: 8px 16px;
        border-radius: 999px;
        border: 1px solid var(--theme-primary);
        background: #fff;
        color: var(--theme-primary);
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: .2s;
    }
    .kami-item-copy:hover { background: var(--theme-primary); color: #fff; }

    .kami-usage-header {
        padding: 12px 18px;
        border-bottom: 1px solid #f3f4f6;
        display: flex; align-items: center; gap: 10px;
        background: #f9fafb;
    }
    .kami-usage-icon {
        width: 30px; height: 30px;
        border-radius: 8px;
        background: #fff7ed;
        color: #f59e0b;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .kami-usage-text { font-size: 14px; font-weight: 700; color: #1f2937; }
    .kami-pay-content { padding: 14px 18px; font-size: 13px; color: #4b5563; line-height: 1.9; word-break: break-all; }
    .kami-pay-content img { max-width: 100%; height: auto; }

    .kami-contact-card {
        padding: 22px 18px; text-align: center; background: #fff;
    }
    .kami-contact-card .contact-title { font-size: 14px; color: #1f2937; margin-bottom: 12px; font-weight: 600; }
    .kami-contact-card .contact-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 9px 20px; border-radius: 999px;
        background: linear-gradient(135deg, var(--theme-primary), var(--tp-light));
        color: #fff; font-size: 13px; font-weight: 600;
        text-decoration: none; transition: .2s;
    }
    .kami-contact-card .contact-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(var(--tp-rgb),0.25); color: #fff; }

    /* 虚拟服务详情 */
    .service-status-card {
        padding: 24px 20px;
        text-align: center;
        background: linear-gradient(135deg, var(--theme-primary), var(--tp-light));
        color: #fff;
    }
    .service-status-icon {
        width: 60px; height: 60px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 28px;
        margin-bottom: 12px;
    }
    .service-status-text { font-size: 18px; font-weight: 700; margin-bottom: 4px; }
    .service-status-desc { font-size: 13px; opacity: .85; }

    .service-info-header,
    .service-message-header {
        padding: 12px 18px;
        border-bottom: 1px solid #f3f4f6;
        display: flex; align-items: center; gap: 8px;
        background: #f9fafb;
        color: #1f2937; font-size: 14px; font-weight: 700;
    }
    .service-info-header i,
    .service-message-header i { color: var(--theme-primary); }
    .service-info-list { padding: 10px 18px 16px; }
    .service-info-item {
        display: flex; justify-content: space-between; gap: 10px;
        padding: 8px 0;
        border-bottom: 1px dashed #f3f4f6;
        font-size: 13px;
    }
    .service-info-item:last-child { border-bottom: 0; }
    .service-info-label { color: #6b7280; flex-shrink: 0; }
    .service-info-value { color: #1f2937; text-align: right; word-break: break-all; }
    .service-info-value.highlight { color: #ef4444; font-weight: 700; }
    .service-message-content {
        padding: 14px 18px;
        font-size: 13px;
        color: #4b5563;
        line-height: 1.9;
        word-break: break-all;
        white-space: pre-wrap;
    }

    /* ===================== Layer 弹窗皮肤（卡密 / 订单详情） ===================== */
    .layui-layer.layui-layer-vs-dialog {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 18px 60px rgba(15, 23, 42, 0.18);
    }
    .layui-layer.layui-layer-vs-dialog .layui-layer-title {
        background: linear-gradient(135deg, var(--theme-primary), var(--tp-light));
        color: #fff;
        border-bottom: 0;
        font-weight: 700;
        letter-spacing: .2px;
        padding-left: 20px;
    }
    .layui-layer.layui-layer-vs-dialog .layui-layer-setwin a.layui-layer-close1 {
        background-position: -260px 0;
        filter: brightness(0) invert(1);
        opacity: .85;
    }
    .layui-layer.layui-layer-vs-dialog .layui-layer-setwin a.layui-layer-close1:hover { opacity: 1; }
    .layui-layer.layui-layer-vs-dialog .layui-layer-content {
        padding: 0 !important;
        background: #f8fafc;
    }
    .vs-dialog-inner {
        padding: 16px;
        /* 滚动由外层 .layui-layer-content 接管，避免双滚动条 */
    }
    .vs-dialog-loading {
        padding: 80px 10px;
        text-align: center;
        color: #9ca3af;
        font-size: 14px;
    }
    .vs-dialog-loading i { margin-right: 6px; }
    /* 弹窗内卡片最后一张取消底部外边距，避免多余留白 */
    .vs-dialog-inner > div:last-child { margin-bottom: 0 !important; }

</style>

<main class="vorder-page">
    <!-- 结果头部 -->
    <div class="vorder-result-header">
        <div class="vorder-result-title">
            <i class="fa fa-search"></i> 订单查询结果
        </div>
        <div class="vorder-result-count">
            共找到 <span><?= $orderCount ?></span> 个订单
        </div>
        <div class="vorder-result-actions">
            <a href="/user/visitors.php"><i class="fa fa-search"></i> 重新查询</a>
        </div>
    </div>

    <?php if (empty($list)): ?>
        <div class="vorder-empty">
            <div class="vorder-empty-icon"><i class="fa fa-inbox"></i></div>
            <div class="vorder-empty-title">没有找到相关订单</div>
            <div class="vorder-empty-desc">请检查输入的信息是否正确后重新查询。</div>
            <a href="/user/visitors.php" class="vorder-empty-btn"><i class="fa fa-refresh"></i> 重新查询</a>
        </div>
    <?php else: ?>
        <div class="vorder-list">
            <?php foreach ($list as $val): ?>
                <?php
                // 赠品判定：与官方控制器保持一致 —— item_price == 0 且 attr_spec 含 [赠品]
                $isGift = !empty($val['is_gift']);
                if (!$isGift && !empty($val['attr_spec']) && strpos($val['attr_spec'], '[赠品]') !== false && (int)($val['item_price'] ?? $val['unit_price'] ?? 0) === 0) {
                    $isGift = true;
                }
                $unitPriceYuan = isset($val['unit_price']) ? number_format(((int)$val['unit_price']) / 100, 2) : '';
                $orderListId = (int)($val['order_list_id'] ?? 0);
                $isRefunding = !empty($val['is_refunding']);
                $statusCls = 'closed';
                if (isset($val['status'])) {
                    if ($val['status'] == 2) $statusCls = 'paid';
                    elseif ($val['status'] == 0) $statusCls = 'unpaid';
                    elseif ($val['status'] == 1) $statusCls = 'pending';
                }
                if ($isRefunding) $statusCls = 'refunding';
                if ($isGift) $statusCls = 'gift';
                ?>
                <div class="order-card<?= $isGift ? ' gift-card' : '' ?>" data-order-no="<?= htmlspecialchars((string)$val['out_trade_no']) ?>">
                    <div class="order-header-info">
                        <div class="order-no">订单编号：<strong><?= htmlspecialchars((string)$val['out_trade_no']) ?></strong></div>
                        <div class="order-status <?= $statusCls ?>">
                            <?php if ($isGift): ?>
                                <i class="fa fa-gift"></i> 赠品
                            <?php else: ?>
                                <?= htmlspecialchars((string)($val['status_text'] ?? '-')) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="order-goods">
                        <div class="goods-item">
                            <?php if ($order_goods_img_switch == 'y'): ?>
                                <?php if (!empty($val['cover'])): ?>
                                <a href="<?= htmlspecialchars((string)$val['url']) ?>" target="_blank">
                                    <img src="<?= htmlspecialchars((string)$val['cover']) ?>" class="goods-img" alt="<?= htmlspecialchars((string)$val['title']) ?>" onerror="this.style.display='none';this.parentNode.insertAdjacentHTML('afterbegin','<div class=\'goods-img-placeholder\'><i class=\'fa fa-image\'></i></div>');">
                                </a>
                                <?php else: ?>
                                <a href="<?= htmlspecialchars((string)$val['url']) ?>" target="_blank"><div class="goods-img-placeholder"><i class="fa fa-image"></i></div></a>
                                <?php endif; ?>
                            <?php endif; ?>
                            <div class="goods-info">
                                <div class="goods-name">
                                    <?php if ($isGift): ?>
                                        <span class="gift-badge"><i class="fa fa-gift"></i> 赠品</span>
                                    <?php endif; ?>
                                    <a href="<?= htmlspecialchars((string)$val['url']) ?>" target="_blank"><?= htmlspecialchars((string)$val['title']) ?></a>
                                </div>
                                <?php if (!empty($val['attr_spec'])): ?>
                                    <div class="goods-spec"><?= str_replace('[赠品] ', '', $val['attr_spec']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($val['attach_user_text'])): ?>
                                    <div class="goods-spec"><?= $val['attach_user_text'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="order-amount">
                        <div class="pay-time">
                            支付方式：<?= htmlspecialchars(__payment_cn($val['payment'] ?? '')) ?>
                            <span style="margin:0 6px;color:#d1d5db;">|</span>
                            <?php if (!empty($val['pay_time'])): ?>
                                付款时间：<?= htmlspecialchars((string)$val['pay_time_text']) ?>
                            <?php else: ?>
                                创建时间：<?= date('Y-m-d H:i:s', (int)$val['create_time']) ?>
                            <?php endif; ?>
                        </div>
                        <div class="amount-info">
                            <?php if ($isGift): ?>
                                <span class="amount-text">共 <?= (int)$val['quantity'] ?> 件商品</span>
                                <span class="amount-value" style="color:#10b981;">免费赠送</span>
                                <?php if ($unitPriceYuan !== '' && $unitPriceYuan !== '0.00'): ?>
                                    <span class="original-price">原价¥<?= $unitPriceYuan ?></span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="amount-text">共 <?= (int)$val['quantity'] ?> 件商品 合计：</span>
                                <span class="amount-value">¥<?= htmlspecialchars((string)$val['amount']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="order-btn-group">
                        <?php
                        // 注：不使用 doAction('user_order_list_btn')，商品插件会输出"查看卡密 + 再次购买"，与游客查单页的自定义弹窗按钮重复
                        $goodsType = (string)($val['type'] ?? '');
                        $statusInt = (int)($val['status'] ?? 0);
                        $isPhysicalOrder = ($goodsType === 'physical');
                        $showOrderDetail = (!$isPhysicalOrder && (($statusInt === 1 || $goodsType === 'service') && in_array($statusInt, [1, 2], true) && $orderListId > 0));
                        $showKami = (!$isPhysicalOrder && !$showOrderDetail && $statusInt === 2 && $orderListId > 0);
                        $canAftersale = (!$isGift && in_array($statusInt, [1, 2, 4], true) && $aftersale_plugin_enabled && $orderListId > 0);
                        ?>
                        <?php if ($statusInt === 0): ?>
                            <a href="javascript:;" class="btn-cancel-order" onclick="cancelOrder('<?= htmlspecialchars((string)$val['out_trade_no']) ?>', this)">
                                <i class="fa fa-times-circle"></i> 取消订单
                            </a>
                            <?php if ((Option::get('continue_pay_switch') ?: 'y') === 'y'): ?>
                            <a href="<?= DC_URL ?>?action=pay&out_trade_no=<?= urlencode((string)$val['out_trade_no']) ?>" class="btn-continue-pay" data-create-time="<?= (int)$val['create_time'] ?>">
                                <i class="fa fa-credit-card"></i> 继续付款 <span class="countdown"></span>
                            </a>
                            <?php endif; ?>
                        <?php else: ?>
                        <?php if ($showOrderDetail): ?>
                            <a href="javascript:;"
                               class="layui-btn layui-btn-primary"
                               onclick="showServicePage('<?= htmlspecialchars((string)$val['out_trade_no']) ?>', <?= $orderListId ?>, <?= $statusInt ?>)">
                                <i class="fa fa-file-text-o"></i> 订单详情
                            </a>
                        <?php elseif ($showKami): ?>
                            <a href="javascript:;"
                               class="layui-btn layui-btn-primary"
                               onclick="showKamiPage('<?= htmlspecialchars((string)$val['out_trade_no']) ?>', <?= $orderListId ?>)">
                                <i class="fa fa-eye"></i> 查看卡密
                            </a>
                        <?php endif; ?>

                        <?php if (!$isGift && in_array($statusInt, [1, 2, 4], true) && $orderListId > 0): ?>
                            <?php doAction('order_result_item_buttons', $val); ?>
                            <?php if (!$aftersale_plugin_enabled && !empty($val['url'])): ?>
                            <a href="<?= htmlspecialchars((string)$val['url']) ?>" class="layui-btn layui-btn-normal">
                                <i class="fa fa-shopping-cart"></i> 再次购买
                            </a>
                            <?php endif; ?>
                        <?php elseif (!$isGift && !empty($val['url'])): ?>
                            <a href="<?= htmlspecialchars((string)$val['url']) ?>" class="layui-btn layui-btn-normal">
                                <i class="fa fa-shopping-cart"></i> 再次购买
                            </a>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<script>
$(function () {
    // 兼容既有 pay-btn 钩子
    $('.pay-btn').click(function () {
        var key = $(this).data('key');
        $('#pay_plugin-' + key).val($(this).data('pay-plugin'));
        $('#pay_name-' + key).val($(this).data('pay-name'));
        $('#payment-' + key).val($(this).data('pay-name'));
        $('.go-pay-' + key).click();
    });

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
                    $card.find('.order-status').removeClass('unpaid pending paid refunding gift').addClass('closed').html('已取消');
                    $card.find('.order-btn-group').html(
                        '<a href="' + ($card.find('.goods-name a').attr('href') || '<?= DC_URL ?>') + '" class="layui-btn layui-btn-normal"><i class="fa fa-shopping-cart"></i> 再次购买</a>'
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
                    $card.find('.order-status').removeClass('unpaid pending paid refunding gift').addClass('closed').html('已取消');
                    $card.find('.order-btn-group').html(
                        '<a href="' + ($card.find('.goods-name a').attr('href') || '<?= DC_URL ?>') + '" class="layui-btn layui-btn-normal"><i class="fa fa-shopping-cart"></i> 再次购买</a>'
                    );
                } else {
                    layer.msg(res.msg || '取消失败', { icon: 2 });
                }
            },
            error: function () { layer.close(li); layer.msg('请求失败', { icon: 2 }); }
        });
    });
}

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>"']/g, function (s) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[s];
    });
}

// ========== 卡密滑出页 ==========
var currentKamiOrderNo = '';
var currentKamiOrderListId = 0;
var kamiCache = {};
var currentKamiList = [];

function showKamiPage(orderNo, orderListId) {
    currentKamiOrderNo = orderNo;
    currentKamiOrderListId = orderListId || 0;
    var cacheKey = orderNo + '_' + orderListId;
    var isMobile = window.innerWidth <= 768;
    var area = isMobile ? ['94%', '84vh'] : ['720px', '640px'];
    layer.open({
        type: 1,
        title: '<i class="fa fa-credit-card" style="margin-right:6px;color:#60a5fa;"></i> 订单卡密',
        area: area,
        maxmin: false,
        shadeClose: false,
        skin: 'vs-dialog',
        content: '<div class="vs-dialog-inner" id="vsKamiContent"><div class="vs-dialog-loading"><i class="fa fa-spinner fa-spin"></i> 加载中...</div></div>',
        success: function () {
            if (kamiCache[cacheKey]) {
                renderKamiPage(kamiCache[cacheKey]);
                return;
            }
            $.ajax({
                type: 'POST',
                url: '<?= DC_URL ?>user/order.php?action=get_order_serect',
                data: { out_trade_no: orderNo, order_list_id: orderListId, limit: 500 },
                dataType: 'json',
                success: function (res) {
                    if (res.code == 200) {
                        kamiCache[cacheKey] = res.data;
                        renderKamiPage(res.data);
                    } else {
                        $('#vsKamiContent').html('<div class="vs-dialog-loading">' + (res.msg || '暂无卡密信息') + '</div>');
                    }
                },
                error: function () {
                    $('#vsKamiContent').html('<div class="vs-dialog-loading">加载失败，请重试</div>');
                }
            });
        }
    });
}

function backFromKami() {
    layer.closeAll('page');
}

function renderKamiPage(data) {
    var list = (data && data.list) || [];
    var count = (data && data.count) || list.length;
    var payContent = (data && data.pay_content) || '';
    currentKamiList = list;
    var html = '';
    // 卡密内容卡
    html += '<div class="kami-content-card">';
    html += '<div class="kami-content-header">';
    html += '<div class="kami-info">';
    html += '<div class="kami-info-icon"><i class="fa fa-credit-card"></i></div>';
    html += '<div class="kami-info-text">您购买的卡密 <span>' + count + '</span> 张，已发货 <span>' + count + '</span> 张</div>';
    html += '</div>';
    html += '<div class="kami-header-actions">';
    html += '<a href="javascript:;" class="kami-header-btn" onclick="copyAllKami()">一键复制</a>';
    html += '<a href="javascript:;" class="kami-header-btn" onclick="exportKami()">导出卡密</a>';
    html += '</div>';
    html += '</div>';
    html += '<div class="kami-list">';
    if (list.length > 0) {
        for (var i = 0; i < list.length; i++) {
            var content = list[i].content || '';
            html += '<div class="kami-item">';
            html += '<div class="kami-item-header"><span class="kami-item-num">第 ' + (i + 1) + ' 张</span></div>';
            html += '<div class="kami-item-content">';
            html += '<div class="kami-item-value">' + escapeHtml(content) + '</div>';
            html += '<button type="button" class="kami-item-copy" onclick="copySingleKami(' + i + ')">复制</button>';
            html += '</div>';
            html += '</div>';
        }
    } else {
        html += '<div class="kami-item"><div class="kami-item-value" style="text-align:center;color:#9ca3af;">暂无卡密数据</div></div>';
    }
    html += '</div>';
    html += '</div>';
    // 使用说明卡
    if (payContent) {
        html += '<div class="kami-usage-card">';
        html += '<div class="kami-usage-header">';
        html += '<div class="kami-usage-icon"><i class="fa fa-file-text-o"></i></div>';
        html += '<div class="kami-usage-text">使用说明</div>';
        html += '</div>';
        html += '<div class="kami-pay-content">' + payContent + '</div>';
        html += '</div>';
    }
    // 联系卖家卡
    html += '<div class="kami-contact-card">';
    html += '<div class="contact-title">遇到问题？</div>';
    html += '<a href="<?= DC_URL ?>?action=help" class="contact-btn"><i class="fa fa-commenting-o"></i> 联系卖家</a>';
    html += '</div>';
    $('#vsKamiContent').html(html);
}

function copyTextToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        return navigator.clipboard.writeText(text);
    }
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed'; ta.style.top = '-1000px';
    document.body.appendChild(ta);
    ta.select();
    try { document.execCommand('copy'); } catch (e) {}
    document.body.removeChild(ta);
    return Promise.resolve();
}

function copySingleKami(index) {
    if (!currentKamiList[index]) return;
    copyTextToClipboard(currentKamiList[index].content || '').then(function () {
        layer.msg('已复制', { icon: 1, time: 1200 });
    });
}

function copyAllKami() {
    if (!currentKamiList || !currentKamiList.length) {
        layer.msg('暂无卡密可复制');
        return;
    }
    var text = currentKamiList.map(function (it) { return it.content || ''; }).join('\n');
    copyTextToClipboard(text).then(function () {
        layer.msg('已复制全部卡密', { icon: 1, time: 1500 });
    });
}

function exportKami() {
    if (!currentKamiList || !currentKamiList.length) {
        layer.msg('暂无卡密可导出');
        return;
    }
    var text = currentKamiList.map(function (it) { return it.content || ''; }).join('\n');
    var blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = '卡密_' + currentKamiOrderNo + '.txt';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
}

// ========== 虚拟服务详情滑出页 ==========
var currentServiceOrderNo = '';
var currentServiceOrderListId = 0;
var currentServiceStatus = 0;

function showServicePage(orderNo, orderListId, status) {
    currentServiceOrderNo = orderNo;
    currentServiceOrderListId = parseInt(orderListId) || 0;
    currentServiceStatus = parseInt(status) || 1;
    var isMobile = window.innerWidth <= 768;
    var area = isMobile ? ['94%', '84vh'] : ['720px', '640px'];
    layer.open({
        type: 1,
        title: '<i class="fa fa-file-text-o" style="margin-right:6px;color:#60a5fa;"></i> 订单详情',
        area: area,
        maxmin: false,
        shadeClose: false,
        skin: 'vs-dialog',
        content: '<div class="vs-dialog-inner" id="vsServiceContent"><div class="vs-dialog-loading"><i class="fa fa-spinner fa-spin"></i> 加载中...</div></div>',
        success: function () {
            $.ajax({
                type: 'POST',
                url: '<?= DC_URL ?>user/api.php?action=get_service_detail',
                data: { out_trade_no: orderNo, order_list_id: currentServiceOrderListId },
                dataType: 'json',
                success: function (res) {
                    if (res.code == 200) {
                        renderServicePage(res.data);
                    } else {
                        $('#vsServiceContent').html('<div class="vs-dialog-loading">' + (res.msg || '加载失败') + '</div>');
                    }
                },
                error: function () {
                    $('#vsServiceContent').html('<div class="vs-dialog-loading">加载失败，请重试</div>');
                }
            });
        }
    });
}

function backFromService() {
    layer.closeAll('page');
}

function renderServicePage(data) {
    data = data || {};
    var status = data.status !== undefined ? data.status : currentServiceStatus;
    var isCompleted = (parseInt(status) === 2);
    var html = '';
    // 状态卡
    if (isCompleted) {
        html += '<div class="service-status-card" style="background:linear-gradient(135deg,#10b981,#34d399);">';
        html += '<div class="service-status-icon"><i class="fa fa-check-circle"></i></div>';
        html += '<div class="service-status-text">订单已完成</div>';
        html += '<div class="service-status-desc">商家已处理完成，感谢您的购买</div>';
        html += '</div>';
    } else {
        html += '<div class="service-status-card">';
        html += '<div class="service-status-icon"><i class="fa fa-clock-o"></i></div>';
        html += '<div class="service-status-text">等待商家处理</div>';
        html += '<div class="service-status-desc">您的订单已支付成功，商家正在处理中</div>';
        html += '</div>';
    }
    // 订单信息
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
    html += '<div class="service-info-item"><div class="service-info-label">订单状态</div><div class="service-info-value ' + (isCompleted ? '' : 'highlight') + '" style="' + (isCompleted ? 'color:#10b981;' : '') + '">' + (isCompleted ? '已完成' : '待发货') + '</div></div>';
    html += '</div>';
    html += '</div>';
    // 发货内容
    if (isCompleted && data.deliver_content) {
        html += '<div class="service-message-card">';
        html += '<div class="service-message-header"><i class="fa fa-gift"></i><span>发货内容</span></div>';
        html += '<div class="service-message-content">' + escapeHtml(data.deliver_content) + '</div>';
        html += '</div>';
    }
    // 商家留言
    if (data.message) {
        html += '<div class="service-message-card">';
        html += '<div class="service-message-header"><i class="fa fa-commenting-o"></i><span>商家留言</span></div>';
        html += '<div class="service-message-content">' + escapeHtml(data.message) + '</div>';
        html += '</div>';
    }
    // 温馨提示
    html += '<div class="service-info-card">';
    html += '<div class="service-info-header"><i class="fa fa-info-circle"></i><span>温馨提示</span></div>';
    html += '<div class="service-info-list">';
    html += '<div class="service-info-item" style="display:block;"><div class="service-info-value" style="color:#6b7280;line-height:1.8;text-align:left;">';
    if (isCompleted) {
        html += '1. 您购买的人工发货商品已处理完成<br>';
        html += '2. 如有任何问题，请联系商家客服<br>';
        html += '3. 感谢您的支持与信任';
    } else {
        html += '1. 您购买的是人工发货商品，商家会尽快为您处理<br>';
        html += '2. 处理完成后，您可以在订单中查看发货信息<br>';
        html += '3. 如有疑问，请联系商家客服';
    }
    html += '</div></div></div></div>';
    // 联系卖家
    html += '<div class="kami-contact-card">';
    html += '<div class="contact-title">遇到问题？</div>';
    html += '<a href="<?= DC_URL ?>?action=help" class="contact-btn"><i class="fa fa-commenting-o"></i> 联系卖家</a>';
    html += '</div>';
    $('#vsServiceContent').html(html);
}

</script>

<script>
    $('#menu-visitors').addClass('menu-current');

</script>
<?php doAction('tpl_footer'); ?>
