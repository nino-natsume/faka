<?php
/**
 * plugin management
 */

/**
 * @var string $action
 * @var object $CACHE
 */

require_once 'globals.php';

$plugin = Input::getStrVar("plugin");
$filter = Input::getStrVar('filter'); // on or off

// 获取待更新数量（用于角标显示）
$updateCount = 0;
$plugin_categories = [];
if (empty($action) && empty($plugin)) {
    $Plugin_Model = new Plugin_Model();
    $allPlugins = $Plugin_Model->getPlugins('');
    $check = [];
    foreach($allPlugins as $val){
        $check[] = [
            'name' => $val['Plugin'],
            'version' => $val['Version'],
            'type' => 'plugin'
        ];
    }
    $Store_Model = new Store_Model();
    $update_data = $Store_Model->checkUpdate($check);
    $updateCount = count($update_data);
     
    // 获取插件分类列表
    $plugin_categories = $Store_Model->getCategories('plugin');
}

if (empty($action) && empty($plugin)) {

    $br = '<a href="./">数据中心</a><a><cite>插件管理</cite></a>';

    include View::getAdmView('header');
    require_once(View::getAdmView('plugin'));
    include View::getAdmView('footer');
    View::output();
}

if ($action == 'index') {
    $Plugin_Model = new Plugin_Model();
    
    // 如果是待更新筛选，先获取全部插件
    $filterForModel = ($filter === 'update') ? '' : $filter;
    $plugins = $Plugin_Model->getPlugins($filterForModel);

    $check = [];
    foreach($plugins as $val){
        $check[] = [
            'name' => $val['Plugin'],
            'version' => $val['Version'],
            'type' => 'plugin'
        ];
    }

    // 使用自建授权系统检查更新，同时获取购买信息
    $Store_Model = new Store_Model();
    $checkResult = $Store_Model->checkUpdate($check, true);
    $update_data = $checkResult['updates'] ?? [];
    $purchase_info = $checkResult['purchase_info'] ?? [];
    $category_map = $checkResult['category_map'] ?? [];
 
    // 获取"主站后台"快捷入口配置
    $show_in_admin_raw = Option::get('plugin_show_in_admin');
    $show_in_admin_list = $show_in_admin_raw ? (is_array($show_in_admin_raw) ? $show_in_admin_raw : @json_decode($show_in_admin_raw, true)) : [];
    if (!is_array($show_in_admin_list)) $show_in_admin_list = [];
    
    // 引入授权验证类
    require_once DC_ROOT . '/include/lib/plugin_license.php';
    
    // 收集所有插件slug，批量强制刷新授权状态
    // 注意：如果授权服务器不可用，会静默失败并使用本地缓存，不影响已启用的插件
    $allSlugs = array_column($plugins, 'Plugin');
    if (!empty($allSlugs)) {
        $domain = getTopHost();
        PluginLicense::batchVerify($allSlugs, $domain);
    }
    
    // 自动禁用到期/被拉黑的插件
    $expiredPlugins = [];
    foreach($plugins as $val){
        if ($val['active']) {
            // 从缓存验证（batchVerify 已刷新缓存）
            if (!PluginLicense::verify($val['Plugin'])) {
                $expiredPlugins[] = $val['Plugin'] . '/' . $val['Plugin'] . '.php';
            }
        }
    }
    if (!empty($expiredPlugins)) {
        foreach($expiredPlugins as $alias) {
            $Plugin_Model->inactivePlugin($alias);
        }
        $CACHE->updateCache('options');
        // 重新获取插件列表
        $plugins = $Plugin_Model->getPlugins($filterForModel);
    }

    foreach($plugins as $key => $val){
        $plugins[$key]['update'] = 0;
        $plugins[$key]['expire_time'] = '';
        $plugins[$key]['buy_type'] = '';
        $plugins[$key]['license_status'] = ''; // 授权状态：valid/expired/blocked/unauthorized/local/trial
        $pluginKey = 'plugin:' . $val['Plugin'];
        $plugins[$key]['category_id'] = $category_map[$pluginKey] ?? ($category_map[$val['Plugin']] ?? 0);
        $plugins[$key]['show_in_admin'] = in_array($val['Plugin'], $show_in_admin_list) ? 1 : 0;
        
        // 检查更新
        foreach($update_data as $v){
            if($val['Plugin'] == $v['name']){
                $plugins[$key]['update'] = 1;
                $plugins[$key]['id'] = $v['id'];
                $plugins[$key]['new_version'] = $v['new_version'];
            }
        }
        
        // 添加购买信息（到期时间）
        if (isset($purchase_info[$pluginKey])) {
            $plugins[$key]['buy_type'] = $purchase_info[$pluginKey]['buy_type'] ?? '';
            $plugins[$key]['expire_time'] = $purchase_info[$pluginKey]['expire_time'] ?? '';
        } else if (isset($purchase_info[$val['Plugin']])) {
            $plugins[$key]['buy_type'] = $purchase_info[$val['Plugin']]['buy_type'] ?? '';
            $plugins[$key]['expire_time'] = $purchase_info[$val['Plugin']]['expire_time'] ?? '';
        }
        
        // 添加授权状态和购买类型（从缓存读取）
        $plugins[$key]['license_status'] = PluginLicense::getStatus($val['Plugin']);
        // 如果 buy_type 为空，从缓存获取
        if (empty($plugins[$key]['buy_type'])) {
            $plugins[$key]['buy_type'] = PluginLicense::getBuyType($val['Plugin']);
        }
    }
    
    // 如果是待更新筛选，只保留有更新的插件
    if ($filter === 'update') {
        $plugins = array_filter($plugins, function($p) {
            return $p['update'] == 1;
        });
        $plugins = array_values($plugins);
    }
    
    // 排序：有更新的在前 > 启用的在前 > 按名称字母排序
    usort($plugins, function($a, $b) {
        // 1. 有更新的排最前
        if ($a['update'] != $b['update']) {
            return $b['update'] - $a['update'];
        }
        // 2. 启用的排前面
        if ($a['active'] != $b['active']) {
            return $b['active'] - $a['active'];
        }
        // 3. 按名称字母排序
        return strcasecmp($a['Name'], $b['Name']);
    });

    // 分页处理
    $page = Input::getIntVar('page', 1);
    $limit = Input::getIntVar('limit', 10);
    // 直接从 $_GET 获取 keyword
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    $category_id = Input::getIntVar('category_id', 0);
 
    // 分类过滤
    if ($category_id > 0) {
        $plugins = array_filter($plugins, function($item) use ($category_id) {
            return ($item['category_id'] ?? 0) == $category_id;
        });
        $plugins = array_values($plugins);
    }
    
    // 关键词过滤
    if (!empty($keyword)) {
        $keyword = strtolower($keyword);
        $plugins = array_filter($plugins, function($item) use ($keyword) {
            $name = strtolower($item['Name'] ?? '');
            $desc = strtolower($item['Description'] ?? '');
            $author = strtolower($item['Author'] ?? '');
            $alias = strtolower($item['alias'] ?? '');
            $plugin = strtolower($item['Plugin'] ?? '');
            return strpos($name, $keyword) !== false || 
                   strpos($desc, $keyword) !== false || 
                   strpos($author, $keyword) !== false ||
                   strpos($alias, $keyword) !== false ||
                   strpos($plugin, $keyword) !== false;
        });
        $plugins = array_values($plugins); // 重新索引
    }
    
    $total = count($plugins);
    
    // 如果 limit 很大（如9999），返回全部数据（用于卡片视图）
    if ($limit < 1000) {
        $offset = ($page - 1) * $limit;
        $plugins = array_slice($plugins, $offset, $limit);
    }

    output::data($plugins, $total);
}

