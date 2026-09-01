<?php defined('DC_ROOT') || exit('access denied!'); ?>

<style>
.upgrade-disabled-wrapper {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: -apple-system, BlinkMacSystemFont, "PingFang SC", "Segoe UI", Roboto, sans-serif;
}
.upgrade-disabled-card {
    background: linear-gradient(0deg, #fff, #f3f5f8);
    border: 2px solid #fff;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    padding: 50px 60px;
    text-align: center;
    max-width: 500px;
    width: 100%;
}
.upgrade-disabled-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #f0f9ff;
    color: #3b82f6;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
    font-size: 40px;
}
.upgrade-disabled-title {
    font-size: 22px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 12px;
}
.upgrade-disabled-desc {
    font-size: 14px;
    color: #888;
    line-height: 1.6;
    margin-bottom: 20px;
}
.upgrade-disabled-version {
    font-size: 28px;
    font-weight: 700;
    color: #4C7D71;
    margin-bottom: 6px;
}
.upgrade-disabled-version-label {
    font-size: 13px;
    color: #aaa;
}

#accordion > .active .menu-link,
#accordion > .active .menu-link .fa {
    color: #4C7D71 !important;
    background: #EDF2F1 !important;
}
</style>

<div class="upgrade-disabled-wrapper">
    <div class="upgrade-disabled-card">
        <div class="upgrade-disabled-icon">
            <i class="ri-checkbox-circle-line"></i>
        </div>
        <div class="upgrade-disabled-title">系统已是最新版本</div>
        <div class="upgrade-disabled-desc">在线更新功能已关闭</div>
        <div class="upgrade-disabled-version">v<?= htmlspecialchars($currentVersion) ?></div>
        <div class="upgrade-disabled-version-label">当前运行版本</div>
    </div>
</div>

<script>
    $("#menu-upgrade").addClass('active');
</script>
