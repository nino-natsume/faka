<?php defined('DC_ROOT') || exit('access denied!'); ?>

<?php
// 获取授权服务器地址
$licenseServerUrl = '';
if (defined('CURRENT_LINE') && defined('DC_LINE') && isset(DC_LINE[CURRENT_LINE])) {
    $licenseServerUrl = rtrim(DC_LINE[CURRENT_LINE]['value'], '/');
} elseif (defined('LICENSE_SERVER_URL') && LICENSE_SERVER_URL) {
    $licenseServerUrl = rtrim(LICENSE_SERVER_URL, '/');
} else {
    $licenseServerUrl = 'https://dcshop.xzsc.cc';
}
?>

<style>

.balance-card {
    position: relative;
    overflow: hidden;
    color: #1e293b;
    background: url('https://cloudcache.tencentcs.cn/qcloud/ui/activity-v2/build/LatestActivity/images/latest_card_bg_type1.png') center/cover no-repeat;
    border-radius: 10px;
    padding: 25px 30px;
    margin-bottom: 24px;
    border: 2px solid #fff;
    box-shadow: 8px 8px 20px 0 rgba(55, 99, 170, .1);
}
.balance-card-inner {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 20px;
    align-items: end;
}
.balance-card-main { min-width: 0; }
.balance-title {
    font-size: 14px;
    color: #64748b;
    letter-spacing: 1px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.balance-amount-row { display: flex; align-items: flex-end; gap: 14px; flex-wrap: wrap; }
.balance-amount {
    font-size: 38px;
    font-weight: 800;
    line-height: 1;
    letter-spacing: 1px;
    color: #1e293b;
}
.balance-log-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    color: #64748b;
    background: rgba(37,99,235,.08);
    border-radius: 20px;
    padding: 4px 12px;
    text-decoration: none;
    transition: 0.2s;
}
.balance-log-link:hover { background: rgba(37,99,235,0.14); color: #2563eb; text-decoration: none; }
.balance-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 10px; }
.balance-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 500;
    color: #475569;
    background: rgba(37,99,235,.06);
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 18px;
    white-space: nowrap;
    text-decoration: none;
    cursor: pointer;
    transition: .18s ease;
}
.balance-btn:hover {
    color: #2563eb;
    text-decoration: none;
    transform: translateY(-2px);
    background: rgba(37,99,235,.10);
}
.balance-btn-primary {
    background: #2563eb;
    color: #fff;
    border-color: #2563eb;
    box-shadow: 0 4px 12px rgba(37,99,235,.25);
}
.balance-btn-primary:hover { color: #fff; background: #1d4ed8; }
@media (max-width: 768px) {
    .balance-card { padding: 24px 20px; }
    .balance-card-inner { grid-template-columns: 1fr; }
    .balance-actions { justify-content: flex-start; }
}



/* ========== 主内容网格 ========== */
.svip-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}
.svip-main { display: flex; flex-direction: column; gap: 20px; min-width: 0; }
.svip-side { display: flex; flex-direction: column; gap: 20px; min-width: 0; }
.svip-main .svip-panel, .svip-side .svip-panel { flex: 1; display: flex; flex-direction: column; }

