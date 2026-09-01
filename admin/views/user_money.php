<?php defined('DC_ROOT') || exit('access denied!'); ?>
<style>
    body{
        overflow: hidden;
    }
</style>


<form class="layui-form " action="user.php?action=money_ajax" id="form">
    <div style="padding: 32px;" id="open-box">
        <div class="layui-form-item">
            <label class="layui-form-label">当前余额</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?= $money ?>" readonly disabled>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">操作类型</label>
            <div class="layui-form">
                <input type="radio" name="type" value="inc" title="增加">
                <input type="radio" name="type" value="dec" title="减少">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">调整金额</label>
            <div class="layui-input-block">
                <input type="number" class="layui-input" value="" name="money">
            </div>
        </div>


        <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
        <input type="hidden" value="<?= $uid ?>" name="user_id"/>
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
                    parent.layer.close('money')
                    parent.layer.msg('用户余额已调整');
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
    $("#open-box").css({
        "max-height": maxHeight + "px", // 单位必须加 px
        "overflow-y": "auto" // 内容超过 max-height 时显示垂直滚动条
    });
</script>
