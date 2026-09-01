<?php
/**
 * 博客边栏管理
 */

/**
 * @var string $action
 * @var object $CACHE
 */

require_once 'globals.php';

$isAjaxWg = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!function_exists('blogWidgetCleanText')) {
    function blogWidgetCleanText($value, $limit = 120) {
        $value = trim(strip_tags((string)$value));
        $limit = max(1, (int)$limit);
        return function_exists('mb_substr') ? mb_substr($value, 0, $limit, 'UTF-8') : substr($value, 0, $limit);
    }
}

if (!function_exists('blogWidgetCleanIcon')) {
    function blogWidgetCleanIcon($value) {
        $value = preg_replace('/[^\w\-\s]/', '', (string)$value);
        return trim($value);
    }
}

if (!function_exists('blogWidgetCleanUrl')) {
    function blogWidgetCleanUrl($value) {
        $value = trim(strip_tags((string)$value));
        if ($value === '') {
            return '';
        }
        if (preg_match('#^(https?:)?//#i', $value)
            || strpos($value, '/') === 0
            || strpos($value, './') === 0
            || strpos($value, '../') === 0
            || strpos($value, '#') === 0
            || stripos($value, 'mailto:') === 0
            || stripos($value, 'tel:') === 0) {
            return $value;
        }
        return '';
    }
}

if (!function_exists('blogWidgetCleanImageUrl')) {
    function blogWidgetCleanImageUrl($value) {
        $value = trim(strip_tags((string)$value));
        if ($value === '') {
            return '';
        }
        if (preg_match('#^(https?:)?//#i', $value)
            || strpos($value, '/') === 0
            || strpos($value, './') === 0
            || strpos($value, '../') === 0) {
            return $value;
        }
        return '';
    }
}

if (!function_exists('blogWidgetCleanExternalLinks')) {
    function blogWidgetCleanExternalLinks($raw, $limit = 8) {
        $links = [];
        foreach (preg_split('/\r\n|\r|\n/', (string)$raw) as $line) {
            if (count($links) >= $limit) {
                break;
            }
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $parts = array_map('trim', explode('|', $line, 3));
            if (count($parts) < 3) {
                continue;
            }
            $icon = blogWidgetCleanIcon($parts[0]);
            $name = blogWidgetCleanText($parts[1], 30);
            $url = blogWidgetCleanUrl($parts[2]);
            if ($name === '' || $url === '') {
                continue;
            }
            $links[] = $icon . '|' . $name . '|' . $url;
        }
        return implode("\n", $links);
    }
}

if ($action === 'get_data') {
    $widgets = Option::get('widgets1');
    $widgetTitle = Option::get('widget_title');
    $custom_widget = Option::get('custom_widget');
    $widgetTitle = array_merge(Option::getWidgetTitle(), is_array($widgetTitle) ? $widgetTitle : []);
    $widgets = is_array($widgets) ? $widgets : [];
    $custom_widget = is_array($custom_widget) ? $custom_widget : [];
    Output::ok([
        'widgets' => array_values($widgets),
        'widget_title' => $widgetTitle,
        'custom_widget' => $custom_widget,
        'options' => [
            'index_comnum' => Option::get('index_comnum'),
            'comment_subnum' => Option::get('comment_subnum'),
            'index_newtwnum' => Option::get('index_newtwnum'),
            'index_newlognum' => Option::get('index_newlognum'),
            'index_hotlognum' => Option::get('index_hotlognum'),
            'blogger_show_nickname' => Option::get('blogger_show_nickname'),
            'blogger_nickname' => Option::get('blogger_nickname'),
            'blogger_avatar' => Option::get('blogger_avatar'),
            'blogger_intro_show' => Option::get('blogger_intro_show'),
            'blogger_intro_text' => Option::get('blogger_intro_text'),
            'blogger_button1_show' => Option::get('blogger_button1_show'),
            'blogger_button1_text' => Option::get('blogger_button1_text'),
            'blogger_button1_icon' => Option::get('blogger_button1_icon'),
            'blogger_button1_url' => Option::get('blogger_button1_url'),
            'blogger_button1_newtab' => Option::get('blogger_button1_newtab'),
            'blogger_button2_show' => Option::get('blogger_button2_show'),
            'blogger_button2_text' => Option::get('blogger_button2_text'),
            'blogger_button2_icon' => Option::get('blogger_button2_icon'),
            'blogger_button2_url' => Option::get('blogger_button2_url'),
            'blogger_button2_newtab' => Option::get('blogger_button2_newtab'),
            'blogger_external_links' => Option::get('blogger_external_links'),
        ]
    ]);
}

