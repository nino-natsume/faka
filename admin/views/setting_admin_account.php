<?php defined('DC_ROOT') || exit('access denied!'); ?>
<style>
    .aacfg-section { background: #fff; border: 1px solid #eef1f4; border-radius: 8px; padding: 18px 20px; margin-bottom: 14px; }
    .aacfg-title { font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; }
    .aacfg-title i { color: #2563eb; }
    .aacfg-hint {
        background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a;
        padding: 10px 14px; border-radius: 8px; font-size: 13px; line-height: 1.7; margin-bottom: 14px;
    }
    .aacfg-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 14px; }
    .aacfg-stat-card { background: #fff; border: 1px solid #eef1f4; border-radius: 10px; padding: 16px 18px; }
    .aacfg-stat-label { color: #6b7280; font-size: 12px; margin-bottom: 6px; }
    .aacfg-stat-value { color: #111827; font-size: 24px; font-weight: 700; line-height: 1.2; }
    .aacfg-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 12px; }
    .aacfg-toolbar .layui-form { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin: 0; }
    .aacfg-toolbar .layui-input-inline { width: 260px; margin: 0; }
    .aacfg-tip { color: #6b7280; font-size: 12px; line-height: 1.7; }
    .aacfg-badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 999px; font-size: 12px; line-height: 1; }
    .aacfg-badge.admin { background: #eef6ff; color: #1d4ed8; }
    .aacfg-badge.founder { background: #fff7ed; color: #c2410c; }
    @media (max-width: 900px) {
        .aacfg-stats { grid-template-columns: 1fr; }
        .aacfg-toolbar { align-items: stretch; }
        .aacfg-toolbar .layui-form { width: 100%; }
        .aacfg-toolbar .layui-input-inline { width: 100%; }
    }
</style>

<div class="layui-tabs order-tabs-wrapper" style="display:flex;align-items:center;justify-content:space-between;" lay-options="{trigger: false}">
    <ul class="layui-tabs-header">
        <li><a href="./setting.php">系统配置</a></li>
        <li><a href="./setting.php?action=agreement">协议管理</a></li>
        <li class="layui-this"><a href="./setting.php?action=admin_account">管理员账户</a></li>
        <li><a href="./setting.php?action=seo">SEO设置</a></li>
        <li><a href="./setting.php?action=mail">邮箱配置</a></li>
    </ul>
</div>
<div class="layui-card" style="border-radius:8px;overflow:hidden;">
    <div class="layui-card-header" style="display:flex;align-items:center;justify-content:center;position:relative;height:auto;padding:12px 15px;">
        <span style="position:absolute;left:15px;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">管理员账户</span>
    </div>
    <div class="layui-card-body" style="padding:20px;">
        <div class="aacfg-hint">
            这里单独维护后台管理员权限，不再和普通用户资料编辑混在一起。普通用户仍在<a href="<?= DC_URL ?>admin/user.php" target="_blank">用户管理</a>里维护资料、余额与会员等级；当用户被设为管理员后，会从普通用户列表移到本页管理。
        </div>

        <div class="aacfg-stats">
            <div class="aacfg-stat-card">
                <div class="aacfg-stat-label">管理员账户数</div>
                <div class="aacfg-stat-value"><?= intval($adminAccountStats['admin_count'] ?? 0) ?></div>
            </div>
            <div class="aacfg-stat-card">
                <div class="aacfg-stat-label">创始人账户数</div>
                <div class="aacfg-stat-value"><?= intval($adminAccountStats['founder_count'] ?? 0) ?></div>
            </div>
            <div class="aacfg-stat-card">
                <div class="aacfg-stat-label">普通用户数</div>
                <div class="aacfg-stat-value"><?= intval($adminAccountStats['user_count'] ?? 0) ?></div>
            </div>
        </div>

        <div class="aacfg-section">
            <div class="aacfg-title"><i class="ri-user-search-line"></i> 搜索普通用户并设为管理员</div>
            <div class="aacfg-toolbar">
                <div class="aacfg-tip">输入 UID、登录账号、昵称、手机或邮箱后搜索。设为管理员后，该用户会从普通用户管理列表转入本页。</div>
                <form class="layui-form">
                    <div class="layui-input-inline">
                        <input type="text" name="candidate_keyword" placeholder="搜索普通用户" autocomplete="off" class="layui-input">
                    </div>
                    <button class="layui-btn layui-btn-sm" lay-submit lay-filter="aacfg-candidate-search"><i class="ri-search-line"></i> 搜索</button>
                    <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" id="aacfg-candidate-reset">重置</button>
                </form>
            </div>
            <table class="layui-hide" id="aacfg-candidate" lay-filter="aacfg-candidate"></table>
        </div>

        <div class="aacfg-section">
            <div class="aacfg-title"><i class="ri-shield-user-line"></i> 当前管理员账户</div>
            <div class="aacfg-toolbar">
                <div class="aacfg-tip">这里统一显示所有后台管理员账户。创始人账号和当前登录账号不允许在本页直接取消管理员权限。</div>
                <form class="layui-form">
                    <div class="layui-input-inline">
                        <input type="text" name="admin_keyword" placeholder="筛选管理员账户" autocomplete="off" class="layui-input">
                    </div>
                    <button class="layui-btn layui-btn-sm" lay-submit lay-filter="aacfg-admin-search"><i class="ri-search-line"></i> 搜索</button>
                    <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" id="aacfg-admin-reset">重置</button>
                </form>
            </div>
            <table class="layui-hide" id="aacfg-admin" lay-filter="aacfg-admin"></table>
        </div>
    </div>
</div>

<script type="text/html" id="aacfg-role-tpl">
    {{# if(d.is_founder == 1){ }}
    <span class="aacfg-badge founder"><i class="ri-vip-crown-2-line"></i>{{ d.role_name }}</span>
    {{# } else { }}
    <span class="aacfg-badge admin"><i class="ri-shield-star-line"></i>{{ d.role_name }}</span>
    {{# } }}
</script>

<script type="text/html" id="aacfg-candidate-action">
    <button class="layui-btn layui-btn-xs" lay-event="promote"><i class="ri-shield-user-line"></i> 设为管理员</button>
</script>

<script type="text/html" id="aacfg-admin-action">
    {{# if(d.can_revoke == 1){ }}
    <button class="layui-btn layui-btn-xs layui-btn-danger" lay-event="revoke"><i class="ri-user-unfollow-line"></i> 取消管理员</button>
    {{# } else if(d.is_founder == 1) { }}
    <span class="layui-btn layui-btn-xs layui-btn-primary layui-btn-disabled">创始人保护</span>
    {{# } else { }}
    <span class="layui-btn layui-btn-xs layui-btn-primary layui-btn-disabled">当前账号</span>
    {{# } }}
</script>

<div style="height: 96px;"></div>

<script>
layui.use(['table', 'form'], function(){
    var table = layui.table;
    var form = layui.form;
    var $ = layui.$;
    var TOKEN = '<?= LoginAuth::genToken() ?>';

    window.adminCandidateTable = table.render({
        elem: '#aacfg-candidate',
        id: 'aacfg-candidate-table',
        url: 'setting.php?action=admin_account_search',
        where: { keyword: '' },
        page: true,
        limits: [10, 20, 50],
        limit: 10,
        text: { none: '请输入关键词后搜索普通用户' },
        cols: [[
            {field:'uid', title:'UID', width:80, sort:true},
            {field:'login', title:'登录账号', minWidth:140},
            {field:'nickname', title:'昵称', minWidth:120},
            {field:'tel', title:'手机号码', minWidth:130},
            {field:'email', title:'邮箱', minWidth:180},
            {field:'level_name', title:'会员等级', minWidth:120},
            {field:'create_time', title:'注册时间', width:150},
            {fixed: 'right', title:'操作', templet:'#aacfg-candidate-action', width:130, align:'center'}
        ]]
    });

    window.adminAccountTable = table.render({
        elem: '#aacfg-admin',
        id: 'aacfg-admin-table',
        url: 'setting.php?action=admin_account_index',
        where: { keyword: '' },
        page: true,
        limits: [10, 20, 50],
        limit: 10,
        text: { none: '暂无管理员账户' },
        cols: [[
            {field:'uid', title:'UID', width:80, sort:true},
            {field:'login', title:'登录账号', minWidth:140},
            {field:'nickname', title:'昵称', minWidth:120},
            {field:'role_name', title:'权限类型', minWidth:120, templet:'#aacfg-role-tpl'},
            {field:'level_name', title:'会员等级', minWidth:120},
            {field:'tel', title:'手机号码', minWidth:130},
            {field:'email', title:'邮箱', minWidth:180},
            {field:'create_time', title:'注册时间', width:150},
            {fixed: 'right', title:'操作', templet:'#aacfg-admin-action', width:140, align:'center'}
        ]]
    });

    function reloadCandidate(resetPage) {
        table.reload('aacfg-candidate-table', {
            page: resetPage ? { curr: 1 } : undefined,
            where: {
                keyword: $.trim($('input[name="candidate_keyword"]').val())
            }
        });
    }

    function reloadAdminList(resetPage) {
        table.reload('aacfg-admin-table', {
            page: resetPage ? { curr: 1 } : undefined,
            where: {
                keyword: $.trim($('input[name="admin_keyword"]').val())
            }
        });
    }

    form.on('submit(aacfg-candidate-search)', function(){
        reloadCandidate(true);
        return false;
    });

    form.on('submit(aacfg-admin-search)', function(){
        reloadAdminList(true);
        return false;
    });

    $('#aacfg-candidate-reset').on('click', function(){
        $('input[name="candidate_keyword"]').val('');
        reloadCandidate(true);
    });

    $('#aacfg-admin-reset').on('click', function(){
        $('input[name="admin_keyword"]').val('');
        reloadAdminList(true);
    });

    table.on('tool(aacfg-candidate)', function(obj){
        if (obj.event !== 'promote') {
            return;
        }
        layer.confirm('确认将用户“' + obj.data.nickname + '”设为管理员账号吗？设为管理员后，该账号将从普通用户管理列表移到本页。', {
            btn: ['确认设置', '取消'],
            icon: 3,
            title: '设为管理员'
        }, function(index){
            layer.close(index);
            $.post('setting.php?action=admin_account_set', { uid: obj.data.uid, token: TOKEN }, function(e){
                if (e.code == 400) return layer.msg(e.msg);
                layer.msg('已设为管理员账号');
                reloadCandidate(false);
                reloadAdminList(false);
            }, 'json');
        });
    });

    table.on('tool(aacfg-admin)', function(obj){
        if (obj.event !== 'revoke') {
            return;
        }
        layer.confirm('确认取消“' + obj.data.nickname + '”的管理员权限吗？取消后，该账号将回到普通用户管理列表。', {
            btn: ['确认取消', '保留'],
            icon: 3,
            title: '取消管理员权限'
        }, function(index){
            layer.close(index);
            $.post('setting.php?action=admin_account_cancel', { uid: obj.data.uid, token: TOKEN }, function(e){
                if (e.code == 400) return layer.msg(e.msg);
                layer.msg('已取消管理员权限');
                reloadCandidate(false);
                reloadAdminList(false);
            }, 'json');
        });
    });
});
</script>
