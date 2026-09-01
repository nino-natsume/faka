<?php
defined('DC_ROOT') || exit('access denied!');
$token = LoginAuth::genToken();
?>

<style>
.layui-table-tool-temp { padding-right: 0; }
<?php if (Input::getIntVar('popup', 0)): ?>
body { padding-top: 10px; }
.layui-table-tool-temp { padding-top: 10px; }
<?php endif; ?>
</style>

<table class="layui-hide" id="recycle" lay-filter="recycle"></table>

<!-- 工具栏 -->
<script type="text/html" id="recycle-toolbar">
<?php if (Input::getIntVar('popup', 0) == 0): ?>
<div style="display:flex;align-items:center;justify-content:center;position:relative;padding:10px 0 15px;border-bottom:1px solid #f0f0f0;">
    <span class="mac-dots-fs" title="点击放大/还原（Esc 退出）" style="position:absolute;left:0;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
        <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
        <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
        <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
    </span>
    <span style="color:#667797;font-size:14px;font-weight:500;">发货记录回收站</span>
</div>
<?php endif; ?>
<div style="display:flex;align-items:center;justify-content:space-between;width:100%;">
    <div class="layui-btn-container">
        <button class="layui-btn layui-btn-primary layui-border-green" lay-event="refresh"><i class="ri-refresh-line"></i></button>
        <button id="toolbar-restore" class="layui-btn layui-btn-sm layui-bg-blue layui-btn-disabled" lay-event="restore"><i class="ri-arrow-go-back-line"></i> 恢复</button>
        <button id="toolbar-permanent-del" class="layui-btn  layui-bg-red layui-btn-disabled" lay-event="permanent_delete"><i class="ri-delete-bin-line"></i> 彻底删除</button>
        <button class="layui-btn layui-btn-sm layui-bg-orange" lay-event="empty_all"><i class="ri-delete-bin-7-line"></i> 清空回收站</button>
    </div>
    <form class="layui-form" style="display:flex;align-items:center;gap:6px;margin:0;">
        <div class="layui-input-inline layui-input-wrap" style="width:180px;margin:0;">
            <input type="text" name="keyword" placeholder="发货内容/商品名/ID" lay-affix="clear" class="layui-input">
        </div>
        <button class="layui-btn layui-btn-sm" lay-submit lay-filter="recycle-search">搜索</button>
        <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" id="recycle-btn-reset">重置</button>
    </form>
</div>
</script>

<!-- 操作 -->
<script type="text/html" id="recycle-operate">
<a class="layui-btn layui-btn-xs layui-bg-blue" lay-event="restore">恢复</a>
<a class="layui-btn layui-btn-xs layui-bg-red" lay-event="permanent_delete">彻底删除</a>
</script>

<!-- 内容 -->
<script type="text/html" id="recycle-content">
<div style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ d.content_short }}">{{ d.content_short }}</div>
</script>

