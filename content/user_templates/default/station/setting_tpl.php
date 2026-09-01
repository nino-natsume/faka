<?php
defined('DC_ROOT') || exit('access denied!');
?>
<style>
    .station-tpl-page { display: flex; flex-direction: column; gap: 22px; padding: 8px 0 18px; }
    .stp-page-header { display: flex; align-items: center; gap: 14px; }
    .stp-back-btn { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 12px; border: 1.5px solid var(--card-border, #e2e8f0); background: var(--card-bg, #fff); color: var(--text-sub, #64748b); font-size: 16px; cursor: pointer; transition: .18s; text-decoration: none; flex-shrink: 0; }
    .stp-back-btn:hover { color: var(--theme-primary); border-color: rgba(var(--tp-rgb),.22); background: rgba(var(--tp-rgb),.06); text-decoration: none; }
    .stp-page-title { margin: 0; font-size: 22px; font-weight: 800; color: var(--text-main, #1e293b); }
    .stp-page-desc { margin: 2px 0 0; font-size: 13px; color: var(--text-sub, #64748b); }
    .stp-hero { position: relative; overflow: hidden; border-radius: 10px; padding: 30px 32px; background: linear-gradient(135deg, rgba(43,88,200,.96) 0%, rgba(73,125,242,.92) 58%, rgba(126,167,255,.88) 100%); box-shadow: 0 24px 56px rgba(var(--tp-rgb),.2); color: #fff; border: 2px solid #fff; }
    .stp-hero::before,.stp-hero::after { content:''; position: absolute; border-radius: 999px; background: rgba(255,255,255,.12); pointer-events: none; }
    .stp-hero::before { width: 220px; height: 220px; right: -72px; top: -96px; }
    .stp-hero::after { width: 170px; height: 170px; right: 108px; bottom: -92px; }
    .stp-hero-inner { position: relative; z-index: 1; display: grid; grid-template-columns: minmax(0,1fr) auto; gap: 18px; align-items: end; }
    .stp-eyebrow { display: inline-flex; align-items: center; gap: 8px; padding: 7px 12px; border-radius: 999px; background: rgba(255,255,255,.14); font-size: 12px; font-weight: 700; letter-spacing: .08em; }
    .stp-title { margin: 14px 0 10px; color: #fff; font-size: 32px; line-height: 1.2; font-weight: 800; }
    .stp-desc { max-width: 760px; margin: 0; color: rgba(255,255,255,.84); font-size: 14px; line-height: 1.9; }
    .stp-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 10px; }
    .stp-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-width: 120px; height: 44px; padding: 0 18px; border-radius: 14px; border: 1px solid rgba(255,255,255,.24); background: rgba(255,255,255,.12); color: #fff; font-size: 14px; font-weight: 700; text-decoration: none; cursor: pointer; transition: .18s ease; }
    .stp-btn:hover { color: #fff; text-decoration: none; transform: translateY(-2px); background: rgba(255,255,255,.18); }
    .stp-btn.is-primary { background: #fff; border-color: rgba(255,255,255,.9); color: #2456ca; box-shadow: 0 12px 28px rgba(16,42,104,.2); }
    .stp-btn.is-primary:hover { color: #1f4ab4; }
    .stp-card { background: var(--pc-card-bg); border: 2px solid #fff; box-shadow: 0 1px 18px #12345b0a; border-radius: 10px; }
    .stp-tabs-bar { padding: 18px 0px; display: flex; flex-wrap: wrap; gap: 14px; align-items: center; justify-content: space-between; }
    .stp-tabs-group { display: inline-flex; flex-wrap: wrap; gap: 10px; padding: 8px; border-radius: 10px; background: var(--bg-secondary, #f5f7fa); }
    .stp-tab { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 0 18px; border-radius: 14px; color: var(--text-sub); font-size: 14px; font-weight: 700; text-decoration: none; transition: .18s; }
    .stp-tab:hover { color: var(--text-main); text-decoration: none; background: rgba(var(--tp-rgb),.06); }
    .stp-tab.is-active { background: #fff; color: var(--theme-primary); box-shadow: 0 10px 20px rgba(15,23,42,.08); }
    .stp-metrics { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: 16px; }
    .stp-metric { padding: 22px; }
    .stp-metric-label { color: var(--text-sub); font-size: 13px; font-weight: 600; }
    .stp-metric-value { margin-top: 10px; color: var(--text-main); font-size: 26px; font-weight: 800; line-height: 1.25; }
    .stp-metric-note { margin-top: 8px; color: var(--text-sub); font-size: 12px; line-height: 1.7; }
    .stp-notice { padding: 22px 24px; display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
    .stp-notice strong { display: block; margin-bottom: 8px; color: var(--text-main); font-size: 16px; font-weight: 800; }
    .stp-notice p { margin: 0; color: var(--text-sub); font-size: 13px; line-height: 1.85; }
    .stp-badge { display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 999px; background: rgba(var(--tp-rgb),.08); color: var(--theme-primary); font-size: 12px; font-weight: 700; white-space: nowrap; }
    .stp-toolbar { padding: 18px 20px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
    .stp-tool-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 42px; padding: 0 16px; border: 1px solid var(--card-border); border-radius: 14px; background: #fff; color: var(--text-main); font-size: 14px; font-weight: 700; cursor: pointer; transition: .18s; }
    .stp-tool-btn:hover { transform: translateY(-2px); border-color: rgba(var(--tp-rgb),.28); box-shadow: 0 14px 26px rgba(15,23,42,.08); }
    .stp-tool-btn.is-primary { background: linear-gradient(135deg,var(--tp-dark) 0%,var(--tp-light) 100%); border-color: transparent; color: #fff; }
    .stp-data { padding: 18px 18px 8px; }
    .stp-data-head { display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 16px; padding: 0 6px; }
    .stp-data-title { margin: 0; color: var(--text-main); font-size: 18px; font-weight: 800; }
    .stp-data-desc { margin: 8px 0 0; color: var(--text-sub); font-size: 12px; line-height: 1.8; }
    .stp-table-wrap .layui-table-view { margin: 0; border-radius: 10px; overflow: hidden; border-color: var(--card-border); }
    .stp-table-wrap .layui-table-header th { background: rgba(var(--tp-rgb),.06); color: var(--text-main); font-weight: 700; border-color: var(--card-border); }
    .stp-table-wrap .layui-table-body tr:hover td { background: rgba(var(--tp-rgb),.03); }
    .stp-table-wrap .layui-table-header { overflow-x: auto !important; scrollbar-width: none; }
    .stp-table-wrap .layui-table-header::-webkit-scrollbar { display: none; }
    .stp-table-wrap .layui-table th:last-child,
    .stp-table-wrap .layui-table td:last-child { position: sticky !important; right: 0; z-index: 2; background: #fff; box-shadow: -2px 0 4px rgba(0,0,0,.04); }
    .stp-table-wrap .layui-table-header th:last-child { background: rgba(var(--tp-rgb),.06); }
    .stp-cover { display: inline-flex; align-items: center; justify-content: center; width: 52px; height: 52px; border-radius: 14px; overflow: hidden; background: linear-gradient(135deg, rgba(var(--tp-rgb),.08), rgba(var(--tp-rgb),.12)); border: 1px solid rgba(var(--tp-rgb),.1); cursor: pointer; }
    .stp-cover .stp-cover-fallback { font-size: 24px; color: rgba(var(--tp-rgb),.4); }
    .stp-cover img { width: 100%; height: 100%; object-fit: cover; }
    .stp-name-cell { display: flex; flex-direction: column; gap: 4px; padding: 4px 0; }
    .stp-name-line { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; min-width: 0; }
    .stp-name-main { color: var(--text-main); font-size: 14px; font-weight: 700; line-height: 1.7; }
    .stp-name-sub { color: var(--text-sub); font-size: 12px; line-height: 1.7; }
    .stp-license-tag { display: inline-flex; align-items: center; gap: 4px; padding: 3px 7px; border-radius: 999px; font-size: 11px; font-weight: 700; line-height: 1.35; white-space: nowrap; }
    .stp-license-tag.is-unauthorized { background: rgba(245,158,11,.12); color: #b45309; }
    .stp-license-tag.is-expired,.stp-license-tag.is-tampered { background: rgba(239,68,68,.12); color: #dc2626; }
    .stp-license-tag.is-blocked { background: rgba(51,51,51,.10); color: #333; }
    .stp-license-tag.is-trial { background: rgba(var(--tp-rgb),.10); color: var(--theme-primary); }
    .stp-license-tag.is-valid { background: rgba(22,163,74,.10); color: #16a34a; }
    .stp-ver { display: inline-flex; align-items: center; justify-content: center; min-width: 70px; padding: 6px 12px; border-radius: 999px; background: rgba(var(--tp-rgb),.1); color: var(--theme-primary); font-size: 12px; font-weight: 700; }
    .stp-op-group { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; }
    .stp-op-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-width: 78px; height: 34px; padding: 0 14px; border-radius: 12px; border: 1px solid rgba(var(--tp-rgb),.16); background: rgba(var(--tp-rgb),.06); color: var(--theme-primary); font-size: 12px; font-weight: 700; cursor: pointer; }
    .stp-op-btn.is-update { background: rgba(var(--tp-rgb),.08); border-color: rgba(var(--tp-rgb),.14); color: var(--theme-primary); }
    .stp-col-empty { display: inline-flex; align-items: center; justify-content: center; min-width: 52px; color: var(--text-muted); font-size: 12px; font-weight: 600; }
    .stp-mobile-list { display: none; gap: 14px; margin-top: 2px; }
    .stp-mobile-card { padding: 18px; border: 1px solid var(--card-border); border-radius: 10px; background: linear-gradient(180deg,rgba(255,255,255,.98),rgba(245,248,255,.96)); }
    .stp-mobile-card.is-locked { border-color: rgba(239,68,68,.22); background: linear-gradient(180deg,rgba(255,255,255,.98),rgba(254,242,242,.88)); }
    .stp-mobile-head { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 14px; }
    .stp-mobile-head .stp-cover { flex-shrink: 0; width: 60px; height: 60px; }
    .stp-mobile-info { flex: 1; min-width: 0; }
    .stp-mobile-title { margin: 0; color: var(--text-main); font-size: 15px; font-weight: 800; line-height: 1.7; }
    .stp-mobile-sub { color: var(--text-sub); font-size: 12px; line-height: 1.7; }
    .stp-mobile-license { margin-top: 6px; }
    .stp-mobile-switches { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 10px; margin-bottom: 14px; }
    .stp-mobile-sw { padding: 12px 14px; border-radius: 10px; border: 1px solid rgba(var(--tp-rgb),.08); background: rgba(var(--tp-rgb),.04); }
    .stp-mobile-sw-label { display: block; color: var(--text-sub); font-size: 11px; font-weight: 600; margin-bottom: 10px; }
    .stp-mobile-actions { display: flex; flex-wrap: wrap; gap: 10px; }
    .stp-empty { padding: 36px 20px; text-align: center; color: var(--text-sub); font-size: 13px; }
    @media(max-width:1100px){
        .stp-hero-inner { grid-template-columns: 1fr; }
        .stp-actions { justify-content: flex-start; }
        .stp-metrics { grid-template-columns: repeat(2,minmax(0,1fr)); }
    }
    @media(max-width:768px){
        .stp-hero { border-radius: 10px; padding: 24px 20px; }
        .stp-title { font-size: 26px; }
        .stp-actions { display: grid; grid-template-columns: 1fr 1fr; width: 100%; }
        .stp-btn { width: 100%; min-width: 0; }
        .stp-tabs-bar { display: grid; grid-template-columns: 1fr; }
        .stp-tabs-group { width: 100%; }
        .stp-tab { flex: 1; }
        .stp-toolbar { flex-direction: column; align-items: stretch; }
        .stp-tool-btn { width: 100%; }
        .stp-table-wrap { display: none; }
        .stp-mobile-list { display: grid; }
    }
    @media(max-width:560px){
        .stp-metrics,.stp-mobile-switches,.stp-actions { grid-template-columns: 1fr; }
        .stp-notice,.stp-data-head { flex-direction: column; align-items: flex-start; }
    }
</style>

<main class="station-tpl-page">
    <div class="stp-page-header">
        <a href="?action=setting" class="stp-back-btn"><i class="fa fa-arrow-left"></i></a>
        <div>
            <h1 class="stp-page-title">模板配置</h1>
            <p class="stp-page-desc">管理分店前台模板、用户后台模板与底部导航</p>
        </div>
    </div>
    <section class="stp-card stp-data">
        <div class="stp-tabs-bar">
            <div class="stp-tabs-group" id="tplScopeTabs">
                <a href="javascript:;" class="stp-tab is-active" data-scope="front">前台模板</a>
                <a href="javascript:;" class="stp-tab" data-scope="user_tpl">用户后台模板</a>
                <a href="javascript:;" class="stp-tab" data-scope="bottom_nav">底部导航</a>
            </div>
            <span class="stp-badge" id="tplScopeBadge"><i class="fa fa-info-circle"></i> 前台模板至少保留一套启用，请直接切换其他模板</span>
        </div>
        <div class="stp-data-head">
            <div>
                <h2 class="stp-data-title" id="tplScopeTitle">前台模板列表</h2>
                <p class="stp-data-desc" id="tplScopeDesc">用于分站首页与商品前台，电脑端和手机端可分别切换模板，但不支持完全关闭。</p>
            </div>
        </div>
        <div class="stp-table-wrap">
            <table class="layui-hide" id="index" lay-filter="index"></table>
        </div>
        <div class="stp-mobile-list" id="tplMobileList"></div>
    </section>
</main>

<script>
    layui.use(['table', 'form', 'layer'], function(){
        var table = layui.table;
        var form = layui.form;
        var layer = layui.layer;
        var tableId = 'stationTplTable';
        var currentScope = 'front';
        var nullImg = '<?= DC_URL ?>admin/views/images/null.png';
        function coverHtml(preview, evtAttr) {
            if (preview && preview.indexOf('null.png') === -1 && preview.indexOf('theme.png') === -1) {
                return '<img onerror="this.onerror=null;this.parentNode.innerHTML=\'<i class=fa fa-picture-o stp-cover-fallback></i>\'" src="' + safeText(preview) + '" />';
            }
            return '<i class="fa fa-picture-o stp-cover-fallback"></i>';
        }
        var scopeMap = {
            front: {
                title: '前台模板列表',
                desc: '用于分站首页与商品前台，电脑端和手机端可分别切换模板，但不支持完全关闭。',
                badge: '前台模板至少保留一套启用，请直接切换其他模板',
                listUrl: '?action=get_tpl',
                switchUrl: '?action=use',
                telSwitchUrl: '?action=use_tel',
                settingKind: '',
                canTel: true,
                allowUpdate: true,
                emptyText: '暂无可管理的分店前台模板'
            },
            user_tpl: {
                title: '用户后台模板列表',
                desc: '用于分站域名下的用户后台界面，电脑端和手机端可分别切换模板；未单独设置时默认启用 default 模板，且不支持完全关闭。',
                badge: '用户后台模板至少保留一套启用，请直接切换其他模板',
                listUrl: '?action=get_user_tpl',
                switchUrl: '?action=use_user_tpl',
                telSwitchUrl: '?action=use_user_tpl_tel',
                settingKind: 'user_tpl',
                canTel: true,
                allowUpdate: false,
                emptyText: '暂无可管理的用户后台模板'
            },
            bottom_nav: {
                title: '底部导航模板列表',
                desc: '用于分站前台底部导航展示，未单独设置时默认启用 default 模板。',
                badge: '未单独启用时自动使用 default 模板',
                listUrl: '?action=get_bottom_nav_tpl',
                switchUrl: '?action=use_bottom_nav',
                telSwitchUrl: '',
                settingKind: 'bottom_nav',
                canTel: false,
                allowUpdate: false,
                emptyText: '暂无可管理的底部导航模板'
            }
        };

        function safeText(v){ return $('<div>').text(v == null ? '' : String(v)).html(); }

        function getScopeConfig(){
            return scopeMap[currentScope] || scopeMap.front;
        }

        function getSettingUrl(tplFile){
            var cfg = getScopeConfig();
            var url = '<?= DC_URL ?>user/template.php?action=setting_page&tpl=' + encodeURIComponent(tplFile);
            if(cfg.settingKind){
                url += '&kind=' + encodeURIComponent(cfg.settingKind);
            }
            return url;
        }

        function licenseInvalidStatus(status){
            return $.inArray(String(status || ''), ['expired', 'blocked', 'unauthorized', 'tampered']) !== -1;
        }

        function licenseMessage(status, customMsg){
            if(customMsg){ return customMsg; }
            status = String(status || '');
            if(status === 'unauthorized') return '模板未授权，请联系站长';
            if(status === 'expired') return '模板已到期，请联系站长续期';
            if(status === 'blocked') return '模板已被禁用，请联系站长';
            if(status === 'tampered') return '模板授权异常，请联系站长';
            return '模板授权状态异常，请联系站长';
        }

        function licenseBadge(d){
            d = d || {};
            var status = String(d.license_status || '');
            var buyType = String(d.buy_type || '');
            var cls = '', text = '', icon = 'fa-lock';
            if(status === 'blocked'){ cls = 'is-blocked'; text = '已禁用'; icon = 'fa-ban'; }
            else if(status === 'unauthorized'){ cls = 'is-unauthorized'; text = '未授权'; icon = 'fa-lock'; }
            else if(status === 'expired'){ cls = 'is-expired'; text = '已到期'; icon = 'fa-exclamation-circle'; }
            else if(status === 'tampered'){ cls = 'is-tampered'; text = '授权异常'; icon = 'fa-warning'; }
            else if(status === 'trial' || buyType === 'trial'){ cls = 'is-trial'; text = '试用中'; icon = 'fa-gift'; }
            else if(buyType === 'permanent'){ cls = 'is-valid'; text = '已买断'; icon = 'fa-check-circle'; }
            else if(buyType === 'monthly' && d.expire_time){ cls = 'is-unauthorized'; text = '到期 ' + String(d.expire_time).split(' ')[0]; icon = 'fa-clock-o'; }
            if(!text){ return ''; }
            return '<span class="stp-license-tag ' + cls + '"><i class="fa ' + icon + '"></i>' + safeText(text) + '</span>';
        }

        function guardLicenseOpen(status, msg){
            if(!licenseInvalidStatus(status)){
                return true;
            }
            layer.msg(licenseMessage(status, msg));
            renderTable();
            return false;
        }

        function switchHtml(name, filterName, checked, data){
            data = data || {};
            return '<input type="checkbox" name="' + safeText(name) + '" value="' + safeText(name) + '" title=" ON |OFF " lay-skin="switch" lay-filter="' + filterName + '" data-license-status="' + safeText(data.license_status || '') + '" data-license-msg="' + safeText(data.license_msg || '') + '" ' + (checked ? 'checked' : '') + '>';
        }

        function emptyColHtml(text){
            return '<span class="stp-col-empty">' + safeText(text || '--') + '</span>';
        }

        function renderScopeMeta(){
            var cfg = getScopeConfig();
            $('#tplScopeTitle').text(cfg.title);
            $('#tplScopeDesc').text(cfg.desc);
            $('#tplScopeBadge').html('<i class="fa fa-info-circle"></i> ' + safeText(cfg.badge));
        }

        function getCols(){
            var cfg = getScopeConfig();
            var cols = [
                {field:'preview', title:'预览', width:90, align:'center', templet:function(d){ return '<a href="javascript:;" class="stp-cover" lay-event="img">' + coverHtml(d.preview) + '</a>'; }},
                {field:'tplname', title:'模板信息', minWidth:200, templet:function(d){ return '<div class="stp-name-cell"><div class="stp-name-line"><span class="stp-name-main">' + safeText(d.tplname) + '</span>' + licenseBadge(d) + '</div><div class="stp-name-sub">' + safeText(d.tplfile) + '</div></div>'; }},
                {field:'version', title:'版本', width:120, align:'center', templet:function(d){ return '<span class="stp-ver">v' + safeText(d.version) + '</span>'; }}
            ];
            if(cfg.canTel){
                cols.push({field:'switch', title:'电脑端', align:'center', width:140, templet:function(d){ return switchHtml(d.tplfile, 'switch', d.switch === 'y', d); }});
                cols.push({field:'tel_switch', title:'手机端', align:'center', width:140, templet:function(d){ return switchHtml(d.tplfile, 'tel_switch', d.tel_switch === 'y', d); }});
            } else {
                cols.push({field:'switch', title:'启用', align:'center', width:140, templet:function(d){ return switchHtml(d.tplfile, 'switch', d.switch === 'y', d); }});
            }
            cols.push({title:'操作', width:200, align:'center', templet:function(d){
                var html = '<div class="stp-op-group">';
                if(cfg.allowUpdate && d.update === 'y'){
                    html += '<button type="button" class="stp-op-btn is-update" lay-event="update"><i class="fa fa-cloud-download"></i> 更新</button>';
                }
                if(d.has_setting === 'y'){
                    html += '<button type="button" class="stp-op-btn" lay-event="setting"><i class="fa fa-sliders"></i> 配置</button>';
                }
                html += '</div>';
                if(html === '<div class="stp-op-group"></div>'){
                    return emptyColHtml('暂无');
                }
                return html;
            }});
            return [cols];
        }

        function renderMobileList(list){
            var cfg = getScopeConfig();
            var $el = $('#tplMobileList');
            if(!list.length){ $el.html('<div class="stp-empty">' + safeText(cfg.emptyText) + '</div>'); return; }
            var html = list.map(function(d){
                var switchesHtml = cfg.canTel
                    ? '<div class="stp-mobile-switches">' +
                        '<div class="stp-mobile-sw"><span class="stp-mobile-sw-label">电脑端</span>' + switchHtml(d.tplfile, 'switch', d.switch === 'y', d) + '</div>' +
                        '<div class="stp-mobile-sw"><span class="stp-mobile-sw-label">手机端</span>' + switchHtml(d.tplfile, 'tel_switch', d.tel_switch === 'y', d) + '</div>' +
                      '</div>'
                    : '<div class="stp-mobile-switches" style="grid-template-columns:1fr;">' +
                        '<div class="stp-mobile-sw"><span class="stp-mobile-sw-label">启用状态</span>' + switchHtml(d.tplfile, 'switch', d.switch === 'y', d) + '</div>' +
                      '</div>';
                var actionsHtml = '';
                if(cfg.allowUpdate && d.update === 'y'){
                    actionsHtml += '<button type="button" class="stp-op-btn is-update" data-action="update" data-tpl="' + safeText(d.tplfile) + '"><i class="fa fa-cloud-download"></i> 更新</button>';
                }
                if(d.has_setting === 'y'){
                    actionsHtml += '<button type="button" class="stp-op-btn" data-action="setting" data-tpl="' + safeText(d.tplfile) + '"><i class="fa fa-sliders"></i> 配置</button>';
                }
                return '<article class="stp-mobile-card' + ((d.license_invalid === 'y') ? ' is-locked' : '') + '">' +
                    '<div class="stp-mobile-head">' +
                        '<a href="javascript:;" class="stp-cover" data-action="img" data-preview="' + safeText(d.preview) + '" data-name="' + safeText(d.tplname) + '">' + coverHtml(d.preview) + '</a>' +
                        '<div class="stp-mobile-info">' +
                            '<h3 class="stp-mobile-title">' + safeText(d.tplname) + '</h3>' +
                            '<div class="stp-mobile-sub">' + safeText(d.tplfile) + ' &middot; v' + safeText(d.version) + '</div>' +
                            '<div class="stp-mobile-license">' + licenseBadge(d) + '</div>' +
                        '</div>' +
                    '</div>' +
                    switchesHtml +
                    '<div class="stp-mobile-actions">' +
                        actionsHtml +
                    '</div>' +
                '</article>';
            }).join('');
            $el.html(html);
            form.render('checkbox');
        }

        function renderTable(){
            var cfg = getScopeConfig();
            renderScopeMeta();
            $('.stp-table-wrap').html('<table class="layui-hide" id="index" lay-filter="index"></table>');
            table.render({
                elem: '#index',
                id: tableId,
                autoSort: false,
                url: cfg.listUrl,
                limits: [10,20,30,50,100],
                page: false,
                lineStyle: 'height: 64px;',
                defaultToolbar: ['filter','exports'],
                cols: getCols(),
                done: function(res){
                    renderMobileList(res.data || []);
                    form.render('checkbox');
                },
                error: function(res, msg){
                    console.log(res, msg);
                }
            });
        }

        renderTable();

        $('#tplScopeTabs').on('click', '.stp-tab', function(){
            var scope = $(this).data('scope');
            if(!scopeMap[scope] || scope === currentScope){
                return;
            }
            currentScope = scope;
            $('#tplScopeTabs .stp-tab').removeClass('is-active');
            $(this).addClass('is-active');
            renderTable();
        });

        form.on('switch(switch)', function(obj){
            var cfg = getScopeConfig();
            var active = obj.elem.checked ? 1 : 0;
            if(!active && currentScope !== 'bottom_nav'){
                layer.msg('当前模板类型只支持切换，不支持完全关闭');
                renderTable();
                return;
            }
            var tpl = active ? this.name : 'em_null_tpl';
            var loadIdx = layer.load(2);
            $.ajax({
                url: cfg.switchUrl, type: 'POST', dataType: 'json',
                data: { tpl: tpl, status: active, token: '<?= LoginAuth::genToken() ?>' },
                success: function(e){
                    if(e.code == 400){ layer.msg(e.msg); renderTable(); return; }
                    layer.msg('模板配置已更新');
                    renderTable();
                },
                error: function(err){ layer.msg(err.responseJSON && err.responseJSON.msg ? err.responseJSON.msg : '请求失败'); },
                complete: function(){ layer.close(loadIdx); }
            });
        });

        form.on('switch(tel_switch)', function(obj){
            var cfg = getScopeConfig();
            if(!cfg.canTel || !cfg.telSwitchUrl){
                renderTable();
                return;
            }
            var active = obj.elem.checked ? 1 : 0;
            if(!active){
                layer.msg('当前模板类型只支持切换，不支持完全关闭');
                renderTable();
                return;
            }
            var tpl = active ? this.name : 'em_null_tpl';
            var loadIdx = layer.load(2);
            $.ajax({
                url: cfg.telSwitchUrl, type: 'POST', dataType: 'json',
                data: { tpl: tpl, status: active, token: '<?= LoginAuth::genToken() ?>' },
                success: function(e){
                    if(e.code == 400){ layer.msg(e.msg); renderTable(); return; }
                    layer.msg('模板配置已更新');
                    renderTable();
                },
                error: function(err){ layer.msg(err.responseJSON && err.responseJSON.msg ? err.responseJSON.msg : '请求失败'); },
                complete: function(){ layer.close(loadIdx); }
            });
        });

        table.on('tool(index)', function(obj){
            var data = obj.data;
            if(obj.event === 'img'){
                var src = (data.preview && data.preview.indexOf('null.png') === -1 && data.preview.indexOf('theme.png') === -1) ? data.preview : nullImg;
                layer.photos({ photos: { title: data.tplname, start: 0, data: [{ alt: data.tplname, pid: 1, src: src }] } });
            }
            if(obj.event === 'update'){
                var cfg = getScopeConfig();
                if(!cfg.allowUpdate){
                    return;
                }
                var loadIdx = layer.load(2);
                $.ajax({
                    url: '?action=upgrade', type: 'POST', dataType: 'json',
                    data: { alias: data.tplfile, token: '<?= LoginAuth::genToken() ?>' },
                    success: function(e){
                        if(e.code == 400) return layer.msg(e.msg);
                        layer.msg('模板已升级');
                        renderTable();
                    },
                    error: function(err){ layer.msg(err.responseJSON && err.responseJSON.msg ? err.responseJSON.msg : '请求失败'); },
                    complete: function(){ layer.close(loadIdx); }
                });
            }
            if(obj.event === 'setting'){
                var isMobile = window.innerWidth < 1200;
                layer.open({
                    id: 'setting',
                    title: '模板配置',
                    type: 2,
                    area: isMobile ? ['100%', '100%'] : ['1000px', '80%'],
                    skin: 'layui-layer-molv',
                    content: getSettingUrl(data.tplfile),
                    fixed: false, scrollbar: false, maxmin: true, shadeClose: true,
                    success: function(layero, index){ if(currentScope === 'bottom_nav') layer.full(index); }
                });
            }
        });


        $('#tplMobileList').on('click', '[data-action="img"]', function(){
            var $t = $(this);
            var src = $t.data('preview');
            src = (src && src.indexOf('null.png') === -1 && src.indexOf('theme.png') === -1) ? src : nullImg;
            layer.photos({ photos: { title: $t.data('name'), start: 0, data: [{ alt: $t.data('name'), pid: 1, src: src }] } });
        });

        $('#tplMobileList').on('click', '[data-action="update"]', function(){
            var cfg = getScopeConfig();
            if(!cfg.allowUpdate){
                return;
            }
            var tplFile = $(this).data('tpl');
            var loadIdx = layer.load(2);
            $.ajax({
                url: '?action=upgrade', type: 'POST', dataType: 'json',
                data: { alias: tplFile, token: '<?= LoginAuth::genToken() ?>' },
                success: function(e){
                    if(e.code == 400) return layer.msg(e.msg);
                    layer.msg('模板已升级');
                    renderTable();
                },
                error: function(err){ layer.msg(err.responseJSON && err.responseJSON.msg ? err.responseJSON.msg : '请求失败'); },
                complete: function(){ layer.close(loadIdx); }
            });
        });

        $('#tplMobileList').on('click', '[data-action="setting"]', function(){
            var tplFile = $(this).data('tpl');
            var isMobile = window.innerWidth < 1200;
            layer.open({
                id: 'setting',
                title: '模板配置',
                type: 2,
                area: isMobile ? ['100%', '100%'] : ['1000px', '80%'],
                skin: 'layui-layer-molv',
                content: getSettingUrl(tplFile),
                fixed: false, scrollbar: false, maxmin: true, shadeClose: true,
                success: function(layero, index){ if(currentScope === 'bottom_nav') layer.full(index); }
            });
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

