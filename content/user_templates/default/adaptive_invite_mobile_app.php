<?php defined('DC_ROOT') || exit('access denied!'); ?>
<?php
global $userData;
$_inviteDomainOptions = isset($inviteDomainOptions) && is_array($inviteDomainOptions) ? array_values($inviteDomainOptions) : [];
$_myInviteCode = isset($myInviteCode) ? (string)$myInviteCode : '';
$_myInviteLink = isset($myInviteLink) ? (string)$myInviteLink : '';
$_inviteCount = isset($inviteCount) ? (int)$inviteCount : 0;
$_totalFansCount = isset($totalFansCount) ? (int)$totalFansCount : 0;
$_myWechat = trim((string)($myWechat ?? ($userData['wechat'] ?? '')));
$_hasWechat = $_myWechat !== '';
?>
<style>
    * { box-sizing: border-box; }
    .uc-site-footer { display: none !important; }
    .mia-page {
        min-height: 100vh;
        background: #f4f5f7;
        padding-bottom: calc(24px + env(safe-area-inset-bottom, 0px));
        -webkit-tap-highlight-color: transparent;
        -webkit-font-smoothing: antialiased;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }
    .mia-hero {
        position: relative;
        overflow: hidden;
        padding: 22px 16px 18px;
        background: linear-gradient(160deg, var(--theme-primary, #667eea) 0%, var(--theme-secondary, #764ba2) 100%);
        color: #fff;
    }
    .mia-hero::before {
        content: '';
        position: absolute;
        right: -48px;
        top: -54px;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
        pointer-events: none;
    }
    .mia-hero::after {
        content: '';
        position: absolute;
        left: 32%;
        bottom: -64px;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: rgba(255,255,255,.05);
        pointer-events: none;
    }
    .mia-hero-main { position: relative; z-index: 1; }
    .mia-hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 28px;
        padding: 0 10px;
        border-radius: 999px;
        background: rgba(255,255,255,.14);
        color: rgba(255,255,255,.88);
        font-size: 12px;
        font-weight: 600;
    }
    .mia-hero-title {
        margin-top: 12px;
        font-size: 25px;
        line-height: 1.15;
        font-weight: 800;
        letter-spacing: -.3px;
    }
    .mia-hero-desc {
        margin-top: 8px;
        max-width: 280px;
        font-size: 13px;
        line-height: 1.6;
        color: rgba(255,255,255,.76);
    }
    .mia-stat-grid {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 18px;
    }
    .mia-stat {
        min-width: 0;
        padding: 13px 12px;
        border-radius: 16px;
        background: rgba(255,255,255,.13);
        border: 1px solid rgba(255,255,255,.12);
        color: #fff;
        text-decoration: none;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
    .mia-stat:active { transform: scale(.98); }
    .mia-stat-num {
        font-size: 24px;
        line-height: 1;
        font-weight: 800;
        font-feature-settings: 'tnum';
    }
    .mia-stat-label {
        margin-top: 7px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
        font-size: 12px;
        color: rgba(255,255,255,.76);
    }
    .mia-shell {
        padding: 12px 12px 0;
    }
    .mia-card {
        background: linear-gradient(0deg, #fff, #f3f5f8);
        border-radius: 10px;
        overflow: hidden;
        box-shadow: var(--shadow-primary);
        border: 2px solid #fff;
        margin-bottom: 12px;
    }
    .mia-card-pad { padding: 16px; }
    .mia-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 13px;
    }
    .mia-card-title {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
        font-size: 16px;
        color: #1f2937;
        font-weight: 800;
    }
    .mia-card-title i {
        width: 32px;
        height: 32px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(var(--tp-rgb), .08);
        color: var(--theme-primary, #667eea);
        font-size: 17px;
        flex-shrink: 0;
    }
    .mia-head-link {
        display: inline-flex;
        align-items: center;
        gap: 2px;
        color: var(--theme-primary, #667eea);
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }
    .mia-line-card {
        display: flex;
        align-items: center;
        gap: 11px;
        min-height: 58px;
        padding: 12px;
        border-radius: 10px;
        background: linear-gradient(0deg, #fff, #f3f5f8);
        border: 2px solid #fff;
        box-shadow: var(--shadow-primary);
    }
    .mia-line-card + .mia-line-card { margin-top: 10px; }
    .mia-line-main { flex: 1; min-width: 0; }
    .mia-line-label {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 6px;
        color: #8a94a6;
        font-size: 12px;
        font-weight: 600;
    }
    .mia-line-value {
        color: #111827;
        font-size: 15px;
        font-weight: 800;
        line-height: 1.25;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        user-select: all;
    }
    .mia-code-value {
        font-size: 22px;
        letter-spacing: 1.5px;
        font-family: 'SF Mono', Menlo, Consolas, monospace;
        color: var(--theme-primary, #667eea);
    }
    .mia-link-value {
        font-family: 'SF Mono', Menlo, Consolas, monospace;
        font-size: 12px;
        color: #334155;
        font-weight: 600;
    }
    .mia-copy-btn {
        flex: 0 0 auto;
        height: 34px;
        min-width: 62px;
        border: none;
        border-radius: 13px;
        background: var(--theme-primary, #667eea);
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        box-shadow: 0 6px 14px rgba(var(--tp-rgb), .18);
    }
    .mia-copy-btn:active { transform: scale(.96); }
    .mia-domain-wrap {
        margin-top: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 12px;
        border-radius: 15px;
        background: rgba(var(--tp-rgb), .05);
        color: #64748b;
        font-size: 12px;
    }
    .mia-domain-wrap i { color: var(--theme-primary, #667eea); font-size: 15px; }
    .mia-domain-select {
        flex: 1;
        min-width: 0;
        height: 32px;
        border: 0;
        border-radius: 10px;
        background: rgba(255,255,255,.86);
        color: #334155;
        padding: 0 10px;
        font-size: 12px;
        outline: none;
    }
    .mia-action-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 12px;
    }
    .mia-action-btn {
        height: 44px;
        border: none;
        border-radius: 15px;
        background: #f3f4f6;
        color: #4b5563;
        font-size: 13px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .mia-action-btn.is-primary {
        background: linear-gradient(135deg, var(--theme-primary, #667eea), var(--theme-secondary, #764ba2));
        color: #fff;
        box-shadow: 0 8px 18px rgba(var(--tp-rgb), .16);
    }
    .mia-action-btn:active { transform: scale(.98); }
    .mia-poster-strip {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding: 2px 1px 4px;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }
    .mia-poster-strip::-webkit-scrollbar { display: none; }
    .mia-poster-item {
        flex: 0 0 112px;
        border-radius: 10px;
        overflow: hidden;
        background: linear-gradient(0deg, #fff, #f3f5f8);
        border: 2px solid #fff;
        position: relative;
        box-shadow: var(--shadow-primary);
    }
    .mia-poster-item:active { transform: scale(.98); }
    .mia-poster-item img {
        width: 100%;
        display: block;
    }
    .mia-poster-tag {
        position: absolute;
        left: 8px;
        right: 8px;
        bottom: 8px;
        height: 28px;
        border-radius: 12px;
        background: rgba(17,24,39,.58);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        font-size: 11px;
        font-weight: 700;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
    }
    .mia-step-list { padding: 3px 0 0; }
    .mia-step {
        display: flex;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f1f3f6;
    }
    .mia-step:last-child { border-bottom: none; padding-bottom: 0; }
    .mia-step-num {
        width: 30px;
        height: 30px;
        border-radius: 12px;
        background: rgba(var(--tp-rgb), .08);
        color: var(--theme-primary, #667eea);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 800;
        flex-shrink: 0;
    }
    .mia-step-body { flex: 1; min-width: 0; }
    .mia-step-title { color: #1f2937; font-size: 14px; font-weight: 800; }
    .mia-step-desc { margin-top: 4px; color: #8a94a6; font-size: 12px; line-height: 1.5; }
    .mia-empty {
        padding: 60px 16px;
        text-align: center;
        color: #9ca3af;
    }
    .mia-empty svg {
        display: block;
        width: 140px;
        height: auto;
        margin: 0 auto 10px;
    }
    .mia-empty i {
        display: block;
        margin-bottom: 10px;
        color: #cbd5e1;
        font-size: 42px;
    }
    .mia-empty-title {
        color: #475569;
        font-size: 15px;
        font-weight: 800;
    }
    .mia-empty-desc {
        margin-top: 6px;
        font-size: 12px;
        line-height: 1.6;
    }
    .mia-poster-modal {
        height: 100%;
        display: flex;
        flex-direction: column;
        background: #f4f5f7;
        overflow: hidden;
    }
    .mia-poster-modal-body {
        position: relative;
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        padding: 16px;
        text-align: center;
        -webkit-overflow-scrolling: touch;
    }
    .mia-poster-modal-img {
        width: 100%;
        max-width: 360px;
        border-radius: 18px;
        box-shadow: 0 16px 38px rgba(15,23,42,.18);
    }
    .mia-poster-modal-foot {
        flex-shrink: 0;
        padding: 12px 14px calc(14px + env(safe-area-inset-bottom, 0px));
        background: #fff;
        border-top: 1px solid #eef2f7;
    }
    .mia-poster-modal-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
    .mia-poster-modal-btn {
        height: 43px;
        border: none;
        border-radius: 15px;
        font-size: 14px;
        font-weight: 800;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .mia-poster-modal-btn.is-primary {
        background: linear-gradient(135deg, var(--theme-primary, #667eea), var(--theme-secondary, #764ba2));
        color: #fff;
    }
    .mia-poster-modal-btn.is-plain {
        background: #f3f4f6;
        color: #4b5563;
    }
    .mia-poster-modal-hint {
        margin-top: 10px;
        text-align: center;
        color: #9ca3af;
        font-size: 12px;
    }
    .mia-poster-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: rgba(255,255,255,.88);
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 18px rgba(15,23,42,.16);
        z-index: 3;
    }
    .mia-poster-prev { left: 10px; }
    .mia-poster-next { right: 10px; }

    /* 白色版：参考截图的邀请卡片 + 海报滑动布局 */

    .mia-screen { padding: 12px 14px 0; }
    .mia-contact {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 44px;
        padding: 8px 10px;
        border-radius: 10px;
        background: linear-gradient(0deg, #fff, #f3f5f8);
        border: 2px solid #fff;
        box-shadow: var(--shadow-primary);
        text-decoration: none;
        width: 100%;
        font-family: inherit;
        cursor: pointer;
    }
    .mia-wx-ico {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #23c55e;
        color: #fff;
        font-size: 16px;
        flex-shrink: 0;
    }
    .mia-contact-main {
        flex: 1;
        min-width: 0;
        color: #4b5563;
        font-size: 14px;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .mia-contact-btn {
        height: 30px;
        padding: 0 12px;
        border-radius: 999px;
        background: linear-gradient(135deg, #f7d489, #f1b95d);
        color: #6f4512;
        font-size: 12px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .mia-app-modal-mask{position:fixed;inset:0;z-index:19999;background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:opacity .25s,visibility .25s}
    .mia-app-modal-mask.is-show{opacity:1;visibility:visible}
    .mia-app-modal{width:min(88vw,340px);background:#fff;border-radius:20px;overflow:hidden;transform:translateY(24px) scale(.96);transition:transform .28s cubic-bezier(.22,.61,.36,1);box-shadow:0 20px 50px rgba(0,0,0,.18)}
    .mia-app-modal-mask.is-show .mia-app-modal{transform:translateY(0) scale(1)}
    .mia-app-modal-header{padding:22px 22px 0;text-align:center}
    .mia-app-modal-icon{width:52px;height:52px;margin:0 auto 12px;border-radius:50%;background:linear-gradient(135deg,var(--theme-primary,#667eea),var(--theme-secondary,#764ba2));color:#fff;display:flex;align-items:center;justify-content:center;font-size:24px}
    .mia-app-modal-title{font-size:17px;font-weight:800;color:#252d3b}
    .mia-app-modal-body{padding:0 22px 6px;text-align:center}
    .mia-app-wechat-field{text-align:left}
    .mia-app-wechat-label{display:flex;align-items:center;gap:6px;margin-bottom:9px;color:#252d3b;font-size:13px;font-weight:800}
    .mia-app-wechat-input{width:100%;height:44px;border:1px solid #eceef2;border-radius:14px;outline:none;padding:0 12px;background:#f8f9fb;color:#20242c;font-size:14px;font-weight:700}
    .mia-app-wechat-input:focus{border-color:rgba(var(--tp-rgb,102,126,234),.35);box-shadow:0 0 0 4px rgba(var(--tp-rgb,102,126,234),.12);background:#fff}
    .mia-app-modal-notice{margin-top:12px;padding:12px;border-radius:13px;background:#eef5ff;border:1px solid #dbeafe;color:#5f6673;font-size:12px;line-height:1.75;text-align:left}
    .mia-app-modal-notice-title{display:flex;align-items:center;gap:6px;margin-bottom:6px;color:#252d3b;font-size:13px;font-weight:900}
    .mia-app-modal-foot{display:flex;gap:10px;padding:10px 22px 8px}
    .mia-app-modal-foot-note{padding:0 22px 18px;text-align:center;color:#a0a7b4;font-size:12px;line-height:1.5}
    .mia-app-modal-btn{flex:1;height:44px;border:none;border-radius:14px;font-size:15px;font-weight:700;cursor:pointer;transition:.15s;font-family:inherit}
    .mia-app-modal-btn:disabled{opacity:.65}
    .mia-app-modal-cancel{background:#f3f4f6;color:#5f6673}
    .mia-app-modal-cancel:active{background:#e8ebf0}
    .mia-app-modal-confirm{background:linear-gradient(135deg,var(--theme-primary,#667eea),var(--theme-secondary,#764ba2));color:#fff;box-shadow:0 6px 16px rgba(var(--tp-rgb,102,126,234),.22)}
    .mia-app-modal-confirm:active{transform:scale(.97)}
    .mia-code-wrap {
        position: relative;
        padding: 24px 0 12px;
        text-align: center;
    }
    .mia-code-ticket {
        position: relative;
        width: 230px;
        margin: 0 auto;
        padding: 18px 18px 14px;
        border-radius: 12px;
        background: linear-gradient(145deg, #fff4d7 0%, #f8c969 100%);
        border: 1px solid rgba(178,124,41,.28);
        box-shadow: 0 14px 26px rgba(177,119,41,.16);
        text-align: left;
    }
    .mia-code-ticket::before {
        content: '';
        position: absolute;
        left: -10px;
        right: -10px;
        top: -11px;
        height: 20px;
        border-radius: 999px;
        background: linear-gradient(180deg, #e9edf2, #bac1c9);
        box-shadow: inset 0 1px 0 rgba(255,255,255,.7), 0 2px 8px rgba(15,23,42,.08);
    }
    .mia-ticket-label {
        margin-bottom: 5px;
        color: #8a6330;
        font-size: 12px;
        font-weight: 700;
    }
    .mia-code-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .mia-ticket-code {
        flex: 1;
        min-width: 0;
        color: #7a4214;
        font-size: 24px;
        line-height: 1;
        font-weight: 800;
        letter-spacing: 1px;
        font-family: 'SF Mono', Menlo, Consolas, monospace;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        user-select: all;
    }
    .mia-ticket-copy {
        border: none;
        height: 26px;
        padding: 0 12px;
        border-radius: 999px;
        background: #d92f3a;
        color: #fff;
        font-size: 12px;
        font-weight: 800;
        flex-shrink: 0;
    }
    .mia-mini-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 16px;
    }
    .mia-mini-stat {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 10px 12px;
        border-radius: 10px;
        background: linear-gradient(0deg, #fff, #f3f5f8);
        border: 2px solid #fff;
        color: #374151;
        text-decoration: none;
        box-shadow: var(--shadow-primary);
    }
    .mia-mini-stat b {
        color: #1f2937;
        font-size: 20px;
        line-height: 1;
    }
    .mia-mini-stat span {
        display: block;
        margin-top: 3px;
        color: #8a94a6;
        font-size: 11px;
        font-weight: 700;
    }
    .mia-rule-card {
        margin-top: 14px;
        padding: 13px 14px;
        border-radius: 10px;
        background: linear-gradient(0deg, #fff, #f3f5f8);
        border: 2px solid #fff;
        box-shadow: var(--shadow-primary);
        color: #4b5563;
        font-size: 12px;
        line-height: 1.8;
    }
    .mia-rule-title {
        margin: 0 0 5px;
        color: #1f2937;
        font-size: 13px;
        font-weight: 900;
    }
    .mia-rule-card b { color: #111827; }
    .mia-domain-pill {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 10px;
        padding: 8px 10px;
        border-radius: 12px;
        background: #fffaf0;
        color: #8a6330;
    }
    .mia-domain-pill select {
        flex: 1;
        min-width: 0;
        height: 30px;
        border: 0;
        border-radius: 9px;
        background: #fff;
        color: #374151;
        padding: 0 8px;
        font-size: 12px;
        outline: none;
    }
    .mia-poster-area {
        margin-top: 16px;
        /* 底部有固定操作栏，留出可滚动空白，避免海报/圆点被遮挡 */
        padding-bottom: calc(118px + env(safe-area-inset-bottom, 0px));
    }
    .mia-poster-list {
        display: flex;
        gap: 14px;
        overflow-x: auto;
        padding: 2px 18px 10px 2px;
        margin-right: -14px;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .mia-poster-list::-webkit-scrollbar { display: none; }
    .mia-poster-card {
        flex: 0 0 252px;
        position: relative;
        border-radius: 10px;
        overflow: hidden;
        background: linear-gradient(0deg, #fff, #f3f5f8);
        border: 2px solid #fff;
        box-shadow: var(--shadow-primary);
        scroll-snap-align: start;
    }
    .mia-poster-card:active { transform: scale(.985); }
    .mia-poster-card img {
        display: block;
        width: 100%;
        height: 448px;
        object-fit: cover;
    }
    .mia-poster-qr-mask {
        position: absolute;
        left: var(--qr-cx, 50%);
        top: var(--qr-cy, 54%);
        width: var(--qr-size, 38%);
        aspect-ratio: 1 / 1;
        transform: translate(-50%, -50%);
        border-radius: 10px;
        background: rgba(17,24,39,.54);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
        text-align: center;
        font-size: 12px;
        line-height: 1.25;
        font-weight: 800;
        letter-spacing: .2px;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,.35), 0 6px 14px rgba(0,0,0,.16);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        pointer-events: none;
        z-index: 2;
    }
    .mia-poster-card.is-generated .mia-poster-qr-mask { display: none; }
    .mia-poster-check {
        position: absolute;
        right: 12px;
        top: 12px;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
        background: #57bf62;
        color: #fff;
        border: 2px solid rgba(255,255,255,.9);
        font-size: 18px;
        box-shadow: 0 4px 10px rgba(25,128,48,.18);
    }
    .mia-poster-card.is-active .mia-poster-check { display: flex; }
    .mia-poster-dots {
        display: flex;
        justify-content: center;
        gap: 7px;
        padding: 2px 0 0;
    }
    .mia-poster-dots span {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #d6d1c8;
    }
    .mia-poster-dots span.is-active { background: #c79a51; }
    .mia-bottom-actions {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 80;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        padding: 13px 10px calc(10px + env(safe-area-inset-bottom, 0px));
        background: rgba(255,255,255,.96);
        border-top: 1px solid #eef0f3;
        box-shadow: 0 -8px 24px rgba(15,23,42,.08);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
    }
    .mia-bottom-action {
        border: 0;
        background: transparent;
        color: #4b5563;
        font-size: 11px;
        font-weight: 700;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }
    .mia-bottom-action span:last-child { white-space: nowrap; }
    .mia-bottom-ico {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 21px;
        box-shadow: 0 6px 14px rgba(15,23,42,.10);
    }
    .mia-bottom-ico.c1 { background: linear-gradient(135deg, #ff9a3d, #ff7a20); }
    .mia-bottom-ico.c2 { background: linear-gradient(135deg, #6a5bdb, #4b36c9); }
    .mia-bottom-ico.c3 { background: linear-gradient(135deg, #58d092, #33b974); }
    .mia-bottom-ico.c4 { background: linear-gradient(135deg, #ff6a32, #f1491d); }
    @media (max-width: 380px) {
        .mia-poster-card { flex-basis: 238px; }
        .mia-poster-card img { height: 423px; }
        .mia-ticket-code { font-size: 22px; }
    }
    /* 域名选择弹窗 */
    .mia-domain-popup {
        padding: 18px 16px 14px;
    }
    .mia-domain-popup-title {
        font-size: 16px;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 14px;
        text-align: center;
    }
    .mia-domain-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .mia-domain-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 14px;
        border-radius: 12px;
        background: #f9fafb;
        border: 1.5px solid #eef0f3;
        margin-bottom: 10px;
        cursor: pointer;
        transition: border-color .15s, background .15s;
    }
    .mia-domain-item:last-child { margin-bottom: 0; }
    .mia-domain-item.is-active {
        border-color: #f1b95d;
        background: #fffbf2;
    }
    .mia-domain-radio {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 2px solid #d1d5db;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: border-color .15s;
    }
    .mia-domain-item.is-active .mia-domain-radio {
        border-color: #f1b95d;
    }
    .mia-domain-radio::after {
        content: '';
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: transparent;
        transition: background .15s;
    }
    .mia-domain-item.is-active .mia-domain-radio::after {
        background: #f1b95d;
    }
    .mia-domain-info { flex: 1; min-width: 0; }
    .mia-domain-label {
        font-size: 14px;
        font-weight: 700;
        color: #1f2937;
    }
    .mia-domain-url {
        margin-top: 2px;
        font-size: 12px;
        color: #8a94a6;
        word-break: break-all;
    }
    .mia-domain-save {
        display: block;
        width: 100%;
        height: 44px;
        margin-top: 16px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #f7d489, #f1b95d);
        color: #6f4512;
        font-size: 15px;
        font-weight: 800;
        cursor: pointer;
    }
    .mia-domain-save:active { opacity: .85; }
</style>

<main class="mia-page">
    <div class="mia-screen">
        <?php if ($_myInviteCode !== ''): ?>
        <?php if (!$_hasWechat): ?>
        <button type="button" class="mia-contact" onclick="openMiaWechatSetting()">
            <span class="mia-wx-ico"><i class="ri-wechat-fill"></i></span>
            <span class="mia-contact-main">填写微信号，让粉丝联系我</span>
            <span class="mia-contact-btn">立即填写</span>
        </button>
        <?php endif; ?>

        <section class="mia-code-wrap">
            <div class="mia-code-ticket">
                <div class="mia-ticket-label">我的邀请码</div>
                <div class="mia-code-row">
                    <div class="mia-ticket-code" id="invCode"><?= htmlspecialchars($_myInviteCode) ?></div>
                    <button type="button" class="mia-ticket-copy" onclick="doCopy('invCode')">复制</button>
                </div>
            </div>

        </section>

        <section class="mia-rule-card">
            <div class="mia-rule-title">邀请方式：</div>
            <div><b>【方式1】</b>复制邀请码给好友注册时填写；</div>
            <div><b>【方式2】</b>复制邀请链接给好友，好友通过链接访问后注册；</div>
            <div><b>【方式3】</b>选择海报生成专属二维码，好友扫码即可进入注册。</div>
            <div id="invLink" style="position:absolute;left:-9999px;top:-9999px;"><?= htmlspecialchars($_myInviteLink) ?></div>
        </section>

        <section class="mia-poster-area">
            <div class="mia-poster-list">
                <?php
                $_posterQrStyles = [
                    1 => '--qr-cx:50%;--qr-cy:54.5%;--qr-size:45%;',
                    2 => '--qr-cx:50%;--qr-cy:56.5%;--qr-size:45%;',
                    3 => '--qr-cx:50%;--qr-cy:52.5%;--qr-size:45%;',
                    4 => '--qr-cx:50%;--qr-cy:54%;--qr-size:58%;'
                ];
                ?>
                <?php for ($pi = 1; $pi <= 4; $pi++): ?>
                <div class="mia-poster-card<?= $pi === 1 ? ' is-active' : '' ?>" onclick="selectPoster(<?= $pi - 1 ?>)">
                    <img src="<?= DC_URL ?>admin/views/images/hb<?= $pi ?>.png" alt="海报<?= $pi ?>">
                    <div class="mia-poster-qr-mask" style="<?= $_posterQrStyles[$pi] ?>"><span>分享生成二维码</span></div>
                    <div class="mia-poster-check"><i class="ri-check-line"></i></div>
                </div>
                <?php endfor; ?>
            </div>
            <div class="mia-poster-dots">
                <span class="is-active"></span><span></span><span></span><span></span>
            </div>
        </section>
        <?php else: ?>
        <section class="mia-card">
            <div class="mia-empty">
                <?php include __DIR__ . '/_svg_empty.php'; ?>
                <div class="mia-empty-title">暂无邀请码</div>
                <div class="mia-empty-desc">请联系管理员开启或生成邀请码后再邀请好友。</div>
            </div>
        </section>
        <?php endif; ?>
    </div>
    <?php if ($_myInviteCode !== ''): ?>
    <div class="mia-bottom-actions">
        <button type="button" class="mia-bottom-action" onclick="openDomainPicker()"><span class="mia-bottom-ico c1"><i class="ri-global-line"></i></span><span>邀请域名</span></button>
        <button type="button" class="mia-bottom-action" onclick="genPoster(window._currentPosterIdx || 0)"><span class="mia-bottom-ico c2"><i class="ri-share-box-line"></i></span><span>分享海报</span></button>
        <button type="button" class="mia-bottom-action" onclick="doCopy('invLink')"><span class="mia-bottom-ico c3"><i class="ri-file-copy-line"></i></span><span>复制链接</span></button>
        <a href="./account.php?action=fans" class="mia-bottom-action"><span class="mia-bottom-ico c4"><i class="ri-team-line"></i></span><span>我的粉丝</span></a>
    </div>
    <?php endif; ?>
</main>

<div class="mia-app-modal-mask" id="miaWechatModal">
    <div class="mia-app-modal">
        <div class="mia-app-modal-header">
            <div class="mia-app-modal-icon"><i class="ri-wechat-line"></i></div>
            <div class="mia-app-modal-title">设置微信号</div>
        </div>
        <form id="miaWechatForm">
            <div class="mia-app-modal-body">
                <div class="mia-app-wechat-field">
                    <label class="mia-app-wechat-label" for="miaWechatInput"><i class="ri-wechat-line"></i> 微信号</label>
                    <input class="mia-app-wechat-input" id="miaWechatInput" type="text" maxlength="40" value="<?= htmlspecialchars($_myWechat, ENT_QUOTES) ?>" placeholder="请输入你的微信号">
                </div>
                <div class="mia-app-modal-notice">
                    <div class="mia-app-modal-notice-title"><i class="fa fa-info-circle"></i> 温馨提示</div>
                    <div class="mia-app-modal-notice-text">设置微信号有助您与上级或团队成员沟通交流，经验分享，促进订单成交量。您设置的微信号会展示给您的上级成员和团队成员。如因此涉及到个人隐私，可选择不设置或清空设置。</div>
                </div>
            </div>
            <div class="mia-app-modal-foot">
                <button type="button" class="mia-app-modal-btn mia-app-modal-cancel">稍后再说</button>
                <button type="submit" class="mia-app-modal-btn mia-app-modal-confirm" id="miaWechatSave">保存设置</button>
            </div>
            <div class="mia-app-modal-foot-note">可在个人信息处修改微信号</div>
        </form>
    </div>
</div>

<script>
    $('#menu-invite').addClass('menu-current');

    var _invCode = <?= json_encode($_myInviteCode) ?>;
    var _invOpts = <?= json_encode($_inviteDomainOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    var miaWechatValue = <?= json_encode($_myWechat, JSON_UNESCAPED_UNICODE) ?>;
    var miaWechatToken = '<?= LoginAuth::genToken() ?>';

    var _currentDomainIdx = 0;

    function showMiaAppModal(selector) {
        $(selector).addClass('is-show');
    }

    function hideMiaAppModal(selector) {
        $(selector).removeClass('is-show');
    }

    function openMiaWechatSetting() {
        $('#miaWechatInput').val(miaWechatValue || '');
        $('#miaWechatSave').prop('disabled', false).text('保存设置');
        showMiaAppModal('#miaWechatModal');
        setTimeout(function(){ $('#miaWechatInput').trigger('focus'); }, 180);
    }

    $('#miaWechatForm').on('submit', function(e) {
        e.preventDefault();
        var val = $.trim($('#miaWechatInput').val() || '');
        val = val.replace(/\s+/g, '');
        if (!val) {
            return layui.layer.msg('请输入微信号，暂不设置可关闭弹窗', {icon: 0});
        }
        if (!/^[A-Za-z0-9_\-]{3,40}$/.test(val)) {
            return layui.layer.msg('微信号仅支持3-40位字母、数字、下划线或中划线', {icon: 0});
        }
        var $btn = $('#miaWechatSave').prop('disabled', true).text('保存中...');
        $.ajax({
            url: './account.php?action=fans_save_wechat',
            type: 'POST',
            dataType: 'json',
            data: { wechat: val, token: miaWechatToken },
            success: function(res) {
                if (res && res.code === 0) {
                    miaWechatValue = val;
                    layui.layer.msg(res.data || '微信号已保存', {icon: 1});
                    hideMiaAppModal('#miaWechatModal');
                    $('.mia-contact').slideUp(180, function(){ $(this).remove(); });
                } else {
                    layui.layer.msg((res && res.msg) || '保存失败', {icon: 2});
                }
            },
            error: function(xhr) {
                var res = xhr.responseJSON || {};
                layui.layer.msg(res.msg || '保存失败，请稍后重试', {icon: 2});
            },
            complete: function() {
                $btn.prop('disabled', false).text('保存设置');
            }
        });
        return false;
    });

    $('.mia-app-modal-cancel').on('click', function() {
        var $mask = $(this).closest('.mia-app-modal-mask');
        hideMiaAppModal('#' + $mask.attr('id'));
    });

    $('.mia-app-modal-mask').on('click', function(e) {
        if (e.target !== this) return;
        hideMiaAppModal('#' + this.id);
    });

    function switchInvDomain(idx) {
        var linkEl = document.getElementById('invLink');
        if (!linkEl) return;
        var opt = _invOpts[idx];
        if (opt && _invCode) {
            _currentDomainIdx = idx;
            linkEl.textContent = opt.base + opt.suffix + _invCode;
        }
    }

    function openDomainPicker() {
        if (_invOpts.length <= 1) {
            layui.layer.msg('当前仅有一个邀请域名');
            return;
        }
        var html = '<div class="mia-domain-popup">';
        html += '<div class="mia-domain-popup-title">选择邀请域名</div>';
        html += '<div style="margin:-8px 0 14px;padding:10px 12px;border-radius:10px;background:#f8f5ee;color:#7a6332;font-size:12px;line-height:1.7;">选定的域名将用于：<b>生成海报二维码</b>、<b>复制邀请链接</b>。好友通过该链接访问注册后自动绑定为您的粉丝。</div>';
        html += '<ul class="mia-domain-list">';
        for (var i = 0; i < _invOpts.length; i++) {
            var active = (i === _currentDomainIdx) ? ' is-active' : '';
            var domain = _invOpts[i].base.replace(/^\/\/|^https?:\/\//i, '');
            html += '<li class="mia-domain-item' + active + '" data-idx="' + i + '" onclick="pickDomain(this,' + i + ')">';
            html += '<span class="mia-domain-radio"></span>';
            html += '<span class="mia-domain-info"><span class="mia-domain-label">' + _invOpts[i].label + '</span><span class="mia-domain-url">' + domain + '</span></span>';
            html += '</li>';
        }
        html += '</ul>';
        html += '<button type="button" class="mia-domain-save" onclick="saveDomainPick()">确认使用</button>';
        html += '</div>';
        layui.layer.open({
            type: 1,
            title: false,
            area: ['88%', 'auto'],
            content: html,
            shadeClose: true,
            closeBtn: 0,
            scrollbar: false,
            anim: 2,
            success: function(layero){
                $(layero).css({'border-radius':'16px','overflow':'hidden'});
            }
        });
    }

    var _pendingDomainIdx = _currentDomainIdx;
    function pickDomain(el, idx) {
        _pendingDomainIdx = idx;
        $(el).addClass('is-active').siblings().removeClass('is-active');
    }
    function saveDomainPick() {
        switchInvDomain(_pendingDomainIdx);
        layui.layer.closeAll();
        layui.layer.msg('已切换邀请域名');
    }

    function copyText(text) {
        if (!text) { layui.layer.msg('暂无可复制内容'); return; }
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(function(){
                layui.layer.msg('已复制到剪贴板');
            }, function(){
                fallbackCopy(text);
            });
        } else {
            fallbackCopy(text);
        }
    }

    function fallbackCopy(text) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        ta.style.top = '-9999px';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        try { document.execCommand('copy'); layui.layer.msg('已复制到剪贴板'); }
        catch (e) { layui.layer.msg('复制失败，请长按内容复制'); }
        document.body.removeChild(ta);
    }

    function doCopy(id) {
        var el = document.getElementById(id);
        copyText(el ? (el.innerText || el.textContent || '') : '');
    }
</script>
<?php if ($_myInviteCode !== ''): ?>
<script src="<?= DC_URL ?>content/static/js/qrcode.min.js"></script>
<script>
(function(){
    var _posterConfigs = [
        { src: '<?= DC_URL ?>admin/views/images/hb1.png', cx: 0.50, cy: 0.545, size: 0.38 },
        { src: '<?= DC_URL ?>admin/views/images/hb2.png', cx: 0.50, cy: 0.565, size: 0.45 },
        { src: '<?= DC_URL ?>admin/views/images/hb3.png', cx: 0.50, cy: 0.525, size: 0.39 },
        { src: '<?= DC_URL ?>admin/views/images/hb4.png', cx: 0.50, cy: 0.540, size: 0.58 }
    ];

    function markPosterCard(idx, dataUrl) {
        var cards = document.querySelectorAll('.mia-poster-card');
        for (var i = 0; i < cards.length; i++) {
            cards[i].classList.toggle('is-active', i === idx);
            if (i === idx && dataUrl) {
                cards[i].classList.add('is-generated');
                var img = cards[i].querySelector('img');
                if (img) img.src = dataUrl;
            }
        }
        var dots = document.querySelectorAll('.mia-poster-dots span');
        for (var j = 0; j < dots.length; j++) {
            dots[j].classList.toggle('is-active', j === idx);
        }
    }

    window.selectPoster = function(idx) {
        window._currentPosterIdx = idx;
        markPosterCard(idx);
        var cards = document.querySelectorAll('.mia-poster-card');
        if (cards[idx]) {
            cards[idx].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        }
    };

    window.genPoster = function(idx) {
        if (typeof QRCode === 'undefined') { layui.layer.msg('二维码组件未加载'); return; }
        if (!_invCode) { layui.layer.msg('暂无邀请码'); return; }
        var cfg = _posterConfigs[idx] || _posterConfigs[0];
        var el = document.getElementById('invLink');
        var inviteLink = el ? (el.innerText || el.textContent || '') : '';
        if (!inviteLink) { layui.layer.msg('邀请链接为空'); return; }

        var loadIdx = layui.layer.load(2);
        var tmpDiv = document.createElement('div');
        tmpDiv.style.cssText = 'position:fixed;left:-9999px;top:-9999px;width:512px;height:512px;';
        document.body.appendChild(tmpDiv);

        new QRCode(tmpDiv, {
            text: inviteLink,
            width: 512,
            height: 512,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });

        setTimeout(function(){
            var qrCanvas = tmpDiv.querySelector('canvas');
            if (!qrCanvas) {
                document.body.removeChild(tmpDiv);
                layui.layer.close(loadIdx);
                layui.layer.msg('二维码生成失败');
                return;
            }

            var posterImg = new Image();
            posterImg.onload = function(){
                var canvas = document.createElement('canvas');
                var ctx = canvas.getContext('2d');
                canvas.width = posterImg.naturalWidth;
                canvas.height = posterImg.naturalHeight;
                ctx.drawImage(posterImg, 0, 0);

                var qrW = Math.round(canvas.width * cfg.size);
                var qrX = Math.round(canvas.width * cfg.cx - qrW / 2);
                var qrY = Math.round(canvas.height * cfg.cy - qrW / 2);
                var pad = Math.round(qrW * 0.06);
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(qrX - pad, qrY - pad, qrW + pad * 2, qrW + pad * 2);
                ctx.drawImage(qrCanvas, qrX, qrY, qrW, qrW);

                document.body.removeChild(tmpDiv);
                layui.layer.close(loadIdx);

                var dataUrl = canvas.toDataURL('image/png');
                window._currentPosterIdx = idx;
                window._currentPosterDataUrl = dataUrl;
                markPosterCard(idx, dataUrl);
                var prevIdx = (idx - 1 + _posterConfigs.length) % _posterConfigs.length;
                var nextIdx = (idx + 1) % _posterConfigs.length;
                var html = '<div class="mia-poster-modal">'
                    + '<div class="mia-poster-modal-body">'
                    + '<div class="mia-poster-nav mia-poster-prev" onclick="switchPoster(' + prevIdx + ')"><i class="ri-arrow-left-s-line"></i></div>'
                    + '<div class="mia-poster-nav mia-poster-next" onclick="switchPoster(' + nextIdx + ')"><i class="ri-arrow-right-s-line"></i></div>'
                    + '<img id="_posterPreviewImg" src="' + dataUrl + '" class="mia-poster-modal-img" />'
                    + '</div>'
                    + '<div class="mia-poster-modal-foot">'
                    + '<div class="mia-poster-modal-actions">'
                    + '<a id="_posterDL" download="invite_poster.png" class="mia-poster-modal-btn is-primary"><i class="ri-download-line"></i> 保存海报</a>'
                    + '<button type="button" class="mia-poster-modal-btn is-plain" onclick="layui.layer.closeAll()"><i class="ri-close-line"></i> 关闭</button>'
                    + '</div>'
                    + '<div class="mia-poster-modal-hint">长按图片或点击“保存海报”下载到本地</div>'
                    + '</div>'
                    + '</div>';
                layui.layer.open({
                    type: 1,
                    title: false,
                    area: ['94%', '88%'],
                    content: html,
                    shadeClose: true,
                    closeBtn: 0,
                    scrollbar: false,
                    success: function(layero){
                        $(layero).find('#_posterDL').attr('href', dataUrl);
                        $(layero).find('.layui-layer-content').css({'overflow':'hidden'});
                        $(layero).css({'border-radius':'18px', 'overflow':'hidden'});
                    }
                });
            };
            posterImg.onerror = function(){
                document.body.removeChild(tmpDiv);
                layui.layer.close(loadIdx);
                layui.layer.msg('海报图片加载失败');
            };
            posterImg.src = cfg.src;
        }, 200);
    };

    window.switchPoster = function(newIdx) {
        layui.layer.closeAll();
        genPoster(newIdx);
    };
})();
</script>
<?php endif; ?>
