<?php defined('DC_ROOT') || exit('access denied!'); ?>

 <div class="layui-tabs order-tabs-wrapper" style="display:flex;align-items:center;justify-content:space-between;" lay-options="{trigger: false}">
     <ul class="layui-tabs-header">
         <li class="<?= $tab === 'profit' ? 'layui-this' : '' ?>"><a href="<?= DC_URL ?>admin/price_rule.php?tab=profit">批量加价规则</a></li>
         <li class="<?= $tab === 'single' ? 'layui-this' : '' ?>"><a href="<?= DC_URL ?>admin/price_rule.php?tab=single">单商品规则</a></li>
     </ul>
 </div>

<table class="layui-hide" id="ruleTable" lay-filter="ruleTable"></table>

 <?php if ($tab === 'profit'): ?>
 <script type="text/html" id="toolbar">
    <div style="display:flex;align-items:center;justify-content:center;position:relative;padding:10px 0px 15px;border-bottom:1px solid #f0f0f0;">
        <span style="position:absolute;left:0;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">批量加价规则</span>
    </div>
    <div class="layui-btn-container" style="display:flex;align-items:center;flex-wrap:wrap;">
        <button class="layui-btn layui-btn-primary layui-border-green" lay-event="refresh"><i class="ri-refresh-line"></i></button>
        <button type="button" class="layui-btn" lay-event="add"><i class="ri-add-line"></i> 新建规则</button>
        <button type="button" class="layui-btn layui-btn-warm" lay-event="default"><i class="ri-magic-line"></i> 一键生成默认规则</button>
        <button id="toolbar-del" class="layui-btn layui-bg-red layui-btn-disabled" lay-event="del">删除</button>
        <div class="layui-input-inline layui-input-wrap" style="width:240px;margin-left:auto;">
            <input id="search-kw" type="text" placeholder="搜索名称或ID" autocomplete="off" lay-affix="clear" class="layui-input">
        </div>
        <button class="layui-btn layui-btn-sm" lay-event="search">搜索</button>
        <button class="layui-btn layui-btn-sm layui-btn-primary" lay-event="reset">重置</button>
    </div>
    <div style="padding:0;color:#6b7280;font-size:12px;line-height:1.7;">
        作用：便宜商品多加一点价、贵的商品少加一点，系统根据成本价自动调。创建好规则后，去「会员等级 → 按成本自动调节规则」或「商品编辑」里选用即可。
    </div>
