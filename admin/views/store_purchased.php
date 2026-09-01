<?php defined('DC_ROOT') || exit('access denied!'); ?>

<?php
$licenseCenterUrl = '';
if (defined('CURRENT_LINE') && defined('DC_LINE') && isset(DC_LINE[CURRENT_LINE])) {
    $licenseCenterUrl = rtrim(DC_LINE[CURRENT_LINE]['value'], '/') . '/user/';
} elseif (defined('LICENSE_SERVER_URL') && LICENSE_SERVER_URL) {
    $licenseCenterUrl = rtrim(LICENSE_SERVER_URL, '/') . '/user/';
} else {
    $licenseCenterUrl = 'https://dcshop.xzsc.cc/user/';
}
?>

<style>
/* Mac 风格分类栏 */
.store-category-bar { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
.store-refresh-btn { height: 28px; padding: 0 10px; border-radius: 6px; border: none; background: #f0f1f4; color: #666; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; justify-content: center; font-size: 13px; white-space: nowrap; transition: all 0.2s; flex-shrink: 0; }
.store-refresh-btn:hover { background: #e4e5e9; color: #333; }
.store-refresh-btn:active { transform: scale(0.92); }
html[data-theme="dark"] .store-refresh-btn { background: #2a2a2a; color: #b0b0b0; }
html[data-theme="dark"] .store-refresh-btn:hover { background: #3a3a3a; color: #e0e0e0; }

/* 已购买子分类筛选 - Mac 胶囊 */
.purchase-filter { display: inline-flex; flex-wrap: wrap; gap: 2px; padding: 3px; background: #f0f1f4; border-radius: 8px; }
.filter-item { display: flex; align-items: center; gap: 5px; padding: 5px 14px; background: transparent; border: none; border-radius: 6px; text-decoration: none; color: #666; font-size: 13px; transition: all 0.2s; cursor: pointer; white-space: nowrap; }
.filter-item:hover { color: #333; background: rgba(255,255,255,.5); }
.filter-item.active { color: #333; font-weight: 500; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.08), 0 0 0 0.5px rgba(0,0,0,.04); }
.filter-item.has-expired:not(.active) { color: #ff4d4f; }
.filter-item.has-expired:not(.active) .filter-count { background: #ff4d4f; color: #fff; }
.filter-item.has-uninstalled:not(.active) { color: #1e9fff; }
.filter-item.has-uninstalled:not(.active) .filter-count { background: #1e9fff; color: #fff; }
.filter-item i { font-size: 14px; }
.filter-count { background: #e8eaed; padding: 1px 8px; border-radius: 10px; font-size: 11px; }
.filter-item.active .filter-count { background: rgba(22,186,170,.15); color: #16baaa; }
html[data-theme="dark"] .purchase-filter { background: #2a2a2a; }
html[data-theme="dark"] .filter-item { color: #b0b0b0; }
html[data-theme="dark"] .filter-item.active { color: #e0e0e0; background: #3a3a3a; box-shadow: 0 1px 3px rgba(0,0,0,.3); }

/* 视图切换按钮 */
.view-switch { display: flex; background: #f5f5f5; border-radius: 6px; padding: 3px; margin-left: auto;box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
.view-switch .view-btn { display: inline-flex; align-items: center; justify-content: center; gap: 5px; padding: 6px 12px; border: none; background: transparent; cursor: pointer; font-size: 13px; color: #999; transition: all 0.2s; border-radius: 4px; }
.view-switch .view-btn:hover { color: #666; }
.view-switch .view-btn.active { background: #fff; color: #16baaa; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }

/* 卡片视图样式 */
.store-cards { display: none; }
.store-cards.active { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; padding: 10px 0; }
@media (max-width: 1400px) { .store-cards.active { grid-template-columns: repeat(4, 1fr); } }
@media (max-width: 1100px) { .store-cards.active { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 768px) { .store-cards.active { grid-template-columns: repeat(2, 1fr); gap: 12px; } .view-switch { margin-left: 0; margin-top: 10px; } }

.store-card { background: #ffffff85; border: 1px solid #eef1f4; border-radius: 6px; overflow: hidden;  transition: all 0.3s; }
.store-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.12); }
.store-card-cover { width: 100%; aspect-ratio: 1 / 1; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; overflow: hidden; cursor: pointer; position: relative; }
.store-card-cover img { width: 100%; height: 100%; object-fit: cover; }
.store-card-cover .default-icon { font-size: 64px; color: rgba(255,255,255,0.8); }
.store-card-body { padding: 12px 15px; }
.store-card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.store-card-title { font-size: 15px; font-weight: 600; color: #333; }
.store-card-version { font-size: 12px; color: #999; flex-shrink: 0; }
.store-card-desc { font-size: 13px; color: #666; line-height: 1.5; height: 60px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; }
.store-card-meta { font-size: 12px; color: #999; display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; border-top: 1px solid #f0f0f0; }
.store-card-meta i { font-size: 14px; margin-right: 4px; }
.store-card-meta .meta-left { display: flex; align-items: center; gap: 12px; }
.store-card-prices { display: flex; gap: 4px; padding: 8px 0 0; flex-wrap: wrap; }
.store-card-prices .price-tag { display: inline-flex; align-items: center; padding: 2px 5px; border-radius: 4px; font-size: 11px; white-space: nowrap; }
.store-card-prices .price-tag .label { color: #fff; padding: 1px 3px; border-radius: 3px; margin-right: 3px; font-weight: 700; }
.store-card-prices .price-tag .value { font-weight: 600; }
.store-card-prices .price-tag.vip { background: rgba(255,247,237,0.95); }
.store-card-prices .price-tag.vip .label { background: #f97316; }
.store-card-prices .price-tag.vip .value { color: #ea580c; }
.store-card-prices .price-tag.svip { background: #fef3c7; }
.store-card-prices .price-tag.svip .label { background: #eab308; }
.store-card-prices .price-tag.svip .value { color: #ca8a04; }
.store-card-prices .price-tag.zhizun { background: #efe8e5; }
.store-card-prices .price-tag.zhizun .label { background: linear-gradient(135deg, #430000 0%, #bf9500 100%)!important; }
.store-card-prices .price-tag.zhizun .value { color: #7c3aed; }
.store-card-prices .price-tag .value.free { color: #16a34a !important; }
.store-card-actions { display: flex; gap: 6px; }
.store-card-actions .layui-btn { margin: 0; }
.store-card-installed { position: absolute; top: 10px; left: 10px; padding: 4px 10px; background: rgba(22, 163, 74, 0.9); color: #fff; font-size: 12px; border-radius: 12px; }
#index-container.hidden { display: none; }

/* 应用详情弹窗 */
.app-detail-modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 19891015; display: flex; align-items: center; justify-content: center; padding: 20px; }
.app-detail-box { background: #fff; border-radius: 12px; max-width: 500px; width: 100%; max-height: 90vh; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.2); display: flex; flex-direction: column; }
.app-detail-header { position: relative; aspect-ratio: 16 / 9; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.app-detail-header img { width: 100%; height: 100%; object-fit: cover; }
.app-detail-close { position: absolute; top: 10px; right: 10px; width: 32px; height: 32px; background: rgba(0,0,0,0.3); border: none; border-radius: 50%; color: #fff; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.app-detail-body { padding: 20px; overflow-y: auto; flex: 1; }
.app-detail-title { font-size: 20px; font-weight: 600; margin-bottom: 12px; color: #333; display: flex; align-items: center; flex-wrap: wrap; gap: 6px; }
.app-detail-meta { display: flex; gap: 15px; margin-bottom: 15px; font-size: 13px; color: #999; flex-wrap: wrap; }
.app-detail-meta i { margin-right: 4px; }
.app-detail-desc { color: #666; line-height: 1.8; margin-bottom: 20px; font-size: 14px; }
.app-detail-prices { display: flex; gap: 8px; margin-bottom: 15px; flex-wrap: wrap; }
.app-detail-prices .price-item { display: flex; align-items: center; padding: 6px 10px; border-radius: 6px; font-size: 12px; }
.app-detail-prices .price-item .label { color: #fff; padding: 2px 6px; border-radius: 4px; margin-right: 6px; font-weight: 600; font-size: 11px; }
.app-detail-prices .price-item .value { font-weight: 600; font-size: 13px; }
.app-detail-prices .price-item.vip { background: #fff7ed; }
.app-detail-prices .price-item.vip .label { background: #f97316; }
.app-detail-prices .price-item.vip .value { color: #ea580c; }
.app-detail-prices .price-item.svip { background: #fef3c7; }
.app-detail-prices .price-item.svip .label { background: #eab308; }
.app-detail-prices .price-item.svip .value { color: #ca8a04; }
.app-detail-prices .price-item.zhizun { background: #f3e8ff; }
.app-detail-prices .price-item.zhizun .label { background: linear-gradient(135deg, #430000 0%, #bf9500 100%); }
.app-detail-prices .price-item.zhizun .value { color: #7c3aed; }
.app-detail-prices .price-item .value.free { color: #16a34a !important; }
.app-detail-buyout { background: linear-gradient(135deg, #fff7ed 0%, #fef3c7 100%); border: 1px solid #fed7aa; border-radius: 8px; padding: 12px; margin-bottom: 15px; }
.app-detail-buyout-title { font-size: 13px; color: #92400e; margin-bottom: 8px; display: flex; align-items: center; gap: 6px; }
.app-detail-buyout-prices { display: flex; gap: 12px; flex-wrap: wrap; }
.app-detail-buyout-prices span { font-size: 12px; }
.app-detail-changelog { background: #f9f9f9; border-radius: 8px; padding: 15px; margin-bottom: 20px; }
.app-detail-changelog h4 { font-size: 14px; margin-bottom: 10px; color: #333; display: flex; align-items: center; gap: 6px; }
.app-detail-changelog ul { margin: 0; padding-left: 20px; color: #666; font-size: 13px; line-height: 1.8; }
.app-detail-actions { display: flex; gap: 10px; padding: 15px 20px; border-top: 1px solid #f0f0f0; background: #fff; flex-shrink: 0; }
.app-detail-actions .layui-btn { flex: 1; height: 42px; font-size: 14px; margin: 0; display: inline-flex; align-items: center; justify-content: center; }
@media (max-width: 768px) {
    .app-detail-modal { padding: 10px; }
    .app-detail-box { max-height: 95vh; border-radius: 8px; }
    .app-detail-header { aspect-ratio: 16 / 9; }
    .app-detail-body { padding: 15px; }
    .app-detail-title { font-size: 18px; }
    .app-detail-prices .price-item { padding: 4px 8px; }
    .app-detail-prices .price-item .label { padding: 1px 4px; font-size: 10px; }
    .app-detail-prices .price-item .value { font-size: 12px; }
    .app-detail-actions { padding: 12px 15px; }
    .app-detail-actions .layui-btn { height: 38px; font-size: 13px; }
}

/* 购买弹窗样式 */
.buy-modal-skin { border-radius: 12px !important; overflow: hidden !important; }
.buy-modal-skin .layui-layer-content { padding: 0 !important; }
.buy-modal-skin .layui-layer-btn { text-align: center !important; padding: 15px 20px !important; border-top: 1px solid #f0f0f0 !important; }
.buy-modal-skin .layui-layer-btn a { display: inline-block !important; width: auto !important; height: auto !important; line-height: 1 !important; padding: 12px 30px !important; font-size: 14px !important; border-radius: 6px !important; margin: 0 8px !important; text-decoration: none !important; border: none !important; cursor: pointer !important; }
.buy-modal-skin .layui-layer-btn .layui-layer-btn0 { background: #16baaa !important; color: #fff !important; }
.buy-modal-skin .layui-layer-btn .layui-layer-btn0:hover { background: #139d91 !important; }
.buy-modal-skin .layui-layer-btn .layui-layer-btn1 { background: #f0f0f0 !important; color: #666 !important; }
.buy-modal-skin .layui-layer-btn .layui-layer-btn1:hover { background: #e5e5e5 !important; }
/* 深色模式 - 购买弹窗 */
.buy-modal-skin.dark-mode { background: #1e1e1e !important; }
.buy-modal-skin.dark-mode .layui-layer-content { background: #1e1e1e !important; }
.buy-modal-skin.dark-mode .layui-layer-btn { background: #1e1e1e !important; border-color: #333 !important; }
.buy-modal-skin.dark-mode .layui-layer-btn .layui-layer-btn1 { background: #333 !important; color: #e0e0e0 !important; }
.dark-mode .buy-modal-header { background: linear-gradient(135deg, #1a1a2e 0%, #252540 100%) !important; }
.dark-mode .buy-modal-title { color: #e0e0e0 !important; }
.dark-mode .buy-modal-body { background: #1e1e1e !important; }
.dark-mode .buy-price-section { background: #252525 !important; }
.dark-mode .price-amount { color: #ff6b6b !important; }
.dark-mode .price-unit { color: #888 !important; }
.dark-mode .duration-label { color: #b0b0b0 !important; }
.dark-mode .duration-tip { color: #888 !important; }
.dark-mode .duration-btn { background: #333 !important; border-color: #444 !important; color: #b0b0b0 !important; }
.dark-mode .duration-input { background: #333 !important; border-color: #444 !important; color: #e0e0e0 !important; }
.dark-mode .duration-text { color: #888 !important; }
.dark-mode .preset-btn { background: #333 !important; border-color: #444 !important; color: #b0b0b0 !important; }
.dark-mode .preset-btn:hover, .dark-mode .preset-btn.active { background: #667eea !important; border-color: #667eea !important; color: #fff !important; }
.dark-mode .buy-total-section { background: #252525 !important; border-color: #333 !important; }
.dark-mode .total-label { color: #b0b0b0 !important; }
.dark-mode .total-amount { color: #ff6b6b !important; }
.dark-mode .buy-modal-footer { color: #888 !important; border-color: #333 !important; }
.dark-mode .buy-buyout-tip { background: #2d2408 !important; border-color: #5a4a00 !important; }
.dark-mode .buyout-tip-text { color: #d4a574 !important; }

.buy-modal-header { text-align: center; padding: 30px 20px 20px; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border-radius: 12px 12px 0 0; }
.buy-modal-cover { margin-bottom: 15px; }
.buy-modal-title { font-size: 18px; font-weight: 600; color: #1e293b; margin: 0; }
.buy-modal-body { padding: 25px; }
.buy-price-section { background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); border-radius: 16px; padding: 25px; margin-bottom: 20px; text-align: center; position: relative; overflow: hidden; }
.buy-price-section::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); }
.buy-price-display { margin-bottom: 20px; }
.price-amount { font-size: 36px; font-weight: 700; color: #dc2626; line-height: 1; }
.price-unit { font-size: 16px; color: #64748b; margin-left: 4px; }
.buy-duration-section { margin-bottom: 20px; }
.duration-label { display: block; font-size: 14px; color: #475569; margin-bottom: 12px; font-weight: 500; }
.duration-tip { font-size: 12px; color: #94a3b8; font-weight: 400; }
.duration-controls { display: flex; align-items: center; justify-content: center; gap: 15px; margin-bottom: 15px; }
.duration-btn { width: 44px; height: 44px; border: 2px solid #e2e8f0; background: #fff; border-radius: 12px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; font-size: 18px; color: #64748b; }
.duration-btn:hover { border-color: #667eea; color: #667eea; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15); }
.duration-btn:active { transform: translateY(0); }
.duration-display { display: flex; align-items: center; gap: 8px; }
.duration-input { width: 80px; height: 44px; border: 2px solid #e2e8f0; border-radius: 12px; text-align: center; font-size: 20px; font-weight: 600; color: #1e293b; background: #fff; transition: all 0.2s; }
.duration-input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
.duration-text { font-size: 14px; color: #64748b; font-weight: 500; }
.duration-presets { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; }
.preset-btn { padding: 8px 16px; border: 1px solid #e2e8f0; background: #fff; border-radius: 20px; font-size: 13px; color: #64748b; cursor: pointer; transition: all 0.2s; }
.preset-btn:hover, .preset-btn.active { border-color: #667eea; background: #667eea; color: #fff; transform: translateY(-1px); }
.buy-total-section { display: flex; align-items: center; justify-content: space-between; padding: 15px 20px; background: #fff; border-radius: 12px; border: 2px solid #e2e8f0; }
.total-label { font-size: 16px; color: #475569; font-weight: 500; }
.total-amount { font-size: 24px; font-weight: 700; color: #dc2626; }
.buy-buyout-tip { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 1px solid #f59e0b; border-radius: 12px; padding: 15px; margin-bottom: 15px; }
.buyout-tip-content { display: flex; align-items: center; justify-content: space-between; gap: 15px; }
.buyout-tip-text { font-size: 13px; color: #92400e; display: flex; align-items: center; gap: 6px; }
.buyout-tip-text i { color: #f59e0b; }
.buyout-btn { padding: 6px 12px; background: #f59e0b; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s; white-space: nowrap; }
.buyout-btn:hover { background: #d97706; transform: translateY(-1px); }
.buy-price-section.buyout-only { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
.buyout-label { font-size: 14px; color: rgba(255, 255, 255, 0.8); margin-bottom: 10px; }
.buyout-price { font-size: 42px; font-weight: 700; color: #fff; margin-bottom: 8px; }
.buyout-desc { font-size: 13px; color: rgba(255, 255, 255, 0.8); display: flex; align-items: center; justify-content: center; gap: 6px; }
.buy-modal-footer { text-align: center; font-size: 12px; color: #64748b; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 15px 0 0; border-top: 1px solid #f1f5f9; }
.buy-modal-footer i { color: #94a3b8; }

/* 深色模式适配 - 卡片和详情 */
html[data-theme="dark"] .store-card { background: #1e1e1e !important; }
html[data-theme="dark"] .store-card-title { color: #e0e0e0 !important; }
html[data-theme="dark"] .store-card-desc { color: #b0b0b0 !important; }
html[data-theme="dark"] .store-card-meta { border-color: #333 !important; color: #888 !important; }
html[data-theme="dark"] .app-detail-box { background: #1e1e1e !important; }
html[data-theme="dark"] .app-detail-title { color: #e0e0e0 !important; }
html[data-theme="dark"] .app-detail-desc { color: #b0b0b0 !important; }
html[data-theme="dark"] .app-detail-meta { color: #888 !important; }
html[data-theme="dark"] .app-detail-changelog { background: #2a2a2a !important; }
html[data-theme="dark"] .app-detail-changelog h4 { color: #e0e0e0 !important; }
html[data-theme="dark"] .app-detail-changelog ul { color: #b0b0b0 !important; }
html[data-theme="dark"] .filter-count { background: #333; }
html[data-theme="dark"] .filter-item.active .filter-count { background: rgba(22,186,170,.2); color: #16baaa; }

/* 深色模式适配 - 分页器 */
html[data-theme="dark"] .layui-laypage a, html[data-theme="dark"] .layui-laypage span { color: #b0b0b0 !important; background-color: #2a2a2a !important; border-color: #444 !important; }
html[data-theme="dark"] .layui-laypage a:hover { color: #16baaa !important; }
html[data-theme="dark"] .layui-laypage .layui-laypage-curr .layui-laypage-em { background-color: #16baaa !important; }
html[data-theme="dark"] .layui-laypage .layui-laypage-curr span, html[data-theme="dark"] .layui-laypage .layui-laypage-curr em { color: #fff !important; background-color: transparent !important; }
html[data-theme="dark"] .layui-laypage input, html[data-theme="dark"] .layui-laypage select { background-color: #2a2a2a !important; border-color: #444 !important; color: #e0e0e0 !important; }
html[data-theme="dark"] .layui-laypage .layui-laypage-count, html[data-theme="dark"] .layui-laypage .layui-laypage-limits, html[data-theme="dark"] .layui-laypage .layui-laypage-skip { color: #b0b0b0 !important; background-color: transparent !important; }
html[data-theme="dark"] .layui-laypage .layui-laypage-btn { background-color: #2a2a2a !important; border-color: #444 !important; color: #b0b0b0 !important; }
html[data-theme="dark"] .layui-laypage .layui-laypage-btn:hover { color: #16baaa !important; }
html[data-theme="dark"] #cards-pagination { background-color: transparent !important; }

</style>

<!-- 顶部导航 -->
<div class="layui-tabs order-tabs-wrapper" style="display:flex;align-items:center;justify-content:space-between;" lay-options="{trigger: false}">
    <ul class="layui-tabs-header">
        <li><a href="./store.php">全部应用</a></li>
        <li><a href="./store.php?action=tpl">模板主题</a></li>
        <li><a href="./store.php?action=plu">扩展插件</a></li>
        <li class="layui-this"><a href="./store.php?action=purchased">已购买</a></li>
        <li><a href="./plugin.php">已安装</a></li>
    </ul>
    <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
        <div class="layui-input-inline layui-input-wrap" style="width:180px;margin:0;">
            <input type="text" class="layui-input" id="store-search" placeholder="搜索已购..." lay-affix="clear">
        </div>
        <button type="button" class="layui-btn layui-btn-sm" id="store-search-btn">搜索</button>
        <button type="button" class="layui-btn-sm layui-btn-primary" id="store-reset-btn"><i class="ri-refresh-line"></i> 刷新</button>
    </div>
</div>

<!-- 已购买子分类筛选 -->
<div class="store-category-bar">
    <div class="purchase-filter">
    <span class="filter-item active" data-filter="all">全部 <span class="filter-count" id="count-all">0</span></span>
    <span class="filter-item" data-filter="permanent">已买断 <span class="filter-count" id="count-permanent">0</span></span>
    <span class="filter-item" data-filter="monthly">订阅中 <span class="filter-count" id="count-monthly">0</span></span>
    <span class="filter-item" data-filter="uninstalled">待安装 <span class="filter-count" id="count-uninstalled">0</span></span>
    <span class="filter-item" data-filter="expired">已到期 <span class="filter-count" id="count-expired">0</span></span>
    </div>
</div>

<div class="layui-table-view" style="border-radius:8px;overflow:hidden;">
    <div class="layui-card-header" style="display:flex;align-items:center;justify-content:center;position:relative;height:auto;padding:12px 15px;">
        <span style="position:absolute;left:15px;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">已购买</span>
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
        <div class="store-cards" id="cards-container"></div>
        <div id="cards-pagination" style="display:none; padding: 15px 0; text-align: center;"></div>
    </div>
</div>

<script type="text/html" id="cover">
    <a href="javascript:;" lay-event="img">
        {{# if(d.cover){ }}
        <img onerror="this.onerror=null; this.parentNode.innerHTML='<i class=\'ri-apps-line\' style=\'font-size:24px;color:#999;\'></i>'" src="{{ d.cover }}" style="width: 45px; height: 45px; border-radius: 6px; object-fit: cover;" />
        {{# } else { }}
        <i class="ri-apps-line" style="font-size:24px;color:#999;"></i>
        {{# } }}
    </a>
</script>

<script type="text/html" id="name">
    <div style="cursor: pointer;" lay-event="detail">
        <div><strong>{{ d.name }}</strong>{{# if(d.buy_type === 'permanent'){ }}<span style="background:#ff9800;color:#fff;font-size:10px;padding:1px 5px;border-radius:3px;margin-left:6px;">已买断</span>{{# } else if(d.is_expired == 1){ }}<span style="background:#ff4d4f;color:#fff;font-size:10px;padding:1px 5px;border-radius:3px;margin-left:6px;">已到期</span>{{# } else if(d.buy_type === 'trial'){ }}<span style="background:#9333ea;color:#fff;font-size:10px;padding:1px 5px;border-radius:3px;margin-left:6px;">试用中</span>{{# } else if(d.buy_type === 'monthly'){ }}<span style="background:#1e9fff;color:#fff;font-size:10px;padding:1px 5px;border-radius:3px;margin-left:6px;">订阅中</span>{{# } }}{{# if(d.is_install == 'y'){ }}<span style="background:#16a34a;color:#fff;font-size:10px;padding:1px 5px;border-radius:3px;margin-left:6px;">已安装</span>{{# } }}</div>
        <div style="color: #999; font-size: 12px; margin-top: 3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ d.description || '暂无描述' }}</div>
    </div>
</script>

<script type="text/html" id="expire">
    {{# if(d.buy_type === 'permanent'){ }}
    <span style="color: #ff9800; font-weight: 600;">永久</span>
    {{# } else if(d.is_expired == 1){ }}
    <span style="color: #ff4d4f;">{{ d.expire_time ? d.expire_time.split(' ')[0] : '已到期' }}</span>
    {{# } else { }}
    <span>{{ d.expire_time ? d.expire_time.split(' ')[0] : '-' }}</span>
    {{# } }}
</script>

<script type="text/html" id="operate">
    {{# if(d.is_install == 'y'){ }}
        {{# if(d.buy_type === 'permanent'){ }}
        <button class="layui-btn layui-btn-sm layui-btn-disabled">已买断</button>
        {{# } else if(d.is_expired == 1){ }}
        <button class="layui-btn layui-btn-sm layui-btn-danger layui-btn-disabled">已到期</button>
        <button class="layui-btn layui-btn-sm layui-btn-warm" lay-event="renew">续期</button>
        {{# } else { }}
        <button class="layui-btn layui-btn-sm" lay-event="showExpire">期限</button>
        <button class="layui-btn layui-btn-sm layui-btn-warm" lay-event="renew">续期</button>
        {{# } }}
    {{# } else if(d.is_expired == 1){ }}
    <button class="layui-btn layui-btn-sm layui-btn-danger layui-btn-disabled">已到期</button>
    <button class="layui-btn layui-btn-sm layui-btn-warm" lay-event="renew">续期</button>
    {{# } else if(d.buy_type === 'permanent'){ }}
    <button class="layui-btn layui-btn-sm" lay-event="install">安装</button>
    {{# } else { }}
    <button class="layui-btn layui-btn-sm" lay-event="install">安装</button>
    <button class="layui-btn layui-btn-sm layui-btn-warm" lay-event="renew">续期</button>
    {{# } }}
</script>

<script>
var currentFilter = 'all';
var storeData = [];
var allStoreData = [];
// 默认视图：localStorage 已有值时使用历史偏好；否则按视口宽度区分 —— 移动端 (≤768px) 默认卡片视图，PC 端默认列表视图。
// 用户手动切换后会自动写入 localStorage，下次访问按用户偏好。
var currentView = localStorage.getItem('store_view') || ((window.matchMedia && window.matchMedia('(max-width: 768px)').matches) ? 'card' : 'list');
var cardPageSize = 10;
var cardCurrentPage = 1;
var purchasedStats = {all: 0, permanent: 0, monthly: 0, expired: 0, uninstalled: 0};

function updateFilterCounts(stats) {
    purchasedStats = stats || {all: 0, permanent: 0, monthly: 0, expired: 0, uninstalled: 0};
    $('#count-all').text(purchasedStats.all);
    $('#count-permanent').text(purchasedStats.permanent);
    $('#count-monthly').text(purchasedStats.monthly);
    $('#count-expired').text(purchasedStats.expired);
    $('#count-uninstalled').text(purchasedStats.uninstalled);
    if (purchasedStats.expired > 0) {
        $('.filter-item[data-filter="expired"]').addClass('has-expired');
    } else {
        $('.filter-item[data-filter="expired"]').removeClass('has-expired');
    }
    // 待安装高亮
    if (purchasedStats.uninstalled > 0) {
        $('.filter-item[data-filter="uninstalled"]').addClass('has-uninstalled');
    } else {
        $('.filter-item[data-filter="uninstalled"]').removeClass('has-uninstalled');
    }
}

function renderStoreCards(pageData) {
    var dataToRender = pageData || storeData;
    if (dataToRender.length === 0) {
        var emptyMsg = '暂无已购应用';
        if (currentFilter === 'permanent') emptyMsg = '暂无已买断的应用';
        else if (currentFilter === 'monthly') emptyMsg = '暂无订阅中的应用';
        else if (currentFilter === 'expired') emptyMsg = '暂无已到期的应用';
        else if (currentFilter === 'uninstalled') emptyMsg = '暂无待安装的应用';
        $('#cards-container').removeClass('active').css({'display':'flex','justify-content':'center','align-items':'center','min-height':'300px'}).html('<div style="text-align:center;padding:50px 0;color:#999;"><i class="ri-inbox-line" style="font-size:48px;display:block;margin-bottom:10px;"></i>' + emptyMsg + '</div>');
        $('#cards-pagination').hide();
        return;
    }
    $('#cards-container').addClass('active').css({'display':'','justify-content':'','align-items':'','min-height':''});
    var html = '';
    dataToRender.forEach(function(d) {
        var coverImg = d.cover 
            ? '<img src="' + d.cover + '" onerror="this.parentNode.innerHTML=\'<i class=\\\'ri-apps-line default-icon\\\'></i>\'">'
            : '<i class="ri-apps-line default-icon"></i>';
        var statusTag = '';
        if (d.buy_type === 'permanent') {
            statusTag = '<span class="store-card-installed" style="background:rgba(255,152,0,0.9);"><i class="ri-vip-crown-line"></i> 已买断</span>';
        } else if (d.is_expired == 1) {
            statusTag = '<span class="store-card-installed" style="background:rgba(255,77,79,0.9);"><i class="ri-error-warning-line"></i> 已到期</span>';
        } else if (d.buy_type === 'trial') {
            statusTag = '<span class="store-card-installed" style="background:rgba(147,51,234,0.9);"><i class="ri-gift-line"></i> 试用中</span>';
        } else if (d.buy_type === 'monthly') {
            statusTag = '<span class="store-card-installed" style="background:rgba(30,159,255,0.9);"><i class="ri-time-line"></i> 订阅中</span>';
        }
        var installedTag = d.is_install === 'y' ? '<span class="store-card-installed" style="top:40px;"><i class="ri-check-line"></i> 已安装</span>' : '';
        
        var actionBtn = '';
        if (d.is_install === 'y') {
            if (d.buy_type === 'permanent') actionBtn = '<button class="layui-btn layui-btn-sm layui-btn-disabled">已买断</button>';
            else if (d.is_expired == 1) actionBtn = '<button class="layui-btn layui-btn-sm layui-btn-danger layui-btn-disabled">已到期</button><button class="layui-btn layui-btn-sm layui-btn-warm" data-event="renew">续期</button>';
            else actionBtn = '<button class="layui-btn layui-btn-sm" data-event="showExpire">期限</button><button class="layui-btn layui-btn-sm layui-btn-warm" data-event="renew">续期</button>';
        } else if (d.is_expired == 1) {
            actionBtn = '<button class="layui-btn layui-btn-sm layui-btn-danger layui-btn-disabled">已到期</button><button class="layui-btn layui-btn-sm layui-btn-warm" data-event="renew">续期</button>';
        } else if (d.buy_type === 'permanent') {
            actionBtn = '<button class="layui-btn layui-btn-sm" data-event="install">安装</button>';
        } else {
            actionBtn = '<button class="layui-btn layui-btn-sm" data-event="install">安装</button><button class="layui-btn layui-btn-sm layui-btn-warm" data-event="renew">续期</button>';
        }
        
        html += '<div class="store-card" data-app=\'' + JSON.stringify(d).replace(/'/g, "&#39;") + '\'>';
        html += '  <div class="store-card-cover" data-event="img">' + coverImg + statusTag + installedTag + '</div>';
        html += '  <div class="store-card-body"><div class="store-card-header"><div class="store-card-title">' + d.name + '</div><span class="store-card-version">v' + d.version + '</span></div>';
        html += '    <div class="store-card-desc">' + (d.description || '暂无描述') + '</div></div>';
        html += '  <div class="store-card-meta"><div class="meta-left"><span><i class="ri-user-line"></i>' + (d.author || '未知') + '</span></div><div class="store-card-actions">' + actionBtn + '</div></div></div>';
    });
    $('#cards-container').html(html);
}

function loadStoreData(callback) {
    var loadIndex = layer.load(2);
    $.ajax({
        url: '?action=purchased_ajax&filter=' + currentFilter + '&limit=9999', type: 'GET', dataType: 'json',
        success: function(res) { 
            allStoreData = res.data || []; 
            storeData = allStoreData;
            cardCurrentPage = 1;
            if (res.stats) updateFilterCounts(res.stats);
            if (callback) callback(); 
        },
        error: function() { layer.msg('加载失败'); },
        complete: function() { layer.close(loadIndex); }
    });
}

function renderCardPagination() {
    if (allStoreData.length === 0) { 
        $('#cards-pagination').hide(); 
        renderStoreCards([]); // 显示空提示
        return; 
    }
    layui.use('laypage', function(){
        var laypage = layui.laypage;
        laypage.render({
            elem: 'cards-pagination', count: allStoreData.length, limit: cardPageSize, curr: cardCurrentPage, limits: [10, 20, 50],
            layout: ['count', 'prev', 'page', 'next', 'limit', 'skip'],
            jump: function(obj, first){
                cardCurrentPage = obj.curr; cardPageSize = obj.limit;
                var start = (obj.curr - 1) * obj.limit;
                var pageData = allStoreData.slice(start, start + obj.limit);
                renderStoreCards(pageData);
            }
        });
    });
    $('#cards-pagination').show();
}

var tableInstance = null;

function loadStats() {
    $.ajax({
        url: '?action=purchased_ajax&filter=all&limit=1', type: 'GET', dataType: 'json',
        success: function(res) { if (res.stats) updateFilterCounts(res.stats); }
    });
}

layui.use(['table'], function(){
    var table = layui.table;
    tableInstance = table;
    var $ = layui.$;
    
    loadStats();

    $('.filter-item').on('click', function(){
        var filter = $(this).data('filter');
        currentFilter = filter;
        $('.filter-item').removeClass('active');
        $(this).addClass('active');
        
        // 根据筛选类型设置空数据提示
        var emptyMsg = '暂无已购应用';
        if (filter === 'permanent') emptyMsg = '暂无已买断的应用';
        else if (filter === 'monthly') emptyMsg = '暂无订阅中的应用';
        else if (filter === 'expired') emptyMsg = '暂无已到期的应用';
        else if (filter === 'uninstalled') emptyMsg = '暂无待安装的应用';
        
        table.reload('index', { url: '?action=purchased_ajax&filter=' + filter, text: { none: emptyMsg } });
        if (currentView === 'card') loadStoreData(function(){ renderCardPagination(); });
    });

    table.render({
        elem: '#index', url: '?action=purchased_ajax&filter=' + currentFilter, page: true, limits: [10, 20, 50], lineStyle: 'height: 65px;',
        text: { none: '暂无已购应用' },
        cols: [[
            {field: 'cover', title: '图标', width: 75, templet: '#cover', align: 'center'},
            {field: 'name', title: '应用名称', minWidth: 300, templet: '#name'},
            {field: 'expire_time', title: '到期时间', width: 120, align: 'center', templet: '#expire'},
            {field: 'author', title: '作者', width: 100, align: 'center'},
            {field: 'version', title: '版本', width: 90, align: 'center'},
            {fixed: 'right', title: '操作', width: 200, align: 'center', templet: '#operate'}
        ]],
        done: function(res) { 
            if (res.data) storeData = res.data; 
            if (res.stats) updateFilterCounts(res.stats);
        }
    });

    table.on('tool(index)', function(obj){ handleAction(obj.event, obj.data, table); });
    
    // 刷新按钮（原重置+刷新合并）
    $('#store-reset-btn').on('click', function() {
        var $icon = $(this).find('i');
        $icon.css('transition','transform 0.5s').css('transform','rotate(360deg)');
        setTimeout(function(){ $icon.css('transition','none').css('transform',''); }, 500);
        $('#store-search').val('');
        $('.filter-item').removeClass('active').first().addClass('active');
        currentFilter = 'all';
        table.reload('index', { url: '?action=purchased_ajax&filter=all', page: { curr: 1 } });
        if (currentView === 'card') loadStoreData(function(){ renderCardPagination(); });
    });

    // 搜索功能
    $('#store-search-btn').on('click', function() {
        var keyword = $('#store-search').val().trim();
        filterPurchasedApps(keyword);
    });
    
    $('#store-search').on('keypress', function(e) {
        if (e.which === 13) {
            var keyword = $(this).val().trim();
            filterPurchasedApps(keyword);
        }
    });
    
    function filterPurchasedApps(keyword) {
        // 前端过滤已购买的应用
        if (currentView === 'card') {
            if (!keyword) {
                loadStoreData(function(){ renderCardPagination(); });
            } else {
                keyword = keyword.toLowerCase();
                var filtered = allStoreData.filter(function(app) {
                    var name = (app.name || '').toLowerCase();
                    var desc = (app.description || '').toLowerCase();
                    return name.indexOf(keyword) > -1 || desc.indexOf(keyword) > -1;
                });
                var tempData = allStoreData;
                allStoreData = filtered;
                cardCurrentPage = 1;
                renderCardPagination();
                allStoreData = tempData;
            }
        } else {
            table.reload('index', { page: { curr: 1 }, where: { keyword: keyword } });
        }
    }
    
    $('.view-btn').on('click', function() {
        var view = $(this).data('view');
        if (view === currentView) return;
        currentView = view;
        localStorage.setItem('store_view', view);
        $('.view-btn').removeClass('active');
        $(this).addClass('active');
        if (view === 'card') {
            $('#index-container').addClass('hidden');
            $('#cards-container').addClass('active');
            if (allStoreData.length === 0) loadStoreData(function(){ renderCardPagination(); });
            else renderCardPagination();
        } else {
            $('#index-container').removeClass('hidden');
            $('#cards-container').removeClass('active').css({'display':'','justify-content':'','align-items':'','min-height':''}).html('');
            $('#cards-pagination').hide();
        }
    });
    
    $('#cards-container').on('click', '[data-event]', function(e) {
        e.stopPropagation();
        var event = $(this).data('event');
        var data = $(this).closest('.store-card').data('app');
        handleAction(event, data, table);
    });
    
    $('.view-btn').removeClass('active');
    $('.view-btn[data-view="' + currentView + '"]').addClass('active');
    if (currentView === 'card') {
        $('#index-container').addClass('hidden');
        $('#cards-container').addClass('active');
        loadStoreData(function(){ renderCardPagination(); });
    }
});

function getPurchasedInstallType(data) {
    var type = (data && data.type) ? String(data.type) : 'plugin';
    if (type === 'home_template' || type === 'station_template' || type === 'tpl' || type === 'home' || type === 'station') type = 'template';
    if (type === 'user' || type === 'user_tpl') type = 'user_template';
    if (type === 'bottom_nav' || type === 'bottom_nav_tpl') type = 'bottom_nav_template';
    if (type === 'blog' || type === 'blog_tpl') type = 'blog_template';
    var allow = ['plugin', 'template', 'user_template', 'bottom_nav_template', 'blog_template'];
    return allow.indexOf(type) >= 0 ? type : 'plugin';
}

function getPurchasedManageUrl(data) {
    var type = getPurchasedInstallType(data);
    var s = data.name ? encodeURIComponent(data.name) : '';
    if (type === 'plugin') return './plugin.php' + (s ? '?search=' + s : '');
    if (type === 'user_template') return './template.php?tab=user' + (s ? '&search=' + s : '');
    if (type === 'bottom_nav_template') return './template.php?tab=bottom_nav' + (s ? '&search=' + s : '');
    if (type === 'blog_template') return './template.php?tab=blog' + (s ? '&search=' + s : '');
    return './template.php?tab=home' + (s ? '&search=' + s : '');
}

function isPurchasedTemplate(data) {
    return getPurchasedInstallType(data) !== 'plugin';
}

/**
 * 通用异步安装函数（CDN兼容）
 */
function asyncInstallApp(pluginId, type, appName, onSuccess, onFail) {
    var loadIdx = layer.load(2, {shade: [0.3, '#000']});
    $.post('./store.php?action=install', { type: type, plugin_id: pluginId }, function(res) {
        if (res.code !== 0) {
            layer.close(loadIdx);
            if (onFail) onFail(res.msg || '安装请求失败');
            else layer.msg(res.msg || '安装请求失败', {icon: 2});
            return;
        }
        var pollCount = 0;
        var pollTimer = setInterval(function() {
            pollCount++;
            if (pollCount > 90) { clearInterval(pollTimer); layer.close(loadIdx); if (onFail) onFail('安装超时，请重试'); else layer.msg('安装超时，请重试', {icon: 2}); return; }
            $.ajax({ url: './store.php?action=install_progress', type: 'GET', dataType: 'json', timeout: 5000, success: function(pRes) {
                if (pRes.code !== 0) return;
                var task = pRes.data || {};
                if (task.status === 'completed') { clearInterval(pollTimer); layer.close(loadIdx); if (onSuccess) onSuccess(); }
                else if (task.status === 'failed') { clearInterval(pollTimer); layer.close(loadIdx); if (onFail) onFail(task.error || '安装失败'); else layer.msg(task.error || '安装失败', {icon: 2}); }
                else if (task.status === 'expired') { clearInterval(pollTimer); layer.close(loadIdx); if (onFail) onFail('安装超时，请重试'); else layer.msg('安装超时，请重试', {icon: 2}); }
            }});
        }, 2000);
    }, 'json').fail(function(xhr) {
        layer.close(loadIdx);
        var msg = '网络请求失败';
        try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e) {}
        if (onFail) onFail(msg); else layer.msg(msg, {icon: 2});
    });
}

function handleAction(event, data, table) {
    var tbl = table || tableInstance;
    
    function refreshData() {
        if(tbl) tbl.reload('index');
        if (currentView === 'card') loadStoreData(function(){ renderCardPagination(); });
    }
    
    if(event === 'showExpire') {
        var expireText = data.expire_time ? '到期时间：' + data.expire_time.split(' ')[0] : '永久有效（已买断）';
        layer.msg(expireText, {icon: 1, time: 3000});
        return;
    }
    
    if(event === 'renew') {
        event = 'buy';
    }
    
    if(event === 'install'){
        asyncInstallApp(data.id, getPurchasedInstallType(data), data.name, function(){
            layer.open({
                type: 1, title: '安装成功', area: '400px',
                content: '<div style="padding:20px;text-align:center;"><i class="ri-checkbox-circle-fill" style="font-size:48px;color:#16a34a;"></i><div style="margin:15px 0;font-size:16px;">应用「' + data.name + '」已安装</div></div>',
                btn: ['去启用', '稍后再说'],
                yes: function(i) { layer.close(i); location.href = getPurchasedManageUrl(data); },
                btn2: function(i) { layer.close(i); refreshData(); }
            });
        }, function(err){ layer.msg(err, {icon: 2}); });
    }
    
    if(event === 'buy'){
        var hasMonthly = data.has_monthly == 1;
        var hasBuyoutPrice = (data.vip_price > 0 || data.svip_price > 0 || data.price_supreme > 0 || data.my_price > 0);
        var allowBuyout = data.allow_buyout != 0 && hasBuyoutPrice;
        var monthlyPrice = data.my_price_monthly || 0;
        // 所有等级的买断价和月付价都为0才是真正免费（静默获取）
        // 某等级专属免费时仍弹购买窗（显示"免费"仪式感）
        var allBuyoutFree = (data.vip_price == 0 && data.svip_price == 0 && data.price_supreme == 0);
        var allMonthlyFree = (!hasMonthly || (data.price_monthly_vip == 0 && data.price_monthly_svip == 0 && data.price_monthly_supreme == 0));
        var isFree = (data.my_price == 0 && data.my_price_monthly == 0 && allBuyoutFree && allMonthlyFree);
        
        if (isFree) {
            layer.load(2);
            $.post('./store.php?action=buy', { app_id: data.id, buy_type: 'permanent', months: 1 }, function(res){
                layer.closeAll();
                if(res.code == 0) {
                    layer.open({
                        type: 1, title: '获取成功', area: '400px',
                        content: '<div style="padding:20px;text-align:center;"><i class="ri-checkbox-circle-fill" style="font-size:48px;color:#16a34a;"></i><div style="margin:15px 0;font-size:16px;">应用「' + data.name + '」已添加到您的应用列表</div></div>',
                        btn: ['立即安装', '稍后再说'],
                        yes: function(idx) { 
                            layer.close(idx);
                            asyncInstallApp(data.id, getPurchasedInstallType(data), data.name, function(){
                                layer.open({
                                    type: 1, title: '安装成功', area: '400px',
                                    content: '<div style="padding:20px;text-align:center;"><i class="ri-checkbox-circle-fill" style="font-size:48px;color:#16a34a;"></i><div style="margin:15px 0;font-size:16px;">应用「' + data.name + '」已安装</div></div>',
                                    btn: ['去启用', '稍后再说'],
                                    yes: function(i) { layer.close(i); location.href = getPurchasedManageUrl(data); },
                                    btn2: function(i) { layer.close(i); refreshData(); }
                                });
                            }, function(err){ layer.msg(err, {icon: 2}); });
                        },
                        btn2: function(idx) { layer.close(idx); refreshData(); }
                    });
                } else { if(res.data && res.data.need_recharge) layer.confirm(res.msg + '<br><br>是否前往授权中心充值？', {icon: 2, title: '余额不足'}, function(){ window.open('<?= $licenseCenterUrl ?>'); }); else layer.msg(res.msg, {icon: 2}); }
            }, 'json').fail(function(xhr){ layer.closeAll(); layer.msg(xhr.responseJSON ? xhr.responseJSON.msg : '获取失败', {icon: 2}); });
            return;
        }
        
        var coverHtml = data.cover 
            ? '<img src="' + data.cover + '" style="width: 80px; height: 80px; border-radius: 16px; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.15);" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\'"><div style="display:none;width:80px;height:80px;border-radius:16px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);align-items:center;justify-content:center;"><i class="ri-apps-line" style="font-size:36px;color:#fff;"></i></div>'
            : '<div style="width:80px;height:80px;border-radius:16px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);display:flex;align-items:center;justify-content:center;"><i class="ri-apps-line" style="font-size:36px;color:#fff;"></i></div>';
        
        var buyOptionsHtml = '<div class="buy-modal-header">';
        buyOptionsHtml += '<div class="em-modal-close-btn" onclick="layer.closeAll()"><svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></div>';
        buyOptionsHtml += '<div class="buy-modal-cover">' + coverHtml + '</div>';
        buyOptionsHtml += '<div class="buy-modal-title">' + data.name + '</div>';
        buyOptionsHtml += '</div>';
        
        buyOptionsHtml += '<div class="buy-modal-body">';
        if (hasMonthly) {
            buyOptionsHtml += '<input type="hidden" name="buy_type" value="monthly">';
            buyOptionsHtml += '<input type="hidden" id="buy-months" value="1">';
            buyOptionsHtml += '<div class="buy-price-section">';
            buyOptionsHtml += '<div class="buy-price-display">';
            buyOptionsHtml += '<span class="price-amount">¥' + monthlyPrice + '</span>';
            buyOptionsHtml += '<span class="price-unit">/月</span>';
            buyOptionsHtml += '</div>';
            buyOptionsHtml += '<div class="buy-duration-section">';
            buyOptionsHtml += '<label class="duration-label">购买时长 <span class="duration-tip">(一次最长24个月)</span></label>';
            buyOptionsHtml += '<div class="duration-controls">';
            buyOptionsHtml += '<button type="button" class="duration-btn duration-btn-minus" onclick="changeMonths(-1, ' + monthlyPrice + ')"><i class="ri-subtract-line"></i></button>';
            buyOptionsHtml += '<div class="duration-display">';
            buyOptionsHtml += '<input type="number" id="months-input" class="duration-input" value="1" min="1" max="24" onchange="updateMonthsFromInput(' + monthlyPrice + ')" onkeyup="updateMonthsFromInput(' + monthlyPrice + ')">';
            buyOptionsHtml += '<span class="duration-text">个月</span>';
            buyOptionsHtml += '</div>';
            buyOptionsHtml += '<button type="button" class="duration-btn duration-btn-plus" onclick="changeMonths(1, ' + monthlyPrice + ')"><i class="ri-add-line"></i></button>';
            buyOptionsHtml += '</div>';
            buyOptionsHtml += '<div class="duration-presets">';
            buyOptionsHtml += '<button type="button" class="preset-btn" onclick="setMonths(3, ' + monthlyPrice + ')">3个月</button>';
            buyOptionsHtml += '<button type="button" class="preset-btn" onclick="setMonths(6, ' + monthlyPrice + ')">6个月</button>';
            buyOptionsHtml += '<button type="button" class="preset-btn" onclick="setMonths(12, ' + monthlyPrice + ')">1年</button>';
            buyOptionsHtml += '</div>';
            buyOptionsHtml += '</div>';
            buyOptionsHtml += '<div class="buy-total-section">';
            buyOptionsHtml += '<span class="total-label">合计金额</span>';
            buyOptionsHtml += '<span id="total-price" class="total-amount">¥' + monthlyPrice + '</span>';
            buyOptionsHtml += '</div>';
            if (allowBuyout) {
                buyOptionsHtml += '<div class="buy-buyout-tip">';
                buyOptionsHtml += '<div class="buyout-tip-content">';
                buyOptionsHtml += '<div class="buyout-tip-text"><i class="ri-gift-line"></i> 支持买断，永久使用</div>';
                var levelNames = ['未授权', 'VIP', 'SVIP', '至尊'];
                var myLevelName = levelNames[data.user_level] || 'VIP';
                var _buyoutPrice = (data.my_price != null ? data.my_price : data.vip_price) || 0;
                buyOptionsHtml += '<button type="button" class="buyout-btn" onclick="switchToBuyout()">' + myLevelName + '买断价：' + (_buyoutPrice == 0 ? '免费' : '¥' + _buyoutPrice) + '</button>';
                buyOptionsHtml += '</div>';
                buyOptionsHtml += '</div>';
            }
        } else if (allowBuyout) {
            buyOptionsHtml += '<input type="hidden" name="buy_type" value="permanent">';
            buyOptionsHtml += '<div class="buy-price-section buyout-only">';
            var levelNames2 = ['未授权', 'VIP', 'SVIP', '至尊'];
            var myLevelName2 = levelNames2[data.user_level] || 'VIP';
            buyOptionsHtml += '<div class="buyout-label">' + myLevelName2 + '买断价</div>';
            var _buyoutPrice2 = (data.my_price != null ? data.my_price : data.vip_price) || 0;
            buyOptionsHtml += '<div class="buyout-price">' + (_buyoutPrice2 == 0 ? '免费' : '¥' + _buyoutPrice2) + '</div>';
            buyOptionsHtml += '<div class="buyout-desc">一次付费，永久使用</div>';
            buyOptionsHtml += '</div>';
        }
        buyOptionsHtml += '<div class="buy-modal-footer">';
        buyOptionsHtml += '<i class="ri-wallet-3-line"></i> 将从您的授权中心账户余额中扣除';
        buyOptionsHtml += '</div>';
        buyOptionsHtml += '</div>';

        window.switchToBuyout = function() {
            layer.closeAll();
            var coverHtml = data.cover 
                ? '<img src="' + data.cover + '" style="width: 80px; height: 80px; border-radius: 16px; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.15);" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\'"><div style="display:none;width:80px;height:80px;border-radius:16px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);align-items:center;justify-content:center;"><i class="ri-apps-line" style="font-size:36px;color:#fff;"></i></div>'
                : '<div style="width:80px;height:80px;border-radius:16px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);display:flex;align-items:center;justify-content:center;"><i class="ri-apps-line" style="font-size:36px;color:#fff;"></i></div>';
            
            var buyoutHtml = '<div class="buy-modal-header">';
            buyoutHtml += '<div class="em-modal-close-btn" onclick="layer.closeAll()"><svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></div>';
            buyoutHtml += '<div class="buy-modal-cover">' + coverHtml + '</div>';
            buyoutHtml += '<div class="buy-modal-title">' + data.name + '</div>';
            buyoutHtml += '</div>';
            buyoutHtml += '<div class="buy-modal-body">';
            buyoutHtml += '<input type="hidden" name="buy_type" value="permanent">';
            buyoutHtml += '<div class="buy-price-section buyout-only">';
            var levelNames3 = ['未授权', 'VIP', 'SVIP', '至尊'];
            var myLevelName3 = levelNames3[data.user_level] || 'VIP';
            buyoutHtml += '<div class="buyout-label">' + myLevelName3 + '买断价</div>';
            var _buyoutPrice3 = (data.my_price != null ? data.my_price : data.vip_price) || 0;
            buyoutHtml += '<div class="buyout-price">' + (_buyoutPrice3 == 0 ? '免费' : '¥' + _buyoutPrice3) + '</div>';
            buyoutHtml += '<div class="buyout-desc"><i class="ri-infinity-line"></i> 一次付费，永久使用</div>';
            buyoutHtml += '</div>';
            if (hasMonthly) {
                buyoutHtml += '<div style="text-align: center; margin-bottom: 15px;"><a href="javascript:;" onclick="layer.closeAll();handleAction(\'buy\', window.currentBuyData);" style="color: #1e9fff; font-size: 13px;"><i class="ri-arrow-left-line"></i> 返回月付</a></div>';
            }
            buyoutHtml += '<div class="buy-modal-footer"><i class="ri-wallet-3-line"></i> 将从您的授权中心账户余额中扣除</div>';
            buyoutHtml += '</div>';
            
            layer.open({ 
                type: 1, title: false, closeBtn: 0, area: '420px', shadeClose: true, content: buyoutHtml, btn: ['确认买断', '取消'], btnAlign: 'c', skin: 'buy-modal-skin',
                success: function(layero) { if (document.documentElement.getAttribute('data-theme') === 'dark') { $(layero).addClass('dark-mode'); } },
                yes: function(index){
                layer.close(index); layer.load(2);
                $.post('./store.php?action=buy', { app_id: data.id, buy_type: 'permanent', months: 1 }, function(res){
                    layer.closeAll();
                    if(res.code == 0) {
                        layer.open({
                            type: 1, title: '购买成功', area: '400px',
                            content: '<div style="padding:20px;text-align:center;"><i class="ri-checkbox-circle-fill" style="font-size:48px;color:#16a34a;"></i><div style="margin:15px 0;font-size:16px;">应用「' + data.name + '」已买断</div></div>',
                            btn: ['立即安装', '稍后再说'],
                            yes: function(idx) {
                                layer.close(idx);
                                asyncInstallApp(data.id, getPurchasedInstallType(data), data.name, function(){
                                    layer.open({
                                        type: 1, title: '安装成功', area: '400px',
                                        content: '<div style="padding:20px;text-align:center;"><i class="ri-checkbox-circle-fill" style="font-size:48px;color:#16a34a;"></i><div style="margin:15px 0;font-size:16px;">应用「' + data.name + '」已安装</div></div>',
                                        btn: ['去启用', '稍后再说'],
                                        yes: function(i) { layer.close(i); location.href = getPurchasedManageUrl(data); },
                                        btn2: function(i) { layer.close(i); refreshData(); }
                                    });
                                }, function(err){ layer.msg(err, {icon: 2}); });
                            },
                            btn2: function(idx) { layer.close(idx); refreshData(); }
                        });
                    } else { if(res.data && res.data.need_recharge) layer.confirm(res.msg + '<br><br>是否前往授权中心充值？', {icon: 2, title: '余额不足'}, function(){ window.open('<?= $licenseCenterUrl ?>'); }); else layer.msg(res.msg, {icon: 2}); }
                }, 'json').fail(function(xhr){ layer.closeAll(); layer.msg(xhr.responseJSON ? xhr.responseJSON.msg : '购买失败', {icon: 2}); });
            }});
        };
        
        window.changeMonths = function(delta, price) {
            var current = parseInt($('#buy-months').val()) || 1;
            var newVal = Math.max(1, Math.min(24, current + delta));
            $('#buy-months').val(newVal);
            $('#months-input').val(newVal);
            $('#total-price').text('¥' + (price * newVal));
        };
        
        window.updateMonthsFromInput = function(price) {
            var inputVal = parseInt($('#months-input').val()) || 1;
            var newVal = Math.max(1, Math.min(24, inputVal));
            if (inputVal !== newVal) { $('#months-input').val(newVal); }
            $('#buy-months').val(newVal);
            $('#total-price').text('¥' + (price * newVal));
        };
        
        window.setMonths = function(months, price) {
            $('#buy-months').val(months);
            $('#months-input').val(months);
            $('#total-price').text('¥' + (price * months));
            $('.preset-btn').removeClass('active');
            $('.preset-btn').each(function() {
                if ($(this).text() === months + '个月' || (months === 12 && $(this).text() === '1年')) { $(this).addClass('active'); }
            });
        };
        
        window.currentBuyData = data;

        layer.open({ 
            type: 1, title: false, closeBtn: 0, area: '420px', shadeClose: true, content: buyOptionsHtml, btn: ['确认购买', '取消'], btnAlign: 'c', skin: 'buy-modal-skin',
            success: function(layero) { if (document.documentElement.getAttribute('data-theme') === 'dark') { $(layero).addClass('dark-mode'); } },
            yes: function(index){
            var buyType = $('input[name=buy_type]').val() || 'monthly';
            var months = buyType === 'monthly' ? (parseInt($('#buy-months').val()) || 1) : 1;
            layer.close(index); layer.load(2);
            $.post('./store.php?action=buy', { app_id: data.id, buy_type: buyType, months: months }, function(res){
                layer.closeAll();
                var successTitle = buyType === 'monthly' ? '订阅成功' : '购买成功';
                var successText = buyType === 'monthly' ? '应用「' + data.name + '」已订阅' + months + '个月' : '应用「' + data.name + '」已购买';
                if(res.code == 0) {
                    layer.open({
                        type: 1, title: successTitle, area: '400px',
                        content: '<div style="padding:20px;text-align:center;"><i class="ri-checkbox-circle-fill" style="font-size:48px;color:#16a34a;"></i><div style="margin:15px 0;font-size:16px;">' + successText + '</div></div>',
                        btn: ['立即安装', '稍后再说'],
                        yes: function(idx) {
                            layer.close(idx);
                            asyncInstallApp(data.id, getPurchasedInstallType(data), data.name, function(){
                                layer.open({
                                    type: 1, title: '安装成功', area: '400px',
                                    content: '<div style="padding:20px;text-align:center;"><i class="ri-checkbox-circle-fill" style="font-size:48px;color:#16a34a;"></i><div style="margin:15px 0;font-size:16px;">应用「' + data.name + '」已安装</div></div>',
                                    btn: ['去启用', '稍后再说'],
                                    yes: function(i) { layer.close(i); location.href = getPurchasedManageUrl(data); },
                                    btn2: function(i) { layer.close(i); refreshData(); }
                                });
                            }, function(err){ layer.msg(err, {icon: 2}); });
                        },
                        btn2: function(idx) { layer.close(idx); refreshData(); }
                    });
                } else { if(res.data && res.data.need_recharge) layer.confirm(res.msg + '<br><br>是否前往授权中心充值？', {icon: 2, title: '余额不足'}, function(){ window.open('<?= $licenseCenterUrl ?>'); }); else layer.msg(res.msg, {icon: 2}); }
            }, 'json').fail(function(xhr){ layer.closeAll(); layer.msg(xhr.responseJSON ? xhr.responseJSON.msg : '购买失败', {icon: 2}); });
        }});
    }
    
    if(event === 'img') showAppDetail(data);
    if(event === 'detail') showAppDetail(data);
}

function showAppDetail(app) {
    var changelogHtml = '';
    if (app.changelog) {
        var logs = app.changelog.split('\n').filter(function(l){ return l.trim(); });
        if (logs.length > 0) {
            changelogHtml = '<div class="app-detail-changelog"><h4><i class="ri-file-list-3-line"></i> 更新日志 (v' + app.version + ')</h4><ul>';
            logs.forEach(function(log){ changelogHtml += '<li>' + escapeHtml(log) + '</li>'; });
            changelogHtml += '</ul></div>';
        }
    }
    
    var pricesHtml = '<div class="app-detail-prices">';
    if (app.has_monthly) {
        pricesHtml += '<div class="price-item vip"><span class="label">VIP</span><span class="value' + (app.price_monthly_vip == 0 ? ' free' : '') + '">' + (app.price_monthly_vip == 0 ? '免费' : '¥' + app.price_monthly_vip + '/月') + '</span></div>';
        pricesHtml += '<div class="price-item svip"><span class="label">SVIP</span><span class="value' + (app.price_monthly_svip == 0 ? ' free' : '') + '">' + (app.price_monthly_svip == 0 ? '免费' : '¥' + app.price_monthly_svip + '/月') + '</span></div>';
        pricesHtml += '<div class="price-item zhizun"><span class="label">至尊</span><span class="value' + (app.price_monthly_supreme == 0 ? ' free' : '') + '">' + (app.price_monthly_supreme == 0 ? '免费' : '¥' + app.price_monthly_supreme + '/月') + '</span></div>';
        pricesHtml += '</div>';
        if (app.allow_buyout != 0 && (app.vip_price > 0 || app.svip_price > 0 || app.price_supreme > 0)) {
            pricesHtml += '<div class="app-detail-buyout">';
            pricesHtml += '<div class="app-detail-buyout-title"><i class="ri-vip-crown-line"></i> 支持买断（一次付费，永久使用）</div>';
            pricesHtml += '<div class="app-detail-buyout-prices">';
            pricesHtml += '<span style="color:#ea580c;font-weight:600;">VIP买断价： ¥' + app.vip_price + '</span>';
            pricesHtml += '<span style="color:#ca8a04;font-weight:600;">SVIP买断价： ¥' + app.svip_price + '</span>';
            pricesHtml += '<span style="color:#7c3aed;font-weight:600;">至尊买断价： ' + (app.price_supreme == 0 ? '免费' : '¥' + app.price_supreme) + '</span>';
            pricesHtml += '</div></div>';
        }
    } else {
        pricesHtml += '<div class="price-item vip"><span class="label">VIP</span><span class="value' + (app.vip_price == 0 ? ' free' : '') + '">' + (app.vip_price == 0 ? '免费' : '¥' + app.vip_price) + '</span></div>';
        pricesHtml += '<div class="price-item svip"><span class="label">SVIP</span><span class="value' + (app.svip_price == 0 ? ' free' : '') + '">' + (app.svip_price == 0 ? '免费' : '¥' + app.svip_price) + '</span></div>';
        pricesHtml += '<div class="price-item zhizun"><span class="label">至尊</span><span class="value' + (app.price_supreme == 0 ? ' free' : '') + '">' + (app.price_supreme == 0 ? '免费' : '¥' + app.price_supreme) + '</span></div>';
        pricesHtml += '</div>';
    }
    
    var actionsHtml = '';
    if (app.is_install === 'y') actionsHtml = '<button class="layui-btn layui-btn-disabled" style="flex:1;">已安装</button>';
    else if (app.is_expired == 1) actionsHtml = '<button class="layui-btn layui-btn-warm" onclick="doDetailAction(\'renew\')" style="flex:1;"><i class="ri-refresh-line"></i> 续期</button>';
    else if (app.buy_type === 'permanent') actionsHtml = '<button class="layui-btn" onclick="doDetailAction(\'install\')" style="flex:1;"><i class="ri-download-line"></i> 安装</button>';
    else actionsHtml = '<button class="layui-btn" onclick="doDetailAction(\'install\')" style="flex:1;"><i class="ri-download-line"></i> 安装</button><button class="layui-btn layui-btn-warm" onclick="doDetailAction(\'renew\')" style="flex:1;margin-left:10px;"><i class="ri-refresh-line"></i> 续期</button>';
    
    var statusTags = '';
    if (app.is_install === 'y') {
        statusTags += '<span style="background:#16a34a;color:#fff;font-size:10px;padding:2px 6px;border-radius:3px;margin-left:8px;">已安装</span>';
    }
    if (app.buy_type === 'permanent') {
        statusTags += '<span style="background:#ff9800;color:#fff;font-size:10px;padding:2px 6px;border-radius:3px;margin-left:8px;">已买断</span>';
    } else if (app.buy_type === 'trial' && app.is_expired != 1) {
        statusTags += '<span style="background:#9333ea;color:#fff;font-size:10px;padding:2px 6px;border-radius:3px;margin-left:8px;">试用中</span>';
    } else if (app.buy_type === 'monthly' && app.is_expired != 1) {
        statusTags += '<span style="background:#1e9fff;color:#fff;font-size:10px;padding:2px 6px;border-radius:3px;margin-left:8px;">订阅中</span>';
    } else if (app.is_expired == 1) {
        statusTags += '<span style="background:#ff4d4f;color:#fff;font-size:10px;padding:2px 6px;border-radius:3px;margin-left:8px;">已到期</span>';
    }
    
    var expireInfo = '';
    if (app.buy_type === 'permanent') {
        expireInfo = '<span><i class="ri-infinity-line"></i> 永久有效</span>';
    } else if (app.expire_time) {
        expireInfo = '<span style="color:' + (app.is_expired == 1 ? '#ff4d4f' : 'inherit') + ';"><i class="ri-calendar-line"></i> 到期：' + app.expire_time.split(' ')[0] + '</span>';
    }
    
    var typeIcon = isPurchasedTemplate(app) ? 'ri-palette-line' : 'ri-plug-line';
    var typeNameMap = {template:'首页模板', user_template:'用户后台模板', bottom_nav_template:'底部导航模板', blog_template:'博客模板'};
    var typeName = isPurchasedTemplate(app) ? (typeNameMap[getPurchasedInstallType(app)] || '模板') : '插件';
    
    var html = '<div class="app-detail-modal" onclick="closeAppDetail()"><div class="app-detail-box" onclick="event.stopPropagation()"><div class="app-detail-header">' +
        (app.cover ? '<img src="' + escapeHtml(app.cover) + '">' : '') + '<button class="app-detail-close" onclick="closeAppDetail()"><i class="ri-close-line"></i></button></div>' +
        '<div class="app-detail-body"><div class="app-detail-title">' + escapeHtml(app.name) + statusTags + '</div>' +
        '<div class="app-detail-meta"><span><i class="' + typeIcon + '"></i> ' + typeName + '</span><span><i class="ri-price-tag-3-line"></i> v' + app.version + '</span><span><i class="ri-user-line"></i> ' + escapeHtml(app.author || '未知') + '</span>' + expireInfo + '</div>' +
        '<div class="app-detail-desc">' + escapeHtml(app.description || '暂无描述') + '</div>' + pricesHtml + changelogHtml +
        '</div><div class="app-detail-actions">' + actionsHtml + '</div></div></div>';
    window.currentDetailApp = app;
    $('body').append(html);
}
function closeAppDetail() { $('.app-detail-modal').remove(); window.currentDetailApp = null; }
function doDetailAction(action) { var app = window.currentDetailApp; closeAppDetail(); if(app) handleAction(action, app); }
function escapeHtml(str) { if (!str) return ''; return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }
$(document).on('keydown', function(e) { if (e.key === 'Escape') closeAppDetail(); });

$(function(){ $("#menu-store").addClass('open'); $("#menu-store > .submenu").show(); $("#menu-store > .link .admin-arrow").addClass('active'); $("#menu-store-list").addClass('active'); });
</script>