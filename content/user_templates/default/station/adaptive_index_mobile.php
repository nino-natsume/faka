<?php
defined('DC_ROOT') || exit('access denied!');

$_stationData = (isset($userData['station']) && is_array($userData['station'])) ? $userData['station'] : [];
$_levelData = (isset($currentStationLevel) && is_array($currentStationLevel)) ? $currentStationLevel : ((isset($station) && is_array($station)) ? $station : []);

$stationName = !empty($_stationData['name']) ? $_stationData['name'] : ($_levelData['name'] ?? '我的分店');
$stationTitle = !empty($_stationData['title']) ? $_stationData['title'] : '暂未设置网站标题';
$_siteSubtitle = !empty($_stationData['site_subtitle']) ? $_stationData['site_subtitle'] : '';
$_stationDomainRaw = trim((string)($_stationData['domain'] ?? ''));
$_stationSubDomainRaw = (!empty($_stationData['domain_2_prefix']) && !empty($_stationData['domain_2_suffix'])) ? ($_stationData['domain_2_prefix'] . $_stationData['domain_2_suffix']) : '';
$_stationSlug = !empty($_stationData['slug']) ? trim((string)$_stationData['slug']) : '';
if ($_stationSlug === 'NULL') $_stationSlug = '';
$_stationShareLink = $_stationSlug !== '' ? rtrim((string)Option::get('blogurl'), '/') . '/s/' . $_stationSlug : '';
$_stationCreateTime = !empty($_stationData['create_time']) ? (int)$_stationData['create_time'] : 0;
$stationCreatedAt = $_stationCreateTime > 0 ? date('Y-m-d', $_stationCreateTime) : '--';
$stationOpenDays = $_stationCreateTime > 0 ? max(1, (int)floor((time() - $_stationCreateTime) / 86400) + 1) : 0;

$_todayAmountValue = (float)($today_amount ?? 0);
$_yesterdayAmountValue = (float)($yesterday_amount ?? 0);
$_monthAmountValue = (float)($month_amount ?? 0);
$_totalAmountValue = (float)($total_amount ?? 0);
$_todayOrders = (int)($today_orders ?? 0);
$_yesterdayOrders = (int)($yesterday_orders ?? 0);
$_monthOrders = (int)($month_orders ?? 0);
$_totalOrders = (int)($total_orders ?? 0);
$_todayUsers = (int)($today_users ?? 0);
$_yesterdayUsers = (int)($yesterday_users ?? 0);
$_monthUsers = (int)($month_users ?? 0);
$_totalUsers = (int)($total_users ?? 0);
$_walletBalance = isset($userData['money']) ? (float)$userData['money'] : 0;
$_myStats = (isset($myStats) && is_array($myStats)) ? $myStats : [];
$_subStations = (int)($_myStats['subs'] ?? 0);

$_stationLevelName = !empty($_levelData['name']) ? $_levelData['name'] : '分店版本';
$_stationLevelIcon = trim((string)($_levelData['icon'] ?? ''));
if ($_stationLevelIcon === '') $_stationLevelIcon = 'ri-store-2-line';
$_stationLevelIconImage = trim((string)($_levelData['icon_image'] ?? ''));

