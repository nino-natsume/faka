<?php
/*
Plugin Name: 系统工具箱
Version: 2.0.0
Plugin URL:
Description: 一键修复系统常见问题，包括商品库存同步、订单状态校正、缓存清理、数据完整性检查等
Author: DCSHOP
Author URL:
Ui: Layui
*/

defined('DC_ROOT') || exit('access denied!');


// 判断当前是否在修复工具箱页面
function isRepairPage() {
    $currentPage = basename($_SERVER['PHP_SELF']);
    $plugin = $_GET['plugin'] ?? '';
    $action = $_GET['action'] ?? '';
    return $currentPage === 'plugin.php' && $action === 'setting_page' && $plugin === 'repair';
}

// 添加后台修复菜单（使用 adm_menu 钩子）
function repairMenuPlugin(){
    if (function_exists('canRenderAdminMenu') && !canRenderAdminMenu('menu-repair')) {
        return;
    }
    $isActive = isRepairPage();
    $activeClass = $isActive ? ' active' : '';
    $linkActiveClass = $isActive ? ' active' : '';
    ?>
    <li id="menu-repair" class="admin-menu-item<?= $activeClass ?>">
        <a href="./plugin.php?action=setting_page&plugin=repair&type=admin" class="menu-link<?= $linkActiveClass ?>">
            <i class="ri-tools-line"></i>系统工具
        </a>
    </li>
    <?php
}
addAction('adm_menu', 'repairMenuPlugin');

// 如果当前在修复工具箱页面，关闭其他展开的菜单
function repairCloseOtherMenus() {
    if (!isRepairPage()) return;
    ?>
    <script>
    $(function(){
        // 关闭所有展开的菜单
        $('#accordion > .admin-menu-item.open').removeClass('open');
        $('#accordion > .admin-menu-item > .submenu').hide();
        $('#accordion > .admin-menu-item .admin-arrow').removeClass('active');
        // 高亮修复工具箱
        $('#menu-repair').addClass('active');
        $('#menu-repair > a').addClass('active');
    });
    </script>
    <?php
}
addAction('adm_footer', 'repairCloseOtherMenus');
