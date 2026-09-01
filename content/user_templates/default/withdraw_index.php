<?php

defined('DC_ROOT') || exit('access denied!');

?>

<style>

    .withdraw-page {

        display: flex;

        flex-direction: column;

        gap: 22px;

        padding: 8px 0 18px;

    }



    .withdraw-hero {

        padding: 24px 28px;

        border-radius: 10px;

        background: var(--pc-card-bg);

        border: 2px solid #fff;

        box-shadow: 0 1px 18px #12345b0a;

    }



    .withdraw-hero-inner {

        display: grid;

        grid-template-columns: minmax(0,1fr) auto;

        gap: 18px;

        align-items: center;

    }



    .withdraw-eyebrow {

        display: inline-flex;

        align-items: center;

        gap: 8px;

        padding: 7px 12px;

        border-radius: 999px;

        background: rgba(255,255,255,.14);

        font-size: 12px;

        font-weight: 700;

        letter-spacing: .08em;

    }



    .withdraw-title {

        margin: 0 0 10px;

        color: #0f172a;

        font-size: 22px;

        line-height: 1.2;

        font-weight: 800;

    }
 
    .withdraw-desc {

        max-width: 760px;

        margin: 0;

        color: #64748b;

        font-size: 14px;

        line-height: 1.9;

    }
 
    .withdraw-actions {

        display: flex;

        flex-wrap: wrap;

        justify-content: flex-end;

        gap: 10px;

    }
 
    .withdraw-btn {

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

    .withdraw-btn:hover {

        color: #1e293b;

        text-decoration: none;

        border-color: #cbd5e1;

        box-shadow: 0 2px 8px rgba(15,23,42,.06);

    }

    .withdraw-btn.is-primary {

        background: var(--theme-primary);

        border-color: var(--theme-primary);

        color: #fff;

    }

    .withdraw-btn.is-primary:hover {

        background: var(--tp-dark);

        border-color: var(--tp-dark);

        color: #fff;

        box-shadow: 0 4px 14px rgba(var(--tp-rgb),.25);

    }
 
    .withdraw-card {
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        border-radius: 10px;
        box-shadow: 0 1px 18px #12345b0a;
    }
 
    .withdraw-data {
        padding: 18px 18px 8px;
    }
 
    .withdraw-data-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 16px;
        padding: 0 6px;
    }
 
    .withdraw-data-title {
        margin: 0;
        color: var(--text-main);
        font-size: 18px;
        font-weight: 800;
    }
 
    .withdraw-data-desc {
        margin: 8px 0 0;
        color: var(--text-sub);
        font-size: 12px;
        line-height: 1.8;
    }
 
    .withdraw-table-wrap .layui-table-view {
        margin: 0;
        border-radius: 10px;
        overflow: hidden;
    }

    .withdraw-table-wrap .layui-table-header th {
        background: rgba(79,70,229,.05);
        color: var(--text-main);
        font-weight: 700;
        border-color: var(--card-border);
    }

    .withdraw-table-wrap .layui-table td,
    .withdraw-table-wrap .layui-table th {
        padding-top: 14px;
        padding-bottom: 14px;
    }
    .withdraw-table-wrap .layui-table-cell {
        height: auto !important;
        overflow: visible !important;
        text-overflow: unset !important;
        white-space: normal !important;
    }
    .withdraw-table-wrap .layui-table-body tr:hover td {
        background: rgba(79,70,229,.03);
    }

    .withdraw-table-wrap .layui-table-fixed-r .layui-table-header th { 
        background: rgba(79,70,229,.05); 
        color: var(--text-main); 
        font-weight: 700; 
        border-color: var(--card-border); 
    }
    .withdraw-table-wrap .layui-table-fixed-r { 
        box-shadow: -3px 0 8px rgba(0,0,0,.06); 
    }

    .withdraw-status { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; }
    .withdraw-status.is-pending { background: #fff7e6; color: #faad14; }
    .withdraw-status.is-success { background: #f6ffed; color: #52c41a; }
    .withdraw-status.is-reject  { background: #fff1f0; color: #ff4d4f; }

    /* ... */
    .withdraw-method { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; }
    .withdraw-method.is-alipay { background: #e6f4ff; color: #1677ff; }
    .withdraw-method.is-wechat { background: #f6ffed; color: #07c160; }
    .withdraw-method.is-qq     { background: #fff1f0; color: #e03a3a; }
    .withdraw-method.is-bank   { background: #f0f5ff; color: var(--tp-dark); }

    .withdraw-amount-cell { color: var(--text-main); font-size: 13px; line-height: 1.7; }
    .withdraw-amount-cell .amount-main { color: #ff4d4f; font-weight: 700; }
    .withdraw-amount-cell .amount-sub { display: block; font-size: 12px; font-weight: 400; color: var(--text-sub); margin-top: 2px; }

    .withdraw-account-cell { font-size: 13px; line-height: 1.5; }
    .withdraw-account-cell .acc-name { font-weight: 500; color: var(--text-main); }
    .withdraw-account-cell .acc-info { color: var(--text-sub); font-size: 12px; }

    .withdraw-time-cell { font-size: 13px; color: var(--text-main); }

    .withdraw-handle-info { font-size: 13px; line-height: 1.6; color: var(--text-main); }
    .withdraw-handle-info .handle-remark { color: var(--text-main); }
    .withdraw-handle-info .handle-tip { color: var(--text-sub); font-size: 12px; margin-bottom: 2px; }
    .withdraw-handle-time { font-size: 12px; color: var(--text-sub); }

    @media (max-width: 1100px) {
        .withdraw-hero-inner {
            grid-template-columns: 1fr;
        }
    }
</style>

<main class="withdraw-page">
    <section class="withdraw-hero">
        <div class="withdraw-hero-inner">
            <div>
                <h1 class="withdraw-title">提现记录</h1>
                <p class="withdraw-desc">查看每一笔提现的申请金额、手续费、到账金额或退回结果。</p>
            </div>
            <div class="withdraw-actions">
                <a href="/user/balance.php" class="withdraw-btn"><i class="ri-wallet-3-line"></i> 我的钱包</a>
                <button type="button" class="withdraw-btn is-primary" id="withdrawRefreshHero"><i class="fa fa-refresh"></i> 刷新记录</button>
            </div>
        </div>
    </section>

    <section class="withdraw-card withdraw-data">
        <div class="withdraw-data-head">
            <div>
                <h2 class="withdraw-data-title">申请明细</h2>
                <p class="withdraw-data-desc">每条记录都会显示申请金额、手续费、预计/实际到账金额、收款账户和处理说明。</p>
            </div>
        </div>
        <div class="withdraw-table-wrap">
            <table class="layui-hide" id="index" lay-filter="index"></table>
        </div>
    </section>
</main>

<script type="text/html" id="withdrawStatusTpl">
    {{# if(d.status == 0){ }}<span class="withdraw-status is-pending">待审核</span>{{# } }}
    {{# if(d.status == 1){ }}<span class="withdraw-status is-success">已通过</span>{{# } }}
    {{# if(d.status == 2){ }}<span class="withdraw-status is-reject">已驳回</span>{{# } }}
</script>

<script type="text/html" id="withdrawMethodTpl">
    {{# var cls = 'is-' + (d.method || '').toLowerCase(); }}
    <span class="withdraw-method {{= cls }}">{{= d.method_text || d.method || '-' }}</span>
</script>

<script type="text/html" id="withdrawAmountTpl">
    {{#
        var amountText = d.amount_text || d.amount || '0.00';
        var feeText = d.service_change_text || d.service_change || '0.00';
        var expectedText = d.expected_actual_amount_text || d.expected_actual_amount || d.actual_amount_text || amountText;
        var actualText = (d.actual_amount_text && d.actual_amount_text !== '-') ? d.actual_amount_text : expectedText;
        var settleLabel = '预计到账';
        var settleAmount = expectedText;
        if (parseInt(d.status, 10) === 1) {
            settleLabel = '实际到账';
            settleAmount = actualText;
        } else if (parseInt(d.status, 10) === 2) {
            settleLabel = '已退回余额';
            settleAmount = amountText;
        }
    }}
    <div class="withdraw-amount-cell">
        <div class="amount-main">申请 ¥{{= amountText }}</div>
        <div class="amount-sub">手续费 ¥{{= feeText }}</div>
        <div class="amount-sub">{{= settleLabel }} ¥{{= settleAmount }}</div>
    </div>
</script>

<script type="text/html" id="withdrawAccountTpl">
    <div class="withdraw-account-cell">
        <div class="acc-name">{{= d.realname || '-' }}</div>
        <div class="acc-info">到账账号：{{= d.account || '-' }}</div>
    </div>
</script>

<script type="text/html" id="withdrawRemarkTpl">
    <div class="withdraw-time-cell">{{= d.remark || '未填写申请备注' }}</div>
</script>

<script type="text/html" id="withdrawTimeTpl">
    <div class="withdraw-time-cell">{{= d.create_time_text || d.create_time || '-' }}</div>
</script>

<script type="text/html" id="withdrawHandleTpl">
    {{# if(d.status == 0){ }}
    <div class="withdraw-handle-info">
        <div class="handle-tip">等待审核中</div>
        <div class="withdraw-handle-time">用户余额已按申请金额全额扣减</div>
    </div>
    {{# } else { }}
    <div class="withdraw-handle-info">
        <div class="handle-tip">{{= d.status == 1 ? '处理结果：已打款' : '处理结果：已驳回并退回余额' }}</div>
        <div class="handle-remark">{{= d.handle_remark || (d.status == 1 ? '已完成处理，请注意查收！' : '该申请已驳回，申请金额已退回余额。') }}</div>
        {{# if((d.handle_time_text || d.handle_time) && (d.handle_time_text || d.handle_time) !== '-'){ }}<div class="withdraw-handle-time">处理时间：{{= d.handle_time_text || d.handle_time }}</div>{{# } }}
    </div>
    {{# } }}
</script>

<script>
    layui.use(['table', 'layer'], function() {
        var table = layui.table;
        var layer = layui.layer;
        var tableId = 'withdrawTable';

        function syncFixedRowHeights(tableView) {
            var $view = tableView || $('.withdraw-table-wrap .layui-table-view');
            var $mainRows = $view.find('.layui-table-body.layui-table-main tr');
            var $fixedR = $view.find('.layui-table-fixed-r .layui-table-body tr');
            if ($mainRows.length && $fixedR.length) {
                $mainRows.each(function(i) {
                    var h = $(this).outerHeight();
                    $fixedR.eq(i).css('height', h + 'px');
                });
            }
        }

        window.table = table.render({
            elem: '#index',
            id: tableId,
            autoSort: false,
            url: '?action=withdraw_list',
            limits: [10,20,30,50,100,200,500,1000],
            page: true,
            defaultToolbar: ['filter', 'exports'],
            cols: [[
                {field:'account', title:'到账账户', minWidth:180, templet:'#withdrawAccountTpl'},
                {field:'amount', title:'金额明细', width:130, templet:'#withdrawAmountTpl'},
                {field:'method_text', title:'提现方式', width:110, align:'center', templet:'#withdrawMethodTpl'},
                {field:'create_time_text', title:'申请时间', width:170, templet:'#withdrawTimeTpl'},
                {field:'remark', title:'申请备注', minWidth:150, templet:'#withdrawRemarkTpl'},
                {field:'handle_remark', title:'处理结果', minWidth:240, templet:'#withdrawHandleTpl'},
                {field:'status', title:'进度', width:100, align:'center', fixed:'right', templet:'#withdrawStatusTpl'}
            ]],
            done: function(res, curr, count) {
                var $view = this.elem.next('.layui-table-view');
                setTimeout(function(){ syncFixedRowHeights($view); }, 50);
            },
            error: function(res, msg) {
                console.log(res, msg);
            }
        });

        $('#withdrawRefreshBtn, #withdrawRefreshHero').on('click', function() {
            table.reload(tableId);
        });
    });

    $('#menu-withdraw').addClass('menu-current');
</script>
<?php include __DIR__ . '/_pc_page_footer.php'; ?>