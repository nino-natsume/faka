<?php defined('DC_ROOT') || exit('access denied!'); ?>
<?php $blogname = Option::get('blogname'); ?>
<?php
$__signin_bgs = [
    'https://dscache.tencent-cloud.cn/upload/uploader/all-base-4d4ab1d67ce695c13f6172a90395a0e784a571c2.png',
    'https://dscache.tencent-cloud.cn/upload/uploader/enterprise-base-ab12aa5ac597f609c91a4775cb3756cada51a7d9.png',
];
$__signin_bg = $__signin_bgs[array_rand($__signin_bgs)];
?>
<style>
    :root{
        --mac-bg1:#b4d2ff;--mac-bg2:#f3c6ff;--mac-bg3:#ffd6a5;--mac-bg4:#b1f0d8;
        --mac-text:#1d1d1f;--mac-sub:#6e6e73;--mac-accent:#0a84ff;
    }
    *{margin:0;box-sizing:border-box;}
    html,body{height:100%;}
    body{
        font-family:-apple-system,BlinkMacSystemFont,"SF Pro Display","SF Pro Text","PingFang SC","Helvetica Neue",Helvetica,Arial,sans-serif;
        color:var(--mac-text);min-height:100vh;display:flex;align-items:center;justify-content:center;
        overflow:hidden;background:#f5f7fa;position:relative;
    }
    .mac-wallpaper{position:fixed;inset:0;z-index:-2;background:#f5f7fa;}
    .mac-window{
        width:420px;max-width:92vw;border-radius:10px;overflow:hidden;
        background-position:center;background-size:cover;background-repeat:no-repeat;
        backdrop-filter:saturate(180%) blur(30px);-webkit-backdrop-filter:saturate(180%) blur(30px);
        border:2px solid #fff;
        box-shadow:0 30px 60px rgba(0,0,0,.0),0 10px 20px rgba(0,0,0,.12),inset 0 1px 0 rgba(255,255,255,.6);
        animation:macPop .5s cubic-bezier(.2,.8,.2,1);
    }
    @keyframes macPop{from{opacity:0;transform:translateY(12px) scale(.98);}to{opacity:1;transform:none;}}
    .mac-titlebar{
        height:36px;display:flex;align-items:center;padding:0 14px;gap:8px;
        background:linear-gradient(to bottom,rgba(255,255,255,.55),rgba(255,255,255,.25));
        border-bottom:1px solid rgba(0,0,0,.06);user-select:none;
    }
    .mac-traffic{display:flex;align-items:center;gap:8px;}
    .mac-dot{width:12px;height:12px;border-radius:50%;box-shadow:inset 0 0 0 .5px rgba(0,0,0,.15);}
    .mac-dot.red{background:#ff5f57;}.mac-dot.yellow{background:#febc2e;}.mac-dot.green{background:#28c840;}
    .mac-title{flex:1;text-align:center;font-size:13px;font-weight:600;color:#3c3c43;letter-spacing:.2px;}
    .mac-body{padding:28px 32px 22px;}
    .mac-avatar{
        width:72px;height:72px;margin:0 auto 12px;border-radius:50%;
        background:linear-gradient(135deg,#34c759,#30d158);
        display:flex;align-items:center;justify-content:center;color:#fff;font-size:32px;
        box-shadow:0 8px 18px rgba(52,199,89,.3),inset 0 0 0 2px rgba(255,255,255,.7);
    }
    .mac-page-title{text-align:center;font-size:17px;font-weight:600;color:#1d1d1f;margin-bottom:2px;}
    .mac-page-tip{text-align:center;font-size:12px;color:var(--mac-sub);margin-bottom:20px;}
    .mac-alert{
        padding:8px 12px;border-radius:8px;font-size:12px;margin-bottom:12px;
        display:flex;align-items:center;gap:6px;
    }
    .mac-alert.error{background:rgba(255,59,48,.1);color:#ff3b30;border:1px solid rgba(255,59,48,.15);}
    .mac-alert.success{background:rgba(52,199,89,.1);color:#34c759;border:1px solid rgba(52,199,89,.15);}
    .mac-form .mac-field{position:relative;margin-bottom:10px;}
    .mac-form .mac-field .mac-icon{
        position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#8e8e93;font-size:15px;pointer-events:none;
    }
    .mac-form input.mac-input{
        width:100%;height:42px;padding:0 14px 0 40px;
        border:1px solid rgba(0,0,0,.08);background:rgba(255,255,255,.75);border-radius:10px;
        font-size:13px;color:#1d1d1f;outline:none;transition:all .2s ease;-webkit-appearance:none;
    }
    .mac-form input.mac-input::placeholder{color:#a1a1a6;}
    .mac-form input.mac-input:focus{border-color:rgba(10,132,255,.7);background:#fff;box-shadow:0 0 0 4px rgba(10,132,255,.18);}
    .mac-submit-wrap{display:flex;justify-content:center;margin:6px 0 4px;}
    .mac-go{
        width:52px;height:52px;border:2px solid #fff;border-radius:50%;
        background-image:linear-gradient(0deg,#f5f5f5 0%,#f3f5f8 100%);
        backdrop-filter:saturate(180%) blur(16px);-webkit-backdrop-filter:saturate(180%) blur(16px);
        color:#3c3c43;font-size:22px;display:flex;align-items:center;justify-content:center;cursor:pointer;
        box-shadow:0 6px 16px rgba(0,0,0,.12),inset 0 1px 0 rgba(255,255,255,.6);
    }
    .mac-go:hover{background:rgba(255,255,255,.55);transform:translateY(-2px) scale(1.04);box-shadow:0 10px 22px rgba(0,0,0,.16);}
    .mac-go:active{transform:translateY(0) scale(.98);filter:brightness(.96);}
    .mac-go i{line-height:1;}
    .mac-ext{text-align:center;margin-top:14px;font-size:12px;color:var(--mac-sub);}
    .mac-ext a{color:var(--mac-accent);text-decoration:none;}
    .mac-ext a:hover{text-decoration:underline;}
    .mac-dock{
        position:fixed;bottom:14px;left:50%;transform:translateX(-50%);
        display:flex;align-items:center;gap:10px;padding:8px 14px;border-radius:18px;
        background:rgba(255,255,255,.45);backdrop-filter:saturate(180%) blur(22px);-webkit-backdrop-filter:saturate(180%) blur(22px);
        border:1px solid rgba(255,255,255,.5);box-shadow:0 12px 30px rgba(0,0,0,.18);z-index:10;
    }
    .mac-dock a{
        width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;
        color:#1d1d1f;font-size:20px;text-decoration:none;
        background:linear-gradient(135deg,#ffffff 0%,#e7ecf5 100%);box-shadow:0 4px 10px rgba(0,0,0,.12);transition:transform .2s ease;position:relative;
    }
    .mac-dock a:hover{transform:translateY(-6px) scale(1.08);}
    .mac-dock a .tip{
        position:absolute;bottom:54px;left:50%;transform:translateX(-50%);
        background:rgba(0,0,0,.7);color:#fff;font-size:12px;padding:3px 8px;border-radius:6px;
        white-space:nowrap;opacity:0;pointer-events:none;transition:opacity .15s ease;
    }
    .mac-dock a:hover .tip{opacity:1;}
    .mac-dock .sep{width:1px;height:26px;background:rgba(0,0,0,.12);margin:0 2px;}
    .mac-dock .copyright{font-size:12px;color:var(--mac-sub);padding:0 6px;}
    @media(max-width:480px){
        body{align-items:flex-start;padding-top:12vh;}
        .mac-window{width:94vw;}
        .mac-body{padding:22px 22px 18px;}
        .mac-avatar{width:60px;height:60px;font-size:28px;}
        .mac-dock .copyright,.mac-dock .sep{display:none;}
    }
</style>

<div class="mac-wallpaper"></div>

<div class="mac-window" role="dialog" aria-label="重置密码" style="background-image:url('<?= htmlspecialchars($__signin_bg, ENT_QUOTES) ?>');">
    <div class="mac-titlebar">
        <div class="mac-traffic">
            <span class="mac-dot red"></span>
            <span class="mac-dot yellow"></span>
            <span class="mac-dot green"></span>
        </div>
        <div class="mac-title">重置密码</div>
        <div style="width:52px;"></div>
    </div>

    <div class="mac-body">
        <div class="mac-avatar"><i class="ri-lock-unlock-line"></i></div>
        <div class="mac-page-title">设置新密码</div>
        <div class="mac-page-tip">验证码已发送至邮箱，请查收后填写</div>

        <?php if (isset($_GET['succ_mail'])): ?>
            <div class="mac-alert success"><i class="ri-checkbox-circle-line"></i> 邮件验证码已发送，请查收邮箱</div>
        <?php endif ?>
        <?php if (isset($_GET['err_mail_code'])): ?>
            <div class="mac-alert error"><i class="ri-error-warning-line"></i> 邮件验证码错误</div>
        <?php endif ?>
        <?php if (isset($_GET['error_pwd_len'])): ?>
            <div class="mac-alert error"><i class="ri-error-warning-line"></i> 密码长度不合规</div>
        <?php endif ?>
        <?php if (isset($_GET['error_pwd2'])): ?>
            <div class="mac-alert error"><i class="ri-error-warning-line"></i> 两次输入的密码不一致</div>
        <?php endif ?>

        <form method="post" class="layui-form mac-form" action="./account.php?action=doreset2">
            <div class="mac-field">
                <i class="mac-icon ri-mail-check-line"></i>
                <input type="text" class="mac-input" id="mail_code" name="mail_code" placeholder="邮件验证码" required autofocus>
            </div>

            <div class="mac-field">
                <i class="mac-icon ri-lock-2-line"></i>
                <input type="password" class="mac-input" id="passwd" name="passwd" minlength="6" autocomplete="new-password" placeholder="新密码" required>
            </div>

            <div class="mac-field">
                <i class="mac-icon ri-lock-check-line"></i>
                <input type="password" class="mac-input" id="repasswd" name="repasswd" minlength="6" placeholder="确认新密码" required>
            </div>

            <div class="mac-submit-wrap">
                <button type="submit" class="mac-go" title="重置密码">
                    <i class="ri-check-line"></i>
                </button>
            </div>

            <div class="mac-ext">
                <a href="./">返回登录</a>
            </div>
        </form>
    </div>
</div>

<div class="mac-dock">
    <a href="../" title="返回首页">
        <i class="ri-home-4-line"></i>
        <span class="tip">返回首页</span>
    </a>
    <a href="./" title="返回登录">
        <i class="ri-login-box-line"></i>
        <span class="tip">返回登录</span>
    </a>
    <span class="sep"></span>
    <span class="copyright">© <?= date('Y') ?> <?= htmlspecialchars($blogname) ?></span>
</div>

<script>
    $(function () {
        if (typeof hideActived === 'function') { setTimeout(hideActived, 6000); }
        $('.mac-form .mac-input').on('keydown', function(e){
            if(e.key === 'Enter'){ e.preventDefault(); $('.mac-go').trigger('click'); }
        });
    });
</script>
</body>
</html>
