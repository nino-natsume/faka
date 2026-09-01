<?php
/**
 * 默认用户模板 - 模板配置文件
 *
 * 配置分类：
 * 1. 主题配色 - 主色、辅助色、强调色 + 预设方案
 * 2. 头部与侧边栏 - 头部背景色、侧边栏装饰、头部图标显示控制
 * 3. 自定义CSS - 高级用户自定义注入
 */

defined('DC_ROOT') || exit('access denied!');

function plugin_setting_view($tpl = 'default') {
    $tplOpt = TplOptions::getInstance();
    $tplKey = userTplSettingKey($tpl);
    $data = $tplOpt->getTemplateOptions($tplKey);
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $kind = isset($GLOBALS['__template_setting_kind']) ? trim((string)$GLOBALS['__template_setting_kind']) : '';
    $formAction = substr($script, -18) === '/user/template.php' && $kind === 'user_tpl'
        ? '?tpl=' . urlencode($tpl) . '&kind=user_tpl&action=setting_ajax'
        : '?tpl=' . urlencode($tpl) . '&action=user_setting_ajax';

    // ========== 主题配色 ==========
    $data['theme_primary']   = empty($data['theme_primary'])   ? '#2196F3' : $data['theme_primary'];
    $data['theme_secondary'] = empty($data['theme_secondary']) ? '#2f69d9' : $data['theme_secondary'];
    $data['theme_accent']    = empty($data['theme_accent'])    ? '#ff9800' : $data['theme_accent'];

    // ========== PC卡片色调 ==========
    $data['pc_card_gradient'] = isset($data['pc_card_gradient']) && $data['pc_card_gradient'] !== '' ? $data['pc_card_gradient'] : '';

    // ========== 移动端卡片色调 ==========
    $data['mobile_card_gradient'] = isset($data['mobile_card_gradient']) && $data['mobile_card_gradient'] !== '' ? $data['mobile_card_gradient'] : '';

    // ========== 头部设置 ==========
    $data['header_bg']             = isset($data['header_bg']) && $data['header_bg'] !== '' ? $data['header_bg'] : '';
    $data['header_title_color']    = isset($data['header_title_color']) && $data['header_title_color'] !== '' ? $data['header_title_color'] : '';
    $data['header_subtitle_color'] = isset($data['header_subtitle_color']) && $data['header_subtitle_color'] !== '' ? $data['header_subtitle_color'] : '';

    // ========== 侧边栏 ==========
    $data['sidebar_mac_dots'] = empty($data['sidebar_mac_dots']) ? 'y' : $data['sidebar_mac_dots'];

    // ========== 显示控制 ==========
    $data['show_order_icon']   = empty($data['show_order_icon'])   ? 'y' : $data['show_order_icon'];
    $data['show_bell_icon']    = empty($data['show_bell_icon'])    ? 'y' : $data['show_bell_icon'];
    $data['show_service_icon'] = empty($data['show_service_icon']) ? 'y' : $data['show_service_icon'];

    // ========== 自定义CSS ==========
    $data['custom_css'] = isset($data['custom_css']) ? $data['custom_css'] : '';
    ?>
    <style>
        #form-btn {
            background: #eee;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
            line-height: 50px;
            text-align: center;
            z-index: 100;
        }
        #open-box { padding-bottom: 60px; }
        .section-title {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px dashed #e6e6e6;
        }
        .section-title:first-child { margin-top: 0; padding-top: 0; border-top: none; }
        .section-title .layui-form-label { color: #333; font-weight: bold; font-size: 14px; }
        .switch-inline-item {
            display: inline-flex;
            align-items: center;
            margin-right: 25px;
            margin-bottom: 10px;
        }
        .switch-inline-item .switch-label {
            margin-right: 8px;
            color: #666;
            font-size: 13px;
        }
        .theme-presets { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 15px; }
        .theme-preset {
            width: 80px; height: 50px; border-radius: 8px; cursor: pointer;
            border: 2px solid transparent; transition: all 0.2s;
            position: relative; overflow: hidden;
        }
        .theme-preset:hover { transform: scale(1.05); }
        .theme-preset.active { border-color: #333; }
        .theme-preset::after {
            content: attr(data-name);
            position: absolute; bottom: 2px; left: 0; right: 0;
            text-align: center; font-size: 10px; color: #fff;
            text-shadow: 0 1px 2px rgba(0,0,0,0.5);
        }
        .color-input-group { display: flex; gap: 8px; align-items: center; }
        .color-input-group input[type="text"] { flex: 1; }
        .color-input-group input[type="color"] {
            width: 38px; height: 38px; padding: 0;
            border: 1px solid #e6e6e6; border-radius: 4px; cursor: pointer;
        }
        .tab-inner { padding: 10px 0 20px; }
        .tab-inner .layui-form-item { margin-bottom: 18px; }
        .group-label {
            font-weight: 600; color: #555; font-size: 13px;
            padding: 10px 0 6px; border-bottom: 1px solid #f0f0f0; margin-bottom: 14px;
        }
    </style>

    <form class="layui-form" id="form" method="post" action="<?= $formAction ?>">
    <input type="hidden" name="token" value="<?= htmlspecialchars(LoginAuth::genToken(), ENT_QUOTES) ?>">
    <div style="padding:15px 20px 80px;" id="open-box">
    <div class="layui-tab" lay-filter="setting-tab">
        <ul class="layui-tab-title">
            <li class="layui-this"><i class="ri-palette-line"></i> 主题配色</li>
            <li><i class="ri-layout-top-line"></i> 头部与侧边栏</li>
            <li><i class="ri-code-s-slash-line"></i> 自定义CSS</li>
        </ul>
        <div class="layui-tab-content">

            <!-- ===== TAB 1: 主题配色 ===== -->
            <div class="layui-tab-item layui-show"><div class="tab-inner">
                <div class="group-label">预设主题方案</div>
                <div class="layui-form-item">
                    <label class="layui-form-label">快速选择</label>
                    <div class="layui-input-block">
                        <div class="theme-presets">
                            <div class="theme-preset" data-name="默认蓝" data-primary="#2196F3" data-secondary="#2f69d9" data-accent="#ff9800" data-header="#0c6be1" data-titlecolor="#E1F5FE"
                                 style="background:linear-gradient(#0c6be1 0 14px, transparent 14px), linear-gradient(135deg,#2196F3 50%,#ff6600 50%);"></div>
                            <div class="theme-preset" data-name="科技紫" data-primary="#7c4dff" data-secondary="#651fff" data-accent="#ff9100" data-header="#651fff" data-titlecolor="#ff5722"
                                 style="background:linear-gradient(#651fff 0 14px, transparent 14px), linear-gradient(135deg,#7c4dff 50%,#ff5722 50%);"></div>
                            <div class="theme-preset" data-name="活力橙" data-primary="#ff9800" data-secondary="#f57c00" data-accent="#e91e63" data-header="#f57c00" data-titlecolor="#ffffff"
                                 style="background:linear-gradient(#f57c00 0 14px, transparent 14px), linear-gradient(135deg,#ff9800 50%,#e91e63 50%);"></div>
                            <div class="theme-preset" data-name="清新绿" data-primary="#4caf50" data-secondary="#388e3c" data-accent="#ff9800" data-header="#388e3c" data-titlecolor="#ffd740"
                                 style="background:linear-gradient(#388e3c 0 14px, transparent 14px), linear-gradient(135deg,#4caf50 50%,#ff5722 50%);"></div>
                            <div class="theme-preset" data-name="商务灰" data-primary="#607d8b" data-secondary="#455a64" data-accent="#ff9800" data-header="#455a64" data-titlecolor="#ff6600"
                                 style="background:linear-gradient(#455a64 0 14px, transparent 14px), linear-gradient(135deg,#607d8b 50%,#ff6600 50%);"></div>
                            <div class="theme-preset" data-name="玫瑰红" data-primary="#e91e63" data-secondary="#c2185b" data-accent="#ff9800" data-header="#c2185b" data-titlecolor="#ffd740"
                                 style="background:linear-gradient(#c2185b 0 14px, transparent 14px), linear-gradient(135deg,#e91e63 50%,#ff6600 50%);"></div>
                            <div class="theme-preset" data-name="青蓝" data-primary="#00bcd4" data-secondary="#0097a7" data-accent="#ffc107" data-header="#0097a7" data-titlecolor="#ffc107"
                                 style="background:linear-gradient(#0097a7 0 14px, transparent 14px), linear-gradient(135deg,#00bcd4 50%,#ff5722 50%);"></div>
                            <div class="theme-preset" data-name="黑金" data-primary="#d4af37" data-secondary="#262626" data-accent="#ff4757" data-header="#262626" data-titlecolor="#d4af37"
                                 style="background:linear-gradient(#262626 0 14px, transparent 14px), linear-gradient(135deg,#262626 50%,#d4af37 50%);"></div>
                        </div>
                    </div>
                </div>

                <div class="group-label">自定义颜色</div>
                <div class="layui-form-item">
                    <label class="layui-form-label">主色调</label>
                    <div class="layui-input-block">
                        <div class="color-input-group">
                            <input type="text" name="theme_primary" id="theme_primary" value="<?= htmlspecialchars($data['theme_primary']) ?>" class="layui-input" placeholder="#2196F3">
                            <input type="color" id="theme_primary_picker" value="<?= htmlspecialchars($data['theme_primary']) ?>">
                        </div>
                        <div class="layui-form-mid layui-word-aux">侧边栏图标、选中高亮、按钮等主色</div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">辅助色</label>
                    <div class="layui-input-block">
                        <div class="color-input-group">
                            <input type="text" name="theme_secondary" id="theme_secondary" value="<?= htmlspecialchars($data['theme_secondary']) ?>" class="layui-input" placeholder="#2f69d9">
                            <input type="color" id="theme_secondary_picker" value="<?= htmlspecialchars($data['theme_secondary']) ?>">
                        </div>
                        <div class="layui-form-mid layui-word-aux">渐变搭配色</div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">强调色</label>
                    <div class="layui-input-block">
                        <div class="color-input-group">
                            <input type="text" name="theme_accent" id="theme_accent" value="<?= htmlspecialchars($data['theme_accent']) ?>" class="layui-input" placeholder="#ff9800">
                            <input type="color" id="theme_accent_picker" value="<?= htmlspecialchars($data['theme_accent']) ?>">
                        </div>
                        <div class="layui-form-mid layui-word-aux">特殊强调元素的颜色</div>
                    </div>
                </div>
                <div class="group-label">PC 卡片色调</div>
                <div class="layui-form-item">
                    <label class="layui-form-label">快速选择</label>
                    <div class="layui-input-block">
                        <div class="card-tone-presets" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
                            <div class="card-tone-btn" data-gradient="linear-gradient(0deg, #f9fbff, #eff4fd)" style="width:72px;height:36px;border-radius:8px;cursor:pointer;border:2px solid transparent;background:linear-gradient(0deg, #f9fbff, #eff4fd);position:relative;transition:all .2s;" title="默认蓝"><span style="position:absolute;bottom:1px;left:0;right:0;text-align:center;font-size:9px;color:#667;">默认蓝</span></div>
                            <div class="card-tone-btn" data-gradient="linear-gradient(0deg, #fcfbff, #f8f5fe)" style="width:72px;height:36px;border-radius:8px;cursor:pointer;border:2px solid transparent;background:linear-gradient(0deg, #fcfbff, #f8f5fe);position:relative;transition:all .2s;" title="科技紫"><span style="position:absolute;bottom:1px;left:0;right:0;text-align:center;font-size:9px;color:#667;">科技紫</span></div>
                            <div class="card-tone-btn" data-gradient="linear-gradient(0deg, #fffcfb, #fef8f5)" style="width:72px;height:36px;border-radius:8px;cursor:pointer;border:2px solid transparent;background:linear-gradient(0deg, #fffcfb, #fef8f5);position:relative;transition:all .2s;" title="活力橙"><span style="position:absolute;bottom:1px;left:0;right:0;text-align:center;font-size:9px;color:#667;">活力橙</span></div>
                            <div class="card-tone-btn" data-gradient="linear-gradient(0deg, #fbfffc, #f5fef8)" style="width:72px;height:36px;border-radius:8px;cursor:pointer;border:2px solid transparent;background:linear-gradient(0deg, #fbfffc, #f5fef8);position:relative;transition:all .2s;" title="清新绿"><span style="position:absolute;bottom:1px;left:0;right:0;text-align:center;font-size:9px;color:#667;">清新绿</span></div>
                            <div class="card-tone-btn" data-gradient="linear-gradient(0deg, #fffbfc, #fef5f8)" style="width:72px;height:36px;border-radius:8px;cursor:pointer;border:2px solid transparent;background:linear-gradient(0deg, #fffbfc, #fef5f8);position:relative;transition:all .2s;" title="玫瑰红"><span style="position:absolute;bottom:1px;left:0;right:0;text-align:center;font-size:9px;color:#667;">玫瑰红</span></div>
                            <div class="card-tone-btn" data-gradient="linear-gradient(0deg, #fbfeff, #f5fdfe)" style="width:72px;height:36px;border-radius:8px;cursor:pointer;border:2px solid transparent;background:linear-gradient(0deg, #fbfeff, #f5fdfe);position:relative;transition:all .2s;" title="青蓝"><span style="position:absolute;bottom:1px;left:0;right:0;text-align:center;font-size:9px;color:#667;">青蓝</span></div>
                            <div class="card-tone-btn" data-gradient="linear-gradient(0deg, #fcfcfd, #f6f7f9)" style="width:72px;height:36px;border-radius:8px;cursor:pointer;border:2px solid transparent;background:linear-gradient(0deg, #fcfcfd, #f6f7f9);position:relative;transition:all .2s;" title="商务灰"><span style="position:absolute;bottom:1px;left:0;right:0;text-align:center;font-size:9px;color:#667;">商务灰</span></div>
                            <div class="card-tone-btn" data-gradient="linear-gradient(0deg, #fffefc, #fefbf5)" style="width:72px;height:36px;border-radius:8px;cursor:pointer;border:2px solid transparent;background:linear-gradient(0deg, #fffefc, #fefbf5);position:relative;transition:all .2s;" title="黑金"><span style="position:absolute;bottom:1px;left:0;right:0;text-align:center;font-size:9px;color:#667;">黑金</span></div>
                        </div>
                        <input type="text" name="pc_card_gradient" id="pc_card_gradient" value="<?= htmlspecialchars($data['pc_card_gradient']) ?>" class="layui-input" placeholder="留空则使用默认蓝色调 linear-gradient(0deg, #f9fbff, #eff4fd)" style="font-size:12px;">
                        <div class="layui-form-mid layui-word-aux">PC 个人中心所有卡片的背景渐变色调，留空使用默认蓝</div>
                        <div id="card-tone-preview" style="width:100%;height:40px;border-radius:8px;border:2px solid #fff;box-shadow:0 1px 18px #12345b0a;margin-top:8px;"></div>
                    </div>
                </div>
                <div class="group-label">移动端卡片色调</div>
                <div class="layui-form-item">
                    <label class="layui-form-label">渐变值</label>
                    <div class="layui-input-block">
                        <input type="text" name="mobile_card_gradient" id="mobile_card_gradient" value="<?= htmlspecialchars($data['mobile_card_gradient']) ?>" class="layui-input" placeholder="留空则使用经典白透 linear-gradient(135deg, rgba(255,255,255,0.92), rgba(255,255,255,0.76))" style="font-size:12px;">
                        <div class="layui-form-mid layui-word-aux">移动端个人中心顶部卡片背景渐变，留空使用经典白透</div>
                    </div>
                </div>
            </div></div>

            <!-- ===== TAB 2: 头部与侧边栏 ===== -->
            <div class="layui-tab-item"><div class="tab-inner">
                <div class="group-label">头部导航栏</div>
                <div class="layui-form-item">
                    <label class="layui-form-label">头部背景色</label>
                    <div class="layui-input-block">
                        <div class="color-input-group">
                            <input type="text" name="header_bg" id="header_bg" value="<?= htmlspecialchars($data['header_bg']) ?>" class="layui-input" placeholder="留空则跟随全局设置">
                            <input type="color" id="header_bg_picker" value="<?= $data['header_bg'] ?: '#0c6be1' ?>">
                        </div>
                        <div class="layui-form-mid layui-word-aux">留空则使用全局外观设置中的头部背景色</div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">标题文字色</label>
                    <div class="layui-input-block">
                        <div class="color-input-group">
                            <input type="text" name="header_title_color" id="header_title_color" value="<?= htmlspecialchars($data['header_title_color']) ?>" class="layui-input" placeholder="留空则跟随全局设置">
                            <input type="color" id="header_title_color_picker" value="<?= $data['header_title_color'] ?: '#ffffff' ?>">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">副标题文字色</label>
                    <div class="layui-input-block">
                        <div class="color-input-group">
                            <input type="text" name="header_subtitle_color" id="header_subtitle_color" value="<?= htmlspecialchars($data['header_subtitle_color']) ?>" class="layui-input" placeholder="留空则跟随全局设置">
                            <input type="color" id="header_subtitle_color_picker" value="<?= $data['header_subtitle_color'] ?: 'rgba(255,255,255,0.8)' ?>">
                        </div>
                    </div>
                </div>

                <div class="group-label">头部图标显示</div>
                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <div class="switch-inline-item">
                            <span class="switch-label">订单图标</span>
                            <input type="checkbox" name="show_order_icon" lay-skin="switch" lay-text="显示|隐藏" <?= $data['show_order_icon'] === 'y' ? 'checked' : '' ?>>
                        </div>
                        <div class="switch-inline-item">
                            <span class="switch-label">公告图标</span>
                            <input type="checkbox" name="show_bell_icon" lay-skin="switch" lay-text="显示|隐藏" <?= $data['show_bell_icon'] === 'y' ? 'checked' : '' ?>>
                        </div>
                        <div class="switch-inline-item">
                            <span class="switch-label">客服图标</span>
                            <input type="checkbox" name="show_service_icon" lay-skin="switch" lay-text="显示|隐藏" <?= $data['show_service_icon'] === 'y' ? 'checked' : '' ?>>
                        </div>
                    </div>
                </div>

                <div class="group-label">侧边栏</div>
                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <div class="switch-inline-item">
                            <span class="switch-label">macOS 三圆点装饰</span>
                            <input type="checkbox" name="sidebar_mac_dots" lay-skin="switch" lay-text="显示|隐藏" <?= $data['sidebar_mac_dots'] === 'y' ? 'checked' : '' ?>>
                        </div>
                    </div>
                </div>
            </div></div>

            <!-- ===== TAB 3: 自定义CSS ===== -->
            <div class="layui-tab-item"><div class="tab-inner">
                <div class="group-label">自定义 CSS</div>
                <div class="layui-form-item">
                    <label class="layui-form-label">CSS 代码</label>
                    <div class="layui-input-block">
                        <textarea name="custom_css" class="layui-textarea" style="min-height:200px;font-family:Consolas,monospace;font-size:13px;" placeholder="/* 在此输入自定义 CSS */"><?= htmlspecialchars($data['custom_css']) ?></textarea>
                        <div class="layui-form-mid layui-word-aux">会被注入到用户个人中心的 &lt;style&gt; 标签中，可覆盖任意样式</div>
                    </div>
                </div>
            </div></div>

        </div>
    </div>
    </div>

    <div id="form-btn">
        <button class="layui-btn" lay-submit lay-filter="form">保存配置</button>
    </div>
    </form>

    <script>
    layui.use(['form', 'layer', 'element'], function(){
        var form = layui.form;
        var layer = layui.layer;
        var $ = layui.$;

        // 颜色选择器同步
        function bindColorPicker(inputId, pickerId) {
            var $input = $('#' + inputId);
            var $picker = $('#' + pickerId);
            $picker.on('input change', function(){ $input.val(this.value); });
            $input.on('input change', function(){
                var v = $.trim(this.value);
                if (/^#[0-9a-fA-F]{6}$/.test(v)) { $picker.val(v); }
            });
        }
        bindColorPicker('theme_primary', 'theme_primary_picker');
        bindColorPicker('theme_secondary', 'theme_secondary_picker');
        bindColorPicker('theme_accent', 'theme_accent_picker');
        bindColorPicker('header_bg', 'header_bg_picker');
        bindColorPicker('header_title_color', 'header_title_color_picker');
        bindColorPicker('header_subtitle_color', 'header_subtitle_color_picker');

        // 主题预设
        $('.theme-preset').on('click', function(){
            var $el = $(this);
            $('.theme-preset').removeClass('active');
            $el.addClass('active');
            $('#theme_primary').val($el.data('primary'));
            $('#theme_primary_picker').val($el.data('primary'));
            $('#theme_secondary').val($el.data('secondary'));
            $('#theme_secondary_picker').val($el.data('secondary'));
            $('#theme_accent').val($el.data('accent'));
            $('#theme_accent_picker').val($el.data('accent'));
            var hdr = $el.data('header');
            if (hdr) {
                $('#header_bg').val(hdr);
                $('#header_bg_picker').val(hdr);
            }
            var tc = $el.data('titlecolor');
            if (tc) {
                $('#header_title_color').val(tc);
                if (/^#[0-9a-fA-F]{6}$/.test(tc)) $('#header_title_color_picker').val(tc);
            }
            // 联动卡片色调
            var cardToneMap = {
                '#2196F3': 'linear-gradient(0deg, #f9fbff, #eff4fd)',
                '#7c4dff': 'linear-gradient(0deg, #fcfbff, #f8f5fe)',
                '#ff9800': 'linear-gradient(0deg, #fffcfb, #fef8f5)',
                '#4caf50': 'linear-gradient(0deg, #fbfffc, #f5fef8)',
                '#607d8b': 'linear-gradient(0deg, #fcfcfd, #f6f7f9)',
                '#e91e63': 'linear-gradient(0deg, #fffbfc, #fef5f8)',
                '#00bcd4': 'linear-gradient(0deg, #fbfeff, #f5fdfe)',
                '#d4af37': 'linear-gradient(0deg, #fffefc, #fefbf5)'
            };
            var mappedTone = cardToneMap[$el.data('primary')] || '';
            if (mappedTone) {
                $('#pc_card_gradient').val(mappedTone);
                updateCardTonePreview();
                highlightCardTone();
            }
        });
        // 高亮当前预设
        var curP = $('#theme_primary').val(), curS = $('#theme_secondary').val(), curA = $('#theme_accent').val();
        $('.theme-preset').each(function(){
            if ($(this).data('primary') === curP && $(this).data('secondary') === curS && $(this).data('accent') === curA) {
                $(this).addClass('active');
            }
        });

        // 卡片色调预设
        function updateCardTonePreview() {
            var v = $.trim($('#pc_card_gradient').val()) || 'linear-gradient(0deg, #f9fbff, #eff4fd)';
            $('#card-tone-preview').css('background', v);
        }
        function highlightCardTone() {
            var cur = $.trim($('#pc_card_gradient').val());
            $('.card-tone-btn').css('border-color', 'transparent');
            $('.card-tone-btn').each(function(){
                if ($(this).data('gradient') === cur) $(this).css('border-color', '#333');
            });
        }
        $('.card-tone-btn').on('click', function(){
            var g = $(this).data('gradient');
            $('#pc_card_gradient').val(g);
            updateCardTonePreview();
            highlightCardTone();
        });
        $('#pc_card_gradient').on('input', function(){
            updateCardTonePreview();
            highlightCardTone();
        });
        updateCardTonePreview();
        highlightCardTone();

        // 表单提交
        form.on('submit(form)', function(formData){
            var postData = {
                token: formData.field.token || $('input[name="token"]').val() || '',
                theme_primary:   $.trim($('#theme_primary').val()),
                theme_secondary: $.trim($('#theme_secondary').val()),
                theme_accent:    $.trim($('#theme_accent').val()),
                header_bg:             $.trim($('#header_bg').val()),
                header_title_color:    $.trim($('#header_title_color').val()),
                header_subtitle_color: $.trim($('#header_subtitle_color').val()),
                show_order_icon:   formData.field.show_order_icon   === 'on' ? 'y' : 'n',
                show_bell_icon:    formData.field.show_bell_icon    === 'on' ? 'y' : 'n',
                show_service_icon: formData.field.show_service_icon === 'on' ? 'y' : 'n',
                sidebar_mac_dots:  formData.field.sidebar_mac_dots  === 'on' ? 'y' : 'n',
                pc_card_gradient:  $.trim($('#pc_card_gradient').val()),
                mobile_card_gradient: $.trim($('#mobile_card_gradient').val()),
                custom_css: formData.field.custom_css || ''
            };
            var idx = layer.load(1);
            $.post($('#form').attr('action'), postData, function(res){
                layer.close(idx);
                if (res.code === 0 || res.code === 200) {
                    var frameIndex = parent.layer.getFrameIndex(window.name);
                    if (frameIndex !== undefined && frameIndex !== null && frameIndex !== '') {
                        parent.layer.close(frameIndex);
                    }
                    parent.layer.msg('已保存配置');
                } else {
                    layer.msg(res.msg || '保存失败', {icon: 2});
                }
            }, 'json').fail(function(xhr){
                layer.close(idx);
                var msg = '请求失败';
                if (xhr && xhr.responseJSON && xhr.responseJSON.msg) {
                    msg = xhr.responseJSON.msg;
                } else if (xhr && xhr.responseText) {
                    try {
                        var json = JSON.parse(xhr.responseText);
                        if (json && json.msg) msg = json.msg;
                    } catch (e) {}
                }
                layer.msg(msg, {icon: 2});
            });
            return false;
        });
    });
    </script>
<?php
}

function plugin_setting($tpl = 'default') {
    $tplOpt = TplOptions::getInstance();
    $tpl = userTplSettingKey($tpl);

    $theme_primary         = isset($_POST['theme_primary'])         ? trim($_POST['theme_primary'])         : '#2196F3';
    $theme_secondary       = isset($_POST['theme_secondary'])       ? trim($_POST['theme_secondary'])       : '#2f69d9';
    $theme_accent          = isset($_POST['theme_accent'])          ? trim($_POST['theme_accent'])          : '#ff9800';
    $header_bg             = isset($_POST['header_bg'])             ? trim($_POST['header_bg'])             : '';
    $header_title_color    = isset($_POST['header_title_color'])    ? trim($_POST['header_title_color'])    : '';
    $header_subtitle_color = isset($_POST['header_subtitle_color']) ? trim($_POST['header_subtitle_color']) : '';
    $show_order_icon       = isset($_POST['show_order_icon'])       ? trim($_POST['show_order_icon'])       : 'n';
    $show_bell_icon        = isset($_POST['show_bell_icon'])        ? trim($_POST['show_bell_icon'])        : 'n';
    $show_service_icon     = isset($_POST['show_service_icon'])     ? trim($_POST['show_service_icon'])     : 'n';
    $sidebar_mac_dots      = isset($_POST['sidebar_mac_dots'])      ? trim($_POST['sidebar_mac_dots'])      : 'n';
    $custom_css            = isset($_POST['custom_css'])            ? trim($_POST['custom_css'])            : '';
    $pc_card_gradient      = isset($_POST['pc_card_gradient'])      ? trim($_POST['pc_card_gradient'])      : '';
    $mobile_card_gradient  = isset($_POST['mobile_card_gradient'])  ? trim($_POST['mobile_card_gradient'])  : '';

    // 简单校验颜色格式
    $colorFields = [&$theme_primary, &$theme_secondary, &$theme_accent, &$header_bg, &$header_title_color, &$header_subtitle_color];
    foreach ($colorFields as &$color) {
        if ($color !== '' && !preg_match('/^#[0-9a-fA-F]{3,8}$/', $color) && !preg_match('/^rgba?\(/', $color)) {
            $color = '';
        }
    }
    unset($color);

    $data = [
        ['template' => $tpl, 'name' => 'theme_primary',         'depend' => '', 'data' => serialize($theme_primary)],
        ['template' => $tpl, 'name' => 'theme_secondary',       'depend' => '', 'data' => serialize($theme_secondary)],
        ['template' => $tpl, 'name' => 'theme_accent',          'depend' => '', 'data' => serialize($theme_accent)],
        ['template' => $tpl, 'name' => 'header_bg',             'depend' => '', 'data' => serialize($header_bg)],
        ['template' => $tpl, 'name' => 'header_title_color',    'depend' => '', 'data' => serialize($header_title_color)],
        ['template' => $tpl, 'name' => 'header_subtitle_color', 'depend' => '', 'data' => serialize($header_subtitle_color)],
        ['template' => $tpl, 'name' => 'show_order_icon',       'depend' => '', 'data' => serialize($show_order_icon)],
        ['template' => $tpl, 'name' => 'show_bell_icon',        'depend' => '', 'data' => serialize($show_bell_icon)],
        ['template' => $tpl, 'name' => 'show_service_icon',     'depend' => '', 'data' => serialize($show_service_icon)],
        ['template' => $tpl, 'name' => 'sidebar_mac_dots',      'depend' => '', 'data' => serialize($sidebar_mac_dots)],
        ['template' => $tpl, 'name' => 'custom_css',            'depend' => '', 'data' => serialize($custom_css)],
        ['template' => $tpl, 'name' => 'pc_card_gradient',      'depend' => '', 'data' => serialize($pc_card_gradient)],
        ['template' => $tpl, 'name' => 'mobile_card_gradient',  'depend' => '', 'data' => serialize($mobile_card_gradient)],
    ];

    $tplOpt->insert('data', $data, true);
    Output::ok();
}
