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
        border-radius:10px;
        box-shadow:0 12px 40px rgba(0,0,0,.08);
        overflow:visible;
        animation:authPop .45s cubic-bezier(.2,.8,.2,1);
    }
    @keyframes authPop{from{opacity:0;transform:translateY(16px) scale(.97);}to{opacity:1;transform:none;}}
    .auth-header{
        padding:32px 32px 0;text-align:center;
    }
    .auth-avatar{
        width:72px;height:72px;margin:0 auto 12px;border-radius:50%;
        background:linear-gradient(135deg,<?= $_auth_primary ?>,<?= $_auth_primary_dark ?>);
        display:flex;align-items:center;justify-content:center;
        color:#fff;font-size:32px;
        box-shadow:0 6px 18px <?= $_auth_shadow_30 ?>;
    }
    .auth-title{font-size:20px;font-weight:600;color:#1a1a1a;margin-bottom:2px;}
    .auth-tip{font-size:13px;color:#86909c;margin-bottom:0;}
    .auth-body{padding:24px 32px 28px;}
    .auth-toggle{
        display:flex;justify-content:center;gap:20px;margin-top:16px;position:relative;
    }
    .auth-toggle-item{
        width:44px;height:44px;border-radius:50%;border:none;
        font-size:18px;
        display:flex;align-items:center;justify-content:center;
        cursor:pointer;transition:all .25s;position:relative;
    }
    .auth-toggle-item[data-type="username"]{background:rgba(124,77,255,.12);color:#7c4dff;}
    .auth-toggle-item[data-type="username"]:hover{background:rgba(124,77,255,.22);}
    .auth-toggle-item[data-type="tel"]{background:rgba(76,175,80,.12);color:#43a047;}
    .auth-toggle-item[data-type="tel"]:hover{background:rgba(76,175,80,.22);}
    .auth-toggle-item[data-type="email"]{background:rgba(255,152,0,.12);color:#f57c00;}
    .auth-toggle-item[data-type="email"]:hover{background:rgba(255,152,0,.22);}
    .auth-toggle-item .toggle-tip{
        position:absolute;bottom:-22px;left:50%;transform:translateX(-50%);
        font-size:11px;color:#86909c;white-space:nowrap;opacity:0;transition:opacity .2s;
    }
    .auth-toggle-item:hover .toggle-tip{opacity:1;}
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
    .auth-row{
        display:flex;align-items:center;justify-content:space-between;
        margin:4px 0 18px;font-size:13px;color:#86909c;
    }
    .auth-row a{color:<?= $_auth_primary ?>;text-decoration:none;font-size:13px;}
    .auth-row a:hover{text-decoration:underline;}
    .auth-row label{display:flex;align-items:center;gap:6px;cursor:pointer;user-select:none;}
    .auth-row label input{accent-color:<?= $_auth_primary ?>;width:15px;height:15px;}
    .auth-btn{
        width:100%;height:44px;border:none;border-radius:10px;
        background:linear-gradient(135deg,<?= $_auth_primary ?>,<?= $_auth_primary_dark ?>);
        color:#fff;font-size:15px;font-weight:500;cursor:pointer;
        transition:all .25s;box-shadow:0 4px 12px <?= $_auth_shadow_25 ?>;
    }
    .auth-btn:hover{transform:translateY(-1px);box-shadow:0 6px 18px <?= $_auth_shadow_35 ?>;}
    .auth-btn:active{transform:translateY(0);}
    .auth-toggle .qqlr-login-wrap{
        margin:0!important;
        width:44px;
        height:44px;
        flex:0 0 44px;
        display:flex!important;
        align-items:center;
        justify-content:center;
        position:relative;
    }
    .auth-toggle .qqlr-login-btn{
        width:44px!important;
        height:44px!important;
        min-width:44px!important;
        padding:0!important;
        border-radius:50%!important;
        background:rgba(18,183,245,.12)!important;
        color:#12b7f5!important;
        display:flex!important;
        align-items:center!important;
        justify-content:center!important;
        gap:0!important;
        text-decoration:none!important;
        box-shadow:none!important;
        font-size:18px!important;
        font-weight:400!important;
        transition:all .25s!important;
    }
    .auth-toggle .qqlr-login-btn:hover{background:rgba(18,183,245,.22)!important;color:#12b7f5!important;transform:none;box-shadow:none!important;}
    .auth-toggle .qqlr-login-btn span{
        position:absolute!important;
        left:50%!important;
        bottom:-22px!important;
        transform:translateX(-50%)!important;
        width:auto!important;
        height:auto!important;
        padding:0!important;
        margin:0!important;
        overflow:visible!important;
        clip:auto!important;
        white-space:nowrap!important;
        border:0!important;
        color:#86909c!important;
        font-size:11px!important;
        line-height:1!important;
        font-weight:400!important;
        opacity:0;
        transition:opacity .2s;
    }
    .auth-toggle .qqlr-login-wrap:hover span{opacity:1;}
    .auth-toggle .qqlr-login-btn i{font-size:22px!important;line-height:1!important;}
    .auth-toggle .wclr-login-wrap{
        margin:0!important;
        width:44px;
        height:44px;
        flex:0 0 44px;
        display:flex!important;
        align-items:center;
        justify-content:center;
        position:relative;
    }
    .auth-toggle .wclr-login-btn{
        width:44px!important;
        height:44px!important;
        min-width:44px!important;
        padding:0!important;
        border-radius:50%!important;
        background:rgba(7,193,96,.12)!important;
        color:#07c160!important;
        display:flex!important;
        align-items:center!important;
        justify-content:center!important;
        gap:0!important;
        text-decoration:none!important;
        box-shadow:none!important;
        font-size:18px!important;
        font-weight:400!important;
        transition:all .25s!important;
    }
    .auth-toggle .wclr-login-btn:hover{background:rgba(7,193,96,.22)!important;color:#07c160!important;transform:none;box-shadow:none!important;}
    .auth-toggle .wclr-login-btn span{
        position:absolute!important;
        left:50%!important;
        bottom:-22px!important;
        transform:translateX(-50%)!important;
        width:auto!important;
        height:auto!important;
        padding:0!important;
        margin:0!important;
        overflow:visible!important;
        clip:auto!important;
        white-space:nowrap!important;
        border:0!important;
        color:#86909c!important;
        font-size:11px!important;
        line-height:1!important;
        font-weight:400!important;
        opacity:0;
        transition:opacity .2s;
    }
    .auth-toggle .wclr-login-wrap:hover span{opacity:1;}
    .auth-toggle .wclr-login-btn i{font-size:22px!important;line-height:1!important;}
    .auth-wechat-panel{margin:0 0 14px;text-align:center;}
    .auth-wechat-card{background:#fff;border:1px solid #e5e6eb;border-radius:14px;padding:14px;box-shadow:0 8px 24px rgba(15,23,42,.04);}
    .auth-wechat-head{display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:10px;color:#07c160;font-size:13px;font-weight:500;}
    .auth-wechat-head i{font-size:20px;}
    .auth-wechat-frame-wrap{position:relative;height:240px;background:#fff;border-radius:12px;overflow:hidden;}
    .auth-wechat-frame{display:block;width:100%;height:400px;border:0;background:#fff;overflow:hidden;transform:scale(.83);transform-origin:top center;}
    .auth-wechat-frame-wrap:after{content:"";position:absolute;left:0;right:0;bottom:0;height:0px;background:#fff;z-index:1;pointer-events:none;}
    .auth-wechat-loading{position:absolute;inset:0;z-index:2;display:flex;align-items:center;justify-content:center;gap:8px;background:#fff;color:#86909c;font-size:13px;}
    .auth-wechat-panel.is-loaded .auth-wechat-loading{display:none;}
    .auth-wechat-spin{width:16px;height:16px;border-radius:50%;border:2px solid rgba(7,193,96,.18);border-top-color:#07c160;animation:authWechatSpin .8s linear infinite;}
    @keyframes authWechatSpin{to{transform:rotate(360deg)}}
    .auth-wechat-refresh{border:0;background:transparent;color:#07c160;font-size:12px;cursor:pointer;padding:4px 8px;}
    .auth-wechat-refresh:hover{text-decoration:underline;}
    .auth-toggle .auth-overflow-hidden,
    .auth-toggle .auth-toggle-item.auth-overflow-hidden,
    .auth-toggle .qqlr-login-wrap.auth-overflow-hidden,
    .auth-toggle .wclr-login-wrap.auth-overflow-hidden{display:none!important;}
    .auth-more-btn{
        width:44px;height:44px;border:0;border-radius:50%;
        background:rgba(100,116,139,.12);color:#64748b;
        display:none;align-items:center;justify-content:center;
        cursor:pointer;transition:all .25s;position:relative;
        font-size:20px;padding:0;
    }
    .auth-more-btn:hover,.auth-more-btn.is-active{background:rgba(100,116,139,.22);color:#475569;}
    .auth-more-btn .toggle-tip{
        position:absolute;bottom:-22px;left:50%;transform:translateX(-50%);
        font-size:11px;color:#86909c;white-space:nowrap;opacity:0;transition:opacity .2s;
    }
    .auth-more-btn:hover .toggle-tip{opacity:1;}
    .auth-more-popover{
        position:absolute;left:50%;bottom:58px;transform:translateX(-50%) translateY(-8px);
        width:292px;padding:14px;background:rgba(255,255,255,.96);
        border:1px solid rgba(226,232,240,.9);border-radius:18px;
        box-shadow:0 18px 48px rgba(15,23,42,.14);
        backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);
        z-index:30;display:none;opacity:0;pointer-events:none;transition:all .18s ease;
    }
    .auth-more-popover.is-active{display:block;opacity:1;pointer-events:auto;transform:translateX(-50%) translateY(0);}
    .auth-more-popover::before{
        content:'';position:absolute;bottom:-7px;left:50%;width:12px;height:12px;
        background:rgba(255,255,255,.96);border-right:1px solid rgba(226,232,240,.9);
        border-bottom:1px solid rgba(226,232,240,.9);transform:translateX(-50%) rotate(45deg);
    }
    .auth-more-title{font-size:13px;font-weight:700;color:#1f2937;margin:2px 4px 12px;text-align:left;}
    .auth-more-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;}
    .auth-more-method{
        border:0;background:transparent;text-decoration:none;color:#475569;
        display:flex;flex-direction:column;align-items:center;gap:7px;
        padding:9px 4px;border-radius:14px;cursor:pointer;transition:background .18s,transform .18s;
        font-size:12px;font-weight:500;
    }
    .auth-more-method:hover{background:#f8fafc;text-decoration:none;color:#334155;transform:translateY(-1px);}
    .auth-more-icon{
        width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;
        font-size:20px;background:#eef2ff;color:<?= $_auth_primary ?>;
    }
    .auth-more-method[data-type="tel"] .auth-more-icon{background:rgba(76,175,80,.12);color:#43a047;}
    .auth-more-method[data-type="email"] .auth-more-icon{background:rgba(255,152,0,.12);color:#f57c00;}
    .auth-more-method[data-type="username"] .auth-more-icon{background:rgba(124,77,255,.12);color:#7c4dff;}
    .auth-more-method[data-provider="qq"] .auth-more-icon{background:rgba(18,183,245,.12);color:#12b7f5;}
    .auth-more-method[data-provider="wechat"] .auth-more-icon{background:rgba(7,193,96,.12);color:#07c160;}
    .auth-more-name{max-width:100%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .auth-footer{
        display:flex;align-items:center;justify-content:space-between;
        margin-top:16px;font-size:13px;color:#86909c;
    }
    .auth-footer a{color:#86909c;text-decoration:none;display:flex;align-items:center;gap:4px;}
    .auth-footer a:hover{color:<?= $_auth_primary ?>;}
    .auth-footer a.auth-reg{color:<?= $_auth_primary ?>;font-weight:500;}
    .auth-sms-row{display:flex;gap:8px;margin-bottom:12px;align-items:center;}
    .display-hide{display:none!important;}
    .auth-toggle .auth-toggle-item.display-hide,
    .auth-toggle .qqlr-login-wrap.display-hide,
    .auth-toggle .wclr-login-wrap.display-hide{display:none!important;}
    .auth-sms-row .auth-field{flex:1;margin:0;}
    .auth-sms-row .sms-send-btn{
        flex-shrink:0;height:44px;padding:0 16px;border:none;border-radius:10px;
        background:linear-gradient(135deg,<?= $_auth_primary ?>,<?= $_auth_primary_dark ?>);color:#fff;font-size:13px;font-weight:500;
        cursor:pointer;white-space:nowrap;transition:all .25s;
    }
    .auth-sms-row .sms-send-btn:hover{box-shadow:0 4px 12px <?= $_auth_shadow_25 ?>;}
    .auth-sms-row .sms-send-btn:disabled{opacity:.55;cursor:not-allowed;}
    @media(max-width:480px){
        body{padding:16px;align-items:flex-start;padding-top:8vh;}
        .auth-card{border-radius:14px;}
        .auth-header{padding:24px 20px 0;}
        .auth-body{padding:18px 20px 22px;}
        .auth-avatar{width:60px;height:60px;font-size:28px;}
    }
</style>
<?php doAction('user_login') ?>

<div class="auth-card">
    <div class="auth-header">
        <?php $__login_icon = Option::get('personal_center_icon'); ?>
        <div class="auth-avatar"><?php if (!empty($__login_icon)): ?><img src="<?= htmlspecialchars(getFileUrl($__login_icon)) ?>" alt="Logo" style="width:100%;height:100%;object-fit:cover;border-radius:50%;"><?php else: ?><i class="ri-user-smile-line"></i><?php endif; ?></div>
        <div class="auth-title"><?= htmlspecialchars($blogname) ?></div>
        <div class="auth-tip"><?= $__greeting ?></div>
    </div>

    <div class="auth-body">
        <?php
        $_login_tabs = [];
        if(Option::get('login_username_switch') == 'y') $_login_tabs[] = ['key'=>'username','label'=>'用户名','icon'=>'ri-user-3-line'];
        if(Option::get('login_tel_switch') == 'y') $_login_tabs[] = ['key'=>'tel','label'=>'手机','icon'=>'ri-phone-line'];
        if(Option::get('login_email_switch') == 'y') $_login_tabs[] = ['key'=>'email','label'=>'邮箱','icon'=>'ri-mail-line'];
        ob_start(); doAction('user_login_remember'); $_login_oauth_html = trim(ob_get_clean());
        $_wechat_login_url = '';
        if (preg_match('/class="[^"]*wclr-login-btn[^"]*"[^>]*href="([^"]+)"/i', $_login_oauth_html, $_wechat_match)) {
            $_wechat_login_url = html_entity_decode($_wechat_match[1], ENT_QUOTES, 'UTF-8');
        } elseif (preg_match('/href="([^"]+)"[^>]*class="[^"]*wclr-login-btn[^"]*"/i', $_login_oauth_html, $_wechat_match)) {
            $_wechat_login_url = html_entity_decode($_wechat_match[1], ENT_QUOTES, 'UTF-8');
        }
        if ($_wechat_login_url !== '') {
            $_wechat_login_url .= (strpos($_wechat_login_url, '?') === false ? '?' : '&') . 'wclr_frame=1';
        }
        ?>

        <form class="layui-form" lay-filter="loginForm">
            <?php if(Option::get('login_username_switch') == 'y'): ?>
            <div class="auth-field username-field <?= $default_type !== 'username' ? 'display-hide' : '' ?>">
                <i class="field-icon ri-user-3-line"></i>
                <input type="text" name="username" placeholder="请输入用户名" autocomplete="username">
                <button type="button" class="field-clear" tabindex="-1">&times;</button>
            </div>
            <?php endif; ?>

            <?php if(Option::get('login_tel_switch') == 'y'): ?>
            <div class="auth-field phone-field <?= $default_type !== 'tel' ? 'display-hide' : '' ?>">
                <i class="field-icon ri-phone-line"></i>
                <input type="tel" name="tel" placeholder="请输入手机号码" autocomplete="tel">
                <button type="button" class="field-clear" tabindex="-1">&times;</button>
            </div>
            <?php endif; ?>

            <?php if(Option::get('login_email_switch') == 'y'): ?>
            <div class="auth-field email-field <?= $default_type !== 'email' ? 'display-hide' : '' ?>">
                <i class="field-icon ri-mail-line"></i>
                <input type="text" name="email" placeholder="请输入邮箱地址" autocomplete="email">
                <button type="button" class="field-clear" tabindex="-1">&times;</button>
            </div>
            <?php endif; ?>

            <div class="auth-field password-field">
                <i class="field-icon ri-lock-2-line"></i>
                <input type="password" name="password" placeholder="请输入密码" autocomplete="current-password">
                <i class="field-eye ri-eye-off-line" id="pwdEye"></i>
            </div>

            <?php if(Option::get('login_tel_switch') == 'y'): ?>
            <div class="auth-sms-row sms-code-field <?= $default_type !== 'tel' ? 'display-hide' : '' ?>">
                <div class="auth-field">
                    <i class="field-icon ri-message-2-line"></i>
                    <input type="text" name="sms_code" maxlength="6" placeholder="短信验证码" autocomplete="one-time-code">
                </div>
                <button type="button" class="sms-send-btn" id="loginSmsSend">发送验证码</button>
            </div>
            <?php endif; ?>

            <?php if(Option::get('login_email_switch') == 'y'): ?>
            <div class="auth-sms-row email-code-field <?= $default_type !== 'email' ? 'display-hide' : '' ?>">
                <div class="auth-field">
                    <i class="field-icon ri-mail-check-line"></i>
                    <input type="text" name="email_code" maxlength="6" placeholder="邮箱验证码" autocomplete="one-time-code">
                </div>
                <button type="button" class="sms-send-btn" id="loginEmailSend">发送验证码</button>
            </div>
            <?php endif; ?>

            <?php if($_wechat_login_url !== ''): ?>
            <div class="auth-wechat-panel display-hide">
                <div class="auth-wechat-card">
                    <div class="auth-wechat-head"><i class="ri-wechat-fill"></i><span>微信快捷登录</span></div>
                    <div class="auth-wechat-frame-wrap">
                        <div class="auth-wechat-loading"><span class="auth-wechat-spin"></span><span>正在加载微信登录二维码...</span></div>
                        <iframe class="auth-wechat-frame" title="微信扫码登录" data-src="<?= htmlspecialchars($_wechat_login_url, ENT_QUOTES, 'UTF-8') ?>"></iframe>
                    </div>
                    <button type="button" class="auth-wechat-refresh">刷新二维码</button>
                </div>
            </div>
            <?php endif; ?>

            <?php if($login_code): ?>
            <div class="auth-captcha pwd-login-only">
                <div class="auth-field">
                    <i class="field-icon ri-shield-check-line"></i>
                    <input type="text" name="login_code" placeholder="验证码" autocomplete="off">
                </div>
                <img src="../include/lib/checkcode.php" id="checkcode" alt="验证码">
            </div>
            <?php endif; ?>

            <div class="auth-row">
                <label><input type="checkbox" name="persist" value="1"><span>记住我</span></label>
                <a href="account.php?action=reset" class="pwd-login-only">忘记密码？</a>
            </div>

            <input type="hidden" name="type" id="type" value="<?= $default_type ?>">
            <button type="button" class="auth-btn" lay-submit lay-filter="login">登录</button>

            <div class="auth-footer">
                <?php if(Option::get('register_switch') === 'y'): ?>
                <a href="account.php?action=signup" class="auth-reg"><i class="ri-user-add-line"></i> 立即注册</a>
                <?php else: ?>
                <span></span>
                <?php endif; ?>
                <a href="../"><i class="ri-home-4-line"></i> 返回首页</a>
            </div>

            <?php if(count($_login_tabs) > 1 || $_login_oauth_html !== ''): ?>
            <div class="auth-toggle">
                <?php foreach($_login_tabs as $i => $tab): ?>
                <div class="auth-toggle-item" data-type="<?= $tab['key'] ?>">
                    <i class="<?= $tab['icon'] ?>"></i>
                    <span class="toggle-tip"><?= $tab['label'] ?></span>
                </div>
                <?php endforeach; ?>
                <?= $_login_oauth_html ?>
                <button type="button" class="auth-more-btn" aria-label="更多登录方式">
                    <i class="ri-more-fill"></i>
                    <span class="toggle-tip">更多</span>
                </button>
                <div class="auth-more-popover" role="dialog" aria-modal="false" aria-label="更多登录方式">
                    <div class="auth-more-title">更多登录方式</div>
                    <div class="auth-more-grid"></div>
                </div>
            </div>
            <?php endif; ?>
        </form>
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

    var loginMethodLabels = {username:'用户名', tel:'手机', email:'邮箱'};
    function authEscapeHtml(text){
        return String(text || '').replace(/[&<>"']/g, function(s){
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s];
        });
    }
    function getAuthMethodData(el){
        var $el = $(el);
        var data = {type:'', provider:'', href:'', icon:'ri-login-circle-line', label:'登录'};
        if($el.hasClass('auth-toggle-item')){
            data.type = String($el.data('type') || '');
            data.icon = $.trim($el.find('i').first().attr('class') || data.icon);
            data.label = $.trim($el.find('.toggle-tip').first().text()) || loginMethodLabels[data.type] || '账号';
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
    function renderAuthMoreMethods($toggle, items){
        var html = '';
        for(var i = 0; i < items.length; i++){
            var data = getAuthMethodData(items[i]);
            var attrs = '';
            if(data.type) attrs += ' data-type="' + authEscapeHtml(data.type) + '"';
            if(data.provider) attrs += ' data-provider="' + authEscapeHtml(data.provider) + '"';
            if(data.provider === 'wechat'){
                html += '<button type="button" class="auth-more-method" data-provider="wechat">' +
                    '<span class="auth-more-icon"><i class="' + authEscapeHtml(data.icon) + '"></i></span>' +
                    '<span class="auth-more-name">' + authEscapeHtml(data.label) + '</span></button>';
            }else if(data.href){
                html += '<a class="auth-more-method" href="' + authEscapeHtml(data.href) + '"' + attrs + '>' +
                    '<span class="auth-more-icon"><i class="' + authEscapeHtml(data.icon) + '"></i></span>' +
                    '<span class="auth-more-name">' + authEscapeHtml(data.label) + '</span></a>';
            }else{
                html += '<button type="button" class="auth-more-method"' + attrs + '>' +
                    '<span class="auth-more-icon"><i class="' + authEscapeHtml(data.icon) + '"></i></span>' +
                    '<span class="auth-more-name">' + authEscapeHtml(data.label) + '</span></button>';
            }
        }
        $toggle.find('.auth-more-grid').html(html);
    }
    function closeAuthMore(){
        $('.auth-more-btn').removeClass('is-active');
        $('.auth-more-popover').removeClass('is-active');
    }
    function organizeAuthMethods(){
        var $toggle = $('.auth-toggle');
        if(!$toggle.length) return;
        var $items = $toggle.children('.auth-toggle-item,.qqlr-login-wrap,.wclr-login-wrap');
        var visible = [];
        $items.removeClass('auth-overflow-hidden');
        $items.each(function(){
            if($(this).hasClass('display-hide')) return;
            visible.push(this);
        });
        var overflow = visible.length > 3;
        var $moreBtn = $toggle.children('.auth-more-btn');
        if(overflow){
            for(var i = 3; i < visible.length; i++) $(visible[i]).addClass('auth-overflow-hidden');
            $moreBtn.css('display', 'flex');
            renderAuthMoreMethods($toggle, visible);
        }else{
            $moreBtn.hide();
            closeAuthMore();
            $toggle.find('.auth-more-grid').empty();
        }
    }
    $('.auth-toggle').on('click', '.auth-more-btn', function(e){
        e.preventDefault();
        e.stopPropagation();
        organizeAuthMethods();
        $(this).toggleClass('is-active');
        $(this).siblings('.auth-more-popover').toggleClass('is-active');
    });
    $('.auth-toggle').on('click', '.auth-more-method[data-type]', function(e){
        e.preventDefault();
        var type = $(this).data('type');
        $('#type').val(type);
        switchLoginType(type);
        closeAuthMore();
    });
    $('.auth-toggle').on('click', '.wclr-login-btn', function(e){
        e.preventDefault();
        $('#type').val('wechat');
        switchLoginType('wechat');
    });
    $('.auth-toggle').on('click', '.auth-more-method[data-provider="wechat"]', function(e){
        e.preventDefault();
        $('#type').val('wechat');
        switchLoginType('wechat');
        closeAuthMore();
    });
    $(document).on('click', function(e){
        if(!$(e.target).closest('.auth-toggle').length) closeAuthMore();
    }).on('keydown', function(e){
        if(e.key === 'Escape') closeAuthMore();
    });
    function loadWechatFrame(force){
        var $panel = $('.auth-wechat-panel');
        var $frame = $panel.find('.auth-wechat-frame');
        if(!$frame.length) return;
        var src = $frame.attr('data-src') || '';
        if(!src) return;
        if(force || !$frame.attr('src')){
            $panel.removeClass('is-loaded');
            $frame.attr('src', src + (src.indexOf('?') === -1 ? '?' : '&') + '_t=' + Date.now());
        }
    }
    $('.auth-wechat-frame').on('load', function(){
        $('.auth-wechat-panel').addClass('is-loaded');
    });
    $('.auth-wechat-refresh').on('click', function(e){
        e.preventDefault();
        loadWechatFrame(true);
    });

    // 登录方式切换（统一用 addClass/removeClass 操控 display-hide，避免 !important 冲突）
    function switchLoginType(type) {
        // 隐藏当前方式的图标，只显示其他方式
        $('.auth-toggle-item').removeClass('display-hide');
        $('.auth-toggle-item[data-type="'+type+'"]').addClass('display-hide');
        $('.wclr-login-wrap').toggleClass('display-hide', type === 'wechat');
        closeAuthMore();

        $('.phone-field,.email-field,.username-field').addClass('display-hide');
        $('.sms-code-field,.email-code-field').addClass('display-hide');
        $('.auth-wechat-panel').addClass('display-hide');
        if(type === 'wechat'){
            $('.password-field,.auth-captcha,.auth-row,.auth-btn').addClass('display-hide');
            $('.pwd-login-only').addClass('display-hide');
            $('.auth-wechat-panel').removeClass('display-hide');
            loadWechatFrame(false);
        } else if(type === 'tel'){
            $('.phone-field').removeClass('display-hide');
            $('.password-field').addClass('display-hide');
            $('.sms-code-field').removeClass('display-hide');
            $('.pwd-login-only').addClass('display-hide');
            $('.auth-row,.auth-btn').removeClass('display-hide');
        } else if(type === 'email'){
            $('.email-field').removeClass('display-hide');
            $('.password-field').addClass('display-hide');
            $('.email-code-field').removeClass('display-hide');
            $('.pwd-login-only').addClass('display-hide');
            $('.auth-row,.auth-btn').removeClass('display-hide');
        } else {
            if(type === 'username') $('.username-field').removeClass('display-hide');
            $('.password-field').removeClass('display-hide');
            $('.pwd-login-only').removeClass('display-hide');
            $('.auth-row,.auth-btn').removeClass('display-hide');
        }
        organizeAuthMethods();
    }
    $('.auth-toggle-item').click(function(){
        var type = $(this).data('type');
        $('#type').val(type);
        switchLoginType(type);
    });
    // 初始状态
    switchLoginType($('#type').val());

    // 验证码点击刷新
    $('#checkcode').on('click', function(){ $(this).attr('src','../include/lib/checkcode.php?' + Date.now()); });

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

    // 密码框删字符自动全删（仅对用户「主动删除」生效）
    //
    // 历史 bug：旧版本仅用 value.length 缩短作为判定条件，会在以下场景误清密码：
    //   1) 浏览器自动填充密码后，用户聚焦输入框 → 浏览器默认全选已填内容；
    //   2) 用户输入第一个字符 → 选中内容被覆盖式替换，length 由 N 突变为 1；
    //   3) 旧逻辑命中 `1 < N && 1 > 0`，把刚刚输入的那个字符也一并清空。
    // 结果：用户感觉"输入第一个密码字符没生效"，实际是被脚本误清。
    //
    // 修复：使用 InputEvent.inputType 精确判定操作类型，只在「真删除」时清空：
    //   - deleteContentBackward (Backspace) / deleteContentForward (Delete) / deleteByCut (剪切) → 视为删除；
    //   - insertText / insertReplacementText / insertFromPaste / insertCompositionText 等 → 视为插入或替换，不再触发清空。
    var pwPrev = 0;
    $('input[name="password"]').on('focus',function(){ pwPrev = this.value.length; });
    $('input[name="password"]').on('input',function(e){
        var orig = e.originalEvent || {};
        var inputType = orig.inputType || '';
        // inputType 在 Chrome 60+/Firefox 67+/Safari 10.1+ 全支持；
        // 极少数老浏览器没有 inputType（空字符串），保守起见此时不触发清空，避免误伤。
        var isUserDeletion = inputType.indexOf('delete') === 0;
        if (isUserDeletion && this.value.length < pwPrev && this.value.length > 0) {
            this.value = '';
            $(this).siblings('.field-clear').removeClass('show');
        }
        pwPrev = this.value.length;
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
    // 页面加载恢复倒计时
    resumeCountdown($('#loginSmsSend'), 'cd_login_sms');
    resumeCountdown($('#loginEmailSend'), 'cd_login_email');

    // 手机登录：发送短信验证码
    $('#loginSmsSend').on('click', function(){
        var phone = $.trim($('input[name="tel"]').val());
        if(!phone || !/^1[3-9]\d{9}$/.test(phone)){
            return layer.msg('请输入正确的手机号', {icon:0});
        }
        var $btn = $(this);
        // 弹出图形验证码
        layer.open({
            type:1, title:'安全验证', area:['340px','auto'], shadeClose:true,
            content:'<div style="padding:20px 24px 10px;overflow:hidden">' +
                '<div style="color:#64748b;font-size:13px;margin-bottom:10px">请输入图形验证码后发送短信</div>' +
                '<div style="display:flex;gap:10px;align-items:center;overflow:hidden">' +
                '<input id="_smsCapInput" type="text" maxlength="8" placeholder="验证码" style="flex:1;min-width:0;height:44px;padding:0 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:16px;outline:none;box-sizing:border-box">' +
                '<img id="_smsCapImg" src="' + _captchaUrl + Date.now() + '" style="height:40px;max-width:120px;flex-shrink:0;border-radius:6px;cursor:pointer" title="点击刷新">' +
                '</div></div>',
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
                        if(e.code==0){
                            layer.msg('验证码已发送',{icon:1});
                            startCountdown($btn,'cd_login_sms',60);
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
                layero.find('.layui-layer-btn0').css({background:'<?= $_auth_primary ?>',borderColor:'<?= $_auth_primary ?>'});
                layero.find('#_smsCapImg').on('click',function(){$(this).attr('src',_captchaUrl+Date.now());});
            }
        });
    });

    // 邮箱登录：发送邮箱验证码
    $('#loginEmailSend').on('click', function(){
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
                '<input id="_emailCapInput" type="text" maxlength="8" placeholder="验证码" style="flex:1;min-width:0;height:44px;padding:0 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:16px;outline:none;box-sizing:border-box">' +
                '<img id="_emailCapImg" src="' + _captchaUrl + Date.now() + '" style="height:40px;max-width:120px;flex-shrink:0;border-radius:6px;cursor:pointer" title="点击刷新">' +
                '</div></div>',
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
                        if(e.code==0){
                            layer.msg('验证码已发送至邮箱',{icon:1});
                            startCountdown($btn,'cd_login_email',60);
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
                layero.find('.layui-layer-btn0').css({background:'<?= $_auth_primary ?>',borderColor:'<?= $_auth_primary ?>'});
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
</body>
</html>
