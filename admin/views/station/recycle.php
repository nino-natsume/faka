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
    <span style="color:#667797;font-size:14px;font-weight:500;">分店回收站</span>
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
        <div class="layui-input-inline layui-input-wrap" style="width:120px;margin:0;">
            <select name="level_id">
                <option value="">分店等级</option>
                <?php foreach ($levels as $lv): ?>
                <option value="<?= $lv['id'] ?>"><?= htmlspecialchars($lv['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="layui-input-inline layui-input-wrap" style="width:160px;margin:0;">
            <input type="text" name="keyword" placeholder="名称/域名/用户名/ID" lay-affix="clear" class="layui-input">
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

<!-- 用户信息 -->
<script type="text/html" id="recycle-user">
<div>{{ d.nickname || d.username }}</div>
<div style="color:#999;font-size:12px;">UID: {{ d.user_id }}</div>
</script>

<!-- 域名 -->
<script type="text/html" id="recycle-domain">
{{# if(d.domain){ }}<div>{{ d.domain }}</div>{{# } }}
{{# if(d.domain_2){ }}<div style="color:#999;font-size:12px;">{{ d.domain_2 }}</div>{{# } }}
{{# if(!d.domain && !d.domain_2){ }}<span style="color:#ccc;">未绑定</span>{{# } }}
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
        $.ajax({
            url: '?action=' + action, type: 'POST', dataType: 'json', data: data,
            success: function(e){
                layer.close(loading);
                if(e.code == 0){
                    layer.msg(e.msg || '操作成功', {icon:1});
                    table.reload(TABLE_ID);
                    if(typeof onOk === 'function') onOk(e);
                } else {
                    layer.msg(e.msg || '操作失败', {icon:2});
                }
            },
            error: function(xhr){
                layer.close(loading);
                var msg = '操作失败';
                try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e){}
                layer.msg(msg, {icon:2});
            }
        });
    }

    function getCheckedIds(){
        var rows = table.checkStatus(TABLE_ID).data;
        return rows.length ? rows.map(function(r){ return r.id; }).join(',') : '';
    }

    var pageSize = parseInt(localStorage.getItem('station_recycle_limit')) || 10;

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
            {field:'id',                title:'ID',       width:60,  align:'center'},
            {field:'name',              title:'店铺名称', minWidth:130},
            {field:'user_id',           title:'用户',     width:130, templet:'#recycle-user'},
            {field:'level_name',        title:'分店等级', width:110, align:'center'},
            {field:'domain',            title:'域名',     width:180, templet:'#recycle-domain'},
            {field:'create_time_text',  title:'开通时间', width:140, align:'center'},
            {field:'delete_time_text',  title:'删除时间', width:140, align:'center'},
            {fixed:'right', title:'操作', width:160, align:'center', templet:'#recycle-operate'}
        ]],
        done: function(res, curr){
            setTimeout(function(){
                var sel = document.querySelector('.layui-laypage-limits select');
                if(sel) sel.onchange = function(){
                    localStorage.setItem('station_recycle_limit', this.value);
                    table.reload(TABLE_ID, { page:{curr:curr}, limit:parseInt(this.value) });
                };
            }, 0);
        }
    });

    /* 搜索 & 重置 */
    form.on('submit(recycle-search)', function(data){
        table.reload(TABLE_ID, { page:{curr:1}, where:data.field });
        return false;
    });

    $(document).on('click', '#recycle-btn-reset', function(){
        var $bar = $('.layui-table-tool-temp');
        $bar.find('input[name="keyword"]').val('');
        $bar.find('select[name="level_id"]').val('');
        form.render('select');
        table.reload(TABLE_ID, { page:{curr:1}, where:{level_id:'', keyword:''} });
    });

    /* 工具栏按钮 */
    table.on('toolbar(recycle)', function(obj){
        switch(obj.event){
            case 'refresh':
                table.reload(TABLE_ID);
                break;
            case 'restore':
                var ids = getCheckedIds();
                if(!ids) return layer.msg('请选择要恢复的分店');
                layer.confirm('确定要恢复选中的分店吗？', {btn:['确认恢复','取消'], icon:3, title:'恢复分店'}, function(idx){
                    layer.close(idx);
                    ajaxPost('restore', {ids:ids});
                });
                break;
            case 'permanent_delete':
                var ids = getCheckedIds();
                if(!ids) return layer.msg('请选择要删除的分店');
                layer.confirm('确定要彻底删除选中的分店吗？<br><span style="color:red;">此操作不可恢复！</span>', {
                    btn:['确认删除','取消'], icon:2, title:'彻底删除'
                }, function(idx){
                    layer.close(idx);
                    ajaxPost('permanent_delete', {ids:ids});
                });
                break;
            case 'empty_all':
                var countdown = 5, timer = null;
                var emptyIdx = layer.open({
                    type:1, title:'危险操作', area:['400px','auto'],
                    content: '<div style="padding:20px;text-align:center;"><i class="layui-icon layui-icon-tips" style="font-size:48px;color:#ff9800;"></i><p style="margin:15px 0;">确定要清空分店回收站吗？</p><p style="color:#ff4d4f;">此操作将永久删除所有已删除的分店，不可恢复！</p></div>',
                    btn:['确认清空 (5)','取消'],
                    yes: function(idx){ if(countdown>0)return; layer.close(idx); ajaxPost('empty_all',{}); },
                    btn2: function(){ clearInterval(timer); },
                    cancel: function(){ clearInterval(timer); },
                    success: function(layero){
                        var btn = layero.find('.layui-layer-btn0');
                        btn.css({'background':'#ccc','border-color':'#ccc','cursor':'not-allowed'});
                        timer = setInterval(function(){
                            countdown--;
                            if(countdown>0){ btn.text('确认清空 ('+countdown+')'); }
                            else { clearInterval(timer); btn.text('确认清空').css({'background':'#ff5722','border-color':'#ff5722','cursor':'pointer'}); }
                        }, 1000);
                    }
                });
                break;
        }
    });

    /* 单行操作 */
    table.on('tool(recycle)', function(obj){
        var d = obj.data;
        switch(obj.event){
            case 'restore':
                layer.confirm('确定要恢复这个分店吗？', {btn:['确认恢复','取消'], icon:3, title:'恢复分店'}, function(idx){
                    layer.close(idx);
                    ajaxPost('restore', {ids:d.id});
                });
                break;
            case 'permanent_delete':
                layer.confirm('确定要彻底删除这个分店吗？<br><span style="color:red;">此操作不可恢复！</span>', {
                    btn:['确认删除','取消'], icon:2, title:'彻底删除'
                }, function(idx){
                    layer.close(idx);
                    ajaxPost('permanent_delete', {ids:d.id});
                });
                break;
        }
    });

    /* 复选框 → 按钮状态 */
    table.on('checkbox(recycle)', function(){
        var n = table.checkStatus(TABLE_ID).data.length;
        $('#toolbar-restore, #toolbar-permanent-del').toggleClass('layui-btn-disabled', n === 0);
    });
});
</script>
