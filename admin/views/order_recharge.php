<?php
defined('DC_ROOT') || exit('access denied!');

// 获取主题配色
$activePlugins = Option::get('active_plugins') ?: [];
$isAdminColorActive = in_array('admin_color/admin_color.php', $activePlugins);

if ($isAdminColorActive) {
    $colorStorage = Storage::getInstance('admin_color');
    $tplPrimaryColor = $colorStorage->getValue('primary_color') ?: '#16b777';
    $tplPrimaryColorDark = $colorStorage->getValue('primary_color_dark') ?: '#4C7D71';
    $tplPrimarySolid = (strpos($tplPrimaryColor, 'gradient') !== false) ? $tplPrimaryColorDark : $tplPrimaryColor;
} else {
    $tplPrimaryColor = '#667eea';
    $tplPrimaryColorDark = '#764ba2';
    $tplPrimarySolid = '#667eea';
}
?>

<!-- 订单状态Tab -->
<div class="layui-tabs order-tabs-wrapper" lay-options="{trigger: false}">
    <ul class="layui-tabs-header">
        <li class="layui-this order-tab" data-status=""><a>全部</a></li>
        <li class="order-tab" data-status="0"><a>待付款</a></li>
        <li class="order-tab" data-status="2"><a>已完成</a></li>
    </ul>
</div>

<style>.layui-table-tool-temp { padding-right: 0; }</style>
<table class="layui-hide" id="rc-table" lay-filter="rc-table"></table>

<script type="text/html" id="rc-toolbar">
    <div style="display:flex;align-items:center;justify-content:center;position:relative;padding:10px 0px 15px;border-bottom:1px solid #f0f0f0;">
        <span style="position:absolute;left:0;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">充值订单</span>
    </div>
    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
        <div class="layui-btn-container">
            <button class="layui-btn layui-btn-primary layui-border-green" lay-event="refresh"><i class="ri-refresh-line"></i></button>
            <button id="rc-toolbar-del" class="layui-btn  layui-bg-red layui-btn-disabled" lay-event="del">删除</button>
            <button class="layui-btn layui-btn-primary layui-border-blue" lay-event="export"><i class="ri-download-line"></i> 导出</button>
            <button class="layui-btn layui-btn-primary layui-border-orange" lay-event="clean"><i class="ri-delete-bin-line"></i> 清理</button>
        </div>
        <form class="layui-form" style="display: flex; align-items: center; gap: 6px; margin: 0;">
            <div class="layui-input-inline layui-input-wrap" style="width: 170px; margin: 0;">
                <input type="text" value="" name="email_username" placeholder="用户邮箱/昵称/手机" lay-affix="clear" class="layui-input">
            </div>
            <div class="layui-input-inline layui-input-wrap" style="width: 140px; margin: 0;">
                <input type="text" value="" name="out_trade_no" placeholder="订单号" lay-affix="clear" class="layui-input">
            </div>
            <button class="layui-btn layui-btn-sm" lay-submit lay-filter="rc-search">搜索</button>
            <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" id="rc-btn-reset">重置</button>
        </form>
    </div>
</script>

