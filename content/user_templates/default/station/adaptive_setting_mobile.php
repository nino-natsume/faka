<?php
defined('DC_ROOT') || exit('access denied!');

$domain2Full = '';
if (!empty($userStation['domain_2_prefix']) && !empty($userStation['domain_2_suffix'])) {
    $domain2Full = htmlspecialchars($userStation['domain_2_prefix'] . $userStation['domain_2_suffix']);
}
$domainFull = !empty($userStation['domain']) ? htmlspecialchars($userStation['domain']) : '';
$_slugVal = $userStation['slug'] ?? '';
if ($_slugVal === 'NULL' || $_slugVal === null) $_slugVal = '';
$stationSlug = $_slugVal;
$stationShareLink = $stationSlug !== '' ? rtrim(Option::get('blogurl'), '/') . '/s/' . $stationSlug : '';
$_rollNoticePreview = mb_strlen($userStation['roll_notice'] ?? '') > 60 ? mb_substr($userStation['roll_notice'], 0, 60) . '...' : ($userStation['roll_notice'] ?? '');
$_homeNoticePreview = strip_tags($userStation['home_notice'] ?? '');
$_homeNoticePreview = mb_strlen($_homeNoticePreview) > 60 ? mb_substr($_homeNoticePreview, 0, 60) . '...' : $_homeNoticePreview;
$_domainChangePrice = (float)(Option::get('station_domain_change_price') ?: 0);
$_cnameDomain = trim((string)Option::get('station_cname_domain'));
$_stationLogo = $userStation['logo'] ?? '';
$_stationFavicon = $userStation['favicon'] ?? '';
$_siteDesc = $userStation['site_description'] ?? '';
$_siteKey = $userStation['site_key'] ?? '';
$_logTitleStyle = (int)($userStation['log_title_style'] ?? 0);
$_icp = $userStation['icp'] ?? '';
$_footerInfo = $userStation['footer_info'] ?? '';
$_userAgreement = $userStation['user_agreement'] ?? '';
$_privacyPolicy = $userStation['privacy_policy'] ?? '';
$_agreementSiteName = htmlspecialchars($userStation['name'] ?: Option::get('blogname') ?: '本站');
if (empty($_userAgreement)) {
    $_userAgreement = '<h2>用户服务协议</h2>'
        . '<p>欢迎使用 <strong>' . $_agreementSiteName . '</strong>（以下简称"本站"）提供的虚拟商品自动发卡服务。请您在注册或使用本站服务之前，仔细阅读本协议。</p>';
}
if (empty($_privacyPolicy)) {
    $_privacyPolicy = '<h2>隐私政策</h2>'
        . '<p><strong>' . $_agreementSiteName . '</strong> 非常重视用户的隐私保护。本隐私政策说明我们如何收集、使用、存储和保护您的个人信息。</p>';
}
$_siteDescPreview = mb_strlen($_siteDesc) > 50 ? mb_substr($_siteDesc, 0, 50) . '...' : $_siteDesc;
$_siteKeyPreview = mb_strlen($_siteKey) > 50 ? mb_substr($_siteKey, 0, 50) . '...' : $_siteKey;
$_logTitleLabels = ['商品名称', '商品名称 - 站点标题', '商品名称 - 浏览器标题'];
$_logTitleLabel = $_logTitleLabels[$_logTitleStyle] ?? $_logTitleLabels[0];
$_stationName = $userStation['name'] ?: '我的店铺';
$_stationTitle = $userStation['title'] ?: '未设置';
$_siteSubtitle = $userStation['site_subtitle'] ?? '';
$_hasDomain = !empty($domain2Full) || !empty($domainFull) || (!empty($station_slug_mode) && $station_slug_mode === '1' && !empty($stationSlug));
$_filled = function($value) {
    return trim((string)$value) !== '';
};
$_validStationDomains = array_values(array_filter(array_map('trim', (array)($station_domain ?? []))));
$_basicConfigured = $_filled($userStation['name'] ?? '')
    && $_filled($userStation['title'] ?? '')
    && $_filled($_siteSubtitle)
    && $_filled($_stationLogo)
    && $_filled($_stationFavicon)
    && $_filled($_siteKey)
    && $_filled($_siteDesc)
    && $_filled($_icp)
    && $_filled($_userAgreement)
    && $_filled($_privacyPolicy);
