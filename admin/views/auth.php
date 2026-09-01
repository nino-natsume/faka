<?php defined('DC_ROOT') || exit('access denied!'); ?>

<style>
#accordion > .active .menu-link{
    background: #EDF2F1!important;
}
#accordion > .active .menu-link, #accordion > .active .menu-link .fa{
    color: #4C7D71!important;
}

.auth-page-wrapper {
    min-height: calc(100vh - 120px);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: -apple-system, BlinkMacSystemFont, "PingFang SC", "Helvetica Neue", STHeiti, "Microsoft Yahei", Tahoma, Simsun, sans-serif;
}

.premium-card {
    background: linear-gradient(0deg, #fff, #f3f5f8);
    border: 2px solid #fff;
    width: 100%;
    max-width: 600px;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    position: relative;
    padding: 30px 40px;
    box-sizing: border-box;
}
.mac-dots { display:flex;gap:6px;margin-bottom:20px; }
.mac-dots i { width:12px;height:12px;border-radius:50%;display:inline-block; }
.mac-dots .dot-r { background:#ff5f57; }
.mac-dots .dot-y { background:#febc2e; }
.mac-dots .dot-g { background:#28c840; }

.form-header {
    margin-bottom: 35px;
    text-align: center;
}
.form-title {
    font-size: 26px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 12px;
}
.form-subtitle {
    color: #888;
    font-size: 15px;
    line-height: 1.5;
}

.status-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    font-size: 40px;
}
.status-icon.success {
    background: #e8f5e9;
    color: #4caf50;
}
.status-icon.warning {
    background: #fff3e0;
    color: #ff9800;
}

.auth-info {
    background: #f8faff;
    border: 1px solid #e6f0ff;
    border-radius: 12px;
    padding: 25px;
    margin-top: 25px;
}
.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    font-size: 14px;
    border-bottom: 1px dashed #e1e1e1;
    padding-bottom: 15px;
}
.info-row:last-child {
    margin-bottom: 0;
    border-bottom: none;
    padding-bottom: 0;
}
.info-label {
    color: #666;
}
.info-val {
    font-weight: 600;
    color: #333;
}
.info-val.highlight {
    color: #4caf50;
}

.btn-action {
    width: 100%;
    height: 50px;
    background: #165DFF;
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    text-decoration: none;
    margin-top: 25px;
}
.btn-action:hover {
    background: #0d4fd6;
    color: #fff;
}
.btn-action.secondary {
    background: #EDF2F1;
    color: #4C7D71;
}
.btn-action.secondary:hover {
    background: #dce5e3;
}
.btn-action.ghost {
    background: #fff;
    color: #4C7D71;
    border: 1px solid #d9e4df;
}
.btn-action.ghost:hover {
    background: #f7faf8;
    color: #35564e;
}

.auth-submit-form {
    margin-top: 20px;
}
.auth-code-row {
    display: block;
    margin-top: 18px;
}
.auth-code-input {
    width: 100%;
    height: 50px;
    border: 1px solid #d9e4f2;
    border-radius: 10px;
    padding: 0 16px;
    font-size: 15px;
    color: #333;
    background: #fff;
    box-sizing: border-box;
}
.auth-code-input:focus {
    outline: none;
    border-color: #4C7D71;
    box-shadow: 0 0 0 3px rgba(22, 93, 255, 0.12);
}
.auth-action-row {
    display: flex;
    gap: 12px;
    margin-top: 30px;
}
.auth-code-btn {
    height: 50px;
    border: none;
    border-radius: 10px;
    background: #4C7D71;
    color: #fff;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    flex: 1;
}
.auth-code-btn:hover {
    background: #35564e;
}
.auth-code-btn.is-loading {
    opacity: 0.75;
    cursor: not-allowed;
}
.auth-refresh-btn {
    height: 50px;
    border: none;
    border-radius: 10px;
    background: #EDF2F1;
    color: #4C7D71;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    flex: 1;
}
.auth-refresh-btn:hover {
    background: #dce5e3;
}
.auth-alert {
    border-radius: 10px;
    padding: 13px 15px;
    font-size: 14px;
    line-height: 1.7;
    margin-top: 18px;
}
.auth-alert.success {
    background: #f6ffed;
    border: 1px solid #b7eb8f;
    color: #389e0d;
}
.auth-alert.error {
    background: #fff2f0;
    border: 1px solid #ffccc7;
    color: #cf1322;
}

.tip-text {
    margin-top: 20px;
    font-size: 13px;
    color: #999;
    text-align: center;
    line-height: 1.8;
}
</style>

<div class="auth-page-wrapper">
    <div class="premium-card">
        <div class="mac-dots"><i class="dot-r"></i><i class="dot-y"></i><i class="dot-g"></i></div>
        <?php if ($authResult['authorized']): ?>
            <!-- 已授权 -->
            <div class="form-header">
                <div class="status-icon success">
                    <i class="ri-checkbox-circle-line"></i>
                </div>
                <div class="form-title">授权验证通过</div>
                <div class="form-subtitle">当前域名已获得正版授权，感谢您的支持</div>
            </div>

            <div class="auth-info">
                <div class="info-row">
                    <span class="info-label">授权域名</span>
                    <span class="info-val"><?= htmlspecialchars(getTopHost()) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">授权类型</span>
                    <span class="info-val highlight"><?= htmlspecialchars($authResult['type_name']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">到期时间</span>
                    <span class="info-val"><?= htmlspecialchars($authResult['expire_time']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">系统版本</span>
                    <span class="info-val">v<?= Option::DC_VERSION ?></span>
                </div>
                <?php
                $maskedAccountUsername = $authResult['account_username'] ?? '';
                if (mb_strlen($maskedAccountUsername) > 4) {
                    $maskedAccountUsername = mb_substr($maskedAccountUsername, 0, 2) . '****' . mb_substr($maskedAccountUsername, -2);
                } elseif (mb_strlen($maskedAccountUsername) > 2) {
                    $maskedAccountUsername = mb_substr($maskedAccountUsername, 0, 1) . '****' . mb_substr($maskedAccountUsername, -1);
                }
                ?>
                <div class="info-row" style="<?php if (empty($authResult['account_bound'])): ?>border-bottom:none;margin-bottom:0;padding-bottom:0;<?php endif; ?>">
                    <span class="info-label">服务端账号</span>
                    <span class="info-val"><?php if (!empty($authResult['account_bound'])): ?><?= htmlspecialchars($maskedAccountUsername ?: '已绑定') ?><?php else: ?><span style="color: #ff9800;">未绑定</span><?php endif; ?></span>
                </div>
                <?php if (empty($authResult['account_bound'])): ?>
                <div style="padding-top:15px;">
                    <div style="font-size:13px;color:#8c8c8c;line-height:1.6;margin-bottom:14px;">绑定服务端账号后可使用应用商店、管理授权等功能</div>
                    <div style="display:flex;gap:10px;">
                        <?php if (!empty($authResult['claim_register_url'])): ?>
                        <a href="<?= htmlspecialchars($authResult['claim_register_url']) ?>" target="_blank" style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:10px 0;background:#4C7D71;color:#fff;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;transition:all .2s;">
                            <i class="ri-user-add-line"></i> 注册并绑定
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($authResult['claim_login_url'])): ?>
                        <a href="<?= htmlspecialchars($authResult['claim_login_url']) ?>" target="_blank" style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:10px 0;background:#fff;color:#4C7D71;border:1px solid #d9e4df;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;transition:all .2s;">
                            <i class="ri-login-circle-line"></i> 登录老用户绑定
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($authResult['account_bound'])): ?>
            <a href="<?= htmlspecialchars(Register::getAuthCenterUrl()) ?>" target="_blank" class="btn-action secondary">
                <i class="ri-settings-3-line" style="margin-right: 8px;"></i> 前往授权中心管理
            </a>

            <div class="tip-text">
                如需更换域名或管理授权，请前往授权中心操作
            </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- 未授权 -->
            <div class="form-header">
                <div class="status-icon warning">
                    <i class="ri-error-warning-line"></i>
                </div>
                <div class="form-title">域名未授权</div>
                <div class="form-subtitle"><?= htmlspecialchars($authResult['msg']) ?></div>
            </div>

            <div class="auth-info" style="background: #fffbf0; border-color: #ffe7ba;">
                <div class="info-row">
                    <span class="info-label">当前域名</span>
                    <span class="info-val"><?= htmlspecialchars(getTopHost()) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">授权状态</span>
                    <span class="info-val" style="color: #ff9800;">未授权</span>
                </div>
            </div>

            <?php if (!empty($activateMsg)): ?>
            <div class="auth-alert <?= $activateOk ? 'success' : 'error' ?>">
                <?= htmlspecialchars($activateMsg) ?>
            </div>
            <?php endif; ?>

            <form method="post" class="auth-submit-form" id="authSubmitForm">
                <div class="auth-code-row">
                    <input type="text" name="license_key" class="auth-code-input" id="authLicenseKeyInput" placeholder="请输入授权码" autocomplete="off" required oninvalid="this.setCustomValidity('请输入授权码后再提交')" oninput="this.setCustomValidity('')">
                </div>
                <div class="auth-action-row">
                    <button type="submit" class="auth-code-btn" id="authSubmitBtn">立即授权</button>
                    <button type="button" class="auth-refresh-btn" onclick="location.reload()">
                        <i class="ri-refresh-line" style="margin-right: 8px;"></i> 刷新授权状态
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    $("#menu-auth").addClass('active');
    layui.use('layer', function(){
        var layer = layui.layer;

        <?php if (!empty($activateMsg)): ?>
        layer.msg('<?= addslashes($activateMsg) ?>', {
            icon: <?= $activateOk ? '1' : '2' ?>,
            time: 3000
        });
        <?php endif; ?>

        var form = document.getElementById('authSubmitForm');
        var input = document.getElementById('authLicenseKeyInput');
        var submitBtn = document.getElementById('authSubmitBtn');
        if (!form || !input || !submitBtn) {
            return;
        }
        var isSubmitting = false;
        form.addEventListener('submit', function(e){
            e.preventDefault();
            if (isSubmitting) return;
            var licenseKey = input.value.trim();
            if (!licenseKey) {
                layer.msg('请输入授权码后再提交', {icon: 0, time: 1800});
                return;
            }
            layer.confirm('确认使用当前授权码绑定本站域名吗？<br><span style="color:#999;font-size:12px;">授权过程中请耐心等待，不要关闭页面。</span>', {
                icon: 3,
                title: '确认授权',
                btn: ['确认授权', '取消']
            }, function(confirmIndex){
                layer.close(confirmIndex);
                isSubmitting = true;
                submitBtn.disabled = true;
                submitBtn.classList.add('is-loading');
                submitBtn.innerHTML = '正在授权...';
                var loadIndex = layer.load(2, {shade: [0.15, '#000']});

                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'auth.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.timeout = 30000;
                xhr.onload = function(){
                    layer.close(loadIndex);
                    isSubmitting = false;
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('is-loading');
                    submitBtn.innerHTML = '立即授权';
                    try {
                        var res = JSON.parse(xhr.responseText);
                    } catch(ex) {
                        layer.msg('授权服务器响应异常，请稍后重试', {icon: 2, time: 3000});
                        return;
                    }
                    if (res.code === 0) {
                        layer.msg(res.msg || '授权激活成功', {icon: 1, time: 2000}, function(){
                            location.reload();
                        });
                    } else {
                        layer.msg(res.msg || '授权失败', {icon: 2, time: 3000});
                    }
                };
                xhr.onerror = function(){
                    layer.close(loadIndex);
                    isSubmitting = false;
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('is-loading');
                    submitBtn.innerHTML = '立即授权';
                    layer.msg('网络请求失败，请检查网络后重试', {icon: 2, time: 3000});
                };
                xhr.ontimeout = function(){
                    layer.close(loadIndex);
                    isSubmitting = false;
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('is-loading');
                    submitBtn.innerHTML = '立即授权';
                    layer.msg('授权服务器响应超时，请稍后重试', {icon: 2, time: 3000});
                };
                xhr.send('license_key=' + encodeURIComponent(licenseKey));
            });
        });
    });
</script>
