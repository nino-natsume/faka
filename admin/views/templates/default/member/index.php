<?php defined('DC_ROOT') || exit('access denied!'); ?>

<table class="layui-hide" id="index" lay-filter="index"></table>

<script type="text/html" id="toolbar">
    <div style="display:flex;align-items:center;justify-content:center;position:relative;padding:10px 0px 15px;border-bottom:1px solid #f0f0f0;">
        <span style="position:absolute;left:0;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">会员等级</span>
    </div>
    <div class="layui-btn-container">
        <button class="layui-btn layui-btn-primary layui-border-green" lay-event="refresh"><i class="ri-refresh-line"></i></button>
        <button type="button" class="layui-btn" lay-event="add">添加</button>
        <button id="toolbar-del" class="layui-btn layui-bg-red layui-btn-disabled" lay-event="del">删除</button>
        <button type="button" class="layui-btn layui-btn-warm" lay-event="reset">选择预设方案</button>
    </div>
</script>

<script type="text/html" id="stateTpl">
    <input type="checkbox" name="{{= d.id }}" value="{{= d.id }}" title=" 启用 | 停用 " lay-skin="switch" lay-filter="switch-state" {{= d.state == 1 ? 'checked' : '' }}>
</script>

<script type="text/html" id="markupTpl">{{ d.markup_ratio }}%</script>
<script type="text/html" id="profitTpl">{{ d.actual_profit }}%</script>
<script type="text/html" id="thresholdTpl">{{ d.profit_threshold }}%</script>
<script type="text/html" id="renewTpl">{{ d.renew_ratio }}%</script>
<script type="text/html" id="durationTpl">{{ d.duration_days > 0 ? d.duration_days + ' 天' : '永久' }}</script>

