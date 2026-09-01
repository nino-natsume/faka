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
    .aset-page {
        display: flex;
        flex-direction: column;
        gap: 22px;
        padding: 8px 0 18px;
    }

    .aset-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 24px 28px;
        border-radius: 10px;
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        box-shadow: 0 1px 18px #12345b0a;
    }

    .aset-page-title {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
        color: var(--text-main, #1f2937);
    }

    .aset-page-desc {
        margin: 4px 0 0;
        font-size: 13px;
        color: var(--text-sub, #64748b);
    }

    .aset-metrics {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .aset-metric,
    .aset-notice,
    .aset-panel,
    .aset-card {
    padding: 24px 28px;
    border-radius: 10px;
    background: var(--pc-card-bg);
    border: 2px solid #fff;
    box-shadow: 0 1px 18px #12345b0a;
    }

    .aset-metric {
        padding: 20px 22px;
    }

    .aset-metric-label {
        color: var(--text-sub);
        font-size: 13px;
        font-weight: 600;
    }

    .aset-metric-value {
        margin-top: 8px;
        color: var(--text-main);
        font-size: 22px;
        font-weight: 800;
        line-height: 1.25;
        word-break: break-all;
    }

    .aset-metric-note {
        margin-top: 6px;
        color: var(--text-sub);
        font-size: 12px;
        line-height: 1.7;
    }

    /* 头像上传区 */
    .aset-avatar-row {
        display: flex;
        align-items: center;
        gap: 18px;
        padding: 18px;
        margin-bottom: 18px;
        border-radius: 10px;
        background: rgba(var(--tp-rgb),.04);
    }

    .aset-avatar-preview {
        width: 92px;
        height: 92px;
        border-radius: 50%;
        overflow: hidden;
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--theme-primary), var(--tp-light));
        color: #fff;
        font-size: 32px;
        font-weight: 900;
        box-shadow: 0 14px 28px rgba(var(--tp-rgb),.18);
    }

    .aset-avatar-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .aset-avatar-fallback {
        line-height: 1;
    }

    .aset-avatar-meta {
        min-width: 0;
        flex: 1;
    }

    .aset-avatar-title {
        color: var(--text-main);
        font-size: 16px;
        font-weight: 800;
    }

    .aset-avatar-tip {
        margin-top: 6px;
        color: var(--text-sub);
        font-size: 12px;
        line-height: 1.7;
    }

    .aset-avatar-actions {
        margin-top: 12px;
    }

    .aset-avatar-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: 38px;
        padding: 0 16px;
        border: 1px solid rgba(var(--tp-rgb),.2);
        border-radius: 10px;
        background: #fff;
        color: var(--theme-primary);
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: background .18s ease, transform .18s ease, box-shadow .18s ease;
    }

    .aset-avatar-btn:hover {
        transform: translateY(-1px);
        background: rgba(var(--tp-rgb),.06);
        box-shadow: 0 10px 22px rgba(var(--tp-rgb),.14);
    }

    /* APP 风格头像裁切器 */
    .aset-crop-mask {
        position: fixed;
        inset: 0;
        z-index: 19999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 18px;
        background: rgba(15, 23, 42, .46);
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
        width: min(92vw, 440px);
        max-height: calc(100vh - 36px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .22);
        transform: translateY(24px) scale(.96);
        transition: transform .28s cubic-bezier(.22,.61,.36,1);
    }

    .aset-crop-mask.is-show .aset-crop-panel {
        transform: translateY(0) scale(1);
    }

    .aset-crop-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 18px 12px;
    }

    .aset-crop-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        color: #fff;
        font-size: 18px;
        background: linear-gradient(135deg, var(--theme-primary), var(--theme-secondary, var(--tp-light)));
        box-shadow: 0 10px 22px rgba(var(--tp-rgb), .2);
    }

    .aset-crop-title {
        color: var(--text-main);
        font-size: 17px;
        font-weight: 900;
        line-height: 1.25;
    }

    .aset-crop-sub {
        margin-top: 3px;
        color: var(--text-sub);
        font-size: 12px;
        line-height: 1.5;
    }

    .aset-crop-close {
        width: 34px;
        height: 34px;
        margin-left: auto;
        border: 0;
        border-radius: 50%;
        background: #f3f4f6;
        color: #8b95a5;
        cursor: pointer;
        transition: background .18s ease, color .18s ease, transform .18s ease;
    }

    .aset-crop-close:hover {
        background: rgba(var(--tp-rgb), .08);
        color: var(--theme-primary);
    }

    .aset-crop-close:active {
        transform: scale(.94);
    }

    .aset-crop-body {
        padding: 0 18px 10px;
        overflow: auto;
    }

    .aset-crop-stage {
        width: 100%;
        height: min(56vh, 360px);
        min-height: 280px;
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
        background: rgba(var(--tp-rgb), .07);
        color: var(--text-sub);
        font-size: 12px;
        line-height: 1.65;
    }

    .aset-crop-tip i {
        margin-top: 2px;
        color: var(--theme-primary);
    }

    .aset-crop-foot {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        gap: 10px;
        padding: 10px 18px calc(18px + env(safe-area-inset-bottom, 0px));
        border-top: 1px solid #f1f3f6;
        background: rgba(255,255,255,.96);
    }

    .aset-crop-btn {
        height: 42px;
        border: 0;
        border-radius: 14px;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
        transition: transform .16s ease, opacity .16s ease, background .16s ease;
    }

    .aset-crop-btn:active {
        transform: scale(.97);
    }

    .aset-crop-btn:disabled {
        opacity: .62;
        cursor: not-allowed;
    }

    .aset-crop-cancel {
        background: #f3f4f6;
        color: #5f6673;
    }

    .aset-crop-confirm {
        color: #fff;
        background: linear-gradient(135deg, var(--theme-primary), var(--theme-secondary, var(--tp-light)));
        box-shadow: 0 10px 22px rgba(var(--tp-rgb), .18);
    }

    .aset-crop-panel .cropper-view-box,
    .aset-crop-panel .cropper-face {
        border-radius: 50%;
    }

    .aset-crop-panel .cropper-view-box {
        outline-color: rgba(255,255,255,.95);
        outline-width: 2px;
        box-shadow: 0 0 0 1px rgba(var(--tp-rgb), .22);
    }

    @media (max-width: 560px) {
        .aset-crop-mask {
            align-items: flex-end;
            padding: 10px;
        }

        .aset-crop-panel {
            width: 100%;
            max-height: calc(100vh - 20px);
            border-radius: 22px 22px 18px 18px;
        }

        .aset-crop-stage {
            height: 52vh;
            min-height: 260px;
        }

        .aset-crop-foot {
            grid-template-columns: 1fr 1fr;
        }
    }

    .aset-notice {
        padding: 22px 24px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .aset-notice strong {
        display: block;
        margin-bottom: 8px;
        color: var(--text-main);
        font-size: 16px;
        font-weight: 800;
    }

    .aset-notice p {
        margin: 0;
        color: var(--text-sub);
        font-size: 13px;
        line-height: 1.85;
    }

    .aset-badge,
    .aset-inline-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(var(--tp-rgb),.08);
        color: var(--theme-primary);
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .aset-grid-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(0, .8fr);
        gap: 18px;
    }

    .aset-panel {
        padding: 24px;
    }

    .aset-panel-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 18px;
    }

    .aset-panel-title {
        margin: 0;
        color: var(--text-main);
        font-size: 18px;
        font-weight: 800;
    }

    .aset-panel-desc {
        margin: 8px 0 0;
        color: var(--text-sub);
        font-size: 13px;
        line-height: 1.8;
    }

    .aset-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 16px;
        overflow-x: auto;
        scrollbar-width: none;
    }

    .aset-tabs::-webkit-scrollbar {
        display: none;
    }

    .aset-tab {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-width: 110px;
        height: 42px;
        padding: 0 18px;
        border: 1px solid rgba(var(--tp-rgb),.1);
        border-radius: 999px;
        background: rgba(var(--tp-rgb),.04);
        color: var(--text-sub);
        font-size: 14px;
        font-weight: 700;
        transition: all .18s ease;
    }

    .aset-tab.is-active {
        background: linear-gradient(135deg, var(--theme-primary) 0%, var(--tp-light) 100%);
        border-color: transparent;
        color: #fff;
        box-shadow: 0 12px 28px rgba(var(--tp-rgb),.18);
    }

    .aset-panels {
        display: grid;
    }

    .aset-panel-item {
        display: none;
    }

    .aset-panel-item.is-active {
        display: block;
    }

    .aset-card {
        padding: 22px;
    }

    .aset-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .aset-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .aset-field.is-full {
        grid-column: 1 / -1;
    }

    .aset-field-tip {
        display: flex;
        align-items: flex-start;
        gap: 6px;
        margin-top: 7px;
        color: #94a3b8;
        font-size: 12px;
        line-height: 1.6;
    }

    .aset-field-tip i {
        margin-top: 2px;
        color: var(--theme-primary);
    }

    .aset-label,
    .aset-row-label,
    .aset-block-label {
        color: var(--text-main);
        font-size: 14px;
        font-weight: 700;
    }

    .aset-control {
        min-width: 0;
    }

    .aset-input,
    .aset-textarea {
        width: 100%;
        border: 1px solid rgba(148,163,184,.28);
        border-radius: 10px;
        background: rgba(248,250,252,.92);
        color: var(--text-main);
        font-size: 14px;
        outline: none;
        transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
    }

    .aset-input {
        height: 48px;
        padding: 0 16px;
    }

    .aset-textarea {
        min-height: 168px;
        line-height: 1.7;
        padding: 14px 16px;
        resize: none;
    }

    .aset-input:focus,
    .aset-textarea:focus {
        border-color: rgba(var(--tp-rgb),.46);
        box-shadow: 0 0 0 4px rgba(var(--tp-rgb),.1);
        background: #fff;
    }

    @keyframes asetHighlight {
        0%   { box-shadow: 0 0 0 4px rgba(var(--tp-rgb),.25); }
        50%  { box-shadow: 0 0 0 6px rgba(var(--tp-rgb),.15); }
        100% { box-shadow: 0 0 0 4px rgba(var(--tp-rgb),.25); }
    }
    .aset-input.is-highlight {
        border-color: var(--theme-primary);
        box-shadow: 0 0 0 4px rgba(var(--tp-rgb),.18);
        background: #fff;
        animation: asetHighlight 1s ease-in-out 2;
    }

    .aset-input::placeholder,
    .aset-textarea::placeholder {
        color: #94a3b8;
    }

    .aset-input[disabled],
    .aset-textarea[disabled] {
        color: #94a3b8;
        -webkit-text-fill-color: #94a3b8;
        opacity: 1;
        cursor: not-allowed;
    }

    .aset-submit-wrap {
        margin-top: 16px;
        text-align: right;
    }

    .aset-submit,
    .aset-disabled-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-width: 148px;
        height: 48px;
        padding: 0 20px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 800;
    }

    .aset-submit {
        border: 0;
        color: #fff;
        background: linear-gradient(135deg, var(--theme-primary) 0%, var(--tp-light) 100%);
        box-shadow: 0 14px 28px rgba(var(--tp-rgb),.18);
    }

    .aset-submit:hover {
        transform: translateY(-1px);
    }

    .aset-submit[disabled] {
        opacity: .7;
        cursor: wait;
    }

    .aset-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .aset-social {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        padding: 18px 12px;
        border-radius: 10px;
        background: rgba(var(--tp-rgb),.04);
        text-decoration: none;
        color: var(--text-main);
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .aset-social:hover {
        color: var(--text-main);
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: 0 14px 26px rgba(15,23,42,.08);
    }

    .aset-social-icon {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: linear-gradient(135deg, #cbd5e1 0%, #94a3b8 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .aset-social.is-accent .aset-social-icon {
        background: linear-gradient(135deg, var(--theme-primary) 0%, var(--tp-light) 100%);
    }

    .aset-social-name {
        font-size: 13px;
        font-weight: 700;
        line-height: 1.4;
        text-align: center;
        word-break: break-word;
    }

    .aset-soft-tip,
    .aset-doc {
        margin-top: 14px;
        color: var(--text-sub);
        font-size: 13px;
        line-height: 1.85;
    }

    .aset-doc span {
        color: var(--theme-primary);
        font-weight: 700;
    }

    .aset-secret {
        display: grid;
        gap: 12px;
    }

    .aset-row,
    .aset-info-item,
    .aset-shortcut {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 18px;
        border-radius: 10px;
        background: rgba(var(--tp-rgb),.04);
    }

    .aset-row-value,
    .aset-info-value {
        color: var(--text-main);
        font-size: 14px;
        font-weight: 800;
        text-align: right;
        word-break: break-all;
    }

    .aset-row-value.is-danger,
    .aset-info-value.is-muted {
        color: var(--text-sub);
        font-weight: 600;
    }

    .aset-block {
        margin-top: 4px;
    }

    .aset-side-stack {
        display: grid;
        gap: 18px;
        align-self: start;
        position: sticky;
        top: 120px;
    }

    .aset-profile-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 18px;
        border-radius: 10px;
        background: rgba(var(--tp-rgb),.04);
        margin-bottom: 16px;
    }

    .aset-avatar {
        width: 78px;
        height: 78px;
        border-radius: 50%;
        overflow: hidden;
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--theme-primary), var(--tp-light));
        color: #fff;
        font-size: 28px;
        font-weight: 900;
        box-shadow: 0 14px 28px rgba(var(--tp-rgb),.18);
    }

    .aset-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .aset-profile-name {
        margin: 0 0 6px;
        color: var(--text-main);
        font-size: 22px;
        font-weight: 800;
    }

    .aset-profile-desc {
        margin: 0;
        color: var(--text-sub);
        font-size: 13px;
        line-height: 1.8;
    }

    .aset-info-list,
    .aset-shortcuts {
        display: grid;
        gap: 12px;
    }

    .aset-info-label {
        color: var(--text-sub);
        font-size: 13px;
        font-weight: 700;
    }

    .aset-shortcut {
        text-decoration: none;
        color: var(--text-main);
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .aset-shortcut:hover {
        color: var(--text-main);
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(15,23,42,.08);
    }

    .aset-shortcut-main {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 800;
    }

    .aset-shortcut i:last-child {
        color: var(--text-sub);
    }

    /* 邮箱绑定区域 */
    .aset-email-wrap { position: relative; }
    .aset-email-wrap .aset-input { padding-right: 100px; }
    .aset-email-btn {
        position: absolute; right: 4px; top: 50%; transform: translateY(-50%);
        height: 38px; padding: 0 14px;
        border-radius: 8px; border: none;
        background: transparent; color: var(--theme-primary);
        font-size: 13px; font-weight: 700; cursor: pointer;
        white-space: nowrap; transition: background .18s;
    }
    .aset-email-btn:hover { background: rgba(var(--tp-rgb),.08); }
    .aset-email-btn[disabled] { opacity: .5; cursor: not-allowed; }
    .aset-email-btn.is-danger { color: #ef4444; }
    .aset-email-btn.is-danger:hover { background: rgba(239,68,68,.06); }
    .aset-email-warn { color: #f59e0b; font-size: 12px; margin-top: 8px; display: flex; align-items: center; gap: 4px; }

    /* 手机绑定区域（复用邮箱绑定样式） */
    .aset-phone-wrap { position: relative; }
    .aset-phone-wrap .aset-input { padding-right: 100px; }
    .aset-phone-btn {
        position: absolute; right: 4px; top: 50%; transform: translateY(-50%);
        height: 38px; padding: 0 14px;
        border-radius: 8px; border: none;
        background: transparent; color: var(--theme-primary);
        font-size: 13px; font-weight: 700; cursor: pointer;
        white-space: nowrap; transition: background .18s;
    }
    .aset-phone-btn:hover { background: rgba(var(--tp-rgb),.08); }
    .aset-phone-btn[disabled] { opacity: .5; cursor: not-allowed; }
    .aset-phone-btn.is-danger { color: #ef4444; }
    .aset-phone-btn.is-danger:hover { background: rgba(239,68,68,.06); }
    .aset-phone-warn { color: #f59e0b; font-size: 12px; margin-top: 8px; display: flex; align-items: center; gap: 4px; }

    @media (max-width: 1200px) {
        .aset-metrics {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .aset-grid-layout {
            grid-template-columns: 1fr;
        }

        .aset-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .aset-side-stack {
            position: static;
            top: auto;
        }
    }

    @media (max-width: 768px) {
        .aset-page {
            padding: 0 0 calc(24px + env(safe-area-inset-bottom));
            gap: 14px;
        }

        .aset-notice {
            flex-direction: column;
            align-items: flex-start;
            padding: 18px 20px;
            border-radius: 10px;
        }

        .aset-metrics,
        .aset-form-grid,
        .aset-grid {
            grid-template-columns: 1fr;
        }

        .aset-panel,
        .aset-card {
            padding: 18px;
            border-radius: 10px;
        }

        .aset-avatar-row {
            padding: 14px;
            gap: 14px;
            margin-bottom: 14px;
        }

        .aset-avatar-preview {
            width: 72px;
            height: 72px;
            font-size: 26px;
        }

        .aset-panel-head,
        .aset-profile-card,
        .aset-row,
        .aset-info-item,
        .aset-shortcut {
            flex-direction: column;
            align-items: flex-start;
        }

        .aset-row-value,
        .aset-info-value {
            text-align: left;
        }

        .aset-submit,
        .aset-disabled-btn {
            width: 100%;
        }
    }
    /* 密钥弹窗 - 与移动端邀请好友页设置微信号弹窗统一风格 */
    .akey-modal-mask{position:fixed;inset:0;z-index:19999;background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;pointer-events:none;transition:opacity .25s,visibility .25s}
    .akey-modal-mask.is-show{opacity:1;visibility:visible;pointer-events:auto}
    .akey-modal{width:min(88vw,360px);background:#fff;border-radius:20px;overflow:hidden;transform:translateY(24px) scale(.96);transition:transform .28s cubic-bezier(.22,.61,.36,1);box-shadow:0 20px 50px rgba(0,0,0,.18)}
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
    <header class="aset-page-header">
        <div>
            <h1 class="aset-page-title">信息设置</h1>
            <p class="aset-page-desc">管理账户资料、绑定信息和对接密钥</p>
        </div>
    </header>

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
            </div>

            <div class="aset-panels">
                <div class="aset-panel-item is-active" data-panel="profile">
                    <form id="accountSettingForm" action="/user/account.php?action=setting_save" method="post">
                        <input type="hidden" name="token" value="<?= LoginAuth::genToken() ?>">
                        
                            <div class="aset-avatar-row">
                                <div class="aset-avatar-preview" id="asetAvatarPreview">
                                    <img id="asetAvatarImg" src="<?= htmlspecialchars($settingAvatar) ?>" alt="头像">
                                </div>
                                <div class="aset-avatar-meta">
                                    <div class="aset-avatar-title">账户头像</div>
                                    <div class="aset-avatar-tip">支持 JPG / PNG / GIF / WEBP，文件大小 ≤ 2MB</div>
                                    <div class="aset-avatar-actions">
                                        <button type="button" class="aset-avatar-btn" id="asetAvatarBtn"><i class="fa fa-camera"></i> 更换头像</button>
                                        <input type="file" id="asetAvatarInput" accept="image/jpeg,image/png,image/gif,image/webp" hidden>
                                    </div>
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
                                        <div class="aset-field-tip"><i class="fa fa-info-circle"></i><span>设置后会展示给您的上级成员和团队成员，便于沟通交流。</span></div>
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
                                <div class="aset-row-label">对接链接</div>
                                <div class="aset-row-value" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                    <span id="api-link" style="font-family:monospace;word-break:break-all;font-size:13px;letter-spacing:0.2px;cursor:pointer;" title="点击复制对接链接"><?= htmlspecialchars($settingDockingLink) ?></span>
                                    <button type="button" id="api-link-copy-btn" style="background:none;border:none;color:#8b95a5;cursor:pointer;padding:4px;font-size:16px;outline:none;" title="复制对接链接"><i class="ri-file-copy-line"></i></button>
                                </div>
                            </div>
                            <div class="aset-row">
                                <div class="aset-row-label">对接 ID</div>
                                <div class="aset-row-value" style="display:flex;align-items:center;gap:8px;">
                                    <span id="api-uid"><?= $settingUid ?></span>
                                    <button type="button" id="api-uid-copy-btn" style="background:none;border:none;color:#8b95a5;cursor:pointer;padding:4px;font-size:16px;outline:none;" title="复制对接ID"><i class="ri-file-copy-line"></i></button>
                                </div>
                            </div>
                            <div class="aset-row">
                                <div class="aset-row-label">对接密钥</div>
                                <div class="aset-row-value" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                                    <span id="api-key-display" style="font-family:monospace;word-break:break-all;font-size:13px;letter-spacing:0.5px;cursor:pointer;" title="点击复制密钥">未生成</span>
                                    <button type="button" id="api-key-copy-btn" style="display:none;background:none;border:none;color:#8b95a5;cursor:pointer;padding:4px;font-size:16px;outline:none;" title="复制密钥"><i class="ri-file-copy-line"></i></button>
                                    <button type="button" id="api-key-reset-btn" style="display:none;background:none;border:none;color:#8b95a5;cursor:pointer;padding:4px;font-size:18px;outline:none;" title="重置密钥"><i class="ri-refresh-line"></i></button>
                                    <button type="button" class="aset-submit" id="api-key-gen-btn" style="height:30px;padding:0 12px;font-size:12px;line-height:30px;width:auto;min-width:0;border-radius:8px;display:none;">
                                        <span>生成密钥</span>
                                    </button>
                                </div>
                            </div>
                            <div class="aset-block">
                                <div class="aset-block-label">IP白名单</div>
                                <textarea class="aset-textarea" id="api-whitelist-input" placeholder="一行一个IP，留空不限制任何IP" style="height:90px;"></textarea>
                                <div class="aset-submit-wrap">
                                    <button class="aset-submit" type="button" id="api-whitelist-save-btn"><i class="fa fa-save"></i> 保存白名单</button>
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

        <aside class="aset-side-stack">
            <div class="aset-metric">
                <div class="aset-metric-label">账户 ID</div>
                <div class="aset-metric-value">#<?= $settingUid ?></div>
                <div class="aset-metric-note">当前账号编号</div>
            </div>
            <div class="aset-metric">
                <div class="aset-metric-label">资料完善度</div>
                <div class="aset-metric-value"><?= $bindCount ?>/3</div>
                <div class="aset-metric-note">头像、手机、邮箱完成情况</div>
            </div>
            <div class="aset-metric">
                <div class="aset-metric-label">安全等级</div>
                <div class="aset-metric-value"><?= $safetyText ?></div>
                <div class="aset-metric-note">完善绑定信息可提升安全性</div>
            </div>
            <div class="aset-metric">
                <div class="aset-metric-label">当前登录账号</div>
                <div class="aset-metric-value"><?= htmlspecialchars($settingUsername) ?></div>
                <div class="aset-metric-note">当前登录使用的账号</div>
            </div>
        </aside>
    </section>
</main>

<div class="aset-crop-mask" id="asetAvatarCropModal">
    <div class="aset-crop-panel">
        <div class="aset-crop-head">
            <div class="aset-crop-icon"><i class="fa fa-crop"></i></div>
            <div>
                <div class="aset-crop-title">裁切头像</div>
                <div class="aset-crop-sub">拖动图片选择圆形头像区域</div>
            </div>
            <button type="button" class="aset-crop-close" id="asetCropClose" aria-label="关闭"><i class="fa fa-times"></i></button>
        </div>
        <div class="aset-crop-body">
            <div class="aset-crop-stage">
                <img id="asetCropImage" src="" alt="裁切头像">
            </div>
            <div class="aset-crop-tip"><i class="fa fa-info-circle"></i><span>建议使用清晰正方形图片。可以拖动、缩放图片，确认后会自动保存头像。</span></div>
        </div>
        <div class="aset-crop-foot">
            <button type="button" class="aset-crop-btn aset-crop-cancel" id="asetCropCancel">取消</button>
            <button type="button" class="aset-crop-btn aset-crop-confirm" id="asetCropConfirm">裁切并保存</button>
        </div>
    </div>
</div>

<!-- 密钥重置确认弹窗 -->
<div class="akey-modal-mask" id="akeyConfirmModal">
    <div class="akey-modal">
        <div class="akey-modal-header">
            <div class="akey-modal-icon is-warn"><i class="ri-key-2-line"></i></div>
            <div class="akey-modal-title">确认重置对接密钥？</div>
        </div>
        <div class="akey-modal-body">
            <div class="akey-modal-desc">重置后旧密钥将立即失效，您必须更新相关对接系统的配置，否则对接功能将中断。</div>
        </div>
        <div class="akey-modal-foot">
            <button type="button" class="akey-modal-btn akey-modal-cancel" id="akeyConfirmCancel">再想想</button>
            <button type="button" class="akey-modal-btn akey-modal-confirm is-danger" id="akeyConfirmOk">确认重置</button>
        </div>
        <div class="akey-modal-foot-note">重置后请及时更新对接配置</div>
    </div>
</div>

<!-- 密钥重置成功弹窗 -->
<div class="akey-modal-mask" id="akeySuccessModal">
    <div class="akey-modal">
        <div class="akey-modal-header">
            <div class="akey-modal-icon is-ok"><i class="ri-checkbox-circle-line"></i></div>
            <div class="akey-modal-title">密钥已重置成功</div>
        </div>
        <div class="akey-modal-body">
            <div class="akey-modal-key-box" id="akeyNewKeyDisplay"></div>
            <div class="akey-modal-notice">
                <div class="akey-modal-notice-title"><i class="ri-information-line"></i> 温馨提示</div>
                <div>对接密钥已成功重置，此后可在页面点击眼睛图标随时查看明文。</div>
            </div>
        </div>
        <div class="akey-modal-foot">
            <button type="button" class="akey-modal-btn akey-modal-cancel" id="akeySuccessClose">关闭</button>
            <button type="button" class="akey-modal-btn akey-modal-confirm" id="akeySuccessCopy">复制密钥</button>
        </div>
        <div class="akey-modal-foot-note">可随时在此页面查看密钥</div>
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

    $('.aset-tab').on('click', function () {
        var tab = $(this).data('tab');
        $('.aset-tab').removeClass('is-active');
        $(this).addClass('is-active');
        $('.aset-panel-item').removeClass('is-active');
        $('.aset-panel-item[data-panel="' + tab + '"]').addClass('is-active');
    });

    // APP 移动端样式头像裁切器
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

    $('#asetAvatarBtn,#asetAvatarPreview').on('click', function() {
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

    $('#asetCropCancel,#asetCropClose').on('click', closeAvatarCropper);
    $('#asetAvatarCropModal').on('click', function(e) {
        if (e.target === this && !avatarSubmitting) closeAvatarCropper();
    });
    $('#asetCropConfirm').on('click', function() {
        if (!avatarCropper) return layer.msg('请先选择头像图片', {icon: 0});
        avatarCropper.getCroppedCanvas({
            width: 512,
            height: 512,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        }).toBlob(function(blob) {
            if (!blob) return layer.msg('裁切失败，请重试', {icon: 2});
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
    function loadApiKey() {
        $.post('/user/account.php?action=api_key_get', {}, function(res) {
            if (res.code === 0) {
                var d = res.data;
                if (d.has_key) {
                    $('#api-key-display').text(d.api_key);
                    $('#api-key-copy-btn').show();
                    $('#api-key-reset-btn').show();
                    $('#api-key-gen-btn').hide();
                } else {
                    $('#api-key-display').text('未生成');
                    $('#api-key-copy-btn').hide();
                    $('#api-key-reset-btn').hide();
                    $('#api-key-gen-btn').show();
                }
                $('#api-whitelist-input').val(d.whitelist);
            }
        }, 'json');
    }

    // 复制对接ID
    $('#api-uid-copy-btn').on('click', function() {
        copyApiKeyText($('#api-uid').text(), '对接ID已复制到剪贴板');
    });

    // 复制对接链接
    $('#api-link-copy-btn,#api-link').on('click', function() {
        copyApiKeyText($('#api-link').text(), '对接链接已复制到剪贴板');
    });

    // 复制密钥（图标按钮）
    $('#api-key-copy-btn').on('click', function() {
        var key = $('#api-key-display').text();
        if (key && key !== '未生成') copyApiKeyText(key, '对接密钥已复制到剪贴板');
    });

    // 点击密钥文字也可复制
    $('#api-key-display').on('click', function() {
        var key = $(this).text();
        if (key && key !== '未生成') copyApiKeyText(key, '对接密钥已复制到剪贴板');
    });

    // 重置密钥（图标按钮）
    $('#api-key-reset-btn').on('click', function() {
        showAkeyModal('akeyConfirmModal');
    });

    // 生成密钥按钮
    $('#api-key-gen-btn').on('click', function() {
        doResetKey();
    });

    var _akeyNewKey = '';

    function showAkeyModal(id) { $('#' + id).addClass('is-show'); }
    function hideAkeyModal(id) { $('#' + id).removeClass('is-show'); }

    // 点击遮罩关闭
    $('.akey-modal-mask').on('click', function(e) { if (e.target === this) hideAkeyModal(this.id); });

    // 确认弹窗按钮
    $('#akeyConfirmCancel').on('click', function() { hideAkeyModal('akeyConfirmModal'); });
    $('#akeyConfirmOk').on('click', function() {
        hideAkeyModal('akeyConfirmModal');
        doResetKey();
    });

    // 成功弹窗按钮
    $('#akeySuccessClose').on('click', function() { hideAkeyModal('akeySuccessModal'); });
    $('#akeySuccessCopy').on('click', function() {
        copyApiKeyText(_akeyNewKey, '密钥已成功复制到剪贴板');
    });

    function doResetKey() {
        var idx = layer.load();
        var token = $('input[name="token"]').val();
        $.post('/user/account.php?action=api_key_reset', {token: token}, function(res) {
            layer.close(idx);
            if (res.code === 0) {
                _akeyNewKey = res.data.api_key;
                $('#akeyNewKeyDisplay').text(res.data.api_key);
                showAkeyModal('akeySuccessModal');
                loadApiKey();
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

    function copyApiKeyText(text, tip) {
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

    $('#api-whitelist-save-btn').on('click', function() {
        var whitelist = $('#api-whitelist-input').val();
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

    loadApiKey();
});
</script>
<?php include __DIR__ . '/_pc_page_footer.php'; ?>

