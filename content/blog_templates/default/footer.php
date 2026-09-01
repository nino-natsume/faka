<?php

/**
 * 页面底部信息
 */
defined('DC_ROOT') || exit('access denied!');
$blogFooterShow = Option::get('blog_footer_show') === 'n' ? false : true;
$blogFooterCustomText = trim((string)(function_exists('blogDefaultTplOption')
    ? blogDefaultTplOption('blog_footer_custom_text', blogDefaultTplFooterCustomText(), '')
    : (Option::get('blog_footer_custom_text') ?: '')));
$blogFooterShowIcp = Option::get('blog_footer_show_icp') === 'n' ? false : true;
$blogFooterShowSystem = Option::get('blog_footer_show_system') === 'n' ? false : true;
$blogFooterLinksRaw = (string)(function_exists('blogDefaultTplOption')
    ? blogDefaultTplOption('blog_footer_links', blogDefaultTplFooterLinks(), '')
    : (Option::get('blog_footer_links') ?: ''));
$blogFooterLinks = [];
foreach (preg_split('/\r\n|\r|\n/', $blogFooterLinksRaw) as $line) {
    $parts = array_map('trim', explode('|', (string)$line, 3));
    if (count($parts) < 3 || $parts[1] === '' || $parts[2] === '') {
        continue;
    }
    $url = $parts[2];
    if (!preg_match('#^(https?:)?//#i', $url) && strpos($url, '/') !== 0 && stripos($url, 'mailto:') !== 0 && stripos($url, 'tel:') !== 0) {
        continue;
    }
    $blogFooterLinks[] = [
        'icon' => preg_replace('/[^\w\-\s]/', '', $parts[0]) ?: 'ri-links-line',
        'name' => strip_tags($parts[1]),
        'url' => $url,
    ];
}
?>
<?php
// 底部导航适配 - 博客页面也显示底部导航栏
$_blog_bottom_nav_view = View::getBottomNavView('render');
?>
<?php if ($blogFooterShow): ?>
<footer class="blog-footer">
    <div class="container footinfo">
        <div class="blog-footer-tape" aria-hidden="true"></div>
        <div class="blog-footer-stamp" aria-hidden="true"><i class="ri-quill-pen-line"></i></div>
        <div class="blog-footer-main">
            <div class="blog-footer-note">
                <div class="blog-footer-kicker"><i class="ri-leaf-line"></i>页脚便签</div>
                <?php if ($blogFooterCustomText !== ''): ?>
                    <div class="blog-footer-custom"><?= nl2br(htmlspecialchars($blogFooterCustomText, ENT_QUOTES)) ?></div>
                <?php else: ?>
                    <div class="blog-footer-custom blog-footer-custom-empty">把灵感留在纸上，把故事继续写下去。</div>
                <?php endif ?>
            </div>
            <?php if (!empty($blogFooterLinks)): ?>
                <nav class="blog-footer-links" aria-label="页脚链接">
                    <?php foreach ($blogFooterLinks as $footerLink): ?>
                        <a href="<?= htmlspecialchars($footerLink['url'], ENT_QUOTES) ?>" target="_blank" rel="noopener noreferrer">
                            <i class="<?= htmlspecialchars($footerLink['icon'], ENT_QUOTES) ?>"></i>
                            <span><?= htmlspecialchars($footerLink['name'], ENT_QUOTES) ?></span>
                        </a>
                    <?php endforeach ?>
                </nav>
            <?php endif ?>
        </div>
        <div class="blog-footer-bottom">
            <?php if ($blogFooterShowIcp && !empty($icp)): ?>
                <div class="blog-footer-icp">
                    <i class="ri-shield-check-line"></i>
                    <a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($icp, ENT_QUOTES) ?></a>
                </div>
            <?php endif ?>
            <?php if ($blogFooterShowSystem): ?>
                <div class="blog-footer-system"><?= $footer_info ?></div>
            <?php endif ?>
            <?php doAction('index_footer') ?>
        </div>
    </div>
</footer>
<?php else: ?>
    <?php doAction('index_footer') ?>
<?php endif ?>

<?php if (is_file($_blog_bottom_nav_view)): ?>
<style>
@media (max-width: 768px) {
    .blog-footer { padding-bottom: 70px; }
    body { padding-bottom: 70px; }
}
</style>
<?php include $_blog_bottom_nav_view; ?>
<?php endif; ?>

</body>

</html>