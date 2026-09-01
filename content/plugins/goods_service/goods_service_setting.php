<?php
defined('DC_ROOT') || exit('access denied!');

function plugin_setting_view() {
    $plugin_storage = Storage::getInstance('goods_service');
    $quick_phrases = $plugin_storage->getValue('quick_phrases');
    if (empty($quick_phrases)) {
        $quick_phrases = "服务已完成\n问题已解决\n已处理完毕\n充值成功\n代购完成";
    }
    ?>
    <form class="layui-form" id="form" method="post" action="./plugin.php?plugin=goods_service&action=setting">
        <div style="padding: 25px;" id="open-box">
            <blockquote class="layui-elem-quote">
                <i class="ri-information-line"></i> 虚拟服务商品设置<hr />
                虚拟服务商品适用于需要人工处理的订单，如代充值、代下单、定制服务等。<br />
                买家下单付款后，商家在后台手动发货，填写处理结果。
            </blockquote>

            <!-- 快捷短语设置 -->
            <div class="layui-form-item layui-form-text">
                <label class="layui-form-label">快捷短语</label>
                <div class="layui-input-block">
                    <textarea class="layui-textarea" name="quick_phrases" placeholder="每行一个短语" style="min-height: 150px;"><?= htmlspecialchars($quick_phrases) ?></textarea>
                    <div class="layui-form-mid layui-word-aux">发货时可快速填入的短语，每行一个。如：服务已完成、充值成功、已处理等</div>
                </div>
            </div>

            <!-- 预览 -->
            <div class="layui-form-item">
                <label class="layui-form-label">效果预览</label>
                <div class="layui-input-block">
                    <div id="preview-phrases" style="display: flex; flex-wrap: wrap; gap: 8px; padding: 10px 0;">
                        <!-- 动态生成 -->
                    </div>
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
        html, body { overflow: hidden !important; }
        
        .preview-tag {
            padding: 6px 12px;
            background: #f0f2f5;
            border: 1px solid #e0e0e0;
            border-radius: 15px;
            font-size: 12px;
            color: #666;
        }
    </style>

    <script>
        layui.use(['form'], function(){
            var $ = layui.$;
            var form = layui.form;
            
            // 更新预览
            function updatePreview() {
                var text = $('textarea[name="quick_phrases"]').val();
                var phrases = text.split('\n').filter(function(p) { return p.trim(); });
                var html = '';
                phrases.forEach(function(p) {
                    html += '<span class="preview-tag">' + $('<div>').text(p.trim()).html() + '</span>';
                });
                $('#preview-phrases').html(html || '<span style="color:#999;">暂无快捷短语</span>');
            }
            
            // 初始化预览
            updatePreview();
            
            // 监听输入
            $('textarea[name="quick_phrases"]').on('input', updatePreview);
            
            // 表单提交
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

        var maxHeight = $(window.parent).innerHeight() * 0.75;
        $("#open-box").css({ "max-height": maxHeight + "px", "overflow-y": "auto" });
    </script>
<?php }

function plugin_setting() {
    $quick_phrases = Input::postStrVar('quick_phrases');
    
    $plugin_storage = Storage::getInstance('goods_service');
    $plugin_storage->setValue('quick_phrases', trim($quick_phrases));
    
    Output::ok();
}
