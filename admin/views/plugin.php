<?php defined('DC_ROOT') || exit('access denied!'); ?>

<style>
/* 修复 layui 弹窗关闭按钮颜色 */
.layui-layer-setwin .layui-layer-close1 {
    color: #999 !important;
}
.layui-layer-setwin .layui-layer-close1:hover {
    color: #333 !important;
}

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

/* 移动端适配 */
@media (max-width: 768px) {
    .view-switch {
        margin-left: auto;
        margin-top: 10px;
    }
    .view-switch .view-btn {
        padding: 5px 10px;
        font-size: 12px;
    }
    .plugin-cards.active {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    .plugin-card-cover {
        aspect-ratio: 1 / 1;
    }
    .plugin-card-body {
        padding: 10px 12px;
    }
    .plugin-card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
    .plugin-card-title {
        font-size: 14px;
    }
    .plugin-card-info {
        font-size: 11px;
    }
    .plugin-card-desc {
        font-size: 12px;
        height: 36px;
    }
    .plugin-card-actions {
        padding: 0 12px 12px;
        flex-wrap: wrap;
    }
    .plugin-card-actions .layui-btn {
        flex: 1 1 auto;
        min-width: 60px;
        height: 32px;
        line-height: 32px;
        padding: 0 10px;
        font-size: 13px;
    }
    .plugin-card-switch {
        padding: 0px 8px;
        font-size: 13px;
        border-radius: 10px;
    }
    .plugin-card-switch i {
        font-size: 24px;
    }
}

/* 卡片视图样式 */
.plugin-cards {
    display: none;
}
.plugin-cards.active {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 20px;
}
@media (max-width: 1400px) {
    .plugin-cards.active {
        grid-template-columns: repeat(4, 1fr);
    }
}
@media (max-width: 1100px) {
    .plugin-cards.active {
        grid-template-columns: repeat(3, 1fr);
    }
}
@media (max-width: 768px) {
    .plugin-cards.active {
        grid-template-columns: repeat(2, 1fr);
    }
}
.plugin-card {
    background: #ffffff85;
    border: 1px solid #eef1f4;
    border-radius: 6px;
    overflow: hidden;
    transition: all 0.3s;
}
.plugin-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
}
.plugin-card-cover {
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
.plugin-card-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.plugin-card-cover .default-icon {
    font-size: 64px;
    color: rgba(255,255,255,0.8);
}
.plugin-card-body {
    padding: 15px;
}
.plugin-card-body {
    padding: 5px 10px 0px 10px;
}
.plugin-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}
.plugin-card-title {
    font-size: 15px;
    font-weight: 600;
    color: #333;
}
.plugin-card-info {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: #999;
}
.plugin-card-info span {
    white-space: nowrap;
}
.plugin-card-desc {
    font-size: 13px;
    color: #666;
    line-height: 1.5;
    height: 60px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
}
.plugin-card-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 12px;
    color: #999;
    padding: 3px 10px 5px;
}
/* 到期时间标签 */
.plugin-expire-tag {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 4px;
}
.plugin-expire-tag.permanent {
    background: rgba(22, 163, 74, 0.1);
    color: #16a34a;
}
.plugin-expire-tag.monthly {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}
.plugin-expire-tag.local {
    background: rgba(153, 153, 153, 0.1);
    color: #999;
}
.plugin-expire-tag.expired {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}
.plugin-expire-tag.blocked {
    background: rgba(51, 51, 51, 0.1);
    color: #333;
}
.plugin-expire-tag.unauthorized {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}
.plugin-expire-tag.trial {
    background: rgba(147, 51, 234, 0.1);
    color: #9333ea;
}
/* 深色模式 */
html[data-theme="dark"] .plugin-expire-tag.permanent {
    background: rgba(22, 163, 74, 0.2);
}
html[data-theme="dark"] .plugin-expire-tag.monthly {
    background: rgba(245, 158, 11, 0.2);
}
html[data-theme="dark"] .plugin-expire-tag.local {
    background: rgba(153, 153, 153, 0.2);
}
html[data-theme="dark"] .plugin-expire-tag.expired {
    background: rgba(239, 68, 68, 0.2);
}
html[data-theme="dark"] .plugin-expire-tag.blocked {
    background: rgba(51, 51, 51, 0.3);
    color: #999;
}
html[data-theme="dark"] .plugin-expire-tag.unauthorized {
    background: rgba(245, 158, 11, 0.2);
}
html[data-theme="dark"] .plugin-expire-tag.trial {
    background: rgba(147, 51, 234, 0.2);
}

/* 深色模式适配 - 分页器 */
html[data-theme="dark"] .layui-laypage a,
html[data-theme="dark"] .layui-laypage span {
    color: #b0b0b0 !important;
    background-color: #2a2a2a !important;
    border-color: #444 !important;
}
html[data-theme="dark"] .layui-laypage a:hover {
    color: #16baaa !important;
}
html[data-theme="dark"] .layui-laypage .layui-laypage-curr .layui-laypage-em {
    background-color: #16baaa !important;
}
html[data-theme="dark"] .layui-laypage .layui-laypage-curr span,
html[data-theme="dark"] .layui-laypage .layui-laypage-curr em {
    color: #fff !important;
    background-color: transparent !important;
}
html[data-theme="dark"] .layui-laypage input,
html[data-theme="dark"] .layui-laypage select {
    background-color: #2a2a2a !important;
    border-color: #444 !important;
    color: #e0e0e0 !important;
}
html[data-theme="dark"] .layui-laypage .layui-laypage-count,
html[data-theme="dark"] .layui-laypage .layui-laypage-limits,
html[data-theme="dark"] .layui-laypage .layui-laypage-skip {
    color: #b0b0b0 !important;
    background-color: transparent !important;
}
html[data-theme="dark"] .layui-laypage .layui-laypage-btn {
    background-color: #2a2a2a !important;
    border-color: #444 !important;
    color: #b0b0b0 !important;
}
html[data-theme="dark"] .layui-laypage .layui-laypage-btn:hover {
    color: #16baaa !important;
}
html[data-theme="dark"] #cards-pagination {
    background-color: transparent !important;
}

/* 封面上的开关按钮 */
.plugin-card-switch {
    position: absolute;
    top: 10px;
    left: 10px;
    padding: 0px 10px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
    font-size: 15px;
    font-weight: 700;
    transition: all 0.2s;
    z-index: 2;
}
.plugin-card-switch i {
    font-size: 30px;
}
.plugin-card-switch.on {
    background: rgba(34, 197, 94, 0.9);
    color: #fff;
}
.plugin-card-switch.off {
    background: rgba(0, 0, 0, 0.5);
    color: rgba(255, 255, 255, 0.9);
}
.plugin-card-switch:hover {
    transform: scale(1.05);
}
/* 关闭状态的卡片整体变灰 */
.plugin-card.disabled {
    opacity: 0.7;
}
.plugin-card.disabled .plugin-card-cover {
    filter: grayscale(50%);
}
.plugin-card-actions {
    display: flex;
    gap: 8px;
    padding: 0 15px 15px;
}
.plugin-card-actions .layui-btn {
    flex: 1;
    margin: 0;
}
.plugin-card .update-dot {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #ff5722;
    color: #fff;
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 10px;
}

