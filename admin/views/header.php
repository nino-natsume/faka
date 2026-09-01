<?php
defined('DC_ROOT') || exit('access denied!');

// 获取当前页面路径，用于菜单高亮
$currentPage = basename($_SERVER['PHP_SELF']);
$currentAction = $_GET['action'] ?? '';
$currentFilter = $_GET['filter'] ?? '';
$currentType = $_GET['type'] ?? '';

// 菜单状态判断函数
function isMenuOpen($menuId) {
    global $currentPage, $currentAction, $currentType;

    // 插件配置页面不展开插件管理菜单（配置页面通常从其他地方打开）
    $plugin = $_GET['plugin'] ?? '';
    if ($currentPage === 'plugin.php' && $currentAction === 'setting_page' && $plugin === 'goods_physical' && ($_GET['shipping_action'] ?? '') === 'manage') {
        return $menuId === 'menu-goods';
    }
    if ($currentPage === 'plugin.php' && $currentAction === 'setting_page') {
        return $menuId === 'menu-plugin'; // 插件配置页面高亮插件菜单
    }

    // 轮播管理页面属于外观设置
    if ($currentPage === 'plugin.php' && $plugin === 'banner' && $currentAction === 'manage') {
        return $menuId === 'menu-appearance';
    }
    if ($currentPage === 'shop.php' && in_array($currentAction, ['user', 'user_save', 'user_config_save'], true)) {
        return $menuId === 'menu-user';
    }
    if ($currentPage === 'shop.php' && in_array($currentAction, ['station_setting', 'station_setting_save'], true)) {
        return $menuId === 'menu-station';
    }

    $menuMap = [
        'menu-goods' => ['goods.php', 'goods_save.php', 'goods_recycle.php', 'sort.php', 'sku.php', 'stock.php', 'price_rule.php', 'profit_rule.php', 'single_rule.php'],
        'menu-order' => ['order.php', 'order_recycle.php', 'aftersale.php', 'order_recharge.php'],
        'menu-user' => ['user.php', 'user_recycle.php', 'member.php', 'withdraw.php', 'user_log.php', 'recharge_card.php'],
        'menu-blog' => ['article.php', 'article_save.php', 'comment.php', 'page.php'],
        'menu-station' => ['station.php'],
        'menu-appearance' => ['template.php'],
        'menu-plugin' => ['plugin.php'],
        'menu-store' => ['store.php'],
        'menu-system' => ['setting.php', 'shop.php', 'resources.php', 'upgrade.php', 'calibrate.php'],
    ];
    // sort.php 根据 type 参数判断属于哪个菜单
    if ($currentPage === 'sort.php') {
        if ($currentType === 'blog') {
            return $menuId === 'menu-blog';
        }
        return $menuId === 'menu-goods';
    }
    if (isset($menuMap[$menuId])) {
        return in_array($currentPage, $menuMap[$menuId]);
    }
    return false;
}

function isMenuItemActive($menuItemId) {
    global $currentPage, $currentAction, $currentFilter, $currentType;
    $itemMap = [
        // 商品管理
        'menu-goods-list' => ['page' => 'goods.php'],
        'menu-sort-list' => ['page' => 'sort.php', 'type' => ''],
        'menu-sku-list' => ['page' => 'sku.php'],
        'menu-stock-list' => ['page' => 'stock.php'],
        'menu-goods-price-rule' => ['page' => 'price_rule.php'],
        'menu-goods-recycle' => ['page' => 'goods_recycle.php'],
        // 订单管理
        'menu-order-goods' => ['page' => 'order.php'],
        'menu-order-recharge' => ['page' => 'order_recharge.php'],
        'menu-order-recycle' => ['page' => 'order_recycle.php'],
        // 用户管理
        'menu-user-default' => ['page' => 'user.php'],
        'menu-user-recycle' => ['page' => 'user_recycle.php'],
        'menu-user-member' => ['page' => 'member.php'],
        'menu-user-withdraw' => ['page' => 'withdraw.php'],
        'menu-user-log' => ['page' => 'user_log.php'],
        'menu-user-recharge-card' => ['page' => 'recharge_card.php'],
        // 博客管理
        'menu-blog-list' => ['page' => 'article.php'],
        'menu-blog-comment' => ['page' => 'comment.php'],
        'menu-blog-sort' => ['page' => 'sort.php', 'type' => 'blog'],
        'menu-blog-page' => ['page' => 'page.php'],
        // 分店管理
        'menu-station-lists' => ['page' => 'station.php', 'action' => 'lists'],
        'menu-station-level' => ['page' => 'station.php', 'action' => 'level'],
        'menu-user-config' => ['page' => 'shop.php', 'action' => 'user'],
        'menu-station-setting' => ['page' => 'shop.php', 'action' => 'station_setting'],
        // 外观设置
        'menu-template' => ['page' => 'template.php'],
        // 插件管理
        'menu-plugin-all' => ['page' => 'plugin.php', 'filter' => ''],
        'menu-plugin-on' => ['page' => 'plugin.php', 'filter' => 'on'],
        'menu-plugin-off' => ['page' => 'plugin.php', 'filter' => 'off'],
        'menu-plugin-update' => ['page' => 'plugin.php', 'filter' => 'update'],
        // 应用商店
        'menu-store-list' => ['page' => 'store.php', 'action' => ['', 'tpl', 'plu', 'purchased', 'mine']],
        'menu-store-recharge' => ['page' => 'store.php', 'action' => 'svip'],
        // 系统管理
        'menu-setting' => ['page' => 'setting.php', 'action' => ['', 'index', 'seo', 'mail', 'api']],
        'menu-manage-account' => ['page' => 'setting.php', 'action' => 'admin_account'],
        'menu-shop' => ['page' => 'shop.php', 'action' => ['', 'index', 'index_save', 'gg', 'gg_save', 'btx', 'btx_save']],
        'menu-resources' => ['page' => 'resources.php'],
        'menu-upgrade' => ['page' => 'upgrade.php'],
        'menu-calibrate' => ['page' => 'calibrate.php'],
    ];
    if (!isset($itemMap[$menuItemId])) {
        return false;
    }
    $item = $itemMap[$menuItemId];
    if ($item['page'] !== $currentPage) {
        return false;
    }
    // 检查 action
    if (isset($item['action'])) {
        if (is_array($item['action'])) {
            if (!in_array($currentAction, $item['action'])) return false;
        } else {
            if ($currentAction !== $item['action']) return false;
        }
    }
    // 检查 filter
    if (isset($item['filter'])) {
        if ($currentFilter !== $item['filter']) return false;
    }
    // 检查 type
    if (isset($item['type'])) {
        if ($currentType !== $item['type']) return false;
    }
    return true;
}

function canRenderAdminMenu($menuId) {
    if (!class_exists('Admin_Permission_Service')) {
        return true;
    }
    return Admin_Permission_Service::canRenderMenu($menuId);
}

function adminMenuUrl($menuId, $fallback) {
    if (!class_exists('Admin_Permission_Service')) {
        return $fallback;
    }
    return Admin_Permission_Service::getMenuUrl($menuId, $fallback);
}

