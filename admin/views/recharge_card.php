<?php defined('DC_ROOT') || exit('access denied!'); ?>
<style>
.rcard-stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 15px;
}
.rcard-stat-item { padding: 16px 18px; border-radius: 6px; background-image: linear-gradient(0deg, #fff, #f3f5f8);box-shadow: 8px 8px 20px 0 rgba(55, 99, 170, .1); border: 2px solid #fff;}
.rcard-stat-label {
    color: #666;
    font-size: 13px;
    margin-bottom: 8px;
}
.rcard-stat-value {
    color: #222;
    font-size: 24px;
    font-weight: 600;
    line-height: 1.2;
}
.rcard-badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;
}
.rcard-badge.unused {
    background: rgba(82, 196, 26, .12);
    color: #52c41a;
}
.rcard-badge.used {
    background: rgba(24, 144, 255, .12);
    color: #1890ff;
}
.rcard-user-empty {
    color: #999;
}
.rcard-batch {
    color: #666;
    font-size: 12px;
}
.rcard-filter-form {
    margin-left: auto;
}
.rcard-filter-form .layui-form-item {
    margin-bottom: 0;
}
.rcard-preset-group {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 0 0 16px;
}
.rcard-preset-btn {
    padding: 6px 12px;
    border-radius: 16px;
    border: 1px solid #d9ecff;
    background: #f4faff;
    color: #1677ff;
    cursor: pointer;
    transition: all .2s;
}
.rcard-preset-btn:hover {
    background: #1677ff;
    color: #fff;
    border-color: #1677ff;
}
.rcard-generate-box {
    padding: 18px 20px 4px;
}
.rcard-generate-note {
    color: #999;
    font-size: 12px;
    margin: -6px 0 14px;
}
.rcard-last-box {
    padding: 18px 20px;
}
.rcard-last-title {
    font-size: 15px;
    color: #333;
    margin-bottom: 10px;
    font-weight: 600;
}
.rcard-last-meta {
    font-size: 12px;
    color: #666;
    margin-bottom: 12px;
}
.rcard-last-text {
    width: 100%;
    min-height: 280px;
    border: 1px solid #e5e6eb;
    border-radius: 8px;
    padding: 12px;
    resize: vertical;
    font-family: Consolas, monospace;
    font-size: 13px;
    line-height: 1.7;
}
@media (max-width: 1200px) {
    .rcard-stats {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
@media (max-width: 768px) {
    .rcard-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .rcard-filter-form {
        width: 100%;
        margin-left: 0;
    }
}
</style>

<div class="layui-tabs order-tabs-wrapper" lay-options="{trigger: false}">
    <ul class="layui-tabs-header">
        <li class="layui-this rcard-tab" data-status=""><a>全部</a></li>
        <li class="rcard-tab" data-status="0"><a>未使用</a></li>
        <li class="rcard-tab" data-status="1"><a>已使用</a></li>
    </ul>
</div>

<div class="rcard-stats">
    <div class="rcard-stat-item">
        <div class="rcard-stat-label">全部卡密</div>
        <div class="rcard-stat-value" id="rcard-stat-total"><?= intval($stats['total_count'] ?? 0) ?></div>
    </div>
    <div class="rcard-stat-item">
        <div class="rcard-stat-label">未使用</div>
        <div class="rcard-stat-value" id="rcard-stat-unused"><?= intval($stats['unused_count'] ?? 0) ?></div>
    </div>
    <div class="rcard-stat-item">
        <div class="rcard-stat-label">已使用</div>
        <div class="rcard-stat-value" id="rcard-stat-used"><?= intval($stats['used_count'] ?? 0) ?></div>
    </div>
    <div class="rcard-stat-item">
        <div class="rcard-stat-label">累计面额</div>
        <div class="rcard-stat-value" id="rcard-stat-amount">¥<?= $stats['amount_total'] ?? '0.00' ?></div>
    </div>
</div>

<table class="layui-hide" id="rcard-table" lay-filter="rcard-table"></table>

<script type="text/html" id="rcard-toolbar">
    <div style="display:flex;align-items:center;justify-content:center;position:relative;padding:10px 0px 15px;border-bottom:1px solid #f0f0f0;">
        <span style="position:absolute;left:0;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">充值卡密</span>
    </div>
    <div style="display: flex; align-items: center; justify-content: flex-end; width: 100%;">
        <form class="layui-form rcard-filter-form" style="display: flex; align-items: center; gap: 6px; margin: 0; flex-wrap: wrap;">
            <div class="layui-input-inline layui-input-wrap" style="width:180px;margin:0;">
                <input type="text" value="" name="keyword" placeholder="卡密/名称/类型/批次/用户" lay-affix="clear" class="layui-input">
            </div>
            <div class="layui-input-inline layui-input-wrap" style="width:110px;margin:0;">
                <input type="text" value="" name="type" placeholder="卡密类型" lay-affix="clear" class="layui-input">
            </div>
            <div class="layui-input-inline layui-input-wrap" style="width:110px;margin:0;">
                <input type="text" value="" name="batch_no" placeholder="批次号" lay-affix="clear" class="layui-input">
            </div>
            <button class="layui-btn layui-btn-sm" lay-submit lay-filter="rcard-search">搜索</button>
            <button type="reset" class="layui-btn layui-btn-sm layui-btn-primary">重置</button>
        </form>
    </div>
    <div style="display:flex;align-items:center;flex-wrap:wrap;gap:8px;width:100%;padding-top:10px;border-top:1px solid #f0f0f0;margin-top:10px;">
        <button class="layui-btn layui-btn-sm layui-btn-normal" id="rcard-generate-btn"><i class="layui-icon layui-icon-add-circle"></i> 生成充值卡</button>
        <button class="layui-btn layui-btn-sm layui-btn-primary" id="rcard-copy-last-btn"><i class="layui-icon layui-icon-file-b"></i> 复制最后生成卡密</button>
        <button class="layui-btn layui-btn-sm layui-btn-primary" id="rcard-export-current-btn"><i class="layui-icon layui-icon-export"></i> 导出当前勾选</button>
        <button class="layui-btn layui-btn-sm layui-btn-primary" id="rcard-export-last-btn"><i class="layui-icon layui-icon-download-circle"></i> 导出最后批次</button>
        <button class="layui-btn layui-btn-sm" id="rcard-refresh-btn"><i class="layui-icon layui-icon-refresh"></i> 刷新</button>
        <button class="layui-btn layui-btn-sm layui-bg-red layui-btn-disabled" id="rcard-delete-selected-btn"><i class="layui-icon layui-icon-delete"></i> 删除</button>
        <span style="margin-left:auto;color:#999;font-size:12px;" id="rcard-last-tip">最后生成批次：暂无</span>
    </div>
</script>

<script type="text/html" id="rcard-status-tpl">
    {{# if(d.status == 1){ }}
    <span class="rcard-badge used">已使用</span>
    {{# } else { }}
    <span class="rcard-badge unused">未使用</span>
    {{# } }}
</script>

<script type="text/html" id="rcard-user-tpl">
    {{# if(d.user_id > 0){ }}
    <div>{{ d.user_nickname || '未设置' }}</div>
    <div style="color:#999;font-size:12px;">{{ d.user_email || d.user_tel || d.user_username || ('UID：' + d.user_id) }}</div>
    {{# } else { }}
    <span class="rcard-user-empty">未使用</span>
    {{# } }}
</script>

<script type="text/html" id="rcard-batch-tpl">
    <div>{{ d.batch_no }}</div>
    <div class="rcard-batch">{{ d.create_time_text }}</div>
</script>

<script type="text/html" id="rcard-operate-tpl">
    <div class="layui-clear-space">
        <a class="layui-btn layui-btn-sm" lay-event="copy">复制</a>
        {{# if(d.status != 1){ }}
            <a class="layui-btn layui-btn-sm layui-bg-red" lay-event="del">删除</a>
        {{# } }}
    </div>
</script>

<script type="text/html" id="rcard-export-tpl">
    <div class="rcard-generate-box">
        <form class="layui-form" id="rcard-export-form">
            <input type="hidden" name="export_mode" value="">
            <input type="hidden" name="export_ids" value="">
            <div class="layui-form-item">
                <label class="layui-form-label">导出状态</label>
                <div class="layui-input-block">
                    <input type="radio" name="export_status" value="" title="全部" checked>
                    <input type="radio" name="export_status" value="0" title="未使用">
                    <input type="radio" name="export_status" value="1" title="已使用">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">导出格式</label>
                <div class="layui-input-block">
                    <input type="radio" name="export_format" value="line" title="一行一个卡密" checked>
                    <input type="radio" name="export_format" value="table" title="表格格式（含详细信息）">
                </div>
            </div>
            <div class="layui-form-item" style="margin-bottom:0;text-align:center;">
                <button type="submit" class="layui-btn" lay-submit lay-filter="rcard-export-submit">确认导出</button>
            </div>
        </form>
    </div>
</script>

<script type="text/html" id="rcard-generate-tpl">
    <div class="rcard-generate-box">
        <div class="rcard-preset-group">
            <span class="rcard-preset-btn" data-type="通用卡" data-title="10元充值卡" data-amount="10" data-num="10">10元 × 10张</span>
            <span class="rcard-preset-btn" data-type="通用卡" data-title="30元充值卡" data-amount="30" data-num="20">30元 × 20张</span>
            <span class="rcard-preset-btn" data-type="活动卡" data-title="50元活动充值卡" data-amount="50" data-num="50">50元 × 50张</span>
            <span class="rcard-preset-btn" data-type="代理卡" data-title="100元代理充值卡" data-amount="100" data-num="100">100元 × 100张</span>
        </div>
        <div class="rcard-generate-note">快捷预设会自动填充表单，你也可以手动修改。</div>
        <form class="layui-form" id="rcard-generate-form">
            <div class="layui-form-item">
                <label class="layui-form-label">卡密类型</label>
                <div class="layui-input-block">
                    <input type="text" name="type" value="通用卡" required lay-verify="required" placeholder="例如：通用卡 / 活动卡 / 代理卡" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">充值卡名称</label>
                <div class="layui-input-block">
                    <input type="text" name="title" value="10元充值卡" required lay-verify="required" placeholder="请输入充值卡名称" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">充值卡面额</label>
                <div class="layui-input-block">
                    <input type="number" name="amount" value="10" min="0.01" step="0.01" required lay-verify="required" placeholder="请输入面额" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">生成数量</label>
                <div class="layui-input-block">
                    <input type="number" name="num" value="10" min="1" max="500" required lay-verify="required" placeholder="单次最多 500 张" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item" style="margin-bottom:0;text-align:center;">
                <button type="submit" class="layui-btn">确认生成</button>
            </div>
        </form>
    </div>
</script>

<script>
layui.use(['table', 'form'], function(){
    var table = layui.table;
    var form = layui.form;
    var $ = layui.$;
    var layer = layui.layer;
    var currentStatus = '';
    var lastGeneratedData = { batch_no: '', count: 0, cards: [], copy_text: '', create_time_text: '' };

    function getFilters(){
        return {
            keyword: $('input[name="keyword"]').val(),
            type: $('input[name="type"]').val(),
            batch_no: $('input[name="batch_no"]').val(),
            status: currentStatus
        };
    }

    function updateStats(stats){
        if(!stats) return;
        $('#rcard-stat-total').text(stats.total_count || 0);
        $('#rcard-stat-unused').text(stats.unused_count || 0);
        $('#rcard-stat-used').text(stats.used_count || 0);
        $('#rcard-stat-amount').text('¥' + (stats.amount_total || '0.00'));
    }

    function updateLastTip(){
        if(!lastGeneratedData.batch_no){
            $('#rcard-last-tip').text('最后生成批次：暂无');
            return;
        }
        $('#rcard-last-tip').text('最后生成批次：' + lastGeneratedData.batch_no + '，共 ' + lastGeneratedData.count + ' 张，生成时间 ' + (lastGeneratedData.create_time_text || '刚刚'));
    }

    function fetchLastBatch(){
        $.get('?action=last_batch', function(res){
            if(res.code === 0){
                lastGeneratedData = res.data || lastGeneratedData;
                updateLastTip();
            }
        }, 'json');
    }

    function copyText(text, successMsg){
        if(!text){
            return layer.msg('没有可复制的内容');
        }
        if(navigator.clipboard && window.isSecureContext){
            navigator.clipboard.writeText(text).then(function(){
                layer.msg(successMsg || '复制成功');
            }).catch(function(){
                fallbackCopy(text, successMsg);
            });
            return;
        }
        fallbackCopy(text, successMsg);
    }

    function fallbackCopy(text, successMsg){
        var $temp = $('<textarea>').css({ position: 'fixed', top: '-9999px', left: '-9999px' }).val(text).appendTo('body');
        $temp[0].select();
        document.execCommand('copy');
        $temp.remove();
        layer.msg(successMsg || '复制成功');
    }

    function openExportDialog(mode, ids){
        layer.open({
            type: 1,
            title: mode === 'last' ? '导出最后批次' : '导出当前勾选',
            area: [window.innerWidth < 768 ? '98%' : '480px', 'auto'],
            skin: 'dc-layer-modern',
            shadeClose: true,
            content: $('#rcard-export-tpl').html(),
            success: function(layero, index){
                $(layero).find('input[name="export_mode"]').val(mode);
                $(layero).find('input[name="export_ids"]').val(ids || '');
                form.render('radio');
                form.on('submit(rcard-export-submit)', function(formData){
                    var params = [
                        'action=export',
                        'mode=' + encodeURIComponent(mode),
                        'token=<?= LoginAuth::genToken() ?>',
                        'format=' + encodeURIComponent(formData.field.export_format || 'line'),
                        'export_status=' + encodeURIComponent(formData.field.export_status || '')
                    ];
                    if(mode === 'selected' && ids){
                        params.push('ids=' + encodeURIComponent(ids));
                    }
                    window.open('recharge_card.php?' + params.join('&'));
                    layer.close(index);
                    return false;
                });
            }
        });
    }

    function refreshTable(resetPage){
        var options = { where: getFilters() };
        if(resetPage){
            options.page = { curr: 1 };
        }
        table.reload('rcard-table', options);
    }

    window.table = table.render({
        elem: '#rcard-table',
        id: 'rcard-table',
        url: '?action=index',
        toolbar: '#rcard-toolbar',
        limits: [10, 20, 30, 50, 100],
        lineStyle: 'height: 62px;',
        page: true,
        defaultToolbar: [],
        cols: [[
            {type: 'checkbox', fixed: 'left'},
            {field: 'id', title: 'ID', width: 70, sort: true},
            {field: 'card_key', title: '卡密', minWidth: 195},
            {field: 'title', title: '充值卡名称', minWidth: 150},
            {field: 'type', title: '卡密类型', width: 110},
            {field: 'amount_text', title: '面额', width: 90, templet: function(d){ return '¥' + d.amount_text; }},
            {field: 'status_text', title: '状态', width: 90, templet: '#rcard-status-tpl'},
            {field: 'batch_no', title: '批次', width: 144, templet: '#rcard-batch-tpl'},
            {field: 'user_display', title: '使用用户', minWidth: 130, templet: '#rcard-user-tpl'},
            {field: 'use_time_text', title: '使用时间', width: 165},
            {fixed: 'right', title: '操作', templet: '#rcard-operate-tpl', width: 150}
        ]],
        where: getFilters(),
        done: function(){
            updateLastTip();
            bindToolbarButtons();
        }
    });

    form.on('submit(rcard-search)', function(){
        refreshTable(true);
        return false;
    });

    table.on('checkbox(rcard-table)', function(){
        var selected = table.checkStatus('rcard-table').data || [];
        $('#rcard-delete-selected-btn').toggleClass('layui-btn-disabled', selected.length === 0);
    });

    table.on('tool(rcard-table)', function(obj){
        var data = obj.data;
        if(obj.event === 'copy'){
            copyText(data.card_key, '卡密已复制');
            return;
        }
        if(obj.event === 'del'){
            layer.confirm('确定删除这张充值卡吗？', { btn: ['确认', '取消'], icon: 3, title: '温馨提示' }, function(index){
                layer.close(index);
                $.post('?action=del', { ids: data.id, token: '<?= LoginAuth::genToken() ?>' }, function(res){
                    if(res.code === 1) return layer.msg(res.msg);
                    layer.msg('充值卡已删除');
                    updateStats(res.data || {});
                    refreshTable(false);
                }, 'json');
            });
        }
    });

    $('.rcard-tab').on('click', function(){
        $('.rcard-tab').removeClass('layui-this');
        $(this).addClass('layui-this');
        currentStatus = $(this).data('status').toString();
        refreshTable(true);
    });

    function bindToolbarButtons(){
        $('#rcard-refresh-btn').off('click').on('click', function(){
            fetchLastBatch();
            refreshTable(false);
        });
        $('#rcard-copy-last-btn').off('click').on('click', function(){
            if(!lastGeneratedData.copy_text){
                return layer.msg('暂无最后生成卡密');
            }
            copyText(lastGeneratedData.copy_text, '最后生成卡密已复制');
        });
        $('#rcard-export-current-btn').off('click').on('click', function(){
            var selected = table.checkStatus('rcard-table').data || [];
            if(selected.length === 0){
                return layer.msg('请先勾选要导出的卡密');
            }
            var ids = $.map(selected, function(item){ return item.id; }).join(',');
            openExportDialog('selected', ids);
        });
        $('#rcard-export-last-btn').off('click').on('click', function(){
            if(!lastGeneratedData.batch_no){
                return layer.msg('暂无最后生成批次');
            }
            openExportDialog('last', '');
        });
        $('#rcard-delete-selected-btn').off('click').on('click', function(){
            var selected = table.checkStatus('rcard-table').data || [];
            if(selected.length === 0){
                return;
            }
            var ids = $.map(selected, function(item){ return item.id; }).join(',');
            layer.confirm('确定删除的充值卡吗？已使用卡密不会被删除。', { btn: ['确认', '取消'], icon: 3, title: '温馨提示' }, function(index){
                layer.close(index);
                $.post('?action=del', { ids: ids, token: '<?= LoginAuth::genToken() ?>' }, function(res){
                    if(res.code === 1) return layer.msg(res.msg);
                    layer.msg('充值卡已删除');
                    updateStats(res.data || {});
                    refreshTable(false);
                }, 'json');
            });
        });
        $('#rcard-generate-btn').off('click').on('click', function(){
        var isMobile = window.innerWidth < 768;
        var area = isMobile ? ['98%', '92%'] : ['620px', 'auto'];
        layer.open({
            type: 1,
            title: '生成充值卡',
            area: area,
            skin: 'dc-layer-modern',
            shadeClose: true,
            content: $('#rcard-generate-tpl').html(),
            success: function(layero, index){
                form.render();
                $(layero).on('click', '.rcard-preset-btn', function(){
                    var $btn = $(this);
                    $(layero).find('input[name="type"]').val($btn.data('type'));
                    $(layero).find('input[name="title"]').val($btn.data('title'));
                    $(layero).find('input[name="amount"]').val($btn.data('amount'));
                    $(layero).find('input[name="num"]').val($btn.data('num'));
                });
                $(layero).on('submit', '#rcard-generate-form', function(e){
                    e.preventDefault();
                    var data = $(this).serializeArray();
                    data.push({ name: 'token', value: '<?= LoginAuth::genToken() ?>' });
                    $.ajax({
                        url: '?action=generate',
                        type: 'POST',
                        dataType: 'json',
                        data: $.param(data),
                        success: function(res){
                            if(res.code === 1) return layer.msg(res.msg);
                            lastGeneratedData = res.data.batch || lastGeneratedData;
                            updateLastTip();
                            updateStats(res.data.stats || {});
                            layer.close(index);
                            refreshTable(true);
                            showLastBatchDialog();
                        },
                        error: function(err){
                            layer.msg(err.responseJSON ? err.responseJSON.msg : '生成失败');
                        }
                    });
                });
            }
        });
    });
    } // end bindToolbarButtons

    function showLastBatchDialog(){
        if(!lastGeneratedData.copy_text){
            return;
        }
        var html = '<div class="rcard-last-box">'
            + '<div class="rcard-last-title">已生成 ' + lastGeneratedData.count + ' 张充值卡</div>'
            + '<div class="rcard-last-meta">批次：' + lastGeneratedData.batch_no + '　生成时间：' + (lastGeneratedData.create_time_text || '刚刚') + '</div>'
            + '<textarea class="rcard-last-text" id="rcard-last-textarea" readonly></textarea>'
            + '<div style="text-align:center;margin-top:12px;">'
            + '<button type="button" class="layui-btn" id="rcard-copy-result-btn">复制这批卡密</button>'
            + '</div>'
            + '</div>';
        layer.open({
            type: 1,
            title: '最后生成卡密',
            area: [window.innerWidth < 768 ? '98%' : '720px', window.innerWidth < 768 ? '92%' : '560px'],
            skin: 'dc-layer-modern',
            shadeClose: true,
            content: html,
            success: function(layero){
                $(layero).find('#rcard-last-textarea').val(lastGeneratedData.copy_text);
                $(layero).on('click', '#rcard-copy-result-btn', function(){
                    copyText(lastGeneratedData.copy_text, '这批卡密已复制');
                });
            }
        });
    }

    fetchLastBatch();
});
</script>
