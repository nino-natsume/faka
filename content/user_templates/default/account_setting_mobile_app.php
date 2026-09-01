<?php
defined('DC_ROOT') || exit('access denied!');
$settingName = !empty($user['nickname']) ? $user['nickname'] : $user['username'];
$settingUsername = isset($user['username']) ? $user['username'] : '';
$settingEmail = isset($user['email']) ? $user['email'] : '';
$settingTel = isset($user['tel']) ? $user['tel'] : '';
$settingWechat = trim((string)($user['wechat'] ?? ''));
$settingUid = isset($user['uid']) ? (int)$user['uid'] : (int)UID;
$settingAvatar = User::getAvatar($user['photo'] ?? '');
$settingDockingScheme = 'http';
if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')) {
    $settingDockingScheme = 'https';
}
$settingDockingHost = $_SERVER['HTTP_HOST'] ?? parse_url(DC_URL, PHP_URL_HOST) ?? '';
$settingDockingLink = $settingDockingHost !== '' ? $settingDockingScheme . '://' . $settingDockingHost : rtrim(DC_URL, '/');
$settingInitialSource = $settingUsername ?: 'U';
$settingInitial = function_exists('mb_substr') ? mb_substr($settingInitialSource, 0, 1, 'UTF-8') : substr($settingInitialSource, 0, 1);
$bindCount = 0;
if (!empty($user['photo'])) {
    $bindCount++;
}
if (!empty($settingTel)) {
    $bindCount++;
}
if (!empty($settingEmail)) {
    $bindCount++;
}
$safetyText = $bindCount >= 3 ? '高' : ($bindCount == 2 ? '中' : '基础');

// 邮箱绑定状态
$_emailBound = !empty($settingEmail);
$_hasOtherLogin = !empty(trim($settingUsername)) || !empty(trim($settingTel));
$_canUnbindEmail = $_emailBound && $_hasOtherLogin;
$_maskedEmail = '';
if ($_emailBound) {
    $_ep = explode('@', $settingEmail);
    if (count($_ep) === 2) {
        $_l = $_ep[0];
        $_maskedEmail = (mb_strlen($_l, 'UTF-8') > 2 ? mb_substr($_l, 0, 2, 'UTF-8') . '***' : $_l . '***') . '@' . $_ep[1];
    } else {
        $_maskedEmail = $settingEmail;
    }
}

