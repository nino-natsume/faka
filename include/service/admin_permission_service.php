<?php
class Admin_Permission_Service {

    private static $currentLoaded = false;
    private static $currentRestricted = false;
    private static $currentGroup = [];
    private static $currentAllowedKeys = null;

    public static function getMenuTree() {
        $tree = [
            'menu-dashboard' => [
                'label' => '数据中心',
                'url' => 'admin/',
                'routes' => [
                    ['page' => 'index.php'],
                    ['page' => 'ajax.php', 'action' => 'delete_install_file'],
                ],
            ],
            'menu-goods' => [
                'label' => '商品管理',
                'children' => [
                    'menu-goods-list' => [
                        'label' => '商品列表',
                        'url' => 'admin/goods.php',
                        'routes' => [
                            ['page' => 'goods.php'],
                            ['page' => 'goods_save.php'],
                            ['page' => 'profit_rule.php'],
                            ['page' => 'single_rule.php'],
                        ],
                    ],
                    'menu-sort-list' => [
                        'label' => '商品分类',
                        'url' => 'admin/sort.php',
                        'routes' => [
                            ['page' => 'sort.php', 'type_not' => 'blog'],
                        ],
                    ],
                    'menu-sku-list' => [
                        'label' => '商品规格',
                        'url' => 'admin/sku.php',
                        'routes' => [
                            ['page' => 'sku.php'],
                        ],
                    ],
                    'menu-stock-list' => [
                        'label' => '库存管理',
                        'url' => 'admin/stock.php',
                        'routes' => [
                            ['page' => 'stock.php'],
                        ],
                    ],
                    'menu-goods-price-rule' => [
                        'label' => '加价规则',
                        'url' => 'admin/price_rule.php',
                        'routes' => [
                            ['page' => 'price_rule.php'],
                        ],
                    ],
                    'menu-goods-recycle' => [
                        'label' => '商品回收',
                        'url' => 'admin/goods_recycle.php',
                        'routes' => [
                            ['page' => 'goods_recycle.php'],
                        ],
                    ],
                ],
            ],
            'menu-order' => [
                'label' => '订单管理',
                'children' => [
                    'menu-order-goods' => [
                        'label' => '商品订单',
                        'url' => 'admin/order.php',
                        'routes' => [
                            ['page' => 'order.php'],
                            ['page' => 'order_recycle.php'],
                        ],
                    ],
                    'menu-order-recharge' => [
                        'label' => '充值订单',
                        'url' => 'admin/order_recharge.php',
                        'routes' => [
                            ['page' => 'order_recharge.php'],
                        ],
                    ],
                ],
            ],
            'menu-user' => [
                'label' => '用户管理',
                'children' => [
                    'menu-user-default' => [
                        'label' => '用户管理',
                        'url' => 'admin/user.php',
                        'routes' => [
                            ['page' => 'user.php'],
                        ],
                    ],
                    'menu-user-recycle' => [
                        'label' => '用户回收',
                        'url' => 'admin/user_recycle.php',
                        'routes' => [
                            ['page' => 'user_recycle.php'],
                        ],
                    ],
                    'menu-user-member' => [
                        'label' => '会员等级',
                        'url' => 'admin/member.php',
                        'routes' => [
                            ['page' => 'member.php'],
                        ],
                    ],
                    'menu-user-withdraw' => [
                        'label' => '提现申请',
                        'url' => 'admin/withdraw.php',
                        'routes' => [
                            ['page' => 'withdraw.php'],
                        ],
                    ],
                    'menu-user-log' => [
                        'label' => '用户日志',
                        'url' => 'admin/user_log.php',
                        'routes' => [
                            ['page' => 'user_log.php'],
                        ],
                    ],
                    'menu-user-recharge-card' => [
                        'label' => '充值卡密',
                        'url' => 'admin/recharge_card.php',
                        'routes' => [
                            ['page' => 'recharge_card.php'],
                        ],
                    ],
                ],
            ],
            'menu-blog' => [
                'label' => '博客管理',
                'children' => [
                    'menu-blog-list' => [
                        'label' => '文章列表',
                        'url' => 'admin/article.php',
                        'routes' => [
                            ['page' => 'article.php'],
                            ['page' => 'article_save.php'],
                            ['page' => 'twitter.php'],
                            ['page' => 'media.php'],
                            ['page' => 'plugin_user.php'],
                        ],
                    ],
                    'menu-blog-comment' => [
                        'label' => '评论管理',
                        'url' => 'admin/comment.php',
                        'routes' => [
                            ['page' => 'comment.php'],
                        ],
                    ],
                    'menu-blog-sort' => [
                        'label' => '文章分类',
                        'url' => 'admin/sort.php?type=blog',
                        'routes' => [
                            ['page' => 'sort.php', 'type' => 'blog'],
                        ],
                    ],
                    'menu-blog-page' => [
                        'label' => '页面管理',
                        'url' => 'admin/page.php',
                        'routes' => [
                            ['page' => 'page.php'],
                        ],
                    ],
                    'menu-blog-widgets' => [
                        'label' => '边栏管理',
                        'url' => 'admin/widgets.php',
                        'routes' => [
                            ['page' => 'widgets.php'],
                        ],
                    ],
                    'menu-blog-link' => [
                        'label' => '友情链接',
                        'url' => 'admin/link.php',
                        'routes' => [
                            ['page' => 'link.php'],
                        ],
                    ],
                ],
            ],
            'menu-station' => [
                'label' => '分店管理',
                'children' => [
                    'menu-station-lists' => [
                        'label' => '分店管理',
                        'url' => 'admin/station.php?action=lists',
                        'routes' => [
                            ['page' => 'station.php', 'action' => ['lists', 'save', 'edit', 'open', 'domain', 'bind_domain', 'unbind_domain']],
                        ],
                    ],
                    'menu-station-level' => [
                        'label' => '分店等级',
                        'url' => 'admin/station.php?action=level',
                        'routes' => [
                            ['page' => 'station.php', 'action' => ['level', 'level_save', 'level_add', 'level_edit', 'level_delete']],
                        ],
                    ],
                ],
            ],
            'menu-appearance' => [
                'label' => '外观设置',
                'children' => [
                    'menu-template' => [
                        'label' => '模板管理',
                        'url' => 'admin/template.php',
                        'routes' => [
                            ['page' => 'template.php'],
                            ['page' => 'blog_navbar.php', 'action' => ['list', 'save_list', 'add', 'add_sort', 'add_page']],
                        ],
                    ],
                ],
            ],
            'menu-plugin' => [
                'label' => '插件管理',
                'children' => [
                    'menu-plugin-all' => [
                        'label' => '已安装',
                        'url' => 'admin/plugin.php',
                        'routes' => [
                            ['page' => 'plugin.php', 'filter' => ''],
                            ['page' => 'plugin.php', 'action' => ['setting_page']],
                        ],
                    ],
                    'menu-plugin-on' => [
                        'label' => '启用中',
                        'url' => 'admin/plugin.php?filter=on',
                        'routes' => [
                            ['page' => 'plugin.php', 'filter' => 'on'],
                        ],
                    ],
                    'menu-plugin-off' => [
                        'label' => '已关闭',
                        'url' => 'admin/plugin.php?filter=off',
                        'routes' => [
                            ['page' => 'plugin.php', 'filter' => 'off'],
                        ],
                    ],
                    'menu-plugin-update' => [
                        'label' => '待更新',
                        'url' => 'admin/plugin.php?filter=update',
                        'routes' => [
                            ['page' => 'plugin.php', 'filter' => 'update'],
                        ],
                    ],
                ],
            ],
            'menu-store' => [
                'label' => '应用商店',
                'children' => [
                    'menu-store-list' => [
                        'label' => '应用列表',
                        'url' => 'admin/store.php',
                        'routes' => [
                            ['page' => 'store.php', 'action' => ['', 'tpl', 'plu', 'purchased', 'mine']],
                        ],
                    ],
                    'menu-store-recharge' => [
                        'label' => '余额充值',
                        'url' => 'admin/store.php?action=svip',
                        'routes' => [
                            ['page' => 'store.php', 'action' => 'svip'],
                        ],
                    ],
                ],
            ],
            'menu-system' => [
                'label' => '系统管理',
                'children' => [
                    'menu-setting' => [
                        'label' => '系统配置',
                        'url' => 'admin/setting.php',
                        'routes' => [
                            ['page' => 'setting.php', 'action' => ['', 'index', 'seo', 'mail', 'api', 'save', 'index_save', 'seo_save', 'mail_save', 'api_save', 'api_reset']],
                        ],
                    ],
                    'menu-shop' => [
                        'label' => '商城配置',
                        'url' => 'admin/shop.php',
                        'routes' => [
                            ['page' => 'shop.php'],
                        ],
                    ],
                    'menu-manage-account' => [
                        'label' => '后台账户',
                        'url' => 'admin/setting.php?action=admin_account',
                        'routes' => [
                            ['page' => 'setting.php', 'action' => 'admin_account'],
                            ['page' => 'setting.php', 'action_prefix' => 'admin_account_'],
                            ['page' => 'setting.php', 'action_prefix' => 'admin_group_'],
                        ],
                    ],
                    'menu-resources' => [
                        'label' => '资源管理',
                        'url' => 'admin/resources.php',
                        'routes' => [
                            ['page' => 'resources.php'],
                            ['page' => 'ajax.php', 'action' => ['resource_delete', 'resource_batch_delete', 'resource_delete_dir']],
                        ],
                    ],
                    'menu-calibrate' => [
                        'label' => '文件校准',
                        'url' => 'admin/calibrate.php',
                        'routes' => [
                            ['page' => 'calibrate.php'],
                            ['page' => 'ajax.php', 'action' => ['calibrate_files', 'restore_calibrate_backup', 'delete_calibrate_backup']],
                        ],
                    ],
                    'menu-upgrade' => [
                        'label' => '系统升级',
                        'url' => 'admin/upgrade.php',
                        'routes' => [
                            ['page' => 'upgrade.php'],
                            ['page' => 'ajax.php', 'action' => ['check_update', 'download_upgrade', 'extract_upgrade', 'execute_upgrade_sql']],
                        ],
                    ],
                ],
            ],
            'menu-auth' => [
                'label' => '正版授权',
                'url' => 'admin/auth.php',
                'routes' => [
                    ['page' => 'auth.php'],
                ],
            ],
        ];
        return self::appendOptionalPluginMenus($tree);
    }

