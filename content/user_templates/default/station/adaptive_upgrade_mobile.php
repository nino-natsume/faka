<?php
defined('DC_ROOT') || exit('access denied!');
$_userBalance = isset($userData['money']) ? floatval($userData['money']) : 0;
?>
<style>
    .mup-page { padding:0 0 calc(24px + env(safe-area-inset-bottom)); background:#f5f7fb; min-height:100%; }
    .mup-hero { background:linear-gradient(180deg,#2f63d6 0%,#2b58c8 100%); color:#fff; padding:24px 16px 32px; position:relative; overflow:hidden; }
    .mup-hero::after { content:''; position:absolute; bottom:-1px; left:0; right:0; height:24px; background:#f5f7fb; border-radius:24px 24px 0 0; }
    .mup-hero-title { font-size:22px; font-weight:700; margin-bottom:6px; }
    .mup-hero-desc { font-size:13px; opacity:.82; line-height:1.7; }
    .mup-hero-badge { display:inline-flex; align-items:center; gap:6px; margin-top:10px; padding:5px 14px; border-radius:999px; background:rgba(255,255,255,.16); font-size:12px; font-weight:600; }
    .mup-hero-badge-icon { width:22px; height:22px; margin-left:-8px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center; background:rgba(255,255,255,.18); overflow:hidden; flex-shrink:0; }
    .mup-hero-badge-icon img { width:100%; height:100%; object-fit:cover; display:block; }
    .mup-body { padding:0 14px; position:relative; z-index:1; margin-top:-8px; }
    .mup-list { display:flex; flex-direction:column; gap:14px; }
    .mup-card{background:linear-gradient(0deg, #fff, #f3f5f8); border-radius:10px; box-shadow:var(--shadow-primary); padding:20px 16px; position:relative; border:2px solid #fff; transition:border-color .2s,box-shadow .2s,transform .15s;}
    .mup-card:active { transform:scale(.985); }
    .mup-card.is-current { border-color:rgba(47,117,255,.25); background:linear-gradient(135deg,rgba(47,117,255,.04),rgba(47,117,255,.01)); }
    .mup-card.can-upgrade.is-active{border-color:#2f75ff; box-shadow:var(--shadow-primary);}
    .mup-card-tag { position:absolute; top:12px; right:12px; font-size:11px; font-weight:600; padding:3px 10px; border-radius:999px; }
    .tag-current { background:rgba(47,117,255,.1); color:#2f75ff; }
    .tag-selected { background:#2f75ff; color:#fff; opacity:0; transform:scale(.8); transition:opacity .2s,transform .2s; }
    .mup-card.is-active .tag-selected { opacity:1; transform:scale(1); }
    .mup-card-name { font-size:17px; font-weight:700; color:#20293a; margin-bottom:4px; padding-right:70px; }
    .mup-card-icon { width:32px; height:32px; margin-right:8px; border-radius:12px; display:inline-flex; align-items:center; justify-content:center; vertical-align:middle; background:linear-gradient(135deg,#2f75ff,#5b8def); color:#fff; font-size:17px; overflow:hidden; }
    .mup-card-icon img { width:100%; height:100%; object-fit:cover; display:block; }
    .mup-card-desc { font-size:12px; color:#8f9aad; margin-bottom:12px; line-height:1.6; }
    .mup-card-price { font-size:26px; font-weight:800; color:#2f75ff; line-height:1; margin-bottom:12px; }
    .mup-card-price small { font-size:12px; color:#8f9aad; font-weight:500; margin-left:4px; }
    .mup-card-diff { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:999px; background:rgba(16,185,129,.1); color:#059669; font-size:11px; font-weight:600; margin-bottom:12px; }
    .mup-perms { display:flex; flex-direction:column; gap:0; }
    .mup-perm { display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f0f3f8; font-size:13px; color:#4c5a70; }
    .mup-perm:last-child { border-bottom:0; }
    .mup-perm-yes { color:#10b981; font-weight:600; }
    .mup-perm-no { color:#d1d5db; font-weight:600; }
    .mup-past { opacity:.5; }
    .mup-auto { margin-top:12px; padding:10px 12px; border-radius:10px; background:rgba(16,185,129,.04); border:1px solid rgba(16,185,129,.1); }
    .mup-auto-title { display:flex; align-items:center; gap:5px; font-size:11px; font-weight:700; color:#059669; margin-bottom:6px; }
    .mup-auto-mode { font-weight:500; opacity:.8; }
    .mup-auto-items { display:grid; gap:5px; }
    .mup-auto-row { display:grid; grid-template-columns:52px 1fr auto; align-items:center; gap:8px; font-size:11px; }
    .mup-auto-label { color:#7b8699; font-weight:600; }
    .mup-auto-bar { height:5px; border-radius:999px; background:rgba(16,185,129,.1); overflow:hidden; }
    .mup-auto-fill { height:100%; border-radius:999px; background:linear-gradient(90deg,#10b981,#34d399); }
    .mup-auto-num { color:#20293a; font-weight:700; font-size:10px; white-space:nowrap; }
    .mup-bottom { margin-top:18px; padding:0 14px; }
    .mup-balance { text-align:center; font-size:13px; color:#7b8699; margin-bottom:12px; }
    .mup-balance strong { color:#20293a; }
    .mup-btn { width:100%; height:48px; border:0; border-radius:14px; background:linear-gradient(135deg,#2f75ff 0%,#5b8def 100%); color:#fff; font-size:16px; font-weight:700; box-shadow:0 8px 24px rgba(47,117,255,.25); }
    .mup-btn:disabled { opacity:.55; box-shadow:none; }
</style>

<div class="mup-page">
    <div class="mup-hero">
        <div class="mup-hero-title">分店升级</div>
        <div class="mup-hero-desc">升级分店，解锁更多权限和功能。</div>
        <?php if ($currentLevel): ?>
            <?php
                $_currentLevelIcon = trim((string)($currentLevel['icon'] ?? ''));
                if ($_currentLevelIcon === '') $_currentLevelIcon = 'ri-store-2-line';
                $_currentLevelIconImage = trim((string)($currentLevel['icon_image'] ?? ''));
            ?>
            <div class="mup-hero-badge"><span class="mup-hero-badge-icon"><?= $_currentLevelIconImage !== '' ? '<img src="' . htmlspecialchars($_currentLevelIconImage) . '" alt="">' : '<i class="' . htmlspecialchars($_currentLevelIcon) . '"></i>' ?></span> 当前：<?= htmlspecialchars($currentLevel['name']) ?></div>
        <?php endif; ?>
    </div>
    <div class="mup-body">
        <div class="mup-list">
            <?php
            $permLabels = Station_Model::PERM_MAP;
            foreach ($upgradeLevels as $lv):
                $isCurrent = !empty($lv['is_current']);
                $canUpgrade = !empty($lv['can_upgrade']);
                $cls = $isCurrent ? 'is-current' : ($canUpgrade ? 'can-upgrade' : 'mup-past');
                $_lvIcon = trim((string)($lv['icon'] ?? ''));
                if ($_lvIcon === '') $_lvIcon = 'ri-store-2-line';
                $_lvIconImage = trim((string)($lv['icon_image'] ?? ''));
            ?>
            <div class="mup-card <?= $cls ?>" <?= $canUpgrade ? 'data-id="'.$lv['id'].'" data-diff="'.$lv['diff_price'].'" data-name="'.htmlspecialchars($lv['name']).'"' : '' ?>>
                <?php if ($isCurrent): ?>
                    <span class="mup-card-tag tag-current">当前等级</span>
                <?php elseif ($canUpgrade): ?>
                    <span class="mup-card-tag tag-selected">已选择</span>
                <?php endif; ?>
                <div class="mup-card-name"><span class="mup-card-icon"><?= $_lvIconImage !== '' ? '<img src="' . htmlspecialchars($_lvIconImage) . '" alt="">' : '<i class="' . htmlspecialchars($_lvIcon) . '"></i>' ?></span><?= htmlspecialchars($lv['name']) ?></div>
                <div class="mup-card-desc"><?= htmlspecialchars($lv['description'] ?? '') ?></div>
                <div class="mup-card-price">¥<?= number_format($lv['price'], 2) ?><small>/ 永久</small></div>
                <?php if ($canUpgrade): ?>
                    <div class="mup-card-diff"><i class="fa fa-arrow-up"></i> 补 ¥<?= number_format($lv['diff_price'], 2) ?></div>
                <?php endif; ?>
                <div class="mup-perms">
                    <?php foreach ($permLabels as $field => $label): ?>
                        <div class="mup-perm">
                            <span><?= $label ?></span>
                            <?php if (($lv[$field] ?? 'n') === 'y'): ?>
                                <span class="mup-perm-yes"><i class="fa fa-check"></i></span>
                            <?php else: ?>
                                <span class="mup-perm-no"><i class="fa fa-times"></i></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($lv['has_auto_upgrade']) && $canUpgrade): ?>
                    <div class="mup-auto">
                        <div class="mup-auto-title"><i class="fa fa-arrow-up"></i> 自动升级 <span class="mup-auto-mode">（<?= htmlspecialchars($lv['upgrade_mode_text']) ?>）</span></div>
                        <div class="mup-auto-items">
                            <?php
                            $conditions = [
                                ['key'=>'upgrade_sales_amount','stat'=>'sales','label'=>'销售额','unit'=>'元','fmt'=>true],
                                ['key'=>'upgrade_order_count','stat'=>'orders','label'=>'订单量','unit'=>'单','fmt'=>false],
                                ['key'=>'upgrade_days','stat'=>'days','label'=>'运营天数','unit'=>'天','fmt'=>false],
                                ['key'=>'upgrade_sub_count','stat'=>'subs','label'=>'下级分店','unit'=>'店','fmt'=>false],
                            ];
                            foreach ($conditions as $c):
                                $target = $c['fmt'] ? (float)($lv[$c['key']] ?? 0) : (int)($lv[$c['key']] ?? 0);
                                if ($target <= 0) continue;
                                $current = $c['fmt'] ? (float)($myStats[$c['stat']] ?? 0) : (int)($myStats[$c['stat']] ?? 0);
                                $pct = min(100, round($current / $target * 100));
                            ?>
                            <div class="mup-auto-row">
                                <span class="mup-auto-label"><?= $c['label'] ?></span>
                                <div class="mup-auto-bar"><div class="mup-auto-fill" style="width:<?= $pct ?>%"></div></div>
                                <span class="mup-auto-num"><?= $c['fmt'] ? number_format($current,2) : $current ?>/<?= $c['fmt'] ? number_format($target,2) : $target ?><?= $c['unit'] ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="mup-bottom">
            <div class="mup-balance">账户余额：<strong>¥<?= number_format($_userBalance, 2) ?></strong></div>
            <button class="mup-btn" id="upgradeBtn" disabled>选择等级后升级</button>
        </div>
    </div>
</div>

<script>
layui.use(['layer','jquery'], function(){
    var $ = layui.$;
    var layer = layui.layer;
    var selectedId = '', selectedDiff = 0, selectedName = '';

    $('.mup-card.can-upgrade').click(function(){
        $('.mup-card.can-upgrade').removeClass('is-active');
        $(this).addClass('is-active');
        selectedId = $(this).data('id');
        selectedDiff = parseFloat($(this).data('diff'));
        selectedName = $(this).data('name');
        var label = selectedDiff > 0 ? '确认升级（补 ¥' + selectedDiff.toFixed(2) + '）' : '确认升级（免费）';
        $('#upgradeBtn').prop('disabled', false).text(label);
    });

    $('#upgradeBtn').click(function(){
        if (!selectedId) return;
        var msg = '确定升级至「' + selectedName + '」？';
        if (selectedDiff > 0) msg += '\n将扣除 ¥' + selectedDiff.toFixed(2);
        layer.confirm(msg, {
            btn: ['确认升级','取消'],
            title: '升级确认'
        }, function(ci){
            layer.close(ci);
            var $btn = $('#upgradeBtn');
            $btn.prop('disabled', true).text('处理中...');
            var li = layer.load(2, {shade:[0.3,'#000']});
            $.ajax({
                type:'POST', url:'?action=upgrade_ajax',
                data:{ id:selectedId, token:'<?= LoginAuth::genToken() ?>' },
                dataType:'json',
                success:function(e){
                    layer.close(li);
                    if(e.code==400){ layer.msg(e.msg,{icon:2}); return; }
                    layer.alert(e.msg,{ icon:1, title:'升级成功', btn:['确定'], yes:function(){ location.href='station.php'; } });
                },
                error:function(xhr){
                    layer.close(li);
                    var m='升级失败'; try{m=JSON.parse(xhr.responseText).msg||m;}catch(ex){} layer.msg(m,{icon:2});
                },
                complete:function(){ $btn.prop('disabled',false).text('选择等级后升级'); }
            });
        });
    });
});
</script>
