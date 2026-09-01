<?php defined('DC_ROOT') || exit('access denied!'); ?>

<style>
    .seocfg-section { background: #ffffff85; border: 1px solid #eef1f4; border-radius: 8px; padding: 18px 20px; margin-bottom: 14px; }
    .seocfg-title { font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; }
    .seocfg-title i { color: #2563eb; }
    .seocfg-row { display: grid; grid-template-columns: 160px 1fr; gap: 10px; align-items: start; padding: 8px 0; }
    .seocfg-row > label { color: #374151; font-weight: 500; padding-top: 10px; }
    .seocfg-row .layui-input-block { margin-left: 0; }
    .seocfg-row .layui-input,
    .seocfg-row .layui-textarea,
    .seocfg-row .layui-form-select,
    .seocfg-row .layui-select-title,
    .seocfg-row select { max-width: 860px; }
    .seocfg-tip { color: #6b7280; font-size: 12px; line-height: 1.7; margin-top: 8px; }
    .seocfg-radio-group { display: flex; flex-direction: column; gap: 10px; }
    .seocfg-actions { text-align: center; margin-top: 10px; }

    @media (max-width: 768px) {
        .layui-card-body { padding: 12px !important; }
        .seocfg-row { grid-template-columns: 1fr; gap: 4px; }
        .seocfg-row > label { padding-top: 0; font-size: 13px; }
        .seocfg-section { padding: 14px 12px; }
        .seocfg-row .layui-input,
        .seocfg-row .layui-textarea,
        .seocfg-row .layui-form-select,
        .seocfg-row .layui-select-title,
        .seocfg-row select { max-width: 100%; }
        .rewrite-config-box { font-size: 12px; }
        .rewrite-config-box pre { font-size: 11px; overflow-x: auto; }
    }
</style>

<div class="layui-tabs order-tabs-wrapper" style="display:flex;align-items:center;justify-content:space-between;" lay-options="{trigger: false}">
    <ul class="layui-tabs-header">
        <li><a href="./setting.php">系统配置</a></li>
        <li><a href="./setting.php?action=blog">博客配置</a></li>
        <li><a href="./setting.php?action=agreement">协议管理</a></li>
        <li class="layui-this"><a href="./setting.php?action=seo">SEO设置</a></li>
        <li><a href="./setting.php?action=mail">邮箱配置</a></li>
    </ul>
</div>
<div class="layui-table-view" style="border-radius:8px;overflow:hidden;">
    <div class="layui-card-header" style="display:flex;align-items:center;justify-content:center;position:relative;height:auto;padding:12px 15px;">
        <span style="position:absolute;left:15px;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">SEO设置</span>
    </div>
    <div class="layui-card-body" style="padding:20px;">
        <form action="setting.php?action=seo_save" method="post" name="setting_form" id="setting_form" class="layui-form">
            <div class="seocfg-section">
                <div class="seocfg-title"><i class="ri-links-line"></i>链接格式</div>
                <div class="seocfg-row">
                    <label>链接格式</label>
                    <div>
                        <div class="seocfg-radio-group">
                            <div class="layui-input-block">
                                <input type="radio" name="permalink" value="0" title="0 默认格式&nbsp;&nbsp;<?= DC_URL ?>?post=1" <?= $ex0 ?>>
                            </div>
                            <div class="layui-input-block">
                                <input type="radio" name="permalink" value="1" title="1 文件格式&nbsp;&nbsp;<?= DC_URL ?>post-1.html" <?= $ex1 ?>>
                            </div>
                            <div class="layui-input-block">
                                <input type="radio" name="permalink" value="2" title="2 目录格式&nbsp;&nbsp;<?= DC_URL ?>post/1" <?= $ex2 ?>>
                            </div>
                            <!--<div class="layui-input-block">
                                <input type="radio" name="permalink" value="3" title="3 分类格式&nbsp;&nbsp;<?php /*= DC_URL */?>category/1.html" <?php /*= $ex3 */?>>
                            </div>-->
                        </div>
                        <div class="seocfg-tip">默认格式兼容性最高，文件格式和目录格式需配合伪静态规则使用。</div>
                    </div>
                </div>

                <!-- 伪静态配置说明 -->
                <div class="seocfg-row" id="rewrite-tips" style="display: none;">
                    <label>伪静态说明</label>
                    <div>
                        <div class="rewrite-config-box">
                            <div class="rewrite-header">
                                <i class="ri-error-warning-line"></i>
                                <span>使用伪静态链接格式需要配置服务器规则</span>
                            </div>
                            <div class="rewrite-tabs">
                                <span class="rewrite-tab active" data-type="nginx">Nginx</span>
                                <span class="rewrite-tab" data-type="apache">Apache</span>
                                <span class="rewrite-tab" data-type="baota">宝塔面板</span>
                            </div>
                            <div class="rewrite-content">
                                <div class="rewrite-panel active" data-type="nginx">
                                    <p>将以下规则添加到 Nginx 站点配置的 server 块中：</p>
                                    <pre><code>location / {
    index index.php index.html;
    if (!-e $request_filename){
        rewrite ^/(.*)$ /index.php last;
    }
}</code></pre>
                                    <button type="button" class="copy-btn" onclick="copyRewriteRule('nginx')"><i class="ri-file-copy-line"></i> 复制</button>
                                </div>
                                <div class="rewrite-panel" data-type="apache">
                                    <p>Apache 已自带 .htaccess 文件，确保：</p>
                                    <ol>
                                        <li>已开启 <code>mod_rewrite</code> 模块</li>
                                        <li>站点配置中 <code>AllowOverride All</code></li>
                                    </ol>
                                    <p>如果仍不生效，检查 .htaccess 文件内容：</p>
                                    <pre><code>&lt;IfModule mod_rewrite.c&gt;
    RewriteEngine on
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteBase /
    RewriteRule . /index.php [L]
&lt;/IfModule&gt;</code></pre>
                                    <button type="button" class="copy-btn" onclick="copyRewriteRule('apache')"><i class="ri-file-copy-line"></i> 复制</button>
                                </div>
                                <div class="rewrite-panel" data-type="baota">
                                    <p>宝塔面板配置步骤：</p>
                                    <ol>
                                        <li>进入 <strong>网站</strong> → 点击站点 <strong>设置</strong></li>
                                        <li>选择 <strong>伪静态</strong> 选项卡</li>
                                        <li>在下拉框选择 <code>thinkphp</code> 或粘贴以下规则：</li>
                                    </ol>
                                    <pre><code>location / {
    if (!-e $request_filename){
        rewrite ^/(.*)$ /index.php last;
    }
}</code></pre>
                                    <button type="button" class="copy-btn" onclick="copyRewriteRule('baota')"><i class="ri-file-copy-line"></i> 复制</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="seocfg-section">
                <div class="seocfg-title"><i class="ri-global-line"></i>SEO 信息</div>
                <div class="seocfg-row">
                    <label>站点浏览器标题</label>
                    <div>
                        <input class="layui-input" value="<?= $site_title ?>" name="site_title">
                        <div class="seocfg-tip">显示在浏览器标签页和搜索引擎标题中。</div>
                    </div>
                </div>
                <div class="seocfg-row">
                    <label>站点关键字</label>
                    <div>
                        <input class="layui-input" value="<?= $site_key ?>" name="site_key">
                        <div class="seocfg-tip">多个关键字请使用英文逗号分隔。</div>
                    </div>
                </div>
                <div class="seocfg-row">
                    <label>站点描述</label>
                    <div>
                        <textarea name="site_description" class="layui-textarea"><?= $site_description ?></textarea>
                        <div class="seocfg-tip">建议填写简洁、可读的业务描述，便于搜索引擎抓取。</div>
                    </div>
                </div>
            </div>

            <div class="seocfg-section">
                <div class="seocfg-title"><i class="ri-file-search-line"></i>详情页标题方案</div>
                <div class="seocfg-row">
                    <label>浏览器标题方案</label>
                    <div>
                        <select name="log_title_style">
                            <option value="0" <?= $opt0 ?>>商品名称</option>
                            <option value="1" <?= $opt1 ?>>商品名称 - 站点标题</option>
                            <option value="2" <?= $opt2 ?>>商品名称 - 站点浏览器标题</option>
                        </select>
                        <div class="seocfg-tip">统一控制详情页 `&lt;title&gt;` 的展示组合方式。</div>
                    </div>
                </div>
            </div>

            <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
            <div class="seocfg-actions">
                <button type="submit" class="layui-btn" lay-submit lay-filter="demo1">保存设置</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </form>
    </div>
</div>

<div style="height: 96px;"></div>


<script>
    $(function () {
        // 提交表单
        $("#setting_form").submit(function (event) {
            event.preventDefault();
            submitForm("#setting_form");
        });

        // 监听链接格式变化，显示/隐藏伪静态配置说明
        function checkPermalink() {
            var val = $('input[name="permalink"]:checked').val();
            if (val === '0') {
                $('#rewrite-tips').slideUp(200);
            } else {
                $('#rewrite-tips').slideDown(200);
            }
        }
        
        $('input[name="permalink"]').change(checkPermalink);
        checkPermalink(); // 初始检查

        // 伪静态配置Tab切换
        $('.rewrite-tab').click(function() {
            var type = $(this).data('type');
            $('.rewrite-tab').removeClass('active');
            $(this).addClass('active');
            $('.rewrite-panel').removeClass('active');
            $('.rewrite-panel[data-type="' + type + '"]').addClass('active');
        });
    });

    // 复制伪静态规则
    function copyRewriteRule(type) {
        var text = '';
        if (type === 'nginx' || type === 'baota') {
            text = 'location / {\n    if (!-e $request_filename){\n        rewrite ^/(.*)$ /index.php last;\n    }\n}';
        } else if (type === 'apache') {
            text = '<IfModule mod_rewrite.c>\n    RewriteEngine on\n    RewriteCond %{REQUEST_FILENAME} !-f\n    RewriteCond %{REQUEST_FILENAME} !-d\n    RewriteBase /\n    RewriteRule . /index.php [L]\n</IfModule>';
        }
        
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                layer.msg('已复制到剪贴板');
            });
        } else {
            var textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            layer.msg('已复制到剪贴板');
        }
    }
</script>

<style>
.rewrite-config-box {
    background: #f8fbff;
    border: 1px solid #dbeafe;
    border-radius: 8px;
    padding: 20px;
    margin-top: 5px;
    max-width: 860px;
}

.rewrite-header {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #2563eb;
    font-weight: 500;
    margin-bottom: 15px;
}

.rewrite-header i {
    font-size: 18px;
}

.rewrite-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 10px;
}

