<?php
defined('DC_ROOT') || exit('access denied!');
$orderList = isset($list) && is_array($list) ? $list : [];
?>
<style>
    .mvorder-page { padding: 14px 14px calc(24px + env(safe-area-inset-bottom)); background: #f5f7fb; min-height: 100%; }
    .mvorder-summary { background: linear-gradient(180deg, #2f63d6 0%, #2b58c8 100%); color: #fff; border-radius: 18px; padding: 18px 16px; box-shadow: 0 12px 28px rgba(47, 99, 214, 0.18); }
    .mvorder-summary-title { font-size: 14px; opacity: 0.82; margin-bottom: 8px; }
    .mvorder-summary strong { display: block; font-size: 26px; font-weight: 700; }
    .mvorder-summary span { display: block; margin-top: 6px; font-size: 12px; opacity: 0.76; }
    .mvorder-list { display: flex; flex-direction: column; gap: 12px; margin-top: 14px; }
    .mvorder-empty, .mvorder-card { background: linear-gradient(0deg, #fff, #f3f5f8); border: 2px solid #fff; border-radius: 10px; box-shadow: var(--shadow-primary); }
    .mvorder-empty { padding: 60px 16px; text-align: center; color: #bbb; font-size: 13px; }
    .mvorder-empty svg { display: block; width: 140px; height: auto; margin: 0 auto 10px; }
    .mvorder-card { overflow: hidden; transition: transform 0.15s; }
    .mvorder-card:active { transform: scale(0.985); }
    .mvorder-card-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 14px 14px 10px; }
    .mvorder-card-no { font-size: 12px; color: #8f9aad; word-break: break-all; }
    .mvorder-card-status { display: inline-flex; align-items: center; justify-content: center; padding: 0 10px; height: 24px; border-radius: 999px; background: #fef3e8; color: #ff8a00; font-size: 12px; font-weight: 600; white-space: nowrap; }
    .mvorder-card-status.s-paid { background: #ecfdf5; color: #10b981; }
    .mvorder-card-status.s-shipped { background: #eff6ff; color: #2563eb; }
    .mvorder-card-status.s-unpaid, .mvorder-card-status.s-pending { background: #fef3e8; color: #ff8a00; }
    .mvorder-card-status.s-closed { background: #f3f4f6; color: #9ca3af; }
    .mvorder-card-status.s-refunding { background: #fef2f2; color: #ef4444; }
    .mvorder-card-status.s-gift { background: #ecfdf5; color: #10b981; }
    .mvorder-card-body { display: flex; gap: 12px; padding: 0 14px 12px; }
    .mvorder-cover { width: 72px; height: 72px; border-radius: 12px; object-fit: cover; background: #f3f6fb; flex: 0 0 auto; }
    .mvorder-info { flex: 1; min-width: 0; }
    .mvorder-title { font-size: 15px; color: #20293a; font-weight: 700; line-height: 1.45; margin-bottom: 6px; }
    .mvorder-title a { color: inherit; text-decoration: none; }
    .mvorder-meta { font-size: 12px; color: #7b8699; line-height: 1.7; }
    .mvorder-card-foot { padding: 12px 14px 14px; border-top: 1px solid #eef2f7; }
    .mvorder-line { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 10px; }
    .mvorder-time { font-size: 12px; color: #8f9aad; }
    .mvorder-amount { font-size: 15px; font-weight: 700; color: #f04452; }
    .mvorder-actions { display: flex; justify-content: flex-end; flex-wrap: wrap; gap: 8px; }
    .mvorder-actions .mvorder-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        min-height: 32px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid #dbe5f2;
        background: #fff;
        color: #3b82f6;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
    }
    .mvorder-actions .mvorder-btn:active { opacity: .82; }
    .mvorder-dialog-inner { padding: 14px; max-height: 72vh; overflow-y: auto; background: #f5f7fb; }
    .mvorder-dialog-loading { padding: 36px 12px; text-align: center; color: #8f9aad; }
    .mvorder-detail-status { border-radius: 14px; padding: 18px 14px; color: #fff; text-align: center; background: linear-gradient(135deg,#f59e0b,#fbbf24); margin-bottom: 12px; }
    .mvorder-detail-status.is-completed { background: linear-gradient(135deg,#10b981,#34d399); }
    .mvorder-detail-title { font-size: 16px; font-weight: 700; margin-top: 6px; }
    .mvorder-detail-desc { font-size: 12px; opacity: .86; margin-top: 4px; }
    .mvorder-detail-card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; box-shadow: 0 8px 20px rgba(15,23,42,.06); }
    .mvorder-detail-head { display: flex; align-items: center; gap: 6px; color: #1f2937; font-weight: 700; margin-bottom: 10px; }
    .mvorder-detail-item { display: flex; justify-content: space-between; gap: 12px; padding: 8px 0; border-top: 1px solid #f1f5f9; font-size: 13px; }
    .mvorder-detail-item:first-child { border-top: 0; }
    .mvorder-detail-label { flex: 0 0 auto; color: #8f9aad; }
    .mvorder-detail-value { color: #1f2937; text-align: right; word-break: break-all; }
    .mvorder-detail-value.is-highlight { color: #f04452; font-weight: 700; }
    .mvorder-detail-message { color: #475569; line-height: 1.8; word-break: break-all; white-space: pre-wrap; font-size: 13px; }
</style>
<div class='mvorder-page'>
    <div class='mvorder-summary'>
        <div class='mvorder-summary-title'>查询结果</div>
        <strong><?= (int)count($orderList) ?> 条</strong>
        <span>以下是符合当前查询条件的订单记录</span>
    </div>
    <?php if (empty($orderList)): ?>
        <div class='mvorder-list'>
            <div class='mvorder-empty'><?php include __DIR__ . '/_svg_empty.php'; ?>没有查找到任何订单</div>
        </div>
    <?php else: ?>
        <div class='mvorder-list'>
            <?php foreach ($orderList as $val): ?>
                <div class='mvorder-card'>
                    <div class='mvorder-card-top'>
                        <div class='mvorder-card-no'>订单编号：<?= htmlspecialchars($val['out_trade_no']) ?></div>
                        <?php
                        $_msCls = 's-closed';
                        if (!empty($val['is_refunding'])) $_msCls = 's-refunding';
                        elseif (!empty($val['is_gift'])) $_msCls = 's-gift';
                        elseif (isset($val['status'])) { if ($val['status'] == 2) $_msCls = 's-paid'; elseif ($val['status'] == 4) $_msCls = 's-shipped'; elseif ($val['status'] == 0) $_msCls = 's-unpaid'; elseif ($val['status'] == 1) $_msCls = 's-pending'; }
                        $orderListId = (int)($val['order_list_id'] ?? 0);
                        $statusInt = (int)($val['status'] ?? 0);
                        $goodsType = (string)($val['type'] ?? '');
                        $showOrderDetail = ($goodsType !== 'physical' && (($statusInt === 1 || $goodsType === 'service') && in_array($statusInt, [1, 2], true) && $orderListId > 0));
                        ?>
                        <div class='mvorder-card-status <?= $_msCls ?>'><?= htmlspecialchars($val['status_text']) ?></div>
                    </div>
                    <div class='mvorder-card-body'>
                        <a href='<?= htmlspecialchars($val['url']) ?>' target='_blank'>
                            <img class='mvorder-cover' src='<?= htmlspecialchars($val['cover']) ?>' alt='<?= htmlspecialchars($val['title']) ?>'>
                        </a>
                        <div class='mvorder-info'>
                            <div class='mvorder-title'><a target='_blank' href='<?= htmlspecialchars($val['url']) ?>'><?= htmlspecialchars($val['title']) ?></a></div>
                            <div class='mvorder-meta'><?= htmlspecialchars($val['attr_spec']) ?></div>
                            <div class='mvorder-meta'><?= htmlspecialchars($val['attach_user_text']) ?></div>
                        </div>
                    </div>
                    <div class='mvorder-card-foot'>
                        <div class='mvorder-line'>
                            <div class='mvorder-time'><?php if (!empty($val['pay_time'])): ?>付款时间：<?= htmlspecialchars($val['pay_time_text']) ?><?php else: ?>未支付<?php endif; ?></div>
                            <div class='mvorder-amount'>共<?= (int)$val['quantity'] ?>件商品 ￥<?= htmlspecialchars($val['amount']) ?></div>
                        </div>
                        <div class='mvorder-actions'>
                            <?php if ($showOrderDetail): ?>
                            <a href='javascript:;' class='mvorder-btn' onclick="showServicePage('<?= htmlspecialchars((string)$val['out_trade_no']) ?>', <?= $orderListId ?>, <?= $statusInt ?>)"><i class='fa fa-file-text-o'></i> 订单详情</a>
                            <?php endif; ?>
                            <?php if (in_array($statusInt, [1, 2, 4], true)): ?>
                            <?php doAction('user_order_list_btn', $val, $val); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<script>
var currentServiceOrderNo = '';
var currentServiceOrderListId = 0;
var currentServiceStatus = 0;

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>"']/g, function (s) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[s];
    });
}

function showServicePage(orderNo, orderListId, status) {
    currentServiceOrderNo = orderNo;
    currentServiceOrderListId = parseInt(orderListId, 10) || 0;
    currentServiceStatus = parseInt(status, 10) || 1;
    layer.open({
        type: 1,
        title: '<i class="fa fa-file-text-o" style="margin-right:6px;color:#60a5fa;"></i> 订单详情',
        area: ['94%', '78vh'],
        shadeClose: true,
        skin: 'vs-dialog',
        content: '<div class="mvorder-dialog-inner" id="mvorderServiceContent"><div class="mvorder-dialog-loading"><i class="fa fa-spinner fa-spin"></i> 加载中...</div></div>',
        success: function () {
            $.ajax({
                type: 'POST',
                url: '<?= DC_URL ?>user/api.php?action=get_service_detail',
                data: { out_trade_no: orderNo, order_list_id: currentServiceOrderListId },
                dataType: 'json',
                success: function (res) {
                    if (res.code == 200) {
                        renderServicePage(res.data || {});
                    } else {
                        $('#mvorderServiceContent').html('<div class="mvorder-dialog-loading">' + escapeHtml(res.msg || '加载失败') + '</div>');
                    }
                },
                error: function () {
                    $('#mvorderServiceContent').html('<div class="mvorder-dialog-loading">加载失败，请重试</div>');
                }
            });
        }
    });
}

function renderServicePage(data) {
    var status = data.status !== undefined ? parseInt(data.status, 10) : currentServiceStatus;
    var isCompleted = status === 2;
    var html = '';
    html += '<div class="mvorder-detail-status' + (isCompleted ? ' is-completed' : '') + '">';
    html += '<i class="fa ' + (isCompleted ? 'fa-check-circle' : 'fa-clock-o') + '"></i>';
    html += '<div class="mvorder-detail-title">' + (isCompleted ? '订单已完成' : '等待商家处理') + '</div>';
    html += '<div class="mvorder-detail-desc">' + (isCompleted ? '商家已处理完成，感谢您的购买' : '您的订单已支付成功，商家正在处理中') + '</div>';
    html += '</div>';

    html += '<div class="mvorder-detail-card">';
    html += '<div class="mvorder-detail-head"><i class="fa fa-file-text-o"></i><span>订单信息</span></div>';
    html += '<div class="mvorder-detail-item"><div class="mvorder-detail-label">订单编号</div><div class="mvorder-detail-value">' + escapeHtml(data.out_trade_no || currentServiceOrderNo) + '</div></div>';
    html += '<div class="mvorder-detail-item"><div class="mvorder-detail-label">商品名称</div><div class="mvorder-detail-value">' + escapeHtml(data.goods_title || '-') + '</div></div>';
    if (data.attr_spec) html += '<div class="mvorder-detail-item"><div class="mvorder-detail-label">商品规格</div><div class="mvorder-detail-value">' + escapeHtml(data.attr_spec) + '</div></div>';
    html += '<div class="mvorder-detail-item"><div class="mvorder-detail-label">购买数量</div><div class="mvorder-detail-value">' + escapeHtml(data.quantity || 1) + ' 件</div></div>';
    html += '<div class="mvorder-detail-item"><div class="mvorder-detail-label">订单金额</div><div class="mvorder-detail-value is-highlight">￥' + escapeHtml(data.amount || '0.00') + '</div></div>';
    html += '<div class="mvorder-detail-item"><div class="mvorder-detail-label">支付时间</div><div class="mvorder-detail-value">' + escapeHtml(data.pay_time || '-') + '</div></div>';
    html += '<div class="mvorder-detail-item"><div class="mvorder-detail-label">订单状态</div><div class="mvorder-detail-value is-highlight">' + (isCompleted ? '已完成' : '待发货') + '</div></div>';
    html += '</div>';

    if (isCompleted && data.deliver_content) {
        html += '<div class="mvorder-detail-card"><div class="mvorder-detail-head"><i class="fa fa-gift"></i><span>发货内容</span></div><div class="mvorder-detail-message">' + escapeHtml(data.deliver_content) + '</div></div>';
    }
    if (data.message) {
        html += '<div class="mvorder-detail-card"><div class="mvorder-detail-head"><i class="fa fa-commenting-o"></i><span>商家留言</span></div><div class="mvorder-detail-message">' + escapeHtml(data.message) + '</div></div>';
    }
    html += '<div class="mvorder-detail-card"><div class="mvorder-detail-head"><i class="fa fa-info-circle"></i><span>温馨提示</span></div><div class="mvorder-detail-message">' + (isCompleted ? '如有任何问题，请联系商家客服。' : '订单正在等待商家处理，处理完成后可在订单中查看后续信息。') + '</div></div>';
    $('#mvorderServiceContent').html(html);
}
</script>