/* ========== 通用面板 ========== */
.svip-panel {
    background: #ffffff85;
    border-radius: 10px;
    padding: 20px;
    border: 1px solid #eef1f4;
}
.svip-panel-hd {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}
.svip-panel-icon {
    width: 36px; height: 36px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; color: #fff; flex-shrink: 0;
    background: linear-gradient(135deg, #2563eb, #3b82f6);
}
.svip-panel-icon.purple { background: linear-gradient(135deg, #8b5cf6, #a78bfa); }
.svip-panel-icon.green { background: linear-gradient(135deg, #10b981, #34d399); }
.svip-panel-icon.orange { background: linear-gradient(135deg, #f59e0b, #d97706); }
.svip-panel-title { font-size: 15px; font-weight: 700; color: #1e293b; }
.svip-panel-sub { font-size: 12px; color: #94a3b8; margin-top: 1px; }

/* ========== 充值金额选择 ========== */
.rc-amount-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}
.rc-amount-item {
    background: #f8fafc;
    border: 2px solid #f1f5f9;
    border-radius: 8px;
    padding: 14px 6px;
    text-align: center;
    cursor: pointer;
    transition: all 0.18s;
}
.rc-amount-item:hover { background: #eef2ff; border-color: #c7d2fe; }
.rc-amount-item.active { background: linear-gradient(135deg, #2563eb, #3b82f6); border-color: transparent; }
.rc-amount-item.active .rc-amount-num,
.rc-amount-item.active .rc-amount-unit { color: #fff; }
.rc-amount-num { font-size: 20px; font-weight: 700; color: #1e293b; }
.rc-amount-unit { font-size: 11px; color: #94a3b8; margin-left: 2px; }
.rc-custom-row {
    margin-top: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.rc-custom-label { font-size: 12px; color: #64748b; white-space: nowrap; }
.rc-custom-input-wrap {
    flex: 1;
    display: flex;
    align-items: center;
    background: #f8fafc;
    border-radius: 8px;
    padding: 0 10px;
    border: 2px solid #f1f5f9;
    transition: all 0.2s;
}
.rc-custom-input-wrap:focus-within { background: #fff; border-color: #2563eb; }
.rc-input-prefix { font-size: 15px; font-weight: 600; color: #2563eb; }
.rc-custom-input-wrap input {
    flex: 1; border: none; background: none;
    padding: 9px 6px; font-size: 15px; font-weight: 600; outline: none;
}
.rc-submit-btn {
    width: 100%; padding: 12px; margin-top: 12px;
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    color: #fff; border: none; border-radius: 8px;
    font-size: 14px; font-weight: 600; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 6px;
    transition: all 0.2s;
    box-shadow: 0 4px 14px rgba(37,99,235,0.25);
}
.rc-submit-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(37,99,235,0.4); }

/* ========== 短信包 ========== */
.sms-pack-list { display: flex; flex-direction: column; gap: 10px; }
.sms-pack-row {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 14px; background: #f8fafc; border-radius: 8px;
    border: 2px solid #f1f5f9; transition: all 0.18s;
}
.sms-pack-row:hover { background: #faf5ff; border-color: #e9d5ff; }
.sms-pack-meta { flex: 1; min-width: 0; }
.sms-pack-name { font-size: 14px; font-weight: 600; color: #1e293b; }
.sms-pack-desc { font-size: 12px; color: #94a3b8; margin-top: 2px; }
.sms-pack-price { font-size: 17px; font-weight: 700; color: #8b5cf6; margin-right: 10px; white-space: nowrap; }
.sms-pack-btn {
    padding: 7px 18px; border: none; border-radius: 6px;
    background: linear-gradient(135deg, #8b5cf6, #a78bfa);
    color: #fff; font-size: 12px; font-weight: 600;
    cursor: pointer; transition: all 0.2s; white-space: nowrap;
}
.sms-pack-btn:hover { box-shadow: 0 3px 10px rgba(139,92,246,0.35); transform: translateY(-1px); }
.sms-pack-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; box-shadow: none; }


/* ========== layui弹窗内支付方式列表 ========== */
.rc-modal-amount {
    text-align: center; padding: 14px;
    background: #f8fafc; border-radius: 8px; margin-bottom: 14px;
}
.rc-pay-label { font-size: 12px; color: #94a3b8; display: block; margin-bottom: 4px; }
.rc-pay-price { font-size: 30px; font-weight: 700; color: #2563eb; }
.rc-pay-methods { display: flex; flex-direction: column; gap: 8px; }
.rc-pay-card {
    display: flex; align-items: center; gap: 12px;
    padding: 12px; background: #f8fafc; border-radius: 8px;
    cursor: pointer; transition: all 0.18s; border: 2px solid transparent;
}
.rc-pay-card:hover { background: #fff; border-color: #2563eb; box-shadow: 0 3px 10px rgba(37,99,235,0.1); }
.rc-pay-icon {
    width: 40px; height: 40px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: #fff; flex-shrink: 0;
}
.rc-pay-icon.wxpay { background: linear-gradient(135deg, #07c160, #06ad56); }
.rc-pay-icon.alipay { background: linear-gradient(135deg, #1677ff, #0958d9); }
.rc-pay-icon.qqpay { background: linear-gradient(135deg, #12b7f5, #0d9cd9); }
.rc-pay-icon.manual { background: linear-gradient(135deg, #f59e0b, #d97706); }
.rc-pay-info { flex: 1; }
.rc-pay-name { font-size: 13px; font-weight: 600; color: #1e293b; }
.rc-pay-desc { font-size: 12px; color: #94a3b8; margin-top: 1px; }
.rc-pay-arrow { color: #cbd5e1; font-size: 16px; }
.rc-pay-card.disabled { opacity: 0.5; cursor: not-allowed; }
.rc-pay-card.disabled:hover { background: #f8fafc; border-color: transparent; box-shadow: none; }
.rc-pay-card-tip { font-size: 11px; color: #ef4444; margin-top: 2px; }

/* 充值说明 */
.rc-tips-card {  padding: 14px 16px; margin-top: 16px; }
.rc-tips-title { font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 8px; display: flex; align-items: center; gap: 5px; }
.rc-tips-title i { color: #3b82f6; font-size: 15px; }
.rc-tips-list { font-size: 12px; color: #94a3b8; line-height: 1.8; }
.rc-tips-item { display: flex; align-items: baseline; gap: 6px; }
.rc-tips-item::before { content: '•'; color: #3b82f6; flex-shrink: 0; }

/* ========== layui弹窗内扫码区 ========== */
.rc-qr-body { text-align: center; }
.rc-qr-amount { font-size: 26px; font-weight: 700; color: #2563eb; margin: 8px 0 12px; }
.rc-qr-wrap {
    background: #f8fafc; border-radius: 10px; padding: 16px;
    margin-bottom: 12px; min-height: 220px;
    display: flex; align-items: center; justify-content: center;
}
.rc-qr-loading { color: #94a3b8; font-size: 14px; text-align: center; }
.rc-qr-tip { font-size: 13px; color: #64748b; margin-bottom: 10px; }
.rc-qr-tip i { color: #2563eb; margin-right: 4px; }
.rc-qr-status {
    padding: 10px; background: #eef2ff; border-radius: 8px;
    font-size: 13px; color: #2563eb;
}
.rc-qr-status.success { background: #ecfdf5; color: #10b981; }
.rc-qr-status.error { background: #fef2f2; color: #ef4444; }
.rc-status-spin {
    display: inline-block; width: 13px; height: 13px;
    border: 2px solid #2563eb; border-top-color: transparent;
    border-radius: 50%; animation: rcSpin 1s linear infinite;
    margin-right: 6px; vertical-align: middle;
}
@keyframes rcSpin { to { transform: rotate(360deg); } }
.ri-spin { animation: rcSpin 1s linear infinite; }

/* ========== 余额卡片短信 ========== */
.balance-divider { width: 1px; height: 32px; background: #e2e8f0; margin: 0 20px; }
.balance-sms { display: flex; align-items: baseline; gap: 4px; }
.sms-num { font-size: 28px; font-weight: 700; color: #1e293b; }
.sms-unit { font-size: 14px; color: #94a3b8; font-weight: normal; }
.balance-amount-row { display: flex; align-items: center; }

/* ========== 外层 card-body 内边距 ========== */
.svip-card-body { padding: 20px; }

/* ========== 响应式 ========== */
@media (max-width: 1400px) {
    .svip-card-body { padding: 20px 80px; }
}
@media (max-width: 1100px) {
    .svip-card-body { padding: 20px 30px; }
}
@media (max-width: 900px) {
    .svip-card-body { padding: 20px 16px; }
    .svip-grid { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    .svip-card-body { padding: 16px 10px; }
    .balance-amount-row { flex-wrap: wrap; gap: 8px; }
    .balance-divider { display: none; }
    .balance-amount { font-size: 28px; }
    .sms-num { font-size: 22px; }
}
@media (max-width: 640px) {
    .svip-card-body { padding: 12px 6px; }
    .rc-amount-grid { grid-template-columns: repeat(3, 1fr); gap: 6px; }
    .rc-amount-item { padding: 10px 4px; }
    .rc-amount-num { font-size: 17px; }
    .sms-pack-row { flex-wrap: wrap; }
    .sms-pack-price { margin-right: 0; }
    .balance-card { padding: 20px 16px; }
    .balance-actions { flex-direction: column; }
    .balance-btn { justify-content: center; }
}


</style>

<!-- 余额卡片 -->
<section class="balance-card">
    <div class="balance-card-inner">
        <div class="balance-card-main">
            <div class="balance-title"><i class="ri-wallet-3-line"></i> 服务端账号<?php if($username): ?> ： <?= htmlspecialchars($username) ?><?php endif; ?></div>
            <div class="balance-amount-row">
                <div class="balance-amount"><span class="sms-unit">余额 </span>¥ <?= number_format($balance, 2) ?></div>
                <div class="balance-divider"></div>
                <div class="balance-sms"><span class="sms-unit">剩余 </span><span class="sms-num" id="smsBalanceNum"><?= (int)$smsBalance ?></span> <span class="sms-unit">条短信</span></div>
            </div>
        </div>
        <div class="balance-actions">
            <a href="./store.php" class="balance-btn balance-btn-primary">
                <i class="ri-shopping-bag-line"></i> 应用商店
            </a>
            <a href="<?= $licenseServerUrl ?>/user/" target="_blank" class="balance-btn">
                <i class="ri-user-line"></i> 用户中心
            </a>
        </div>
    </div>
</section>

<div class="layui-table-view" style="border-radius:8px;overflow:hidden;">
    <div class="layui-card-header" style="display:flex;align-items:center;justify-content:center;position:relative;height:auto;padding:12px 15px;">
        <span style="position:absolute;left:15px;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">余额充值</span>
    </div>
    <div class="layui-card-body svip-card-body">

<!-- 主内容区：左栏充值+右栏短信包 -->
<div class="svip-grid">
    <div class="svip-main">
        <!-- 在线充值 -->
        <div class="svip-panel">
            <div class="svip-panel-hd">
                
                <div>
                    <div class="svip-panel-title">在线充值</div>
                </div>
            </div>
            <div id="rcRechargeBody">
                <div class="rc-amount-grid">
                    <div class="rc-amount-item" data-amount="10"><span class="rc-amount-num">10</span><span class="rc-amount-unit">元</span></div>
                    <div class="rc-amount-item" data-amount="50"><span class="rc-amount-num">50</span><span class="rc-amount-unit">元</span></div>
                    <div class="rc-amount-item" data-amount="100"><span class="rc-amount-num">100</span><span class="rc-amount-unit">元</span></div>
                    <div class="rc-amount-item" data-amount="200"><span class="rc-amount-num">200</span><span class="rc-amount-unit">元</span></div>
                    <div class="rc-amount-item" data-amount="500"><span class="rc-amount-num">500</span><span class="rc-amount-unit">元</span></div>
                    <div class="rc-amount-item" data-amount="1000"><span class="rc-amount-num">1000</span><span class="rc-amount-unit">元</span></div>
                </div>
                <div class="rc-custom-row">
                    <span class="rc-custom-label">自定义</span>
                    <div class="rc-custom-input-wrap">
                        <span class="rc-input-prefix">¥</span>
                        <input type="number" id="rcAmountInput" placeholder="1-10000" min="1" max="10000">
                    </div>
                </div>
                <button class="rc-submit-btn" id="rcSubmitBtn" onclick="rcShowPayModal()">
                    <i class="ri-secure-payment-line"></i> 立即充值
                </button>
                <div id="rcRechargeTips" class="rc-tips-card" style="display:none;"></div>
            </div>
        </div>
    </div>

    <div class="svip-side">
        <!-- 购买短信包 -->
        <?php if (!empty($smsPackages)): ?>
        <div class="svip-panel">
            <div class="svip-panel-hd">
                <div class="svip-panel-icon purple"><i class="ri-message-2-line"></i></div>
                <div>
                    <div class="svip-panel-title">购买短信包</div>
                </div>
            </div>
            <div class="sms-pack-list">
                <?php foreach ($smsPackages as $idx => $pkg): ?>
                <div class="sms-pack-row">
                    <div class="sms-pack-meta">
                        <div class="sms-pack-name"><?= htmlspecialchars($pkg['name']) ?></div>
                        <div class="sms-pack-desc"><?= number_format($pkg['count']) ?> 条 · <?= number_format($pkg['price'] / max($pkg['count'], 1), 3) ?> 元/条</div>
                    </div>
                    <div class="sms-pack-price">¥<?= number_format($pkg['price'], 0) ?></div>
                    <button class="sms-pack-btn" onclick="rcBuySmsPack(<?= $idx ?>)">购买</button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>


    </div>
</div>

<script src="<?= $licenseServerUrl ?>/assets/qrcode.min.js"></script>
<script>
$(function(){
    $("#menu-store").addClass('open');
    $("#menu-store > .submenu").show();
    $("#menu-store > .link .admin-arrow").addClass('active');
    $("#menu-store-recharge").addClass('active');
});

var layer = layui.layer;

// ========== 金额选择 ==========
document.querySelectorAll('.rc-amount-item').forEach(function(item) {
    item.addEventListener('click', function() {
        document.querySelectorAll('.rc-amount-item').forEach(function(i) { i.classList.remove('active'); });
        this.classList.add('active');
        document.getElementById('rcAmountInput').value = this.dataset.amount;
    });
});
document.getElementById('rcAmountInput').addEventListener('input', function() {
    document.querySelectorAll('.rc-amount-item').forEach(function(i) { i.classList.remove('active'); });
});

// ========== 状态变量 ==========
var rcPayMethodsData = null;
var rcCurrentAmount = 0;
var rcCheckTimer = null;
var rcOutTradeNo = '';
var rcPayType = '';
var rcPayIndex = 0;
var rcQrcodeObj = null;
var rcPayLayerId = null;
var rcQrLayerId = null;

function rcGetAmount() {
    return parseFloat(document.getElementById('rcAmountInput').value) || 0;
}

// ========== 页面加载时预取支付方式和充值说明 ==========
$.get('./store.php?action=recharge_methods', function(res) {
    if (res.code == 200 && res.data) {
        rcPayMethodsData = res.data;
        rcRenderRechargeTips(res.data.recharge_tips);
    }
}, 'json');

// ========== 支付方式弹窗（layui layer） ==========
function rcShowPayModal() {
    var amount = rcGetAmount();
    if (!amount || amount < 1 || amount > 10000) {
        layer.msg('请选择或输入充值金额（1-10000元）', {icon: 0});
        return;
    }
    rcCurrentAmount = amount;

    var content = '<div style="padding:20px;">'
        + '<div class="rc-modal-amount"><span class="rc-pay-label">支付金额</span><span class="rc-pay-price">¥' + amount + '</span></div>'
        + '<div class="rc-pay-methods" id="rcPayMethods"><div style="text-align:center;padding:30px;color:#999;"><i class="ri-loader-4-line ri-spin" style="font-size:24px;"></i><br>加载支付方式中...</div></div>'
        + '</div>';

    rcPayLayerId = layer.open({
        type: 1,
        title: '选择支付方式',
        area: [Math.min(440, window.innerWidth - 30) + 'px', 'auto'],
        maxHeight: 520,
        content: content,
        shadeClose: true,
        end: function() { rcPayLayerId = null; }
    });

    if (rcPayMethodsData) {
        rcRenderPayMethods(rcPayMethodsData);
        return;
    }

    $.get('./store.php?action=recharge_methods', function(res) {
        if (res.code == 200 && res.data) {
            rcPayMethodsData = res.data;
            rcRenderPayMethods(res.data);
            rcRenderRechargeTips(res.data.recharge_tips);
        } else {
            $('#rcPayMethods').html('<div style="text-align:center;padding:30px;color:#999;">' + (res.msg || '获取支付方式失败') + '</div>');
        }
    }, 'json').fail(function() {
        $('#rcPayMethods').html('<div style="text-align:center;padding:30px;color:#ef4444;">网络错误，请刷新重试</div>');
    });
}

// ========== 支付规则判断 ==========
function rcCheckMethodAllowed(rules, methodKey, amount) {
    if (!rules || (!rules.amount_rule_enabled && !rules.agent_rule_enabled)) return { allowed: true, tip: '' };
    // 代理规则优先
    if (rules.is_agent && rules.agent_rule_enabled) {
        if (amount < rules.agent_amount) {
            if (rules.agent_small_methods && rules.agent_small_methods.length > 0) {
                if (rules.agent_small_methods.indexOf(methodKey) === -1) return { allowed: false, tip: rules.agent_small_tip || '' };
            }
        } else {
            if (rules.agent_large_methods && rules.agent_large_methods.length > 0) {
                if (rules.agent_large_methods.indexOf(methodKey) === -1) return { allowed: false, tip: rules.agent_large_tip || '' };
            }
        }
        return { allowed: true, tip: '' };
    }
    // 普通用户金额规则
    if (rules.amount_rule_enabled) {
        if (rules.small_method && amount < rules.small_amount) {
            if (rules.small_method !== methodKey) return { allowed: false, tip: rules.small_tip || '' };
        }
        if (rules.large_methods && rules.large_methods.length > 0 && amount >= rules.large_amount) {
            if (rules.large_methods.indexOf(methodKey) === -1) return { allowed: false, tip: rules.large_tip || '' };
        }
    }
    return { allowed: true, tip: '' };
}

function rcRenderPayMethods(data) {
    var html = '';
    var typeIcons = { wxpay: 'ri-wechat-pay-fill', alipay: 'ri-alipay-fill', qqpay: 'ri-qq-fill' };
    var typeDescs = { wxpay: '微信扫码支付', alipay: '支付宝扫码支付', qqpay: 'QQ钱包支付' };
    var rules = data.pay_rules || {};
    var amount = rcCurrentAmount;

    if (data.epay_methods) {
        data.epay_methods.forEach(function(m) {
            var icon = typeIcons[m.type] || 'ri-bank-card-line';
            var desc = typeDescs[m.type] || '在线支付';
            var st = rcCheckMethodAllowed(rules, '' + m.index, amount);
            var cls = st.allowed ? '' : ' disabled';
            var tipHtml = st.tip ? '<div class="rc-pay-card-tip">' + st.tip + '</div>' : '';
            html += '<div class="rc-pay-card' + cls + '" data-method="epay" data-index="' + m.index + '" data-type="' + m.type + '" data-tip="' + (st.tip||'').replace(/"/g,'&quot;') + '">'
                + '<div class="rc-pay-icon ' + m.type + '"><i class="' + icon + '"></i></div>'
                + '<div class="rc-pay-info"><div class="rc-pay-name">' + m.name + '</div><div class="rc-pay-desc">' + desc + '</div>' + tipHtml + '</div>'
                + '<div class="rc-pay-arrow"><i class="ri-arrow-right-s-line"></i></div></div>';
        });
    }
    if (data.alipay_methods) {
        data.alipay_methods.forEach(function(m) {
            var st = rcCheckMethodAllowed(rules, 'alipay_' + m.index, amount);
            var cls = st.allowed ? '' : ' disabled';
            var tipHtml = st.tip ? '<div class="rc-pay-card-tip">' + st.tip + '</div>' : '';
            html += '<div class="rc-pay-card' + cls + '" data-method="alipay" data-index="' + m.index + '" data-tip="' + (st.tip||'').replace(/"/g,'&quot;') + '">'
                + '<div class="rc-pay-icon alipay"><i class="ri-alipay-fill"></i></div>'
                + '<div class="rc-pay-info"><div class="rc-pay-name">' + m.name + '</div><div class="rc-pay-desc">支付宝官方支付</div>' + tipHtml + '</div>'
                + '<div class="rc-pay-arrow"><i class="ri-arrow-right-s-line"></i></div></div>';
        });
    }
    if (data.manual_enabled) {
        var st = rcCheckMethodAllowed(rules, 'manual', amount);
        var cls = st.allowed ? '' : ' disabled';
        var tipHtml = st.tip ? '<div class="rc-pay-card-tip">' + st.tip + '</div>' : '';
        html += '<div class="rc-pay-card' + cls + '" data-method="manual" data-tip="' + (st.tip||'').replace(/"/g,'&quot;') + '">'
            + '<div class="rc-pay-icon manual"><i class="ri-customer-service-2-line"></i></div>'
            + '<div class="rc-pay-info"><div class="rc-pay-name">' + (data.manual_name || '人工充值') + '</div><div class="rc-pay-desc">联系客服人工充值</div>' + tipHtml + '</div>'
            + '<div class="rc-pay-arrow"><i class="ri-arrow-right-s-line"></i></div></div>';
    }
    if (!html) {
        html = '<div style="text-align:center;padding:30px;color:#999;">暂无可用支付方式，请前往用户中心充值</div>';
    }
    $('#rcPayMethods').html(html);
    // 绑定点击事件（统一用事件委托）
    $('#rcPayMethods').off('click', '.rc-pay-card').on('click', '.rc-pay-card', function() {
        var $card = $(this);
        if ($card.hasClass('disabled')) {
            layer.msg($card.data('tip') || '该支付方式不可用', {icon: 0});
            return;
        }
        var method = $card.data('method');
        var idx = $card.data('index');
        var type = $card.data('type');
        if (method === 'epay') rcSubmitEpay(idx, type);
        else if (method === 'alipay') rcSubmitAlipay(idx);
        else if (method === 'manual') rcSubmitManual();
    });
}

// ========== 充值说明渲染 ==========
function rcRenderRechargeTips(tipsText) {
    var el = document.getElementById('rcRechargeTips');
    if (!el) return;
    if (!tipsText || !tipsText.trim()) { el.style.display = 'none'; return; }
    var lines = tipsText.split('\n').map(function(s) { return s.trim(); }).filter(function(s) { return s; });
    if (!lines.length) { el.style.display = 'none'; return; }
    var html = '<div class="rc-tips-title"><i class="ri-lightbulb-line"></i> 充值说明</div><div class="rc-tips-list">';
    lines.forEach(function(line) {
        html += '<div class="rc-tips-item">' + line.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</div>';
    });
    html += '</div>';
    el.innerHTML = html;
    el.style.display = '';
}

function rcClosePayModal() {
    if (rcPayLayerId) { layer.close(rcPayLayerId); rcPayLayerId = null; }
}

// ========== 易支付 ==========
function rcSubmitEpay(index, payType) {
    rcClosePayModal();
    rcShowQrModal(rcCurrentAmount, payType);

    $.post('./store.php?action=create_recharge', {
        amount: rcCurrentAmount,
        pay_type: 'epay',
        pay_index: index
    }, function(res) {
        if (res.code == 200 && res.data) {
            rcOutTradeNo = res.data.out_trade_no;
            rcPayType = 'epay';
            rcPayIndex = index;
            rcShowQrCode(res.data.qr_code, res.data.pay_name, res.data.pay_type);
            rcStartCheck();
        } else {
            rcQrError(res.msg || '创建订单失败');
        }
    }, 'json').fail(function(xhr) {
        var msg = '网络错误，请重试';
        try { if (xhr.responseJSON && xhr.responseJSON.msg) msg = xhr.responseJSON.msg; } catch(e){}
        rcQrError(msg);
    });
}

// ========== 支付宝官方 ==========
function rcSubmitAlipay(index) {
    rcClosePayModal();
    rcShowQrModal(rcCurrentAmount, 'alipay');

    $.post('./store.php?action=create_recharge', {
        amount: rcCurrentAmount,
        pay_type: 'alipay_official',
        pay_index: index
    }, function(res) {
        if (res.code == 200 && res.data) {
            rcOutTradeNo = res.data.out_trade_no;
            rcPayType = 'alipay';
            rcPayIndex = index;
            rcShowQrCode(res.data.qr_code, res.data.pay_name, 'alipay');
            rcStartCheck();
        } else {
            rcQrError(res.msg || '创建订单失败');
        }
    }, 'json').fail(function(xhr) {
        var msg = '网络错误，请重试';
        try { if (xhr.responseJSON && xhr.responseJSON.msg) msg = xhr.responseJSON.msg; } catch(e){}
        rcQrError(msg);
    });
}

// ========== 人工充值（新标签页打开授权端原生页面） ==========
function rcSubmitManual() {
    rcClosePayModal();

    $.post('./store.php?action=create_recharge', {
        amount: rcCurrentAmount,
        pay_type: 'manual',
        pay_index: 0,
        return_url: location.href
    }, function(res) {
        if (res.code == 200 && res.data) {
            rcOutTradeNo = res.data.out_trade_no;
            rcPayType = 'manual';
            rcPayIndex = 0;
            if (res.data.manual_url) {
                window.open(res.data.manual_url, '_blank');
                rcStartCheck();
                layer.msg('已在新标签页打开人工充值页面，请联系客服完成充值', {icon: 0, time: 5000});
            }
        } else {
            layer.msg(res.msg || '创建订单失败', {icon: 2});
        }
    }, 'json').fail(function(xhr) {
        var msg = '网络错误，请重试';
        try { if (xhr.responseJSON && xhr.responseJSON.msg) msg = xhr.responseJSON.msg; } catch(e){}
        layer.msg(msg, {icon: 2});
    });
}

// ========== 扫码弹窗（layui layer） ==========
function rcGetQrMeta(payType) {
    var meta = { title: '在线支付', tip: '请使用对应APP扫描二维码完成支付' };
    if (payType === 'wxpay') { meta.title = '微信支付'; meta.tip = '请使用微信扫描二维码完成支付'; }
    else if (payType === 'alipay') { meta.title = '支付宝支付'; meta.tip = '请使用支付宝扫描二维码完成支付'; }
    return meta;
}

function rcBuildQrHtml(amount, payType) {
    var meta = rcGetQrMeta(payType);
    return '<div class="rc-qr-body" style="padding:20px;">'
        + '<div class="rc-qr-amount" style="margin-top:0;">¥<span id="rcQrAmount">' + parseFloat(amount).toFixed(2) + '</span></div>'
        + '<div class="rc-qr-wrap"><div id="rcQrBox" style="display:none;"></div><div id="rcQrLoading" class="rc-qr-loading"><i class="ri-loader-4-line ri-spin" style="font-size:28px;display:block;margin-bottom:8px;"></i> 创建订单中...</div></div>'
        + '<div class="rc-qr-tip" id="rcQrTip"><i class="ri-smartphone-line"></i> ' + meta.tip + '</div>'
        + '<div class="rc-qr-status" id="rcQrStatus"><span class="rc-status-spin"></span>正在创建订单...</div>'
        + '</div>';
}

function rcShowQrModal(amount, payType) {
    var meta = rcGetQrMeta(payType);
    rcQrLayerId = layer.open({
        type: 1,
        title: meta.title,
        area: [Math.min(400, window.innerWidth - 30) + 'px', 'auto'],
        content: rcBuildQrHtml(amount, payType),
        shadeClose: true,
        end: function() {
            rcQrLayerId = null;
            rcStopCheck();
        }
    });
}

function rcShowQrCode(qrUrl, payName, payType) {
    if (payName && rcQrLayerId) {
        // 更新 layui 弹窗标题
        $('#layui-layer' + rcQrLayerId + ' .layui-layer-title').text(payName);
    }
    $('#rcQrLoading').hide();
    var box = document.getElementById('rcQrBox');
    if (box) {
        box.innerHTML = '';
        box.style.display = 'block';
        rcQrcodeObj = new QRCode(box, {
            text: qrUrl,
            width: 200,
            height: 200,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
    }
    $('#rcQrStatus').html('<span class="rc-status-spin"></span>等待支付中...');
}

function rcQrError(msg) {
    $('#rcQrLoading').html('<i class="ri-error-warning-line" style="font-size:40px;color:#ef4444;"></i>');
    $('#rcQrStatus').attr('class', 'rc-qr-status error').html('<i class="ri-close-circle-line"></i> ' + msg);
}

function rcCloseQrModal() {
    if (rcQrLayerId) { layer.close(rcQrLayerId); rcQrLayerId = null; }
    rcStopCheck();
}

// ========== 轮询支付状态 ==========
function rcStartCheck() {
    rcStopCheck();
    var count = 0, max = 180;
    rcCheckTimer = setInterval(function() {
        if (count++ >= max) {
            rcStopCheck();
            var statusEl = document.getElementById('rcQrStatus');
            if (statusEl) {
                statusEl.className = 'rc-qr-status error';
                statusEl.innerHTML = '<i class="ri-time-line"></i> 支付超时，请关闭后重试';
            }
            return;
        }
        $.get('./store.php?action=check_recharge&out_trade_no=' + rcOutTradeNo + '&pay_type=' + rcPayType + '&idx=' + rcPayIndex, function(res) {
            if (res && res.paid) {
                rcStopCheck();
                rcOnPaySuccess(res.new_balance);
            }
        }, 'json');
    }, 2000);
}

function rcStopCheck() {
    if (rcCheckTimer) { clearInterval(rcCheckTimer); rcCheckTimer = null; }
}

function rcOnPaySuccess(newBalance) {
    if (newBalance) {
        var el = document.querySelector('.balance-amount');
        if (el) el.textContent = '¥ ' + parseFloat(newBalance).toFixed(2);
    }
    // 扫码弹窗成功
    var statusEl = document.getElementById('rcQrStatus');
    if (statusEl && rcQrLayerId) {
        statusEl.className = 'rc-qr-status success';
        statusEl.innerHTML = '<i class="ri-check-line"></i> 支付成功！余额已到账';
        setTimeout(rcCloseQrModal, 1500);
    }
    // 人工充值（新标签页）到账
    if (rcPayType === 'manual' && !rcQrLayerId) {
        layer.msg('充值成功！余额已到账', {icon: 1, time: 3000});
    }
}

// ========== 购买短信包 ==========
var rcSmsPackages = <?= json_encode($smsPackages ?? [], JSON_UNESCAPED_UNICODE) ?>;
var rcSmsBuying = false;

function rcBuySmsPack(idx) {
    if (rcSmsBuying) return;
    var pkg = rcSmsPackages[idx];
    if (!pkg) return;
    
    layer.confirm('确认使用余额 ¥' + parseFloat(pkg.price).toFixed(2) + ' 购买「' + pkg.name + '」（' + pkg.count + '条短信）？', {
        title: '购买短信包',
        btn: ['确认购买', '取消']
    }, function(layIdx) {
        layer.close(layIdx);
        rcSmsBuying = true;
        document.querySelectorAll('.sms-pack-btn').forEach(function(b) { b.disabled = true; });
        
        $.post('./store.php?action=buy_sms_pack', { pack_index: idx }, function(res) {
            rcSmsBuying = false;
            document.querySelectorAll('.sms-pack-btn').forEach(function(b) { b.disabled = false; });
            
            if (res.code == 200 && res.data) {
                layer.msg('购买成功！短信余额 +' + pkg.count + ' 条', {icon: 1});
                var balEl = document.querySelector('.balance-amount');
                if (balEl) balEl.textContent = '¥ ' + parseFloat(res.data.balance).toFixed(2);
                var smsEl = document.getElementById('smsBalanceNum');
                if (smsEl) smsEl.textContent = res.data.sms_balance;
                var smsEl2 = document.getElementById('smsBalanceNum2');
                if (smsEl2) smsEl2.textContent = res.data.sms_balance;
            } else {
                layer.msg(res.msg || '购买失败', {icon: 2});
            }
        }, 'json').fail(function(xhr) {
            rcSmsBuying = false;
            document.querySelectorAll('.sms-pack-btn').forEach(function(b) { b.disabled = false; });
            var msg = '网络错误，请重试';
            try { if (xhr.responseJSON && xhr.responseJSON.msg) msg = xhr.responseJSON.msg; } catch(e){}
            layer.msg(msg, {icon: 2});
        });
    });
}
</script>
