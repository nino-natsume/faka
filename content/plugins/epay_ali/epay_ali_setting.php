<?php
defined('DC_ROOT') || exit('access denied!');

function plugin_setting_view() {
    $plugin_storage = Storage::getInstance('epay_ali');

    $url = $plugin_storage->getValue('url');
    $appid = $plugin_storage->getValue('appid');
    $private_key = $plugin_storage->getValue('private_key');
    $name = $plugin_storage->getValue('name');


    $url = empty($url) ? '' : $url;
    $appid = empty($appid) ? '' : $appid;
    $private_key = empty($private_key) ? '' : $private_key;
    $name = empty($name) ? '易支付/支付宝' : $name;

    ?>


        <form class="layui-form" id="form" method="post" action="./plugin.php?plugin=epay_ali&action=setting">
            <div style="padding: 25px;" id="open-box">
                <blockquote class="layui-elem-quote">
                    <span style="">注意事项</span><hr />
                    ① 易支付需要填入接口地址、商户ID和密钥。<br />
                    ② 请确保接口地址以http://或https://开头，并以/结尾。<br />
                    ③ <span style="color:#E6A23C;">接口地址、商户ID、商户密钥未配置完整时，前台付款方式将自动隐藏。</span>
                </blockquote>

                <div class="form-section">
                    <div class="layui-form-item">
                        <label class="layui-form-label">接口地址</label>
                        <div class="layui-input-block">
                            <input type="text" class="layui-input" name="url" value="<?= $url ?>">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">商户ID</label>
                        <div class="layui-input-block">
                            <input type="text" class="layui-input" name="appid" value="<?= $appid ?>">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">商户密钥</label>
                        <div class="layui-input-block">
                            <input type="text" class="layui-input" name="private_key" value="<?= $private_key ?>">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">自定义支付方式名称</label>
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
                        if(e.code == 400){
                            return layer.msg(e.msg)
                        }
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
    $url = Input::postStrVar('url');
    $appid = Input::postStrVar('appid');
    $private_key = Input::postStrVar('private_key');
    $name = Input::postStrVar('name');

    $plugin_storage = Storage::getInstance('epay_ali');

    $plugin_storage->setValue('url', $url);
    $plugin_storage->setValue('appid', $appid);
    $plugin_storage->setValue('private_key', $private_key);
    $plugin_storage->setValue('name', $name);
    Output::ok();
}
