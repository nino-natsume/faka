<?php
defined('DC_ROOT') || exit('access denied!');
?>
<style>
    .mwithdraw-page { padding: 14px 14px calc(24px + env(safe-area-inset-bottom)); background: #f5f7fb; min-height: 100%; }
    .mwithdraw-summary { background: linear-gradient(180deg, #2f63d6 0%, #2b58c8 100%); color: #fff; border-radius: 18px; padding: 18px 16px; box-shadow: 0 12px 28px rgba(47, 99, 214, 0.18); }
    .mwithdraw-summary strong { display: block; font-size: 24px; font-weight: 700; margin-bottom: 8px; }
    .mwithdraw-summary span { display: block; font-size: 12px; opacity: 0.78; line-height: 1.8; }
    .mwithdraw-link { display: inline-flex; align-items: center; justify-content: center; margin-top: 12px; color: #fff; text-decoration: none; font-size: 13px; font-weight: 600; }
    .mwithdraw-list { display: flex; flex-direction: column; gap: 12px; margin-top: 14px; }
    .mwithdraw-item, .mwithdraw-empty { background: linear-gradient(0deg, #fff, #f3f5f8); border: 2px solid #fff; border-radius: 10px; box-shadow: var(--shadow-primary); }
    .mwithdraw-item { padding: 14px; transition: transform 0.15s; }
    .mwithdraw-item:active { transform: scale(0.985); }
    .mwithdraw-empty { padding: 60px 16px; text-align: center; color: #bbb; font-size: 13px; }
    .mwithdraw-empty svg { display: block; width: 140px; height: auto; margin: 0 auto 10px; }
    .mwithdraw-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 10px; }
    .mwithdraw-amount { font-size: 20px; font-weight: 700; color: #f04452; }
    .mwithdraw-status { display: inline-flex; align-items: center; justify-content: center; padding: 0 10px; height: 24px; border-radius: 999px; background: #f3f8ff; color: #2f75ff; font-size: 12px; font-weight: 600; }
    .mwithdraw-meta { color: #7b8699; font-size: 12px; line-height: 1.9; }
    .mwithdraw-more { width: 100%; height: 42px; border: 0; border-radius: 12px; background: #fff; color: #4c5a70; font-size: 14px; font-weight: 600; box-shadow: 0 8px 24px rgba(31,52,88,0.06); margin-top: 12px; }
    .mwithdraw-intro { margin-top: 14px; padding: 12px 14px; border-radius: 10px; background: linear-gradient(0deg, #fff, #f3f5f8); border: 2px solid #fff; color: #7b8699; font-size: 12px; line-height: 1.8; box-shadow: var(--shadow-primary); }
</style>
<div class='mwithdraw-page'>
    <div class='mwithdraw-summary'>
        <strong>提现记录</strong>
        <span>查看每一笔提现的申请金额、手续费、到账金额或退回结果。如需再次申请提现，可返回钱包页继续操作。</span>
        <a class='mwithdraw-link' href='/user/balance.php'>返回我的钱包</a>
    </div>
    <div class='mwithdraw-intro'>申请金额就是本次实际扣减的余额金额；手续费只影响到账金额。若提现被驳回，申请金额会原路退回到余额。</div>
    <div class='mwithdraw-list' id='withdraw-mobile-list'></div>
    <button type='button' class='mwithdraw-more' id='withdraw-mobile-more'>加载更多</button>
</div>
<template id="dcEmptyIllust"><?php include __DIR__ . '/_svg_empty.php'; ?></template>
<script>
layui.use(['layer'], function(){
    var $ = layui.$;
    var layer = layui.layer;
    var page = 1;
    var limit = 10;
    var loading = false;
    var finished = false;
    var _emptySvg = (document.getElementById('dcEmptyIllust') || {}).innerHTML || '';

    function escapeHtml(str) {
        return $('<div/>').text(str == null ? '' : String(str)).html();
    }

    function getWithdrawSettleInfo(item) {
        var status = parseInt(item.status, 10);
        var amountText = item.amount_text || item.amount || '0.00';
        var expectedText = item.expected_actual_amount_text || item.expected_actual_amount || item.actual_amount_text || amountText;
        var actualText = (item.actual_amount_text && item.actual_amount_text !== '-') ? item.actual_amount_text : expectedText;
        if (status === 1) {
            return {label: '实际到账', amount: actualText};
        }
        if (status === 2) {
            return {label: '已退回余额', amount: amountText};
        }
        return {label: '预计到账', amount: expectedText};
    }

    function getWithdrawHandleText(item) {
        var status = parseInt(item.status, 10);
        if (status === 1) {
            return item.handle_remark || '已完成处理，请注意查收！';
        }
        if (status === 2) {
            return item.handle_remark || '该申请已驳回，申请金额已退回余额。';
        }
        return '等待审核中，用户余额已按申请金额全额扣减';
    }

    function renderList(list, append) {
        var html = '';
        if (!list.length && !append) {
            $('#withdraw-mobile-list').html('<div class="mwithdraw-empty">' + _emptySvg + '暂无提现记录</div>');
            return;
        }
        $.each(list, function(_, item){
            var statusText = item.status_text || (item.status == 1 ? '已通过' : (item.status == 2 ? '已驳回' : '待审核'));
            var settleInfo = getWithdrawSettleInfo(item);
            var handleText = getWithdrawHandleText(item);
            html += '<div class="mwithdraw-item">'
                + '<div class="mwithdraw-top">'
                + '<div class="mwithdraw-amount">￥' + escapeHtml(item.amount_text || item.amount || '0.00') + '</div>'
                + '<div class="mwithdraw-status">' + escapeHtml(statusText) + '</div>'
                + '</div>'
                + '<div class="mwithdraw-meta">'
                + '余额扣减：¥' + escapeHtml(item.amount_text || item.amount || '0.00') + '<br>'
                + '提现方式：' + escapeHtml(item.method_text || item.method || '--') + '<br>'
                + '手续费：¥' + escapeHtml(item.service_change_text || item.service_change || '0.00') + '<br>'
                + escapeHtml(settleInfo.label) + '：¥' + escapeHtml(settleInfo.amount || '0.00') + '<br>'
                + '账户信息：' + escapeHtml(item.account || '--') + '<br>'
                + '真实姓名：' + escapeHtml(item.realname || '--') + '<br>'
                + '备注说明：' + escapeHtml(item.remark || '无') + '<br>'
                + '处理说明：' + escapeHtml(handleText) + '<br>'
                + '申请时间：' + escapeHtml(item.create_time_text || item.create_time || '')
                + (((item.handle_time_text || item.handle_time) && (item.handle_time_text || item.handle_time) !== '-') ? '<br>处理时间：' + escapeHtml(item.handle_time_text || item.handle_time) : '')
                + '</div>'
                + '</div>';
        });
        if (append) {
            $('#withdraw-mobile-list').append(html);
        } else {
            $('#withdraw-mobile-list').html(html);
        }
    }

    function loadList() {
        if (loading || finished) {
            return;
        }
        loading = true;
        var idx = layer.load(2, {shade: 0.06});
        $.getJSON('?action=withdraw_list&page=' + page + '&limit=' + limit, function(res){
            if (res.code === 0) {
                var list = res.data || [];
                if (page === 1 && !list.length) {
                    $('#withdraw-mobile-more').hide();
                } else {
                    $('#withdraw-mobile-more').show();
                }
                renderList(list, page > 1);
                if (list.length < limit) {
                    finished = true;
                    $('#withdraw-mobile-more').text('没有更多了').prop('disabled', true);
                }
                page++;
            } else {
                layer.msg(res.msg || '加载失败');
            }
        }).fail(function(){
            layer.msg('加载失败');
        }).always(function(){
            loading = false;
            layer.close(idx);
        });
    }

    $('#withdraw-mobile-more').on('click', loadList);
    loadList();
});
</script>