if($action == 'switch'){
    LoginAuth::checkToken();
    $Plugin_Model = new Plugin_Model();
    $alias = Input::postStrVar('plugin');
    $status = Input::postIntVar('status');
    
    // 启用时检查插件是否已到期
    if($status == 1){
        $pluginSlug = explode('/', $alias)[0];
        
        // 使用 PluginLicense 验证（强制刷新，确保获取最新状态）
        require_once DC_ROOT . '/include/lib/plugin_license.php';
        if (!PluginLicense::verify($pluginSlug, true)) {
            $pluginStatus = PluginLicense::getStatus($pluginSlug);
            $errorMsg = '该插件已到期，请续期后再启用';
            if ($pluginStatus === 'blocked') {
                $errorMsg = '该插件已被开发者禁用，无法启用';
            } else if ($pluginStatus === 'unauthorized') {
                $errorMsg = '您尚未购买此插件，请先购买后启用';
            } else if ($pluginStatus === 'tampered') {
                $errorMsg = '系统文件异常，检测到关键系统文件被修改，请重新安装原版系统文件或联系开发者';
            }
            header('Content-Type: application/json; charset=UTF-8');
            header("HTTP/1.1 400 Bad Request");
            die(json_encode(['code' => 400, 'msg' => $errorMsg], JSON_UNESCAPED_UNICODE));
        }
        
        $res = $Plugin_Model->activePlugin($alias);
    }else{
        if (strpos($alias, 'tpl_options') !== false) {
            output::error('禁止操作该插件');
        }
        $Plugin_Model->inactivePlugin($alias);
        $res = true;
    }
    if($res){
        $CACHE->updateCache('options');
        output::ok('操作成功');
    }else{
        output::error('操作失败');
    }
}

