<?php

/**
 * 侧边栏
 */
defined('DC_ROOT') || exit('access denied!');
$blogSidebarShow = Option::get('blog_sidebar_show') === 'n' ? false : true;
if (!$blogSidebarShow) {
    return;
}
$blogSidebarCardStyle = Option::get('blog_sidebar_card_style');
$blogSidebarCardStyle = in_array($blogSidebarCardStyle, ['default', 'compact', 'clean'], true) ? $blogSidebarCardStyle : 'default';
?>
<div class="column-small side-bar blog-sidebar-style-<?= htmlspecialchars($blogSidebarCardStyle, ENT_QUOTES) ?>">
    <?php
    $widgets = !empty($options_cache['widgets1']) ? unserialize($options_cache['widgets1']) : array();
    $widgets = is_array($widgets) ? $widgets : array();
    if (!array_key_exists('widgets1', $options_cache)) {
        $widgets = Option::getDefWidget();
    }
    $widget_title = !empty($options_cache['widget_title']) ? @unserialize($options_cache['widget_title']) : array();
    $widget_title = is_array($widget_title) ? array_merge(Option::getWidgetTitle(), $widget_title) : Option::getWidgetTitle();
    $custom_widget = !empty($options_cache['custom_widget']) ? @unserialize($options_cache['custom_widget']) : array();
    $custom_widget = is_array($custom_widget) ? $custom_widget : array();
    doAction('diff_side');


    foreach ($widgets as $val) {
        if (strpos($val, 'custom_wg_') === 0) {
            $callback = 'widget_custom_text';
            if (function_exists($callback) && isset($custom_widget[$val])) {
                call_user_func($callback, htmlspecialchars($custom_widget[$val]['title']), $custom_widget[$val]['content']);
            }
        } else {
            $callback = 'widget_' . $val;
            if (function_exists($callback)) {
                $widgetTitle = isset($widget_title[$val]) ? $widget_title[$val] : $val;
                preg_match("/^.*\s\((.*)\)/", $widgetTitle, $matchs);
                $wgTitle = isset($matchs[1]) ? $matchs[1] : $widgetTitle;
                call_user_func($callback, htmlspecialchars($wgTitle));
            }
        }
    }
    ?>
</div>