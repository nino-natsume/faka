<?php defined('DC_ROOT') || exit('access denied!'); ?>
<?php
$blogname = Option::get('blogname');
include __DIR__ . '/_auth_theme_vars.php';
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no,viewport-fit=cover">
    <title>找回密码 - <?= htmlspecialchars($blogname) ?></title>
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
.m-topbar-action{
    font-size:15px;font-weight:600;color:var(--c-primary);
    text-decoration:none;flex:0 0 auto;padding:6px 0;
    -webkit-tap-highlight-color:transparent;
}
.m-topbar-action:active{opacity:.7;}
.m-auth-header{
    padding:calc(56px + env(safe-area-inset-top,0px)) 28px 0;
    text-align:center;
}
.m-auth-logo{
    width:68px;height:68px;margin:0 auto 14px;border-radius:50%;
    background:linear-gradient(135deg,var(--c-primary),var(--c-primary-dark));
    display:flex;align-items:center;justify-content:center;
    color:#fff;font-size:30px;
    box-shadow:0 8px 24px rgba(var(--c-primary-rgb),.25);
}
.m-auth-site{font-size:20px;font-weight:700;color:var(--c-text);margin-bottom:4px;}
.m-auth-tip{font-size:13px;color:var(--c-sub);}
.m-auth-body{
    flex:1;
    padding:28px 30px 0;
}
.m-alert{
    padding:10px 14px;border-radius:10px;font-size:13px;margin-bottom:14px;
    display:flex;align-items:center;gap:6px;
}
.m-alert.error{background:rgba(255,59,48,.08);color:#e53e3e;border:1px solid rgba(255,59,48,.12);}
.m-alert.success{background:rgba(52,199,89,.08);color:#2f855a;border:1px solid rgba(52,199,89,.12);}
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
.m-captcha-row{display:flex;gap:8px;margin-bottom:12px;align-items:center;}
.m-captcha-row .m-field{flex:1;margin:0;}
.m-captcha-row img{height:40px;border-radius:10px;cursor:pointer;border:1px solid var(--c-border);}
.m-auth-btn{
    width:100%;height:45px;border:none;border-radius:10px;
    background:linear-gradient(135deg,var(--c-primary),var(--c-primary-dark));
    color:#fff;font-size:16px;font-weight:600;cursor:pointer;
    transition:all .2s;
    box-shadow:0 4px 16px rgba(var(--c-primary-rgb),.25);
}
.m-auth-btn:active{transform:scale(.98);box-shadow:0 2px 8px rgba(var(--c-primary-rgb),.2);}

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
.m-field .f-clear{
    position:absolute;right:14px;top:50%;transform:translateY(-50%);
    width:24px;height:24px;border:0;border-radius:50%;background:#eef2f7;color:#9aa6b2;
    font-size:16px;line-height:1;text-align:center;cursor:pointer;padding:0;z-index:3;
    box-shadow:inset 0 0 0 1px rgba(148,163,184,.12);
}
.m-field .f-clear.show{display:flex;align-items:center;justify-content:center;}
.m-captcha-row{gap:10px;margin-bottom:14px;align-items:center;}
.m-captcha-row .m-field{margin:0;}
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
</style>
</head>
<body>
<div class="m-auth-page">
    <div class="m-topbar">
        <a class="m-topbar-back" href="javascript:history.back()" aria-label="back">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
        <div class="m-topbar-spacer"></div>
        <a class="m-topbar-action" href="account.php?action=signin">登录</a>
    </div>
    <div class="m-auth-header">
        <div class="m-auth-logo"><i class="ri-mail-send-line"></i></div>
        <div class="m-auth-site">找回密码</div>
        <div class="m-auth-tip">请输入注册时使用的邮箱地址</div>
    </div>

    <div class="m-auth-body">
        <?php if (isset($_GET['error_mail'])): ?>
            <div class="m-alert error"><i class="ri-error-warning-line"></i> 错误的注册邮箱</div>
        <?php endif ?>
        <?php if (isset($_GET['error_sendmail'])): ?>
            <div class="m-alert error"><i class="ri-error-warning-line"></i> 邮件验证码发送失败，请检查邮件通知设置</div>
        <?php endif ?>
        <?php if (isset($_GET['err_ckcode'])): ?>
            <div class="m-alert error"><i class="ri-error-warning-line"></i> 图形验证码错误</div>
        <?php endif ?>

        <form method="post" action="./account.php?action=doreset">
            <div class="m-field">
                <i class="f-icon ri-mail-line"></i>
                <input type="email" id="mail" name="mail" placeholder="注册邮箱" required autofocus>
                <button type="button" class="f-clear" tabindex="-1">&times;</button>
            </div>

            <?php if ($login_code): ?>
            <div class="m-captcha-row">
                <div class="m-field">
                    <i class="f-icon ri-shield-check-line"></i>
                    <input type="text" name="login_code" id="login_code" placeholder="验证码" required>
                </div>
                <img src="../include/lib/checkcode.php" id="checkcode" alt="验证码">
            </div>
            <?php endif ?>

            <button type="submit" class="m-auth-btn">发送验证码</button>
        </form>
    </div>

</div>

<script>
$(function(){
    if(typeof hideActived === 'function') setTimeout(hideActived, 6000);
    $('#checkcode').on('click', function(){
        $(this).attr('src','../include/lib/checkcode.php?' + Date.now());
    });
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
});
</script>
<?php include __DIR__ . '/_auth_pull_refresh.php'; ?>
</body>
</html>