// 获取插件/模板待更新数量（在线更新已关闭，始终为0）
$pluginUpdateCount = 0;
$tplUpdateCount = 0;
?>
<!doctype html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
<!--    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name=renderer content=webkit>
    <title>管理中心 - <?= Option::get('blogname') ?></title>
    <?php
    $favicon_url = getSiteFaviconUrl(DC_URL . 'admin/views/images/favicon.ico');
    ?>
    <link rel="icon" type="image/x-icon" href="<?= $favicon_url ?>"/>
    <link rel="shortcut icon" href="<?= $favicon_url ?>"/>

    <!-- 预加载关键字体文件 -->
    <link rel="preload" href="<?= DC_URL ?>admin/views/remixicon/remixicon.woff2" as="font" type="font/woff2" crossorigin>

    <!-- 关键 CSS - 优先加载图标字体，避免闪烁 -->
    <link rel="stylesheet" type="text/css" href="<?= DC_URL ?>admin/views/remixicon/remixicon.css">
    <style>
        /* 防止图标字体加载时闪烁 */
        [class^="ri-"], [class*=" ri-"] {
            font-family: 'remixicon' !important;
            font-style: normal;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        /* 菜单容器预设样式，防止布局跳动 */
        .menu-container { min-width: 200px; }
        .menu-link i { width: 20px; display: inline-block; text-align: center; }
    </style>

    <link rel="stylesheet" href="<?= DC_URL ?>admin/views/layui-v2.11.6//layui/css/layui.css">
    <script src="<?= DC_URL ?>admin/views/layui-v2.11.6/layui/layui.js"></script>
    <script>layui.use('layer', function(){
        var _open = layui.layer.open;
        layui.layer.open = function(o){
            o = o || {};
            if((o.type === 1 || o.type === 2) && !o.skin){ o.skin = 'dc-layer-modern'; }
            return _open.call(this, o);
        };
        var _min = layui.layer.min;
        layui.layer.min = function(index, options){
            _min.call(this, index, options);
            var elem = document.getElementById('layui-layer' + index);
            if(elem){ elem.style.width = '250px'; }
        };
    });</script>

    <!-- 全局冻结列行高同步：解决 layui 表格左/右冻结列与主表行高对不齐的问题 -->
    <script>layui.use(['table', 'jquery'], function(){
        var table = layui.table;
        var $ = layui.$;

        // 同步指定表格视图中 fixed-l / fixed-r 的 tr 行高 与 主表 tr 保持一致
        function syncFixedRowHeight($view){
            if(!$view || !$view.length) return;
            var $box = $view.children('.layui-table-box');
            if(!$box.length) return;
            // 主表 tbody tr（非 fixed）
            var $mainBody = $box.children('.layui-table-body');
            var $mainRows = $mainBody.find('> table > tbody > tr');
            if(!$mainRows.length) return;
            var $fixedL = $box.children('.layui-table-fixed-l').find('> .layui-table-body > table > tbody > tr');
            var $fixedR = $box.children('.layui-table-fixed-r').find('> .layui-table-body > table > tbody > tr');
            $mainRows.each(function(i){
                var h = this.getBoundingClientRect().height;
                if(!h) return;
                if($fixedL.length) $fixedL.eq(i).css('height', h + 'px');
                if($fixedR.length) $fixedR.eq(i).css('height', h + 'px');
            });
        }

        function syncAll(){
            $('.layui-table-view').each(function(){ syncFixedRowHeight($(this)); });
        }

        var scheduleTimer;
        function scheduleSyncAll(delay){
            clearTimeout(scheduleTimer);
            scheduleTimer = setTimeout(syncAll, typeof delay === 'number' ? delay : 60);
        }

        // 为每个表格视图挂一个 ResizeObserver，监听主表 tbody 尺寸变化自动同步
        var RO = window.ResizeObserver;
        function observeView($view){
            if(!RO || !$view || !$view.length) return;
            var $box = $view.children('.layui-table-box');
            var tbody = $box.children('.layui-table-body').find('> table > tbody')[0];
            if(!tbody || tbody.__dcFixedObserved) return;
            tbody.__dcFixedObserved = true;
            var ro = new RO(function(){ scheduleSyncAll(30); });
            ro.observe(tbody);
        }
        function observeAll(){
            $('.layui-table-view').each(function(){ observeView($(this)); });
        }

        // 拦截 table.render：在用户 done 回调执行后再同步行高，不影响用户原有逻辑
        var origRender = table.render;
        table.render = function(opts){
            if(opts && typeof opts === 'object'){
                var userDone = opts.done;
                opts.done = function(){
                    var ret;
                    try { if(typeof userDone === 'function') ret = userDone.apply(this, arguments); } catch(e){ (window.console||{}).error && console.error(e); }
                    var tableId = (this && this.id) || opts.id || '';
                    setTimeout(function(){
                        var $view = tableId ? $('.layui-table-view[lay-id="' + tableId + '"]') : $();
                        if($view.length){ syncFixedRowHeight($view); observeView($view); }
                        else { syncAll(); observeAll(); }
                        // 多次兜底：异步图片 / 字体 / templet 内的延迟渲染
                        scheduleSyncAll(150);
                        setTimeout(syncAll, 400);
                        setTimeout(syncAll, 900);
                    }, 0);
                    return ret;
                };
            }
            return origRender.apply(this, arguments);
        };

        // 窗口尺寸变化时也重新同步（层叠菜单 / 响应式 / 用户拖动列宽等）
        $(window).on('resize', function(){ scheduleSyncAll(150); });

        // 图片等延迟加载内容导致高度变化时兜底同步
        // 注意：load 事件不冒泡，不能用 $(document).on('load', 'img')，必须用 capture 阶段监听
        document.addEventListener('load', function(e){
            var t = e && e.target;
            if(t && t.tagName === 'IMG'){
                // 仅在表格视图内的图片才触发，避免无关图片触发全量同步
                if(t.closest && t.closest('.layui-table-view')){ scheduleSyncAll(30); }
            }
        }, true);
    });</script>

    <!-- jquery v3.5.1 -->
    <script src="<?= DC_URL ?>admin/views/js/jquery.min.3.5.1.js"></script>



    <!-- Font Awesome -->
    <link rel="stylesheet" type="text/css" href="<?= DC_URL ?>admin/views/font-awesome-4.7.0/css/font-awesome.min.css">



    <link rel="stylesheet" href="<?= DC_URL ?>admin/views/css/style.css?v=<?= time() ?>">

    <script src="<?= DC_URL ?>admin/views/js/common.js?t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>

    <script>
        $(function(){
            var Accordion = function(el, multiple) {
                this.el = el || {};
                this.multiple = multiple || false;

                // Variables privadas
                var links = this.el.find('.link');
                // Evento
                links.on('click', {el: this.el, multiple: this.multiple}, this.dropdown)
            }

            Accordion.prototype.dropdown = function(e) {
                var $el = e.data.el;
                $this = $(this),
                    $next = $this.next();

                $this.find('.admin-arrow').toggleClass('active')

                $next.slideToggle();
                $this.parent().toggleClass('open');

                if (!e.data.multiple) {
                    $el.find('.submenu').not($next).slideUp().parent().removeClass('open');
                    $el.find('.submenu').not($next).slideUp().parent().children().children().removeClass('active');
                };
            }
            var accordion = new Accordion($('#accordion'), false);
        })
    </script>

    <script>
    // ========== 防止布局闪烁：在 DOM 解析前立即设置导航模式类名 ==========
    (function(){
        var m;
        try { m = localStorage.getItem('dc_admin_nav_mode'); } catch(e){}
        if (!m) m = 'dock';
        // 移动端（<=800px）强制使用 sidebar 展示，不修改用户桌面端偏好
        var w = window.innerWidth || document.documentElement.clientWidth || 0;
        var effective = (w > 0 && w <= 800) ? 'sidebar' : m;
        document.documentElement.className += ' pre-nav-' + effective;
    })();
    </script>
    <style>
        /* FOUC 防闪：根据 pre-nav-* 类提前隐藏不需要的元素 */
        .pre-nav-dock .menu-container { display: none !important; }
        .pre-nav-dock .pc-top-nav { left: 0 !important; }
        .pre-nav-dock .main { left: 0 !important; }
        .pre-nav-sidebar .dock-bar { display: none !important; }
    </style>

    <?php doAction('adm_head') ?>
</head>
<body id="page-top">
<?php if (defined('DC_LICENSE_TAMPERED') && DC_LICENSE_TAMPERED): ?>
<div id="tamper-warning" style="position:fixed;top:0;left:0;right:0;z-index:99999;background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff;padding:12px 20px;text-align:center;font-size:14px;box-shadow:0 2px 12px rgba(220,38,38,0.4);display:flex;align-items:center;justify-content:center;gap:10px;">
    <i class="ri-shield-keyhole-line" style="font-size:20px;flex-shrink:0;"></i>
    <span><strong>安全警告：</strong>检测到部分系统文件完整性校验失败，可能已被第三方修改或损坏，部分功能已受限。请前往
    <a href="<?= DC_URL ?>admin/calibrate.php" style="color:#fde68a;text-decoration:underline;font-weight:600;">文件校准</a> 修复，或从官方渠道重新获取原始安装包。</span>
</div>
<style>#admin-container{padding-top:48px;}</style>
<?php endif; ?>
<div id="editor-md-dialog"></div>
<div id="admin-container">
    <!-- 遮罩层 -->
    <div class="overlay"></div>


    <nav class="menu-container" id="left-menu">
        <a class="logo" href="<?= DC_URL ?>admin">
            <?= Option::get('blogname') ?>
        </a>
        <ul id="accordion" class="menu accordion">
            <?php if (canRenderAdminMenu('menu-dashboard')): ?>
            <li class="admin-menu-item<?= $currentPage === 'index.php' ? ' active' : '' ?>" id="menu-dashboard">
                <a href="<?= DC_URL ?>admin" class="menu-link"><i class="ri-dashboard-line"></i>数据中心</a>
            </li>
            <?php endif; ?>

            <?php if (canRenderAdminMenu('menu-goods')): ?>
            <li class="admin-menu-item has-submenu<?= isMenuOpen('menu-goods') ? ' open' : '' ?>" id="menu-goods">
                <div class="menu-link link">
                    <i class="ri-shopping-bag-line"></i><span>商品管理</span><i class="admin-arrow ri-arrow-right-s-line<?= isMenuOpen('menu-goods') ? ' active' : '' ?>"></i>
                </div>
                <ul class="submenu"<?= isMenuOpen('menu-goods') ? ' style="display:block;"' : '' ?>>
                    <?php if (canRenderAdminMenu('menu-goods-list')): ?>
                    <li id="menu-goods-list" class="admin-menu-item<?= isMenuItemActive('menu-goods-list') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/goods.php" class="menu-link<?= isMenuItemActive('menu-goods-list') ? ' active' : '' ?>">-&nbsp;&nbsp;商品列表</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-sort-list')): ?>
                    <li id="menu-sort-list" class="admin-menu-item<?= isMenuItemActive('menu-sort-list') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/sort.php" class="menu-link<?= isMenuItemActive('menu-sort-list') ? ' active' : '' ?>">-&nbsp;&nbsp;商品分类</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-sku-list')): ?>
                    <li id="menu-sku-list" class="admin-menu-item<?= isMenuItemActive('menu-sku-list') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/sku.php" class="menu-link<?= isMenuItemActive('menu-sku-list') ? ' active' : '' ?>">-&nbsp;&nbsp;商品规格</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-stock-list')): ?>
                    <li id="menu-stock-list" class="admin-menu-item<?= isMenuItemActive('menu-stock-list') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/stock.php" class="menu-link<?= isMenuItemActive('menu-stock-list') ? ' active' : '' ?>">-&nbsp;&nbsp;库存管理</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-goods-price-rule')): ?>
                    <li id="menu-goods-price-rule" class="admin-menu-item<?= isMenuItemActive('menu-goods-price-rule') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/price_rule.php" class="menu-link<?= isMenuItemActive('menu-goods-price-rule') ? ' active' : '' ?>">-&nbsp;&nbsp;加价规则</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-goods-recycle')): ?>
                    <li id="menu-goods-recycle" class="admin-menu-item<?= isMenuItemActive('menu-goods-recycle') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/goods_recycle.php" class="menu-link<?= isMenuItemActive('menu-goods-recycle') ? ' active' : '' ?>">-&nbsp;&nbsp;商品回收</a></li>
                    <?php endif; ?>
                    <?php doAction('adm_menu_goods'); ?>
                </ul>
            </li>
            <?php endif; ?>
            <?php if (canRenderAdminMenu('menu-order')): ?>
            <li class="admin-menu-item has-submenu<?= isMenuOpen('menu-order') ? ' open' : '' ?>" id="menu-order">
                <div class="menu-link link">
                    <i class="ri-file-list-3-line"></i>订单管理<i class="admin-arrow ri-arrow-right-s-line<?= isMenuOpen('menu-order') ? ' active' : '' ?>"></i>
                </div>
                <ul class="submenu"<?= isMenuOpen('menu-order') ? ' style="display:block;"' : '' ?>>
                    <?php if (canRenderAdminMenu('menu-order-goods')): ?>
                    <li id="menu-order-goods" class="admin-menu-item<?= isMenuItemActive('menu-order-goods') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/order.php" class="menu-link<?= isMenuItemActive('menu-order-goods') ? ' active' : '' ?>">-&nbsp;&nbsp;商品订单</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-order-recharge')): ?>
                    <li id="menu-order-recharge" class="admin-menu-item<?= isMenuItemActive('menu-order-recharge') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/order_recharge.php" class="menu-link<?= isMenuItemActive('menu-order-recharge') ? ' active' : '' ?>">-&nbsp;&nbsp;充值订单</a></li>
                    <?php endif; ?>
                    <?php doAction('adm_menu_order'); ?>
                    <?php if (canRenderAdminMenu('menu-order-recycle')): ?>
                    <li id="menu-order-recycle" class="admin-menu-item<?= isMenuItemActive('menu-order-recycle') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/order_recycle.php" class="menu-link<?= isMenuItemActive('menu-order-recycle') ? ' active' : '' ?>">-&nbsp;&nbsp;订单回收</a></li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>

            <?php if (canRenderAdminMenu('menu-user')): ?>
            <li class="admin-menu-item has-submenu<?= isMenuOpen('menu-user') ? ' open' : '' ?>" id="menu-user">
                <div class="menu-link link">
                    <i class="ri-user-line"></i>用户管理<i class="admin-arrow ri-arrow-right-s-line<?= isMenuOpen('menu-user') ? ' active' : '' ?>"></i>
                </div>
                <ul class="submenu"<?= isMenuOpen('menu-user') ? ' style="display:block;"' : '' ?>>
                    <?php if (canRenderAdminMenu('menu-user-default')): ?>
                    <li id="menu-user-default" class="admin-menu-item<?= isMenuItemActive('menu-user-default') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/user.php" class="menu-link<?= isMenuItemActive('menu-user-default') ? ' active' : '' ?>">-&nbsp;&nbsp;用户管理</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-user-recycle')): ?>
                    <li id="menu-user-recycle" class="admin-menu-item<?= isMenuItemActive('menu-user-recycle') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/user_recycle.php" class="menu-link<?= isMenuItemActive('menu-user-recycle') ? ' active' : '' ?>">-&nbsp;&nbsp;用户回收</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-user-member')): ?>
                    <li id="menu-user-member" class="admin-menu-item<?= isMenuItemActive('menu-user-member') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/member.php" class="menu-link<?= isMenuItemActive('menu-user-member') ? ' active' : '' ?>">-&nbsp;&nbsp;会员等级</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-user-withdraw')): ?>
                    <li id="menu-user-withdraw" class="admin-menu-item<?= isMenuItemActive('menu-user-withdraw') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/withdraw.php" class="menu-link<?= isMenuItemActive('menu-user-withdraw') ? ' active' : '' ?>">-&nbsp;&nbsp;提现申请</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-user-log')): ?>
                    <li id="menu-user-log" class="admin-menu-item<?= isMenuItemActive('menu-user-log') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/user_log.php" class="menu-link<?= isMenuItemActive('menu-user-log') ? ' active' : '' ?>">-&nbsp;&nbsp;用户日志</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-user-recharge-card')): ?>
                    <li id="menu-user-recharge-card" class="admin-menu-item<?= isMenuItemActive('menu-user-recharge-card') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/recharge_card.php" class="menu-link<?= isMenuItemActive('menu-user-recharge-card') ? ' active' : '' ?>">-&nbsp;&nbsp;充值卡密</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-shop')): ?>
                    <li id="menu-user-config" class="admin-menu-item<?= isMenuItemActive('menu-user-config') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/shop.php?action=user" class="menu-link<?= isMenuItemActive('menu-user-config') ? ' active' : '' ?>">-&nbsp;&nbsp;用户配置</a></li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>
            <?php if (canRenderAdminMenu('menu-station')): ?>
            <li class="admin-menu-item has-submenu<?= isMenuOpen('menu-station') ? ' open' : '' ?>" id="menu-station">
                <div class="menu-link link">
                    <i class="ri-git-branch-line"></i>分店管理<i class="admin-arrow ri-arrow-right-s-line<?= isMenuOpen('menu-station') ? ' active' : '' ?>"></i>
                </div>
                <ul class="submenu"<?= isMenuOpen('menu-station') ? ' style="display:block;"' : '' ?>>
                    <?php if (canRenderAdminMenu('menu-station-lists')): ?>
                    <li id="menu-station-lists" class="admin-menu-item<?= isMenuItemActive('menu-station-lists') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/station.php?action=lists" class="menu-link<?= isMenuItemActive('menu-station-lists') ? ' active' : '' ?>">-&nbsp;&nbsp;分店管理</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-station-level')): ?>
                    <li id="menu-station-level" class="admin-menu-item<?= isMenuItemActive('menu-station-level') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/station.php?action=level" class="menu-link<?= isMenuItemActive('menu-station-level') ? ' active' : '' ?>">-&nbsp;&nbsp;分店等级</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-shop')): ?>
                    <li id="menu-station-setting" class="admin-menu-item<?= isMenuItemActive('menu-station-setting') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/shop.php?action=station_setting" class="menu-link<?= isMenuItemActive('menu-station-setting') ? ' active' : '' ?>">-&nbsp;&nbsp;分店配置</a></li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>
            <?php if (canRenderAdminMenu('menu-blog')): ?>
            <li class="admin-menu-item has-submenu<?= isMenuOpen('menu-blog') ? ' open' : '' ?>" id="menu-blog">
                <div class="menu-link link">
                    <i class="ri-article-line"></i>博客管理<i class="admin-arrow ri-arrow-right-s-line<?= isMenuOpen('menu-blog') ? ' active' : '' ?>"></i>
                </div>
                <ul class="submenu"<?= isMenuOpen('menu-blog') ? ' style="display:block;"' : '' ?>>
                    <?php if (canRenderAdminMenu('menu-blog-list')): ?>
                    <li id="menu-blog-list" class="admin-menu-item<?= isMenuItemActive('menu-blog-list') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/article.php" class="menu-link<?= isMenuItemActive('menu-blog-list') ? ' active' : '' ?>">-&nbsp;&nbsp;文章列表</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-blog-comment')): ?>
                    <li id="menu-blog-comment" class="admin-menu-item<?= isMenuItemActive('menu-blog-comment') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/comment.php" class="menu-link<?= isMenuItemActive('menu-blog-comment') ? ' active' : '' ?>">-&nbsp;&nbsp;评论管理</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-blog-sort')): ?>
                    <li id="menu-blog-sort" class="admin-menu-item<?= isMenuItemActive('menu-blog-sort') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/sort.php?type=blog" class="menu-link<?= isMenuItemActive('menu-blog-sort') ? ' active' : '' ?>">-&nbsp;&nbsp;文章分类</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-blog-page')): ?>
                    <li id="menu-blog-page" class="admin-menu-item<?= isMenuItemActive('menu-blog-page') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/page.php" class="menu-link<?= isMenuItemActive('menu-blog-page') ? ' active' : '' ?>">-&nbsp;&nbsp;页面管理</a></li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>
            <?php if (canRenderAdminMenu('menu-appearance')): ?>
            <li class="admin-menu-item has-submenu<?= isMenuOpen('menu-appearance') ? ' open' : '' ?>" id="menu-appearance">
                <div class="menu-link link">
                    <i class="ri-palette-line"></i><span>外观设置</span><?php if($tplUpdateCount > 0): ?><span class="menu-dot"></span><?php endif; ?><i class="admin-arrow ri-arrow-right-s-line<?= isMenuOpen('menu-appearance') ? ' active' : '' ?>"></i>
                </div>
                <ul class="submenu"<?= isMenuOpen('menu-appearance') ? ' style="display:block;"' : '' ?>>
                    <?php if (canRenderAdminMenu('menu-template')): ?>
                    <li id="menu-template" class="admin-menu-item<?= isMenuItemActive('menu-template') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/template.php" class="menu-link<?= isMenuItemActive('menu-template') ? ' active' : '' ?>">-&nbsp;&nbsp;模板管理<?php if($tplUpdateCount > 0): ?><span class="menu-badge"><?= $tplUpdateCount ?></span><?php endif; ?></a></li>
                    <?php endif; ?>
                    <?php doAction('adm_menu_appearance'); ?>
                </ul>
            </li>
            <?php endif; ?>

            <?php if (canRenderAdminMenu('menu-plugin')): ?>
            <li class="admin-menu-item has-submenu<?= isMenuOpen('menu-plugin') ? ' open' : '' ?>" id="menu-plugin">
                <div class="menu-link link">
                    <i class="ri-plug-line"></i><span>插件管理</span><?php if($pluginUpdateCount > 0): ?><span class="menu-dot"></span><?php endif; ?><i class="admin-arrow ri-arrow-right-s-line<?= isMenuOpen('menu-plugin') ? ' active' : '' ?>"></i>
                </div>
                <ul class="submenu"<?= isMenuOpen('menu-plugin') ? ' style="display:block;"' : '' ?>>
                    <?php if (canRenderAdminMenu('menu-plugin-all')): ?>
                    <li id="menu-plugin-all" class="admin-menu-item<?= isMenuItemActive('menu-plugin-all') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/plugin.php" class="menu-link<?= isMenuItemActive('menu-plugin-all') ? ' active' : '' ?>">-&nbsp;&nbsp;已安装</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-plugin-on')): ?>
                    <li id="menu-plugin-on" class="admin-menu-item<?= isMenuItemActive('menu-plugin-on') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/plugin.php?filter=on" class="menu-link<?= isMenuItemActive('menu-plugin-on') ? ' active' : '' ?>">-&nbsp;&nbsp;启用中</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-plugin-off')): ?>
                    <li id="menu-plugin-off" class="admin-menu-item<?= isMenuItemActive('menu-plugin-off') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/plugin.php?filter=off" class="menu-link<?= isMenuItemActive('menu-plugin-off') ? ' active' : '' ?>">-&nbsp;&nbsp;已关闭</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-plugin-update')): ?>
                    <li id="menu-plugin-update" class="admin-menu-item<?= isMenuItemActive('menu-plugin-update') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/plugin.php?filter=update" class="menu-link<?= isMenuItemActive('menu-plugin-update') ? ' active' : '' ?>">-&nbsp;&nbsp;待更新<?php if($pluginUpdateCount > 0): ?><span class="menu-badge"><?= $pluginUpdateCount ?></span><?php endif; ?></a></li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>
            <?php /* 应用商店菜单已隐藏
            if (canRenderAdminMenu('menu-store')): ?>
            <li id="menu-store" class="admin-menu-item has-submenu<?= isMenuOpen('menu-store') ? ' open' : '' ?>">
                <div class="menu-link link">
                    <i class="ri-store-2-line"></i><span>应用商店</span><i class="admin-arrow ri-arrow-right-s-line<?= isMenuOpen('menu-store') ? ' active' : '' ?>"></i>
                </div>
                <ul class="submenu"<?= isMenuOpen('menu-store') ? ' style="display:block;"' : '' ?>>
                    <?php if (canRenderAdminMenu('menu-store-list')): ?>
                    <li id="menu-store-list" class="admin-menu-item<?= isMenuItemActive('menu-store-list') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/store.php" class="menu-link<?= isMenuItemActive('menu-store-list') ? ' active' : '' ?>">-&nbsp;&nbsp;应用列表</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-store-recharge')): ?>
                    <li id="menu-store-recharge" class="admin-menu-item<?= isMenuItemActive('menu-store-recharge') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/store.php?action=svip" class="menu-link<?= isMenuItemActive('menu-store-recharge') ? ' active' : '' ?>">-&nbsp;&nbsp;余额充值</a></li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif;
            */ ?>
            <?php if (canRenderAdminMenu('menu-system')): ?>
            <li class="admin-menu-item has-submenu<?= isMenuOpen('menu-system') ? ' open' : '' ?>" id="menu-system">
                <div class="menu-link link">
                    <i class="ri-settings-3-line"></i>系统管理<i class="admin-arrow ri-arrow-right-s-line<?= isMenuOpen('menu-system') ? ' active' : '' ?>"></i>
                </div>
                <ul class="submenu"<?= isMenuOpen('menu-system') ? ' style="display:block;"' : '' ?>>
                    <?php if (canRenderAdminMenu('menu-setting')): ?>
                    <li id="menu-setting" class="admin-menu-item<?= isMenuItemActive('menu-setting') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/setting.php" class="menu-link<?= isMenuItemActive('menu-setting') ? ' active' : '' ?>">-&nbsp;&nbsp;系统配置</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-shop')): ?>
                    <li id="menu-shop" class="admin-menu-item<?= isMenuItemActive('menu-shop') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/shop.php" class="menu-link<?= isMenuItemActive('menu-shop') ? ' active' : '' ?>">-&nbsp;&nbsp;商城配置</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-manage-account')): ?>
                    <li id="menu-manage-account" class="admin-menu-item<?= isMenuItemActive('menu-manage-account') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/setting.php?action=admin_account" class="menu-link<?= isMenuItemActive('menu-manage-account') ? ' active' : '' ?>">-&nbsp;&nbsp;后台账户</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-resources')): ?>
                    <li id="menu-resources" class="admin-menu-item<?= isMenuItemActive('menu-resources') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/resources.php" class="menu-link<?= isMenuItemActive('menu-resources') ? ' active' : '' ?>">-&nbsp;&nbsp;资源管理</a></li>
                    <?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-calibrate')): ?>
                    <li id="menu-calibrate" class="admin-menu-item<?= isMenuItemActive('menu-calibrate') ? ' active' : '' ?>"><a href="<?= DC_URL ?>admin/calibrate.php" class="menu-link<?= isMenuItemActive('menu-calibrate') ? ' active' : '' ?>">-&nbsp;&nbsp;文件校准</a></li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>

            <?php doAction('adm_menu') ?>
            <?php doAction('adm_menu_ext') ?>
            <?php if(!defined('DEMO_MODE') || DEMO_MODE != true): ?>

            <?php if (canRenderAdminMenu('menu-auth')): ?>
            <?php if (Register::isRegLocal()) : ?>
                <li id="menu-auth" class="admin-menu-item<?= $currentPage === 'auth.php' ? ' active' : '' ?>">
                    <a href="<?= DC_URL ?>admin/auth.php" class="menu-link<?= $currentPage === 'auth.php' ? ' active' : '' ?>">
                        <i class="ri-shield-check-line"></i>正版授权
                    </a>
                </li>
            <?php endif; ?>
            <?php if (!Register::isRegLocal()) : ?>
                <li id="menu-auth" class="admin-menu-item<?= $currentPage === 'auth.php' ? ' active' : '' ?>">
                    <a href="<?= DC_URL ?>admin/auth.php" class="menu-link" style="color: #4C7D71; background: #EDF2F1;">
                        <i class="ri-shield-check-line" style="color: #4C7D71;"></i>申请正版授权
                    </a>
                </li>
            <?php endif; ?>
            <?php endif; ?>

            <?php endif; ?>
        </ul>

        <div class="sidebar-bottom-switch" onclick="toggleNavMode('dock')">
            <i class="ri-layout-bottom-line"></i><span>切换为程序坞</span>
        </div>

    </nav>

    <!-- Dock 底部导航栏 -->
    <nav class="dock-bar" id="dock-bar">
        <div class="dock-items">
            <?php if (canRenderAdminMenu('menu-dashboard')): ?>
            <div class="dock-item-wrap">
                <a href="<?= DC_URL ?>admin" class="dock-item<?= $currentPage === 'index.php' ? ' active' : '' ?>"><i class="ri-dashboard-line"></i><span>数据中心</span></a>
            </div>
            <?php endif; ?>
            <?php if (canRenderAdminMenu('menu-goods')): ?>
            <div class="dock-item-wrap has-sub">
                <a href="<?= adminMenuUrl('menu-goods', DC_URL . 'admin/goods.php') ?>" class="dock-item<?= isMenuOpen('menu-goods') ? ' active' : '' ?>"><i class="ri-shopping-bag-line"></i><span>商品</span></a>
                <div class="dock-sub">
                    <?php if (canRenderAdminMenu('menu-goods-list')): ?><a href="<?= DC_URL ?>admin/goods.php" class="dock-sub-item" style="--i:0"><i class="ri-list-check-2"></i>商品列表</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-sort-list')): ?><a href="<?= DC_URL ?>admin/sort.php" class="dock-sub-item" style="--i:1"><i class="ri-folder-line"></i>商品分类</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-sku-list')): ?><a href="<?= DC_URL ?>admin/sku.php" class="dock-sub-item" style="--i:2"><i class="ri-price-tag-3-line"></i>商品规格</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-stock-list')): ?><a href="<?= DC_URL ?>admin/stock.php" class="dock-sub-item" style="--i:3"><i class="ri-inbox-line"></i>库存管理</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-goods-price-rule')): ?><a href="<?= DC_URL ?>admin/price_rule.php" class="dock-sub-item" style="--i:4"><i class="ri-funds-line"></i>加价规则</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-goods-recycle')): ?><a href="<?= DC_URL ?>admin/goods_recycle.php" class="dock-sub-item" style="--i:5"><i class="ri-delete-bin-line"></i>商品回收</a><?php endif; ?>
                    <?php doAction('adm_dock_goods'); ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if (canRenderAdminMenu('menu-order')): ?>
            <div class="dock-item-wrap has-sub">
                <a href="<?= adminMenuUrl('menu-order', DC_URL . 'admin/order.php') ?>" class="dock-item<?= isMenuOpen('menu-order') ? ' active' : '' ?>"><i class="ri-file-list-3-line"></i><span>订单</span></a>
                <div class="dock-sub">
                    <?php if (canRenderAdminMenu('menu-order-goods')): ?><a href="<?= DC_URL ?>admin/order.php" class="dock-sub-item" style="--i:0"><i class="ri-shopping-cart-line"></i>商品订单</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-order-recharge')): ?><a href="<?= DC_URL ?>admin/order_recharge.php" class="dock-sub-item" style="--i:1"><i class="ri-wallet-3-line"></i>充值订单</a><?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if (canRenderAdminMenu('menu-user')): ?>
            <div class="dock-item-wrap has-sub">
                <a href="<?= adminMenuUrl('menu-user', DC_URL . 'admin/user.php') ?>" class="dock-item<?= isMenuOpen('menu-user') ? ' active' : '' ?>"><i class="ri-user-line"></i><span>用户</span></a>
                <div class="dock-sub">
                    <?php if (canRenderAdminMenu('menu-user-default')): ?><a href="<?= DC_URL ?>admin/user.php" class="dock-sub-item" style="--i:0"><i class="ri-team-line"></i>用户管理</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-user-recycle')): ?><a href="<?= DC_URL ?>admin/user_recycle.php" class="dock-sub-item" style="--i:1"><i class="ri-delete-bin-line"></i>用户回收</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-user-member')): ?><a href="<?= DC_URL ?>admin/member.php" class="dock-sub-item" style="--i:2"><i class="ri-vip-crown-line"></i>会员等级</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-user-withdraw')): ?><a href="<?= DC_URL ?>admin/withdraw.php" class="dock-sub-item" style="--i:3"><i class="ri-money-cny-circle-line"></i>提现申请</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-user-log')): ?><a href="<?= DC_URL ?>admin/user_log.php" class="dock-sub-item" style="--i:4"><i class="ri-file-text-line"></i>用户日志</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-user-recharge-card')): ?><a href="<?= DC_URL ?>admin/recharge_card.php" class="dock-sub-item" style="--i:5"><i class="ri-bank-card-line"></i>充值卡密</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-shop')): ?><a href="<?= DC_URL ?>admin/shop.php?action=user" class="dock-sub-item" style="--i:6"><i class="ri-user-settings-line"></i>用户配置</a><?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if (canRenderAdminMenu('menu-station')): ?>
            <div class="dock-item-wrap has-sub">
                <a href="<?= adminMenuUrl('menu-station', DC_URL . 'admin/station.php?action=lists') ?>" class="dock-item<?= isMenuOpen('menu-station') ? ' active' : '' ?>"><i class="ri-git-branch-line"></i><span>分店</span></a>
                <div class="dock-sub">
                    <?php if (canRenderAdminMenu('menu-station-lists')): ?><a href="<?= DC_URL ?>admin/station.php?action=lists" class="dock-sub-item" style="--i:0"><i class="ri-list-unordered"></i>分店管理</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-station-level')): ?><a href="<?= DC_URL ?>admin/station.php?action=level" class="dock-sub-item" style="--i:1"><i class="ri-medal-line"></i>分店等级</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-shop')): ?><a href="<?= DC_URL ?>admin/shop.php?action=station_setting" class="dock-sub-item" style="--i:2"><i class="ri-settings-4-line"></i>分店配置</a><?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if (canRenderAdminMenu('menu-blog')): ?>
            <div class="dock-item-wrap has-sub">
                <a href="<?= adminMenuUrl('menu-blog', DC_URL . 'admin/article.php') ?>" class="dock-item<?= isMenuOpen('menu-blog') ? ' active' : '' ?>"><i class="ri-article-line"></i><span>博客</span></a>
                <div class="dock-sub">
                    <?php if (canRenderAdminMenu('menu-blog-list')): ?><a href="<?= DC_URL ?>admin/article.php" class="dock-sub-item" style="--i:0"><i class="ri-file-list-line"></i>文章列表</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-blog-comment')): ?><a href="<?= DC_URL ?>admin/comment.php" class="dock-sub-item" style="--i:1"><i class="ri-chat-3-line"></i>评论管理</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-blog-sort')): ?><a href="<?= DC_URL ?>admin/sort.php?type=blog" class="dock-sub-item" style="--i:2"><i class="ri-folder-line"></i>文章分类</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-blog-page')): ?><a href="<?= DC_URL ?>admin/page.php" class="dock-sub-item" style="--i:3"><i class="ri-pages-line"></i>页面管理</a><?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if (canRenderAdminMenu('menu-appearance')): ?>
            <div class="dock-item-wrap">
                <a href="<?= adminMenuUrl('menu-appearance', DC_URL . 'admin/template.php') ?>" class="dock-item<?= isMenuOpen('menu-appearance') ? ' active' : '' ?>"><i class="ri-palette-line"></i><span>外观</span><?php if($tplUpdateCount > 0): ?><span class="dock-dot"></span><?php endif; ?></a>
            </div>
            <?php endif; ?>
            <?php if (canRenderAdminMenu('menu-plugin')): ?>
            <div class="dock-item-wrap has-sub">
                <a href="<?= adminMenuUrl('menu-plugin', DC_URL . 'admin/plugin.php') ?>" class="dock-item<?= isMenuOpen('menu-plugin') ? ' active' : '' ?>"><i class="ri-plug-line"></i><span>插件</span><?php if($pluginUpdateCount > 0): ?><span class="dock-dot"></span><?php endif; ?></a>
                <div class="dock-sub">
                    <?php if (canRenderAdminMenu('menu-plugin-all')): ?><a href="<?= DC_URL ?>admin/plugin.php" class="dock-sub-item" style="--i:0"><i class="ri-apps-line"></i>已安装</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-plugin-on')): ?><a href="<?= DC_URL ?>admin/plugin.php?filter=on" class="dock-sub-item" style="--i:1"><i class="ri-checkbox-circle-line"></i>启用中</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-plugin-off')): ?><a href="<?= DC_URL ?>admin/plugin.php?filter=off" class="dock-sub-item" style="--i:2"><i class="ri-close-circle-line"></i>已关闭</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-plugin-update')): ?><a href="<?= DC_URL ?>admin/plugin.php?filter=update" class="dock-sub-item" style="--i:3"><i class="ri-refresh-line"></i>待更新<?php if($pluginUpdateCount > 0): ?><span class="dock-sub-badge"><?= $pluginUpdateCount ?></span><?php endif; ?></a><?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php /* Dock应用商店菜单已隐藏
            if (canRenderAdminMenu('menu-store')): ?>
            <div class="dock-item-wrap has-sub">
                <a href="<?= adminMenuUrl('menu-store', DC_URL . 'admin/store.php') ?>" class="dock-item<?= isMenuOpen('menu-store') ? ' active' : '' ?>"><i class="ri-store-2-line"></i><span>商店</span></a>
                <div class="dock-sub">
                    <?php if (canRenderAdminMenu('menu-store-list')): ?><a href="<?= DC_URL ?>admin/store.php" class="dock-sub-item" style="--i:0"><i class="ri-apps-2-line"></i>应用列表</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-store-recharge')): ?><a href="<?= DC_URL ?>admin/store.php?action=svip" class="dock-sub-item" style="--i:1"><i class="ri-wallet-line"></i>余额充值</a><?php endif; ?>
                </div>
            </div>
            <?php endif;
            */ ?>
            <?php if (canRenderAdminMenu('menu-system')): ?>
            <div class="dock-item-wrap has-sub">
                <a href="<?= adminMenuUrl('menu-system', DC_URL . 'admin/setting.php') ?>" class="dock-item<?= isMenuOpen('menu-system') ? ' active' : '' ?>"><i class="ri-settings-3-line"></i><span>系统</span></a>
                <div class="dock-sub">
                    <?php if (canRenderAdminMenu('menu-setting')): ?><a href="<?= DC_URL ?>admin/setting.php" class="dock-sub-item" style="--i:0"><i class="ri-settings-line"></i>系统配置</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-shop')): ?><a href="<?= DC_URL ?>admin/shop.php" class="dock-sub-item" style="--i:1"><i class="ri-store-line"></i>商城配置</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-manage-account')): ?><a href="<?= DC_URL ?>admin/setting.php?action=admin_account" class="dock-sub-item" style="--i:2"><i class="ri-shield-user-line"></i>后台账户</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-resources')): ?><a href="<?= DC_URL ?>admin/resources.php" class="dock-sub-item" style="--i:3"><i class="ri-hard-drive-2-line"></i>资源管理</a><?php endif; ?>
                    <?php if (canRenderAdminMenu('menu-calibrate')): ?><a href="<?= DC_URL ?>admin/calibrate.php" class="dock-sub-item" style="--i:4"><i class="ri-shield-check-line"></i>文件校准</a><?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if (canRenderAdminMenu('menu-auth')): ?>
            <div class="dock-item-wrap">
                <a href="<?= DC_URL ?>admin/auth.php" class="dock-item<?= $currentPage === 'auth.php' ? ' active' : '' ?>"><i class="ri-shield-check-line"></i><span>授权</span></a>
            </div>
            <?php endif; ?>
            <div class="dock-divider"></div>
            <div class="dock-item dock-toggle-btn" title="切换为侧边栏" onclick="toggleNavMode('sidebar')">
                <i class="ri-layout-left-line"></i><span>侧栏</span>
            </div>
        </div>
    </nav>

    <script>
    // ========== 自动同步插件菜单到 Dock ==========
    document.addEventListener('DOMContentLoaded', function() {
        var dockEl = document.querySelector('.dock-items');
        if (!dockEl) return;
        var divider = dockEl.querySelector('.dock-divider');

        function absUrl(h) { var a = document.createElement('a'); a.href = h; return a.href; }

        function findWrap(file) {
            var ws = dockEl.querySelectorAll('.dock-item-wrap');
            for (var i = 0; i < ws.length; i++) {
                var a = ws[i].querySelector('.dock-item');
                if (a && (a.getAttribute('href') || '').indexOf('/' + file) !== -1) return ws[i];
            }
            return null;
        }

        // 页面/插件图标映射
        var pluginIcons = {
            // 内置页面
            'template.php': 'ri-layout-4-line',
            'goods.php': 'ri-list-check-2',
            'goods_recycle.php': 'ri-delete-bin-line',
            'sort.php': 'ri-folder-line',
            'sku.php': 'ri-price-tag-3-line',
            'stock.php': 'ri-inbox-line',
            'order.php': 'ri-shopping-cart-line',
            'order_recharge.php': 'ri-wallet-3-line',
            'user.php': 'ri-team-line',
            'user_recycle.php': 'ri-delete-bin-line',
            'member.php': 'ri-vip-crown-line',
            'profit_rule.php': 'ri-price-tag-3-line',
            'single_rule.php': 'ri-funds-line',
            'withdraw.php': 'ri-money-cny-circle-line',
            'user_log.php': 'ri-file-text-line',
            'recharge_card.php': 'ri-bank-card-line',
            'article.php': 'ri-file-list-line',
            'comment.php': 'ri-chat-3-line',
            'page.php': 'ri-pages-line',
            'widgets.php': 'ri-layout-right-line',
            'link.php': 'ri-link',
            'station.php': 'ri-list-unordered',
            'setting.php': 'ri-settings-line',
            'shop.php': 'ri-store-line',
            'resources.php': 'ri-hard-drive-2-line',
            'calibrate.php': 'ri-shield-check-line',
            'upgrade.php': 'ri-upload-cloud-line',
            'store.php': 'ri-apps-2-line',
            // 插件页面
            'aftersale.php': 'ri-customer-service-2-line',
            'order_recycle.php': 'ri-delete-bin-line',
            'plugin=banner': 'ri-slideshow-3-line',
            'plugin=admin_color': 'ri-palette-line',
            'plugin=repair': 'ri-tools-line',
            'plugin=php_encrypt': 'ri-lock-2-line',
            'plugin=coupon': 'ri-coupon-3-line',
            'plugin=manual_pay': 'ri-hand-coin-line',
            'plugin=auto_clean': 'ri-timer-line'
        };
        function guessIcon(href) {
            if (!href) return 'ri-arrow-right-s-line';
            for (var key in pluginIcons) { if (href.indexOf(key) !== -1) return pluginIcons[key]; }
            return 'ri-arrow-right-s-line';
        }

        function extractInfo(link, href) {
            var clone = link.cloneNode(true);
            var bs = clone.querySelectorAll('.menu-badge');
            for (var b = 0; b < bs.length; b++) bs[b].parentNode.removeChild(bs[b]);
            var text = clone.textContent.replace(/^[\-\s\u00a0]+/, '').trim();
            var badge = link.querySelector('.menu-badge');
            var badgeH = badge ? '<span class="dock-sub-badge">' + badge.textContent + '</span>' : '';
            var ic = link.querySelector('i');
            var icCls = ic ? ic.className : guessIcon(href || link.getAttribute('href'));
            return { text: text, badge: badgeH, icon: icCls };
        }

        // 1) 同步各菜单组的插件子项
        var groups = {
            'menu-goods':'goods.php', 'menu-order':'order.php', 'menu-user':'user.php',
            'menu-blog':'article.php', 'menu-station':'station.php', 'menu-appearance':'template.php',
            'menu-plugin':'plugin.php', 'menu-store':'store.php', 'menu-system':'setting.php'
        };
        for (var mid in groups) {
            var sb = document.querySelector('#' + mid + ' > .submenu');
            if (!sb) continue;
            var wrap = findWrap(groups[mid]);
            if (!wrap) continue;
            var ds = wrap.querySelector('.dock-sub');
            var has = {};
            if (ds) {
                var di = ds.querySelectorAll('.dock-sub-item');
                for (var j = 0; j < di.length; j++) has[absUrl(di[j].getAttribute('href'))] = 1;
            }
            var links = sb.querySelectorAll('li > a.menu-link');
            for (var k = 0; k < links.length; k++) {
                var h = links[k].getAttribute('href');
                if (!h || has[absUrl(h)]) continue;
                if (!ds) { ds = document.createElement('div'); ds.className = 'dock-sub'; wrap.appendChild(ds); wrap.classList.add('has-sub'); }
                var idx = ds.querySelectorAll('.dock-sub-item').length;
                var info = extractInfo(links[k], h);
                var ni = document.createElement('a');
                ni.href = h; ni.className = 'dock-sub-item'; ni.style.setProperty('--i', idx);
                ni.innerHTML = '<i class="' + info.icon + '"></i>' + info.text + info.badge;
                ds.appendChild(ni);
            }
        }

        // 2) 同步顶层插件菜单项（adm_menu 钩子）
        var known = ['menu-dashboard','menu-goods','menu-order','menu-user','menu-blog','menu-station','menu-appearance','menu-plugin','menu-store','menu-system','menu-auth'];
        var acc = document.getElementById('accordion');
        if (!acc) return;
        var tops = acc.querySelectorAll(':scope > li.admin-menu-item');
        for (var t = 0; t < tops.length; t++) {
            var li = tops[t];
            if (!li.id || known.indexOf(li.id) !== -1) continue;
            var tl = li.querySelector('a.menu-link');
            if (!tl) continue;
            var tAbs = absUrl(tl.getAttribute('href'));
            var dup = false;
            var allA = dockEl.querySelectorAll('.dock-item');
            for (var d = 0; d < allA.length; d++) { if (absUrl(allA[d].getAttribute('href')) === tAbs) { dup = true; break; } }
            if (dup) continue;

            var nw = document.createElement('div'); nw.className = 'dock-item-wrap';
            var tHref = tl.getAttribute('href');
            var ti = extractInfo(tl, tHref);
            var txt = ti.text.length > 5 ? ti.text.substring(0, 4) : ti.text;
            var act = li.classList.contains('active') ? ' active' : '';
            nw.innerHTML = '<a href="' + tl.getAttribute('href') + '" class="dock-item' + act + '"><i class="' + ti.icon + '"></i><span>' + txt + '</span></a>';

            // 若有子菜单也同步
            var tSub = li.querySelector('.submenu');
            if (tSub) {
                var sls = tSub.querySelectorAll('li > a.menu-link');
                if (sls.length > 0) {
                    nw.classList.add('has-sub');
                    var sd = document.createElement('div'); sd.className = 'dock-sub';
                    for (var s = 0; s < sls.length; s++) {
                        var sHref = sls[s].getAttribute('href');
                        var si = extractInfo(sls[s], sHref);
                        var sa = document.createElement('a');
                        sa.href = sHref; sa.className = 'dock-sub-item'; sa.style.setProperty('--i', s);
                        sa.innerHTML = '<i class="' + si.icon + '"></i>' + si.text + si.badge;
                        sd.appendChild(sa);
                    }
                    nw.appendChild(sd);
                }
            }
            if (divider) dockEl.insertBefore(nw, divider);
        }

        // 3) 同步侧边栏小红点到 Dock 图标
        for (var mid in groups) {
            var sMenu = document.getElementById(mid);
            if (!sMenu) continue;
            var sDot = sMenu.querySelector('.menu-link .menu-dot');
            if (!sDot) continue;
            var dWrap = findWrap(groups[mid]);
            if (!dWrap) continue;
            var dItem = dWrap.querySelector('.dock-item');
            if (dItem && !dItem.querySelector('.dock-dot')) {
                var newDot = document.createElement('span');
                newDot.className = 'dock-dot';
                dItem.appendChild(newDot);
            }
        }
    });
    </script>

    <ul class="layui-nav top-nav pc-top-nav" lay-bar="disabled">


        <div class="" style="display: inline-block; line-height: 62px;">
                <span class="layui-breadcrumb" lay-separator=">">
                    <?= $br ?>
                </span>
        </div>
        <div style="float: right;">

            <!-- 线路选择 -->
            <li class="layui-nav-item" lay-unselect>
                <a href="javascript:;" id="current-line-pc">选择线路：<?= DC_LINE[CURRENT_LINE]['name'] ?> - <span class="line-ping" data-line="<?= CURRENT_LINE ?>">检测中...</span></a>
                <dl class="layui-nav-child layui-nav-child-c" id="line-dropdown-pc">
                    <?php foreach(DC_LINE as $key => $line): ?>
                    <dd><a href="javascript:;" class="line-select" data-line="<?= $key ?>" data-url="<?= $line['value'] ?>"><?= $line['name'] ?> - <span class="line-ping" data-line="<?= $key ?>">检测中...</span></a></dd>
                    <?php endforeach; ?>
                    <dd style="border-top: 1px solid rgba(0,0,0,0.05);"><a href="javascript:;" class="line-refresh"><i class="ri-refresh-line"></i> 重新检测延迟</a></dd>
                </dl>
            </li>
            <li class="layui-nav-item" lay-unselect>
                <a href="<?= DC_URL ?>" target="_blank">网站首页</a>
            </li>
            <li class="layui-nav-item" lay-unselect>
                <a href="javascript:;">
                    <img src="<?= User::getAvatar($user_cache[UID]['avatar']) ?>" class="layui-nav-img">
                    <span><?= $user_cache[UID]['name'] ?></span>
                </a>
                <dl class="layui-nav-child layui-nav-child-c">
                    <dd><a href="<?= DC_URL ?>admin/setting.php?action=admin_account">后台账户</a></dd>
                    <dd><a href="javascript:;" class="delete-cache">清除缓存</a></dd>
                    <hr>
                    <dd><a href="<?= DC_URL ?>admin/account.php?action=logout">退出登录</a></dd>
                </dl>
            </li>
        </div>

    </ul>

    <menu class="nav top-nav mobile-top-nav" style="padding-left: 0; padding-right: 0;">
        <ul class="layui-nav layui-bg-gray" lay-bar="disabled" style="width: 100%; display: flex;justify-content: space-between;">
            <li class="item nav-item" id="mobile-menu-btn" style="line-height: 62px;">
                <span style="padding: 10px;"><i class="ri-menu-line" style="font-size: 20px;"></i></span>
            </li>

            <!-- 线路选择 -->
            <li class="layui-nav-item" lay-unselect>
                <a href="javascript:;" id="current-line-mobile">选择线路：<?= DC_LINE[CURRENT_LINE]['name'] ?> - <span class="line-ping" data-line="<?= CURRENT_LINE ?>">检测中...</span></a>
                <dl class="layui-nav-child layui-nav-child-c" id="line-dropdown-mobile">
                    <?php foreach(DC_LINE as $key => $line): ?>
                        <dd><a href="javascript:;" class="line-select" data-line="<?= $key ?>" data-url="<?= $line['value'] ?>"><?= $line['name'] ?> - <span class="line-ping" data-line="<?= $key ?>">检测中...</span></a></dd>
                    <?php endforeach; ?>
                    <dd style="border-top: 1px solid rgba(0,0,0,0.05);"><a href="javascript:;" class="line-refresh"><i class="ri-refresh-line"></i> 重新检测延迟</a></dd>
                </dl>
            </li>
            <li class="layui-nav-item" lay-unselect>
                <a href="javascript:;">
                    <img src="<?= User::getAvatar($user_cache[UID]['avatar']) ?>" class="layui-nav-img">
                </a>

                <dl class="layui-nav-child layui-nav-child-c">
                    <dd><a href="<?= DC_URL ?>">网站首页</a></dd>
                    <dd><a href="<?= DC_URL ?>admin/setting.php?action=admin_account">后台账户</a></dd>
                    <dd><a href="javascript:;" class="delete-cache">清除缓存</a></dd>
                    <hr>
                    <dd><a href="<?= DC_URL ?>admin/account.php?action=logout">退出登录</a></dd>
                </dl>
            </li>


        </ul>

    </menu>

    <script>
        $('#mobile-menu-btn').click(function(){
            $('#left-menu').addClass('show');
            $('.main').addClass('show_menu');
            $('.overlay').addClass('show');
            document.body.style.overflow = 'hidden';
        })
        $('.overlay').click(function(){
            $('#left-menu').removeClass('show');
            $('.main').removeClass('show_menu');
            $('.overlay').removeClass('show');
            document.body.style.overflow = '';
        })

        // 线路延迟样式（绿/黄/红标识）
        var pingStyle = document.createElement('style');
        pingStyle.textContent = [
            '.line-ping { font-size: 12px; margin-left: 2px; opacity: 0.9; font-weight: 500; }',
            '.line-dot { display: inline-block; width: 7px; height: 7px; border-radius: 50%; margin-right: 4px; vertical-align: middle; }',
            '.line-dot.green { background: #22c55e; box-shadow: 0 0 4px #22c55e88; }',
            '.line-dot.yellow { background: #f59e0b; box-shadow: 0 0 4px #f59e0b88; }',
            '.line-dot.red { background: #ef4444; box-shadow: 0 0 4px #ef444488; }',
            '.line-dot.gray { background: #9ca3af; }',
            '.line-ping.green { color: #16a34a; }',
            '.line-ping.yellow { color: #d97706; }',
            '.line-ping.red { color: #dc2626; }',
        ].join('\n');
        document.head.appendChild(pingStyle);

        var CURRENT_LINE_INDEX = <?= CURRENT_LINE ?>;
        var LINE_NAMES = <?= json_encode(array_column(DC_LINE, 'name')) ?>;

        // 根据平均延迟(ms)返回颜色等级
        function getPingLevel(pingText) {
            if (pingText === '超时' || pingText === '失败') return 'red';
            var v = parseInt(pingText);
            if (isNaN(v) || v < 0) return 'red';
            if (v <= 500) return 'green';    // 平均 ≤500ms = 优秀
            if (v <= 1500) return 'yellow';   // 平均 ≤1500ms = 一般
            return 'red';                     // >1500ms = 较慢
        }

        // 解析延迟毫秒数
        function parsePingMs(pingText) {
            if (pingText === '超时' || pingText === '失败') return 99999;
            var v = parseInt(pingText);
            return isNaN(v) || v < 0 ? 99999 : v;
        }

        // 显示延迟数据（带颜色）
        function displayPing(lineKey, pingText) {
            var level = getPingLevel(pingText);
            var pingSpans = $('.line-ping[data-line="' + lineKey + '"]');
            pingSpans.removeClass('green yellow red').addClass(level);
            pingSpans.html('<span class="line-dot ' + level + '"></span>' + pingText);
        }

        // 线路选择功能
        $('.line-select').click(function(){
            var line = $(this).data('line');
            layer.load(2);
            $.ajax({
                type: "POST",
                url: "<?= DC_URL ?>admin/index.php?action=update_line",
                data: { line: line },
                dataType: "json",
                success: function (e) {
                    if(e.code == 400){
                        layer.msg(e.msg)
                    }else{
                        layer.msg('切换成功，正在刷新页面...', {
                            time: 500
                        }, function(){
                            try { localStorage.removeItem('dc_line_ping_cache'); } catch(e){}
                            location.reload();
                        });
                    }
                },
                error: function (xhr) {
                    try { layer.msg(JSON.parse(xhr.responseText).msg); } catch(e){ layer.msg('切换失败'); }
                },
                complete: function() { layer.closeAll('loading'); }
            });
        });

        // 线路延迟检测（服务器端 curl 测速 + 本地缓存）
        var LINE_PING_CACHE_KEY = 'dc_line_ping_cache';
        var LINE_PING_CACHE_DURATION = 3600000; // 1小时缓存

        function getLinePingCache() {
            try {
                var cache = localStorage.getItem(LINE_PING_CACHE_KEY);
                if (cache) {
                    var data = JSON.parse(cache);
                    if (data.timestamp && (Date.now() - data.timestamp) < LINE_PING_CACHE_DURATION) {
                        return data.pings;
                    }
                }
            } catch(e) {}
            return null;
        }

        function saveLinePingCache(pings) {
            try {
                localStorage.setItem(LINE_PING_CACHE_KEY, JSON.stringify({ timestamp: Date.now(), pings: pings }));
            } catch(e) {}
        }

        // 所有线路检测完后：判断是否需要提示切换（em-modal风格弹窗）
        function suggestBetterLine(pings) {
            try { if (sessionStorage.getItem('dc_line_suggested')) return; } catch(e){}

            var currentPing = parsePingMs(pings[CURRENT_LINE_INDEX] || '99999');
            var bestKey = CURRENT_LINE_INDEX;
            var bestPing = currentPing;

            for (var k in pings) {
                var p = parsePingMs(pings[k]);
                if (p < bestPing) { bestPing = p; bestKey = parseInt(k); }
            }

            if (bestKey !== CURRENT_LINE_INDEX && (currentPing > bestPing * 2 || currentPing >= 99999) && bestPing < 99999) {
                var bestName = LINE_NAMES[bestKey] || ('线路' + bestKey);
                var bestLevel = getPingLevel(pings[bestKey]);
                var currentLevel = getPingLevel(pings[CURRENT_LINE_INDEX] || '失败');
                var dotHtml = function(lv) { return '<span class="line-dot ' + lv + '" style="display:inline-block;width:8px;height:8px;"></span>'; };

                var content = '<div class="em-modal-box">';
                content += '<div class="em-modal-close-btn" onclick="layer.closeAll()"><svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></div>';
                content += '<div class="em-modal-header">';
                content += '<div class="em-modal-icon-wrapper" style="background:#FEF3C7;color:#F59E0B;"><i class="ri-signal-wifi-line" style="font-size:32px;"></i></div>';
                content += '<div class="em-modal-title" style="font-size:20px;">检测到更快的线路</div>';
                content += '<div class="em-modal-desc">当前线路响应较慢，建议切换到更快的线路以获得更好的体验</div>';
                content += '</div>';
                content += '<div class="em-modal-body"><div class="em-modal-list">';
                // 当前线路
                content += '<div class="em-modal-item" style="cursor:default;border-color:' + (currentLevel==='red'?'#FCA5A5':currentLevel==='yellow'?'#FDE68A':'#E5E7EB') + ';">';
                content += '<div class="em-item-icon" style="background:' + (currentLevel==='red'?'#FEE2E2':'#FEF3C7') + ';color:' + (currentLevel==='red'?'#DC2626':'#D97706') + ';"><i class="ri-close-circle-line"></i></div>';
                content += '<div class="em-item-content"><span class="em-item-title">当前：' + (LINE_NAMES[CURRENT_LINE_INDEX] || '') + '</span>';
                content += '<span class="em-item-sub" style="font-family:inherit;color:' + (currentLevel==='red'?'#DC2626':'#D97706') + ';">' + dotHtml(currentLevel) + ' 平均延迟 ' + (pings[CURRENT_LINE_INDEX] || '失败') + '</span></div></div>';
                // 推荐线路
                content += '<div class="em-modal-item" style="cursor:pointer;border-color:#BBF7D0;" onclick="switchToBestLine(' + bestKey + ')">';
                content += '<div class="em-item-icon" style="background:#DCFCE7;color:#16A34A;"><i class="ri-checkbox-circle-line"></i></div>';
                content += '<div class="em-item-content"><span class="em-item-title">推荐：' + bestName + '</span>';
                content += '<span class="em-item-sub" style="font-family:inherit;color:#16A34A;">' + dotHtml(bestLevel) + ' 平均延迟 ' + pings[bestKey] + '</span></div>';
                content += '<div class="em-item-action"><i class="ri-arrow-right-s-line"></i></div></div>';
                // 切换按钮
                content += '</div>';
                content += '<div style="margin-top:16px;display:flex;gap:10px;">';
                content += '<button onclick="switchToBestLine(' + bestKey + ')" style="flex:1;height:42px;border:none;border-radius:8px;background:#4C7D71;color:#fff;font-size:14px;font-weight:500;cursor:pointer;">切换到 ' + bestName + '</button>';
                content += '<button onclick="layer.closeAll()" style="width:100px;height:42px;border:1px solid #E5E7EB;border-radius:8px;background:#fff;color:#6B7280;font-size:14px;cursor:pointer;">暂不</button>';
                content += '</div></div></div>';

                var isMobile = window.innerWidth < 640;
                layer.open({
                    type: 1, title: false, closeBtn: 0, shadeClose: true,
                    area: isMobile ? ['90%', 'auto'] : ['420px', 'auto'],
                    skin: 'em-modal-skin', btn: false, content: content
                });

                try { sessionStorage.setItem('dc_line_suggested', '1'); } catch(e){}
            }
        }

        // 切换到最佳线路
        window.switchToBestLine = function(lineKey) {
            layer.closeAll();
            layer.load(2);
            $.post("<?= DC_URL ?>admin/index.php?action=update_line", { line: lineKey }, function() {
                try { localStorage.removeItem('dc_line_ping_cache'); } catch(ex){}
                location.reload();
            }, 'json').always(function(){ layer.closeAll('loading'); });
        };

        // 服务器端检测所有线路延迟（并行逐条检测）
        function checkAllLinesPing(forceRefresh) {
            var cachedPings = getLinePingCache();

            if (cachedPings && !forceRefresh) {
                for (var lineKey in cachedPings) {
                    displayPing(lineKey, cachedPings[lineKey]);
                }
                suggestBetterLine(cachedPings);
                return;
            }

            // 并行逐条调用单条检测接口
            var newPings = {};
            var done = 0;
            var total = LINE_NAMES.length;

            for (var i = 0; i < total; i++) {
                (function(k) {
                    $.ajax({
                        url: '<?= DC_URL ?>admin/index.php?action=ping_single_line&line=' + k,
                        type: 'GET', dataType: 'json', timeout: 5000,
                        success: function(res) {
                            if (res.code == 200 && res.data && res.data[k]) {
                                var item = res.data[k];
                                var pingText = item.status === 'ok' ? item.avg_ms + 'ms' : '超时';
                                newPings[k] = pingText;
                                displayPing(k, pingText);
                            }
                        },
                        error: function() {
                            newPings[k] = '超时';
                            displayPing(k, '超时');
                        },
                        complete: function() {
                            done++;
                            if (done >= total) {
                                saveLinePingCache(newPings);
                                suggestBetterLine(newPings);
                            }
                        }
                    });
                })(i);
            }
        }

        $(function(){ checkAllLinesPing(false); });

        $('.line-refresh').click(function(e){
            e.stopPropagation();
            $('.line-ping').html('<span class="line-dot gray"></span>检测中...');
            try { sessionStorage.removeItem('dc_line_suggested'); } catch(ex){}
            checkAllLinesPing(true);
            layer.msg('正在从服务器检测线路延迟...');
        });

        $('.delete-cache').click(function(){
            layer.load(2);
            $.ajax({
                type: "POST",
                url: "<?= DC_URL ?>admin/index.php?action=delete_cache",
                data: { type: 'cache' },
                dataType: "json",
                success: function (e) {
                    if(e.code == 400){
                        layer.msg(e.msg)
                    }else{
                        layer.msg('缓存删除成功');
                        setTimeout(function(){ location.reload(); }, 800);
                    }

                },
                error: function (xhr) {
                    layer.msg(JSON.parse(xhr.responseText).msg);
                },
                complete: function(xhr, textStatus) {
                    layer.closeAll('loading');
                }
            });
        })

        // ========== 导航模式切换（Dock / Sidebar） ==========
        var NAV_MODE_KEY = 'dc_admin_nav_mode';
        var NAV_MOBILE_BP = 800;

        // 用户持久化偏好（桌面端默认 dock）
        function getStoredNavMode() {
            try { return localStorage.getItem(NAV_MODE_KEY) || 'dock'; } catch(e) { return 'dock'; }
        }
        function setStoredNavMode(mode){
            try { localStorage.setItem(NAV_MODE_KEY, mode); } catch(e) {}
        }
        function isMobileViewport(){
            var w = window.innerWidth || document.documentElement.clientWidth || 0;
            return w > 0 && w <= NAV_MOBILE_BP;
        }
        // 实际应用的模式：移动端一律 sidebar，桌面端跟随持久化偏好
        function getEffectiveNavMode(){
            return isMobileViewport() ? 'sidebar' : getStoredNavMode();
        }
        // 保持对外兼容
        window.getNavMode = getStoredNavMode;

        function renderNavMode(effective){
            var $container = $('#admin-container');
            var $icon = $('#nav-mode-icon');
            if (effective === 'dock') {
                $container.addClass('nav-dock-mode').removeClass('nav-sidebar-mode');
                if ($icon.length) {
                    $icon.removeClass('ri-layout-bottom-line').addClass('ri-layout-left-line');
                    $('#nav-mode-toggle').attr('title', '切换为侧边栏');
                }
            } else {
                $container.removeClass('nav-dock-mode').addClass('nav-sidebar-mode');
                if ($icon.length) {
                    $icon.removeClass('ri-layout-left-line').addClass('ri-layout-bottom-line');
                    $('#nav-mode-toggle').attr('title', '切换为程序坞');
                }
            }
            // 移除 FOUC 防闪类
            $('html').removeClass('pre-nav-dock pre-nav-sidebar');
            // 切换后自动刷新布局（layui 表格、echarts 等依赖容器宽度的组件）
            setTimeout(function() {
                window.dispatchEvent(new Event('resize'));
                if (window.layui && layui.table) {
                    try { layui.table.resize(); } catch(ex) {}
                }
            }, 50);
        }

        // mode: 'dock' | 'sidebar'；save: 是否持久化为桌面偏好
        function applyNavMode(mode, save){
            if (save !== false && (mode === 'dock' || mode === 'sidebar')) {
                setStoredNavMode(mode);
            }
            // 移动端强制 sidebar，保存偏好但不渲染成 dock
            renderNavMode(isMobileViewport() ? 'sidebar' : (mode === 'sidebar' ? 'sidebar' : 'dock'));
        }

        window.toggleNavMode = function(mode) {
            var current = getStoredNavMode();
            var next = mode || (current === 'dock' ? 'sidebar' : 'dock');
            applyNavMode(next, true);
        };

        // 顶部栏切换按钮点击
        $('#nav-mode-toggle').click(function(e) {
            e.preventDefault();
            toggleNavMode();
        });

        // 窗口尺寸跨越移动端断点时自动重排（桌面<->移动）
        var lastMobile = isMobileViewport();
        var navResizeTimer;
        $(window).on('resize', function(){
            clearTimeout(navResizeTimer);
            navResizeTimer = setTimeout(function(){
                var nowMobile = isMobileViewport();
                if (nowMobile !== lastMobile) {
                    lastMobile = nowMobile;
                    applyNavMode(getStoredNavMode(), false);
                }
            }, 120);
        });

        // 初始化应用（按当前视口决定 effective）
        applyNavMode(getStoredNavMode(), false);

    </script>




    <div id="" class="main">






        <div id="admin-content">

<div id="admin-content-body">
<?php if (Register::isDemoSite()): ?>
<div style="background:#FF9800;color:#fff;padding:10px 20px;margin-bottom:15px;border-radius:6px;text-align:center;font-size:13px;">
    <i class="ri-information-line"></i>
    <span>当前为演示站点，仅供参观体验</span>
</div>
<?php endif; ?>
