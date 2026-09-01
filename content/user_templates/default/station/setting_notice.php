<?php
defined('DC_ROOT') || exit('access denied!');
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

    .ss-form-footer { display: flex; align-items: center; justify-content: flex-end; gap: 10px; padding: 16px 28px; border-top: 1px solid var(--card-border, #e2e8f0); background: var(--bg-secondary, #f8fafc); }
    .ss-btn-save { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-width: 140px; height: 44px; padding: 0 22px; border: 0; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; transition: .18s; background: linear-gradient(135deg, var(--tp-dark), var(--tp-light)); color: #fff; box-shadow: 0 8px 20px rgba(var(--tp-rgb),.18); }
    .ss-btn-save:hover { transform: translateY(-1px); box-shadow: 0 12px 28px rgba(var(--tp-rgb),.24); }

    .tox-tinymce { border-radius: 10px !important; border: 1.5px solid var(--input-border, #e2e8f0) !important; overflow: hidden; }

    @media (max-width: 768px) {
        .ss-form-body { padding: 20px; }
        .ss-form-footer { padding: 14px 20px; }
    }
</style>

<main class="ss-page">
    <div class="ss-page-header">
        <a href="?action=setting" class="ss-back-btn"><i class="fa fa-arrow-left"></i></a>
        <div>
            <h1 class="ss-page-title">站内公告</h1>
            <p class="ss-page-desc">滚动公告和内容公告，向访客展示重要信息</p>
        </div>
    </div>

    <div class="ss-form-card">
        <form id="form-notice">
            <div class="ss-form-body">
                <div class="ss-form-grid">
                    <div class="ss-form-item">
                        <label class="ss-label"><i class="fa fa-commenting-o" style="color:#d97706;"></i> 滚动公告</label>
                        <textarea placeholder="请输入滚动公告内容" name="roll_notice" class="ss-textarea"><?= htmlspecialchars($userStation['roll_notice']) ?></textarea>
                        <span class="ss-tips">不换行 = 单条横向滚动；换行多条 = 移动端自动上下滚动。</span>
                    </div>
                    <div class="ss-form-item">
                        <label class="ss-label"><i class="fa fa-file-text-o" style="color:#d97706;"></i> 内容公告</label>
                        <textarea id="home_notice_editor" name="home_notice" class="ss-textarea" style="min-height:200px;"><?= htmlspecialchars($userStation['home_notice']) ?></textarea>
                    </div>
                </div>
            </div>
        </form>
        <div class="ss-form-footer">
            <button type="button" class="ss-btn-save" id="btn-save-notice"><i class="fa fa-check"></i> 保存</button>
        </div>
    </div>
</main>

<script src="<?= DC_URL ?>admin/tinymce/tinymce.min.js?t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>
<script>
(function() {
    function boot() {
        if (!window.jQuery || !window.layui || !window.layui.use) { setTimeout(boot, 80); return; }
        layui.use(['layer'], function() {
            var layer = layui.layer;
            var _uploadUrl = <?= json_encode(DC_URL . 'user/article.php?action=upload_cover2') ?>;

            // 初始化 TinyMCE
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
                content_style: 'body { font-family: Helvetica, Arial, sans-serif; font-size: 15px }',
                setup: function(editor) { editor.on('input change undo redo cut paste', function() { editor.save(); }); }
            });

            // 保存
            $('#btn-save-notice').on('click', function() {
                if (window.tinymce) tinymce.triggerSave();
                var data = $('#form-notice').serialize();
                var $btn = $(this);
                $btn.prop('disabled', true).text('保存中...');
                $.ajax({
                    type: 'POST', url: '?action=setting_notice_ajax', data: data, dataType: 'json',
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
