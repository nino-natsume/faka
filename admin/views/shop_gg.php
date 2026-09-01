<?php defined('DC_ROOT') || exit('access denied!'); ?>

<div class="layui-tabs order-tabs-wrapper" style="display:flex;align-items:center;justify-content:space-between;" lay-options="{trigger: false}">
    <ul class="layui-tabs-header">
        <li><a href="./shop.php">商城配置</a></li>
        <li class="layui-this"><a href="./shop.php?action=gg">公告设置</a></li>
        <li><a href="./shop.php?action=btx">下单输入框</a></li>
        <li><a href="./shop.php?action=user">用户配置</a></li>
        <li><a href="./shop.php?action=station_setting">分店配置</a></li>
    </ul>
</div>
<div class="layui-table-view" style="border-radius:8px;overflow:hidden;">
    <div class="layui-card-header" style="display:flex;align-items:center;justify-content:center;position:relative;height:auto;padding:12px 15px;">
        <span style="position:absolute;left:15px;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">公告设置</span>
    </div>
    <div class="layui-card-body" style="padding:20px;">
        <blockquote class="layui-elem-quote">
            <i class="ri-information-line"></i> 当不输入任何公告内容时，前端页面将不显示相关公告区域
        </blockquote>
        
        <form action="shop.php?action=gg_save" method="post" name="setting_form" id="setting_form" class="layui-form">
            <div class="layui-form-item">
                <label class="layui-form-label">滚动公告</label>
                <div class="layui-input-block">
                    <textarea name="roll_bulletin" placeholder="请输入内容" class="layui-textarea"><?= $roll_bulletin ?></textarea>
                    <div class="layui-form-mid layui-word-aux">不换行：前端展示为单条横向滚动公告（右→左滚动）。换行多条：每行一条，移动端个人中心会自动智能识别并展示为上下滚动公告。</div>
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">内容公告</label>
                <div class="layui-input-block">
                    <textarea id="home_bulletin" name="home_bulletin"><?= $home_bulletin ?></textarea>
                </div>
            </div>
            <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
            <div class="layui-form-item" style="text-align:center;margin-top:10px;">
                <button type="submit" class="layui-btn" lay-submit lay-filter="demo1">保存设置</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </form>
    </div>
</div>


<script src="./tinymce/tinymce.min.js?t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>
<script>
    $(function () {
        // 提交表单
        $("#setting_form").submit(function (event) {
            event.preventDefault();
            submitForm("#setting_form");
        });
    });

    $(function() {
        const example_image_upload_handler = (blobInfo, progress) => new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.withCredentials = false;
            xhr.open('POST', '/admin/article.php?action=upload_cover2');
            xhr.upload.onprogress = (e) => {
                progress(e.loaded / e.total * 100);
            };
            xhr.onload = () => {
                if (xhr.status === 403) {
                    reject({ message: 'HTTP Error: ' + xhr.status, remove: true });
                    return;
                }
                if (xhr.status < 200 || xhr.status >= 300) {
                    reject('HTTP Error: ' + xhr.status);
                    return;
                }
                const json = JSON.parse(xhr.responseText);
                if (!json || typeof json.location != 'string') {
                    reject('Invalid JSON: ' + xhr.responseText);
                    return;
                }
                resolve(json.location);
            };
            xhr.onerror = () => {
                reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
            };
            const formData = new FormData();
            formData.append('image', blobInfo.blob(), blobInfo.filename());
            xhr.send(formData);
        });
        tinymce.init({
            selector: 'textarea#home_bulletin',
            language: 'zh_CN',
            height: 500,
            images_upload_handler: example_image_upload_handler,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'wordcount', 'autosave'
            ],
            autosave_ask_before_unload: false,
            toolbar: 'undo redo | blocks | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }',


            // 添加初始化完成后的回调
            setup: function(editor) {
                editor.on('init', function() {
                    // 保存编辑器实例到全局变量
                    editorInstance = editor;
                    console.log('TinyMCE 初始化完成');
                });
                editor.on('input change undo redo cut paste', function() {
                    // 手动更新关联的文本域
                    editor.save();
                });
            }
        }).then(function(editors) {
            // 可选：Promise 方式获取编辑器实例
            if (editors && editors.length > 0) {
                editorInstance = editors[0];
            }
        }).catch(function(error) {
            console.error('TinyMCE 初始化失败:', error);
        });
    });
</script>
