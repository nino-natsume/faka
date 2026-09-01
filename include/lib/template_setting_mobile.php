<?php defined('DC_ROOT') || exit('access denied!');
if (!empty($GLOBALS['__dc_template_setting_mobile_assets'])) {
    return;
}
$GLOBALS['__dc_template_setting_mobile_assets'] = true;
?>
<style id="dc-template-setting-mobile-style">
    body.dc-template-setting-page,
    body.dc-template-setting-page * { box-sizing: border-box; }
    body.dc-template-setting-page {
        min-width: 0 !important;
        max-width: 100%;
        overflow-x: hidden;
        background: #f6f8fb;
    }
    body.dc-template-setting-page form.layui-form,
    body.dc-template-setting-page #form,
    body.dc-template-setting-page #open-box,
    body.dc-template-setting-page .layui-tab,
    body.dc-template-setting-page .layui-tab-content,
    body.dc-template-setting-page .layui-tab-item,
    body.dc-template-setting-page .tab-inner {
        max-width: 100%;
    }
    body.dc-template-setting-page #open-box {
        padding: 16px 20px 92px !important;
    }
    body.dc-template-setting-page .layui-tab-title {
        height: auto;
        min-height: 40px;
        white-space: normal;
    }
    body.dc-template-setting-page .layui-tab-content {
        padding: 14px 0 0;
    }
    body.dc-template-setting-page .layui-input,
    body.dc-template-setting-page .layui-textarea,
    body.dc-template-setting-page .layui-select,
    body.dc-template-setting-page select,
    body.dc-template-setting-page input,
    body.dc-template-setting-page textarea {
        max-width: 100%;
    }
    body.dc-template-setting-page img,
    body.dc-template-setting-page video,
    body.dc-template-setting-page canvas {
        max-width: 100%;
    }
    body.dc-template-setting-page .layui-table-view,
    body.dc-template-setting-page .layui-table-box,
    body.dc-template-setting-page .layui-table-main {
        max-width: 100%;
        overflow-x: auto;
    }
    body.dc-template-setting-page #form-btn {
        min-height: 56px;
        height: auto !important;
        line-height: normal !important;
        padding: 9px 14px calc(9px + env(safe-area-inset-bottom, 0px)) !important;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: rgba(255, 255, 255, .94) !important;
        border-top: 1px solid rgba(15, 23, 42, .08);
        box-shadow: 0 -10px 28px rgba(15, 23, 42, .08);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        z-index: 999 !important;
    }
    body.dc-template-setting-page #form-btn .layui-btn {
        min-width: 128px;
    }

    @media (max-width: 768px) {
        html,
        body.dc-template-setting-page {
            width: 100% !important;
            overflow-x: hidden !important;
        }
        body.dc-template-setting-page {
            font-size: 14px;
            background: #f7f9fc;
            padding: 0 !important;
        }
        body.dc-template-setting-page #open-box {
            padding: 12px 12px 96px !important;
        }
        body.dc-template-setting-page .layui-tab {
            margin: 0;
            position: relative;
        }
        /* 手机端使用横向滑动 Tab，禁用 layui 默认“更多/展开”按钮，避免按钮无效且跟随滑动位移 */
        body.dc-template-setting-page .layui-tab .layui-tab-bar,
        body.dc-template-setting-page .layui-tab-title .layui-tab-bar {
            display: none !important;
            pointer-events: none !important;
        }
        body.dc-template-setting-page .layui-tab[overflow] > .layui-tab-title,
        body.dc-template-setting-page .layui-tab-title.layui-tab-more {
            overflow-x: auto !important;
            overflow-y: hidden !important;
            white-space: nowrap !important;
            flex-wrap: nowrap !important;
            padding-right: 2px !important;
        }
        body.dc-template-setting-page .layui-tab-title {
            display: flex !important;
            align-items: center;
            gap: 8px;
            height: auto !important;
            min-height: 0 !important;
            padding: 4px 2px 10px;
            border-bottom: 0 !important;
            white-space: nowrap !important;
            overflow-x: auto !important;
            overflow-y: hidden !important;
            flex-wrap: nowrap !important;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        body.dc-template-setting-page .layui-tab-title::-webkit-scrollbar { display: none; }
        body.dc-template-setting-page .layui-tab-title li {
            flex: 0 0 auto;
            height: 36px !important;
            line-height: 36px !important;
            min-width: auto;
            padding: 0 13px !important;
            border-radius: 999px !important;
            border: 1px solid #e8edf5 !important;
            background: #fff;
            color: #5f6b7a;
            font-size: 13px !important;
            font-weight: 600;
        }
        body.dc-template-setting-page .layui-tab-title li.layui-this {
            color: #fff !important;
            background: linear-gradient(135deg, #1677ff, #4f8dff) !important;
            border-color: transparent !important;
            box-shadow: 0 8px 18px rgba(22, 119, 255, .22);
        }
        body.dc-template-setting-page .layui-tab-title .layui-this:after {
            display: none !important;
        }
        body.dc-template-setting-page .layui-tab-content {
            padding: 10px 0 0 !important;
        }
        body.dc-template-setting-page .layui-form-item {
            margin-bottom: 16px !important;
        }
        body.dc-template-setting-page .layui-form-label {
            float: none !important;
            display: block !important;
            width: auto !important;
            max-width: 100%;
            padding: 0 0 8px !important;
            line-height: 1.55 !important;
            text-align: left !important;
            color: #1f2937;
            font-weight: 700;
        }
        body.dc-template-setting-page .section-title {
            margin-top: 20px !important;
            padding-top: 16px !important;
        }
        body.dc-template-setting-page .section-title:first-child {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }
        body.dc-template-setting-page .section-title .layui-form-label {
            padding-bottom: 0 !important;
            font-size: 15px !important;
        }
        body.dc-template-setting-page .layui-input-block {
            margin-left: 0 !important;
            min-height: 0;
            width: 100% !important;
        }
        body.dc-template-setting-page .layui-input-inline {
            float: none !important;
            display: block !important;
            width: 100% !important;
            margin: 0 0 10px 0 !important;
        }
        body.dc-template-setting-page .layui-form-mid,
        body.dc-template-setting-page .layui-word-aux,
        body.dc-template-setting-page .layui-text-em {
            float: none !important;
            display: block;
            width: 100%;
            padding: 7px 0 0 !important;
            line-height: 1.65 !important;
            font-size: 12px;
        }
        body.dc-template-setting-page .layui-input,
        body.dc-template-setting-page .layui-select,
        body.dc-template-setting-page .layui-textarea,
        body.dc-template-setting-page select {
            width: 100% !important;
            max-width: none !important;
        }
        body.dc-template-setting-page .layui-form-select,
        body.dc-template-setting-page .layui-select-title {
            width: 100% !important;
            max-width: none !important;
        }
        body.dc-template-setting-page textarea.layui-textarea {
            min-height: 110px;
        }
        body.dc-template-setting-page .color-input-group {
            width: 100% !important;
            max-width: none !important;
        }
        body.dc-template-setting-page .color-input-group input[type="text"] {
            min-width: 0;
        }
        body.dc-template-setting-page .color-input-group input[type="color"] {
            flex: 0 0 42px;
            width: 42px !important;
            height: 38px !important;
        }
        body.dc-template-setting-page .theme-presets,
        body.dc-template-setting-page .card-tone-presets {
            gap: 8px !important;
            width: 100%;
        }
        body.dc-template-setting-page .theme-preset,
        body.dc-template-setting-page .card-tone-btn {
            width: calc(50% - 4px) !important;
            max-width: 128px;
            min-width: 96px;
        }
        body.dc-template-setting-page .switch-inline-item {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            margin: 0 0 10px 0 !important;
            padding: 10px 12px;
            border-radius: 10px;
            background: #fff;
            border: 1px solid #eef2f7;
        }
        body.dc-template-setting-page .switch-inline-item .switch-label {
            margin-right: 12px;
            color: #374151;
            font-weight: 600;
        }
        body.dc-template-setting-page .faq-config-list,
        body.dc-template-setting-page .ci-config-list,
        body.dc-template-setting-page .bn-config-list {
            padding: 10px !important;
            border-radius: 12px !important;
        }
        body.dc-template-setting-page .faq-config-item,
        body.dc-template-setting-page .bn-config-item {
            padding: 12px !important;
            border-radius: 12px !important;
        }
        body.dc-template-setting-page .faq-config-item .faq-num,
        body.dc-template-setting-page .bn-num {
            position: static !important;
            transform: none !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            margin: 0 0 10px 0;
        }
        body.dc-template-setting-page .faq-config-item .faq-inputs,
        body.dc-template-setting-page .bn-inputs {
            display: flex !important;
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 8px !important;
            margin-left: 0 !important;
            overflow: visible !important;
            width: 100%;
        }
        body.dc-template-setting-page .faq-config-item .faq-q,
        body.dc-template-setting-page .faq-config-item .faq-a,
        body.dc-template-setting-page .bn-img-cell,
        body.dc-template-setting-page .bn-url-wrap,
        body.dc-template-setting-page .bn-img-input,
        body.dc-template-setting-page .bn-url {
            width: 100% !important;
            min-width: 0 !important;
            max-width: none !important;
        }
        body.dc-template-setting-page .bn-img-cell,
        body.dc-template-setting-page .bn-url-wrap {
            flex-direction: column !important;
            align-items: stretch !important;
        }
        body.dc-template-setting-page .bn-img-preview {
            width: 100% !important;
            height: 110px !important;
        }
        body.dc-template-setting-page .bn-upload-btn,
        body.dc-template-setting-page .bn-nav-picker-btn,
        body.dc-template-setting-page .ci-upload-btn,
        body.dc-template-setting-page .ci-nav-picker-btn {
            width: 100% !important;
            text-align: center;
        }
        body.dc-template-setting-page .ci-config-item {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 10px !important;
        }
        body.dc-template-setting-page .ci-config-item .ci-num {
            margin-top: 0 !important;
        }
        body.dc-template-setting-page .ci-config-item .ci-inputs,
        body.dc-template-setting-page .ci-config-item .ci-img-wrap,
        body.dc-template-setting-page .ci-config-item .ci-url-wrap,
        body.dc-template-setting-page .ci-config-item .ci-name,
        body.dc-template-setting-page .ci-config-item .ci-url,
        body.dc-template-setting-page .ci-config-item .ci-img {
            width: 100% !important;
            min-width: 0 !important;
            max-width: none !important;
        }
        body.dc-template-setting-page .ci-config-item .ci-inputs,
        body.dc-template-setting-page .ci-config-item .ci-img-wrap,
        body.dc-template-setting-page .ci-config-item .ci-url-wrap {
            flex-direction: column !important;
            align-items: stretch !important;
        }
        body.dc-template-setting-page .ci-ri-wrap,
        body.dc-template-setting-page .ci-ri-btn {
            width: 100% !important;
        }
        body.dc-template-setting-page .ci-ri-btn {
            height: 40px;
        }
        body.dc-template-setting-page .setting-status-box {
            border-radius: 12px !important;
            padding: 12px !important;
        }
        body.dc-template-setting-page .nav-quick-panel {
            padding: 12px !important;
        }
        body.dc-template-setting-page .nav-quick-tabs {
            flex-wrap: nowrap !important;
            overflow-x: auto;
            padding-bottom: 10px;
        }
        body.dc-template-setting-page .nav-quick-tab {
            flex: 0 0 auto;
        }
        body.dc-template-setting-page #form-btn {
            justify-content: stretch;
        }
        body.dc-template-setting-page #form-btn .layui-btn {
            width: 100%;
            min-width: 0;
            height: 42px;
            line-height: 42px;
            border-radius: 10px;
        }
    }
