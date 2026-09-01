<?php
defined('DC_ROOT') || exit('access denied!');
?>

<style>body { padding-top: 10px; }</style>
<!-- 订单状态Tab -->
<div class="layui-tabs order-tabs-wrapper" lay-options="{trigger: false}">
    <ul class="layui-tabs-header">
        <li class="layui-this order-tab" data-status=""><a>全部</a></li>
        <li class="order-tab" data-status="0"><a>待付款</a></li>
        <li class="order-tab" data-status="1"><a>待发货</a></li>
        <li class="order-tab" data-status="4"><a>待收货</a></li>
        <li class="order-tab" data-status="2"><a>已完成</a></li>
        <li class="order-tab" data-status="refunding"><a>退款中</a></li>
        <li class="order-tab" data-status="3"><a>已取消</a></li>
    </ul>
</div>

<style>.layui-table-tool-temp { padding-right: 0; }</style>
<table class="layui-hide" id="index" lay-filter="index"></table>

<script type="text/html" id="toolbar">
    <div style="display:flex;align-items:center;justify-content:center;position:relative;padding:10px 0px 15px;border-bottom:1px solid #f0f0f0;">
        <span style="position:absolute;left:0;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">订单回收站</span>
    </div>
    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
        <div class="layui-btn-container">
            <button class="layui-btn layui-btn-primary layui-border-green" lay-event="refresh"><i class="ri-refresh-line"></i></button>
            <button id="toolbar-restore" class="layui-btn layui-btn-sm layui-btn-normal layui-btn-disabled" lay-event="restore">
                <i class="ri-arrow-go-back-line"></i> 恢复
            </button>
            <button id="toolbar-del" class="layui-btn  layui-bg-red layui-btn-disabled" lay-event="del">
                <i class="ri-delete-bin-line"></i> 永久删除
            </button>
            <button class="layui-btn layui-btn-sm layui-bg-orange" lay-event="empty">
                <i class="ri-delete-bin-7-line"></i> 清空回收站
            </button>
        </div>
        <form class="layui-form" style="display: flex; align-items: center; gap: 6px; margin: 0; flex-wrap: wrap;">
            <div class="layui-input-inline layui-input-wrap" style="width: 170px; margin: 0;">
                <input type="text" value="" name="email_username" placeholder="用户邮箱/昵称/手机" lay-affix="clear" class="layui-input">
            </div>
            <div class="layui-input-inline layui-input-wrap" style="width: 130px; margin: 0;">
                <input type="text" value="" name="out_trade_no" placeholder="订单号" lay-affix="clear" class="layui-input">
            </div>
            <div class="layui-input-inline layui-input-wrap" style="width: 130px; margin: 0;">
                <input type="text" value="" name="goods_title" placeholder="商品名" lay-affix="clear" class="layui-input">
            </div>
            <div class="layui-input-inline layui-input-wrap" style="width: 130px; margin: 0;">
                <input type="text" value="" name="order_required" placeholder="下单必填项" lay-affix="clear" class="layui-input">
            </div>
            <div class="layui-input-inline layui-input-wrap" style="width: 130px; margin: 0;">
                <input type="text" value="" name="kami_content" placeholder="卡密反查" lay-affix="clear" class="layui-input">
            </div>
            <button class="layui-btn layui-btn-sm" lay-submit lay-filter="index-search">搜索</button>
            <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" id="btn-reset">重置</button>
        </form>
    </div>
</script>

