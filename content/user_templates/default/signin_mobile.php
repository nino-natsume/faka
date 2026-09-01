<?php defined('DC_ROOT') || exit('access denied!'); ?>
<?php
$blogname = Option::get('blogname');
$__greetings = [
    '今天也是充满干劲的一天 ✨','欢迎回来，美好的一天从这里开始！',
    '保持微笑，好运自然来 😊','又是元气满满的一天！',
    '你的努力终将不负期望 💪','生活明朗，万物可爱 🌈',
    '星光不负赶路人 🌙','每一天都是新的开始 🌟',
];
$__greeting = $__greetings[array_rand($__greetings)];
$_login_icon = Option::get('personal_center_icon');
include __DIR__ . '/_auth_theme_vars.php';
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no,viewport-fit=cover">
    <title>登录 - <?= htmlspecialchars($blogname) ?></title>
    <link href="<?= getSiteFaviconUrl(DC_URL . 'admin/views/images/favicon.ico'); ?>" rel="shortcut icon">
    <link rel="stylesheet" href="<?= DC_URL ?>admin/views/layui-v2.11.6/layui/css/layui.css">
    <script src="<?= DC_URL ?>admin/views/layui-v2.11.6/layui/layui.js"></script>
    <script src="<?= DC_URL ?>admin/views/js/jquery.min.3.5.1.js"></script>
    <link rel="stylesheet" href="<?= DC_URL ?>admin/views/remixicon/remixicon.css">
    <?php doAction('login_head') ?>
