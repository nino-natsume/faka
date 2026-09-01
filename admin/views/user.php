<?php
defined('DC_ROOT') || exit('access denied!');

$activePlugins = Option::get('active_plugins') ?: [];
$isAdminColorActive = in_array('admin_color/admin_color.php', $activePlugins);
if ($isAdminColorActive) {
    $colorStorage = Storage::getInstance('admin_color');
    $tplPrimaryColorDark = $colorStorage->getValue('primary_color_dark') ?: '#4C7D71';
    $tplPrimaryColor = $colorStorage->getValue('primary_color') ?: '#4C7D71';
    $gearColor = (strpos($tplPrimaryColor, 'gradient') !== false) ? $tplPrimaryColorDark : $tplPrimaryColor;
} else {
    $gearColor = '#4C7D71';
}
?>
<table class="layui-hide" id="index" lay-filter="index"></table>
<script type="text/html" id="toolbar">
    <div style="display:flex;align-items:center;justify-content:center;position:relative;padding:10px 0px 15px;border-bottom:1px solid #f0f0f0;">
        <span style="position:absolute;left:0;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">用户管理</span>
    </div>
    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
        <div class="layui-btn-container">
            <button class="layui-btn layui-btn-primary layui-border-green" lay-event="refresh"><i class="ri-refresh-line"></i></button>
            <button class="layui-btn layui-btn-sm layui-btn-normal" lay-event="addUser">添加用户</button>
            <a class="layui-btn layui-btn-sm layui-bg-orange" href="./user_recycle.php"><i class="ri-delete-bin-line"></i> 用户回收</a>
        </div>
        <form class="layui-form" style="display: flex; align-items: center; gap: 6px; margin: 0; flex-wrap: wrap;">
            <div class="layui-input-inline layui-input-wrap" style="width: 120px; margin: 0;">
                <select name="member_id">
                    <option value="">会员等级</option>
                    <?php foreach($member_list as $val): ?>
                    <option value="<?= $val['id'] ?>"><?= $val['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="layui-input-inline layui-input-wrap" style="width: 200px; margin: 0;">
                <input type="text" value="" name="keyword" placeholder="账号/昵称/手机/邮箱/ID/注册IP" lay-affix="clear" class="layui-input">
            </div>
            <button class="layui-btn layui-btn-sm" lay-submit lay-filter="index-search">搜索</button>
            <button type="reset" class="layui-btn layui-btn-sm layui-btn-primary">重置</button>
        </form>
    </div>
</script>
<script type="text/html" id="money">
    <div class="layui-clear-space">
        <a href="javascript:;" class="dc-money-btn" data-id="{{ d.uid }}" lay-event="money">
            <span class="dc-value-badge dc-value-badge-money">{{ d.money }}</span>
        </a>
    </div>
</script>
<script type="text/html" id="creditsTpl">
    <div class="layui-clear-space">
        <a href="javascript:;" class="dc-credits-btn" data-id="{{ d.uid }}" lay-event="credits">
            <span class="dc-value-badge dc-value-badge-credits">{{ d.credits }}</span>
        </a>
    </div>
</script>
<script type="text/html" id="regIpTpl">
    {{# if(d.reg_ip){ }}
    <a href="javascript:;" class="dc-regip-btn" lay-event="filter_ip" title="点击筛选该 IP 下注册的所有账号">{{ d.reg_ip }}</a>
    {{# } else { }}
    <span style="color:#c0c4cc;">-</span>
    {{# } }}
</script>
<style>
.dc-user-row-active { background-color: #f2f6fc !important; }
.dc-user-row-active td { background-color: #f2f6fc !important; }
#index + .layui-table-view .layui-table-body td .layui-table-cell { font-size: 13px; }
.dc-user-ban-icon { margin-left: 4px; font-size: 14px; vertical-align: middle; }
td[data-field="avatar_url"] .layui-table-grid-down { display: none !important; }
td[data-field="avatar_url"] .layui-table-cell { overflow: visible; height: auto; line-height: normal; padding: 4px 0; }
.dc-bind-superior-btn { display: inline-flex; align-items: center; justify-content: center; cursor: pointer; text-decoration: none; }
.dc-money-btn, .dc-credits-btn { display: inline-flex; align-items: center; justify-content: center; text-decoration: none; }
.dc-bind-superior-btn.is-empty { color: #ff7a45; }
.dc-bind-superior-btn.is-empty:hover { color: #ff4d4f; }
.dc-bind-superior-btn.is-bound { color: inherit; }
.dc-superior-id-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 58px; height: 22px; padding: 0 5px; border: 1px solid #cfe0ff; border-radius: 4px; background: #f2f7ff; color: #2f80ff; font-weight: 500; line-height: 30px; box-sizing: border-box; transition: all .2s ease; }
.dc-bind-superior-btn.is-bound:hover .dc-superior-id-badge { border-color: #9fc3ff; background: #eaf3ff; color: #1d6fe9; }
.dc-value-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 58px; height: 22px; padding: 0 5px; border: 1px solid transparent; border-radius: 4px; font-weight: 500; line-height: 30px; box-sizing: border-box; transition: all .2s ease; }
.dc-value-badge-money { border-color: #b7f0e2; background: #edfffa; color: #16baaa; }
.dc-money-btn:hover .dc-value-badge-money { border-color: #8ce0cc; background: #e2fff8; color: #109788; }
.dc-value-badge-credits { border-color: #ffd5bf; background: #fff4ed; color: #ff7a45; }
.dc-credits-btn:hover .dc-value-badge-credits { border-color: #ffbe9c; background: #fff0e7; color: #f05f24; }
.dc-regip-btn { color: #2563eb; text-decoration: none; cursor: pointer; }
.dc-regip-btn:hover { color: #1d4ed8; text-decoration: underline; }
</style>
<script type="text/html" id="avatarTpl">
    <img src="{{ d.avatar_url }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover;vertical-align:middle;background:#f5f5f5;">
</script>
<script type="text/html" id="nicknameTpl">
    {{ d.nickname }}{{# if(d.state != 0){ }}<span class="dc-user-ban-icon" title="已封禁">🈲</span>{{# } }}
</script>
<script type="text/html" id="superiorTpl">
    <a href="javascript:;" class="dc-bind-superior-btn {{# if((d.superior || 0) > 0){ }}is-bound{{# } else { }}is-empty{{# } }}" data-uid="{{ d.uid }}" data-login="{{ d.login }}" data-nickname="{{ d.name }}">
        {{# if((d.superior || 0) > 0){ }}
        <span class="dc-superior-id-badge">{{ d.superior }}</span>
        {{# } else { }}
        没上级
        {{# } }}
    </a>
</script>
<script type="text/html" id="operate">
    <div style="text-align:center;">
        <a href="javascript:;" class="dc-user-action-btn" data-uid="{{ d.uid }}" data-state="{{ d.state }}">
            <i class="layui-icon layui-icon-set-fill" style="font-size:22px;color:<?= $gearColor ?>;"></i>
        </a>
    </div>
</script>
<script type="text/html" id="addUserFormTpl">
    <form class="layui-form" style="padding:25px 25px 0;" lay-filter="addUserForm">
        <div class="layui-form-item">
            <label class="layui-form-label">登录账号</label>
            <div class="layui-input-block">
                <input type="text" name="username" required lay-verify="required" placeholder="请输入登录账号" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">密码</label>
            <div class="layui-input-block">
                <input type="password" name="password" required lay-verify="required" placeholder="请输入密码，至少6位" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">用户昵称</label>
            <div class="layui-input-block">
                <input type="text" name="nickname" placeholder="可留空，默认同步登录账号" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">余额</label>
            <div class="layui-input-block">
                <input type="number" name="money" value="0" min="0" step="0.01" placeholder="初始余额，默认0" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">会员等级</label>
            <div class="layui-input-block">
                <select name="level">
                    <?php foreach($member_list as $val): ?>
                    <option value="<?= $val['id'] ?>" <?= intval($default_member_id ?? 0) === intval($val['id']) ? 'selected' : '' ?>><?= $val['name'] ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="layui-form-mid layui-word-aux">会员等级决定会员价、功能门槛与分销参数，不影响后台权限。</div>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">绑定邮箱</label>
            <div class="layui-input-block">
                <input type="text" name="email" placeholder="选填" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">绑定手机</label>
            <div class="layui-input-block">
                <input type="text" name="tel" placeholder="选填" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item" style="text-align:center;padding:10px 0;">
            <button class="layui-btn" lay-submit lay-filter="submitAddUser">确认添加</button>
        </div>
    </form>
</script>


<script>
    layui.use(['table', 'dropdown'], function(){
        var table = layui.table;
        var form = layui.form;
        var dropdown = layui.dropdown;
        var $ = layui.$;
        var urlParams = new URLSearchParams(window.location.search);
        var initKeyword = urlParams.get('keyword') || '';
        $('input[name="keyword"]').val(initKeyword);
        // 创建渲染实例
        window.table = table.render({
            elem: '#index',
            id: 'index',
            autoSort: false,
            url: '?action=index', // 此处为静态模拟数据，实际使用时需换成真实接口
            where: initKeyword ? { keyword: initKeyword } : {},
            toolbar: '#toolbar',
            limits: [10,20,30,50,100],
            page: true,
            defaultToolbar: [],
            cols: [[
                {field:'uid', title: 'ID', sort: true, width: 75},
                {field:'avatar_url', title: '头像', width: 50, align: 'center', unresize: true, templet: '#avatarTpl'},
                {field:'login', title: '登录账号', minWidth: 120, maxWidth: 160},
                {field:'nickname', title: '用户昵称', minWidth: 130, maxWidth: 170, templet: '#nicknameTpl'},
                {field:'tel', title:'手机号码', minWidth: 110},
                {field:'email', title:'邮箱', minWidth: 160},
                {field:'level_name', title:'会员等级', minWidth: 110, maxWidth:140},
                {field:'superior', title:'上级ID', minWidth: 90, maxWidth: 110, templet: '#superiorTpl'},
                {field:'money', title:'余额', sort: true, minWidth: 80, maxWidth: 120, templet: '#money'},
                {field:'credits', title:'积分', sort: true, minWidth: 80, maxWidth: 100, templet: '#creditsTpl'},
                {field:'reg_ip', title:'注册IP', minWidth: 120, maxWidth: 125, templet: '#regIpTpl'},
                {field:'create_time', title:'注册时间', sort: true, width: 140},
                {fixed: 'right', title:'操作', templet: '#operate', width: 60, align: 'center'}
            ]],

            error: function(res, msg){
                console.log(res, msg)
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

        // 重置按钮：清空表单 + 清空筛选参数 + 重载
        $(document).on('click', 'form.layui-form button[type="reset"]', function(){
            setTimeout(function(){
                // 同步 layui 的 select/checkbox 等自定义控件显示
                form.render();
                table.reload('index', {
                    page: { curr: 1 },
                    where: {}
                });
            }, 0);
        });


        // 工具栏事件
        table.on('toolbar(index)', function(obj){
            var id = obj.config.id;
            var othis = lay(this);
            switch(obj.event){
                case 'refresh':
                    table.reload(id);
                    break;
                case 'addUser':
                    var isMobile = window.innerWidth < 768;
                    var area = isMobile ? ['98%', '90%'] : ['560px', '75%'];
                    layer.open({
                        type: 1,
                        title: '添加用户',
                        area: area,
                        skin: 'dc-layer-modern',
                        shadeClose: true,
                        content: $('#addUserFormTpl').html(),
                        success: function(layero, idx){
                            form.render();
                            form.on('submit(submitAddUser)', function(formData){
                                $.ajax({
                                    url: '?action=new',
                                    type: 'POST',
                                    dataType: 'json',
                                    data: $.extend(formData.field, { token: '<?= LoginAuth::genToken() ?>' }),
                                    success: function(e){
                                        if(e.code == 400) return layer.msg(e.msg);
                                        layer.close(idx);
                                        layer.msg('已添加用户');
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
            };
        });

        // 点击空白处移除行高亮
        $(document).on('click', function(){ $('.dc-user-row-active').removeClass('dc-user-row-active'); });

        $(document).on('click', '.dc-bind-superior-btn', function(e){
            e.stopPropagation();
            var uid = parseInt($(this).data('uid'), 10) || 0;
            var login = $(this).data('login') || '';
            var nickname = $(this).data('nickname') || '';
            openBindSuperiorDialog(uid, nickname || login || ('UID ' + uid));
        });

        // 齿轮菜单：点击后弹出下拉操作列表
        $(document).on('click', '.dc-user-action-btn', function(e){
            e.stopPropagation();
            var $btn = $(this);
            var uid = $btn.data('uid');
            var state = $btn.data('state');

            // 高亮当前行
            $('.dc-user-row-active').removeClass('dc-user-row-active');
            $btn.closest('tr').addClass('dc-user-row-active');

            // 构建菜单项
            var menuData = [
                {title: '<b style="color:#333;">UID：' + uid + '</b>', id: 'header', disabled: true},
                {type: '-'},
                {title: '编辑账户', id: 'edit'},
                {title: '收支明细', id: 'balance_detail'},
                {title: '相关订单', id: 'orders'},
                {title: '详细信息', id: 'detail'},
                {type: '-'}
            ];
            if(state == 0){
                menuData.push({title: '<span style="color:#e6a23c;">封禁账号</span>', id: 'forbid'});
            } else {
                menuData.push({title: '<span style="color:#409eff;">解除封禁</span>', id: 'unforbid'});
            }
            menuData.push({title: '<span style="color:#ff4d4f;">删除账号</span>', id: 'del'});

            // 销毁之前的实例再重新渲染
            if(window._dcUserDropdown){
                try { window._dcUserDropdown.config = null; } catch(ex){}
            }
            window._dcUserDropdown = dropdown.render({
                elem: $btn[0],
                show: true,
                align: 'right',
                data: menuData,
                templet: function(d){
                    return d.title;
                },
                click: function(obj){
                    dcUserAction(obj.id, uid);
                }
            });
        });

        // 统一处理用户操作
        function dcUserAction(action, uid){
            var tableId = 'index';
            if(action === 'detail'){
                var isMobile = window.innerWidth < 768;
                var area = isMobile ? ['98%', '94%'] : ['980px', '92%'];
                layer.open({
                    id: 'detail',
                    title: '用户详细信息 - UID ：' + uid,
                    type: 2,
                    area: area,
                    skin: 'dc-layer-modern',
                    scrollbar: false,
                    content: 'user.php?action=detail&uid=' + uid,
                    fixed: false,
                    maxmin: true,
                    shadeClose: true
                });
            }
            if(action === 'edit'){
                var isMobile = window.innerWidth < 768;
                var area = isMobile ? ['98%', 'auto'] : ['700px', 'auto'];
                layer.open({
                    id: 'edit',
                    title: '编辑账户',
                    type: 2,
                    area: area,
                    skin: 'dc-layer-modern',
                    content: 'user.php?action=edit&uid=' + uid,
                    fixed: false,
                    maxmin: true,
                    shadeClose: true,
                    success: function(layero, index, that){
                        layer.iframeAuto(index);
                        that.offset();
                    }
                });
            }
            if(action === 'balance_detail'){
                var isMobile = window.innerWidth < 768;
                var area = isMobile ? ['98%', '90%'] : ['1000px', '85%'];
                layer.open({
                    title: '收支明细 - UID ：' + uid,
                    type: 2,
                    area: area,
                    skin: 'dc-layer-modern',
                    content: 'user_log.php?popup=1&filter_uid=' + uid,
                    fixed: false,
                    maxmin: true,
                    shadeClose: true
                });
            }
            if(action === 'orders'){
                var isMobile = window.innerWidth < 768;
                var area = isMobile ? ['98%', '90%'] : ['1100px', '85%'];
                layer.open({
                    title: '相关订单 - UID ：' + uid,
                    type: 2,
                    area: area,
                    skin: 'dc-layer-modern',
                    content: 'order.php?popup=1&filter_uid=' + uid,
                    fixed: false,
                    maxmin: true,
                    shadeClose: true
                });
            }
            if(action === 'forbid'){
                layer.confirm('确定要封禁该用户吗？', {
                    btn: ['确认', '取消'],
                    icon: 3,
                    title: '温馨提示'
                }, function(idx){
                    layer.close(idx);
                    $.ajax({
                        url: '?action=forbid',
                        type: 'POST',
                        dataType: 'json',
                        data: { ids: uid, token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e){
                            if(e.code == 400) return layer.msg(e.msg);
                            layer.msg('已封禁用户');
                            table.reload(tableId);
                        },
                        error: function(err){ layer.msg(err.responseJSON.msg); }
                    });
                });
            }
            if(action === 'unforbid'){
                $.ajax({
                    url: '?action=unforbid',
                    type: 'POST',
                    dataType: 'json',
                    data: { ids: uid, token: '<?= LoginAuth::genToken() ?>' },
                    success: function(e){
                        if(e.code == 400) return layer.msg(e.msg);
                        layer.msg('已解除封禁');
                        table.reload(tableId);
                    },
                    error: function(err){ layer.msg(err.responseJSON.msg); }
                });
            }
            if(action === 'del'){
                layer.confirm('确定要删除该用户吗？', {
                    btn: ['确认', '取消'],
                    icon: 3,
                    title: '温馨提示'
                }, function(idx){
                    layer.close(idx);
                    $.ajax({
                        url: '?action=del',
                        type: 'POST',
                        dataType: 'json',
                        data: { ids: uid, token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e){
                            if(e.code == 400) return layer.msg(e.msg);
                            layer.msg('已移入用户回收站');
                            table.reload(tableId);
                        },
                        error: function(err){ layer.msg(err.responseJSON.msg); }
                    });
                });
            }
        }

        function openBindSuperiorDialog(uid, label){
            if(!uid){
                return layer.msg('用户ID无效');
            }
            var isMobile = window.innerWidth < 768;
            var area = isMobile ? ['98%', 'auto'] : ['500px', 'auto'];
            layer.open({
                id: 'superior',
                title: '设置上级ID - ' + label,
                type: 2,
                area: area,
                skin: 'dc-layer-modern',
                content: 'user.php?action=superior&uid=' + uid,
                fixed: false,
                maxmin: true,
                shadeClose: true,
                success: function(layero, index, that){
                    layer.iframeAuto(index);
                    that.offset();
                }
            });
        }

        // 余额点击事件（保留原有逻辑）
        table.on('tool(index)', function(obj){
            var data = obj.data;
            if(obj.event === 'money'){
                var isMobile = window.innerWidth < 768;
                var area = isMobile ? ['98%', 'auto'] : ['500px', 'auto'];
                layer.open({
                    id: 'money',
                    title: '调整用户余额',
                    type: 2,
                    area: area,
                    skin: 'dc-layer-modern',
                    content: 'user.php?action=money&uid=' + data.uid,
                    fixed: false,
                    maxmin: true,
                    shadeClose: true,
                    success: function(layero, index, that){
                        layer.iframeAuto(index);
                        that.offset();
                    }
                });
            }
            if(obj.event === 'credits'){
                var isMobile = window.innerWidth < 768;
                var area = isMobile ? ['98%', 'auto'] : ['500px', 'auto'];
                layer.open({
                    id: 'credits',
                    title: '调整用户积分',
                    type: 2,
                    area: area,
                    skin: 'dc-layer-modern',
                    content: 'user.php?action=credits&uid=' + data.uid,
                    fixed: false,
                    maxmin: true,
                    shadeClose: true,
                    success: function(layero, index, that){
                        layer.iframeAuto(index);
                        that.offset();
                    }
                });
            }
            if(obj.event === 'filter_ip'){
                var ip = (data.reg_ip || '').toString();
                if(!ip){
                    return layer.msg('该用户未记录注册IP');
                }
                table.reload('index', {
                    page: { curr: 1 },
                    where: { reg_ip: ip }
                });
                layer.msg('已筛选 IP ' + ip + ' 下注册的账号');
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


