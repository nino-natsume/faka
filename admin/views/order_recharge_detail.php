<?php defined('DC_ROOT') || exit('access denied!'); ?>
<style>
    body { background: #fff; }
    .rc-detail { max-width: 600px; margin: 0 auto; padding: 0px; }
    .rc-detail-card { background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,.06); overflow: hidden; }
    .rc-detail-status { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; }
    .rc-detail-status.is-paid { background: #f0fdf4; color: #16a34a; }
    .rc-detail-status.is-unpaid { background: #fef2f2; color: #dc2626; }
    .rc-detail-amount { padding: 24px; display: flex; align-items: center; justify-content: center; gap: 14px; border-bottom: 1px solid #f0f2f5; }
    .rc-detail-amount .amount-label { font-size: 13px; color: #9ca3af; }
    .rc-detail-amount .amount-value { font-size: 32px; font-weight: 700; color: #1f2937; }
    .rc-detail-amount .amount-value small { font-size: 18px; font-weight: 600; margin-right: 2px; }
    .rc-detail-rows { padding: 16px 24px; }
    .rc-detail-row { display: flex; align-items: flex-start; padding: 10px 0; border-bottom: 1px solid #f8f9fc; }
    .rc-detail-row:last-child { border-bottom: none; }
    .rc-detail-row .label { flex: 0 0 100px; color: #6b7280; font-size: 13px; }
    .rc-detail-row .value { flex: 1; color: #1f2937; font-size: 13px; font-weight: 500; word-break: break-all; }
</style>
<div class="rc-detail">
    <div class="rc-detail-card">
        <div class="rc-detail-amount">
            <div class="amount-value"><small>¥</small><?= $order['amount_yuan'] ?></div>
            <span class="rc-detail-status <?= $order['status'] == 2 ? 'is-paid' : 'is-unpaid' ?>">
                <i class="fa fa-<?= $order['status'] == 2 ? 'check-circle' : 'clock-o' ?>"></i>
                <?= htmlspecialchars($order['status_text']) ?>
            </span>
        </div>
        <div class="rc-detail-rows">
            <div class="rc-detail-row">
                <div class="label">订单号</div>
                <div class="value"><?= htmlspecialchars($order['out_trade_no']) ?></div>
            </div>
            <?php if (!empty($order['up_no']) && $order['up_no'] !== '无'): ?>
            <div class="rc-detail-row">
                <div class="label">平台单号</div>
                <div class="value"><?= htmlspecialchars($order['up_no']) ?></div>
            </div>
            <?php endif; ?>
            <div class="rc-detail-row">
                <div class="label">充值用户</div>
                <div class="value">
                    <?php if (!empty($user)): ?>
                        <?= htmlspecialchars($user['nickname'] ?: $user['username'] ?: '未设置') ?>
                        <?php if (!empty($user['email'])): ?>
                            <br><span style="color:#9ca3af;"><?= htmlspecialchars($user['email']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($user['tel'])): ?>
                            <br><span style="color:#9ca3af;"><?= htmlspecialchars($user['tel']) ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        用户ID: <?= intval($order['user_id']) ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="rc-detail-row">
                <div class="label">支付方式</div>
                <div class="value"><?= htmlspecialchars($order['payment'] ?: $order['pay_plugin'] ?: '未支付') ?></div>
            </div>
            <div class="rc-detail-row">
                <div class="label">下单IP</div>
                <div class="value"><?= htmlspecialchars($order['client_ip'] ?: '-') ?></div>
            </div>
            <div class="rc-detail-row">
                <div class="label">下单时间</div>
                <div class="value"><?= $order['create_time_text'] ?></div>
            </div>
            <div class="rc-detail-row">
                <div class="label">支付时间</div>
                <div class="value"><?= $order['pay_time_text'] ?></div>
            </div>
            <div class="rc-detail-row">
                <div class="label">过期时间</div>
                <div class="value"><?= $order['expire_time_text'] ?: '-' ?></div>
            </div>
        </div>
    </div>
</div>
