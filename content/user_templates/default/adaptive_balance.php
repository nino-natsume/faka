<?php
defined('DC_ROOT') || exit('access denied!');

$balanceRechargeOptions = isset($rechargeAmountOptions) && is_array($rechargeAmountOptions) ? $rechargeAmountOptions : [10, 50, 100, 200, 500, 1000];
$balancePaymentList = isset($payment) && is_array($payment) ? $payment : [];
$virtualCurrencyNameEsc = htmlspecialchars(getVirtualCurrencyName(), ENT_QUOTES, 'UTF-8');

$balanceResolveIcon = function ($icon) {
    $icon = (string)$icon;
    if ($icon === '') {
        return '';
    }
    if (preg_match('/^(https?:)?\/\//i', $icon) || strpos($icon, '/') === 0) {
        return $icon;
    }
    return DC_URL . ltrim($icon, './');
};
?>
<style>
    /* ============ 钱包 Hero 卡片 ============ */
    .balance-page {
        display: flex;
        flex-direction: column;
        gap: 18px;
        padding: 6px 0 18px;
    }

    .balance-card {
        padding: 24px 28px;
        border-radius: 10px;
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        box-shadow: 0 1px 18px #12345b0a;
    }

    .balance-card-inner {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0,1fr) auto;
        gap: 18px;
        align-items: end;
    }

    .balance-card-main { min-width: 0; }

    .balance-title {
        font-size: 13px;
        color: #64748b;
        font-weight: 600;
        letter-spacing: .5px;
        margin-bottom: 12px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .balance-title .fa { color: #94a3b8; }

    .balance-amount-row { display: flex; align-items: flex-end; gap: 14px; flex-wrap: wrap; }

    .balance-amount {
        font-size: 36px;
        font-weight: 800;
        line-height: 1;
        letter-spacing: .5px;
        color: #0f172a;
    }

    .balance-log-link {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(var(--tp-rgb),.06);
        color: var(--theme-primary);
        font-size: 12px;
        font-weight: 500;
        text-decoration: none;
        transition: 0.2s;
    }
    .balance-log-link:hover { background: rgba(var(--tp-rgb),.1); color: var(--tp-dark); text-decoration: none; }

    .balance-credits {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 6px 14px;
        border-radius: 999px;
        background: rgba(124,58,237,.06);
        color: #7c3aed;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: .5px;
    }
    .balance-credits .fa { font-size: 12px; opacity: .7; }

    .balance-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 10px; }

    .balance-btn {
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
    .balance-btn:hover {
        color: #1e293b;
        text-decoration: none;
        border-color: #cbd5e1;
        box-shadow: 0 2px 8px rgba(15,23,42,.06);
    }
    .balance-btn-primary {
        background: var(--theme-primary);
        color: #fff;
        border-color: var(--theme-primary);
    }
    .balance-btn-primary:hover { color: #fff; background: var(--tp-dark); border-color: var(--tp-dark); box-shadow: 0 4px 14px rgba(var(--tp-rgb),.25); }

    /* ============ 充值网格 ============ */
    .balance-recharge-grid {
        display: grid;
        grid-template-columns: 1fr 1.35fr;
        gap: 18px;
    }

    .balance-recharge-card {
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        border-radius: 10px;
        box-shadow: 0 1px 18px #12345b0a;
        padding: 26px 28px;
        display: flex;
        flex-direction: column;
    }

    .balance-recharge-title {
        font-size: 18px;
        font-weight: 800;
        color: #1f2937;
        margin: 0 0 8px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .balance-recharge-title i { color: var(--theme-primary); }

    .balance-recharge-desc {
        margin: 0 0 20px;
        color: #6b7280;
        font-size: 13px;
        line-height: 1.8;
    }

    /* ===== 卡密充值卡片 ===== */

    .balance-kami-input-row {
        display: flex;
        align-items: center;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
        padding: 4px 4px 4px 14px;
        transition: 0.2s;
    }
    .balance-kami-input-row:focus-within {
        border-color: var(--theme-primary);
        box-shadow: 0 0 0 3px rgba(var(--tp-rgb), 0.1);
    }

    .balance-kami-input-row i {
        color: #9ca3af;
        font-size: 15px;
        margin-right: 10px;
    }

    .balance-kami-input {
        flex: 1;
        border: none;
        outline: none;
        height: 44px;
        font-size: 14px;
        color: #1f2937;
        background: transparent;
    }

    .balance-kami-input::placeholder { color: #c4c9d4; }

    .balance-kami-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        gap: 6px;
        padding: 10px 22px;
        min-height: 44px;
        font-size: 14px;
        font-weight: 700;
        color: #fff;
        background: linear-gradient(135deg, var(--theme-primary) 0%, var(--tp-light) 100%);
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: 0.2s;
        white-space: nowrap;
    }
    .balance-kami-submit i { color: #fff; margin-right: 0; }
    .balance-kami-submit:hover { transform: translateY(-1px); box-shadow: 0 10px 22px rgba(var(--tp-rgb), 0.28); }
    .balance-kami-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; box-shadow: none; }

    .balance-kami-points {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px dashed rgba(var(--tp-rgb),.12);
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .balance-kami-point {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        color: #6b7280;
        font-size: 13px;
        line-height: 1.65;
    }
    .balance-kami-point i { color: #10b981; margin-top: 3px; flex-shrink: 0; }
    .balance-kami-point.is-note i { color: #f59e0b; }

    /* ===== 在线充值卡片 ===== */
    .balance-recharge-group { margin-bottom: 18px; }

    .balance-recharge-label {
        color: #374151;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 10px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .balance-recharge-label::before {
        content: "";
        width: 3px;
        height: 14px;
        background: var(--theme-primary);
        border-radius: 2px;
    }

    .balance-amount-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }

    .balance-amount-item {
        cursor: pointer;
        padding: 12px 6px;
        background: #fafafa;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        color: #374151;
        font-weight: 700;
        font-size: 18px;
        text-align: center;
        transition: 0.18s;
        font-family: inherit;
    }
    .balance-amount-item span { font-size: 12px; color: #9ca3af; margin-left: 3px; font-weight: 500; }
    .balance-amount-item:hover { border-color: rgba(var(--tp-rgb),.3); color: var(--theme-primary); }
    .balance-amount-item.is-active {
        background: linear-gradient(135deg, var(--theme-primary), var(--tp-light));
        color: #fff;
        border-color: transparent;
        box-shadow: 0 10px 20px rgba(var(--tp-rgb), 0.22);
    }
    .balance-amount-item.is-active span { color: rgba(255,255,255,0.85); }

    .balance-custom-row {
        margin-top: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .balance-custom-label { color: #6b7280; font-size: 13px; white-space: nowrap; }
    .balance-custom-input-wrap {
        flex: 1;
        display: inline-flex;
        align-items: center;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
        padding: 0 14px;
        transition: 0.2s;
    }
    .balance-custom-input-wrap:focus-within {
        border-color: var(--theme-primary);
        box-shadow: 0 0 0 3px rgba(var(--tp-rgb), 0.08);
    }
    .balance-custom-symbol { color: var(--theme-primary); font-weight: 700; margin-right: 4px; }
    .balance-custom-input {
        border: none;
        outline: none;
        height: 42px;
        flex: 1;
        font-size: 15px;
        color: #1f2937;
        background: transparent;
    }

    .balance-payment-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .balance-payment-item {
        cursor: pointer;
        padding: 12px 14px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
        transition: 0.18s;
    }
    .balance-payment-item:hover { border-color: rgba(var(--tp-rgb),.3); }
    .balance-payment-item.is-active {
        border-color: var(--theme-primary);
        background: rgba(var(--tp-rgb),.06);
        box-shadow: 0 6px 16px rgba(var(--tp-rgb), 0.1);
    }

    .balance-payment-icon {
        width: 28px;
        height: 28px;
        object-fit: contain;
        border-radius: 6px;
        background: #f5f5f5;
        flex-shrink: 0;
    }

    .balance-payment-info { flex: 1; min-width: 0; }
    .balance-payment-name {
        color: #1f2937;
        font-size: 14px;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .balance-payment-check {
        color: var(--theme-primary);
        font-size: 18px;
        opacity: 0;
        transition: 0.15s;
    }
    .balance-payment-item.is-active .balance-payment-check { opacity: 1; }

    .balance-payment-empty {
        padding: 28px 20px;
        text-align: center;
        color: #9ca3af;
        font-size: 13px;
        background: #fafafa;
        border: 1px dashed #e5e7eb;
        border-radius: 10px;
    }

    .balance-recharge-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        min-height: 48px;
        font-size: 15px;
        font-weight: 700;
        color: #fff;
        background: linear-gradient(135deg, var(--theme-primary) 0%, var(--tp-light) 100%);
        border: none;
        border-radius: 12px;
        cursor: pointer;
        transition: 0.2s;
        margin-top: 8px;
    }
    .balance-recharge-submit:hover { transform: translateY(-1px); box-shadow: 0 10px 22px rgba(var(--tp-rgb), 0.28); }
    .balance-recharge-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; box-shadow: none; }

    /* 提现弹窗 */
    .layui-layer-wd {
        border-radius: 16px !important;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(15, 23, 42, 0.18) !important;
        border: 2px solid #fff !important;
    }
    .layui-layer-wd .layui-layer-title {
        background: linear-gradient(135deg, var(--theme-primary) 0%, var(--tp-light) 100%);
        color: #fff;
        border-bottom: none;
        font-size: 15px;
        font-weight: 600;
        height: 48px;
        line-height: 48px;
        padding: 0 18px;
        border-radius: 16px 16px 0 0;
    }
    .layui-layer-wd .layui-layer-setwin a { top: 14px; }
    .layui-layer-wd .layui-layer-setwin a cite { background: rgba(255, 255, 255, 0.7); }
    .layui-layer-wd .layui-layer-content { border-radius: 0 0 16px 16px; }

    /* 响应式 */
    @media (max-width: 992px) {
        .balance-recharge-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 768px) {
        .balance-card { padding: 24px 20px; }
        .balance-card-inner { grid-template-columns: 1fr; }
        .balance-actions { justify-content: flex-start; }
        .balance-amount { font-size: 30px; }
        .balance-recharge-card { padding: 20px; }
        .balance-amount-grid { grid-template-columns: repeat(2, 1fr); }
        .balance-payment-grid { grid-template-columns: 1fr; }
        .balance-custom-row { flex-direction: column; align-items: stretch; gap: 8px; }
    }
</style>

<main class="balance-page">
    <!-- 钱包 Hero 卡片 -->
    <section class="balance-card">
        <div class="balance-card-inner">
            <div class="balance-card-main">
                <div class="balance-title"><i class="fa fa-wallet"></i> 我的钱包</div>
                <div class="balance-amount-row">
                    <div class="balance-amount" id="balanceAmountDisplay">¥ <?= htmlspecialchars((string)($user['money'] ?? '0.00')) ?></div>
                    <div class="balance-credits"><i class="fa fa-diamond"></i> <?= $virtualCurrencyNameEsc ?> <?= (int)($user['credits'] ?? 0) ?></div>
                    <a href="/user/balance.php?action=balance_log" class="balance-log-link">
                        收支明细 <i class="fa fa-angle-right"></i>
                    </a>
                </div>
            </div>
            <div class="balance-actions">
                <a href="/user/balance.php?action=withdraw_index" class="balance-btn">
                    <i class="fa fa-list-alt"></i> 提现记录
                </a>
                <button type="button" class="balance-btn balance-btn-primary" id="withdraw-btn">
                    <i class="fa fa-money"></i> 余额提现
                </button>
            </div>
        </div>
    </section>

    <!-- 充值网格 -->
    <section class="balance-recharge-grid">
        <!-- 卡密充值 -->
        <div class="balance-recharge-card balance-kami-card">
            <h2 class="balance-recharge-title"><i class="fa fa-ticket"></i> 卡密充值</h2>
            <p class="balance-recharge-desc">输入充值卡密后即可为钱包余额充值，充值成功后余额实时到账。</p>

            <div class="balance-kami-input-row">
                <i class="fa fa-key"></i>
                <input type="text" id="balanceKamiInput" class="balance-kami-input" placeholder="请输入充值卡密" maxlength="64" autocomplete="off">
                <button type="button" class="balance-kami-submit" id="balanceKamiSubmit">
                    兑换 <i class="fa fa-mouse-pointer"></i>
                </button>
            </div>

            <div class="balance-kami-points">
                <div class="balance-kami-point">
                    <i class="fa fa-check-circle"></i>
                    <span>输入卡密后点击充值，余额实时到账。</span>
                </div>
                <div class="balance-kami-point">
                    <i class="fa fa-check-circle"></i>
                    <span>每张卡密仅可使用一次，请妥善保管。</span>
                </div>
                <div class="balance-kami-point is-note">
                    <i class="fa fa-info-circle"></i>
                    <span>如遇卡密问题请联系管理员处理。</span>
                </div>
            </div>
        </div>
        

        <!-- 在线充值 -->
        <div class="balance-recharge-card">
            <h2 class="balance-recharge-title"><i class="fa fa-credit-card"></i> 在线充值</h2>
            <p class="balance-recharge-desc">选择充值金额和支付方式后即可发起支付，支付成功后余额会自动到账。</p>

            <div class="balance-recharge-group">
                <div class="balance-recharge-label">选择充值金额</div>
                <div class="balance-amount-grid">
                    <?php foreach ($balanceRechargeOptions as $index => $amount): ?>
                        <button type="button" class="balance-amount-item<?= $index === 0 ? ' is-active' : '' ?>" data-amount="<?= htmlspecialchars((string)$amount) ?>">
                            <?= intval($amount) ?><span>元</span>
                        </button>
                    <?php endforeach; ?>
                </div>
                <div class="balance-custom-row">
                    <div class="balance-custom-label">自定义金额</div>
                    <div class="balance-custom-input-wrap">
                        <span class="balance-custom-symbol">¥</span>
                        <input type="number" class="balance-custom-input" id="balanceRechargeAmount" min="1" max="10000" step="0.01" placeholder="1 - 10000">
                    </div>
                </div>
            </div>

            <div class="balance-recharge-group">
                <div class="balance-recharge-label">选择支付方式</div>
                <?php if (!empty($balancePaymentList)): ?>
                    <div class="balance-payment-grid">
                        <?php foreach ($balancePaymentList as $index => $payItem): ?>
                            <div class="balance-payment-item<?= !empty($payItem['active']) || $index === 0 ? ' is-active' : '' ?>"
                                 data-plugin="<?= htmlspecialchars((string)$payItem['plugin_name']) ?>"
                                 data-title="<?= htmlspecialchars((string)$payItem['title']) ?>"
                                 data-name="<?= htmlspecialchars((string)($payItem['name'] ?? '')) ?>">
                                <img src="<?= htmlspecialchars($balanceResolveIcon($payItem['icon'] ?? '')) ?>" alt="<?= htmlspecialchars((string)$payItem['title']) ?>" class="balance-payment-icon">
                                <div class="balance-payment-info">
                                    <div class="balance-payment-name"><?= htmlspecialchars((string)$payItem['title']) ?></div>
                                </div>
                                <i class="fa fa-check-circle balance-payment-check"></i>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="balance-payment-empty">当前站点暂未开启在线支付通道，请联系管理员配置支付插件。</div>
                <?php endif; ?>
            </div>

            <button type="button" class="balance-recharge-submit" id="balanceRechargeSubmit"<?= empty($balancePaymentList) ? ' disabled' : '' ?>>
                立即充值 <i class="fa fa-arrow-right"></i>
            </button>
        </div>
    </section>
</main>

<script>
layui.use(['layer', 'jquery'], function () {
    var $ = layui.$;
    var layer = layui.layer;
    var selectedAmount = parseFloat($('.balance-amount-item.is-active').attr('data-amount') || '0');

    function getSelectedPayment() {
        return $('.balance-payment-item.is-active').first();
    }

    // 同步更新余额显示
    function syncBalanceDisplay(newMoney) {
        if (typeof newMoney !== 'number' || isNaN(newMoney)) return;
        $('#balanceAmountDisplay').text('¥ ' + newMoney.toFixed(2));
    }
    function getCurrentBalance() {
        var txt = ($('#balanceAmountDisplay').text() || '').replace(/[^0-9.\-]/g, '');
        return parseFloat(txt) || 0;
    }

    // ====== 金额选择 ======
    $('.balance-amount-item').on('click', function () {
        $('.balance-amount-item').removeClass('is-active');
        $(this).addClass('is-active');
        selectedAmount = parseFloat($(this).attr('data-amount') || '0');
        $('#balanceRechargeAmount').val('');
    });

    $('#balanceRechargeAmount').on('input', function () {
        $('.balance-amount-item').removeClass('is-active');
        selectedAmount = parseFloat($(this).val() || '0');
    });

    $('.balance-payment-item').on('click', function () {
        $('.balance-payment-item').removeClass('is-active');
        $(this).addClass('is-active');
    });

    // ====== 卡密充值 ======
    function submitKamiRecharge() {
        var cardKey = ($('#balanceKamiInput').val() || '').trim();
        if (!cardKey) {
            layer.msg('请输入充值卡密', { icon: 0 });
            return;
        }
        var $btn = $('#balanceKamiSubmit');
        var loadIndex = layer.load(2, { shade: [0.15, '#000'] });
        $btn.prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: '?action=card_redeem_ajax',
            dataType: 'json',
            data: {
                card_key: cardKey,
                token: '<?= LoginAuth::genToken() ?>'
            },
            success: function (res) {
                if (res.code == 200) {
                    var amt = parseFloat((res.data && res.data.amount) || 0);
                    if (amt > 0) {
                        syncBalanceDisplay(getCurrentBalance() + amt);
                    }
                    $('#balanceKamiInput').val('');
                    var title = (res.data && res.data.title) ? res.data.title : '充值成功';
                    layer.msg('充值成功，面额 ¥' + (res.data.amount || '0.00') + '（' + title + '）', { icon: 1, time: 2600 });
                    return;
                }
                layer.msg(res.msg || '充值失败', { icon: 2 });
            },
            error: function (xhr) {
                var msg = '充值失败，请稍后再试';
                try { msg = JSON.parse(xhr.responseText).msg || msg; } catch (e) {}
                layer.msg(msg, { icon: 2 });
            },
            complete: function () {
                layer.close(loadIndex);
                $btn.prop('disabled', false);
            }
        });
    }

    $('#balanceKamiSubmit').on('click', submitKamiRecharge);
    $('#balanceKamiInput').on('keydown', function (e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            submitKamiRecharge();
        }
    });

    // ====== 在线充值 ======
    $('#balanceRechargeSubmit').on('click', function () {
        var amount = parseFloat($('#balanceRechargeAmount').val() || selectedAmount || 0);
        var $payment = getSelectedPayment();

        if (!amount || amount < 1 || amount > 10000) {
            return layer.msg('充值金额需在 1 - 10000 元之间', { icon: 0 });
        }
        if (!$payment.length) {
            return layer.msg('请选择支付方式', { icon: 0 });
        }

        var $btn = $(this);
        var loadIndex = layer.load(2, { shade: [0.2, '#000'] });
        $btn.prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: '?action=recharge_ajax',
            dataType: 'json',
            data: {
                amount: amount,
                payment_plugin: $payment.attr('data-plugin'),
                payment_title: $payment.attr('data-title'),
                token: '<?= LoginAuth::genToken() ?>'
            },
            success: function (res) {
                if (res.code == 200) {
                    layer.msg('正在跳转支付页面');
                    location.href = '<?= DC_URL ?>/?action=pay&out_trade_no=' + res.data.out_trade_no;
                    return;
                }
                layer.msg(res.msg || '提交失败', { icon: 2 });
            },
            error: function (xhr) {
                var msg = '提交失败，请稍后再试';
                try { msg = JSON.parse(xhr.responseText).msg || msg; } catch (e) {}
                layer.msg(msg, { icon: 2 });
            },
            complete: function () {
                layer.close(loadIndex);
                $btn.prop('disabled', <?= empty($balancePaymentList) ? 'true' : 'false' ?>);
            }
        });
    });

    // ====== 余额提现弹窗 ======
    $('#withdraw-btn').on('click', function () {
        var isMobile = window.innerWidth < 768;
        layer.open({
            id: 'withdraw',
            type: 2,
            title: '申请提现',
            area: isMobile ? ['98%', 'auto'] : ['500px', 'auto'],
            skin: 'layui-layer-wd',
            content: '?action=withdraw',
            fixed: false,
            maxmin: false,
            shadeClose: true,
            success: function (layero, index, that) {
                layer.iframeAuto(index);
                that.offset();
            }
        });
    });
});
</script>

<?php include __DIR__ . '/_pc_page_footer.php'; ?>
<script>
    $('#menu-balance').addClass('menu-current');
</script>
