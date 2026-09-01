<?php
defined('DC_ROOT') || exit('access denied!');
?>
<style>
    /* 主内容区样式 */
    .main-content {
        padding: 20px 15px;
    }

</style>
<style>


    .main-content .layui-panel{
        border-radius: 10px;
    }
    .layui-form-label{
        /*font-weight: bold;*/
        color: #a855f7;
    }
    .layui-table-header { overflow-x: auto !important; scrollbar-width: none; }
    .layui-table-header::-webkit-scrollbar { display: none; }
    .layui-table th:first-child,
    .layui-table td:first-child { position: sticky !important; left: 0; z-index: 2; background: #fff; }
    .layui-table th:nth-child(2),
    .layui-table td:nth-child(2) { position: sticky !important; left: 48px; z-index: 2; background: #fff; box-shadow: 2px 0 4px rgba(0,0,0,.04); }
    .layui-table-header th:first-child,
    .layui-table-header th:nth-child(2) { background: #fafafa; }
</style>

<!-- 主内容区 -->
<main class="main-content">



    <div class="layui-panel">

        <blockquote class="layui-elem-quote">
            显示开关生效需在店铺配置中设置主站分类自定义模式
        </blockquote>

        <table class="layui-hide" id="index" lay-filter="index"></table>
        <script type="text/html" id="toolbar">
            <div class="layui-btn-container">
                <button class="layui-btn layui-btn-primary layui-border-green" lay-event="refresh">
                    <i class="fa fa-refresh" style=""></i>
                </button>
                <button class="toolbar-select layui-btn layui-btn-sm layui-bg-green layui-btn-disabled" lay-event="show">
                    显示选中
                </button>
                <button class="toolbar-select layui-btn  layui-bg-red layui-btn-disabled" lay-event="hide">
                    隐藏选中
                </button>
            </div>
        </script>
        <script type="text/html" id="cover">
            <div class="layui-clear-space">
                <a href="javascript:;" data-id="{{ d.id }}" lay-event="img">
                    <img onerror="this.onerror=null; this.src='<?= DC_URL ?>admin/views/images/null.png'" class="cover" data-img="{{ d.sortimg }}" src="{{ d.sortimg }}" style="width: 40px; border-radius: 3px;" />
                </a>
            </div>
        </script>
        <script type="text/html" id="is_show">
            <input type="checkbox" name="{{= d.sid }}" value="{{= d.sid }}" title=" ON |OFF " lay-skin="switch" lay-filter="switch" {{= d.is_show == 'y' ? "checked" : "" }}>
        </script>
        <script type="text/html" id="operate">
            <div class="layui-clear-space">
                <a class="layui-btn" lay-event="edit">编辑</a>
            </div>
        </script>

    </div>


</main>

<script>
    // 初始化Layui模块
    layui.use(['element', 'layer'], function() {
        var table = layui.table;
        var form = layui.form;
// 创建渲染实例
        window.table = table.render({
            elem: '#index',
            autoSort: false,
            url: '?action=master_sort_index', // 此处为静态模拟数据，实际使用时需换成真实接口
            toolbar: '#toolbar',
            limits: [],
            page: false,
            lineStyle: 'height: 60px;',
            defaultToolbar: ['filter', 'exports'],


            cols: [[
                {type: 'checkbox'},
                {field:'sortimg', title:'图片', width: 80, templet: '#cover', align: 'center'},
                {field:'sortname', title:'主站分类名称', minWidth: 170 },
                {field:'custom_name', title:'自定义名称', minWidth: 170 },
                {field:'is_show', title:'显示', align: 'center', width: 100, templet: '#is_show'},
                {title:'操作', templet: '#operate', width: 100, align: 'center'}
            ]],

            error: function(res, msg){
                console.log(res, msg)
            }
        });

        // 状态 - 开关操作
        form.on('switch(switch)', function(obj){
            var is_show = obj.elem.checked == true ? 'y' : 'n';
            var id = this.value;
            var loadSwitch = layer.load(2);
            $.ajax({
                url: '?action=master_sort_switch',
                type: 'POST',
                dataType: 'json',
                data: { id: id, is_show: is_show, token: '<?= LoginAuth::genToken() ?>' },
                success: function(e) {
                    if(e.code == 400){
                        layer.msg(e.msg)
                    }else{
                        layer.msg('分类状态已更新');
                    }

                },
                error: function(err) {
                    layer.msg(err.responseJSON.msg);
                },
                complete: function() {
                    layer.close(loadSwitch);
                }
            });
        });


        // 工具栏事件
        table.on('toolbar(index)', function(obj){
            var id = obj.config.id;
            var checkStatus = table.checkStatus(id);
            var othis = lay(this);
            if(obj.event == 'refresh'){
                table.reload(id);
            }

            if(obj.event == 'show'){
                var data = checkStatus.data;
                if(data.length == 0){
                    return;
                }
                var ids = $.map(data, function(item) {
                    return item.sid; // 提取每个对象的uid
                }).join(',');
                layer.confirm('确定要显示选中的数据？', {
                    btn: ['确认', '取消'], // 按钮
                    icon: 3,             // 图标，3表示问号
                    title: '温馨提示'
                }, function(index) {
                    layer.close(index); // 关闭对话框
                    $.ajax({
                        url: '?action=master_sort_show',
                        type: 'POST',
                        dataType: 'json',
                        data: { ids: ids, token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e) {
                            if(e.code == 400){
                                return layer.msg(e.msg)
                            }
                            layer.msg('已设为显示');
                            table.reload(id);
                        },
                        error: function(err) {
                            layer.msg(err.responseJSON.msg);
                        }
                    });
                });
            }
            if(obj.event == 'hide'){
                var data = checkStatus.data;
                if(data.length == 0){
                    return;
                }
                var ids = $.map(data, function(item) {
                    return item.sid; // 提取每个对象的uid
                }).join(',');
                layer.confirm('确定要隐藏选中的数据？', {
                    btn: ['确认', '取消'], // 按钮
                    icon: 3,             // 图标，3表示问号
                    title: '温馨提示'
                }, function(index) {
                    layer.close(index); // 关闭对话框
                    $.ajax({
                        url: '?action=master_sort_hide',
                        type: 'POST',
                        dataType: 'json',
                        data: { ids: ids, token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e) {
                            if(e.code == 400){
                                return layer.msg(e.msg)
                            }
                            layer.msg('已设为隐藏');
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
                        data: { ids: data.sid, type: 'goods', token: '<?= LoginAuth::genToken() ?>' },
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
                    skin: 'layui-layer-molv',
                    content: '?action=master_sort_edit&sort_id=' + data.sid,
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
                $('.toolbar-select').addClass('layui-btn-disabled');
            }else{
                $('.toolbar-select').removeClass('layui-btn-disabled');
            }
        });

    });
</script>

<script>
    $('#menu-station').addClass('open');
    $('#menu-station > ul').css('display', 'block');
    $('#menu-station > a > i.nav_right').attr('class', 'fa fa-angle-down nav_right');
    $('#menu-station-master_sort').addClass('menu-current');
</script>