<script type="text/html" id="autoUpgradeTpl">
{{# var parts = []; }}
{{# if(d.upgrade_direct_count > 0) parts.push('直推' + d.upgrade_direct_count + '人'); }}
{{# if(d.upgrade_consume_amount > 0) parts.push('消费' + d.upgrade_consume_amount + '元'); }}
{{# if(d.upgrade_team_count > 0) parts.push('团队' + d.upgrade_team_count + '人'); }}
{{# if(parts.length === 0){ }}
<span style="color:#94a3b8;">仅付费</span>
{{# } else { }}
<span style="color:#059669;">{{ parts.join(d.upgrade_mode === 'all' ? ' + ' : ' / ') }}</span>
<span style="color:#94a3b8;font-size:11px;">（{{ d.upgrade_mode === 'all' ? '全部' : '任一' }}）</span>
{{# } }}
</script>

<script type="text/html" id="sortTpl">
    <div class="layui-clear-space">
        <a class="layui-btn layui-btn-xs layui-btn-primary" lay-event="sort-top" title="置顶">↑↑</a>
        <a class="layui-btn layui-btn-xs layui-btn-primary" lay-event="sort-up" title="上移">↑</a>
        <a class="layui-btn layui-btn-xs layui-btn-primary" lay-event="sort-down" title="下移">↓</a>
        <a class="layui-btn layui-btn-xs layui-btn-primary" lay-event="sort-bottom" title="置底">↓↓</a>
    </div>
</script>

<script type="text/html" id="nameTpl">
    <span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:9px;background:#eff6ff;color:#2563eb;margin-right:6px;vertical-align:middle;overflow:hidden;">
    {{# if(d.icon_image){ }}
        <img src="{{ d.icon_image }}" style="width:100%;height:100%;object-fit:cover;">
    {{# } else { }}
        <i class="{{ d.icon || 'ri-vip-diamond-line' }}" style="font-size:17px;"></i>
    {{# } }}
    </span>
    {{ d.name }}
    {{# if(d.is_default == 1){ }}
        <span style="background:#16a34a;color:#fff;font-size:11px;padding:1px 6px;border-radius:3px;margin-left:4px;">默认</span>
    {{# } }}
</script>

<script type="text/html" id="operate">
    <div class="layui-clear-space">
        {{# if(d.is_default != 1){ }}
        <a class="layui-btn layui-btn-xs layui-btn-normal" lay-event="set_default">设为默认</a>
        {{# } }}
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        {{# if(d.is_default != 1){ }}
        <a class="layui-btn layui-btn-xs layui-bg-red" lay-event="del">删除</a>
        {{# } }}
    </div>
</script>

<script>
layui.use(['table', 'form'], function(){
    var table = layui.table;
    var form = layui.form;
    var TOKEN = '<?= LoginAuth::genToken() ?>';
    var RESET_LEVEL_COUNT = <?= intval($memberResetLevelCount ?? 0) ?>;
    var RESET_USER_COUNT = <?= intval($memberResetUserCount ?? 0) ?>;
    var RESET_COUNTDOWN_SECONDS = 10;

    window.table = table.render({
        elem: '#index',
        autoSort: false,
        url: '?action=index',
        toolbar: '#toolbar',
        limits: [],
        page: false,
        lineStyle: 'height: 30px;',
        defaultToolbar: [],
        cols: [[
            {type: 'checkbox', fixed: 'left'},
            {field: 'id', title: 'ID', width: 60},
            {field: 'name', title: '会员等级', width: 180, templet: '#nameTpl'},
            {field: 'price', title: '开通/升级价', minWidth: 110},
            {field: 'markup_ratio', title: '默认加价比例', minWidth: 110, templet: '#markupTpl'},
            {field: 'exchange_ratio', title: '积分兑换倍数', minWidth: 110},
            {field: 'actual_profit', title: '上级先拿比例', minWidth: 110, templet: '#profitTpl'},
            {field: 'profit_threshold', title: '停止上分阈值', minWidth: 110, templet: '#thresholdTpl'},
            {field: 'duration_days', title: '有效期', minWidth: 70, templet: '#durationTpl'},
            {field: 'renew_ratio', title: '续费收费比例', minWidth: 110, templet: '#renewTpl'},
            {field: 'upgrade_mode', title: '自动升级', minWidth: 160, templet: '#autoUpgradeTpl'},
            {field: 'state', title: '状态', minWidth: 100, templet: '#stateTpl'},
            {fixed: 'right', title: '操作', templet: '#operate', width: 220, align: 'center'}
        ]],
        error: function(res, msg){ console.log(res, msg); }
    });

    // 工具栏事件
    table.on('toolbar(index)', function(obj){
        var id = obj.config.id;
        var checkStatus = table.checkStatus(id);
        if (obj.event === 'refresh') {
            table.reload(id);
        }
        if (obj.event === 'add') {
            var isMobile = window.innerWidth < 768;
            var area = isMobile ? ['98%', 'auto'] : ['860px', 'auto'];
            layer.open({
                id: 'add', title: '添加会员等级', type: 2, area: area,
                skin: 'dc-layer-modern', content: '?action=add',
                fixed: false, maxmin: true, shadeClose: true,
                success: function(layero, index, that){
                    layer.iframeAuto(index);
                    that.offset();
                }
            });
        }
        if (obj.event === 'del') {
            var data = checkStatus.data;
            if (data.length == 0) return;
            var ids = $.map(data, function(item){ return item.id; }).join(',');
            layer.confirm('确定要删除选中的 ' + data.length + ' 个等级吗？', {
                btn: ['确认', '取消'], icon: 3, title: '温馨提示'
            }, function(index){
                layer.close(index);
                $.post('?action=del', { ids: ids, token: TOKEN }, function(e){
                    if (e.code == 400) return layer.msg(e.msg);
                    layer.msg('会员已删除');
                    table.reload(id);
                }, 'json');
            });
        }
        if (obj.event === 'reset') {
            var isMobile = window.innerWidth < 768;
            var presets = <?= json_encode(array_values(array_map(function($k, $v){ $v['key'] = $k; return $v; }, array_keys($memberPresetOptions), $memberPresetOptions)), JSON_UNESCAPED_UNICODE) ?>;

            var html = '<div style="padding:16px 20px 6px;">';
            html += '<div style="color:#64748b;font-size:13px;margin-bottom:14px;">选择一个预设方案，系统将自动覆盖当前等级。当前共 <b>' + RESET_LEVEL_COUNT + '</b> 个等级、<b>' + RESET_USER_COUNT + '</b> 个已升级用户。多出的等级会被自动停用（不删除）。</div>';
            html += '<div style="display:grid;grid-template-columns:' + (isMobile ? '1fr' : 'repeat(3,1fr)') + ';gap:14px;">';
            for (var i = 0; i < presets.length; i++) {
                var p = presets[i];
                html += '<div class="preset-card" data-key="' + p.key + '" style="border:2px solid #e5e7eb;border-radius:12px;padding:16px;cursor:pointer;transition:all .2s;position:relative;">';
                html += '<div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">';
                html += '<i class="' + p.icon + '" style="font-size:22px;color:' + p.color + ';"></i>';
                html += '<span style="font-size:16px;font-weight:600;">' + p.name + '</span>';
                html += '<span style="background:' + p.color + ';color:#fff;font-size:11px;padding:1px 8px;border-radius:10px;">' + p.tag + '</span>';
                html += '</div>';
                html += '<div style="color:#6b7280;font-size:12px;margin-bottom:6px;">' + p.levels + ' 个等级</div>';
                html += '<div style="color:#374151;font-size:13px;line-height:1.6;margin-bottom:10px;">' + p.desc + '</div>';
                html += '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;margin-bottom:8px;">';
                html += '<div style="font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">' + p.example.title + '</div>';
                for (var j = 0; j < p.example.lines.length; j++) {
                    html += '<div style="font-size:12px;color:#334155;line-height:1.7;">' + p.example.lines[j] + '</div>';
                }
                html += '</div>';
                html += '<div style="font-size:12px;color:#059669;margin-bottom:4px;"><i class="ri-money-cny-circle-line"></i> ' + p.income + '</div>';
                if (p.auto_upgrade) {
                    html += '<div style="font-size:12px;color:#2563eb;margin-bottom:4px;line-height:1.6;"><i class="ri-arrow-up-circle-line"></i> ' + p.auto_upgrade + '</div>';
                }
                html += '<div style="font-size:11px;color:#94a3b8;line-height:1.5;"><i class="ri-lightbulb-line"></i> ' + p.tip + '</div>';
                html += '</div>';
            }
            html += '</div></div>';

            var presetIdx = layer.open({
                type: 1,
                title: '选择预设方案',
                area: isMobile ? ['96%', 'auto'] : ['880px', 'auto'],
                shadeClose: true,
                resize: false,
                content: html,
                btn: false,
                success: function(layero) {
                    layero.find('.preset-card').on('mouseenter', function(){
                        $(this).css({ 'border-color': $(this).find('i:first').css('color'), 'box-shadow': '0 2px 12px rgba(0,0,0,.08)' });
                    }).on('mouseleave', function(){
                        $(this).css({ 'border-color': '#e5e7eb', 'box-shadow': 'none' });
                    }).on('click', function(){
                        var key = $(this).data('key');
                        var pName = presets.filter(function(x){ return x.key === key; })[0].name;
                        var pLevels = presets.filter(function(x){ return x.key === key; })[0].levels;
                        layer.close(presetIdx);

                        var countdown = 5;
                        var timer2 = null;
                        var cText = function(s){ return s > 0 ? '确认应用（' + s + 's）' : '确认应用「' + pName + '」'; };
                        layer.open({
                            type: 1, title: '确认应用预设', shadeClose: false, resize: false,
                            area: isMobile ? ['94%', 'auto'] : ['460px', 'auto'],
                            content: '<div style="padding:18px 20px 10px;line-height:1.8;">'
                                + '<div style="background:#fff7ed;border:1px solid #fdba74;color:#9a3412;border-radius:8px;padding:12px 14px;margin-bottom:10px;">'
                                + '<div style="font-weight:600;margin-bottom:4px;"><i class="ri-alert-line"></i> 确认应用「' + pName + '」预设？</div>'
                                + '<div>将用 <b>' + pLevels + ' 个预设等级</b>覆盖当前前 ' + pLevels + ' 档等级的名称、价格、分销参数。</div>'
                                + '<div>多出的现有等级会被 <b>自动停用</b>（不会删除，不影响已购用户的 ID）。</div>'
                                + '<div>不足的部分会 <b>自动创建补齐</b>。</div>'
                                + '</div></div>',
                            btn: [cText(countdown), '取消'],
                            success: function(layero2){
                                var btn = layero2.find('.layui-layer-btn0');
                                btn.addClass('layui-btn-disabled').css({ 'pointer-events':'none', 'opacity':'0.6' });
                                timer2 = setInterval(function(){
                                    countdown--;
                                    btn.text(cText(countdown));
                                    if (countdown <= 0) {
                                        clearInterval(timer2); timer2 = null;
                                        btn.removeClass('layui-btn-disabled').css({ 'pointer-events':'', 'opacity':'' });
                                    }
                                }, 1000);
                            },
                            yes: function(idx){
                                if (countdown > 0) return false;
                                layer.close(idx);
                                var loadIdx = layer.load(2);
                                $.post('?action=reset_default', { token: TOKEN, confirm_reset: 1, preset: key }, function(e){
                                    layer.close(loadIdx);
                                    if (e.code == 400) return layer.msg(e.msg);
                                    layer.msg('已应用「' + pName + '」预设方案');
                                    table.reload(id);
                                }, 'json');
                                return false;
                            },
                            end: function(){ if (timer2) clearInterval(timer2); }
                        });
                    });
                }
            });
        }
    });

    // 行操作
    table.on('tool(index)', function(obj){
        var data = obj.data;
        var id = obj.config.id;
        if (obj.event === 'del') {
            layer.confirm('确定删除等级"' + data.name + '"？', {
                btn: ['确认', '取消'], icon: 3, title: '温馨提示'
            }, function(index){
                layer.close(index);
                $.post('?action=del', { ids: data.id, token: TOKEN }, function(e){
                    if (e.code == 400) return layer.msg(e.msg);
                    layer.msg('会员已删除');
                    table.reload(id);
                }, 'json');
            });
        }
        if (obj.event === 'edit') {
            var isMobile = window.innerWidth < 768;
            var area = isMobile ? ['98%', 'auto'] : ['860px', 'auto'];
            layer.open({
                id: 'edit', title: '编辑会员等级', type: 2, area: area,
                skin: 'dc-layer-modern', content: '?action=edit&id=' + data.id,
                fixed: false, maxmin: true, shadeClose: true,
                success: function(layero, index, that){
                    layer.iframeAuto(index);
                    that.offset();
                }
            });
        }
        if (obj.event === 'set_default') {
            layer.confirm('确定将“' + data.name + '”设为默认等级？新注册用户将自动分配此等级。', {
                btn: ['确认', '取消'], icon: 3, title: '设置默认等级'
            }, function(index){
                layer.close(index);
                $.post('?action=set_default', { id: data.id, token: TOKEN }, function(e){
                    if (e.code == 400) return layer.msg(e.msg);
                    layer.msg('已设为默认等级');
                    table.reload(id);
                }, 'json');
            });
        }
        // 排序操作
        var sortMap = { 'sort-top': 1, 'sort-up': 2, 'sort-down': 3, 'sort-bottom': 4 };
        if (sortMap[obj.event]) {
            $.post('?action=sort_move', { id: data.id, type: sortMap[obj.event], token: TOKEN }, function(e){
                if (e.code == 400) return layer.msg(e.msg);
                table.reload(id);
            }, 'json');
        }
    });

    // 启停切换
    form.on('switch(switch-state)', function(obj){
        var state = obj.elem.checked ? 1 : 0;
        var id = this.name;
        $.post('?action=toggle_state', { id: id, state: state, token: TOKEN }, function(e){
            if (e.code == 400) return layer.msg(e.msg);
            layer.msg('会员状态已更新');
        }, 'json');
    });

    // 复选框选中→删除按钮
    table.on('checkbox(index)', function(obj){
        var id = obj.config.id;
        var checkData = table.checkStatus(id).data;
        if (checkData.length == 0) $('#toolbar-del').addClass('layui-btn-disabled');
        else $('#toolbar-del').removeClass('layui-btn-disabled');
    });
});
</script>
