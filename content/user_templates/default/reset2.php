<?php defined('DC_ROOT') || exit('access denied!'); ?>
<?php
$blogname = Option::get('blogname');
include __DIR__ . '/_auth_theme_vars.php';
?>
<style>
    *{margin:0;padding:0;box-sizing:border-box;}
    body{
        font-family:-apple-system,BlinkMacSystemFont,"PingFang SC","Helvetica Neue",Helvetica,Arial,sans-serif;
        min-height:100vh;display:flex;align-items:center;justify-content:center;
        background:radial-gradient(circle at 12% 55%,rgba(<?= $_auth_primary_rgb ?>,.12),transparent 25%),
                   radial-gradient(circle at 85% 33%,rgba(108,99,255,.12),transparent 25%),#f5f7fa;
        padding:20px;
    }
    .auth-card{
        width:420px;max-width:100%;
        background:rgba(255,255,255,.72);
        backdrop-filter:saturate(180%) blur(20px);-webkit-backdrop-filter:saturate(180%) blur(20px);
        border:1px solid rgba(255,255,255,.85);
        border-radius:16px;box-shadow:0 12px 40px rgba(0,0,0,.08);
        overflow:hidden;animation:authPop .45s cubic-bezier(.2,.8,.2,1);
    }
    @keyframes authPop{from{opacity:0;transform:translateY(16px) scale(.97);}to{opacity:1;transform:none;}}
    .auth-header{padding:32px 32px 0;text-align:center;}
    .auth-avatar{
        width:72px;height:72px;margin:0 auto 12px;border-radius:50%;
        background:linear-gradient(135deg,<?= $_auth_primary ?>,<?= $_auth_primary_dark ?>);
        display:flex;align-items:center;justify-content:center;
        color:#fff;font-size:32px;box-shadow:0 6px 18px <?= $_auth_shadow_30 ?>;
    }
    .auth-title{font-size:20px;font-weight:600;color:#1a1a1a;margin-bottom:2px;}
    .auth-tip{font-size:13px;color:#86909c;}
    .auth-body{padding:24px 32px 28px;}
    .auth-alert{
        padding:10px 12px;border-radius:8px;font-size:13px;margin-bottom:12px;
        display:flex;align-items:center;gap:6px;
    }
    .auth-alert.error{background:rgba(255,59,48,.08);color:#e53e3e;border:1px solid rgba(255,59,48,.12);}
    .auth-alert.success{background:rgba(52,199,89,.08);color:#2f855a;border:1px solid rgba(52,199,89,.12);}
    .auth-field{position:relative;margin-bottom:12px;}
    .auth-field .field-icon{
        position:absolute;left:14px;top:50%;transform:translateY(-50%);
        color:#c9cdd4;font-size:16px;pointer-events:none;
    }
    .auth-field input{
        width:100%;height:44px;padding:0 40px 0 42px;
        border:1px solid #e5e6eb;background:#fff;border-radius:10px;
        font-size:14px;color:#1a1a1a;outline:none;transition:all .2s;
    }
    .auth-field input::placeholder{color:#c9cdd4;}
    .auth-field input:focus{border-color:<?= $_auth_primary ?>;box-shadow:0 0 0 3px rgba(<?= $_auth_primary_rgb ?>,.12);}
    .auth-field .field-clear{
        position:absolute;right:12px;top:50%;transform:translateY(-50%);
        width:18px;height:18px;border-radius:4px;border:none;
        background:rgba(0,0,0,.1);color:#fff;font-size:11px;line-height:18px;text-align:center;
        cursor:pointer;display:none;padding:0;
    }
    .auth-field .field-clear.show{display:block;}
    .auth-btn{
        width:100%;height:44px;border:none;border-radius:10px;
        background:linear-gradient(135deg,<?= $_auth_primary ?>,<?= $_auth_primary_dark ?>);
        color:#fff;font-size:15px;font-weight:500;cursor:pointer;
        transition:all .25s;box-shadow:0 4px 12px <?= $_auth_shadow_25 ?>;
    }
    .auth-btn:hover{transform:translateY(-1px);box-shadow:0 6px 18px <?= $_auth_shadow_35 ?>;}
    .auth-btn:active{transform:translateY(0);}
    .auth-footer{text-align:center;margin-top:16px;font-size:13px;color:#86909c;}
    .auth-footer a{color:<?= $_auth_primary ?>;text-decoration:none;font-weight:500;}
    .auth-footer a:hover{text-decoration:underline;}
    .auth-links{
        display:flex;justify-content:center;gap:24px;
        margin-top:12px;padding-top:12px;border-top:1px solid #f2f3f5;font-size:12px;
    }
    .auth-links a{color:#86909c;text-decoration:none;display:flex;align-items:center;gap:4px;}
    .auth-links a:hover{color:<?= $_auth_primary ?>;}
    .auth-step{
        display:flex;align-items:center;justify-content:center;gap:8px;
        margin-bottom:18px;font-size:12px;color:#c9cdd4;
    }
    .auth-step .step{
        display:flex;align-items:center;gap:4px;
    }
    .auth-step .step.done{color:#34c759;}
    .auth-step .step.active{color:<?= $_auth_primary ?>;font-weight:500;}
    .auth-step .step-line{width:24px;height:1px;background:#e5e6eb;}
    @media(max-width:480px){
        body{padding:16px;align-items:flex-start;padding-top:8vh;}
        .auth-card{border-radius:14px;}
        .auth-header{padding:24px 20px 0;}
        .auth-body{padding:18px 20px 22px;}
        .auth-avatar{width:60px;height:60px;font-size:28px;}
    }
</style>

<div class="auth-card">
    <div class="auth-header">
        <div class="auth-avatar"><i class="ri-key-2-line"></i></div>
        <div class="auth-title">重置密码</div>
        <div class="auth-tip">请查收邮箱中的验证码并设置新密码</div>
    </div>

    <div class="auth-body">
        <div class="auth-step">
            <span class="step done"><i class="ri-checkbox-circle-fill"></i> 验证邮箱</span>
            <span class="step-line"></span>
            <span class="step active"><i class="ri-edit-circle-line"></i> 设置新密码</span>
        </div>

        <?php if (isset($_GET['succ_mail'])): ?>
            <div class="auth-alert success"><i class="ri-checkbox-circle-line"></i> 邮件验证码已发送，请查收邮箱</div>
        <?php endif ?>
        <?php if (isset($_GET['err_mail_code'])): ?>
            <div class="auth-alert error"><i class="ri-error-warning-line"></i> 邮件验证码错误</div>
        <?php endif ?>
        <?php if (isset($_GET['error_pwd_len'])): ?>
            <div class="auth-alert error"><i class="ri-error-warning-line"></i> 密码长度不合规</div>
        <?php endif ?>
        <?php if (isset($_GET['error_pwd2'])): ?>
            <div class="auth-alert error"><i class="ri-error-warning-line"></i> 两次输入的密码不一致</div>
        <?php endif ?>

        <form method="post" action="./account.php?action=doreset2">
            <div class="auth-field">
                <i class="field-icon ri-mail-open-line"></i>
                <input type="text" id="mail_code" name="mail_code" placeholder="邮件验证码" required autofocus>
                <button type="button" class="field-clear" tabindex="-1">&times;</button>
            </div>

            <div class="auth-field">
                <i class="field-icon ri-lock-2-line"></i>
                <input type="password" id="passwd" name="passwd" minlength="6" placeholder="新密码（至少6位）" autocomplete="new-password" required>
                <button type="button" class="field-clear" tabindex="-1">&times;</button>
            </div>

            <div class="auth-field">
                <i class="field-icon ri-lock-check-line"></i>
                <input type="password" id="repasswd" name="repasswd" minlength="6" placeholder="确认新密码" autocomplete="new-password" required>
                <button type="button" class="field-clear" tabindex="-1">&times;</button>
            </div>

            <button type="submit" class="auth-btn">重置密码</button>

            <div class="auth-footer"><a href="account.php?action=signin">返回登录</a></div>

            <div class="auth-links">
                <a href="../"><i class="ri-home-4-line"></i> 返回首页</a>
                <a href="account.php?action=signup"><i class="ri-user-add-line"></i> 注册账号</a>
            </div>
        </form>
    </div>
</div>

<script>
$(function(){
    if(typeof hideActived === 'function') setTimeout(hideActived, 6000);
    // 清除按钮
    $('.auth-body').on('input focus','.auth-field input',function(){
        var $c = $(this).siblings('.field-clear');
        this.value.length > 0 ? $c.addClass('show') : $c.removeClass('show');
    }).on('blur','.auth-field input',function(){
        var $c = $(this).siblings('.field-clear');
        setTimeout(function(){ $c.removeClass('show'); },150);
    });
    $('.auth-body').on('click','.field-clear',function(){
        $(this).siblings('input').val('').focus();
        $(this).removeClass('show');
    });
});
</script>
</body>
</html>