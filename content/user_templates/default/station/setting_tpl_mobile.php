<?php
defined('DC_ROOT') || exit('access denied!');
?>
<style>
    .uc-site-footer{display:none!important}
    .station-tpl-page,.station-tpl-page *{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
    .station-tpl-page{--stp-primary:var(--theme-primary,#667eea);--stp-primary-rgb:var(--tp-rgb,102,126,234);--stp-soft:rgba(var(--stp-primary-rgb),.10);min-height:100vh;padding:12px 12px calc(28px + env(safe-area-inset-bottom,0px));background:#f5f6f8;color:#20242c;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif}
    .stp-tabs-wrap{position:sticky;top:calc(50px + env(safe-area-inset-top,0px));z-index:10;margin:-10px -12px 12px;padding:10px 12px 8px;background:rgba(245,245,246,.96);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px)}
    .stp-tabs-group{position:relative;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:7px;padding:4px;border-radius:10px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;box-shadow:var(--shadow-primary)}
    .stp-tab{position:relative;z-index:1;height:34px;border-radius:999px;display:flex;align-items:center;justify-content:center;color:#697180;background:transparent;font-size:12px;font-weight:900;text-decoration:none;white-space:nowrap}
    .stp-tab.is-active{background:var(--stp-soft);color:var(--stp-primary);box-shadow:none; border-radius: 10px;}.stp-tab-indicator{position:absolute;left:0;bottom:4px;width:24px;height:3px;border-radius:999px;background:var(--stp-primary);z-index:2;pointer-events:none;will-change:left,width}
    .stp-scope-card{margin-bottom:12px;padding:16px;border-radius:10px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;box-shadow:var(--shadow-primary)}
    .stp-data-title{margin:0;color:#20242c;font-size:14px;font-weight:900;line-height:1.2}.stp-data-desc{margin:4px 0 0;color:#8b95a5;font-size:12px;line-height:1.6}.stp-badge{display:inline-flex;margin-top:10px;align-items:center;gap:6px;padding:8px 10px;border-radius:999px;background:var(--stp-soft);color:var(--stp-primary);font-size:11px;font-weight:900}
    .stp-table-wrap{display:none}.stp-mobile-list{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;touch-action:pan-y;padding-bottom:8px}.stp-empty{grid-column:1/-1;padding:30px 16px;border-radius:10px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;text-align:center;color:#8b95a5;font-size:12px;box-shadow:var(--shadow-primary)}
    .stp-mobile-card{position:relative;overflow:hidden;border-radius:10px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;box-shadow:var(--shadow-primary);transition:border-color .18s ease,box-shadow .18s ease,transform .18s ease}.stp-mobile-card:before{content:'';position:absolute;left:0;right:0;top:0;height:3px;background:linear-gradient(90deg,var(--stp-primary),var(--theme-secondary,#764ba2));opacity:0;z-index:2}.stp-mobile-card.is-current{border-color:rgba(var(--stp-primary-rgb),.20);box-shadow:var(--shadow-primary)}.stp-mobile-card.is-current:before{opacity:1}
    .stp-preview-wrap{position:relative;overflow:hidden;background:linear-gradient(135deg,#eef2ff,#f8fafc)}.stp-cover{display:flex;align-items:center;justify-content:center;overflow:hidden;text-decoration:none;color:#b0b8c4}.stp-cover img{width:100%;height:100%;object-fit:cover;display:block}.stp-mobile-preview{position:relative;width:100%;aspect-ratio:1/1;height:auto;background:linear-gradient(135deg,#eef2ff,#f8fafc)}.stp-cover-fallback{font-size:30px;color:#c1c9d5}
    .stp-mobile-body{padding:10px}.stp-mobile-head{display:flex;align-items:flex-start;gap:7px;margin-bottom:8px}.stp-mobile-info{min-width:0;max-width:100%;flex:1}.stp-mobile-title{display:block;margin:0;color:#20242c;font-size:13px;font-weight:900;line-height:1.35;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.stp-mobile-meta{display:flex;align-items:center;gap:6px;margin-top:5px;min-width:0}.stp-mobile-sub{display:flex;align-items:center;gap:4px;min-width:0;flex:1;color:#8b95a5;font-size:11px;line-height:1.35;overflow:hidden}.stp-mobile-sub i{flex:0 0 auto}.stp-mobile-path{display:block;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.stp-version{flex:0 0 auto;display:inline-flex;align-items:center;justify-content:center;height:22px;padding:0 7px;border-radius:999px;background:var(--stp-soft);color:var(--stp-primary);font-size:10px;font-weight:900}.stp-license-row{display:flex;flex-wrap:wrap;gap:5px;margin-top:7px}.stp-license-tag{display:inline-flex;align-items:center;gap:4px;height:21px;padding:0 7px;border-radius:999px;font-size:10px;font-weight:900;line-height:1;white-space:nowrap}.stp-license-tag.is-unauthorized{background:rgba(245,158,11,.13);color:#b45309}.stp-license-tag.is-expired,.stp-license-tag.is-tampered{background:rgba(239,68,68,.12);color:#dc2626}.stp-license-tag.is-blocked{background:rgba(51,51,51,.10);color:#333}.stp-license-tag.is-trial{background:var(--stp-soft);color:var(--stp-primary)}.stp-license-tag.is-valid{background:rgba(22,163,74,.10);color:#16a34a}.stp-mobile-card.is-locked{border-color:rgba(239,68,68,.25);background:linear-gradient(0deg,#fff,#fff7f7)}
    .stp-preview-switches{display:flex;align-items:center;gap:7px;margin:0 0 8px}.stp-preview-toggle{flex:1;min-width:0;height:32px;padding:0 7px;border:1px solid rgba(var(--stp-primary-rgb),.10);border-radius:12px;background:#f8f9fb;color:#7c8797;display:flex;align-items:center;justify-content:space-between;gap:5px;font-size:11px;font-weight:900;white-space:nowrap}.stp-preview-toggle-text{min-width:0;overflow:hidden;text-overflow:ellipsis}.stp-preview-toggle-track{position:relative;flex:0 0 auto;width:26px;height:15px;border-radius:999px;background:#d7dde8}.stp-preview-toggle-track:after{content:'';position:absolute;left:2px;top:2px;width:11px;height:11px;border-radius:999px;background:#fff;box-shadow:0 1px 4px rgba(15,23,42,.18);transition:left .18s ease}.stp-preview-toggle.is-on{background:var(--stp-soft);border-color:rgba(var(--stp-primary-rgb),.18);color:var(--stp-primary)}.stp-preview-toggle.is-on .stp-preview-toggle-track{background:var(--stp-primary)}.stp-preview-toggle.is-on .stp-preview-toggle-track:after{left:13px}.stp-preview-toggle:disabled{opacity:1}.layui-form-switch{box-sizing:content-box}.layui-form-onswitch{border-color:var(--stp-primary)!important;background-color:var(--stp-primary)!important}
    .stp-mobile-actions{display:grid;grid-template-columns:1fr;gap:7px;margin-top:2px}.stp-mobile-actions.is-empty{display:block;margin-top:0}.stp-op-group{display:flex;flex-wrap:wrap;gap:8px;justify-content:center}.stp-op-btn{display:inline-flex;align-items:center;justify-content:center;gap:5px;width:100%;height:34px;padding:0 10px;border:0;border-radius:13px;background:var(--stp-soft);color:var(--stp-primary);font-size:12px;font-weight:900;text-decoration:none}.stp-op-btn.is-update{background:#ecfdf5;color:#047857}.stp-action-empty{display:flex;align-items:center;justify-content:center;height:32px;border-radius:12px;background:#f8f9fb;color:#a0a8b4;font-size:11px;font-weight:800}.stp-col-empty{display:inline-flex;align-items:center;justify-content:center;min-width:52px;color:#8b95a5;font-size:12px;font-weight:600}
    @media(max-width:360px){.station-tpl-page{padding-left:10px;padding-right:10px}.stp-tab{font-size:11px}.stp-mobile-list{gap:8px}.stp-mobile-card{position:relative;overflow:hidden;border-radius:10px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;box-shadow:var(--shadow-primary);transition:border-color .18s ease,box-shadow .18s ease,transform .18s ease}.stp-mobile-body{padding:9px}.stp-preview-switches{gap:5px;margin-bottom:7px}.stp-preview-toggle{height:30px;padding:0 5px;font-size:10px}.stp-preview-toggle-track{width:24px;height:14px}.stp-preview-toggle-track:after{width:10px;height:10px}.stp-preview-toggle.is-on .stp-preview-toggle-track:after{left:12px}.stp-version{font-size:9px;padding:0 6px}}
</style>

<main class="station-tpl-page">
<div class="stp-tabs-wrap">
        <div class="stp-tabs-group" id="tplScopeTabs">
            <a href="javascript:;" class="stp-tab is-active" data-scope="front">前台模板</a>
            <a href="javascript:;" class="stp-tab" data-scope="user_tpl">用户后台模板</a>
            <a href="javascript:;" class="stp-tab" data-scope="bottom_nav">底部导航</a>
            <div class="stp-tab-indicator" id="stpTabIndicator"></div>
        </div>
    </div>

    <section class="stp-scope-card">
        <h2 class="stp-data-title" id="tplScopeTitle">前台模板列表</h2>
        <p class="stp-data-desc" id="tplScopeDesc">用于分站首页与商品前台，电脑端和手机端可分别切换模板，但不支持完全关闭。</p>
        <span class="stp-badge" id="tplScopeBadge"><i class="fa fa-info-circle"></i> 前台模板至少保留一套启用，请直接切换其他模板</span>
    </section>

    <div class="stp-table-wrap">
        <table class="layui-hide" id="index" lay-filter="index"></table>
    </div>
    <div class="stp-mobile-list" id="tplMobileList"></div>
</main>

<script>
    layui.use(['table', 'form', 'layer'], function(){
        var table = layui.table;
        var form = layui.form;
        var layer = layui.layer;
        var tableId = 'stationTplTable';
        var currentScope = 'front';
        var stpScopeNames = ['front', 'user_tpl', 'bottom_nav'];
        var stpIndicatorTimer = null;
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

        function moveStpTabIndicator($tab, animate) {
            var $indicator = $('#stpTabIndicator');
            if (!$tab.length || !$indicator.length) return;
            var tabLeft = $tab.position().left;
            var tabWidth = $tab.outerWidth();
            var indicatorW = 24;
            var targetLeft = tabLeft + (tabWidth - indicatorW) / 2;
            if (!animate || !$indicator.data('inited')) {
                $indicator.css({ left: targetLeft + 'px', width: indicatorW + 'px', transition: 'none' });
                $indicator.data('inited', true);
                return;
            }
            var curLeft = parseFloat($indicator.css('left')) || 0;
            var stretchLeft = targetLeft > curLeft ? curLeft : targetLeft;
            var stretchWidth = Math.abs(targetLeft - curLeft) + indicatorW;
            if (stpIndicatorTimer) clearTimeout(stpIndicatorTimer);
            $indicator.css({
                left: stretchLeft + 'px',
                width: stretchWidth + 'px',
                transition: 'left .16s cubic-bezier(.4,0,.2,1), width .16s cubic-bezier(.4,0,.2,1)'
            });
            stpIndicatorTimer = setTimeout(function(){
                $indicator.css({
                    left: targetLeft + 'px',
                    width: indicatorW + 'px',
                    transition: 'left .13s cubic-bezier(.4,0,.2,1), width .13s cubic-bezier(.4,0,.2,1)'
                });
            }, 200);
        }

        function setTplScope(scope, animate) {
            if (!scopeMap[scope]) scope = stpScopeNames[0] || 'front';
            currentScope = scope;
            $('#tplScopeTabs .stp-tab').removeClass('is-active');
            $('#tplScopeTabs .stp-tab[data-scope="' + scope + '"]').addClass('is-active');
            moveStpTabIndicator($('#tplScopeTabs .stp-tab[data-scope="' + scope + '"]'), animate !== false);
            renderTable();
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

        function mobileUseBtn(tplFile, filterName, isOn, onText, offText, allowOff, data) {
            data = data || {};
            var canClick = !isOn || allowOff;
            var cls = isOn ? 'is-on' : 'is-off';
            var active = isOn ? (allowOff ? 0 : 1) : 1;
            return '<button type="button" class="stp-preview-toggle ' + cls + '" data-action="mobile-switch" data-filter="' + filterName + '" data-tpl="' + safeText(tplFile) + '" data-active="' + active + '" data-license-status="' + safeText(data.license_status || '') + '" data-license-msg="' + safeText(data.license_msg || '') + '" aria-pressed="' + (isOn ? 'true' : 'false') + '"' + (canClick ? '' : ' disabled') + '><span class="stp-preview-toggle-text">' + safeText(isOn ? onText : offText) + '</span><span class="stp-preview-toggle-track"></span></button>';
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
                {field:'tplname', title:'模板信息', minWidth:200, templet:function(d){ return '<div class="stp-name-cell"><div class="stp-name-main">' + safeText(d.tplname) + licenseBadge(d) + '</div><div class="stp-name-sub">' + safeText(d.tplfile) + '</div></div>'; }},
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
                var pcOn = d.switch === 'y';
                var telOn = d.tel_switch === 'y';
                var activeClass = (pcOn || (cfg.canTel && telOn)) ? ' is-current' : '';
                var switchesHtml = cfg.canTel
                    ? '<div class="stp-preview-switches">' +
                        mobileUseBtn(d.tplfile, 'switch', pcOn, '电脑端', '电脑端', false, d) +
                        mobileUseBtn(d.tplfile, 'tel_switch', telOn, '手机端', '手机端', false, d) +
                      '</div>'
                    : '<div class="stp-preview-switches">' +
                        mobileUseBtn(d.tplfile, 'switch', pcOn, '导航已开启', '导航已关闭', true, d) +
                      '</div>';
                var actionsHtml = '';
                if(cfg.allowUpdate && d.update === 'y'){
                    actionsHtml += '<button type="button" class="stp-op-btn is-update" data-action="update" data-tpl="' + safeText(d.tplfile) + '"><i class="fa fa-cloud-download"></i> 更新</button>';
                }
                if(d.has_setting === 'y'){
                    actionsHtml += '<button type="button" class="stp-op-btn" data-action="setting" data-tpl="' + safeText(d.tplfile) + '"><i class="fa fa-sliders"></i> 配置</button>';
                }
                var licenseHtml = licenseBadge(d);
                return '<article class="stp-mobile-card' + activeClass + ((d.license_invalid === 'y') ? ' is-locked' : '') + '">' +
                    '<div class="stp-preview-wrap">' +
                        '<a href="javascript:;" class="stp-cover stp-mobile-preview" data-action="img" data-preview="' + safeText(d.preview) + '" data-name="' + safeText(d.tplname) + '">' + coverHtml(d.preview) + '</a>' +
                    '</div>' +
                    '<div class="stp-mobile-body">' +
                        '<div class="stp-mobile-head">' +
                            '<div class="stp-mobile-info">' +
                                '<h3 class="stp-mobile-title">' + safeText(d.tplname) + '</h3>' +
                                '<div class="stp-mobile-meta">' +
                                    '<div class="stp-mobile-sub"><i class="fa fa-folder-o"></i><span class="stp-mobile-path">' + safeText(d.tplfile) + '</span></div>' +
                                    '<span class="stp-version">v' + safeText(d.version) + '</span>' +
                                '</div>' +
                                (licenseHtml ? '<div class="stp-license-row">' + licenseHtml + '</div>' : '') +
                            '</div>' +
                        '</div>' +
                        switchesHtml +
                        '<div class="stp-mobile-actions' + (actionsHtml ? '' : ' is-empty') + '">' +
                            (actionsHtml || '<span class="stp-action-empty">暂无可配置项</span>') +
                        '</div>' +
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
        moveStpTabIndicator($('#tplScopeTabs .stp-tab.is-active'), false);

        $('#tplScopeTabs').on('click', '.stp-tab', function(){
            var scope = $(this).data('scope');
            if(!scopeMap[scope] || scope === currentScope) return;
            setTplScope(scope, true);
        });

        var touchStartX = 0, touchStartY = 0, touchMoved = false, ignoreSwipe = false;
        $('.station-tpl-page').on('touchstart', function(e){
            var $target = $(e.target);
            ignoreSwipe = $target.closest('input, textarea, select, button, .layui-form-switch, .stp-use-btn, [contenteditable="true"]').length > 0;
            touchMoved = false;
            if (ignoreSwipe) return;
            var t = e.originalEvent.touches && e.originalEvent.touches[0];
            if (!t) return;
            touchStartX = t.clientX;
            touchStartY = t.clientY;
        });
        $('.station-tpl-page').on('touchmove', function(e){
            if (ignoreSwipe) return;
            var t = e.originalEvent.touches && e.originalEvent.touches[0];
            if (!t) return;
            var dx = t.clientX - touchStartX;
            var dy = t.clientY - touchStartY;
            if (Math.abs(dx) > 20 && Math.abs(dy) < 42) touchMoved = true;
        });
        $('.station-tpl-page').on('touchend', function(e){
            if (ignoreSwipe) { ignoreSwipe = false; return; }
            if (!touchMoved) return;
            var changed = e.originalEvent.changedTouches && e.originalEvent.changedTouches[0];
            if (!changed) return;
            var diff = changed.clientX - touchStartX;
            if (Math.abs(diff) < 50) return;
            var idx = $.inArray(currentScope, stpScopeNames);
            if (idx < 0) idx = 0;
            if (diff < 0 && idx < stpScopeNames.length - 1) idx++;
            else if (diff > 0 && idx > 0) idx--;
            else return;
            setTplScope(stpScopeNames[idx], true);
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

        $('#tplMobileList').on('click', '[data-action="mobile-switch"]', function(){
            var $btn = $(this);
            if ($btn.prop('disabled')) return;
            var cfg = getScopeConfig();
            var filterName = $btn.data('filter');
            var active = parseInt($btn.data('active'), 10) || 0;
            var tplFile = $btn.data('tpl');
            var url = '';
            if (filterName === 'tel_switch') {
                if (!cfg.canTel || !cfg.telSwitchUrl) return;
                if (!active) {
                    layer.msg('当前模板类型只支持切换，不支持完全关闭');
                    renderTable();
                    return;
                }
                url = cfg.telSwitchUrl;
            } else {
                if (!active && currentScope !== 'bottom_nav') {
                    layer.msg('当前模板类型只支持切换，不支持完全关闭');
                    renderTable();
                    return;
                }
                url = cfg.switchUrl;
            }
            var tpl = active ? tplFile : 'em_null_tpl';
            $btn.prop('disabled', true).addClass('is-loading');
            var loadIdx = layer.load(2);
            $.ajax({
                url: url, type: 'POST', dataType: 'json',
                data: { tpl: tpl, status: active, token: '<?= LoginAuth::genToken() ?>' },
                success: function(e){
                    if(e.code == 400){ layer.msg(e.msg); renderTable(); return; }
                    layer.msg('模板配置已更新');
                    renderTable();
                },
                error: function(err){ layer.msg(err.responseJSON && err.responseJSON.msg ? err.responseJSON.msg : '请求失败'); },
                complete: function(){ layer.close(loadIdx); $btn.prop('disabled', false).removeClass('is-loading'); }
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
                    area: isMobile ? ['98%', '85%'] : ['1000px', '80%'],
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
                area: isMobile ? ['98%', '85%'] : ['1000px', '80%'],
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