    private static function appendOptionalPluginMenus($tree) {
        $pluginMenus = [
            [
                'file' => DC_ROOT . '/content/plugins/aftersale/aftersale.php',
                'parent' => 'menu-order',
                'id' => 'menu-order-aftersale',
                'label' => '售后订单',
                'url' => 'admin/aftersale.php',
                'routes' => [
                    ['page' => 'aftersale.php'],
                ],
            ],
            [
                'file' => DC_ROOT . '/content/plugins/banner/banner.php',
                'parent' => 'menu-appearance',
                'id' => 'menu-banner',
                'label' => '轮播管理',
                'url' => 'admin/plugin.php?plugin=banner&action=manage',
                'routes' => [
                    ['page' => 'plugin.php', 'action' => 'manage', 'plugin' => 'banner'],
                ],
            ],
            [
                'file' => DC_ROOT . '/content/plugins/admin_color/admin_color.php',
                'parent' => 'menu-appearance',
                'id' => 'menu-admin-color',
                'label' => '后台配色',
                'url' => 'admin/plugin.php?plugin=admin_color&action=setting_page&type=admin',
                'routes' => [
                    ['page' => 'plugin.php', 'action' => 'setting_page', 'plugin' => 'admin_color'],
                ],
            ],
            [
                'file' => DC_ROOT . '/content/plugins/repair/repair.php',
                'parent' => '',
                'id' => 'menu-repair',
                'label' => '系统工具',
                'url' => 'admin/plugin.php?action=setting_page&plugin=repair&type=admin',
                'routes' => [
                    ['page' => 'plugin.php', 'action' => 'setting_page', 'plugin' => 'repair'],
                ],
            ],
        ];
        foreach ($pluginMenus as $pluginMenu) {
            if (!is_file($pluginMenu['file'])) {
                continue;
            }
            $menuData = [
                'label' => $pluginMenu['label'],
                'url' => $pluginMenu['url'],
                'routes' => $pluginMenu['routes'],
            ];
            if ($pluginMenu['parent'] !== '' && isset($tree[$pluginMenu['parent']])) {
                if (empty($tree[$pluginMenu['parent']]['children']) || !is_array($tree[$pluginMenu['parent']]['children'])) {
                    $tree[$pluginMenu['parent']]['children'] = [];
                }
                $tree[$pluginMenu['parent']]['children'][$pluginMenu['id']] = $menuData;
                continue;
            }
            $tree[$pluginMenu['id']] = $menuData;
        }
        return $tree;
    }

