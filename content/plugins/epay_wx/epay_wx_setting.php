<?php
defined('DC_ROOT') || exit('access denied!');

function plugin_setting_view() {
    $plugin_storage = Storage::getInstance('epay_wx');

    $url = $plugin_storage->getValue('url');
    $appid = $plugin_storage->getValue('appid');
    $private_key = $plugin_storage->getValue('private_key');
    $name = $plugin_storage->getValue('name');


    $url = empty($url) ? '' : $url;
    $appid = empty($appid) ? '' : $appid;
    $private_key = empty($private_key) ? '' : $private_key;
    $name = empty($name) ? '易支付/微信' : $name;

    ?>
    <style>
        html, body {
            height: 100%;
            overflow: hidden;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        #form {
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        #open-box {
            height: calc(100% - 60px);
            overflow-y: auto;
            padding: 25px;
            box-sizing: border-box;
        }
        #form-btn {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            background-color: #fff;
            border-top: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 999;
            box-shadow: 0 -1px 5px rgba(0,0,0,0.02);
        }
        /* 优化滚动条样式 */
        #open-box::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        #open-box::-webkit-scrollbar-thumb {
            border-radius: 3px;
            background: #d7d7d7;
        }
        #open-box::-webkit-scrollbar-track {
            background: #fff;
        }
    </style>

    <form class="layui-form" id="form" method="post" action="./plugin.php?plugin=epay_wx&action=setting">
        <div id="open-box">

            <blockquote class="layui-elem-quote">
                <span style="">注意事项</span><hr />
                ① 易支付通常需要填入接口地址、商户ID和密钥。<br />
                ② 请确保接口地址以http://或https://开头，并以/结尾。<br />
                ③ 自定义名称将在前台支付页面显示。<br />
                ④ <span style="color:#E6A23C;">接口地址、商户ID、商户密钥未配置完整时，前台付款方式将自动隐藏。</span>
            </blockquote>

            <div class="form-section">
                <div class="layui-form-item">
                    <label class="layui-form-label">接口地址</label>
                    <div class="layui-input-block">
                        <input type="text" class="layui-input" name="url" value="<?= $url ?>" placeholder="请输入接口地址，例如：https://pay.example.com/">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">商户ID</label>
                    <div class="layui-input-block">
                        <input type="text" class="layui-input" name="appid" value="<?= $appid ?>" placeholder="请输入商户ID">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">商户密钥</label>
                    <div class="layui-input-block">
                        <input type="text" class="layui-input" name="private_key" value="<?= $private_key ?>" placeholder="请输入商户密钥">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">显示名称</label>
                    <div class="layui-input-block">
                        <input type="text" class="layui-input" name="name" value="<?= $name ?>" placeholder="前台显示的支付方式名称">
                    </div>
                </div>
            </div>

        </div>

        <div id="form-btn">
            <div class="layui-input-block" style="margin: 0;">
                <button type="submit" class="layui-btn" lay-submit lay-filter="submit">保存配置</button>
            </div>
        </div>
    </form>

    <script>
        layui.use(['form'], function(){
            var $ = layui.$;
            var form = layui.form;
            var layer = layui.layer;
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
                    },
                    error: function (xhr) {
                        layer.msg(JSON.parse(xhr.responseText).msg);
                    }
                });
                return false; // 阻止默认 form 跳转
            });
        })
    </script>
<?php }

function plugin_setting() {
    $url = Input::postStrVar('url');
    $appid = Input::postStrVar('appid');
    $private_key = Input::postStrVar('private_key');
    $name = Input::postStrVar('name');

    $plugin_storage = Storage::getInstance('epay_wx');

    $plugin_storage->setValue('url', $url);
    $plugin_storage->setValue('appid', $appid);
    $plugin_storage->setValue('private_key', $private_key);
    $plugin_storage->setValue('name', $name);
    Output::ok();
}