if ($action === '') {



    $widgets = Option::get('widgets1');
    $widgetTitle = Option::get('widget_title');
    $custom_widget = Option::get('custom_widget');
    $widgetTitle = array_merge(Option::getWidgetTitle(), is_array($widgetTitle) ? $widgetTitle : []);
    $widgets = is_array($widgets) ? $widgets : [];
    $custom_widget = is_array($custom_widget) ? $custom_widget : [];
    $widgetTitle = array_map('htmlspecialchars', $widgetTitle);
    $tpl_sidenum = Option::get('tpl_sidenum');

    foreach ($custom_widget as $key => $val) {
        $custom_widget[$key] = array_map('htmlspecialchars', $val);
    }

    $customWgTitle = [];
    foreach ($widgetTitle as $key => $val) {
        if (preg_match("/^.*\s\((.*)\)/", $val, $matchs)) {
            $customWgTitle[$key] = $matchs[1];
        } else {
            $customWgTitle[$key] = $val;
        }
    }


    $br = '<a href="./">数据中心</a><a href="./article.php">博客管理</a><a><cite>边栏管理</cite></a>';

    include View::getAdmView('header');
    require_once View::getAdmView('widgets');
    include View::getAdmView('footer');
    View::output();
}

if ($action === 'setwg') {
    $widgetTitle = Option::get('widget_title');                                             //当前所有组件标题
    $widgetTitle = array_merge(Option::getWidgetTitle(), is_array($widgetTitle) ? $widgetTitle : []);
    $widget = isset($_GET['wg']) ? $_GET['wg'] : '';                                        //要修改的组件
    $wgTitle = isset($_POST['title']) ? $_POST['title'] : '';                               //新组件名
    $systemWidgets = array_diff(array_keys(Option::getWidgetTitle()), ['custom_text']);

    if ($widget !== 'custom_text' && !in_array($widget, $systemWidgets, true)) {
        Output::error('组件不存在');
    }

    if ($widget !== 'custom_text') {
        preg_match("/^(.*)\s\(.*/", $widgetTitle[$widget], $matchs);
        $realWgTitle = isset($matchs[1]) ? $matchs[1] : $widgetTitle[$widget];

        $widgetTitle[$widget] = $realWgTitle != $wgTitle && $wgTitle !== '' ? $realWgTitle . ' (' . $wgTitle . ')' : $realWgTitle;
        $widgetTitle = addslashes(serialize($widgetTitle));

        Option::updateOption('widget_title', $widgetTitle);
    }

    switch ($widget) {
        case 'newcomm':
            $index_comnum = isset($_POST['index_comnum']) ? (int)$_POST['index_comnum'] : 5;
            $index_comnum = max(1, min(6, $index_comnum));
            $comment_subnum = isset($_POST['comment_subnum']) ? (int)$_POST['comment_subnum'] : 60;
            $comment_subnum = max(5, min(90, $comment_subnum));
            Option::updateOption('index_comnum', $index_comnum);
            Option::updateOption('comment_subnum', $comment_subnum);
            $CACHE->updateCache('comment');
            break;
        case 'blogger':
            $blogger_show_nickname = isset($_POST['blogger_show_nickname']) ? 'y' : 'n';
            $blogger_nickname = blogWidgetCleanText($_POST['blogger_nickname'] ?? '', 60);
            $blogger_avatar = blogWidgetCleanImageUrl($_POST['blogger_avatar'] ?? '');
            $blogger_intro_show = isset($_POST['blogger_intro_show']) ? 'y' : 'n';
            $blogger_intro_text = blogWidgetCleanText($_POST['blogger_intro_text'] ?? '', 500);
            $blogger_button1_show = isset($_POST['blogger_button1_show']) ? 'y' : 'n';
            $blogger_button1_text = blogWidgetCleanText($_POST['blogger_button1_text'] ?? '文章手札', 30);
            $blogger_button1_icon = blogWidgetCleanIcon($_POST['blogger_button1_icon'] ?? 'ri-book-open-line');
            $blogger_button1_url = blogWidgetCleanUrl($_POST['blogger_button1_url'] ?? '');
            $blogger_button1_newtab = isset($_POST['blogger_button1_newtab']) ? 'y' : 'n';
            $blogger_button2_show = isset($_POST['blogger_button2_show']) ? 'y' : 'n';
            $blogger_button2_text = blogWidgetCleanText($_POST['blogger_button2_text'] ?? '返回首页', 30);
            $blogger_button2_icon = blogWidgetCleanIcon($_POST['blogger_button2_icon'] ?? 'ri-home-heart-line');
            $blogger_button2_url = blogWidgetCleanUrl($_POST['blogger_button2_url'] ?? '');
            $blogger_button2_newtab = isset($_POST['blogger_button2_newtab']) ? 'y' : 'n';
            $blogger_external_links = blogWidgetCleanExternalLinks($_POST['blogger_external_links'] ?? '', 8);

            Option::updateOption('blogger_show_nickname', $blogger_show_nickname);
            Option::updateOption('blogger_nickname', $blogger_nickname);
            Option::updateOption('blogger_avatar', $blogger_avatar);
            Option::updateOption('blogger_intro_show', $blogger_intro_show);
            Option::updateOption('blogger_intro_text', $blogger_intro_text);
            Option::updateOption('blogger_button1_show', $blogger_button1_show);
            Option::updateOption('blogger_button1_text', $blogger_button1_text);
            Option::updateOption('blogger_button1_icon', $blogger_button1_icon);
            Option::updateOption('blogger_button1_url', $blogger_button1_url);
            Option::updateOption('blogger_button1_newtab', $blogger_button1_newtab);
            Option::updateOption('blogger_button2_show', $blogger_button2_show);
            Option::updateOption('blogger_button2_text', $blogger_button2_text);
            Option::updateOption('blogger_button2_icon', $blogger_button2_icon);
            Option::updateOption('blogger_button2_url', $blogger_button2_url);
            Option::updateOption('blogger_button2_newtab', $blogger_button2_newtab);
            Option::updateOption('blogger_external_links', $blogger_external_links);
            break;
        case 'twitter':
            $index_newtwnum = isset($_POST['index_newtwnum']) ? (int)$_POST['index_newtwnum'] : 5;
            $index_newtwnum = max(1, min(5, $index_newtwnum));
            Option::updateOption('index_newtwnum', $index_newtwnum);
            break;
        case 'newlog':
            $index_newlog = isset($_POST['index_newlog']) ? (int)$_POST['index_newlog'] : 5;
            $index_newlog = max(1, min(6, $index_newlog));
            Option::updateOption('index_newlognum', $index_newlog);
            $CACHE->updateCache('newlog');
            break;
        case 'hotlog':
            $index_hotlognum = isset($_POST['index_hotlognum']) ? (int)$_POST['index_hotlognum'] : 5;
            $index_hotlognum = max(1, min(6, $index_hotlognum));
            Option::updateOption('index_hotlognum', $index_hotlognum);
            break;
        case 'custom_text':
            $custom_widget = Option::get('custom_widget');
            $custom_widget = is_array($custom_widget) ? $custom_widget : [];
            $title = isset($_POST['title']) ? $_POST['title'] : '';
            $content = isset($_POST['content']) ? $_POST['content'] : '';
            $custom_wg_id = isset($_POST['custom_wg_id']) ? $_POST['custom_wg_id'] : '';//要修改的组件id
            $new_title = isset($_POST['new_title']) ? $_POST['new_title'] : '';
            $new_content = isset($_POST['new_content']) ? $_POST['new_content'] : '';
            $rmwg = isset($_GET['rmwg']) ? $_GET['rmwg'] : '';//要删除的组件id
            $custom_wg_id = preg_match('/^custom_wg_\d+$/', $custom_wg_id) ? $custom_wg_id : '';
            $rmwg = preg_match('/^custom_wg_\d+$/', $rmwg) ? $rmwg : '';
            //添加新自定义组件
            if ($rmwg) {
                $widgets = Option::get('widgets1');
                if (is_array($widgets) && !empty($widgets)) {
                    foreach ($widgets as $key => $val) {
                        if ($val == $rmwg) {
                            unset($widgets[$key]);
                        }
                    }
                    $widgets = array_values($widgets);
                    $widgets_str = addslashes(serialize($widgets));
                    Option::updateOption("widgets1", $widgets_str);
                }
                unset($custom_widget[$rmwg]);
                $custom_widget_str = addslashes(serialize($custom_widget));
                Option::updateOption('custom_widget', $custom_widget_str);
            } elseif ($custom_wg_id && array_key_exists($custom_wg_id, $custom_widget)) {
                $custom_widget[$custom_wg_id] = array('title' => $title, 'content' => $content);
                $custom_widget_str = addslashes(serialize($custom_widget));
                Option::updateOption('custom_widget', $custom_widget_str);
            } elseif ($new_title !== '' || $new_content !== '') {
                //确定组件索引
                $i = 0;
                $maxKey = 0;
                if (is_array($custom_widget)) {
                    foreach ($custom_widget as $key => $val) {
                        if (preg_match("/^custom_wg_(\d+)/", $key, $matches)) {
                            $k = (int)$matches[1];
                            if ($k > $i) {
                                $maxKey = $k;
                            }
                            $i = $k;
                        }
                    }
                }
                $custom_wg_index = $maxKey + 1;
                $custom_wg_index = 'custom_wg_' . $custom_wg_index;
                $custom_widget[$custom_wg_index] = array('title' => $new_title, 'content' => $new_content);
                $custom_widget_str = addslashes(serialize($custom_widget));
                Option::updateOption('custom_widget', $custom_widget_str);
            }
            break;
    }
    $CACHE->updateCache('options');
    if ($isAjaxWg) { Output::ok(); }
    emDirect("./widgets.php?activated=1");
}

