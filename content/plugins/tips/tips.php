<?php
/*
Plugin Name: 碎碎念
Version: 1.1.0
Plugin URL:
Description: 后台首页随机展示一句碎碎念，内容可自定义，位置可调整，智能适配DIY后台主题配色插件的背景色调
Author: DCSHOP
Author URL:
Ui: Layui
*/

defined('DC_ROOT') || exit('access denied!');

$array_tips = [
    '💾 为防数据丢失，建议定期备份数据库和重要文件',
    '🛍️ 应用商店有丰富的插件和模板，快去探索吧',
    '📄 利用自建页面功能，为您的商城打造独特的展示页面',
    '🔒 检查并删除站点根目录下的 install.php 文件，确保网站安全',
    '🌐 推荐使用 Chrome、Edge 等现代浏览器获得最佳体验',
    '📊 定期查看订单统计和用户反馈，优化商城运营',
    '🎨 尝试不同的主题配色，让您的商城更具个性',
    '⚡ 启用缓存功能可以显著提升网站访问速度',
    '🔧 遇到问题时，先查看系统日志获取详细错误信息',
    '💡 合理设置商品分类和标签，方便用户快速找到商品'
];

function tips_init() {
    $plugin_storage = Storage::getInstance('tips');
    $custom_tips = $plugin_storage->getValue('custom_tips');
    $tip_position = $plugin_storage->getValue('tip_position') ?: 'top';
    
    // 如果有自定义小贴士，使用自定义的，否则使用默认的
    if (!empty($custom_tips)) {
        $tips_array = array_filter(explode("\n", $custom_tips));
        // 去除每行的空白字符
        $tips_array = array_map('trim', $tips_array);
        $tips_array = array_filter($tips_array); // 再次过滤空行
    } else {
        global $array_tips;
        $tips_array = $array_tips;
    }
    
    if (empty($tips_array)) return;
    
    $i = mt_rand(0, count($tips_array) - 1);
    $tip = $tips_array[$i];
    
    // 根据位置添加不同的 CSS 类
    $positionClass = $tip_position == 'bottom' ? 'tip-bottom' : 'tip-top';
    echo "<div id=\"tip\" class=\"{$positionClass}\"> $tip</div>";
}

// 始终使用 adm_main_top 钩子，通过 CSS 控制位置
addAction('adm_main_top', 'tips_init');

function tips_css() {
    $plugin_storage = Storage::getInstance('tips');
    $show_icon = $plugin_storage->getValue('show_icon');
    $show_icon = ($show_icon === null || $show_icon === '') ? '1' : $show_icon;
    $tip_position = $plugin_storage->getValue('tip_position') ?: 'top';
    
    // 读取配色插件的设置
    $activePlugins = Option::get('active_plugins') ?: [];
    $isAdminColorActive = in_array('admin_color/admin_color.php', $activePlugins);
    
    $tipColor = '#999999'; // 默认灰色
    
    if ($isAdminColorActive) {
        $colorStorage = Storage::getInstance('admin_color');
        $bgType = $colorStorage->getValue('bg_type') ?: 'solid';
        $bgColor = $colorStorage->getValue('bg_color') ?: '#f0f2f5';
        $bgPreset = $colorStorage->getValue('bg_preset') ?: '';
        
        // 判断背景是否为深色
        $isDarkBg = false;
        if ($bgType == 'solid' && $bgColor) {
            // 纯色背景，计算亮度
            $hex = ltrim($bgColor, '#');
            if (strlen($hex) == 6) {
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
                $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;
                $isDarkBg = $brightness < 128;
            }
        } elseif ($bgType == 'preset' && $bgPreset) {
            // 预设渐变，检查是否包含深色关键词
            $isDarkBg = (strpos($bgPreset, '#2c3e50') !== false || 
                        strpos($bgPreset, '#0c3483') !== false ||
                        strpos($bgPreset, '#374151') !== false);
        }
        
        // 深色背景用浅色文字，浅色背景用深色文字
        $tipColor = $isDarkBg ? '#e5e7eb' : '#6b7280';
    }
    
    // 根据是否显示图标设置不同样式
    $backgroundStyle = '';
    $paddingStyle = '';
    
    if ($show_icon == '1') {
        $backgroundStyle = 'background:url(../content/plugins/tips/icon_tips.gif) no-repeat left 3px;';
        $paddingStyle = 'padding:0px 18px;';
    } else {
        $paddingStyle = 'padding:8px 12px;';
    }
    
    // 根据位置设置不同的样式
    $positionStyle = '';
    if ($tip_position == 'bottom') {
        $positionStyle = '
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
        max-width: 400px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border: 1px solid rgba(0,0,0,0.1);';
    }
    
    echo "<style>
    #tip{
        {$backgroundStyle}
        {$paddingStyle}
        font-size:14px;
        color: {$tipColor};
        margin-bottom: 12px;
        border-radius: 4px;
        {$positionStyle}
    }
    .tip-bottom {
        animation: slideInUp 0.5s ease-out;
        background-color: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
    }
    @keyframes slideInUp {
        from {
            transform: translateY(100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    </style>\n";
}
// EP EM ET
addAction('adm_head', 'tips_css');
