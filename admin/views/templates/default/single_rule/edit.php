<?php defined('DC_ROOT') || exit('access denied!'); ?>
<style>
    body { overflow: hidden; background: #fff; }
    .sr-wrap { padding: 20px 22px; }
    .sr-tip {
        background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a;
        padding: 14px 16px; border-radius: 10px; font-size: 13px; line-height: 1.8; margin-bottom: 16px;
        overflow: hidden;
    }
    .sr-guide-title { font-size: 14px; font-weight: 600; margin-bottom: 6px; }
    .sr-guide-text { margin-top: 2px; color: #1e3a8a; }
    .sr-guide-box {
        margin-top: 10px; padding: 10px 12px; background: #fff; border: 1px solid #dbeafe;
        border-radius: 8px; line-height: 1.9;
    }
    .sr-guide-box-title { font-weight: 500; margin-bottom: 4px; color: #1e3a8a; }
    .sr-row { display: grid; grid-template-columns: 88px 1fr; gap: 10px; align-items: center; margin-bottom: 14px; }
    .sr-row > label { color: #374151; font-weight: 500; }
    .sr-type-opts { display: flex; gap: 10px; }
    .sr-type-opts label {
        cursor: pointer; padding: 6px 14px; border-radius: 8px; border: 1px solid #e5e7eb;
        transition: 0.15s; display: flex; align-items: center; gap: 6px; background: #f9fafb;
    }
    .sr-type-opts label.active {
        background: linear-gradient(135deg, #2563eb, #3b82f6); color: #fff; border-color: #2563eb;
    }
    .sr-table {
        width: 100%; border-collapse: collapse; border: 1px solid #e5e7eb; border-radius: 8px;
        overflow: hidden; font-size: 13px;
    }
    .sr-table th { background: #f3f4f6; color: #374151; font-weight: 600; padding: 10px; text-align: left; }
    .sr-table td { padding: 8px 10px; border-top: 1px solid #f1f5f9; color: #4b5563; }
    .sr-table td input { width: 100%; padding: 4px 8px; border: 1px solid #e5e7eb; border-radius: 6px; box-sizing: border-box; }
    .sr-table td input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,0.15); }
    .sr-table .unit { color: #9ca3af; font-size: 12px; }
    .sr-preview {
        background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px;
        padding: 14px 16px; font-size: 13px; color: #374151; margin-top: 10px;
    }
    .sr-preview input { width: 90px; }
    .sr-preview .results { margin-top: 10px; padding-top: 10px; border-top: 1px solid #dcfce7; line-height: 1.9; }
    .sr-preview .sr-result-line { padding: 4px 0; }
    .sr-preview .sr-result-level { color: #2563eb; font-weight: 600; }
    .sr-preview .sr-result-subline { padding-left: 14px; color: #4b5563; }
    .sr-preview .sr-result-subline b { color: #059669; font-size: 14px; }
    .sr-mode-hint { font-size: 12px; color: #6b7280; line-height: 1.7; margin-top: 6px; }
    .sr-table-tip { font-size: 12px; color: #6b7280; line-height: 1.8; margin-top: 8px; }
    .sr-footer { border-top: 1px solid #f0f0f0; padding: 12px 22px; background: #fafafa; text-align: right; }
    .sr-empty { color: #9ca3af; padding: 20px 10px; text-align: center; }
</style>

<div class="sr-wrap">
    <div class="sr-tip">
        <div class="sr-guide-title">这个规则怎么用？</div>
        <div class="sr-guide-text">大部分商品用等级的“默认加价比例”就够了，但有些重点商品或爆款，你想给不同等级的会员设置不同的加价 —— 这个规则就是给单个商品“开小灶”的。</div>
        <div class="sr-guide-text">绑定到商品后，这个商品的价格就按这里的设置来，<b>不再走等级默认加价比例</b>。</div>
        <div class="sr-guide-box">
            <div class="sr-guide-box-title">两种加价模式，任选其一：</div>
            <div style="margin-bottom:3px;"><span style="color:#2563eb;font-weight:600;">固定金额加价</span>：直接在成本价上加一个固定的数。例：成本 100 元，加 20 元 → <b style="color:#059669;">卖 120 元</b></div>
            <div><span style="color:#f97316;font-weight:600;">按百分比加价</span>：按成本价的百分比加价。例：成本 100 元，加 30% → <b style="color:#059669;">卖 130 元</b></div>
            <div style="margin-top:6px;color:#64748b;">下方表格有两列：“普通购买”是花钱买时的加价，“积分购买”是用积分换时的加价，可以分开设。不填 = 该等级不“开小灶”，继续用默认加价比例。</div>
        </div>
    </div>

    <div class="sr-row">
        <label>规则名称</label>
        <input id="ruleName" type="text" class="layui-input" value="<?= htmlspecialchars($info['name']) ?>" placeholder="起个名字，如：爆款商品加价、活动专属加价">
    </div>

    <div class="sr-row">
        <label>加价模式</label>
        <div>
            <div class="sr-type-opts" id="typeOpts">
                <label data-type="1" class="<?= (int)$info['type'] === 1 ? 'active' : '' ?>"><input type="radio" name="type" value="1" <?= (int)$info['type'] === 1 ? 'checked' : '' ?> style="display:none;"> <i class="ri-price-tag-3-line"></i> 固定金额加价（元）</label>
                <label data-type="2" class="<?= (int)$info['type'] === 2 ? 'active' : '' ?>"><input type="radio" name="type" value="2" <?= (int)$info['type'] === 2 ? 'checked' : '' ?> style="display:none;"> <i class="ri-percent-line"></i> 按百分比加价（%）</label>
            </div>
            <div class="sr-mode-hint" id="modeHint" style="margin-top:6px;"></div>
        </div>
    </div>

    <?php if (empty($levels)): ?>
        <div class="sr-empty">
            <i class="ri-alert-line" style="font-size:24px;color:#f59e0b;"></i><br>
            系统暂无启用的会员等级，请先前往 <a href="<?= DC_URL ?>admin/member.php" target="_blank" style="color:#2563eb;">会员等级</a> 创建或启用等级后再配置规则。
        </div>
    <?php else: ?>
    <div class="sr-row" style="grid-template-columns: 88px 1fr; align-items: flex-start;">
        <label style="padding-top: 10px;">按等级设置</label>
        <div>
            <table class="sr-table">
                <thead>
                    <tr>
                        <th>会员等级</th>
                        <th style="width: 180px;">花钱买时加多少 <span class="unit" id="thPriceUnit">(元)</span></th>
                        <th style="width: 180px;">用积分换时加多少 <span class="unit" id="thProfitsUnit">(元)</span></th>
                    </tr>
                </thead>
                <tbody id="levelBody">
                    <?php
                    $currentRules = json_decode($info['rules'] ?: '{}', true) ?: [];
                    foreach ($levels as $lv):
                        $lvId = (int)$lv['id'];
                        $p = (float)($currentRules[(string)$lvId]['price'] ?? 0);
                        $f = (float)($currentRules[(string)$lvId]['profits'] ?? 0);
                        $pValue = $p != 0 ? (string)$p : '';
                        $fValue = $f != 0 ? (string)$f : '';
                    ?>
                    <tr>
                        <td><b><?= htmlspecialchars($lv['name']) ?></b> <span class="unit">(#<?= $lvId ?>)</span></td>
                        <td><input type="number" step="0.0001" data-lv="<?= $lvId ?>" data-key="price" class="lv-input" value="<?= htmlspecialchars($pValue) ?>" placeholder="不填 = 不单独设置"></td>
                        <td><input type="number" step="0.0001" data-lv="<?= $lvId ?>" data-key="profits" class="lv-input" value="<?= htmlspecialchars($fValue) ?>" placeholder="不填 = 不单独设置"></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="sr-table-tip">两列可以填不同的数，让花钱买和用积分换的加价不一样。某个格子不填 = 这个等级不“开小灶”，继续用默认加价比例算价。</div>
            <div class="sr-preview">
                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                    <span style="font-weight:500;">模拟计算：</span>
                    商品成本价 <input id="testCost" type="number" class="layui-input" value="100" step="0.01"> 元
                    <button type="button" class="layui-btn layui-btn-sm layui-btn-normal" onclick="calcPreview()"><i class="ri-calculator-line"></i> 计算</button>
                </div>
                <div class="results" id="testResults"><span style="color:#9ca3af;">填完上方数据后，点击「计算」查看各等级花钱买 / 用积分换分别卖多少钱</span></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div style="height:60px;"></div>
<div class="sr-footer">
    <button class="layui-btn" onclick="saveRule()"><i class="ri-save-line"></i> 保存规则</button>
    <button class="layui-btn layui-btn-primary" onclick="parent.layer.closeAll()">取消</button>
</div>

<script>
var RULE_ID = <?= (int)$info['id'] ?>;
var TOKEN = '<?= LoginAuth::genToken() ?>';
var LEVELS = <?= json_encode(array_map(function($lv){ return ['id' => (int)$lv['id'], 'name' => $lv['name']]; }, $levels ?? [])) ?>;

// 类型切换
$('#typeOpts label').on('click', function(){
    $('#typeOpts label').removeClass('active');
    $(this).addClass('active');
    $(this).find('input').prop('checked', true);
    updateUnitHints();
    calcPreview();
});

// 读取当前规则
function readRules() {
    var rules = {};
    $('.lv-input').each(function(){
        var lv = $(this).attr('data-lv');
        var key = $(this).attr('data-key');
        var val = parseFloat($(this).val()) || 0;
        if (!rules[lv]) rules[lv] = { price: 0, profits: 0 };
        rules[lv][key] = val;
    });
    return rules;
}

// 当前类型
function getType() {
    return parseInt($('#typeOpts label.active').attr('data-type'), 10) || 1;
}

// 实时预览
function updateUnitHints() {
    var type = getType();
    var unit = type === 1 ? '(元)' : '(%)';
    $('#thPriceUnit, #thProfitsUnit').text(unit);
    $('#modeHint').text(type === 1 ? '当前选择固定金额加价：下方填的数字就是在成本价上直接加多少元。比如填 20，就是加 20 元。' : '当前选择百分比加价：下方填的数字就是在成本价上加百分之几。比如填 30，就是加 30%。');
}

function buildPreviewLine(label, cost, bump, type) {
    bump = parseFloat(bump) || 0;
    if (bump === 0) return '';
    if (type === 1) {
        var fixedPrice = cost + bump;
        return '<div class="sr-result-subline">' + label + '：成本价 ' + cost.toFixed(2) + ' 元，在原价基础上加 ' + bump.toFixed(2) + ' 元 → <b>' + fixedPrice.toFixed(2) + ' 元</b></div>';
    }
    var addAmount = cost * bump / 100;
    var percentPrice = cost + addAmount;
    return '<div class="sr-result-subline">' + label + '：成本价 ' + cost.toFixed(2) + ' 元，在原价基础上加 ' + bump.toFixed(2) + '%（约 ' + addAmount.toFixed(2) + ' 元） → <b>' + percentPrice.toFixed(2) + ' 元</b></div>';
}

function calcPreview() {
    var cost = parseFloat($('#testCost').val()) || 0;
    var $res = $('#testResults');
    if (cost <= 0) { $res.html('<span style="color:#f59e0b;">请输入商品成本价</span>'); return; }
    var rules = readRules();
    var type = getType();
    var typeLabel = type === 1 ? '固定金额加价' : '百分比加价';
    var html = '<div style="margin-bottom:4px;color:#6b7280;font-size:12px;">当前模式：<b style="color:#2563eb;">' + typeLabel + '</b>，商品成本价：<b>' + cost.toFixed(2) + ' 元</b></div>';
    var matched = 0;
    LEVELS.forEach(function(lv){
        var r = rules[lv.id];
        if (!r) return;
        var p = parseFloat(r.price) || 0;
        var f = parseFloat(r.profits) || 0;
        if (p === 0 && f === 0) return;
        matched++;
        html += '<div class="sr-result-line"><span class="sr-result-level">' + lv.name + '</span></div>';
        if (p !== 0) html += buildPreviewLine('花钱买', cost, p, type);
        if (f !== 0) html += buildPreviewLine('用积分换', cost, f, type);
    });
    if (matched === 0) {
        html += '<div style="color:#f59e0b;">上方表格还没填任何数据，填好后再点计算看效果</div>';
    }
    $res.html(html);
}
$(document).on('input', '.lv-input, #testCost', calcPreview);

// 保存
function saveRule() {
    var name = $.trim($('#ruleName').val());
    if (name === '') { layer.msg('请填写规则名称', { icon: 2 }); return; }
    var rules = readRules();
    // 清理全 0 的等级（避免污染）
    var cleaned = {};
    $.each(rules, function(lv, v){
        if ((v.price && v.price !== 0) || (v.profits && v.profits !== 0)) {
            cleaned[lv] = v;
        }
    });
    var loadIdx = layer.load(2);
    $.ajax({
        type: 'POST', url: 'price_rule.php?tab=single&action=save_ajax', dataType: 'json',
        data: { id: RULE_ID, name: name, type: getType(), rules: JSON.stringify(cleaned), token: TOKEN },
        success: function(e){
            layer.close(loadIdx);
            if (e.code != 0) { layer.msg(e.msg || '保存失败', { icon: 2 }); return; }
            layer.msg('单品规则已保存', { icon: 1, time: 900 });
            setTimeout(function(){
                if (parent.reloadSingleRuleTable) parent.reloadSingleRuleTable();
                parent.layer.closeAll();
            }, 1000);
        },
        error: function(){ layer.close(loadIdx); layer.msg('网络错误', { icon: 2 }); }
    });
}

updateUnitHints();
calcPreview();
var maxHeight = $(window.parent).innerHeight() * 0.75;
$(".sr-wrap").css({ "max-height": maxHeight + "px", "overflow-y": "auto" });
</script>