.rewrite-tab {
    padding: 6px 16px;
    background: #eef2ff;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.2s;
}

.rewrite-tab:hover {
    background: #dbeafe;
}

.rewrite-tab.active {
    background: #2563eb;
    color: #fff;
}

.rewrite-panel {
    display: none;
}

.rewrite-panel.active {
    display: block;
}

.rewrite-panel p {
    margin: 0 0 10px;
    color: #666;
    font-size: 13px;
}

.rewrite-panel ol {
    margin: 0 0 10px;
    padding-left: 20px;
    color: #666;
    font-size: 13px;
}

.rewrite-panel ol li {
    margin-bottom: 5px;
}

.rewrite-panel pre {
    background: #2d2d2d;
    color: #f8f8f2;
    padding: 15px;
    border-radius: 6px;
    overflow-x: auto;
    margin: 10px 0;
    font-size: 12px;
    line-height: 1.6;
}

.rewrite-panel code {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
}

.rewrite-panel .copy-btn {
    background: #2563eb;
    color: #fff;
    border: none;
    padding: 6px 14px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: background 0.2s;
}

.rewrite-panel .copy-btn:hover {
    background: #1d4ed8;
}

/* 深色模式 */
html[data-theme="dark"] .rewrite-config-box {
    background: #252525;
    border-color: #333;
}

html[data-theme="dark"] .rewrite-tabs {
    border-color: #444;
}

html[data-theme="dark"] .rewrite-tab {
    background: #333;
    color: #b0b0b0;
}

html[data-theme="dark"] .rewrite-tab:hover {
    background: #444;
}

html[data-theme="dark"] .rewrite-tab.active {
    background: #2563eb;
    color: #fff;
}

html[data-theme="dark"] .rewrite-panel p,
html[data-theme="dark"] .rewrite-panel ol {
    color: #b0b0b0;
}

html[data-theme="dark"] .rewrite-panel pre {
    background: #1a1a1a;
}
</style>