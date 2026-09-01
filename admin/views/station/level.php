<?php defined('DC_ROOT') || exit('access denied!'); ?>

<style>
    .perm-tags { display:flex; flex-wrap:nowrap; gap:4px; white-space:nowrap; }
    .perm-tag { display:inline-flex; align-items:center; gap:3px; padding:2px 8px; border-radius:999px; font-size:12px; font-weight:600; line-height:20px; }
    .perm-tag.on { background:rgba(16,185,129,.12); color:#059669; }
    .perm-tag.off { background:rgba(156,163,175,.1); color:#9ca3af; text-decoration:line-through; }
</style>

<table class="layui-hide" id="index" lay-filter="index"></table>
<script type="text/html" id="toolbar">
    <div style="display:flex;align-items:center;justify-content:center;position:relative;padding:10px 0px 15px;border-bottom:1px solid #f0f0f0;">
        <span style="position:absolute;left:0;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">分店等级</span>
    </div>
    <div class="layui-btn-container">
        <button class="layui-btn layui-btn-primary layui-border-green" lay-event="refresh"><i class="ri-refresh-line"></i></button>
        <button type="button" class="layui-btn" lay-event="add">添加</button>
        <button id="toolbar-del" class="layui-btn  layui-bg-red layui-btn-disabled" lay-event="del">删除</button>
        <button type="button" class="layui-btn layui-btn-warm" lay-event="reset">选择预设方案</button>
    </div>
</script>

<script type="text/html" id="tpl-perms">
    <div class="perm-tags">
        {{# var map = {is_domain:'域名',perm_setprice:'改价',perm_goodsstate:'上下架',perm_tpl:'模板',perm_config:'配置'}; }}
        {{# for(var k in map){ }}
            <span class="perm-tag {{= d[k]==='y'?'on':'off' }}">{{= map[k] }}</span>
        {{# } }}
    </div>
</script>

<script type="text/html" id="nameTpl">
    <span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:9px;background:#eff6ff;color:#2563eb;margin-right:6px;vertical-align:middle;overflow:hidden;">
    {{# if(d.icon_image){ }}
        <img src="{{ d.icon_image }}" style="width:100%;height:100%;object-fit:cover;">
    {{# } else { }}
        <i class="{{ d.icon || 'ri-store-2-line' }}" style="font-size:17px;"></i>
    {{# } }}
    </span>
    {{ d.name }}
</script>

<script type="text/html" id="usingTpl">
    <input type="checkbox" name="{{= d.id }}" value="{{= d.id }}" title=" 启用 | 停用 " lay-skin="switch" lay-filter="switch-using" {{= d.using === 'y' ? 'checked' : '' }}>
</script>

<script type="text/html" id="operate">
    <div class="layui-clear-space">
        <a class="layui-btn layui-btn-xs" lay-event="edit"><i class="ri-edit-line"></i> 编辑</a>
        <a class="layui-btn layui-btn-xs layui-bg-red" lay-event="del"><i class="ri-delete-bin-line"></i></a>
    </div>
</script>


<script>
    layui.use(['table', 'form'], function(){
        var table = layui.table;
        var form = layui.form;
        var TOKEN = '<?= LoginAuth::genToken() ?>';
        var presets = [
            {
                key: 'a', name: '基础版', tag: '快速上手', icon: 'ri-rocket-line', color: '#16a34a', levels: 2,
                desc: '只有 2 个等级，最简单的分店模式。免费版+付费全功能版。',
                details: ['基础分店：免费，模板+配置+提现', '高级分店：99元，全功能解锁'],
                gate: '基础分店不限制；高级分店需会员第2档',
                auto_upgrade: '高级分店：销售≥50元 或 订单≥30单即自动升级',
                tip: '适合刚开始做分店系统的商家，后续可手动添加更多等级。'
            },
            {
                key: 'b', name: '标准版', tag: '多数适用', icon: 'ri-building-line', color: '#2563eb', levels: 3,
                desc: '3 个等级，体验→专业→旗舰，逐级解锁功能，费率递减。',
                details: ['体验版：免费，仅模板+配置', '专业版：99元，+域名+改价+提现', '旗舰版：299元，+供货+下级分店'],
                gate: '体验版不限制；专业版需会员第2档；旗舰版需会员第3档',
                auto_upgrade: '专业版：销售≥500元 或 30单；旗舰版：销售≥2000元 或 100单 或 运营≥30天 或 下级≥3店',
                tip: '兼顾免费引流与付费升级，适合大多数分店场景。'
            },
            {
                key: 'c', name: '完整版', tag: '精细运营', icon: 'ri-building-4-line', color: '#ea580c', levels: 5,
                desc: '5 个等级，从免费到旗舰全覆盖，费率阶梯递减，功能逐步解锁。',
                details: ['免费版：0元，仅配置', '入门版：29元，+模板+提现', '标准版：99元，+域名+改价', '专业版：199元，+供货', '旗舰版：399元，+下级分店'],
                gate: '免费/入门不限制；标准版需会员第2档；专业版需第3档；旗舰版需第4档',
                auto_upgrade: '入门版：销售≥200元；标准版：销售≥500元；专业版：销售+订单+天数全达标；旗舰版：销售+订单+天数+下级全达标',
                tip: '适合需要精细化等级运营、多档付费的成熟平台。'
            }
        ];
        // 创建渲染实例
        window.table = table.render({
            elem: '#index',
            autoSort: false,
            url: '?action=level_index',
            toolbar: '#toolbar',
            limits: [],
            page: false,
            lineStyle: 'height: auto;',
            defaultToolbar: [],
            cols: [[
                {type: 'checkbox', fixed: 'left'},
                {field:'sort', title:'排序', width: 70, align:'center' },
                {field:'name', title:'等级名称', width: 150, templet: '#nameTpl' },
                {field:'price', title:'价格', width:80 },
                {field:'member_gate_name', title:'开通门槛', width: 100, align:'center' },
                {field:'perms', title:'权限', width: 250, templet: '#tpl-perms' },
                {field:'service_change_fmt', title:'供货费', width: 80 },
                {field:'upgrade_desc', title:'自动升级', minWidth: 380 },
                {field:'using', title:'状态', width: 90, templet: '#usingTpl'},
                {fixed: 'right', title:'操作', templet: '#operate', width: 130, align: 'center'}
            ]],

            error: function(res, msg){
                console.log(res, msg)
            }
        });

        // 工具栏事件
        table.on('toolbar(index)', function(obj){
            var id = obj.config.id;
            var checkStatus = table.checkStatus(id);
            if(obj.event == 'refresh'){
                table.reload(id);
            }
            if(obj.event == 'add'){
                let isMobile = window.innerWidth < 768;
                let area = isMobile ? ['98%', 'auto']  : ['750px', 'auto'];
                layer.open({
                    id: 'add',
                    title: '添加分店等级',
                    type: 2,
                    area: area,
                    skin: 'dc-layer-modern',
                    content: '?action=level_add',
                    fixed: false,
                    maxmin: true,
                    shadeClose: true,
                    success: function(layero, index, that){
                        layer.iframeAuto(index);
                        that.offset();
                    }
                });
            }
            if(obj.event == 'reset'){
                var isMobile = window.innerWidth < 768;
                var html = '<div style="padding:16px 20px 6px;">';
                html += '<div style="color:#64748b;font-size:13px;margin-bottom:14px;">选择一个预设方案，系统将用预设等级覆盖当前等级配置。多出的现有等级不会删除，仅排序后移。</div>';
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
                    for (var j = 0; j < p.details.length; j++) {
                        html += '<div style="font-size:12px;color:#334155;line-height:1.7;">• ' + p.details[j] + '</div>';
                    }
                    html += '</div>';
                    if (p.gate) {
                        html += '<div style="font-size:12px;color:#2563eb;margin-bottom:4px;line-height:1.6;"><i class="ri-lock-line"></i> ' + p.gate + '</div>';
                    }
                    if (p.auto_upgrade) {
                        html += '<div style="font-size:12px;color:#059669;margin-bottom:4px;line-height:1.6;"><i class="ri-arrow-up-circle-line"></i> ' + p.auto_upgrade + '</div>';
                    }
                    html += '<div style="font-size:11px;color:#94a3b8;line-height:1.5;"><i class="ri-lightbulb-line"></i> ' + p.tip + '</div>';
                    html += '</div>';
                }
                html += '</div></div>';

                var presetIdx = layer.open({
                    type: 1, title: '选择预设方案', area: isMobile ? ['96%', 'auto'] : ['880px', 'auto'],
                    shadeClose: true, resize: false, content: html, btn: false,
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
                                    + '<div style="background:#fff7ed;border:1px solid #fdba74;color:#9a3412;border-radius:8px;padding:12px 14px;">'
                                    + '<div style="font-weight:600;margin-bottom:4px;"><i class="ri-alert-line"></i> 确认应用「' + pName + '」预设？</div>'
                                    + '<div>将用 <b>' + pLevels + ' 个预设等级</b>覆盖当前等级的名称、价格、权限、费率。</div>'
                                    + '<div>多出的现有等级 <b>不会删除</b>，仅排序后移。</div>'
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
                                    $.post('?action=level_reset_ajax', { token: TOKEN, confirm_reset: 1, preset: key }, function(e){
                                        layer.close(loadIdx);
                                        if (e.code !== 0 && e.code !== '0') return layer.msg(e.msg || '操作失败', {icon: 2});
                                        layer.msg('已应用「' + pName + '」预设方案', {icon: 1});
                                        table.reload(id);
                                    }, 'json').fail(function(xhr){
                                        layer.close(loadIdx);
                                        var msg = '请求失败';
                                        try { var r = JSON.parse(xhr.responseText); if(r.msg) msg = r.msg; } catch(ex){}
                                        layer.msg(msg, {icon: 2});
                                    });
                                    return false;
                                },
                                end: function(){ if (timer2) clearInterval(timer2); }
                            });
                        });
                    }
                });
            }
            if(obj.event == 'del'){
                var data = checkStatus.data;
                if(data.length == 0) return;
                var ids = $.map(data, function(item) {
                    return item.id;
                }).join(',');
                layer.confirm('确定要删除所选等级？', {
                    btn: ['确认', '取消'],
                    icon: 3,
                    title: '温馨提示'
                }, function(index) {
                    layer.close(index);
                    $.ajax({
                        url: '?action=level_del',
                        type: 'POST',
                        dataType: 'json',
                        data: { ids: ids, token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e) {
                            if(e.code == 400) return layer.msg(e.msg);
                            layer.msg('等级已删除');
                            table.reload(id);
                        },
                        error: function(err) {
                            layer.msg(err.responseJSON.msg);
                        }
                    });
                });
            }
        });

        // 触发单元格工具事件
        table.on('tool(index)', function(obj){
            var data = obj.data;
            var id = obj.config.id;
            if(obj.event == 'del'){
                layer.confirm('确定删除？', {
                    btn: ['确认', '取消'],
                    icon: 3,
                    title: '温馨提示'
                }, function(index) {
                    layer.close(index);
                    $.ajax({
                        url: '?action=level_del',
                        type: 'POST',
                        dataType: 'json',
                        data: { ids: data.id, token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e) {
                            if(e.code == 400) return layer.msg(e.msg);
                            layer.msg('等级已删除');
                            table.reload(id);
                        },
                        error: function(err) {
                            layer.msg(err.responseJSON.msg);
                        }
                    });
                });
            }
            if(obj.event === 'edit'){
                let isMobile = window.innerWidth < 768;
                let area = isMobile ? ['98%', 'auto']  : ['750px', 'auto'];
                layer.open({
                    id: 'edit',
                    title: '编辑分店等级',
                    type: 2,
                    area: area,
                    skin: 'dc-layer-modern',
                    content: '?action=level_edit&id=' + data.id,
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

        // 启停开关
        form.on('switch(switch-using)', function(obj){
            var status = obj.elem.checked ? 'y' : 'n';
            var id = this.name;
            $.post('?action=level_switch_using', { id: id, status: status, token: TOKEN }, function(e){
                if (e.code == 400) return layer.msg(e.msg);
                layer.msg('等级状态已更新');
            }, 'json');
        });

        // 触发表格复选框选择
        table.on('checkbox(index)', function(obj){
            var id = obj.config.id;
            var checkData = table.checkStatus(id).data;
            if(checkData.length == 0){
                $('#toolbar-del').addClass('layui-btn-disabled');
            }else{
                $('#toolbar-del').removeClass('layui-btn-disabled');
            }
        });

    });
</script>


