<?php
$uc_app_mode = !empty($uc_app_mode);
$uc_hide_bottom_nav = !empty($uc_hide_bottom_nav);
$uc_show_bottom_nav = !empty($uc_show_bottom_nav);
if (function_exists('dcDefaultUserBottomNavHidden')) {
    $uc_hide_bottom_nav = dcDefaultUserBottomNavHidden($uc_app_mode, $uc_show_bottom_nav, $uc_hide_bottom_nav, $uc_page_title ?? '');
} else {
    if ($uc_show_bottom_nav) {
        $uc_hide_bottom_nav = false;
    } elseif (isMobile() && $uc_app_mode) {
        // default 模板的移动端 App 二级页通常带顶部返回栏，不再叠加底部导航。
        // 若个别页面仍需显示，可在 include footer 前设置 $uc_show_bottom_nav = true。
        $_uc_mobile_page_title = isset($uc_page_title) ? trim((string)$uc_page_title) : '';
        $_uc_request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $_uc_request_path = (string)(parse_url((string)$_uc_request_uri, PHP_URL_PATH) ?: '');
        $_uc_request_path = rtrim($_uc_request_path, '/');
        $_uc_request_path = $_uc_request_path === '' ? '/' : $_uc_request_path;
        $_uc_is_user_home = preg_match('#(?:^|/)user(?:/index\.php)?$#i', $_uc_request_path) === 1;
        if ($_uc_mobile_page_title !== '' && !$_uc_is_user_home) {
            $uc_hide_bottom_nav = true;
        }
    }
}
?>
<?php if (isMobile() && $uc_app_mode): ?>
<?php doAction('open_footer') ?>
    </div><!-- .m-content -->
    <div class="uc-global-pull-refresh-indicator" id="uc-global-pull-refresh-indicator" aria-hidden="true">
        <i class="ri-refresh-line"></i>
    </div>
    <?php if (empty($uc_hide_bottom_nav) && empty($uc_bottom_nav_rendered)): ?>
    <?php $_bottom_nav_view = View::getBottomNavView('render'); ?>
    <?php if (is_file($_bottom_nav_view)): ?>
    <style>body.m-app-body { padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 70px); }</style>
    <?php include $_bottom_nav_view; ?>
    <?php $uc_bottom_nav_rendered = true; ?>
    <?php endif; ?>
    <?php endif; ?>
    <style>
        .uc-global-pull-refresh-indicator {
            display: none;
            opacity: 0;
            pointer-events: none;
        }

        @media (max-width: 768px) {
            html,
            body.m-app-body {
                overscroll-behavior-y: none;
            }
            .uc-global-pull-refresh-indicator {
                position: fixed;
                top: calc(env(safe-area-inset-top, 0px) + 8px);
                left: 50%;
                width: 42px;
                height: 42px;
                padding: 0;
                box-sizing: border-box;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.96);
                color: var(--theme-primary, #2196F3);
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.14);
                border: 1px solid rgba(226, 232, 240, 0.96);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1005;
                opacity: 0;
                pointer-events: none;
                transform: translate3d(-50%, -22px, 0) scale(0.8);
                transition: opacity 0.18s ease, transform 0.18s cubic-bezier(.22, .61, .36, 1);
                will-change: transform, opacity;
            }
            .uc-global-pull-refresh-indicator i {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 20px;
                height: 20px;
                font-size: 20px;
                line-height: 1;
                font-style: normal;
            }
            .uc-global-pull-refresh-indicator i::before {
                display: block;
                line-height: 1;
                transition: transform 0.2s ease;
                transform-origin: center center;
                will-change: transform;
            }
            .uc-global-pull-refresh-indicator.is-visible {
                opacity: 1;
            }
            .uc-global-pull-refresh-indicator.is-ready i::before {
                transform: rotate(180deg);
            }
            .uc-global-pull-refresh-indicator.is-refreshing {
                opacity: 1;
            }
            .uc-global-pull-refresh-indicator.is-refreshing i::before {
                animation: ucGlobalPullRefreshSpin 0.75s linear infinite;
            }
        }

        @keyframes ucGlobalPullRefreshSpin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
    <script>
    (function() {
        if (window.__ucGlobalPullRefreshBound) return;
        window.__ucGlobalPullRefreshBound = true;

        var indicator = document.getElementById('uc-global-pull-refresh-indicator');
        if (!indicator) return;

        var startY = 0;
        var startX = 0;
        var pullDistance = 0;
        var pulling = false;
        var refreshing = false;
        var startBuffer = 30;
        var triggerDistance = 110;
        var maxDistance = 136;
        var refreshHoldDistance = 68;

        function isEnabled() {
            return window.matchMedia('(max-width: 768px)').matches;
        }

        function getScrollTop() {
            return window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
        }

        function easeDistance(distance) {
            if (distance <= 0) return 0;
            if (distance <= 130) return distance * 0.42;
            return Math.min(maxDistance, 54.6 + (distance - 130) * 0.34);
        }

        function setIndicatorProgress(distance, isRefreshing) {
            var progress = Math.max(0, Math.min(1, distance / triggerDistance));
            var translateY = -22 + Math.min(isRefreshing ? 56 : 84, distance * 0.72);
            var scale = isRefreshing ? 1 : (0.8 + progress * 0.2);
            indicator.style.transform = 'translate3d(-50%,' + translateY.toFixed(1) + 'px,0) scale(' + scale.toFixed(3) + ')';
            indicator.style.opacity = progress > 0 ? Math.min(1, 0.24 + progress * 0.76).toFixed(3) : '';
        }

        function clearIndicatorStyle() {
            indicator.style.transform = '';
            indicator.style.opacity = '';
        }

        function resetPullState() {
            pullDistance = 0;
            pulling = false;
            indicator.classList.remove('is-visible', 'is-ready', 'is-refreshing');
            clearIndicatorStyle();
        }

        // 检测触摸目标是否处于可独立滚动的子容器内
        function isInsideScrollableChild(target) {
            var el = target;
            while (el && el !== document.body && el !== document.documentElement) {
                var ov = el.style.overflowY || window.getComputedStyle(el).overflowY;
                if ((ov === 'auto' || ov === 'scroll') && el.scrollHeight > el.clientHeight + 1) {
                    return true;
                }
                el = el.parentElement;
            }
            return false;
        }

        document.addEventListener('touchstart', function(event) {
            if (!isEnabled() || refreshing || event.touches.length !== 1) return;
            // 当页面被全屏覆盖层锁定时不触发下拉刷新
            if (window.getComputedStyle(document.body).position === 'fixed') return;
            // 触摸点在可独立滚动的子容器内时不触发下拉刷新
            if (isInsideScrollableChild(event.target)) return;
            if (getScrollTop() > 0) {
                pulling = false;
                return;
            }
            startY = event.touches[0].clientY;
            startX = event.touches[0].clientX;
            pullDistance = 0;
            pulling = true;
        }, { passive: true });

        document.addEventListener('touchmove', function(event) {
            if (!isEnabled() || !pulling || refreshing || event.touches.length !== 1) return;
            var currentY = event.touches[0].clientY;
            var currentX = event.touches[0].clientX;
            var deltaY = currentY - startY;
            var deltaX = currentX - startX;

            if (Math.abs(deltaX) > Math.abs(deltaY) && pullDistance === 0) {
                return;
            }
            if (deltaY <= 0) {
                if (pullDistance > 0) {
                    resetPullState();
                }
                return;
            }
            if (getScrollTop() > 0) {
                resetPullState();
                return;
            }

            var effectiveDelta = deltaY - startBuffer;
            if (effectiveDelta <= 0) {
                indicator.classList.remove('is-visible', 'is-ready');
                clearIndicatorStyle();
                event.preventDefault();
                return;
            }

            pullDistance = easeDistance(effectiveDelta);
            indicator.classList.add('is-visible');
            indicator.classList.toggle('is-ready', pullDistance >= triggerDistance);
            setIndicatorProgress(pullDistance, false);
            event.preventDefault();
        }, { passive: false });

        document.addEventListener('touchend', function() {
            if (!isEnabled() || !pulling || refreshing) return;
            pulling = false;
            if (pullDistance >= triggerDistance) {
                refreshing = true;
                indicator.classList.remove('is-ready');
                void indicator.offsetWidth;
                indicator.classList.add('is-visible', 'is-refreshing');
                setIndicatorProgress(refreshHoldDistance, true);
                window.setTimeout(function() {
                    window.location.reload();
                }, 650);
                return;
            }
            resetPullState();
        }, { passive: true });

        document.addEventListener('touchcancel', function() {
            if (refreshing) return;
            resetPullState();
        }, { passive: true });

        window.addEventListener('resize', function() {
            if (!isEnabled() && !refreshing) {
                resetPullState();
            }
        });
    })();
    </script>
    <style>
        body.uc-logout-confirm-open { overflow: hidden; }
        .uc-logout-confirm {
            position: fixed;
            inset: 0;
            z-index: 3000;
            background: rgba(15, 23, 42, 0.3);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            display: none;
            align-items: flex-end;
            justify-content: center;
        }
        .uc-logout-confirm.is-active { display: flex; }
        .uc-logout-sheet {
            width: 100%;
            max-width: 500px;
            background: #fff;
            border-radius: 20px 20px 0 0;
            box-shadow: 0 -8px 40px rgba(0,0,0,0.12);
            transform: translateY(100%);
            transition: transform 0.28s cubic-bezier(.22,.61,.36,1);
            overflow: hidden;
            padding-bottom: env(safe-area-inset-bottom, 0px);
        }
        .uc-logout-confirm.is-active .uc-logout-sheet { transform: translateY(0); }
        .uc-logout-handle {
            display: flex;
            justify-content: center;
            padding: 14px 0 6px;
            cursor: grab;
        }
        .uc-logout-handle span {
            width: 36px;
            height: 4px;
            border-radius: 4px;
            background: #e2e8f0;
        }
        .uc-logout-body {
            padding: 20px 24px 10px;
            text-align: center;
        }
        .uc-logout-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #dc2626;
            margin-bottom: 16px;
        }
        .uc-logout-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }
        .uc-logout-desc {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.6;
        }
        .uc-logout-actions {
            padding: 16px 24px 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .uc-logout-btn {
            width: 100%;
            height: 48px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            cursor: pointer;
        }
        .uc-logout-btn.is-submit {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
            box-shadow: 0 4px 14px rgba(220,38,38,0.25);
        }
        .uc-logout-btn.is-submit:active { opacity: 0.85; }
        .uc-logout-btn.is-cancel {
            background: #f3f4f6;
            color: #6b7280;
        }
        .uc-logout-btn.is-cancel:active { background: #e5e7eb; }
    </style>
    <script>
    (function() {
        if (window.__ucLogoutConfirmBound) return;
        window.__ucLogoutConfirmBound = true;

        var sheetEl = null;

        function bindSwipeDismiss(el, onDismiss) {
            var startY = 0, currentY = 0, dragging = false;
            el.addEventListener('touchstart', function(e) {
                startY = e.touches[0].clientY;
                currentY = 0;
                dragging = true;
                el.style.transition = 'none';
            }, { passive: true });
            el.addEventListener('touchmove', function(e) {
                if (!dragging) return;
                var dy = e.touches[0].clientY - startY;
                if (dy < 0) { dy = 0; }
                if (dy > 0) { e.preventDefault(); }
                currentY = dy;
                el.style.transform = 'translateY(' + dy + 'px)';
            }, { passive: false });
            el.addEventListener('touchend', function() {
                if (!dragging) return;
                dragging = false;
                el.style.transition = 'transform 0.28s cubic-bezier(.22,.61,.36,1)';
                if (currentY > 80) {
                    el.style.transform = 'translateY(100%)';
                    setTimeout(onDismiss, 200);
                } else {
                    el.style.transform = 'translateY(0)';
                }
            }, { passive: true });
        }

        function ensureDialog() {
            var dialog = document.getElementById('ucLogoutConfirm');
            if (dialog) return dialog;
            dialog = document.createElement('div');
            dialog.id = 'ucLogoutConfirm';
            dialog.className = 'uc-logout-confirm';
            dialog.innerHTML = ''
                + '<div class="uc-logout-sheet">'
                + '<div class="uc-logout-handle"><span></span></div>'
                + '<div class="uc-logout-body">'
                + '<div class="uc-logout-icon"><i class="ri-logout-box-r-line"></i></div>'
                + '<div class="uc-logout-title">确认退出登录？</div>'
                + '<div class="uc-logout-desc">退出后将返回首页，如需继续使用请重新登录</div>'
                + '</div>'
                + '<div class="uc-logout-actions">'
                + '<button type="button" class="uc-logout-btn is-submit" data-action="confirm">确认退出</button>'
                + '<button type="button" class="uc-logout-btn is-cancel" data-action="cancel">暂不退出</button>'
                + '</div>'
                + '</div>';
            document.body.appendChild(dialog);
            sheetEl = dialog.querySelector('.uc-logout-sheet');
            bindSwipeDismiss(sheetEl, closeDialog);
            dialog.addEventListener('click', function(event) {
                if (event.target === dialog || event.target.getAttribute('data-action') === 'cancel') {
                    closeDialog();
                    return;
                }
                if (event.target.getAttribute('data-action') === 'confirm') {
                    var href = dialog.getAttribute('data-href') || '/user/account.php?action=logout';
                    closeDialog();
                    window.location.href = href;
                }
            });
            return dialog;
        }

        function openDialog(href) {
            var dialog = ensureDialog();
            dialog.setAttribute('data-href', href || '/user/account.php?action=logout');
            document.body.classList.add('uc-logout-confirm-open');
            dialog.classList.add('is-active');
            if (sheetEl) {
                sheetEl.style.transition = 'transform 0.28s cubic-bezier(.22,.61,.36,1)';
                sheetEl.style.transform = 'translateY(0)';
            }
        }

        function closeDialog() {
            var dialog = document.getElementById('ucLogoutConfirm');
            if (!dialog) return;
            if (sheetEl) {
                sheetEl.style.transition = 'transform 0.28s cubic-bezier(.22,.61,.36,1)';
                sheetEl.style.transform = 'translateY(100%)';
            }
            setTimeout(function() {
                dialog.classList.remove('is-active');
                document.body.classList.remove('uc-logout-confirm-open');
            }, 260);
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') closeDialog();
        });

        document.addEventListener('click', function(event) {
            var link = event.target && event.target.closest ? event.target.closest('a[href*="account.php?action=logout"]') : null;
            if (!link) return;
            event.preventDefault();
            openDialog(link.getAttribute('href') || '/user/account.php?action=logout');
        }, false);
    })();
    </script>
    <?php
    $_uc_footer_info = Option::get('footer_info');
    $_uc_icp = Option::get('icp');
    if (!empty($_uc_footer_info) || !empty($_uc_icp)):
    ?>
    <style>
        .uc-site-footer { background: rgba(248,248,248,0.5); padding: 20px 16px calc(env(safe-area-inset-bottom,0px) + <?= empty($uc_hide_bottom_nav) ? '78px' : '20px' ?>); color: #999; border-top: 1px solid #eee; font-size: 12px; }
        .uc-site-footer .copyright { display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 5px; }
        .uc-site-footer .copyright a { color: #999; transition: color .2s; text-decoration: none; }
        .uc-site-footer .copyright a:hover { color: #4C7D71; text-decoration: none; }
        .uc-site-footer .divider { color: #eee; margin: 0 2px; font-size: 10px; }
        @media (max-width: 768px) {
            .uc-site-footer .copyright { flex-direction: column; gap: 2px; }
            .uc-site-footer .divider { display: none; }
        }
    </style>
    <footer class="uc-site-footer">
        <div class="copyright">
            <?php if (!empty($_uc_footer_info)): ?><span><?= $_uc_footer_info ?></span><?php endif; ?>
            <?php if (!empty($_uc_footer_info) && !empty($_uc_icp)): ?><span class="divider">|</span><?php endif; ?>
            <?php if (!empty($_uc_icp)): ?><a href="https://beian.miit.gov.cn/" target="_blank" rel="nofollow noopener"><?= htmlspecialchars($_uc_icp) ?></a><?php endif; ?>
        </div>
    </footer>
    <?php endif; ?>
    <?php include __DIR__ . '/../_adaptive_layer_theme.php'; ?>
 </body>
 </html>
 <?php else: ?>
 <?php doAction('open_footer') ?>
                </div><!-- /.user-content -->
            </div><!-- /.user-layout -->
        </main>
    </div>

    <script>
    (function(){
        // 自动高亮当前菜单
        var path = location.pathname + location.search;
        var links = document.querySelectorAll('.sidebar-menu a');
        var best = null, bestLen = 0;
        links.forEach(function(a){
            var href = a.getAttribute('href');
            if (href && path.indexOf(href) === 0 && href.length > bestLen) {
                best = a; bestLen = href.length;
            }
        });
        // /user 精确匹配
        if (path === '/user' || path === '/user/' || path === '/user/index.php') {
            links.forEach(function(a){ a.classList.remove('active','menu-current'); });
            var home = document.getElementById('menu-index');
            if (home) home.classList.add('active');
        } else if (best) {
            links.forEach(function(a){ a.classList.remove('active','menu-current'); });
            best.classList.add('active');
        }
        // macOS 窗口标题跟随当前菜单
        setTimeout(function(){
            var cur = document.querySelector('.sidebar-menu a.active, .sidebar-menu a.menu-current');
            var wt = document.querySelector('.win-title');
            if (cur && wt) wt.textContent = cur.textContent.trim();
        }, 0);
    })();
    </script>
    <style>
        body.uc-logout-confirm-open {
            overflow: hidden;
        }
        .uc-logout-confirm {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding: 14px;
            background: rgba(15, 23, 42, 0.26);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.18s ease;
            z-index: 3000;
        }
        .uc-logout-confirm.is-active {
            opacity: 1;
            pointer-events: auto;
        }
        .uc-logout-confirm-card {
            width: min(100%, 420px);
            margin-bottom: calc(8px + env(safe-area-inset-bottom, 0px));
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 24px 48px rgba(15, 23, 42, 0.18);
            overflow: hidden;
            transform: translateY(18px) scale(0.98);
            transition: transform 0.22s cubic-bezier(.22, .61, .36, 1);
        }
        .uc-logout-confirm.is-active .uc-logout-confirm-card {
            transform: translateY(0) scale(1);
        }
        .uc-logout-confirm-head {
            padding: 20px 18px 16px;
            background: linear-gradient(180deg, var(--theme-primary) 0%, var(--tp-dark) 100%);
            color: #ffffff;
        }
        .uc-logout-confirm-badge {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: rgba(255,255,255,0.14);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-bottom: 14px;
        }
        .uc-logout-confirm-title {
            font-size: 20px;
            font-weight: 700;
            line-height: 1.25;
        }
        .uc-logout-confirm-desc {
            margin-top: 8px;
            font-size: 13px;
            line-height: 1.8;
            color: rgba(255,255,255,0.82);
        }
        .uc-logout-confirm-body {
            padding: 18px 16px 8px;
        }
        .uc-logout-confirm-tip {
            color: #20293a;
            font-size: 15px;
            line-height: 1.8;
        }
        .uc-logout-confirm-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            padding: 16px 16px 16px;
        }
        .uc-logout-confirm-btn {
            height: 46px;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 700;
        }
        .uc-logout-confirm-btn.is-cancel {
            border: 0;
            background: #f4f7fb;
            color: #6b7688;
        }
        .uc-logout-confirm-btn.is-submit {
            border: 1px solid var(--theme-primary);
            background: #ffffff;
            color: var(--theme-primary);
            box-shadow: 0 8px 24px rgba(47, 117, 255, 0.08);
        }
        @media (min-width: 769px) {
            .uc-logout-confirm {
                align-items: center;
            }
            .uc-logout-confirm-card {
                margin-bottom: 0;
            }
        }
    </style>
    <script>
    (function() {
        if (window.__ucLogoutConfirmBound) return;
        window.__ucLogoutConfirmBound = true;

        function ensureDialog() {
            var dialog = document.getElementById('ucLogoutConfirm');
            if (dialog) return dialog;
            dialog = document.createElement('div');
            dialog.id = 'ucLogoutConfirm';
            dialog.className = 'uc-logout-confirm';
            dialog.innerHTML = ''
                + '<div class="uc-logout-confirm-card" role="dialog" aria-modal="true" aria-labelledby="ucLogoutConfirmTitle">'
                + '<div class="uc-logout-confirm-head">'
                + '<div class="uc-logout-confirm-badge"><i class="fa fa-sign-out"></i></div>'
                + '<div class="uc-logout-confirm-title" id="ucLogoutConfirmTitle">退出登录确认</div>'
                + '<div class="uc-logout-confirm-desc">确定要退出当前账号吗？</div>'
                + '</div>'
                + '<div class="uc-logout-confirm-actions">'
                + '<button type="button" class="uc-logout-confirm-btn is-cancel" data-action="cancel">暂不退出</button>'
                + '<button type="button" class="uc-logout-confirm-btn is-submit" data-action="confirm">确认退出</button>'
                + '</div>'
                + '</div>';
            document.body.appendChild(dialog);
            dialog.addEventListener('click', function(event) {
                if (event.target === dialog || event.target.getAttribute('data-action') === 'cancel') {
                    closeDialog();
                    return;
                }
                if (event.target.getAttribute('data-action') === 'confirm') {
                    var href = dialog.getAttribute('data-href') || '/user/account.php?action=logout';
                    closeDialog();
                    window.location.href = href;
                }
            });
            return dialog;
        }

        function openDialog(href) {
            var dialog = ensureDialog();
            dialog.setAttribute('data-href', href || '/user/account.php?action=logout');
            document.body.classList.add('uc-logout-confirm-open');
            dialog.classList.add('is-active');
        }

        function closeDialog() {
            var dialog = document.getElementById('ucLogoutConfirm');
            if (!dialog) return;
            dialog.classList.remove('is-active');
            document.body.classList.remove('uc-logout-confirm-open');
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeDialog();
            }
        });

        document.addEventListener('click', function(event) {
            var link = event.target && event.target.closest ? event.target.closest('a[href*="account.php?action=logout"]') : null;
            if (!link) return;
            event.preventDefault();
            openDialog(link.getAttribute('href') || '/user/account.php?action=logout');
        }, false);
    })();
    </script>
    <?php if (empty($uc_hide_bottom_nav)): ?>
        <?php $_bottom_nav_view = View::getBottomNavView('render'); ?>
        <?php if (is_file($_bottom_nav_view)): ?>
        <style>
            @media (max-width: 1200px) {
                body {
                    padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 110px);
                }
            }
        </style>
        <?php include $_bottom_nav_view; ?>
        <?php endif; ?>
    <?php endif; ?>
    <?php include __DIR__ . '/../_adaptive_layer_theme.php'; ?>
</body>
</html>
<?php endif; ?>
