<?php

/**
 * 自建页面模板
 */
defined('DC_ROOT') || exit('access denied!');
$pageTitle = htmlspecialchars(htmlspecialchars_decode((string)($log_title ?? ''), ENT_QUOTES), ENT_QUOTES);
?>
<article class="container blog-container">
    <div class="row">
        <div class="column-big log-con " id="page">
            <?php if (function_exists('blog_breadcrumbs')) blog_breadcrumbs($logData); ?>
            <h1 class="page-title"><?= $pageTitle ?></h1>
            <div class="markdown">
                <?= function_exists('blog_lazy_content') ? blog_lazy_content($log_content) : $log_content ?>
            </div>
            <?php blog_comments_post($logid, $ckname, $ckmail, $ckurl, $verifyCode, $allow_remark) ?>
            <?php blog_comments($comments, $comnum) ?>
        </div>
        <?php
        include View::getBlogView('side');
        ?>
    </div>
</article>
<?php
include View::getBlogView('footer');
?>