// 同步授权状态（强制刷新所有插件的授权缓存）
if ($action == 'sync_license') {
    LoginAuth::checkToken();
    
    require_once DC_ROOT . '/include/lib/plugin_license.php';
    
    // 清除缓存并强制重新验证
    $result = PluginLicense::syncAll();
    
    if ($result['success']) {
        output::ok('同步成功，已更新 ' . $result['count'] . ' 个插件的授权状态');
    } else {
        output::error($result['msg'] ?? '同步失败，请稍后重试');
    }
}


// Load plug-in configuration page
if (empty($action) && $plugin) {
    $setting_file = "../content/plugins/$plugin/{$plugin}_setting.php";
    
    // 检查插件是否已到期
    require_once DC_ROOT . '/include/lib/plugin_license.php';
    if (!PluginLicense::verify($plugin)) {
        include View::getAdmView('header');
        PluginLicense::showExpiredNotice($plugin, $plugin);
        include View::getAdmView('footer');
        View::output();
        exit;
    }
    
    // 检查设置文件是否存在
    if (!file_exists($setting_file)) {
        emMsg('插件设置文件不存在', './plugin.php');
    }
    
    // 检查是否使用旧版常量
    $content = @file_get_contents($setting_file, false, null, 0, 500);
    if ($content && strpos($content, "defined('EM_ROOT')") !== false) {
        emMsg('插件使用了旧版常量 EM_ROOT，请联系开发者更新', './plugin.php');
    }
    
    try {
        require_once $setting_file;
        include View::getAdmView('header');
        plugin_setting_view();
        include View::getAdmView('footer');
    } catch (Throwable $e) {
        error_log("Plugin setting error [{$plugin}]: " . $e->getMessage());
        emMsg('插件设置页面加载失败：' . htmlspecialchars($e->getMessage()), './plugin.php');
    }
}
if($action == 'setting_page'){
    $type = Input::getStrVar('type');
    if($type == 'admin'){
        $br = '<a href="./">数据中心</a><a><cite>插件扩展功能</cite></a>';
    }
    
    // 检查插件是否已到期
    require_once DC_ROOT . '/include/lib/plugin_license.php';
    if (!PluginLicense::verify($plugin)) {
        include View::getAdmView($type == 'admin' ? 'header' : 'open_head');
        PluginLicense::showExpiredNotice($plugin, $plugin);
        include View::getAdmView($type == 'admin' ? 'footer' : 'open_foot');
        exit;
    }
    
    $setting_file = "../content/plugins/$plugin/{$plugin}_setting.php";
    
    // 检查设置文件是否存在
    if (!file_exists($setting_file)) {
        emMsg('插件设置文件不存在');
    }
    
    // 检查是否使用旧版常量
    $content = @file_get_contents($setting_file, false, null, 0, 500);
    if ($content && strpos($content, "defined('EM_ROOT')") !== false) {
        emMsg('插件使用了旧版常量 EM_ROOT，请联系开发者更新');
    }
    
    try {
        require_once $setting_file;
        $pluginJsonAction = Input::getStrVar('shipping_action', '');
        if ($plugin === 'goods_physical' && in_array($pluginJsonAction, ['list', 'save', 'del', 'toggle', 'templates', 'apply_preset'], true)) {
            plugin_setting_view();
            exit;
        }
        if ($plugin === 'goods_physical' && $pluginJsonAction === 'edit') {
            include View::getAdmView('open_head');
            plugin_setting_view();
            include View::getAdmView('open_foot');
            exit;
        }
        include View::getAdmView($type == 'admin' ? 'header' : 'open_head');
        plugin_setting_view();
        include View::getAdmView($type == 'admin' ? 'footer' : 'open_foot');
    } catch (Throwable $e) {
        error_log("Plugin setting error [{$plugin}]: " . $e->getMessage());
        emMsg('插件设置页面加载失败：' . htmlspecialchars($e->getMessage()));
    }
}

