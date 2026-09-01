<?php
/**
 * 默认博客模板(default) - 模板配置文件
 *
 * 配置内容：
 * 1. 博客基础信息
 * 2. 首页轮播
 * 3. 博客导航管理
 *
 * 页面结构和样式按 content/templates/default/default_setting.php 的配置页写法组织。
 */

defined('DC_ROOT') || exit('access denied!');

if (!function_exists('blogDefaultTemplateUrl')) {
    function blogDefaultTemplateUrl($path = '')
    {
        $path = ltrim(str_replace('\\', '/', (string)$path), '/');
        $base = defined('BLOG_TEMPLATE_URL') ? BLOG_TEMPLATE_URL : (DC_URL . 'content/blog_templates/default/');
        if (!preg_match('#^(https?:)?//#i', $base) && strpos($base, '/') !== 0 && strpos($base, './') !== 0 && strpos($base, '../') !== 0) {
            $base = rtrim(DC_URL, '/') . '/' . ltrim($base, '/');
        }
        return rtrim($base, '/') . '/' . $path;
    }
}

if (!function_exists('blogDefaultLogoUrl')) {
    function blogDefaultLogoUrl()
    {
        return blogDefaultTemplateUrl('images/logo.png');
    }
}

if (!function_exists('blogDefaultTplRawOptions')) {
    function blogDefaultTplRawOptions()
    {
        static $options = null;
        if ($options === null) {
            $options = Cache::getInstance()->readCache('options');
            $options = is_array($options) ? $options : [];
        }
        return $options;
    }
}

if (!function_exists('blogDefaultTplOptionExists')) {
    function blogDefaultTplOptionExists($name)
    {
        $options = blogDefaultTplRawOptions();
        return array_key_exists($name, $options);
    }
}

if (!function_exists('blogDefaultTplOption')) {
    function blogDefaultTplOption($name, $default = '', $emptyFallback = null)
    {
        $options = blogDefaultTplRawOptions();
        if (array_key_exists($name, $options)) {
            $value = $options[$name];
            if (($value === '' || $value === null) && $emptyFallback !== null) {
                return $emptyFallback;
            }
            return $value;
        }
        return $default;
    }
}

if (!function_exists('blogDefaultTplOfficialUrl')) {
    function blogDefaultTplOfficialUrl()
    {
        return 'https://dcshop.xzsc.cc/';
    }
}

if (!function_exists('blogDefaultTplSiteName')) {
    function blogDefaultTplSiteName()
    {
        return 'DCSHOP的小博客';
    }
}

if (!function_exists('blogDefaultTplSiteDesc')) {
    function blogDefaultTplSiteDesc()
    {
        return '与你同行，探索心灵之窗。';
    }
}

if (!function_exists('blogDefaultTplFooterCustomText')) {
    function blogDefaultTplFooterCustomText()
    {
        return '© 2026 DCSHOP的博客。记录技术与生活。';
    }
}

if (!function_exists('blogDefaultTplFooterLinks')) {
    function blogDefaultTplFooterLinks()
    {
        return 'ri-links-line|DCSHOP多财商城官方默认链接|' . blogDefaultTplOfficialUrl();
    }
}

if (!function_exists('blogDefaultTplBloggerAvatar')) {
    function blogDefaultTplBloggerAvatar()
    {
        return '/content/blog_templates/default/images/logo.png';
    }
}

if (!function_exists('blogDefaultTplBloggerNickname')) {
    function blogDefaultTplBloggerNickname()
    {
        return 'DCSHOP多财商城';
    }
}

if (!function_exists('blogDefaultTplBloggerIntro')) {
    function blogDefaultTplBloggerIntro()
    {
        return '在多财小站里，记录技术灵感，也收藏生活微光。';
    }
}

if (!function_exists('blogDefaultTplBloggerExternalLinks')) {
    function blogDefaultTplBloggerExternalLinks()
    {
        return 'ri-links-line|DCSHOP多财商城官方默认链接|' . blogDefaultTplOfficialUrl();
    }
}

if (!function_exists('blogDefaultBannerItems')) {
    function blogDefaultBannerItems()
    {
        $items = [];
        for ($i = 1; $i <= 4; $i++) {
            $items[] = [
                'img' => blogDefaultTemplateUrl('images/img' . $i . '.png'),
                'title' => '',
                'url' => '',
                'newtab' => 'y',
                'enabled' => 'y',
            ];
        }
        return $items;
    }
}

if (!function_exists('blogDefaultFriendLink')) {
    function blogDefaultFriendLink()
    {
        return [
            'id' => 0,
            'sitename' => 'DCSHOP多财商城系统',
            'siteurl' => 'https://dcshop.xzsc.cc/',
            'icon' => '',
            'description' => '',
            '_is_default' => true,
        ];
    }
}

if (!function_exists('blogDefaultSettingContext')) {
    function blogDefaultSettingContext()
    {
        global $CACHE;

        $emPage = new Log_Model();
        $Blog_Navi_Model = new Blog_Navi_Model();
        $Blog_Navi_Model->initDefaultNavis();
        $blog_sorts = $CACHE->readCache('blog_sort');
        $pages = $emPage->getAllPageList();
        $blog_navis = $Blog_Navi_Model->getAllNavisRaw();
        foreach ($blog_navis as &$blog_navi) {
            $blog_navi['type_name'] = blogDefaultNavTypeName($blog_navi['type']);
        }
        unset($blog_navi);

        return [
            'blog_sorts' => is_array($blog_sorts) ? $blog_sorts : [],
            'pages' => is_array($pages) ? $pages : [],
            'blog_navis' => is_array($blog_navis) ? $blog_navis : [],
            'blog_site_name' => blogDefaultTplOption('blog_site_name', blogDefaultTplSiteName(), ''),
            'blog_site_desc' => blogDefaultTplOption('blog_site_desc', blogDefaultTplSiteDesc(), ''),
            'blog_logo' => Option::get('blog_logo') ?: blogDefaultLogoUrl(),
            'blog_title_link' => Option::get('blog_title_link') ?: 'blog',
            'blog_index_lognum' => blogDefaultReadIntOption('index_lognum', 10, 1, 20),
            'blog_list_layout' => in_array(Option::get('blog_list_layout'), ['default', 'compact', 'simple'], true) ? Option::get('blog_list_layout') : 'compact',
            'blog_list_show_cover' => Option::get('blog_list_show_cover') === 'n' ? 'n' : 'y',
            'blog_list_cover_height' => blogDefaultReadIntOption('blog_list_cover_height', 205, 120, 420),
            'blog_list_show_summary' => Option::get('blog_list_show_summary') === 'n' ? 'n' : 'y',
            'blog_list_summary_length' => blogDefaultReadIntOption('blog_list_summary_length', 180, 60, 500),
            'blog_list_show_category' => Option::get('blog_list_show_category') === 'n' ? 'n' : 'y',
            'blog_list_show_author' => Option::get('blog_list_show_author') === 'n' ? 'n' : 'y',
            'blog_list_show_date' => Option::get('blog_list_show_date') === 'n' ? 'n' : 'y',
            'blog_list_show_tags' => Option::get('blog_list_show_tags') === 'n' ? 'n' : 'y',
            'blog_list_show_readmore' => Option::get('blog_list_show_readmore') === 'n' ? 'n' : 'y',
            'blog_list_show_stats' => Option::get('blog_list_show_stats') === 'n' ? 'n' : 'y',
            'blog_detail_show_date' => Option::get('blog_detail_show_date') === 'n' ? 'n' : 'y',
            'blog_detail_show_reading_time' => Option::get('blog_detail_show_reading_time') === 'n' ? 'n' : 'y',
            'blog_detail_show_author' => Option::get('blog_detail_show_author') === 'n' ? 'n' : 'y',
            'blog_detail_show_category' => Option::get('blog_detail_show_category') === 'n' ? 'n' : 'y',
            'blog_detail_show_views' => Option::get('blog_detail_show_views') === 'n' ? 'n' : 'y',
            'blog_detail_show_comments_count' => Option::get('blog_detail_show_comments_count') === 'n' ? 'n' : 'y',
            'blog_detail_show_tags' => Option::get('blog_detail_show_tags') === 'n' ? 'n' : 'y',
            'blog_detail_show_share' => Option::get('blog_detail_show_share') === 'n' ? 'n' : 'y',
            'blog_detail_show_author_card' => Option::get('blog_detail_show_author_card') === 'n' ? 'n' : 'y',
            'blog_detail_show_related' => Option::get('blog_detail_show_related') === 'n' ? 'n' : 'y',
            'blog_detail_show_neighbor' => Option::get('blog_detail_show_neighbor') === 'n' ? 'n' : 'y',
            'blog_detail_show_comments' => Option::get('blog_detail_show_comments') === 'n' ? 'n' : 'y',
            'blog_sidebar_show' => Option::get('blog_sidebar_show') === 'n' ? 'n' : 'y',
            'blog_sidebar_position' => Option::get('blog_sidebar_position') === 'left' ? 'left' : 'right',
            'blog_sidebar_sticky' => Option::get('blog_sidebar_sticky') === 'y' ? 'y' : 'n',
            'blog_sidebar_mobile_show' => Option::get('blog_sidebar_mobile_show') === 'n' ? 'n' : 'y',
            'blog_sidebar_card_style' => in_array(Option::get('blog_sidebar_card_style'), ['default', 'compact', 'clean'], true) ? Option::get('blog_sidebar_card_style') : 'default',
            'blog_footer_show' => Option::get('blog_footer_show') === 'n' ? 'n' : 'y',
            'blog_footer_custom_text' => blogDefaultTplOption('blog_footer_custom_text', blogDefaultTplFooterCustomText(), ''),
            'blog_footer_show_icp' => Option::get('blog_footer_show_icp') === 'n' ? 'n' : 'y',
            'blog_footer_show_system' => Option::get('blog_footer_show_system') === 'n' ? 'n' : 'y',
            'blog_footer_links' => blogDefaultTplOption('blog_footer_links', blogDefaultTplFooterLinks(), ''),
        ];
    }
}

if (!function_exists('blogDefaultNavTypeName')) {
    function blogDefaultNavTypeName($type)
    {
        switch ((int)$type) {
            case Blog_Navi_Model::navitype_home:
            case Blog_Navi_Model::navitype_blog:
                return '系统';
            case Blog_Navi_Model::navitype_blogsort:
                return '文章分类';
            case Blog_Navi_Model::navitype_page:
                return '页面';
            case Blog_Navi_Model::navitype_custom:
            default:
                return '自定义';
        }
    }
}

if (!function_exists('blogDefaultNavQuickUrl')) {
    /**
     * 后台“快捷选择”只回填站内路径，不写入当前后台域名。
     * 这样博客独立域名下点击导航时会停留在博客域名；如需跳转外部/主站域名，仍可手动填写完整 URL。
     */
    function blogDefaultNavQuickUrl($url)
    {
        $url = trim((string)$url);
        if ($url === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $url)) {
            $parts = parse_url($url);
            if (is_array($parts)) {
                $path = isset($parts['path']) && $parts['path'] !== '' ? $parts['path'] : '/';
                $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '';
                $fragment = isset($parts['fragment']) && $parts['fragment'] !== '' ? '#' . $parts['fragment'] : '';
                return $path . $query . $fragment;
            }
        }

        if ($url[0] === '?') {
            return '/' . $url;
        }
        if ($url[0] === '/' || $url[0] === '#') {
            return $url;
        }
        return '/' . ltrim($url, '/');
    }
}

if (!function_exists('blogDefaultReadIntOption')) {
    function blogDefaultReadIntOption($name, $default, $min, $max)
    {
        $value = Option::get($name);
        if ($value === '' || $value === null) {
            return (string)$default;
        }
        $value = (int)$value;
        return (string)max($min, min($max, $value));
    }
}

if (!function_exists('blogDefaultDecodeBannerItems')) {
    function blogDefaultDecodeBannerItems($raw)
    {
        if (is_array($raw)) {
            return $raw;
        }

        $raw = trim((string)$raw);
        if ($raw === '') {
            return [];
        }

        $items = json_decode($raw, true);
        if (is_array($items)) {
            return $items;
        }

        $items = @unserialize($raw, ['allowed_classes' => false]);
        return is_array($items) ? $items : [];
    }
}

if (!function_exists('blogDefaultGetBannerItems')) {
    function blogDefaultGetBannerItems()
    {
        $rawItems = Option::get('blog_banner_items');
        $items = blogDefaultDecodeBannerItems($rawItems);
        if (!empty($items)) {
            return $items;
        }

        if ($rawItems !== '' && $rawItems !== null) {
            return [];
        }

        return blogDefaultBannerItems();
    }
}

