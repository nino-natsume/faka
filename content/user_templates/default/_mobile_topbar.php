<?php
/**
 * 手机端 App 风格顶部导航栏
 * 引用方式：在 _adaptive_header.php 中 include 本文件
 * 需要变量：$uc_page_title (string, 页面标题)
 */
defined('DC_ROOT') || exit('access denied!');
if (!isset($uc_page_title)) return;
?>
<div class="m-topbar" id="mTopbar">
    <a class="m-topbar-back" href="javascript:history.back()" aria-label="返回">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    </a>
    <div class="m-topbar-title"><?= htmlspecialchars($uc_page_title) ?></div>
    <div class="m-topbar-placeholder"></div>
</div>
<style>
.m-topbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 200;
    display: flex;
    align-items: center;
    height: calc(50px + env(safe-area-inset-top, 0px));
    padding-top: env(safe-area-inset-top, 0px);
    padding-left: 6px;
    padding-right: 6px;
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    border-bottom: 0.5px solid rgba(0, 0, 0, 0.06);
    transition: box-shadow 0.2s ease, background 0.2s ease;
}
.m-topbar.is-scrolled {
    background: rgba(255, 255, 255, 0.98);
    box-shadow: 0 1px 12px rgba(0, 0, 0, 0.06);
}
.m-topbar-back {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    color: var(--text-main, #1f2937);
    text-decoration: none;
    flex: 0 0 auto;
    transition: background 0.15s ease;
    -webkit-tap-highlight-color: transparent;
}
.m-topbar-back:active {
    background: rgba(0, 0, 0, 0.05);
}
.m-topbar-title {
    flex: 1;
    text-align: center;
    font-size: 17px;
    font-weight: 600;
    color: var(--text-main, #1f2937);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    letter-spacing: 0.2px;
}
.m-topbar-placeholder {
    width: 40px;
    flex: 0 0 auto;
}
</style>
<script>
!function(){
    var bar = document.getElementById('mTopbar');
    if (!bar) return;
    var cls = 'is-scrolled', last = false;
    function check() {
        var scrolled = window.scrollY > 8;
        if (scrolled !== last) {
            last = scrolled;
            bar.classList.toggle(cls, scrolled);
        }
    }
    window.addEventListener('scroll', check, { passive: true });
    check();
}();
</script>
