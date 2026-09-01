<?php defined('DC_ROOT') || exit('access denied!'); ?>

<table class="layui-hide" id="commentTable" lay-filter="commentTable"></table>

<!-- toolbar -->
<script type="text/html" id="toolbar">
    <div style="display:flex;align-items:center;justify-content:center;position:relative;padding:10px 0px 15px;border-bottom:1px solid #f0f0f0;">
        <span style="position:absolute;left:0;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">评论管理</span>
    </div>
    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
        <div class="layui-btn-container">
            <button class="layui-btn layui-btn-primary layui-border-green" lay-event="refresh"><i class="ri-refresh-line"></i></button>
            <?php if (User::haveEditPermission()): ?>
            <button id="toolbar-pub" class="layui-btn layui-btn-normal layui-btn-disabled" lay-event="pub">审核</button>
            <button id="toolbar-hide" class="layui-btn layui-btn-primary layui-btn-disabled" lay-event="hide">隐藏</button>
            <button id="toolbar-top" class="layui-btn layui-btn-warm layui-btn-disabled" lay-event="top">置顶</button>
            <button id="toolbar-untop" class="layui-btn layui-btn-primary layui-btn-disabled" lay-event="untop">取消置顶</button>
            <?php endif; ?>
            <button id="toolbar-del" class="layui-btn layui-bg-red layui-btn-disabled" lay-event="del">删除</button>
        </div>
        <form class="layui-form" style="display: flex; align-items: center; gap: 6px; margin: 0; flex-wrap: wrap; justify-content: flex-end;">
            <div class="layui-input-inline" style="width: 110px; margin: 0;">
                <select name="hide">
                    <option value="">全部状态</option>
                    <option value="n">已公开</option>
                    <option value="y">待审核</option>
                </select>
            </div>
            <div class="layui-input-inline layui-input-wrap" style="width: 200px; margin: 0;">
                <input type="text" value="" name="keyword" placeholder="搜索内容、评论人、IP..." lay-affix="clear" class="layui-input">
            </div>
            <button class="layui-btn layui-btn-sm" lay-submit lay-filter="comment-search">搜索</button>
            <button type="reset" class="layui-btn layui-btn-sm layui-btn-primary">重置</button>
        </form>
    </div>
</script>

