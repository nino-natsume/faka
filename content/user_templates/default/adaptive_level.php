<?php
defined('DC_ROOT') || exit('access denied!');

$levelStatus = isset($status) && is_array($status) ? $status : [];
$levelList = isset($levels) && is_array($levels) ? $levels : [];
$levelOrderList = isset($levelOrders) && is_array($levelOrders) ? $levelOrders : [];
$walletBalance = isset($user['money']) ? (float)$user['money'] : 0;
$virtualCurrencyNameEsc = htmlspecialchars(getVirtualCurrencyName(), ENT_QUOTES, 'UTF-8');
$paidFlag = !empty($paid);
$_lvIndexMap = []; $_i = 0; foreach ($levelList as $_lv) { $_i++; $_lvIndexMap[intval($_lv['id'])] = $_i; }
$_lvColors = [
    1 => ['var(--tp-light)','var(--theme-primary)'],
    2 => ['#60a5fa','var(--theme-primary)'],
    3 => ['#a78bfa','#7c3aed'],
    4 => ['#f472b6','#db2777'],
    5 => ['#fb923c','#ea580c'],
    6 => ['#fbbf24','#d97706'],
    7 => ['#34d399','#059669'],
    8 => ['#f87171','#dc2626'],
];
$currentNameRaw = $levelStatus['level_name'] ?? '未开通等级';
$currentName = htmlspecialchars($currentNameRaw);
$activeIndex = 0;
$nextLevel = null;
$hasCurrentLevel = !empty($levelStatus['level_id']);
foreach ($levelList as $i => $lv) {
    if (!empty($lv['is_current'])) $activeIndex = $i;
}
foreach ($levelList as $i => $lv) {
    if ($i > $activeIndex && !empty($lv['can_purchase'])) { $nextLevel = $lv; break; }
}
$activeLevel = !empty($levelList) ? $levelList[$activeIndex] : [];
$currentIcon = $hasCurrentLevel && !empty($activeLevel['icon']) ? (string)$activeLevel['icon'] : 'ri-vip-diamond-line';
$currentIconImg = $hasCurrentLevel && !empty($activeLevel['icon_image']) ? (string)$activeLevel['icon_image'] : '';
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
$stationSwitch = (int)Level_Service::getSetting(Level_Service::OPT_STATION_SWITCH, 0);
$canStation = false;
$lowestGateName = '';
if ($stationSwitch) {
    $userLevelId = (int)Level_Service::getActiveLevelId($user);
    $_db = Database::getInstance();
    $_dbp = DB_PREFIX;
    $userMemberSort = 0;
    $_uml = $_db->once_fetch_array("SELECT sort FROM {$_dbp}member WHERE id = {$userLevelId}");
    if ($_uml) $userMemberSort = (int)$_uml['sort'];
    $_stLevels = $_db->fetch_all("SELECT member_gate FROM {$_dbp}station_level ORDER BY sort ASC");
    foreach ($_stLevels as $_sl) {
        $g = intval($_sl['member_gate'] ?? 0);
        if ($g === 0) { $canStation = true; break; }
        $_glv = $_db->once_fetch_array("SELECT sort FROM {$_dbp}member WHERE id = {$g}");
        if ($_glv && $userMemberSort >= (int)$_glv['sort']) { $canStation = true; break; }
        if (empty($lowestGateName)) {
            $_gn = $_db->once_fetch_array("SELECT name FROM {$_dbp}member WHERE id = {$g}");
            $lowestGateName = $_gn ? $_gn['name'] : '';
        }
    }
}
?>
<style>
    .level-page { display: flex; flex-direction: column; gap: 22px; padding: 6px 0 24px; }

    /* ── hero ── */
    .level-hero { padding: 26px 28px; border-radius: 10px; background: var(--pc-card-bg); border: 2px solid #fff; box-shadow: 0 1px 18px #12345b0a;}
    .level-hero-inner { display: grid; grid-template-columns: minmax(0,1fr) auto; gap: 16px; align-items: center; }
    .level-hero-title { font-size: 13px; color: var(--theme-primary); font-weight: 600; letter-spacing: .5px; margin-bottom: 8px; display: inline-flex; align-items: center; gap: 6px; }
    .level-hero-title .fa { color: var(--theme-primary); }
    .level-hero-name-row { display: flex; align-items: baseline; gap: 14px; flex-wrap: wrap; }
    .level-hero-name { font-size: 22px; font-weight: 800; line-height: 1.15; color: #0f172a; }
    .level-hero-name span { color: var(--theme-primary); }
    .level-hero-meta { font-size: 13px; color: var(--theme-primary); }
    .level-hero-badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px; border-radius: 999px; background: #f1f5f9; color: #475569; font-size: 12px; margin-right: 8px; }
    .level-hero-badge .fa { color: var(--theme-primary); }
    .level-hero-badge.warn { background: #fef3c7; color: #92400e; }
    .level-hero-badge.warn .fa { color: #f59e0b; }
    .level-hero-badge.danger { background: #fef2f2; color: #dc2626; }
    .level-hero-badge.danger .fa { color: #ef4444; }
    .level-hero-station { display: inline-flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 8px 16px; border-radius: 999px; background: #f8fafc; border: 1px solid #e2e8f0; font-size: 13px; color: #475569; align-self: center; transition: background .2s; }
    .level-hero-station:hover { background: #f1f5f9; }
    .level-hero-station .st-icon { font-size: 16px; line-height: 1; }
    .level-hero-station .st-icon.ok { color: #059669; }
    .level-hero-station .st-icon.locked { opacity: .5; color: var(--theme-primary); }
    .level-hero-station .st-text { font-weight: 600; color: #1e293b; }
    .level-hero-station .st-req { font-size: 12px; color: var(--theme-primary); }
    .level-hero-station .st-divider { width: 1px; height: 14px; background: #e2e8f0; }
    .level-hero-station-btn { color: var(--theme-primary); text-decoration: none; padding: 3px 12px; background: rgba(var(--tp-rgb),.06); border-radius: 999px; font-size: 12px; font-weight: 600; transition: .18s; display: inline-flex; align-items: center; gap: 4px; }
    .level-hero-station-btn:hover { background: rgba(var(--tp-rgb),.12); color: var(--tp-dark); text-decoration: none; }

    /* ── grid ── */
    .level-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 18px; }

    /* ── card ── */
    .level-card {
        background: var(--pc-card-bg); border: 2px solid #fff; border-radius: 10px; box-shadow: 0 1px 18px #12345b0a;
        display: flex; flex-direction: column; position: relative;
        transition: transform .22s, box-shadow .22s, border-color .22s;
        overflow: hidden;
    }
    .level-card:hover { transform: translateY(-4px); border-color: rgba(var(--tp-rgb),.22); box-shadow: 0 12px 32px rgba(var(--tp-rgb),.10), 0 2px 6px rgba(0,0,0,.04); }

    /* ── card title ── */
    .lvc-title { display: flex; align-items: center; gap: 10px; padding: 20px 22px 0; }
    .lvc-title-lv {
        font-size: 12px; font-weight: 600; color: #6b7280;
        flex-shrink: 0; line-height: 1;
    }
    .lvc-title-name { font-size: 18px; font-weight: 700; color: #1f2937; line-height: 1.3; }
    .lvc-title-icon { width: 38px; height: 38px; border-radius: 12px; background: rgba(var(--tp-rgb),.08); color: var(--lvc-accent); display: inline-flex; align-items: center; justify-content: center; font-size: 21px; overflow: hidden; flex-shrink: 0; }
    .lvc-title-icon img { width: 100%; height: 100%; object-fit: cover; }
    .lvc-title-current {
        margin-left: auto; font-size: 11px; font-weight: 600; color: var(--lvc-accent);
        padding: 2px 10px; border-radius: 999px;
        background: rgba(var(--tp-rgb),.06); border: 1px solid rgba(var(--tp-rgb),.12); flex-shrink: 0;
    }

    /* ── card body ── */
    .lvc-body { padding: 14px 22px 20px; display: flex; flex-direction: column; gap: 14px; flex: 1; }

    /* price */
    .lvc-price { display: flex; align-items: baseline; gap: 3px; padding-top: 2px; }
    .lvc-price .sym { font-size: 16px; font-weight: 700; color: var(--lvc-accent); }
    .lvc-price .num { font-size: 34px; font-weight: 900; color: var(--lvc-accent); line-height: 1; letter-spacing: -1px; }
    .lvc-price .unit { font-size: 12px; color: #9ca3af; margin-left: 5px; font-weight: 500; }

    /* meta tags */
    .lvc-meta { display: flex; gap: 6px; flex-wrap: wrap; }
    .lvc-meta .tag { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; background: #f3f4f6; color: #6b7280; }
    .lvc-meta .tag.up { background: #ecfdf5; color: #059669; }

    /* desc */
    .lvc-desc { font-size: 12.5px; color: #6b7280; line-height: 1.7; padding: 10px 14px; background: #f9fafb; border-radius: 10px; border-left: 3px solid #d1d5db; }

    /* upgrade hint */
    .lvc-upgrade { background: linear-gradient(135deg, #ecfdf5, #d1fae5); border: 1px solid #a7f3d0; border-radius: 1px; padding: 10px 12px; font-size: 12px; color: #047857; }
    .lvc-upgrade-title { font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 5px; font-size: 12px; color: #065f46; }
    .lvc-upgrade-mode { font-weight: 400; font-size: 10px; background: #d1fae5; color: #047857; padding: 1px 6px; border-radius: 4px; margin-left: 2px; }
    .lvc-upgrade-item { display: flex; align-items: center; gap: 5px; padding: 2px 0; color: #047857; font-size: 12px; }
    .lvc-upgrade-item .fa { width: 14px; text-align: center; color: #10b981; }

    /* divider */
    .lvc-divider { height: 1px; background: #f1f5f9; margin: 2px 0; }

    /* buttons */
    .level-card-btn {
        margin-top: auto; display: flex; align-items: center; justify-content: center; gap: 6px;
        min-height: 42px; padding: 0 18px; border-radius: 10px; border: none; cursor: pointer;
        font-size: 14px; font-weight: 600; transition: all .18s;
        background: var(--theme-primary); color: #fff;
    }
    .level-card-btn:hover { background: var(--tp-dark); }
    .level-card-btn.is-disabled { background: #f3f4f6; color: #9ca3af; cursor: not-allowed; }
    .level-card-btn.is-disabled:hover { background: #f3f4f6; }
    .level-card-tag {
        margin-top: auto; display: flex; align-items: center; justify-content: center; gap: 6px;
        min-height: 42px; padding: 0 18px; border-radius: 10px;
        font-size: 14px; font-weight: 600;
        background: rgba(var(--tp-rgb),.04); color: var(--theme-primary); border: 1px solid rgba(var(--tp-rgb),.18);
    }

    /* current card */
    .level-card.is-current { border-color: var(--lvc-accent); border-width: 2px; box-shadow: 0 0 0 3px rgba(var(--tp-rgb),.08); }
    .level-card.is-current:hover { transform: none; box-shadow: 0 0 0 3px rgba(var(--tp-rgb),.08); }

    /* ── history ── */
    .level-history { background: var(--pc-card-bg); border: 2px solid #fff; border-radius: 10px; padding: 20px 22px; box-shadow: 0 1px 18px #12345b0a; }
    .level-history-title { font-size: 15px; font-weight: 700; color: #111827; margin-bottom: 14px; display: inline-flex; align-items: center; gap: 6px; }
    .level-history-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .level-history-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .level-history-table th, .level-history-table td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #f3f4f6; white-space: nowrap; }
    .level-history-table th { color: #6b7280; font-weight: 600; background: #fafafa; }

    .level-history-table thead th:first-child { background: rgba(79,70,229,.05); }
    .level-history-empty { color: #9ca3af; padding: 20px 0; text-align: center; }
    .level-state-0 { color: #f59e0b; }
    .level-state-1 { color: #10b981; }
    .level-state--1 { color: #ef4444; }

    /* purchase modal */
    .lv-modal-mask { position: fixed; inset: 0; z-index: 19999; background: rgba(0,0,0,.45); display: flex; align-items: center; justify-content: center; opacity: 0; visibility: hidden; transition: opacity .25s, visibility .25s; }
    .lv-modal-mask.is-show { opacity: 1; visibility: visible; }
    .lv-modal { width: min(420px, 90vw); background: var(--pc-card-bg); border-radius: 10px; overflow: hidden; transform: translateY(24px) scale(.96); transition: transform .28s cubic-bezier(.22,.61,.36,1); box-shadow: 0 20px 50px rgba(0,0,0,.18); border: 2px solid #fff;}
    .lv-modal-mask.is-show .lv-modal { transform: translateY(0) scale(1); }
    .lv-modal-header { padding: 28px 28px 0; text-align: center; }
    .lv-modal-icon { width: 56px; height: 56px; margin: 0 auto 14px; border-radius: 50%; background: linear-gradient(135deg,#f8dfad,#e8bb72); display: flex; align-items: center; justify-content: center; font-size: 26px; color: #7a5a2b; overflow: hidden; }
    .lv-modal-icon img { width: 100%; height: 100%; object-fit: cover; }
    .lv-modal-title { font-size: 18px; font-weight: 800; color: #252d3b; }
    .lv-modal-body { padding: 18px 28px 8px; }
    .lv-modal-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f2f4f7; font-size: 14px; color: #5f6673; }
    .lv-modal-row:last-child { border-bottom: none; }
    .lv-modal-row b { color: #252d3b; font-weight: 700; }
    .lv-modal-row .lv-price { color: #c88332; font-weight: 800; font-size: 16px; }
    .lv-modal-foot { display: flex; gap: 12px; padding: 12px 28px 28px; }
    .lv-modal-btn { flex: 1; height: 46px; border: none; border-radius: 14px; font-size: 15px; font-weight: 700; cursor: pointer; transition: .15s; }
    .lv-modal-cancel { background: #f3f4f6; color: #5f6673; }
    .lv-modal-cancel:hover { background: #e8ebf0; }
    .lv-modal-confirm { background: linear-gradient(135deg,#f1ca86,#e4ad5e); color: #3d2a13; box-shadow: 0 6px 16px rgba(222,174,92,.22); }
    .lv-modal-confirm:hover { background: linear-gradient(135deg,#f5d79d,#e7b767); }
    .lv-modal-confirm:active { transform: scale(.97); }

    .level-page { gap: 24px; padding: 4px 0 28px; }
    .level-hero { position: relative; padding: 0; border: 0; border-radius: 10px; background: url('<?= DC_URL ?>content/user_templates/default/img/grzx_hy_bj_v2.png') center/105% 120% no-repeat, linear-gradient(135deg,#1c1b2a 0%,#2b2a3f 48%,#171724 100%); color: #fff; overflow: hidden; box-shadow: 0 1px 18px #12345b0a; border: 2px solid #fff;}
    .level-hero::before { content: ''; position: absolute; top: -82px; right: -46px; width: 230px; height: 230px; border-radius: 50%; background: radial-gradient(circle,rgba(244,210,152,.14) 0%,rgba(244,210,152,0) 68%); }
    .level-hero-inner { position: relative; z-index: 1; display: grid; grid-template-columns: minmax(0,1fr) 310px; gap: 24px; align-items: stretch; padding: 20px 30px; }
    .level-hero-main { display: flex; flex-direction: column; justify-content: space-between; min-height: 180px; }
    .level-hero-user { display: flex; align-items: center; gap: 16px; margin-top: 15px;}
    .level-hero-avatar { width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg,#f8dfad,#e8bb72); display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid rgba(255,255,255,.3); flex-shrink: 0; }
    .level-hero-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .level-hero-avatar span { font-size: 24px; font-weight: 800; color: #755427; }
    .level-hero-title { margin: 0 0 4px; color: #fff; font-size: 22px; font-weight: 800; letter-spacing: 0; }
    .level-hero-subtitle { color: rgba(255,255,255,.55); font-size: 13px; font-weight: 400; }
    .level-hero-name-row { align-items: center; gap: 12px; }
    .level-hero-name { display: inline-flex; align-items: center; gap: 10px; color: #fff; font-size: 28px; font-weight: 900; }
    .level-hero-name span { color: #f5dcae; }
    .level-hero-current-icon { width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg,#f8dfad,#e8bb72); color: #7a5a2b; display: inline-flex; align-items: center; justify-content: center; font-size: 22px; overflow: hidden; flex-shrink: 0; }
    .level-hero-current-icon img { width: 100%; height: 100%; object-fit: cover; }
    .level-hero-badge { background: rgba(255,255,255,.12); color: #f5dcae; border: 1px solid rgba(245,220,174,.16); margin-right: 0; }
    .level-hero-badge .fa { color: #f5dcae; }
    .level-hero-badge.danger { background: rgba(239,68,68,.15); color: #fecaca; border-color: rgba(254,202,202,.18); }
    .level-hero-badge.danger .fa { color: #fecaca; }
    .level-hero-progress { margin-top: 30px; max-width: 680px; }
    .level-hero-progress-top { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 12px; font-size: 13px; color: rgba(255,255,255,.72); }
    .level-hero-progress-top b { color: #f5dcae; }
    .level-progress-line { height: 8px; border-radius: 999px; background: rgba(255,255,255,.1); overflow: hidden; }
    .level-progress-fill { width: <?= $progressPercent ?>%; height: 100%; border-radius: inherit; background: linear-gradient(90deg,#d4c4a8,#f1ca86); box-shadow: 0 0 18px rgba(241,202,134,.28); }
    .level-upgrade-hint { margin-top: 10px; color: rgba(255,255,255,.55); font-size: 12px; line-height: 1.6; }
    .level-hero-side { border: 1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.07); border-radius: 18px; padding: 18px; backdrop-filter: blur(8px); display: flex; flex-direction: column; justify-content: space-between; gap: 16px; }
    .level-wallet-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .level-wallet-item { padding: 14px 12px; border-radius: 14px; background: rgba(255,255,255,.08); }
    .level-wallet-k { color: rgba(255,255,255,.5); font-size: 12px; }
    .level-wallet-v { margin-top: 6px; color: #f5dcae; font-size: 19px; font-weight: 900; }
    .level-hero-station { width: 100%; justify-content: space-between; padding: 12px 14px; border-radius: 14px; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.1); color: rgba(255,255,255,.72); }
    .level-hero-station:hover { background: rgba(255,255,255,.1); }
    .level-hero-station .st-text { color: #fff; }
    .level-hero-station .st-req { color: #f5dcae; }
    .level-hero-station .st-divider { background: rgba(255,255,255,.14); }
    .level-hero-station .st-icon.ok { color: #86efac; }
    .level-hero-station .st-icon.locked { color: #f5dcae; opacity: .75; }
    .level-hero-station-btn { background: rgba(245,220,174,.16); color: #f5dcae; }
    .level-hero-station-btn:hover { background: rgba(245,220,174,.24); color: #fff; }
    .level-paid-tip { padding: 13px 16px; border-radius: 16px; background: #ecfdf5; border: 1px solid #bbf7d0; color: #047857; display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; }
    .level-section-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
    .level-section-title { color: #252d3b; font-size: 20px; font-weight: 900; }
    .level-section-sub { color: #9a7440; font-size: 13px; }
    .level-grid { grid-template-columns: repeat(auto-fill, minmax(238px, 1fr)); gap: 16px; }
    .level-card { border: 2px solid #fff; border-radius: 10px; background: var(--pc-card-bg); box-shadow: 0 1px 18px #12345b0a; }

    .level-card:hover { border-color: #e5bd7b; box-shadow: 0 14px 30px rgba(210,153,67,.14); }
    .level-card.is-current { border-color: #e5bd7b; box-shadow: 0 0 0 3px rgba(229,189,123,.14), 0 12px 28px rgba(210,153,67,.1); }
    .level-card.is-current:hover { transform: translateY(-2px); box-shadow: 0 0 0 3px rgba(229,189,123,.14), 0 12px 28px rgba(210,153,67,.1); }
    .lvc-title { padding: 20px 20px 0; gap: 8px; }
    .lvc-title-icon { width: 42px; height: 42px; border-radius: 50%; background: #f8ecd8; color: #c88332; font-size: 22px; }
    .lvc-title-lv { color: #9ca3af; font-size: 12px; font-weight: 700; }
    .lvc-title-name { color: #252d3b; font-size: 17px; font-weight: 900; }
    .lvc-title-current { color: #9a7440; background: #fff4df; border-color: #f2d7aa; }
    .lvc-body { padding: 16px 20px 20px; gap: 12px; }
    .lvc-price .sym, .lvc-price .num { color: #c88332; }
    .lvc-price .num { font-size: 32px; }
    .lvc-meta .tag.up { background: #fff4df; color: #9a7440; }
    .lvc-desc { border: 0; background: #e8ebf0; color: #77808f; border-radius: 5px; }
    .lvc-upgrade { background: #f7fbf7; border-color: #d9f1dd; color: #047857; }
    .lvc-divider { background: #f2f4f7; }
    .level-card-btn { border-radius: 14px; min-height: 44px; background: linear-gradient(135deg,#f1ca86,#e4ad5e); color: #3d2a13; font-weight: 800; box-shadow: 0 8px 18px rgba(222,174,92,.18); }
    .level-card-btn:hover { background: linear-gradient(135deg,#f5d79d,#e7b767); color: #3d2a13; transform: translateY(-1px); }
    .level-card-btn.is-disabled { background: #e8ebf0; color: #9ca3af; box-shadow: none; }
    .level-card-btn.is-disabled:hover { background: #e8ebf0; transform: none; }
    .level-card-tag { border: 0; border-radius: 14px; background: #fff4df; color: #9a7440; font-weight: 800; }
    .level-history { border: 2px solid #fff; border-radius: 10px; background: var(--pc-card-bg); box-shadow: 0 1px 18px #12345b0a; }
    .level-history-title { font-size: 18px; font-weight: 900; color: #252d3b; }
    .level-history-table th { background: rgba(79,70,229,.05); color: #8b95a5; }

    @media (max-width: 768px) {
        .level-hero { padding: 22px 18px; }
        .level-hero-inner { grid-template-columns: 1fr; }
        .level-grid { grid-template-columns: 1fr; gap: 14px; }
        .lvc-title { padding: 18px 18px 0; }
        .lvc-body { padding: 12px 18px 18px; }
    }
</style>

<main class="level-page">
    <!-- Hero 当前等级 -->
    <section class="level-hero">
        <div class="level-hero-inner">
            <div class="level-hero-main">
                <div class="level-hero-user">
                    <?php
                        $_avatar = !empty($user['photo']) ? User::getAvatar($user['photo']) : '';
                        $_displayName = !empty($user['nickname']) ? $user['nickname'] : ($user['username'] ?? '用户');
                        $_initial = function_exists('mb_substr') ? mb_substr($_displayName, 0, 1, 'UTF-8') : substr($_displayName, 0, 1);
                    ?>
                    <div class="level-hero-avatar"><?php if($_avatar): ?><img src="<?= htmlspecialchars($_avatar) ?>" alt=""><?php else: ?><span><?= htmlspecialchars($_initial) ?></span><?php endif; ?></div>
                    <div>
                        <div class="level-hero-title"><?= htmlspecialchars($_displayName) ?></div>
                        <div class="level-hero-subtitle">升级后可获得更低价格与更多经营权益</div>
                    </div>
                </div>
                <div class="level-hero-progress">
                    <div class="level-hero-progress-top">
                        <span>当前等级 <b><?= $currentName ?></b></span>
                        <span>下一级：<b><?= htmlspecialchars($nextName) ?></b></span>
                    </div>
                    <div class="level-progress-line"><div class="level-progress-fill"></div></div>
                    <?php if (!empty($upgradeHint)): ?><div class="level-upgrade-hint"><?= htmlspecialchars($upgradeHint) ?></div><?php endif; ?>
                </div>
            </div>
            <aside class="level-hero-side">
                <div class="level-wallet-grid">
                    <div class="level-wallet-item">
                        <div class="level-wallet-k">钱包余额</div>
                        <div class="level-wallet-v">¥<?= number_format($walletBalance, 2) ?></div>
                    </div>
                    <div class="level-wallet-item">
                        <div class="level-wallet-k">账户<?= $virtualCurrencyNameEsc ?></div>
                        <div class="level-wallet-v"><?= intval($user['credits'] ?? 0) ?></div>
                    </div>
                </div>
                <?php if ($stationSwitch): ?>
                <div class="level-hero-station">
                    <?php if ($canStation): ?>
                        <i class="fa fa-check-circle st-icon ok"></i>
                        <span class="st-text">可开通分店</span>
                        <span class="st-divider"></span>
                        <a href="/user/station.php?action=open" class="level-hero-station-btn">去开通 <i class="fa fa-arrow-right"></i></a>
                    <?php else: ?>
                        <i class="fa fa-lock st-icon locked"></i>
                        <span class="st-text">分店未解锁</span>
                        <?php if (!empty($lowestGateName)): ?>
                        <span class="st-divider"></span>
                        <span class="st-req">需 <?= htmlspecialchars($lowestGateName) ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </aside>
        </div>
    </section>
    <?php if ($paidFlag): ?><div class="level-paid-tip"><i class="ri-checkbox-circle-line"></i> 开通成功，会员等级已更新。</div><?php endif; ?>


    <!-- 等级卡片网格 -->
    <section class="level-grid">
        <?php $lvIndex = 0; foreach ($levelList as $lv): $lvIndex++;
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
        <?php $_c = $_lvColors[$lvIndex] ?? $_lvColors[1]; ?>
        <article class="level-card<?= $isCurrent ? ' is-current' : '' ?>" style="--lvc-grad:linear-gradient(135deg,<?= $_c[0] ?>,<?= $_c[1] ?>);--lvc-accent:<?= $_c[1] ?>" data-id="<?= intval($lv['id']) ?>" data-name="<?= htmlspecialchars($lv['name']) ?>" data-price="<?= htmlspecialchars((string)$payAmount) ?>" data-action-label="<?= htmlspecialchars($purchaseLabel) ?>" data-icon="<?= htmlspecialchars($_icon) ?>" data-icon-image="<?= htmlspecialchars($_iconImg) ?>">
            <div class="lvc-title">
                <span class="lvc-title-icon"><?php if($_iconImg): ?><img src="<?= htmlspecialchars($_iconImg) ?>" alt=""><?php else: ?><i class="<?= htmlspecialchars($_icon) ?>"></i><?php endif; ?></span>
                <span class="lvc-title-lv">Lv.<?= $lvIndex ?></span>
                <span class="lvc-title-name"><?= htmlspecialchars($lv['name']) ?></span>
                <?php if ($isCurrent): ?><span class="lvc-title-current"><i class="fa fa-check-circle"></i> 当前</span><?php endif; ?>
            </div>
            <div class="lvc-body">
                <div class="lvc-price">
                    <span class="sym">¥</span>
                    <span class="num"><?= number_format($showPrice, 2) ?></span>
                    <?php if ($duration > 0): ?>
                        <span class="unit">/ <?= $duration ?> 天</span>
                    <?php else: ?>
                        <span class="unit">/ 永久</span>
                    <?php endif; ?>
                </div>
                <?php
                    $upgDirect  = intval($lv['upgrade_direct_count'] ?? 0);
                    $upgConsume = floatval($lv['upgrade_consume_amount'] ?? 0);
                    $upgTeam    = intval($lv['upgrade_team_count'] ?? 0);
                    $hasUpgCond = ($upgDirect > 0 || $upgConsume > 0 || $upgTeam > 0);
                ?>
                <?php if ($hasUpgCond): ?>
                <div class="lvc-meta">
                    <?php $upgMode = ($lv['upgrade_mode'] ?? 'any') === 'all' ? '全部满足' : '任一满足'; ?>
                </div>
                <?php endif; ?>
                <?php if ($hasUpgCond): ?>
                    <div class="lvc-upgrade">
                        <div class="lvc-upgrade-title"><i class="fa fa-bolt"></i> 达标免费升级 <span class="lvc-upgrade-mode"><?= $upgMode ?></span></div>
                        <?php if ($upgDirect > 0): ?>
                            <div class="lvc-upgrade-item"><i class="fa fa-user-plus"></i> 直推邀请 <?= $upgDirect ?> 人</div>
                        <?php endif; ?>
                        <?php if ($upgConsume > 0): ?>
                            <div class="lvc-upgrade-item"><i class="fa fa-shopping-cart"></i> 累计消费 ¥<?= number_format($upgConsume, 0) ?></div>
                        <?php endif; ?>
                        <?php if ($upgTeam > 0): ?>
                            <div class="lvc-upgrade-item"><i class="fa fa-users"></i> 团队人数 <?= $upgTeam ?> 人</div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($lv['content'])): ?>
                    <div class="lvc-desc"><?= nl2br(htmlspecialchars($lv['content'])) ?></div>
                <?php endif; ?>
                <div class="lvc-divider"></div>
                <?php if ($isCurrent): ?>
                    <div class="level-card-tag"><i class="fa fa-check-circle"></i> 当前等级</div>
                <?php elseif ($disabled): ?>
                    <button type="button" class="level-card-btn is-disabled" disabled title="<?= htmlspecialchars($lv['purchase_disabled_reason'] ?? '') ?>">
                        <i class="fa fa-lock"></i> <?= htmlspecialchars($purchaseLabel) ?>
                    </button>
                <?php else: ?>
                    <button type="button" class="level-card-btn">
                        <i class="fa fa-arrow-up"></i> <?= htmlspecialchars($purchaseLabel) ?>
                    </button>
                <?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>
        <?php if (empty($levelList)): ?>
            <div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: #9ca3af; background: #fafafa; border-radius: 12px;">
                暂无开放的会员等级
            </div>
        <?php endif; ?>
    </section>

    <!-- 历史订单 -->
    <section class="level-history">
        <div class="level-history-title"><i class="fa fa-history"></i> 开通记录</div>
        <?php if (empty($levelOrderList)): ?>
            <div class="level-history-empty">暂无开通记录</div>
        <?php else: ?>
        <div class="level-history-scroll">
        <table class="level-history-table">
            <thead>
                <tr>
                    <th>订单号</th>
                    <th>类型</th>
                    <th>金额</th>
                    <th>状态</th>
                    <th>时间</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($levelOrderList as $row):
                    $typeMap = ['open' => '开通', 'renew' => '续费', 'upgrade' => '升级'];
                    $stateMap = [0 => '待支付', 1 => '已完成', -1 => '已取消'];
                    $typeText = $typeMap[$row['purchase_type']] ?? $row['purchase_type'];
                    $stateText = $stateMap[intval($row['state'])] ?? '未知';
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['out_trade_no']) ?></td>
                    <td><?= $typeText ?></td>
                    <td>¥<?= number_format(floatval($row['amount']), 2) ?></td>
                    <td class="level-state-<?= intval($row['state']) ?>"><?= $stateText ?></td>
                    <td><?= !empty($row['create_time']) ? date('Y-m-d H:i', $row['create_time']) : '--' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </section>
</main>

<div class="lv-modal-mask" id="lvModal">
    <div class="lv-modal">
        <div class="lv-modal-header">
            <div class="lv-modal-icon" id="lvModalIcon"><i class="ri-vip-diamond-line"></i></div>
            <div class="lv-modal-title">升级会员等级</div>
        </div>
        <div class="lv-modal-body">
            <div class="lv-modal-row"><span>目标等级</span><b id="lvModalLevel">-</b></div>
            <div class="lv-modal-row"><span>扣除钱包</span><span class="lv-price" id="lvModalPay">¥0.00</span></div>
        </div>
        <div class="lv-modal-foot">
            <button class="lv-modal-btn lv-modal-cancel" id="lvModalCancel">取消</button>
            <button class="lv-modal-btn lv-modal-confirm" id="lvModalConfirm">确定升级</button>
        </div>
    </div>
</div>

<script>
layui.use(['layer', 'jquery'], function () {
    var $ = layui.$;
    var layer = layui.layer;
    var token = '<?= LoginAuth::genToken() ?>';
    var balanceUrl = '<?= DC_URL ?>user/balance.php';
    var submitting = false;

    // 钱包余额支付：确认 → AJAX
    function submitUpgrade(levelId, levelName, amount) {
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
                        location.href = '/user/level.php';
                    }, 900);
                    return;
                }
                if (res.code == 402) {
                    // 余额不足 → 引导去充值
                    var d = res.data || {};
                    var html = '<div style="line-height:1.8;">'
                        + '当前余额 <b style="color:#ef4444;">¥' + parseFloat(d.balance || 0).toFixed(2) + '</b>，'
                        + '开通需要 <b style="color:var(--theme-primary);">¥' + parseFloat(d.required || 0).toFixed(2) + '</b>，'
                        + '还差 <b style="color:#ef4444;">¥' + parseFloat(d.shortage || 0).toFixed(2) + '</b>。<br>'
                        + '本页仅支持钱包余额开通、续费或升级，是否前往钱包充值？'
                        + '</div>';
                    layer.confirm(html, {
                        title: '余额不足，请先充值',
                        btn: ['前往充值', '取消'],
                        icon: 0,
                        skin: '',
                    }, function () {
                        location.href = d.redirect || balanceUrl;
                    });
                    return;
                }
                layer.msg(res.msg || '提交失败', { icon: 2 });
            },
            complete: function () {
                submitting = false;
            }
        });
    }

    // 自定义弹窗
    var _modalLevelId = 0;
    function showModal(id, name, pay, iconImg, iconCls) {
        _modalLevelId = id;
        $('#lvModalLevel').text(name);
        $('#lvModalPay').text('¥' + pay.toFixed(2));
        var ih = iconImg ? '<img src="'+iconImg+'">' : '<i class="'+(iconCls||'ri-vip-diamond-line')+'"></i>';
        $('#lvModalIcon').html(ih);
        $('#lvModal').addClass('is-show');
    }
    function hideModal() { $('#lvModal').removeClass('is-show'); }

    // 点击开通 → 确认弹窗
    $('.level-card-btn').on('click', function () {
        if ($(this).hasClass('is-disabled')) return;
        var $card = $(this).closest('.level-card');
        var levelId = parseInt($card.attr('data-id'), 10);
        var levelName = $card.attr('data-name');
        var amount = parseFloat($card.attr('data-price'));
        var iconImg = $card.attr('data-icon-image') || '';
        var iconCls = $card.attr('data-icon') || 'ri-vip-diamond-line';
        showModal(levelId, levelName, amount, iconImg, iconCls);
    });
    $('#lvModalCancel, #lvModal').on('click', function (e) { if (e.target === this) hideModal(); });
    $('#lvModalConfirm').on('click', function () { hideModal(); submitUpgrade(_modalLevelId); });
});
</script>

<?php include __DIR__ . '/_pc_page_footer.php'; ?>
<script>
    $('#menu-level').addClass('menu-current');
</script>