// 手机绑定状态
$_telBound = !empty($settingTel);
$_hasOtherLoginForTel = !empty(trim($settingUsername)) || !empty(trim($settingEmail));
$_canUnbindTel = $_telBound && $_hasOtherLoginForTel;
$_maskedTel = '';
if ($_telBound) {
    $_maskedTel = substr($settingTel, 0, 3) . '****' . substr($settingTel, -4);
}
?>
<link rel="stylesheet" href="<?= DC_URL ?>admin/views/css/cropper.min.css">
<script src="<?= DC_URL ?>admin/views/js/cropper.min.js"></script>
<style>
    /* APP移动端信息设置页：参考粉丝、钱包、明细页的卡片式视觉 */
    .uc-site-footer { display: none !important; }
    .aset-page,
    .aset-page * {
        box-sizing: border-box;
        -webkit-tap-highlight-color: transparent;
    }
    .aset-page {
        --aset-primary: var(--theme-primary, #ff4a58);
        --aset-primary-rgb: var(--tp-rgb, 255,74,88);
        --aset-soft: rgba(var(--aset-primary-rgb), .10);
        display: block !important;
        min-height: 100vh;
        padding: 12px 12px calc(76px + env(safe-area-inset-bottom, 0px)) !important;
        background: #f5f5f6;
        color: #20242c;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }
    .aset-grid-layout {
        display: block;
    }
    .aset-panel {
        padding: 0;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }
    .aset-panel-head {
        display: none;
    }
    .aset-tabs{position: sticky; top: calc(50px + env(safe-area-inset-top, 0px)); z-index: 10; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 6px; margin: 0 -2px 12px; padding: 4px; overflow: visible; border: 2px solid #fff; border-radius: 10px; background: linear-gradient(0deg, #fff, #f3f5f8); box-shadow: var(--shadow-primary); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);}
    .aset-tab {
        flex: 1 1 auto;
        min-width: 0;
        height: 34px;
        padding: 0 6px;
        border: 0;
        border-radius: 999px;
        background: transparent;
        color: #697180;
        box-shadow: none;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
        position: relative;
        z-index: 1;
    }
    .aset-tab.is-active {
        color: var(--aset-primary);
        background: var(--aset-soft);
        border-color: transparent;
        box-shadow: none; border-radius: 10px;
    }
    .aset-tab-indicator {
        position: absolute;
        left: 0;
        bottom: 4px;
        width: 24px;
        height: 3px;
        border-radius: 999px;
        background: var(--aset-primary);
        z-index: 2;
        will-change: left, width;
        pointer-events: none;
    }
    .aset-panels {
        display: block;
        touch-action: pan-y;
    }
    .aset-panel-item {
        display: none;
    }
    .aset-panel-item.is-active {
        display: block;
    }
    #accountSettingForm {
        display: grid;
        gap: 12px;
    }
    .aset-avatar-row,
    .aset-form-grid,
    .aset-panel-item[data-panel="quick"].is-active,
    .aset-secret {
        margin: 0;
        padding: 16px;
        border: 2px solid #fff;
        border-radius: 10px;
        background: linear-gradient(0deg, #fff, #f3f5f8);
        box-shadow: var(--shadow-primary);
    }
    .aset-avatar-row {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .aset-avatar-preview {
        position: relative;
        width: 72px;
        height: 72px;
        min-width: 72px;
        border: 3px solid rgba(255,255,255,.9);
        border-radius: 50%;
        overflow: visible;
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--aset-primary), var(--theme-secondary, #ff7a45));
        color: #fff;
        box-shadow: 0 10px 24px rgba(var(--aset-primary-rgb), .16);
        font-size: 26px;
        font-weight: 900;
        line-height: 1;
        cursor: pointer;
    }
    .aset-avatar-preview img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }
    .aset-avatar-fallback {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }
    .aset-avatar-change-icon {
        position: absolute;
        right: -4px;
        bottom: -4px;
        z-index: 2;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        border: 2px solid #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--theme-primary, #ff4a58), var(--theme-secondary, #ff7a45));
        color: #fff;
        font-size: 12px;
        box-shadow: 0 4px 10px rgba(var(--aset-primary-rgb), .24);
        pointer-events: none;
    }
    .aset-avatar-preview:active .aset-avatar-change-icon {
        transform: scale(.94);
    }
    .aset-avatar-title {
        color: #20242c;
        font-size: 16px;
        font-weight: 900;
    }
    .aset-avatar-tip {
        margin-top: 5px;
        color: #8b95a5;
        font-size: 12px;
        line-height: 1.6;
    }
    .aset-form-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 13px;
    }
    .aset-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }
    .aset-label,
    .aset-row-label,
    .aset-block-label {
        color: #2a303a;
        font-size: 13px;
        font-weight: 900;
    }
    .aset-control {
        min-width: 0;
    }
    .aset-input,
    .aset-textarea {
        width: 100%;
        border: 1px solid #edf0f5;
        border-radius: 14px;
        background: #f8f9fb;
        color: #20242c;
        outline: none;
        font-size: 14px;
        box-shadow: none;
    }
    .aset-input {
        height: 44px;
        padding: 0 14px;
    }
    .aset-textarea {
        min-height: 132px;
        padding: 12px 14px;
        line-height: 1.7;
        resize: none;
    }
    .aset-input:focus,
    .aset-textarea:focus {
        border-color: rgba(var(--aset-primary-rgb), .36);
        background: #fff;
        box-shadow: 0 0 0 3px rgba(var(--aset-primary-rgb), .08);
    }
    .aset-input[disabled],
    .aset-textarea[disabled] {
        color: #98a1af;
        -webkit-text-fill-color: #98a1af;
        background: #f3f4f6;
        opacity: 1;
    }
    .aset-input::placeholder,
    .aset-textarea::placeholder {
        color: #a6afbd;
    }
    .aset-email-wrap,
    .aset-phone-wrap {
        position: relative;
    }
    .aset-email-wrap .aset-input,
    .aset-phone-wrap .aset-input {
        padding-right: 100px;
    }
    .aset-email-btn,
    .aset-phone-btn {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        height: 34px;
        padding: 0 12px;
        border: 0;
        border-radius: 999px;
        background: #fff;
        color: var(--aset-primary);
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }
    .aset-email-btn.is-danger,
    .aset-phone-btn.is-danger {
        color: #ef4444;
        background: #fff5f5;
    }
    .aset-email-btn[disabled],
    .aset-phone-btn[disabled] {
        color: #a6afbd;
        background: #f3f4f6;
        opacity: 1;
    }
    .aset-email-warn,
    .aset-phone-warn,
    .aset-field-tip {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-top: 8px;
        color: #f59e0b;
        font-size: 12px;
        line-height: 1.5;
    }
    .aset-field-tip {
        align-items: flex-start;
        color: #8b95a5;
    }
    .aset-submit-wrap {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 30;
        margin: 0;
        padding: 10px 12px calc(10px + env(safe-area-inset-bottom, 0px));
        background: rgba(255,255,255,.96);
        border-top: 1px solid #edf0f5;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        text-align: initial;
    }
    .aset-submit,
    .aset-disabled-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        width: 100%;
        height: 46px;
        min-width: 0;
        padding: 0 16px;
        border: 0;
        border-radius: 16px;
        font-size: 14px;
        font-weight: 900;
    }
    .aset-submit {
        color: #fff;
        background: linear-gradient(135deg, var(--theme-primary, #ff4a58), var(--theme-secondary, #ff7a45));
        box-shadow: 0 10px 24px rgba(var(--aset-primary-rgb), .18);
    }
    .aset-submit:hover {
        transform: none;
    }
    .aset-disabled-btn {
        margin-top: 10px;
        color: #8b95a5;
        background: #edf0f5;
    }
    .aset-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }
    .aset-social {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 92px;
        padding: 12px 8px;
        border-radius: 16px;
        background: #f8f9fb;
        color: #2a303a;
        text-decoration: none;
        box-shadow: none;
    }
    .aset-social:hover {
        color: #2a303a;
        transform: none;
        box-shadow: none;
        text-decoration: none;
    }
    .aset-social-icon {
        width: 42px;
        height: 42px;
        border-radius: 16px;
        background: #e8ebf0;
        color: #9aa3af;
        font-size: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .aset-social.is-accent .aset-social-icon {
        background: var(--aset-soft);
        color: var(--aset-primary);
    }
    .aset-social-name {
        margin-top: 8px;
        color: #566070;
        font-size: 12px;
        font-weight: 800;
    }
    .aset-soft-tip,
    .aset-doc {
        margin-top: 12px;
        color: #8b95a5;
        font-size: 12px;
        line-height: 1.7;
    }
    .aset-doc span {
        color: var(--aset-primary);
        font-weight: 900;
    }
    .aset-secret {
        display: grid;
        gap: 12px;
    }
    .aset-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 13px 0px 13px 14px;
        border-radius: 15px;
        background: #fcfcfc;
    }
    .aset-row-value,
    .aset-info-value {
        color: #20242c;
        font-size: 13px;
        font-weight: 900;
        text-align: right;
        word-break: break-all;
    }
    .aset-row-value.is-danger,
    .aset-info-value.is-muted {
        color: #8b95a5;
        font-weight: 700;
    }
    .aset-block {
        margin-top: 2px;
    }
    .aset-secret .aset-row {
        justify-content: flex-start;
    }
    .aset-secret .aset-row .aset-row-value {
        text-align: left;
    }
    .aset-block-label {
        display: block;
        margin-bottom: 8px;
    }
    .aset-side-stack {
        display: none !important;
    }
    /* 头像裁切器：APP 底部弹窗样式 */
    .aset-crop-mask,
    .aset-crop-mask * {
        box-sizing: border-box;
        -webkit-tap-highlight-color: transparent;
    }
    .aset-crop-mask {
        --aset-primary: var(--theme-primary, #ff4a58);
        --aset-primary-rgb: var(--tp-rgb, 255,74,88);
        --aset-soft: rgba(var(--aset-primary-rgb), .10);
        position: fixed;
        inset: 0;
        z-index: 19999;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding: 10px 10px calc(10px + env(safe-area-inset-bottom, 0px));
        background: rgba(15, 23, 42, .48);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity .24s ease, visibility .24s ease;
    }
    .aset-crop-mask.is-show {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }
    .aset-crop-panel {
        width: 100%;
        max-width: 430px;
        max-height: calc(100vh - 20px - env(safe-area-inset-bottom, 0px));
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border-radius: 24px 24px 18px 18px;
        background: #fff;
        box-shadow: 0 -10px 42px rgba(15, 23, 42, .22);
        transform: translateY(110%);
        transition: transform .3s cubic-bezier(.22,.61,.36,1);
    }
    .aset-crop-mask.is-show .aset-crop-panel {
        transform: translateY(0);
    }
    .aset-crop-handle {
        width: 42px;
        height: 4px;
        margin: 10px auto 0;
        border-radius: 999px;
        background: #d8dee8;
        cursor: grab;
        touch-action: none;
    }
    .aset-crop-handle:active {
        cursor: grabbing;
    }
    .aset-crop-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px 10px;
        cursor: grab;
        touch-action: none;
    }
    .aset-crop-head:active {
        cursor: grabbing;
    }
    .aset-crop-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        color: #fff;
        font-size: 18px;
        background: linear-gradient(135deg, var(--theme-primary, #ff4a58), var(--theme-secondary, #ff7a45));
        box-shadow: 0 10px 22px rgba(var(--aset-primary-rgb), .18);
    }
    .aset-crop-title {
        color: #20242c;
        font-size: 17px;
        font-weight: 900;
        line-height: 1.25;
    }
    .aset-crop-sub {
        margin-top: 3px;
        color: #8b95a5;
        font-size: 12px;
        line-height: 1.45;
    }
    .aset-crop-close {
        width: 34px;
        height: 34px;
        margin-left: auto;
        border: 0;
        border-radius: 50%;
        background: #f3f4f6;
        color: #8b95a5;
        font-size: 14px;
    }
    .aset-crop-body {
        padding: 0 14px 10px;
        overflow: auto;
    }
    .aset-crop-stage {
        width: 100%;
        height: min(52vh, 360px);
        min-height: 270px;
        overflow: hidden;
        border-radius: 18px;
        background: #111827;
    }
    .aset-crop-stage img {
        display: block;
        max-width: 100%;
    }
    .aset-crop-tip {
        display: flex;
        align-items: flex-start;
        gap: 7px;
        margin-top: 10px;
        padding: 10px 12px;
        border-radius: 14px;
        background: var(--aset-soft);
        color: #7c8797;
        font-size: 12px;
        line-height: 1.65;
    }
    .aset-crop-tip i {
        margin-top: 2px;
        color: var(--aset-primary);
    }
    .aset-crop-foot {
        display: grid;
        grid-template-columns: 1fr 1.25fr;
        gap: 10px;
        padding: 10px 14px calc(14px + env(safe-area-inset-bottom, 0px));
        border-top: 1px solid #edf0f5;
        background: rgba(255,255,255,.96);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
    .aset-crop-btn {
        height: 44px;
        border: 0;
        border-radius: 15px;
        font-size: 14px;
        font-weight: 900;
        transition: transform .16s ease, opacity .16s ease;
    }
    .aset-crop-btn:active {
        transform: scale(.97);
    }
    .aset-crop-btn:disabled {
        opacity: .62;
    }
    .aset-crop-cancel {
        background: #f3f4f6;
        color: #5f6673;
    }
    .aset-crop-confirm {
        color: #fff;
        background: linear-gradient(135deg, var(--theme-primary, #ff4a58), var(--theme-secondary, #ff7a45));
        box-shadow: 0 10px 22px rgba(var(--aset-primary-rgb), .18);
    }
    .aset-crop-panel .cropper-view-box,
    .aset-crop-panel .cropper-face {
        border-radius: 50%;
    }
    .aset-crop-panel .cropper-view-box {
        outline-color: rgba(255,255,255,.95);
        outline-width: 2px;
        box-shadow: 0 0 0 1px rgba(var(--aset-primary-rgb), .22);
    }
    @media (max-width: 360px) {
        .aset-avatar-row {
            align-items: flex-start;
        }
        .aset-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    /* 密钥弹窗 - 与移动端邀请好友页设置微信号弹窗统一风格 */
    .akey-modal-mask{position:fixed;inset:0;z-index:19999;background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;pointer-events:none;transition:opacity .25s,visibility .25s}
    .akey-modal-mask.is-show{opacity:1;visibility:visible;pointer-events:auto}
    .akey-modal{width:min(88vw,340px);background:#fff;border-radius:20px;overflow:hidden;transform:translateY(24px) scale(.96);transition:transform .28s cubic-bezier(.22,.61,.36,1);box-shadow:0 20px 50px rgba(0,0,0,.18)}
    .akey-modal-mask.is-show .akey-modal{transform:translateY(0) scale(1)}
    .akey-modal-header{padding:22px 22px 0;text-align:center}
    .akey-modal-icon{width:52px;height:52px;margin:0 auto 12px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;color:#fff}
    .akey-modal-icon.is-warn{background:linear-gradient(135deg,#FFC107,#FF9800)}
    .akey-modal-icon.is-ok{background:linear-gradient(135deg,#10b981,#059669)}
    .akey-modal-title{font-size:17px;font-weight:800;color:#252d3b}
    .akey-modal-body{padding:0 22px 6px;text-align:center}
    .akey-modal-desc{color:#5f6673;font-size:13px;line-height:1.75;text-align:left;margin-top:8px}
    .akey-modal-key-box{margin:12px 0 8px;padding:12px;border-radius:13px;background:#f8f9fb;border:1px solid #e5e7eb;font-family:'SF Mono',Menlo,Consolas,monospace;font-size:13px;font-weight:700;color:#111827;word-break:break-all;user-select:all;text-align:center;letter-spacing:.5px}
    .akey-modal-notice{margin-top:12px;padding:12px;border-radius:13px;background:#ecfdf5;border:1px solid #d1fae5;color:#5f6673;font-size:12px;line-height:1.75;text-align:left}
    .akey-modal-notice-title{display:flex;align-items:center;gap:6px;margin-bottom:6px;color:#252d3b;font-size:13px;font-weight:900}
    .akey-modal-foot{display:flex;gap:10px;padding:10px 22px 8px}
    .akey-modal-foot-note{padding:0 22px 18px;text-align:center;color:#a0a7b4;font-size:12px;line-height:1.5}
    .akey-modal-btn{flex:1;height:44px;border:none;border-radius:14px;font-size:15px;font-weight:700;cursor:pointer;transition:.15s;font-family:inherit}
    .akey-modal-btn:disabled{opacity:.65}
    .akey-modal-cancel{background:#f3f4f6;color:#5f6673}
    .akey-modal-cancel:active{background:#e8ebf0}
    .akey-modal-confirm{background:linear-gradient(135deg,var(--theme-primary,#667eea),var(--tp-dark,#764ba2));color:#fff;box-shadow:0 6px 16px rgba(var(--tp-rgb,102,126,234),.22)}
    .akey-modal-confirm:active{transform:scale(.97)}
    .akey-modal-confirm.is-danger{background:linear-gradient(135deg,#FFC107,#FF9800);box-shadow:0 6px 16px rgba(255,193,7,.22)}
</style>

<main class="aset-page">
    <section class="aset-grid-layout">
        <div class="aset-panel">
            <div class="aset-panel-head">
                <div>
                    <h3 class="aset-panel-title">账户设置</h3>
                </div>
            </div>

            <div class="aset-tabs">
                <button class="aset-tab is-active" type="button" data-tab="profile">信息配置</button>
                <button class="aset-tab" type="button" data-tab="quick">快捷登录</button>
                <button class="aset-tab" type="button" data-tab="secret">对接密钥</button>
                <div class="aset-tab-indicator" id="asetTabIndicator"></div>
            </div>

            <div class="aset-panels">
                <div class="aset-panel-item is-active" data-panel="profile">
                    <form id="accountSettingForm" action="/user/account.php?action=setting_save" method="post">
                        <input type="hidden" name="token" value="<?= LoginAuth::genToken() ?>">
                        
                            <div class="aset-avatar-row">
                                <div class="aset-avatar-preview" id="asetAvatarPreview">
                                <?php if (!empty($user['photo'])): ?>
                                    <img id="asetAvatarImg" src="<?= htmlspecialchars($settingAvatar) ?>" alt="头像">
                                <?php else: ?>
                                    <span class="aset-avatar-fallback"><?= htmlspecialchars($settingInitial) ?></span>
                                <?php endif; ?>
                                    <span class="aset-avatar-change-icon"><i class="fa fa-camera"></i></span>
                                </div>
                                <div class="aset-avatar-meta">
                                    <div class="aset-avatar-title">账户头像</div>
                                    <div class="aset-avatar-tip">支持 JPG / PNG / GIF / WEBP，文件大小 ≤ 2MB</div>
                                    <input type="file" id="asetAvatarInput" accept="image/jpeg,image/png,image/gif,image/webp" hidden>
                                </div>
                            </div>
                            <div class="aset-form-grid">
                                <div class="aset-field">
                                    <div class="aset-label">用户昵称</div>
                                    <div class="aset-control">
                                        <input class="aset-input" type="text" name="nickname" maxlength="20" value="<?= htmlspecialchars($settingName) ?>" placeholder="请输入用户名称">
                                    </div>
                                </div>
                                <div class="aset-field">
                                    <div class="aset-label">登录账号</div>
                                    <div class="aset-control">
                                        <input class="aset-input" type="text" name="username" maxlength="30" value="<?= htmlspecialchars($settingUsername) ?>" placeholder="请输入登录账号">
                                    </div>
                                </div>
                                <div class="aset-field">
                                    <div class="aset-label">微信号</div>
                                    <div class="aset-control">
                                        <input class="aset-input" type="text" name="wechat" maxlength="40" value="<?= htmlspecialchars($settingWechat, ENT_QUOTES) ?>" placeholder="请输入微信号，可留空">
                                        <div class="aset-field-tip"><i class="fa fa-info-circle"></i> 设置后会展示给您的上级成员和团队成员，便于沟通交流。</div>
                                    </div>
                                </div>
                                <div class="aset-field" id="phoneSection">
                                    <div class="aset-label">绑定手机</div>
                                    <div class="aset-control">
                                    <?php if (!$_telBound): ?>
                                        <div class="aset-phone-wrap">
                                            <input class="aset-input" type="tel" id="bindPhoneInput" maxlength="11" placeholder="请输入手机号">
                                            <button type="button" class="aset-phone-btn" id="sendBindPhoneCode">发送验证码</button>
                                        </div>
                                    <?php elseif ($_canUnbindTel): ?>
                                        <div class="aset-phone-wrap">
                                            <input class="aset-input" type="text" value="<?= htmlspecialchars($_maskedTel) ?>" disabled>
                                            <button type="button" class="aset-phone-btn is-danger" id="startUnbindPhone">解绑</button>
                                        </div>
                                    <?php else: ?>
                                        <input class="aset-input" type="text" value="<?= htmlspecialchars($_maskedTel) ?>" disabled>
                                        <div class="aset-phone-warn"><i class="fa fa-info-circle"></i> 手机号为唯一登录方式，不可解绑</div>
                                    <?php endif; ?>
                                    </div>
                                </div>
                                <div class="aset-field" id="emailSection">
                                    <div class="aset-label">绑定邮箱</div>
                                    <div class="aset-control">
                                    <?php if (!$_emailBound): ?>
                                        <div class="aset-email-wrap">
                                            <input class="aset-input" type="email" id="bindEmailAddr" placeholder="请输入邮箱地址">
                                            <button type="button" class="aset-email-btn" id="sendBindCode">发送验证码</button>
                                        </div>
                                    <?php elseif ($_canUnbindEmail): ?>
                                        <div class="aset-email-wrap">
                                            <input class="aset-input" type="text" value="<?= htmlspecialchars($_maskedEmail) ?>" disabled>
                                            <button type="button" class="aset-email-btn is-danger" id="startUnbind">解绑</button>
                                        </div>
                                    <?php else: ?>
                                        <input class="aset-input" type="text" value="<?= htmlspecialchars($_maskedEmail) ?>" disabled>
                                        <div class="aset-email-warn"><i class="fa fa-info-circle"></i> 邮箱为唯一登录方式，不可解绑</div>
                                    <?php endif; ?>
                                    </div>
                                </div>
                                <div class="aset-field">
                                    <div class="aset-label">登录密码</div>
                                    <div class="aset-control">
                                        <input class="aset-input" type="password" name="new_password" autocomplete="new-password" placeholder="留空不修改，请填写新的密码">
                                    </div>
                                </div>
                            </div>
                            <div class="aset-submit-wrap">
                                <button class="aset-submit" id="accountSettingSubmit" type="submit"><i class="fa fa-save"></i> 保存设置</button>
                            </div>
                        
                    </form>
                </div>

                <div class="aset-panel-item" data-panel="quick">
                        <div class="aset-grid">
                            <a class="aset-social" href="#" data-coming="1">
                                <div class="aset-social-icon"><i class="fa fa-qq"></i></div>
                                <div class="aset-social-name">QQ</div>
                            </a>
                            <a class="aset-social" href="#" data-coming="1">
                                <div class="aset-social-icon"><i class="fa fa-weixin"></i></div>
                                <div class="aset-social-name">微信</div>
                            </a>
                            <a class="aset-social" href="#" data-coming="1">
                                <div class="aset-social-icon"><i class="fa fa-credit-card-alt"></i></div>
                                <div class="aset-social-name">支付宝</div>
                            </a>
                            <a class="aset-social" href="#" data-coming="1">
                                <div class="aset-social-icon"><i class="fa fa-facebook"></i></div>
                                <div class="aset-social-name">facebook</div>
                            </a>
                            <a class="aset-social" href="#" data-coming="1">
                                <div class="aset-social-icon"><i class="fa fa-github"></i></div>
                                <div class="aset-social-name">GitHub</div>
                            </a>
                            <a class="aset-social" href="#" data-coming="1">
                                <div class="aset-social-icon"><i class="fa fa-weibo"></i></div>
                                <div class="aset-social-name">微博</div>
                            </a>
                            <a class="aset-social" href="#" data-coming="1">
                                <div class="aset-social-icon"><i class="fa fa-twitter"></i></div>
                                <div class="aset-social-name">twitter</div>
                            </a>
                        </div>
                        <div class="aset-soft-tip">快捷登录功能站长暂未开放，开放后可直接在这里使用。</div>
                </div>

                <div class="aset-panel-item" data-panel="secret">
                    
                        <div class="aset-secret">
                            <div class="aset-row">
                                <div class="aset-row-label">对接链接:</div>
                                <div class="aset-row-value" style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                    <span id="api-link-m" style="font-family:monospace;word-break:break-all;font-size:13px;letter-spacing:0px;cursor:pointer;" title="点击复制对接链接"><?= htmlspecialchars($settingDockingLink) ?></span>
                                    <button type="button" id="api-link-copy-btn-m" style="background:none;border:none;color:#8b95a5;cursor:pointer;padding:4px;font-size:16px;outline:none;" title="复制对接链接"><i class="ri-file-copy-line"></i></button>
                                </div>
                            </div>
                            <div class="aset-row">
                                <div class="aset-row-label">对接 ID:</div>
                                <div class="aset-row-value" style="display:flex;align-items:center;gap:8px;">
                                    <span id="api-uid-m"><?= $settingUid ?></span>
                                    <button type="button" id="api-uid-copy-btn-m" style="background:none;border:none;color:#8b95a5;cursor:pointer;padding:4px;font-size:16px;outline:none;" title="复制对接ID"><i class="ri-file-copy-line"></i></button>
                                </div>
                            </div>
                            <div class="aset-row">
                                <div class="aset-row-label">对接密钥:</div>
                                <div class="aset-row-value" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                    <span id="api-key-display-m" style="font-family:monospace;word-break:break-all;font-size:13px;letter-spacing:-0.8px;cursor:pointer;" title="点击复制密钥">未生成</span>
                                    <button type="button" id="api-key-copy-btn-m" style="display:none;background:none;border:none;color:#8b95a5;cursor:pointer;font-size:16px;outline:none;" title="复制密钥"><i class="ri-file-copy-line"></i></button>
                                    <button type="button" id="api-key-reset-btn-m" style="display:none;background:none;border:none;color:#8b95a5;cursor:pointer;font-size:16px;outline:none;" title="重置密钥"><i class="ri-refresh-line"></i></button>
                                    <button type="button" class="aset-submit" id="api-key-gen-btn-m" style="height:28px;padding:0 10px;font-size:12px;line-height:28px;width:auto;min-width:0;border-radius:8px;display:none;">
                                        <span>生成密钥</span>
                                    </button>
                                </div>
                            </div>
                            <div class="aset-block">
                                <div class="aset-block-label">IP白名单</div>
                                <textarea class="aset-textarea" id="api-whitelist-input-m" placeholder="一行一个IP，留空不限制任何IP" style="height:80px;"></textarea>
                                <div class="aset-submit-wrap">
                                    <button class="aset-submit" type="button" id="api-whitelist-save-btn-m"><i class="fa fa-save"></i> 保存白名单</button>
                                </div>
                                <div class="aset-doc" style="margin-top:10px;font-size:12px;color:#666;">
                                    <i class="fa fa-book"></i> 
                                    dcshop系统对接开发文档：<a href="https://dcshop.xzsc.cc/api_doc.php" target="_blank" style="color:var(--theme-primary);text-decoration:underline;">查看</a>
                                </div>
                            </div>
                        </div>
                                    </div>
            </div>
        </div>

    </section>
</main>

<div class="aset-crop-mask" id="asetAvatarCropModal">
    <div class="aset-crop-panel">
        <div class="aset-crop-handle"></div>
        <div class="aset-crop-head">
            <div class="aset-crop-icon"><i class="fa fa-crop"></i></div>
            <div>
                <div class="aset-crop-title">裁切头像</div>
                <div class="aset-crop-sub">像 APP 一样拖动、缩放后保存</div>
            </div>
            <button type="button" class="aset-crop-close" id="asetCropClose" aria-label="关闭"><i class="fa fa-times"></i></button>
        </div>
        <div class="aset-crop-body">
            <div class="aset-crop-stage">
                <img id="asetCropImage" src="" alt="裁切头像">
            </div>
        </div>
        <div class="aset-crop-foot">
            <button type="button" class="aset-crop-btn aset-crop-cancel" id="asetCropCancel">取消</button>
            <button type="button" class="aset-crop-btn aset-crop-confirm" id="asetCropConfirm">裁切并保存</button>
        </div>
    </div>
</div>

<!-- 密钥重置确认弹窗 -->
<div class="akey-modal-mask" id="akeyConfirmModalM">
    <div class="akey-modal">
        <div class="akey-modal-header">
            <div class="akey-modal-icon is-warn"><i class="ri-key-2-line"></i></div>
            <div class="akey-modal-title">确认重置对接密钥？</div>
        </div>
        <div class="akey-modal-body">
            <div class="akey-modal-desc">重置后旧密钥将立即失效，您必须更新相关对接系统的配置，否则对接功能将中断。</div>
        </div>
        <div class="akey-modal-foot">
            <button type="button" class="akey-modal-btn akey-modal-cancel" id="akeyConfirmCancelM">再想想</button>
            <button type="button" class="akey-modal-btn akey-modal-confirm is-danger" id="akeyConfirmOkM">确认重置</button>
        </div>
        <div class="akey-modal-foot-note"> </div>
    </div>
</div>

<!-- 密钥重置成功弹窗 -->
<div class="akey-modal-mask" id="akeySuccessModalM">
    <div class="akey-modal">
        <div class="akey-modal-header">
            <div class="akey-modal-icon is-ok"><i class="ri-checkbox-circle-line"></i></div>
            <div class="akey-modal-title">密钥已重置成功</div>
        </div>
        <div class="akey-modal-body">
            <div class="akey-modal-key-box" id="akeyNewKeyDisplayM"></div>
            <div class="akey-modal-notice">
                <div class="akey-modal-notice-title"><i class="ri-information-line"></i> 温馨提示</div>
                <div>对接密钥已成功重置，请及时更新对接配置，否则对接功能将中断。</div>
            </div>
        </div>
        <div class="akey-modal-foot">
            <button type="button" class="akey-modal-btn akey-modal-cancel" id="akeySuccessCloseM">关闭</button>
            <button type="button" class="akey-modal-btn akey-modal-confirm" id="akeySuccessCopyM">复制密钥</button>
        </div>
        <div class="akey-modal-foot-note"> </div>
    </div>
</div>

<script>
layui.use(['layer', 'jquery'], function () {
    var $ = layui.$;
    var layer = layui.layer;
    var $submitBtn = $('#accountSettingSubmit');
    var defaultSubmitHtml = $submitBtn.html();

    $('#menu-account-setting').addClass('menu-current');

    // hash #nickname → 滚动并高亮用户名称输入框
    if (window.location.hash === '#nickname') {
        var $nick = $('input[name="nickname"]');
        if ($nick.length) {
            setTimeout(function() {
                var el = $nick[0];
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                $nick.addClass('is-highlight').focus();
                setTimeout(function() { $nick.removeClass('is-highlight'); }, 2600);
            }, 300);
        }
    }

    var asetTabNames = [];
    $('.aset-tab').each(function () {
        asetTabNames.push($(this).data('tab'));
    });
    var asetCurrentTab = $('.aset-tab.is-active').data('tab') || asetTabNames[0] || 'profile';
    var asetIndicatorTimer = null;
    function moveAccountTabIndicator($tab, animate) {
        var $indicator = $('#asetTabIndicator');
        if (!$tab.length || !$indicator.length) return;
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
        var stretchLeft = targetLeft > curLeft ? curLeft : targetLeft;
        var stretchWidth = Math.abs(targetLeft - curLeft) + indicatorW;
        if (asetIndicatorTimer) {
            clearTimeout(asetIndicatorTimer);
            asetIndicatorTimer = null;
        }
        $indicator.css({
            left: stretchLeft + 'px',
            width: stretchWidth + 'px',
            transition: 'left 0.16s cubic-bezier(.4,0,.2,1), width 0.16s cubic-bezier(.4,0,.2,1)'
        });
        asetIndicatorTimer = setTimeout(function() {
            $indicator.css({
                left: targetLeft + 'px',
                width: indicatorW + 'px',
                transition: 'left 0.13s cubic-bezier(.4,0,.2,1), width 0.13s cubic-bezier(.4,0,.2,1)'
            });
        }, 200);
    }
    function setAccountSettingTab(tab) {
        if ($.inArray(tab, asetTabNames) === -1) tab = asetTabNames[0] || 'profile';
        asetCurrentTab = tab;
        $('.aset-tab').removeClass('is-active');
        $('.aset-tab[data-tab="' + tab + '"]').addClass('is-active');
        moveAccountTabIndicator($('.aset-tab[data-tab="' + tab + '"]'), true);
        $('.aset-panel-item').removeClass('is-active');
        $('.aset-panel-item[data-panel="' + tab + '"]').addClass('is-active');
    }
    moveAccountTabIndicator($('.aset-tab.is-active'), false);

    $('.aset-tab').on('click', function () {
        setAccountSettingTab($(this).data('tab'));
    });

    // APP 页面左右滑动切换：信息配置 / 快捷登录 / 对接密钥
    var asetTouchStartX = 0, asetTouchStartY = 0, asetTouchMoved = false, asetIgnoreSwipe = false;
    $('.aset-page').on('touchstart', function (e) {
        var $target = $(e.target);
        asetIgnoreSwipe = $target.closest('input, textarea, select, [contenteditable="true"]').length > 0;
        asetTouchMoved = false;
        if (asetIgnoreSwipe) return;
        var t = e.originalEvent.touches && e.originalEvent.touches[0];
        if (!t) return;
        asetTouchStartX = t.clientX;
        asetTouchStartY = t.clientY;
    });
    $('.aset-page').on('touchmove', function (e) {
        if (asetIgnoreSwipe) return;
        var t = e.originalEvent.touches && e.originalEvent.touches[0];
        if (!t) return;
        var dx = t.clientX - asetTouchStartX;
        var dy = t.clientY - asetTouchStartY;
        if (Math.abs(dx) > 20 && Math.abs(dy) < 42) {
            asetTouchMoved = true;
        }
    });
    $('.aset-page').on('touchend', function (e) {
        if (asetIgnoreSwipe) {
            asetIgnoreSwipe = false;
            return;
        }
        if (!asetTouchMoved) return;
        var changed = e.originalEvent.changedTouches && e.originalEvent.changedTouches[0];
        if (!changed) return;
        var diff = changed.clientX - asetTouchStartX;
        if (Math.abs(diff) < 50) return;
        var idx = $.inArray(asetCurrentTab, asetTabNames);
        if (idx < 0) idx = 0;
        if (diff < 0 && idx < asetTabNames.length - 1) {
            idx++;
        } else if (diff > 0 && idx > 0) {
            idx--;
        } else {
            return;
        }
        setAccountSettingTab(asetTabNames[idx]);
    });

    // APP 样式头像裁切上传
    var avatarCropper = null;
    var avatarOriginalFile = null;
    var avatarObjectUrl = '';
    var avatarSubmitting = false;
    var avatarToken = '<?= LoginAuth::genToken() ?>';

    function destroyAvatarCropper() {
        if (avatarCropper) {
            avatarCropper.destroy();
            avatarCropper = null;
        }
    }

    function closeAvatarCropper() {
        destroyAvatarCropper();
        $('#asetAvatarCropModal').removeClass('is-show');
        $('.aset-crop-panel').css({ transition: '', transform: '' });
        $('#asetCropImage').attr('src', '');
        if (avatarObjectUrl) {
            URL.revokeObjectURL(avatarObjectUrl);
            avatarObjectUrl = '';
        }
        avatarOriginalFile = null;
        $('#asetAvatarInput').val('');
        $('#asetCropConfirm').prop('disabled', false);
    }

    function refreshAvatarPreview(newUrl) {
        if (!newUrl) return;
        var cacheBustUrl = newUrl + (newUrl.indexOf('?') >= 0 ? '&' : '?') + '_=' + Date.now();
        var $preview = $('#asetAvatarPreview');
        if ($preview.find('img').length) {
            $preview.find('img').attr('src', cacheBustUrl);
        } else {
            $preview.html('<img id="asetAvatarImg" src="' + cacheBustUrl + '" alt="头像">');
        }
        $('.user-profile-avatar').attr('src', cacheBustUrl);
    }

    function uploadAvatarBlob(blob, filename) {
        if (avatarSubmitting || !blob) return;
        avatarSubmitting = true;
        $('#asetCropConfirm').prop('disabled', true);
        var fd = new FormData();
        fd.append('file', blob, filename || 'avatar.jpg');
        fd.append('token', avatarToken);
        var loadIdx = layer.load(2, {shade: [0.08, '#000']});
        $.ajax({
            url: '<?= DC_URL ?>user/api.php?action=upload_user_avatar',
            type: 'POST',
            data: fd,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function(res) {
                if (!res || res.code !== 200) {
                    return layer.msg((res && res.msg) || '上传失败', {icon: 2, time: 2600});
                }
                refreshAvatarPreview(res.data && res.data.url ? res.data.url : '');
                layer.msg(res.msg || '头像已更新', {icon: 1, time: 1600});
                closeAvatarCropper();
            },
            error: function(xhr) {
                var msg = '上传失败，请稍后重试';
                try { msg = JSON.parse(xhr.responseText).msg || msg; } catch (e) {}
                layer.msg(msg, {icon: 2, time: 2600});
            },
            complete: function() {
                layer.close(loadIdx);
                avatarSubmitting = false;
                $('#asetCropConfirm').prop('disabled', false);
            }
        });
    }

    function openAvatarCropper(file) {
        avatarOriginalFile = file;
        if (avatarObjectUrl) URL.revokeObjectURL(avatarObjectUrl);
        avatarObjectUrl = URL.createObjectURL(file);
        destroyAvatarCropper();
        $('#asetCropImage').attr('src', avatarObjectUrl);
        $('#asetAvatarCropModal').addClass('is-show');
        setTimeout(function() {
            var image = document.getElementById('asetCropImage');
            if (!image || typeof Cropper === 'undefined') {
                layer.msg('裁切组件加载失败，请刷新后重试', {icon: 2});
                return;
            }
            avatarCropper = new Cropper(image, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: .86,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
                responsive: true,
                background: false
            });
        }, 180);
    }

    function bindAvatarCropSwipeDismiss() {
        var panel = document.querySelector('#asetAvatarCropModal .aset-crop-panel');
        var handle = document.querySelector('#asetAvatarCropModal .aset-crop-handle');
        var head = document.querySelector('#asetAvatarCropModal .aset-crop-head');
        if (!panel || !handle || panel.__cropSwipeBound) return;
        panel.__cropSwipeBound = true;
        var startY = 0;
        var currentY = 0;
        var dragging = false;

        function onTouchStart(e) {
            if (avatarSubmitting || !e.touches || !e.touches.length) return;
            if (e.target.closest && e.target.closest('#asetCropClose')) return;
            startY = e.touches[0].clientY;
            currentY = 0;
            dragging = true;
            panel.style.transition = 'none';
        }

        function onTouchMove(e) {
            if (!dragging || !e.touches || !e.touches.length) return;
            var dy = e.touches[0].clientY - startY;
            if (dy < 0) dy = 0;
            currentY = dy;
            if (dy > 0) e.preventDefault();
            panel.style.transform = 'translateY(' + dy + 'px)';
        }

        function onTouchEnd() {
            if (!dragging) return;
            dragging = false;
            panel.style.transition = 'transform .3s cubic-bezier(.22,.61,.36,1)';
            if (currentY > 80) {
                panel.style.transform = 'translateY(110%)';
                setTimeout(function() {
                    closeAvatarCropper();
                }, 220);
            } else {
                panel.style.transform = 'translateY(0)';
                setTimeout(function() {
                    if ($('#asetAvatarCropModal').hasClass('is-show')) {
                        panel.style.transition = '';
                        panel.style.transform = '';
                    }
                }, 300);
            }
        }

        function onTouchCancel() {
            if (!dragging) return;
            dragging = false;
            panel.style.transition = 'transform .3s cubic-bezier(.22,.61,.36,1)';
            panel.style.transform = 'translateY(0)';
            setTimeout(function() {
                if ($('#asetAvatarCropModal').hasClass('is-show')) {
                    panel.style.transition = '';
                    panel.style.transform = '';
                }
            }, 300);
        }

        [handle, head].forEach(function(el) {
            if (!el) return;
            el.addEventListener('touchstart', onTouchStart, { passive: true });
            el.addEventListener('touchmove', onTouchMove, { passive: false });
            el.addEventListener('touchend', onTouchEnd, { passive: true });
            el.addEventListener('touchcancel', onTouchCancel, { passive: true });
        });
    }

    $('#asetAvatarPreview').on('click', function() {
        $('#asetAvatarInput').trigger('click');
    });

    $('#asetAvatarInput').on('change', function() {
        var file = this.files && this.files[0] ? this.files[0] : null;
        if (!file) return;
        var allowTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if ($.inArray(file.type, allowTypes) === -1) {
            $(this).val('');
            return layer.msg('只支持 JPG / PNG / GIF / WEBP 格式', {icon: 2});
        }
        if (file.size > 2 * 1024 * 1024) {
            $(this).val('');
            return layer.msg('图片大小不能超过 2MB', {icon: 2});
        }
        openAvatarCropper(file);
    });

    bindAvatarCropSwipeDismiss();
    $('#asetCropCancel,#asetCropClose').on('click', closeAvatarCropper);
    $('#asetAvatarCropModal').on('click', function(e) {
        if (e.target === this && !avatarSubmitting) closeAvatarCropper();
    });
    $('#asetCropConfirm').on('click', function() {
        if (!avatarCropper) return layer.msg('请先选择头像图片', {icon: 0});
        $('#asetCropConfirm').prop('disabled', true);
        avatarCropper.getCroppedCanvas({
            width: 512,
            height: 512,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        }).toBlob(function(blob) {
            if (!blob) {
                $('#asetCropConfirm').prop('disabled', false);
                return layer.msg('裁切失败，请重试', {icon: 2});
            }
            uploadAvatarBlob(blob, 'avatar.jpg');
        }, 'image/jpeg', .9);
    });

    $('#accountSettingForm').on('submit', function (event) {
        event.preventDefault();
        $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 保存中...');
        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: $(this).serialize(),
            dataType: 'json',
            success: function (e) {
                if (e.code == 400) {
                    layer.msg(e.msg, {icon: 2});
                } else {
                    layer.msg(e.data || '资料已保存', {icon: 1});
                }
            },
            error: function (xhr) {
                var msg = '保存失败，请稍后重试';
                try {
                    msg = JSON.parse(xhr.responseText).msg || msg;
                } catch (ex) {}
                layer.msg(msg, {icon: 2});
            },
            complete: function () {
                $submitBtn.prop('disabled', false).html(defaultSubmitHtml);
            }
        });
    });

    $('[data-coming="1"]').on('click', function (event) {
        event.preventDefault();
        layer.msg('该功能后续开放', {icon: 0});
    });

    // ===== 图形验证码弹窗（发送短信/邮箱验证码前校验） =====
    var _captchaUrl = '<?= DC_URL ?>include/lib/checkcode.php?t=';
    var _emailToken = $('input[name="token"]').val();

    window.openCaptchaDialog = function(title, onVerified) {
        layer.open({
            type: 1, title: title, area: ['360px','auto'], shadeClose: true,
            content: '<div style="padding:20px 24px 10px">' +
                '<div style="color:#64748b;font-size:13px;margin-bottom:10px">请先输入图形验证码</div>' +
                '<div style="display:flex;gap:10px;align-items:center">' +
                '<input id="_captchaInput" type="text" maxlength="8" placeholder="验证码" style="flex:1;height:44px;padding:0 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:16px;outline:none;box-sizing:border-box">' +
                '<img id="_captchaImg" src="' + _captchaUrl + Date.now() + '" style="height:40px;border-radius:6px;cursor:pointer" title="点击刷新">' +
                '</div></div>',
            btn: ['确认', '取消'],
            yes: function(idx) {
                var code = $.trim($('#_captchaInput').val());
                if (!code) return layer.msg('请输入图形验证码', {icon: 0});
                layer.close(idx);
                onVerified(code);
            },
            success: function(layero) {
                layero.find('.layui-layer-btn0').css({background: 'var(--theme-primary)', borderColor: 'var(--theme-primary)'});
                layero.find('#_captchaImg').on('click', function() {
                    $(this).attr('src', _captchaUrl + Date.now());
                });
            }
        });
    }

    function openCodeDialog(title, hint, onConfirm) {
        layer.open({
            type: 1, title: title, area: ['360px','auto'], shadeClose: true,
            content: '<div style="padding:20px 24px 10px">' +
                '<div style="color:#64748b;font-size:13px;margin-bottom:14px">' + hint + '</div>' +
                '<input id="_dlgCode" type="text" maxlength="6" placeholder="请输入验证码" style="width:100%;height:44px;padding:0 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:16px;outline:none;box-sizing:border-box">' +
                '</div>',
            btn: ['确认', '取消'],
            yes: function(idx) {
                var code = $.trim($('#_dlgCode').val());
                if (!code) return layer.msg('请输入验证码', {icon: 0});
                onConfirm(code, idx);
            },
            success: function(layero) {
                layero.find('.layui-layer-btn0').css({background: 'var(--theme-primary)', borderColor: 'var(--theme-primary)'});
            }
        });
    }

    // 发送验证码 + 弹窗绑定（先验图形验证码）
    $('#sendBindCode').on('click', function() {
        var email = $.trim($('#bindEmailAddr').val());
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            return layer.msg('请输入正确的邮箱地址', {icon: 0});
        }
        var $btn = $(this);
        openCaptchaDialog('绑定邮箱', function(imgcode) {
            $btn.prop('disabled', true).text('发送中...');
            $.post('/user/account.php?action=send_email_code', { mail: email, imgcode: imgcode }, function() {
                $btn.text('已发送');
                var cd = 60;
                var t = setInterval(function() { cd--; if (cd <= 0) { clearInterval(t); $btn.prop('disabled', false).text('发送验证码'); } else { $btn.text(cd + 's'); } }, 1000);
                openCodeDialog('绑定邮箱', '邮箱验证码已发送至 ' + email, function(code, idx) {
                    $.post('/user/account.php?action=bind_email', { email: email, mail_code: code, token: _emailToken }, function() {
                        layer.close(idx);
                        layer.msg('邮箱绑定成功', {icon: 1});
                        setTimeout(function() { location.reload(); }, 1000);
                    }, 'json').fail(function(xhr) {
                        var msg = '绑定失败';
                        try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e) {}
                        layer.msg(msg, {icon: 2});
                    });
                });
            }, 'json').fail(function(xhr) {
                $btn.prop('disabled', false).text('发送验证码');
                var msg = '发送失败';
                try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e) {}
                layer.msg(msg, {icon: 2});
            });
        });
    });

    // 解绑：先验图形验证码，再发送邮箱验证码 + 弹窗验证
    $('#startUnbind').on('click', function() {
        var $btn = $(this);
        openCaptchaDialog('解绑邮箱', function(imgcode) {
            $btn.prop('disabled', true).text('发送中...');
            $.post('/user/account.php?action=send_email_code', { mail: '<?= addslashes($settingEmail) ?>', imgcode: imgcode }, function() {
                $btn.text('已发送');
                var cd = 60;
                var t = setInterval(function() { cd--; if (cd <= 0) { clearInterval(t); $btn.prop('disabled', false).text('解绑邮箱'); } else { $btn.text(cd + 's'); } }, 1000);
                openCodeDialog('解绑邮箱', '邮箱验证码已发送至当前绑定邮箱', function(code, idx) {
                    $.post('/user/account.php?action=unbind_email', { mail_code: code, token: _emailToken }, function() {
                        layer.close(idx);
                        layer.msg('邮箱已解绑', {icon: 1});
                        setTimeout(function() { location.reload(); }, 1000);
                    }, 'json').fail(function(xhr) {
                        var msg = '解绑失败';
                        try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e) {}
                        layer.msg(msg, {icon: 2});
                    });
                });
            }, 'json').fail(function(xhr) {
                $btn.prop('disabled', false).text('解绑邮箱');
                var msg = '发送失败';
                try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e) {}
                layer.msg(msg, {icon: 2});
            });
        });
    });

    // ===== 手机绑定 / 解绑 =====
    function phoneCountdown($btn, origText) {
        var cd = 60;
        var t = setInterval(function() {
            cd--;
            if (cd <= 0) { clearInterval(t); $btn.prop('disabled', false).text(origText); }
            else { $btn.text(cd + 's'); }
        }, 1000);
    }

    // 绑定手机：先验图形验证码，再发送短信 + 弹窗输入
    $('#sendBindPhoneCode').on('click', function() {
        var phone = $.trim($('#bindPhoneInput').val());
        if (!phone || !/^1[3-9]\d{9}$/.test(phone)) {
            return layer.msg('请输入正确的手机号', {icon: 0});
        }
        var $btn = $(this);
        openCaptchaDialog('绑定手机', function(imgcode) {
            $btn.prop('disabled', true).text('发送中...');
            $.post('/user/account.php?action=send_bind_phone_code', { phone: phone, imgcode: imgcode }, function() {
                $btn.text('已发送');
                phoneCountdown($btn, '发送验证码');
                openCodeDialog('绑定手机', '短信验证码已发送至 ' + phone.substr(0,3) + '****' + phone.substr(-4), function(code, idx) {
                    $.post('/user/account.php?action=bind_phone', { phone: phone, code: code, token: _emailToken }, function() {
                        layer.close(idx);
                        layer.msg('手机号绑定成功', {icon: 1});
                        setTimeout(function() { location.reload(); }, 1000);
                    }, 'json').fail(function(xhr) {
                        var msg = '绑定失败';
                        try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e) {}
                        layer.msg(msg, {icon: 2});
                    });
                });
            }, 'json').fail(function(xhr) {
                $btn.prop('disabled', false).text('发送验证码');
                var msg = '发送失败';
                try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e) {}
                layer.msg(msg, {icon: 2});
            });
        });
    });

    // 解绑手机：先验图形验证码，再发送短信 + 弹窗验证
    $('#startUnbindPhone').on('click', function() {
        var $btn = $(this);
        openCaptchaDialog('解绑手机', function(imgcode) {
            $btn.prop('disabled', true).text('发送中...');
            $.post('/user/account.php?action=send_unbind_phone_code', { imgcode: imgcode }, function() {
                $btn.text('已发送');
                phoneCountdown($btn, '解绑');
                openCodeDialog('解绑手机', '短信验证码已发送至当前绑定手机', function(code, idx) {
                    $.post('/user/account.php?action=unbind_phone', { code: code, token: _emailToken }, function() {
                        layer.close(idx);
                        layer.msg('手机号已解绑', {icon: 1});
                        setTimeout(function() { location.reload(); }, 1000);
                    }, 'json').fail(function(xhr) {
                        var msg = '解绑失败';
                        try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e) {}
                        layer.msg(msg, {icon: 2});
                    });
                });
            }, 'json').fail(function(xhr) {
                $btn.prop('disabled', false).text('解绑');
                var msg = '发送失败';
                try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e) {}
                layer.msg(msg, {icon: 2});
            });
        });
    });

    // ===== API对接密钥 =====
    function loadApiKeyM() {
        $.post('/user/account.php?action=api_key_get', {}, function(res) {
            if (res.code === 0) {
                var d = res.data;
                if (d.has_key) {
                    $('#api-key-display-m').text(d.api_key);
                    $('#api-key-copy-btn-m').show();
                    $('#api-key-reset-btn-m').show();
                    $('#api-key-gen-btn-m').hide();
                } else {
                    $('#api-key-display-m').text('未生成');
                    $('#api-key-copy-btn-m').hide();
                    $('#api-key-reset-btn-m').hide();
                    $('#api-key-gen-btn-m').show();
                }
                $('#api-whitelist-input-m').val(d.whitelist);
            }
        }, 'json');
    }

    // 复制对接ID
    $('#api-uid-copy-btn-m').on('click', function(e) {
        e.preventDefault(); this.blur();
        copyApiKeyTextM($('#api-uid-m').text(), '对接ID已复制到剪贴板');
    });

    // 复制对接链接
    $('#api-link-copy-btn-m,#api-link-m').on('click', function(e) {
        e.preventDefault(); this.blur();
        copyApiKeyTextM($('#api-link-m').text(), '对接链接已复制到剪贴板');
    });

    // 复制密钥（图标按钮）
    $('#api-key-copy-btn-m').on('click', function(e) {
        e.preventDefault(); this.blur();
        var key = $('#api-key-display-m').text();
        if (key && key !== '未生成') copyApiKeyTextM(key, '对接密钥已复制到剪贴板');
    });

    // 点击密钥文字也可复制
    $('#api-key-display-m').on('click', function(e) {
        e.preventDefault(); this.blur();
        var key = $(this).text();
        if (key && key !== '未生成') copyApiKeyTextM(key, '对接密钥已复制到剪贴板');
    });

    // 重置密钥（图标按钮）
    $('#api-key-reset-btn-m').on('click', function(e) {
        e.preventDefault(); this.blur();
        showAkeyModalM('akeyConfirmModalM');
    });

    // 生成密钥按钮
    $('#api-key-gen-btn-m').on('click', function(e) {
        e.preventDefault(); this.blur();
        doResetKeyM();
    });

    var _akeyNewKeyM = '';

    function showAkeyModalM(id) { $('#' + id).addClass('is-show'); }
    function hideAkeyModalM(id) { $('#' + id).removeClass('is-show'); }

    // 点击遮罩关闭
    $('.akey-modal-mask').on('click', function(e) { if (e.target === this) hideAkeyModalM(this.id); });

    // 确认弹窗按钮
    $('#akeyConfirmCancelM').on('click', function() { hideAkeyModalM('akeyConfirmModalM'); });
    $('#akeyConfirmOkM').on('click', function() {
        hideAkeyModalM('akeyConfirmModalM');
        doResetKeyM();
    });

    // 成功弹窗按钮
    $('#akeySuccessCloseM').on('click', function() { hideAkeyModalM('akeySuccessModalM'); });
    $('#akeySuccessCopyM').on('click', function() {
        copyApiKeyTextM(_akeyNewKeyM, '密钥已成功复制到剪贴板');
    });

    function doResetKeyM() {
        var idx = layer.load();
        var token = $('input[name="token"]').val();
        $.post('/user/account.php?action=api_key_reset', {token: token}, function(res) {
            layer.close(idx);
            if (res.code === 0) {
                _akeyNewKeyM = res.data.api_key;
                $('#akeyNewKeyDisplayM').text(res.data.api_key);
                showAkeyModalM('akeySuccessModalM');
                loadApiKeyM();
            } else {
                layer.msg(res.msg, {icon: 2});
            }
        }, 'json').fail(function(xhr) {
            layer.close(idx);
            var msg = '重置密钥失败，请稍后重试';
            try { msg = JSON.parse(xhr.responseText).msg || msg; } catch (e) {}
            layer.msg(msg, {icon: 2});
        });
    }

    function copyApiKeyTextM(text, tip) {
        tip = tip || '已复制到剪贴板';
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function(){ layer.msg(tip, {icon: 1}); });
        } else {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.setAttribute('readonly', '');
            ta.style.position = 'fixed';
            ta.style.top = '0';
            ta.style.left = '0';
            ta.style.width = '1px';
            ta.style.height = '1px';
            ta.style.padding = '0';
            ta.style.border = 'none';
            ta.style.outline = 'none';
            ta.style.boxShadow = 'none';
            ta.style.background = 'transparent';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            
            var isiOS = navigator.userAgent.match(/ipad|iphone/i);
            if (isiOS) {
                var range = document.createRange();
                range.selectNodeContents(ta);
                var selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(range);
                ta.setSelectionRange(0, 999999);
            } else {
                ta.select();
            }
            
            document.execCommand('copy');
            document.body.removeChild(ta);
            layer.msg(tip, {icon: 1});
        }
    }

    $('#api-whitelist-save-btn-m').on('click', function() {
        var whitelist = $('#api-whitelist-input-m').val();
        var token = $('input[name="token"]').val();
        var idx = layer.load();
        $.post('/user/account.php?action=api_whitelist_save', {whitelist: whitelist, token: token}, function(res) {
            layer.close(idx);
            if (res.code === 0) {
                layer.msg('IP白名单配置已成功保存', {icon: 1});
            } else {
                layer.msg(res.msg, {icon: 2});
            }
        }, 'json').fail(function(xhr) {
            layer.close(idx);
            var msg = '保存IP白名单失败，请稍后重试';
            try { msg = JSON.parse(xhr.responseText).msg || msg; } catch (e) {}
            layer.msg(msg, {icon: 2});
        });
    });

    loadApiKeyM();
});
</script>