// Save plug-in settings
if ($action == 'setting') {
    if (!empty($_POST)) {
        require_once "../content/plugins/$plugin/{$plugin}_setting.php";
        if (false === plugin_setting()) {
            emDirect("./plugin.php?plugin={$plugin}&error=1");
        } else {
            emDirect("./plugin.php?plugin={$plugin}&setting=1");
        }
    } else {
        emDirect("./plugin.php?plugin={$plugin}&error=1");
    }
}

// 切换插件"主站后台"快捷入口
if ($action == 'toggle_admin_shortcut') {
    LoginAuth::checkToken();
    $pluginSlug = Input::postStrVar('plugin');
    $enabled = Input::postIntVar('enabled');
 
    $show_in_admin_raw = Option::get('plugin_show_in_admin');
    $show_in_admin_list = $show_in_admin_raw ? (is_array($show_in_admin_raw) ? $show_in_admin_raw : @json_decode($show_in_admin_raw, true)) : [];
    if (!is_array($show_in_admin_list)) $show_in_admin_list = [];
 
    if ($enabled) {
        if (!in_array($pluginSlug, $show_in_admin_list)) {
            $show_in_admin_list[] = $pluginSlug;
        }
    } else {
        $show_in_admin_list = array_values(array_diff($show_in_admin_list, [$pluginSlug]));
    }
 
    Option::updateOption('plugin_show_in_admin', json_encode($show_in_admin_list, JSON_UNESCAPED_UNICODE));
    $CACHE->updateCache('options');
    output::ok('操作成功');
}
 
// 获取"主站后台"快捷入口插件列表（供悬浮图标使用）
if ($action == 'admin_shortcuts') {
    $show_in_admin_raw = Option::get('plugin_show_in_admin');
    $show_in_admin_list = $show_in_admin_raw ? (is_array($show_in_admin_raw) ? $show_in_admin_raw : @json_decode($show_in_admin_raw, true)) : [];
    if (!is_array($show_in_admin_list)) $show_in_admin_list = [];
 
    $Plugin_Model = new Plugin_Model();
    $allPlugins = $Plugin_Model->getPlugins('');
 
    $shortcuts = [];
    foreach ($allPlugins as $p) {
        if (in_array($p['Plugin'], $show_in_admin_list) && $p['Setting']) {
            $shortcuts[] = [
                'Plugin' => $p['Plugin'],
                'Name' => $p['Name'],
                'Ui' => $p['Ui'],
                'preview' => $p['preview'],
                'active' => $p['active'],
            ];
        }
    }
 
    output::data($shortcuts, count($shortcuts));
}
 

if ($action == 'del') {
    LoginAuth::checkToken();
    $plugin = Input::postStrVar('plugin');
    $Plugin_Model = new Plugin_Model();
    $Plugin_Model->inactivePlugin($plugin);
    $Plugin_Model->rmCallback($plugin);
    $path = preg_replace("/^([\w-]+)\/[\w-]+\.php$/i", "$1", $plugin);

    if ($path && true === emDeleteFile('../content/plugins/' . $path)) {
        $CACHE->updateCache('options');
        output::ok('删除成功');
    } else {
        output::ok('删除成功');
    }
}