    public static function getPermissionMenus() {
        $tree = self::getMenuTree();
        $menus = [];
        foreach ($tree as $menuId => $meta) {
            if (!empty($meta['children'])) {
                foreach ($meta['children'] as $childId => $child) {
                    $child['parent_id'] = $menuId;
                    $menus[$childId] = $child;
                }
            } else {
                $meta['parent_id'] = '';
                $menus[$menuId] = $meta;
            }
        }
        return $menus;
    }

    public static function getPermissionGroups() {
        $tree = self::getMenuTree();
        $groups = [];
        foreach ($tree as $menuId => $meta) {
            if (!empty($meta['children'])) {
                $children = [];
                foreach ($meta['children'] as $childId => $child) {
                    $children[] = [
                        'id' => $childId,
                        'label' => $child['label'],
                    ];
                }
                $groups[] = [
                    'id' => $menuId,
                    'label' => $meta['label'],
                    'children' => $children,
                ];
            } else {
                $groups[] = [
                    'id' => $menuId,
                    'label' => $meta['label'],
                    'children' => [
                        [
                            'id' => $menuId,
                            'label' => $meta['label'],
                        ],
                    ],
                ];
            }
        }
        return $groups;
    }

    public static function getPermissionPresets() {
        $presets = [
            [
                'id' => 'finance',
                'label' => '财务',
                'permissions' => [
                    'menu-dashboard',
                    'menu-order-goods',
                    'menu-order-recharge',
                    'menu-user-withdraw',
                    'menu-user-log',
                ],
            ],
            [
                'id' => 'service',
                'label' => '客服售后',
                'permissions' => [
                    'menu-dashboard',
                    'menu-order-goods',
                    'menu-order-aftersale',
                    'menu-user-log',
                ],
            ],
            [
                'id' => 'goods',
                'label' => '商品运营',
                'permissions' => [
                    'menu-dashboard',
                    'menu-goods-list',
                    'menu-sort-list',
                    'menu-sku-list',
                    'menu-stock-list',
                    'menu-goods-price-rule',
                    'menu-goods-recycle',
                ],
            ],
            [
                'id' => 'content',
                'label' => '内容运营',
                'permissions' => [
                    'menu-dashboard',
                    'menu-blog-list',
                    'menu-blog-comment',
                    'menu-blog-sort',
                    'menu-blog-page',
                    'menu-blog-widgets',
                    'menu-blog-link',
                    'menu-banner',
                ],
            ],
            [
                'id' => 'system',
                'label' => '系统维护',
                'permissions' => [
                    'menu-dashboard',
                    'menu-manage-account',
                    'menu-setting',
                    'menu-shop',
                    'menu-resources',
                    'menu-calibrate',
                    'menu-upgrade',
                    'menu-admin-color',
                    'menu-repair',
                ],
            ],
        ];
        $result = [];
        foreach ($presets as $preset) {
            $preset['permissions'] = self::normalizePermissions($preset['permissions']);
            if (!empty($preset['permissions'])) {
                $result[] = $preset;
            }
        }
        return $result;
    }

