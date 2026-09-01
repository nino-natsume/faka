<?php defined('DC_ROOT') || exit('access denied!'); ?>
<style>
    body{
        overflow: hidden;
    }
</style>


<form class="layui-form " action="sort.php?action=save" id="form">
    <div style="padding: 25px;" id="open-box">
        <div class="layui-form-item">
            <label class="layui-form-label">分类名</label>
            <div class="layui-input-block">
                <input type="text" name="sortname" class="layui-input" value="<?= $data['sortname'] ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">别名</label>
            <div class="layui-input-block">
                <input type="text" name="alias" class="layui-input" value="<?= $data['alias'] ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">父分类</label>
            <div class="layui-input-block">
                <select class="layui-input" name="pid">
                    <option value="0">无</option>
                    <?php
                    $renderSortOptions = function ($pid = 0, $level = 0) use (&$renderSortOptions, $sorts, $data, $id) {
                        foreach ($sorts as $key => $val) {
                            if ((int)$key === (int)$id || (int)$val['pid'] !== (int)$pid) {
                                continue;
                            }
                            $selected = (int)$key === (int)$data['pid'] ? ' selected' : '';
                            echo '<option' . $selected . ' value="' . (int)$key . '">' . str_repeat('&nbsp;&nbsp;&nbsp;', $level) . ($level > 0 ? '└ ' : '') . $val['sortname'] . '</option>';
                            $renderSortOptions($key, $level + 1);
                        }
                    };
                    $renderSortOptions();
                    ?>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">分类图片</label>
            <div class="layui-input-block">
                <div class="layui-input-group" style="width: 100%; display: flex;">
                    <input type="text" value="<?= $data['sortimg'] ?>" placeholder="分类图片" class="layui-input" name="sortimg" id="sortimg">
                    <div id="ID-upload-demo-btn" class="layui-input-split layui-input-suffix layui-btn" style="display: table-cell; line-height: 192%;">
                        上传图片
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">分类图标</label>
            <div class="layui-input-block">
                <div class="layui-input-group" style="width: 100%; display: flex; align-items: center; gap: 10px;">
                    <input type="text" value="<?= $data['sorticon'] ?? '' ?>" placeholder="Remix Icon 类名，如：ri-apps-line" class="layui-input" name="sorticon" id="sorticon" style="flex: 1;">
                    <div id="icon-preview" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border: 1px solid #eee; border-radius: 4px; background: #f8f9fa;">
                        <i class="<?= $data['sorticon'] ?? 'ri-apps-line' ?>" style="font-size: 24px; color: #333;"></i>
                    </div>
                    <button type="button" class="layui-btn layui-btn-sm" id="select-icon-btn">选择图标</button>
                </div>
                <div class="layui-form-mid layui-word-aux" style="margin-top: 5px;">
                    图标库：<a href="https://remixicon.com/" target="_blank" style="color: #1E9FFF;">Remix Icon</a>
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">排序</label>
            <div class="layui-input-block">
                <input type="number" name="taxis" class="layui-input" value="<?= $data['taxis'] ?>">
            </div>
        </div>
        <?php if ($type === 'blog'): ?>
        <div class="layui-form-item">
            <label class="layui-form-label">分类模板</label>
            <div class="layui-input-block">
                <select name="template">
                    <option value="log_list">默认列表模板</option>
                    <?php if (!empty($customTemplates)): ?>
                        <?php foreach ($customTemplates as $tpl): ?>
                            <option value="<?= $tpl['filename'] ?>" <?= ($data['template'] ?? '') === $tpl['filename'] ? 'selected' : '' ?>><?= $tpl['comment'] ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <div class="layui-form-mid layui-word-aux">可选择当前博客模板中的 log_list_xxx.php。</div>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">每页文章数</label>
            <div class="layui-input-block">
                <input type="number" min="0" name="page_count" class="layui-input" value="<?= (int)($data['page_count'] ?? 0) ?>" placeholder="0 表示使用博客全局每页数量">
            </div>
        </div>
        <?php endif; ?>
        <div class="layui-form-item">
            <label class="layui-form-label">标题（用于分类页的 title）支持变量: {{site_title}}, {{site_name}}, {{sort_name}}</label>
            <div class="layui-input-block">
                <input type="text" name="title" class="layui-input" value="<?= $data['title'] ?>">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">关键词（用于分类页的 keywords，英文逗号分割）</label>
            <div class="layui-input-block">
                <textarea class="layui-textarea" name="kw"><?= $data['kw'] ?></textarea>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">描述（用于分类页的 description）</label>
            <div class="layui-input-block">
                <textarea class="layui-textarea" name="description"><?= $data['description'] ?></textarea>
            </div>
        </div>





        <input type="hidden" name="type" value="<?= $type ?>" />
        <input type="hidden" name="sid" value="<?= $id ?>" />
        <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
    </div>
    <div style="width: 100%; height: 50px;"></div>
    <div class="" id="form-btn">
        <div class="layui-input-block" style="margin: 0 auto;">
            <button type="submit" class="layui-btn" lay-submit lay-filter="submit">立即提交</button>
            <button type="reset" class="layui-btn layui-btn-primary">重置</button>
        </div>
    </div>