<script type="text/html" id="rc-userinfo">
    <div>{{ d.user_nickname }}</div>
    {{# if(d.user_email){ }}
    <div style="color:#999;font-size:12px;">{{ d.user_email }}</div>
    {{# } }}
    {{# if(d.user_tel){ }}
    <div style="color:#999;font-size:12px;">{{ d.user_tel }}</div>
    {{# } }}
</script>

<script type="text/html" id="rc-status">
    {{# if(d.status == 2){ }}
    <span style="color:#52c41a;font-weight:500;">{{ d.status_text }}</span>
    {{# } else if(d.status == 0){ }}
    <span style="color:#ff4d4f;font-weight:500;">{{ d.status_text }}</span>
    {{# } else { }}
    <span style="color:#fa8c16;font-weight:500;">{{ d.status_text }}</span>
    {{# } }}
</script>

<script type="text/html" id="rc-operate">
    <div class="layui-clear-space">
        <a class="layui-btn" lay-event="detail">详情</a>
        {{# if(d.pay_time_text == ''){ }}
        <a class="layui-btn layui-bg-orange" lay-event="budan">补单</a>
        {{# } }}
        <a class="layui-btn layui-bg-red" lay-event="del">删除</a>
    </div>
</script>

<script>
    layui.use(['table', 'form'], function(){
        var table = layui.table;
        var form = layui.form;
        var $ = layui.$;

        var currentStatus = '';

        table.render({
            elem: '#rc-table',
            id: 'rc-table',
            url: '?action=index',
            toolbar: '#rc-toolbar',
            limits: [10, 20, 30, 50, 100],
            lineStyle: 'height: 60px;',
            page: true,
            defaultToolbar: [],
            cols: [[
                {type: 'checkbox', fixed: 'left'},
                {field: 'out_trade_no', title: '订单号', width: 185},
                {field: 'amount_yuan', title: '充值金额', width: 88},
                {field: 'user_email', title: '充值用户', minWidth: 140, maxWidth: 220, templet: '#rc-userinfo'},
                {field: 'status_text', title: '状态', width: 90, templet: '#rc-status'},
                {field: 'payment', title: '支付方式', width: 88},
                {field: 'client_ip', title: '下单IP', width: 140},
                {field: 'create_time_text', title: '下单时间', width: 170},
                {field: 'pay_time_text', title: '支付时间', width: 170},
                {fixed: 'right', title: '操作', templet: '#rc-operate', width: 210}
            ]]
        });

        // Tab 切换
        $('.order-tab').on('click', function(){
            var status = $(this).data('status');
            currentStatus = status;
            $('.order-tab').removeClass('layui-this');
            $(this).addClass('layui-this');
            table.reload('rc-table', {
                page: { curr: 1 },
                where: {
                    status: status,
                    email_username: $('input[name="email_username"]').val(),
                    out_trade_no: $('input[name="out_trade_no"]').val(),
                    client_ip: $('input[name="client_ip"]').val()
                }
            });
        });

        // 重置按钮
        $(document).on('click', '#rc-btn-reset', function(){
            $('.layui-table-tool-temp input').val('');
            table.reload('rc-table', { page: {curr: 1}, where: {email_username: '', out_trade_no: '', status: currentStatus} });
        });

        // 搜索
        form.on('submit(rc-search)', function(data){
            var field = data.field;
            field.status = currentStatus;
            table.reload('rc-table', {
                page: { curr: 1 },
                where: field
            });
            return false;
        });

        // 工具栏事件
        table.on('toolbar(rc-table)', function(obj){
            var id = obj.config.id;
            var checkStatus = table.checkStatus(id);
            switch(obj.event){
                case 'refresh':
                    table.reload(id);
                    break;
                case 'export':
                    var exportHtml = '<div style="padding:20px 24px 10px;">'
                        + '<div style="font-size:13px;color:#666;margin-bottom:14px;">请选择要导出的订单范围：</div>'
                        + '<div style="display:flex;flex-wrap:wrap;gap:10px;" id="rc-export-status-opts">'
                        + '<label class="export-opt layui-btn layui-btn-sm layui-btn-checked" data-val=""><i class="ri-checkbox-circle-fill"></i> 全部订单</label>'
                        + '<label class="export-opt layui-btn layui-btn-sm layui-btn-primary" data-val="0"><i class="ri-checkbox-blank-circle-line"></i> 待付款</label>'
                        + '<label class="export-opt layui-btn layui-btn-sm layui-btn-primary" data-val="2"><i class="ri-checkbox-blank-circle-line"></i> 已完成</label>'
                        + '</div>'
                        + '<div style="margin-top:16px;padding-top:14px;border-top:1px solid #f0f0f0;">'
                        + '<div style="display:flex;align-items:center;gap:6px;"><i class="ri-filter-line" style="color:#1677ff;"></i><span style="font-size:12px;color:#999;">当前搜索条件将同步生效</span></div>'
                        + '</div>'
                        + '</div>';
                    layer.open({
                        title: '导出充值订单',
                        skin: 'dc-layer-modern',
                        area: ['380px', 'auto'],
                        content: exportHtml,
                        btn: ['<i class="ri-download-line"></i> 开始导出', '取消'],
                        yes: function(idx) {
                            var sel = $('#rc-export-status-opts .layui-btn-checked').data('val');
                            var params = new URLSearchParams({
                                action: 'export',
                                status: (sel !== undefined && sel !== '') ? sel : '',
                                email_username: $('input[name="email_username"]').val() || '',
                                out_trade_no: $('input[name="out_trade_no"]').val() || ''
                            });
                            window.open('order_recharge.php?' + params.toString());
                            layer.close(idx);
                        },
                        success: function(layero) {
                            layero.find('.export-opt').on('click', function(){
                                layero.find('.export-opt').removeClass('layui-btn-checked').addClass('layui-btn-primary')
                                    .find('i').attr('class', 'ri-checkbox-blank-circle-line');
                                $(this).removeClass('layui-btn-primary').addClass('layui-btn-checked')
                                    .find('i').attr('class', 'ri-checkbox-circle-fill');
                            });
                        }
                    });
                    break;
                case 'clean':
                    var cleanHtml = '<div style="padding:20px 20px 10px;">'
                        + '<div style="font-size:13px;color:#666;margin-bottom:6px;">清理未支付的历史充值订单，选择时间范围：</div>'
                        + '<div style="font-size:12px;color:#f5222d;margin-bottom:14px;"><i class="ri-error-warning-line"></i> 清理后订单将被移入回收站</div>'
                        + '<div style="display:flex;gap:10px;" id="rc-clean-days-opts">'
                        + '<label class="export-opt layui-btn layui-btn-sm layui-btn-primary" data-val="30" style="flex:1;min-width:0;padding:0 12px;">30 天前</label>'
                        + '<label class="export-opt layui-btn layui-btn-sm layui-btn-primary" data-val="90" style="flex:1;min-width:0;padding:0 12px;">90 天前</label>'
                        + '<label class="export-opt layui-btn layui-btn-sm layui-btn-checked" data-val="180" style="flex:1;min-width:0;padding:0 12px;">180 天前</label>'
                        + '<label class="export-opt layui-btn layui-btn-sm layui-btn-primary" data-val="365" style="flex:1;min-width:0;padding:0 12px;">365 天前</label>'
                        + '</div>'
                        + '</div>';
                    layer.open({
                        title: '清理未支付订单',
                        skin: 'dc-layer-modern',
                        area: ['460px', 'auto'],
                        content: cleanHtml,
                        btn: ['<i class="ri-delete-bin-line"></i> 确认清理', '取消'],
                        yes: function(idx) {
                            var days = $('#rc-clean-days-opts .layui-btn-checked').data('val');
                            layer.close(idx);
                            var loadIdx = layer.load(2);
                            $.ajax({
                                url: '?action=clean_cancelled',
                                type: 'POST',
                                dataType: 'json',
                                data: { days: days, token: '<?= LoginAuth::genToken() ?>' },
                                success: function(e) {
                                    layer.close(loadIdx);
                                    if (e.code == 400) return layer.msg(e.msg);
                                    layer.msg('已清理 ' + (e.data.count || 0) + ' 条未支付订单');
                                    table.reload(id);
                                },
                                error: function(err) {
                                    layer.close(loadIdx);
                                    layer.msg('操作失败');
                                }
                            });
                        },
                        success: function(layero) {
                            layero.find('.export-opt').on('click', function(){
                                layero.find('.export-opt').removeClass('layui-btn-checked').addClass('layui-btn-primary');
                                $(this).removeClass('layui-btn-primary').addClass('layui-btn-checked');
                            });
                        }
                    });
                    break;
                case 'del':
                    var data = checkStatus.data;
                    if(data.length == 0) break;
                    var ids = $.map(data, function(item){ return item.id; }).join(',');
                    layer.confirm('确定要删除的充值订单吗？', {
                        btn: ['确认', '取消'],
                        icon: 3,
                        title: '温馨提示'
                    }, function(index){
                        layer.close(index);
                        $.ajax({
                            url: '?action=del',
                            type: 'POST',
                            dataType: 'json',
                            data: { ids: ids, token: '<?= LoginAuth::genToken() ?>' },
                            success: function(e){
                                if(e.code == 400) return layer.msg(e.msg);
                                layer.msg('充值订单已删除');
                                table.reload(id);
                            },
                            error: function(err){
                                var msg = (err.responseJSON && err.responseJSON.msg) ? err.responseJSON.msg : '操作失败';
                                layer.msg(msg);
                            }
                        });
                    });
                    break;
            }
        });

        // 行操作事件
        table.on('tool(rc-table)', function(obj){
            var data = obj.data;
            var id = obj.config.id;

            if(obj.event === 'del'){
                layer.confirm('确定要删除这条充值订单吗？', {
                    btn: ['确认', '取消'],
                    icon: 3,
                    title: '温馨提示'
                }, function(index){
                    layer.close(index);
                    $.ajax({
                        url: '?action=del',
                        type: 'POST',
                        dataType: 'json',
                        data: { ids: data.id, token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e){
                            if(e.code == 400) return layer.msg(e.msg);
                            layer.msg('充值订单已删除');
                            table.reload(id);
                        },
                        error: function(err){
                            var msg = (err.responseJSON && err.responseJSON.msg) ? err.responseJSON.msg : '操作失败';
                            layer.msg(msg);
                        }
                    });
                });
            }

            if(obj.event === 'budan'){
                layer.confirm('确定要对该充值订单执行补单操作？补单成功后将自动为用户充值余额。', {
                    btn: ['确认', '取消'],
                    icon: 3,
                    title: '补单确认'
                }, function(index){
                    layer.close(index);
                    $.ajax({
                        url: '?action=repay',
                        type: 'POST',
                        dataType: 'json',
                        data: { out_trade_no: data.out_trade_no },
                        success: function(e){
                            if(e.code == 400) return layer.msg(e.msg);
                            layer.msg('补单成功，余额已到账');
                            table.reload(id);
                        },
                        error: function(err){
                            var msg = (err.responseJSON && err.responseJSON.msg) ? err.responseJSON.msg : '补单失败';
                            layer.msg(msg);
                        }
                    });
                });
            }

            if(obj.event === 'detail'){
                var isMobile = window.innerWidth < 768;
                var area = isMobile ? ['98%', 'auto'] : ['600px', 'auto'];
                layer.open({
                    id: 'rc-detail',
                    title: '充值订单详情',
                    type: 2,
                    area: area,
                    scrollbar: false,
                    skin: 'dc-layer-modern',
                    content: 'order_recharge.php?action=detail&order_id=' + data.id,
                    fixed: false,
                    maxmin: true,
                    shadeClose: true,
                    success: function(layero, index, that){
                        layer.iframeAuto(index);
                        that.offset();
                    }
                });
            }
        });

        // 复选框
        table.on('checkbox(rc-table)', function(obj){
            var id = obj.config.id;
            var checkData = table.checkStatus(id).data;
            if(checkData.length == 0){
                $('#rc-toolbar-del').addClass('layui-btn-disabled');
            } else {
                $('#rc-toolbar-del').removeClass('layui-btn-disabled');
            }
        });
    });
</script>


