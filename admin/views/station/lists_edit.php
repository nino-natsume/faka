<?php defined('DC_ROOT') || exit('access denied!'); ?>
<style>
    body { overflow: hidden; }
    .edit-grid { display:grid; grid-template-columns:1fr 1fr; gap:0 24px; }
    .edit-grid .layui-form-item { margin-bottom:12px; }
    .edit-grid .is-full { grid-column: 1 / -1; }
    .section-title { font-size:14px; font-weight:700; color:#334155; padding:10px 0 6px; border-bottom:1px solid #f0f0f0; margin-bottom:14px; }
    .form-tips { font-size:12px; color:#999; margin-top:4px; display:block; }
    .domain-row { display:flex; align-items:center; gap:6px; }
    .domain-row .domain-prefix { width:160px; flex-shrink:0; }
    .domain-row .domain-sep { color:#94a3b8; font-weight:600; flex-shrink:0; user-select:none; }
    .domain-row .domain-suffix { flex:1; min-width:0; }
    .domain-row .domain-suffix .layui-form-select { width:100%; }
    .domain-preview { font-size:12px; color:#4a7cf7; margin-top:6px; padding:5px 10px; background:#f0f5ff; border-radius:4px; display:none; word-break:break-all; line-height:1.6; }
    .domain-preview.active { display:block; }
    .info-tag { display:inline-block; background:#f0f5ff; color:#4a7cf7; padding:2px 10px; border-radius:4px; font-size:13px; margin-right:6px; }
    .layui-form-label .req { color:#ff5722; margin-right:2px; font-weight:700; }
</style>

<form class="layui-form" action="?action=lists_edit_ajax" id="form">
    <div style="padding: 25px;" id="open-box">
        <div style="margin-bottom:16px;">
            <span class="info-tag">ID: <?= $info['id'] ?></span>
            <span class="info-tag">站长: <?= htmlspecialchars($info['username'] ?: $info['tel'] ?: $info['email'] ?: '-') ?> (UID:<?= $info['user_id'] ?>)</span>
        </div>

        <div class="section-title">基本信息</div>
        <div class="edit-grid">
            <div class="layui-form-item">
                <label class="layui-form-label"><span class="req">*</span>店铺名称</label>
                <div class="layui-input-block">
                    <input type="text" name="name" class="layui-input" value="<?= htmlspecialchars($info['name']) ?>">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">网站标题</label>
                <div class="layui-input-block">
                    <input type="text" name="title" class="layui-input" value="<?= htmlspecialchars($info['title']) ?>">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">网站副标题</label>
                <div class="layui-input-block">
                    <input type="text" name="site_subtitle" class="layui-input" value="<?= htmlspecialchars($info['site_subtitle'] ?? '') ?>" placeholder="可留空，前台将使用全站副标题">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label"><span class="req">*</span>分店等级</label>
                <div class="layui-input-block">
                    <select name="level_id">
                        <?php foreach ($levels as $lv): ?>
                        <option value="<?= $lv['id'] ?>" <?= $info['level_id'] == $lv['id'] ? 'selected' : '' ?>><?= htmlspecialchars($lv['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="section-title">域名配置</div>
        <div class="edit-grid">
            <div class="layui-form-item is-full">
                <label class="layui-form-label">二级域名</label>
                <div class="layui-input-block">
                    <div class="domain-row">
                        <div class="domain-prefix">
                            <input type="text" name="domain_2_prefix" id="domain2Prefix" class="layui-input" value="<?= htmlspecialchars($info['domain_2_prefix']) ?>" placeholder="前缀">
                        </div>
                        <span class="domain-sep">.</span>
                        <div class="domain-suffix">
                            <select name="domain_2_suffix" id="domain2Suffix" lay-filter="domain2Suffix">
                                <option value="">请选择主域名</option>
                                <?php foreach ($station_domain_list as $val): if (trim($val) === '') continue; ?>
                                <option value=".<?= htmlspecialchars($val) ?>" <?= $info['domain_2_suffix'] == '.' . $val ? 'selected' : '' ?>><?= htmlspecialchars($val) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="domain-preview" id="domain2Preview"></div>
                    <span class="form-tips">在「系统管理 → 分店设置」中配置可选的主域名列表。需要将二级域名泛解析到本站服务器。</span>
                </div>
            </div>
            <div class="layui-form-item is-full">
                <label class="layui-form-label">独立域名</label>
                <div class="layui-input-block">
                    <input type="text" name="domain" class="layui-input" value="<?= htmlspecialchars($info['domain']) ?>" placeholder="如 shop.example.com">
                    <span class="form-tips">需要将域名 CNAME 或 A 记录解析到本站服务器 IP。</span>
                </div>
            </div>
            <?php if ($station_slug_mode === '1'): ?>
            <div class="layui-form-item is-full">
                <label class="layui-form-label">店铺标识</label>
                <div class="layui-input-block">
                    <input type="text" name="slug" class="layui-input" value="<?= htmlspecialchars($info['slug'] ?? '') ?>" placeholder="如 shop1" maxlength="50" style="max-width:300px;">
                    <span class="form-tips">访问路径：/s/后缀 &nbsp;（字母、数字、下划线、连字符，2-50 位）</span>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="section-title">公告内容</div>
        <div class="edit-grid">
            <div class="layui-form-item is-full">
                <label class="layui-form-label">滚动公告</label>
                <div class="layui-input-block">
                    <textarea name="roll_notice" class="layui-textarea" style="min-height:60px;"><?= htmlspecialchars($info['roll_notice']) ?></textarea>
                </div>
            </div>
            <div class="layui-form-item is-full">
                <label class="layui-form-label">内容公告</label>
                <div class="layui-input-block">
                    <textarea name="home_notice" class="layui-textarea" style="min-height:80px;"><?= htmlspecialchars($info['home_notice']) ?></textarea>
                </div>
            </div>
        </div>

        <input name="id" value="<?= $info['id'] ?>" type="hidden"/>
        <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
    </div>
    <div style="width: 100%; height: 50px;"></div>
    <div id="form-btn">
        <div class="layui-input-block" style="margin: 0 auto;">
            <button type="submit" class="layui-btn" lay-submit lay-filter="submit">保存</button>
            <button type="reset" class="layui-btn layui-btn-primary">重置</button>
        </div>
    </div>
</form>

<script>
    layui.use(['form'], function(){
        var $ = layui.$;
        var form = layui.form;
        form.render();

        // --- 二级域名实时预览 ---
        function updateDomain2Preview() {
            var prefix = $('#domain2Prefix').val().trim();
            var suffix = $('select[name="domain_2_suffix"]').val() || '';
            var $preview = $('#domain2Preview');
            if (prefix && suffix) {
                $preview.html('<b>预览：</b>http://' + prefix + suffix).addClass('active');
            } else {
                $preview.removeClass('active');
            }
        }
        $('#domain2Prefix').on('input', updateDomain2Preview);
        form.on('select(domain2Suffix)', function(){ updateDomain2Preview(); });
        updateDomain2Preview();

        form.on('submit(submit)', function(data){
            var field = data.field;
            var url = $('#form').attr('action');
            $.ajax({
                type: "POST",
                url: url,
                data: field,
                dataType: "json",
                success: function (e) {
                    if(e.code == 400) return layer.msg(e.msg);
                    parent.layer.closeAll();
                    parent.layer.msg('分站信息已保存');
                    window.parent.table.reload();
                },
                error: function (xhr) {
                    var msg = '操作失败';
                    try { msg = JSON.parse(xhr.responseText).msg; } catch(e){
                        msg = '操作失败 [HTTP ' + xhr.status + '] ' + (xhr.responseText || '').substring(0, 200);
                    }
                    console.error('Station save error:', xhr.status, xhr.responseText);
                    layer.msg(msg);
                }
            });
            return false;
        });
    });

    var maxHeight = $(window.parent).innerHeight() * 0.75;
    $("#open-box").css({
        "max-height": maxHeight + "px",
        "overflow-y": "auto"
    });
</script>
