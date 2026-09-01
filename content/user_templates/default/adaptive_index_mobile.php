<?php
defined('DC_ROOT') || exit('access denied!');

$_is_logged_in = defined('ISLOGIN') && ISLOGIN;
$_login_on = Option::get('login_switch') == 'y';
$_register_on = Option::get('register_switch') == 'y';
$_virtual_currency_name = htmlspecialchars(getVirtualCurrencyName(), ENT_QUOTES, 'UTF-8');

if ($_is_logged_in) {
    $avatar_path = isset($userData['photo']) ? $userData['photo'] : '';
    $avatar_url = User::getAvatar($avatar_path);
    $display_name = !empty($userData['nickname']) ? $userData['nickname'] : ($userData['username'] ?? '');
    $display_initial = function_exists('mb_substr') ? mb_substr($display_name, 0, 1, 'UTF-8') : substr($display_name, 0, 1);
    $myInviteCode = !empty($userData['invite_code']) ? $userData['invite_code'] : '';
    $_invDomain = '';
    if (!empty($userData['station']['domain'])) {
        $_invDomain = '//' . $userData['station']['domain'];
    } elseif (!empty($userData['station']['domain_2'])) {
        $_invDomain = '//' . $userData['station']['domain_2'];
    }
    $siteUrl = $_invDomain !== '' ? $_invDomain : rtrim(DC_URL, '/');
    $myInviteLink = $myInviteCode !== '' ? $siteUrl . '/?invite=' . $myInviteCode : '';
    // 会员等级名称
    $_memberModel = new Member_Model();
    $_activeLevelId = class_exists('Level_Service') ? Level_Service::getActiveLevelId($userData) : null;
    if ($_activeLevelId === null) $_activeLevelId = (int)$_memberModel->getDefaultLevelId();
    $_activeLevelRow = $_activeLevelId > 0 ? $_memberModel->getById($_activeLevelId) : null;
    $_levelName = $_activeLevelRow ? $_activeLevelRow['name'] : '';
    if (empty($_levelName)) $_levelName = '普通用户';
    $_levelIcon = ($_activeLevelRow && !empty($_activeLevelRow['icon'])) ? (string)$_activeLevelRow['icon'] : 'ri-vip-diamond-line';
    $_levelIconImage = ($_activeLevelRow && !empty($_activeLevelRow['icon_image'])) ? (string)$_activeLevelRow['icon_image'] : '';
    // 下一等级 & 升级进度
    $_allLevels = $_memberModel->getActiveList(); // sort ASC
    $_curSort = $_activeLevelRow ? (int)$_activeLevelRow['sort'] : 0;
    $_nextLevel = null;
    foreach ($_allLevels as $_lv) {
        if ((int)$_lv['sort'] > $_curSort) { $_nextLevel = $_lv; break; }
    }
    $_upgradePercent = 0;
    $_upgradeHint = '';
    if ($_nextLevel) {
        $_nDirect  = (int)($_nextLevel['upgrade_direct_count'] ?? 0);
        $_nConsume = (float)($_nextLevel['upgrade_consume_amount'] ?? 0);
        $_nTeam    = (int)($_nextLevel['upgrade_team_count'] ?? 0);
        $_hasAnyCond = ($_nDirect > 0 || $_nConsume > 0 || $_nTeam > 0);
        if ($_hasAnyCond) {
            $_pcts = [];
            if ($_nDirect > 0) {
                $_curDirect = $_memberModel->countDirectFans(UID);
                $_pcts[] = min(100, round($_curDirect / $_nDirect * 100));
                $_upgradeHint = '直推 ' . $_curDirect . '/' . $_nDirect;
            }
            if ($_nConsume > 0) {
                $_curConsume = $_memberModel->sumConsumeAmount(UID);
                $_pcts[] = min(100, round($_curConsume / $_nConsume * 100));
                if ($_upgradeHint) $_upgradeHint .= '，';
                $_upgradeHint .= '消费 ' . $_curConsume . '/' . $_nConsume;
            }
            if ($_nTeam > 0) {
                $_curTeam = $_memberModel->countTeamFans(UID);
                $_pcts[] = min(100, round($_curTeam / $_nTeam * 100));
                if ($_upgradeHint) $_upgradeHint .= '，';
                $_upgradeHint .= '团队 ' . $_curTeam . '/' . $_nTeam;
            }
            $_upgradeMode = ($_nextLevel['upgrade_mode'] ?? 'any') === 'all' ? 'all' : 'any';
            $_upgradePercent = $_upgradeMode === 'all' ? min($_pcts) : max($_pcts);
        }
    }
} else {
    $avatar_path = '';
    $avatar_url = '';
    $display_name = '';
    $display_initial = '';
    $myInviteCode = '';
    $myInviteLink = '';
    $_levelName = '';
    $_levelIcon = 'ri-vip-diamond-line';
    $_levelIconImage = '';
}

