<?php
defined('DC_ROOT') || exit('access denied!');
?>
<style>
    .station-data-page {
        display: flex;
        flex-direction: column;
        gap: 22px;
        padding: 8px 0 18px;
    }

    .station-page-hero {
        padding: 24px 28px;
        border-radius: 10px;
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        box-shadow: 0 1px 18px #12345b0a;
    }

    .station-page-hero-content {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 18px;
        align-items: center;
    }

    .station-page-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        background: rgba(var(--tp-rgb),.06);
        color: var(--theme-primary);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
    }

    .station-page-title {
        margin: 14px 0 10px;
        color: #0f172a;
        font-size: 22px;
        line-height: 1.2;
        font-weight: 800;
    }

    .station-page-desc {
        max-width: 760px;
        margin: 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.9;
    }

    .station-page-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 10px;
    }

    .station-page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-width: 110px;
        min-height: 42px;
        padding: 0 18px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #475569;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: .18s ease;
    }

    .station-page-btn:hover {
        color: #1e293b;
        text-decoration: none;
        border-color: #cbd5e1;
        box-shadow: 0 2px 8px rgba(15,23,42,.06);
    }

    .station-page-btn.is-primary {
        background: var(--theme-primary);
        border-color: var(--theme-primary);
        color: #fff;
    }

    .station-page-btn.is-primary:hover {
        background: var(--tp-dark);
        border-color: var(--tp-dark);
        color: #fff;
        box-shadow: 0 4px 14px rgba(var(--tp-rgb),.25);
    }

    .station-metrics-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .station-metric-card,
    .station-panel {
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        border-radius: 10px;
        box-shadow: 0 1px 18px #12345b0a;
    }

    .station-metric-card {
        padding: 22px;
    }

    .station-metric-label {
        color: var(--text-sub);
        font-size: 13px;
        font-weight: 600;
    }

    .station-metric-value {
        margin-top: 10px;
        color: var(--text-main);
        font-size: 26px;
        font-weight: 800;
        line-height: 1.25;
    }

    .station-metric-note {
        margin-top: 8px;
        color: var(--text-sub);
        font-size: 12px;
        line-height: 1.7;
    }

    .station-notice-panel {
        padding: 22px 24px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .station-notice-panel strong {
        display: block;
        margin-bottom: 8px;
        color: var(--text-main);
        font-size: 16px;
    }

    .station-notice-panel p {
        margin: 0;
        color: var(--text-sub);
        font-size: 13px;
        line-height: 1.85;
    }

    .station-inline-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(var(--tp-rgb), 0.08);
        color: var(--theme-primary);
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .station-filter-panel {
        padding: 20px;
    }

    .station-filter-input {
        width: 100%;
        height: 44px;
        padding: 0 14px;
        border: 1px solid var(--card-border);
        border-radius: 14px;
        background: #fff;
        color: var(--text-main);
        font-size: 14px;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .station-filter-input:focus {
        outline: none;
        border-color: rgba(var(--tp-rgb), 0.46);
        box-shadow: 0 0 0 4px rgba(var(--tp-rgb), 0.1);
    }

    .station-filter-select {
        height: 44px;
        padding: 0 14px;
        border: 1px solid var(--card-border);
        border-radius: 14px;
        background: #fff;
        color: var(--text-main);
        font-size: 14px;
        -webkit-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M2 4l4 4 4-4'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 32px;
    }

    .station-batch-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .station-toolbar-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        padding: 0 16px;
        border: 1px solid var(--card-border);
        border-radius: 14px;
        background: #fff;
        color: var(--text-main);
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .station-toolbar-btn:hover {
        transform: translateY(-2px);
        border-color: rgba(var(--tp-rgb), 0.28);
        box-shadow: 0 14px 26px rgba(15, 23, 42, 0.08);
    }

    .station-toolbar-btn.is-primary {
        background: linear-gradient(135deg, var(--theme-primary) 0%, var(--tp-light) 100%);
        border-color: transparent;
        color: #fff;
    }

    .station-toolbar-btn.is-success {
        color: #117a4d;
    }

    .station-toolbar-btn.is-danger {
        color: #d03c4f;
    }

    .station-toolbar-btn.is-disabled,
    .station-toolbar-btn[disabled] {
        opacity: 0.45;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .station-data-panel {
        padding: 18px 18px 8px;
    }

    .station-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 16px;
        padding: 0 6px;
    }

    .station-panel-title {
        margin: 0;
        color: var(--text-main);
        font-size: 18px;
        font-weight: 800;
    }

    .station-panel-desc {
        margin: 8px 0 0;
        color: var(--text-sub);
        font-size: 13px;
        line-height: 1.8;
    }

    .station-table-wrap .layui-table-view {
        margin: 0;
        border-radius: 10px;
        overflow: hidden;
        border-color: var(--card-border);
    }

    .station-table-wrap .layui-table-header th {
        background: rgba(var(--tp-rgb), 0.06);
        color: var(--text-main);
        font-weight: 700;
        border-color: var(--card-border);
    }

    .station-table-wrap .layui-table-body tr:hover td {
        background: rgba(var(--tp-rgb), 0.03);
    }

    .station-table-wrap .layui-table-header { overflow-x: auto !important; scrollbar-width: none; }
    .station-table-wrap .layui-table-header::-webkit-scrollbar { display: none; }
    .station-table-wrap .layui-table th:last-child,
    .station-table-wrap .layui-table td:last-child {
        position: sticky !important; right: 0; z-index: 2;
        background: #fff; box-shadow: -2px 0 4px rgba(0,0,0,.04);
    }
    .station-table-wrap .layui-table-header th:last-child { background: rgba(var(--tp-rgb),.06); }

    .station-empty {
        padding: 36px 20px;
        text-align: center;
        color: var(--text-sub);
        font-size: 13px;
    }

    .goods-cover-cell {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        object-fit: cover;
        background: #f0f2f5;
        border: 1px solid var(--card-border, #e5e7eb);
    }

    .goods-cover-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 8px;
        background: #f0f2f5;
        border: 1px solid var(--card-border, #e5e7eb);
        color: #c0c4cc;
        font-size: 18px;
    }

    .goods-name-cell {
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding: 4px 0;
        overflow: hidden;
    }

    .goods-name-title {
        color: var(--text-main);
        font-size: 12px;
        font-weight: 600;
        line-height: 1.7;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .goods-name-sub {
        color: var(--text-sub);
        font-size: 12px;
        line-height: 1.7;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .goods-price-cell {
        font-size: 13px;
        font-weight: 700;
        color: var(--text-main);
    }

    .goods-profit-cell {
        font-size: 13px;
        font-weight: 700;
        color: #059669;
    }

    .goods-premium-badge,
    .goods-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0px 13px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
    }

    .goods-premium-badge {
        background: rgba(255, 170, 0, 0.12);
        color: #d97706;
    }

    .goods-status-badge.is-show {
        background: rgba(16, 185, 129, 0.12);
        color: #059669;
    }

    .goods-status-badge.is-hide {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
    }

    .station-operate-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 34px;
        padding: 0 14px;
        border-radius: 5px;
        border: 1px solid rgba(var(--tp-rgb), 0.18);
        color: var(--theme-primary);
        background: rgba(var(--tp-rgb), 0.06);
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }

    .station-mobile-list {
        display: none;
        gap: 14px;
        margin-top: 2px;
    }

    .station-mobile-card {
        padding: 18px;
        border: 1px solid var(--card-border);
        border-radius: 10px;
        background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(247,250,255,0.96));
    }

    .station-mobile-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }

    .station-mobile-card-title {
        margin: 0;
        color: var(--text-main);
        font-size: 15px;
        font-weight: 800;
        line-height: 1.7;
    }

    .station-mobile-card-sub {
        margin-top: 6px;
        color: var(--text-sub);
        font-size: 12px;
        line-height: 1.7;
    }

    .station-mobile-meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 14px;
    }

    .station-mobile-meta-item {
        padding: 12px 14px;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(var(--tp-rgb), 0.08);
    }

    .station-mobile-meta-label {
        color: var(--text-sub);
        font-size: 11px;
        font-weight: 600;
    }

    .station-mobile-meta-value {
        margin-top: 6px;
        color: var(--text-main);
        font-size: 13px;
        font-weight: 700;
        line-height: 1.7;
    }

    .station-mobile-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .station-mobile-edit {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0 14px;
        height: 38px;
        border-radius: 12px;
        border: 1px solid rgba(var(--tp-rgb), 0.16);
        background: rgba(var(--tp-rgb), 0.06);
        color: var(--theme-primary);
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
    }

    @media (max-width: 1100px) {
        .station-metrics-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .station-page-hero-content {
            grid-template-columns: 1fr;
        }

        .station-page-actions {
            justify-content: flex-start;
        }
    }

    @media (max-width: 768px) {
        .station-page-hero {
            border-radius: 10px;
            padding: 24px 20px;
        }

        .station-page-title {
            font-size: 26px;
        }

        .station-page-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
        }

        .station-page-btn {
            width: 100%;
            min-width: 0;
        }

        #stationGoodsSearch {
            flex-direction: column;
        }

        #stationGoodsSearch .station-filter-input,
        #stationGoodsSearch .station-filter-select,
        #stationGoodsSearch .station-toolbar-btn {
            width: 100%;
            min-width: 0;
        }

        .station-panel-head {
            flex-direction: column;
            align-items: flex-start;
        }

        .station-batch-actions {
            width: 100%;
        }

        .station-batch-actions .station-toolbar-btn {
            flex: 1;
            min-width: 0;
        }

        .station-table-wrap {
            display: none;
        }

        .station-mobile-list {
            display: grid;
        }
    }

    @media (max-width: 560px) {
        .station-metrics-grid,
        .station-mobile-meta,
        .station-page-actions {
            grid-template-columns: 1fr;
        }

        .station-notice-panel {
            flex-direction: column;
        }
    }
