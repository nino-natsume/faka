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
?>

<style>
    .ss-page { display: flex; flex-direction: column; gap: 22px; padding: 8px 0 18px; }
    .ss-page-header { display: flex; align-items: center; gap: 14px; }
    .ss-back-btn { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 12px; border: 1.5px solid var(--card-border, #e2e8f0); background: var(--card-bg, #fff); color: var(--text-sub, #64748b); font-size: 16px; cursor: pointer; transition: .18s; text-decoration: none; flex-shrink: 0; }
    .ss-back-btn:hover { color: var(--theme-primary); border-color: rgba(var(--tp-rgb),.22); background: rgba(var(--tp-rgb),.06); text-decoration: none; }
    .ss-page-title { margin: 0; font-size: 22px; font-weight: 800; color: var(--text-main, #1e293b); }
    .ss-page-desc { margin: 2px 0 0; font-size: 13px; color: var(--text-sub, #64748b); }

    .ss-form-card { background: var(--pc-card-bg); border: 2px solid #fff; border-radius: 14px; box-shadow: 0 1px 18px #12345b0a; overflow: hidden; }
    .ss-form-body { padding: 28px; }
    .ss-form-grid { display: grid; grid-template-columns: 1fr; gap: 18px; }
    .ss-form-item { margin-bottom: 0; }
    .ss-label { display: flex; align-items: center; gap: 6px; margin-bottom: 10px; color: var(--text-main, #1e293b); font-size: 14px; font-weight: 600; }
    .ss-input, .ss-textarea, .ss-select {
        width: 100%; border: 1.5px solid var(--input-border, #e2e8f0); background: var(--input-bg, #fff);
        color: var(--text-main); border-radius: 10px; padding: 13px 16px; font-size: 14px;
        transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
    }
    .ss-input:focus, .ss-textarea:focus, .ss-select:focus {
        outline: none; border-color: rgba(var(--tp-rgb),.5); box-shadow: 0 0 0 4px rgba(var(--tp-rgb),.08); background: #fff;
    }
    .ss-textarea { min-height: 112px; resize: vertical; }
    .ss-tips { display: block; margin-top: 8px; color: var(--text-sub, #64748b); font-size: 12px; line-height: 1.7; }

    .ss-upload-row { display: flex; align-items: center; gap: 12px; }
    .ss-upload-row .ss-input { flex: 1; min-width: 0; }
    .ss-upload-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; flex-shrink: 0; height: 46px; padding: 0 16px; border: 1.5px solid var(--input-border, #e2e8f0); border-radius: 10px; background: var(--bg-secondary, #f8fafc); color: var(--text-main, #1e293b); font-size: 13px; font-weight: 600; cursor: pointer; transition: background .18s, border-color .18s; white-space: nowrap; }
    .ss-upload-btn:hover { background: rgba(var(--tp-rgb),.06); border-color: rgba(var(--tp-rgb),.22); }
    .ss-upload-preview { display: flex; align-items: center; gap: 10px; margin-top: 8px; }
    .ss-upload-preview img { max-height: 40px; max-width: 180px; border-radius: 6px; border: 1px solid var(--input-border, #e2e8f0); background: #fff; object-fit: contain; }
    .ss-upload-preview .ss-upload-clear { font-size: 12px; color: #ef4444; cursor: pointer; border: none; background: none; padding: 0; }
    .ss-upload-preview .ss-upload-clear:hover { text-decoration: underline; }

    .ss-tabs-bar { padding: 18px 20px; display: flex; flex-wrap: wrap; gap: 14px; align-items: center; }
    .ss-tabs-group { display: inline-flex; flex-wrap: wrap; gap: 10px; padding: 8px; border-radius: 10px; background: var(--bg-secondary, #f5f7fa); }
    .ss-tab { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 42px; padding: 0 18px; border-radius: 14px; border: none; background: none; color: var(--text-sub, #64748b); font-size: 14px; font-weight: 700; cursor: pointer; transition: .18s; white-space: nowrap; }
    .ss-tab:hover { color: var(--text-main, #1e293b); background: rgba(var(--tp-rgb),.06); }
    .ss-tab.is-active { background: #fff; color: var(--theme-primary); box-shadow: 0 10px 20px rgba(15,23,42,.08); }
    .ss-tab-panel { display: none; }
    .ss-tab-panel.is-active { display: block; }

    .ss-form-footer { display: flex; align-items: center; justify-content: flex-end; gap: 10px; padding: 16px 28px; border-top: 1px solid var(--card-border, #e2e8f0); background: var(--bg-secondary, #f8fafc); }
    .ss-btn-save { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-width: 140px; height: 44px; padding: 0 22px; border: 0; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; transition: .18s; background: linear-gradient(135deg, var(--tp-dark), var(--tp-light)); color: #fff; box-shadow: 0 8px 20px rgba(var(--tp-rgb),.18); }
    .ss-btn-save:hover { transform: translateY(-1px); box-shadow: 0 12px 28px rgba(var(--tp-rgb),.24); }

    .tox-tinymce { border-radius: 10px !important; border: 1.5px solid var(--input-border, #e2e8f0) !important; overflow: hidden; }

    @media (max-width: 768px) {
        .ss-form-body { padding: 20px; }
        .ss-form-footer { padding: 14px 20px; }
        .ss-tabs-bar { padding: 14px 16px; }
        .ss-tabs-group { gap: 6px; padding: 6px; }
        .ss-tab { min-height: 36px; padding: 0 14px; font-size: 12px; }
    }
</style>

<main class="ss-page">
    <div class="ss-page-header">
        <a href="?action=setting" class="ss-back-btn"><i class="fa fa-arrow-left"></i></a>
        <div>
            <h1 class="ss-page-title">基础信息</h1>
            <p class="ss-page-desc">店铺名称、标题、Logo、SEO、底部信息与协议</p>
        </div>
    </div>

    <div class="ss-form-card">
        <div class="ss-tabs-bar">
            <div class="ss-tabs-group">
                <button type="button" class="ss-tab is-active" data-tab="basic-general"><i class="fa fa-bookmark-o"></i> 基本信息</button>
                <button type="button" class="ss-tab" data-tab="basic-seo"><i class="fa fa-search"></i> SEO 设置</button>
                <button type="button" class="ss-tab" data-tab="basic-footer"><i class="fa fa-registered"></i> 底部信息</button>
                <button type="button" class="ss-tab" data-tab="basic-agreement"><i class="fa fa-file-text-o"></i> 协议管理</button>
            </div>
        </div>
        <form id="form-basic">
            <div class="ss-tab-panel is-active" data-panel="basic-general">
                <div class="ss-form-body">
                    <div class="ss-form-grid">
                        <div class="ss-form-item">
                            <label class="ss-label"><i class="fa fa-bookmark-o" style="color:var(--theme-primary);"></i> 店铺名称</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($userStation['name']) ?>" placeholder="请输入店铺名称" class="ss-input">
                        </div>
                        <div class="ss-form-item">
                            <label class="ss-label"><i class="fa fa-header" style="color:var(--theme-primary);"></i> 网站标题</label>
                            <input type="text" name="title" value="<?= htmlspecialchars($userStation['title']) ?>" placeholder="请输入网站标题" class="ss-input">
                            <span class="ss-tips">显示在浏览器标签页与搜索标题中的文案。</span>
                        </div>
                        <div class="ss-form-item">
                            <label class="ss-label"><i class="fa fa-font" style="color:var(--theme-primary);"></i> 网站副标题</label>
                            <input type="text" name="site_subtitle" value="<?= htmlspecialchars($userStation['site_subtitle'] ?? '') ?>" placeholder="请输入网站副标题" class="ss-input">
                            <span class="ss-tips">显示在首页标题下方的辅助说明文字。</span>
                        </div>
                        <div class="ss-form-item">
                            <label class="ss-label"><i class="fa fa-picture-o" style="color:var(--theme-primary);"></i> 网站 Logo</label>
                            <div class="ss-upload-row">
                                <input type="text" name="logo" value="<?= htmlspecialchars($_stationLogo) ?>" placeholder="留空使用默认 Logo" class="ss-input" id="ss-logo-input">
                                <button type="button" class="ss-upload-btn" id="ss-logo-btn"><i class="fa fa-cloud-upload"></i> 上传</button>
                            </div>
                            <div class="ss-upload-preview" id="ss-logo-preview"<?= empty($_stationLogo) ? ' style="display:none;"' : '' ?>>
                                <img src="<?= !empty($_stationLogo) ? htmlspecialchars(getFileUrl($_stationLogo)) : '' ?>" alt="">
                                <button type="button" class="ss-upload-clear" data-target="logo">&times; 清除</button>
                            </div>
                            <span class="ss-tips">前台网站 Logo，建议横向图片，推荐尺寸 180×60，支持 png、jpg、webp、svg。</span>
                        </div>
                        <div class="ss-form-item">
                            <label class="ss-label"><i class="fa fa-star-o" style="color:var(--theme-primary);"></i> Favicon</label>
                            <div class="ss-upload-row">
                                <input type="text" name="favicon" value="<?= htmlspecialchars($_stationFavicon) ?>" placeholder="留空使用默认图标" class="ss-input" id="ss-favicon-input">
                                <button type="button" class="ss-upload-btn" id="ss-favicon-btn"><i class="fa fa-cloud-upload"></i> 上传</button>
                            </div>
                            <div class="ss-upload-preview" id="ss-favicon-preview"<?= empty($_stationFavicon) ? ' style="display:none;"' : '' ?>>
                                <img src="<?= !empty($_stationFavicon) ? htmlspecialchars(getFileUrl($_stationFavicon)) : '' ?>" alt="">
                                <button type="button" class="ss-upload-clear" data-target="favicon">&times; 清除</button>
                            </div>
                            <span class="ss-tips">浏览器标签页图标，推荐尺寸 48×48，支持 ico、png、jpg、svg。</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ss-tab-panel" data-panel="basic-seo">
                <div class="ss-form-body">
                    <div class="ss-form-grid">
                        <div class="ss-form-item">
                            <label class="ss-label"><i class="fa fa-key" style="color:#6366f1;"></i> 站点关键字</label>
                            <input type="text" name="site_key" value="<?= htmlspecialchars($_siteKey) ?>" placeholder="多个关键字用英文逗号分隔" class="ss-input">
                            <span class="ss-tips">填写几个和你店铺相关的词，用英文逗号隔开，比如：发卡,自动发卡,卡密</span>
                        </div>
                        <div class="ss-form-item">
                            <label class="ss-label"><i class="fa fa-align-left" style="color:#6366f1;"></i> 站点描述</label>
                            <textarea name="site_description" placeholder="一句话描述你的网站" class="ss-textarea" style="min-height:80px;"><?= htmlspecialchars($_siteDesc) ?></textarea>
                            <span class="ss-tips">搜索引擎会把它展示在搜索结果的摘要区域。建议控制在 80 字以内。</span>
                        </div>
                        <div class="ss-form-item">
                            <label class="ss-label"><i class="fa fa-file-text" style="color:#6366f1;"></i> 详情页标题方案</label>
                            <select name="log_title_style" class="ss-select">
                                <option value="0" <?= $_logTitleStyle == 0 ? 'selected' : '' ?>>商品名称</option>
                                <option value="1" <?= $_logTitleStyle == 1 ? 'selected' : '' ?>>商品名称 - 站点标题</option>
                                <option value="2" <?= $_logTitleStyle == 2 ? 'selected' : '' ?>>商品名称 - 浏览器标题</option>
                            </select>
                            <span class="ss-tips">控制商品详情页浏览器标签页的标题显示方式。</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ss-tab-panel" data-panel="basic-footer">
                <div class="ss-form-body">
                    <div class="ss-form-grid">
                        <div class="ss-form-item">
                            <label class="ss-label"><i class="fa fa-shield" style="color:#64748b;"></i> ICP备案号</label>
                            <input type="text" name="icp" value="<?= htmlspecialchars($_icp) ?>" placeholder="例如：京ICP备12345678号" class="ss-input">
                            <span class="ss-tips">备案号会显示在网站底部。没有备案可以不填。</span>
                        </div>
                        <div class="ss-form-item">
                            <label class="ss-label"><i class="fa fa-code" style="color:#64748b;"></i> 首页底部信息</label>
                            <textarea name="footer_info" placeholder="支持 HTML，可用于版权说明、统计代码等" class="ss-textarea" style="min-height:80px;"><?= htmlspecialchars($_footerInfo) ?></textarea>
                            <span class="ss-tips">原样显示在网站最底部，支持 HTML。常见用法：版权声明、第三方统计代码。</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ss-tab-panel" data-panel="basic-agreement">
                <div class="ss-form-body">
                    <div class="ss-form-grid">
                        <div class="ss-form-item">
                            <label class="ss-label"><i class="fa fa-file-text-o" style="color:#f43f5e;"></i> 用户服务协议</label>
                            <textarea id="editor_agreement" name="user_agreement" class="ss-textarea" style="min-height:200px;"><?= htmlspecialchars($_userAgreement) ?></textarea>
                            <span class="ss-tips">用户注册时看到的《用户服务协议》内容，支持富文本编辑。留空则前端不显示对应链接。</span>
                        </div>
                        <div class="ss-form-item">
                            <label class="ss-label"><i class="fa fa-shield" style="color:#f43f5e;"></i> 隐私政策</label>
                            <textarea id="editor_privacy" name="privacy_policy" class="ss-textarea" style="min-height:200px;"><?= htmlspecialchars($_privacyPolicy) ?></textarea>
                            <span class="ss-tips">用户注册时看到的《隐私政策》内容，支持富文本编辑。留空则前端不显示对应链接。</span>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="master_sort" value="<?= (int)$userStation['master_sort'] ?>">
            <input type="hidden" name="master_goods" value="<?= (int)$userStation['master_goods'] ?>">
        </form>
        <div class="ss-form-footer">
            <button type="button" class="ss-btn-save" id="btn-save-basic"><i class="fa fa-check"></i> 保存</button>
        </div>
    </div>
</main>

<script src="<?= DC_URL ?>admin/tinymce/tinymce.min.js?t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>
<script>
(function() {
    function boot() {
        if (!window.jQuery || !window.layui || !window.layui.use) { setTimeout(boot, 80); return; }
        layui.use(['layer', 'upload'], function() {
            var layer = layui.layer, upload = layui.upload;
            var _uploadUrl = <?= json_encode(DC_URL . 'user/article.php?action=upload_cover2') ?>;
            var _agreementReady = false;

            // Tabs
            $('.ss-tab').on('click', function() {
                var p = $(this).data('tab');
                $(this).addClass('is-active').siblings('.ss-tab').removeClass('is-active');
                $('.ss-tab-panel').removeClass('is-active');
                $('.ss-tab-panel[data-panel="' + p + '"]').addClass('is-active');
            });

            // 协议 Tab 点击时初始化 TinyMCE
            $('.ss-tab[data-tab="basic-agreement"]').on('click', function() {
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
                    content_style: 'body { font-family: Helvetica, Arial, sans-serif; font-size: 15px }',
                    setup: function(editor) { editor.on('input change undo redo cut paste', function() { editor.save(); }); }
                });
            });

            // Logo/Favicon 上传
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

            // 清除按钮
            $('.ss-upload-clear').on('click', function() {
                var t = $(this).data('target');
                $('#ss-' + t + '-input').val('');
                $('#ss-' + t + '-preview').hide();
            });

            // 输入框变化同步预览
            $('#ss-logo-input, #ss-favicon-input').on('input', function() {
                var v = $(this).val(), isLogo = this.id === 'ss-logo-input';
                var $pv = $(isLogo ? '#ss-logo-preview' : '#ss-favicon-preview');
                if (v) { $pv.show().find('img').attr('src', v.replace(/^\.\./, '')); } else { $pv.hide(); }
            });

            // 保存
            $('#btn-save-basic').on('click', function() {
                if (window.tinymce) tinymce.triggerSave();
                var data = $('#form-basic').serialize();
                var $btn = $(this);
                $btn.prop('disabled', true).text('保存中...');
                $.ajax({
                    type: 'POST', url: '?action=setting_basic_ajax', data: data, dataType: 'json',
                    success: function(e) {
                        $btn.prop('disabled', false).html('<i class="fa fa-check"></i> 保存');
                        if (e.code == 400) { layer.msg(e.msg, {icon: 2}); }
                        else { layer.msg(e.msg || '保存成功', {icon: 1}); }
                    },
                    error: function() {
                        $btn.prop('disabled', false).html('<i class="fa fa-check"></i> 保存');
                        layer.msg('网络请求发生错误，请重试', {icon: 2});
                    }
                });
            });
        });
    }
    boot();
})();
</script>
