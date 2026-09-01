<?php
defined('DC_ROOT') || exit('access denied!');
$_userBalance = isset($userData['money']) ? floatval($userData['money']) : 0;
?>

<style>
    .upgrade-page { display:flex; flex-direction:column; gap:22px; padding:8px 0 18px; }

    .upgrade-hero {
        position:relative; overflow:hidden; border-radius:10px; padding:30px 32px;
        background:linear-gradient(135deg,rgba(124,58,237,.96) 0%,rgba(139,92,246,.92) 58%,rgba(167,139,250,.88) 100%);
        box-shadow:0 24px 56px rgba(124,58,237,.2); color:#fff;
    }
    .upgrade-hero::before,.upgrade-hero::after { content:''; position:absolute; border-radius:999px; background:rgba(255,255,255,.12); pointer-events:none; }
    .upgrade-hero::before { width:220px; height:220px; right:-72px; top:-96px; }
    .upgrade-hero::after { width:170px; height:170px; right:108px; bottom:-92px; }

    .upgrade-hero-inner { position:relative; z-index:1; }
    .upgrade-hero h1 { color:#fff; font-size:28px; font-weight:800; margin:0 0 8px; line-height:1.3; }
    .upgrade-hero p { color:rgba(255,255,255,.82); font-size:14px; margin:0; line-height:1.8; }
    .upgrade-current-badge { display:inline-flex; align-items:center; gap:6px; margin-top:14px; padding:6px 16px; border-radius:999px; background:rgba(255,255,255,.16); font-size:13px; font-weight:700; }
    .upgrade-current-icon { width:24px; height:24px; margin-left:-8px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center; background:rgba(255,255,255,.18); overflow:hidden; flex-shrink:0; }
    .upgrade-current-icon img { width:100%; height:100%; object-fit:cover; display:block; }

    .upgrade-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:18px; }

    .upgrade-card {
        position:relative; overflow:hidden; border-radius:10px; padding:24px;
        background:var(--pc-card-bg); border:2px solid #fff; box-shadow:0 1px 18px #12345b0a;
        transition:transform .18s,box-shadow .18s,border-color .18s; cursor:default;
    }
    .upgrade-card.is-current { border-color:rgba(124,58,237,.3); background:linear-gradient(135deg,rgba(124,58,237,.04),rgba(139,92,246,.02)); }
    .upgrade-card.can-upgrade { cursor:pointer; }
    .upgrade-card.can-upgrade:hover { transform:translateY(-4px); border-color:rgba(124,58,237,.3); box-shadow:0 18px 36px rgba(124,58,237,.14); }
    .upgrade-card.can-upgrade.selected { border-color:rgba(124,58,237,.5); box-shadow:0 18px 36px rgba(124,58,237,.2); }
    .upgrade-card.can-upgrade.selected::before { content:''; position:absolute; top:0; left:0; right:0; height:4px; background:linear-gradient(90deg,#7c3aed,#8b5cf6); }

    .upgrade-card-badge {
        position:absolute; top:14px; right:14px; padding:4px 12px; border-radius:999px; font-size:11px; font-weight:700;
    }
    .badge-current { background:rgba(124,58,237,.12); color:#7c3aed; }
    .badge-selected { background:linear-gradient(135deg,#7c3aed,#8b5cf6); color:#fff; opacity:0; transform:scale(.9); transition:.18s; }
    .upgrade-card.selected .badge-selected { opacity:1; transform:scale(1); }

    .upgrade-card-name { font-size:20px; font-weight:800; color:var(--text-main); margin-bottom:6px; padding-right:80px; }
    .upgrade-card-name .upgrade-card-icon { width:34px; height:34px; margin-right:8px; border-radius:12px; display:inline-flex; align-items:center; justify-content:center; vertical-align:middle; background:linear-gradient(135deg,#7c3aed,#8b5cf6); color:#fff; font-size:18px; overflow:hidden; }
    .upgrade-card-name .upgrade-card-icon img { width:100%; height:100%; object-fit:cover; display:block; }
    .upgrade-card-desc { font-size:13px; color:var(--text-sub); margin-bottom:14px; line-height:1.7; min-height:22px; }
    .upgrade-card-price { font-size:30px; font-weight:900; color:#7c3aed; line-height:1; margin-bottom:16px; }
    .upgrade-card-price small { font-size:13px; color:var(--text-sub); font-weight:600; }
    .upgrade-card-diff { display:inline-flex; align-items:center; gap:4px; padding:4px 10px; border-radius:999px; background:rgba(16,185,129,.1); color:#059669; font-size:12px; font-weight:700; margin-bottom:14px; }

    .upgrade-perms { display:grid; gap:6px; }
    .upgrade-perm { display:flex; align-items:center; justify-content:space-between; padding:10px 12px; border-radius:8px; background:rgba(124,58,237,.03); font-size:13px; font-weight:600; color:var(--text-main); }
    .perm-yes { color:#10b981; }
    .perm-no { color:#d1d5db; }

    .upgrade-action-bar {
        display:flex; align-items:center; justify-content:space-between; gap:16px; padding:18px 20px;
        background:var(--pc-card-bg); border:2px solid #fff; box-shadow:0 1px 18px #12345b0a; border-radius:10px;
    }
    .upgrade-balance { font-size:14px; color:var(--text-sub); }
    .upgrade-balance strong { color:var(--text-main); font-size:16px; }
    .btn-upgrade {
        display:inline-flex; align-items:center; justify-content:center; gap:8px;
        min-width:140px; min-height:44px; padding:0 24px; border:0; border-radius:14px;
        background:linear-gradient(135deg,#7c3aed,#8b5cf6); color:#fff; font-size:14px; font-weight:700;
        box-shadow:0 14px 28px rgba(124,58,237,.2); cursor:pointer;
        transition:transform .18s,box-shadow .18s;
    }
    .btn-upgrade:hover { transform:translateY(-2px); box-shadow:0 18px 34px rgba(124,58,237,.24); }
    .btn-upgrade:disabled { opacity:.55; cursor:not-allowed; transform:none; box-shadow:none; }

    .upgrade-auto { margin-top:14px; padding:12px 14px; border-radius:10px; background:rgba(16,185,129,.04); border:1px solid rgba(16,185,129,.12); }
    .upgrade-auto-title { display:flex; align-items:center; gap:6px; font-size:12px; font-weight:700; color:#059669; margin-bottom:8px; }
    .upgrade-auto-mode { font-weight:500; color:#047857; opacity:.8; }
    .upgrade-auto-items { display:grid; gap:6px; }
    .upgrade-auto-row { display:grid; grid-template-columns:auto 1fr auto; align-items:center; gap:10px; font-size:12px; }
    .upgrade-auto-label { color:var(--text-sub); font-weight:600; white-space:nowrap; min-width:60px; }
    .upgrade-auto-bar { height:6px; border-radius:999px; background:rgba(16,185,129,.12); overflow:hidden; }
    .upgrade-auto-fill { height:100%; border-radius:999px; background:linear-gradient(90deg,#10b981,#34d399); transition:width .4s ease; }
    .upgrade-auto-num { color:var(--text-main); font-weight:700; font-size:11px; white-space:nowrap; }
    .upgrade-auto-done { display:inline-flex; align-items:center; gap:4px; margin-top:8px; padding:4px 10px; border-radius:999px; background:rgba(16,185,129,.1); color:#059669; font-size:11px; font-weight:700; }

    @media (max-width:768px) {
        .upgrade-grid { grid-template-columns:1fr; }
        .upgrade-hero { padding:24px 20px; }
        .upgrade-action-bar { flex-direction:column; align-items:stretch; }
        .btn-upgrade { width:100%; }
    }
</style>

<main class="upgrade-page">
    <section class="upgrade-hero">
        <div class="upgrade-hero-inner">
            <h1>分店升级</h1>
            <p>升级分店等级，解锁更多权限和功能，提升您的运营能力。</p>
            <?php if ($currentLevel): ?>
                <?php
                    $_currentLevelIcon = trim((string)($currentLevel['icon'] ?? ''));
                    if ($_currentLevelIcon === '') $_currentLevelIcon = 'ri-store-2-line';
                    $_currentLevelIconImage = trim((string)($currentLevel['icon_image'] ?? ''));
                ?>
                <div class="upgrade-current-badge">
                    <span class="upgrade-current-icon"><?= $_currentLevelIconImage !== '' ? '<img src="' . htmlspecialchars($_currentLevelIconImage) . '" alt="">' : '<i class="' . htmlspecialchars($_currentLevelIcon) . '"></i>' ?></span> 当前等级：<?= htmlspecialchars($currentLevel['name']) ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <div class="upgrade-grid">
        <?php
        $permLabels = Station_Model::PERM_MAP;
        foreach ($upgradeLevels as $lv):
            $isCurrent = !empty($lv['is_current']);
            $canUpgrade = !empty($lv['can_upgrade']);
            $cls = $isCurrent ? 'is-current' : ($canUpgrade ? 'can-upgrade' : '');
            $_lvIcon = trim((string)($lv['icon'] ?? ''));
            if ($_lvIcon === '') $_lvIcon = 'ri-store-2-line';
            $_lvIconImage = trim((string)($lv['icon_image'] ?? ''));
        ?>
        <div class="upgrade-card <?= $cls ?>" <?= $canUpgrade ? 'data-id="'.$lv['id'].'" data-diff="'.$lv['diff_price'].'" data-name="'.htmlspecialchars($lv['name']).'"' : '' ?>>
            <?php if ($isCurrent): ?>
                <span class="upgrade-card-badge badge-current">当前等级</span>
            <?php elseif ($canUpgrade): ?>
                <span class="upgrade-card-badge badge-selected"><i class="fa fa-check"></i> 已选择</span>
            <?php endif; ?>

            <div class="upgrade-card-name"><span class="upgrade-card-icon"><?= $_lvIconImage !== '' ? '<img src="' . htmlspecialchars($_lvIconImage) . '" alt="">' : '<i class="' . htmlspecialchars($_lvIcon) . '"></i>' ?></span><?= htmlspecialchars($lv['name']) ?></div>
            <div class="upgrade-card-desc"><?= htmlspecialchars($lv['description'] ?? '') ?></div>
            <div class="upgrade-card-price">
                ¥<?= number_format($lv['price'], 2) ?>
                <small>/ 永久</small>
            </div>
            <?php if ($canUpgrade): ?>
                <div class="upgrade-card-diff"><i class="fa fa-arrow-up"></i> 补差价 ¥<?= number_format($lv['diff_price'], 2) ?></div>
            <?php endif; ?>

            <div class="upgrade-perms">
                <?php foreach ($permLabels as $field => $label): ?>
                    <div class="upgrade-perm">
                        <span><?= $label ?></span>
                        <?php if (($lv[$field] ?? 'n') === 'y'): ?>
                            <span class="perm-yes"><i class="fa fa-check"></i></span>
                        <?php else: ?>
                            <span class="perm-no"><i class="fa fa-times"></i></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($lv['has_auto_upgrade']) && $canUpgrade): ?>
                <div class="upgrade-auto">
                    <div class="upgrade-auto-title">
                        <i class="fa fa-arrow-up"></i> 自动升级条件
                        <span class="upgrade-auto-mode">（<?= htmlspecialchars($lv['upgrade_mode_text']) ?>）</span>
                    </div>
                    <div class="upgrade-auto-items">
                        <?php
                        $conditions = [
                            ['key' => 'upgrade_sales_amount', 'stat' => 'sales', 'label' => '销售额', 'unit' => '元', 'fmt' => true],
                            ['key' => 'upgrade_order_count', 'stat' => 'orders', 'label' => '订单量', 'unit' => '单', 'fmt' => false],
                            ['key' => 'upgrade_days', 'stat' => 'days', 'label' => '运营天数', 'unit' => '天', 'fmt' => false],
                            ['key' => 'upgrade_sub_count', 'stat' => 'subs', 'label' => '下级分店', 'unit' => '店', 'fmt' => false],
                        ];
                        foreach ($conditions as $c):
                            $target = ($c['fmt']) ? (float)($lv[$c['key']] ?? 0) : (int)($lv[$c['key']] ?? 0);
                            if ($target <= 0) continue;
                            $current = ($c['fmt']) ? (float)($myStats[$c['stat']] ?? 0) : (int)($myStats[$c['stat']] ?? 0);
                            $pct = min(100, round($current / $target * 100));
                            $done = $current >= $target;
                        ?>
                        <div class="upgrade-auto-row">
                            <span class="upgrade-auto-label"><?= $c['label'] ?></span>
                            <div class="upgrade-auto-bar"><div class="upgrade-auto-fill" style="width:<?= $pct ?>%<?= $done ? ';background:linear-gradient(90deg,#10b981,#059669)' : '' ?>"></div></div>
                            <span class="upgrade-auto-num"><?= $c['fmt'] ? number_format($current, 2) : $current ?> / <?= $c['fmt'] ? number_format($target, 2) : $target ?><?= $c['unit'] ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="upgrade-action-bar">
        <div class="upgrade-balance">账户余额：<strong>¥<?= number_format($_userBalance, 2) ?></strong></div>
        <button class="btn-upgrade" id="upgradeBtn" disabled><i class="fa fa-rocket"></i> 选择等级后升级</button>
    </div>
</main>

<script>
layui.use(['layer','jquery'], function(){
    var $ = layui.$;
    var layer = layui.layer;
    var selectedId = '', selectedDiff = 0, selectedName = '';

    $('.upgrade-card.can-upgrade').click(function(){
        $('.upgrade-card.can-upgrade').removeClass('selected');
        $(this).addClass('selected');
        selectedId = $(this).data('id');
        selectedDiff = parseFloat($(this).data('diff'));
        selectedName = $(this).data('name');
        var label = selectedDiff > 0 ? '确认升级（补 ¥' + selectedDiff.toFixed(2) + '）' : '确认升级（免费）';
        $('#upgradeBtn').prop('disabled', false).html('<i class="fa fa-rocket"></i> ' + label);
    });

    $('#upgradeBtn').click(function(){
        if (!selectedId) return;
        var confirmMsg = '确定升级至「' + selectedName + '」？';
        if (selectedDiff > 0) confirmMsg += '\n将从余额扣除 ¥' + selectedDiff.toFixed(2);

        layer.confirm(confirmMsg, {
            btn: ['确认升级','取消'],
            icon: 3,
            title: '升级确认'
        }, function(confirmIdx){
            layer.close(confirmIdx);
            var $btn = $('#upgradeBtn');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 处理中...');
            var loadIdx = layer.load(2, {shade:[0.3,'#000']});
            $.ajax({
                type: 'POST',
                url: '?action=upgrade_ajax',
                data: { id: selectedId, token: '<?= LoginAuth::genToken() ?>' },
                dataType: 'json',
                success: function(e) {
                    layer.close(loadIdx);
                    if (e.code == 400) {
                        layer.msg(e.msg, {icon:2, time:3000});
                        return;
                    }
                    layer.alert(e.msg, {
                        icon: 1,
                        title: '升级成功',
                        btn: ['确定'],
                        yes: function(){ location.href = 'station.php'; }
                    });
                },
                error: function(xhr){
                    layer.close(loadIdx);
                    var msg = '升级失败，请稍后重试';
                    try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(ex){}
                    layer.msg(msg, {icon:2, time:3000});
                },
                complete: function(){
                    $btn.prop('disabled', false).html('<i class="fa fa-rocket"></i> 选择等级后升级');
                }
            });
        });
    });
});
</script>

<?php include __DIR__ . '/../_pc_page_footer.php'; ?>
<script>
    $('#menu-station').addClass('menu-current');
</script>