</style>

<main class="station-data-page">
    <section class="station-page-hero">
        <div class="station-page-hero-content">
            <div>
                <h1 class="station-page-title">商品管理</h1>
                <p class="station-page-desc">管理商品的显示状态、自定义名称和加价比例。</p>
            </div>
            <div class="station-page-actions">
                <a href="?action=setting" class="station-page-btn"><i class="fa fa-cog"></i> 店铺配置</a>
                <a href="?action=order" class="station-page-btn"><i class="fa fa-file-text-o"></i> 分店订单</a>
            </div>
        </div>
    </section>

    <section class="station-metrics-grid">
        <div class="station-metric-card">
            <div class="station-metric-label">商品总数</div>
            <div class="station-metric-value" id="goodsStatTotal">-</div>
        </div>
        <div class="station-metric-card">
            <div class="station-metric-label">总已上架商品</div>
            <div class="station-metric-value" id="goodsStatVisible">-</div>
        </div>
        <div class="station-metric-card">
            <div class="station-metric-label">总已下架的商品</div>
            <div class="station-metric-value" id="goodsStatHidden">-</div>
        </div>
        <div class="station-metric-card">
            <div class="station-metric-label">总商品平均加价比例</div>
            <div class="station-metric-value" id="goodsStatPremium">-</div>
        </div>
    </section>


    <section class="station-panel station-filter-panel">
        <form class="layui-form" id="stationGoodsSearch" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
            <input type="text" name="title" class="layui-input" placeholder="搜索商品名称" style="flex:2;min-width:160px;">
            <select name="status" class="station-filter-select" style="flex:1;min-width:120px;">
                <option value="">全部状态</option>
                <option value="y">已上架</option>
                <option value="n">已下架</option>
            </select>
            <button type="submit" class="station-toolbar-btn is-primary" lay-submit lay-filter="goodsSearchSubmit" style="white-space:nowrap;"><i class="fa fa-search"></i> 搜索</button>
            <button type="button" class="station-toolbar-btn" id="goodsSearchReset" style="white-space:nowrap;"><i class="fa fa-repeat"></i> 重置</button>
        </form>
    </section>

    <section class="station-panel station-data-panel">
        <div class="station-panel-head">
            <div>
                <h2 class="station-panel-title">商品列表</h2>
                <p class="station-panel-desc">查看商品状态、加价比例和自定义名称。</p>
            </div>
            <div class="station-batch-actions">
                <button type="button" class="station-toolbar-btn station-batch-btn is-success is-disabled" id="goodsShowSelected" disabled><i class="fa fa-eye"></i> 批量上架</button>
                <button type="button" class="station-toolbar-btn station-batch-btn is-danger is-disabled" id="goodsHideSelected" disabled><i class="fa fa-eye-slash"></i> 批量下架</button>
                <button type="button" class="station-toolbar-btn is-primary" id="goodsPremiumBtn"><i class="fa fa-percent"></i> 一键调价</button>
            </div>
        </div>
        <div class="station-table-wrap">
            <table class="layui-hide" id="index" lay-filter="index"></table>
        </div>
        <div class="station-mobile-list" id="goodsMobileList"></div>
    </section>
