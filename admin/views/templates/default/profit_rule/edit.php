<?php defined('DC_ROOT') || exit('access denied!'); ?>
<style>
    body { overflow: hidden; background: #fff; }
    .pr-wrap { padding: 20px 22px; }
    .pr-tip {
        background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a;
        padding: 14px 16px; border-radius: 10px; font-size: 13px; line-height: 1.8; margin-bottom: 16px;
        overflow: hidden;
    }
    .pr-guide-title { font-size: 14px; font-weight: 600; margin-bottom: 6px; }
    .pr-guide-text { margin-top: 2px; color: #1e3a8a; }
    .pr-guide-box {
        margin-top: 10px; padding: 10px 12px; background: #fff; border: 1px solid #dbeafe;
        border-radius: 8px; line-height: 1.9;
    }
    .pr-guide-box .pr-guide-box-title { font-weight: 500; margin-bottom: 4px; color: #1e3a8a; }
    .pr-row { display: grid; grid-template-columns: 88px 1fr; gap: 10px; align-items: center; margin-bottom: 14px; }
    .pr-row > label { color: #374151; font-weight: 500; }
    .pr-preview {
        background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px;
        padding: 14px 16px; font-size: 13px; color: #374151;
    }
    .pr-preview input { display: inline-block; width: 80px; margin: 0 4px; }
    .pr-preview .calc-steps { margin-top: 10px; padding-top: 10px; border-top: 1px solid #dcfce7; font-size: 13px; line-height: 1.9; }
    .pr-preview .calc-steps b { color: #2563eb; }
    .pr-preview .calc-steps .final { font-size: 15px; color: #059669; font-weight: 700; }
    .pr-intervals { display: flex; flex-direction: column; gap: 6px; }
    .pr-interval {
        display: flex; align-items: flex-start; gap: 10px; padding: 12px 12px;
        background: #f3f4f6; border-radius: 8px; border: 1px solid transparent;
        transition: 0.15s;
    }
    .pr-interval:hover { background: #eff6ff; border-color: #bfdbfe; }
    .pr-interval .idx {
        background: #2563eb; color: #fff; width: 22px; height: 22px; font-size: 12px;
        display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; font-weight: 700;
        flex-shrink: 0;
    }
    .pr-interval .pr-interval-body { flex: 1; min-width: 0; }
    .pr-interval .pr-interval-title { font-size: 13px; color: #1e293b; line-height: 1.8; }
    .pr-interval .pr-interval-title b { color: #2563eb; }
    .pr-interval .pr-interval-example { font-size: 12px; color: #94a3b8; margin-top: 4px; line-height: 1.8; }
    .pr-interval .del {
        margin-left: auto; color: #ef4444; cursor: pointer; padding: 3px 8px;
        border: 1px solid #fecaca; border-radius: 6px; font-size: 12px;
        flex-shrink: 0; align-self: center;
    }
    .pr-interval .del:hover { background: #fef2f2; }
    .pr-add-btn {
        background: linear-gradient(135deg, #2563eb, #3b82f6); color: #fff;
        border: none; border-radius: 8px; padding: 8px 16px; cursor: pointer;
        font-size: 13px; font-weight: 600;
    }
    .pr-add-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 14px rgba(37,99,235,0.3); }
    .pr-empty { padding: 24px; text-align: center; color: #9ca3af; font-size: 13px; background: #f9fafb; border-radius: 10px; border: 1px dashed #d1d5db; }
    .pr-footer {
        border-top: 1px solid #f0f0f0; padding: 12px 22px; background: #fafafa; text-align: right;
    }
    .pr-dialog-field { margin-bottom: 12px; }
    .pr-dialog-field > label { display: block; font-size: 13px; color: #374151; font-weight: 500; margin-bottom: 4px; }
    .pr-dialog-field > .hint { font-size: 12px; color: #9ca3af; margin-top: 3px; }
    .pr-dialog-field > .hint b { color: #2563eb; }
    .pr-dialog-field input { width: 100%; }
</style>

<div class="pr-wrap" id="App">
    <div class="pr-tip">
        <div class="pr-guide-title">这个功能有什么用？</div>
        <div class="pr-guide-text">你在会员等级里设了“默认加价比例”，但便宜和贵的商品用同一个比例会不太合理：</div>
        <div class="pr-guide-text" style="padding-left:6px;">5 元的商品加 100% → 卖 10 元，用户觉得还行；<b style="color:#ef4444;">500 元的商品加 100% → 卖 1000 元，用户嫌太贵！</b></div>
        <div class="pr-guide-text">所以这个规则让你按成本价分区间：<b>便宜的全额加价，贵的少加一点</b>。</div>
        <div class="pr-guide-box">
            <div class="pr-guide-box-title">计算公式：售价 = 成本价 + 成本价 × 等级加价比例 × 加价力度</div>
            <div style="margin-top:4px;">举例：成本价 <b>30 元</b>，等级加价比例 <b>100%</b> 的情况下：</div>
            <div style="padding-left:6px;">加价力度 <b style="color:#2563eb;">100%</b> → 30 + 30×100%×100% = <b style="color:#059669;">60 元</b>（全额加价，跟不用规则一样）</div>
            <div style="padding-left:6px;">加价力度 <b style="color:#2563eb;">80%</b> → 30 + 30×100%×80% = <b style="color:#059669;">54 元</b>（加价打了8折）</div>
            <div style="padding-left:6px;">加价力度 <b style="color:#2563eb;">50%</b> → 30 + 30×100%×50% = <b style="color:#059669;">45 元</b>（加价打了5折）</div>
            <div style="margin-top:4px;color:#64748b;">简单说：<b>加价力度 = 等级加价比例打几折生效</b>。100% = 全额加价，50% = 加价打半，0% = 不加价。</div>
        </div>
    </div>

    <div class="pr-row">
        <label>规则名称</label>
        <input id="ruleName" type="text" class="layui-input" value="<?= htmlspecialchars($info['name']) ?>" placeholder="起个名字，如：全站默认、VIP专属加价">
    </div>

    <div class="pr-row">
        <label>加价区间</label>
        <div class="pr-intervals" id="intervalList"></div>
    </div>

    <div class="pr-row">
        <label></label>
        <div><button type="button" class="pr-add-btn" onclick="addInterval()"><i class="ri-add-line"></i> 新增加价区间</button></div>
    </div>

    <div class="pr-row">
        <label>模拟计算</label>
        <div class="pr-preview">
            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                商品成本价 <input id="testCost" type="number" class="layui-input" value="10" step="0.01"> 元，
                等级加价比例（会员等级里设的） <input id="testMarkup" type="number" class="layui-input" value="100" step="0.01"> %
                <button type="button" class="layui-btn layui-btn-sm layui-btn-normal" onclick="calcPreview()"><i class="ri-calculator-line"></i> 计算</button>
            </div>
            <div class="calc-steps" id="calcSteps"></div>
        </div>
    </div>
</div>

<div style="height:60px;"></div>
<div class="pr-footer">
    <button class="layui-btn" onclick="saveRule()"><i class="ri-save-line"></i> 保存规则</button>
    <button class="layui-btn layui-btn-primary" onclick="parent.layer.closeAll()">取消</button>
</div>

<script>
var RULE_ID = <?= (int)$info['id'] ?>;
var TOKEN = '<?= LoginAuth::genToken() ?>';
var intervals = <?= json_encode(json_decode($info['rules'] ?: '[]', true) ?: []) ?>;

function profitDesc(p) {
    p = Number(p);
    if (p >= 100) return '（全额加价）';
    if (p <= 0) return '（不加价）';
    if (p % 10 === 0) return '（打' + (p / 10) + '折加价）';
    return '（保留' + p + '%加价）';
}

function renderIntervals() {
    var $wrap = $('#intervalList');
    $wrap.empty();
    if (intervals.length === 0) {
        $wrap.html('<div class="pr-empty">尚未配置加价区间，请点击下方「新增加价区间」按钮开始配置</div>');
        calcPreview();
        return;
    }
    intervals.forEach(function(it, idx){
        var desc = profitDesc(it.profit);
        var exCost = ((Number(it.min) + Number(it.max)) / 2).toFixed(2);
        var exAdd = (exCost * 1 * it.profit / 100).toFixed(2);
        var exPrice = (Number(exCost) + Number(exAdd)).toFixed(2);
        var html = '<div class="pr-interval">'
            + '<span class="idx">' + (idx + 1) + '</span>'
            + '<div class="pr-interval-body">'
            + '<div class="pr-interval-title">成本价 <b>' + it.min + '</b> ~ <b>' + it.max + '</b> 元 → 加价力度 <b>' + it.profit + '%</b>' + desc + '</div>'
            + '<div class="pr-interval-example">例：成本价 ' + exCost + ' 元，加价 ' + exAdd + ' 元 → 售价约 ' + exPrice + ' 元（按等级加价比 100% 计算）</div>'
            + '</div>'
            + '<span class="del" data-idx="' + idx + '">删除</span>'
            + '</div>';
        $wrap.append(html);
    });
    calcPreview();
}

function addInterval() {
    var prev = intervals.length > 0 ? intervals[intervals.length - 1] : null;
    var minDefault = prev ? (Number(prev.max)).toString() : '0';
    var profitLimit = prev ? Number(prev.profit) : 100;
    var content = '<div style="padding:16px 20px;">'
        + '<div class="pr-dialog-field"><label>成本价最低值（元）</label><input id="dMin" type="number" step="0.0001" class="layui-input" value="' + minDefault + '" ' + (prev ? 'readonly style="background:#f3f4f6;"' : '') + '>'
        + '<div class="hint">成本价 ≥ 这个数的商品，就用这个区间的加价力度</div></div>'
        + '<div class="pr-dialog-field"><label>成本价最高值（元）</label><input id="dMax" type="number" step="0.0001" class="layui-input" placeholder="如：100">'
        + '<div class="hint">成本价 ≤ 这个数的商品，就用这个区间的加价力度</div></div>'
        + '<div class="pr-dialog-field"><label>加价力度（%）—— 等级加价比例打几折生效</label><input id="dProfit" type="number" step="0.01" min="0" max="' + profitLimit + '" class="layui-input" placeholder="0 ~ ' + profitLimit + '">'
        + '<div class="hint"><b>100%</b> = 全额加价（跟不用规则一样），<b>80%</b> = 加价打8折，<b>50%</b> = 加价打对折，<b>0%</b> = 不加价按成本卖</div></div>'
        + (prev ? '<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:8px 12px;font-size:12px;color:#92400e;">提示：加价力度应该递减，不能超过上一个区间的 ' + profitLimit + '%（贵的商品加价应该更少）</div>' : '')
        + '</div>';

    layer.open({
        title: '新增加价区间',
        type: 1, area: ['420px', 'auto'], content: content,
        btn: ['确认添加', '取消'], skin: 'dc-layer-modern',
        yes: function(idx){
            var min = parseFloat($('#dMin').val());
            var max = parseFloat($('#dMax').val());
            var profit = parseFloat($('#dProfit').val());
            if (isNaN(min) || isNaN(max) || isNaN(profit)) { layer.msg('请完整填写'); return; }
            if (max <= min) { layer.msg('最高值必须大于最低值'); return; }
            if (profit < 0 || profit > 100) { layer.msg('加价力度必须在 0~100 之间'); return; }
            if (profit > profitLimit) { layer.msg('加价力度不能超过上一区间的 ' + profitLimit + '%'); return; }
            intervals.push({ min: min, max: max, profit: profit });
            renderIntervals();
            layer.close(idx);
        }
    });
}

// 删除规则
$(document).on('click', '.pr-interval .del', function(){
    var idx = parseInt($(this).attr('data-idx'), 10);
    layer.confirm('删除此区间后，后续区间也会一起删除。确定继续？', { icon: 3, title: '确认删除' }, function(i){
        intervals = intervals.slice(0, idx);
        renderIntervals();
        layer.close(i);
    });
});

// 测试售价
function calcPreview() {
    var cost = parseFloat($('#testCost').val()) || 0;
    var markup = parseFloat($('#testMarkup').val()) || 0;
    var $steps = $('#calcSteps');
    if (cost <= 0) {
        $steps.html('<span style="color:#9ca3af;">请输入商品成本价</span>');
        return;
    }
    var profit = 100;
    var matched = false;
    var matchRange = '';
    intervals.forEach(function(it){
        if (!matched && cost >= it.min && cost <= it.max) {
            profit = Number(it.profit);
            matchRange = it.min + ' ~ ' + it.max;
            matched = true;
        }
    });
    var addAmount = cost * markup / 100 * profit / 100;
    var price = cost + addAmount;
    var html = '';
    if (matched) {
        html += '<div>1. 匹配到区间：成本价 <b>' + matchRange + ' 元</b>，加价力度 <b>' + profit + '%</b>' + profitDesc(profit) + '</div>';
    } else {
        html += '<div>1. <span style="color:#f59e0b;">未匹配到任何区间，使用默认加价力度 100%（全额加价）</span></div>';
    }
    html += '<div>2. 加价金额 = ' + cost + ' × ' + markup + '% × ' + profit + '% = <b>' + addAmount.toFixed(2) + ' 元</b></div>';
    html += '<div class="final">3. 最终售价 = ' + cost + ' + ' + addAmount.toFixed(2) + ' = ' + price.toFixed(2) + ' 元</div>';
    $steps.html(html);
}
$('#testCost, #testMarkup').on('input', calcPreview);

// 保存
function saveRule() {
    var name = $.trim($('#ruleName').val());
    if (name === '') { layer.msg('请填写规则名称', { icon: 2 }); return; }
    if (intervals.length === 0) { layer.msg('请至少添加一条区间', { icon: 2 }); return; }
    var loadIdx = layer.load(2);
    $.ajax({
        type: 'POST', url: 'price_rule.php?tab=profit&action=save_ajax', dataType: 'json',
        data: { id: RULE_ID, name: name, rules: JSON.stringify(intervals), token: TOKEN },
        success: function(e){
            layer.close(loadIdx);
            if (e.code != 0) { layer.msg(e.msg || '保存失败', { icon: 2 }); return; }
            layer.msg('分润规则已保存', { icon: 1, time: 900 });
            setTimeout(function(){
                if (parent.reloadRuleTable) parent.reloadRuleTable();
                parent.layer.closeAll();
            }, 1000);
        },
        error: function(){ layer.close(loadIdx); layer.msg('网络错误', { icon: 2 }); }
    });
}

renderIntervals();
var maxHeight = $(window.parent).innerHeight() * 0.75;
$(".pr-wrap").css({ "max-height": maxHeight + "px", "overflow-y": "auto" });
</script>
