<?php defined('DC_ROOT') || exit('access denied!'); ?>
<style>
    .bacfg-section { background: #fff; border: 1px solid #eef1f4; border-radius: 8px; padding: 18px 20px; margin-bottom: 14px; }
    .bacfg-title { font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; }
    .bacfg-title i { color: #2563eb; }
    .bacfg-toolbar { display:flex; align-items:center; justify-content:space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 12px; }
    .bacfg-toolbar .layui-form { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin:0; }
    .bacfg-toolbar .layui-input-inline { width: 260px; margin: 0; }
    .bacfg-tip { color:#6b7280; font-size:12px; line-height:1.7; }
    .bacfg-badge { display:inline-flex; align-items:center; gap:4px; padding:4px 10px; border-radius:999px; font-size:12px; line-height:1; }
    .bacfg-badge.backend { background:#eef6ff; color:#1d4ed8; }
    .bacfg-badge.founder { background:#fff7ed; color:#c2410c; }
    .bacfg-group-tip { display:inline-flex; align-items:center; gap:6px; color:#475569; font-size:12px; }
    .bacfg-panel { display: none; }
    .bacfg-panel.is-active { display: block; }
    .bacfg-modal { padding: 18px 18px 8px; }
    .bacfg-modal-fill { height: 100%; display: flex; flex-direction: column; box-sizing: border-box; }
    .bacfg-modal-scroll { flex: 1; min-height: 0; overflow: auto; padding: 18px 18px 8px; box-sizing: border-box; }
    .bacfg-modal-row { margin-bottom: 14px; }
    .bacfg-modal-label { display:block; font-size: 13px; color:#374151; font-weight:600; margin-bottom:8px; }
    .bacfg-modal-tip { color:#6b7280; font-size:12px; line-height:1.7; margin-top:6px; }
    .bacfg-perm-grid { display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; align-content: start; }
    .bacfg-perm-card { border:1px solid #e5e7eb; border-radius:8px; padding:12px 12px 8px; background:#f8fafc; }
    .bacfg-perm-head { padding-bottom:8px; margin-bottom:8px; border-bottom:1px solid #e5e7eb; font-weight:600; color:#111827; }
    .bacfg-perm-list { display:flex; flex-direction:column; gap:8px; }
    .bacfg-check-item { display:flex; align-items:flex-start; gap:8px; cursor:pointer; color:#374151; font-size:13px; line-height:1.5; }
    .bacfg-check-item input[type="checkbox"] { width:16px; height:16px; margin:2px 0 0; flex-shrink:0; accent-color:#2563eb; }
    .bacfg-check-item span { flex:1; min-width:0; }
    .bacfg-parent-label { color:#111827; font-weight:600; }
    .bacfg-child-label { color:#475569; }
    .bacfg-empty-inline { color:#94a3b8; font-size:12px; }
    .bacfg-preset-bar { display:flex; flex-wrap:wrap; gap:8px; margin:0 0 12px; }
    .bacfg-preset-btn { border-radius:999px !important; }
    .bacfg-search-dialog { height: 100%; display: flex; flex-direction: column; padding: 16px 20px; box-sizing: border-box; }
    .bacfg-search-toolbar { flex-shrink: 0; margin-bottom: 10px; }
    .bacfg-search-table-wrap { flex: 1; min-height: 0; }
    /* 当前后台账户个人资料卡 */
    .admin-profile-card { background: url('https://cloudcache.tencentcs.cn/qcloud/ui/activity-v2/build/LatestActivity/images/latest_card_bg_type1.png') center/cover no-repeat; border: 2px solid #fff;box-shadow: 8px 8px 20px 0 rgba(55, 99, 170, .1); border-radius: 6px; padding: 20px 24px; display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
    .admin-profile-avatar { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 2px solid #e5e7eb; }
    .admin-profile-info { flex: 1; min-width: 180px; }
    .admin-profile-name { font-size: 16px; font-weight: 600; color: #111827; display: flex; align-items: center; gap: 8px; }
    .admin-profile-name .role-tag { font-size: 11px; font-weight: 500; color: #2563eb; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 4px; padding: 1px 6px; }
    .admin-profile-meta { color: #6b7280; font-size: 13px; margin-top: 4px; display: flex; gap: 16px; flex-wrap: wrap; }
    .admin-profile-meta span { display: flex; align-items: center; gap: 4px; }
    .admin-profile-actions { display: flex; gap: 8px; }
    .profile-edit-section { padding: 20px 24px; }
    .profile-edit-row { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
    .profile-edit-row > label { width: 70px; color: #374151; font-weight: 500; text-align: right; flex-shrink: 0; }
    .profile-edit-row .layui-input { flex: 1; }
    .profile-edit-avatar-box { display: flex; align-items: center; gap: 14px; }
    .profile-edit-avatar-box img { width: 56px; height: 56px; border-radius: 50%; object-fit: cover; border: 2px solid #e5e7eb; cursor: pointer; transition: opacity .2s; }
    .profile-edit-avatar-box img:hover { opacity: .8; }
    .profile-edit-avatar-box .tip { color: #9ca3af; font-size: 12px; }
    .profile-edit-btns { text-align: right; padding: 0 24px 20px; }
    .avatar-cropper-container { padding: 20px; text-align: center; }
    .avatar-cropper-container .img-container { max-width: 100%; max-height: 400px; margin: 0 auto; }
    .avatar-cropper-container .img-container img { display: block; max-width: 100%; }
    .avatar-cropper-btns { padding: 15px 20px; text-align: center; border-top: 1px solid #eee; }
    /* 分段 Tabs */
    .bacfg-seg-tabs { display:inline-flex; background:#eef1f5; border-radius:8px; padding:3px; margin-bottom:14px; }
    .bacfg-seg-btn { border:none; outline:none; background:transparent; padding:8px 20px; font-size:14px; font-weight:500; color:#667797; cursor:pointer; border-radius:6px; transition:all .2s; line-height:1; }
    .bacfg-seg-btn.is-active { background:#fff; color:#111827; box-shadow:0 1px 3px rgba(0,0,0,.08); }
    .bacfg-seg-btn:hover:not(.is-active) { color:#111827; }
    @media (max-width: 1100px) {
        .bacfg-perm-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
        .bacfg-toolbar { align-items: stretch; }
        .bacfg-toolbar .layui-form { width:100%; }
        .bacfg-toolbar .layui-input-inline { width:100%; }
    }
</style>

<!-- 分段 Tabs -->
<div class="bacfg-seg-tabs" id="bacfg-seg-tabs">
    <button class="bacfg-seg-btn is-active" data-target="account">后台账户</button>
    <button class="bacfg-seg-btn" data-target="group">用户组权限</button>
</div>

<!-- 当前后台账户个人资料 -->
<link rel="stylesheet" href="<?= DC_URL ?>admin/views/css/cropper.min.css">
<script src="<?= DC_URL ?>admin/views/js/cropper.min.js"></script>
<div class="admin-profile-card" style="margin-bottom:12px;">
    <img src="<?= $currentAdmin['avatar_url'] ?>" class="admin-profile-avatar" id="profileCardAvatar">
    <div class="admin-profile-info">
        <div class="admin-profile-name">
            <span id="profileCardNickname"><?= htmlspecialchars($currentAdmin['nickname']) ?></span>
            <span class="role-tag"><?= $currentAdmin['role_name'] ?></span>
        </div>
        <div class="admin-profile-meta">
            <span><i class="ri-user-line"></i> <em id="profileCardUsername"><?= htmlspecialchars($currentAdmin['username']) ?></em></span>
            <span><i class="ri-mail-line"></i> <em id="profileCardEmail" style="font-style:normal;"><?= htmlspecialchars($currentAdmin['email'] ?: '未绑定邮箱') ?></em><?php if (empty($currentAdmin['email'])): ?> <em id="profileCardEmailHint" style="color:#f59e0b;font-style:normal;font-size:11px;">绑定邮箱可找回密码</em><?php endif ?></span>
            <span><i class="ri-time-line"></i> 注册于 <?= date('Y-m-d', $currentAdmin['create_time']) ?></span>
        </div>
    </div>
    <div class="admin-profile-actions">
        <button type="button" class="layui-btn layui-btn-sm" id="openProfileEdit"><i class="ri-edit-line"></i> 编辑资料</button>
        <button type="button" class="layui-btn layui-btn-sm layui-bg-blue" id="openPasswordEdit"><i class="ri-lock-password-line"></i> 修改密码</button>
    </div>
</div>

<div class="bacfg-panel is-active" data-panel="account">
    <table class="layui-hide" id="bacfg-admin" lay-filter="bacfg-admin"></table>
</div>
<div class="bacfg-panel" data-panel="group">
    <table class="layui-hide" id="bacfg-group" lay-filter="bacfg-group"></table>
</div>

<script type="text/html" id="bacfg-admin-toolbar">
    <div style="display:flex;align-items:center;justify-content:center;position:relative;padding:10px 0 15px;border-bottom:1px solid #f0f0f0;">
        <span style="position:absolute;left:0;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">后台账户</span>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;width:100%;flex-wrap:wrap;gap:8px;">
        <div class="layui-btn-container">
            <button class="layui-btn layui-btn-sm layui-btn-normal" lay-event="addAdmin"><i class="ri-add-line"></i> 添加</button>
        </div>
        <form class="layui-form" style="display:flex;align-items:center;gap:6px;margin:0;flex-wrap:wrap;">
            <div class="layui-input-inline layui-input-wrap" style="width:200px;margin:0;">
                <input type="text" name="admin_keyword" placeholder="登录账号/昵称/UID" lay-affix="clear" class="layui-input">
            </div>
            <button class="layui-btn layui-btn-sm" lay-submit lay-filter="bacfg-admin-search">搜索</button>
            <button type="reset" class="layui-btn layui-btn-sm layui-btn-primary">重置</button>
        </form>
    </div>
</script>
<script type="text/html" id="bacfg-group-toolbar">
    <div style="display:flex;align-items:center;justify-content:center;position:relative;padding:10px 0 15px;border-bottom:1px solid #f0f0f0;">
        <span style="position:absolute;left:0;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">用户组权限</span>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;width:100%;flex-wrap:wrap;gap:8px;">
        <div class="layui-btn-container">
            <button class="layui-btn layui-btn-sm layui-btn-normal" lay-event="addGroup"><i class="ri-add-line"></i> 新增用户组</button>
        </div>
        <form class="layui-form" style="display:flex;align-items:center;gap:6px;margin:0;flex-wrap:wrap;">
            <div class="layui-input-inline layui-input-wrap" style="width:200px;margin:0;">
                <input type="text" name="group_keyword" placeholder="搜索用户组" lay-affix="clear" class="layui-input">
            </div>
            <button class="layui-btn layui-btn-sm" lay-submit lay-filter="bacfg-group-search">搜索</button>
            <button type="reset" class="layui-btn layui-btn-sm layui-btn-primary">重置</button>
        </form>
    </div>
    <div style="padding:0;color:#6b7280;font-size:12px;">默认后台组始终保留、菜单全开且不允许删除。</div>
</script>
<script type="text/html" id="bacfg-role-tpl">
    {{# if(d.is_founder == 1){ }}
    <span class="bacfg-badge founder"><i class="ri-vip-crown-2-line"></i>{{ d.role_name }}</span>
    {{# } else { }}
    <span class="bacfg-badge backend"><i class="ri-shield-user-line"></i>{{ d.role_name }}</span>
    {{# } }}
</script>

<script type="text/html" id="bacfg-candidate-action">
    <button class="layui-btn layui-btn-xs" lay-event="promote"><i class="ri-user-follow-line"></i> 开通管理</button>
</script>

<script type="text/html" id="bacfg-admin-group-tpl">
    <span class="bacfg-group-tip"><i class="ri-team-line"></i>{{ d.group_name }}</span>
</script>

<script type="text/html" id="bacfg-admin-action">
    {{# if(d.can_change_group == 1){ }}
    <button class="layui-btn layui-btn-xs layui-btn-primary" lay-event="changeGroup"><i class="ri-exchange-line"></i> 调整用户组</button>
    {{# } else { }}
    <span class="layui-btn layui-btn-xs layui-btn-primary layui-btn-disabled">用户组锁定</span>
    {{# } }}
    {{# if(d.can_revoke == 1){ }}
    <button class="layui-btn layui-btn-xs layui-btn-danger" lay-event="revoke"><i class="ri-user-unfollow-line"></i> 取消后台</button>
    {{# } else if(d.is_founder == 1) { }}
    <span class="layui-btn layui-btn-xs layui-btn-primary layui-btn-disabled">创始人保护</span>
    {{# } else { }}
    <span class="layui-btn layui-btn-xs layui-btn-primary layui-btn-disabled">当前账号</span>
    {{# } }}
</script>

<script type="text/html" id="bacfg-group-action">
    {{# if(d.is_default_group == 1){ }}
    <span class="layui-btn layui-btn-xs layui-btn-primary layui-btn-disabled">默认后台组</span>
    <span class="layui-btn layui-btn-xs layui-btn-primary layui-btn-disabled">不可删除</span>
    {{# } else { }}
    <button class="layui-btn layui-btn-xs layui-btn-primary" lay-event="edit"><i class="ri-edit-line"></i> 编辑</button>
    {{# if(d.account_count > 0){ }}
    <span class="layui-btn layui-btn-xs layui-btn-primary layui-btn-disabled">已绑定账户</span>
    {{# } else { }}
    <button class="layui-btn layui-btn-xs layui-btn-danger" lay-event="delete"><i class="ri-delete-bin-line"></i> 删除</button>
    {{# } }}
    {{# } }}
</script>

<div style="height:96px;"></div>

<script>
layui.use(['table', 'form', 'element'], function(){
    var table = layui.table;
    var form = layui.form;
    var $ = layui.$;
    var layer = layui.layer;
    var TOKEN = '<?= LoginAuth::genToken() ?>';
    var permissionGroups = <?= json_encode($backendPermissionGroups, JSON_UNESCAPED_UNICODE) ?>;
    var permissionPresets = <?= json_encode($backendPermissionPresets, JSON_UNESCAPED_UNICODE) ?>;
    var groupCache = [];
    var groupTableReady = false;

    function getGroupById(id) {
        id = parseInt(id || 0, 10);
        for (var i = 0; i < groupCache.length; i++) {
            if (parseInt(groupCache[i].id, 10) === id) {
                return groupCache[i];
            }
        }
        return null;
    }

    function parsePermissionKeys(raw) {
        if ($.isArray(raw)) {
            return raw;
        }
        if (!raw) {
            return [];
        }
        if (typeof raw === 'string') {
            try {
                var parsed = JSON.parse(raw);
                return $.isArray(parsed) ? parsed : [];
            } catch (e) {
                return [];
            }
        }
        return [];
    }

    function decodeHtmlText(text) {
        return $('<textarea/>').html(text || '').text();
    }

    function loadGroups(callback) {
        $.getJSON('setting.php?action=admin_group_all', function(res){
            groupCache = (res && res.data) ? res.data : [];
            if ($.isFunction(callback)) {
                callback(groupCache);
            }
        });
    }

    function renderGroupOptions(selectedId) {
        selectedId = parseInt(selectedId || 0, 10);
        var html = '<option value="">请选择用户组</option>';
        for (var i = 0; i < groupCache.length; i++) {
            var group = groupCache[i];
            var gid = parseInt(group.id, 10);
            var groupName = $('<div/>').text(decodeHtmlText(group.name)).html();
            html += '<option value="' + gid + '"' + (gid === selectedId ? ' selected' : '') + '>' + groupName + '</option>';
        }
        return html;
    }

    function getPermissionPresetById(presetId) {
        for (var i = 0; i < permissionPresets.length; i++) {
            if (String(permissionPresets[i].id) === String(presetId)) {
                return permissionPresets[i];
            }
        }
        return null;
    }

    function getAllPermissionKeys() {
        var keyMap = {};
        var keys = [];
        for (var i = 0; i < permissionGroups.length; i++) {
            var children = permissionGroups[i].children || [];
            for (var j = 0; j < children.length; j++) {
                var key = String(children[j].id || '');
                if (key !== '' && !keyMap[key]) {
                    keyMap[key] = true;
                    keys.push(key);
                }
            }
        }
        return keys;
    }

    function renderPermissionPresetBar() {
        var html = '<div class="bacfg-preset-bar">';
        for (var i = 0; i < permissionPresets.length; i++) {
            var preset = permissionPresets[i];
            var presetLabel = $('<div/>').text(preset.label || '').html();
            html += '<button type="button" class="layui-btn layui-btn-sm layui-btn-primary bacfg-preset-btn" data-preset="' + preset.id + '">' + presetLabel + '</button>';
        }
        html += '<button type="button" class="layui-btn layui-btn-sm layui-btn-primary bacfg-preset-btn" data-preset="__all">全部权限</button>';
        html += '<button type="button" class="layui-btn layui-btn-sm layui-btn-primary bacfg-preset-btn" data-preset="__clear">清空勾选</button>';
        html += '</div>';
        html += '<div class="bacfg-modal-tip" style="margin:-2px 0 12px;">点击预设可一键推荐勾选，之后仍可继续微调。</div>';
        return html;
    }

    function renderPermissionMatrix(selectedKeys) {
        selectedKeys = selectedKeys || [];
        var selectedMap = {};
        for (var i = 0; i < selectedKeys.length; i++) {
            selectedMap[selectedKeys[i]] = true;
        }
        var html = '<div class="bacfg-perm-grid">';
        for (var g = 0; g < permissionGroups.length; g++) {
            var group = permissionGroups[g];
            var groupLabel = $('<div/>').text(group.label || '').html();
            html += '<div class="bacfg-perm-card" data-parent="' + group.id + '">';
            html += '<div class="bacfg-perm-head"><label class="bacfg-check-item bacfg-parent-label"><input type="checkbox" class="bacfg-parent-check" data-parent="' + group.id + '"><span>' + groupLabel + '</span></label></div>';
            html += '<div class="bacfg-perm-list">';
            var children = group.children || [];
            if (!children.length) {
                html += '<div class="bacfg-empty-inline">暂无可配置菜单</div>';
            }
            for (var c = 0; c < children.length; c++) {
                var child = children[c];
                var childLabel = $('<div/>').text(child.label || '').html();
                html += '<label class="bacfg-check-item bacfg-child-label"><input type="checkbox" class="bacfg-child-check" data-parent="' + group.id + '" value="' + child.id + '"' + (selectedMap[child.id] ? ' checked' : '') + '><span>' + childLabel + '</span></label>';
            }
            html += '</div></div>';
        }
        html += '</div>';
        return html;
    }

    function applyPermissionSelection(scope, keys) {
        keys = keys || [];
        var selectedMap = {};
        for (var i = 0; i < keys.length; i++) {
            selectedMap[String(keys[i])] = true;
        }
        scope.find('.bacfg-child-check').each(function(){
            $(this).prop('checked', !!selectedMap[$(this).val()]);
        });
        syncParentChecks(scope);
    }

    function syncParentChecks(scope) {
        scope.find('.bacfg-perm-card').each(function(){
            var card = $(this);
            var children = card.find('.bacfg-child-check');
            var checked = children.filter(':checked').length;
            var parentCheck = card.find('.bacfg-parent-check').get(0);
            if (!parentCheck) {
                return;
            }
            parentCheck.checked = children.length > 0 && checked === children.length;
            parentCheck.indeterminate = checked > 0 && checked < children.length;
        });
    }

    function bindPermissionMatrix(scope) {
        scope.off('change', '.bacfg-parent-check').on('change', '.bacfg-parent-check', function(){
            var parent = $(this).data('parent');
            this.indeterminate = false;
            scope.find('.bacfg-child-check[data-parent="' + parent + '"]').prop('checked', this.checked);
        });
        scope.off('change', '.bacfg-child-check').on('change', '.bacfg-child-check', function(){
            syncParentChecks(scope);
        });
        syncParentChecks(scope);
    }

    function bindPermissionPresetActions(scope) {
        scope.off('click', '.bacfg-preset-btn').on('click', '.bacfg-preset-btn', function(){
            var presetId = String($(this).data('preset') || '');
            var preset = getPermissionPresetById(presetId);
            var keys = [];
            var msg = '';
            if (presetId === '__all') {
                keys = getAllPermissionKeys();
                msg = '已勾选全部菜单';
            } else if (presetId === '__clear') {
                msg = '已清空勾选';
            } else if (preset) {
                keys = preset.permissions || [];
                msg = '已套用“' + preset.label + '”预设';
            } else {
                return;
            }
            applyPermissionSelection(scope, keys);
            layer.msg(msg, {time: 1000});
        });
    }

    function openPromoteDialog(userRow) {
        if (!groupCache.length) {
            layer.msg('请先在“用户组权限”里新增至少一个用户组');
            return;
        }
        var html = ''
            + '<div class="bacfg-modal">'
            + '  <div class="bacfg-modal-row">'
            + '      <label class="bacfg-modal-label">目标用户</label>'
            + '      <div class="bacfg-modal-tip">UID：' + userRow.uid + '，登录账号：' + userRow.login + '，昵称：' + userRow.nickname + '</div>'
            + '  </div>'
            + '  <div class="bacfg-modal-row">'
            + '      <label class="bacfg-modal-label">选择用户组</label>'
            + '      <select id="bacfg-promote-group" class="layui-input">' + renderGroupOptions(0) + '</select>'
            + '      <div class="bacfg-modal-tip">后台账户开通后，会按所选用户组显示后台菜单。</div>'
            + '  </div>'
            + '</div>';
        layer.open({
            type: 1,
            title: '开通后台账户',
            area: ['420px', 'auto'],
            skin: 'dc-layer-modern',
            content: html,
            btn: ['确认开通', '取消'],
            success: function(){ form.render('select'); },
            yes: function(index){
                var groupId = parseInt($('#bacfg-promote-group').val() || 0, 10);
                if (groupId <= 0) {
                    layer.msg('请选择用户组');
                    return false;
                }
                $.post('setting.php?action=admin_account_set', { uid: userRow.uid, group_id: groupId, token: TOKEN }, function(e){
                    if (e.code == 400) return layer.msg(e.msg);
                    layer.close(index);
                    layer.msg('已开通后台账户');
                    reloadCandidate(false);
                    reloadAdminList(false);
                    reloadGroupList(false);
                }, 'json');
                return false;
            }
        });
    }

    function openChangeGroupDialog(row) {
        if (!groupCache.length) {
            layer.msg('请先新增用户组');
            return;
        }
        var html = ''
            + '<div class="bacfg-modal">'
            + '  <div class="bacfg-modal-row">'
            + '      <label class="bacfg-modal-label">后台账户</label>'
            + '      <div class="bacfg-modal-tip">UID：' + row.uid + '，登录账号：' + row.login + '，当前用户组：' + row.group_name + '</div>'
            + '  </div>'
            + '  <div class="bacfg-modal-row">'
            + '      <label class="bacfg-modal-label">调整为</label>'
            + '      <select id="bacfg-change-group" class="layui-input">' + renderGroupOptions(row.admin_group_id) + '</select>'
            + '  </div>'
            + '</div>';
        layer.open({
            type: 1,
            title: '调整用户组',
            area: ['420px', 'auto'],
            skin: 'dc-layer-modern',
            content: html,
            btn: ['确认调整', '取消'],
            success: function(){ form.render('select'); },
            yes: function(index){
                var groupId = parseInt($('#bacfg-change-group').val() || 0, 10);
                if (groupId <= 0) {
                    layer.msg('请选择用户组');
                    return false;
                }
                $.post('setting.php?action=admin_account_group_save', { uid: row.uid, group_id: groupId, token: TOKEN }, function(e){
                    if (e.code == 400) return layer.msg(e.msg);
                    layer.close(index);
                    layer.msg('已调整用户组');
                    reloadAdminList(false);
                }, 'json');
                return false;
            }
        });
    }

    function openGroupDialog(groupRow) {
        if (groupRow && parseInt(groupRow.is_default_group || 0, 10) === 1) {
            layer.msg('默认后台组为系统保留组，菜单始终全开，无需编辑');
            return;
        }
        var selectedKeys = groupRow ? parsePermissionKeys(groupRow.menu_permissions) : [];
        var groupName = groupRow ? $('<div/>').text(decodeHtmlText(groupRow.name)).html() : '';
        var html = ''
            + '<div class="bacfg-modal-fill">'
            + '  <div class="bacfg-modal-scroll">'
            + '      <div class="bacfg-modal-row">'
            + '          <label class="bacfg-modal-label">用户组名称</label>'
            + '          <input type="text" id="bacfg-group-name" class="layui-input" maxlength="30" value="' + groupName + '" placeholder="请输入用户组名称">'
            + '      </div>'
            + '      <div class="bacfg-modal-row">'
            + '          <label class="bacfg-modal-label">可见菜单</label>'
            + '          ' + renderPermissionPresetBar()
            + '          <div id="bacfg-permission-box">' + renderPermissionMatrix(selectedKeys) + '</div>'
            + '          <div class="bacfg-modal-tip">这里统一控制后台菜单显示与页面访问，已接入权限树的插件菜单也会一起生效。未勾选的菜单不会显示，直接访问也会被拦截。</div>'
            + '      </div>'
            + '  </div>'
            + '</div>';
        var dialogWidth = Math.min($(window).width() - 40, 920);
        var dialogHeight = Math.min(Math.max($(window).height() - 120, 620), 820);
        layer.open({
            type: 1,
            title: groupRow ? '编辑用户组' : '新增用户组',
            area: [dialogWidth + 'px', dialogHeight + 'px'],
            skin: 'dc-layer-modern',
            content: html,
            btn: [groupRow ? '保存修改' : '创建用户组', '取消'],
            success: function(layero){
                layero.find('.layui-layer-content').css('overflow', 'hidden');
                bindPermissionMatrix(layero);
                bindPermissionPresetActions(layero);
            },
            yes: function(index, layero){
                var name = $.trim($('#bacfg-group-name').val());
                var permissions = [];
                layero.find('.bacfg-child-check:checked').each(function(){
                    permissions.push($(this).val());
                });
                $.post('setting.php?action=admin_group_save', {
                    id: groupRow ? groupRow.id : 0,
                    name: name,
                    menu_permissions: permissions,
                    token: TOKEN
                }, function(e){
                    if (e.code == 400) return layer.msg(e.msg);
                    layer.close(index);
                    layer.msg(groupRow ? '用户组已更新' : '用户组已创建');
                    loadGroups(function(){
                        reloadGroupList(false);
                        reloadAdminList(false);
                    });
                }, 'json');
                return false;
            }
        });
    }

    var candidateTableRendered = false;

    function openAddAdminDialog(){
        var dialogWidth = Math.min($(window).width() - 40, 1100);
        var dialogHeight = Math.min(Math.max($(window).height() - 120, 560), 760);
        layer.open({
            type: 1,
            title: '添加后台账户',
            area: [dialogWidth + 'px', dialogHeight + 'px'],
            skin: 'dc-layer-modern',
            shadeClose: true,
            content: '<div class="bacfg-search-dialog">'
                + '<div class="bacfg-toolbar bacfg-search-toolbar">'
                + '<div class="bacfg-tip">输入 UID、登录账号、昵称、手机或邮箱后搜索普通用户，点击「开通管理」即可开通。</div>'
                + '<form class="layui-form" style="display:flex;align-items:center;gap:8px;">'
                + '<div class="layui-input-inline" style="width:240px;margin:0;"><input type="text" name="candidate_keyword" placeholder="搜索普通用户" autocomplete="off" class="layui-input"></div>'
                + '<button class="layui-btn layui-btn-sm" lay-submit lay-filter="bacfg-candidate-search"><i class="ri-search-line"></i> 搜索</button>'
                + '<button type="button" class="layui-btn layui-btn-sm layui-btn-primary" id="bacfg-candidate-reset">重置</button>'
                + '</form></div>'
                + '<div class="bacfg-search-table-wrap"><table class="layui-hide" id="bacfg-candidate" lay-filter="bacfg-candidate"></table></div>'
                + '</div>',
            success: function(layero){
                layero.find('.layui-layer-content').css('overflow', 'hidden');
                var tableHeight = layero.find('.bacfg-search-dialog').innerHeight() - layero.find('.bacfg-search-toolbar').outerHeight(true) - 4;
                table.render({
                    elem: '#bacfg-candidate',
                    id: 'bacfg-candidate-table',
                    url: 'setting.php?action=admin_account_search',
                    where: { keyword: '' },
                    height: Math.max(tableHeight, 280),
                    page: true,
                    limits: [10, 20, 50],
                    limit: 10,
                    text: { none: '请输入关键词后搜索普通用户' },
                    cols: [[
                        {field:'uid', title:'UID', width:70, sort:true},
                        {field:'login', title:'登录账号', minWidth:120},
                        {field:'nickname', title:'昵称', minWidth:100},
                        {field:'tel', title:'手机号码', minWidth:120},
                        {field:'email', title:'邮箱', minWidth:160},
                        {field:'create_time', title:'注册时间', width:140},
                        {fixed: 'right', title:'操作', templet:'#bacfg-candidate-action', width:120, align:'center'}
                    ]]
                });
                candidateTableRendered = true;
                layero.find('#bacfg-candidate-reset').on('click', function(){ layero.find('input[name="candidate_keyword"]').val(''); reloadCandidate(true); });
                form.render();
            },
            end: function(){ candidateTableRendered = false; }
        });
    }

    window.backendAccountTable = table.render({
        elem: '#bacfg-admin',
        id: 'bacfg-admin-table',
        toolbar: '#bacfg-admin-toolbar',
        defaultToolbar: [],
        url: 'setting.php?action=admin_account_index',
        where: { keyword: '' },
        page: true,
        limits: [10, 20, 50],
        limit: 10,
        text: { none: '暂无后台账户' },
        cols: [[
            {field:'uid', title:'UID', width:80, sort:true},
            {field:'login', title:'登录账号', minWidth:140},
            {field:'nickname', title:'昵称', width:80},
            {field:'email', title:'邮箱', minWidth:160, templet: function(d){ return d.email ? d.email : '<span style="color:#9ca3af">未绑定</span>'; }},
            {field:'role_name', title:'账户类型', width:105, templet:'#bacfg-role-tpl'},
            {field:'group_name', title:'用户组', minWidth:160, templet:'#bacfg-admin-group-tpl'},
            {field:'permission_summary', title:'可见菜单', minWidth:220},
            {field:'create_time', title:'注册时间', width:150},
            {fixed: 'right', title:'操作', templet:'#bacfg-admin-action', minWidth:190, align:'center'}
        ]]
    });
    table.on('toolbar(bacfg-admin)', function(obj){
        if (obj.event === 'addAdmin') { openAddAdminDialog(); }
    });

    function renderGroupTable() {
        if (groupTableReady) {
            return;
        }
        groupTableReady = true;
        window.backendGroupTable = table.render({
            elem: '#bacfg-group',
            id: 'bacfg-group-table',
            toolbar: '#bacfg-group-toolbar',
            defaultToolbar: [],
            url: 'setting.php?action=admin_group_index',
            where: { keyword: '' },
            page: true,
            limits: [10, 20, 50],
            limit: 10,
            text: { none: '暂无用户组，请先新增' },
            cols: [[
                {field:'id', title:'ID', width:80, sort:true},
                {field:'name', title:'用户组名称', minWidth:180},
                {field:'permission_summary', title:'可见菜单', minWidth:260},
                {field:'account_count', title:'绑定账户数', width:120, sort:true},
                {field:'update_time_text', title:'更新时间', width:160},
                {fixed: 'right', title:'操作', templet:'#bacfg-group-action', width:180, align:'center'}
            ]]
        });
        table.on('toolbar(bacfg-group)', function(obj){
            if (obj.event === 'addGroup') { openGroupDialog(null); }
        });
    }

    function switchPageTab(target) {
        $('#bacfg-seg-tabs .bacfg-seg-btn').removeClass('is-active');
        $('#bacfg-seg-tabs .bacfg-seg-btn[data-target="' + target + '"]').addClass('is-active');
        $('.bacfg-panel').removeClass('is-active');
        $('.bacfg-panel[data-panel="' + target + '"]').addClass('is-active');
        if (target === 'group') {
            renderGroupTable();
            table.resize('bacfg-group-table');
        } else {
            table.resize('bacfg-admin-table');
        }
    }
    $('#bacfg-seg-tabs').on('click', '.bacfg-seg-btn', function(){
        switchPageTab($(this).data('target'));
    });

    function reloadCandidate(resetPage) {
        if (!candidateTableRendered) return;
        table.reload('bacfg-candidate-table', {
            page: resetPage ? { curr: 1 } : undefined,
            where: { keyword: $.trim($('input[name="candidate_keyword"]').val()) }
        });
    }

    function reloadAdminList(resetPage) {
        table.reload('bacfg-admin-table', {
            page: resetPage ? { curr: 1 } : undefined,
            where: { keyword: $.trim($('input[name="admin_keyword"]').val()) }
        });
    }

    function reloadGroupList(resetPage) {
        if (!groupTableReady) {
            renderGroupTable();
            return;
        }
        table.reload('bacfg-group-table', {
            page: resetPage ? { curr: 1 } : undefined,
            where: { keyword: $.trim($('input[name="group_keyword"]').val()) }
        });
    }

    form.on('submit(bacfg-candidate-search)', function(){ reloadCandidate(true); return false; });
    form.on('submit(bacfg-admin-search)', function(){ reloadAdminList(true); return false; });
    form.on('submit(bacfg-group-search)', function(){ reloadGroupList(true); return false; });


    table.on('tool(bacfg-candidate)', function(obj){
        if (obj.event === 'promote') {
            openPromoteDialog(obj.data);
        }
    });

    table.on('tool(bacfg-admin)', function(obj){
        if (obj.event === 'changeGroup') {
            openChangeGroupDialog(obj.data);
            return;
        }
        if (obj.event === 'revoke') {
            layer.confirm('确认取消“' + obj.data.nickname + '”的管理权限吗？取消后，该账号将回到普通用户列表。', {
                btn: ['确认取消', '保留'],
                icon: 3,
                title: '取消管理权限'
            }, function(index){
                layer.close(index);
                $.post('setting.php?action=admin_account_cancel', { uid: obj.data.uid, token: TOKEN }, function(e){
                    if (e.code == 400) return layer.msg(e.msg);
                    layer.msg('已取消管理权限');
                    reloadCandidate(false);
                    reloadAdminList(false);
                    reloadGroupList(false);
                }, 'json');
            });
        }
    });

    table.on('tool(bacfg-group)', function(obj){
        if (obj.event === 'edit') {
            var cacheRow = getGroupById(obj.data.id) || obj.data;
            openGroupDialog(cacheRow);
            return;
        }
        if (obj.event === 'delete') {
            layer.confirm('确认删除用户组“' + obj.data.name + '”吗？删除后不可恢复。', {
                btn: ['确认删除', '取消'],
                icon: 3,
                title: '删除用户组'
            }, function(index){
                layer.close(index);
                $.post('setting.php?action=admin_group_delete', { id: obj.data.id, token: TOKEN }, function(e){
                    if (e.code == 400) return layer.msg(e.msg);
                    layer.msg('用户组已删除');
                    loadGroups(function(){
                        reloadGroupList(false);
                        reloadAdminList(false);
                    });
                }, 'json');
            });
        }
    });

    // =====================
    // 当前登录账户 - 编辑资料 / 修改密码
    // =====================
    var profileCropper = null;

    $('#openProfileEdit').click(function() {
        var curEmail = ($('#profileCardEmail').text() || '').trim();
        if (curEmail === '未绑定邮箱') curEmail = '';
        layer.open({
            type: 1,
            title: '编辑个人资料',
            area: ['460px', 'auto'],
            resize: false,
            shadeClose: true,
            content: '<div class="profile-edit-section">'
                + '<div class="profile-edit-row"><label>头像</label>'
                + '<div class="profile-edit-avatar-box">'
                + '<label for="popup_upload_image" style="cursor:pointer">'
                + '<img src="' + $('#profileCardAvatar').attr('src') + '" id="popupAvatar">'
                + '<input type="file" id="popup_upload_image" accept="image/*" style="display:none">'
                + '</label><span class="tip">点击头像更换</span></div></div>'
                + '<div class="layui-form profile-edit-row" lay-filter="profile-edit-form">'
                + '<label>昵称</label><input class="layui-input" name="name" id="popupNickname" value="' + $('#profileCardNickname').text() + '" maxlength="20" required></div>'
                + '<div class="layui-form profile-edit-row" lay-filter="profile-edit-form">'
                + '<label>登录账号</label><input class="layui-input" name="username" id="popupUsername" value="' + $('#profileCardUsername').text() + '"></div>'
                + '<div class="layui-form profile-edit-row" lay-filter="profile-edit-form">'
                + '<label>邮箱</label><input class="layui-input" name="email" id="popupEmail" type="email" value="' + curEmail + '" placeholder="绑定邮箱可用于找回密码"></div>'
                + '</div><div class="profile-edit-btns"><button type="button" class="layui-btn" id="profileSaveBtn">保存</button></div>',
            success: function(layero, index) {
                form.render(null, 'profile-edit-form');
                layero.find('#popup_upload_image').change(function(e) {
                    var files = e.target.files;
                    if (!files || !files.length) return;
                    var file = files[0];
                    if (!file.type.startsWith('image')) return layer.msg('只能上传图片');
                    var reader = new FileReader();
                    reader.onload = function(ev) { openProfileCropper(ev.target.result, file); };
                    reader.readAsDataURL(file);
                    $(this).val('');
                });
                layero.find('#profileSaveBtn').click(function() {
                    var name = layero.find('#popupNickname').val();
                    var username = layero.find('#popupUsername').val();
                    var email = layero.find('#popupEmail').val().trim();
                    if (!name) return layer.msg('昵称不能为空');
                    $.ajax({
                        type: 'POST',
                        url: 'setting.php?action=profile_update',
                        data: { name: name, username: username, email: email, description: '', token: TOKEN },
                        dataType: 'json',
                        success: function(r) {
                            if (r.code != 0) return layer.msg(r.msg);
                            $('#profileCardNickname').text(name);
                            $('#profileCardUsername').text(username);
                            var displayEmail = email || '未绑定邮箱';
                            $('#profileCardEmail').text(displayEmail);
                            var $emailHint = $('#profileCardEmailHint');
                            if (email) { $emailHint.hide(); } else { $emailHint.show(); }
                            layer.close(index);
                            layer.msg('账号信息已保存');
                        },
                        error: function(xhr) { try { layer.msg(JSON.parse(xhr.responseText).msg); } catch(e) { layer.msg('保存失败'); } }
                    });
                });
            }
        });
    });

    $('#openPasswordEdit').click(function() {
        layer.open({
            type: 1, area: '350px', resize: false, shadeClose: true, title: '修改密码',
            content: '<div class="layui-form" lay-filter="pwd-form" style="padding:20px;">'
                + '<div class="layui-form-item"><div class="layui-input-wrap"><div class="layui-input-prefix"><i class="ri-lock-password-line"></i></div>'
                + '<input type="password" name="new_passwd" lay-verify="required" placeholder="新密码" class="layui-input" lay-affix="eye"></div></div>'
                + '<div class="layui-form-item"><div class="layui-input-wrap"><div class="layui-input-prefix"><i class="ri-lock-password-line"></i></div>'
                + '<input type="password" name="new_passwd2" lay-verify="required" placeholder="确认密码" class="layui-input" lay-affix="eye"></div></div>'
                + '<input name="token" value="' + TOKEN + '" type="hidden"/>'
                + '<div class="layui-form-item"><button class="layui-btn layui-btn-fluid" lay-submit lay-filter="pwd-save">保存密码</button></div></div>',
            success: function(layero, index) {
                form.render(null, 'pwd-form');
                form.on('submit(pwd-save)', function(data) {
                    $.ajax({
                        type: 'POST', url: 'setting.php?action=profile_change_password',
                        data: data.field, dataType: 'json',
                        success: function(e) {
                            if (e.code == 400) return layer.msg(e.msg);
                            layer.close(index);
                            layer.msg('修改成功');
                        },
                        error: function(xhr) { try { layer.msg(JSON.parse(xhr.responseText).msg); } catch(e) { layer.msg('修改失败'); } }
                    });
                    return false;
                });
            }
        });
    });

    function openProfileCropper(imageSrc, originalFile) {
        layer.open({
            type: 1, title: '裁剪头像', area: ['500px', '550px'], shadeClose: false, move: false,
            content: '<div class="avatar-cropper-container"><div class="img-container"><img id="cropper-image" src="' + imageSrc + '"></div></div>'
                + '<div class="avatar-cropper-btns"><button type="button" class="layui-btn layui-btn-primary" id="btn-cancel">取消</button>'
                + '<button type="button" class="layui-btn" id="btn-use-original">使用原图</button>'
                + '<button type="button" class="layui-btn layui-bg-green" id="btn-crop">裁剪并保存</button></div>',
            success: function(layero2, idx2) {
                var image = document.getElementById('cropper-image');
                profileCropper = new Cropper(image, { aspectRatio: 1, viewMode: 1, dragMode: 'move', autoCropArea: 0.8, restore: false, guides: true, center: true, highlight: false, cropBoxMovable: true, cropBoxResizable: true, toggleDragModeOnDblclick: false });
                $('#btn-cancel').click(function() { destroyProfileCropper(); layer.close(idx2); });
                $('#btn-use-original').click(function() { uploadProfileAvatar(originalFile, originalFile.name, idx2); });
                $('#btn-crop').click(function() {
                    if (!profileCropper) return;
                    profileCropper.getCroppedCanvas({ width: 200, height: 200 }).toBlob(function(blob) { uploadProfileAvatar(blob, 'avatar.jpg', idx2); }, 'image/jpeg', 0.9);
                });
            },
            end: function() { destroyProfileCropper(); }
        });
    }
    function destroyProfileCropper() { if (profileCropper) { profileCropper.destroy(); profileCropper = null; } }
    function uploadProfileAvatar(blob, filename, layerIndex) {
        var fd = new FormData();
        fd.append('image', blob, filename);
        var loadIdx = layer.load(2);
        $.ajax({
            url: 'setting.php?action=profile_update_avatar',
            method: 'POST', data: fd, processData: false, contentType: false,
            success: function(data) {
                layer.close(loadIdx);
                if (data.code == 0) {
                    var newSrc = data.data + '?t=' + Date.now();
                    $('#profileCardAvatar').attr('src', newSrc);
                    $('#popupAvatar').attr('src', newSrc);
                    layer.msg('头像更新成功');
                    destroyProfileCropper();
                    layer.close(layerIndex);
                } else { layer.msg(data.msg || '上传失败'); }
            },
            error: function() { layer.close(loadIdx); layer.msg('上传头像出错了'); }
        });
    }

    loadGroups();
    switchPageTab('account');
});
</script>
