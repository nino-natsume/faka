<?php
defined('DC_ROOT') || exit('access denied!');
?>
<style>
.update-badge {
    display: inline-block;
    min-width: 18px;
    height: 18px;
    line-height: 18px;
    padding: 0 5px;
    background: #ff5722;
    color: #fff;
    font-size: 12px;
    border-radius: 10px;
    text-align: center;
    margin-left: 5px;
}

/* 模板详情弹窗样式 */
.tpl-detail-modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 19891015; display: flex; align-items: center; justify-content: center; padding: 20px; }
.tpl-detail-box { background: #fff; border-radius: 12px; max-width: 500px; width: 100%; max-height: 80vh; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
.tpl-detail-header { position: relative; aspect-ratio: 1 / 1; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; }
.tpl-detail-header img { width: 100%; height: 100%; object-fit: cover; }
.tpl-detail-header .default-icon { font-size: 80px; color: rgba(255,255,255,0.8); }
.tpl-detail-close { position: absolute; top: 10px; right: 10px; width: 32px; height: 32px; background: rgba(0,0,0,0.3); border: none; border-radius: 50%; color: #fff; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
.tpl-detail-close:hover { background: rgba(0,0,0,0.5); }
.tpl-detail-body { padding: 20px; max-height: 50vh; overflow-y: auto; }
.tpl-detail-title { font-size: 20px; font-weight: 600; margin-bottom: 12px; color: #333; }
.tpl-detail-meta { display: flex; gap: 15px; margin-bottom: 15px; font-size: 13px; color: #999; flex-wrap: wrap; }
.tpl-detail-meta i { margin-right: 4px; }
.tpl-detail-desc { color: #666; line-height: 1.8; margin-bottom: 20px; font-size: 14px; }
.tpl-detail-status { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
.tpl-detail-status-item { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 20px; font-size: 13px; cursor: pointer; transition: all 0.2s; }
.tpl-detail-status-item.on { background: #dcfce7; color: #16a34a; }
.tpl-detail-status-item.off { background: #f3f4f6; color: #6b7280; }
.tpl-detail-status-item:hover { opacity: 0.8; }
.tpl-detail-actions { display: flex; gap: 10px; flex-wrap: wrap; }
.tpl-detail-actions .layui-btn { flex: 1; height: 42px; line-height: 32px; font-size: 14px; min-width: 80px; }

/* 深色模式适配 */
html[data-theme="dark"] .tpl-detail-box { background: #1e1e1e; }
html[data-theme="dark"] .tpl-detail-title { color: #e0e0e0; }
html[data-theme="dark"] .tpl-detail-meta { color: #888; }
html[data-theme="dark"] .tpl-detail-desc { color: #b0b0b0; }
html[data-theme="dark"] .tpl-detail-status-item.on { background: rgba(22, 163, 74, 0.2); }
html[data-theme="dark"] .tpl-detail-status-item.off { background: rgba(107, 114, 128, 0.2); }

/* 搜索框样式 */
.tpl-search-box { transition: all 0.2s; }
.tpl-search-box:focus-within { box-shadow: 0 0 0 2px rgba(22, 186, 170, 0.2) !important; }
.tpl-search-box input::placeholder { color: #bbb; }
html[data-theme="dark"] .tpl-search-box { background: #2a2a2a !important; }
html[data-theme="dark"] .tpl-search-box input { color: #e0e0e0; }
html[data-theme="dark"] .tpl-search-box input::placeholder { color: #666; }
html[data-theme="dark"] .tpl-search-box i { color: #666 !important; }

/* 视图切换按钮 */
.view-switch {
    display: flex;
    background: #f5f5f5;
    border-radius: 6px;
    margin-bottom: 5px;
    padding: 3px;
    flex-shrink: 0;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}
.view-switch .view-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 6px 12px;
    border: none;
    background: transparent;
    cursor: pointer;
    font-size: 13px;
    color: #999;
    transition: all 0.2s;
    border-radius: 4px;
}
.view-switch .view-btn:hover {
    color: #666;
}
.view-switch .view-btn.active {
    background: #fff;
    color: #16baaa;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

/* 卡片视图样式 */
.tpl-cards {
    display: none;
}
.tpl-cards.active {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 20px;
}
@media (max-width: 1400px) {
    .tpl-cards.active {
        grid-template-columns: repeat(4, 1fr);
    }
}
@media (max-width: 1100px) {
    .tpl-cards.active {
        grid-template-columns: repeat(3, 1fr);
    }
}
@media (max-width: 768px) {
    .tpl-cards.active {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    .view-switch {
        margin-left: auto;
        margin-top: 10px;
    }
    .view-switch .view-btn {
        padding: 5px 10px;
        font-size: 12px;
    }
    .tpl-card-cover {
        aspect-ratio: 1 / 1;
    }
    .tpl-card-body {
        padding: 10px 12px;
    }
    .tpl-card-title {
        font-size: 14px;
    }
    .tpl-card-desc {
        font-size: 12px;
        height: 36px;
    }
    .tpl-card-actions {
        padding: 0 12px 12px;
        flex-wrap: wrap;
    }
    .tpl-card-actions .layui-btn {
        flex: 1 1 auto;
        min-width: 60px;
        height: 32px;
        line-height: 32px;
        padding: 0 10px;
        font-size: 13px;
    }
}
.tpl-card {
    background: #ffffff85;
    border: 1px solid #eef1f4;
    border-radius: 6px;
    overflow: hidden;
    transition: all 0.3s;
}
.tpl-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
}
.tpl-card-cover {
    width: 100%;
    aspect-ratio: 1 / 1;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    cursor: pointer;
    position: relative;
}
.tpl-card-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.tpl-card-cover .default-icon {
    font-size: 64px;
    color: rgba(255,255,255,0.8);
}
.tpl-card-body {
    padding: 5px 10px 0px 10px;
}
.tpl-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}
.tpl-card-title {
    font-size: 15px;
    font-weight: 600;
    color: #333;
}
.tpl-card-desc {
    font-size: 13px;
    color: #666;
    line-height: 1.5;
    height: 40px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
.tpl-card-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 12px;
    color: #999;
    padding: 3px 10px 5px;
}
.tpl-card-actions {
    display: flex;
    gap: 8px;
    padding: 0 15px 15px;
}
.tpl-card-actions .layui-btn {
    flex: 1;
    margin: 0;
}
.tpl-card .update-dot {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #ff5722;
    color: #fff;
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 10px;
}
.tpl-card-switches {
    position: absolute;
    top: 8px;
    left: 8px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    z-index: 2;
}
.tpl-card-switch {
    padding: 0px 10px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 700;
    transition: all 0.2s;
    user-select: none;
}
.tpl-card-switch i {
    font-size: 28px;
}
.tpl-card-switch:hover {
    transform: scale(1.05);
}
.tpl-card-switch.on {
    background: rgba(34, 197, 94, 0.9);
    color: #fff;
}
.tpl-card-switch.off {
    background: rgba(0, 0, 0, 0.5);
    color: rgba(255, 255, 255, 0.9);
}
.tpl-card.disabled {
    opacity: 0.7;
}
.tpl-card.disabled .tpl-card-cover {
    filter: grayscale(50%);
}
/* 到期时间标签 */
.tpl-expire-tag {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 4px;
}
.tpl-expire-tag.permanent { background: rgba(22, 163, 74, 0.1); color: #16a34a; }
.tpl-expire-tag.monthly { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
.tpl-expire-tag.local { background: rgba(153, 153, 153, 0.1); color: #999; }
.tpl-expire-tag.expired { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
.tpl-expire-tag.blocked { background: rgba(51, 51, 51, 0.1); color: #333; }
.tpl-expire-tag.unauthorized { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
.tpl-expire-tag.trial { background: rgba(147, 51, 234, 0.1); color: #9333ea; }
html[data-theme="dark"] .tpl-expire-tag.permanent { background: rgba(22, 163, 74, 0.2); }
html[data-theme="dark"] .tpl-expire-tag.monthly { background: rgba(245, 158, 11, 0.2); }
html[data-theme="dark"] .tpl-expire-tag.local { background: rgba(153, 153, 153, 0.2); }
html[data-theme="dark"] .tpl-expire-tag.expired { background: rgba(239, 68, 68, 0.2); }
html[data-theme="dark"] .tpl-expire-tag.blocked { background: rgba(51, 51, 51, 0.3); color: #999; }
html[data-theme="dark"] .tpl-expire-tag.unauthorized { background: rgba(245, 158, 11, 0.2); }
html[data-theme="dark"] .tpl-expire-tag.trial { background: rgba(147, 51, 234, 0.2); }

/* 内嵌 layui 表格的卡片装饰移除规则已迁移到全局 style.css，
   选择器 #tpl-index-container / #bottomNav-index-container / #userTpl-index-container / #blogTpl-index-container .layui-table-view
   覆盖本页四个模板 tab，避免多处重复维护。 */

/* 列表视图隐藏 */
#tpl-index-container.hidden,
#bottomNav-index-container.hidden,
#userTpl-index-container.hidden,
#blogTpl-index-container.hidden {
    display: none;
}


/* 深色模式适配 - 分页器 */
html[data-theme="dark"] #tpl-cards-pagination .layui-laypage a,
html[data-theme="dark"] #tpl-cards-pagination .layui-laypage span,
html[data-theme="dark"] #bottomNav-cards-pagination .layui-laypage a,
html[data-theme="dark"] #bottomNav-cards-pagination .layui-laypage span,
html[data-theme="dark"] #userTpl-cards-pagination .layui-laypage a,
html[data-theme="dark"] #userTpl-cards-pagination .layui-laypage span,
html[data-theme="dark"] #blogTpl-cards-pagination .layui-laypage a,
html[data-theme="dark"] #blogTpl-cards-pagination .layui-laypage span {
    color: #b0b0b0 !important;
    background-color: #2a2a2a !important;
    border-color: #444 !important;
}
html[data-theme="dark"] #tpl-cards-pagination .layui-laypage a:hover,
html[data-theme="dark"] #bottomNav-cards-pagination .layui-laypage a:hover,
html[data-theme="dark"] #userTpl-cards-pagination .layui-laypage a:hover,
html[data-theme="dark"] #blogTpl-cards-pagination .layui-laypage a:hover {
    color: #16baaa !important;
}
html[data-theme="dark"] #tpl-cards-pagination .layui-laypage .layui-laypage-curr .layui-laypage-em {
    background-color: #16baaa !important;
}
html[data-theme="dark"] #tpl-cards-pagination .layui-laypage .layui-laypage-curr span,
html[data-theme="dark"] #tpl-cards-pagination .layui-laypage .layui-laypage-curr em {
    color: #fff !important;
    background-color: transparent !important;
}
</style>

<?php
$storeTemplateTypeMap = [
    'home'       => 'template',
    'user'       => 'user_template',
    'bottom_nav' => 'bottom_nav_template',
    'blog'       => 'blog_template',
];
$storeTemplateType = $storeTemplateTypeMap[$tab] ?? 'all';
$storeTemplateHref = './store.php?action=tpl' . ($storeTemplateType !== 'all' ? '&template_type=' . urlencode($storeTemplateType) : '') . '&title=全部模板';
?>
<div class="layui-tabs order-tabs-wrapper" style="display:flex;align-items:center;justify-content:space-between;" lay-options="{trigger: false}">
    <ul class="layui-tabs-header">
        <li><a href="<?= htmlspecialchars($storeTemplateHref, ENT_QUOTES) ?>"><i class="ri-store-2-line"></i> 应用商店</a></li>
        <li class="<?= $tab == 'home' ? 'layui-this' : '' ?>"><a href="./template.php?tab=home" data-tab="home">首页模板<?php if($homeTplUpdateCount > 0): ?><span class="update-badge"><?= $homeTplUpdateCount ?></span><?php endif; ?></a></li>
        <li class="<?= $tab == 'user' ? 'layui-this' : '' ?>"><a href="./template.php?tab=user" data-tab="user">用户后台<?php if($userTplUpdateCount > 0): ?><span class="update-badge"><?= $userTplUpdateCount ?></span><?php endif; ?></a></li>
        <li class="<?= $tab == 'bottom_nav' ? 'layui-this' : '' ?>"><a href="./template.php?tab=bottom_nav" data-tab="bottom_nav">底部导航<?php if($bottomNavTplUpdateCount > 0): ?><span class="update-badge"><?= $bottomNavTplUpdateCount ?></span><?php endif; ?></a></li>
        <li class="<?= $tab == 'blog' ? 'layui-this' : '' ?>"><a href="./template.php?tab=blog" data-tab="blog">博客模板<?php if($blogTplUpdateCount > 0): ?><span class="update-badge"><?= $blogTplUpdateCount ?></span><?php endif; ?></a></li>
        <li class="<?= $tab == 'admin' ? 'layui-this' : '' ?>"><a href="./template.php?tab=admin" data-tab="admin">管理后台</a></li>
    </ul>
    <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
        <div class="layui-input-inline layui-input-wrap" style="width:180px;margin:0;">
            <input type="text" class="layui-input" id="tpl-search" placeholder="搜索模板..." lay-affix="clear">
        </div>
        <button type="button" class="layui-btn layui-btn-sm" id="tpl-search-btn">搜索</button>
        <button type="button" class="layui-btn-sm layui-btn-primary" id="tpl-reset-btn"><i class="ri-refresh-line"></i> 刷新</button>
    </div>
</div>

<div class="tpl-tab-panel" id="tab-home" <?= $tab != 'home' ? 'style="display:none;"' : '' ?>>
    <div class="layui-table-view" style="border-radius:8px;overflow:hidden;">
        <div class="layui-card-header" style="display:flex;align-items:center;justify-content:center;position:relative;height:auto;padding:12px 15px;">
            <span style="position:absolute;left:15px;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
                <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
                <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
                <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
            </span>
            <span style="color:#667797;font-size:14px;font-weight:500;">首页模板</span>
            <span style="position:absolute;right:15px;top:65%;transform:translateY(-50%);display:flex;align-items:center;gap:10px;">
                <div class="view-switch" style="margin-bottom:0;">
                    <span class="view-btn active" data-view="list" data-tab="home" title="列表视图"><i class="ri-list-check"></i> 列表</span>
                    <span class="view-btn" data-view="card" data-tab="home" title="卡片视图"><i class="ri-grid-fill"></i> 卡片</span>
                </div>
            </span>
        </div>
        <div class="layui-card-body" style="padding:20px 0;">
            <div id="tpl-index-container">
                <table class="layui-hide" id="index" lay-filter="index"></table>
            </div>
            <div class="tpl-cards" id="tpl-cards-container"></div>
            <div id="tpl-cards-pagination" style="display:none; padding: 15px 0; text-align: center;"></div>
        </div>
    </div>
</div>
<div class="tpl-tab-panel" id="tab-bottom_nav" <?= $tab != 'bottom_nav' ? 'style="display:none;"' : '' ?>>
    <div class="layui-table-view" style="border-radius:8px;overflow:hidden;">
        <div class="layui-card-header" style="display:flex;align-items:center;justify-content:center;position:relative;height:auto;padding:12px 15px;">
            <span style="position:absolute;left:15px;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
                <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
                <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
                <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
            </span>
            <span style="color:#667797;font-size:14px;font-weight:500;">底部导航</span>
            <span style="position:absolute;right:15px;top:65%;transform:translateY(-50%);display:flex;align-items:center;gap:10px;">
                <div class="view-switch" style="margin-bottom:0;">
                    <span class="view-btn active" data-view="list" data-tab="bottom_nav" title="列表视图"><i class="ri-list-check"></i> 列表</span>
                    <span class="view-btn" data-view="card" data-tab="bottom_nav" title="卡片视图"><i class="ri-grid-fill"></i> 卡片</span>
                </div>
            </span>
        </div>
        <div class="layui-card-body" style="padding:20px 0;">
            <div id="bottomNav-index-container">
                <table class="layui-hide" id="bottomNavTplTable" lay-filter="bottomNavTplTable"></table>
            </div>
            <div class="tpl-cards" id="bottomNav-cards-container"></div>
            <div id="bottomNav-cards-pagination" style="display:none; padding: 15px 0; text-align: center;"></div>
        </div>
    </div>
</div>
<div class="tpl-tab-panel" id="tab-user" <?= $tab != 'user' ? 'style="display:none;"' : '' ?>>
    <div class="layui-table-view" style="border-radius:8px;overflow:hidden;">
        <div class="layui-card-header" style="display:flex;align-items:center;justify-content:center;position:relative;height:auto;padding:12px 15px;">
            <span style="position:absolute;left:15px;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
                <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
                <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
                <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
            </span>
            <span style="color:#667797;font-size:14px;font-weight:500;">用户后台模板</span>
            <span style="position:absolute;right:15px;top:65%;transform:translateY(-50%);display:flex;align-items:center;gap:10px;">
                <div class="view-switch" style="margin-bottom:0;">
                    <span class="view-btn active" data-view="list" data-tab="user" title="列表视图"><i class="ri-list-check"></i> 列表</span>
                    <span class="view-btn" data-view="card" data-tab="user" title="卡片视图"><i class="ri-grid-fill"></i> 卡片</span>
                </div>
            </span>
        </div>
        <div class="layui-card-body" style="padding:20px 0;">
            <div id="userTpl-index-container">
                <table class="layui-hide" id="userTplTable" lay-filter="userTplTable"></table>
            </div>
            <div class="tpl-cards" id="userTpl-cards-container"></div>
            <div id="userTpl-cards-pagination" style="display:none; padding: 15px 0; text-align: center;"></div>
        </div>
    </div>
</div>

<script type="text/html" id="cover">
    <div class="layui-clear-space">
        <a href="javascript:;" lay-event="detail">
            <img onerror="this.onerror=null; this.src='./views/images/null.png'" class="cover" src="{{ d.preview }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 3px; cursor: pointer;" />
        </a>
    </div>
</script>

<script type="text/html" id="title">
    <div class="layui-clear-space" style="cursor: pointer; overflow: hidden;" lay-event="detail">
        <div style="line-height: 22px;">
            <strong>{{ d.tplname }}</strong>
            {{# if(d.license_status === 'blocked'){ }}
            <span style="background:rgba(51,51,51,0.1);color:#333;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-forbid-line" style="margin-right:3px;"></i>已被禁用</span>
            {{# } else if(d.license_status === 'unauthorized'){ }}
            <span style="background:rgba(245,158,11,0.1);color:#f59e0b;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-lock-line" style="margin-right:3px;"></i>未授权</span>
            {{# } else if(d.license_status === 'expired'){ }}
            <span style="background:rgba(239,68,68,0.1);color:#ef4444;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-error-warning-line" style="margin-right:3px;"></i>已到期</span>
            {{# } else if(d.license_status === 'trial' || d.buy_type === 'trial'){ }}
            <span style="background:rgba(147,51,234,0.1);color:#9333ea;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-gift-line" style="margin-right:3px;"></i>试用中</span>
            {{# } else if(d.buy_type === 'permanent'){ }}
            <span style="background:rgba(22,163,74,0.1);color:#16a34a;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-shield-check-line" style="margin-right:3px;"></i>已买断</span>
            {{# } else if(d.buy_type === 'monthly' && d.expire_time){ }}
            {{# var now = new Date(); var expireDate = new Date(d.expire_time); }}
            {{# if(expireDate > now){ }}
            <span style="background:rgba(245,158,11,0.1);color:#f59e0b;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-time-line" style="margin-right:3px;"></i>到期<span style="margin-left:4px;">{{ d.expire_time.split(' ')[0] }}</span></span>
            {{# } else { }}
            <span style="background:rgba(239,68,68,0.1);color:#ef4444;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-error-warning-line" style="margin-right:3px;"></i>已到期</span>
            {{# } }}
            {{# } else { }}
            <span style="background:rgba(153,153,153,0.1);color:#999;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-infinity-line" style="margin-right:3px;"></i>永久可用</span>
            {{# } }}
        </div>
        <div style="color: #999; font-size: 12px; margin-top: 3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ d.tpldes || '暂无描述' }}</div>
    </div>
</script>

<script type="text/html" id="switch">
    <input type="checkbox" name="{{= d.tplfile }}" value="{{= d.tplfile }}" title=" ON |OFF " lay-skin="switch" lay-filter="switch" {{= d.switch == 'y' ? "checked" : "" }}>
</script>

<script type="text/html" id="tel_switch">
    <input type="checkbox" name="{{= d.tplfile }}" value="{{= d.tplfile }}" title=" ON |OFF " lay-skin="switch" lay-filter="tel_switch" {{= d.tel_switch == 'y' ? "checked" : "" }}>
</script>

<script type="text/html" id="operate">
    <div class="layui-clear-space">
        {{# if(d.has_setting === 'y'){ }}
        <a class="layui-btn layui-bg-blue" lay-event="setting">配置</a>
        {{# } }}
        <a class="layui-btn layui-bg-red" lay-event="del">卸载</a>
        {{# if(d.update === 'y'){ }}
        <a class="layui-btn layui-bg-green" lay-event="update">更新</a>
        {{# } }}
    </div>
</script>

<script type="text/html" id="bottomNavTplCover">
    <div class="layui-clear-space">
        <a href="javascript:;" lay-event="detail">
            <img onerror="this.onerror=null; this.src='./views/images/null.png'" class="cover" src="{{ d.preview }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 3px; cursor: pointer;" />
        </a>
    </div>
</script>

<script type="text/html" id="bottomNavTplTitle">
    <div class="layui-clear-space" style="cursor: pointer; overflow: hidden;" lay-event="detail">
        <div style="line-height: 22px;">
            <strong>{{ d.tplname }}</strong>
            {{# if(d.license_status === 'blocked'){ }}
            <span style="background:rgba(51,51,51,0.1);color:#333;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-forbid-line" style="margin-right:3px;"></i>已被禁用</span>
            {{# } else if(d.license_status === 'unauthorized'){ }}
            <span style="background:rgba(245,158,11,0.1);color:#f59e0b;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-lock-line" style="margin-right:3px;"></i>未授权</span>
            {{# } else if(d.license_status === 'expired'){ }}
            <span style="background:rgba(239,68,68,0.1);color:#ef4444;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-error-warning-line" style="margin-right:3px;"></i>已到期</span>
            {{# } else if(d.license_status === 'trial' || d.buy_type === 'trial'){ }}
            <span style="background:rgba(147,51,234,0.1);color:#9333ea;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-gift-line" style="margin-right:3px;"></i>试用中</span>
            {{# } else if(d.buy_type === 'permanent'){ }}
            <span style="background:rgba(22,163,74,0.1);color:#16a34a;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-shield-check-line" style="margin-right:3px;"></i>已买断</span>
            {{# } else if(d.buy_type === 'monthly' && d.expire_time){ }}
            {{# var now = new Date(); var expireDate = new Date(d.expire_time); }}
            {{# if(expireDate > now){ }}
            <span style="background:rgba(245,158,11,0.1);color:#f59e0b;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-time-line" style="margin-right:3px;"></i>到期<span style="margin-left:4px;">{{ d.expire_time.split(' ')[0] }}</span></span>
            {{# } else { }}
            <span style="background:rgba(239,68,68,0.1);color:#ef4444;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-error-warning-line" style="margin-right:3px;"></i>已到期</span>
            {{# } }}
            {{# } else { }}
            <span style="background:rgba(153,153,153,0.1);color:#999;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-infinity-line" style="margin-right:3px;"></i>永久可用</span>
            {{# } }}
        </div>
        <div style="color: #999; font-size: 12px; margin-top: 3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ d.tpldes || '暂无描述' }}</div>
    </div>
</script>

<script type="text/html" id="bottomNavTplSwitch">
    <input type="checkbox" name="{{= d.tplfile }}" value="{{= d.tplfile }}" title=" ON |OFF " lay-skin="switch" lay-filter="bottomNavTplSwitch" {{= d.switch == 'y' ? "checked" : "" }}>
</script>

<script type="text/html" id="blogTplSwitch">
    <input type="checkbox" name="{{= d.tplfile }}" value="{{= d.tplfile }}" title=" ON |OFF " lay-skin="switch" lay-filter="blogTplSwitch" {{= d.switch == 'y' ? "checked" : "" }}>
</script>

<script type="text/html" id="blogTplTelSwitch">
    <input type="checkbox" name="{{= d.tplfile }}" value="{{= d.tplfile }}" title=" ON |OFF " lay-skin="switch" lay-filter="blogTplTelSwitch" {{= d.tel_switch == 'y' ? "checked" : "" }}>
</script>

<script type="text/html" id="bottomNavTplOperate">
    <div class="layui-clear-space">
        {{# if(d.has_setting === 'y'){ }}
        <a class="layui-btn layui-bg-blue" lay-event="setting">配置</a>
        {{# } }}
        <a class="layui-btn layui-bg-red" lay-event="del">卸载</a>
        {{# if(d.update === 'y'){ }}
        <a class="layui-btn layui-bg-green" lay-event="update">更新</a>
        {{# } }}
    </div>
</script>

<script type="text/html" id="blogTplCover">
    <div class="layui-clear-space">
        <a href="javascript:;" lay-event="detail">
            <img onerror="this.onerror=null; this.src='./views/images/null.png'" class="cover" src="{{ d.preview }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 3px; cursor: pointer;" />
        </a>
    </div>
</script>

<script type="text/html" id="blogTplTitle">
    <div class="layui-clear-space" style="cursor: pointer; overflow: hidden;" lay-event="detail">
        <div style="line-height: 22px;">
            <strong>{{ d.tplname }}</strong>
            {{# if(d.license_status === 'blocked'){ }}
            <span style="background:rgba(51,51,51,0.1);color:#333;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-forbid-line" style="margin-right:3px;"></i>已被禁用</span>
            {{# } else if(d.license_status === 'unauthorized'){ }}
            <span style="background:rgba(245,158,11,0.1);color:#f59e0b;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-lock-line" style="margin-right:3px;"></i>未授权</span>
            {{# } else if(d.license_status === 'expired'){ }}
            <span style="background:rgba(239,68,68,0.1);color:#ef4444;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-error-warning-line" style="margin-right:3px;"></i>已到期</span>
            {{# } else if(d.license_status === 'trial' || d.buy_type === 'trial'){ }}
            <span style="background:rgba(147,51,234,0.1);color:#9333ea;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-gift-line" style="margin-right:3px;"></i>试用中</span>
            {{# } else if(d.buy_type === 'permanent'){ }}
            <span style="background:rgba(22,163,74,0.1);color:#16a34a;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-shield-check-line" style="margin-right:3px;"></i>已买断</span>
            {{# } else if(d.buy_type === 'monthly' && d.expire_time){ }}
            {{# var now = new Date(); var expireDate = new Date(d.expire_time); }}
            {{# if(expireDate > now){ }}
            <span style="background:rgba(245,158,11,0.1);color:#f59e0b;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-time-line" style="margin-right:3px;"></i>到期<span style="margin-left:4px;">{{ d.expire_time.split(' ')[0] }}</span></span>
            {{# } else { }}
            <span style="background:rgba(239,68,68,0.1);color:#ef4444;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-error-warning-line" style="margin-right:3px;"></i>已到期</span>
            {{# } }}
            {{# } else { }}
            <span style="background:rgba(153,153,153,0.1);color:#999;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-infinity-line" style="margin-right:3px;"></i>永久可用</span>
            {{# } }}
        </div>
        <div style="color: #999; font-size: 12px; margin-top: 3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ d.tpldes || '暂无描述' }}</div>
    </div>
</script>

<script type="text/html" id="blogTplOperate">
    <div class="layui-clear-space">
        {{# if(d.has_setting === 'y'){ }}
        <a class="layui-btn layui-bg-blue" lay-event="setting">配置</a>
        {{# } }}
        <a class="layui-btn layui-bg-red" lay-event="del">卸载</a>
        {{# if(d.update === 'y'){ }}
        <a class="layui-btn layui-bg-green" lay-event="update">更新</a>
        {{# } }}
    </div>
</script>


<!-- 用户后台模板 - 封面模板 -->
<script type="text/html" id="userTplCover">
    <div class="layui-clear-space">
        <a href="javascript:;" lay-event="detail">
            <img onerror="this.onerror=null; this.src='./views/images/null.png'" class="cover" src="{{ d.preview }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 3px; cursor: pointer;" />
        </a>
    </div>
</script>

<!-- 用户后台模板 - 名称模板 -->
<script type="text/html" id="userTplTitle">
    <div class="layui-clear-space" style="cursor: pointer; overflow: hidden;" lay-event="detail">
        <div style="line-height: 22px;">
            <strong>{{ d.tplname }}</strong>
            {{# if(d.license_status === 'blocked'){ }}
            <span style="background:rgba(51,51,51,0.1);color:#333;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-forbid-line" style="margin-right:3px;"></i>已被禁用</span>
            {{# } else if(d.license_status === 'unauthorized'){ }}
            <span style="background:rgba(245,158,11,0.1);color:#f59e0b;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-lock-line" style="margin-right:3px;"></i>未授权</span>
            {{# } else if(d.license_status === 'expired'){ }}
            <span style="background:rgba(239,68,68,0.1);color:#ef4444;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-error-warning-line" style="margin-right:3px;"></i>已到期</span>
            {{# } else if(d.license_status === 'trial' || d.buy_type === 'trial'){ }}
            <span style="background:rgba(147,51,234,0.1);color:#9333ea;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-gift-line" style="margin-right:3px;"></i>试用中</span>
            {{# } else if(d.buy_type === 'permanent'){ }}
            <span style="background:rgba(22,163,74,0.1);color:#16a34a;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-shield-check-line" style="margin-right:3px;"></i>已买断</span>
                {{# } else if(d.buy_type === 'monthly' && d.expire_time){ }}
                {{# var now = new Date(); var expireDate = new Date(d.expire_time); }}
                {{# if(expireDate > now){ }}
            <span style="background:rgba(245,158,11,0.1);color:#f59e0b;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-time-line" style="margin-right:3px;"></i>到期<span style="margin-left:4px;">{{ d.expire_time.split(' ')[0] }}</span></span>
                {{# } else { }}
            <span style="background:rgba(239,68,68,0.1);color:#ef4444;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-error-warning-line" style="margin-right:3px;"></i>已到期</span>
                {{# } }}
            {{# } else { }}
            <span style="background:rgba(153,153,153,0.1);color:#999;font-size:12px;padding:2px 6px;border-radius:3px;margin-left:6px;"><i class="ri-infinity-line" style="margin-right:3px;"></i>永久可用</span>
            {{# } }}
        </div>
        <div style="color: #999; font-size: 12px; margin-top: 3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ d.tpldes || '暂无描述' }}</div>
    </div>
</script>

<!-- 用户后台模板 - PC端开关 -->
<script type="text/html" id="userTplSwitch">
    <input type="checkbox" name="{{= d.tplfile }}" value="{{= d.tplfile }}" title=" ON |OFF " lay-skin="switch" lay-filter="userTplSwitch" {{= d.switch == 'y' ? "checked" : "" }}>
</script>

<!-- 用户后台模板 - 手机端开关 -->
<script type="text/html" id="userTplTelSwitch">
    <input type="checkbox" name="{{= d.tplfile }}" value="{{= d.tplfile }}" title=" ON |OFF " lay-skin="switch" lay-filter="userTplTelSwitch" {{= d.tel_switch == 'y' ? "checked" : "" }}>
</script>

<!-- 用户后台模板 - 操作 -->
<script type="text/html" id="userTplOperate">
    <div class="layui-clear-space">
        {{# if(d.has_setting === 'y'){ }}
        <a class="layui-btn layui-bg-blue" lay-event="setting">配置</a>
        {{# } }}
        <a class="layui-btn layui-bg-red" lay-event="del">卸载</a>
        {{# if(d.update === 'y'){ }}
        <a class="layui-btn layui-bg-green" lay-event="update">更新</a>
        {{# } }}
    </div>
</script>
<div class="tpl-tab-panel" id="tab-blog" <?= $tab != 'blog' ? 'style="display:none;"' : '' ?>>
    <div class="layui-table-view" style="border-radius:8px;overflow:hidden;">
        <div class="layui-card-header" style="display:flex;align-items:center;justify-content:center;position:relative;height:auto;padding:12px 15px;">
            <span style="position:absolute;left:15px;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
                <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
                <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
                <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
            </span>
            <span style="color:#667797;font-size:14px;font-weight:500;">博客模板</span>
            <span style="position:absolute;right:15px;top:65%;transform:translateY(-50%);display:flex;align-items:center;gap:10px;">
                <div class="view-switch" style="margin-bottom:0;">
                    <span class="view-btn active" data-view="list" data-tab="blog" title="列表视图"><i class="ri-list-check"></i> 列表</span>
                    <span class="view-btn" data-view="card" data-tab="blog" title="卡片视图"><i class="ri-grid-fill"></i> 卡片</span>
                </div>
            </span>
        </div>
        <div class="layui-card-body" style="padding:20px 0;">
            <div id="blogTpl-index-container">
                <table class="layui-hide" id="blogTplTable" lay-filter="blogTplTable"></table>
            </div>
            <div class="tpl-cards" id="blogTpl-cards-container"></div>
            <div id="blogTpl-cards-pagination" style="display:none; padding: 15px 0; text-align: center;"></div>
        </div>
    </div>
</div>

<div class="tpl-tab-panel" id="tab-admin" <?= $tab != 'admin' ? 'style="display:none;"' : '' ?>>
    <div style="text-align:center; padding:80px 20px; color:#999;">
        <i class="ri-settings-3-line" style="font-size:64px; color:#ddd; display:block; margin-bottom:20px;"></i>
        <h3 style="font-size:18px; color:#666; margin-bottom:10px;">管理后台模板</h3>
        <p style="font-size:14px; line-height:1.8;">该功能正在开发中，后续将支持独立的管理后台模板管理。<br>届时可切换不同的后台管理界面风格，提升管理体验！</p>
    </div>
</div>

<script>
    layui.use(['table'], function(){
        var table = layui.table;
        var form = layui.form;
        // 创建渲染实例
        window.table = table.render({
            elem: '#index',
            id: 'index',
            method: 'get',
            autoSort: false,
            url: '?action=index',
            limits: [10, 20, 50],
            page: true,
            lineStyle: 'height: 69px;',
            cols: [[
                {field:'name', title:'图标', width: 80, templet: '#cover', align: 'center'},
                {field:'title', title:'模板名称', minWidth: 520, templet: '#title'},
                {field:'version', title:'版本', width: 100, align: 'center' },
                {field:'author', title:'作者', width: 110, align: 'center'},
                {field:'switch', title:'电脑端', align: 'center', width: 100, templet: '#switch'},
                {field:'tel_switch', title:'手机端', align: 'center', width: 100, templet: '#tel_switch'},
                {fixed: 'right', title:'操作', templet: '#operate', width: <?= $homeTplUpdateCount > 0 ? 280 : 210 ?>, align: 'left'}
            ]],

            done: function(){},

            error: function(res, msg){
                console.log(res, msg)
            }
        });

        // 状态 - 开关操作
        form.on('switch(switch)', function(obj){
            var active = obj.elem.checked == true ? 1 : 0;
            var tpl = this.name;
            if(active == 0){
                tpl = 'em_null_tpl';
            }
            var loadSwitch = layer.load(2);
            $.ajax({
                url: '?action=use',
                type: 'POST',
                dataType: 'json',
                data: { tpl: tpl, status: active, token: '<?= LoginAuth::genToken() ?>' },
                success: function(e) {
                    if(e.code == 400){
                        return layer.msg(e.msg)
                    }
                    layer.msg('模板配置已更新');
                    table.reload('index');
                },
                error: function(err) {
                    layer.msg(err.responseJSON.msg);
                },
                complete: function() {
                    layer.close(loadSwitch);
                }
            });
        });
        form.on('switch(tel_switch)', function(obj){
            var active = obj.elem.checked == true ? 1 : 0;
            var tpl = this.name;
            if(active == 0){
                tpl = 'em_null_tpl';
            }
            var loadSwitch = layer.load(2);
            $.ajax({
                url: '?action=use_tel',
                type: 'POST',
                dataType: 'json',
                data: { tpl: tpl, status: active, token: '<?= LoginAuth::genToken() ?>' },
                success: function(e) {
                    if(e.code == 400){
                        return layer.msg(e.msg)
                    }
                    layer.msg('模板配置已更新');
                    table.reload('index');
                },
                error: function(err) {
                    layer.msg(err.responseJSON.msg);
                },
                complete: function() {
                    layer.close(loadSwitch);
                }
            });
        });


        // 触发单元格工具事件
        table.on('tool(index)', function(obj){ // 双击 toolDouble
            var data = obj.data; // 获得当前行数据
            var id = obj.config.id;
            if(obj.event == 'del'){
                layer.confirm('确定卸载该模板？', {
                    btn: ['确认', '取消'], // 按钮
                    icon: 3,             // 图标，3表示问号
                    title: '温馨提示'
                }, function(index) {
                    layer.close(index); // 关闭对话框
                    $.ajax({
                        url: '?action=del',
                        type: 'POST',
                        dataType: 'json',
                        data: { ids: data.tplfile, token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e) {
                            if(e.code == 400){
                                return layer.msg(e.msg)
                            }
                            layer.msg('模板已卸载');
                            table.reload(id);
                        },
                        error: function(err) {
                            layer.msg(err.responseJSON.msg);
                        }
                    });
                });
            }

            if(obj.event === 'detail'){
                showTplDetail(data);
            }
            if(obj.event === 'update'){
                var loadSwitch = layer.load(2);
                $.ajax({
                    url: '?action=upgrade',
                    type: 'POST',
                    dataType: 'json',
                    data: { plugin_id: data.id, alias: data.tplfile, token: '<?= LoginAuth::genToken() ?>' },
                    success: function(e) {
                        if(e.code == 400){
                            return layer.msg(e.msg)
                        }
                        layer.msg('模板已升级至最新版');
                        table.reload(id);
                    },
                    error: function(err) {
                        layer.msg(err.responseJSON.msg);
                    },
                    complete: function() {
                        layer.close(loadSwitch);
                    }
                });

            }
            if(obj.event === 'setting'){
                let isMobile = window.innerWidth < 1200;
                let area = isMobile ? ['100%', '100%'] : ['1000px', '80%'];
                layer.open({
                    id: 'setting',
                    title: '配置',
                    type: 2,
                    area: area,
                    // skin: 'layui-layer-win10',
                    skin: 'dc-layer-modern',
                    content: '?action=setting_page&tpl=' + encodeURIComponent(data.tplfile) + '&_t=' + Date.now(),
                    fixed: false, // 不固定
                    scrollbar: false,
                    maxmin: true,
                    shadeClose: true,
                    success: function(layero, index, that){
                    }
                });
            }
        });

        // 触发排序事件
        table.on('sort(index)', function(obj){
            console.log(obj.field); // 当前排序的字段名
            console.log(obj.type); // 当前排序类型：desc（降序）、asc（升序）、null（空对象，默认排序）
            console.log(this); // 当前排序的 th 对象

            // 尽管我们的 table 自带排序功能，但并没有请求服务端。
            // 有些时候，你可能需要根据当前排序的字段，重新向后端发送请求，从而实现服务端排序，如：
            table.reload('index', {
                initSort: obj, // 记录初始排序，如果不设的话，将无法标记表头的排序状态。
                where: { // 请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    field: obj.field, // 排序字段
                    order: obj.type // 排序方式
                }
            });
        });

        // 触发表格复选框选择
        table.on('checkbox(index)', function(obj){
            var id = obj.config.id;
            var checkData = table.checkStatus(id).data;
            console.log(checkData)
            if(checkData.length == 0){
                $('#toolbar-del').addClass('layui-btn-disabled');
            }else{
                $('#toolbar-del').removeClass('layui-btn-disabled');
            }
        });

        // 分页栏事件
        table.on('pagebar(index)', function(obj){
            alert()
            console.log(obj); // 查看对象所有成员
            console.log(obj.config); // 当前实例的配置信息
            console.log(obj.event); // 属性 lay-event 对应的值
        });


        // 表头自定义元素工具事件 --- 2.8.8+
        table.on('colTool(test)', function(obj){
            var event = obj.event;
            console.log(obj);
            if(event === 'email-tips'){
                layer.alert(layui.util.escape(JSON.stringify(obj.col)), {
                    title: '当前列属性选项'
                });
            }
        });


    });
</script>

<!-- 用户后台模板 JS -->
<script>
    layui.use(['table'], function(){
        var table = layui.table;
        var form = layui.form;

        window.bottomNavTplTableInited = false;
        window.blogTplTableInited = false;

        window.initBottomNavTplTable = function(){
            if (window.bottomNavTplTableInited) return;
            window.bottomNavTplTableInited = true;

            table.render({
                elem: '#bottomNavTplTable',
                id: 'bottomNavTplTable',
                method: 'get',
                autoSort: false,
                url: '?action=bottom_nav_index',
                limits: [10, 20, 50],
                page: true,
                lineStyle: 'height: 69px;',
                done: function(){},
                cols: [[
                    {field:'name', title:'图标', width: 80, templet: '#bottomNavTplCover', align: 'center'},
                    {field:'title', title:'模板名称', minWidth: 520, templet: '#bottomNavTplTitle'},
                    {field:'version', title:'版本', width: 100, align: 'center'},
                    {field:'author', title:'作者', width: 110, align: 'center'},
                    {field:'switch', title:'启用', align: 'center', width: 100, templet: '#bottomNavTplSwitch'},
                    {fixed: 'right', title:'操作', templet: '#bottomNavTplOperate', width: <?= $bottomNavTplUpdateCount > 0 ? 280 : 210 ?>, align: 'left'}
                ]],
                error: function(res, msg){
                    console.log(res, msg);
                }
            });
        };

        window.initBlogTplTable = function(){
            if (window.blogTplTableInited) return;
            window.blogTplTableInited = true;

            table.render({
                elem: '#blogTplTable',
                id: 'blogTplTable',
                method: 'get',
                autoSort: false,
                url: '?action=blog_index',
                limits: [10, 20, 50],
                page: true,
                lineStyle: 'height: 69px;',
                done: function(){},
                cols: [[
                    {field:'name', title:'图标', width: 80, templet: '#blogTplCover', align: 'center'},
                    {field:'title', title:'模板名称', minWidth: 520, templet: '#blogTplTitle'},
                    {field:'version', title:'版本', width: 100, align: 'center'},
                    {field:'author', title:'作者', width: 110, align: 'center'},
                    {field:'switch', title:'电脑端', align: 'center', width: 100, templet: '#blogTplSwitch'},
                    {field:'tel_switch', title:'手机端', align: 'center', width: 100, templet: '#blogTplTelSwitch'},
                    {fixed: 'right', title:'操作', templet: '#blogTplOperate', width: <?= $blogTplUpdateCount > 0 ? 280 : 210 ?>, align: 'left'}
                ]],
                error: function(res, msg){
                    console.log(res, msg);
                }
            });
        };

        // 标记是否已初始化过用户后台模板表格
        window.userTplTableInited = false;

        // 初始化用户后台模板表格
        window.initUserTplTable = function(){
            if (window.userTplTableInited) return;
            window.userTplTableInited = true;

            table.render({
                elem: '#userTplTable',
                id: 'userTplTable',
                method: 'get',
                autoSort: false,
                url: '?action=user_index',
                limits: [10, 20, 50],
                page: true,
                lineStyle: 'height: 69px;',
                done: function(){},
                cols: [[
                    {field:'name', title:'图标', width: 80, templet: '#userTplCover', align: 'center'},
                    {field:'title', title:'模板名称', minWidth: 520, templet: '#userTplTitle'},
                    {field:'version', title:'版本', width: 100, align: 'center'},
                    {field:'author', title:'作者', width: 110, align: 'center'},
                    {field:'switch', title:'电脑端', align: 'center', width: 100, templet: '#userTplSwitch'},
                    {field:'tel_switch', title:'手机端', align: 'center', width: 100, templet: '#userTplTelSwitch'},
                    {fixed: 'right', title:'操作', templet: '#userTplOperate', width: <?= $userTplUpdateCount > 0 ? 280 : 210 ?>, align: 'left'}
                ]],
                error: function(res, msg){
                    console.log(res, msg);
                }
            });
        };

        // 页面加载时根据 tab 参数自动初始化
        var initTab = '<?= $tab ?>';
        if (initTab === 'bottom_nav') {
            window.initBottomNavTplTable();
        }
        if (initTab === 'blog') {
            window.initBlogTplTable();
        }
        if (initTab === 'user') {
            window.initUserTplTable();
        }

        form.on('switch(bottomNavTplSwitch)', function(obj){
            var tpl = obj.elem.checked ? this.name : 'em_null_tpl';
            var loadSwitch = layer.load(2);
            $.ajax({
                url: '?action=bottom_nav_use',
                type: 'POST',
                dataType: 'json',
                data: { tpl: tpl, token: '<?= LoginAuth::genToken() ?>' },
                success: function(e) {
                    if (e.code == 400) return layer.msg(e.msg);
                    layer.msg('模板配置已更新');
                    table.reload('bottomNavTplTable');
                },
                error: function(err) {
                    layer.msg(err.responseJSON.msg);
                },
                complete: function() {
                    layer.close(loadSwitch);
                }
            });
        });

        form.on('switch(blogTplSwitch)', function(obj){
            var tpl = obj.elem.checked ? this.name : 'em_null_tpl';
            var loadSwitch = layer.load(2);
            $.ajax({
                url: '?action=blog_use',
                type: 'POST',
                dataType: 'json',
                data: { tpl: tpl, token: '<?= LoginAuth::genToken() ?>' },
                success: function(e) {
                    if (e.code == 400) return layer.msg(e.msg);
                    layer.msg('博客模板已切换');
                    table.reload('blogTplTable');
                },
                error: function(err) {
                    layer.msg(err.responseJSON ? err.responseJSON.msg : '操作失败');
                },
                complete: function() {
                    layer.close(loadSwitch);
                }
            });
        });

        form.on('switch(blogTplTelSwitch)', function(obj){
            var tpl = obj.elem.checked ? this.name : 'em_null_tpl';
            var loadSwitch = layer.load(2);
            $.ajax({
                url: '?action=blog_use_tel',
                type: 'POST',
                dataType: 'json',
                data: { tpl: tpl, token: '<?= LoginAuth::genToken() ?>' },
                success: function(e) {
                    if (e.code == 400) return layer.msg(e.msg);
                    layer.msg('博客手机端模板已切换');
                    table.reload('blogTplTable');
                },
                error: function(err) {
                    layer.msg(err.responseJSON ? err.responseJSON.msg : '操作失败');
                },
                complete: function() {
                    layer.close(loadSwitch);
                }
            });
        });

        table.on('tool(bottomNavTplTable)', function(obj){
            var data = obj.data;
            if (obj.event === 'detail') {
                showBottomNavTplDetail(data);
                return;
            }
            if (obj.event == 'setting') {
                let isMobile = window.innerWidth < 1200;
                let area = isMobile ? ['100%', '100%'] : ['1000px', '80%'];
                layer.open({
                    id: 'bottomNavSetting',
                    title: '底部导航配置',
                    type: 2,
                    area: area,
                    skin: 'dc-layer-modern',
                    content: '?action=bottom_nav_setting_page&tpl=' + encodeURIComponent(data.tplfile) + '&_t=' + Date.now(),
                    fixed: false,
                    scrollbar: false,
                    maxmin: true,
                    shadeClose: true,
                    success: function(layero, index) {
                        layer.full(index);
                    }
                });
                return;
            }
            if (obj.event == 'del') {
                layer.confirm('确定卸载该底部导航模板？', {
                    btn: ['确认卸载', '取消'],
                    icon: 3,
                    title: '温馨提示'
                }, function(index) {
                    layer.close(index);
                    var loadSwitch = layer.load(2);
                    $.ajax({
                        url: '?action=bottom_nav_del',
                        type: 'POST',
                        dataType: 'json',
                        data: { ids: data.tplfile, token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e) {
                            if (e.code == 400) return layer.msg(e.msg);
                            layer.msg('模板已卸载');
                            table.reload('bottomNavTplTable');
                        },
                        error: function(err) {
                            layer.msg(err.responseJSON ? err.responseJSON.msg : '卸载失败');
                        },
                        complete: function() {
                            layer.close(loadSwitch);
                        }
                    });
                });
            }
            if (obj.event === 'update') {
                var loadSwitch = layer.load(2);
                $.ajax({
                    url: '?action=bottom_nav_upgrade',
                    type: 'POST',
                    dataType: 'json',
                    data: { plugin_id: data.id, alias: data.tplfile, token: '<?= LoginAuth::genToken() ?>' },
                    success: function(e) {
                        if (e.code == 400) return layer.msg(e.msg);
                        layer.msg('模板已升级至最新版');
                        table.reload('bottomNavTplTable');
                    },
                    error: function(err) {
                        layer.msg(err.responseJSON ? err.responseJSON.msg : '更新失败');
                    },
                    complete: function() {
                        layer.close(loadSwitch);
                    }
                });
            }
        });

        table.on('tool(blogTplTable)', function(obj){
            var data = obj.data;
            if (obj.event === 'detail') {
                showBlogTplDetail(data);
                return;
            }
            if (obj.event == 'setting') {
                let isMobile = window.innerWidth < 1200;
                let area = isMobile ? ['96%', '92%'] : ['1100px', '82%'];
                layer.open({
                    id: 'blogTplSetting',
                    title: '博客模板配置',
                    type: 2,
                    area: area,
                    skin: 'dc-layer-modern',
                    content: '?action=blog_setting_page&tpl=' + encodeURIComponent(data.tplfile) + '&_t=' + Date.now(),
                    fixed: false,
                    scrollbar: false,
                    maxmin: true,
                    shadeClose: true
                });
                return;
            }
            if (obj.event == 'del') {
                handleBlogTplDel(data);
            }
            if (obj.event === 'update') {
                handleBlogTplUpdate(data);
            }
        });

    });

    // 底部导航模板 - 详情弹窗（与首页模板弹窗统一结构）
    function showBottomNavTplDetail(tpl) {
        var coverHtml = tpl.preview && tpl.preview.indexOf('null.png') === -1 && tpl.preview.indexOf('theme.png') === -1
            ? '<img src="' + escapeHtml(tpl.preview) + '" onerror="this.style.display=\'none\'">'
            : '<i class="ri-layout-4-line default-icon"></i>';

        // 启用状态（底部导航只有单开关）
        var switchHtml = tpl.switch === 'y'
            ? '<div class="tpl-detail-status-item on" onclick="toggleBottomNavTplStatus(\'' + tpl.tplfile + '\', 0)"><i class="ri-navigation-line"></i> 已启用 <span style="font-size:11px;opacity:0.8;">(点击关闭)</span></div>'
            : '<div class="tpl-detail-status-item off" onclick="toggleBottomNavTplStatus(\'' + tpl.tplfile + '\', 1)"><i class="ri-navigation-line"></i> 已关闭 <span style="font-size:11px;opacity:0.8;">(点击启用)</span></div>';

        // 授权状态
        var licenseHtml = (function(){
            if (tpl.license_status === 'blocked') {
                return '<span style="color:#333;"><i class="ri-forbid-line"></i> 已被禁用</span>';
            } else if (tpl.license_status === 'unauthorized') {
                return '<span style="color:#f59e0b;"><i class="ri-lock-line"></i> 未授权</span>';
            } else if (tpl.license_status === 'expired') {
                return '<span style="color:#ef4444;"><i class="ri-error-warning-line"></i> 已到期</span>';
            } else if (tpl.license_status === 'trial' || tpl.buy_type === 'trial') {
                return '<span style="color:#9333ea;"><i class="ri-gift-line"></i> 试用中</span>';
            } else if (tpl.buy_type === 'permanent') {
                return '<span style="color:#16a34a;"><i class="ri-shield-check-line"></i> 已买断</span>';
            } else if (tpl.buy_type === 'monthly' && tpl.expire_time) {
                var now = new Date();
                var expireDate = new Date(tpl.expire_time);
                if (expireDate > now) {
                    return '<span style="color:#f59e0b;"><i class="ri-time-line"></i> 到期：' + tpl.expire_time.split(' ')[0] + '</span>';
                } else {
                    return '<span style="color:#ef4444;"><i class="ri-error-warning-line"></i> 已到期</span>';
                }
            } else {
                return '<span><i class="ri-infinity-line"></i> 永久可用</span>';
            }
        })();

        var actionsHtml = '';
        if (tpl.has_setting === 'y') {
            actionsHtml += '<button class="layui-btn layui-bg-blue" onclick="openBottomNavTplSetting(window._currentBottomNavTpl)"><i class="ri-settings-3-line"></i> 配置</button>';
        }
        actionsHtml += '<button class="layui-btn layui-bg-red" onclick="var t = window._currentBottomNavTpl; closeBottomNavTplDetail(); handleBottomNavTplDel(t);"><i class="ri-delete-bin-line"></i> 卸载</button>';
        if (tpl.update === 'y') {
            actionsHtml += '<button class="layui-btn layui-bg-green" onclick="var t = window._currentBottomNavTpl; closeBottomNavTplDetail(); handleBottomNavTplUpdate(t);"><i class="ri-refresh-line"></i> 更新</button>';
        }

        var html = '<div class="tpl-detail-modal bottomNav-tpl-detail-modal" onclick="closeBottomNavTplDetail()">' +
            '<div class="tpl-detail-box" onclick="event.stopPropagation()">' +
                '<div class="tpl-detail-header">' + coverHtml +
                    '<button class="tpl-detail-close" onclick="closeBottomNavTplDetail()"><i class="ri-close-line"></i></button>' +
                '</div>' +
                '<div class="tpl-detail-body">' +
                    '<div class="tpl-detail-title">' + escapeHtml(tpl.tplname) + '</div>' +
                    '<div class="tpl-detail-meta">' +
                        '<span><i class="ri-user-line"></i> ' + escapeHtml(tpl.author || '未知') + '</span>' +
                        '<span><i class="ri-price-tag-3-line"></i> v' + escapeHtml(tpl.version || '1.0.0') + '</span>' +
                        licenseHtml +
                    '</div>' +
                    '<div class="tpl-detail-status">' + switchHtml + '</div>' +
                    '<div class="tpl-detail-desc">' + escapeHtml(tpl.tpldes || '暂无描述') + '</div>' +
                    '<div class="tpl-detail-actions">' + actionsHtml + '</div>' +
                '</div>' +
            '</div>' +
        '</div>';

        window._currentBottomNavTpl = tpl;
        $('body').append(html);
    }

    function closeBottomNavTplDetail() {
        $('.bottomNav-tpl-detail-modal').remove();
        window._currentBottomNavTpl = null;
    }

    function toggleBottomNavTplStatus(tplfile, newActive) {
        var tpl = newActive == 1 ? tplfile : 'em_null_tpl';
        var loadSwitch = layer.load(2);
        $.ajax({
            url: '?action=bottom_nav_use',
            type: 'POST',
            dataType: 'json',
            data: { tpl: tpl, token: '<?= LoginAuth::genToken() ?>' },
            success: function(e) {
                if (e.code == 400) return layer.msg(e.msg);
                layer.msg('模板配置已更新');
                closeBottomNavTplDetail();
                layui.table.reload('bottomNavTplTable');
                // 卡片视图下同步刷新
                if (window._tabCardViews && window._tabCardViews.bottom_nav && window._tabCardViews.bottom_nav.currentView === 'card') {
                    window.loadTabCards('bottom_nav', function(){ window.renderTabCardPagination('bottom_nav'); });
                }
            },
            error: function(err) {
                layer.msg(err.responseJSON ? err.responseJSON.msg : '操作失败');
            },
            complete: function() {
                layer.close(loadSwitch);
            }
        });
    }

    function openBottomNavTplSetting(tpl) {
        if (!tpl || tpl.has_setting !== 'y') return;
        let isMobile = window.innerWidth < 1200;
        let area = isMobile ? ['100%', '100%'] : ['1000px', '80%'];
        layer.open({
            id: 'bottomNavSetting',
            title: '底部导航配置',
            type: 2,
            area: area,
            skin: 'dc-layer-modern',
            content: '?action=bottom_nav_setting_page&tpl=' + encodeURIComponent(tpl.tplfile) + '&_t=' + Date.now(),
            fixed: false,
            scrollbar: false,
            maxmin: true,
            shadeClose: true,
            success: function(layero, index) {
                layer.full(index);
            }
        });
    }

    function handleBottomNavTplDel(data) {
        layer.confirm('确定卸载该底部导航模板？', {
            btn: ['确认卸载', '取消'],
            icon: 3,
            title: '温馨提示'
        }, function(index) {
            layer.close(index);
            var loadSwitch = layer.load(2);
            $.ajax({
                url: '?action=bottom_nav_del',
                type: 'POST',
                dataType: 'json',
                data: { ids: data.tplfile, token: '<?= LoginAuth::genToken() ?>' },
                success: function(e) {
                    if (e.code == 400) return layer.msg(e.msg);
                    layer.msg('模板已卸载');
                    layui.table.reload('bottomNavTplTable');
                },
                error: function(err) {
                    layer.msg(err.responseJSON ? err.responseJSON.msg : '卸载失败');
                },
                complete: function() {
                    layer.close(loadSwitch);
                }
            });
        });
    }

    function handleBottomNavTplUpdate(data) {
        var loadSwitch = layer.load(2);
        $.ajax({
            url: '?action=bottom_nav_upgrade',
            type: 'POST',
            dataType: 'json',
            data: { plugin_id: data.id, alias: data.tplfile, token: '<?= LoginAuth::genToken() ?>' },
            success: function(e) {
                if (e.code == 400) return layer.msg(e.msg);
                layer.msg('模板已升级至最新版');
                layui.table.reload('bottomNavTplTable');
                if (window._tabCardViews && window._tabCardViews.bottom_nav && window._tabCardViews.bottom_nav.currentView === 'card') {
                    window.loadTabCards('bottom_nav', function(){ window.renderTabCardPagination('bottom_nav'); });
                }
            },
            error: function(err) {
                layer.msg(err.responseJSON ? err.responseJSON.msg : '更新失败');
            },
            complete: function() {
                layer.close(loadSwitch);
            }
        });
    }

    // 博客模板 - 详情弹窗
    function showBlogTplDetail(tpl) {
        var coverHtml = tpl.preview && tpl.preview.indexOf('null.png') === -1 && tpl.preview.indexOf('theme.png') === -1
            ? '<img src="' + escapeHtml(tpl.preview) + '" onerror="this.style.display=\'none\'">'
            : '<i class="ri-article-line default-icon"></i>';

        var switchHtml = tpl.switch === 'y'
            ? '<div class="tpl-detail-status-item on" onclick="toggleBlogTplStatus(\'' + tpl.tplfile + '\', 0)"><i class="ri-article-line"></i> 已启用 <span style="font-size:11px;opacity:0.8;">(点击关闭)</span></div>'
            : '<div class="tpl-detail-status-item off" onclick="toggleBlogTplStatus(\'' + tpl.tplfile + '\', 1)"><i class="ri-article-line"></i> 已关闭 <span style="font-size:11px;opacity:0.8;">(点击启用)</span></div>';

        var licenseHtml = (function(){
            if (tpl.license_status === 'blocked') {
                return '<span style="color:#333;"><i class="ri-forbid-line"></i> 已被禁用</span>';
            } else if (tpl.license_status === 'unauthorized') {
                return '<span style="color:#f59e0b;"><i class="ri-lock-line"></i> 未授权</span>';
            } else if (tpl.license_status === 'expired') {
                return '<span style="color:#ef4444;"><i class="ri-error-warning-line"></i> 已到期</span>';
            } else if (tpl.license_status === 'trial' || tpl.buy_type === 'trial') {
                return '<span style="color:#9333ea;"><i class="ri-gift-line"></i> 试用中</span>';
            } else if (tpl.buy_type === 'permanent') {
                return '<span style="color:#16a34a;"><i class="ri-shield-check-line"></i> 已买断</span>';
            } else if (tpl.buy_type === 'monthly' && tpl.expire_time) {
                var now = new Date();
                var expireDate = new Date(tpl.expire_time);
                if (expireDate > now) {
                    return '<span style="color:#f59e0b;"><i class="ri-time-line"></i> 到期：' + tpl.expire_time.split(' ')[0] + '</span>';
                } else {
                    return '<span style="color:#ef4444;"><i class="ri-error-warning-line"></i> 已到期</span>';
                }
            } else {
                return '<span><i class="ri-infinity-line"></i> 永久可用</span>';
            }
        })();

        var actionsHtml = '';
        if (tpl.has_setting === 'y') {
            actionsHtml += '<button class="layui-btn layui-bg-blue" onclick="openBlogTplSetting(window._currentBlogTpl)"><i class="ri-settings-3-line"></i> 配置</button>';
        }
        actionsHtml += '<button class="layui-btn layui-bg-red" onclick="var t = window._currentBlogTpl; closeBlogTplDetail(); handleBlogTplDel(t);"><i class="ri-delete-bin-line"></i> 卸载</button>';
        if (tpl.update === 'y') {
            actionsHtml += '<button class="layui-btn layui-bg-green" onclick="var t = window._currentBlogTpl; closeBlogTplDetail(); handleBlogTplUpdate(t);"><i class="ri-refresh-line"></i> 更新</button>';
        }

        var html = '<div class="tpl-detail-modal blog-tpl-detail-modal" onclick="closeBlogTplDetail()">' +
            '<div class="tpl-detail-box" onclick="event.stopPropagation()">' +
                '<div class="tpl-detail-header">' + coverHtml +
                    '<button class="tpl-detail-close" onclick="closeBlogTplDetail()"><i class="ri-close-line"></i></button>' +
                '</div>' +
                '<div class="tpl-detail-body">' +
                    '<div class="tpl-detail-title">' + escapeHtml(tpl.tplname) + '</div>' +
                    '<div class="tpl-detail-meta">' +
                        '<span><i class="ri-user-line"></i> ' + escapeHtml(tpl.author || '未知') + '</span>' +
                        '<span><i class="ri-price-tag-3-line"></i> v' + escapeHtml(tpl.version || '1.0.0') + '</span>' +
                        licenseHtml +
                    '</div>' +
                    '<div class="tpl-detail-status">' + switchHtml + '</div>' +
                    '<div class="tpl-detail-desc">' + escapeHtml(tpl.tpldes || '暂无描述') + '</div>' +
                    '<div class="tpl-detail-actions">' + actionsHtml + '</div>' +
                '</div>' +
            '</div>' +
        '</div>';

        window._currentBlogTpl = tpl;
        $('body').append(html);
    }

    function closeBlogTplDetail() {
        $('.blog-tpl-detail-modal').remove();
        window._currentBlogTpl = null;
    }

    function toggleBlogTplStatus(tplfile, newActive) {
        var tpl = newActive == 1 ? tplfile : 'em_null_tpl';
        var loadSwitch = layer.load(2);
        $.ajax({
            url: '?action=blog_use',
            type: 'POST',
            dataType: 'json',
            data: { tpl: tpl, token: '<?= LoginAuth::genToken() ?>' },
            success: function(e) {
                if (e.code == 400) return layer.msg(e.msg);
                layer.msg('博客模板已切换');
                closeBlogTplDetail();
                layui.table.reload('blogTplTable');
                if (window._tabCardViews && window._tabCardViews.blog && window._tabCardViews.blog.currentView === 'card') {
                    window.loadTabCards('blog', function(){ window.renderTabCardPagination('blog'); });
                }
            },
            error: function(err) {
                layer.msg(err.responseJSON ? err.responseJSON.msg : '操作失败');
            },
            complete: function() {
                layer.close(loadSwitch);
            }
        });
    }

    function openBlogTplSetting(tpl) {
        if (!tpl || tpl.has_setting !== 'y') return;
        let isMobile = window.innerWidth < 1200;
        let area = isMobile ? ['96%', '92%'] : ['1100px', '82%'];
        layer.open({
            id: 'blogTplSetting',
            title: '博客模板配置',
            type: 2,
            area: area,
            skin: 'dc-layer-modern',
            content: '?action=blog_setting_page&tpl=' + encodeURIComponent(tpl.tplfile) + '&_t=' + Date.now(),
            fixed: false,
            scrollbar: false,
            maxmin: true,
            shadeClose: true
        });
    }

    function handleBlogTplDel(data) {
        layer.confirm('确定卸载该博客模板？', {
            btn: ['确认卸载', '取消'],
            icon: 3,
            title: '温馨提示'
        }, function(index) {
            layer.close(index);
            var loadSwitch = layer.load(2);
            $.ajax({
                url: '?action=blog_del',
                type: 'POST',
                dataType: 'json',
                data: { ids: data.tplfile, token: '<?= LoginAuth::genToken() ?>' },
                success: function(e) {
                    if (e.code == 400) return layer.msg(e.msg);
                    layer.msg('模板已卸载');
                    layui.table.reload('blogTplTable');
                    if (window._tabCardViews && window._tabCardViews.blog && window._tabCardViews.blog.currentView === 'card') {
                        window.loadTabCards('blog', function(){ window.renderTabCardPagination('blog'); });
                    }
                },
                error: function(err) {
                    layer.msg(err.responseJSON ? err.responseJSON.msg : '卸载失败');
                },
                complete: function() {
                    layer.close(loadSwitch);
                }
            });
        });
    }

    function handleBlogTplUpdate(data) {
        var loadSwitch = layer.load(2);
        $.ajax({
            url: '?action=blog_upgrade',
            type: 'POST',
            dataType: 'json',
            data: { plugin_id: data.id, alias: data.tplfile, token: '<?= LoginAuth::genToken() ?>' },
            success: function(e) {
                if (e.code == 400) return layer.msg(e.msg);
                layer.msg('模板已升级至最新版');
                layui.table.reload('blogTplTable');
                if (window._tabCardViews && window._tabCardViews.blog && window._tabCardViews.blog.currentView === 'card') {
                    window.loadTabCards('blog', function(){ window.renderTabCardPagination('blog'); });
                }
            },
            error: function(err) {
                layer.msg(err.responseJSON ? err.responseJSON.msg : '更新失败');
            },
            complete: function() {
                layer.close(loadSwitch);
            }
        });
    }

</script>
<script>
    $(function(){
        var table = layui.table;
        var form = layui.form;

        // 用户后台模板 - PC端开关
        form.on('switch(userTplSwitch)', function(obj){
            var active = obj.elem.checked ? 1 : 0;
            var tpl = active == 1 ? this.name : '';
            var loadSwitch = layer.load(2);
            $.ajax({
                url: '?action=user_use',
                type: 'POST',
                dataType: 'json',
                data: { tpl: tpl, token: '<?= LoginAuth::genToken() ?>' },
                success: function(e) {
                    if (e.code == 400) return layer.msg(e.msg);
                    layer.msg('模板配置已更新');
                    table.reload('userTplTable');
                },
                error: function(err) {
                    layer.msg(err.responseJSON ? err.responseJSON.msg : '操作失败');
                },
                complete: function() {
                    layer.close(loadSwitch);
                }
            });
        });

        // 用户后台模板 - 手机端开关
        form.on('switch(userTplTelSwitch)', function(obj){
            var active = obj.elem.checked ? 1 : 0;
            var tpl = active == 1 ? this.name : '';
            var loadSwitch = layer.load(2);
            $.ajax({
                url: '?action=user_use_tel',
                type: 'POST',
                dataType: 'json',
                data: { tpl: tpl, token: '<?= LoginAuth::genToken() ?>' },
                success: function(e) {
                    if (e.code == 400) return layer.msg(e.msg);
                    layer.msg('模板配置已更新');
                    table.reload('userTplTable');
                },
                error: function(err) {
                    layer.msg(err.responseJSON ? err.responseJSON.msg : '操作失败');
                },
                complete: function() {
                    layer.close(loadSwitch);
                }
            });
        });

        // 用户后台模板 - 行操作事件
        table.on('tool(userTplTable)', function(obj){
            var data = obj.data;
            if (obj.event == 'setting') {
                let isMobile = window.innerWidth < 1200;
                let area = isMobile ? ['100%', '100%'] : ['1000px', '80%'];
                layer.open({
                    id: 'userTplSetting',
                    title: '用户后台模板配置',
                    type: 2,
                    area: area,
                    skin: 'dc-layer-modern',
                    content: '?action=user_setting_page&tpl=' + encodeURIComponent(data.tplfile) + '&_t=' + Date.now(),
                    fixed: false,
                    scrollbar: false,
                    maxmin: true,
                    shadeClose: true
                });
                return;
            }
            if (obj.event == 'del') {
                layer.confirm('确定卸载该模板？', {
                    btn: ['确认卸载', '取消'],
                    icon: 3,
                    title: '温馨提示'
                }, function(index) {
                    layer.close(index);
                    var loadSwitch = layer.load(2);
                    $.ajax({
                        url: '?action=user_del',
                        type: 'POST',
                        dataType: 'json',
                        data: { ids: data.tplfile, token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e) {
                            if (e.code == 400) return layer.msg(e.msg);
                            layer.msg('模板已卸载');
                            table.reload('userTplTable');
                        },
                        error: function(err) {
                            layer.msg(err.responseJSON ? err.responseJSON.msg : '卸载失败');
                        },
                        complete: function() {
                            layer.close(loadSwitch);
                        }
                    });
                });
            }
            if (obj.event === 'update') {
                var loadSwitch = layer.load(2);
                $.ajax({
                    url: '?action=user_upgrade',
                    type: 'POST',
                    dataType: 'json',
                    data: { plugin_id: data.id, alias: data.tplfile, token: '<?= LoginAuth::genToken() ?>' },
                    success: function(e) {
                        if (e.code == 400) return layer.msg(e.msg);
                        layer.msg('模板已升级至最新版');
                        table.reload('userTplTable');
                    },
                    error: function(err) {
                        layer.msg(err.responseJSON ? err.responseJSON.msg : '更新失败');
                    },
                    complete: function() {
                        layer.close(loadSwitch);
                    }
                });
            }
            if (obj.event === 'detail') {
                showUserTplDetail(data);
            }
        });

    });

    // 用户后台模板 - 详情弹窗（与首页模板弹窗统一结构）
    function showUserTplDetail(tpl) {
        var coverHtml = tpl.preview && tpl.preview.indexOf('null.png') === -1 && tpl.preview.indexOf('theme.png') === -1
            ? '<img src="' + escapeHtml(tpl.preview) + '" onerror="this.style.display=\'none\'">'
            : '<i class="ri-layout-4-line default-icon"></i>';

        // 电脑端状态
        var pcStatusHtml = tpl.switch === 'y'
            ? '<div class="tpl-detail-status-item on" onclick="toggleUserTplStatus(\'' + tpl.tplfile + '\', \'pc\', 0)"><i class="ri-computer-line"></i> 电脑端 <span style="font-size:11px;opacity:0.8;">(点击关闭)</span></div>'
            : '<div class="tpl-detail-status-item off" onclick="toggleUserTplStatus(\'' + tpl.tplfile + '\', \'pc\', 1)"><i class="ri-computer-line"></i> 电脑端 <span style="font-size:11px;opacity:0.8;">(点击启用)</span></div>';

        // 手机端状态
        var telStatusHtml = tpl.tel_switch === 'y'
            ? '<div class="tpl-detail-status-item on" onclick="toggleUserTplStatus(\'' + tpl.tplfile + '\', \'tel\', 0)"><i class="ri-smartphone-line"></i> 手机端 <span style="font-size:11px;opacity:0.8;">(点击关闭)</span></div>'
            : '<div class="tpl-detail-status-item off" onclick="toggleUserTplStatus(\'' + tpl.tplfile + '\', \'tel\', 1)"><i class="ri-smartphone-line"></i> 手机端 <span style="font-size:11px;opacity:0.8;">(点击启用)</span></div>';

        // 授权状态
        var licenseHtml = (function(){
            if (tpl.license_status === 'blocked') {
                return '<span style="color:#333;"><i class="ri-forbid-line"></i> 已被禁用</span>';
            } else if (tpl.license_status === 'unauthorized') {
                return '<span style="color:#f59e0b;"><i class="ri-lock-line"></i> 未授权</span>';
            } else if (tpl.license_status === 'expired') {
                return '<span style="color:#ef4444;"><i class="ri-error-warning-line"></i> 已到期</span>';
            } else if (tpl.license_status === 'trial' || tpl.buy_type === 'trial') {
                return '<span style="color:#9333ea;"><i class="ri-gift-line"></i> 试用中</span>';
            } else if (tpl.buy_type === 'permanent') {
                return '<span style="color:#16a34a;"><i class="ri-shield-check-line"></i> 已买断</span>';
            } else if (tpl.buy_type === 'monthly' && tpl.expire_time) {
                var now = new Date();
                var expireDate = new Date(tpl.expire_time);
                if (expireDate > now) {
                    return '<span style="color:#f59e0b;"><i class="ri-time-line"></i> 到期：' + tpl.expire_time.split(' ')[0] + '</span>';
                } else {
                    return '<span style="color:#ef4444;"><i class="ri-error-warning-line"></i> 已到期</span>';
                }
            } else {
                return '<span><i class="ri-infinity-line"></i> 永久可用</span>';
            }
        })();

        var actionsHtml = '';
        if (tpl.has_setting === 'y') {
            actionsHtml += '<button class="layui-btn layui-bg-blue" onclick="openUserTplSetting(window._currentUserTpl)"><i class="ri-settings-3-line"></i> 配置</button>';
        }
        actionsHtml += '<button class="layui-btn layui-bg-red" onclick="var t = window._currentUserTpl; closeUserTplDetail(); handleUserTplDel(t);"><i class="ri-delete-bin-line"></i> 卸载</button>';
        if (tpl.update === 'y') {
            actionsHtml += '<button class="layui-btn layui-bg-green" onclick="var t = window._currentUserTpl; closeUserTplDetail(); handleUserTplUpdate(t);"><i class="ri-refresh-line"></i> 更新</button>';
        }

        var html = '<div class="tpl-detail-modal user-tpl-detail-modal" onclick="closeUserTplDetail()">' +
            '<div class="tpl-detail-box" onclick="event.stopPropagation()">' +
                '<div class="tpl-detail-header">' + coverHtml +
                    '<button class="tpl-detail-close" onclick="closeUserTplDetail()"><i class="ri-close-line"></i></button>' +
                '</div>' +
                '<div class="tpl-detail-body">' +
                    '<div class="tpl-detail-title">' + escapeHtml(tpl.tplname) + '</div>' +
                    '<div class="tpl-detail-meta">' +
                        '<span><i class="ri-user-line"></i> ' + escapeHtml(tpl.author || '未知') + '</span>' +
                        '<span><i class="ri-price-tag-3-line"></i> v' + escapeHtml(tpl.version || '1.0.0') + '</span>' +
                        licenseHtml +
                    '</div>' +
                    '<div class="tpl-detail-status">' + pcStatusHtml + telStatusHtml + '</div>' +
                    '<div class="tpl-detail-desc">' + escapeHtml(tpl.tpldes || '暂无描述') + '</div>' +
                    '<div class="tpl-detail-actions">' + actionsHtml + '</div>' +
                '</div>' +
            '</div>' +
        '</div>';

        window._currentUserTpl = tpl;
        $('body').append(html);
    }

    function closeUserTplDetail() {
        $('.user-tpl-detail-modal').remove();
        window._currentUserTpl = null;
    }

    function toggleUserTplStatus(tplfile, type, newActive) {
        var tpl = newActive == 1 ? tplfile : '';
        var url = type === 'tel' ? '?action=user_use_tel' : '?action=user_use';
        var loadSwitch = layer.load(2);
        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: { tpl: tpl, token: '<?= LoginAuth::genToken() ?>' },
            success: function(e) {
                if (e.code == 400) return layer.msg(e.msg);
                layer.msg('模板配置已更新');
                closeUserTplDetail();
                layui.table.reload('userTplTable');
                // 卡片视图下同步刷新
                if (window._tabCardViews && window._tabCardViews.user && window._tabCardViews.user.currentView === 'card') {
                    window.loadTabCards('user', function(){ window.renderTabCardPagination('user'); });
                }
            },
            error: function(err) {
                layer.msg(err.responseJSON ? err.responseJSON.msg : '操作失败');
            },
            complete: function() {
                layer.close(loadSwitch);
            }
        });
    }

    function openUserTplSetting(tpl) {
        if (!tpl || tpl.has_setting !== 'y') {
            return;
        }
        let isMobile = window.innerWidth < 1200;
        let area = isMobile ? ['100%', '100%'] : ['1000px', '80%'];
        layer.open({
            id: 'userTplSetting',
            title: '用户后台模板配置',
            type: 2,
            area: area,
            skin: 'dc-layer-modern',
            content: '?action=user_setting_page&tpl=' + encodeURIComponent(tpl.tplfile) + '&_t=' + Date.now(),
            fixed: false,
            scrollbar: false,
            maxmin: true,
            shadeClose: true
        });
    }

    function handleUserTplDel(data) {
        layer.confirm('确定卸载该模板？', {
            btn: ['确认卸载', '取消'],
            icon: 3,
            title: '温馨提示'
        }, function(index) {
            layer.close(index);
            var loadSwitch = layer.load(2);
            $.ajax({
                url: '?action=user_del',
                type: 'POST',
                dataType: 'json',
                data: { ids: data.tplfile, token: '<?= LoginAuth::genToken() ?>' },
                success: function(e) {
                    if (e.code == 400) return layer.msg(e.msg);
                    layer.msg('模板已卸载');
                    layui.table.reload('userTplTable');
                },
                error: function(err) {
                    layer.msg(err.responseJSON ? err.responseJSON.msg : '卸载失败');
                },
                complete: function() {
                    layer.close(loadSwitch);
                }
            });
        });
    }

    function handleUserTplUpdate(data) {
        var loadSwitch = layer.load(2);
        $.ajax({
            url: '?action=user_upgrade',
            type: 'POST',
            dataType: 'json',
            data: { plugin_id: data.id, alias: data.tplfile, token: '<?= LoginAuth::genToken() ?>' },
            success: function(e) {
                if (e.code == 400) return layer.msg(e.msg);
                layer.msg('模板已升级至最新版');
                layui.table.reload('userTplTable');
                if (window._tabCardViews && window._tabCardViews.user && window._tabCardViews.user.currentView === 'card') {
                    window.loadTabCards('user', function(){ window.renderTabCardPagination('user'); });
                }
            },
            error: function(err) {
                layer.msg(err.responseJSON ? err.responseJSON.msg : '更新失败');
            },
            complete: function() {
                layer.close(loadSwitch);
            }
        });
    }
</script>

<script>
    // Tab 切换
    $(document).on('click', '.order-tabs-wrapper .layui-tabs-header li a', function(e){
        var tabId = $(this).data('tab');
        if (!tabId) return; // 无 data-tab 属性的链接（如应用商店）正常跳转
        e.preventDefault();
        var $li = $(this).parent();
        $li.addClass('layui-this').siblings().removeClass('layui-this');
        $('.tpl-tab-panel').hide();
        $('#tab-' + tabId).show();
        // 更新URL参数
        var url = './template.php?tab=' + tabId;
        history.pushState(null, '', url);
        if (tabId === 'bottom_nav' && typeof window.initBottomNavTplTable === 'function') {
            window.initBottomNavTplTable();
        }
        if (tabId === 'blog' && typeof window.initBlogTplTable === 'function') {
            window.initBlogTplTable();
        }
        // 懒加载：首次切换到用户后台tab时初始化表格
        if (tabId === 'user' && typeof window.initUserTplTable === 'function') {
            window.initUserTplTable();
        }
        // 切换 tab 时，如果该 tab 记忆了卡片视图则自动切换
        if (window._tabCardViews && window._tabCardViews[tabId]) {
            var s = window._tabCardViews[tabId];
            var cfg = { home: {l:'#tpl-index-container',c:'#tpl-cards-container'}, user: {l:'#userTpl-index-container',c:'#userTpl-cards-container'}, bottom_nav: {l:'#bottomNav-index-container',c:'#bottomNav-cards-container'}, blog: {l:'#blogTpl-index-container',c:'#blogTpl-cards-container'} };
            var cc = cfg[tabId];
            if (cc && s.currentView === 'card') {
                $(cc.l).addClass('hidden');
                $(cc.c).addClass('active');
                if (s.allData.length === 0) {
                    window.loadTabCards(tabId, function(){ window.renderTabCardPagination(tabId); });
                } else {
                    window.renderTabCardPagination(tabId);
                }
            }
        }
    });

    // 获取当前可见 tab 名称
    function getCurrentVisibleTab() {
        if ($('#tab-user').is(':visible')) return 'user';
        if ($('#tab-bottom_nav').is(':visible')) return 'bottom_nav';
        if ($('#tab-blog').is(':visible')) return 'blog';
        return 'home';
    }
    // tab 对应的列表表格ID
    var tabTableIdMap = { home: 'index', user: 'userTplTable', bottom_nav: 'bottomNavTplTable', blog: 'blogTplTable' };

    // 全局搜索逻辑
    function doGlobalTplSearch() {
        var keyword = $('#tpl-search').val().trim();
        var tab = getCurrentVisibleTab();
        var tableId = tabTableIdMap[tab];

        // 卡片视图下的搜索
        if (window._tabCardViews && window._tabCardViews[tab] && window._tabCardViews[tab].currentView === 'card') {
            window.doTplCardFilter(keyword);
            return;
        }
        layui.table.reload(tableId, { page: { curr: 1 }, where: { keyword: keyword } });
    }
    $(document).on('click', '#tpl-search-btn', function(){ doGlobalTplSearch(); });
    $(document).on('click', '#tpl-reset-btn', function(){
        var $icon = $(this).find('i');
        $icon.css('transition','transform 0.5s').css('transform','rotate(360deg)');
        setTimeout(function(){ $icon.css('transition','none').css('transform',''); }, 500);
        $('#tpl-search').val('');
        var tab = getCurrentVisibleTab();
        if (window._tabCardViews && window._tabCardViews[tab] && window._tabCardViews[tab].currentView === 'card') {
            window._tabCardViews[tab].allData = [];
            window._tabCardViews[tab].filteredData = [];
            window.loadTabCards(tab, function(){ window.renderTabCardPagination(tab); });
        } else {
            doGlobalTplSearch();
        }
    });
    $(document).on('keypress', '#tpl-search', function(e){
        if (e.which === 13) { doGlobalTplSearch(); e.preventDefault(); }
    });

    // URL ?search= 参数自动搜索（从商店安装成功跳转过来）
    (function() {
        var urlSearch = new URLSearchParams(window.location.search).get('search');
        if (urlSearch) {
            $('#tpl-search').val(urlSearch);
            setTimeout(function() { doGlobalTplSearch(); }, 500);
            // 清除 search 参数，保留 tab 参数，避免刷新后重复搜索
            if (window.history.replaceState) {
                var params = new URLSearchParams(window.location.search);
                params.delete('search');
                var cleanUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                window.history.replaceState(null, '', cleanUrl);
            }
        }
    })();
</script>

<script>
    // 显示模板详情弹窗
    function showTplDetail(tpl) {
        var coverHtml = tpl.preview && tpl.preview.indexOf('null.png') === -1 && tpl.preview.indexOf('theme.png') === -1
            ? '<img src="' + escapeHtml(tpl.preview) + '" onerror="this.style.display=\'none\'">'
            : '<i class="ri-layout-4-line default-icon"></i>';

        // 电脑端状态
        var pcStatusHtml = tpl.switch === 'y'
            ? '<div class="tpl-detail-status-item on" onclick="toggleTplStatus(\'' + tpl.tplfile + '\', \'pc\', 0)"><i class="ri-computer-line"></i> 电脑端 <span style="font-size:11px;opacity:0.8;">(点击关闭)</span></div>'
            : '<div class="tpl-detail-status-item off" onclick="toggleTplStatus(\'' + tpl.tplfile + '\', \'pc\', 1)"><i class="ri-computer-line"></i> 电脑端 <span style="font-size:11px;opacity:0.8;">(点击启用)</span></div>';

        // 手机端状态
        var telStatusHtml = tpl.tel_switch === 'y'
            ? '<div class="tpl-detail-status-item on" onclick="toggleTplStatus(\'' + tpl.tplfile + '\', \'tel\', 0)"><i class="ri-smartphone-line"></i> 手机端 <span style="font-size:11px;opacity:0.8;">(点击关闭)</span></div>'
            : '<div class="tpl-detail-status-item off" onclick="toggleTplStatus(\'' + tpl.tplfile + '\', \'tel\', 1)"><i class="ri-smartphone-line"></i> 手机端 <span style="font-size:11px;opacity:0.8;">(点击启用)</span></div>';

        // 授权状态
        var licenseHtml = (function(){
            if (tpl.license_status === 'blocked') {
                return '<span style="color:#333;"><i class="ri-forbid-line"></i> 已被禁用</span>';
            } else if (tpl.license_status === 'unauthorized') {
                return '<span style="color:#f59e0b;"><i class="ri-lock-line"></i> 未授权</span>';
            } else if (tpl.license_status === 'expired') {
                return '<span style="color:#ef4444;"><i class="ri-error-warning-line"></i> 已到期</span>';
            } else if (tpl.license_status === 'trial' || tpl.buy_type === 'trial') {
                return '<span style="color:#9333ea;"><i class="ri-gift-line"></i> 试用中</span>';
            } else if (tpl.buy_type === 'permanent') {
                return '<span style="color:#16a34a;"><i class="ri-shield-check-line"></i> 已买断</span>';
            } else if (tpl.buy_type === 'monthly' && tpl.expire_time) {
                var now = new Date();
                var expireDate = new Date(tpl.expire_time);
                if (expireDate > now) {
                    return '<span style="color:#f59e0b;"><i class="ri-time-line"></i> 到期：' + tpl.expire_time.split(' ')[0] + '</span>';
                } else {
                    return '<span style="color:#ef4444;"><i class="ri-error-warning-line"></i> 已到期</span>';
                }
            } else {
                return '<span><i class="ri-infinity-line"></i> 永久可用</span>';
            }
        })();

        var actionsHtml = '';
        if (tpl.has_setting === 'y') {
            actionsHtml += '<button class="layui-btn layui-bg-blue" onclick="var t = currentDetailTpl; closeTplDetail(); handleTplAction(\'setting\', t);"><i class="ri-settings-3-line"></i> 配置</button>';
        }
        actionsHtml += '<button class="layui-btn layui-bg-red" onclick="var t = currentDetailTpl; closeTplDetail(); handleTplAction(\'del\', t);"><i class="ri-delete-bin-line"></i> 卸载</button>';
        if (tpl.update === 'y') {
            actionsHtml += '<button class="layui-btn layui-bg-green" onclick="var t = currentDetailTpl; closeTplDetail(); handleTplAction(\'update\', t);"><i class="ri-refresh-line"></i> 更新</button>';
        }

        var html = '<div class="tpl-detail-modal" onclick="closeTplDetail()">' +
            '<div class="tpl-detail-box" onclick="event.stopPropagation()">' +
                '<div class="tpl-detail-header">' + coverHtml +
                    '<button class="tpl-detail-close" onclick="closeTplDetail()"><i class="ri-close-line"></i></button>' +
                '</div>' +
                '<div class="tpl-detail-body">' +
                    '<div class="tpl-detail-title">' + escapeHtml(tpl.tplname) + '</div>' +
                    '<div class="tpl-detail-meta">' +
                        '<span><i class="ri-user-line"></i> ' + escapeHtml(tpl.author || '未知') + '</span>' +
                        '<span><i class="ri-price-tag-3-line"></i> v' + escapeHtml(tpl.version || '1.0.0') + '</span>' +
                        licenseHtml +
                    '</div>' +
                    '<div class="tpl-detail-status">' + pcStatusHtml + telStatusHtml + '</div>' +
                    '<div class="tpl-detail-desc">' + escapeHtml(tpl.tpldes || '暂无描述') + '</div>' +
                    '<div class="tpl-detail-actions">' + actionsHtml + '</div>' +
                '</div>' +
            '</div>' +
        '</div>';

        window.currentDetailTpl = tpl;
        $('body').append(html);
    }

    // 关闭详情弹窗
    function closeTplDetail() {
        $('.tpl-detail-modal').remove();
        window.currentDetailTpl = null;
    }

    // 切换模板状态
    function toggleTplStatus(tplfile, type, newActive) {
        var tpl = newActive == 1 ? tplfile : 'em_null_tpl';
        var url = type === 'tel' ? '?action=use_tel' : '?action=use';
        var loadSwitch = layer.load(2);
        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: { tpl: tpl, token: '<?= LoginAuth::genToken() ?>' },
            success: function(e) {
                if (e.code == 400) return layer.msg(e.msg);
                layer.msg('模板配置已更新');
                closeTplDetail();
                window.table.reload('index');
                // 卡片视图下同步刷新
                if (window.tplCurrentView === 'card' && typeof window.loadTplCards === 'function') {
                    window.loadTplCards(function(){ window.renderTplCardPagination(); });
                }
            },
            error: function(err) {
                layer.msg(err.responseJSON.msg);
            },
            complete: function() {
                layer.close(loadSwitch);
            }
        });
    }

    // 处理模板操作
    function handleTplAction(event, data) {
        if (event === 'setting') {
            let isMobile = window.innerWidth < 1200;
            let area = isMobile ? ['100%', '100%'] : ['1000px', '80%'];
            layer.open({
                id: 'setting',
                title: '配置',
                type: 2,
                area: area,
                skin: 'dc-layer-modern',
                content: '?action=setting_page&tpl=' + encodeURIComponent(data.tplfile) + '&_t=' + Date.now(),
                fixed: false,
                scrollbar: false,
                maxmin: true,
                shadeClose: true
            });
        }

        if (event === 'del') {
            layer.confirm('确定卸载该模板？', {
                btn: ['确认卸载', '取消'],
                icon: 3,
                title: '温馨提示'
            }, function(index) {
                layer.close(index);
                var loadSwitch = layer.load(2);
                $.ajax({
                    url: '?action=del',
                    type: 'POST',
                    dataType: 'json',
                    data: { ids: data.tplfile, token: '<?= LoginAuth::genToken() ?>' },
                    success: function(e) {
                        if (e.code == 400) return layer.msg(e.msg);
                        layer.msg('模板已卸载');
                        window.table.reload('index');
                    },
                    error: function(err) {
                        layer.msg(err.responseJSON.msg);
                    },
                    complete: function() {
                        layer.close(loadSwitch);
                    }
                });
            });
        }

        if (event === 'update') {
            var loadSwitch = layer.load(2);
            $.ajax({
                url: '?action=upgrade',
                type: 'POST',
                dataType: 'json',
                data: { plugin_id: data.id, alias: data.tplfile, token: '<?= LoginAuth::genToken() ?>' },
                success: function(e) {
                    if (e.code == 400) return layer.msg(e.msg);
                    layer.msg('模板已升级至最新版');
                    window.table.reload('index');
                },
                error: function(err) {
                    layer.msg(err.responseJSON.msg);
                },
                complete: function() {
                    layer.close(loadSwitch);
                }
            });
        }
    }

    // HTML转义
    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    // ESC关闭弹窗
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            closeTplDetail();
            if (typeof closeUserTplDetail === 'function') closeUserTplDetail();
            if (typeof closeBottomNavTplDetail === 'function') closeBottomNavTplDetail();
            if (typeof closeBlogTplDetail === 'function') closeBlogTplDetail();
        }
    });
</script>

<script>
    $(function () {
        // ========== 通用卡片视图系统（支持 home / user / bottom_nav / blog 四个 tab） ==========
        var tabConfig = {
            home: {
                listContainer: '#tpl-index-container', cardsContainer: '#tpl-cards-container',
                paginationElem: 'tpl-cards-pagination', paginationContainer: '#tpl-cards-pagination',
                dataUrl: './template.php?action=index&limit=9999', tableId: 'index',
                hasDualSwitch: true, togglePcAction: '?action=use', toggleTelAction: '?action=use_tel',
                emptyTpl: 'em_null_tpl', detailFn: 'showTplDetail',
                settingAction: '?action=setting_page', delAction: '?action=del',
                upgradeAction: '?action=upgrade', hasUpdate: true, hasLicense: true
            },
            user: {
                listContainer: '#userTpl-index-container', cardsContainer: '#userTpl-cards-container',
                paginationElem: 'userTpl-cards-pagination', paginationContainer: '#userTpl-cards-pagination',
                dataUrl: './template.php?action=user_index&limit=9999', tableId: 'userTplTable',
                hasDualSwitch: true, togglePcAction: '?action=user_use', toggleTelAction: '?action=user_use_tel',
                emptyTpl: '', detailFn: 'showUserTplDetail',
                settingAction: '?action=user_setting_page', delAction: '?action=user_del',
                upgradeAction: '?action=user_upgrade', hasUpdate: true, hasLicense: true
            },
            bottom_nav: {
                listContainer: '#bottomNav-index-container', cardsContainer: '#bottomNav-cards-container',
                paginationElem: 'bottomNav-cards-pagination', paginationContainer: '#bottomNav-cards-pagination',
                dataUrl: './template.php?action=bottom_nav_index&limit=9999', tableId: 'bottomNavTplTable',
                hasDualSwitch: false, togglePcAction: '?action=bottom_nav_use',
                emptyTpl: 'em_null_tpl', detailFn: 'showBottomNavTplDetail',
                settingAction: '?action=bottom_nav_setting_page', delAction: '?action=bottom_nav_del',
                upgradeAction: '?action=bottom_nav_upgrade', hasUpdate: true, hasLicense: true
            },
            blog: {
                listContainer: '#blogTpl-index-container', cardsContainer: '#blogTpl-cards-container',
                paginationElem: 'blogTpl-cards-pagination', paginationContainer: '#blogTpl-cards-pagination',
                dataUrl: './template.php?action=blog_index&limit=9999', tableId: 'blogTplTable',
                hasDualSwitch: true, togglePcAction: '?action=blog_use', toggleTelAction: '?action=blog_use_tel',
                emptyTpl: 'em_null_tpl', detailFn: 'showBlogTplDetail',
                settingAction: '?action=blog_setting_page', delAction: '?action=blog_del',
                upgradeAction: '?action=blog_upgrade', hasUpdate: true, hasLicense: true
            }
        };

        // 每个 tab 的卡片视图状态（共享同一个视图偏好）
        // 默认视图：localStorage 已有值时使用历史偏好；否则按视口宽度区分 —— 移动端 (≤768px) 默认卡片视图，PC 端默认列表视图。
        // 用户手动切换后会自动写入 localStorage，下次访问按用户偏好。
        var sharedView = localStorage.getItem('tpl_view') || ((window.matchMedia && window.matchMedia('(max-width: 768px)').matches) ? 'card' : 'list');
        window._tabCardViews = {};
        $.each(tabConfig, function(tab) {
            window._tabCardViews[tab] = {
                currentView: sharedView,
                allData: [], filteredData: [], pageSize: 10, currentPage: 1
            };
        });

        // 兼容旧代码（详情弹窗刷新等）
        window.tplCurrentView = window._tabCardViews.home.currentView;
        window.loadTplCards = function(cb) { window.loadTabCards('home', cb); };
        window.renderTplCardPagination = function() { window.renderTabCardPagination('home'); };

        // 渲染卡片分页
        window.renderTabCardPagination = function(tab) {
            var s = window._tabCardViews[tab], c = tabConfig[tab];
            if (s.allData.length === 0) { renderCards(tab, []); $(c.paginationContainer).hide(); return; }
            layui.use('laypage', function(){
                layui.laypage.render({
                    elem: c.paginationElem, count: s.allData.length, limit: s.pageSize, curr: s.currentPage,
                    limits: [10, 20, 50], layout: ['count', 'prev', 'page', 'next', 'limit', 'skip'],
                    jump: function(obj){
                        s.currentPage = obj.curr; s.pageSize = obj.limit;
                        renderCards(tab, s.allData.slice((obj.curr-1)*obj.limit, obj.curr*obj.limit));
                    }
                });
            });
            $(c.paginationContainer).show();
        };

        // 渲染卡片
        function renderCards(tab, pageData) {
            var s = window._tabCardViews[tab], c = tabConfig[tab];
            var $ct = $(c.cardsContainer);
            if (pageData.length === 0 && s.allData.length === 0) {
                $ct.removeClass('active').css({'display':'flex','justify-content':'center','align-items':'center','min-height':'300px'})
                   .html('<div style="text-align:center;padding:80px 20px;"><div style="color:#999;font-size:14px;">暂无模板</div></div>');
                $(c.paginationContainer).hide(); return;
            }
            $ct.addClass('active').css({'display':'','justify-content':'','align-items':'','min-height':''});
            var html = '';
            pageData.forEach(function(d) {
                var coverImg = d.preview && d.preview.indexOf('null.png') === -1 && d.preview.indexOf('theme.png') === -1
                    ? '<img src="' + d.preview + '" onerror="this.parentNode.innerHTML=\'<i class=\\\'ri-layout-4-line default-icon\\\'></i>\'">'
                    : '<i class="ri-layout-4-line default-icon"></i>';
                var updateBadge = d.update === 'y' && c.hasUpdate ? '<span class="update-dot">有更新</span>' : '';
                var isActive = c.hasDualSwitch ? (d.switch === 'y' || d.tel_switch === 'y') : (d.switch === 'y');
                var cardClass = isActive ? '' : ' disabled';

                // 开关按钮
                var switchesHtml = '';
                if (c.hasDualSwitch) {
                    var pcSw = d.switch === 'y'
                        ? '<span class="tpl-card-switch on" data-event="toggle_pc" data-tpl="'+d.tplfile+'" data-active="1" data-tab="'+tab+'" title="点击关闭电脑端"><i class="ri-toggle-fill"></i> PC</span>'
                        : '<span class="tpl-card-switch off" data-event="toggle_pc" data-tpl="'+d.tplfile+'" data-active="0" data-tab="'+tab+'" title="点击启用电脑端"><i class="ri-toggle-line"></i> PC</span>';
                    var telSw = d.tel_switch === 'y'
                        ? '<span class="tpl-card-switch on" data-event="toggle_tel" data-tpl="'+d.tplfile+'" data-active="1" data-tab="'+tab+'" title="点击关闭手机端"><i class="ri-toggle-fill"></i> 手机</span>'
                        : '<span class="tpl-card-switch off" data-event="toggle_tel" data-tpl="'+d.tplfile+'" data-active="0" data-tab="'+tab+'" title="点击启用手机端"><i class="ri-toggle-line"></i> 手机</span>';
                    switchesHtml = '<div class="tpl-card-switches">' + pcSw + telSw + '</div>';
                } else {
                    var sw = d.switch === 'y'
                        ? '<span class="tpl-card-switch on" data-event="toggle_pc" data-tpl="'+d.tplfile+'" data-active="1" data-tab="'+tab+'" title="点击关闭"><i class="ri-toggle-fill"></i> ON</span>'
                        : '<span class="tpl-card-switch off" data-event="toggle_pc" data-tpl="'+d.tplfile+'" data-active="0" data-tab="'+tab+'" title="点击启用"><i class="ri-toggle-line"></i> OFF</span>';
                    switchesHtml = '<div class="tpl-card-switches">' + sw + '</div>';
                }

                html += '<div class="tpl-card' + cardClass + '" data-tpl=\'' + JSON.stringify(d).replace(/'/g,"&#39;") + '\' data-tab="'+tab+'">';
                html += '  <div class="tpl-card-cover">' + coverImg + switchesHtml + updateBadge + '</div>';
                html += '  <div class="tpl-card-body"><div class="tpl-card-header"><div class="tpl-card-title">' + escapeHtml(d.tplname) + '</div></div>';
                html += '  <div class="tpl-card-desc">' + escapeHtml(d.tpldes || '暂无描述') + '</div></div>';

                // 到期/授权信息
                var expireHtml = '';
                if (c.hasLicense) {
                    if (d.license_status === 'blocked') expireHtml = '<span class="tpl-expire-tag blocked"><i class="ri-forbid-line"></i> 已被禁用</span>';
                    else if (d.license_status === 'unauthorized') expireHtml = '<span class="tpl-expire-tag unauthorized"><i class="ri-lock-line"></i> 未授权</span>';
                    else if (d.license_status === 'expired') expireHtml = '<span class="tpl-expire-tag expired"><i class="ri-error-warning-line"></i> 已到期</span>';
                    else if (d.license_status === 'trial' || d.buy_type === 'trial') expireHtml = '<span class="tpl-expire-tag trial"><i class="ri-gift-line"></i> 试用中</span>';
                    else if (d.buy_type === 'permanent') expireHtml = '<span class="tpl-expire-tag permanent"><i class="ri-shield-check-line"></i> 已买断</span>';
                    else if (d.buy_type === 'monthly' && d.expire_time) {
                        expireHtml = new Date(d.expire_time) > new Date()
                            ? '<span class="tpl-expire-tag monthly"><i class="ri-time-line"></i> ' + d.expire_time.split(' ')[0] + '</span>'
                            : '<span class="tpl-expire-tag expired"><i class="ri-error-warning-line"></i> 已到期</span>';
                    } else expireHtml = '<span class="tpl-expire-tag local"><i class="ri-infinity-line"></i> 永久可用</span>';
                }
                html += '<div class="tpl-card-meta"><span><i class="ri-user-line"></i> ' + escapeHtml(d.author || '未知') + '</span>' + expireHtml + '</div>';
                html += '<div class="tpl-card-actions">';
                if (d.has_setting === 'y') html += '<button class="layui-btn layui-btn-sm layui-bg-blue" data-event="setting">配置</button>';
                html += '<button class="layui-btn layui-btn-sm layui-bg-red" data-event="del">删除</button>';
                if (d.update === 'y' && c.hasUpdate) html += '<button class="layui-btn layui-btn-sm layui-bg-green" data-event="update">更新</button>';
                html += '</div></div>';
            });
            $ct.html(html);
        }

        // 加载卡片数据
        window.loadTabCards = function(tab, callback) {
            var s = window._tabCardViews[tab], c = tabConfig[tab];
            var loadIndex = layer.load(2);
            $.ajax({
                url: c.dataUrl, type: 'GET', dataType: 'json',
                success: function(res) {
                    s.allData = (res.code === 200 || res.code === 0) ? (res.data || []) : (Array.isArray(res) ? res : (res.data || []));
                    s.filteredData = s.allData; s.currentPage = 1;
                    if (callback) callback();
                },
                error: function() { layer.msg('加载失败'); },
                complete: function() { layer.close(loadIndex); }
            });
        };

        // 卡片搜索过滤（当前可见 tab）
        window.doTplCardFilter = function(keyword) {
            var tab = 'home';
            if ($('#tab-user').is(':visible')) tab = 'user';
            else if ($('#tab-bottom_nav').is(':visible')) tab = 'bottom_nav';
            else if ($('#tab-blog').is(':visible')) tab = 'blog';
            var s = window._tabCardViews[tab];
            var filtered = s.filteredData;
            if (keyword) {
                keyword = keyword.toLowerCase();
                filtered = filtered.filter(function(t) {
                    return (t.tplname||'').toLowerCase().indexOf(keyword)>-1 || (t.tpldes||'').toLowerCase().indexOf(keyword)>-1
                        || (t.author||'').toLowerCase().indexOf(keyword)>-1 || (t.tplfile||'').toLowerCase().indexOf(keyword)>-1;
                });
            }
            s.allData = filtered; s.currentPage = 1;
            window.renderTabCardPagination(tab);
        };

        // ========== 页面加载初始化 ==========
        var initTab = '<?= $tab ?>';
        // 初始化每个 tab 的视图按钮状态
        $.each(window._tabCardViews, function(tab, s) {
            $('[data-tab="'+tab+'"].view-btn').removeClass('active');
            $('[data-tab="'+tab+'"][data-view="'+s.currentView+'"]').addClass('active');
        });
        // 初始化 home tab 卡片视图
        if (window._tabCardViews.home.currentView === 'card') {
            $('#tpl-index-container').addClass('hidden');
            $('#tpl-cards-container').addClass('active');
            window.loadTabCards('home', function(){ window.renderTabCardPagination('home'); });
        }
        // 如果打开的是 user / bottom_nav / blog 且为卡片视图
        if ((initTab === 'user' || initTab === 'bottom_nav' || initTab === 'blog') && window._tabCardViews[initTab].currentView === 'card') {
            if (initTab === 'user' && window.initUserTplTable) window.initUserTplTable();
            if (initTab === 'bottom_nav' && window.initBottomNavTplTable) window.initBottomNavTplTable();
            if (initTab === 'blog' && window.initBlogTplTable) window.initBlogTplTable();
            $(tabConfig[initTab].listContainer).addClass('hidden');
            $(tabConfig[initTab].cardsContainer).addClass('active');
            window.loadTabCards(initTab, function(){ window.renderTabCardPagination(initTab); });
        }

        // ========== 视图切换（所有 tab 同步） ==========
        $(document).on('click', '.view-btn', function() {
            var view = $(this).data('view');
            var clickedTab = $(this).data('tab');
            if (!clickedTab || !tabConfig[clickedTab]) return;
            if (view === window._tabCardViews[clickedTab].currentView) return;

            // 保存统一偏好
            localStorage.setItem('tpl_view', view);
            window.tplCurrentView = view;

            // 同步所有 tab
            $.each(tabConfig, function(tab, c) {
                var s = window._tabCardViews[tab];
                s.currentView = view;

                // 更新按钮状态
                $('[data-tab="'+tab+'"].view-btn').removeClass('active');
                $('[data-tab="'+tab+'"][data-view="'+view+'"]').addClass('active');

                if (view === 'card') {
                    $(c.listContainer).addClass('hidden');
                    $(c.cardsContainer).addClass('active');
                    if (tab === 'user' && window.initUserTplTable) window.initUserTplTable();
                    if (tab === 'bottom_nav' && window.initBottomNavTplTable) window.initBottomNavTplTable();
                    if (tab === 'blog' && window.initBlogTplTable) window.initBlogTplTable();
                    // 仅当前可见 tab 立即加载数据，其他 tab 切换时懒加载
                    if ($('#tab-' + tab).is(':visible')) {
                        if (s.allData.length === 0) {
                            window.loadTabCards(tab, function(){ window.renderTabCardPagination(tab); });
                        } else {
                            window.renderTabCardPagination(tab);
                        }
                    }
                } else {
                    $(c.listContainer).removeClass('hidden');
                    $(c.cardsContainer).removeClass('active');
                    $(c.paginationContainer).hide();
                }
            });
        });

        // ========== 卡片事件委托 ==========
        // 点击封面弹出详情
        $(document).on('click', '.tpl-card-cover', function(e) {
            if ($(e.target).closest('[data-event^="toggle_"]').length) return;
            var card = $(this).closest('.tpl-card');
            var data = card.data('tpl'), tab = card.data('tab');
            var c = tabConfig[tab];
            if (c && c.detailFn && typeof window[c.detailFn] === 'function') window[c.detailFn](data);
        });

        // 开关点击
        $(document).on('click', '.tpl-card-switch[data-event^="toggle_"]', function(e) {
            e.stopPropagation();
            var $this = $(this), eventType = $this.data('event'), tplfile = $this.data('tpl'), tab = $this.data('tab');
            var c = tabConfig[tab]; if (!c) return;
            var currentActive = parseInt($this.data('active')), newActive = currentActive === 1 ? 0 : 1;
            var tpl = newActive === 1 ? tplfile : c.emptyTpl;
            var url = (c.hasDualSwitch && eventType === 'toggle_tel') ? c.toggleTelAction : c.togglePcAction;
            var loadSwitch = layer.load(2);
            $.ajax({
                url: url, type: 'POST', dataType: 'json',
                data: { tpl: tpl, token: '<?= LoginAuth::genToken() ?>' },
                success: function(e) {
                    if (e.code == 400) return layer.msg(e.msg);
                    layer.msg('模板配置已更新');
                    window.loadTabCards(tab, function(){ window.renderTabCardPagination(tab); });
                    layui.table.reload(c.tableId);
                },
                error: function(err) { layer.msg(err.responseJSON.msg); },
                complete: function() { layer.close(loadSwitch); }
            });
        });

        // 操作按钮（配置、删除、更新）
        $(document).on('click', '.tpl-card [data-event]', function(e) {
            e.stopPropagation();
            var event = $(this).data('event');
            if (event === 'toggle_pc' || event === 'toggle_tel') return;
            var card = $(this).closest('.tpl-card'), data = card.data('tpl'), tab = card.data('tab');
            var c = tabConfig[tab]; if (!c) return;

            if (event === 'setting') {
                var isMobile = window.innerWidth < 1200;
                var isBlogSetting = tab === 'blog';
                var isBottomNav = tab === 'bottom_nav';
                var area = isBlogSetting ? (isMobile ? ['96%', '92%'] : ['1100px', '82%'])
                         : isBottomNav ? (isMobile ? ['100%', '100%'] : ['1000px', '80%'])
                         : (isMobile ? ['100%', '100%'] : ['1000px', '80%']);
                var layerId = isBlogSetting ? 'blogTplSetting' : (isBottomNav ? 'bottomNavSetting' : 'setting');
                var layerTitle = isBlogSetting ? '博客模板配置' : (isBottomNav ? '底部导航配置' : '配置');
                layer.open({ id: layerId, title: layerTitle, type:2, area:area, skin:'dc-layer-modern',
                    content: c.settingAction + '&tpl=' + encodeURIComponent(data.tplfile) + '&_t=' + Date.now(), fixed:false, scrollbar:false, maxmin:true, shadeClose:true,
                    success: isBottomNav ? function(layero, index){ layer.full(index); } : undefined });
            }
            if (event === 'del') {
                layer.confirm('确定删除该模板？', { btn:['确认删除','取消'], icon:3, title:'温馨提示' }, function(index) {
                    layer.close(index);
                    var loadSwitch = layer.load(2);
                    $.ajax({
                        url: c.delAction, type:'POST', dataType:'json',
                        data: { ids: data.tplfile, token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e) {
                            if (e.code == 400) return layer.msg(e.msg);
                            layer.msg('模板已删除');
                            window.loadTabCards(tab, function(){ window.renderTabCardPagination(tab); });
                            layui.table.reload(c.tableId);
                        },
                        error: function(err) { layer.msg(err.responseJSON ? err.responseJSON.msg : '删除失败'); },
                        complete: function() { layer.close(loadSwitch); }
                    });
                });
            }
            if (event === 'update' && c.hasUpdate) {
                var loadSwitch = layer.load(2);
                $.ajax({
                    url: c.upgradeAction, type:'POST', dataType:'json',
                    data: { plugin_id: data.id, alias: data.tplfile, token: '<?= LoginAuth::genToken() ?>' },
                    success: function(e) {
                        if (e.code == 400) return layer.msg(e.msg);
                        layer.msg('模板已升级至最新版');
                        window.loadTabCards(tab, function(){ window.renderTabCardPagination(tab); });
                        layui.table.reload(c.tableId);
                    },
                    error: function(err) { layer.msg(err.responseJSON ? err.responseJSON.msg : '更新失败'); },
                    complete: function() { layer.close(loadSwitch); }
                });
            }
        });
    });
</script>

