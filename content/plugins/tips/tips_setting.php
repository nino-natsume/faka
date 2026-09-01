<?php
defined('DC_ROOT') || exit('access denied!');

function plugin_setting_view() {
    $plugin_storage = Storage::getInstance('tips');
    $custom_tips = $plugin_storage->getValue('custom_tips') ?: '';
    $show_icon = $plugin_storage->getValue('show_icon');
    $show_icon = ($show_icon === null || $show_icon === '') ? '1' : $show_icon;
    $tip_position = $plugin_storage->getValue('tip_position') ?: 'top';
    ?>
    <form class="layui-form" id="form" method="post" action="./plugin.php?plugin=tips&action=setting">
        <div style="padding: 25px;" id="open-box">
            <blockquote class="layui-elem-quote">
                <i class="ri-lightbulb-line"></i> 小贴士配置说明
                <br>• 自定义小贴士：每行一条，留空则使用默认小贴士
                <br>• 支持 emoji 表情和 HTML 标签
                <br>• 系统会随机显示其中一条小贴士
            </blockquote>

            <div class="layui-form-item">
                <label class="layui-form-label">显示位置</label>
                <div class="layui-input-block">
                    <input type="radio" name="tip_position" value="top" title="顶部显示" <?= $tip_position == 'top' ? 'checked' : '' ?>>
                    <input type="radio" name="tip_position" value="bottom" title="底部显示" <?= $tip_position == 'bottom' ? 'checked' : '' ?>>
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">显示图标</label>
                <div class="layui-input-block">
                    <input type="hidden" name="show_icon" value="0">
                    <input type="checkbox" name="show_icon" value="1" lay-skin="switch" lay-text="开启|关闭" <?= $show_icon == '1' ? 'checked' : '' ?>>
                </div>
            </div>

            <div class="layui-form-item layui-form-text">
                <label class="layui-form-label">自定义小贴士</label>
                <div class="layui-input-block">
                    <textarea name="custom_tips" class="layui-textarea" rows="10" placeholder="每行一条小贴士，留空使用默认内容&#10;例如：&#10;💾 记得定期备份数据库哦&#10;🎉 欢迎使用 DCSHOP 商城系统&#10;🔧 有问题可以查看帮助文档"><?= htmlspecialchars($custom_tips) ?></textarea>
                </div>
            </div>

            <div style="height: 80px;"></div>
        </div>

        <div style="width: 100%; height: 50px;"></div>
        <div id="form-btn">
            <div class="layui-input-block" style="margin: 0 auto;">
                <button type="submit" class="layui-btn" lay-submit lay-filter="submit"><i class="ri-check-line"></i> 保存配置</button>
                <button type="reset" class="layui-btn layui-btn-primary"><i class="ri-refresh-line"></i> 重置</button>
            </div>
        </div>
    </form>

    <style>
        /* 隐藏弹窗层滚动条，只保留内容区滚动条 */
        html, body { overflow: hidden !important; }
    </style>

    <script>
        layui.use(['form'], function(){
            var $ = layui.$;
            var form = layui.form;
            
            form.on('submit(submit)', function(data){
                var field = data.field;
                var url = $('#form').attr('action');
                
                $.ajax({
                    type: "POST",
                    url: url,
                    data: field,
                    dataType: "json",
                    success: function(e) {
                        if(e.code == 400) return layer.msg(e.msg);
                        layer.msg('已保存配置');
                        setTimeout(function() {
                            parent.layer.close('edit');
                        }, 500);
                    },
                    error: function(xhr) {
                        layer.msg(JSON.parse(xhr.responseText).msg);
                    }
                });
                return false;
            });
            
            form.render();
        });

        // 设置内容区最大高度和滚动
        var maxHeight = $(window.parent).innerHeight() * 0.75;
        $("#open-box").css({ "max-height": maxHeight + "px", "overflow-y": "auto" });
    </script>
<?php }

function plugin_setting() {
    $custom_tips = Input::postStrVar('custom_tips');
    $show_icon = Input::postStrVar('show_icon');
    $tip_position = Input::postStrVar('tip_position') ?: 'top';
    
    // checkbox 开关：选中时值为 '1'，未选中时值为 '0'（来自隐藏字段）
    $show_icon = $show_icon === '1' ? '1' : '0';
    
    $plugin_storage = Storage::getInstance('tips');
    $plugin_storage->setValue('custom_tips', trim($custom_tips));
    $plugin_storage->setValue('show_icon', $show_icon);
    $plugin_storage->setValue('tip_position', $tip_position);
    
    Output::ok();
}