<!-- content column template -->
<script type="text/html" id="contentTpl">
    <div style="max-height:60px;overflow:hidden;line-height:1.5;">{{ d.comment }}</div>
    <div style="margin-top:4px;">
        {{# if(d.hide === 'y'){ }}
            <span class="layui-badge">待审</span>
        {{# } else { }}
            <span class="layui-badge layui-bg-green">公开</span>
        {{# } }}
        {{# if(d.top === 'y'){ }}
            <span class="layui-badge layui-bg-orange">置顶</span>
        {{# } }}
    </div>
</script>

<!-- poster column template -->
<script type="text/html" id="posterTpl">
    <div>
        {{# if(d.uid > 0){ }}
            <a href="comment.php?uid={{ d.uid }}" style="color:#1E9FFF;">{{ d.poster }}</a>
        {{# } else { }}
            <span>{{ d.poster }}</span>
        {{# } }}
    </div>
    {{# if(d.mail){ }}
        <div style="color:#999;font-size:12px;">{{ d.mail }}</div>
    {{# } }}
    {{# if(d.ip){ }}
        <div style="color:#999;font-size:12px;">IP: {{ d.ip }}</div>
    {{# } }}
    <div style="color:#bbb;font-size:11px;">{{ d.os }} {{ d.browse }}</div>
</script>

<!-- article column template -->
<script type="text/html" id="articleTpl">
    <div>
        <a href="{{ d.log_url }}" target="_blank" style="color:#333;">{{ d.title }}</a>
    </div>
    <div style="margin-top:2px;">
        <a href="comment.php?gid={{ d.gid }}" class="layui-btn layui-btn-primary layui-btn-xs">该文评论</a>
    </div>
</script>

<!-- operate column template -->
<script type="text/html" id="operateTpl">
    <div class="layui-clear-space">
        <a class="layui-btn layui-btn-xs" lay-event="reply">回复</a>
        <?php if (User::haveEditPermission()): ?>
        {{# if(d.hide === 'y'){ }}
            <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="pub">审核</a>
        {{# } else { }}
            <a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="hide">隐藏</a>
        {{# } }}
        {{# if(d.top === 'y'){ }}
            <a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="untop">取消置顶</a>
        {{# } else { }}
            <a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="top">置顶</a>
        {{# } }}
        {{# if(d.ip){ }}
            <a class="layui-btn layui-bg-orange layui-btn-xs" lay-event="delbyip">按IP删</a>
        {{# } }}
        <?php endif; ?>
        <a class="layui-btn layui-bg-red layui-btn-xs" lay-event="del">删除</a>
    </div>
</script>

<script>
layui.use(['table', 'form', 'layer'], function(){
    var table = layui.table;
    var form = layui.form;
    var layer = layui.layer;
    var token = '<?= LoginAuth::genToken() ?>';

    // render table
    window.commentTableInst = table.render({
        elem: '#commentTable',
        url: '?action=index',
        toolbar: '#toolbar',
        page: true,
        limits: [10, 20, 30, 50, 100],
        defaultToolbar: [],
        lineStyle: 'height: 20px;',
        cols: [[
            {type: 'checkbox', fixed: 'left'},
            {field: 'comment', title: '内容', minWidth: 220, templet: '#contentTpl'},
            {field: 'poster', title: '评论人', width: 160, templet: '#posterTpl'},
            {field: 'title', title: '来自文章', width: 180, templet: '#articleTpl'},
            {field: 'date', title: '时间', width: 130},
            {fixed: 'right', title: '操作', templet: '#operateTpl', width: 280, align: 'center'}
        ]],
        error: function(res, msg){
            console.log(res, msg);
        },
        done: function(){
            form.render();
        }
    });

    // search
    form.on('submit(comment-search)', function(data){
        table.reload('commentTable', {
            page: { curr: 1 },
            where: data.field
        });
        return false;
    });

    // helper: batch AJAX
    function batchAjax(operate, ids, confirmMsg, callback) {
        var doIt = function(){
            $.ajax({
                url: '?action=batch',
                type: 'POST',
                dataType: 'json',
                data: { operate: operate, ids: ids, token: token },
                success: function(res){
                    if(res.code !== 0){ return layer.msg(res.msg); }
                    layer.msg(res.data || '操作成功');
                    table.reload('commentTable');
                    if(callback) callback();
                },
                error: function(err){
                    layer.msg('操作失败');
                }
            });
        };
        if(confirmMsg){
            layer.confirm(confirmMsg, { btn: ['确认','取消'], icon: 3, title: '温馨提示' }, function(idx){
                layer.close(idx);
                doIt();
            });
        } else {
            doIt();
        }
    }

    // toolbar events
    table.on('toolbar(commentTable)', function(obj){
        var id = obj.config.id;
        var checkStatus = table.checkStatus(id);
        if(obj.event === 'refresh'){
            table.reload(id);
            return;
        }
        var data = checkStatus.data;
        if(data.length === 0){
            layer.msg('请先选择评论');
            return;
        }
        var ids = $.map(data, function(item){ return item.cid; }).join(',');

        switch(obj.event){
            case 'del':   batchAjax('del', ids, '确定删除所选评论？'); break;
            case 'pub':   batchAjax('pub', ids); break;
            case 'hide':  batchAjax('hide', ids); break;
            case 'top':   batchAjax('top', ids); break;
            case 'untop': batchAjax('untop', ids); break;
        }
    });

    // row tool events
    table.on('tool(commentTable)', function(obj){
        var data = obj.data;
        var id = obj.config.id;

        if(obj.event === 'del'){
            layer.confirm('确定删除该评论？', { btn: ['确认','取消'], icon: 3, title: '温馨提示' }, function(idx){
                layer.close(idx);
                batchAjax('del', String(data.cid));
            });
            return;
        }
        if(obj.event === 'pub'){
            batchAjax('pub', String(data.cid));
            return;
        }
        if(obj.event === 'hide'){
            batchAjax('hide', String(data.cid));
            return;
        }
        if(obj.event === 'top'){
            batchAjax('top', String(data.cid));
            return;
        }
        if(obj.event === 'untop'){
            batchAjax('untop', String(data.cid));
            return;
        }
        if(obj.event === 'delbyip'){
            layer.confirm('确定删除该IP（' + data.ip + '）的所有评论？', { btn: ['确认','取消'], icon: 3, title: '温馨提示' }, function(idx){
                layer.close(idx);
                $.ajax({
                    url: '?action=ajaxdelbyip',
                    type: 'POST',
                    dataType: 'json',
                    data: { ip: data.ip, token: token },
                    success: function(res){
                        if(res.code !== 0){ return layer.msg(res.msg); }
                        layer.msg(res.data || '删除成功');
                        table.reload(id);
                    },
                    error: function(){ layer.msg('操作失败'); }
                });
            });
            return;
        }
        if(obj.event === 'reply'){
            layer.open({
                type: 1,
                title: '回复评论',
                area: ['500px', 'auto'],
                skin: 'dc-layer-modern',
                content: '<div style="padding:20px;">'
                    + '<div style="background:#f8f9fa;padding:12px;border-radius:8px;margin-bottom:16px;max-height:120px;overflow:auto;color:#666;font-size:13px;line-height:1.6;">' + (data.comment || '') + '</div>'
                    + '<textarea id="replyContent" class="layui-textarea" placeholder="输入回复内容..." style="min-height:100px;"></textarea>'
                    + '</div>',
                btn: ['回复', '取消'],
                yes: function(idx){
                    var replyText = $.trim($('#replyContent').val());
                    if(!replyText){
                        layer.msg('回复内容不能为空');
                        return;
                    }
                    $.ajax({
                        url: '?action=ajaxreply',
                        type: 'POST',
                        dataType: 'json',
                        data: { cid: data.cid, hide: data.hide, reply: replyText, token: token },
                        success: function(res){
                            if(res.code !== 0){ return layer.msg(res.msg); }
                            layer.close(idx);
                            layer.msg(res.data || '回复成功');
                            table.reload(id);
                        },
                        error: function(){ layer.msg('操作失败'); }
                    });
                }
            });
            return;
        }
    });

    // checkbox toggle toolbar buttons
    table.on('checkbox(commentTable)', function(obj){
        var id = obj.config.id;
        var checkData = table.checkStatus(id).data;
        var btns = ['#toolbar-del', '#toolbar-pub', '#toolbar-hide', '#toolbar-top', '#toolbar-untop'];
        if(checkData.length === 0){
            $.each(btns, function(i, sel){ $(sel).addClass('layui-btn-disabled'); });
        } else {
            $.each(btns, function(i, sel){ $(sel).removeClass('layui-btn-disabled'); });
        }
    });
});
</script>

<script>
$(function(){
    $("#menu-blog-comment").addClass('active');
});
</script>
