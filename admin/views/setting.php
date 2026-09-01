<?php defined('DC_ROOT') || exit('access denied!'); ?>

<style>
    .scfg-section { background: #ffffff85; border: 1px solid #eef1f4; border-radius: 8px; padding: 18px 20px; margin-bottom: 14px; }
    .scfg-title { font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; }
    .scfg-title i { color: #2563eb; }
    .scfg-row { display: grid; grid-template-columns: 160px 1fr; gap: 10px; align-items: start; padding: 8px 0; }
    .scfg-row > label { color: #374151; font-weight: 500; padding-top: 10px; }
    .scfg-row .layui-input-block { margin-left: 0; }
    .scfg-row .layui-input,
    .scfg-row .layui-textarea,
    .scfg-row .layui-form-select,
    .scfg-row .layui-select-title,
    .scfg-row select { max-width: 860px; }
    .scfg-tip { color: #6b7280; font-size: 12px; line-height: 1.7; margin-top: 8px; }
    .scfg-tip b { color: #2563eb; }
    .scfg-upload-group { width: 100%; max-width: 860px; display: flex; }
    .scfg-upload-meta { display: flex; align-items: center; gap: 12px; margin-top: 10px; }
    .scfg-upload-preview { width: 56px; height: 56px; border: 1px dashed #d1d5db; border-radius: 10px; background: #f9fafb; display: flex; align-items: center; justify-content: center; overflow: hidden; color: #9ca3af; font-size: 12px; flex-shrink: 0; }
    .scfg-upload-preview img { width: 100%; height: 100%; object-fit: contain; display: block; }
    .scfg-actions { text-align: center; margin-top: 10px; }

    @media (max-width: 768px) {
        .layui-card-body { padding: 12px !important; }
        .scfg-row { grid-template-columns: 1fr; gap: 4px; }
        .scfg-row > label { padding-top: 0; font-size: 13px; }
        .scfg-section { padding: 14px 12px; }
        .scfg-upload-group { max-width: 100%; }
        .scfg-upload-meta { flex-wrap: wrap; }
        .scfg-row .layui-input,
        .scfg-row .layui-textarea,
        .scfg-row .layui-form-select,
        .scfg-row .layui-select-title,
        .scfg-row select { max-width: 100%; }
    }
</style>

<div class="layui-tabs order-tabs-wrapper" style="display:flex;align-items:center;justify-content:space-between;" lay-options="{trigger: false}">
    <ul class="layui-tabs-header">
        <li class="layui-this"><a href="./setting.php">系统配置</a></li>
        <li><a href="./setting.php?action=blog">博客配置</a></li>
        <li><a href="./setting.php?action=agreement">协议管理</a></li>
        <li><a href="./setting.php?action=seo">SEO设置</a></li>
        <li><a href="./setting.php?action=mail">邮箱配置</a></li>
    </ul>
</div>
<div class="layui-table-view" style="border-radius:8px;overflow:hidden;">
    <div class="layui-card-header" style="display:flex;align-items:center;justify-content:center;position:relative;height:auto;padding:12px 15px;">
        <span style="position:absolute;left:15px;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">系统配置</span>
    </div>
    <div class="layui-card-body" style="padding:20px;">
        <form action="setting.php?action=save" method="post" name="setting_form" id="setting_form" class="layui-form">
            <div class="scfg-section">
                <div class="scfg-title"><i class="ri-settings-3-line"></i>站点信息</div>
                <div class="scfg-row">
                    <label>站点标题</label>
                    <div>
                        <input class="layui-input" value="<?= $blogname ?>" name="blogname">
                        <div class="scfg-tip">前台与后台的主标题默认使用这里的名称。</div>
                    </div>
                </div>
                <div class="scfg-row">
                    <label>网站副标题</label>
                    <div>
                        <input class="layui-input" value="<?= isset($site_subtitle) ? $site_subtitle : '' ?>" name="site_subtitle" placeholder="显示在站点标题下方的副标题">
                        <div class="scfg-tip">用于首页和导航区域的辅助说明文案。</div>
                    </div>
                </div>
                <div class="scfg-row">
                    <label>博客名称</label>
                    <div>
                        <input class="layui-input" value="<?= isset($blog_site_name) ? $blog_site_name : '' ?>" name="blog_site_name" placeholder="博客页面显示的名称，留空则使用站点标题">
                        <div class="scfg-tip">博客页可单独使用不同名称，留空则回退到站点标题。</div>
                    </div>
                </div>
                <div class="scfg-row">
                    <label>站点地址</label>
                    <div>
                        <input class="layui-input readonly" value="<?= $blogurl ?>" name="blogurl" type="url" readonly required>
                        <div style="margin-top:10px;">
                            <input type="checkbox" name="detect_url" id="detect_url" value="y" <?= $conf_detect_url ?> title="自动检测站点地址">
                        </div>
                        <div class="scfg-tip">如开启后首页样式异常，请关闭自动检测并手动填写正确域名。</div>
                    </div>
                </div>
                <div class="scfg-row">
                    <label>选择时区</label>
                    <div>
                        <select name="timezone">
                            <?php foreach ($tzlist as $key => $value):
                                $ex = $key == $timezone ? "selected=\"selected\"" : '' ?>
                                <option value="<?= $key ?>" <?= $ex ?>><?= $value ?></option>
                            <?php endforeach ?>
                        </select>
                        <div class="scfg-tip">影响订单、日志和通知时间的显示口径。</div>
                    </div>
                </div>
                <div class="scfg-row">
                    <label>ICP备案号</label>
                    <div>
                        <input class="layui-input" value="<?= $icp ?>" name="icp"/>
                        <div class="scfg-tip">显示在前台底部备案信息区域。</div>
                    </div>
                </div>
            </div>

            <div class="scfg-section">
                <div class="scfg-title"><i class="ri-shield-check-line"></i>图形验证码</div>
                <div class="scfg-row">
                    <label>验证码开关</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="login_code" value="y" lay-skin="switch" lay-text="开启|关闭" <?= $login_code == 'y' ? 'checked' : '' ?>>
                        </div>
                        <div class="scfg-tip">开启后登录、注册、找回密码页面将显示图形验证码。需要服务器支持 GD 库。</div>
                    </div>
                </div>
                <div class="scfg-row">
                    <label>验证码类型</label>
                    <div>
                        <select name="captcha_type">
                            <option value="num" <?= ($captcha_type ?? 'num') === 'num' ? 'selected' : '' ?>>纯数字验证码</option>
                            <option value="alpha" <?= ($captcha_type ?? '') === 'alpha' ? 'selected' : '' ?>>纯字母验证码</option>
                            <option value="mix" <?= ($captcha_type ?? '') === 'mix' ? 'selected' : '' ?>>字母+数字混合验证码</option>
                            <option value="zh" <?= ($captcha_type ?? '') === 'zh' ? 'selected' : '' ?>>中文汉字验证码</option>
                            <option value="math" <?= ($captcha_type ?? '') === 'math' ? 'selected' : '' ?>>运算符验证码(+ - × ÷)</option>
                            <option value="random" <?= ($captcha_type ?? '') === 'random' ? 'selected' : '' ?>>随机验证码(上方5种随机)</option>
                        </select>
                        <div class="scfg-tip">选择验证码的内容类型，默认纯数字。</div>
                    </div>
                </div>
            </div>

            <div class="scfg-section">
                <div class="scfg-title"><i class="ri-file-text-line"></i>页脚与说明</div>
                <div class="scfg-row">
                    <label>首页底部信息</label>
                    <div>
                        <textarea name="footer_info" rows="6" class="layui-textarea"><?= $footer_info ?></textarea>
                        <div class="scfg-tip">支持 HTML，可用于版权说明、统计代码或补充说明。</div>
                    </div>
                </div>
            </div>

            <div class="scfg-section">
                <div class="scfg-title"><i class="ri-image-line"></i>站点资源</div>
                <div class="scfg-row">
                    <label>登录图标</label>
                    <div>
                        <div class="layui-input-group scfg-upload-group">
                            <input type="text" value="<?= $personal_center_icon ?>" placeholder="请输入或上传图片地址" class="layui-input" name="personal_center_icon" id="login-bg">
                            <div id="login-bg-btn" class="layui-input-split layui-input-suffix layui-btn" style="display: table-cell; line-height: 192%;">上传图片</div>
                        </div>
                        <div class="scfg-upload-meta">
                            <div class="scfg-upload-preview" id="personal-center-icon-preview" data-default="" data-placeholder="暂无">
                                <?php if (!empty($personal_center_icon)): ?>
                                <img src="<?= htmlspecialchars(getFileUrl($personal_center_icon)) ?>" alt="个人中心图标预览">
                                <?php else: ?>
                                <span>暂无</span>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="layui-btn layui-btn-sm layui-btn-primary scfg-clear-btn" data-input="#login-bg" data-preview="#personal-center-icon-preview">清空</button>
                        </div>
                        <div class="scfg-tip">用于用户/后台登录页头像图标展示，建议使用 <b>64×64</b> 左右的方形透明 PNG / JPG。</div>
                    </div>
                </div>
                <div class="scfg-row">
                    <label>网站 Logo</label>
                    <div>
                        <div class="layui-input-group scfg-upload-group">
                            <input type="text" value="<?= $logo ?>" placeholder="留空使用默认 Logo" class="layui-input" name="logo" id="logo">
                            <div id="logo-btn" class="layui-input-split layui-input-suffix layui-btn" style="display: table-cell; line-height: 192%;">上传图片</div>
                        </div>
                        <div class="scfg-upload-meta">
                            <div class="scfg-upload-preview" id="logo-preview" data-default="<?= DC_URL ?>admin/views/images/logo.apng" data-placeholder="默认">
                                <img src="<?= !empty($logo) ? htmlspecialchars(getFileUrl($logo)) : DC_URL . 'admin/views/images/logo.apng' ?>" alt="网站 Logo 预览">
                            </div>
                            <button type="button" class="layui-btn layui-btn-sm layui-btn-primary scfg-clear-btn" data-input="#logo" data-preview="#logo-preview">恢复默认</button>
                        </div>
                        <div class="scfg-tip">前台网站 Logo，建议横向图片，推荐尺寸 <b>180×60</b>，支持 `.png`、`.jpg`、`.webp`、`.svg`。</div>
                    </div>
                </div>
                <div class="scfg-row">
                    <label>前后台通用 Favicon</label>
                    <div>
                        <div class="layui-input-group scfg-upload-group">
                            <input type="text" value="<?= isset($admin_favicon) ? $admin_favicon : '' ?>" placeholder="留空使用默认图标" class="layui-input" name="admin_favicon" id="admin_favicon">
                            <div id="admin-favicon-btn" class="layui-input-split layui-input-suffix layui-btn" style="display: table-cell; line-height: 192%;">上传图标</div>
                        </div>
                        <div class="scfg-upload-meta">
                            <div class="scfg-upload-preview" id="admin-favicon-preview" data-default="<?= DC_URL ?>admin/views/images/favicon.ico" data-placeholder="默认">
                                <img src="<?= !empty($admin_favicon) ? htmlspecialchars(getSiteFaviconUrl()) : DC_URL . 'admin/views/images/favicon.ico' ?>" alt="Favicon 预览">
                            </div>
                            <button type="button" class="layui-btn layui-btn-sm layui-btn-primary scfg-clear-btn" data-input="#admin_favicon" data-preview="#admin-favicon-preview">恢复默认</button>
                        </div>
                        <div class="scfg-tip">用于前台站点、后台管理中心与浏览器标签页图标，推荐尺寸 <b>48×48</b>，支持 `.ico`、`.png`、`.jpg`、`.svg`。</div>
                    </div>
                </div>
            </div>

            <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
            <div class="scfg-actions">
                <button type="submit" class="layui-btn" lay-submit lay-filter="demo1">保存设置</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </form>
    </div>
</div>

<div style="height: 96px;"></div>

<script>
    layui.use(['table', 'upload', 'element'], function(){
        var $ = layui.$;
        var upload = layui.upload;
        var element = layui.element;
        var loadIndex = null;

        function updatePreview(inputSelector, previewSelector){
            var value = $.trim($(inputSelector).val());
            var preview = $(previewSelector);
            var defaultUrl = preview.data('default') || '';
            var placeholder = preview.data('placeholder') || '暂无';
            var url = value || defaultUrl;
            if (url && !/^(https?:)?\/\//i.test(url) && url.indexOf('data:') !== 0 && url.charAt(0) !== '/') {
                if (url.indexOf('../') === 0) {
                    url = '<?= DC_URL ?>' + url.substring(3);
                } else {
                    url = '<?= DC_URL ?>' + url.replace(/^\.\//, '');
                }
            }
            if (url) {
                preview.html('<img src="' + url + '" alt="预览">');
            } else {
                preview.html('<span>' + placeholder + '</span>');
            }
        }

        $('#login-bg, #logo, #admin_favicon').on('input change', function(){
            var previewMap = {
                '#login-bg': '#personal-center-icon-preview',
                '#logo': '#logo-preview',
                '#admin_favicon': '#admin-favicon-preview'
            };
            updatePreview('#' + this.id, previewMap['#' + this.id]);
        });

        $(document).on('click', '.scfg-clear-btn', function(){
            var inputSelector = $(this).data('input');
            var previewSelector = $(this).data('preview');
            $(inputSelector).val('');
            updatePreview(inputSelector, previewSelector);
        });

        $('#setting_form').on('reset', function(){
            setTimeout(function(){
                updatePreview('#login-bg', '#personal-center-icon-preview');
                updatePreview('#logo', '#logo-preview');
                updatePreview('#admin_favicon', '#admin-favicon-preview');
            }, 0);
        });

        var loginBg = upload.render({
            elem: '#login-bg-btn',
            field: 'image',
            accept: 'images',
            exts: 'png|jpg|jpeg|gif|webp|svg|ico',
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
                layer.close(loadIndex);
                if(res.code == 400){
                    return layer.msg(res.msg)
                }
                // 若上传失败
                if(res.code > 0){
                    return layer.msg('上传失败');
                }
                // 上传成功的一些操作
                if(res.code == 0){
                    $('#login-bg').val(res.data);
                    updatePreview('#login-bg', '#personal-center-icon-preview');
                }
                $('#ID-upload-demo-text').html(''); // 置空上传失败的状态
            },
            error: function(){
                layer.close(loadIndex);
                // 演示失败状态，并实现重传
                var demoText = $('#ID-upload-demo-text');
                demoText.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-xs demo-reload">重试</a>');
                demoText.find('.demo-reload').on('click', function(){
                    loginBg.upload();
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

        var upload2 = upload.render({
            elem: '#logo-btn',
            field: 'image',
            accept: 'images',
            exts: 'png|jpg|jpeg|gif|webp|svg',
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
                layer.close(loadIndex);
                if(res.code == 400){
                    return layer.msg(res.msg)
                }
                // 若上传失败
                if(res.code > 0){
                    return layer.msg('上传失败');
                }
                // 上传成功的一些操作
                if(res.code == 0){
                    $('#logo').val(res.data);
                    updatePreview('#logo', '#logo-preview');
                }
                $('#ID-upload-demo-text').html(''); // 置空上传失败的状态
            },
            error: function(){
                layer.close(loadIndex);
                // 演示失败状态，并实现重传
                var demoText = $('#ID-upload-demo-text');
                demoText.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-xs demo-reload">重试</a>');
                demoText.find('.demo-reload').on('click', function(){
                    upload2.upload();
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

        // 后台Favicon上传
        var faviconUpload = upload.render({
            elem: '#admin-favicon-btn',
            field: 'image',
            accept: 'images',
            exts: 'ico|png|jpg|jpeg|gif|webp|svg',
            url: './article.php?action=upload_cover',
            before: function(obj){
                loadIndex = layer.load(2);
            },
            done: function(res){
                layer.close(loadIndex);
                if(res.code == 400){
                    return layer.msg(res.msg)
                }
                if(res.code > 0){
                    return layer.msg('上传失败');
                }
                if(res.code == 0){
                    $('#admin_favicon').val(res.data);
                    updatePreview('#admin_favicon', '#admin-favicon-preview');
                    layer.msg('上传成功');
                }
            },
            error: function(){
                layer.close(loadIndex);
                layer.msg('上传失败');
            }
        });

    })
    // 提交表单
    $("#setting_form").submit(function (event) {
        event.preventDefault();
        submitForm("#setting_form");
    });

</script>
