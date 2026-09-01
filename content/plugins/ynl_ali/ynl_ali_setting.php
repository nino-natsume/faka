<?php
defined('DC_ROOT') || exit('access denied!');


function plugin_setting_view() {
    $plugin_storage = Storage::getInstance('ynl_ali');

    $pid = $plugin_storage->getValue('pid');
    $key = $plugin_storage->getValue('key');
    $name = $plugin_storage->getValue('name');

    $pid = empty($pid) ? '' : $pid;
    $key = empty($key) ? '' : $key;
    $name = empty($name) ? '码支付/支付宝' : $name;





    ?>

    <form class="layui-form" id="form" method="post" action="./plugin.php?plugin=ynl_ali&action=setting">
        <div style="padding: 25px;" id="open-box">

            <blockquote class="layui-elem-quote">
                <span style="">注意事项</span><hr />
                注册地址：<a href="https://pay.1kexiu.xyz/" target="_blank">https://pay.1kexiu.xyz/</a><br />
                <span style="color:#E6A23C;">PID 和 KEY 未配置完整时，前台付款方式将自动隐藏。</span>
            </blockquote>

            <!-- 基本信息设置 -->
            <div class="form-section">



                <div class="layui-form-item">
                    <label class="layui-form-label">接口地址</label>
                    <div class="layui-input-block">
                        <input type="text" class="layui-input" readonly disabled name="url" value="https://pay.1kexiu.xyz/">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">PID</label>
                    <div class="layui-input-block">
                        <input type="text" class="layui-input" name="pid" value="<?= $pid ?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">KEY</label>
                    <div class="layui-input-block">
                        <input type="text" class="layui-input" name="key" value="<?= $key ?>">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">自定义支付名称</label>
                    <div class="layui-input-block">
                        <input type="text" class="layui-input" name="name" value="<?= $name ?>">
                    </div>
                </div>




            </div>

        </div>

        <div style="width: 100%; height: 50px;"></div>
        <div class="" id="form-btn">
            <div class="layui-input-block" style="margin: 0 auto;">
                <button type="submit" class="layui-btn" lay-submit lay-filter="submit">保存配置</button>
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
                        parent.layer.close('edit')
                        parent.layer.msg('已保存配置');
                        // window.parent.table.reload();
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





<?php }

function plugin_setting() {
    $pid = Input::postStrVar('pid');
    $key = Input::postStrVar('key');
    $name = Input::postStrVar('name');

    $plugin_storage = Storage::getInstance('ynl_ali');

    $plugin_storage->setValue('pid', $pid);
    $plugin_storage->setValue('key', $key);
    $plugin_storage->setValue('name', $name);
    Output::ok();
}
