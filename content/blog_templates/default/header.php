<?php
/*
Template Name:默认模板
Template Type: blog_template
Version:1.2.1
Template Url:https://dcshop.xzsc.cc
Description:DCSHOP的系统默认模板
Author:dcshop
Author Url:https://dcshop.xzsc.cc
*/

defined('DC_ROOT') || exit('access denied!');
require_once View::getBlogView('module');
$v = '1720327745';

if (!function_exists('blogTplPlainText')) {
    function blogTplPlainText($value, $limit = 180)
    {
        $text = html_entity_decode(strip_tags((string)$value), ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', trim($text));
        if ($text === '') {
            return '';
        }
        $limit = max(40, (int)$limit);
        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            return mb_strlen($text, 'UTF-8') > $limit ? mb_substr($text, 0, $limit, 'UTF-8') . '…' : $text;
        }
        return strlen($text) > $limit ? substr($text, 0, $limit) . '…' : $text;
    }
}

if (!function_exists('blogTplAbsoluteUrl')) {
    function blogTplAbsoluteUrl($url)
    {
        $url = trim((string)$url);
        if ($url === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }
        if (strpos($url, '//') === 0) {
            $scheme = parse_url(DC_URL, PHP_URL_SCHEME) ?: 'https';
            return $scheme . ':' . $url;
        }
        $base = rtrim(DC_URL, '/');
        if (strpos($url, '/') === 0) {
            $parts = parse_url(DC_URL);
            $origin = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? '');
            if (!empty($parts['port'])) {
                $origin .= ':' . $parts['port'];
            }
            return $origin . $url;
        }
        $url = preg_replace('#^(\./|\../)+#', '', $url);
        return $base . '/' . ltrim($url, '/');
    }
}

