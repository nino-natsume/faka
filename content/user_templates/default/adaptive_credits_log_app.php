<?php
defined('DC_ROOT') || exit('access denied!');
$_userCredits = isset($user['credits']) ? (int)$user['credits'] : 0;
$virtualCurrencyName = getVirtualCurrencyName();
$virtualCurrencyNameEsc = htmlspecialchars($virtualCurrencyName, ENT_QUOTES, 'UTF-8');
$virtualCurrencyNameJs = json_encode($virtualCurrencyName, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<style>
    * { box-sizing: border-box; }
    .uc-site-footer { display: none !important; }
    /* 替换topbar右侧占位为筛选按钮 */
    .m-topbar-placeholder { visibility: hidden; }
    .cl-topbar-filter {
        position: fixed;
        top: 0;
        right: 6px;
        z-index: 201;
        width: 40px;
        height: calc(50px + env(safe-area-inset-top, 0px));
        padding-top: env(safe-area-inset-top, 0px);
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: 0;
        color: var(--text-main, #1f2937);
        font-size: 17px;
    }
    .cl-topbar-filter .cl-badge {
        position: absolute;
        top: calc(6px + env(safe-area-inset-top, 0px));
        right: 4px;
        min-width: 16px;
        height: 16px;
        border-radius: 8px;
        background: #ef4444;
        color: #fff;
        font-size: 10px;
        font-weight: 600;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
        line-height: 1;
    }
    .cl-topbar-filter .cl-badge.is-show { display: flex; }
    /* ===== 筛选面板 ===== */
    .cl-filter-overlay {
        position: fixed;
        inset: 0;
        z-index: 199;
        background: rgba(0,0,0,0.35);
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.25s, visibility 0.25s;
    }
    .cl-filter-overlay.is-open { opacity: 1; visibility: visible; }
    .cl-filter-clip {
        position: fixed;
        top: calc(50px + env(safe-area-inset-top, 0px));
        left: 0;
        right: 0;
        z-index: 200;
        overflow: hidden;
        pointer-events: none;
        height: 0;
        transition: height 0s 0.35s;
    }
    .cl-filter-clip.is-open {
        height: auto;
        max-height: calc(100vh - 50px - env(safe-area-inset-top, 0px));
        pointer-events: auto;
        transition: height 0s 0s;
    }
    .cl-filter-panel {
        background: #fff;
        border-radius: 0 0 16px 16px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        padding: 20px 16px;
        transform: translateY(-100%);
        transition: transform 0.3s cubic-bezier(.4,0,.2,1);
    }
    .cl-filter-clip.is-open .cl-filter-panel { transform: translateY(0); }
    .cl-fp-title {
        font-size: 13px;
        font-weight: 600;
        color: #666;
        margin-bottom: 10px;
    }
    .cl-fp-chips {
        display: flex;
        gap: 8px;
        margin-bottom: 18px;
    }
    .cl-fp-chip {
        flex: 1;
        height: 34px;
        border: 1px solid #eee;
        border-radius: 8px;
        background: #f9f9f9;
        font-size: 13px;
        font-weight: 500;
        color: #666;
        transition: all 0.15s;
    }
    .cl-fp-chip.is-active {
        background: color-mix(in srgb, var(--theme-primary, #667eea) 8%, white);
        border-color: var(--theme-primary, #667eea);
        color: var(--theme-primary, #667eea);
        font-weight: 600;
    }
    .cl-fp-dates {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }
    .cl-fp-date-wrap {
        flex: 1;
        position: relative;
    }
    .cl-fp-date-input {
        width: 100%;
        height: 42px;
        border: none;
        border-radius: 21px;
        background: #f5f5f5;
        padding: 0 16px;
        font-size: 13px;
        color: transparent;
        text-align: center;
        outline: none;
        -webkit-appearance: none;
    }
    .cl-fp-date-input.has-value { color: #333; }
    .cl-fp-date-input:focus { background: color-mix(in srgb, var(--theme-primary, #667eea) 8%, white); }
    .cl-fp-date-label {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        color: #bbb;
        pointer-events: none;
    }
    .cl-fp-date-input.has-value + .cl-fp-date-label { display: none; }
    .cl-fp-sep { color: #bbb; font-size: 13px; flex-shrink: 0; }
    .cl-fp-actions {
        display: flex;
        gap: 10px;
    }
    .cl-fp-btn {
        flex: 1;
        height: 40px;
        border: 0;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        transition: opacity 0.15s;
    }
    .cl-fp-btn:active { opacity: 0.7; }
    .cl-fp-btn.is-reset {
        background: #f5f5f5;
        color: #999;
    }
    .cl-fp-btn.is-confirm {
        background: var(--theme-primary, #667eea);
        color: #fff;
    }
    .cl-page {
        min-height: 100vh;
        background: #f4f5f7;
        padding: 12px 12px calc(20px + env(safe-area-inset-bottom, 0px));
        -webkit-tap-highlight-color: transparent;
        -webkit-font-smoothing: antialiased;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }
    /* ===== 统计概览 ===== */
    .cl-summary {
        background: linear-gradient(160deg, var(--theme-primary, #667eea) 0%, var(--theme-secondary, #764ba2) 100%);
        padding: 18px 16px 14px;
        color: #fff;
        position: relative;
        overflow: hidden;
        border-radius: 18px;
        box-shadow: 0 10px 26px rgba(var(--tp-rgb), .18);
    }
    .cl-summary::before {
        content: '';
        position: absolute;
        top: -50px; right: -30px;
        width: 140px; height: 140px;
        border-radius: 50%;
        background: rgba(255,255,255,0.06);
        pointer-events: none;
    }
    .cl-summary-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
        position: relative;
        z-index: 1;
    }
    .cl-summary-balance {
        margin-bottom: 16px;
        position: relative;
        z-index: 1;
    }
    .cl-summary-link {
        display: inline-flex;
        align-items: center;
        gap: 2px;
        font-size: 12px;
        color: rgba(255,255,255,0.75);
        text-decoration: none;
        padding: 4px 10px;
        border-radius: 14px;
        background: rgba(255,255,255,0.12);
        transition: background 0.15s;
    }
    .cl-summary-link:active {
        background: rgba(255,255,255,0.2);
    }
    .cl-summary-link i { font-size: 13px; }
    .cl-summary-label {
        font-size: 12px;
        opacity: 0.7;
        margin-bottom: 6px;
    }
    .cl-summary-amount {
        font-size: 32px;
        font-weight: 700;
        font-feature-settings: 'tnum';
        letter-spacing: -0.5px;
        line-height: 1;
    }
    .cl-summary-amount small {
        font-size: 16px;
        font-weight: 500;
        margin-right: 2px;
        opacity: 0.85;
    }
    .cl-summary-row {
        display: flex;
        gap: 0;
        position: relative;
        z-index: 1;
    }
    .cl-summary-stat {
        flex: 1;
        text-align: center;
    }
    .cl-summary-stat + .cl-summary-stat {
        border-left: 1px solid rgba(255,255,255,0.15);
    }
    .cl-summary-stat-label {
        font-size: 11px;
        opacity: 0.6;
        margin-bottom: 4px;
    }
    .cl-summary-stat-val {
        font-size: 16px;
        font-weight: 600;
    }
    .cl-summary-stat-note {
        font-size: 10px;
        opacity: 0.5;
        margin-top: 2px;
    }
    /* ===== 筛选 Tabs ===== */
    .cl-filter{display: flex; margin: 12px 0 0; padding: 4px; background: linear-gradient(0deg, #fff, #f3f5f8); border: 2px solid #fff; border-radius: 10px; position: sticky; top: calc(50px + env(safe-area-inset-top, 0px) + 8px); z-index: 15; box-shadow: var(--shadow-primary);}
    .cl-filter-tab {
        flex: 1;
        height: 38px;
        border: 0;
        background: transparent;
        border-radius: 999px;
        font-size: 14px;
        font-weight: 500;
        color: #888;
        position: relative;
        transition: color 0.25s;
    }
    .cl-filter-tab.is-active {
        color: var(--theme-primary, #667eea);
        font-weight: 600;
        background: color-mix(in srgb, var(--theme-primary, #667eea) 8%, white);
        box-shadow: none; border-radius: 10px;
    }
    .cl-filter-indicator {
        position: absolute;
        bottom: 4px;
        left: 0;
        height: 3px;
        border-radius: 3px;
        background: var(--theme-primary, #667eea);
        will-change: transform, width;
        transition: none;
    }
    /* ===== 流水列表 ===== */
    .cl-list {
        background: transparent;
        margin-top: 12px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .cl-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        background: linear-gradient(0deg, #fff, #f3f5f8);
        border: 2px solid #fff;
        border-radius: 10px;
        box-shadow: var(--shadow-primary);
    }
    .cl-item:last-child { border-bottom: 2px solid #fff; }
    .cl-item-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .cl-item-icon.is-in {
        background: color-mix(in srgb, var(--theme-primary, #667eea) 8%, white);
        color: var(--theme-primary, #667eea);
    }
    .cl-item-icon.is-out {
        background: #fef2f2;
        color: #ef4444;
    }
    .cl-item-body {
        flex: 1;
        min-width: 0;
    }
    .cl-item-desc {
        font-size: 13px;
        font-weight: 500;
        color: #1a1a1a;
        line-height: 1.4;
        word-break: break-all;
    }
    .cl-item-meta {
        margin-top: 3px;
        font-size: 11px;
        color: #bbb;
    }
    .cl-item-right {
        text-align: right;
        flex-shrink: 0;
    }
    .cl-item-amount {
        font-size: 15px;
        font-weight: 600;
        font-feature-settings: 'tnum';
        line-height: 1.3;
    }
    .cl-item-amount.is-in { color: var(--theme-primary, #667eea); }
    .cl-item-amount.is-out { color: #333; }
    /* ===== 分页器 / 状态 ===== */
    .cl-pager { display: none; margin-top: 12px; padding-bottom: 8px; }
    .cl-pager.is-visible { display: block; }
    .cl-pager-row { display: grid; grid-template-columns: 72px minmax(0,1fr) 72px; gap: 8px; align-items: center; }
    .cl-page-btn { height: 32px; border: 0; border-radius: 999px; background: #fff; color: var(--theme-primary, #667eea); font-size: 12px; font-weight: 900; display: flex; align-items: center; justify-content: center; gap: 4px; box-shadow: 0 4px 14px rgba(31,52,88,0.06); }
    .cl-page-btn:disabled { background: #f8f9fb; color: #c0c7d2; box-shadow: none; }
    .cl-page-current { height: 32px; border-radius: 999px; background: #fff; color: #20242c; font-size: 12px; font-weight: 900; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(31,52,88,0.06); }
    .cl-empty {
        padding: 60px 20px;
        text-align: center;
        background: linear-gradient(0deg, #fff, #f3f5f8);
        color: #bbb;
        font-size: 13px;
        border-radius: 10px;
        border: 2px solid #fff;
        box-shadow: var(--shadow-primary);
    }
    .cl-empty svg {
        display: block;
        width: 140px;
        height: auto;
        margin: 0 auto 10px;
    }
    .cl-empty-text {
        font-size: 13px;
        color: #bbb;
    }
    /* ===== 日期分组 ===== */
    .cl-date-header {
        padding: 6px 4px 2px;
        font-size: 12px;
        font-weight: 600;
        color: #999;
        background: transparent;
    }
</style>

<button type='button' class='cl-topbar-filter' id='clFilterBtn'>
    <i class='fa fa-sliders'></i>
    <span class='cl-badge' id='clFilterBadge'></span>
</button>
<div class='cl-filter-overlay' id='clFilterOverlay'></div>
<div class='cl-filter-clip' id='clFilterClip'>
    <div class='cl-filter-panel' id='clFilterPanel'>
        <div class='cl-fp-title'><?= $virtualCurrencyNameEsc ?>类型</div>
        <div class='cl-fp-chips'>
            <button type='button' class='cl-fp-chip is-active' data-type='all'>全部</button>
            <button type='button' class='cl-fp-chip' data-type='in'>获得</button>
            <button type='button' class='cl-fp-chip' data-type='out'>扣减</button>
        </div>
        <div class='cl-fp-title'>时间段</div>
        <div class='cl-fp-dates'>
            <div class='cl-fp-date-wrap'>
                <input type='date' class='cl-fp-date-input' id='clDateStart'>
                <div class='cl-fp-date-label'>开始时间</div>
            </div>
            <span class='cl-fp-sep'>至</span>
            <div class='cl-fp-date-wrap'>
                <input type='date' class='cl-fp-date-input' id='clDateEnd'>
                <div class='cl-fp-date-label'>结束时间</div>
            </div>
        </div>
        <div class='cl-fp-actions'>
            <button type='button' class='cl-fp-btn is-reset' id='clFpReset'>重置</button>
            <button type='button' class='cl-fp-btn is-confirm' id='clFpConfirm'>确认筛选</button>
        </div>
    </div>
</div>

<div class='cl-page'>
    <div class='cl-summary'>
        <div class='cl-summary-top'>
            <div class='cl-summary-label'>今日收益</div>
            <a href='/user/balance.php?action=balance_log' class='cl-summary-link'>收支明细 <i class='fa fa-angle-right'></i></a>
        </div>
        <div class='cl-summary-balance'>
            <div class='cl-summary-amount'><small><i class='fa fa-diamond'></i></small> <span id='clTodayIncome'>0</span></div>
        </div>
        <div class='cl-summary-row'>
            <div class='cl-summary-stat'>
                <div class='cl-summary-stat-label'>累计获得</div>
                <div class='cl-summary-stat-val' id='clStatIn'>--</div>
            </div>
            <div class='cl-summary-stat'>
                <div class='cl-summary-stat-label'>累计扣减</div>
                <div class='cl-summary-stat-val' id='clStatOut'>--</div>
            </div>
            <div class='cl-summary-stat'>
                <div class='cl-summary-stat-label'>总笔数</div>
                <div class='cl-summary-stat-val' id='clStatTotal'>--</div>
                <div class='cl-summary-stat-note'></div>
            </div>
        </div>
    </div>

    <div class='cl-filter'>
        <button type='button' class='cl-filter-tab is-active' data-filter='all'>全部</button>
        <button type='button' class='cl-filter-tab' data-filter='in'>获得</button>
        <button type='button' class='cl-filter-tab' data-filter='out'>扣减</button>
        <div class='cl-filter-indicator' id='clFilterIndicator'></div>
    </div>

    <div id='clListWrap'>
        <div id='clList' class='cl-list'></div>
    </div>
    <nav class='cl-pager' id='clPager'>
        <div class='cl-pager-row'>
            <button type='button' class='cl-page-btn' id='clPrevPage'><i class='fa fa-angle-left'></i> 上一页</button>
            <div class='cl-page-current' id='clPageCurrent'>1 / 1</div>
            <button type='button' class='cl-page-btn' id='clNextPage'>下一页 <i class='fa fa-angle-right'></i></button>
        </div>
    </nav>
</div>

<template id="dcEmptyIllust"><?php include __DIR__ . '/_svg_empty.php'; ?></template>

<script>
layui.use(['layer'], function(){
    var $ = layui.$;
    var layer = layui.layer;
    var page = 1, limit = 10, totalCount = 0, loading = false;
    var currentFilter = 'all';
    var allData = [];
    var virtualCurrencyName = <?= $virtualCurrencyNameJs ?>;

    function escapeHtml(str) {
        return $('<div/>').text(str == null ? '' : String(str)).html();
    }

    function formatDate(str) {
        if (!str) return '';
        var d = str.substring(0, 10);
        var t = str.substring(11, 16);
        var today = new Date();
        var todayStr = today.getFullYear() + '-' + String(today.getMonth()+1).padStart(2,'0') + '-' + String(today.getDate()).padStart(2,'0');
        var yest = new Date(today); yest.setDate(yest.getDate()-1);
        var yestStr = yest.getFullYear() + '-' + String(yest.getMonth()+1).padStart(2,'0') + '-' + String(yest.getDate()).padStart(2,'0');
        if (d === todayStr) return '今天 ' + t;
        if (d === yestStr) return '昨天 ' + t;
        return d.substring(5) + ' ' + t;
    }

    function groupDateLabel(str) {
        if (!str) return '';
        var d = str.substring(0, 10);
        var today = new Date();
        var todayStr = today.getFullYear() + '-' + String(today.getMonth()+1).padStart(2,'0') + '-' + String(today.getDate()).padStart(2,'0');
        var yest = new Date(today); yest.setDate(yest.getDate()-1);
        var yestStr = yest.getFullYear() + '-' + String(yest.getMonth()+1).padStart(2,'0') + '-' + String(yest.getDate()).padStart(2,'0');
        if (d === todayStr) return '今天';
        if (d === yestStr) return '昨天';
        var year = d.substring(0,4);
        if (year === String(today.getFullYear())) return d.substring(5).replace('-', '月') + '日';
        return d.replace(/-/g, '.');
    }

    var _emptySvg = (document.getElementById('dcEmptyIllust') || {}).innerHTML || '';

    function renderList(data) {
        if (!data.length) {
            if (page === 1) {
                $('#clList').html('<div class="cl-empty">' + _emptySvg + '<div class="cl-empty-text">暂无' + escapeHtml(virtualCurrencyName) + '记录</div></div>');
            }
            return;
        }
        var html = '';
        var lastDate = '';
        for (var i = 0; i < data.length; i++) {
            var d = data[i];
            var isIn = d.plus === 'y';
            var timeStr = d.create_time_text || '';
            var dateLabel = groupDateLabel(timeStr);
            if (dateLabel && dateLabel !== lastDate) {
                html += '<div class="cl-date-header">' + escapeHtml(dateLabel) + '</div>';
                lastDate = dateLabel;
            }
            var rawDate = (timeStr || '').substring(0, 10);
            html += '<div class="cl-item" data-plus="' + (isIn ? 'y' : 'n') + '" data-date="' + rawDate + '">'
                + '<div class="cl-item-icon ' + (isIn ? 'is-in' : 'is-out') + '"><i class="fa ' + (isIn ? 'fa-arrow-up' : 'fa-arrow-down') + '"></i></div>'
                + '<div class="cl-item-body">'
                + '<div class="cl-item-desc">' + escapeHtml(d.content || (virtualCurrencyName + '变动')) + '</div>'
                + '<div class="cl-item-meta">' + escapeHtml(formatDate(timeStr)) + '</div>'
                + '</div>'
                + '<div class="cl-item-right">'
                + '<div class="cl-item-amount ' + (isIn ? 'is-in' : 'is-out') + '">' + (isIn ? '+' : '-') + escapeHtml(d.abs_amount) + '</div>'
                + '</div>'
                + '</div>';
        }
        $('#clList').html(html);
        applyFilter();
    }

    var dateStart = '', dateEnd = '';

    function applyFilter() {
        $('#clList .cl-empty.is-filter-empty').remove();
        $('#clList .cl-item').each(function(){
            var $el = $(this);
            var show = true;
            if (currentFilter !== 'all') {
                var val = currentFilter === 'in' ? 'y' : 'n';
                if ($el.attr('data-plus') !== val) show = false;
            }
            if (show && (dateStart || dateEnd)) {
                var raw = $el.attr('data-date') || '';
                if (raw) {
                    if (dateStart && raw < dateStart) show = false;
                    if (dateEnd && raw > dateEnd) show = false;
                }
            }
            $el.toggle(show);
        });
        $('#clList .cl-date-header').each(function(){
            var $next = $(this).nextUntil('.cl-date-header', '.cl-item:visible');
            $(this).toggle($next.length > 0);
        });
        if (allData.length > 0 && $('#clList .cl-item:visible').length === 0) {
            $('#clList').append('<div class="cl-empty is-filter-empty">' + _emptySvg + '<div class="cl-empty-text">暂无相关记录</div></div>');
        }
    }

    function calcTodayIncome() {
        var today = (new Date()).getFullYear() + '-' + String((new Date()).getMonth()+1).padStart(2,'0') + '-' + String((new Date()).getDate()).padStart(2,'0');
        var sum = 0;
        for (var i = 0; i < allData.length; i++) {
            var d = allData[i];
            if (d.plus === 'y' && (d.create_time_text || '').substring(0, 10) === today) {
                sum += parseInt(d.abs_amount) || 0;
            }
        }
        $('#clTodayIncome').text(sum);
    }

    function updatePager() {
        var totalPages = Math.max(1, Math.ceil((parseInt(totalCount, 10) || 0) / limit));
        if (page > totalPages) page = totalPages;
        $('#clPager').toggleClass('is-visible', totalCount > limit);
        $('#clPrevPage').prop('disabled', loading || page <= 1);
        $('#clNextPage').prop('disabled', loading || page >= totalPages);
        $('#clPageCurrent').text(page + ' / ' + totalPages);
    }

    function loadData() {
        if (loading) return;
        loading = true;
        updatePager();
        $.get('?action=credits_log_list', { page: page, limit: limit }, function(res){
            if (res.code === 0) {
                totalCount = parseInt(res.count || 0, 10) || 0;
                var iAmt = parseInt(res.stat_inc_amt) || 0;
                var dAmt = parseInt(res.stat_dec_amt) || 0;
                $('#clStatTotal').text(totalCount);
                $('#clStatIn').text(iAmt > 0 ? iAmt : '--');
                $('#clStatOut').text(dAmt > 0 ? dAmt : '--');
                var list = res.data || [];
                allData = list;
                calcTodayIncome();
                renderList(list);
                updatePager();
            } else {
                layer.msg(res.msg || '加载失败');
            }
        }, 'json').fail(function(){
            layer.msg('加载失败');
        }).always(function(){
            loading = false;
            updatePager();
        });
    }

    // ===== 拉扯感 indicator =====
    var $indicator = $('#clFilterIndicator');
    var indicatorTimer = null;

    function moveIndicator($tab, animate) {
        if (!$tab.length) return;
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

        if (indicatorTimer) { clearTimeout(indicatorTimer); indicatorTimer = null; }

        $indicator.css({
            left: stretchLeft + 'px',
            width: stretchWidth + 'px',
            transition: 'left 0.16s cubic-bezier(.4,0,.2,1), width 0.16s cubic-bezier(.4,0,.2,1)'
        });

        indicatorTimer = setTimeout(function() {
            $indicator.css({
                left: targetLeft + 'px',
                width: indicatorW + 'px',
                transition: 'left 0.13s cubic-bezier(.4,0,.2,1), width 0.13s cubic-bezier(.4,0,.2,1)'
            });
        }, 200);
    }

    moveIndicator($('.cl-filter-tab.is-active'), false);

    $(document).on('click', '.cl-filter-tab', function(){
        $('.cl-filter-tab').removeClass('is-active');
        $(this).addClass('is-active');
        currentFilter = $(this).attr('data-filter');
        moveIndicator($(this), true);
        applyFilter();
    });

    $('#clPrevPage').on('click', function(){
        if (page <= 1 || loading) return;
        page -= 1;
        loadData();
    });
    $('#clNextPage').on('click', function(){
        var totalPages = Math.max(1, Math.ceil((parseInt(totalCount, 10) || 0) / limit));
        if (page >= totalPages || loading) return;
        page += 1;
        loadData();
    });

    loadData();

    // ===== 筛选面板逻辑 =====
    var $fpanel = $('#clFilterPanel'), $foverlay = $('#clFilterOverlay');
    var fpOpen = false;

    var $fclip = $('#clFilterClip');
    function toggleFilterPanel(open) {
        fpOpen = typeof open === 'boolean' ? open : !fpOpen;
        $fclip.toggleClass('is-open', fpOpen);
        $foverlay.toggleClass('is-open', fpOpen);
    }

    function updateBadge() {
        var count = 0;
        if (currentFilter !== 'all') count++;
        if (dateStart || dateEnd) count++;
        var $b = $('#clFilterBadge');
        if (count > 0) {
            $b.text(count).addClass('is-show');
        } else {
            $b.removeClass('is-show');
        }
    }

    $('#clFilterBtn').on('click', function(){ toggleFilterPanel(); });
    $foverlay.on('click', function(){ toggleFilterPanel(false); });

    // date placeholder
    $('.cl-fp-date-input').on('change input', function(){
        $(this).toggleClass('has-value', !!this.value);
    });

    $(document).on('click', '.cl-fp-chip', function(){
        $('.cl-fp-chip').removeClass('is-active');
        $(this).addClass('is-active');
    });

    $('#clFpConfirm').on('click', function(){
        var ds = $('#clDateStart').val() || '', de = $('#clDateEnd').val() || '';
        if (ds && de) {
            var d1 = new Date(ds), d2 = new Date(de);
            if (d2 < d1) { layer.msg('结束时间不能早于开始时间'); return; }
            var maxEnd = new Date(d1); maxEnd.setMonth(maxEnd.getMonth() + 1);
            if (d2 > maxEnd) { layer.msg('时间范围最长一个月'); return; }
        }
        var type = $('.cl-fp-chip.is-active').attr('data-type') || 'all';
        dateStart = ds;
        dateEnd = de;
        currentFilter = type;
        $('.cl-filter-tab').removeClass('is-active');
        var $tab = $('.cl-filter-tab[data-filter="' + type + '"]');
        $tab.addClass('is-active');
        moveIndicator($tab, false);
        applyFilter();
        updateBadge();
        toggleFilterPanel(false);
    });

    $('#clFpReset').on('click', function(){
        $('.cl-fp-chip').removeClass('is-active');
        $('.cl-fp-chip[data-type="all"]').addClass('is-active');
        $('#clDateStart').val('').removeClass('has-value');
        $('#clDateEnd').val('').removeClass('has-value');
        dateStart = '';
        dateEnd = '';
        currentFilter = 'all';
        $('.cl-filter-tab').removeClass('is-active');
        var $tab = $('.cl-filter-tab[data-filter="all"]');
        $tab.addClass('is-active');
        moveIndicator($tab, false);
        applyFilter();
        updateBadge();
        toggleFilterPanel(false);
    });

    $(document).on('click', '.cl-filter-tab', function(){
        var f = $(this).attr('data-filter');
        $('.cl-fp-chip').removeClass('is-active');
        $('.cl-fp-chip[data-type="' + f + '"]').addClass('is-active');
        updateBadge();
    });

    // ===== 左右滑动切换 Tabs =====
    var filterNames = ['all', 'in', 'out'];
    var touchStartX = 0, touchStartY = 0, touchMoved = false;
    var $listWrap = $('.cl-page');

    function getCurrentFilterIndex() {
        var cur = $('.cl-filter-tab.is-active').attr('data-filter');
        var idx = filterNames.indexOf(cur);
        return idx >= 0 ? idx : 0;
    }

    function switchFilter(name) {
        $('.cl-filter-tab').removeClass('is-active');
        var $tab = $('.cl-filter-tab[data-filter="' + name + '"]');
        $tab.addClass('is-active');
        currentFilter = name;
        moveIndicator($tab, true);
        applyFilter();
    }

    $listWrap.on('touchstart', function(e) {
        var t = e.originalEvent.touches[0];
        touchStartX = t.clientX;
        touchStartY = t.clientY;
        touchMoved = false;
    });

    $listWrap.on('touchmove', function(e) {
        if (touchMoved) return;
        var t = e.originalEvent.touches[0];
        var dx = t.clientX - touchStartX;
        var dy = t.clientY - touchStartY;
        if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 30) {
            touchMoved = true;
            var idx = getCurrentFilterIndex();
            if (dx < 0 && idx < filterNames.length - 1) {
                switchFilter(filterNames[idx + 1]);
            } else if (dx > 0 && idx > 0) {
                switchFilter(filterNames[idx - 1]);
            }
        }
    });
});
</script>

<script>
    $('#menu-balance-log').addClass('menu-current');
</script>
