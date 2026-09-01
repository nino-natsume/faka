<?php
defined('DC_ROOT') || exit('access denied!');
$activePlugins = Option::get('active_plugins') ?: [];
$isAdminColorActive = in_array('admin_color/admin_color.php', $activePlugins);
if ($isAdminColorActive) {
    $colorStorage = Storage::getInstance('admin_color');
    $tplPrimaryColorDark = $colorStorage->getValue('primary_color_dark') ?: '#4C7D71';
    $tplPrimaryColor = $colorStorage->getValue('primary_color') ?: '#4C7D71';
    $gearColor = (strpos($tplPrimaryColor, 'gradient') !== false) ? $tplPrimaryColorDark : $tplPrimaryColor;
} else {
    $gearColor = '#4C7D71';
}
$token = LoginAuth::genToken();
?>

<!-- ==================== 样式 ==================== -->
<style>
.layui-table-tool-temp { padding-right: 0; }

/* 行高亮 */
.dc-goods-row-active,
.dc-goods-row-active td { background-color: #f2f6fc !important; }

/* SKU 标签 */
.sku-tag { display: inline-block; padding: 0 6px; line-height: 18px; font-size: 12px; border-radius: 2px; font-weight: 400; vertical-align: middle; }
.sku-tag.sku-multi { background: #e8f4ff; color: #1677ff; }
.sku-tag.sku-single { background: #e3eeea; color: #52886a; }

/* 商品类型标签 */
.goods-type-tag { display: inline-block; max-width: 100%; padding: 1px 8px; line-height: 20px; font-size: 12px; font-weight: 500; border-radius: 4px; border: 1px solid; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; vertical-align: middle; box-sizing: border-box; }
.goods-type-tag.type-once { background: #edf7ee; color: #389e0d; border-color: #b7eb8f; }
.goods-type-tag.type-service { background: #fff1f0; color: #cf1322; border-color: #ffa39e; }
.goods-type-tag.type-general { background: #e6f4ff; color: #0958d9; border-color: #91caff; }
.goods-type-tag.type-docking { background: #f9f0ff; color: #531dab; border-color: #d3adf7; }
.goods-type-tag.type-physical { background: #fff7ed; color: #c2410c; border-color: #fdba74; }
@media (max-width: 768px) {
    .goods-type-tag { padding: 1px 6px; font-size: 11px; line-height: 18px; }
}

/* 上下架状态 */
.shelf-status { cursor: pointer; padding: 1px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; display: inline-block; line-height: 20px; transition: all .2s; }
.shelf-status.on { background: #dcfce7; color: #16a34a; border: 1px solid #86efac; }
.shelf-status.off { background: #fef2f2; color: #dc2626; border: 1px solid #fca5a5; }
.shelf-status:hover { opacity: .8; transform: scale(1.05); }

/* 排序输入框 */
.sort-num-input {
    width:60px!important; height:28px!important; line-height:28px;
    text-align:center; font-size:13px; font-weight:500; color:#333;
    border:1px solid #e8e8e8!important; border-radius:6px!important;
    background:#fafafa; transition:all .2s;
    -moz-appearance:textfield;
}
.sort-num-input::-webkit-outer-spin-button,
.sort-num-input::-webkit-inner-spin-button { -webkit-appearance:none; margin:0; }
.sort-num-input:hover { border-color:#c0c4cc!important; background:#fff; }
.sort-num-input:focus { border-color:#1e9fff!important; background:#fff; box-shadow:0 0 0 2px rgba(30,159,255,.12); }
</style>

<!-- ==================== 表格 ==================== -->
<table class="layui-hide" id="index" lay-filter="index"></table>

<!-- ==================== 模板 ==================== -->

<!-- 工具栏 -->
<script type="text/html" id="toolbar">
<div style="display:flex;align-items:center;justify-content:center;position:relative;padding:10px 0 15px;border-bottom:1px solid #f0f0f0;">
    <span class="mac-dots-fs" title="点击放大/还原（Esc 退出）" style="position:absolute;left:0;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
        <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
        <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
        <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
    </span>
    <span style="color:#667797;font-size:14px;font-weight:500;">商品列表</span>
</div>
<div style="display:flex;align-items:center;justify-content:space-between;width:100%;">
    <div class="layui-btn-container">
        <button class="layui-btn layui-btn-primary layui-border-green" lay-event="refresh"><i class="ri-refresh-line"></i></button>
        <button class="layui-btn" lay-event="add">添加</button>
        <button id="toolbar-del" class="layui-btn  layui-bg-red layui-btn-disabled" lay-event="del">删除</button>
        <button class="layui-btn layui-btn-warm" lay-event="recycle"><i class="ri-delete-bin-line"></i> 回收站</button>
        <button id="toolbar-off-shelf" class="layui-btn layui-btn-primary layui-border-blue" lay-event="off_shelf"><i class="ri-eye-off-line"></i> 已下架</button>
        <button id="toolbar-docking" class="layui-btn layui-btn-primary layui-border-blue" lay-event="docking_filter"><i class="ri-link"></i> 对接商品</button>
    </div>
    <form class="layui-form" style="display:flex;align-items:center;gap:8px;margin:0;">
        <div class="layui-input-inline layui-input-wrap" style="width:140px;margin:0;">
            <select name="category_id">
                <option value="">商品分类</option>
                <?php foreach ($sorts as $val): ?>
                <option value="<?= $val['sid'] ?>"><?= $val['sortname'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="layui-input-inline layui-input-wrap" style="width:140px;margin:0;">
            <select name="goods_type">
                <option value="">商品类型</option>
                <?php foreach ($goodsTypeList as $gt): ?>
                <option value="<?= $gt['value'] ?>"><?= $gt['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="layui-input-inline layui-input-wrap" style="width:160px;margin:0;">
            <input type="text" name="keyword" placeholder="商品名称" lay-affix="clear" class="layui-input">
        </div>
        <button class="layui-btn layui-btn-sm" lay-submit lay-filter="index-search">搜索</button>
        <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" id="btn-reset">重置</button>
    </form>
</div>
</script>

<!-- 封面 -->
<script type="text/html" id="tpl-cover">
<a href="javascript:;" lay-event="img">
    <img onerror="this.onerror=null;this.src='./views/images/null.png'" src="{{ d.cover }}" style="width:40px;border-radius:3px;">
</a>
</script>

<!-- 标题（含规格标签） -->
<script type="text/html" id="tpl-title">
{{# if(d.is_docking){ }}<span class="goods-type-tag type-docking" style="font-size:11px;padding:0 5px;line-height:18px;margin-right:3px;vertical-align:middle;">对接</span>{{# } }}
{{# if(d.is_sku === 'y'){ }}<span class="sku-tag sku-multi">多规格</span>{{# } }}
{{# if(d.is_sku === 'n'){ }}<span class="sku-tag sku-single">单规格</span>{{# } }}
<span style="margin-left:4px;">{{ d.title }}</span>
</script>

<!-- 商品类型 -->
<script type="text/html" id="tpl-type">
<?php doAction('adm_goods_list_type', "{{ d.type }}"); ?>
</script>

<!-- 库存（多规格悬停提示） -->
<script type="text/html" id="tpl-stock">
{{# if(d.sku_tips){ }}
<span class="sku-stock-tip" data-tips="{{ d.sku_tips }}" style="cursor:help;border-bottom:1px dashed #999;">{{ d.stock }}</span>
{{# } else { }}
{{ d.stock }}
{{# } }}
</script>

<!-- 上下架状态 -->
<script type="text/html" id="tpl-shelf">
<span class="shelf-status {{= d.is_on_shelf == 1 ? 'on' : 'off' }}" data-id="{{= d.id }}" data-status="{{= d.is_on_shelf }}" title="点击切换">
    {{= d.is_on_shelf == 1 ? '上架中' : '已下架' }}
</span>
</script>

<!-- 排序输入框 -->
<script type="text/html" id="tpl-sort-num">
<input type="number" class="layui-input sort-num-input" data-id="{{ d.id }}" value="{{ d.sort_num || 0 }}" title="数字越大越靠前">
</script>

<!-- 操作（齿轮） -->
<script type="text/html" id="tpl-operate">
<a href="javascript:;" class="dc-goods-action-btn" data-id="{{ d.id }}" data-type="{{= d.is_docking ? 'docking' : d.type }}" data-title="{{ d.title }}">
    <i class="layui-icon layui-icon-set-fill" style="font-size:22px;color:<?= $gearColor ?>;"></i>
</a>
</script>

<!-- ==================== 逻辑 ==================== -->
<script>
layui.use(['table', 'dropdown'], function(){
    var table    = layui.table;
    var form     = layui.form;
    var dropdown = layui.dropdown;
    var $        = layui.$;
    var TOKEN    = '<?= $token ?>';
    var TABLE_ID = 'index';

    /* ---------- 工具函数 ---------- */

    function isMobile(){ return window.innerWidth < 1200; }
    function layerArea(w, h){ return isMobile() ? ['98%','85%'] : [w, h]; }

    function ajaxPost(action, data, onOk){
        data.token = TOKEN;
        $.ajax({
            url: '?action=' + action, type: 'POST', dataType: 'json', data: data,
            success: function(e){
                if(e.code == 400){ layer.msg(e.msg); return; }
                onOk(e);
            },
            error: function(xhr){
                layer.msg(xhr.responseJSON ? xhr.responseJSON.msg : '操作失败');
            }
        });
    }

    function clipboardCopy(text){
        if(navigator.clipboard && navigator.clipboard.writeText){
            navigator.clipboard.writeText(text).then(
                function(){ layer.msg('链接已复制', {icon:1, time:1500}); },
                function(){ fallbackCopy(text); }
            );
        } else {
            fallbackCopy(text);
        }
    }

    function fallbackCopy(text){
        var ta = document.createElement('textarea');
        ta.value = text; ta.style.cssText = 'position:fixed;opacity:0';
        document.body.appendChild(ta); ta.select();
        try { document.execCommand('copy'); layer.msg('链接已复制', {icon:1, time:1500}); }
        catch(e){ layer.msg('复制失败，请手动复制：' + text, {icon:2}); }
        document.body.removeChild(ta);
    }

    /* ---------- 状态 ---------- */

    var offShelfFilter = false;
    var dockingFilter = false;
    var pageSize = parseInt(localStorage.getItem('goods_limit')) || 10;

    /* ---------- 表格渲染 ---------- */

    table.render({
        elem: '#index',
        id: TABLE_ID,
        url: '?action=index',
        toolbar: '#toolbar',
        defaultToolbar: [],
        autoSort: false,
        page: true,
        limit: pageSize,
        limits: [10,20,30,50,100],
        lineStyle: 'height:0',
        cols: [[
            {type:'checkbox', fixed: 'left'},
            {field:'sort_num',    title:'排序',     width:90,  sort:true, align:'center', templet:'#tpl-sort-num'},
            {field:'cover',       title:'商品图',   width:80,   align:'center', templet:'#tpl-cover'},
            {field:'title',       title:'商品标题', minWidth:260, templet:'#tpl-title'},
            {field:'sort_name',   title:'商品分类', width:92,   align:'center'},
            {field:'type_text',   title:'商品类型', width:110,  align:'center', templet:'#tpl-type'},
            {field:'sales',       title:'销量',     width:80,   sort:true, align:'center'},
            {field:'stock',       title:'库存',     width:80,   sort:true, align:'center', templet:'#tpl-stock'},
            {field:'is_on_shelf', title:'上架',     width:88,   align:'center', templet:'#tpl-shelf'},
            {field:'create_time', title:'添加时间', width:150,  sort:true, align:'center'},
            {fixed: 'right', title:'操作', width:80, align:'center', templet:'#tpl-operate'}
        ]],
        done: function(res, curr){
            var self = this;

            // 同步"已下架"按钮状态
            var $btn = $('[lay-event="off_shelf"]');
            if(offShelfFilter){
                $btn.removeClass('layui-btn-primary layui-border-blue').addClass('layui-bg-green')
                    .html('<i class="ri-eye-line"></i> 全部商品');
            } else {
                $btn.removeClass('layui-bg-green').addClass('layui-btn-primary layui-border-blue')
                    .html('<i class="ri-eye-off-line"></i> 已下架');
            }

            // 同步"对接商品"按钮状态
            var $dkBtn = $('[lay-event="docking_filter"]');
            if(dockingFilter){
                $dkBtn.removeClass('layui-btn-primary layui-border-blue').addClass('layui-bg-green')
                    .html('<i class="ri-link"></i> 全部商品');
            } else {
                $dkBtn.removeClass('layui-bg-green').addClass('layui-btn-primary layui-border-blue')
                    .html('<i class="ri-link"></i> 对接商品');
            }

            // 持久化每页条数
            setTimeout(function(){
                var sel = document.querySelector('.layui-laypage-limits select');
                if(sel) sel.onchange = function(){
                    localStorage.setItem('goods_limit', this.value);
                    table.reload(TABLE_ID, { page:{curr:curr}, limit:parseInt(this.value) });
                };
            }, 0);
        }
    });

    /* ---------- 搜索 & 重置 ---------- */

    form.on('submit(index-search)', function(data){
        table.reload(TABLE_ID, { page:{curr:1}, where:data.field });
        return false;
    });

    $(document).on('click', '#btn-reset', function(){
        var $bar = $('.layui-table-tool-temp');
        $bar.find('input[name="keyword"]').val('');
        $bar.find('select[name="category_id"]').val('');
        $bar.find('select[name="goods_type"]').val('');
        form.render('select');
        offShelfFilter = false;
        dockingFilter = false;
        table.reload(TABLE_ID, { page:{curr:1}, where:{category_id:'', goods_type:'', keyword:'', is_on_shelf:'', is_docking:''} });
    });

    /* ---------- 工具栏按钮 ---------- */

    table.on('toolbar(index)', function(obj){
        var checked = table.checkStatus(TABLE_ID);
        switch(obj.event){
            case 'refresh':
                table.reload(TABLE_ID);
                break;

            case 'add':
                layer.open({
                    id:'add-goods', title:'添加商品', type:2,
                    area:layerArea('1200px','860px'), skin:'dc-layer-modern',
                    content:'goods.php?action=release&popup=1',
                    fixed:false, maxmin:true, shadeClose:true,
                    end: function(){ table.reload(TABLE_ID); }
                });
                break;

            case 'off_shelf':
                offShelfFilter = !offShelfFilter;
                table.reload(TABLE_ID, { page:{curr:1}, where:{is_on_shelf: offShelfFilter ? 0 : ''} });
                break;

            case 'docking_filter':
                dockingFilter = !dockingFilter;
                table.reload(TABLE_ID, { page:{curr:1}, where:{is_docking: dockingFilter ? 1 : ''} });
                break;

            case 'del':
                var rows = checked.data;
                if(!rows.length) return;
                var ids = rows.map(function(r){ return r.id; }).join(',');
                layer.confirm('确定要删除选中的商品？', {btn:['确认','取消'], icon:3, title:'温馨提示'}, function(idx){
                    layer.close(idx);
                    ajaxPost('del', {ids:ids}, function(){
                        layer.msg('商品已删除');
                        table.reload(TABLE_ID);
                    });
                });
                break;


            case 'recycle':
                layer.open({
                    id:'recycle', title:'商品回收站', type:2,
                    area:layerArea('1200px','800px'), skin:'dc-layer-modern',
                    content:'goods_recycle.php?popup=1',
                    fixed:false, maxmin:true, shadeClose:true
                });
                break;
        }
    });

    /* ---------- 行工具事件（封面预览） ---------- */

    table.on('tool(index)', function(obj){
        if(obj.event === 'img'){
            layer.photos({
                photos: {title:obj.data.title, start:0, data:[{alt:obj.data.title, pid:1, src:obj.data.cover}]}
            });
        }
    });

    /* ---------- 服务端排序 ---------- */

    table.on('sort(index)', function(obj){
        table.reload(TABLE_ID, {
            initSort: obj,
            where: { field:obj.field, order:obj.type }
        });
    });

    /* ---------- 复选框 → 删除按钮状态 ---------- */

    table.on('checkbox(index)', function(){
        var n = table.checkStatus(TABLE_ID).data.length;
        $('#toolbar-del').toggleClass('layui-btn-disabled', n === 0);
    });

    /* ---------- 上下架切换 ---------- */

    $(document).on('click', '.shelf-status', function(){
        var $el = $(this), id = $el.data('id'), cur = parseInt($el.data('status')), next = cur === 1 ? 0 : 1;
        var loading = layer.load(2);
        ajaxPost('shelf', {goods_id:id, status:next}, function(){
            $el.data('status', next);
            if(next === 1){ $el.removeClass('off').addClass('on').text('上架中'); }
            else { $el.removeClass('on').addClass('off').text('已下架'); }
            layer.msg('状态已更新');
        });
        // 无论成功失败都关闭 loading（ajaxPost error 分支也会执行）
        setTimeout(function(){ layer.close(loading); }, 3000);
    });

    /* ---------- 排序输入框 ---------- */

    $(document).on('change', '.sort-num-input', function(){
        var $el = $(this), loading = layer.load(2);
        ajaxPost('update_sort_num', {goods_id:$el.data('id'), sort_num:$el.val() || 0}, function(e){
            layer.close(loading);
            layer.msg(e.code == 200 ? '排序已更新' : (e.msg || '更新失败'));
        });
    });

    /* ---------- 多规格库存悬停提示 ---------- */

    var tipIdx = null;
    $(document).on('mouseenter', '.sku-stock-tip', function(){
        var t = $(this).data('tips');
        if(t) tipIdx = layer.tips(t.replace(/\n/g,'<br>'), this, {tips:[1,'#333'], time:0});
    }).on('mouseleave', '.sku-stock-tip', function(){
        if(tipIdx){ layer.close(tipIdx); tipIdx = null; }
    });

    /* ---------- 齿轮下拉菜单 ---------- */

    $(document).on('click', function(){ $('.dc-goods-row-active').removeClass('dc-goods-row-active'); });

    $(document).on('click', '.dc-goods-action-btn', function(e){
        e.stopPropagation();
        var $btn = $(this), gid = $btn.data('id');
        var gtype = $btn.data('type') || '';
        var gtitle = $btn.data('title') || '';
        $('.dc-goods-row-active').removeClass('dc-goods-row-active');
        $btn.closest('tr').addClass('dc-goods-row-active');

        // 根据商品类型动态决定「库存管理」菜单项
        var stockItem = (gtype === 'docking')
            ? {title:'对接订单', id:'docking_order'}
            : {title:'库存管理', id:'stock'};

        if(window._dcGoodsDD) try{ window._dcGoodsDD.config = null; } catch(ex){}
        window._dcGoodsDD = dropdown.render({
            elem: $btn[0], show:true, align:'right',
            data: [
                {title:'<b style="color:#333;">ID：'+gid+'</b>', id:'header', disabled:true},
                {type:'-'},
                {title:'编辑商品', id:'edit'},
                stockItem,
                {title:'复制商品', id:'copy'},
                {title:'商品链接', id:'copyLink'},
                {type:'-'},
                {title:'<span style="color:#ff4d4f;">删除商品</span>', id:'del'}
            ],
            templet: function(d){ return d.title; },
            click: function(o){ handleGoodsAction(o.id, gid, gtitle); }
        });
    });

    /* ---------- 商品操作分发 ---------- */

    function handleGoodsAction(act, gid, gtitle){
        switch(act){
            case 'edit':
                layer.open({
                    id:'edit-goods', title:'编辑商品', type:2,
                    area:layerArea('1200px','860px'), skin:'dc-layer-modern',
                    content:'goods.php?action=edit&id=' + gid + '&popup=1',
                    fixed:false, maxmin:true, shadeClose:true,
                    end: function(){ table.reload(TABLE_ID); }
                });
                break;

            case 'stock':
                layer.open({
                    id:'stock', title:'库存管理', type:2,
                    area:layerArea('1200px','860px'), skin:'dc-layer-modern',
                    content:'stock.php?action=stock_popup&goods_id=' + gid,
                    fixed:false, maxmin:true, shadeClose:true
                });
                break;

            case 'docking_order':
                layer.open({
                    id:'docking-order', title:'对接订单 - ' + gtitle, type:2,
                    area:layerArea('1200px','860px'), skin:'dc-layer-modern',
                    content:'order.php?popup=1&goods_title=' + encodeURIComponent(gtitle),
                    fixed:false, maxmin:true, shadeClose:true
                });
                break;

            case 'copy':
                layer.confirm('确定要复制这个商品吗？<br><small style="color:#999;">复制的商品将默认为下架状态</small>', {
                    btn:['确认复制','取消'], icon:3, title:'复制商品'
                }, function(idx){
                    layer.close(idx);
                    var loading = layer.load(2);
                    ajaxPost('copy', {goods_id:gid}, function(){
                        layer.msg('商品复制成功！', {icon:1});
                        table.reload(TABLE_ID);
                    });
                    setTimeout(function(){ layer.close(loading); }, 5000);
                });
                break;

            case 'copyLink':
                var base = location.origin + location.pathname.replace(/\/admin\/.*$/, '');
                clipboardCopy(base + '/?post=' + gid);
                break;

            case 'del':
                layer.confirm('确定删除该商品？', {btn:['确认','取消'], icon:3, title:'温馨提示'}, function(idx){
                    layer.close(idx);
                    ajaxPost('del', {ids:gid}, function(){
                        layer.msg('商品已删除');
                        table.reload(TABLE_ID);
                    });
                });
                break;
        }
    }
});
</script>
