<?php
defined('DC_ROOT') || exit('access denied!');

$levelStatus = isset($status) && is_array($status) ? $status : [];
$levelList = isset($levels) && is_array($levels) ? $levels : [];
$levelOrderList = isset($levelOrders) && is_array($levelOrders) ? $levelOrders : [];
$paidFlag = !empty($paid);
$walletBalance = isset($user['money']) ? (float)$user['money'] : 0;
$virtualCurrencyNameEsc = htmlspecialchars(getVirtualCurrencyName(), ENT_QUOTES, 'UTF-8');
$currentNameRaw = $levelStatus['level_name'] ?? '普通用户';
$currentName = htmlspecialchars($currentNameRaw);
$imgBase = DC_URL . 'content/user_templates/default/img/';
$badgeImg = $imgBase . 'fenzhan.png';
$activeIndex = 0;
$selectedIndex = 0;
$nextLevel = null;
$lvNameMap = [];
foreach ($levelList as $i => $lv) {
    $lvNameMap[intval($lv['id'])] = $lv['name'];
    if (!empty($lv['is_current'])) $activeIndex = $i;
}
foreach ($levelList as $i => $lv) {
    if ($i > $activeIndex && !empty($lv['can_purchase'])) { $nextLevel = $lv; $selectedIndex = $i; break; }
}
$activeLevel = !empty($levelList) ? $levelList[$activeIndex] : [];
$_stationModel = new Station_Model();
$_allStationLevels = $_stationModel->getAllLevels(true);
$_permFields = [
    ['field' => '_always',         'icon' => 'ri-share-forward-line',  'label' => '商品分销'],
    ['field' => '_always',         'icon' => 'ri-coupon-line',         'label' => '商品优惠'],
    ['field' => '_always',         'icon' => 'ri-customer-service-2-line', 'label' => '专属客服'],
    ['field' => '_always',         'icon' => 'ri-gift-line',           'label' => '专享活动'],
    ['field' => '_always',         'icon' => 'ri-sparkling-2-line',    'label' => '更多权益'],
    ['field' => '_withdraw',       'icon' => 'ri-bank-card-line',      'label' => '余额提现'],
    ['field' => '_can_open',       'icon' => 'ri-store-2-line',        'label' => '开通分店'],
    ['field' => 'perm_config',     'icon' => 'ri-settings-3-line',     'label' => '分店配置'],
    ['field' => 'perm_setprice',   'icon' => 'ri-price-tag-3-line',    'label' => '自定义价格'],
    ['field' => 'perm_goodsstate', 'icon' => 'ri-shopping-bag-line',   'label' => '商品上下架'],
    ['field' => 'perm_tpl',        'icon' => 'ri-layout-4-line',       'label' => '分店模板'],
    ['field' => 'is_domain',       'icon' => 'ri-global-line',         'label' => '分店域名'],
];
$_lvPerksMap = [];
foreach ($levelList as $_lvi) {
    $_lid = intval($_lvi['id']);
    $_unlocked = ['_can_open' => false, '_withdraw' => Level_Service::isLevelAtLeast($_lid, Level_Service::getSetting(Level_Service::OPT_DEPOSIT_GRADE, 0)), '_always' => true];
    foreach ($_allStationLevels as $_sl) {
        $_gate = intval($_sl['member_gate'] ?? 0);
        if ($_gate > 0 && !Level_Service::isLevelAtLeast($_lid, $_gate)) continue;
        $_unlocked['_can_open'] = true;
        foreach (['perm_config','perm_setprice','perm_goodsstate','perm_tpl','is_domain'] as $_f) {
            if (($_sl[$_f] ?? 'n') === 'y') $_unlocked[$_f] = true;
        }
    }
    $_perks = [];
    foreach ($_permFields as $_pd) {
        $_perks[] = [
            'icon'  => $_pd['icon'],
            'label' => $_pd['label'],
            'ok'    => !empty($_unlocked[$_pd['field']]),
        ];
    }
    $_lvPerksMap[$_lid] = $_perks;
}
$nextName = $nextLevel ? $nextLevel['name'] : '最高等级';
$levelTotal = max(1, count($levelList));
$progressPercent = $levelTotal > 1 ? min(100, max(8, round(($activeIndex + 1) / $levelTotal * 100))) : 100;
$upgradeHint = '';
if ($nextLevel && isset($memberModel) && $memberModel instanceof Member_Model) {
    $nDirect  = (int)($nextLevel['upgrade_direct_count'] ?? 0);
    $nConsume = (float)($nextLevel['upgrade_consume_amount'] ?? 0);
    $nTeam    = (int)($nextLevel['upgrade_team_count'] ?? 0);
    $parts = [];
    $pcts = [];
    if ($nDirect > 0) {
        $curDirect = $memberModel->countDirectFans(UID);
        $pcts[] = min(100, round($curDirect / $nDirect * 100));
        $parts[] = '直推 ' . $curDirect . '/' . $nDirect . ' 人';
    }
    if ($nConsume > 0) {
        $curConsume = $memberModel->sumConsumeAmount(UID);
        $pcts[] = min(100, round($curConsume / $nConsume * 100));
        $parts[] = '消费 ¥' . number_format($curConsume, 0) . '/¥' . number_format($nConsume, 0);
    }
    if ($nTeam > 0) {
        $curTeam = $memberModel->countTeamFans(UID);
        $pcts[] = min(100, round($curTeam / $nTeam * 100));
        $parts[] = '团队 ' . $curTeam . '/' . $nTeam . ' 人';
    }
    if (!empty($parts)) {
        $mode = ($nextLevel['upgrade_mode'] ?? 'any') === 'all' ? 'all' : 'any';
        $progressPercent = $mode === 'all' ? min($pcts) : max($pcts);
        $upgradeHint = ($mode === 'all' ? '需全部满足免费升级：' : '任一达标免费升级：') . implode('，', $parts);
    }
}
?>
<style>
.lvx-page{min-height:100vh;background:#f7f8fb;color:#273043;padding-bottom:calc(26px + env(safe-area-inset-bottom));}
.uc-site-footer{display:none!important;}
.lvx-modal-mask{position:fixed;inset:0;z-index:19999;background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:opacity .25s,visibility .25s;}
.lvx-modal-mask.is-show{opacity:1;visibility:visible;}
.lvx-modal{width:min(88vw,340px);background:#fff;border-radius:20px;overflow:hidden;transform:translateY(24px) scale(.96);transition:transform .28s cubic-bezier(.22,.61,.36,1);box-shadow:0 20px 50px rgba(0,0,0,.18);}
.lvx-modal-mask.is-show .lvx-modal{transform:translateY(0) scale(1);}
.lvx-modal-header{padding:22px 22px 0;text-align:center;}
.lvx-modal-icon{width:52px;height:52px;margin:0 auto 12px;border-radius:50%;background:linear-gradient(135deg,#f8dfad,#e8bb72);display:flex;align-items:center;justify-content:center;font-size:24px;color:#7a5a2b;}
.lvx-modal-title{font-size:17px;font-weight:800;color:#252d3b;}
.lvx-modal-body{padding:16px 22px 6px;text-align:center;}
.lvx-modal-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f2f4f7;font-size:13px;color:#5f6673;}
.lvx-modal-row:last-child{border-bottom:none;}
.lvx-modal-row b{color:#252d3b;font-weight:700;}.lvx-modal-row .lvx-price{color:#c88332;font-weight:800;font-size:15px;}
.lvx-modal-foot{display:flex;gap:10px;padding:10px 22px 22px;}
.lvx-modal-btn{flex:1;height:44px;border:none;border-radius:14px;font-size:15px;font-weight:700;cursor:pointer;transition:.15s;}
.lvx-modal-cancel{background:#f3f4f6;color:#5f6673;}.lvx-modal-cancel:active{background:#e8ebf0;}
.lvx-modal-confirm{background:linear-gradient(135deg,#f1ca86,#e4ad5e);color:#3d2a13;box-shadow:0 6px 16px rgba(222,174,92,.22);}.lvx-modal-confirm:active{transform:scale(.97);}
.lvx-shell{padding:14px 14px 0;}
.lvx-hero{position:relative;border-radius:16px;padding:18px 18px 16px;background:url('<?= DC_URL ?>content/user_templates/default/img/grzx_hy_bj_v2.png') center/100% 100% no-repeat,linear-gradient(135deg,#1e1e2e 0%,#2a2a3d 50%,#1e1e2e 100%);color:#fff;overflow:hidden;box-shadow:0 8px 28px rgba(0,0,0,.18);}
.lvx-hero::before{content:'';position:absolute;top:-36px;right:-24px;width:120px;height:120px;border-radius:50%;background:radial-gradient(circle,rgba(200,180,150,.08) 0%,transparent 70%);}
.lvx-hero-top{display:flex;align-items:center;gap:12px;position:relative;z-index:1;}
.lvx-avatar{width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,#f8dfad,#e8bb72);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;border:1px solid rgba(255,255,255,.28);}
.lvx-avatar img{width:100%;height:100%;object-fit:cover;}.lvx-avatar span{font-size:20px;font-weight:800;color:#755427;}
.lvx-user{flex:1;min-width:0}.lvx-user-name{font-size:17px;font-weight:800;line-height:1.2;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}.lvx-user-sub{font-size:12px;color:rgba(255,255,255,.72);margin-top:5px;}
.lvx-chip{height:28px;padding:0 10px;border-radius:14px;background:rgba(255,255,255,.12);display:flex;align-items:center;gap:5px;font-size:12px;color:#f5dcae;}
.lvx-metrics{display:flex;align-items:center;gap:24px;margin-top:3px;position:relative;z-index:1;}
.lvx-metric{display:flex;align-items:baseline;gap:5px;}.lvx-metric-k{font-size:12px;color:rgba(255,255,255,.5);}.lvx-metric-v{font-size:14px;font-weight:800;color:#f5dcae;}
.lvx-paid{margin:12px 0 0;padding:10px 12px;border-radius:13px;background:#ecfdf5;color:#047857;font-size:13px;display:flex;align-items:center;gap:6px;}
.lvx-section{margin-top:18px;}.lvx-title-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:11px;}.lvx-title{font-size:16px;font-weight:800;color:#252d3b;}.lvx-sub-link{font-size:12px;color:#9a7440;}
.lvx-level-strip{display:flex;gap:10px;overflow-x:auto;padding:2px 1px 5px;-webkit-overflow-scrolling:touch;scrollbar-width:none}.lvx-level-strip::-webkit-scrollbar{display:none;}
.lvx-level{flex:0 0 100px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;border-radius:10px;padding:14px 8px 12px;text-align:center;position:relative;cursor:pointer;transition:.2s;box-shadow:var(--shadow-primary);display:flex;flex-direction:column;align-items:center;}
.lvx-level.is-active{border-color:#e5bd7b;background:linear-gradient(180deg,#fffaf1 0%,#fff 100%);transform:translateY(-2px);box-shadow:var(--shadow-primary);}
.lvx-level.is-disabled{opacity:.58;}.lvx-level.is-disabled.is-active{opacity:.72;}
.lvx-level-icon{width:34px;height:34px;border-radius:50%;background:#f8ecd8;color:#c88332;display:flex;align-items:center;justify-content:center;overflow:hidden;margin-bottom:6px;font-size:19px;}.lvx-level-icon img{width:100%;height:100%;object-fit:cover;}
.lvx-level-row{display:flex;align-items:center;gap:3px;margin-bottom:2px;}.lvx-lv-tag{font-size:9px;color:#d49337;font-weight:800;}.lvx-level-name{font-size:11px;color:#2f3746;font-weight:700;line-height:1.2;}.lvx-level-dur{font-size:10px;color:#b0b5be;margin-bottom:3px;}.lvx-level-foot{font-size:12px;color:#9ca3af;font-weight:600;}.lvx-level-foot.owned{color:#c88332;font-weight:800;}
.lvx-action-card{margin-top:12px;}
.lvx-desc-card{background:linear-gradient(0deg, #fff, #f3f5f8);border-radius:10px;padding:14px 15px;border:2px solid #fff;font-size:12px;color:#77808f;line-height:1.8;box-shadow: var(--shadow-primary);}.lvx-desc-section{margin-top:18px;}.lvx-desc-section.is-empty{display:none;box-shadow: var(--shadow-primary);}
.lvx-auto{margin-top:10px;padding:10px 12px;border-radius:13px;background:#ecfdf5;color:#047857;border:1px solid #d1fae5;}.lvx-auto-title{display:flex;align-items:center;gap:5px;margin-bottom:4px;color:#065f46;font-size:12px;font-weight:900;}.lvx-auto-title span{padding:1px 6px;border-radius:999px;background:#d1fae5;color:#047857;font-size:10px;font-weight:800;}
.lvx-upgrade-btn{width:100%;height:44px;border:none;border-radius:16px;background:linear-gradient(135deg,#f1ca86,#e4ad5e);color:#3d2a13;font-size:15px;font-weight:800;box-shadow:0 8px 18px rgba(222,174,92,.18);}.lvx-upgrade-btn:disabled{background:#e8ebf0;color:#9ca3af;box-shadow:none;}
.lvx-perks{display:grid;grid-template-columns:repeat(4,1fr);gap:13px 8px;background:linear-gradient(0deg, #fff, #f3f5f8);border-radius:10px;padding:16px 12px;border:2px solid #fff;box-shadow: var(--shadow-primary);}
.lvx-perk{text-align:center}.lvx-perk-ico{width:38px;height:38px;margin:0 auto 7px;border-radius:15px;background:linear-gradient(135deg,#f4d391,#eabc72);color:#fff;display:flex;align-items:center;justify-content:center;font-size:19px;box-shadow:0 5px 12px rgba(214,169,92,.2);}.lvx-perk-txt{font-size:11px;color:#5f6673;white-space:nowrap;}
.lvx-perk-off .lvx-perk-ico{background:#e0e3e8;color:#b0b5be;box-shadow:none;}.lvx-perk-off .lvx-perk-txt{color:#b0b5be;}
.lvx-hero-progress{margin-top:13px;padding-top:8px;border-top:1px solid rgba(212,196,168,.12);position:relative;z-index:1;}.lvx-hero-progress-top{display:flex;align-items:center;justify-content:space-between;gap:10px;}.lvx-hero-progress-title{font-size:12px;color:#e8dcc8;font-weight:700;}.lvx-hero-progress-next{font-size:11px;color:#8a8396;white-space:nowrap;}.lvx-progress-line{height:6px;background:rgba(255,255,255,.1);border-radius:6px;margin:10px 0 8px;overflow:hidden}.lvx-progress-fill{height:100%;width:<?= $progressPercent ?>%;background:linear-gradient(90deg,#d4c4a8,#bfa97e);border-radius:6px;}.lvx-progress-meta{display:flex;align-items:center;justify-content:space-between;font-size:11px;color:#8a8396;}.lvx-hero-mini-btn{height:28px;border:none;border-radius:999px;background:rgba(212,196,168,.18);color:#d4c4a8;padding:0 12px;font-size:12px;font-weight:700;}
.lvx-upgrade-hint{margin-top:7px;font-size:11px;color:#6b6580;line-height:1.5;position:relative;z-index:1;}
.lvx-record{background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;border-radius:10px;padding:14px 15px;box-shadow: var(--shadow-primary);}.lvx-record-item{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f2f4f7;}.lvx-record-item:last-child{border-bottom:0}.lvx-record-left{font-size:12px;color:#7a8290;}.lvx-record-left b{display:block;color:#303846;font-size:13px;margin-bottom:3px;}.lvx-record-amt{font-size:14px;font-weight:800;color:#c88332;text-align:right;}.lvx-record-state{font-size:11px;color:#8b95a5;margin-top:3px;text-align:right;}.lvx-empty{text-align:center;color:#a0a7b4;font-size:13px;padding:18px 0;}
.lvx-empty{padding:60px 16px;color:#bbb;font-size:13px;}
.lvx-empty svg{display:block;width:140px;height:auto;margin:0 auto 10px;}
</style>
<main class="lvx-page">
    <div class="lvx-shell">
        <section class="lvx-hero">
            <div class="lvx-hero-top">
                <?php $av = !empty($user['photo']) ? User::getAvatar($user['photo']) : ''; ?>
                <div class="lvx-avatar"><?php if($av): ?><img src="<?= $av ?>" alt=""><?php else: ?><span><?= function_exists('mb_substr') ? mb_substr($user['nickname'] ?? $user['username'] ?? '?', 0, 1, 'UTF-8') : '?' ?></span><?php endif; ?></div>
                <div class="lvx-user"><div class="lvx-user-name"><?= htmlspecialchars($user['nickname'] ?? $user['username'] ?? '') ?></div><div class="lvx-metrics"><div class="lvx-metric"><div class="lvx-metric-k">余额</div><div class="lvx-metric-v">¥<?= number_format($walletBalance, 2) ?></div></div><div class="lvx-metric"><div class="lvx-metric-k"><?= $virtualCurrencyNameEsc ?></div><div class="lvx-metric-v"><?= intval($user['credits'] ?? 0) ?></div></div></div></div>
                <div class="lvx-chip"><i class="ri-time-line"></i> <?= htmlspecialchars($levelStatus['expire_text'] ?? '永久有效') ?></div>
            </div>
            <div class="lvx-hero-progress">
                <div class="lvx-hero-progress-top">
                    <div class="lvx-hero-progress-title"><i class="fa fa-diamond"></i> 当前等级 <?= $currentName ?></div>
                    <div class="lvx-hero-progress-next">下一档：<?= htmlspecialchars($nextName) ?></div>
                </div>
                <div class="lvx-progress-line"><div class="lvx-progress-fill"></div></div>
                <?php if (!empty($upgradeHint)): ?>
                <div class="lvx-upgrade-hint"><?= htmlspecialchars($upgradeHint) ?></div>
                <?php endif; ?>
                <div class="lvx-progress-meta"></div>
            </div>
        </section>
        <?php if ($paidFlag): ?><div class="lvx-paid"><i class="ri-checkbox-circle-line"></i> 开通成功，会员等级已更新。</div><?php endif; ?>
        <section class="lvx-section">
            <div class="lvx-title-row"><div class="lvx-title">选择等级</div></div>
            <div class="lvx-level-strip" id="lvxLevels">
                <?php foreach ($levelList as $idx => $lv):
                    $isCurrent = !empty($lv['is_current']);
                    $showPrice = floatval($lv['display_price'] ?? $lv['open_price'] ?? $lv['price'] ?? 0);
                    $payAmount = floatval($lv['pay_amount'] ?? $showPrice);
                    $canPurchase = !empty($lv['can_purchase']);
                    $_isOwned = $isCurrent || !$canPurchase;
                    $label = !empty($lv['purchase_label']) ? $lv['purchase_label'] : '开通';
                    $desc = trim((string)($lv['content'] ?? ''));
                    $_upgDirect = (int)($lv['upgrade_direct_count'] ?? 0);
                    $_upgConsume = (float)($lv['upgrade_consume_amount'] ?? 0);
                    $_upgTeam = (int)($lv['upgrade_team_count'] ?? 0);
                    $_hasUpgCond = ($_upgDirect > 0 || $_upgConsume > 0 || $_upgTeam > 0);
                    $_upgModeText = '';
                    $_upgDesc = '';
                    if ($_hasUpgCond) {
                        $_upgModeText = (($lv['upgrade_mode'] ?? 'any') === 'all') ? '全部满足' : '任一满足';
                        $_upgDescLines = [];
                        if ($_upgDirect > 0) $_upgDescLines[] = '直推邀请 ' . $_upgDirect . ' 人';
                        if ($_upgConsume > 0) $_upgDescLines[] = '累计消费 ¥' . number_format($_upgConsume, 0);
                        if ($_upgTeam > 0) $_upgDescLines[] = '团队人数 ' . $_upgTeam . ' 人';
                        $_upgDesc = implode('、', $_upgDescLines);
                    }
                    $_dur = intval($lv['duration_days'] ?? 0);
                    $_durText = $_dur > 0 ? ($_dur >= 365 ? intval($_dur / 365) . '年' : $_dur . '天') : '永久';
                    $_icon = !empty($lv['icon']) ? (string)$lv['icon'] : 'ri-vip-diamond-line';
                    $_iconImg = !empty($lv['icon_image']) ? (string)$lv['icon_image'] : '';
                ?>
                <div class="lvx-level<?= $idx === $selectedIndex ? ' is-active' : '' ?><?= $_isOwned ? ' is-disabled' : '' ?>" data-id="<?= intval($lv['id']) ?>" data-name="<?= htmlspecialchars($lv['name']) ?>" data-price="<?= htmlspecialchars((string)$showPrice) ?>" data-pay="<?= htmlspecialchars((string)$payAmount) ?>" data-can="<?= $canPurchase ? '1' : '0' ?>" data-current="<?= $isCurrent ? '1' : '0' ?>" data-owned="<?= $_isOwned ? '1' : '0' ?>" data-label="<?= htmlspecialchars($label) ?>" data-desc="<?= htmlspecialchars($desc) ?>" data-upgrade="<?= htmlspecialchars($_upgDesc) ?>" data-upgrade-mode="<?= htmlspecialchars($_upgModeText) ?>" data-icon="<?= htmlspecialchars($_icon) ?>" data-icon-image="<?= htmlspecialchars($_iconImg) ?>" data-perks="<?= htmlspecialchars(json_encode($_lvPerksMap[intval($lv['id'])] ?? [], JSON_UNESCAPED_UNICODE)) ?>">
                    <div class="lvx-level-icon"><?php if($_iconImg): ?><img src="<?= htmlspecialchars($_iconImg) ?>" alt=""><?php else: ?><i class="<?= htmlspecialchars($_icon) ?>"></i><?php endif; ?></div><div class="lvx-level-row"><span class="lvx-lv-tag">Lv<?= $idx + 1 ?></span><span class="lvx-level-name"><?= htmlspecialchars($lv['name']) ?></span></div><div class="lvx-level-dur"><?= $_durText ?></div>
                    <?php if($_isOwned): ?><div class="lvx-level-foot owned">已拥有</div><?php else: ?><div class="lvx-level-foot">¥<?= number_format($showPrice, 2) ?></div><?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($levelList)): ?><div class="lvx-action-card" id="lvxActionCard"></div><?php endif; ?>
        </section>
        <section class="lvx-desc-section is-empty" id="lvxDescSection"><div class="lvx-title-row"><div class="lvx-title">等级说明</div></div><div class="lvx-desc-card" id="lvxDescCard"></div>
        </section>
        <section class="lvx-section"><div class="lvx-title-row"><div class="lvx-title">会员权益</div></div><div class="lvx-perks" id="lvxPerks"></div></section>
        <section class="lvx-section"><div class="lvx-title-row"><div class="lvx-title">开通记录</div></div><div class="lvx-record">
            <?php if (empty($levelOrderList)): ?><div class="lvx-empty"><?php include __DIR__ . '/_svg_empty.php'; ?>暂无开通记录</div><?php else: ?><?php foreach ($levelOrderList as $row): $typeMap=['open'=>'开通','renew'=>'续费','upgrade'=>'升级'];$stateMap=[0=>'待支付',1=>'已完成',-1=>'已取消'];$typeText=$typeMap[$row['purchase_type']]??$row['purchase_type'];$stateText=$stateMap[intval($row['state'])]??'未知'; ?>
            <div class="lvx-record-item"><div class="lvx-record-left"><b><?= $typeText ?>：<?= htmlspecialchars($lvNameMap[intval($row['level_id'])] ?? 'ID:' . intval($row['level_id'])) ?></b><?= !empty($row['create_time']) ? date('Y-m-d H:i', $row['create_time']) : '--' ?></div><div><div class="lvx-record-amt">¥<?= number_format(floatval($row['amount']), 2) ?></div><div class="lvx-record-state"><?= $stateText ?></div></div></div>
            <?php endforeach; ?><?php endif; ?>
        </div></section>
    </div>
</main>
<div class="lvx-modal-mask" id="lvxModal">
    <div class="lvx-modal">
        <div class="lvx-modal-header">
            <div class="lvx-modal-icon"><i class="ri-vip-diamond-line"></i></div>
            <div class="lvx-modal-title">升级会员等级</div>
        </div>
        <div class="lvx-modal-body">
            <div class="lvx-modal-row"><span>目标等级</span><b class="lvx-modal-level">-</b></div>
            <div class="lvx-modal-row"><span>扣除钱包</span><span class="lvx-price lvx-modal-pay">¥0.00</span></div>
        </div>
        <div class="lvx-modal-foot">
            <button class="lvx-modal-btn lvx-modal-cancel">取消</button>
            <button class="lvx-modal-btn lvx-modal-confirm">确定升级</button>
        </div>
    </div>
</div>
<script>
layui.use(['layer','jquery'],function(){
    var $=layui.$,layer=layui.layer,token='<?= LoginAuth::genToken() ?>',submitting=false,selected=$('#lvxLevels .lvx-level.is-active');
    function escapeHtml(str){return $('<div/>').text(str==null?'':String(str)).html();}
    function renderAction($el){
        selected=$el;$('#lvxLevels .lvx-level').removeClass('is-active');$el.addClass('is-active');
        var pay=parseFloat($el.data('pay')||0),desc=$el.attr('data-desc')||'升级后可获得更完整的用户权益与经营能力。',upgrade=$el.attr('data-upgrade')||'',upgradeMode=$el.attr('data-upgrade-mode')||'',cur=$el.data('current')==1||$el.data('current')==='1',owned=$el.data('owned')==1||$el.data('owned')==='1',can=$el.data('can')==1||$el.data('can')==='1',label=$el.data('label')||'立即升级';
        var h='<button class="lvx-upgrade-btn" id="lvxUpgradeBtn" '+((cur||owned||!can)?'disabled':'')+'>'+((cur||owned)?'已拥有':label)+'</button>';
        $('#lvxActionCard').html(h);
        var dh=desc?'<div>'+escapeHtml(desc).replace(/\n/g,'<br>')+'</div>':'';
        if(upgrade){dh+='<div class="lvx-auto"><div class="lvx-auto-title"><i class="fa fa-bolt"></i> 达标自动升级会员等级'+(upgradeMode?'<span>'+escapeHtml(upgradeMode)+'</span>':'')+'</div><div>'+escapeHtml(upgrade)+'</div></div>';}
        if(dh){$('#lvxDescCard').html(dh);$('#lvxDescSection').removeClass('is-empty');}else{$('#lvxDescCard').html('');$('#lvxDescSection').addClass('is-empty');}
        var perks=$el.data('perks')||[];perks.sort(function(a,b){return (b.ok?1:0)-(a.ok?1:0);});var ph='';
        for(var i=0;i<perks.length;i++){var p=perks[i]||{},cls=p.ok?'':'lvx-perk-off';ph+='<div class="lvx-perk '+cls+'"><div class="lvx-perk-ico"><i class="'+escapeHtml(p.icon||'')+'"></i></div><div class="lvx-perk-txt">'+escapeHtml(p.label||'')+'</div></div>';}
        $('#lvxPerks').html(ph);
    }
    $('#lvxLevels .lvx-level').on('click',function(){renderAction($(this));});
    if(selected.length){renderAction(selected);var strip=$('#lvxLevels')[0],card=selected[0];if(strip&&card){strip.scrollLeft=card.offsetLeft-strip.offsetWidth/2+card.offsetWidth/2;}}
    function submitUpgrade(levelId){if(submitting)return;submitting=true;var li=layer.load(2,{shade:[.2,'#000']});$.ajax({type:'POST',url:'?action=upgrade_ajax',dataType:'json',data:{level_id:levelId,token:token},success:function(res){layer.close(li);if(res.code==200){layer.msg('开通成功',{icon:1,time:1200});setTimeout(function(){location.href='/user/level.php?paid=1';},900);return;}if(res.code==402){var d=res.data||{},h='<div style="line-height:1.8;">余额：<b style="color:#ef4444;">¥'+parseFloat(d.balance||0).toFixed(2)+'</b><br>需要：<b style="color:#c88332;">¥'+parseFloat(d.required||0).toFixed(2)+'</b><br>差额：<b style="color:#ef4444;">¥'+parseFloat(d.shortage||0).toFixed(2)+'</b><br>是否前往充值？</div>';layer.confirm(h,{title:'余额不足',btn:['去充值','取消']},function(){location.href=d.redirect||'/user/balance.php';});return;}layer.msg(res.msg||'提交失败',{icon:2});},error:function(){layer.close(li);layer.msg('请求失败',{icon:2});},complete:function(){submitting=false;}});}
    function showUpgradeModal(id,name,pay){var m=$('#lvxModal');m.find('.lvx-modal-level').text(name);m.find('.lvx-modal-pay').text('¥'+pay.toFixed(2));var iconImg=selected.data('icon-image')||'',iconCls=selected.data('icon')||'ri-vip-diamond-line';m.find('.lvx-modal-icon').html(iconImg?'<img src="'+iconImg+'" style="width:100%;height:100%;object-fit:cover;border-radius:50%">':'<i class="'+iconCls+'"></i>');m.data('lid',id).addClass('is-show');}
    function hideModal(){$('#lvxModal').removeClass('is-show');}
    $(document).on('click','#lvxUpgradeBtn,#lvxMiniBtn',function(){if(!selected.length)return;var cur=selected.data('current')==1||selected.data('current')==='1',owned=selected.data('owned')==1||selected.data('owned')==='1',can=selected.data('can')==1||selected.data('can')==='1';if(cur||owned||!can)return;var id=parseInt(selected.data('id'),10),name=selected.data('name'),pay=parseFloat(selected.data('pay')||0);showUpgradeModal(id,name,pay);});
    $(document).on('click','.lvx-modal-cancel, .lvx-modal-mask',function(e){if(e.target===this)hideModal();});
    $(document).on('click','.lvx-modal-confirm',function(){var id=$('#lvxModal').data('lid');hideModal();submitUpgrade(id);});
});
</script>
<script>$('#menu-level').addClass('menu-current');</script>
