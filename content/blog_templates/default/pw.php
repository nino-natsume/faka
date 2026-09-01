<?php

/**
 * 加密文章输入密码页面
 */
defined('DC_ROOT') || exit('access denied!');

global $blogname;
$blogTplUrl = defined('BLOG_TEMPLATE_URL') ? BLOG_TEMPLATE_URL : (DC_URL . 'content/blog_templates/default/');
if (!preg_match('#^(https?:)?//#i', $blogTplUrl) && strpos($blogTplUrl, '/') !== 0 && strpos($blogTplUrl, './') !== 0 && strpos($blogTplUrl, '../') !== 0) {
    $blogTplUrl = rtrim(DC_URL, '/') . '/' . ltrim($blogTplUrl, '/');
}
$blogTplVersion = '1720327731';
$blogSiteName = function_exists('blogDefaultTplOption')
    ? blogDefaultTplOption('blog_site_name', blogDefaultTplSiteName(), ($blogname ?: Option::get('blogname')))
    : (Option::get('blog_site_name') ?: ($blogname ?: Option::get('blogname')));
$blogSiteName = $blogSiteName ?: 'Blog';
$blogLogo = Option::get('blog_logo') ?: (rtrim($blogTplUrl, '/') . '/images/logo.png');
if ($blogLogo !== '' && !preg_match('#^(https?:)?//#i', $blogLogo) && strpos($blogLogo, '/') !== 0 && strpos($blogLogo, './') !== 0 && strpos($blogLogo, '../') !== 0) {
    $blogLogo = '';
}
$blogHomeUrl = (Option::get('blog_title_link') === 'home') ? DC_URL : (function_exists('dcGetBlogHomeUrl') ? dcGetBlogHomeUrl() : DC_URL . 'blog');
$favicon = function_exists('_g') ? _g('favicon') : '';
$favicon = empty($favicon) ? (DC_URL . 'favicon.ico') : $favicon;
$hasWrongPassword = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
?>
<!doctype html>
<html lang="zh-cn" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="color-scheme" content="light dark">
    <title>请输入文章访问密码 - <?= htmlspecialchars($blogSiteName, ENT_QUOTES) ?></title>
    <link href="<?= htmlspecialchars($favicon, ENT_QUOTES) ?>" rel="icon">
    <link href="<?= htmlspecialchars($blogTplUrl, ENT_QUOTES) ?>css/style.css?v=<?= $blogTplVersion ?>&t=<?= Option::DC_VERSION_TIMESTAMP ?>" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="<?= DC_URL ?>admin/views/remixicon/remixicon.css">
    <script>
        (function () {
            var savedTheme = '';
            try { savedTheme = localStorage.getItem('theme') || ''; } catch (e) {}
            var systemDark = !!(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.setAttribute('data-theme', (savedTheme === 'dark' || savedTheme === 'light') ? savedTheme : (systemDark ? 'dark' : 'light'));
        })();
    </script>
</head>

<body class="blog-special-page">
    <main class="blog-special-wrap">
        <section class="blog-special-card">
            <a class="blog-special-brand" href="<?= htmlspecialchars($blogHomeUrl, ENT_QUOTES) ?>">
                <?php if ($blogLogo !== ''): ?>
                    <img src="<?= htmlspecialchars($blogLogo, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($blogSiteName, ENT_QUOTES) ?>">
                <?php else: ?>
                    <i class="ri-article-line"></i>
                <?php endif; ?>
                <span><?= htmlspecialchars($blogSiteName, ENT_QUOTES) ?></span>
            </a>
            <div class="blog-special-icon"><i class="ri-lock-password-line"></i></div>
            <h1 class="blog-special-title">请输入文章访问密码</h1>
            <p class="blog-special-desc">这篇文章已开启访问保护，请输入正确密码后继续阅读。</p>
            <?php if ($hasWrongPassword): ?>
                <div class="blog-special-error"><i class="ri-error-warning-line"></i> 密码不正确，请重新输入。</div>
            <?php endif; ?>
            <form class="blog-special-form" action="" method="post">
                <div class="blog-special-input-row">
                    <input type="password" id="logpwd" name="logpwd" required autofocus placeholder="请输入访问密码" autocomplete="current-password">
                    <button type="submit"><i class="ri-check-line"></i>提交</button>
                </div>
            </form>
            <div class="blog-special-actions">
                <a class="blog-special-btn ghost" href="<?= htmlspecialchars($blogHomeUrl, ENT_QUOTES) ?>"><i class="ri-home-5-line"></i>返回博客首页</a>
                <a class="blog-special-btn ghost" href="javascript:history.back(-1);"><i class="ri-arrow-left-line"></i>返回上一页</a>
            </div>
        </section>
    </main>
    <script src="<?= DC_URL ?>admin/views/js/jquery.min.3.5.1.js?v=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>
    <script src="<?= htmlspecialchars($blogTplUrl, ENT_QUOTES) ?>js/common_tpl.js?v=<?= $blogTplVersion ?>&t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>
</body>

</html>