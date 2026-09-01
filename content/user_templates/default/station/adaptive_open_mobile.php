<?php
defined('DC_ROOT') || exit('access denied!');
$_stationList = isset($station) && is_array($station) ? array_values($station) : [];
$_walletBalance = isset($userData['money']) ? (float)$userData['money'] : 0;
$_hasStation = !empty($userData['station']);
$_currentStationLevelId = $_hasStation ? intval($userData['station']['level_id'] ?? 0) : 0;
$_upgradePriceMode = Option::get('station_upgrade_price_mode') ?: 'diff';
$_currentStationLevel = null;
foreach ($_stationList as $_row) {
    if ($_currentStationLevelId > 0 && intval($_row['id']) === $_currentStationLevelId) {
        $_currentStationLevel = $_row;
        break;
    }
}
if ($_hasStation && empty($_currentStationLevel) && $_currentStationLevelId > 0) {
    $_currentStationLevel = Database::getInstance()->once_fetch_array("SELECT * FROM " . DB_PREFIX . "station_level WHERE id={$_currentStationLevelId}");
}
$_currentStationSort = $_currentStationLevel ? intval($_currentStationLevel['sort'] ?? 0) : -1;
$_currentStationPrice = $_currentStationLevel ? (float)($_currentStationLevel['price'] ?? 0) : 0;
$_currentStationName = $_hasStation ? ($_currentStationLevel['name'] ?? ($userData['station']['name'] ?? '已开通分店')) : '未开通分店';
$_currentStationIcon = $_hasStation ? trim((string)($_currentStationLevel['icon'] ?? '')) : 'ri-store-2-line';
if ($_currentStationIcon === '') $_currentStationIcon = 'ri-store-2-line';
$_currentStationIconImage = $_hasStation ? trim((string)($_currentStationLevel['icon_image'] ?? '')) : '';
$_stationCreateTime = ($_hasStation && !empty($userData['station']['create_time'])) ? (int)$userData['station']['create_time'] : 0;
$_stationOpenDays = $_stationCreateTime > 0 ? max(1, (int)floor((time() - $_stationCreateTime) / 86400) + 1) : 0;
$_userLevelSort = 0;
if (class_exists('Level_Service')) {
    $_activeLevelId = (int)Level_Service::getActiveLevelId($userData);
    if ($_activeLevelId > 0) {
        $_levelRow = Database::getInstance()->once_fetch_array("SELECT sort FROM " . DB_PREFIX . "member WHERE id={$_activeLevelId}");
        $_userLevelSort = $_levelRow ? (int)$_levelRow['sort'] : 0;
    }
}
$_permLabels = Station_Model::PERM_MAP;
$_permIcons = [
    'perm_config' => 'fa-cog',
    'perm_setprice' => 'fa-tags',
    'perm_goodsstate' => 'fa-shopping-bag',
    'perm_tpl' => 'fa-th-large',
    'is_domain' => 'fa-globe',
    'is_index' => 'fa-home',
    'is_notice' => 'fa-bullhorn',
    'is_slide' => 'fa-image',
    'is_service' => 'fa-headphones',
];
$_planColors = [
    ['#f7d9a1', '#d39a45'],
    ['#c7b8ff', '#7c5cff'],
    ['#9bd3ff', '#2f75ff'],
    ['#99f0cb', '#10b981'],
    ['#ffca9a', '#f97316'],
    ['#ffacc8', '#ec4899'],
    ['#f7d47a', '#d99a12'],
];
?>
<style>
    *{box-sizing:border-box}.uc-site-footer{display:none!important}.mopen-page,.mopen-page *{-webkit-tap-highlight-color:transparent}.mopen-page{--mopen-primary:var(--theme-primary,#667eea);--mopen-primary-rgb:var(--tp-rgb,102,126,234);min-height:100vh;background:#f5f5f6;color:#273043;padding-bottom:calc(28px + env(safe-area-inset-bottom,0px));font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif}.mopen-shell{padding:12px 12px 0}
    .mopen-hero{position:relative;overflow:hidden;border-radius:18px;padding:18px 18px 16px;color:#fff;background:url('<?= DC_URL ?>content/user_templates/default/img/grzx_hy_bj_v.png') center/100% 100% no-repeat,linear-gradient(135deg,#1e1e2e,#2a2a3d 52%,#1e1e2e);box-shadow:0 10px 28px rgba(0,0,0,.18)}.mopen-hero:before{content:'';position:absolute;top:-44px;right:-28px;width:142px;height:142px;border-radius:50%;background:radial-gradient(circle,rgba(248,223,173,.16),rgba(248,223,173,0) 72%)}.mopen-hero-top{position:relative;z-index:1;display:flex;align-items:center;gap:12px}.mopen-hero-icon{width:50px;height:50px;border-radius:18px;background:linear-gradient(135deg,#f8dfad,#e8bb72);color:#755427;display:flex;align-items:center;justify-content:center;font-size:22px;box-shadow:0 8px 18px rgba(232,187,114,.16);flex-shrink:0}.mopen-hero-main{flex:1;min-width:0}.mopen-hero-title{font-size:17px;line-height:1.25;font-weight:900;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.mopen-hero-desc{margin-top:5px;color:rgba(255,255,255,.70);font-size:12px;line-height:1.55}.mopen-wallet{flex-shrink:0;display:inline-flex;flex-direction:column;align-items:flex-end;gap:3px;min-width:78px;padding:8px 10px;border-radius:15px;background:rgba(255,255,255,.10);color:#f5dcae;text-decoration:none;border:1px solid rgba(255,255,255,.12)}.mopen-wallet span{color:rgba(255,255,255,.58);font-size:10px}.mopen-wallet b{color:#f5dcae;font-size:13px;line-height:1}.mopen-hero-stats{position:relative;z-index:1;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));margin-top:16px;padding-top:13px;border-top:1px solid rgba(212,196,168,.13)}.mopen-hero-stat{text-align:center;border-right:1px solid rgba(212,196,168,.13)}.mopen-hero-stat:last-child{border-right:0}.mopen-hero-stat span{display:block;color:rgba(255,255,255,.50);font-size:11px}.mopen-hero-stat b{display:block;margin-top:5px;color:#f5dcae;font-size:13px;font-weight:900}
    .mopen-wallet{align-items:center;justify-content:center;gap:4px;min-width:74px;min-height:58px;padding:8px 11px 9px;border-radius:15px;background:linear-gradient(145deg,rgba(255,255,255,.18),rgba(255,255,255,.06));border:1px solid rgba(245,220,174,.28);box-shadow:inset 0 1px 0 rgba(255,255,255,.18),0 8px 20px rgba(0,0,0,.12)}
    .mopen-wallet span{display:inline-flex;align-items:center;justify-content:center;gap:3px;color:rgba(255,255,255,.68);font-size:10px;font-weight:800;letter-spacing:.4px;white-space:nowrap}.mopen-wallet span i{color:#f5dcae;font-size:12px}
    .mopen-wallet b{display:flex;align-items:baseline;justify-content:center;margin-top:0;color:#ffe7b7;font-size:0;line-height:1;text-shadow:0 2px 10px rgba(245,220,174,.22)}.mopen-wallet b em{font-style:normal;font-size:18px;font-weight:900;letter-spacing:-.6px}.mopen-wallet b small{margin-left:2px;color:#f5dcae;font-size:12px;font-weight:900}.mopen-wallet b .mopen-wait{color:#f5dcae;font-size:13px;font-weight:900}
    .mopen-section{margin-top:18px}.mopen-title-row{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:11px}.mopen-title{color:#252d3b;font-size:16px;font-weight:900}.mopen-sub-link{color:#9a7440;font-size:12px;text-decoration:none}.mopen-plan-strip{display:flex;gap:10px;overflow-x:auto;padding:2px 1px 6px;-webkit-overflow-scrolling:touch;scrollbar-width:none}.mopen-plan-strip::-webkit-scrollbar{display:none}.mopen-plan{flex:0 0 108px;min-height:128px;padding:13px 9px 11px;border-radius:10px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;box-shadow:var(--shadow-primary);text-align:center;position:relative;cursor:pointer;transition:.18s}.mopen-plan:active{transform:scale(.97)}.mopen-plan.is-active{border-color:var(--mopen-card-accent,#e5bd7b);background:linear-gradient(180deg,#fffaf1,#fff);transform:translateY(-2px);box-shadow:var(--shadow-primary)}.mopen-plan.is-disabled{opacity:.58}.mopen-plan.is-disabled.is-active{opacity:.72}.mopen-plan-icon{width:36px;height:36px;margin:0 auto 7px;border-radius:50%;background:linear-gradient(135deg,var(--mopen-card-c1,#f7d9a1),var(--mopen-card-c2,#d39a45));color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;box-shadow:0 6px 14px rgba(214,169,92,.18)}.mopen-plan-row{display:flex;align-items:center;justify-content:center;gap:3px;margin-bottom:3px}.mopen-lv-tag{color:var(--mopen-card-accent,#d49337);font-size:9px;font-weight:900}.mopen-plan-name{max-width:72px;color:#2f3746;font-size:12px;line-height:1.2;font-weight:800;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.mopen-plan-price{margin-top:5px;color:#9ca3af;font-size:11px;line-height:1.3;font-weight:700}.mopen-plan-price b{color:var(--mopen-card-accent,#c88332);font-size:14px;font-weight:900}.mopen-plan-foot{margin-top:6px;color:#a0a7b4;font-size:10px;font-weight:800}.mopen-plan-foot.is-ok{color:var(--mopen-card-accent,#c88332)}.mopen-plan-mark{position:absolute;top:8px;right:8px;width:18px;height:18px;border-radius:50%;display:none;align-items:center;justify-content:center;background:var(--mopen-card-accent,#c88332);color:#fff;font-size:11px}.mopen-plan.is-active .mopen-plan-mark{display:flex}
    .mopen-action-card{margin-top:12px}.mopen-main-btn{width:100%;height:46px;border:0;border-radius:16px;background:linear-gradient(135deg,#f1ca86,#e4ad5e);color:#3d2a13;font-size:15px;font-weight:900;box-shadow:0 8px 18px rgba(222,174,92,.18);cursor:pointer}.mopen-main-btn:active{transform:scale(.98)}.mopen-main-btn:disabled{background:#e8ebf0;color:#9ca3af;box-shadow:none;transform:none}.mopen-desc-section.is-empty{display:none}.mopen-desc-card,.mopen-info,.mopen-empty{background:linear-gradient(0deg, #fff, #f3f5f8);border-radius:10px;border:2px solid #fff;box-shadow:var(--shadow-primary)}.mopen-desc-card{padding:14px 15px;color:#77808f;font-size:12px;line-height:1.8}.mopen-auto{margin-top:10px;padding:10px 12px;border-radius:13px;background:#ecfdf5;color:#047857;border:1px solid #d1fae5}.mopen-auto-title{display:flex;align-items:center;gap:5px;margin-bottom:4px;color:#065f46;font-size:12px;font-weight:900}.mopen-auto-title span{padding:1px 6px;border-radius:999px;background:#d1fae5;color:#047857;font-size:10px;font-weight:800}.mopen-perks{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px 8px;background:linear-gradient(0deg, #fff, #f3f5f8);border-radius:10px;padding:16px 12px;border:2px solid #fff;box-shadow:var(--shadow-primary)}.mopen-perk{text-align:center;min-width:0}.mopen-perk-ico{width:38px;height:38px;margin:0 auto 7px;border-radius:15px;background:linear-gradient(135deg,#f4d391,#eabc72);color:#fff;display:flex;align-items:center;justify-content:center;font-size:17px;box-shadow:0 5px 12px rgba(214,169,92,.20)}.mopen-perk-txt{color:#5f6673;font-size:11px;line-height:1.25;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.mopen-perk-val{margin-top:2px;color:#c88332;font-size:10px;font-weight:900}.mopen-perk-off .mopen-perk-ico{background:#e0e3e8;color:#b0b5be;box-shadow:none}.mopen-perk-off .mopen-perk-txt{color:#b0b5be}
    .mopen-info{padding:14px 15px}.mopen-info-row{display:flex;align-items:flex-start;gap:10px;padding:10px 0;border-bottom:1px solid #f2f4f7}.mopen-info-row:first-child{padding-top:0}.mopen-info-row:last-child{border-bottom:0;padding-bottom:0}.mopen-info-row i{width:26px;height:26px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(var(--mopen-primary-rgb),.08);color:var(--mopen-primary);flex-shrink:0;margin-top:1px}.mopen-info-row b{display:block;margin-bottom:2px;color:#303846;font-size:13px}.mopen-info-row p{margin:0;color:#7a8290;font-size:12px;line-height:1.6}.mopen-empty{padding:58px 16px;color:#a0a7b4;text-align:center;font-size:13px}.mopen-empty svg{display:block;width:136px;height:auto;margin:0 auto 10px}
    .mopen-modal-mask{position:fixed;inset:0;z-index:19999;background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:.25s}.mopen-modal-mask.is-show{opacity:1;visibility:visible}.mopen-modal{width:min(88vw,340px);background:#fff;border-radius:20px;overflow:hidden;transform:translateY(24px) scale(.96);transition:transform .28s cubic-bezier(.22,.61,.36,1);box-shadow:0 20px 50px rgba(0,0,0,.18)}.mopen-modal-mask.is-show .mopen-modal{transform:translateY(0) scale(1)}.mopen-modal-header{padding:22px 22px 0;text-align:center}.mopen-modal-icon{width:52px;height:52px;margin:0 auto 12px;border-radius:50%;background:linear-gradient(135deg,#f8dfad,#e8bb72);display:flex;align-items:center;justify-content:center;color:#7a5a2b;font-size:22px}.mopen-modal-title{color:#252d3b;font-size:17px;font-weight:900}.mopen-modal-body{padding:16px 22px 6px}.mopen-modal-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid #f2f4f7;color:#5f6673;font-size:13px}.mopen-modal-row:last-child{border-bottom:0}.mopen-modal-row b{color:#252d3b;font-weight:800;text-align:right}.mopen-modal-price{color:#c88332!important;font-size:15px;font-weight:900}.mopen-modal-foot{display:flex;gap:10px;padding:10px 22px 22px}.mopen-modal-btn{flex:1;height:44px;border:0;border-radius:14px;font-size:15px;font-weight:800;cursor:pointer}.mopen-modal-cancel{background:#f3f4f6;color:#5f6673}.mopen-modal-confirm{background:linear-gradient(135deg,#f1ca86,#e4ad5e);color:#3d2a13;box-shadow:0 6px 16px rgba(222,174,92,.22)}
    .mopen-hero-icon img,.mopen-plan-icon img,.mopen-modal-icon img{width:100%;height:100%;object-fit:cover;border-radius:inherit;display:block}
</style>
<main class="mopen-page">
    <div class="mopen-shell">
        <section class="mopen-hero">
            <div class="mopen-hero-top">
                <div class="mopen-hero-icon"><?= $_hasStation && $_currentStationIconImage !== '' ? '<img src="' . htmlspecialchars($_currentStationIconImage) . '" alt="">' : '<i class="' . htmlspecialchars($_hasStation ? $_currentStationIcon : 'ri-store-2-line') . '"></i>' ?></div>
                <div class="mopen-hero-main">
                    <div class="mopen-hero-title"><?= htmlspecialchars($_hasStation ? $_currentStationName : '开通您的专属分店') ?></div>
                    <div class="mopen-hero-desc"><?= $_hasStation ? '您已拥有分店，可继续选择更高版本升级。' : '选择适合您的版本，开启独立运营与佣金收益。' ?></div>
                </div>
                <div class="mopen-wallet"><span><i class="ri-time-line"></i><?= $_hasStation ? '已运营' : '运营天数' ?></span><b><?php if ($_hasStation): ?><em><?= $_stationOpenDays ?></em><small>天</small><?php else: ?><span class="mopen-wait">待开通</span><?php endif; ?></b></div>
            </div>
            <div class="mopen-hero-stats">
                <div class="mopen-hero-stat"><span>当前状态</span><b><?= $_hasStation ? '已开通' : '待开通' ?></b></div>
                <div class="mopen-hero-stat"><span>有效期</span><b><?= $_hasStation ? '永久有效' : '已到期' ?></b></div>
                <a class="mopen-hero-stat" href="/user/balance.php"><span>钱包余额</span><b>¥<?= number_format($_walletBalance, 2) ?></b></a>
            </div>
        </section>

        <section class="mopen-section">
            <div class="mopen-title-row"><div class="mopen-title">选择分店版本</div><a class="mopen-sub-link" href="/user/balance.php">余额不足？去充值</a></div>
            <?php if (!empty($_stationList)): ?>
            <div class="mopen-plan-strip" id="mopenPlans">
                <?php
                $_selectedSet = false;
                foreach ($_stationList as $_idx => $val):
                    $memberGate = intval($val['member_gate'] ?? 0);
                    $targetSort = intval($val['sort'] ?? 0);
                    $isOwned = $_hasStation && $targetSort <= $_currentStationSort;
                    $isCurrent = $_hasStation && intval($val['id']) === $_currentStationLevelId;
                    $targetPrice = (float)($val['price'] ?? 0);
                    $payAmount = $targetPrice; 
                    $canPurchase = true;
                    $disabledReason = '';
                    $actionLabel = $_hasStation ? '升级分店' : '立即开通';
                    if ($_hasStation) {
                        if ($isCurrent) {
                            $canPurchase = false;
                            $actionLabel = '当前';
                        } elseif ($isOwned) {
                            $canPurchase = false;
                            $actionLabel = '已拥有';
                        } else {
                            $payAmount = ($_upgradePriceMode === 'full') ? $targetPrice : max(0, round($targetPrice - $_currentStationPrice, 2));
                            $actionLabel = $payAmount > 0 ? (($_upgradePriceMode === 'full') ? '¥' . number_format($payAmount, 2) . ' 升级' : '补 ¥' . number_format($payAmount, 2) . ' 升级') : '免费升级';
                        }
                    } else {
                        $actionLabel = $payAmount > 0 ? '¥' . number_format($payAmount, 2) . ' 开通' : '免费开通';
                    }
                    if (!$isOwned && $memberGate > 0) {
                        $_gateSortRow = Database::getInstance()->once_fetch_array("SELECT sort FROM " . DB_PREFIX . "member WHERE id={$memberGate}");
                        $_gateSort = $_gateSortRow ? (int)$_gateSortRow['sort'] : 0;
                        if ($_userLevelSort < $_gateSort) {
                            $canPurchase = false;
                            $disabledReason = '需会员等级达到「' . ($val['member_gate_name'] ?? '') . '」';
                            $actionLabel = $disabledReason;
                        }
                    }
                    $_perks = [];
                    foreach ($_permLabels as $_field => $_label) {
                        $_perks[] = ['icon' => $_permIcons[$_field] ?? 'fa-check-circle', 'label' => $_label, 'ok' => (($val[$_field] ?? 'n') === 'y'), 'value' => ''];
                    }
                    $_perks[] = ['icon' => 'fa-percent', 'label' => '供货手续费', 'ok' => true, 'value' => number_format(((float)($val['service_change'] ?? 0)) * 100, 2) . '%'];
                    $_c = $_planColors[$_idx % count($_planColors)];
                    $_isActive = !$_selectedSet && $canPurchase;
                    if ($_isActive) $_selectedSet = true;
                    $_footText = $isCurrent ? '当前' : ($isOwned ? '已拥有' : ($canPurchase ? ($_hasStation ? '可升级' : '可开通') : '暂不可用'));
                    $_footClass = ($isOwned || $canPurchase) ? ' is-ok' : '';
                    $_levelIcon = trim((string)($val['icon'] ?? ''));
                    if ($_levelIcon === '') $_levelIcon = 'ri-store-2-line';
                    $_levelIconImage = trim((string)($val['icon_image'] ?? ''));
                ?>
                <div class="mopen-plan<?= $_isActive ? ' is-active' : '' ?><?= !$canPurchase ? ' is-disabled' : '' ?>"
                     style="--mopen-card-c1:<?= $_c[0] ?>;--mopen-card-c2:<?= $_c[1] ?>;--mopen-card-accent:<?= $_c[1] ?>;"
                     data-id="<?= intval($val['id']) ?>" data-name="<?= htmlspecialchars($val['name'], ENT_QUOTES) ?>" data-pay="<?= htmlspecialchars((string)$payAmount, ENT_QUOTES) ?>"
                     data-can="<?= $canPurchase ? '1' : '0' ?>" data-current="<?= $isCurrent ? '1' : '0' ?>" data-owned="<?= $isOwned ? '1' : '0' ?>" data-disabled-reason="<?= htmlspecialchars($disabledReason, ENT_QUOTES) ?>"
                     data-action-label="<?= htmlspecialchars($actionLabel, ENT_QUOTES) ?>" data-desc="<?= htmlspecialchars(trim((string)($val['description'] ?? '')), ENT_QUOTES) ?>"
                     data-upgrade="<?= htmlspecialchars(trim((string)($val['upgrade_desc'] ?? '')), ENT_QUOTES) ?>" data-upgrade-mode="<?= htmlspecialchars((string)($val['upgrade_mode_text'] ?? ''), ENT_QUOTES) ?>"
                     data-icon="<?= htmlspecialchars($_levelIcon, ENT_QUOTES) ?>" data-icon-image="<?= htmlspecialchars($_levelIconImage, ENT_QUOTES) ?>" data-perks="<?= htmlspecialchars(json_encode($_perks, JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>">
                    <div class="mopen-plan-mark"><i class="fa fa-check"></i></div>
                    <div class="mopen-plan-icon"><?= $_levelIconImage !== '' ? '<img src="' . htmlspecialchars($_levelIconImage) . '" alt="">' : '<i class="' . htmlspecialchars($_levelIcon) . '"></i>' ?></div>
                    <div class="mopen-plan-row"><span class="mopen-lv-tag">Lv<?= $_idx + 1 ?></span><span class="mopen-plan-name"><?= htmlspecialchars($val['name']) ?></span></div>
                    <div class="mopen-plan-price"><b>¥<?= number_format($targetPrice, 2) ?></b><br>永久</div>
                    <div class="mopen-plan-foot<?= $_footClass ?>"><?= htmlspecialchars($_footText) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="mopen-action-card" id="mopenActionCard"></div>
            <?php else: ?>
            <div class="mopen-empty"><?php include __DIR__ . '/../_svg_empty.php'; ?>暂无开放的分店版本</div>
            <?php endif; ?>
        </section>

        <?php if (!empty($_stationList)): ?>
        <section class="mopen-section mopen-desc-section is-empty" id="mopenDescSection"><div class="mopen-title-row"><div class="mopen-title">版本说明</div></div><div class="mopen-desc-card" id="mopenDescCard"></div></section>
        <section class="mopen-section"><div class="mopen-title-row"><div class="mopen-title">分店权益</div></div><div class="mopen-perks" id="mopenPerks"></div></section>
        <?php endif; ?>

        <section class="mopen-section">
            <div class="mopen-title-row"><div class="mopen-title">开通说明</div></div>
            <div class="mopen-info">
                <div class="mopen-info-row"><i class="fa fa-line-chart"></i><div><b>分店返佣</b><p>售出主站商品后，按差价或规则返还佣金，收益自动进入余额。</p></div></div>
                <div class="mopen-info-row"><i class="fa fa-credit-card"></i><div><b>余额支付</b><p>开通或升级会从钱包余额扣除，余额不足请先充值。</p></div></div>
                <div class="mopen-info-row"><i class="fa fa-bolt"></i><div><b>自动升级</b><p>达到销售额、订单量、运营天数等指标后，可按规则自动升级。</p></div></div>
            </div>
        </section>
    </div>
</main>
<div class="mopen-modal-mask" id="mopenModal"><div class="mopen-modal">
    <div class="mopen-modal-header"><div class="mopen-modal-icon"><i class="fa fa-home"></i></div><div class="mopen-modal-title">开通分店</div></div>
    <div class="mopen-modal-body">
        <div class="mopen-modal-row"><span>目标版本</span><b class="mopen-modal-level">-</b></div>
        <div class="mopen-modal-row"><span>扣除钱包</span><b class="mopen-modal-price">¥0.00</b></div>
        <div class="mopen-modal-row"><span>当前余额</span><b>¥<?= number_format($_walletBalance, 2) ?></b></div>
    </div>
    <div class="mopen-modal-foot"><button type="button" class="mopen-modal-btn mopen-modal-cancel">取消</button><button type="button" class="mopen-modal-btn mopen-modal-confirm">确认办理</button></div>
</div></div>
<script>
layui.use(['layer','jquery'], function(){
    var layer = layui.layer, $ = layui.$;
    var token = '<?= LoginAuth::genToken() ?>';
    var selected = $('#mopenPlans .mopen-plan.is-active');
    var submitting = false;
    function escapeHtml(str){ return $('<div/>').text(str == null ? '' : String(str)).html(); }
    function getPerks($el){ try { return JSON.parse($el.attr('data-perks') || '[]'); } catch(e) { return []; } }
    function renderPlan($el){
        if (!$el.length) return;
        selected = $el;
        $('#mopenPlans .mopen-plan').removeClass('is-active');
        $el.addClass('is-active');
        var can = $el.attr('data-can') === '1';
        var cur = $el.attr('data-current') === '1';
        var owned = $el.attr('data-owned') === '1';
        var reason = $el.attr('data-disabled-reason') || '';
        var actionLabel = $el.attr('data-action-label') || '立即开通';
        var btnText = cur ? '当前' : (owned ? '已拥有' : (can ? actionLabel : (reason || actionLabel || '当前版本暂不可用')));
        $('#mopenActionCard').html('<button type="button" class="mopen-main-btn" id="mopenSubmitBtn" ' + ((cur || owned || !can) ? 'disabled' : '') + '>' + escapeHtml(btnText) + '</button>');
        var desc = $el.attr('data-desc') || '', upgrade = $el.attr('data-upgrade') || '', upgradeMode = $el.attr('data-upgrade-mode') || '';
        var dh = desc ? '<div>' + escapeHtml(desc).replace(/\n/g, '<br>') + '</div>' : '';
        if (upgrade) dh += '<div class="mopen-auto"><div class="mopen-auto-title"><i class="fa fa-bolt"></i> 达标自动升级分店版本' + (upgradeMode ? '<span>' + escapeHtml(upgradeMode) + '</span>' : '') + '</div><div>' + escapeHtml(upgrade) + '</div></div>';
        if (reason && !owned) dh += '<div class="mopen-auto" style="background:#fff7ed;border-color:#fed7aa;color:#c2410c;"><div class="mopen-auto-title" style="color:#c2410c;"><i class="fa fa-lock"></i> 开通门槛</div><div>' + escapeHtml(reason) + '</div></div>';
        if (dh) { $('#mopenDescCard').html(dh); $('#mopenDescSection').removeClass('is-empty'); } else { $('#mopenDescCard').empty(); $('#mopenDescSection').addClass('is-empty'); }
        var perks = getPerks($el);
        perks.sort(function(a,b){ return (b.ok ? 1 : 0) - (a.ok ? 1 : 0); });
        var ph = '';
        for (var i = 0; i < perks.length; i++) {
            var p = perks[i] || {}, cls = p.ok ? '' : ' mopen-perk-off';
            ph += '<div class="mopen-perk' + cls + '"><div class="mopen-perk-ico"><i class="fa ' + escapeHtml(p.icon || 'fa-check-circle') + '"></i></div><div class="mopen-perk-txt">' + escapeHtml(p.label || '') + '</div>' + (p.value ? '<div class="mopen-perk-val">' + escapeHtml(p.value) + '</div>' : '') + '</div>';
        }
        $('#mopenPerks').html(ph);
    }
    if (!selected.length) selected = $('#mopenPlans .mopen-plan[data-current="1"]').first();
    if (!selected.length) selected = $('#mopenPlans .mopen-plan').first();
    selected.addClass('is-active');
    if (selected.length) {
        renderPlan(selected);
        var strip = $('#mopenPlans')[0], card = selected[0];
        if (strip && card) strip.scrollLeft = card.offsetLeft - strip.offsetWidth / 2 + card.offsetWidth / 2;
    }
    $('#mopenPlans .mopen-plan').on('click', function(){
        renderPlan($(this));
        var reason = $(this).attr('data-disabled-reason') || '';
        if ($(this).attr('data-can') !== '1' && reason && $(this).attr('data-owned') !== '1') layer.msg(reason, {icon: 0, time: 2200});
    });
    function hideModal(){ $('#mopenModal').removeClass('is-show'); }
    function showModal(){
        if (!selected.length || selected.attr('data-can') !== '1') return;
        var pay = parseFloat(selected.attr('data-pay') || '0') || 0;
        var actionLabel = selected.attr('data-action-label') || '确认办理';
        var isUpgrade = actionLabel.indexOf('升级') !== -1;
        $('#mopenModal .mopen-modal-title').text(isUpgrade ? '升级分店' : '开通分店');
        $('#mopenModal .mopen-modal-level').text(selected.attr('data-name') || '');
        $('#mopenModal .mopen-modal-price').text('¥' + pay.toFixed(2));
        var iconImage = selected.attr('data-icon-image') || '';
        var iconClass = selected.attr('data-icon') || 'ri-store-2-line';
        $('#mopenModal .mopen-modal-icon').html(iconImage ? '<img src="' + iconImage + '" alt="">' : '<i class="' + iconClass + '"></i>');
        $('#mopenModal .mopen-modal-confirm').text(isUpgrade ? '确认升级' : '确认开通');
        $('#mopenModal').addClass('is-show');
    }
    function submitOpen(levelId){
        if (submitting) return;
        submitting = true;
        var idx = layer.load(2, {shade: [0.3, '#000']});
        $.ajax({
            type: 'POST',
            url: '?action=open_ajax',
            data: {id: levelId, token: token},
            dataType: 'json',
            success: function(e){
                layer.close(idx);
                if (e.code == 400) {
                    var msg = e.msg || '操作失败';
                    if (msg.indexOf('余额不足') !== -1 || msg.indexOf('请充值') !== -1) {
                        layer.confirm('<div style="line-height:1.8;">' + escapeHtml(msg) + '<br>是否前往钱包充值？</div>', {title:'余额不足', btn:['前往充值','取消'], icon:0}, function(){ location.href = '/user/balance.php'; });
                    } else {
                        layer.msg(msg, {icon: 2, time: 3000});
                    }
                    return;
                }
                layer.alert(e.msg || '操作成功', {icon: 1, title: '办理成功', btn: ['确定'], yes: function(){ location.href = 'station.php'; }});
            },
            error: function(xhr){
                layer.close(idx);
                var msg = '操作失败，请稍后重试';
                try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(ex){}
                layer.msg(msg, {icon: 2, time: 3000});
            },
            complete: function(){ submitting = false; }
        });
    }
    $(document).on('click', '#mopenSubmitBtn', showModal);
    $(document).on('click', '.mopen-modal-cancel, .mopen-modal-mask', function(e){ if (e.target === this) hideModal(); });
    $(document).on('click', '.mopen-modal-confirm', function(){
        if (!selected.length) return;
        var levelId = parseInt(selected.attr('data-id'), 10) || 0;
        if (!levelId) return;
        hideModal();
        submitOpen(levelId);
    });
});
</script>
<script>
    $('#menu-open-station').addClass('menu-current');
</script>
