<?php defined('DC_ROOT') || exit('access denied!'); ?>
<?php
global $userData;
$_myWechat = trim((string)($myWechat ?? ($userData['wechat'] ?? '')));
$_hasWechat = $_myWechat !== '';
?>

<style>
    .invite-page {
        display: flex;
        flex-direction: column;
        gap: 22px;
        padding: 8px 0 18px;
    }

    .invite-hero {
        padding: 24px 28px;
        border-radius: 10px;
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        box-shadow: 0 1px 18px #12345b0a;
    }

    .invite-hero-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
    }

    .invite-hero-left {
        flex: 1;
        min-width: 0;
    }

    .invite-hero-title {
        font-size: 22px;
        font-weight: 800;
        margin-bottom: 4px;
        color: var(--text-main, #1f2937);
    }

    .invite-hero-desc {
        font-size: 13px;
        color: var(--text-sub, #64748b);
    }

    .invite-hero-stats {
        display: flex;
        gap: 20px;
        flex-shrink: 0;
    }

    .invite-hero-stat-item {
        text-align: center;
        padding: 10px 16px;
        border-radius: 10px;
        background: #f1f5f9;
    }

    .invite-hero-stat-num {
        font-size: 22px;
        font-weight: 700;
        line-height: 1.2;
        color: var(--text-main, #1f2937);
    }

    .invite-hero-stat-label {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 2px;
    }

    .invite-section {
        padding: 24px 28px;
        border-radius: 10px;
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        box-shadow: 0 1px 18px #12345b0a;
    }

    .invite-section-title {
        font-size: 15px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .invite-section-title i {
        color: #6366f1;
        font-size: 16px;
    }

    .invite-field {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }

    .invite-field:last-of-type {
        margin-bottom: 0;
    }

    .invite-field-label {
        flex-shrink: 0;
        font-size: 13px;
        color: #86909c;
        width: 68px;
    }

    .invite-domain-select {
        height: 42px;
        padding: 0 10px;
        border: 1px solid var(--theme-primary);
        border-radius: 8px;
        background: #fff;
        font-size: 13px;
        color: #1a1a1a;
        cursor: pointer;
        outline: none;
        min-width: 110px;
        flex-shrink: 0;
    }
    .invite-domain-select:focus {
        border-color: var(--theme-primary);
    }

    .invite-field-value {
        flex: 1;
        min-width: 0;
        height: 42px;
        padding: 0 14px;
        border: 1px solid var(--theme-primary);
        border-radius: 8px;
        background: #f7f8fa;
        font-size: 14px;
        color: #1a1a1a;
        display: flex;
        align-items: center;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        user-select: all;
        font-family: 'SF Mono', 'Menlo', 'Consolas', monospace;
        letter-spacing: .5px;
    }

    .invite-field-btn {
        flex-shrink: 0;
        height: 42px;
        padding: 0 20px;
        border: none;
        border-radius: 8px;
        background: var(--theme-primary);
        color: #fff;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all .2s;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .invite-field-btn:hover {
        background: var(--tp-dark);
        box-shadow: 0 4px 12px rgba(var(--tp-rgb),.3);
    }

    .invite-field-btn:active {
        transform: scale(.97);
    }

    .invite-wechat-value.is-empty {
        color: #94a3b8;
        font-family: inherit;
        letter-spacing: 0;
        user-select: none;
    }

    .invite-field-btn.is-wechat {
        background: #07c160;
        border-color: #07c160;
    }

    .invite-field-btn.is-wechat:hover {
        background: #06ad56;
        box-shadow: 0 4px 12px rgba(7,193,96,.25);
    }

    .invite-wechat-tip {
        margin: -2px 0 12px 78px;
        color: #94a3b8;
        font-size: 12px;
        line-height: 1.7;
    }

    .invite-steps {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-top: 4px;
    }

    .invite-step {
        text-align: center;
        padding: 18px 12px;
        border-radius: 10px;
        background: #f8f9ff;
        border: 1px solid rgba(var(--tp-rgb),.08);
    }

    .invite-step-num {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--theme-primary), #8b5cf6);
        color: #fff;
        font-size: 16px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
    }

    .invite-step-title {
        font-size: 14px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 4px;
    }

    .invite-step-desc {
        font-size: 12px;
        color: #86909c;
        line-height: 1.5;
    }

    .invite-empty {
        text-align: center;
        padding: 40px 20px;
        color: #86909c;
    }

    .invite-empty i {
        font-size: 48px;
        color: #c9cdd4;
        margin-bottom: 12px;
        display: block;
    }

    .invite-empty p {
        font-size: 14px;
    }

    .invite-poster-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }
    .invite-poster-item {
        cursor: pointer;
        border-radius: 10px;
        overflow: hidden;
        border: 2px solid var(--theme-primary);
        transition: all .25s;
        position: relative;
    }
    .invite-poster-item:hover {
        border-color: var(--theme-primary);
        box-shadow: 0 6px 20px rgba(var(--tp-rgb),.2);
        transform: translateY(-3px);
    }
    .invite-poster-item img {
        width: 100%;
        display: block;
    }
    .invite-poster-mask {
        position: absolute;
        inset: 0;
        background: rgba(var(--tp-rgb),.55);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity .25s;
    }
    .invite-poster-item:hover .invite-poster-mask {
        opacity: 1;
    }
    .invite-poster-mask span {
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 999px;
        background: rgba(0,0,0,.2);
    }

    .poster-modal-wrap {
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
    }
    .poster-modal-body {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        padding: 20px;
        background: #f5f5f5;
        text-align: center;
        -webkit-overflow-scrolling: touch;
    }
    .poster-modal-img {
        max-width: 100%;
        border-radius: 8px;
        box-shadow: 0 8px 32px rgba(0,0,0,.12);
    }
    .poster-modal-footer {
        flex-shrink: 0;
        text-align: center;
        padding: 16px 20px 14px;
        background: #fff;
        border-top: 1px solid var(--theme-primary);
    }
    .poster-modal-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }
    .poster-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        height: 42px;
        padding: 0 28px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all .2s;
        text-decoration: none;
        border: none;
        line-height: 42px;
    }
    .poster-btn-primary {
        background: var(--theme-primary);
        color: #fff;
    }
    .poster-btn-primary:hover {
        background: var(--tp-dark);
        box-shadow: 0 4px 12px rgba(var(--tp-rgb),.3);
    }
    .poster-btn-default {
        background: #fff;
        color: #4b5563;
        border: 1px solid #d1d5db;
    }
    .poster-btn-default:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }
    .poster-modal-hint {
        margin: 10px 0 0;
        font-size: 12px;
        color: #86909c;
    }
    .poster-modal-body { position: relative; }
    .poster-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255,255,255,.85);
        border: 1px solid var(--theme-primary);
        color: #4b5563;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 10;
        transition: all .2s;
        box-shadow: 0 2px 8px rgba(0,0,0,.1);
    }
    .poster-nav:hover { background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,.15); }
    .poster-nav-prev { left: 8px; }
    .poster-nav-next { right: 8px; }

    @media (max-width: 768px) {
        .invite-page {
            gap: 14px;
            padding: 0;
        }

        .invite-hero {
            border-radius: 0;
            padding: 18px 16px;
            border: none;
            margin: 0 -16px;
            width: calc(100% + 32px);
        }

        .invite-hero-content {
            flex-direction: column;
            align-items: flex-start;
            gap: 14px;
        }

        .invite-hero-title {
            font-size: 18px;
        }

        .invite-hero-stats {
            width: 100%;
            gap: 12px;
        }

        .invite-hero-stat-item {
            flex: 1;
            padding: 8px 12px;
        }

        .invite-hero-stat-num {
            font-size: 20px;
        }

        .invite-section {
            border-radius: 10px;
            padding: 16px;
        }

        .invite-field {
            flex-wrap: wrap;
            gap: 8px;
        }

        .invite-field-label {
            width: 100%;
            font-size: 12px;
            margin-bottom: -4px;
        }

        .invite-field-value {
            flex: 1;
            min-width: 0;
            height: 40px;
            font-size: 13px;
        }

        .invite-field-btn {
            height: 40px;
            padding: 0 14px;
            font-size: 12px;
        }

        .invite-domain-select {
            width: 100%;
            height: 40px;
            font-size: 13px;
        }

        .invite-steps {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .invite-step {
            display: flex;
            align-items: center;
            gap: 14px;
            text-align: left;
            padding: 14px 16px;
        }

        .invite-step-num {
            margin: 0;
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            font-size: 14px;
        }

        .invite-step-body {
            flex: 1;
            min-width: 0;
        }

        .invite-poster-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
    }
</style>

<main class="invite-page">
    <section class="invite-hero">
        <div class="invite-hero-content">
            <div class="invite-hero-left">
                <div class="invite-hero-title">邀请好友</div>
                <div class="invite-hero-desc">分享您的专属邀请码或链接，好友注册后自动成为您的下级用户</div>
            </div>
            <div class="invite-hero-stats">
                <a href="./account.php?action=fans" class="invite-hero-stat-item" style="text-decoration:none;color:inherit;cursor:pointer;">
                    <div class="invite-hero-stat-num"><?= $inviteCount ?></div>
                    <div class="invite-hero-stat-label">已邀直推粉丝</div>
                </a>
                <a href="./account.php?action=fans" class="invite-hero-stat-item" style="text-decoration:none;color:inherit;cursor:pointer;">
                    <div class="invite-hero-stat-num"><?= $totalFansCount ?></div>
                    <div class="invite-hero-stat-label">所有粉丝总数</div>
                </a>
            </div>
        </div>
    </section>

    <?php if ($myInviteCode !== ''): ?>
    <section class="invite-section">
        <div class="invite-section-title">我的邀请信息</div>
        <div class="invite-field">
            <span class="invite-field-label">邀请码</span>
            <div class="invite-field-value" id="invCode"><?= htmlspecialchars($myInviteCode) ?></div>
            <button type="button" class="invite-field-btn" onclick="doCopy('invCode')"><i class="fa fa-copy"></i> 复制</button>
        </div>
        <?php if (count($inviteDomainOptions) > 1): ?>
        <div class="invite-field">
            <span class="invite-field-label">链接域名</span>
            <select class="invite-domain-select" id="invDomainSel" onchange="switchInvDomain()">
                <?php foreach ($inviteDomainOptions as $i => $opt): ?>
                <option value="<?= $i ?>"><?= htmlspecialchars($opt['label']) ?>（<?= htmlspecialchars(preg_replace('#^//|^https?://#', '', $opt['base'])) ?>）</option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div class="invite-field">
            <span class="invite-field-label">邀请链接</span>
            <div class="invite-field-value" id="invLink"><?= htmlspecialchars($myInviteLink) ?></div>
            <button type="button" class="invite-field-btn" onclick="doCopy('invLink')"><i class="fa fa-copy"></i> 复制</button>
            <button type="button" class="invite-field-btn" style="background:#6366f1;color:#fff;border-color:#6366f1;" onclick="genPoster(0)"><i class="fa fa-image"></i> 生成海报</button>
        </div>
        <div class="invite-field">
            <span class="invite-field-label">微信号</span>
            <div class="invite-field-value invite-wechat-value<?= $_hasWechat ? '' : ' is-empty' ?>" id="myWechatValue"><?= $_hasWechat ? htmlspecialchars($_myWechat) : '未设置' ?></div>
            <button type="button" class="invite-field-btn" id="copyWechatBtn" onclick="doCopy('myWechatValue')" style="<?= $_hasWechat ? '' : 'display:none;' ?>"><i class="fa fa-copy"></i> 复制</button>
            <button type="button" class="invite-field-btn is-wechat" id="inviteWechatEditBtn" onclick="openInviteWechatSetting()"><i class="fa fa-weixin"></i> <span><?= $_hasWechat ? '修改' : '设置' ?></span></button>
        </div>
        <div class="invite-wechat-tip">设置后会展示给您的上级成员和团队成员，方便沟通交流与粉丝转化。</div>
    </section>

    <section class="invite-section">
        <div class="invite-section-title">邀请海报</div>
        <p style="font-size:13px;color:#86909c;margin:-8px 0 14px;">选择一款海报模板，自动生成带有您专属邀请二维码的海报</p>
        <div class="invite-poster-grid">
            <?php for ($pi = 1; $pi <= 4; $pi++): ?>
            <div class="invite-poster-item" onclick="genPoster(<?= $pi - 1 ?>)">
                <img src="<?= DC_URL ?>admin/views/images/hb<?= $pi ?>.png" alt="海报<?= $pi ?>">
                <div class="invite-poster-mask"><span><i class="fa fa-magic"></i> 生成海报</span></div>
            </div>
            <?php endfor; ?>
        </div>
    </section>
    <?php else: ?>
    <section class="invite-section">
        <div class="invite-empty">
            <i class="fa fa-exclamation-circle"></i>
            <p>暂无邀请码，请联系管理员</p>
        </div>
    </section>
    <?php endif; ?>

    <section class="invite-section">
        <div class="invite-section-title"><i class="fa fa-info-circle"></i> 邀请流程</div>
        <div class="invite-steps">
            <div class="invite-step">
                <div class="invite-step-num">1</div>
                <div class="invite-step-body">
                    <div class="invite-step-title">分享邀请</div>
                    <div class="invite-step-desc">复制邀请码或链接发送给好友</div>
                </div>
            </div>
            <div class="invite-step">
                <div class="invite-step-num">2</div>
                <div class="invite-step-body">
                    <div class="invite-step-title">好友注册</div>
                    <div class="invite-step-desc">好友通过链接或填写邀请码注册</div>
                </div>
            </div>
            <div class="invite-step">
                <div class="invite-step-num">3</div>
                <div class="invite-step-body">
                    <div class="invite-step-title">绑定成功</div>
                    <div class="invite-step-desc">好友自动成为您的下级用户</div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
    $('#menu-invite').addClass('menu-current');

    var _invCode = <?= json_encode($myInviteCode) ?>;
    var _invOpts = <?= json_encode(array_values($inviteDomainOptions), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    var inviteWechatValue = <?= json_encode($_myWechat, JSON_UNESCAPED_UNICODE) ?>;
    var inviteWechatToken = '<?= LoginAuth::genToken() ?>';

    function switchInvDomain() {
        var sel = document.getElementById('invDomainSel');
        if (!sel) return;
        var idx = parseInt(sel.value, 10);
        var opt = _invOpts[idx];
        if (opt && _invCode) {
            document.getElementById('invLink').textContent = opt.base + opt.suffix + _invCode;
        }
    }

    function doCopy(id) {
        var text = document.getElementById(id).innerText;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function(){ layui.layer.msg('已复制到剪贴板'); });
        } else {
            var ta = document.createElement('textarea');
            ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
            document.body.appendChild(ta); ta.select(); document.execCommand('copy');
            document.body.removeChild(ta); layui.layer.msg('已复制到剪贴板');
        }
    }

    function openInviteWechatSetting() {
        var html = '<div style="padding:22px 24px 8px;">'
            + '<div style="display:flex;align-items:center;gap:8px;color:#1f2937;font-size:14px;font-weight:700;margin-bottom:10px;"><i class="fa fa-weixin" style="color:#07c160;"></i> 微信号</div>'
            + '<input id="inviteWechatInput" type="text" maxlength="40" value="' + $('<div>').text(inviteWechatValue || '').html() + '" placeholder="请输入微信号，可留空" style="width:100%;height:42px;padding:0 12px;border:1px solid #e5e7eb;border-radius:8px;outline:none;box-sizing:border-box;">'
            + '<div style="margin-top:10px;color:#94a3b8;font-size:12px;line-height:1.7;">支持 3-40 位字母、数字、下划线或中划线；留空可清空微信号。</div>'
            + '</div>';
        layui.layer.open({
            type: 1,
            title: '设置微信号',
            area: ['380px', 'auto'],
            shadeClose: true,
            content: html,
            btn: ['保存设置', '取消'],
            yes: function(idx, layero) {
                var val = $.trim(layero.find('#inviteWechatInput').val() || '').replace(/\s+/g, '');
                if (val !== '' && !/^[A-Za-z0-9_\-]{3,40}$/.test(val)) {
                    return layui.layer.msg('微信号仅支持3-40位字母、数字、下划线或中划线', {icon: 0});
                }
                $.ajax({
                    url: './account.php?action=fans_save_wechat',
                    type: 'POST',
                    dataType: 'json',
                    data: {wechat: val, token: inviteWechatToken},
                    success: function(res) {
                        if (res && res.code === 0) {
                            inviteWechatValue = val;
                            $('#myWechatValue').text(val || '未设置').toggleClass('is-empty', val === '');
                            $('#copyWechatBtn').toggle(!!val);
                            $('#inviteWechatEditBtn span').text(val ? '修改' : '设置');
                            layui.layer.close(idx);
                            layui.layer.msg(res.data || '微信号已保存', {icon: 1});
                        } else {
                            layui.layer.msg((res && res.msg) || '保存失败', {icon: 2});
                        }
                    },
                    error: function(xhr) {
                        var res = xhr.responseJSON || {};
                        layui.layer.msg(res.msg || '保存失败，请稍后重试', {icon: 2});
                    }
                });
            }
        });
    }
