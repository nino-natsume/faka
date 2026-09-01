<?php
/**
 * 页面底部信息
 */
defined('DC_ROOT') || exit('access denied!');

// 获取单页模式设置
$_single_page_mode = _g('single_page_mode');
$_footer_show = _g('footer_show') ?: 'y';
$_bottom_nav_view = View::getBottomNavView('render');
$_footer_icp_icon = defined('TEMPLATE_URL') ? TEMPLATE_URL . 'img/icp.png' : DC_URL . 'content/templates/auroracard/img/icp.png';
?>
<?php doAction('page_footer') ?>

<?php if($_single_page_mode !== 'y' || $_footer_show == 'y'): ?>
<footer class="main-footer" style="margin-top: 20px;">
    <div class="container">
        <div class="footer-content">
            <div class="footer-info">
                <div class="copyright">
                    <span><?= $footer_info ?></span>
                    <?php if (!empty($icp)): ?>
                        <span class="divider">|</span>
                        <a class="icp-link" href="https://beian.miit.gov.cn/" target="_blank" rel="nofollow noopener"><img src="<?= htmlspecialchars($_footer_icp_icon, ENT_QUOTES) ?>" alt="ICP备案"><?= $icp ?></a>
                    <?php endif; ?>
                    <span class="divider">|</span>
                    <?php doAction('index_footer') ?>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
    /* 桌面端页脚样式 - 极简版 */
    .main-footer {
        background: rgba(248, 248, 248, 0.5);
        padding: 20px 0;
        color: #999;
        border-top: 1px solid #eee;
        font-size: 12px;
    }
    
    .footer-content {
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    .footer-brand-text {
        font-weight: 700;
        color: #4C7D71;
        font-size: 13px;
    }

    .copyright {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        gap: 5px;
    }

    .copyright a {
        color: #999;
        transition: color 0.2s;
    }

    .copyright .icp-link {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .copyright .icp-link img {
        width: 14px;
        height: 14px;
        object-fit: contain;
        display: inline-block;
    }
    
    .copyright a:hover {
        color: #4C7D71;
        text-decoration: none;
    }

    .divider {
        color: #eee;
        margin: 0 2px;
        font-size: 10px;
    }

    /* 移动端适配 */
    @media (max-width: 768px) {
        .main-footer {
            padding: 15px 0 70px; /* 底部留出空间 */
        }
        .copyright {
            flex-direction: column;
            gap: 2px;
        }
        .divider {
            display: none; /* 移动端隐藏竖线，改换行 */
        }
    }
</style>
<?php endif; ?>

<?php 
if($_single_page_mode !== 'y'): 
// 判断当前页面
$_current_action = isset($_GET['action']) ? $_GET['action'] : '';
$_current_uri = $_SERVER['REQUEST_URI'] ?? '';
$_is_home = (empty($_current_action) && strpos($_current_uri, 'user/') === false && strpos($_current_uri, '?') === false) || $_current_action === 'index';
$_is_order_query = $_current_action === 'order_query';
$_is_user = strpos($_current_uri, 'user/') !== false;
?>
<!-- 底部导航 - 仅非单页模式显示 -->
<?php if (is_file($_bottom_nav_view)): ?>
<?php include $_bottom_nav_view; ?>
<?php endif; ?>
<?php endif; ?>

<style>

</style>

<script>
    if(typeof tipsMsg === 'undefined') {
        var tipsMsg = {
            least_one    : '',
            exceeds      : '',
            exceeds_limit: '',
            mobile_order : ''
        };
    }
</script>

<?php $_current_template_slug = basename(rtrim(str_replace('\\', '/', TEMPLATE_PATH), '/')); ?>
<?php if ($_current_template_slug === 'default'): ?>
<div class="fk-global-pull-refresh-indicator" id="fk-global-pull-refresh-indicator" aria-hidden="true">
    <i class="ri-refresh-line"></i>
</div>
<style>
    .fk-global-pull-refresh-indicator {
        display: none;
        opacity: 0;
        pointer-events: none;
    }

    @media (max-width: 768px) {
        html,
        body {
            overscroll-behavior-y: none;
        }
        .fk-global-pull-refresh-indicator {
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
        .fk-global-pull-refresh-indicator i {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            font-size: 20px;
            line-height: 1;
            font-style: normal;
        }
        .fk-global-pull-refresh-indicator i::before {
            display: block;
            line-height: 1;
            transition: transform 0.2s ease;
            transform-origin: center center;
            will-change: transform;
        }
        .fk-global-pull-refresh-indicator.is-visible {
            opacity: 1;
        }
        .fk-global-pull-refresh-indicator.is-ready i::before {
            transform: rotate(180deg);
        }
        .fk-global-pull-refresh-indicator.is-refreshing {
            opacity: 1;
        }
        .fk-global-pull-refresh-indicator.is-refreshing i::before {
            animation: fkGlobalPullRefreshSpin 0.75s linear infinite;
        }
    }

    @keyframes fkGlobalPullRefreshSpin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
<script>
(function() {
    if (window.__fkGlobalPullRefreshBound) return;
    window.__fkGlobalPullRefreshBound = true;

    var indicator = document.getElementById('fk-global-pull-refresh-indicator');
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

    // 检测触摸目标是否处于可独立滚动的子容器内（如聊天消息列表）
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
        // 当页面被全屏覆盖层锁定时（如聊天页 body.chat-open）不触发下拉刷新
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
<?php endif; ?>

<?php doAction('tpl_footer'); ?>

<!-- <script src="<?= TEMPLATE_URL ?>js/main.js"></script> -->
<!-- <script src="../../content/common/js/home.js"></script> -->
</body>
</html>
