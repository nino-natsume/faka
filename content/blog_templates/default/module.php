<?php

/**
 * 侧边栏组件、页面模块
 */
defined('DC_ROOT') || exit('access denied!');

if (!function_exists('blogDefaultTplRawOptions')) {
    function blogDefaultTplRawOptions()
    {
        static $options = null;
        if ($options === null) {
            $cache = Cache::getInstance();
            $options = $cache->readCache('options');
            $options = is_array($options) ? $options : [];
        }
        return $options;
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

/**
 * 文章预计阅读时间
 *
 * 按中文字符和英文/数字单词分别计数，折算为阅读量后估算分钟数。
 */
if (!function_exists('blog_reading_stats')) {
    function blog_reading_stats($content, $wordsPerMinute = 350)
    {
        $content = (string)$content;
        $content = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', ' ', $content);
        $text = html_entity_decode(strip_tags($content), ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', trim($text));

        if ($text === '') {
            return [
                'words' => 0,
                'minutes' => 1,
                'label' => '约 1 分钟',
            ];
        }

        $cjkCount = preg_match_all('/[\x{4e00}-\x{9fff}\x{3400}-\x{4dbf}\x{3040}-\x{30ff}\x{ac00}-\x{d7af}]/u', $text, $matches);
        $nonCjkText = preg_replace('/[\x{4e00}-\x{9fff}\x{3400}-\x{4dbf}\x{3040}-\x{30ff}\x{ac00}-\x{d7af}]/u', ' ', $text);
        $wordCount = preg_match_all('/[A-Za-z0-9]+(?:[\'._-][A-Za-z0-9]+)*/u', $nonCjkText, $matches);
        $words = max(0, (int)$cjkCount + (int)$wordCount);

        $wordsPerMinute = max(120, (int)$wordsPerMinute);
        $minutes = max(1, (int)ceil($words / $wordsPerMinute));

        return [
            'words' => $words,
            'minutes' => $minutes,
            'label' => '约 ' . $minutes . ' 分钟',
        ];
    }
}

if (!function_exists('blog_default_related_collect')) {
    function blog_default_related_collect($logData, $limit = 6)
    {
        $limit = max(1, min(12, (int)$limit));
        $currentId = (int)($logData['logid'] ?? 0);
        if ($currentId <= 0) {
            return [];
        }

        $logModel = new Log_Model();
        $related = [];
        $relatedIds = [];
        $appendItems = function ($items, $reason) use (&$related, &$relatedIds, $currentId, $limit) {
            if (empty($items) || !is_array($items)) {
                return;
            }
            foreach ($items as $item) {
                $id = (int)($item['logid'] ?? ($item['gid'] ?? 0));
                if ($id <= 0 || $id === $currentId || isset($relatedIds[$id])) {
                    continue;
                }
                $item['_related_reason'] = $reason;
                $related[] = $item;
                $relatedIds[$id] = true;
                if (count($related) >= $limit) {
                    return;
                }
            }
        };

        $tagIds = [];
        if (!empty($logData['tags'])) {
            $rawTagIds = explode(',', (string)$logData['tags']);
            foreach ($rawTagIds as $tagId) {
                $tagId = (int)trim($tagId);
                if ($tagId > 0) {
                    $tagIds[$tagId] = $tagId;
                }
            }
        }

        if (empty($tagIds)) {
            $tagModel = new Tag_Model();
            foreach ($tagModel->getTagIdsFromBlogId($currentId) as $tagId) {
                $tagId = (int)$tagId;
                if ($tagId > 0) {
                    $tagIds[$tagId] = $tagId;
                }
            }
        }

        if (!empty($tagIds)) {
            $tagModel = isset($tagModel) ? $tagModel : new Tag_Model();
            $scores = [];
            foreach ($tagIds as $tagId) {
                foreach ($tagModel->getBlogIdsFromTagId($tagId) as $blogId) {
                    $blogId = (int)$blogId;
                    if ($blogId > 0 && $blogId !== $currentId) {
                        $scores[$blogId] = isset($scores[$blogId]) ? ($scores[$blogId] + 1) : 1;
                    }
                }
            }
            if (!empty($scores)) {
                arsort($scores);
                $candidateIds = array_slice(array_keys($scores), 0, max($limit * 3, $limit));
                $candidateIds = array_map('intval', $candidateIds);
                $idStr = implode(',', $candidateIds);
                if ($idStr !== '') {
                    $appendItems($logModel->getLogsForHome("AND gid IN ($idStr) ORDER BY FIELD(gid, $idStr)", 1, count($candidateIds)), '标签相关');
                }
            }
        }

        if (count($related) < $limit && !empty($logData['sortid'])) {
            $sortId = (int)$logData['sortid'];
            if ($sortId > 0) {
                $excludeIds = array_unique(array_merge([$currentId], array_keys($relatedIds)));
                $excludeStr = implode(',', array_map('intval', $excludeIds));
                $appendItems($logModel->getLogsForHome("AND sortid=$sortId AND gid NOT IN ($excludeStr) ORDER BY date DESC", 1, $limit - count($related)), '同分类');
            }
        }

        if (count($related) < $limit) {
            $excludeIds = array_unique(array_merge([$currentId], array_keys($relatedIds)));
            $excludeStr = implode(',', array_map('intval', $excludeIds));
            $appendItems($logModel->getLogsForHome("AND gid NOT IN ($excludeStr) ORDER BY date DESC", 1, $limit - count($related)), '最新文章');
        }

        return $related;
    }
}

if (!function_exists('blog_related_posts')) {
    function blog_related_posts($logData, $limit = 6)
    {
        $items = blog_default_related_collect($logData, $limit);
        if (empty($items)) {
            return;
        }
        ?>
        <section class="blog-related-posts" aria-label="相关文章">
            <div class="blog-related-head">
                <h3><i class="ri-links-line"></i>相关文章</h3>
                <span>基于标签、分类和最新文章自动推荐</span>
            </div>
            <div class="blog-related-grid">
                <?php foreach ($items as $item): ?>
                    <?php
                    $itemId = (int)($item['logid'] ?? ($item['gid'] ?? 0));
                    $itemTitle = htmlspecialchars(htmlspecialchars_decode((string)($item['log_title'] ?? ($item['title'] ?? '无标题')), ENT_QUOTES), ENT_QUOTES);
                    $itemUrl = htmlspecialchars($item['log_url'] ?? Url::log($itemId), ENT_QUOTES);
                    $itemCover = trim((string)($item['log_cover'] ?? ''));
                    $itemDate = !empty($item['date']) ? date('Y-n-j', (int)$item['date']) : '';
                    $itemViews = isset($item['views']) ? (int)$item['views'] : 0;
                    $reason = htmlspecialchars((string)($item['_related_reason'] ?? '相关'), ENT_QUOTES);
                    ?>
                    <a class="blog-related-card" href="<?= $itemUrl ?>" title="<?= $itemTitle ?>">
                        <span class="blog-related-cover">
                            <?php if ($itemCover !== ''): ?>
                                <img src="<?= htmlspecialchars($itemCover, ENT_QUOTES) ?>" alt="<?= $itemTitle ?>" loading="lazy">
                            <?php else: ?>
                                <span class="blog-related-cover-placeholder"><i class="ri-article-line"></i></span>
                            <?php endif; ?>
                        </span>
                        <span class="blog-related-body">
                            <strong><?= $itemTitle ?></strong>
                            <span class="blog-related-meta">
                                <em><?= $reason ?></em>
                                <?php if ($itemDate !== ''): ?><span><i class="ri-time-line"></i><?= htmlspecialchars($itemDate, ENT_QUOTES) ?></span><?php endif; ?>
                                <?php if ($itemViews > 0): ?><span><i class="ri-eye-line"></i><?= $itemViews ?></span><?php endif; ?>
                            </span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
    }
}

if (!function_exists('blog_author_card')) {
    function blog_author_card($authorId, $logData = [])
    {
        $authorId = (int)$authorId;
        if ($authorId <= 0) {
            return;
        }

        $userModel = new User_Model();
        $user = $userModel->getOneUser($authorId);
        if (empty($user)) {
            return;
        }

        $name = !empty($user['nickname']) ? $user['nickname'] : (!empty($user['username']) ? $user['username'] : '匿名作者');
        $description = trim((string)($user['description'] ?? ''));
        if ($description === '') {
            $description = '这个作者很神秘，还没有留下简介。';
        }
        $avatar = User::getAvatar($user['photo'] ?? '');
        $roleName = !empty($user['role']) ? User::getRoleName($user['role'], $authorId) : '';
        $roleName = $roleName ?: '作者';
        $authorUrl = Url::author($authorId);

        $logModel = new Log_Model();
        $postCount = (int)$logModel->getLogNum('n', 'and author=' . $authorId, 'blog', 0);
        $joined = !empty($user['create_time']) ? date('Y-n-j', (int)$user['create_time']) : '';
        ?>
        <section class="blog-author-card" aria-label="作者信息">
            <div class="blog-author-card-bg"></div>
            <a class="blog-author-avatar" href="<?= htmlspecialchars($authorUrl, ENT_QUOTES) ?>">
                <img src="<?= htmlspecialchars($avatar, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($name, ENT_QUOTES) ?>">
            </a>
            <div class="blog-author-info">
                <div class="blog-author-head">
                    <div>
                        <span class="blog-author-kicker">本文作者</span>
                        <h3><?= htmlspecialchars($name, ENT_QUOTES) ?></h3>
                    </div>
                    <span class="blog-author-role"><i class="ri-vip-crown-line"></i><?= htmlspecialchars($roleName, ENT_QUOTES) ?></span>
                </div>
                <p><?= htmlspecialchars($description, ENT_QUOTES) ?></p>
                <div class="blog-author-meta">
                    <span><i class="ri-article-line"></i>文章 <?= $postCount ?> 篇</span>
                    <?php if ($joined !== ''): ?><span><i class="ri-calendar-check-line"></i>加入于 <?= htmlspecialchars($joined, ENT_QUOTES) ?></span><?php endif; ?>
                </div>
            </div>
            <a class="blog-author-more" href="<?= htmlspecialchars($authorUrl, ENT_QUOTES) ?>">
                作者主页 <i class="ri-arrow-right-line"></i>
            </a>
        </section>
        <?php
    }
}

if (!function_exists('blog_breadcrumb_text')) {
    function blog_breadcrumb_text($value, $fallback = '')
    {
        $text = html_entity_decode(strip_tags((string)$value), ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', trim($text));
        return $text === '' ? $fallback : $text;
    }
}

if (!function_exists('blog_breadcrumbs')) {
    function blog_breadcrumbs($context = [])
    {
        $context = is_array($context) ? $context : [];
        $page = isset($context['page']) ? max(1, (int)$context['page']) : max(1, Input::getIntVar('page', 1));
        $type = isset($context['breadcrumb_type']) ? (string)$context['breadcrumb_type'] : '';

        if ($type === '') {
            if (!empty($context['logid'])) {
                $type = (!empty($context['type']) && $context['type'] === 'page') ? 'page' : 'article';
            } elseif (!empty($context['sortid'])) {
                $type = 'sort';
            } elseif (isset($context['tag']) && $context['tag'] !== '') {
                $type = 'tag';
            } elseif (!empty($context['record'])) {
                $type = 'record';
            } elseif (!empty($context['author'])) {
                $type = 'author';
            } elseif (isset($context['keywordRaw']) || Input::getStrVar('type') === 'blog') {
                $type = 'search';
            } elseif ($page > 1) {
                $type = 'blog-page';
            } else {
                return;
            }
        }

        $blogName = blog_breadcrumb_text(function_exists('blogDefaultTplOption')
            ? blogDefaultTplOption('blog_site_name', blogDefaultTplSiteName(), Option::get('blogname'))
            : (Option::get('blog_site_name') ?: Option::get('blogname')), '博客');
        $items = [
            ['label' => '首页', 'url' => DC_URL],
            ['label' => $blogName, 'url' => function_exists('dcGetBlogHomeUrl') ? dcGetBlogHomeUrl() : DC_URL . 'blog'],
        ];
        $append = function ($label, $url = '') use (&$items) {
            $label = blog_breadcrumb_text($label);
            if ($label === '') {
                return;
            }
            $items[] = ['label' => $label, 'url' => (string)$url];
        };
        $appendSortChain = function ($sortId, $linkCurrent = true) use ($append) {
            $sortId = (int)$sortId;
            if ($sortId <= 0) {
                return;
            }
            $sortCache = Cache::getInstance()->readCache('blog_sort');
            $chain = [];
            $guard = 0;
            while ($sortId > 0 && isset($sortCache[$sortId]) && $guard < 20) {
                $sort = $sortCache[$sortId];
                $chain[] = [
                    'sid' => $sortId,
                    'name' => isset($sort['sortname']) ? $sort['sortname'] : '',
                    'pid' => isset($sort['pid']) ? (int)$sort['pid'] : 0,
                ];
                $sortId = isset($sort['pid']) ? (int)$sort['pid'] : 0;
                $guard++;
            }
            $chain = array_reverse($chain);
            $lastIndex = count($chain) - 1;
            foreach ($chain as $index => $sort) {
                $url = ($linkCurrent || $index < $lastIndex) ? Url::blogSort((int)$sort['sid']) : '';
                $append($sort['name'], $url);
            }
        };

        switch ($type) {
            case 'article':
                $appendSortChain($context['sortid'] ?? 0, true);
                $append($context['title'] ?? ($context['log_title'] ?? '正文'));
                break;
            case 'page':
                $append($context['title'] ?? ($context['log_title'] ?? '页面'));
                break;
            case 'sort':
                $appendSortChain($context['sortid'] ?? 0, $page > 1);
                if ($page > 1) {
                    $append('第 ' . $page . ' 页');
                }
                break;
            case 'tag':
                $tag = stripslashes((string)($context['tag'] ?? ''));
                $append('标签：' . $tag, $page > 1 ? Url::tag(rawurlencode($tag)) : '');
                if ($page > 1) {
                    $append('第 ' . $page . ' 页');
                }
                break;
            case 'record':
                $record = (string)($context['record'] ?? '');
                $append('归档：' . $record, $page > 1 ? Url::record($record) : '');
                if ($page > 1) {
                    $append('第 ' . $page . ' 页');
                }
                break;
            case 'author':
                $authorId = (int)($context['author'] ?? 0);
                $authorName = '';
                if ($authorId > 0) {
                    $user = (new User_Model())->getOneUser($authorId);
                    $authorName = !empty($user['nickname']) ? $user['nickname'] : ($user['username'] ?? '');
                }
                $append('作者：' . blog_breadcrumb_text($authorName, '匿名'), $page > 1 && $authorId > 0 ? Url::author($authorId) : '');
                if ($page > 1) {
                    $append('第 ' . $page . ' 页');
                }
                break;
            case 'search':
                $keyword = isset($context['keywordRaw']) ? (string)$context['keywordRaw'] : Input::getStrVar('keyword');
                $append($keyword === '' ? '搜索' : '搜索：' . $keyword);
                if ($page > 1) {
                    $append('第 ' . $page . ' 页');
                }
                break;
            case 'blog-page':
                if ($page <= 1) {
                    return;
                }
                $append('第 ' . $page . ' 页');
                break;
            default:
                return;
        }

        if (count($items) <= 2 && $page <= 1) {
            return;
        }
        ?>
        <nav class="blog-breadcrumb" aria-label="面包屑导航">
            <ol itemscope itemtype="https://schema.org/BreadcrumbList">
                <?php foreach ($items as $index => $item): ?>
                    <?php $isLast = $index === count($items) - 1; ?>
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <?php if (!$isLast && $item['url'] !== ''): ?>
                            <a href="<?= htmlspecialchars($item['url'], ENT_QUOTES) ?>" itemprop="item"><span itemprop="name"><?= htmlspecialchars($item['label'], ENT_QUOTES) ?></span></a>
                        <?php else: ?>
                            <span itemprop="name" aria-current="<?= $isLast ? 'page' : 'false' ?>"><?= htmlspecialchars($item['label'], ENT_QUOTES) ?></span>
                        <?php endif; ?>
                        <meta itemprop="position" content="<?= $index + 1 ?>">
                    </li>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php
    }
}

if (!function_exists('blog_lazy_content')) {
    function blog_lazy_content($content)
    {
        $content = (string)$content;
        if ($content === '' || stripos($content, '<img') === false) {
            return $content;
        }

        return preg_replace_callback('/<img\b[^>]*>/i', function ($matches) {
            $tag = $matches[0];
            $attrs = [];
            if (!preg_match('/\sloading\s*=/i', $tag)) {
                $attrs[] = 'loading="lazy"';
            }
            if (!preg_match('/\sdecoding\s*=/i', $tag)) {
                $attrs[] = 'decoding="async"';
            }
            if (empty($attrs)) {
                return $tag;
            }
            return preg_replace('/<img\b/i', '<img ' . implode(' ', $attrs), $tag, 1);
        }, $content);
    }
}
?>
<?php
if (!function_exists('blog_sidebar_limit_count')) {
    function blog_sidebar_limit_count($optionName, $default = 6, $min = 1, $max = 6)
    {
        $value = $optionName ? Option::get($optionName) : $default;
        $value = (int)($value ?: $default);
        return max($min, min($max, $value));
    }
}

if (!function_exists('blog_sidebar_limit_items')) {
    function blog_sidebar_limit_items($items, $limit)
    {
        $items = is_array($items) ? $items : [];
        $limit = max(1, (int)$limit);
        return array_slice($items, 0, $limit, true);
    }
}
?>
<?php
/**
 * 侧边栏：链接
 */
function widget_link($title)
{
    global $CACHE;
    $link_cache = blog_sidebar_limit_items($CACHE->readCache('link'), 6);
    if (empty($link_cache)) {
        $link_cache = [[
            'link' => 'DCSHOP多财商城系统',
            'url' => 'https://dcshop.xzsc.cc/',
            'des' => '',
            'icon' => '',
        ]];
    }
    //if (!blog_tool_ishome()) return;#只在首页显示友链去掉双斜杠注释即可
?>
    <div class="widget shadow-theme widget-link">
        <div class="widget-title">
            <h3><i class="ri-links-line"></i><?= htmlspecialchars($title, ENT_QUOTES) ?></h3>
        </div>
        <ul class="widget-list blog-link-list no-margin-bottom unstyle-li">
            <?php
            foreach ($link_cache as $value):
                $icon = isset($value['icon']) ? $value['icon'] : '';
            ?>
                <li>
                    <a class="blog-link-card" href="<?= htmlspecialchars($value['url'], ENT_QUOTES) ?>" title="<?= htmlspecialchars($value['des'], ENT_QUOTES) ?>" target="_blank" rel="noopener noreferrer">
                        <span class="blog-link-icon">
                            <?php if ($icon): ?>
                                <img src="<?= htmlspecialchars($icon, ENT_QUOTES) ?>" alt="" loading="lazy" decoding="async">
                            <?php else: ?>
                                <i class="ri-leaf-line"></i>
                            <?php endif; ?>
                        </span>
                        <span class="blog-link-text">
                            <strong><?= htmlspecialchars($value['link'], ENT_QUOTES) ?></strong>
                            <?php if (!empty($value['des'])): ?><em><?= htmlspecialchars($value['des'], ENT_QUOTES) ?></em><?php endif; ?>
                        </span>
                        <i class="ri-arrow-right-up-line blog-link-arrow"></i>
                    </a>
                </li>
            <?php endforeach ?>
        </ul>
    </div>
<?php } ?>
<?php
/**
 * 侧边栏：个人资料
 */
function widget_blogger($title)
{
    global $blogname, $bloginfo;
    $safeUrl = function ($url) {
        $url = trim((string)$url);
        if ($url === '') {
            return '';
        }
        return (preg_match('#^(https?:)?//#i', $url)
            || strpos($url, '/') === 0
            || strpos($url, './') === 0
            || strpos($url, '../') === 0
            || strpos($url, '#') === 0
            || stripos($url, 'mailto:') === 0
            || stripos($url, 'tel:') === 0) ? $url : '';
    };
    $safeImageUrl = function ($url) {
        $url = trim((string)$url);
        if ($url === '') {
            return '';
        }
        return (preg_match('#^(https?:)?//#i', $url)
            || strpos($url, '/') === 0
            || strpos($url, './') === 0
            || strpos($url, '../') === 0) ? $url : '';
    };
    $cleanIcon = function ($icon, $fallback = 'ri-links-line') {
        $icon = trim(preg_replace('/[^\w\-\s]/', '', (string)$icon));
        return $icon !== '' ? $icon : $fallback;
    };
    $parseExternalLinks = function ($raw, $limit = 8) use ($safeUrl, $cleanIcon) {
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
            $name = trim(strip_tags($parts[1]));
            $url = $safeUrl($parts[2]);
            if ($name === '' || $url === '') {
                continue;
            }
            $links[] = [
                'icon' => $cleanIcon($parts[0]),
                'name' => $name,
                'url' => $url,
            ];
        }
        return $links;
    };
    $userModel = new User_Model();
    $uid = UID ?: 1;
    $name = '';
    $description = '';
    $avatar = DC_URL . "admin/views/images/avatar.svg";
    $user = $userModel->getOneUser($uid);
    if ($user) {
        $name = $user['nickname'];
        $description = $user['description'];
        $avatar = User::getAvatar($user['photo']);
    }
    $name = trim((string)$name) !== '' ? $name : (blogDefaultTplOption('blog_site_name', blogDefaultTplSiteName(), ($blogname ?: 'Blog')));
    $description = trim((string)$description) !== '' ? $description : (blogDefaultTplOption('blog_site_desc', blogDefaultTplSiteDesc(), ($bloginfo ?: '写点日常，也收集一些灵感。')));
    $customName = trim((string)blogDefaultTplOption('blogger_nickname', blogDefaultTplBloggerNickname(), ''));
    if ($customName !== '') {
        $name = $customName;
    }
    $customAvatar = $safeImageUrl(blogDefaultTplOption('blogger_avatar', blogDefaultTplBloggerAvatar(), '') ?: '');
    if ($customAvatar !== '') {
        $avatar = $customAvatar;
    }
    $showNickname = Option::get('blogger_show_nickname') === 'n' ? false : true;
    $showIntro = Option::get('blogger_intro_show') === 'n' ? false : true;
    $customIntro = trim((string)blogDefaultTplOption('blogger_intro_text', blogDefaultTplBloggerIntro(), ''));
    if ($customIntro !== '') {
        $description = $customIntro;
    }
    $buttons = [
        [
            'show' => Option::get('blogger_button1_show') === 'n' ? false : true,
            'text' => trim((string)(Option::get('blogger_button1_text') ?: '文章手札')),
            'icon' => $cleanIcon(Option::get('blogger_button1_icon') ?: 'ri-book-open-line', 'ri-book-open-line'),
            'url' => $safeUrl(Option::get('blogger_button1_url') ?: (function_exists('dcGetBlogHomeUrl') ? dcGetBlogHomeUrl() : DC_URL . 'blog')),
            'newtab' => Option::get('blogger_button1_newtab') === 'y',
        ],
        [
            'show' => Option::get('blogger_button2_show') === 'n' ? false : true,
            'text' => trim((string)(Option::get('blogger_button2_text') ?: '返回首页')),
            'icon' => $cleanIcon(Option::get('blogger_button2_icon') ?: 'ri-home-heart-line', 'ri-home-heart-line'),
            'url' => $safeUrl(Option::get('blogger_button2_url') ?: DC_URL),
            'newtab' => Option::get('blogger_button2_newtab') === 'y',
        ],
    ];
    $buttons = array_values(array_filter($buttons, function ($button) {
        return !empty($button['show']) && $button['url'] !== '' && $button['text'] !== '';
    }));
    $externalLinks = $parseExternalLinks(blogDefaultTplOption('blogger_external_links', blogDefaultTplBloggerExternalLinks(), '') ?: '', 8);
?>
    <div class="widget shadow-theme widget-blogger">
        <div class="unstyle-li bloggerinfo">
            <div class="bloggerinfo-cover" aria-hidden="true">
                <span class="bloggerinfo-sun"></span>
                <span class="bloggerinfo-line"></span>
            </div>
            <div class="bloggerinfo-main">
                <a class="bloggerinfo-avatar" href="./admin/setting.php" title="编辑个人资料">
                    <img class="bloggerinfo-img" src="<?= htmlspecialchars($avatar, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($name, ENT_QUOTES) ?>" />
                </a>
                <div class="bloggerinfo-text">
                    <div class="bloggerinfo-kicker"><i class="ri-quill-pen-line"></i><?= htmlspecialchars($title ?: '个人资料', ENT_QUOTES) ?></div>
                    <?php if ($showNickname): ?>
                    <div class="bloginfo-name"><b><?= htmlspecialchars($name, ENT_QUOTES) ?></b></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($showIntro && trim((string)$description) !== ''): ?>
            <div class="bloginfo-descript"><?= nl2br(htmlspecialchars($description, ENT_QUOTES)) ?></div>
            <?php endif; ?>
            <?php if (!empty($externalLinks)): ?>
            <div class="bloggerinfo-links" aria-label="外链">
                <?php foreach ($externalLinks as $link): ?>
                <a href="<?= htmlspecialchars($link['url'], ENT_QUOTES) ?>" target="_blank" rel="noopener noreferrer" title="<?= htmlspecialchars($link['name'], ENT_QUOTES) ?>"><i class="<?= htmlspecialchars($link['icon'], ENT_QUOTES) ?>"></i><span><?= htmlspecialchars($link['name'], ENT_QUOTES) ?></span></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($buttons)): ?>
            <div class="bloggerinfo-actions bloggerinfo-actions-<?= count($buttons) ?>">
                <?php foreach ($buttons as $button): ?>
                <a href="<?= htmlspecialchars($button['url'], ENT_QUOTES) ?>"<?= $button['newtab'] ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><i class="<?= htmlspecialchars($button['icon'], ENT_QUOTES) ?>"></i><span><?= htmlspecialchars($button['text'], ENT_QUOTES) ?></span></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
<?php } ?>
<?php
/**
 * 侧边栏：日历
 */
function widget_calendar($title)
{ ?>
    <div class="widget shadow-theme widget-calendar">
        <div class="widget-title m">
            <h3><i class="ri-calendar-2-line"></i><?= htmlspecialchars($title, ENT_QUOTES) ?></h3>
        </div>
        <div class="unstyle-li blog-calendar-wrap">
            <div id="calendar"></div>
            <script>
                sendinfo('<?= Calendar::url() ?>', 'calendar');
            </script>
        </div>
    </div>
<?php } ?>
<?php
/**
 * 侧边栏：标签
 */
function widget_tag($title)
{
    global $CACHE;
    $tag_cache = blog_sidebar_limit_items($CACHE->readCache('tags'), 18) ?>
    <div class="widget shadow-theme widget-tag">
        <div class="widget-title m">
            <h3><i class="ri-price-tag-3-line"></i><?= htmlspecialchars($title, ENT_QUOTES) ?></h3>
        </div>
        <div class="unstyle-li tag-container">
            <?php foreach ($tag_cache as $value): ?>
                <span>
                    <a href="<?= Url::tag($value['tagurl']) ?>" title="<?= (int)$value['usenum'] ?> 篇文章" class="tags-side">
                        <i class="ri-hashtag"></i><?= htmlspecialchars($value['tagname'], ENT_QUOTES) ?><em><?= (int)$value['usenum'] ?></em>
                    </a>
                </span>
            <?php endforeach ?>
        </div>
    </div>
<?php } ?>
<?php
/**
 * 侧边栏：分类
 */
if (!function_exists('blog_sort_icon_html')) {
    function blog_sort_icon_html($sort, $class = 'blog-sort-icon')
    {
        $icon = isset($sort['sorticon']) ? trim((string)$sort['sorticon']) : '';
        if ($icon === '') {
            return '';
        }
        return '<i class="' . htmlspecialchars($icon, ENT_QUOTES) . ' ' . htmlspecialchars($class, ENT_QUOTES) . '" aria-hidden="true"></i>';
    }
}

function widget_sort($title)
{
    global $CACHE;
    $sort_cache = is_array($CACHE->readCache('blog_sort')) ? $CACHE->readCache('blog_sort') : [];
    $sortLimit = 8;
    $childSortLimit = 4;
    $sortShown = 0;
    ?>
    <div class="widget shadow-theme widget-sort">
        <div class="widget-title m">
            <h3><i class="ri-folder-3-line"></i><?= htmlspecialchars($title, ENT_QUOTES) ?></h3>
        </div>
        <ul class="unstyle-li log-classify-f">
            <?php
            foreach ($sort_cache as $value):
                if ($value['pid'] != 0)
                    continue;
                if ($sortShown >= $sortLimit)
                    break;
                $sortShown++;
            ?>
                <li class="sort-parent-item">
                    <a class="sort-card" href="<?= Url::blogSort($value['sid']) ?>" title="<?= htmlspecialchars($value["description"], ENT_QUOTES) ?>">
                        <span class="sort-card-name"><?= blog_sort_icon_html($value, 'blog-sort-icon blog-side-sort-icon') ?><strong><?= htmlspecialchars($value['sortname'], ENT_QUOTES) ?></strong></span>
                        <span class="sort-card-count"><?= (int)$value['lognum'] ?></span>
                    </a>
                    <?php if (!empty($value['children'])): ?>
                        <ul class="log-classify-c">
                            <?php
                            $children = $value['children'];
                            $childShown = 0;
                            foreach ($children as $key):
                                if ($childShown >= $childSortLimit)
                                    break;
                                $value = $sort_cache[$key];
                                $childShown++;
                            ?>
                                <li class="sort-child-item">
                                    <a class="sort-card sort-card-child" href="<?= Url::blogSort($value['sid']) ?>" title="<?= htmlspecialchars($value["description"], ENT_QUOTES) ?>">
                                        <span class="sort-card-name"><?= blog_sort_icon_html($value, 'blog-sort-icon blog-side-sort-icon') ?><strong><?= htmlspecialchars($value['sortname'], ENT_QUOTES) ?></strong></span>
                                        <span class="sort-card-count"><?= (int)$value['lognum'] ?></span>
                                    </a>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    <?php endif ?>
                </li>
            <?php endforeach ?>
        </ul>
    </div>
<?php } ?>
<?php
/**
 * 侧边栏：最新微语
 */
function widget_twitter($title)
{
    global $CACHE;
    $index_newtwnum = blog_sidebar_limit_count('index_newtwnum', 5, 1, 5);
    $Twitter_Model = new Twitter_Model();
    $ts = $Twitter_Model->getTwitters('', 1, $index_newtwnum);
    $user_cache = $CACHE->readCache('user');
?>
    <div class="widget shadow-theme widget-twitter">
        <div class="widget-title m">
            <h3><i class="ri-chat-quote-line"></i><?= htmlspecialchars($title, ENT_QUOTES) ?></h3>
        </div>
        <ul class="unstyle-li blog-twitter-list">
            <?php foreach ($ts as $value):
                $author = isset($user_cache[$value['author']]['name']) ? $user_cache[$value['author']]['name'] : '博主';
            ?>
                <li class="twitter-card">
                    <div class="twitter-card-icon"><i class="ri-double-quotes-l"></i></div>
                    <div class="twitter-card-body">
                        <div class="twitter-card-content"><?= htmlspecialchars(strip_tags((string)$value['t']), ENT_QUOTES) ?></div>
                        <div class="twitter-card-meta"><span><?= htmlspecialchars($author, ENT_QUOTES) ?></span><time><?= htmlspecialchars($value['date'], ENT_QUOTES) ?></time></div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php } ?>
<?php
/**
 * 侧边栏：最新评论
 */
function widget_newcomm($title)
{
    global $CACHE;
    $com_cache = blog_sidebar_limit_items($CACHE->readCache('comment'), blog_sidebar_limit_count('index_comnum', 5, 1, 6));
    $commentSubnum = blog_sidebar_limit_count('comment_subnum', 60, 5, 90);
?>
    <div class="widget shadow-theme widget-newcomm">
        <div class="widget-title">
            <h3><i class="ri-chat-smile-3-line"></i><?= htmlspecialchars($title, ENT_QUOTES) ?></h3>
        </div>
        <ul class="unstyle-li blog-comment-list">
            <?php
            foreach ($com_cache as $value):
                $url = Url::comment($value['gid'], $value['page'], $value['cid']);
                $avatar = getEmUserAvatar($value['uid'], $value['mail']);
            ?>
                <li class="comment-info">
                    <a class="comment-info-avatar" href="<?= htmlspecialchars($url, ENT_QUOTES) ?>">
                        <img class="comment-info_img" src="<?= htmlspecialchars($avatar, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($value['name'], ENT_QUOTES) ?>" loading="lazy" decoding="async" />
                    </a>
                    <div class="comment-info-body">
                        <div class="comment-info-head">
                            <span class="comm-lates-name"><?= htmlspecialchars($value['name'], ENT_QUOTES) ?></span>
                            <span class="logcom-latest-time"><?= smartDate($value['date']) ?></span>
                        </div>
                        <a class="comment-info-content" href="<?= htmlspecialchars($url, ENT_QUOTES) ?>"><?= htmlspecialchars(function_exists('mb_substr') ? mb_substr(strip_tags((string)$value['content']), 0, $commentSubnum, 'UTF-8') : substr(strip_tags((string)$value['content']), 0, $commentSubnum), ENT_QUOTES) ?></a>
                    </div>
                </li>
            <?php endforeach ?>
        </ul>
    </div>
<?php } ?>
<?php
/**
 * 侧边栏：最新文章
 */
function widget_newlog($title)
{
    $Log_Model = new Log_Model();
    $newLogs = $Log_Model->getLogsForHome(' ORDER BY date DESC', 1, blog_sidebar_limit_count('index_newlognum', 5, 1, 6));
?>
    <div class="widget shadow-theme widget-article-list widget-newlog">
        <div class="widget-title m">
            <h3><i class="ri-article-line"></i><?= htmlspecialchars($title, ENT_QUOTES) ?></h3>
        </div>
        <ul class="unstyle-li side-article-list">
            <?php foreach ($newLogs as $value): ?>
                <li class="blog-lates">
                    <a class="side-article-card" href="<?= htmlspecialchars($value['log_url'], ENT_QUOTES) ?>">
                        <span class="side-article-cover">
                            <?php if ($value['log_cover']): ?>
                                <img src="<?= htmlspecialchars($value['log_cover'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($value['log_title'], ENT_QUOTES) ?>" loading="lazy" decoding="async">
                            <?php else: ?>
                                <i class="ri-quill-pen-line"></i>
                            <?php endif ?>
                        </span>
                        <span class="side-article-body">
                            <strong><?= htmlspecialchars($value['log_title'], ENT_QUOTES) ?></strong>
                            <em><i class="ri-time-line"></i><?= !empty($value['date']) ? date('m-d', (int)$value['date']) : '新文章' ?></em>
                        </span>
                    </a>
                </li>
            <?php endforeach ?>
        </ul>
    </div>
<?php } ?>
<?php
/**
 * 侧边栏：热门文章
 */
function widget_hotlog($title)
{
    $index_hotlognum = blog_sidebar_limit_count('index_hotlognum', 5, 1, 6);
    $Log_Model = new Log_Model();
    $hotLogs = $Log_Model->getHotLog($index_hotlognum) ?>
    <div class="widget shadow-theme widget-article-list widget-hotlog">
        <div class="widget-title m">
            <h3><i class="ri-fire-line"></i><?= htmlspecialchars($title, ENT_QUOTES) ?></h3>
        </div>
        <ul class="unstyle-li side-article-list side-hot-list">
            <?php $hotIndex = 0; foreach ($hotLogs as $value): $hotIndex++; ?>
                <li class="blog-lates">
                    <a class="side-article-card" href="<?= Url::log($value['gid']) ?>">
                        <span class="side-hot-rank"><?= $hotIndex ?></span>
                        <span class="side-article-cover">
                            <?php if ($value['cover']): ?>
                                <img src="<?= htmlspecialchars($value['cover'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($value['title'], ENT_QUOTES) ?>" loading="lazy" decoding="async">
                            <?php else: ?>
                                <i class="ri-fire-line"></i>
                            <?php endif ?>
                        </span>
                        <span class="side-article-body">
                            <strong><?= htmlspecialchars($value['title'], ENT_QUOTES) ?></strong>
                            <em><i class="ri-eye-line"></i>热门阅读</em>
                        </span>
                    </a>
                </li>
            <?php endforeach ?>
        </ul>
    </div>
<?php } ?>
<?php
/**
 * 侧边栏：搜索
 */
function widget_search($title)
{ ?>
    <div class="widget shadow-theme widget-search">
        <div class="widget-title">
            <h3><i class="ri-search-2-line"></i><?= htmlspecialchars($title, ENT_QUOTES) ?></h3>
        </div>
        <div class="unstyle-li blog-side-search">
            <p>在这些手札里翻一翻</p>
            <form class="blog-side-search-form" name="keyform" method="get" action="<?= htmlspecialchars(function_exists('dcGetBlogBaseUrl') ? dcGetBlogBaseUrl('index.php') : DC_URL . 'index.php', ENT_QUOTES) ?>">
                <label class="blog-side-search-input">
                    <i class="ri-search-line"></i>
                    <input name="keyword" class="search form-control" autocomplete="off" aria-label="搜索文章" type="search" placeholder="输入关键词..." />
                </label>
                <input type="hidden" name="type" value="blog" />
                <button type="submit"><span>搜索</span><i class="ri-arrow-right-up-line"></i></button>
            </form>
        </div>
    </div>
<?php } ?>
<?php
/**
 * 侧边栏：归档
 */
function widget_archive($title)
{
    $bar_id = "36";
    global $CACHE;
    $record_cache = blog_sidebar_limit_items($CACHE->readCache('record'), 12);
?>
    <div class="widget shadow-theme widget-archive">
        <div class="widget-title m">
            <h3><i class="ri-archive-drawer-line"></i><?= htmlspecialchars($title, ENT_QUOTES) ?></h3>
        </div>
        <div class="archive-select-wrap">
            <i class="ri-calendar-todo-line"></i>
            <select id="archive" class="archive">
                <?php foreach ($record_cache as $value): ?>
                    <option value="<?= Url::record($value['date']) ?>"><?= htmlspecialchars($value['record'], ENT_QUOTES) ?>&nbsp;(<?= (int)$value['lognum'] ?>)</option>
                <?php endforeach ?>
            </select>
        </div>
    </div>
<?php } ?>
<?php
/**
 * 侧边栏：自定义组件
 */
function widget_custom_text($title, $content)
{ ?>
    <div class="widget shadow-theme">
        <div class="widget-title m">
            <h3><?= $title ?></h3>
        </div>
        <ul class="unstyle-li">
            <?= $content ?>
        </ul>
    </div>
<?php } ?>
<?php
/**
 * 页顶：导航
 */
function blog_navi()
{
    global $CACHE;
    // 使用博客专用导航
    $navi_cache = $CACHE->readCache('blog_navi');
    $blog_sort_cache = $CACHE->readCache('blog_sort');
    $blog_sort_cache = is_array($blog_sort_cache) ? $blog_sort_cache : [];
    
    // 如果博客导航为空，或仍是旧版默认导航，补齐默认导航。
    if (empty($navi_cache) || count($navi_cache) < 3) {
        $blogNaviModel = new Blog_Navi_Model();
        $blogNaviModel->initDefaultNavis();
        $navi_cache = $CACHE->readCache('blog_navi');
    }
    
    // 获取当前URL信息用于高亮判断
    $current_uri = html_entity_decode($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8');
    $normalizePath = function ($path) {
        $path = rawurldecode((string)$path);
        return rtrim($path, '/') ?: '/';
    };
    $parseNavUrl = function ($url) use ($normalizePath) {
        $url = html_entity_decode(trim((string)$url), ENT_QUOTES, 'UTF-8');
        if ($url === '') {
            return ['path' => '/', 'query' => '', 'params' => [], 'host' => ''];
        }
        $parts = parse_url($url);
        if ($parts === false) {
            $parts = [];
        }
        $query = isset($parts['query']) ? str_replace('&amp;', '&', (string)$parts['query']) : '';
        $params = [];
        if ($query !== '') {
            parse_str($query, $params);
        }
        $host = strtolower((string)($parts['host'] ?? ''));
        if ($host !== '' && isset($parts['port'])) {
            $host .= ':' . (int)$parts['port'];
        }
        return [
            'path' => $normalizePath($parts['path'] ?? '/'),
            'query' => $query,
            'params' => $params,
            'host' => $host,
        ];
    };
    $current_info = $parseNavUrl($current_uri);
    $current_path = $current_info['path'];
    $current_path_norm = $current_info['path'];
    $current_params = $current_info['params'];
    $current_host = strtolower($_SERVER['HTTP_HOST'] ?? '');
    $dc_parts = parse_url(DC_URL);
    $dc_host = strtolower((string)($dc_parts['host'] ?? ''));
    if ($dc_host !== '' && isset($dc_parts['port'])) {
        $dc_host .= ':' . (int)$dc_parts['port'];
    }
    $current_blogsort = Input::getIntVar('blogsort');
    $current_blog = Input::getIntVar('blog');
    if (!$current_blogsort && isset($current_params['blogsort']) && is_numeric($current_params['blogsort'])) {
        $current_blogsort = (int)$current_params['blogsort'];
    }
    if (!$current_blog && isset($current_params['blog']) && is_numeric($current_params['blog'])) {
        $current_blog = (int)$current_params['blog'];
    }
    $resolveBlogSortFromPath = function ($path) use ($blog_sort_cache) {
        $path = (string)$path;
        if (!preg_match('|/blogsort/([^?]+)|', $path, $matches)) {
            return 0;
        }
        $sortPath = trim($matches[1], '/');
        $sortParts = $sortPath === '' ? [] : explode('/', $sortPath);
        $pagePos = array_search('page', $sortParts, true);
        if ($pagePos !== false) {
            $sortParts = array_slice($sortParts, 0, $pagePos);
        }
        $sortIndex = urldecode((string)end($sortParts));
        if (is_numeric($sortIndex)) {
            return (int)$sortIndex;
        }
        if ($sortIndex !== '') {
            foreach ($blog_sort_cache as $sid => $sort) {
                if (isset($sort['alias']) && $sort['alias'] === $sortIndex) {
                    return (int)$sid;
                }
                $sortName = isset($sort['sortname']) ? html_entity_decode((string)$sort['sortname'], ENT_QUOTES, 'UTF-8') : '';
                if ($sortName !== '' && $sortName === $sortIndex) {
                    return (int)$sid;
                }
            }
        }
        return 0;
    };
    if (!$current_blogsort) {
        $current_blogsort = $resolveBlogSortFromPath($current_path);
    }
    $isSameBlogNavUrl = function ($url) use ($parseNavUrl, $current_path_norm, $current_params, $current_blogsort, $current_blog, $resolveBlogSortFromPath, $current_host, $dc_host) {
        if (trim((string)$url) === '') {
            return false;
        }
        $nav = $parseNavUrl($url);
        if ($nav['host'] !== '' && $current_host !== '' && $nav['host'] !== $current_host && ($dc_host === '' || $nav['host'] !== $dc_host)) {
            return false;
        }
        $nav_params = $nav['params'];
        if (isset($nav_params['blogsort']) && is_numeric($nav_params['blogsort'])) {
            return $current_blogsort > 0 && $current_blogsort === (int)$nav_params['blogsort'];
        }
        if (isset($nav_params['blog']) && is_numeric($nav_params['blog'])) {
            return $current_blog > 0 && $current_blog === (int)$nav_params['blog'];
        }
        $nav_blogsort = $resolveBlogSortFromPath($nav['path']);
        if ($nav_blogsort > 0) {
            return $current_blogsort > 0 && $current_blogsort === $nav_blogsort;
        }
        if ($nav['path'] === $current_path_norm || ($nav['path'] !== '/' && strpos($current_path_norm, $nav['path'] . '/page/') === 0)) {
            if (!empty($nav_params)) {
                foreach ($nav_params as $key => $val) {
                    if ($key === 'page') {
                        continue;
                    }
                    if (!isset($current_params[$key]) || (string)$current_params[$key] !== (string)$val) {
                        return false;
                    }
                }
            }
            if ($nav['path'] === '/') {
                return empty($nav_params) ? empty($current_params) : true;
            }
            return true;
        }
        return false;
    };
    
    // 判断是否在博客首页（/blog 或 /blog/）
    $is_blog_home = preg_match('|^/blog/?$|', $current_uri) || preg_match('|^/blog/?\?|', $current_uri);
    
    // 判断是否在博客分类页
    $is_blogsort_page = $current_blogsort > 0 || preg_match('|/blogsort/|', $current_uri);
    
    // 从URL中提取分类ID/别名，兼容 /blogsort/父别名/子别名/page/2
    if (!$current_blogsort) {
        $current_blogsort = $resolveBlogSortFromPath($current_path);
    }
    
    // 判断是否在文章详情页
    $is_blog_detail = $current_blog > 0 || preg_match('|/blog/\d+|', $current_uri);
    ?>
    <div class="blog-header-nav" id="navbarResponsive">
        <ul class="nav-list">
            <?php
            foreach ($navi_cache as $value):
                if ($value['pid'] != 0) {
                    continue;
                }
                $newtab = $value['newtab'] == 'y' ? 'target="_blank"' : '';
                
                // 判断当前导航是否应该高亮
                $is_active = false;
                $type = $value['type'];
                $typeId = isset($value['typeId']) ? $value['typeId'] : 0;
                $nav_url = $value['url'];
                $navIcon = isset($value['naviicon']) ? trim((string)$value['naviicon']) : '';
                
                if ($type == Blog_Navi_Model::navitype_home || $type == Blog_Navi_Model::navitype_blog) {
                    // 首页/博客导航：在博客首页时高亮（不在文章详情页、不在分类页）
                    $is_active = ($is_blog_home || $isSameBlogNavUrl($nav_url)) && !$current_blog && !$current_blogsort;
                } elseif ($type == Blog_Navi_Model::navitype_blogsort) {
                    // 文章分类导航：当前分类ID匹配时高亮
                    $is_active = $current_blogsort > 0 && $current_blogsort == $typeId;
                } elseif ($type == Blog_Navi_Model::navitype_page) {
                    // 页面导航：URL匹配时高亮
                    $is_active = ($current_blog > 0 && $current_blog == $typeId) || $isSameBlogNavUrl($nav_url);
                } elseif ($type == Blog_Navi_Model::navitype_custom) {
                    // 自定义导航：精确匹配URL
                    if (!empty($nav_url)) {
                        // 提取导航URL中的参数进行匹配
                        $nav_query = parse_url($nav_url, PHP_URL_QUERY) ?: '';
                        $current_query = parse_url($current_uri, PHP_URL_QUERY) ?: '';
                        
                        // 检查是否是文章链接 ?blog=xx
                        if (preg_match('/blog=(\d+)/', $nav_query, $m)) {
                            $nav_blog_id = (int)$m[1];
                            $is_active = $current_blog == $nav_blog_id;
                        } else {
                            // 其他自定义链接，路径匹配
                            $is_active = $isSameBlogNavUrl($nav_url);
                        }
                    }
                }
                if ($type == Blog_Navi_Model::navitype_blogsort && $navIcon === '' && !empty($blog_sort_cache[$typeId]['sorticon'])) {
                    $navIcon = $blog_sort_cache[$typeId]['sorticon'];
                }
                
                $active_class = $is_active ? ' active' : '';
                ?>
                <?php if (!empty($value['children']) || !empty($value['childnavi'])) : ?>
                <li class="list-item list-menu">
                    <?php if (!empty($value['children'])): ?>
                        <a class="nav-link has-down<?= $active_class ?>" href="<?= $nav_url ?>" <?= $newtab ?>>
                            <?php if (!empty($navIcon)): ?>
                                <i class="<?= htmlspecialchars($navIcon, ENT_QUOTES) ?> blog-sort-icon blog-nav-sort-icon"></i>
                            <?php endif; ?>
                            <?= $value['naviname'] ?>
                        </a>
                        <ul class="dropdown-menus">
                            <?php foreach ($value['children'] as $row) {
                                $child_active = ($current_blogsort > 0 && $current_blogsort == $row['sid']) ? ' active' : '';
                                $child_icon = blog_sort_icon_html($row, 'blog-sort-icon blog-nav-sort-icon');
                                echo '<li class="list-item list-menu"><a class="nav-link' . $child_active . '" href="' . Url::blogSort($row['sid']) . '">' . $child_icon . $row['sortname'] . '</a></li>';
                            } ?>
                        </ul>
                    <?php endif ?>
                    <?php if (!empty($value['childnavi'])) : ?>
                        <a class="nav-link has-down<?= $active_class ?>" href="<?= $nav_url ?>" <?= $newtab ?>>
                            <?php if (!empty($navIcon)): ?>
                                <i class="<?= htmlspecialchars($navIcon, ENT_QUOTES) ?> blog-sort-icon blog-nav-sort-icon"></i>
                            <?php endif; ?>
                            <?= $value['naviname'] ?>
                        </a>
                        <ul class="dropdown-menus">
                            <?php foreach ($value['childnavi'] as $row) {
                                $child_newtab = $row['newtab'] == 'y' ? 'target="_blank"' : '';
                                $child_icon = !empty($row['naviicon']) ? '<i class="' . htmlspecialchars($row['naviicon'], ENT_QUOTES) . ' blog-sort-icon blog-nav-sort-icon"></i>' : '';
                                $child_type = isset($row['type']) ? (int)$row['type'] : Blog_Navi_Model::navitype_custom;
                                $child_type_id = isset($row['typeId']) ? (int)$row['typeId'] : 0;
                                $child_active = '';
                                if ($child_type == Blog_Navi_Model::navitype_blogsort && $current_blogsort > 0 && $current_blogsort == $child_type_id) {
                                    $child_active = ' active';
                                } elseif ($child_type == Blog_Navi_Model::navitype_page && (($current_blog > 0 && $current_blog == $child_type_id) || $isSameBlogNavUrl($row['url']))) {
                                    $child_active = ' active';
                                } elseif ($child_type == Blog_Navi_Model::navitype_custom && $isSameBlogNavUrl($row['url'])) {
                                    $child_active = ' active';
                                }
                                echo '<li class="list-item list-menu"><a class="nav-link' . $child_active . '" href="' . htmlspecialchars($row['url'], ENT_QUOTES) . "\" $child_newtab >" . $child_icon . $row['naviname'] . '</a></li>';
                            } ?>
                        </ul>
                    <?php endif ?>
                </li>
            <?php else: ?>
                <li class="list-item list-menu">
                    <a class="nav-link<?= $active_class ?>" href="<?= $nav_url ?>" <?= $newtab ?>>
                        <?php if (!empty($navIcon)): ?>
                            <i class="<?= htmlspecialchars($navIcon, ENT_QUOTES) ?> blog-sort-icon blog-nav-sort-icon"></i>
                        <?php endif; ?>
                        <?= $value['naviname'] ?>
                    </a>
                </li>
            <?php endif ?>
            <?php endforeach ?>
        </ul>
    </div>
<?php } ?>
<?php
/**
 * 文章列出卡片：置顶标志
 */
function topflg($top, $sortop = 'n', $sortid = null)
{
    $ishome_flg = '<span class="log-topflg" >置顶</span>';
    $issort_flg = '<span class="log-topflg" >分类置顶</span>';
    if (blog_tool_ishome()) {
        echo $top == 'y' ? $ishome_flg : '';
    } elseif ($sortid) {
        echo $sortop == 'y' ? $issort_flg : '';
    }
}

?>
<?php
/**
 * 文章详情页：编辑链接
 */
function editflg($logid, $author)
{
    $editflg = User::haveEditPermission() || $author == UID ? '<a href="' . DC_URL . 'admin/article.php?action=edit&gid=' . $logid . '" target="_blank"><span class="iconfont icon-edit"></span></a>' : '';
    echo $editflg;
}

?>
<?php
/**
 * 文章详情页：分类
 */
function blog_sort($sortID)
{
    $Sort_Model = new Sort_Model();
    $r = $Sort_Model->getOneSortById($sortID);
    $sortName = isset($r['sortname']) ? $r['sortname'] : '';
?>
    <?php if (!empty($sortName)) { ?>
        <a href="<?= htmlspecialchars(Url::blogSort($sortID), ENT_QUOTES) ?>"><?= blog_sort_icon_html($r, 'blog-sort-icon blog-meta-sort-icon') ?><?= $sortName ?></a>
<?php }
} ?>
<?php
/**
 * 首页文章列表：分类
 */
function bloglist_sort($sortID)
{
    $Sort_Model = new Sort_Model();
    $r = $Sort_Model->getOneSortById($sortID);
    $sortName = isset($r['sortname']) ? $r['sortname'] : '';
?>
    <?php if (!empty($sortName)) { ?>
        <span class="loglist-sort">
            <a href="<?= htmlspecialchars(Url::blogSort($sortID), ENT_QUOTES) ?>"><?= blog_sort_icon_html($r, 'blog-sort-icon blog-meta-sort-icon') ?><?= $sortName ?></a>
        </span>
<?php }
} ?>
<?php
/**
 * 首页文章列表和文章详情页：标签
 */
function blog_tag($blogid)
{
    $tag_model = new Tag_Model();
    $tag_ids = $tag_model->getTagIdsFromBlogId($blogid);
    $tag_names = $tag_model->getNamesFromIds($tag_ids);
    if (!empty($tag_names)) {
        $tag = '';
        foreach ($tag_names as $value) {
            $tagUrl = htmlspecialchars(Url::tag(rawurlencode($value)), ENT_QUOTES);
            $tagName = htmlspecialchars($value, ENT_QUOTES);
            $tag .= "    <a href=\"" . $tagUrl . "\" class=\"tags\" title=\"标签\">#" . $tagName . '</a>';
        }
        echo $tag;
    }
}

?>
<?php
/**
 * 首页文章列表和文章详情页：作者
 */
function blog_author($uid)
{
    $uid = (int)$uid;
    if ($uid <= 0) {
        echo '<span>匿名</span>';
        return;
    }
    $User_Model = new User_Model();
    $user_info = $User_Model->getOneUser($uid);
    $author = !empty($user_info['nickname']) ? $user_info['nickname'] : '匿名';
    echo '<a href="' . htmlspecialchars(Url::author($uid), ENT_QUOTES) . '">' . htmlspecialchars($author, ENT_QUOTES) . '</a>';
}

?>
<?php
/**
 * 文章详情页：相邻文章
 */
function neighbor_log($neighborLog)
{
    $prevLog = !empty($neighborLog['prevLog']) ? $neighborLog['prevLog'] : null;
    $nextLog = !empty($neighborLog['nextLog']) ? $neighborLog['nextLog'] : null;
    ?>
    <?php if ($prevLog || $nextLog): ?>
        <nav class="neighbor-log" aria-label="相邻文章">
            <?php if ($prevLog): ?>
                <a class="neighbor-item prev-log" href="<?= htmlspecialchars(Url::log($prevLog['gid']), ENT_QUOTES) ?>" title="上一篇：<?= htmlspecialchars($prevLog['title'], ENT_QUOTES) ?>">
                    <span class="neighbor-label"><i class="ri-arrow-left-s-line"></i>上一篇</span>
                    <strong><?= htmlspecialchars($prevLog['title'], ENT_QUOTES) ?></strong>
                </a>
            <?php endif ?>
            <?php if ($nextLog): ?>
                <a class="neighbor-item next-log" href="<?= htmlspecialchars(Url::log($nextLog['gid']), ENT_QUOTES) ?>" title="下一篇：<?= htmlspecialchars($nextLog['title'], ENT_QUOTES) ?>">
                    <span class="neighbor-label">下一篇<i class="ri-arrow-right-s-line"></i></span>
                    <strong><?= htmlspecialchars($nextLog['title'], ENT_QUOTES) ?></strong>
                </a>
            <?php endif ?>
        </nav>
    <?php endif ?>
<?php } ?>
<?php
/**
 * 文章详情页：评论列表
 */
function blog_comments($comments, $comnum)
{
    extract($comments);
    $comments = isset($comments) && is_array($comments) ? $comments : [];
    $commentStacks = isset($commentStacks) && is_array($commentStacks) ? $commentStacks : [];
    $commentPageUrl = isset($commentPageUrl) ? $commentPageUrl : '';
    if (!$commentStacks): ?>
        <div class="comment-empty"><i class="ri-chat-1-line"></i><span>还没有评论，欢迎留下第一条想法。</span></div>
        <?php return; ?>
    <?php endif ?>
    <div class="comment-header"><b>全部评论</b></div>
    <?php
    foreach ($commentStacks as $cid):
        if (empty($comments[$cid])) {
            continue;
        }
        $comment = $comments[$cid];
    ?>
        <div class="comment" id="<?= (int)$comment['cid'] ?>">
            <?php
            $avatar = getEmUserAvatar($comment['uid'], $comment['mail']);
            ?>
            <div class="avatar"><img src="<?= htmlspecialchars($avatar, ENT_QUOTES) ?>" alt="avatar" /></div>
            <div class="comment-infos">
                <div class="arrow"></div>
                <b><?= $comment['poster'] ?> </b><span class="comment-time"><?= htmlspecialchars($comment['date'], ENT_QUOTES) ?></span>
                <div class="comment-content"><?= $comment['content'] ?></div>
                <div class="comment-reply">
                    <span class="com-reply">回复</span>
                </div>
            </div>
            <?php blog_comments_children($comments, isset($comment['children']) ? $comment['children'] : []) ?>
        </div>
    <?php endforeach ?>
    <div id="pagenavi">
        <?= $commentPageUrl ?>
    </div>
<?php } ?>
<?php
/**
 * 文章详情页：子评论
 */
function blog_comments_children($comments, $children)
{
    $comments = is_array($comments) ? $comments : [];
    $children = is_array($children) ? $children : [];
    foreach ($children as $child):
        if (empty($comments[$child])) {
            continue;
        }
        $comment = $comments[$child];
?>
        <div class="comment comment-children" id="<?= (int)$comment['cid'] ?>">
            <?php
            $avatar = getEmUserAvatar($comment['uid'], $comment['mail']);
            ?>
            <div class="avatar"><img src="<?= htmlspecialchars($avatar, ENT_QUOTES) ?>" alt="commentator" /></div>
            <div class="comment-infos">
                <div class="arrow"></div>
                <b><?= $comment['poster'] ?> </b><span class="comment-time"><?= htmlspecialchars($comment['date'], ENT_QUOTES) ?></span>
                <div class="comment-content"><?= $comment['content'] ?></div>
                <?php if ((int)($comment['level'] ?? 0) < 4): ?>
                    <div class="comment-reply">
                        <span class="com-reply comment-replay-btn">回复</span>
                    </div>
                <?php endif ?>
            </div>
            <?php blog_comments_children($comments, isset($comment['children']) ? $comment['children'] : []) ?>
        </div>
    <?php endforeach ?>
<?php } ?>
<?php
/**
 * 文章详情页：评论表单
 */
function blog_comments_post($logid, $ckname, $ckmail, $ckurl, $verifyCode, $allow_remark)
{
    $isLoginComment = Option::get('login_comment');
    $commentLoginRedirect = function_exists('dcGetCurrentUrl') ? dcGetCurrentUrl() : DC_URL . ltrim($_SERVER['REQUEST_URI'] ?? '', '/');
    $commentLoginUrl = function_exists('dcGetUserLoginUrl') ? dcGetUserLoginUrl($commentLoginRedirect) : DC_URL . 'user/account.php?action=signin';
    $commentEnabled = $allow_remark == 'y';
    if (!$commentEnabled): ?>
        <div class="comment-closed"><i class="ri-lock-line"></i><span>当前文章评论已关闭。</span></div>
        <?php return; ?>
    <?php endif ?>
    <div class="comment-post" id="comment-post">
        <form class="commentform" method="post" name="commentform" action="<?= htmlspecialchars(DC_URL . 'index.php?action=addcom', ENT_QUOTES) ?>" id="commentform">
            <input type="hidden" name="gid" value="<?= (int)$logid ?>" />
            <input type="hidden" name="token" value="<?= htmlspecialchars(LoginAuth::genToken(), ENT_QUOTES) ?>" />
            <textarea class="form-control log_comment" name="comment" id="comment" rows="10" tabindex="4" placeholder="撰写评论，理性交流，友善表达" required></textarea>
            <?php if (User::isVisitor() && $isLoginComment === 'n'): ?>
                <div class="comment-info" id="comment-info">
                    <input class="form-control com_control comment-name" id="info_n" autocomplete="off" type="text" name="comname" maxlength="49"
                        value="<?= htmlspecialchars($ckname, ENT_QUOTES) ?>" size="22"
                        tabindex="1" placeholder="昵称*" required />
                    <input class="form-control com_control comment-mail" id="info_m" autocomplete="off" type="email" name="commail" maxlength="128"
                        value="<?= htmlspecialchars($ckmail, ENT_QUOTES) ?>" size="22"
                        tabindex="2" placeholder="邮箱" />
                </div>
            <?php endif ?>
            <span class="com_submit_p">
                <?php if (User::isVisitor() && $isLoginComment === 'y'): ?>
                    请先 <a href="<?= htmlspecialchars($commentLoginUrl, ENT_QUOTES) ?>">登录</a> 再评论
                <?php else: ?>
                    <input class="btn" <?php if ($verifyCode != "") { ?> type="button" data-toggle="modal" data-target="#myModal" <?php } else { ?> type="submit" <?php } ?>
                        id="comment_submit" value="发布评论" tabindex="6" />
                <?php endif; ?>
            </span>
            <?php if ($verifyCode != "") { ?>
                <div class="modal" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content blog-verify-modal-content">
                            <input type="hidden" id="blog_url" value="<?= htmlspecialchars(DC_URL, ENT_QUOTES) ?>" />
                            <div class="modal-header blog-verify-modal-header">输入验证码</div>
                            <?= $verifyCode ?>
                            <div class="modal-footer blog-verify-modal-footer">
                                <button type="button" class="btn" id="close-modal" data-dismiss="modal">关闭</button>
                                <button type="submit" class="btn" id="comment_submit2">提交</button>
                            </div>
                        </div>
                    </div>
                    <div class="lock-screen"></div>
                </div>
            <?php } ?>
            <input type="hidden" name="pid" id="comment-pid" value="0" tabindex="1" />
        </form>
    </div>
<?php } ?>
<?php
/**
 * 判断函数：是否是首页
 */
function blog_tool_ishome()
{
    if (DC_URL . trim(Dispatcher::setPath(), '/') == DC_URL) {
        return true;
    } else {
        return FALSE;
    }
}
?>
<?php
function getEmUserAvatar($uid, $mail)
{
    $avatar = '';
    if ($uid) {
        $userModel = new User_Model();
        $user = $userModel->getOneUser($uid);
        $avatar = $user && !empty($user['photo']) ? User::getAvatar($user['photo']) : '';
    } elseif ($mail) {
        $avatar = getGravatar($mail);
    }
    return $avatar ?: DC_URL . "admin/views/images/avatar.svg";
}
?>