    public static function getAllPermissionKeys() {
        return array_keys(self::getPermissionMenus());
    }


    public static function normalizePermissions($permissions) {
        if (is_string($permissions)) {
            $decoded = json_decode($permissions, true);
            if (is_array($decoded)) {
                $permissions = $decoded;
            } else {
                $permissions = array_filter(array_map('trim', explode(',', $permissions)));
            }
        }
        if (!is_array($permissions)) {
            $permissions = [];
        }
        $allowMap = array_flip(self::getAllPermissionKeys());
        $normalized = [];
        foreach ($permissions as $permission) {
            $permission = trim((string)$permission);
            if ($permission !== '' && isset($allowMap[$permission])) {
                $normalized[$permission] = $permission;
            }
        }
        return array_values($normalized);
    }

    public static function encodePermissions($permissions) {
        return json_encode(self::normalizePermissions($permissions), JSON_UNESCAPED_UNICODE);
    }

    public static function getPermissionSummary($permissions, $limit = 4) {
        $permissions = self::normalizePermissions($permissions);
        if (empty($permissions)) {
            return '未勾选菜单';
        }
        $menus = self::getPermissionMenus();
        $labels = [];
        foreach ($permissions as $permission) {
            if (!empty($menus[$permission]['label'])) {
                $labels[] = $menus[$permission]['label'];
            }
        }
        if (count($labels) > $limit) {
            $extra = count($labels) - $limit;
            $labels = array_slice($labels, 0, $limit);
            $labels[] = '等 ' . ($limit + $extra) . ' 项';
        }
        return implode('、', $labels);
    }

