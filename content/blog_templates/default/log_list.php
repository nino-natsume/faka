<?php

/**
 * 首页模板
 */
defined('DC_ROOT') || exit('access denied!');
$blogTplUrl = defined('BLOG_TEMPLATE_URL') ? BLOG_TEMPLATE_URL : (DC_URL . 'content/blog_templates/default/');
if (!preg_match('#^(https?:)?//#i', $blogTplUrl) && strpos($blogTplUrl, '/') !== 0 && strpos($blogTplUrl, './') !== 0 && strpos($blogTplUrl, '../') !== 0) {
    $blogTplUrl = rtrim(DC_URL, '/') . '/' . ltrim($blogTplUrl, '/');
}
$blogDefaultTemplateUrl = function ($path = '') use ($blogTplUrl) {
    return rtrim($blogTplUrl, '/') . '/' . ltrim(str_replace('\\', '/', (string)$path), '/');
};
$blogDefaultBannerItems = function () use ($blogDefaultTemplateUrl) {
    $items = [];
    for ($i = 1; $i <= 4; $i++) {
        $items[] = [
            'img' => $blogDefaultTemplateUrl('images/img' . $i . '.png'),
            'title' => '',
            'url' => '',
            'newtab' => 'y',
            'enabled' => 'y',
        ];
    }
    return $items;
};
$blogListLayout = Option::get('blog_list_layout');
$blogListLayout = in_array($blogListLayout, ['default', 'compact', 'simple'], true) ? $blogListLayout : 'compact';
$blogListShowCover = Option::get('blog_list_show_cover') === 'n' ? false : true;
$blogListCoverHeight = max(120, min(420, (int)(Option::get('blog_list_cover_height') ?: 205)));
$blogListShowSummary = Option::get('blog_list_show_summary') === 'n' ? false : true;
$blogListSummaryLength = max(60, min(500, (int)(Option::get('blog_list_summary_length') ?: 180)));
$blogListShowCategory = Option::get('blog_list_show_category') === 'n' ? false : true;
$blogListShowAuthor = Option::get('blog_list_show_author') === 'n' ? false : true;
$blogListShowDate = Option::get('blog_list_show_date') === 'n' ? false : true;
$blogListShowTags = Option::get('blog_list_show_tags') === 'n' ? false : true;
$blogListShowReadmore = Option::get('blog_list_show_readmore') === 'n' ? false : true;
$blogListShowStats = Option::get('blog_list_show_stats') === 'n' ? false : true;
$blogListShowMeta = $blogListShowCategory || $blogListShowAuthor || $blogListShowDate;
$blogSidebarShow = Option::get('blog_sidebar_show') === 'n' ? false : true;
$blogSidebarPosition = Option::get('blog_sidebar_position') === 'left' ? 'left' : 'right';
$blogSidebarSticky = Option::get('blog_sidebar_sticky') === 'y';
$blogSidebarMobileShow = Option::get('blog_sidebar_mobile_show') === 'n' ? false : true;
$blogSidebarCardStyle = Option::get('blog_sidebar_card_style');
$blogSidebarCardStyle = in_array($blogSidebarCardStyle, ['default', 'compact', 'clean'], true) ? $blogSidebarCardStyle : 'default';
$blogCurrentListPage = isset($page) ? max(1, (int)$page) : max(1, Input::getIntVar('page', 1));
$blogListIsHomeContext = empty($sortid)
    && (!isset($tag) || trim((string)$tag) === '')
    && empty($record)
    && (empty($author) || !empty($logid))
    && (!isset($keywordRaw) || trim((string)$keywordRaw) === '' || Input::getStrVar('type') !== 'blog');
$blogListIsFiltered = $blogCurrentListPage > 1
    || (!empty($sortid) && (int)$sortid > 0)
    || (isset($tag) && trim((string)$tag) !== '')
    || (!empty($record))
    || (!empty($author) && empty($logid))
    || (isset($keywordRaw) && trim((string)$keywordRaw) !== '' && Input::getStrVar('type') === 'blog');
