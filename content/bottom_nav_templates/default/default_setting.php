<?php
defined('DC_ROOT') || exit('access denied!');

if (!function_exists('bottomNavDefaultSettingKey')) {
    function bottomNavDefaultSettingKey($tpl = 'default')
    {
        $tpl = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$tpl);
        return 'bottom_nav_' . ($tpl ?: 'default');
    }
}

if (!function_exists('bottomNavDefaultDefaultItems')) {
    function bottomNavDefaultDefaultItems()
    {
        return [
            [
                'enabled' => 'y',
                'type' => 'home',
                'title' => '首页',
                'url' => '',
                'icon' => 'ri-home-3-line',
                'active_icon' => 'ri-home-3-fill',
                'icon_image' => '',
                'active_icon_image' => '',
                'target' => '_self',
                'match_rule' => 'q=',
                'visible' => 'all',
            ],
            [
                'enabled' => 'y',
                'type' => 'order_query',
                'title' => '查单',
                'url' => '',
                'icon' => 'ri-file-search-line',
                'active_icon' => 'ri-file-search-fill',
                'icon_image' => '',
                'active_icon_image' => '',
                'target' => '_self',
                'match_rule' => '',
                'visible' => 'all',
            ],
            [
                'enabled' => 'y',
                'type' => 'user',
                'title' => '我的',
                'url' => '',
                'icon' => 'ri-user-4-line',
                'active_icon' => 'ri-user-4-fill',
                'icon_image' => '',
                'active_icon_image' => '',
                'target' => '_self',
                'match_rule' => '',
                'visible' => 'all',
            ],
        ];
    }
}

if (!function_exists('bottomNavDefaultSanitizeIconImage')) {
    function bottomNavDefaultSanitizeIconImage($url)
    {
        $url = trim((string)$url);
        if ($url === '') {
            return '';
        }
        $url = str_replace(["\r", "\n", '"', "'", '<', '>'], '', $url);
        if (preg_match('#^https?://#i', $url) || strpos($url, '//') === 0) {
            return $url;
        }
        if (strpos($url, '/') !== 0) {
            $url = '/' . ltrim($url, '/');
        }
        return preg_match('#^/[^\s]+$#', $url) ? $url : '';
    }
}

if (!function_exists('bottomNavDefaultDefaultConfig')) {
    function bottomNavDefaultDefaultConfig()
    {
        return [
            'show_on_desktop' => 'n',
            'show_label' => 'y',
            'enable_safe_area' => 'y',
            'show_user_center_auto' => 'n',
            'show_on_goods_detail' => 'n',
            'hide_on_links' => "help\n?action=order_query&contact=",
            'desktop_breakpoint' => '1200',
            'max_width' => '0',
            'side_gap' => '10',
            'bottom_offset' => '10',
            'container_padding' => '10',
            'item_gap' => '30',
            'container_radius' => '45',
            'item_radius' => '30',
            'item_height' => '50',
            'icon_size' => '20',
            'icon_translate_y' => '2',
            'text_size' => '11',
            'text_translate_y' => '0',
            'font_weight' => '500',
            'blur_strength' => '10',
            'z_index' => '1200',
            'shadow_y' => '14',
            'shadow_blur' => '34',
            'active_scale' => '1.04',
            'active_translate_y' => '-1',
            'background_color' => '#ffffff',
            'background_alpha' => '78',
            'background_color_dark' => '#111827',
            'background_alpha_dark' => '78',
            'border_color' => '#ffffff',
            'border_alpha' => '50',
            'border_color_dark' => '#ffffff',
            'border_alpha_dark' => '8',
            'text_color' => '#718096',
            'text_color_dark' => '#9ca3af',
            'active_text_color' => '#2196F3',
            'active_text_color_dark' => '#2196F3',
            'active_bg_color' => '#2196F3',
            'active_bg_color_dark' => '#2196F3',
            'active_bg_alpha' => '15',
            'active_bg_alpha_dark' => '15',
            'shadow_color' => '#0f172a',
            'shadow_alpha' => '14',
            'shadow_color_dark' => '#000000',
            'shadow_alpha_dark' => '28',
            'nav_items' => bottomNavDefaultDefaultItems(),
        ];
    }
}

if (!function_exists('bottomNavDefaultNormalizeItems')) {
    function bottomNavDefaultNormalizeItems($items)
    {
        $defaults = bottomNavDefaultDefaultItems();
        if (!is_array($items) || empty($items)) {
            return $defaults;
        }
        $normalized = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $normalized[] = [
                'enabled' => isset($item['enabled']) && $item['enabled'] === 'n' ? 'n' : 'y',
                'type' => !empty($item['type']) ? trim($item['type']) : 'custom',
                'title' => isset($item['title']) ? trim($item['title']) : '',
                'url' => isset($item['url']) ? trim($item['url']) : '',
                'icon' => isset($item['icon']) ? trim($item['icon']) : 'ri-menu-line',
                'active_icon' => isset($item['active_icon']) ? trim($item['active_icon']) : '',
                'icon_image' => bottomNavDefaultSanitizeIconImage($item['icon_image'] ?? ''),
                'active_icon_image' => bottomNavDefaultSanitizeIconImage($item['active_icon_image'] ?? ''),
                'target' => isset($item['target']) && $item['target'] === '_blank' ? '_blank' : '_self',
                'match_rule' => isset($item['match_rule']) ? trim($item['match_rule']) : '',
                'visible' => isset($item['visible']) && $item['visible'] === 'user_center_enabled' ? 'user_center_enabled' : 'all',
            ];
        }
        return empty($normalized) ? $defaults : $normalized;
    }
}

if (!function_exists('bottomNavDefaultConfig')) {
    function bottomNavDefaultConfig($tpl = 'default')
    {
        $tplOptions = TplOptions::getInstance();
        $defaults = bottomNavDefaultDefaultConfig();
        $saved = $tplOptions->getTemplateOptions(bottomNavDefaultSettingKey($tpl));
        if (!is_array($saved)) {
            $saved = [];
        }
        $data = array_merge($defaults, $saved);
        $data['nav_items'] = bottomNavDefaultNormalizeItems(isset($saved['nav_items']) ? $saved['nav_items'] : $defaults['nav_items']);
        return $data;
    }
}

if (!function_exists('bottomNavDefaultFormAction')) {
    function bottomNavDefaultFormAction($tpl = 'default')
    {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $kind = isset($GLOBALS['__template_setting_kind']) ? trim((string)$GLOBALS['__template_setting_kind']) : '';
        if (substr($script, -18) === '/user/template.php' && $kind === 'bottom_nav') {
            return '?action=setting_ajax&kind=bottom_nav&tpl=' . urlencode($tpl);
        }
        return '?action=bottom_nav_setting_ajax&tpl=' . urlencode($tpl);
    }
}

if (!function_exists('bottomNavDefaultRemixIconClassMap')) {
    function bottomNavDefaultRemixIconClassMap()
    {
        static $classMap = null;
        if ($classMap !== null) {
            return $classMap;
        }
        $classMap = [];
        $cssFile = DC_ROOT . 'admin/views/remixicon/remixicon.css';
        if (!is_file($cssFile) || !is_readable($cssFile)) {
            return $classMap;
        }
        $content = file_get_contents($cssFile);
        if ($content === false || $content === '') {
            return $classMap;
        }
        if (preg_match_all('/\.((?:ri-[a-z0-9-]+))\:before/i', $content, $matches) && !empty($matches[1])) {
            foreach ($matches[1] as $iconClass) {
                $classMap[strtolower($iconClass)] = true;
            }
        }
        return $classMap;
    }
}

if (!function_exists('bottomNavDefaultExpandRemixIconGroup')) {
    function bottomNavDefaultExpandRemixIconGroup($icons)
    {
        $classMap = bottomNavDefaultRemixIconClassMap();
        $expanded = [];
        foreach ((array)$icons as $icon) {
            $icon = trim((string)$icon);
            if ($icon === '') {
                continue;
            }
            if (!in_array($icon, $expanded, true)) {
                $expanded[] = $icon;
            }
            $pairIcon = '';
            if (preg_match('/-line$/', $icon)) {
                $pairIcon = preg_replace('/-line$/', '-fill', $icon);
            } elseif (preg_match('/-fill$/', $icon)) {
                $pairIcon = preg_replace('/-fill$/', '-line', $icon);
            }
            if ($pairIcon !== '' && isset($classMap[strtolower($pairIcon)]) && !in_array($pairIcon, $expanded, true)) {
                $expanded[] = $pairIcon;
            }
        }
        return $expanded;
    }
}

if (!function_exists('bottomNavDefaultRemixIcons')) {
    function bottomNavDefaultRemixIcons()
    {
        $groups = [
            '基础' => [
                'ri-home-5-line', 'ri-home-5-fill', 'ri-apps-2-line', 'ri-grid-line', 'ri-dashboard-3-line',
                'ri-menu-line', 'ri-links-line', 'ri-compass-3-line', 'ri-star-line', 'ri-heart-3-line',
            ],
            '订单交易' => [
                'ri-search-eye-line', 'ri-file-list-3-line', 'ri-shopping-bag-3-line', 'ri-shopping-cart-2-line', 'ri-secure-payment-line',
                'ri-bank-card-line', 'ri-coupon-3-line', 'ri-exchange-dollar-line', 'ri-history-line', 'ri-refund-2-line',
            ],
            '用户账号' => [
                'ri-user-3-line', 'ri-user-3-fill', 'ri-user-settings-line', 'ri-account-circle-line', 'ri-login-box-line',
                'ri-logout-box-r-line', 'ri-vip-crown-line', 'ri-team-line', 'ri-user-heart-line', 'ri-id-card-line',
            ],
            '客服帮助' => [
                'ri-customer-service-2-line', 'ri-question-line', 'ri-information-2-line', 'ri-message-3-line', 'ri-chat-3-line',
                'ri-phone-line', 'ri-mail-line', 'ri-service-line', 'ri-shield-check-line', 'ri-notification-3-line',
            ],
            '内容功能' => [
                'ri-store-2-line', 'ri-gift-2-line', 'ri-medal-2-line', 'ri-rocket-2-line', 'ri-flashlight-line',
                'ri-book-open-line', 'ri-bookmark-3-line', 'ri-image-line', 'ri-play-circle-line', 'ri-settings-3-line',
            ],
        ];
        foreach ($groups as $group => $icons) {
            $groups[$group] = bottomNavDefaultExpandRemixIconGroup($icons);
        }
        return $groups;
    }
}

