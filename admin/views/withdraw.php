<?php
defined('DC_ROOT') || exit('access denied!');
?>
<style>
.wdm-page-title { font-size: 18px; font-weight: 600; color: #333; line-height: 1.4; }
.wdm-stats { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 14px; margin-bottom: 15px; }
.wdm-stat-card { padding: 16px 18px; border-radius: 6px; background-image: linear-gradient(0deg, #fff, #f3f5f8);box-shadow: 8px 8px 20px 0 rgba(55, 99, 170, .1); border: 2px solid #fff; }
.wdm-stat-label { font-size: 13px; color: #666; }
.wdm-stat-value { margin-top: 8px; font-size: 24px; line-height: 1.2; font-weight: 600; color: #222; }
.wdm-panel { margin-bottom: 15px; padding: 15px 20px; border-radius: 6px;background-image: linear-gradient(0deg, #fff, #f3f5f8);border: 2px solid #fff; box-shadow: 8px 8px 20px 0 rgba(55, 99, 170, .1);}
.wdm-filter-row { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; justify-content: flex-end; }
.wdm-filter-row .layui-input-inline { margin-right: 0; }
.wdm-filter-actions { display: inline-flex; gap: 10px; }
.wdm-toolbar-btn { display: inline-flex; align-items: center; gap: 6px; }
.wdm-user-cell, .wdm-info-cell, .wdm-amount-cell, .wdm-time-cell, .wdm-remark-cell { line-height: 1.7; }
.wdm-user-name, .wdm-info-main, .wdm-amount-main, .wdm-time-main, .wdm-remark-main { color: #111827; font-weight: 600; }
.wdm-user-name { font-weight: 400; }
.wdm-info-main { font-weight: 400; }
.wdm-user-contact, .wdm-info-sub, .wdm-amount-sub, .wdm-time-sub, .wdm-remark-sub { color: #64748b; font-size: 12px; margin-top: 2px; }
.wdm-info-main.is-receiver { color: #1677ff; }
.wdm-info-main.is-method-alipay { color: #1677ff; }
.wdm-info-main.is-method-wechat { color: #52c41a; }
.wdm-info-main.is-method-qq { color: #ff4d4f; }
.wdm-info-main.is-method-bank { color: #1677ff; }
.wdm-info-sub.is-account { color: #475569; }
.wdm-open-receipt { cursor: pointer; }
.wdm-receipt-link { display: inline-flex; align-items: center; gap: 4px; color: #1677ff; font-size: 12px; cursor: pointer; white-space: nowrap; }
.wdm-receipt-link:hover { color: #0958d9; text-decoration: none; }
.wdm-receipt-link .layui-icon { font-size: 16px; }
.wdm-receipt-none { color: #ccc; font-size: 12px; white-space: nowrap; }

.wdm-amount-main { font-weight: 400; font-size: 13px; }
.wdm-time-main { font-weight: 400; font-size: 12px; }
.wdm-remark-main { font-weight: 400; font-size: 12px; }
.wdm-amount-main span.is-method-alipay { color: #1677ff; }
.wdm-amount-main span.is-method-wechat { color: #52c41a; }
.wdm-amount-main span.is-method-qq { color: #ff4d4f; }
.wdm-amount-main span.is-method-bank { color: #1677ff; }
.wdm-amount-sub.is-pending { color: #94a3b8; }
.wdm-uid-tag, .wdm-status-badge { display: inline-flex; align-items: center; justify-content: center; padding: 2px 10px; border-radius: 3px; font-size: 12px; cursor: pointer; transition: .18s ease; }
.wdm-uid-tag { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; font-size: 11px; vertical-align: middle; }
.wdm-uid-tag:hover { background: #dbeafe; color: #1d4ed8; text-decoration: none; }
.wdm-status-badge.is-pending { background: #fff7e6; color: #d48806; border: 1px solid #ffe7ba; }
.wdm-status-badge.is-pass { background: #f6ffed; color: #389e0d; border: 1px solid #d9f7be; }
.wdm-status-badge.is-reject { background: #fff1f0; color: #cf1322; border: 1px solid #ffccc7; }
.wdm-status-badge.is-unknown { background: #f5f5f5; color: #666; border: 1px solid #e5e7eb; }
.wdm-status-badge:hover { opacity: .88; }
.wdm-op { display: inline-flex; gap: 8px; align-items: center; }
.wdm-btn-outline { border: 1px solid #d9d9d9; background: #fff; color: #666; }
.wdm-btn-outline:hover { border-color: #bfbfbf; color: #333; }
.wdm-empty { padding: 50px 0; color: #94a3b8; text-align: center; }
.wdm-modal { display: flex; flex-direction: column; max-height: calc(100vh - 140px); background: #fff; }
.wdm-modal-body { flex: 1; overflow-y: auto; padding: 18px 18px 0; }
.wdm-modal-section { margin-top: 14px; padding: 14px; border-radius: 6px; background: #fff; border: 1px solid #eee; }
.wdm-modal-title { margin-bottom: 12px; font-size: 15px; font-weight: 700; color: #111827; }
.wdm-modal-section.is-receiver .wdm-modal-title { color: #1677ff; }
.wdm-modal-section.is-receipt .wdm-modal-title { color: #2563eb; }
.wdm-modal-section.is-remark .wdm-modal-title { color: #d46b08; }
.wdm-modal-section.is-process .wdm-modal-title { color: #389e0d; }
.wdm-modal-desc { color: #666; font-size: 13px; line-height: 1.8; word-break: break-all; }
.wdm-receiver-body { display: grid; grid-template-columns: auto 1fr; gap: 14px; align-items: start; }
.wdm-receiver-left .wdm-receipt-preview { width: 200px; height: 200px; }
.wdm-receiver-right .wdm-modal-list { display: grid; grid-template-columns: 1fr; gap: 10px; }
.wdm-receiver-noimg .wdm-modal-list { display: grid; grid-template-columns: 1fr; gap: 10px; }
.wdm-modal-item { padding: 0; border-radius: 6px; background: #fafafa; display: grid; grid-auto-flow: column; grid-auto-columns: minmax(0, 1fr); }
.wdm-modal-item.is-span-2 { grid-column: span 2; }
.wdm-modal-field { padding: 12px 14px; }
.wdm-modal-field + .wdm-modal-field { border-left: 1px dashed #e5e7eb; }
.wdm-modal-item span { display: block; font-size: 12px; color: #94a3b8; }
.wdm-modal-item strong { display: block; margin-top: 6px; color: #111827; font-size: 14px; line-height: 1.7; word-break: break-all; }
.wdm-modal-field.is-amount strong { color: #cf1322; }
.wdm-modal-field.is-method-alipay strong { color: #1677ff; }
.wdm-modal-field.is-method-wechat strong { color: #52c41a; }
.wdm-modal-field.is-method-qq strong { color: #ff4d4f; }
.wdm-modal-field.is-method-bank strong { color: #1677ff; }
.wdm-receipt-preview { display: inline-flex; align-items: center; justify-content: center; width: 180px; height: 180px; border-radius: 10px; overflow: hidden; background: #fff; border: 1px solid #e5e7eb; }
.wdm-receipt-preview img { width: 100%; height: 100%; object-fit: cover; display: block; }
.wdm-receipt-tip { margin-top: 10px; color: #64748b; font-size: 12px; }
.wdm-modal-form .layui-form-item:last-child { margin-bottom: 0; }
.wdm-quick-words { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
.wdm-quick-word { display: inline-flex; align-items: center; padding: 5px 10px; border-radius: 999px; border: 1px solid #dbeafe; background: #eff6ff; color: #2563eb; font-size: 12px; cursor: pointer; }
.wdm-quick-word:hover { background: #dbeafe; }
.wdm-modal-actions { flex-shrink: 0; display: flex; justify-content: flex-end; gap: 10px; padding: 14px 18px 18px; border-top: 1px solid #eee; background: #fff; }
@media (max-width: 1440px) { .wdm-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 1100px) { .wdm-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 768px) { .wdm-stats, .wdm-modal-list { grid-template-columns: 1fr; } .wdm-modal-item.is-span-2 { grid-column: span 1; } }
@media (max-width: 640px) {
    .wdm-modal-body { padding: 12px 12px 0; }
    .wdm-modal-section { padding: 10px; }
    .wdm-receiver-body { grid-template-columns: 1fr; }
    .wdm-receiver-left { text-align: center; }
    .wdm-receiver-left .wdm-receipt-preview { width: 140px; height: 140px; margin: 0 auto; }
    .wdm-modal-item { grid-auto-flow: row; grid-auto-columns: auto; }
    .wdm-modal-field + .wdm-modal-field { border-left: none; border-top: 1px dashed #e5e7eb; }
    .wdm-modal-field { padding: 10px 12px; }
    .wdm-modal-item strong { font-size: 13px; }
    .wdm-modal-form .layui-form-label { width: 90px !important; }
    .wdm-modal-form .layui-input-block { margin-left: 90px !important; }
    .wdm-modal-actions { padding: 10px 12px 14px; gap: 8px; flex-wrap: wrap; }
    .wdm-modal-actions .layui-btn { flex: 1; min-width: 0; }
}
</style>

    <div class="wdm-stats">
        <div class="wdm-stat-card"><div class="wdm-stat-label">申请总数</div><div class="wdm-stat-value" id="wdm-stat-total">0</div></div>
        <div class="wdm-stat-card"><div class="wdm-stat-label">待审核</div><div class="wdm-stat-value" id="wdm-stat-pending">0</div></div>
        <div class="wdm-stat-card"><div class="wdm-stat-label">已通过</div><div class="wdm-stat-value" id="wdm-stat-pass">0</div></div>
        <div class="wdm-stat-card"><div class="wdm-stat-label">已驳回</div><div class="wdm-stat-value" id="wdm-stat-reject">0</div></div>
        <div class="wdm-stat-card"><div class="wdm-stat-label">待处理金额</div><div class="wdm-stat-value" id="wdm-stat-pending-amount">0.00</div></div>
    </div>

        <table class="layui-hide" id="wdm-table" lay-filter="wdm-table"></table>

<script type="text/html" id="wdm-toolbar">
    <div style="display:flex;align-items:center;justify-content:center;position:relative;padding:10px 0px 15px;border-bottom:1px solid #f0f0f0;">
        <span style="position:absolute;left:0;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">提现申请</span>
    </div>
    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
        <div class="layui-btn-container">
            <button class="layui-btn layui-btn-primary layui-border-green" lay-event="refresh"><i class="ri-refresh-line"></i></button>
            <button id="toolbar-del" class="layui-btn  layui-bg-red layui-btn-disabled" lay-event="del">删除</button>
        </div>
        <form class="layui-form" lay-filter="wdm-search-form" id="wdm-search-form" style="display: flex; align-items: center; gap: 6px; margin: 0; flex-wrap: wrap;">
            <div class="layui-input-inline layui-input-wrap" style="width:100px;margin:0;"><input type="text" name="user_id" id="wdm-user-id" placeholder="用户编号" lay-affix="clear" class="layui-input" autocomplete="off"></div>
            <div class="layui-input-inline layui-input-wrap" style="width:110px;margin:0;">
                <select name="status" id="wdm-status">
                    <option value="">全部状态</option>
                    <option value="0">待审核</option>
                    <option value="1">已通过</option>
                    <option value="2">已驳回</option>
                </select>
            </div>
            <div class="layui-input-inline layui-input-wrap" style="width:110px;margin:0;">
                <select name="method" id="wdm-method">
                    <option value="">全部方式</option>
                    <option value="alipay">支付宝</option>
                    <option value="wechat">微信</option>
                    <option value="qq">QQ</option>
                    <option value="bank">银行卡</option>
                </select>
            </div>
            <div class="layui-input-inline layui-input-wrap" style="width:180px;margin:0;"><input type="text" name="keyword" id="wdm-keyword" placeholder="用户/账号/姓名/备注" lay-affix="clear" class="layui-input" autocomplete="off"></div>
            <button class="layui-btn layui-btn-sm" lay-submit lay-filter="wdm-search">搜索</button>
            <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" id="wdm-reset">重置</button>
        </form>
    </div>
</script>

<script type="text/html" id="wdm-tpl-user">
    <div class="wdm-user-cell">
        <div class="wdm-user-name">{{ d.user_display }}</div>
        <div class="wdm-user-contact"><a href="javascript:;" class="wdm-uid-tag" data-uid="{{ d.user_id }}">UID {{ d.user_id }}</a> {{# if(d.user_username){ }}{{ d.user_username }}{{# } else { }}{{ d.user_contact }}{{# } }}</div>
    </div>
</script>

<script type="text/html" id="wdm-tpl-receiver">
    <div class="wdm-info-cell">
        <div class="wdm-info-main">{{ d.realname || '-' }}</div>
        <div class="wdm-info-sub is-account">{{ d.account || '-' }}</div>
    </div>
</script>

<script type="text/html" id="wdm-tpl-receipt-img">
    {{# if(d.receipt_image_url){ }}
    <a href="javascript:;" class="wdm-open-receipt wdm-receipt-link" data-img="{{ d.receipt_image_url }}"><i class="layui-icon layui-icon-picture"></i> 查看收款码</a>
    {{# } else { }}
    <span class="wdm-receipt-none">未上传收款码</span>
    {{# } }}
</script>

<script type="text/html" id="wdm-tpl-amount">
    <div class="wdm-amount-cell">
        <div class="wdm-amount-main is-request"><span class="is-method-{{ d.method }}">{{ d.method_text }}申请 </span>¥{{ d.amount_text }}</div>
        <div class="wdm-amount-sub">手续费 ¥{{ d.service_change_text }}</div>
        {{# if(parseInt(d.status, 10) === 0){ }}
        <div class="wdm-amount-sub is-pending">预计到账 ¥{{ d.expected_actual_amount_text }}</div>
        {{# } else if(parseInt(d.status, 10) === 1){ }}
        <div class="wdm-amount-sub is-actual">实际到账 ¥{{ d.actual_amount_text !== '-' ? d.actual_amount_text : d.expected_actual_amount_text }}</div>
        {{# } else { }}
        <div class="wdm-amount-sub">已退回余额 ¥{{ d.amount_text }}</div>
        {{# } }}
    </div>
</script>

<script type="text/html" id="wdm-tpl-status">
    <span class="wdm-status-badge is-{{ d.status_class }}" data-status="{{ d.status }}">{{ d.status_text }}</span>
</script>

<script type="text/html" id="wdm-tpl-time">
    <div class="wdm-time-cell">
        <div class="wdm-time-main">申请：{{ d.create_time_text }}</div>
        <div class="wdm-time-sub">处理：{{ d.handle_time_text }}</div>
    </div>
</script>

<script type="text/html" id="wdm-tpl-remark">
    <div class="wdm-remark-cell">
        <div class="wdm-remark-main">{{ d.remark || '未填写申请备注' }}</div>
        {{# if(d.handle_remark){ }}
        <div class="wdm-remark-sub">处理：{{ d.handle_remark }}</div>
        {{# } }}
    </div>
</script>

<script type="text/html" id="wdm-tpl-operate">
    <div class="wdm-op">
        {{# if(parseInt(d.status, 10) === 0){ }}
        <a class="layui-btn  layui-bg-orange" lay-event="audit">审核</a>
        {{# } else { }}
        <a class="layui-btn  layui-btn-primary" lay-event="audit">详情</a>
        {{# } }}
    </div>
</script>

<script>
layui.use(['table', 'form'], function(){
    var table = layui.table;
    var form = layui.form;
    var $ = layui.$;
    var layer = layui.layer;
    var pageSize = parseInt(localStorage.getItem('withdraw_limit') || '10', 10);
    var quickWords = {
        finish: ['已完成处理，请注意查收', '提现审核通过，已转至指定账户', '已核实打款，如有问题请联系客服'],
        reject: ['账户信息有误，金额已退回余额，请核对后重新申请', '未能核验收款信息，金额已退回余额', '申请信息异常，金额已退回余额，请联系客服']
    };

    function escapeHtml(str) {
        return String(str || '').replace(/[&<>"']/g, function(s) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s];
        });
    }

    function toMoney(val) {
        var num = parseFloat(val || 0);
        if (isNaN(num)) {
            num = 0;
        }
        return num.toFixed(2);
    }

    function getMethodColorClass(method) {
        method = String(method || '').toLowerCase();
        if (method === 'wechat') {
            return 'is-method-wechat';
        }
        if (method === 'qq') {
            return 'is-method-qq';
        }
        if (method === 'bank') {
            return 'is-method-bank';
        }
        return 'is-method-alipay';
    }

    function openReceiptPreview(url) {
        if (!url) {
            return;
        }
        layer.open({
            type: 1,
            title: '收款码大图',
            area: ['520px', 'auto'],
            shadeClose: true,
            content: '<div style="padding:10px;text-align:center;"><img src="' + url + '" style="max-width:100%;max-height:70vh;border-radius:6px;"></div>',
            success: function(layero, index) {
                var $img = layero.find('img');
                if ($img[0] && $img[0].complete) {
                    layer.style(index, {top: Math.max(0, ($(window).height() - layero.outerHeight()) / 2) + 'px'});
                } else {
                    $img.on('load', function(){
                        layer.style(index, {top: Math.max(0, ($(window).height() - layero.outerHeight()) / 2) + 'px'});
                    });
                }
            }
        });
    }

    function collectFilters() {
        return {
            user_id: $.trim($('#wdm-user-id').val()),
            status: $('#wdm-status').val(),
            method: $('#wdm-method').val(),
            keyword: $.trim($('#wdm-keyword').val())
        };
    }

    function updateStats(data) {
        data = data || {};
        $('#wdm-stat-total').text(data.total_count || 0);
        $('#wdm-stat-pending').text(data.pending_count || 0);
        $('#wdm-stat-pass').text(data.pass_count || 0);
        $('#wdm-stat-reject').text(data.reject_count || 0);
        $('#wdm-stat-pending-amount').text(toMoney(data.pending_amount || 0));
    }

    function loadStats() {
        $.get('?action=stats', collectFilters(), function(res){
            if (res && res.code === 200) {
                updateStats(res.data || {});
            }
        }, 'json');
    }

    function reloadTable(resetPage) {
        var options = { where: collectFilters() };
        if (resetPage) {
            options.page = { curr: 1 };
        }
        table.reload('wdm-table', options);
        loadStats();
    }

    function buildQuickWords(type) {
        var words = quickWords[type] || [];
        var html = [];
        for (var i = 0; i < words.length; i++) {
            html.push('<span class="wdm-quick-word" data-text="' + escapeHtml(words[i]) + '">' + escapeHtml(words[i]) + '</span>');
        }
        return html.join('');
    }

    function openAuditModal(data) {
        var isPending = parseInt(data.status, 10) === 0;
        var defaultHandleRemark = escapeHtml('已完成处理，请注意查收！');
        var requestRemark = data.remark ? escapeHtml(data.remark) : '未填写申请备注';
        var handleRemark = data.handle_remark ? escapeHtml(data.handle_remark) : (parseInt(data.status, 10) === 1 ? defaultHandleRemark : '暂无处理说明');
        var feeAmount = toMoney(data.service_change_text || 0);
        var suggestedActualAmount = toMoney(data.expected_actual_amount_text || data.amount_text || 0);
        var finalActualAmount = (data.actual_amount_text && data.actual_amount_text !== '-') ? toMoney(data.actual_amount_text) : suggestedActualAmount;
        var settleLabel = isPending ? '预计到账' : (parseInt(data.status, 10) === 1 ? '实际到账' : '退回余额');
        var settleValue = parseInt(data.status, 10) === 2 ? '¥' + escapeHtml(data.amount_text) : '¥' + escapeHtml(finalActualAmount);
        var receiptImageHtml = data.receipt_image_url ? '<div class="wdm-receipt-preview" style="cursor:pointer;" data-img="' + escapeHtml(data.receipt_image_url) + '"><img src="' + escapeHtml(data.receipt_image_url) + '" alt="收款码"></div><div class="wdm-receipt-tip">点击图片查看大图</div>' : '<div class="wdm-modal-desc">未上传收款码图片</div>';
        var html = [
            '<div class="wdm-modal">',
                '<div class="wdm-modal-body">',
                '<div class="wdm-modal-section is-receiver">',
                    '<div class="wdm-modal-title">收款信息</div>',
                    '<div class="' + (data.receipt_image_url ? 'wdm-receiver-body' : 'wdm-receiver-noimg') + '">',
                        (data.receipt_image_url ? '<div class="wdm-receiver-left">' + receiptImageHtml + '</div>' : ''),
                        '<div class="wdm-receiver-right">',
                            '<div class="wdm-modal-list">',
                                '<div class="wdm-modal-item">',
                                    '<div class="wdm-modal-field"><span>用户UID</span><strong>' + escapeHtml(data.user_id) + '</strong></div>',
                                    '<div class="wdm-modal-field"><span>用户昵称</span><strong>' + escapeHtml(data.user_display || '-') + '</strong></div>',
                                    '<div class="wdm-modal-field"><span>登录账号</span><strong>' + escapeHtml(data.user_username || '-') + '</strong></div>',
                                '</div>',
                                '<div class="wdm-modal-item">',
                                    '<div class="wdm-modal-field is-amount"><span>申请金额</span><strong>¥' + escapeHtml(data.amount_text) + '</strong></div>',
                                    '<div class="wdm-modal-field"><span>手续费</span><strong>¥' + escapeHtml(feeAmount) + '</strong></div>',
                                    '<div class="wdm-modal-field is-amount"><span>' + settleLabel + '</span><strong>' + settleValue + '</strong></div>',
                                    '<div class="wdm-modal-field ' + getMethodColorClass(data.method) + '"><span>提现方式</span><strong>' + escapeHtml(data.method_text) + '</strong></div>',
                                '</div>',
                                '<div class="wdm-modal-item">',
                                    '<div class="wdm-modal-field"><span>收款姓名</span><strong>' + escapeHtml(data.realname || '-') + '</strong></div>',
                                    '<div class="wdm-modal-field"><span>收款账号</span><strong>' + escapeHtml(data.account || '-') + '</strong></div>',
                                    '<div class="wdm-modal-field"><span>申请时间</span><strong>' + escapeHtml(data.create_time_text || '-') + '</strong></div>',
                                    '<div class="wdm-modal-field"><span>申请备注</span><strong>' + requestRemark + '</strong></div>',
                                '</div>',
                                (isPending ? '' : '<div class="wdm-modal-item"><div class="wdm-modal-field"><span>处理时间</span><strong>' + escapeHtml(data.handle_time_text || '-') + '</strong></div></div>'),
                            '</div>',
                        '</div>',
                    '</div>',
                '</div>',
                '<div class="wdm-modal-section is-process">',
                    '<div class="wdm-modal-title">处理信息</div>',
                    (isPending ? [
                        '<div class="wdm-modal-form">',
                            '<div class="layui-form-item">',
                                '<label class="layui-form-label" style="width:110px;">实际处理金额</label>',
                                '<div class="layui-input-block" style="margin-left:110px;">',
                                    '<input type="number" id="wdm-actual-amount" class="layui-input" value="' + escapeHtml(suggestedActualAmount) + '" min="0.01" step="0.01" placeholder="请填写实际处理金额">',
                                    '<div class="wdm-amount-sub" id="wdm-actual-hint" style="margin-top:8px;">用户余额已扣 ¥' + escapeHtml(data.amount_text) + '，手续费 ¥' + escapeHtml(feeAmount) + '，建议到账 ¥' + escapeHtml(suggestedActualAmount) + '</div>',
                                '</div>',
                            '</div>',
                            '<div class="layui-form-item">',
                                '<label class="layui-form-label" style="width:110px;">处理说明</label>',
                                '<div class="layui-input-block" style="margin-left:110px;">',
                                    '<textarea id="wdm-handle-remark" class="layui-textarea" placeholder="驳回时必须请填写原因哦！"></textarea>',
                                    '<div class="wdm-quick-words">' + buildQuickWords('finish') + buildQuickWords('reject') + '</div>',
                                '</div>',
                            '</div>',
                        '</div>'
                    ].join('') : '<div class="wdm-modal-desc">' + handleRemark + '</div>'),
                '</div>',
                '</div>',
                '<div class="wdm-modal-actions">',
                    '<button type="button" class="layui-btn layui-btn-sm layui-btn-primary wdm-modal-close">关闭</button>',
                    (isPending ? '<button type="button" class="layui-btn layui-btn-sm layui-bg-red wdm-modal-reject">驳回并退回</button><button type="button" class="layui-btn layui-btn-sm layui-bg-blue wdm-modal-pass">审核通过</button>' : ''),
                '</div>',
            '</div>'
        ].join('');

        layer.open({
            type: 1,
            title: isPending ? '处理提现申请' : '提现详情',
            area: window.innerWidth <= 640 ? ['95%', 'auto'] : ['920px', 'auto'],
            shadeClose: true,
            maxWidth: 980,
            content: html,
            success: function(layero, index) {
                $(layero).on('click', '.wdm-receipt-preview[data-img]', function(){
                    openReceiptPreview($(this).data('img') || '');
                });
                $(layero).on('click', '.wdm-modal-close', function(){
                    layer.close(index);
                });
                $(layero).on('click', '.wdm-quick-word', function(){
                    $('#wdm-handle-remark').val($(this).data('text') || '');
                });
                $(layero).on('click', '.wdm-modal-pass', function(){
                    submitAction(index, data.id, 'finish');
                });
                $(layero).on('click', '.wdm-modal-reject', function(){
                    submitAction(index, data.id, 'reject');
                });
                $(layero).on('input change', '#wdm-actual-amount', function(){
                    var currentActual = $.trim($(this).val());
                    var tip = '用户余额已扣 ¥' + data.amount_text + '，手续费 ¥' + feeAmount + '，建议到账 ¥' + suggestedActualAmount;
                    if (currentActual !== '') {
                        tip += '，当前填写实际到账 ¥' + toMoney(currentActual);
                    }
                    $('#wdm-actual-hint').text(tip);
                });
            }
        });
    }

    function submitAction(layerIndex, id, type) {
        var actualAmount = $.trim($('#wdm-actual-amount').val());
        var handleRemark = $.trim($('#wdm-handle-remark').val());
        if (type === 'finish' && !actualAmount) {
            return layer.msg('请填写实际处理金额');
        }
        if (type === 'reject' && !handleRemark) {
            return layer.msg('请填写驳回说明');
        }
        layer.confirm(type === 'finish' ? '确认通过该提现申请？' : '确认驳回并退回余额？', {icon: 3, title: '确认操作'}, function(confirmIndex){
            var loadIndex = layer.load(2);
            $.ajax({
                url: '?action=cmd',
                type: 'POST',
                dataType: 'json',
                data: {
                    id: id,
                    type: type,
                    actual_amount: actualAmount,
                    handle_remark: handleRemark,
                    token: '<?= LoginAuth::genToken() ?>'
                },
                success: function(res) {
                    layer.close(loadIndex);
                    layer.close(confirmIndex);
                    if (!res || res.code !== 200) {
                        return layer.msg((res && res.msg) || '处理失败');
                    }
                    layer.close(layerIndex);
                    layer.msg(res.msg || '处理成功');
                    reloadTable(false);
                },
                error: function(xhr) {
                    layer.close(loadIndex);
                    layer.close(confirmIndex);
                    var msg = '处理失败，请稍后重试';
                    try { msg = JSON.parse(xhr.responseText).msg || msg; } catch (e) {}
                    layer.msg(msg);
                }
            });
        });
    }

    table.render({
        elem: '#wdm-table',
        id: 'wdm-table',
        url: '?action=index',
        where: collectFilters(),
        toolbar: '#wdm-toolbar',
        page: true,
        limit: pageSize,
        limits: [10, 20, 30, 50, 100],
        autoSort: false,
        lineStyle: 'height: 62px;',
        defaultToolbar: [],
        text: {none: '<div class="wdm-empty">暂无提现申请</div>'},
        cols: [[
            {type: 'checkbox', width: 50, fixed: 'left'},
            {field: 'id', title: 'ID', width: 70},
            {field: 'user_id', title: '用户', width: 170, templet: '#wdm-tpl-user'},
            {field: 'account', title: '收款信息', minWidth: 150, templet: '#wdm-tpl-receiver'},
            {field: 'receipt_image_url', title: '收款码', width: 110, align: 'center', templet: '#wdm-tpl-receipt-img'},
            {field: 'amount_text', title: '提现金额', width: 220, templet: '#wdm-tpl-amount'},
            {field: 'status_text', title: '状态', width: 90, templet: '#wdm-tpl-status', align: 'center'},
            {field: 'create_time_text', title: '时间', minWidth: 180, templet: '#wdm-tpl-time'},
            {field: 'remark', title: '备注', minWidth: 220, templet: '#wdm-tpl-remark'},
            {fixed: 'right', title: '操作', width: 110, align: 'center', templet: '#wdm-tpl-operate'}
        ]],
        done: function() {
            setTimeout(function(){
                var select = document.querySelector('.layui-laypage-limits select');
                if (select && !select.dataset.bindWithdrawLimit) {
                    select.dataset.bindWithdrawLimit = '1';
                    select.addEventListener('change', function(){
                        localStorage.setItem('withdraw_limit', this.value);
                    });
                }
            }, 0);
        }
    });

    loadStats();
    form.render('select');

    form.on('submit(wdm-search)', function(){
        reloadTable(true);
        return false;
    });

    $('#wdm-reset').on('click', function(){
        $('#wdm-search-form')[0].reset();
        form.render('select');
        reloadTable(true);
    });

    $(document).on('click', '.wdm-open-receipt[data-img]', function(){
        openReceiptPreview($(this).data('img') || '');
    });

    $(document).on('click', '.wdm-uid-tag', function(){
        $('#wdm-user-id').val($(this).data('uid') || '');
        reloadTable(true);
    });

    $(document).on('click', '.wdm-status-badge', function(){
        var status = $(this).data('status');
        if (status === '' || typeof status === 'undefined') {
            return;
        }
        $('#wdm-status').val(String(status));
        form.render('select');
        reloadTable(true);
    });

    table.on('toolbar(wdm-table)', function(obj){
        if (obj.event === 'refresh') {
            reloadTable(false);
        }
        if (obj.event === 'del') {
            var checkData = table.checkStatus('wdm-table').data;
            if (!checkData.length) {
                return layer.msg('请先勾选要删除的记录');
            }
            var ids = checkData.map(function(d){ return d.id; });
            layer.confirm('确认删除的 ' + ids.length + ' 条提现记录？删除后不可恢复！', {icon: 3, title: '确认删除'}, function(confirmIndex){
                var loadIndex = layer.load(2);
                $.ajax({
                    url: '?action=del',
                    type: 'POST',
                    dataType: 'json',
                    data: { ids: ids.join(','), token: '<?= LoginAuth::genToken() ?>' },
                    success: function(res){
                        layer.close(loadIndex);
                        layer.close(confirmIndex);
                        if (!res || res.code !== 200) {
                            return layer.msg((res && res.msg) || '删除失败');
                        }
                        layer.msg(res.msg || '删除成功');
                        reloadTable(false);
                    },
                    error: function(){
                        layer.close(loadIndex);
                        layer.close(confirmIndex);
                        layer.msg('删除失败，请稍后重试');
                    }
                });
            });
        }
    });

    table.on('tool(wdm-table)', function(obj){
        if (obj.event === 'audit') {
            openAuditModal(obj.data);
        }
    });
});
</script>


