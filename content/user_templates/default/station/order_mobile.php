<?php
defined('DC_ROOT') || exit('access denied!');
?>
<style>
    .uc-site-footer{display:none!important}
    .station-order-app,.station-order-app *{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
    .station-order-app{--som-primary:var(--theme-primary,#667eea);--som-primary-rgb:var(--tp-rgb,102,126,234);--som-soft:rgba(var(--som-primary-rgb),.10);min-height:100vh;padding:12px 12px calc(28px + env(safe-area-inset-bottom,0px));background:#f5f6f8;color:#20242c;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif}
    .som-filter-wrap{position:sticky;top:calc(50px + env(safe-area-inset-top,0px));z-index:10;margin:-10px -12px 12px;padding:10px 12px 8px;background:rgba(245,245,246,.96);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px)}
    .som-filter-card{padding:12px;border-radius:10px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;box-shadow:var(--shadow-primary)}
    .som-search-main{display:grid;grid-template-columns:92px minmax(0,1fr) 74px;gap:8px}.som-input,.som-type-btn{width:100%;height:40px;padding:0 12px;border:1px solid #edf0f5;border-radius:13px;background:#f8f9fb;color:#20242c;font-size:13px!important;outline:none}.som-input{-webkit-appearance:none;appearance:none}.som-input::placeholder{color:#9aa3af;font-size:13px}.som-input:focus,.som-type.is-open .som-type-btn{border-color:rgba(var(--som-primary-rgb),.35);box-shadow:0 0 0 3px rgba(var(--som-primary-rgb),.08);background-color:#fff}.som-type{position:relative;z-index:12}.som-type-btn{border:1px solid #edf0f5;display:flex;align-items:center;justify-content:space-between;gap:4px;font-weight:900;white-space:nowrap;cursor:pointer}.som-type-btn i{color:#9aa3af;font-size:12px;transition:transform .18s ease}.som-type.is-open .som-type-btn i{transform:rotate(180deg);color:var(--som-primary)}.som-type-menu{position:absolute;left:0;right:0;top:calc(100% + 6px);z-index:30;padding:5px;border:1px solid #edf0f5;border-radius:14px;background:#fff;box-shadow:0 12px 28px rgba(15,23,42,.12);opacity:0;visibility:hidden;transform:translateY(-4px);transition:opacity .16s ease,visibility .16s ease,transform .16s ease}.som-type.is-open .som-type-menu{opacity:1;visibility:visible;transform:translateY(0)}.som-type-option{width:100%;height:34px;border:0;border-radius:10px;background:transparent;color:#697180;font-size:12px;font-weight:900;display:flex;align-items:center;justify-content:center}.som-type-option.is-active{background:var(--som-soft);color:var(--som-primary)}
    .som-search-btn{height:40px;border:0;border-radius:13px;font-size:13px;font-weight:900;display:flex;align-items:center;justify-content:center;gap:5px;white-space:nowrap;background:linear-gradient(135deg,var(--theme-primary,#667eea),var(--theme-secondary,#764ba2));color:#fff;box-shadow:0 8px 18px rgba(var(--som-primary-rgb),.16)}
    .som-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:7px;margin-bottom:12px}.som-stat{min-width:0;padding:10px 6px;border-radius:10px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;box-shadow:var(--shadow-primary);text-align:center}.som-stat-value{display:block;color:#20242c;font-size:15px;font-weight:900;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.som-stat-value.is-money{font-size:13px;color:#c88332}.som-stat-value.is-profit{font-size:13px;color:#059669}.som-stat-label{display:block;margin-top:4px;color:#8b95a5;font-size:10px;font-weight:800;white-space:nowrap}
    .som-list{display:grid;grid-template-columns:1fr;gap:10px;touch-action:auto}.som-empty{padding:60px 16px;border-radius:10px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;text-align:center;color:#bbb;font-size:13px;line-height:1.7;box-shadow:var(--shadow-primary)}.som-empty svg{display:block;width:140px;height:auto;margin:0 auto 10px}.som-card{position:relative;overflow:hidden;border-radius:10px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;box-shadow:var(--shadow-primary)}.som-card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;padding:13px 12px 10px}.som-title-wrap{min-width:0;flex:1}.som-title{margin:0;color:#20242c;font-size:14px;font-weight:900;line-height:1.38;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}.som-sub{margin-top:5px;color:#8b95a5;font-size:11px;line-height:1.45;word-break:break-all}.som-status{flex:0 0 auto;display:inline-flex;align-items:center;justify-content:center;min-width:58px;height:25px;padding:0 8px;border-radius:999px;font-size:11px;font-weight:900}.som-status.is-success{background:#ecfdf5;color:#059669}.som-status.is-warning{background:#fffbeb;color:#d97706}.som-status.is-info{background:var(--som-soft);color:var(--som-primary)}.som-status.is-danger{background:#fff1f2;color:#e11d48}.som-status.is-neutral{background:#f3f4f6;color:#6b7280}
    .som-meta-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:7px;padding:0 12px 10px}.som-meta{min-width:0;padding:9px 6px;border-radius:13px;background:#f8f9fb;text-align:center}.som-meta-label{display:block;color:#8b95a5;font-size:10px;font-weight:800;line-height:1.1}.som-meta-value{display:block;margin-top:5px;color:#20242c;font-size:12px;font-weight:900;line-height:1.15;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.som-meta-value.is-amount{color:#d9485f}.som-meta-value.is-profit{color:#059669}.som-meta-value.is-empty{color:#9ca3af}
    .som-info{padding:0 12px 12px}.som-info-row{display:flex;align-items:flex-start;gap:9px;padding:9px 0;border-top:1px solid #f2f4f7;color:#697180;font-size:12px;line-height:1.55}.som-info-row i{width:22px;height:22px;border-radius:9px;background:var(--som-soft);color:var(--som-primary);display:flex;align-items:center;justify-content:center;flex:0 0 auto}.som-info-row span{flex:1;min-width:0;word-break:break-all}.som-info-row b{color:#303846;font-weight:900}.som-extra{display:inline-flex;margin-left:6px;height:20px;padding:0 7px;border-radius:999px;background:var(--som-soft);color:var(--som-primary);font-size:10px;font-weight:900;vertical-align:middle}
    .som-pager{display:none;margin-top:12px;padding-bottom:8px}.som-pager.is-visible{display:block}.som-pager-row{display:grid;grid-template-columns:72px minmax(0,1fr) 72px;gap:8px;align-items:center}.som-page-btn{height:32px;border:0;border-radius:999px;background:#fff;color:var(--som-primary);font-size:12px;font-weight:900;display:flex;align-items:center;justify-content:center;gap:4px;box-shadow:0 4px 14px rgba(31,52,88,.06)}.som-page-btn:disabled{background:#f8f9fb;color:#c0c7d2;box-shadow:none}.som-page-current{height:32px;border-radius:999px;background:#fff;color:#20242c;font-size:12px;font-weight:900;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(31,52,88,.06)}.som-page-numbers{display:flex;justify-content:center;gap:5px;flex-wrap:wrap;margin-top:8px}.som-page-num,.som-page-ellipsis{min-width:26px;height:26px;padding:0 8px;border:0;border-radius:999px;background:transparent;color:#8b95a5;font-size:11px;font-weight:900;display:inline-flex;align-items:center;justify-content:center}.som-page-num.is-active{background:var(--som-soft);color:var(--som-primary)}
    @media(max-width:360px){.station-order-app{padding-left:10px;padding-right:10px}.som-search-main{grid-template-columns:86px minmax(0,1fr) 64px;gap:6px}.som-type-btn{padding-left:8px;padding-right:8px}.som-search-btn{font-size:12px}.som-stats{gap:6px}.som-stat-value{font-size:14px}.som-stat-value.is-money,.som-stat-value.is-profit{font-size:12px}.som-meta-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.som-card{position:relative;overflow:hidden;border-radius:10px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;box-shadow:var(--shadow-primary)}}
</style>

<main class="station-order-app">
    <div class="som-filter-wrap">
        <section class="som-filter-card">
            <form id="somSearch">
                <div class="som-search-main">
                    <div class="som-type" id="somSearchType">
                        <input type="hidden" name="search_type" value="out_trade_no">
                        <button type="button" class="som-type-btn" id="somTypeBtn" aria-expanded="false"><span id="somTypeLabel">订单号</span><i class="fa fa-angle-down"></i></button>
                        <div class="som-type-menu" id="somTypeMenu">
                            <button type="button" class="som-type-option is-active" data-type="out_trade_no" data-label="订单号" data-placeholder="输入订单号">订单号</button>
                            <button type="button" class="som-type-option" data-type="goods_title" data-label="商品名" data-placeholder="输入商品名称">商品名</button>
                            <button type="button" class="som-type-option" data-type="email_username" data-label="用户" data-placeholder="输入用户昵称">用户</button>
                        </div>
                    </div>
                    <input type="search" name="keyword" class="som-input" placeholder="输入订单号">
                    <button type="submit" class="som-search-btn"><i class="fa fa-search"></i> 搜索</button>
                </div>
            </form>
        </section>
    </div>

    <section class="som-stats">
        <div class="som-stat"><span class="som-stat-value" id="somTotal">-</span><span class="som-stat-label">总订单</span></div>
        <div class="som-stat"><span class="som-stat-value is-money" id="somAmount">-</span><span class="som-stat-label">总金额</span></div>
        <div class="som-stat"><span class="som-stat-value" id="somPaid">-</span><span class="som-stat-label">已支付</span></div>
        <div class="som-stat"><span class="som-stat-value is-profit" id="somComm">-</span><span class="som-stat-label">佣金</span></div>
    </section>

    <section class="som-list" id="somOrderList"></section>
    <nav class="som-pager" id="somPager">
        <div class="som-pager-row">
            <button type="button" class="som-page-btn" id="somPrevPage"><i class="fa fa-angle-left"></i> 上一页</button>
            <div class="som-page-current" id="somPageCurrent">1 / 1</div>
            <button type="button" class="som-page-btn" id="somNextPage">下一页 <i class="fa fa-angle-right"></i></button>
        </div>
    </nav>
</main>

<template id="somEmptyIllust"><?php include __DIR__ . '/../_svg_empty.php'; ?></template>

<script>
layui.use(['layer'], function(){
    var layer = layui.layer;
    var emptySvg = (document.getElementById('somEmptyIllust') || {}).innerHTML || '';
    var state = { page: 1, limit: 10, total: 0, loading: false, filters: { out_trade_no: '', goods_title: '', email_username: '' } };
    var placeholderMap = {
        out_trade_no: '输入订单号',
        goods_title: '输入商品名称',
        email_username: '输入用户昵称'
    };

    function safeText(value) {
        return $('<div>').text(value == null || value === '' ? '' : String(value)).html();
    }

    function parseNumber(value) {
        var num = parseFloat(String(value || '0').replace(/,/g, ''));
        return isNaN(num) ? 0 : num;
    }

    function getFirstGoods(item) {
        if (!item || !item.list || !item.list.length) return { title: '未知商品', attr_spec: '', quantity: 0 };
        return item.list[0] || { title: '未知商品', attr_spec: '', quantity: 0 };
    }

    function statusClass(text) {
        text = text || '';
        if (text.indexOf('取消') !== -1 || text.indexOf('退款') !== -1 || text.indexOf('失败') !== -1) return 'is-danger';
        if (text.indexOf('未支付') !== -1 || text.indexOf('待支付') !== -1) return 'is-warning';
        if (text.indexOf('待发货') !== -1 || text.indexOf('部分') !== -1 || text.indexOf('处理中') !== -1) return 'is-info';
        if (text.indexOf('完成') !== -1 || text.indexOf('已支付') !== -1 || text.indexOf('已发货') !== -1) return 'is-success';
        return 'is-neutral';
    }

    function readFilters() {
        var type = $('#somSearch [name="search_type"]').val() || 'out_trade_no';
        var keyword = $.trim($('#somSearch [name="keyword"]').val() || '');
        state.filters.out_trade_no = '';
        state.filters.goods_title = '';
        state.filters.email_username = '';
        if (state.filters[type] !== undefined) state.filters[type] = keyword;
    }

    function syncSearchPlaceholder() {
        var type = $('#somSearch [name="search_type"]').val() || 'out_trade_no';
        var $option = $('#somTypeMenu .som-type-option[data-type="' + type + '"]');
        $('#somSearch [name="keyword"]').attr('placeholder', $option.data('placeholder') || placeholderMap[type] || '输入关键词');
        $('#somTypeLabel').text($option.data('label') || '订单号');
        $('#somTypeMenu .som-type-option').removeClass('is-active');
        $option.addClass('is-active');
    }

    function closeSearchType() {
        $('#somSearchType').removeClass('is-open');
        $('#somTypeBtn').attr('aria-expanded', 'false');
    }

    function setSearchType(type, label, placeholder) {
        if (!state.filters.hasOwnProperty(type)) type = 'out_trade_no';
        $('#somSearch [name="search_type"]').val(type);
        $('#somTypeLabel').text(label || '订单号');
        $('#somSearch [name="keyword"]').attr('placeholder', placeholder || placeholderMap[type] || '输入关键词');
        $('#somTypeMenu .som-type-option').removeClass('is-active');
        $('#somTypeMenu .som-type-option[data-type="' + type + '"]').addClass('is-active');
        closeSearchType();
    }

    function updateStats(res) {
        if (!res) return;
        $('#somTotal').text(res.stat_total || 0);
        $('#somAmount').text(res.stat_amount ? '¥' + res.stat_amount : '-');
        $('#somPaid').text(res.stat_paid || 0);
        var comm = parseNumber(res.stat_commission || '0');
        $('#somComm').text(comm > 0 ? '¥' + res.stat_commission : '-');
    }

    function renderPageNumbers(totalPages) {
        var $nums = $('#somPageNumbers');
        if (!state.total || totalPages <= 1) { $nums.html(''); return; }
        var pages = [1];
        var start = Math.max(2, state.page - 1);
        var end = Math.min(totalPages - 1, state.page + 1);
        if (start > 2) pages.push('...');
        for (var i = start; i <= end; i++) pages.push(i);
        if (end < totalPages - 1) pages.push('...');
        if (totalPages > 1) pages.push(totalPages);
        $nums.html(pages.map(function(p){
            if (p === '...') return '<span class="som-page-ellipsis">...</span>';
            return '<button type="button" class="som-page-num' + (p === state.page ? ' is-active' : '') + '" data-page="' + p + '">' + p + '</button>';
        }).join(''));
    }

    function updatePager() {
        var totalPages = Math.max(1, Math.ceil((parseInt(state.total, 10) || 0) / state.limit));
        if (state.page > totalPages) state.page = totalPages;
        $('#somPager').toggleClass('is-visible', state.total > state.limit);
        $('#somPrevPage').prop('disabled', state.loading || state.page <= 1);
        $('#somNextPage').prop('disabled', state.loading || state.page >= totalPages);
        $('#somPageCurrent').text(state.page + ' / ' + totalPages);
        renderPageNumbers(totalPages);
    }

    function renderOrders(list) {
        var $list = $('#somOrderList');
        if (!list || !list.length) {
            $list.html('<div class="som-empty">' + emptySvg + '暂无相关订单</div>');
            return;
        }
        var html = list.map(function(item){
            var goods = getFirstGoods(item);
            var statusText = item.status_text || '处理中';
            var commission = parseNumber(item.commission || '0');
            var extraCount = item.list && item.list.length > 1 ? item.list.length - 1 : 0;
            var userName = item.user_nickname || '未命名用户';
            if (String(item.user_id) === '0') userName += ' / 游客';
            var contact = [];
            if (item.user_email) contact.push(item.user_email);
            if (item.user_tel) contact.push(item.user_tel);
            return '<article class="som-card">' +
                '<div class="som-card-head">' +
                    '<div class="som-title-wrap">' +
                        '<h3 class="som-title">' + safeText(goods.title || '未知商品') + (extraCount > 0 ? '<span class="som-extra">另含 ' + extraCount + ' 项</span>' : '') + '</h3>' +
                        '<div class="som-sub">订单号：' + safeText(item.out_trade_no || '') + '</div>' +
                        '<div class="som-sub">规格：' + safeText(goods.attr_spec || '默认规格') + '</div>' +
                    '</div>' +
                    '<span class="som-status ' + statusClass(statusText) + '">' + safeText(statusText) + '</span>' +
                '</div>' +
                '<div class="som-meta-grid">' +
                    '<div class="som-meta"><span class="som-meta-label">订单金额</span><span class="som-meta-value is-amount">¥' + safeText(item.amount || '0.00') + '</span></div>' +
                    '<div class="som-meta"><span class="som-meta-label">分佣</span><span class="som-meta-value ' + (commission > 0 ? 'is-profit' : 'is-empty') + '">' + (commission > 0 ? '+¥' + safeText(item.commission) : '-') + '</span></div>' +
                    '<div class="som-meta"><span class="som-meta-label">数量</span><span class="som-meta-value">' + safeText(goods.quantity || 0) + '</span></div>' +
                    '<div class="som-meta"><span class="som-meta-label">支付</span><span class="som-meta-value">' + safeText(item.payment || '未支付') + '</span></div>' +
                '</div>' +
                '<div class="som-info">' +
                    '<div class="som-info-row"><i class="fa fa-user-o"></i><span>购买用户：<b>' + safeText(userName) + '</b>' + (contact.length ? '<br>' + safeText(contact.join(' / ')) : '') + '</span></div>' +
                    '<div class="som-info-row"><i class="fa fa-clock-o"></i><span>支付时间：<b>' + safeText(item.pay_time || '暂未支付') + '</b></span></div>' +
                '</div>' +
            '</article>';
        }).join('');
        $list.html(html);
    }

    function loadOrders(reset) {
        if (state.loading) return;
        readFilters();
        if (reset) {
            state.page = 1;
            state.total = 0;
            $('#somOrderList').html('<div class="som-empty">正在加载订单...</div>');
            updatePager();
        }
        state.loading = true;
        updatePager();
        $.ajax({
            url: '?action=order_index',
            type: 'GET',
            dataType: 'json',
            data: {
                page: state.page,
                limit: state.limit,
                out_trade_no: state.filters.out_trade_no,
                goods_title: state.filters.goods_title,
                email_username: state.filters.email_username
            },
            success: function(res) {
                if (!res || (res.code && res.code != 0)) {
                    $('#somOrderList').html('<div class="som-empty">' + safeText(res && res.msg ? res.msg : '订单加载失败') + '</div>');
                    return;
                }
                var data = res.data || [];
                state.total = parseInt(res.count || res.total || data.length || 0, 10) || 0;
                updateStats(res);
                renderOrders(data);
            },
            error: function(err) {
                $('#somOrderList').html('<div class="som-empty">' + safeText(err.responseJSON && err.responseJSON.msg ? err.responseJSON.msg : '请求失败，请重试') + '</div>');
            },
            complete: function() {
                state.loading = false;
                updatePager();
            }
        });
    }

    window.table = { reload: function(){ loadOrders(false); } };

    $('#somSearch').on('submit', function(e){ e.preventDefault(); loadOrders(true); return false; });
    $('#somTypeBtn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $type = $('#somSearchType');
        var willOpen = !$type.hasClass('is-open');
        $type.toggleClass('is-open', willOpen);
        $(this).attr('aria-expanded', willOpen ? 'true' : 'false');
    });
    $('#somTypeMenu').on('click', '.som-type-option', function(e) {
        e.preventDefault();
        e.stopPropagation();
        setSearchType($(this).data('type'), $(this).data('label'), $(this).data('placeholder'));
    });
    $(document).on('click', function(e) {
        if ($(e.target).closest('#somSearchType').length) return;
        closeSearchType();
    });
    $('#somPrevPage').on('click', function(){ if (state.page > 1 && !state.loading) { state.page -= 1; loadOrders(false); } });
    $('#somNextPage').on('click', function(){ var totalPages = Math.max(1, Math.ceil((parseInt(state.total, 10) || 0) / state.limit)); if (state.page < totalPages && !state.loading) { state.page += 1; loadOrders(false); } });
    $('#somPageNumbers').on('click', '.som-page-num', function(){ var page = parseInt($(this).data('page'), 10); if (page > 0 && page !== state.page && !state.loading) { state.page = page; loadOrders(false); } });

    syncSearchPlaceholder();
    loadOrders(true);
});
</script>

<script>
    $('#menu-station').addClass('open');
    $('#menu-station > ul').css('display', 'block');
    $('#menu-station > a > i.nav_right').attr('class', 'fa fa-angle-down nav_right');
    $('#menu-station-order').addClass('menu-current');
</script>
