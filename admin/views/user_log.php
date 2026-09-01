<style>
.ul-search-bar { margin-bottom: 15px; padding: 15px 20px; border-radius: 6px;background-image: linear-gradient(0deg, #fff, #f3f5f8);border: 2px solid #fff;box-shadow: 8px 8px 20px 0 rgba(55, 99, 170, .1); }
.ul-search-bar .layui-form-item { margin-bottom: 0; }
.ul-search-bar .layui-input-inline { width: 160px; margin-right: 10px; }
.ul-search-row { display:flex; flex-wrap:wrap; gap:8px; align-items:flex-end; justify-content:flex-end; }
.ul-type-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    font-weight: 500;
}
.ul-type-badge:hover { opacity: 0.85; }
.ul-type-register { background: #e6f7ff; color: #1890ff; }
.ul-type-login { background: #f6ffed; color: #52c41a; }
.ul-type-logout { background: #f5f5f5; color: #999; }
.ul-type-balance_inc, .ul-type-balance_recharge { background: #f6ffed; color: #52c41a; }
.ul-type-balance_dec { background: #fff2e8; color: #fa8c16; }
.ul-type-withdraw_apply { background: #fff7e6; color: #faad14; }
.ul-type-withdraw_pass { background: #f6ffed; color: #52c41a; }
.ul-type-withdraw_reject { background: #fff1f0; color: #ff4d4f; }
.ul-type-order_create { background: #e6f7ff; color: #1890ff; }
.ul-type-order_pay { background: #f6ffed; color: #52c41a; }
.ul-type-order_repay { background: #fff7e6; color: #faad14; }
.ul-type-level_change { background: #f9f0ff; color: #722ed1; }
.ul-type-station_open { background: #e6fffb; color: #13c2c2; }
.ul-type-user_forbid { background: #fff1f0; color: #ff4d4f; }
.ul-type-user_unforbid { background: #f6ffed; color: #52c41a; }
.ul-type-admin_create { background: #e6f7ff; color: #1890ff; }
.ul-type-admin_edit { background: #e6f7ff; color: #1890ff; }
.ul-type-admin_money { background: #fff7e6; color: #faad14; }
.ul-type-admin_delete { background: #fff1f0; color: #ff4d4f; }
.ul-type-card_redeem { background: #e6f4ff; color: #1677ff; }
.ul-type-password_reset, .ul-type-password_change { background: #fff7e6; color: #faad14; }
.ul-type-profile_update { background: #e6f7ff; color: #1890ff; }
.ul-type-withdraw_receipt_image_update { background: #e6f7ff; color: #1890ff; }
.ul-type-order_delete { background: #fff1f0; color: #ff4d4f; }
.ul-type-avatar_update { background: #e6f7ff; color: #1890ff; }
.ul-type-level_upgrade { background: #f9f0ff; color: #722ed1; }
.ul-type-auto_upgrade { background: #f9f0ff; color: #722ed1; }
.ul-type-station_upgrade { background: #e6fffb; color: #13c2c2; }
.ul-type-station_auto_upgrade { background: #e6fffb; color: #13c2c2; }
.ul-type-superior_bind { background: #e6f7ff; color: #1890ff; }
.ul-type-superior_bind_manual { background: #fff7e6; color: #faad14; }
.ul-type-balance_commission { background: #f6ffed; color: #52c41a; }
.ul-type-station_commission { background: #f6ffed; color: #52c41a; }
.ul-type-upgrade_commission { background: #f6ffed; color: #52c41a; }
.ul-type-commission_refund { background: #fff2e8; color: #fa8c16; }
.ul-type-commission_fail { background: #fff1f0; color: #ff4d4f; }
.ul-type-upgrade_commission_fail { background: #fff1f0; color: #ff4d4f; }
.ul-type-commission_refund_fail { background: #fff1f0; color: #ff4d4f; }
.ul-type-admin_credits { background: #fff7e6; color: #faad14; }
.ul-type-admin_restore { background: #f6ffed; color: #52c41a; }
.ul-type-admin_permanent_delete { background: #fff1f0; color: #ff4d4f; }
.ul-uid-link {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: 4px;
    border: 1px solid #d6e4ff;
    background: #f0f5ff;
    color: #1890ff;
    cursor: pointer;
    font-weight: 500;
    line-height: 1.4;
    transition: .18s ease;
}
.ul-uid-link:hover {
    color: #1677ff;
    background: #e6f4ff;
    border-color: #91caff;
    text-decoration: none;
}
.ul-amount-pos { color: #52c41a; font-weight: 500; }
.ul-amount-neg { color: #ff4d4f; font-weight: 500; }
.ul-filter-tags { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin-bottom: 10px; min-height: 32px; }
.ul-filter-tag {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 10px; background: #f0f5ff; border: 1px solid #d6e4ff;
    border-radius: 4px; font-size: 13px; color: #1890ff;
}
.ul-filter-tag .ul-tag-close {
    cursor: pointer; font-size: 14px; margin-left: 4px; color: #999;
}
.ul-filter-tag .ul-tag-close:hover { color: #ff4d4f; }
</style>

<div class="ul-filter-tags" id="ul-active-filters"></div>

<table class="layui-hide" id="ul-table" lay-filter="ul-table"></table>

<script type="text/html" id="ul-toolbar">
    <div style="display:flex;align-items:center;justify-content:center;position:relative;padding:10px 0px 15px;border-bottom:1px solid #f0f0f0;">
        <span style="position:absolute;left:0;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">用户日志</span>
    </div>
    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
        <div class="layui-btn-container">
            <button class="layui-btn layui-btn-primary layui-border-green" lay-event="refresh"><i class="ri-refresh-line"></i></button>
            <button id="ul-toolbar-del" class="layui-btn  layui-bg-red layui-btn-disabled" lay-event="del">删除</button>
            <button class="layui-btn layui-btn-warm" lay-event="clean">清理</button>
        </div>
        <form class="layui-form" lay-filter="ul-search-form" style="display: flex; align-items: center; gap: 6px; margin: 0; flex-wrap: wrap;">
            <div class="layui-input-inline layui-input-wrap" style="width:100px;margin:0;">
                <input type="text" name="uid" id="ul-search-uid" placeholder="用户编号" lay-affix="clear" class="layui-input" autocomplete="off">
            </div>
            <div class="layui-input-inline layui-input-wrap" style="width:120px;margin:0;">
                <select name="type" id="ul-search-type" lay-filter="ul-search-type">
                    <option value="">全部类型</option>
                    <?php foreach($logTypes as $t): ?>
                    <option value="<?= htmlspecialchars($t['type']) ?>"><?= htmlspecialchars($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="layui-input-inline layui-input-wrap" style="width:130px;margin:0;">
                <input type="text" name="keyword" id="ul-search-keyword" placeholder="搜索内容" lay-affix="clear" class="layui-input" autocomplete="off">
            </div>
            <div class="layui-input-inline layui-input-wrap" style="width:110px;margin:0;">
                <input type="text" name="ip" id="ul-search-ip" placeholder="IP地址" lay-affix="clear" class="layui-input" autocomplete="off">
            </div>
            <div class="layui-input-inline layui-input-wrap" style="width:120px;margin:0;">
                <input type="text" name="date_start" id="ul-date-start" placeholder="开始日期" class="layui-input" autocomplete="off">
            </div>
            <div class="layui-input-inline layui-input-wrap" style="width:120px;margin:0;">
                <input type="text" name="date_end" id="ul-date-end" placeholder="结束日期" class="layui-input" autocomplete="off">
            </div>
            <button type="button" class="layui-btn layui-btn-sm" id="ul-btn-search">搜索</button>
            <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" id="ul-btn-reset">重置</button>
        </form>
    </div>
</script>

<script type="text/html" id="ul-tpl-uid">
    <a href="javascript:;" class="ul-uid-link" data-uid="{{ d.uid }}">{{ d.uid }}</a>
</script>

<script type="text/html" id="ul-tpl-type">
    <span class="ul-type-badge ul-type-{{ d.type }}" data-type="{{ d.type }}">{{ d.type_name }}</span>
</script>

<script type="text/html" id="ul-tpl-amount">
    {{# if(d.amount > 0){ }}
    <span class="ul-amount-pos">+{{ d.amount }}</span>
    {{# } else if(d.amount < 0){ }}
    <span class="ul-amount-neg">{{ d.amount }}</span>
    {{# } else { }}
    <span style="color:#ccc;">-</span>
    {{# } }}
</script>

<script type="text/html" id="ul-tpl-user">
    <div>{{ d.user_display }}</div>
    {{# if(d.user_username){ }}
    <div style="color:#999;font-size:12px;">{{ d.user_username }}</div>
    {{# } }}
</script>

<script type="text/html" id="ul-tpl-operate">
    <a class="layui-btn layui-bg-red" lay-event="del">删除</a>
</script>

<script>
layui.use(['table', 'form', 'laydate'], function(){
    var table = layui.table;
    var form = layui.form;
    var laydate = layui.laydate;
    var $ = layui.$;
    var typeNames = <?= json_encode(User_Log_Model::$typeNames, JSON_UNESCAPED_UNICODE) ?>;

    // 读取 URL 参数（从用户管理齿轮菜单跳转过来时自动筛选）
    var urlParams = new URLSearchParams(window.location.search);
    var initUid = urlParams.get('filter_uid') || '';

    // 当前筛选状态
    var currentFilters = { uid: initUid, type: '', keyword: '', ip: '', date_start: '', date_end: '' };
    if(initUid){ $('#ul-search-uid').val(initUid); }

    // 日期选择器
    laydate.render({ elem: '#ul-date-start', type: 'date' });
    laydate.render({ elem: '#ul-date-end', type: 'date' });

    // 渲染表格
    table.render({
        elem: '#ul-table',
        id: 'ul-table',
        url: '?action=index',
        where: currentFilters,
        toolbar: '#ul-toolbar',
        page: true,
        limits: [10, 20, 30, 50, 100],
        defaultToolbar: [],
        autoSort: false,
        lineStyle: 'height: 50px;',
        cols: [[
            {type: 'checkbox', fixed: 'left'},
            {field: 'id', title: 'ID', width: 80, sort: true},
            {field: 'uid', title: '用户编号', width: 100, templet: '#ul-tpl-uid'},
            {field: 'type_name', title: '操作类型', width: 125, templet: '#ul-tpl-type'},
            {field: 'amount', title: '数量', width: 100, sort: true, templet: '#ul-tpl-amount'},
            {field: 'content', title: '内容', minWidth: 240},
            {field: 'ip', title: 'IP', width: 140},
            {field: 'create_time_text', title: '时间', width: 170, sort: true},
            {fixed: 'right', title: '操作', width: 100, templet: '#ul-tpl-operate'}
        ]]
    });

    // 排序
    table.on('sort(ul-table)', function(obj){
        var fieldMap = { create_time_text: 'create_time', type_name: 'type' };
        var field = fieldMap[obj.field] || obj.field;
        table.reload('ul-table', {
            initSort: obj,
            where: $.extend({}, currentFilters, { field: field, order: obj.type })
        });
    });

    // 工具栏
    table.on('toolbar(ul-table)', function(obj){
        if(obj.event === 'refresh'){
            table.reload('ul-table');
        }
        if(obj.event === 'del'){
            var checkData = table.checkStatus('ul-table').data;
            if(checkData.length === 0) return;
            layer.confirm('确定删除的 ' + checkData.length + ' 条日志？', function(idx){
                var ids = checkData.map(function(d){ return d.id; }).join(',');
                $.post('?action=del', { ids: ids, token: '<?= LoginAuth::genToken() ?>' }, function(res){
                    if(res.code === 0){
                        layer.close(idx);
                        table.reload('ul-table');
                        layer.msg('日志已删除');
                    } else {
                        layer.msg(res.msg || '删除失败');
                    }
                }, 'json');
            });
        }
        if(obj.event === 'clean'){
            var cleanHtml = '<div style="padding:20px 20px 10px;">'
                + '<div style="font-size:13px;color:#666;margin-bottom:6px;">清理历史日志，选择时间范围：</div>'
                + '<div style="font-size:12px;color:#f5222d;margin-bottom:14px;"><i class="ri-error-warning-line"></i> 清理后日志将被永久删除，不可恢复</div>'
                + '<div style="display:flex;gap:10px;" id="ul-clean-days-opts">'
                + '<label class="export-opt layui-btn layui-btn-sm layui-btn-primary" data-val="30" style="flex:1;min-width:0;padding:0 12px;">30 天前</label>'
                + '<label class="export-opt layui-btn layui-btn-sm layui-btn-primary" data-val="90" style="flex:1;min-width:0;padding:0 12px;">90 天前</label>'
                + '<label class="export-opt layui-btn layui-btn-sm layui-btn-checked" data-val="180" style="flex:1;min-width:0;padding:0 12px;">180 天前</label>'
                + '<label class="export-opt layui-btn layui-btn-sm layui-btn-primary" data-val="365" style="flex:1;min-width:0;padding:0 12px;">365 天前</label>'
                + '</div>'
                + '</div>';
            layer.open({
                title: '清理历史日志',
                skin: 'dc-layer-modern',
                area: ['460px', 'auto'],
                content: cleanHtml,
                btn: ['<i class="ri-delete-bin-line"></i> 确认清理', '取消'],
                yes: function(idx) {
                    var days = $('#ul-clean-days-opts .layui-btn-checked').data('val');
                    layer.close(idx);
                    var loadIdx = layer.load(2);
                    $.ajax({
                        url: '?action=clean',
                        type: 'POST',
                        dataType: 'json',
                        data: { days: days, token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e) {
                            layer.close(loadIdx);
                            if (e.code == 400) return layer.msg(e.msg);
                            layer.msg('已清理 ' + (e.data.count || 0) + ' 条历史日志');
                            table.reload('ul-table');
                        },
                        error: function(err) {
                            layer.close(loadIdx);
                            layer.msg('操作失败');
                        }
                    });
                },
                success: function(layero) {
                    layero.find('.export-opt').on('click', function(){
                        layero.find('.export-opt').removeClass('layui-btn-checked').addClass('layui-btn-primary');
                        $(this).removeClass('layui-btn-primary').addClass('layui-btn-checked');
                    });
                }
            });
        }
    });

    // 快捷筛选：点击用户编号
    $(document).on('click', '.ul-uid-link', function(e){
        e.preventDefault();
        var uid = $(this).attr('data-uid') || '';
        currentFilters.uid = uid;
        $('#ul-search-uid').val(uid);
        doSearch();
    });

    // 快捷筛选：点击操作类型
    $(document).on('click', '.ul-type-badge', function(){
        var type = $(this).attr('data-type') || '';
        currentFilters.type = type;
        $('#ul-search-type').val(type);
        form.render('select');
        doSearch();
    });

    // 行操作
    table.on('tool(ul-table)', function(obj){
        if(obj.event === 'del'){
            layer.confirm('确定删除该条日志？', function(idx){
                $.post('?action=del', { ids: obj.data.id, token: '<?= LoginAuth::genToken() ?>' }, function(res){
                    if(res.code === 0){
                        layer.close(idx);
                        obj.del();
                        layer.msg('日志已删除');
                    } else {
                        layer.msg(res.msg || '删除失败');
                    }
                }, 'json');
            });
        }
    });

    // 勾选状态 → 删除按钮
    table.on('checkbox(ul-table)', function(){
        var checked = table.checkStatus('ul-table').data.length > 0;
        var $btn = $('#ul-toolbar-del');
        if(checked){
            $btn.removeClass('layui-btn-disabled');
        } else {
            $btn.addClass('layui-btn-disabled');
        }
    });

    // 搜索按钮
    $('#ul-btn-search').on('click', function(){
        currentFilters.uid = $('#ul-search-uid').val().trim();
        currentFilters.type = $('#ul-search-type').val();
        currentFilters.keyword = $('#ul-search-keyword').val().trim();
        currentFilters.ip = $('#ul-search-ip').val().trim();
        currentFilters.date_start = $('#ul-date-start').val();
        currentFilters.date_end = $('#ul-date-end').val();
        doSearch();
    });

    // 重置按钮
    $('#ul-btn-reset').on('click', function(){
        currentFilters = { uid: '', type: '', keyword: '', ip: '', date_start: '', date_end: '' };
        $('#ul-search-uid').val('');
        $('#ul-search-type').val('');
        $('#ul-search-keyword').val('');
        $('#ul-search-ip').val('');
        $('#ul-date-start').val('');
        $('#ul-date-end').val('');
        form.render('select');
        doSearch();
    });

    // 执行搜索
    function doSearch(){
        table.reload('ul-table', {
            page: { curr: 1 },
            where: currentFilters
        });
        renderFilterTags();
    }

    // 渲染筛选标签
    function renderFilterTags(){
        var html = '';
        if(currentFilters.uid){
            html += '<span class="ul-filter-tag">用户编号: ' + currentFilters.uid + ' <i class="layui-icon layui-icon-close ul-tag-close" onclick="ulClearFilter(\'uid\')"></i></span>';
        }
        if(currentFilters.type){
            var tn = typeNames[currentFilters.type] || currentFilters.type;
            html += '<span class="ul-filter-tag">类型: ' + tn + ' <i class="layui-icon layui-icon-close ul-tag-close" onclick="ulClearFilter(\'type\')"></i></span>';
        }
        if(currentFilters.keyword){
            html += '<span class="ul-filter-tag">内容: ' + currentFilters.keyword + ' <i class="layui-icon layui-icon-close ul-tag-close" onclick="ulClearFilter(\'keyword\')"></i></span>';
        }
        if(currentFilters.ip){
            html += '<span class="ul-filter-tag">IP: ' + currentFilters.ip + ' <i class="layui-icon layui-icon-close ul-tag-close" onclick="ulClearFilter(\'ip\')"></i></span>';
        }
        if(currentFilters.date_start || currentFilters.date_end){
            html += '<span class="ul-filter-tag">日期: ' + (currentFilters.date_start||'*') + ' ~ ' + (currentFilters.date_end||'*') + ' <i class="layui-icon layui-icon-close ul-tag-close" onclick="ulClearFilter(\'date\')"></i></span>';
        }
        $('#ul-active-filters').html(html);
    }

    // 全局方法：清除单个筛选
    window.ulClearFilter = function(key){
        if(key === 'uid'){ currentFilters.uid = ''; $('#ul-search-uid').val(''); }
        if(key === 'type'){ currentFilters.type = ''; $('#ul-search-type').val(''); form.render('select'); }
        if(key === 'keyword'){ currentFilters.keyword = ''; $('#ul-search-keyword').val(''); }
        if(key === 'ip'){ currentFilters.ip = ''; $('#ul-search-ip').val(''); }
        if(key === 'date'){ currentFilters.date_start = ''; currentFilters.date_end = ''; $('#ul-date-start').val(''); $('#ul-date-end').val(''); }
        doSearch();
    };
});
</script>
