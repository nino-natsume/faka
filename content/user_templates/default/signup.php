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
        background:#f3f5f8;
        backdrop-filter:saturate(180%) blur(20px);-webkit-backdrop-filter:saturate(180%) blur(20px);
        border:2px solid #fff;
        border-radius:10px;box-shadow:0 12px 40px rgba(0,0,0,.08);
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
    .auth-field .field-eye{
        position:absolute;right:12px;top:50%;transform:translateY(-50%);
        color:#c9cdd4;font-size:18px;cursor:pointer;padding:4px;transition:color .2s;
    }
    .auth-field .field-eye:hover,.auth-field .field-eye.active{color:<?= $_auth_primary ?>;}
    .auth-captcha{display:flex;gap:8px;margin-bottom:12px;align-items:center;}
    .auth-captcha .auth-field{flex:1;margin:0;}
    .auth-captcha img{height:44px;border-radius:10px;cursor:pointer;border:1px solid #e5e6eb;}
    .auth-agreement{
        margin:6px 0 16px;font-size:12px;color:#86909c;
        display:flex;align-items:flex-start;gap:6px;
    }
    .auth-agreement input{accent-color:<?= $_auth_primary ?>;width:15px;height:15px;margin-top:1px;flex-shrink:0;}
    .auth-agreement a{color:<?= $_auth_primary ?>;text-decoration:none;}
    .auth-agreement a:hover{text-decoration:underline;}
    .auth-invite-tip{
        display:flex;align-items:center;gap:6px;
        padding:8px 12px;border-radius:8px;margin-bottom:12px;
        background:rgba(<?= $_auth_primary_rgb ?>,.08);color:<?= $_auth_primary_dark ?>;font-size:12px;
    }
    .auth-btn{
        width:100%;height:44px;border:none;border-radius:10px;
        background:linear-gradient(135deg,<?= $_auth_primary ?>,<?= $_auth_primary_dark ?>);
        color:#fff;font-size:15px;font-weight:500;cursor:pointer;
        transition:all .25s;box-shadow:0 4px 12px <?= $_auth_shadow_25 ?>;
    }
    .auth-btn:hover{transform:translateY(-1px);box-shadow:0 6px 18px <?= $_auth_shadow_35 ?>;}
    .auth-btn:active{transform:translateY(0);}
    .auth-footer{
        display:flex;align-items:center;justify-content:space-between;
        margin-top:16px;font-size:13px;color:#86909c;
    }
    .auth-footer a{color:#86909c;text-decoration:none;display:flex;align-items:center;gap:4px;}
    .auth-footer a:hover{color:<?= $_auth_primary ?>;}
    .auth-footer a.auth-login{color:<?= $_auth_primary ?>;font-weight:500;}
    .auth-sms-row{display:flex;gap:8px;margin-bottom:12px;align-items:center;}
    .auth-sms-row .auth-field{flex:1;margin:0;}
    .auth-sms-row .sms-send-btn{
        flex-shrink:0;height:44px;padding:0 16px;border:none;border-radius:10px;
        background:linear-gradient(135deg,<?= $_auth_primary ?>,<?= $_auth_primary_dark ?>);color:#fff;font-size:13px;font-weight:500;
        cursor:pointer;white-space:nowrap;transition:all .25s;
    }
    .auth-sms-row .sms-send-btn:hover{box-shadow:0 4px 12px <?= $_auth_shadow_25 ?>;}
    .auth-sms-row .sms-send-btn:disabled{opacity:.55;cursor:not-allowed;}
    .display-hide{display:none!important;}
    @media(max-width:480px){
        body{padding:16px;align-items:flex-start;padding-top:6vh;}
        .auth-card{border-radius:14px;}
        .auth-header{padding:24px 20px 0;}
        .auth-body{padding:18px 20px 22px;}
        .auth-avatar{width:60px;height:60px;font-size:28px;}
        .auth-footer{font-size:12px;}
    }
</style>
<?php doAction('user_register') ?>

<div class="auth-card">
    <div class="auth-header">
        <div class="auth-avatar"><i class="ri-user-add-line"></i></div>
        <div class="auth-title">创建账号</div>
        <div class="auth-tip">注册后即可享受全部功能</div>
    </div>

    <div class="auth-body">
        <?php
        $_hasUsername = Option::get('register_username_switch') == 'y';
        $_hasTel = Option::get('register_tel_switch') == 'y';
        $_hasEmail = Option::get('register_email_switch') == 'y';
        $_noneOpen = !$_hasUsername && !$_hasTel && !$_hasEmail;
        ?>
        <?php if($_noneOpen): ?>
        <div style="text-align:center;padding:20px 0 8px;">
            <i class="ri-lock-line" style="font-size:40px;color:#c9cdd4;display:block;margin-bottom:10px;"></i>
            <div style="font-size:15px;color:#4e5969;font-weight:500;margin-bottom:6px;">暂未开放注册</div>
            <div style="font-size:13px;color:#86909c;line-height:1.6;">管理员尚未开启任何注册方式，<br>请联系管理员或稍后再试。</div>
        </div>
        <div class="auth-footer">
            <a href="account.php?action=signin" class="auth-login"><i class="ri-login-box-line"></i> 立即登录</a>
            <a href="../"><i class="ri-home-4-line"></i> 返回首页</a>
        </div>
        <?php else: ?>
        <form class="layui-form" lay-filter="registerForm">
            <?php if($_hasUsername): ?>
            <div class="auth-field">
                <i class="field-icon ri-user-3-line"></i>
                <input type="text" name="username" placeholder="<?= ($_hasTel || $_hasEmail) ? '用户名（3-20位）' : '请输入用户名（3-20位）' ?>" autocomplete="off">
                <button type="button" class="field-clear" tabindex="-1">&times;</button>
            </div>
            <?php endif; ?>

            <?php if($_hasTel): ?>
            <div class="auth-field">
                <i class="field-icon ri-phone-line"></i>
                <input type="tel" name="tel" placeholder="手机号码" autocomplete="off">
                <button type="button" class="field-clear" tabindex="-1">&times;</button>
            </div>
            <div class="auth-sms-row">
                <div class="auth-field">
                    <i class="field-icon ri-message-2-line"></i>
                    <input type="text" name="sms_code" maxlength="6" placeholder="短信验证码" autocomplete="one-time-code">
                </div>
                <button type="button" class="sms-send-btn" id="regSmsSend">发送验证码</button>
            </div>
            <?php endif; ?>

            <?php if($_hasEmail): ?>
            <div class="auth-field">
                <i class="field-icon ri-mail-line"></i>
                <input type="text" name="email" placeholder="邮箱地址" autocomplete="off">
                <button type="button" class="field-clear" tabindex="-1">&times;</button>
            </div>
            <div class="auth-sms-row">
                <div class="auth-field">
                    <i class="field-icon ri-mail-check-line"></i>
                    <input type="text" name="email_code" maxlength="6" placeholder="邮箱验证码" autocomplete="one-time-code">
                </div>
                <button type="button" class="sms-send-btn" id="regEmailSend">发送验证码</button>
            </div>
            <?php endif; ?>

            <div class="auth-field">
                <i class="field-icon ri-lock-2-line"></i>
                <input type="password" name="password" placeholder="请设置密码（6-16位）" autocomplete="new-password">
                <i class="field-eye ri-eye-off-line" data-target="password"></i>
            </div>

            <div class="auth-field">
                <i class="field-icon ri-lock-check-line"></i>
                <input type="password" name="repassword" placeholder="请再次输入密码" autocomplete="new-password">
                <i class="field-eye ri-eye-off-line" data-target="repassword"></i>
            </div>

            <div class="auth-field">
                <i class="field-icon ri-gift-line"></i>
                <input type="text" name="invite_code" id="invite_code" placeholder="邀请码<?= !empty($register_bind_invite) ? '（必填）' : '（选填）' ?>" autocomplete="off" value="<?= !empty($inviteCode) ? htmlspecialchars($inviteCode) : '' ?>" <?= !empty($inviteCode) ? 'readonly style="background:#f7f8fa;color:#86909c;"' : '' ?>>
                <?php if(empty($inviteCode)): ?>
                <button type="button" class="field-clear" tabindex="-1">&times;</button>
                <?php endif; ?>
            </div>

            <?php if($login_code): ?>
            <div class="auth-captcha">
                <div class="auth-field">
                    <i class="field-icon ri-shield-check-line"></i>
                    <input type="text" name="login_code" placeholder="验证码" autocomplete="off">
                </div>
                <img src="../include/lib/checkcode.php" id="checkcode" alt="验证码">
            </div>
            <?php endif; ?>

            <?php doAction('user_register_remember') ?>

            <div class="auth-agreement">
                <input type="checkbox" name="agreement" checked lay-verify="required">
                <span>我已阅读并同意<a href="account.php?action=agreement" target="_blank">《用户服务协议》</a>和<a href="account.php?action=privacy" target="_blank">《隐私政策》</a></span>
            </div>

            <button type="button" class="auth-btn" lay-submit lay-filter="register">注册</button>

            <div class="auth-footer">
                <a href="account.php?action=signin" class="auth-login"><i class="ri-login-box-line"></i> 立即登录</a>
                <a href="../"><i class="ri-home-4-line"></i> 返回首页</a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<script>
layui.use(['form','layer'], function(){
    var form = layui.form, layer = layui.layer, $ = layui.$;

    // 验证码点击刷新
    $('#checkcode').on('click', function(){ $(this).attr('src','../include/lib/checkcode.php?' + Date.now()); });

    // 密码显示/隐藏切换
    $('.field-eye').on('click', function(){
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

    // 倒计时工具（localStorage 持久化）
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
        if(end > Math.floor(Date.now()/1000)){
            _runCountdown($btn, key, end);
        }
    }
    resumeCountdown($('#regSmsSend'), 'cd_reg_sms');
    resumeCountdown($('#regEmailSend'), 'cd_reg_email');

    // 手机注册：发送短信验证码
    $('#regSmsSend').on('click', function(){
        var phone = $.trim($('input[name="tel"]').val());
        if(!phone || !/^1[3-9]\d{9}$/.test(phone)){
            return layer.msg('请输入正确的手机号', {icon:0});
        }
        var $btn = $(this);
        layer.open({
            type:1, title:'安全验证', area:['340px','auto'], shadeClose:true,
            content:'<div style="padding:20px 24px 10px;overflow:hidden">' +
                '<div style="color:#64748b;font-size:13px;margin-bottom:10px">请输入图形验证码后发送短信</div>' +
                '<div style="display:flex;gap:10px;align-items:center;overflow:hidden">' +
                '<input id="_regSmsCapInput" type="text" maxlength="8" placeholder="验证码" style="flex:1;min-width:0;height:44px;padding:0 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:16px;outline:none;box-sizing:border-box">' +
                '<img id="_regSmsCapImg" src="' + _captchaUrl + Date.now() + '" style="height:40px;max-width:120px;flex-shrink:0;border-radius:6px;cursor:pointer" title="点击刷新">' +
                '</div></div>',
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
                        if(e.code==0){
                            layer.msg('验证码已发送',{icon:1});
                            startCountdown($btn,'cd_reg_sms',60);
                        } else {
                            $btn.prop('disabled',false).text('发送验证码');
                            layer.msg(e.msg||'发送失败',{icon:2});
                        }
                    },
                    error:function(xhr){
                        $btn.prop('disabled',false).text('发送验证码');
                        try{layer.msg(JSON.parse(xhr.responseText).msg,{icon:2});}catch(err){layer.msg('发送失败',{icon:2});}
                    }
                });
            },
            success:function(layero){
                layero.find('.layui-layer-btn0').css({background:'#34c759',borderColor:'#34c759'});
                layero.find('#_regSmsCapImg').on('click',function(){$(this).attr('src',_captchaUrl+Date.now());});
            }
        });
    });

    // 邮箱注册：发送邮箱验证码
    $('#regEmailSend').on('click', function(){
        var email = $.trim($('input[name="email"]').val());
        if(!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){
            return layer.msg('请输入正确的邮箱地址', {icon:0});
        }
        var $btn = $(this);
        layer.open({
            type:1, title:'安全验证', area:['340px','auto'], shadeClose:true,
            content:'<div style="padding:20px 24px 10px;overflow:hidden">' +
                '<div style="color:#64748b;font-size:13px;margin-bottom:10px">请输入图形验证码后发送邮件</div>' +
                '<div style="display:flex;gap:10px;align-items:center;overflow:hidden">' +
                '<input id="_regEmailCapInput" type="text" maxlength="8" placeholder="验证码" style="flex:1;min-width:0;height:44px;padding:0 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:16px;outline:none;box-sizing:border-box">' +
                '<img id="_regEmailCapImg" src="' + _captchaUrl + Date.now() + '" style="height:40px;max-width:120px;flex-shrink:0;border-radius:6px;cursor:pointer" title="点击刷新">' +
                '</div></div>',
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
                        if(e.code==0){
                            layer.msg('验证码已发送至邮箱',{icon:1});
                            startCountdown($btn,'cd_reg_email',60);
                        } else {
                            $btn.prop('disabled',false).text('发送验证码');
                            layer.msg(e.msg||'发送失败',{icon:2});
                        }
                    },
                    error:function(xhr){
                        $btn.prop('disabled',false).text('发送验证码');
                        try{layer.msg(JSON.parse(xhr.responseText).msg,{icon:2});}catch(err){layer.msg('发送失败',{icon:2});}
                    }
                });
            },
            success:function(layero){
                layero.find('.layui-layer-btn0').css({background:'#34c759',borderColor:'#34c759'});
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
</body>
</html>
