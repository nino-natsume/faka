<?php
defined('DC_ROOT') || exit('access denied!');
$virtualCurrencyNameEsc = htmlspecialchars(getVirtualCurrencyName(), ENT_QUOTES, 'UTF-8');
?>

<style>
    .balance-log-page {
        display: flex;
        flex-direction: column;
        gap: 22px;
        padding: 8px 0 18px;
    }

    .balance-log-hero {
        padding: 24px 28px;
        border-radius: 10px;
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        box-shadow: 0 1px 18px #12345b0a;
    }


    .balance-log-hero-inner {
        display: grid;
        grid-template-columns: minmax(0,1fr) auto;
        gap: 18px;
        align-items: center;
    }

    .balance-log-title {
        margin: 0 0 10px;
        color: #0f172a;
        font-size: 22px;
        line-height: 1.2;
        font-weight: 800;
    }

    .balance-log-desc {
        max-width: 760px;
        margin: 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.9;
    }

    .balance-log-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 10px;
    }

    .balance-log-btn {
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
        cursor: pointer;
        transition: .18s ease;
    }

    .balance-log-btn:hover {
        color: #1e293b;
        text-decoration: none;
        border-color: #cbd5e1;
        box-shadow: 0 2px 8px rgba(15,23,42,.06);
    }

    .balance-log-btn.is-primary {
        background: var(--theme-primary);
        color: #fff;
        border-color: var(--theme-primary);
    }

    .balance-log-btn.is-primary:hover {
        background: var(--tp-dark);
        border-color: var(--tp-dark);
        color: #fff;
        box-shadow: 0 4px 14px rgba(var(--tp-rgb),.25);
    }

    .balance-log-metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .balance-log-metric,
    .balance-log-card {
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        border-radius: 10px;
        box-shadow: 0 1px 18px #12345b0a;
    }

    .balance-log-metric {
        padding: 22px;
    }

    .balance-log-metric-label {
        color: var(--text-sub);
        font-size: 13px;
        font-weight: 600;
    }

    .balance-log-metric-value {
        margin-top: 10px;
        color: var(--text-main);
        font-size: 26px;
        font-weight: 800;
        line-height: 1.25;
    }

    .balance-log-card {
        padding: 18px 18px 8px;
    }

    .balance-log-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 16px;
        padding: 0 6px;
    }

    .balance-log-card-title {
        margin: 0;
        color: var(--text-main);
        font-size: 18px;
        font-weight: 800;
    }

    .balance-log-card .layui-table-view {
        margin: 0;
        border-radius: 10px;
        overflow: hidden;
    }

    .balance-log-card .layui-table-header th {
        background: rgba(var(--tp-rgb),.05);
        color: var(--text-main);
        font-weight: 700;
        border-color: var(--card-border);
    }

    .balance-log-card .layui-table td,
    .balance-log-card .layui-table th {
        padding-top: 14px;
        padding-bottom: 14px;
    }

    .balance-log-card .layui-table tbody tr:hover td {
        background: rgba(var(--tp-rgb),.03);
    }

    .balance-log-card .layui-table-header { overflow-x: auto !important; scrollbar-width: none; }
    .balance-log-card .layui-table-header::-webkit-scrollbar { display: none; }
    .balance-log-card .layui-table th:first-child,
    .balance-log-card .layui-table td:first-child {
        position: sticky !important; left: 0; z-index: 2;
        background: #fff; box-shadow: 2px 0 4px rgba(0,0,0,.04);
    }
    .balance-log-card .layui-table-header th:first-child { background: rgba(var(--tp-rgb),.05); }

    .layui-badge {
        border-radius: 20px;
        font-weight: 700;
        font-size: 12px;
        padding: 6px 16px;
        height: auto;
        line-height: 1.4;
    }

    .layui-bg-blue {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }

    .layui-bg-cyan {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        border: none;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }

    .balance-log-toolbar {
        padding: 18px 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }

    .balance-log-tool-btn {
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
        transition: .18s;
    }

    .balance-log-tool-btn:hover {
        transform: translateY(-2px);
        border-color: rgba(var(--tp-rgb),.28);
        box-shadow: 0 14px 26px rgba(15,23,42,.08);
    }

    .balance-log-tool-btn.is-primary {
        background: linear-gradient(135deg,var(--theme-primary) 0%,var(--tp-light) 100%);
        border-color: transparent;
        color: #fff;
    }

    .balance-log-metric-note {
        margin-top: 6px;
        font-size: 12px;
        color: var(--text-sub);
    }

    .blog-amount-in  { font-weight: 800; color: #059669; font-size: 24px; }
    .blog-amount-out { font-weight: 800; color: #dc2626; font-size: 24px; }

    .log-type-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    .log-type-badge.is-in  { background: #f6ffed; color: #52c41a; }
    .log-type-badge.is-out { background: #fff2e8; color: #fa8c16; }

    .log-amount-pos { color: #52c41a; font-weight: 500; }
    .log-amount-neg { color: #ff4d4f; font-weight: 500; }

    .log-amount-note {
        display: block;
        margin-top: 0px;
        color: var(--text-sub);
        font-size: 11px;
    }

    .log-desc-cell { line-height: 1.65; }

    .log-desc-main {
        color: var(--text-main);
        font-size: 11px;
        font-weight: 600;
    }

    .log-desc-sub {
        margin-top: 0px;
        color: var(--text-sub);
        font-size: 11px;
    }

    .log-balance-cell { line-height: 1.65; }

    .log-balance-label {
        display: inline-block;
        min-width: 50px;
        color: var(--text-sub);
        font-size: 12px;
    }

    .log-balance-value {
        color: var(--text-main);
        font-size: 11px;
        font-weight: 600;
    }

    .log-balance-arrow { color: #9ca3af; margin: 0 6px; font-size: 11px; }

    @media (max-width: 1100px) {
        .balance-log-metrics {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .balance-log-hero {
            border-radius: 10px;
            padding: 24px 20px;
        }

        .balance-log-hero-inner {
            grid-template-columns: 1fr;
        }

        .balance-log-title {
            font-size: 26px;
        }

        .balance-log-actions {
            justify-content: flex-start;
        }

        .balance-log-metrics {
            grid-template-columns: 1fr 1fr;
        }
    }
</style>

<main class="balance-log-page">
    <section class="balance-log-hero">
        <div class="balance-log-hero-inner">
            <div>
                <h1 class="balance-log-title">收支明细</h1>
                <p class="balance-log-desc">查看每一笔余额入账或扣减的说明、变动金额和前后余额。</p>
            </div>
            <div class="balance-log-actions">
                <a href="/user/balance.php?action=credits_log" class="balance-log-btn"><i class="fa fa-diamond"></i> <?= $virtualCurrencyNameEsc ?>明细</a>
                <button type="button" class="balance-log-btn is-primary" id="balanceLogRefreshBtn"><i class="fa fa-refresh"></i> 刷新明细</button>
            </div>
        </div>
    </section>

    <section class="balance-log-metrics">
        <div class="balance-log-metric">
            <div class="balance-log-metric-label">当前余额</div>
            <div class="balance-log-metric-value">¥<?= number_format($user['money'], 2) ?></div>
            <div class="balance-log-metric-note">共 <span id="logStatTotal">-</span> 笔记录</div>
        </div>
        <div class="balance-log-metric">
            <div class="balance-log-metric-label">累计入账</div>
            <div class="balance-log-metric-value blog-amount-in" id="logStatPlusAmt">-</div>
            <div class="balance-log-metric-note">共 <span id="logStatPlus">-</span> 笔</div>
        </div>
        <div class="balance-log-metric">
            <div class="balance-log-metric-label">累计扣减</div>
            <div class="balance-log-metric-value blog-amount-out" id="logStatMinusAmt">-</div>
            <div class="balance-log-metric-note">共 <span id="logStatMinus">-</span> 笔</div>
        </div>
    </section>

    <div class="balance-log-card">
        <div class="balance-log-card-head">
            <div>
                <h2 class="balance-log-card-title">变动记录</h2>
                <p class="balance-log-metric-note">每条记录都会显示变动说明、收支方向、变动金额以及变动前后的余额。</p>
            </div>
        </div>
        <table class="layui-hide" id="balanceLogTable" lay-filter="balanceLogTable"></table>

        <script type="text/html" id="descTpl">
            <div class="log-desc-cell">
                <div class="log-desc-main">{{= d.description || '余额变动' }}</div>
                <div class="log-desc-sub">{{= d.plus == 'y' ? '本次为余额入账' : '本次为余额扣减' }}</div>
            </div>
        </script>

        <script type="text/html" id="moneyTpl">
            {{# if(d.plus == 'y'){ }}
            <span class="log-amount-pos">+¥{{= d.money }}</span>
            <span class="log-amount-note">本次入账</span>
            {{# } else { }}
            <span class="log-amount-neg">-¥{{= d.money }}</span>
            <span class="log-amount-note">本次扣减</span>
            {{# } }}
        </script>

        <script type="text/html" id="typeTpl">
            {{# if(d.plus == 'y'){ }}
            <span class="log-type-badge is-in">入账</span>
            {{# } else { }}
            <span class="log-type-badge is-out">扣减</span>
            {{# } }}
        </script>

        <script type="text/html" id="balanceTpl">
            {{# var after = (parseFloat(d.update_before) + (d.plus=='y'?1:-1)*parseFloat(d.money)).toFixed(2); }}
            <div class="log-balance-cell">
                <div><span class="log-balance-label">变动前</span><span class="log-balance-value">¥{{= d.update_before }}</span></div>
                <div><span class="log-balance-label">变动后</span><span class="log-balance-value">¥{{= after }}</span></div>
            </div>
        </script>
    </div>
</main>

<script>
    layui.use(['table', 'layer', 'jquery'], function() {
        var $ = layui.$;
        var table = layui.table;
        var tableId = 'balanceLogTableIns';

        function updateStats(res) {
            var total = res.count || 0;
            var plusCnt = res.stat_plus_cnt || 0;
            var plusAmt = parseFloat(res.stat_plus_amt) || 0;
            var minusCnt = res.stat_minus_cnt || 0;
            var minusAmt = parseFloat(res.stat_minus_amt) || 0;
            $('#logStatTotal').text(total);
            $('#logStatPlus').text(plusCnt);
            $('#logStatMinus').text(minusCnt);
            $('#logStatPlusAmt').text(plusCnt > 0 ? '¥' + plusAmt.toFixed(2) : '-');
            $('#logStatMinusAmt').text(minusCnt > 0 ? '¥' + minusAmt.toFixed(2) : '-');
        }

        table.render({
            elem: '#balanceLogTable',
            id: tableId,
            autoSort: false,
            url: '?action=index',
            limits: [10, 20, 30, 50, 100, 200, 500, 1000],
            page: true,
            lineStyle: 'height: 62px;',
            defaultToolbar: ['filter', 'exports'],
            cols: [[
                {field: 'plus', title: '收支', width: 90, align: 'center', templet: '#typeTpl'},
                {field: 'description', title: '变动说明', minWidth: 280, templet: '#descTpl'},
                {field: 'money', title: '本次金额', width: 140, templet: '#moneyTpl'},
                {field: 'update_before', title: '前后余额', width: 150, templet: '#balanceTpl'},
                {field: 'create_time', title: '记录时间', width: 170}
            ]],
            done: function(res) {
                updateStats(res);
            }
        });

        $('#balanceLogRefreshBtn').on('click', function() {
            table.reload(tableId);
        });
    });
</script>

<?php include __DIR__ . '/_pc_page_footer.php'; ?>
<script>
    $('#menu-balance-log').addClass('menu-current');
</script>
