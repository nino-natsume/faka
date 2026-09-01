<?php
defined('DC_ROOT') || exit('access denied!');
$virtualCurrencyName = getVirtualCurrencyName();
$virtualCurrencyNameEsc = htmlspecialchars($virtualCurrencyName, ENT_QUOTES, 'UTF-8');
?>

<style>
    .credits-log-page {
        display: flex;
        flex-direction: column;
        gap: 22px;
        padding: 8px 0 18px;
    }

    .credits-log-page-header { display: flex; align-items: center; gap: 14px; }
    .credits-log-back-btn { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 12px; border: 1.5px solid var(--card-border, #e2e8f0); background: var(--card-bg, #fff); color: var(--text-sub, #64748b); font-size: 16px; cursor: pointer; transition: .18s; text-decoration: none; flex-shrink: 0; }
    .credits-log-back-btn:hover { color: #7c3aed; border-color: #ddd6fe; background: #f5f3ff; text-decoration: none; }
    .credits-log-page-title { margin: 0; font-size: 22px; font-weight: 800; color: var(--text-main, #1e293b); }
    .credits-log-page-desc { margin: 2px 0 0; font-size: 13px; color: var(--text-sub, #64748b); }

    .credits-log-metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .credits-log-metric {
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        box-shadow: 0 1px 18px #12345b0a;
        border-radius: 10px;
        padding: 22px;
    }

    .credits-log-metric-label {
        color: var(--text-sub);
        font-size: 13px;
        font-weight: 600;
    }

    .credits-log-metric-value {
        margin-top: 10px;
        color: var(--text-main);
        font-size: 26px;
        font-weight: 800;
        line-height: 1.25;
    }

    .credits-log-metric-note {
        margin-top: 6px;
        font-size: 12px;
        color: var(--text-sub);
    }

    .credits-log-card {
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        box-shadow: 0 1px 18px #12345b0a;
        border-radius: 10px;
        padding: 18px 18px 8px;
    }

    .credits-log-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 16px;
        padding: 0 6px;
    }

    .credits-log-card-title {
        margin: 0;
        color: var(--text-main);
        font-size: 18px;
        font-weight: 800;
    }

    .credits-log-card .layui-table-view {
        margin: 0;
        border-radius: 10px;
        overflow: hidden;
        border-color: var(--card-border);
    }

    .credits-log-card .layui-table-header th {
        background: rgba(124,58,237,.05);
        color: var(--text-main);
        font-weight: 700;
        border-color: var(--card-border);
    }

    .credits-log-card .layui-table td,
    .credits-log-card .layui-table th {
        padding-top: 14px;
        padding-bottom: 14px;
    }

    .credits-log-card .layui-table tbody tr:hover td {
        background: rgba(124,58,237,.03);
    }

    .credits-amt-in  { font-weight: 800; color: #059669; font-size: 24px; }
    .credits-amt-out { font-weight: 800; color: #dc2626; font-size: 24px; }

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
        margin-top: 0;
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
        margin-top: 0;
        color: var(--text-sub);
        font-size: 11px;
    }

    @media (max-width: 768px) {
        .credits-log-metrics {
            grid-template-columns: 1fr 1fr;
        }
    }
</style>

<main class="credits-log-page">
    <div class="credits-log-page-header">
        <a href="/user/balance.php?action=balance_log" class="credits-log-back-btn"><i class="fa fa-arrow-left"></i></a>
        <div>
            <h1 class="credits-log-page-title"><?= $virtualCurrencyNameEsc ?>明细</h1>
            <p class="credits-log-page-desc">查看每一笔<?= $virtualCurrencyNameEsc ?>获得或扣减的说明、变动数量和记录时间</p>
        </div>
    </div>

    <section class="credits-log-metrics">
        <div class="credits-log-metric">
            <div class="credits-log-metric-label"><i class="fa fa-diamond" style="margin-right:4px;"></i>当前<?= $virtualCurrencyNameEsc ?></div>
            <div class="credits-log-metric-value"><?= (int)($user['credits'] ?? 0) ?></div>
            <div class="credits-log-metric-note">共 <span id="creditsStatTotal">-</span> 笔记录</div>
        </div>
        <div class="credits-log-metric">
            <div class="credits-log-metric-label">累计获得</div>
            <div class="credits-log-metric-value credits-amt-in" id="creditsStatIncAmt">-</div>
            <div class="credits-log-metric-note">共 <span id="creditsStatInc">-</span> 笔</div>
        </div>
        <div class="credits-log-metric">
            <div class="credits-log-metric-label">累计扣减</div>
            <div class="credits-log-metric-value credits-amt-out" id="creditsStatDecAmt">-</div>
            <div class="credits-log-metric-note">共 <span id="creditsStatDec">-</span> 笔</div>
        </div>
    </section>

    <div class="credits-log-card">
        <div class="credits-log-card-head">
            <div>
                <h2 class="credits-log-card-title"><i class="fa fa-diamond" style="margin-right:6px;color:#a78bfa;"></i><?= $virtualCurrencyNameEsc ?>变动记录</h2>
                <p class="credits-log-metric-note">每条记录都会显示<?= $virtualCurrencyNameEsc ?>变动说明、收支方向和变动数量。</p>
            </div>
        </div>
        <table class="layui-hide" id="creditsLogTable" lay-filter="creditsLogTable"></table>

        <script type="text/html" id="creditsDescTpl">
            <div class="log-desc-cell">
                <div class="log-desc-main">{{= d.content || '<?= $virtualCurrencyNameEsc ?>变动' }}</div>
                <div class="log-desc-sub">{{= d.plus == 'y' ? '本次为<?= $virtualCurrencyNameEsc ?>获得' : '本次为<?= $virtualCurrencyNameEsc ?>扣减' }}</div>
            </div>
        </script>

        <script type="text/html" id="creditsAmountTpl">
            {{# if(d.plus == 'y'){ }}
            <span class="log-amount-pos">+{{= d.abs_amount }}</span>
            <span class="log-amount-note">本次获得</span>
            {{# } else { }}
            <span class="log-amount-neg">-{{= d.abs_amount }}</span>
            <span class="log-amount-note">本次扣减</span>
            {{# } }}
        </script>

        <script type="text/html" id="creditsTypeTpl">
            {{# if(d.plus == 'y'){ }}
            <span class="log-type-badge is-in">获得</span>
            {{# } else { }}
            <span class="log-type-badge is-out">扣减</span>
            {{# } }}
        </script>
    </div>
</main>

<script>
    layui.use(['table', 'layer', 'jquery'], function() {
        var $ = layui.$;
        var table = layui.table;
        var creditsTableId = 'creditsLogTableIns';

        function updateCreditsStats(res) {
            var total = res.count || 0;
            var incCnt = res.stat_inc_cnt || 0;
            var incAmt = parseInt(res.stat_inc_amt) || 0;
            var decCnt = res.stat_dec_cnt || 0;
            var decAmt = parseInt(res.stat_dec_amt) || 0;
            $('#creditsStatTotal').text(total);
            $('#creditsStatInc').text(incCnt);
            $('#creditsStatDec').text(decCnt);
            $('#creditsStatIncAmt').text(incCnt > 0 ? incAmt : '-');
            $('#creditsStatDecAmt').text(decCnt > 0 ? decAmt : '-');
        }

        table.render({
            elem: '#creditsLogTable',
            id: creditsTableId,
            autoSort: false,
            url: '?action=credits_log_list',
            limits: [10, 20, 30, 50, 100],
            page: true,
            lineStyle: 'height: 62px;',
            defaultToolbar: ['filter', 'exports'],
            cols: [[
                {field: 'plus', title: '收支', width: 90, align: 'center', templet: '#creditsTypeTpl'},
                {field: 'content', title: '变动说明', minWidth: 280, templet: '#creditsDescTpl'},
                {field: 'abs_amount', title: '变动数量', width: 140, templet: '#creditsAmountTpl'},
                {field: 'create_time_text', title: '记录时间', width: 170}
            ]],
            done: function(res) {
                updateCreditsStats(res);
            }
        });

        $('#creditsLogRefreshBtn').on('click', function() {
            table.reload(creditsTableId);
        });
    });
</script>

<?php include __DIR__ . '/_pc_page_footer.php'; ?>
<script>
    $('#menu-balance-log').addClass('menu-current');
</script>
