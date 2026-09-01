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
    <title>注册 - <?= htmlspecialchars($blogname) ?></title>
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
    padding:24px 30px 60px;
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
    background:linear-gradient(135deg,var(--c-primary),var(--c-primary-dark));color:#fff;font-size:13px;font-weight:500;
    cursor:pointer;white-space:nowrap;transition:opacity .2s;
}
.m-sms-btn:disabled{opacity:.5;cursor:not-allowed;}
.m-captcha-row{display:flex;gap:8px;margin-bottom:12px;align-items:center;}
.m-captcha-row .m-field{flex:1;margin:0;}
.m-captcha-row img{height:40px;border-radius:10px;cursor:pointer;border:1px solid var(--c-border);}
.m-invite-tip{
    display:flex;align-items:center;gap:6px;
    padding:10px 14px;border-radius:10px;margin-bottom:12px;
    background:rgba(var(--c-primary-rgb),.08);color:var(--c-primary-dark);font-size:13px;
}
.m-auth-btn{
    width:100%;height:45px;border:none;border-radius:10px;
    background:linear-gradient(135deg,var(--c-primary),var(--c-primary-dark));
    color:#fff;font-size:16px;font-weight:600;cursor:pointer;
    transition:all .2s;
    box-shadow:0 4px 16px rgba(var(--c-primary-rgb),.25);
}
.m-auth-btn:active{transform:scale(.98);box-shadow:0 2px 8px rgba(var(--c-primary-rgb),.2);}
.display-hide{display:none!important;}
.m-auth-agreement-bottom{
    position:fixed;bottom:0;left:0;right:0;z-index:100;
    padding:10px 24px calc(10px + env(safe-area-inset-bottom,0));
    background:var(--c-bg);
    text-align:center;
    font-size:12px;color:var(--c-sub);
    display:flex;align-items:center;justify-content:center;gap:4px;
}
.m-auth-agreement-bottom input{accent-color:var(--c-primary);width:14px;height:14px;flex-shrink:0;}
.m-auth-agreement-bottom a{color:var(--c-primary);text-decoration:none;}

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
</style>
</head>
<body>
<?php doAction('user_register') ?>
<div class="m-auth-page">
    <div class="m-topbar">
        <a class="m-topbar-back" href="javascript:history.back()" aria-label="back">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
        <div class="m-topbar-spacer"></div>
        <a class="m-topbar-action" href="account.php?action=signin">登录</a>
    </div>
    <div class="m-auth-header">
        <div class="m-auth-logo"><i class="ri-user-add-line"></i></div>
        <div class="m-auth-site">创建账号</div>
        <div class="m-auth-tip">注册后即可享受全部功能</div>
    </div>

    <div class="m-auth-body">
        <?php
        $_hasUsername = Option::get('register_username_switch') == 'y';
        $_hasTel = Option::get('register_tel_switch') == 'y';
        $_hasEmail = Option::get('register_email_switch') == 'y';
        $_noneOpen = !$_hasUsername && !$_hasTel && !$_hasEmail;
        ?>
        <?php if($_noneOpen): ?>
        <div style="text-align:center;padding:30px 0 10px;">
            <i class="ri-lock-line" style="font-size:44px;color:#c9cdd4;display:block;margin-bottom:12px;"></i>
            <div style="font-size:16px;color:#4e5969;font-weight:600;margin-bottom:6px;">暂未开放注册</div>
            <div style="font-size:13px;color:#86909c;line-height:1.6;">管理员尚未开启任何注册方式，<br>请联系管理员或稍后再试。</div>
        </div>
        <?php else: ?>
        <form class="layui-form" lay-filter="registerForm">
            <?php if($_hasUsername): ?>
            <div class="m-field">
                <i class="f-icon ri-user-3-line"></i>
                <input type="text" name="username" placeholder="<?= ($_hasTel || $_hasEmail) ? '用户名（3-20位）' : '请输入用户名（3-20位）' ?>" autocomplete="off">
                <button type="button" class="f-clear" tabindex="-1">&times;</button>
            </div>
            <?php endif; ?>

            <?php if($_hasTel): ?>
            <div class="m-field">
                <i class="f-icon ri-phone-line"></i>
                <input type="tel" name="tel" placeholder="手机号码" autocomplete="off">
                <button type="button" class="f-clear" tabindex="-1">&times;</button>
            </div>
            <div class="m-sms-row">
                <div class="m-field">
                    <i class="f-icon ri-message-2-line"></i>
                    <input type="text" name="sms_code" maxlength="6" placeholder="短信验证码" autocomplete="one-time-code">
                </div>
                <button type="button" class="m-sms-btn" id="regSmsSend">发送验证码</button>
            </div>
            <?php endif; ?>

            <?php if($_hasEmail): ?>
            <div class="m-field">
                <i class="f-icon ri-mail-line"></i>
                <input type="text" name="email" placeholder="邮箱地址" autocomplete="off">
                <button type="button" class="f-clear" tabindex="-1">&times;</button>
            </div>
            <div class="m-sms-row">
                <div class="m-field">
                    <i class="f-icon ri-mail-check-line"></i>
                    <input type="text" name="email_code" maxlength="6" placeholder="邮箱验证码" autocomplete="one-time-code">
                </div>
                <button type="button" class="m-sms-btn" id="regEmailSend">发送验证码</button>
            </div>
            <?php endif; ?>

            <div class="m-field">
                <i class="f-icon ri-lock-2-line"></i>
                <input type="password" name="password" placeholder="请设置密码（6-16位）" autocomplete="new-password">
                <i class="f-eye ri-eye-off-line" data-target="password"></i>
            </div>

            <div class="m-field">
                <i class="f-icon ri-lock-check-line"></i>
                <input type="password" name="repassword" placeholder="请再次输入密码" autocomplete="new-password">
                <i class="f-eye ri-eye-off-line" data-target="repassword"></i>
            </div>

            <div class="m-field">
                <i class="f-icon ri-gift-line"></i>
                <input type="text" name="invite_code" id="invite_code" placeholder="邀请码<?= !empty($register_bind_invite) ? '（必填）' : '（选填）' ?>" autocomplete="off" value="<?= !empty($inviteCode) ? htmlspecialchars($inviteCode) : '' ?>" <?= !empty($inviteCode) ? 'readonly style="background:#f7f8fa;color:#86909c;"' : '' ?>>
                <?php if(empty($inviteCode)): ?>
                <button type="button" class="f-clear" tabindex="-1">&times;</button>
                <?php endif; ?>
            </div>

            <?php if($login_code): ?>
            <div class="m-captcha-row">
                <div class="m-field">
                    <i class="f-icon ri-shield-check-line"></i>
                    <input type="text" name="login_code" placeholder="验证码" autocomplete="off">
                </div>
                <img src="../include/lib/checkcode.php" id="checkcode" alt="验证码">
            </div>
            <?php endif; ?>

            <?php doAction('user_register_remember') ?>

            <button type="button" class="m-auth-btn" lay-submit lay-filter="register">注册</button>

        </form>
        <?php endif; ?>
    </div>

    <div class="m-auth-agreement-bottom">
        <input type="checkbox" id="reg_agreement" name="agreement" checked>
        <span>我已阅读并同意<a href="account.php?action=agreement" target="_blank">《用户服务协议》</a>和<a href="account.php?action=privacy" target="_blank">《隐私政策》</a></span>
    </div>
