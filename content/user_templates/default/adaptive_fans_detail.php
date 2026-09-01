<?php
defined('DC_ROOT') || exit('access denied!');
$_fansTypeMap = isset($fansTypeMap) && is_array($fansTypeMap) ? $fansTypeMap : [
    'total' => '总粉丝',
    'direct' => '直邀粉丝',
    'referral' => '推荐粉丝',
    'active' => '有效粉丝',
    'potential' => '潜在粉丝'
];
$_fansType = isset($fansType) ? (string)$fansType : 'total';
if (!isset($_fansTypeMap[$_fansType])) {
    $_fansType = 'total';
}
?>

<style>
    .fans-detail-page {
        display: flex;
        flex-direction: column;
        gap: 22px;
        padding: 8px 0 18px;
    }

    .fans-detail-hero,
    .fans-detail-list-card {
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        border-radius: 10px;
        box-shadow: 0 1px 18px #12345b0a;
    }

    .fans-detail-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 24px 28px;
    }

    .fans-detail-title {
        margin: 0;
        color: #0f172a;
        font-size: 22px;
        line-height: 1.25;
        font-weight: 800;
    }

    .fans-detail-desc {
        margin-top: 6px;
        color: #64748b;
        font-size: 13px;
        line-height: 1.7;
    }

    .fans-detail-count {
        min-width: 148px;
        padding: 14px 20px;
        border-radius: 10px;
        background: linear-gradient(135deg, rgba(var(--tp-rgb), .10), rgba(var(--tp-rgb), .04));
        text-align: center;
    }

    .fans-detail-count-num {
        color: var(--theme-primary);
        font-size: 28px;
        line-height: 1.15;
        font-weight: 800;
    }

    .fans-detail-count-label {
        margin-top: 3px;
        color: #64748b;
        font-size: 12px;
    }

    .fans-detail-tabs {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 18px 20px;
        border-radius: 10px;
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        box-shadow: 0 1px 18px #12345b0a;
    }

    .fans-detail-tab {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 94px;
        height: 36px;
        padding: 0 18px;
        border: 1px solid #e5e7eb;
        border-radius: 999px;
        background: #fff;
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: .18s;
    }

    .fans-detail-tab:hover {
        color: var(--theme-primary);
        border-color: rgba(var(--tp-rgb), .28);
        background: rgba(var(--tp-rgb), .05);
        text-decoration: none;
    }

    .fans-detail-tab.is-active {
        color: #fff;
        border-color: var(--theme-primary);
        background: var(--theme-primary);
        box-shadow: 0 8px 20px rgba(var(--tp-rgb), .18);
    }

    .fans-detail-list-card {
        overflow: hidden;
    }

    .fans-detail-list-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 22px;
        border-bottom: 1px solid #edf2f7;
    }

    .fans-detail-list-title {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #0f172a;
        font-size: 16px;
        font-weight: 800;
    }

    .fans-detail-list-title i {
        color: var(--theme-primary);
    }

    .fans-detail-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #94a3b8;
        font-size: 13px;
    }

    .fans-detail-refresh {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        height: 32px;
        padding: 0 13px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
        color: #64748b;
        font-size: 12px;
        cursor: pointer;
        transition: .18s;
    }

    .fans-detail-refresh:hover {
        color: var(--theme-primary);
        border-color: rgba(var(--tp-rgb), .28);
        background: rgba(var(--tp-rgb), .05);
    }

    .fans-detail-table {
        width: 100%;
        border-collapse: collapse;
    }

    .fans-detail-table th {
        height: 46px;
        padding: 0 20px;
        background: #f8fafc;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        text-align: left;
        white-space: nowrap;
    }

    .fans-detail-table td {
        padding: 14px 20px;
        border-top: 1px solid #f1f5f9;
        color: #334155;
        font-size: 13px;
        vertical-align: middle;
    }

    .fans-detail-user {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .fans-detail-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(var(--tp-rgb), .10);
        color: var(--theme-primary);
        font-size: 15px;
        font-weight: 800;
    }

    .fans-detail-avatar img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .fans-detail-name {
        display: flex;
        align-items: center;
        gap: 6px;
        min-width: 0;
    }

    .fans-detail-name strong {
        max-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #111827;
        font-size: 14px;
        font-weight: 700;
    }

    .fans-detail-uid {
        margin-top: 3px;
        color: #94a3b8;
        font-size: 12px;
    }

    .fans-detail-badge {
        display: inline-flex;
        align-items: center;
        height: 20px;
        padding: 0 8px;
        border-radius: 999px;
        background: rgba(var(--tp-rgb), .10);
        color: var(--theme-primary);
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    .fans-detail-badge.is-station {
        background: rgba(5,150,105,.10);
        color: #059669;
    }

    .fans-detail-money {
        color: #ef4444;
        font-weight: 700;
    }

    .fans-detail-wechat {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    .fans-detail-wechat-value {
        max-width: 138px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #07a452;
        font-weight: 700;
    }

    .fans-detail-wechat-empty {
        color: #cbd5e1;
    }

    .fans-detail-wechat-copy {
        height: 26px;
        padding: 0 9px;
        border: 1px solid rgba(7,193,96,.22);
        border-radius: 999px;
        background: rgba(7,193,96,.06);
        color: #07a452;
        font-size: 12px;
        line-height: 24px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: .18s;
        white-space: nowrap;
    }

    .fans-detail-wechat-copy:hover {
        background: #07c160;
        border-color: #07c160;
        color: #fff;
    }

    .fans-detail-empty,
    .fans-detail-loading {
        padding: 70px 20px;
        color: #94a3b8;
        font-size: 14px;
        text-align: center;
    }

    .fans-detail-empty svg {
        display: block;
        width: 150px;
        height: auto;
        margin: 0 auto 10px;
    }

    .fans-detail-loading i {
        margin-right: 6px;
    }

    .fans-detail-pager {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 18px 20px;
        border-top: 1px solid #edf2f7;
    }

    .fans-detail-page-btn {
        height: 32px;
        padding: 0 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
        color: #64748b;
        font-size: 12px;
        cursor: pointer;
    }

    .fans-detail-page-btn:hover:not([disabled]) {
        color: var(--theme-primary);
        border-color: rgba(var(--tp-rgb), .28);
        background: rgba(var(--tp-rgb), .05);
    }

    .fans-detail-page-btn[disabled] {
        opacity: .45;
        cursor: not-allowed;
    }

    .fans-detail-page-info {
        color: #64748b;
        font-size: 13px;
    }
</style>

<main class="fans-detail-page">
    <section class="fans-detail-hero">
        <div>
            <h1 class="fans-detail-title" id="fansDetailHeroTitle"><?= htmlspecialchars($_fansTypeMap[$_fansType]) ?></h1>
            <div class="fans-detail-desc" id="fansDetailHeroDesc">查看粉丝明细，按加入时间倒序展示，可切换不同粉丝类型。</div>
        </div>
        <div class="fans-detail-count">
            <div class="fans-detail-count-num" id="fansDetailTotalNum">0</div>
            <div class="fans-detail-count-label">当前类型人数</div>
        </div>
    </section>

    <nav class="fans-detail-tabs">
        <?php foreach ($_fansTypeMap as $_typeKey => $_typeName): ?>
        <a class="fans-detail-tab<?= $_typeKey === $_fansType ? ' is-active' : '' ?>" href="./account.php?action=fans_detail&type=<?= htmlspecialchars($_typeKey) ?>" data-type="<?= htmlspecialchars($_typeKey) ?>"><?= htmlspecialchars($_typeName) ?></a>
        <?php endforeach; ?>
    </nav>

    <section class="fans-detail-list-card">
        <div class="fans-detail-list-head">
            <div class="fans-detail-list-title"><i class="fa fa-users"></i><span id="fansDetailListTitle"><?= htmlspecialchars($_fansTypeMap[$_fansType]) ?></span></div>
            <div class="fans-detail-actions">
                <span id="fansDetailListCount">共 0 人</span>
                <button class="fans-detail-refresh" type="button" id="fansDetailRefreshBtn"><i class="fa fa-refresh"></i> 刷新</button>
            </div>
        </div>
        <div id="fansDetailListWrap">
            <div class="fans-detail-loading"><i class="fa fa-spinner fa-spin"></i> 加载中...</div>
        </div>
        <div class="fans-detail-pager" id="fansDetailPager" style="display:none;"></div>
    </section>
</main>

<template id="dcEmptyIllust"><?php include __DIR__ . '/_svg_empty.php'; ?></template>

<script>
    $('#menu-fans').addClass('menu-current');
    var fansDetailTypeTitles = <?= json_encode($_fansTypeMap, JSON_UNESCAPED_UNICODE) ?>;
    var fansDetailTypeDesc = {
        total: '包含您的直邀粉丝，以及下级继续邀请产生的所有推荐粉丝。',
        direct: '通过您的邀请链接、邀请码或海报二维码直接注册的粉丝。',
        referral: '由您的直邀粉丝继续邀请产生的下级粉丝。',
        active: '已有付款记录的粉丝，可重点维护与转化。',
        potential: '暂未付款的粉丝，可继续通过活动、优惠或提醒引导完成首购。'
    };
    var fansDetailCurrentType = <?= json_encode($_fansType) ?>;
    var fansDetailPage = 1;
    var fansDetailLoading = false;
    var fansDetailEmptySvg = (document.getElementById('dcEmptyIllust') || {}).innerHTML || '';

    function fansDetailEsc(str) {
        return String(str == null ? '' : str).replace(/[&<>"']/g, function(s) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s];
        });
    }

    function setFansDetailType(type, page, pushState) {
        if (!fansDetailTypeTitles[type]) type = 'total';
        fansDetailCurrentType = type;
        fansDetailPage = page || 1;
        $('.fans-detail-tab').removeClass('is-active');
        $('.fans-detail-tab[data-type="' + type + '"]').addClass('is-active');
        $('#fansDetailHeroTitle,#fansDetailListTitle').text(fansDetailTypeTitles[type]);
        $('#fansDetailHeroDesc').text(fansDetailTypeDesc[type] || '查看粉丝明细。');
        if (pushState && window.history && history.replaceState) {
            history.replaceState(null, '', './account.php?action=fans_detail&type=' + encodeURIComponent(type));
        }
        loadFansDetail(type, fansDetailPage);
    }

    function renderFansDetailRows(list) {
        var html = '<table class="fans-detail-table">'
            + '<thead><tr>'
            + '<th>粉丝信息</th>'
            + '<th>会员/分店</th>'
            + '<th>累计消费</th>'
            + '<th>微信号</th>'
            + '<th>加入时间</th>'
            + '</tr></thead><tbody>';
        for (var i = 0; i < list.length; i++) {
            var fan = list[i] || {};
            var name = fan.nickname || ('UID:' + fan.uid);
            var initial = (name || '?').trim().charAt(0) || '?';
            var avatar = fan.photo
                ? '<div class="fans-detail-avatar"><img src="' + fansDetailEsc(fan.photo) + '" alt=""></div>'
                : '<div class="fans-detail-avatar"><span>' + fansDetailEsc(initial) + '</span></div>';
            var badges = '';
            if (fan.level_name) badges += '<span class="fans-detail-badge">' + fansDetailEsc(fan.level_name) + '</span>';
            if (fan.has_station) badges += '<span class="fans-detail-badge is-station"><i class="fa fa-home" style="margin-right:3px;"></i>' + fansDetailEsc(fan.station_level_name || '分店') + '</span>';
            if (!badges) badges = '<span style="color:#cbd5e1;">--</span>';
            var expend = parseFloat(fan.expend || 0);
            if (isNaN(expend)) expend = 0;
            var wechat = $.trim(String(fan.wechat || ''));
            var wechatHtml = wechat
                ? '<div class="fans-detail-wechat"><span class="fans-detail-wechat-value" title="' + fansDetailEsc(wechat) + '">' + fansDetailEsc(wechat) + '</span><button type="button" class="fans-detail-wechat-copy" data-wechat="' + fansDetailEsc(wechat) + '"><i class="fa fa-copy"></i> 复制</button></div>'
                : '<span class="fans-detail-wechat-empty">未设置</span>';
            html += '<tr>'
                + '<td><div class="fans-detail-user">' + avatar + '<div><div class="fans-detail-name"><strong>' + fansDetailEsc(name) + '</strong></div><div class="fans-detail-uid">UID: ' + fansDetailEsc(fan.uid || '--') + '</div></div></div></td>'
                + '<td>' + badges + '</td>'
                + '<td><span class="fans-detail-money">¥' + expend.toFixed(2) + '</span></td>'
                + '<td>' + wechatHtml + '</td>'
                + '<td>' + fansDetailEsc(fan.create_time || '--') + '</td>'
                + '</tr>';
        }
        html += '</tbody></table>';
        return html;
    }

    function renderFansDetailPager(total, page) {
        var totalPages = Math.ceil((parseInt(total, 10) || 0) / 20);
        if (totalPages <= 1) {
            $('#fansDetailPager').hide().empty();
            return;
        }
        var html = '<button class="fans-detail-page-btn" type="button" ' + (page <= 1 ? 'disabled' : '') + ' data-page="' + (page - 1) + '">上一页</button>'
            + '<span class="fans-detail-page-info">第 ' + page + ' / ' + totalPages + ' 页</span>'
            + '<button class="fans-detail-page-btn" type="button" ' + (page >= totalPages ? 'disabled' : '') + ' data-page="' + (page + 1) + '">下一页</button>';
        $('#fansDetailPager').html(html).show();
    }

    function loadFansDetail(type, page) {
        if (fansDetailLoading) return;
        fansDetailLoading = true;
        $('#fansDetailListWrap').html('<div class="fans-detail-loading"><i class="fa fa-spinner fa-spin"></i> 加载中...</div>');
        $('#fansDetailPager').hide();
        $.getJSON('./account.php?action=fans_list&type=' + encodeURIComponent(type) + '&page=' + page, function(res) {
            if (res.code !== 0) {
                $('#fansDetailListWrap').html('<div class="fans-detail-empty">' + fansDetailEmptySvg + fansDetailEsc(res.msg || '加载失败') + '</div>');
                $('#fansDetailTotalNum').text('0');
                $('#fansDetailListCount').text('共 0 人');
                return;
            }
            var total = parseInt(res.total || 0, 10) || 0;
            var list = res.list || [];
            $('#fansDetailTotalNum').text(total);
            $('#fansDetailListCount').text('共 ' + total + ' 人');
            if (!list.length) {
                $('#fansDetailListWrap').html('<div class="fans-detail-empty">' + fansDetailEmptySvg + '暂无粉丝记录</div>');
                $('#fansDetailPager').hide().empty();
                return;
            }
            $('#fansDetailListWrap').html(renderFansDetailRows(list));
            fansDetailPage = page;
            renderFansDetailPager(total, page);
        }).fail(function() {
            $('#fansDetailListWrap').html('<div class="fans-detail-empty">' + fansDetailEmptySvg + '网络异常，请稍后重试</div>');
        }).always(function() {
            fansDetailLoading = false;
        });
    }

    $('.fans-detail-tab').on('click', function(e) {
        e.preventDefault();
        setFansDetailType($(this).data('type'), 1, true);
    });

    $('#fansDetailRefreshBtn').on('click', function() {
        loadFansDetail(fansDetailCurrentType, fansDetailPage);
    });

    $('#fansDetailPager').on('click', '.fans-detail-page-btn:not([disabled])', function() {
        var page = parseInt($(this).data('page'), 10) || 1;
        loadFansDetail(fansDetailCurrentType, page);
    });

    $('#fansDetailListWrap').on('click', '.fans-detail-wechat-copy', function() {
        var text = $(this).data('wechat') || '';
        if (!text) {
            layui.layer.msg('暂无可复制的微信号', {icon: 0});
            return;
        }
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function(){ layui.layer.msg('已复制微信号', {icon: 1}); });
        } else {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            layui.layer.msg('已复制微信号', {icon: 1});
        }
    });

    $(function() {
        setFansDetailType(fansDetailCurrentType, 1, false);
    });
</script>

<?php include __DIR__ . '/_pc_page_footer.php'; ?>