/* 列表视图隐藏 */
#index-container.hidden {
    display: none;
}

/* 插件详情弹窗 */
.plugin-detail-modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 19891015; display: flex; align-items: center; justify-content: center; padding: 20px; }
.plugin-detail-box { background: #fff; border-radius: 12px; max-width: 500px; width: 100%; max-height: 80vh; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
.plugin-detail-header { position: relative; aspect-ratio: 1 / 1; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; }
.plugin-detail-header img { width: 100%; height: 100%; object-fit: cover; }
.plugin-detail-header .default-icon { font-size: 80px; color: rgba(255,255,255,0.8); }
.plugin-detail-close { position: absolute; top: 10px; right: 10px; width: 32px; height: 32px; background: rgba(0,0,0,0.3); border: none; border-radius: 50%; color: #fff; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
.plugin-detail-close:hover { background: rgba(0,0,0,0.5); }
.plugin-detail-body { padding: 20px; max-height: 50vh; overflow-y: auto; }
.plugin-detail-title { font-size: 20px; font-weight: 600; margin-bottom: 12px; color: #333; }
.plugin-detail-meta { display: flex; gap: 15px; margin-bottom: 15px; font-size: 13px; color: #999; flex-wrap: wrap; }
.plugin-detail-meta i { margin-right: 4px; }
.plugin-detail-desc { color: #666; line-height: 1.8; margin-bottom: 20px; font-size: 14px; }
.plugin-detail-status { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 20px; font-size: 13px; margin-bottom: 20px; }
.plugin-detail-status.on { background: #dcfce7; color: #16a34a; }
.plugin-detail-status.off { background: #f3f4f6; color: #6b7280; }
.plugin-detail-actions { display: flex; gap: 10px; flex-wrap: wrap; }
.plugin-detail-actions .layui-btn { flex: 1; height: 42px; line-height: 32px; font-size: 14px; min-width: 80px; }

/* 分类标签 - Mac 风格胶囊 */
.plugin-category-bar { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
.plugin-category-tabs { display: inline-flex; flex-wrap: wrap; gap: 2px; padding: 3px; background: #f0f1f4; border-radius: 8px; }
.plugin-category-tabs .cat-item { padding: 5px 14px; border-radius: 6px; cursor: pointer; font-size: 13px; color: #666; background: transparent; border: none; transition: all 0.2s; white-space: nowrap; }
.plugin-category-tabs .cat-item:hover { color: #333; background: rgba(255,255,255,.5); }
.plugin-category-tabs .cat-item.active { color: #333; font-weight: 500; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.08), 0 0 0 0.5px rgba(0,0,0,.04); }
html[data-theme="dark"] .plugin-category-tabs { background: #2a2a2a; }
html[data-theme="dark"] .plugin-category-tabs .cat-item { color: #b0b0b0; }
html[data-theme="dark"] .plugin-category-tabs .cat-item:hover { color: #e2e8f0; background: rgba(255,255,255,.06); }
html[data-theme="dark"] .plugin-category-tabs .cat-item.active { color: #e0e0e0; background: #3a3a3a; box-shadow: 0 1px 3px rgba(0,0,0,.3); }
.plugin-refresh-btn { height: 28px; padding: 0 10px; border-radius: 6px; border: none; background: #f0f1f4; color: #666; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; justify-content: center; font-size: 13px; white-space: nowrap; transition: all 0.2s; flex-shrink: 0; }
.plugin-refresh-btn:hover { background: #e4e5e9; color: #333; }
.plugin-refresh-btn:active { transform: scale(0.92); }
html[data-theme="dark"] .plugin-refresh-btn { background: #2a2a2a; color: #b0b0b0; }
html[data-theme="dark"] .plugin-refresh-btn:hover { background: #3a3a3a; color: #e0e0e0; }
 
/* 主站后台开关 */
.admin-shortcut-toggle { display: inline-flex; align-items: center; gap: 0; padding: 2px 6px; border-radius: 4px; font-size: 12px; cursor: pointer; transition: all 0.2s; border: 1px solid transparent; }
.admin-shortcut-toggle .toggle-text { display: none; margin-left: 4px; }
.admin-shortcut-toggle:hover .toggle-text { display: inline; }
.admin-shortcut-toggle:hover { padding: 2px 8px; opacity: 0.8; }
.admin-shortcut-toggle.on { background: rgba(22,186,170,0.1); color: #16baaa; border-color: rgba(22,186,170,0.3); }
.admin-shortcut-toggle.off { background: #f5f5f5; color: #999; border-color: #e8e8e8; }
html[data-theme="dark"] .admin-shortcut-toggle.off { background: #2a2a2a; border-color: #444; }

/* 快捷悬浮状态 */
.shortcut-status.on { background: #dcfce7; color: #16a34a; border: 1px solid #86efac; }
.shortcut-status.off { background: #fef2f2; color: #dc2626; border: 1px solid #fca5a5; }
.shortcut-status:hover { opacity: 0.8; transform: scale(1.05); transition: all 0.2s; }
html[data-theme="dark"] .shortcut-status.on { background: rgba(22,163,74,0.2); color: #22c55e; border-color: rgba(22,163,74,0.3); }
html[data-theme="dark"] .shortcut-status.off { background: rgba(220,38,38,0.2); color: #ef4444; border-color: rgba(220,38,38,0.3); }

/* 内嵌 layui 表格的卡片装饰移除规则已迁移到全局 style.css，
   选择器 .layui-card .layui-card-body #index-container .layui-table-view
   覆盖本页与 store.php 等同架构页面，避免多处重复维护。 */
</style>

<div class="layui-tabs order-tabs-wrapper" style="display:flex;align-items:center;justify-content:space-between;" lay-options="{trigger: false}">
    <ul class="layui-tabs-header">
        <li><a href="./store.php?action=plu&plugin_type=0&title=全部插件"><i class="ri-store-2-line"></i> 应用商店</a></li>
        <li class="<?= $filter == '' ? 'layui-this' : '' ?>"><a href="./plugin.php">已安装</a></li>
        <li class="<?= $filter == 'on' ? 'layui-this' : '' ?>"><a href="./plugin.php?filter=on">启用中</a></li>
        <li class="<?= $filter == 'off' ? 'layui-this' : '' ?>"><a href="./plugin.php?filter=off">已关闭</a></li>
        <li class="<?= $filter == 'update' ? 'layui-this' : '' ?>">
            <a href="./plugin.php?filter=update">待更新<?php if($updateCount > 0): ?><span class="update-badge"><?= $updateCount ?></span><?php endif; ?></a>
        </li>
    </ul>
    <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
        <div class="layui-input-inline layui-input-wrap" style="width:180px;margin:0;">
            <input type="text" class="layui-input" id="plugin-search" placeholder="搜索插件..." lay-affix="clear">
        </div>
        <button type="button" class="layui-btn layui-btn-sm" id="plugin-search-btn">搜索</button>
        <button type="button" class="layui-btn-sm layui-btn-primary" id="plugin-reset-btn"><i class="ri-refresh-line"></i> 刷新</button>
        <button class="layui-btn layui-btn-sm layui-btn-normal" id="plugin-upload-btn"><i class="ri-upload-2-line"></i> 上传插件</button>
    </div>
</div>

<?php if (!empty($plugin_categories)): ?>
<div class="plugin-category-bar">
    <div class="plugin-category-tabs" id="plugin-category-tabs">
        <span class="cat-item active" data-id="0">全部</span>
        <?php foreach ($plugin_categories as $cat): ?>
        <span class="cat-item" data-id="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></span>
        <?php endforeach; ?>
    </div>
</div>
<?php else: ?>
<div class="plugin-category-bar" style="margin-bottom:12px;">
</div>
<?php endif; ?>

<div class="layui-table-view" style="border-radius:8px;overflow:hidden;">
    <div class="layui-card-header" style="display:flex;align-items:center;justify-content:center;position:relative;height:auto;padding:12px 15px;">
        <span style="position:absolute;left:15px;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">应用插件</span>
        <span style="position:absolute;right:15px;top:65%;transform:translateY(-50%);display:flex;align-items:center;gap:10px;">
            <div class="view-switch" style="margin-bottom:0;">
                <span class="view-btn active" data-view="list" title="列表视图"><i class="ri-list-check"></i> 列表</span>
                <span class="view-btn" data-view="card" title="卡片视图"><i class="ri-grid-fill"></i> 卡片</span>
            </div>
        </span>
    </div>
    <div class="layui-card-body" style="padding:20px 0;">
        <div id="index-container">
            <table class="layui-hide" id="index" lay-filter="index"></table>
        </div>
        <div class="plugin-cards" id="cards-container"></div>
        <div id="cards-pagination" style="display:none; padding: 15px 0; text-align: center;"></div>
    </div>
</div>
<script type="text/html" id="switch">
    <input type="checkbox" name="{{= d.alias }}" value="{{= d.active }}" title=" ON |OFF " lay-skin="switch" lay-filter="switch" {{= d.active == 1 ? "checked" : "" }}>
</script>
<script type="text/html" id="shortcut">
    {{#  if(d.Setting == true){ }}
    <span class="shortcut-status {{= d.show_in_admin ? 'on' : 'off' }}" lay-event="toggle_admin" data-plugin="{{= d.Plugin }}" data-enabled="{{= d.show_in_admin }}" title="点击切换快捷悬浮状态" style="cursor: pointer; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">
        {{= d.show_in_admin ? '开启中' : '已关闭' }}
    </span>
    {{#  } else { }}
    <span style="color: #ccc; font-size: 12px;">-</span>
    {{#  } }}
</script>

<script type="text/html" id="cover">
    <div class="layui-clear-space">
        <a href="javascript:;" data-id="{{ d.id }}" lay-event="detail">
            <img onerror="this.onerror=null; this.src='./views/images/null.png'" class="cover" data-img="{{ d.preview }}" src="{{ d.preview }}" style="width: 50px; border-radius: 3px; cursor: pointer;" />
        </a>
    </div>
</script>

<script type="text/html" id="name">
    <div class="layui-clear-space" style="cursor: pointer;" lay-event="detail">
        <div>
            <strong>{{ d.Name }}</strong>
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
        <div style="color: #999; font-size: 12px; margin-top: 3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ d.Description }}</div>
    </div>
</script>
<script type="text/html" id="operate">
    <div class="layui-clear-space">
        {{#  if(d.Setting == true){ }}
        <button class="layui-btn layui-bg-blue" lay-event="setting">配置</button>
        {{#  } }}
        <button class="layui-btn layui-bg-red" lay-event="del">卸载</button>
        {{#  if(d.update == 1){ }}
        <button class="layui-btn layui-bg-green" lay-event="update">更新</button>
        {{#  } }}

    </div>
</script>


<script>
    layui.use(['table'], function(){
        var table = layui.table;
        var form = layui.form;
        var dropdown = layui.dropdown;


        // 创建渲染实例
        window.table = table.render({
            elem: '#index',
            id: 'index',
            autoSort: false,
            method: 'get',
            url: './plugin.php?action=index&filter=<?= $filter ?>',
            limits: [10, 20, 50],
            lineStyle: 'height: 69px;',
            text: { none: '<?= $filter === "update" ? "暂无更新" : "无数据" ?>' },
            page: true,
            cols: [[
                {field:'preview', title:'图标', width: 80, templet: '#cover', align: 'center'},
                {field:'name', title:'插件名', minWidth: 520, templet: '#name'},
                {field:'active', title:'应用开关', width: 100, templet: '#switch'},
                {field:'show_in_admin', title:'快捷悬浮', width: 100, templet: '#shortcut'},
                {field:'Author', title:'作者', width: 110},
                {field:'Version', title:'版本', width: 100},
                {fixed: 'right', title:'操作', templet: '#operate', width: <?= $updateCount > 0 ? 210 : 150 ?>}
            ]],

            error: function(res, msg){
            }
        });

        // 搜索提交
        form.on('submit(index-search)', function(data){
            var field = data.field; // 获得表单字段
            // 执行搜索重载
            table.reload('index', {
                page: {
                    curr: 1 // 重新从第 1 页开始
                },
                where: field // 搜索的字段
            });
            return false; // 阻止默认 form 跳转
        });

        // 状态 - 开关操作
        form.on('switch(switch)', function(obj){
            var active = obj.elem.checked == true ? 1 : 0;
            var alias = this.name;
            var loadSwitch = layer.load(2);
            $.ajax({
                url: '?action=switch',
                type: 'POST',
                dataType: 'json',
                data: { plugin: alias, status: active, token: '<?= LoginAuth::genToken() ?>' },
                success: function(e) {
                    if(e.code == 400){
                        return layer.msg(e.msg)
                    }
                    layer.msg('插件状态已更新');
                },
                error: function(err) {
                    layer.msg(err.responseJSON.msg);
                },
                complete: function() {
                    layer.close(loadSwitch);
                }
            });
        });


        // 工具栏事件
        table.on('toolbar(index)', function(obj){
            var id = obj.config.id;
            var checkStatus = table.checkStatus(id);
            var othis = lay(this);
            if(obj.event == 'refresh'){
                table.reload(id);
            }
        });

        // 触发单元格工具事件
        table.on('tool(index)', function(obj){ // 双击 toolDouble
            var data = obj.data; // 获得当前行数据
            var id = obj.config.id;
            if(obj.event == 'del'){
                layer.confirm('删除该插件？', {
                    btn: ['删除插件', '取消'], // 按钮
                    icon: 3,             // 图标，3表示问号
                    title: '温馨提示'
                }, function(index) {
                    var loadSwitch = layer.load(2);
                    $.ajax({
                        url: '?action=del',
                        type: 'POST',
                        dataType: 'json',
                        data: { plugin: data.alias, token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e) {
                            if(e.code == 400){
                                return layer.msg(e.msg)
                            }
                            layer.msg('插件已卸载/删除');
                            table.reload(id);
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
            if(obj.event == 'update'){
                var loadSwitch = layer.load(2);
                $.ajax({
                    url: '?action=upgrade',
                    type: 'POST',
                    dataType: 'json',
                    data: { plugin_id: data.id, alias: data.Plugin, token: '<?= LoginAuth::genToken() ?>' },
                    success: function(e) {
                        if(e.code == 400){
                            return layer.msg(e.msg)
                        }
                        layer.msg('插件已升级至最新版');
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
                if(data.Ui == 'Layui'){
                    let isMobile = window.innerWidth < 1200;
                    let area = isMobile ? ['98%', '85%']  : ['1000px', '80%'];
                    layer.open({
                        id: 'edit',
                        title: data.Name,
                        type: 2,
                        area: area,
                        // skin: 'layui-layer-win10',
                        skin: 'dc-layer-modern',
                        content: 'plugin.php?action=setting_page&plugin=' + data.Plugin,
                        fixed: false, // 不固定
                        maxmin: true,
                        shadeClose: true,
                        success: function(layero, index, that){
                            // layer.iframeAuto(index); // 让 iframe 高度自适应
                            // that.offset(); // 重新自适应弹层坐标
                        }
                    });
                }else{
                    location.href = "./plugin.php?plugin=" + data.Plugin;
                }

            }
            if(obj.event === 'img'){
                layer.photos({
                    photos: {
                        "title": data.Name,
                        "start": 0,
                        "data": [
                            {
                                "alt": data.Name,
                                "pid": 1,
                                "src": data.preview,
                            }
                        ]
                    }
                });
            }
            if(obj.event === 'detail'){
                showPluginDetail(data);
            }

            if(obj.event === 'toggle_admin'){
                toggleAdminShortcut(data.Plugin, data.show_in_admin ? 0 : 1, function(){
                    table.reload('index');
                    // 同步卡片数据
                    allPluginsData.forEach(function(p){
                        if(p.Plugin === data.Plugin) p.show_in_admin = data.show_in_admin ? 0 : 1;
                    });
                });
            }
        });
            
    });

</script>


<script>
    $(function () {
        // 展开插件管理菜单
        $("#menu-plugin").addClass('open');
        $("#menu-plugin > .submenu").show();
        $("#menu-plugin > .link .admin-arrow").addClass('active');
        
        // 根据 filter 参数高亮对应的子菜单
        var filter = '<?= $filter ?>';
        if(filter === 'on') {
            $("#menu-plugin-on").addClass('active');
        } else if(filter === 'off') {
            $("#menu-plugin-off").addClass('active');
        } else if(filter === 'update') {
            $("#menu-plugin-update").addClass('active');
        } else {
            $("#menu-plugin-all").addClass('active');
        }
        
        // 视图切换功能
        // 默认视图：localStorage 已有值时使用历史偏好；否则按视口宽度区分 —— 移动端 (≤768px) 默认卡片视图，PC 端默认列表视图。
        // 用户手动切换后会自动写入 localStorage，下次访问按用户偏好。
        var currentView = localStorage.getItem('plugin_view') || ((window.matchMedia && window.matchMedia('(max-width: 768px)').matches) ? 'card' : 'list');
        var pluginsData = [];
        var allPluginsData = []; // 保存所有数据用于分页
        var cardPageSize = 10; // 卡片视图每页显示数量
        var cardCurrentPage = 1; // 卡片视图当前页
        
        // 初始化视图
        function initView() {
            $('.view-btn').removeClass('active');
            $('.view-btn[data-view="' + currentView + '"]').addClass('active');
            
            if (currentView === 'card') {
                $('#index-container').addClass('hidden');
                $('#cards-container').addClass('active');
                renderCardPagination();
            } else {
                $('#index-container').removeClass('hidden');
                $('#cards-container').removeClass('active');
                $('#cards-pagination').hide();
            }
        }
        
        // 渲染卡片分页
        function renderCardPagination() {
            if (allPluginsData.length === 0) {
                renderCards([]);
                $('#cards-pagination').hide();
                return;
            }
            layui.use('laypage', function(){
                var laypage = layui.laypage;
                laypage.render({
                    elem: 'cards-pagination',
                    count: allPluginsData.length,
                    limit: cardPageSize,
                    curr: cardCurrentPage,
                    limits: [10, 20, 50],
                    layout: ['count', 'prev', 'page', 'next', 'limit', 'skip'],
                    jump: function(obj, first){
                        cardCurrentPage = obj.curr;
                        cardPageSize = obj.limit;
                        var start = (obj.curr - 1) * obj.limit;
                        var end = start + obj.limit;
                        var pageData = allPluginsData.slice(start, end);
                        renderCards(pageData);
                    }
                });
            });
            $('#cards-pagination').show();
        }
        
        // 渲染卡片视图
        function renderCards(pageData) {
            var dataToRender = pageData || pluginsData;
            var dataToRender = pageData || pluginsData;
            if (dataToRender.length === 0 && allPluginsData.length === 0) {
                var emptySvg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 150'%3E%3Cdefs%3E%3ClinearGradient id='grad' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%23e8e8e8'/%3E%3Cstop offset='100%25' style='stop-color:%23d0d0d0'/%3E%3C/linearGradient%3E%3C/defs%3E%3Cellipse cx='100' cy='130' rx='70' ry='12' fill='%23f0f0f0'/%3E%3Crect x='50' y='30' width='100' height='90' rx='8' fill='url(%23grad)' stroke='%23ccc' stroke-width='2'/%3E%3Crect x='60' y='45' width='80' height='8' rx='4' fill='%23fff'/%3E%3Crect x='60' y='60' width='60' height='8' rx='4' fill='%23fff'/%3E%3Crect x='60' y='75' width='70' height='8' rx='4' fill='%23fff'/%3E%3Crect x='60' y='90' width='50' height='8' rx='4' fill='%23fff'/%3E%3Ccircle cx='140' cy='25' r='20' fill='%23f5f5f5' stroke='%23ddd' stroke-width='2'/%3E%3Cpath d='M135 25 L145 25 M140 20 L140 30' stroke='%23bbb' stroke-width='3' stroke-linecap='round'/%3E%3C/svg%3E";
                var emptyText = '<?= $filter === "update" ? "暂无更新" : "暂无插件" ?>';
                $('#cards-container').removeClass('active').css({'display':'flex','justify-content':'center','align-items':'center','min-height':'300px'}).html('<div style="text-align:center;padding:80px 20px;"><img src="' + emptySvg + '" style="width:200px;max-width:80%;margin-bottom:20px;"><div style="color:#999;font-size:14px;">' + emptyText + '</div></div>');
                $('#cards-pagination').hide();
                return;
            }
            $('#cards-container').addClass('active').css({'display':'','justify-content':'','align-items':'','min-height':''});
            
            var html = '';
            dataToRender.forEach(function(d) {
                var coverImg = d.preview && d.preview.indexOf('plugin-icon.png') === -1 
                    ? '<img src="' + d.preview + '" onerror="this.parentNode.innerHTML=\'<i class=\\\'ri-puzzle-line default-icon\\\'></i>\'">' 
                    : '<i class="ri-puzzle-line default-icon"></i>';
                
                var updateBadge = d.update == 1 ? '<span class="update-dot">有更新</span>' : '';
                var isActive = d.active == 1;
                var cardClass = isActive ? '' : ' disabled';
                var statusTag = isActive 
                    ? '<span class="plugin-card-switch on" data-event="toggle" data-alias="' + d.alias + '" data-active="1"><i class="ri-toggle-fill"></i> ON</span>'
                    : '<span class="plugin-card-switch off" data-event="toggle" data-alias="' + d.alias + '" data-active="0"><i class="ri-toggle-line"></i> OFF</span>';
                
                html += '<div class="plugin-card' + cardClass + '" data-plugin=\'' + JSON.stringify(d).replace(/'/g, "&#39;") + '\'>';
                html += '  <div class="plugin-card-cover" data-preview="' + d.preview + '">' + coverImg + statusTag + updateBadge + '</div>';
                html += '  <div class="plugin-card-body">';
                html += '    <div class="plugin-card-header">';
                html += '      <div class="plugin-card-title">' + d.Name + '</div>';
                html += '    </div>';
                html += '    <div class="plugin-card-desc">' + (d.Description || '暂无描述') + '</div>';
                html += '  </div>';
                // 到期时间显示
                var expireHtml = '';
                if (d.license_status === 'blocked') {
                    expireHtml = '<span class="plugin-expire-tag blocked"><i class="ri-forbid-line"></i> 已被禁用</span>';
                } else if (d.license_status === 'unauthorized') {
                    expireHtml = '<span class="plugin-expire-tag unauthorized"><i class="ri-lock-line"></i> 未授权</span>';
                } else if (d.license_status === 'expired') {
                    expireHtml = '<span class="plugin-expire-tag expired"><i class="ri-error-warning-line"></i> 已到期</span>';
                } else if (d.license_status === 'trial' || d.buy_type === 'trial') {
                    expireHtml = '<span class="plugin-expire-tag trial"><i class="ri-gift-line"></i> 试用中</span>';
                } else if (d.buy_type === 'permanent') {
                    expireHtml = '<span class="plugin-expire-tag permanent"><i class="ri-shield-check-line"></i> 已买断</span>';
                } else if (d.buy_type === 'monthly' && d.expire_time) {
                    var now = new Date();
                    var expireDate = new Date(d.expire_time);
                    if (expireDate > now) {
                        expireHtml = '<span class="plugin-expire-tag monthly"><i class="ri-time-line"></i> ' + d.expire_time.split(' ')[0] + '</span>';
                    } else {
                        expireHtml = '<span class="plugin-expire-tag expired"><i class="ri-error-warning-line"></i> 已到期</span>';
                    }
                } else {
                    expireHtml = '<span class="plugin-expire-tag local"><i class="ri-infinity-line"></i> 永久可用</span>';
                }
                html += '  <div class="plugin-card-meta"><span><i class="ri-user-line"></i> ' + d.Author + '</span>' + expireHtml + '</div>';
                html += '  <div class="plugin-card-actions">';
                if (d.Setting) {
                    html += '    <span class="admin-shortcut-toggle ' + (d.show_in_admin ? 'on' : 'off') + '" data-event="toggle_admin" data-plugin="' + d.Plugin + '" data-enabled="' + (d.show_in_admin || 0) + '" title="主站后台快捷入口"><i class="' + (d.show_in_admin ? 'ri-pushpin-fill' : 'ri-pushpin-line') + '"></i><span class="toggle-text">快捷入口</span></span>';
                    html += '    <button class="layui-btn layui-btn-sm layui-bg-blue" data-event="setting">配置</button>';
                }
                html += '    <button class="layui-btn layui-btn-sm layui-bg-red" data-event="del">卸载</button>';
                if (d.update == 1) {
                    html += '    <button class="layui-btn layui-btn-sm layui-bg-green" data-event="update">更新</button>';
                }
                html += '  </div>';
                html += '</div>';
            });
            
            $('#cards-container').html(html);
        }
        
        // 加载插件数据
        function loadPlugins(callback) {
            var loadIndex = layer.load(2);
            $.ajax({
                url: './plugin.php?action=index&filter=<?= $filter ?>&limit=9999',
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    // 兼容不同的返回格式
                    if (res.code === 200 || res.code === 0) {
                        allPluginsData = res.data || [];
                    } else if (Array.isArray(res)) {
                        allPluginsData = res;
                    } else if (res.data) {
                        allPluginsData = res.data;
                    }
                    pluginsData = allPluginsData;
                    cardCurrentPage = 1;
                    if (callback) callback();
                },
                error: function(err) {
                    layer.msg('加载失败');
                },
                complete: function() {
                    layer.close(loadIndex);
                }
            });
        }
        
        // 页面加载时，如果是卡片视图则加载数据
        if (currentView === 'card') {
            $('#index-container').addClass('hidden');
            $('#cards-container').addClass('active');
            $('.view-btn').removeClass('active');
            $('.view-btn[data-view="card"]').addClass('active');
            loadPlugins(function(){ renderCardPagination(); });
        }
        
        // 视图切换点击事件
        $(document).on('click', '.view-btn', function() {
            var view = $(this).data('view');
            if (view === currentView) return;
            
            currentView = view;
            localStorage.setItem('plugin_view', view);
            
            $('.view-btn').removeClass('active');
            $(this).addClass('active');
            
            if (view === 'card') {
                $('#index-container').addClass('hidden');
                $('#cards-container').addClass('active');
                if (allPluginsData.length === 0) {
                    loadPlugins(function(){ renderCardPagination(); });
                } else {
                    renderCardPagination();
                }
            } else {
                $('#index-container').removeClass('hidden');
                $('#cards-container').removeClass('active');
                $('#cards-pagination').hide();
            }
        });
        // 当前分类筛选
        var currentCategoryId = 0;
 
        // 分类标签点击事件
        $(document).on('click', '#plugin-category-tabs .cat-item', function() {
            var catId = parseInt($(this).data('id'));
            if (catId === currentCategoryId) return;
            currentCategoryId = catId;
            $('#plugin-category-tabs .cat-item').removeClass('active');
            $(this).addClass('active');
 
            var keyword = $('#plugin-search').val().trim().toLowerCase();
            filterPlugins(keyword);
        });
 
        // 搜索功能
        $(document).on('click', '#plugin-search-btn', function() {
            var keyword = $('#plugin-search').val().trim().toLowerCase();
            filterPlugins(keyword);
        });
        $(document).on('click', '#plugin-reset-btn', function() {
            var $icon = $(this).find('i');
            $icon.css('transition','transform 0.5s').css('transform','rotate(360deg)');
            setTimeout(function(){ $icon.css('transition','none').css('transform',''); }, 500);
            $('#plugin-search').val('');
            currentCategoryId = 0;
            $('#plugin-category-tabs .cat-item').removeClass('active').first().addClass('active');
            if (currentView === 'card') {
                pluginsData = []; allPluginsData = [];
                loadPlugins(function(){ doCardFilter(''); });
            } else {
                filterPlugins('');
            }
        });
        $(document).on('keypress', '#plugin-search', function(e) {
            if (e.which === 13) {
                var keyword = $(this).val().trim().toLowerCase();
                filterPlugins(keyword);
            }
        });
        $(document).on('click', '#plugin-upload-btn', function() {
            showUploadDialog();
        });

        // URL ?search= 参数自动搜索（从商店安装成功跳转过来）
        (function() {
            var urlSearch = new URLSearchParams(window.location.search).get('search');
            if (urlSearch) {
                $('#plugin-search').val(urlSearch);
                // 延迟执行，确保表格/卡片渲染完毕
                setTimeout(function() { filterPlugins(urlSearch.trim().toLowerCase()); }, 300);
                // 清除 URL 参数，避免刷新后重复搜索
                if (window.history.replaceState) {
                    var cleanUrl = window.location.pathname;
                    window.history.replaceState(null, '', cleanUrl);
                }
            }
        })();
        
        function filterPlugins(keyword) {
            if (currentView === 'card') {
                if (pluginsData.length === 0) {
                    loadPlugins(function() {
                        doCardFilter(keyword);
                    });
                } else {
                    doCardFilter(keyword);
                }
            } else {
                layui.table.reload('index', { 
                    page: { curr: 1 }, 
                    where: { keyword: keyword, category_id: currentCategoryId }
                });
            }
        }
        
        function doCardFilter(keyword) {
            var filtered = pluginsData;
            // 分类过滤
            if (currentCategoryId > 0) {
                filtered = filtered.filter(function(p) {
                    return p.category_id == currentCategoryId;
                });
            }
            // 关键词过滤
            if (keyword) {
                filtered = filtered.filter(function(p) {
                    var name = (p.Name || '').toLowerCase();
                    var desc = (p.Description || '').toLowerCase();
                    var author = (p.Author || '').toLowerCase();
                    var alias = (p.alias || '').toLowerCase();
                    return name.indexOf(keyword) > -1 || desc.indexOf(keyword) > -1 || author.indexOf(keyword) > -1 || alias.indexOf(keyword) > -1;
                });
            }
            allPluginsData = filtered;
            cardCurrentPage = 1;
            renderCardPagination();
        }
        
        // 卡片视图事件委托 - 点击封面弹出详情
        $('#cards-container').on('click', '.plugin-card-cover', function(e) {
            // 如果点击的是开关按钮，不弹出详情
            if ($(e.target).closest('[data-event="toggle"]').length) return;
            
            var card = $(this).closest('.plugin-card');
            var data = card.data('plugin');
            showPluginDetail(data);
        });
        
        $('#cards-container').on('click', '[data-event]', function(e) {
            e.stopPropagation(); // 阻止事件冒泡
            var event = $(this).data('event');
            var card = $(this).closest('.plugin-card');
            var data = card.data('plugin');
            
            // 状态切换
            if (event === 'toggle') {
                var alias = $(this).data('alias');
                var currentActive = $(this).data('active');
                var newActive = currentActive == 1 ? 0 : 1;
                var loadSwitch = layer.load(2);
                $.ajax({
                    url: '?action=switch',
                    type: 'POST',
                    dataType: 'json',
                    data: { plugin: alias, status: newActive, token: '<?= LoginAuth::genToken() ?>' },
                    success: function(e) {
                        if (e.code == 400) return layer.msg(e.msg);
                        layer.msg('插件状态已更新');
                        // 更新本地数据并重新渲染
                        allPluginsData.forEach(function(p) {
                            if (p.alias === alias) p.active = newActive;
                        });
                        renderCardPagination();
                        window.table.reload('index');
                    },
                    error: function(err) {
                        layer.msg(err.responseJSON.msg);
                    },
                    complete: function() {
                        layer.close(loadSwitch);
                    }
                });
                return;
            }
            
            if (event === 'setting') {
                if (data.Ui == 'Layui') {
                    let isMobile = window.innerWidth < 1200;
                    let area = isMobile ? ['98%', '85%'] : ['1000px', '80%'];
                    layer.open({
                        id: 'edit',
                        title: data.Name,
                        type: 2,
                        area: area,
                        skin: 'dc-layer-modern',
                        content: 'plugin.php?action=setting_page&plugin=' + data.Plugin,
                        fixed: false,
                        maxmin: true,
                        shadeClose: true
                    });
                } else {
                    location.href = "./plugin.php?plugin=" + data.Plugin;
                }
            }
            
            if (event === 'del') {
                layer.confirm('删除该插件？', {
                    btn: ['删除插件', '取消'],
                    icon: 3,
                    title: '温馨提示'
                }, function(index) {
                    var loadSwitch = layer.load(2);
                    $.ajax({
                        url: '?action=del',
                        type: 'POST',
                        dataType: 'json',
                        data: { plugin: data.alias, token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e) {
                            if (e.code == 400) return layer.msg(e.msg);
                            layer.msg('插件已卸载/删除');
                            loadPlugins(function(){ renderCardPagination(); });
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
                    data: { plugin_id: data.id, alias: data.Plugin, token: '<?= LoginAuth::genToken() ?>' },
                    success: function(e) {
                        if (e.code == 400) return layer.msg(e.msg);
                        layer.msg('插件已升级至最新版');
                        loadPlugins(function(){ renderCardPagination(); });
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
             
            if (event === 'toggle_admin') {
                var pluginSlug = $(this).data('plugin');
                var currentEnabled = parseInt($(this).data('enabled'));
                toggleAdminShortcut(pluginSlug, currentEnabled ? 0 : 1, function(){
                    pluginsData.forEach(function(p){
                        if(p.Plugin === pluginSlug) p.show_in_admin = currentEnabled ? 0 : 1;
                    });
                    allPluginsData.forEach(function(p){
                        if(p.Plugin === pluginSlug) p.show_in_admin = currentEnabled ? 0 : 1;
                    });
                    renderCardPagination();
                    window.table.reload('index');
                });
            }
        });
    });
    
    // 显示插件详情弹窗
    function showPluginDetail(plugin) {
        var coverHtml = plugin.preview && plugin.preview.indexOf('plugin-icon.png') === -1
            ? '<img src="' + escapeHtml(plugin.preview) + '" onerror="this.style.display=\'none\'">'
            : '<i class="ri-puzzle-line default-icon"></i>';
        
        var statusHtml = plugin.active == 1
            ? '<div class="plugin-detail-status on" style="cursor:pointer;" onclick="togglePluginStatus(\'' + plugin.alias + '\', 0)"><i class="ri-checkbox-circle-fill"></i> 已启用 <span style="font-size:11px;opacity:0.8;">(点击关闭)</span></div>'
            : '<div class="plugin-detail-status off" style="cursor:pointer;" onclick="togglePluginStatus(\'' + plugin.alias + '\', 1)"><i class="ri-close-circle-line"></i> 已关闭 <span style="font-size:11px;opacity:0.8;">(点击启用)</span></div>';
        
        var actionsHtml = '';
        if (plugin.Setting) {
            actionsHtml += '<button class="layui-btn layui-bg-blue" onclick="var p = currentDetailPlugin; closePluginDetail(); handlePluginAction(\'setting\', p);"><i class="ri-settings-3-line"></i> 配置</button>';
        }
        actionsHtml += '<button class="layui-btn layui-bg-red" onclick="var p = currentDetailPlugin; closePluginDetail(); handlePluginAction(\'del\', p);"><i class="ri-delete-bin-line"></i> 卸载</button>';
        if (plugin.update == 1) {
            actionsHtml += '<button class="layui-btn layui-bg-green" onclick="var p = currentDetailPlugin; closePluginDetail(); handlePluginAction(\'update\', p);"><i class="ri-refresh-line"></i> 更新</button>';
        }
        
        var html = '<div class="plugin-detail-modal" onclick="closePluginDetail()">' +
            '<div class="plugin-detail-box" onclick="event.stopPropagation()">' +
                '<div class="plugin-detail-header">' + coverHtml +
                    '<button class="plugin-detail-close" onclick="closePluginDetail()"><i class="ri-close-line"></i></button>' +
                '</div>' +
                '<div class="plugin-detail-body">' +
                    '<div class="plugin-detail-title">' + escapeHtml(plugin.Name) + '</div>' +
                    '<div class="plugin-detail-meta">' +
                        '<span><i class="ri-user-line"></i> ' + escapeHtml(plugin.Author || '未知') + '</span>' +
                        '<span><i class="ri-price-tag-3-line"></i> v' + escapeHtml(plugin.Version) + '</span>' +
                        (function(){
                            if (plugin.license_status === 'blocked') {
                                return '<span style="color:#333;"><i class="ri-forbid-line"></i> 已被禁用</span>';
                            } else if (plugin.license_status === 'unauthorized') {
                                return '<span style="color:#f59e0b;"><i class="ri-lock-line"></i> 未授权</span>';
                            } else if (plugin.license_status === 'expired') {
                                return '<span style="color:#ef4444;"><i class="ri-error-warning-line"></i> 已到期</span>';
                            } else if (plugin.license_status === 'trial' || plugin.buy_type === 'trial') {
                                return '<span style="color:#9333ea;"><i class="ri-gift-line"></i> 试用中</span>';
                            } else if (plugin.buy_type === 'permanent') {
                                return '<span style="color:#16a34a;"><i class="ri-shield-check-line"></i> 已买断</span>';
                            } else if (plugin.buy_type === 'monthly' && plugin.expire_time) {
                                var now = new Date();
                                var expireDate = new Date(plugin.expire_time);
                                if (expireDate > now) {
                                    return '<span style="color:#f59e0b;"><i class="ri-time-line"></i> 到期：' + plugin.expire_time.split(' ')[0] + '</span>';
                                } else {
                                    return '<span style="color:#ef4444;"><i class="ri-error-warning-line"></i> 已到期</span>';
                                }
                            } else {
                                return '<span><i class="ri-infinity-line"></i> 永久可用</span>';
                            }
                        })() +
                    '</div>' +
                    statusHtml +
                    '<div class="plugin-detail-desc">' + escapeHtml(plugin.Description || '暂无描述') + '</div>' +
                    '<div class="plugin-detail-actions">' + actionsHtml + '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
        
        window.currentDetailPlugin = plugin;
        $('body').append(html);
    }
    
    // 关闭详情弹窗
    function closePluginDetail() {
        $('.plugin-detail-modal').remove();
        window.currentDetailPlugin = null;
    }
    
    // 处理插件操作
    function handlePluginAction(event, data) {
        if (event === 'setting') {
            if (data.Ui == 'Layui') {
                let isMobile = window.innerWidth < 1200;
                let area = isMobile ? ['98%', '85%'] : ['1000px', '80%'];
                layer.open({
                    id: 'edit',
                    title: data.Name,
                    type: 2,
                    area: area,
                    skin: 'dc-layer-modern',
                    content: 'plugin.php?action=setting_page&plugin=' + data.Plugin,
                    fixed: false,
                    maxmin: true,
                    shadeClose: true
                });
            } else {
                location.href = "./plugin.php?plugin=" + data.Plugin;
            }
        }
        
        if (event === 'del') {
            layer.confirm('删除该插件？', {
                btn: ['删除插件', '取消'],
                icon: 3,
                title: '温馨提示'
            }, function(index) {
                layer.close(index);
                var loadSwitch = layer.load(2);
                $.ajax({
                    url: '?action=del',
                    type: 'POST',
                    dataType: 'json',
                    data: { plugin: data.alias, token: '<?= LoginAuth::genToken() ?>' },
                    success: function(e) {
                        if (e.code == 400) return layer.msg(e.msg);
                        layer.msg('插件已卸载/删除');
                        location.reload();
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
                data: { plugin_id: data.id, alias: data.Plugin, token: '<?= LoginAuth::genToken() ?>' },
                success: function(e) {
                    if (e.code == 400) return layer.msg(e.msg);
                    layer.msg('插件已升级至最新版');
                    location.reload();
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
    
    // 切换插件状态
    function togglePluginStatus(alias, newActive) {
        var loadSwitch = layer.load(2);
        $.ajax({
            url: '?action=switch',
            type: 'POST',
            dataType: 'json',
            data: { plugin: alias, status: newActive, token: '<?= LoginAuth::genToken() ?>' },
            success: function(e) {
                if (e.code == 400) return layer.msg(e.msg);
                layer.msg('插件状态已更新');
                closePluginDetail();
                location.reload();
            },
            error: function(err) {
                layer.msg(err.responseJSON.msg);
            },
            complete: function() {
                layer.close(loadSwitch);
            }
        });
    }
    // 切换"主站后台"快捷入口
    function toggleAdminShortcut(pluginSlug, enabled, callback) {
        $.ajax({
            url: '?action=toggle_admin_shortcut',
            type: 'POST',
            dataType: 'json',
            data: { plugin: pluginSlug, enabled: enabled, token: '<?= LoginAuth::genToken() ?>' },
            success: function(e) {
                if (e.code == 400) return layer.msg(e.msg);
                layer.msg(enabled ? '已添加到快捷入口' : '已从快捷入口移除');
                // 触发悬浮图标刷新
                if (window.refreshPluginFab) {
                    window.refreshPluginFab();
                }
                if (callback) callback();
            },
            error: function(err) {
                layer.msg(err.responseJSON ? err.responseJSON.msg : '操作失败');
            }
        });
    }
 
    // HTML转义
    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }
    
    // ESC关闭弹窗
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') closePluginDetail();
    });
    
    // 显示上传插件弹窗
    function showUploadDialog() {
        var html = '<div style="padding: 20px;">' +
            '<form id="upload-plugin-form" enctype="multipart/form-data">' +
            '<input type="hidden" name="token" value="<?= LoginAuth::genToken() ?>">' +
            '<div class="layui-form-item">' +
            '<div class="layui-upload-drag" id="upload-drag-area" style="width: 100%; padding: 30px 0; border: 2px dashed #e2e2e2; border-radius: 8px; text-align: center; cursor: pointer; transition: all 0.3s;">' +
            '<i class="ri-upload-cloud-2-line" style="font-size: 48px; color: #999;"></i>' +
            '<p style="margin: 10px 0 5px; color: #666;">点击或拖拽插件安装包到此处</p>' +
            '<p style="font-size: 12px; color: #999;">仅支持 .zip 格式的插件安装包</p>' +
            '</div>' +
            '<input type="file" name="pluzip" id="plugin-file-input" accept=".zip" style="position:absolute;left:-9999px;">' +
            '</div>' +
            '<div id="upload-file-info" style="display: none; padding: 15px; background: #f8f9fa; border-radius: 6px; margin-bottom: 15px;">' +
            '<div style="display: flex; align-items: center; gap: 10px;">' +
            '<i class="ri-file-zip-line" style="font-size: 32px; color: #16baaa;"></i>' +
            '<div style="flex: 1;">' +
            '<div id="upload-file-name" style="font-weight: 600; color: #333;"></div>' +
            '<div id="upload-file-size" style="font-size: 12px; color: #999;"></div>' +
            '</div>' +
            '<button type="button" class="layui-btn layui-btn-xs layui-btn-danger" id="clear-file-btn"><i class="ri-close-line"></i></button>' +
            '</div>' +
            '</div>' +
            '<div class="layui-form-item" style="margin-bottom: 0; text-align: center;">' +
            '<button type="button" class="layui-btn layui-btn-lg" id="upload-submit-btn" disabled style="width: 200px;"><i class="ri-upload-2-line"></i> 安装插件</button>' +
            '</div>' +
            '</form>' +
            '</div>';
        
        layer.open({
            type: 1,
            title: '<i class="ri-upload-cloud-2-line"></i> 上传插件安装包',
            area: ['500px', 'auto'],
            content: html,
            success: function(layero, index) {
                var $layer = $(layero);
                
                // 点击上传区域触发文件选择
                $layer.find('#upload-drag-area').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $layer.find('#plugin-file-input')[0].click();
                });
                
                // 拖拽上传
                $layer.find('#upload-drag-area').on('dragover', function(e) {
                    e.preventDefault();
                    $(this).css('border-color', '#16baaa');
                }).on('dragleave', function(e) {
                    e.preventDefault();
                    $(this).css('border-color', '#e2e2e2');
                }).on('drop', function(e) {
                    e.preventDefault();
                    $(this).css('border-color', '#e2e2e2');
                    var files = e.originalEvent.dataTransfer.files;
                    if (files.length > 0) {
                        handleFileSelectInLayer(files[0], $layer);
                    }
                });
                
                // 文件选择变化
                $layer.find('#plugin-file-input').on('change', function() {
                    if (this.files.length > 0) {
                        handleFileSelectInLayer(this.files[0], $layer);
                    }
                });
                
                // 清除文件
                $layer.find('#clear-file-btn').on('click', function() {
                    $layer.find('#plugin-file-input').val('');
                    $layer.find('#upload-drag-area').show();
                    $layer.find('#upload-file-info').hide();
                    $layer.find('#upload-submit-btn').prop('disabled', true);
                    window.uploadPluginFile = null;
                });
                
                // 提交上传
                $layer.find('#upload-submit-btn').on('click', function() {
                    submitUploadPluginInLayer($layer, index);
                });
            }
        });
    }
    
    // 处理文件选择
    function handleFileSelectInLayer(file, $layer) {
        if (!file.name.toLowerCase().endsWith('.zip')) {
            layer.msg('请选择 .zip 格式的插件安装包', {icon: 2});
            return;
        }
        
        $layer.find('#upload-file-name').text(file.name);
        $layer.find('#upload-file-size').text(formatFileSize(file.size));
        $layer.find('#upload-drag-area').hide();
        $layer.find('#upload-file-info').show();
        $layer.find('#upload-submit-btn').prop('disabled', false);
        
        // 保存文件引用
        window.uploadPluginFile = file;
    }
    
    // 格式化文件大小
    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }
    
    // 提交上传
    function submitUploadPluginInLayer($layer, layerIndex) {
        if (!window.uploadPluginFile) {
            layer.msg('请先选择插件安装包', {icon: 2});
            return;
        }
        
        var formData = new FormData();
        formData.append('pluzip', window.uploadPluginFile);
        formData.append('token', '<?= LoginAuth::genToken() ?>');
        
        var loadIndex = layer.load(2, {shade: [0.3, '#000']});
        $layer.find('#upload-submit-btn').prop('disabled', true).html('<i class="ri-loader-4-line dc-spin"></i> 安装中...');
        
        $.ajax({
            url: './plugin.php?action=upload_ajax',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                layer.close(loadIndex);
                if (res.code == 0 || res.code == 200) {
                    layer.close(layerIndex);
                    layer.msg('插件安装成功！', {icon: 1}, function() {
                        location.reload();
                    });
                } else {
                    layer.msg(res.msg || '安装失败', {icon: 2});
                    $layer.find('#upload-submit-btn').prop('disabled', false).html('<i class="ri-upload-2-line"></i> 安装插件');
                }
            },
            error: function(xhr) {
                layer.close(loadIndex);
                var msg = '安装失败';
                if (xhr.responseJSON && xhr.responseJSON.msg) {
                    msg = xhr.responseJSON.msg;
                }
                layer.msg(msg, {icon: 2});
                $layer.find('#upload-submit-btn').prop('disabled', false).html('<i class="ri-upload-2-line"></i> 安装插件');
            }
        });
    }
</script>
