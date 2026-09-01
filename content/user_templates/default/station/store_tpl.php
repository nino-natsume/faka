<?php
defined('DC_ROOT') || exit('access denied!');
?>
<style>
    .store-tpl-page { display: flex; flex-direction: column; gap: 22px; padding: 8px 0 18px; }
    .sts-hero { position: relative; overflow: hidden; border-radius: 10px; padding: 30px 32px; background: linear-gradient(135deg, rgba(var(--tp-rgb),.96) 0%, rgba(139,92,246,.92) 58%, rgba(167,139,250,.88) 100%); box-shadow: 0 24px 56px rgba(var(--tp-rgb),.2); color: #fff; }
    .sts-hero::before,.sts-hero::after { content:''; position: absolute; border-radius: 999px; background: rgba(255,255,255,.1); pointer-events: none; }
    .sts-hero::before { width: 240px; height: 240px; right: -80px; top: -100px; }
    .sts-hero::after { width: 180px; height: 180px; right: 120px; bottom: -100px; }
    .sts-hero-inner { position: relative; z-index: 1; display: grid; grid-template-columns: minmax(0,1fr) auto; gap: 18px; align-items: end; }
    .sts-eyebrow { display: inline-flex; align-items: center; gap: 8px; padding: 7px 12px; border-radius: 999px; background: rgba(255,255,255,.14); font-size: 12px; font-weight: 700; letter-spacing: .08em; }
    .sts-title { margin: 14px 0 10px; color: #fff; font-size: 32px; line-height: 1.2; font-weight: 800; }
    .sts-desc { max-width: 760px; margin: 0; color: rgba(255,255,255,.84); font-size: 14px; line-height: 1.9; }
    .sts-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 10px; }
    .sts-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-width: 120px; height: 44px; padding: 0 18px; border-radius: 14px; border: 1px solid rgba(255,255,255,.24); background: rgba(255,255,255,.12); color: #fff; font-size: 14px; font-weight: 700; text-decoration: none; cursor: pointer; transition: .18s ease; }
    .sts-btn:hover { color: #fff; text-decoration: none; transform: translateY(-2px); background: rgba(255,255,255,.18); }
    .sts-btn.is-primary { background: #fff; border-color: rgba(255,255,255,.9); color: var(--theme-primary); box-shadow: 0 12px 28px rgba(16,42,104,.2); }
    .sts-btn.is-primary:hover { color: var(--theme-primary); }
    .sts-card { background: var(--pc-card-bg); border: 2px solid #fff; box-shadow: 0 1px 18px #12345b0a; border-radius: 10px; }
    .sts-tabs-bar { padding: 18px 20px; display: flex; flex-wrap: wrap; gap: 14px; align-items: center; justify-content: space-between; }
    .sts-tabs-group { display: inline-flex; flex-wrap: wrap; gap: 10px; padding: 8px; border-radius: 10px; background: var(--bg-secondary, #f5f7fa); }
    .sts-tab { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 0 18px; border-radius: 14px; color: var(--text-sub); font-size: 14px; font-weight: 700; text-decoration: none; transition: .18s; }
    .sts-tab:hover { color: var(--text-main); text-decoration: none; background: rgba(var(--tp-rgb),.06); }
    .sts-tab.is-active { background: #fff; color: var(--theme-primary); box-shadow: 0 10px 20px rgba(15,23,42,.08); }
    .sts-search-bar { padding: 18px 20px; display: flex; flex-wrap: wrap; gap: 12px; align-items: center; }
    .sts-search-input { flex: 1; min-width: 180px; height: 44px; padding: 0 18px; border: 1px solid var(--card-border); border-radius: 14px; background: var(--bg-secondary,#f5f7fa); color: var(--text-main); font-size: 14px; outline: none; transition: .18s; }
    .sts-search-input:focus { border-color: rgba(var(--tp-rgb),.4); box-shadow: 0 0 0 4px rgba(var(--tp-rgb),.08); }
    .sts-search-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 0 20px; border: none; border-radius: 14px; background: linear-gradient(135deg,#7c3aed 0%,var(--tp-light) 100%); color: #fff; font-size: 14px; font-weight: 700; cursor: pointer; transition: .18s; }
    .sts-search-btn:hover { transform: translateY(-2px); box-shadow: 0 14px 26px rgba(var(--tp-rgb),.2); }
    .sts-notice { padding: 22px 24px; display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
    .sts-notice strong { display: block; margin-bottom: 8px; color: var(--text-main); font-size: 16px; font-weight: 800; }
    .sts-notice p { margin: 0; color: var(--text-sub); font-size: 13px; line-height: 1.85; }
    .sts-badge { display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 999px; background: rgba(var(--tp-rgb),.08); color: var(--theme-primary); font-size: 12px; font-weight: 700; white-space: nowrap; }
    .sts-toolbar { padding: 18px 20px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
    .sts-tool-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 42px; padding: 0 16px; border: 1px solid var(--card-border); border-radius: 14px; background: #fff; color: var(--text-main); font-size: 14px; font-weight: 700; cursor: pointer; transition: .18s; }
    .sts-tool-btn:hover { transform: translateY(-2px); border-color: rgba(var(--tp-rgb),.28); box-shadow: 0 14px 26px rgba(15,23,42,.08); }
    .sts-data { padding: 18px 18px 8px; }
    .sts-data-head { display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 16px; padding: 0 6px; }
    .sts-data-title { margin: 0; color: var(--text-main); font-size: 18px; font-weight: 800; }
    .sts-data-desc { margin: 8px 0 0; color: var(--text-sub); font-size: 12px; line-height: 1.8; }
    .sts-table-wrap .layui-table-view { margin: 0; border-radius: 10px; overflow: hidden; border-color: var(--card-border); }
    .sts-table-wrap .layui-table-header th { background: rgba(var(--tp-rgb),.05); color: var(--text-main); font-weight: 700; border-color: var(--card-border); }
    .sts-table-wrap .layui-table-body tr:hover td { background: rgba(var(--tp-rgb),.03); }
    .sts-table-wrap .layui-table-header { overflow-x: auto !important; scrollbar-width: none; }
    .sts-table-wrap .layui-table-header::-webkit-scrollbar { display: none; }
    .sts-table-wrap .layui-table th:first-child,
    .sts-table-wrap .layui-table td:first-child { position: sticky !important; left: 0; z-index: 2; background: #fff; box-shadow: 2px 0 4px rgba(0,0,0,.04); }
    .sts-table-wrap .layui-table-header th:first-child { background: rgba(var(--tp-rgb),.05); }
    .sts-cover { display: inline-flex; width: 56px; height: 56px; border-radius: 14px; overflow: hidden; background: rgba(var(--tp-rgb),.06); border: 1px solid rgba(var(--tp-rgb),.08); cursor: pointer; }
    .sts-cover img { width: 100%; height: 100%; object-fit: cover; }
    .sts-name-cell { display: flex; flex-direction: column; gap: 4px; padding: 4px 0; }
    .sts-name-main { color: var(--text-main); font-size: 14px; font-weight: 700; line-height: 1.7; }
    .sts-name-sub { color: var(--text-sub); font-size: 12px; line-height: 1.7; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .sts-price { display: inline-flex; align-items: center; justify-content: center; min-width: 64px; padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; }
    .sts-price.is-free { background: rgba(16,185,129,.1); color: #059669; }
    .sts-price.is-paid { background: rgba(239,68,68,.08); color: #dc2626; }
    .sts-ver { display: inline-flex; align-items: center; justify-content: center; min-width: 60px; padding: 6px 12px; border-radius: 999px; background: rgba(var(--tp-rgb),.1); color: var(--theme-primary); font-size: 12px; font-weight: 700; }
    .sts-op-group { display: flex; flex-wrap: wrap; gap: 8px; }
    .sts-op-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-width: 78px; height: 34px; padding: 0 14px; border-radius: 12px; border: 1px solid rgba(var(--tp-rgb),.16); background: rgba(var(--tp-rgb),.06); color: var(--theme-primary); font-size: 12px; font-weight: 700; cursor: pointer; text-decoration: none; transition: .18s; }
    .sts-op-btn:hover { text-decoration: none; color: var(--theme-primary); transform: translateY(-1px); }
    .sts-op-btn.is-installed { background: rgba(16,185,129,.08); border-color: rgba(16,185,129,.16); color: #059669; cursor: default; pointer-events: none; }
    .sts-op-btn.is-buy { background: rgba(var(--tp-rgb),.08); border-color: rgba(var(--tp-rgb),.16); color: var(--theme-primary); }
    .sts-op-btn.is-install { background: linear-gradient(135deg,#7c3aed 0%,var(--tp-light) 100%); border-color: transparent; color: #fff; }
    .sts-op-btn.is-auth { background: rgba(245,158,11,.08); border-color: rgba(245,158,11,.16); color: #d97706; }
    .sts-mobile-list { display: none; gap: 14px; margin-top: 2px; }
    .sts-mobile-card { padding: 18px; border: 1px solid var(--card-border); border-radius: 10px; background: linear-gradient(180deg,rgba(255,255,255,.98),rgba(250,245,255,.96)); }
    .sts-mobile-head { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 14px; }
    .sts-mobile-head .sts-cover { flex-shrink: 0; width: 64px; height: 64px; }
    .sts-mobile-info { flex: 1; min-width: 0; }
    .sts-mobile-title { margin: 0; color: var(--text-main); font-size: 15px; font-weight: 800; line-height: 1.7; }
    .sts-mobile-desc { color: var(--text-sub); font-size: 12px; line-height: 1.7; margin-top: 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .sts-mobile-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
    .sts-mobile-actions { display: flex; flex-wrap: wrap; gap: 10px; }
    .sts-empty { padding: 36px 20px; text-align: center; color: var(--text-sub); font-size: 13px; }
    .sts-mobile-pager { display: none; padding: 16px 0; text-align: center; }
    .sts-pager-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 42px; padding: 0 24px; border: 1px solid var(--card-border); border-radius: 14px; background: #fff; color: var(--text-main); font-size: 14px; font-weight: 700; cursor: pointer; transition: .18s; }
    .sts-pager-btn:hover { border-color: rgba(var(--tp-rgb),.28); }
    .sts-pager-btn.is-disabled { opacity: .4; pointer-events: none; }
    @media(max-width:1100px){
        .sts-hero-inner { grid-template-columns: 1fr; }
        .sts-actions { justify-content: flex-start; }
    }
    @media(max-width:768px){
        .sts-hero { border-radius: 10px; padding: 24px 20px; }
        .sts-title { font-size: 26px; }
        .sts-actions { display: grid; grid-template-columns: 1fr 1fr; width: 100%; }
        .sts-btn { width: 100%; min-width: 0; }
        .sts-tabs-bar { display: grid; grid-template-columns: 1fr; }
        .sts-tabs-group { width: 100%; }
        .sts-tab { flex: 1; }
        .sts-search-bar { flex-direction: column; align-items: stretch; }
        .sts-search-input { width: 100%; }
        .sts-search-btn { width: 100%; }
        .sts-toolbar { flex-direction: column; align-items: stretch; }
        .sts-tool-btn { width: 100%; }
        .sts-table-wrap { display: none; }
        .sts-mobile-list { display: grid; }
        .sts-mobile-pager { display: block; }
    }
    @media(max-width:560px){
        .sts-actions { grid-template-columns: 1fr; }
        .sts-notice,.sts-data-head { flex-direction: column; align-items: flex-start; }
    }
</style>

<main class="store-tpl-page">
    <section class="sts-hero">
        <div class="sts-hero-inner">
            <div>
                <span class="sts-eyebrow"><i class="fa fa-shopping-bag"></i> TEMPLATE STORE</span>
                <h1 class="sts-title">模板商店</h1>
                <p class="sts-desc">浏览并安装可用的分店前端模板。支持在线预览、一键购买与安装，免费模板可直接部署，手机端自动切换为卡片浏览模式。</p>
            </div>
            <div class="sts-actions">
                <a href="?action=setting_tpl" class="sts-btn"><i class="fa fa-puzzle-piece"></i> 我的模板</a>
                <a href="?action=setting" class="sts-btn"><i class="fa fa-cog"></i> 基础设置</a>
                <button type="button" class="sts-btn is-primary" id="storeRefreshHero"><i class="fa fa-refresh"></i> 刷新商店</button>
            </div>
        </div>
    </section>

    <section class="sts-card sts-tabs-bar">
        <div class="sts-tabs-group">
            <a href="?action=setting" class="sts-tab">基础设置</a>
            <a href="?action=setting_tpl" class="sts-tab">模板配置</a>
        </div>
        <div class="sts-tabs-group">
            <a href="?action=setting_tpl" class="sts-tab">我的模板</a>
            <a href="?action=store_tpl" class="sts-tab is-active">模板商店</a>
        </div>
    </section>

    <section class="sts-card sts-search-bar">
        <input type="text" class="sts-search-input" id="storeKeyword" placeholder="搜索模板名称或关键词…" />
        <button type="button" class="sts-search-btn" id="storeSearchBtn"><i class="fa fa-search"></i> 搜索</button>
    </section>

    <section class="sts-card sts-notice">
        <div>
            <strong>商店说明</strong>
            <p>免费模板可直接安装使用。付费模板购买后点击"立即安装"即可部署。安装完成后前往"我的模板"页启用。限授权站点的模板需先完成站点授权。</p>
        </div>
        <span class="sts-badge"><i class="fa fa-info-circle"></i> 安装后去我的模板启用</span>
    </section>

    <section class="sts-card sts-toolbar">
        <button type="button" class="sts-tool-btn" id="storeRefreshBtn"><i class="fa fa-refresh"></i> 刷新列表</button>
    </section>

    <section class="sts-card sts-data">
        <div class="sts-data-head">
            <div>
                <h2 class="sts-data-title">可用模板</h2>
                <p class="sts-data-desc">桌面端使用表格浏览，手机端自动切换为卡片模式，方便查看模板详情与操作。</p>
            </div>
            <span class="sts-badge"><i class="fa fa-th-large"></i> PC 表格 / 手机卡片</span>
        </div>
        <div class="sts-table-wrap">
            <table class="layui-hide" id="index" lay-filter="index"></table>
        </div>
        <div class="sts-mobile-list" id="storeMobileList"></div>
        <div class="sts-mobile-pager" id="storeMobilePager"></div>
    </section>
</main>

<script type="text/html" id="storeCoverTpl">
    <a href="javascript:;" class="sts-cover" lay-event="img"><img onerror="this.onerror=null;this.src='./views/images/null.png'" src="{{ d.img }}" /></a>
</script>
<script type="text/html" id="storeNameTpl">
    <div class="sts-name-cell"><div class="sts-name-main">{{ d.name }}</div><div class="sts-name-sub">{{ d.description }}</div></div>
</script>
<script type="text/html" id="storeVipTpl">
    {{# if(d.vip_price == 0){ }}<span class="sts-price is-free">免费</span>{{# } }}
    {{# if(d.vip_price != 0){ }}<span class="sts-price is-paid">&yen;{{ d.vip_price }}</span>{{# } }}
</script>
<script type="text/html" id="storeSvipTpl">
    {{# if(d.svip_price == 0){ }}<span class="sts-price is-free">免费</span>{{# } }}
    {{# if(d.svip_price != 0){ }}<span class="sts-price is-paid">&yen;{{ d.svip_price }}</span>{{# } }}
</script>
<script type="text/html" id="storeVersionTpl">
    <span class="sts-ver">v{{ d.version }}</span>
</script>
<script type="text/html" id="storeOperateTpl">
    <div class="sts-op-group">
        {{# if(d.is_install == 'y'){ }}<span class="sts-op-btn is-installed"><i class="fa fa-check-circle"></i> 已安装</span>{{# } }}
        {{# if(d.is_install == 'n' && d.reg_type == '0' && d.vip_price > 0){ }}<button type="button" class="sts-op-btn is-auth" lay-event="auth"><i class="fa fa-lock"></i> 需授权</button>{{# } }}
        {{# if(d.pay == 'y' && d.is_install == 'n'){ }}<button type="button" class="sts-op-btn is-install" lay-event="install"><i class="fa fa-download"></i> 安装</button>{{# } }}
        {{# if(d.pay == 'n' && d.is_install == 'n' && d.reg_type == '1' && d.vip_price > 0){ }}<a target="_blank" href="<?= DC_LINE[CURRENT_LINE]['value'] ?>index/order/submit/station_unique/<?= $userData['station']['station_unique'] ?>/{{ ['tpl','template','home','home_template','station','station_template','user','user_tpl','user_template','bottom_nav','bottom_nav_tpl','bottom_nav_template','blog','blog_tpl','blog_template'].indexOf(d.type) !== -1 ? 'tpl' : 'plugin' }}/{{ d.id }}" class="sts-op-btn is-buy"><i class="fa fa-shopping-cart"></i> 购买</a>{{# } }}
        {{# if(d.pay == 'n' && d.is_install == 'n' && d.reg_type == '2' && d.svip_price > 0){ }}<a target="_blank" href="<?= DC_LINE[CURRENT_LINE]['value'] ?>index/order/submit/station_unique/<?= $userData['station']['station_unique'] ?>/{{ ['tpl','template','home','home_template','station','station_template','user','user_tpl','user_template','bottom_nav','bottom_nav_tpl','bottom_nav_template','blog','blog_tpl','blog_template'].indexOf(d.type) !== -1 ? 'tpl' : 'plugin' }}/{{ d.id }}" class="sts-op-btn is-buy"><i class="fa fa-shopping-cart"></i> 购买</a>{{# } }}
        {{# if(d.is_install == 'n' && d.reg_type == '0' && d.vip_price == 0){ }}<button type="button" class="sts-op-btn is-install" lay-event="install"><i class="fa fa-download"></i> 免费安装</button>{{# } }}
        {{# if(d.is_install == 'n' && d.reg_type == '1' && d.vip_price == 0){ }}<button type="button" class="sts-op-btn is-install" lay-event="install"><i class="fa fa-download"></i> 免费安装</button>{{# } }}
        {{# if(d.is_install == 'n' && d.reg_type == '2' && d.svip_price == 0){ }}<button type="button" class="sts-op-btn is-install" lay-event="install"><i class="fa fa-download"></i> 免费安装</button>{{# } }}
    </div>
</script>

<script>
    layui.use(['table', 'form', 'layer'], function(){
        var table = layui.table, layer = layui.layer, form = layui.form;
        var tableId = 'index';
        var mobilePage = 1, mobileLimit = 10, mobileTotalPages = 1;
        var buyBaseUrl = "<?= DC_LINE[CURRENT_LINE]['value'] ?>index/order/submit/station_unique/<?= $userData['station']['station_unique'] ?>";

        function getInstallType(rawType){
            var type = rawType || 'plugin';
            if (type === 'tpl' || type === 'home' || type === 'home_template' || type === 'station' || type === 'station_template' || type === 'template') {
                return 'tpl';
            }
            if (type === 'user' || type === 'user_tpl') return 'user_template';
            if (type === 'bottom_nav' || type === 'bottom_nav_tpl') return 'bottom_nav_template';
            if (type === 'blog' || type === 'blog_tpl') return 'blog_template';
            return ['user_template', 'bottom_nav_template', 'blog_template'].indexOf(type) !== -1 ? type : 'plugin';
        }

        function getInstallManageUrl(rawType){
            var type = getInstallType(rawType);
            if (type === 'blog_template') return '<?= DC_URL ?>admin/template.php?tab=blog';
            return '?action=setting_tpl';
        }

        function asyncInstallApp(postData, appName, onSuccess, onFail) {
            var loadIdx = layer.load(2);
            $.ajax({
                url: './store.php?action=install', type: 'POST', dataType: 'json', data: postData,
                success: function(res) {
                    if (res.code !== 0 && res.code !== 200) {
                        layer.close(loadIdx);
                        if (onFail) onFail(res.msg || '安装请求失败');
                        else layer.msg(res.msg || '安装请求失败');
                        return;
                    }
                    var pollCount = 0;
                    var pollTimer = setInterval(function() {
                        pollCount++;
                        if (pollCount > 90) { clearInterval(pollTimer); layer.close(loadIdx); if (onFail) onFail('安装超时，请重试'); return; }
                        $.ajax({ url: './store.php?action=install_progress', type: 'GET', dataType: 'json', timeout: 5000, success: function(pRes) {
                            if (!pRes || (pRes.code !== 0 && pRes.code !== 200)) return;
                            var task = pRes.data || {};
                            if (task.status === 'completed') { clearInterval(pollTimer); layer.close(loadIdx); if (onSuccess) onSuccess(task); }
                            else if (task.status === 'failed') { clearInterval(pollTimer); layer.close(loadIdx); if (onFail) onFail(task.error || '安装失败'); }
                            else if (task.status === 'expired') { clearInterval(pollTimer); layer.close(loadIdx); if (onFail) onFail('安装超时，请重试'); }
                        }});
                    }, 2000);
                },
                error: function(err) {
                    layer.close(loadIdx);
                    if (onFail) onFail(err.responseJSON && err.responseJSON.msg ? err.responseJSON.msg : '安装失败');
                }
            });
        }

        function safeText(v){ return $('<div>').text(v == null ? '' : String(v)).html(); }

        function buildOperateHtml(d){
            var html = '<div class="sts-mobile-actions">';
            if(d.is_install === 'y'){
                html += '<span class="sts-op-btn is-installed"><i class="fa fa-check-circle"></i> 已安装</span>';
            }
            if(d.is_install === 'n' && d.reg_type === '0' && d.vip_price > 0){
                html += '<button type="button" class="sts-op-btn is-auth" data-action="auth"><i class="fa fa-lock"></i> 需授权</button>';
            }
            if(d.pay === 'y' && d.is_install === 'n'){
                html += '<button type="button" class="sts-op-btn is-install" data-action="install" data-id="' + safeText(d.id) + '" data-slug="' + safeText(d.english_name || d.slug || '') + '" data-type="' + safeText(d.type) + '" data-download="' + safeText(d.download_url) + '" data-cdn="' + safeText(d.cdn_download_url) + '"><i class="fa fa-download"></i> 安装</button>';
            }
            if(d.pay === 'n' && d.is_install === 'n' && d.reg_type === '1' && d.vip_price > 0){
                html += '<a target="_blank" href="' + buyBaseUrl + '/' + (['tpl','template','home','home_template','station','station_template','user','user_tpl','user_template','bottom_nav','bottom_nav_tpl','bottom_nav_template','blog','blog_tpl','blog_template'].indexOf(d.type) !== -1 ? 'tpl' : 'plugin') + '/' + d.id + '" class="sts-op-btn is-buy"><i class="fa fa-shopping-cart"></i> 购买</a>';
            }
            if(d.pay === 'n' && d.is_install === 'n' && d.reg_type === '2' && d.svip_price > 0){
                html += '<a target="_blank" href="' + buyBaseUrl + '/' + (['tpl','template','home','home_template','station','station_template','user','user_tpl','user_template','bottom_nav','bottom_nav_tpl','bottom_nav_template','blog','blog_tpl','blog_template'].indexOf(d.type) !== -1 ? 'tpl' : 'plugin') + '/' + d.id + '" class="sts-op-btn is-buy"><i class="fa fa-shopping-cart"></i> 购买</a>';
            }
            if(d.is_install === 'n' && d.reg_type === '0' && d.vip_price == 0){
                html += '<button type="button" class="sts-op-btn is-install" data-action="install" data-id="' + safeText(d.id) + '" data-slug="' + safeText(d.english_name || d.slug || '') + '" data-type="' + safeText(d.type) + '" data-download="' + safeText(d.download_url) + '" data-cdn="' + safeText(d.cdn_download_url) + '"><i class="fa fa-download"></i> 免费安装</button>';
            }
            if(d.is_install === 'n' && d.reg_type === '1' && d.vip_price == 0){
                html += '<button type="button" class="sts-op-btn is-install" data-action="install" data-id="' + safeText(d.id) + '" data-slug="' + safeText(d.english_name || d.slug || '') + '" data-type="' + safeText(d.type) + '" data-download="' + safeText(d.download_url) + '" data-cdn="' + safeText(d.cdn_download_url) + '"><i class="fa fa-download"></i> 免费安装</button>';
            }
            if(d.is_install === 'n' && d.reg_type === '2' && d.svip_price == 0){
                html += '<button type="button" class="sts-op-btn is-install" data-action="install" data-id="' + safeText(d.id) + '" data-slug="' + safeText(d.english_name || d.slug || '') + '" data-type="' + safeText(d.type) + '" data-download="' + safeText(d.download_url) + '" data-cdn="' + safeText(d.cdn_download_url) + '"><i class="fa fa-download"></i> 免费安装</button>';
            }
            html += '</div>';
            return html;
        }

        function renderMobileList(list){
            var $el = $('#storeMobileList');
            if(!list.length){ $el.html('<div class="sts-empty">暂无可用模板</div>'); return; }
            var html = list.map(function(d){
                var priceVip = d.vip_price == 0 ? '<span class="sts-price is-free">免费</span>' : '<span class="sts-price is-paid">&yen;' + safeText(d.vip_price) + '</span>';
                var priceSvip = d.svip_price == 0 ? '<span class="sts-price is-free">SVIP免费</span>' : '<span class="sts-price is-paid">SVIP &yen;' + safeText(d.svip_price) + '</span>';
                return '<article class="sts-mobile-card">' +
                    '<div class="sts-mobile-head">' +
                        '<a href="javascript:;" class="sts-cover" data-action="img" data-name="' + safeText(d.name) + '" data-src="' + safeText(d.img) + '"><img onerror="this.onerror=null;this.src=\'./views/images/null.png\'" src="' + safeText(d.img) + '" /></a>' +
                        '<div class="sts-mobile-info">' +
                            '<h3 class="sts-mobile-title">' + safeText(d.name) + '</h3>' +
                            '<p class="sts-mobile-desc">' + safeText(d.description) + '</p>' +
                        '</div>' +
                    '</div>' +
                    '<div class="sts-mobile-meta">' +
                        priceVip + priceSvip +
                        '<span class="sts-ver">v' + safeText(d.version) + '</span>' +
                    '</div>' +
                    buildOperateHtml(d) +
                '</article>';
            }).join('');
            $el.html(html);
        }

        function renderMobilePager(){
            var $pager = $('#storeMobilePager');
            if(mobileTotalPages <= 1){ $pager.html(''); return; }
            $pager.html(
                '<button type="button" class="sts-pager-btn' + (mobilePage <= 1 ? ' is-disabled' : '') + '" id="storeMobilePrev"><i class="fa fa-angle-left"></i> 上一页</button> ' +
                '<span style="display:inline-block;padding:0 12px;font-size:13px;color:var(--text-sub);">' + mobilePage + ' / ' + mobileTotalPages + '</span> ' +
                '<button type="button" class="sts-pager-btn' + (mobilePage >= mobileTotalPages ? ' is-disabled' : '') + '" id="storeMobileNext">下一页 <i class="fa fa-angle-right"></i></button>'
            );
        }

        function loadMobileData(page){
            var keyword = $('#storeKeyword').val() || '';
            $.ajax({
                url: '?action=store_tpl_ajax', type: 'GET', dataType: 'json',
                data: { page: page, keyword: keyword },
                success: function(res){
                    var list = res.data || [];
                    var count = res.count || 0;
                    mobileTotalPages = Math.max(1, Math.ceil(count / 10));
                    mobilePage = page;
                    renderMobileList(list);
                    renderMobilePager();
                }
            });
        }

        window.table = table.render({
            elem: '#index',
            id: tableId,
            autoSort: false,
            url: '?action=store_tpl_ajax',
            limits: [10,20,30,50,100],
            lineStyle: 'height: 72px;',
            page: true,
            defaultToolbar: ['filter','exports'],
            cols: [[
                {field:'preview', title:'预览', width: 80, templet: '#storeCoverTpl', align: 'center'},
                {field:'name', title:'模板信息', minWidth: 280, templet: '#storeNameTpl'},
                {field:'svip_price', title:'SVIP', width: 100, align: 'center', templet: '#storeSvipTpl'},
                {field:'vip_price', title:'普通会员', width: 100, align: 'center', templet: '#storeVipTpl'},
                {field:'version', title:'版本', width: 110, align: 'center', templet: '#storeVersionTpl'},
                {title:'操作', templet: '#storeOperateTpl', width: 200, align: 'center'}
            ]],
            done: function(res){
                if(window.innerWidth < 768){
                    var count = res.count || 0;
                    mobileTotalPages = Math.max(1, Math.ceil(count / 10));
                    mobilePage = 1;
                    renderMobileList(res.data || []);
                    renderMobilePager();
                }
            },
            error: function(res, msg){
                console.log(res, msg);
            }
        });

        table.on('tool(index)', function(obj){
            var data = obj.data;
            if(obj.event === 'img'){
                layer.photos({ photos: { title: data.name, start: 0, data: [{ alt: data.name, pid: 1, src: data.img }] } });
            }
            if(obj.event === 'auth'){
                layer.confirm('是否前往授权页面？', {
                    btn: ['立即前往', '取消'], icon: 3, title: '温馨提示'
                }, function(idx){ layer.closeAll(); location.href = './auth.php'; });
            }
            if(obj.event === 'install'){
                var type = getInstallType(data.type);
                asyncInstallApp(
                    { type: type, plugin_id: data.id, slug: (data.english_name || data.slug || ''), source: data.download_url, cdn_source: data.cdn_download_url },
                    data.name,
                    function(){ layer.alert('安装成功', function(){ location.href = getInstallManageUrl(data.type); }); table.reload(tableId); },
                    function(err){ layer.alert(err); }
                );
            }
        });

        $('#storeSearchBtn').on('click', function(){
            var kw = $('#storeKeyword').val() || '';
            table.reload(tableId, { page: { curr: 1 }, where: { keyword: kw } });
            if(window.innerWidth < 768) loadMobileData(1);
        });
        $('#storeKeyword').on('keydown', function(e){
            if(e.keyCode === 13){ e.preventDefault(); $('#storeSearchBtn').click(); }
        });

        $('#storeRefreshBtn, #storeRefreshHero').on('click', function(){
            table.reload(tableId);
            if(window.innerWidth < 768) loadMobileData(mobilePage);
        });

        $(document).on('click', '#storeMobilePrev', function(){ if(mobilePage > 1) loadMobileData(mobilePage - 1); });
        $(document).on('click', '#storeMobileNext', function(){ if(mobilePage < mobileTotalPages) loadMobileData(mobilePage + 1); });

        $('#storeMobileList').on('click', '[data-action="img"]', function(){
            var $t = $(this);
            layer.photos({ photos: { title: $t.data('name'), start: 0, data: [{ alt: $t.data('name'), pid: 1, src: $t.data('src') }] } });
        });

        $('#storeMobileList').on('click', '[data-action="auth"]', function(){
            layer.confirm('是否前往授权页面？', {
                btn: ['立即前往', '取消'], icon: 3, title: '温馨提示'
            }, function(idx){ layer.closeAll(); location.href = './auth.php'; });
        });

        $('#storeMobileList').on('click', '[data-action="install"]', function(){
            var $btn = $(this);
            var rawType = String($btn.data('type') || 'plugin');
            var type = getInstallType(rawType);
            asyncInstallApp(
                { type: type, plugin_id: $btn.data('id'), slug: $btn.data('slug'), source: $btn.data('download'), cdn_source: $btn.data('cdn') },
                '',
                function(){ layer.alert('安装成功', function(){ location.href = getInstallManageUrl(rawType); }); },
                function(err){ layer.alert(err); }
            );
        });
    });
</script>

<script>
    $('#menu-station').addClass('open');
    $('#menu-station > ul').css('display', 'block');
    $('#menu-station > a > i.nav_right').attr('class', 'fa fa-angle-down nav_right');
    $('#menu-station-setting').addClass('menu-current');
</script>
<?php include __DIR__ . '/../_pc_page_footer.php'; ?>
