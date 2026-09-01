<?php defined('DC_ROOT') || exit('access denied!'); ?>

<div style="padding: 20px 0px;">

    <form class="layui-form" id="form" method="post" action="article_save.php" style="padding: 20px; background: #fff;">
        <input type="hidden" name="ishide" id="ishide" value="<?= $hide ?>" />
        <input type="hidden" name="as_logid" id="as_logid" value="<?= $logid ?>" />
        <input type="hidden" name="gid" id="gid" value="<?= $logid ?>" />
        <input type="hidden" name="author" id="author" value="<?= $author ?>" />

        <div class="layui-form-item">
            <label class="layui-form-label">文章标题</label>
            <div class="layui-input-block">
                <input type="text" name="title" class="layui-input" value="<?= $title ?>">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">文章内容</label>
            <div class="layui-input-block">
                <textarea class="basic-example" name="content"><?= $content ?></textarea>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">摘要（选填）</label>
            <div class="layui-input-block">
                <textarea class="layui-textarea" name="logexcerpt"><?= $excerpt ?></textarea>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">封面图</label>
            <div class="layui-input-block">
                <div class="layui-input-group" style="width: 100%; display: flex;">
                    <input type="text" value="<?= $cover ?>" placeholder="封面图" class="layui-input" name="cover" id="sortimg">
                    <div id="ID-upload-demo-btn" class="layui-input-split layui-input-suffix layui-btn" style="display: table-cell; line-height: 192%;">
                        上传图片
                    </div>
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">文章分类</label>
            <div class="layui-input-block">
                <select name="sort">
                    <option value="">选择分类</option>
                    <?php
                    $renderArticleSortOptions = function ($pid = 0, $level = 0) use (&$renderArticleSortOptions, $sorts, $sortid) {
                        foreach ($sorts as $key => $value) {
                            if ((int)$value['pid'] !== (int)$pid) {
                                continue;
                            }
                            $flg = (int)$value['sid'] === (int)$sortid ? ' selected' : '';
                            echo '<option value="' . (int)$value['sid'] . '"' . $flg . '>' . str_repeat('&nbsp;&nbsp;&nbsp;', $level) . ($level > 0 ? '└ ' : '') . $value['sortname'] . '</option>';
                            $renderArticleSortOptions($key, $level + 1);
                        }
                    };
                    $renderArticleSortOptions();
                    ?>
                </select>
            </div>
        </div>

        <?php if (!empty($customTemplates)): ?>
        <div class="layui-form-item">
            <label class="layui-form-label">文章模板</label>
            <div class="layui-input-block">
                <select name="template">
                    <option value="">默认文章模板</option>
                    <?php foreach ($customTemplates as $tpl): ?>
                        <option value="<?= $tpl['filename'] ?>" <?= $template === $tpl['filename'] ? 'selected' : '' ?>><?= $tpl['comment'] ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-tips">可选择当前博客模板中的 echo_log_xxx.php。</div>
            </div>
        </div>
        <?php endif; ?>

        <div class="layui-form-item">
            <label class="layui-form-label">标签</label>
            <div class="layui-input-block">
                <input type="text" name="tag" class="layui-input" value="<?= $tagStr ?>">
                <div class="form-tips">也用于页面关键词，英文逗号分隔</div>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">跳转链接</label>
            <div class="layui-input-block">
                <input type="text" name="link" class="layui-input" value="<?= $link ?>">
                <div class="form-tips">填写后不展示文章内容直接跳转该地址</div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-input-block">
                <input type="checkbox" value="y" name="allow_remark" title="允许评论" <?= $is_allow_remark ?>>
            </div>
            <div class="layui-input-block">
                <input type="checkbox" value="y" name="top" title="首页置顶" <?= $is_top ?>>
            </div>
            <div class="layui-input-block">
                <input type="checkbox" value="y" name="sortop" title="分类置顶" <?= $is_sortop ?>>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">链接别名</label>
            <div class="layui-input-block">
                <input type="text" name="alias" class="layui-input" value="<?= $alias ?>">
                <div class="form-tips">英文字母、数字组成，用于<a href="./setting.php?action=seo">seo设置</a></div>
            </div>
        </div>


        <div class="layui-input-block" style="margin: 0 auto;">
            <button type="submit" class="layui-btn" lay-submit lay-filter="submit">立即发布</button>
        </div>

    </form>

