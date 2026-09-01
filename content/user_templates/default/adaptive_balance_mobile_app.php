<?php
defined('DC_ROOT') || exit('access denied!');
$walletBalance = isset($user['money']) ? (float)$user['money'] : 0;
$balanceRechargeOptions = isset($rechargeAmountOptions) && is_array($rechargeAmountOptions) ? $rechargeAmountOptions : [10, 50, 100, 200, 500, 1000];
$balancePaymentList = isset($payment) && is_array($payment) ? $payment : [];
$virtualCurrencyNameEsc = htmlspecialchars(getVirtualCurrencyName(), ENT_QUOTES, 'UTF-8');
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
    $savedMethod = (string)($withdrawMethodOptions[0]['value'] ?? 'alipay');
}
$withdrawMethodIconMap = [
    'alipay' => ['class' => 'ri-alipay-fill', 'color' => '#1677ff'],
    'wechat' => ['class' => 'fa fa-wechat', 'color' => '#07c160'],
    'qq' => ['class' => 'fa fa-qq', 'color' => '#12b7f5'],
    'bank' => ['class' => 'fa fa-credit-card', 'color' => '#f59e0b'],
];
$balanceResolveIcon = function ($icon) {
    $icon = (string)$icon;
    if ($icon === '') return '';
    if (preg_match('/^(https?:)?\/\//i', $icon) || strpos($icon, '/') === 0) return $icon;
    return DC_URL . ltrim($icon, './');
};
$withdrawMinAmount = isset($withdrawMinAmount) ? (float)$withdrawMinAmount : 10;
$withdrawFeeRate = isset($withdrawFeeRate) ? (float)$withdrawFeeRate : 0;
$withdrawMinAmountText = number_format($withdrawMinAmount, 2, '.', '');
$withdrawFeeRateText = rtrim(rtrim(number_format($withdrawFeeRate, 2, '.', ''), '0'), '.');
if ($withdrawFeeRateText === '') $withdrawFeeRateText = '0';
?>
<style>
    * { box-sizing: border-box; }
    .uc-site-footer { display: none !important; }
    .wallet-page {
        min-height: 100vh;
        background: #f4f5f7;
        padding: 12px 12px calc(24px + env(safe-area-inset-bottom, 0px));
        -webkit-tap-highlight-color: transparent;
        -webkit-font-smoothing: antialiased;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }
    /* ===== 余额主卡 ===== */
    .wallet-hero {
        margin: 0;
        padding: 18px 18px 20px;
        background: linear-gradient(160deg, var(--theme-primary, #667eea) 0%, var(--theme-secondary, #764ba2) 100%);
        color: #fff;
        position: relative;
        overflow: hidden;
        border-radius: 18px;
        box-shadow: 0 10px 26px rgba(var(--tp-rgb), .18);
    }
    .wallet-hero::before {
        content: '';
        position: absolute;
        top: -60px;
        right: -40px;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: rgba(255,255,255,0.07);
        pointer-events: none;
    }
    .wallet-hero::after {
        content: '';
        position: absolute;
        bottom: -30px;
        left: 30%;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,0.04);
        pointer-events: none;
    }
    .wallet-hero-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
        position: relative;
        z-index: 1;
    }
    .wallet-hero-label {
        font-size: 13px;
        opacity: 0.8;
        font-weight: 400;
    }
    .wallet-hero-detail-btn {
        display: inline-flex;
        align-items: center;
        gap: 2px;
        font-size: 12px;
        color: rgba(255,255,255,0.75);
        text-decoration: none;
        padding: 4px 10px;
        border-radius: 14px;
        background: rgba(255,255,255,0.12);
        transition: background 0.15s;
    }
    .wallet-hero-detail-btn:active { background: rgba(255,255,255,0.2); }
    .wallet-hero-detail-btn i { font-size: 13px; }
    .wallet-hero-amount {
        font-size: 36px;
        line-height: 1;
        font-weight: 700;
        font-feature-settings: 'tnum';
        letter-spacing: -0.5px;
    }
    .wallet-hero-amount small {
        font-size: 20px;
        font-weight: 500;
        margin-right: 2px;
        opacity: 0.85;
        vertical-align: baseline;
    }
    .wallet-hero-credits {
        margin-top: 14px;
        font-size: 13px;
        color: rgba(255,255,255,0.65);
    }
    .wallet-hero-credits i {
        font-size: 12px;
        margin-right: 4px;
        opacity: 0.8;
    }
    /* ===== 通知条 ===== */
    .wallet-notice {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        margin-top: 12px;
        background: #fffbf0;
        border: 1px solid #fef0cd;
        border-radius: 16px;
        box-shadow: 0 6px 18px rgba(180,83,9,.05);
    }
    .wallet-notice > i {
        color: #f59e0b;
        font-size: 15px;
        flex-shrink: 0;
    }
    .wallet-notice-scroll {
        flex: 1;
        min-width: 0;
        height: 20px;
        overflow: hidden;
        position: relative;
    }
    .wallet-notice-list {
        animation: walletNoticeScroll 10s ease-in-out infinite;
    }
    .wallet-notice-item {
        height: 20px;
        line-height: 20px;
        font-size: 13px;
        color: #b45309;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    @keyframes walletNoticeScroll {
        0%, 12%   { transform: translateY(0); }
        25%, 37%  { transform: translateY(-20px); }
        50%, 62%  { transform: translateY(-40px); }
        75%, 87%  { transform: translateY(-60px); }
        100%      { transform: translateY(0); }
    }
    /* ===== 分段 Tabs ===== */
    .wallet-tabs{display: flex; margin: 12px 0 0; padding: 4px; background: linear-gradient(0deg, #fff, #f3f5f8); border: 2px solid #fff; border-radius: 10px; position: sticky; top: calc(50px + env(safe-area-inset-top, 0px) + 8px); z-index: 10; box-shadow: var(--shadow-primary);}
    .wallet-tab {
        flex: 1;
        border: 0;
        background: transparent;
        height: 38px;
        border-radius: 999px;
        color: #888;
        font-size: 14px;
        font-weight: 500;
        position: relative;
        transition: color 0.25s;
    }
    .wallet-tab.is-active {
        color: var(--theme-primary, #667eea);
        font-weight: 600;
        background: color-mix(in srgb, var(--theme-primary, #667eea) 8%, white);
        box-shadow: none; border-radius: 10px;
    }
    .wallet-tab-indicator {
        position: absolute;
        bottom: 4px;
        left: 0;
        height: 3px;
        border-radius: 3px;
        background: var(--theme-primary, #667eea);
        will-change: transform, width;
        transition: none;
    }
    /* ===== 面板 ===== */
    .wallet-panel-wrap {
        padding: 12px 0 0;
    }
    .wallet-panel { display: none; }
    .wallet-panel.is-active { display: block; }
    /* ===== 卡片 ===== */
    .wallet-card {
        background: linear-gradient(0deg, #fff, #f3f5f8);
        border-radius: 10px;
        padding: 18px 16px;
        margin: 0 0 12px;
        border: 2px solid #fff;
        box-shadow: var(--shadow-primary);
    }
    .wallet-card + .wallet-card { border-top: 2px solid #fff; }
    .wallet-withdraw-card { padding: 0; overflow: hidden; }
    .wallet-card-title {
        font-size: 16px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 4px;
    }
    .wallet-card-desc {
        color: #999;
        font-size: 13px;
        line-height: 1.5;
    }
    /* ===== 表单字段 ===== */
    .wallet-field { margin-bottom: 20px; }
    .wallet-field:last-child { margin-bottom: 0; }
    .wallet-field label {
        display: block;
        margin-bottom: 10px;
        color: #333;
        font-size: 14px;
        font-weight: 600;
    }
    .wallet-input {
        width: 100%;
        height: 50px;
        border: 1px solid #e8e8e8;
        border-radius: 10px;
        padding: 0 14px;
        background: #f9f9f9;
        color: #1a1a1a;
        font-size: 16px;
        outline: none;
        transition: border-color 0.2s, background 0.2s;
        box-sizing: border-box;
    }
    .wallet-input:focus { border-color: var(--theme-primary, #667eea); background: #fff; }
    .wallet-input::placeholder { color: #bbb; }
    /* ===== 按钮 ===== */
    .wallet-primary-btn,
    .wallet-secondary-btn {
        width: 100%;
        height: 50px;
        border-radius: 25px;
        font-size: 16px;
        font-weight: 600;
        margin-top: 24px;
        border: none;
        cursor: pointer;
        transition: transform 0.1s, opacity 0.15s;
        letter-spacing: 0.5px;
    }
    .wallet-primary-btn {
        background: linear-gradient(135deg, var(--theme-primary, #667eea), var(--theme-secondary, #764ba2));
        color: #fff;
        box-shadow: 0 4px 16px color-mix(in srgb, var(--theme-primary, #667eea) 30%, transparent);
    }
    .wallet-primary-btn:active { transform: scale(0.97); opacity: 0.9; }
    .wallet-primary-btn:disabled { opacity: 0.5; transform: none; }
    .wallet-secondary-btn {
        background: linear-gradient(135deg, var(--theme-primary, #667eea), var(--theme-secondary, #764ba2));
        color: #fff;
        box-shadow: 0 4px 16px color-mix(in srgb, var(--theme-primary, #667eea) 30%, transparent);
    }
    .wallet-secondary-btn:active { transform: scale(0.97); opacity: 0.9; }
    /* ===== 金额选择 ===== */
    .wallet-amount-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-top: 10px;
    }
    .wallet-amount-chip {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1px;
        height: 56px;
        border-radius: 12px;
        border: none;
        background: #f5f6f8;
        cursor: pointer;
        transition: all 0.2s;
        padding: 0;
        position: relative;
    }
    .wallet-amount-chip strong {
        font-size: 18px;
        font-weight: 700;
        color: #333;
        line-height: 1;
    }
    .wallet-amount-chip span {
        font-size: 12px;
        color: #999;
        margin-top: 2px;
    }
    .wallet-amount-chip.is-active {
        background: color-mix(in srgb, var(--theme-primary, #667eea) 8%, white);
        box-shadow: inset 0 0 0 1px var(--theme-primary, #667eea);
    }
    .wallet-amount-chip.is-active strong { color: var(--theme-primary, #667eea); }
    .wallet-amount-chip.is-active span { color: var(--theme-primary, #667eea); }
    /* ===== 带前缀的输入框 ===== */
    .wallet-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }
    .wallet-input-prefix {
        position: absolute;
        left: 14px;
        color: #999;
        font-size: 18px;
        font-weight: 600;
        pointer-events: none;
        z-index: 1;
    }
    .wallet-input-has-prefix { padding-left: 36px; }
    /* ===== 支付方式 ===== */
    .wallet-pay-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-top: 10px;
    }
    .wallet-pay-item {
        display: flex;
        align-items: center;
        gap: 8px;
        height: 58px;
        padding: 0 10px;
        border-radius: 12px;
        border: 1px solid #eee;
        background: #fafafa;
        cursor: pointer;
        transition: all 0.15s;
        position: relative;
        overflow: hidden;
    }
    .wallet-pay-icon {
        width: 30px;
        height: 30px;
        object-fit: contain;
        border-radius: 8px;
        flex-shrink: 0;
    }
    .wallet-pay-info { flex: 1; min-width: 0; overflow: hidden; }
    .wallet-pay-name {
        font-size: 13px;
        font-weight: 500;
        color: #333;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .wallet-pay-check {
        font-size: 18px;
        color: #ddd;
        transition: all 0.15s;
        flex-shrink: 0;
    }
    .wallet-pay-item.is-active {
        border-color: var(--theme-primary, #667eea);
        background: color-mix(in srgb, var(--theme-primary, #667eea) 6%, white);
    }
    .wallet-pay-item.is-active .wallet-pay-check { color: var(--theme-primary, #667eea); }
    .wallet-pay-empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 24px;
        color: #ccc;
        font-size: 14px;
        border-radius: 14px;
        background: #f9fafb;
    }
    /* ===== 提现表单 ===== */
    .wallet-upload-placeholder {
        width: 140px;
        height: 140px;
        margin: 20px auto 12px;
        border-radius: 14px;
        background: #f9f9f9;
        border: 2px dashed #ddd;
        color: #999;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-size: 13px;
        position: relative;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.18s;
    }
    .wallet-upload-placeholder:active { border-color: rgba(var(--tp-rgb),.3); background: color-mix(in srgb, var(--theme-primary, #667eea) 6%, white); }
    .wallet-upload-placeholder.is-uploaded { padding: 0; background: color-mix(in srgb, var(--theme-primary, #667eea) 6%, white); border-color: rgba(var(--tp-rgb),.3); border-style: solid; }
    .wallet-upload-placeholder img { width:100%; height:100%; object-fit:cover; display:none; }
    .wallet-upload-placeholder.is-uploaded img { display:block; }
    .wallet-upload-text { display:flex; flex-direction:column; align-items:center; justify-content:center; width:100%; height:100%; padding:14px; }
    .wallet-upload-placeholder.is-uploaded .wallet-upload-text { position:absolute; left:4px; right:4px; bottom:4px; width:auto; height:auto; padding:6px 8px; border-radius:8px; background:rgba(0,0,0,.5); color:#fff; backdrop-filter:blur(4px); }
    .wallet-upload-name { display:block; margin-top:6px; font-size:11px; color:#bbb; }
    .wallet-upload-placeholder.is-uploaded .wallet-upload-name { color:rgba(255,255,255,.8); margin-top:3px; }
    .wallet-upload-tip { padding: 0 16px 14px; color: #aaa; font-size: 12px; text-align: center; }
    .wallet-line-field {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 54px;
        padding: 0 16px;
        border-top: 0.5px solid #f0f0f0;
    }
    .wallet-line-field.is-textarea { align-items: flex-start; }
    .wallet-line-label {
        flex: 0 0 72px;
        color: #333;
        font-size: 15px;
        font-weight: 500;
    }
    .wallet-line-input,
    .wallet-line-textarea {
        flex: 1;
        min-width: 0;
        border: 0;
        background: transparent;
        color: #1a1a1a;
        font-size: 16px;
        outline: none;
        padding: 0;
    }
    .wallet-line-input { height: 54px; }
    .wallet-line-textarea { min-height: 72px; line-height: 1.7; padding: 14px 0; resize: none; }
    .wallet-line-input::placeholder, .wallet-line-textarea::placeholder { color: #bbb; }
    .wallet-amount-inline {
        flex: 1;
        min-width: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .wallet-amount-inline strong {
        flex: 0 0 auto;
        color: var(--theme-primary, #667eea);
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }
    .wallet-withdraw-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .wallet-withdraw-tag {
        display: inline-flex;
        align-items: center;
        height: 26px;
        padding: 0 10px;
        border-radius: 13px;
        background: #fff7ed;
        color: #c2410c;
        font-size: 12px;
        font-weight: 500;
    }
    .wallet-withdraw-calc {
        padding-top: 8px;
        color: #999;
        font-size: 12px;
        line-height: 1.8;
    }
    .wallet-withdraw-calc strong { color: var(--theme-primary, #667eea); font-weight: 600; }
    .wallet-radio {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 0;
        background: transparent;
        color: #333;
        font-size: 15px;
        padding: 0;
    }
    .wallet-radio-circle {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 2px solid #ddd;
        position: relative;
        box-sizing: border-box;
        background: #fff;
        transition: border-color 0.15s;
    }
    .wallet-radio.is-active .wallet-radio-circle { border-color: var(--theme-primary, #667eea); }
    .wallet-radio.is-active .wallet-radio-circle::after {
        content: '';
        position: absolute;
        inset: 3px;
        border-radius: 50%;
        background: var(--theme-primary, #667eea);
    }
    .wallet-submit-wrap { padding: 20px 16px 16px; }
    /* ===== 列表 ===== */
    .wallet-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .wallet-item {
        background: linear-gradient(0deg, #fff, #f3f5f8);
        border-radius: 10px;
        padding: 16px;
        border: 2px solid #fff;
        box-shadow: var(--shadow-primary);
    }
    .wallet-item:first-child { border-radius: 10px; }
    .wallet-item:last-child { border-bottom: 2px solid #fff; }
    .wallet-item:active { background: #fafafa; }
    .wallet-item-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 8px;
    }
    .wallet-item-title { color: #1a1a1a; font-size: 15px; font-weight: 600; }
    .wallet-item-amount { font-size: 15px; font-weight: 700; }
    .wallet-item-amount.plus { color: #16a34a; }
    .wallet-item-amount.minus { color: #ef4444; }
    .wallet-item-meta { color: #999; font-size: 12px; line-height: 1.9; }
    .wallet-status {
        display: inline-flex;
        align-items: center;
        height: 22px;
        padding: 0 8px;
        border-radius: 4px;
        background: color-mix(in srgb, var(--theme-primary, #667eea) 8%, white);
        color: var(--theme-primary, #667eea);
        font-size: 11px;
        font-weight: 600;
    }
    .wallet-empty {
        padding: 60px 20px;
        text-align: center;
        color: #ccc;
        font-size: 14px;
        background: linear-gradient(0deg, #fff, #f3f5f8);
        border-radius: 10px;
        border: 2px solid #fff;
        box-shadow: var(--shadow-primary);
    }
    .wallet-empty svg {
        display: block;
        width: 140px;
        height: auto;
        margin: 0 auto 10px;
    }
    .wallet-more {
        width: calc(100% - 32px);
        height: 44px;
        border: 0;
        border-radius: 22px;
        background: #fff;
        color: #666;
        font-size: 14px;
        font-weight: 500;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        margin: 14px 16px 0;
    }
    .wallet-more:active { background: #f5f5f5; }
    .wallet-card-key-input { font-family: 'SF Mono', Menlo, Consolas, monospace; letter-spacing: 1.5px; }
    .wallet-card-tips { margin-top: 16px; display: grid; gap: 8px; }
    .wallet-card-tip-item {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        color: #999;
        font-size: 13px;
        line-height: 1.6;
    }
    .wallet-card-tip-item i { color: var(--theme-primary, #667eea); margin-top: 3px; flex-shrink: 0; font-size: 12px; }
    .wallet-log-intro {
        margin: 0 0 10px;
        padding: 14px 16px;
        background: linear-gradient(0deg, #fff, #f3f5f8);
        border: 2px solid #fff;
        border-radius: 10px;
        color: #999;
        font-size: 12px;
        line-height: 1.8;
        box-shadow: var(--shadow-primary);
    }
    /* ===== 卡片头部（图标+标题） ===== */
    .wallet-card-header,
    .wallet-withdraw-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }
    .wallet-card-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 18px;
        flex-shrink: 0;
    }
    /* ===== 提现表单分组 ===== */
    .wallet-upload-area {
        padding: 0 16px;
    }
    .wallet-form-group {
        background: #f9f9f9;
        border-radius: 12px;
        margin: 0 16px;
        overflow: hidden;
    }
    .wallet-form-group .wallet-line-field {
        padding: 0 14px;
        border-top-color: #eee;
    }
    .wallet-form-group .wallet-line-field:first-child { border-top: none; }
    .wallet-withdraw-info-inner {
        padding: 12px 14px;
        border-top: 0.5px solid #f0f0f0;
    }
    .wallet-withdraw-info {
        padding: 14px 16px 0;
    }
    .wallet-withdraw-header {
        padding: 20px 16px 0;
    }
    .wallet-method-section {
        padding: 16px 16px 0;
    }
    .wallet-method-section label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #333;
        margin-bottom: 10px;
    }
    .wallet-method-chips {
        display: flex;
        gap: 10px;
    }
    .wallet-method-chip {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        height: 44px;
        border-radius: 10px;
        border: 1px solid #e8e8e8;
        background: #f9f9f9;
        font-size: 14px;
        font-weight: 500;
        color: #333;
        padding: 0;
        cursor: pointer;
        transition: all 0.15s;
    }
    .wallet-method-chip i { font-size: 16px; }
    .wallet-method-chip.is-active {
        border-color: var(--theme-primary, #667eea);
        background: color-mix(in srgb, var(--theme-primary, #667eea) 8%, white);
        color: var(--theme-primary, #667eea);
    }
    .wallet-method-chip .wallet-radio-circle { display: none; }
</style>
<div class='wallet-page'>
    <div class='wallet-hero'>
        <div class='wallet-hero-top'>
            <div class='wallet-hero-label'>账户余额</div>
            <a class='wallet-hero-detail-btn' href='/user/balance.php?action=balance_log'>收支明细 <i class='fa fa-angle-right'></i></a>
        </div>
        <div class='wallet-hero-amount'><small>¥</small><span id='walletBalanceMain'><?= number_format($walletBalance, 2) ?></span></div>
        <div class='wallet-hero-credits'><i class='fa fa-diamond'></i>账户<?= $virtualCurrencyNameEsc ?> <?= (int)($user['credits'] ?? 0) ?></div>
    </div>
    <div class='wallet-notice'>
        <i class='fa fa-volume-up'></i>
        <div class='wallet-notice-scroll'>
            <div class='wallet-notice-list'>
                <div class='wallet-notice-item'>余额提现申请成功后，请耐心等待客服打款！</div>
                <div class='wallet-notice-item'>若在线充值未到账，可联系客服处理！</div>
                <div class='wallet-notice-item'>升级本站高级会员后，可享受更多福利！</div>
                <div class='wallet-notice-item'>充值卡密可通过加入官方群后购买获取！</div>
            </div>
        </div>
    </div>
    <div class='wallet-tabs'>
        <button type='button' class='wallet-tab is-active' data-tab='recharge'>在线充值</button>
        <button type='button' class='wallet-tab' data-tab='card'>卡密充值</button>
        <button type='button' class='wallet-tab' data-tab='withdraw'>余额提现</button>
        <button type='button' class='wallet-tab' data-tab='withdraw-log'>提现日志</button>
        <div class='wallet-tab-indicator' id='walletTabIndicator'></div>
    </div>
    <div class='wallet-panel-wrap'>
        <!-- ===== 在线充值 ===== -->
        <div class='wallet-panel is-active' data-panel='recharge'>
            <div class='wallet-card'>
                <div class='wallet-field'>
                    <label>选择金额</label>
                    <div class='wallet-amount-grid'>
                        <?php foreach($balanceRechargeOptions as $index => $amount): ?>
                        <button type='button' class='wallet-amount-chip wallet-amount-tag<?= $index === 0 ? ' is-active' : '' ?>' data-amount='<?= htmlspecialchars($amount) ?>'>
                            <strong><?= intval($amount) ?></strong><span>元</span>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class='wallet-field'>
                    <label>自定义金额</label>
                    <div class='wallet-input-wrap'>
                        <span class='wallet-input-prefix'>¥</span>
                        <input type='number' class='wallet-input wallet-input-has-prefix' id='walletRechargeAmount' placeholder='1 - 10000' min='1' max='10000' step='0.01' value='<?= !empty($balanceRechargeOptions[0]) ? htmlspecialchars($balanceRechargeOptions[0]) : '' ?>'>
                    </div>
                </div>
                <div class='wallet-field'>
                    <label>支付方式</label>
                    <div class='wallet-pay-grid'>
                        <?php if (!empty($balancePaymentList)): ?>
                            <?php foreach($balancePaymentList as $index => $payItem): ?>
                            <div class='wallet-pay-item wallet-payment-method<?= !empty($payItem['active']) || $index === 0 ? ' is-active' : '' ?>' data-plugin='<?= htmlspecialchars($payItem['plugin_name']) ?>' data-title='<?= htmlspecialchars($payItem['title']) ?>'>
                                <img src='<?= htmlspecialchars($balanceResolveIcon($payItem['icon'] ?? '')) ?>' alt='<?= htmlspecialchars($payItem['title']) ?>' class='wallet-pay-icon'>
                                <div class='wallet-pay-info'><div class='wallet-pay-name'><?= htmlspecialchars($payItem['title']) ?></div></div>
                                <i class='wallet-pay-check fa fa-check-circle'></i>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class='wallet-pay-empty'>暂未开启在线支付通道</div>
                        <?php endif; ?>
                    </div>
                </div>
                <button type='button' class='wallet-primary-btn' id='walletRechargeBtn'<?= empty($balancePaymentList) ? ' disabled' : '' ?>>确认充值</button>
            </div>
        </div>
        <!-- ===== 卡密充值 ===== -->
        <div class='wallet-panel' data-panel='card'>
            <div class='wallet-card'>
                <div class='wallet-field'>
                    <label>充值卡密</label>
                    <div class='wallet-input-wrap'>
                        <span class='wallet-input-prefix'><i class='fa fa-key'></i></span>
                        <input type='text' class='wallet-input wallet-input-has-prefix wallet-card-key-input' id='walletCardKeyInput' placeholder='请输入充值卡密' maxlength='64' autocomplete='off'>
                    </div>
                </div>
                <div class='wallet-card-tips'>
                    <div class='wallet-card-tip-item'><i class='fa fa-check-circle'></i>输入卡密后点击充值，余额实时到账</div>
                    <div class='wallet-card-tip-item'><i class='fa fa-check-circle'></i>每张卡密仅可使用一次，请妥善保管</div>
                    <div class='wallet-card-tip-item'><i class='fa fa-info-circle'></i>如遇问题请联系客服处理</div>
                </div>
                <button type='button' class='wallet-secondary-btn' id='walletCardBtn'>立即充值</button>
            </div>
        </div>
        <!-- ===== 余额提现 ===== -->
        <div class='wallet-panel' data-panel='withdraw'>
            <form class='wallet-card wallet-withdraw-card' id='walletWithdrawForm'>
                <input type='hidden' name='token' value='<?= LoginAuth::genToken() ?>'>
                <input type='hidden' name='method' id='walletWithdrawMethod' value='<?= htmlspecialchars($savedMethod, ENT_QUOTES) ?>'>
                <input type='hidden' name='receipt_image' id='walletReceiptImage' value='<?= htmlspecialchars($savedWithdrawReceiptImage, ENT_QUOTES) ?>'>
                <div class='wallet-upload-area'>
                    <div class='wallet-upload-placeholder<?= $hasSavedWithdrawReceipt ? ' is-uploaded' : '' ?>' id='walletReceiptUpload'>
                        <img id='walletReceiptPreview' src='<?= htmlspecialchars($savedWithdrawReceiptImageUrl, ENT_QUOTES) ?>' alt='收款码'>
                        <div class='wallet-upload-text'>
                            <i class='fa fa-qrcode' style='font-size:24px;margin-bottom:6px;opacity:.4;'></i>
                            <span id='walletReceiptText'><?= $hasSavedWithdrawReceipt ? '点击重新上传' : '上传收款码' ?></span>
                            <small class='wallet-upload-name' id='walletReceiptName'><?= $hasSavedWithdrawReceipt ? '已保存默认收款码' : '' ?></small>
                        </div>
                    </div>
                </div>
                <div class='wallet-form-group'>
                    <div class='wallet-line-field'>
                        <div class='wallet-line-label'>收款姓名</div>
                        <input type='text' class='wallet-line-input' name='realname' placeholder='请填写真实姓名' value='<?= htmlspecialchars($savedRealname, ENT_QUOTES) ?>'>
                    </div>
                    <div class='wallet-line-field'>
                        <div class='wallet-line-label'>提现账号</div>
                        <input type='text' class='wallet-line-input' name='account' placeholder='请填写收款账号' value='<?= htmlspecialchars($savedAccount, ENT_QUOTES) ?>'>
                    </div>
                    <div class='wallet-line-field'>
                        <div class='wallet-line-label'>提现金额</div>
                        <div class='wallet-amount-inline'>
                            <input type='number' class='wallet-line-input' id='walletWithdrawAmount' name='amount' placeholder='请输入金额' min='<?= $withdrawMinAmount ?>' step='0.01'>
                            <strong>可提 ¥<span id='walletBalanceInline'><?= number_format($walletBalance, 2) ?></span></strong>
                        </div>
                    </div>
                    <div class='wallet-line-field is-textarea'>
                        <div class='wallet-line-label'>提现备注</div>
                        <textarea class='wallet-line-textarea' name='remark' placeholder='选填，管理员可见'></textarea>
                    </div>
                    <div class='wallet-withdraw-info-inner'>
                        <div class='wallet-withdraw-calc' id='walletWithdrawCalcTip'>输入金额后自动计算到账金额</div>
                    </div>
                </div>
                <div class='wallet-withdraw-info'>
                    <div class='wallet-withdraw-tags'>
                        <span class='wallet-withdraw-tag'><?= rtrim(rtrim($withdrawMinAmountText, '0'), '.') ?>元起提</span>
                        <span class='wallet-withdraw-tag'>费率<?= htmlspecialchars($withdrawFeeRateText, ENT_QUOTES) ?>%</span>
                    </div>
                </div>
                <div class='wallet-method-section'>
                    <label>提现方式</label>
                    <div class='wallet-method-chips'>
                        <?php foreach ($withdrawMethodOptions as $methodOption): ?>
                            <?php
                            $methodValue = (string)($methodOption['value'] ?? '');
                            if ($methodValue === '') continue;
                            $methodLabel = (string)($methodOption['label'] ?? $methodValue);
                            $methodMeta = $withdrawMethodIconMap[$methodValue] ?? ['class' => 'fa fa-credit-card', 'color' => '#667eea'];
                            ?>
                            <button type='button' class='wallet-method-chip wallet-radio<?= $savedMethod === $methodValue ? ' is-active' : '' ?>' data-method='<?= htmlspecialchars($methodValue, ENT_QUOTES) ?>'>
                                <i class='<?= htmlspecialchars($methodMeta['class'], ENT_QUOTES) ?>' style='color:<?= htmlspecialchars($methodMeta['color'], ENT_QUOTES) ?>;'></i><?= htmlspecialchars($methodLabel, ENT_QUOTES) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class='wallet-submit-wrap'>
                    <button type='submit' class='wallet-primary-btn'>提交提现申请</button>
                </div>
            </form>
        </div>
        <!-- ===== 提现日志 ===== -->
        <div class='wallet-panel' data-panel='withdraw-log'>
            <div id='walletWithdrawList' class='wallet-list'></div>
            <button type='button' class='wallet-more' id='walletWithdrawMore'>加载更多</button>
        </div>
    </div>
</div>

<template id="dcEmptyIllust"><?php include __DIR__ . '/_svg_empty.php'; ?></template>

<script>
layui.use(['layer', 'upload'], function(){
    var $ = layui.$;
    var layer = layui.layer;
    var upload = layui.upload;
    var walletBalance = <?= json_encode(round($walletBalance, 2)) ?>;
    var withdrawMinAmount = <?= json_encode((float)$withdrawMinAmount) ?>;
    var withdrawFeeRate = <?= json_encode((float)$withdrawFeeRate) ?>;
    var initialReceiptPath = <?= json_encode($savedWithdrawReceiptImage) ?>;
    var initialReceiptUrl = <?= json_encode($savedWithdrawReceiptImageUrl) ?>;
    var withdrawPage = 1;
    var withdrawLimit = 10;
    var withdrawLoading = false;
    var withdrawFinished = false;
    var walletUploadLoading = 0;
    var initialWithdrawMethod = <?= json_encode($savedMethod) ?> || 'alipay';
    var _emptySvg = (document.getElementById('dcEmptyIllust') || {}).innerHTML || '';

    function escapeHtml(str) {
        return $('<div/>').text(str == null ? '' : String(str)).html();
    }

    var $indicator = $('#walletTabIndicator');
    var indicatorAnimTimer = null;

    function moveIndicator($tab, animate) {
        if (!$tab.length) return;
        var tabLeft = $tab.position().left;
        var tabWidth = $tab.outerWidth();
        var indicatorW = 24;
        var targetLeft = tabLeft + (tabWidth - indicatorW) / 2;

        if (!animate || !$indicator.data('inited')) {
            $indicator.css({ left: targetLeft + 'px', width: indicatorW + 'px', transition: 'none' });
            $indicator.data('inited', true);
            return;
        }

        var curLeft = parseFloat($indicator.css('left')) || 0;
        var goRight = targetLeft > curLeft;
        var stretchLeft = goRight ? curLeft : targetLeft;
        var stretchWidth = Math.abs(targetLeft - curLeft) + indicatorW;

        if (indicatorAnimTimer) { clearTimeout(indicatorAnimTimer); indicatorAnimTimer = null; }

        // phase 1: stretch
        $indicator.css({
            left: stretchLeft + 'px',
            width: stretchWidth + 'px',
            transition: 'left 0.16s cubic-bezier(.4,0,.2,1), width 0.16s cubic-bezier(.4,0,.2,1)'
        });

        // phase 2: shrink to target
        indicatorAnimTimer = setTimeout(function() {
            $indicator.css({
                left: targetLeft + 'px',
                width: indicatorW + 'px',
                transition: 'left 0.13s cubic-bezier(.4,0,.2,1), width 0.13s cubic-bezier(.4,0,.2,1)'
            });
        }, 200);
    }

    function setActiveTab(name) {
        $('.wallet-tab').removeClass('is-active');
        var $tab = $('.wallet-tab[data-tab="' + name + '"]');
        $tab.addClass('is-active');
        $('.wallet-panel').removeClass('is-active');
        $('.wallet-panel[data-panel="' + name + '"]').addClass('is-active');
        moveIndicator($tab, true);
    }

    // init: honour URL hash to open a specific tab
    var _initHash = (location.hash || '').replace('#', '');
    if (_initHash && $('.wallet-tab[data-tab="' + _initHash + '"]').length) {
        setActiveTab(_initHash);
    }
    moveIndicator($('.wallet-tab.is-active'), false);

    function syncWalletBalanceDisplay() {
        var value = walletBalance.toFixed(2);
        $('#walletBalanceMain').text(value);
        $('#walletBalanceAvailable').text(value);
        $('#walletBalanceInline').text(value);
    }

    function setWalletReceipt(path, url, name) {
        if (!path || !url) {
            return;
        }
        $('#walletReceiptImage').val(path);
        $('#walletReceiptPreview').attr('src', url);
        $('#walletReceiptText').text('点击重新上传');
        $('#walletReceiptName').text(name || '收款码已上传');
        $('#walletReceiptUpload').addClass('is-uploaded');
    }

    function applyWalletSavedReceipt() {
        if (!initialReceiptPath || !initialReceiptUrl) {
            return false;
        }
        setWalletReceipt(initialReceiptPath, initialReceiptUrl, '已保存默认收款码');
        return true;
    }

    function resetWalletReceipt() {
        $('#walletReceiptImage').val('');
        $('#walletReceiptPreview').attr('src', '');
        $('#walletReceiptText').text('点击上传收款码');
        $('#walletReceiptName').text('支持 JPG/PNG/GIF/WEBP，最大 2MB');
        $('#walletReceiptUpload').removeClass('is-uploaded');
        applyWalletSavedReceipt();
    }

    function resetWithdrawList() {
        withdrawPage = 1;
        withdrawLoading = false;
        withdrawFinished = false;
        $('#walletWithdrawList').empty();
        $('#walletWithdrawMore').text('加载更多').prop('disabled', false);
    }

    function formatMoney(value) {
        var num = parseFloat(value || 0);
        if (isNaN(num)) num = 0;
        return num.toFixed(2);
    }

    function updateWalletWithdrawCalcTip() {
        var amount = parseFloat($.trim($('#walletWithdrawAmount').val()) || '0');
        if (isNaN(amount) || amount <= 0) {
            $('#walletWithdrawCalcTip').text('手续费率 ' + withdrawFeeRate + '% · 输入金额后自动计算到账金额，用户余额按申请金额全额扣减');
            return;
        }
        var fee = Math.max(0, Math.min(amount, Math.round(amount * withdrawFeeRate) / 100));
        fee = Math.round(fee * 100) / 100;
        var actual = Math.max(0, Math.round((amount - fee) * 100) / 100);
        $('#walletWithdrawCalcTip').html('本次将扣余额 <strong>¥' + formatMoney(amount) + '</strong> · 手续费 <strong>¥' + formatMoney(fee) + '</strong> · 预计到账 <strong>¥' + formatMoney(actual) + '</strong>');
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

    function renderWithdrawList(list, append) {
        var html = '';
        if (!list.length && !append) {
            $('#walletWithdrawList').html('<div class=\'wallet-empty\'>' + _emptySvg + '暂无提现记录</div>');
            return;
        }
        $.each(list, function(_, item){
            var statusText = item.status_text || (item.status == 1 ? '已通过' : (item.status == 2 ? '已驳回' : '待审核'));
            var settleInfo = getWithdrawSettleInfo(item);
            var handleText = getWithdrawHandleText(item);
            html += '<div class=\'wallet-item\'>'
                + '<div class=\'wallet-item-top\'>'
                + '<div class=\'wallet-item-title\'>提现金额：¥' + escapeHtml(item.amount_text || item.amount || '0.00') + '</div>'
                + '<span class=\'wallet-status\'>' + escapeHtml(statusText) + '</span>'
                + '</div>'
                + '<div class=\'wallet-item-meta\'>'
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
            $('#walletWithdrawList').append(html);
        } else {
            $('#walletWithdrawList').html(html);
        }
    }

    function loadWithdrawList() {
        if (withdrawLoading || withdrawFinished) {
            return;
        }
        withdrawLoading = true;
        var idx = layer.load(2, {shade: 0.06});
        $.getJSON('?action=withdraw_list&page=' + withdrawPage + '&limit=' + withdrawLimit, function(res){
            if (res.code === 0) {
                var list = res.data || [];
                if (withdrawPage === 1 && !list.length) {
                    $('#walletWithdrawMore').hide();
                } else {
                    $('#walletWithdrawMore').show();
                }
                renderWithdrawList(list, withdrawPage > 1);
                if (list.length < withdrawLimit) {
                    withdrawFinished = true;
                    $('#walletWithdrawMore').text('没有更多了').prop('disabled', true);
                }
                withdrawPage++;
            } else {
                layer.msg(res.msg || '加载失败');
            }
        }).fail(function(){
            layer.msg('加载失败');
        }).always(function(){
            withdrawLoading = false;
            layer.close(idx);
        });
    }

    $(document).on('click', '.wallet-tab', function(){
        var name = $(this).attr('data-tab');
        setActiveTab(name);
    });

    $(document).on('click', '.wallet-payment-method', function(){
        $('.wallet-payment-method').removeClass('is-active');
        $(this).addClass('is-active');
    });

    $(document).on('click', '.wallet-amount-tag', function(){
        $(this).addClass('is-active').siblings('.wallet-amount-tag').removeClass('is-active');
        $('#walletRechargeAmount').val($(this).attr('data-amount') || '');
    });

    $('#walletRechargeAmount').on('input', function(){
        $('.wallet-amount-tag').removeClass('is-active');
    });

    $(document).on('click', '.wallet-radio', function(){
        var method = $(this).attr('data-method') || initialWithdrawMethod;
        $('.wallet-radio').removeClass('is-active');
        $(this).addClass('is-active');
        $('#walletWithdrawMethod').val(method);
    });

    $('#walletWithdrawAmount').on('input change', updateWalletWithdrawCalcTip);

    upload.render({
        elem: '#walletReceiptUpload',
        url: '<?= DC_URL ?>user/api.php?action=upload_withdraw_receipt_image',
        field: 'file',
        accept: 'images',
        exts: 'jpg|jpeg|png|gif|webp',
        size: 2048,
        data: {
            token: '<?= LoginAuth::genToken() ?>'
        },
        before: function(){
            walletUploadLoading = layer.load(2, {shade: 0.06});
        },
        done: function(res){
            if (walletUploadLoading) {
                layer.close(walletUploadLoading);
                walletUploadLoading = 0;
            }
            if (!res || res.code != 200) {
                return layer.msg((res && res.msg) || '上传失败', {icon: 2, time: 2600});
            }
            initialReceiptPath = (res.data && res.data.path) || '';
            initialReceiptUrl = (res.data && res.data.url) || '';
            setWalletReceipt(initialReceiptPath, initialReceiptUrl, (res.data && res.data.name) || '收款码已上传');
        },
        error: function(){
            if (walletUploadLoading) {
                layer.close(walletUploadLoading);
                walletUploadLoading = 0;
            }
            layer.msg('上传失败，请稍后重试', {icon: 2, time: 2600});
        }
    });

    $('#walletRechargeBtn').on('click', function(){
        var amountValue = parseFloat($.trim($('#walletRechargeAmount').val()) || '0');
        var $payment = $('.wallet-payment-method.is-active').first();
        if (!amountValue || amountValue < 1 || amountValue > 10000) {
            return layer.msg('充值金额需在 1 - 10000 元之间');
        }
        if (!$payment.length) {
            return layer.msg('请选择支付方式');
        }
        var $btn = $(this);
        var loadIndex = layer.load(2, {shade: 0.06});
        $btn.prop('disabled', true);
        $.ajax({
            type: 'POST',
            url: '?action=recharge_ajax',
            dataType: 'json',
            data: {
                amount: amountValue,
                payment_plugin: $payment.attr('data-plugin'),
                payment_title: $payment.attr('data-title'),
                token: '<?= LoginAuth::genToken() ?>'
            },
            success: function(e){
                layer.close(loadIndex);
                if (e.code == 200) {
                    layer.msg('正在跳转支付页面');
                    location.href = '<?= DC_URL ?>/?action=pay&out_trade_no=' + e.data.out_trade_no;
                    return;
                }
                $btn.prop('disabled', <?= empty($balancePaymentList) ? 'true' : 'false' ?>);
                layer.msg(e.msg || '提交失败');
            },
            error: function(xhr){
                layer.close(loadIndex);
                $btn.prop('disabled', <?= empty($balancePaymentList) ? 'true' : 'false' ?>);
                var msg = '提交失败，请稍后重试';
                try { msg = JSON.parse(xhr.responseText).msg || msg; } catch (ex) {}
                layer.msg(msg, {icon: 2, time: 2600});
            }
        });
    });

    $('#walletCardBtn').on('click', function(){
        var cardKey = $.trim($('#walletCardKeyInput').val());
        if (!cardKey) {
            return layer.msg('请输入充值卡密');
        }
        var $btn = $(this);
        var loadIndex = layer.load(2, {shade: 0.06});
        $btn.prop('disabled', true);
        $.ajax({
            type: 'POST',
            url: '?action=card_redeem_ajax',
            dataType: 'json',
            data: {
                card_key: cardKey,
                token: '<?= LoginAuth::genToken() ?>'
            },
            success: function(e){
                if (e.code == 200) {
                    var amt = parseFloat(e.data.amount || 0);
                    if (amt > 0) {
                        walletBalance += amt;
                        syncWalletBalanceDisplay();
                    }
                    $('#walletCardKeyInput').val('');
                    layer.msg('充值成功，面额 ¥' + (e.data.amount || ''), {icon: 1, time: 2500});
                    return;
                }
                layer.msg(e.msg || '充值失败');
            },
            error: function(xhr){
                var msg = '充值失败，请稍后再试';
                try { msg = JSON.parse(xhr.responseText).msg || msg; } catch (ex) {}
                layer.msg(msg, {icon: 2, time: 2600});
            },
            complete: function(){
                layer.close(loadIndex);
                $btn.prop('disabled', false);
            }
        });
    });

    $('#walletWithdrawForm').on('submit', function(event){
        event.preventDefault();
        var amountValue = parseFloat($.trim($(this).find('[name="amount"]').val()) || '0');
        if (!amountValue || amountValue <= 0) {
            return layer.msg('请输入有效的提现金额', {icon: 2, time: 2600});
        }
        if (withdrawMinAmount > 0 && amountValue < withdrawMinAmount) {
            return layer.msg('最低提现金额为 ¥' + formatMoney(withdrawMinAmount), {icon: 2, time: 2600});
        }
        if (amountValue > walletBalance) {
            return layer.msg('提现金额不能大于当前可用余额', {icon: 2, time: 2600});
        }
        var loadIndex = layer.load(2, {shade: 0.06});
        $.ajax({
            type: 'POST',
            url: '?action=withdraw_ajax',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(e){
                layer.close(loadIndex);
                if (e.code == 400) {
                    return layer.msg(e.msg, {icon: 2, time: 2600});
                }
                if (amountValue > 0) {
                    walletBalance = Math.max(0, walletBalance - amountValue);
                    syncWalletBalanceDisplay();
                }
                $('#walletWithdrawForm')[0].reset();
                resetWalletReceipt();
                $('#walletWithdrawMethod').val(initialWithdrawMethod);
                $('.wallet-radio').removeClass('is-active');
                $('.wallet-radio[data-method="' + initialWithdrawMethod + '"]').addClass('is-active');
                updateWalletWithdrawCalcTip();
                resetWithdrawList();
                setActiveTab('withdraw-log');
                loadWithdrawList();
                layer.msg(e.msg || '提现申请已提交，请等待审核', {icon: 1, time: 2200});
            },
            error: function(xhr){
                layer.close(loadIndex);
                var msg = '提交失败，请稍后重试';
                try { msg = JSON.parse(xhr.responseText).msg || msg; } catch (ex) {}
                layer.msg(msg, {icon: 2, time: 2600});
            }
        });
    });

    $('#walletWithdrawMore').on('click', loadWithdrawList);

    syncWalletBalanceDisplay();
    updateWalletWithdrawCalcTip();
    loadWithdrawList();

    // ===== 手势左右滑动切换 Tab =====
    var tabNames = ['recharge', 'card', 'withdraw', 'withdraw-log'];
    var touchStartX = 0, touchStartY = 0, touchMoved = false;
    var $panelWrap = $('.wallet-page');

    function getCurrentTabIndex() {
        var current = $('.wallet-tab.is-active').attr('data-tab');
        var idx = tabNames.indexOf(current);
        return idx >= 0 ? idx : 0;
    }

    $panelWrap.on('touchstart', function(e) {
        var t = e.originalEvent.touches[0];
        touchStartX = t.clientX;
        touchStartY = t.clientY;
        touchMoved = false;
    });

    $panelWrap.on('touchmove', function(e) {
        if (touchMoved) return;
        var t = e.originalEvent.touches[0];
        var dx = t.clientX - touchStartX;
        var dy = t.clientY - touchStartY;
        if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 30) {
            touchMoved = true;
            var idx = getCurrentTabIndex();
            if (dx < 0 && idx < tabNames.length - 1) {
                setActiveTab(tabNames[idx + 1]);
            } else if (dx > 0 && idx > 0) {
                setActiveTab(tabNames[idx - 1]);
            }
        }
    });
});
</script>
