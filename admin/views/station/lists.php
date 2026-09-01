<?php defined('DC_ROOT') || exit('access denied!'); ?>
<?php
$db = Database::getInstance();
$db_prefix = DB_PREFIX;
$levels = $db->fetch_all("SELECT id, name FROM {$db_prefix}station_level ORDER BY sort ASC, id ASC");
$options_cache = $GLOBALS['CACHE']->readCache('options');
$slug_mode = isset($options_cache['station_slug_mode']) ? $options_cache['station_slug_mode'] : '0';
?>

<table class="layui-hide" id="index" lay-filter="index"></table>

<script type="text/html" id="toolbar">
    <div style="display:flex;align-items:center;justify-content:center;position:relative;padding:10px 0px 15px;border-bottom:1px solid #f0f0f0;">
        <span style="position:absolute;left:0;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">分店管理</span>
    </div>
    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
        <div class="layui-btn-container">
            <button class="layui-btn layui-btn-primary layui-border-green" lay-event="refresh"><i class="ri-refresh-line"></i></button>
            <button class="layui-btn layui-btn-sm layui-btn-normal" lay-event="addStation">添加分店</button>
            <button class="layui-btn layui-btn-warm" lay-event="recycle"><i class="ri-delete-bin-line"></i> 回收站</button>
        </div>
        <form class="layui-form" style="display: flex; align-items: center; gap: 6px; margin: 0; flex-wrap: wrap;">
            <div class="layui-input-inline layui-input-wrap" style="width: 120px; margin: 0;">
                <select name="level_id">
                    <option value="">分店等级</option>
                    <?php foreach ($levels as $lv): ?>
                    <option value="<?= $lv['id'] ?>"><?= htmlspecialchars($lv['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="layui-input-inline layui-input-wrap" style="width: 200px; margin: 0;">
                <input type="text" value="" name="keyword" placeholder="名称/域名/手机/标识码/ID" lay-affix="clear" class="layui-input">
            </div>
            <button class="layui-btn layui-btn-sm" lay-submit lay-filter="index-search">搜索</button>
            <button type="reset" class="layui-btn layui-btn-sm layui-btn-primary">重置</button>
        </form>
    </div>
</script>

<script type="text/html" id="addStationFormTpl">
    <form class="layui-form" style="padding:25px 25px 0;" lay-filter="addStationForm">
        <div class="layui-form-item">
            <label class="layui-form-label">用户 UID</label>
            <div class="layui-input-block">
                <input type="number" name="user_id" required lay-verify="required" placeholder="请输入要开通分店的用户 UID" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">分店等级</label>
            <div class="layui-input-block">
                <select name="level_id" lay-verify="required">
                    <option value="">请选择等级</option>
                    <?php foreach ($levels as $lv): ?>
                    <option value="<?= $lv['id'] ?>"><?= htmlspecialchars($lv['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">店铺名称</label>
            <div class="layui-input-block">
                <input type="text" name="name" placeholder="可留空，默认：用户名的店铺" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">网站标题</label>
            <div class="layui-input-block">
                <input type="text" name="title" placeholder="可留空，默认同店铺名称" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">网站副标题</label>
            <div class="layui-input-block">
                <input type="text" name="site_subtitle" placeholder="可留空，前台将使用全站副标题" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item" style="text-align:center;padding:10px 0;">
            <button class="layui-btn" lay-submit lay-filter="submitAddStation">确认添加</button>
        </div>
    </form>
</script>

<script type="text/html" id="tpl-userinfo">
    <div style="line-height:1.5;">
        <div style="font-weight:500;">{{= d.user_info }}</div>
        <div style="font-size:12px;color:#999;">UID:{{= d.user_id }}</div>
    </div>
</script>

<script type="text/html" id="tpl-domain">
    <div style="line-height:1.5;font-size:12px;">
        {{# if(d.domain_2){ }}<div title="{{= d.domain_2 }}">{{= d.domain_2 }}</div>{{# } }}
        {{# if(d.domain){ }}<div style="color:#4a7cf7;" title="{{= d.domain }}">{{= d.domain }}</div>{{# } }}
        {{# if(d.slug && d.slug !== 'NULL'){ }}<div style="color:#67c23a;" title="/s/{{= d.slug }}">/s/{{= d.slug }}</div>{{# } }}
        {{# if(!d.domain_2 && !d.domain && (!d.slug || d.slug === 'NULL')){ }}<span style="color:#ccc;">未配置</span>{{# } }}
    </div>
</script>

<script type="text/html" id="tpl-level">
    <span style="display:inline-block;background:#f0f5ff;color:#4a7cf7;padding:2px 10px;border-radius:4px;font-size:12px;">{{= d.level_name || '未设置' }}</span>
</script>

<script type="text/html" id="tpl-status">
    <input type="checkbox" name="{{= d.id }}" value="{{= d.id }}" title=" 启用 | 停用 " lay-skin="switch" lay-filter="switch-status" {{= parseInt(d.status) === 1 ? 'checked' : '' }}>
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
        var $ = layui.$;
        var TOKEN = '<?= LoginAuth::genToken() ?>';

        // 创建渲染实例
        window.table = table.render({
            elem: '#index',
            id: 'index',
            autoSort: false,
            url: '?action=lists_index',
            toolbar: '#toolbar',
            limits: [],
            page: false,
            lineStyle: 'height: 50px;',
            defaultToolbar: [],
            cols: [[
                {field:'id', title:'ID', width: 70, sort: true, align: 'center'},
                {field:'name', title:'店铺名称', minWidth: 140},
                {field:'user_info', title:'站长信息', minWidth: 140, templet: '#tpl-userinfo'},
                {field:'level_name', title:'等级', width: 120, align: 'center', templet: '#tpl-level'},
                {title:'域名/标识', minWidth: 180, templet: '#tpl-domain'},
                {field:'order_count', title:'订单数', width: 90, align: 'center'},
                {field:'total_commission', title:'总佣金', width: 110, align: 'right'},
                {field:'status', title:'状态', width: 100, align: 'center', templet: '#tpl-status'},
                {field:'create_time', title:'开通时间', width: 140, align: 'center'},
                {fixed: 'right', title:'操作', templet: '#operate', width: 130, align: 'center'}
            ]],
            done: function(){
                form.render('checkbox');
            },
            error: function(res, msg){
                console.log(res, msg)
            }
        });

        // 搜索提交
        form.on('submit(index-search)', function(data){
            var field = data.field;
            table.reload('index', {
                where: field
            });
            return false;
        });

        // 重置按钮：清空表单 + 清空筛选参数 + 重载
        $(document).on('click', 'form.layui-form button[type="reset"]', function(){
            setTimeout(function(){
                form.render();
                table.reload('index', {
                    where: {}
                });
            }, 0);
        });

        // 工具栏事件
        table.on('toolbar(index)', function(obj){
            var id = obj.config.id;
            switch(obj.event){
                case 'refresh':
                    table.reload(id);
                    break;
                case 'recycle':
                    var _m = window.innerWidth < 1200;
                    layer.open({
                        id:'recycle', title:'分店回收站', type:2,
                        area: _m ? ['98%','85%'] : ['1200px','800px'], skin:'dc-layer-modern',
                        content:'station_recycle.php?popup=1',
                        fixed:false, maxmin:true, shadeClose:true
                    });
                    break;
                case 'addStation':
                    var isMobile = window.innerWidth < 768;
                    var area = isMobile ? ['98%', 'auto'] : ['520px', 'auto'];
                    layer.open({
                        type: 1,
                        title: '添加分店',
                        area: area,
                        skin: 'dc-layer-modern',
                        shadeClose: true,
                        content: $('#addStationFormTpl').html(),
                        success: function(layero, idx){
                            form.render();
                            form.on('submit(submitAddStation)', function(formData){
                                $.ajax({
                                    url: '?action=lists_add_ajax',
                                    type: 'POST',
                                    dataType: 'json',
                                    data: $.extend(formData.field, { token: TOKEN }),
                                    success: function(e){
                                        if(e.code == 400) return layer.msg(e.msg);
                                        layer.close(idx);
                                        layer.msg('添加成功');
                                        table.reload(id);
                                    },
                                    error: function(err){
                                        layer.msg(err.responseJSON ? err.responseJSON.msg : '添加失败');
                                    }
                                });
                                return false;
                            });
                        }
                    });
                    break;
            }
        });

        // 行操作事件
        table.on('tool(index)', function(obj){
            var data = obj.data;
            var id = obj.config.id;

            if(obj.event === 'del'){
                layer.confirm('确定删除分店「' + data.name + '」？', {
                    btn: ['确认', '取消'], icon: 3, title: '删除确认'
                }, function(index) {
                    layer.close(index);
                    $.ajax({
                        url: '?action=lists_del', type: 'POST', dataType: 'json',
                        data: { ids: data.id, token: TOKEN },
                        success: function(e) {
                            if(e.code == 400) return layer.msg(e.msg);
                            layer.msg('分站已删除');
                            table.reload(id);
                        },
                        error: function(err) {
                            try { layer.msg(err.responseJSON.msg); } catch(e){ layer.msg('操作失败'); }
                        }
                    });
                });
            }

            if(obj.event === 'edit'){
                var isMobile = window.innerWidth < 768;
                var area = isMobile ? ['98%', 'auto'] : ['750px', 'auto'];
                layer.open({
                    id: 'station-edit',
                    title: '编辑分店 - ' + data.name,
                    type: 2,
                    area: area,
                    skin: 'dc-layer-modern',
                    content: '?action=lists_edit&id=' + data.id,
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

        // 分店启停开关
        form.on('switch(switch-status)', function(obj){
            var checked = obj.elem.checked;
            var id = this.name;
            $.ajax({
                url: '?action=lists_switch_status',
                type: 'POST',
                dataType: 'json',
                data: { id: id, status: checked ? '1' : '0', token: TOKEN },
                success: function(e){
                    if(e.code == 400){
                        obj.elem.checked = !checked;
                        form.render('checkbox');
                        return layer.msg(e.msg || '操作失败');
                    }
                    layer.msg(checked ? '分店已启用' : '分店已停用');
                },
                error: function(){
                    obj.elem.checked = !checked;
                    form.render('checkbox');
                    layer.msg('操作失败，请稍后重试');
                }
            });
        });

    });
</script>
