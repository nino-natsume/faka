<?php defined('DC_ROOT') || exit('access denied!'); ?>

<table class="layui-hide" id="index" lay-filter="index"></table>

<script type="text/html" id="toolbar">
    <?php if (empty($_GET['embed'])): ?>
    <div style="display:flex;align-items:center;justify-content:center;position:relative;padding:10px 0px 15px;border-bottom:1px solid #f0f0f0;">
        <span style="position:absolute;left:0;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">批量加价规则</span>
    </div>
    <?php endif; ?>
    <div class="layui-btn-container" style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
        <button class="layui-btn layui-btn-primary layui-border-green" lay-event="refresh"><i class="ri-refresh-line"></i></button>
        <button type="button" class="layui-btn" lay-event="add"><i class="ri-add-line"></i> 新建规则</button>
        <button id="toolbar-del" class="layui-btn layui-bg-red layui-btn-disabled" lay-event="del">删除</button>
        <div class="layui-input-inline" style="width:240px;margin-left:auto;">
            <input id="search-kw" type="text" placeholder="搜索名称或ID" autocomplete="off" class="layui-input">
        </div>
        <button class="layui-btn layui-btn-primary" lay-event="search"><i class="ri-search-line"></i></button>
    </div>
    <div style="padding:8px 0 0;color:#6b7280;font-size:12px;">
        优先级：商品独立价 → 单商品规则 → 商品自定义利润比 → <b style="color:#2563eb;">批量加价规则</b> → 等级加价。
    </div>
</script>

<script type="text/html" id="stateTpl">
    <input type="checkbox" name="{{= d.id }}" value="{{= d.id }}" title=" 启用 | 停用 " lay-skin="switch" lay-filter="switch-state" {{= d.state == 1 ? 'checked' : '' }}>
</script>

<script type="text/html" id="rulesTpl">
    <div style="padding:4px 0;">{{ d.rules_html }}</div>
</script>

