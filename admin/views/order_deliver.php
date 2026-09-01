<?php defined('DC_ROOT') || exit('access denied!'); ?>
<style>
    html, body { margin: 0; padding: 0; height: 100%; overflow: hidden; }
    a{
        color: #16baaa;
    }
    a:hover{
        text-decoration: underline;
    }
    #form { height: 100vh; display: flex; flex-direction: column; overflow: hidden; }
    #open-box { flex: 1 1 auto; min-height: 0; padding: 25px; overflow-y: auto; box-sizing: border-box; }
    #form-btn { flex: 0 0 auto; min-height: 72px; background: #fff; border-top: 1px solid #edf0f5; box-shadow: 0 -4px 14px rgba(15, 23, 42, .06); padding: 12px 20px 16px; display: flex; align-items: center; justify-content: center; box-sizing: border-box; z-index: 20; }
    #form-btn .layui-btn-container { margin: 0; flex-wrap: wrap; }
    #form-btn .layui-btn { min-width: 120px; height: 34px; line-height: 34px; padding: 0 18px; border-radius: 6px; font-size: 13px; font-weight: 500; border: none; transition: all .2s; margin: 0; }
    #form-btn .layui-btn i { margin-right: 4px; font-size: 14px; }
    #form-btn .layui-btn:hover { opacity: .85; }
    #form-btn .layui-bg-blue { background: #1677ff; color: #fff; }
</style>


<form class="layui-form" action="order.php?action=deliver_ajax" id="form">
    <div id="open-box">
        <p style="padding-bottom: 10px;">确认发货后，虚拟服务订单流程结束</p>
        <textarea id="remark-text" name="remark" placeholder="请填写内容..." class="layui-textarea">服务已完成</textarea>
        <div class="quick-remarks mt-3" style="margin-top: 5px;">
            <span>快速填写：</span>
            <a href="javascript:;" class="quick-remark" data-text="服务已完成">服务已完成</a> |
            <a href="javascript:;" class="quick-remark" data-text="问题已解决">问题已解决</a>
        </div>


        <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
        <input type="hidden" value="<?= $order_id ?>" name="id"/>
    </div>
    <div id="form-btn">
        <div class="layui-btn-container" style="display: inline-flex; gap: 10px; justify-content: center; width: 100%;">
            <button type="submit" class="layui-btn layui-bg-blue" lay-submit lay-filter="submit">
                <i class="layui-icon layui-icon-ok"></i> 手动确认发货
            </button>
            <button type="reset" class="layui-btn layui-btn-primary">
                <i class="layui-icon layui-icon-refresh"></i> 重置
            </button>
        </div>
    </div>
</form>



<script>

    // 快速填写点击事件
    $(document).on('click', '.quick-remark', function() {
        const text = $(this).data('text');
        $('#remark-text').val(text).focus();
    });


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
                    parent.layer.close('deliver')
                    parent.layer.msg('订单已发货');
                    window.parent.table.reload();
                },
                error: function (xhr) {
                    layer.msg(JSON.parse(xhr.responseText).msg);
                }
            });
            return false; // 阻止默认 form 跳转
        });



    })
</script>