$blogPageUrls = $blogListIsHomeContext && function_exists('dcGetBlogListPaginationUrls')
    ? dcGetBlogListPaginationUrls()
    : ['base' => isset($pageurl) ? (string)$pageurl : Url::blogPage(), 'home' => function_exists('dcGetBlogHomeUrl') ? dcGetBlogHomeUrl() : (DC_URL . 'blog')];
$page_url = pagination(
    isset($lognum) ? (int)$lognum : 0,
    isset($index_lognum) ? (int)$index_lognum : 1,
    $blogCurrentListPage,
    isset($blogPageUrls['base']) ? (string)$blogPageUrls['base'] : (isset($pageurl) ? (string)$pageurl : Url::blogPage())
);
$blogLayoutClasses = [
    'blog-sidebar-layout',
    'blog-sidebar-' . $blogSidebarPosition,
    $blogSidebarShow ? 'blog-sidebar-enabled' : 'blog-sidebar-hidden',
    $blogSidebarSticky ? 'blog-sidebar-sticky' : '',
    $blogSidebarMobileShow ? '' : 'blog-sidebar-mobile-hide',
    'blog-sidebar-style-' . $blogSidebarCardStyle,
];
?>
<main class="container blog-container <?= htmlspecialchars(trim(implode(' ', $blogLayoutClasses)), ENT_QUOTES) ?>">
    <div class="row">
        <div class="column-big<?= $blogSidebarShow ? '' : ' column-full' ?>">
            <?php
            if (function_exists('blog_breadcrumbs')) {
                blog_breadcrumbs([
                    'page' => isset($page) ? (int)$page : 1,
                    'sortid' => isset($sortid) ? (int)$sortid : 0,
                    'tag' => isset($tag) ? $tag : '',
                    'record' => isset($record) ? $record : '',
                    'author' => isset($author) && empty($logid) ? (int)$author : 0,
                    'keywordRaw' => isset($keywordRaw) ? $keywordRaw : null,
                ]);
            }
            ?>
            <?php
            $isSafeSlideUrl = function ($url) {
                $url = trim((string)$url);
                return $url === ''
                    || preg_match('#^(https?:)?//#i', $url)
                    || strpos($url, '/') === 0
                    || strpos($url, './') === 0
                    || strpos($url, '../') === 0;
            };
            $decodeBlogBannerItems = function ($raw) {
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
            };
            $slides = [];
            $blogBannerShow = Option::get('blog_banner_show');
            $hasBlogBannerSetting = $blogBannerShow !== '' && $blogBannerShow !== null;
            if ($blogBannerShow === 'y' || !$hasBlogBannerSetting) {
                $rawBlogBannerItems = Option::get('blog_banner_items');
                $blogBannerItems = $decodeBlogBannerItems($rawBlogBannerItems);
                if (empty($blogBannerItems) && ($rawBlogBannerItems === '' || $rawBlogBannerItems === null)) {
                    $blogBannerItems = $blogDefaultBannerItems();
                }
                foreach ($blogBannerItems as $item) {
                    $slideImg = isset($item['img']) ? trim((string)$item['img']) : '';
                    $slideTitle = isset($item['title']) ? strip_tags((string)$item['title']) : '';
                    $slideLink = isset($item['url']) ? trim((string)$item['url']) : (isset($item['link']) ? trim((string)$item['link']) : '');
                    $slideNewtab = (isset($item['newtab']) && $item['newtab'] === 'n') ? 'n' : 'y';
                    $slideEnabled = (isset($item['enabled']) && $item['enabled'] === 'n') ? 'n' : 'y';
                    if ($slideEnabled === 'n' || $slideImg === '' || !$isSafeSlideUrl($slideImg) || !$isSafeSlideUrl($slideLink)) {
                        continue;
                    }
                    $slides[] = [
                        'img' => $slideImg,
                        'title' => $slideTitle,
                        'link' => $slideLink !== '' ? $slideLink : '#',
                        'newtab' => $slideNewtab,
                    ];
                }
            }
            $blogBannerHeight = max(120, min(600, (int)(Option::get('blog_banner_height') ?: 350)));
            $blogBannerMobileHeight = max(100, min(420, (int)(Option::get('blog_banner_mobile_height') ?: 200)));
            $blogBannerSpeed = max(500, min(10000, (int)(Option::get('blog_banner_speed') ?: 3000)));
            $blogBannerAnimation = Option::get('blog_banner_animation') === 'slide' ? 'slide' : 'fade';
            $blogBannerCount = count($slides);
            if (!$blogListIsFiltered && !empty($slides)) : ?>
                <div class="slideshow-container blog-slideshow blog-slideshow-<?= htmlspecialchars($blogBannerAnimation, ENT_QUOTES) ?>" id="blogSlideshow" data-speed="<?= (int)$blogBannerSpeed ?>" style="--blog-slide-height:<?= (int)$blogBannerHeight ?>px;--blog-slide-mobile-height:<?= (int)$blogBannerMobileHeight ?>px;">
                    <div class="blog-slideshow-pin" aria-hidden="true"><i class="ri-pushpin-2-line"></i></div>
                    <div class="blog-slideshow-kicker"><i class="ri-image-line"></i>精选手札</div>
                    <div class="blog-slideshow-stage">
                        <?php
                        foreach ($slides as $slideIndex => $slide):
                            $target = $slide['newtab'] === 'y' && $slide['link'] !== '#' ? ' target="_blank" rel="noopener noreferrer"' : '';
                            $slideTitle = trim((string)$slide['title']);
                        ?>
                            <div class="mySlides <?= $blogBannerAnimation === 'fade' ? 'fade' : 'slide' ?>">
                                <a class="blog-slide-link" href="<?= htmlspecialchars($slide['link'], ENT_QUOTES) ?>"<?= $target ?> aria-label="<?= htmlspecialchars($slideTitle !== '' ? $slideTitle : '查看轮播图', ENT_QUOTES) ?>" draggable="false">
                                    <img src="<?= htmlspecialchars($slide['img'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($slideTitle, ENT_QUOTES) ?>" loading="<?= $slideIndex === 0 ? 'eager' : 'lazy' ?>" decoding="async" draggable="false">
                                    <span class="blog-slide-shade" aria-hidden="true"></span>
                                    <?php if ($slideTitle !== ''): ?>
                                        <span class="slideshow-text">
                                            <span class="slideshow-text-label">今日推荐</span>
                                            <strong><?= htmlspecialchars($slideTitle, ENT_QUOTES) ?></strong>
                                            <span class="slideshow-text-more">翻开看看 <i class="ri-arrow-right-up-line"></i></span>
                                        </span>
                                    <?php endif; ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($blogBannerCount > 1): ?>
                        <div class="blog-slideshow-dots" aria-label="轮播图分页">
                            <?php for ($i = 0; $i < $blogBannerCount; $i++): ?>
                                <button type="button" class="blog-slideshow-dot" aria-label="切换到第 <?= $i + 1 ?> 张"></button>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <script>
                    (function () {
                        var container = document.getElementById('blogSlideshow');
                        if (!container) return;
                        var slides = container.querySelectorAll('.mySlides');
                        var dots = container.querySelectorAll('.blog-slideshow-dot');
                        if (!slides.length) return;
                        var slideIndex = 0;
                        var timeoutID = null;
                        var speed = parseInt(container.getAttribute('data-speed'), 10) || 3000;
                        var stage = container.querySelector('.blog-slideshow-stage');
                        var dragStartX = 0;
                        var dragStartY = 0;
                        var dragDeltaX = 0;
                        var isDragging = false;
                        var didSwipe = false;
                        var swipeThreshold = 48;

                        function showSlide(index) {
                            if (index >= slides.length) index = 0;
                            if (index < 0) index = slides.length - 1;
                            for (var i = 0; i < slides.length; i++) {
                                slides[i].style.display = 'none';
                                slides[i].classList.remove('is-active');
                                if (dots[i]) {
                                    dots[i].classList.remove('is-active');
                                    dots[i].setAttribute('aria-current', 'false');
                                }
                            }
                            slideIndex = index;
                            slides[slideIndex].style.display = 'block';
                            slides[slideIndex].classList.add('is-active');
                            if (dots[slideIndex]) {
                                dots[slideIndex].classList.add('is-active');
                                dots[slideIndex].setAttribute('aria-current', 'true');
                            }
                            clearTimeout(timeoutID);
                            if (slides.length > 1) {
                                timeoutID = setTimeout(function () {
                                    showSlide(slideIndex + 1);
                                }, speed);
                            }
                        }

                        function dragPoint(event) {
                            var source = event.touches && event.touches.length ? event.touches[0] : (event.changedTouches && event.changedTouches.length ? event.changedTouches[0] : event);
                            return { x: source.clientX || 0, y: source.clientY || 0 };
                        }

                        function dragStart(event) {
                            if (slides.length < 2) return;
                            if (event.type === 'mousedown' && event.button !== 0) return;
                            if (event.cancelable) {
                                event.preventDefault();
                            }
                            var point = dragPoint(event);
                            dragStartX = point.x;
                            dragStartY = point.y;
                            dragDeltaX = 0;
                            isDragging = true;
                            didSwipe = false;
                            container.classList.add('is-dragging');
                            clearTimeout(timeoutID);
                        }

                        function dragMove(event) {
                            if (!isDragging) return;
                            var point = dragPoint(event);
                            var deltaY = point.y - dragStartY;
                            dragDeltaX = point.x - dragStartX;
                            if (Math.abs(dragDeltaX) > 8 && Math.abs(dragDeltaX) > Math.abs(deltaY)) {
                                event.preventDefault();
                                var active = slides[slideIndex];
                                if (active) {
                                    active.style.transform = 'translateX(' + Math.max(-80, Math.min(80, dragDeltaX * 0.22)) + 'px)';
                                }
                            }
                        }

                        function dragEnd() {
                            if (!isDragging) return;
                            var active = slides[slideIndex];
                            if (active) {
                                active.style.transform = '';
                            }
                            container.classList.remove('is-dragging');
                            isDragging = false;
                            if (Math.abs(dragDeltaX) >= swipeThreshold) {
                                didSwipe = true;
                                showSlide(dragDeltaX < 0 ? slideIndex + 1 : slideIndex - 1);
                                setTimeout(function () { didSwipe = false; }, 80);
                            } else {
                                showSlide(slideIndex);
                            }
                        }

                        if (stage) {
                            stage.addEventListener('dragstart', function (event) {
                                event.preventDefault();
                            });
                            stage.addEventListener('mousedown', dragStart);
                            stage.addEventListener('mousemove', dragMove);
                            document.addEventListener('mouseup', dragEnd);
                            stage.addEventListener('mouseleave', dragEnd);
                            stage.addEventListener('touchstart', dragStart, { passive: true });
                            stage.addEventListener('touchmove', dragMove, { passive: false });
                            stage.addEventListener('touchend', dragEnd);
                            stage.addEventListener('touchcancel', dragEnd);
                            stage.addEventListener('click', function (event) {
                                if (didSwipe || Math.abs(dragDeltaX) >= swipeThreshold) {
                                    event.preventDefault();
                                    event.stopPropagation();
                                }
                            }, true);
                        }
                        for (var d = 0; d < dots.length; d++) {
                            (function (dotIndex) {
                                dots[dotIndex].addEventListener('click', function () { showSlide(dotIndex); });
                            })(d);
                        }
                        showSlide(0);
                    })();
                </script>
            <?php endif; ?>
            <?php doAction('index_loglist_top');
            if (!empty($logs)):
                foreach ($logs as $value):
                    $blogListHasCover = $blogListShowCover && !empty($value['log_cover']);
                    $blogListItemClass = 'shadow-theme bottom-5 blog-list-item blog-list-' . $blogListLayout . ($blogListHasCover ? '' : ' blog-list-no-cover');
                    $blogListTitleText = htmlspecialchars(htmlspecialchars_decode(strip_tags((string)$value['log_title']), ENT_QUOTES), ENT_QUOTES);
                    $blogListDate = !empty($value['date']) ? (int)$value['date'] : time();
                ?>
                    <article class="<?= htmlspecialchars($blogListItemClass, ENT_QUOTES) ?>" style="--blog-list-cover-height:<?= (int)$blogListCoverHeight ?>px;">
                        <div class="blog-card-tape" aria-hidden="true"></div>
                        <div class="blog-card-date" aria-hidden="true">
                            <strong><?= date('d', $blogListDate) ?></strong>
                            <span><?= date('m月', $blogListDate) ?></span>
                            <em><?= date('Y', $blogListDate) ?></em>
                        </div>
                        <div class="blog-card-main">
                            <div class="card-padding loglist-body">
                                <div class="blog-meta loglist-meta blog-card-kicker">
                                    <?php if ($blogListShowCategory && !empty($value['sortid']) && (int)$value['sortid'] > 0): ?>
                                        <span class="blog-meta-item blog-meta-sort"><?php bloglist_sort($value['sortid']) ?></span>
                                    <?php else: ?>
                                        <span class="blog-card-kicker-label"><i class="ri-quill-pen-line"></i>手札</span>
                                    <?php endif ?>
                                    <?php if ($blogListShowAuthor): ?>
                                        <span class="blog-meta-item"><i class="ri-user-line"></i><?php blog_author($value['author']) ?></span>
                                    <?php endif ?>
                                    <?php if ($blogListShowDate): ?>
                                        <span class="blog-meta-item"><i class="ri-time-line"></i><time><?= date('Y-n-j H:i', $blogListDate) ?></time></span>
                                    <?php endif ?>
                                    <?php if ($blogListShowStats): ?>
                                        <span class="log-count blog-meta-item">
                                            <a href="<?= htmlspecialchars($value['log_url'], ENT_QUOTES) ?>"><span class="iconfont icon-view"></span> <?= (int)$value['views'] ?></a>
                                            <a href="<?= htmlspecialchars($value['log_url'], ENT_QUOTES) ?>#comments"><i class="ri-chat-3-line"></i> <?= (int)$value['comnum'] ?></a>
                                        </span>
                                    <?php endif ?>
                                </div>
                                <h2 class="card-title">
                                    <a href="<?= htmlspecialchars($value['log_url'], ENT_QUOTES) ?>" class="loglist-title"><?= $value['log_title'] ?></a>
                                    <?php topflg($value['top'] ?? 'n', $value['sortop'] ?? 'n', isset($sortid) ? $sortid : '') ?>
                                </h2>
                                <?php if ($blogListShowSummary): ?>
                                    <div class="loglist-content markdown"><?php $listDesc = subContent($value['log_description'], $blogListSummaryLength, 1); echo function_exists('blog_lazy_content') ? blog_lazy_content($listDesc) : $listDesc; ?></div>
                                <?php endif ?>
                                <?php if ($blogListShowTags || $blogListShowReadmore): ?>
                                    <div class="blog-card-footer">
                                        <?php if ($blogListShowTags): ?>
                                            <div class="loglist-tag"><?php blog_tag($value['logid']) ?></div>
                                        <?php endif ?>
                                        <?php if ($blogListShowReadmore): ?>
                                            <div class="log-info">
                                                <a class="read-more" href="<?= htmlspecialchars($value['log_url'], ENT_QUOTES) ?>">继续翻阅 <i class="ri-arrow-right-up-line"></i></a>
                                            </div>
                                        <?php endif ?>
                                    </div>
                                <?php endif ?>
                            </div>
                            <?php if ($blogListHasCover) : ?>
                                <a class="loglist-cover" href="<?= htmlspecialchars($value['log_url'], ENT_QUOTES) ?>" aria-label="<?= $blogListTitleText ?>">
                                    <img src="<?= htmlspecialchars($value['log_cover'], ENT_QUOTES) ?>" alt="<?= $blogListTitleText ?>" class="rea-width" data-action="zoom" loading="lazy" decoding="async">
                                </a>
                            <?php else: ?>
                                <a class="loglist-cover loglist-cover-empty" href="<?= htmlspecialchars($value['log_url'], ENT_QUOTES) ?>" aria-label="<?= $blogListTitleText ?>">
                                    <span class="loglist-cover-empty-icon"><i class="ri-quill-pen-line"></i></span>
                                    <span>没有配图<br>也值得翻开</span>
                                </a>
                            <?php endif ?>
                        </div>
                    </article>
                <?php
                endforeach;
            else:
                ?>
                <p>抱歉，暂时还没有内容。</p>
            <?php endif ?>
            <div class="pagination bottom-5">
                <?= $page_url ?>
            </div>
        </div>
        <?php if ($blogSidebarShow) include View::getBlogView('side') ?>
    </div>
</main>

<?php include View::getBlogView('footer') ?>