</main>

<script type="text/html" id="goodsCoverTpl">
    <img src="{{ d.cover || '<?= DC_URL ?>admin/views/images/null.png' }}" class="goods-cover-cell" alt="" onerror="this.onerror=null;this.src='<?= DC_URL ?>admin/views/images/null.png'">
</script>
<script type="text/html" id="goodsNameTpl">
    <div class="goods-name-cell">
        <div class="goods-name-title">{{ d.title }}</div>
        <div class="goods-name-sub">自定义名称：{{ d.custom_name ? d.custom_name : '未设置' }}</div>
    </div>
</script>
<script type="text/html" id="goodsCostTpl">
    <span class="goods-price-cell">&yen;{{ d.cost_yuan }}</span>
</script>
<script type="text/html" id="goodsSellTpl">
    <span class="goods-price-cell">&yen;{{ d.sell_yuan }}</span>
</script>
<script type="text/html" id="goodsProfitTpl">
    <span class="goods-profit-cell">+&yen;{{ d.profit_yuan }}</span>
</script>
<script type="text/html" id="goodsPremiumTpl">
    <span class="goods-premium-badge">{{ d.premium == undefined || d.premium == 'undefined' ? '10%' : d.premium + '%' }}</span>
</script>
<script type="text/html" id="goodsStatusTpl">
    <span class="goods-status-badge {{ d.is_show == 'y' ? 'is-show' : 'is-hide' }}">{{ d.is_show == 'y' ? '显示中' : '已隐藏' }}</span>