</div>

<script>
layui.use(['form','layer'], function(){
    var form = layui.form, layer = layui.layer, $ = layui.$;

    $('#checkcode').on('click', function(){ $(this).attr('src','../include/lib/checkcode.php?' + Date.now()); });

    // 密码显示/隐藏切换
    $('.f-eye').on('click', function(){
        var $input = $(this).siblings('input');
        if($input.attr('type') === 'password'){
            $input.attr('type','text');
            $(this).removeClass('ri-eye-off-line').addClass('ri-eye-line active');
        } else {
            $input.attr('type','password');
            $(this).removeClass('ri-eye-line active').addClass('ri-eye-off-line');
        }
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
    resumeCountdown($('#regSmsSend'), 'cd_reg_sms');
    resumeCountdown($('#regEmailSend'), 'cd_reg_email');

    // 发送短信验证码
    $('#regSmsSend').on('click', function(){
        var phone = $.trim($('input[name="tel"]').val());
        if(!phone || !/^1[3-9]\d{9}$/.test(phone)) return layer.msg('请输入正确的手机号', {icon:0});
        var $btn = $(this);
        layer.open({
            type:1, title:'安全验证', area:['90%','auto'], shadeClose:true,
            content:'<div style="padding:20px 20px 10px"><div style="color:#64748b;font-size:13px;margin-bottom:10px">请输入图形验证码后发送短信</div><div style="display:flex;gap:10px;align-items:center"><input id="_regSmsCapInput" type="text" maxlength="8" placeholder="验证码" style="flex:1;min-width:0;height:44px;padding:0 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:16px;outline:none;box-sizing:border-box"><img id="_regSmsCapImg" src="'+_captchaUrl+Date.now()+'" style="height:40px;max-width:120px;flex-shrink:0;border-radius:10px;cursor:pointer" title="点击刷新"></div></div>',
            btn:['发送','取消'],
            yes:function(idx){
                var imgcode = $.trim($('#_regSmsCapInput').val());
                if(!imgcode) return layer.msg('请输入图形验证码',{icon:0});
                layer.close(idx);
                $btn.prop('disabled',true).text('发送中...');
                $.ajax({
                    type:'POST', url:'./account.php?action=send_register_sms_code',
                    data:{tel:phone, imgcode:imgcode}, dataType:'json',
                    success:function(e){
                        if(e.code==0){ layer.msg('验证码已发送',{icon:1}); startCountdown($btn,'cd_reg_sms',60); }
                        else { $btn.prop('disabled',false).text('发送验证码'); layer.msg(e.msg||'发送失败',{icon:2}); }
                    },
                    error:function(){ $btn.prop('disabled',false).text('发送验证码'); layer.msg('发送失败',{icon:2}); }
                });
            },
            success:function(layero){
                layero.find('.layui-layer-btn0').css({background:'<?= $_auth_primary ?>',borderColor:'<?= $_auth_primary ?>'});
                layero.find('#_regSmsCapImg').on('click',function(){$(this).attr('src',_captchaUrl+Date.now());});
            }
        });
    });

    // 发送邮箱验证码
    $('#regEmailSend').on('click', function(){
        var email = $.trim($('input[name="email"]').val());
        if(!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return layer.msg('请输入正确的邮箱地址', {icon:0});
        var $btn = $(this);
        layer.open({
            type:1, title:'安全验证', area:['90%','auto'], shadeClose:true,
            content:'<div style="padding:20px 20px 10px"><div style="color:#64748b;font-size:13px;margin-bottom:10px">请输入图形验证码后发送邮件</div><div style="display:flex;gap:10px;align-items:center"><input id="_regEmailCapInput" type="text" maxlength="8" placeholder="验证码" style="flex:1;min-width:0;height:44px;padding:0 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:16px;outline:none;box-sizing:border-box"><img id="_regEmailCapImg" src="'+_captchaUrl+Date.now()+'" style="height:40px;max-width:120px;flex-shrink:0;border-radius:10px;cursor:pointer" title="点击刷新"></div></div>',
            btn:['发送','取消'],
            yes:function(idx){
                var imgcode = $.trim($('#_regEmailCapInput').val());
                if(!imgcode) return layer.msg('请输入图形验证码',{icon:0});
                layer.close(idx);
                $btn.prop('disabled',true).text('发送中...');
                $.ajax({
                    type:'POST', url:'./account.php?action=send_register_email_code',
                    data:{mail:email, imgcode:imgcode}, dataType:'json',
                    success:function(e){
                        if(e.code==0){ layer.msg('验证码已发送至邮箱',{icon:1}); startCountdown($btn,'cd_reg_email',60); }
                        else { $btn.prop('disabled',false).text('发送验证码'); layer.msg(e.msg||'发送失败',{icon:2}); }
                    },
                    error:function(){ $btn.prop('disabled',false).text('发送验证码'); layer.msg('发送失败',{icon:2}); }
                });
            },
            success:function(layero){
                layero.find('.layui-layer-btn0').css({background:'<?= $_auth_primary ?>',borderColor:'<?= $_auth_primary ?>'});
                layero.find('#_regEmailCapImg').on('click',function(){$(this).attr('src',_captchaUrl+Date.now());});
            }
        });
    });

    // 注册提交
    form.on('submit(register)', function(data){
        layer.load(2);
        $.ajax({
            type:'POST', url:'./account.php?action=dosignup',
            data: data.field, dataType:'json',
            success:function(e){
                if(e.code == 0){
                    layer.msg('注册成功，正在跳转');
                    location.href = '/user/';
                } else {
                    <?php doAction('user_register_error') ?>
                    layer.msg(e.msg);
                    $('#checkcode').attr('src','../include/lib/checkcode.php?' + Date.now());
                }
            },
            error:function(xhr){
                try{ layer.msg(JSON.parse(xhr.responseText).msg); }catch(err){ layer.msg('注册失败'); }
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
