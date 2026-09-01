<?php defined('DC_ROOT') || exit('access denied!'); ?>
<?php
// 订单状态样式映射
$statusStyles = [
    '待支付' => ['bg' => '#fff7e6', 'color' => '#fa8c16', 'icon' => '<i class="ri-time-line"></i>'],
    '已支付' => ['bg' => '#e6f7ff', 'color' => '#1890ff', 'icon' => '<i class="ri-bank-card-line"></i>'],
    '已发货' => ['bg' => '#f6ffed', 'color' => '#52c41a', 'icon' => '<i class="ri-check-line"></i>'],
    '已完成' => ['bg' => '#f6ffed', 'color' => '#52c41a', 'icon' => '<i class="ri-check-double-line"></i>'],
    '已取消' => ['bg' => '#fff1f0', 'color' => '#ff4d4f', 'icon' => '<i class="ri-close-circle-line"></i>'],
    '已退款' => ['bg' => '#fff1f0', 'color' => '#ff4d4f', 'icon' => '<i class="ri-refund-line"></i>'],
];
$currentStatus = $statusStyles[$order['status']] ?? ['bg' => '#f5f5f5', 'color' => '#666', 'icon' => '<i class="ri-file-list-line"></i>'];
$retryDockingType = 'docking';
$retryGoodsId = (int)($order_list['goods_id'] ?? 0);
if ($retryGoodsId > 0) {
    $retryTableExists = function($table) use ($db) {
        $table = trim((string)$table);
        if ($table === '' || !preg_match('/^[a-zA-Z0-9_]+$/', $table)) return false;
        return (bool)$db->once_fetch_array("SHOW TABLES LIKE '" . $db->escape_string($table) . "'");
    };
    $retryQingjiu = $retryTableExists($db_prefix . 'qingjiu_goods') ? $db->once_fetch_array("select id from {$db_prefix}qingjiu_goods where goods_id={$retryGoodsId} limit 1") : false;
    $retryYiciyuan = $retryTableExists($db_prefix . 'yiciyuan_goods') ? $db->once_fetch_array("select id from {$db_prefix}yiciyuan_goods where goods_id={$retryGoodsId} limit 1") : false;
    if (!empty($order['qingjiu_err_msg']) || !empty($retryQingjiu)) {
        $retryDockingType = 'qingjiu';
    } elseif (!empty($order['yiciyuan_err_msg']) || !empty($retryYiciyuan)) {
        $retryDockingType = 'yiciyuan';
    }
}
?>
<style>
    .order-detail-container { padding: 20px; }
    
    /* 订单头部卡片 */
    .order-header-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 20px;
        color: #fff;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    .order-header-top {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }
    .order-icon {
        width: 50px;
        height: 50px;
        background: rgba(255,255,255,0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-right: 15px;
    }
    .order-header-info h3 {
        margin: 0 0 5px 0;
        font-size: 18px;
        font-weight: 600;
    }
    .order-header-info p {
        margin: 0;
        opacity: 0.9;
        font-size: 13px;
    }
    
    /* 状态统计卡片 */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 20px;
    }
    .stat-card {
        background: #fff;
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border: 1px solid #f0f0f0;
        transition: all 0.3s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .stat-card .stat-label {
        font-size: 12px;
        color: #999;
        margin-bottom: 8px;
    }
    .stat-card .stat-value {
        font-size: 16px;
        font-weight: 600;
        color: #333;
    }
    .stat-card.status-card .stat-value {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 13px;
    }
    
    /* 区块标题 */
    .section-title {
        display: flex;
        align-items: center;
        margin: 20px 0 12px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
    }
    .section-title .icon {
        width: 28px;
        height: 28px;
        background: linear-gradient(135deg, #16b777 0%, #12a56a 100%);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 14px;
        margin-right: 10px;
    }
    .section-title .text {
        font-size: 15px;
        font-weight: 600;
        color: #333;
    }
    .section-title .badge {
        margin-left: 8px;
        background: #f0f0f0;
        color: #666;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: normal;
    }
    
    /* 信息卡片 */
    .info-card {
        background: #fff;
        border-radius: 10px;
        padding: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        border: 1px solid #f0f0f0;
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    .info-item {
        display: flex;
        align-items: center;
        font-size: 13px;
    }
    .info-item .label {
        color: #999;
        min-width: 70px;
    }
    .info-item .value {
        color: #333;
        font-weight: 500;
    }
    .info-item .value.highlight {
        color: #1890ff;
    }
    
    /* 商品卡片 */
    .product-card {
        border: 1px solid #e8e8e8;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 12px;
        background: #fff;
        transition: all 0.3s;
    }
    .product-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .product-card.gift-card {
        border-color: #52c41a;
        background: linear-gradient(135deg, #f6ffed 0%, #fff 100%);
    }
    .product-header {
        display: flex;
        align-items: center;
        margin-bottom: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f0f0f0;
    }
    .gift-badge {
        background: linear-gradient(135deg, #52c41a 0%, #389e0d 100%);
        color: #fff;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        margin-right: 10px;
        box-shadow: 0 2px 6px rgba(82, 196, 26, 0.3);
    }
    .product-title {
        font-size: 15px;
        font-weight: 600;
        color: #333;
    }
    .product-info-row {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }
    .product-info-item {
        font-size: 13px;
        color: #666;
    }
    .product-info-item label {
        color: #999;
        margin-right: 5px;
    }
    
    /* 发货内容 */
    .deliver-box {
        margin-top: 14px;
        padding: 14px;
        background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
        border-radius: 8px;
        border: 1px dashed #d9d9d9;
    }
    .deliver-box-title {
        font-size: 13px;
        color: #333;
        margin-bottom: 10px;
        font-weight: 600;
        display: flex;
        align-items: center;
    }
    .deliver-box-title span {
        margin-right: 6px;
    }
    .deliver-content {
        font-size: 12px;
        color: #555;
        line-height: 1.8;
        word-break: break-all;
        background: #fff;
        padding: 10px;
        border-radius: 6px;
        border: 1px solid #eee;
    }
    .deliver-box.physical-deliver-box {
        background: linear-gradient(135deg, #f8fffb 0%, #ffffff 100%);
        border: 1px solid #bbf7d0;
    }
    .physical-deliver-box .deliver-content {
        background: transparent;
        border: 0;
        padding: 0;
    }
    .physical-order-detail {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .physical-action-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }
    .physical-copy-address-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        height: 30px;
        padding: 0 12px;
        border: 1px solid #86efac;
        border-radius: 999px;
        background: #f0fdf4;
        color: #15803d;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .physical-copy-address-btn:hover {
        background: #16a34a;
        border-color: #16a34a;
        color: #fff;
    }
    .physical-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        width: fit-content;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }
    .physical-status.is-waiting { background: #fff7e6; color: #d46b08; }
    .physical-status.is-shipped { background: #e6fffb; color: #08979c; }
    .physical-status.is-done { background: #f6ffed; color: #389e0d; }
    .physical-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }
    .physical-info-item {
        background: #fff;
        border: 1px solid #edf2f7;
        border-radius: 8px;
        padding: 10px 12px;
    }
    .physical-info-item.is-address {
        grid-column: 1 / -1;
    }
    .physical-label {
        display: block;
        color: #94a3b8;
        font-size: 12px;
        margin-bottom: 4px;
    }
    .physical-info-item strong,
    .physical-logistics-row strong {
        display: block;
        color: #1f2937;
        font-size: 13px;
        font-weight: 600;
        word-break: break-word;
    }
    .physical-logistics {
        border-radius: 10px;
        border: 1px solid #bae6fd;
        background: #f0f9ff;
        padding: 12px;
    }
    .physical-logistics-title {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #0369a1;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 8px;
    }
    .physical-logistics-row {
        display: grid;
        grid-template-columns: 72px minmax(0, 1fr);
        gap: 10px;
        padding: 6px 0;
        border-top: 1px dashed #bae6fd;
    }
    .physical-logistics-row span {
        color: #64748b;
        font-size: 12px;
    }
    .download-link {
        display: inline-flex;
        align-items: center;
        margin-top: 10px;
        font-size: 12px;
        color: #16baaa;
        text-decoration: none;
        padding: 6px 12px;
        background: #e6fffb;
        border-radius: 4px;
        transition: all 0.3s;
    }
    .download-link:hover {
        background: #16baaa;
        color: #fff;
    }
    
    /* 附加信息卡片 */
    .extra-info-card {
        background: #fffbe6;
        border: 1px solid #ffe58f;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 13px;
        color: #ad6800;
    }
    .required-info-card {
        background: #e6f7ff;
        border: 1px solid #91d5ff;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 13px;
        color: #0050b3;
    }
    
    /* 用户头像 */
    .user-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #1890ff 0%, #096dd9 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 16px;
        margin-right: 12px;
    }
    .user-info-content {
        display: flex;
        align-items: center;
    }
    .user-details .nickname {
        font-size: 14px;
        font-weight: 600;
        color: #333;
    }
    .user-details .email {
        font-size: 12px;
        color: #999;
        margin-top: 2px;
    }
    
    /* 深色模式适配 */
    html[data-theme="dark"] .order-detail-container,
    html.dark-mode .order-detail-container { background: #1a1a1a; }
    
    html[data-theme="dark"] .stat-card,
    html.dark-mode .stat-card { background: #252525; border-color: #333; }
    html[data-theme="dark"] .stat-card:hover,
    html.dark-mode .stat-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
    html[data-theme="dark"] .stat-card .stat-label,
    html.dark-mode .stat-card .stat-label { color: #888; }
    html[data-theme="dark"] .stat-card .stat-value,
    html.dark-mode .stat-card .stat-value { color: #e0e0e0; }
    
    html[data-theme="dark"] .section-title,
    html.dark-mode .section-title { border-bottom-color: #333; }
    html[data-theme="dark"] .section-title .text,
    html.dark-mode .section-title .text { color: #e0e0e0; }
    html[data-theme="dark"] .section-title .badge,
    html.dark-mode .section-title .badge { background: #333; color: #b0b0b0; }
    
    html[data-theme="dark"] .info-card,
    html.dark-mode .info-card { background: #252525; border-color: #333; }
    html[data-theme="dark"] .info-item .label,
    html.dark-mode .info-item .label { color: #888; }
    html[data-theme="dark"] .info-item .value,
    html.dark-mode .info-item .value { color: #e0e0e0; }
    
    html[data-theme="dark"] .product-card,
    html.dark-mode .product-card { background: #252525; border-color: #333; }
    html[data-theme="dark"] .product-card:hover,
    html.dark-mode .product-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
    html[data-theme="dark"] .product-card.gift-card,
    html.dark-mode .product-card.gift-card { background: linear-gradient(135deg, #1a2e1a 0%, #252525 100%); border-color: #3d6b3d; }
    html[data-theme="dark"] .product-header,
    html.dark-mode .product-header { border-bottom-color: #333; }
    html[data-theme="dark"] .product-title,
    html.dark-mode .product-title { color: #e0e0e0; }
    html[data-theme="dark"] .product-info-item,
    html.dark-mode .product-info-item { color: #b0b0b0; }
    html[data-theme="dark"] .product-info-item label,
    html.dark-mode .product-info-item label { color: #888; }
    
    html[data-theme="dark"] .deliver-box,
    html.dark-mode .deliver-box { background: linear-gradient(135deg, #2a2a2a 0%, #252525 100%); border-color: #444; }
    html[data-theme="dark"] .deliver-box-title,
    html.dark-mode .deliver-box-title { color: #e0e0e0; }
    html[data-theme="dark"] .deliver-content,
    html.dark-mode .deliver-content { background: #1e1e1e; border-color: #333; color: #b0b0b0; }
    html[data-theme="dark"] .deliver-box.physical-deliver-box,
    html.dark-mode .deliver-box.physical-deliver-box { background: linear-gradient(135deg, #10251b 0%, #1f2421 100%); border-color: #26543a; }
    html[data-theme="dark"] .physical-deliver-box .deliver-content,
    html.dark-mode .physical-deliver-box .deliver-content { background: transparent; border-color: transparent; }
    html[data-theme="dark"] .physical-copy-address-btn,
    html.dark-mode .physical-copy-address-btn { background: #13301f; border-color: #276749; color: #86efac; }
    html[data-theme="dark"] .physical-copy-address-btn:hover,
    html.dark-mode .physical-copy-address-btn:hover { background: #16a34a; border-color: #16a34a; color: #fff; }
    html[data-theme="dark"] .physical-info-item,
    html.dark-mode .physical-info-item { background: #1f2522; border-color: #33443a; }
    html[data-theme="dark"] .physical-label,
    html.dark-mode .physical-label,
    html[data-theme="dark"] .physical-logistics-row span,
    html.dark-mode .physical-logistics-row span { color: #93a29a; }
    html[data-theme="dark"] .physical-info-item strong,
    html.dark-mode .physical-info-item strong,
    html[data-theme="dark"] .physical-logistics-row strong,
    html.dark-mode .physical-logistics-row strong { color: #e5e7eb; }
    html[data-theme="dark"] .physical-logistics,
    html.dark-mode .physical-logistics { background: #112435; border-color: #1d4d6d; }
    html[data-theme="dark"] .physical-logistics-title,
    html.dark-mode .physical-logistics-title { color: #7dd3fc; }
    html[data-theme="dark"] .physical-logistics-row,
    html.dark-mode .physical-logistics-row { border-top-color: #1d4d6d; }
    html[data-theme="dark"] .download-link,
    html.dark-mode .download-link { background: #1a3a38; color: #16baaa; }
    html[data-theme="dark"] .download-link:hover,
    html.dark-mode .download-link:hover { background: #16baaa; color: #fff; }
    
    html[data-theme="dark"] .extra-info-card,
    html.dark-mode .extra-info-card { background: #2a2a1a; border-color: #5a5a2a; color: #d4a800; }
    html[data-theme="dark"] .required-info-card,
    html.dark-mode .required-info-card { background: #1a2a3a; border-color: #2a4a6a; color: #7dd3fc; }
    html[data-theme="dark"] .required-info-card div[style*="color:#666"],
    html.dark-mode .required-info-card div[style*="color:#666"] { color: #888 !important; }
    html[data-theme="dark"] .required-info-card div[style*="color:#0050b3"],
    html.dark-mode .required-info-card div[style*="color:#0050b3"] { color: #7dd3fc !important; }
    
    html[data-theme="dark"] .user-details .nickname,
    html.dark-mode .user-details .nickname { color: #e0e0e0; }
    html[data-theme="dark"] .user-details .email,
    html.dark-mode .user-details .email { color: #888; }
    
    /* 深色模式滚动条 */
    html[data-theme="dark"] #open-box::-webkit-scrollbar-track,
    html.dark-mode #open-box::-webkit-scrollbar-track { background: #333; }
    html[data-theme="dark"] #open-box::-webkit-scrollbar-thumb,
    html.dark-mode #open-box::-webkit-scrollbar-thumb { background: #555; }
    @media (max-width: 640px) {
        .physical-info-grid {
            grid-template-columns: 1fr;
        }
        .physical-action-row {
            align-items: flex-start;
        }
    }
</style>

<div class="order-detail-container" id="open-box">
    <!-- 订单头部卡片 -->
    <div class="order-header-card">
        <div class="order-header-top">
            <div class="order-icon"><i class="ri-shopping-cart-line"></i></div>
            <div class="order-header-info">
                <h3>商品订单</h3>
                <p>订单号：<?= $order['out_trade_no'] ?><?= $order['up_no'] ? ' | 商户订单：'.$order['up_no'] : '' ?></p>
            </div>
        </div>
        <div style="display:flex;gap:20px;margin-top:15px;padding-top:15px;border-top:1px solid rgba(255,255,255,0.2);flex-wrap:wrap;">
            <div style="font-size:13px;opacity:0.9;">
                <span style="opacity:0.7;">下单时间：</span><?= date('Y-m-d H:i:s', $order['create_time']) ?>
            </div>
            <div style="font-size:13px;opacity:0.9;">
                <span style="opacity:0.7;">支付时间：</span><?= empty($order['pay_time']) ? '未支付' : date('Y-m-d H:i:s', $order['pay_time']) ?>
            </div>
        </div>
    </div>
    
    <!-- ===== 对接失败错误横幅提示 ===== -->
    <?php if (!empty($order['docking_err_msg'])): ?>
    <div class="docking-error-banner" style="background: #fff2f0; border: 1px solid #ffccc7; border-radius: 10px; padding: 15px 20px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 12px; box-shadow: 0 2px 8px rgba(255, 77, 79, 0.05);">
        <div style="color: #ff4d4f; font-size: 20px; margin-top: 2px;"><i class="ri-error-warning-fill"></i></div>
        <div style="flex: 1;">
            <h4 style="font-weight: 700; color: #ff4d4f; font-size: 14px; margin: 0 0 4px 0;">自动对接下单失败</h4>
            <p style="font-size: 13px; color: #f5222d; margin: 0; line-height: 1.5;">失败原因：<strong style="color: #cf1322; background: #fff; padding: 2px 6px; border-radius: 4px; border: 1px solid #ffccc7; font-family: 'Outfit', sans-serif; margin-left: 4px;"><?= htmlspecialchars($order['docking_err_msg']) ?></strong></p>
            <p style="font-size: 12px; color: #8c8c8c; margin: 6px 0 0 0;">通常由于对接的主站账号余额不足、商品已下架或对接密钥失效所致。请您处理完余额或主站问题后，点击下方按钮一键重试自动对接下单与发货。</p>
            <div style="margin-top: 12px;">
                <button type="button" class="layui-btn layui-btn-xs layui-bg-red btn-retry-docking" data-id="<?= $order['id'] ?>" data-retry-type="<?= htmlspecialchars($retryDockingType) ?>" style="border-radius: 4px; padding: 0 12px; height: 28px; line-height: 28px; font-weight: 600;"><i class="ri-refresh-line" style="vertical-align: middle; margin-right: 4px;"></i>重新尝试自动对接发货</button>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- 状态统计卡片 -->
    <div class="stats-row">
        <div class="stat-card status-card">
            <div class="stat-label">订单状态</div>
            <div class="stat-value" style="background:<?= $currentStatus['bg'] ?>;color:<?= $currentStatus['color'] ?>;">
                <?= $currentStatus['icon'] ?> <?= $order['status'] ?>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-label">订单金额</div>
            <div class="stat-value" style="color:#ff4d4f;">¥<?= $order['amount'] ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">支付方式</div>
            <div class="stat-value"><?php
                // 支付插件名称映射
                $paymentNames = [
                    'balance' => '余额支付',
                    'epay_wx' => '易支付(微信)',
                    'epay_ali' => '易支付(支付宝)',
                    'epay_ali2' => '易支付(支付宝)',
                    'epay_qq' => '易支付(QQ钱包)',
                    'epay_jj' => '易支付(京东)',
                    'epay_jj2' => '易支付(京东)',
                    'alipay' => '支付宝',
                    'wxpay' => '微信支付',
                    'ynl_wx' => '微信支付',
                    'ynl_ali' => '支付宝',
                ];
                
                // 动态获取支付插件的显示名称
                $payPlugins = ['manual_pay', 'ynl_wx', 'ynl_ali', 'epay'];
                foreach ($payPlugins as $pluginName) {
                    $pluginStorage = Storage::getInstance($pluginName);
                    $displayName = $pluginStorage->getValue('display_name');
                    if (!empty($displayName)) {
                        $paymentNames[$pluginName] = $displayName;
                    }
                }
                
                if (!empty($order['payment'])) {
                    // 如果 payment 字段是插件名，也尝试转换
                    echo $paymentNames[$order['payment']] ?? $order['payment'];
                } elseif (!empty($order['pay_name'])) {
                    echo $order['pay_name'];
                } elseif (!empty($order['pay_plugin'])) {
                    echo $paymentNames[$order['pay_plugin']] ?? $order['pay_plugin'];
                } else {
                    echo '未支付';
                }
            ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">下单IP</div>
            <div class="stat-value" style="font-size:12px;"><?= $order['client_ip'] ?></div>
        </div>
    </div>
    
    <!-- 用户信息和下单必填项 -->
    <div class="section-title">
        <div class="icon"><i class="ri-user-line"></i></div>
        <span class="text">用户信息</span>
    </div>
    <div style="display:flex;gap:15px;flex-wrap:wrap;">
        <div class="info-card" style="flex:1;min-width:200px;">
            <div class="user-info-content">
                <div class="user-avatar"><?= mb_substr($user['nickname'] ?: '游', 0, 1) ?></div>
                <div class="user-details">
                    <div class="nickname"><?= $user['nickname'] ?: '游客用户' ?></div>
                    <div class="email"><?= $user['email'] ?: '未绑定邮箱' ?></div>
                </div>
            </div>
        </div>
        <div class="required-info-card" style="flex:1;min-width:200px;display:flex;align-items:center;">
            <div>
                <div style="font-size:12px;color:#666;margin-bottom:5px;"><i class="ri-edit-line"></i> 下单必填项</div>
                <div style="color:#0050b3;font-weight:500;"><?= $order_required ?></div>
            </div>
        </div>
    </div>
    
    <!-- 商品列表 -->
    <div class="section-title">
        <div class="icon"><i class="ri-box-3-line"></i></div>
        <span class="text">商品列表</span>
        <span class="badge">共 <?= count($all_order_lists) ?> 件</span>
    </div>
    <?php foreach ($all_order_lists as $index => $item): ?>
    <div class="product-card <?= $item['is_gift'] ? 'gift-card' : '' ?>">
        <div class="product-header">
            <?php if ($item['is_gift']): ?>
            <span class="gift-badge"><i class="ri-gift-line"></i> 赠品</span>
            <?php endif; ?>
            <span class="product-title"><?= $item['goods']['title'] ?></span>
        </div>
        <div class="product-info-row">
            <div class="product-info-item">
                <label>规格：</label><?= $item['attr_spec_display'] ?: '默认规格' ?>
            </div>
            <div class="product-info-item">
                <label>单价：</label>
                <?php if ($item['is_gift']): ?>
                <span style="color:#52c41a;font-weight:600;">免费赠送</span>
                <del style="color:#999;margin-left:5px;">¥<?= $item['unit_price'] ?></del>
                <?php else: ?>
                <span style="color:#ff4d4f;font-weight:600;">¥<?= $item['unit_price'] ?></span>
                <?php endif; ?>
            </div>
            <div class="product-info-item">
                <label>数量：</label><span style="font-weight:600;"><?= $item['quantity'] ?></span>
            </div>
        </div>
        <?php if (!empty($item['deliver_content']) && $item['deliver_content'] != '无'): ?>
        <div class="deliver-box <?= (($item['goods']['type'] ?? '') === 'physical') ? 'physical-deliver-box' : '' ?>">
            <div class="deliver-box-title"><span><i class="<?= (($item['goods']['type'] ?? '') === 'physical') ? 'ri-truck-line' : 'ri-file-text-line' ?>"></i></span> <?= (($item['goods']['type'] ?? '') === 'physical') ? '收货与物流' : '发货内容' ?></div>
            <div class="deliver-content"><?= $item['deliver_content'] ?></div>
            <?php if (($item['goods']['type'] ?? '') !== 'physical'): ?>
            <a href="order.php?action=download&goods_type=<?= $item['goods']['type'] ?>&order_list_id=<?= $item['id'] ?>" class="download-link" onclick="try{window.parent.open(this.href)}catch(e){window.open(this.href)};return false;"><i class="ri-download-line"></i> 下载全部卡密</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    
    <?php if ($order_list['attach_user'] != '无'): ?>
    <!-- 附加选项 -->
    <div class="section-title">
        <div class="icon"><i class="ri-settings-3-line"></i></div>
        <span class="text">附加选项</span>
    </div>
    <div class="extra-info-card">
        <?= $order_list['attach_user'] ?>
    </div>
    <?php endif; ?>
</div>



<script>
    // 同步父页面深色模式
    (function(){
        if(parent && parent.document.documentElement.getAttribute('data-theme') === 'dark'){
            document.documentElement.setAttribute('data-theme', 'dark');
            document.body.style.background = '#1a1a1a';
        }
    })();
    
    layui.use(['table'], function(){
        var $ = layui.$;
        var form = layui.form;
        form.on('submit(submit)', function(data){
            var field = data.field; // 获取表单全部字段值
            var url = $('#form').attr('action');
            $.ajax({
                type: "POST",
                url: url,
                data: field,
                dataType: "json",
                success: function (e) {
                    parent.layer.close('detail')
                    parent.layer.msg('订单信息已保存');
                    window.parent.table.reload();
                },
                error: function (xhr) {
                    layer.msg(JSON.parse(xhr.responseText).msg);
                }
            });
            return false; // 阻止默认 form 跳转
        });

        $(document).on('click', '.physical-copy-address-btn', function() {
            var text = $(this).attr('data-copy-info') || '';
            if (!text) {
                layer.msg('没有可复制的收货信息');
                return;
            }
            function done() {
                layer.msg('收货信息已复制', {icon: 1});
            }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(done).catch(function(){ fallbackCopy(text, done); });
            } else {
                fallbackCopy(text, done);
            }
        });

        function fallbackCopy(text, callback) {
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                if (typeof callback === 'function') callback();
            } catch (e) {
                layer.msg('复制失败，请手动复制');
            }
            document.body.removeChild(textarea);
        }

        // 重新尝试对接下单
        $(document).on('click', '.btn-retry-docking', function() {
            var btn = $(this);
            var id = btn.data('id');
            var retryType = btn.data('retry-type') || 'docking';
            var originalText = btn.html();
            
            btn.addClass('layui-btn-disabled').prop('disabled', true).html('<i class="layui-icon layui-icon-loading layui-anim layui-anim-rotate layui-anim-loop" style="display:inline-block;vertical-align:middle;margin-right:4px;"></i>对接下单中...');
            
            $.ajax({
                type: "POST",
                url: "order.php?action=retry_docking_ajax",
                data: { id: id, retry_type: retryType, token: "<?= LoginAuth::genToken() ?>" },
                dataType: "json",
                success: function(res) {
                    if (res.code != 0 && res.code != 200) {
                        layer.msg(res.msg || '重新对接下单失败', {icon: 2});
                        btn.removeClass('layui-btn-disabled').prop('disabled', false).html(originalText);
                        return;
                    }
                    var successMsg = (res.data && res.data.message) ? res.data.message : '已重新向同系统货源提交对接下单请求';
                    layer.msg(successMsg, {icon: 1});
                    setTimeout(function(){
                        location.reload();
                        if (window.parent && window.parent.table) {
                            window.parent.table.reload();
                        }
                    }, 800);
                },
                error: function(xhr) {
                    var errorMsg = '对接下单失败';
                    try {
                        var json = JSON.parse(xhr.responseText);
                        if (json && json.msg) errorMsg = json.msg;
                    } catch(e) {}
                    layer.msg('下单失败：' + errorMsg, {icon: 2});
                    btn.removeClass('layui-btn-disabled').prop('disabled', false).html(originalText);
                }
            });
        });
    })



    var maxHeight = $(window.parent).innerHeight() * 0.75;
    $("#open-box").css({
        "max-height": maxHeight + "px", // 单位必须加 px
        "overflow-y": "auto" // 内容超过 max-height 时显示垂直滚动条
    });
</script>
