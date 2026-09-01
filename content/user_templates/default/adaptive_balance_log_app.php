<?php
defined('DC_ROOT') || exit('access denied!');
$_userMoney = isset($user['money']) ? (float)$user['money'] : 0;
$virtualCurrencyNameEsc = htmlspecialchars(getVirtualCurrencyName(), ENT_QUOTES, 'UTF-8');
?>
<style>
    * { box-sizing: border-box; }
    .uc-site-footer { display: none !important; }
    /* 替换topbar右侧占位为筛选按钮 */
    .m-topbar-placeholder { visibility: hidden; }
    .bl-topbar-filter {
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
    .bl-topbar-filter .bl-badge {
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
    .bl-topbar-filter .bl-badge.is-show { display: flex; }
    /* ===== 筛选面板 ===== */
    .bl-filter-overlay {
        position: fixed;
        inset: 0;
        z-index: 199;
        background: rgba(0,0,0,0.35);
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.25s, visibility 0.25s;
    }
    .bl-filter-overlay.is-open { opacity: 1; visibility: visible; }
    .bl-filter-clip {
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
    .bl-filter-clip.is-open {
        height: auto;
        max-height: calc(100vh - 50px - env(safe-area-inset-top, 0px));
        pointer-events: auto;
        transition: height 0s 0s;
    }
    .bl-filter-panel {
        background: #fff;
        border-radius: 0 0 16px 16px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        padding: 20px 16px;
        transform: translateY(-100%);
        transition: transform 0.3s cubic-bezier(.4,0,.2,1);
    }
    .bl-filter-clip.is-open .bl-filter-panel { transform: translateY(0); }
    .bl-fp-title {
        font-size: 13px;
        font-weight: 600;
        color: #666;
        margin-bottom: 10px;
    }
    .bl-fp-chips {
        display: flex;
        gap: 8px;
        margin-bottom: 18px;
    }
    .bl-fp-chip {
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
    .bl-fp-chip.is-active {
        background: color-mix(in srgb, var(--theme-primary, #667eea) 8%, white);
        border-color: var(--theme-primary, #667eea);
        color: var(--theme-primary, #667eea);
        font-weight: 600;
    }
    .bl-fp-dates {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }
    .bl-fp-date-wrap {
        flex: 1;
        position: relative;
    }
    .bl-fp-date-input {
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
    .bl-fp-date-input.has-value { color: #333; }
    .bl-fp-date-input:focus { background: color-mix(in srgb, var(--theme-primary, #667eea) 8%, white); }
    .bl-fp-date-label {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        color: #bbb;
        pointer-events: none;
    }
    .bl-fp-date-input.has-value + .bl-fp-date-label { display: none; }
    .bl-fp-sep { color: #bbb; font-size: 13px; flex-shrink: 0; }
    .bl-fp-actions {
        display: flex;
        gap: 10px;
    }
    .bl-fp-btn {
        flex: 1;
        height: 40px;
        border: 0;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        transition: opacity 0.15s;
    }
    .bl-fp-btn:active { opacity: 0.7; }
    .bl-fp-btn.is-reset {
        background: #f5f5f5;
        color: #999;
    }
    .bl-fp-btn.is-confirm {
        background: var(--theme-primary, #667eea);
        color: #fff;
    }
    .bl-page {
        min-height: 100vh;
        background: #f4f5f7;
        padding: 12px 12px calc(20px + env(safe-area-inset-bottom, 0px));
        -webkit-tap-highlight-color: transparent;
        -webkit-font-smoothing: antialiased;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }
    /* ===== 统计概览 ===== */
    .bl-summary {
        background: linear-gradient(160deg, var(--theme-primary, #667eea) 0%, var(--theme-secondary, #764ba2) 100%);
        padding: 18px 16px 14px;
        color: #fff;
        position: relative;
        overflow: hidden;
        border-radius: 18px;
        box-shadow: 0 10px 26px rgba(var(--tp-rgb), .18);
    }
    .bl-summary::before {
        content: '';
        position: absolute;
        top: -50px; right: -30px;
        width: 140px; height: 140px;
        border-radius: 50%;
        background: rgba(255,255,255,0.06);
        pointer-events: none;
    }
    .bl-summary-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
        position: relative;
        z-index: 1;
    }
    .bl-summary-balance {
        margin-bottom: 16px;
        position: relative;
        z-index: 1;
    }
    .bl-summary-link {
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
    .bl-summary-link:active {
        background: rgba(255,255,255,0.2);
    }
    .bl-summary-link i { font-size: 13px; }
    .bl-summary-label {
        font-size: 12px;
        opacity: 0.7;
        margin-bottom: 6px;
    }
    .bl-summary-amount {
        font-size: 32px;
        font-weight: 700;
        font-feature-settings: 'tnum';
        letter-spacing: -0.5px;
        line-height: 1;
    }
    .bl-summary-amount small {
        font-size: 18px;
        font-weight: 500;
        margin-right: 2px;
        opacity: 0.85;
    }
    .bl-summary-row {
        display: flex;
        gap: 0;
        position: relative;
        z-index: 1;
    }
    .bl-summary-stat {
        flex: 1;
        text-align: center;
    }
    .bl-summary-stat + .bl-summary-stat {
        border-left: 1px solid rgba(255,255,255,0.15);
    }
    .bl-summary-stat-label {
        font-size: 11px;
        opacity: 0.6;
        margin-bottom: 4px;
    }
    .bl-summary-stat-val {
        font-size: 16px;
        font-weight: 600;
    }
    .bl-summary-stat-note {
        font-size: 10px;
        opacity: 0.5;
        margin-top: 2px;
    }
    /* ===== 筛选 Tabs ===== */
    .bl-filter{display: flex; margin: 12px 0 0; padding: 4px; background: linear-gradient(0deg, #fff, #f3f5f8); border: 2px solid #fff; border-radius: 10px; position: sticky; top: calc(50px + env(safe-area-inset-top, 0px) + 8px); z-index: 15; box-shadow: var(--shadow-primary);}
    .bl-filter-tab {
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
    .bl-filter-tab.is-active {
        color: var(--theme-primary, #667eea);
        font-weight: 600;
        background: color-mix(in srgb, var(--theme-primary, #667eea) 8%, white);
        box-shadow: none; border-radius: 10px;
    }
    .bl-filter-indicator {
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
    .bl-list {
        background: transparent;
        margin-top: 12px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .bl-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        background: linear-gradient(0deg, #fff, #f3f5f8);
        border: 2px solid #fff;
        border-radius: 10px;
        box-shadow: var(--shadow-primary);
    }
    .bl-item:last-child { border-bottom: 2px solid #fff; }
    .bl-item-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .bl-item-icon.is-in {
        background: #ecfdf5;
        color: #10b981;
    }
    .bl-item-icon.is-out {
        background: #fef2f2;
        color: #ef4444;
    }
    .bl-item-body {
        flex: 1;
        min-width: 0;
    }
    .bl-item-desc {
        font-size: 13px;
        font-weight: 500;
        color: #1a1a1a;
        line-height: 1.4;
        word-break: break-all;
    }
    .bl-item-meta {
        margin-top: 3px;
        font-size: 11px;
        color: #bbb;
    }
    .bl-item-right {
        text-align: right;
        flex-shrink: 0;
    }
    .bl-item-amount {
        font-size: 15px;
        font-weight: 600;
        font-feature-settings: 'tnum';
        line-height: 1.3;
    }
    .bl-item-amount.is-in { color: #10b981; }
    .bl-item-amount.is-out { color: #333; }
    .bl-item-balance {
        margin-top: 2px;
        font-size: 11px;
        color: #ccc;
    }
    /* ===== 分页器 / 状态 ===== */
    .bl-pager { display: none; margin-top: 12px; padding-bottom: 8px; }
    .bl-pager.is-visible { display: block; }
    .bl-pager-row { display: grid; grid-template-columns: 72px minmax(0,1fr) 72px; gap: 8px; align-items: center; }
    .bl-page-btn { height: 32px; border: 0; border-radius: 999px; background: #fff; color: var(--theme-primary, #667eea); font-size: 12px; font-weight: 900; display: flex; align-items: center; justify-content: center; gap: 4px; box-shadow: 0 4px 14px rgba(31,52,88,0.06); }
    .bl-page-btn:disabled { background: #f8f9fb; color: #c0c7d2; box-shadow: none; }
    .bl-page-current { height: 32px; border-radius: 999px; background: #fff; color: #20242c; font-size: 12px; font-weight: 900; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(31,52,88,0.06); }
    .bl-empty {
        padding: 60px 20px;
        text-align: center;
        background: linear-gradient(0deg, #fff, #f3f5f8);
        color: #bbb;
        font-size: 13px;
        border-radius: 10px;
        border: 2px solid #fff;
        box-shadow: var(--shadow-primary);
    }
    .bl-empty svg {
        display: block;
        width: 140px;
        height: auto;
        margin: 0 auto 10px;
    }
    .bl-empty-text {
        font-size: 13px;
        color: #bbb;
    }
    /* ===== 日期分组 ===== */
    .bl-date-header {
        padding: 6px 4px 2px;
        font-size: 12px;
        font-weight: 600;
        color: #999;
        background: transparent;
    }
</style>

<button type='button' class='bl-topbar-filter' id='blFilterBtn'>
    <i class='fa fa-sliders'></i>
    <span class='bl-badge' id='blFilterBadge'></span>
</button>
<div class='bl-filter-overlay' id='blFilterOverlay'></div>
<div class='bl-filter-clip' id='blFilterClip'>
    <div class='bl-filter-panel' id='blFilterPanel'>
        <div class='bl-fp-title'>收支类型</div>
        <div class='bl-fp-chips'>
            <button type='button' class='bl-fp-chip is-active' data-type='all'>全部</button>
            <button type='button' class='bl-fp-chip' data-type='in'>收入</button>
            <button type='button' class='bl-fp-chip' data-type='out'>支出</button>
        </div>
        <div class='bl-fp-title'>时间段</div>
        <div class='bl-fp-dates'>
            <div class='bl-fp-date-wrap'>
                <input type='date' class='bl-fp-date-input' id='blDateStart'>
                <div class='bl-fp-date-label'>开始时间</div>
            </div>
            <span class='bl-fp-sep'>至</span>
            <div class='bl-fp-date-wrap'>
                <input type='date' class='bl-fp-date-input' id='blDateEnd'>
                <div class='bl-fp-date-label'>结束时间</div>
            </div>
        </div>
        <div class='bl-fp-actions'>
            <button type='button' class='bl-fp-btn is-reset' id='blFpReset'>重置</button>
            <button type='button' class='bl-fp-btn is-confirm' id='blFpConfirm'>确认筛选</button>
        </div>
    </div>
</div>

<div class='bl-page'>
    <div class='bl-summary'>
        <div class='bl-summary-top'>
            <div class='bl-summary-label'>今日收益</div>
            <a href='/user/balance.php?action=credits_log' class='bl-summary-link'><?= $virtualCurrencyNameEsc ?>明细 <i class='fa fa-angle-right'></i></a>
        </div>
        <div class='bl-summary-balance'>
            <div class='bl-summary-amount'><small>¥</small><span id='blTodayIncome'>0.00</span></div>
        </div>
        <div class='bl-summary-row'>
            <div class='bl-summary-stat'>
                <div class='bl-summary-stat-label'>累计收入</div>
                <div class='bl-summary-stat-val' id='blStatIn'>--</div>
            </div>
            <div class='bl-summary-stat'>
                <div class='bl-summary-stat-label'>累计支出</div>
                <div class='bl-summary-stat-val' id='blStatOut'>--</div>
            </div>
            <div class='bl-summary-stat'>
                <div class='bl-summary-stat-label'>总笔数</div>
                <div class='bl-summary-stat-val' id='blStatTotal'>--</div>
                <div class='bl-summary-stat-note'></div>
            </div>
        </div>
    </div>

    <div class='bl-filter'>
        <button type='button' class='bl-filter-tab is-active' data-filter='all'>全部</button>
        <button type='button' class='bl-filter-tab' data-filter='in'>收入</button>
        <button type='button' class='bl-filter-tab' data-filter='out'>支出</button>
        <div class='bl-filter-indicator' id='blFilterIndicator'></div>
    </div>

    <div id='blListWrap'>
        <div id='blList' class='bl-list'></div>
    </div>
    <nav class='bl-pager' id='blPager'>
        <div class='bl-pager-row'>
            <button type='button' class='bl-page-btn' id='blPrevPage'><i class='fa fa-angle-left'></i> 上一页</button>
            <div class='bl-page-current' id='blPageCurrent'>1 / 1</div>
            <button type='button' class='bl-page-btn' id='blNextPage'>下一页 <i class='fa fa-angle-right'></i></button>
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
                $('#blList').html('<div class="bl-empty">' + _emptySvg + '<div class="bl-empty-text">暂无收支记录</div></div>');
            }
            return;
        }
        var html = '';
        var lastDate = '';
        for (var i = 0; i < data.length; i++) {
            var d = data[i];
            var isIn = d.plus === 'y';
            var dateLabel = groupDateLabel(d.create_time);
            if (dateLabel && dateLabel !== lastDate) {
                html += '<div class="bl-date-header">' + escapeHtml(dateLabel) + '</div>';
                lastDate = dateLabel;
            }
            var after = (parseFloat(d.update_before) + (isIn ? 1 : -1) * parseFloat(d.money)).toFixed(2);
            var rawDate = (d.create_time || '').substring(0, 10);
            html += '<div class="bl-item" data-plus="' + (isIn ? 'y' : 'n') + '" data-date="' + rawDate + '">'
                + '<div class="bl-item-icon ' + (isIn ? 'is-in' : 'is-out') + '"><i class="fa ' + (isIn ? 'fa-arrow-up' : 'fa-arrow-down') + '"></i></div>'
                + '<div class="bl-item-body">'
                + '<div class="bl-item-desc">' + escapeHtml(d.description || '余额变动') + '</div>'
                + '<div class="bl-item-meta">' + escapeHtml(formatDate(d.create_time)) + '</div>'
                + '</div>'
                + '<div class="bl-item-right">'
                + '<div class="bl-item-amount ' + (isIn ? 'is-in' : 'is-out') + '">' + (isIn ? '+' : '-') + '¥' + escapeHtml(d.money) + '</div>'
                + '<div class="bl-item-balance">余额 ¥' + escapeHtml(after) + '</div>'
                + '</div>'
                + '</div>';
        }
        $('#blList').html(html);
        applyFilter();
    }

    var dateStart = '', dateEnd = '';

    function applyFilter() {
        $('#blList .bl-empty.is-filter-empty').remove();
        $('#blList .bl-item').each(function(){
            var $el = $(this);
            var show = true;
            // type filter
            if (currentFilter !== 'all') {
                var val = currentFilter === 'in' ? 'y' : 'n';
                if ($el.attr('data-plus') !== val) show = false;
            }
            // date filter
            if (show && (dateStart || dateEnd)) {
                var itemDate = ($el.find('.bl-item-meta').text() || '').replace(/今天/, getTodayStr()).replace(/昨天/, getYestStr());
                var dateOnly = itemDate.substring(0, 5);
                if (dateOnly.indexOf('-') === -1 && dateOnly.indexOf('/') === -1) {
                    // format like "06-15 12:30" -> need full date
                }
                var raw = $el.attr('data-date') || '';
                if (raw) {
                    if (dateStart && raw < dateStart) show = false;
                    if (dateEnd && raw > dateEnd) show = false;
                }
            }
            $el.toggle(show);
        });
        $('#blList .bl-date-header').each(function(){
            var $next = $(this).nextUntil('.bl-date-header', '.bl-item:visible');
            $(this).toggle($next.length > 0);
        });
        if (allData.length > 0 && $('#blList .bl-item:visible').length === 0) {
            $('#blList').append('<div class="bl-empty is-filter-empty">' + _emptySvg + '<div class="bl-empty-text">暂无相关记录</div></div>');
        }
    }

    function getTodayStr() {
        var t = new Date();
        return t.getFullYear() + '-' + String(t.getMonth()+1).padStart(2,'0') + '-' + String(t.getDate()).padStart(2,'0');
    }
    function getYestStr() {
        var t = new Date(); t.setDate(t.getDate()-1);
        return t.getFullYear() + '-' + String(t.getMonth()+1).padStart(2,'0') + '-' + String(t.getDate()).padStart(2,'0');
    }

    function calcTodayIncome() {
        var today = getTodayStr();
        var sum = 0;
        for (var i = 0; i < allData.length; i++) {
            var d = allData[i];
            if (d.plus === 'y' && (d.create_time || '').substring(0, 10) === today) {
                sum += parseFloat(d.money) || 0;
            }
        }
        $('#blTodayIncome').text(sum.toFixed(2));
    }

    function updatePager() {
        var totalPages = Math.max(1, Math.ceil((parseInt(totalCount, 10) || 0) / limit));
        if (page > totalPages) page = totalPages;
        $('#blPager').toggleClass('is-visible', totalCount > limit);
        $('#blPrevPage').prop('disabled', loading || page <= 1);
        $('#blNextPage').prop('disabled', loading || page >= totalPages);
        $('#blPageCurrent').text(page + ' / ' + totalPages);
    }

    function loadData() {
        if (loading) return;
        loading = true;
        updatePager();
        $.get('?action=index', { page: page, limit: limit }, function(res){
            if (res.code === 0) {
                totalCount = parseInt(res.count || 0, 10) || 0;
                var pAmt = parseFloat(res.stat_plus_amt) || 0;
                var mAmt = parseFloat(res.stat_minus_amt) || 0;
                $('#blStatTotal').text(totalCount);
                $('#blStatIn').text(pAmt > 0 ? '¥' + pAmt.toFixed(2) : '--');
                $('#blStatOut').text(mAmt > 0 ? '¥' + mAmt.toFixed(2) : '--');
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
    var $indicator = $('#blFilterIndicator');
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

    moveIndicator($('.bl-filter-tab.is-active'), false);

    $(document).on('click', '.bl-filter-tab', function(){
        $('.bl-filter-tab').removeClass('is-active');
        $(this).addClass('is-active');
        currentFilter = $(this).attr('data-filter');
        moveIndicator($(this), true);
        applyFilter();
    });

    $('#blPrevPage').on('click', function(){
        if (page <= 1 || loading) return;
        page -= 1;
        loadData();
    });
    $('#blNextPage').on('click', function(){
        var totalPages = Math.max(1, Math.ceil((parseInt(totalCount, 10) || 0) / limit));
        if (page >= totalPages || loading) return;
        page += 1;
        loadData();
    });

    loadData();

    // ===== 筛选面板逻辑 =====
    var $fpanel = $('#blFilterPanel'), $foverlay = $('#blFilterOverlay');
    var fpOpen = false;

    var $fclip = $('#blFilterClip');
    function toggleFilterPanel(open) {
        fpOpen = typeof open === 'boolean' ? open : !fpOpen;
        $fclip.toggleClass('is-open', fpOpen);
        $foverlay.toggleClass('is-open', fpOpen);
    }

    function updateBadge() {
        var count = 0;
        if (currentFilter !== 'all') count++;
        if (dateStart || dateEnd) count++;
        var $b = $('#blFilterBadge');
        if (count > 0) {
            $b.text(count).addClass('is-show');
        } else {
            $b.removeClass('is-show');
        }
    }

    $('#blFilterBtn').on('click', function(){ toggleFilterPanel(); });
    $foverlay.on('click', function(){ toggleFilterPanel(false); });

    // date placeholder
    $('.bl-fp-date-input').on('change input', function(){
        $(this).toggleClass('has-value', !!this.value);
    });

    // type chips in panel
    $(document).on('click', '.bl-fp-chip', function(){
        $('.bl-fp-chip').removeClass('is-active');
        $(this).addClass('is-active');
    });

    $('#blFpConfirm').on('click', function(){
        var ds = $('#blDateStart').val() || '', de = $('#blDateEnd').val() || '';
        if (ds && de) {
            var d1 = new Date(ds), d2 = new Date(de);
            if (d2 < d1) { layer.msg('结束时间不能早于开始时间'); return; }
            var maxEnd = new Date(d1); maxEnd.setMonth(maxEnd.getMonth() + 1);
            if (d2 > maxEnd) { layer.msg('时间范围最长一个月'); return; }
        }
        var type = $('.bl-fp-chip.is-active').attr('data-type') || 'all';
        dateStart = ds;
        dateEnd = de;
        currentFilter = type;
        $('.bl-filter-tab').removeClass('is-active');
        var $tab = $('.bl-filter-tab[data-filter="' + type + '"]');
        $tab.addClass('is-active');
        moveIndicator($tab, false);
        applyFilter();
        updateBadge();
        toggleFilterPanel(false);
    });

    $('#blFpReset').on('click', function(){
        $('.bl-fp-chip').removeClass('is-active');
        $('.bl-fp-chip[data-type="all"]').addClass('is-active');
        $('#blDateStart').val('').removeClass('has-value');
        $('#blDateEnd').val('').removeClass('has-value');
        dateStart = '';
        dateEnd = '';
        currentFilter = 'all';
        $('.bl-filter-tab').removeClass('is-active');
        var $tab = $('.bl-filter-tab[data-filter="all"]');
        $tab.addClass('is-active');
        moveIndicator($tab, false);
        applyFilter();
        updateBadge();
        toggleFilterPanel(false);
    });

    // sync panel chips when tab clicked
    $(document).on('click', '.bl-filter-tab', function(){
        var f = $(this).attr('data-filter');
        $('.bl-fp-chip').removeClass('is-active');
        $('.bl-fp-chip[data-type="' + f + '"]').addClass('is-active');
        updateBadge();
    });

    // ===== 左右滑动切换 Tabs =====
    var filterNames = ['all', 'in', 'out'];
    var touchStartX = 0, touchStartY = 0, touchMoved = false;
    var $listWrap = $('.bl-page');

    function getCurrentFilterIndex() {
        var cur = $('.bl-filter-tab.is-active').attr('data-filter');
        var idx = filterNames.indexOf(cur);
        return idx >= 0 ? idx : 0;
    }

    function switchFilter(name) {
        $('.bl-filter-tab').removeClass('is-active');
        var $tab = $('.bl-filter-tab[data-filter="' + name + '"]');
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