if ($action === 'compages') {
    $postedWidgets = isset($_POST['widgets']) && is_array($_POST['widgets']) ? $_POST['widgets'] : [];
    $customWidget = Option::get('custom_widget');
    $customWidget = is_array($customWidget) ? $customWidget : [];
    $allowWidgets = array_merge(array_diff(array_keys(Option::getWidgetTitle()), ['custom_text']), array_keys($customWidget));
    $postedWidgets = array_values(array_filter($postedWidgets, function ($widget) use ($allowWidgets) {
        return in_array($widget, $allowWidgets, true);
    }));
    $widgets = addslashes(serialize($postedWidgets));
    Option::updateOption("widgets1", $widgets);
    $CACHE->updateCache('options');
    if ($isAjaxWg) { Output::ok(); }
    emDirect("./widgets.php?activated=1");
}

if ($action === 'reset') {
    LoginAuth::checkToken();
    $widget_title = serialize(Option::getWidgetTitle());
    $default_widget = serialize(Option::getDefWidget());

    Option::updateOption("widget_title", $widget_title);
    Option::updateOption("custom_widget", 'a:0:{}');
    Option::updateOption("widgets1", $default_widget);

    $CACHE->updateCache('options');
    if ($isAjaxWg) { Output::ok(); }
    emDirect("./widgets.php?activated=1");
}
