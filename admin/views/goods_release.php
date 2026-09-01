<?php defined('DC_ROOT') || exit('access denied!'); ?>
<link rel="stylesheet" type="text/css" href="./views/css/views/goods.css?t=<?= Option::DC_VERSION_TIMESTAMP ?>">
<style>
    .post-type{
        display: <?= $goods['type'] == 'post' ? 'block' : 'none' ?>
    }
    .goods-card-wrap {
        background: linear-gradient(0deg,#fff,#f3f5f8);
        border: 2px solid #fff;
        border-radius: 8px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        overflow: hidden;
        margin-bottom: 20px;
    }
    .goods-card-header {
        position:relative; height:auto; padding:12px 15px;
        border-bottom:1px solid #f0f0f0;
        display:flex; align-items:center; justify-content:center;
    }
    .goods-card-header .mac-dots { display:flex; align-items:center; gap:6px; position:absolute; left:15px; top:50%; transform:translateY(-50%); }
    .goods-card-header .mac-dots i { width:12px; height:12px; border-radius:50%; display:inline-block; }
    .goods-card-header .mac-dots .d1 { background:#ff5f57; }
    .goods-card-header .mac-dots .d2 { background:#febc2e; }
    .goods-card-header .mac-dots .d3 { background:#28c840; }
    .goods-card-header .card-title { color:#667797; font-size:14px; font-weight:500; }
    .goods-card-body { padding: 14px 16px 18px; }
/* 表单分区 */
.ge-section { margin-top: 22px; padding-top: 18px; border-top: 1px dashed #e0e0e0; }
.ge-section-title { font-size: 13px; font-weight: 600; color: #555; margin-bottom: 14px; display:flex; align-items:center; gap:6px; }
.ge-section-title i { color: #667797; font-size: 15px; }
.goods-card-body .layui-form-label { font-weight: 500; color: #333; }
.goods-card-body .layui-form-mid.layui-text-em { color: #999; font-size: 12px; }
.goods-card-body .layui-tabs-item { padding: 16px 0 0; }
/* 商品图（多图） */
.gallery-grid { display:flex; flex-wrap:wrap; gap:12px; align-items:flex-start; }
.gallery-cell { position:relative; width:100px; height:100px; border-radius:10px; overflow:hidden; background:#fafafa; border:1.5px solid #ececec; cursor:move; flex-shrink:0; transition:border-color .15s, box-shadow .15s; }
.gallery-cell:hover { border-color:#1e9fff; box-shadow:0 2px 8px rgba(30,159,255,.12); }
.gallery-cell.dragging { opacity:.4; }
.gallery-cell.drag-over { border-color:#1e9fff; border-style:dashed; background:#f0faff; }
.gallery-cell img { width:100%; height:100%; object-fit:cover; display:block; pointer-events:none; }
.gallery-cell.is-load-error { border-color:#f59e0b; background:#fff7ed; }
.gallery-cell.is-load-error img { opacity:.18; }
.gallery-cell.is-load-error::after { content:'图片加载失败'; position:absolute; left:6px; right:6px; top:50%; transform:translateY(-50%); color:#b45309; font-size:12px; line-height:1.5; text-align:center; word-break:break-all; }
.gallery-cell.is-load-error .gc-set-main { display:none; }
.gallery-cell .gc-main-badge { position:absolute; top:4px; left:4px; background:linear-gradient(135deg,#ff7043,#ff5722); color:#fff; font-size:11px; padding:2px 7px; border-radius:10px; line-height:1.4; font-weight:500; box-shadow:0 1px 3px rgba(255,87,34,.4); pointer-events:none; }
.gallery-cell .gc-del { position:absolute; top:4px; right:4px; width:20px; height:20px; border-radius:50%; background:rgba(0,0,0,.55); color:#fff; font-size:12px; line-height:20px; text-align:center; cursor:pointer; opacity:0; transition:opacity .15s, background .15s; }
.gallery-cell:hover .gc-del { opacity:1; }
.gallery-cell .gc-del:hover { background:#ff5722; }
.gallery-cell .gc-set-main { position:absolute; bottom:0; left:0; right:0; background:rgba(0,0,0,.55); color:#fff; font-size:11px; padding:3px 0; text-align:center; cursor:pointer; opacity:0; transition:opacity .15s; }
.gallery-cell:hover .gc-set-main { opacity:1; }
.gallery-cell.is-main .gc-set-main { display:none; }
.gallery-add-box { width:100px; height:100px; border:1.5px dashed #d9d9d9; border-radius:10px; display:flex; flex-direction:column; align-items:center; justify-content:center; cursor:pointer; transition:all .2s; background:#fafafa; flex-shrink:0; gap:4px; }
.gallery-add-box:hover { border-color:#1e9fff; background:#f0faff; }
.gallery-add-box .ga-plus { font-size:28px; color:#c0c4cc; line-height:1; transition:color .2s; }
.gallery-add-box .ga-text { font-size:12px; color:#999; }
.gallery-add-box:hover .ga-plus, .gallery-add-box:hover .ga-text { color:#1e9fff; }
.gallery-tip { font-size:12px; color:#999; margin-top:8px; line-height:1.6; }
.gallery-tip .num { color:#1e9fff; font-weight:600; }
/* 文件库弹窗 */
.media-lib { padding:0; font-family:inherit; display:flex; flex-direction:column; height:100%; overflow:hidden; }
.media-lib-header { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:14px 16px; border-bottom:1px solid #f0f0f0; }
.media-lib-header .ml-search { flex:1; max-width:260px; }
.media-lib-header .ml-search input { height:32px; border-radius:6px; }
.media-lib-drop { border:2px dashed #d9d9d9; border-radius:8px; margin:12px 16px; padding:28px 16px; text-align:center; color:#999; font-size:13px; transition:all .2s; cursor:pointer; }
.media-lib-drop.drag-over { border-color:#1e9fff; background:#f0faff; color:#1e9fff; }
.media-lib-drop i { font-size:32px; display:block; margin-bottom:6px; color:#c0c4cc; }
.media-lib-drop.drag-over i { color:#1e9fff; }
.media-lib-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(100px,1fr)); gap:2px 10px; padding:8px 16px 12px; flex:1; min-height:0; overflow-y:auto; }
.media-lib-grid::-webkit-scrollbar { width:5px; } .media-lib-grid::-webkit-scrollbar-thumb { background:#d9d9d9; border-radius:3px; }
.ml-item { position:relative; border-radius:1px; overflow:hidden; cursor:pointer; border:2px solid transparent; transition:all .15s; background:#f9f9f9; }
.ml-item .ml-img-wrap { position:relative; width:100%; aspect-ratio:1; overflow:hidden; }
.ml-item:hover { border-color:#1e9fff; transform:translateY(-1px); box-shadow:0 2px 8px rgba(0,0,0,.08); }
.ml-item.selected { border-color:#1e9fff; }
.ml-item.selected::after { content:'\e605'; font-family:'layui-icon'; position:absolute; top:4px; right:4px; background:#1e9fff; color:#fff; width:20px; height:20px; border-radius:50%; font-size:12px; display:flex; align-items:center; justify-content:center; }
.ml-item .ml-img-wrap img { width:100%; height:100%; object-fit:cover; display:block; }
.ml-item .ml-bar { height:24px; background:#f5f5f5; display:flex; align-items:center; justify-content:flex-end; padding:0 6px; border-top:1px solid #eee; }
.ml-item .ml-name { color:#666; font-size:10px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; flex:1; min-width:0; }
.ml-item .ml-del { color:#bbb; font-size:14px; cursor:pointer; transition:color .15s; flex-shrink:0; margin-left:4px; line-height:1; }
.ml-item .ml-del:hover { color:#ff5722; }
.media-lib-footer { display:flex; align-items:center; justify-content:space-between; padding:10px 16px; border-top:1px solid #f0f0f0; position:sticky; bottom:0; z-index:2147483647; background:#fff; flex-shrink:0; box-shadow:0 -2px 8px rgba(0,0,0,.06); }
.media-lib-pager { display:flex; align-items:center; gap:6px; }
.media-lib-pager .pg-btn { border:1px solid #e0e0e0; background:#fff; border-radius:4px; padding:3px 10px; cursor:pointer; font-size:12px; transition:all .15s; }
.media-lib-pager .pg-btn:hover { border-color:#1e9fff; color:#1e9fff; }
.media-lib-pager .pg-btn:disabled { opacity:.4; cursor:not-allowed; }
.media-lib-pager .pg-info { font-size:12px; color:#999; }
.ml-empty { grid-column:1/-1; text-align:center; padding:40px 0; color:#bbb; font-size:13px; }
.ml-loading { grid-column:1/-1; text-align:center; padding:40px 0; color:#999; font-size:13px; }
/* 批量删除按钮 */
.ml-batch-del { border:none; background:none; color:#ccc; font-size:13px; cursor:default; padding:4px 10px; transition:all .2s; pointer-events:none; display:inline-flex; align-items:center; gap:4px; }
.ml-batch-del.active { color:#ff5722; cursor:pointer; pointer-events:auto; }
.ml-batch-del.active:hover { color:#d84315; }
.media-lib-footer .ml-right-btns { display:flex; align-items:center; gap:8px; }
/* 移动端适配 */
@media screen and (max-width:768px) {
  .media-lib-header { flex-wrap:wrap; gap:8px; padding:10px 12px; }
  .media-lib-header .ml-search { max-width:100%; flex-basis:100%; order:2; }
  .media-lib-header #ml-upload-btn { order:1; margin-left:auto; font-size:12px; padding:0 10px; height:30px; line-height:30px; }
  .media-lib-drop { margin:8px 12px; padding:18px 12px; font-size:12px; }
  .media-lib-drop i { font-size:26px; margin-bottom:4px; }
  .media-lib-grid { grid-template-columns:repeat(auto-fill,minmax(80px,1fr)); gap:10px 8px; padding:6px 12px 10px; }
  .ml-item .ml-bar { height:22px; padding:0 4px; }
  .ml-item .ml-name { font-size:9px; }
  .ml-item .ml-del { font-size:12px; }
  .media-lib-footer { flex-wrap:wrap; gap:8px; padding:8px 12px; }
  .media-lib-pager { width:100%; justify-content:center; order:2; }
  .media-lib-pager .pg-btn { padding:4px 12px; font-size:11px; }
  .media-lib-footer .ml-right-btns { order:1; margin-left:auto; }
  .ml-batch-del { font-size:12px; padding:4px 6px; }
}
.ge-action-bar {
    text-align:center; padding: 20px 0 6px;
    border-top: 1px solid #f0f0f0; margin-top: 20px;
}
<?php if (!empty($isPopup)): ?>
    .goods-card-header { display:none!important; }
    .goods-card-wrap { margin-bottom:0; }
    body { padding: 10px 10px 0; padding-bottom: 68px; }
    .ge-action-bar {
        position:fixed; bottom:0; left:0; right:0; z-index:999;
        background:#fff; margin:0; padding:14px 0;
        box-shadow: 0 -2px 8px rgba(0,0,0,0.06);
    }
<?php endif; ?>
/* 下单输入框表格 */
.btx-table { width:100%; border-collapse:collapse; }
.btx-table th { background:#fafafa; font-weight:600; font-size:13px; color:#666; text-align:left; padding:10px 14px; border-bottom:2px solid #eee; }
.btx-table td { padding:10px 14px; border-bottom:1px solid #f0f0f0; font-size:13px; vertical-align:middle; }
.btx-table tr:hover td { background:#f7f9fc; }
</style>
<div class="goods-card-wrap">
<div class="goods-card-header">
    <span class="mac-dots"><i class="d1"></i><i class="d2"></i><i class="d3"></i></span>
    <span class="card-title"><?= $goods['id'] ? '编辑商品' : '添加商品' ?></span>
</div>
<div class="goods-card-body">

    <div class="layui-tabs order-tabs-wrapper" style="margin-bottom: 12px;" lay-options="{}">
        <ul class="layui-tabs-header">
            <li lay-id="aaa" class="layui-this"><a>基本信息</a></li>
            <li lay-id="bbb"><a>规格与价格</a></li>
            <li lay-id="ccc"><a>商品详情</a></li>
            <li lay-id="ddd"><a>下单输入框</a></li>
            <li lay-id="eee"><a>营销设置</a></li>
        </ul>
        <?= doAction('goods_eidt_tabs_head') ?>
        <form class="layui-form" id="form" method="post" action="goods_save.php">
            <input type="hidden" name="goods_id" value="<?= $goods['id'] ?>" />
            <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
            <div class="layui-tabs-body">
                <div class="layui-tabs-item layui-show">
                    <div class="ge-section-title" style="border-top:none;margin-top:0;padding-top:0;"><i class="ri-shopping-bag-line"></i> 商品基本信息</div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">
                            商品类型<a href="store.php?action=plu&category=goods" target="_blank" style="margin-left: 20px; color: #1e9fff;">+ 添加更多类型</a>
                        </label>
                        <?php if($action == 'edit'): ?>
                            <input type="hidden" name="type" id="goods-base-type" value="<?= $goods['type'] ?>" />
                        <?php endif; ?>
                        <div class="layui-input-block">
                            <?php if(!empty($goods['goods_type_all'])): ?>
                            <?php
                                $fixedTypeOrder = ['once' => 10, 'general' => 20, 'service' => 30, 'physical' => 40, 'docking' => 50, 'qingjiu' => 60, 'xiaoqing' => 65, 'yiciyuan' => 70, 'mcy' => 80];
                                $fixedTypeNameOrder = ['一卡一密' => 10, '通用卡密' => 20, '虚拟服务' => 30, '实物发货' => 40, '同系统对接' => 50];
                                $goodsTypeItems = [];
                                foreach ($goods['goods_type_all'] as $typeIndex => $typeItem) {
                                    $typeValue = (string)($typeItem['value'] ?? '');
                                    $typeName = (string)($typeItem['name'] ?? '');
                                    $typeItem['_sort_order'] = $fixedTypeOrder[$typeValue] ?? ($fixedTypeNameOrder[$typeName] ?? 1000);
                                    $typeItem['_sort_index'] = $typeIndex;
                                    $goodsTypeItems[] = $typeItem;
                                }
                                usort($goodsTypeItems, function($a, $b) {
                                    if ($a['_sort_order'] == $b['_sort_order']) {
                                        return $a['_sort_index'] <=> $b['_sort_index'];
                                    }
                                    return $a['_sort_order'] <=> $b['_sort_order'];
                                });
                            ?>
                            <div class="goods-type-cards">
                                <?php foreach($goodsTypeItems as $val): ?>
                                <?php $isActive = $goods['type'] == $val['value'] || (!empty($goods['is_docking']) && ($goods['docking_type'] ?? '') === 'docking' && $val['value'] == 'docking') || (!empty($goods['is_qingjiu']) && $val['value'] == 'qingjiu') || (!empty($goods['is_xiaoqing']) && $val['value'] == 'xiaoqing') || (!empty($goods['is_yiciyuan']) && $val['value'] == 'yiciyuan') || (!empty($goods['is_mcy']) && $val['value'] == 'mcy'); ?>
                                <label class="goods-type-card <?= $isActive ? 'active' : '' ?> <?= $action == 'edit' ? 'disabled' : '' ?>">
                                    <input <?= $action == 'edit' ? 'disabled' : '' ?> lay-ignore lay-filter="goods-type-radio" type="radio" name="type" value="<?= $val['value'] ?>" <?= $isActive ? 'checked' : '' ?>>
                                    <i class="<?php
                                        $type_icons = ['once'=>'ri-key-2-line','general'=>'ri-database-2-line','service'=>'ri-customer-service-2-line','post'=>'ri-links-line'];
                                        echo $type_icons[$val['value']] ?? 'ri-apps-line';
                                    ?>"></i>
                                    <span><?= $val['name'] ?></span>
                                    <i class="ri-check-line goods-type-check"></i>
                                </label>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <span class="form-tips">
                                未启用任何商品类型插件，请前往<a href="plugin.php" style="color: #1e9fff;">插件管理菜单</a>启用插件或<a href="store.php?action=plu&category=goods" style="color: #1e9fff;">前往应用市场</a>安装更多商品功能插件
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">商品分类</label>
                        <div class="layui-input-block">
                            <select name="sort_id">
                                <option value="">选择分类</option>
                                <?php
                                    foreach ($sorts as $key => $value):
                                        if ($value['pid'] != 0) {
                                            continue;
                                        }
                                        $flg = $value['sid'] == $goods['sort_id'] ? 'selected' : '';
                                ?>
                                    <option value="<?= $value['sid'] ?>" <?= $flg ?>><?= $value['sortname'] ?></option>
                                    <?php
                                        $children = $value['children'];
                                        foreach ($children as $key):
                                            $value = $sorts[$key];
                                            $flg = $value['sid'] == $goods['sort_id'] ? 'selected' : '';
                                    ?>
                                        <option value="<?= $value['sid'] ?>" <?= $flg ?>>&nbsp; &nbsp; &nbsp; <?= $value['sortname'] ?></option>
                                    <?php
                                        endforeach;
                                        endforeach;
                                    ?>
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">商品名称</label>
                        <div class="layui-input-block">
                            <input type="text" name="title" class="layui-input" value="<?= $goods['title'] ?>">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">数量单位名称</label>
                        <div class="layui-input-block">
                            <input type="text" name="unit_name" class="layui-input" value="<?= htmlspecialchars(isset($goods['unit_name']) ? $goods['unit_name'] : '') ?>" placeholder="如：/个、/张、/斤、/件">
                            <div class="layui-form-mid layui-text-em">用于前台商品价格后的数量单位显示，如 /个,/张,/斤,/件，可留空，默认"/个"</div>
                        </div>
                    </div>

                    <div class="layui-form-item post-type">
                        <label class="layui-form-label">URL</label>
                        <div class="layui-input-block">
                            <input name="post_url" id="" type="text" class="layui-input" placeholder="请输入访问的URL" value="<?= $goods['post_url'] ?>">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">商品图</label>
                        <div class="layui-input-block">
                            <div class="gallery-grid" id="gallery-grid">
                                <?php $_gl = !empty($goods['gallery_list']) ? $goods['gallery_list'] : ($goods['cover'] ? [$goods['cover']] : []); ?>
                                <?php foreach($_gl as $_i => $_u): ?>
                                <div class="gallery-cell <?= $_i === 0 ? 'is-main' : '' ?>" data-url="<?= htmlspecialchars($_u) ?>" draggable="true">
                                    <img src="<?= htmlspecialchars($_u) ?>" alt="" onerror="this.onerror=null;this.parentElement.classList.add('is-load-error');">
                                    <?php if($_i === 0): ?><span class="gc-main-badge">主图</span><?php endif; ?>
                                    <span class="gc-del" title="移除">×</span>
                                    <div class="gc-set-main" title="设为主图">设为主图</div>
                                </div>
                                <?php endforeach; ?>
                                <div class="gallery-add-box" id="gallery-add-box">
                                    <span class="ga-plus">+</span>
                                    <span class="ga-text">添加图片</span>
                                </div>
                            </div>
                            <input type="hidden" name="cover" id="sortimg" value="<?= htmlspecialchars($goods['cover'] ?? '') ?>">
                            <div class="gallery-tip">
                                第一张为商品主图（前台列表、订单、分享均使用主图）。最多上传 <span class="num">10</span> 张，拖拽可调整顺序，悬停图片可"设为主图"或移除。
                            </div>
                        </div>
                    </div>

                    <div class="ge-section">
                        <div class="ge-section-title"><i class="ri-settings-3-line"></i> 发布设置</div>
                        <div class="layui-form-item">
                            <div class="layui-input-block" style="display:flex;flex-wrap:wrap;gap:16px;">
                                <input type="checkbox" value="1" name="is_on_shelf" title="上架" <?= $goods['is_on_shelf'] == 1 ? 'checked' : '' ?>>
                                <input type="checkbox" value="1" name="index_top" title="首页置顶" <?= $goods['index_top'] == 1 ? 'checked' : '' ?>>
                                <input type="checkbox" value="1" name="sort_top" title="分类置顶" <?= $goods['sort_top'] == 1 ? 'checked' : '' ?>>
                                <input type="checkbox" value="1" name="allow_dock" title="允许被对接" <?= (!isset($goods['allow_dock']) || $goods['allow_dock'] == 1) ? 'checked' : '' ?>>
                            </div>
                            <div class="layui-form-mid layui-text-em" style="margin-left:0;">上架后前台可见；置顶商品优先展示在首页或所属分类列表顶部</div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">排序权重</label>
                            <div class="layui-input-block">
                                <input type="number" name="sort_num" class="layui-input" value="<?= $goods['sort_num'] ?>">
                                <div class="layui-form-mid layui-text-em">数字越大，前台展示越靠前（置顶状态相同时生效）</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="layui-tabs-item">
                    <div class="ge-section-title" style="border-top:none;margin-top:0;padding-top:0;"><i class="ri-list-settings-line"></i> 规格设置</div>
                    <div class="sku-guide-tip" id="sku-mode-tip">
                        <i class="ri-information-line" style="color:#1e9fff;"></i>
                        <b>单规格</b>：商品仅有一种价格和库存，直接填写价格即可。
                        <b>多规格</b>：商品有多个版本（如不同颜色/套餐），需先选择规格模板并勾选规格值，系统自动生成 SKU 价格组合。
                    </div>
                    <div class="fairy-form form">
                        <div id="s1">
                            <div id="fairy-is-attribute"></div>
                            <div id="fairy-product-type"></div>
                            <div id="fairy-attribute-table"></div>
                            <div id="fairy-spec-table"></div>
                        </div>
                        <div class="sku-price-divider">
                            <div class="ge-section-title"><i class="ri-money-cny-circle-line"></i> 价格与库存</div>
                            <div class="sku-guide-tip" id="sku-price-tip">
                                <i class="ri-information-line" style="color:#1e9fff;"></i>
                                <span id="sku-price-tip-single"><b style="color:#4caf50;">游客价</b>、<b style="color:#4caf50;">成本价</b>必填；<b style="color:#e53e3e;">固定价</b>选填（填写后所有登录用户统一按此价，覆盖等级算价）；<b>划线价</b>选填（默认0）。<br><span style="color:#e67e22;"><i class="ri-alert-line"></i> 游客价不建议低于成本价，建议 ≥ 最低等级会员价，否则用户登录后反而看到更高价格。</span></span>
                                <span id="sku-price-tip-multi" style="display:none;">勾选规格值后自动生成 SKU 组合，点击表头 <i class="fa fa-edit" style="color:#1e9fff;"></i> 图标可批量设置整列值。<b style="color:#4caf50;">游客价</b>、<b style="color:#4caf50;">成本价</b>必填；<b style="color:#e53e3e;">固定价</b>选填（填写后所有登录用户统一按此价，覆盖等级算价）；<b>划线价</b>选填（默认0）。<br><span style="color:#e67e22;"><i class="ri-alert-line"></i> 游客价不建议低于成本价，建议 ≥ 最低等级会员价，否则用户登录后反而看到更高价格。</span></span>
                            </div>
                        </div>
                        <div id="fairy-sku-table"></div>

                        <?php
                        $lpPreviewCostDefault = '';
                        if (($action ?? '') === 'edit' && ($goods['is_sku'] ?? 'n') === 'n' && !empty($goods['skus'][0]['cost_price'])) {
                            $lpPreviewCostDefault = number_format(((float)$goods['skus'][0]['cost_price']) / 100, 2, '.', '');
                        }
                        $lpOverrideMode = 'inherit';
                        if (!empty($goods['has_member_price'])) {
                            $lpOverrideMode = 'manual';
                        } elseif ((int)($goods['single_rule_id'] ?? 0) > 0) {
                            $lpOverrideMode = 'single_rule';
                        } elseif (abs((float)($goods['profit_ratio'] ?? 100) - 100) > 0.0001) {
                            $lpOverrideMode = 'profit_ratio';
                        } elseif ((int)($goods['profit_rule_id'] ?? 0) > 0) {
                            $lpOverrideMode = 'profit_rule';
                        }
                        $lpAdvancedOpen = $lpOverrideMode !== 'inherit';
                        ?>

                        <!-- ===== 会员等级定价 ===== -->
                        <div class="ge-section-title" style="margin-top:28px;"><i class="ri-price-tag-3-line"></i> 会员等级定价</div>

                        <!-- 定价模式说明卡片 -->
                        <div class="lp-mode-card">
                            <div class="lp-mode-header">
                                <i class="ri-lightbulb-line" style="color:#ff9800;font-size:18px;"></i>
                                <span>推荐做法：先继承会员等级默认规则</span>
                            </div>
                            <div class="lp-mode-body">
                                <div class="lp-mode-flow">
                                    <div class="lp-flow-step">
                                        <div class="lp-flow-icon" style="background:#e8f5e9;color:#4caf50;"><i class="ri-money-cny-circle-line"></i></div>
                                        <div class="lp-flow-text">
                                            <b>填写成本价</b>
                                            <span>上方价格表中的「成本价」</span>
                                        </div>
                                    </div>
                                    <div class="lp-flow-arrow"><i class="ri-arrow-right-s-line"></i></div>
                                    <div class="lp-flow-step">
                                        <div class="lp-flow-icon" style="background:#e3f2fd;color:#1e88e5;"><i class="ri-calculator-line"></i></div>
                                        <div class="lp-flow-text">
                                            <b>默认继承会员等级规则</b>
                                            <span>按会员等级里的默认规则自动计算</span>
                                        </div>
                                    </div>
                                    <div class="lp-flow-arrow"><i class="ri-arrow-right-s-line"></i></div>
                                    <div class="lp-flow-step">
                                        <div class="lp-flow-icon" style="background:#fff3e0;color:#f57c00;"><i class="ri-user-star-line"></i></div>
                                        <div class="lp-flow-text">
                                            <b>特殊商品再覆盖</b>
                                            <span>只有例外商品才在本页单独设置</span>
                                        </div>
                                    </div>
                                </div>
                            <!--<div class="lp-decision-grid">
                                    <div class="lp-decision-card">
                                        <div class="lp-decision-badge">推荐</div>
                                        <div class="lp-decision-title">默认模式：继承会员等级规则</div>
                                        <div class="lp-decision-desc">商品只填成本价即可。系统会读取会员等级里的默认加价比例和自动调节规则来计算会员价。</div>
                                    </div>
                                    <div class="lp-decision-card is-warn">
                                        <div class="lp-decision-badge">例外</div>
                                        <div class="lp-decision-title">覆盖模式：只给少数商品单独设置</div>
                                        <div class="lp-decision-desc">只有这个商品真的想走特殊价格时，才在下方启用单独定价、商品专属规则或商品自动调节规则。</div>
                                    </div>
                                </div>
                                <div class="lp-mode-tip">
                                    <i class="ri-checkbox-circle-line" style="color:#4caf50;"></i>
                                    <b>大多数情况下，你只需要填写 游客价 和 成本价 就够了。</b>系统会根据<a href="<?= DC_URL ?>admin/member.php" target="_blank">会员等级</a>里设置的默认规则自动为每个等级计算售价。如果这个商品压根不想走等级算价，直接填写上方<b style="color:#e53e3e;">固定价</b>即可。
                                </div>-->
                            </div>
                        </div>

                        <!-- 等级价实时预览 -->
                        <div class="lp-preview-section">
                            <div class="lp-preview-bar">
                                <span class="lp-preview-label"><i class="ri-eye-line"></i> 当前规则下的各等级售价预览</span>
                                <div class="lp-preview-input">
                                    <input id="lv-preview-cost" type="number" step="0.01" class="layui-input" placeholder="先输入要预览的成本价" value="<?= htmlspecialchars($lpPreviewCostDefault) ?>" style="width:200px;height:32px;">
                                    <span style="color:#999;font-size:12px;">元</span>
                                    <button type="button" class="layui-btn layui-btn-sm layui-btn-normal" id="lv-preview-btn"><i class="ri-refresh-line"></i> 预览价格</button>
                                </div>
                            </div>
                            <div class="lp-preview-note"><i class="ri-information-line"></i>这里填写的成本价只用于试算当前规则下的会员售价，不会直接保存到商品价格里。</div>
                            <div class="lp-preview-status" id="lv-preview-status">当前商品正在继承会员等级默认规则。你只需要填写成本价，系统就会按各等级默认规则自动算价。</div>
                            <div id="lv-preview-result" style="display:none;">
                                <table class="layui-table" style="margin:0;">
                                    <thead>
                                        <tr>
                                            <th>等级</th>
                                            <th style="width:140px;">会员售价</th>
                                            <th>计算方式</th>
                                        </tr>
                                    </thead>
                                    <tbody id="lv-preview-tbody"></tbody>
                                </table>
                            </div>
                            <div id="lv-preview-empty" class="lp-preview-empty">
                                <i class="ri-line-chart-line" style="font-size:28px;color:#d0d5dd;"></i>
                                <span>点击「预览价格」查看当前继承/覆盖规则算出来的各等级售价</span>
                            </div>
                        </div>

                        <!-- 独立定价（高级，可折叠） -->
                        <div class="lp-advanced-section">
                            <div class="lp-advanced-toggle" id="lp-adv-toggle">
                                <i class="ri-arrow-right-s-line lp-adv-arrow<?= $lpAdvancedOpen ? ' open' : '' ?>" id="lp-adv-arrow"></i>
                                <span>商品特殊覆盖设置</span>
                                <span class="lp-adv-desc">（明确选择一种覆盖方式，避免多种规则叠在一起）</span>
                            </div>
                            <div class="lp-advanced-body" id="lp-adv-body" style="display:<?= $lpAdvancedOpen ? 'block' : 'none' ?>;">
                                <div class="sku-guide-tip" style="margin-bottom:16px;">
                                    <i class="ri-information-line" style="color:#1e9fff;"></i>
                                    默认推荐继承会员等级规则。只有这个商品确实要例外时，再在下面明确选择一种覆盖方式；系统只会提交你当前选中的这一种商品级规则。
                                </div>
                                <input type="hidden" name="pricing_override_mode" id="pricing_override_mode" value="<?= htmlspecialchars($lpOverrideMode) ?>">
                                <div class="lp-mode-picker" id="lp-mode-picker">
                                    <div class="lp-mode-option<?= $lpOverrideMode === 'inherit' ? ' is-active' : '' ?>" data-mode="inherit">
                                        <div class="lp-mode-option-badge">推荐</div>
                                        <div class="lp-mode-option-title">继续继承会员等级默认规则</div>
                                        <div class="lp-mode-option-desc">不做商品级覆盖。商品只填成本价，会员价继续按会员等级里的默认规则自动计算。</div>
                                    </div>
                                    <div class="lp-mode-option<?= $lpOverrideMode === 'manual' ? ' is-active' : '' ?>" data-mode="manual">
                                        <div class="lp-mode-option-badge">例外</div>
                                        <div class="lp-mode-option-title">只给部分等级单独定价</div>
                                        <div class="lp-mode-option-desc">只覆盖你手动填写的等级。没填的等级仍然继承会员等级默认规则。</div>
                                    </div>
                                    <div class="lp-mode-option<?= $lpOverrideMode === 'single_rule' ? ' is-active' : '' ?>" data-mode="single_rule">
                                        <div class="lp-mode-option-badge">规则</div>
                                        <div class="lp-mode-option-title">给这个商品绑定专属加价规则</div>
                                        <div class="lp-mode-option-desc">让当前商品统一使用自己的一套规则，适合这类商品整体都想按另一种梯度来卖。</div>
                                    </div>
                                    <div class="lp-mode-option<?= $lpOverrideMode === 'profit_ratio' ? ' is-active' : '' ?>" data-mode="profit_ratio">
                                        <div class="lp-mode-option-badge">折扣</div>
                                        <div class="lp-mode-option-title">整体调低这个商品的加价力度</div>
                                        <div class="lp-mode-option-desc">保留会员等级的默认加价基准，但统一按一个折扣比例缩放，不再继续套自动调节规则。</div>
                                    </div>
                                    <div class="lp-mode-option<?= $lpOverrideMode === 'profit_rule' ? ' is-active' : '' ?>" data-mode="profit_rule">
                                        <div class="lp-mode-option-badge">自动</div>
                                        <div class="lp-mode-option-title">按这个商品自己的成本区间调节</div>
                                        <div class="lp-mode-option-desc">给当前商品单独绑定成本自动调节规则，覆盖会员等级里默认绑定的调节规则。</div>
                                    </div>
                                </div>

                                <div class="lp-mode-panel<?= $lpOverrideMode === 'inherit' ? ' is-active' : '' ?>" data-mode-panel="inherit">
                                    <div class="lp-mode-panel-tip">
                                        <i class="ri-checkbox-circle-line" style="color:#16a34a;"></i>
                                        当前商品不做任何商品级覆盖。保存后会继续继承会员等级里的默认加价比例和自动调节规则。
                                    </div>
                                </div>

                                <div class="lp-mode-panel<?= $lpOverrideMode === 'manual' ? ' is-active' : '' ?>" data-mode-panel="manual">
                                    <div class="lp-mode-panel-tip">
                                        <i class="ri-price-tag-3-line" style="color:#2563eb;"></i>
                                        只给需要例外的等级填写单独售价。留空的等级仍然继续继承会员等级默认规则。
                                    </div>
                                    <div id="lp-manual-prices">
                                    <div class="lp-manual-table-wrap">
                                        <table class="layui-table" style="margin:0;">
                                            <thead>
                                                <tr>
                                                    <th style="width:180px;">会员等级</th>
                                                    <th style="width:200px;">独立售价（元）</th>
                                                    <th>说明</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $memberModel2 = new Member_Model();
                                                $activeLevels = $memberModel2->getActiveList();
                                                // 已有的独立价格
                                                $_existPrices = [];
                                                if (!empty($goods['id'])) {
                                                    $db2 = Database::getInstance();
                                                    $_mpRows = $db2->fetch_all("SELECT * FROM " . DB_PREFIX . "member_price WHERE goods_id=" . (int)$goods['id'] . " AND sku='0'");
                                                    foreach ($_mpRows as $_mp) { $_existPrices[(int)$_mp['member_level']] = (int)$_mp['price']; }
                                                }
                                                foreach ($activeLevels as $_lv):
                                                    $_lvId = (int)$_lv['id'];
                                                    $_hasPrice = isset($_existPrices[$_lvId]);
                                                    $_priceVal = $_hasPrice ? number_format($_existPrices[$_lvId] / 100, 2, '.', '') : '';
                                                ?>
                                                <tr>
                                                    <td>
                                                        <span style="font-weight:600;color:#333;"><?= htmlspecialchars($_lv['name']) ?></span>
                                                        <span style="color:#999;font-size:12px;">（默认规则：加价 <?= (float)$_lv['markup_ratio'] ?>%）</span>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" min="0" name="skus[member][<?= $_lvId ?>]" class="layui-input" style="height:32px;" value="<?= $_priceVal ?>" placeholder="留空=自动计算" <?= $lpOverrideMode === 'manual' ? '' : 'disabled' ?>>
                                                    </td>
                                                    <td style="color:#999;font-size:12px;"><?= $_hasPrice ? '已设置单独售价，保存后优先使用' : '留空 = 继续继承会员等级默认规则' ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="lp-manual-tip">
                                        <i class="ri-error-warning-line" style="color:#ff9800;"></i>
                                        只需填写需要特殊定价的等级，<b>留空 = 继续继承会员等级默认规则</b>。不需要全部填写。
                                    </div>
                                </div>
                                </div>

                                <div class="lp-mode-panel<?= $lpOverrideMode === 'single_rule' ? ' is-active' : '' ?>" data-mode-panel="single_rule">
                                    <div class="lp-mode-panel-tip">
                                        <i class="ri-settings-4-line" style="color:#7c3aed;"></i>
                                        当前商品将使用自己的专属加价规则，适合整类商品都想统一换一套定价梯度时使用。
                                    </div>
                                    <div class="layui-form-item">
                                        <label class="layui-form-label">商品专属加价规则</label>
                                        <div class="layui-input-inline" style="width: 260px;">
                                            <select name="single_rule_id" <?= $lpOverrideMode === 'single_rule' ? '' : 'disabled' ?>>
                                                <option value="0">请选择规则</option>
                                                <?php foreach (($singleRules ?? []) as $r): ?>
                                                    <option value="<?= (int)$r['id'] ?>" <?= (int)($goods['single_rule_id'] ?? 0) === (int)$r['id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['name']) ?> (<?= intval($r['type'] ?? 1) === 2 ? '百分比' : '固定' ?>加价)</option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="layui-form-mid layui-text-em">让这个商品使用自己的一套加价规则，会覆盖会员等级默认规则（<a href="<?= DC_URL ?>admin/price_rule.php?tab=single" target="_blank">管理规则</a>）</div>
                                    </div>
                                </div>

                                <div class="lp-mode-panel<?= $lpOverrideMode === 'profit_ratio' ? ' is-active' : '' ?>" data-mode-panel="profit_ratio">
                                    <div class="lp-mode-panel-tip">
                                        <i class="ri-scales-3-line" style="color:#ea580c;"></i>
                                        当前商品会在会员等级默认加价的基础上，再统一乘一个折扣比例。适合只想整体调低加价力度时使用。
                                    </div>
                                    <div class="layui-form-item">
                                        <label class="layui-form-label">商品加价力度折扣</label>
                                        <div class="layui-input-inline" style="width: 160px;">
                                            <input type="number" name="profit_ratio" class="layui-input" min="0" max="100" step="0.01" value="<?= (float)($goods['profit_ratio'] ?? 100) ?>" <?= $lpOverrideMode === 'profit_ratio' ? '' : 'disabled' ?>>
                                        </div>
                                        <div class="layui-form-mid layui-text-em">0~100%，默认 100。80 = 在默认加价基础上按 8 折加价；设置后会直接使用这一折扣，不再继续套自动调节规则。</div>
                                    </div>
                                </div>

                                <div class="lp-mode-panel<?= $lpOverrideMode === 'profit_rule' ? ' is-active' : '' ?>" data-mode-panel="profit_rule">
                                    <div class="lp-mode-panel-tip">
                                        <i class="ri-line-chart-line" style="color:#0891b2;"></i>
                                        当前商品会按自己绑定的成本区间规则自动调节加价，适合成本跨度特别大的商品。
                                    </div>
                                    <div class="layui-form-item">
                                        <label class="layui-form-label">商品成本自动调节规则</label>
                                        <div class="layui-input-inline" style="width: 260px;">
                                            <select name="profit_rule_id" <?= $lpOverrideMode === 'profit_rule' ? '' : 'disabled' ?>>
                                                <option value="0">请选择规则</option>
                                                <?php foreach (($profitRules ?? []) as $r): ?>
                                                    <option value="<?= (int)$r['id'] ?>" <?= (int)($goods['profit_rule_id'] ?? 0) === (int)$r['id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="layui-form-mid layui-text-em">让这个商品按自己的成本区间调节加价，会覆盖会员等级里绑定的自动调节规则（<a href="<?= DC_URL ?>admin/price_rule.php?tab=profit" target="_blank">管理规则</a>）</div>
                                    </div>
                                </div>

                                <div class="ge-section" style="margin-top:16px;padding-top:16px;">
                                    <div class="ge-section-title" style="margin-top:0;padding-top:0;"><i class="ri-settings-3-line"></i> 显示设置</div>
                                    <div class="layui-form-item">
                                        <label class="layui-form-label">价格保留位数</label>
                                        <div class="layui-input-inline" style="width: 100px;">
                                            <input type="number" name="accuracy" class="layui-input" min="0" max="8" step="1" value="<?= (int)($goods['accuracy'] ?? 2) ?>">
                                        </div>
                                        <div class="layui-form-mid layui-text-em">前台价格显示保留几位小数，默认 2 位。</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

<style>
/* 会员定价模块样式 */
.lp-mode-card { background:#fafbfc; border:1px solid #eef0f3; border-radius:8px; margin-bottom:18px; overflow:hidden; }
.lp-mode-header { padding:10px 16px; background:#f5f7fa; border-bottom:1px solid #eef0f3; display:flex; align-items:center; gap:8px; font-weight:600; font-size:14px; color:#333; }
.lp-mode-body { padding:16px; }
.lp-mode-flow { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:14px; }
.lp-flow-step { display:flex; align-items:center; gap:10px; background:#fff; border:1px solid #eee; border-radius:8px; padding:10px 14px; min-width:170px; }
.lp-flow-icon { width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
.lp-flow-text { display:flex; flex-direction:column; gap:2px; }
.lp-flow-text b { font-size:13px; color:#333; }
.lp-flow-text span { font-size:11px; color:#999; }
.lp-flow-arrow { color:#ccc; font-size:20px; flex-shrink:0; }
.lp-mode-tip { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; padding:10px 14px; font-size:12px; color:#333; display:flex; align-items:flex-start; gap:6px; }
.lp-mode-tip b { color:#16a34a; }
.lp-mode-tip a { color:#1e9fff; }
.lp-decision-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:12px; margin-bottom:14px; }
.lp-decision-card { background:#fff; border:1px solid #dbeafe; border-radius:8px; padding:12px 14px; }
.lp-decision-card.is-warn { border-color:#fde68a; background:#fffbeb; }
.lp-decision-badge { display:inline-block; padding:2px 8px; border-radius:999px; background:#dbeafe; color:#1d4ed8; font-size:12px; margin-bottom:8px; }
.lp-decision-title { font-size:13px; font-weight:600; color:#1f2937; margin-bottom:4px; }
.lp-decision-desc { font-size:12px; color:#6b7280; line-height:1.8; }

.lp-preview-section { background:#fafbfc; border:1px solid #eef0f3; border-radius:8px; margin-bottom:18px; overflow:hidden; }
.lp-preview-bar { display:flex; align-items:center; justify-content:space-between; padding:10px 16px; background:#f5f7fa; border-bottom:1px solid #eef0f3; flex-wrap:wrap; gap:8px; }
.lp-preview-label { font-weight:600; font-size:13px; color:#333; display:flex; align-items:center; gap:5px; }
.lp-preview-input { display:flex; align-items:center; gap:6px; }
.lp-preview-note { padding:8px 16px; background:#fff7ed; border-bottom:1px solid #fed7aa; color:#9a3412; font-size:12px; display:flex; align-items:center; gap:6px; }
.lp-preview-status { padding:12px 16px; background:#eff6ff; border-bottom:1px solid #dbeafe; color:#1e3a8a; font-size:12px; line-height:1.9; }
.lp-preview-empty { padding:30px 16px; text-align:center; color:#bbb; font-size:13px; display:flex; flex-direction:column; align-items:center; gap:8px; }

.lp-advanced-section { background:#fafbfc; border:1px solid #eef0f3; border-radius:8px; margin-bottom:18px; overflow:hidden; }
.lp-advanced-toggle { padding:10px 16px; cursor:pointer; display:flex; align-items:center; gap:6px; font-size:13px; color:#666; user-select:none; transition:background .15s; }
.lp-advanced-toggle:hover { background:#f0f2f5; }
.lp-adv-arrow { transition:transform .2s; font-size:16px; }
.lp-adv-arrow.open { transform:rotate(90deg); }
.lp-adv-desc { color:#999; font-size:12px; }
.lp-advanced-body { padding:0 16px 16px; }
.lp-mode-picker { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:12px; margin:-2px 0 16px; }
.lp-mode-option { background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:14px; cursor:pointer; transition:border-color .15s, box-shadow .15s, transform .15s, background .15s; }
.lp-mode-option:hover { border-color:#93c5fd; box-shadow:0 8px 20px rgba(37, 99, 235, 0.08); transform:translateY(-1px); }
.lp-mode-option.is-active { border-color:#2563eb; background:#eff6ff; box-shadow:0 0 0 1px rgba(37, 99, 235, 0.08); }
.lp-mode-option-badge { display:inline-block; padding:2px 8px; border-radius:999px; background:#e5e7eb; color:#4b5563; font-size:12px; margin-bottom:8px; }
.lp-mode-option.is-active .lp-mode-option-badge { background:#dbeafe; color:#1d4ed8; }
.lp-mode-option-title { font-size:14px; font-weight:600; color:#111827; margin-bottom:6px; }
.lp-mode-option-desc { font-size:12px; color:#6b7280; line-height:1.8; }
.lp-mode-panel { display:none; background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:14px; margin-bottom:14px; }
.lp-mode-panel.is-active { display:block; }
.lp-mode-panel-tip { margin-bottom:12px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:6px; padding:10px 12px; font-size:12px; color:#334155; line-height:1.8; display:flex; align-items:flex-start; gap:6px; }

.lp-manual-table-wrap { border:1px solid #eee; border-radius:6px; overflow:hidden; margin-top:10px; }
.lp-manual-table-wrap .layui-table { margin:0; }
.lp-manual-table-wrap .layui-table thead th { background:#f9fafb; font-size:12px; }
.lp-manual-tip { margin-top:8px; font-size:12px; color:#666; display:flex; align-items:center; gap:4px; }

@media screen and (max-width: 768px) {
    .lp-decision-grid,
    .lp-mode-picker { grid-template-columns:1fr; }
}
</style>

<script>
// 高级选项折叠
(function(){
    function renderModeFieldState(mode) {
        var selectorMap = {
            manual: '#lp-manual-prices input[type="number"]',
            single_rule: 'select[name="single_rule_id"]',
            profit_ratio: 'input[name="profit_ratio"]',
            profit_rule: 'select[name="profit_rule_id"]'
        };
        $.each(selectorMap, function(key, selector){
            $(selector).prop('disabled', key !== mode);
        });
        if (window.layui && layui.form) {
            layui.form.render('select');
        }
    }

    window.getPricingOverrideMode = function(){
        return $('#pricing_override_mode').val() || 'inherit';
    };

    window.syncPricingOverrideMode = function(mode, options){
        options = options || {};
        if (!mode) mode = 'inherit';
        $('#pricing_override_mode').val(mode);
        $('.lp-mode-option').removeClass('is-active');
        $('.lp-mode-option[data-mode="' + mode + '"]').addClass('is-active');
        $('.lp-mode-panel').removeClass('is-active').hide();
        $('.lp-mode-panel[data-mode-panel="' + mode + '"]').addClass('is-active').show();
        renderModeFieldState(mode);
        if (!options.silent && window.refreshPricingPreview) {
            window.refreshPricingPreview();
        }
    };

    $('#lp-adv-toggle').on('click', function(){
        var $body = $('#lp-adv-body'), $arrow = $('#lp-adv-arrow');
        $body.slideToggle(200);
        $arrow.toggleClass('open');
    });

    $(document).on('click', '.lp-mode-option', function(){
        window.syncPricingOverrideMode($(this).data('mode'));
    });

    window.syncPricingOverrideMode(window.getPricingOverrideMode(), { silent: true });
})();
// 等级价预览交互
(function(){
    function collectManualPriceMap() {
        var map = {};
        $('#lp-manual-prices input[type="number"]').each(function(){
            var name = $(this).attr('name') || '';
            var match = name.match(/skus\[member\]\[(\d+)\]/);
            var val = $.trim($(this).val());
            if (!match || val === '') return;
            var price = parseFloat(val);
            if (!isNaN(price)) {
                map[match[1]] = price;
            }
        });
        return map;
    }

    function getPreviewCostInfo() {
        var raw = $.trim($('#lv-preview-cost').val());
        if (raw === '') {
            return { raw: raw, value: null, valid: false, reason: 'empty' };
        }
        var value = parseFloat(raw);
        if (isNaN(value) || value <= 0) {
            return { raw: raw, value: value, valid: false, reason: 'invalid' };
        }
        return { raw: raw, value: value, valid: true, reason: '' };
    }

    function getActivePricingState() {
        var mode = window.getPricingOverrideMode ? window.getPricingOverrideMode() : 'inherit';
        var state = {
            mode: mode,
            singleRuleId: 0,
            profitRuleId: 0,
            profitRatio: 100,
            manualPrices: {},
            manualCount: 0
        };

        if (mode === 'manual') {
            state.manualPrices = collectManualPriceMap();
            state.manualCount = Object.keys(state.manualPrices).length;
        } else if (mode === 'single_rule') {
            state.singleRuleId = parseInt($('select[name="single_rule_id"]').val(), 10) || 0;
        } else if (mode === 'profit_ratio') {
            var ratio = parseFloat($('input[name="profit_ratio"]').val());
            state.profitRatio = isNaN(ratio) ? 100 : ratio;
        } else if (mode === 'profit_rule') {
            state.profitRuleId = parseInt($('select[name="profit_rule_id"]').val(), 10) || 0;
        }

        return state;
    }

    function renderPricingSummary() {
        var state = getActivePricingState();
        var previewCost = getPreviewCostInfo();
        var text = '当前商品正在继承会员等级默认规则。系统会读取各等级里的默认加价比例和自动调节规则来算价。';

        if (state.mode === 'manual') {
            text = state.manualCount > 0
                ? '当前商品已切到“部分等级单独定价”模式，并为 ' + state.manualCount + ' 个等级填写了单独售价；这些等级保存后优先使用手动价格，其余等级继续继承会员等级默认规则。'
                : '当前商品已切到“部分等级单独定价”模式，但你还没填写任何等级；如果保持留空，保存后等同继续继承会员等级默认规则。';
        } else if (state.mode === 'single_rule') {
            text = state.singleRuleId > 0
                ? '当前商品已启用“商品专属加价规则”，会覆盖会员等级默认规则。'
                : '当前商品已切到“商品专属加价规则”模式，但你还没选择具体规则；此时预览会先按继承会员等级默认规则显示。';
        } else if (state.mode === 'profit_ratio') {
            text = Math.abs(state.profitRatio - 100) > 0.0001
                ? '当前商品已启用“商品加价力度折扣 ' + state.profitRatio + '%”，会直接按默认加价比例 × 这个折扣计算，不再继续套自动调节规则。'
                : '当前商品已切到“商品加价力度折扣”模式，但折扣仍是 100%；此时效果等同继续继承会员等级默认规则。';
        } else if (state.mode === 'profit_rule') {
            text = state.profitRuleId > 0
                ? '当前商品已启用“商品成本自动调节规则”，会覆盖会员等级里绑定的自动调节规则。'
                : '当前商品已切到“商品成本自动调节规则”模式，但你还没选择具体规则；此时预览会先按继承会员等级默认规则显示。';
        }

        if (!previewCost.valid) {
            text += previewCost.reason === 'empty'
                ? ' 先输入一个真实成本价，再看预览结果会更准确。'
                : ' 请填写大于 0 的成本价后再预览。';
        }

        $('#lv-preview-status').text(text);
    }

    function doLevelPreview(options){
        options = options || {};
        var previewCost = getPreviewCostInfo();
        if (!previewCost.valid) {
            $('#lv-preview-result').hide();
            $('#lv-preview-empty').show().find('span').text(previewCost.reason === 'empty' ? '先输入一个真实成本价，再查看当前继承/覆盖规则算出来的各等级售价' : '请填写大于 0 的成本价，再查看各等级售价预览');
            renderPricingSummary();
            if (!options.silent) {
                layer.msg(previewCost.reason === 'empty' ? '请先输入成本价再预览' : '成本价必须大于 0');
            }
            return;
        }
        var cost = previewCost.value;
        var state = getActivePricingState();
        var pr = state.profitRuleId;
        var sr = state.singleRuleId;
        var pf = state.profitRatio;
        var ac = parseInt($('input[name="accuracy"]').val(), 10);
        var manualPrices = state.manualPrices;
        if (isNaN(ac)) ac = 2;
        var loadIdx = layer.load(1, { shade: [0.1, '#000'] });
        $.ajax({
            type: 'POST', url: '<?= DC_URL ?>admin/goods.php?action=level_preview', dataType: 'json',
            data: { cost: cost, profit_rule_id: pr, single_rule_id: sr, profit_ratio: pf, accuracy: ac },
            success: function(res){
                layer.close(loadIdx);
                if (res.code != 0) { layer.msg(res.msg || '预览失败', { icon: 2 }); return; }
                var rows = res.data || [];
                var html = '';
                rows.forEach(function(r){
                    if (r.level_id < 0) return; // 跳过游客行
                    if (state.mode === 'manual' && manualPrices[r.level_id] !== undefined) {
                        r.price = '¥' + manualPrices[r.level_id].toFixed(ac);
                        r.source = '商品已单独定价：保存后优先使用该等级独立售价';
                    }
                    html += '<tr><td><b>' + r.level_name + '</b></td><td><b style="color:#eb2525;font-size:15px;">' + r.price + '</b></td><td style="color:#888;font-size:12px;">' + r.source + '</td></tr>';
                });
                if (!html) html = '<tr><td colspan="3" style="text-align:center;color:#bbb;">暂无会员等级，请先<a href="member.php" target="_blank">创建等级</a></td></tr>';
                $('#lv-preview-tbody').html(html);
                $('#lv-preview-result').show();
                $('#lv-preview-empty').hide();
                renderPricingSummary();
            },
            error: function(){ layer.close(loadIdx); layer.msg('网络错误', { icon: 2 }); }
        });
    }
    window.refreshPricingPreview = function(){
        renderPricingSummary();
        if ($('#lv-preview-result').is(':visible')) doLevelPreview();
    };
    $(document).on('click', '#lv-preview-btn', doLevelPreview);
    $(document).on('change input', 'select[name="profit_rule_id"], select[name="single_rule_id"], input[name="profit_ratio"], input[name="accuracy"], #lp-manual-prices input[type="number"], #lv-preview-cost', function(){
        renderPricingSummary();
        if ($('#lv-preview-result').is(':visible')) doLevelPreview();
    });
    renderPricingSummary();
    // 首次自动预览
    if (getPreviewCostInfo().valid) {
        setTimeout(function(){ doLevelPreview({ silent: true }); }, 500);
    }
})();
</script>
                </div>
                <div class="layui-tabs-item">
                    <div class="ge-section-title" style="border-top:none;margin-top:0;padding-top:0;"><i class="ri-file-text-line"></i> 商品描述</div>
                    <div class="sku-guide-tip">
                        <i class="ri-information-line" style="color:#1e9fff;"></i>
                        简介内容为纯文本，会显示在商品列表的摘要区域。商品详情支持富文本编辑，在商品页面展开显示。
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">简介内容</label>
                        <div class="layui-input-block">
                            <textarea class="layui-textarea" name="des" placeholder="输入商品简短描述，用于列表页摘要展示"><?= $goods['des'] ?></textarea>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">商品详情</label>
                        <div class="layui-input-block">
                            <textarea class="basic-example" name="content"><?= $goods['content'] ?></textarea>
                        </div>
                    </div>
                    
                    <!-- 多规格独立详情 -->
                    <div id="skuContentSection" style="<?= $goods['is_sku'] == 'y' ? '' : 'display:none;' ?>">
                        <div class="ge-section">
                            <div class="ge-section-title"><i class="ri-layout-column-line"></i> 规格独立详情</div>
                            <div class="sku-guide-tip" style="margin-bottom:12px;">
                                <i class="ri-information-line" style="color:#1e9fff;"></i>
                                可为每个规格设置独立的商品详情，留空则使用上方的通用商品详情。<br>
                                <b style="color:#ff9800;">选择多规格后需要先保存商品，才能在此处设置各规格的独立详情。</b>
                            </div>
                        </div>
                        <?php if($goods['is_sku'] == 'y' && !empty($goods['skus'])): ?>
                            <?php foreach($goods['skus'] as $sku): ?>
                                <?php 
                                $sku_key = $sku['sku'];
                                $sku_display_name = '规格 ' . $sku_key;
                                if(!empty($goods['sku_names'][$sku_key])) {
                                    $sku_display_name = $goods['sku_names'][$sku_key];
                                }
                                ?>
                                <div class="layui-form-item sku-content-group" data-sku="<?= $sku_key ?>">
                                    <label class="layui-form-label" style="width:auto;min-width:120px;"><?= $sku_display_name ?></label>
                                    <div class="layui-input-block">
                                        <textarea class="sku-content-editor" name="sku_content[<?= $sku_key ?>]" placeholder="输入该规格的独立详情，留空使用通用详情"><?= htmlspecialchars($sku['content'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="layui-form-item" id="noSkuContentTip">
                                <div class="layui-input-block" style="margin-left:0;">
                                    <div class="detail-empty-tip">
                                        <i class="ri-save-line" style="font-size:20px;color:#ccc;"></i>
                                        <span>请先保存商品并设置好多规格信息，保存后刷新页面即可在此处编辑各规格独立详情</span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="ge-section">
                        <div class="ge-section-title"><i class="ri-file-list-3-line"></i> 订单详情页</div>
                        <div class="sku-guide-tip" style="margin-bottom:12px;">
                            <i class="ri-information-line" style="color:#1e9fff;"></i>
                            此内容在买家支付成功后的订单详情页额外显示，可用于放置使用说明、注意事项等信息。留空则不显示。
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">订单额外内容</label>
                        <div class="layui-input-block">
                            <textarea class="basic-example" name="pay_content"><?= $goods['pay_content'] ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="layui-tabs-item">
                    <div class="ge-section-title" style="border-top:none;margin-top:0;padding-top:0;"><i class="ri-list-check-2"></i> 下单输入框（商品级）</div>

                    <?php if (!empty($goods['is_docking'])): ?>
                    <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:6px;padding:14px 18px;margin-bottom:16px;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                            <i class="ri-lock-line" style="color:#ea580c;font-size:18px;"></i>
                            <b style="color:#9a3412;font-size:14px;">对接商品 — 输入框由主站同步</b>
                        </div>
                        <div style="color:#92400e;font-size:12px;line-height:1.8;">
                            此商品为对接商品，下单输入框从主站自动同步，不支持手动修改。
                        </div>
                    </div>
                    <?php
                    $dockAttachData = json_decode($goods['attach_user'] ?? '[]', true) ?: [];
                    if (!empty($dockAttachData)):
                    ?>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;overflow:hidden;margin-bottom:16px;">
                        <table class="layui-table" style="margin:0;">
                            <thead><tr>
                                <th>字段名称</th><th>输入提示</th><th>类型</th><th>是否必填</th><th>说明</th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($dockAttachData as $ai): ?>
                                <tr>
                                    <td><b><?= htmlspecialchars($ai['name'] ?? '') ?></b></td>
                                    <td><?= htmlspecialchars($ai['placeholder'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($ai['type'] ?? 'string') ?></td>
                                    <td><?= (!isset($ai['required']) || $ai['required']) ? '<span style="color:#16a34a;">必填</span>' : '选填' ?></td>
                                    <td><?= htmlspecialchars($ai['tip'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div style="color:#999;font-size:13px;padding:10px 0;">主站未配置下单输入框，如需添加请前往主站操作后重新导入。</div>
                    <?php endif; ?>

                    <textarea name="attach_user" id="au-json" style="display:none;"><?= $goods['attach_user'] ?></textarea>

                    <?php else: ?>
                    <div class="sku-guide-tip">
                        <i class="ri-information-line" style="color:#1e9fff;"></i>
                        此处设置的输入框<b style="color:#333;">仅对当前商品生效</b>，买家下单时除显示这里配置的输入框外，还会显示
                        <a href="./shop.php?action=btx" target="_blank" style="color:#1e9fff;font-weight:600;">全局下单输入框</a>
                        中的字段。<br>
                        <b>区别：</b>全局输入框（商城配置 → 下单输入框）对所有商品统一生效；商品级输入框仅对当前商品生效，适合需要额外收集信息的商品。
                    </div>

                    <div class="layui-form-item">
                        <div class="layui-input-inline" style="width:160px;">
                            <input type="text" class="layui-input" id="au-name" placeholder="字段名称（如：联系QQ）">
                        </div>
                        <div class="layui-input-inline" style="width:180px;">
                            <input type="text" class="layui-input" id="au-placeholder" placeholder="输入提示（如：请输入QQ号）">
                        </div>
                        <div class="layui-input-inline" style="width:100px;">
                            <select id="au-type" lay-ignore style="width:100%;height:38px;padding:0 10px;border:1px solid #e6e6e6;border-radius:2px;font-size:14px;background:#fff;outline:none;appearance:auto;">
                                <option value="string" selected>文本</option>
                                <option value="tel">手机号</option>
                                <option value="email">邮箱</option>
                                <option value="num">数字</option>
                            </select>
                        </div>
                        <div class="layui-input-inline" style="width:80px;">
                            <select id="au-required" lay-ignore style="width:100%;height:38px;padding:0 10px;border:1px solid #e6e6e6;border-radius:2px;font-size:14px;background:#fff;outline:none;appearance:auto;">
                                <option value="1" selected>必填</option>
                                <option value="0">选填</option>
                            </select>
                        </div>
                        <div class="layui-input-inline" style="width:180px;">
                            <input type="text" class="layui-input" id="au-tip" placeholder="说明（如：用于查询订单）">
                        </div>
                        <div class="layui-input-inline" style="width:auto;">
                            <span id="au-add-btn" class="layui-btn layui-bg-blue"><i class="ri-add-line"></i> 添加</span>
                        </div>
                    </div>

                    <div id="au-email-tip" style="background:#e8f4ff;border-left:3px solid #1e9fff;padding:10px 15px;border-radius:4px;margin-bottom:16px;font-size:12px;color:#999;display:none;">
                        <i class="ri-information-line" style="color:#1e9fff;"></i>
                        设置邮箱类型后，如需订单支付成功后自动推送卡密到买家邮箱，请前往<a href="./store.php?action=plu&keyword=订单卡密邮箱推送" style="color:#1e9fff;font-weight:600;">应用商店</a>安装【订单卡密邮箱推送】插件。
                    </div>

                    <div id="au-list"></div>

                    <textarea name="attach_user" id="au-json" style="display:none;"></textarea>
                    <?php endif; ?>
                    
                </div>
                <div class="layui-tabs-item">
                    <div class="ge-section-title" style="border-top:none;margin-top:0;padding-top:0;"><i class="ri-bar-chart-box-line"></i> 销量设置</div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">销量</label>
                        <div class="layui-input-block">
                            <input type="number" name="sales" class="layui-input" value="<?= $goods['sales'] ?>">
                            <div class="layui-form-mid layui-text-em">设置前台显示的销量数字</div>
                        </div>
                    </div>

                    <div class="ge-section">
                        <div class="ge-section-title"><i class="ri-price-tag-3-line"></i> 批量购买优惠</div>
                    <div class="sku-guide-tip">
                        <i class="ri-information-line" style="color:#1e9fff;"></i>
                        三种优惠<b>可同时设置、各自独立生效</b>。结算顺序：<b>每件优惠</b>先按件减价，得到小计后再扣<b>订单优惠</b>固定金额，最后按<b>订单折扣</b>打折。多规格商品可针对每个规格单独设置；单规格商品使用"通用优惠"即可。
                    </div>

                    <div class="layui-form-item" style="margin-bottom:10px;">
                        <label class="layui-form-label">优惠标题</label>
                        <div class="layui-input-inline" style="width:240px;">
                            <input type="text" name="discount_title" maxlength="32" class="layui-input" placeholder="批发优惠" value="<?= htmlspecialchars($goods['discount_title'] ?? '', ENT_QUOTES) ?>" />
                        </div>
                        <div class="layui-form-mid layui-text-em">前端商品页展示的入口文案与弹窗标题，留空则使用"批发优惠"（最长 32 字）</div>
                    </div>

                    <?php
                    // 三类优惠统一配置：name 前缀、源数据、标签、placeholder、step、amount 字段转换回显
                    $_discount_sections = [
                        [
                            'key'   => 'item',
                            'name'  => 'discount',            // type=1 每件优惠（元）
                            'data'  => $discount ?? [],
                            'title' => '每件优惠（元）',
                            'desc'  => '购买达到指定数量后，每件商品减免金额（元）',
                            'ph'    => '每件优惠金额(元)',
                            'step'  => '0.01',
                            'conv'  => function($v){ return $v / 100; },
                        ],
                        [
                            'key'   => 'order',
                            'name'  => 'discount_order',      // type=2 订单优惠（元）
                            'data'  => $discount_order ?? [],
                            'title' => '订单优惠（元）',
                            'desc'  => '购买达到指定数量后，整单减免固定金额（元）',
                            'ph'    => '订单减免金额(元)',
                            'step'  => '0.01',
                            'conv'  => function($v){ return $v / 100; },
                        ],
                        [
                            'key'   => 'percent',
                            'name'  => 'discount_percent',    // type=3 订单折扣（折，0-10）
                            'data'  => $discount_percent ?? [],
                            'title' => '订单折扣（折）',
                            'desc'  => '购买达到指定数量后，整单按折数打折（如 9.5 = 9.5折；小于 10）',
                            'ph'    => '折数 (如 9.5)',
                            'step'  => '0.1',
                            'conv'  => function($v){ return $v / 10; },
                        ],
                    ];

                    // 渲染一条 kv-item
                    $renderKv = function($name, $sku_key, $quantity, $amount, $step, $ph){
                        $q = $quantity === '' ? '' : htmlspecialchars((string)$quantity, ENT_QUOTES);
                        $a = $amount === '' ? '' : htmlspecialchars((string)$amount, ENT_QUOTES);
                        ?>
                        <div class="kv-item">
                            <div class="layui-input-inline">
                                <input value="<?= $q ?>" step="1" name="<?= $name ?>[<?= $sku_key ?>][number][]" type="number" class="layui-input" placeholder="购买数量" />
                            </div>
                            <div class="layui-input-inline">
                                <input value="<?= $a ?>" step="<?= $step ?>" name="<?= $name ?>[<?= $sku_key ?>][amount][]" type="number" class="layui-input" placeholder="<?= $ph ?>" />
                            </div>
                            <button type="button" class="kv-btn delete-btn layui-btn layui-bg-red"><i class="ri-delete-bin-line"></i></button>
                            <button type="button" class="kv-btn add-btn layui-btn layui-bg-blue"><i class="ri-add-line"></i></button>
                        </div>
                        <?php
                    };
                    ?>

                    <?php foreach($_discount_sections as $sec): ?>
                        <div class="discount-section" style="margin-top:18px;padding:12px 14px;background:#fafbfc;border:1px solid #eef0f3;border-radius:6px;">
                            <div style="font-weight:600;color:#333;margin-bottom:4px;"><i class="ri-price-tag-3-line" style="color:#1e9fff;"></i> <?= $sec['title'] ?></div>
                            <div style="color:#999;font-size:12px;margin-bottom:10px;"><?= $sec['desc'] ?></div>

                            <!-- 单规格：通用优惠 -->
                            <div class="discount-single-wrap" style="<?= $goods['is_sku'] == 'y' ? 'display:none;' : '' ?>">
                                <div class="layui-form-item">
                                    <label class="layui-form-label">通用优惠设置</label>
                                    <div class="discount-items" data-sku="0" data-name="<?= $sec['name'] ?>" data-step="<?= $sec['step'] ?>" data-ph="<?= $sec['ph'] ?>">
                                        <?php
                                        $rows = array_filter($sec['data'], function($d){ return empty($d['sku']) || $d['sku'] == '0'; });
                                        if(empty($rows)){
                                            $renderKv($sec['name'], '0', '', '', $sec['step'], $sec['ph']);
                                        }else{
                                            foreach($rows as $val){
                                                $amt = $sec['conv']((float)$val['amount']);
                                                $renderKv($sec['name'], '0', (int)$val['quantity'], $amt, $sec['step'], $sec['ph']);
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <!-- 多规格：按 SKU 分组 -->
                            <div class="discount-multi-wrap" style="<?= $goods['is_sku'] == 'y' ? '' : 'display:none;' ?>">
                                <?php if($goods['is_sku'] == 'y' && !empty($goods['skus'])): ?>
                                    <?php
                                    // 按 SKU 分组
                                    $sku_rows = [];
                                    foreach($sec['data'] as $d){
                                        if(!empty($d['sku']) && $d['sku'] != '0'){
                                            $sku_rows[$d['sku']][] = $d;
                                        }
                                    }
                                    ?>
                                    <?php foreach($goods['skus'] as $sku): ?>
                                        <?php
                                        $sku_key = $sku['sku'];
                                        $sku_display_name = !empty($goods['sku_names'][$sku_key]) ? $goods['sku_names'][$sku_key] : '规格 ' . $sku_key;
                                        $this_rows = $sku_rows[$sku_key] ?? [];
                                        ?>
                                        <div class="layui-form-item sku-discount-group">
                                            <label class="layui-form-label" style="width:auto;min-width:120px;"><?= htmlspecialchars($sku_display_name) ?></label>
                                            <div class="discount-items" data-sku="<?= htmlspecialchars($sku_key, ENT_QUOTES) ?>" data-name="<?= $sec['name'] ?>" data-step="<?= $sec['step'] ?>" data-ph="<?= $sec['ph'] ?>">
                                                <?php
                                                if(empty($this_rows)){
                                                    $renderKv($sec['name'], $sku_key, '', '', $sec['step'], $sec['ph']);
                                                }else{
                                                    foreach($this_rows as $d){
                                                        $amt = $sec['conv']((float)$d['amount']);
                                                        $renderKv($sec['name'], $sku_key, (int)$d['quantity'], $amt, $sec['step'], $sec['ph']);
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="layui-form-item">
                                        <div class="layui-input-block" style="margin-left:0;">
                                            <div class="detail-empty-tip">
                                                <i class="ri-save-line" style="font-size:20px;color:#ccc;"></i>
                                                <span>请先保存商品并设置好多规格信息，保存后刷新页面即可设置各规格独立优惠</span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    </div><!-- /.ge-section 批量购买优惠 -->
                </div><!-- /.layui-tabs-item 营销设置 -->
            </div>

            <div class="ge-action-bar">
                <button type="submit" class="layui-btn" lay-submit lay-filter="submit"><i class="ri-save-line"></i> 保存</button>
                <?php if (!empty($isPopup)): ?>
                <button type="button" class="layui-btn layui-btn-primary" onclick="try{parent.layui.layer.closeAll();}catch(e){window.close();}">取消</button>
                <?php else: ?>
                <button type="button" class="layui-btn layui-btn-primary" onclick="history.back()">取消</button>
                <?php endif; ?>
            </div>
            <?= doAction('goods_eidt_form_foot') ?>
        </form>
    </div>
</div><!-- /.goods-card-body -->
</div><!-- /.goods-card-wrap -->


<script src="./views/components/lay-module/sortable.js?t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>
<script src="./views/components/lay-module/skuTable.js?t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>

<script src="./tinymce/tinymce.min.js?t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>

<script src="./views/js/views/goods.js?t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>


<script>
    layui.use(['form', 'laydate', 'util'], function(){
        var $ = layui.$;
        var form = layui.form;
        var upload = layui.upload;
        var element = layui.element;
        form.on('submit(submit)', function(data){
            var field = data.field; // 获取表单全部字段值

            var baseType = $('#goods-base-type').val();
            if (baseType) {
                field.type = baseType;
            }

            // 商品图集：layui field 同名键只保留最后一个，需手动从 DOM 顺序重建数组
            var __gallery = [];
            $('#gallery-grid .gallery-cell').each(function(){
                var u = $(this).data('url');
                if(u && __gallery.indexOf(u) < 0) __gallery.push(u);
            });
            // 移除 layui 收集的同名残留 key
            Object.keys(field).forEach(function(k){
                if(k === 'gallery' || k === 'gallery[]' || k.indexOf('gallery[') === 0){
                    delete field[k];
                }
            });
            field.gallery = __gallery;
            // cover 同步为第一张（与 rebuildGallery 行为一致）
            field.cover = __gallery.length ? __gallery[0] : '';

            var pricingMode = $('#pricing_override_mode').val() || 'inherit';
            if (pricingMode !== 'manual') {
                Object.keys(field).forEach(function(key){
                    if (key.indexOf('skus[member][') === 0) {
                        delete field[key];
                    }
                });
            }
            if (pricingMode !== 'single_rule') {
                field.single_rule_id = 0;
            }
            if (pricingMode !== 'profit_ratio') {
                field.profit_ratio = 100;
            }
            if (pricingMode !== 'profit_rule') {
                field.profit_rule_id = 0;
            }
            var url = $('#form').attr('action');
            $.ajax({
                type: "POST",
                url: url,
                data: field,
                dataType: "json",
                success: function (e) {
                    if(e.code == 400){
                        layer.msg(e.msg || '保存失败', {icon: 2});
                    }else{
                        var _saveTip = '<?= $action == "edit" ? "修改成功" : "添加成功" ?>';
                        layer.msg(_saveTip, {icon: 1, time: 1200}, function(){
                            <?php if (!empty($isPopup)): ?>
                            try { parent.layui.layer.closeAll(); parent.ws_table && parent.ws_table.reload(); } catch(ex) {}
                            <?php else: ?>
                            location.href = "goods.php";
                            <?php endif; ?>
                        });
                    }

                },
                error: function (xhr) {
                    try {
                        var res = JSON.parse(xhr.responseText);
                        layer.msg(res.msg || '保存失败');
                    } catch(e) {
                        layer.msg('保存失败，请刷新页面重试');
                    }
                }
            });
            return false; // 阻止默认 form 跳转
        });


        /* ========== 商品图（多图）：核心管理 ========== */
        var GALLERY_MAX = 10;
        var $galleryGrid = $('#gallery-grid');

        // 重建图集 UI：根据当前 cell 顺序刷新主图标记 + 同步 cover
        function rebuildGallery(){
            var $cells = $galleryGrid.find('.gallery-cell');
            $cells.each(function(idx){
                var $c = $(this);
                $c.find('.gc-main-badge').remove();
                if(idx === 0){
                    $c.addClass('is-main');
                    $c.prepend('<span class="gc-main-badge">主图</span>');
                } else {
                    $c.removeClass('is-main');
                }
            });
            // cover = 第一张
            $('#sortimg').val($cells.length ? $cells.first().data('url') : '');
            // 添加按钮显隐
            $('#gallery-add-box').toggle($cells.length < GALLERY_MAX);
        }

        // 注：gallery 提交字段由 layui form.on('submit(submit)') 钩子在提交时
        // 直接从 DOM 顺序构建并写入 field.gallery（layui field 不支持同名多键，
        // 所以无法用 <input name="gallery[]"> 让 layui 自动收集）。

        // 添加图片到末尾（避免重复）
        function appendGalleryImage(url){
            url = String(url||'').trim();
            if(!url) return false;
            // 重复跳过
            var dup = false;
            $galleryGrid.find('.gallery-cell').each(function(){ if($(this).data('url') === url){ dup = true; return false; } });
            if(dup) return false;
            var $cells = $galleryGrid.find('.gallery-cell');
            if($cells.length >= GALLERY_MAX){
                layer.msg('最多上传 ' + GALLERY_MAX + ' 张');
                return false;
            }
            var html = '<div class="gallery-cell" data-url="' + url.replace(/"/g,'&quot;') + '" draggable="true">'
                     + '<img src="' + url + '" alt="" onerror="this.onerror=null;this.parentElement.classList.add(\'is-load-error\');">'
                     + '<span class="gc-del" title="移除">×</span>'
                     + '<div class="gc-set-main" title="设为主图">设为主图</div>'
                     + '</div>';
            $('#gallery-add-box').before(html);
            rebuildGallery();
            return true;
        }

        // 移除单张
        $galleryGrid.on('click', '.gc-del', function(e){
            e.stopPropagation();
            var $cell = $(this).closest('.gallery-cell');
            var isMain = $cell.hasClass('is-main');
            var hasOthers = $galleryGrid.find('.gallery-cell').length > 1;
            if(isMain && hasOthers){
                layer.confirm('这是当前主图，移除后将自动以下一张为主图。确认移除？', {icon:3, title:'提示'}, function(idx){
                    layer.close(idx);
                    $cell.remove(); rebuildGallery();
                });
            } else {
                $cell.remove(); rebuildGallery();
            }
        });

        // 设为主图
        $galleryGrid.on('click', '.gc-set-main', function(e){
            e.stopPropagation();
            var $cell = $(this).closest('.gallery-cell');
            $cell.prependTo($galleryGrid);
            rebuildGallery();
        });

        // 拖拽排序（HTML5 Drag API，原生 jQuery 已加载）
        var dragCell = null;
        $galleryGrid.on('dragstart', '.gallery-cell', function(e){
            dragCell = this;
            $(this).addClass('dragging');
            e.originalEvent.dataTransfer.effectAllowed = 'move';
            // Firefox 需要 setData
            try { e.originalEvent.dataTransfer.setData('text/plain', ''); } catch(_){}
        });
        $galleryGrid.on('dragend', '.gallery-cell', function(){
            $(this).removeClass('dragging');
            $galleryGrid.find('.gallery-cell').removeClass('drag-over');
            dragCell = null;
        });
        $galleryGrid.on('dragover', '.gallery-cell', function(e){
            e.preventDefault();
            if(this === dragCell) return;
            $galleryGrid.find('.gallery-cell').removeClass('drag-over');
            $(this).addClass('drag-over');
        });
        $galleryGrid.on('dragleave', '.gallery-cell', function(){
            $(this).removeClass('drag-over');
        });
        $galleryGrid.on('drop', '.gallery-cell', function(e){
            e.preventDefault();
            $(this).removeClass('drag-over');
            if(!dragCell || dragCell === this) return;
            var dragIdx = $(dragCell).index();
            var dropIdx = $(this).index();
            if(dragIdx < dropIdx){
                $(this).after(dragCell);
            } else {
                $(this).before(dragCell);
            }
            rebuildGallery();
        });

        // 点击添加按钮 → 打开文件库（多选模式）
        $('#gallery-add-box').on('click', function(){ openMediaLib(true); });

        // 页面初始化：刷新主图标记 + 添加按钮显隐
        rebuildGallery();

        /* ========== 文件库弹窗（支持单选/多选） ========== */
        var mlPage = 1, mlPages = 1, mlSelected = [], mlLayerIdx = 0, mlMulti = false;

        function mlBuildHtml(){
            return '<div class="media-lib">'
                + '<div class="media-lib-header">'
                +   '<div class="ml-search"><input class="layui-input" id="ml-keyword" placeholder="搜索图片..."></div>'
                +   '<button class="layui-btn layui-btn-sm layui-btn-normal" id="ml-upload-btn"><i class="layui-icon layui-icon-upload"></i> 上传图片</button>'
                + '</div>'
                + '<div class="media-lib-drop" id="ml-drop"><i class="layui-icon layui-icon-upload-drag"></i>拖拽图片到此处上传，或点击此处选择文件</div>'
                + '<div class="media-lib-grid" id="ml-grid"><div class="ml-loading">加载中...</div></div>'
                + '<div class="media-lib-footer">'
                +   '<div class="media-lib-pager"><button class="pg-btn" id="ml-prev" disabled>&laquo; 上一页</button><span class="pg-info" id="ml-page-info">1/1</span><button class="pg-btn" id="ml-next">&raquo; 下一页</button></div>'
                +   '<div class="ml-right-btns"><button class="ml-batch-del" id="ml-batch-del"><i class="layui-icon layui-icon-delete"></i>删除</button><button class="layui-btn layui-btn-sm" id="ml-confirm">确定选择</button></div>'
                + '</div>'
                + '</div>';
        }

        function mlLoad(page, keyword){
            mlPage = page || 1;
            var $g = $('#ml-grid');
            $g.html('<div class="ml-loading">加载中...</div>');
            $.getJSON('goods.php?action=media_images&page=' + mlPage + '&keyword=' + encodeURIComponent(keyword||''), function(res){
                if(res.code !== 0){ $g.html('<div class="ml-empty">加载失败</div>'); return; }
                mlPages = res.pages || 1;
                $('#ml-page-info').text(mlPage + '/' + mlPages);
                $('#ml-prev').prop('disabled', mlPage <= 1);
                $('#ml-next').prop('disabled', mlPage >= mlPages);
                if(!res.data.length){ $g.html('<div class="ml-empty">暂无图片</div>'); return; }
                var html = '';
                res.data.forEach(function(img){
                    var sel = (mlSelected.indexOf(img.url) > -1) ? ' selected' : '';
                    html += '<div class="ml-item' + sel + '" data-url="' + img.url + '" data-aid="' + img.aid + '">'
                          + '<div class="ml-img-wrap"><img src="' + img.thumb + '" loading="lazy" onerror="this.closest(\'.ml-item\').style.display=\'none\'"></div>'
                          + '<div class="ml-bar"><span class="ml-name">' + (img.filename||'') + '</span><i class="layui-icon layui-icon-delete ml-del" title="删除"></i></div></div>';
                });
                $g.html(html);
            });
        }

        function mlUploadFile(file){
            var fd = new FormData();
            fd.append('image', file);
            var li = layer.load(2);
            return $.ajax({
                url: './goods.php?action=upload_cover',
                type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json'
            }).always(function(){ layer.close(li); }).then(function(res){
                if(res.code == 0 && res.data){
                    if(mlMulti){
                        // 多选模式：上传完直接加入选中集
                        if(mlSelected.indexOf(res.data) < 0) mlSelected.push(res.data);
                    } else {
                        mlSelected = [res.data];
                    }
                    return res.data;
                }
                layer.msg(res.msg || '上传失败', {icon:2});
                return $.Deferred().reject().promise();
            }, function(){ layer.msg('上传失败'); });
        }

        // 批量上传
        function mlUploadFiles(files){
            var arr = [].slice.call(files).filter(function(f){ return /^image\//i.test(f.type); });
            if(!arr.length) return;
            var chain = $.Deferred().resolve().promise();
            arr.forEach(function(f){ chain = chain.then(function(){ return mlUploadFile(f); }); });
            chain.always(function(){
                layer.msg('上传完成', {icon:1, time:1000});
                mlLoad(1, $('#ml-keyword').val());
            });
        }

        // 统一打开入口（multi: true=多选追加到 gallery, false=单选给 #sortimg）
        function openMediaLib(multi){
            mlMulti = !!multi;
            // 多选默认空集（避免与已加入图集的重复）
            mlSelected = mlMulti ? [] : ($('#sortimg').val().trim() ? [$('#sortimg').val().trim()] : []);
            mlLayerIdx = layer.open({
                type: 1, title: mlMulti ? '文件库 - 选择图片（可多选）' : '文件库 - 选择图片', skin: 'dc-layer-modern',
                area: window.innerWidth <= 768 ? ['95%','90%'] : ['720px','600px'], shadeClose: true,
                content: mlBuildHtml(),
                success: function(layero){
                    layero.find('.layui-layer-content').css({'overflow':'hidden','display':'flex','flex-direction':'column'});
                    mlLoad(1);
                    // 搜索
                    var searchTimer;
                    layero.on('input', '#ml-keyword', function(){ var v=this.value; clearTimeout(searchTimer); searchTimer=setTimeout(function(){ mlLoad(1,v); },400); });
                    // 翻页
                    layero.on('click', '#ml-prev', function(){ if(mlPage>1) mlLoad(mlPage-1, $('#ml-keyword').val()); });
                    layero.on('click', '#ml-next', function(){ if(mlPage<mlPages) mlLoad(mlPage+1, $('#ml-keyword').val()); });
                    // 更新批量删除按钮状态
                    function mlUpdateBatchDel(){
                        var $btn = $('#ml-batch-del');
                        if(mlSelected.length > 0){ $btn.addClass('active'); } else { $btn.removeClass('active'); }
                    }
                    // 选中（多选 toggle / 单选互斥）
                    layero.on('click', '.ml-item', function(e){
                        if($(e.target).hasClass('ml-del')) return;
                        var u = $(this).data('url');
                        if(mlMulti){
                            var i = mlSelected.indexOf(u);
                            if(i > -1){ mlSelected.splice(i,1); $(this).removeClass('selected'); }
                            else { mlSelected.push(u); $(this).addClass('selected'); }
                        } else {
                            mlSelected = [u];
                            layero.find('.ml-item').removeClass('selected');
                            $(this).addClass('selected');
                        }
                        mlUpdateBatchDel();
                    });
                    // 单张删除图片
                    layero.on('click', '.ml-del', function(e){
                        e.stopPropagation();
                        var $item = $(this).closest('.ml-item'), aid = $item.data('aid'), url = $item.data('url');
                        layer.confirm('确定删除这张图片？删除后不可恢复', {icon:3, title:'提示'}, function(ci){
                            layer.close(ci);
                            $.post('goods.php?action=media_delete', {aid:aid}, function(res){
                                if(res.code==0){
                                    $item.fadeOut(200,function(){$(this).remove();});
                                    var idx = mlSelected.indexOf(url);
                                    if(idx > -1) mlSelected.splice(idx,1);
                                    mlUpdateBatchDel();
                                    layer.msg('已删除',{icon:1,time:1000});
                                }
                                else { layer.msg(res.msg||'删除失败',{icon:2}); }
                            },'json');
                        });
                    });
                    // 批量删除选中图片
                    layero.on('click', '#ml-batch-del', function(){
                        if(!mlSelected.length) return;
                        var selItems = [];
                        layero.find('.ml-item.selected').each(function(){ selItems.push({ $el:$(this), aid:$(this).data('aid'), url:$(this).data('url') }); });
                        if(!selItems.length){ layer.msg('请先选择要删除的图片',{icon:0}); return; }
                        layer.confirm('确定删除选中的 ' + selItems.length + ' 张图片？删除后不可恢复', {icon:3, title:'批量删除'}, function(ci){
                            layer.close(ci);
                            var li = layer.load(2);
                            var chain = $.Deferred().resolve().promise();
                            selItems.forEach(function(item){
                                chain = chain.then(function(){
                                    return $.post('goods.php?action=media_delete', {aid:item.aid}, function(res){
                                        if(res.code==0){
                                            item.$el.remove();
                                            var idx = mlSelected.indexOf(item.url);
                                            if(idx > -1) mlSelected.splice(idx,1);
                                        }
                                    },'json');
                                });
                            });
                            chain.always(function(){
                                layer.close(li);
                                mlUpdateBatchDel();
                                layer.msg('删除完成',{icon:1,time:1000});
                                mlLoad(mlPage, $('#ml-keyword').val());
                            });
                        });
                    });
                    // 确定
                    layero.on('click', '#ml-confirm', function(){
                        if(mlMulti){
                            var added = 0, skipped = 0;
                            mlSelected.forEach(function(u){ if(appendGalleryImage(u)) added++; else skipped++; });
                            if(added) layer.msg('已添加 ' + added + ' 张' + (skipped ? '，跳过 ' + skipped + ' 张重复/超量' : ''), {icon:1, time:1500});
                        } else {
                            if(mlSelected.length){ $('#sortimg').val(mlSelected[0]); }
                        }
                        layer.close(mlLayerIdx);
                    });
                    // 上传按钮
                    var $fileInput = $('<input type="file" accept="image/*" ' + (mlMulti ? 'multiple' : '') + ' style="display:none">');
                    layero.append($fileInput);
                    layero.on('click', '#ml-upload-btn', function(){ $fileInput.trigger('click'); });
                    $fileInput.on('change', function(){
                        if(this.files && this.files.length){
                            if(mlMulti) mlUploadFiles(this.files);
                            else if(this.files[0]) mlUploadFile(this.files[0]).then(function(){ mlLoad(1, $('#ml-keyword').val()); });
                        }
                        this.value='';
                    });
                    // 拖拽上传
                    var $drop = layero.find('#ml-drop');
                    $drop.on('dragover', function(e){ e.preventDefault(); e.stopPropagation(); $(this).addClass('drag-over'); });
                    $drop.on('dragleave drop', function(e){ e.preventDefault(); e.stopPropagation(); $(this).removeClass('drag-over'); });
                    $drop.on('drop', function(e){
                        var files=e.originalEvent.dataTransfer.files;
                        if(!files.length) return;
                        if(mlMulti) mlUploadFiles(files);
                        else mlUploadFile(files[0]).then(function(){ mlLoad(1, $('#ml-keyword').val()); });
                    });
                    $drop.on('click', function(){ $fileInput.trigger('click'); });
                }
            });
        }


    })
</script>


<script>

    var form = $('#addgoods');

    // 多规格head（仅基础4列，不再包含会员等级列）
    var thed = <?= json_encode($sku_table['head']) ?>;

    // 多规格body（仅基础4列）
    var tbody = <?= json_encode($sku_table['body']) ?>;

    <?php if($action == 'edit'): ?>
        var tbody2 = [ // 单规格：游客价 → 成本价 → 固定价 → 划线价
            {type: 'input', field: 'skus[guest_price]', value: "<?= $goods['skus'][0]['guest_price'] /100 ?>", placeholder: '必填，建议≥最低会员价'},
            {type: 'input', field: 'skus[cost_price]', value: "<?= $goods['skus'][0]['cost_price'] /100 ?>", placeholder: '必填，等级算价基础'},
            {type: 'input', field: 'skus[user_price]', value: "<?= empty($goods['skus'][0]['user_price']) ? '' : ($goods['skus'][0]['user_price'] /100) ?>", placeholder: '选填，空=走等级算价（不可填0）'},
            {type: 'input', field: 'skus[market_price]', value: "<?= empty($goods['skus'][0]['market_price']) ? '' : ($goods['skus'][0]['market_price'] /100) ?>", placeholder: '选填，默认0'},
        ];
    <?php endif; ?>

    <?php if($action == 'release'): ?>
        var tbody2 = [ // 单规格：游客价 → 成本价 → 固定价 → 划线价
            {type: 'input', field: 'skus[guest_price]', value: "", placeholder: '必填，建议≥最低会员价'},
            {type: 'input', field: 'skus[cost_price]', value: "", placeholder: '必填，等级算价基础'},
            {type: 'input', field: 'skus[user_price]', value: "", placeholder: '选填，空=走等级算价（不可填0）'},
            {type: 'input', field: 'skus[market_price]', value: "", placeholder: '选填，默认0'},
        ];
    <?php endif; ?>

    var options = {
        isAttributeElemId: 'fairy-is-attribute',
        productTypeElemId: 'fairy-product-type',
        attributeTableElemId: 'fairy-attribute-table',
        specTableElemId: 'fairy-spec-table',
        skuTableElemId: 'fairy-sku-table',
        //商品规格模式 0单规格 1多规格
        mode: "<?= $goods['is_sku'] == 'y' ? 1 : 0 ?>",
        //是否开启sku表行合并
        rowspan: true,
        //图片上传接口
        uploadUrl: './json/upload.json',
        //获取商品类型接口
        productTypeUrl: '?action=goods_type_data',
        //创建规格模板接口
        goodsTypeCreateUrl: '?action=create_goods_type',
        //获取商品类型下的规格和属性接口
        attrSpecUrl: '?action=attr_spec_data',
        //创建规格接口
        specCreateUrl: '?action=create_spec',
        //删除规格接口
        specDeleteUrl: './json/specDelete.json',
        //创建规格值接口
        specValueCreateUrl: '?action=create_spec_value',
        //删除规格值接口
        specValueDeleteUrl: './json/specValueDelete.json',
        //单规格SKU表配置
        singleSkuTableConfig: {
            thead: thed,
            tbody: tbody2
        },
        //多规格SKU表配置
        multipleSkuTableConfig: {
            thead: thed,
            tbody: tbody
        },
        // ========================================== 回显时相关配置参数 ========================================== //
        //商品id
        goods_id: "<?php echo $goods['id'] ?>",
        //商品类型id
        productTypeId: "<?= isset($goods['attr_id']) ? $goods['attr_id'] : '' ?>",
        //sku数据接口
        skuDataUrl: '?action=sku_data',
    }

    var skuTableObj = new SkuTable(options);

    form.on('submit', function (data) {

        console.log(data)

        //获取表单数据
        console.log(data.field);

        // return false;

        if (skuTableObj.getMode() == 0) {
            //单规格
            // layer.alert(JSON.stringify(data.field), {title: '提交的数据'});
        } else {
            //多规格
            var state = Object.keys(data.field).some(function (item, index, array) {
                return item.startsWith('skus');
            });
            // state ? layer.alert(JSON.stringify(data.field), {title: '提交的数据'}) : layer.msg('sku表数据不能为空', {icon: 5, anim: 6});
        }

        return true;
    });

    // 规格模式切换时更新价格提示
    $('body').on('change', 'input[lay-filter="fairy-is-attribute"]', function() {
        var isMulti = $(this).val() === 'y';
        $('#sku-price-tip-single').toggle(!isMulti);
        $('#sku-price-tip-multi').toggle(isMulti);
    });
    // 初始化提示状态
    (function() {
        var isMulti = <?= json_encode($goods['is_sku'] === 'y') ?>;
        $('#sku-price-tip-single').toggle(!isMulti);
        $('#sku-price-tip-multi').toggle(isMulti);
    })();

</script>


<!--下单输入框（商品级）-->
<script>
(function(){
    var AU_TYPE_MAP = { 'string':'文本', 'tel':'手机号', 'email':'邮箱', 'num':'数字' };
    var AU_TYPE_COLOR = { 'string':'#e0e0e0;color:#555', 'tel':'#e8f5e9;color:#2e7d32', 'email':'#e3f2fd;color:#1565c0', 'num':'#fff3e0;color:#e65100' };

    // 兼容旧格式: {"key":"value"} → [{name,placeholder,type,required,tip}]
    var rawData = <?= $goods['attach_user'] ? $goods['attach_user'] : '[]' ?>;
    var auItems = [];
    if(Array.isArray(rawData)){
        auItems = rawData;
    } else if(rawData && typeof rawData === 'object'){
        Object.keys(rawData).forEach(function(k){ auItems.push({name:k, placeholder:rawData[k]||'', type:'string', required:true, tip:''}); });
    }
    auItems.forEach(function(it){ if(typeof it.required==='undefined') it.required=true; if(!it.tip) it.tip=''; if(!it.type) it.type='string'; });

    function auEscape(text){ if(!text)return ''; var d=document.createElement('div'); d.appendChild(document.createTextNode(text)); return d.innerHTML; }

    // 添加
    $('#au-add-btn').click(function(){
        var name=$('#au-name').val().trim();
        if(!name){ layer.msg('请输入字段名称',{icon:0}); return; }
        auItems.push({ name:name, placeholder:$('#au-placeholder').val().trim(), type:$('#au-type').val()||'string', required:$('#au-required').val()==='1', tip:$('#au-tip').val().trim() });
        auRender();
        $('#au-name').val('').focus(); $('#au-placeholder').val(''); $('#au-tip').val('');
        layer.msg('已添加: '+auEscape(name), {icon:1, time:1000});
    });
    $('#au-name,#au-placeholder,#au-tip').on('keypress',function(e){ if(e.which===13){ e.preventDefault(); $('#au-add-btn').click(); } });
    // 邮箱提示
    $('#au-type').on('change',function(){ $('#au-email-tip')[this.value==='email'?'slideDown':'slideUp'](200); });

    // 编辑弹窗
    function auEdit(idx){
        var it=auItems[idx];
        var typeOpts=''; ['string','tel','email','num'].forEach(function(t){ typeOpts+='<option value="'+t+'"'+(it.type===t?' selected':'')+'>'+AU_TYPE_MAP[t]+'</option>'; });
        var reqOpts='<option value="1"'+(it.required?' selected':'')+'>必填</option><option value="0"'+(!it.required?' selected':'')+'>选填</option>';
        var html='<div style="padding:20px 20px 0;">'
            +'<div class="layui-form-item"><label class="layui-form-label">字段名称</label><div class="layui-input-block"><input type="text" class="layui-input" id="aue-name" value="'+auEscape(it.name)+'"></div></div>'
            +'<div class="layui-form-item"><label class="layui-form-label">输入提示</label><div class="layui-input-block"><input type="text" class="layui-input" id="aue-ph" value="'+auEscape(it.placeholder||'')+'"></div></div>'
            +'<div class="layui-form-item"><label class="layui-form-label">类型</label><div class="layui-input-block"><select id="aue-type" lay-ignore style="width:100%;height:38px;padding:0 10px;border:1px solid #e6e6e6;border-radius:2px;font-size:14px;background:#fff;outline:none;appearance:auto;">'+typeOpts+'</select></div></div>'
            +'<div class="layui-form-item"><label class="layui-form-label">是否必填</label><div class="layui-input-block"><select id="aue-req" lay-ignore style="width:100%;height:38px;padding:0 10px;border:1px solid #e6e6e6;border-radius:2px;font-size:14px;background:#fff;outline:none;appearance:auto;">'+reqOpts+'</select></div></div>'
            +'<div class="layui-form-item"><label class="layui-form-label">说明文字</label><div class="layui-input-block"><input type="text" class="layui-input" id="aue-tip" value="'+auEscape(it.tip||'')+'" placeholder="留空则不显示说明"></div></div>'
            +'</div>';
        layer.open({
            type:1, title:'编辑输入框 - '+auEscape(it.name), area:['500px','auto'], shadeClose:true, content:html,
            btn:['保存','取消'],
            yes:function(index){
                var n=$('#aue-name').val().trim(); if(!n){ layer.msg('字段名称不能为空',{icon:0}); return; }
                auItems[idx].name=n; auItems[idx].placeholder=$('#aue-ph').val().trim(); auItems[idx].type=$('#aue-type').val();
                auItems[idx].required=$('#aue-req').val()==='1'; auItems[idx].tip=$('#aue-tip').val().trim();
                auRender(); layer.close(index); layer.msg('已保存',{icon:1,time:1000});
            }
        });
    }

    // 渲染列表
    function auRender(){
        var $l=$('#au-list').empty();
        if(!auItems.length){
            $l.html('<div style="text-align:center;color:#bbb;padding:30px 0;font-size:13px;"><i class="ri-file-list-3-line" style="font-size:28px;display:block;margin-bottom:6px;"></i>暂未添加商品级下单输入框</div>');
            $('#au-json').val('[]');
            return;
        }
        var html='<table class="btx-table"><thead><tr><th style="width:40px;color:#aaa;">#</th><th>字段名称</th><th>输入提示</th><th style="width:80px;">类型</th><th style="width:70px;text-align:center;">必填</th><th>说明</th><th style="width:80px;text-align:center;">操作</th></tr></thead><tbody>';
        auItems.forEach(function(it,i){
            var tl=AU_TYPE_MAP[it.type]||it.type, tc=AU_TYPE_COLOR[it.type]||AU_TYPE_COLOR['string'];
            var isReq=it.required!==false;
            var reqHtml=isReq?'<span class="au-req-tag" style="background:#ffebee;color:#c62828;display:inline-block;padding:2px 8px;border-radius:3px;font-size:12px;cursor:pointer;">必填</span>'
                              :'<span class="au-req-tag" style="background:#e8f5e9;color:#2e7d32;display:inline-block;padding:2px 8px;border-radius:3px;font-size:12px;cursor:pointer;">选填</span>';
            var tipText=it.tip?auEscape(it.tip):'<span style="color:#ccc;">-</span>';
            html+='<tr>'
                +'<td style="color:#aaa;">'+(i+1)+'</td>'
                +'<td>'+auEscape(it.name)+'</td>'
                +'<td style="color:#999;">'+(it.placeholder?auEscape(it.placeholder):'-')+'</td>'
                +'<td><span style="display:inline-block;padding:2px 8px;border-radius:3px;font-size:12px;background:'+tc+'">'+tl+'</span></td>'
                +'<td style="text-align:center;">'+reqHtml+'</td>'
                +'<td style="color:#999;font-size:12px;">'+tipText+'</td>'
                +'<td style="text-align:center;"><span class="au-edit" data-idx="'+i+'" title="编辑" style="color:#1e9fff;cursor:pointer;font-size:14px;margin-right:8px;"><i class="ri-edit-line"></i></span><span class="au-del" data-idx="'+i+'" title="删除" style="color:#ff5722;cursor:pointer;font-size:14px;"><i class="ri-delete-bin-line"></i></span></td>'
                +'</tr>';
        });
        html+='</tbody></table>';
        $l.html(html);

        // 点击必填/选填切换
        $l.find('.au-req-tag').click(function(){ var idx=$(this).closest('tr').find('.au-del').data('idx'); auItems[idx].required=!auItems[idx].required; auRender(); });
        // 编辑
        $l.find('.au-edit').click(function(){ auEdit($(this).data('idx')); });
        // 删除
        $l.find('.au-del').click(function(){ var idx=$(this).data('idx'); var n=auItems[idx].name; layer.confirm('确定删除「'+auEscape(n)+'」吗？',{icon:3,title:'删除确认'},function(ci){ auItems.splice(idx,1); auRender(); layer.close(ci); layer.msg('已删除',{icon:1,time:1000}); }); });

        $('#au-json').val(JSON.stringify(auItems));
    }

    auRender();
})();
</script>

<!-- 批量优惠设置功能 -->
<script>
(function() {
    var $ = layui.$;

    // 根据容器的 data 属性创建一条 kv-item（name / step / placeholder 由容器指定）
    function createDiscountItem($container, quantity, amount) {
        var name = $container.data('name') || 'discount';
        var step = $container.data('step') || '0.01';
        var ph   = $container.data('ph')   || '优惠值';
        var skuKey = $container.data('sku');
        quantity = quantity || '';
        amount = amount || '';
        return '<div class="kv-item">' +
            '<div class="layui-input-inline">' +
            '<input step="1" name="' + name + '[' + skuKey + '][number][]" type="number" class="layui-input" placeholder="购买数量" value="' + quantity + '" />' +
            '</div>' +
            '<div class="layui-input-inline">' +
            '<input step="' + step + '" name="' + name + '[' + skuKey + '][amount][]" type="number" class="layui-input" placeholder="' + ph + '" value="' + amount + '" />' +
            '</div>' +
            '<button type="button" class="kv-btn delete-btn layui-btn layui-bg-red"><i class="ri-delete-bin-line"></i></button>' +
            '<button type="button" class="kv-btn add-btn layui-btn layui-bg-blue"><i class="ri-add-line"></i></button>' +
            '</div>';
    }

    // 添加/删除优惠项事件
    $(document).on('click', '.discount-items .add-btn', function() {
        var $container = $(this).closest('.discount-items');
        $container.append(createDiscountItem($container, '', ''));
    });

    $(document).on('click', '.discount-items .delete-btn', function() {
        var $item = $(this).closest('.kv-item');
        var $container = $item.closest('.discount-items');
        if ($container.find('.kv-item').length > 1) {
            $item.remove();
        } else {
            $item.find('input').val('');
        }
    });

    // 监听规格类型切换，同步切换每个 section 内的单/多规格区域
    $(document).on('change', 'input[name="is_sku"]', function() {
        var isSku = $(this).val() === 'y';
        $('.discount-section').each(function(){
            if (isSku) {
                $(this).find('.discount-single-wrap').hide();
                $(this).find('.discount-multi-wrap').show();
            } else {
                $(this).find('.discount-single-wrap').show();
                $(this).find('.discount-multi-wrap').hide();
            }
        });
        if (isSku) {
            $('#skuContentSection').show();
        } else {
            $('#skuContentSection').hide();
        }
    });
})();
</script>