</script>
<script type="text/html" id="goodsSwitchTpl">
    <input type="checkbox" value="{{= d.id }}" title="上架" lay-skin="switch" lay-filter="goodsSwitch" {{= d.is_show == 'y' ? 'checked' : '' }}>
</script>
<script type="text/html" id="goodsOperateTpl">
    <button type="button" class="station-operate-btn" lay-event="edit">编辑</button>
</script>

<script>
    layui.use(['table', 'form', 'layer'], function() {
        var table = layui.table;
        var form = layui.form;
        var layer = layui.layer;
        var tableId = 'stationMasterGoodsTable';

        function safeText(value) {
            return $('<div>').text(value == null || value === '' ? '' : String(value)).html();
        }

        function getPremiumValue(value) {
            if (value === undefined || value === null || value === '' || value === 'undefined') {
                return 10;
            }
            var num = parseFloat(value);
            return isNaN(num) ? 10 : num;
        }

        function updateBatchButtons() {
            var selected = table.checkStatus(tableId).data || [];
            var disabled = selected.length === 0;
            $('#goodsShowSelected, #goodsHideSelected').prop('disabled', disabled).toggleClass('is-disabled', disabled);
        }

        function openPremiumDialog() {
            var isMobile = window.innerWidth < 768;
            layer.open({
                id: 'station-master-goods-premium',
                title: '一键调价',
                type: 2,
                area: isMobile ? ['98%', 'auto'] : ['700px', 'auto'],
                skin: 'layui-layer-molv',
                content: '?action=master_goods_premium',
                fixed: false,
                maxmin: true,
                shadeClose: true,
                success: function(layero, index, that){
                    layer.iframeAuto(index);
                    that.offset();
                }
            });
        }

        function openEditDialog(goodsId) {
            var isMobile = window.innerWidth < 768;
            layer.open({
                id: 'station-master-goods-edit',
                title: '编辑商品',
                type: 2,
                area: isMobile ? ['98%', 'auto'] : ['700px', 'auto'],
                skin: 'layui-layer-molv',
                content: '?action=master_goods_edit&goods_id=' + goodsId,
                fixed: false,
                maxmin: true,
                shadeClose: true,
                success: function(layero, index, that){
                    layer.iframeAuto(index);
                    that.offset();
                }
            });
        }

        function renderMobileList(list) {
            var $list = $('#goodsMobileList');
            if (!list.length) {
                $list.html('<div class="station-empty">当前没有可管理的主站商品</div>');
                return;
            }
            var html = list.map(function(item) {
                var isShow = item.is_show === 'y';
                var premium = getPremiumValue(item.premium);
                return '' +
                    '<article class="station-mobile-card">' +
                        '<div class="station-mobile-card-head">' +
                            '<img src="' + (item.cover || '<?= DC_URL ?>admin/views/images/null.png') + '" class="goods-cover-cell" alt="" onerror="this.onerror=null;this.src=\'<?= DC_URL ?>admin/views/images/null.png\'">' +
                            '<div style="flex:1;min-width:0;">' +
                                '<h3 class="station-mobile-card-title">' + safeText(item.title || '未命名商品') + '</h3>' +
                                '<div class="station-mobile-card-sub">自定义名称：' + safeText(item.custom_name || '未设置，默认继承主站名称') + '</div>' +
                            '</div>' +
                            '<span class="goods-status-badge ' + (isShow ? 'is-show' : 'is-hide') + '">' + (isShow ? '显示中' : '已隐藏') + '</span>' +
                        '</div>' +
                        '<div class="station-mobile-meta">' +
                            '<div class="station-mobile-meta-item">' +
                                '<div class="station-mobile-meta-label">我的成本</div>' +
                                '<div class="station-mobile-meta-value">&yen;' + safeText(item.cost_yuan) + '</div>' +
                            '</div>' +
                            '<div class="station-mobile-meta-item">' +
                                '<div class="station-mobile-meta-label">前台售价</div>' +
                                '<div class="station-mobile-meta-value">&yen;' + safeText(item.sell_yuan) + '</div>' +
                            '</div>' +
                            '<div class="station-mobile-meta-item">' +
                                '<div class="station-mobile-meta-label">每单利润</div>' +
                                '<div class="station-mobile-meta-value" style="color:#059669">+&yen;' + safeText(item.profit_yuan) + '</div>' +
                            '</div>' +
                            '<div class="station-mobile-meta-item">' +
                                '<div class="station-mobile-meta-label">加价 / 销量 / 库存</div>' +
                                '<div class="station-mobile-meta-value">' + safeText(premium) + '% / ' + safeText(item.sales || 0) + ' / ' + safeText(item.stock || 0) + '</div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="station-mobile-actions">' +
                            '<input type="checkbox" value="' + safeText(item.id) + '" title="显示" lay-skin="switch" lay-filter="goodsSwitch" ' + (isShow ? 'checked' : '') + '>' +
                            '<button type="button" class="station-mobile-edit" data-action="edit" data-id="' + safeText(item.id) + '"><i class="fa fa-pencil"></i> 编辑</button>' +
                        '</div>' +
                    '</article>';
            }).join('');
            $list.html(html);
            form.render('checkbox');
        }

        function updateStats() {
            $.ajax({
                url: '?action=master_goods_stats',
                dataType: 'json',
                success: function(res) {
                    if (!res || res.code !== 0) return;
                    $('#goodsStatTotal').text(res.visible + res.hidden);
                    $('#goodsStatVisible').text(res.visible);
                    $('#goodsStatHidden').text(res.hidden);
                    $('#goodsStatPremium').text(res.avg_premium + '%');
                }
            });
        }

        function batchToggle(url, text) {
            var selected = table.checkStatus(tableId).data || [];
            if (!selected.length) {
                layer.msg('请先勾选要操作的商品');
                return;
            }
            var ids = $.map(selected, function(item) { return item.id; }).join(',');
            layer.confirm('确定要' + text + '选中的商品吗？', {
                btn: ['确认', '取消'],
                icon: 3,
                title: '温馨提示'
            }, function(index) {
                layer.close(index);
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data: { ids: ids, token: '<?= LoginAuth::genToken() ?>' },
                    success: function(e) {
                        if (e.code == 400) {
                            return layer.msg(e.msg);
                        }
                        layer.msg(text === '显示' ? '已设为显示' : '已设为隐藏');
                        table.reload(tableId);
                    },
                    error: function(err) {
                        layer.msg(err.responseJSON && err.responseJSON.msg ? err.responseJSON.msg : '请求失败，请重试');
                    }
                });
            });
        }

        window.table = table.render({
            elem: '#index',
            id: tableId,
            autoSort: false,
            url: '?action=master_goods_index',
            limits: [10, 20, 30, 50, 100, 200, 500, 1000],
            page: true,
            lineStyle: 'height: 56px;',
            defaultToolbar: ['filter', 'exports'],
            cols: [[
                {type: 'checkbox', width: 52},
                {field: 'cover', title: '商品图', width: 75, align: 'center', templet: '#goodsCoverTpl'},
                {field: 'title', title: '商品信息', minWidth: 200, templet: '#goodsNameTpl'},
                {field: 'cost_yuan', title: '我的成本', width: 90, align: 'center', templet: '#goodsCostTpl'},
                {field: 'sell_yuan', title: '前台售价', width: 90, align: 'center', templet: '#goodsSellTpl'},
                {field: 'profit_yuan', title: '每单利润', width: 90, align: 'center', templet: '#goodsProfitTpl'},
                {field: 'sales', title: '销量', width: 75, align: 'center', sort: true},
                {field: 'stock', title: '库存', width: 75, align: 'center', sort: true},
                {field: 'premium', title: '加价', width: 90, align: 'center', templet: '#goodsPremiumTpl'},
                {field: 'is_show', title: '上下架', width: 90, align: 'center', templet: '#goodsSwitchTpl'},
                {title: '操作', width: 85, align: 'center', templet: '#goodsOperateTpl'}
            ]],
            done: function(res) {
                updateStats();
                renderMobileList(res.data || []);
                updateBatchButtons();
            },
            error: function(res, msg) {
                console.log(res, msg);
            }
        });

        form.on('switch(goodsSwitch)', function(obj) {
            var is_show = obj.elem.checked ? 'y' : 'n';
            var id = this.value;
            var loadSwitch = layer.load(2);
            $.ajax({
                url: '?action=master_goods_switch',
                type: 'POST',
                dataType: 'json',
                data: { id: id, is_show: is_show, token: '<?= LoginAuth::genToken() ?>' },
                success: function(e) {
                    if (e.code == 400) {
                        layer.msg(e.msg);
                        table.reload(tableId);
                    } else {
                        layer.msg('商品状态已更新');
                        table.reload(tableId);
                    }
                },
                error: function(err) {
                    layer.msg(err.responseJSON && err.responseJSON.msg ? err.responseJSON.msg : '请求失败，请重试');
                    table.reload(tableId);
                },
                complete: function() {
                    layer.close(loadSwitch);
                }
            });
        });

        table.on('tool(index)', function(obj) {
            if (obj.event === 'edit') {
                openEditDialog(obj.data.id);
            }
        });

        table.on('checkbox(index)', function() {
            updateBatchButtons();
        });

        form.on('submit(goodsSearchSubmit)', function(data) {
            table.reload(tableId, {
                page: {curr: 1},
                where: data.field
            });
            return false;
        });

        $('#goodsSearchReset').on('click', function() {
            document.getElementById('stationGoodsSearch').reset();
            table.reload(tableId, {
                page: {curr: 1},
                where: { title: '', status: '' }
            });
        });

        $('#goodsRefreshBtn').on('click', function() {
            table.reload(tableId);
        });

        $('#goodsPremiumBtn, #goodsOpenPremium').on('click', function() {
            openPremiumDialog();
        });

        $('#goodsShowSelected').on('click', function() {
            if ($(this).prop('disabled')) return;
            batchToggle('?action=master_goods_show', '显示');
        });

        $('#goodsHideSelected').on('click', function() {
            if ($(this).prop('disabled')) return;
            batchToggle('?action=master_goods_hide', '隐藏');
        });

        $('#goodsMobileList').on('click', '[data-action="edit"]', function() {
            openEditDialog($(this).data('id'));
        });
    });
</script>

<script>
    $('#menu-station').addClass('open');
    $('#menu-station > ul').css('display', 'block');
    $('#menu-station > a > i.nav_right').attr('class', 'fa fa-angle-down nav_right');
    $('#menu-station-master_goods').addClass('menu-current');
</script>
<?php include __DIR__ . '/../_pc_page_footer.php'; ?>
