<?php defined('DC_ROOT') || exit('access denied!'); ?>

<table class="layui-hide" id="index" lay-filter="index"></table>
<script type="text/html" id="toolbar">
    <div style="display:flex;align-items:center;justify-content:center;position:relative;padding:10px 0px 15px;border-bottom:1px solid #f0f0f0;">
        <span style="position:absolute;left:0;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;"><?= $type === 'blog' ? '文章分类' : '商品分类' ?></span>
    </div>
    <div class="layui-btn-container">
        <button class="layui-btn layui-btn-primary layui-border-green" lay-event="refresh">
            <i class="ri-refresh-line" style=""></i>
        </button>
        <button type="button" class="layui-btn" lay-event="add">添加</button>
        <button id="toolbar-del" class="layui-btn  layui-bg-red layui-btn-disabled" lay-event="del">删除</button>
    </div>
</script>
<script type="text/html" id="cover">
    <div class="layui-clear-space">
        <a href="javascript:;" data-id="{{ d.id }}" lay-event="img">
            <img onerror="this.onerror=null; this.src='./views/images/null.png'" class="cover" data-img="{{ d.sortimg }}" src="{{ d.sortimg }}" style="width: 40px; border-radius: 3px;" />
        </a>
    </div>
</script>
<script type="text/html" id="sorticonTpl">
    {{# if(d.sorticon) { }}
        <span title="{{ d.sorticon }}" style="display:inline-flex;align-items:center;justify-content:center;width:100%;">
            <i class="{{ d.sorticon }}" style="font-size:20px;color:#333;"></i>
        </span>
    {{# } else { }}
        <span style="color:#bbb;">未设置</span>
    {{# } }}
</script>
<script type="text/html" id="aliasTpl">
    {{# if(d.alias) { }}
        <span title="{{ d.alias }}">{{ d.alias }}</span>
    {{# } else { }}
        <span style="color:#bbb;">未设置</span>
    {{# } }}
</script>
<script type="text/html" id="parentSortTpl">
    <span title="{{ d.parent_sortname || '无' }}">{{ d.parent_sortname || '无' }}</span>
</script>
<script type="text/html" id="titleTpl">
    {{# if(d.title_admin) { }}
        <span title="{{ d.title_admin }}">{{ d.title_admin }}</span>
    {{# } else { }}
        <span style="color:#bbb;">未设置</span>
    {{# } }}
</script>
<script type="text/html" id="kwTpl">
    {{# if(d.kw) { }}
        <span title="{{ d.kw }}">{{ d.kw }}</span>
    {{# } else { }}
        <span style="color:#bbb;">未设置</span>
    {{# } }}
</script>
<script type="text/html" id="descTpl">
    {{# if(d.description) { }}
        <span title="{{ d.description }}">{{ d.description }}</span>
    {{# } else { }}
        <span style="color:#bbb;">未设置</span>
    {{# } }}
</script>
<script type="text/html" id="templateTpl">
    {{# if(d.template) { }}
        <span title="{{ d.template }}">{{ d.template }}</span>
    {{# } else { }}
        <span style="color:#bbb;">默认</span>
    {{# } }}
</script>
<script type="text/html" id="pageCountTpl">
    {{# if(parseInt(d.page_count || 0) > 0) { }}
        <span>{{ d.page_count }}</span>
    {{# } else { }}
        <span style="color:#bbb;">全局</span>
    {{# } }}
</script>


<script type="text/html" id="is_on_shelf">
    <input type="checkbox" name="{{= d.id }}" value="{{= d.id }}" title=" ON |OFF " lay-skin="switch" lay-filter="switch" {{= d.is_on_shelf == 1 ? "checked" : "" }}>
</script>
<script type="text/html" id="type">
    <div class="layui-clear-space">
        <span>{{ d.type_text }}</span>
    </div>
</script>
<script type="text/html" id="stock">
    <div class="layui-clear-space">
        {{ d.stock }}
    </div>
</script>

<script type="text/html" id="operate">
    <div class="layui-clear-space">
        <a class="layui-btn" lay-event="edit">编辑</a>
        <a class="layui-btn layui-bg-red" lay-event="del">删除</a>
    </div>
</script>


<script>
    layui.use(['table'], function(){
        var table = layui.table;
        var form = layui.form;
        // 创建渲染实例
        window.table = table.render({
            elem: '#index',
            autoSort: false,
            url: '?action=index&type=<?= $type ?>', // 此处为静态模拟数据，实际使用时需换成真实接口
            toolbar: '#toolbar',
            limits: [],
            page: false,
            lineStyle: 'height: 30px;',
            defaultToolbar: [],
            cols: [[
                {type: 'checkbox', fixed: 'left'},
                {field:'sortimg', title:'图片', width: 50, templet: '#cover', align: 'center'},
                <?php if ($type === 'blog'): ?>
                {field:'sorticon', title:'图标', width: 50, templet: '#sorticonTpl'},
                <?php endif; ?>
                {field:'sortname', title:'分类名称', minWidth: 120 },
                <?php if ($type === 'blog'): ?>
                {field:'alias', title:'别名', Width: 100, templet: '#aliasTpl'},
                {field:'parent_sortname', title:'父分类', minWidth: 120, templet: '#parentSortTpl'},
                {field:'template', title:'模板', minWidth: 130, templet: '#templateTpl'},
                {field:'page_count', title:'每页数', width: 85, align: 'center', templet: '#pageCountTpl'},
                {field:'title_admin', title:'标题', minWidth: 180, templet: '#titleTpl'},
                {field:'kw', title:'关键词', minWidth: 180, templet: '#kwTpl'},
                {field:'description', title:'描述', minWidth: 220, templet: '#descTpl'},
                <?php endif; ?>
                {field:'taxis', title:'排序', width: 70, align: 'center' },
                {fixed: 'right', title:'操作', templet: '#operate', width: 150, align: 'center'}
            ]],

            error: function(res, msg){
                console.log(res, msg)
            }
        });


        // 工具栏事件
        table.on('toolbar(index)', function(obj){
            var id = obj.config.id;
            var checkStatus = table.checkStatus(id);
            var othis = lay(this);
            if(obj.event == 'refresh'){
                table.reload(id);
            }
            if(obj.event == 'add'){
                let isMobile = window.innerWidth < 768;
                let area = isMobile ? ['98%', 'auto']  : ['700px', 'auto'];
                layer.open({
                    id: 'add',
                    title: '添加',
                    type: 2,
                    area: area,
                    // skin: 'layui-layer-win10',
                    skin: 'dc-layer-modern',
                    content: '?action=add&type=<?= $type ?>',
                    fixed: false, // 不固定
                    maxmin: true,
                    shadeClose: true,
                    success: function(layero, index, that){
                        layer.iframeAuto(index); // 让 iframe 高度自适应
                        that.offset(); // 重新自适应弹层坐标
                    }
                });
            }
            if(obj.event == 'del'){
                var data = checkStatus.data;
                if(data.length == 0){
                    return;
                }
                var ids = $.map(data, function(item) {
                    return item.sid; // 提取每个对象的uid
                }).join(',');
                layer.confirm('确定要删除的数据？', {
                    btn: ['确认', '取消'], // 按钮
                    icon: 3,             // 图标，3表示问号
                    title: '温馨提示'
                }, function(index) {
                    layer.close(index); // 关闭对话框
                    $.ajax({
                        url: '?action=del',
                        type: 'POST',
                        dataType: 'json',
                        data: { ids: ids, type: '<?= $type ?>', token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e) {
                            if(e.code == 400){
                                return layer.msg(e.msg)
                            }
                            layer.msg('分类已删除');
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
        table.on('tool(index)', function(obj){ // 双击 toolDouble
            var data = obj.data; // 获得当前行数据
            var id = obj.config.id;
            if(obj.event == 'del'){
                layer.confirm('确定删除？', {
                    btn: ['确认', '取消'], // 按钮
                    icon: 3,             // 图标，3表示问号
                    title: '温馨提示'
                }, function(index) {
                    layer.close(index); // 关闭对话框
                    $.ajax({
                        url: '?action=del',
                        type: 'POST',
                        dataType: 'json',
                        data: { ids: data.sid, type: '<?= $type ?>', token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e) {
                            if(e.code == 400){
                                return layer.msg(e.msg)
                            }
                            layer.msg('分类已删除');
                            table.reload(id);
                        },
                        error: function(err) {
                            layer.msg(err.responseJSON.msg);
                        }
                    });
                });
            }

            if(obj.event === 'img'){
                layer.photos({
                    photos: {
                        "title": data.title,
                        "start": 0,
                        "data": [
                            {
                                "alt": data.title,
                                "pid": 1,
                                "src": data.sortimg,
                            }
                        ]
                    }
                });
            }
            if(obj.event === 'edit'){
                let isMobile = window.innerWidth < 768;
                let area = isMobile ? ['98%', 'auto']  : ['700px', 'auto'];
                layer.open({
                    id: 'edit',
                    title: '编辑',
                    type: 2,
                    area: area,
                    skin: 'dc-layer-modern',
                    content: '?action=edit&type=<?= $type ?>&id=' + data.sid,
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


    });
</script>

<?php if($type == 'goods'): ?>

<?php else: ?>
    
<?php endif; ?>