<script>
layui.use(['table', 'form'], function(){
    var table    = layui.table;
    var form     = layui.form;
    var $        = layui.$;
    var TOKEN    = '<?= $token ?>';
    var TABLE_ID = 'recycle';

    function ajaxPost(action, data, onOk){
        data.token = TOKEN;
        var loading = layer.load(2);
        var successText = {restore:'发货模板已恢复', permanent_delete:'发货模板已彻底删除', empty_all:'发货模板回收站已清空'}[action] || '发货模板回收站已更新';
        var errorText = {restore:'发货模板恢复失败', permanent_delete:'发货模板彻底删除失败', empty_all:'发货模板回收站清空失败'}[action] || '发货模板回收站处理失败';
        $.ajax({
            url: '?action=' + action, type: 'POST', dataType: 'json', data: data,
            success: function(e){
                layer.close(loading);
                if(e.code == 0){
                    layer.msg(e.msg && e.msg !== 'ok' ? e.msg : successText, {icon:1});
                    table.reload(TABLE_ID);
                    if(typeof onOk === 'function') onOk(e);
                } else {
                    layer.msg(e.msg || errorText, {icon:2});
                }
            },
            error: function(xhr){
                layer.close(loading);
                var msg = errorText;
                try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e){}
                layer.msg(msg, {icon:2});
            }
        });
    }

    function getCheckedIds(){
        var rows = table.checkStatus(TABLE_ID).data;
        return rows.length ? rows.map(function(r){ return r.id; }).join(',') : '';
    }

    var pageSize = parseInt(localStorage.getItem('deliver_recycle_limit')) || 10;

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
            {type:'checkbox', fixed:'left'},
            {field:'id',                title:'ID',       width:70,  align:'center'},
            {field:'goods_title',       title:'商品',     width:180},
            {field:'content_short',     title:'发货内容', minWidth:200, templet:'#recycle-content'},
            {field:'sku',               title:'规格',     width:100, align:'center'},
            {field:'create_time_text',  title:'发货时间', width:140, align:'center'},
            {field:'delete_time_text',  title:'删除时间', width:140, align:'center'},
            {fixed:'right', title:'操作', width:160, align:'center', templet:'#recycle-operate'}
        ]],
        done: function(res, curr){
            setTimeout(function(){
                var sel = document.querySelector('.layui-laypage-limits select');
                if(sel) sel.onchange = function(){
                    localStorage.setItem('deliver_recycle_limit', this.value);
                    table.reload(TABLE_ID, { page:{curr:curr}, limit:parseInt(this.value) });
                };
            }, 0);
        }
    });

    form.on('submit(recycle-search)', function(data){
        table.reload(TABLE_ID, { page:{curr:1}, where:data.field });
        return false;
    });

    $(document).on('click', '#recycle-btn-reset', function(){
        $('.layui-table-tool-temp input[name="keyword"]').val('');
        table.reload(TABLE_ID, { page:{curr:1}, where:{keyword:''} });
    });

    table.on('toolbar(recycle)', function(obj){
        switch(obj.event){
            case 'refresh': table.reload(TABLE_ID); break;
            case 'restore':
                var ids = getCheckedIds();
                if(!ids) return layer.msg('请选择要恢复的记录');
                layer.confirm('确定要恢复选中的发货记录吗？', {btn:['确认恢复','取消'], icon:3, title:'恢复记录'}, function(idx){
                    layer.close(idx); ajaxPost('restore', {ids:ids});
                });
                break;
            case 'permanent_delete':
                var ids = getCheckedIds();
                if(!ids) return layer.msg('请选择要删除的记录');
                layer.confirm('确定要彻底删除选中的发货记录吗？<br><span style="color:red;">此操作不可恢复！</span>', {
                    btn:['确认删除','取消'], icon:2, title:'彻底删除'
                }, function(idx){ layer.close(idx); ajaxPost('permanent_delete', {ids:ids}); });
                break;
            case 'empty_all':
                var countdown = 5, timer = null;
                layer.open({
                    type:1, title:'危险操作', area:['400px','auto'],
                    content:'<div style="padding:20px;text-align:center;"><i class="layui-icon layui-icon-tips" style="font-size:48px;color:#ff9800;"></i><p style="margin:15px 0;">确定要清空发货记录回收站吗？</p><p style="color:#ff4d4f;">此操作不可恢复！</p></div>',
                    btn:['确认清空 (5)','取消'],
                    yes: function(idx){ if(countdown>0)return; layer.close(idx); ajaxPost('empty_all',{}); },
                    btn2: function(){ clearInterval(timer); },
                    cancel: function(){ clearInterval(timer); },
                    success: function(layero){
                        var btn = layero.find('.layui-layer-btn0');
                        btn.css({'background':'#ccc','border-color':'#ccc','cursor':'not-allowed'});
                        timer = setInterval(function(){
                            countdown--;
                            if(countdown>0) btn.text('确认清空 ('+countdown+')');
                            else { clearInterval(timer); btn.text('确认清空').css({'background':'#ff5722','border-color':'#ff5722','cursor':'pointer'}); }
                        }, 1000);
                    }
                });
                break;
        }
    });

    table.on('tool(recycle)', function(obj){
        var d = obj.data;
        switch(obj.event){
            case 'restore':
                layer.confirm('确定要恢复这条记录吗？', {btn:['确认恢复','取消'], icon:3, title:'恢复'}, function(idx){
                    layer.close(idx); ajaxPost('restore', {ids:d.id});
                });
                break;
            case 'permanent_delete':
                layer.confirm('确定要彻底删除这条记录吗？<br><span style="color:red;">此操作不可恢复！</span>', {
                    btn:['确认删除','取消'], icon:2, title:'彻底删除'
                }, function(idx){ layer.close(idx); ajaxPost('permanent_delete', {ids:d.id}); });
                break;
        }
    });

    table.on('checkbox(recycle)', function(){
        var n = table.checkStatus(TABLE_ID).data.length;
        $('#toolbar-restore, #toolbar-permanent-del').toggleClass('layui-btn-disabled', n === 0);
    });
});
</script>
