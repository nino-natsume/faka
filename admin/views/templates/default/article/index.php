<?php
defined('DC_ROOT') || exit('access denied!');
?>
<table class="layui-hide" id="index" lay-filter="index"></table>
<script type="text/html" id="toolbar">
    <div style="display:flex;align-items:center;justify-content:center;position:relative;padding:10px 0px 15px;border-bottom:1px solid #f0f0f0;">
        <span style="position:absolute;left:0;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">文章管理</span>
    </div>
    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
        <div class="layui-btn-container">
            <button class="layui-btn layui-btn-primary layui-border-green" lay-event="refresh"><i class="ri-refresh-line"></i></button>
            <button type="button" class="layui-btn" lay-event="add">添加</button>
            <button id="toolbar-del" class="layui-btn  layui-bg-red layui-btn-disabled" lay-event="del">删除</button>
        </div>
        <form class="layui-form" style="display: flex; align-items: center; gap: 6px; margin: 0; flex-wrap: wrap; justify-content: flex-end;">
            <div class="layui-input-inline" style="width: 130px; margin: 0;">
                <select name="sid">
                    <option value="">全部分类</option>
                    <?php foreach ($sorts as $sid => $sort): ?>
                        <option value="<?= (int)$sid ?>"><?= !empty($sort['pid']) ? '└ ' : '' ?><?= $sort['sortname'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="layui-input-inline" style="width: 110px; margin: 0;">
                <select name="draft">
                    <option value="0">已发布</option>
                    <option value="1">草稿</option>
                </select>
            </div>
            <div class="layui-input-inline" style="width: 110px; margin: 0;">
                <select name="checked">
                    <option value="">全部审核</option>
                    <option value="y">已审核</option>
                    <option value="n">待审核</option>
                </select>
            </div>
            <div class="layui-input-inline" style="width: 120px; margin: 0;">
                <select name="order">
                    <option value="">最新发布</option>
                    <option value="view">浏览最多</option>
                    <option value="comm">评论最多</option>
                    <option value="top">置顶优先</option>
                </select>
            </div>
            <div class="layui-input-inline layui-input-wrap" style="width: 160px; margin: 0;">
                <input type="text" value="" name="keyword" placeholder="搜索标题..." lay-affix="clear" class="layui-input">
            </div>
            <button class="layui-btn layui-btn-sm" lay-submit lay-filter="index-search">搜索</button>
            <button type="reset" class="layui-btn layui-btn-sm layui-btn-primary">重置</button>
        </form>
    </div>
</script>
<script type="text/html" id="cover">
    <div class="layui-clear-space">
        <a href="javascript:;" data-id="{{ d.id }}" lay-event="img">
            <img onerror="this.onerror=null; this.src='./views/images/null.png'" class="cover" data-img="{{ d.cover }}" src="{{ d.cover }}" style="width: 40px; border-radius: 3px;" />
        </a>
    </div>
</script>

<script type="text/html" id="title">
    <div class="layui-clear-space">

        <span style="">{{ d.title }}</span>
    </div>
</script>
<script type="text/html" id="is_sku">
    <div class="layui-clear-space">

    </div>
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
<script type="text/html" id="money">
    <div class="layui-clear-space">
        <a href="javascript:;" style="color: #16baaa;" data-id="{{ d.uid }}" lay-event="money">{{ d.money }}</a>
    </div>
</script>
<script type="text/html" id="operate">
    <div class="layui-clear-space">
        <a class="layui-btn layui-btn-primary" lay-event="comment">评论</a>
        <a class="layui-btn" lay-event="edit">编辑</a>
        <a class="layui-btn layui-bg-red" lay-event="del">删除</a>
    </div>
</script>


<script>
    layui.use(['table', 'form'], function(){
        var table = layui.table;
        var form = layui.form;
        // 创建渲染实例
        window.table = table.render({
            elem: '#index',
            autoSort: false,
            url: '?action=index', // 此处为静态模拟数据，实际使用时需换成真实接口
            toolbar: '#toolbar',
            limits: [10,20,30,50,100],
            page: true,
            lineStyle: 'height: 30px;',
            defaultToolbar: [],
            cols: [[
                {type: 'checkbox', fixed: 'left'},
                {field:'name', title:'封面图', width: 80, templet: '#cover', align: 'center'},
                {field:'title', title:'文章标题', minWidth: 200, templet: '#title'},
                {field:'views', title:'浏览数', width: 130, align: 'center', sort: true },
                {field:'comnum', title:'评论数', width: 110, align: 'center', sort: true },
                {field:'sortname', title:'分类', width: 130, sort: true, align: 'center'},
                {field:'date', title:'添加时间', sort: true, width: 150, align: 'center'},
                {fixed: 'right', title:'操作', templet: '#operate', width: 210, align: 'center'}
            ]],

            error: function(res, msg){
                console.log(res, msg)
            },
            done: function(){
                form.render();
            }
        });

        // 搜索提交
        form.on('submit(index-search)', function(data){
            var field = data.field; // 获得表单字段
            // 执行搜索重载
            table.reload('index', {
                page: {
                    curr: 1 // 重新从第 1 页开始
                },
                where: field // 搜索的字段
            });
            return false; // 阻止默认 form 跳转
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
                location.href="?action=write"
            }
            if(obj.event == 'del'){
                var data = checkStatus.data;
                if(data.length == 0){
                    return;
                }
                var ids = $.map(data, function(item) {
                    return item.gid; // 提取每个对象的uid
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
                        data: { ids: ids, token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e) {
                            if(e.code == 400){
                                return layer.msg(e.msg)
                            }
                            layer.msg('文章已删除');
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
                        data: { ids: data.gid, token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e) {
                            if(e.code == 400){
                                return layer.msg(e.msg)
                            }
                            layer.msg('文章已删除');
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
                                "src": data.cover,
                            }
                        ]
                    }
                });
            }
            if(obj.event === 'edit'){
                location.href = "?action=edit&gid=" + data.gid;
            }
            if(obj.event === 'comment'){
                location.href = "comment.php?gid=" + data.gid;
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


    });
</script>