</script>
<?php if ($myInviteCode !== ''): ?>
<script src="<?= DC_URL ?>content/static/js/qrcode.min.js"></script>
<script>
(function(){
    var _posterConfigs = [
        { src: '<?= DC_URL ?>admin/views/images/hb1.png', cx: 0.50, cy: 0.545, size: 0.38 },
        { src: '<?= DC_URL ?>admin/views/images/hb2.png', cx: 0.50, cy: 0.565, size: 0.45 },
        { src: '<?= DC_URL ?>admin/views/images/hb3.png', cx: 0.50, cy: 0.525, size: 0.39 },
        { src: '<?= DC_URL ?>admin/views/images/hb4.png', cx: 0.50, cy: 0.540, size: 0.58 }
    ];

    window.genPoster = function(idx) {
        if (typeof QRCode === 'undefined') { layui.layer.msg('二维码组件未加载'); return; }
        if (!_invCode) { layui.layer.msg('暂无邀请码'); return; }
        var cfg = _posterConfigs[idx];
        var el = document.getElementById('invLink');
        var inviteLink = el ? el.innerText : '';
        if (!inviteLink) { layui.layer.msg('邀请链接为空'); return; }

        var loadIdx = layui.layer.load(2);
        var tmpDiv = document.createElement('div');
        tmpDiv.style.cssText = 'position:fixed;left:-9999px;top:-9999px;';
        document.body.appendChild(tmpDiv);

        new QRCode(tmpDiv, {
            text: inviteLink,
            width: 512,
            height: 512,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });

        setTimeout(function(){
            var qrCanvas = tmpDiv.querySelector('canvas');
            if (!qrCanvas) {
                document.body.removeChild(tmpDiv);
                layui.layer.close(loadIdx);
                layui.layer.msg('二维码生成失败');
                return;
            }

            var posterImg = new Image();
            posterImg.onload = function(){
                var canvas = document.createElement('canvas');
                var ctx = canvas.getContext('2d');
                canvas.width = posterImg.naturalWidth;
                canvas.height = posterImg.naturalHeight;
                ctx.drawImage(posterImg, 0, 0);

                var qrW = Math.round(canvas.width * cfg.size);
                var qrX = Math.round(canvas.width * cfg.cx - qrW / 2);
                var qrY = Math.round(canvas.height * cfg.cy - qrW / 2);
                var pad = Math.round(qrW * 0.06);
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(qrX - pad, qrY - pad, qrW + pad * 2, qrW + pad * 2);
                ctx.drawImage(qrCanvas, qrX, qrY, qrW, qrW);

                document.body.removeChild(tmpDiv);
                layui.layer.close(loadIdx);

                var dataUrl = canvas.toDataURL('image/png');
                var isMobile = window.innerWidth < 768;
                window._currentPosterIdx = idx;
                window._currentPosterDataUrl = dataUrl;
                var prevIdx = (idx - 1 + _posterConfigs.length) % _posterConfigs.length;
                var nextIdx = (idx + 1) % _posterConfigs.length;
                var html = '<div class="poster-modal-wrap">'
                    + '<div class="poster-modal-body">'
                    + '<div class="poster-nav poster-nav-prev" onclick="switchPoster(' + prevIdx + ')"><i class="fa fa-chevron-left"></i></div>'
                    + '<div class="poster-nav poster-nav-next" onclick="switchPoster(' + nextIdx + ')"><i class="fa fa-chevron-right"></i></div>'
                    + '<img id="_posterPreviewImg" src="' + dataUrl + '" class="poster-modal-img" />'
                    + '</div>'
                    + '<div class="poster-modal-footer">'
                    + '<div class="poster-modal-actions">'
                    + '<a id="_posterDL" download="invite_poster.png" class="poster-btn poster-btn-primary"><i class="fa fa-download"></i> \u4FDD\u5B58\u6D77\u62A5</a>'
                    + '<button type="button" class="poster-btn poster-btn-default" onclick="layui.layer.closeAll()"><i class="fa fa-times"></i> \u5173\u95ED</button>'
                    + '</div>'
                    + '<p class="poster-modal-hint">\u957F\u6309\u56FE\u7247\u6216\u70B9\u51FB\u201C\u4FDD\u5B58\u6D77\u62A5\u201D\u4E0B\u8F7D\u5230\u672C\u5730</p>'
                    + '</div>'
                    + '</div>';
                layui.layer.open({
                    type: 1,
                    title: '邀请海报预览',
                    area: isMobile ? ['96%', '90%'] : ['520px', '85%'],
                    content: html,
                    shadeClose: true,
                    scrollbar: false,
                    success: function(layero){
                        $(layero).find('#_posterDL').attr('href', dataUrl);
                        $(layero).find('.layui-layer-content').css({'overflow':'hidden'});
                    }
                });
            };
            posterImg.onerror = function(){
                document.body.removeChild(tmpDiv);
                layui.layer.close(loadIdx);
                layui.layer.msg('海报图片加载失败');
            };
            posterImg.src = cfg.src;
        }, 200);
    };
    window.switchPoster = function(newIdx) {
        layui.layer.closeAll();
        genPoster(newIdx);
    };
})();
</script>
<?php endif; ?>
<?php include __DIR__ . '/_pc_page_footer.php'; ?>