<script type="text/html" id="goodsTpl">
    <div class="goods-info">
        {{# layui.each(d.list, function(index, item){ }}
        <div style="{{# if(index > 0){ }}margin-top:5px;padding-top:5px;border-top:1px dashed #eee;{{# } }}">
            {{# if(item.attr_spec && item.attr_spec.indexOf('[赠品]') > -1){ }}
            <span style="background:#52c41a;color:#fff;padding:1px 4px;border-radius:2px;font-size:11px;margin-right:3px;">赠</span>
            {{# } }}
            <div class="row-1-hidden">{{ item.title }}</div>
            <div style="color:#999;font-size:12px;">{{ item.attr_spec ? item.attr_spec.replace('[赠品] ', '') : '默认规格' }}</div>
        </div>
        {{# }); }}
    </div>
</script>

<script type="text/html" id="quantityTpl">
    <div class="goods-info">
        {{# layui.each(d.list, function(index, item){ }}
        <div style="{{# if(index > 0){ }}margin-top:5px;padding-top:5px;{{# } }}">{{ item.quantity }}</div>
        {{# }); }}
    </div>
</script>

<script type="text/html" id="userInfoTpl">
    <div class="goods-info">
        <div>{{ d.user_nickname }}</div>
        {{# if(d.user_id == 0){ }}
        <div>游客身份</div>
        {{# } }}
        {{# if(d.user_email != ''){ }}
        <div>{{ d.user_email }}</div>
        {{# } }}
        {{# if(d.user_tel != ''){ }}
        <div>{{ d.user_tel }}</div>
        {{# } }}
    </div>
</script>

<script type="text/html" id="statusTpl">
    {{# if(d.status_text == '已完成'){ }}
    <span style="color:#52c41a;font-weight:500;">{{ d.status_text }}</span>
    {{# } else if(d.status_text == '未支付' || d.status_text == '待付款'){ }}
    <span style="color:#ff4d4f;font-weight:500;">{{ d.status_text }}</span>
    {{# } else if(d.status_text == '待收货'){ }}
    <span style="color:#1677ff;font-weight:500;">{{ d.status_text }}</span>
    {{# } else if(d.status_text == '退款中'){ }}
    <span style="color:#f5222d;font-weight:500;">{{ d.status_text }}</span>
    {{# } else if(d.status_text == '已取消'){ }}
    <span style="color:#999;font-weight:500;">{{ d.status_text }}</span>
    {{# } else { }}
    <span style="color:#fa8c16;font-weight:500;">{{ d.status_text }}</span>
    {{# } }}
</script>

<script type="text/html" id="operate">
    <div class="layui-clear-space">
        <a class="layui-btn" lay-event="detail">详情</a>
        <a class="layui-btn layui-btn-normal" lay-event="restore">恢复</a>
        <a class="layui-btn layui-bg-red" lay-event="del">删除</a>
    </div>
</script>

<script>
layui.use(['table', 'form'], function(){
    var table = layui.table;
    var form = layui.form;
    var $ = layui.$;
    
    var currentStatus = '';
    
    window.recycleTable = table.render({
        elem: '#index',
        autoSort: false,
        url: '?action=index',
        toolbar: '#toolbar',
        limits: [10, 20, 30, 50, 100],
        lineStyle: 'height: 69px;',
        page: true,
        defaultToolbar: [],
        cols: [[
            {type: 'checkbox', fixed: 'left'},
            {field: 'out_trade_no', title: '订单号', width: 173},
            {field: 'goods', title: '商品信息', templet: '#goodsTpl', minWidth: 232, maxWidth: 500},
            {field: 'id', title: '数量', width: 50, templet: '#quantityTpl'},
            {field: 'amount', title: '订单金额', width: 100},
            {field: 'user_email', title: '用户信息', width: 120, templet: '#userInfoTpl'},
            {field: 'status_text', title: '订单状态', width: 90, templet: '#statusTpl'},
            {field: 'pay_time', title: '支付时间', width: 160},
            {field: 'delete_time_text', title: '删除时间', width: 160},
            {fixed: 'right', title: '操作', templet: '#operate', minWidth: 210}
        ]],
        error: function(res, msg){
            console.log(res, msg);
        }
    });
    
    $('.order-tab').on('click', function(){
        var status = $(this).data('status');
        currentStatus = status;
        $('.order-tab').removeClass('layui-this');
        $(this).addClass('layui-this');
        table.reload('index', {
            page: { curr: 1 },
            where: {
                status: status,
                email_username: $('input[name="email_username"]').val(),
                out_trade_no: $('input[name="out_trade_no"]').val(),
                goods_title: $('input[name="goods_title"]').val(),
                client_ip: $('input[name="client_ip"]').val(),
                order_required: $('input[name="order_required"]').val(),
                kami_content: $('input[name="kami_content"]').val()
            }
        });
    });

    form.on('submit(index-search)', function(data){
        var field = data.field;
        field.status = currentStatus;
        table.reload('index', { page: { curr: 1 }, where: field });
        return false;
    });

    $(document).on('click', '#btn-reset', function(){
        $('.layui-table-tool-temp input').val('');
        table.reload('index', { page: {curr: 1}, where: {email_username: '', out_trade_no: '', goods_title: '', order_required: '', kami_content: '', status: currentStatus} });
    });

    table.on('toolbar(index)', function(obj){
        var id = obj.config.id;
        var checkStatus = table.checkStatus(id);
        
        switch(obj.event){
            case 'refresh':
                table.reload(id);
                break;
                
            case 'restore':
                var data = checkStatus.data;
                if(data.length == 0) break;
                var ids = $.map(data, function(item) { return item.id; }).join(',');
                layer.confirm('确定要恢复选中的 ' + data.length + ' 条订单吗？', {
                    btn: ['确认', '取消'], icon: 3, title: '确认恢复'
                }, function(index) {
                    layer.close(index);
                    $.ajax({
                        url: '?action=restore', type: 'POST', dataType: 'json',
                        data: { ids: ids, token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e) {
                            if(e.code == 400) return layer.msg(e.msg);
                            layer.msg('恢复成功'); table.reload(id);
                        },
                        error: function(err) { layer.msg(err.responseJSON ? err.responseJSON.msg : '操作失败'); }
                    });
                });
                break;
                
            case 'del':
                var data = checkStatus.data;
                if(data.length == 0) break;
                var ids = $.map(data, function(item) { return item.id; }).join(',');
                layer.confirm('确定要永久删除的 ' + data.length + ' 条订单吗？<br><span style="color:#ff4d4f;">此操作不可恢复！</span>', {
                    btn: ['确认删除', '取消'], icon: 3, title: '危险操作'
                }, function(index) {
                    layer.close(index);
                    $.ajax({
                        url: '?action=delete', type: 'POST', dataType: 'json',
                        data: { ids: ids, token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e) {
                            if(e.code == 400) return layer.msg(e.msg);
                            layer.msg('订单已彻底删除'); table.reload(id);
                        },
                        error: function(err) { layer.msg(err.responseJSON ? err.responseJSON.msg : '操作失败'); }
                    });
                });
                break;
                
            case 'empty':
                var emptyCountdown = 5, emptyTimer = null;
                layer.open({
                    type: 1, title: '危险操作', area: ['400px', 'auto'],
                    content: '<div style="padding:20px;text-align:center;"><i class="layui-icon layui-icon-tips" style="font-size:48px;color:#ff9800;"></i><p style="margin:15px 0;">确定要清空回收站吗？</p><p style="color:#ff4d4f;">此操作将永久删除所有已删除的订单，不可恢复！</p></div>',
                    btn: ['确认清空 (5)', '取消'],
                    yes: function(index) {
                        if (emptyCountdown > 0) return;
                        layer.close(index);
                        $.ajax({
                            url: '?action=empty', type: 'POST', dataType: 'json',
                            data: { token: '<?= LoginAuth::genToken() ?>' },
                            success: function(e) {
                                if(e.code == 400) return layer.msg(e.msg);
                                layer.msg('订单已彻底删除');
                                table.reload(id);
                            },
                            error: function(err) { layer.msg(err.responseJSON ? err.responseJSON.msg : '操作失败'); }
                        });
                    },
                    btn2: function() { clearInterval(emptyTimer); },
                    cancel: function() { clearInterval(emptyTimer); },
                    success: function(layero) {
                        var btn = layero.find('.layui-layer-btn0');
                        btn.css({'background': '#ccc', 'border-color': '#ccc', 'cursor': 'not-allowed'});
                        emptyTimer = setInterval(function() {
                            emptyCountdown--;
                            if (emptyCountdown > 0) { btn.text('确认清空 (' + emptyCountdown + ')'); }
                            else { clearInterval(emptyTimer); btn.text('确认清空').css({'background': '#ff5722', 'border-color': '#ff5722', 'cursor': 'pointer'}); }
                        }, 1000);
                    }
                });
                break;
        }
    });

    table.on('tool(index)', function(obj){
        var data = obj.data;
        var id = obj.config.id;
        
        if(obj.event === 'detail'){
            let isMobile = window.innerWidth < 768;
            let area = isMobile ? ['98%', 'auto'] : ['800px', 'auto'];
            layer.open({
                id: 'detail', title: '订单详情', type: 2, area: area,
                scrollbar: false, skin: 'dc-layer-modern',
                content: 'order.php?action=detail&order_id=' + data.id,
                fixed: false, maxmin: true, shadeClose: true,
                success: function(layero, index, that){ layer.iframeAuto(index); that.offset(); }
            });
        }
        
        if(obj.event == 'restore'){
            layer.confirm('确定要恢复这条订单吗？', { btn: ['确认', '取消'], icon: 3, title: '确认恢复' }, function(index) {
                layer.close(index);
                $.ajax({
                    url: '?action=restore', type: 'POST', dataType: 'json',
                    data: { ids: data.id, token: '<?= LoginAuth::genToken() ?>' },
                    success: function(e) { if(e.code == 400) return layer.msg(e.msg); layer.msg('恢复成功'); table.reload(id); },
                    error: function(err) { layer.msg(err.responseJSON ? err.responseJSON.msg : '操作失败'); }
                });
            });
        }
        
        if(obj.event == 'del'){
            layer.confirm('确定要永久删除这条订单吗？<br><span style="color:#ff4d4f;">此操作不可恢复！</span>', {
                btn: ['确认删除', '取消'], icon: 3, title: '危险操作'
            }, function(index) {
                layer.close(index);
                $.ajax({
                    url: '?action=delete', type: 'POST', dataType: 'json',
                    data: { ids: data.id, token: '<?= LoginAuth::genToken() ?>' },
                    success: function(e) { if(e.code == 400) return layer.msg(e.msg); layer.msg('订单已彻底删除'); table.reload(id); },
                    error: function(err) { layer.msg(err.responseJSON ? err.responseJSON.msg : '操作失败'); }
                });
            });
        }
    });

    table.on('checkbox(index)', function(obj){
        var id = obj.config.id;
        var checkData = table.checkStatus(id).data;
        if(checkData.length == 0){
            $('#toolbar-restore').addClass('layui-btn-disabled');
            $('#toolbar-del').addClass('layui-btn-disabled');
        } else {
            $('#toolbar-restore').removeClass('layui-btn-disabled');
            $('#toolbar-del').removeClass('layui-btn-disabled');
        }
    });
});
</script>
