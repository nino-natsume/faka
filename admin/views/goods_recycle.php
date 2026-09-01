<?php
defined('DC_ROOT') || exit('access denied!');
$token = LoginAuth::genToken();
?>

<!-- ==================== 样式 ==================== -->
<style>
.layui-table-tool-temp { padding-right: 0; }
#recycle + .layui-table-view .layui-table-cell { height: 32px; line-height: 32px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; box-sizing: border-box; }
#recycle + .layui-table-view .layui-table-fixed .layui-table-cell { height: 32px; line-height: 32px; }
.recycle-title-cell { display: flex; align-items: center; gap: 8px; min-width: 0; overflow: hidden; white-space: nowrap; }
.recycle-title-cell .layui-badge,
.recycle-title-cell .layui-badge-rim { flex-shrink: 0; margin-left: 0 !important; }
.recycle-title-text { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
@media screen and (max-width: 768px) {
    #recycle + .layui-table-view .layui-table-cell { height: 30px; line-height: 30px; }
    .recycle-title-cell { gap: 5px; }
}
<?php if (Input::getIntVar('popup', 0)): ?>
body { padding-top: 10px; }
.layui-table-tool-temp { padding-top: 10px; }
<?php endif; ?>
</style>

<!-- ==================== 表格 ==================== -->
<table class="layui-hide" id="recycle" lay-filter="recycle"></table>

<!-- ==================== 模板 ==================== -->

<!-- 工具栏 -->
<script type="text/html" id="recycle-toolbar">
<?php if (Input::getIntVar('popup', 0) == 0): ?>
<div style="display:flex;align-items:center;justify-content:center;position:relative;padding:10px 0 15px;border-bottom:1px solid #f0f0f0;">
    <span class="mac-dots-fs" title="点击放大/还原（Esc 退出）" style="position:absolute;left:0;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
        <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
        <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
        <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
    </span>
    <span style="color:#667797;font-size:14px;font-weight:500;">商品回收站</span>
</div>
<?php endif; ?>
<div style="display:flex;align-items:center;justify-content:space-between;width:100%;">
    <div class="layui-btn-container">
        <button class="layui-btn layui-btn-primary layui-border-green" lay-event="refresh"><i class="ri-refresh-line"></i></button>
        <button id="toolbar-restore" class="layui-btn layui-btn-sm layui-bg-blue layui-btn-disabled" lay-event="restore"><i class="ri-arrow-go-back-line"></i> 恢复</button>
        <button id="toolbar-permanent-del" class="layui-btn  layui-bg-red layui-btn-disabled" lay-event="permanent_delete"><i class="ri-delete-bin-line"></i> 彻底删除</button>
    </div>
    <form class="layui-form" style="display:flex;align-items:center;gap:6px;margin:0;">
        <div class="layui-input-inline layui-input-wrap" style="width:140px;margin:0;">
            <select name="category_id">
                <option value="">商品分类</option>
                <?php foreach ($sorts as $val): ?>
                <option value="<?= $val['sid'] ?>"><?= $val['sortname'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="layui-input-inline layui-input-wrap" style="width:140px;margin:0;">
            <input type="text" name="keyword" placeholder="商品名称" lay-affix="clear" class="layui-input">
        </div>
        <button class="layui-btn layui-btn-sm" lay-submit lay-filter="recycle-search">搜索</button>
        <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" id="recycle-btn-reset">重置</button>
    </form>
</div>
</script>

<!-- 封面 -->
<script type="text/html" id="recycle-cover">
<a href="javascript:;" lay-event="img">
    <img onerror="this.onerror=null;this.src='./views/images/null.png'" src="{{ d.cover }}" style="width:40px;border-radius:3px;">
</a>
</script>

<!-- 标题 -->
<script type="text/html" id="recycle-title">
{{# var typeMap = {duli:'green', xuni:'red', guding:'blue', post:'purple'}; }}
<div class="recycle-title-cell" title="{{ d.title }}">
    {{# if(typeMap[d.type]){ }}<span class="layui-badge layui-bg-{{= typeMap[d.type] }}">{{ d.type_text }}</span>{{# } }}
    {{# if(d.is_sku === 'y'){ }}<span class="layui-badge-rim layui-border-blue">多规格</span>{{# } }}
    {{# if(d.is_sku === 'n'){ }}<span class="layui-badge-rim layui-border-cyan">单规格</span>{{# } }}
    <span class="recycle-title-text">{{ d.title }}</span>
</div>
</script>

<!-- 操作 -->
<script type="text/html" id="recycle-operate">
<a class="layui-btn layui-btn-xs layui-bg-blue" lay-event="restore">恢复</a>
<a class="layui-btn layui-btn-xs layui-bg-red" lay-event="permanent_delete">彻底删除</a>
</script>

<!-- ==================== 逻辑 ==================== -->
<script>
layui.use(['table', 'form'], function(){
    var table    = layui.table;
    var form     = layui.form;
    var $        = layui.$;
    var TOKEN    = '<?= $token ?>';
    var TABLE_ID = 'recycle';

    /* ---------- 工具函数 ---------- */

    function ajaxPost(action, data, onOk){
        data.token = TOKEN;
        var loading = layer.load(2);
        $.ajax({
            url: '?action=' + action, type: 'POST', dataType: 'json', data: data,
            success: function(e){
                layer.close(loading);
                if(e.code == 0){
                    layer.msg(e.msg || onOk.msg || '操作成功', {icon:1});
                    if(onOk.reload !== false) reloadRecycleTable();
                    if(typeof onOk === 'function') onOk(e);
                } else {
                    layer.msg(e.msg || '操作失败', {icon:2});
                }
            },
            error: function(xhr){
                layer.close(loading);
                var msg = '操作失败';
                try { msg = xhr.responseJSON.msg || JSON.parse(xhr.responseText).msg || msg; } catch(e){}
                layer.msg(msg, {icon:2});
            }
        });
    }

    function getCheckedIds(){
        var rows = table.checkStatus(TABLE_ID).data;
        return rows.length ? rows.map(function(r){ return r.id; }).join(',') : '';
    }

    /* ---------- 表格渲染 ---------- */

    var pageSize = parseInt(localStorage.getItem('recycle_limit')) || 10;

    function resizeRecycleTable(){
        if (table.resize) table.resize(TABLE_ID);
    }

    function reloadRecycleTable(options){
        table.reload(TABLE_ID, options || {});
        setTimeout(resizeRecycleTable, 80);
    }

    table.render({
        elem: '#recycle',
        id: TABLE_ID,
        url: '?action=list',
        toolbar: '#recycle-toolbar',
        defaultToolbar: [],
        autoSort: false,
        page: true,
        limit: pageSize,
        limits: [10,20,30,50,100],
        lineStyle: 'height:30px',
        cols: [[
            {type:'checkbox', fixed: 'left'},
            {field:'cover',                  title:'封面图',   width:80,  align:'center', templet:'#recycle-cover'},
            {field:'title',                  title:'商品标题', minWidth:170, templet:'#recycle-title'},
            {field:'sort_name',              title:'商品分类', width:130, align:'center'},
            {field:'sales',                  title:'销量',     width:80,  align:'center'},
            {field:'stock',                  title:'库存',     width:73,  align:'center'},
            {field:'create_time',            title:'创建时间', width:150, align:'center'},
            {field:'delete_time_formatted',  title:'删除时间', width:150, align:'center'},
            {fixed: 'right', title:'操作', width:160, align:'center', templet:'#recycle-operate'}
        ]],
        done: function(res, curr){
            resizeRecycleTable();
            setTimeout(function(){
                resizeRecycleTable();
                var sel = document.querySelector('.layui-laypage-limits select');
                if(sel) sel.onchange = function(){
                    localStorage.setItem('recycle_limit', this.value);
                    reloadRecycleTable({ page:{curr:curr}, limit:parseInt(this.value) });
                };
            }, 0);
        }
    });

    /* ---------- 搜索 & 重置 ---------- */

    form.on('submit(recycle-search)', function(data){
        reloadRecycleTable({ page:{curr:1}, where:data.field });
        return false;
    });

    $(document).on('click', '#recycle-btn-reset', function(){
        var $bar = $('.layui-table-tool-temp');
        $bar.find('input[name="keyword"]').val('');
        $bar.find('select[name="category_id"]').val('');
        form.render('select');
        reloadRecycleTable({ page:{curr:1}, where:{category_id:'', keyword:''} });
    });

    /* ---------- 工具栏按钮 ---------- */

    table.on('toolbar(recycle)', function(obj){
        switch(obj.event){
            case 'refresh':
                reloadRecycleTable();
                break;

            case 'restore':
                var ids = getCheckedIds();
                if(!ids) return layer.msg('请选择要恢复的商品');
                layer.confirm('确定要恢复选中的商品吗？', {btn:['确认恢复','取消'], icon:3, title:'恢复商品'}, function(idx){
                    layer.close(idx);
                    ajaxPost('restore', {ids:ids}, function(){});
                });
                break;

            case 'permanent_delete':
                var ids = getCheckedIds();
                if(!ids) return layer.msg('请选择要删除的商品');
                layer.confirm('确定要彻底删除选中的商品吗？<br><span style="color:#666;">将同时清理未售卡密库存、规格、折扣等关联数据</span><br><span style="color:red;">此操作不可恢复！</span>', {
                    btn:['确认删除','取消'], icon:2, title:'彻底删除'
                }, function(idx){
                    layer.close(idx);
                    ajaxPost('permanent_delete', {ids:ids}, function(){});
                });
                break;
        }
    });

    /* ---------- 单行操作 ---------- */

    table.on('tool(recycle)', function(obj){
        var d = obj.data;
        switch(obj.event){
            case 'img':
                layer.photos({ photos:{title:d.title, start:0, data:[{alt:d.title, pid:1, src:d.cover}]} });
                break;

            case 'restore':
                layer.confirm('确定要恢复这个商品吗？', {btn:['确认恢复','取消'], icon:3, title:'恢复商品'}, function(idx){
                    layer.close(idx);
                    ajaxPost('restore', {ids:d.id}, function(){});
                });
                break;

            case 'permanent_delete':
                layer.confirm('确定要彻底删除这个商品吗？<br><span style="color:#666;">将同时清理未售卡密库存、规格、折扣等关联数据</span><br><span style="color:red;">此操作不可恢复！</span>', {
                    btn:['确认删除','取消'], icon:2, title:'彻底删除'
                }, function(idx){
                    layer.close(idx);
                    ajaxPost('permanent_delete', {ids:d.id}, function(){});
                });
                break;
        }
    });

    /* ---------- 复选框 → 按钮状态 ---------- */

    table.on('checkbox(recycle)', function(){
        var n = table.checkStatus(TABLE_ID).data.length;
        $('#toolbar-restore, #toolbar-permanent-del').toggleClass('layui-btn-disabled', n === 0);
    });

    window.addEventListener('resize', function(){
        setTimeout(resizeRecycleTable, 80);
    });
});
</script>