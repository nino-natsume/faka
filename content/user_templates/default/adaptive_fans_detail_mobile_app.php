<?php
/**
 * 移动端 APP 粉丝明细页
 */
defined('DC_ROOT') || exit('access denied!');
$_fansTypeMap = [
    'total' => '总粉丝',
    'direct' => '直邀粉丝',
    'referral' => '推荐粉丝',
    'active' => '有效粉丝',
    'potential' => '潜在粉丝'
];
$_fansType = isset($fansType) ? (string)$fansType : 'total';
if (!isset($_fansTypeMap[$_fansType])) $_fansType = 'total';
?>
<style>
    .uc-site-footer { display: none !important; }
    .mfand-page,
    .mfand-page * {
        box-sizing: border-box;
        -webkit-tap-highlight-color: transparent;
    }
    .mfand-page {
        --mfand-primary: var(--theme-primary, #ff4a58);
        --mfand-primary-rgb: var(--tp-rgb, 255,74,88);
        --mfand-soft: rgba(var(--mfand-primary-rgb), .10);
        min-height: 100vh;
        background: #f5f5f6;
        color: #20242c;
        padding: 10px 12px calc(28px + env(safe-area-inset-bottom, 0px));
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }
    .mfand-tabs-wrap {
        position: sticky;
        top: calc(50px + env(safe-area-inset-top, 0px));
        z-index: 10;
        margin: -10px -12px 12px;
        padding: 10px 12px 8px;
        background: rgba(245,245,246,.96);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
    .mfand-tabs{position: relative; display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 7px; padding: 4px; border-radius: 10px; background: linear-gradient(0deg, #fff, #f3f5f8); box-shadow: var(--shadow-primary); border: 2px solid #fff;}
    .mfand-tab {
        height: 34px;
        border: none;
        border-radius: 999px;
        background: transparent;
        color: #697180;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
        cursor: pointer;
        position: relative;
        z-index: 1;
    }
    .mfand-tab.is-active {
        background: var(--mfand-soft);
        color: var(--mfand-primary); border-radius: 10px;
    }
    .mfand-tab-indicator {
        position: absolute;
        left: 0;
        bottom: 4px;
        height: 3px;
        width: 24px;
        border-radius: 999px;
        background: var(--mfand-primary);
        z-index: 2;
        will-change: left, width;
        pointer-events: none;
    }
    .mfand-summary {
        position: relative;
        overflow: hidden;
        border-radius: 17px;
        padding: 16px 16px 14px;
        background:
            radial-gradient(circle at 8% 0%, rgba(255,255,255,.86), rgba(255,255,255,0) 34%),
            linear-gradient(132deg, #fff7ec 0%, #f6d5b6 48%, #efbf94 100%);
        color: #71431d;
        box-shadow: 0 8px 24px rgba(163,106,55,.10);
        margin-bottom: 12px;
    }
    .mfand-summary::after {
        content: '';
        position: absolute;
        right: -44px;
        top: -52px;
        width: 140px;
        height: 140px;
        border-radius: 50%;
        background: rgba(255,255,255,.34);
    }
    .mfand-summary-main { position: relative; z-index: 1; }
    .mfand-summary-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        font-size: 16px;
        font-weight: 900;
    }
    .mfand-summary-title span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .mfand-summary-count {
        margin-top: 8px;
        font-size: 28px;
        font-weight: 900;
        line-height: 1;
        color: #5e3516;
    }
    .mfand-summary-count small {
        margin-left: 3px;
        font-size: 13px;
        font-weight: 800;
    }
    .mfand-summary-desc {
        margin-top: 8px;
        color: rgba(112,66,31,.72);
        font-size: 12px;
        line-height: 1.7;
        font-weight: 600;
    }
    .mfand-list-card {
        border-radius: 10px;
        background: linear-gradient(0deg, #fff, #f3f5f8);
        box-shadow: var(--shadow-primary);
        border: 2px solid #fff;
        padding: 13px 12px 16px;
    }
    .mfand-list-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
        padding: 0 4px;
    }
    .mfand-list-head b {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #20242c;
        font-size: 16px;
        font-weight: 900;
    }
    .mfand-list-head span {
        color: #9aa1ad;
        font-size: 12px;
        font-weight: 700;
    }
    .mfand-sort-bar {
        height: 38px;
        border-radius: 999px;
        background: #f6f7f9;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        color: #7b8290;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 12px;
    }
    .mfand-sort-bar b {
        color: var(--mfand-primary);
        font-weight: 900;
    }
    .mfand-list { min-height: 260px; }
    .mfand-loading,
    .mfand-empty {
        text-align: center;
        color: #a3a8b3;
        font-size: 13px;
        padding: 60px 16px;
    }
    .mfand-loading i {
        display: inline-block;
        margin-right: 4px;
        animation: mfandSpin .8s linear infinite;
    }
    @keyframes mfandSpin { to { transform: rotate(360deg); } }
    .mfand-empty svg {
        display: block;
        width: 140px;
        height: auto;
        margin: 0 auto 10px;
    }
    .mfand-fan-item {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 12px 4px;
        border-bottom: 1px solid #f1f2f4;
    }
    .mfand-fan-item:last-child { border-bottom: none; }
    .mfand-avatar {
        position: relative;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        overflow: visible;
        background: var(--mfand-soft);
        color: var(--mfand-primary);
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        font-weight: 900;
    }
    .mfand-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        border-radius: 50%;
    }
    .mfand-avatar-level {
        position: absolute;
        left: 50%;
        bottom: -7px;
        z-index: 2;
        max-width: 64px;
        padding: 1px 6px;
        border: 1px solid #fff;
        border-radius: 999px;
        background: var(--mfand-soft);
        color: var(--mfand-primary);
        box-shadow: 0 3px 8px rgba(var(--mfand-primary-rgb), .12);
        font-size: 9px;
        font-weight: 800;
        line-height: 1.35;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transform: translateX(-50%);
    }
    .mfand-fan-main { flex: 1; min-width: 0; }
    .mfand-fan-action { flex-shrink: 0; }
    .mfand-fan-name {
        display: flex;
        align-items: center;
        min-width: 0;
        color: #20242c;
        font-size: 14px;
        font-weight: 900;
    }
    .mfand-fan-name strong {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .mfand-uid {
        flex-shrink: 0;
        margin-left: 6px;
        padding: 1px 6px;
        border-radius: 999px;
        background: #eee;
        color: #8b95a5;
        font-size: 10px;
        font-weight: 400;
    }
    .mfand-badge {
        flex-shrink: 0;
        margin-left: 5px;
        padding: 1px 6px;
        border-radius: 999px;
        background: var(--mfand-soft);
        color: var(--mfand-primary);
        font-size: 10px;
        font-weight: 800;
    }
    .mfand-badge.is-station {
        background: #ecfdf5;
        color: #059669;
    }
    .mfand-fan-meta {
        margin-top: 4px;
        color: #999faa;
        font-size: 12px;
        line-height: 1.5;
    }
    .mfand-contact-btn {
        height: 30px;
        padding: 0 9px;
        border: 0;
        border-radius: 999px;
        background: #ecfdf5;
        color: #059669;
        font-size: 11px;
        font-weight: 800;
        font-family: inherit;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        white-space: nowrap;
        cursor: pointer;
    }
    .mfand-contact-btn:active {
        transform: scale(.96);
        background: #dff8ec;
    }
    .mfand-load-more {
        width: 100%;
        height: 38px;
        margin-top: 10px;
        border: none;
        border-radius: 999px;
        background: #f6f7f9;
        color: #8c93a0;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
    }
    .mfand-load-more[disabled] { opacity: .58; cursor: default; }
    .mfand-app-modal-mask{position:fixed;inset:0;z-index:19999;background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:opacity .25s,visibility .25s}
    .mfand-app-modal-mask.is-show{opacity:1;visibility:visible}
    .mfand-app-modal{width:min(88vw,340px);background:#fff;border-radius:20px;overflow:hidden;transform:translateY(24px) scale(.96);transition:transform .28s cubic-bezier(.22,.61,.36,1);box-shadow:0 20px 50px rgba(0,0,0,.18)}
    .mfand-app-modal-mask.is-show .mfand-app-modal{transform:translateY(0) scale(1)}
    .mfand-app-modal-header{padding:22px 22px 0;text-align:center}
    .mfand-app-modal-icon{width:52px;height:52px;margin:0 auto 12px;border-radius:50%;background:linear-gradient(135deg,var(--theme-primary,#667eea),var(--theme-secondary,#764ba2));color:#fff;display:flex;align-items:center;justify-content:center;font-size:24px}
    .mfand-app-modal-avatar{width:100%;height:100%;border-radius:50%;object-fit:cover;display:block;background:#f0f1f3}
    .mfand-app-modal-body{padding:0 22px 6px;text-align:center}
    .mfand-wechat-name{color:#5f6673;font-size:13px;font-weight:700;line-height:1.5}
    .mfand-wechat-value{margin-top:10px;padding:12px 10px;border-radius:14px;background:#f8f9fb;color:#20242c;font-size:18px;line-height:1.25;font-weight:900;word-break:break-all}
    .mfand-wechat-empty{margin-top:10px;padding:14px 12px;border-radius:14px;background:#f8f9fb;color:#8b95a5;font-size:13px;line-height:1.7;font-weight:700}
    .mfand-app-modal-foot{display:flex;gap:10px;padding:10px 22px 22px}
    .mfand-app-modal-btn{flex:1;height:44px;border:none;border-radius:14px;font-size:15px;font-weight:700;cursor:pointer;transition:.15s}
    .mfand-app-modal-btn:disabled{opacity:.65}
    .mfand-app-modal-cancel{background:#f3f4f6;color:#5f6673}
    .mfand-app-modal-cancel:active{background:#e8ebf0}
    .mfand-app-modal-confirm{background:linear-gradient(135deg,var(--theme-primary,#667eea),var(--theme-secondary,#764ba2));color:#fff;box-shadow:0 6px 16px rgba(var(--mfand-primary-rgb),.22)}
    .mfand-app-modal-confirm:active{transform:scale(.97)}
</style>

<main class="mfand-page">
    <div class="mfand-tabs-wrap">
        <div class="mfand-tabs">
            <?php foreach ($_fansTypeMap as $_typeKey => $_typeName): ?>
            <button type="button" class="mfand-tab<?= $_typeKey === $_fansType ? ' is-active' : '' ?>" data-type="<?= htmlspecialchars($_typeKey) ?>" onclick="setFansType('<?= htmlspecialchars($_typeKey) ?>')"><?= htmlspecialchars($_typeName) ?></button>
            <?php endforeach; ?>
            <div class="mfand-tab-indicator" id="mfandTabIndicator"></div>
        </div>
    </div>

    <section class="mfand-summary">
        <div class="mfand-summary-main">
            <div class="mfand-summary-title">
                <span><i class="ri-user-search-line"></i><em id="mfandSummaryTitle" style="font-style:normal;"><?= htmlspecialchars($_fansTypeMap[$_fansType]) ?></em></span>
                <i class="ri-arrow-down-s-line"></i>
            </div>
            <div class="mfand-summary-count"><span id="mfandTotalNum">0</span><small>人</small></div>
            <div class="mfand-summary-desc" id="mfandSummaryDesc">正在加载粉丝数据，请稍候。</div>
        </div>
    </section>

    <section class="mfand-list-card">
        <div class="mfand-list-head">
            <b><i class="ri-list-check-2"></i><span id="mfandListTitle"><?= htmlspecialchars($_fansTypeMap[$_fansType]) ?></span></b>
            <span id="mfandListCount">共 0 人</span>
        </div>
        <div class="mfand-sort-bar"><b>加入时间</b><span>按最新粉丝优先展示</span></div>
        <div class="mfand-list" id="mfandList"><div class="mfand-loading"><i class="ri-loader-4-line"></i> 加载中...</div></div>
        <button type="button" class="mfand-load-more" id="mfandLoadMore" onclick="loadFans(mfandCurrentType, mfandPage + 1, true)" style="display:none;">加载更多</button>
    </section>
</main>

<template id="dcEmptyIllust"><?php include __DIR__ . '/_svg_empty.php'; ?></template>

<div class="mfand-app-modal-mask" id="mfandWechatModal">
    <div class="mfand-app-modal">
        <div class="mfand-app-modal-header">
            <div class="mfand-app-modal-icon"><img class="mfand-app-modal-avatar" id="mfandWechatAvatar" src="" alt=""></div>
        </div>
        <div class="mfand-app-modal-body">
            <div class="mfand-wechat-card">
                <div class="mfand-wechat-name" id="mfandWechatName">TA</div>
                <div class="mfand-wechat-value" id="mfandWechatValue" style="display:none;"></div>
                <div class="mfand-wechat-empty" id="mfandWechatEmpty" style="display:none;">TA 暂未设置微信号</div>
            </div>
        </div>
        <div class="mfand-app-modal-foot">
            <button type="button" class="mfand-app-modal-btn mfand-app-modal-cancel">知道了</button>
            <button type="button" class="mfand-app-modal-btn mfand-app-modal-confirm" id="mfandCopyWechat">复制微信号</button>
        </div>
    </div>
</div>

<script>
    $('#menu-fans').addClass('menu-current');
    var mfandEmptySvg = (document.getElementById('dcEmptyIllust') || {}).innerHTML || '';
    var mfandTypeTitles = <?= json_encode($_fansTypeMap, JSON_UNESCAPED_UNICODE) ?>;
    var mfandTypeDesc = {
        total: '包含您的直邀粉丝，以及下级继续邀请产生的所有推荐粉丝。',
        direct: '通过您的邀请链接、邀请码或海报二维码直接注册的粉丝。',
        referral: '由您的直邀粉丝继续邀请产生的下级粉丝。',
        active: '已有付款记录的粉丝，可重点维护与转化。',
        potential: '暂未付款的粉丝，可继续引导完成首购。'
    };
    var mfandCurrentType = <?= json_encode($_fansType) ?>;
    var mfandPage = 1;
    var mfandLoading = false;
    var mfandHasMore = false;
    var mfandRequestSeq = 0;
    var mfandCurrentWechat = '';

    function mfandEsc(str) {
        return String(str == null ? '' : str).replace(/[&<>"']/g, function(s) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s];
        });
    }

    function showMfandModal(selector) {
        $(selector).addClass('is-show');
    }

    function hideMfandModal(selector) {
        $(selector).removeClass('is-show');
    }

    function openMfandWechatModal(data) {
        var name = data.name || 'TA';
        var avatar = data.avatar || '';
        var wechat = $.trim(data.wechat || '');
        mfandCurrentWechat = wechat;
        $('#mfandWechatName').text(name);
        $('#mfandWechatAvatar').attr('src', avatar);
        if (wechat) {
            $('#mfandWechatValue').text(wechat).show();
            $('#mfandWechatEmpty').hide();
            $('#mfandCopyWechat').show();
        } else {
            $('#mfandWechatValue').hide().text('');
            $('#mfandWechatEmpty').text(name + ' 暂未设置微信号').show();
            $('#mfandCopyWechat').hide();
        }
        showMfandModal('#mfandWechatModal');
    }

    var mfandIndicatorTimer = null;
    function moveFansTabIndicator($tab, animate) {
        var $indicator = $('#mfandTabIndicator');
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
        if (mfandIndicatorTimer) {
            clearTimeout(mfandIndicatorTimer);
            mfandIndicatorTimer = null;
        }
        $indicator.css({
            left: stretchLeft + 'px',
            width: stretchWidth + 'px',
            transition: 'left 0.16s cubic-bezier(.4,0,.2,1), width 0.16s cubic-bezier(.4,0,.2,1)'
        });
        mfandIndicatorTimer = setTimeout(function() {
            $indicator.css({
                left: targetLeft + 'px',
                width: indicatorW + 'px',
                transition: 'left 0.13s cubic-bezier(.4,0,.2,1), width 0.13s cubic-bezier(.4,0,.2,1)'
            });
        }, 200);
    }

    function setFansType(type) {
        if (!mfandTypeTitles[type]) type = 'total';
        mfandCurrentType = type;
        mfandPage = 1;
        mfandHasMore = false;
        $('.mfand-tab').removeClass('is-active');
        $('.mfand-tab[data-type="' + type + '"]').addClass('is-active');
        moveFansTabIndicator($('.mfand-tab[data-type="' + type + '"]'), true);
        $('#mfandSummaryTitle,#mfandListTitle').text(mfandTypeTitles[type]);
        $('.m-topbar-title').text(mfandTypeTitles[type]);
        $('#mfandSummaryDesc').text(mfandTypeDesc[type] || '查看粉丝明细。');
        if (window.history && history.replaceState) {
            history.replaceState(null, '', './account.php?action=fans_detail&type=' + encodeURIComponent(type));
        }
        loadFans(type, 1, false);
    }

    function renderFan(fan) {
        var name = fan.nickname || ('UID:' + fan.uid);
        var initial = (name || '?').trim().charAt(0) || '?';
        var avatarSrc = fan.photo || '';
        var levelHtml = fan.level_name ? '<span class="mfand-avatar-level">' + mfandEsc(fan.level_name) + '</span>' : '';
        var avatar = fan.photo
            ? '<div class="mfand-avatar"><img src="' + mfandEsc(fan.photo) + '" alt="">' + levelHtml + '</div>'
            : '<div class="mfand-avatar"><span>' + mfandEsc(initial) + '</span>' + levelHtml + '</div>';
        var badges = '';
        if (fan.has_station) badges += '<span class="mfand-badge is-station">' + mfandEsc(fan.station_level_name || '分店') + '</span>';
        var expend = parseFloat(fan.expend || 0);
        if (isNaN(expend)) expend = 0;
        return '<div class="mfand-fan-item">'
            + avatar
            + '<div class="mfand-fan-main">'
            + '<div class="mfand-fan-name"><strong>' + mfandEsc(name) + '</strong><span class="mfand-uid">UID:' + mfandEsc(fan.uid || '--') + '</span>' + badges + '</div>'
            + '<div class="mfand-fan-meta">加入：' + mfandEsc(fan.create_time || '--') + ' · 消费：¥' + expend.toFixed(2) + '</div>'
            + '</div>'
            + '<div class="mfand-fan-action"><button type="button" class="mfand-contact-btn" data-name="' + mfandEsc(name) + '" data-avatar="' + mfandEsc(avatarSrc) + '" data-wechat="' + mfandEsc(fan.wechat || '') + '"><i class="ri-wechat-line"></i> 联系TA</button></div>'
            + '</div>';
    }

    $(document).on('click', '.mfand-contact-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $btn = $(this);
        openMfandWechatModal({
            name: $btn.attr('data-name') || 'TA',
            avatar: $btn.attr('data-avatar') || '',
            wechat: $btn.attr('data-wechat') || ''
        });
    });

    $('.mfand-app-modal-cancel').on('click', function() {
        var $mask = $(this).closest('.mfand-app-modal-mask');
        hideMfandModal('#' + $mask.attr('id'));
    });

    $('.mfand-app-modal-mask').on('click', function(e) {
        if (e.target !== this) return;
        hideMfandModal('#' + this.id);
    });

    $('#mfandCopyWechat').on('click', function() {
        var text = $.trim(mfandCurrentWechat || '');
        if (!text) return layui.layer.msg('TA 暂未设置微信号', {icon: 0});
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

    function loadFans(type, page, append) {
        if (mfandLoading) return;
        var requestSeq = ++mfandRequestSeq;
        mfandLoading = true;
        $('#mfandLoadMore').prop('disabled', true).text('加载中...');
        if (!append) {
            $('#mfandList').html('<div class="mfand-loading"><i class="ri-loader-4-line"></i> 加载中...</div>');
            $('#mfandLoadMore').hide();
            $('#mfandTotalNum').text('0');
            $('#mfandListCount').text('共 0 人');
        }
        $.getJSON('./account.php?action=fans_list&type=' + encodeURIComponent(type) + '&page=' + page, function(res) {
            if (requestSeq !== mfandRequestSeq) return;
            if (res.code !== 0) {
                $('#mfandList').html('<div class="mfand-empty">' + mfandEmptySvg + mfandEsc(res.msg || '加载失败') + '</div>');
                mfandHasMore = false;
                $('#mfandLoadMore').hide();
                return;
            }
            var total = parseInt(res.total || 0, 10) || 0;
            var list = res.list || [];
            $('#mfandTotalNum').text(total);
            $('#mfandListCount').text('共 ' + total + ' 人');
            if (list.length === 0 && page === 1) {
                $('#mfandList').html('<div class="mfand-empty">' + mfandEmptySvg + '暂无粉丝记录</div>');
                mfandHasMore = false;
                $('#mfandLoadMore').hide();
                return;
            }
            var html = '';
            for (var i = 0; i < list.length; i++) html += renderFan(list[i]);
            if (append) $('#mfandList').append(html); else $('#mfandList').html(html);
            mfandPage = page;
            var totalPages = Math.ceil(total / 20);
            mfandHasMore = page < totalPages;
            if (mfandHasMore) {
                $('#mfandLoadMore').show().prop('disabled', false).text('加载更多');
            } else {
                $('#mfandLoadMore').show().prop('disabled', true).text('没有更多了');
            }
        }).fail(function() {
            if (requestSeq !== mfandRequestSeq) return;
            $('#mfandList').html('<div class="mfand-empty">' + mfandEmptySvg + '网络异常，请稍后重试</div>');
            mfandHasMore = false;
            $('#mfandLoadMore').hide();
        }).always(function() {
            if (requestSeq !== mfandRequestSeq) return;
            mfandLoading = false;
            if ($('#mfandLoadMore').is(':visible') && !$('#mfandLoadMore').prop('disabled')) {
                $('#mfandLoadMore').text('加载更多');
            }
        });
    }

    window.addEventListener('scroll', function() {
        if (!mfandHasMore || mfandLoading) return;
        var scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
        var winH = window.innerHeight || document.documentElement.clientHeight || 0;
        var docH = Math.max(document.documentElement.scrollHeight, document.body.scrollHeight);
        if (docH - scrollTop - winH < 180) {
            loadFans(mfandCurrentType, mfandPage + 1, true);
        }
    }, { passive: true });

    $(function() {
        moveFansTabIndicator($('.mfand-tab.is-active'), false);
        setFansType(mfandCurrentType);

        var fanTypeNames = [];
        $('.mfand-tab').each(function() {
            fanTypeNames.push($(this).attr('data-type'));
        });
        var touchStartX = 0, touchStartY = 0, touchMoved = false;
        $('.mfand-page').on('touchstart', function(e) {
            var t = e.originalEvent.touches[0];
            touchStartX = t.clientX;
            touchStartY = t.clientY;
            touchMoved = false;
        });
        $('.mfand-page').on('touchmove', function(e) {
            var t = e.originalEvent.touches[0];
            if (Math.abs(t.clientX - touchStartX) > 20 && Math.abs(t.clientY - touchStartY) < 40) {
                touchMoved = true;
            }
        });
        $('.mfand-page').on('touchend', function(e) {
            if (!touchMoved) return;
            var endX = e.originalEvent.changedTouches[0].clientX;
            var diff = endX - touchStartX;
            if (Math.abs(diff) < 50) return;
            var idx = fanTypeNames.indexOf(mfandCurrentType);
            if (idx < 0) idx = 0;
            if (diff < 0 && idx < fanTypeNames.length - 1) {
                idx++;
            } else if (diff > 0 && idx > 0) {
                idx--;
            } else {
                return;
            }
            setFansType(fanTypeNames[idx]);
        });
    });
</script>
