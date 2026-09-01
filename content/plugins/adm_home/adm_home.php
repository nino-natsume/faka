<?php
/*
Plugin Name: 数据看板
Version: 2.0.1
Plugin URL:
Description: 在后台首页展示销售额、利润、订单量等核心数据图表，支持按周/月/年筛选，智能适配DIY后台主题配色
Author: DCSHOP
Author URL:
Ui: Layui
*/

defined('DC_ROOT') || exit('access denied!');

function adm_home(){
    // 读取配色插件的设置（仅在插件启用时生效）
    $activePlugins = Option::get('active_plugins') ?: [];
    $isAdminColorActive = in_array('admin_color/admin_color.php', $activePlugins);
    
    if ($isAdminColorActive) {
        $colorStorage = Storage::getInstance('admin_color');
        $primaryColor = $colorStorage->getValue('primary_color') ?: '#4C7D71';
        $primaryColorDark = $colorStorage->getValue('primary_color_dark') ?: '#4C7D71';
        $chartColor = (strpos($primaryColor, 'gradient') !== false) ? $primaryColorDark : $primaryColor;
    } else {
        $primaryColor = '#4C7D71';
        $primaryColorDark = '#4C7D71';
        $chartColor = '#4C7D71';
    }
    
    ?>
<style>
/* ===== 数据看板 v2 ===== */
.dash-wrap { margin-top: 20px; }

/* 时间筛选栏 */
.dash-filter { margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
.dash-filter-btns { background: #fff; border: 1px solid #e8e8e8; padding: 4px; border-radius: 10px; display: inline-flex; gap: 4px; }
.dash-filter-btns .layui-btn { background: transparent !important; color: #6B7280 !important; border: none; border-radius: 8px; height: 34px; line-height: 34px; padding: 0 18px; font-size: 13px; font-weight: 500; transition: all 0.25s; margin: 0 !important; }
.dash-filter-btns .layui-btn:hover { color: <?= $chartColor ?> !important; background: #f5f5f5 !important; }
.dash-filter-btns .layui-btn.active { background: <?= $chartColor ?> !important; color: #fff !important; box-shadow: 0 2px 8px <?= $chartColor ?>40; }
.dash-filter-title { font-size: 15px; font-weight: 700; color: #1f2937; display: flex; align-items: center; gap: 8px; }
.dash-filter-title i { font-size: 22px; color: <?= $chartColor ?>; }

/* === 统一 layui-card 样式（与 .index-kp 完全一致） === */
.dash-wrap .layui-card { border-radius: 6px; box-shadow: 8px 8px 20px 0 rgba(55,99,170,.1); transition: all 0.3s ease; margin-bottom: 15px; background: linear-gradient(rgba(255,255,255,.55),rgba(255,255,255,.55)), url('https://dscache.tencent-cloud.cn/upload/uploader/all-base-4d4ab1d67ce695c13f6172a90395a0e784a571c2.png') center/cover no-repeat; border: 2px solid #fff; height: 100%; }
.dash-wrap .layui-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px <?= $chartColor ?>26; border-color: <?= $chartColor ?>; }
.dash-wrap .layui-card-header { border-bottom: 1px solid #EDF2F1; padding: 0 20px; font-weight: 600; color: #374151; background: transparent; height: 50px; line-height: 50px; }
.dash-wrap .dash-stats-grid .layui-card-body { padding: 14px 20px; text-align: center; }
.dash-wrap .font-strong { font-size: 20px; font-weight: 700; color: <?= $chartColor ?>; line-height: 1.2; margin-bottom: 4px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; height: auto; font-variant-numeric: tabular-nums; }
.dash-wrap .info { margin-top: 0 !important; }
.dash-wrap .layui-card-body div.pb-3 { color: #9CA3AF; font-size: 13px; margin-top: 4px !important; }
.dash-wrap .layui-badge { background: <?= $chartColor ?> !important; color: #fff !important; border: 1px solid <?= $chartColor ?>; border-radius: 4px; height: 22px; line-height: 22px; padding: 0 8px; font-size: 13px; padding-top: 2px; margin-top: 12px; font-weight: normal; }

/* 卡片内彩色图标 */
.dash-wrap .layui-card-body { position: relative; }
.dash-wrap .stat-icon { position: absolute; top: 10px; right: 14px; width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
.dash-wrap .stat-icon.ic-sales { background: <?= $chartColor ?>15; color: <?= $chartColor ?>; }
.dash-wrap .stat-icon.ic-profit { background: #fef3c7; color: #d97706; }
.dash-wrap .stat-icon.ic-orders { background: #ede9fe; color: #7c3aed; }
.dash-wrap .stat-icon.ic-users { background: #e0f2fe; color: #0284c7; }

/* 图表卡片 body 不居中 */
.dash-wrap .dash-chart-card .layui-card-body { text-align: left; padding: 16px 20px; }

/* 布局 */
.dash-stats-grid { width: 100%; margin-bottom: 20px; }
.dash-charts { display: grid; grid-template-columns: 1fr 1fr; gap: 9px; margin-bottom: 0px; }
.dash-bottom { display: grid; grid-template-columns: 1fr 1fr; gap: 9px; }
.dash-charts .layui-card, .dash-bottom .layui-card { height: auto; }

/* 商品排行 */
.dash-rank-list { list-style: none; padding: 0; margin: 0; min-width: 0; }
.dash-rank-item { display: flex; align-items: center; padding: 12px 0; border-bottom: 1px solid #f5f5f5; gap: 12px; min-width: 0; }
.dash-rank-item:last-child { border-bottom: none; }
.dash-rank-num { width: 24px; height: 24px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0; background: #f3f4f6; color: #9ca3af; }
.dash-rank-item:nth-child(1) .dash-rank-num { background: <?= $chartColor ?>; color: #fff; }
.dash-rank-item:nth-child(2) .dash-rank-num { background: <?= $chartColor ?>99; color: #fff; }
.dash-rank-item:nth-child(3) .dash-rank-num { background: <?= $chartColor ?>55; color: #fff; }
.dash-rank-info { flex: 1 1 auto; min-width: 0; overflow: hidden; }
.dash-rank-name { display: block; max-width: 100%; font-size: 13px; color: #374151; font-weight: 500; line-height: 1.4; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.dash-rank-qty { font-size: 11px; color: #9ca3af; margin-top: 2px; line-height: 1.45; }
.dash-rank-amount { flex: 0 0 auto; max-width: 96px; font-size: 14px; font-weight: 600; color: #1f2937; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-align: right; }
.dash-rank-empty { text-align: center; padding: 40px 0; color: #d1d5db; font-size: 13px; }

/* 响应式 */
@media (max-width: 1200px) { .dash-charts, .dash-bottom { grid-template-columns: 1fr; } }
@media (max-width: 640px) {
    .dash-rank-item { align-items: flex-start; gap: 8px; padding: 10px 0; }
    .dash-rank-name { white-space: normal; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; word-break: break-all; }
    .dash-rank-qty { word-break: break-word; }
    .dash-rank-amount { max-width: 78px; font-size: 13px; line-height: 1.4; padding-top: 2px; }
}
@media (max-width: 420px) {
    .dash-rank-item { display: grid; grid-template-columns: 22px minmax(0, 1fr); grid-template-areas: "num info" ". amount"; align-items: start; }
    .dash-rank-num { grid-area: num; width: 22px; height: 22px; }
    .dash-rank-info { grid-area: info; }
    .dash-rank-amount { grid-area: amount; max-width: 100%; padding-top: 0; text-align: left; }
}

/* ===== 暗色主题 ===== */
html[data-theme="dark"] .dash-filter-btns { background: #1e1e1e; border-color: #333; }
html[data-theme="dark"] .dash-filter-btns .layui-btn { color: #aaa !important; }
html[data-theme="dark"] .dash-filter-btns .layui-btn:hover { background: #2a2a2a !important; }
html[data-theme="dark"] .dash-filter-title { color: #e0e0e0; }
html[data-theme="dark"] .dash-wrap .layui-card { background-image: none; background: #1e1e1e; border-color: #2a2a2a; box-shadow: 0 1px 4px rgba(0,0,0,0.2); }
html[data-theme="dark"] .dash-wrap .layui-card:hover { border-color: <?= $chartColor ?>60; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
html[data-theme="dark"] .dash-wrap .layui-card-header { border-bottom-color: #333; color: #e0e0e0; }
html[data-theme="dark"] .dash-wrap .layui-card-body { color: #ccc; }
html[data-theme="dark"] .dash-wrap .layui-card-body div.pb-3 { color: #888; }
html[data-theme="dark"] .dash-wrap .font-strong { color: <?= $chartColor ?>; }
html[data-theme="dark"] .dash-rank-item { border-color: #2a2a2a; }
html[data-theme="dark"] .dash-rank-name { color: #ccc; }
html[data-theme="dark"] .dash-rank-amount { color: #e0e0e0; }
html[data-theme="dark"] .dash-rank-num { background: #2a2a2a; color: #888; }
html[data-theme="dark"] .dash-rank-empty { color: #555; }
</style>
<script src="/content/plugins/adm_home/js/echarts.min.js"></script>

<div class="dash-wrap">
    <!-- 标题 + 时间筛选 -->
    <div class="dash-filter">
        <div class="dash-filter-title"><i class="ri-bar-chart-box-line"></i>数据看板</div>
        <div class="dash-filter-btns" id="dash-filter-btns">
            <button type="button" class="layui-btn active" data-type="">近7天</button>
            <button type="button" class="layui-btn" data-type="week">本周</button>
            <button type="button" class="layui-btn" data-type="month">本月</button>
            <button type="button" class="layui-btn" data-type="year">今年</button>
        </div>
    </div>

    <!-- 汇总统计卡片 - 与数据中心卡片样式一致 -->
    <div class="dash-stats-grid grid-cols-xs-2 grid-cols-sm-2 grid-cols-xl-4 grid-cols-lg-2 grid-cols-md-2 mb-3 grid-gap-10">
        <div class="layui-card">
            <div class="layui-card-header" style="display: flex; justify-content: space-between;">
                <span>总销售额</span>
                <span class="layui-badge" id="ds-period-badge1">周期</span>
            </div>
            <div class="layui-card-body">
                <div class="stat-icon ic-sales"><i class="ri-money-cny-circle-line"></i></div>
                <p class="info mt-5 font-strong" id="ds-sales">--</p>
                <div class="pb-3 mt-2" id="ds-sales-prev">上期 --</div>
            </div>
        </div>
        <div class="layui-card">
            <div class="layui-card-header" style="display: flex; justify-content: space-between;">
                <span>总利润</span>
                <span class="layui-badge" id="ds-period-badge2">周期</span>
            </div>
            <div class="layui-card-body">
                <div class="stat-icon ic-profit"><i class="ri-funds-line"></i></div>
                <p class="info mt-5 font-strong" id="ds-profit">--</p>
                <div class="pb-3 mt-2" id="ds-profit-prev">上期 --</div>
            </div>
        </div>
        <div class="layui-card">
            <div class="layui-card-header" style="display: flex; justify-content: space-between;">
                <span>订单量</span>
                <span class="layui-badge" id="ds-period-badge3">周期</span>
            </div>
            <div class="layui-card-body">
                <div class="stat-icon ic-orders"><i class="ri-file-list-3-line"></i></div>
                <p class="info mt-5 font-strong" id="ds-orders">--</p>
                <div class="pb-3 mt-2" id="ds-orders-prev">上期 --</div>
            </div>
        </div>
        <div class="layui-card">
            <div class="layui-card-header" style="display: flex; justify-content: space-between;">
                <span>下单用户</span>
                <span class="layui-badge" id="ds-period-badge4">周期</span>
            </div>
            <div class="layui-card-body">
                <div class="stat-icon ic-users"><i class="ri-user-heart-line"></i></div>
                <p class="info mt-5 font-strong" id="ds-users">--</p>
                <div class="pb-3 mt-2" id="ds-users-prev">上期 --</div>
            </div>
        </div>
    </div>

    <!-- 趋势图表 -->
    <div class="dash-charts">
        <div class="layui-card dash-chart-card">
            <div class="layui-card-header" style="display: flex; justify-content: space-between;">
                <span><i class="ri-line-chart-line" style="margin-right:4px;"></i>销售与利润趋势</span>
            </div>
            <div class="layui-card-body">
                <div id="adm-home-one" style="width:100%;height:340px;"></div>
            </div>
        </div>
        <div class="layui-card dash-chart-card">
            <div class="layui-card-header" style="display: flex; justify-content: space-between;">
                <span><i class="ri-bar-chart-grouped-line" style="margin-right:4px;"></i>订单数量与下单用户</span>
            </div>
            <div class="layui-card-body">
                <div id="adm-home-two" style="width:100%;height:340px;"></div>
            </div>
        </div>
    </div>

    <!-- 商品排行 + 支付分布 -->
    <div class="dash-bottom">
        <div class="layui-card dash-chart-card">
            <div class="layui-card-header" style="display: flex; justify-content: space-between;">
                <span><i class="ri-trophy-line" style="margin-right:4px;"></i>商品销量排行 TOP5</span>
            </div>
            <div class="layui-card-body">
                <ul class="dash-rank-list" id="dash-rank-list">
                    <li class="dash-rank-empty">加载中...</li>
                </ul>
            </div>
        </div>
        <div class="layui-card dash-chart-card">
            <div class="layui-card-header" style="display: flex; justify-content: space-between;">
                <span><i class="ri-pie-chart-line" style="margin-right:4px;"></i>支付方式分布</span>
            </div>
            <div class="layui-card-body">
                <div id="adm-home-pay" style="width:100%;height:300px;"></div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
(function(){
    var themeColor = '<?= $chartColor ?>';
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    var textColor = isDark ? '#aaa' : '#6B7280';
    var textColorStrong = isDark ? '#e0e0e0' : '#1F2937';
    var borderColor = isDark ? '#333' : '#f0f0f0';
    var splitColor = isDark ? '#2a2a2a' : '#F3F4F6';
    var tooltipBg = isDark ? 'rgba(30,30,30,0.95)' : 'rgba(255,255,255,0.96)';

    // 通用 tooltip 配置
    var tooltipBase = {
        backgroundColor: tooltipBg,
        borderColor: borderColor,
        textStyle: { color: textColorStrong, fontSize: 13 },
        extraCssText: 'box-shadow: 0 4px 16px rgba(0,0,0,0.1); border-radius: 8px; padding: 12px 16px;'
    };

    // === 图表1：销售与利润趋势 ===
    var chart1 = echarts.init(document.getElementById('adm-home-one'));
    var opt1 = {
        backgroundColor: 'transparent',
        grid: { top: 45, right: 16, bottom: 20, left: 16, containLabel: true },
        legend: { top: 0, right: 0, itemWidth: 12, itemHeight: 3, itemGap: 16, textStyle: { color: textColor, fontSize: 12 } },
        tooltip: $.extend({}, tooltipBase, { trigger: 'axis', axisPointer: { type: 'cross', crossStyle: { color: borderColor }, lineStyle: { color: borderColor } } }),
        xAxis: { type: 'category', boundaryGap: false, data: [], axisLine: { lineStyle: { color: borderColor } }, axisLabel: { color: textColor, fontSize: 11, margin: 12 }, axisTick: { show: false } },
        yAxis: { type: 'value', splitLine: { lineStyle: { color: splitColor, type: 'dashed' } }, axisLabel: { color: textColor, fontSize: 11 }, axisLine: { show: false }, axisTick: { show: false } },
        series: [{
            data: [], type: 'line', name: '销售额', smooth: true, symbol: 'emptyCircle', symbolSize: 6, showSymbol: false,
            emphasis: { focus: 'series', itemStyle: { borderWidth: 3 } },
            areaStyle: { color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{ offset: 0, color: themeColor + '30' }, { offset: 1, color: themeColor + '05' }]) },
            lineStyle: { width: 2.5, color: themeColor },
            itemStyle: { color: themeColor, borderColor: '#fff', borderWidth: 2 }
        },{
            data: [], type: 'line', name: '利润', smooth: true, symbol: 'emptyCircle', symbolSize: 6, showSymbol: false,
            emphasis: { focus: 'series', itemStyle: { borderWidth: 3 } },
            areaStyle: { color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{ offset: 0, color: 'rgba(217,119,6,0.18)' }, { offset: 1, color: 'rgba(217,119,6,0.02)' }]) },
            lineStyle: { width: 2.5, color: '#d97706' },
            itemStyle: { color: '#d97706', borderColor: '#fff', borderWidth: 2 }
        }]
    };
    chart1.setOption(opt1);

    // === 图表2：订单与用户 ===
    var chart2 = echarts.init(document.getElementById('adm-home-two'));
    var opt2 = {
        backgroundColor: 'transparent',
        grid: { top: 45, right: 16, bottom: 20, left: 16, containLabel: true },
        legend: { top: 0, right: 0, itemWidth: 12, itemHeight: 8, itemGap: 16, textStyle: { color: textColor, fontSize: 12 } },
        tooltip: $.extend({}, tooltipBase, { trigger: 'axis', axisPointer: { type: 'shadow', shadowStyle: { color: 'rgba(0,0,0,0.03)' } } }),
        xAxis: { type: 'category', data: [], axisLine: { lineStyle: { color: borderColor } }, axisLabel: { color: textColor, fontSize: 11, margin: 12 }, axisTick: { show: false } },
        yAxis: { type: 'value', splitLine: { lineStyle: { color: splitColor, type: 'dashed' } }, axisLabel: { color: textColor, fontSize: 11 }, axisLine: { show: false }, axisTick: { show: false } },
        series: [{
            data: [], type: 'bar', name: '订单数量', barMaxWidth: 24, barGap: '30%',
            itemStyle: { color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{ offset: 0, color: themeColor }, { offset: 1, color: themeColor + '88' }]), borderRadius: [6, 6, 0, 0] }
        },{
            data: [], type: 'bar', name: '下单用户', barMaxWidth: 24,
            itemStyle: { color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{ offset: 0, color: '#7c3aed' }, { offset: 1, color: '#7c3aed88' }]), borderRadius: [6, 6, 0, 0] }
        }]
    };
    chart2.setOption(opt2);

    // === 图表3：支付方式分布 ===
    var chart3 = echarts.init(document.getElementById('adm-home-pay'));
    var opt3 = {
        backgroundColor: 'transparent',
        tooltip: $.extend({}, tooltipBase, { trigger: 'item', formatter: function(p) { return '<b>' + p.name + '</b><br/>金额: ¥' + Number(p.value).toFixed(2) + '<br/>占比: ' + p.percent + '%'; } }),
        legend: { orient: 'vertical', right: 10, top: 'center', textStyle: { color: textColor, fontSize: 12 }, itemWidth: 10, itemHeight: 10 },
        series: [{
            type: 'pie', radius: ['45%', '72%'], center: ['35%', '50%'], avoidLabelOverlap: false,
            itemStyle: { borderRadius: 6, borderColor: isDark ? '#1e1e1e' : '#fff', borderWidth: 3 },
            label: { show: false },
            emphasis: { label: { show: true, fontSize: 14, fontWeight: 'bold', color: textColorStrong }, itemStyle: { shadowBlur: 10, shadowColor: 'rgba(0,0,0,0.15)' } },
            data: []
        }]
    };
    chart3.setOption(opt3);

    // 支付方式颜色映射
    var payColors = [themeColor, '#d97706', '#7c3aed', '#0284c7', '#059669', '#dc2626', '#9ca3af'];

    // 格式化数字
    function fmtNum(n) {
        if (n === undefined || n === null) return '--';
        n = Number(n);
        if (n >= 10000) return (n / 10000).toFixed(2) + '万';
        return n % 1 === 0 ? n.toLocaleString() : n.toFixed(2);
    }

    // 周期名称映射
    var periodNames = { '': '近7天', 'week': '本周', 'month': '本月', 'year': '今年' };
    var currentPeriod = '';

    // 更新汇总卡片
    function updateStats(data) {
        var s = data.summary || {};
        var p = data.prevSummary || {};

        // 更新周期徽章
        var pName = periodNames[currentPeriod] || '近7天';
        for (var b = 1; b <= 4; b++) $('#ds-period-badge' + b).text(pName);

        // 填充主要数值
        $('#ds-sales').text('¥' + fmtNum(s.totalSales));
        $('#ds-profit').text('¥' + fmtNum(s.totalProfit));
        $('#ds-orders').text(fmtNum(s.totalOrders));
        $('#ds-users').text(fmtNum(s.totalUsers));

        // 填充上期数值
        $('#ds-sales-prev').text('上期 ¥' + fmtNum(p.totalSales));
        $('#ds-profit-prev').text('上期 ¥' + fmtNum(p.totalProfit));
        $('#ds-orders-prev').text('上期 ' + fmtNum(p.totalOrders) + ' 单');
        $('#ds-users-prev').text('上期 ' + fmtNum(p.totalUsers) + ' 人');

    }

    // 更新商品排行
    function updateRank(products) {
        var $list = $('#dash-rank-list');
        if (!products || products.length === 0) {
            $list.html('<li class="dash-rank-empty"><i class="ri-inbox-line" style="font-size:32px;display:block;margin-bottom:8px;"></i>暂无数据</li>');
            return;
        }
        var html = '';
        for (var i = 0; i < products.length; i++) {
            var p = products[i];
            html += '<li class="dash-rank-item">' +
                '<span class="dash-rank-num">' + (i + 1) + '</span>' +
                '<div class="dash-rank-info"><div class="dash-rank-name" title="' + (p.goods_name || '已删除商品') + '">' + (p.goods_name || '已删除商品') + '</div>' +
                '<div class="dash-rank-qty">售出 ' + (p.total_qty || 0) + ' 件 | 剩余 <span style="color:' + (p.remaining_stock === null || p.remaining_stock === undefined ? '#9ca3af' : (p.remaining_stock <= 0 ? '#dc2626' : (p.remaining_stock <= 10 ? '#d97706' : '#059669'))) + ';font-weight:600">' + (p.remaining_stock !== null && p.remaining_stock !== undefined ? p.remaining_stock : '--') + '</span> 库存</div></div>' +
                '<div class="dash-rank-amount">¥' + Number(p.total_amount || 0).toFixed(2) + '</div>' +
                '</li>';
        }
        $list.html(html);
    }

    // 更新支付方式饼图
    function updatePayChart(payDist) {
        if (!payDist || payDist.length === 0) {
            opt3.series[0].data = [{ value: 0, name: '暂无数据', itemStyle: { color: '#e5e7eb' } }];
        } else {
            opt3.series[0].data = payDist.map(function(item, idx) {
                return { value: Number(item.total_amount) || 0, name: item.pay_name || '未知', itemStyle: { color: payColors[idx % payColors.length] } };
            });
        }
        chart3.setOption(opt3);
    }

    // 加载数据
    function loadDashData(type) {
        type = type || '';
        currentPeriod = type;
        $.get('/?plugin=adm_home&type=' + type, function(e) {
            if (!e || !e.data) return;
            var d = e.data;

            // 更新趋势图
            opt1.xAxis.data = d.oneTitle || [];
            opt1.series[0].data = (d.oneValue && d.oneValue[0]) || [];
            opt1.series[1].data = (d.oneValue && d.oneValue[1]) || [];
            chart1.setOption(opt1);

            opt2.xAxis.data = d.oneTitle || [];
            opt2.series[0].data = (d.twoValue && d.twoValue[0]) || [];
            opt2.series[1].data = (d.twoValue && d.twoValue[1]) || [];
            chart2.setOption(opt2);

            // 更新汇总卡片
            updateStats(d);
            // 更新商品排行
            updateRank(d.topProducts);
            // 更新支付分布
            updatePayChart(d.payDist);
        }, 'json');
    }

    // 初始加载
    setTimeout(function() { loadDashData(); }, 100);

    // 切换时间筛选
    $('#dash-filter-btns .layui-btn').click(function() {
        $('#dash-filter-btns .layui-btn').removeClass('active');
        $(this).addClass('active');
        loadDashData($(this).data('type'));
    });

    // 窗口自适应
    window.addEventListener('resize', function() {
        chart1.resize();
        chart2.resize();
        chart3.resize();
    });
})();
</script>

<?php
}

addAction('adm_main_content', 'adm_home');
