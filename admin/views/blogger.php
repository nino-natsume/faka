<?php defined('DC_ROOT') || exit('access denied!'); ?>

<!-- Cropper.js -->
<link rel="stylesheet" href="<?= DC_URL ?>admin/views/css/cropper.min.css">
<script src="<?= DC_URL ?>admin/views/js/cropper.min.js"></script>

<style>
/* 头像裁剪弹窗样式 */
.avatar-cropper-container {
    padding: 20px;
    text-align: center;
}
.avatar-cropper-container .img-container {
    max-width: 100%;
    max-height: 400px;
    margin: 0 auto;
}
.avatar-cropper-container .img-container img {
    display: block;
    max-width: 100%;
}
.avatar-cropper-btns {
    padding: 15px 20px;
    text-align: center;
    border-top: 1px solid #eee;
}
/* 头像点击样式 */
#avatar_image {
    cursor: pointer;
    border-radius: 50%;
    transition: opacity 0.3s;
}
#avatar_image:hover {
    opacity: 0.8;
}
.profilecfg-section { background: #fff; border: 1px solid #eef1f4; border-radius: 8px; padding: 18px 20px; margin-bottom: 14px; }
.profilecfg-title { font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; }
.profilecfg-title i { color: #2563eb; }
.profilecfg-row { display: grid; grid-template-columns: 160px 1fr; gap: 10px; align-items: start; padding: 8px 0; }
.profilecfg-row > label { color: #374151; font-weight: 500; padding-top: 10px; }
.profilecfg-row .layui-input-block { margin-left: 0; }
.profilecfg-row .layui-input { max-width: 860px; }
.profilecfg-tip { color: #6b7280; font-size: 12px; line-height: 1.7; margin-top: 8px; }
.profilecfg-avatar-box { display: flex; align-items: center; gap: 18px; flex-wrap: wrap; }
.profilecfg-actions { text-align: center; margin-top: 10px; }
</style>

<div class="layui-tabs order-tabs-wrapper" style="display:flex;align-items:center;justify-content:space-between;" lay-options="{trigger: false}">
    <ul class="layui-tabs-header">
        <li><a href="./setting.php">系统配置</a></li>
        <li><a href="./setting.php?action=user">用户配置</a></li>
        <li><a href="./setting.php?action=seo">SEO设置</a></li>
        <li><a href="./setting.php?action=mail">邮箱配置</a></li>
        <li class="layui-this"><a href="./blogger.php">个人信息</a></li>
    </ul>
</div>
<div class="layui-card" style="border-radius:8px;overflow:hidden;">
    <div class="layui-card-header" style="display:flex;align-items:center;justify-content:center;position:relative;height:auto;padding:12px 15px;">
        <span style="position:absolute;left:15px;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">个人信息</span>
    </div>
    <div class="layui-card-body" style="padding:20px;">
        <form action="blogger.php?action=update" method="post" name="profile_setting_form" id="profile_setting_form" class="layui-form">
            <div class="profilecfg-section">
                <div class="profilecfg-title"><i class="ri-account-circle-line"></i>头像与资料</div>
                <div class="profilecfg-row">
                    <label>当前头像</label>
                    <div>
                        <div class="profilecfg-avatar-box">
                            <label for="upload_image" style="cursor: pointer;">
                                <img src="<?= $icon ?>" width="120" height="120" id="avatar_image" title="点击更换头像"/>
                                <input type="file" name="image" id="upload_image" accept="image/*" style="display:none"/>
                            </label>
                            <div class="profilecfg-tip">点击头像更换。上传后可先裁剪，再保存为新的管理员头像。</div>
                        </div>
                    </div>
                </div>
                <div class="profilecfg-row">
                    <label>昵称</label>
                    <div>
                        <input class="layui-input" value="<?= $nickname ?>" name="name" maxlength="20" required>
                        <div class="profilecfg-tip">用于后台顶部和部分资料展示，建议填写便于识别的昵称。</div>
                    </div>
                </div>
                <div class="profilecfg-row">
                    <label>登录账号</label>
                    <div>
                        <input class="layui-input" value="<?= $username ?>" name="username" id="username">
                        <div class="profilecfg-tip">保存后将作为新的后台登录账号，请确保唯一且便于记忆。</div>
                    </div>
                </div>
            </div>
            <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
            <div class="profilecfg-actions">
                <button type="submit" class="layui-btn" lay-submit lay-filter="demo1">保存设置</button>
                <a href="javascript:;" class="layui-btn layui-bg-blue" id="editPasswordModal">修改密码</a>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </form>
    </div>
</div>

<div style="height: 96px;"></div>

<script>
layui.use(['layer', 'form'], function() {
    var $ = layui.$;
    var layer = layui.layer;
    var form = layui.form;
    var cropper = null;
    var cropperLayerIndex = null;

    // 菜单高亮
    $("#menu-system").addClass('has-list in');
    $("#menu-system .fa-angle-right").addClass('active');
    $('#menu-setting > a').addClass('active');

    // 提交表单
    $("#profile_setting_form").submit(function(event) {
        event.preventDefault();
        submitForm("#profile_setting_form");
    });

    // 修改密码弹窗
    $('#editPasswordModal').click(function() {
        layer.open({
            type: 1,
            area: '350px',
            resize: false,
            shadeClose: true,
            title: '修改密码',
            content: `
                <div class="layui-form" lay-filter="pwd-form" style="padding: 20px;">
                    <div class="layui-form-item">
                        <div class="layui-input-wrap">
                            <div class="layui-input-prefix"><i class="ri-lock-password-line"></i></div>
                            <input type="password" name="new_passwd" lay-verify="required" placeholder="新密码" class="layui-input" lay-affix="eye">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-input-wrap">
                            <div class="layui-input-prefix"><i class="ri-lock-password-line"></i></div>
                            <input type="password" name="new_passwd2" lay-verify="required" placeholder="确认密码" class="layui-input" lay-affix="eye">
                        </div>
                    </div>
                    <input name="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
                    <div class="layui-form-item">
                        <button class="layui-btn layui-btn-fluid" lay-submit lay-filter="pwd-save">保存密码</button>
                    </div>
                </div>
            `,
            success: function(layero, index) {
                form.render(null, 'pwd-form');
                form.on('submit(pwd-save)', function(data) {
                    $.ajax({
                        type: "POST",
                        url: "blogger.php?action=change_password",
                        data: data.field,
                        dataType: "json",
                        success: function(e) {
                            if (e.code == 400) return layer.msg(e.msg);
                            layer.close(index);
                            layer.msg('修改成功');
                        },
                        error: function(xhr) {
                            layer.msg(JSON.parse(xhr.responseText).msg);
                        }
                    });
                    return false;
                });
            }
        });
    });

    // 头像上传和裁剪
    $('#upload_image').change(function(event) {
        var files = event.target.files;
        if (!files || files.length === 0) return;
        
        var file = files[0];
        if (!file.type.startsWith('image')) {
            layer.msg('只能上传图片');
            return;
        }

        var reader = new FileReader();
        reader.onload = function(e) {
            openCropperDialog(e.target.result, file);
        };
        reader.readAsDataURL(file);
        
        // 清空input，允许重复选择同一文件
        $(this).val('');
    });

    // 打开裁剪弹窗
    function openCropperDialog(imageSrc, originalFile) {
        var content = `
            <div class="avatar-cropper-container">
                <div class="img-container">
                    <img id="cropper-image" src="${imageSrc}">
                </div>
            </div>
            <div class="avatar-cropper-btns">
                <button type="button" class="layui-btn layui-btn-primary" id="btn-cancel">取消</button>
                <button type="button" class="layui-btn" id="btn-use-original">使用原图</button>
                <button type="button" class="layui-btn layui-bg-green" id="btn-crop">裁剪并保存</button>
            </div>
        `;

        cropperLayerIndex = layer.open({
            type: 1,
            title: '裁剪头像',
            area: ['500px', '550px'],
            content: content,
            shadeClose: false,
            move: false,
            success: function(layero, index) {
                var image = document.getElementById('cropper-image');
                
                // 初始化裁剪器
                cropper = new Cropper(image, {
                    aspectRatio: 1,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.8,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false
                });

                // 取消按钮
                $('#btn-cancel').click(function() {
                    destroyCropper();
                    layer.close(index);
                });

                // 使用原图
                $('#btn-use-original').click(function() {
                    uploadAvatar(originalFile, originalFile.name, index);
                });

                // 裁剪并保存
                $('#btn-crop').click(function() {
                    if (!cropper) return;
                    
                    var canvas = cropper.getCroppedCanvas({
                        width: 200,
                        height: 200
                    });

                    canvas.toBlob(function(blob) {
                        uploadAvatar(blob, 'avatar.jpg', index);
                    }, 'image/jpeg', 0.9);
                });
            },
            end: function() {
                destroyCropper();
            }
        });
    }

    // 销毁裁剪器
    function destroyCropper() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    }

    // 上传头像
    function uploadAvatar(blob, filename, layerIndex) {
        var formData = new FormData();
        formData.append('image', blob, filename);

        var loadIndex = layer.load(2);

        $.ajax({
            url: './blogger.php?action=update_avatar',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
                layer.close(loadIndex);
                if (data.code == 0) {
                    $('#avatar_image').attr('src', data.data + '?t=' + new Date().getTime());
                    layer.msg('头像更新成功');
                    destroyCropper();
                    layer.close(layerIndex);
                } else {
                    layer.msg(data.msg || '上传失败');
                }
            },
            error: function(xhr) {
                layer.close(loadIndex);
                var data = xhr.responseJSON;
                layer.msg(data && data.msg ? data.msg : '上传头像出错了');
            }
        });
    }
});
</script>
