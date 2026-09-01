<?php defined('DC_ROOT') || exit('access denied!'); ?>

<!-- 检查是否有开启快捷入口的插件 -->
<?php 
$adminShortcuts = Option::get('plugin_show_in_admin');
$hasShortcuts = false;
if ($adminShortcuts) {
    $shortcuts = json_decode($adminShortcuts, true);
    $hasShortcuts = is_array($shortcuts) && count($shortcuts) > 0;
}
?>


</div>

</div>


<a class="scroll-to-top rounded" href="#page-top">
    <i class="icofont-rounded-up"></i>
</a>



</div>
</div>
<?php doAction('adm_footer') ?>

<?php if ($hasShortcuts): ?>
<!-- 插件快捷入口悬浮按钮 -->
<style>
.plugin-fab { position: fixed; right: 20px; top: 50%; transform: translateY(-50%); z-index: 9999; display: flex; flex-direction: column; align-items: center; gap: 10px; }
.plugin-fab-btn { width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(0deg, #f5f5f5 0%, #f3f5f8 100%); color: #4c7d71; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: 8px 8px 20px 0 rgba(55,99,170,.1); transition: all 0.3s; border: 2px solid #fff;}
.plugin-fab-btn:hover { transform: scale(1.1); box-shadow: 0 6px 20px rgb(28 35 67 / 50%)}
.plugin-fab-panel { display: none; position: fixed; right: 80px; top: 50%; transform: translateY(-50%); width: 300px; max-height: 420px; background: #fff; border-radius: 12px; box-shadow: 0 20px 50px rgba(0,0,0,0.18), 0 1px 0 rgba(0,0,0,0.04); overflow: hidden; z-index: 9998; border: 1px solid rgba(0,0,0,.06); }
.plugin-fab-panel.show { display: block; }
.plugin-fab-panel-header { position: relative; height: 40px; padding: 0 14px; background: linear-gradient(to bottom, #f7f7f9, #eef0f3); border-bottom: 1px solid rgba(0,0,0,.08); display: flex; align-items: center; justify-content: space-between; user-select: none; }
.plugin-fab-traffic { display: flex; align-items: center; gap: 7px; }
.plugin-fab-traffic span { width: 12px; height: 12px; border-radius: 50%; box-shadow: inset 0 0 0 .5px rgba(0,0,0,.15); cursor: pointer; transition: filter .2s; }
.plugin-fab-traffic span.tl-r { background: #ff5f57; }
.plugin-fab-traffic span.tl-y { background: #febc2e; }
.plugin-fab-traffic span.tl-g { background: #28c840; }
.plugin-fab-traffic:hover span { filter: brightness(.95); }
.plugin-fab-panel-title { position: absolute; left: 0; right: 0; text-align: center; font-size: 13px; font-weight: 600; color: #3c3c43; letter-spacing: .2px; pointer-events: none; }
.plugin-fab-all-btn { padding: 4px 10px; border-radius: 6px; background: transparent; border: none; color: #6e6e73; cursor: pointer; display: inline-flex; align-items: center; gap: 3px; font-size: 12px; transition: all .2s; flex-shrink: 0; text-decoration: none; position: relative; z-index: 1; }
.plugin-fab-all-btn:hover { background: rgba(0,0,0,.06); color: #1d1d1f; }
.plugin-fab-panel-body { padding: 8px; max-height: 320px; overflow-y: auto; }
.plugin-fab-item { display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: 8px; cursor: pointer; transition: all 0.2s; text-decoration: none; color: #333; }
.plugin-fab-item:hover { background: #f5f5f5; }
.plugin-fab-item img { width: 36px; height: 36px; border-radius: 8px; object-fit: cover; flex-shrink: 0; }
.plugin-fab-item .fab-icon { width: 36px; height: 36px; border-radius: 8px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 18px; flex-shrink: 0; }
.plugin-fab-item-info { flex: 1; min-width: 0; }
.plugin-fab-item-name { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.plugin-fab-item-status { font-size: 11px; color: #999; }
.plugin-fab-empty { padding: 30px; text-align: center; color: #999; font-size: 13px; }
.plugin-fab-empty i { font-size: 36px; display: block; margin-bottom: 10px; color: #ddd; }
html[data-theme="dark"] .plugin-fab-panel { background: #1e1e1e; box-shadow: 0 20px 50px rgba(0,0,0,0.5); border-color: rgba(255,255,255,.08); }
html[data-theme="dark"] .plugin-fab-panel-header { background: linear-gradient(to bottom, #2a2a2c, #242426); border-bottom-color: rgba(255,255,255,.08); }
html[data-theme="dark"] .plugin-fab-panel-title { color: #e5e5e7; }
html[data-theme="dark"] .plugin-fab-all-btn { color: #b0b0b5; }
html[data-theme="dark"] .plugin-fab-all-btn:hover { background: rgba(255,255,255,.08); color: #fff; }
html[data-theme="dark"] .plugin-fab-item { color: #e0e0e0; }
html[data-theme="dark"] .plugin-fab-item:hover { background: #2a2a2a; }
html[data-theme="dark"] .plugin-fab-item-status { color: #888; }
html[data-theme="dark"] .plugin-fab-empty { color: #666; }
html[data-theme="dark"] .plugin-fab-empty i { color: #444; }
</style>

<div class="plugin-fab" id="plugin-fab" style="display: block;">
    <button class="plugin-fab-btn" id="plugin-fab-btn" title="插件快捷入口">
        <i class="ri-plug-line"></i>
    </button>
</div>
<div class="plugin-fab-panel" id="plugin-fab-panel">
    <div class="plugin-fab-panel-header">
        <div class="plugin-fab-traffic" id="plugin-fab-traffic" title="关闭">
            <span class="tl-r"></span>
            <span class="tl-y"></span>
            <span class="tl-g"></span>
        </div>
        <div class="plugin-fab-panel-title">插件快捷入口</div>
        <a href="./plugin.php" class="plugin-fab-all-btn" title="插件管理">管理<i class="ri-arrow-right-s-line"></i></a>
    </div>
    <div class="plugin-fab-panel-body" id="plugin-fab-body">
        <div class="plugin-fab-empty"><i class="ri-loader-4-line"></i>加载中...</div>
    </div>
</div>
<script>
(function(){
    var fabBtn = document.getElementById('plugin-fab-btn');
    var fabPanel = document.getElementById('plugin-fab-panel');
    var fabBody = document.getElementById('plugin-fab-body');
    var loaded = false;

    if (!fabBtn) return;

    fabBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        fabPanel.classList.toggle('show');
        if (!loaded) {
            loaded = true;
            loadShortcuts();
        }
    });

    document.addEventListener('click', function(e) {
        if (!fabPanel.contains(e.target) && e.target !== fabBtn && !fabBtn.contains(e.target)) {
            fabPanel.classList.remove('show');
        }
    });

    // 红灯点击关闭面板
    var trafficRed = document.querySelector('#plugin-fab-traffic .tl-r');
    if (trafficRed) {
        trafficRed.addEventListener('click', function(e){
            e.stopPropagation();
            fabPanel.classList.remove('show');
        });
    }

    function loadShortcuts(callback) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', './plugin.php?action=admin_shortcuts&t=' + Date.now(), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    var res = JSON.parse(xhr.responseText);
                    var data = res.data || [];
                    if (data.length === 0) {
                        // 没有快捷入口，隐藏悬浮图标
                        document.getElementById('plugin-fab').style.display = 'none';
                        fabBody.innerHTML = '<div class="plugin-fab-empty"><i class="ri-puzzle-line"></i>暂无快捷入口<br><small>在插件管理中点击「后台」按钮添加</small></div>';
                        if (callback) callback();
                        return;
                    }
                    // 有快捷入口，显示悬浮图标
                    document.getElementById('plugin-fab').style.display = 'flex';
                    var html = '';
                    data.forEach(function(p) {
                        var hasPreview = p.preview && p.preview.indexOf('plugin-icon.png') === -1;
                        var iconHtml = hasPreview 
                            ? '<img src="' + p.preview + '" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\';"><div class="fab-icon" style="display:none;"><i class="ri-puzzle-line"></i></div>'
                            : '<div class="fab-icon"><i class="ri-puzzle-line"></i></div>';
                        var statusText = p.active == 1 ? '<span style="color:#16a34a;">运行中</span>' : '<span style="color:#999;">已关闭</span>';
                        html += '<div class="plugin-fab-item" data-plugin="' + p.Plugin + '" data-ui="' + (p.Ui || '') + '" data-name="' + (p.Name || '').replace(/"/g, '&quot;') + '">';
                        html += iconHtml;
                        html += '<div class="plugin-fab-item-info"><div class="plugin-fab-item-name">' + (p.Name || p.Plugin) + '</div><div class="plugin-fab-item-status">' + statusText + '</div></div>';
                        html += '<i class="ri-arrow-right-s-line" style="color:#ccc;"></i>';
                        html += '</div>';
                    });
                    fabBody.innerHTML = html;
                    if (callback) callback();
                } catch(e) {
                    fabBody.innerHTML = '<div class="plugin-fab-empty"><i class="ri-error-warning-line"></i>加载失败</div>';
                    if (callback) callback();
                }
            } else {
                if (callback) callback();
            }
        };
        xhr.onerror = function() {
            fabBody.innerHTML = '<div class="plugin-fab-empty"><i class="ri-error-warning-line"></i>加载失败</div>';
            if (callback) callback();
        };
        xhr.send();
    }
    
    // 暴露刷新函数到全局作用域
    window.refreshPluginFab = loadShortcuts;

    document.addEventListener('click', function(e) {
        var item = e.target.closest('.plugin-fab-item');
        if (!item) return;
        var pluginSlug = item.getAttribute('data-plugin');
        var ui = item.getAttribute('data-ui');
        var name = item.getAttribute('data-name');
        fabPanel.classList.remove('show');
        if (ui === 'Layui') {
            var isMobile = window.innerWidth < 1200;
            var area = isMobile ? ['98%', '85%'] : ['1000px', '80%'];
            layer.open({
                id: 'edit',
                title: name || pluginSlug,
                type: 2,
                area: area,
                skin: 'dc-layer-modern',
                content: 'plugin.php?action=setting_page&plugin=' + pluginSlug,
                fixed: false,
                maxmin: true,
                shadeClose: true
            });
        } else {
            location.href = './plugin.php?plugin=' + pluginSlug;
        }
    });
})();
</script>
<?php endif; ?>

<style>
    /* ==========================================================================
       EM Modal Pro - 自定义弹窗样式系统
       ========================================================================== */
 
    /* 覆盖 Layui 默认弹窗容器 */
    .em-modal-skin {
        border-radius: 20px !important;
        background: transparent !important;
        box-shadow: none !important;
    }
 
    .em-modal-skin .layui-layer-content {
        overflow: visible !important;
        background: transparent !important;
        border-radius: 20px;
    }
 
    /* 核心容器 */
    .em-modal-box {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        overflow: hidden;
        position: relative;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
 
    /* 顶部装饰条 (可选) */
    .em-modal-box::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(90deg, #4C7D71, #6BA596);
    }
 
    /* 自定义关闭按钮 */
    .em-modal-close-btn {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #F3F4F6;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 10;
        color: #9CA3AF;
    }
    .em-modal-close-btn:hover {
        background: #E5E7EB;
        color: #4B5563;
        transform: rotate(90deg);
    }
    .em-modal-close-btn svg {
        width: 18px;
        height: 18px;
        fill: currentColor;
    }
 
    /* 头部区域 */
    .em-modal-header {
        padding: 40px 32px 24px;
        text-align: center;
        background: #fff;
    }
 
    .em-modal-icon-wrapper {
        width: 64px;
        height: 64px;
        background: #EDF2F1;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        color: #4C7D71;
    }
    .em-modal-icon-wrapper i {
        font-size: 32px;
    }
 
    .em-modal-title {
        font-size: 24px;
        font-weight: 800;
        color: #111827;
        margin-bottom: 8px;
        letter-spacing: -0.5px;
    }
 
    .em-modal-desc {
        font-size: 14px;
        color: #6B7280;
        line-height: 1.5;
        max-width: 80%;
        margin: 0 auto;
    }
 
    /* 内容区域 */
    .em-modal-body {
        padding: 0 32px 40px;
        background: #fff;
    }
 
    .em-modal-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
 
    .em-modal-item {
        display: flex;
        align-items: center;
        padding: 16px 20px;
        background: #F9FAFB;
        border: 1px solid #F3F4F6;
        border-radius: 12px;
        transition: all 0.2s;
        text-decoration: none;
        position: relative;
    }
 
    .em-modal-item:hover {
        background: #fff;
        border-color: #4C7D71;
        box-shadow: 0 8px 20px rgba(76, 125, 113, 0.1);
        transform: translateY(-2px);
    }
 
    .em-item-icon {
        width: 40px;
        height: 40px;
        background: #fff;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4C7D71;
        margin-right: 16px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        flex-shrink: 0;
    }
 
    .em-item-content {
        flex: 1;
        min-width: 0;
    }
 
    .em-item-title {
        font-size: 15px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 2px;
        display: block;
    }
 
    .em-item-sub {
        font-size: 12px;
        color: #9CA3AF;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-family: monospace;
    }
 
    .em-item-action {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #D1D5DB;
        transition: all 0.2s;
    }
 
    .em-modal-item:hover .em-item-action {
        color: #4C7D71;
        transform: translateX(4px);
    }
 
    /* 移动端适配 */
    @media (max-width: 640px) {
        .em-modal-header { padding: 32px 24px 20px; }
        .em-modal-body { padding: 0 24px 32px; }
        .em-modal-title { font-size: 20px; }
        .em-modal-item { padding: 14px; }
    }
</style>
 
<script>
    $('.get-em-buy-info').click(function(){
        var loadIndex = layer.load(2);
        $.ajax({
            url: "./index.php?action=get_em_buy_info",
            type: "GET",
            dataType: "json",
            success: function(e){
                if(e.code == 200){
                    var d = e.data || {};
                    var qq = d.service_qq || '';
                    var list = Array.isArray(d.buy_url) ? d.buy_url : [];
                    var clean = function(s){ return String(s || '').replace(/[`'"\s]/g,'').trim(); };
                    var linksHtml = '';
                    list.forEach(function(it){
                        var name = it.name || '官方授权渠道';
                        var url = clean(it.url);
                        if(url){
                            linksHtml += `
                                <a href="${url}" target="_blank" rel="noopener" class="em-modal-item">
                                    <div class="em-item-icon"><i class="ri-shopping-cart-line"></i></div>
                                    <div class="em-item-content">
                                        <span class="em-item-title">${name}</span>
                                        <span class="em-item-sub">${url}</span>
                                    </div>
                                    <div class="em-item-action"><i class="ri-arrow-right-s-line"></i></div>
                                </a>
                            `;
                        }
                    });
                    
                    var isMobile = window.innerWidth < 640;
                    var area = isMobile ? ['90%', 'auto'] : ['520px', 'auto'];
                    var isAgentInfo = d.is_agent_info || false;
                    var desc = qq ? ((isAgentInfo ? '客服QQ：' : '官方客服QQ：') + qq) : '请通过官方认证渠道获取正版授权';
                    
                    var content = `
                        <div class="em-modal-box">
                            <div class="em-modal-close-btn" onclick="layer.close(layer.index)">
                                <svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                            </div>
                            <div class="em-modal-header">
                                <div class="em-modal-icon-wrapper"><i class="ri-vip-diamond-line"></i></div>
                                <div class="em-modal-title">获取正版授权</div>
                                <div class="em-modal-desc">${desc}</div>
                            </div>
                            <div class="em-modal-body">
                                <div class="em-modal-list">
                                    ${linksHtml}
                                </div>
                            </div>
                        </div>
                    `;
                    
                    layer.open({
                        type: 1,
                        title: false,
                        closeBtn: 0,
                        area: area,
                        shadeClose: true,
                        skin: 'em-modal-skin',
                        btn: false,
                        content: content
                    });
                }else{
                    layer.msg(e.msg, {icon: 2});
                }
            },
            error: function(xhr, textStatus, errorThrown){
                layer.msg('网络请求超时，请重试或更换其他线路~', {icon: 2});
            },
            complete: function(){
                layer.close(loadIndex);
            }
        });
    })


    $(function () {
        $(document).on('click', 'a.scroll-to-top', function (e) {
            var $anchor = $(this);
            $('html, body').stop().animate({
                scrollTop: ($($anchor.attr('href')).offset().top)
            }, 1000, 'easeInOutExpo');
            e.preventDefault();
        });



        // 初始化
        const menu = $("#left-menu");
        const overlay = $(".overlay");
        const toggleBtn = $(".show-nav");
        overlay.css({ opacity: 0.06, display: "none" });

        // 切换菜单
        toggleBtn.click(function() {
            $('#content').addClass('toright')
            $('#left-menu').addClass('toright')
            overlay.show();
        });

        // 点击遮罩层关闭菜单
        overlay.click(function() {
            $('#content').removeClass('toright')
            $('#left-menu').removeClass('toright')
            overlay.hide();
        });

    });
</script>

<!-- 设置页通用：回车键保存 -->
<script>
(function(){
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;

    // 仅在设置类页面启用，避免干扰列表/搜索表单的 Enter 行为
    function isSettingPage(){
        var path = (location.pathname || '').toLowerCase();
        if (/(^|\/)(setting|blogger|shop|shop_btx|shop_gg)\.php$/.test(path)) return true;
        // 允许页面显式开启：<body data-enter-save="1"> 或 <form data-enter-save="1">
        if ($('body').attr('data-enter-save') === '1') return true;
        return false;
    }
    if (!isSettingPage() && !$('form[data-enter-save="1"]').length) return;

    $(document).on('keydown', 'form input, form select', function(e){
        if (e.key !== 'Enter' && e.keyCode !== 13) return;
        // 屏蔽中文输入法回车确认候选词
        if (e.isComposing || (e.originalEvent && e.originalEvent.isComposing)) return;
        if (e.ctrlKey || e.shiftKey || e.altKey || e.metaKey) return;

        var el = e.target;
        var tag = (el.tagName || '').toLowerCase();
        if (tag === 'textarea') return;
        var type = (el.type || '').toLowerCase();
        if (['button','submit','reset','file','checkbox','radio'].indexOf(type) !== -1) return;

        var $form = $(el).closest('form');
        if (!$form.length) return;

        // 跳过后台账户页等未声明 lay-submit 保存按钮的搜索/筛选类表单
        var $btn = $form.find('button[lay-submit][lay-filter]').not('[disabled]').first();
        if (!$btn.length) {
            $btn = $form.find('button[type="submit"]').not('[disabled]').first();
        }
        if (!$btn.length) return;

        // 当所在弹层（layui-layer）是只读/详情弹窗时不拦截
        if ($(el).closest('.layui-layer-page').find('form').length === 0 &&
            $(el).closest('.layui-layer-page').length > 0) {
            return;
        }

        e.preventDefault();
        e.stopPropagation();
        // 必须用原生 click()：layui form.on('submit(filter)') 通过 addEventListener 绑定，
        // jQuery 的 .trigger('click') 只会触发 jQuery 事件队列，不会触发 layui 的原生监听
        try {
            if ($btn[0] && typeof $btn[0].click === 'function') {
                $btn[0].click();
            } else {
                $btn.trigger('click');
            }
        } catch(err) {
            $btn.trigger('click');
        }
    });
})();
</script>
<?php
// 强制更新拦截（在线更新已关闭，不再检查）
?>
</body>
</html>