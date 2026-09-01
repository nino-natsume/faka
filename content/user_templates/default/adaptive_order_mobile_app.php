<?php
defined('DC_ROOT') || exit('access denied!');
$orderList = isset($list) && is_array($list) ? $list : [];

// 售后配置（与PC端完全一致）
$order_goods_img_switch = Option::get('order_goods_img_switch') ?: 'y';
$active_plugins = Option::get('active_plugins');
$aftersale_plugin_enabled = is_array($active_plugins) && in_array('aftersale/aftersale.php', $active_plugins);

// 查询售后中的 order_list_id 集合
$_refundingSet = [];
if ($aftersale_plugin_enabled && !empty($orderList)) {
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $_olIds = array_filter(array_column($orderList, 'order_list_id'));
    if (!empty($_olIds)) {
        $_olIdsStr = implode(',', array_map('intval', $_olIds));
        $_refRows = $db->fetch_all("SELECT DISTINCT order_list_id FROM {$db_prefix}aftersale WHERE order_list_id IN ({$_olIdsStr}) AND status IN (0, 1)");
        foreach ($_refRows as $_rr) { $_refundingSet[(int)$_rr['order_list_id']] = true; }
    }
}

if (!function_exists('__uorder_status_cls')) {
    function __uorder_status_cls($status) {
        $s = (int)$status;
        if ($s === 0) return 'unpaid';
        if ($s === 1) return 'pending';
        if ($s === 2) return 'paid';
        if ($s === 4) return 'shipped';
        return 'closed';
    }
}
if (!function_exists('__payment_cn')) {
    function __payment_cn($raw) {
        static $map = [
            'alipay'=>'支付宝','alipay2'=>'支付宝','wechat'=>'微信支付','wxpay'=>'微信支付','weixin'=>'微信支付',
            'wechatpay'=>'微信支付','qq'=>'QQ钱包','qqpay'=>'QQ钱包','balance'=>'余额支付',
            'bank'=>'银行卡','paypal'=>'PayPal','usdt'=>'USDT',
            'epay_wx'=>'微信支付','epay_ali'=>'支付宝','epay_ali2'=>'支付宝',
            'epay_qq'=>'QQ钱包','epay_jj'=>'京东支付','epay_jj2'=>'京东支付',
            'ynl_wx'=>'微信支付','ynl_ali'=>'支付宝','manual_pay'=>'手动转账',
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

$_initTab = isset($_GET['status']) ? $_GET['status'] : 'all';
?>
<style>
* { box-sizing: border-box; }
.uc-site-footer { display: none !important; }
.m-topbar-placeholder { display: none !important; }
.m-topbar-action { display:inline-flex; align-items:center; justify-content:center; width:40px; margin-right:6px; color:var(--theme-primary, #667eea); font-size:13px; font-weight:600; text-decoration:none; flex:0 0 auto; white-space:nowrap; }
.m-topbar-action:active { opacity:0.5; }
.mo-page {
    min-height: 100vh; background: #f5f7fb;
    padding: 14px 14px calc(24px + env(safe-area-inset-bottom, 0px));
    -webkit-tap-highlight-color: transparent; -webkit-font-smoothing: antialiased;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}
.mo-filter{display:flex; margin:0; padding:4px; background:linear-gradient(0deg, #fff, #f3f5f8); border:2px solid #fff; border-radius:10px; position:sticky; top:calc(50px + env(safe-area-inset-top, 0px) + 8px); z-index:15; box-shadow:var(--shadow-primary); overflow:hidden;}
.mo-filter-tab {
    flex:1; min-width:0; height:38px; border:0; background:transparent; border-radius:999px;
    font-size:12px; font-weight:600; color:#8b95a5; position:relative; transition:color 0.25s, background 0.25s; white-space:nowrap;
}
.mo-filter-tab.is-active { color:var(--theme-primary, #667eea); background:rgba(102,126,234,.08); font-weight:700; border-radius: 10px; }
.mo-filter-indicator { position:absolute; bottom:4px; left:0; height:3px; border-radius:3px; background:var(--theme-primary, #667eea); will-change:transform,width; transition:none; }
.mo-list { display:flex; flex-direction:column; gap:12px; background:transparent; margin-top:12px; }
.mo-empty { padding:60px 16px; text-align:center; color:#bbb; font-size:13px; background:linear-gradient(0deg, #fff, #f3f5f8); border:2px solid #fff; border-radius:10px; box-shadow:var(--shadow-primary); }
.mo-empty svg { display:block; width:140px; height:auto; margin:0 auto 10px; }
.mo-pager { display:none; margin-top:12px; padding-bottom:8px; }
.mo-pager.is-visible { display:block; }
.mo-pager-row { display:grid; grid-template-columns:72px minmax(0,1fr) 72px; gap:8px; align-items:center; }
.mo-page-btn { height:32px; border:0; border-radius:999px; background:#fff; color:var(--theme-primary, #667eea); font-size:12px; font-weight:900; display:flex; align-items:center; justify-content:center; gap:4px; box-shadow:0 4px 14px rgba(31,52,88,0.06); }
.mo-page-btn:disabled { background:#f8f9fb; color:#c0c7d2; box-shadow:none; }
.mo-page-current { height:32px; border-radius:999px; background:#fff; color:#20242c; font-size:12px; font-weight:900; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 14px rgba(31,52,88,0.06); }
.mo-card { padding:0; border:2px solid #fff; background:linear-gradient(0deg, #fff, #f3f5f8); border-radius:10px; box-shadow:var(--shadow-primary); overflow:hidden; transition:transform .15s, box-shadow .15s; }
.mo-card:active{transform:scale(.985); box-shadow:var(--shadow-primary);}
.mo-card.is-gift { background:linear-gradient(0deg, #fff, #f3f5f8); }
.mo-card-top { display:flex; align-items:center; justify-content:space-between; gap:10px; margin:0; padding:14px 14px 10px; }
.mo-card-no { font-size:12px; color:#8f9aad; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.mo-card-status { display:inline-flex; align-items:center; gap:3px; padding:0 10px; height:24px; border-radius:999px; font-size:11px; font-weight:700; white-space:nowrap; flex-shrink:0; }
.mo-card-status.s-unpaid, .mo-card-status.s-pending { background:#fef3e8; color:#ff8a00; }
.mo-card-status.s-shipped { background:#eff6ff; color:#2563eb; }
.mo-card-status.s-paid { background:#ecfdf5; color:#10b981; }
.mo-card-status.s-closed { background:#f3f4f6; color:#9ca3af; }
.mo-card-status.s-refunding { background:#fef2f2; color:#ef4444; }
.mo-card-status.s-gift { background:#ecfdf5; color:#10b981; }
.mo-card-body { display:flex; gap:12px; margin:0; padding:0 14px 12px; }
.mo-cover { width:72px; height:72px; border-radius:12px; object-fit:cover; background:#f3f6fb; flex:0 0 auto; }
.mo-cover-placeholder { width:72px; height:72px; border-radius:12px; background:linear-gradient(135deg,#f0f4ff,#e8ecf4); flex:0 0 auto; display:flex; align-items:center; justify-content:center; color:#c0c8d8; font-size:24px; }
.mo-info { flex:1; min-width:0; }
.mo-title { font-size:15px; color:#20293a; font-weight:700; line-height:1.45; margin-bottom:5px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.mo-title a { color:inherit; text-decoration:none; }
.mo-meta { font-size:12px; color:#7b8699; line-height:1.7; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.mo-card-foot { display:flex; align-items:center; justify-content:space-between; gap:10px; margin:0; padding:12px 14px 0; border-top:1px solid #eef2f7; }
.mo-time { font-size:11px; color:#8f9aad; }
.mo-amount { font-size:15px; font-weight:700; color:#f04452; white-space:nowrap; }
.mo-amount.is-gift { color:#10b981; font-size:13px; }
.mo-actions { display:flex; justify-content:flex-end; flex-wrap:wrap; gap:8px; margin:0; padding:10px 14px 14px; }
.mo-btn {
    display:inline-flex; align-items:center; gap:4px; padding:0 14px; height:32px; border-radius:16px;
    font-size:12px; font-weight:600; text-decoration:none; border:1px solid transparent; transition:opacity 0.15s; white-space:nowrap;
}
.mo-btn:active { opacity:0.7; }
.mo-btn.is-primary { background:var(--theme-primary, #667eea); color:#fff; }
.mo-btn.is-outline { background:#fff; color:var(--theme-primary, #667eea); border-color:var(--theme-primary, #667eea); }
.mo-btn.is-warn { background:linear-gradient(135deg,#f59e0b,#f97316); color:#fff; }
.mo-btn.is-danger-outline { background:#fff; color:#ef4444; border-color:#ef4444; }
.mo-btn.is-gray { background:#fff; color:#6b7280; border-color:#d1d5db; }
.mo-btn .countdown { font-weight:400; font-size:11px; opacity:.85; margin-left:2px; }
/* 底部弹出面板 (bottom sheet) */
body.mo-sheet-open { overflow:hidden; }
.mo-sheet-overlay {
    position:fixed; inset:0; z-index:3000;
    background:rgba(15,23,42,0.3); backdrop-filter:blur(4px); -webkit-backdrop-filter:blur(4px);
    display:none; align-items:flex-end; justify-content:center;
}
.mo-sheet-overlay.is-active { display:flex; }
.mo-sheet {
    width:100%; max-width:500px; max-height:92vh;
    display:flex; flex-direction:column;
    background:#fff; border-radius:20px 20px 0 0;
    box-shadow:0 -8px 40px rgba(0,0,0,0.12);
    transform:translateY(100%); transition:transform 0.28s cubic-bezier(.22,.61,.36,1);
    overflow:hidden;
}
.mo-sheet.is-up { transform:translateY(0); }
.mo-sheet-handle { display:flex; justify-content:center; padding:14px 0 6px; flex-shrink:0; cursor:grab; }
.mo-sheet-handle span { width:36px; height:4px; border-radius:4px; background:#e2e8f0; }
.mo-sheet-header {
    display:flex; align-items:center; gap:10px;
    padding:10px 18px 14px; border-bottom:1px solid #f1f5f9; flex-shrink:0;
}
.mo-sheet-icon {
    width:38px; height:38px; border-radius:12px;
    background:linear-gradient(135deg,var(--theme-primary, #667eea),var(--theme-secondary, #764ba2)); color:#fff;
    display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;
}
.mo-sheet-title { flex:1; font-size:17px; font-weight:800; color:#1f2937; }
.mo-sheet-close {
    width:32px; height:32px; border:none; border-radius:50%; background:#f1f5f9;
    color:#94a3b8; font-size:16px; display:flex; align-items:center; justify-content:center;
    cursor:pointer; flex-shrink:0; transition:all 0.15s;
}
.mo-sheet-close:active { background:#e2e8f0; color:#475569; }
.mo-sheet-body {
    flex:1 1 auto; overflow-y:auto; -webkit-overflow-scrolling:touch;
    padding:14px 16px; min-height:80px; background:#f8fafc;
}
.mo-sheet-body::-webkit-scrollbar { width:3px; }
.mo-sheet-body::-webkit-scrollbar-thumb { background:#e2e8f0; border-radius:3px; }
.mo-sheet-footer {
    padding:12px 18px calc(12px + env(safe-area-inset-bottom, 0px)); flex-shrink:0;
    border-top:1px solid #f1f5f9;
}
.mo-sheet-footer-btn {
    width:100%; height:46px; border:none; border-radius:14px;
    background:linear-gradient(135deg,var(--theme-primary, #667eea),var(--theme-secondary, #764ba2)); color:#fff;
    font-size:16px; font-weight:700; cursor:pointer; transition:opacity 0.15s;
}
.mo-sheet-footer-btn:active { opacity:0.85; }
.mo-sheet-loading { padding:60px 10px; text-align:center; color:#9ca3af; font-size:13px; }
.mo-sheet-loading i { margin-right:6px; }
/* 卡密/服务卡片 */
.kami-content-card,.kami-usage-card,.service-status-card,.service-info-card,.service-message-card,.kami-contact-card { background:linear-gradient(0deg, #fff, #f3f5f8); border:2px solid #fff; border-radius:10px; box-shadow:var(--shadow-primary); margin-bottom:12px; overflow:hidden; }
.kami-content-header { padding:12px 14px; border-bottom:1px solid #f3f4f6; background:color-mix(in srgb, var(--theme-primary, #667eea) 6%, white); display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap; }
.kami-info { display:inline-flex; align-items:center; gap:8px; }
.kami-info-icon { width:30px; height:30px; border-radius:50%; background:linear-gradient(135deg,var(--theme-primary, #667eea),var(--theme-secondary, #764ba2)); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:14px; }
.kami-info-text { font-size:13px; color:#1f2937; font-weight:500; }
.kami-info-text span { color:var(--theme-primary, #667eea); font-weight:700; margin:0 2px; }
.kami-header-actions { display:inline-flex; gap:6px; }
.kami-header-btn { padding:4px 12px; border-radius:999px; border:1px solid var(--theme-primary, #667eea); color:var(--theme-primary, #667eea); font-size:11px; text-decoration:none; }
.kami-list { padding:8px 14px 14px; }
.kami-item { padding:10px 0; border-bottom:1px dashed #f3f4f6; }
.kami-item:last-child { border-bottom:0; }
.kami-item-header { margin-bottom:4px; }
.kami-item-num { display:inline-block; padding:2px 8px; background:color-mix(in srgb, var(--theme-primary, #667eea) 8%, white); color:var(--theme-primary, #667eea); font-size:11px; border-radius:999px; font-weight:700; }
.kami-item-content { display:flex; align-items:center; gap:8px; }
.kami-item-value { flex:1; word-break:break-all; padding:8px 12px; background:#f9fafb; border-radius:8px; font-family:'Menlo',Consolas,monospace; font-size:12px; color:#1f2937; min-height:38px; line-height:1.7; }
.kami-item-copy { flex-shrink:0; padding:6px 14px; border-radius:999px; border:1px solid var(--theme-primary, #667eea); background:#fff; color:var(--theme-primary, #667eea); font-size:11px; font-weight:600; cursor:pointer; }
.kami-usage-header { padding:10px 14px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:8px; background:#f9fafb; }
.kami-usage-icon { width:26px; height:26px; border-radius:8px; background:#fff7ed; color:#f59e0b; display:inline-flex; align-items:center; justify-content:center; }
.kami-usage-text { font-size:13px; font-weight:700; color:#1f2937; }
.kami-pay-content { padding:12px 14px; font-size:12px; color:#4b5563; line-height:1.9; word-break:break-all; }
.kami-pay-content img { max-width:100%; height:auto; }
.kami-contact-card { padding:18px 14px; text-align:center; }
.kami-contact-card .contact-title { font-size:13px; color:#1f2937; margin-bottom:10px; font-weight:600; }
.kami-contact-card .contact-btn { display:inline-flex; align-items:center; gap:6px; padding:8px 18px; border-radius:999px; background:linear-gradient(135deg,var(--theme-primary, #667eea),var(--theme-secondary, #764ba2)); color:#fff; font-size:12px; font-weight:600; text-decoration:none; }
.service-status-card { padding:20px 16px; text-align:center; background:linear-gradient(135deg,var(--theme-primary, #667eea),var(--theme-secondary, #764ba2)); color:#fff; }
.service-status-icon { width:50px; height:50px; border-radius:50%; background:rgba(255,255,255,0.2); display:inline-flex; align-items:center; justify-content:center; font-size:24px; margin-bottom:10px; }
.service-status-text { font-size:16px; font-weight:700; margin-bottom:4px; }
.service-status-desc { font-size:12px; opacity:.85; }
.service-info-header,.service-message-header { padding:10px 14px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:8px; background:#f9fafb; color:#1f2937; font-size:13px; font-weight:700; }
.service-info-header i,.service-message-header i { color:var(--theme-primary, #667eea); }
.service-info-list { padding:8px 14px 14px; }
.service-info-item { display:flex; justify-content:space-between; gap:8px; padding:6px 0; border-bottom:1px dashed #f3f4f6; font-size:12px; }
.service-info-item:last-child { border-bottom:0; }
.service-info-label { color:#6b7280; flex-shrink:0; }
.service-info-value { color:#1f2937; text-align:right; word-break:break-all; }
.service-info-value.highlight { color:#ef4444; font-weight:700; }
.service-message-content { padding:12px 14px; font-size:12px; color:#4b5563; line-height:1.9; word-break:break-all; white-space:pre-wrap; }
.mo-app-modal-mask{position:fixed;inset:0;z-index:19999;background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:opacity .25s,visibility .25s}
.mo-app-modal-mask.is-show{opacity:1;visibility:visible}
.mo-app-modal{width:min(88vw,340px);background:#fff;border-radius:20px;overflow:hidden;transform:translateY(24px) scale(.96);transition:transform .28s cubic-bezier(.22,.61,.36,1);box-shadow:0 20px 50px rgba(0,0,0,.18)}
.mo-app-modal-mask.is-show .mo-app-modal{transform:translateY(0) scale(1)}
.mo-app-modal-header{padding:22px 22px 0;text-align:center}
.mo-app-modal-icon{width:52px;height:52px;margin:0 auto 12px;border-radius:50%;background:linear-gradient(135deg,var(--theme-primary,#667eea),var(--theme-secondary,#764ba2));color:#fff;display:flex;align-items:center;justify-content:center;font-size:24px}
.mo-app-modal-title{font-size:17px;font-weight:800;color:#252d3b}
.mo-app-modal-body{padding:16px 22px 6px;text-align:center}
.mo-app-modal-text{padding:2px 0 8px;color:#5f6673;font-size:13px;line-height:1.8}
.mo-app-modal-sub{display:block;margin-top:4px;color:#9ca3af;font-size:12px;line-height:1.7}
.mo-app-modal-foot{display:flex;gap:10px;padding:10px 22px 22px}
.mo-app-modal-btn{flex:1;height:44px;border:none;border-radius:14px;font-size:15px;font-weight:700;cursor:pointer;transition:.15s;font-family:inherit}
.mo-app-modal-btn:disabled{opacity:.65}
.mo-app-modal-cancel{background:#f3f4f6;color:#5f6673}
.mo-app-modal-cancel:active{background:#e8ebf0}
.mo-app-modal-confirm{background:linear-gradient(135deg,var(--theme-primary,#667eea),var(--theme-secondary,#764ba2));color:#fff;box-shadow:0 6px 16px rgba(var(--tp-rgb,102,126,234),.22)}
.mo-app-modal-confirm:active{transform:scale(.97)}
</style>

<script>!function(){var b=document.querySelector('.m-topbar');if(!b)return;var a=document.createElement('a');a.className='m-topbar-action';a.href='<?= DC_URL ?>?action=order_query';a.textContent='游客查单';b.appendChild(a);}();</script>
<div class='mo-page'>

    <div class='mo-filter'>
        <button type='button' class='mo-filter-tab<?= $_initTab === 'all' ? ' is-active' : '' ?>' data-filter='all'>全部</button>
        <button type='button' class='mo-filter-tab<?= $_initTab === 'unpaid' ? ' is-active' : '' ?>' data-filter='unpaid'>待付款</button>
        <button type='button' class='mo-filter-tab<?= $_initTab === 'pending' ? ' is-active' : '' ?>' data-filter='pending'>待发货</button>
        <button type='button' class='mo-filter-tab<?= $_initTab === 'shipped' ? ' is-active' : '' ?>' data-filter='shipped'>待收货</button>
        <button type='button' class='mo-filter-tab<?= $_initTab === 'completed' ? ' is-active' : '' ?>' data-filter='completed'>已完成</button>
        <?php if ($aftersale_plugin_enabled): ?>
        <button type='button' class='mo-filter-tab<?= $_initTab === 'refunding' ? ' is-active' : '' ?>' data-filter='refunding'>退款中</button>
        <?php endif; ?>
        <button type='button' class='mo-filter-tab<?= $_initTab === 'closed' ? ' is-active' : '' ?>' data-filter='closed'>已取消</button>
        <div class='mo-filter-indicator' id='moFilterIndicator'></div>
    </div>

    <div id='moListWrap'>
        <div id='moList' class='mo-list'>
<?php if (empty($orderList)): ?>
            <div class='mo-empty'><?php include __DIR__ . '/_svg_empty.php'; ?>暂无订单记录</div>
<?php else: ?>
<?php foreach ($orderList as $val):
    $statusCls = __uorder_status_cls($val['status'] ?? -1);
    $isGift = !empty($val['is_gift']);
    if (!$isGift && !empty($val['attr_spec']) && strpos((string)$val['attr_spec'], '[赠品]') !== false && (int)($val['item_price'] ?? $val['unit_price'] ?? 0) === 0) $isGift = true;
    $orderListId = (int)($val['order_list_id'] ?? 0);
    $goodsType = (string)($val['type'] ?? '');
    $statusInt = (int)($val['status'] ?? 0);
    $isRefunding = isset($_refundingSet[$orderListId]);
    $displayStatusCls = $isGift ? 's-gift' : ($isRefunding ? 's-refunding' : 's-' . $statusCls);
    $filterCls = $isRefunding ? 'refunding' : $statusCls;
    if ($filterCls === 'paid') $filterCls = 'completed';
    if ($isGift) $filterCls = 'completed';
    $showOrderDetail = (($statusInt === 1 || $goodsType === 'service') && in_array($statusInt, [1, 2], true) && $goodsType !== 'physical' && $orderListId > 0);
    $showKami = ($goodsType !== 'physical' && !$showOrderDetail && $statusInt === 2 && $orderListId > 0);
    $canAftersale = (!$isGift && in_array($statusInt, [1, 2, 4], true) && $aftersale_plugin_enabled && $orderListId > 0);
?>
            <div class='mo-card<?= $isGift ? ' is-gift' : '' ?>' data-order-id='<?= htmlspecialchars((string)$val['out_trade_no']) ?>' data-status='<?= $filterCls ?>'<?= $isRefunding ? " data-refunding='1'" : '' ?>>
                <div class='mo-card-top'>
                    <div class='mo-card-no'><?= htmlspecialchars((string)$val['out_trade_no']) ?></div>
                    <div class='mo-card-status <?= $displayStatusCls ?>'>
<?php if ($isGift): ?><i class='fa fa-gift'></i> 赠品<?php elseif ($isRefunding): ?><i class='fa fa-exclamation-circle'></i> 售后中<?php else: ?><?= htmlspecialchars((string)($val['status_text'] ?? '-')) ?><?php endif; ?>
                    </div>
                </div>
                <div class='mo-card-body'>
<?php if ($order_goods_img_switch == 'y'): ?>
<?php if (!empty($val['cover'])): ?>
                    <a href='<?= htmlspecialchars((string)$val['url']) ?>' target='_blank'><img class='mo-cover' src='<?= htmlspecialchars((string)$val['cover']) ?>' alt='<?= htmlspecialchars((string)$val['title']) ?>' loading='lazy' onerror="this.style.display='none';this.parentNode.insertAdjacentHTML('afterend','<div class=\'mo-cover-placeholder\'><i class=\'fa fa-image\'></i></div>');this.parentNode.remove();"></a>
<?php else: ?>
                    <a href='<?= htmlspecialchars((string)$val['url']) ?>' target='_blank'><div class='mo-cover-placeholder'><i class='fa fa-image'></i></div></a>
<?php endif; ?>
<?php endif; ?>
                    <div class='mo-info'>
                        <div class='mo-title'>
<?php if ($isGift): ?><span style='display:inline-block;background:linear-gradient(135deg,#10b981,#34d399);color:#fff;padding:1px 6px;border-radius:3px;font-size:10px;font-weight:700;margin-right:4px;vertical-align:middle;'>赠品</span><?php endif; ?>
                            <a target='_blank' href='<?= htmlspecialchars((string)$val['url']) ?>'><?= htmlspecialchars((string)$val['title']) ?></a>
                        </div>
<?php if (!empty($val['attr_spec'])): ?><div class='mo-meta'><?= str_replace('[赠品] ', '', (string)$val['attr_spec']) ?></div><?php endif; ?>
<?php if (!empty($val['attach_user_text'])): ?><div class='mo-meta'><?= $val['attach_user_text'] ?></div><?php endif; ?>
                    </div>
                </div>
                <div class='mo-card-foot'>
                    <div class='mo-time'><?= htmlspecialchars(__payment_cn($val['payment'] ?? '')) ?><?php if (!empty($val['pay_time'])): ?> · <?= htmlspecialchars((string)$val['pay_time_text']) ?><?php else: ?> · <?= date('Y-m-d H:i', (int)$val['create_time']) ?><?php endif; ?></div>
<?php if ($isGift): ?>
                    <div class='mo-amount is-gift'>免费赠送</div>
<?php else: ?>
                    <div class='mo-amount'>共<?= (int)$val['quantity'] ?>件 ¥<?= htmlspecialchars((string)$val['amount']) ?></div>
<?php endif; ?>
                </div>
                <div class='mo-actions'>
<?php if ($statusInt === 0): ?>
                    <a href='javascript:;' class='mo-btn is-gray' onclick="cancelOrder('<?= htmlspecialchars((string)$val['out_trade_no']) ?>', this)"><i class='fa fa-times-circle'></i> 取消</a>
<?php if ((Option::get('continue_pay_switch') ?: 'y') === 'y'): ?>
                    <a href='<?= DC_URL ?>?action=pay&out_trade_no=<?= urlencode((string)$val['out_trade_no']) ?>' class='mo-btn is-warn btn-continue-pay' data-create-time='<?= (int)$val['create_time'] ?>'><i class='fa fa-credit-card'></i> 付款 <span class='countdown'></span></a>
<?php endif; ?>
<?php else: ?>
<?php if ($showOrderDetail): ?>
                    <a href='javascript:;' class='mo-btn is-outline' onclick="showServicePage('<?= htmlspecialchars((string)$val['out_trade_no']) ?>', <?= $orderListId ?>, <?= $statusInt ?>)"><i class='fa fa-file-text-o'></i> 订单详情</a>
<?php elseif ($showKami): ?>
                    <a href='javascript:;' class='mo-btn is-outline' onclick="showKamiPage('<?= htmlspecialchars((string)$val['out_trade_no']) ?>', <?= $orderListId ?>)"><i class='fa fa-eye'></i> 查看卡密</a>
<?php endif; ?>
<?php if (in_array($statusInt, [1, 2, 4], true)): ?>
<?php doAction('order_result_item_buttons', $val); ?>
<?php endif; ?>
<?php if (!$canAftersale && !$isGift && !empty($val['url'])): ?>
                    <a href='<?= htmlspecialchars((string)$val['url']) ?>' class='mo-btn is-primary'><i class='fa fa-shopping-cart'></i> 再次购买</a>
<?php endif; ?>
<?php if ($statusInt === 3): ?>
                    <a href='javascript:;' class='mo-btn is-danger-outline' onclick="deleteOrder('<?= htmlspecialchars((string)$val['out_trade_no']) ?>', this)"><i class='fa fa-trash-o'></i> 删除</a>
<?php endif; ?>
<?php endif; ?>
                </div>
            </div>
<?php endforeach; ?>
<?php endif; ?>
        </div>
    </div>
    <nav class="mo-pager" id="moPager">
        <div class="mo-pager-row">
            <button type="button" class="mo-page-btn" id="moPrevPage"><i class="fa fa-angle-left"></i> 上一页</button>
            <div class="mo-page-current" id="moPageCurrent">1 / 1</div>
            <button type="button" class="mo-page-btn" id="moNextPage">下一页 <i class="fa fa-angle-right"></i></button>
        </div>
    </nav>
</div>

<template id="dcEmptyIllust"><?php include __DIR__ . '/_svg_empty.php'; ?></template>

<div class="mo-app-modal-mask" id="moConfirmModal">
    <div class="mo-app-modal">
        <div class="mo-app-modal-header">
            <div class="mo-app-modal-icon" id="moConfirmIcon"><i class="fa fa-question"></i></div>
            <div class="mo-app-modal-title" id="moConfirmTitle">确认操作</div>
        </div>
        <div class="mo-app-modal-body">
            <div class="mo-app-modal-text" id="moConfirmText">确定继续操作吗？</div>
        </div>
        <div class="mo-app-modal-foot">
            <button type="button" class="mo-app-modal-btn mo-app-modal-cancel" id="moConfirmCancel">取消</button>
            <button type="button" class="mo-app-modal-btn mo-app-modal-confirm" id="moConfirmOk">确认</button>
        </div>
    </div>
</div>

<script>
var currentKamiOrderNo = '';
var currentKamiOrderListId = 0;
var kamiCache = {};
var currentKamiList = [];
var currentServiceOrderNo = '';
var currentServiceOrderListId = 0;
var currentServiceStatus = 0;
var moEmptySvg = '';
var moConfirmCallback = null;

function showMoAppModal(selector) {
    $(selector).addClass('is-show');
}

function hideMoAppModal(selector) {
    $(selector).removeClass('is-show');
}

function openMoConfirm(options) {
    options = options || {};
    $('#moConfirmTitle').text(options.title || '确认操作');
    $('#moConfirmIcon').html('<i class="fa ' + (options.icon || 'fa-question') + '"></i>');
    $('#moConfirmText').html(options.html || escapeHtml(options.text || '确定继续操作吗？'));
    $('#moConfirmCancel').text(options.cancelText || '取消');
    $('#moConfirmOk').text(options.confirmText || '确认').prop('disabled', false);
    moConfirmCallback = typeof options.onConfirm === 'function' ? options.onConfirm : null;
    showMoAppModal('#moConfirmModal');
}

function closeMoConfirm() {
    hideMoAppModal('#moConfirmModal');
    moConfirmCallback = null;
}

/* ========== moSheet 底部弹出面板 ========== */
var moSheet = (function() {
    var overlay = null, sheet = null, bodyEl = null, footerEl = null, _swipeBound = false;

    function create() {
        if (overlay) return;
        overlay = document.createElement('div');
        overlay.className = 'mo-sheet-overlay';
        overlay.innerHTML = ''
            + '<div class="mo-sheet">'
            +   '<div class="mo-sheet-handle"><span></span></div>'
            +   '<div class="mo-sheet-header">'
            +     '<div class="mo-sheet-icon" id="moSheetIcon"><i class="fa fa-file-text-o"></i></div>'
            +     '<div class="mo-sheet-title" id="moSheetTitle"></div>'
            +     '<button type="button" class="mo-sheet-close" id="moSheetCloseBtn"><i class="fa fa-times"></i></button>'
            +   '</div>'
            +   '<div class="mo-sheet-body" id="moSheetBody"></div>'
            +   '<div class="mo-sheet-footer" id="moSheetFooter" style="display:none;">'
            +     '<button type="button" class="mo-sheet-footer-btn" id="moSheetActionBtn"></button>'
            +   '</div>'
            + '</div>';
        document.body.appendChild(overlay);
        sheet = overlay.querySelector('.mo-sheet');
        bodyEl = overlay.querySelector('#moSheetBody');
        footerEl = overlay.querySelector('#moSheetFooter');
        overlay.addEventListener('click', function(ev) { if (ev.target === overlay) close(); });
        overlay.querySelector('#moSheetCloseBtn').addEventListener('click', close);
    }

    function bindSwipeDismiss() {
        if (_swipeBound || !sheet) return;
        _swipeBound = true;
        var startY = 0, currentY = 0, dragging = false;
        sheet.addEventListener('touchstart', function(e) {
            var scrollable = e.target.closest('.mo-sheet-body');
            if (scrollable && scrollable.scrollTop > 0) return;
            startY = e.touches[0].clientY; currentY = 0; dragging = true;
            sheet.style.transition = 'none';
        }, { passive: true });
        sheet.addEventListener('touchmove', function(e) {
            if (!dragging) return;
            var dy = e.touches[0].clientY - startY;
            if (dy < 0) dy = 0;
            if (dy > 0) e.preventDefault();
            currentY = dy;
            sheet.style.transform = 'translateY(' + dy + 'px)';
        }, { passive: false });
        sheet.addEventListener('touchend', function() {
            if (!dragging) return;
            dragging = false;
            sheet.style.transition = 'transform 0.28s cubic-bezier(.22,.61,.36,1)';
            if (currentY > 80) {
                sheet.style.transform = 'translateY(100%)';
                setTimeout(close, 200);
            } else {
                sheet.style.transform = 'translateY(0)';
            }
        }, { passive: true });
    }

    function open(icon, title, bodyHtml, footerBtnText, footerBtnCallback, noPadding) {
        create();
        overlay.querySelector('#moSheetIcon').innerHTML = '<i class="fa ' + (icon || 'fa-file-text-o') + '"></i>';
        overlay.querySelector('#moSheetTitle').textContent = title || '';
        bodyEl.innerHTML = bodyHtml || '';
        if (noPadding) { bodyEl.style.padding = '0'; } else { bodyEl.style.padding = ''; }
        if (footerBtnText && footerBtnCallback) {
            footerEl.style.display = '';
            var actionBtn = overlay.querySelector('#moSheetActionBtn');
            actionBtn.textContent = footerBtnText;
            var newBtn = actionBtn.cloneNode(true);
            actionBtn.parentNode.replaceChild(newBtn, actionBtn);
            newBtn.addEventListener('click', footerBtnCallback);
        } else {
            footerEl.style.display = 'none';
        }
        document.body.classList.add('mo-sheet-open');
        overlay.classList.add('is-active');
        sheet.style.transform = '';
        requestAnimationFrame(function() { sheet.classList.add('is-up'); });
        bindSwipeDismiss();
        return close;
    }

    function close() {
        if (!overlay) return;
        sheet.style.transition = 'transform 0.28s cubic-bezier(.22,.61,.36,1)';
        sheet.style.transform = '';
        sheet.classList.remove('is-up');
        setTimeout(function() {
            overlay.classList.remove('is-active');
            document.body.classList.remove('mo-sheet-open');
        }, 260);
    }

    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') close(); });

    return { open: open, close: close };
})();

$(function(){
    var _emptySvg = (document.getElementById('dcEmptyIllust') || {}).innerHTML || '';
    moEmptySvg = _emptySvg;
    $('.pay-btn').click(function(){
        var key = $(this).data('key');
        $('#pay_plugin-' + key).val($(this).data('pay-plugin'));
        $('#pay_name-' + key).val($(this).data('pay-name'));
        $('#payment-' + key).val($(this).data('pay-name'));
        $('.go-pay-' + key).click();
    });

    $('#moConfirmCancel').on('click', closeMoConfirm);
    $('#moConfirmModal').on('click', function(e) {
        if (e.target === this) closeMoConfirm();
    });
    $('#moConfirmOk').on('click', function() {
        var callback = moConfirmCallback;
        closeMoConfirm();
        if (callback) callback();
    });

    // 待支付倒计时
    var PAY_TIMEOUT = <?= max(1, intval(Option::get('continue_pay_timeout') ?: 30)) ?> * 60;
    function fmtCountdown(sec) { if (sec <= 0) return '已超时'; var m = Math.floor(sec / 60), s = sec % 60; return (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s; }
    function _autoCancelExpired($card) {
        if ($card.data('auto-cancelled')) return;
        $card.data('auto-cancelled', true);
        var outTradeNo = $card.data('order-id');
        if (!outTradeNo) return;
        $.ajax({ url: '<?= DC_URL ?>user/api.php?action=cancel_order', type: 'POST', data: { out_trade_no: outTradeNo }, dataType: 'json',
            success: function(res) {
                if (res.code == 200) {
                    $card.attr('data-status', 'closed');
                    $card.find('.mo-card-status').removeClass('s-unpaid s-pending s-paid s-refunding s-gift').addClass('s-closed').html('已取消');
                    var buyUrl = $card.find('.mo-title a').attr('href') || '<?= DC_URL ?>';
                    $card.find('.mo-actions').html('<a href="' + buyUrl + '" class="mo-btn is-primary"><i class="fa fa-shopping-cart"></i> 再次购买</a><a href="javascript:;" class="mo-btn is-danger-outline" onclick="deleteOrder(\'' + outTradeNo + '\', this)"><i class="fa fa-trash-o"></i> 删除</a>');
                }
            }
        });
    }
    function tickCountdown() {
        var now = Math.floor(Date.now() / 1000);
        $('.btn-continue-pay').each(function(){
            var $btn = $(this), ct = parseInt($btn.data('create-time')) || 0, remain = PAY_TIMEOUT - (now - ct), $cd = $btn.find('.countdown');
            if (remain <= 0) { $cd.text('已超时'); $btn.css({ opacity: .55, pointerEvents: 'none' }); _autoCancelExpired($btn.closest('.mo-card')); }
            else { $cd.text(fmtCountdown(remain)); }
        });
    }
    tickCountdown(); setInterval(tickCountdown, 1000);

    // Tab Indicator
    var $indicator = $('#moFilterIndicator'), indicatorTimer = null;
    function moveIndicator($tab, animate) {
        if (!$tab.length) return;
        var tabLeft = $tab.position().left, tabWidth = $tab.outerWidth(), indicatorW = 24;
        var targetLeft = tabLeft + (tabWidth - indicatorW) / 2;
        if (!animate || !$indicator.data('inited')) { $indicator.css({ left: targetLeft + 'px', width: indicatorW + 'px', transition: 'none' }); $indicator.data('inited', true); return; }
        var curLeft = parseFloat($indicator.css('left')) || 0;
        var stretchLeft = targetLeft > curLeft ? curLeft : targetLeft;
        var stretchWidth = Math.abs(targetLeft - curLeft) + indicatorW;
        if (indicatorTimer) { clearTimeout(indicatorTimer); indicatorTimer = null; }
        $indicator.css({ left: stretchLeft + 'px', width: stretchWidth + 'px', transition: 'left 0.16s cubic-bezier(.4,0,.2,1), width 0.16s cubic-bezier(.4,0,.2,1)' });
        indicatorTimer = setTimeout(function() { $indicator.css({ left: targetLeft + 'px', width: indicatorW + 'px', transition: 'left 0.13s cubic-bezier(.4,0,.2,1), width 0.13s cubic-bezier(.4,0,.2,1)' }); }, 200);
    }
    moveIndicator($('.mo-filter-tab.is-active'), false);

    var currentFilter = '<?= $_initTab ?>';
    var moPagerState = { page: 1, limit: 10, total: 0 };
    function getFilteredCards() {
        if (currentFilter === 'all') return $('#moList .mo-card');
        if (currentFilter === 'refunding') return $('#moList .mo-card[data-refunding="1"]');
        return $('#moList .mo-card').filter(function(){ return $(this).attr('data-status') === currentFilter; });
    }
    function updatePager(total) {
        moPagerState.total = parseInt(total, 10) || 0;
        var totalPages = Math.max(1, Math.ceil(moPagerState.total / moPagerState.limit));
        if (moPagerState.page > totalPages) moPagerState.page = totalPages;
        $('#moPager').toggleClass('is-visible', moPagerState.total > moPagerState.limit);
        $('#moPrevPage').prop('disabled', moPagerState.page <= 1);
        $('#moNextPage').prop('disabled', moPagerState.page >= totalPages);
        $('#moPageCurrent').text(moPagerState.page + ' / ' + totalPages);
    }
    function applyFilter() {
        $('#moList .mo-empty').remove();
        var $matched = getFilteredCards();
        var total = $matched.length;
        var totalPages = Math.max(1, Math.ceil(total / moPagerState.limit));
        if (moPagerState.page > totalPages) moPagerState.page = totalPages;
        var start = (moPagerState.page - 1) * moPagerState.limit;
        var end = start + moPagerState.limit;
        $('#moList .mo-card').hide();
        $matched.slice(start, end).show();
        updatePager(total);
        if (!total) { $('#moList').append('<div class="mo-empty">' + _emptySvg + '暂无相关订单</div>'); }
    }
    window.moOrderApplyFilter = applyFilter;
    applyFilter();

    $(document).on('click', '.mo-filter-tab', function(){
        if ($(this).hasClass('is-active')) return;
        $('.mo-filter-tab').removeClass('is-active'); $(this).addClass('is-active');
        currentFilter = $(this).attr('data-filter');
        moPagerState.page = 1;
        moveIndicator($(this), true); applyFilter();
    });
    $('#moPrevPage').on('click', function(){
        if (moPagerState.page <= 1) return;
        moPagerState.page -= 1;
        applyFilter();
    });
    $('#moNextPage').on('click', function(){
        var totalPages = Math.max(1, Math.ceil((parseInt(moPagerState.total, 10) || 0) / moPagerState.limit));
        if (moPagerState.page >= totalPages) return;
        moPagerState.page += 1;
        applyFilter();
    });

    // Swipe
    var filterNames = []; $('.mo-filter-tab').each(function(){ filterNames.push($(this).attr('data-filter')); });
    var touchStartX = 0, touchStartY = 0, touchMoved = false, $listWrap = $('.mo-page');
    $listWrap.on('touchstart', function(e) { var t = e.originalEvent.touches[0]; touchStartX = t.clientX; touchStartY = t.clientY; touchMoved = false; });
    $listWrap.on('touchmove', function(e) { var t = e.originalEvent.touches[0]; if (Math.abs(t.clientX - touchStartX) > 20 && Math.abs(t.clientY - touchStartY) < 40) touchMoved = true; });
    $listWrap.on('touchend', function(e) {
        if (!touchMoved) return; var endX = e.originalEvent.changedTouches[0].clientX, diff = endX - touchStartX;
        if (Math.abs(diff) < 50) return;
        var idx = filterNames.indexOf(currentFilter); if (idx < 0) idx = 0;
        if (diff < 0 && idx < filterNames.length - 1) idx++; else if (diff > 0 && idx > 0) idx--; else return;
        $('.mo-filter-tab[data-filter="' + filterNames[idx] + '"]').click();
    });

    var hash = (window.location.hash || '').replace('#', '');
    if (hash) { var $t = $('.mo-filter-tab[data-filter="' + hash + '"]'); if ($t.length) $t.trigger('click'); }
});

function cancelOrder(outTradeNo, btnEl) {
    openMoConfirm({
        title: '取消订单',
        icon: 'fa-times-circle',
        text: '确定要取消该订单吗？',
        confirmText: '确定取消',
        cancelText: '再想想',
        onConfirm: function() {
            var li = layer.load(2);
            $.ajax({ url: '<?= DC_URL ?>user/api.php?action=cancel_order', type: 'POST', data: { out_trade_no: outTradeNo }, dataType: 'json',
                success: function(res) { layer.close(li);
                    if (res.code == 200) { layer.msg('订单已取消', { icon: 1, time: 1500 }); var $card = $(btnEl).closest('.mo-card'); $card.attr('data-status', 'closed'); $card.find('.mo-card-status').removeClass('s-unpaid s-pending s-paid s-refunding s-gift').addClass('s-closed').html('已取消'); var buyUrl = $card.find('.mo-title a').attr('href') || '<?= DC_URL ?>'; $card.find('.mo-actions').html('<a href="' + buyUrl + '" class="mo-btn is-primary"><i class="fa fa-shopping-cart"></i> 再次购买</a><a href="javascript:;" class="mo-btn is-danger-outline" onclick="deleteOrder(\'' + outTradeNo + '\', this)"><i class="fa fa-trash-o"></i> 删除</a>'); if (typeof window.moOrderApplyFilter === 'function') window.moOrderApplyFilter(); }
                    else { layer.msg(res.msg || '取消失败', { icon: 2 }); }
                }, error: function() { layer.close(li); layer.msg('请求失败', { icon: 2 }); }
            });
        }
    });
}

function deleteOrder(outTradeNo, btnEl) {
    openMoConfirm({
        title: '删除订单',
        icon: 'fa-trash-o',
        text: '确定要删除订单吗？',
        confirmText: '确定',
        cancelText: '取消',
        onConfirm: function() {
            var li = layer.load(2);
            $.ajax({ url: '<?= DC_URL ?>user/api.php?action=delete_order', type: 'POST', data: { out_trade_no: outTradeNo }, dataType: 'json',
                success: function(res) { layer.close(li);
                    if (res.code == 200) { layer.msg('订单已删除', { icon: 1, time: 1500 }); var $card = $(btnEl).closest('.mo-card'); $card.slideUp(300, function() { $card.remove(); if (typeof window.moOrderApplyFilter === 'function') window.moOrderApplyFilter(); }); }
                    else { layer.msg(res.msg || '删除失败', { icon: 2 }); }
                }, error: function() { layer.close(li); layer.msg('请求失败', { icon: 2 }); }
            });
        }
    });
}

function escapeHtml(str) { if (str === null || str === undefined) return ''; return String(str).replace(/[&<>"']/g, function(s) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]; }); }

function showKamiPage(orderNo, orderListId) {
    currentKamiOrderNo = orderNo; currentKamiOrderListId = orderListId || 0;
    var cacheKey = orderNo + '_' + orderListId;
    moSheet.open('fa-credit-card', '订单卡密', '<div class="mo-sheet-loading"><i class="fa fa-spinner fa-spin"></i> 加载中...</div>');
    if (kamiCache[cacheKey]) { renderKamiPage(kamiCache[cacheKey]); return; }
    $.ajax({ type: 'POST', url: '<?= DC_URL ?>user/order.php?action=get_order_serect', data: { out_trade_no: orderNo, order_list_id: orderListId, limit: 500 }, dataType: 'json',
        success: function(res) { if (res.code == 200) { kamiCache[cacheKey] = res.data; renderKamiPage(res.data); } else { $('#moSheetBody').html('<div class="mo-sheet-loading">' + (res.msg || '暂无卡密信息') + '</div>'); } },
        error: function() { $('#moSheetBody').html('<div class="mo-sheet-loading">加载失败，请重试</div>'); }
    });
}
function renderKamiPage(data) {
    var list = (data && data.list) || [], count = (data && data.count) || list.length, payContent = (data && data.pay_content) || '';
    currentKamiList = list;
    var html = '<div class="kami-content-card"><div class="kami-content-header"><div class="kami-info"><div class="kami-info-icon"><i class="fa fa-credit-card"></i></div><div class="kami-info-text">卡密 <span>' + count + '</span> 张</div></div><div class="kami-header-actions"><a href="javascript:;" class="kami-header-btn" onclick="copyAllKami()">一键复制</a><a href="javascript:;" class="kami-header-btn" onclick="exportKami()">导出</a></div></div><div class="kami-list">';
    if (list.length > 0) { for (var i = 0; i < list.length; i++) { html += '<div class="kami-item"><div class="kami-item-header"><span class="kami-item-num">第 ' + (i + 1) + ' 张</span></div><div class="kami-item-content"><div class="kami-item-value">' + escapeHtml(list[i].content || '') + '</div><button type="button" class="kami-item-copy" onclick="copySingleKami(' + i + ')">复制</button></div></div>'; } }
    else { html += '<div class="kami-item"><div class="kami-item-value" style="text-align:center;color:#9ca3af;">暂无卡密数据</div></div>'; }
    html += '</div></div>';
    if (payContent) { html += '<div class="kami-usage-card"><div class="kami-usage-header"><div class="kami-usage-icon"><i class="fa fa-file-text-o"></i></div><div class="kami-usage-text">使用说明</div></div><div class="kami-pay-content">' + payContent + '</div></div>'; }
    html += '<div class="kami-contact-card"><div class="contact-title">遇到问题？</div><a href="<?= DC_URL ?>?action=help" class="contact-btn"><i class="fa fa-commenting-o"></i> 联系卖家</a></div>';
    $('#moSheetBody').html(html);
}
function copyTextToClipboard(text) { if (navigator.clipboard && navigator.clipboard.writeText) return navigator.clipboard.writeText(text); var ta = document.createElement('textarea'); ta.value = text; ta.style.position = 'fixed'; ta.style.top = '-1000px'; document.body.appendChild(ta); ta.select(); try { document.execCommand('copy'); } catch(e) {} document.body.removeChild(ta); return Promise.resolve(); }
function copySingleKami(index) { if (!currentKamiList[index]) return; copyTextToClipboard(currentKamiList[index].content || '').then(function() { layer.msg('已复制', { icon: 1, time: 1200 }); }); }
function copyAllKami() { if (!currentKamiList || !currentKamiList.length) { layer.msg('暂无卡密可复制'); return; } copyTextToClipboard(currentKamiList.map(function(it) { return it.content || ''; }).join('\n')).then(function() { layer.msg('已复制全部卡密', { icon: 1, time: 1500 }); }); }
function exportKami() {
    if (!currentKamiList || !currentKamiList.length) { layer.msg('暂无卡密可导出'); return; }
    var text = currentKamiList.map(function(it) { return it.content || ''; }).join('\n');
    var blob = new Blob([text], { type: 'text/plain;charset=utf-8' }), url = URL.createObjectURL(blob);
    var a = document.createElement('a'); a.href = url; a.download = '卡密_' + currentKamiOrderNo + '.txt';
    document.body.appendChild(a); a.click(); document.body.removeChild(a); setTimeout(function() { URL.revokeObjectURL(url); }, 1000);
}

function showServicePage(orderNo, orderListId, status) {
    currentServiceOrderNo = orderNo; currentServiceOrderListId = parseInt(orderListId) || 0; currentServiceStatus = parseInt(status) || 1;
    moSheet.open('fa-file-text-o', '订单详情', '<div class="mo-sheet-loading"><i class="fa fa-spinner fa-spin"></i> 加载中...</div>');
    $.ajax({ type: 'POST', url: '<?= DC_URL ?>user/api.php?action=get_service_detail', data: { out_trade_no: orderNo, order_list_id: currentServiceOrderListId }, dataType: 'json',
        success: function(res) { if (res.code == 200) { renderServicePage(res.data); } else { $('#moSheetBody').html('<div class="mo-sheet-loading">' + (res.msg || '加载失败') + '</div>'); } },
        error: function() { $('#moSheetBody').html('<div class="mo-sheet-loading">加载失败，请重试</div>'); }
    });
}
function renderServicePage(data) {
    data = data || {}; var status = data.status !== undefined ? data.status : currentServiceStatus; var isCompleted = (parseInt(status) === 2); var html = '';
    if (isCompleted) { html += '<div class="service-status-card" style="background:linear-gradient(135deg,#10b981,#34d399);"><div class="service-status-icon"><i class="fa fa-check-circle"></i></div><div class="service-status-text">订单已完成</div><div class="service-status-desc">商家已处理完成</div></div>'; }
    else { html += '<div class="service-status-card"><div class="service-status-icon"><i class="fa fa-clock-o"></i></div><div class="service-status-text">等待商家处理</div><div class="service-status-desc">订单已支付，商家正在处理</div></div>'; }
    html += '<div class="service-info-card"><div class="service-info-header"><i class="fa fa-file-text-o"></i><span>订单信息</span></div><div class="service-info-list">';
    html += '<div class="service-info-item"><div class="service-info-label">订单编号</div><div class="service-info-value">' + escapeHtml(data.out_trade_no || currentServiceOrderNo) + '</div></div>';
    html += '<div class="service-info-item"><div class="service-info-label">商品名称</div><div class="service-info-value">' + escapeHtml(data.goods_title || '-') + '</div></div>';
    if (data.attr_spec) html += '<div class="service-info-item"><div class="service-info-label">规格</div><div class="service-info-value">' + escapeHtml(data.attr_spec) + '</div></div>';
    html += '<div class="service-info-item"><div class="service-info-label">数量</div><div class="service-info-value">' + (data.quantity || 1) + ' 件</div></div>';
    html += '<div class="service-info-item"><div class="service-info-label">金额</div><div class="service-info-value highlight">¥' + (data.amount || '0.00') + '</div></div>';
    html += '<div class="service-info-item"><div class="service-info-label">支付时间</div><div class="service-info-value">' + escapeHtml(data.pay_time || '-') + '</div></div>';
    html += '<div class="service-info-item"><div class="service-info-label">状态</div><div class="service-info-value" style="' + (isCompleted ? 'color:#10b981;' : 'color:#ef4444;') + '">' + (isCompleted ? '已完成' : '待发货') + '</div></div>';
    html += '</div></div>';
    if (isCompleted && data.deliver_content) html += '<div class="service-message-card"><div class="service-message-header"><i class="fa fa-gift"></i><span>发货内容</span></div><div class="service-message-content">' + escapeHtml(data.deliver_content) + '</div></div>';
    if (data.message) html += '<div class="service-message-card"><div class="service-message-header"><i class="fa fa-commenting-o"></i><span>商家留言</span></div><div class="service-message-content">' + escapeHtml(data.message) + '</div></div>';
    html += '<div class="kami-contact-card"><div class="contact-title">遇到问题？</div><a href="<?= DC_URL ?>?action=help" class="contact-btn"><i class="fa fa-commenting-o"></i> 联系卖家</a></div>';
    $('#moSheetBody').html(html);
}

</script>

<script>
$('#menu-order').addClass('menu-current');
</script>
<script>
(function() {
    var params = new URLSearchParams(window.location.search);
    var autoShow = params.get('auto_show');
    if (!autoShow) return;
    if (window.history && window.history.replaceState) { window.history.replaceState(null, '', window.location.pathname); }
    setTimeout(function() {
        var card = document.querySelector('.mo-card[data-order-id="' + autoShow + '"]');
        if (!card) return;
        var btn = card.querySelector('[onclick*="showKamiPage"]') || card.querySelector('[onclick*="showServicePage"]');
        if (btn) btn.click();
    }, 300);
})();
</script>
<?php doAction('tpl_footer'); ?>
