<?php defined('DC_ROOT') || exit('access denied!'); ?>
<style>
    body{
        overflow: hidden;
    }
</style>

<form class="layui-form " action="user.php?action=bind_superior" id="form">
    <div style="padding: 32px;" id="open-box">
        <div class="layui-form-item">
            <label class="layui-form-label">目标用户</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?= htmlspecialchars($target_label) ?>" readonly disabled>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">当前上级ID</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?= $current_superior_id > 0 ? $current_superior_id : '没上级' ?>" readonly disabled>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">上级ID</label>
            <div class="layui-input-block">
                <input type="number" class="layui-input" value="<?= $current_superior_value ?>" name="superior_uid" min="1" step="1" placeholder="请输入要绑定的上级用户ID">
                <div class="layui-form-mid layui-word-aux">提交时会自动检查该 ID 是否存在，且不能绑定自己、后台账户或形成循环上下级。</div>
            </div>
        </div>

        <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
        <input type="hidden" value="<?= $uid ?>" name="uid"/>
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
            var superiorUid = $.trim(field.superior_uid || '');
            if(!/^\d+$/.test(superiorUid) || parseInt(superiorUid, 10) <= 0){
                layer.msg('请输入正确的上级ID');
                return false;
            }
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
                    parent.layer.close('superior')
                    parent.layer.msg('上级已绑定');
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
