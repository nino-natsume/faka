<?php defined('DC_ROOT') || exit('access denied!'); ?>
<style>
    .ucfg-section { background: #ffffff85; border: 1px solid #eef1f4; border-radius: 8px; padding: 18px 20px; margin-bottom: 14px; }
    .ucfg-title { font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; }
    .ucfg-title i { color: #2563eb; }
    .ucfg-sub { font-size: 12px; font-weight: 600; color: #6b7280; margin: 14px 0 6px; padding: 6px 0 4px; border-bottom: 1px dashed #e5e7eb; display: flex; align-items: center; gap: 5px; }
    .ucfg-sub:first-child { margin-top: 0; }
    .ucfg-row { display: grid; grid-template-columns: 160px 1fr; gap: 10px; align-items: start; padding: 8px 0; }
    .ucfg-row > label { color: #374151; font-weight: 500; padding-top: 7px; }
    .ucfg-row .layui-input-inline { width: 320px; }
    .ucfg-tip { color: #6b7280; font-size: 12px; line-height: 1.7; margin-top: 4px; }
    .ucfg-tip b { color: #2563eb; }
    .ucfg-tip code { background: #f3f4f6; padding: 1px 5px; border-radius: 3px; font-size: 11px; color: #d946ef; }
    .ucfg-tip-summary { cursor: pointer; }
    .ucfg-tip-detail { display: none; margin-top: 2px; }
    .ucfg-tip-toggle { color: #2563eb; cursor: pointer; font-size: 11px; user-select: none; }
    .ucfg-tip-toggle:hover { text-decoration: underline; }
    .ucfg-tip-detail table { width: 100%; border-collapse: collapse; margin: 6px 0; font-size: 12px; }
    .ucfg-tip-detail table th,
    .ucfg-tip-detail table td { border: 1px solid #e5e7eb; padding: 5px 8px; text-align: left; }
    .ucfg-tip-detail table th { background: #f9fafb; font-weight: 600; color: #374151; }

    @media (max-width: 768px) {
        .layui-card-body { padding: 12px !important; }
        .ucfg-row { grid-template-columns: 1fr; gap: 4px; }
        .ucfg-row > label { padding-top: 0; font-size: 13px; }
        .ucfg-section { padding: 14px 12px; }
        .ucfg-row .layui-input-inline { width: 100%; }
        .ucfg-row .layui-input,
        .ucfg-row .layui-textarea,
        .ucfg-row select { max-width: 100%; }
        .ucfg-sub { font-size: 11px; }
        .ucfg-tip-detail table { font-size: 11px; display: block; overflow-x: auto; }
    }
</style>

<div class="layui-tabs order-tabs-wrapper" style="display:flex;align-items:center;justify-content:space-between;" lay-options="{trigger: false}">
    <ul class="layui-tabs-header">
        <li><a href="./shop.php">商城配置</a></li>
        <li><a href="./shop.php?action=gg">公告设置</a></li>
        <li><a href="./shop.php?action=btx">下单输入框</a></li>
        <li><a href="./shop.php?action=user">用户配置</a></li>
        <li class="layui-this"><a href="./shop.php?action=station_setting">分店配置</a></li>
    </ul>
</div>

<div class="layui-table-view" style="border-radius:8px;overflow:hidden;">
    <div class="layui-card-header" style="display:flex;align-items:center;justify-content:center;position:relative;height:auto;padding:12px 15px;">
        <span style="position:absolute;left:15px;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">分店配置</span>
    </div>
    <div class="layui-card-body" style="padding:20px;">
        <form action="shop.php?action=station_setting_save" method="post" name="setting_form" id="setting_form" class="layui-form">

            <!-- ========== 功能开关 ========== -->
            <div class="ucfg-section">
                <div class="ucfg-title"><i class="ri-toggle-line"></i> 功能开关</div>
                <div class="ucfg-row">
                    <label>分店功能总开关</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="station_switch" value="1" lay-skin="switch" lay-text="开启|关闭" <?= $station_switch == '1' ? 'checked' : '' ?>>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">开启 = 用户可开通分店卖货。关闭 = 完全隐藏分店功能。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail"><b>开启</b> = 用户可以花钱开通自己的分店店铺，用你的商品来卖货赚差价。<br><b>关闭</b> = 完全隐藏分店功能，所有用户都看不到也无法开通。<br>如果你的业务不需要分店模式，可以直接关闭。</span>
                        </div>
                    </div>
                </div>
                <div class="ucfg-row">
                    <label>升级计费方式</label>
                    <div>
                        <div class="layui-input-inline" style="width:200px;">
                            <select name="station_upgrade_price_mode" class="layui-select">
                                <option value="diff" <?= ($station_upgrade_price_mode ?? 'diff') === 'diff' ? 'selected' : '' ?>>补差价</option>
                                <option value="full" <?= ($station_upgrade_price_mode ?? 'diff') === 'full' ? 'selected' : '' ?>>按目标全额</option>
                            </select>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">用户升级分店等级时的收费方式。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail"><b>补差价</b>（推荐）= 只收目标等级价 − 当前等级价的差额。例：当前免费版升到 ¥100 入门版，只收 ¥100。从 ¥100 入门版升到 ¥300 标准版，只收 ¥200。<br><b>按目标全额</b> = 直接按目标等级原价收费。同样场景从 ¥100 入门版升到 ¥300 标准版，收 ¥300。<br><span style="color:#6b7280;">此设置仅影响升级场景，首次开通始终按原价收费。</span></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== 域名配置 ========== -->
            <div class="ucfg-section">
                <div class="ucfg-title"><i class="ri-global-line"></i> 域名配置</div>

                <div class="ucfg-row">
                    <label>分店使用域名</label>
                    <div>
                        <textarea class="layui-textarea" name="station_domain" placeholder="多个域名请使用回车换行" style="min-height:68px;"><?= $station_domain ?></textarea>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">分店二级域名可选后缀，每行一个。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail">
                                <b>作用</b>：分店用户在后台设置二级域名时，从这里选择后缀，再自填前缀。<br>
                                <b>示例</b>：填写 <code>dcshop.com</code>，分店用户填前缀 <code>shop1</code>，最终分店域名为 <code>shop1.dcshop.com</code>。<br><br>
                                <b>DNS 配置（必须）</b>：<br>
                                在域名 DNS 中添加<b>泛解析</b>记录：<br>
                                <table>
                                    <tr><th>记录类型</th><th>主机记录</th><th>记录值</th></tr>
                                    <tr><td>A</td><td><code>*</code></td><td>你的服务器 IP，如 <code>1.2.3.4</code></td></tr>
                                </table>
                                <b>宝塔/Nginx 配置</b>：新建站点，域名填 <code>*.dcshop.com</code>，网站目录指向本项目根目录。<br>
                                多个域名就多行填写，每个都需要做泛解析和站点配置。
                            </span>
                        </div>
                    </div>
                </div>

                <div class="ucfg-row">
                    <label>CNAME解析域名</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="text" name="station_cname_domain" class="layui-input" value="<?= $station_cname_domain ?>" />
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">分店绑定独立域名时，CNAME 指向的目标地址。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail">
                                <b>作用</b>：分店用户想用自己的域名（如 <code>shop.abc.com</code>）访问分店时，需要在自己的域名注册商处添加 CNAME 记录，指向这里填写的域名。<br><br>
                                <b>填写规则</b>：填一个已解析到本服务器的域名，如 <code>cname.dcshop.com</code> 或直接用主站域名 <code>www.dcshop.com</code>。<br><br>
                                <b>DNS 配置（必须）</b>：<br>
                                <table>
                                    <tr><th>配置方</th><th>记录类型</th><th>主机记录</th><th>记录值</th></tr>
                                    <tr><td><b>你（主站）</b></td><td>A</td><td><code>cname</code></td><td>你的服务器 IP，如 <code>1.2.3.4</code></td></tr>
                                    <tr><td><b>分店用户</b></td><td>CNAME</td><td><code>shop</code></td><td><code>cname.dcshop.com</code></td></tr>
                                </table>
                                <b>宝塔/Nginx 配置</b>：需要设置一个<b>默认站点</b>（兜底站点），让所有未匹配域名的请求也指向本项目目录，这样分店用户绑定的独立域名才能正确访问。<br><br>
                                <b>完整流程</b>：<br>
                                1. 你在此填写 <code>cname.dcshop.com</code> 并确保它 A 记录指向服务器<br>
                                2. 分店用户在分店设置中填写独立域名 <code>shop.abc.com</code><br>
                                3. 系统提示用户将 <code>shop.abc.com</code> CNAME 到 <code>cname.dcshop.com</code><br>
                                4. 用户在自己的域名注册商处配置 CNAME 后，访问 <code>shop.abc.com</code> → 请求到达你的服务器 → 系统自动匹配到对应分店
                            </span>
                        </div>
                    </div>
                </div>

                <div class="ucfg-row">
                    <label>保留域名前缀</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="text" name="station_domain_retain" class="layui-input" value="<?= $station_domain_retain ?>" placeholder="www,m,api,admin 逗号分隔" />
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">用户不可使用的保留前缀，防止占用关键子域名。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail">
                                <b>作用</b>：防止分店用户将 <code>www</code>、<code>m</code>、<code>api</code>、<code>admin</code> 等关键前缀注册为自己的二级域名。<br>
                                <b>填写规则</b>：多个保留字用英文逗号分隔，如 <code>www,m,api,admin,mail,ftp</code>。<br>
                                <b>示例</b>：配置 <code>www,m</code> 后，用户设置分店域名时就无法使用 <code>www</code> 和 <code>m</code> 作为域名前缀。<br>
                                留空则不做限制。
                            </span>
                        </div>
                    </div>
                </div>

                <div class="ucfg-row">
                    <label>修改域名价格</label>
                    <div>
                        <div class="layui-input-inline" style="width:160px;">
                            <input type="number" name="station_domain_change_price" class="layui-input" value="<?= $station_domain_change_price ?>" placeholder="0" min="0" step="0.01" />
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">用户修改域名需扣余额（元），填 0 = 免费。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail">
                                <b>作用</b>：防止用户频繁修改域名浪费 DNS 资源，设置收费门槛。<br>
                                <b>扣费规则</b>：用户提交修改域名请求时，系统从用户余额中扣除此金额。余额不足则拒绝修改。<br>
                                <b>首次设置</b>：用户首次绑定域名（原域名为空）不收费，仅修改时收费。<br>
                                填 <code>0</code> 表示免费修改，不做任何扣费。
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== 店铺标识 ========== -->
            <div class="ucfg-section">
                <div class="ucfg-title"><i class="ri-links-line"></i> 店铺标识</div>

                <div class="ucfg-row">
                    <label>启用店铺标识</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="station_slug_mode" value="1" lay-skin="switch" lay-text="开启|关闭" <?= $station_slug_mode == '1' ? 'checked' : '' ?>>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">开启后分店可通过 <code>/s/标识码</code> 路径访问，无需域名配置。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail">
                                <b>作用</b>：为分店提供零门槛的第三种访问方式，无需 DNS 泛解析或 CNAME。<br>
                                <b>访问方式</b>：用户首次访问 <code><?= htmlspecialchars(rtrim(Option::get('blogurl'), '/')) ?>/s/shop1</code> 后，系统自动记住分店身份（Cookie），后续直接访问主站域名即自动进入该分店。<br><br>
                                <b>三种模式对比</b>：<br>
                                <table>
                                    <tr><th>模式</th><th>示例</th><th>需要DNS</th></tr>
                                    <tr><td>二级域名</td><td><code>shop1.dcshop.com</code></td><td>✅ 泛解析</td></tr>
                                    <tr><td>独立域名</td><td><code>shop.abc.com</code></td><td>✅ CNAME</td></tr>
                                    <tr><td><b>短链接</b></td><td><code>dcshop.com/s/shop1</code></td><td>❌ 无需</td></tr>
                                </table>
                                三种模式可同时启用，互不冲突。分店用户可在后台自定义短链接后缀。<br>
                                <b>注意</b>：用户清除浏览器 Cookie 后需重新通过短链接进入。
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== 安全控制 ========== -->
            <div class="ucfg-section">
                <div class="ucfg-title"><i class="ri-shield-check-line"></i> 安全控制</div>

                <div class="ucfg-row">
                    <label>未绑定域名访问</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="station_domain_strict" value="1" lay-skin="switch" lay-text="拦截|放行" <?= $station_domain_strict == '1' ? 'checked' : '' ?>>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">放行 = 任意域名可访问；拦截 = 仅白名单和已绑定域名。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail">
                                <b>放行（默认）</b>：任何解析到本服务器的域名都能正常访问，不做限制。<br>
                                <b>拦截</b>：开启后，系统会在每次请求时检查当前访问域名：<br>
                                &nbsp;&nbsp;✅ 主站域名（自动识别）→ 放行<br>
                                &nbsp;&nbsp;✅ 下方"主站额外域名"白名单 → 放行<br>
                                &nbsp;&nbsp;✅ 已被分店绑定的域名（<code>domain</code> 或 <code>domain_2</code>）→ 放行<br>
                                &nbsp;&nbsp;❌ 其他未知域名 → 显示拦截提示页<br><br>
                                <b>适用场景</b>：开启泛解析后，防止他人将未授权的域名解析到你的服务器来蹭站。
                            </span>
                        </div>
                    </div>
                </div>

                <div class="ucfg-row">
                    <label>主站额外域名</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="text" name="station_extra_domains" class="layui-input" value="<?= $station_extra_domains ?>" placeholder="多个域名用逗号分隔" />
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">主站额外可访问域名白名单，仅开启拦截时生效。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail">
                                <b>作用</b>：当开启域名拦截后，主站可能还有其他域名需要正常访问（如备用域名、CDN 域名等），在此配置白名单。<br>
                                <b>填写规则</b>：多个域名用英文逗号分隔，如 <code>shop.example.com,m.example.com</code>。<br>
                                <b>注意</b>：主站默认域名无需填写，系统会自动放行。此项仅在上方"未绑定域名访问"设为"拦截"时生效。
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
            <div style="text-align:center;margin-top:10px;">
                <button type="submit" class="layui-btn" lay-submit lay-filter="demo1">保存设置</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </form>
    </div>
</div>

<script>
$(function () {
    // 高亮分店管理菜单
    $("#menu-station").addClass('open');
    $("#menu-station > .submenu").show();
    $("#menu-station > .link .admin-arrow").addClass('active');
    $("#menu-station-setting").addClass('active');

    // 提交表单
    $("#setting_form").submit(function (event) {
        event.preventDefault();
        submitForm("#setting_form");
    });

    // tip 折叠详细说明
    $(document).on('click', '.ucfg-tip-toggle', function(){
        var $tip = $(this).closest('.ucfg-tip');
        var $summary = $tip.find('.ucfg-tip-summary');
        var $detail = $tip.find('.ucfg-tip-detail');
        var isOpen = $detail.is(':visible');
        if (isOpen) {
            $detail.slideUp(150);
            $summary.show();
            $(this).text('详细说明 ▾');
        } else {
            $summary.hide();
            $detail.slideDown(150);
            var $innerToggle = $detail.find('.ucfg-tip-toggle');
            if (!$innerToggle.length) {
                $detail.append(' <span class="ucfg-tip-toggle">收起 ▴</span>');
            } else {
                $innerToggle.text('收起 ▴');
            }
        }
    });
});
</script>