    public static function canRenderMenu($menuId) {
        if (!self::isRestrictedCurrentUser()) {
            return true;
        }
        $tree = self::getMenuTree();
        $allowed = self::getCurrentAllowedMenuKeys();
        if (isset($tree[$menuId])) {
            if (!empty($tree[$menuId]['children'])) {
                foreach ($tree[$menuId]['children'] as $childId => $child) {
                    if (in_array($childId, $allowed, true)) {
                        return true;
                    }
                }
                return false;
            }
            return in_array($menuId, $allowed, true);
        }
        return in_array($menuId, $allowed, true);
    }

    public static function getMenuUrl($menuId, $fallback = '') {
        $tree = self::getMenuTree();
        if (isset($tree[$menuId])) {
            if (!empty($tree[$menuId]['children'])) {
                if (!self::isRestrictedCurrentUser()) {
                    $first = reset($tree[$menuId]['children']);
                    return !empty($first['url']) ? DC_URL . $first['url'] : $fallback;
                }
                foreach ($tree[$menuId]['children'] as $childId => $child) {
                    if (self::canRenderMenu($childId)) {
                        return DC_URL . $child['url'];
                    }
                }
                return $fallback;
            }
            return !empty($tree[$menuId]['url']) ? DC_URL . $tree[$menuId]['url'] : $fallback;
        }
        $menus = self::getPermissionMenus();
        if (!empty($menus[$menuId]['url'])) {
            return DC_URL . $menus[$menuId]['url'];
        }
        return $fallback;
    }

    public static function isRestrictedCurrentUser() {
        self::loadCurrentContext();
        return self::$currentRestricted;
    }

    public static function getCurrentGroup() {
        self::loadCurrentContext();
        return self::$currentGroup;
    }

    public static function getCurrentAllowedMenuKeys() {
        self::loadCurrentContext();
        if (!is_array(self::$currentAllowedKeys)) {
            self::$currentAllowedKeys = self::getAllPermissionKeys();
        }
        return self::$currentAllowedKeys;
    }

    public static function checkCurrentPagePermission() {
        if (!self::canAccessCurrentRequest()) {
            emMsg('你所在的用户组无法使用该功能，请联系管理员', './');
        }
    }