if (!function_exists('blogTplJsonLd')) {
    function blogTplJsonLd($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
}

?>
<!doctype html>
<html lang="zh-cn" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="color-scheme" content="light dark">
    <?php 
    // 博客独立标题
    $blog_site_name_for_title = function_exists('blogDefaultTplOption')
        ? blogDefaultTplOption('blog_site_name', blogDefaultTplSiteName(), $blogname)
        : (Option::get('blog_site_name') ?: $blogname);
    $blog_page_title = str_replace($blogname, $blog_site_name_for_title, $site_title);
    $blog_current_page = isset($page) ? max(1, (int)$page) : max(1, Input::getIntVar('page', 1));
    $blog_canonical_url = '';

    if (!empty($logid)) {
        $blog_canonical_url = (isset($type) && $type === 'page') ? Url::art((int)$logid) : Url::log((int)$logid);
    } elseif (!empty($sortid)) {
        $blog_canonical_url = $blog_current_page > 1 ? Url::blogSort((int)$sortid, 'page') . $blog_current_page : Url::blogSort((int)$sortid);
    } elseif (isset($tag) && $tag !== '') {
        $blog_tag_for_url = urlencode(stripslashes((string)$tag));
        $blog_canonical_url = $blog_current_page > 1 ? Url::tag($blog_tag_for_url, 'page') . $blog_current_page : Url::tag($blog_tag_for_url);
    } elseif (!empty($record)) {
        $blog_canonical_url = $blog_current_page > 1 ? Url::record($record, 'page') . $blog_current_page : Url::record($record);
    } elseif (!empty($author) && empty($logid)) {
        $blog_canonical_url = $blog_current_page > 1 ? Url::author((int)$author, 'page') . $blog_current_page : Url::author((int)$author);
    } elseif (isset($keywordRaw) && Input::getStrVar('type') === 'blog') {
        $blog_search_base_url = function_exists('dcGetBlogBaseUrl') ? dcGetBlogBaseUrl() : DC_URL;
        $blog_canonical_url = $blog_search_base_url . '?keyword=' . urlencode((string)$keywordRaw) . '&type=blog';
        if ($blog_current_page > 1) {
            $blog_canonical_url .= '&page=' . $blog_current_page;
        }
    } else {
        $blog_canonical_url = $blog_current_page > 1 ? Url::blogPage() . $blog_current_page : (function_exists('dcGetBlogHomeUrl') ? dcGetBlogHomeUrl() : DC_URL . 'blog');
    }

    $blog_canonical_url = blogTplAbsoluteUrl($blog_canonical_url);
    $blog_is_article_detail = !empty($logid) && (!isset($type) || $type === 'blog');
    $blog_is_page_detail = !empty($logid) && isset($type) && $type === 'page';
    $blog_og_title = !empty($log_title) ? blogTplPlainText($log_title, 110) : blogTplPlainText($blog_page_title, 110);
    if ($blog_og_title === '') {
        $blog_og_title = $blog_site_name_for_title;
    }
    $blog_og_description = blogTplPlainText($site_description, 220);
    if ($blog_og_description === '' && !empty($log_content)) {
        $blog_og_description = blogTplPlainText($log_content, 220);
    }
    if ($blog_og_description === '') {
        $blog_og_description = blogTplPlainText(function_exists('blogDefaultTplOption')
            ? blogDefaultTplOption('blog_site_desc', blogDefaultTplSiteDesc(), ($bloginfo ?? ''))
            : (Option::get('blog_site_desc') ?: ($bloginfo ?? '')), 220);
    }
    $blog_default_tpl_url = defined('BLOG_TEMPLATE_URL') ? BLOG_TEMPLATE_URL : (DC_URL . 'content/blog_templates/default/');
    if (!preg_match('#^(https?:)?//#i', $blog_default_tpl_url) && strpos($blog_default_tpl_url, '/') !== 0 && strpos($blog_default_tpl_url, './') !== 0 && strpos($blog_default_tpl_url, '../') !== 0) {
        $blog_default_tpl_url = rtrim(DC_URL, '/') . '/' . ltrim($blog_default_tpl_url, '/');
    }
    $blog_default_logo = rtrim($blog_default_tpl_url, '/') . '/images/logo.png';
    $blog_head_logo = Option::get('blog_logo') ?: $blog_default_logo;
    $blog_og_image = !empty($log_cover) ? $log_cover : $blog_head_logo;
    if ($blog_og_image === '') {
        $blog_og_image = empty(_g('favicon')) ? DC_URL . 'favicon.ico' : _g('favicon');
    }
    $blog_og_image = blogTplAbsoluteUrl($blog_og_image);
    $blog_og_type = $blog_is_article_detail ? 'article' : 'website';
    $blog_author_name_for_meta = '';
    $blog_author_url_for_meta = '';
    if (!empty($author)) {
        $blog_meta_user = (new User_Model())->getOneUser((int)$author);
        if (!empty($blog_meta_user)) {
            $blog_author_name_for_meta = blogTplPlainText($blog_meta_user['nickname'] ?? ($blog_meta_user['username'] ?? ''), 80);
            $blog_author_url_for_meta = blogTplAbsoluteUrl(Url::author((int)$author));
        }
    }
    $blog_article_time = !empty($date) ? (int)$date : (!empty($timestamp) ? (int)$timestamp : 0);
    $blog_article_section = '';
    if (!empty($sortid)) {
        $blog_sort_cache_for_meta = Cache::getInstance()->readCache('blog_sort');
        if (!empty($blog_sort_cache_for_meta[$sortid]['sortname'])) {
            $blog_article_section = blogTplPlainText($blog_sort_cache_for_meta[$sortid]['sortname'], 60);
        }
    }
    $blog_article_tags_for_meta = [];
    if ($blog_is_article_detail && !empty($tags)) {
        $blog_tag_ids_for_meta = array_filter(array_map('intval', explode(',', (string)$tags)));
        if (!empty($blog_tag_ids_for_meta)) {
            $blog_tag_names_for_meta = (new Tag_Model())->getNamesFromIds($blog_tag_ids_for_meta);
            foreach ($blog_tag_names_for_meta as $blog_tag_name_for_meta) {
                $blog_article_tags_for_meta[] = blogTplPlainText($blog_tag_name_for_meta, 40);
            }
        }
    }
    $blog_site_description_for_schema = blogTplPlainText(function_exists('blogDefaultTplOption')
        ? blogDefaultTplOption('blog_site_desc', blogDefaultTplSiteDesc(), ($bloginfo ?? $blog_og_description))
        : (Option::get('blog_site_desc') ?: ($bloginfo ?? $blog_og_description)), 220);
    $blog_home_schema_url = blogTplAbsoluteUrl(function_exists('dcGetBlogHomeUrl') ? dcGetBlogHomeUrl() : DC_URL . 'blog');
    $blog_publisher_schema = [
        '@type' => 'Organization',
        'name' => $blog_site_name_for_title,
        'url' => blogTplAbsoluteUrl(DC_URL),
    ];
    if ($blog_og_image !== '') {
        $blog_publisher_schema['logo'] = [
            '@type' => 'ImageObject',
            'url' => $blog_og_image,
        ];
    }
    if ($blog_is_article_detail) {
        $blog_json_ld = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $blog_canonical_url,
            ],
            'headline' => $blog_og_title,
            'description' => $blog_og_description,
            'url' => $blog_canonical_url,
            'inLanguage' => 'zh-CN',
            'isPartOf' => [
                '@type' => 'Blog',
                'name' => $blog_site_name_for_title,
                'url' => $blog_home_schema_url,
            ],
            'publisher' => $blog_publisher_schema,
        ];
        if ($blog_og_image !== '') {
            $blog_json_ld['image'] = [$blog_og_image];
        }
        if ($blog_article_time > 0) {
            $blog_json_ld['datePublished'] = date('c', $blog_article_time);
            $blog_json_ld['dateModified'] = date('c', $blog_article_time);
        }
        if ($blog_author_name_for_meta !== '') {
            $blog_json_ld['author'] = [
                '@type' => 'Person',
                'name' => $blog_author_name_for_meta,
                'url' => $blog_author_url_for_meta,
            ];
        }
        if ($blog_article_section !== '') {
            $blog_json_ld['articleSection'] = $blog_article_section;
        }
        if (!empty($blog_article_tags_for_meta)) {
            $blog_json_ld['keywords'] = implode(',', $blog_article_tags_for_meta);
        }
    } elseif ($blog_is_page_detail) {
        $blog_json_ld = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $blog_og_title,
            'description' => $blog_og_description,
            'url' => $blog_canonical_url,
            'inLanguage' => 'zh-CN',
            'isPartOf' => [
                '@type' => 'Blog',
                'name' => $blog_site_name_for_title,
                'url' => $blog_home_schema_url,
            ],
            'publisher' => $blog_publisher_schema,
        ];
    } else {
        $blog_is_blog_home_schema = empty($sortid) && empty($tag) && empty($record) && empty($author) && !isset($keywordRaw);
        $blog_json_ld = [
            '@context' => 'https://schema.org',
            '@type' => $blog_is_blog_home_schema ? 'Blog' : 'CollectionPage',
            'name' => $blog_og_title,
            'description' => $blog_og_description ?: $blog_site_description_for_schema,
            'url' => $blog_canonical_url,
            'inLanguage' => 'zh-CN',
            'publisher' => $blog_publisher_schema,
        ];
        if ($blog_is_blog_home_schema) {
            $blog_json_ld['potentialAction'] = [
                '@type' => 'SearchAction',
                'target' => blogTplAbsoluteUrl(DC_URL) . '?keyword={search_term_string}&type=blog',
                'query-input' => 'required name=search_term_string',
            ];
        } else {
            $blog_json_ld['isPartOf'] = [
                '@type' => 'Blog',
                'name' => $blog_site_name_for_title,
                'url' => $blog_home_schema_url,
            ];
        }
    }
    ?>
    <title><?= htmlspecialchars($blog_page_title, ENT_QUOTES) ?></title>
    <meta name="keywords" content="<?= htmlspecialchars($site_key, ENT_QUOTES) ?>" />
    <meta name="description" content="<?= htmlspecialchars($site_description, ENT_QUOTES) ?>" />
    <link rel="canonical" href="<?= htmlspecialchars($blog_canonical_url, ENT_QUOTES) ?>" />
    <meta property="og:locale" content="zh_CN" />
    <meta property="og:site_name" content="<?= htmlspecialchars($blog_site_name_for_title, ENT_QUOTES) ?>" />
    <meta property="og:type" content="<?= htmlspecialchars($blog_og_type, ENT_QUOTES) ?>" />
    <meta property="og:title" content="<?= htmlspecialchars($blog_og_title, ENT_QUOTES) ?>" />
    <meta property="og:description" content="<?= htmlspecialchars($blog_og_description, ENT_QUOTES) ?>" />
    <meta property="og:url" content="<?= htmlspecialchars($blog_canonical_url, ENT_QUOTES) ?>" />
    <?php if ($blog_og_image !== ''): ?>
        <meta property="og:image" content="<?= htmlspecialchars($blog_og_image, ENT_QUOTES) ?>" />
        <meta property="og:image:alt" content="<?= htmlspecialchars($blog_og_title, ENT_QUOTES) ?>" />
    <?php endif; ?>
    <?php if ($blog_is_article_detail): ?>
        <?php if ($blog_article_time > 0): ?>
            <meta property="article:published_time" content="<?= htmlspecialchars(date('c', $blog_article_time), ENT_QUOTES) ?>" />
            <meta property="article:modified_time" content="<?= htmlspecialchars(date('c', $blog_article_time), ENT_QUOTES) ?>" />
        <?php endif; ?>
        <?php if ($blog_author_url_for_meta !== ''): ?><meta property="article:author" content="<?= htmlspecialchars($blog_author_url_for_meta, ENT_QUOTES) ?>" /><?php endif; ?>
        <?php if ($blog_article_section !== ''): ?><meta property="article:section" content="<?= htmlspecialchars($blog_article_section, ENT_QUOTES) ?>" /><?php endif; ?>
        <?php foreach ($blog_article_tags_for_meta as $blog_article_tag_for_meta): ?>
            <meta property="article:tag" content="<?= htmlspecialchars($blog_article_tag_for_meta, ENT_QUOTES) ?>" />
        <?php endforeach; ?>
    <?php endif; ?>
    <meta name="twitter:card" content="<?= $blog_og_image !== '' ? 'summary_large_image' : 'summary' ?>" />
    <meta name="twitter:title" content="<?= htmlspecialchars($blog_og_title, ENT_QUOTES) ?>" />
    <meta name="twitter:description" content="<?= htmlspecialchars($blog_og_description, ENT_QUOTES) ?>" />
    <?php if ($blog_og_image !== ''): ?><meta name="twitter:image" content="<?= htmlspecialchars($blog_og_image, ENT_QUOTES) ?>" /><?php endif; ?>
    <script type="application/ld+json"><?= blogTplJsonLd($blog_json_ld) ?></script>
    <link href="<?= htmlspecialchars(empty(_g('favicon')) ? DC_URL . 'favicon.ico' : _g('favicon'), ENT_QUOTES); ?>" rel="icon">
    <link rel="alternate" title="RSS" href="<?= DC_URL ?>rss.php" type="application/rss+xml" />
    <link href="<?= BLOG_TEMPLATE_URL ?>css/style.css?v=<?= $v ?>&t=<?= Option::DC_VERSION_TIMESTAMP ?>" rel="stylesheet" />
    <link href="<?= BLOG_TEMPLATE_URL ?>css/icon/iconfont.css?v=<?= $v ?>&t=<?= Option::DC_VERSION_TIMESTAMP ?>" rel="stylesheet" />
    <link href="<?= BLOG_TEMPLATE_URL ?>css/markdown.css?v=<?= $v ?>&t=<?= Option::DC_VERSION_TIMESTAMP ?>" rel="stylesheet" />
    <link rel="stylesheet" href="<?= DC_URL ?>admin/views/remixicon/remixicon.css">
    <script src="../../../admin/views/js/jquery.min.3.5.1.js?v=<?= $v ?>&t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>
    <script src="<?= DC_URL ?>content/static/js/qrcode.min.js?v=<?= $v ?>&t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>
    <script src="<?= BLOG_TEMPLATE_URL ?>js/common_tpl.js?v=<?= $v ?>&t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>
    <script src="<?= BLOG_TEMPLATE_URL ?>js/zoom.js?v=<?= $v ?>&t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>
    <?php doAction('index_head') ?>
    <script>
        // 日历生成和翻页
        function sendinfo(url) {
            $("#calendar").load(url)
        }

        // 页面渲染前先应用主题，避免刷新时闪烁
        (function () {
            var savedTheme = '';
            try {
                savedTheme = localStorage.getItem('theme') || '';
            } catch (e) {}
            var systemDark = false;
            if (window.matchMedia) {
                systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            }
            var theme = (savedTheme === 'dark' || savedTheme === 'light') ? savedTheme : (systemDark ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
</head>

<body>
    <?php 
    // 博客独立设置
    $blog_site_name = function_exists('blogDefaultTplOption')
        ? blogDefaultTplOption('blog_site_name', blogDefaultTplSiteName(), $blogname)
        : (Option::get('blog_site_name') ?: $blogname);
    $blog_site_desc = function_exists('blogDefaultTplOption')
        ? blogDefaultTplOption('blog_site_desc', blogDefaultTplSiteDesc(), $bloginfo)
        : (Option::get('blog_site_desc') ?: $bloginfo);
    $blog_logo = Option::get('blog_logo') ?: $blog_default_logo;
    if ($blog_logo !== '' && !preg_match('#^(https?:)?//#i', $blog_logo) && strpos($blog_logo, '/') !== 0 && strpos($blog_logo, './') !== 0 && strpos($blog_logo, '../') !== 0) {
        $blog_logo = '';
    }
    $blog_title_link = Option::get('blog_title_link') ?: 'blog';
    $blog_home_url = ($blog_title_link == 'home') ? DC_URL : (function_exists('dcGetBlogHomeUrl') ? dcGetBlogHomeUrl() : DC_URL . 'blog');
    // 前台固定使用模板默认手绘风格配色，不再读取后台头部配色覆盖项。
    ?>
    <nav class="blog-header" aria-label="博客顶部导航">
        <div class="blog-header-c container">
            <div class="blog-header-tape blog-header-tape-left" aria-hidden="true"></div>
            <div class="blog-header-tape blog-header-tape-right" aria-hidden="true"></div>
            <div class="blog-header-thread" aria-hidden="true"></div>
            <div class="blog-header-brand">
                <?php if (!empty($blog_logo)): ?>
                    <!-- 有Logo时显示Logo + 标题 + 副标题 -->
                    <a class="blog-header-logo" href="<?= htmlspecialchars($blog_home_url, ENT_QUOTES) ?>">
                        <img src="<?= htmlspecialchars($blog_logo, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($blog_site_name, ENT_QUOTES) ?>">
                    </a>
                    <div class="blog-header-text">
                        <a class="blog-header-title" href="<?= htmlspecialchars($blog_home_url, ENT_QUOTES) ?>"><?= htmlspecialchars($blog_site_name, ENT_QUOTES) ?></a>
                        <?php if (!empty($blog_site_desc)): ?>
                        <div class="blog-header-subtitle" title="<?= htmlspecialchars($blog_site_desc, ENT_QUOTES) ?>"><?= htmlspecialchars($blog_site_desc, ENT_QUOTES) ?></div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- 无Logo时只显示标题 + 副标题 -->
                    <div class="blog-header-text">
                        <a class="blog-header-title" href="<?= htmlspecialchars($blog_home_url, ENT_QUOTES) ?>"><?= htmlspecialchars($blog_site_name, ENT_QUOTES) ?></a>
                        <?php if (!empty($blog_site_desc)): ?>
                        <div class="blog-header-subtitle" title="<?= htmlspecialchars($blog_site_desc, ENT_QUOTES) ?>"><?= htmlspecialchars($blog_site_desc, ENT_QUOTES) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <button type="button" class="blog-header-toggle" aria-label="展开博客导航" aria-expanded="false" aria-controls="navbarResponsive">
                <svg class="blogtoggle-icon">
                    <rect x="1" y="1" width="26" height="1.6" />
                    <rect x="1" y="8" width="26" height="1.6" />
                    <rect x="1" y="15" width="26" height="1.6" />
                </svg>
            </button>
            <div class="blog-header-menu-board">
                <?php blog_navi() ?>
            </div>
            <div class="blog-header-actions">
                <button type="button" class="blog-search-toggle" data-search-action="open" aria-label="打开搜索" title="搜索文章">
                    <i class="ri-search-line" aria-hidden="true"></i>
                    <span class="blog-search-toggle-text">查找</span>
                </button>
                <button type="button" class="blog-theme-toggle" id="theme-toggle" aria-label="切换夜间模式" aria-pressed="false" title="切换夜间模式">
                    <i class="ri-moon-line" aria-hidden="true"></i>
                    <span class="blog-theme-toggle-text">夜读</span>
                </button>
            </div>
            <?php doAction('index_navi_ext') ?>
        </div>
    </nav>
    <div class="blog-search-modal" id="blogSearchModal" aria-hidden="true">
        <div class="blog-search-mask" data-search-action="close"></div>
        <div class="blog-search-dialog" role="dialog" aria-modal="true" aria-labelledby="blogSearchTitle">
            <button type="button" class="blog-search-close" data-search-action="close" aria-label="关闭搜索"><i class="ri-close-line"></i></button>
            <div class="blog-search-icon"><i class="ri-search-2-line"></i></div>
            <h2 id="blogSearchTitle">翻找文章</h2>
            <p>输入关键词，在手札里找一找</p>
            <form class="blog-search-form" id="blogSearchForm" method="get" action="<?= htmlspecialchars(function_exists('dcGetBlogBaseUrl') ? dcGetBlogBaseUrl('index.php') : DC_URL . 'index.php', ENT_QUOTES) ?>">
                <i class="ri-search-line"></i>
                <input type="search" name="keyword" id="blogSearchInput" value="<?= htmlspecialchars(isset($keywordRaw) ? (string)$keywordRaw : '', ENT_QUOTES) ?>" placeholder="输入关键词..." autocomplete="off">
                <input type="hidden" name="type" value="blog">
                <button type="submit">开始查找</button>
            </form>
            <div class="blog-search-tips">
                <span><i class="ri-keyboard-line"></i>按 <kbd>/</kbd> 或 <kbd>Ctrl</kbd> + <kbd>K</kbd> 打开</span>
                <span><i class="ri-corner-down-left-line"></i>回车搜索</span>
            </div>
            <div class="blog-search-history" id="blogSearchHistory" aria-live="polite"></div>
        </div>
    </div>
