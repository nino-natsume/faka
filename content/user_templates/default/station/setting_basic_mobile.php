<?php
defined('DC_ROOT') || exit('access denied!');

$_stationLogo = $userStation['logo'] ?? '';
$_stationFavicon = $userStation['favicon'] ?? '';
$_siteDesc = $userStation['site_description'] ?? '';
$_siteKey = $userStation['site_key'] ?? '';
$_logTitleStyle = (int)($userStation['log_title_style'] ?? 0);
$_icp = $userStation['icp'] ?? '';
$_footerInfo = $userStation['footer_info'] ?? '';
$_userAgreement = $userStation['user_agreement'] ?? '';
$_privacyPolicy = $userStation['privacy_policy'] ?? '';
$_agreementSiteName = htmlspecialchars($userStation['name'] ?: Option::get('blogname') ?: '本站');
if (empty($_userAgreement)) {
    $_userAgreement = '<h2>用户服务协议</h2>'
        . '<p>欢迎使用 <strong>' . $_agreementSiteName . '</strong>（以下简称"本站"）提供的虚拟商品自动发卡服务。请您在注册或使用本站服务之前，仔细阅读本协议。注册即表示您已充分阅读、理解并同意接受本协议的全部内容。</p>'
        . '<h3>一、服务说明</h3><p>本站是一个虚拟商品在线交易平台，提供卡密、兑换码、账号、充值服务等数字商品的自动化销售与发放服务。</p>'
        . '<h3>二、用户账号</h3><ol><li>用户注册时应提供真实、准确的信息，并妥善保管账号及密码。</li><li>用户应对其账号下的一切行为和交易负责。</li><li>用户不得将账号转让、出借给他人使用。</li><li>本站有权对异常账号进行限制或封禁。</li></ol>'
        . '<h3>三、交易规则</h3><ol><li>用户下单前应仔细阅读商品描述，下单付款即视为认可商品内容。</li><li>虚拟商品具有可复制性和不可回收性，<strong>一经发货原则上不支持退款</strong>。</li><li>如遇卡密无效等质量问题，请在收货后 24 小时内联系客服。</li><li>因用户自身原因造成的损失，本站不承担责任。</li></ol>'
        . '<h3>四、免责声明</h3><ol><li>本站作为交易平台，不对第三方商品的实际效果承担担保责任。</li><li>因不可抗力导致的服务中断，本站不承担赔偿责任。</li></ol>'
        . '<h3>五、协议变更</h3><p>本站有权根据运营需要修改本协议，修改后将在本站公布。继续使用即视为同意。</p>';
}
if (empty($_privacyPolicy)) {
    $_privacyPolicy = '<h2>隐私政策</h2>'
        . '<p><strong>' . $_agreementSiteName . '</strong>（以下简称"本站"）非常重视用户的隐私保护。</p>'
        . '<h3>一、信息收集</h3><ol><li><strong>注册信息：</strong>用户名、邮箱、手机号。</li><li><strong>订单信息：</strong>商品名称、订单号、支付金额等。</li><li><strong>设备与日志信息：</strong>IP 地址、浏览器类型等。</li></ol>'
        . '<h3>二、信息使用</h3><ol><li>处理订单与交易结算。</li><li>提供客服支持。</li><li>监测异常交易，防范欺诈。</li></ol>'
        . '<h3>三、信息存储与保护</h3><ol><li>您的个人信息存储在安全的服务器中。</li><li>用户密码经过不可逆加密存储。</li></ol>'
        . '<h3>四、政策变更</h3><p>我们可能根据业务发展更新本隐私政策，更新后将在本站公布。继续使用即视为同意。</p>';
}
$_filled = function($v) { return trim((string)$v) !== ''; };
$_basicTotal = 10;
$_basicDone = 0;
foreach ([$userStation['name'] ?? '', $userStation['title'] ?? '', $userStation['site_subtitle'] ?? '', $_stationLogo, $_stationFavicon, $_siteKey, $_siteDesc, $_icp, $_userAgreement, $_privacyPolicy] as $_v) {
    if ($_filled($_v)) $_basicDone++;
}
?>
<style>
    .uc-site-footer{display:none!important}.smp-page,.smp-page *{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
    .smp-page{--smp-primary:var(--theme-primary,#667eea);--smp-primary-rgb:var(--tp-rgb,102,126,234);--smp-soft:rgba(var(--smp-primary-rgb),.10);min-height:100vh;padding:12px 12px calc(76px + env(safe-area-inset-bottom,0px));background:#f5f6f8;color:#20242c;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif}
    .smp-tabs-wrap{position:sticky;top:calc(50px + env(safe-area-inset-top,0px));z-index:10;margin:-10px -12px 12px;padding:10px 12px 8px;background:rgba(245,245,246,.96);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px)}
    .smp-tabs{position:relative;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:7px;padding:4px;border-radius:10px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;box-shadow:var(--shadow-primary)}
    .ss-tab{position:relative;z-index:1;height:34px;border:0;border-radius:999px;background:transparent;color:#697180;font-size:12px;font-weight:900;white-space:nowrap}.ss-tab.is-active{color:var(--smp-primary);background:var(--smp-soft); border-radius: 10px;}.smp-tab-indicator{position:absolute;left:0;bottom:4px;width:24px;height:3px;border-radius:999px;background:var(--smp-primary);z-index:2;pointer-events:none;will-change:left,width}
    .smp-panels{touch-action:pan-y pinch-zoom}
    .ss-tab-panel{display:none}.ss-tab-panel.is-active{display:block}.smp-card{display:block;margin-bottom:12px;padding:16px;border-radius:10px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;box-shadow:var(--shadow-primary);text-decoration:none;color:inherit}.smp-card-head{display:flex;align-items:flex-start;gap:9px;margin-bottom:13px}.smp-card-icon{width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:var(--smp-soft);color:var(--smp-primary);font-size:15px;flex-shrink:0}.smp-card-title{margin:0;color:#20242c;font-size:14px;font-weight:900;line-height:1.2}.smp-card-desc{margin:4px 0 0;color:#8b95a5;font-size:12px;line-height:1.6;font-weight:500}.smp-fields{display:grid;gap:9px}.smp-field{display:block;padding:11px 12px;border-radius:14px;background:#f8f9fb}.smp-label{display:block;margin-bottom:8px;color:#7c8797;font-size:12px;font-weight:800}.ss-input,.ss-textarea,.ss-select{width:100%;border:1px solid #edf0f5;border-radius:12px;background:#fff;color:#20242c;font-size:16px;outline:none;box-shadow:none}.ss-input,.ss-select{height:42px;padding:0 12px}.ss-textarea{min-height:108px;padding:11px 12px;line-height:1.7;resize:vertical}.ss-input:focus,.ss-textarea:focus,.ss-select:focus{border-color:rgba(var(--smp-primary-rgb),.35);box-shadow:0 0 0 3px rgba(var(--smp-primary-rgb),.08)}.smp-tip{display:block;margin-top:7px;color:#8b95a5;font-size:12px;line-height:1.65}.smp-upload{display:flex;gap:8px;align-items:center}.smp-upload .ss-input{flex:1;min-width:0}.smp-upload-btn{height:42px;padding:0 12px;border:0;border-radius:12px;background:var(--smp-soft);color:var(--smp-primary);font-size:12px;font-weight:900;white-space:nowrap}.smp-preview{display:flex;align-items:center;gap:10px;margin-top:8px}.smp-preview img{max-height:38px;max-width:140px;border-radius:7px;background:#fff;object-fit:contain}.smp-clear{border:0;background:none;color:#ef4444;font-size:12px}.smp-savebar{position:fixed;left:0;right:0;bottom:0;z-index:30;padding:10px 12px calc(10px + env(safe-area-inset-bottom,0px));background:rgba(255,255,255,.96);border-top:1px solid #edf0f5;backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px)}.smp-save{width:100%;height:46px;border:0;border-radius:16px;background:linear-gradient(135deg,var(--theme-primary,#667eea),var(--theme-secondary,#764ba2));color:#fff;font-size:14px;font-weight:900;box-shadow:0 10px 24px rgba(var(--smp-primary-rgb),.18)}.tox-tinymce{border-radius:12px!important;border:1px solid #edf0f5!important;overflow:hidden}
    @media(max-width:360px){.smp-page{padding-left:10px;padding-right:10px}.ss-tab{font-size:11px}.smp-field{padding-left:10px;padding-right:10px}.smp-upload{flex-wrap:wrap}.smp-upload-btn{width:100%}}
</style>

<main class="smp-page">
<div class="smp-tabs-wrap">
        <div class="smp-tabs">
            <button type="button" class="ss-tab is-active" data-tab="basic-general">基本信息</button>
            <button type="button" class="ss-tab" data-tab="basic-seo">SEO 设置</button>
            <button type="button" class="ss-tab" data-tab="basic-footer">底部信息</button>
            <button type="button" class="ss-tab" data-tab="basic-agreement">协议管理</button>
            <div class="smp-tab-indicator" id="smpTabIndicator"></div>
        </div>
    </div>

    <form id="form-basic" class="smp-panels">
        <section class="ss-tab-panel is-active" data-panel="basic-general">
            <div class="smp-card">
                <div class="smp-card-head"><div class="smp-card-icon"><i class="fa fa-home"></i></div><div><h2 class="smp-card-title">基本展示</h2><p class="smp-card-desc">决定用户看到的店铺名称、标题和品牌图标。</p></div></div>
                <div class="smp-fields">
                    <label class="smp-field"><span class="smp-label">店铺名称</span><input type="text" name="name" value="<?= htmlspecialchars($userStation['name']) ?>" placeholder="请输入店铺名称" class="ss-input"></label>
                    <label class="smp-field"><span class="smp-label">网站标题</span><input type="text" name="title" value="<?= htmlspecialchars($userStation['title']) ?>" placeholder="请输入网站标题" class="ss-input"><span class="smp-tip">显示在浏览器标签页与搜索标题中的文案。</span></label>
                    <label class="smp-field"><span class="smp-label">网站副标题</span><input type="text" name="site_subtitle" value="<?= htmlspecialchars($userStation['site_subtitle'] ?? '') ?>" placeholder="请输入网站副标题" class="ss-input"><span class="smp-tip">显示在首页标题下方的辅助说明文字。</span></label>
                    <div class="smp-field"><span class="smp-label">网站 Logo</span><div class="smp-upload"><input type="text" name="logo" value="<?= htmlspecialchars($_stationLogo) ?>" placeholder="留空使用默认 Logo" class="ss-input" id="ss-logo-input"><button type="button" class="smp-upload-btn" id="ss-logo-btn"><i class="fa fa-cloud-upload"></i> 上传</button></div><div class="smp-preview" id="ss-logo-preview"<?= empty($_stationLogo) ? ' style="display:none;"' : '' ?>><img src="<?= !empty($_stationLogo) ? htmlspecialchars(getFileUrl($_stationLogo)) : '' ?>" alt=""><button type="button" class="smp-clear" data-target="logo">清除</button></div><span class="smp-tip">建议横向图片，推荐尺寸 180×60，支持 png、jpg、webp、svg。</span></div>
                    <div class="smp-field"><span class="smp-label">Favicon</span><div class="smp-upload"><input type="text" name="favicon" value="<?= htmlspecialchars($_stationFavicon) ?>" placeholder="留空使用默认图标" class="ss-input" id="ss-favicon-input"><button type="button" class="smp-upload-btn" id="ss-favicon-btn"><i class="fa fa-cloud-upload"></i> 上传</button></div><div class="smp-preview" id="ss-favicon-preview"<?= empty($_stationFavicon) ? ' style="display:none;"' : '' ?>><img src="<?= !empty($_stationFavicon) ? htmlspecialchars(getFileUrl($_stationFavicon)) : '' ?>" alt=""><button type="button" class="smp-clear" data-target="favicon">清除</button></div><span class="smp-tip">浏览器标签页图标，推荐尺寸 48×48，支持 ico、png、jpg、svg。</span></div>
                </div>
            </div>
        </section>

        <section class="ss-tab-panel" data-panel="basic-seo"><div class="smp-card"><div class="smp-card-head"><div class="smp-card-icon"><i class="fa fa-search"></i></div><div><h2 class="smp-card-title">SEO 设置</h2><p class="smp-card-desc">配置关键词、站点描述和商品详情页标题方案。</p></div></div><div class="smp-fields"><label class="smp-field"><span class="smp-label">站点关键字</span><input type="text" name="site_key" value="<?= htmlspecialchars($_siteKey) ?>" placeholder="多个关键字用英文逗号分隔" class="ss-input"><span class="smp-tip">例如：发卡,自动发卡,卡密</span></label><label class="smp-field"><span class="smp-label">站点描述</span><textarea name="site_description" placeholder="一句话描述你的网站" class="ss-textarea" style="min-height:92px;"><?= htmlspecialchars($_siteDesc) ?></textarea><span class="smp-tip">搜索引擎会把它展示在搜索结果摘要区域，建议控制在 80 字以内。</span></label><label class="smp-field"><span class="smp-label">详情页标题方案</span><select name="log_title_style" class="ss-select"><option value="0" <?= $_logTitleStyle == 0 ? 'selected' : '' ?>>商品名称</option><option value="1" <?= $_logTitleStyle == 1 ? 'selected' : '' ?>>商品名称 - 站点标题</option><option value="2" <?= $_logTitleStyle == 2 ? 'selected' : '' ?>>商品名称 - 浏览器标题</option></select><span class="smp-tip">控制商品详情页浏览器标签页的标题显示方式。</span></label></div></div></section>

        <section class="ss-tab-panel" data-panel="basic-footer"><div class="smp-card"><div class="smp-card-head"><div class="smp-card-icon"><i class="fa fa-shield"></i></div><div><h2 class="smp-card-title">底部信息</h2><p class="smp-card-desc">备案号、版权说明和统计代码会展示在网站底部。</p></div></div><div class="smp-fields"><label class="smp-field"><span class="smp-label">ICP备案号</span><input type="text" name="icp" value="<?= htmlspecialchars($_icp) ?>" placeholder="例如：京ICP备12345678号" class="ss-input"><span class="smp-tip">没有备案可以不填。</span></label><label class="smp-field"><span class="smp-label">首页底部信息</span><textarea name="footer_info" placeholder="支持 HTML，可用于版权说明、统计代码等" class="ss-textarea" style="min-height:92px;"><?= htmlspecialchars($_footerInfo) ?></textarea><span class="smp-tip">原样显示在网站最底部，支持 HTML。</span></label></div></div></section>

        <section class="ss-tab-panel" data-panel="basic-agreement"><div class="smp-card"><div class="smp-card-head"><div class="smp-card-icon"><i class="fa fa-file-text-o"></i></div><div><h2 class="smp-card-title">协议管理</h2><p class="smp-card-desc">设置用户注册和下单时可查看的协议内容。</p></div></div><div class="smp-fields"><label class="smp-field"><span class="smp-label">用户服务协议</span><textarea id="editor_agreement" name="user_agreement" class="ss-textarea" style="min-height:220px;"><?= htmlspecialchars($_userAgreement) ?></textarea><span class="smp-tip">支持富文本编辑，留空则前端不显示对应链接。</span></label><label class="smp-field"><span class="smp-label">隐私政策</span><textarea id="editor_privacy" name="privacy_policy" class="ss-textarea" style="min-height:220px;"><?= htmlspecialchars($_privacyPolicy) ?></textarea><span class="smp-tip">支持富文本编辑，留空则前端不显示对应链接。</span></label></div></div></section>
        <input type="hidden" name="master_sort" value="<?= (int)$userStation['master_sort'] ?>">
        <input type="hidden" name="master_goods" value="<?= (int)$userStation['master_goods'] ?>">
    </form>

    <div class="smp-savebar"><button type="button" class="smp-save" id="btn-save-basic"><i class="fa fa-check"></i> 保存基础信息</button></div>
</main>

<script src="<?= DC_URL ?>admin/tinymce/tinymce.min.js?t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>
<script>
(function() {
    function boot() {
        if (!window.jQuery || !window.layui || !window.layui.use) { setTimeout(boot, 80); return; }
        layui.use(['layer', 'upload'], function() {
            var $ = window.jQuery;
            var layer = layui.layer, upload = layui.upload;
            var _uploadUrl = <?= json_encode(DC_URL . 'user/article.php?action=upload_cover2') ?>;
            var _agreementReady = false;
            var smpTabs = [];
            $('.ss-tab').each(function(){ smpTabs.push($(this).data('tab')); });
            var smpCurrentTab = $('.ss-tab.is-active').data('tab') || smpTabs[0] || 'basic-general';
            var smpIndicatorTimer = null;

            function moveSmpIndicator($tab, animate) {
                var $indicator = $('#smpTabIndicator');
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
                if (smpIndicatorTimer) clearTimeout(smpIndicatorTimer);
                $indicator.css({ left: stretchLeft + 'px', width: stretchWidth + 'px', transition: 'left .16s cubic-bezier(.4,0,.2,1), width .16s cubic-bezier(.4,0,.2,1)' });
                smpIndicatorTimer = setTimeout(function(){
                    $indicator.css({ left: targetLeft + 'px', width: indicatorW + 'px', transition: 'left .13s cubic-bezier(.4,0,.2,1), width .13s cubic-bezier(.4,0,.2,1)' });
                }, 200);
            }

            function initAgreementEditors() {
                if (_agreementReady) return;
                _agreementReady = true;
                tinymce.init({
                    selector: '#editor_agreement, #editor_privacy',
                    language: 'zh_CN', height: 320,
                    images_upload_handler: function(blobInfo, progress) {
                        return new Promise(function(resolve, reject) {
                            var xhr = new XMLHttpRequest();
                            xhr.open('POST', _uploadUrl);
                            xhr.upload.onprogress = function(e) { progress(e.loaded / e.total * 100); };
                            xhr.onload = function() {
                                if (xhr.status < 200 || xhr.status >= 300) { reject('HTTP Error: ' + xhr.status); return; }
                                var json = JSON.parse(xhr.responseText);
                                if (!json || typeof json.location != 'string') { reject('Invalid JSON'); return; }
                                resolve(json.location);
                            };
                            xhr.onerror = function() { reject('Upload failed'); };
                            var fd = new FormData(); fd.append('image', blobInfo.blob(), blobInfo.filename()); xhr.send(fd);
                        });
                    },
                    plugins: ['advlist','autolink','lists','link','image','charmap','preview','anchor','searchreplace','visualblocks','code','fullscreen','insertdatetime','media','table','wordcount'],
                    toolbar: 'undo redo | blocks | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat',
                    content_style: 'html, body { font-family: Helvetica, Arial, sans-serif; font-size: 16px; -webkit-text-size-adjust: 100%; } body { line-height: 1.7; } p, div, li, td, th { font-size: 16px; }',
                    setup: function(editor) { editor.on('input change undo redo cut paste', function() { editor.save(); }); }
                });
            }

            function setSmpTab(tab, animate) {
                if ($.inArray(tab, smpTabs) === -1) tab = smpTabs[0] || 'basic-general';
                smpCurrentTab = tab;
                $('.ss-tab').removeClass('is-active');
                $('.ss-tab[data-tab="' + tab + '"]').addClass('is-active');
                moveSmpIndicator($('.ss-tab[data-tab="' + tab + '"]'), animate !== false);
                $('.ss-tab-panel').removeClass('is-active');
                $('.ss-tab-panel[data-panel="' + tab + '"]').addClass('is-active');
                if (tab === 'basic-agreement') initAgreementEditors();
            }
            moveSmpIndicator($('.ss-tab.is-active'), false);
            $('.ss-tab').on('click', function() { setSmpTab($(this).data('tab'), true); });

            var touchStartX = 0, touchStartY = 0, touchMoved = false, ignoreSwipe = false;
            $('.smp-page').on('touchstart', function(e){
                if (e.originalEvent.touches && e.originalEvent.touches.length > 1) {
                    ignoreSwipe = true;
                    touchMoved = false;
                    return;
                }
                var $target = $(e.target);
                ignoreSwipe = $target.closest('input, textarea, select, [contenteditable="true"], .tox, .tox-tinymce').length > 0;
                touchMoved = false;
                if (ignoreSwipe) return;
                var t = e.originalEvent.touches && e.originalEvent.touches[0]; if (!t) return;
                touchStartX = t.clientX; touchStartY = t.clientY;
            });
            $('.smp-page').on('touchmove', function(e){
                if (e.originalEvent.touches && e.originalEvent.touches.length > 1) return;
                if (ignoreSwipe) return;
                var t = e.originalEvent.touches && e.originalEvent.touches[0]; if (!t) return;
                var dx = t.clientX - touchStartX, dy = t.clientY - touchStartY;
                if (Math.abs(dx) > 20 && Math.abs(dy) < 42) touchMoved = true;
            });
            $('.smp-page').on('touchend', function(e){
                if (ignoreSwipe) { ignoreSwipe = false; return; }
                if (!touchMoved) return;
                var changed = e.originalEvent.changedTouches && e.originalEvent.changedTouches[0]; if (!changed) return;
                var diff = changed.clientX - touchStartX;
                if (Math.abs(diff) < 50) return;
                var idx = $.inArray(smpCurrentTab, smpTabs); if (idx < 0) idx = 0;
                if (diff < 0 && idx < smpTabs.length - 1) idx++;
                else if (diff > 0 && idx > 0) idx--;
                else return;
                setSmpTab(smpTabs[idx], true);
            });

            function bindUpload(btnId, inputId, previewId) {
                upload.render({
                    elem: '#' + btnId, field: 'image', accept: 'images',
                    exts: 'png|jpg|jpeg|gif|webp|svg|ico', url: _uploadUrl,
                    done: function(res) {
                        if (res && res.location) {
                            $('#' + inputId).val(res.location);
                            $('#' + previewId).show().find('img').attr('src', res.location.replace(/^\.\./, ''));
                            layer.msg('上传成功', {icon: 1});
                        } else { layer.msg('上传失败', {icon: 2}); }
                    },
                    error: function() { layer.msg('上传失败', {icon: 2}); }
                });
            }
            bindUpload('ss-logo-btn', 'ss-logo-input', 'ss-logo-preview');
            bindUpload('ss-favicon-btn', 'ss-favicon-input', 'ss-favicon-preview');
            $('.smp-clear').on('click', function() { var t = $(this).data('target'); $('#ss-' + t + '-input').val(''); $('#ss-' + t + '-preview').hide(); });
            $('#ss-logo-input, #ss-favicon-input').on('input', function() { var v = $(this).val(), isLogo = this.id === 'ss-logo-input'; var $pv = $(isLogo ? '#ss-logo-preview' : '#ss-favicon-preview'); if (v) { $pv.show().find('img').attr('src', v.replace(/^\.\./, '')); } else { $pv.hide(); } });
            $('#btn-save-basic').on('click', function() { if (window.tinymce) tinymce.triggerSave(); var data = $('#form-basic').serialize(); var $btn = $(this); $btn.prop('disabled', true).text('保存中...'); $.ajax({ type: 'POST', url: '?action=setting_basic_ajax', data: data, dataType: 'json', success: function(e) { $btn.prop('disabled', false).html('<i class="fa fa-check"></i> 保存基础信息'); if (e.code == 400) { layer.msg(e.msg, {icon: 2}); } else { layer.msg(e.msg || '保存成功', {icon: 1}); } }, error: function() { $btn.prop('disabled', false).html('<i class="fa fa-check"></i> 保存基础信息'); layer.msg('网络请求发生错误，请重试', {icon: 2}); } }); });
        });
    }
    boot();
})();
</script>
