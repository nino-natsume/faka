<?php defined('DC_ROOT') || exit('access denied!'); ?>
<?php $blogname = Option::get('blogname'); ?>
<?php $__login_icon = Option::get('personal_center_icon'); ?>
<?php
$__signin_bgs = [
    'https://dscache.tencent-cloud.cn/upload/uploader/all-base-4d4ab1d67ce695c13f6172a90395a0e784a571c2.png',
    'https://dscache.tencent-cloud.cn/upload/uploader/enterprise-base-ab12aa5ac597f609c91a4775cb3756cada51a7d9.png',
];
$__signin_bg = $__signin_bgs[array_rand($__signin_bgs)];
?>
<style>
    :root{
        --mac-bg1:#b4d2ff;
        --mac-bg2:#f3c6ff;
        --mac-bg3:#ffd6a5;
        --mac-bg4:#b1f0d8;
        --mac-text:#1d1d1f;
        --mac-sub:#6e6e73;
        --mac-accent:#0a84ff;
    }
    *{margin:0;box-sizing:border-box;}
    html,body{height:100%;}
    body{
        font-family:-apple-system,BlinkMacSystemFont,"SF Pro Display","SF Pro Text","PingFang SC","Helvetica Neue",Helvetica,Arial,sans-serif;
        color:var(--mac-text);
        min-height:100vh;
        display:flex;
        align-items:center;
        justify-content:center;
        overflow:hidden;
        background:#f5f7fa;
        position:relative;
    }
    /* 背景同后台默认 */
    .mac-wallpaper{
        position:fixed;inset:0;z-index:-2;
        background:#f5f7fa;
    }
    .mac-noise{display:none;}

    /* 登录窗口 */
    .mac-window{
        width:420px;max-width:92vw;
        border-radius:10px;overflow:hidden;
        background-position:center;background-size:cover;background-repeat:no-repeat;
        backdrop-filter:saturate(180%) blur(30px);
        -webkit-backdrop-filter:saturate(180%) blur(30px);
        border: 2px solid #fff;
        box-shadow:
            0 30px 60px rgba(0,0,0,.0),
            0 10px 20px rgba(0,0,0,.12),
            inset 0 1px 0 rgba(255,255,255,.6);
        animation:macPop .5s cubic-bezier(.2,.8,.2,1);
    }
    @keyframes macPop{from{opacity:0;transform:translateY(12px) scale(.98);}to{opacity:1;transform:none;}}

    .mac-titlebar{
        height:36px;display:flex;align-items:center;
        padding:0 14px;gap:8px;
        background:linear-gradient(to bottom,rgba(255,255,255,.55),rgba(255,255,255,.25));
        border-bottom:1px solid rgba(0,0,0,.06);
        user-select:none;
    }
    .mac-traffic{display:flex;align-items:center;gap:8px;}
    .mac-dot{
        width:12px;height:12px;border-radius:50%;
        box-shadow:inset 0 0 0 .5px rgba(0,0,0,.15);
        transition:transform .2s ease;
    }
    .mac-dot.red{background:#ff5f57;}
    .mac-dot.yellow{background:#febc2e;}
    .mac-dot.green{background:#28c840;}
    .mac-traffic:hover .mac-dot{transform:scale(1.05);}
    .mac-title{
        flex:1;text-align:center;font-size:13px;font-weight:600;color:#3c3c43;
        letter-spacing:.2px;
    }

    .mac-body{padding:28px 32px 22px;}

    .mac-avatar{
        width:88px;height:88px;margin:6px auto 14px;border-radius:50%;
        background:linear-gradient(135deg, #80CBC4, #4C7D71);
        display:flex;align-items:center;justify-content:center;
        color:#fff;font-size:40px;
        box-shadow:0 8px 18px rgba(80,80,150,.25),inset 0 0 0 2px rgba(255,255,255,.7);
    }
    .mac-user-name{
        text-align:center;font-size:17px;font-weight:600;color:#1d1d1f;margin-bottom:2px;
    }
    .mac-user-tip{
        text-align:center;font-size:12px;color:var(--mac-sub);margin-bottom:22px;
    }

    .mac-form .mac-field{
        position:relative;margin-bottom:10px;
    }
    .mac-form .mac-field .mac-icon{
        position:absolute;left:14px;top:50%;transform:translateY(-50%);
        color:#8e8e93;font-size:15px;pointer-events:none;
    }
    .mac-form input.mac-input{
        width:100%;height:42px;
        padding:0 44px 0 40px;
        border:1px solid rgba(0,0,0,.08);
        background:rgba(255,255,255,.75);
        border-radius:10px;
        font-size:13px;color:#1d1d1f;
        outline:none;transition:all .2s ease;
        -webkit-appearance:none;
    }
    .mac-form input.mac-input::placeholder{color:#a1a1a6;}
    .mac-form input.mac-input:focus{
        border-color:rgba(10,132,255,.7);
        background:#fff;
        box-shadow:0 0 0 4px rgba(10,132,255,.18);
    }
    .mac-field .mac-clear{
        position:absolute;right:12px;top:50%;transform:translateY(-50%);
        width:18px;height:18px;border-radius:20%;border:none;
        background:rgba(0,0,0,.12);color:#fff;font-size:11px;line-height:18px;text-align:center;
        cursor:pointer;display:none;padding:0;transition:background .15s;
    }
    .mac-field .mac-clear:hover{background:rgba(0,0,0,.25);}
    .mac-field .mac-clear.is-show{display:block;}
    .mac-submit-wrap{display:flex;justify-content:center;margin:6px 0 4px;}
    .mac-go{
        width:52px;height:52px;    
        border: 2px solid #fff;
        border-radius:50%;
        background-image: linear-gradient(0deg, #f5f5f5 0%, #f3f5f8 100%);
        backdrop-filter:saturate(180%) blur(16px);
        -webkit-backdrop-filter:saturate(180%) blur(16px);
        color:#3c3c43;font-size:22px;
        display:flex;align-items:center;justify-content:center;
        cursor:pointer;
        box-shadow:0 6px 16px rgba(0,0,0,.12),inset 0 1px 0 rgba(255,255,255,.6);
    }
    .mac-go:hover{
        background:rgba(255,255,255,.55);
        transform:translateY(-2px) scale(1.04);
        box-shadow:0 10px 22px rgba(0,0,0,.16),inset 0 1px 0 rgba(255,255,255,.7);
    }
    .mac-go:active{transform:translateY(0) scale(.98);filter:brightness(.96);}
    .mac-go i{line-height:1;}

    .mac-captcha{
        display:flex;gap:8px;margin-bottom:10px;align-items:center;
    }
    .mac-captcha .mac-field{flex:1;margin:0;}
    .mac-captcha img{
        height:42px;border-radius:10px;border:1px solid rgba(0,0,0,.08);
        cursor:pointer;transition:all .2s ease;background:#fff;
    }
    .mac-captcha img:hover{transform:scale(1.02);}

    .mac-row{
        display:flex;align-items:center;justify-content:space-between;
        margin:10px 2px 18px;font-size:13px;color:var(--mac-sub);
    }
    .mac-row .mac-forgot{color:var(--mac-accent);text-decoration:none;font-size:12px;}
    .mac-row .mac-forgot:hover{text-decoration:underline;}
    .mac-check{display:flex;align-items:center;gap:6px;cursor:pointer;user-select:none;}
    .mac-check input{accent-color:var(--mac-accent);width:14px;height:14px;}

    .mac-ext{text-align:center;margin-top:14px;font-size:12px;color:var(--mac-sub);}
    .mac-ext a{color:var(--mac-accent);text-decoration:none;}
    .mac-ext a:hover{text-decoration:underline;}

    /* 底部 Dock */
    .mac-dock{
        position:fixed;bottom:14px;left:50%;transform:translateX(-50%);
        display:flex;align-items:center;gap:10px;
        padding:8px 14px;border-radius:18px;
        background:rgba(255,255,255,.45);
        backdrop-filter:saturate(180%) blur(22px);
        -webkit-backdrop-filter:saturate(180%) blur(22px);
        border:1px solid rgba(255,255,255,.5);
        box-shadow:0 12px 30px rgba(0,0,0,.18);
        z-index:10;
    }
    .mac-dock a{
        width:44px;height:44px;border-radius:12px;
        display:flex;align-items:center;justify-content:center;
        color:#1d1d1f;font-size:20px;text-decoration:none;
        background:linear-gradient(135deg,#ffffff 0%,#e7ecf5 100%);
        box-shadow:0 4px 10px rgba(0,0,0,.12);
        transition:transform .2s ease;
        position:relative;
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

    /* 线路选择器 */
    .dock-line-wrap{position:relative;}
    .dock-line-btn{
        width:auto !important;padding:0 12px !important;
        gap:6px;font-size:12px !important;
        background:linear-gradient(135deg,#ffffff 0%,#e7ecf5 100%) !important;
        max-width:220px;overflow:hidden;white-space:nowrap;
    }
    .dock-line-btn .line-name-text{
        display:inline-block;max-width:100px;overflow:hidden;text-overflow:ellipsis;vertical-align:middle;white-space:nowrap;
    }
    .dock-line-btn #lineArrow{
        transition:transform .2s ease;flex-shrink:0;
    }
    .dock-line-panel.show ~ #lineToggleBtn #lineArrow,
    .dock-line-wrap.open .dock-line-btn #lineArrow{
        transform:rotate(180deg);
    }
    .dock-line-btn .line-dot-s{
        display:inline-block;width:6px;height:6px;border-radius:50%;vertical-align:middle;flex-shrink:0;
    }
    .dock-line-btn .line-dot-s.green{background:#22c55e;box-shadow:0 0 3px #22c55e88;}
    .dock-line-btn .line-dot-s.yellow{background:#f59e0b;box-shadow:0 0 3px #f59e0b88;}
    .dock-line-btn .line-dot-s.red{background:#ef4444;box-shadow:0 0 3px #ef444488;}
    .dock-line-btn .line-dot-s.gray{background:#9ca3af;}
    .dock-line-panel{
        display:none;position:absolute;bottom:58px;left:50%;transform:translateX(-50%);
        min-width:260px;padding:12px;border-radius:14px;
        background:rgba(255,255,255,.85);
        backdrop-filter:saturate(180%) blur(22px);
        -webkit-backdrop-filter:saturate(180%) blur(22px);
        border:1px solid rgba(255,255,255,.6);
        box-shadow:0 12px 40px rgba(0,0,0,.18);
        z-index:20;
    }
    .dock-line-panel.show{display:block;}
    .dock-line-panel .lp-title{
        font-size:13px;font-weight:600;color:#1d1d1f;margin-bottom:8px;padding:0 4px;
        display:flex;align-items:center;justify-content:space-between;
    }
    .dock-line-panel .lp-title .lp-refresh{
        font-size:11px;color:var(--mac-accent);cursor:pointer;font-weight:400;
    }
    .dock-line-panel .lp-title .lp-refresh:hover{text-decoration:underline;}
    .dock-line-panel .lp-item{
        display:flex;align-items:center;gap:8px;
        padding:8px 10px;border-radius:8px;cursor:pointer;
        transition:background .15s;
    }
    .dock-line-panel .lp-item:hover{background:rgba(0,0,0,.04);}
    .dock-line-panel .lp-item.active{background:rgba(10,132,255,.08);}
    .dock-line-panel .lp-item .lp-dot{
        width:8px;height:8px;border-radius:50%;flex-shrink:0;
    }
    .dock-line-panel .lp-item .lp-dot.green{background:#22c55e;box-shadow:0 0 4px #22c55e88;}
    .dock-line-panel .lp-item .lp-dot.yellow{background:#f59e0b;box-shadow:0 0 4px #f59e0b88;}
    .dock-line-panel .lp-item .lp-dot.red{background:#ef4444;box-shadow:0 0 4px #ef444488;}
    .dock-line-panel .lp-item .lp-dot.gray{background:#9ca3af;}
    .dock-line-panel .lp-item .lp-name{
        flex:1;font-size:13px;color:#1d1d1f;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0;
    }
    .dock-line-panel .lp-item .lp-ping{font-size:12px;font-weight:500;flex-shrink:0;white-space:nowrap;}
    .dock-line-panel .lp-item .lp-ping.green{color:#16a34a;}
    .dock-line-panel .lp-item .lp-ping.yellow{color:#d97706;}
    .dock-line-panel .lp-item .lp-ping.red{color:#dc2626;}
    .dock-line-panel .lp-item .lp-ping.gray{color:#9ca3af;}
    .dock-line-panel .lp-item .lp-check{
        font-size:14px;color:var(--mac-accent);opacity:0;
    }
    .dock-line-panel .lp-item.active .lp-check{opacity:1;}
    .dock-line-panel .lp-hint{
        font-size:11px;color:var(--mac-sub);padding:6px 4px 0;border-top:1px solid rgba(0,0,0,.06);margin-top:6px;
    }

    @media(max-width:480px){
        body{align-items:flex-start;padding-top:12vh;}
        .mac-window{width:94vw;}
        .mac-body{padding:22px 22px 18px;}
        .mac-avatar{width:72px;height:72px;font-size:34px;margin-top:0;}
        .mac-dock{max-width:96vw;padding:8px 10px;gap:6px;}
        .mac-dock .copyright,
        .mac-dock .sep{display:none;}
        .dock-line-btn{max-width:160px !important;font-size:11px !important;padding:0 8px !important;}
        .dock-line-btn .line-name-text{max-width:60px;}
        .dock-line-panel{min-width:200px;max-width:88vw;left:auto;right:0;transform:none;}
        .dock-line-panel .lp-name{font-size:12px;}
    }
</style>

<div class="mac-wallpaper"></div>
<div class="mac-noise"></div>

<!-- 登录窗口 -->
<div class="mac-window" role="dialog" aria-label="登录" style="background-image:url('<?= htmlspecialchars($__signin_bg, ENT_QUOTES) ?>');">
    <div class="mac-titlebar">
        <div class="mac-traffic">
            <span class="mac-dot red"></span>
            <span class="mac-dot yellow"></span>
            <span class="mac-dot green"></span>
        </div>
        <div class="mac-title">登录</div>
        <div style="width:52px;"></div>
    </div>

    <div class="mac-body">
        <div class="mac-avatar"><?php if (!empty($__login_icon)): ?><img src="<?= htmlspecialchars(getFileUrl($__login_icon)) ?>" alt="Logo" style="width:100%;height:100%;object-fit:cover;border-radius:50%;"><?php else: ?><i class="ri-user-smile-line"></i><?php endif; ?></div>
        <div class="mac-user-name">管理员</div>
        <?php
        $__greetings = [
            '今天也是充满干劲的一天 ✨',
            '你来啦，今天会是很棒的一天！',
            '嘿，准备好大展身手了吗 🚀',
            '每一天都是新的开始 🌟',
            '保持微笑，好运自然来 😊',
            '又是元气满满的一天！',
            '你的努力终将不负期望 💪',
            '今日事，今日毕，加油鸭 🦆',
            '愿你所有的坚持都有回报 🎯',
            '做最好的自己，每天进步一点点',
            '生活明朗，万物可爱 🌈',
            '星光不负赶路人 🌙',
        ];
        ?>
        <div class="mac-user-tip"><?= $__greetings[array_rand($__greetings)] ?></div>

        <form method="post" class="layui-form mac-form" action="./account.php?action=dosignin">
            <div class="mac-field">
                <i class="mac-icon ri-user-line"></i>
                <input type="text" class="mac-input" id="user" name="user" placeholder="请输入账号/邮箱" autocomplete="username" autofocus>
                <button type="button" class="mac-clear" tabindex="-1">&times;</button>
            </div>

            <div class="mac-field">
                <i class="mac-icon ri-lock-2-line"></i>
                <input type="password" class="mac-input" id="pw" name="pw" placeholder="请输入密码" autocomplete="current-password">
                <button type="button" class="mac-clear" tabindex="-1">&times;</button>
            </div>

            <?php if ($login_code): ?>
            <div class="mac-captcha">
                <div class="mac-field">
                    <i class="mac-icon ri-shield-check-line"></i>
                    <input type="text" name="login_code" class="mac-input" id="login_code" placeholder="验证码" required>
                </div>
                <img src="../include/lib/checkcode.php" id="checkcode" alt="验证码">
            </div>
            <?php endif ?>

            <?php doAction('admin_login_remember') ?>
            <?php doAction('login_form') ?>

            <div class="mac-row">
                <label class="mac-check">
                    <input type="checkbox" id="persist" name="persist" value="1">
                    <span>记住我</span>
                </label>
                <a href="./account.php?action=reset" class="mac-forgot">忘记密码？</a>
            </div>

            <div class="mac-submit-wrap">
                <button type="button" lay-submit lay-filter="demo-submit" class="mac-go" title="登录">
                    <i class="ri-arrow-right-line"></i>
                </button>
            </div>

            <div class="mac-ext">
                <?php doAction('admin_login_ext') ?>
            </div>
        </form>
    </div>
</div>

<!-- 底部 Dock -->
<div class="mac-dock">
    <a href="../" title="返回首页">
        <i class="ri-home-4-line"></i>
        <span class="tip">返回首页</span>
    </a>
    <span class="sep"></span>
    <!-- 线路选择器 -->
    <div class="dock-line-wrap">
        <a href="javascript:;" class="dock-line-btn" id="lineToggleBtn" title="切换授权线路">
            <span class="line-dot-s gray" id="currentLineDot"></span>
            <span class="line-name-text" id="currentLineName"><?= DC_LINE[CURRENT_LINE]['name'] ?></span>
            <span id="currentLinePing" style="opacity:.7;">检测中</span>
            <i class="ri-arrow-up-s-line" id="lineArrow" style="font-size:14px;opacity:.5;margin-left:-2px;"></i>
        </a>
        <div class="dock-line-panel" id="linePanel">
            <div class="lp-title">
                <span>选择线路</span>
                <span class="lp-refresh" id="lpRefresh"><i class="ri-refresh-line"></i> 重新检测</span>
            </div>
            <?php foreach (DC_LINE as $key => $line): ?>
            <div class="lp-item<?= $key === CURRENT_LINE ? ' active' : '' ?>" data-line="<?= $key ?>">
                <span class="lp-dot gray" data-dot="<?= $key ?>"></span>
                <span class="lp-name"><?= $line['name'] ?></span>
                <span class="lp-ping gray" data-ping="<?= $key ?>">检测中...</span>
                <i class="ri-check-line lp-check"></i>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <span class="sep"></span>
    <span class="copyright"> <?= date('Y') ?> <?= htmlspecialchars($blogname) ?></span>
</div>

<script>
    layui.use(function(){
        var $ = layui.$;
        var form = layui.form;
        var layer = layui.layer;
        form.on('submit(demo-submit)', function(data){
            var field = data.field;
            field.resp = 'json';
            $.ajax({
                type: "POST",
                url: "./account.php?action=dosignin",
                data: field,
                dataType: "json",
                success: function (e) {
                    if(e.code == 0){
                        layer.msg('登录成功，正在跳转');
                        location.href="/admin";
                    }else{
                        <?php doAction('admin_login_error') ?>
                        layer.msg(e.msg || '登录失败');
                        var _ck = document.getElementById('checkcode');
                        if(_ck) _ck.src = '../include/lib/checkcode.php?' + Date.now();
                    }
                },
                error: function (xhr) {
                    var msg = '请求失败';
                    try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(err){}
                    layer.msg(msg);
                    var _ck = document.getElementById('checkcode');
                    if(_ck) _ck.src = '../include/lib/checkcode.php?' + Date.now();
                }
            });
            return false;
        });
    });
</script>

<script>
    $(function () {
        if (typeof hideActived === 'function') { setTimeout(hideActived, 6000); }
        $('#checkcode').on('click', function () {
            var timestamp = new Date().getTime();
            $(this).attr("src", "../include/lib/checkcode.php?" + timestamp);
        });
        // 回车提交
        $('.mac-form .mac-input').on('keydown', function(e){
            if(e.key === 'Enter'){
                e.preventDefault();
                $('.mac-go').trigger('click');
            }
        });

        // ========== 线路选择器逻辑 ==========
        var CURRENT_IDX = <?= CURRENT_LINE ?>;
        var AUTO_SWITCH_KEY = 'dc_login_auto_switched';

        function pingLevel(ms) {
            if (ms < 0) return 'red';
            if (ms <= 500) return 'green';
            if (ms <= 1500) return 'yellow';
            return 'red';
        }

        function showPingResult(data) {
            var curItem = data[CURRENT_IDX];
            if (curItem) {
                var lv = curItem.status === 'ok' ? pingLevel(curItem.avg_ms) : 'red';
                var txt = curItem.status === 'ok' ? curItem.avg_ms + 'ms' : '超时';
                $('#currentLineDot').attr('class', 'line-dot-s ' + lv);
                $('#currentLinePing').text(txt).css('color', lv==='green'?'#16a34a':lv==='yellow'?'#d97706':'#dc2626');
            }
            for (var k in data) {
                var item = data[k];
                var level = item.status === 'ok' ? pingLevel(item.avg_ms) : 'red';
                var text = item.status === 'ok' ? item.avg_ms + 'ms' : '超时';
                $('[data-dot="' + k + '"]').attr('class', 'lp-dot ' + level);
                $('[data-ping="' + k + '"]').attr('class', 'lp-ping ' + level).text(text);
            }
        }

        // 自动切到最快线路（仅首次加载执行一次）
        function autoSwitchBestLine(data) {
            // 已经自动切换过，不再重复（防无限刷新）
            try { if (sessionStorage.getItem(AUTO_SWITCH_KEY)) return; } catch(e){}

            var bestIdx = -1, bestMs = 999999;
            for (var k in data) {
                var item = data[k];
                if (item.status === 'ok' && item.avg_ms < bestMs) {
                    bestMs = item.avg_ms;
                    bestIdx = parseInt(k);
                }
            }
            // 没有可用线路，不切
            if (bestIdx < 0) return;
            // 当前线路已经是最快的，不切
            if (bestIdx === CURRENT_IDX) return;
            // 当前线路可用且差距不大（不超过2倍），不切
            var curItem = data[CURRENT_IDX];
            if (curItem && curItem.status === 'ok' && curItem.avg_ms <= bestMs * 2) return;

            // 标记已自动切换，防止刷新后再次触发
            try { sessionStorage.setItem(AUTO_SWITCH_KEY, '1'); } catch(e){}

            // 显示切换提示
            $('#currentLinePing').text('自动切换中...');
            $.ajax({
                url: './account.php?action=update_line_login',
                type: 'POST', dataType: 'json', data: { line: bestIdx },
                success: function(res) {
                    if (res.code == 200) {
                        location.reload();
                    }
                }
            });
        }

        function doPingLines(isManual) {
            $('#currentLinePing').text('检测中').css('color', '');
            $('[data-ping]').attr('class', 'lp-ping gray').text('检测中...');
            $('[data-dot]').attr('class', 'lp-dot gray');
            $('#currentLineDot').attr('class', 'line-dot-s gray');

            var lineKeys = [];
            $('.lp-item').each(function(){ lineKeys.push(parseInt($(this).data('line'))); });
            var results = {};
            var done = 0;
            var total = lineKeys.length;

            lineKeys.forEach(function(k) {
                $.ajax({
                    url: './account.php?action=ping_single_line_login&line=' + k,
                    type: 'GET', dataType: 'json', timeout: 5000,
                    success: function(res) {
                        if (res.code == 200 && res.data) {
                            results[k] = res.data;
                            var item = res.data;
                            var level = item.status === 'ok' ? pingLevel(item.avg_ms) : 'red';
                            var text = item.status === 'ok' ? item.avg_ms + 'ms' : '超时';
                            $('[data-dot="' + k + '"]').attr('class', 'lp-dot ' + level);
                            $('[data-ping="' + k + '"]').attr('class', 'lp-ping ' + level).text(text);
                            if (k === CURRENT_IDX) {
                                $('#currentLineDot').attr('class', 'line-dot-s ' + level);
                                $('#currentLinePing').text(text).css('color', level==='green'?'#16a34a':level==='yellow'?'#d97706':'#dc2626');
                            }
                        }
                    },
                    error: function() {
                        results[k] = {ms: -1, avg_ms: -1, status: 'timeout'};
                        $('[data-dot="' + k + '"]').attr('class', 'lp-dot red');
                        $('[data-ping="' + k + '"]').attr('class', 'lp-ping red').text('超时');
                        if (k === CURRENT_IDX) {
                            $('#currentLineDot').attr('class', 'line-dot-s red');
                            $('#currentLinePing').text('超时').css('color', '#dc2626');
                        }
                    },
                    complete: function() {
                        done++;
                        if (done >= total && !isManual) {
                            autoSwitchBestLine(results);
                        }
                    }
                });
            });
        }

        // 打开/关闭面板
        $('#lineToggleBtn').on('click', function(e) {
            e.stopPropagation();
            var $wrap = $('.dock-line-wrap');
            $wrap.toggleClass('open');
            $('#linePanel').toggleClass('show');
        });
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.dock-line-wrap').length) {
                $('.dock-line-wrap').removeClass('open');
                $('#linePanel').removeClass('show');
            }
        });

        // 手动刷新检测（清除自动切换标记，允许再次自动切换）
        $('#lpRefresh').on('click', function(e) {
            e.stopPropagation();
            try { sessionStorage.removeItem(AUTO_SWITCH_KEY); } catch(ex){}
            doPingLines(false);
        });

        // 手动切换线路
        $('.lp-item').on('click', function() {
            var idx = parseInt($(this).data('line'));
            if (idx === CURRENT_IDX) return;
            var $el = $(this);
            $el.find('.lp-ping').text('切换中...');
            // 手动切换后标记，防止刷新后又被自动切回
            try { sessionStorage.setItem(AUTO_SWITCH_KEY, '1'); } catch(e){}
            $.ajax({
                url: './account.php?action=update_line_login',
                type: 'POST', dataType: 'json', data: { line: idx },
                success: function(res) {
                    if (res.code == 200) {
                        location.reload();
                    } else {
                        $el.find('.lp-ping').text(res.msg || '失败');
                    }
                },
                error: function() {
                    $el.find('.lp-ping').text('切换失败');
                }
            });
        });

        // 页面加载：自动检测 + 自动选最快线路
        doPingLines(false);

        // 清除按钮显隐
        $('.mac-form').on('input focus', '.mac-input', function(){
            var $btn = $(this).siblings('.mac-clear');
            if(this.value.length > 0){ $btn.addClass('is-show'); } else { $btn.removeClass('is-show'); }
        }).on('blur', '.mac-input', function(){
            var $btn = $(this).siblings('.mac-clear');
            setTimeout(function(){ $btn.removeClass('is-show'); }, 150);
        });
        $('.mac-form').on('click', '.mac-clear', function(){
            var $input = $(this).siblings('.mac-input');
            $input.val('').focus();
            $(this).removeClass('is-show');
        });

        // 密码框删一个字符自动全删（仅对用户「主动删除」生效）
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
        var pwPrevLen = 0;
        $('#pw').on('focus', function(){ pwPrevLen = this.value.length; });
        $('#pw').on('input', function(e){
            var orig = e.originalEvent || {};
            var inputType = orig.inputType || '';
            // inputType 在 Chrome 60+/Firefox 67+/Safari 10.1+ 全支持；
            // 极少数老浏览器没有 inputType（空字符串），保守起见此时不触发清空，避免误伤。
            var isUserDeletion = inputType.indexOf('delete') === 0;
            if (isUserDeletion && this.value.length < pwPrevLen && this.value.length > 0) {
                this.value = '';
                $(this).siblings('.mac-clear').removeClass('is-show');
            }
            pwPrevLen = this.value.length;
        });
    });
</script>

</body>
</html>