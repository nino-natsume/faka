<?php
defined('DC_ROOT') || exit('access denied!');
$token = LoginAuth::genToken();
$selectedGoodsId = Input::getIntVar('goods_id', 0);
$isPopup = !empty($isPopup);
?>

<!-- ==================== 样式 ==================== -->
<style>
/* 外层卡片壳 —— 对齐其他现代后台页 */
.stk-wrap {
    background: linear-gradient(0deg,#fff,#f3f5f8);
    border: 2px solid #fff;
    border-radius: 8px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    overflow: hidden;
    margin-bottom: 20px;
}
.stk-header { position:relative; height:auto; padding:12px 15px; border-bottom:1px solid #f0f0f0; display:flex; align-items:center; justify-content:center; }
.stk-mac-dots { display:flex; align-items:center; gap:6px; position:absolute; left:15px; top:50%; transform:translateY(-50%); }
.stk-mac-dots i { width:12px; height:12px; border-radius:50%; display:inline-block; }
.stk-mac-dots .d1 { background:#ff5f57; }
.stk-mac-dots .d2 { background:#febc2e; }
.stk-mac-dots .d3 { background:#28c840; }
.stk-title-text { color:#667797; font-size:14px; font-weight:500; }
.stk-body { padding: 14px 16px 18px; }

/* 引导面板 */
.stk-empty { display:flex; justify-content:center; align-items:center; min-height:380px; }
.stk-empty-card { text-align:center; padding:50px 60px; background:#fff; border-radius:12px; box-shadow:0 4px 24px rgba(0,0,0,.04); border:1px dashed #d9d9d9; }
.stk-empty-card .ico { font-size:72px; color:#c4cdd8; line-height:1; margin-bottom:18px; }
.stk-empty-card h3 { font-size:20px; color:#333; margin:0 0 8px; font-weight:600; }
.stk-empty-card p { color:#999; margin:0 0 24px; font-size:14px; }
.stk-btn-primary { background:linear-gradient(135deg,#1677ff,#4096ff); border:0; color:#fff; padding:10px 22px; border-radius:6px; font-size:14px; font-weight:500; cursor:pointer; box-shadow:0 3px 10px rgba(22,119,255,.25); }
.stk-btn-primary:hover { background:linear-gradient(135deg,#0958d9,#1677ff); color:#fff; }
.stk-btn-primary i { margin-right:6px; }
.stk-entry-panel { background:#ffffff85; border:1px solid #ebedf0; border-radius:12px; padding:16px 18px; }
.stk-entry-head { display:flex; align-items:flex-end; justify-content:space-between; gap:10px; flex-wrap:wrap; margin-bottom:12px; }
.stk-entry-title { font-size:18px; color:#222; font-weight:600; }
.stk-entry-sub { color:#8a94a6; font-size:12px; }

/* 商品信息卡 */
.stk-goods-card { background:#ffffff85; border:1px solid #ebedf0; border-radius:10px; padding:16px 18px; display:flex; gap:18px; margin-bottom:14px; }
.stk-goods-cover { width:90px; height:90px; border-radius:8px; background:#f5f7fa; flex-shrink:0; overflow:hidden; display:flex; align-items:center; justify-content:center; }
.stk-goods-cover img { width:100%; height:100%; object-fit:cover; }
.stk-goods-cover .ph { font-size:36px; color:#d9d9d9; }
.stk-goods-info { flex:1; min-width:0; }
.stk-goods-head { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:8px; }
.stk-goods-title { font-size:16px; font-weight:600; color:#222; max-width:60%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
/* 商品类型徽章（对齐商品列表的 goods-type-tag）*/
.goods-type-tag { display:inline-block; padding:0 6px; line-height:18px; font-size:11px; font-weight:500; border-radius:3px; border:1px solid #d9d9d9; color:#595959; background:#fafafa; }
.goods-type-tag.type-once    { background:#edf7ee; color:#389e0d; border-color:#b7eb8f; }
.goods-type-tag.type-service { background:#fff1f0; color:#cf1322; border-color:#ffa39e; }
.goods-type-tag.type-physical { background:#fff7e6; color:#d46b08; border-color:#ffd591; }
.goods-type-tag.type-general { background:#e6f4ff; color:#0958d9; border-color:#91caff; }
.goods-type-tag.type-duli    { background:#e6f4ff; color:#0958d9; border-color:#91caff; }
.goods-type-tag.type-docking { background:#f9f0ff; color:#531dab; border-color:#d3adf7; }
.goods-type-tag.type-guding  { background:#edf7ee; color:#389e0d; border-color:#b7eb8f; }
.goods-type-tag.type-xuni    { background:#fff7e6; color:#d46b08; border-color:#ffd591; }
.goods-type-tag.type-post    { background:#f9f0ff; color:#531dab; border-color:#d3adf7; }
/* SKU 徽章（对齐商品列表的 sku-tag）*/
.sku-tag { display:inline-block; padding:0 5px; line-height:18px; font-size:11px; border-radius:3px; font-weight:400; vertical-align:middle; }
.sku-tag.sku-multi  { background:#e8f4ff; color:#1677ff; }
.sku-tag.sku-single { background:#e3eeea; color:#52886a; }
.stk-goods-switch { margin-left:auto; display:flex; align-items:center; gap:8px; }
.stk-link-btn { color:#1677ff; cursor:pointer; font-size:13px; padding:3px 10px; border:1px solid #91caff; border-radius:4px; background:#e6f4ff; }
.stk-link-btn:hover { background:#1677ff; color:#fff; }
.stk-metrics { display:flex; gap:24px; margin-top:6px; }
.stk-metric { display:flex; flex-direction:column; gap:2px; }
.stk-metric .lbl { font-size:12px; color:#8a94a6; }
.stk-metric .val { font-size:22px; font-weight:700; color:#1677ff; line-height:1.2; }
.stk-metric.sold .val { color:#fa8c16; }
.stk-sku-list { display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; align-items:center; }
.stk-sku-list-label { color:#8a94a6; font-size:12px; margin-right:2px; }
.stk-sku-chip { background:#f5f7fa; border:1px solid #e6ebf0; padding:4px 10px; border-radius:16px; font-size:12px; color:#555; cursor:pointer; transition:all .15s; user-select:none; }
.stk-sku-chip:hover { background:#e6f4ff; border-color:#91caff; color:#1677ff; }
.stk-sku-chip.active { background:#1677ff; border-color:#1677ff; color:#fff; }
.stk-sku-chip.active b { color:#fff; }
.stk-sku-chip b { color:#1677ff; margin-left:4px; font-weight:600; }
.stk-sku-chip-reset { border:1px dashed #d9d9d9; color:#8a94a6; background:transparent; }
.stk-sku-chip-reset:hover { border-color:#ff4d4f; color:#ff4d4f; background:transparent; }
.stk-sku-chip-reset i { font-size:12px; vertical-align:middle; margin-right:3px; }
.stk-goods-tip { margin-top:10px; padding:8px 12px; border-radius:6px; background:#f5f7fa; color:#667797; font-size:12px; line-height:1.7; }
.stk-goods-tip.warn { background:#fff7e6; color:#d46b08; }

/* 工具栏 + Tab */
.stk-toolbar { display:flex; align-items:center; gap:8px; flex-wrap:wrap; background:#ffffff85; border:1px solid #ebedf0; border-radius:10px; padding:10px 12px; margin-bottom:10px; }
.stk-toolbar .sep { flex:1; }
.stk-action-btn { display:inline-flex; align-items:center; gap:4px; padding:6px 12px; border-radius:4px; font-size:13px; cursor:pointer; border:1px solid transparent; }
.stk-action-btn i { font-size:14px; }
.stk-action-btn.primary { background:#1677ff; color:#fff; }
.stk-action-btn.primary:hover { background:#0958d9; }
.stk-action-btn.success { background:#389e0d; color:#fff; }
.stk-action-btn.success:hover { background:#237804; }
.stk-action-btn.warn { background:#fa8c16; color:#fff; }
.stk-action-btn.warn:hover { background:#d46b08; }
.stk-action-btn.danger { background:#cf1322; color:#fff; }
.stk-action-btn.danger:hover { background:#a8071a; }
.stk-action-btn.ghost { background:#fff; color:#555; border-color:#d9d9d9; }
.stk-action-btn.ghost:hover { color:#1677ff; border-color:#1677ff; }
.stk-search-box { display:flex; align-items:center; gap:0; }
.stk-search-box input { width:350px; height:30px; padding:0 10px; border:1px solid #d9d9d9; border-right:0; border-radius:4px 0 0 4px; outline:none; font-size:13px; }
.stk-search-box input:focus { border-color:#1677ff; }
.stk-search-box button { height:30px; padding:0 14px; background:#1677ff; color:#fff; border:0; border-radius:0 4px 4px 0; cursor:pointer; font-size:13px; }
.stk-search-box button:hover { background:#0958d9; }

.stk-tabs { display:flex; align-items:center; background:#ffffff85; border:1px solid #ebedf0; border-radius:10px 10px 0 0; padding:0 12px; border-bottom:0; }
.stk-tab { padding:12px 20px; font-size:14px; color:#666; cursor:pointer; border-bottom:2px solid transparent; margin-right:8px; }
.stk-tab:hover { color:#1677ff; }
.stk-tab.active { color:#1677ff; font-weight:600; border-bottom-color:#1677ff; }
.stk-tab .badge { background:#f0f0f0; color:#666; font-size:11px; padding:1px 6px; border-radius:10px; margin-left:6px; }
.stk-tab.active .badge { background:#e6f4ff; color:#1677ff; }

.stk-table-wrap { background:#ffffff85; border:1px solid #ebedf0; border-top:0; border-radius:0 0 10px 10px; padding:10px; }
.stk-cell-content { max-width:360px; word-break:break-all; white-space:normal; line-height:1.55; font-family:Consolas,Menlo,monospace; font-size:12px; }
.stk-sku-tag-inline { display:inline-block; padding:0 6px; line-height:18px; font-size:12px; border-radius:2px; background:#e8f4ff; color:#1677ff; }
.stk-batch-tag-inline { display:inline-block; max-width:140px; padding:0 6px; line-height:18px; font-size:12px; border-radius:2px; background:#fff7e6; color:#d46b08; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; vertical-align:middle; }

/* 选择商品弹窗 */
.stk-picker { padding:14px; }
.stk-picker-search { display:flex; align-items:center; gap:8px; margin-bottom:12px; }
.stk-picker-search input { flex:1; height:32px; padding:0 12px; border:1px solid #d9d9d9; border-radius:4px; outline:none; font-size:13px; }
.stk-picker-search input:focus { border-color:#1677ff; }
.stk-picker-search button { height:32px; padding:0 16px; background:#1677ff; color:#fff; border:0; border-radius:4px; cursor:pointer; font-size:13px; }
.stk-picker-grid { display:grid; gap:10px; max-height:520px; overflow-y:auto; align-content:start; align-items:start; }
.stk-picker-card { width:100%; min-width:0; background:#fff; border:1px solid #ebedf0; border-radius:8px; padding:10px; cursor:pointer; transition:all .15s; display:flex; gap:10px; align-self:start; box-sizing:border-box; }
.stk-picker-card:hover { border-color:#1677ff; box-shadow:0 4px 14px rgba(22,119,255,.12); transform:translateY(-1px); }
.stk-picker-card.is-docking { opacity:.55; cursor:not-allowed; }
.stk-picker-card.is-docking:hover { border-color:#d3adf7; box-shadow:none; transform:none; }
.stk-picker-card .cov { width:56px; height:56px; background:#f5f7fa; border-radius:6px; overflow:hidden; flex-shrink:0; display:flex; align-items:center; justify-content:center; }
.stk-picker-card .cov img { width:100%; height:100%; object-fit:cover; }
.stk-picker-card .cov i { color:#d9d9d9; font-size:24px; }
.stk-picker-card .info { flex:1; min-width:0; display:flex; flex-direction:column; justify-content:space-between; }
.stk-picker-card .tt { font-size:13px; font-weight:500; color:#222; margin-bottom:4px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.stk-picker-card .meta { font-size:10px; color:#8a94a6; display:flex; align-items:center; gap:6px; min-width:0; }
.stk-picker-card .meta .tag { padding:1px 6px; border-radius:3px; font-size:11px; }
.stk-picker-card .meta .stk-picker-remain { margin-left:auto; display:inline-flex; align-items:center; gap:2px; min-width:0; max-width:78px; white-space:nowrap; }
.stk-picker-card .meta .stk-picker-remain b { display:inline-block; max-width:42px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:#1677ff; vertical-align:middle; }
.stk-picker-pagination { display:flex; justify-content:center; padding:12px 0 4px; }
.stk-picker-inline { padding:0; }
.stk-picker-inline .stk-picker-grid { grid-template-columns:repeat(5, minmax(0, 1fr)); max-height:none; min-height:240px; }
.stk-picker:not(.stk-picker-inline) .stk-picker-grid { grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); }
.stk-picker:not(.stk-picker-inline) .stk-picker-card { min-height:88px; padding:10px 12px; }
.stk-picker:not(.stk-picker-inline) .stk-picker-card .info { justify-content:center; gap:8px; }
.stk-picker:not(.stk-picker-inline) .stk-picker-card .tt { margin-bottom:0; }

@media (max-width: 1200px) {
    .stk-picker-inline .stk-picker-grid { grid-template-columns:repeat(4, minmax(0, 1fr)); }
}

@media (max-width: 980px) {
    .stk-picker-inline .stk-picker-grid { grid-template-columns:repeat(3, minmax(0, 1fr)); }
    .stk-picker:not(.stk-picker-inline) .stk-picker-grid { grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); }
}

@media (max-width: 768px) {
    .stk-body { padding:10px 10px 14px; }
    .stk-picker-inline .stk-picker-grid { grid-template-columns:repeat(2, minmax(0, 1fr)); gap:8px; }
    .stk-picker:not(.stk-picker-inline) .stk-picker-grid { grid-template-columns:1fr; }
    .stk-picker-card .cov { width:44px; height:44px; }
    .stk-picker-card .tt { font-size:12px; }
    .stk-picker-card .meta { flex-wrap:wrap; }
    .stk-goods-card { flex-direction:column; gap:10px; padding:12px; }
    .stk-goods-cover { width:60px; height:60px; }
    .stk-goods-head { flex-wrap:wrap; }
    .stk-goods-title { max-width:100%; font-size:14px; }
    .stk-goods-switch { margin-left:0; margin-top:6px; }
    .stk-metrics { gap:16px; }
    .stk-metric .val { font-size:18px; }
    .stk-sku-list { gap:6px; }
    .stk-sku-chip { padding:3px 8px; font-size:11px; }
    .stk-toolbar { padding:8px 10px; gap:6px; flex-wrap:wrap; }
    .stk-action-btn { padding:5px 8px; font-size:12px; }
    .stk-search-box { width:100%; order:99; }
    .stk-search-box input { width:100%; flex:1; }
    .stk-tabs { padding:0 8px; overflow-x:auto; }
    .stk-tab { padding:10px 12px; font-size:13px; white-space:nowrap; }
    .stk-entry-head { flex-direction:column; align-items:flex-start; gap:4px; }
    .stk-entry-title { font-size:16px; }
}

@media (max-width: 480px) {
    .stk-picker-inline .stk-picker-grid { grid-template-columns:1fr; }
    .stk-toolbar .stk-action-btn span.hide-xs { display:none; }
}

/* 导入弹窗 */
.stk-import { padding:18px 22px; }
.stk-import-up { border:2px dashed #d9d9d9; border-radius:8px; padding:22px; text-align:center; cursor:pointer; background:#fafbfc; transition:all .15s; }
.stk-import-up:hover { border-color:#1677ff; background:#f0f7ff; }
.stk-import-up i { font-size:36px; color:#b7c0cc; display:block; margin-bottom:8px; }
.stk-import-up.has-file { border-style:solid; border-color:#389e0d; background:#f6ffed; }
.stk-import-up.has-file i { color:#389e0d; }
.stk-import-tip { color:#8a94a6; font-size:12px; line-height:1.6; margin-top:10px; padding:8px 12px; background:#f5f7fa; border-left:3px solid #1677ff; border-radius:3px; }
.stk-import-sku { margin-top:12px; }
.stk-import-sku label { display:inline-block; margin-right:10px; color:#555; font-size:13px; }
.stk-import-sku select { height:30px; padding:0 8px; border:1px solid #d9d9d9; border-radius:4px; min-width:180px; }
.stk-import-batch { margin-top:12px; display:flex; align-items:center; gap:10px; }
.stk-import-batch label { color:#555; font-size:13px; flex-shrink:0; }
.stk-import-batch input { flex:1; height:30px; padding:0 10px; border:1px solid #d9d9d9; border-radius:4px; }

/* 结果弹窗 */
.stk-result { padding:18px 22px; }
.stk-result-row { display:flex; align-items:flex-start; gap:8px; padding:6px 0; border-bottom:1px dashed #f0f0f0; }
.stk-result-row .lbl { color:#666; width:90px; }
.stk-result-row .val { flex:1; color:#222; font-weight:500; word-break:break-all; }
.stk-result-row.ok .val { color:#389e0d; }
.stk-result-row.warn .val { color:#fa8c16; }
.stk-result-row.err .val { color:#cf1322; }
.stk-failed-list { margin-top:12px; max-height:220px; overflow:auto; background:#fafbfc; border:1px solid #ebedf0; border-radius:4px; padding:8px 12px; font-size:12px; font-family:Consolas,monospace; }
.stk-failed-list div { padding:3px 0; border-bottom:1px dotted #eee; color:#cf1322; word-break:break-all; }

/* 清空已使用弹窗 */
.stk-clearused { padding:18px 22px; }
.stk-clearused-opts { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin-top:14px; }
.stk-clearused-opt { border:1px solid #ebedf0; border-radius:6px; padding:10px; cursor:pointer; text-align:center; transition:all .15s; font-size:13px; }
.stk-clearused-opt:hover { border-color:#1677ff; color:#1677ff; }
.stk-clearused-opt.active { border-color:#1677ff; background:#e6f4ff; color:#1677ff; font-weight:600; }
.stk-clearused-custom { display:flex; align-items:center; gap:8px; margin-top:10px; }
.stk-clearused-custom input { flex:1; height:30px; padding:0 10px; border:1px solid #d9d9d9; border-radius:4px; }
<?php if ($isPopup): ?>
.stk-header { display:none!important; }
.stk-wrap { margin-bottom:0; }
<?php endif; ?>
</style>

<div class="stk-wrap">
<div class="stk-header">
    <span class="stk-mac-dots"><i class="d1"></i><i class="d2"></i><i class="d3"></i></span>
    <span class="stk-title-text">库存管理</span>
</div>
<div class="stk-body">

<?php if ($selectedGoodsId <= 0): ?>
    <div class="stk-entry-panel">
        <div class="stk-entry-head">
            <div class="stk-entry-title">选择要管理的商品</div>
            <div class="stk-entry-sub">商品已直接展示，点击任意商品即可进入对应库存管理</div>
        </div>
        <div class="stk-picker stk-picker-inline" id="stk-picker-inline">
            <div class="stk-picker-search">
                <input class="stk-picker-kw" type="text" placeholder="搜索商品名或输入商品ID…">
                <button class="stk-picker-search-btn">搜索</button>
            </div>
            <div class="stk-picker-grid"></div>
            <div class="stk-picker-pagination" id="stk-picker-page-inline"></div>
        </div>
    </div>

<?php else: ?>
    <!-- ========= 商品信息卡（JS 填充） ========= -->
    <div id="stk-card" class="stk-goods-card" data-goods-id="<?= (int)$selectedGoodsId ?>">
        <div class="stk-goods-cover"><i class="ri-image-line ph"></i></div>
        <div class="stk-goods-info">
            <div class="stk-goods-head">
                <span class="stk-goods-title">正在加载商品信息…</span>
            </div>
        </div>
    </div>

    <!-- ========= 工具栏 ========= -->
    <div class="stk-toolbar" id="stk-toolbar">
        <button class="stk-action-btn primary" data-evt="add"><i class="ri-add-line"></i> 添加库存</button>
        <button class="stk-action-btn success" data-evt="import"><i class="ri-upload-2-line"></i> 导入库存</button>
        <button class="stk-action-btn warn" data-evt="clear-used"><i class="ri-eraser-line"></i> 清空已使用</button>
        <button class="stk-action-btn danger" data-evt="clear-all"><i class="ri-delete-bin-6-line"></i> 清空库存</button>
        <button class="stk-action-btn ghost" data-evt="export"><i class="ri-download-2-line"></i> 导出</button>
        <div class="sep"></div>
        <button class="stk-action-btn ghost" data-evt="batch-del" id="stk-batch-del" style="display:none;"><i class="ri-delete-bin-line"></i> 删除选中</button>
        <div class="stk-search-box">
            <input id="stk-keyword" type="text" placeholder="搜索卡密/批次号/订单号/UID/昵称/邮箱/手机/IP/ID">
            <button id="stk-search-btn"><i class="ri-search-line"></i></button>
        </div>
    </div>

    <!-- ========= Tab ========= -->
    <div class="stk-tabs">
        <div class="stk-tab active" data-tab="unused"><i class="ri-inbox-unarchive-line"></i> 未使用<span class="badge" id="stk-badge-unused">0</span></div>
        <div class="stk-tab" data-tab="used"><i class="ri-check-double-line"></i> 已使用<span class="badge" id="stk-badge-used">0</span></div>
    </div>

    <!-- ========= 表格 ========= -->
    <div class="stk-table-wrap">
        <table class="layui-hide" id="stock" lay-filter="stock"></table>
    </div>

    <!-- ========= 列模板 ========= -->
    <script type="text/html" id="stk-tpl-content">
        <div class="stk-cell-content">{{ d.content || '' }}</div>
    </script>
    <script type="text/html" id="stk-tpl-sku">
        {{# if(d.sku_name){ }}<span class="stk-sku-tag-inline">{{ d.sku_name }}</span>{{# } else { }}<span style="color:#bbb;">默认</span>{{# } }}
    </script>
    <script type="text/html" id="stk-tpl-batch">
        {{# if(d.batch_no){ }}<span class="stk-batch-tag-inline" title="{{ d.batch_no }}">{{ d.batch_no }}</span>{{# } else { }}<span style="color:#bbb;">-</span>{{# } }}
    </script>
    <script type="text/html" id="stk-tpl-quantity">
        <b style="color:#1677ff;font-size:14px;">{{ d.quantity }}</b>
    </script>
    <script type="text/html" id="stk-tpl-physical-mode">
        <span style="display:inline-block;padding:2px 8px;border-radius:10px;background:#fff7e6;color:#d46b08;font-size:12px;">数量库存</span>
    </script>
    <script type="text/html" id="stk-tpl-ops-physical">
        <a class="layui-btn layui-btn-xs layui-bg-orange" lay-event="physicalAdd"><i class="ri-inbox-archive-line"></i> 入库</a>
    </script>
    <script type="text/html" id="stk-tpl-ops-unused">
        <a class="layui-btn layui-btn-xs layui-btn-primary" lay-event="view"><i class="ri-eye-line"></i></a>
        <a class="layui-btn layui-btn-xs layui-bg-blue" lay-event="edit"><i class="ri-edit-line"></i></a>
        <a class="layui-btn layui-btn-xs layui-bg-red" lay-event="del"><i class="ri-delete-bin-line"></i></a>
    </script>
    <script type="text/html" id="stk-tpl-ops-used">
        <a class="layui-btn layui-btn-xs layui-btn-primary" lay-event="view"><i class="ri-eye-line"></i> 查看</a>
        {{# if(d.order_id){ }}<a class="layui-btn layui-btn-xs layui-bg-blue" lay-event="goorder" data-id="{{ d.order_id }}"><i class="ri-external-link-line"></i> 订单</a>{{# } }}
    </script>
    <script type="text/html" id="stk-tpl-order">
        {{# if(d.out_trade_no){ }}<div style="font-family:Consolas,monospace;font-size:12px;"><a href="{{ d.order_list_url || ('order.php?out_trade_no=' + encodeURIComponent(d.out_trade_no)) }}" target="_blank" style="color:#1677ff;">{{ d.out_trade_no }}</a></div>{{# } }}
        {{# if(d.order_id){ }}<div style="color:#8a94a6;font-size:11px;">#{{ d.order_id }}</div>{{# } }}
    </script>
    <script type="text/html" id="stk-tpl-buyer">
        {{# var buyerText = d.buyer_display || d.buyer_guest_text || '-'; }}
        {{# if(d.user_list_url){ }}<a href="{{ d.user_list_url }}" target="_blank" style="display:inline-block;max-width:100%;font-size:12px;color:#1677ff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;vertical-align:middle;" title="{{ buyerText }}">{{ buyerText }}</a>{{# } else { }}<span style="display:inline-block;max-width:100%;font-size:12px;color:#666;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;vertical-align:middle;" title="{{ buyerText }}">{{ buyerText }}</span>{{# } }}
    </script>
    <script type="text/html" id="stk-tpl-buyer-ip">
        {{# if(d.client_ip){ }}<div style="font-family:Consolas,monospace;font-size:12px;">{{ d.client_ip }}</div>{{# } else { }}<span style="color:#bbb;">-</span>{{# } }}
    </script>
<?php endif; ?>
</div><!-- /.stk-body -->
</div><!-- /.stk-wrap -->

<!-- ==================== 业务逻辑 ==================== -->
<script>
layui.use(['table', 'form', 'laytpl', 'laypage', 'layer'], function () {
    var TOKEN        = '<?= $token ?>';
    var SELECTED_GID = <?= (int)$selectedGoodsId ?>;
    var IS_POPUP     = <?= $isPopup ? 'true' : 'false' ?>;
    var $ = layui.$, layer = layui.layer, table = layui.table, form = layui.form;

    function stockBaseUrl (gid) {
        return IS_POPUP
            ? 'stock.php?action=stock_popup&goods_id=' + gid
            : 'stock.php?goods_id=' + gid;
    }

    function buildGoodsCoverHtml(cover, iconClass) {
        var safeCover = $.trim(String(cover || ''));
        iconClass = iconClass || 'ri-image-line';
        if (!safeCover) return '<i class="' + iconClass + '"></i>';
        safeCover = $('<div>').text(safeCover).html();
        return '<img src="' + safeCover + '" onerror="this.parentNode.innerHTML=\'<i class=&quot;' + iconClass + '&quot;></i>\'">';
    }

    function formatPickerRemaining(remain) {
        var txt = $.trim(String(typeof remain === 'undefined' || remain === null ? 0 : remain));
        if (!txt) txt = '0';
        return txt.length > 5 ? (txt.slice(0, 5) + '…') : txt;
    }

    function renderPickerCards(list) {
        var htm = '';
        list.forEach(function (g) {
            var safeTitle = $('<div>').text(g.title).html();
            var cov = buildGoodsCoverHtml(g.cover, 'ri-image-line');
            var typeTxt = $('<div>').text(g.type_text || '未知类型').html();
            var remainFull = $.trim(String(typeof g.remaining === 'undefined' || g.remaining === null ? 0 : g.remaining));
            if (!remainFull) remainFull = '0';
            var remainText = $('<div>').text(formatPickerRemaining(remainFull)).html();
            var skuTag  = (g.is_sku === 'y')
                ? '<span class="sku-tag sku-multi">多规格</span>'
                : '<span class="sku-tag sku-single">单规格</span>';
            var cardClass = 'stk-picker-card' + (g.type === 'docking' ? ' is-docking' : '');
            htm += '<div class="' + cardClass + '" data-id="' + g.id + '" data-type="' + g.type + '">'
                +   '<div class="cov">' + cov + '</div>'
                +   '<div class="info">'
                +     '<div class="tt" title="' + safeTitle + '">' + safeTitle + '</div>'
                +     '<div class="meta">'
                +       '<span class="goods-type-tag type-' + g.type + '">' + typeTxt + '</span>'
                +       skuTag
                +       '<span class="stk-picker-remain" title="剩余 ' + $('<div>').text(remainFull).html() + '">剩余 <b>' + remainText + '</b></span>'
                +     '</div>'
                +   '</div>'
                + '</div>';
        });
        return htm;
    }

    function initGoodsPicker(rootSelector, options) {
        var $root = $(rootSelector);
        if (!$root.length) return null;

        var state = $.extend({ page: 1, limit: 15, kw: '', pageElem: '' }, options || {});
        var $grid = $root.find('.stk-picker-grid');
        var $keyword = $root.find('.stk-picker-kw');
        var $searchBtn = $root.find('.stk-picker-search-btn');
        var $page = $root.find('.stk-picker-pagination');

        function load() {
            $grid.html('<div style="grid-column:1/-1;text-align:center;padding:40px;color:#999;">加载中…</div>');
            $.get('stock.php?action=goods_picker_ajax', { page: state.page, limit: state.limit, keyword: state.kw }, function (r) {
                if (!r || r.code !== 0) {
                    $grid.html('<div style="grid-column:1/-1;text-align:center;padding:40px;color:#cf1322;">加载失败：' + (r && r.msg || '') + '</div>');
                    return;
                }
                if (!r.data || !r.data.length) {
                    $grid.html('<div style="grid-column:1/-1;text-align:center;padding:40px;color:#999;">没有找到商品</div>');
                    $page.empty();
                    return;
                }

                $grid.html(renderPickerCards(r.data));
                layui.use(['laypage'], function () {
                    layui.laypage.render({
                        elem: state.pageElem,
                        count: r.count,
                        curr: state.page,
                        limit: state.limit,
                        limits: [state.limit],
                        layout: ['prev', 'page', 'next', 'count'],
                        jump: function (obj, first) {
                            if (!first) {
                                state.page = obj.curr;
                                load();
                            }
                        }
                    });
                });
            }, 'json').fail(function () {
                $grid.html('<div style="grid-column:1/-1;text-align:center;padding:40px;color:#cf1322;">网络错误</div>');
            });
        }

        $searchBtn.off('click').on('click', function () {
            state.page = 1;
            state.kw = $keyword.val().trim();
            load();
        });
        $keyword.off('keyup').on('keyup', function (e) {
            if (e.keyCode === 13) {
                $searchBtn.click();
            }
        });
        $root.off('click', '.stk-picker-card').on('click', '.stk-picker-card', function () {
            var gid = $(this).data('id');
            var gtype = $(this).data('type') || '';
            if (gtype === 'docking') {
                layer.msg('同系统对接商品由货源站自动管理库存，无需手动管理卡密', {icon: 0, time: 2500});
                return;
            }
            window.location.href = stockBaseUrl(gid);
        });

        load();
        return { load: load, state: state };
    }

    /* ========= 通用 AJAX ========= */
    function ajaxJson(action, data, type, cb) {
        type = type || 'POST';
        data = data || {};
        if (type === 'POST') data.token = TOKEN;
        $.ajax({
            url: 'stock.php?action=' + action, type: type, dataType: 'json', data: data,
            success: function (r) { cb && cb(null, r); },
            error: function (xhr) {
                var msg = '请求失败';
                try { msg = (xhr.responseJSON && xhr.responseJSON.msg) || JSON.parse(xhr.responseText).msg || msg; } catch (e) {}
                cb && cb(msg || '请求失败', null);
            }
        });
    }

    function ajaxUrl(url, data, type, cb) {
        type = type || 'POST';
        data = data || {};
        if (type === 'POST' && typeof data.token === 'undefined') data.token = TOKEN;
        $.ajax({
            url: url, type: type, dataType: 'json', data: data,
            success: function (r) { cb && cb(null, r); },
            error: function (xhr) {
                var msg = '请求失败';
                try { msg = (xhr.responseJSON && xhr.responseJSON.msg) || JSON.parse(xhr.responseText).msg || msg; } catch (e) {}
                cb && cb(msg || '请求失败', null);
            }
        });
    }

    /* ========= 商品选择器弹窗 ========= */
    function openPicker () {
        var pickerPageId = 'stk-picker-page-' + Date.now();
        var html = ''
            + '<div class="stk-picker">'
            +   '<div class="stk-picker-search">'
            +     '<input class="stk-picker-kw" type="text" placeholder="搜索商品名或输入商品ID…">'
            +     '<button class="stk-picker-search-btn">搜索</button>'
            +   '</div>'
            +   '<div class="stk-picker-grid"></div>'
            +   '<div class="stk-picker-pagination" id="' + pickerPageId + '"></div>'
            + '</div>';

        layer.open({
            type: 1, title: '选择商品 · 库存管理',
            area: (window.innerWidth < 768) ? ['96%', '90%'] : ['860px', '660px'], skin: 'dc-layer-modern',
            content: html, shadeClose: true, resize: false,
            success: function (layero) {
                var popupLimit = (window.innerWidth < 768) ? 8 : 15;
                initGoodsPicker(layero.find('.stk-picker').eq(0), { page: 1, limit: popupLimit, kw: '', pageElem: pickerPageId });
            }
        });
    }

    /* 引导页"选择商品"按钮 */
    $(document).on('click', '#stk-pick-btn', openPicker);

    if (SELECTED_GID <= 0) {
        var inlineLimit = (window.innerWidth < 768) ? 10 : 30;
        initGoodsPicker('#stk-picker-inline', { page: 1, limit: inlineLimit, kw: '', pageElem: 'stk-picker-page-inline' });
        return;
    }

    /* ========= 商品信息卡加载 ========= */
    var GOODS = null;
    var currentSkuFilter = '';   // 当前规格筛选

    function loadGoodsCard () {
        $.get('stock.php?action=goods_info_ajax', { goods_id: SELECTED_GID }, function (r) {
            if (!r || r.code !== 0) { layer.msg('加载商品失败：' + (r && r.msg || ''), { icon: 2 }); return; }
            var firstLoad = (GOODS === null);
            GOODS = r.data;
            renderGoodsCard();
            // 填充 badge
            $('#stk-badge-unused').text(GOODS.remaining || 0);
            $('#stk-badge-used').text(GOODS.sold || 0);
            // 模板类（general/service）需要根据 GOODS.type 动态加"可用数量"列，首次拿到 GOODS 后重新渲染一次表格
            // 实物商品的库存表由 GOODS.skus / GOODS.remaining 生成，每次加载商品信息都要同步刷新
            if (firstLoad || isPhysicalGoods()) renderTable();
        }, 'json');
    }

    function reloadStockView () {
        if (!isPhysicalGoods()) {
            table.reload('stock');
        }
        loadGoodsCard();
    }

    function isPhysicalGoods () {
        return GOODS && GOODS.type === 'physical';
    }

    function isTemplateStockGoods () {
        return GOODS && (GOODS.type === 'general' || GOODS.type === 'service');
    }

    function getTemplateStatus () {
        return GOODS && GOODS.template_status ? GOODS.template_status : null;
    }

    function getTemplateSkuState (sku) {
        if (!GOODS || !GOODS.skus || !GOODS.skus.length) return null;
        sku = String(sku || '');
        for (var i = 0; i < GOODS.skus.length; i++) {
            if (String(GOODS.skus[i].sku) === sku) {
                return GOODS.skus[i];
            }
        }
        return null;
    }

    function canImportStock () {
        return GOODS && (GOODS.type === 'duli' || GOODS.type === 'once');
    }

    function getGoodsSkuText (sku) {
        sku = String(sku || '0');
        if (!GOODS || GOODS.is_sku !== 'y' || sku === '0') return '默认/无规格';
        if (GOODS.skus && GOODS.skus.length) {
            for (var i = 0; i < GOODS.skus.length; i++) {
                if (String(GOODS.skus[i].sku) === sku) {
                    return GOODS.skus[i].sku_name || '默认/无规格';
                }
            }
        }
        return sku;
    }

    function formatImportFileSize (size) {
        size = parseFloat(size || 0);
        if (!size || size < 0) return '';
        if (size < 1024) return size.toFixed(0) + ' B';
        if (size < 1024 * 1024) return (size / 1024).toFixed(1) + ' KB';
        return (size / 1024 / 1024).toFixed(2) + ' MB';
    }

    function syncAddButtonState () {
        var $btn = $('#stk-toolbar [data-evt="add"]');
        if (!$btn.length) return;

        var html = '<i class="ri-add-line"></i> 添加库存';
        var btnClass = 'primary';
        var title = '';
        var tpl = getTemplateStatus();
        var currentTplSku = currentSkuFilter ? getTemplateSkuState(currentSkuFilter) : null;

        if (isPhysicalGoods()) {
            html = '<i class="ri-inbox-archive-line"></i> 实物入库';
            title = '为实物商品增加可售库存数量';
        } else if (isTemplateStockGoods() && tpl && tpl.enabled) {
            if (currentTplSku && currentTplSku.template_exists) {
                html = '<i class="ri-edit-line"></i> 当前规格去编辑';
                btnClass = 'ghost';
                title = '当前筛选规格已配置模板，请直接编辑卡密内容或可用次数';
            } else if (tpl.all_locked) {
                html = '<i class="ri-edit-line"></i> 模板已配置';
                btnClass = 'ghost';
                title = '模板已全部配置完成，请直接编辑已有卡密';
            } else if (tpl.has_existing) {
                html = '<i class="ri-add-line"></i> 添加剩余模板';
                title = '已配置规格不可重复新增，请为未配置规格添加模板';
            }
        }

        $btn.removeClass('primary success warn danger ghost').addClass(btnClass).html(html);
        if (title) $btn.attr('title', title);
        else $btn.removeAttr('title');
    }

    function syncImportButtonState () {
        var $btn = $('#stk-toolbar [data-evt="import"]');
        if (!$btn.length) return;
        $btn.removeClass('primary success warn danger ghost');
        if (isPhysicalGoods()) {
            $btn.hide();
            return;
        }
        $btn.show();
        if (canImportStock()) {
            $btn.addClass('success').removeAttr('title');
            return;
        }
        $btn.addClass('ghost').attr('title', '当前商品类型暂不支持批量导入');
    }

    function syncPhysicalStockMode () {
        var physical = isPhysicalGoods();
        $('#stk-toolbar [data-evt="clear-used"], #stk-toolbar [data-evt="export"], #stk-batch-del, #stk-toolbar .stk-search-box').toggle(!physical);
        $('#stk-toolbar [data-evt="clear-all"]').html(physical
            ? '<i class="ri-delete-bin-6-line"></i> 清空库存'
            : '<i class="ri-delete-bin-6-line"></i> 清空库存');
        $('.stk-tabs').toggle(!physical);
        $('#stk-keyword').attr('placeholder', physical ? '实物库存按规格和数量管理，无需搜索卡密' : '搜索卡密/批次号/订单号/UID/昵称/邮箱/手机/IP/ID');
    }

    function renderGoodsCard () {
        var safeTitle = $('<div>').text(GOODS.title).html();
        var cov = buildGoodsCoverHtml(GOODS.cover, 'ri-image-line ph');

        var skuHtml = '';
        if (GOODS.skus && GOODS.skus.length) {
            skuHtml = '<div class="stk-sku-list">'
                    + '<span class="stk-sku-list-label"><i class="ri-price-tag-3-line"></i> 规格筛选：</span>';
            GOODS.skus.forEach(function (s) {
                var esc = $('<div>').text(s.sku_name).html();
                var active = (currentSkuFilter === s.sku) ? ' active' : '';
                skuHtml += '<span class="stk-sku-chip' + active + '" data-sku="' + s.sku + '" title="点击按此规格筛选">'
                        + esc + ' <b>' + s.stock + '</b></span>';
            });
            if (currentSkuFilter) {
                skuHtml += '<span class="stk-sku-chip stk-sku-chip-reset" id="stk-sku-reset"><i class="ri-close-line"></i>清除筛选</span>';
            }
            skuHtml += '</div>';
        }

        var templateTip = '';
        var tpl = getTemplateStatus();
        var currentTplSku = currentSkuFilter ? getTemplateSkuState(currentSkuFilter) : null;
        if (isPhysicalGoods()) {
            templateTip = '<div class="stk-goods-tip">实物商品按规格维护可售数量，不保存卡密内容；买家付款后系统自动扣减库存，发货信息在订单发货页维护。</div>';
        } else if (isTemplateStockGoods() && tpl && tpl.enabled) {
            if (currentTplSku && currentTplSku.template_exists) {
                templateTip = '<div class="stk-goods-tip warn">当前筛选规格已配置模板，请直接编辑卡密内容或可用次数。</div>';
            } else if (tpl.all_locked) {
                templateTip = '<div class="stk-goods-tip warn">模板卡密已全部配置完成，请直接在列表中编辑内容或可用次数。</div>';
            } else if (tpl.has_existing) {
                templateTip = '<div class="stk-goods-tip">已配置 ' + tpl.configured_count + '/' + tpl.total_slots + ' 个规格；已配置规格请直接编辑，未配置规格可继续添加。</div>';
            }
        }

        var typeTxt = $('<div>').text(GOODS.type_text || '未知类型').html();
        var skuTag  = (GOODS.is_sku === 'y')
            ? '<span class="sku-tag sku-multi">多规格</span>'
            : '<span class="sku-tag sku-single">单规格</span>';

        var html = ''
            + '<div class="stk-goods-cover">' + cov + '</div>'
            + '<div class="stk-goods-info">'
            +   '<div class="stk-goods-head">'
            +     '<span class="stk-goods-title" title="' + safeTitle + '">' + safeTitle + '</span>'
            +     '<span class="goods-type-tag type-' + GOODS.type + '">' + typeTxt + '</span>'
            +     skuTag
            + (IS_POPUP ? '' : '<div class="stk-goods-switch"><span class="stk-link-btn" id="stk-change-goods"><i class="ri-refresh-line"></i> 切换商品</span></div>')
            +   '</div>'
            +   '<div class="stk-metrics">'
            +     '<div class="stk-metric"><span class="lbl">' + (isPhysicalGoods() ? '当前库存' : '剩余库存') + '</span><span class="val">' + GOODS.remaining + '</span></div>'
            +     '<div class="stk-metric sold"><span class="lbl">已售数量</span><span class="val">' + GOODS.sold + '</span></div>'
            +   '</div>'
            +   templateTip
            +   skuHtml
            + '</div>';

        $('#stk-card').html(html);
        syncAddButtonState();
        syncImportButtonState();
        syncPhysicalStockMode();
    }

    $(document).on('click', '#stk-change-goods', openPicker);

    loadGoodsCard();

    /* ========= 列表（Tab 联动） ========= */
    var TAB_UNUSED = 'unused', TAB_USED = 'used';
    var currentTab = TAB_UNUSED;

    function buildPhysicalStockRows () {
        if (!GOODS) return [];
        var rows = [];
        if (GOODS.is_sku === 'y' && GOODS.skus && GOODS.skus.length) {
            GOODS.skus.forEach(function (s, idx) {
                if (currentSkuFilter && String(s.sku) !== String(currentSkuFilter)) return;
                rows.push({
                    stock_id: idx + 1,
                    sku: s.sku,
                    sku_name: s.sku_name || '默认规格',
                    quantity: parseInt(s.stock, 10) || 0,
                    sold: '-'
                });
            });
            return rows;
        }
        rows.push({
            stock_id: 1,
            sku: '0',
            sku_name: '默认规格',
            quantity: parseInt(GOODS.remaining, 10) || 0,
            sold: parseInt(GOODS.sold, 10) || 0
        });
        return rows;
    }

    function colsPhysical () {
        return [[
            { field: 'stock_id', title: 'ID', width: 80, align: 'center' },
            { field: 'sku_name', title: '规格', minWidth: 220, templet: '#stk-tpl-sku' },
            { field: 'quantity', title: '当前库存', width: 120, align: 'center', templet: '#stk-tpl-quantity' },
            { field: 'sold', title: '已售数量', width: 120, align: 'center' },
            { title: '库存模式', width: 120, align: 'center', templet: '#stk-tpl-physical-mode' },
            { fixed: 'right', title: '操作', width: 120, align: 'center', templet: '#stk-tpl-ops-physical' }
        ]];
    }

    function colsUnused () {
        // 模板类（通用卡密 / 虚拟服务）：一条卡密绑定 skus.stock 可用次数；一卡一密类不显示这一列
        var isTemplateType = GOODS && (GOODS.type === 'general' || GOODS.type === 'service');
        var isOnceType = GOODS && GOODS.type === 'once';
        var cols = [
            { type: 'checkbox', fixed: 'left' },
            { field: 'stock_id',        title: 'ID',       width: 80, align: 'center' },
            { field: 'sku_name',        title: '规格',     width: 140, templet: '#stk-tpl-sku' },
            { field: 'content',         title: '卡密内容', minWidth: 240, templet: '#stk-tpl-content' }
        ];
        if (isOnceType) {
            cols.splice(3, 0, { field: 'batch_no', title: '批次号', width: 160, templet: '#stk-tpl-batch' });
        }
        if (isTemplateType) {
            cols.push({ field: 'quantity', title: '可用数量', width: 100, align: 'center', templet: '#stk-tpl-quantity' });
        }
        cols.push({ field: 'create_time_fmt', title: '添加时间', width: 150, align: 'center' });
        cols.push({ fixed: 'right', title: '操作', width: 160, align: 'center', templet: '#stk-tpl-ops-unused' });
        return [cols];
    }

    function colsUsed () {
        var isOnceType = GOODS && GOODS.type === 'once';
        var cols = [
            { field: 'deliver_id',      title: 'ID',       width: 70, align: 'center' },
            { field: 'sku_name',        title: '规格',     width: 130, templet: '#stk-tpl-sku' }
        ];
        if (isOnceType) {
            cols.push({ field: 'batch_no', title: '批次号', width: 100, templet: '#stk-tpl-batch' });
        }
        cols = cols.concat([
            { field: 'content',         title: '卡密内容', minWidth: 220, templet: '#stk-tpl-content' },
            { field: 'out_trade_no',    title: '订单号',   width: 140, templet: '#stk-tpl-order' },
            { field: 'buyer_name',      title: '买家信息', minWidth: 180, templet: '#stk-tpl-buyer' },
            { field: 'client_ip',       title: '买家IP',   width: 100, templet: '#stk-tpl-buyer-ip' },
            { field: 'deliver_time_fmt',title: '发货时间', width: 150, align: 'center' },
            { fixed: 'right', title: '操作',            width: 150, align: 'center', templet: '#stk-tpl-ops-used' }
        ]);
        return [cols];
    }

    function tableWhere () {
        return {
            goods_id: SELECTED_GID,
            keyword:  $('#stk-keyword').val().trim(),
            sku:      currentSkuFilter || ''
        };
    }

    function renderTable () {
        if (isPhysicalGoods()) {
            currentTab = TAB_UNUSED;
            table.render({
                elem: '#stock', id: 'stock',
                data: buildPhysicalStockRows(),
                cols: colsPhysical(),
                autoSort: false, page: false,
                defaultToolbar: [],
                done: function () {
                    $('#stk-batch-del').hide();
                },
                error: function (xhr) {
                    console.error('[physical stock list error]', xhr && xhr.status, xhr && xhr.responseText);
                    layer.msg('实物库存列表加载失败，请查看控制台', { icon: 2 });
                }
            });
            syncPhysicalStockMode();
            return;
        }

        var url, cols;
        if (currentTab === TAB_UNUSED) { url = 'stock.php?action=stock_ajax'; cols = colsUnused(); }
        else { url = 'stock.php?action=used_ajax'; cols = colsUsed(); }

        table.render({
            elem: '#stock', id: 'stock',
            url: url,
            where: tableWhere(),
            cols: cols,
            autoSort: false, page: true, limit: 10, limits: [10, 20, 30, 50, 100],
            defaultToolbar: [],
            done: function (res) {
                $('#stk-batch-del').toggle(currentTab === TAB_UNUSED);
            },
            error: function (xhr) {
                console.error('[stock list error]', xhr && xhr.status, xhr && xhr.responseText);
                layer.msg('列表加载失败，请查看控制台', { icon: 2 });
            }
        });
    }

    /* Tab 切换 */
    $(document).on('click', '.stk-tab', function () {
        var tab = $(this).data('tab');
        if (tab === currentTab) return;
        $('.stk-tab').removeClass('active');
        $(this).addClass('active');
        currentTab = tab;
        renderTable();
    });

    /* 搜索 */
    $(document).on('click', '#stk-search-btn', function () {
        table.reload('stock', { page: { curr: 1 }, where: tableWhere() });
    });
    $(document).on('keyup', '#stk-keyword', function (e) { if (e.keyCode === 13) $('#stk-search-btn').click(); });

    /* 规格 chip 点击筛选（多规格商品专用） */
    $(document).on('click', '.stk-sku-chip', function () {
        // 重置按钮单独处理
        if ($(this).hasClass('stk-sku-chip-reset')) {
            currentSkuFilter = '';
        } else {
            var sku = $(this).data('sku');
            if (sku === undefined || sku === null || sku === '') return;
            sku = String(sku);
            // 再次点击已激活的 chip → 取消筛选
            currentSkuFilter = (currentSkuFilter === sku) ? '' : sku;
        }
        renderGoodsCard();
        if (isPhysicalGoods()) {
            renderTable();
            return;
        }
        table.reload('stock', { page: { curr: 1 }, where: tableWhere() });
    });

    /* ========= 工具栏按钮分发 ========= */
    $(document).on('click', '#stk-toolbar .stk-action-btn', function () {
        var evt = $(this).data('evt');
        if (evt === 'add')        return openAdd();
        if (evt === 'import')     return openImport();
        if (evt === 'clear-used') return openClearUsed();
        if (evt === 'clear-all')  return openClearAll();
        if (evt === 'export')     return openExport();
        if (evt === 'batch-del')  return batchDel();
    });

    /* -- 添加库存：核心类型走 stock_add_new；插件类型路由到插件原生 add 页 -- */
    // 插件商品类型 → 添加卡密 URL / layer.close id（对齐插件内 parent.layer.close(xxx)）
    var PLUGIN_STOCK_MAP = {
        'once':     { addUrl: '/?plugin=goods_once&action=add', editUrl: '/?plugin=goods_once&action=edit', delUrl: '/?plugin=goods_once&action=del', addLayerId: 'add', editLayerId: 'edit' },
        'general':  { addUrl: '/?plugin=goods_general&action=add', editUrl: '/?plugin=goods_general&action=edit', delUrl: '/?plugin=goods_general&action=del', addLayerId: 'add', editLayerId: 'edit' },
        'service':  { addUrl: '/?plugin=goods_service&action=add', editUrl: '/?plugin=goods_service&action=edit', delUrl: '/?plugin=goods_service&action=del', addLayerId: 'add', editLayerId: 'edit' }
    };

    function openPluginEditStock (stockId, tipMsg) {
        var cfg = GOODS ? PLUGIN_STOCK_MAP[GOODS.type] : null;
        if (!cfg || !stockId) return false;
        window.ws_table = { reload: reloadStockView };
        if (tipMsg) {
            layer.msg(tipMsg, { icon: 0, time: 1800 });
        }
        layer.open({
            id: cfg.editLayerId, type: 2, title: '编辑卡密',
            area: (window.innerWidth < 768) ? ['96%', '80%'] : ['640px', '560px'], skin: 'dc-layer-modern',
            content: cfg.editUrl + '&goods_id=' + SELECTED_GID + '&stock_id=' + stockId,
            shadeClose: true, fixed: false, maxmin: true
        });
        return true;
    }

    function openAdd () {
        if (!GOODS) { layer.msg('商品信息未加载完成，请稍候', { icon: 2 }); return; }
        if (window.DC_STOCK_ADD_HANDLERS && typeof window.DC_STOCK_ADD_HANDLERS[GOODS.type] === 'function') {
            return window.DC_STOCK_ADD_HANDLERS[GOODS.type]({
                goods: GOODS,
                goodsId: SELECTED_GID,
                reload: reloadStockView,
                layer: layer,
                currentSku: currentSkuFilter
            });
        }
        var cfg = PLUGIN_STOCK_MAP[GOODS.type];
        var tpl = getTemplateStatus();
        var currentTplSku = currentSkuFilter ? getTemplateSkuState(currentSkuFilter) : null;

        if (cfg && isTemplateStockGoods() && tpl && tpl.enabled) {
            if (currentTplSku && currentTplSku.template_exists) {
                if (currentTplSku.template_stock_id) {
                    return openPluginEditStock(currentTplSku.template_stock_id, '当前筛选规格已配置模板，已为你打开编辑页');
                }
                layer.msg('当前筛选规格已配置模板，请直接在列表中编辑卡密内容或可用次数', { icon: 0 });
                return;
            }
            if (tpl.all_locked) {
                if (tpl.single_stock_id) {
                    return openPluginEditStock(tpl.single_stock_id, '当前商品模板已配置完成，已为你打开编辑页');
                }
                layer.msg('当前商品的模板卡密已全部配置完成，请直接在列表中点击“编辑卡密”修改；多规格商品也可先点上方规格筛选后再编辑', { icon: 0, time: 2600 });
                return;
            }
        }

        window.ws_table = { reload: reloadStockView };
        var url, layerId;
        if (cfg) {
            url = cfg.addUrl + '&goods_id=' + SELECTED_GID;
            layerId = cfg.addLayerId;
        } else {
            // 核心类型（duli/guding/xuni 等）走 stock_add_new.php，其 iframe 用 parent.layer.close('add_stock')
            url = 'stock.php?action=stock_add_new&goods_id=' + SELECTED_GID;
            layerId = 'add_stock';
        }

        layer.open({
            id: layerId, type: 2, title: '添加库存',
            area: (window.innerWidth < 768) ? ['96%', '80%'] : ['640px', '560px'], skin: 'dc-layer-modern',
            content: url,
            shadeClose: true, fixed: false, maxmin: true
        });
    }

    /* -- 导入 -- */
    function openImport () {
        if (!canImportStock()) {
            layer.msg('当前商品类型暂不支持批量导入，请使用添加库存或编辑功能处理', { icon: 0 });
            return;
        }
        var skuOptions = '<option value="0">默认/无规格</option>';
        if (GOODS && GOODS.is_sku === 'y' && GOODS.skus && GOODS.skus.length) {
            skuOptions = '';
            GOODS.skus.forEach(function (s) {
                skuOptions += '<option value="' + s.sku + '">' + $('<div>').text(s.sku_name).html() + '</option>';
            });
        }

        var html = ''
            + '<div class="stk-import">'
            +   '<div class="stk-import-up" id="stk-import-drop">'
            +     '<i class="ri-upload-cloud-2-line"></i>'
            +     '<div style="font-size:14px;color:#333;" id="stk-import-fname">点击选择 .txt 文件</div>'
            +     '<div style="font-size:12px;color:#8a94a6;margin-top:4px;">或将文件拖到此处</div>'
            +     '<input id="stk-import-file" type="file" accept=".txt" style="display:none;">'
            +   '</div>'
            +   (GOODS && GOODS.is_sku === 'y' ? ('<div class="stk-import-sku"><label>导入规格：</label><select id="stk-import-sku">' + skuOptions + '</select></div>') : '')
            +   (GOODS && GOODS.type === 'once' ? '<div class="stk-import-batch"><label>批次号：</label><input id="stk-import-batch" type="text" placeholder="选填，用于搜索与筛选"></div>' : '')
            +   '<div class="stk-import-tip">'
            +     '<strong>文件格式</strong>：纯文本 .txt，<b>一行一个卡密</b>，空行会被忽略；<br>'
            +     '<strong>推荐使用</strong>：导入[去重] —— 会排除文件内重复以及数据库中该商品已存在的卡密。'
            +   '</div>'
            + '</div>';

        var idx = layer.open({
            type: 1, title: '导入库存', area: (window.innerWidth < 768) ? ['96%', 'auto'] : ['560px', 'auto'], skin: 'dc-layer-modern',
            content: html, shadeClose: false,
            btn: ['导入', '导入[去重]', '取消'],
            btn1: function () { return doImport(idx, 0); },
            btn2: function () { return doImport(idx, 1); },
            btn3: function () { layer.close(idx); }
        });

        $(document).off('click.imp').on('click.imp', '#stk-import-drop', function (e) {
            if ($(e.target).is('#stk-import-file')) return;
            var input = document.getElementById('stk-import-file');
            if (input) input.click();
        });
        $(document).off('change.imp').on('change.imp', '#stk-import-file', function () {
            var f = this.files && this.files[0];
            if (!f) return;
            if (!/\.txt$/i.test(f.name)) { layer.msg('仅支持 .txt 格式文件', { icon: 2 }); this.value = ''; return; }
            $('#stk-import-fname').text(f.name + ' (' + (f.size / 1024).toFixed(1) + ' KB)');
            $('#stk-import-drop').addClass('has-file');
        });

        function doImport (idx, dedup) {
            var file = document.getElementById('stk-import-file').files[0];
            if (!file) { layer.msg('请先选择 .txt 文件', { icon: 2 }); return false; }
            var sku = $('#stk-import-sku').val() || '0';
            var batchNo = $('#stk-import-batch').val() || '';
            var importMeta = {
                mode_text: dedup ? '导入[去重]' : '普通导入',
                goods_title: GOODS ? GOODS.title : '',
                file_name: file.name || '',
                file_size: file.size || 0,
                sku_name: getGoodsSkuText(sku),
                batch_no: batchNo,
                show_batch: GOODS && GOODS.type === 'once'
            };

            var fd = new FormData();
            fd.append('file', file);
            fd.append('goods_id', SELECTED_GID);
            fd.append('sku', sku);
            fd.append('batch_no', batchNo);
            fd.append('dedup', dedup);
            fd.append('token', TOKEN);

            var loading = layer.load(2);
            $.ajax({
                url: 'stock.php?action=import_ajax', type: 'POST', data: fd,
                processData: false, contentType: false, dataType: 'json',
                success: function (r) {
                    layer.close(loading);
                    if (!r || r.code !== 0) {
                        showImportErrorResult((r && r.msg) || '导入失败', importMeta);
                        return;
                    }
                    layer.close(idx);
                    showImportResult(r.data, importMeta);
                    table.reload('stock');
                    loadGoodsCard();
                },
                error: function (xhr) {
                    layer.close(loading);
                    var msg = '导入失败';
                    try { msg = (xhr.responseJSON && xhr.responseJSON.msg) || JSON.parse(xhr.responseText).msg || msg; } catch (e) {}
                    showImportErrorResult(msg, importMeta);
                }
            });
            return false;
        }
    }

    function parseImportErrorPayload (raw) {
        if ($.isPlainObject(raw)) return raw;
        if (typeof raw !== 'string') {
            return { message: '导入失败', failed: [], failed_total: 0 };
        }
        try {
            var parsed = JSON.parse(raw);
            if ($.isPlainObject(parsed)) {
                return parsed;
            }
        } catch (e) {}
        return { message: raw || '导入失败', failed: [], failed_total: 0 };
    }

    function showImportErrorResult (raw, meta) {
        var payload = parseImportErrorPayload(raw);
        var failed = $.isArray(payload.failed) ? payload.failed : [];
        var failedShown = failed.slice(0, 100);
        var failedTotal = parseInt(payload.failed_total, 10) || failed.length;
        var failedMore = Math.max(0, failedTotal - failedShown.length);
        if (!failedShown.length) {
            layer.msg(payload.message || '导入失败', { icon: 2 });
            return;
        }

        meta = meta || {};
        var goodsTitle = $('<div>').text(meta.goods_title || (GOODS ? GOODS.title : '')).html();
        var fileName = $('<div>').text(meta.file_name || '').html();
        var fileSizeText = formatImportFileSize(meta.file_size || 0);
        var skuText = $('<div>').text(meta.sku_name || '默认/无规格').html();
        var batchText = $('<div>').text(meta.batch_no || '未填写').html();
        var modeText = $('<div>').text(meta.mode_text || '普通导入').html();
        var messageText = $('<div>').text(payload.message || '导入失败').html();

        var html = '<div class="stk-result">'
            + '<div class="stk-result-row err"><span class="lbl">处理结果</span><span class="val">' + messageText + '</span></div>'
            + '<div class="stk-result-row"><span class="lbl">当前商品</span><span class="val">' + goodsTitle + '</span></div>'
            + '<div class="stk-result-row"><span class="lbl">导入模式</span><span class="val">' + modeText + '</span></div>'
            + '<div class="stk-result-row"><span class="lbl">导入文件</span><span class="val">' + fileName + (fileSizeText ? '（' + fileSizeText + '）' : '') + '</span></div>'
            + '<div class="stk-result-row"><span class="lbl">导入规格</span><span class="val">' + skuText + '</span></div>';
        if (meta.show_batch) {
            html += '<div class="stk-result-row"><span class="lbl">批次号</span><span class="val">' + batchText + '</span></div>';
        }
        html += '<div style="margin-top:10px;font-size:12px;color:#8a94a6;">失败卡密已直接展示如下：</div>';
        html += '<div class="stk-failed-list">';
        failedShown.forEach(function (f) {
            var content = $('<div>').text(f.content || '').html();
            var reason = $('<div>').text(f.reason || '写入失败').html();
            html += '<div>' + content + ' —— ' + reason + '</div>';
        });
        if (failedMore > 0) {
            html += '<div style="color:#8a94a6;">其余 ' + failedMore + ' 条未展开，请按需分批导入查看</div>';
        }
        html += '</div></div>';

        layer.open({ type: 1, title: '导入失败', area: (window.innerWidth < 768) ? ['96%', 'auto'] : ['560px', 'auto'], skin: 'dc-layer-modern', content: html, btn: ['确定'] });
    }

    function showImportResult (d, meta) {
        d = d || {};
        meta = meta || {};
        var total = parseInt(d.total, 10) || 0;
        var inserted = parseInt(d.inserted, 10) || 0;
        var fileDup = parseInt(d.file_dup, 10) || 0;
        var dbDup = parseInt(d.db_dup, 10) || 0;
        var ignored = Math.max(0, total - inserted);
        var failed = $.isArray(d.failed) ? d.failed : [];
        var failedShown = failed.slice(0, 100);
        var failedMore = Math.max(0, failed.length - failedShown.length);
        var ok = inserted > 0 ? 'ok' : 'warn';
        var resultText = inserted > 0 ? (ignored > 0 ? '导入完成，部分记录已跳过' : '导入完成，所有记录已写入') : '本次未写入新的卡密';
        var goodsTitle = $('<div>').text(meta.goods_title || (GOODS ? GOODS.title : '')).html();
        var fileName = $('<div>').text(meta.file_name || '').html();
        var fileSizeText = formatImportFileSize(meta.file_size || 0);
        var skuText = $('<div>').text(meta.sku_name || '默认/无规格').html();
        var batchText = $('<div>').text(meta.batch_no || '未填写').html();
        var modeText = $('<div>').text(meta.mode_text || (d.dedup ? '导入[去重]' : '普通导入')).html();
        var html = '<div class="stk-result">'
            + '<div class="stk-result-row ' + ok + '"><span class="lbl">处理结果</span><span class="val">' + resultText + '</span></div>'
            + '<div class="stk-result-row"><span class="lbl">当前商品</span><span class="val">' + goodsTitle + '</span></div>'
            + '<div class="stk-result-row"><span class="lbl">导入模式</span><span class="val">' + modeText + '</span></div>'
            + '<div class="stk-result-row"><span class="lbl">导入文件</span><span class="val">' + fileName + (fileSizeText ? '（' + fileSizeText + '）' : '') + '</span></div>'
            + '<div class="stk-result-row"><span class="lbl">导入规格</span><span class="val">' + skuText + '</span></div>';
        if (meta.show_batch) {
            html += '<div class="stk-result-row"><span class="lbl">批次号</span><span class="val">' + batchText + '</span></div>';
        }
        html += '<div class="stk-result-row"><span class="lbl">文件总数</span><span class="val">' + total + ' 条</span></div>';
        html += '<div class="stk-result-row ok"><span class="lbl">成功写入</span><span class="val">' + inserted + ' 条</span></div>';
        html += '<div class="stk-result-row' + (ignored > 0 ? ' warn' : '') + '"><span class="lbl">跳过数量</span><span class="val">' + ignored + ' 条</span></div>';
        if (d.dedup) {
            html += '<div class="stk-result-row warn"><span class="lbl">文件内重复</span><span class="val">' + fileDup + ' 条</span></div>';
            html += '<div class="stk-result-row err"><span class="lbl">数据库已存在</span><span class="val">' + dbDup + ' 条</span></div>';
        }
        html += '<div class="stk-result-row"><span class="lbl">刷新状态</span><span class="val">库存列表与商品信息已自动刷新</span></div>';
        if (failed.length) {
            html += '<div style="margin-top:10px;font-size:12px;color:#8a94a6;">失败卡密已直接展示如下（仅展示前 ' + failedShown.length + ' 条）：</div>';
            html += '<div class="stk-failed-list">';
            failedShown.forEach(function (f) {
                var content = $('<div>').text(f.content || '').html();
                var reason = $('<div>').text(f.reason || '写入失败').html();
                html += '<div>' + content + ' —— ' + reason + '</div>';
            });
            if (failedMore > 0) {
                html += '<div style="color:#8a94a6;">其余 ' + failedMore + ' 条未展开，请按需分批导入查看</div>';
            }
            html += '</div>';
        }
        html += '</div>';

        layer.open({ type: 1, title: '导入结果', area: (window.innerWidth < 768) ? ['96%', 'auto'] : ['560px', 'auto'], skin: 'dc-layer-modern', content: html, btn: ['确定'] });
    }

    /* -- 清空已使用（方案 D） -- */
    function openClearUsed () {
        var html = '<div class="stk-clearused">'
            + '<div style="color:#666;font-size:13px;line-height:1.7;">将清理当前商品下<strong>已发货订单</strong>中 N 天前的卡密记录（软删除 deliver 记录），订单本身、金额、买家信息不受影响。</div>'
            + '<div class="stk-clearused-opts">'
            +   '<div class="stk-clearused-opt" data-d="30">30 天前</div>'
            +   '<div class="stk-clearused-opt" data-d="90">90 天前</div>'
            +   '<div class="stk-clearused-opt active" data-d="180">180 天前</div>'
            +   '<div class="stk-clearused-opt" data-d="365">365 天前</div>'
            + '</div>'
            + '<div class="stk-clearused-custom"><label style="color:#666;">自定义：</label><input type="number" id="stk-cu-custom" placeholder="填入天数" min="1" max="3650"><span style="color:#666;">天前</span></div>'
            + '</div>';

        var idx = layer.open({
            type: 1, title: '清空已使用 · 选择清理范围', area: (window.innerWidth < 768) ? ['96%', 'auto'] : ['460px', 'auto'], skin: 'dc-layer-modern',
            content: html, btn: ['确认清理', '取消'],
            btn1: function () {
                var activeBtn = $('.stk-clearused-opt.active');
                var custom = parseInt($('#stk-cu-custom').val()) || 0;
                var days = custom > 0 ? custom : (activeBtn.length ? parseInt(activeBtn.data('d')) : 180);
                layer.confirm('确认清理 ' + days + ' 天前的已使用卡密记录？<br>该操作不可恢复。', { icon: 3, title: '二次确认', btn: ['确认', '取消'] }, function (c) {
                    layer.close(c);
                    ajaxJson('clear_used_ajax', { goods_id: SELECTED_GID, days: days }, 'POST', function (err, r) {
                        if (err) return layer.msg(err, { icon: 2 });
                        if (r.code !== 0) return layer.msg(r.msg || '清理失败', { icon: 2 });
                        layer.close(idx);
                        layer.msg('已清理 ' + (r.data && r.data.affected || 0) + ' 条已使用记录', { icon: 1 });
                        table.reload('stock');
                        loadGoodsCard();
                    });
                });
                return false;
            },
            btn2: function () { layer.close(idx); }
        });

        $(document).off('click.cu').on('click.cu', '.stk-clearused-opt', function () {
            $('.stk-clearused-opt').removeClass('active');
            $(this).addClass('active');
            $('#stk-cu-custom').val('');
        });
        $(document).off('input.cu').on('input.cu', '#stk-cu-custom', function () {
            if ($(this).val()) $('.stk-clearused-opt').removeClass('active');
        });
    }

    /* -- 清空全部 -- */
    function openClearAll () {
        var confirmText = isPhysicalGoods()
            ? '确定清空当前实物商品的<strong>所有可售库存</strong>吗？<br><span style="color:#cf1322;">此操作会把商品库存和规格库存清零，不可恢复！</span>'
            : '确定清空当前商品的<strong>所有未使用</strong>库存吗？<br><span style="color:#cf1322;">此操作不可恢复！</span>';
        layer.confirm(confirmText, {
            icon: 2, title: '危险操作确认', btn: ['我确定清空', '取消']
        }, function (c) {
            layer.close(c);
            ajaxJson('clear_all_ajax', { goods_id: SELECTED_GID }, 'POST', function (err, r) {
                if (err) return layer.msg(err, { icon: 2 });
                if (r.code !== 0) return layer.msg(r.msg || '清空失败', { icon: 2 });
                layer.msg(isPhysicalGoods() ? '已清空该商品所有可售库存' : '已清空该商品所有未使用库存', { icon: 1 });
                table.reload('stock');
                loadGoodsCard();
            });
        });
    }

    /* -- 导出 -- */
    function openExport () {
        var html = '<div style="padding:22px;text-align:center;">'
            + '<div style="font-size:14px;color:#333;margin-bottom:16px;">请选择导出模式（当前商品：<strong>' + (GOODS ? $('<div>').text(GOODS.title).html() : '') + '</strong>）</div>'
            + '<div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">'
            +   '<button class="stk-action-btn primary" data-mode="all"><i class="ri-file-list-3-line"></i> 全部（csv）</button>'
            +   '<button class="stk-action-btn success" data-mode="unused"><i class="ri-inbox-unarchive-line"></i> 未使用（txt）</button>'
            +   '<button class="stk-action-btn warn" data-mode="used"><i class="ri-check-double-line"></i> 已使用（csv）</button>'
            + '</div></div>';

        var idx = layer.open({ type: 1, title: '导出卡密', area: (window.innerWidth < 768) ? ['96%', 'auto'] : ['520px', 'auto'], skin: 'dc-layer-modern', content: html });

        $(document).off('click.exp').on('click.exp', '[data-mode]', function () {
            var m = $(this).data('mode');
            if (!m) return;
            layer.close(idx);
            var mp = { all: 'export_all', unused: 'export_unused', used: 'export_used' };
            window.location.href = 'stock.php?action=' + mp[m] + '&goods_id=' + SELECTED_GID + '&t=' + Date.now();
        });
    }

    /* -- 批量删除（仅未使用 tab）-- */
    function batchDel () {
        var rows = table.checkStatus('stock').data;
        if (!rows.length) return layer.msg('请先勾选要删除的条目');
        var ids = rows.map(function (r) { return r.stock_id; }).join(',');
        layer.confirm('确定删除选中的 ' + rows.length + ' 条库存吗？<br><span style="color:#cf1322;">此操作不可恢复！</span>', {
            icon: 2, title: '批量删除', btn: ['确认删除', '取消']
        }, function (c) {
            layer.close(c);
            var cfg = GOODS ? PLUGIN_STOCK_MAP[GOODS.type] : null;
            var req = cfg
                ? function (cb) { ajaxUrl(cfg.delUrl, { ids: ids, goods_id: SELECTED_GID }, 'POST', cb); }
                : function (cb) { ajaxJson('del_ajax', { ids: ids }, 'POST', cb); };
            req(function (err, r) {
                if (err) return layer.msg(err, { icon: 2 });
                if (r.code !== 0 && r.code !== 200) return layer.msg(r.msg || '删除失败', { icon: 2 });
                layer.msg('库存已删除', { icon: 1 });
                reloadStockView();
            });
        });
    }

    /* -- 单行操作 -- */
    table.on('tool(stock)', function (obj) {
        var d = obj.data, evt = obj.event;
        if (evt === 'physicalAdd') {
            openAdd();
            return;
        }
        if (evt === 'view') {
            layer.open({
                type: 1, title: '卡密详情',
                area: (window.innerWidth < 768) ? ['96%', '60%'] : ['560px', '420px'], skin: 'dc-layer-modern',
                content: '<div style="padding:18px;word-break:break-all;white-space:pre-wrap;line-height:1.7;font-family:Consolas,monospace;">' +
                          (d.content ? $('<div>').text(d.content).html() : '<span style="color:#999;">（空）</span>') +
                         '</div>'
            });
            return;
        }
        if (evt === 'edit' && currentTab === TAB_UNUSED) {
            var pluginCfg = GOODS ? PLUGIN_STOCK_MAP[GOODS.type] : null;
            if (pluginCfg) {
                openPluginEditStock(d.stock_id);
                return;
            }
            var qtyVal = d.quantity || 0;
            if (GOODS && GOODS.skus && GOODS.skus.length && d.sku) {
                GOODS.skus.some(function (s) {
                    if (String(s.sku) === String(d.sku)) {
                        qtyVal = s.stock || 0;
                        return true;
                    }
                    return false;
                });
            } else if (GOODS && GOODS.type !== 'duli') {
                qtyVal = GOODS.remaining || qtyVal;
            }
            var showQuantity = GOODS && GOODS.type !== 'duli';
            var html = '<div style="padding:18px;">'
                + (showQuantity
                    ? '<div class="layui-form-item"><label class="layui-form-label">库存数量</label><div class="layui-input-block"><input type="number" name="quantity" class="layui-input" value="' + qtyVal + '"></div></div>'
                    : '')
                + '<div class="layui-form-item"><label class="layui-form-label">卡密内容</label><div class="layui-input-block"><textarea name="content" class="layui-textarea" style="min-height:180px;">' + $('<div>').text(d.content || '').html() + '</textarea></div></div>'
                + '</div>';
            layer.open({
                type: 1, title: '编辑卡密', area: (window.innerWidth < 768) ? ['96%', 'auto'] : ['560px', 'auto'], skin: 'dc-layer-modern',
                content: html, btn: ['保存', '取消'],
                yes: function (idx, layero) {
                    var q = showQuantity ? (layero.find('input[name="quantity"]').val() || 0) : qtyVal;
                    var c = layero.find('textarea[name="content"]').val();
                    ajaxJson('edit_ajax', { stock_id: d.stock_id, quantity: q, content: c }, 'POST', function (err, r) {
                        if (err) return layer.msg(err, { icon: 2 });
                        if (r.code !== 0 && r.code !== 200) return layer.msg(r.msg || '保存失败', { icon: 2 });
                        layer.close(idx);
                        layer.msg('库存信息已保存', { icon: 1 });
                        reloadStockView();
                    });
                }
            });
            return;
        }
        if (evt === 'del' && currentTab === TAB_UNUSED) {
            layer.confirm('确认删除该卡密？', { icon: 3, btn: ['确认', '取消'] }, function (idx) {
                layer.close(idx);
                var cfg = GOODS ? PLUGIN_STOCK_MAP[GOODS.type] : null;
                var req = cfg
                    ? function (cb) { ajaxUrl(cfg.delUrl, { ids: d.stock_id, goods_id: SELECTED_GID }, 'POST', cb); }
                    : function (cb) { ajaxJson('del_ajax', { ids: d.stock_id }, 'POST', cb); };
                req(function (err, r) {
                    if (err) return layer.msg(err, { icon: 2 });
                    if (r.code !== 0 && r.code !== 200) return layer.msg(r.msg || '删除失败', { icon: 2 });
                    layer.msg('已删除', { icon: 1 });
                    reloadStockView();
                });
            });
            return;
        }
        if (evt === 'goorder') {
            window.open(d.order_list_url || ('order.php?out_trade_no=' + encodeURIComponent(d.out_trade_no || '')), '_blank');
            return;
        }
    });
});
</script>
