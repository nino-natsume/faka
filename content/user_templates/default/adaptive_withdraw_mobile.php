<?php
defined('DC_ROOT') || exit('access denied!');
$walletBalance = isset($user['money']) ? (float)$user['money'] : 0;
$withdrawMinAmount = isset($withdrawMinAmount) ? (float)$withdrawMinAmount : 10;
$withdrawFeeRate = isset($withdrawFeeRate) ? (float)$withdrawFeeRate : 0;
$withdrawMinAmountText = number_format($withdrawMinAmount, 2, '.', '');
$withdrawFeeRateText = rtrim(rtrim(number_format($withdrawFeeRate, 2, '.', ''), '0'), '.');
if ($withdrawFeeRateText === '') $withdrawFeeRateText = '0';
$savedWithdrawReceiptImage = isset($savedWithdrawReceiptImage) ? trim((string)$savedWithdrawReceiptImage) : '';
$savedWithdrawReceiptImageUrl = isset($savedWithdrawReceiptImageUrl) ? trim((string)$savedWithdrawReceiptImageUrl) : '';
$hasSavedWithdrawReceipt = $savedWithdrawReceiptImage !== '' && $savedWithdrawReceiptImageUrl !== '';
$savedRealname = isset($savedWithdrawRealname) ? trim((string)$savedWithdrawRealname) : '';
$savedAccount = isset($savedWithdrawAccount) ? trim((string)$savedWithdrawAccount) : '';
$savedMethod = isset($savedWithdrawMethod) ? trim((string)$savedWithdrawMethod) : '';
$withdrawMethodOptions = (isset($withdrawMethodOptions) && is_array($withdrawMethodOptions) && !empty($withdrawMethodOptions)) ? $withdrawMethodOptions : [
    ['value' => 'alipay', 'label' => '支付宝'],
    ['value' => 'wechat', 'label' => '微信'],
    ['value' => 'qq', 'label' => 'QQ'],
    ['value' => 'bank', 'label' => '银行卡'],
];
$withdrawMethodValues = array_map(function($item) { return (string)($item['value'] ?? ''); }, $withdrawMethodOptions);
if ($savedMethod === '' || !in_array($savedMethod, $withdrawMethodValues, true)) {
    $savedMethod = (string)($withdrawMethodOptions[0]['value'] ?? '');
}
?>
<style>
    body { background: var(--bg-main); }
    .uw-page { padding: 12px; }
    .uw-card{background: linear-gradient(0deg, #fff, #f3f5f8); border: 2px solid #fff; border-radius: 10px; box-shadow: var(--shadow-primary); overflow: hidden;}
    .uw-head { padding: 14px 14px 8px; }
    .uw-title { font-size: 18px; font-weight: 900; color: var(--text-main); }
    .uw-desc { margin-top: 4px; font-size: 12px; color: var(--text-sub); }
    .uw-body { padding: 0 14px 14px; }
    .uw-upload-box { margin-bottom: 12px; }
    .uw-upload-trigger { position: relative; width: 132px; height: 120px; margin: 0 auto 8px; border-radius: 16px; border: 1px solid var(--card-border); background: #f8fafc; overflow: hidden; display:flex; align-items:center; justify-content:center; text-align:center; cursor:pointer; transition:.18s ease; }
    .uw-upload-trigger:hover { border-color: #cbd5e1; }
    .uw-upload-trigger.is-uploaded { border-color: rgba(var(--tp-rgb),.18); background: rgba(var(--tp-rgb),.06); }
    .uw-upload-trigger img { width:100%; height:100%; object-fit:cover; display:none; }
    .uw-upload-trigger.is-uploaded img { display:block; }
    .uw-upload-mask { padding: 12px; color: var(--text-sub); font-size: 12px; line-height: 1.6; }
    .uw-upload-trigger.is-uploaded .uw-upload-mask { position:absolute; left:8px; right:8px; bottom:8px; padding:8px 10px; border-radius:12px; background:rgba(15,23,42,.56); color:#fff; line-height:1.5; }
    .uw-upload-name { display:block; margin-top:4px; font-size:12px; opacity:.86; }
    .uw-upload-tip { text-align:center; font-size:12px; color:var(--text-sub); }
    .uw-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:12px; }
    .uw-field { margin-bottom: 0; }
    .uw-field.is-span-2 { grid-column: span 2; }
    .uw-label { display:block; margin-bottom:8px; font-size:13px; color:var(--text-main); font-weight:800; }
    .uw-field-tip { margin-top: 6px; font-size: 12px; color: var(--text-sub); line-height: 1.6; }
    .uw-field-tip strong { color: var(--theme-primary, #667eea); font-weight: 800; }
    .uw-input, .uw-textarea, .uw-select { width:100%; border-radius:14px; border:1px solid var(--card-border); background:#fff; }
    .uw-textarea { min-height: 80px; }
    .uw-tips { margin-top: 12px; padding: 10px 12px; border-radius: 14px; background: rgba(102,126,234,0.06); color: var(--text-sub); font-size: 12px; line-height: 1.7; }
    .uw-foot { padding: 12px 14px 14px; display:grid; grid-template-columns: 1fr 1fr; gap:10px; }
    .uw-btn { height: 42px; border-radius: 14px; font-weight: 800; }

    @media (max-width: 360px) {
        .uw-grid { grid-template-columns: 1fr; }
        .uw-field.is-span-2 { grid-column: span 1; }
    }
</style>
<div class="uw-page">
    <div class="uw-card">
        <div class="uw-head">
            <div class="uw-title">余额提现申请</div>
            <div class="uw-desc">请准确填写提现信息，提交后等待审核处理</div>
        </div>
        <form class="layui-form" id="form">
            <div class="uw-body">
                <input name="receipt_image" id="uw-receipt-image" value="<?= htmlspecialchars($savedWithdrawReceiptImage, ENT_QUOTES) ?>" type="hidden">
                <div class="uw-upload-box">
                    <div class="uw-upload-trigger<?= $hasSavedWithdrawReceipt ? ' is-uploaded' : '' ?>" id="uw-upload-trigger">
                        <img id="uw-upload-preview" src="<?= htmlspecialchars($savedWithdrawReceiptImageUrl, ENT_QUOTES) ?>" alt="收款码">
                        <div class="uw-upload-mask">
                            <span id="uw-upload-text"><?= $hasSavedWithdrawReceipt ? '点击重新上传' : '点击上传收款码' ?></span>
                            <span class="uw-upload-name" id="uw-upload-name"><?= $hasSavedWithdrawReceipt ? '已保存默认收款码' : '支持 JPG/PNG/GIF/WEBP，最大 2MB' ?></span>
                        </div>
                    </div>
                    <div class="uw-upload-tip">上传后会自动保存为默认收款码，下次提现可直接复用</div>
                </div>
                <div class="uw-grid">
                    <div class="uw-field">
                        <label class="uw-label">提现金额</label>
                        <input type="number" name="amount" id="uw-amount-input" placeholder="请输入提现金额" class="layui-input uw-input" step="0.01" min="<?= $withdrawMinAmount ?>">
                        <div class="uw-field-tip">最低 <strong>¥<?= $withdrawMinAmountText ?></strong> · 可提余额 <strong>¥<?= number_format($walletBalance, 2) ?></strong></div>
                        <div class="uw-field-tip" id="uw-amount-calc-tip">手续费率 <?= htmlspecialchars($withdrawFeeRateText, ENT_QUOTES) ?>% · 输入金额后自动计算到账金额</div>
                    </div>
                    <div class="uw-field">
                        <label class="uw-label">提现方式</label>
                        <select name="method" class="uw-select">
                            <option value="">请选择提现方式</option>
                            <?php foreach ($withdrawMethodOptions as $methodOption): ?>
                                <?php $methodValue = (string)($methodOption['value'] ?? ''); if ($methodValue === '') continue; ?>
                                <option value="<?= htmlspecialchars($methodValue, ENT_QUOTES) ?>"<?= $savedMethod === $methodValue ? ' selected' : '' ?>><?= htmlspecialchars((string)($methodOption['label'] ?? $methodValue), ENT_QUOTES) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="uw-field">
                        <label class="uw-label">账户信息</label>
                        <input type="text" name="account" placeholder="请输入提现账户" class="layui-input uw-input" value="<?= htmlspecialchars($savedAccount, ENT_QUOTES) ?>">
                    </div>
                    <div class="uw-field">
                        <label class="uw-label">真实姓名</label>
                        <input type="text" name="realname" placeholder="请输入真实姓名" class="layui-input uw-input" value="<?= htmlspecialchars($savedRealname, ENT_QUOTES) ?>">
                    </div>
                    <div class="uw-field is-span-2">
                        <label class="uw-label">备注说明</label>
                        <textarea name="remark" placeholder="请输入备注说明（可选）" class="layui-textarea uw-textarea" rows="3"></textarea>
                    </div>
                </div>
                <div class="uw-tips">
                    1. 提现金额不能超过当前可用余额。<br>
                    2. 提交后将在 1-3 个工作日内处理。<br>
                    3. 账户信息错误可能导致提现失败。
                </div>
                <input name="token" value="<?= LoginAuth::genToken() ?>" type="hidden">
            </div>
            <div class="uw-foot">
                <button type="submit" class="layui-btn uw-btn" lay-submit lay-filter="submit">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary uw-btn">重置表单</button>
            </div>
        </form>
    </div>
</div>
<script>
layui.use(['form', 'upload', 'layer'], function(){
    var $ = layui.$;
    var layer = layui.layer;
    var form = layui.form;
    var upload = layui.upload;
    var uploadLoading = 0;
    var feeRate = <?= json_encode((float)$withdrawFeeRate) ?>;
    var initialReceiptPath = <?= json_encode($savedWithdrawReceiptImage) ?>;
    var initialReceiptUrl = <?= json_encode($savedWithdrawReceiptImageUrl) ?>;

    function formatMoney(value) {
        var num = parseFloat(value || 0);
        if (isNaN(num)) num = 0;
        return num.toFixed(2);
    }

    function updateAmountCalcTip() {
        var amount = parseFloat($('#uw-amount-input').val() || 0);
        if (isNaN(amount) || amount <= 0) {
            $('#uw-amount-calc-tip').text('手续费率 ' + feeRate + '% · 输入金额后自动计算到账金额');
            return;
        }
        var fee = Math.max(0, Math.min(amount, Math.round(amount * feeRate) / 100));
        fee = Math.round(fee * 100) / 100;
        var actual = Math.max(0, Math.round((amount - fee) * 100) / 100);
        $('#uw-amount-calc-tip').text('本次提现手续费 ¥' + formatMoney(fee) + ' · 预计到账 ¥' + formatMoney(actual));
    }

    function setReceipt(path, url, name) {
        if (!path || !url) {
            return;
        }
        $('#uw-receipt-image').val(path);
        $('#uw-upload-preview').attr('src', url);
        $('#uw-upload-text').text('点击重新上传');
        $('#uw-upload-name').text(name || '收款码已上传');
        $('#uw-upload-trigger').addClass('is-uploaded');
    }

    function applySavedReceipt() {
        if (!initialReceiptPath || !initialReceiptUrl) {
            return false;
        }
        setReceipt(initialReceiptPath, initialReceiptUrl, '已保存默认收款码');
        return true;
    }

    function resetReceipt() {
        $('#uw-receipt-image').val('');
        $('#uw-upload-preview').attr('src', '');
        $('#uw-upload-text').text('点击上传收款码');
        $('#uw-upload-name').text('支持 JPG/PNG/GIF/WEBP，最大 2MB');
        $('#uw-upload-trigger').removeClass('is-uploaded');
        applySavedReceipt();
    }

    upload.render({
        elem: '#uw-upload-trigger',
        url: '<?= DC_URL ?>user/api.php?action=upload_withdraw_receipt_image',
        field: 'file',
        accept: 'images',
        exts: 'jpg|jpeg|png|gif|webp',
        size: 2048,
        data: {
            token: '<?= LoginAuth::genToken() ?>'
        },
        before: function(){
            uploadLoading = layer.load(2);
        },
        done: function(res){
            if (uploadLoading) {
                layer.close(uploadLoading);
                uploadLoading = 0;
            }
            if (!res || res.code != 200) {
                return layer.msg((res && res.msg) || '上传失败', {icon:2,time:3000});
            }
            initialReceiptPath = (res.data && res.data.path) || '';
            initialReceiptUrl = (res.data && res.data.url) || '';
            setReceipt(initialReceiptPath, initialReceiptUrl, (res.data && res.data.name) || '收款码已上传');
        },
        error: function(){
            if (uploadLoading) {
                layer.close(uploadLoading);
                uploadLoading = 0;
            }
            layer.msg('上传失败，请稍后重试', {icon:2,time:3000});
        }
    });

    $('#uw-amount-input').on('input change', updateAmountCalcTip);
    updateAmountCalcTip();

    form.on('submit(submit)', function(data){
        var loadIndex = layer.load(2);
        $.ajax({
            type: 'POST',
            url: '?action=withdraw_ajax',
            data: data.field,
            dataType: 'json',
            success: function(e){
                layer.close(loadIndex);
                if (e.code == 400) {
                    return layer.msg(e.msg, {icon:2,time:3000});
                }
                resetReceipt();
                parent.layer.closeAll();
                parent.layer.msg('提现申请已提交，请耐心等待处理', {icon:1,time:3000});
                parent.location.reload();
            },
            error: function(xhr){
                layer.close(loadIndex);
                var msg = '提交失败，请稍后重试';
                try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e) {}
                layer.msg(msg, {icon:2,time:3000});
            }
        });
        return false;
    });
    form.render('select');
});
</script>