<style>
:root {
    --c-primary: <?= $_auth_primary ?>;
    --c-primary-rgb: <?= $_auth_primary_rgb ?>;
    --c-primary-dark: <?= $_auth_primary_dark ?>;
    --c-bg: #f5f7fa;
    --c-card: #fff;
    --c-text: #1f2937;
    --c-sub: #86909c;
    --c-border: #e5e6eb;
    --c-green: #34c759;
}
*{margin:0;padding:0;box-sizing:border-box;}
html,body{height:100%;margin:0;}
body{
    font-family:-apple-system,BlinkMacSystemFont,"PingFang SC","Helvetica Neue",sans-serif;
    background:var(--c-bg);
    color:var(--c-text);
    -webkit-tap-highlight-color:transparent;
    padding-top:env(safe-area-inset-top,0);
}
.m-auth-page{
    height:100%;
    display:flex;
    flex-direction:column;
    overflow-y:auto;
    -webkit-overflow-scrolling:touch;
}
.m-topbar{
    position:fixed;top:0;left:0;right:0;z-index:200;
    display:flex;align-items:center;
    height:calc(50px + env(safe-area-inset-top,0px));
    padding-top:env(safe-area-inset-top,0px);
    padding-left:6px;padding-right:12px;
    background:transparent;
}
.m-topbar-back{
    display:flex;align-items:center;justify-content:center;
    width:40px;height:40px;border-radius:10px;
    color:var(--c-text);text-decoration:none;flex:0 0 auto;
    -webkit-tap-highlight-color:transparent;
}
.m-topbar-back:active{background:rgba(0,0,0,.05);}
.m-topbar-spacer{flex:1;}
.m-topbar-reg{
    font-size:15px;font-weight:600;color:var(--c-primary);
    text-decoration:none;flex:0 0 auto;padding:6px 0;
    -webkit-tap-highlight-color:transparent;
}
.m-topbar-reg:active{opacity:.7;}
.m-auth-header{
    padding:calc(52px + env(safe-area-inset-top,0px)) 28px 0;
    text-align:center;
}
.m-auth-logo{
    width:68px;height:68px;margin:0 auto 10px;border-radius:50%;
    background:linear-gradient(135deg,var(--c-primary),var(--c-primary-dark));
    display:flex;align-items:center;justify-content:center;
    color:#fff;font-size:26px;
    box-shadow:0 6px 18px rgba(var(--c-primary-rgb),.25);
}
.m-auth-logo img{width:100%;height:100%;object-fit:cover;border-radius:50%;}
.m-auth-site{font-size:18px;font-weight:700;color:var(--c-text);margin-bottom:2px;}
.m-auth-greeting{font-size:12px;color:var(--c-sub);}
.m-auth-body{
    padding:22px 30px 140px;
}
.m-auth-toggle{
    display:flex;
    justify-content:center;
    gap:20px;
    margin-top:6px;
}
.m-auth-toggle-item{
    width:46px;height:46px;border-radius:50%;border:none;
    font-size:19px;display:flex;align-items:center;justify-content:center;
    cursor:pointer;transition:all .2s;position:relative;
    -webkit-tap-highlight-color:transparent;
}
.m-auth-toggle-item[data-type="username"]{background:rgba(124,77,255,.1);color:#7c4dff;}
.m-auth-toggle-item[data-type="username"]:active{background:rgba(124,77,255,.2);}
.m-auth-toggle-item[data-type="tel"]{background:rgba(76,175,80,.1);color:#43a047;}
.m-auth-toggle-item[data-type="tel"]:active{background:rgba(76,175,80,.2);}
.m-auth-toggle-item[data-type="email"]{background:rgba(255,152,0,.1);color:#f57c00;}
.m-auth-toggle-item[data-type="email"]:active{background:rgba(255,152,0,.2);}
.m-auth-toggle-item .toggle-label{
    position:absolute;bottom:-18px;left:50%;transform:translateX(-50%);
    font-size:10px;color:var(--c-sub);white-space:nowrap;
}
.m-field{position:relative;margin-bottom:12px;}
.m-field .f-icon{
    position:absolute;left:14px;top:50%;transform:translateY(-50%);
    color:#c9cdd4;font-size:16px;pointer-events:none;
}
.m-field input{
    width:100%;height:40px;padding:0 42px 0 42px;
    border:1.5px solid var(--c-border);background:var(--c-card);border-radius:10px;
    font-size:16px;color:var(--c-text);outline:none;transition:all .2s;
    -webkit-appearance:none;
}
.m-field input::placeholder{color:#c9cdd4;}
.m-field input:focus{border-color:var(--c-primary);box-shadow:0 0 0 3px rgba(var(--c-primary-rgb),.1);}
.m-field .f-clear{
    position:absolute;right:12px;top:50%;transform:translateY(-50%);
    width:20px;height:20px;border-radius:50%;border:none;
    background:rgba(0,0,0,.08);color:#999;font-size:12px;line-height:20px;text-align:center;
    cursor:pointer;display:none;padding:0;
}
.m-field .f-clear.show{display:block;}
.m-field .f-eye{
    position:absolute;right:12px;top:50%;transform:translateY(-50%);
    color:#c9cdd4;font-size:18px;cursor:pointer;padding:4px;
    -webkit-tap-highlight-color:transparent;
}
.m-field .f-eye.active{color:var(--c-primary);}
.m-sms-row{display:flex;gap:8px;margin-bottom:12px;}
.m-sms-row .m-field{flex:1;margin:0;}
.m-sms-btn{
    flex:0 0 auto;height:40px;padding:0 16px;border:none;border-radius:10px;
    background:var(--c-primary);color:#fff;font-size:13px;font-weight:500;
    cursor:pointer;white-space:nowrap;transition:opacity .2s;
}
.m-sms-btn:disabled{opacity:.5;cursor:not-allowed;}
.m-captcha-row{display:flex;gap:8px;margin-bottom:12px;align-items:center;}
.m-captcha-row .m-field{flex:1;margin:0;}
.m-captcha-row img{height:40px;border-radius:10px;cursor:pointer;border:1px solid var(--c-border);}
.m-auth-row{
    display:flex;align-items:center;justify-content:space-between;
    margin:4px 0 20px;font-size:13px;color:var(--c-sub);
}
.m-auth-row a{color:var(--c-primary);text-decoration:none;}
.m-auth-row label{display:flex;align-items:center;gap:4px;cursor:pointer;user-select:none;font-size:12px;}
.m-auth-row label input{accent-color:var(--c-primary);width:14px;height:14px;flex-shrink:0;-webkit-appearance:none;appearance:none;border:1px solid var(--c-border);border-radius:3px;background:#fff;position:relative;}
.m-auth-row label input:checked{background:var(--c-primary);border-color:var(--c-primary);}
.m-auth-row label input:checked::after{content:'✓';position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#fff;font-size:10px;font-weight:700;}
.m-auth-btn{
    width:100%;height:45px;border:none;border-radius:10px;
    background:linear-gradient(135deg,var(--c-primary),var(--c-primary-dark));
    color:#fff;font-size:16px;font-weight:600;cursor:pointer;
    transition:all .2s;
    box-shadow:0 4px 16px rgba(var(--c-primary-rgb),.25);
}
.m-auth-btn:active{transform:scale(.98);box-shadow:0 2px 8px rgba(var(--c-primary-rgb),.2);}
.m-auth-bottom-bar{
    position:fixed;bottom:0;left:0;right:0;z-index:100;
    background:var(--c-bg);
    padding-bottom:env(safe-area-inset-bottom,0);
}
.m-auth-bottom-toggle{
    padding:14px 24px 30px;
    text-align:center;
}
.m-other-divider{
    display:flex;align-items:center;justify-content:center;gap:12px;
    margin-bottom:12px;color:var(--c-sub);font-size:12px;
}
.m-other-divider::before,.m-other-divider::after{
    content:'';width:48px;height:.5px;background:#d9d9d9;
}
.m-other-divider span{white-space:nowrap;}
.display-hide{display:none!important;}
.m-auth-agreement-line{
    padding:8px 24px 10px;
    text-align:center;
    font-size:12px;color:var(--c-sub);
    display:flex;align-items:center;justify-content:center;gap:4px;
}
.m-auth-agreement-line input{accent-color:var(--c-primary);width:14px;height:14px;flex-shrink:0;-webkit-appearance:none;appearance:none;border:1px solid var(--c-border);border-radius:3px;background:#fff;position:relative;}
.m-auth-agreement-line input:checked{background:var(--c-primary);border-color:var(--c-primary);}
.m-auth-agreement-line input:checked::after{content:'✓';position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#fff;font-size:10px;font-weight:700;}
.m-auth-agreement-line a{color:var(--c-primary);text-decoration:none;}

/* 现代 App 风格输入框 */
.m-field{position:relative;margin-bottom:14px;}
.m-field .f-icon{
    position:absolute;left:16px;top:50%;transform:translateY(-50%);
    width:24px;height:24px;display:flex;align-items:center;justify-content:center;
    color:rgba(var(--c-primary-rgb),.68);font-size:18px;pointer-events:none;z-index:2;
    transition:color .2s ease, transform .2s ease;
}
.m-field:focus-within .f-icon{color:var(--c-primary);transform:translateY(-50%) scale(1.04);}
.m-field input{
    width:100%;height:45px;padding:0 48px 0 50px;
    border:1px solid rgba(31,41,55,.06);border-radius:10px;
    background:linear-gradient(180deg,rgba(255,255,255,.98),rgba(255,255,255,.9));
    box-shadow:0 10px 26px rgba(31,52,88,.07), inset 0 1px 0 rgba(255,255,255,.9);
    color:var(--c-text);font-size:15px;font-weight:500;letter-spacing:.1px;outline:none;
    caret-color:var(--c-primary);transition:border-color .2s ease, box-shadow .2s ease, background .2s ease;
    -webkit-appearance:none;
}
.m-field input::placeholder{color:#aeb8c6;font-weight:400;}
.m-field input:focus{
    border-color:rgba(var(--c-primary-rgb),.45);background:#fff;
    box-shadow:0 0 0 4px rgba(var(--c-primary-rgb),.10), 0 14px 32px rgba(var(--c-primary-rgb),.12);
}
.m-field input[readonly]{
    background:linear-gradient(180deg,#f6f8fb,#eef2f7)!important;
    color:#8a95a5!important;border-color:rgba(148,163,184,.16);
}
.m-field .f-clear{
    position:absolute;right:14px;top:50%;transform:translateY(-50%);
    width:24px;height:24px;border:0;border-radius:50%;background:#eef2f7;color:#9aa6b2;
    font-size:16px;line-height:1;text-align:center;cursor:pointer;padding:0;z-index:3;
    box-shadow:inset 0 0 0 1px rgba(148,163,184,.12);
}
.m-field .f-clear.show{display:flex;align-items:center;justify-content:center;}
.m-field .f-eye{
    position:absolute;right:12px;top:50%;transform:translateY(-50%);
    width:30px;height:30px;padding:0;border-radius:50%;display:flex;align-items:center;justify-content:center;
    color:#9aa6b2;font-size:19px;cursor:pointer;z-index:3;transition:background .2s ease,color .2s ease;
    -webkit-tap-highlight-color:transparent;
}
.m-field .f-eye:active{background:#eef2f7;}
.m-field .f-eye.active{color:var(--c-primary);background:rgba(var(--c-primary-rgb),.10);}
.m-sms-row,.m-captcha-row{gap:10px;margin-bottom:14px;align-items:center;}
.m-sms-row .m-field,.m-captcha-row .m-field{margin:0;}
.m-sms-btn{
    height:45px;min-width:104px;padding:0 14px;border:0;border-radius:10px;
    background:linear-gradient(135deg,var(--c-primary),var(--c-primary-dark));color:#fff;
    font-size:13px;font-weight:700;white-space:nowrap;cursor:pointer;
    box-shadow:0 10px 22px rgba(var(--c-primary-rgb),.18);transition:opacity .2s ease, transform .2s ease;
}
.m-sms-btn:active{transform:scale(.98);}
.m-sms-btn:disabled{opacity:.5;cursor:not-allowed;transform:none;}
.m-captcha-row img{
    height:45px;max-width:116px;border-radius:10px;cursor:pointer;
    border:1px solid rgba(31,41,55,.06);background:#fff;
    box-shadow:0 10px 26px rgba(31,52,88,.07);
}
.m-auth-btn{
    height:45px;border-radius:10px;font-weight:800;letter-spacing:.2px;
    box-shadow:0 14px 28px rgba(var(--c-primary-rgb),.24);
}
.m-auth-btn:active{transform:scale(.985);box-shadow:0 8px 18px rgba(var(--c-primary-rgb),.20);}
.m-auth-toggle .qqlr-login-wrap{
    margin:0!important;
    width:46px;
    height:46px;
    flex:0 0 46px;
    display:flex!important;
    align-items:center;
    justify-content:center;
}
.m-auth-toggle .qqlr-login-btn{
    width:46px!important;
    height:46px!important;
    min-width:46px!important;
    padding:0!important;
    border-radius:50%!important;
    background:rgba(18,183,245,.10)!important;
    color:#12b7f5!important;
    display:flex!important;
    align-items:center!important;
    justify-content:center!important;
    gap:0!important;
    text-decoration:none!important;
    box-shadow:none!important;
    font-size:19px!important;
    font-weight:400!important;
    transition:all .2s!important;
    -webkit-tap-highlight-color:transparent;
}
.m-auth-toggle .qqlr-login-btn:active{background:rgba(18,183,245,.20)!important;color:#12b7f5!important;transform:none;box-shadow:none!important;}
.m-auth-toggle .qqlr-login-btn span{
    position:absolute!important;
    width:1px!important;height:1px!important;
    padding:0!important;margin:-1px!important;
    overflow:hidden!important;clip:rect(0,0,0,0)!important;
    white-space:nowrap!important;border:0!important;
}
.m-auth-toggle .qqlr-login-btn i{font-size:23px!important;line-height:1!important;}
.m-auth-toggle .wclr-login-wrap{
    margin:0!important;
    width:46px;
    height:46px;
    flex:0 0 46px;
    display:flex!important;
    align-items:center;
    justify-content:center;
}
.m-auth-toggle .wclr-login-btn{
    width:46px!important;
    height:46px!important;
    min-width:46px!important;
    padding:0!important;
    border-radius:50%!important;
    background:rgba(7,193,96,.10)!important;
    color:#07c160!important;
    display:flex!important;
    align-items:center!important;
    justify-content:center!important;
    gap:0!important;
    text-decoration:none!important;
    box-shadow:none!important;
    font-size:19px!important;
    font-weight:400!important;
    transition:all .2s!important;
    -webkit-tap-highlight-color:transparent;
}
.m-auth-toggle .wclr-login-btn:active{background:rgba(7,193,96,.20)!important;color:#07c160!important;transform:none;box-shadow:none!important;}
.m-auth-toggle .wclr-login-btn span{
    position:absolute!important;
    width:1px!important;height:1px!important;
    padding:0!important;margin:-1px!important;
    overflow:hidden!important;clip:rect(0,0,0,0)!important;
    white-space:nowrap!important;border:0!important;
}
.m-auth-toggle .wclr-login-btn i{font-size:23px!important;line-height:1!important;}
.m-auth-toggle .m-auth-overflow-hidden,
.m-auth-toggle .m-auth-toggle-item.m-auth-overflow-hidden,
.m-auth-toggle .qqlr-login-wrap.m-auth-overflow-hidden,
.m-auth-toggle .wclr-login-wrap.m-auth-overflow-hidden{display:none!important;}
.m-login-more-btn{
    margin:0!important;
    width:46px;height:46px;flex:0 0 46px;
    border:0;border-radius:50%;background:rgba(100,116,139,.10);color:#64748b;
    display:none;align-items:center;justify-content:center;
    font-size:22px;cursor:pointer;transition:all .2s;
    -webkit-tap-highlight-color:transparent;
}
.m-login-more-btn:active,.m-login-more-btn.is-active{background:rgba(100,116,139,.20);color:#475569;transform:scale(.98);}
body.m-login-more-open{overflow:hidden;}
.m-login-more-mask{
    position:fixed;inset:0;z-index:3000;
    background:rgba(15,23,42,.30);
    backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);
    display:none;align-items:flex-end;justify-content:center;
}
.m-login-more-mask.is-active{display:flex;}
.m-login-more-sheet{
    position:relative;
    width:100%;max-width:500px;background:#fff;
    border-radius:20px 20px 0 0;
    box-shadow:0 -8px 40px rgba(0,0,0,.12);
    transform:translateY(100%);
    transition:transform .28s cubic-bezier(.22,.61,.36,1);
    overflow:hidden;padding-bottom:env(safe-area-inset-bottom,0);
}
.m-login-more-mask.is-active .m-login-more-sheet{transform:translateY(0);}
.m-login-more-handle{display:flex;justify-content:center;padding:14px 0 6px;cursor:grab;}
.m-login-more-handle span{width:36px;height:4px;border-radius:4px;background:#e2e8f0;}
.m-login-more-close{
    position:absolute;top:12px;right:14px;z-index:2;
    width:34px;height:34px;border:0;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    background:#f3f4f6;color:#64748b;font-size:20px;
    cursor:pointer;-webkit-tap-highlight-color:transparent;
    transition:background .2s ease,transform .2s ease,color .2s ease;
}
.m-login-more-close:active{background:#e5e7eb;color:#475569;transform:scale(.96);}
.m-login-more-head{padding:12px 24px 10px;text-align:center;}
.m-login-more-icon{
    width:54px;height:54px;border-radius:50%;
    display:inline-flex;align-items:center;justify-content:center;
    background:linear-gradient(135deg,rgba(var(--c-primary-rgb),.14),rgba(var(--c-primary-rgb),.06));
    color:var(--c-primary);font-size:24px;margin-bottom:12px;
}
.m-login-more-title{font-size:18px;font-weight:800;color:#1f2937;margin-bottom:6px;}
.m-login-more-desc{font-size:13px;color:#6b7280;line-height:1.6;}
.m-login-more-grid{
    padding:14px 22px 24px;
    display:grid;grid-template-columns:repeat(4,minmax(0,1fr));
    gap:12px 10px;max-height:42vh;overflow-y:auto;-webkit-overflow-scrolling:touch;
}
.m-login-more-method{
    border:0;background:transparent;text-decoration:none;color:#475569;
    display:flex;flex-direction:column;align-items:center;gap:7px;
    padding:9px 2px;border-radius:14px;cursor:pointer;
    font-size:12px;font-weight:500;-webkit-tap-highlight-color:transparent;
}
.m-login-more-method:active{background:#f8fafc;transform:scale(.97);}
.m-login-more-method-icon{
    width:46px;height:46px;border-radius:50%;display:flex;align-items:center;justify-content:center;
    font-size:21px;background:rgba(var(--c-primary-rgb),.10);color:var(--c-primary);
}
.m-login-more-method[data-type="tel"] .m-login-more-method-icon{background:rgba(76,175,80,.10);color:#43a047;}
.m-login-more-method[data-type="email"] .m-login-more-method-icon{background:rgba(255,152,0,.10);color:#f57c00;}
.m-login-more-method[data-type="username"] .m-login-more-method-icon{background:rgba(124,77,255,.10);color:#7c4dff;}
.m-login-more-method[data-provider="qq"] .m-login-more-method-icon{background:rgba(18,183,245,.10);color:#12b7f5;}
.m-login-more-method[data-provider="wechat"] .m-login-more-method-icon{background:rgba(7,193,96,.10);color:#07c160;}
.m-login-more-method-name{max-width:100%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
</style>
</head>
<body>
<?php doAction('user_login') ?>
<div class="m-auth-page">
    <div class="m-topbar">
        <a class="m-topbar-back" href="javascript:history.back()" aria-label="返回">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
        <div class="m-topbar-spacer"></div>
        <?php if(Option::get('register_switch') === 'y'): ?>
        <a class="m-topbar-reg" href="account.php?action=signup">注册</a>
        <?php endif; ?>
    </div>
    <div class="m-auth-header">
        <div class="m-auth-logo">
            <?php if (!empty($_login_icon)): ?>
            <img src="<?= htmlspecialchars(getFileUrl($_login_icon)) ?>" alt="Logo">
            <?php else: ?>
            <i class="ri-user-smile-line"></i>
            <?php endif; ?>
        </div>
        <div class="m-auth-site"><?= htmlspecialchars($blogname) ?></div>
        <div class="m-auth-greeting"><?= $__greeting ?></div>
    </div>

    <div class="m-auth-body">
        <?php
        $_login_tabs = [];
        if(Option::get('login_username_switch') == 'y') $_login_tabs[] = ['key'=>'username','label'=>'账号','icon'=>'ri-user-3-line'];
        if(Option::get('login_tel_switch') == 'y') $_login_tabs[] = ['key'=>'tel','label'=>'手机','icon'=>'ri-phone-line'];
        if(Option::get('login_email_switch') == 'y') $_login_tabs[] = ['key'=>'email','label'=>'邮箱','icon'=>'ri-mail-line'];
        ?>

        <form class="layui-form" lay-filter="loginForm">
            <?php if(Option::get('login_username_switch') == 'y'): ?>
            <div class="m-field username-field <?= $default_type !== 'username' ? 'display-hide' : '' ?>">
                <i class="f-icon ri-user-3-line"></i>
                <input type="text" name="username" placeholder="请输入用户名" autocomplete="username">
                <button type="button" class="f-clear" tabindex="-1">&times;</button>
            </div>
            <?php endif; ?>

            <?php if(Option::get('login_tel_switch') == 'y'): ?>
            <div class="m-field phone-field <?= $default_type !== 'tel' ? 'display-hide' : '' ?>">
                <i class="f-icon ri-phone-line"></i>
                <input type="tel" name="tel" placeholder="请输入手机号码" autocomplete="tel">
                <button type="button" class="f-clear" tabindex="-1">&times;</button>
            </div>
            <?php endif; ?>

            <?php if(Option::get('login_email_switch') == 'y'): ?>
            <div class="m-field email-field <?= $default_type !== 'email' ? 'display-hide' : '' ?>">
                <i class="f-icon ri-mail-line"></i>
                <input type="text" name="email" placeholder="请输入邮箱地址" autocomplete="email">
                <button type="button" class="f-clear" tabindex="-1">&times;</button>
            </div>
            <?php endif; ?>

            <div class="m-field password-field">
                <i class="f-icon ri-lock-2-line"></i>
                <input type="password" name="password" placeholder="请输入密码" autocomplete="current-password">
                <i class="f-eye ri-eye-off-line" id="pwdEye"></i>
            </div>

            <?php if(Option::get('login_tel_switch') == 'y'): ?>
            <div class="m-sms-row sms-code-field <?= $default_type !== 'tel' ? 'display-hide' : '' ?>">
                <div class="m-field">
                    <i class="f-icon ri-message-2-line"></i>
                    <input type="text" name="sms_code" maxlength="6" placeholder="短信验证码" autocomplete="one-time-code">
                </div>
                <button type="button" class="m-sms-btn" id="loginSmsSend">发送验证码</button>
            </div>
            <?php endif; ?>

            <?php if(Option::get('login_email_switch') == 'y'): ?>
            <div class="m-sms-row email-code-field <?= $default_type !== 'email' ? 'display-hide' : '' ?>">
                <div class="m-field">
                    <i class="f-icon ri-mail-check-line"></i>
                    <input type="text" name="email_code" maxlength="6" placeholder="邮箱验证码" autocomplete="one-time-code">
                </div>
                <button type="button" class="m-sms-btn" id="loginEmailSend">发送验证码</button>
            </div>
            <?php endif; ?>

            <?php if($login_code): ?>
            <div class="m-captcha-row pwd-login-only">
                <div class="m-field">
                    <i class="f-icon ri-shield-check-line"></i>
                    <input type="text" name="login_code" placeholder="验证码" autocomplete="off">
                </div>
                <img src="../include/lib/checkcode.php" id="checkcode" alt="验证码">
            </div>
            <?php endif; ?>

            <div class="m-auth-row">
                <label><input type="checkbox" name="persist" value="1" lay-ignore><span>记住我</span></label>
                <a href="account.php?action=reset" class="pwd-login-only">忘记密码？</a>
            </div>

            <input type="hidden" name="type" id="type" value="<?= $default_type ?>">
            <button type="button" class="m-auth-btn" lay-submit lay-filter="login">登录</button>
        </form>

    </div>

    <?php ob_start(); doAction('user_login_remember'); $_login_oauth_html = trim(ob_get_clean()); ?>
    <div class="m-auth-bottom-bar">
        <?php if(count($_login_tabs) > 1 || $_login_oauth_html !== ''): ?>
        <div class="m-auth-bottom-toggle">
            <div class="m-other-divider"><span>其他登录方式</span></div>
            <div class="m-auth-toggle">
                <?php foreach($_login_tabs as $tab): ?>
                <div class="m-auth-toggle-item" data-type="<?= $tab['key'] ?>">
                    <i class="<?= $tab['icon'] ?>"></i>
                </div>
                <?php endforeach; ?>
                <?= $_login_oauth_html ?>
                <button type="button" class="m-login-more-btn" aria-label="更多登录方式"><i class="ri-more-fill"></i></button>
            </div>
        </div>
        <?php endif; ?>
        <div class="m-auth-agreement-line">
            <input type="checkbox" id="login_agreement" checked>
            <span>我已阅读并同意<a href="account.php?action=agreement" target="_blank">《用户服务协议》</a>和<a href="account.php?action=privacy" target="_blank">《隐私政策》</a></span>
        </div>
    </div>
</div>

<div class="m-login-more-mask" id="mLoginMoreMask" aria-hidden="true">
    <div class="m-login-more-sheet" role="dialog" aria-modal="true" aria-label="更多登录方式">
        <div class="m-login-more-handle"><span></span></div>
        <button type="button" class="m-login-more-close" aria-label="关闭"><i class="ri-close-line"></i></button>
        <div class="m-login-more-head">
            <div class="m-login-more-icon"><i class="ri-more-fill"></i></div>
            <div class="m-login-more-title">更多登录方式</div>
            <div class="m-login-more-desc">请选择一种登录方式继续访问</div>
        </div>
        <div class="m-login-more-grid"></div>
    </div>
</div>

<script>
layui.use(['form','layer'], function(){
    var form = layui.form, layer = layui.layer, $ = layui.$;

    // 密码显示/隐藏切换
    $('#pwdEye').on('click', function(){
        var $input = $(this).siblings('input[name="password"]');
        if($input.attr('type') === 'password'){
            $input.attr('type','text');
            $(this).removeClass('ri-eye-off-line').addClass('ri-eye-line active');
        } else {
            $input.attr('type','password');
            $(this).removeClass('ri-eye-line active').addClass('ri-eye-off-line');
        }
    });

    var mLoginMethodLabels = {username:'账号', tel:'手机', email:'邮箱'};
    var mMoreSheet = document.querySelector('.m-login-more-sheet');
    function mEscapeHtml(text){
        return String(text || '').replace(/[&<>"']/g, function(s){
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s];
        });
    }
    function getMLoginMethodData(el){
        var $el = $(el);
        var data = {type:'', provider:'', href:'', icon:'ri-login-circle-line', label:'登录'};
        if($el.hasClass('m-auth-toggle-item')){
            data.type = String($el.data('type') || '');
            data.icon = $.trim($el.find('i').first().attr('class') || data.icon);
            data.label = mLoginMethodLabels[data.type] || '账号';
            return data;
        }
        var $btn = $el.find('a').first();
        if($btn.length){
            data.href = $btn.attr('href') || '';
            data.icon = $.trim($btn.find('i').first().attr('class') || data.icon);
            data.label = $.trim($btn.find('span').first().text()) || '快捷登录';
            if($el.hasClass('qqlr-login-wrap')){ data.provider = 'qq'; data.label = 'QQ'; }
            if($el.hasClass('wclr-login-wrap')){ data.provider = 'wechat'; data.label = '微信'; }
        }
        return data;
    }
    function renderMLoginMoreMethods(items){
        var html = '';
        for(var i = 0; i < items.length; i++){
            var data = getMLoginMethodData(items[i]);
            var attrs = '';
            if(data.type) attrs += ' data-type="' + mEscapeHtml(data.type) + '"';
            if(data.provider) attrs += ' data-provider="' + mEscapeHtml(data.provider) + '"';
            if(data.href){
                html += '<a class="m-login-more-method" href="' + mEscapeHtml(data.href) + '"' + attrs + '>' +
                    '<span class="m-login-more-method-icon"><i class="' + mEscapeHtml(data.icon) + '"></i></span>' +
                    '<span class="m-login-more-method-name">' + mEscapeHtml(data.label) + '</span></a>';
            }else{
                html += '<button type="button" class="m-login-more-method"' + attrs + '>' +
                    '<span class="m-login-more-method-icon"><i class="' + mEscapeHtml(data.icon) + '"></i></span>' +
                    '<span class="m-login-more-method-name">' + mEscapeHtml(data.label) + '</span></button>';
            }
        }
        $('.m-login-more-grid').html(html);
    }
    function closeMLoginMore(){
        var mask = document.getElementById('mLoginMoreMask');
        if(!mask) return;
        $('.m-login-more-btn').removeClass('is-active');
        if(mMoreSheet){
            mMoreSheet.style.transition = 'transform .28s cubic-bezier(.22,.61,.36,1)';
            mMoreSheet.style.transform = 'translateY(100%)';
        }
        setTimeout(function(){
            mask.classList.remove('is-active');
            mask.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('m-login-more-open');
        }, 240);
    }
    function openMLoginMore(){
        organizeMLoginMethods();
        var mask = document.getElementById('mLoginMoreMask');
        if(!mask) return;
        $('.m-login-more-btn').addClass('is-active');
        document.body.classList.add('m-login-more-open');
        mask.classList.add('is-active');
        mask.setAttribute('aria-hidden', 'false');
        if(mMoreSheet){
            mMoreSheet.style.transition = 'transform .28s cubic-bezier(.22,.61,.36,1)';
            mMoreSheet.style.transform = 'translateY(0)';
        }
    }
    function bindMLoginMoreSwipe(el){
        if(!el || el.__mLoginMoreSwipeBound) return;
        el.__mLoginMoreSwipeBound = true;
        var startY = 0, currentY = 0, dragging = false;
        el.addEventListener('touchstart', function(e){
            startY = e.touches[0].clientY;
            currentY = 0;
            dragging = true;
            el.style.transition = 'none';
        }, {passive:true});
        el.addEventListener('touchmove', function(e){
            if(!dragging) return;
            var dy = e.touches[0].clientY - startY;
            if(dy < 0) dy = 0;
            if(dy > 0) e.preventDefault();
            currentY = dy;
            el.style.transform = 'translateY(' + dy + 'px)';
        }, {passive:false});
        el.addEventListener('touchend', function(){
            if(!dragging) return;
            dragging = false;
            el.style.transition = 'transform .28s cubic-bezier(.22,.61,.36,1)';
            if(currentY > 80){
                el.style.transform = 'translateY(100%)';
                setTimeout(closeMLoginMore, 160);
            }else{
                el.style.transform = 'translateY(0)';
            }
        }, {passive:true});
    }
    function organizeMLoginMethods(){
        var $toggle = $('.m-auth-toggle');
        if(!$toggle.length) return;
        var $items = $toggle.children('.m-auth-toggle-item,.qqlr-login-wrap,.wclr-login-wrap');
        var visible = [];
        $items.removeClass('m-auth-overflow-hidden');
        $items.each(function(){
            if($(this).hasClass('display-hide')) return;
            visible.push(this);
        });
        var overflow = visible.length > 3;
        var $moreBtn = $toggle.children('.m-login-more-btn');
        if(overflow){
            for(var i = 3; i < visible.length; i++) $(visible[i]).addClass('m-auth-overflow-hidden');
            $moreBtn.css('display', 'flex');
            renderMLoginMoreMethods(visible);
        }else{
            $moreBtn.hide();
            $('.m-login-more-grid').empty();
            closeMLoginMore();
        }
    }
    bindMLoginMoreSwipe(mMoreSheet);
    $('.m-auth-toggle').on('click', '.m-login-more-btn', function(e){
        e.preventDefault();
        openMLoginMore();
    });
    $('.m-login-more-mask').on('click', function(e){
        if(e.target === this) closeMLoginMore();
    });
    $('.m-login-more-close').on('click', function(e){
        e.preventDefault();
        closeMLoginMore();
    });
    $('.m-login-more-grid').on('click', '.m-login-more-method[data-type]', function(e){
        e.preventDefault();
        var type = $(this).data('type');
        $('#type').val(type);
        switchLoginType(type);
        closeMLoginMore();
    });

    function switchLoginType(type) {
        $('.m-auth-toggle-item').removeClass('display-hide');
        $('.m-auth-toggle-item[data-type="'+type+'"]').addClass('display-hide');
        closeMLoginMore();
        $('.phone-field,.email-field,.username-field').addClass('display-hide');
        $('.sms-code-field,.email-code-field').addClass('display-hide');
        if(type === 'tel'){
            $('.phone-field').removeClass('display-hide');
            $('.password-field').addClass('display-hide');
            $('.sms-code-field').removeClass('display-hide');
            $('.pwd-login-only').addClass('display-hide');
        } else if(type === 'email'){
            $('.email-field').removeClass('display-hide');
            $('.password-field').addClass('display-hide');
            $('.email-code-field').removeClass('display-hide');
            $('.pwd-login-only').addClass('display-hide');
        } else {
            if(type === 'username') $('.username-field').removeClass('display-hide');
            $('.password-field').removeClass('display-hide');
            $('.pwd-login-only').removeClass('display-hide');
        }
        organizeMLoginMethods();
    }
    $('.m-auth-toggle-item').click(function(){
        var type = $(this).data('type');
        $('#type').val(type);
        switchLoginType(type);
    });
    switchLoginType($('#type').val());

    $('#checkcode').on('click', function(){ $(this).attr('src','../include/lib/checkcode.php?' + Date.now()); });

    // 清除按钮
    $('.m-auth-body').on('input focus','.m-field input',function(){
        var $c = $(this).siblings('.f-clear');
        this.value.length > 0 ? $c.addClass('show') : $c.removeClass('show');
    }).on('blur','.m-field input',function(){
        var $c = $(this).siblings('.f-clear');
        setTimeout(function(){ $c.removeClass('show'); },150);
    });
    $('.m-auth-body').on('click','.f-clear',function(){
        $(this).siblings('input').val('').focus();
        $(this).removeClass('show');
    });

    // 倒计时
    var _captchaUrl = '../include/lib/checkcode.php?t=';
    function startCountdown($btn, key, seconds){
        var end = Math.floor(Date.now()/1000) + seconds;
        localStorage.setItem(key, end);
        _runCountdown($btn, key, end);
    }
    function _runCountdown($btn, key, end){
        $btn.prop('disabled',true);
        var t = setInterval(function(){
            var left = end - Math.floor(Date.now()/1000);
            if(left<=0){ clearInterval(t); localStorage.removeItem(key); $btn.prop('disabled',false).text('发送验证码'); }
            else{ $btn.text(left+'s'); }
        },1000);
        var left = end - Math.floor(Date.now()/1000);
        $btn.text(left > 0 ? left+'s' : '发送验证码');
    }
    function resumeCountdown($btn, key){
        var end = parseInt(localStorage.getItem(key)||'0');
        if(end > Math.floor(Date.now()/1000)) _runCountdown($btn, key, end);
    }
    resumeCountdown($('#loginSmsSend'), 'cd_login_sms');
    resumeCountdown($('#loginEmailSend'), 'cd_login_email');

    // 发送短信验证码
    $('#loginSmsSend').on('click', function(){
        var phone = $.trim($('input[name="tel"]').val());
        if(!phone || !/^1[3-9]\d{9}$/.test(phone)) return layer.msg('请输入正确的手机号', {icon:0});
        var $btn = $(this);
        layer.open({
            type:1, title:'安全验证', area:['90%','auto'], shadeClose:true,
            content:'<div style="padding:20px 20px 10px"><div style="color:#64748b;font-size:13px;margin-bottom:10px">请输入图形验证码后发送短信</div><div style="display:flex;gap:10px;align-items:center"><input id="_smsCapInput" type="text" maxlength="8" placeholder="验证码" style="flex:1;min-width:0;height:44px;padding:0 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:16px;outline:none;box-sizing:border-box"><img id="_smsCapImg" src="'+_captchaUrl+Date.now()+'" style="height:40px;max-width:120px;flex-shrink:0;border-radius:10px;cursor:pointer" title="点击刷新"></div></div>',
            btn:['发送','取消'],
            yes:function(idx){
                var imgcode = $.trim($('#_smsCapInput').val());
                if(!imgcode) return layer.msg('请输入图形验证码',{icon:0});
                layer.close(idx);
                $btn.prop('disabled',true).text('发送中...');
                $.ajax({
                    type:'POST', url:'./account.php?action=send_login_sms_code',
                    data:{tel:phone, imgcode:imgcode}, dataType:'json',
                    success:function(e){
                        if(e.code==0){ layer.msg('验证码已发送',{icon:1}); startCountdown($btn,'cd_login_sms',60); }
                        else { $btn.prop('disabled',false).text('发送验证码'); layer.msg(e.msg||'发送失败',{icon:2}); }
                    },
                    error:function(){ $btn.prop('disabled',false).text('发送验证码'); layer.msg('发送失败',{icon:2}); }
                });
            },
            success:function(layero){
                layero.find('.layui-layer-btn0').css({background:'#2196F3',borderColor:'#2196F3'});
                layero.find('#_smsCapImg').on('click',function(){$(this).attr('src',_captchaUrl+Date.now());});
            }
        });
    });

    // 发送邮箱验证码
    $('#loginEmailSend').on('click', function(){
        var email = $.trim($('input[name="email"]').val());
        if(!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return layer.msg('请输入正确的邮箱地址', {icon:0});
        var $btn = $(this);
        layer.open({
            type:1, title:'安全验证', area:['90%','auto'], shadeClose:true,
            content:'<div style="padding:20px 20px 10px"><div style="color:#64748b;font-size:13px;margin-bottom:10px">请输入图形验证码后发送邮件</div><div style="display:flex;gap:10px;align-items:center"><input id="_emailCapInput" type="text" maxlength="8" placeholder="验证码" style="flex:1;min-width:0;height:44px;padding:0 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:16px;outline:none;box-sizing:border-box"><img id="_emailCapImg" src="'+_captchaUrl+Date.now()+'" style="height:40px;max-width:120px;flex-shrink:0;border-radius:10px;cursor:pointer" title="点击刷新"></div></div>',
            btn:['发送','取消'],
            yes:function(idx){
                var imgcode = $.trim($('#_emailCapInput').val());
                if(!imgcode) return layer.msg('请输入图形验证码',{icon:0});
                layer.close(idx);
                $btn.prop('disabled',true).text('发送中...');
                $.ajax({
                    type:'POST', url:'./account.php?action=send_login_email_code',
                    data:{email:email, imgcode:imgcode}, dataType:'json',
                    success:function(e){
                        if(e.code==0){ layer.msg('验证码已发送至邮箱',{icon:1}); startCountdown($btn,'cd_login_email',60); }
                        else { $btn.prop('disabled',false).text('发送验证码'); layer.msg(e.msg||'发送失败',{icon:2}); }
                    },
                    error:function(){ $btn.prop('disabled',false).text('发送验证码'); layer.msg('发送失败',{icon:2}); }
                });
            },
            success:function(layero){
                layero.find('.layui-layer-btn0').css({background:'#2196F3',borderColor:'#2196F3'});
                layero.find('#_emailCapImg').on('click',function(){$(this).attr('src',_captchaUrl+Date.now());});
            }
        });
    });

    var _loginRedirectUrl = <?= json_encode(!empty($login_redirect_url) ? $login_redirect_url : '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    // 登录提交
    form.on('submit(login)', function(data){
        layer.load(2);
        $.ajax({
            type:'POST', url:'./account.php?action=dosignin',
            data: data.field, dataType:'json',
            success:function(e){
                if(e.code == 0){
                    layer.msg('登录成功，正在跳转');
                    location.href = _loginRedirectUrl || '/user/';
                } else {
                    <?php doAction('admin_login_error') ?>
                    layer.msg(e.msg);
                    $('#checkcode').attr('src','../include/lib/checkcode.php?' + Date.now());
                }
            },
            error:function(xhr){
                try{ layer.msg(JSON.parse(xhr.responseText).msg); }catch(err){ layer.msg('请求失败'); }
                $('#checkcode').attr('src','../include/lib/checkcode.php?' + Date.now());
            },
            complete:function(){ layer.closeAll('loading'); }
        });
        return false;
    });
});
</script>
<?php include __DIR__ . '/_auth_pull_refresh.php'; ?>
</body>
</html>
