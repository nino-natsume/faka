<?php defined('DC_ROOT') || exit('access denied!'); ?>

<style>
    .bcfg-section { background: #ffffff85; border: 1px solid #eef1f4; border-radius: 8px; padding: 18px 20px; margin-bottom: 14px; }
    .bcfg-title { font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; }
    .bcfg-title i { color: #2563eb; }
    .bcfg-row { display: grid; grid-template-columns: 160px 1fr; gap: 10px; align-items: start; padding: 8px 0; }
    .bcfg-row > label { color: #374151; font-weight: 500; padding-top: 10px; }
    .bcfg-row .layui-input-block { margin-left: 0; }
    .bcfg-row .layui-input,
    .bcfg-row .layui-textarea,
    .bcfg-row .layui-form-select,
    .bcfg-row .layui-select-title,
    .bcfg-row select { max-width: 860px; }
    .bcfg-tip { color: #6b7280; font-size: 12px; line-height: 1.7; margin-top: 8px; }
    .bcfg-actions { text-align: center; margin-top: 10px; }

    @media (max-width: 768px) {
        .layui-card-body { padding: 12px !important; }
        .bcfg-row { grid-template-columns: 1fr; gap: 4px; }
        .bcfg-row > label { padding-top: 0; font-size: 13px; }
        .bcfg-section { padding: 14px 12px; }
        .bcfg-row .layui-input,
        .bcfg-row .layui-textarea,
        .bcfg-row .layui-form-select,
        .bcfg-row .layui-select-title,
        .bcfg-row select { max-width: 100%; }
    }
</style>

<div class="layui-tabs order-tabs-wrapper" style="display:flex;align-items:center;justify-content:space-between;" lay-options="{trigger: false}">
    <ul class="layui-tabs-header">
        <li><a href="./setting.php">系统配置</a></li>
        <li class="layui-this"><a href="./setting.php?action=blog">博客配置</a></li>
        <li><a href="./setting.php?action=agreement">协议管理</a></li>
        <li><a href="./setting.php?action=seo">SEO设置</a></li>
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
        <span style="color:#667797;font-size:14px;font-weight:500;">博客配置</span>
    </div>
    <div class="layui-card-body" style="padding:20px;">
        <form action="setting.php?action=blog_save" method="post" name="blog_setting_form" id="blog_setting_form" class="layui-form">

            <div class="bcfg-section">
                <div class="bcfg-title"><i class="ri-global-line"></i>博客访问域名</div>
                <div class="bcfg-row">
                    <label>博客独立域名</label>
                    <div>
                        <textarea class="layui-textarea" name="blog_independent_domain" placeholder="例如：blog.example.com，多个域名可换行填写" style="min-height:68px;"><?= htmlspecialchars($blog_independent_domain ?? '', ENT_QUOTES) ?></textarea>
                        <div class="bcfg-tip">
                            填写后，对应域名访问根目录会直接进入博客首页；<code>/page/2</code> 会映射为博客分页。<br>
                            域名只填主机名，不需要填写 <code>http://</code>、<code>https://</code> 或路径。多个域名支持换行或英文逗号分隔。<br>
                            使用前请先将该域名 DNS 解析到当前服务器，并在宝塔/Nginx/Apache 中绑定到本站目录。<br>
                            <b>注意</b>：不要和分店独立域名共用同一个域名；如果重复配置，系统会优先按博客独立域名处理，可能导致分店无法通过该域名访问。
                        </div>
                    </div>
                </div>
            </div>

            <div class="bcfg-section">
                <div class="bcfg-title"><i class="ri-chat-3-line"></i>评论设置</div>
                <div class="bcfg-row">
                    <label>允许评论</label>
                    <div>
                        <input type="checkbox" name="iscomment" value="y" lay-skin="switch" lay-text="开启|关闭" <?= $conf_iscomment ?>>
                        <div class="bcfg-tip">全局评论总开关，关闭后所有文章和页面都无法发表评论。</div>
                    </div>
                </div>
                <div class="bcfg-row">
                    <label>评论审核</label>
                    <div>
                        <input type="checkbox" name="ischkcomment" value="y" lay-skin="switch" lay-text="开启|关闭" <?= $conf_ischkcomment ?>>
                        <div class="bcfg-tip">开启后访客评论需管理员审核后才会公开显示。</div>
                    </div>
                </div>
                <div class="bcfg-row">
                    <label>登录后评论</label>
                    <div>
                        <input type="checkbox" name="login_comment" value="y" lay-skin="switch" lay-text="开启|关闭" <?= $conf_login_comment ?>>
                        <div class="bcfg-tip">开启后仅登录用户可以发表评论。</div>
                    </div>
                </div>
                <div class="bcfg-row">
                    <label>评论验证码</label>
                    <div>
                        <input type="checkbox" name="comment_code" value="y" lay-skin="switch" lay-text="开启|关闭" <?= $conf_comment_code ?>>
                        <div class="bcfg-tip">开启后访客评论需输入验证码，需服务器支持 GD 库。</div>
                    </div>
                </div>
                <div class="bcfg-row">
                    <label>评论间隔（秒）</label>
                    <div>
                        <input class="layui-input" style="max-width:200px;" type="number" min="0" value="<?= $comment_interval ?>" name="comment_interval">
                        <div class="bcfg-tip">同一用户两次评论的最小间隔秒数，防止恶意刷评，设为 0 则不限制。</div>
                    </div>
                </div>
                <div class="bcfg-row">
                    <label>评论分页</label>
                    <div>
                        <input type="checkbox" name="comment_paging" value="y" lay-skin="switch" lay-text="开启|关闭" <?= $conf_comment_paging ?>>
                        <div class="bcfg-tip">开启后评论列表将按设定的每页条数进行分页。</div>
                    </div>
                </div>
                <div class="bcfg-row">
                    <label>每页评论数</label>
                    <div>
                        <input class="layui-input" style="max-width:200px;" type="number" min="1" value="<?= $comment_pnum ?>" name="comment_pnum">
                        <div class="bcfg-tip">开启评论分页后，每页显示的评论条数。</div>
                    </div>
                </div>
                <div class="bcfg-row">
                    <label>评论排序</label>
                    <div>
                        <select name="comment_order">
                            <option value="newer" <?= $ex3 ?>>新评论靠前</option>
                            <option value="older" <?= $ex4 ?>>旧评论靠前</option>
                        </select>
                        <div class="bcfg-tip">前台评论列表的默认排序方式。</div>
                    </div>
                </div>
            </div>

            <div class="bcfg-section">
                <div class="bcfg-title"><i class="ri-rss-line"></i>RSS 订阅</div>
                <div class="bcfg-row">
                    <label>RSS 输出条数</label>
                    <div>
                        <input class="layui-input" style="max-width:200px;" type="number" min="1" value="<?= $rss_output_num ?>" name="rss_output_num">
                        <div class="bcfg-tip">RSS 订阅源输出的最新文章条数。</div>
                    </div>
                </div>
                <div class="bcfg-row">
                    <label>RSS 输出全文</label>
                    <div>
                        <select name="rss_output_fulltext">
                            <option value="y" <?= $ex1 ?>>输出全文</option>
                            <option value="n" <?= $ex2 ?>>仅输出摘要</option>
                        </select>
                        <div class="bcfg-tip">RSS 输出全文可能增加带宽，仅输出摘要可引导用户访问站点。</div>
                    </div>
                </div>
            </div>

            <div class="bcfg-section">
                <div class="bcfg-title"><i class="ri-image-line"></i>图片与附件</div>
                <div class="bcfg-row">
                    <label>自动缩略图</label>
                    <div>
                        <input type="checkbox" name="isthumbnail" value="y" lay-skin="switch" lay-text="开启|关闭" <?= $conf_isthumbnail ?>>
                        <div class="bcfg-tip">开启后上传图片自动生成缩略图。</div>
                    </div>
                </div>
                <div class="bcfg-row">
                    <label>缩略图最大宽度</label>
                    <div>
                        <input class="layui-input" style="max-width:200px;" type="number" min="1" value="<?= $att_imgmaxw ?>" name="att_imgmaxw">
                        <div class="bcfg-tip">上传图片自动缩放的最大宽度（像素）。</div>
                    </div>
                </div>
                <div class="bcfg-row">
                    <label>缩略图最大高度</label>
                    <div>
                        <input class="layui-input" style="max-width:200px;" type="number" min="1" value="<?= $att_imgmaxh ?>" name="att_imgmaxh">
                        <div class="bcfg-tip">上传图片自动缩放的最大高度（像素）。</div>
                    </div>
                </div>
            </div>

            <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
            <div class="bcfg-actions">
                <button type="submit" class="layui-btn" lay-submit lay-filter="blog-setting">保存设置</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </form>
    </div>
</div>

<div style="height: 96px;"></div>

<script>
    $("#blog_setting_form").submit(function (event) {
        event.preventDefault();
        submitForm("#blog_setting_form");
    });
</script>