</div>


<script src="./tinymce/tinymce.min.js?t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>


<script>
    layui.use(['form', 'laydate', 'util'], function(){
        var $ = layui.$;
        var form = layui.form;
        var upload = layui.upload;
        var element = layui.element;
        form.on('submit(submit)', function(data){
            var field = data.field; // 获取表单全部字段值
            var url = $('#form').attr('action');
            $.ajax({
                type: "POST",
                url: url,
                data: field,
                dataType: "json",
                success: function (e) {
                    if(e.code == 400){
                        return layer.msg(e.msg)
                    }
                    location.href = "article.php"
                },
                error: function (xhr) {
                    layer.msg(JSON.parse(xhr.responseText).msg);
                }
            });
            return false; // 阻止默认 form 跳转
        });


        var uploadInst = upload.render({
            elem: '#ID-upload-demo-btn',
            field: 'image',
            url: './article.php?action=upload_cover', // 实际使用时改成您自己的上传接口即可。
            before: function(obj){
                // 预读本地文件示例，不支持ie8
                obj.preview(function(index, file, result){
                    $('#ID-upload-demo-img').attr('src', result); // 图片链接（base64）
                });

                element.progress('filter-demo', '0%'); // 进度条复位
                loadIndex = layer.load(2);
            },
            done: function(res){
                if(res.code == 400){
                    return layer.msg(res.msg)
                }
                // 若上传失败
                if(res.code > 0){
                    return layer.msg('上传失败');
                }
                // 上传成功的一些操作
                if(res.code == 0){
                    $('#sortimg').val(res.data)
                }
                $('#ID-upload-demo-text').html(''); // 置空上传失败的状态
            },
            error: function(){
                // 演示失败状态，并实现重传
                var demoText = $('#ID-upload-demo-text');
                demoText.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-xs demo-reload">重试</a>');
                demoText.find('.demo-reload').on('click', function(){
                    uploadInst.upload();
                });
            },
            // 进度条
            progress: function(n, elem, e){
                element.progress('filter-demo', n + '%'); // 可配合 layui 进度条元素使用
                if(n == 100){
                    layer.close(loadIndex)
                }
            }
        });


    })
</script>


<script>
    $("#alias").keyup(function() {
        checkalias();
    });
    $(function(){

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

        // 编辑器
        tinymce.init({
            selector: 'textarea.basic-example',
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
    })


    // 文章编辑界面全局快捷键 Ctrl（Cmd）+ S 保存内容
    document.addEventListener('keydown', function(e) {
        if (e.keyCode == 83 && (navigator.platform.match("Mac") ? e.metaKey : e.ctrlKey)) {
            e.preventDefault();
            autosave(2);
        }
    });

    // 显示插件扩展label
    const postBar = $("#post_bar");
    if (postBar.children().length === 0) {
        $("#post_bar_label").hide();
    }

    // 自定义字段
    $(document).on('click', '.field_del', function() {
        $(this).closest('.field_list').remove();
    });
    $(document).on('click', '.field_add', function() {
        var newField = `
                    <div class="form-row field_list">
                        <div class="col-sm-4">
                            <input type="text" name="field_keys[]" list="customFieldList" value="" id="field_keys" class="form-control" placeholder="字段名称" maxlength="120" required>
                            <datalist id="customFieldList">
                                <?php foreach ($customFields as $k => $v): ?>
                                    <option value="<?= $k ?>"><?= $k . '【' . $v['name'] . '】' . $v['description'] ?></option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-sm-6 mx-sm-3">
                            <input type="text" name="field_values[]" value="" id="field_values" class="form-control" placeholder="字段值" required>
                        </div>
                        <div class="col-auto mt-1">
                            <button type="button" class="btn btn-outline-danger field_del">删除</button>
                        </div>
                    </div>
                `;
        $('#field_box').append(newField);
    });

    // 高级选项展开状态
    initDisplayState('adv_set');
    // 自动截取摘要状态
    initCheckboxState('auto_excerpt');
    // 自动提取封面状态
    initCheckboxState('auto_cover');
</script>