<?php
defined('DC_ROOT') || exit('access denied!');
?>
<style>
    .station-order-page {
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

    .station-panel,
    .station-metric-card {
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        border-radius: 10px;
        box-shadow: 0 1px 18px #12345b0a;
    }

    .station-filter-panel {
        padding: 20px;
    }

    .station-filter-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .station-filter-item {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .station-filter-label {
        color: var(--text-main);
        font-size: 13px;
        font-weight: 700;
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

    .station-filter-actions {
        display: flex;
        gap: 10px;
        align-items: flex-end;
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

    .station-metrics-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
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

    .order-cell-stack {
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding: 4px 0;
    }

    .order-cell-title {
        color: var(--text-main);
        font-size: 12px;
        font-weight: 600;
        line-height: 1.7;
    }

    .order-cell-sub {
        color: var(--text-sub);
        font-size: 11px;
        line-height: 1.7;
    }

    .order-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 78px;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }

    .order-badge.is-success {
        background: rgba(16, 185, 129, 0.12);
        color: #059669;
    }

    .order-badge.is-warning {
        background: rgba(245, 158, 11, 0.14);
        color: #d97706;
    }

    .order-badge.is-info {
        background: rgba(var(--tp-rgb), 0.1);
        color: var(--theme-primary);
    }

    .order-badge.is-danger {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
    }

    .order-badge.is-neutral {
        background: rgba(156, 163, 175, 0.12);
        color: #6b7280;
    }

    .order-amount {
        color: #d9485f;
        font-size: 12px;
        font-weight: 800;
    }

    .order-commission {
        color: #059669;
        font-size: 12px;
        font-weight: 800;
    }

    .order-commission-zero {
        color: #9ca3af;
        font-size: 12px;
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
        background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(247,248,255,0.96));
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
        word-break: break-all;
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
        background: rgba(255, 255, 255, 0.92);
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

    @media (max-width: 1100px) {
        .station-page-hero-content,
        .station-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .station-page-actions {
            justify-content: flex-start;
        }

        .station-metrics-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .station-page-hero {
            border-radius: 10px;
            padding: 24px 20px;
        }

        .station-page-hero-content,
        .station-filter-grid,
        .station-page-actions {
            grid-template-columns: 1fr;
        }

        .station-page-title {
            font-size: 26px;
        }

        .station-page-actions {
            display: grid;
            width: 100%;
        }

        .station-page-btn,
        .station-filter-actions .station-toolbar-btn {
            width: 100%;
        }

        .station-filter-actions {
            flex-direction: column;
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
        .station-mobile-meta {
            grid-template-columns: 1fr;
        }

        .station-panel-head {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<main class="station-order-page">
    <section class="station-page-hero">
        <div class="station-page-hero-content">
            <div>
                <h1 class="station-page-title">分店订单</h1>
                <p class="station-page-desc">查看订单信息、支付状态和筛选结果。</p>
            </div>
            <div class="station-page-actions">
                <a href="?action=master_goods" class="station-page-btn"><i class="fa fa-cubes"></i> 商品管理</a>
                <button type="button" class="station-page-btn is-primary" id="orderRefreshHero"><i class="fa fa-refresh"></i> 刷新订单</button>
            </div>
        </div>
    </section>

    <section class="station-metrics-grid">
        <div class="station-metric-card">
            <div class="station-metric-label">全部订单</div>
            <div class="station-metric-value" id="orderStatTotal">-</div>
        </div>
        <div class="station-metric-card">
            <div class="station-metric-label">订单总额</div>
            <div class="station-metric-value" id="orderStatAmount">-</div>
        </div>
        <div class="station-metric-card">
            <div class="station-metric-label">已支付订单</div>
            <div class="station-metric-value" id="orderStatPaid">-</div>
        </div>
        <div class="station-metric-card">
            <div class="station-metric-label">累计佣金</div>
            <div class="station-metric-value" id="orderStatComm" style="color:#059669">-</div>
        </div>
    </section>
    <section class="station-panel station-filter-panel">
        <form class="layui-form" id="stationOrderSearch" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
            <input type="text" name="out_trade_no" class="layui-input" placeholder="订单号" style="flex:1;min-width:140px;">
            <input type="text" name="goods_title" class="layui-input" placeholder="商品名称" style="flex:1;min-width:140px;">
            <input type="text" name="email_username" class="layui-input" placeholder="用户关键词" style="flex:1;min-width:140px;">
            <button type="submit" class="station-toolbar-btn is-primary" lay-submit lay-filter="orderSearchSubmit" style="white-space:nowrap;"><i class="fa fa-search"></i> 搜索</button>
            <button type="button" class="station-toolbar-btn" id="orderSearchReset" style="white-space:nowrap;"><i class="fa fa-repeat"></i> 重置</button>
        </form>
    </section>


    <section class="station-panel station-data-panel">
        <div class="station-panel-head">
            <div>
                <h2 class="station-panel-title">订单列表</h2>
                <p class="station-panel-desc">支持按订单号、商品、用户关键词进行筛选。</p>
            </div>
        </div>
        <div class="station-table-wrap">
            <table class="layui-hide" id="index" lay-filter="index"></table>
        </div>
        <div class="station-mobile-list" id="orderMobileList"></div>
    </section>
</main>

<script type="text/html" id="orderNoTpl">
    <div class="order-cell-stack">
        <div class="order-cell-title">{{ d.out_trade_no }}</div>
        <div class="order-cell-sub">支付时间：{{ d.pay_time ? d.pay_time : '暂未支付' }}</div>
    </div>
</script>
<script type="text/html" id="goodsTpl">
    <div class="order-cell-stack">
        <div class="order-cell-title">{{ d.list[0].title }}</div>
        <div class="order-cell-sub">规格：{{ d.list[0].attr_spec ? d.list[0].attr_spec : '默认规格' }}</div>
        {{# if(d.list.length > 1){ }}
        <div class="order-cell-sub">另含 {{ d.list.length - 1 }} 项商品</div>
        {{# } }}
    </div>
</script>
<script type="text/html" id="quantityTpl">
    <div class="order-cell-title">{{ d.list[0].quantity }}</div>
</script>
<script type="text/html" id="amountTpl">
    <span class="order-amount">¥{{ d.amount }}</span>
</script>
<script type="text/html" id="commissionTpl">
    {{# if(parseFloat(d.commission) > 0){ }}
    <span class="order-commission">+¥{{ d.commission }}</span>
    {{# } else { }}
    <span class="order-commission-zero">-</span>
    {{# } }}
</script>
<script type="text/html" id="userTpl">
    <div class="order-cell-stack">
        <div class="order-cell-title">{{ d.user_nickname ? d.user_nickname : '未命名用户' }}</div>
        {{# if(d.user_id == 0){ }}
        <div class="order-cell-sub">游客身份</div>
        {{# } }}
        {{# if(d.user_email != ''){ }}
        <div class="order-cell-sub">{{ d.user_email }}</div>
        {{# } }}
        {{# if(d.user_tel != ''){ }}
        <div class="order-cell-sub">{{ d.user_tel }}</div>
        {{# } }}
    </div>
</script>
<script type="text/html" id="statusTpl">
    {{# var _s = d.status_text || ''; var _c = 'is-neutral';
       if(_s === '已完成') _c = 'is-success';
       else if(_s === '待发货' || _s === '部分发货') _c = 'is-info';
       else if(_s === '未支付') _c = 'is-warning';
       else if(_s === '已取消' || _s === '退款中') _c = 'is-danger';
    }}
    <span class="order-badge {{ _c }}">{{ _s }}</span>
</script>
<script type="text/html" id="paymentTpl">
    <span class="order-badge is-neutral">{{ d.payment ? d.payment : '未支付' }}</span>
</script>

<script>
    layui.use(['table', 'form', 'layer'], function() {
        var table = layui.table;
        var form = layui.form;
        var layer = layui.layer;
        var tableId = 'stationOrderTable';

        function safeText(value) {
            return $('<div>').text(value == null || value === '' ? '' : String(value)).html();
        }

        function parseAmount(value) {
            var num = parseFloat(String(value || '0').replace(/,/g, ''));
            return isNaN(num) ? 0 : num;
        }

        function getFirstGoods(item) {
            if (!item || !item.list || !item.list.length) {
                return {title: '未知商品', attr_spec: '', quantity: 0};
            }
            return item.list[0];
        }

        function updateStats(res) {
            if (!res) return;
            $('#orderStatTotal').text(res.stat_total || 0);
            $('#orderStatAmount').text(res.stat_amount ? '¥' + res.stat_amount : '-');
            $('#orderStatPaid').text(res.stat_paid || 0);
            var comm = parseFloat(res.stat_commission || '0');
            $('#orderStatComm').text(comm > 0 ? '¥' + res.stat_commission : '-');
        }

        function renderMobileList(list) {
            var $list = $('#orderMobileList');
            if (!list.length) {
                $list.html('<div class="station-empty">当前筛选条件下暂无订单数据</div>');
                return;
            }
            var html = list.map(function(item) {
                var goods = getFirstGoods(item);
                var statusSuccess = item.status_text && (item.status_text.indexOf('已') !== -1 || item.status_text.indexOf('成') !== -1);
                return '' +
                    '<article class="station-mobile-card">' +
                        '<div class="station-mobile-card-head">' +
                            '<div>' +
                                '<h3 class="station-mobile-card-title">' + safeText(goods.title || '未知商品') + '</h3>' +
                                '<div class="station-mobile-card-sub">订单号：' + safeText(item.out_trade_no || '') + '</div>' +
                                '<div class="station-mobile-card-sub">规格：' + safeText(goods.attr_spec || '默认规格') + '</div>' +
                            '</div>' +
                            '<span class="order-badge ' + (statusSuccess ? 'is-success' : 'is-warning') + '">' + safeText(item.status_text || '处理中') + '</span>' +
                        '</div>' +
                        '<div class="station-mobile-meta">' +
                            '<div class="station-mobile-meta-item">' +
                                '<div class="station-mobile-meta-label">订单金额</div>' +
                                '<div class="station-mobile-meta-value">¥' + safeText(item.amount || '0.00') + '</div>' +
                            '</div>' +
                            '<div class="station-mobile-meta-item">' +
                                '<div class="station-mobile-meta-label">商品数量</div>' +
                                '<div class="station-mobile-meta-value">' + safeText(goods.quantity || 0) + '</div>' +
                            '</div>' +
                            '<div class="station-mobile-meta-item">' +
                                '<div class="station-mobile-meta-label">分佣</div>' +
                                '<div class="station-mobile-meta-value" style="color:' + (parseFloat(item.commission)>0?'#059669':'#9ca3af') + '">' + (parseFloat(item.commission)>0 ? '+¥'+safeText(item.commission) : '-') + '</div>' +
                            '</div>' +
                            '<div class="station-mobile-meta-item">' +
                                '<div class="station-mobile-meta-label">用户昵称</div>' +
                                '<div class="station-mobile-meta-value">' + safeText(item.user_nickname || '未命名用户') + (String(item.user_id) === '0' ? ' / 游客' : '') + '</div>' +
                            '</div>' +
                            '<div class="station-mobile-meta-item">' +
                                '<div class="station-mobile-meta-label">支付方式</div>' +
                                '<div class="station-mobile-meta-value">' + safeText(item.payment || '未支付') + '</div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="station-mobile-card-sub">支付时间：' + safeText(item.pay_time || '暂未支付') + '</div>' +
                    '</article>';
            }).join('');
            $list.html(html);
        }

        window.table = table.render({
            elem: '#index',
            id: tableId,
            autoSort: false,
            url: '?action=order_index',
            limits: [10, 20, 30, 50, 100, 200, 500, 1000],
            page: true,
            lineStyle: 'height: 76px;',
            defaultToolbar: ['filter', 'exports'],
            cols: [[
                {field: 'out_trade_no', title: '订单信息', minWidth: 165, templet: '#orderNoTpl'},
                {field: 'goods', title: '商品信息', minWidth: 250, templet: '#goodsTpl'},
                {field: 'quantity', title: '数量', width: 76, align: 'center', templet: '#quantityTpl'},
                {field: 'amount', title: '订单金额', width: 100, align: 'center', templet: '#amountTpl'},
                {field: 'commission', title: '分佣', width: 100, align: 'center', templet: '#commissionTpl'},
                {field: 'user_email', title: '用户昵称', minWidth: 180, templet: '#userTpl'},
                {field: 'status_text', title: '订单状态', width: 110, align: 'center', templet: '#statusTpl'}
            ]],
            done: function(res) {
                updateStats(res);
                renderMobileList(res.data || []);
            },
            error: function(res, msg) {
                console.log(res, msg);
            }
        });

        form.on('submit(orderSearchSubmit)', function(data) {
            table.reload(tableId, {
                page: {curr: 1},
                where: data.field
            });
            return false;
        });

        $('#orderSearchReset').on('click', function() {
            document.getElementById('stationOrderSearch').reset();
            table.reload(tableId, {
                page: {curr: 1},
                where: {
                    out_trade_no: '',
                    goods_title: '',
                    email_username: ''
                }
            });
        });

        $('#orderRefreshHero').on('click', function() {
            table.reload(tableId);
        });
    });
</script>

<script>
    $('#menu-station').addClass('open');
    $('#menu-station > ul').css('display', 'block');
    $('#menu-station > a > i.nav_right').attr('class', 'fa fa-angle-down nav_right');
    $('#menu-station-order').addClass('menu-current');
</script>
<?php include __DIR__ . '/../_pc_page_footer.php'; ?>
