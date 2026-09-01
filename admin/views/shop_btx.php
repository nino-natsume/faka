<?php defined('DC_ROOT') || exit('access denied!'); ?>

<div class="layui-tabs order-tabs-wrapper" style="display:flex;align-items:center;justify-content:space-between;" lay-options="{trigger: false}">
    <ul class="layui-tabs-header">
        <li><a href="./shop.php">商城配置</a></li>
        <li><a href="./shop.php?action=gg">公告设置</a></li>
        <li class="layui-this"><a href="./shop.php?action=btx">下单输入框</a></li>
        <li><a href="./shop.php?action=user">用户配置</a></li>
        <li><a href="./shop.php?action=station_setting">分店配置</a></li>
    </ul>
</div>

<style>
    .btx-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
    .btx-table th { background: #fafafa; font-weight: 600; font-size: 13px; color: #666; text-align: left; padding: 10px 14px; border-bottom: 2px solid #eee; }
    .btx-table td { padding: 10px 14px; border-bottom: 1px solid #f0f0f0; font-size: 13px; vertical-align: middle; }
    .btx-table tr:hover td { background: #f7f9fc; }
    .btx-table .btx-idx { color: #aaa; width: 40px; }
    .btx-table .btx-type { width: 80px; }
    .btx-table .btx-req { width: 70px; text-align: center; }
    .btx-table .btx-type-tag { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 12px; }
    .btx-table .btx-req-tag { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 12px; cursor: pointer; }
    .btx-table .btx-ops { width: 80px; text-align: center; }
    .btx-table .btx-edit { color: #1e9fff; cursor: pointer; font-size: 14px; margin-right: 8px; }
    .btx-table .btx-edit:hover { color: #0d8de7; }
    .btx-table .btx-del { color: #ff5722; cursor: pointer; font-size: 14px; }
    .btx-table .btx-del:hover { color: #d32f2f; }
    .btx-table .btx-tip { color: #999; font-size: 12px; margin-top: 2px; }
    .btx-empty { text-align: center; color: #bbb; padding: 30px 0; font-size: 13px; }
    .btx-email-tip { background: #e8f4ff; border-left: 3px solid #1e9fff; padding: 10px 15px; border-radius: 4px; margin-bottom: 16px; font-size: 13px; display: none; }
    .btx-email-tip a { color: #1e9fff; font-weight: 600; }
</style>

<div class="layui-table-view" style="border-radius:8px;overflow:hidden;">
    <div class="layui-card-header" style="display:flex;align-items:center;justify-content:center;position:relative;height:auto;padding:12px 15px;">
        <span style="position:absolute;left:15px;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">下单输入框</span>
    </div>
    <div class="layui-card-body" style="padding:20px;">
        <form action="shop.php?action=btx_save" method="post" name="setting_form" id="setting_form" class="layui-form">

            <!-- ====== 添加区 ====== -->
            <fieldset class="layui-elem-field layui-field-title" style="margin-top: 0;">
                <legend>添加输入框</legend>
            </fieldset>

            <div style="background: #f6f8fa; border-radius: 4px; padding: 12px 16px; margin-bottom: 16px; font-size: 13px; color: #666; line-height: 1.8;">
                <i class="ri-information-line" style="color: #1e9fff;"></i>
                此处设置的输入框为<b style="color:#333;">全局生效</b>，所有商品下单时均会显示。如需针对某个商品单独设置，请在添加/编辑商品时配置。
            </div>

            <div class="layui-form-item">
                <div class="layui-input-inline" style="width: 180px;">
                    <input type="text" class="layui-input" id="keyInput" placeholder="字段名称（如：联系QQ）">
                </div>
                <div class="layui-input-inline" style="width: 200px;">
                    <input type="text" class="layui-input" id="valueInput" placeholder="输入提示（如：请输入QQ号）">
                </div>
                <div class="layui-input-inline" style="width: 110px;">
                    <select id="typeInput" lay-filter="typeSelect">
                        <option value="string" selected>文本</option>
                        <option value="tel">手机号</option>
                        <option value="email">邮箱</option>
                        <option value="num">数字</option>
                    </select>
                </div>
                <div class="layui-input-inline" style="width: 80px;">
                    <select id="requiredInput">
                        <option value="1" selected>必填</option>
                        <option value="0">选填</option>
                    </select>
                </div>
                <div class="layui-input-inline" style="width: 220px;">
                    <input type="text" class="layui-input" id="tipInput" placeholder="说明（如：用于查询订单）">
                </div>
                <div class="layui-input-inline" style="width: auto;">
                    <span id="addBtn" class="layui-btn layui-bg-blue"><i class="ri-add-line"></i> 添加</span>
                </div>
            </div>

            <div id="emailTip" class="btx-email-tip">
                <i class="ri-information-line" style="color: #1e9fff;"></i>
                设置邮箱类型后，如需订单支付成功后自动推送卡密到买家邮箱，请前往<a href="./store.php?action=plu&keyword=订单卡密邮箱推送">应用商店</a>安装【订单卡密邮箱推送】插件。
            </div>

            <!-- ====== 已添加列表 ====== -->
            <fieldset class="layui-elem-field layui-field-title">
                <legend>已添加的输入框 <span id="btxCount" style="font-size: 12px; color: #999; font-weight: normal;"></span></legend>
            </fieldset>

            <div id="keyValueList"></div>

            <textarea name="order_required" id="jsonOutput" style="display:none;"></textarea>

            <!-- ====== 提交 ====== -->
            <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
            <div class="layui-form-item" style="text-align:center;margin-top:20px;">
                <button type="submit" class="layui-btn" lay-submit lay-filter="demo1">保存设置</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </form>
    </div>
</div>

<script>
$(function () {
    $("#setting_form").submit(function (event) {
        event.preventDefault();
        submitForm("#setting_form");
    });

    var TYPE_MAP = { 'string': '文本', 'tel': '手机号', 'email': '邮箱', 'num': '数字' };
    var TYPE_COLOR = { 'string': '#e0e0e0;color:#555', 'tel': '#e8f5e9;color:#2e7d32', 'email': '#e3f2fd;color:#1565c0', 'num': '#fff3e0;color:#e65100' };
    var items = <?= empty($order_required) ? '[]' : $order_required ?>;

    // 兼容旧数据
    items.forEach(function(item) {
        if (typeof item.required === 'undefined') item.required = true;
        if (typeof item.tip === 'undefined') item.tip = '';
    });

    // 添加
    $('#addBtn').click(function() {
        var name = $('#keyInput').val().trim();
        var placeholder = $('#valueInput').val().trim();
        var type = $('#typeInput').val() || 'string';
        var req = $('#requiredInput').val() === '1';
        var tip = $('#tipInput').val().trim();
        if (!name) { layer.msg('请输入字段名称', {icon: 0}); return; }
        items.push({ name: name, placeholder: placeholder, type: type, required: req, tip: tip });
        render();
        $('#keyInput').val('').focus();
        $('#valueInput').val('');
        $('#tipInput').val('');
        layer.msg('已添加: ' + escapeHtml(name), {icon: 1, time: 1200});
    });

    // 回车添加
    $('#keyInput, #valueInput, #tipInput').keypress(function(e) { if (e.which === 13) { e.preventDefault(); $('#addBtn').click(); } });

    // 邮箱提示
    layui.form.on('select(typeSelect)', function(data) {
        $('#emailTip')[data.value === 'email' ? 'slideDown' : 'slideUp'](200);
    });

    // 编辑弹窗
    function openEdit(idx) {
        var item = items[idx];
        var typeOptions = '';
        ['string', 'tel', 'email', 'num'].forEach(function(t) {
            typeOptions += '<option value="' + t + '"' + (item.type === t ? ' selected' : '') + '>' + TYPE_MAP[t] + '</option>';
        });
        var reqOptions = '<option value="1"' + (item.required ? ' selected' : '') + '>必填</option>'
                       + '<option value="0"' + (!item.required ? ' selected' : '') + '>选填</option>';
        var formHtml = '<div style="padding:20px 20px 0;">'
            + '<div class="layui-form-item"><label class="layui-form-label">字段名称</label><div class="layui-input-block"><input type="text" class="layui-input" id="editName" value="' + escapeHtml(item.name) + '"></div></div>'
            + '<div class="layui-form-item"><label class="layui-form-label">输入提示</label><div class="layui-input-block"><input type="text" class="layui-input" id="editPlaceholder" value="' + escapeHtml(item.placeholder || '') + '"></div></div>'
            + '<div class="layui-form-item"><label class="layui-form-label">类型</label><div class="layui-input-block"><select id="editType" lay-ignore style="width:100%;height:38px;padding:0 10px;border:1px solid #e6e6e6;border-radius:2px;font-size:14px;background:#fff;outline:none;appearance:auto;">' + typeOptions + '</select></div></div>'
            + '<div class="layui-form-item"><label class="layui-form-label">是否必填</label><div class="layui-input-block"><select id="editRequired" lay-ignore style="width:100%;height:38px;padding:0 10px;border:1px solid #e6e6e6;border-radius:2px;font-size:14px;background:#fff;outline:none;appearance:auto;">' + reqOptions + '</select></div></div>'
            + '<div class="layui-form-item"><label class="layui-form-label">说明文字</label><div class="layui-input-block"><input type="text" class="layui-input" id="editTip" value="' + escapeHtml(item.tip || '') + '" placeholder="留空则使用模板默认说明"></div></div>'
            + '</div>';
        layer.open({
            type: 1, title: '编辑输入框 - ' + escapeHtml(item.name), area: ['500px', 'auto'], shadeClose: true,
            content: formHtml,
            btn: ['保存', '取消'],
            yes: function(index) {
                var newName = $('#editName').val().trim();
                if (!newName) { layer.msg('字段名称不能为空', {icon: 0}); return; }
                items[idx].name = newName;
                items[idx].placeholder = $('#editPlaceholder').val().trim();
                items[idx].type = $('#editType').val();
                items[idx].required = $('#editRequired').val() === '1';
                items[idx].tip = $('#editTip').val().trim();
                render();
                layer.close(index);
                layer.msg('已保存', {icon: 1, time: 1000});
            }
        });
    }

    // 渲染列表
    function render() {
        var $list = $('#keyValueList').empty();
        $('#btxCount').text(items.length > 0 ? '（共 ' + items.length + ' 项）' : '');
        if (items.length === 0) {
            $list.html('<div class="btx-empty"><i class="ri-file-list-3-line" style="font-size:28px;display:block;margin-bottom:6px;"></i>暂无输入框，请在上方添加</div>');
            $('#jsonOutput').val('[]');
            return;
        }
        var html = '<table class="btx-table"><thead><tr><th class="btx-idx">#</th><th>字段名称</th><th>输入提示</th><th class="btx-type">类型</th><th class="btx-req">必填</th><th>说明</th><th class="btx-ops">操作</th></tr></thead><tbody>';
        items.forEach(function(item, i) {
            var tl = TYPE_MAP[item.type] || item.type;
            var tc = TYPE_COLOR[item.type] || TYPE_COLOR['string'];
            var isReq = item.required !== false;
            var reqHtml = isReq
                ? '<span class="btx-req-tag" style="background:#ffebee;color:#c62828;">必填</span>'
                : '<span class="btx-req-tag" style="background:#e8f5e9;color:#2e7d32;">选填</span>';
            var tipText = item.tip ? escapeHtml(item.tip) : '<span style="color:#ccc;">默认</span>';
            html += '<tr>'
                + '<td class="btx-idx">' + (i + 1) + '</td>'
                + '<td>' + escapeHtml(item.name) + '</td>'
                + '<td style="color:#999;">' + (item.placeholder ? escapeHtml(item.placeholder) : '-') + '</td>'
                + '<td class="btx-type"><span class="btx-type-tag" style="background:' + tc + '">' + tl + '</span></td>'
                + '<td class="btx-req">' + reqHtml + '</td>'
                + '<td class="btx-tip">' + tipText + '</td>'
                + '<td class="btx-ops"><span class="btx-edit" data-idx="' + i + '" title="编辑"><i class="ri-edit-line"></i></span><span class="btx-del" data-idx="' + i + '" title="删除"><i class="ri-delete-bin-line"></i></span></td>'
                + '</tr>';
        });
        html += '</tbody></table>';
        $list.html(html);

        // 点击必填/选填标签切换
        $list.find('.btx-req-tag').click(function() {
            var idx = $(this).closest('tr').find('.btx-del').data('idx');
            items[idx].required = !items[idx].required;
            render();
        });

        // 编辑事件
        $list.find('.btx-edit').click(function() {
            openEdit($(this).data('idx'));
        });

        // 删除事件
        $list.find('.btx-del').click(function() {
            var idx = $(this).data('idx');
            var itemName = items[idx].name;
            layer.confirm('确定删除「' + escapeHtml(itemName) + '」吗？', { icon: 3, title: '删除确认' }, function(index) {
                items.splice(idx, 1);
                render();
                layer.close(index);
                layer.msg('已删除', {icon: 1, time: 1000});
            });
        });

        $('#jsonOutput').val(JSON.stringify(items));
    }

    function escapeHtml(text) {
        if (!text) return '';
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(text));
        return d.innerHTML;
    }

    // 初始渲染
    render();
});
</script>
