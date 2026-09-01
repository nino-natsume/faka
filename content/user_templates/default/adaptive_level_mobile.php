<?php
defined('DC_ROOT') || exit('access denied!');

$levelStatus = isset($status) && is_array($status) ? $status : [];
$levelList = isset($levels) && is_array($levels) ? $levels : [];
$levelOrderList = isset($levelOrders) && is_array($levelOrders) ? $levelOrders : [];
$paidFlag = !empty($paid);
$walletBalance = isset($user['money']) ? (float)$user['money'] : 0;
?>
<style>
    .lvm-page {
        min-height: 100%;
        background: #f5f7fb;
        padding-bottom: calc(24px + env(safe-area-inset-bottom));
    }
    /* Hero */
    .lvm-hero {
        background: linear-gradient(180deg, var(--theme-primary, #667eea) 0%, var(--theme-secondary, #764ba2) 100%);
        color: #fff;
        padding: 20px 16px 38px;
        position: relative;
        overflow: hidden;
    }
    .lvm-hero::after {
        content: '';
        position: absolute;
        bottom: -1px; left: 0; right: 0;
        height: 24px;
        background: #f5f7fb;
        border-radius: 24px 24px 0 0;
    }
    .lvm-hero-label { font-size: 13px; opacity: .82; margin-bottom: 10px; }
    .lvm-hero-name { font-size: 28px; font-weight: 700; line-height: 1.1; letter-spacing: 0.5px; }
    .lvm-hero-meta { margin-top: 10px; font-size: 13px; opacity: .9; display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
    .lvm-hero-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 999px; background: rgba(255,255,255,.2); font-size: 12px; }
    .lvm-hero-badge.warn { background: rgba(255,204,0,.35); }
    .lvm-hero-badge.danger { background: rgba(255,90,90,.35); }
    .lvm-hero-wallet {
        margin-top: 12px; padding: 8px 12px; background: rgba(255,255,255,.15); border-radius: 10px;
        display: inline-flex; align-items: center; gap: 8px; font-size: 13px;
    }
    .lvm-hero-wallet .amt { font-size: 16px; font-weight: 700; }
    .lvm-hero-wallet a {
        color: #fff; text-decoration: none; padding: 2px 10px; background: rgba(255,255,255,.18);
        border-radius: 999px; font-size: 12px;
    }

    .lvm-hero-note {
        margin-top: 10px;
        font-size: 12px;
        line-height: 1.7;
        opacity: .88;
    }

    /* 顶部成功提示 */
    .lvm-paid-tip {
        margin: -20px 14px 0;
        padding: 10px 14px;
        border-radius: 10px;
        background: #f0fdf4;
        color: #166534;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 6px;
        position: relative;
        z-index: 2;
    }

    /* 等级列表 */
    .lvm-wrap { padding: 14px; }
    .lvm-section-title {
        font-size: 14px; font-weight: 700; color: #20293a; margin: 4px 2px 10px;
        display: flex; align-items: center; gap: 6px;
    }
    .lvm-section-title::before { content: ''; display: inline-block; width: 3px; height: 14px; background: var(--theme-primary, #667eea); border-radius: 2px; }

    .lvm-list { display: flex; flex-direction: column; gap: 10px; }
    .lvm-card{background: linear-gradient(0deg, #fff, #f3f5f8); border: 2px solid #fff; border-radius: 10px; box-shadow: var(--shadow-primary); padding: 14px; display: flex; flex-direction: column; gap: 8px; position: relative;}
    .lvm-card.is-current { background: linear-gradient(180deg, color-mix(in srgb, var(--theme-primary, #667eea) 8%, white) 0%, #fff 100%); border-color: color-mix(in srgb, var(--theme-primary, #667eea) 35%, white); }
    .lvm-card.is-current::before {
        content: '当前'; position: absolute; top: 10px; right: 10px;
        background: var(--theme-primary, #667eea); color: #fff; font-size: 10px; padding: 2px 8px; border-radius: 999px;
    }
    .lvm-card-top { display: flex; justify-content: space-between; align-items: center; gap: 10px; }
    .lvm-card-title { display: flex; align-items: center; gap: 9px; min-width: 0; }
    .lvm-card-icon { width: 34px; height: 34px; border-radius: 12px; background: color-mix(in srgb, var(--theme-primary, #667eea) 10%, white); color: var(--theme-primary, #667eea); display: inline-flex; align-items: center; justify-content: center; font-size: 19px; overflow: hidden; flex-shrink: 0; }
    .lvm-card-icon img { width: 100%; height: 100%; object-fit: cover; }
    .lvm-card-name { font-size: 16px; font-weight: 700; color: #20293a; }
    .lvm-card-price .sym { font-size: 13px; color: #6b7280; font-weight: 600; }
    .lvm-card-price .num { font-size: 22px; font-weight: 800; color: var(--theme-primary, #667eea); line-height: 1; }
    .lvm-card-price .unit { font-size: 11px; color: #9ca3af; margin-left: 4px; }
    .lvm-card-meta { display: flex; flex-wrap: wrap; gap: 6px; font-size: 12px; color: #6b7280; }
    .lvm-card-meta .tag { display: inline-flex; align-items: center; gap: 3px; padding: 2px 8px; background: #f3f4f6; border-radius: 999px; }
    .lvm-card-info { display: flex; flex-direction: column; gap: 6px; flex: 1; }
    .lvm-card-desc {
        font-size: 12px; color: #6b7280; line-height: 1.65; padding: 6px 10px;
        background: #f9fafb; border-radius: 6px; border-left: 3px solid #d1d5db;
    }
    .lvm-desc-upgrade { color: #92400e; }
    .lvm-card-desc-text + .lvm-desc-upgrade {
        margin-top: 8px; padding-top: 8px; border-top: 1px dashed #e5e7eb;
    }
    .lvm-card-upgrade {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1px solid #fde68a; border-radius: 6px; padding: 8px 10px;
        font-size: 11px; color: #92400e;
    }
    .lvm-card-upgrade-title {
        font-weight: 600; margin-bottom: 3px; display: flex; align-items: center; gap: 4px; font-size: 11px; color: #78350f;
    }
    .lvm-card-upgrade-title .fa { color: #f59e0b; }
    .lvm-upgrade-mode {
        font-weight: 400; font-size: 10px; background: #fef3c7; color: #b45309; padding: 1px 5px; border-radius: 3px;
    }
    .lvm-card-upgrade-item {
        display: flex; align-items: center; gap: 4px; padding: 1px 0; color: #a16207; font-size: 11px;
    }
    .lvm-card-upgrade-item .fa { width: 13px; text-align: center; color: #d97706; }
    .lvm-card-btn {
        margin-top: 4px; height: 40px; border: none; border-radius: 10px;
        color: #fff; font-size: 14px; font-weight: 700; cursor: pointer;
        background: linear-gradient(135deg, var(--theme-primary, #667eea), var(--theme-secondary, #764ba2));
    }
    .lvm-card-btn:disabled { background: #e5e7eb; color: #9ca3af; cursor: not-allowed; }
    .lvm-card-tag {
        margin-top: 4px; height: 40px; line-height: 38px; text-align: center;
        border-radius: 10px; font-size: 14px; font-weight: 700; letter-spacing: 1px;
        background: color-mix(in srgb, var(--theme-primary, #667eea) 8%, white); color: var(--theme-primary, #667eea); border: 1px solid color-mix(in srgb, var(--theme-primary, #667eea) 25%, white);
    }

    /* 历史记录 */
    .lvm-history{margin-top: 16px; background: linear-gradient(0deg, #fff, #f3f5f8); border: 2px solid #fff; border-radius: 10px; box-shadow: var(--shadow-primary); padding: 12px 14px;}
    .lvm-history-item {
        padding: 10px 0;
        border-bottom: 1px solid #f3f4f6;
        display: flex; justify-content: space-between; align-items: center;
    }
    .lvm-history-item:last-child { border-bottom: 0; }
    .lvm-history-left { font-size: 12px; color: #6b7280; }
    .lvm-history-left b { color: #20293a; display: block; font-size: 13px; margin-bottom: 4px; font-weight: 600; }
    .lvm-history-right { text-align: right; }
    .lvm-history-amount { font-size: 15px; font-weight: 700; color: var(--theme-primary, #667eea); }
    .lvm-history-state { font-size: 11px; padding: 2px 8px; border-radius: 999px; display: inline-block; margin-top: 3px; }
    .lvm-state-0 { background: #fef3c7; color: #92400e; }
    .lvm-state-1 { background: #d1fae5; color: #065f46; }
    .lvm-state--1 { background: #fee2e2; color: #991b1b; }
    .lvm-history-empty { text-align: center; color: #9ca3af; padding: 24px 0; font-size: 13px; }

</style>

<main class="lvm-page">
    <!-- Hero -->
    <section class="lvm-hero">
        <div class="lvm-hero-label"><i class="fa fa-vcard"></i> 我的等级</div>
        <div class="lvm-hero-name"><?= htmlspecialchars($levelStatus['level_name'] ?? '未开通等级') ?></div>
        <div class="lvm-hero-meta">
            <?php if (!empty($levelStatus['is_expired'])): ?>
                <span class="lvm-hero-badge danger"><i class="fa fa-exclamation-triangle"></i> 已过期</span>
            <?php elseif (!empty($levelStatus['is_permanent']) && intval($levelStatus['level_id']) > 0): ?>
                <span class="lvm-hero-badge"><i class="fa fa-infinity"></i> 永久有效</span>
            <?php elseif (empty($levelStatus['level_id'])): ?>
                <span class="lvm-hero-badge"><i class="fa fa-user"></i> 未开通</span>
            <?php else: ?>
                <span class="lvm-hero-badge"><i class="fa fa-clock-o"></i> 有效期至</span>
            <?php endif; ?>
            <?= htmlspecialchars($levelStatus['expire_text'] ?? '') ?>
        </div>
        <div class="lvm-hero-wallet">
            <i class="ri-wallet-3-line"></i>
            <span>钱包</span>
            <span class="amt">¥<?= number_format($walletBalance, 2) ?></span>
            <a href="/user/balance.php">充值</a>
        </div>
        <div class="lvm-hero-note">会员等级仅支持钱包余额开通、续费或升级，余额不足请先充值后再回来办理。</div>
    </section>

    <?php if ($paidFlag): ?>
    <div class="lvm-paid-tip">
        <i class="fa fa-check-circle"></i> 开通成功！等级已更新。
    </div>
    <?php endif; ?>

    <div class="lvm-wrap">
        <div class="lvm-section-title">可选等级</div>
        <div class="lvm-list">
            <?php foreach ($levelList as $lv):
                $isCurrent = !empty($lv['is_current']);
                $openPrice = floatval($lv['open_price']);
                $showPrice = floatval($lv['display_price'] ?? $openPrice);
                $payAmount = floatval($lv['pay_amount'] ?? $showPrice);
                $canPurchase = !empty($lv['can_purchase']);
                $purchaseLabel = !empty($lv['purchase_label']) ? $lv['purchase_label'] : '开通';
                $disabled = !$isCurrent && !$canPurchase;
                $duration = intval($lv['duration_days']);
                $_icon = !empty($lv['icon']) ? (string)$lv['icon'] : 'ri-vip-diamond-line';
                $_iconImg = !empty($lv['icon_image']) ? (string)$lv['icon_image'] : '';
            ?>
            <div class="lvm-card<?= $isCurrent ? ' is-current' : '' ?>" data-id="<?= intval($lv['id']) ?>" data-name="<?= htmlspecialchars($lv['name']) ?>" data-price="<?= htmlspecialchars((string)$payAmount) ?>" data-action-label="<?= htmlspecialchars($purchaseLabel) ?>" data-icon="<?= htmlspecialchars($_icon) ?>" data-icon-image="<?= htmlspecialchars($_iconImg) ?>">
                <div class="lvm-card-top">
                    <div class="lvm-card-title"><span class="lvm-card-icon"><?php if($_iconImg): ?><img src="<?= htmlspecialchars($_iconImg) ?>" alt=""><?php else: ?><i class="<?= htmlspecialchars($_icon) ?>"></i><?php endif; ?></span><div class="lvm-card-name"><?= htmlspecialchars($lv['name']) ?></div></div>
                    <div class="lvm-card-price">
                        <span class="sym">¥</span>
                        <span class="num"><?= number_format($showPrice, 2) ?></span>
                    </div>
                </div>
                <div class="lvm-card-meta">
                    <?php if ($duration > 0): ?>
                        <span class="tag"><i class="fa fa-clock-o"></i> <?= $duration ?>天</span>
                    <?php else: ?>
                        <span class="tag"><i class="fa fa-infinity"></i> 永久</span>
                    <?php endif; ?>
                    <?php
                        $upgDirect  = intval($lv['upgrade_direct_count'] ?? 0);
                        $upgConsume = floatval($lv['upgrade_consume_amount'] ?? 0);
                        $upgTeam    = intval($lv['upgrade_team_count'] ?? 0);
                        $hasUpgCond = ($upgDirect > 0 || $upgConsume > 0 || $upgTeam > 0);
                    ?>
                    <?php if ($hasUpgCond): ?>
                        <?php $upgMode = ($lv['upgrade_mode'] ?? 'any') === 'all' ? '全部满足' : '任一满足'; ?>
                        <span class="tag" title="<?= $upgMode ?>"><i class="fa fa-bolt"></i> 可免费升级</span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($lv['content']) || $hasUpgCond): ?>
                <div class="lvm-card-info">
                    <div class="lvm-card-desc">
                        <?php if (!empty($lv['content'])): ?>
                            <div class="lvm-card-desc-text"><?= nl2br(htmlspecialchars($lv['content'])) ?></div>
                        <?php endif; ?>
                        <?php if ($hasUpgCond): ?>
                        <div class="lvm-desc-upgrade">
                            <div class="lvm-card-upgrade-title"><i class="fa fa-bolt"></i> 达标免费升级 <span class="lvm-upgrade-mode"><?= $upgMode ?></span></div>
                            <?php if ($upgDirect > 0): ?>
                                <div class="lvm-card-upgrade-item"><i class="fa fa-user-plus"></i> 直推邀请 <?= $upgDirect ?> 人</div>
                            <?php endif; ?>
                            <?php if ($upgConsume > 0): ?>
                                <div class="lvm-card-upgrade-item"><i class="fa fa-shopping-cart"></i> 累计消费 ¥<?= number_format($upgConsume, 0) ?></div>
                            <?php endif; ?>
                            <?php if ($upgTeam > 0): ?>
                                <div class="lvm-card-upgrade-item"><i class="fa fa-users"></i> 团队人数 <?= $upgTeam ?> 人</div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($isCurrent): ?>
                    <div class="lvm-card-tag"><i class="fa fa-check-circle"></i> 当前等级</div>
                <?php elseif ($disabled): ?>
                    <button class="lvm-card-btn" disabled title="<?= htmlspecialchars($lv['purchase_disabled_reason'] ?? '') ?>"><?= htmlspecialchars($purchaseLabel) ?></button>
                <?php else: ?>
                    <button class="lvm-card-btn"><?= htmlspecialchars($purchaseLabel) ?></button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php if (empty($levelList)): ?>
                <div class="lvm-history-empty">暂无开放的会员等级</div>
            <?php endif; ?>
        </div>

        <!-- 开通记录 -->
        <div class="lvm-section-title" style="margin-top: 18px;">开通记录</div>
        <div class="lvm-history">
            <?php if (empty($levelOrderList)): ?>
                <div class="lvm-history-empty">暂无开通记录</div>
            <?php else: ?>
                <?php foreach ($levelOrderList as $row):
                    $typeMap = ['open' => '开通', 'renew' => '续费', 'upgrade' => '升级'];
                    $stateMap = [0 => '待支付', 1 => '已完成', -1 => '已取消'];
                    $typeText = $typeMap[$row['purchase_type']] ?? $row['purchase_type'];
                    $stateText = $stateMap[intval($row['state'])] ?? '未知';
                ?>
                <div class="lvm-history-item">
                    <div class="lvm-history-left">
                        <b><?= $typeText ?>（ID:<?= intval($row['level_id']) ?>）</b>
                        <?= !empty($row['create_time']) ? date('Y-m-d H:i', $row['create_time']) : '--' ?>
                    </div>
                    <div class="lvm-history-right">
                        <div class="lvm-history-amount">¥<?= number_format(floatval($row['amount']), 2) ?></div>
                        <span class="lvm-history-state lvm-state-<?= intval($row['state']) ?>"><?= $stateText ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
layui.use(['layer', 'jquery'], function () {
    var $ = layui.$;
    var layer = layui.layer;
    var token = '<?= LoginAuth::genToken() ?>';
    var balanceUrl = '<?= DC_URL ?>user/balance.php';
    var submitting = false;

    function submitUpgrade(levelId) {
        if (submitting) return;
        submitting = true;
        var loadIndex = layer.load(2, { shade: [0.2, '#000'] });
        $.ajax({
            type: 'POST', url: '?action=upgrade_ajax', dataType: 'json',
            data: { level_id: levelId, token: token },
            success: function (res) {
                layer.close(loadIndex);
                if (res.code == 200) {
                    layer.msg('开通成功', { icon: 1, time: 1200 });
                    setTimeout(function () {
                        location.href = '/user/level.php?paid=1';
                    }, 900);
                    return;
                }
                if (res.code == 402) {
                    var d = res.data || {};
                    var html = '<div style="line-height:1.8;">'
                        + '余额：<b style="color:#ef4444;">¥' + parseFloat(d.balance || 0).toFixed(2) + '</b><br>'
                        + '需要：<b style="color:var(--theme-primary, #667eea);">¥' + parseFloat(d.required || 0).toFixed(2) + '</b><br>'
                        + '差额：<b style="color:#ef4444;">¥' + parseFloat(d.shortage || 0).toFixed(2) + '</b><br>'
                        + '本页仅支持钱包余额开通、续费或升级，是否前往充值？'
                        + '</div>';
                    layer.confirm(html, {
                        title: '余额不足，请先充值',
                        btn: ['去充值', '取消'],
                        icon: 0,
                        skin: '',
                    }, function () {
                        location.href = d.redirect || balanceUrl;
                    });
                    return;
                }
                layer.msg(res.msg || '提交失败', { icon: 2 });
            },
            error: function (xhr) {
                layer.close(loadIndex);
                var msg = '提交失败，请稍后再试';
                try {
                    msg = JSON.parse(xhr.responseText).msg || msg;
                } catch (e) {
                    var raw = (xhr.responseText || '').replace(/<[^>]+>/g, '').replace(/\s+/g, ' ').trim();
                    if (raw) msg = '服务器错误：' + raw.slice(0, 120);
                }
                layer.msg(msg, { icon: 2, time: 5000 });
            },
            complete: function () {
                submitting = false;
            }
        });
    }

    $('.lvm-card-btn').on('click', function () {
        if ($(this).prop('disabled')) return;
        var $card = $(this).closest('.lvm-card');
        var levelId = parseInt($card.attr('data-id'), 10);
        var levelName = $card.attr('data-name');
        var amount = parseFloat($card.attr('data-price'));
        var actionLabel = $card.attr('data-action-label') || '开通';
        var iconImg = $card.attr('data-icon-image') || '';
        var iconCls = $card.attr('data-icon') || 'ri-vip-diamond-line';
        var iconHtml = iconImg ? '<img src="'+iconImg+'" style="width:28px;height:28px;border-radius:50%;object-fit:cover;vertical-align:middle">' : '<i class="'+iconCls+'" style="font-size:22px;color:var(--theme-primary,#667eea);vertical-align:middle"></i>';

        var html = '<div style="line-height:1.8;">'
            + actionLabel + '等级 ' + iconHtml + ' <b style="color:var(--theme-primary, #667eea);">' + levelName + '</b>？<br>'
            + '将扣除钱包 <b style="color:#ef4444;">¥' + amount.toFixed(2) + '</b>'
            + '</div>';
        layer.confirm(html, {
            title: actionLabel + '会员等级',
            btn: ['确认' + actionLabel, '取消'],
            icon: 3,
            skin: '',
        }, function (idx) {
            layer.close(idx);
            submitUpgrade(levelId);
        });
    });
});
</script>

<script>
    $('#menu-level').addClass('menu-current');
</script>
