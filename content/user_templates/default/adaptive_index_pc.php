<?php
defined('DC_ROOT') || exit('access denied!');

$avatar_path = isset($userData['photo']) ? $userData['photo'] : '';
$avatar_url = User::getAvatar($avatar_path);
$home_bulletin_raw = Option::get('home_bulletin');
$home_bulletin = html_entity_decode($home_bulletin_raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$roll_bulletin = Option::get('roll_bulletin');
$has_home_bulletin = !empty(preg_replace('/[\s\x{00A0}]+/u', '', strip_tags($home_bulletin)));
$has_roll_bulletin = !empty(preg_replace('/[\s\x{00A0}]+/u', '', strip_tags($roll_bulletin)));
$username = !empty($userData['username']) ? $userData['username'] : '未命名用户';
$displayName = !empty($userData['nickname']) ? $userData['nickname'] : $username;
$balanceAmount = isset($userData['money']) ? floatval($userData['money']) : 0;
$expendAmount = isset($userData['expend']) ? floatval($userData['expend']) : 0;
$telText = empty($userData['tel']) ? '未绑定手机' : $userData['tel'];
$emailText = empty($userData['email']) ? '未绑定邮箱' : $userData['email'];
$profileUid = isset($userData['uid']) ? (int)$userData['uid'] : (int)UID;
$profileRoleRaw = isset($userData['role']) ? User::getRoleName($userData['role'], $profileUid) : '';
// 使用 Level_Service 获取准确的有效等级名称
$memberModel = new Member_Model();
$activeLevelId = class_exists('Level_Service') ? Level_Service::getActiveLevelId($userData) : null;
if ($activeLevelId === null) {
    $activeLevelId = (int)$memberModel->getDefaultLevelId();
}
$activeLevelRow = $activeLevelId > 0 ? $memberModel->getById($activeLevelId) : null;
$profileLevelName = $activeLevelRow ? $activeLevelRow['name'] : $profileRoleRaw;
if (empty($profileLevelName)) {
    $profileLevelName = '普通用户';
}
$profileLevelIcon = ($activeLevelRow && !empty($activeLevelRow['icon'])) ? (string)$activeLevelRow['icon'] : 'ri-vip-diamond-line';
$profileLevelIconImage = ($activeLevelRow && !empty($activeLevelRow['icon_image'])) ? (string)$activeLevelRow['icon_image'] : '';
$_allActiveLevels = $memberModel->getActiveList();
$_lvIndexMap = []; $_i = 0;
foreach ($_allActiveLevels as $_lv) { $_i++; $_lvIndexMap[intval($_lv['id'])] = $_i; }
$profileLevelIndex = ($activeLevelId > 0 && isset($_lvIndexMap[$activeLevelId])) ? $_lvIndexMap[$activeLevelId] : 0;
$stationOpened = !empty($userData['station']);
$stationData = $stationOpened ? $userData['station'] : null;
$stationName = $stationData ? ($stationData['name'] ?? '我的分店') : '';
$stationDomain = '';
if ($stationData) {
    if (!empty($stationData['domain'])) {
        $stationDomain = $stationData['domain'];
    } elseif (!empty($stationData['domain_2'])) {
        $stationDomain = $stationData['domain_2'];
    }
}
$accountSafety = 0;
if (!empty($userData['tel'])) {
    $accountSafety++;
}
if (!empty($userData['email'])) {
    $accountSafety++;
}
if (!empty($userData['photo'])) {
    $accountSafety++;
}
$accountSafetyText = $accountSafety >= 3 ? '高' : ($accountSafety == 2 ? '中' : '基础');
$myInviteCode = !empty($userData['invite_code']) ? $userData['invite_code'] : '';
$_invDomain = '';
if (!empty($userData['station']['domain'])) {
    $_invDomain = '//' . $userData['station']['domain'];
} elseif (!empty($userData['station']['domain_2'])) {
    $_invDomain = '//' . $userData['station']['domain_2'];
}
$siteUrl = $_invDomain !== '' ? $_invDomain : rtrim(DC_URL, '/');
$myInviteLink = $myInviteCode !== '' ? $siteUrl . '/?invite=' . $myInviteCode : '';
?>

<style>
    .center-home-page {
        display: flex;
        flex-direction: column;
        gap: 22px;
        padding: 8px 0 18px;
    }

    .center-home-hero {
        padding: 24px 28px;
        border-radius: 10px;
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        box-shadow: 0 1px 18px #12345b0a;
    }

    .center-home-hero-content {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 18px;
        align-items: center;
    }

    .center-home-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
    }

    .center-home-balance-chip {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        min-height: 42px;
        padding: 0 18px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
        color: #475569;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }

    .center-home-balance-chip strong {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
    }

    .center-home-hero-profile {
        display: flex;
        align-items: center;
        gap: 18px;
        min-width: 0;
    }

    .center-home-hero-profile .center-home-avatar {
        width: 64px;
        height: 64px;
        border: 3px solid #f1f5f9;
        box-shadow: 0 4px 12px rgba(15,23,42,.08);
    }

    .center-home-name-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 8px;
    }

    .center-home-hero-profile .center-home-user-name {
        margin: 0;
        color: var(--text-main, #1e293b);
        font-size: 20px;
    }

    .center-home-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-width: 100px;
        height: 40px;
        padding: 0 16px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #475569;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: .18s ease;
    }

    .center-home-btn:hover {
        color: #1e293b;
        text-decoration: none;
        border-color: #cbd5e1;
        box-shadow: 0 2px 8px rgba(15,23,42,.06);
    }

    .center-home-btn.is-primary {
        background: var(--theme-primary);
        border-color: var(--theme-primary);
        color: #fff;
    }

    .center-home-btn.is-primary:hover {
        background: var(--tp-dark);
        border-color: var(--tp-dark);
        color: #fff;
        box-shadow: 0 4px 14px rgba(var(--tp-rgb),.25);
    }

    .center-home-metrics {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .center-home-metric,
    .center-home-notice,
    .center-home-panel {
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        border-radius: 10px;
        box-shadow: 0 1px 18px #12345b0a;
    }

    .center-home-metric {
        padding: 22px;
    }

    .center-home-metric-label {
        color: var(--text-sub);
        font-size: 13px;
        font-weight: 600;
    }

    .center-home-metric-value {
        margin-top: 10px;
        color: var(--text-main);
        font-size: 26px;
        font-weight: 800;
        line-height: 1.25;
    }


    .center-home-notice {
        padding: 22px 24px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .center-home-notice strong {
        display: block;
        margin-bottom: 8px;
        color: var(--text-main);
        font-size: 16px;
        font-weight: 800;
    }

    .center-home-notice p {
        margin: 0;
        color: var(--text-sub);
        font-size: 13px;
        line-height: 1.85;
    }

    .center-home-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(79,70,229,.08);
        color: #468ae5;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .center-home-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.08fr) minmax(0, .92fr);
        gap: 18px;
    }

    .center-home-panel {
        padding: 24px;
    }

    .center-home-panel-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 18px;
    }

    .center-home-panel-title {
        margin: 0;
        color: var(--text-main);
        font-size: 18px;
        font-weight: 800;
    }

    .center-home-panel-desc {
        margin: 8px 0 0;
        color: var(--text-sub);
        font-size: 13px;
        line-height: 1.8;
    }

    .center-home-inline-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: 999px;
        background: rgba(79,70,229,.08);
        color: #468ae5;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .center-home-user-card {
        display: flex;
        align-items: center;
        gap: 18px;
        padding: 18px;
        border-radius: 10px;
        background: rgba(79,70,229,.04);
        margin-bottom: 18px;
    }

    .center-home-avatar {
        width: 84px;
        height: 84px;
        border-radius: 50%;
        flex: 0 0 auto;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #468ae5, #6366f1);
        color: #fff;
        font-size: 30px;
        font-weight: 900;
        box-shadow: 0 14px 30px rgba(79,70,229,.18);
    }

    .center-home-avatar img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }

    .center-home-user-name {
        margin: 0 0 6px;
        color: var(--text-main);
        font-size: 22px;
        font-weight: 600;
    }

    .center-home-user-subtitle {
        margin: 0;
        color: var(--text-sub);
        font-size: 13px;
        line-height: 1.8;
    }

    .center-home-info-list,
    .center-home-action-list,
    .center-home-notice-content {
        display: grid;
        gap: 12px;
    }

    .center-home-info-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 18px;
        border-radius: 10px;
        background: rgba(79,70,229,.04);
    }

    .center-home-info-label {
        color: var(--text-sub);
        font-size: 13px;
        font-weight: 700;
    }

    .center-home-info-value {
        color: var(--text-main);
        font-size: 15px;
        font-weight: 800;
        text-align: right;
        word-break: break-all;
    }

    .center-home-info-value.is-empty {
        color: var(--text-muted);
        font-weight: 600;
    }

    .order-status-tabs {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
    }

    .order-status-tab {
        flex: 1 0 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 20px 12px;
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
        text-decoration: none;
        white-space: nowrap;
        transition: color .18s ease;
    }

    .order-status-tab i {
        font-size: 24px;
        color: #9ca3af;
        transition: color .18s ease;
    }

    .order-status-tab:hover {
        color: var(--theme-primary);
        text-decoration: none;
    }

    .order-status-tab:hover i {
        color: var(--theme-primary);
    }

    .center-home-notice-content {
        color: var(--text-sub);
        font-size: 14px;
        line-height: 1.85;
    }

    .center-home-notice-content a {
        color: var(--theme-primary);
        text-decoration: none;
    }

    .center-home-notice-content a:hover {
        text-decoration: underline;
    }

    .center-home-notice-empty {
        padding: 48px 20px;
        text-align: center;
        color: var(--text-muted, #9ca3af);
    }

    .center-home-notice-empty .fa {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: .35;
    }

    .center-home-notice-empty p {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
    }

    .center-home-profile-body {
        min-width: 0;
    }

    .center-home-profile-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px;
    }

    .center-home-level-tag {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 30px;
        padding: 0 12px;
        border: 1px solid rgba(79,70,229,.24);
        border-radius: 30px;
        background: rgba(79,70,229,.08);
        color: var(--theme-primary);
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all .2s;
        cursor: pointer;
    }
    .center-home-level-tag:hover {
        background: rgba(79,70,229,.14);
        color: #4338ca;
        text-decoration: none;
    }
    .center-home-level-tag i { font-size: 16px; line-height: 1; }
    .center-home-level-icon-img { width: 18px; height: 18px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }

    .center-home-station-tag {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        min-height: 36px;
        padding: 0 16px;
        border: 1px solid rgba(107,114,128,.2);
        border-radius: 30px;
        background: rgba(107,114,128,.06);
        color: #6b7280;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        transition: all .2s;
        cursor: pointer;
    }
    .center-home-station-tag:hover {
        border-color: rgba(79,70,229,.24);
        color: #468ae5;
        text-decoration: none;
    }
    .center-home-station-tag.is-open {
        border-color: rgba(5,150,105,.2);
        background: rgba(5,150,105,.06);
        color: #059669;
    }
    .center-home-station-tag.is-open:hover {
        border-color: rgba(5,150,105,.3);
        background: rgba(5,150,105,.1);
        color: #047857;
    }

    .center-home-profile-id {
        color: #9ca3af;
        font-size: 16px;
        font-weight: 700;
    }

    .center-home-profile-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #6b7280;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        white-space: nowrap;
    }

    .center-home-profile-link:hover {
        color: #468ae5;
        text-decoration: none;
    }

    @media (max-width: 1200px) {
        .center-home-metrics {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .center-home-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .center-home-hero {
            padding: 20px 18px;
            border-radius: 12px;
        }

        .center-home-hero-content {
            grid-template-columns: 1fr;
        }

        .center-home-actions,
        .center-home-notice {
            justify-content: flex-start;
        }

        .center-home-hero-profile {
            flex-direction: column;
            align-items: flex-start;
        }

        .center-home-name-row {
            flex-wrap: wrap;
        }

        .center-home-notice {
            flex-direction: column;
            align-items: flex-start;
        }

        .center-home-metrics,
        .quick-actions {
            grid-template-columns: 1fr;
        }

        .center-home-panel-head,
        .center-home-info-item,
        .center-home-user-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .center-home-info-value {
            text-align: left;
        }

        .center-home-panel-head,
        .center-home-info-item,
        .center-home-user-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .center-home-profile-id {
            font-size: 15px;
        }
    }

    .invite-panel {
        background: #fff;
        border-radius: 10px;
        border: 1px solid #f0f0f0;
        padding: 20px 24px;
    }

    .invite-panel-title {
        font-size: 15px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .invite-panel-title i {
        color: #6366f1;
    }

    .invite-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .invite-row:last-child {
        margin-bottom: 0;
    }

    .invite-label {
        flex-shrink: 0;
        font-size: 13px;
        color: #86909c;
        width: 60px;
    }

    .invite-value {
        flex: 1;
        height: 38px;
        padding: 0 12px;
        border: 1px solid #e5e6eb;
        border-radius: 8px;
        background: #f7f8fa;
        font-size: 13px;
        color: #1a1a1a;
        display: flex;
        align-items: center;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        user-select: all;
    }

    .invite-copy-btn {
        flex-shrink: 0;
        height: 38px;
        padding: 0 16px;
        border: 1px solid #6366f1;
        border-radius: 8px;
        background: #fff;
        color: #6366f1;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all .2s;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .invite-copy-btn:hover {
        background: #6366f1;
        color: #fff;
    }

    .invite-tip {
        margin-top: 10px;
        font-size: 12px;
        color: #b0b4bb;
    }

</style>

<main class="center-home-page">
    <section class="center-home-hero">
        <div class="center-home-hero-content">
            <div class="center-home-hero-profile">
                <div class="center-home-avatar">
                    <img src="<?= htmlspecialchars($avatar_url) ?>" alt="avatar">
                </div>
                <div class="center-home-profile-body">
                    <div class="center-home-name-row">
                        <h4 class="center-home-user-name"><?= htmlspecialchars($displayName) ?></h4>
                        <a href="/user/level.php" class="center-home-level-tag"><?php if ($profileLevelIconImage): ?><img class="center-home-level-icon-img" src="<?= htmlspecialchars($profileLevelIconImage) ?>" alt=""><?php else: ?><i class="<?= htmlspecialchars($profileLevelIcon) ?>"></i><?php endif; ?><?= htmlspecialchars($profileLevelName) ?></a>
                        <a href="/user/account.php?action=setting" class="center-home-profile-link"><i class="fa fa-pencil"></i> 调整信息</a>
                    </div>
                    <div class="center-home-profile-meta">
                        <?php if ($stationOpened): ?>
                        <a href="/user/station.php" class="center-home-station-tag is-open"><i class="fa fa-home"></i> <?= htmlspecialchars($stationName) ?></a>
                        <?php else: ?>
                        <a href="/user/station.php?action=open" class="center-home-station-tag"><i class="fa fa-home"></i> 未开通分店</a>
                        <?php endif; ?>
                        <span class="center-home-profile-id">ID: <?= $profileUid ?></span>
                    </div>
                </div>
            </div>
            <div class="center-home-actions">
                <div class="center-home-balance-chip"><span>余额</span><strong>¥<?= number_format($balanceAmount, 2) ?></strong></div>
                <a href="/user/balance.php" class="center-home-btn is-primary"><i class="fa fa-plus-circle"></i> 充值</a>
            </div>
        </div>
    </section>

    <section class="center-home-panel">
        <div class="center-home-panel-head">
            <div>
                <h3 class="center-home-panel-title">我的订单</h3>
            </div>
        </div>
        <div class="order-status-tabs">
            <a href="/user/order.php" class="order-status-tab"><i class="fa fa-file-text-o"></i>全部订单</a>
            <a href="/user/order.php#unpaid" class="order-status-tab"><i class="fa fa-clock-o"></i>待付款</a>
            <a href="/user/order.php#pending" class="order-status-tab"><i class="fa fa-cube"></i>待发货</a>
            <a href="/user/order.php#shipped" class="order-status-tab"><i class="fa fa-truck"></i>待收货</a>
            <a href="/user/order.php#paid" class="order-status-tab"><i class="fa fa-check-circle-o"></i>已完成</a>
            <a href="/user/order.php#refunding" class="order-status-tab"><i class="fa fa-refresh"></i>退款中</a>
            <a href="/user/order.php#closed" class="order-status-tab"><i class="fa fa-times-circle-o"></i>已取消</a>
        </div>
    </section>

    <section class="center-home-metrics">
        <div class="center-home-metric">
            <div class="center-home-metric-label">账户余额</div>
            <div class="center-home-metric-value">¥<?= number_format($balanceAmount, 2) ?></div>
        </div>
        <div class="center-home-metric">
            <div class="center-home-metric-label">总消费额</div>
            <div class="center-home-metric-value">¥<?= number_format($expendAmount, 2) ?></div>
        </div>
        <div class="center-home-metric">
            <div class="center-home-metric-label">资料完善度</div>
            <div class="center-home-metric-value"><?= $accountSafety ?>/3</div>
        </div>
        <div class="center-home-metric">
            <div class="center-home-metric-label">安全等级</div>
            <div class="center-home-metric-value"><?= $accountSafetyText ?></div>
        </div>
    </section>


    <section class="center-home-panel">
        <div class="center-home-panel-head">
            <div>
                <h3 class="center-home-panel-title">站点公告</h3>
            </div>
        </div>
        <?php if ($has_home_bulletin): ?>
        <div class="center-home-notice-content">
            <?php if ($has_home_bulletin): ?>
                <div><?= $home_bulletin ?></div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="center-home-notice-empty">
            <i class="fa fa-bullhorn"></i>
            <p>当前站点没有配置公告</p>
        </div>
        <?php endif; ?>
    </section>
</main>

<script>
    $('#menu-index').addClass('menu-current');
    function copyInvite(id) {
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
</script>
<?php include __DIR__ . '/_pc_page_footer.php'; ?>