</style>
<script id="dc-template-setting-mobile-script">
(function(){
    function ready(fn){
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
        else fn();
    }
    function isMobile(){
        return window.matchMedia ? window.matchMedia('(max-width: 768px)').matches : window.innerWidth <= 768;
    }
    var tabNormalizeTimer = null;
    function normalizeMobileTabs(){
        if (!document.body || !document.body.classList.contains('dc-template-setting-page') || !isMobile()) return;
        var tabs = document.querySelectorAll('.layui-tab');
        tabs.forEach(function(tab){ if (tab.hasAttribute('overflow')) tab.removeAttribute('overflow'); });
        var titles = document.querySelectorAll('.layui-tab-title');
        titles.forEach(function(title){
            if (title.classList.contains('layui-tab-more')) title.classList.remove('layui-tab-more');
            if (title.style.left && title.style.left !== '0px') title.style.left = '0px';
        });
        var bars = document.querySelectorAll('.layui-tab-bar');
        bars.forEach(function(bar){
            if (bar.parentNode) bar.parentNode.removeChild(bar);
        });
    }
    function scheduleNormalizeMobileTabs(){
        if (tabNormalizeTimer) clearTimeout(tabNormalizeTimer);
        tabNormalizeTimer = setTimeout(normalizeMobileTabs, 30);
    }
    ready(function(){
        document.body.classList.add('dc-template-setting-page');
        var forms = document.querySelectorAll('form.layui-form, #form');
        forms.forEach(function(form){ form.classList.add('dc-template-setting-form'); });
        normalizeMobileTabs();
        [80, 240, 600, 1200].forEach(function(delay){ setTimeout(normalizeMobileTabs, delay); });
        if (window.MutationObserver) {
            var observer = new MutationObserver(scheduleNormalizeMobileTabs);
            observer.observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['overflow', 'class', 'style'] });
        }
        window.addEventListener('resize', scheduleNormalizeMobileTabs);
        window.addEventListener('orientationchange', scheduleNormalizeMobileTabs);
    });
})();
</script>