$home_bulletin_raw = Option::get('home_bulletin');
$bulletin_hash = md5($home_bulletin_raw);
$home_bulletin = html_entity_decode($home_bulletin_raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$roll_bulletin = Option::get('roll_bulletin');
$has_home_bulletin = !empty(trim($home_bulletin));
$roll_items = array_values(array_filter(array_map('trim', preg_split("/\r\n|\r|\n/", (string)$roll_bulletin)), function ($v) {
    return $v !== '';
}));
$has_roll_bulletin = count($roll_items) > 0;

// 移动端个人中心：未启用任何底部导航模板时，补一个“商城首页”快捷入口。
$_current_bottom_nav_tpl = '';
if (class_exists('View') && method_exists('View', 'getCurrentBottomNavTemplateSlug')) {
    $_current_bottom_nav_tpl = trim((string)View::getCurrentBottomNavTemplateSlug());
}
$_show_shop_home_shortcut = ($_current_bottom_nav_tpl === '' || $_current_bottom_nav_tpl === 'em_null_tpl');
?>

<style>
    .uc-app {
        width: 100%;
        max-width: 100%;
        margin: 0;
        padding: env(safe-area-inset-top) env(safe-area-inset-right) calc(18px + env(safe-area-inset-bottom)) env(safe-area-inset-left);
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .uc-top {
        padding: 30px 18px 27px;
        border-radius: 0;
        background: <?= !empty($_ut['mobile_card_gradient']) ? $_ut['mobile_card_gradient'] : 'linear-gradient(135deg, rgba(255,255,255,0.92), rgba(255,255,255,0.76))' ?>;
        border: none;
        box-shadow: none;
        position: relative;
        overflow: hidden;
    }

    .uc-upgrade-card,
    .uc-card,
    .uc-notice {
        margin-left: 16px;
        margin-right: 16px;
    }

    .uc-icon-btn.is-unread {
        position: relative;
    }

    .uc-icon-btn.is-unread::after {
        content: '';
        position: absolute;
        top: 6px;
        right: 6px;
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #ff3b30;
        box-shadow: 0 0 0 2px rgba(255,255,255,0.9);
    }

    .uc-top::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(560px 220px at 10% 0%, rgba(102,126,234,0.22), transparent 60%),
            radial-gradient(520px 220px at 90% 10%, rgba(118,75,162,0.20), transparent 60%);
        pointer-events: none;
    }

    .uc-top-inner {
        position: relative;
        display: flex;
        align-items: center;
        flex-wrap: nowrap;
        gap: 12px;
    }

    .uc-top-assets {
        position: relative;
        margin-top: 20px;
    }

    .uc-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        overflow: hidden;
        flex: 0 0 auto;
        border: 2px solid rgba(255,255,255,0.75);
        box-shadow: 0 8px 22px rgba(0,0,0,0.10);
        background: rgba(255,255,255,0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        color: var(--text-main);
        font-size: 20px;
    }

    .uc-avatar img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }

    .uc-user { min-width: 0; flex: 1 1 0; overflow: hidden; }

    .uc-user-name-row {
        display: flex;
        align-items: center;
        gap: 6px;
        max-width: 100%;
        margin-bottom: 6px;
        min-width: 0;
    }

    .uc-user-name {
        font-size: 16px;
        font-weight: 900;
        color: var(--text-main);
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        min-width: 0;
        flex-shrink: 1;
    }

    .uc-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 999px;
        background: linear-gradient(135deg, var(--theme-primary, #667eea), var(--theme-secondary, #764ba2));
        border: none;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
        flex-shrink: 0;
        line-height: 1.4;
    }
    .uc-badge i { font-size: 13px; line-height: 1; }
    .uc-badge img { width: 14px; height: 14px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }

    .uc-user-sub {
        display: flex;
        align-items: center;
        gap: 6px;
        color: var(--text-sub);
        font-size: 12px;
        font-weight: 500;
    }

    .uc-invite-code-text {
        letter-spacing: 0.5px;
    }

    .uc-invite-code-hidden {
        letter-spacing: 1px;
    }

    .uc-sub-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border: none;
        border-radius: 6px;
        background: rgba(0,0,0,0.05);
        color: var(--text-sub);
        font-size: 12px;
        cursor: pointer;
        padding: 0;
        transition: all 0.15s ease;
        text-decoration: none;
    }
    .uc-sub-btn:active {
        background: var(--theme-primary, #667eea);
        color: #fff;
    }

    .uc-actions { display: flex; gap: 10px; flex: 0 0 auto; }

    .uc-icon-btn {
        width: 40px;
        height: 40px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.75);
        border: none;
        box-shadow: 0 10px 24px rgba(0,0,0,0.08);
        color: var(--text-main);
        text-decoration: none;
        transition: var(--transition);
    }

    .uc-card {
        padding: 18px;
        border-radius: 10px;
        background: linear-gradient(0deg, #fff, #f3f5f8);
        border: none;
        box-shadow: var(--shadow-primary);
        border: 2px solid #fff;
    }

    .uc-card-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 5px;
        color: var(--text-main);
        font-weight: 900;
        font-size: 15px;
    }

    .uc-card-title small { font-weight: 500; color: var(--text-sub); }

    .uc-upgrade-card {
        cursor: pointer;
        border-radius: 14px;
        background: url('<?= DC_URL ?>content/user_templates/default/img/grzx_hy_bj_v2.png') center/100% 100% no-repeat, linear-gradient(135deg, #1e1e2e 0%, #2a2a3d 50%, #1e1e2e 100%);
        padding: 15px 18px 10px;
        position: relative;
        z-index: 3;
        margin-top: -40px;
        overflow: hidden;
        box-shadow: 0 8px 28px rgba(0,0,0,0.18);
    }
    .uc-upgrade-card::before {
        content: '';
        position: absolute;
        top: -30px; right: -20px;
        width: 100px; height: 100px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(200,180,150,0.08) 0%, transparent 70%);
        pointer-events: none;
    }
    .uc-upgrade-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }
    .uc-upgrade-title {
        font-size: 15px;
        font-weight: 800;
        color: #e8dcc8;
        line-height: 1.4;
    }
    .uc-upgrade-cta {
        flex-shrink: 0;
        padding: 3px 10px;
        border-radius: 999px;
        background: linear-gradient(135deg, #d4c4a8, #bfa97e);
        color: #2a2a3d;
        font-size: 11px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        position: relative;
        z-index: 2;
        display: inline-block;
    }
    .uc-upgrade-cta:active { opacity: 0.85; }
    .uc-upgrade-bar-wrap {
        margin-bottom: 8px;
        padding-top: 24px;
        position: relative;
    }
    .uc-upgrade-pct {
        position: absolute;
        top: 0;
        left: <?= $_upgradePercent ?>%;
        transform: translateX(-50%);
        font-size: 11px;
        font-weight: 700;
        color: #a09882;
        background: rgba(255,255,255,0.08);
        padding: 2px 8px;
        border-radius: 6px;
        white-space: nowrap;
    }
    .uc-upgrade-bar {
        height: 6px;
        border-radius: 6px;
        background: rgba(255,255,255,0.1);
        overflow: hidden;
    }
    .uc-upgrade-bar-fill {
        height: 100%;
        border-radius: 6px;
        background: linear-gradient(90deg, #d4c4a8, #bfa97e);
        transition: width 0.6s ease;
    }
    .uc-upgrade-labels {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 6px;
    }
    .uc-upgrade-lv {
        font-size: 11px;
        font-weight: 500;
        color: #8a8396;
    }
    .uc-upgrade-hint {
        font-size: 11px;
        color: #6b6580;
        text-align: center;
        flex: 1;
    }

    .uc-assets {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0px;
    }

    .uc-asset {
        border-radius: 14px;
        padding: 10px 6px;
        background: transparent;
        border: none;
        box-shadow: none;
        text-align: center;
        text-decoration: none;
        color: inherit;
        transition: var(--transition);
        min-width: 0;
    }

    .uc-asset-val {
        font-size: 13px;
        font-weight: 900;
        color: var(--text-main);
        line-height: 1.2;
        margin-bottom: 6px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .uc-asset-lbl {
        font-size: 13px;
        color: var(--text-sub);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .uc-notice {
        padding: 10px 14px;
        border-radius: 10px;
        border: none;
        background: linear-gradient(0deg, #fff, #f3f5f8);
        box-shadow: var(--shadow-primary);
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        border: 2px solid #fff;
    }

    .uc-notice-text {
        color: var(--text-main);
        font-size: 14px;
        font-weight: 800;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        flex: 1;
    }

    .uc-notice-marquee {
        display: inline-block;
        padding-left: 100%;
        animation: ucNoticeMarquee 14s linear infinite;
    }

    @keyframes ucNoticeMarquee {
        0% { transform: translateX(0); }
        100% { transform: translateX(-100%); }
    }

    .uc-notice-ticker {
        flex: 1;
        overflow: hidden;
        height: 20px;
        line-height: 20px;
    }

    .uc-notice-item {
        height: 20px;
        line-height: 20px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: var(--text-main);
        font-size: 14px;
        font-weight: 800;
    }

    .uc-orders {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 10px;
    }

    .uc-order-item {
        text-decoration: none;
        color: inherit;
        background: transparent;
        border: none;
        border-radius: 16px;
        padding: 10px 4px;
        text-align: center;
        transition: var(--transition);
        min-width: 0;
    }

    .uc-order-item:active {
        background: rgba(0,0,0,0.03);
    }

    .uc-order-icon {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(0,0,0,0.00);
        color: var(--text-main);
        margin-bottom: 6px;
        font-size: 25px;
    }

    .uc-order-lbl {
        font-size: 12px;
        color: var(--text-sub);
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .uc-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }

    .uc-grid-item {
        text-decoration: none;
        color: inherit;
        background: transparent;
        border: none;
        border-radius: 18px;
        padding: 14px 6px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        text-align: center;
        transition: var(--transition);
        min-width: 0;
    }

    .uc-grid-item:active {
        background: rgba(0,0,0,0.03);
    }

    .uc-grid-item.is-disabled {
        opacity: 0.55;
        pointer-events: none;
        filter: grayscale(20%);
    }

    .uc-grid-ico {
        width: 46px;
        height: 46px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0,0,0,0.04);
        color: var(--theme-primary);
        font-size: 22px;
    }

    .uc-grid-lbl {
        font-size: 13px;
        font-weight: 900;
        color: var(--text-main);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        width: 100%;
    }

    @media (max-width: 520px) {
        .uc-avatar { width: 56px; height: 56px; }
        .uc-card { padding: 10px 15px 10px 15px; }
        .uc-grid { gap: 10px; grid-template-columns: repeat(4, 1fr); }
    }

    @media (max-width: 420px) {
        .uc-grid { grid-template-columns: repeat(4, 1fr); }
    }

    .uc-invite{padding: 16px; border-radius: 10px; background: linear-gradient(0deg, #fff, #f3f5f8); border: 2px solid #fff; box-shadow: var(--shadow-primary); margin-left: 16px; margin-right: 16px;}
    .uc-invite-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .uc-invite-title i { color: #6366f1; }
    .uc-invite-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }
    .uc-invite-row:last-of-type { margin-bottom: 0; }
    .uc-invite-lbl {
        flex-shrink: 0;
        font-size: 12px;
        color: var(--text-sub);
        width: 50px;
    }
    .uc-invite-val {
        flex: 1;
        min-width: 0;
        height: 34px;
        padding: 0 10px;
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 8px;
        background: rgba(0,0,0,.02);
        font-size: 12px;
        color: var(--text-main);
        display: flex;
        align-items: center;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        user-select: all;
    }
    .uc-invite-copy {
        flex-shrink: 0;
        height: 34px;
        padding: 0 12px;
        border: 1px solid #6366f1;
        border-radius: 8px;
        background: #fff;
        color: #6366f1;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
    }
    .uc-invite-copy:active { background: #6366f1; color: #fff; }
    .uc-invite-tip {
        margin-top: 8px;
        font-size: 11px;
        color: #b0b4bb;
    }
</style>

<style>
    .uc-guest-login-link {
        display: inline-flex;
        align-items: center;
        gap: 2px;
        color: var(--text-sub, #6b7280);
        font-size: 13px;
        text-decoration: none;
        margin-top: 2px;
    }
    .uc-guest-login-link:hover,
    .uc-guest-login-link:active {
        color: var(--theme-primary, #667eea);
        text-decoration: none;
    }
    .uc-guest-avatar-svg {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        display: block;
    }

    /* ===== App \u98ce\u683c\u516c\u544a\u5e95\u90e8\u5f39\u7a97 ===== */
    body.uc-bulletin-open { overflow: hidden; }

    .uc-bulletin-overlay {
        position: fixed;
        inset: 0;
        z-index: 3000;
        background: rgba(15, 23, 42, 0.3);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        display: none;
        align-items: flex-end;
        justify-content: center;
    }
    .uc-bulletin-overlay.is-active { display: flex; }

    .uc-bulletin-sheet {
        width: 100%;
        max-width: 500px;
        max-height: 80vh;
        display: flex;
        flex-direction: column;
        background: #fff;
        border-radius: 20px 20px 0 0;
        box-shadow: 0 -8px 40px rgba(0, 0, 0, 0.12);
        transform: translateY(100%);
        transition: transform 0.28s cubic-bezier(.22, .61, .36, 1);
        overflow: hidden;
    }
    .uc-bulletin-sheet.is-up { transform: translateY(0); }

    .uc-bulletin-handle {
        display: flex;
        justify-content: center;
        padding: 14px 0 6px;
        flex-shrink: 0;
        cursor: grab;
    }
    .uc-bulletin-handle span {
        width: 36px;
        height: 4px;
        border-radius: 4px;
        background: #e2e8f0;
    }

    .uc-bulletin-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 18px 14px;
        border-bottom: 1px solid #f1f5f9;
        flex-shrink: 0;
    }
    .uc-bulletin-icon {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--theme-primary, #667eea), var(--theme-secondary, #764ba2));
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .uc-bulletin-title {
        flex: 1;
        font-size: 17px;
        font-weight: 800;
        color: var(--text-main, #1f2937);
    }
    .uc-bulletin-close {
        width: 32px;
        height: 32px;
        border: none;
        border-radius: 50%;
        background: #f1f5f9;
        color: #94a3b8;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        transition: all 0.15s ease;
    }
    .uc-bulletin-close:active { background: #e2e8f0; color: #475569; }

    .uc-bulletin-body {
        flex: 1 1 auto;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        padding: 18px 20px;
        font-size: 14px;
        line-height: 1.85;
        color: #374151;
        word-break: break-word;
        min-height: 80px;
    }
    .uc-bulletin-body img { max-width: 100%; border-radius: 10px; margin: 8px 0; }
    .uc-bulletin-body a { color: var(--theme-primary, var(--theme-primary)); text-decoration: none; }
    .uc-bulletin-body::-webkit-scrollbar { width: 3px; }
    .uc-bulletin-body::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 3px; }

    .uc-bulletin-footer {
        padding: 12px 18px calc(12px + env(safe-area-inset-bottom, 0px));
        flex-shrink: 0;
    }
    .uc-bulletin-btn {
        width: 100%;
        height: 46px;
        border: none;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--theme-primary, #667eea), var(--theme-secondary, #764ba2));
        color: #fff;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: opacity 0.15s ease;
    }
    .uc-bulletin-btn:active { opacity: 0.85; }
</style>
<div class="uc-app">
    <div class="uc-top">
        <div class="uc-top-inner">
            <?php if ($_is_logged_in): ?>
            <a class="uc-avatar" href="/user/account.php?action=setting" style="text-decoration:none;">
                <img src="<?= htmlspecialchars($avatar_url) ?>" alt="avatar">
            </a>
            <div class="uc-user">
                <div class="uc-user-name-row">
                    <span class="uc-user-name"><?= htmlspecialchars($display_name) ?></span>
                    <span class="uc-badge"><?php if ($_levelIconImage): ?><img src="<?= htmlspecialchars($_levelIconImage) ?>" alt=""><?php else: ?><i class="<?= htmlspecialchars($_levelIcon) ?>"></i><?php endif; ?><?= htmlspecialchars($_levelName) ?></span>
                </div>
                <?php if ($myInviteCode !== ''): ?>
                <div class="uc-user-sub">
                    <span>邀请码: <span class="uc-invite-code-text" id="ucInvCodeVal"><?= htmlspecialchars($myInviteCode) ?></span><span class="uc-invite-code-hidden" id="ucInvCodeMask" style="display:none;"><?= str_repeat('*', mb_strlen($myInviteCode, 'UTF-8')) ?></span></span>
                    <button type="button" class="uc-sub-btn" id="ucInvToggle" title="隐藏/显示"><i class="fa fa-eye"></i></button>
                    <button type="button" class="uc-sub-btn" id="ucInvCopy" title="复制邀请码"><i class="fa fa-copy"></i></button>
                </div>
                <?php endif; ?>
            </div>
            <div class="uc-actions">
                <a class="uc-icon-btn <?= $has_home_bulletin ? 'is-unread' : '' ?>" id="ucBellBtn" href="#" title="通知">
                    <i class="fa fa-bell"></i>
                </a>
                <a class="uc-icon-btn" href="/user/account.php?action=setting" title="设置">
                    <i class="fa fa-cog"></i>
                </a>
            </div>
            <?php else: ?>
            <a class="uc-avatar" href="/user/account.php?action=signin" style="background:#c8c8c8;text-decoration:none;">
                <svg class="uc-guest-avatar-svg" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg"><circle cx="60" cy="60" r="60" fill="#c8c8c8"/><circle cx="60" cy="46" r="20" fill="#fff"/><ellipse cx="60" cy="98" rx="36" ry="28" fill="#fff"/></svg>
            </a>
            <div class="uc-user">
                <a href="/user/account.php?action=signin" class="uc-guest-login-link" style="font-size:17px;font-weight:700;color:var(--text-main,#1f2937);">立即登录 <i class="fa fa-angle-right" style="font-size:18px;"></i></a>
            </div>
            <div class="uc-actions">
                <a class="uc-icon-btn <?= $has_home_bulletin ? 'is-unread' : '' ?>" id="ucBellBtn" href="#" title="通知">
                    <i class="fa fa-bell"></i>
                </a>
            </div>
            <?php endif; ?>
        </div>

        <div class="uc-top-assets">
            <div class="uc-assets">
                <a class="uc-asset" href="<?= $_is_logged_in ? '/user/balance.php' : '#' ?>">
                    <div class="uc-asset-val"><?= $_is_logged_in ? '¥' . number_format($userData['money'], 2) : '--' ?></div>
                    <div class="uc-asset-lbl">账户余额</div>
                </a>
                <a class="uc-asset" href="<?= $_is_logged_in ? '/user/balance.php?action=credits_log' : '#' ?>">
                    <div class="uc-asset-val"><?= $_is_logged_in ? intval($userData['credits']) : '--' ?></div>
                    <div class="uc-asset-lbl">账户<?= $_virtual_currency_name ?></div>
                </a>
                <a class="uc-asset" href="<?= $_is_logged_in ? '/user/balance.php?action=balance_log' : '#' ?>">
                    <div class="uc-asset-val"><?= $_is_logged_in ? '0' : '--' ?></div>
                    <div class="uc-asset-lbl">今日收益</div>
                </a>
                <a class="uc-asset" href="<?= $_is_logged_in ? '/user/balance.php?action=balance_log' : '#' ?>">
                    <div class="uc-asset-val"><?= $_is_logged_in ? '¥' . number_format($userData['expend'], 2) : '--' ?></div>
                    <div class="uc-asset-lbl">累计消费</div>
                </a>
            </div>
        </div>
    </div>

    <?php if($_is_logged_in): ?>
    <div class="uc-upgrade-card" onclick="location.href='/user/level.php'">
        <?php if ($_nextLevel): ?>
        <div class="uc-upgrade-top">
            <div class="uc-upgrade-title"><i class="fa fa-diamond" style="color:#d4c4a8;margin-right:4px;"></i>升级 <?= htmlspecialchars($_nextLevel['name']) ?> 享更多权益</div>
            <a class="uc-upgrade-cta" href="/user/level.php">去升级 &gt;</a>
        </div>
        <?php if ($_upgradePercent > 0 || !empty($_upgradeHint)): ?>
        <div class="uc-upgrade-bar-wrap">
            <span class="uc-upgrade-pct"><?= $_upgradePercent ?>%</span>
            <div class="uc-upgrade-bar"><div class="uc-upgrade-bar-fill" style="width:<?= $_upgradePercent ?>%"></div></div>
        </div>
        <div class="uc-upgrade-labels">
            <span class="uc-upgrade-lv"><?= htmlspecialchars($_levelName) ?></span>
            <span class="uc-upgrade-hint"><?= htmlspecialchars($_upgradeHint) ?></span>
            <span class="uc-upgrade-lv"><?= htmlspecialchars($_nextLevel['name']) ?></span>
        </div>
        <?php else: ?>
        <div class="uc-upgrade-labels">
            <span class="uc-upgrade-lv">当前：<?= htmlspecialchars($_levelName) ?></span>
            <span class="uc-upgrade-lv">目标：<?= htmlspecialchars($_nextLevel['name']) ?></span>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="uc-upgrade-top">
            <div class="uc-upgrade-title"><i class="fa fa-diamond" style="color:#d4c4a8;margin-right:4px;"></i>您已是最高等级 · <?= htmlspecialchars($_levelName) ?></div>
            <a class="uc-upgrade-cta" href="/user/level.php">会员中心 &gt;</a>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="uc-card">
        <div class="uc-card-title">
            <span>订单信息</span>
            <small><a href="/user/order.php" style="color: var(--theme-primary); text-decoration:none;">全部订单</a></small>
        </div>
        <div class="uc-orders">
            <a class="uc-order-item" href="/user/order.php?status=unpaid">
                <div class="uc-order-icon"><i class="ri-wallet-3-line"></i></div>
                <div class="uc-order-lbl">待付款</div>
            </a>
            <a class="uc-order-item" href="/user/order.php?status=pending">
                <div class="uc-order-icon"><i class="ri-inbox-archive-line"></i></div>
                <div class="uc-order-lbl">待发货</div>
            </a>
            <a class="uc-order-item" href="/user/order.php?status=shipped">
                <div class="uc-order-icon"><i class="ri-truck-line"></i></div>
                <div class="uc-order-lbl">待收货</div>
            </a>
            <a class="uc-order-item" href="/user/order.php?status=completed">
                <div class="uc-order-icon"><i class="ri-checkbox-circle-line"></i></div>
                <div class="uc-order-lbl">已完成</div>
            </a>
            <a class="uc-order-item" href="/user/order.php?status=refunding">
                <div class="uc-order-icon"><i class="ri-refund-2-line"></i></div>
                <div class="uc-order-lbl">退款中</div>
            </a>
        </div>
    </div>

    <?php if ($has_roll_bulletin): ?>
    <div class="uc-notice">
        
            <i class="ri-megaphone-line" style="color: var(--text-main);"></i>
        
        <?php if (count($roll_items) <= 1): ?>
            <div class="uc-notice-text"><span class="uc-notice-marquee"><?= htmlspecialchars($roll_items[0] ?? '') ?></span></div>
        <?php else: ?>
            <div class="uc-notice-ticker" id="ucNoticeTicker">
                <div id="ucNoticeTrack">
                    <?php foreach ($roll_items as $it): ?>
                        <div class="uc-notice-item"><?= htmlspecialchars($it) ?></div>
                    <?php endforeach; ?>
                    <div class="uc-notice-item"><?= htmlspecialchars($roll_items[0]) ?></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if($_station_on && $_is_logged_in && !empty($userData['station'])): ?>
    <div class="uc-card">
        <div class="uc-card-title"><span>我的小店</span></div>
        <div class="uc-orders">
            <a class="uc-order-item" href="/user/station.php?action=open">
                <div class="uc-order-icon"><i class="ri-arrow-up-circle-line"></i></div>
                <div class="uc-order-lbl">升级分店</div>
            </a>
            <a class="uc-order-item" href="/user/station.php">
                <div class="uc-order-icon"><i class="ri-dashboard-line"></i></div>
                <div class="uc-order-lbl">店铺概览</div>
            </a>
            <a class="uc-order-item" href="/user/station.php?action=setting">
                <div class="uc-order-icon"><i class="ri-tools-line"></i></div>
                <div class="uc-order-lbl">店铺配置</div>
            </a>
            <a class="uc-order-item" href="/user/station.php?action=master_goods">
                <div class="uc-order-icon"><i class="ri-shopping-bag-line"></i></div>
                <div class="uc-order-lbl">商品管理</div>
            </a>
            <a class="uc-order-item" href="/user/station.php?action=order">
                <div class="uc-order-icon"><i class="ri-file-list-3-line"></i></div>
                <div class="uc-order-lbl">分店订单</div>
            </a>
        </div>
    </div>
    <?php endif; ?>

    <div class="uc-card">
        <div class="uc-card-title"><span>常用功能</span></div>
        <div class="uc-orders" style="grid-template-columns:repeat(5,1fr);flex-wrap:wrap;">
            <?php if($_show_shop_home_shortcut): ?>
            <a class="uc-order-item" href="<?= DC_URL ?>">
                <div class="uc-order-icon"><i class="ri-home-5-line"></i></div>
                <div class="uc-order-lbl" style="color:var(--theme-primary);">商城首页</div>
            </a>
            <?php endif; ?>
            <?php if($_station_on && !($_is_logged_in && !empty($userData['station']))): ?>
            <a class="uc-order-item" href="/user/station.php?action=open">
                <div class="uc-order-icon"><i class="ri-store-2-line"></i></div>
                <div class="uc-order-lbl" style="color:var(--theme-primary);">开通分店</div>
            </a>
            <?php endif; ?>
            <a class="uc-order-item" href="/user/balance.php">
                <div class="uc-order-icon"><i class="ri-wallet-line"></i></div>
                <div class="uc-order-lbl">我的钱包</div>
            </a>
            <a class="uc-order-item" href="/user/balance.php?action=balance_log">
                <div class="uc-order-icon"><i class="ri-exchange-line"></i></div>
                <div class="uc-order-lbl">收支明细</div>
            </a>
            <a class="uc-order-item" href="/user/balance.php#withdraw-log">
                <div class="uc-order-icon"><i class="ri-bank-card-line"></i></div>
                <div class="uc-order-lbl">提现记录</div>
            </a>
            <a class="uc-order-item" href="/user/level.php">
                <div class="uc-order-icon"><i class="ri-vip-crown-line"></i></div>
                <div class="uc-order-lbl">会员中心</div>
            </a>
            <a class="uc-order-item" href="/user/account.php?action=invite">
                <div class="uc-order-icon"><i class="ri-user-add-line"></i></div>
                <div class="uc-order-lbl">邀请好友</div>
            </a>
            <a class="uc-order-item" href="/user/account.php?action=fans">
                <div class="uc-order-icon"><i class="ri-team-line"></i></div>
                <div class="uc-order-lbl">我的粉丝</div>
            </a>
            <a class="uc-order-item" href="/user/footprint.php">
                <div class="uc-order-icon"><i class="ri-footprint-line"></i></div>
                <div class="uc-order-lbl">浏览足迹</div>
            </a>
            <a class="uc-order-item" href="/user/account.php?action=setting">
                <div class="uc-order-icon"><i class="ri-settings-3-line"></i></div>
                <div class="uc-order-lbl">信息设置</div>
            </a>
            <?php if ($_is_logged_in): ?>
            <a class="uc-order-item" href="/user/account.php?action=logout">
                <div class="uc-order-icon"><i class="ri-logout-box-r-line"></i></div>
                <div class="uc-order-lbl">退出登录</div>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    (function () {
        var ticker = document.getElementById('ucNoticeTicker');
        var track = document.getElementById('ucNoticeTrack');
        if (!ticker || !track) return;

        var item0 = track.children && track.children[0];
        if (!item0) return;
        var itemHeight = item0.getBoundingClientRect().height || 20;
        var itemsCount = track.children.length;
        if (itemsCount <= 2) return;

        var step = 0;
        var maxStep = itemsCount - 1;

        function gotoStep(n, animate) {
            if (!track) return;
            track.style.transition = animate ? 'transform 0.45s ease' : 'none';
            track.style.transform = 'translateY(' + (-n * itemHeight) + 'px)';
        }

        gotoStep(0, false);

        track.addEventListener('transitionend', function () {
            if (step >= maxStep) {
                step = 0;
                gotoStep(0, false);
            }
        });

        setInterval(function () {
            step += 1;
            gotoStep(step, true);
        }, 2600);
    })();

    (function () {
        var hasBulletin = <?= $has_home_bulletin ? 'true' : 'false' ?>;
        if (!hasBulletin) {
            var bell0 = document.getElementById('ucBellBtn');
            if (bell0) bell0.classList.remove('is-unread');
            return;
        }

        var uid = <?= $_is_logged_in ? intval($userData['uid']) : 0 ?>;
        var bulletinHash = '<?= $bulletin_hash ?>';
        var readKey = uid > 0 ? ('uc_bulletin_read_' + uid + '_' + bulletinHash) : '';
        var bell = document.getElementById('ucBellBtn');

        try {
            if (readKey && bell && window.localStorage && localStorage.getItem(readKey) === '1') {
                bell.classList.remove('is-unread');
            }
        } catch (e) {}

        if (!bell) return;

        var bulletinHtml = <?= json_encode($home_bulletin, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

        bell.addEventListener('click', function (e) {
            e.preventDefault();
            openBulletinSheet(bulletinHtml);
            try {
                if (readKey && window.localStorage) localStorage.setItem(readKey, '1');
            } catch (err) {}
            bell.classList.remove('is-unread');
        });
    })();

    (function () {
        var overlay = null, sheet = null, body = null;

        function create() {
            if (overlay) return;
            overlay = document.createElement('div');
            overlay.className = 'uc-bulletin-overlay';
            overlay.innerHTML = ''
                + '<div class="uc-bulletin-sheet">'
                +   '<div class="uc-bulletin-handle"><span></span></div>'
                +   '<div class="uc-bulletin-header">'
                +     '<div class="uc-bulletin-icon"><i class="fa fa-bullhorn"></i></div>'
                +     '<div class="uc-bulletin-title">系统公告</div>'
                +     '<button type="button" class="uc-bulletin-close"><i class="fa fa-times"></i></button>'
                +   '</div>'
                +   '<div class="uc-bulletin-body" id="ucBulletinBody"></div>'
                +   '<div class="uc-bulletin-footer">'
                +     '<button type="button" class="uc-bulletin-btn">我知道了</button>'
                +   '</div>'
                + '</div>';
            document.body.appendChild(overlay);
            sheet = overlay.querySelector('.uc-bulletin-sheet');
            body = overlay.querySelector('#ucBulletinBody');
            overlay.addEventListener('click', function (ev) { if (ev.target === overlay) close(); });
            overlay.querySelector('.uc-bulletin-close').addEventListener('click', close);
            overlay.querySelector('.uc-bulletin-btn').addEventListener('click', close);
        }

        function bindSwipeDismiss(el, onDismiss) {
            var startY = 0, currentY = 0, dragging = false;
            el.addEventListener('touchstart', function(e) {
                var scrollable = e.target.closest('.uc-bulletin-body');
                if (scrollable && scrollable.scrollTop > 0) return;
                startY = e.touches[0].clientY;
                currentY = 0;
                dragging = true;
                el.style.transition = 'none';
            }, { passive: true });
            el.addEventListener('touchmove', function(e) {
                if (!dragging) return;
                var dy = e.touches[0].clientY - startY;
                if (dy < 0) { dy = 0; }
                if (dy > 0) { e.preventDefault(); }
                currentY = dy;
                el.style.transform = 'translateY(' + dy + 'px)';
            }, { passive: false });
            el.addEventListener('touchend', function() {
                if (!dragging) return;
                dragging = false;
                el.style.transition = 'transform 0.28s cubic-bezier(.22,.61,.36,1)';
                if (currentY > 80) {
                    el.style.transform = 'translateY(100%)';
                    setTimeout(onDismiss, 200);
                } else {
                    el.style.transform = 'translateY(0)';
                }
            }, { passive: true });
        }

        function open(html) {
            create();
            body.innerHTML = (html && html.trim()) ? html : '<div style="text-align:center;padding:40px 0;color:#9ca3af;"><i class="fa fa-bullhorn" style="font-size:40px;opacity:.3;display:block;margin-bottom:12px;"></i><p>暂无公告</p></div>';
            document.body.classList.add('uc-bulletin-open');
            overlay.classList.add('is-active');
            sheet.style.transform = '';
            requestAnimationFrame(function () { sheet.classList.add('is-up'); });
            if (!sheet.__swipeBound) {
                bindSwipeDismiss(sheet, close);
                sheet.__swipeBound = true;
            }
        }

        function close() {
            if (!overlay) return;
            sheet.style.transition = 'transform 0.28s cubic-bezier(.22,.61,.36,1)';
            sheet.style.transform = '';
            sheet.classList.remove('is-up');
            setTimeout(function () {
                overlay.classList.remove('is-active');
                document.body.classList.remove('uc-bulletin-open');
            }, 260);
        }

        window.openBulletinSheet = open;
    })();

    function mcopy(id) {
        var text = document.getElementById(id).innerText;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function(){ layui.layer.msg('已复制'); });
        } else {
            var ta = document.createElement('textarea');
            ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
            document.body.appendChild(ta); ta.select(); document.execCommand('copy');
            document.body.removeChild(ta); layui.layer.msg('已复制');
        }
    }

    (function () {
        var toggleBtn = document.getElementById('ucInvToggle');
        var copyBtn = document.getElementById('ucInvCopy');
        var codeVal = document.getElementById('ucInvCodeVal');
        var codeMask = document.getElementById('ucInvCodeMask');
        if (!toggleBtn || !codeVal || !codeMask) return;

        var hidden = false;
        toggleBtn.addEventListener('click', function () {
            hidden = !hidden;
            codeVal.style.display = hidden ? 'none' : '';
            codeMask.style.display = hidden ? '' : 'none';
            toggleBtn.querySelector('i').className = hidden ? 'fa fa-eye-slash' : 'fa fa-eye';
        });

        if (copyBtn) {
            copyBtn.addEventListener('click', function () {
                mcopy('ucInvCodeVal');
            });
        }
    })();
</script>
