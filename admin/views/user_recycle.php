<?php
defined('DC_ROOT') || exit('access denied!');
$token = LoginAuth::genToken();
?>

<style>
.layui-table-tool-temp { padding-right: 0; }
td[data-field="avatar_url"] .layui-table-cell { overflow: visible; height: auto; line-height: normal; padding: 4px 0; }
</style>

<table class="layui-hide" id="recycle" lay-filter="recycle"></table>

<script type="text/html" id="recycle-toolbar">
<div style="display:flex;align-items:center;justify-content:center;position:relative;padding:10px 0 15px;border-bottom:1px solid #f0f0f0;">
    <span class="mac-dots-fs" title="点击放大/还原（Esc 退出）" style="position:absolute;left:0;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
        <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
        <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
        <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
    </span>
    <span style="color:#667797;font-size:14px;font-weight:500;">用户回收站</span>
</div>
<div style="display:flex;align-items:center;justify-content:space-between;width:100%;">
    <div class="layui-btn-container">
        <button class="layui-btn layui-btn-primary layui-border-green" lay-event="refresh"><i class="ri-refresh-line"></i></button>
        <button id="toolbar-restore" class="layui-btn layui-btn-sm layui-bg-blue layui-btn-disabled" lay-event="restore"><i class="ri-arrow-go-back-line"></i> 恢复</button>
        <button id="toolbar-permanent-del" class="layui-btn layui-bg-red layui-btn-disabled" lay-event="permanent_delete"><i class="ri-delete-bin-line"></i> 彻底删除</button>
        <button class="layui-btn layui-btn-sm layui-bg-orange" lay-event="empty_all"><i class="ri-delete-bin-7-line"></i> 清空回收站</button>
    </div>
    <form class="layui-form" style="display:flex;align-items:center;gap:6px;margin:0;flex-wrap:wrap;">
        <div class="layui-input-inline layui-input-wrap" style="width:120px;margin:0;">
            <select name="member_id">
                <option value="">会员等级</option>
                <?php foreach($member_list as $val): ?>
                <option value="<?= $val['id'] ?>"><?= $val['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="layui-input-inline layui-input-wrap" style="width:200px;margin:0;">
            <input type="text" name="keyword" placeholder="账号/昵称/手机/邮箱/ID/注册IP" lay-affix="clear" class="layui-input">
        </div>
        <button class="layui-btn layui-btn-sm" lay-submit lay-filter="recycle-search">搜索</button>
        <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" id="recycle-btn-reset">重置</button>
    </form>
</div>
</script>

<script type="text/html" id="avatarTpl">
<img src="{{ d.avatar_url }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover;vertical-align:middle;background:#f5f5f5;">
</script>

<script type="text/html" id="userInfoTpl">
<div>{{ d.name || d.login }}</div>
<div style="color:#999;font-size:12px;">{{ d.login }}</div>
</script>

<script type="text/html" id="contactTpl">
{{# if(d.email){ }}<div>{{ d.email }}</div>{{# } }}
{{# if(d.tel){ }}<div style="color:#999;font-size:12px;">{{ d.tel }}</div>{{# } }}
{{# if(!d.email && !d.tel){ }}<span style="color:#ccc;">-</span>{{# } }}
</script>

<script type="text/html" id="recycle-operate">
<a class="layui-btn layui-btn-xs layui-bg-blue" lay-event="restore">恢复</a>
<a class="layui-btn layui-btn-xs layui-bg-red" lay-event="permanent_delete">彻底删除</a>
</script>

<script>
layui.use(['table', 'form'], function(){
    var table = layui.table;
    var form = layui.form;
    var $ = layui.$;
    var TOKEN = '<?= $token ?>';
    var TABLE_ID = 'recycle';

    function ajaxPost(action, data){
        data.token = TOKEN;
        var loading = layer.load(2);
        $.ajax({
            url: '?action=' + action,
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function(e){
                layer.close(loading);
                if(e.code == 0){
                    var successMsg = (e.data && e.data.msg) ? e.data.msg : '用户回收站已更新';
                    layer.msg(successMsg, {icon:1});
                    table.reload(TABLE_ID);
                } else {
                    layer.msg(e.msg || '用户回收站处理失败', {icon:2});
                }
            },
            error: function(xhr){
                layer.close(loading);
                var msg = '用户回收站处理失败';
                try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e){}
                layer.msg(msg, {icon:2});
            }
        });
    }

    function getCheckedIds(){
        var rows = table.checkStatus(TABLE_ID).data;
        return rows.length ? rows.map(function(r){ return r.uid; }).join(',') : '';
    }

    var pageSize = parseInt(localStorage.getItem('user_recycle_limit')) || 10;

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
        lineStyle: 'height:42px',
        cols: [[
            {type:'checkbox', fixed:'left'},
            {field:'uid', title:'UID', width:80, align:'center', sort:true},
            {field:'avatar_url', title:'头像', width:70, align:'center', templet:'#avatarTpl'},
            {field:'name', title:'用户', minWidth:160, templet:'#userInfoTpl'},
            {field:'level_name', title:'会员等级', width:120, align:'center'},
            {field:'money', title:'余额', width:90, align:'center', sort:true},
            {field:'credits', title:'积分', width:80, align:'center', sort:true},
            {field:'email', title:'联系方式', width:180, templet:'#contactTpl'},
            {field:'reg_ip', title:'注册IP', width:130, align:'center'},
            {field:'create_time_text', title:'注册时间', width:140, align:'center'},
            {field:'delete_time_text', title:'删除时间', width:140, align:'center', sort:true},
            {fixed:'right', title:'操作', width:160, align:'center', templet:'#recycle-operate'}
        ]],
        done: function(res, curr){
            setTimeout(function(){
                var sel = document.querySelector('.layui-laypage-limits select');
                if(sel) sel.onchange = function(){
                    localStorage.setItem('user_recycle_limit', this.value);
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
        var $bar = $('.layui-table-tool-temp');
        $bar.find('input[name="keyword"]').val('');
        $bar.find('select[name="member_id"]').val('');
        form.render('select');
        table.reload(TABLE_ID, { page:{curr:1}, where:{member_id:'', keyword:''} });
    });

    table.on('toolbar(recycle)', function(obj){
        switch(obj.event){
            case 'refresh':
                table.reload(TABLE_ID);
                break;
            case 'restore':
                var ids = getCheckedIds();
                if(!ids) return layer.msg('请选择要恢复的用户');
                layer.confirm('确定要恢复选中的用户吗？', {btn:['确认恢复','取消'], icon:3, title:'恢复用户'}, function(idx){
                    layer.close(idx);
                    ajaxPost('restore', {ids:ids});
                });
                break;
            case 'permanent_delete':
                var ids = getCheckedIds();
                if(!ids) return layer.msg('请选择要删除的用户');
                layer.confirm('确定要彻底删除选中的用户吗？<br><span style="color:red;">此操作不可恢复！</span>', {btn:['确认删除','取消'], icon:2, title:'彻底删除'}, function(idx){
                    layer.close(idx);
                    ajaxPost('permanent_delete', {ids:ids});
                });
                break;
            case 'empty_all':
                var countdown = 5, timer = null;
                layer.open({
                    type:1,
                    title:'危险操作',
                    area:['400px','auto'],
                    content:'<div style="padding:20px;text-align:center;"><i class="layui-icon layui-icon-tips" style="font-size:48px;color:#ff9800;"></i><p style="margin:15px 0;">确定要清空用户回收站吗？</p><p style="color:#ff4d4f;">此操作将永久删除所有已删除用户，不可恢复！</p></div>',
                    btn:['确认清空 (5)','取消'],
                    yes:function(idx){ if(countdown>0)return; layer.close(idx); ajaxPost('empty_all',{}); },
                    btn2:function(){ clearInterval(timer); },
                    cancel:function(){ clearInterval(timer); },
                    success:function(layero){
                        var btn = layero.find('.layui-layer-btn0');
                        btn.css({'background':'#ccc','border-color':'#ccc','cursor':'not-allowed'});
                        timer = setInterval(function(){
                            countdown--;
                            if(countdown>0){ btn.text('确认清空 (' + countdown + ')'); }
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
                layer.confirm('确定要恢复这个用户吗？', {btn:['确认恢复','取消'], icon:3, title:'恢复用户'}, function(idx){
                    layer.close(idx);
                    ajaxPost('restore', {ids:d.uid});
                });
                break;
            case 'permanent_delete':
                layer.confirm('确定要彻底删除这个用户吗？<br><span style="color:red;">此操作不可恢复！</span>', {btn:['确认删除','取消'], icon:2, title:'彻底删除'}, function(idx){
                    layer.close(idx);
                    ajaxPost('permanent_delete', {ids:d.uid});
                });
                break;
        }
    });

    table.on('checkbox(recycle)', function(){
        var n = table.checkStatus(TABLE_ID).data.length;
        $('#toolbar-restore, #toolbar-permanent-del').toggleClass('layui-btn-disabled', n === 0);
    });
});
</script>
