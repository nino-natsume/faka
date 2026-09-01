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
        <li class="order-tab" data-status="1"><a>待发货</a></li>
        <li class="order-tab" data-status="4"><a>待收货</a></li>
        <li class="order-tab" data-status="2"><a>已完成</a></li>
        <li class="order-tab" data-status="refunding"><a>退款中</a></li>
        <li class="order-tab" data-status="3"><a>已取消</a></li>
    </ul>
</div>

<style>.layui-table-tool-temp { padding-right: 0; }</style>
<style>
.goods-type-tag { display: inline-block; max-width: 100%; padding: 1px 8px; line-height: 20px; font-size: 12px; font-weight: 500; border-radius: 4px; border: 1px solid; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; vertical-align: middle; box-sizing: border-box; }
.goods-type-tag.type-docking { background: #f9f0ff; color: #531dab; border-color: #d3adf7; }
</style>
<table class="layui-hide" id="index" lay-filter="index"></table>
<script type="text/html" id="toolbar">
    <div style="display:flex;align-items:center;justify-content:center;position:relative;padding:10px 0px 15px;border-bottom:1px solid #f0f0f0;">
        <span style="position:absolute;left:0;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">商品订单</span>
    </div>
    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
        <div class="layui-btn-container">
            <button class="layui-btn layui-btn-primary layui-border-green" lay-event="refresh"><i class="ri-refresh-line"></i></button>
            <button id="toolbar-del" class="layui-btn  layui-bg-red layui-btn-disabled" lay-event="del">删除</button>
            <button class="layui-btn layui-btn-primary layui-border-blue" lay-event="export"><i class="ri-download-line"></i> 导出</button>
            <button class="layui-btn layui-btn-primary layui-border-orange" lay-event="clean"><i class="ri-delete-bin-line"></i> 清理</button>
            <button class="layui-btn layui-btn-warm" lay-event="recycle"><i class="ri-delete-bin-line"></i> 回收站</button>
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
<script type="text/html" id="goods">
    <div class="goods-info">
        {{# layui.each(d.list, function(index, item){ }}
        <div style="{{# if(index > 0){ }}margin-top:5px;padding-top:5px;border-top:1px dashed #eee;{{# } }}">
            {{# if(item.attr_spec && item.attr_spec.indexOf('[赠品]') > -1){ }}
            <span style="background:#52c41a;color:#fff;padding:1px 4px;border-radius:2px;font-size:11px;margin-right:3px;">赠</span>
            {{# } }}
            <div class="row-1-hidden">{{# if(parseInt(item.is_docking || 0, 10) === 1){ }}<span class="goods-type-tag type-docking" style="font-size:11px;padding:0 5px;line-height:18px;margin-right:3px;vertical-align:middle;">对接</span>{{# } }}{{ item.title }}</div>
            <div style="color:#999;font-size:12px;">{{ item.attr_spec ? item.attr_spec.replace('[赠品] ', '') : '默认规格' }}</div>
        </div>
        {{# }); }}
    </div>
</script>

<script type="text/html" id="quantity">
    <div class="goods-info">
        {{# layui.each(d.list, function(index, item){ }}
        <div style="{{# if(index > 0){ }}margin-top:5px;padding-top:5px;{{# } }}">{{ item.quantity }}</div>
        {{# }); }}
    </div>
</script>
<script type="text/html" id="userinfo">
    <div class="goods-info">
        <div>{{ d.user_nickname }}</div>
        {{# if(d.user_id == 0){ }}
        <div>游客身份</div>
        {{#  } }}
        {{# if(d.user_email != ''){ }}
        <div>{{ d.user_email }}</div>
        {{#  } }}
        {{# if(d.user_tel != ''){ }}
        <div>{{ d.user_tel }}</div>
        {{#  } }}
    </div>
</script>
<script type="text/html" id="statusTpl">
    {{# if(d.status_text == '已完成'){ }}
    <span style="color:#52c41a;font-weight:500;">{{ d.status_text }}</span>
    {{# } else if(d.status_text == '未支付'){ }}
    <span style="color:#ff4d4f;font-weight:500;">{{ d.status_text }}</span>
    {{# } else if(d.status_text == '待收货'){ }}
    <span style="color:#1677ff;font-weight:500;">{{ d.status_text }}</span>
    {{# } else if(d.status_text == '已取消'){ }}
    <span style="color:#999;font-weight:500;">{{ d.status_text }}</span>
    {{# } else if(d.status_text == '退款中'){ }}
    <span style="color:#f5222d;font-weight:500;">{{ d.status_text }}</span>
    {{# } else if(d.status_text == '待对接站发货'){ }}
    <span style="color:#722ed1;font-weight:500;">{{ d.status_text }}</span>
    {{# } else { }}
    <span style="color:#fa8c16;font-weight:500;">{{ d.status_text }}</span>
    {{# } }}
    
    {{# if(d.docking_err_msg && d.docking_err_msg !== ''){ }}
    <div style="margin-top:4px;">
        <span class="layui-badge layui-bg-red docking-err-badge" style="font-size:10px;padding:1px 5px;border-radius:3px;cursor:pointer;display:inline-block;line-height:1.4;" data-msg="{{ d.docking_err_msg }}"><i class="ri-error-warning-line" style="vertical-align: middle; margin-right: 2px;"></i>对接失败</span>
    </div>
    {{# } }}
</script>
<script type="text/html" id="operate">
    <div class="layui-clear-space">
        <a class="layui-btn" lay-event="detail">详情</a>
        {{# if(d.status == 1){ }}
            {{# if(d.docking_err_msg && d.docking_err_msg !== ''){ }}
            <a class="layui-btn layui-bg-orange" lay-event="deliver">补单</a>
            {{# } else { }}
            <a class="layui-btn layui-bg-blue" lay-event="deliver">发货</a>
            {{# } }}
        {{#  } }}
        {{#  if(d.pay_time == ''){ }}
        <a class="layui-btn layui-bg-orange" lay-event="budan">补单</a>
        {{#  } }}
        <a class="layui-btn layui-bg-red" lay-event="del">删除</a>
    </div>
</script>


<script>
    layui.use(['table'], function(){
        var table = layui.table;
        var form = layui.form;
        var $ = layui.$;

        // 读取 URL 参数（从用户管理齿轮菜单跳转过来时自动筛选）
        var urlParams = new URLSearchParams(window.location.search);
        var initFilterUid = urlParams.get('filter_uid') || '';
        var initOutTradeNo = urlParams.get('out_trade_no') || '';
        var initEmailUsername = urlParams.get('email_username') || '';
        var initClientIp = urlParams.get('client_ip') || '';
        var initGoodsTitle = urlParams.get('goods_title') || '';
        var initOrderRequired = urlParams.get('order_required') || '';
        var initKamiContent = urlParams.get('kami_content') || '';
        
        // 当前选中的状态
        var currentStatus = '';

        $('input[name="email_username"]').val(initEmailUsername);
        $('input[name="out_trade_no"]').val(initOutTradeNo);
        $('input[name="goods_title"]').val(initGoodsTitle);
        $('input[name="order_required"]').val(initOrderRequired);
        $('input[name="kami_content"]').val(initKamiContent);

        var initWhere = {};
        if (initFilterUid) initWhere.filter_uid = initFilterUid;
        if (initOutTradeNo) initWhere.out_trade_no = initOutTradeNo;
        if (initEmailUsername) initWhere.email_username = initEmailUsername;
        if (initClientIp) initWhere.client_ip = initClientIp;
        if (initGoodsTitle) initWhere.goods_title = initGoodsTitle;
        if (initOrderRequired) initWhere.order_required = initOrderRequired;
        if (initKamiContent) initWhere.kami_content = initKamiContent;
        
        // 是否弹窗模式（从商品列表等处打开）
        var isPopup = urlParams.get('popup') === '1';

        // 弹窗模式使用紧凑列（去掉 fixed，缩减宽度，适配 1200px 弹窗）
        var tableCols = isPopup ? [
            {type: 'checkbox'},
            {field:'out_trade_no', title: '订单号', width: 165},
            {field:'goods', title:'商品信息',templet: '#goods', minWidth: 180},
            {field:'id', title: '数量', width: 50, templet: '#quantity'},
            {field:'amount', title:'订单金额', width: 90},
            {field:'user_email', title:'用户信息', minWidth: 120, maxWidth: 160, templet: '#userinfo'},
            {field:'status_text', title:'订单状态', width: 88, templet: '#statusTpl'},
            {field:'pay_time', title:'支付时间', width: 150},
            {title:'操作', templet: '#operate', width: 200}
        ] : [
            {type: 'checkbox', fixed: 'left'},
            {field:'out_trade_no', title: '订单号', width: 172},
            {field:'goods', title:'商品信息',templet: '#goods', minWidth: 215, maxWidth: 500},
            {field:'id', title: '数量', width: 50, templet: '#quantity'},
            {field:'amount', title:'订单金额', width: 100},
            {field:'user_email', title:'用户信息', minWidth: 130, maxWidth: 180, templet: '#userinfo'},
            {field:'status_text', title:'订单状态', width: 110, templet: '#statusTpl'},
            {field:'payment', title:'支付方式', width: 100},
            {field:'pay_time', title:'支付时间', width: 160},
            {fixed: 'right', title:'操作', templet: '#operate', minWidth: 210}
        ];

        // 创建渲染实例
        window.table = table.render({
            elem: '#index',
            autoSort: false,
            url: '?action=index',
            where: initWhere,
            toolbar: '#toolbar',
            limits: [10,20,30,50,100],
            lineStyle: 'height: 69px;',
            page: true,
            defaultToolbar: [],
            cols: [tableCols],
            error: function(res, msg){
                console.log(res, msg)
            }
        });
        
        // Tab 切换事件
        $('.order-tab').on('click', function(){
            var status = $(this).data('status');
            currentStatus = status;
            
            // 切换激活状态
            $('.order-tab').removeClass('layui-this');
            $(this).addClass('layui-this');
            
            // 重新加载表格
            table.reload('index', {
                page: { curr: 1 },
                where: {
                    status: status,
                    filter_uid: initFilterUid,
                    email_username: $('input[name="email_username"]').val(),
                    out_trade_no: $('input[name="out_trade_no"]').val(),
                    goods_title: $('input[name="goods_title"]').val(),
                    client_ip: $('input[name="client_ip"]').val(),
                    order_required: $('input[name="order_required"]').val()
                }
            });
        });

        // 搜索提交
        form.on('submit(index-search)', function(data){
            var field = data.field;
            field.status = currentStatus;
            if(initFilterUid) field.filter_uid = initFilterUid;
            table.reload('index', {
                page: { curr: 1 },
                where: field
            });
            return false;
        });

        // 重置按钮：清除筛选并刷新表格
        $(document).on('click', '#btn-reset', function(){
            $('.layui-table-tool-temp input').val('');
            table.reload('index', { page: {curr: 1}, where: {email_username: '', out_trade_no: '', goods_title: '', order_required: '', kami_content: '', status: currentStatus} });
        });

        // 工具栏事件
        table.on('toolbar(index)', function(obj){
            var id = obj.config.id;
            var checkStatus = table.checkStatus(id);
            var othis = lay(this);
            switch(obj.event){
                case 'refresh':
                    table.reload(id);
                    break;
                case 'export':
                    var exportHtml = '<div style="padding:20px 24px 10px;">'
                        + '<div style="font-size:13px;color:#666;margin-bottom:14px;">请选择要导出的订单范围：</div>'
                        + '<div style="display:flex;flex-wrap:wrap;gap:10px;" id="export-status-opts">'
                        + '<label class="export-opt layui-btn layui-btn-sm layui-btn-checked" data-val=""><i class="ri-checkbox-circle-fill"></i> 全部订单</label>'
                        + '<label class="export-opt layui-btn layui-btn-sm layui-btn-primary" data-val="0"><i class="ri-checkbox-blank-circle-line"></i> 待付款</label>'
                        + '<label class="export-opt layui-btn layui-btn-sm layui-btn-primary" data-val="1"><i class="ri-checkbox-blank-circle-line"></i> 待发货</label>'
                        + '<label class="export-opt layui-btn layui-btn-sm layui-btn-primary" data-val="4"><i class="ri-checkbox-blank-circle-line"></i> 待收货</label>'
                        + '<label class="export-opt layui-btn layui-btn-sm layui-btn-primary" data-val="2"><i class="ri-checkbox-blank-circle-line"></i> 已完成</label>'
                        + '<label class="export-opt layui-btn layui-btn-sm layui-btn-primary" data-val="refunding"><i class="ri-checkbox-blank-circle-line"></i> 退款中</label>'
                        + '<label class="export-opt layui-btn layui-btn-sm layui-btn-primary" data-val="3"><i class="ri-checkbox-blank-circle-line"></i> 已取消</label>'
                        + '</div>'
                        + '<div style="margin-top:16px;padding-top:14px;border-top:1px solid #f0f0f0;">'
                        + '<div style="display:flex;align-items:center;gap:6px;"><i class="ri-filter-line" style="color:#1677ff;"></i><span style="font-size:12px;color:#999;">当前搜索条件将同步生效</span></div>'
                        + '</div>'
                        + '</div>';
                    layer.open({
                        title: '导出商品订单',
                        skin: 'dc-layer-modern',
                        area: ['420px', 'auto'],
                        content: exportHtml,
                        btn: ['<i class="ri-download-line"></i> 开始导出', '取消'],
                        yes: function(idx) {
                            var sel = $('#export-status-opts .layui-btn-checked').data('val');
                            var params = new URLSearchParams({
                                action: 'export',
                                status: (sel !== undefined && sel !== '') ? sel : '',
                                email_username: $('input[name="email_username"]').val() || '',
                                out_trade_no: $('input[name="out_trade_no"]').val() || '',
                                goods_title: $('input[name="goods_title"]').val() || '',
                                order_required: $('input[name="order_required"]').val() || '',
                                kami_content: $('input[name="kami_content"]').val() || '',
                                client_ip: $('input[name="client_ip"]').val() || ''
                            });
                            if (initFilterUid) params.set('filter_uid', initFilterUid);
                            window.open('order.php?' + params.toString());
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
                case 'recycle':
                    window.location.href = 'order_recycle.php';
                    break;
                case 'clean':
                    var cleanHtml = '<div style="padding:20px 20px 10px;">'
                        + '<div style="font-size:13px;color:#666;margin-bottom:6px;">清理已取消的历史订单，选择时间范围：</div>'
                        + '<div style="font-size:12px;color:#f5222d;margin-bottom:14px;"><i class="ri-error-warning-line"></i> 清理后订单将被移入回收站</div>'
                        + '<div style="display:flex;gap:10px;" id="clean-days-opts">'
                        + '<label class="export-opt layui-btn layui-btn-sm layui-btn-primary" data-val="30" style="flex:1;min-width:0;padding:0 12px;">30 天前</label>'
                        + '<label class="export-opt layui-btn layui-btn-sm layui-btn-primary" data-val="90" style="flex:1;min-width:0;padding:0 12px;">90 天前</label>'
                        + '<label class="export-opt layui-btn layui-btn-sm layui-btn-checked" data-val="180" style="flex:1;min-width:0;padding:0 12px;">180 天前</label>'
                        + '<label class="export-opt layui-btn layui-btn-sm layui-btn-primary" data-val="365" style="flex:1;min-width:0;padding:0 12px;">365 天前</label>'
                        + '</div>'
                        + '</div>';
                    layer.open({
                        title: '清理已取消订单',
                        skin: 'dc-layer-modern',
                        area: ['460px', 'auto'],
                        content: cleanHtml,
                        btn: ['<i class="ri-delete-bin-line"></i> 确认清理', '取消'],
                        yes: function(idx) {
                            var days = $('#clean-days-opts .layui-btn-checked').data('val');
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
                                    layer.msg('已清理 ' + (e.data.count || 0) + ' 条已取消订单');
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
                    if(data.length == 0){
                        break;
                    }
                    var ids = $.map(data, function(item) {
                        return item.id; // 提取每个对象的uid
                    }).join(',');
                    layer.confirm('确定要删除的数据吗？', {
                        btn: ['确认', '取消'], // 按钮
                        icon: 3,             // 图标，3表示问号
                        title: '温馨提示'
                    }, function(index) {
                        layer.close(index); // 关闭对话框
                        $.ajax({
                            url: '?action=del',
                            type: 'POST',
                            dataType: 'json',
                            data: { ids: ids, token: '<?= LoginAuth::genToken() ?>' },
                            success: function(e) {
                                if(e.code == 400){
                                    return layer.msg(e.msg)
                                }
                                layer.msg('订单已移入回收站');
                                table.reload(id);
                            },
                            error: function(err) {
                                var msg = (err.responseJSON && err.responseJSON.msg) ? err.responseJSON.msg : '操作失败，请检查服务器日志';
                                layer.msg(msg);
                            }
                        });
                    });
                    break;
            };
        });

        // 触发单元格工具事件
        table.on('tool(index)', function(obj){ // 双击 toolDouble
            var data = obj.data; // 获得当前行数据
            var id = obj.config.id;
            if(obj.event == 'del'){
                layer.confirm('确定要删除这条数据吗？', {
                    btn: ['确认', '取消'], // 按钮
                    icon: 3,             // 图标，3表示问号
                    title: '温馨提示'
                }, function(index) {
                    layer.close(index); // 关闭对话框
                    $.ajax({
                        url: '?action=del',
                        type: 'POST',
                        dataType: 'json',
                        data: { ids: data.id, token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e) {
                            if(e.code == 400){
                                return layer.msg(e.msg)
                            }
                            layer.msg('订单已移入回收站');
                            table.reload(id);
                        },
                        error: function(err) {
                            var msg = (err.responseJSON && err.responseJSON.msg) ? err.responseJSON.msg : '操作失败，请检查服务器日志';
                            layer.msg(msg);
                        }
                    });
                });
            }
            if(obj.event === 'budan'){
                layer.confirm('将该订单改为已支付状态？', {
                    btn: ['确认', '取消'], // 按钮
                    icon: 3,             // 图标，3表示问号
                    title: '温馨提示'
                }, function(index) {
                    layer.close(index); // 关闭对话框
                    $.ajax({
                        url: '?action=repay',
                        type: 'POST',
                        dataType: 'json',
                        data: { out_trade_no: data.out_trade_no },
                        success: function(e) {
                            if(e.code == 400){
                                return layer.msg(e.msg)
                            }
                            layer.msg('补单成功');
                            table.reload(id);
                        },
                        error: function(err) {
                            var msg = (err.responseJSON && err.responseJSON.msg) ? err.responseJSON.msg : '补单失败，请检查服务器日志';
                            layer.msg(msg);
                        }
                    });
                }, function() {
                });
            }

            if(obj.event === 'deliver'){
                let isMobile = window.innerWidth < 768;
                let popupHeight = isMobile ? 'calc(100vh - 32px)' : Math.min(720, window.innerHeight - 100) + 'px';
                let area = isMobile ? ['98%', popupHeight]  : ['500px', popupHeight];
                layer.open({
                    id: 'deliver',
                    title: '发货信息填写',
                    type: 2,
                    area: area,
                    // skin: 'layui-layer-win10',
                    skin: 'dc-layer-modern',
                    content: 'order.php?action=deliver&order_id=' + data.id,
                    fixed: true,
                    maxmin: true,
                    shadeClose: true
                });
            }
            if(obj.event === 'detail'){
                let isMobile = window.innerWidth < 768;
                let area = isMobile ? ['98%', 'auto']  : ['800px', 'auto'];
                layer.open({
                    id: 'detail',
                    title: '订单详情',
                    type: 2,
                    area: area,
                    scrollbar: false,
                    skin: 'dc-layer-modern',
                    content: 'order.php?action=detail&order_id=' + data.id,
                    fixed: false, // 不固定
                    maxmin: true,
                    shadeClose: true,
                    success: function(layero, index, that){
                        layer.iframeAuto(index); // 让 iframe 高度自适应
                        that.offset(); // 重新自适应弹层坐标
                    }
                });
            }
        });

        // 触发排序事件
        table.on('sort(index)', function(obj){
            console.log(obj.field); // 当前排序的字段名
            console.log(obj.type); // 当前排序类型：desc（降序）、asc（升序）、null（空对象，默认排序）
            console.log(this); // 当前排序的 th 对象

            // 尽管我们的 table 自带排序功能，但并没有请求服务端。
            // 有些时候，你可能需要根据当前排序的字段，重新向后端发送请求，从而实现服务端排序，如：
            table.reload('index', {
                initSort: obj, // 记录初始排序，如果不设的话，将无法标记表头的排序状态。
                where: { // 请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    field: obj.field, // 排序字段
                    order: obj.type // 排序方式
                }
            });
        });

        // 触发表格复选框选择
        table.on('checkbox(index)', function(obj){
            var id = obj.config.id;
            var checkData = table.checkStatus(id).data;
            console.log(checkData)
            if(checkData.length == 0){
                $('#toolbar-del').addClass('layui-btn-disabled');
            }else{
                $('#toolbar-del').removeClass('layui-btn-disabled');
            }
        });

        // 分页栏事件
        table.on('pagebar(index)', function(obj){
            alert()
            console.log(obj); // 查看对象所有成员
            console.log(obj.config); // 当前实例的配置信息
            console.log(obj.event); // 属性 lay-event 对应的值
        });


        // 表头自定义元素工具事件 --- 2.8.8+
        table.on('colTool(test)', function(obj){
            var event = obj.event;
            console.log(obj);
            if(event === 'email-tips'){
                layer.alert(layui.util.escape(JSON.stringify(obj.col)), {
                    title: '当前列属性选项'
                });
            }
        });


        // ===== 对接失败 badge 点击查看原因 =====
        $(document).on('click', '.docking-err-badge', function(){
            var msg = $(this).data('msg') || '未知错误';
            layer.alert('<div style="font-size:14px;line-height:1.8;padding:10px 0;"><b style="color:#f5222d;">对接失败原因：</b><br><span style="color:#333;">' + msg + '</span></div>', {
                title: '对接下单失败详情',
                icon: 2,
                area: ['420px', 'auto'],
                shadeClose: true
            });
        });

    });
</script>