if ($action == 'upload_zip') {
    if (defined('APP_UPLOAD_FORBID') && APP_UPLOAD_FORBID === true) {
        emMsg('系统禁止上传安装应用');
    }
    LoginAuth::checkToken();
    $zipfile = isset($_FILES['pluzip']) ? $_FILES['pluzip'] : '';

    if ($zipfile['error'] == 4) {
        emDirect("./plugin.php?error_d=1");
    }
    if ($zipfile['error'] == 1) {
        emDirect("./plugin.php?error_g=1");
    }
    if (!$zipfile || $zipfile['error'] >= 1 || empty($zipfile['tmp_name'])) {
        emMsg('插件上传失败， 错误码：' . $zipfile['error']);
    }
    if (getFileSuffix($zipfile['name']) != 'zip') {
        emDirect("./plugin.php?error_f=1");
    }

    $ret = emUnZip($zipfile['tmp_name'], '../content/plugins/', 'plugin');
    switch ($ret) {
        case 0:
            emDirect("./plugin.php?activate_install=1");
            break;
        case -1:
            emDirect("./plugin.php?error_e=1");
            break;
        case 1:
        case 2:
            emDirect("./plugin.php?error_b=1");
            break;
        case 3:
            emDirect("./plugin.php?error_c=1");
            break;
    }
}

// AJAX 上传插件
if ($action == 'upload_ajax') {
    if (defined('APP_UPLOAD_FORBID') && APP_UPLOAD_FORBID === true) {
        output::error('系统禁止上传安装应用');
    }
    LoginAuth::checkToken();
    
    $zipfile = isset($_FILES['pluzip']) ? $_FILES['pluzip'] : '';
    
    if (!$zipfile || empty($zipfile['tmp_name'])) {
        output::error('请选择插件安装包');
    }
    
    if ($zipfile['error'] == 4) {
        output::error('未选择文件');
    }
    if ($zipfile['error'] == 1 || $zipfile['error'] == 2) {
        output::error('文件过大，超出服务器限制');
    }
    if ($zipfile['error'] >= 1) {
        output::error('上传失败，错误码：' . $zipfile['error']);
    }
    
    if (getFileSuffix($zipfile['name']) != 'zip') {
        output::error('仅支持 .zip 格式的插件安装包');
    }
    
    // 检查 ZIP 文件有效性
    $zip = new ZipArchive();
    $openResult = $zip->open($zipfile['tmp_name']);
    if ($openResult !== true) {
        output::error('无效的 ZIP 文件，无法打开');
    }
    
    // 检查是否包含有效的插件文件
    $hasPluginFile = false;
    $pluginName = '';
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $filename = $zip->getNameIndex($i);
        // 查找主插件文件 (插件名/插件名.php)
        if (preg_match('/^([^\/]+)\/\1\.php$/', $filename, $matches)) {
            $hasPluginFile = true;
            $pluginName = $matches[1];
            
            // 读取插件文件内容检查头部声明
            $content = $zip->getFromIndex($i);
            if ($content && strpos($content, 'Plugin Name:') === false) {
                $zip->close();
                output::error('插件文件缺少必要的头部声明 (Plugin Name)');
            }
            break;
        }
    }
    $zip->close();
    
    if (!$hasPluginFile) {
        output::error('无效的插件安装包：未找到主插件文件（应为 插件名/插件名.php 结构）');
    }
    
    // 检查插件是否已存在
    $pluginDir = '../content/plugins/' . $pluginName;
    if (is_dir($pluginDir)) {
        // 插件已存在，将覆盖安装
    }
    
    // 解压安装
    $ret = emUnZip($zipfile['tmp_name'], '../content/plugins/', 'plugin');
    switch ($ret) {
        case 0:
            output::ok('插件安装成功');
            break;
        case -1:
            output::error('插件安装包结构不正确');
            break;
        case 1:
        case 2:
            output::error('安装失败：目录不可写，请检查 content/plugins 目录权限');
            break;
        case 3:
            output::error('安装失败：服务器缺少 Zip 扩展');
            break;
        default:
            output::error('安装失败，未知错误');
    }
}