$_domain2Configured = empty($_validStationDomains) || ($_filled($userStation['domain_2_prefix'] ?? '') && $_filled($userStation['domain_2_suffix'] ?? ''));
$_domainConfigured = $_domain2Configured
    && (empty($_cnameDomain) || $_filled($userStation['domain'] ?? ''))
    && (($station_slug_mode ?? '0') !== '1' || $_filled($stationSlug));
$_noticeConfigured = $_filled($userStation['roll_notice'] ?? '') && $_filled($userStation['home_notice'] ?? '');
$_templateConfigured = $_filled($_tplPcName ?? '')
    && $_filled($_tplTelName ?? '')
    && $_filled($_ucPcName ?? '')
    && $_filled($_ucTelName ?? '')
    && $_filled($_bnName ?? '')
    && $_bnName !== '未启用';
?>
<style>
    .uc-site-footer{display:none!important}.sms-page,.sms-page *{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
    .sms-page{--sms-primary:var(--theme-primary,#667eea);--sms-primary-rgb:var(--tp-rgb,102,126,234);--sms-soft:rgba(var(--sms-primary-rgb),.10);min-height:100vh;padding:12px 12px calc(28px + env(safe-area-inset-bottom,0px));background:#f5f6f8;color:#20242c;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif}
    .sms-tabs-wrap{position:sticky;top:calc(50px + env(safe-area-inset-top,0px));z-index:10;margin:-10px -12px 12px;padding:10px 12px 8px;background:rgba(245,245,246,.96);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px)}
    .sms-tabs{position:relative;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:7px;padding:4px;border-radius:10px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;box-shadow:var(--shadow-primary)}
    .sms-tab{position:relative;z-index:1;height:34px;border:0;border-radius:999px;background:transparent;color:#697180;font-size:12px;font-weight:900;white-space:nowrap}.sms-tab.is-active{color:var(--sms-primary);background:var(--sms-soft); border-radius: 10px;}.sms-tab-indicator{position:absolute;left:0;bottom:4px;width:24px;height:3px;border-radius:999px;background:var(--sms-primary);z-index:2;pointer-events:none;will-change:left,width}
    .sms-panels{touch-action:pan-y}.sms-panel{display:none}.sms-panel.is-active{display:block}.sms-card{display:block;margin-bottom:12px;padding:16px;border-radius:10px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;box-shadow:var(--shadow-primary);text-decoration:none;color:inherit}.sms-card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:13px}.sms-card-title{display:flex;align-items:center;gap:9px;color:#20242c}.sms-card-title i{width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:var(--sms-soft);color:var(--sms-primary);font-size:15px;flex-shrink:0}.sms-card-title span{display:block;font-size:14px;font-weight:900;line-height:1.2}.sms-card-title em{display:block;color:#8b95a5;font-size:12px;line-height:1.6;font-style:normal;font-weight:500}.sms-list{display:grid;gap:9px}.sms-row{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:11px 12px;border-radius:14px;background:#f8f9fb}.sms-row-label{color:#7c8797;font-size:12px;font-weight:800;white-space:nowrap}.sms-row-value{min-width:0;color:#20242c;font-size:12px;line-height:1.55;font-weight:900;text-align:right;word-break:break-all}.sms-row-value.is-empty{color:#9ca3af;font-weight:700}.sms-row-value img{max-height:28px;max-width:120px;vertical-align:middle;border-radius:5px}.sms-status{height:28px;padding:0 10px;border-radius:999px;display:flex;align-items:center;gap:5px;font-size:11px;font-weight:900;white-space:nowrap}.sms-status.is-on{background:#ecfdf5;color:#047857}.sms-status.is-off{background:#f3f4f6;color:#6b7280}.sms-soft-tip{margin-top:10px;padding:11px 12px;border-radius:14px;background:#fffbeb;color:#92400e;font-size:12px;line-height:1.7}.sms-template-stack{display:grid;gap:10px}.sms-template-box{padding:13px;border-radius:15px;background:#f8f9fb}.sms-template-title{display:flex;align-items:center;justify-content:space-between;gap:10px;color:#20242c;font-size:13px;font-weight:900}.sms-template-meta{margin-top:8px;color:#7c8797;font-size:12px;line-height:1.7;word-break:break-all}.sms-primary-link{height:42px;margin-top:12px;border-radius:15px;background:linear-gradient(135deg,var(--theme-primary,#667eea),var(--theme-secondary,#764ba2));display:flex;align-items:center;justify-content:center;gap:7px;color:#fff;text-decoration:none;font-size:13px;font-weight:900;box-shadow:0 8px 20px rgba(var(--sms-primary-rgb),.16)}
    @media(max-width:360px){.sms-page{padding-left:10px;padding-right:10px}.sms-tab{font-size:11px}.sms-row{padding-left:10px;padding-right:10px}.sms-row-label,.sms-row-value{font-size:11px}}
</style>

<main class="sms-page">
    <div class="sms-tabs-wrap">
        <div class="sms-tabs">
            <button type="button" class="sms-tab is-active" data-tab="basic">基础信息</button>
            <button type="button" class="sms-tab" data-tab="domain">域名配置</button>
            <button type="button" class="sms-tab" data-tab="notice">站内公告</button>
            <button type="button" class="sms-tab" data-tab="template">分店模板</button>
            <div class="sms-tab-indicator" id="smsTabIndicator"></div>
        </div>
    </div>

    <div class="sms-panels">
        <section class="sms-panel is-active" data-panel="basic">
            <div class="sms-card">
                <div class="sms-card-head">
                    <div class="sms-card-title"><i class="ri-store-2-line"></i><div><span>基础信息</span><em>店铺名称、标题、SEO、底部信息与协议</em></div></div>
                    <span class="sms-status <?= $_basicConfigured ? 'is-on' : 'is-off' ?>"><i class="fa <?= $_basicConfigured ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i><?= $_basicConfigured ? '已配置' : '未全部配置' ?></span>
                </div>
                <div class="sms-list">
                    <div class="sms-row"><span class="sms-row-label">店铺名称</span><span class="sms-row-value"><?= htmlspecialchars($userStation['name'] ?: '未设置') ?></span></div>
                    <div class="sms-row"><span class="sms-row-label">网站标题</span><span class="sms-row-value<?= empty($userStation['title']) ? ' is-empty' : '' ?>"><?= htmlspecialchars($_stationTitle) ?></span></div>
                    <div class="sms-row"><span class="sms-row-label">网站副标题</span><span class="sms-row-value<?= empty($_siteSubtitle) ? ' is-empty' : '' ?>"><?= htmlspecialchars($_siteSubtitle ?: '未设置') ?></span></div>
                    <div class="sms-row"><span class="sms-row-label">网站 Logo</span><span class="sms-row-value<?= empty($_stationLogo) ? ' is-empty' : '' ?>"><?php if (!empty($_stationLogo)): ?><img src="<?= htmlspecialchars(getFileUrl($_stationLogo)) ?>" alt="Logo"><?php else: ?>未设置<?php endif; ?></span></div>
                    <div class="sms-row"><span class="sms-row-label">Favicon</span><span class="sms-row-value<?= empty($_stationFavicon) ? ' is-empty' : '' ?>"><?php if (!empty($_stationFavicon)): ?><img src="<?= htmlspecialchars(getFileUrl($_stationFavicon)) ?>" alt="Favicon" style="max-width:28px;"><?php else: ?>未设置<?php endif; ?></span></div>
                    <div class="sms-row"><span class="sms-row-label">标题方案</span><span class="sms-row-value"><?= htmlspecialchars($_logTitleLabel) ?></span></div>
                    <div class="sms-row"><span class="sms-row-label">关键词</span><span class="sms-row-value<?= empty($_siteKey) ? ' is-empty' : '' ?>"><?= htmlspecialchars($_siteKeyPreview ?: '未设置') ?></span></div>
                    <div class="sms-row"><span class="sms-row-label">站点描述</span><span class="sms-row-value<?= empty($_siteDesc) ? ' is-empty' : '' ?>"><?= htmlspecialchars($_siteDescPreview ?: '未设置') ?></span></div>
                    <div class="sms-row"><span class="sms-row-label">ICP备案号</span><span class="sms-row-value<?= empty($_icp) ? ' is-empty' : '' ?>"><?= htmlspecialchars($_icp ?: '未设置') ?></span></div>
                    <div class="sms-row"><span class="sms-row-label">服务协议</span><span class="sms-row-value"><?= empty($_userAgreement) ? '未设置' : '已配置（' . mb_strlen(strip_tags($_userAgreement)) . '字）' ?></span></div>
                    <div class="sms-row"><span class="sms-row-label">隐私政策</span><span class="sms-row-value"><?= empty($_privacyPolicy) ? '未设置' : '已配置（' . mb_strlen(strip_tags($_privacyPolicy)) . '字）' ?></span></div>
                </div>
                <a class="sms-primary-link" href="?action=setting_basic"><i class="ri-settings-4-line"></i>基础信息修改</a>
            </div>
        </section>

        <section class="sms-panel" data-panel="domain">
            <div class="sms-card">
                <div class="sms-card-head">
                    <div class="sms-card-title"><i class="ri-global-line"></i><div><span>域名配置</span><em>二级域名、独立域名与店铺访问标识</em></div></div>
                    <span class="sms-status <?= $_domainConfigured ? 'is-on' : 'is-off' ?>"><i class="fa <?= $_domainConfigured ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i><?= $_domainConfigured ? '已配置' : '未全部配置' ?></span>
                </div>
                <div class="sms-list">
                    <div class="sms-row"><span class="sms-row-label">二级域名</span><span class="sms-row-value<?= empty($domain2Full) ? ' is-empty' : '' ?>"><?= $domain2Full ?: '未绑定' ?></span></div>
                    <?php if (!empty($_cnameDomain)): ?>
                    <div class="sms-row"><span class="sms-row-label">独立域名</span><span class="sms-row-value<?= empty($domainFull) ? ' is-empty' : '' ?>"><?= $domainFull ?: '未绑定' ?></span></div>
                    <?php endif; ?>
                    <?php if ($station_slug_mode === '1'): ?>
                    <div class="sms-row"><span class="sms-row-label">店铺标识</span><span class="sms-row-value<?= empty($stationSlug) ? ' is-empty' : '' ?>"><?= $stationSlug ? htmlspecialchars($stationShareLink) : '未设置' ?></span></div>
                    <?php endif; ?>
                    <?php if ($_domainChangePrice > 0): ?>
                    <div class="sms-row"><span class="sms-row-label">修改费用</span><span class="sms-row-value" style="color:#d97706;">¥<?= number_format($_domainChangePrice, 2) ?>/次（首次绑定免费）</span></div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($_cnameDomain)): ?><div class="sms-soft-tip">独立域名需先解析 CNAME 到：<?= htmlspecialchars($_cnameDomain) ?></div><?php endif; ?>
                <a class="sms-primary-link" href="?action=setting_domain"><i class="ri-settings-4-line"></i>域名配置修改</a>
            </div>
        </section>

        <section class="sms-panel" data-panel="notice">
            <div class="sms-card">
                <div class="sms-card-head">
                    <div class="sms-card-title"><i class="ri-volume-up-line"></i><div><span>站内公告</span><em>设置小店滚动公告与首页公告</em></div></div>
                    <span class="sms-status <?= $_noticeConfigured ? 'is-on' : 'is-off' ?>"><i class="fa <?= $_noticeConfigured ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i><?= $_noticeConfigured ? '已配置' : '未全部配置' ?></span>
                </div>
                <div class="sms-list">
                    <div class="sms-row"><span class="sms-row-label">滚动公告</span><span class="sms-row-value<?= empty($userStation['roll_notice']) ? ' is-empty' : '' ?>"><?= htmlspecialchars($_rollNoticePreview ?: '未设置') ?></span></div>
                    <div class="sms-row"><span class="sms-row-label">内容公告</span><span class="sms-row-value<?= empty($userStation['home_notice']) ? ' is-empty' : '' ?>"><?= htmlspecialchars($_homeNoticePreview ?: '未设置') ?></span></div>
                </div>
                <a class="sms-primary-link" href="?action=setting_notice"><i class="ri-settings-4-line"></i>站内公告修改</a>
            </div>
        </section>

        <section class="sms-panel" data-panel="template">
            <div class="sms-card">
                <div class="sms-card-head">
                    <div class="sms-card-title"><i class="ri-palette-line"></i><div><span>模板配置</span><em>管理前台模板、用户后台模板与底部导航模板</em></div></div>
                    <span class="sms-status <?= $_templateConfigured ? 'is-on' : 'is-off' ?>"><i class="fa <?= $_templateConfigured ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i><?= $_templateConfigured ? '已配置' : '未全部配置' ?></span>
                </div>
                <div class="sms-template-stack">
                    <div class="sms-template-box"><div class="sms-template-title"><span>前台模板</span><i class="ri-window-line"></i></div><div class="sms-template-meta">电脑端：<?= htmlspecialchars($_tplPcName) ?><br>手机端：<?= htmlspecialchars($_tplTelName) ?></div></div>
                    <div class="sms-template-box"><div class="sms-template-title"><span>后台模板</span><i class="ri-user-settings-line"></i></div><div class="sms-template-meta">电脑端：<?= htmlspecialchars($_ucPcName) ?><br>手机端：<?= htmlspecialchars($_ucTelName) ?></div></div>
                    <div class="sms-template-box"><div class="sms-template-title"><span>底部导航</span><i class="ri-layout-bottom-line"></i></div><div class="sms-template-meta"><?= htmlspecialchars($_bnName) ?></div></div>
                </div>
                <a class="sms-primary-link" href="?action=setting_tpl"><i class="ri-settings-4-line"></i>进入模板配置</a>
            </div>
        </section>
    </div>
</main>

<script>
layui.use(['jquery'], function(){
    var $ = layui.$;
    $('#menu-station').addClass('menu-current');

    var smsTabNames = [];
    $('.sms-tab').each(function(){ smsTabNames.push($(this).data('tab')); });
    var smsCurrentTab = $('.sms-tab.is-active').data('tab') || smsTabNames[0] || 'basic';
    var smsIndicatorTimer = null;

    function moveSmsTabIndicator($tab, animate) {
        var $indicator = $('#smsTabIndicator');
        if (!$tab.length || !$indicator.length) return;
        var tabLeft = $tab.position().left;
        var tabWidth = $tab.outerWidth();
        var indicatorW = 24;
        var targetLeft = tabLeft + (tabWidth - indicatorW) / 2;
        if (!animate || !$indicator.data('inited')) {
            $indicator.css({ left: targetLeft + 'px', width: indicatorW + 'px', transition: 'none' });
            $indicator.data('inited', true);
            return;
        }
        var curLeft = parseFloat($indicator.css('left')) || 0;
        var stretchLeft = targetLeft > curLeft ? curLeft : targetLeft;
        var stretchWidth = Math.abs(targetLeft - curLeft) + indicatorW;
        if (smsIndicatorTimer) clearTimeout(smsIndicatorTimer);
        $indicator.css({
            left: stretchLeft + 'px',
            width: stretchWidth + 'px',
            transition: 'left .16s cubic-bezier(.4,0,.2,1), width .16s cubic-bezier(.4,0,.2,1)'
        });
        smsIndicatorTimer = setTimeout(function(){
            $indicator.css({
                left: targetLeft + 'px',
                width: indicatorW + 'px',
                transition: 'left .13s cubic-bezier(.4,0,.2,1), width .13s cubic-bezier(.4,0,.2,1)'
            });
        }, 200);
    }

    function setStationSettingTab(tab) {
        if ($.inArray(tab, smsTabNames) === -1) tab = smsTabNames[0] || 'basic';
        smsCurrentTab = tab;
        $('.sms-tab').removeClass('is-active');
        $('.sms-tab[data-tab="' + tab + '"]').addClass('is-active');
        moveSmsTabIndicator($('.sms-tab[data-tab="' + tab + '"]'), true);
        $('.sms-panel').removeClass('is-active');
        $('.sms-panel[data-panel="' + tab + '"]').addClass('is-active');
    }

    moveSmsTabIndicator($('.sms-tab.is-active'), false);
    $('.sms-tab').on('click', function(){ setStationSettingTab($(this).data('tab')); });

    var touchStartX = 0, touchStartY = 0, touchMoved = false, ignoreSwipe = false;
    $('.sms-page').on('touchstart', function(e){
        var $target = $(e.target);
        ignoreSwipe = $target.closest('input, textarea, select, [contenteditable="true"]').length > 0;
        touchMoved = false;
        if (ignoreSwipe) return;
        var t = e.originalEvent.touches && e.originalEvent.touches[0];
        if (!t) return;
        touchStartX = t.clientX;
        touchStartY = t.clientY;
    });
    $('.sms-page').on('touchmove', function(e){
        if (ignoreSwipe) return;
        var t = e.originalEvent.touches && e.originalEvent.touches[0];
        if (!t) return;
        var dx = t.clientX - touchStartX;
        var dy = t.clientY - touchStartY;
        if (Math.abs(dx) > 20 && Math.abs(dy) < 42) touchMoved = true;
    });
    $('.sms-page').on('touchend', function(e){
        if (ignoreSwipe) { ignoreSwipe = false; return; }
        if (!touchMoved) return;
        var changed = e.originalEvent.changedTouches && e.originalEvent.changedTouches[0];
        if (!changed) return;
        var diff = changed.clientX - touchStartX;
        if (Math.abs(diff) < 50) return;
        var idx = $.inArray(smsCurrentTab, smsTabNames);
        if (idx < 0) idx = 0;
        if (diff < 0 && idx < smsTabNames.length - 1) idx++;
        else if (diff > 0 && idx > 0) idx--;
        else return;
        setStationSettingTab(smsTabNames[idx]);
    });
});
</script>