<script type="text/html" id="usageTpl">
    {{# if (d.usage_goods == 0 && d.usage_level == 0) { }}
    <span class="layui-badge-rim" style="color:#9ca3af;">未被引用</span>
    {{# } else { }}
    <a class="layui-btn layui-btn-xs layui-btn-primary" lay-event="usage">
        {{# if (d.usage_goods > 0) { }} 商品 {{= d.usage_goods }} {{# } }}
        {{# if (d.usage_level > 0) { }} 等级 {{= d.usage_level }} {{# } }}
    </a>
    {{# } }}
</script>

<script type="text/html" id="operate">
    <div class="layui-clear-space">
        <a class="layui-btn" lay-event="edit">编辑</a>
        <a class="layui-btn layui-bg-red" lay-event="del">删除</a>
    </div>
</script>

<script>
layui.use(['table', 'form'], function(){
    var table = layui.table;
    var form = layui.form;
    var TOKEN = '<?= LoginAuth::genToken() ?>';

    window.table = table.render({
        elem: '#index',
        url: '?action=index',
        toolbar: '#toolbar',
        limits: [20, 50, 100],
        limit: 20,
        page: true,
        lineStyle: 'min-height: 60px;',
        defaultToolbar: [],
        cols: [[
            {type: 'checkbox', fixed: 'left'},
            {field: 'id', title: 'ID', width: 60},
            {field: 'name', title: '规则名称', minWidth: 140},
            {field: 'rules_html', title: '成本区间配置', minWidth: 320, templet: '#rulesTpl'},
            {title: '引用', minWidth: 120, templet: '#usageTpl'},
            {field: 'state', title: '状态', minWidth: 100, templet: '#stateTpl'},
            {field: 'create_time_str', title: '创建时间', minWidth: 150},
            {fixed: 'right', title: '操作', templet: '#operate', width: 160, align: 'center'}
        ]],
        error: function(res, msg){ console.log(res, msg); }
    });

    // 工具栏事件
    table.on('toolbar(index)', function(obj){
        var id = obj.config.id;
        var checkStatus = table.checkStatus(id);
        if (obj.event === 'refresh') table.reload(id);
        if (obj.event === 'search') table.reload(id, { where: { keyword: $('#search-kw').val() }, page: { curr: 1 } });
        if (obj.event === 'add') openEdit(0);
        if (obj.event === 'del') {
            var data = checkStatus.data;
            if (data.length === 0) return;
            var ids = $.map(data, function(it){ return it.id; }).join(',');
            layer.confirm('确认删除选中的 ' + data.length + ' 条规则？<br><span style="color:#ef4444;">被商品或等级引用的规则无法删除</span>', {
                btn: ['确认', '取消'], icon: 3, title: '温馨提示'
            }, function(idx){
                layer.close(idx);
                $.post('?action=del', { ids: ids, token: TOKEN }, function(e){
                    if (e.code == 400) return layer.msg(e.msg, { icon: 2 });
                    layer.msg('规则已删除', { icon: 1 });
                    table.reload(id);
                }, 'json');
            });
        }
    });

    // 行操作
    table.on('tool(index)', function(obj){
        var data = obj.data;
        var id = obj.config.id;
        if (obj.event === 'edit') openEdit(data.id);
        if (obj.event === 'del') {
            layer.confirm('确定删除规则「' + data.name + '」？', {
                btn: ['确认', '取消'], icon: 3
            }, function(idx){
                layer.close(idx);
                $.post('?action=del', { ids: data.id, token: TOKEN }, function(e){
                    if (e.code == 400) return layer.msg(e.msg, { icon: 2 });
                    layer.msg('规则已删除', { icon: 1 });
                    table.reload(id);
                }, 'json');
            });
        }
        if (obj.event === 'usage') showUsage(data.id);
    });

    // 启停切换
    form.on('switch(switch-state)', function(obj){
        var state = obj.elem.checked ? 1 : 0;
        var id = this.name;
        $.post('?action=toggle_state', { id: id, state: state, token: TOKEN }, function(e){
            if (e.code == 400) return layer.msg(e.msg, { icon: 2 });
            layer.msg('规则状态已更新', { icon: 1 });
        }, 'json');
    });

    // 复选框选中→删除按钮
    table.on('checkbox(index)', function(obj){
        var id = obj.config.id;
        var checkData = table.checkStatus(id).data;
        if (checkData.length === 0) $('#toolbar-del').addClass('layui-btn-disabled');
        else $('#toolbar-del').removeClass('layui-btn-disabled');
    });

    // 搜索框回车
    $('#search-kw').on('keydown', function(e){
        if (e.keyCode === 13) {
            table.reload('index', { where: { keyword: $(this).val() }, page: { curr: 1 } });
        }
    });

    // 打开编辑弹窗
    function openEdit(id) {
        var isMobile = window.innerWidth < 768;
        var area = isMobile ? ['98%', 'auto'] : ['720px', 'auto'];
        layer.open({
            id: 'edit', title: id > 0 ? '编辑批量加价规则' : '新建批量加价规则',
            type: 2, area: area, skin: 'dc-layer-modern',
            content: '?action=edit' + (id > 0 ? '&id=' + id : ''),
            fixed: false, maxmin: true, shadeClose: false,
            success: function(layero, index, that){
                layer.iframeAuto(index);
                that.offset();
            }
        });
    }

    // 查看引用详情
    function showUsage(id) {
        $.get('?action=usage&id=' + id, function(e){
            if (e.code == 400) return layer.msg(e.msg, { icon: 2 });
            var d = e.data;
            var html = '<div style="padding:12px 18px;max-height:60vh;overflow-y:auto;">';
            html += '<div style="margin-bottom:10px;font-size:13px;color:#4b5563;">规则「<b style="color:#2563eb;">' + d.rule.name + '</b>」被以下对象引用：</div>';
            if (d.goods.length > 0) {
                html += '<div style="margin-top:8px;"><b>商品 (' + d.goods.length + ')：</b></div><div style="padding-left:10px;">';
                d.goods.forEach(function(g){
                    html += '<div style="padding:3px 0;">#' + g.id + ' - ' + g.title + '</div>';
                });
                html += '</div>';
            }
            if (d.levels.length > 0) {
                html += '<div style="margin-top:12px;"><b>等级 (' + d.levels.length + ')：</b></div><div style="padding-left:10px;">';
                d.levels.forEach(function(l){
                    html += '<div style="padding:3px 0;">#' + l.id + ' - ' + l.name + '</div>';
                });
                html += '</div>';
            }
            if (d.goods.length === 0 && d.levels.length === 0) {
                html += '<div style="color:#9ca3af;padding:14px 0;">无引用</div>';
            }
            html += '</div>';
            layer.open({
                title: '规则引用详情', type: 1, area: ['520px', 'auto'],
                shadeClose: true, content: html, skin: 'dc-layer-modern'
            });
        }, 'json');
    }

    // 刷新表格（外部可用）
    window.reloadRuleTable = function(){ table.reload('index'); };
});
</script>