</script>
 <script type="text/html" id="stateTpl">
     <input type="checkbox" name="{{= d.id }}" value="{{= d.id }}" title=" 启用 | 停用 " lay-skin="switch" lay-filter="switch-state" {{= d.state == 1 ? 'checked' : '' }}>
 </script>
 <script type="text/html" id="rulesTpl">
     <div style="padding:4px 0;">{{= d.rules_html }}</div>
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
 <script type="text/html" id="operateTpl">
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
     var BASE = '?tab=profit';

     window.ruleTableInstance = table.render({
         id: 'ruleTable',
         elem: '#ruleTable',
         url: BASE + '&action=index',
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
             {field: 'rules_html', title: '加价档位（成本价 → 加价力度）', minWidth: 320, templet: function(d){
                 var html = d.rules_html || '<span style="color:#9ca3af;">暂无规则</span>';
                 if (typeof html === 'string' && html.indexOf('&lt;') !== -1) {
                     var decoder = document.createElement('textarea');
                     decoder.innerHTML = html;
                     html = decoder.value;
                 }
                 return '<div style="padding:4px 0;line-height:1.8;">' + html + '</div>';
             }},
             {title: '引用', minWidth: 120, templet: '#usageTpl'},
             {field: 'state', title: '状态', minWidth: 100, templet: '#stateTpl'},
             {field: 'create_time_str', title: '创建时间', minWidth: 150},
             {fixed: 'right', title: '操作', templet: '#operateTpl', width: 160, align: 'center'}
         ]]
     });

     table.on('toolbar(ruleTable)', function(obj){
         var id = obj.config.id;
         var checkStatus = table.checkStatus(id);
         if (obj.event === 'refresh') table.reload(id);
         if (obj.event === 'search') table.reload(id, { where: { keyword: $('#search-kw').val() }, page: { curr: 1 } });
         if (obj.event === 'reset') { $('#search-kw').val(''); table.reload(id, { where: { keyword: '' }, page: { curr: 1 } }); }
         if (obj.event === 'add') openEdit(0);
         if (obj.event === 'default') {
             layer.confirm('将会创建一条<b style="color:#2563eb;">内置的默认加价规则</b>（适合大多数场景）：<br><div style="margin-top:6px;padding:8px 10px;background:#f9fafb;border-radius:6px;font-size:12px;color:#4b5563;line-height:1.8;">• 成本价 0 ~ 10 元 → 加价力度 <b>100%</b>（全额加价）<br>• 成本价 10 ~ 50 元 → 加价力度 <b>80%</b>（打8折加价）<br>• 成本价 50 ~ 200 元 → 加价力度 <b>60%</b>（打6折加价）<br>• 成本价 200 ~ 1000 元 → 加价力度 <b>40%</b>（打4折加价）<br>• 成本价 1000 元以上 → 加价力度 <b>20%</b>（打2折加价）</div>', {btn: ['确认生成', '取消'], icon: 3, title: '一键生成默认规则', area: '480px'}, function(idx){
                 layer.close(idx);
                 var loadIdx = layer.load(2);
                 $.post(BASE + '&action=create_default', { token: TOKEN }, function(e){
                     layer.close(loadIdx);
                     if (e.code != 0) return layer.msg(e.msg || '生成失败', { icon: 2 });
                     layer.msg('默认规则生成成功', { icon: 1 });
                     table.reload(id);
                 }, 'json');
             });
         }
         if (obj.event === 'del') {
             var data = checkStatus.data;
             if (data.length === 0) return;
             var ids = $.map(data, function(it){ return it.id; }).join(',');
             var totalGoods = 0, totalLevels = 0;
             $.each(data, function(i, it){ totalGoods += (it.usage_goods || 0); totalLevels += (it.usage_level || 0); });
             var hasRef = totalGoods + totalLevels > 0;
             var msg = '确认删除选中的 ' + data.length + ' 条规则？';
             if (hasRef) {
                 var refParts = [];
                 if (totalGoods > 0) refParts.push(totalGoods + ' 个商品');
                 if (totalLevels > 0) refParts.push(totalLevels + ' 个等级');
                 msg += '<br><span style="color:#ef4444;">其中部分规则被 ' + refParts.join('、') + ' 引用，删除后将自动解除绑定</span>';
             }
             layer.confirm(msg, {btn: ['确认删除', '取消'], icon: 3, title: '温馨提示'}, function(idx){
                 layer.close(idx);
                 doDelete(id, ids, hasRef ? 1 : 0);
             });
         }
     });

     table.on('tool(ruleTable)', function(obj){
         var data = obj.data;
         var id = obj.config.id;
         if (obj.event === 'edit') openEdit(data.id);
         if (obj.event === 'del') {
             var gUsage = data.usage_goods || 0, lUsage = data.usage_level || 0;
             var refTotal = gUsage + lUsage;
             var msg;
             if (refTotal > 0) {
                 var parts = [];
                 if (gUsage > 0) parts.push('<b style="color:#ef4444;">' + gUsage + '</b> 个商品');
                 if (lUsage > 0) parts.push('<b style="color:#ef4444;">' + lUsage + '</b> 个等级');
                 msg = '规则「' + data.name + '」正被 ' + parts.join('、') + ' 引用，删除后将自动解除绑定。<br>确定删除？';
             } else {
                 msg = '确定删除规则「' + data.name + '」？';
             }
             layer.confirm(msg, {btn: ['确认删除', '取消'], icon: 3, title: '温馨提示'}, function(idx){
                 layer.close(idx);
                 doDelete(id, data.id, refTotal > 0 ? 1 : 0);
             });
         }
         if (obj.event === 'usage') showUsage(data.id);
     });

     form.on('switch(switch-state)', function(obj){
         var state = obj.elem.checked ? 1 : 0;
         var id = this.name;
         $.post(BASE + '&action=toggle_state', { id: id, state: state, token: TOKEN }, function(e){
             if (e.code == 400) return layer.msg(e.msg, { icon: 2 });
             layer.msg('规则状态已更新', { icon: 1 });
         }, 'json');
     });

     table.on('checkbox(ruleTable)', function(obj){
         var id = obj.config.id;
         var checkData = table.checkStatus(id).data;
         if (checkData.length === 0) $('#toolbar-del').addClass('layui-btn-disabled');
         else $('#toolbar-del').removeClass('layui-btn-disabled');
     });

     $('#search-kw').on('keydown', function(e){
         if (e.keyCode === 13) {
             table.reload('ruleTable', { where: { keyword: $(this).val() }, page: { curr: 1 } });
         }
     });

     function doDelete(tableId, ids, force) {
         $.ajax({
             url: BASE + '&action=del',
             type: 'POST',
             data: { ids: ids, force: force, token: TOKEN },
             dataType: 'json',
             success: function(e){
                 layer.msg('规则已删除', { icon: 1 });
                 table.reload(tableId);
             },
             error: function(xhr){
                 try {
                     var res = JSON.parse(xhr.responseText);
                     layer.msg(res.msg || '删除失败', { icon: 2 });
                 } catch(e) {
                     layer.msg('删除失败', { icon: 2 });
                 }
             }
         });
     }

     function openEdit(id) {
         var isMobile = window.innerWidth < 768;
         var area = isMobile ? ['98%', 'auto'] : ['720px', 'auto'];
         layer.open({
             id: 'edit', title: id > 0 ? '编辑批量加价规则' : '新建批量加价规则',
             type: 2, area: area, skin: 'dc-layer-modern',
             content: BASE + '&action=edit' + (id > 0 ? '&id=' + id : ''),
             fixed: false, maxmin: true, shadeClose: false,
             success: function(layero, index, that){
                 layer.iframeAuto(index);
                 that.offset();
             }
         });
     }

     function showUsage(id) {
         $.get(BASE + '&action=usage&id=' + id, function(e){
             if (e.code == 400) return layer.msg(e.msg, { icon: 2 });
             var d = e.data;
             var html = '<div style="padding:12px 18px;max-height:60vh;overflow-y:auto;">';
             html += '<div style="margin-bottom:10px;font-size:13px;color:#4b5563;">规则「<b style="color:#2563eb;">' + d.rule.name + '</b>」被以下对象引用：</div>';
             if (d.goods.length > 0) {
                 html += '<div style="margin-top:8px;"><b>商品 (' + d.goods.length + ')：</b></div><div style="padding-left:10px;">';
                 d.goods.forEach(function(g){ html += '<div style="padding:3px 0;">#' + g.id + ' - ' + g.title + '</div>'; });
                 html += '</div>';
             }
             if (d.levels.length > 0) {
                 html += '<div style="margin-top:12px;"><b>等级 (' + d.levels.length + ')：</b></div><div style="padding-left:10px;">';
                 d.levels.forEach(function(l){ html += '<div style="padding:3px 0;">#' + l.id + ' - ' + l.name + '</div>'; });
                 html += '</div>';
             }
             if (d.goods.length === 0 && d.levels.length === 0) {
                 html += '<div style="color:#9ca3af;padding:14px 0;">无引用</div>';
             }
             html += '</div>';
             layer.open({ title: '规则引用详情', type: 1, area: ['520px', 'auto'], shadeClose: true, content: html, skin: 'dc-layer-modern' });
         }, 'json');
     }

     window.reloadRuleTable = function(){ table.reload('ruleTable'); };
     window.reloadSingleRuleTable = window.reloadRuleTable;
 });
 </script>
 <?php else: ?>
 <script type="text/html" id="toolbar">
    <div style="display:flex;align-items:center;justify-content:center;position:relative;padding:10px 0px 15px;border-bottom:1px solid #f0f0f0;">
        <span style="position:absolute;left:0;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">单商品加价规则</span>
    </div>
    <div class="layui-btn-container" style="display:flex;align-items:center;flex-wrap:wrap;">
        <button class="layui-btn layui-btn-primary layui-border-green" lay-event="refresh"><i class="ri-refresh-line"></i></button>
        <button type="button" class="layui-btn" lay-event="add"><i class="ri-add-line"></i> 新建规则</button>
        <button id="toolbar-del" class="layui-btn layui-bg-red layui-btn-disabled" lay-event="del">删除</button>
        <div class="layui-input-inline layui-input-wrap" style="width:240px;margin-left:auto;">
            <input id="search-kw" type="text" placeholder="搜索名称或ID" autocomplete="off" lay-affix="clear" class="layui-input">
        </div>
        <button class="layui-btn layui-btn-sm" lay-event="search">搜索</button>
        <button class="layui-btn layui-btn-sm layui-btn-primary" lay-event="reset">重置</button>
    </div>
    <div style="padding:0;color:#6b7280;font-size:12px;line-height:1.7;">
        作用：给某个重点商品“开小灶”，不同等级的会员看到不同的加价。创建好规则后，去商品编辑页选择「单商品加价规则」绑定即可。
    </div>
