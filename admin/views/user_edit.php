<?php defined('DC_ROOT') || exit('access denied!'); ?>
<style>
    #form-btn{
        background: #eee;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: 50px;
        line-height: 50px;
        margin: 0 auto;
        text-align: center;
    }
    body{
        overflow: hidden;
    }
</style>


<form class="layui-form " action="user.php?action=edit_ajax" id="form">
    <div style="padding: 25px;" id="open-box">
        <div class="layui-form-item">
            <label class="layui-form-label">登录账号</label>
            <div class="layui-input-block">
                <input type="text" name="username" class="layui-input" value="<?= $username ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">昵称</label>
            <div class="layui-input-block">
                <input type="text" name="nickname" class="layui-input" value="<?= $nickname ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">手机号码</label>
            <div class="layui-input-block">
                <input type="text" name="tel" class="layui-input" value="<?= $tel ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">邮箱</label>
            <div class="layui-input-block">
                <input type="email" name="email" class="layui-input" value="<?= $email ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">会员等级</label>
            <div class="layui-input-block">
                <select class="layui-input" name="level">
                    <?php foreach($members as $val): ?>
                        <option value="<?= $val['id'] ?>" <?= $selected_level == $val['id'] ? 'selected' : '' ?>><?= $val['name'] ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="layui-form-mid layui-word-aux"><?= $level_field_tip ?></div>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">密码(不修改请留空)</label>
            <div class="layui-input-block">
                <input type="text" name="password" class="layui-input" autocomplete="new-password">
            </div>
        </div>

        <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
        <input type="hidden" value="<?= $uid ?>" name="uid"/>
    </div>
    <div style="width: 100%; height: 50px;"></div>
    <div class="" id="form-btn">
        <div class="layui-input-block" style="margin: 0 auto;">
            <button type="submit" class="layui-btn" lay-submit lay-filter="submit">立即提交</button>
            <button type="reset" class="layui-btn layui-btn-primary">重置</button>
        </div>
    </div>
</form>



<script>
    layui.use(['table'], function(){
        var $ = layui.$;
        var form = layui.form;
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
                    parent.layer.close('edit')
                    parent.layer.msg('用户信息已保存');
                    window.parent.table.reload();
                },
                error: function (xhr) {
                    layer.msg(JSON.parse(xhr.responseText).msg);
                }
            });
            return false; // 阻止默认 form 跳转
        });



    })
    var maxHeight = $(window.parent).innerHeight() * 0.75;



    // 2. 为 #open-box 设置 max-height，同时添加溢出滚动
    $("#open-box").css({
        "max-height": maxHeight + "px", // 单位必须加 px
        "overflow-y": "auto" // 内容超过 max-height 时显示垂直滚动条
    });
</script>