function plugin_setting_view($tpl = 'default')
{
    $data = bottomNavDefaultConfig($tpl);
    $defaultConfigJson = json_encode(bottomNavDefaultDefaultConfig(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $defaultItemsJson = json_encode(bottomNavDefaultDefaultItems(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $iconGroups = bottomNavDefaultRemixIcons();
    $iconGroupsJson = json_encode($iconGroups, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    ?>
    <style>
        :root { --bn-footer-height: 72px; --bn-shell-gap: 22px; }
        html, body { height: 100%; overflow: hidden; background: #f6f8fb; }
        #form { height: 100%; }
        #open-box { height: 320px; min-height: 320px; padding: var(--bn-shell-gap); box-sizing: border-box; overflow: hidden; }
        #form-btn { position: fixed; left: 0; right: 0; bottom: 0; min-height: var(--bn-footer-height); padding: 12px 18px; box-sizing: border-box; background: rgba(255,255,255,0.96); box-shadow: 0 -8px 20px rgba(15,23,42,0.06); z-index: 999; display: flex; align-items: center; justify-content: center; gap: 12px; }
        #form-btn .layui-btn { min-width: 136px; }
        .bn-panel { background: #fff; border-radius: 14px; box-shadow: 0 6px 20px rgba(15,23,42,0.05); padding: 16px; margin-bottom: 12px; }
        .bn-panel-title { font-size: 15px; font-weight: 700; color: #1f2937; margin-bottom: 6px; }
        .bn-panel-desc { color: #94a3b8; font-size: 12px; margin-bottom: 12px; }
        .bn-section-stack { display: flex; flex-direction: column; gap: 12px; }
        .bn-section-card { border: 1px solid #e8eef5; border-radius: 12px; background: linear-gradient(180deg, #fbfdff 0%, #f8fbff 100%); padding: 12px; }
        .bn-section-title { font-size: 13px; font-weight: 700; color: #1f2937; margin-bottom: 10px; }
        .bn-settings-grid { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 10px 12px; }
        .bn-field { min-width: 0; grid-column: span 3; }
        .bn-field.span-2 { grid-column: span 2; }
        .bn-field.span-3 { grid-column: span 3; }
        .bn-field.span-4 { grid-column: span 4; }
        .bn-field.span-6 { grid-column: span 6; }
        .bn-field.span-8 { grid-column: span 8; }
        .bn-field.span-12 { grid-column: span 12; }
        .bn-grid-label { display: block; margin-bottom: 6px; color: #4b5563; font-size: 12px; font-weight: 600; }
        .bn-switch-item { display: inline-flex; align-items: center; gap: 10px; }
        .bn-switch-item .txt { color: #374151; font-size: 12px; font-weight: 600; }
        .bn-switch-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; }
        .bn-switch-row .bn-switch-item { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 0; padding: 8px 10px; border: 1px solid #e8eef5; border-radius: 12px; background: #fff; }
        .bn-switch-row .bn-switch-item .txt { color: #374151; font-size: 12px; font-weight: 600; }
        .bn-color-group { display: flex; align-items: center; gap: 8px; }
        .bn-color-group input[type="text"] { flex: 1; }
        .bn-color-group input[type="color"] { width: 40px; height: 38px; border: 1px solid #e5e7eb; border-radius: 6px; padding: 0; cursor: pointer; }
        .bn-settings-column .layui-input,
        .bn-settings-column .layui-select-title input { height: 34px; line-height: 34px; border-radius: 10px; font-size: 12px; }
        .bn-settings-column .layui-textarea { min-height: 78px; border-radius: 10px; font-size: 12px; padding: 10px 12px; }
        .bn-settings-column .layui-form-select .layui-edge { right: 10px; }
        .bn-item-list { display: flex; flex-direction: column; gap: 14px; }
        .bn-item-card { border: 1px solid #e5e7eb; border-radius: 12px; background: #fbfdff; overflow: hidden; }
        .bn-item-head { display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%); border-bottom: 1px solid #e5e7eb; }
        .bn-item-head-left { display: flex; align-items: center; gap: 10px; }
        .bn-item-index { width: 28px; height: 28px; border-radius: 50%; background: #1e9fff; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; }
        .bn-item-title { font-size: 14px; font-weight: 700; color: #1f2937; }
        .bn-item-body { padding: 12px 12px 2px; }
        .bn-item-actions { display: flex; align-items: center; gap: 10px; }
        .bn-item-remove { color: #ef4444; cursor: pointer; font-size: 18px; }
        .bn-item-remove:hover { color: #dc2626; }
        .bn-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-top: 16px; }
        .bn-help { color: #6b7280; font-size: 12px; line-height: 1.8; }
        .bn-icon-input-wrap { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .bn-icon-input-wrap .layui-input { flex: 1; min-width: 160px; }
        .bn-icon-preview { width: 38px; height: 38px; border-radius: 10px; border: 1px solid #e5e7eb; background: #fff; display: inline-flex; align-items: center; justify-content: center; color: #111827; font-size: 18px; flex-shrink: 0; }
        .bn-icon-preview img { width: 100%; height: 100%; object-fit: contain; border-radius: inherit; }
        .bn-icon-trigger { flex-shrink: 0; }
        .bn-icon-upload-box { padding: 0; border: 0; border-radius: 0; background: transparent; display: inline-flex; align-items: center; gap: 8px; min-height: 0; flex-shrink: 0; }
        .bn-icon-upload-main { min-width: 0; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .bn-icon-upload-actions { display: flex; flex-wrap: wrap; gap: 6px; }
        .bn-icon-upload-actions .layui-btn,
        .bn-icon-trigger,
        .bn-toolbar .layui-btn {
            margin: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border-radius: 10px;
            font-weight: 700;
            letter-spacing: .01em;
            transition: all .2s ease;
        }
        .bn-icon-upload-actions .layui-btn,
        .bn-icon-trigger {
            padding: 0 12px;
            height: 30px;
            line-height: 30px;
            font-size: 12px;
            border: 1px solid #dbe4f0;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            color: #475569;
            box-shadow: 0 4px 12px rgba(15,23,42,.06);
        }
        .bn-icon-upload-actions .layui-btn:hover,
        .bn-icon-trigger:hover {
            transform: translateY(-1px);
            border-color: #93c5fd;
            box-shadow: 0 8px 18px rgba(59,130,246,.14);
        }
        .bn-icon-upload-trigger {
            color: #2563eb;
            background: linear-gradient(180deg, #ffffff 0%, #eff6ff 100%);
        }
        .bn-icon-image-clear {
            color: #c2410c;
            border-color: #fed7aa;
            background: linear-gradient(180deg, #fff7ed 0%, #fffbeb 100%);
        }
        .bn-icon-image-clear:hover {
            border-color: #fdba74;
            box-shadow: 0 8px 18px rgba(249,115,22,.14);
        }
        .bn-icon-trigger {
            color: #fff;
            border-color: transparent;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            box-shadow: 0 10px 22px rgba(37,99,235,.24);
        }
        .bn-icon-trigger:hover {
            color: #fff;
            border-color: transparent;
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            box-shadow: 0 12px 24px rgba(37,99,235,.3);
        }
        .bn-toolbar .layui-btn {
            min-height: 40px;
            padding: 0 18px;
            font-size: 13px;
            border: 1px solid #dbe4f0;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            color: #334155;
            box-shadow: 0 8px 20px rgba(15,23,42,.06);
        }
        .bn-toolbar .layui-btn i { font-size: 16px; }
        .bn-toolbar .layui-btn:hover {
            transform: translateY(-1px);
            border-color: #93c5fd;
            color: #0f172a;
            box-shadow: 0 12px 24px rgba(59,130,246,.12);
        }
        #bn-add-item {
            color: #fff;
            border-color: transparent;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            box-shadow: 0 12px 24px rgba(37,99,235,.22);
        }
        #bn-add-item:hover {
            color: #fff;
            border-color: transparent;
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            box-shadow: 0 14px 28px rgba(37,99,235,.28);
        }
        #bn-reset-default {
            color: #c2410c;
            border-color: #fed7aa;
            background: linear-gradient(180deg, #fff7ed 0%, #fffbeb 100%);
            box-shadow: 0 8px 20px rgba(249,115,22,.12);
        }
        #bn-reset-default:hover {
            color: #9a3412;
            border-color: #fdba74;
            box-shadow: 0 12px 24px rgba(249,115,22,.16);
        }
        .bn-icon-upload-path { display: none; }
        .bn-icon-dialog { padding: 16px; }
        .bn-icon-search { margin-bottom: 14px; }
        .bn-icon-group { margin-bottom: 18px; }
        .bn-icon-group:last-child { margin-bottom: 0; }
        .bn-icon-group-title { font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 10px; }
        .bn-icon-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(72px, 1fr)); gap: 10px; }
        .bn-icon-grid-item { border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; padding: 10px 6px; text-align: center; cursor: pointer; transition: all .2s ease; }
        .bn-icon-grid-item:hover { border-color: #1e9fff; box-shadow: 0 6px 16px rgba(30,159,255,.08); transform: translateY(-1px); }
        .bn-icon-grid-item.active { border-color: #1e9fff; background: rgba(30,159,255,.06); box-shadow: 0 0 0 3px rgba(30,159,255,.08); }
        .bn-icon-grid-item i { display: block; font-size: 22px; color: #1f2937; margin-bottom: 8px; }
        .bn-icon-grid-item span { display: block; font-size: 11px; color: #6b7280; line-height: 1.35; word-break: break-all; }
        .bn-workbench { display: grid; grid-template-columns: 220px minmax(360px, 440px) minmax(620px, 1fr); gap: 20px; align-items: start; height: 100%; min-height: 0; }
        .bn-workbench.hide-preview { grid-template-columns: 220px minmax(620px, 1fr); }
        .bn-side-menu, .bn-preview-column, .bn-settings-column { min-width: 0; min-height: 0; }
        .bn-workbench.hide-preview .bn-preview-column { display: none; }
        .bn-workbench.hide-preview [data-panel-target="bn-panel-preview"] { display: none; }
        .bn-side-menu-inner { position: sticky; top: 22px; background: #fff; border-radius: 18px; box-shadow: 0 10px 28px rgba(15,23,42,.08); padding: 18px 14px; }
        .bn-side-menu-title { font-size: 14px; font-weight: 700; color: #111827; margin-bottom: 14px; padding: 0 8px; }
        .bn-side-menu-list { display: flex; flex-direction: column; gap: 8px; }
        .bn-side-item { width: 100%; border: 1px solid #e5e7eb; background: linear-gradient(180deg, #fbfdff 0%, #f6f9fd 100%); color: #475569; border-radius: 14px; padding: 12px 14px; text-align: left; display: flex; align-items: center; gap: 10px; cursor: pointer; transition: all .2s ease; }
        .bn-side-item:hover { border-color: #1e9fff; color: #1e9fff; transform: translateY(-1px); }
        .bn-side-item.active { border-color: #1e9fff; color: #1e9fff; background: rgba(30,159,255,.08); box-shadow: 0 0 0 3px rgba(30,159,255,.08); }
        .bn-side-dot { width: 10px; height: 10px; border-radius: 50%; background: currentColor; opacity: .88; }
        .bn-side-text { font-size: 13px; font-weight: 700; }
        .bn-preview-column .bn-panel { margin-bottom: 0; height: 100%; display: flex; flex-direction: column; }
        .bn-settings-column { height: 100%; overflow-y: auto; overflow-x: hidden; padding-right: 8px; padding-bottom: 28px; box-sizing: border-box; }
        .bn-settings-column::-webkit-scrollbar { width: 8px; }
        .bn-settings-column::-webkit-scrollbar-thumb { background: rgba(148,163,184,.45); border-radius: 999px; }
        .bn-settings-column::-webkit-scrollbar-track { background: transparent; }
        .bn-settings-column .bn-panel:last-child { margin-bottom: 0; }
        .bn-settings-bottom-spacer { height: calc(var(--bn-footer-height) + 24px); flex: 0 0 auto; }
        .bn-preview-shell { display: grid; grid-template-columns: 1fr; gap: 14px; align-items: center; }
        .bn-preview-stage { display: flex; justify-content: center; }
        .bn-preview-phone { width: 330px; padding: 12px; border-radius: 15px; background: linear-gradient(180deg, #2b3445 0%, #111827 100%); box-shadow: 0 20px 50px rgba(15,23,42,.22); }
        .bn-preview-phone[data-theme="dark"] { background: linear-gradient(180deg, #151a24 0%, #090d14 100%); }
        .bn-preview-screen { position: relative; height: 620px; border-radius: 10px; overflow: hidden; background: linear-gradient(180deg, #f8fbff 0%, #eef4ff 100%); }
        .bn-preview-phone[data-theme="dark"] .bn-preview-screen { background: linear-gradient(180deg, #111827 0%, #0b1220 100%); }
        .bn-preview-notch { position: absolute; left: 50%; top: 0px; transform: translateX(-50%); width: 118px; height: 24px; border-radius: 0 0 18px 18px; background: rgba(17,24,39,.94); z-index: 2; }
        .bn-preview-topbar { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding: 44px 18px 0; }
        .bn-preview-badge { display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 999px; background: rgba(255,255,255,.72); color: #1f2937; font-size: 12px; font-weight: 700; backdrop-filter: blur(12px); }
        .bn-preview-phone[data-theme="dark"] .bn-preview-badge { background: rgba(255,255,255,.08); color: #e5e7eb; }
        .bn-preview-toolbar { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 8px; }
        .bn-preview-toggle { border: 1px solid #dbe4f0; background: rgba(255,255,255,.8); color: #475569; border-radius: 999px; padding: 6px 11px; font-size: 12px; line-height: 1; cursor: pointer; transition: all .2s ease; }
        .bn-preview-toggle.active { border-color: #1e9fff; color: #1e9fff; background: rgba(30,159,255,.08); box-shadow: 0 0 0 3px rgba(30,159,255,.08); }
        .bn-preview-content { padding: 18px; display: flex; flex-direction: column; gap: 12px; }
        .bn-preview-card { border-radius: 18px; background: rgba(255,255,255,.76); backdrop-filter: blur(12px); box-shadow: 0 8px 24px rgba(15,23,42,.08); padding: 16px; }
        .bn-preview-phone[data-theme="dark"] .bn-preview-card { background: rgba(255,255,255,.06); box-shadow: 0 8px 24px rgba(0,0,0,.2); }
        .bn-preview-card.small { padding: 14px 16px; }
        .bn-preview-card-title { font-size: 15px; font-weight: 700; color: #111827; margin-bottom: 6px; }
        .bn-preview-phone[data-theme="dark"] .bn-preview-card-title { color: #f9fafb; }
        .bn-preview-card-text { font-size: 12px; line-height: 1.8; color: #64748b; }
        .bn-preview-phone[data-theme="dark"] .bn-preview-card-text { color: #94a3b8; }
        .bn-preview-meta { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .bn-preview-stat { border-radius: 16px; background: rgba(255,255,255,.66); padding: 14px; box-shadow: 0 8px 24px rgba(15,23,42,.06); }
        .bn-preview-phone[data-theme="dark"] .bn-preview-stat { background: rgba(255,255,255,.05); box-shadow: 0 8px 24px rgba(0,0,0,.18); }
        .bn-preview-stat strong { display: block; color: #111827; font-size: 15px; font-weight: 700; margin-bottom: 4px; }
        .bn-preview-phone[data-theme="dark"] .bn-preview-stat strong { color: #f9fafb; }
        .bn-preview-stat span { font-size: 12px; color: #64748b; }
        .bn-preview-phone[data-theme="dark"] .bn-preview-stat span { color: #94a3b8; }
        .bn-preview-bottom-nav { position: absolute; left: var(--preview-side-gap, 14px); right: var(--preview-side-gap, 14px); bottom: var(--preview-bottom, 14px); display: flex; gap: var(--preview-gap, 8px); padding: var(--preview-padding, 8px); border-radius: var(--preview-radius, 22px); background: var(--preview-bg, rgba(255,255,255,.78)); border: 1px solid var(--preview-border, rgba(255,255,255,.5)); box-shadow: var(--preview-shadow, 0 14px 34px rgba(15,23,42,.14)); backdrop-filter: blur(var(--preview-blur, 24px)); }
        .bn-preview-bottom-nav.is-empty { align-items: center; justify-content: center; min-height: 92px; }
        .bn-preview-item { flex: 1; min-width: 0; min-height: var(--preview-item-height, 54px); border-radius: var(--preview-item-radius, 16px); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: var(--preview-item-gap, 3px); color: var(--preview-text, #718096); transition: all .2s ease; cursor: pointer; user-select: none; overflow: visible; }
        .bn-preview-item i { font-size: var(--preview-icon-size, 20px); line-height: 1; margin-bottom: 0; flex-shrink: 0; transform: translateY(var(--preview-icon-translate, 0px)); transition: transform .2s ease; }
        .bn-preview-item img { width: var(--preview-icon-size, 20px); height: var(--preview-icon-size, 20px); object-fit: contain; display: block; margin-bottom: 0; flex-shrink: 0; transform: translateY(var(--preview-icon-translate, 0px)); transition: transform .2s ease; }
        .bn-preview-item span { display: block; width: 100%; font-size: var(--preview-text-size, 11px); line-height: 1.3; text-align: center; box-sizing: border-box; padding-bottom: .14em; position: relative; top: var(--preview-text-translate, 0px); flex-shrink: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; transition: top .2s ease; }
        .bn-preview-item:hover { background: rgba(255,255,255,.18); }
        .bn-preview-item.active { color: var(--preview-active-color, #2196f3); background: var(--preview-active-bg, rgba(33,150,243,.12)); font-weight: var(--preview-font-weight, 600); box-shadow: inset 0 0 0 1px var(--preview-active-bg, rgba(33,150,243,.12)); }
        .bn-preview-item.active i,
        .bn-preview-item.active img { transform: translateY(calc(var(--preview-icon-translate, 0px) + var(--preview-active-translate, -1px))) scale(var(--preview-active-scale, 1.04)); }
        .bn-preview-empty { color: var(--preview-text, #718096); font-size: 12px; text-align: center; line-height: 1.8; }
        .bn-range-origin-label,
        .bn-range-origin-input { display: none !important; }
        .bn-range-box { margin-top: 6px; padding: 8px 10px; border: 1px solid #e8eef5; border-radius: 10px; background: linear-gradient(180deg, #fbfdff 0%, #f6f9fd 100%); }
        .bn-range-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 6px; font-size: 12px; color: #64748b; }
        .bn-range-value { color: #1e9fff; font-weight: 700; }
        .bn-range-slider { width: 100%; accent-color: #1e9fff; cursor: pointer; }
        @media (max-width: 768px) {
            html, body { height: auto; overflow: auto; }
            #form { height: auto; }
            #open-box { height: auto; padding: 14px 14px calc(var(--bn-footer-height) + 14px); overflow: visible; }
            .bn-panel { padding: 16px; border-radius: 12px; }
            .bn-workbench { grid-template-columns: 1fr; height: auto; }
            .bn-workbench.hide-preview { grid-template-columns: 1fr; }
            .bn-side-menu { display: none; }
            .bn-side-menu-inner, .bn-preview-column .bn-panel { position: static; }
            .bn-preview-column .bn-panel { height: auto; }
            .bn-settings-column { height: auto; overflow: visible; padding-right: 0; padding-bottom: 0; }
            .bn-settings-bottom-spacer { display: none; }
            .bn-preview-phone { width: 100%; max-width: 340px; }
            .bn-item-head { flex-direction: column; align-items: flex-start; gap: 10px; }
            .bn-item-actions { width: 100%; justify-content: space-between; }
            .bn-icon-upload-box { width: 100%; }
            .bn-icon-upload-main { width: 100%; }
            .bn-icon-upload-actions { width: 100%; }
            .bn-icon-upload-actions .layui-btn,
            .bn-icon-trigger,
            .bn-toolbar .layui-btn { width: 100%; }
            .bn-settings-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .bn-field,
            .bn-field.span-2,
            .bn-field.span-3,
            .bn-field.span-4,
            .bn-field.span-6,
            .bn-field.span-8,
            .bn-field.span-12 { grid-column: span 2; }
        }
    </style>
    <form class="layui-form" id="form" method="post" action="<?= bottomNavDefaultFormAction($tpl) ?>">
        <input type="hidden" name="token" value="<?= htmlspecialchars(LoginAuth::genToken(), ENT_QUOTES) ?>">
        <div id="open-box">
            <div class="bn-workbench">
                <div class="bn-side-menu">
                    <div class="bn-side-menu-inner">
                        <div class="bn-side-menu-title">底部导航工作台</div>
                        <div class="bn-side-menu-list">
                            <button type="button" class="bn-side-item active" data-panel-target="bn-panel-preview">
                                <span class="bn-side-dot"></span>
                                <span class="bn-side-text">实时预览</span>
                            </button>
                            <button type="button" class="bn-side-item" data-panel-target="bn-panel-layout">
                                <span class="bn-side-dot"></span>
                                <span class="bn-side-text">显示与布局</span>
                            </button>
                            <button type="button" class="bn-side-item" data-panel-target="bn-panel-colors">
                                <span class="bn-side-dot"></span>
                                <span class="bn-side-text">颜色与透明度</span>
                            </button>
                            <button type="button" class="bn-side-item" data-panel-target="bn-panel-items">
                                <span class="bn-side-dot"></span>
                                <span class="bn-side-text">导航项目</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="bn-preview-column">
            <div class="bn-panel" id="bn-panel-preview">
                <div class="bn-panel-title">实时预览</div>
                <div class="bn-panel-desc">修改图标、颜色、尺寸后，这里的底部导航会立即同步。</div>
                <div class="bn-preview-shell">
                    <div class="bn-preview-stage">
                        <div class="bn-preview-phone" id="bn-live-preview-root" data-theme="light">
                            <div class="bn-preview-screen">
                                <div class="bn-preview-notch"></div>
                                <div class="bn-preview-topbar">
                                    <span class="bn-preview-badge">Default 预览</span>
                                    <div class="bn-preview-toolbar">
                                        <button type="button" class="bn-preview-toggle active" data-preview-theme="light">浅色</button>
                                        <button type="button" class="bn-preview-toggle" data-preview-theme="dark">深色</button>
                                    </div>
                                </div>
                                <div class="bn-preview-content">
                                    <div class="bn-preview-card">
                                        <div class="bn-preview-card-title">当前模板页面预览</div>
                                        <div class="bn-preview-card-text">这里模拟的是 default 移动端页面主体区域，底部导航会按照你当前正在编辑的配置即时变化。</div>
                                    </div>
                                    <div class="bn-preview-meta">
                                        <div class="bn-preview-stat">
                                            <strong id="bn-preview-mode">浅色模式</strong>
                                            <span>预览主题</span>
                                        </div>
                                        <div class="bn-preview-stat">
                                            <strong id="bn-preview-count">0 项</strong>
                                            <span>当前导航数量</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="bn-preview-bottom-nav" id="bn-live-preview-nav"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                </div>
                <div class="bn-settings-column">
            <div class="bn-panel" id="bn-panel-layout">
                <div class="bn-panel-title">显示与布局</div>
                <div class="bn-panel-desc">控制显示方式、位置尺寸与交互动效。</div>
                <div class="bn-section-stack">
                    <div class="bn-section-card">
                        <div class="bn-section-title">基础开关</div>
                        <div class="bn-switch-row">
                            <div class="bn-switch-item">
                                <span class="txt">桌面端显示</span>
                                <input type="checkbox" name="show_on_desktop" lay-skin="switch" lay-text="开启|关闭" <?= $data['show_on_desktop'] === 'y' ? 'checked' : '' ?>>
                            </div>
                            <div class="bn-switch-item">
                                <span class="txt">显示文字标签</span>
                                <input type="checkbox" name="show_label" lay-skin="switch" lay-text="开启|关闭" <?= $data['show_label'] === 'y' ? 'checked' : '' ?>>
                            </div>
                            <div class="bn-switch-item">
                                <span class="txt">适配安全区</span>
                                <input type="checkbox" name="enable_safe_area" lay-skin="switch" lay-text="开启|关闭" <?= $data['enable_safe_area'] === 'y' ? 'checked' : '' ?>>
                            </div>
                            <div class="bn-switch-item">
                                <span class="txt">自动隐藏个人中心类项</span>
                                <input type="checkbox" name="show_user_center_auto" lay-skin="switch" lay-text="开启|关闭" <?= $data['show_user_center_auto'] === 'y' ? 'checked' : '' ?>>
                            </div>
                            <div class="bn-switch-item">
                                <span class="txt">商品详情页显示</span>
                                <input type="checkbox" name="show_on_goods_detail" lay-skin="switch" lay-text="开启|关闭" <?= $data['show_on_goods_detail'] === 'y' ? 'checked' : '' ?>>
                            </div>
                        </div>
                    </div>
                    <div class="bn-section-card">
                        <div class="bn-section-title">位置与尺寸</div>
                        <div class="bn-settings-grid">
                            <div class="bn-field span-3">
                                <label class="bn-grid-label">移动断点</label>
                                <input type="number" name="desktop_breakpoint" value="<?= htmlspecialchars($data['desktop_breakpoint']) ?>" class="layui-input" min="480" max="2200">
                            </div>
                            <div class="bn-field span-3">
                                <label class="bn-grid-label">最大宽度</label>
                                <input type="number" name="max_width" value="<?= htmlspecialchars($data['max_width']) ?>" class="layui-input" min="0" max="1600" placeholder="0 表示自适应全宽">
                            </div>
                            <div class="bn-field span-3">
                                <label class="bn-grid-label">左右边距</label>
                                <input type="number" name="side_gap" value="<?= htmlspecialchars($data['side_gap']) ?>" class="layui-input" min="0" max="120">
                            </div>
                            <div class="bn-field span-3">
                                <label class="bn-grid-label">底部偏移</label>
                                <input type="number" name="bottom_offset" value="<?= htmlspecialchars($data['bottom_offset']) ?>" class="layui-input" min="0" max="80">
                            </div>
                            <div class="bn-field span-3">
                                <label class="bn-grid-label">容器内边距</label>
                                <input type="number" name="container_padding" value="<?= htmlspecialchars($data['container_padding']) ?>" class="layui-input" min="0" max="30">
                            </div>
                            <div class="bn-field span-3">
                                <label class="bn-grid-label">项目间距</label>
                                <input type="number" name="item_gap" value="<?= htmlspecialchars($data['item_gap']) ?>" class="layui-input" min="0" max="30">
                            </div>
                            <div class="bn-field span-3">
                                <label class="bn-grid-label">容器圆角</label>
                                <input type="number" name="container_radius" value="<?= htmlspecialchars($data['container_radius']) ?>" class="layui-input" min="0" max="60">
                            </div>
                            <div class="bn-field span-3">
                                <label class="bn-grid-label">项目圆角</label>
                                <input type="number" name="item_radius" value="<?= htmlspecialchars($data['item_radius']) ?>" class="layui-input" min="0" max="40">
                            </div>
                            <div class="bn-field span-3">
                                <label class="bn-grid-label">项目高度</label>
                                <input type="number" name="item_height" value="<?= htmlspecialchars($data['item_height']) ?>" class="layui-input" min="32" max="120">
                            </div>
                            <div class="bn-field span-3">
                                <label class="bn-grid-label">图标尺寸</label>
                                <input type="number" name="icon_size" value="<?= htmlspecialchars($data['icon_size']) ?>" class="layui-input" min="12" max="48">
                            </div>
                            <div class="bn-field span-3">
                                <label class="bn-grid-label">图标上下位移</label>
                                <input type="number" name="icon_translate_y" value="<?= htmlspecialchars($data['icon_translate_y']) ?>" class="layui-input" min="-20" max="20">
                            </div>
                            <div class="bn-field span-3">
                                <label class="bn-grid-label">文字尺寸</label>
                                <input type="number" name="text_size" value="<?= htmlspecialchars($data['text_size']) ?>" class="layui-input" min="10" max="24">
                            </div>
                            <div class="bn-field span-3">
                                <label class="bn-grid-label">文字上下位移</label>
                                <input type="number" name="text_translate_y" value="<?= htmlspecialchars($data['text_translate_y']) ?>" class="layui-input" min="-20" max="20">
                            </div>
                            <div class="bn-field span-3">
                                <label class="bn-grid-label">激活字重</label>
                                <input type="number" name="font_weight" value="<?= htmlspecialchars($data['font_weight']) ?>" class="layui-input" min="400" max="800" step="100">
                            </div>
                        </div>
                    </div>
                    <div class="bn-section-card">
                        <div class="bn-section-title">效果与层级</div>
                        <div class="bn-settings-grid">
                            <div class="bn-field span-3">
                                <label class="bn-grid-label">毛玻璃强度</label>
                                <input type="number" name="blur_strength" value="<?= htmlspecialchars($data['blur_strength']) ?>" class="layui-input" min="0" max="60">
                            </div>
                            <div class="bn-field span-3">
                                <label class="bn-grid-label">层级 z-index</label>
                                <input type="number" name="z_index" value="<?= htmlspecialchars($data['z_index']) ?>" class="layui-input" min="10" max="99999">
                            </div>
                            <div class="bn-field span-3">
                                <label class="bn-grid-label">阴影纵向位移</label>
                                <input type="number" name="shadow_y" value="<?= htmlspecialchars($data['shadow_y']) ?>" class="layui-input" min="0" max="60">
                            </div>
                            <div class="bn-field span-3">
                                <label class="bn-grid-label">阴影模糊</label>
                                <input type="number" name="shadow_blur" value="<?= htmlspecialchars($data['shadow_blur']) ?>" class="layui-input" min="0" max="80">
                            </div>
                            <div class="bn-field span-3">
                                <label class="bn-grid-label">激活缩放</label>
                                <input type="number" name="active_scale" value="<?= htmlspecialchars($data['active_scale']) ?>" class="layui-input" min="1" max="1.3" step="0.01">
                            </div>
                            <div class="bn-field span-3">
                                <label class="bn-grid-label">激活位移</label>
                                <input type="number" name="active_translate_y" value="<?= htmlspecialchars($data['active_translate_y']) ?>" class="layui-input" min="-10" max="10" step="1">
                            </div>
                            <div class="bn-field span-12">
                                <label class="bn-grid-label">指定链接不显示</label>
                                <textarea name="hide_on_links" class="layui-textarea" placeholder="一行一个关键词，命中当前链接或 action 时隐藏整个底部导航。比如：help、?action=order_query&contact=、user/、post="><?= htmlspecialchars($data['hide_on_links']) ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bn-panel" id="bn-panel-colors">
                <div class="bn-panel-title">颜色与透明度</div>
                <div class="bn-panel-desc">浅色和深色模式支持分别设置。</div>
                <div class="bn-section-stack">
                    <div class="bn-section-card">
                        <div class="bn-section-title">背景与边框</div>
                        <div class="bn-settings-grid">
                            <div class="bn-field span-4">
                                <label class="bn-grid-label">浅色背景色</label>
                                <div class="bn-color-group">
                                    <input type="text" name="background_color" value="<?= htmlspecialchars($data['background_color']) ?>" class="layui-input" placeholder="#ffffff">
                                    <input type="color" data-sync="background_color" value="<?= htmlspecialchars($data['background_color']) ?>">
                                </div>
                            </div>
                            <div class="bn-field span-2">
                                <label class="bn-grid-label">浅色背景透明度</label>
                                <input type="number" name="background_alpha" value="<?= htmlspecialchars($data['background_alpha']) ?>" class="layui-input" min="0" max="100">
                            </div>
                            <div class="bn-field span-4">
                                <label class="bn-grid-label">深色背景色</label>
                                <div class="bn-color-group">
                                    <input type="text" name="background_color_dark" value="<?= htmlspecialchars($data['background_color_dark']) ?>" class="layui-input" placeholder="#111827">
                                    <input type="color" data-sync="background_color_dark" value="<?= htmlspecialchars($data['background_color_dark']) ?>">
                                </div>
                            </div>
                            <div class="bn-field span-2">
                                <label class="bn-grid-label">深色背景透明度</label>
                                <input type="number" name="background_alpha_dark" value="<?= htmlspecialchars($data['background_alpha_dark']) ?>" class="layui-input" min="0" max="100">
                            </div>
                            <div class="bn-field span-4">
                                <label class="bn-grid-label">浅色边框色</label>
                                <div class="bn-color-group">
                                    <input type="text" name="border_color" value="<?= htmlspecialchars($data['border_color']) ?>" class="layui-input" placeholder="#ffffff">
                                    <input type="color" data-sync="border_color" value="<?= htmlspecialchars($data['border_color']) ?>">
                                </div>
                            </div>
                            <div class="bn-field span-2">
                                <label class="bn-grid-label">浅色边框透明度</label>
                                <input type="number" name="border_alpha" value="<?= htmlspecialchars($data['border_alpha']) ?>" class="layui-input" min="0" max="100">
                            </div>
                            <div class="bn-field span-4">
                                <label class="bn-grid-label">深色边框色</label>
                                <div class="bn-color-group">
                                    <input type="text" name="border_color_dark" value="<?= htmlspecialchars($data['border_color_dark']) ?>" class="layui-input" placeholder="#ffffff">
                                    <input type="color" data-sync="border_color_dark" value="<?= htmlspecialchars($data['border_color_dark']) ?>">
                                </div>
                            </div>
                            <div class="bn-field span-2">
                                <label class="bn-grid-label">深色边框透明度</label>
                                <input type="number" name="border_alpha_dark" value="<?= htmlspecialchars($data['border_alpha_dark']) ?>" class="layui-input" min="0" max="100">
                            </div>
                        </div>
                    </div>
                    <div class="bn-section-card">
                        <div class="bn-section-title">文字与激活态</div>
                        <div class="bn-settings-grid">
                            <div class="bn-field span-4">
                                <label class="bn-grid-label">浅色文字色</label>
                                <div class="bn-color-group">
                                    <input type="text" name="text_color" value="<?= htmlspecialchars($data['text_color']) ?>" class="layui-input" placeholder="#718096">
                                    <input type="color" data-sync="text_color" value="<?= htmlspecialchars($data['text_color']) ?>">
                                </div>
                            </div>
                            <div class="bn-field span-4">
                                <label class="bn-grid-label">深色文字色</label>
                                <div class="bn-color-group">
                                    <input type="text" name="text_color_dark" value="<?= htmlspecialchars($data['text_color_dark']) ?>" class="layui-input" placeholder="#9ca3af">
                                    <input type="color" data-sync="text_color_dark" value="<?= htmlspecialchars($data['text_color_dark']) ?>">
                                </div>
                            </div>
                            <div class="bn-field span-4">
                                <label class="bn-grid-label">浅色激活文字色</label>
                                <div class="bn-color-group">
                                    <input type="text" name="active_text_color" value="<?= htmlspecialchars($data['active_text_color']) ?>" class="layui-input" placeholder="#2196F3">
                                    <input type="color" data-sync="active_text_color" value="<?= htmlspecialchars($data['active_text_color']) ?>">
                                </div>
                            </div>
                            <div class="bn-field span-4">
                                <label class="bn-grid-label">深色激活文字色</label>
                                <div class="bn-color-group">
                                    <input type="text" name="active_text_color_dark" value="<?= htmlspecialchars($data['active_text_color_dark']) ?>" class="layui-input" placeholder="#2196F3">
                                    <input type="color" data-sync="active_text_color_dark" value="<?= htmlspecialchars($data['active_text_color_dark']) ?>">
                                </div>
                            </div>
                            <div class="bn-field span-4">
                                <label class="bn-grid-label">浅色激活底色</label>
                                <div class="bn-color-group">
                                    <input type="text" name="active_bg_color" value="<?= htmlspecialchars($data['active_bg_color']) ?>" class="layui-input" placeholder="#2196F3">
                                    <input type="color" data-sync="active_bg_color" value="<?= htmlspecialchars($data['active_bg_color']) ?>">
                                </div>
                            </div>
                            <div class="bn-field span-4">
                                <label class="bn-grid-label">深色激活底色</label>
                                <div class="bn-color-group">
                                    <input type="text" name="active_bg_color_dark" value="<?= htmlspecialchars($data['active_bg_color_dark']) ?>" class="layui-input" placeholder="#2196F3">
                                    <input type="color" data-sync="active_bg_color_dark" value="<?= htmlspecialchars($data['active_bg_color_dark']) ?>">
                                </div>
                            </div>
                            <div class="bn-field span-2">
                                <label class="bn-grid-label">浅色激活底色透明度</label>
                                <input type="number" name="active_bg_alpha" value="<?= htmlspecialchars($data['active_bg_alpha']) ?>" class="layui-input" min="0" max="100">
                            </div>
                            <div class="bn-field span-2">
                                <label class="bn-grid-label">深色激活底色透明度</label>
                                <input type="number" name="active_bg_alpha_dark" value="<?= htmlspecialchars($data['active_bg_alpha_dark']) ?>" class="layui-input" min="0" max="100">
                            </div>
                        </div>
                    </div>
                    <div class="bn-section-card">
                        <div class="bn-section-title">阴影</div>
                        <div class="bn-settings-grid">
                            <div class="bn-field span-2">
                                <label class="bn-grid-label">浅色阴影透明度</label>
                                <input type="number" name="shadow_alpha" value="<?= htmlspecialchars($data['shadow_alpha']) ?>" class="layui-input" min="0" max="100">
                            </div>
                            <div class="bn-field span-2">
                                <label class="bn-grid-label">深色阴影透明度</label>
                                <input type="number" name="shadow_alpha_dark" value="<?= htmlspecialchars($data['shadow_alpha_dark']) ?>" class="layui-input" min="0" max="100">
                            </div>
                            <div class="bn-field span-4">
                                <label class="bn-grid-label">浅色阴影色</label>
                                <div class="bn-color-group">
                                    <input type="text" name="shadow_color" value="<?= htmlspecialchars($data['shadow_color']) ?>" class="layui-input" placeholder="#0f172a">
                                    <input type="color" data-sync="shadow_color" value="<?= htmlspecialchars($data['shadow_color']) ?>">
                                </div>
                            </div>
                            <div class="bn-field span-4">
                                <label class="bn-grid-label">深色阴影色</label>
                                <div class="bn-color-group">
                                    <input type="text" name="shadow_color_dark" value="<?= htmlspecialchars($data['shadow_color_dark']) ?>" class="layui-input" placeholder="#000000">
                                    <input type="color" data-sync="shadow_color_dark" value="<?= htmlspecialchars($data['shadow_color_dark']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bn-panel" id="bn-panel-items">
                <div class="bn-panel-title">导航项目</div>
                <div class="bn-panel-desc">增删导航项，并设置图标、链接、打开方式与激活匹配规则。</div>
                <div class="bn-help">内置类型会自动识别当前页面高亮，<b>同时也支持叠加下方的激活匹配规则</b>；自定义类型则完全依赖激活匹配规则。<b>首页类型默认已包含分类页高亮</b>，一般不需要再填写 <code>sort_id</code>；如需额外扩展匹配范围，再填写规则。查单页可填 <code>action=order_query</code> 或 <code>order_query</code>，用户中心可填 <code>user/</code>。<b>博客类型</b>会自动在博客列表、文章详情、博客分类等所有博客页面高亮。</div>
                <div class="bn-item-list" id="bn-item-list">
                    <?php foreach ($data['nav_items'] as $index => $item): ?>
                    <div class="bn-item-card">
                        <div class="bn-item-head">
                            <div class="bn-item-head-left">
                                <span class="bn-item-index"><?= $index + 1 ?></span>
                                <div class="bn-item-title">导航项 <span class="item-name-preview"><?= htmlspecialchars($item['title']) ?></span></div>
                            </div>
                            <div class="bn-item-actions">
                                <input type="hidden" name="nav_enabled_state[]" value="<?= $item['enabled'] ?>" class="nav-enabled-state">
                                <div class="bn-switch-item" style="margin-bottom:0;">
                                    <span class="txt">启用</span>
                                    <input type="checkbox" class="nav-enabled-switch" lay-skin="switch" lay-text="显示|隐藏" <?= $item['enabled'] === 'y' ? 'checked' : '' ?>>
                                </div>
                                <i class="ri-delete-bin-6-line bn-item-remove" title="删除"></i>
                            </div>
                        </div>
                        <div class="bn-item-body">
                            <div class="layui-row layui-col-space10">
                                <div class="layui-col-xs12 layui-col-sm6 layui-col-md3">
                                    <label class="bn-grid-label">类型</label>
                                    <select name="nav_type[]">
                                        <option value="home" <?= $item['type'] === 'home' ? 'selected' : '' ?>>首页</option>
                                        <option value="order_query" <?= $item['type'] === 'order_query' ? 'selected' : '' ?>>查单</option>
                                        <option value="user" <?= $item['type'] === 'user' ? 'selected' : '' ?>>个人中心</option>
                                        <option value="help" <?= $item['type'] === 'help' ? 'selected' : '' ?>>帮助页</option>
                                        <option value="blog" <?= $item['type'] === 'blog' ? 'selected' : '' ?>>博客</option>
                                        <option value="custom" <?= $item['type'] === 'custom' ? 'selected' : '' ?>>自定义</option>
                                    </select>
                                </div>
                                <div class="layui-col-xs12 layui-col-sm6 layui-col-md3">
                                    <label class="bn-grid-label">显示条件</label>
                                    <select name="nav_visible[]">
                                        <option value="all" <?= $item['visible'] === 'all' ? 'selected' : '' ?>>始终显示</option>
                                        <option value="user_center_enabled" <?= $item['visible'] === 'user_center_enabled' ? 'selected' : '' ?>>用户中心开启时显示</option>
                                    </select>
                                </div>
                                <div class="layui-col-xs12 layui-col-sm6 layui-col-md3">
                                    <label class="bn-grid-label">标题</label>
                                    <input type="text" name="nav_title[]" value="<?= htmlspecialchars($item['title']) ?>" class="layui-input nav-title-input" placeholder="例如：首页">
                                </div>
                                <div class="layui-col-xs12 layui-col-sm6 layui-col-md3">
                                    <label class="bn-grid-label">打开方式</label>
                                    <select name="nav_target[]">
                                        <option value="_self" <?= $item['target'] === '_self' ? 'selected' : '' ?>>当前窗口</option>
                                        <option value="_blank" <?= $item['target'] === '_blank' ? 'selected' : '' ?>>新窗口</option>
                                    </select>
                                </div>
                                <div class="layui-col-xs12 layui-col-sm6 layui-col-md4">
                                    <label class="bn-grid-label">链接地址</label>
                                    <input type="text" name="nav_url[]" value="<?= htmlspecialchars($item['url']) ?>" class="layui-input" placeholder="留空时使用内置类型默认链接">
                                </div>
                                <div class="layui-col-xs12 layui-col-sm6 layui-col-md6">
                                    <label class="bn-grid-label">默认图标 class</label>
                                    <div class="bn-icon-input-wrap">
                                        <input type="text" name="nav_icon[]" value="<?= htmlspecialchars($item['icon']) ?>" class="layui-input bn-icon-input" data-role="default-icon" placeholder="例如：ri-home-5-line">
                                        <span class="bn-icon-preview"><i class="<?= htmlspecialchars($item['icon']) ?>"></i></span>
                                        <div class="bn-icon-upload-box">
                                            <input type="hidden" name="nav_icon_image[]" value="<?= htmlspecialchars($item['icon_image'] ?? '') ?>" class="bn-icon-image-input" data-role="default-icon-image">
                                            <div class="bn-icon-upload-main">
                                                <div class="bn-icon-upload-actions">
                                                    <button type="button" class="layui-btn layui-btn-primary layui-btn-sm bn-icon-upload-trigger" data-role="default-icon-image">上传图片</button>
                                                    <button type="button" class="layui-btn layui-btn-primary layui-btn-sm bn-icon-image-clear" data-role="default-icon-image">清空</button>
                                                </div>
                                                <div class="bn-icon-upload-path" data-path-role="default-icon-image" title="<?= !empty($item['icon_image']) ? htmlspecialchars($item['icon_image']) : '未上传图片' ?>"><?= !empty($item['icon_image']) ? htmlspecialchars($item['icon_image']) : '未上传图片' ?></div>
                                            </div>
                                        </div>
                                        <button type="button" class="layui-btn layui-btn-primary layui-btn-sm bn-icon-trigger">设置RemixIcon图标</button>
                                    </div>
                                </div>
                                <div class="layui-col-xs12 layui-col-sm6 layui-col-md6">
                                    <label class="bn-grid-label">激活图标 class</label>
                                    <div class="bn-icon-input-wrap">
                                        <input type="text" name="nav_active_icon[]" value="<?= htmlspecialchars($item['active_icon']) ?>" class="layui-input bn-icon-input" data-role="active-icon" placeholder="例如：ri-home-5-fill，不填则沿用默认图标">
                                        <span class="bn-icon-preview"><i class="<?= htmlspecialchars($item['active_icon'] ?: $item['icon']) ?>"></i></span>
                                        <div class="bn-icon-upload-box">
                                            <input type="hidden" name="nav_active_icon_image[]" value="<?= htmlspecialchars($item['active_icon_image'] ?? '') ?>" class="bn-icon-image-input" data-role="active-icon-image">
                                            <div class="bn-icon-upload-main">
                                                <div class="bn-icon-upload-actions">
                                                    <button type="button" class="layui-btn layui-btn-primary layui-btn-sm bn-icon-upload-trigger" data-role="active-icon-image">上传图片</button>
                                                    <button type="button" class="layui-btn layui-btn-primary layui-btn-sm bn-icon-image-clear" data-role="active-icon-image">清空</button>
                                                </div>
                                                <div class="bn-icon-upload-path" data-path-role="active-icon-image" title="<?= !empty($item['active_icon_image']) ? htmlspecialchars($item['active_icon_image']) : '未上传图片' ?>"><?= !empty($item['active_icon_image']) ? htmlspecialchars($item['active_icon_image']) : '未上传图片' ?></div>
                                            </div>
                                        </div>
                                        <button type="button" class="layui-btn layui-btn-primary layui-btn-sm bn-icon-trigger">设置RemixIcon图标</button>
                                    </div>
                                </div>
                                <div class="layui-col-xs12">
                                    <label class="bn-grid-label">激活匹配规则</label>
                                    <textarea name="nav_match_rule[]" class="layui-textarea" style="min-height:76px;" placeholder="支持所有类型；一行一个规则。首页类型默认已包含分类页；如需扩展匹配可填写 action=order_query、user/、sort_id=1 等"><?= htmlspecialchars($item['match_rule']) ?></textarea>
                                    <div class="bn-help" style="margin-top:6px;">匹配的是当前完整链接和 <code>action</code> 参数，支持一行写一个规则。<b>首页类型默认会在首页和分类页高亮</b>；这里只在你需要补充额外页面匹配时再填写，例如 <code>sort_id=1</code>、<code>action=order_query</code>、<code>user/</code>。</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="bn-toolbar">
                    <button type="button" class="layui-btn layui-btn-primary" id="bn-add-item"><i class="ri-add-line"></i>新增导航项</button>
                    <button type="button" class="layui-btn layui-btn-primary" id="bn-reset-default"><i class="ri-refresh-line"></i>恢复默认三项</button>
                </div>
            </div>
                    <div class="bn-settings-bottom-spacer" aria-hidden="true"></div>
                </div>
            </div>
        </div>
        <div id="form-btn">
            <button type="button" class="layui-btn layui-btn-primary" id="bn-reset-config">重置默认配置</button>
            <button class="layui-btn" lay-submit lay-filter="save">保存设置</button>
        </div>
    </form>

    <script type="text/template" id="bn-item-template">
        <div class="bn-item-card">
            <div class="bn-item-head">
                <div class="bn-item-head-left">
                    <span class="bn-item-index">__INDEX__</span>
                    <div class="bn-item-title">导航项 <span class="item-name-preview">自定义</span></div>
                </div>
                <div class="bn-item-actions">
                    <input type="hidden" name="nav_enabled_state[]" value="y" class="nav-enabled-state">
                    <div class="bn-switch-item" style="margin-bottom:0;">
                        <span class="txt">启用</span>
                        <input type="checkbox" class="nav-enabled-switch" lay-skin="switch" lay-text="显示|隐藏" checked>
                    </div>
                    <i class="ri-delete-bin-6-line bn-item-remove" title="删除"></i>
                </div>
            </div>
            <div class="bn-item-body">
                <div class="layui-row layui-col-space10">
                    <div class="layui-col-xs12 layui-col-sm6 layui-col-md3">
                        <label class="bn-grid-label">类型</label>
                        <select name="nav_type[]">
                            <option value="home">首页</option>
                            <option value="order_query">查单</option>
                            <option value="user">个人中心</option>
                            <option value="help">帮助页</option>
                            <option value="blog">博客</option>
                            <option value="custom" selected>自定义</option>
                        </select>
                    </div>
                    <div class="layui-col-xs12 layui-col-sm6 layui-col-md3">
                        <label class="bn-grid-label">显示条件</label>
                        <select name="nav_visible[]">
                            <option value="all" selected>始终显示</option>
                            <option value="user_center_enabled">用户中心开启时显示</option>
                        </select>
                    </div>
                    <div class="layui-col-xs12 layui-col-sm6 layui-col-md3">
                        <label class="bn-grid-label">标题</label>
                        <input type="text" name="nav_title[]" value="自定义" class="layui-input nav-title-input" placeholder="例如：首页">
                    </div>
                    <div class="layui-col-xs12 layui-col-sm6 layui-col-md3">
                        <label class="bn-grid-label">打开方式</label>
                        <select name="nav_target[]">
                            <option value="_self" selected>当前窗口</option>
                            <option value="_blank">新窗口</option>
                        </select>
                    </div>
                    <div class="layui-col-xs12 layui-col-sm6 layui-col-md4">
                        <label class="bn-grid-label">链接地址</label>
                        <input type="text" name="nav_url[]" value="" class="layui-input" placeholder="留空时使用内置类型默认链接">
                    </div>
                    <div class="layui-col-xs12 layui-col-sm6 layui-col-md6">
                        <label class="bn-grid-label">默认图标 class</label>
                        <div class="bn-icon-input-wrap">
                            <input type="text" name="nav_icon[]" value="ri-apps-2-line" class="layui-input bn-icon-input" data-role="default-icon" placeholder="例如：ri-home-5-line">
                            <span class="bn-icon-preview"><i class="ri-apps-2-line"></i></span>
                            <div class="bn-icon-upload-box">
                                <input type="hidden" name="nav_icon_image[]" value="" class="bn-icon-image-input" data-role="default-icon-image">
                                <div class="bn-icon-upload-main">
                                    <div class="bn-icon-upload-actions">
                                        <button type="button" class="layui-btn layui-btn-primary layui-btn-sm bn-icon-upload-trigger" data-role="default-icon-image">上传图片</button>
                                        <button type="button" class="layui-btn layui-btn-primary layui-btn-sm bn-icon-image-clear" data-role="default-icon-image">清空</button>
                                    </div>
                                    <div class="bn-icon-upload-path" data-path-role="default-icon-image" title="未上传图片">未上传图片</div>
                                </div>
                            </div>
                            <button type="button" class="layui-btn layui-btn-primary layui-btn-sm bn-icon-trigger">设置RemixIcon图标</button>
                        </div>
                    </div>
                    <div class="layui-col-xs12 layui-col-sm6 layui-col-md6">
                        <label class="bn-grid-label">激活图标 class</label>
                        <div class="bn-icon-input-wrap">
                            <input type="text" name="nav_active_icon[]" value="" class="layui-input bn-icon-input" data-role="active-icon" placeholder="例如：ri-home-5-fill，不填则沿用默认图标">
                            <span class="bn-icon-preview"><i class="ri-apps-2-line"></i></span>
                            <div class="bn-icon-upload-box">
                                <input type="hidden" name="nav_active_icon_image[]" value="" class="bn-icon-image-input" data-role="active-icon-image">
                                <div class="bn-icon-upload-main">
                                    <div class="bn-icon-upload-actions">
                                        <button type="button" class="layui-btn layui-btn-primary layui-btn-sm bn-icon-upload-trigger" data-role="active-icon-image">上传图片</button>
                                        <button type="button" class="layui-btn layui-btn-primary layui-btn-sm bn-icon-image-clear" data-role="active-icon-image">清空</button>
                                    </div>
                                    <div class="bn-icon-upload-path" data-path-role="active-icon-image" title="未上传图片">未上传图片</div>
                                </div>
                            </div>
                            <button type="button" class="layui-btn layui-btn-primary layui-btn-sm bn-icon-trigger">设置RemixIcon图标</button>
                        </div>
                    </div>
                    <div class="layui-col-xs12">
                        <label class="bn-grid-label">激活匹配规则</label>
                        <textarea name="nav_match_rule[]" class="layui-textarea" style="min-height:76px;" placeholder="支持所有类型；一行一个规则。首页类型默认已包含分类页；如需扩展匹配可填写 action=order_query、user/、sort_id=1 等"></textarea>
                        <div class="bn-help" style="margin-top:6px;">匹配的是当前完整链接和 <code>action</code> 参数，支持一行写一个规则。<b>首页类型默认会在首页和分类页高亮</b>；这里只在你需要补充额外页面匹配时再填写，例如 <code>sort_id=1</code>、<code>action=order_query</code>、<code>user/</code>。</div>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <script>
    layui.use(['form', 'layer', 'upload'], function() {
        var form = layui.form;
        var layer = layui.layer;
        var upload = layui.upload;
        var defaultConfig = <?= $defaultConfigJson ?>;
        var defaultItems = <?= $defaultItemsJson ?>;
        var iconGroups = <?= $iconGroupsJson ?>;
        var remixIconSheetLoaded = false;
        var previewState = { theme: 'light', activeIndex: null };
        var rangeConfigs = {
            side_gap: { min: 0, max: 120, step: 1, unit: 'px' },
            bottom_offset: { min: 0, max: 80, step: 1, unit: 'px' },
            container_padding: { min: 0, max: 30, step: 1, unit: 'px' },
            item_gap: { min: 0, max: 30, step: 1, unit: 'px' },
            container_radius: { min: 0, max: 60, step: 1, unit: 'px' },
            item_radius: { min: 0, max: 40, step: 1, unit: 'px' },
            item_height: { min: 32, max: 120, step: 1, unit: 'px' },
            icon_size: { min: 12, max: 48, step: 1, unit: 'px' },
            icon_translate_y: { min: -20, max: 20, step: 1, unit: 'px' },
            text_size: { min: 10, max: 24, step: 1, unit: 'px' },
            text_translate_y: { min: -20, max: 20, step: 1, unit: 'px' },
            font_weight: { min: 400, max: 800, step: 100, unit: '' },
            blur_strength: { min: 0, max: 60, step: 1, unit: 'px' },
            shadow_y: { min: 0, max: 60, step: 1, unit: 'px' },
            shadow_blur: { min: 0, max: 80, step: 1, unit: 'px' },
            background_alpha: { min: 0, max: 100, step: 1, unit: '%' },
            background_alpha_dark: { min: 0, max: 100, step: 1, unit: '%' },
            border_alpha: { min: 0, max: 100, step: 1, unit: '%' },
            border_alpha_dark: { min: 0, max: 100, step: 1, unit: '%' },
            active_bg_alpha: { min: 0, max: 100, step: 1, unit: '%' },
            active_bg_alpha_dark: { min: 0, max: 100, step: 1, unit: '%' },
            shadow_alpha: { min: 0, max: 100, step: 1, unit: '%' },
            shadow_alpha_dark: { min: 0, max: 100, step: 1, unit: '%' },
            active_scale: { min: 1, max: 1.3, step: 0.01, unit: 'x' },
            active_translate_y: { min: -10, max: 10, step: 1, unit: 'px' }
        };

        function escapeHtml(str) {
            return String(str || '').replace(/[&<>"']/g, function(s) {
                return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[s];
            });
        }

        function safeIconClass(icon) {
            icon = String(icon || '').trim().replace(/[^a-zA-Z0-9\s_-]/g, '');
            return icon || 'ri-apps-2-line';
        }

        function normalizeIconImageUrl(url) {
            url = $.trim(url || '');
            if (!url) return '';
            if (/^(https?:)?\/\//i.test(url) || url.indexOf('/') === 0) {
                return url;
            }
            return '/' + url.replace(/^\/+/, '');
        }

        function renderSmallIconPreview(state) {
            if (state.type === 'image' && state.value) {
                return '<img src="' + escapeHtml(state.value) + '" alt="图标">';
            }
            return '<i class="' + escapeHtml(safeIconClass(state.value)) + '"></i>';
        }

        function getCardIconState($card, role) {
            var defaultIcon = $.trim($card.find('input[name="nav_icon[]"]').val()) || 'ri-apps-2-line';
            var defaultImage = normalizeIconImageUrl($card.find('input[name="nav_icon_image[]"]').val());
            var activeIcon = $.trim($card.find('input[name="nav_active_icon[]"]').val());
            var activeImage = normalizeIconImageUrl($card.find('input[name="nav_active_icon_image[]"]').val());
            if (role === 'active') {
                if (activeImage) return { type: 'image', value: activeImage };
                if (activeIcon) return { type: 'class', value: activeIcon };
                if (defaultImage) return { type: 'image', value: defaultImage };
                return { type: 'class', value: defaultIcon };
            }
            if (defaultImage) return { type: 'image', value: defaultImage };
            return { type: 'class', value: defaultIcon };
        }

        function getPairInput($input) {
            var role = $input.data('role');
            if (role === 'active-icon') {
                return $input.closest('.layui-row').find('input[name="nav_icon[]"]');
            }
            return $input;
        }

        function pushUniqueIcon(list, icon) {
            icon = $.trim(String(icon || ''));
            if (!icon || list.indexOf(icon) !== -1) {
                return;
            }
            list.push(icon);
        }

        function mergeIconGroup(target, groupName, icons) {
            if (!target[groupName]) {
                target[groupName] = [];
            }
            (icons || []).forEach(function(icon) {
                pushUniqueIcon(target[groupName], icon);
            });
        }

        function detectIconGroupName(icon) {
            icon = String(icon || '').toLowerCase();
            if (/(search|shopping|cart|bag|bank-card|coupon|exchange|refund|wallet|secure-payment|price-tag|money|history|file-list|bill|coin|vip-crown|store)/.test(icon)) {
                return '订单交易';
            }
            if (/(user|account|login|logout|team|id-card|contacts|profile|admin)/.test(icon)) {
                return '用户账号';
            }
            if (/(customer-service|question|information|message|chat|phone|mail|service|notification|shield|feedback|support)/.test(icon)) {
                return '客服帮助';
            }
            if (/(gift|medal|rocket|flashlight|book|bookmark|image|play|settings)/.test(icon)) {
                return '内容功能';
            }
            if (/(home|apps|grid|dashboard|menu|links|compass|star|heart|layout|navigation|function)/.test(icon)) {
                return '基础';
            }
            return '更多图标';
        }

        function ensureRemixIconGroupsLoaded() {
            if (remixIconSheetLoaded) {
                return;
            }
            remixIconSheetLoaded = true;
            var mergedGroups = {};
            $.each(iconGroups, function(groupName, icons) {
                mergeIconGroup(mergedGroups, groupName, icons || []);
            });
            Array.prototype.forEach.call(document.styleSheets || [], function(sheet) {
                var href = sheet && sheet.href ? String(sheet.href).toLowerCase() : '';
                if (href && href.indexOf('remixicon') === -1) {
                    return;
                }
                var rules;
                try {
                    rules = sheet.cssRules || sheet.rules;
                } catch (e) {
                    rules = null;
                }
                if (!rules) {
                    return;
                }
                Array.prototype.forEach.call(rules, function(rule) {
                    var selectorText = rule && rule.selectorText ? String(rule.selectorText) : '';
                    if (!selectorText || selectorText.indexOf('.ri-') === -1) {
                        return;
                    }
                    var matches = selectorText.match(/\.ri-[a-z0-9-]+(?=\:before|\:\:before)/ig);
                    if (!matches || !matches.length) {
                        return;
                    }
                    matches.forEach(function(selector) {
                        var icon = selector.replace(/^\./, '').toLowerCase();
                        if (!/(?:-line|-fill)$/.test(icon)) {
                            return;
                        }
                        mergeIconGroup(mergedGroups, detectIconGroupName(icon), [icon]);
                    });
                });
            });
            iconGroups = mergedGroups;
        }

        function getIconDialogGroups(role) {
            ensureRemixIconGroupsLoaded();
            var isActiveRole = role === 'active-icon';
            var expectedSuffix = isActiveRole ? '-fill' : '-line';
            var groups = {};
            $.each(iconGroups, function(group, icons) {
                var list = (icons || []).filter(function(icon) {
                    icon = String(icon || '').trim().toLowerCase();
                    return icon.slice(-expectedSuffix.length) === expectedSuffix;
                });
                if (list.length) {
                    groups[group] = list;
                }
            });
            return groups;
        }

        function getDialogCurrentIcon(currentIcon, role, pairIcon) {
            currentIcon = $.trim(String(currentIcon || ''));
            pairIcon = $.trim(String(pairIcon || ''));
            if (currentIcon) {
                return currentIcon;
            }
            if (role === 'active-icon' && /-line$/i.test(pairIcon)) {
                return pairIcon.replace(/-line$/i, '-fill');
            }
            if (role !== 'active-icon' && /-fill$/i.test(pairIcon)) {
                return pairIcon.replace(/-fill$/i, '-line');
            }
            return pairIcon;
        }

        function uniqueIconList(list) {
            var seen = {};
            return (list || []).filter(function(icon) {
                var normalized = $.trim(String(icon || '')).toLowerCase();
                if (!normalized || seen[normalized]) {
                    return false;
                }
                seen[normalized] = true;
                return true;
            });
        }

        function getRecommendedDialogIcons(role, currentIcon, pairIcon) {
            var dialogGroups = getIconDialogGroups(role);
            var iconLookup = {};
            var candidates = [];

            $.each(dialogGroups, function(group, icons) {
                (icons || []).forEach(function(icon) {
                    iconLookup[String(icon || '').toLowerCase()] = true;
                });
            });

            function pushCandidate(icon) {
                icon = $.trim(String(icon || ''));
                var key = icon.toLowerCase();
                if (!icon || !iconLookup[key]) {
                    return;
                }
                candidates.push(icon);
            }

            currentIcon = $.trim(String(currentIcon || ''));
            pairIcon = $.trim(String(pairIcon || ''));

            pushCandidate(getDialogCurrentIcon(currentIcon, role, pairIcon));
            pushCandidate(currentIcon);

            if (role === 'active-icon') {
                if (/-line$/i.test(pairIcon)) {
                    pushCandidate(pairIcon.replace(/-line$/i, '-fill'));
                }
                pushCandidate(pairIcon);
            } else {
                if (/-fill$/i.test(pairIcon)) {
                    pushCandidate(pairIcon.replace(/-fill$/i, '-line'));
                }
                pushCandidate(pairIcon);
            }

            return uniqueIconList(candidates);
        }

        function refreshInputPreview($input) {
            var $card = $input.closest('.bn-item-card');
            var role = $input.data('role') === 'active-icon' ? 'active' : 'default';
            var state = getCardIconState($card, role);
            $input.siblings('.bn-icon-preview').html(renderSmallIconPreview(state));
        }

        function refreshImageUploadPreview($card) {
            ['default-icon-image', 'active-icon-image'].forEach(function(role) {
                var fieldName = role === 'active-icon-image' ? 'nav_active_icon_image[]' : 'nav_icon_image[]';
                var url = normalizeIconImageUrl($card.find('input[name="' + fieldName + '"]').val());
                $card.find('input[name="' + fieldName + '"]').val(url);
                $card.find('[data-path-role="' + role + '"]').text(url || '未上传图片').attr('title', url || '未上传图片');
            });
        }

        function refreshCardIconUi($scope) {
            $scope.find('.bn-item-card').addBack('.bn-item-card').each(function() {
                var $card = $(this);
                refreshImageUploadPreview($card);
                $card.find('.bn-icon-input').each(function() {
                    refreshInputPreview($(this));
                });
            });
        }

        function initIconUploads(scope) {
            $(scope).find('.bn-icon-upload-trigger').each(function() {
                var $btn = $(this);
                if ($btn.data('upload-inited')) {
                    return;
                }
                $btn.data('upload-inited', true);
                var loadingIndex = null;
                upload.render({
                    elem: this,
                    url: '?action=upload',
                    field: 'image',
                    accept: 'images',
                    acceptMime: 'image/*',
                    before: function() {
                        loadingIndex = layer.load(1, {shade: 0.08});
                    },
                    done: function(res) {
                        if (loadingIndex !== null) {
                            layer.close(loadingIndex);
                            loadingIndex = null;
                        }
                        if (!res || res.code !== 0 || !res.data) {
                            layer.msg(res && res.msg ? res.msg : '上传失败');
                            return;
                        }
                        var url = normalizeIconImageUrl(res.data.url || res.data.src || '');
                        if (!url) {
                            layer.msg('上传失败');
                            return;
                        }
                        var $card = $btn.closest('.bn-item-card');
                        if ($btn.data('role') === 'active-icon-image') {
                            $card.find('input[name="nav_active_icon_image[]"]').val(url);
                        } else {
                            $card.find('input[name="nav_icon_image[]"]').val(url);
                        }
                        refreshCardIconUi($card);
                        renderPreview();
                        layer.msg('上传成功');
                    },
                    error: function() {
                        if (loadingIndex !== null) {
                            layer.close(loadingIndex);
                            loadingIndex = null;
                        }
                        layer.msg('上传失败');
                    }
                });
            });
        }

        function buildIconDialogHtml(keyword, currentIcon, role, pairIcon) {
            keyword = String(keyword || '').toLowerCase().trim();
            currentIcon = String(currentIcon || '').trim();
            role = String(role || '').trim();
            pairIcon = String(pairIcon || '').trim();
            var dialogGroups = getIconDialogGroups(role);
            var recommendedIcons = getRecommendedDialogIcons(role, currentIcon, pairIcon).filter(function(icon) {
                return !keyword || icon.toLowerCase().indexOf(keyword) !== -1;
            });
            var recommendedLookup = {};
            recommendedIcons.forEach(function(icon) {
                recommendedLookup[String(icon || '').toLowerCase()] = true;
            });
            var html = '<div class="bn-icon-dialog">';
            html += '<div class="bn-icon-search"><input type="text" class="layui-input" id="bn-icon-search-input" placeholder="搜索图标类名，例如 home / user / shopping" value="' + escapeHtml(keyword) + '"></div>';
            if (recommendedIcons.length) {
                html += '<div class="bn-icon-group">';
                html += '<div class="bn-icon-group-title">推荐匹配</div>';
                html += '<div class="bn-icon-grid">';
                recommendedIcons.forEach(function(icon) {
                    html += '<div class="bn-icon-grid-item' + (icon === currentIcon ? ' active' : '') + '" data-icon="' + escapeHtml(icon) + '">';
                    html += '<i class="' + escapeHtml(icon) + '"></i>';
                    html += '<span>' + escapeHtml(icon) + '</span>';
                    html += '</div>';
                });
                html += '</div></div>';
            }
            $.each(dialogGroups, function(group, icons) {
                var list = icons.filter(function(icon) {
                    var normalized = String(icon || '').toLowerCase();
                    return (!keyword || normalized.indexOf(keyword) !== -1) && !recommendedLookup[normalized];
                });
                if (!list.length) return;
                html += '<div class="bn-icon-group">';
                html += '<div class="bn-icon-group-title">' + escapeHtml(group) + '</div>';
                html += '<div class="bn-icon-grid">';
                list.forEach(function(icon) {
                    html += '<div class="bn-icon-grid-item' + (icon === currentIcon ? ' active' : '') + '" data-icon="' + escapeHtml(icon) + '">';
                    html += '<i class="' + escapeHtml(icon) + '"></i>';
                    html += '<span>' + escapeHtml(icon) + '</span>';
                    html += '</div>';
                });
                html += '</div></div>';
            });
            html += '</div>';
            return html;
        }

        function openIconPicker($input) {
            var role = $input.data('role');
            var pairIcon = $.trim(getPairInput($input).val());
            var current = getDialogCurrentIcon($.trim($input.val()), role, pairIcon);
            var pickerIndex = layer.open({
                type: 1,
                title: '选择 RemixIcon 图标',
                area: [window.innerWidth < 768 ? '96%' : '860px', window.innerWidth < 768 ? '82%' : '680px'],
                skin: 'dc-layer-modern',
                content: buildIconDialogHtml('', current, role, pairIcon),
                success: function(layero) {
                    layero.on('input', '#bn-icon-search-input', function() {
                        var val = $(this).val();
                        $('.layui-layer-content', layero).html(buildIconDialogHtml(val, current, role, pairIcon));
                        $('#bn-icon-search-input', layero).focus().val(val);
                    });
                    layero.on('click', '.bn-icon-grid-item', function() {
                        var icon = $(this).data('icon') || '';
                        $input.val(icon);
                        refreshCardIconUi($input.closest('.bn-item-card'));
                        renderPreview();
                        layer.close(pickerIndex);
                    });
                }
            });
        }

        function clampValue(value, config) {
            var num = parseFloat(value);
            if (isNaN(num)) num = config.min;
            if (num < config.min) num = config.min;
            if (num > config.max) num = config.max;
            return num;
        }

        function formatRangeValue(value, config) {
            var num = clampValue(value, config);
            if (String(config.step).indexOf('.') !== -1) {
                num = num.toFixed(String(config.step).split('.')[1].length).replace(/0+$/, '').replace(/\.$/, '');
            } else {
                num = Math.round(num);
            }
            return String(num) + (config.unit || '');
        }

        function syncRangeBox($input) {
            var name = $input.attr('name');
            var config = rangeConfigs[name];
            if (!config) return;
            var $rangeBox = $input.next('.bn-range-box');
            if (!$rangeBox.length) return;
            var value = clampValue($input.val(), config);
            $rangeBox.find('.bn-range-slider').val(value);
            $rangeBox.find('.bn-range-value').text(formatRangeValue(value, config));
        }

        function getRangeLabelText($input, fallback) {
            var labelText = $.trim($input.prev('.bn-grid-label').text());
            if (!labelText) {
                labelText = $.trim($input.closest('[class*="layui-col-"]').find('> .bn-grid-label').first().text());
            }
            return labelText || fallback;
        }

        function enhanceRangeInputs() {
            $.each(rangeConfigs, function(name, config) {
                var $input = $('input[name="' + name + '"]').first();
                if (!$input.length || $input.next('.bn-range-box').length) return;
                var value = clampValue($input.val(), config);
                var labelText = getRangeLabelText($input, name);
                $input.addClass('bn-range-origin-input');
                $input.prev('.bn-grid-label').addClass('bn-range-origin-label');
                var html = '';
                html += '<div class="bn-range-box" data-range-name="' + escapeHtml(name) + '">';
                html += '<div class="bn-range-head"><span>' + escapeHtml(labelText) + '</span><span class="bn-range-value">' + escapeHtml(formatRangeValue(value, config)) + '</span></div>';
                html += '<input type="range" class="bn-range-slider" min="' + config.min + '" max="' + config.max + '" step="' + config.step + '" value="' + value + '" data-target-name="' + escapeHtml(name) + '">';
                html += '</div>';
                $input.after(html);
                syncRangeBox($input);
            });
        }

        function hexToRgbString(hex, fallback) {
            hex = String(hex || '').trim();
            var match = hex.match(/^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i);
            if (!match) return fallback || '33, 150, 243';
            return parseInt(match[1], 16) + ', ' + parseInt(match[2], 16) + ', ' + parseInt(match[3], 16);
        }

        function rgba(hex, alpha, fallback) {
            alpha = Math.max(0, Math.min(100, parseFloat(alpha) || 0));
            return 'rgba(' + hexToRgbString(hex, fallback) + ', ' + (alpha / 100) + ')';
        }

        function getValue(name, fallback) {
            var $el = $('[name="' + name + '"]').first();
            if (!$el.length) return fallback;
            if ($el.attr('type') === 'checkbox') {
                return $el.prop('checked') ? 'y' : 'n';
            }
            var value = $.trim($el.val());
            return value === '' ? fallback : value;
        }

        function getPreviewConfig() {
            return {
                show_label: getValue('show_label', 'y'),
                show_user_center_auto: getValue('show_user_center_auto', 'n'),
                side_gap: getValue('side_gap', '12'),
                bottom_offset: getValue('bottom_offset', '10'),
                container_padding: getValue('container_padding', '8'),
                item_gap: getValue('item_gap', '8'),
                container_radius: getValue('container_radius', '22'),
                item_radius: getValue('item_radius', '16'),
                item_height: getValue('item_height', '54'),
                icon_size: getValue('icon_size', '20'),
                icon_translate_y: getValue('icon_translate_y', '0'),
                text_size: getValue('text_size', '11'),
                text_translate_y: getValue('text_translate_y', '0'),
                font_weight: getValue('font_weight', '600'),
                blur_strength: getValue('blur_strength', '24'),
                shadow_y: getValue('shadow_y', '14'),
                shadow_blur: getValue('shadow_blur', '34'),
                active_scale: getValue('active_scale', '1.04'),
                active_translate_y: getValue('active_translate_y', '-1'),
                background_color: getValue('background_color', '#ffffff'),
                background_alpha: getValue('background_alpha', '78'),
                background_color_dark: getValue('background_color_dark', '#111827'),
                background_alpha_dark: getValue('background_alpha_dark', '78'),
                border_color: getValue('border_color', '#ffffff'),
                border_alpha: getValue('border_alpha', '50'),
                border_color_dark: getValue('border_color_dark', '#ffffff'),
                border_alpha_dark: getValue('border_alpha_dark', '8'),
                text_color: getValue('text_color', '#718096'),
                text_color_dark: getValue('text_color_dark', '#9ca3af'),
                active_text_color: getValue('active_text_color', '#2196F3'),
                active_text_color_dark: getValue('active_text_color_dark', '#2196F3'),
                active_bg_color: getValue('active_bg_color', '#2196F3'),
                active_bg_color_dark: getValue('active_bg_color_dark', '#2196F3'),
                active_bg_alpha: getValue('active_bg_alpha', '12'),
                active_bg_alpha_dark: getValue('active_bg_alpha_dark', '20'),
                shadow_color: getValue('shadow_color', '#0f172a'),
                shadow_alpha: getValue('shadow_alpha', '14'),
                shadow_color_dark: getValue('shadow_color_dark', '#000000'),
                shadow_alpha_dark: getValue('shadow_alpha_dark', '28')
            };
        }

        function collectPreviewItems(config) {
            var items = [];
            $('#bn-item-list .bn-item-card').each(function() {
                var $card = $(this);
                var enabled = $card.find('.nav-enabled-state').val() || 'n';
                if (enabled !== 'y') return;
                var type = $card.find('select[name="nav_type[]"]').val() || 'custom';
                var visible = $card.find('select[name="nav_visible[]"]').val() || 'all';
                var title = $.trim($card.find('input[name="nav_title[]"]').val()) || '未命名';
                var icon = $.trim($card.find('input[name="nav_icon[]"]').val()) || 'ri-apps-2-line';
                var activeIcon = $.trim($card.find('input[name="nav_active_icon[]"]').val()) || icon;
                var iconImage = normalizeIconImageUrl($card.find('input[name="nav_icon_image[]"]').val());
                var activeIconImage = normalizeIconImageUrl($card.find('input[name="nav_active_icon_image[]"]').val()) || iconImage;
                if (type === 'home' && !$.trim($card.find('input[name="nav_title[]"]').val())) title = '首页';
                if (type === 'order_query' && !$.trim($card.find('input[name="nav_title[]"]').val())) title = '查单';
                if (type === 'user' && !$.trim($card.find('input[name="nav_title[]"]').val())) title = '我的';
                if (type === 'help' && !$.trim($card.find('input[name="nav_title[]"]').val())) title = '帮助';
                items.push({ type: type, title: title, icon: safeIconClass(icon), active_icon: safeIconClass(activeIcon), icon_image: iconImage, active_icon_image: activeIconImage, active: false });
            });
            var activeIndex = typeof previewState.activeIndex === 'number' ? previewState.activeIndex : -1;
            if (activeIndex < 0 || activeIndex >= items.length) {
                activeIndex = -1;
                items.forEach(function(item, index) {
                    if (activeIndex === -1 && item.type === 'home') activeIndex = index;
                });
                if (activeIndex === -1 && items.length) activeIndex = 0;
            }
            previewState.activeIndex = activeIndex > -1 ? activeIndex : null;
            if (activeIndex > -1) items[activeIndex].active = true;
            return items;
        }

        function renderPreview() {
            var config = getPreviewConfig();
            var items = collectPreviewItems(config);
            var dark = previewState.theme === 'dark';
            var textColor = dark ? config.text_color_dark : config.text_color;
            var activeTextColor = dark ? config.active_text_color_dark : config.active_text_color;
            var backgroundColor = dark ? config.background_color_dark : config.background_color;
            var backgroundAlpha = dark ? config.background_alpha_dark : config.background_alpha;
            var borderColor = dark ? config.border_color_dark : config.border_color;
            var borderAlpha = dark ? config.border_alpha_dark : config.border_alpha;
            var shadowColor = dark ? config.shadow_color_dark : config.shadow_color;
            var shadowAlpha = dark ? config.shadow_alpha_dark : config.shadow_alpha;
            var activeBgColor = dark ? config.active_bg_color_dark : config.active_bg_color;
            var activeBgAlpha = dark ? config.active_bg_alpha_dark : config.active_bg_alpha;
            var $root = $('#bn-live-preview-root');
            var $nav = $('#bn-live-preview-nav');
            $root.attr('data-theme', dark ? 'dark' : 'light');
            $root.css({
                '--preview-side-gap': parseFloat(config.side_gap || 14) + 'px',
                '--preview-bottom': parseFloat(config.bottom_offset || 14) + 'px',
                '--preview-gap': parseFloat(config.item_gap || 8) + 'px',
                '--preview-padding': parseFloat(config.container_padding || 8) + 'px',
                '--preview-radius': parseFloat(config.container_radius || 22) + 'px',
                '--preview-item-radius': parseFloat(config.item_radius || 16) + 'px',
                '--preview-item-height': parseFloat(config.item_height || 54) + 'px',
                '--preview-icon-size': parseFloat(config.icon_size || 20) + 'px',
                '--preview-icon-translate': parseFloat(config.icon_translate_y || 0) + 'px',
                '--preview-text-size': parseFloat(config.text_size || 11) + 'px',
                '--preview-text-translate': parseFloat(config.text_translate_y || 0) + 'px',
                '--preview-text': textColor,
                '--preview-bg': rgba(backgroundColor, backgroundAlpha, dark ? '17, 24, 39' : '255, 255, 255'),
                '--preview-border': rgba(borderColor, borderAlpha, '255, 255, 255'),
                '--preview-shadow': '0 ' + parseFloat(config.shadow_y || 14) + 'px ' + parseFloat(config.shadow_blur || 34) + 'px ' + rgba(shadowColor, shadowAlpha, dark ? '0, 0, 0' : '15, 23, 42'),
                '--preview-blur': parseFloat(config.blur_strength || 24) + 'px',
                '--preview-active-color': activeTextColor,
                '--preview-active-bg': rgba(activeBgColor, activeBgAlpha, '33, 150, 243'),
                '--preview-font-weight': parseFloat(config.font_weight || 600),
                '--preview-active-scale': parseFloat(config.active_scale || 1.04),
                '--preview-active-translate': parseFloat(config.active_translate_y || -1) + 'px',
                '--preview-item-gap': config.show_label === 'y' ? '3px' : '0px'
            });
            $('#bn-preview-mode').text(dark ? '深色模式' : '浅色模式');
            $('#bn-preview-count').text(items.length + ' 项');
            if (!items.length) {
                previewState.activeIndex = null;
                $nav.addClass('is-empty').html('<div class="bn-preview-empty">当前没有可显示的导航项，启用一个导航项后这里会立即显示。</div>');
                return;
            }
            $nav.removeClass('is-empty');
            var html = '';
            items.forEach(function(item, index) {
                html += '<div class="bn-preview-item' + (item.active ? ' active' : '') + '" data-preview-index="' + index + '">';
                if ((item.active ? item.active_icon_image : item.icon_image)) {
                    html += '<img src="' + escapeHtml(item.active ? item.active_icon_image : item.icon_image) + '" alt="' + escapeHtml(item.title) + '">';
                } else {
                    html += '<i class="' + escapeHtml(item.active ? item.active_icon : item.icon) + '"></i>';
                }
                if (config.show_label === 'y') {
                    html += '<span>' + escapeHtml(item.title) + '</span>';
                }
                html += '</div>';
            });
            $nav.html(html);
        }

        function applyDefaultConfig() {
            $.each(defaultConfig, function(name, value) {
                if (name === 'nav_items') {
                    return;
                }
                var $field = $('[name="' + name + '"]').first();
                if (!$field.length) {
                    return;
                }
                if ($field.attr('type') === 'checkbox') {
                    $field.prop('checked', value === 'y');
                    return;
                }
                $field.val(value);
                if ($field.is('textarea')) {
                    return;
                }
                if ($field.attr('type') === 'number' && rangeConfigs[name]) {
                    syncRangeBox($field);
                }
                if (/color/i.test(name)) {
                    $('[data-sync="' + name + '"]').val(value);
                }
            });
            $('#bn-item-list').empty();
            defaultItems.forEach(function(item) {
                appendItem(item);
            });
            form.render();
            renderPreview();
            syncWorkbenchMenuByScroll();
        }

        function setActiveSideItem(targetId) {
            $('[data-panel-target]').removeClass('active');
            $('[data-panel-target="' + targetId + '"]').addClass('active');
        }

        function shouldHidePreviewColumn() {
            if (window.innerWidth <= 1500) {
                return true;
            }
            var screenWidth = window.screen && window.screen.availWidth ? window.screen.availWidth : 0;
            var screenHeight = window.screen && window.screen.availHeight ? window.screen.availHeight : 0;
            var widthGap = screenWidth ? Math.max(0, screenWidth - window.outerWidth) : 0;
            var heightGap = screenHeight ? Math.max(0, screenHeight - window.outerHeight) : 0;
            return widthGap > 120 || heightGap > 140;
        }

        function updatePreviewColumnVisibility() {
            var hidden = shouldHidePreviewColumn();
            var $workbench = $('.bn-workbench');
            $workbench.toggleClass('hide-preview', hidden);
            if (hidden && $('[data-panel-target="bn-panel-preview"]').hasClass('active')) {
                setActiveSideItem('bn-panel-layout');
            }
        }

        function updateWorkbenchViewport() {
            var $openBox = $('#open-box');
            var $footerBar = $('#form-btn');
            if (!$openBox.length || !$footerBar.length) return;
            updatePreviewColumnVisibility();
            var footerHeight = Math.ceil($footerBar.outerHeight(true) || 0);
            document.documentElement.style.setProperty('--bn-footer-height', footerHeight + 'px');
            if (window.innerWidth <= 768) {
                $openBox.css('height', 'auto');
                return;
            }
            var openBoxTop = Math.max(0, Math.floor($openBox.offset().top || 0));
            var availableHeight = window.innerHeight - openBoxTop - footerHeight;
            $openBox.css('height', Math.max(320, availableHeight) + 'px');
        }

        function syncWorkbenchMenuByScroll() {
            var $scrollContainer = $('.bn-settings-column');
            if (!$scrollContainer.length) return;
            var scrollTop = $scrollContainer.scrollTop() + 80;
            if ($('.bn-workbench').hasClass('hide-preview')) {
                if ($scrollContainer.scrollTop() < 24) {
                    setActiveSideItem('bn-panel-layout');
                    return;
                }
            }
            if ($scrollContainer.scrollTop() < 24) {
                setActiveSideItem('bn-panel-preview');
                return;
            }
            var panelIds = ['bn-panel-layout', 'bn-panel-colors', 'bn-panel-items'];
            var currentId = panelIds[0];
            panelIds.forEach(function(id) {
                var $panel = $('#' + id);
                if ($panel.length && $panel.position().top <= scrollTop) {
                    currentId = id;
                }
            });
            setActiveSideItem(currentId);
        }

        function updateItemIndexes() {
            $('#bn-item-list .bn-item-card').each(function(i) {
                $(this).find('.bn-item-index').text(i + 1);
            });
        }

        function bindItemEvents(scope) {
            scope.find('.nav-title-input').off('input').on('input', function() {
                var title = $(this).val().trim() || '自定义';
                $(this).closest('.bn-item-card').find('.item-name-preview').text(title);
            });
            scope.find('.nav-enabled-switch').off('change').on('change', function() {
                $(this).closest('.bn-item-actions').find('.nav-enabled-state').val(this.checked ? 'y' : 'n');
            });
            scope.find('.bn-icon-input').off('input').on('input', function() {
                refreshCardIconUi($(this).closest('.bn-item-card'));
            });
            initIconUploads(scope);
            refreshCardIconUi(scope.closest('.bn-item-card').length ? scope.closest('.bn-item-card') : scope);
        }

        function appendItem(item) {
            var html = $('#bn-item-template').html().replace('__INDEX__', $('#bn-item-list .bn-item-card').length + 1);
            var $item = $(html);
            if (item) {
                $item.find('.nav-enabled-switch').prop('checked', item.enabled !== 'n');
                $item.find('.nav-enabled-state').val(item.enabled === 'n' ? 'n' : 'y');
                $item.find('select[name="nav_type[]"]').val(item.type || 'custom');
                $item.find('select[name="nav_visible[]"]').val(item.visible || 'all');
                $item.find('input[name="nav_title[]"]').val(item.title || '自定义');
                $item.find('.item-name-preview').text(item.title || '自定义');
                $item.find('select[name="nav_target[]"]').val(item.target || '_self');
                $item.find('input[name="nav_url[]"]').val(item.url || '');
                $item.find('input[name="nav_icon[]"]').val(item.icon || 'ri-apps-2-line');
                $item.find('input[name="nav_active_icon[]"]').val(item.active_icon || '');
                $item.find('input[name="nav_icon_image[]"]').val(item.icon_image || '');
                $item.find('input[name="nav_active_icon_image[]"]').val(item.active_icon_image || '');
                $item.find('textarea[name="nav_match_rule[]"]').val(item.match_rule || '');
            }
            $('#bn-item-list').append($item);
            bindItemEvents($item);
            form.render();
            updateItemIndexes();
            renderPreview();
        }

        $(document).on('click', '.bn-item-remove', function() {
            $(this).closest('.bn-item-card').remove();
            updateItemIndexes();
            renderPreview();
        });

        $(document).on('input change', '.bn-range-slider', function() {
            var name = $(this).data('target-name');
            var $input = $('input[name="' + name + '"]').first();
            if (!$input.length) return;
            $input.val($(this).val());
            syncRangeBox($input);
            renderPreview();
        });

        $('[data-sync]').on('input change', function() {
            var name = $(this).data('sync');
            $('input[name="' + name + '"]').val($(this).val());
        });

        bindItemEvents($('#bn-item-list'));
        enhanceRangeInputs();
        $.each(rangeConfigs, function(name) {
            syncRangeBox($('input[name="' + name + '"]').first());
        });

        $('#form').on('input change', 'input, select, textarea', function() {
            var $el = $(this);
            if ($el.hasClass('bn-range-slider')) return;
            if ($el.attr('type') === 'number' && rangeConfigs[$el.attr('name')]) {
                syncRangeBox($el);
            }
            renderPreview();
        });

        $(document).on('change', '.nav-enabled-switch', function() {
            $(this).closest('.bn-item-actions').find('.nav-enabled-state').val(this.checked ? 'y' : 'n');
        });

        $(document).on('click', '.bn-icon-trigger', function() {
            var $input = $(this).siblings('.bn-icon-input');
            openIconPicker($input);
        });

        $(document).on('click', '.bn-icon-image-clear', function() {
            var $card = $(this).closest('.bn-item-card');
            if ($(this).data('role') === 'active-icon-image') {
                $card.find('input[name="nav_active_icon_image[]"]').val('');
            } else {
                $card.find('input[name="nav_icon_image[]"]').val('');
            }
            refreshCardIconUi($card);
            renderPreview();
        });

        $(document).on('click', '[data-preview-theme]', function() {
            previewState.theme = $(this).data('preview-theme');
            $('[data-preview-theme]').removeClass('active');
            $(this).addClass('active');
            renderPreview();
        });

        $(document).on('click', '.bn-preview-item[data-preview-index]', function() {
            var index = parseInt($(this).attr('data-preview-index'), 10);
            if (isNaN(index)) {
                return;
            }
            previewState.activeIndex = index;
            renderPreview();
        });

        $(document).on('click', '[data-panel-target]', function() {
            var targetId = $(this).data('panel-target');
            var $target = $('#' + targetId);
            var $scrollContainer = $('.bn-settings-column');
            if (!$target.length) return;
            if (targetId === 'bn-panel-preview' && $('.bn-workbench').hasClass('hide-preview')) return;
            setActiveSideItem(targetId);
            if (!$scrollContainer.length) return;
            if (targetId === 'bn-panel-preview') {
                $scrollContainer.stop().animate({ scrollTop: 0 }, 220);
                return;
            }
            $scrollContainer.stop().animate({ scrollTop: $scrollContainer.scrollTop() + $target.position().top - 18 }, 220);
        });

        $('.bn-settings-column').on('scroll', function() {
            syncWorkbenchMenuByScroll();
        });

        $('#bn-add-item').on('click', function() {
            appendItem();
        });

        $('#bn-reset-default').on('click', function() {
            layer.confirm('确定恢复为默认三项导航吗？当前未保存的改动会保留在页面，保存后才会生效。', {icon: 3, title: '提示'}, function(index) {
                layer.close(index);
                $('#bn-item-list').empty();
                defaultItems.forEach(function(item) {
                    appendItem(item);
                });
                renderPreview();
            });
        });

        $('#bn-reset-config').on('click', function() {
            layer.confirm('确定将当前底部导航配置恢复为默认值吗？该操作仅重置页面内容，点击保存后才会正式生效。', {icon: 3, title: '提示'}, function(index) {
                layer.close(index);
                applyDefaultConfig();
                layer.msg('已恢复默认配置，请点击保存使其生效');
            });
        });

        $(window).on('resize', function() {
            updateWorkbenchViewport();
        });

        updateWorkbenchViewport();
        setTimeout(updateWorkbenchViewport, 0);
        renderPreview();
        syncWorkbenchMenuByScroll();

        function closeCurrentSettingDialog() {
            if (window.parent && window.parent !== window && window.parent.layer) {
                var frameIndex = window.parent.layer.getFrameIndex(window.name);
                if (frameIndex !== undefined && frameIndex !== null && frameIndex !== '') {
                    window.parent.layer.close(frameIndex);
                    return true;
                }
            }
            return false;
        }

        function handleSaveSuccess(message) {
            var text = message || '保存成功';
            if (window.parent && window.parent.layer) {
                closeCurrentSettingDialog();
                window.parent.layer.msg(text);
                if (window.parent.table) window.parent.table.reload();
            } else {
                layer.msg(text);
            }
        }

        form.on('submit(save)', function() {
            $.post($('#form').attr('action'), $('#form').serialize(), function(res) {
                if (res.code == 400) {
                    return layer.msg(res.msg || '保存失败');
                }
                handleSaveSuccess('已保存配置');
            }, 'json');
            return false;
        });
    });
    </script>
    <?php
}

function plugin_setting($tpl = 'default')
{
    $tplOptions = TplOptions::getInstance();
    $defaults = bottomNavDefaultDefaultConfig();
    $data = [];

    $switchFields = ['show_on_desktop', 'show_label', 'enable_safe_area', 'show_user_center_auto', 'show_on_goods_detail'];
    foreach ($switchFields as $field) {
        $data[$field] = isset($_POST[$field]) ? 'y' : 'n';
    }

    $intFields = [
        'desktop_breakpoint' => [480, 2200],
        'max_width' => [0, 1600],
        'side_gap' => [0, 120],
        'bottom_offset' => [0, 80],
        'container_padding' => [0, 30],
        'item_gap' => [0, 30],
        'container_radius' => [0, 60],
        'item_radius' => [0, 40],
        'item_height' => [32, 120],
        'icon_size' => [12, 48],
        'icon_translate_y' => [-20, 20],
        'text_size' => [10, 24],
        'text_translate_y' => [-20, 20],
        'font_weight' => [400, 800],
        'blur_strength' => [0, 60],
        'z_index' => [10, 99999],
        'shadow_y' => [0, 60],
        'shadow_blur' => [0, 80],
        'background_alpha' => [0, 100],
        'background_alpha_dark' => [0, 100],
        'border_alpha' => [0, 100],
        'border_alpha_dark' => [0, 100],
        'active_bg_alpha' => [0, 100],
        'active_bg_alpha_dark' => [0, 100],
        'shadow_alpha' => [0, 100],
        'shadow_alpha_dark' => [0, 100],
    ];
    foreach ($intFields as $field => $range) {
        $value = isset($_POST[$field]) ? (int)$_POST[$field] : (int)$defaults[$field];
        $data[$field] = (string)max($range[0], min($range[1], $value));
    }

    $floatFields = [
        'active_scale' => [1, 1.3],
        'active_translate_y' => [-10, 10],
    ];
    foreach ($floatFields as $field => $range) {
        $value = isset($_POST[$field]) ? (float)$_POST[$field] : (float)$defaults[$field];
        $value = max($range[0], min($range[1], $value));
        $data[$field] = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    $colorFields = ['background_color', 'background_color_dark', 'border_color', 'border_color_dark', 'text_color', 'text_color_dark', 'active_text_color', 'active_text_color_dark', 'active_bg_color', 'active_bg_color_dark', 'shadow_color', 'shadow_color_dark'];
    foreach ($colorFields as $field) {
        $value = isset($_POST[$field]) ? trim($_POST[$field]) : $defaults[$field];
        $data[$field] = preg_match('/^#([0-9a-fA-F]{6})$/', $value) ? $value : $defaults[$field];
    }

    $data['hide_on_links'] = isset($_POST['hide_on_links']) ? trim((string)$_POST['hide_on_links']) : $defaults['hide_on_links'];

    $titles = isset($_POST['nav_title']) && is_array($_POST['nav_title']) ? $_POST['nav_title'] : [];
    $types = isset($_POST['nav_type']) && is_array($_POST['nav_type']) ? $_POST['nav_type'] : [];
    $urls = isset($_POST['nav_url']) && is_array($_POST['nav_url']) ? $_POST['nav_url'] : [];
    $icons = isset($_POST['nav_icon']) && is_array($_POST['nav_icon']) ? $_POST['nav_icon'] : [];
    $activeIcons = isset($_POST['nav_active_icon']) && is_array($_POST['nav_active_icon']) ? $_POST['nav_active_icon'] : [];
    $iconImages = isset($_POST['nav_icon_image']) && is_array($_POST['nav_icon_image']) ? $_POST['nav_icon_image'] : [];
    $activeIconImages = isset($_POST['nav_active_icon_image']) && is_array($_POST['nav_active_icon_image']) ? $_POST['nav_active_icon_image'] : [];
    $targets = isset($_POST['nav_target']) && is_array($_POST['nav_target']) ? $_POST['nav_target'] : [];
    $matchRules = isset($_POST['nav_match_rule']) && is_array($_POST['nav_match_rule']) ? $_POST['nav_match_rule'] : [];
    $visibleRules = isset($_POST['nav_visible']) && is_array($_POST['nav_visible']) ? $_POST['nav_visible'] : [];
    $enabledStates = isset($_POST['nav_enabled_state']) && is_array($_POST['nav_enabled_state']) ? $_POST['nav_enabled_state'] : [];

    $navItems = [];
    $count = count($titles);
    for ($i = 0; $i < $count; $i++) {
        $title = trim((string)($titles[$i] ?? ''));
        $type = trim((string)($types[$i] ?? 'custom'));
        $icon = trim((string)($icons[$i] ?? 'ri-apps-2-line'));
        $activeIcon = trim((string)($activeIcons[$i] ?? ''));
        $iconImage = bottomNavDefaultSanitizeIconImage($iconImages[$i] ?? '');
        $activeIconImage = bottomNavDefaultSanitizeIconImage($activeIconImages[$i] ?? '');
        $url = trim((string)($urls[$i] ?? ''));
        $target = ($targets[$i] ?? '_self') === '_blank' ? '_blank' : '_self';
        $matchRule = trim((string)($matchRules[$i] ?? ''));
        $visible = ($visibleRules[$i] ?? 'all') === 'user_center_enabled' ? 'user_center_enabled' : 'all';
        if ($title === '' && $url === '' && $icon === '' && $activeIcon === '' && $iconImage === '' && $activeIconImage === '') {
            continue;
        }
        if (!in_array($type, ['home', 'order_query', 'user', 'help', 'blog', 'custom'], true)) {
            $type = 'custom';
        }
        $navItems[] = [
            'enabled' => (($enabledStates[$i] ?? 'n') === 'y') ? 'y' : 'n',
            'type' => $type,
            'title' => $title !== '' ? $title : '未命名',
            'url' => $url,
            'icon' => $icon !== '' ? $icon : 'ri-apps-2-line',
            'active_icon' => $activeIcon,
            'icon_image' => $iconImage,
            'active_icon_image' => $activeIconImage,
            'target' => $target,
            'match_rule' => $matchRule,
            'visible' => $visible,
        ];
    }

    $data['nav_items'] = empty($navItems) ? bottomNavDefaultDefaultItems() : $navItems;

    $saveRows = [];
    foreach ($data as $name => $value) {
        $saveRows[] = [
            'template' => bottomNavDefaultSettingKey($tpl),
            'name' => $name,
            'depend' => '',
            'data' => serialize($value),
        ];
    }
    $tplOptions->insert('data', $saveRows, true);
    Output::ok('保存成功');
}