</script>
 <script type="text/html" id="stateTpl">
     <input type="checkbox" name="{{= d.id }}" value="{{= d.id }}" title=" 启用 | 停用 " lay-skin="switch" lay-filter="switch-state" {{= d.state == 1 ? 'checked' : '' }}>
 </script>
 <script type="text/html" id="typeTpl">
     {{# if (d.type == 1) { }}
     <span class="layui-badge layui-bg-blue">固定金额</span>
     {{# } else { }}
     <span class="layui-badge layui-bg-orange">按百分比</span>
     {{# } }}
 </script>
 <script type="text/html" id="usageTpl">
     {{# if (d.usage == 0) { }}
     <span class="layui-badge-rim" style="color:#9ca3af;">未被引用</span>
     {{# } else { }}
     <a class="layui-btn layui-btn-xs layui-btn-primary" lay-event="usage">已绑定 {{= d.usage }} 个商品</a>
     {{# } }}
 </script>
 <script type="text/html" id="levelTpl">
     {{# if (d.level_count == 0) { }}
     <span style="color:#9ca3af;">还没设置会员等级</span>
     {{# } else { }}
     <span class="layui-badge-rim">已为 <b style="color:#2563eb;">{{= d.level_count }}</b> 个等级单独设置</span>
     {{# } }}
 </script>
 <script type="text/html" id="operateTpl">
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
     var BASE = '?tab=single';

     window.ruleTableInstance = table.render({
         id: 'ruleTable',
         elem: '#ruleTable',
         url: BASE + '&action=index',
         toolbar: '#toolbar',
         limits: [20, 50, 100],
         limit: 20,
         page: true,
         lineStyle: 'min-height: 42px;',
         defaultToolbar: [],
         cols: [[
             {type: 'checkbox', fixed: 'left'},
             {field: 'id', title: 'ID', width: 60},
             {field: 'name', title: '规则名称', minWidth: 140},
             {field: 'type', title: '加价方式', width: 120, templet: '#typeTpl'},
             {title: '已设置会员等级', minWidth: 150, templet: '#levelTpl'},
             {title: '绑定商品', width: 140, templet: '#usageTpl'},
             {field: 'state', title: '状态', width: 100, templet: '#stateTpl'},
             {field: 'create_time_str', title: '创建时间', minWidth: 150},
             {fixed: 'right', title: '操作', templet: '#operateTpl', width: 160, align: 'center'}
         ]]
     });

     table.on('toolbar(ruleTable)', function(obj){
         var id = obj.config.id;
         var checkStatus = table.checkStatus(id);
         if (obj.event === 'refresh') table.reload(id);
         if (obj.event === 'search') table.reload(id, { where: { keyword: $('#search-kw').val() }, page: { curr: 1 } });
         if (obj.event === 'reset') { $('#search-kw').val(''); table.reload(id, { where: { keyword: '' }, page: { curr: 1 } }); }
         if (obj.event === 'add') openEdit(0);
         if (obj.event === 'del') {
             var data = checkStatus.data;
             if (data.length === 0) return;
             var ids = $.map(data, function(it){ return it.id; }).join(',');
             var totalUsage = 0;
             $.each(data, function(i, it){ totalUsage += (it.usage || 0); });
             var msg = '确认删除选中的 ' + data.length + ' 条规则？';
             if (totalUsage > 0) msg += '<br><span style="color:#ef4444;">其中部分规则被商品引用，删除后将自动解除绑定</span>';
             layer.confirm(msg, {btn: ['确认删除', '取消'], icon: 3, title: '温馨提示'}, function(idx){
                 layer.close(idx);
                 doDelete(id, ids, totalUsage > 0 ? 1 : 0);
             });
         }
     });

     table.on('tool(ruleTable)', function(obj){
         var data = obj.data;
         var id = obj.config.id;
         if (obj.event === 'edit') openEdit(data.id);
         if (obj.event === 'del') {
             var hasUsage = (data.usage || 0) > 0;
             var msg = hasUsage
                 ? '规则「' + data.name + '」正被 <b style="color:#ef4444;">' + data.usage + '</b> 个商品引用，删除后将自动解除绑定。<br>确定删除？'
                 : '确定删除规则「' + data.name + '」？';
             layer.confirm(msg, {btn: ['确认删除', '取消'], icon: 3, title: '温馨提示'}, function(idx){
                 layer.close(idx);
                 doDelete(id, data.id, hasUsage ? 1 : 0);
             });
         }
         if (obj.event === 'usage') showUsage(data.id);
     });

     form.on('switch(switch-state)', function(obj){
         var state = obj.elem.checked ? 1 : 0;
         var id = this.name;
         $.post(BASE + '&action=toggle_state', { id: id, state: state, token: TOKEN }, function(e){
             if (e.code == 400) return layer.msg(e.msg, { icon: 2 });
             layer.msg('规则状态已更新', { icon: 1 });
         }, 'json');
     });

     table.on('checkbox(ruleTable)', function(obj){
         var id = obj.config.id;
         var checkData = table.checkStatus(id).data;
         if (checkData.length === 0) $('#toolbar-del').addClass('layui-btn-disabled');
         else $('#toolbar-del').removeClass('layui-btn-disabled');
     });

     $('#search-kw').on('keydown', function(e){
         if (e.keyCode === 13) {
             table.reload('ruleTable', { where: { keyword: $(this).val() }, page: { curr: 1 } });
         }
     });

     function doDelete(tableId, ids, force) {
         $.ajax({
             url: BASE + '&action=del',
             type: 'POST',
             data: { ids: ids, force: force, token: TOKEN },
             dataType: 'json',
             success: function(e){
                 layer.msg('规则已删除', { icon: 1 });
                 table.reload(tableId);
             },
             error: function(xhr){
                 try {
                     var res = JSON.parse(xhr.responseText);
                     layer.msg(res.msg || '删除失败', { icon: 2 });
                 } catch(e) {
                     layer.msg('删除失败', { icon: 2 });
                 }
             }
         });
     }

     function openEdit(id) {
         var isMobile = window.innerWidth < 768;
         var area = isMobile ? ['98%', 'auto'] : ['820px', 'auto'];
         layer.open({
             id: 'edit', title: id > 0 ? '编辑单商品加价规则' : '新建单商品加价规则',
             type: 2, area: area, skin: 'dc-layer-modern',
             content: BASE + '&action=edit' + (id > 0 ? '&id=' + id : ''),
             fixed: false, maxmin: true, shadeClose: false,
             success: function(layero, index, that){
                 layer.iframeAuto(index);
                 that.offset();
             }
         });
     }

     function showUsage(id) {
         $.get(BASE + '&action=usage&id=' + id, function(e){
             if (e.code == 400) return layer.msg(e.msg, { icon: 2 });
             var d = e.data;
             var html = '<div style="padding:12px 18px;max-height:60vh;overflow-y:auto;">';
             html += '<div style="margin-bottom:10px;font-size:13px;color:#4b5563;">规则「<b style="color:#2563eb;">' + d.rule.name + '</b>」被以下商品引用：</div>';
             if (d.goods.length > 0) {
                 html += '<div style="padding-left:4px;">';
                 d.goods.forEach(function(g){ html += '<div style="padding:4px 0;">#' + g.id + ' - ' + g.title + '</div>'; });
                 html += '</div>';
             } else {
                 html += '<div style="color:#9ca3af;padding:14px 0;">无商品引用</div>';
             }
             html += '</div>';
             layer.open({ title: '规则引用详情', type: 1, area: ['520px', 'auto'], shadeClose: true, content: html, skin: 'dc-layer-modern' });
         }, 'json');
     }

     window.reloadSingleRuleTable = function(){ table.reload('ruleTable'); };
     window.reloadRuleTable = window.reloadSingleRuleTable;
 });
 </script>
 <?php endif; ?>