if (!function_exists('plugin_setting_view')) {
    function plugin_setting_view($tpl = 'default')
    {
        $data = blogDefaultSettingContext();
        $blog_sorts = $data['blog_sorts'];
        $pages = $data['pages'];
        $blog_navis = $data['blog_navis'];
        $data['blog_banner_items'] = blogDefaultGetBannerItems();
        $data['blog_banner_show'] = Option::get('blog_banner_show');
        if ($data['blog_banner_show'] === '' || $data['blog_banner_show'] === null) {
            $data['blog_banner_show'] = 'y';
        }
        $data['blog_banner_speed'] = blogDefaultReadIntOption('blog_banner_speed', 3000, 500, 10000);
        $data['blog_banner_height'] = blogDefaultReadIntOption('blog_banner_height', 350, 120, 600);
        $data['blog_banner_mobile_height'] = blogDefaultReadIntOption('blog_banner_mobile_height', 200, 100, 420);
        $data['blog_banner_animation'] = in_array(Option::get('blog_banner_animation'), ['slide', 'fade'], true) ? Option::get('blog_banner_animation') : 'fade';
        $blogNavQuickSources = [
            'preset' => [
                ['name' => '博客首页', 'label' => '博客首页', 'url' => blogDefaultNavQuickUrl(function_exists('dcGetBlogHomeUrl') ? dcGetBlogHomeUrl() : DC_URL . 'blog')],
                ['name' => '商城首页', 'label' => '商城首页', 'url' => '/'],
                ['name' => 'DCSHOP多财商城官方默认链接', 'label' => 'DCSHOP多财商城官方默认链接', 'url' => blogDefaultTplOfficialUrl()],
            ],
            'sort' => [],
            'page' => [],
        ];
        foreach ($blog_sorts as $sort) {
            $prefix = !empty($sort['pid']) ? '└ ' : '';
            $blogNavQuickSources['sort'][] = [
                'name' => $sort['sortname'],
                'label' => $prefix . $sort['sortname'],
                'url' => blogDefaultNavQuickUrl(Url::blogSort($sort['sid'])),
            ];
        }
        foreach ($pages as $page) {
            $blogNavQuickSources['page'][] = [
                'name' => $page['title'],
                'label' => $page['title'],
                'url' => blogDefaultNavQuickUrl(Url::art($page['gid'])),
            ];
        }
        $settingAction = '?action=blog_setting_ajax&tpl=' . urlencode($tpl);
        $blogNavbarActionBase = './blog_navbar.php?action=';

        // Widget data
        $widgetsActive = Option::get('widgets1');
        $widgetsActive = is_array($widgetsActive) ? $widgetsActive : [];
        if (!blogDefaultTplOptionExists('widgets1')) {
            $widgetsActive = Option::getDefWidget();
        }
        $widgetTitleMap = Option::get('widget_title');
        $widgetTitleMap = array_merge(Option::getWidgetTitle(), is_array($widgetTitleMap) ? $widgetTitleMap : []);
        $customWidgets = Option::get('custom_widget');
        $customWidgets = is_array($customWidgets) ? $customWidgets : [];
        $sysWidgetDefs = [
            'blogger' => ['icon' => 'ri-user-line', 'name' => '个人资料'],
            'search' => ['icon' => 'ri-search-line', 'name' => '搜索'],
            'calendar' => ['icon' => 'ri-calendar-line', 'name' => '日历'],
            'tag' => ['icon' => 'ri-price-tag-3-line', 'name' => '标签'],
            'twitter' => ['icon' => 'ri-chat-1-line', 'name' => '微语'],
            'sort' => ['icon' => 'ri-folder-open-line', 'name' => '分类'],
            'archive' => ['icon' => 'ri-archive-line', 'name' => '存档'],
            'newcomm' => ['icon' => 'ri-chat-3-line', 'name' => '最新评论'],
            'newlog' => ['icon' => 'ri-file-text-line', 'name' => '最新文章'],
            'hotlog' => ['icon' => 'ri-fire-line', 'name' => '热门文章'],
            'link' => ['icon' => 'ri-link', 'name' => '友情链接'],
        ];
        $wgCustomTitles = [];
        foreach ($widgetTitleMap as $wkey => $wval) {
            $wgCustomTitles[$wkey] = preg_match("/^.*\\s\\((.*)\\)/", $wval, $wm) ? $wm[1] : $wval;
        }
        $wgOptions = [
            'index_comnum' => max(1, min(6, (int)(Option::get('index_comnum') ?: 5))),
            'comment_subnum' => max(5, min(90, (int)(Option::get('comment_subnum') ?: 60))),
            'index_newtwnum' => max(1, min(5, (int)(Option::get('index_newtwnum') ?: 5))),
            'index_newlognum' => max(1, min(6, (int)(Option::get('index_newlognum') ?: 5))),
            'index_hotlognum' => max(1, min(6, (int)(Option::get('index_hotlognum') ?: 5))),
            'blogger_show_nickname' => Option::get('blogger_show_nickname') === 'n' ? 'n' : 'y',
            'blogger_nickname' => blogDefaultTplOption('blogger_nickname', blogDefaultTplBloggerNickname(), ''),
            'blogger_avatar' => blogDefaultTplOption('blogger_avatar', blogDefaultTplBloggerAvatar(), ''),
            'blogger_intro_show' => Option::get('blogger_intro_show') === 'n' ? 'n' : 'y',
            'blogger_intro_text' => blogDefaultTplOption('blogger_intro_text', blogDefaultTplBloggerIntro(), ''),
            'blogger_button1_show' => Option::get('blogger_button1_show') === 'n' ? 'n' : 'y',
            'blogger_button1_text' => Option::get('blogger_button1_text') ?: '文章手札',
            'blogger_button1_icon' => Option::get('blogger_button1_icon') ?: 'ri-book-open-line',
            'blogger_button1_url' => Option::get('blogger_button1_url') ?: (function_exists('dcGetBlogHomeUrl') ? dcGetBlogHomeUrl() : DC_URL . 'blog'),
            'blogger_button1_newtab' => Option::get('blogger_button1_newtab') === 'y' ? 'y' : 'n',
            'blogger_button2_show' => Option::get('blogger_button2_show') === 'n' ? 'n' : 'y',
            'blogger_button2_text' => Option::get('blogger_button2_text') ?: '返回首页',
            'blogger_button2_icon' => Option::get('blogger_button2_icon') ?: 'ri-home-heart-line',
            'blogger_button2_url' => Option::get('blogger_button2_url') ?: DC_URL,
            'blogger_button2_newtab' => Option::get('blogger_button2_newtab') === 'y' ? 'y' : 'n',
            'blogger_external_links' => blogDefaultTplOption('blogger_external_links', blogDefaultTplBloggerExternalLinks(), ''),
        ];

        // Link data
        $Link_Model = new Link_Model();
        $blogLinks = $Link_Model->getLinks();
        ?>
        <script>document.body && document.body.classList.add('dc-template-setting-page');</script>
        <style>
            #open-box { padding-bottom: 70px; }
            #form-btn { background: #eee; position: fixed; bottom: 0; left: 0; right: 0; height: 50px; line-height: 50px; margin: 0 auto; text-align: center; z-index: 100; }
            .section-title { margin-top: 25px; padding-top: 20px; border-top: 1px dashed #e6e6e6; }
            .section-title:first-child { margin-top: 0; padding-top: 0; border-top: none; }
            .section-title .layui-form-label { color: #333; font-weight: bold; font-size: 14px; }
            .tab-inner { padding: 10px 0 20px; }
            .tab-inner .layui-form-item { margin-bottom: 18px; }
            .group-label { font-weight: 600; color: #555; font-size: 13px; padding: 10px 0 6px; border-bottom: 1px solid #f0f0f0; margin-bottom: 14px; }
            .setting-status-box { margin: 0 0 16px; padding: 12px 14px; border-radius: 8px; border: 1px solid rgba(182,95,54,0.2); background: rgba(228,171,59,0.12); color: #8f4527; font-size: 13px; line-height: 1.7; }
            .layui-tab-title li.layui-this { color: var(--theme-primary,#b65f36) !important; font-weight: 600; border-bottom: 2px solid var(--theme-primary,#b65f36) !important; }
            .layui-tab-title li:hover { color: var(--theme-primary,#b65f36) !important; }
            .blog-setting-card { border: 1px solid #e6e6e6; border-radius: 4px; padding: 15px; background: #fafafa; margin-bottom: 15px; }
            .blog-setting-card .layui-card { border: 1px solid #f0f0f0; border-radius: 4px; box-shadow: none; }
            .blog-setting-card .layui-card-header { color: #555; font-weight: 600; border-bottom: 1px solid #f0f0f0; }
            .blog-logo-preview { display: inline-flex; align-items: center; justify-content: center; width: 92px; height: 48px; margin-left: 8px; border: 1px dashed #ddd; border-radius: 4px; background: #fff; vertical-align: middle; overflow: hidden; }
            .blog-logo-preview img { max-width: 88px; max-height: 44px; }
            .ci-action-bar { display: flex; gap: 8px; margin-bottom: 10px; flex-wrap: wrap; }
            .ci-config-list { border: 1px solid #e6e6e6; border-radius: 10px; padding: 12px; background: #fafcff; }
            .ci-config-item { display: flex; align-items: stretch; gap: 10px; background: #fff; border: 1px solid #e8eef5; border-radius: 10px; padding: 12px; margin-bottom: 10px; transition: box-shadow .2s ease, border-color .2s ease, opacity .2s ease; }
            .ci-config-item:last-child { margin-bottom: 0; }
            .ci-config-item:hover { border-color: rgba(182,95,54,0.28); box-shadow: 0 8px 22px rgba(143,69,39,0.08); }
            .ci-config-item .ci-num { flex-shrink: 0; width: 22px; height: 22px; margin-top: 7px; background: #b65f36; color: #fff; border-radius: 50%; font-size: 12px; text-align: center; line-height: 22px; }
            .ci-config-item .ci-inputs { display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0; flex-wrap: wrap; }
            .ci-config-item .ci-name { flex: 1; min-width: 100px; }
            .ci-config-item .ci-url { flex: 2; min-width: 140px; }
            .ci-config-item .ci-url-wrap { flex: 2.6; min-width: 260px; display: flex; gap: 6px; align-items: center; }
            .ci-config-item .ci-url-wrap .ci-url { flex: 1; min-width: 0; }
            .ci-config-item .ci-ri-wrap { display:flex; align-items:center; flex-shrink:0; }
            .ci-nav-picker-btn { flex-shrink: 0; white-space: nowrap; height: 36px; line-height: 36px; padding: 0 10px; }
            .ci-ri-btn { width:38px; height:38px; border:1.5px dashed #ddd; border-radius:8px; background:#fafafa; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:all 0.2s; padding:0; }
            .ci-ri-btn:hover { border-color:var(--theme-primary,#b65f36); background:rgba(228,171,59,0.12); }
            .ci-ri-btn.ci-has-icon { border-style:solid; border-color:#d0d0d0; background:#fff; }
            .ci-ri-btn i { font-size:20px; color:var(--theme-primary,#b65f36); }
            .ci-ri-btn i.ci-ri-ph { font-size:16px; color:#ccc; }
            .ci-config-item .ci-del { flex-shrink: 0; cursor: pointer; color: #ff5722; font-size: 18px; padding: 4px; line-height: 36px; }
            .ci-config-item .ci-del:hover { color: #d32f2f; }
            .ci-drag { flex-shrink: 0; cursor: grab; color: #c0cada; font-size: 18px; line-height: 36px; user-select: none; }
            .ci-drag:hover { color: #b65f36; }
            .ci-config-item.ci-dragging { opacity: 0.35; box-shadow: none; }
            .ci-config-item.ci-drag-over { border: 2px dashed #5fb878 !important; background: #f4fff6 !important; }
            .blog-nav-type-badge { display:inline-flex; align-items:center; justify-content:center; height:28px; line-height:28px; padding:0 9px; border-radius:14px; background:#f4f7fb; color:#667797; font-size:12px; white-space:nowrap; flex-shrink:0; }
            .blog-nav-empty { text-align:center; color:#999; padding:28px 10px; }
            .blog-nav-check-wrap { display:flex; align-items:center; gap:4px; margin-left:5px; flex-shrink:0; }
            .setting-status-box.is-off { border-color: rgba(255,152,0,0.28); background: rgba(255,152,0,0.08); color: #9a6200; }
            .dependent-setting-panel { transition: opacity .2s ease, filter .2s ease; }
            .dependent-setting-panel.is-disabled { opacity: 0.48; filter: grayscale(0.08); }
            .dependent-setting-panel.is-disabled .group-label { color: #888; }
            .bn-full-item { display: block !important; }
            .bn-full-label { font-size: 13px; font-weight: 600; color: #555; margin-bottom: 8px; }
            .bn-tip { font-size: 12px; color: #999; margin-bottom: 10px; line-height: 1.6; }
            .bn-config-list { background: #f2f2f2; border-radius: 4px; padding: 10px; margin-bottom: 10px; }
            .bn-config-item { background: #fff; border: 1px solid #e6e6e6; border-radius: 4px; padding: 8px 10px; margin-bottom: 8px; position: relative; transition: box-shadow .2s ease, border-color .2s ease, opacity .2s ease; }
            .bn-config-item:last-child { margin-bottom: 0; }
            .bn-config-item:hover { border-color: rgba(182,95,54,0.28); box-shadow: 0 8px 22px rgba(143,69,39,0.08); }
            .bn-config-item.bn-dragging { opacity: 0.35; box-shadow: none; }
            .bn-config-item.bn-drag-over { border: 2px dashed #5fb878 !important; background: #f4fff6 !important; }
            .bn-num { position: absolute; left: -8px; top: 50%; transform: translateY(-50%); width: 20px; height: 20px; background: #5fb878; color: #fff; border-radius: 50%; font-size: 11px; text-align: center; line-height: 20px; }
            .bn-inputs { display: flex; gap: 8px; align-items: center; margin-left: 15px; flex-wrap: wrap; min-width: 0; }
            .bn-drag { flex-shrink: 0; cursor: grab; color: #c0cada; font-size: 18px; line-height: 36px; user-select: none; }
            .bn-drag:hover { color: #b65f36; }
            .bn-img-cell { display: flex; gap: 4px; align-items: center; flex: 3; min-width: 260px; overflow: hidden; }
            .bn-img-preview { width: 64px; height: 40px; border-radius: 4px; border: 1px solid #e6e6e6; background: #f0f0f0 center/cover no-repeat; flex-shrink: 0; }
            .bn-img-input { flex: 1; min-width: 0; }
            .bn-title { flex: 1.3; min-width: 150px; }
            .bn-upload-btn { flex-shrink: 0; white-space: nowrap; padding: 0 8px !important; height: 36px !important; line-height: 36px !important; }
            .bn-url-wrap { flex: 2; min-width: 240px; display: flex; gap: 6px; align-items: center; }
            .bn-url { flex: 1; min-width: 0; }
            .bn-nav-picker-btn { flex-shrink: 0; white-space: nowrap; height: 36px; line-height: 36px; padding: 0 10px; }
            .bn-check-wrap { display:flex; align-items:center; gap:4px; margin-left:5px; flex-shrink:0; }
            .bn-del { flex-shrink: 0; cursor: pointer; color: #ff5722; font-size: 18px; padding: 4px; line-height: 36px; }
            .bn-del:hover { color: #d32f2f; }
            .bn-add-btn { margin-top: 10px; cursor: pointer; color: #b65f36; font-size: 14px; display:inline-flex; align-items:center; gap:5px; }
            .bn-add-btn:hover { color: #8f4527; }
            .nav-quick-panel { padding: 16px; background: #fff; }
            .nav-quick-tip { font-size: 13px; color: #888; margin-bottom: 15px; line-height: 1.6; display: flex; align-items: center; gap: 6px; }
            .nav-quick-tip i { color: #b65f36; font-size: 16px; }
            .nav-quick-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; border-bottom: 1px solid #f0f0f0; padding-bottom: 12px; }
            .nav-quick-tab { display: inline-flex; align-items: center; justify-content: center; height: 34px; padding: 0 16px; border-radius: 17px; background: #f5f7fa; color: #555; cursor: pointer; transition: all .25s; font-size: 13px; font-weight: 500; border: 1px solid transparent; }
            .nav-quick-tab:hover { background: #f8ead5; color: #b65f36; }
            .nav-quick-tab.active { background: #b65f36; color: #fff; box-shadow: 0 3px 8px rgba(143,69,39,0.18); }
            .nav-quick-search { margin-bottom: 16px; position: relative; }
            .nav-quick-search input { border-radius: 8px; padding-left: 36px; border-color: #e0e0e0; transition: all .3s; height: 38px; }
            .nav-quick-search i.ri-search-line { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #aaa; font-size: 16px; pointer-events: none; }
            .nav-quick-list { max-height: 340px; overflow-y: auto; border-radius: 8px; padding: 4px; display: grid; gap: 10px; grid-template-columns: 1fr; }
            .nav-quick-list::-webkit-scrollbar { width: 6px; }
            .nav-quick-list::-webkit-scrollbar-thumb { background: #ddd; border-radius: 3px; }
            .nav-quick-list::-webkit-scrollbar-track { background: transparent; }
            .nav-quick-item { background: #fff; border: 1px solid #ebeef5; border-radius: 8px; padding: 12px 16px; padding-right: 40px; cursor: pointer; transition: all .25s; position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: center; }
            .nav-quick-item:hover { border-color: #b65f36; box-shadow: 0 4px 12px rgba(143,69,39,0.1); transform: translateY(-2px); }
            .nav-quick-item-icon { position: absolute; right: 16px; top: 50%; transform: translateY(-50%); font-size: 20px; color: #b65f36; opacity: 0; transition: all .25s; margin-right: -10px; }
            .nav-quick-item:hover .nav-quick-item-icon { opacity: 1; margin-right: 0; }
            .nav-quick-item:hover .nav-quick-item-title { color: #b65f36; }
            .nav-quick-item-title { font-size: 14px; color: #333; line-height: 1.5; font-weight: 500; transition: color .2s; }
            .nav-quick-item-meta { font-size: 12px; color: #999; margin-top: 6px; word-break: break-all; display: flex; align-items: center; gap: 4px; }
            .nav-quick-empty { text-align: center; color: #999; padding: 40px 10px; display: flex; flex-direction: column; align-items: center; gap: 10px; }
            .nav-quick-empty i { font-size: 48px; color: #ddd; }
            .icon-select-item:hover { background: #f0f0f0; border-color: #b65f36 !important; }
            @media (max-width: 768px) {
                .blog-logo-preview { margin-left: 0; margin-top: 8px; }
                .ci-config-item { align-items: flex-start; }
                .ci-config-item .ci-url-wrap { min-width: 100%; }
                .ci-nav-picker-btn { padding: 0 8px; }
                .bn-img-cell, .bn-url-wrap, .bn-title { min-width: 100%; }
                .bn-nav-picker-btn { padding: 0 8px; }
            }
            /* Widget tab */
            .wg-panel { display: flex; gap: 16px; flex-wrap: wrap; }
            .wg-panel > div { flex: 1; min-width: 280px; }
            .wg-lib-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border: 1px solid #e6e6e6; border-radius: 6px; margin-bottom: 8px; background: #fafafa; cursor: pointer; transition: border-color .2s; }
            .wg-lib-item:hover { border-color: #b65f36; }
            .wg-lib-item i.wg-icon { font-size: 20px; color: #666; flex-shrink: 0; }
            .wg-lib-item .wg-name { flex: 1; font-size: 13px; font-weight: 500; }
            .wg-lib-item .wg-custom-title { font-size: 12px; color: #999; }
            .wg-lib-item .wg-add-btn { color: #5fb878; font-size: 18px; flex-shrink: 0; cursor: pointer; }
            .wg-lib-item .wg-add-btn:hover { color: #4caf50; }
            .wg-active-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border: 1px solid #edd8be; border-radius: 6px; margin-bottom: 6px; background: #fff8e8; }
            .wg-active-item .wg-drag { cursor: move; color: #bbb; font-size: 16px; }
            .wg-active-item .wg-lbl { flex: 1; font-size: 13px; }
            .wg-active-item .wg-cfg-btn { color: #b65f36; cursor: pointer; font-size: 16px; }
            .wg-active-item .wg-rm-btn { color: #ff5722; cursor: pointer; font-size: 16px; }
            /* Link tab */
            .link-card { display: flex; align-items: center; gap: 12px; padding: 10px 14px; border: 1px solid #e6e6e6; border-radius: 6px; margin-bottom: 8px; background: #fafafa; }
            .link-card .link-icon { width: 36px; height: 36px; border-radius: 4px; object-fit: cover; flex-shrink: 0; background: #eee; }
            .link-card .link-info { flex: 1; min-width: 0; }
            .link-card .link-info .link-name { font-weight: 500; font-size: 14px; color: #333; }
            .link-card .link-info .link-url { font-size: 12px; color: #b65f36; word-break: break-all; }
            .link-card .link-actions { display: flex; gap: 8px; flex-shrink: 0; }
            .link-default-badge { display:inline-flex; align-items:center; height:22px; padding:0 8px; border-radius:999px; background:#f8ead5; color:#b65f36; font-size:12px; }
            .bg-icon-pick { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border:1px dashed #d6d6d6; border-radius:6px; background:#fafafa; cursor:pointer; font-size:13px; color:#555; transition:.2s; }
            .bg-icon-pick:hover { border-color:#b65f36; background:#fff; }
            .bg-icon-pick i.bg-icon-pick-preview { font-size:18px; color:#b65f36; }
            .bg-icon-pick .bg-icon-pick-label { color:#888; }
            .bg-icon-popup { padding:14px 16px; max-height:60vh; overflow-y:auto; }
            .bg-icon-popup-search { width:100%; padding:8px 12px; border:1px solid #e6e6e6; border-radius:6px; font-size:13px; margin-bottom:12px; box-sizing:border-box; }
            .bg-icon-popup-search:focus { outline:none; border-color:#b65f36; }
            .bg-icon-popup-group { font-size:12px; color:#999; font-weight:600; margin:14px 0 8px; padding-bottom:4px; border-bottom:1px solid #f2f2f2; }
            .bg-icon-popup-group:first-child { margin-top:0; }
            .bg-icon-popup-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(64px, 1fr)); gap:6px; }
            .bg-icon-cell { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:4px; padding:10px 4px; border:1px solid #f0f0f0; border-radius:6px; cursor:pointer; background:#fff; transition:.15s; }
            .bg-icon-cell:hover { border-color:#b65f36; background:#fff8e8; }
            .bg-icon-cell.is-active { border-color:#b65f36; background:#fff8e8; box-shadow:0 0 0 2px rgba(182,95,54,.2); }
            .bg-icon-cell i { font-size:20px; color:#555; }
            .bg-icon-cell .bg-icon-cell-label { font-size:11px; color:#888; line-height:1; }
            .bg-link-presets { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:12px; }
            .bg-link-preset { display:inline-flex; align-items:center; gap:4px; padding:5px 10px; border:1px solid #e6e6e6; border-radius:999px; cursor:pointer; font-size:12px; color:#555; background:#fafafa; transition:.2s; user-select:none; }
            .bg-link-preset:hover { border-color:#b65f36; background:#fff8e8; color:#b65f36; }
            .bg-link-preset i { font-size:14px; }
            .bg-link-preset.is-custom { background:#fff; border-color:#b65f36; color:#b65f36; border-style:dashed; }
            .bg-link-list { display:flex; flex-direction:column; gap:8px; }
            .bg-link-card { display:flex; align-items:center; gap:10px; padding:10px 12px; border:1px solid #ececec; border-radius:6px; background:#fafafa; }
            .bg-link-card-icon { width:34px; height:34px; display:grid; place-items:center; border-radius:50%; background:#fff8e8; color:#b65f36; flex-shrink:0; }
            .bg-link-card-icon i { font-size:18px; }
            .bg-link-card-info { flex:1; min-width:0; }
            .bg-link-card-info .bg-link-card-name { font-size:13px; font-weight:500; color:#333; }
            .bg-link-card-info .bg-link-card-url { font-size:12px; color:#888; word-break:break-all; line-height:1.4; margin-top:1px; }
            .bg-link-card-acts { display:flex; gap:4px; flex-shrink:0; }
            .bg-link-card-acts a { width:26px; height:26px; display:grid; place-items:center; border-radius:4px; color:#888; cursor:pointer; }
            .bg-link-card-acts a:hover { background:#fff; color:#b65f36; }
            .bg-link-card-acts a.is-danger:hover { background:#ffeae3; color:#ff5722; }
            .bg-link-empty { padding:18px; border:1px dashed #e6e6e6; border-radius:6px; text-align:center; color:#aaa; font-size:13px; }
            .bg-button-pair { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
            @media (max-width: 768px) { .bg-button-pair { grid-template-columns:1fr; } }
            .bg-button-card { border:1px solid #ececec; border-radius:8px; padding:12px 14px; background:#fafafa; transition:opacity .2s; }
            .bg-button-card.is-off { opacity:.55; }
            .bg-button-card-head { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:10px; padding-bottom:8px; border-bottom:1px solid #f0f0f0; flex-wrap:wrap; }
            .bg-button-card-title { font-size:13px; font-weight:600; color:#333; }
            .bg-button-card-preview { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:999px; background:#fff8e8; color:#b65f36; font-size:12px; max-width:100%; }
            .bg-button-card-preview i { font-size:14px; flex-shrink:0; }
            .bg-button-card-preview span { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:130px; }
            .bg-button-card .layui-form-item { margin-bottom:8px; }
            .bg-button-card .layui-form-label { width:60px; font-size:12px; padding:7px 0; }
            .bg-button-card .layui-input-block { margin-left:74px; }
            .bg-button-card .layui-input { font-size:12px; }
        </style>

        <div style="padding:15px 20px 80px;" id="open-box">
            <div class="layui-tab" lay-filter="setting-tab">
                <ul class="layui-tab-title">
                    <li class="layui-this"><i class="ri-settings-3-line"></i> 基础信息</li>
                    <li><i class="ri-slideshow-3-line"></i> 首页轮播</li>
                    <li><i class="ri-list-check-2"></i> 列表设置</li>
                    <li><i class="ri-article-line"></i> 详情页设置</li>
                    <li><i class="ri-sidebar-fold-line"></i> 侧栏设置</li>
                    <li><i class="ri-layout-bottom-line"></i> 页脚设置</li>
                    <li><i class="ri-layout-top-line"></i> 导航管理</li>
                    <li><i class="ri-layout-grid-line"></i> 侧栏组件</li>
                    <li><i class="ri-link"></i> 友情链接</li>
                </ul>
                <div class="layui-tab-content">
                    <div class="layui-tab-item layui-show"><div class="tab-inner">
                        <form class="layui-form" id="blog-basic-form" method="post" action="<?= $settingAction ?>">
                            <input name="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
                            <div class="setting-status-box">这里是默认博客模板的配置页。前台已固定为创意手绘风格配色；导航菜单在“导航管理”中维护。</div>
                            <div class="group-label">博客基础信息</div>

                            <div class="layui-form-item">
                                <label class="layui-form-label">博客名称</label>
                                <div class="layui-input-block">
                                    <input type="text" name="blog_site_name" value="<?= htmlspecialchars($data['blog_site_name'], ENT_QUOTES) ?>" class="layui-input" placeholder="留空则使用系统名称">
                                    <div class="layui-form-mid layui-text-em">显示在博客头部与浏览器标题中。</div>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">博客副标题</label>
                                <div class="layui-input-block">
                                    <input type="text" name="blog_site_desc" value="<?= htmlspecialchars($data['blog_site_desc'], ENT_QUOTES) ?>" class="layui-input" placeholder="留空则使用系统描述">
                                    <div class="layui-form-mid layui-text-em">显示在博客名称下方，可填写一句简短说明。</div>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">博客 Logo</label>
                                <div class="layui-input-block">
                                    <div style="display:flex;gap:8px;align-items:center;max-width:720px;flex-wrap:wrap;">
                                        <input type="text" name="blog_logo" id="blog_logo" value="<?= htmlspecialchars($data['blog_logo'], ENT_QUOTES) ?>" class="layui-input" placeholder="Logo 图片地址" style="flex:1;min-width:220px;">
                                        <button type="button" class="layui-btn layui-btn-normal" id="btn-upload-logo"><i class="ri-upload-line"></i> 上传</button>
                                        <button type="button" class="layui-btn layui-btn-primary" id="btn-clear-logo"><i class="ri-close-line"></i> 清空</button>
                                        <span class="blog-logo-preview" id="blog_logo_preview">
                                            <?php if (!empty($data['blog_logo'])): ?>
                                                <img src="<?= htmlspecialchars($data['blog_logo'], ENT_QUOTES) ?>" alt="logo">
                                            <?php else: ?>
                                                <i class="ri-image-line" style="font-size:22px;color:#bbb;"></i>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="layui-form-mid layui-text-em">建议使用透明 PNG 或 SVG；留空时默认显示模板内置 Logo：<code>content/blog_templates/default/images/logo.png</code>。</div>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">标题跳转</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="blog_title_link" value="blog" title="博客首页" <?= $data['blog_title_link'] == 'home' ? '' : 'checked' ?>>
                                    <input type="radio" name="blog_title_link" value="home" title="网站首页" <?= $data['blog_title_link'] == 'home' ? 'checked' : '' ?>>
                                    <div class="layui-form-mid layui-text-em">点击博客头部标题/Logo 后跳转的位置。</div>
                                </div>
                            </div>
                        </form>
                    </div></div>

                    <div class="layui-tab-item"><div class="tab-inner">
                        <form class="layui-form" id="blog-banner-form" method="post" action="<?= $settingAction ?>">
                            <input name="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
                            <div class="group-label">首页轮播</div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">启用轮播图</label>
                                <div class="layui-input-block">
                                    <input type="checkbox" <?= $data['blog_banner_show'] == 'y' ? 'checked' : '' ?> name="blog_banner_show" lay-skin="switch" lay-filter="blog_banner_show_switch" value="y" title=" ON |OFF ">
                                    <div class="layui-form-mid layui-text-em">开启后在博客首页文章列表上方显示轮播图；下方列表可先配置，关闭时前台不显示。</div>
                                </div>
                            </div>

                            <div class="setting-status-box" id="blog_banner_show_status"></div>

                            <div class="dependent-setting-panel" id="blog_banner_show_panel">
                                <div class="layui-form-item bn-full-item">
                                    <div class="bn-full-label">轮播图列表</div>
                                    <div class="bn-tip">建议尺寸：PC端 <strong>1200×400 px</strong>，移动端 <strong>750×280 px</strong>；支持 JPG/PNG/WebP/GIF。拖动左侧 ⠿ 可排序。</div>
                                    <div class="bn-config-list" id="blog-bn-list">
                                        <?php foreach ($data['blog_banner_items'] as $i => $bn): ?>
                                            <?php
                                            $bnImg = $bn['img'] ?? '';
                                            $bnTitle = $bn['title'] ?? '';
                                            $bnUrl = $bn['url'] ?? ($bn['link'] ?? '');
                                            $bnNewtab = ($bn['newtab'] ?? 'y') === 'n' ? 'n' : 'y';
                                            $bnEnabled = ($bn['enabled'] ?? 'y') === 'n' ? 'n' : 'y';
                                            ?>
                                            <div class="bn-config-item">
                                                <span class="bn-drag" title="拖拽排序">⠿</span>
                                                <span class="bn-num"><?= $i + 1 ?></span>
                                                <div class="bn-inputs">
                                                    <div class="bn-img-cell">
                                                        <div class="bn-img-preview" style="background-image:url('<?= htmlspecialchars($bnImg, ENT_QUOTES) ?>')"></div>
                                                        <input type="text" name="blog_bn_img[]" class="bn-img-input layui-input" value="<?= htmlspecialchars($bnImg, ENT_QUOTES) ?>" placeholder="图片地址（上传后自动填入）">
                                                        <button type="button" class="layui-btn layui-btn-xs layui-btn-warm bn-upload-btn"><i class="ri-upload-line"></i> 上传</button>
                                                    </div>
                                                    <input type="text" name="blog_bn_title[]" value="<?= htmlspecialchars($bnTitle, ENT_QUOTES) ?>" placeholder="图片标题/说明（选填）" class="layui-input bn-title">
                                                    <div class="bn-url-wrap">
                                                        <input type="text" name="blog_bn_url[]" value="<?= htmlspecialchars($bnUrl, ENT_QUOTES) ?>" placeholder="点击跳转链接（选填）" class="layui-input bn-url">
                                                        <button type="button" class="layui-btn layui-btn-primary bn-nav-picker-btn" onclick="openBlogNavQuickPicker(this)"><i class="ri-compasses-2-line"></i> 快捷选择</button>
                                                    </div>
                                                    <div class="bn-check-wrap">
                                                        <input type="checkbox" lay-skin="primary" lay-filter="blog_bn_enabled_dummy" title="启用" <?= $bnEnabled === 'y' ? 'checked' : '' ?>>
                                                        <input type="hidden" name="blog_bn_enabled[]" value="<?= htmlspecialchars($bnEnabled, ENT_QUOTES) ?>" class="bn-enabled-val">
                                                    </div>
                                                    <div class="bn-check-wrap">
                                                        <input type="checkbox" lay-skin="primary" lay-filter="blog_bn_newtab_dummy" title="新窗口" <?= $bnNewtab === 'y' ? 'checked' : '' ?>>
                                                        <input type="hidden" name="blog_bn_newtab[]" value="<?= htmlspecialchars($bnNewtab, ENT_QUOTES) ?>" class="bn-newtab-val">
                                                    </div>
                                                    <span class="bn-del" onclick="removeBlogBn(this)" title="删除"><i class="layui-icon layui-icon-delete"></i></span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="bn-add-btn" onclick="addBlogBn()"><i class="layui-icon layui-icon-add-circle"></i>添加一张</div>
                                </div>

                                <div class="layui-form-item">
                                    <label class="layui-form-label">PC端高度</label>
                                    <div class="layui-input-block">
                                        <div style="display:flex;align-items:center;gap:8px;max-width:240px;">
                                            <input type="number" name="blog_banner_height" value="<?= htmlspecialchars($data['blog_banner_height'], ENT_QUOTES) ?>" min="120" max="600" class="layui-input">
                                            <span style="color:#999;font-size:13px;white-space:nowrap;">px</span>
                                        </div>
                                        <div class="layui-form-mid layui-text-em">PC端轮播图高度，默认 350px，建议 200~500px。</div>
                                    </div>
                                </div>

                                <div class="layui-form-item">
                                    <label class="layui-form-label">手机端高度</label>
                                    <div class="layui-input-block">
                                        <div style="display:flex;align-items:center;gap:8px;max-width:240px;">
                                            <input type="number" name="blog_banner_mobile_height" value="<?= htmlspecialchars($data['blog_banner_mobile_height'], ENT_QUOTES) ?>" min="100" max="420" class="layui-input">
                                            <span style="color:#999;font-size:13px;white-space:nowrap;">px</span>
                                        </div>
                                        <div class="layui-form-mid layui-text-em">移动端轮播图高度，默认 200px，建议 140~280px。</div>
                                    </div>
                                </div>

                                <div class="layui-form-item">
                                    <label class="layui-form-label">切换速度</label>
                                    <div class="layui-input-block">
                                        <div style="display:flex;align-items:center;gap:8px;max-width:240px;">
                                            <input type="number" name="blog_banner_speed" value="<?= htmlspecialchars($data['blog_banner_speed'], ENT_QUOTES) ?>" min="500" max="10000" step="500" class="layui-input">
                                            <span style="color:#999;font-size:13px;white-space:nowrap;">毫秒</span>
                                        </div>
                                        <div class="layui-form-mid layui-text-em">每张图片停留时间，默认 3000ms，建议 2000~6000ms。</div>
                                    </div>
                                </div>

                                <div class="layui-form-item">
                                    <label class="layui-form-label">切换动画</label>
                                    <div class="layui-input-block">
                                        <input type="radio" name="blog_banner_animation" value="fade" <?= $data['blog_banner_animation'] == 'slide' ? '' : 'checked' ?> title="淡入淡出">
                                        <input type="radio" name="blog_banner_animation" value="slide" <?= $data['blog_banner_animation'] == 'slide' ? 'checked' : '' ?> title="横向滑动">
                                        <div class="layui-form-mid layui-text-em">淡入淡出兼容旧样式；横向滑动使用左右位移动画。</div>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div></div>

                    <div class="layui-tab-item"><div class="tab-inner">
                        <form class="layui-form" id="blog-list-form" method="post" action="<?= $settingAction ?>">
                            <input name="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
                            <div class="setting-status-box">这里控制博客首页、分类、标签、搜索、归档等文章列表页的分页数量、排版方式和显示内容。建议先设置“每页文章数”和“列表版式”，再按需要微调封面、摘要和底部信息。</div>
                            <div class="group-label">分页与排版</div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">每页文章数</label>
                                <div class="layui-input-block">
                                    <div style="display:flex;align-items:center;gap:8px;max-width:260px;">
                                        <input type="number" name="blog_index_lognum" value="<?= htmlspecialchars($data['blog_index_lognum'], ENT_QUOTES) ?>" min="1" max="20" class="layui-input">
                                        <span style="color:#999;font-size:13px;white-space:nowrap;">条/页</span>
                                    </div>
                                    <div class="layui-form-mid layui-text-em">首页文章列表达到此数量后自动分页；范围 1~20 条，推荐 5~10 条。</div>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">列表版式</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="blog_list_layout" value="default" title="横幅大图卡" <?= $data['blog_list_layout'] === 'default' ? 'checked' : '' ?>>
                                    <input type="radio" name="blog_list_layout" value="compact" title="紧凑图文卡" <?= $data['blog_list_layout'] === 'compact' ? 'checked' : '' ?>>
                                    <input type="radio" name="blog_list_layout" value="simple" title="纯文字清单" <?= $data['blog_list_layout'] === 'simple' ? 'checked' : '' ?>>
                                    <div class="layui-form-mid layui-text-em">默认使用“紧凑图文卡”；横幅大图卡：封面在上方，统一横图视觉；紧凑图文卡：文字左侧、封面右侧，适合文章较多时节省空间；纯文字清单：不显示封面，只保留文字内容。</div>
                                </div>
                            </div>
                            <div class="group-label" style="margin-top:24px;">封面与摘要</div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">横幅封面</label>
                                <div class="layui-input-block">
                                    <input type="checkbox" <?= $data['blog_list_show_cover'] === 'y' ? 'checked' : '' ?> name="blog_list_show_cover" lay-skin="switch" value="y" title=" ON |OFF ">
                                    <div style="display:flex;align-items:center;gap:8px;max-width:260px;margin-top:10px;">
                                        <input type="number" name="blog_list_cover_height" value="<?= htmlspecialchars($data['blog_list_cover_height'], ENT_QUOTES) ?>" min="120" max="420" class="layui-input">
                                        <span style="color:#999;font-size:13px;white-space:nowrap;">px</span>
                                    </div>
                                    <div class="layui-form-mid layui-text-em">列表封面按横图方式裁剪显示，建议上传 16:9 横图；高度范围 120~420px，默认 205px。关闭后使用占位或纯文字样式。</div>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">显示摘要</label>
                                <div class="layui-input-block">
                                    <input type="checkbox" <?= $data['blog_list_show_summary'] === 'y' ? 'checked' : '' ?> name="blog_list_show_summary" lay-skin="switch" value="y" title=" ON |OFF ">
                                    <div style="display:flex;align-items:center;gap:8px;max-width:260px;margin-top:10px;">
                                        <input type="number" name="blog_list_summary_length" value="<?= htmlspecialchars($data['blog_list_summary_length'], ENT_QUOTES) ?>" min="60" max="500" class="layui-input">
                                        <span style="color:#999;font-size:13px;white-space:nowrap;">字</span>
                                    </div>
                                    <div class="layui-form-mid layui-text-em">控制文章摘要是否显示，以及摘要截取长度，默认 180 字。</div>
                                </div>
                            </div>
                            <div class="group-label" style="margin-top:24px;">文章信息</div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">头部信息</label>
                                <div class="layui-input-block">
                                    <input type="checkbox" name="blog_list_show_category" value="y" title="分类" lay-skin="primary" <?= $data['blog_list_show_category'] === 'y' ? 'checked' : '' ?>>
                                    <input type="checkbox" name="blog_list_show_author" value="y" title="作者" lay-skin="primary" <?= $data['blog_list_show_author'] === 'y' ? 'checked' : '' ?>>
                                    <input type="checkbox" name="blog_list_show_date" value="y" title="日期" lay-skin="primary" <?= $data['blog_list_show_date'] === 'y' ? 'checked' : '' ?>>
                                    <div class="layui-form-mid layui-text-em">显示在标题附近的分类、作者和发布时间。</div>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">底部信息</label>
                                <div class="layui-input-block">
                                    <input type="checkbox" name="blog_list_show_tags" value="y" title="标签" lay-skin="primary" <?= $data['blog_list_show_tags'] === 'y' ? 'checked' : '' ?>>
                                    <input type="checkbox" name="blog_list_show_readmore" value="y" title="阅读全文" lay-skin="primary" <?= $data['blog_list_show_readmore'] === 'y' ? 'checked' : '' ?>>
                                    <input type="checkbox" name="blog_list_show_stats" value="y" title="阅读/评论数" lay-skin="primary" <?= $data['blog_list_show_stats'] === 'y' ? 'checked' : '' ?>>
                                    <div class="layui-form-mid layui-text-em">控制标签、继续阅读按钮以及阅读/评论统计是否显示。</div>
                                </div>
                            </div>
                        </form>
                    </div></div>

                    <div class="layui-tab-item"><div class="tab-inner">
                        <form class="layui-form" id="blog-detail-form" method="post" action="<?= $settingAction ?>">
                            <input name="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
                            <input type="hidden" name="blog_detail_submitted" value="1">
                            <div class="setting-status-box">控制文章详情页中元信息、标签、分享、作者卡片、相关文章、上一篇/下一篇和评论区域的显示。文章封面仅在列表卡片展示，详情页不再重复显示。</div>
                            <div class="group-label">头部与元信息</div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">文章头部</label>
                                <div class="layui-input-block">
                                    <input type="checkbox" name="blog_detail_show_date" value="y" title="发布时间" lay-skin="primary" <?= $data['blog_detail_show_date'] === 'y' ? 'checked' : '' ?>>
                                    <input type="checkbox" name="blog_detail_show_reading_time" value="y" title="预计阅读时间" lay-skin="primary" <?= $data['blog_detail_show_reading_time'] === 'y' ? 'checked' : '' ?>>
                                    <input type="checkbox" name="blog_detail_show_author" value="y" title="作者" lay-skin="primary" <?= $data['blog_detail_show_author'] === 'y' ? 'checked' : '' ?>>
                                    <input type="checkbox" name="blog_detail_show_category" value="y" title="分类" lay-skin="primary" <?= $data['blog_detail_show_category'] === 'y' ? 'checked' : '' ?>>
                                    <input type="checkbox" name="blog_detail_show_views" value="y" title="阅读数" lay-skin="primary" <?= $data['blog_detail_show_views'] === 'y' ? 'checked' : '' ?>>
                                    <input type="checkbox" name="blog_detail_show_comments_count" value="y" title="评论数" lay-skin="primary" <?= $data['blog_detail_show_comments_count'] === 'y' ? 'checked' : '' ?>>
                                    <div class="layui-form-mid layui-text-em">关闭某项后，文章标题下方不再显示对应信息。</div>
                                </div>
                            </div>
                            <div class="group-label" style="margin-top:24px;">详情模块</div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">页面模块</label>
                                <div class="layui-input-block">
                                    <input type="checkbox" name="blog_detail_show_tags" value="y" title="文章标签" lay-skin="primary" <?= $data['blog_detail_show_tags'] === 'y' ? 'checked' : '' ?>>
                                    <input type="checkbox" name="blog_detail_show_share" value="y" title="分享区" lay-skin="primary" <?= $data['blog_detail_show_share'] === 'y' ? 'checked' : '' ?>>
                                    <input type="checkbox" name="blog_detail_show_author_card" value="y" title="作者卡片" lay-skin="primary" <?= $data['blog_detail_show_author_card'] === 'y' ? 'checked' : '' ?>>
                                    <input type="checkbox" name="blog_detail_show_related" value="y" title="相关文章" lay-skin="primary" <?= $data['blog_detail_show_related'] === 'y' ? 'checked' : '' ?>>
                                    <input type="checkbox" name="blog_detail_show_neighbor" value="y" title="上一篇/下一篇" lay-skin="primary" <?= $data['blog_detail_show_neighbor'] === 'y' ? 'checked' : '' ?>>
                                    <input type="checkbox" name="blog_detail_show_comments" value="y" title="评论区" lay-skin="primary" <?= $data['blog_detail_show_comments'] === 'y' ? 'checked' : '' ?>>
                                    <div class="layui-form-mid layui-text-em">这些模块显示在正文下方，可按内容站需求灵活关闭。</div>
                                </div>
                            </div>
                        </form>
                    </div></div>

                    <div class="layui-tab-item"><div class="tab-inner">
                        <form class="layui-form" id="blog-sidebar-form" method="post" action="<?= $settingAction ?>">
                            <input name="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
                            <input type="hidden" name="blog_sidebar_submitted" value="1">
                            <div class="setting-status-box">控制博客列表页侧栏的显示、左右位置、滚动粘性、移动端显示以及卡片风格。</div>
                            <div class="group-label">侧栏布局</div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">显示侧栏</label>
                                <div class="layui-input-block">
                                    <input type="checkbox" <?= $data['blog_sidebar_show'] === 'y' ? 'checked' : '' ?> name="blog_sidebar_show" lay-skin="switch" value="y" title=" ON |OFF ">
                                    <div class="layui-form-mid layui-text-em">关闭后列表页主内容自动铺满宽度。</div>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">侧栏位置</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="blog_sidebar_position" value="right" title="右侧" <?= $data['blog_sidebar_position'] === 'left' ? '' : 'checked' ?>>
                                    <input type="radio" name="blog_sidebar_position" value="left" title="左侧" <?= $data['blog_sidebar_position'] === 'left' ? 'checked' : '' ?>>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">显示行为</label>
                                <div class="layui-input-block">
                                    <input type="checkbox" name="blog_sidebar_sticky" value="y" title="滚动粘性" lay-skin="primary" <?= $data['blog_sidebar_sticky'] === 'y' ? 'checked' : '' ?>>
                                    <input type="checkbox" name="blog_sidebar_mobile_show" value="y" title="移动端显示侧栏" lay-skin="primary" <?= $data['blog_sidebar_mobile_show'] === 'y' ? 'checked' : '' ?>>
                                    <div class="layui-form-mid layui-text-em">移动端关闭后，小屏只显示文章列表，不再堆叠侧栏组件。</div>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">卡片风格</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="blog_sidebar_card_style" value="default" title="默认" <?= $data['blog_sidebar_card_style'] === 'default' ? 'checked' : '' ?>>
                                    <input type="radio" name="blog_sidebar_card_style" value="compact" title="紧凑" <?= $data['blog_sidebar_card_style'] === 'compact' ? 'checked' : '' ?>>
                                    <input type="radio" name="blog_sidebar_card_style" value="clean" title="清爽" <?= $data['blog_sidebar_card_style'] === 'clean' ? 'checked' : '' ?>>
                                </div>
                            </div>
                        </form>
                    </div></div>

                    <div class="layui-tab-item"><div class="tab-inner">
                        <form class="layui-form" id="blog-footer-form" method="post" action="<?= $settingAction ?>">
                            <input name="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
                            <input type="hidden" name="blog_footer_submitted" value="1">
                            <div class="setting-status-box">控制博客底部是否显示，以及自定义文案、备案信息、系统页脚信息和社交链接。</div>
                            <div class="group-label">页脚内容</div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">显示页脚</label>
                                <div class="layui-input-block">
                                    <input type="checkbox" <?= $data['blog_footer_show'] === 'y' ? 'checked' : '' ?> name="blog_footer_show" lay-skin="switch" value="y" title=" ON |OFF ">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">自定义文案</label>
                                <div class="layui-input-block">
                                    <textarea name="blog_footer_custom_text" class="layui-textarea" placeholder="例如：© 2026 我的博客。记录技术与生活。"><?= htmlspecialchars($data['blog_footer_custom_text'], ENT_QUOTES) ?></textarea>
                                    <div class="layui-form-mid layui-text-em">支持换行，前台会按纯文本安全输出；留空则不显示。</div>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">内置信息</label>
                                <div class="layui-input-block">
                                    <input type="checkbox" name="blog_footer_show_icp" value="y" title="ICP备案" lay-skin="primary" <?= $data['blog_footer_show_icp'] === 'y' ? 'checked' : '' ?>>
                                    <input type="checkbox" name="blog_footer_show_system" value="y" title="系统页脚信息" lay-skin="primary" <?= $data['blog_footer_show_system'] === 'y' ? 'checked' : '' ?>>
                                    <div class="layui-form-mid layui-text-em">备案号来自系统设置；系统页脚信息对应原模板的 footer_info。</div>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">社交链接</label>
                                <div class="layui-input-block">
                                    <textarea name="blog_footer_links" class="layui-textarea" placeholder="每行一个：图标类名|名称|链接&#10;ri-github-line|GitHub|https://github.com/xxx&#10;ri-mail-line|邮箱|mailto:hello@example.com"><?= htmlspecialchars($data['blog_footer_links'], ENT_QUOTES) ?></textarea>
                                    <div class="layui-form-mid layui-text-em">每行格式：<code>Remix图标类名|显示名称|链接</code>，最多保存 12 条。</div>
                                </div>
                            </div>
                        </form>
                    </div></div>

                    <div class="layui-tab-item"><div class="tab-inner">
                        <div class="setting-status-box">博客导航会显示在博客模板头部。支持拖拽排序、图标选择、快捷填写链接、显示/隐藏、新窗口打开与删除；调整后点击底部“保存配置”生效。</div>
                        <div class="group-label">导航列表</div>
                        <form class="layui-form" id="blog-nav-list-form">
                            <input name="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
                            <div class="layui-form-item">
                                <label class="layui-form-label">导航菜单列表</label>
                                <div class="layui-input-block">
                                    <div class="ci-action-bar">
                                        <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" onclick="addBlogNav()"><i class="layui-icon layui-icon-add-circle"></i> 新增导航</button>
                                        <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" onclick="loadBlogNavList()"><i class="ri-refresh-line"></i> 刷新列表</button>
                                        <button type="button" class="layui-btn layui-btn-sm layui-btn-danger" onclick="clearBlogNavList()"><i class="ri-delete-bin-line"></i> 清空列表</button>
                                    </div>
                                    <div class="ci-config-list" id="blog-nav-list">
                                        <?php if (!empty($blog_navis)): ?>
                                            <?php foreach ($blog_navis as $i => $nav): ?>
                                                <?php
                                                $isCustom = (int)$nav['type'] === Blog_Navi_Model::navitype_custom;
                                                $navIcon = $nav['naviicon'] ?? '';
                                                $navUrl = $isCustom ? ($nav['url'] ?? '') : ($nav['resolved_url'] ?? ($nav['url'] ?? ''));
                                                ?>
                                                <div class="ci-config-item blog-nav-config-item" data-id="<?= (int)$nav['id'] ?>" data-type="<?= (int)$nav['type'] ?>">
                                                    <span class="ci-drag" title="拖拽排序">⠿</span>
                                                    <span class="ci-num"><?= $i + 1 ?></span>
                                                    <div class="ci-inputs">
                                                        <input type="hidden" name="nav_id[]" value="<?= (int)$nav['id'] ?>" class="nav-id-val">
                                                        <input type="hidden" name="nav_pid[]" value="<?= (int)$nav['pid'] ?>" class="nav-pid-val">
                                                        <input type="hidden" name="nav_hide[]" value="<?= ($nav['hide'] ?? 'n') === 'y' ? 'y' : 'n' ?>" class="nav-hide-val">
                                                        <div class="ci-ri-wrap">
                                                            <button type="button" class="ci-ri-btn <?= !empty($navIcon) ? 'ci-has-icon' : '' ?>" onclick="ciPickBlogNavRi(this)" title="点击选择 Remix 图标">
                                                                <i class="<?= !empty($navIcon) ? htmlspecialchars($navIcon, ENT_QUOTES) : 'ri-add-line ci-ri-ph' ?>"></i>
                                                            </button>
                                                            <input type="hidden" name="nav_ri[]" value="<?= htmlspecialchars($navIcon, ENT_QUOTES) ?>" class="ci-ri-val">
                                                        </div>
                                                        <input type="text" name="nav_name[]" value="<?= htmlspecialchars($nav['naviname'] ?? '', ENT_QUOTES) ?>" placeholder="导航名称" class="layui-input ci-name" style="min-width:100px;">
                                                        <div class="ci-url-wrap">
                                                            <input type="text" name="nav_url[]" value="<?= htmlspecialchars($navUrl, ENT_QUOTES) ?>" placeholder="跳转链接（支持手填或右侧快捷选择）" class="layui-input ci-url" <?= $isCustom ? '' : 'readonly' ?>>
                                                            <button type="button" class="layui-btn layui-btn-primary ci-nav-picker-btn<?= $isCustom ? '' : ' layui-btn-disabled' ?>" onclick="openBlogNavQuickPicker(this)" <?= $isCustom ? '' : 'disabled' ?>><i class="ri-compasses-2-line"></i> 快捷选择</button>
                                                        </div>
                                                        <span class="blog-nav-type-badge"><?= htmlspecialchars($nav['type_name'] ?? blogDefaultNavTypeName($nav['type']), ENT_QUOTES) ?></span>
                                                        <div class="blog-nav-check-wrap">
                                                            <input type="checkbox" lay-skin="primary" lay-filter="blog_nav_show_dummy" title="显示" <?= ($nav['hide'] ?? 'n') === 'n' ? 'checked' : '' ?>>
                                                        </div>
                                                        <div class="blog-nav-check-wrap">
                                                            <input type="checkbox" lay-skin="primary" lay-filter="blog_nav_newtab_dummy" title="新窗口" <?= ($nav['newtab'] ?? 'n') === 'y' ? 'checked' : '' ?>>
                                                            <input type="hidden" name="nav_newtab[]" value="<?= ($nav['newtab'] ?? 'n') === 'y' ? 'y' : 'n' ?>" class="nav-newtab-val">
                                                        </div>
                                                        <span class="ci-del" onclick="removeBlogNav(this)" title="删除"><i class="layui-icon layui-icon-delete"></i></span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="blog-nav-empty">暂无导航，点击“新增导航”创建。</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="layui-form-mid layui-text-em">拖动左侧 ⠿ 可调整顺序。删除行、清空列表、修改显示状态后需点击底部“保存配置”写入；文章分类/页面等系统导航链接由类型自动生成。</div>
                                </div>
                            </div>
                        </form>

                    </div></div>

                    <div class="layui-tab-item"><div class="tab-inner">
                        <div class="setting-status-box">管理博客侧栏中显示的组件。左侧可将组件添加到已启用列表，右侧拖拽调整顺序。点击 <i class="ri-settings-3-line"></i> 可配置组件标题和参数。</div>
                        <div class="wg-panel">
                            <div>
                                <div class="group-label">系统组件组件库</div>
                                <?php foreach ($sysWidgetDefs as $wid => $wdef): ?>
                                <div class="wg-lib-item" data-wid="<?= $wid ?>">
                                    <i class="wg-icon <?= $wdef['icon'] ?>"></i>
                                    <span class="wg-name"><?= htmlspecialchars($wdef['name']) ?><?php if (!empty($wgCustomTitles[$wid]) && $wgCustomTitles[$wid] !== $wdef['name']): ?> <span class="wg-custom-title">(<?= htmlspecialchars($wgCustomTitles[$wid]) ?>)</span><?php endif; ?></span>
                                    <span class="wg-add-btn" onclick="wgAddToActive('<?= $wid ?>')" title="添加到侧栏"><i class="ri-add-circle-line"></i></span>
                                </div>
                                <?php endforeach; ?>
                                <?php if (!empty($customWidgets)): ?>
                                <div class="group-label" style="font-size:12px;color:#999;border:none;padding:8px 0 6px;">自定义组件</div>
                                <?php foreach ($customWidgets as $cwid => $cw): ?>
                                <div class="wg-lib-item" data-wid="<?= htmlspecialchars($cwid) ?>">
                                    <i class="wg-icon ri-code-box-line"></i>
                                    <span class="wg-name"><?= htmlspecialchars($cw['title'] ?: $cwid) ?></span>
                                    <span class="wg-add-btn" onclick="wgAddToActive('<?= htmlspecialchars($cwid, ENT_QUOTES) ?>')" title="添加到侧栏"><i class="ri-add-circle-line"></i></span>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                                <div style="margin-top:12px;">
                                    <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" onclick="wgAddCustom()"><i class="ri-add-line"></i> 新建自定义组件</button>
                                </div>
                            </div>
                            <div>
                                <div class="group-label">已启用组件 <span style="font-weight:normal;font-size:12px;color:#999;">(按顺序显示在侧栏)</span></div>
                                <div id="wg-active-list">
                                    <?php if (empty($widgetsActive)): ?>
                                    <div class="blog-nav-empty" id="wg-empty-tip">暂未启用任何组件，从左侧添加。</div>
                                    <?php else: ?>
                                    <?php foreach ($widgetsActive as $aw):
                                        $awName = '';
                                        if (isset($sysWidgetDefs[$aw])) {
                                            $awName = $sysWidgetDefs[$aw]['name'];
                                            if (!empty($wgCustomTitles[$aw]) && $wgCustomTitles[$aw] !== $awName) $awName .= ' (' . $wgCustomTitles[$aw] . ')';
                                        } elseif (isset($customWidgets[$aw])) {
                                            $awName = $customWidgets[$aw]['title'] ?: $aw;
                                        } else {
                                            $awName = $aw;
                                        }
                                    ?>
                                    <div class="wg-active-item" data-wid="<?= htmlspecialchars($aw) ?>" draggable="false">
                                        <span class="wg-drag" title="拖拽排序">⠿</span>
                                        <span class="wg-lbl"><?= htmlspecialchars($awName) ?></span>
                                        <span class="wg-cfg-btn" onclick="wgConfig('<?= htmlspecialchars($aw, ENT_QUOTES) ?>')" title="配置"><i class="ri-settings-3-line"></i></span>
                                        <span class="wg-rm-btn" onclick="wgRemoveActive(this)" title="移除"><i class="ri-close-circle-line"></i></span>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <div style="margin-top:12px;">
                                    <button type="button" class="layui-btn layui-btn-sm layui-btn-danger" onclick="wgReset()"><i class="ri-refresh-line"></i> 重置默认</button>
                                </div>
                            </div>
                        </div>
                    </div></div>

                    <div class="layui-tab-item"><div class="tab-inner">
                        <div class="setting-status-box">管理博客侧栏显示的友情链接。未添加链接时，前台默认展示“DCSHOP多财商城系统”。可添加、编辑、删除自定义链接，设置名称、链接地址、图标和描述。</div>
                        <div class="group-label">友情链接</div>
                        <div style="margin-bottom:12px;display:flex;gap:8px;">
                            <button type="button" class="layui-btn layui-btn-sm" onclick="blogLinkAdd()"><i class="ri-add-line"></i> 添加链接</button>
                            <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" onclick="blogLinkLoad()"><i class="ri-refresh-line"></i> 刷新</button>
                        </div>
                        <div id="blog-link-list">
                            <?php if (empty($blogLinks)): ?>
                                <?php $blogLinks = [blogDefaultFriendLink()]; ?>
                            <?php endif; ?>
                            <?php foreach ($blogLinks as $li => $lk): ?>
                            <div class="link-card" data-id="<?= (int)$lk['id'] ?>">
                                <?php if (!empty($lk['icon'])): ?>
                                <img class="link-icon" src="<?= htmlspecialchars($lk['icon'], ENT_QUOTES) ?>" onerror="this.style.display='none'">
                                <?php else: ?>
                                <div class="link-icon" style="display:flex;align-items:center;justify-content:center;"><i class="ri-link" style="font-size:18px;color:#bbb;"></i></div>
                                <?php endif; ?>
                                <div class="link-info">
                                    <div class="link-name"><?= htmlspecialchars($lk['sitename']) ?></div>
                                    <div class="link-url"><?= htmlspecialchars($lk['siteurl']) ?></div>
                                </div>
                                <div class="link-actions">
                                    <?php if (!empty($lk['_is_default'])): ?>
                                        <span class="link-default-badge">默认展示</span>
                                    <?php else: ?>
                                        <button type="button" class="layui-btn layui-btn-xs" onclick="blogLinkEdit(<?= (int)$lk['id'] ?>)">编辑</button>
                                        <button type="button" class="layui-btn layui-btn-xs layui-btn-danger" onclick="blogLinkDel(<?= (int)$lk['id'] ?>)">删除</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div></div>

                </div>
            </div>
        </div>
        <form id="blog-save-form" class="layui-form">
        <div id="form-btn">
            <div class="layui-input-block" style="margin:0 auto;">
                <button type="submit" class="layui-btn" lay-submit lay-filter="submit">保存配置</button>
            </div>
        </div>
        </form>

        <script>
        layui.use(function(){
            var $ = layui.$, form = layui.form, layer = layui.layer, element = layui.element, upload = layui.upload;
            var blogNavbarActionBase = '<?= addslashes($blogNavbarActionBase) ?>';
            var blogNavQuickSources = <?= json_encode($blogNavQuickSources, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
            var commonIcons = ['ri-home-line','ri-apps-line','ri-menu-line','ri-fire-line','ri-star-line','ri-heart-line','ri-article-line','ri-book-open-line','ri-newspaper-line','ri-file-text-line','ri-pages-line','ri-folder-2-line','ri-price-tag-3-line','ri-message-3-line','ri-customer-service-line','ri-user-line','ri-team-line','ri-mail-line','ri-phone-line','ri-global-line','ri-links-line','ri-shopping-cart-line','ri-gift-line','ri-vip-crown-line','ri-flashlight-line','ri-rocket-line','ri-image-line','ri-palette-line','ri-service-line','ri-question-line'];

            function refreshLogoPreview(url) {
                $('#blog_logo_preview').html(url ? '<img src="' + url.replace(/"/g, '&quot;') + '" alt="logo">' : '<i class="ri-image-line" style="font-size:22px;color:#bbb;"></i>');
            }
            function closeParentPopup(msg) {
                try {
                    var frameIndex = parent.layer.getFrameIndex(window.name);
                    if (frameIndex !== undefined && frameIndex !== null && frameIndex !== '') {
                        parent.layer.close(frameIndex);
                    }
                    parent.layer.msg(msg || '已保存配置');
                    if (window.parent.table) window.parent.table.reload();
                } catch(e) {
                    layer.msg(msg || '已保存配置');
                }
            }
            function saveSetting($form, successText) {
                $.ajax({
                    url: $form.attr('action'), type: 'POST', data: $form.serialize(), dataType: 'json',
                    success: function(res) {
                        if (res.code !== 0) return layer.msg(res.msg || '保存失败');
                        closeParentPopup(successText || '已保存配置');
                    },
                    error: function(xhr) {
                        var msg = '请求失败';
                        try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e){}
                        layer.msg(msg);
                    }
                });
            }
            // Fixed save bar: tab-based saving
            var _tabSaveMap = {
                0: function(){ saveSetting($('#blog-basic-form'), '博客基础信息已保存'); },
                1: function(){ saveSetting($('#blog-banner-form'), '首页轮播配置已保存'); },
                2: function(){ saveSetting($('#blog-list-form'), '列表设置已保存'); },
                3: function(){ saveSetting($('#blog-detail-form'), '详情页设置已保存'); },
                4: function(){ saveSetting($('#blog-sidebar-form'), '侧栏设置已保存'); },
                5: function(){ saveSetting($('#blog-footer-form'), '页脚设置已保存'); },
                6: function(){ saveBlogNavList(); },
                7: function(){ wgSaveActive(); }
            };
            var _currentBlogTab = 0;
            element.on('tab(setting-tab)', function(data) {
                _currentBlogTab = data.index;
                if (_tabSaveMap[_currentBlogTab]) {
                    $('#form-btn').show();
                } else {
                    $('#form-btn').hide();
                }
            });
            form.on('submit(submit)', function() {
                if (_tabSaveMap[_currentBlogTab]) _tabSaveMap[_currentBlogTab]();
                return false;
            });

            function _esc(str) {
                return String(str == null ? '' : str).replace(/[&<>"']/g, function(s) {
                    return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s];
                });
            }
            function uploadUrlFromRes(res) {
                if (!res) return '';
                if (typeof res.data === 'string') return res.data;
                if (res.data && typeof res.data === 'object') {
                    return res.data.url || res.data.src || res.data.path || res.data.file_path || res.data.location || '';
                }
                return res.url || res.src || res.path || res.file_path || res.location || '';
            }
            function updateBlogBannerShowState(forceState) {
                var isEnabled = typeof forceState === 'boolean' ? forceState : $('input[name="blog_banner_show"]').prop('checked');
                $('#blog_banner_show_panel').toggleClass('is-disabled', !isEnabled);
                $('#blog_banner_show_status')
                    .toggleClass('is-off', !isEnabled)
                    .html(isEnabled
                        ? '当前已开启博客首页轮播：下方轮播图列表、尺寸与动画设置会直接在前台生效。'
                        : '当前未开启博客首页轮播：下方轮播设置暂不在前台显示，但可以先配置，后续开启后立即生效。');
            }
            function updateBlogBnNumbers() {
                $('#blog-bn-list .bn-config-item').each(function(i){ $(this).find('.bn-num').text(i + 1); });
            }
            function blogBnItemHtml(n, item) {
                item = item || {};
                var newtab = (item.newtab || 'y') === 'n' ? 'n' : 'y';
                var enabled = (item.enabled || 'y') === 'n' ? 'n' : 'y';
                return '<div class="bn-config-item">'
                    + '<span class="bn-drag" title="拖拽排序">⠿</span>'
                    + '<span class="bn-num">'+n+'</span>'
                    + '<div class="bn-inputs">'
                    + '<div class="bn-img-cell">'
                    + '<div class="bn-img-preview" style="background-image:url(\''+_esc(item.img || '')+'\')"></div>'
                    + '<input type="text" name="blog_bn_img[]" class="bn-img-input layui-input" value="'+_esc(item.img || '')+'" placeholder="图片地址（上传后自动填入）">'
                    + '<button type="button" class="layui-btn layui-btn-xs layui-btn-warm bn-upload-btn"><i class="ri-upload-line"></i> 上传</button>'
                    + '</div>'
                    + '<input type="text" name="blog_bn_title[]" value="'+_esc(item.title || '')+'" placeholder="图片标题/说明（选填）" class="layui-input bn-title">'
                    + '<div class="bn-url-wrap">'
                    + '<input type="text" name="blog_bn_url[]" value="'+_esc(item.url || '')+'" placeholder="点击跳转链接（选填）" class="layui-input bn-url">'
                    + '<button type="button" class="layui-btn layui-btn-primary bn-nav-picker-btn" onclick="openBlogNavQuickPicker(this)"><i class="ri-compasses-2-line"></i> 快捷选择</button>'
                    + '</div>'
                    + '<div class="bn-check-wrap"><input type="checkbox" lay-skin="primary" lay-filter="blog_bn_enabled_dummy" title="启用" '+(enabled === 'y' ? 'checked' : '')+'><input type="hidden" name="blog_bn_enabled[]" value="'+enabled+'" class="bn-enabled-val"></div>'
                    + '<div class="bn-check-wrap"><input type="checkbox" lay-skin="primary" lay-filter="blog_bn_newtab_dummy" title="新窗口" '+(newtab === 'y' ? 'checked' : '')+'><input type="hidden" name="blog_bn_newtab[]" value="'+newtab+'" class="bn-newtab-val"></div>'
                    + '<span class="bn-del" onclick="removeBlogBn(this)" title="删除"><i class="layui-icon layui-icon-delete"></i></span>'
                    + '</div></div>';
            }
            window.addBlogBn = function() {
                var n = $('#blog-bn-list .bn-config-item').length + 1;
                $('#blog-bn-list').append(blogBnItemHtml(n, {}));
                bindBlogBnUpload($('#blog-bn-list .bn-config-item').last().find('.bn-upload-btn')[0]);
                form.render('checkbox');
            };
            window.removeBlogBn = function(el) {
                $(el).closest('.bn-config-item').remove();
                updateBlogBnNumbers();
            };
            function bindBlogBnUpload(btn) {
                if (!btn) return;
                upload.render({
                    elem: btn,
                    url: 'article.php?action=upload_cover',
                    field: 'image',
                    accept: 'images',
                    exts: 'png|gif|jpg|jpeg|webp',
                    done: function(res) {
                        if (res.code == 0 || res.code == 200) {
                            var url = uploadUrlFromRes(res);
                            if (!url) {
                                layer.msg('上传成功但未返回图片地址');
                                return;
                            }
                            var $item = $(btn).closest('.bn-config-item');
                            $item.find('.bn-img-input').val(url);
                            $item.find('.bn-img-preview').css('background-image', url ? 'url(' + url.replace(/'/g, "\\'") + ')' : 'none');
                            layer.msg('上传成功');
                        } else {
                            layer.msg(res.msg || '上传失败');
                        }
                    },
                    error: function(){ layer.msg('上传失败'); }
                });
            }
            $('#blog-bn-list .bn-upload-btn').each(function(){ bindBlogBnUpload(this); });
            $(document).on('input change', '#blog-bn-list .bn-img-input', function(){
                var url = $.trim($(this).val() || '');
                $(this).closest('.bn-config-item').find('.bn-img-preview').css('background-image', url ? 'url(' + url.replace(/'/g, "\\'") + ')' : 'none');
            });
            form.on('switch(blog_banner_show_switch)', function(obj){
                updateBlogBannerShowState(obj.elem.checked);
            });
            form.on('checkbox(blog_bn_newtab_dummy)', function(obj){
                $(obj.elem).closest('.bn-check-wrap').find('.bn-newtab-val').val(obj.elem.checked ? 'y' : 'n');
            });
            form.on('checkbox(blog_bn_enabled_dummy)', function(obj){
                $(obj.elem).closest('.bn-check-wrap').find('.bn-enabled-val').val(obj.elem.checked ? 'y' : 'n');
            });
            function blogNavTypeName(type) {
                type = parseInt(type || 0, 10);
                if (type === 1 || type === 7) return '系统';
                if (type === 6) return '文章分类';
                if (type === 5) return '页面';
                return '自定义';
            }
            function blogNavItemHtml(n, nav) {
                nav = nav || {};
                var id = parseInt(nav.id || 0, 10);
                var type = parseInt(nav.type || 0, 10);
                var isCustom = type === 0;
                var icon = $.trim(nav.naviicon || '');
                var iconCls = icon ? _esc(icon) : 'ri-add-line ci-ri-ph';
                var hasIconCls = icon ? ' ci-has-icon' : '';
                var url = isCustom ? (nav.url || '') : (nav.resolved_url || nav.url || '');
                var readonly = isCustom ? '' : ' readonly';
                var pickerCls = isCustom ? '' : ' layui-btn-disabled';
                var pickerAttr = isCustom ? '' : ' disabled';
                var showChecked = (nav.hide || 'n') === 'n' ? ' checked' : '';
                var hideVal = (nav.hide || 'n') === 'y' ? 'y' : 'n';
                var newtabChecked = (nav.newtab || 'n') === 'y' ? ' checked' : '';
                var newtabVal = (nav.newtab || 'n') === 'y' ? 'y' : 'n';
                var typeName = nav.type_name || blogNavTypeName(type);
                return '<div class="ci-config-item blog-nav-config-item" data-id="'+id+'" data-type="'+type+'">'
                    + '<span class="ci-drag" title="拖拽排序">⠿</span>'
                    + '<span class="ci-num">'+n+'</span>'
                    + '<div class="ci-inputs">'
                    + '<input type="hidden" name="nav_id[]" value="'+id+'" class="nav-id-val">'
                    + '<input type="hidden" name="nav_pid[]" value="'+_esc(nav.pid || 0)+'" class="nav-pid-val">'
                    + '<input type="hidden" name="nav_hide[]" value="'+hideVal+'" class="nav-hide-val">'
                    + '<div class="ci-ri-wrap"><button type="button" class="ci-ri-btn'+hasIconCls+'" onclick="ciPickBlogNavRi(this)" title="点击选择 Remix 图标"><i class="'+iconCls+'"></i></button><input type="hidden" name="nav_ri[]" value="'+_esc(icon)+'" class="ci-ri-val"></div>'
                    + '<input type="text" name="nav_name[]" value="'+_esc(nav.naviname || '')+'" placeholder="导航名称" class="layui-input ci-name" style="min-width:100px;">'
                    + '<div class="ci-url-wrap"><input type="text" name="nav_url[]" value="'+_esc(url)+'" placeholder="跳转链接（支持手填或右侧快捷选择）" class="layui-input ci-url"'+readonly+'><button type="button" class="layui-btn layui-btn-primary ci-nav-picker-btn'+pickerCls+'" onclick="openBlogNavQuickPicker(this)"'+pickerAttr+'><i class="ri-compasses-2-line"></i> 快捷选择</button></div>'
                    + '<span class="blog-nav-type-badge">'+_esc(typeName)+'</span>'
                    + '<div class="blog-nav-check-wrap"><input type="checkbox" lay-skin="primary" lay-filter="blog_nav_show_dummy" title="显示"'+showChecked+'></div>'
                    + '<div class="blog-nav-check-wrap"><input type="checkbox" lay-skin="primary" lay-filter="blog_nav_newtab_dummy" title="新窗口"'+newtabChecked+'><input type="hidden" name="nav_newtab[]" value="'+newtabVal+'" class="nav-newtab-val"></div>'
                    + '<span class="ci-del" onclick="removeBlogNav(this)" title="删除"><i class="layui-icon layui-icon-delete"></i></span>'
                    + '</div></div>';
            }
            function renderBlogNavList(items) {
                var html = '';
                if (!items || !items.length) {
                    html = '<div class="blog-nav-empty">暂无导航，点击“新增导航”创建。</div>';
                } else {
                    $.each(items, function(i, item){ html += blogNavItemHtml(i + 1, item); });
                }
                $('#blog-nav-list').html(html);
                form.render('checkbox');
            }
            function updateBlogNavNumbers() {
                $('#blog-nav-list .ci-config-item').each(function(i){ $(this).find('.ci-num').text(i + 1); });
            }
            window.addBlogNav = function(name, url, icon, newtab) {
                $('#blog-nav-list .blog-nav-empty').remove();
                var n = $('#blog-nav-list .ci-config-item').length + 1;
                $('#blog-nav-list').append(blogNavItemHtml(n, {
                    id: 0,
                    naviname: name || '',
                    url: url || '',
                    naviicon: icon || '',
                    newtab: newtab || 'n',
                    hide: 'n',
                    pid: 0,
                    type: 0,
                    type_name: '自定义'
                }));
                form.render('checkbox');
            };
            window.removeBlogNav = function(el) {
                $(el).closest('.ci-config-item').remove();
                updateBlogNavNumbers();
                if (!$('#blog-nav-list .ci-config-item').length) {
                    $('#blog-nav-list').html('<div class="blog-nav-empty">暂无导航，点击“新增导航”创建。</div>');
                }
            };
            window.clearBlogNavList = function() {
                if (!$('#blog-nav-list .ci-config-item').length) { layer.msg('列表已空'); return; }
                layer.confirm('确定清空当前导航列表吗？点击底部“保存配置”后将同步删除所有博客导航。', {btn:['确认清空','取消'], icon:3, title:'温馨提示'}, function(index){
                    layer.close(index);
                    $('#blog-nav-list').html('<div class="blog-nav-empty">暂无导航，点击“新增导航”创建。</div>');
                });
            };
            window.loadBlogNavList = function() {
                var loadIndex = layer.load(2);
                $.ajax({
                    url: blogNavbarActionBase + 'list',
                    type: 'GET',
                    dataType: 'json',
                    success: function(res) {
                        if (res.code !== 0) return layer.msg(res.msg || '读取失败');
                        renderBlogNavList(res.data || []);
                    },
                    error: function(xhr) { layer.msg(xhr.responseJSON ? xhr.responseJSON.msg : '读取失败'); },
                    complete: function() { layer.close(loadIndex); }
                });
            };
            window.saveBlogNavList = function() {
                var $items = $('#blog-nav-list .ci-config-item');
                var doSave = function() {
                    var loadIndex = layer.load(2);
                    $.ajax({
                        url: blogNavbarActionBase + 'save_list',
                        type: 'POST',
                        data: $('#blog-nav-list-form').serialize(),
                        dataType: 'json',
                        success: function(res) {
                            if (res.code !== 0) return layer.msg(res.msg || '保存失败');
                            closeParentPopup('导航管理已保存');
                        },
                        error: function(xhr) {
                            var msg = '保存失败';
                            try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e){}
                            layer.msg(msg);
                        },
                        complete: function() { layer.close(loadIndex); }
                    });
                };
                if (!$items.length) {
                    layer.confirm('当前列表为空，保存后将清空所有博客导航，确定继续？', {btn:['确认保存','取消'], icon:3, title:'温馨提示'}, function(index){
                        layer.close(index);
                        doSave();
                    });
                } else {
                    doSave();
                }
            };
            form.on('checkbox(blog_nav_newtab_dummy)', function(obj){
                $(obj.elem).closest('.blog-nav-check-wrap').find('.nav-newtab-val').val(obj.elem.checked ? 'y' : 'n');
            });
            form.on('checkbox(blog_nav_show_dummy)', function(obj){
                $(obj.elem).closest('.blog-nav-config-item').find('.nav-hide-val').val(obj.elem.checked ? 'n' : 'y');
            });

            var _ciDragSrc = null;
            $(document).on('mousedown', '#blog-nav-list .ci-drag', function() {
                $(this).closest('.ci-config-item').attr('draggable', 'true');
            });
            $(document).on('mouseup', '#blog-nav-list .ci-config-item', function() {
                $(this).attr('draggable', 'false');
            });
            $(document).on('dragstart', '#blog-nav-list .ci-config-item', function(e) {
                _ciDragSrc = this;
                $(this).addClass('ci-dragging');
                e.originalEvent.dataTransfer.effectAllowed = 'move';
                e.originalEvent.dataTransfer.setData('text/plain', '');
            });
            $(document).on('dragend', '#blog-nav-list .ci-config-item', function() {
                $(this).attr('draggable', 'false').removeClass('ci-dragging');
                $('#blog-nav-list .ci-config-item').removeClass('ci-drag-over');
            });
            $(document).on('dragover', '#blog-nav-list .ci-config-item', function(e) {
                e.preventDefault();
                e.originalEvent.dataTransfer.dropEffect = 'move';
                if (this !== _ciDragSrc) {
                    $('#blog-nav-list .ci-config-item').removeClass('ci-drag-over');
                    $(this).addClass('ci-drag-over');
                }
                return false;
            });
            $(document).on('dragleave', '#blog-nav-list .ci-config-item', function() {
                $(this).removeClass('ci-drag-over');
            });
            $(document).on('drop', '#blog-nav-list .ci-config-item', function(e) {
                e.preventDefault(); e.stopPropagation();
                if (_ciDragSrc && this !== _ciDragSrc) {
                    var $drag = $(_ciDragSrc), $target = $(this);
                    if ($drag.index() < $target.index()) $drag.insertAfter($target);
                    else $drag.insertBefore($target);
                    updateBlogNavNumbers();
                }
                $('#blog-nav-list .ci-config-item').removeClass('ci-drag-over');
                return false;
            });

            var _bnDragSrc = null;
            $(document).on('mousedown', '#blog-bn-list .bn-drag', function() {
                $(this).closest('.bn-config-item').attr('draggable', 'true');
            });
            $(document).on('mouseup', '#blog-bn-list .bn-config-item', function() {
                $(this).attr('draggable', 'false');
            });
            $(document).on('dragstart', '#blog-bn-list .bn-config-item', function(e) {
                _bnDragSrc = this;
                $(this).addClass('bn-dragging');
                e.originalEvent.dataTransfer.effectAllowed = 'move';
                e.originalEvent.dataTransfer.setData('text/plain', '');
            });
            $(document).on('dragend', '#blog-bn-list .bn-config-item', function() {
                $(this).attr('draggable', 'false').removeClass('bn-dragging');
                $('#blog-bn-list .bn-config-item').removeClass('bn-drag-over');
            });
            $(document).on('dragover', '#blog-bn-list .bn-config-item', function(e) {
                e.preventDefault();
                e.originalEvent.dataTransfer.dropEffect = 'move';
                if (this !== _bnDragSrc) {
                    $('#blog-bn-list .bn-config-item').removeClass('bn-drag-over');
                    $(this).addClass('bn-drag-over');
                }
                return false;
            });
            $(document).on('dragleave', '#blog-bn-list .bn-config-item', function() {
                $(this).removeClass('bn-drag-over');
            });
            $(document).on('drop', '#blog-bn-list .bn-config-item', function(e) {
                e.preventDefault(); e.stopPropagation();
                if (_bnDragSrc && this !== _bnDragSrc) {
                    var $drag = $(_bnDragSrc), $target = $(this);
                    if ($drag.index() < $target.index()) $drag.insertAfter($target);
                    else $drag.insertBefore($target);
                    updateBlogBnNumbers();
                }
                $('#blog-bn-list .bn-config-item').removeClass('bn-drag-over');
                return false;
            });

            upload.render({
                elem: '#btn-upload-logo',
                url: 'article.php?action=upload_cover',
                field: 'image',
                accept: 'images',
                done: function(res) {
                    if (res.code == 0 || res.code == 200) {
                        var url = uploadUrlFromRes(res);
                        if (!url) {
                            layer.msg('上传成功但未返回图片地址');
                            return;
                        }
                        $('#blog_logo').val(url);
                        refreshLogoPreview(url);
                        layer.msg('上传成功');
                    } else {
                        layer.msg(res.msg || '上传失败');
                    }
                },
                error: function() { layer.msg('上传失败'); }
            });

            $('#blog_logo').on('input change', function(){ refreshLogoPreview($(this).val()); });
            $('#btn-clear-logo').on('click', function(){ $('#blog_logo').val(''); refreshLogoPreview(''); });


            function openIconPicker(callback) {
                var html = '<link rel="stylesheet" href="<?= DC_URL ?>admin/views/remixicon/remixicon.css"><div style="padding:20px;"><div style="display:grid;grid-template-columns:repeat(6,1fr);gap:10px;max-height:400px;overflow-y:auto;">';
                commonIcons.forEach(function(icon){
                    var iconName = icon.replace('ri-', '').replace('-line', '');
                    html += '<div class="icon-select-item" data-icon="' + icon + '" style="display:flex;flex-direction:column;align-items:center;padding:10px;cursor:pointer;border:1px solid #eee;border-radius:4px;transition:all .2s;"><i class="' + icon + '" style="font-size:24px;margin-bottom:5px;"></i><span style="font-size:10px;text-align:center;word-break:break-all;">' + iconName + '</span></div>';
                });
                html += '</div></div>';
                layer.open({
                    type: 1, title: '选择图标', area: window.innerWidth < 768 ? ['96%', '80%'] : ['600px', '500px'], content: html,
                    success: function(layero, index) {
                        $(layero).find('.icon-select-item').on('click', function(){
                            callback($(this).attr('data-icon'));
                            layer.close(index);
                        });
                    }
                });
            }
            window.ciPickBlogNavRi = function(btn) {
                var $item = $(btn).closest('.ci-ri-wrap');
                openIconPicker(function(icon){
                    $item.find('.ci-ri-val').val(icon);
                    $item.find('.ci-ri-btn').addClass('ci-has-icon').find('i').attr('class', icon);
                });
            };

            var _blogNavQuickTarget = null;
            var _blogNavQuickState = { type: 'preset', keyword: '' };
            function blogNavQuickTabs() {
                return [
                    { key: 'preset', text: '常用链接' },
                    { key: 'sort', text: '文章分类' },
                    { key: 'page', text: '页面' }
                ];
            }
            function blogNavQuickPanelHtml() {
                var html = '<div class="nav-quick-panel">';
                html += '<div class="nav-quick-tip"><i class="ri-lightbulb-flash-line"></i><span>点击下方项目后，会自动回填当前这一行的导航名称和跳转链接，填完后仍可继续修改。</span></div>';
                html += '<div class="nav-quick-tabs">';
                $.each(blogNavQuickTabs(), function(_, tab) {
                    html += '<span class="nav-quick-tab' + (tab.key === _blogNavQuickState.type ? ' active' : '') + '" data-type="' + tab.key + '">' + tab.text + '</span>';
                });
                html += '</div>';
                html += '<div class="nav-quick-search"><i class="ri-search-line"></i><input type="text" class="layui-input nav-quick-keyword" placeholder="输入名称或链接关键词筛选"></div>';
                html += '<div class="nav-quick-list"></div>';
                html += '</div>';
                return html;
            }
            function renderBlogNavQuickList(layero) {
                var items = blogNavQuickSources[_blogNavQuickState.type] || [];
                var keyword = $.trim(_blogNavQuickState.keyword || '').toLowerCase();
                if (keyword) {
                    items = $.grep(items, function(item) {
                        var haystack = ((item.name || '') + ' ' + (item.label || '') + ' ' + (item.url || '')).toLowerCase();
                        return haystack.indexOf(keyword) !== -1;
                    });
                }
                var html = '';
                if (!items.length) {
                    html = '<div class="nav-quick-empty"><i class="ri-inbox-line"></i><span>当前没有可选项目</span></div>';
                } else {
                    $.each(items, function(_, item) {
                        html += '<div class="nav-quick-item" data-name="' + _esc(item.name || '') + '" data-url="' + _esc(item.url || '') + '">';
                        html += '<div class="nav-quick-item-title">' + _esc(item.label || item.name || '') + '</div>';
                        html += '<div class="nav-quick-item-meta"><i class="ri-link"></i>' + _esc(item.url || '') + '</div>';
                        html += '<i class="ri-check-line nav-quick-item-icon"></i>';
                        html += '</div>';
                    });
                }
                $(layero).find('.nav-quick-tab').removeClass('active').filter('[data-type="' + _blogNavQuickState.type + '"]').addClass('active');
                $(layero).find('.nav-quick-list').html(html);
            }
            window.openBlogNavQuickPicker = function(btn) {
                if ($(btn).is(':disabled') || $(btn).hasClass('layui-btn-disabled')) return;
                _blogNavQuickTarget = $(btn).closest('.ci-config-item, .bn-config-item');
                _blogNavQuickState = { type: 'preset', keyword: '' };
                layer.open({
                    type: 1,
                    title: '快捷选择导航链接',
                    area: window.innerWidth < 768 ? ['96%', '80%'] : ['720px', '560px'],
                    content: blogNavQuickPanelHtml(),
                    success: function(layero, index) {
                        var $layer = $(layero);
                        renderBlogNavQuickList(layero);
                        $layer.find('.nav-quick-keyword').on('input', function() {
                            _blogNavQuickState.keyword = $(this).val() || '';
                            renderBlogNavQuickList(layero);
                        });
                        $layer.on('click', '.nav-quick-tab', function() {
                            _blogNavQuickState.type = $(this).attr('data-type') || 'preset';
                            _blogNavQuickState.keyword = '';
                            $layer.find('.nav-quick-keyword').val('');
                            renderBlogNavQuickList(layero);
                        });
                        $layer.on('click', '.nav-quick-item', function() {
                            if (_blogNavQuickTarget && _blogNavQuickTarget.length) {
                                if (_blogNavQuickTarget.hasClass('bn-config-item')) {
                                    _blogNavQuickTarget.find('.bn-url').val($(this).attr('data-url') || '');
                                } else {
                                    _blogNavQuickTarget.find('.ci-name').val($(this).attr('data-name') || '');
                                    _blogNavQuickTarget.find('.ci-url').val($(this).attr('data-url') || '');
                                }
                            }
                            layer.close(index);
                        });
                    },
                    end: function() { _blogNavQuickTarget = null; }
                });
            };

            // ===== Widget Management =====
            var wgSysNames = <?= json_encode(array_map(function($d){ return $d['name']; }, $sysWidgetDefs), JSON_UNESCAPED_UNICODE) ?>;
            var wgCustomTitles = <?= json_encode($wgCustomTitles, JSON_UNESCAPED_UNICODE) ?>;
            var wgOptions = <?= json_encode($wgOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
            var wgToken = '<?= LoginAuth::genToken() ?>';
            function wgGetName(wid) {
                if (wgSysNames[wid]) {
                    var n = wgSysNames[wid];
                    if (wgCustomTitles[wid] && wgCustomTitles[wid] !== n) n += ' (' + wgCustomTitles[wid] + ')';
                    return n;
                }
                return wgCustomTitles[wid] || wid;
            }
            function wgActiveItemHtml(wid) {
                return '<div class="wg-active-item" data-wid="'+_esc(wid)+'" draggable="false">'
                    +'<span class="wg-drag" title="拖拽排序">\u2807</span>'
                    +'<span class="wg-lbl">'+_esc(wgGetName(wid))+'</span>'
                    +'<span class="wg-cfg-btn" onclick="wgConfig(\''+_esc(wid)+'\')" title="配置"><i class="ri-settings-3-line"></i></span>'
                    +'<span class="wg-rm-btn" onclick="wgRemoveActive(this)" title="移除"><i class="ri-close-circle-line"></i></span>'
                    +'</div>';
            }
            window.wgAddToActive = function(wid) {
                var exists = false;
                $('#wg-active-list .wg-active-item').each(function(){
                    if ($(this).attr('data-wid') === wid) { exists = true; return false; }
                });
                if (exists) { layer.msg('该组件已在启用列表中，不可重复添加'); return; }
                $('#wg-empty-tip').remove();
                $('#wg-active-list').append(wgActiveItemHtml(wid));
                layer.msg('已添加，记得点击底部"保存配置"');
            };
            window.wgRemoveActive = function(el) {
                $(el).closest('.wg-active-item').remove();
                if (!$('#wg-active-list .wg-active-item').length) {
                    $('#wg-active-list').html('<div class="blog-nav-empty" id="wg-empty-tip">暂未启用任何组件，从左侧添加。</div>');
                }
            };
            window.wgSaveActive = function() {
                var widgets = [];
                $('#wg-active-list .wg-active-item').each(function(){ widgets.push($(this).attr('data-wid')); });
                var postData = {};
                widgets.forEach(function(w, i){ postData['widgets['+i+']'] = w; });
                $.ajax({
                    url: './widgets.php?action=compages', type: 'POST', data: postData, dataType: 'json',
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    success: function(res) { if (res.code === 0) closeParentPopup('组件已保存'); else layer.msg(res.msg || '保存失败'); },
                    error: function() { layer.msg('保存失败'); }
                });
            };
            window.wgReset = function() {
                layer.confirm('确定恢复为默认组件配置？', {btn:['确认','取消'], icon:3, title:'温馨提示'}, function(idx){
                    layer.close(idx);
                    $.ajax({
                        url: './widgets.php?action=reset&token='+encodeURIComponent(wgToken), type: 'GET', dataType: 'json',
                        headers: {'X-Requested-With': 'XMLHttpRequest'},
                        success: function(res) {
                            if (res.code === 0) { layer.msg('已恢复默认'); setTimeout(function(){ location.reload(); }, 600); }
                            else layer.msg(res.msg || '重置失败');
                        },
                        error: function() { layer.msg('重置失败'); }
                    });
                });
            };
            // ===== Helpers for blogger widget (icon picker + link cards) =====
            var BG_ICONS_LIB = [
                { group: '社交账号', icons: [
                    ['ri-github-line','GitHub'],['ri-twitter-x-line','X'],['ri-weibo-line','微博'],
                    ['ri-bilibili-line','哔哩'],['ri-zhihu-line','知乎'],['ri-tiktok-line','抖音'],
                    ['ri-wechat-line','微信'],['ri-qq-line','QQ'],['ri-discord-line','Discord'],
                    ['ri-telegram-line','TG'],['ri-instagram-line','Ins'],['ri-facebook-line','FB'],
                    ['ri-youtube-line','YT'],['ri-whatsapp-line','WA'],['ri-line-line','Line']
                ]},
                { group: '联系方式', icons: [
                    ['ri-mail-line','邮箱'],['ri-message-3-line','消息'],['ri-phone-line','电话'],
                    ['ri-customer-service-line','客服'],['ri-mail-send-line','发件']
                ]},
                { group: '内容相关', icons: [
                    ['ri-book-open-line','书本'],['ri-quill-pen-line','羽笔'],['ri-pencil-line','铅笔'],
                    ['ri-edit-line','编辑'],['ri-file-text-line','文档'],['ri-article-line','文章'],
                    ['ri-newspaper-line','报纸'],['ri-bookmark-line','书签'],['ri-folder-2-line','文件夹'],
                    ['ri-price-tag-3-line','标签']
                ]},
                { group: '链接导航', icons: [
                    ['ri-links-line','链接'],['ri-global-line','地球'],['ri-earth-line','世界'],
                    ['ri-home-line','首页'],['ri-home-heart-line','爱家'],['ri-compass-3-line','指南'],
                    ['ri-map-pin-line','位置'],['ri-rss-line','RSS']
                ]},
                { group: '兴趣表达', icons: [
                    ['ri-heart-line','爱心'],['ri-star-line','星'],['ri-fire-line','火'],
                    ['ri-flashlight-line','闪'],['ri-rocket-line','火箭'],['ri-sparkling-line','闪耀'],
                    ['ri-lightbulb-line','灯泡'],['ri-palette-line','调色'],['ri-emotion-happy-line','微笑'],
                    ['ri-thumb-up-line','点赞']
                ]},
                { group: '工具与其他', icons: [
                    ['ri-user-line','用户'],['ri-team-line','团队'],['ri-vip-crown-line','VIP'],
                    ['ri-shopping-cart-line','购物'],['ri-gift-line','礼物'],['ri-question-line','问号'],
                    ['ri-information-line','信息'],['ri-image-line','图片']
                ]}
            ];
            var BG_LINK_PRESETS = [
                { icon:'ri-github-line', name:'GitHub', url:'https://github.com/' },
                { icon:'ri-mail-line', name:'邮箱', url:'mailto:' },
                { icon:'ri-twitter-x-line', name:'X', url:'https://x.com/' },
                { icon:'ri-weibo-line', name:'微博', url:'https://weibo.com/' },
                { icon:'ri-zhihu-line', name:'知乎', url:'https://www.zhihu.com/people/' },
                { icon:'ri-bilibili-line', name:'B站', url:'https://space.bilibili.com/' },
                { icon:'ri-rss-line', name:'RSS', url:'/feed' }
            ];
            function bgOpenIconPicker(currentIcon, onPick) {
                var html = '<div class="bg-icon-popup"><input type="text" class="bg-icon-popup-search" placeholder="搜索图标名称..."><div class="bg-icon-popup-body">';
                BG_ICONS_LIB.forEach(function(grp){
                    html += '<div class="bg-icon-popup-group">'+_esc(grp.group)+'</div><div class="bg-icon-popup-grid">';
                    grp.icons.forEach(function(it){
                        var code = it[0], lbl = it[1];
                        var meta = (lbl + ' ' + code).toLowerCase();
                        html += '<div class="bg-icon-cell'+(code===currentIcon?' is-active':'')+'" data-icon="'+_esc(code)+'" data-meta="'+_esc(meta)+'" title="'+_esc(code)+'"><i class="'+_esc(code)+'"></i><span class="bg-icon-cell-label">'+_esc(lbl)+'</span></div>';
                    });
                    html += '</div>';
                });
                html += '</div></div>';
                layer.open({
                    type: 1, title: '选择图标',
                    area: window.innerWidth < 768 ? ['96%', '70%'] : ['600px', '70%'],
                    content: html,
                    success: function(layero, index) {
                        var $body = $(layero);
                        $body.on('click', '.bg-icon-cell', function(){
                            if (typeof onPick === 'function') onPick($(this).attr('data-icon'));
                            layer.close(index);
                        });
                        $body.find('.bg-icon-popup-search').on('input', function(){
                            var kw = $.trim($(this).val()).toLowerCase();
                            $body.find('.bg-icon-cell').each(function(){
                                var m = $(this).attr('data-meta') || '';
                                $(this).toggle(!kw || m.indexOf(kw) !== -1);
                            });
                            $body.find('.bg-icon-popup-group').each(function(){
                                var $g = $(this), $grid = $g.next('.bg-icon-popup-grid');
                                var has = $grid.find('.bg-icon-cell:visible').length > 0;
                                $g.toggle(has); $grid.toggle(has);
                            });
                        });
                    }
                });
            }
            function bgParseLinks(raw) {
                var arr = [];
                String(raw||'').split(/\r\n|\r|\n/).forEach(function(line){
                    line = line.replace(/^\s+|\s+$/g, '');
                    if (!line) return;
                    var i1 = line.indexOf('|'); if (i1 < 0) return;
                    var i2 = line.indexOf('|', i1+1); if (i2 < 0) return;
                    var icon = line.substring(0, i1).replace(/^\s+|\s+$/g, '');
                    var name = line.substring(i1+1, i2).replace(/^\s+|\s+$/g, '');
                    var url = line.substring(i2+1).replace(/^\s+|\s+$/g, '');
                    if (!name || !url) return;
                    arr.push({ icon: icon || 'ri-links-line', name: name, url: url });
                });
                return arr;
            }
            function bgSerializeLinks(arr) {
                return (arr||[]).map(function(l){
                    return [(l.icon||'ri-links-line').replace(/[|\r\n]/g,''),
                            (l.name||'').replace(/[|\r\n]/g,''),
                            (l.url||'').replace(/[|\r\n]/g,'')].join('|');
                }).join('\n');
            }
            window.wgConfig = function(wid) {
                var cfgFields = '';
                var bo = wgOptions || {};
                var isChecked = function(name, mode) {
                    if (mode === 'yes') return bo[name] === 'y' ? ' checked' : '';
                    return bo[name] === 'n' ? '' : ' checked';
                };
                var textInput = function(name, label, placeholder) {
                    return '<div class="layui-form-item"><label class="layui-form-label">'+label+'</label><div class="layui-input-block"><input type="text" name="'+name+'" value="'+_esc(bo[name] || '')+'" class="layui-input" placeholder="'+_esc(placeholder || '')+'"></div></div>';
                };
                if (wid === 'newcomm') {
                    cfgFields = '<div class="layui-form-item"><label class="layui-form-label">评论条数</label><div class="layui-input-block"><input type="number" name="index_comnum" value="<?= $wgOptions['index_comnum'] ?>" class="layui-input" min="1" max="6"></div></div>'
                        +'<div class="layui-form-item"><label class="layui-form-label">截取字数</label><div class="layui-input-block"><input type="number" name="comment_subnum" value="<?= $wgOptions['comment_subnum'] ?>" class="layui-input" min="5" max="90"></div></div>';
                } else if (wid === 'blogger') {
                    var btn1Icon = bo.blogger_button1_icon || 'ri-book-open-line';
                    var btn2Icon = bo.blogger_button2_icon || 'ri-home-heart-line';
                    var btn1Text = bo.blogger_button1_text || '文章手札';
                    var btn2Text = bo.blogger_button2_text || '返回首页';
                    cfgFields = '<div class="group-label">昵称与头像</div>'
                        + '<div class="layui-form-item"><label class="layui-form-label">显示昵称</label><div class="layui-input-block"><input type="checkbox" name="blogger_show_nickname" value="y" title="显示昵称" lay-skin="primary"'+isChecked('blogger_show_nickname')+'><div class="layui-form-mid layui-text-em">关闭后个人资料卡只显示组件标题、头像和描述。</div></div></div>'
                        + textInput('blogger_nickname', '昵称文字', '留空使用管理员昵称/博客名称')
                        + '<div class="layui-form-item"><label class="layui-form-label">头像地址</label><div class="layui-input-block"><div style="display:flex;gap:8px;"><input type="text" name="blogger_avatar" value="'+_esc(bo.blogger_avatar || '')+'" class="layui-input" placeholder="留空使用管理员头像" style="flex:1;"><button type="button" class="layui-btn layui-btn-primary wg-blogger-avatar-upload"><i class="ri-upload-line"></i> 上传</button></div><div class="layui-form-mid layui-text-em">支持 http(s) 或站内相对路径图片地址。</div></div></div>'
                        + '<div class="group-label">个人介绍</div>'
                        + '<div class="layui-form-item"><label class="layui-form-label">显示介绍</label><div class="layui-input-block"><input type="checkbox" name="blogger_intro_show" value="y" title="显示个人介绍" lay-skin="primary"'+isChecked('blogger_intro_show')+'><div class="layui-form-mid layui-text-em">关闭后个人资料卡不显示介绍文字。</div></div></div>'
                        + '<div class="layui-form-item"><label class="layui-form-label">介绍内容</label><div class="layui-input-block"><textarea name="blogger_intro_text" class="layui-textarea" style="min-height:90px;" placeholder="留空使用管理员个人描述/博客描述">'+_esc(bo.blogger_intro_text || '')+'</textarea><div class="layui-form-mid layui-text-em">显示在头像和昵称下方，支持换行，不支持 HTML。</div></div></div>'
                        + '<div class="group-label">底部两个按钮</div>'
                        + '<div class="layui-form-mid layui-text-em" style="margin:-6px 0 12px;">右上角小标签为实时预览。关闭"启用"开关后该按钮不显示在前台。</div>'
                        + '<div class="bg-button-pair">'
                        +   '<div class="bg-button-card" data-btn="1">'
                        +     '<div class="bg-button-card-head"><span class="bg-button-card-title">按钮一</span><span class="bg-button-card-preview" data-preview="1"><i class="'+_esc(btn1Icon)+'"></i><span>'+_esc(btn1Text)+'</span></span></div>'
                        +     '<div class="layui-form-item"><label class="layui-form-label">启用</label><div class="layui-input-block"><input type="checkbox" name="blogger_button1_show" value="y" title="启用按钮一" lay-skin="primary" lay-filter="bg-btn-toggle" data-toggle="1"'+isChecked('blogger_button1_show')+'></div></div>'
                        +     '<div class="layui-form-item"><label class="layui-form-label">文字</label><div class="layui-input-block"><input type="text" name="blogger_button1_text" value="'+_esc(bo.blogger_button1_text || '')+'" class="layui-input" placeholder="文章手札" data-field="text" data-btn="1"></div></div>'
                        +     '<div class="layui-form-item"><label class="layui-form-label">图标</label><div class="layui-input-block"><button type="button" class="bg-icon-pick" data-target="blogger_button1_icon" data-preview="1"><i class="bg-icon-pick-preview '+_esc(btn1Icon)+'"></i><span class="bg-icon-pick-label">点击选择</span></button><input type="hidden" name="blogger_button1_icon" value="'+_esc(btn1Icon)+'"></div></div>'
                        +     '<div class="layui-form-item"><label class="layui-form-label">链接</label><div class="layui-input-block"><input type="text" name="blogger_button1_url" value="'+_esc(bo.blogger_button1_url || '')+'" class="layui-input" placeholder="/blog 或 https://..."></div></div>'
                        +     '<div class="layui-form-item"><label class="layui-form-label">新窗口</label><div class="layui-input-block"><input type="checkbox" name="blogger_button1_newtab" value="y" title="新窗口打开" lay-skin="primary"'+isChecked('blogger_button1_newtab', 'yes')+'></div></div>'
                        +   '</div>'
                        +   '<div class="bg-button-card" data-btn="2">'
                        +     '<div class="bg-button-card-head"><span class="bg-button-card-title">按钮二</span><span class="bg-button-card-preview" data-preview="2"><i class="'+_esc(btn2Icon)+'"></i><span>'+_esc(btn2Text)+'</span></span></div>'
                        +     '<div class="layui-form-item"><label class="layui-form-label">启用</label><div class="layui-input-block"><input type="checkbox" name="blogger_button2_show" value="y" title="启用按钮二" lay-skin="primary" lay-filter="bg-btn-toggle" data-toggle="2"'+isChecked('blogger_button2_show')+'></div></div>'
                        +     '<div class="layui-form-item"><label class="layui-form-label">文字</label><div class="layui-input-block"><input type="text" name="blogger_button2_text" value="'+_esc(bo.blogger_button2_text || '')+'" class="layui-input" placeholder="返回首页" data-field="text" data-btn="2"></div></div>'
                        +     '<div class="layui-form-item"><label class="layui-form-label">图标</label><div class="layui-input-block"><button type="button" class="bg-icon-pick" data-target="blogger_button2_icon" data-preview="2"><i class="bg-icon-pick-preview '+_esc(btn2Icon)+'"></i><span class="bg-icon-pick-label">点击选择</span></button><input type="hidden" name="blogger_button2_icon" value="'+_esc(btn2Icon)+'"></div></div>'
                        +     '<div class="layui-form-item"><label class="layui-form-label">链接</label><div class="layui-input-block"><input type="text" name="blogger_button2_url" value="'+_esc(bo.blogger_button2_url || '')+'" class="layui-input" placeholder="/ 或 https://..."></div></div>'
                        +     '<div class="layui-form-item"><label class="layui-form-label">新窗口</label><div class="layui-input-block"><input type="checkbox" name="blogger_button2_newtab" value="y" title="新窗口打开" lay-skin="primary"'+isChecked('blogger_button2_newtab', 'yes')+'></div></div>'
                        +   '</div>'
                        + '</div>'
                        + '<div class="group-label" style="margin-top:18px;">个人资料卡外链</div>'
                        + '<div class="layui-form-mid layui-text-em" style="margin:-6px 0 12px;">显示在头像下方的圆形社交按钮，最多 8 条。点击下方常用预设可一键添加。</div>'
                        + '<div class="bg-link-presets" id="bg-link-presets"></div>'
                        + '<div class="bg-link-list" id="bg-link-list"></div>'
                        + '<input type="hidden" name="blogger_external_links" id="bg-link-raw" value="'+_esc(bo.blogger_external_links || '')+'">';
                } else if (wid === 'twitter') {
                    cfgFields = '<div class="layui-form-item"><label class="layui-form-label">显示条数</label><div class="layui-input-block"><input type="number" name="index_newtwnum" value="<?= $wgOptions['index_newtwnum'] ?>" class="layui-input" min="1" max="5"></div></div>';
                } else if (wid === 'newlog') {
                    cfgFields = '<div class="layui-form-item"><label class="layui-form-label">显示条数</label><div class="layui-input-block"><input type="number" name="index_newlog" value="<?= $wgOptions['index_newlognum'] ?>" class="layui-input" min="1" max="6"></div></div>';
                } else if (wid === 'hotlog') {
                    cfgFields = '<div class="layui-form-item"><label class="layui-form-label">显示条数</label><div class="layui-input-block"><input type="number" name="index_hotlognum" value="<?= $wgOptions['index_hotlognum'] ?>" class="layui-input" min="1" max="6"></div></div>';
                }
                var titleVal = wgCustomTitles[wid] || '';
                var isCustomWg = /^custom_wg_/.test(wid);
                var html = '<div style="padding:20px;">';
                if (!isCustomWg) {
                    html += '<div class="layui-form-item"><label class="layui-form-label">自定义标题</label><div class="layui-input-block"><input type="text" name="title" value="'+_esc(titleVal)+'" class="layui-input" placeholder="留空使用默认名称"></div></div>';
                    html += cfgFields;
                } else {
                    html += '<div class="layui-form-item"><label class="layui-form-label">标题</label><div class="layui-input-block"><input type="text" name="title" class="layui-input" id="wg-cfg-title"></div></div>';
                    html += '<div class="layui-form-item"><label class="layui-form-label">HTML内容</label><div class="layui-input-block"><textarea name="content" class="layui-textarea" id="wg-cfg-content" style="min-height:120px;"></textarea></div></div>';
                }
                html += '</div>';
                var layerArea = wid === 'blogger'
                    ? (window.innerWidth < 768 ? ['96%', '92%'] : ['880px', '86%'])
                    : (window.innerWidth < 768 ? ['96%', 'auto'] : ['500px', 'auto']);
                layer.open({
                    type: 1, title: '组件配置 - ' + _esc(wgGetName(wid)),
                    area: layerArea,
                    content: html, btn: ['保存', '取消'],
                    success: function(layero) {
                        form.render();
                        if (wid === 'blogger') {
                            var avatarUploadElem = $(layero).find('.wg-blogger-avatar-upload')[0];
                            if (avatarUploadElem) {
                                upload.render({
                                    elem: avatarUploadElem,
                                    url: 'article.php?action=upload_cover',
                                    field: 'image',
                                    accept: 'images',
                                    exts: 'png|gif|jpg|jpeg|webp',
                                    done: function(res) {
                                        if (res.code == 0 || res.code == 200) {
                                            var url = uploadUrlFromRes(res);
                                            if (!url) return layer.msg('上传成功但未返回图片地址');
                                            $(layero).find('input[name="blogger_avatar"]').val(url);
                                            layer.msg('上传成功');
                                        } else {
                                            layer.msg(res.msg || '上传失败');
                                        }
                                    },
                                    error: function() { layer.msg('上传失败'); }
                                });
                            }
                            // ===== Icon picker buttons (delegated; works for both button cards and link editor) =====
                            $(layero).on('click', '.bg-icon-pick', function(){
                                var $btn = $(this);
                                var target = $btn.attr('data-target');
                                var previewKey = $btn.attr('data-preview');
                                var $hidden = target ? $(layero).find('input[name="'+target+'"]') : $btn.next('input[type="hidden"]');
                                var currentIcon = $hidden.val() || 'ri-links-line';
                                bgOpenIconPicker(currentIcon, function(newIcon){
                                    $hidden.val(newIcon);
                                    $btn.find('i.bg-icon-pick-preview').attr('class', 'bg-icon-pick-preview '+newIcon);
                                    if (previewKey) {
                                        $(layero).find('.bg-button-card-preview[data-preview="'+previewKey+'"] i').attr('class', newIcon);
                                    }
                                });
                            });
                            // ===== Button text live preview =====
                            $(layero).on('input', 'input[data-field="text"][data-btn]', function(){
                                var b = $(this).attr('data-btn');
                                var fallback = b === '1' ? '文章手札' : '返回首页';
                                $(layero).find('.bg-button-card-preview[data-preview="'+b+'"] span').text($(this).val() || fallback);
                            });
                            // ===== Button card dim on toggle =====
                            function bgRefreshBtnDim() {
                                $(layero).find('.bg-button-card').each(function(){
                                    var b = $(this).attr('data-btn');
                                    var checked = $(layero).find('input[data-toggle="'+b+'"]').prop('checked');
                                    $(this).toggleClass('is-off', !checked);
                                });
                            }
                            bgRefreshBtnDim();
                            form.on('checkbox(bg-btn-toggle)', bgRefreshBtnDim);
                            // ===== External link cards state =====
                            var bgLinkState = bgParseLinks(bo.blogger_external_links || '');
                            function bgRenderLinks() {
                                var $list = $(layero).find('#bg-link-list');
                                if (!bgLinkState.length) {
                                    $list.html('<div class="bg-link-empty">还没有外链。点击上方常用预设，或"+ 自定义"按钮来添加。</div>');
                                    return;
                                }
                                var html = '';
                                bgLinkState.forEach(function(lk, i){
                                    html += '<div class="bg-link-card" data-idx="'+i+'">'
                                        + '<div class="bg-link-card-icon"><i class="'+_esc(lk.icon||'ri-links-line')+'"></i></div>'
                                        + '<div class="bg-link-card-info"><div class="bg-link-card-name">'+_esc(lk.name)+'</div><div class="bg-link-card-url">'+_esc(lk.url)+'</div></div>'
                                        + '<div class="bg-link-card-acts">'
                                        +   (i > 0 ? '<a data-act="up" title="上移"><i class="ri-arrow-up-line"></i></a>' : '')
                                        +   (i < bgLinkState.length - 1 ? '<a data-act="down" title="下移"><i class="ri-arrow-down-line"></i></a>' : '')
                                        +   '<a data-act="edit" title="编辑"><i class="ri-edit-line"></i></a>'
                                        +   '<a data-act="del" class="is-danger" title="删除"><i class="ri-delete-bin-line"></i></a>'
                                        + '</div></div>';
                                });
                                $list.html(html);
                            }
                            function bgSyncLinkRaw() {
                                $(layero).find('#bg-link-raw').val(bgSerializeLinks(bgLinkState));
                            }
                            function bgOpenLinkEditor(initial, idx) {
                                var data = $.extend({ icon:'ri-links-line', name:'', url:'' }, initial || {});
                                var html = '<div style="padding:18px 20px;">'
                                    + '<div class="layui-form-item"><label class="layui-form-label">名称</label><div class="layui-input-block"><input type="text" id="bg-lk-name" class="layui-input" maxlength="20" placeholder="如：GitHub" value="'+_esc(data.name)+'"></div></div>'
                                    + '<div class="layui-form-item"><label class="layui-form-label">链接</label><div class="layui-input-block"><input type="text" id="bg-lk-url" class="layui-input" placeholder="https:// 或 mailto:..." value="'+_esc(data.url)+'"></div></div>'
                                    + '<div class="layui-form-item"><label class="layui-form-label">图标</label><div class="layui-input-block"><button type="button" class="bg-icon-pick" id="bg-lk-icon-btn"><i class="bg-icon-pick-preview '+_esc(data.icon)+'"></i><span class="bg-icon-pick-label">点击选择</span></button><input type="hidden" id="bg-lk-icon" value="'+_esc(data.icon)+'"></div></div>'
                                    + '</div>';
                                layer.open({
                                    type: 1, title: idx == null ? '添加外链' : '编辑外链',
                                    area: window.innerWidth < 768 ? ['96%', 'auto'] : ['460px', 'auto'],
                                    content: html, btn: ['保存', '取消'],
                                    success: function(editorLayero) {
                                        $(editorLayero).find('#bg-lk-icon-btn').on('click', function(){
                                            var curr = $(editorLayero).find('#bg-lk-icon').val();
                                            bgOpenIconPicker(curr, function(newIcon){
                                                $(editorLayero).find('#bg-lk-icon').val(newIcon);
                                                $(editorLayero).find('#bg-lk-icon-btn i.bg-icon-pick-preview').attr('class','bg-icon-pick-preview '+newIcon);
                                            });
                                        });
                                    },
                                    yes: function(editorIdx, editorLayero) {
                                        var name = $.trim($(editorLayero).find('#bg-lk-name').val());
                                        var url = $.trim($(editorLayero).find('#bg-lk-url').val());
                                        var icon = $.trim($(editorLayero).find('#bg-lk-icon').val()) || 'ri-links-line';
                                        if (!name) return layer.msg('请填写外链名称');
                                        if (!url) return layer.msg('请填写链接地址');
                                        if (idx == null) {
                                            if (bgLinkState.length >= 8) return layer.msg('外链最多 8 条');
                                            bgLinkState.push({ icon: icon, name: name, url: url });
                                        } else {
                                            bgLinkState[idx] = { icon: icon, name: name, url: url };
                                        }
                                        bgRenderLinks();
                                        bgSyncLinkRaw();
                                        layer.close(editorIdx);
                                    }
                                });
                            }
                            // Render presets row
                            var presetsHtml = '';
                            BG_LINK_PRESETS.forEach(function(p, i){
                                presetsHtml += '<span class="bg-link-preset" data-preset="'+i+'"><i class="'+_esc(p.icon)+'"></i>'+_esc(p.name)+'</span>';
                            });
                            presetsHtml += '<span class="bg-link-preset is-custom" data-preset="-1"><i class="ri-add-line"></i>自定义</span>';
                            $(layero).find('#bg-link-presets').html(presetsHtml);
                            $(layero).on('click', '#bg-link-presets .bg-link-preset', function(){
                                var i = parseInt($(this).attr('data-preset'), 10);
                                if (i === -1) bgOpenLinkEditor(null, null);
                                else bgOpenLinkEditor(BG_LINK_PRESETS[i], null);
                            });
                            $(layero).on('click', '#bg-link-list .bg-link-card-acts a', function(){
                                var $card = $(this).closest('.bg-link-card');
                                var idx = parseInt($card.attr('data-idx'), 10);
                                var act = $(this).attr('data-act');
                                if (act === 'up' && idx > 0) {
                                    var t1 = bgLinkState[idx-1]; bgLinkState[idx-1] = bgLinkState[idx]; bgLinkState[idx] = t1;
                                } else if (act === 'down' && idx < bgLinkState.length - 1) {
                                    var t2 = bgLinkState[idx+1]; bgLinkState[idx+1] = bgLinkState[idx]; bgLinkState[idx] = t2;
                                } else if (act === 'edit') {
                                    bgOpenLinkEditor(bgLinkState[idx], idx);
                                    return;
                                } else if (act === 'del') {
                                    bgLinkState.splice(idx, 1);
                                } else {
                                    return;
                                }
                                bgRenderLinks();
                                bgSyncLinkRaw();
                            });
                            bgRenderLinks();
                            bgSyncLinkRaw();
                        }
                        if (isCustomWg) {
                            $.ajax({
                                url: './widgets.php?action=get_data', type: 'GET', dataType: 'json',
                                headers: {'X-Requested-With': 'XMLHttpRequest'},
                                success: function(res) {
                                    if (res.code === 0 && res.data.custom_widget && res.data.custom_widget[wid]) {
                                        $(layero).find('#wg-cfg-title').val(res.data.custom_widget[wid].title || '');
                                        $(layero).find('#wg-cfg-content').val(res.data.custom_widget[wid].content || '');
                                    }
                                }
                            });
                        }
                    },
                    yes: function(index, layero) {
                        var postData = {};
                        $(layero).find('input[name],textarea[name],select[name]').each(function(){
                            if (this.type === 'checkbox' && !this.checked) return;
                            postData[$(this).attr('name')] = $(this).val();
                        });
                        if (isCustomWg) postData['custom_wg_id'] = wid;
                        $.ajax({
                            url: './widgets.php?action=setwg&wg=' + encodeURIComponent(wid), type: 'POST', data: postData, dataType: 'json',
                            headers: {'X-Requested-With': 'XMLHttpRequest'},
                            success: function(res) {
                                if (res.code === 0) { layer.close(index); layer.msg('配置已保存'); }
                                else layer.msg(res.msg || '保存失败');
                            },
                            error: function() { layer.msg('保存失败'); }
                        });
                    }
                });
            };
            window.wgAddCustom = function() {
                var html = '<div style="padding:20px;">'
                    +'<div class="layui-form-item"><label class="layui-form-label">组件名</label><div class="layui-input-block"><input type="text" name="new_title" class="layui-input" placeholder="组件标题"></div></div>'
                    +'<div class="layui-form-item"><label class="layui-form-label">HTML内容</label><div class="layui-input-block"><textarea name="new_content" class="layui-textarea" style="min-height:120px;" placeholder="支持HTML代码"></textarea></div></div>'
                    +'</div>';
                layer.open({
                    type: 1, title: '新建自定义组件',
                    area: window.innerWidth < 768 ? ['96%', 'auto'] : ['500px', 'auto'],
                    content: html, btn: ['保存', '取消'],
                    yes: function(index, layero) {
                        var postData = {};
                        $(layero).find('input[name],textarea[name]').each(function(){ postData[$(this).attr('name')] = $(this).val(); });
                        $.ajax({
                            url: './widgets.php?action=setwg&wg=custom_text', type: 'POST', data: postData, dataType: 'json',
                            headers: {'X-Requested-With': 'XMLHttpRequest'},
                            success: function(res) {
                                if (res.code === 0) { layer.close(index); layer.msg('自定义组件已创建'); setTimeout(function(){ location.reload(); }, 600); }
                                else layer.msg(res.msg || '创建失败');
                            },
                            error: function() { layer.msg('创建失败'); }
                        });
                    }
                });
            };
            // Widget drag-and-drop
            var _wgDragSrc = null;
            $(document).on('mousedown', '#wg-active-list .wg-drag', function(){ $(this).closest('.wg-active-item').attr('draggable', 'true'); });
            $(document).on('mouseup', '#wg-active-list .wg-active-item', function(){ $(this).attr('draggable', 'false'); });
            $(document).on('dragstart', '#wg-active-list .wg-active-item', function(e){ _wgDragSrc = this; $(this).css('opacity', '0.4'); e.originalEvent.dataTransfer.effectAllowed = 'move'; e.originalEvent.dataTransfer.setData('text/plain', ''); });
            $(document).on('dragend', '#wg-active-list .wg-active-item', function(){ $(this).attr('draggable', 'false').css('opacity', ''); $('#wg-active-list .wg-active-item').removeClass('ci-drag-over'); });
            $(document).on('dragover', '#wg-active-list .wg-active-item', function(e){ e.preventDefault(); if (this !== _wgDragSrc) { $('#wg-active-list .wg-active-item').removeClass('ci-drag-over'); $(this).addClass('ci-drag-over'); } return false; });
            $(document).on('dragleave', '#wg-active-list .wg-active-item', function(){ $(this).removeClass('ci-drag-over'); });
            $(document).on('drop', '#wg-active-list .wg-active-item', function(e){ e.preventDefault(); e.stopPropagation(); if (_wgDragSrc && this !== _wgDragSrc) { var $d = $(_wgDragSrc), $t = $(this); if ($d.index() < $t.index()) $d.insertAfter($t); else $d.insertBefore($t); } $('#wg-active-list .wg-active-item').removeClass('ci-drag-over'); return false; });

            // ===== Link Management =====
            var linkToken = '<?= LoginAuth::genToken() ?>';
            var defaultBlogFriendLink = <?= json_encode(blogDefaultFriendLink(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
            function renderBlogLinkList(links) {
                if (!links || !links.length) {
                    links = [defaultBlogFriendLink];
                }
                var html = '';
                $.each(links, function(i, lk) {
                    var iconHtml = lk.icon
                        ? '<img class="link-icon" src="'+_esc(lk.icon)+'" onerror="this.style.display=\'none\'">'
                        : '<div class="link-icon" style="display:flex;align-items:center;justify-content:center;"><i class="ri-link" style="font-size:18px;color:#bbb;"></i></div>';
                    html += '<div class="link-card" data-id="'+lk.id+'">'
                        + iconHtml
                        + '<div class="link-info"><div class="link-name">'+_esc(lk.sitename)+'</div><div class="link-url">'+_esc(lk.siteurl)+'</div></div>'
                        + '<div class="link-actions">';
                    if (lk._is_default) {
                        html += '<span class="link-default-badge">默认展示</span>';
                    } else {
                        html += '<button type="button" class="layui-btn layui-btn-xs" onclick="blogLinkEdit('+lk.id+')">编辑</button>'
                            + '<button type="button" class="layui-btn layui-btn-xs layui-btn-danger" onclick="blogLinkDel('+lk.id+')">删除</button>';
                    }
                    html += '</div></div>';
                });
                $('#blog-link-list').html(html);
            }
            window.blogLinkLoad = function() {
                var idx = layer.load(2);
                $.ajax({
                    url: './link.php?action=index', type: 'GET', dataType: 'json',
                    success: function(res) { renderBlogLinkList(res.data || []); },
                    error: function() { layer.msg('加载失败'); },
                    complete: function() { layer.close(idx); }
                });
            };
            function linkFormHtml(info) {
                info = info || {};
                return '<div style="padding:20px;">'
                    +'<div class="layui-form-item"><label class="layui-form-label">名称</label><div class="layui-input-block"><input type="text" name="sitename" class="layui-input" value="'+_esc(info.sitename || '')+'"></div></div>'
                    +'<div class="layui-form-item"><label class="layui-form-label">链接</label><div class="layui-input-block"><input type="text" name="siteurl" class="layui-input" value="'+_esc(info.siteurl || '')+'" placeholder="https://"></div></div>'
                    +'<div class="layui-form-item"><label class="layui-form-label">图标</label><div class="layui-input-block"><input type="text" name="icon" class="layui-input" value="'+_esc(info.icon || '')+'" placeholder="图片URL"></div></div>'
                    +'<div class="layui-form-item"><label class="layui-form-label">描述</label><div class="layui-input-block"><textarea name="description" class="layui-textarea">'+(info.description ? _esc(info.description) : '')+'</textarea></div></div>'
                    +'</div>';
            }
            window.blogLinkAdd = function() {
                layer.open({
                    type: 1, title: '添加友情链接',
                    area: window.innerWidth < 768 ? ['96%', 'auto'] : ['550px', 'auto'],
                    content: linkFormHtml(), btn: ['保存', '取消'],
                    yes: function(index, layero) {
                        var postData = { token: linkToken };
                        $(layero).find('input[name],textarea[name]').each(function(){ postData[$(this).attr('name')] = $(this).val(); });
                        $.ajax({
                            url: './link.php?action=add_ajax', type: 'POST', data: postData, dataType: 'json',
                            success: function(res) {
                                if (res.code === 0 || res.code === 200) { layer.close(index); layer.msg('添加成功'); blogLinkLoad(); }
                                else layer.msg(res.msg || '添加失败');
                            },
                            error: function(xhr) { try { layer.msg(JSON.parse(xhr.responseText).msg); } catch(e) { layer.msg('添加失败'); } }
                        });
                    }
                });
            };
            window.blogLinkEdit = function(id) {
                // Load link data then open edit form
                $.ajax({
                    url: './link.php?action=index', type: 'GET', dataType: 'json',
                    success: function(res) {
                        var info = null;
                        $.each(res.data || [], function(_, lk) { if (lk.id == id) { info = lk; return false; } });
                        if (!info) return layer.msg('链接不存在');
                        layer.open({
                            type: 1, title: '编辑友情链接',
                            area: window.innerWidth < 768 ? ['96%', 'auto'] : ['550px', 'auto'],
                            content: linkFormHtml(info), btn: ['保存', '取消'],
                            yes: function(index, layero) {
                                var postData = { id: id, token: linkToken };
                                $(layero).find('input[name],textarea[name]').each(function(){ postData[$(this).attr('name')] = $(this).val(); });
                                $.ajax({
                                    url: './link.php?action=edit_ajax', type: 'POST', data: postData, dataType: 'json',
                                    success: function(res) {
                                        if (res.code === 0 || res.code === 200) { layer.close(index); layer.msg('编辑成功'); blogLinkLoad(); }
                                        else layer.msg(res.msg || '编辑失败');
                                    },
                                    error: function(xhr) { try { layer.msg(JSON.parse(xhr.responseText).msg); } catch(e) { layer.msg('编辑失败'); } }
                                });
                            }
                        });
                    }
                });
            };
            window.blogLinkDel = function(id) {
                layer.confirm('确定删除该链接？', {btn:['确认','取消'], icon:3, title:'温馨提示'}, function(idx){
                    layer.close(idx);
                    $.ajax({
                        url: './link.php?action=del', type: 'POST', data: { ids: id, token: linkToken }, dataType: 'json',
                        success: function(res) {
                            if (res.code === 0 || res.code === 200) { layer.msg('删除成功'); blogLinkLoad(); }
                            else layer.msg(res.msg || '删除失败');
                        },
                        error: function(xhr) { try { layer.msg(JSON.parse(xhr.responseText).msg); } catch(e) { layer.msg('删除失败'); } }
                    });
                });
            };

            updateBlogBannerShowState();
            updateBlogBnNumbers();
            updateBlogNavNumbers();
            element.render('tab');
            form.render();
        });
        </script>
        <?php
    }
}

if (!function_exists('plugin_setting')) {
    function plugin_setting($tpl = 'default')
    {
        global $CACHE;

        $postOrOption = function ($name, $default = '', $emptyFallback = null) {
            return isset($_POST[$name]) ? Input::postStrVar($name, $default) : blogDefaultTplOption($name, $default, $emptyFallback);
        };

        $blog_site_name = $postOrOption('blog_site_name', blogDefaultTplSiteName(), '');
        $blog_site_desc = $postOrOption('blog_site_desc', blogDefaultTplSiteDesc(), '');
        $blog_logo = $postOrOption('blog_logo', blogDefaultLogoUrl());
        $blog_title_link = $postOrOption('blog_title_link', 'blog');
        if (!in_array($blog_title_link, ['blog', 'home'], true)) {
            $blog_title_link = 'blog';
        }
        $isBannerSubmit = isset($_POST['blog_banner_height'])
            || isset($_POST['blog_banner_mobile_height'])
            || isset($_POST['blog_banner_speed'])
            || isset($_POST['blog_banner_animation'])
            || isset($_POST['blog_bn_img']);

        $blog_banner_show = $isBannerSubmit ? (isset($_POST['blog_banner_show']) ? 'y' : 'n') : (Option::get('blog_banner_show') ?: 'y');
        $blog_banner_height = $isBannerSubmit ? max(120, min(600, (int)($_POST['blog_banner_height'] ?? 350))) : (int)blogDefaultReadIntOption('blog_banner_height', 350, 120, 600);
        $blog_banner_mobile_height = $isBannerSubmit ? max(100, min(420, (int)($_POST['blog_banner_mobile_height'] ?? 200))) : (int)blogDefaultReadIntOption('blog_banner_mobile_height', 200, 100, 420);
        $blog_banner_speed = $isBannerSubmit ? max(500, min(10000, (int)($_POST['blog_banner_speed'] ?? 3000))) : (int)blogDefaultReadIntOption('blog_banner_speed', 3000, 500, 10000);
        $blog_banner_animation = $isBannerSubmit ? trim((string)($_POST['blog_banner_animation'] ?? 'fade')) : (Option::get('blog_banner_animation') ?: 'fade');
        if (!in_array($blog_banner_animation, ['slide', 'fade'], true)) {
            $blog_banner_animation = 'fade';
        }
        $blog_banner_items = blogDefaultGetBannerItems();
        if ($isBannerSubmit) {
            $blog_banner_items = [];
            if (!empty($_POST['blog_bn_img']) && is_array($_POST['blog_bn_img'])) {
                foreach ($_POST['blog_bn_img'] as $i => $img) {
                    $img = trim((string)$img);
                    if ($img === '') {
                        continue;
                    }
                    $blog_banner_items[] = [
                        'img' => $img,
                        'title' => isset($_POST['blog_bn_title'][$i]) ? strip_tags(trim((string)$_POST['blog_bn_title'][$i])) : '',
                        'url' => isset($_POST['blog_bn_url'][$i]) ? trim((string)$_POST['blog_bn_url'][$i]) : '',
                        'enabled' => (isset($_POST['blog_bn_enabled'][$i]) && $_POST['blog_bn_enabled'][$i] === 'n') ? 'n' : 'y',
                        'newtab' => (isset($_POST['blog_bn_newtab'][$i]) && $_POST['blog_bn_newtab'][$i] === 'n') ? 'n' : 'y',
                    ];
                }
            }
        }

        $isListSubmit = isset($_POST['blog_list_layout'])
            || isset($_POST['blog_list_cover_height'])
            || isset($_POST['blog_index_lognum'])
            || isset($_POST['blog_list_summary_length']);
        $blog_list_layout = $isListSubmit ? trim((string)($_POST['blog_list_layout'] ?? 'compact')) : (Option::get('blog_list_layout') ?: 'compact');
        if (!in_array($blog_list_layout, ['default', 'compact', 'simple'], true)) {
            $blog_list_layout = 'compact';
        }
        $blog_index_lognum = $isListSubmit ? max(1, min(20, (int)($_POST['blog_index_lognum'] ?? 10))) : (int)blogDefaultReadIntOption('index_lognum', 10, 1, 20);
        $blog_list_show_cover = $isListSubmit ? (isset($_POST['blog_list_show_cover']) ? 'y' : 'n') : (Option::get('blog_list_show_cover') === 'n' ? 'n' : 'y');
        $blog_list_cover_height = $isListSubmit ? max(120, min(420, (int)($_POST['blog_list_cover_height'] ?? 205))) : (int)blogDefaultReadIntOption('blog_list_cover_height', 205, 120, 420);
        $blog_list_show_summary = $isListSubmit ? (isset($_POST['blog_list_show_summary']) ? 'y' : 'n') : (Option::get('blog_list_show_summary') === 'n' ? 'n' : 'y');
        $blog_list_summary_length = $isListSubmit ? max(60, min(500, (int)($_POST['blog_list_summary_length'] ?? 180))) : (int)blogDefaultReadIntOption('blog_list_summary_length', 180, 60, 500);
        $blog_list_show_category = $isListSubmit ? (isset($_POST['blog_list_show_category']) ? 'y' : 'n') : (Option::get('blog_list_show_category') === 'n' ? 'n' : 'y');
        $blog_list_show_author = $isListSubmit ? (isset($_POST['blog_list_show_author']) ? 'y' : 'n') : (Option::get('blog_list_show_author') === 'n' ? 'n' : 'y');
        $blog_list_show_date = $isListSubmit ? (isset($_POST['blog_list_show_date']) ? 'y' : 'n') : (Option::get('blog_list_show_date') === 'n' ? 'n' : 'y');
        $blog_list_show_tags = $isListSubmit ? (isset($_POST['blog_list_show_tags']) ? 'y' : 'n') : (Option::get('blog_list_show_tags') === 'n' ? 'n' : 'y');
        $blog_list_show_readmore = $isListSubmit ? (isset($_POST['blog_list_show_readmore']) ? 'y' : 'n') : (Option::get('blog_list_show_readmore') === 'n' ? 'n' : 'y');
        $blog_list_show_stats = $isListSubmit ? (isset($_POST['blog_list_show_stats']) ? 'y' : 'n') : (Option::get('blog_list_show_stats') === 'n' ? 'n' : 'y');

        $isDetailSubmit = isset($_POST['blog_detail_submitted']);
        $detailOptions = [
            'blog_detail_show_date',
            'blog_detail_show_reading_time',
            'blog_detail_show_author',
            'blog_detail_show_category',
            'blog_detail_show_views',
            'blog_detail_show_comments_count',
            'blog_detail_show_tags',
            'blog_detail_show_share',
            'blog_detail_show_author_card',
            'blog_detail_show_related',
            'blog_detail_show_neighbor',
            'blog_detail_show_comments',
        ];

        $isSidebarSubmit = isset($_POST['blog_sidebar_submitted']);
        $blog_sidebar_show = $isSidebarSubmit ? (isset($_POST['blog_sidebar_show']) ? 'y' : 'n') : (Option::get('blog_sidebar_show') === 'n' ? 'n' : 'y');
        $blog_sidebar_position = $isSidebarSubmit ? trim((string)($_POST['blog_sidebar_position'] ?? 'right')) : (Option::get('blog_sidebar_position') ?: 'right');
        $blog_sidebar_position = $blog_sidebar_position === 'left' ? 'left' : 'right';
        $blog_sidebar_sticky = $isSidebarSubmit ? (isset($_POST['blog_sidebar_sticky']) ? 'y' : 'n') : (Option::get('blog_sidebar_sticky') === 'y' ? 'y' : 'n');
        $blog_sidebar_mobile_show = $isSidebarSubmit ? (isset($_POST['blog_sidebar_mobile_show']) ? 'y' : 'n') : (Option::get('blog_sidebar_mobile_show') === 'n' ? 'n' : 'y');
        $blog_sidebar_card_style = $isSidebarSubmit ? trim((string)($_POST['blog_sidebar_card_style'] ?? 'default')) : (Option::get('blog_sidebar_card_style') ?: 'default');
        if (!in_array($blog_sidebar_card_style, ['default', 'compact', 'clean'], true)) {
            $blog_sidebar_card_style = 'default';
        }

        $isFooterSubmit = isset($_POST['blog_footer_submitted']);
        $blog_footer_show = $isFooterSubmit ? (isset($_POST['blog_footer_show']) ? 'y' : 'n') : (Option::get('blog_footer_show') === 'n' ? 'n' : 'y');
        $blog_footer_custom_text = $isFooterSubmit ? trim((string)($_POST['blog_footer_custom_text'] ?? '')) : blogDefaultTplOption('blog_footer_custom_text', blogDefaultTplFooterCustomText(), '');
        $blog_footer_custom_text = strip_tags($blog_footer_custom_text);
        $blog_footer_custom_text = function_exists('mb_substr') ? mb_substr($blog_footer_custom_text, 0, 500, 'UTF-8') : substr($blog_footer_custom_text, 0, 500);
        $blog_footer_show_icp = $isFooterSubmit ? (isset($_POST['blog_footer_show_icp']) ? 'y' : 'n') : (Option::get('blog_footer_show_icp') === 'n' ? 'n' : 'y');
        $blog_footer_show_system = $isFooterSubmit ? (isset($_POST['blog_footer_show_system']) ? 'y' : 'n') : (Option::get('blog_footer_show_system') === 'n' ? 'n' : 'y');
        $blog_footer_links = $isFooterSubmit ? (string)($_POST['blog_footer_links'] ?? '') : blogDefaultTplOption('blog_footer_links', blogDefaultTplFooterLinks(), '');
        if ($isFooterSubmit) {
            $safeFooterLinks = [];
            foreach (preg_split('/\r\n|\r|\n/', $blog_footer_links) as $line) {
                if (count($safeFooterLinks) >= 12) {
                    break;
                }
                $parts = array_map('trim', explode('|', (string)$line, 3));
                if (count($parts) < 3 || $parts[1] === '' || $parts[2] === '') {
                    continue;
                }
                $icon = preg_replace('/[^\w\-\s]/', '', $parts[0]);
                $name = strip_tags($parts[1]);
                $name = function_exists('mb_substr') ? mb_substr($name, 0, 30, 'UTF-8') : substr($name, 0, 30);
                $url = trim($parts[2]);
                if (!preg_match('#^(https?:)?//#i', $url) && strpos($url, '/') !== 0 && stripos($url, 'mailto:') !== 0 && stripos($url, 'tel:') !== 0) {
                    continue;
                }
                $safeFooterLinks[] = trim($icon) . '|' . $name . '|' . $url;
            }
            $blog_footer_links = implode("\n", $safeFooterLinks);
        }

        Option::updateOption('blog_site_name', $blog_site_name);
        Option::updateOption('blog_site_desc', $blog_site_desc);
        Option::updateOption('blog_logo', $blog_logo);
        Option::updateOption('blog_title_link', $blog_title_link);
        if ($isBannerSubmit) {
            Option::updateOption('blog_banner_show', $blog_banner_show);
            Option::updateOption('blog_banner_items', json_encode($blog_banner_items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            Option::updateOption('blog_banner_height', (string)$blog_banner_height);
            Option::updateOption('blog_banner_mobile_height', (string)$blog_banner_mobile_height);
            Option::updateOption('blog_banner_speed', (string)$blog_banner_speed);
            Option::updateOption('blog_banner_animation', $blog_banner_animation);
        }
        if ($isListSubmit) {
            Option::updateOption('index_lognum', (string)$blog_index_lognum);
            Option::updateOption('blog_list_layout', $blog_list_layout);
            Option::updateOption('blog_list_show_cover', $blog_list_show_cover);
            Option::updateOption('blog_list_cover_height', (string)$blog_list_cover_height);
            Option::updateOption('blog_list_show_summary', $blog_list_show_summary);
            Option::updateOption('blog_list_summary_length', (string)$blog_list_summary_length);
            Option::updateOption('blog_list_show_category', $blog_list_show_category);
            Option::updateOption('blog_list_show_author', $blog_list_show_author);
            Option::updateOption('blog_list_show_date', $blog_list_show_date);
            Option::updateOption('blog_list_show_tags', $blog_list_show_tags);
            Option::updateOption('blog_list_show_readmore', $blog_list_show_readmore);
            Option::updateOption('blog_list_show_stats', $blog_list_show_stats);
        }
        if ($isDetailSubmit) {
            foreach ($detailOptions as $optionName) {
                Option::updateOption($optionName, isset($_POST[$optionName]) ? 'y' : 'n');
            }
        }
        if ($isSidebarSubmit) {
            Option::updateOption('blog_sidebar_show', $blog_sidebar_show);
            Option::updateOption('blog_sidebar_position', $blog_sidebar_position);
            Option::updateOption('blog_sidebar_sticky', $blog_sidebar_sticky);
            Option::updateOption('blog_sidebar_mobile_show', $blog_sidebar_mobile_show);
            Option::updateOption('blog_sidebar_card_style', $blog_sidebar_card_style);
        }
        if ($isFooterSubmit) {
            Option::updateOption('blog_footer_show', $blog_footer_show);
            Option::updateOption('blog_footer_custom_text', $blog_footer_custom_text);
            Option::updateOption('blog_footer_show_icp', $blog_footer_show_icp);
            Option::updateOption('blog_footer_show_system', $blog_footer_show_system);
            Option::updateOption('blog_footer_links', $blog_footer_links);
        }

        $CACHE->updateCache('options');
        Output::ok();
    }
}


