<?php
defined('DC_ROOT') || exit('access denied!');
global $userData;
$_directFansCount = isset($directFansCount) ? (int)$directFansCount : 0;
$_referralFansCount = isset($referralFansCount) ? (int)$referralFansCount : 0;
$_totalFansCount = isset($totalFansCount) ? (int)$totalFansCount : ($_directFansCount + $_referralFansCount);
$_activeFansCount = isset($activeFansCount) ? (int)$activeFansCount : 0;
$_potentialFansCount = isset($potentialFansCount) ? (int)$potentialFansCount : max(0, $_totalFansCount - $_activeFansCount);
$_directActiveFansCount = isset($directActiveFansCount) ? (int)$directActiveFansCount : 0;
$_referralActiveFansCount = isset($referralActiveFansCount) ? (int)$referralActiveFansCount : 0;
$_directPotentialFansCount = isset($directPotentialFansCount) ? (int)$directPotentialFansCount : max(0, $_directFansCount - $_directActiveFansCount);
$_referralPotentialFansCount = isset($referralPotentialFansCount) ? (int)$referralPotentialFansCount : max(0, $_referralFansCount - $_referralActiveFansCount);
$_directTodayNew = isset($directTodayNew) ? (int)$directTodayNew : 0;
$_directTodayActive = isset($directTodayActive) ? (int)$directTodayActive : 0;
$_directTodayOrders = isset($directTodayOrders) ? (int)$directTodayOrders : 0;
$_referralTodayNew = isset($referralTodayNew) ? (int)$referralTodayNew : 0;
$_referralTodayActive = isset($referralTodayActive) ? (int)$referralTodayActive : 0;
$_referralTodayOrders = isset($referralTodayOrders) ? (int)$referralTodayOrders : 0;
$_todayNew = $_directTodayNew + $_referralTodayNew;
$_todayActive = $_directTodayActive + $_referralTodayActive;
$_todayOrders = $_directTodayOrders + $_referralTodayOrders;
$_myWechat = trim((string)($myWechat ?? ($userData['wechat'] ?? '')));
$_hasWechat = $_myWechat !== '';
$_superiorName = !empty($mySuperior) ? trim((string)(($mySuperior['nickname'] ?? '') ?: ($mySuperior['username'] ?? ''))) : '';
$_superiorWechat = !empty($mySuperior) ? trim((string)($mySuperior['wechat'] ?? '')) : '';
$_fanStats = [
    'direct' => [
        'total' => $_directFansCount,
        'today_new' => $_directTodayNew,
        'today_active' => $_directTodayActive,
        'today_orders' => $_directTodayOrders,
        'active' => $_directActiveFansCount,
        'potential' => $_directPotentialFansCount,
    ],
    'referral' => [
        'total' => $_referralFansCount,
        'today_new' => $_referralTodayNew,
        'today_active' => $_referralTodayActive,
        'today_orders' => $_referralTodayOrders,
        'active' => $_referralActiveFansCount,
        'potential' => $_referralPotentialFansCount,
    ],
];
?>
<style>
    .uc-site-footer { display: none !important; }
    .mfan-page,
    .mfan-page * {
        box-sizing: border-box;
        -webkit-tap-highlight-color: transparent;
    }
    .mfan-page {
        --mfan-primary: var(--theme-primary, #ff4a58);
        --mfan-primary-rgb: var(--tp-rgb, 255,74,88);
        --mfan-soft: rgba(var(--mfan-primary-rgb), .10);
        min-height: 100vh;
        background: #f5f5f6;
        color: #20242c;
        padding-bottom: calc(28px + env(safe-area-inset-bottom, 0px));
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }
    .mfan-contact {
        width: 100%;
        min-height: 46px;
        padding: 9px 14px;
        display: flex;
        align-items: center;
        gap: 9px;
        background: var(--mfan-soft);
        color: var(--mfan-primary);
        text-decoration: none;
        border: 0;
        border-bottom: 1px solid rgba(var(--mfan-primary-rgb), .08);
        text-align: left;
        font-family: inherit;
        cursor: pointer;
    }
    .mfan-contact i {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid currentColor;
        font-size: 15px;
        flex-shrink: 0;
    }
    .mfan-contact span {
        flex: 1;
        min-width: 0;
        font-size: 14px;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .mfan-contact em {
        flex-shrink: 0;
        font-style: normal;
        height: 28px;
        padding: 0 13px;
        border-radius: 999px;
        border: 1px solid currentColor;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        background: rgba(255,255,255,.35);
    }
    .mfan-app-modal-mask{position:fixed;inset:0;z-index:19999;background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:opacity .25s,visibility .25s}
    .mfan-app-modal-mask.is-show{opacity:1;visibility:visible}
    .mfan-app-modal{width:min(88vw,340px);background:#fff;border-radius:20px;overflow:hidden;transform:translateY(24px) scale(.96);transition:transform .28s cubic-bezier(.22,.61,.36,1);box-shadow:0 20px 50px rgba(0,0,0,.18)}
    .mfan-app-modal-mask.is-show .mfan-app-modal{transform:translateY(0) scale(1)}
    .mfan-app-modal-header{padding:22px 22px 0;text-align:center}
    .mfan-app-modal-icon{width:52px;height:52px;margin:0 auto 12px;border-radius:50%;background:linear-gradient(135deg,var(--theme-primary,#667eea),var(--theme-secondary,#764ba2));color:#fff;display:flex;align-items:center;justify-content:center;font-size:24px}
    .mfan-app-modal-avatar{width:100%;height:100%;border-radius:50%;object-fit:cover;display:block;background:#f0f1f3}
    .mfan-app-modal-title{font-size:17px;font-weight:800;color:#252d3b}
    .mfan-app-modal-body{padding:0px 22px 6px;text-align:center}
    .mfan-app-wechat-field{text-align:left}
    .mfan-app-wechat-label{display:flex;align-items:center;gap:6px;margin-bottom:9px;color:#252d3b;font-size:13px;font-weight:800}
    .mfan-app-wechat-input{width:100%;height:44px;border:1px solid #eceef2;border-radius:14px;outline:none;padding:0 12px;background:#f8f9fb;color:#20242c;font-size:14px;font-weight:700}
    .mfan-app-wechat-input:focus{border-color:rgba(var(--mfan-primary-rgb),.35);box-shadow:0 0 0 4px rgba(var(--mfan-primary-rgb),.12);background:#fff}
    .mfan-app-modal-notice{margin-top:12px;padding:12px;border-radius:13px;background:#eef5ff;border:1px solid #dbeafe;color:#5f6673;font-size:12px;line-height:1.75;text-align:left}
    .mfan-app-modal-notice-title{display:flex;align-items:center;gap:6px;margin-bottom:6px;color:#252d3b;font-size:13px;font-weight:900}
    .mfan-app-modal-notice-text b{color:#252d3b;font-weight:900}
    .mfan-app-modal-foot{display:flex;gap:10px;padding:10px 22px 20px}
    .mfan-app-modal-foot-note{padding:0 22px 18px;text-align:center;color:#a0a7b4;font-size:12px;line-height:1.5}
    .mfan-app-modal-btn{flex:1;height:44px;border:none;border-radius:14px;font-size:15px;font-weight:700;cursor:pointer;transition:.15s}
    .mfan-app-modal-btn:disabled{opacity:.65}
    .mfan-app-modal-cancel{background:#f3f4f6;color:#5f6673}
    .mfan-app-modal-cancel:active{background:#e8ebf0}
    .mfan-app-modal-confirm{background:linear-gradient(135deg,var(--theme-primary,#667eea),var(--theme-secondary,#764ba2));color:#fff;box-shadow:0 6px 16px rgba(var(--mfan-primary-rgb),.22)}
    .mfan-app-modal-confirm:active{transform:scale(.97)}
    .mfan-shell {
        padding: 12px 12px 0;
    }
    .mfan-hero {
        position: relative;
        overflow: hidden;
        border-radius: 18px;
        padding: 18px 18px 16px;
        background:
            radial-gradient(circle at 10% 0%, rgba(255,255,255,.88), rgba(255,255,255,0) 34%),
            linear-gradient(135deg, rgba(var(--mfan-primary-rgb), .035), rgba(var(--mfan-primary-rgb), .07)),
            linear-gradient(132deg, #fff7ec 0%, #f6d5b6 48%, #efbf94 100%);
        box-shadow: 0 8px 24px rgba(163, 106, 55, .10);
        color: #71431d;
    }
    .mfan-hero::after {
        content: '';
        position: absolute;
        right: -42px;
        top: -50px;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: rgba(255,255,255,.34);
    }
    .mfan-hero-head,
    .mfan-hero-stats {
        position: relative;
        z-index: 1;
    }
    .mfan-hero-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
    }
    .mfan-hero-label {
        display: flex;
        align-items: center;
        gap: 7px;
        color: #85562d;
        font-size: 14px;
        font-weight: 800;
    }
    .mfan-hero-total {
        margin-top: 10px;
        font-size: 28px;
        line-height: 1;
        font-weight: 900;
        letter-spacing: -.3px;
        color: #5e3516;
    }
    .mfan-hero-total span {
        margin-left: 3px;
        font-size: 13px;
        font-weight: 800;
    }
    .mfan-hero-actions {
        display: flex;
        align-items: flex-end;
        flex-direction: column;
        gap: 9px;
        flex-shrink: 0;
    }
    .mfan-invite-btn,
    .mfan-detail-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        text-decoration: none;
        white-space: nowrap;
    }
    .mfan-invite-btn {
        min-height: 30px;
        padding: 0 12px 0 9px;
        border-radius: 999px;
        background: rgba(255,255,255,.58);
        color: #70421f;
        border: 1px solid rgba(255,255,255,.72);
        font-size: 12px;
        font-weight: 800;
        box-shadow:
            0 6px 16px rgba(112,66,31,.10),
            inset 0 0 0 1px rgba(var(--mfan-primary-rgb), .08);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
    .mfan-invite-btn i {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(var(--mfan-primary-rgb), .13);
        color: var(--mfan-primary);
        font-size: 14px;
        font-weight: 900;
    }
    .mfan-invite-btn:active {
        transform: scale(.97);
        color: #70421f;
        background: rgba(255,255,255,.72);
    }
    .mfan-detail-link {
        border: none;
        padding: 0;
        background: transparent;
        color: #70421f;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
    }
    .mfan-hero-line {
        position: relative;
        z-index: 1;
        height: 1px;
        margin: 18px 0 14px;
        background: rgba(126,75,34,.16);
    }
    .mfan-hero-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .mfan-hero-stat {
        text-align: center;
        border-right: 1px solid rgba(126,75,34,.18);
        cursor: pointer;
    }
    .mfan-hero-stat:last-child { border-right: none; }
    .mfan-hero-stat b {
        display: block;
        font-size: 18px;
        line-height: 1.1;
        color: #5e3516;
    }
    .mfan-hero-stat b span {
        display: inline;
        margin-top: 0;
        margin-left: 1px;
        color: inherit;
        font-size: 12px;
        font-weight: 800;
    }
    .mfan-hero-stat span {
        display: block;
        margin-top: 6px;
        color: #70421f;
        font-size: 12px;
        line-height: 1.45;
        font-weight: 700;
    }
    .mfan-hero-stat small {
        display: block;
        margin-top: 2px;
        color: rgba(112,66,31,.68);
        font-size: 11px;
        font-weight: 600;
    }
    .mfan-section-title {
        margin: 22px 6px 12px;
        font-size: 19px;
        line-height: 1.2;
        font-weight: 900;
        color: #20242c;
    }
    .mfan-activity,
    .mfan-referrer {
        background: linear-gradient(0deg, #fff, #f3f5f8);
        border-radius: 11px;
        box-shadow: var(--shadow-primary);
        border: 2px solid #fff;
    }
    .mfan-activity {
        overflow: hidden;
        touch-action: pan-y;
    }
    .mfan-activity-tabs{display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); background: linear-gradient(0deg, #fff, #f3f5f8); border: 2px solid #fff; border-radius: 10px 10px 0 0; box-shadow: var(--shadow-primary);}
    .mfan-activity-tab {
        position: relative;
        height: 54px;
        border: none;
        background: transparent;
        color: #20242c;
        font-size: 16px;
        font-weight: 800;
        cursor: pointer;
    }
    .mfan-activity-tab.is-active {
        background: #fff;
        border-radius: 0px 15px 0px 0px;
        box-shadow: var(--shadow-primary);
    }
    .mfan-activity-tab.is-active::after {
        content: '';
        position: absolute;
        left: 50%;
        bottom: 8px;
        width: 27px;
        height: 4px;
        border-radius: 999px;
        transform: translateX(-50%);
        background: var(--mfan-primary);
    }
    .mfan-activity-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        padding: 22px 10px 20px;
        row-gap: 22px;
    }
    .mfan-activity-item {
        text-align: center;
        padding: 0 4px;
        border-right: 1px solid #eef0f3;
    }
    .mfan-activity-item:nth-child(3n) { border-right: none; }
    .mfan-activity-item b {
        display: block;
        color: #20242c;
        font-size: 18px;
        line-height: 1.15;
        font-weight: 900;
    }
    .mfan-activity-item span {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 3px;
        margin-top: 7px;
        color: #59606d;
        font-size: 13px;
        font-weight: 600;
    }
    .mfan-activity-item span i {
        color: #a9b0bb;
        font-size: 14px;
        padding: 6px;
        margin: -6px;
        border-radius: 50%;
        cursor: pointer;
    }
    .mfan-activity-item span i:active {
        background: rgba(var(--mfan-primary-rgb), .08);
        color: var(--mfan-primary);
    }
    .mfan-desc-link {
        display: block;
        margin: 18px auto 0;
        border: none;
        background: transparent;
        color: #333842;
        font-size: 14px;
        font-weight: 800;
        text-decoration: underline;
        cursor: pointer;
    }
    .mfan-referrer {
        margin-top: 16px;
        padding: 15px;
    }
    .mfan-referrer-title {
        font-size: 15px;
        font-weight: 900;
        color: #20242c;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .mfan-referrer-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .mfan-referrer-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
        background: #f0f1f3;
        flex-shrink: 0;
    }
    .mfan-referrer-main { flex: 1; min-width: 0; }
    .mfan-referrer-name {
        font-size: 14px;
        font-weight: 800;
        color: #20242c;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .mfan-referrer-meta {
        margin-top: 3px;
        color: #8a909b;
        font-size: 12px;
    }
    .mfan-referrer-badge {
        flex-shrink: 0;
        padding: 4px 9px;
        border: 0;
        border-radius: 999px;
        background: #ecfdf5;
        color: #059669;
        font-size: 11px;
        font-weight: 800;
        font-family: inherit;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        cursor: pointer;
        white-space: nowrap;
    }
    .mfan-referrer-badge:active {
        transform: scale(.96);
        background: #dff8ec;
    }
    .mfan-superior-wechat-card {
        text-align: center;
    }
    .mfan-superior-wechat-name {
        color: #5f6673;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.5;
    }
    .mfan-superior-wechat-value {
        margin-top: 10px;
        padding: 12px 10px;
        border-radius: 14px;
        background: #f8f9fb;
        color: #20242c;
        font-size: 18px;
        line-height: 1.25;
        font-weight: 900;
        word-break: break-all;
    }
    .mfan-superior-wechat-empty {
        margin-top: 10px;
        padding: 14px 12px;
        border-radius: 14px;
        background: #f8f9fb;
        color: #8b95a5;
        font-size: 13px;
        line-height: 1.7;
        font-weight: 700;
    }
    .mfan-bind-desc {
        margin: 0 0 10px;
        color: #6b7280;
        font-size: 13px;
        line-height: 1.7;
    }
    .mfan-bind-row {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .mfan-bind-row input {
        flex: 1;
        min-width: 0;
        height: 40px;
        border: 1px solid #eceef2;
        border-radius: 12px;
        outline: none;
        padding: 0 12px;
        color: #20242c;
        background: #f8f9fb;
        font-size: 13px;
    }
    .mfan-bind-row button {
        width: 74px;
        height: 40px;
        border: none;
        border-radius: 12px;
        background: var(--mfan-primary);
        color: #fff;
        font-size: 13px;
        font-weight: 800;
    }
    @media (max-width: 360px) {
        .mfan-hero { padding: 16px 14px 14px; }
        .mfan-activity-item span { font-size: 12px; }
    }
</style>

<main class="mfan-page">
    <?php if (!$_hasWechat): ?>
    <button type="button" class="mfan-contact" onclick="openMfanWechatSetting()">
        <i class="ri-wechat-line"></i>
        <span>设置微信号，方便粉丝联系你哦！</span>
        <em>立即设置</em>
    </button>
    <?php endif; ?>

    <div class="mfan-shell">
        <section class="mfan-hero">
            <div class="mfan-hero-head">
                <div>
                    <div class="mfan-hero-label"><i class="ri-user-heart-line"></i> 总粉丝数</div>
                    <div class="mfan-hero-total"><?= $_totalFansCount ?><span>人</span></div>
                </div>
                <div class="mfan-hero-actions">
                    <a class="mfan-invite-btn" href="./account.php?action=invite"><i class="ri-add-line"></i> 邀请好友</a>
                    <a class="mfan-detail-link" href="./account.php?action=fans_detail&type=total">粉丝明细 <i class="ri-arrow-right-s-line"></i></a>
                </div>
            </div>
            <div class="mfan-hero-line"></div>
            <div class="mfan-hero-stats">
                <div class="mfan-hero-stat" onclick="openFansList('direct')">
                    <b><?= $_directFansCount ?><span>人</span></b>
                    <span>直邀粉丝</span>
                    <small>有效 <?= $_directActiveFansCount ?> 人</small>
                </div>
                <div class="mfan-hero-stat" onclick="openFansList('referral')">
                    <b><?= $_referralFansCount ?><span>人</span></b>
                    <span>推荐粉丝</span>
                    <small>有效 <?= $_referralActiveFansCount ?> 人</small>
                </div>
                <div class="mfan-hero-stat" onclick="openFansList('potential')">
                    <b><?= $_potentialFansCount ?><span>人</span></b>
                    <span>潜在粉丝</span>
                    <small>待转化粉丝</small>
                </div>
            </div>
        </section>

        <div class="mfan-section-title">活跃情况</div>
        <section class="mfan-activity">
            <div class="mfan-activity-tabs">
                <button type="button" class="mfan-activity-tab is-active" data-kind="direct" onclick="switchFansStats('direct')">直邀粉丝</button>
                <button type="button" class="mfan-activity-tab" data-kind="referral" onclick="switchFansStats('referral')">推荐粉丝</button>
            </div>
            <div class="mfan-activity-grid">
                <div class="mfan-activity-item"><b id="mfanStatTotal"><?= $_directFansCount ?>人</b><span>总人数 <i class="ri-question-line" data-help="total"></i></span></div>
                <div class="mfan-activity-item"><b id="mfanStatNew"><?= $_directTodayNew ?>人</b><span>今日新增 <i class="ri-question-line" data-help="new"></i></span></div>
                <div class="mfan-activity-item"><b id="mfanStatActive"><?= $_directTodayActive ?>人</b><span>今日活跃 <i class="ri-question-line" data-help="active_today"></i></span></div>
                <div class="mfan-activity-item"><b id="mfanStatOrders"><?= $_directTodayOrders ?>人</b><span>今日下单 <i class="ri-question-line" data-help="orders"></i></span></div>
                <div class="mfan-activity-item"><b id="mfanStatValid"><?= $_directActiveFansCount ?>人</b><span>有效粉丝 <i class="ri-question-line" data-help="valid"></i></span></div>
                <div class="mfan-activity-item"><b id="mfanStatPotential"><?= $_directPotentialFansCount ?>人</b><span>潜在粉丝 <i class="ri-question-line" data-help="potential"></i></span></div>
            </div>
        </section>
        <button type="button" class="mfan-desc-link" onclick="openFansIntro()">粉丝说明</button>

        <section class="mfan-referrer">
            <div class="mfan-referrer-title"><i class="ri-links-line"></i> 我的推荐人</div>
            <?php if (!empty($mySuperior)): ?>
            <div class="mfan-referrer-row">
                <img class="mfan-referrer-avatar" src="<?= htmlspecialchars(User::getAvatar($mySuperior['photo'] ?? '')) ?>" alt="">
                <div class="mfan-referrer-main">
                    <div class="mfan-referrer-name"><?= htmlspecialchars($mySuperior['nickname'] ?: $mySuperior['username']) ?></div>
                    <div class="mfan-referrer-meta">UID: <?= (int)$mySuperior['uid'] ?> · <?= !empty($mySuperior['create_time']) ? date('Y-m-d', $mySuperior['create_time']) : '--' ?></div>
                </div>
                <button type="button" class="mfan-referrer-badge" onclick="openSuperiorWechat()"><i class="ri-wechat-line"></i> 联系TA</button>
            </div>
            <?php else: ?>
            <p class="mfan-bind-desc">还未绑定推荐人，输入推荐人的邀请码即可绑定。</p>
            <div class="mfan-bind-row">
                <input type="text" id="bindInviteCode" placeholder="输入推荐人邀请码" maxlength="16">
                <button type="button" onclick="bindSuperior()">绑定</button>
            </div>
            <?php endif; ?>
        </section>

    </div>
</main>

<div class="mfan-app-modal-mask" id="mfanWechatModal">
    <div class="mfan-app-modal">
        <div class="mfan-app-modal-header">
            <div class="mfan-app-modal-icon"><i class="ri-wechat-line"></i></div>
            <div class="mfan-app-modal-title">设置微信号</div>
        </div>
        <form id="mfanWechatForm">
            <div class="mfan-app-modal-body">
                <div class="mfan-app-wechat-field">
                    <label class="mfan-app-wechat-label" for="mfanWechatInput"><i class="ri-wechat-line"></i> 微信号</label>
                    <input class="mfan-app-wechat-input" id="mfanWechatInput" type="text" maxlength="40" value="<?= htmlspecialchars($_myWechat, ENT_QUOTES) ?>" placeholder="请输入你的微信号">
                </div>
                <div class="mfan-app-modal-notice">
                    <div class="mfan-app-modal-notice-title"><i class="fa fa-info-circle"></i> 温馨提示</div>
                    <div class="mfan-app-modal-notice-text">设置微信号有助您与上级或团队成员沟通交流，经验分享，促进订单成交量。您设置的微信号会展示给您的上级成员和团队成员。如因此涉及到个人隐私，可选择不设置或清空设置。</div>
                </div>
            </div>
            <div class="mfan-app-modal-foot">
                <button type="button" class="mfan-app-modal-btn mfan-app-modal-cancel">稍后再说</button>
                <button type="submit" class="mfan-app-modal-btn mfan-app-modal-confirm" id="mfanWechatSave">保存设置</button>
            </div>
            <div class="mfan-app-modal-foot-note">可在个人信息处修改微信号</div>
        </form>
    </div>
</div>

<?php if (!empty($mySuperior)): ?>
<div class="mfan-app-modal-mask" id="mfanSuperiorWechatModal">
    <div class="mfan-app-modal">
        <div class="mfan-app-modal-header">
            <div class="mfan-app-modal-icon"><img class="mfan-app-modal-avatar" src="<?= htmlspecialchars(User::getAvatar($mySuperior['photo'] ?? '')) ?>" alt=""></div>
        </div>
        <div class="mfan-app-modal-body">
            <div class="mfan-superior-wechat-card">
                <div class="mfan-superior-wechat-name"><?= htmlspecialchars($_superiorName ?: 'TA') ?></div>
                <?php if ($_superiorWechat !== ''): ?>
                <div class="mfan-superior-wechat-value"><?= htmlspecialchars($_superiorWechat) ?></div>
                <?php else: ?>
                <div class="mfan-superior-wechat-empty">推荐人暂未设置微信号</div>
                <?php endif; ?>
            </div>
        </div>
        <div class="mfan-app-modal-foot">
            <button type="button" class="mfan-app-modal-btn mfan-app-modal-cancel">知道了</button>
            <?php if ($_superiorWechat !== ''): ?>
            <button type="button" class="mfan-app-modal-btn mfan-app-modal-confirm" id="mfanCopySuperiorWechat">复制微信号</button>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    $('#menu-fans').addClass('menu-current');
    var mfanStats = <?= json_encode($_fanStats, JSON_UNESCAPED_UNICODE) ?>;
    var mfanCurrentStatsKind = 'direct';
    var mfanStatsKinds = ['direct', 'referral'];
    var mfanWechatValue = <?= json_encode($_myWechat, JSON_UNESCAPED_UNICODE) ?>;
    var mfanWechatToken = '<?= LoginAuth::genToken() ?>';
    var mfanSuperiorWechat = <?= json_encode($_superiorWechat, JSON_UNESCAPED_UNICODE) ?>;

    function showMfanAppModal(selector) {
        $(selector).addClass('is-show');
    }

    function hideMfanAppModal(selector) {
        $(selector).removeClass('is-show');
    }

    function openMfanWechatSetting() {
        $('#mfanWechatInput').val(mfanWechatValue || '');
        $('#mfanWechatSave').prop('disabled', false).text('保存设置');
        showMfanAppModal('#mfanWechatModal');
        setTimeout(function(){ $('#mfanWechatInput').trigger('focus'); }, 180);
    }

    $('#mfanWechatForm').on('submit', function(e) {
        e.preventDefault();
        var val = $.trim($('#mfanWechatInput').val() || '');
        val = val.replace(/\s+/g, '');
        if (!val) {
            return layui.layer.msg('请输入微信号，暂不设置可关闭弹窗', {icon: 0});
        }
        if (!/^[A-Za-z0-9_\-]{3,40}$/.test(val)) {
            return layui.layer.msg('微信号仅支持3-40位字母、数字、下划线或中划线', {icon: 0});
        }
        var $btn = $('#mfanWechatSave').prop('disabled', true).text('保存中...');
        $.ajax({
            url: './account.php?action=fans_save_wechat',
            type: 'POST',
            dataType: 'json',
            data: { wechat: val, token: mfanWechatToken },
            success: function(res) {
                if (res && res.code === 0) {
                    mfanWechatValue = val;
                    layui.layer.msg(res.data || '微信号已保存', {icon: 1});
                    hideMfanAppModal('#mfanWechatModal');
                    $('.mfan-contact').slideUp(180, function(){ $(this).remove(); });
                } else {
                    layui.layer.msg((res && res.msg) || '保存失败', {icon: 2});
                }
            },
            error: function(xhr) {
                var res = xhr.responseJSON || {};
                layui.layer.msg(res.msg || '保存失败，请稍后重试', {icon: 2});
            },
            complete: function() {
                $btn.prop('disabled', false).text('保存设置');
            }
        });
        return false;
    });

    $('.mfan-app-modal-cancel').on('click', function() {
        var $mask = $(this).closest('.mfan-app-modal-mask');
        hideMfanAppModal('#' + $mask.attr('id'));
    });

    $('.mfan-app-modal-mask').on('click', function(e) {
        if (e.target !== this) return;
        hideMfanAppModal('#' + this.id);
    });

    function openSuperiorWechat() {
        showMfanAppModal('#mfanSuperiorWechatModal');
    }

    $('#mfanCopySuperiorWechat').on('click', function() {
        var text = $.trim(mfanSuperiorWechat || '');
        if (!text) return layui.layer.msg('推荐人暂未设置微信号', {icon: 0});
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                layui.layer.msg('微信号已复制', {icon: 1});
            }, function() {
                layui.layer.msg('复制失败，请手动长按复制', {icon: 0});
            });
            return;
        }
        var input = $('<input>').val(text).appendTo('body').select();
        document.execCommand('copy');
        input.remove();
        layui.layer.msg('微信号已复制', {icon: 1});
    });

    function openFansList(type) {
        location.href = './account.php?action=fans_detail&type=' + encodeURIComponent(type || 'direct');
    }

    var mfanHelpText = {
        total: {
            title: '总人数',
            content: '当前切换类型下的粉丝总数。直邀粉丝为您直接邀请注册的用户，推荐粉丝为下级继续邀请产生的用户。'
        },
        new: {
            title: '今日新增',
            content: '今天 0 点后新注册并归属到当前类型的粉丝人数。'
        },
        active_today: {
            title: '今日活跃',
            content: '今天有资料更新或登录活跃痕迹的粉丝人数，用于观察粉丝近期活跃度。'
        },
        orders: {
            title: '今日下单',
            content: '今天产生订单记录的粉丝人数。同一粉丝今天多次下单只按 1 人统计。'
        },
        valid: {
            title: '有效粉丝',
            content: '已有付款记录的粉丝。系统会根据订单支付时间或已支付状态判断。'
        },
        potential: {
            title: '潜在粉丝',
            content: '暂未产生付款记录的粉丝，可继续通过活动、优惠或提醒引导完成首购。'
        }
    };

    function openFansHelp(key) {
        var info = mfanHelpText[key] || {
            title: '说明',
            content: '这里展示当前粉丝数据的统计说明。'
        };
        layui.layer.open({
            type: 1,
            title: false,
            area: ['84%', 'auto'],
            shadeClose: true,
            closeBtn: 0,
            content: '<div style="padding:18px 16px 16px;line-height:1.85;color:#4b5563;font-size:13px;">'
                + '<div style="text-align:center;color:#20242c;font-size:16px;font-weight:900;margin-bottom:10px;">' + info.title + '</div>'
                + '<div>' + info.content + '</div>'
                + '<button type="button" onclick="layui.layer.closeAll()" style="display:block;width:100%;height:40px;margin-top:16px;border:none;border-radius:999px;background:var(--theme-primary,#667eea);color:#fff;font-size:14px;font-weight:800;">知道了</button>'
                + '</div>',
            success: function(layero) {
                $(layero).css({'border-radius':'16px','overflow':'hidden'});
            }
        });
    }

    $(document).on('click', '.mfan-activity-item .ri-question-line', function(e) {
        e.preventDefault();
        e.stopPropagation();
        openFansHelp($(this).data('help'));
    });

    function switchFansStats(kind) {
        kind = mfanStats[kind] ? kind : 'direct';
        mfanCurrentStatsKind = kind;
        var s = mfanStats[kind] || mfanStats.direct;
        $('.mfan-activity-tab').removeClass('is-active');
        $('.mfan-activity-tab[data-kind="' + kind + '"]').addClass('is-active');
        $('#mfanStatTotal').text((s.total || 0) + '人');
        $('#mfanStatNew').text((s.today_new || 0) + '人');
        $('#mfanStatActive').text((s.today_active || 0) + '人');
        $('#mfanStatOrders').text((s.today_orders || 0) + '人');
        $('#mfanStatValid').text((s.active || 0) + '人');
        $('#mfanStatPotential').text((s.potential || 0) + '人');
    }

    $(function() {
        var touchStartX = 0;
        var touchStartY = 0;
        var touchMoved = false;
        var ignoreStatsSwipe = false;
        $('.mfan-activity').on('touchstart', function(e) {
            ignoreStatsSwipe = $(e.target).closest('.ri-question-line').length > 0;
            touchMoved = false;
            if (ignoreStatsSwipe) return;
            var t = e.originalEvent.touches && e.originalEvent.touches[0];
            if (!t) return;
            touchStartX = t.clientX;
            touchStartY = t.clientY;
        });
        $('.mfan-activity').on('touchmove', function(e) {
            if (ignoreStatsSwipe) return;
            var t = e.originalEvent.touches && e.originalEvent.touches[0];
            if (!t) return;
            var dx = t.clientX - touchStartX;
            var dy = t.clientY - touchStartY;
            if (Math.abs(dx) > 20 && Math.abs(dy) < 40) {
                touchMoved = true;
            }
        });
        $('.mfan-activity').on('touchend', function(e) {
            if (ignoreStatsSwipe) {
                ignoreStatsSwipe = false;
                return;
            }
            if (!touchMoved) return;
            var changed = e.originalEvent.changedTouches && e.originalEvent.changedTouches[0];
            if (!changed) return;
            var diff = changed.clientX - touchStartX;
            if (Math.abs(diff) < 50) return;
            var idx = mfanStatsKinds.indexOf(mfanCurrentStatsKind);
            if (idx < 0) idx = 0;
            if (diff < 0 && idx < mfanStatsKinds.length - 1) {
                idx++;
            } else if (diff > 0 && idx > 0) {
                idx--;
            } else {
                return;
            }
            switchFansStats(mfanStatsKinds[idx]);
        });
    });

    function openFansIntro() {
        var html = '<div style="padding:18px 16px 16px;line-height:1.85;color:#4b5563;font-size:13px;">'
            + '<div style="text-align:center;color:#20242c;font-size:16px;font-weight:900;margin-bottom:10px;">粉丝说明</div>'
            + '<p><b>直邀粉丝：</b>通过您的邀请链接、邀请码或海报二维码注册的用户。</p>'
            + '<p><b>推荐粉丝：</b>由您的直邀粉丝继续邀请产生的下级粉丝。</p>'
            + '<p><b>有效粉丝：</b>已有付款记录的粉丝。</p>'
            + '<p><b>潜在粉丝：</b>暂未付款的粉丝，可继续引导转化。</p>'
            + '</div>';
        layui.layer.open({
            type: 1,
            title: false,
            area: ['86%', 'auto'],
            content: html,
            shadeClose: true,
            closeBtn: 0,
            success: function(layero){ $(layero).css({'border-radius':'16px','overflow':'hidden'}); }
        });
    }

    function bindSuperior() {
        var code = ($('#bindInviteCode').val() || '').trim();
        if (!code) { layui.layer.msg('请输入邀请码'); return; }
        layui.layer.confirm('确认绑定该邀请码为您的推荐人？绑定后不可更改。', { title: '绑定确认', btn: ['确认绑定', '取消'] }, function(idx) {
            layui.layer.close(idx);
            $.post('./account.php?action=fans_bind_superior', { invite_code: code }, function(res) {
                if (res.code === 0) {
                    layui.layer.msg(res.data || '已成功绑定推荐人', { icon: 1 });
                    setTimeout(function() { location.reload(); }, 1000);
                } else {
                    layui.layer.msg(res.msg || '绑定失败', { icon: 2 });
                }
            }, 'json');
        });
    }

</script>
