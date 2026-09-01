<?php defined('DC_ROOT') || exit('access denied!'); ?>
<style>
    body{
        overflow: hidden;
    }
    .layui-table-view-1 .layui-table-body .layui-table tr .layui-table-cell{
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 1; /* 限制显示2行 */
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .layui-table-tool-temp{
        padding-right: 0;
    }
</style>
<div style="padding: 20px 10px;" id="open-box">

    <?php if($goods['type'] == 'duli' || $goods['type'] == 'guding'): ?>
    <div class="layui-tabs" style="margin-bottom: 12px;" lay-options="{trigger: false}">
        <ul class="layui-tabs-header">
            <li class="layui-this"><a href="stock.php?action=index_ws&goods_id=<?= $goods_id ?>">未售出</a></li>
            <li><a href="stock.php?action=index_ys&goods_id=<?= $goods_id ?>">已售出</a></li>
        </ul>
    </div>
    <?php endif; ?>
    <table class="layui-hide" id="stock_index_ws" lay-filter="stock_index_ws"></table>
</div>
<script type="text/html" id="toolbar">
    <div style="display:flex;align-items:center;justify-content:center;position:relative;padding:10px 0 15px;border-bottom:1px solid #f0f0f0;">
        <span style="position:absolute;left:0;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">未售出库存</span>
    </div>
    <div class="layui-btn-container" style="padding-bottom:10px;">
        <button class="layui-btn layui-btn-primary layui-border-green" lay-event="refresh"><i class="ri-refresh-line"></i></button>
        <button type="button" class="layui-btn" lay-event="add">添加</button>
        <button id="toolbar-del" class="layui-btn layui-bg-red layui-btn-disabled" lay-event="del">删除</button>
        <button type="button" class="layui-btn layui-bg-blue" lay-event="export">导出</button>
    </div>
    <?php if($goods['type'] != 'guding' && $goods['type'] != 'xuni'): ?>
    <form class="layui-form" style="display:flex;align-items:center;justify-content:flex-end;gap:6px;margin:0;padding-bottom:10px;">
        <?php if($goods['is_sku'] == 'y'): ?>
        <div class="layui-input-inline layui-input-wrap" style="width:160px;margin:0;">
            <select name="sku" id="search-sku">
                <option value="">商品规格</option>
                <?php foreach($sku_list as $val): ?>
                    <option value="<?= $val['sku'] ?>"><?= $val['sku_name'] ?> (<?= $val['stock_count'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div class="layui-input-inline layui-input-wrap" style="width:200px;margin:0;">
            <input id="search-keyword" type="text" name="keyword" placeholder="卡密内容/批次号" lay-affix="clear" class="layui-input">
        </div>
        <button class="layui-btn layui-btn-sm" lay-submit lay-filter="index-search">搜索</button>
        <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" id="stock-btn-reset">重置</button>
    </form>
    <?php endif; ?>
</script>


<script type="text/html" id="title">
    <div class="layui-clear-space">
        <span>
            {{#  if(d.type == 'duli'){ }}
            <span class="layui-badge layui-bg-green">{{ d.type_text }}</span>
            {{#  }else if(d.type == 'xuni'){ }}
            <span class="layui-badge layui-bg-red">{{ d.type_text }}</span>
            {{#  }else if(d.type == 'guding'){ }}
            <span class="layui-badge layui-bg-blue">{{ d.type_text }}</span>
            {{#  }else if(d.type == 'post'){ }}
            <span class="layui-badge layui-bg-purple">{{ d.type_text }}</span>
            {{#  }else{ }}
            <span class="layui-badge layui-bg-orange">未知类型</span>
            {{#  } }}

        </span>
        <span style="margin-left: 8px;">{{ d.title }}</span>
    </div>
</script>
<script type="text/html" id="is_on_shelf">
    类型
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
        <a class="layui-btn layui-btn-xs layui-bg-blue" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-xs layui-bg-red" lay-event="del">删除</a>
    </div>
</script>


<script>
    layui.use(['table'], function(){
        var table = layui.table;
        var form = layui.form;
        // 创建渲染实例
        window.ws_table = table.render({
            elem: '#stock_index_ws',
            autoSort: false,
            url: 'stock.php?action=index_ws_ajax&goods_id=<?= $goods_id ?>', // 此处为静态模拟数据，实际使用时需换成真实接口
            toolbar: '#toolbar',
            limits: [10,20,30,50,100],
            page: true,
            lineStyle: 'height: 30px;',
            defaultToolbar: [],
            maxHeight : 'full-78',
            cols: [[
                {type: 'checkbox', fixed: 'left'},
                {field:'sku_name', title:'规格', align: 'center', minWidth: 100 },
                {field:'content', title:'卡密内容', align: 'center', minWidth: 200 },
                {field:'quantity', title:'库存', align: 'center', minWidth: 100, sort: true },
                {field:'create_time', title:'添加时间', sort: true, minWwidth: 150, align: 'center'},
                {fixed: 'right', title:'操作', templet: '#operate', minWidth: 210, align: 'center'}
            ]],

            error: function(res, msg){
                console.log(res, msg)
            }
        });
        <?php if($goods['is_sku'] == 'n'): ?>
        // 设置对应列的显示或隐藏
        table.hideCol('stock_index_ws', {
            field: 'sku_name', // 对应表头的 field 属性值
            hide: true // `true` or `false`
        });
        <?php endif; ?>
        <?php if($goods['type'] == 'xuni' || $goods['type'] == 'post'): ?>
        // 设置对应列的显示或隐藏
        table.hideCol('stock_index_ws', {
            field: 'content', // 对应表头的 field 属性值
            hide: true // `true` or `false`
        });
        <?php endif; ?>
        <?php if($goods['type'] == 'duli'): ?>
        // 设置对应列的显示或隐藏
        table.hideCol('stock_index_ws', {
            field: 'quantity', // 对应表头的 field 属性值
            hide: true // `true` or `false`
        });
        <?php endif; ?>




        // 搜索提交
        form.on('submit(index-search)', function(data){
            table.reload('stock_index_ws', {
                page: { curr: 1 },
                where: data.field
            });
            return false;
        });

        // 重置搜索
        $(document).on('click', '#stock-btn-reset', function(){
            var $bar = $('.layui-table-tool-temp');
            $bar.find('input[name="keyword"]').val('');
            $bar.find('select[name="sku"]').val('');
            form.render('select');
            table.reload('stock_index_ws', { page:{curr:1}, where:{keyword:'', sku:''} });
        });



        // 工具栏事件
        table.on('toolbar(stock_index_ws)', function(obj){
            var id = obj.config.id;
            var checkStatus = table.checkStatus(id);
            var othis = lay(this);
            if(obj.event == 'refresh'){
                table.reload(id);
            }
            if(obj.event == 'del'){
                var data = checkStatus.data;
                if(data.length == 0){
                    return false;
                }
                var ids = $.map(data, function(item) {
                    return item.stock_id; // 提取每个对象的uid
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
                        data: { ids: ids, goods_id: <?= $goods['id'] ?>, token: '<?= LoginAuth::genToken() ?>' },
                        success: function(res) {
                            layer.msg('库存已删除');
                            table.reload(id);
                        },
                        error: function(err) {
                            layer.msg(err.responseJSON.msg);
                        }
                    });
                });
            }
            if(obj.event == 'add'){
                let isMobile = window.innerWidth < 768;
                let area = isMobile ? ['98%', 'auto']  : ['700px', 'auto'];
                layer.open({
                    id: 'add_stock',
                    title: '添加库存',
                    type: 2,
                    area: area,
                    // skin: 'layui-layer-win10',
                    skin: 'dc-layer-modern',
                    content: 'stock.php?action=stock_add_new&goods_id=<?= $goods_id ?>',
                    fixed: false, // 不固定
                    maxmin: true,
                    shadeClose: true,
                    success: function(layero, index, that){
                        layer.iframeAuto(index); // 让 iframe 高度自适应
                        that.offset(); // 重新自适应弹层坐标
                    }
                });
            }
            if(obj.event == 'export'){
                let isMobile = window.innerWidth < 768;
                let area = isMobile ? ['98%', 'auto']  : ['500px', 'auto'];
                layer.open({
                    id: 'export',
                    title: '导出库存',
                    type: 2,
                    area: area,
                    // skin: 'layui-layer-win10',
                    skin: 'dc-layer-modern',
                    content: 'stock.php?action=export_page&goods_id=<?= $goods_id ?>',
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

        // 触发单元格工具事件
        table.on('tool(stock_index_ws)', function(obj){ // 双击 toolDouble
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
                        data: { ids: data.stock_id, goods_id: <?= $goods['id'] ?>, token: '<?= LoginAuth::genToken() ?>' },
                        success: function(res) {
                            layer.msg('库存已删除');
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
                let area = isMobile ? ['98%', 'auto']  : ['700px', 'auto'];
                layer.open({
                    id: 'edit',
                    title: '编辑库存',
                    type: 2,
                    area: area,
                    // skin: 'layui-layer-win10',
                    skin: 'dc-layer-modern',
                    content: 'stock.php?action=edit&stock_id=' + data.stock_id,
                    fixed: false, // 不固定
                    maxmin: true,
                    shadeClose: true,
                    success: function(layero, index, that){
                        layer.iframeAuto(index); // 让 iframe 高度自适应
                        that.offset(); // 重新自适应弹层坐标
                    }
                });
            }
            if(obj.event === 'stock'){
                let isMobile = window.innerWidth < 1200;
                let area = isMobile ? ['98%', 'auto']  : ['1000px', 'auto'];
                layer.open({
                    id: 'stock',
                    title: '库存管理',
                    type: 2,
                    area: area,
                    // skin: 'layui-layer-win10',
                    skin: 'dc-layer-modern',
                    content: 'stock.php?action=index&goods_id=' + data.uid,
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
        table.on('sort(stock_index_ws)', function(obj){
            console.log(obj.field); // 当前排序的字段名
            console.log(obj.type); // 当前排序类型：desc（降序）、asc（升序）、null（空对象，默认排序）
            console.log(this); // 当前排序的 th 对象

            // 尽管我们的 table 自带排序功能，但并没有请求服务端。
            // 有些时候，你可能需要根据当前排序的字段，重新向后端发送请求，从而实现服务端排序，如：
            table.reload('stock_index_ws', {
                initSort: obj, // 记录初始排序，如果不设的话，将无法标记表头的排序状态。
                where: { // 请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    field: obj.field, // 排序字段
                    order: obj.type // 排序方式
                }
            });
        });

        // 触发表格复选框选择
        table.on('checkbox(stock_index_ws)', function(obj){
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
        table.on('pagebar(stock_index_ws)', function(obj){
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


    });
</script>



<script>

    var maxHeight = $(window.parent).innerHeight() * 0.75;
    $("#open-box").css({
        "max-height": maxHeight + "px", // 单位必须加 px
        "overflow-y": "auto" // 内容超过 max-height 时显示垂直滚动条
    });
</script>
