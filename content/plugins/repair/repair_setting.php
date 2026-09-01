<?php
defined('DC_ROOT') || exit('access denied!');

function plugin_setting_view() {
    $page_type = Input::getStrVar('type');

    if($page_type == 'admin'){
        ?>
        <link rel="stylesheet" href="<?= DC_URL ?>admin/views/assets/remixicon/remixicon.css">
        <style>
            .repair-container { padding: 0px; }
            .repair-section { margin-bottom: 30px; }
            .repair-section-title { 
                font-size: 16px; 
                font-weight: 600; 
                color: #333; 
                margin-bottom: 15px; 
                padding-bottom: 10px; 
                border-bottom: 1px solid #eee;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .repair-section-title i { color: #16baaa; font-size: 18px; }
            .repair-grid { 
                display: grid; 
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
                gap: 15px; 
            }
            .repair-card {
                background: #fff;
                border: 1px solid #e8e8e8;
                border-radius: 8px;
                padding: 20px;
                transition: all 0.3s;
            }
            .repair-card:hover {
                border-color: #16baaa;
                box-shadow: 0 4px 12px rgba(22, 186, 170, 0.15);
            }
            .repair-card-title {
                font-size: 15px;
                font-weight: 600;
                color: #333;
                margin-bottom: 8px;
                display: flex;
                align-items: center;
                gap: 6px;
            }
            .repair-card-title i { color: #16baaa; }
            .repair-card-desc {
                font-size: 13px;
                color: #999;
                line-height: 1.6;
                margin-bottom: 15px;
                min-height: 40px;
            }
            .repair-card .layui-btn { width: 100%; }
            .repair-stats {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 12px;
                padding: 20px 24px;
                color: #fff;
                margin-bottom: 25px;
            }
            .repair-stats-title {
                font-size: 14px;
                font-weight: 500;
                opacity: 0.95;
                margin-bottom: 16px;
                display: flex;
                align-items: center;
                gap: 6px;
            }
            .repair-stats-grid {
                display: grid;
                grid-template-columns: repeat(6, 1fr);
                gap: 10px;
            }
            @media (max-width: 900px) {
                .repair-stats-grid { grid-template-columns: repeat(3, 1fr); }
            }
            @media (max-width: 540px) {
                .repair-stats-grid { grid-template-columns: repeat(2, 1fr); }
            }
            .repair-stats-item {
                text-align: center;
                background: rgba(255, 255, 255, 0.18);
                border-radius: 8px;
                padding: 12px 8px;
                transition: background 0.2s;
                cursor: default;
            }
            .repair-stats-item:hover { background: rgba(255, 255, 255, 0.28); }
            .repair-stats-icon { font-size: 20px; opacity: 0.9; margin-bottom: 6px; line-height: 1; }
            .repair-stats-num { font-size: 22px; font-weight: 700; line-height: 1; }
            .repair-stats-label { font-size: 11px; opacity: 0.85; margin-top: 5px; }

            /* 深色模式适配 */
            [data-theme="dark"] .repair-section-title,
            .dark-mode .repair-section-title { color: #e0e0e0; border-bottom-color: #3a3a3a; }
            [data-theme="dark"] .repair-card,
            .dark-mode .repair-card { background: #2a2a2a; border-color: #3a3a3a; }
            [data-theme="dark"] .repair-card:hover,
            .dark-mode .repair-card:hover { border-color: #16baaa; box-shadow: 0 4px 12px rgba(22, 186, 170, 0.25); }
            [data-theme="dark"] .repair-card-title,
            .dark-mode .repair-card-title { color: #e0e0e0; }
            [data-theme="dark"] .repair-card-desc,
            .dark-mode .repair-card-desc { color: #888; }
        </style>

        <div class="layui-card" style="border-radius:8px;overflow:hidden;">
            <div class="layui-card-header" style="display:flex;align-items:center;justify-content:center;position:relative;height:auto;padding:12px 15px;">
                <span style="position:absolute;left:15px;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
                    <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
                    <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
                    <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
                </span>
                <span style="color:#667797;font-size:14px;font-weight:500;">系统工具箱</span>
            </div>
            <div class="layui-card-body" style="padding:20px;">
        <div class="repair-container">
            <div class="repair-stats">
                <div class="repair-stats-title"><i class="ri-bar-chart-2-line"></i> 系统概览</div>
                <div class="repair-stats-grid">
                    <div class="repair-stats-item">
                        <div class="repair-stats-icon"><i class="ri-calendar-check-line"></i></div>
                        <div class="repair-stats-num" id="stat-today">-</div>
                        <div class="repair-stats-label">今日订单</div>
                    </div>
                    <div class="repair-stats-item">
                        <div class="repair-stats-icon"><i class="ri-time-line"></i></div>
                        <div class="repair-stats-num" id="stat-pending">-</div>
                        <div class="repair-stats-label">待付款</div>
                    </div>
                    <div class="repair-stats-item">
                        <div class="repair-stats-icon"><i class="ri-file-list-3-line"></i></div>
                        <div class="repair-stats-num" id="stat-orders">-</div>
                        <div class="repair-stats-label">订单总数</div>
                    </div>
                    <div class="repair-stats-item">
                        <div class="repair-stats-icon"><i class="ri-group-line"></i></div>
                        <div class="repair-stats-num" id="stat-users">-</div>
                        <div class="repair-stats-label">用户总数</div>
                    </div>
                    <div class="repair-stats-item">
                        <div class="repair-stats-icon"><i class="ri-store-2-line"></i></div>
                        <div class="repair-stats-num" id="stat-goods">-</div>
                        <div class="repair-stats-label">商品总数</div>
                    </div>
                    <div class="repair-stats-item">
                        <div class="repair-stats-icon"><i class="ri-key-2-line"></i></div>
                        <div class="repair-stats-num" id="stat-kami">-</div>
                        <div class="repair-stats-label">卡密总数</div>
                    </div>
                </div>
            </div>

            <div class="repair-section">
                <div class="repair-section-title"><i class="ri-database-2-line"></i> 数据修复</div>
                <div class="repair-grid">
                    <div class="repair-card">
                        <div class="repair-card-title"><i class="ri-archive-line"></i> 商品库存修复</div>
                        <div class="repair-card-desc">根据实际卡密数量重新计算商品库存，修复库存不同步问题</div>
                        <button type="button" class="layui-btn layui-btn-normal" lay-on="repair-goods-stock">
                            <i class="ri-refresh-line"></i> 开始修复
                        </button>
                    </div>
                    <div class="repair-card">
                        <div class="repair-card-title"><i class="ri-file-list-3-line"></i> 批量库存修复</div>
                        <div class="repair-card-desc">一键修复所有卡密类商品的库存数据，适合大批量修复</div>
                        <button type="button" class="layui-btn layui-btn-normal" lay-on="repair-all-stock">
                            <i class="ri-restart-line"></i> 批量修复
                        </button>
                    </div>
                    <div class="repair-card">
                        <div class="repair-card-title"><i class="ri-shopping-cart-line"></i> 订单状态修复</div>
                        <div class="repair-card-desc">检查并修复异常订单状态，清理过期未支付订单</div>
                        <button type="button" class="layui-btn layui-btn-normal" lay-on="repair-order-status">
                            <i class="ri-refresh-line"></i> 开始修复
                        </button>
                    </div>
                    <div class="repair-card">
                        <div class="repair-card-title"><i class="ri-user-settings-line"></i> 用户数据修复</div>
                        <div class="repair-card-desc">重新计算用户消费金额、订单数量等统计数据</div>
                        <button type="button" class="layui-btn layui-btn-normal" lay-on="repair-user-stats">
                            <i class="ri-refresh-line"></i> 开始修复
                        </button>
                    </div>
                    <div class="repair-card">
                        <div class="repair-card-title"><i class="ri-file-warning-line"></i> 孤立订单数据清理</div>
                        <div class="repair-card-desc">检测并清理无主订单明细（order_list/order_required），避免数据膨胀与统计异常</div>
                        <button type="button" class="layui-btn layui-btn-normal" lay-on="orphan-order-scan">
                            <i class="ri-search-line"></i> 扫描并处理
                        </button>
                    </div>
                    <div class="repair-card">
                        <div class="repair-card-title"><i class="ri-alert-line"></i> 负库存一键修复</div>
                        <div class="repair-card-desc">扫描负库存商品并按实际卡密数量重算库存（仅卡密类商品）</div>
                        <button type="button" class="layui-btn layui-btn-danger" lay-on="fix-negative-stock">
                            <i class="ri-shield-wrench-line"></i> 开始修复
                        </button>
                    </div>
                    <div class="repair-card">
                        <div class="repair-card-title"><i class="ri-key-2-line"></i> 清理孤立卡密</div>
                        <div class="repair-card-desc">删除关联商品已不存在的卡密数据，释放无用数据占用</div>
                        <button type="button" class="layui-btn layui-btn-danger" lay-on="clean-orphan-kami">
                            <i class="ri-delete-back-2-line"></i> 清理卡密
                        </button>
                    </div>
                </div>
            </div>

            <div class="repair-section">
                <div class="repair-section-title"><i class="ri-settings-3-line"></i> 系统维护</div>
                <div class="repair-grid">
                    <div class="repair-card">
                        <div class="repair-card-title"><i class="ri-delete-bin-line"></i> 清理系统缓存</div>
                        <div class="repair-card-desc">清除系统缓存文件，解决数据不更新、显示异常等问题</div>
                        <button type="button" class="layui-btn layui-btn-normal" lay-on="clear-cache">
                            <i class="ri-brush-line"></i> 清理缓存
                        </button>
                    </div>
                    <div class="repair-card">
                        <div class="repair-card-title"><i class="ri-shield-flash-line"></i> 系统环境自检</div>
                        <div class="repair-card-desc">检测目录权限、关键扩展与运行环境，定位常见安装/运行问题（只读）</div>
                        <button type="button" class="layui-btn layui-btn-normal" lay-on="env-check">
                            <i class="ri-search-line"></i> 开始检测
                        </button>
                    </div>
                    <div class="repair-card">
                        <div class="repair-card-title"><i class="ri-timer-2-line"></i> 定时任务健康检查</div>
                        <div class="repair-card-desc">检测常用 cron 插件配置与最近运行情况（只读），便于排查任务不执行</div>
                        <button type="button" class="layui-btn layui-btn-normal" lay-on="cron-health">
                            <i class="ri-heart-pulse-line"></i> 开始检查
                        </button>
                    </div>
                    <div class="repair-card">
                        <div class="repair-card-title"><i class="ri-calendar-close-line"></i> 清理过期订单</div>
                        <div class="repair-card-desc">删除超过指定天数的未支付订单，释放占用的库存</div>
                        <button type="button" class="layui-btn layui-btn-danger" lay-on="clean-expired-orders">
                            <i class="ri-delete-bin-line"></i> 清理订单
                        </button>
                    </div>
                    <div class="repair-card">
                        <div class="repair-card-title"><i class="ri-shield-check-line"></i> 数据完整性检查</div>
                        <div class="repair-card-desc">检查数据库表结构和数据完整性，发现潜在问题</div>
                        <button type="button" class="layui-btn layui-btn-normal" lay-on="check-integrity">
                            <i class="ri-search-line"></i> 开始检查
                        </button>
                    </div>
                    <div class="repair-card">
                        <div class="repair-card-title"><i class="ri-database-line"></i> 优化数据库</div>
                        <div class="repair-card-desc">优化数据库表，清理碎片，提升查询性能</div>
                        <button type="button" class="layui-btn layui-btn-normal" lay-on="optimize-db">
                            <i class="ri-speed-line"></i> 开始优化
                        </button>
                    </div>
                </div>
            </div>

            <div class="repair-section">
                <div class="repair-section-title"><i class="ri-exchange-funds-line"></i> 支付回调诊断中心</div>
                <div id="callback-capture-url" style="background:#f0f9f8;border:1px solid #c8eae7;border-radius:6px;padding:12px 15px;margin-bottom:12px;font-size:13px;">
                    <i class="ri-links-line"></i> 回调捕获地址：<span id="callback-url-text" style="color:#16baaa;word-break:break-all;"></span>
                    <button type="button" class="layui-btn layui-btn-xs layui-btn-normal" onclick="copyCallbackUrl()" style="margin-left:8px;"><i class="ri-file-copy-line"></i> 复制</button>
                    <div style="color:#999;font-size:12px;margin-top:4px;">将此地址配置为支付网关的回调 URL，并在 <code>?gateway=</code> 后追加网关名（如 alipay、wxpay），系统将自动记录所有接收到的回调。</div>
                </div>
                <div style="margin-bottom:10px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <input type="text" id="cb-filter-gateway" class="layui-input" style="width:160px;height:32px;" placeholder="按网关筛选">
                    <button type="button" class="layui-btn layui-btn-sm layui-btn-normal" lay-on="cb-load-log"><i class="ri-search-line"></i> 查询日志</button>
                    <button type="button" class="layui-btn layui-btn-sm layui-btn-danger" lay-on="cb-clear-log"><i class="ri-delete-bin-line"></i> 清空日志</button>
                    <span style="color:#999;font-size:12px;" id="cb-total-info"></span>
                </div>
                <div id="cb-log-table" style="overflow-x:auto;">
                    <table class="layui-table" lay-skin="line" style="margin-bottom:0;">
                        <thead><tr>
                            <th style="width:55px">ID</th>
                            <th style="width:90px">网关</th>
                            <th>订单号</th>
                            <th style="width:80px">金额</th>
                            <th style="width:80px">状态</th>
                            <th style="width:110px">IP</th>
                            <th style="width:145px">时间</th>
                            <th style="width:70px">操作</th>
                        </tr></thead>
                        <tbody id="cb-log-tbody"><tr><td colspan="8" style="text-align:center;color:#999;padding:20px;"><i class="ri-information-line"></i> 点击「查询日志」加载数据</td></tr></tbody>
                    </table>
                </div>
                <div id="cb-pagination" style="margin-top:8px;text-align:right;"></div>
            </div>
        </div>
            </div>
        </div>

        <script>
        layui.use(function(){
            var $ = layui.$;
            var layer = layui.layer;
            var util = layui.util;

            let isMobile = window.innerWidth < 768;
            let area = isMobile ? ['98%', '400px'] : ['600px', '400px'];
            var isDemoSite = <?= Register::isDemoSite() ? 'true' : 'false' ?>;
            function demoCheck(tip) {
                if (isDemoSite) { layer.msg(tip || '演示站暂不支持此操作', {icon: 0}); return true; }
                return false;
            }

            // 加载统计数据
            $.get('/?plugin=repair&action=get_stats', function(res) {
                if (res.code == 0) {
                    var d = res.data;
                    $('#stat-today').text(d.today_orders || 0);
                    $('#stat-pending').text(d.pending_orders || 0);
                    $('#stat-orders').text(d.orders || 0);
                    $('#stat-users').text(d.users || 0);
                    $('#stat-goods').text(d.goods || 0);
                    $('#stat-kami').text(d.kami || 0);
                }
            });

            // 支付回调捕获地址
            var _captureBase = window.location.origin + '/?plugin=repair&action=pay_callback_capture&gateway=';
            $('#callback-url-text').text(_captureBase + 'alipay');

            window.copyCallbackUrl = function(){
                var url = _captureBase + ($('#cb-filter-gateway').val() || 'alipay');
                if(navigator.clipboard){
                    navigator.clipboard.writeText(url).then(function(){ layer.msg('已复制', {icon:1, time:1500}); });
                } else {
                    var tmp = $('<input>').val(url).appendTo('body').select();
                    document.execCommand('copy');
                    tmp.remove();
                    layer.msg('已复制', {icon:1, time:1500});
                }
            };

            var _cbPage = 1;
            window.loadCbLog = function(page){
                _cbPage = page || 1;
                var gw = $('#cb-filter-gateway').val();
                var loadIdx = layer.load(2);
                $.get('/?plugin=repair&action=pay_callback_list_ajax', {page: _cbPage, gateway: gw}, function(res){
                    layer.close(loadIdx);
                    if(res.code != 0){ layer.msg(res.msg, {icon:2}); return; }
                    var list = res.data.list || [], total = res.data.total || 0;
                    $('#cb-total-info').text('共 ' + total + ' 条记录');
                    var html = '';
                    if(list.length === 0){
                        html = '<tr><td colspan="8" style="text-align:center;color:#999;padding:20px;">暂无回调记录</td></tr>';
                    } else {
                        list.forEach(function(r){
                            html += '<tr><td>' + r.id + '</td><td>' + r.gateway + '</td><td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + (r.order_no || '-') + '</td><td>' + (r.amount || '-') + '</td><td>' + (r.status || '-') + '</td><td>' + r.ip + '</td><td>' + r.time_str + '</td>';
                            html += '<td><a href="javascript:;" class="cb-detail" data-id="' + r.id + '" style="color:#16baaa;margin-right:6px;" title="详情"><i class="ri-eye-line"></i></a><a href="javascript:;" class="cb-del" data-id="' + r.id + '" style="color:#dc2626;" title="删除"><i class="ri-delete-bin-line"></i></a></td></tr>';
                        });
                    }
                    $('#cb-log-tbody').html(html);
                    var pages = Math.ceil(total / res.data.limit);
                    var pgHtml = '';
                    for(var p = 1; p <= pages && p <= 10; p++){
                        pgHtml += '<button type="button" onclick="loadCbLog(' + p + ')" class="layui-btn layui-btn-xs ' + (p === _cbPage ? '' : 'layui-btn-primary') + '" style="margin:2px;">' + p + '</button>';
                    }
                    $('#cb-pagination').html(pgHtml);
                    $('.cb-detail').off('click').on('click', function(){
                        var id = $(this).data('id');
                        $.get('/?plugin=repair&action=pay_callback_detail_ajax', {id: id}, function(r){
                            if(r.code != 0){ layer.msg(r.msg, {icon:2}); return; }
                            var d = r.data;
                            var h = '<div style="padding:15px;font-size:13px;">';
                            h += '<div style="margin-bottom:6px;"><b>ID:</b> ' + d.id + ' &nbsp; <b>网关:</b> ' + d.gateway + '</div>';
                            h += '<div style="margin-bottom:6px;"><b>订单号:</b> ' + (d.order_no || '-') + ' &nbsp; <b>金额:</b> ' + (d.amount || '-') + ' &nbsp; <b>状态:</b> ' + (d.status || '-') + '</div>';
                            h += '<div style="margin-bottom:6px;"><b>IP:</b> ' + d.ip + ' &nbsp; <b>时间:</b> ' + d.time_str + '</div>';
                            h += '<div style="margin-top:10px;"><b>原始数据：</b><pre style="background:#f5f5f5;padding:10px;border-radius:4px;overflow:auto;max-height:220px;font-size:12px;word-break:break-all;white-space:pre-wrap;">' + $('<div>').text(d.raw_data || '').html() + '</pre></div>';
                            h += '</div>';
                            layer.open({type:1, title:'回调详情 #' + d.id, area:['620px','auto'], content:h});
                        });
                    });
                    $('.cb-del').off('click').on('click', function(){
                        if(demoCheck('演示站暂不支持删除操作')) return;
                        var id = $(this).data('id');
                        layer.confirm('确定删除此条回调日志？', {icon:3, title:'删除确认'}, function(idx){
                            layer.close(idx);
                            $.post('/?plugin=repair&action=pay_callback_delete_ajax', {id: id}, function(r){
                                if(r.code == 0){ layer.msg('已删除', {icon:1, time:1500}); loadCbLog(_cbPage); }
                                else layer.msg(r.msg, {icon:2});
                            });
                        });
                    });
                }).fail(function(){ layer.close(loadIdx); layer.msg('请求失败', {icon:2}); });
            };

            // 深色模式下预设弹窗背景色
            var isDark = document.documentElement.getAttribute('data-theme') === 'dark' || document.body.classList.contains('dark-mode');
            if(isDark) {
                // 动态添加样式，让弹窗一创建就是深色背景
                var style = document.createElement('style');
                style.textContent = '.layui-layer-iframe .layui-layer-content { background: #1a1a1a !important; }';
                document.head.appendChild(style);
            }

            // 事件
            util.on('lay-on', {
                'repair-goods-stock': function(){
                    if (demoCheck('演示站暂不支持库存修复操作')) return;
                    layer.open({
                        id: 'repair',
                        title: '修复商品库存',
                        type: 2,
                        area: area,
                        content: '/?plugin=repair&action=repair_goods_stock',
                        fixed: false,
                        maxmin: true,
                        shadeClose: true,
                        success: function(layero, index, that){
                            layer.iframeAuto(index);
                            that.offset();
                        }
                    });
                },
                'repair-all-stock': function(){
                    if (demoCheck('演示站暂不支持批量库存修复操作')) return;
                    layer.confirm('确定要批量修复所有卡密类商品的库存吗？', {icon: 3, title: '批量修复'}, function(index){
                        layer.close(index);
                        var loadIdx = layer.load(2);
                        $.post('/?plugin=repair&action=repair_all_stock_ajax', function(res){
                            layer.close(loadIdx);
                            if(res.code == 0){
                                layer.msg('修复完成，共修复 ' + res.data.count + ' 个商品', {icon: 1});
                            } else {
                                layer.msg(res.msg, {icon: 2});
                            }
                        }).fail(function(xhr){
                            layer.close(loadIdx);
                            layer.msg('请求失败', {icon: 2});
                        });
                    });
                },
                'repair-order-status': function(){
                    if (demoCheck('演示站暂不支持订单状态修复操作')) return;
                    layer.confirm('确定要修复订单状态吗？将清理超过24小时未支付的订单', {icon: 3, title: '订单修复'}, function(index){
                        layer.close(index);
                        var loadIdx = layer.load(2);
                        $.post('/?plugin=repair&action=repair_order_status_ajax', function(res){
                            layer.close(loadIdx);
                            if(res.code == 0){
                                layer.msg('修复完成，清理 ' + res.data.cleaned + ' 个过期订单', {icon: 1});
                            } else {
                                layer.msg(res.msg, {icon: 2});
                            }
                        }).fail(function(xhr){
                            layer.close(loadIdx);
                            layer.msg('请求失败', {icon: 2});
                        });
                    });
                },
                'repair-user-stats': function(){
                    if (demoCheck('演示站暂不支持用户数据修复操作')) return;
                    layer.confirm('确定要重新计算所有用户的统计数据吗？', {icon: 3, title: '用户数据修复'}, function(index){
                        layer.close(index);
                        var loadIdx = layer.load(2);
                        $.post('/?plugin=repair&action=repair_user_stats_ajax', function(res){
                            layer.close(loadIdx);
                            if(res.code == 0){
                                layer.msg('修复完成，更新 ' + res.data.count + ' 个用户', {icon: 1});
                            } else {
                                layer.msg(res.msg, {icon: 2});
                            }
                        }).fail(function(xhr){
                            layer.close(loadIdx);
                            layer.msg('请求失败', {icon: 2});
                        });
                    });
                },
                'orphan-order-scan': function(){
                    if (demoCheck('演示站暂不支持孤立订单数据处理')) return;
                    var loadIdx = layer.load(2);
                    $.get('/?plugin=repair&action=orphan_order_report_ajax', function(res){
                        layer.close(loadIdx);
                        if(res.code == 0){
                            var html = '<div style="padding:15px;line-height:2;">';
                            html += '<div><i class="ri-file-list-3-line"></i> 孤立订单明细：' + (res.data.orphan_order_list || 0) + '</div>';
                            html += '<div><i class="ri-file-list-2-line"></i> 孤立订单必填项：' + (res.data.orphan_order_required || 0) + '</div>';
                            html += '</div>';
                            var btns = (res.data.total > 0) ? ['清理这些数据', '关闭'] : ['关闭'];
                            layer.open({
                                type: 1,
                                title: '孤立订单数据扫描结果',
                                area: ['520px', 'auto'],
                                content: html,
                                btn: btns,
                                yes: function(index){
                                    if (res.data.total <= 0) { layer.close(index); return; }
                                    layer.close(index);
                                    layer.confirm('确认清理孤立订单数据吗？建议先备份数据库。', {icon: 3, title: '清理确认'}, function(i2){
                                        layer.close(i2);
                                        var load2 = layer.load(2);
                                        $.post('/?plugin=repair&action=orphan_order_clean_ajax', function(r2){
                                            layer.close(load2);
                                            if(r2.code == 0){
                                                layer.msg('清理完成，共删除 ' + (r2.data.deleted || 0) + ' 条数据', {icon: 1});
                                            } else {
                                                layer.msg(r2.msg, {icon: 2});
                                            }
                                        }).fail(function(){
                                            layer.close(load2);
                                            layer.msg('请求失败', {icon: 2});
                                        });
                                    });
                                }
                            });
                        } else {
                            layer.msg(res.msg, {icon: 2});
                        }
                    }).fail(function(){
                        layer.close(loadIdx);
                        layer.msg('请求失败', {icon: 2});
                    });
                },
                'fix-negative-stock': function(){
                    if (demoCheck('演示站暂不支持负库存修复')) return;
                    layer.confirm('确认扫描并修复负库存商品吗？仅修复卡密类商品库存（once）。', {icon: 3, title: '负库存修复'}, function(index){
                        layer.close(index);
                        var loadIdx = layer.load(2);
                        $.post('/?plugin=repair&action=fix_negative_stock_ajax', function(res){
                            layer.close(loadIdx);
                            if(res.code == 0){
                                layer.msg('修复完成，共修复 ' + (res.data.count || 0) + ' 个商品', {icon: 1});
                            } else {
                                layer.msg(res.msg, {icon: 2});
                            }
                        }).fail(function(){
                            layer.close(loadIdx);
                            layer.msg('请求失败', {icon: 2});
                        });
                    });
                },
                'env-check': function(){
                    var loadIdx = layer.load(2);
                    $.get('/?plugin=repair&action=env_check_ajax', function(res){
                        layer.close(loadIdx);
                        if(res.code == 0){
                            var html = '<div style="padding:15px;line-height:2;">';
                            (res.data || []).forEach(function(item){
                                var icon = item.status == 'ok'
                                    ? '<i class="ri-check-line" style="color:#16a34a"></i>'
                                    : '<i class="ri-close-line" style="color:#dc2626"></i>';
                                html += '<div>' + icon + ' ' + item.name + ': ' + item.message + '</div>';
                            });
                            html += '</div>';
                            layer.open({
                                type: 1,
                                title: '系统环境自检结果',
                                area: ['560px', 'auto'],
                                content: html
                            });
                        } else {
                            layer.msg(res.msg, {icon: 2});
                        }
                    }).fail(function(){
                        layer.close(loadIdx);
                        layer.msg('请求失败', {icon: 2});
                    });
                },
                'cron-health': function(){
                    var loadIdx = layer.load(2);
                    $.get('/?plugin=repair&action=cron_health_ajax', function(res){
                        layer.close(loadIdx);
                        if(res.code == 0){
                            var html = '<div style="padding:15px;line-height:2;">';
                            (res.data || []).forEach(function(item){
                                var icon = item.status == 'ok'
                                    ? '<i class="ri-check-line" style="color:#16a34a"></i>'
                                    : '<i class="ri-close-line" style="color:#dc2626"></i>';
                                html += '<div style="margin-bottom:10px;">' + icon + ' ' + item.name + '<br><span style="color:#666">' + item.message + '</span></div>';
                            });
                            html += '</div>';
                            layer.open({
                                type: 1,
                                title: '定时任务健康检查',
                                area: ['600px', 'auto'],
                                content: html
                            });
                        } else {
                            layer.msg(res.msg, {icon: 2});
                        }
                    }).fail(function(){
                        layer.close(loadIdx);
                        layer.msg('请求失败', {icon: 2});
                    });
                },
                'clear-cache': function(){
                    if (demoCheck('演示站暂不支持清理缓存操作')) return;
                    layer.confirm('确定要清理系统缓存吗？', {icon: 3, title: '清理缓存'}, function(index){
                        layer.close(index);
                        var loadIdx = layer.load(2);
                        $.post('/?plugin=repair&action=clear_cache_ajax', function(res){
                            layer.close(loadIdx);
                            if(res.code == 0){
                                layer.msg('缓存清理完成', {icon: 1});
                            } else {
                                layer.msg(res.msg, {icon: 2});
                            }
                        }).fail(function(xhr){
                            layer.close(loadIdx);
                            layer.msg('请求失败', {icon: 2});
                        });
                    });
                },
                'clean-expired-orders': function(){
                    if (demoCheck('演示站暂不支持清理订单操作')) return;
                    layer.prompt({
                        formType: 0,
                        value: '7',
                        title: '请输入要清理的天数（清理N天前的未支付订单）',
                    }, function(value, index){
                        layer.close(index);
                        var days = parseInt(value) || 7;
                        var loadIdx = layer.load(2);
                        $.post('/?plugin=repair&action=clean_expired_orders_ajax', {days: days}, function(res){
                            layer.close(loadIdx);
                            if(res.code == 0){
                                layer.msg('清理完成，删除 ' + res.data.count + ' 个过期订单', {icon: 1});
                            } else {
                                layer.msg(res.msg, {icon: 2});
                            }
                        }).fail(function(xhr){
                            layer.close(loadIdx);
                            layer.msg('请求失败', {icon: 2});
                        });
                    });
                },
                'check-integrity': function(){
                    var loadIdx = layer.load(2);
                    $.get('/?plugin=repair&action=check_integrity_ajax', function(res){
                        layer.close(loadIdx);
                        if(res.code == 0){
                            var html = '<div style="padding:15px;line-height:2;">';
                            res.data.forEach(function(item){
                                var icon = item.status == 'ok' ? '<i class="ri-check-line" style="color:#16a34a"></i>' : '<i class="ri-close-line" style="color:#dc2626"></i>';
                                html += '<div>' + icon + ' ' + item.name + ': ' + item.message + '</div>';
                            });
                            html += '</div>';
                            layer.open({
                                type: 1,
                                title: '数据完整性检查结果',
                                area: ['500px', 'auto'],
                                content: html
                            });
                        } else {
                            layer.msg(res.msg, {icon: 2});
                        }
                    }).fail(function(){
                        layer.close(loadIdx);
                        layer.msg('请求失败', {icon: 2});
                    });
                },
                'clean-orphan-kami': function(){
                    if (demoCheck('演示站暂不支持清理孤立卡密操作')) return;
                    layer.confirm('确定要清理所有孤立卡密吗？（关联商品已不存在的卡密将被删除）', {icon: 3, title: '清理孤立卡密'}, function(index){
                        layer.close(index);
                        var loadIdx = layer.load(2);
                        $.post('/?plugin=repair&action=clean_orphan_kami_ajax', function(res){
                            layer.close(loadIdx);
                            if(res.code == 0){
                                layer.msg('清理完成，删除 ' + res.data.count + ' 条孤立卡密', {icon: 1});
                            } else {
                                layer.msg(res.msg, {icon: 2});
                            }
                        }).fail(function(xhr){
                            layer.close(loadIdx);
                            layer.msg('请求失败', {icon: 2});
                        });
                    });
                },
                'cb-load-log': function(){ window.loadCbLog(1); },
                'cb-clear-log': function(){
                    if(demoCheck('演示站暂不支持清空操作')) return;
                    layer.confirm('确定清空所有回调日志？此操作不可恢复。', {icon:3, title:'清空确认'}, function(idx){
                        layer.close(idx);
                        $.post('/?plugin=repair&action=pay_callback_clear_ajax', function(r){
                            if(r.code == 0){ layer.msg('日志已清空', {icon:1, time:1500}); window.loadCbLog(1); }
                            else layer.msg(r.msg, {icon:2});
                        });
                    });
                },
                'optimize-db': function(){
                    if (demoCheck('演示站暂不支持数据库优化操作')) return;
                    layer.confirm('确定要优化数据库吗？此操作可能需要一些时间', {icon: 3, title: '优化数据库'}, function(index){
                        layer.close(index);
                        var loadIdx = layer.load(2);
                        $.post('/?plugin=repair&action=optimize_db_ajax', function(res){
                            layer.close(loadIdx);
                            if(res.code == 0){
                                layer.msg('数据库优化完成', {icon: 1});
                            } else {
                                layer.msg(res.msg, {icon: 2});
                            }
                        }).fail(function(xhr){
                            layer.close(loadIdx);
                            layer.msg('请求失败', {icon: 2});
                        });
                    });
                }
            });
        });
        $(function(){
            $("#menu-repair").addClass('active');
        });
        </script>
        <?php
    }

    if(empty($page_type)){
        ?>
        <form class="layui-form" id="form" method="post" action="">
            <div style="padding: 25px;" id="open-box">
                <blockquote class="layui-elem-quote">
                    <i class="ri-information-line"></i> 请启用插件后，刷新页面，在后台左侧菜单找到「系统工具箱」进行操作
                </blockquote>
                <div style="height: 80px;"></div>
            </div>
            <div style="width: 100%; height: 50px;"></div>
            <div id="form-btn">
                <div class="layui-input-block" style="margin: 0 auto;">
                    <button type="button" class="layui-btn" lay-submit lay-filter="close"><i class="ri-close-line"></i> 关闭窗口</button>
                </div>
            </div>
        </form>

        <style>
            html, body { overflow: hidden !important; }
        </style>

        <script>
        layui.use(['form'], function(){
            var $ = layui.$;
            var form = layui.form;
            form.on('submit(close)', function(data){
                parent.layer.close('edit');
                return false;
            });
        });
        var maxHeight = $(window.parent).innerHeight() * 0.75;
        $("#open-box").css({ "max-height": maxHeight + "px", "overflow-y": "auto" });
        </script>
        <?php
    }
}

function plugin_setting() {
    Output::ok();
}
