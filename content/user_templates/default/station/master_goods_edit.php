<?php defined('DC_ROOT') || exit('access denied!'); ?>
<?php
$_customName = empty($station_goods) ? '' : ($station_goods['custom_name'] ?? '');
$_premiumRaw = empty($station_goods) ? 0.10 : (float)($station_goods['premium'] ?? 0.10);
$_premium = $_premiumRaw * 100;
?>
<style>
    body { overflow: hidden; margin: 0; background: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
    .mge-wrap { padding: 0; }
    .mge-body { padding: 24px 28px; display: flex; flex-direction: column; gap: 20px; }
    .mge-item {}
    .mge-label { display: flex; align-items: center; gap: 7px; margin-bottom: 9px; color: #1e293b; font-size: 14px; font-weight: 600; }
    .mge-label i { font-size: 14px; }
    .mge-input {
        width: 100%; border: 1.5px solid #e2e8f0; background: #fff; color: #1e293b;
        border-radius: 10px; padding: 12px 16px; font-size: 14px; box-sizing: border-box;
        transition: border-color .18s ease, box-shadow .18s ease;
    }
    .mge-input:focus { outline: none; border-color: rgba(47,99,214,.5); box-shadow: 0 0 0 4px rgba(47,99,214,.08); }
    .mge-input[readonly] { background: #f1f5f9; color: #64748b; cursor: default; }
    .mge-tips { display: block; margin-top: 8px; color: #64748b; font-size: 12px; line-height: 1.7; }
    .mge-tips b { color: #1e293b; }
    .mge-footer {
        display: flex; align-items: center; justify-content: flex-end; gap: 10px;
        padding: 14px 28px; border-top: 1px solid #e2e8f0; background: #f1f5f9;
    }
    .mge-btn-save {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        min-width: 120px; height: 42px; padding: 0 20px; border: 0; border-radius: 10px;
        font-size: 14px; font-weight: 700; cursor: pointer; transition: .18s;
        background: linear-gradient(135deg, #2f63d6, #4a7ef5); color: #fff;
        box-shadow: 0 6px 16px rgba(47,99,214,.18);
    }
    .mge-btn-save:hover { transform: translateY(-1px); box-shadow: 0 10px 24px rgba(47,99,214,.24); }
    .mge-btn-reset {
        display: inline-flex; align-items: center; justify-content: center;
        height: 42px; padding: 0 18px; border: 1.5px solid #e2e8f0; border-radius: 10px;
        font-size: 14px; font-weight: 600; cursor: pointer; transition: .18s;
        background: #fff; color: #64748b;
    }
    .mge-btn-reset:hover { border-color: #cbd5e1; color: #334155; }
    .mge-notice {
        margin-top: 4px; padding: 14px 16px; border-radius: 8px;
        background: rgba(47,99,214,.05); border: 1px solid rgba(47,99,214,.15);
    }
    .mge-notice-title { font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 6px; }
    .mge-notice-title i { margin-right: 4px; }
    .mge-notice-text { font-size: 12px; line-height: 1.7; color: #475569; }
    .mge-notice-text b { color: #1e293b; }
    @media (max-width: 768px) {
        .mge-body { padding: 18px 16px; gap: 16px; }
        .mge-footer { padding: 12px 16px; }
    }
</style>

<form class="layui-form" action="" id="form">
    <div class="mge-wrap" id="open-box">
        <div class="mge-body">
            <div class="mge-item">
                <label class="mge-label"><i class="fa fa-cube" style="color:#6366f1;"></i> 主站商品名</label>
                <input type="text" readonly class="mge-input" value="<?= htmlspecialchars($goods['title']) ?>">
                <span class="mge-tips">这是主站设置的原始商品名称，仅供参考，无法修改。</span>
            </div>
            <div class="mge-item">
                <label class="mge-label"><i class="fa fa-pencil" style="color:#8b5cf6;"></i> 自定义名称</label>
                <input type="text" name="custom_name" class="mge-input" value="<?= htmlspecialchars($_customName) ?>" placeholder="留空则使用主站商品名">
                <span class="mge-tips">给这个商品起一个你自己的名字，顾客在你的店铺看到的将是这个名称。<b>留空不填</b>则自动沿用主站的商品名。</span>
            </div>
            <div class="mge-item">
                <label class="mge-label"><i class="fa fa-percent" style="color:#f59e0b;"></i> 加价比例</label>
                <input type="number" name="premium" class="mge-input" value="<?= $_premium ?>" placeholder="例如: 25" min="0" step="0.01">
                <span class="mge-tips">直接填百分比数字。填 <b>25</b> 表示加价 <b>25%</b>，填 <b>0</b> 表示不加价（和主站同价）。例如主站卖 100 元，填 25 后你的售价就是 125 元。</span>
            </div>
            <div class="mge-notice">
                <div class="mge-notice-title"><i class="fa fa-info-circle"></i> 为什么我自己看到的价格和设置的不一样？</div>
                <div class="mge-notice-text">加价对所有访客都生效，但不同会员等级的“底价”不同。<b>您自己登录后看到的价格 = 您的等级价 + 加价</b>，会比普通顾客的售价偏低，这是正常的。商品管理页显示的“前台售价”是普通顾客（未登录）看到的价格。</div>
            </div>
        </div>
        <input type="hidden" name="goods_id" value="<?= $goods_id ?>" />
        <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
    </div>
    <div class="mge-footer">
        <button type="reset" class="mge-btn-reset">重置</button>
        <button type="submit" class="mge-btn-save" lay-submit lay-filter="submit"><i class="fa fa-check"></i> 保存</button>
    </div>
</form>



<script>
    layui.use(['table'], function(){
        var $ = layui.$;
        var form = layui.form;
        var upload = layui.upload;
        var element = layui.element;
        form.on('submit(submit)', function(data){
            var field = data.field; // 获取表单全部字段值
            $.ajax({
                type: "POST",
                url: "?action=master_goods_edit_ajax",
                data: field,
                dataType: "json",
                success: function (e) {
                    if(e.code == 400){
                        return layer.msg(e.msg)
                    }
                    var idx = parent.layer.getFrameIndex(window.name);
                    parent.layer.close(idx);
                    parent.layer.msg('商品设置已保存');
                    window.parent.table.reload();
                },
                error: function (xhr) {
                    layer.msg(JSON.parse(xhr.responseText).msg);
                }
            });
            return false; // 阻止默认 form 跳转
        });


        var uploadInst = upload.render({
            elem: '#ID-upload-demo-btn',
            field: 'image',
            url: './article.php?action=upload_cover', // 实际使用时改成您自己的上传接口即可。
            before: function(obj){
                // 预读本地文件示例，不支持ie8
                obj.preview(function(index, file, result){
                    $('#ID-upload-demo-img').attr('src', result); // 图片链接（base64）
                });

                element.progress('filter-demo', '0%'); // 进度条复位
                loadIndex = layer.load(2);
            },
            done: function(res){
                // 若上传失败
                if(res.code > 0){
                    return layer.msg('上传失败');
                }
                // 上传成功的一些操作
                if(res.code == 0){
                    $('#sortimg').val(res.data)
                }
                $('#ID-upload-demo-text').html(''); // 置空上传失败的状态
            },
            error: function(){
                // 演示失败状态，并实现重传
                var demoText = $('#ID-upload-demo-text');
                demoText.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-xs demo-reload">重试</a>');
                demoText.find('.demo-reload').on('click', function(){
                    uploadInst.upload();
                });
            },
            // 进度条
            progress: function(n, elem, e){
                element.progress('filter-demo', n + '%'); // 可配合 layui 进度条元素使用
                if(n == 100){
                    layer.close(loadIndex)
                }
            }
        });



    })






    var maxHeight = $(window.parent).innerHeight() * 0.75;
    // 2. 为 #open-box 设置 max-height，同时添加溢出滚动
    $("#open-box").css({
        "max-height": maxHeight + "px", // 单位必须加 px
        "overflow-y": "auto" // 内容超过 max-height 时显示垂直滚动条
    });
</script>