$_todayVsYesterdayAmount = $_todayAmountValue - $_yesterdayAmountValue;
$_todayVsYesterdayOrders = $_todayOrders - $_yesterdayOrders;
$_todayVsYesterdayUsers = $_todayUsers - $_yesterdayUsers;
$_todayAvgEarning = $_todayOrders > 0 ? ($_todayAmountValue / $_todayOrders) : 0;
$_monthAvgEarning = $_monthOrders > 0 ? ($_monthAmountValue / $_monthOrders) : 0;
$_totalAvgEarning = $_totalOrders > 0 ? ($_totalAmountValue / $_totalOrders) : 0;
$_orderPerUser = $_totalUsers > 0 ? ($_totalOrders / $_totalUsers) : 0;
?>
<style>
    *{box-sizing:border-box}.uc-site-footer{display:none!important}.so-page,.so-page *{-webkit-tap-highlight-color:transparent}
    .so-page{--so-primary:var(--theme-primary,#667eea);--so-primary-rgb:var(--tp-rgb,102,126,234);min-height:100vh;background:#f4f5f7;color:#273043;padding:12px 12px calc(28px + env(safe-area-inset-bottom,0px));font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;-webkit-font-smoothing:antialiased}
    .so-hero{position:relative;overflow:hidden;border-radius:20px;padding:18px;color:#fff;background:linear-gradient(160deg,var(--theme-primary,#667eea) 0%,var(--theme-secondary,#764ba2) 100%);box-shadow:0 10px 26px rgba(var(--so-primary-rgb),.18)}
    .so-hero:before{content:'';position:absolute;right:-48px;top:-58px;width:178px;height:178px;border-radius:50%;background:rgba(255,255,255,.08)}.so-hero:after{content:'';position:absolute;left:34%;bottom:-54px;width:130px;height:130px;border-radius:50%;background:rgba(255,255,255,.05)}
    .so-hero-top{position:relative;z-index:1;display:flex;align-items:center;gap:12px}.so-store-ico{width:48px;height:48px;border-radius:17px;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;overflow:hidden;color:#fff;font-size:22px;flex-shrink:0}.so-store-ico img{width:100%;height:100%;object-fit:cover;display:block}.so-store-main{flex:1;min-width:0}.so-store-name{font-size:18px;line-height:1.25;font-weight:900;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.so-store-sub{margin-top:4px;color:rgba(255,255,255,.72);font-size:12px;line-height:1.45;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.so-running{flex-shrink:0;height:29px;padding:0 10px;border-radius:999px;background:rgba(255,255,255,.13);border:1px solid rgba(255,255,255,.14);display:inline-flex;align-items:center;gap:5px;color:#fff;font-size:12px;font-weight:900}.so-running i{width:6px;height:6px;border-radius:50%;background:#34d399;box-shadow:0 0 0 4px rgba(52,211,153,.15)}
    .so-income-main{position:relative;z-index:1;margin-top:17px}.so-income-label{display:flex;align-items:center;justify-content:space-between;gap:12px;color:rgba(255,255,255,.72);font-size:12px}.so-income-label a{color:rgba(255,255,255,.82);text-decoration:none;font-size:12px}.so-income-money{margin-top:7px;font-size:36px;line-height:1;font-weight:900;letter-spacing:-.8px;font-feature-settings:'tnum'}.so-income-money small{font-size:18px;font-weight:700;opacity:.9;margin-right:2px}.so-income-note{margin-top:8px;color:rgba(255,255,255,.68);font-size:12px;line-height:1.6}.so-income-note b{color:#fff;font-weight:900}
    .so-hero-stats{position:relative;z-index:1;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:0;margin-top:15px;padding-top:13px;border-top:1px solid rgba(255,255,255,.15)}.so-hero-stat{text-align:center;text-decoration:none;color:inherit;border-right:1px solid rgba(255,255,255,.13);min-width:0}.so-hero-stat:last-child{border-right:0}.so-hero-stat span{display:block;color:rgba(255,255,255,.58);font-size:11px}.so-hero-stat b{display:block;margin-top:5px;color:#fff;font-size:13px;font-weight:900;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .so-section{margin-top:18px}.so-title-row{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:11px}.so-title{color:#252d3b;font-size:16px;font-weight:900}.so-sub{color:#9ca3af;font-size:12px}.so-sub-link{color:#9a7440;font-size:12px;font-weight:800;text-decoration:none}.so-card{background:linear-gradient(0deg, #fff, #f3f5f8);border-radius:10px;border:2px solid #fff;box-shadow:var(--shadow-primary);overflow:hidden}
    .so-quick{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px}.so-quick a{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:7px;min-height:76px;border-radius:10px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;box-shadow:var(--shadow-primary);text-decoration:none;color:#5f6673;font-size:11px;font-weight:800}.so-quick a:active{transform:scale(.98)}.so-quick i{width:34px;height:34px;border-radius:13px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;background:var(--q-bg,linear-gradient(135deg,#667eea,#764ba2));box-shadow:0 5px 12px var(--q-shadow,rgba(102,126,234,.18))}
    .so-metric-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.so-metric{padding:14px 13px;border-radius:10px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;box-shadow:var(--shadow-primary);min-width:0}.so-metric-main{display:flex;align-items:baseline;justify-content:space-between;gap:8px;margin-bottom:11px}.so-metric-name{color:#5f6673;font-size:13px;font-weight:900;white-space:nowrap;flex-shrink:0}.so-metric-value{color:#1f2a3d;font-size:23px;line-height:1.12;font-weight:900;letter-spacing:-.4px;text-align:right;word-break:break-all}.so-metric-value.money{font-size:20px;color:#c88332}.so-metric-meta{display:grid;gap:7px;margin-top:12px}.so-metric-meta div{display:flex;align-items:center;justify-content:space-between;gap:8px;color:#8a94a6;font-size:11px}.so-metric-meta b{color:#303846;font-size:11px;font-weight:900;text-align:right;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .so-ledger{padding:4px 14px}.so-ledger-row{display:flex;align-items:center;gap:11px;padding:13px 0;border-top:1px solid #f2f4f7}.so-ledger-row:first-child{border-top:0}.so-ledger-ico{width:36px;height:36px;border-radius:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:#fff7ed;color:#c88332;font-size:16px}.so-ledger-body{flex:1;min-width:0}.so-ledger-title{color:#303846;font-size:13px;font-weight:900}.so-ledger-desc{margin-top:3px;color:#9ca3af;font-size:11px}.so-ledger-amount{text-align:right;flex-shrink:0;color:#c88332;font-size:14px;font-weight:900}.so-ledger-amount span{display:block;margin-top:3px;color:#a0a7b4;font-size:10px;font-weight:700}
    .so-table-card{padding:0 14px}.so-table-row{display:grid;grid-template-columns:72px repeat(4,minmax(0,1fr));align-items:center;gap:6px;padding:11px 0;border-top:1px solid #f2f4f7}.so-table-row:first-child{border-top:0}.so-table-row.is-head{color:#9ca3af;font-size:10px;font-weight:800}.so-table-label{display:flex;align-items:center;gap:5px;color:#5f6673;font-size:12px;font-weight:900;white-space:nowrap}.so-table-val{color:#303846;font-size:12px;font-weight:900;text-align:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.so-table-row.is-head .so-table-val{text-align:center;color:#9ca3af;font-size:10px}
    .so-info{padding:0 14px}.so-info-head{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:15px 0 12px}.so-info-head strong{color:#252d3b;font-size:15px;font-weight:900;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.so-info-tag{height:24px;padding:0 10px;border-radius:999px;background:#ecfdf5;color:#047857;font-size:11px;font-weight:900;display:flex;align-items:center;gap:4px;white-space:nowrap}.so-info-row{display:flex;align-items:flex-start;gap:10px;padding:12px 0;border-top:1px solid #f2f4f7}.so-info-row i{width:27px;height:27px;border-radius:10px;background:rgba(var(--so-primary-rgb),.08);color:var(--so-primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px}.so-info-row div{flex:1;min-width:0}.so-info-row span{display:block;color:#8a94a6;font-size:11px;margin-bottom:3px}.so-info-row b,.so-info-row a{display:block;color:#303846;font-size:13px;line-height:1.55;font-weight:800;text-decoration:none;word-break:break-all}.so-empty-tag{display:inline-flex!important;align-items:center;width:auto!important;min-height:22px;padding:0 8px;border-radius:999px;background:#fef2f2;color:#ef4444!important;font-size:11px!important;font-weight:900!important}
    .so-tips{padding:13px 14px;border-radius:17px;background:#fffbeb;border:1px solid #fde68a;color:#92400e;font-size:12px;line-height:1.75;box-shadow:0 6px 18px rgba(180,83,9,.04)}.so-tips-title{display:flex;align-items:center;gap:6px;color:#78350f;font-weight:900;margin-bottom:4px}
    @media (max-width:360px){.so-page{padding-left:10px;padding-right:10px}.so-hero{padding-left:15px;padding-right:15px}.so-income-money{font-size:32px}.so-quick{grid-template-columns:repeat(2,minmax(0,1fr))}.so-metric-grid{grid-template-columns:1fr}.so-table-row{grid-template-columns:64px repeat(4,minmax(0,1fr));gap:4px}.so-table-label,.so-table-val{font-size:11px}}
</style>

<main class="so-page">
    <section class="so-hero">
        <div class="so-hero-top">
            <div class="so-store-ico"><?= $_stationLevelIconImage !== '' ? '<img src="' . htmlspecialchars($_stationLevelIconImage, ENT_QUOTES) . '" alt="">' : '<i class="' . htmlspecialchars($_stationLevelIcon, ENT_QUOTES) . '"></i>' ?></div>
            <div class="so-store-main">
                <div class="so-store-name"><?= htmlspecialchars($stationName) ?></div>
                <div class="so-store-sub"><?= $stationOpenDays > 0 ? '您已经经营了 ' . $stationOpenDays . ' 天哦' : '经营中' ?></div>
            </div>
            <span class="so-running"><i></i>经营中</span>
        </div>
        <div class="so-income-main">
            <div class="so-income-label"><span>今日收益</span><a href="/user/balance.php?action=balance_log">收支明细 <i class="fa fa-angle-right"></i></a></div>
            <div class="so-income-money"><small>¥</small><?= number_format($_todayAmountValue, 2) ?></div>
            <div class="so-income-note">今日订单 <b><?= $_todayOrders ?></b> 单，较昨日<?= $_todayVsYesterdayAmount >= 0 ? '增加' : '减少' ?> <b>¥<?= number_format(abs($_todayVsYesterdayAmount), 2) ?></b></div>
        </div>
        <div class="so-hero-stats">
            <a class="so-hero-stat" href="/user/balance.php?action=balance_log"><span>昨日收益</span><b>¥<?= number_format($_yesterdayAmountValue, 2) ?></b></a>
            <a class="so-hero-stat" href="/user/balance.php?action=balance_log"><span>本月收益</span><b>¥<?= number_format($_monthAmountValue, 2) ?></b></a>
            <a class="so-hero-stat" href="/user/balance.php?action=balance_log"><span>累计收益</span><b>¥<?= number_format($_totalAmountValue, 2) ?></b></a>
        </div>
    </section>

    <section class="so-section">
        <div class="so-quick">
            <a href="/user/station.php?action=order" style="--q-bg:linear-gradient(135deg,#35b6ff,#2f75ff);--q-shadow:rgba(47,117,255,.18);"><i class="ri-file-list-3-line"></i><span>分店订单</span></a>
            <a href="/user/balance.php?action=balance_log" style="--q-bg:linear-gradient(135deg,#f1ca86,#e4ad5e);--q-shadow:rgba(222,174,92,.18);"><i class="ri-money-cny-circle-line"></i><span>收入明细</span></a>
            <a href="/user/station.php?action=master_goods" style="--q-bg:linear-gradient(135deg,#ff8f5a,#f97316);--q-shadow:rgba(249,115,22,.18);"><i class="ri-shopping-bag-3-line"></i><span>商品管理</span></a>
            <a href="/user/station.php?action=setting" style="--q-bg:linear-gradient(135deg,#667eea,#7c5cff);--q-shadow:rgba(102,126,234,.18);"><i class="ri-settings-3-line"></i><span>店铺配置</span></a>
        </div>
    </section>

    <section class="so-section">
        <div class="so-title-row"><div class="so-title">经营核心数据</div></div>
        <div class="so-metric-grid">
            <div class="so-metric">
                <div class="so-metric-main"><div class="so-metric-name">今日订单</div><div class="so-metric-value"><?= $_todayOrders ?> 单</div></div>
                <div class="so-metric-meta"><div><span>昨日</span><b><?= $_yesterdayOrders ?> 单</b></div><div><span>本月</span><b><?= $_monthOrders ?> 单</b></div><div><span>累计</span><b><?= $_totalOrders ?> 单</b></div></div>
            </div>
            <div class="so-metric">
                <div class="so-metric-main"><div class="so-metric-name">今日新增</div><div class="so-metric-value"><?= $_todayUsers ?> 人</div></div>
                <div class="so-metric-meta"><div><span>昨日</span><b><?= $_yesterdayUsers ?> 人</b></div><div><span>本月</span><b><?= $_monthUsers ?> 人</b></div><div><span>累计</span><b><?= $_totalUsers ?> 人</b></div></div>
            </div>
        </div>
    </section>


    <section class="so-section">
        <div class="so-title-row"><div class="so-title">订单 / 用户明细</div><a class="so-sub-link" href="/user/station.php?action=order">订单列表</a></div>
        <div class="so-card so-table-card">
            <div class="so-table-row is-head"><div></div><div class="so-table-val">今日</div><div class="so-table-val">昨日</div><div class="so-table-val">本月</div><div class="so-table-val">累计</div></div>
            <div class="so-table-row"><div class="so-table-label"><i class="ri-file-list-3-line"></i>订单</div><div class="so-table-val"><?= $_todayOrders ?></div><div class="so-table-val"><?= $_yesterdayOrders ?></div><div class="so-table-val"><?= $_monthOrders ?></div><div class="so-table-val"><?= $_totalOrders ?></div></div>
            <div class="so-table-row"><div class="so-table-label"><i class="ri-money-cny-circle-line"></i>收益</div><div class="so-table-val">¥<?= number_format($_todayAmountValue, 2) ?></div><div class="so-table-val">¥<?= number_format($_yesterdayAmountValue, 2) ?></div><div class="so-table-val">¥<?= number_format($_monthAmountValue, 2) ?></div><div class="so-table-val">¥<?= number_format($_totalAmountValue, 2) ?></div></div>
            <div class="so-table-row"><div class="so-table-label"><i class="ri-user-add-line"></i>用户</div><div class="so-table-val"><?= $_todayUsers ?></div><div class="so-table-val"><?= $_yesterdayUsers ?></div><div class="so-table-val"><?= $_monthUsers ?></div><div class="so-table-val"><?= $_totalUsers ?></div></div>
            <div class="so-table-row"><div class="so-table-label"><i class="ri-calculator-line"></i>单均</div><div class="so-table-val">¥<?= number_format($_todayAvgEarning, 2) ?></div><div class="so-table-val">--</div><div class="so-table-val">¥<?= number_format($_monthAvgEarning, 2) ?></div><div class="so-table-val">¥<?= number_format($_totalAvgEarning, 2) ?></div></div>
        </div>
    </section>

    <section class="so-section">
        <div class="so-title-row"><div class="so-title">店铺信息</div><a class="so-sub-link" href="/user/station.php?action=setting">编辑资料</a></div>
        <div class="so-card so-info">
            <div class="so-info-head"><strong><?= htmlspecialchars($stationName) ?></strong><span class="so-info-tag"><i class="ri-checkbox-circle-line"></i>已开通</span></div>
            <div class="so-info-row"><i class="ri-vip-diamond-line"></i><div><span>当前分店版本</span><b><?= htmlspecialchars($_stationLevelName) ?></b></div></div>
            <div class="so-info-row"><i class="ri-global-line"></i><div><span>独立域名</span><?php if ($_stationDomainRaw !== ''): ?><a href="//<?= htmlspecialchars($_stationDomainRaw, ENT_QUOTES) ?>" target="_blank"><?= htmlspecialchars($_stationDomainRaw) ?></a><?php else: ?><b class="so-empty-tag">未配置</b><?php endif; ?></div></div>
            <div class="so-info-row"><i class="ri-links-line"></i><div><span>二级域名</span><?php if ($_stationSubDomainRaw !== ''): ?><a href="//<?= htmlspecialchars($_stationSubDomainRaw, ENT_QUOTES) ?>" target="_blank"><?= htmlspecialchars($_stationSubDomainRaw) ?></a><?php else: ?><b class="so-empty-tag">未配置</b><?php endif; ?></div></div>
            <div class="so-info-row"><i class="ri-share-forward-line"></i><div><span>店铺访问链接</span><?php if ($_stationShareLink !== ''): ?><a href="<?= htmlspecialchars($_stationShareLink, ENT_QUOTES) ?>" target="_blank"><?= htmlspecialchars($_stationShareLink) ?></a><?php else: ?><b class="so-empty-tag">未生成</b><?php endif; ?></div></div>
            <div class="so-info-row"><i class="ri-window-line"></i><div><span>网站标题</span><b><?= htmlspecialchars($stationTitle) ?></b></div></div>
            <?php if ($_siteSubtitle !== ''): ?><div class="so-info-row"><i class="ri-message-3-line"></i><div><span>副标题</span><b><?= htmlspecialchars($_siteSubtitle) ?></b></div></div><?php endif; ?>
            <div class="so-info-row"><i class="ri-time-line"></i><div><span>开通时间</span><b><?= $_stationCreateTime > 0 ? date('Y-m-d H:i', $_stationCreateTime) : '--' ?></b></div></div>
        </div>
    </section>

</main>

<script>
    $('#menu-station').addClass('menu-current');
</script>
