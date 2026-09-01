<?php
defined('DC_ROOT') || exit('access denied!');
$_stationList = isset($station) && is_array($station) ? $station : [];
$_hasStation = !empty($userData['station']);
$_currentStationLevelId = $_hasStation ? intval($userData['station']['level_id'] ?? 0) : 0;
$_walletBalance = isset($userData['money']) ? (float)$userData['money'] : 0;
$_priceMode = Option::get('station_upgrade_price_mode') ?: 'diff';
$_currentStationLevel = null;
foreach ($_stationList as $_row) {
    if ($_currentStationLevelId > 0 && intval($_row['id']) === $_currentStationLevelId) {
        $_currentStationLevel = $_row;
        break;
    }
}
$_currentSort = $_currentStationLevel ? intval($_currentStationLevel['sort']) : -1;
$_currentPrice = $_currentStationLevel ? (float)$_currentStationLevel['price'] : 0;
$_currentStationIcon = $_hasStation ? trim((string)($_currentStationLevel['icon'] ?? '')) : 'ri-store-2-line';
if ($_currentStationIcon === '') $_currentStationIcon = 'ri-store-2-line';
$_currentStationIconImage = $_hasStation ? trim((string)($_currentStationLevel['icon_image'] ?? '')) : '';
$_userLevelSort = 0;
if (class_exists('Level_Service')) {
    $_activeLevelId = (int)Level_Service::getActiveLevelId($userData);
    if ($_activeLevelId > 0) {
        $_levelRow = Database::getInstance()->once_fetch_array("SELECT sort FROM " . DB_PREFIX . "member WHERE id={$_activeLevelId}");
        $_userLevelSort = $_levelRow ? (int)$_levelRow['sort'] : 0;
    }
}
$_stIndexMap = [];
$_idx = 0;
foreach ($_stationList as $_lv) {
    $_idx++;
    $_stIndexMap[intval($_lv['id'])] = $_idx;
}
$_stColors = [
    1 => ['#94a3b8','#64748b'],
    2 => ['#a78bfa','#7c3aed'],
    3 => ['#60a5fa','var(--theme-primary)'],
    4 => ['#34d399','#059669'],
    5 => ['#fb923c','#ea580c'],
    6 => ['#f472b6','#db2777'],
    7 => ['#fbbf24','#d97706'],
    8 => ['#f87171','#dc2626'],
];
$permLabels = Station_Model::PERM_MAP;
// 获取当前模板
?>
<style>
    .station-level-page { display: flex; flex-direction: column; gap: 22px; padding: 6px 0 24px; }

    /* ── hero ── */
    .station-level-hero { padding: 26px 28px; border-radius: 10px; background: var(--pc-card-bg); border: 2px solid #fff; box-shadow: 0 1px 18px #12345b0a; }
    .station-level-hero-inner { display: grid; grid-template-columns: minmax(0,1fr) auto; gap: 16px; align-items: center; }
    .station-level-hero-name-row { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
    .station-level-hero-icon {
        width: 46px; height: 46px; border-radius: 14px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg,#f8dfad,#e8bb72); color: #7a5a2b; font-size: 22px;
        box-shadow: 0 8px 18px rgba(232,187,114,.16); overflow: hidden;
    }
    .station-level-hero-icon img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .station-level-hero-name { font-size: 22px; font-weight: 800; line-height: 1.15; margin: 12px 0 8px; color: #0f172a; }
    .station-level-hero-meta { font-size: 13px; color: #64748b; }
    .station-level-hero-badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px; border-radius: 999px; background: #f1f5f9; color: var(--tp-dark); font-size: 12px; margin-right: 8px; }
    .station-level-hero-wallet { display: inline-flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 8px 16px; border-radius: 999px; background: #f8fafc; border: 1px solid #e2e8f0; font-size: 13px; color: var(--tp-dark); align-self: center; }
    .station-level-hero-wallet .wallet-amt { font-weight: 800; color: #0f172a; }
    .station-level-hero-wallet a { color: var(--theme-primary); text-decoration: none; padding: 3px 12px; background: rgba(var(--tp-rgb),.06); border-radius: 999px; font-size: 12px; font-weight: 600; transition: .18s; display: inline-flex; align-items: center; gap: 4px; }
    .station-level-hero-wallet a:hover { background: rgba(var(--tp-rgb),.12); color: var(--tp-dark); text-decoration: none; }
    .station-level-hero-note { margin-top: 10px; font-size: 12px; line-height: 1.7; color: var(--tp-light); }

    /* ── grid ── */
    .station-level-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 18px; }

    /* ── card ── */
    .station-level-card {
        background: var(--pc-card-bg); border: 2px solid #fff; border-radius: 10px; box-shadow: 0 1px 18px #12345b0a;
        display: flex; flex-direction: column; position: relative;
        transition: transform .22s, box-shadow .22s, border-color .22s;
        overflow: hidden;
    }
    .station-level-card:hover { transform: translateY(-4px); border-color: rgba(var(--tp-rgb),.22); box-shadow: 0 12px 32px rgba(var(--tp-rgb),.10), 0 2px 6px rgba(0,0,0,.04); }

    /* ── card title ── */
    .slc-title {
        display: flex; align-items: center; gap: 10px; padding: 20px 22px 0;
    }
    .slc-title-icon {
        width: 38px; height: 38px; border-radius: 12px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        background: var(--slc-grad); color: #fff; font-size: 19px;
        box-shadow: 0 8px 18px rgba(var(--tp-rgb),.12); overflow: hidden;
    }
    .slc-title-icon img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .slc-title-lv {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 40px; padding: 2px 10px; border-radius: 6px;
        font-size: 12px; font-weight: 800; color: #fff; line-height: 1.7;
        background: var(--slc-grad); flex-shrink: 0;
    }
    .slc-title-name { font-size: 18px; font-weight: 700; color: #1f2937; line-height: 1.3; }
    .slc-title-current {
        margin-left: auto; font-size: 11px; font-weight: 600; color: var(--slc-accent);
        padding: 2px 10px; border-radius: 999px;
        background: rgba(var(--tp-rgb),.06); border: 1px solid rgba(var(--tp-rgb),.12); flex-shrink: 0;
    }

    /* ── card body ── */
    .slc-body { padding: 14px 22px 20px; display: flex; flex-direction: column; gap: 14px; flex: 1; }

    /* price */
    .slc-price { display: flex; align-items: baseline; gap: 3px; padding-top: 2px; }
    .slc-price .sym { font-size: 16px; font-weight: 700; color: var(--slc-accent); }
    .slc-price .num { font-size: 34px; font-weight: 900; color: var(--slc-accent); line-height: 1; letter-spacing: -1px; }
    .slc-price .unit { font-size: 12px; color: #9ca3af; margin-left: 5px; font-weight: 500; }

    /* meta tags */
    .slc-meta { display: flex; gap: 6px; flex-wrap: wrap; }
    .slc-meta .tag { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; background: #f3f4f6; color: #6b7280; }
    .slc-meta .tag.warn { background: rgba(var(--tp-rgb),.06); color: var(--theme-primary); }
    .slc-meta .tag.up { background: #ecfdf5; color: #059669; }

    /* desc */
    .slc-desc { font-size: 12.5px; color: #6b7280; line-height: 1.7; padding: 10px 14px; background: #f9fafb; border-radius: 10px; border-left: 3px solid #d1d5db; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

    /* upgrade hint */
    .slc-upgrade { background: linear-gradient(135deg, #ecfdf5, #d1fae5); border: 1px solid #a7f3d0; border-radius: 10px; padding: 10px 12px; font-size: 12px; color: #047857; }
    .slc-upgrade-title { font-weight: 700; margin-bottom: 3px; display: flex; align-items: center; gap: 5px; font-size: 12px; color: #065f46; }
    .slc-upgrade-mode { font-weight: 400; font-size: 10px; background: #d1fae5; color: #047857; padding: 1px 6px; border-radius: 4px; margin-left: 2px; }
    .slc-upgrade-desc { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }

    /* features */
    .slc-features { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 5px; }
    .slc-feat {
        display: flex; align-items: center; justify-content: space-between; gap: 6px;
        padding: 7px 10px; border-radius: 8px; background: #f8fafc; font-size: 12px; font-weight: 600; color: #374151;
        border: 1px solid #f1f5f9; transition: background .15s;
    }
    .slc-feat:hover { background: #f1f5f9; }
    .slc-feat span:first-child { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .slc-feat .yes { color: #10b981; font-weight: 700; }
    .slc-feat .no { color: #d1d5db; }
    .slc-feat .val { flex-shrink: 0; color: var(--slc-accent); font-size: 11px; font-weight: 800; }

    /* divider */
    .slc-divider { height: 1px; background: #f1f5f9; margin: 2px 0; }

    /* buttons */
    .station-level-card-btn {
        margin-top: auto; display: flex; align-items: center; justify-content: center; gap: 6px;
        min-height: 42px; padding: 0 18px; border-radius: 10px; border: none; cursor: pointer;
        font-size: 14px; font-weight: 600; transition: all .18s;
        background: var(--theme-primary); color: #fff;
    }
    .station-level-card-btn:hover { background: var(--tp-dark); }
    .station-level-card-btn.is-disabled { background: #f3f4f6; color: #9ca3af; cursor: not-allowed; }
    .station-level-card-btn.is-disabled:hover { background: #f3f4f6; }
    .station-level-card-tag {
        margin-top: auto; display: flex; align-items: center; justify-content: center; gap: 6px;
        min-height: 42px; padding: 0 18px; border-radius: 10px;
        font-size: 14px; font-weight: 600;
        background: rgba(var(--tp-rgb),.04); color: var(--theme-primary); border: 1px solid rgba(var(--tp-rgb),.18);
    }

    /* current card */
    .station-level-card.is-current { border-color: var(--slc-accent); border-width: 2px; box-shadow: 0 0 0 3px rgba(var(--tp-rgb),.08); }
    .station-level-card.is-current:hover { transform: none; box-shadow: 0 0 0 3px rgba(var(--tp-rgb),.08); }

    /* info bar */
    .station-level-info { background: linear-gradient(0deg, #fff, #f3f5f8); border: 2px solid #fff; border-radius: 14px; padding: 18px 20px; display: grid; grid-template-columns: auto repeat(3, minmax(0,1fr)); gap: 12px; align-items: center; }
    .station-level-info-title { font-size: 15px; font-weight: 700; color: #111827; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; }
    .station-level-info p { margin: 0; color: #6b7280; font-size: 12px; line-height: 1.65; }

    @media (max-width: 768px) {
        .station-level-hero { padding: 22px 18px; }
        .station-level-hero-inner { grid-template-columns: 1fr; }
        .station-level-grid { grid-template-columns: 1fr; gap: 14px; }
        .station-level-info { grid-template-columns: 1fr; }
        .slc-title { padding: 18px 18px 0; }
        .slc-body { padding: 12px 18px 18px; }
        .slc-features { grid-template-columns: 1fr; }
    }
</style>

<main class="station-level-page">
    <section class="station-level-hero">
        <div class="station-level-hero-inner">
            <div>
                <div class="station-level-hero-name-row">
                    <div class="station-level-hero-icon"><?= $_hasStation && $_currentStationIconImage !== '' ? '<img src="' . htmlspecialchars($_currentStationIconImage) . '" alt="">' : '<i class="' . htmlspecialchars($_hasStation ? $_currentStationIcon : 'ri-store-2-line') . '"></i>' ?></div>
                    <div class="station-level-hero-name"><?php if ($_hasStation && !empty($_stIndexMap[$_currentStationLevelId])): ?><span style="font-size:13px;padding:3px 10px 3px 0">Lv.<?= $_stIndexMap[$_currentStationLevelId] ?></span><?php endif; ?><?= $_hasStation ? htmlspecialchars($_currentStationLevel['name'] ?? '已开通分店') : '未开通分店' ?></div>
                    <div class="station-level-hero-meta">
                        <?php if ($_hasStation): ?>
                            <span class="station-level-hero-badge"><i class="fa fa-check-circle"></i> 已开通</span>
                        <?php else: ?>
                            <span class="station-level-hero-badge"><i class="fa fa-store"></i> 未开通</span>
                        <?php endif; ?>
                        <?= $_hasStation ? '可选择更高等级升级' : '选择套餐后即可开通' ?>
                    </div>
                </div>
                <div class="station-level-hero-note">分店套餐仅支持钱包余额开通或升级，余额不足请先充值后再回来办理。</div>
            </div>
            <div class="station-level-hero-wallet">
                <i class="fa fa-credit-card"></i>
                <span>钱包余额</span>
                <span class="wallet-amt">¥<?= number_format($_walletBalance, 2) ?></span>
                <a href="/user/balance.php">去充值 <i class="fa fa-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <section class="station-level-grid">
        <?php $stIndex = 0; foreach ($_stationList as $val): $stIndex++;
            $isCurrent = $_hasStation && intval($val['id']) === $_currentStationLevelId;
            $targetSort = intval($val['sort']);
            $targetPrice = (float)$val['price'];
            $payAmount = $targetPrice;
            $canPurchase = true;
            $disabledReason = '';
            $actionLabel = $_hasStation ? '升级' : '开通';
            if ($_hasStation) {
                if ($isCurrent) {
                    $canPurchase = false;
                    $disabledReason = '当前等级';
                    $actionLabel = '当前等级';
                } elseif ($targetSort <= $_currentSort) {
                    $canPurchase = false;
                    $disabledReason = '不支持降级';
                    $actionLabel = '已拥有';
                } else {
                    $payAmount = ($_priceMode === 'full') ? $targetPrice : max(0, round($targetPrice - $_currentPrice, 2));
                    $actionLabel = ($_priceMode === 'full') ? '¥' . number_format($payAmount, 2) . ' 升级' : '补 ¥' . number_format($payAmount, 2) . ' 升级';
                }
            } else {
                $actionLabel = '¥' . number_format($payAmount, 2) . ' 开通';
            }
            $memberGate = intval($val['member_gate'] ?? 0);
            if ($memberGate > 0) {
                $_gateSortRow = Database::getInstance()->once_fetch_array("SELECT sort FROM " . DB_PREFIX . "member WHERE id={$memberGate}");
                $_gateSort = $_gateSortRow ? (int)$_gateSortRow['sort'] : 0;
                if ($_userLevelSort < $_gateSort) {
                    $canPurchase = false;
                    $disabledReason = '需达到会员等级「' . ($val['member_gate_name'] ?? '') . '」';
                    $actionLabel = $disabledReason;
                }
            }
            $_c = $_stColors[$stIndex] ?? $_stColors[1];
            $_levelIcon = trim((string)($val['icon'] ?? ''));
            if ($_levelIcon === '') $_levelIcon = 'ri-store-2-line';
            $_levelIconImage = trim((string)($val['icon_image'] ?? ''));
        ?>
        <article class="station-level-card<?= $isCurrent ? ' is-current' : '' ?>" style="--slc-grad:linear-gradient(135deg,<?= $_c[0] ?>,<?= $_c[1] ?>);--slc-accent:<?= $_c[1] ?>" data-id="<?= intval($val['id']) ?>" data-name="<?= htmlspecialchars($val['name']) ?>" data-price="<?= htmlspecialchars((string)$payAmount) ?>" data-action-label="<?= htmlspecialchars($actionLabel) ?>">
            <div class="slc-title">
                <span class="slc-title-icon"><?= $_levelIconImage !== '' ? '<img src="' . htmlspecialchars($_levelIconImage) . '" alt="">' : '<i class="' . htmlspecialchars($_levelIcon) . '"></i>' ?></span>
                <span class="slc-title-lv">Lv.<?= $stIndex ?></span>
                <span class="slc-title-name"><?= htmlspecialchars($val['name']) ?></span>
                <?php if ($isCurrent): ?><span class="slc-title-current"><i class="fa fa-check-circle"></i> 当前</span><?php endif; ?>
            </div>
            <div class="slc-body">
                <div class="slc-price">
                    <span class="sym">¥</span>
                    <span class="num"><?= number_format($targetPrice, 2) ?></span>
                    <span class="unit">/ 永久</span>
                </div>
                <div class="slc-meta">
                    <?php if (!empty($val['member_gate_name'])): ?>
                        <span class="tag warn"><i class="fa fa-lock"></i> 需<?= htmlspecialchars($val['member_gate_name']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($val['upgrade_desc'])): ?>
                        <span class="tag up"><i class="fa fa-bolt"></i> 可自动升级</span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($val['description'])): ?>
                    <div class="slc-desc"><?= htmlspecialchars($val['description']) ?></div>
                <?php endif; ?>
                <?php if (!empty($val['upgrade_desc'])): ?>
                    <div class="slc-upgrade">
                        <div class="slc-upgrade-title"><i class="fa fa-bolt"></i> 达标自动升级 <span class="slc-upgrade-mode"><?= htmlspecialchars($val['upgrade_mode_text']) ?></span></div>
                        <div class="slc-upgrade-desc"><?= htmlspecialchars($val['upgrade_desc']) ?></div>
                    </div>
                <?php endif; ?>
                <div class="slc-divider"></div>
                <div class="slc-features">
                    <?php foreach ($permLabels as $field => $label): ?>
                        <div class="slc-feat">
                            <span><?= $label ?></span>
                            <?php if (($val[$field] ?? 'n') === 'y'): ?>
                                <span class="yes"><i class="fa fa-check-circle"></i></span>
                            <?php else: ?>
                                <span class="no"><i class="fa fa-minus-circle"></i></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <div class="slc-feat"><span>供货手续费</span><span class="val"><?= number_format($val['service_change'] * 100, 2) ?>%</span></div>
                </div>
                <?php if ($isCurrent): ?>
                    <div class="station-level-card-tag"><i class="fa fa-check-circle"></i> 当前等级</div>
                <?php elseif (!$canPurchase): ?>
                    <button type="button" class="station-level-card-btn is-disabled" disabled title="<?= htmlspecialchars($disabledReason) ?>"><i class="fa fa-lock"></i> <?= htmlspecialchars($actionLabel) ?></button>
                <?php else: ?>
                    <button type="button" class="station-level-card-btn"><i class="fa fa-arrow-up"></i> <?= htmlspecialchars($actionLabel) ?></button>
                <?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>
        <?php if (empty($_stationList)): ?>
            <div style="grid-column:1/-1;padding:40px;text-align:center;color:#9ca3af;background:#fafafa;border-radius:12px;">暂无开放的分店套餐</div>
        <?php endif; ?>
    </section>

</main>

<script>
layui.use(['layer', 'jquery'], function () {
    var $ = layui.$;
    var layer = layui.layer;
    var token = '<?= LoginAuth::genToken() ?>';
    var balanceUrl = '/user/balance.php';
    var submitting = false;

    function submitStationLevel(levelId) {
        if (submitting) return;
        submitting = true;
        var loadIndex = layer.load(2, { shade: [0.2, '#000'] });
        $.ajax({
            type: 'POST', url: '?action=open_ajax', dataType: 'json',
            data: { id: levelId, token: token },
            success: function (res) {
                layer.close(loadIndex);
                if (res.code == 200) {
                    layer.msg(res.msg || '操作成功', { icon: 1, time: 1200 });
                    setTimeout(function () { location.href = '/user/station.php'; }, 900);
                    return;
                }
                var msg = res.msg || '提交失败';
                if (msg.indexOf('余额不足') !== -1 || msg.indexOf('请先充值') !== -1) {
                    layer.confirm('<div style="line-height:1.8;">' + msg + '<br>是否前往钱包充值？</div>', {
                        title: '余额不足，请先充值',
                        btn: ['前往充值', '取消'],
                        icon: 0,
                        skin: ''
                    }, function () {
                        location.href = balanceUrl;
                    });
                    return;
                }
                layer.msg(msg, { icon: 2 });
            },
            error: function (xhr) {
                layer.close(loadIndex);
                var msg = '操作失败，请稍后重试';
                try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e) {}
                layer.msg(msg, { icon: 2 });
            },
            complete: function () {
                submitting = false;
            }
        });
    }

    $('.station-level-card-btn').on('click', function () {
        if ($(this).hasClass('is-disabled')) return;
        var $card = $(this).closest('.station-level-card');
        var levelId = parseInt($card.attr('data-id'), 10);
        var levelName = $card.attr('data-name');
        var amount = parseFloat($card.attr('data-price')) || 0;
        var actionLabel = $card.attr('data-action-label') || '开通';
        var html = '<div style="line-height:1.8;">确定办理分店等级 <b style="color:var(--theme-primary);">' + levelName + '</b>？<br>将从钱包余额扣除 <b style="color:#ef4444;">¥' + amount.toFixed(2) + '</b></div>';
        layer.confirm(html, {
            title: actionLabel.indexOf('升级') !== -1 ? '升级分店等级' : '开通分店',
            btn: ['确认办理', '取消'],
            icon: 3,
            skin: ''
        }, function (idx) {
            layer.close(idx);
            submitStationLevel(levelId);
        });
    });
});
</script>

<?php include __DIR__ . '/../_pc_page_footer.php'; ?>
<script>
    $('#menu-open-station').addClass('menu-current');
</script>