    public static function canAccessCurrentRequest() {
        if (!self::isRestrictedCurrentUser()) {
            return true;
        }
        $page = basename($_SERVER['PHP_SELF']);
        $action = isset($_GET['action']) ? (string)$_GET['action'] : '';
        $filter = isset($_GET['filter']) ? (string)$_GET['filter'] : '';
        $type = isset($_GET['type']) ? (string)$_GET['type'] : '';
        $plugin = isset($_GET['plugin']) ? (string)$_GET['plugin'] : '';
        if (in_array($page, ['index.php'], true)) {
            return true;
        }
        // 个人资料编辑对所有管理员开放
        if ($page === 'setting.php' && strpos($action, 'profile_') === 0) {
            return true;
        }
        $permissionKey = self::resolvePermissionKey($page, $action, $filter, $type, $plugin);
        if ($permissionKey === '') {
            return false;
        }
        return in_array($permissionKey, self::getCurrentAllowedMenuKeys(), true);
    }

    private static function resolvePermissionKey($page, $action, $filter, $type, $plugin) {
        $menus = self::getPermissionMenus();
        $bestMenuId = '';
        $bestScore = -1;
        foreach ($menus as $menuId => $meta) {
            if (empty($meta['routes']) || !is_array($meta['routes'])) {
                continue;
            }
            foreach ($meta['routes'] as $route) {
                if (self::matchRoute($route, $page, $action, $filter, $type, $plugin)) {
                    $score = self::getRoutePriorityScore($route);
                    if ($score > $bestScore) {
                        $bestScore = $score;
                        $bestMenuId = $menuId;
                    }
                }
            }
        }
        return $bestMenuId;
    }

    private static function getRoutePriorityScore($route) {
        $score = 0;
        if (!empty($route['page'])) {
            $score += 5;
        }
        if (array_key_exists('action', $route)) {
            $score += is_array($route['action']) ? 20 : 30;
        }
        if (!empty($route['action_prefix'])) {
            $score += 18;
        }
        if (array_key_exists('filter', $route)) {
            $score += 15;
        }
        if (array_key_exists('type', $route)) {
            $score += 15;
        }
        if (array_key_exists('type_not', $route)) {
            $score += 8;
        }
        if (array_key_exists('plugin', $route)) {
            $score += 100;
        }
        return $score;
    }

    private static function matchRoute($route, $page, $action, $filter, $type, $plugin) {
        if (!empty($route['page']) && $route['page'] !== $page) {
            return false;
        }
        if (array_key_exists('action', $route)) {
            if (is_array($route['action'])) {
                if (!in_array($action, $route['action'], true)) {
                    return false;
                }
            } elseif ((string)$route['action'] !== (string)$action) {
                return false;
            }
        }
        if (!empty($route['action_prefix']) && strpos($action, $route['action_prefix']) !== 0) {
            return false;
        }
        if (array_key_exists('filter', $route) && (string)$route['filter'] !== (string)$filter) {
            return false;
        }
        if (array_key_exists('type', $route) && (string)$route['type'] !== (string)$type) {
            return false;
        }
        if (array_key_exists('type_not', $route) && (string)$route['type_not'] === (string)$type) {
            return false;
        }
        if (array_key_exists('plugin', $route) && (string)$route['plugin'] !== (string)$plugin) {
            return false;
        }
        return true;
    }

    private static function loadCurrentContext() {
        if (self::$currentLoaded) {
            return;
        }
        self::$currentLoaded = true;
        self::$currentRestricted = false;
        self::$currentGroup = [];
        self::$currentAllowedKeys = self::getAllPermissionKeys();
        if (!defined('UID') || UID <= 0 || !defined('ROLE') || ROLE !== User::ROLE_ADMIN || User::isFounder()) {
            return;
        }
        new User_Model();
        $db = Database::getInstance();
        $userTable = DB_PREFIX . 'user';
        $row = $db->once_fetch_array("SELECT admin_group_id FROM {$userTable} WHERE uid=" . (int)UID . " LIMIT 1");
        $groupId = (int)($row['admin_group_id'] ?? 0);
        if ($groupId <= 0) {
            return;
        }
        $groupModel = new Admin_Group_Model();
        $group = $groupModel->getById($groupId);
        if (empty($group)) {
            return;
        }
        self::$currentRestricted = true;
        self::$currentGroup = $group;
        self::$currentAllowedKeys = self::normalizePermissions($group['menu_permissions'] ?? []);
    }
}