</form>

<div id="modal" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="img-container">
                    <div class="row">
                        <div class="col-md-11">
                            <img src="" id="sample_image"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="    align-items: center;
    display: flex
;
    flex: none;
    gap: .75rem;
    padding: 1.25rem;">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" id="crop" class="btn btn-sm btn-success">保存</button>
                <button type="button" id="use_original_image" class="btn btn-sm btn-primary">使用原图</button>
            </div>
        </div>
    </div>
</div>

<script>
    layui.use(['table'], function(){
        var $ = layui.$;
        var form = layui.form;
        var upload = layui.upload;
        var element = layui.element;
        
        // 图标预览更新
        $('#sorticon').on('input', function() {
            var iconClass = $(this).val().trim();
            if (iconClass) {
                $('#icon-preview i').attr('class', iconClass);
            } else {
                $('#icon-preview i').attr('class', 'ri-apps-line');
            }
        });
        
        // 图标选择器
        $('#select-icon-btn').on('click', function() {
            var commonIcons = [
                'ri-apps-line', 'ri-fire-line', 'ri-star-line', 'ri-heart-line', 'ri-shopping-cart-line',
                'ri-gift-line', 'ri-trophy-line', 'ri-medal-line', 'ri-vip-crown-line', 'ri-flashlight-line',
                'ri-rocket-line', 'ri-gamepad-line', 'ri-music-line', 'ri-movie-line', 'ri-camera-line',
                'ri-image-line', 'ri-palette-line', 'ri-brush-line', 'ri-pencil-line', 'ri-edit-line',
                'ri-book-line', 'ri-book-open-line', 'ri-newspaper-line', 'ri-file-text-line', 'ri-article-line',
                'ri-lightbulb-line', 'ri-magic-line', 'ri-sparkling-line', 'ri-cake-line', 'ri-cup-line',
                'ri-restaurant-line', 'ri-store-line', 'ri-shopping-bag-line', 'ri-t-shirt-line', 'ri-shirt-line',
                'ri-service-line', 'ri-customer-service-line', 'ri-headphone-line', 'ri-phone-line', 'ri-smartphone-line',
                'ri-computer-line', 'ri-macbook-line', 'ri-keyboard-line', 'ri-mouse-line', 'ri-hard-drive-line',
                'ri-database-line', 'ri-server-line', 'ri-cloud-line', 'ri-global-line', 'ri-earth-line'
            ];
            
            var html = '<link rel="stylesheet" href="../assets/css/remixicon.css">';
            html += '<div style="padding: 20px;"><div id="icon-grid" style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px; max-height: 400px; overflow-y: auto;">';
            for (var i = 0; i < commonIcons.length; i++) {
                var icon = commonIcons[i];
                var iconName = icon.replace('ri-', '').replace('-line', '');
                html += '<div class="icon-select-item" data-icon="' + icon + '" style="display: flex; flex-direction: column; align-items: center; padding: 10px; cursor: pointer; border: 1px solid #eee; border-radius: 4px; transition: all 0.3s;">';
                html += '<i class="' + icon + '" style="font-size: 24px; margin-bottom: 5px;"></i>';
                html += '<span style="font-size: 10px; text-align: center; word-break: break-all;">' + iconName + '</span>';
                html += '</div>';
            }
            html += '</div></div>';
            
            layer.open({
                type: 1,
                title: '选择图标',
                area: ['600px', '500px'],
                content: html,
                success: function(layero, index) {
                    $(layero).find('.icon-select-item').on('mouseenter', function() {
                        $(this).css({'background': '#f0f0f0', 'border-color': '#1E9FFF'});
                    }).on('mouseleave', function() {
                        $(this).css({'background': '', 'border-color': '#eee'});
                    }).on('click', function() {
                        var selectedIcon = $(this).attr('data-icon');
                        $('#sorticon').val(selectedIcon).trigger('input');
                        layer.close(index);
                    });
                }
            });
        });
        
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
                    parent.layer.msg('编辑成功');
                    window.parent.table.reload();
                },
                error: function (xhr) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        layer.msg(response.msg || '保存失败');
                    } catch(e) {
                        layer.msg('保存失败，请检查数据库是否已添加sorticon字段');
                    }
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
                    return layer.msg(res.msg || '上传失败');
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

