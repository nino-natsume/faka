<?php defined('DC_ROOT') || exit('access denied!'); ?>
<style>
    body{
        overflow: hidden;
    }
</style>

<form class="layui-form " action="user.php?action=credits_ajax" id="form">
    <div style="padding: 32px;" id="open-box">
        <div class="layui-form-item">
            <label class="layui-form-label">当前积分</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?= $credits ?>" readonly disabled>
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
            <label class="layui-form-label">调整积分</label>
            <div class="layui-input-block">
                <input type="number" class="layui-input" value="" name="credits">
            </div>
        </div>

        <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
        <input type="hidden" value="<?= $uid ?>" name="user_id"/>
    </div>
    <div style="width: 100%; height: 50px;"></div>
    <div class="" id="form-btn">
        <div class="layui-input-block" style="margin: 0 auto;">
            <button type="submit" class="layui-btn" lay-submit lay-filter="submit">立即提交</button>
            <button type="button" class="layui-btn layui-btn-primary" id="cancel-btn">取消</button>
        </div>
    </div>
</form>

<script>
    layui.use(['form', 'layer'], function(){
        var $ = layui.$;
        var form = layui.form;
        var layer = layui.layer;
        form.on('submit(submit)', function(data){
            var field = data.field;
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
                    parent.layer.close('credits')
                    parent.layer.msg('用户积分已调整');
                    window.parent.table.reload();
                },
                error: function (xhr) {
                    layer.msg(JSON.parse(xhr.responseText).msg);
                }
            });
            return false;
        });
        $('#cancel-btn').on('click', function(){
            var frameIndex = parent.layer.getFrameIndex(window.name);
            parent.layer.close(frameIndex);
        });
    })

    var maxHeight = $(window.parent).innerHeight() * 0.75;
    $("#open-box").css({
        "max-height": maxHeight + "px",
        "overflow-y": "auto"
    });
</script>
