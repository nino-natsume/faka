<?php

/**
 * 阅读文章页面
 */
defined('DC_ROOT') || exit('access denied!');
$safeLogTitle = htmlspecialchars(htmlspecialchars_decode((string)($log_title ?? ''), ENT_QUOTES), ENT_QUOTES);
$readingStats = function_exists('blog_reading_stats') ? blog_reading_stats($log_content ?? '') : ['words' => 0, 'minutes' => 1, 'label' => '约 1 分钟'];
$shareUrl = htmlspecialchars(Url::log($logid), ENT_QUOTES);
$blogDetailShowDate = Option::get('blog_detail_show_date') === 'n' ? false : true;
$blogDetailShowReadingTime = Option::get('blog_detail_show_reading_time') === 'n' ? false : true;
$blogDetailShowAuthor = Option::get('blog_detail_show_author') === 'n' ? false : true;
$blogDetailShowCategory = Option::get('blog_detail_show_category') === 'n' ? false : true;
$blogDetailShowViews = Option::get('blog_detail_show_views') === 'n' ? false : true;
$blogDetailShowCommentsCount = Option::get('blog_detail_show_comments_count') === 'n' ? false : true;
$blogDetailShowTags = Option::get('blog_detail_show_tags') === 'n' ? false : true;
$blogDetailShowShare = Option::get('blog_detail_show_share') === 'n' ? false : true;
$blogDetailShowAuthorCard = Option::get('blog_detail_show_author_card') === 'n' ? false : true;
$blogDetailShowRelated = Option::get('blog_detail_show_related') === 'n' ? false : true;
$blogDetailShowNeighbor = Option::get('blog_detail_show_neighbor') === 'n' ? false : true;
$blogDetailShowComments = Option::get('blog_detail_show_comments') === 'n' ? false : true;
$blogDetailShowMeta = $blogDetailShowDate || $blogDetailShowReadingTime || $blogDetailShowAuthor || $blogDetailShowCategory || $blogDetailShowViews || $blogDetailShowCommentsCount;
$blogDetailHasSide = $blogDetailShowRelated || $blogDetailShowComments;
?>
<main class="container blog-container blog-detail-container">
    <div class="blog-detail-layout<?= $blogDetailHasSide ? ' blog-detail-has-side' : '' ?>">
        <article class="log-con blog-detail-main">
            <span class="back-top mh" onclick="history.go(-1);">&laquo;</span>
            <?php if (function_exists('blog_breadcrumbs')) blog_breadcrumbs($logData); ?>
            <h1 class="log-title"><?php topflg($top) ?><?= $safeLogTitle ?></h1>
            <?php if ($blogDetailShowMeta): ?>
            <p class="date blog-meta blog-post-meta">
                <?php if ($blogDetailShowDate): ?>
                    <span class="blog-meta-item"><i class="ri-time-line"></i><time><?= date('Y-n-j H:i', $date) ?></time></span>
                <?php endif ?>
                <?php if ($blogDetailShowReadingTime): ?>
                    <span class="blog-meta-item blog-reading-time" title="约 <?= (int)$readingStats['words'] ?> 字"><i class="ri-hourglass-2-line"></i>预计阅读：<?= htmlspecialchars($readingStats['label'], ENT_QUOTES) ?></span>
                <?php endif ?>
                <?php if ($blogDetailShowAuthor): ?>
                    <span class="blog-meta-item"><i class="ri-user-line"></i><?php blog_author($author) ?></span>
                <?php endif ?>
                <?php if ($blogDetailShowCategory && !empty($sortid) && (int)$sortid > 0): ?>
                    <span class="blog-meta-item"><i class="ri-folder-line"></i><?php blog_sort($sortid) ?></span>
                <?php endif ?>
                <?php if ($blogDetailShowViews): ?>
                    <span class="blog-meta-item"><i class="ri-eye-line"></i>阅读：<?= (int)$views ?></span>
                <?php endif ?>
                <?php if ($blogDetailShowCommentsCount): ?>
                    <span class="blog-meta-item"><i class="ri-chat-3-line"></i>评论：<?= (int)$comnum ?></span>
                <?php endif ?>
                <span class="blog-meta-item"><?php editflg($logid, $author) ?></span>
            </p>
            <?php endif ?>
            <hr class="bottom-5" />
            <div class="markdown" id="dcshopEchoLog"><?= function_exists('blog_lazy_content') ? blog_lazy_content($log_content) : $log_content ?></div>
            <?php if ($blogDetailShowTags): ?>
                <div class="blog-post-tags"><?php blog_tag($logid) ?></div>
            <?php endif ?>

            <?php if ($blogDetailShowShare): ?>
            <div class="blog-post-share" id="blogPostShare" data-title="<?= $safeLogTitle ?>" data-url="<?= $shareUrl ?>">
                <span class="blog-share-label"><i class="ri-share-line"></i>分享本文</span>
                <button type="button" class="blog-share-btn" data-share-action="native"><i class="ri-share-forward-line"></i>分享</button>
                <button type="button" class="blog-share-btn" data-share-action="copy"><i class="ri-link"></i>复制链接</button>
                <button type="button" class="blog-share-btn" data-share-action="qrcode"><i class="ri-qr-code-line"></i>二维码</button>
            </div>

            <div class="blog-share-qrcode-modal" id="blogShareQrModal" aria-hidden="true">
                <div class="blog-share-qrcode-mask" data-share-action="close-qrcode"></div>
                <div class="blog-share-qrcode-dialog" role="dialog" aria-modal="true" aria-labelledby="blogShareQrTitle">
                    <button type="button" class="blog-share-qrcode-close" data-share-action="close-qrcode" aria-label="关闭二维码"><i class="ri-close-line"></i></button>
                    <div class="blog-share-qrcode-icon"><i class="ri-qr-code-line"></i></div>
                    <div class="blog-share-qrcode-title" id="blogShareQrTitle">扫码阅读本文</div>
                    <div class="blog-share-qrcode-desc">使用微信、浏览器或任意扫码工具打开文章链接</div>
                    <div class="blog-share-qrcode-box" id="blogShareQrcode"></div>
                    <input type="text" class="blog-share-qrcode-url" value="<?= $shareUrl ?>" readonly onclick="this.select()">
                </div>
            </div>
            <?php endif ?>

            <?php if ($blogDetailShowAuthorCard && function_exists('blog_author_card')) blog_author_card($author, $logData); ?>

            <?php if ($blogDetailShowNeighbor) neighbor_log($neighborLog) ?>

            <div style="clear:both;"></div>
        </article>

        <?php if ($blogDetailShowRelated || $blogDetailShowComments): ?>
            <aside class="blog-detail-side" aria-label="文章互动侧栏">
                <?php if ($blogDetailShowComments): ?>
                    <section class="blog-comments-section" id="comments" aria-labelledby="blogCommentsTitle">
                        <div class="blog-comments-head">
                            <div>
                                <span class="blog-comments-kicker"><i class="ri-chat-smile-3-line"></i>COMMENTS</span>
                                <h2 id="blogCommentsTitle">评论互动</h2>
                            </div>
                            <span class="blog-comments-count"><?= (int)$comnum ?> 条评论</span>
                        </div>
                        <?php blog_comments_post($logid, $ckname, $ckmail, $ckurl, $verifyCode, $allow_remark) ?>
                        <?php blog_comments($comments, $comnum) ?>
                    </section>
                <?php endif ?>

                <?php if ($blogDetailShowRelated): ?>
                    <?php
                    ob_start();
                    doAction('log_related', $logData);
                    $blogRelatedOutput = ob_get_clean();
                    if (trim($blogRelatedOutput) !== '') {
                        echo $blogRelatedOutput;
                    } elseif (function_exists('blog_related_posts')) {
                        blog_related_posts($logData, 4);
                    }
                    ?>
                <?php endif ?>
            </aside>
        <?php endif ?>
    </div>
</main>

<?php include View::getBlogView('footer') ?>