if ($action === 'upgrade') {
    $plugin_id = Input::postStrVar('plugin_id');
    $alias = Input::postStrVar('alias');
    
    // 使用自建授权系统下载
    $Store_Model = new Store_Model();
    $url = $Store_Model->getDownloadUrl($plugin_id);
    
    $temp_file = emFetchFile($url);
    if (!$temp_file) {
        output::error('无法获取更新文件！');
    }
    
    // 检查文件有效性
    if (filesize($temp_file) < 100) {
        $content = file_get_contents($temp_file);
        @unlink($temp_file);
        output::error('更新失败：' . htmlspecialchars($content));
    }
    
    $unzip_path = '../content/plugins/';

    // ====== 覆盖安装：先移除已存在的目标目录 ======
    // Windows 下 ZipArchive::extractTo() 无法覆盖被 opcache 或其他进程锁定的文件，
    // 导致已安装的插件更新时报"目录不可写"。
    $_oldDirRenamed = '';
    $safeAlias = preg_replace('/^([\w-]+)$/i', '$1', $alias);
    $targetDir = $unzip_path . $safeAlias;
    if ($safeAlias === $alias && is_dir($targetDir)) {
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }
        @emDeleteFile($targetDir);
        if (is_dir($targetDir)) {
            $_oldDirRenamed = $targetDir . '_old_' . time();
            if (!@rename($targetDir, $_oldDirRenamed)) {
                $_oldDirRenamed = '';
            }
        }
    }

    $ret = emUnZip($temp_file, $unzip_path, 'plugin');
    @unlink($temp_file);
    switch ($ret) {
        case 0:
            $Plugin_Model = new Plugin_Model();
            $Plugin_Model->upCallback($alias);
            // 清除更新角标缓存，避免侧边栏红点残留
            @unlink(DC_ROOT . '/content/cache/update_badge_count.php');
            // 清理被 rename 的旧目录
            if ($_oldDirRenamed !== '' && is_dir($_oldDirRenamed)) {
                @emDeleteFile($_oldDirRenamed);
            }
            output::ok();
            break;
        case 1:
        case 2:
            // 解压失败时恢复旧目录
            if ($_oldDirRenamed !== '' && is_dir($_oldDirRenamed)) {
                if (!is_dir($targetDir)) {
                    @rename($_oldDirRenamed, $targetDir);
                }
            }
            output::error('更新失败，目录不可写');
            break;
        case 3:
            output::error('更新失败，缺少Zip扩展');
            break;
        default:
            if ($_oldDirRenamed !== '' && is_dir($_oldDirRenamed)) {
                if (!is_dir($targetDir)) {
                    @rename($_oldDirRenamed, $targetDir);
                }
            }
            output::error('更新失败');
    }
}


// 插件管理页面（如轮播图管理）
if ($action === 'manage') {
    $manage_file = "../content/plugins/$plugin/{$plugin}_manage.php";
    
    // 检查管理文件是否存在
    if (!file_exists($manage_file)) {
        emMsg('插件管理页面不存在', './plugin.php');
    }
    
    $br = '<a href="./">数据中心</a><a href="./plugin.php">插件管理</a><a><cite>轮播管理</cite></a>';
    
    require_once $manage_file;
    include View::getAdmView('header');
    
    $func_name = $plugin . '_manage_view';
    if (function_exists($func_name)) {
        $func_name();
    }
    
    include View::getAdmView('footer');
    View::output();
}

// 插件批量保存（如轮播图保存）
if ($action === 'save_all') {
    $save_file = "../content/plugins/$plugin/{$plugin}_manage.php";
    
    if (!file_exists($save_file)) {
        Output::error('插件文件不存在');
    }
    
    require_once $save_file;
    
    $func_name = $plugin . '_save_all';
    if (function_exists($func_name)) {
        $func_name();
    } else {
        Output::error('保存函数不存在');
    }
}
