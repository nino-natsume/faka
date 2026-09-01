<?php
defined('DC_ROOT') || exit('access denied!');
$_rollNotice = $userStation['roll_notice'] ?? '';
$_homeNotice = $userStation['home_notice'] ?? '';
$_rollLen = mb_strlen(trim(strip_tags($_rollNotice)));
$_homeLen = mb_strlen(trim(strip_tags($_homeNotice)));
$_noticeDone = (!empty(trim($_rollNotice)) ? 1 : 0) + (!empty(trim(strip_tags($_homeNotice))) ? 1 : 0);
?>
<style>
    .uc-site-footer{display:none!important}.snm-page,.snm-page *{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
    .snm-page{--snm-primary:var(--theme-primary,#667eea);--snm-primary-rgb:var(--tp-rgb,102,126,234);--snm-soft:rgba(var(--snm-primary-rgb),.10);min-height:100vh;padding:12px 12px calc(76px + env(safe-area-inset-bottom,0px));background:#f5f6f8;color:#20242c;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif}
    .snm-card{display:block;margin-bottom:12px;padding:16px;border-radius:10px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;box-shadow:var(--shadow-primary);text-decoration:none;color:inherit}.snm-card-head{display:flex;align-items:flex-start;gap:9px;margin-bottom:13px}.snm-card-icon{width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:var(--snm-soft);color:var(--snm-primary);font-size:15px;flex-shrink:0}.snm-card-title{margin:0;color:#20242c;font-size:14px;font-weight:900;line-height:1.2}.snm-card-desc{margin:4px 0 0;color:#8b95a5;font-size:12px;line-height:1.6;font-weight:500}.snm-label{display:block;margin-bottom:8px;color:#7c8797;font-size:12px;font-weight:800}.ss-textarea{width:100%;min-height:108px;padding:11px 12px;border:1px solid #edf0f5;border-radius:12px;background:#fff;color:#20242c;font-size:16px;line-height:1.7;outline:none;box-shadow:none;resize:vertical}.ss-textarea:focus{border-color:rgba(var(--snm-primary-rgb),.35);box-shadow:0 0 0 3px rgba(var(--snm-primary-rgb),.08)}.snm-tip{display:block;margin-top:7px;color:#8b95a5;font-size:12px;line-height:1.65}.snm-preview{margin-top:10px;padding:11px 12px;border-radius:14px;background:#f8f9fb;color:#697180;font-size:12px;line-height:1.7}.snm-savebar{position:fixed;left:0;right:0;bottom:0;z-index:30;padding:10px 12px calc(10px + env(safe-area-inset-bottom,0px));background:rgba(255,255,255,.96);border-top:1px solid #edf0f5;backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px)}.snm-save{width:100%;height:46px;border:0;border-radius:16px;background:linear-gradient(135deg,var(--theme-primary,#667eea),var(--theme-secondary,#764ba2));color:#fff;font-size:14px;font-weight:900;box-shadow:0 10px 24px rgba(var(--snm-primary-rgb),.18)}.tox-tinymce{border-radius:12px!important;border:1px solid #edf0f5!important;overflow:hidden}
    @media(max-width:360px){.snm-page{padding-left:10px;padding-right:10px}}
</style>

<main class="snm-page">
<form id="form-notice">
        <section class="snm-card">
            <div class="snm-card-head"><div class="snm-card-icon"><i class="fa fa-commenting-o"></i></div><div><h2 class="snm-card-title">滚动公告</h2><p class="snm-card-desc">适合放置促销、维护、发货提醒等短消息。</p></div></div>
            <label><span class="snm-label">公告内容</span><textarea placeholder="请输入滚动公告内容" name="roll_notice" class="ss-textarea"><?= htmlspecialchars($_rollNotice) ?></textarea><span class="snm-tip">不换行 = 单条横向滚动；换行多条 = 移动端自动上下滚动。</span></label>
            <div class="snm-preview"><i class="fa fa-lightbulb-o"></i> 建议控制在 30 字以内，内容越短越容易被用户看到。</div>
        </section>

        <section class="snm-card">
            <div class="snm-card-head"><div class="snm-card-icon"><i class="fa fa-file-text-o"></i></div><div><h2 class="snm-card-title">内容公告</h2><p class="snm-card-desc">展示在首页公告区域，可使用富文本排版。</p></div></div>
            <label><span class="snm-label">公告详情</span><textarea id="home_notice_editor" name="home_notice" class="ss-textarea" style="min-height:220px;"><?= htmlspecialchars($_homeNotice) ?></textarea><span class="snm-tip">支持加粗、链接、图片等富文本内容。</span></label>
        </section>
    </form>
    <div class="snm-savebar"><button type="button" class="snm-save" id="btn-save-notice"><i class="fa fa-check"></i> 保存站内公告</button></div>
</main>

<script src="<?= DC_URL ?>admin/tinymce/tinymce.min.js?t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>
<script>
(function() {
    function boot() {
        if (!window.jQuery || !window.layui || !window.layui.use) { setTimeout(boot, 80); return; }
        layui.use(['layer'], function() {
            var layer = layui.layer;
            var _uploadUrl = <?= json_encode(DC_URL . 'user/article.php?action=upload_cover2') ?>;
            tinymce.init({
                selector: '#home_notice_editor',
                language: 'zh_CN', height: 280,
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

            $('#btn-save-notice').on('click', function() {
                if (window.tinymce) tinymce.triggerSave();
                var data = $('#form-notice').serialize();
                var $btn = $(this);
                $btn.prop('disabled', true).text('保存中...');
                $.ajax({
                    type: 'POST', url: '?action=setting_notice_ajax', data: data, dataType: 'json',
                    success: function(e) {
                        $btn.prop('disabled', false).html('<i class="fa fa-check"></i> 保存站内公告');
                        if (e.code == 400) { layer.msg(e.msg, {icon: 2}); }
                        else { layer.msg(e.msg || '保存成功', {icon: 1}); }
                    },
                    error: function() {
                        $btn.prop('disabled', false).html('<i class="fa fa-check"></i> 保存站内公告');
                        layer.msg('网络请求发生错误，请重试', {icon: 2});
                    }
                });
            });
        });
    }
    boot();
})();
</script>

