<?php

/**
 * @var string $action
 * @var object $CACHE
 */

require_once 'globals.php';

$Template_Model = new Template_Model();
$Store_Model = new Store_Model();

function sendTemplateSettingNoCacheHeaders()
{
    if (headers_sent()) {
        return;
    }
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
}

function upgradeTemplatePackage($basePath, $alias, $pluginId)
{
    $safeAlias = preg_replace('/^([\w-]+)$/i', '$1', $alias);
    if ($safeAlias !== $alias || !is_dir($basePath . $safeAlias . '/') || !checkTemplateBootstrap($basePath, $safeAlias)) {
        output::error('模板不存在或已损坏');
    }

    $Store_Model = new Store_Model();
    $url = $Store_Model->getDownloadUrl($pluginId);
    $temp_file = emFetchFile($url);
    if (!$temp_file) {
        output::error('无法获取更新文件！');
    }

    if (filesize($temp_file) < 100) {
        $content = file_get_contents($temp_file);
        @unlink($temp_file);
        output::error('更新失败：' . htmlspecialchars($content));
    }

    // ====== 覆盖安装：先移除已存在的目标目录 ======
    // Windows 下 ZipArchive::extractTo() 无法覆盖被 opcache 或其他进程锁定的文件，
    // 导致已安装的模板更新时报"目录不可写"。
    // 策略：opcache_reset → 尝试删除 → 若删除失败则 rename 旧目录 → 解压后清理。
    $_oldDirRenamed = '';
    $targetDir = $basePath . $safeAlias;
    if (is_dir($targetDir)) {
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

    $ret = emUnZip($temp_file, $basePath, 'tpl');
    @unlink($temp_file);
    switch ($ret) {
        case 0:
            runTemplateCallback($basePath, $safeAlias, 'callback_up');
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
            // 解压失败时，如果旧目录被重命名了，尝试恢复
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
            // 解压失败时恢复旧目录
            if ($_oldDirRenamed !== '' && is_dir($_oldDirRenamed)) {
                if (!is_dir($targetDir)) {
                    @rename($_oldDirRenamed, $targetDir);
                }
            }
            output::error('更新失败');
    }
}

function buildTemplateUpdateCheckItems($templates, $type)
{
    $check = [];
    foreach ($templates as $val) {
        if (empty($val['tplfile'])) {
            continue;
        }
        $check[] = [
            'name' => $val['tplfile'],
            'version' => $val['version'] ?? '',
            'type' => $type
        ];
    }
    return $check;
}

function normalizeTemplateAppType($type)
{
    $type = trim((string)$type);
    if (in_array($type, ['tpl', 'home', 'home_template', 'station', 'station_template'], true)) {
        return 'template';
    }
    if (in_array($type, ['user', 'user_tpl'], true)) {
        return 'user_template';
    }
    if (in_array($type, ['bottom_nav', 'bottom_nav_tpl'], true)) {
        return 'bottom_nav_template';
    }
    if (in_array($type, ['blog', 'blog_tpl'], true)) {
        return 'blog_template';
    }
    return in_array($type, ['template', 'user_template', 'bottom_nav_template', 'blog_template'], true) ? $type : '';
}

function getTemplateUpdateTabCounts($Template_Model, $Store_Model)
{
    $counts = [
        'home' => 0,
        'bottom_nav' => 0,
        'user' => 0,
        'blog' => 0,
    ];

    $check = array_merge(
        buildTemplateUpdateCheckItems($Template_Model->getTemplates(), 'template'),
        buildTemplateUpdateCheckItems($Template_Model->getBottomNavTemplates(), 'bottom_nav_template'),
        buildTemplateUpdateCheckItems($Template_Model->getUserCenterTemplates(), 'user_template'),
        buildTemplateUpdateCheckItems($Template_Model->getBlogTemplates(), 'blog_template')
    );

    if (empty($check)) {
        return $counts;
    }

    $updateData = $Store_Model->checkUpdate($check);
    if (!is_array($updateData)) {
        return $counts;
    }

    foreach ($updateData as $item) {
        $type = normalizeTemplateAppType($item['type'] ?? '');
        if ($type === 'template') {
            $counts['home']++;
        } else if ($type === 'bottom_nav_template') {
            $counts['bottom_nav']++;
        } else if ($type === 'user_template') {
            $counts['user']++;
        } else if ($type === 'blog_template') {
            $counts['blog']++;
        }
    }

    return $counts;
}

if ($action === '') {

    $tab = isset($_GET['tab']) ? trim($_GET['tab']) : 'home';
    if (!in_array($tab, ['home', 'bottom_nav', 'user', 'blog', 'admin'])) $tab = 'home';

    // 获取各模板 Tab 的待更新数量，用于 Tab 角标显示
    $templateUpdateCounts = getTemplateUpdateTabCounts($Template_Model, $Store_Model);
    $homeTplUpdateCount = $templateUpdateCounts['home'] ?? 0;
    $bottomNavTplUpdateCount = $templateUpdateCounts['bottom_nav'] ?? 0;
    $userTplUpdateCount = $templateUpdateCounts['user'] ?? 0;
    $blogTplUpdateCount = $templateUpdateCounts['blog'] ?? 0;
    $tplUpdateCount = $homeTplUpdateCount + $bottomNavTplUpdateCount + $userTplUpdateCount + $blogTplUpdateCount;

    $br = '<a href="./">数据中心</a><a href="./template.php">外观设置</a><a><cite>模板主题</cite></a>';

    include View::getAdmView('header');
    require_once View::getAdmView('templates/default/template/index');
    include View::getAdmView('footer');
    View::output();
}

if($action == 'index'){
    $list = $Template_Model->getTemplates();
    $nonce_template = Option::get('nonce_templet');
    $nonce_template_tel = Option::get('nonce_templet_tel');

    // 构建检查更新的数据
    $check = [];
    foreach($list as $key => $val){
        if($nonce_template == $val['tplfile']){
            $list[$key]['switch'] = 'y';
        }else{
            $list[$key]['switch'] = 'n';
        }
        if($nonce_template_tel == $val['tplfile']){
            $list[$key]['tel_switch'] = 'y';
        }else{
            $list[$key]['tel_switch'] = 'n';
        }
        $check[] = [
            'name' => $val['tplfile'],
            'version' => $val['version'],
            'type' => 'template'
        ];
    }

    // 使用自建授权系统检查更新，同时获取购买信息
    $checkResult = $Store_Model->checkUpdate($check, true);
    $update_data = $checkResult['updates'] ?? [];
    $purchase_info = $checkResult['purchase_info'] ?? [];

    // 引入授权验证类
    require_once DC_ROOT . '/include/lib/plugin_license.php';

    // 收集所有模板slug，批量强制刷新授权状态
    // 注意：如果授权服务器不可用，会静默失败并使用本地缓存，不影响已启用的模板
    $allSlugs = array_column($list, 'tplfile');
    if (!empty($allSlugs)) {
        $domain = getTopHost();
        $licenseSlugs = array_map(function($slug) { return 'template:' . $slug; }, $allSlugs);
        PluginLicense::batchVerify($licenseSlugs, $domain);
    }

    // 自动禁用到期/被拉黑的模板
    $needUpdateCache = false;
    foreach($list as $val){
        // 检查当前启用的电脑端模板
        if ($nonce_template == $val['tplfile']) {
            if (!PluginLicense::verify('template:' . $val['tplfile'])) {
                Option::updateOption('nonce_templet', 'em_null_tpl');
                $nonce_template = 'em_null_tpl';
                $needUpdateCache = true;
            }
        }
        // 检查当前启用的手机端模板
        if ($nonce_template_tel == $val['tplfile']) {
            if (!PluginLicense::verify('template:' . $val['tplfile'])) {
                Option::updateOption('nonce_templet_tel', 'em_null_tpl');
                $nonce_template_tel = 'em_null_tpl';
                $needUpdateCache = true;
            }
        }
    }
    if ($needUpdateCache) {
        $CACHE->updateCache('options');
    }

    // 处理更新标记和授权信息
    foreach($list as $key => $val){
        // 更新开关状态（可能已被自动禁用）
        $list[$key]['switch'] = ($nonce_template == $val['tplfile']) ? 'y' : 'n';
        $list[$key]['tel_switch'] = ($nonce_template_tel == $val['tplfile']) ? 'y' : 'n';

        $list[$key]['update'] = 'n';
        $list[$key]['expire_time'] = '';
        $list[$key]['buy_type'] = '';
        $list[$key]['license_status'] = '';

        // 检查更新
        foreach($update_data as $v){
            $updateType = normalizeTemplateAppType($v['type'] ?? 'template');
            if(($v['name'] ?? '') == $val['tplfile'] && $updateType === 'template'){
                $list[$key]['update'] = 'y';
                $list[$key]['id'] = $v['id'];
                $list[$key]['new_version'] = $v['new_version'] ?? '';
            }
        }

        // 添加购买信息（到期时间）
        $purchaseKey = 'template:' . $val['tplfile'];
        if (isset($purchase_info[$purchaseKey])) {
            $list[$key]['buy_type'] = $purchase_info[$purchaseKey]['buy_type'] ?? '';
            $list[$key]['expire_time'] = $purchase_info[$purchaseKey]['expire_time'] ?? '';
        }

        // 添加授权状态和购买类型（从缓存读取）
        $licenseKey = 'template:' . $val['tplfile'];
        $list[$key]['license_status'] = PluginLicense::getStatus($licenseKey);
        // 如果 buy_type 为空，从缓存获取
        if (empty($list[$key]['buy_type'])) {
            $list[$key]['buy_type'] = PluginLicense::getBuyType($licenseKey);
        }
        if (empty($list[$key]['expire_time'])) {
            $licenseCache = Template_Model::getLicenseCache($licenseKey);
            if ($licenseCache) {
                $list[$key]['expire_time'] = $licenseCache['expire_time'] ?? '';
            }
        }
    }

    // 分页处理
    $page = Input::getIntVar('page', 1);
    $limit = Input::getIntVar('limit', 10);
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

    // 关键词过滤
    if (!empty($keyword)) {
        $keyword = strtolower($keyword);
        $list = array_filter($list, function($item) use ($keyword) {
            $name = strtolower($item['tplname'] ?? '');
            $desc = strtolower($item['tpldes'] ?? '');
            $author = strtolower($item['author'] ?? '');
            $file = strtolower($item['tplfile'] ?? '');
            return strpos($name, $keyword) !== false ||
                   strpos($desc, $keyword) !== false ||
                   strpos($author, $keyword) !== false ||
                   strpos($file, $keyword) !== false;
        });
        $list = array_values($list); // 重新索引
    }

    $total = count($list);

    // 如果 limit 很大（如9999），返回全部数据
    if ($limit < 1000) {
        $offset = ($page - 1) * $limit;
        $list = array_slice($list, $offset, $limit);
    }

    output::data($list, $total);
}

if ($action === 'use') {
    LoginAuth::checkToken();
    $tplName = trim((string)Input::postStrVar('tpl'));
    if ($tplName !== 'em_null_tpl') {
        $safeTplName = preg_replace('/^([\w-]+)$/i', '$1', $tplName);
        if ($safeTplName !== $tplName || !is_dir(TPLS_PATH . $safeTplName . '/') || !checkTemplateBootstrap(TPLS_PATH, $safeTplName)) {
            output::error('模板不存在或已损坏');
        }
        $tplName = $safeTplName;
    }

    // 启用时检查模板是否已到期（排除关闭操作）
    if ($tplName !== 'em_null_tpl') {
        require_once DC_ROOT . '/include/lib/plugin_license.php';
        $licenseKey = 'template:' . $tplName;
        if (!PluginLicense::verify($licenseKey, true)) {
            $tplStatus = PluginLicense::getStatus($licenseKey);
            $errorMsg = '该模板已到期，请续期后再启用';
            if ($tplStatus === 'blocked') {
                $errorMsg = '该模板已被开发者禁用，无法启用';
            } else if ($tplStatus === 'unauthorized') {
                $errorMsg = '您尚未购买此模板，请先购买后启用';
            }
            output::error($errorMsg);
        }
    }

    Option::updateOption('nonce_templet', $tplName);
    $CACHE->updateCache('options');
    $Template_Model->initCallback($tplName);
    output::ok();
}

if ($action === 'use_tel') {
    LoginAuth::checkToken();
    $tplName = trim((string)Input::postStrVar('tpl'));
    if ($tplName !== 'em_null_tpl') {
        $safeTplName = preg_replace('/^([\w-]+)$/i', '$1', $tplName);
        if ($safeTplName !== $tplName || !is_dir(TPLS_PATH . $safeTplName . '/') || !checkTemplateBootstrap(TPLS_PATH, $safeTplName)) {
            output::error('模板不存在或已损坏');
        }
        $tplName = $safeTplName;
    }

    // 启用时检查模板是否已到期（排除关闭操作）
    if ($tplName !== 'em_null_tpl') {
        require_once DC_ROOT . '/include/lib/plugin_license.php';
        $licenseKey = 'template:' . $tplName;
        if (!PluginLicense::verify($licenseKey, true)) {
            $tplStatus = PluginLicense::getStatus($licenseKey);
            $errorMsg = '该模板已到期，请续期后再启用';
            if ($tplStatus === 'blocked') {
                $errorMsg = '该模板已被开发者禁用，无法启用';
            } else if ($tplStatus === 'unauthorized') {
                $errorMsg = '您尚未购买此模板，请先购买后启用';
            }
            output::error($errorMsg);
        }
    }

    Option::updateOption('nonce_templet_tel', $tplName);
    $CACHE->updateCache('options');
    $Template_Model->initCallback($tplName);
    output::ok();
}

if ($action === 'del') {
    LoginAuth::checkToken();
    $tpls = Input::postStrVar('ids');
    $tpls = explode(',', $tpls);
    foreach($tpls as $val){
        $Template_Model->rmCallback($val);
        $path = preg_replace("/^([\w-]+)$/i", "$1", $val);
        emDeleteFile(TPLS_PATH . $path);
    }
    output::ok();
}

if ($action === 'upgrade') {
    $plugin_id = Input::postStrVar('plugin_id');
    $alias = Input::postStrVar('alias');
    upgradeTemplatePackage(TPLS_PATH, $alias, $plugin_id);
}

if($action == 'setting_page'){
    sendTemplateSettingNoCacheHeaders();
    $tpl = trim((string)Input::getStrVar('tpl'));
    $safeTpl = preg_replace('/^([\w-]+)$/i', '$1', $tpl);
    $settingFile = getTemplateSettingFile(TPLS_PATH, $safeTpl);
    if ($safeTpl !== $tpl || !checkTemplateBootstrap(TPLS_PATH, $safeTpl) || !loadTemplateBootstrap(TPLS_PATH, $safeTpl) || $settingFile === false) {
        emMsg('模板配置文件不存在或已损坏', './template.php?tab=home');
    }
    // 检查模板是否已到期/未授权
    require_once DC_ROOT . '/include/lib/plugin_license.php';
    $licenseKey = 'template:' . $safeTpl;
    if (!PluginLicense::verify($licenseKey)) {
        $tplDisplayName = $safeTpl;
        $bootstrapFile = getTemplateBootstrapFile(TPLS_PATH, $safeTpl);
        if ($bootstrapFile && file_exists($bootstrapFile)) {
            $headerData = @file_get_contents($bootstrapFile, false, null, 0, 2048);
            if ($headerData && preg_match('/Template\s*Name\s*:\s*(.+)/i', $headerData, $nm)) {
                $tplDisplayName = trim($nm[1]);
            }
        }
        include View::getAdmView('open_head');
        PluginLicense::showExpiredNotice($licenseKey, $tplDisplayName, 'tpl');
        include View::getAdmView('open_foot');
        exit;
    }
    include View::getAdmView('open_head');
    require_once DC_ROOT . '/include/lib/template_setting_mobile.php';
    require_once $settingFile;
    plugin_setting_view();
    include View::getAdmView('open_foot');
}
if($action == 'setting_ajax'){
    if (!LoginAuth::checkAjaxToken()) {
        output::error('安全token校验失败，请刷新页面重试');
    }
    $tpl = trim((string)Input::getStrVar('tpl'));
    $safeTpl = preg_replace('/^([\w-]+)$/i', '$1', $tpl);
    $settingFile = getTemplateSettingFile(TPLS_PATH, $safeTpl);
    if ($safeTpl !== $tpl || !checkTemplateBootstrap(TPLS_PATH, $safeTpl) || !loadTemplateBootstrap(TPLS_PATH, $safeTpl) || $settingFile === false) {
        output::error('模板配置文件不存在或已损坏');
    }
    require_once DC_ROOT . '/include/lib/plugin_license.php';
    $licenseKey = 'template:' . $safeTpl;
    if (!PluginLicense::verify($licenseKey)) {
        $tplStatus = PluginLicense::getStatus($licenseKey);
        $errorMsg = '该模板已到期，请续期后再保存配置';
        if ($tplStatus === 'blocked') {
            $errorMsg = '该模板已被开发者禁用，无法保存配置';
        } else if ($tplStatus === 'unauthorized') {
            $errorMsg = '您尚未购买此模板，请先购买后再保存配置';
        }
        output::error($errorMsg);
    }
    require_once $settingFile;
    plugin_setting($safeTpl);
}

if($action == 'bottom_nav_setting_page'){
    sendTemplateSettingNoCacheHeaders();
    $tpl = trim((string)Input::getStrVar('tpl'));
    $safeTpl = preg_replace('/^([\w-]+)$/i', '$1', $tpl);
    $settingFile = getTemplateSettingFile(BOTTOM_NAV_TPLS_PATH, $safeTpl);
    if ($safeTpl !== $tpl || !checkTemplateBootstrap(BOTTOM_NAV_TPLS_PATH, $safeTpl) || !loadTemplateBootstrap(BOTTOM_NAV_TPLS_PATH, $safeTpl) || $settingFile === false) {
        emMsg('底部导航模板配置文件不存在或已损坏', './template.php?tab=bottom_nav');
    }
    // 检查模板是否已到期/未授权
    require_once DC_ROOT . '/include/lib/plugin_license.php';
    $licenseKey = 'bottom_nav_template:' . $safeTpl;
    if (!PluginLicense::verify($licenseKey)) {
        $tplDisplayName = $safeTpl;
        $bootstrapFile = getTemplateBootstrapFile(BOTTOM_NAV_TPLS_PATH, $safeTpl);
        if ($bootstrapFile && file_exists($bootstrapFile)) {
            $headerData = @file_get_contents($bootstrapFile, false, null, 0, 2048);
            if ($headerData && preg_match('/Template\s*Name\s*:\s*(.+)/i', $headerData, $nm)) {
                $tplDisplayName = trim($nm[1]);
            }
        }
        include View::getAdmView('open_head');
        PluginLicense::showExpiredNotice($licenseKey, $tplDisplayName, 'tpl');
        include View::getAdmView('open_foot');
        exit;
    }
    include View::getAdmView('open_head');
    require_once DC_ROOT . '/include/lib/template_setting_mobile.php';
    require_once $settingFile;
    plugin_setting_view($safeTpl);
    include View::getAdmView('open_foot');
}
if($action == 'bottom_nav_setting_ajax'){
    if (!LoginAuth::checkAjaxToken()) {
        output::error('安全token校验失败，请刷新页面重试');
    }
    $tpl = trim((string)Input::getStrVar('tpl'));
    $safeTpl = preg_replace('/^([\w-]+)$/i', '$1', $tpl);
    $settingFile = getTemplateSettingFile(BOTTOM_NAV_TPLS_PATH, $safeTpl);
    if ($safeTpl !== $tpl || !checkTemplateBootstrap(BOTTOM_NAV_TPLS_PATH, $safeTpl) || !loadTemplateBootstrap(BOTTOM_NAV_TPLS_PATH, $safeTpl) || $settingFile === false) {
        output::error('底部导航模板配置文件不存在或已损坏');
    }
    require_once $settingFile;
    plugin_setting($safeTpl);
}

if($action == 'user_setting_page'){
    sendTemplateSettingNoCacheHeaders();
    $tpl = trim((string)Input::getStrVar('tpl'));
    $safeTpl = preg_replace('/^([\w-]+)$/i', '$1', $tpl);
    $settingFile = getTemplateSettingFile(USER_TPLS_PATH, $safeTpl);
    if ($safeTpl !== $tpl || !checkTemplateBootstrap(USER_TPLS_PATH, $safeTpl) || !loadTemplateBootstrap(USER_TPLS_PATH, $safeTpl) || $settingFile === false) {
        emMsg('用户后台模板配置文件不存在或已损坏', './template.php?tab=user');
    }
    // 检查模板是否已到期/未授权
    require_once DC_ROOT . '/include/lib/plugin_license.php';
    $licenseKey = 'user_template:' . $safeTpl;
    if (!PluginLicense::verify($licenseKey)) {
        $tplDisplayName = $safeTpl;
        $bootstrapFile = getTemplateBootstrapFile(USER_TPLS_PATH, $safeTpl);
        if ($bootstrapFile && file_exists($bootstrapFile)) {
            $headerData = @file_get_contents($bootstrapFile, false, null, 0, 2048);
            if ($headerData && preg_match('/Template\s*Name\s*:\s*(.+)/i', $headerData, $nm)) {
                $tplDisplayName = trim($nm[1]);
            }
        }
        include View::getAdmView('open_head');
        PluginLicense::showExpiredNotice($licenseKey, $tplDisplayName, 'tpl');
        include View::getAdmView('open_foot');
        exit;
    }
    include View::getAdmView('open_head');
    require_once DC_ROOT . '/include/lib/template_setting_mobile.php';
    require_once $settingFile;
    plugin_setting_view($safeTpl);
    include View::getAdmView('open_foot');
}
if($action == 'user_setting_ajax'){
    if (!LoginAuth::checkAjaxToken()) {
        output::error('安全token校验失败，请刷新页面重试');
    }
    $tpl = trim((string)Input::getStrVar('tpl'));
    $safeTpl = preg_replace('/^([\w-]+)$/i', '$1', $tpl);
    $settingFile = getTemplateSettingFile(USER_TPLS_PATH, $safeTpl);
    if ($safeTpl !== $tpl || !checkTemplateBootstrap(USER_TPLS_PATH, $safeTpl) || !loadTemplateBootstrap(USER_TPLS_PATH, $safeTpl) || $settingFile === false) {
        output::error('用户后台模板配置文件不存在或已损坏');
    }
    require_once $settingFile;
    plugin_setting($safeTpl);
}

// 启用用户后台模板（电脑端）
if ($action === 'user_use') {
    LoginAuth::checkToken();
    $tplName = trim((string)Input::postStrVar('tpl'));
    if ($tplName !== '') {
        $safeTplName = preg_replace('/^([\w-]+)$/i', '$1', $tplName);
        $tplPath = USER_TPLS_PATH . $safeTplName . '/';
        if ($safeTplName !== $tplName || !is_dir($tplPath) || !checkTemplateBootstrap(USER_TPLS_PATH, $safeTplName)) {
            output::error('用户后台模板不存在或已损坏');
        }
        $tplName = $safeTplName;

        // 启用时检查模板是否已到期
        require_once DC_ROOT . '/include/lib/plugin_license.php';
        $licenseKey = 'user_template:' . $tplName;
        if (!PluginLicense::verify($licenseKey, true)) {
            $tplStatus = PluginLicense::getStatus($licenseKey);
            $errorMsg = '该模板已到期，请续期后再启用';
            if ($tplStatus === 'blocked') {
                $errorMsg = '该模板已被开发者禁用，无法启用';
            } else if ($tplStatus === 'unauthorized') {
                $errorMsg = '您尚未购买此模板，请先购买后启用';
            }
            output::error($errorMsg);
        }
    }
    Option::updateOption('nonce_user_tpl', $tplName);
    $CACHE->updateCache('options');
    if ($tplName !== '') {
        runTemplateCallback(USER_TPLS_PATH, $tplName, 'callback_init');
    }
    output::ok();
}

// 启用用户后台模板（手机端）
if ($action === 'user_use_tel') {
    LoginAuth::checkToken();
    $tplName = trim((string)Input::postStrVar('tpl'));
    if ($tplName !== '') {
        $safeTplName = preg_replace('/^([\w-]+)$/i', '$1', $tplName);
        $tplPath = USER_TPLS_PATH . $safeTplName . '/';
        if ($safeTplName !== $tplName || !is_dir($tplPath) || !checkTemplateBootstrap(USER_TPLS_PATH, $safeTplName)) {
            output::error('用户后台模板不存在或已损坏');
        }
        $tplName = $safeTplName;

        // 启用时检查模板是否已到期
        require_once DC_ROOT . '/include/lib/plugin_license.php';
        $licenseKey = 'user_template:' . $tplName;
        if (!PluginLicense::verify($licenseKey, true)) {
            $tplStatus = PluginLicense::getStatus($licenseKey);
            $errorMsg = '该模板已到期，请续期后再启用';
            if ($tplStatus === 'blocked') {
                $errorMsg = '该模板已被开发者禁用，无法启用';
            } else if ($tplStatus === 'unauthorized') {
                $errorMsg = '您尚未购买此模板，请先购买后启用';
            }
            output::error($errorMsg);
        }
    }
    Option::updateOption('nonce_user_tpl_tel', $tplName);
    $CACHE->updateCache('options');
    if ($tplName !== '') {
        runTemplateCallback(USER_TPLS_PATH, $tplName, 'callback_init');
    }
    output::ok();
}

// 删除用户后台模板
if ($action === 'user_del') {
    LoginAuth::checkToken();
    $tpls = Input::postStrVar('ids');
    $tpls = explode(',', $tpls);
    $nonce_user_tpl = trim((string)Option::get('nonce_user_tpl'));
    $nonce_user_tpl_tel = trim((string)Option::get('nonce_user_tpl_tel'));

    foreach ($tpls as $val) {
        $val = trim($val);
        if ($val === $nonce_user_tpl || $val === $nonce_user_tpl_tel) {
            output::error('不能删除正在使用的模板');
        }
        $path = preg_replace('/^([\w-]+)$/i', '$1', $val);
        if ($path !== $val || $path === '') {
            output::error('模板标识不合法');
        }
        if (!is_dir(USER_TPLS_PATH . $path)) {
            continue;
        }
        runTemplateCallback(USER_TPLS_PATH, $path, 'callback_rm');
        emDeleteFile(USER_TPLS_PATH . $path);
    }
    output::ok();
}

if ($action === 'user_upgrade') {
    $plugin_id = Input::postStrVar('plugin_id');
    $alias = Input::postStrVar('alias');
    upgradeTemplatePackage(USER_TPLS_PATH, $alias, $plugin_id);
}

// 用户后台模板列表
if ($action == 'user_index') {
    $list = $Template_Model->getUserCenterTemplates();
    $nonce_user_tpl = trim((string)Option::get('nonce_user_tpl'));
    $nonce_user_tpl_tel = trim((string)Option::get('nonce_user_tpl_tel'));

    // 检查更新，同时获取购买信息（与首页模板、plugin.php 保持一致）
    $check = [];
    foreach ($list as $val) {
        $check[] = [
            'name' => $val['tplfile'],
            'version' => $val['version'],
            'type' => 'user_template'
        ];
    }
    $checkResult = $Store_Model->checkUpdate($check, true);
    $update_data = $checkResult['updates'] ?? [];
    $purchase_info = $checkResult['purchase_info'] ?? [];

    // 批量刷新授权状态
    require_once DC_ROOT . '/include/lib/plugin_license.php';
    $allSlugs = array_column($list, 'tplfile');
    if (!empty($allSlugs)) {
        $licenseSlugs = array_map(function($slug) { return 'user_template:' . $slug; }, $allSlugs);
        PluginLicense::batchVerify($licenseSlugs, getTopHost());
    }

    // 自动关闭未授权/已到期/被禁用的当前用户后台模板（对齐首页模板逻辑）
    $needUpdateCache = false;
    foreach ($list as $val) {
        $licenseKey = 'user_template:' . $val['tplfile'];
        if ($nonce_user_tpl == $val['tplfile'] && !PluginLicense::verify($licenseKey)) {
            Option::updateOption('nonce_user_tpl', '');
            $nonce_user_tpl = '';
            $needUpdateCache = true;
        }
        if ($nonce_user_tpl_tel == $val['tplfile'] && !PluginLicense::verify($licenseKey)) {
            Option::updateOption('nonce_user_tpl_tel', '');
            $nonce_user_tpl_tel = '';
            $needUpdateCache = true;
        }
    }
    if ($needUpdateCache) {
        $CACHE->updateCache('options');
    }

    foreach ($list as $key => $val) {
        $list[$key]['switch'] = ($nonce_user_tpl == $val['tplfile']) ? 'y' : 'n';
        $list[$key]['tel_switch'] = ($nonce_user_tpl_tel == $val['tplfile']) ? 'y' : 'n';
        $list[$key]['has_setting'] = $val['has_setting'] ?? 'n';
        $list[$key]['update'] = 'n';
        $list[$key]['expire_time'] = $val['expire_time'] ?? '';
        $list[$key]['buy_type'] = $val['buy_type'] ?? '';

        foreach ($update_data as $v) {
            if (($v['name'] ?? '') == $val['tplfile']) {
                $list[$key]['update'] = 'y';
                $list[$key]['id'] = $v['id'];
                $list[$key]['new_version'] = $v['new_version'] ?? '';
            }
        }

        $purchaseKey = 'user_template:' . $val['tplfile'];
        if (isset($purchase_info[$purchaseKey])) {
            $list[$key]['buy_type'] = $purchase_info[$purchaseKey]['buy_type'] ?? '';
            $list[$key]['expire_time'] = $purchase_info[$purchaseKey]['expire_time'] ?? '';
        }

        // 更新授权状态（批量刷新后的最新状态）
        $licenseKey = 'user_template:' . $val['tplfile'];
        $list[$key]['license_status'] = PluginLicense::getStatus($licenseKey);
        if (empty($list[$key]['buy_type'])) {
            $list[$key]['buy_type'] = PluginLicense::getBuyType($licenseKey);
        }
    }

    $page = Input::getIntVar('page', 1);
    $limit = Input::getIntVar('limit', 10);
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

    if (!empty($keyword)) {
        $keyword = strtolower($keyword);
        $list = array_filter($list, function ($item) use ($keyword) {
            $name = strtolower($item['tplname'] ?? '');
            $desc = strtolower($item['tpldes'] ?? '');
            $author = strtolower($item['author'] ?? '');
            $file = strtolower($item['tplfile'] ?? '');
            return strpos($name, $keyword) !== false ||
                   strpos($desc, $keyword) !== false ||
                   strpos($author, $keyword) !== false ||
                   strpos($file, $keyword) !== false;
        });
        $list = array_values($list);
    }

    $total = count($list);

    if ($limit < 1000) {
        $offset = ($page - 1) * $limit;
        $list = array_slice($list, $offset, $limit);
    }

    output::data($list, $total);
}

// 用户后台模板上传安装
if ($action === 'user_upload') {
    Output::demoCheck();
    $Media_Model = new Media_Model();
    $attach = isset($_FILES['file']) ? $_FILES['file'] : '';

    if (empty($attach) || !is_array($attach) || empty($attach['name'])) {
        Output::error('请选择要上传的模板压缩包', 200);
    }

    $ret = '';
    upload2local($attach, $ret);
    if (empty($ret['success']) || empty($ret['file_info'])) {
        Output::error($ret['message'] ?? '上传失败', 200);
    }

    $filePath = DC_ROOT . '/' . $ret['file_info']['file_path'];
    $unzip_path = USER_TPLS_PATH;
    $result = emUnZip($filePath, $unzip_path, 'tpl');
    @unlink($filePath);

    switch ($result) {
        case 0:
            Output::ok();
            break;
        case 1:
        case 2:
            Output::error('安装失败，目录不可写');
            break;
        case 3:
            Output::error('安装失败，缺少Zip扩展');
            break;
        default:
            Output::error('安装失败');
    }
}

if ($action == 'bottom_nav_index') {
    $list = $Template_Model->getBottomNavTemplates();
    $active_bottom_nav_tpl = Option::get('nonce_bottom_nav_tpl') ?: 'default';

    // 检查更新，同时获取购买信息（与首页模板、plugin.php 保持一致）
    $check = [];
    foreach ($list as $val) {
        $check[] = [
            'name' => $val['tplfile'],
            'version' => $val['version'],
            'type' => 'bottom_nav_template'
        ];
    }
    $checkResult = $Store_Model->checkUpdate($check, true);
    $update_data = $checkResult['updates'] ?? [];
    $purchase_info = $checkResult['purchase_info'] ?? [];

    // 批量刷新授权状态
    require_once DC_ROOT . '/include/lib/plugin_license.php';
    $allSlugs = array_column($list, 'tplfile');
    if (!empty($allSlugs)) {
        $licenseSlugs = array_map(function($slug) { return 'bottom_nav_template:' . $slug; }, $allSlugs);
        PluginLicense::batchVerify($licenseSlugs, getTopHost());
    }

    // 自动关闭未授权/已到期/被禁用的当前底部导航模板（对齐首页模板逻辑）
    $needUpdateCache = false;
    foreach ($list as $val) {
        if ($active_bottom_nav_tpl == $val['tplfile']) {
            $licenseKey = 'bottom_nav_template:' . $val['tplfile'];
            if (!PluginLicense::verify($licenseKey)) {
                Option::updateOption('nonce_bottom_nav_tpl', 'em_null_tpl');
                $active_bottom_nav_tpl = 'em_null_tpl';
                $needUpdateCache = true;
            }
        }
    }
    if ($needUpdateCache) {
        $CACHE->updateCache('options');
    }

    foreach ($list as $key => $val) {
        $list[$key]['switch'] = ($active_bottom_nav_tpl == $val['tplfile']) ? 'y' : 'n';
        $list[$key]['has_setting'] = $val['has_setting'] ?? 'n';
        $list[$key]['update'] = 'n';
        $list[$key]['expire_time'] = $val['expire_time'] ?? '';
        $list[$key]['buy_type'] = $val['buy_type'] ?? '';

        foreach ($update_data as $v) {
            if (($v['name'] ?? '') == $val['tplfile']) {
                $list[$key]['update'] = 'y';
                $list[$key]['id'] = $v['id'];
                $list[$key]['new_version'] = $v['new_version'] ?? '';
            }
        }

        $purchaseKey = 'bottom_nav_template:' . $val['tplfile'];
        if (isset($purchase_info[$purchaseKey])) {
            $list[$key]['buy_type'] = $purchase_info[$purchaseKey]['buy_type'] ?? '';
            $list[$key]['expire_time'] = $purchase_info[$purchaseKey]['expire_time'] ?? '';
        }

        // 授权状态
        $licenseKey = 'bottom_nav_template:' . $val['tplfile'];
        $list[$key]['license_status'] = PluginLicense::getStatus($licenseKey);
        if (empty($list[$key]['buy_type'])) {
            $list[$key]['buy_type'] = PluginLicense::getBuyType($licenseKey);
        }
    }

    $page = Input::getIntVar('page', 1);
    $limit = Input::getIntVar('limit', 10);
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

    if (!empty($keyword)) {
        $keyword = strtolower($keyword);
        $list = array_filter($list, function ($item) use ($keyword) {
            $name = strtolower($item['tplname'] ?? '');
            $desc = strtolower($item['tpldes'] ?? '');
            $author = strtolower($item['author'] ?? '');
            $file = strtolower($item['tplfile'] ?? '');
            return strpos($name, $keyword) !== false ||
                   strpos($desc, $keyword) !== false ||
                   strpos($author, $keyword) !== false ||
                   strpos($file, $keyword) !== false;
        });
        $list = array_values($list);
    }

    $total = count($list);

    if ($limit < 1000) {
        $offset = ($page - 1) * $limit;
        $list = array_slice($list, $offset, $limit);
    }

    output::data($list, $total);
}

if ($action === 'bottom_nav_use') {
    LoginAuth::checkToken();
    $tplName = trim((string)Input::postStrVar('tpl'));
    if ($tplName !== 'em_null_tpl' && $tplName !== '') {
        $safeTplName = preg_replace('/^([\w-]+)$/i', '$1', $tplName);
        if ($safeTplName !== $tplName || !is_dir(BOTTOM_NAV_TPLS_PATH . $safeTplName . '/') || !checkTemplateBootstrap(BOTTOM_NAV_TPLS_PATH, $safeTplName) || !file_exists(BOTTOM_NAV_TPLS_PATH . $safeTplName . '/render.php')) {
            output::error('底部导航模板不存在或已损坏');
        }
        $tplName = $safeTplName;

        // 启用时检查模板是否已到期
        require_once DC_ROOT . '/include/lib/plugin_license.php';
        $licenseKey = 'bottom_nav_template:' . $tplName;
        if (!PluginLicense::verify($licenseKey, true)) {
            $tplStatus = PluginLicense::getStatus($licenseKey);
            $errorMsg = '该模板已到期，请续期后再启用';
            if ($tplStatus === 'blocked') {
                $errorMsg = '该模板已被开发者禁用，无法启用';
            } else if ($tplStatus === 'unauthorized') {
                $errorMsg = '您尚未购买此模板，请先购买后启用';
            }
            output::error($errorMsg);
        }
    } else {
        $tplName = 'em_null_tpl';
    }
    Option::updateOption('nonce_bottom_nav_tpl', $tplName);
    $CACHE->updateCache('options');
    if ($tplName !== 'em_null_tpl') {
        runTemplateCallback(BOTTOM_NAV_TPLS_PATH, $tplName, 'callback_init');
    }
    output::ok();
}

if ($action === 'bottom_nav_del') {
    LoginAuth::checkToken();
    $tpls = Input::postStrVar('ids');
    $tpls = explode(',', $tpls);
    $active_bottom_nav_tpl = Option::get('nonce_bottom_nav_tpl') ?: 'default';

    foreach ($tpls as $val) {
        $val = trim($val);
        if ($val === $active_bottom_nav_tpl) {
            output::error('不能删除正在使用的底部导航模板');
        }
        $path = preg_replace('/^([\w-]+)$/i', '$1', $val);
        if ($path !== $val || $path === '') {
            output::error('模板标识不合法');
        }
        runTemplateCallback(BOTTOM_NAV_TPLS_PATH, $path, 'callback_rm');
        emDeleteFile(BOTTOM_NAV_TPLS_PATH . $path);
    }
    output::ok();
}

if ($action === 'bottom_nav_upgrade') {
    $plugin_id = Input::postStrVar('plugin_id');
    $alias = Input::postStrVar('alias');
    upgradeTemplatePackage(BOTTOM_NAV_TPLS_PATH, $alias, $plugin_id);
}

if($action == 'blog_setting_page'){
    sendTemplateSettingNoCacheHeaders();
    $tpl = trim((string)Input::getStrVar('tpl'));
    $safeTpl = preg_replace('/^([\w-]+)$/i', '$1', $tpl);
    $settingFile = getTemplateSettingFile(BLOG_TPLS_PATH, $safeTpl);
    if ($safeTpl !== $tpl || !checkTemplateBootstrap(BLOG_TPLS_PATH, $safeTpl) || !loadTemplateBootstrap(BLOG_TPLS_PATH, $safeTpl) || $settingFile === false) {
        emMsg('博客模板配置文件不存在或已损坏', './template.php?tab=blog');
    }
    require_once DC_ROOT . '/include/lib/plugin_license.php';
    $licenseKey = 'blog_template:' . $safeTpl;
    if (!PluginLicense::verify($licenseKey)) {
        $tplDisplayName = $safeTpl;
        $bootstrapFile = getTemplateBootstrapFile(BLOG_TPLS_PATH, $safeTpl);
        if ($bootstrapFile && file_exists($bootstrapFile)) {
            $headerData = @file_get_contents($bootstrapFile, false, null, 0, 2048);
            if ($headerData && preg_match('/Template\s*Name\s*:\s*(.+)/i', $headerData, $nm)) {
                $tplDisplayName = trim($nm[1]);
            }
        }
        include View::getAdmView('open_head');
        PluginLicense::showExpiredNotice($licenseKey, $tplDisplayName, 'tpl');
        include View::getAdmView('open_foot');
        exit;
    }
    include View::getAdmView('open_head');
    require_once DC_ROOT . '/include/lib/template_setting_mobile.php';
    require_once $settingFile;
    plugin_setting_view($safeTpl);
    include View::getAdmView('open_foot');
}
if($action == 'blog_setting_ajax'){
    LoginAuth::checkToken();
    $tpl = trim((string)Input::getStrVar('tpl'));
    $safeTpl = preg_replace('/^([\w-]+)$/i', '$1', $tpl);
    $settingFile = getTemplateSettingFile(BLOG_TPLS_PATH, $safeTpl);
    if ($safeTpl !== $tpl || !checkTemplateBootstrap(BLOG_TPLS_PATH, $safeTpl) || !loadTemplateBootstrap(BLOG_TPLS_PATH, $safeTpl) || $settingFile === false) {
        output::error('博客模板配置文件不存在或已损坏');
    }
    require_once $settingFile;
    plugin_setting($safeTpl);
}

if ($action == 'blog_index') {
    $list = $Template_Model->getBlogTemplates();
    $active_blog_tpl = Option::get('nonce_blog_tpl') ?: 'default';
    $active_blog_tpl_tel = Option::get('nonce_blog_tpl_tel') ?: $active_blog_tpl;

    $check = [];
    foreach ($list as $val) {
        $check[] = [
            'name' => $val['tplfile'],
            'version' => $val['version'],
            'type' => 'blog_template'
        ];
    }
    $checkResult = $Store_Model->checkUpdate($check, true);
    $update_data = $checkResult['updates'] ?? [];
    $purchase_info = $checkResult['purchase_info'] ?? [];

    require_once DC_ROOT . '/include/lib/plugin_license.php';
    $allSlugs = array_column($list, 'tplfile');
    if (!empty($allSlugs)) {
        $licenseSlugs = array_map(function($slug) { return 'blog_template:' . $slug; }, $allSlugs);
        PluginLicense::batchVerify($licenseSlugs, getTopHost());
    }

    // 自动关闭未授权/已到期/被禁用的当前博客模板（对齐首页模板逻辑）
    $needUpdateCache = false;
    foreach ($list as $val) {
        $licenseKey = 'blog_template:' . $val['tplfile'];
        if ($active_blog_tpl == $val['tplfile'] && !PluginLicense::verify($licenseKey)) {
            Option::updateOption('nonce_blog_tpl', 'em_null_tpl');
            $active_blog_tpl = 'em_null_tpl';
            $needUpdateCache = true;
        }
        if ($active_blog_tpl_tel == $val['tplfile'] && !PluginLicense::verify($licenseKey)) {
            Option::updateOption('nonce_blog_tpl_tel', 'em_null_tpl');
            $active_blog_tpl_tel = 'em_null_tpl';
            $needUpdateCache = true;
        }
    }
    if ($needUpdateCache) {
        $CACHE->updateCache('options');
    }

    foreach ($list as $key => $val) {
        $list[$key]['switch'] = ($active_blog_tpl == $val['tplfile']) ? 'y' : 'n';
        $list[$key]['tel_switch'] = ($active_blog_tpl_tel == $val['tplfile']) ? 'y' : 'n';
        $list[$key]['has_setting'] = $val['has_setting'] ?? 'n';
        $list[$key]['update'] = 'n';
        $list[$key]['expire_time'] = $val['expire_time'] ?? '';
        $list[$key]['buy_type'] = $val['buy_type'] ?? '';

        foreach ($update_data as $v) {
            $updateType = normalizeTemplateAppType($v['type'] ?? 'blog_template');
            if (($v['name'] ?? '') == $val['tplfile'] && $updateType === 'blog_template') {
                $list[$key]['update'] = 'y';
                $list[$key]['id'] = $v['id'];
                $list[$key]['new_version'] = $v['new_version'] ?? '';
            }
        }

        $purchaseKey = 'blog_template:' . $val['tplfile'];
        if (isset($purchase_info[$purchaseKey])) {
            $list[$key]['buy_type'] = $purchase_info[$purchaseKey]['buy_type'] ?? '';
            $list[$key]['expire_time'] = $purchase_info[$purchaseKey]['expire_time'] ?? '';
        }

        $licenseKey = 'blog_template:' . $val['tplfile'];
        $list[$key]['license_status'] = PluginLicense::getStatus($licenseKey);
        if (empty($list[$key]['buy_type'])) {
            $list[$key]['buy_type'] = PluginLicense::getBuyType($licenseKey);
        }
    }

    $page = Input::getIntVar('page', 1);
    $limit = Input::getIntVar('limit', 10);
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

    if (!empty($keyword)) {
        $keyword = strtolower($keyword);
        $list = array_filter($list, function ($item) use ($keyword) {
            $name = strtolower($item['tplname'] ?? '');
            $desc = strtolower($item['tpldes'] ?? '');
            $author = strtolower($item['author'] ?? '');
            $file = strtolower($item['tplfile'] ?? '');
            return strpos($name, $keyword) !== false ||
                   strpos($desc, $keyword) !== false ||
                   strpos($author, $keyword) !== false ||
                   strpos($file, $keyword) !== false;
        });
        $list = array_values($list);
    }

    $total = count($list);
    if ($limit < 1000) {
        $offset = ($page - 1) * $limit;
        $list = array_slice($list, $offset, $limit);
    }

    output::data($list, $total);
}

if ($action === 'blog_use') {
    LoginAuth::checkToken();
    $tplName = trim((string)Input::postStrVar('tpl'));
    if ($tplName !== 'em_null_tpl' && $tplName !== '') {
        $safeTplName = preg_replace('/^([\w-]+)$/i', '$1', $tplName);
        if ($safeTplName !== $tplName || !is_dir(BLOG_TPLS_PATH . $safeTplName . '/') || !checkTemplateBootstrap(BLOG_TPLS_PATH, $safeTplName)) {
            output::error('博客模板不存在或已损坏');
        }
        $tplName = $safeTplName;

        require_once DC_ROOT . '/include/lib/plugin_license.php';
        $licenseKey = 'blog_template:' . $tplName;
        if (!PluginLicense::verify($licenseKey, true)) {
            $tplStatus = PluginLicense::getStatus($licenseKey);
            $errorMsg = '该模板已到期，请续期后再启用';
            if ($tplStatus === 'blocked') {
                $errorMsg = '该模板已被开发者禁用，无法启用';
            } else if ($tplStatus === 'unauthorized') {
                $errorMsg = '您尚未购买此模板，请先购买后启用';
            }
            output::error($errorMsg);
        }
    } else {
        $tplName = 'em_null_tpl';
    }

    Option::updateOption('nonce_blog_tpl', $tplName);
    $CACHE->updateCache('options');
    if ($tplName !== 'em_null_tpl') {
        runTemplateCallback(BLOG_TPLS_PATH, $tplName, 'callback_init');
    }
    output::ok();
}

if ($action === 'blog_use_tel') {
    LoginAuth::checkToken();
    $tplName = trim((string)Input::postStrVar('tpl'));
    if ($tplName !== 'em_null_tpl' && $tplName !== '') {
        $safeTplName = preg_replace('/^([\w-]+)$/i', '$1', $tplName);
        if ($safeTplName !== $tplName || !is_dir(BLOG_TPLS_PATH . $safeTplName . '/') || !checkTemplateBootstrap(BLOG_TPLS_PATH, $safeTplName)) {
            output::error('博客模板不存在或已损坏');
        }
        $tplName = $safeTplName;

        require_once DC_ROOT . '/include/lib/plugin_license.php';
        $licenseKey = 'blog_template:' . $tplName;
        if (!PluginLicense::verify($licenseKey, true)) {
            $tplStatus = PluginLicense::getStatus($licenseKey);
            $errorMsg = '该模板已到期，请续期后再启用';
            if ($tplStatus === 'blocked') {
                $errorMsg = '该模板已被开发者禁用，无法启用';
            } else if ($tplStatus === 'unauthorized') {
                $errorMsg = '您尚未购买此模板，请先购买后启用';
            }
            output::error($errorMsg);
        }
    } else {
        $tplName = 'em_null_tpl';
    }

    Option::updateOption('nonce_blog_tpl_tel', $tplName);
    $CACHE->updateCache('options');
    if ($tplName !== 'em_null_tpl') {
        runTemplateCallback(BLOG_TPLS_PATH, $tplName, 'callback_init');
    }
    output::ok();
}

if ($action === 'blog_del') {
    LoginAuth::checkToken();
    $tpls = Input::postStrVar('ids');
    $tpls = explode(',', $tpls);
    $active_blog_tpl = Option::get('nonce_blog_tpl') ?: 'default';
    $active_blog_tpl_tel = Option::get('nonce_blog_tpl_tel') ?: $active_blog_tpl;

    foreach ($tpls as $val) {
        $val = trim($val);
        if ($val === 'default') {
            output::error('默认博客模板不能删除');
        }
        if ($val === $active_blog_tpl || $val === $active_blog_tpl_tel) {
            output::error('不能删除正在使用的博客模板');
        }
        $path = preg_replace('/^([\w-]+)$/i', '$1', $val);
        if ($path !== $val || $path === '') {
            output::error('模板标识不合法');
        }
        if (!is_dir(BLOG_TPLS_PATH . $path)) {
            continue;
        }
        runTemplateCallback(BLOG_TPLS_PATH, $path, 'callback_rm');
        emDeleteFile(BLOG_TPLS_PATH . $path);
    }
    output::ok();
}

if ($action === 'blog_upgrade') {
    $plugin_id = Input::postStrVar('plugin_id');
    $alias = Input::postStrVar('alias');
    upgradeTemplatePackage(BLOG_TPLS_PATH, $alias, $plugin_id);
}

if ($action === 'blog_upload') {
    Output::demoCheck();
    $Media_Model = new Media_Model();
    $attach = isset($_FILES['file']) ? $_FILES['file'] : '';

    if (empty($attach) || !is_array($attach) || empty($attach['name'])) {
        Output::error('请选择要上传的模板压缩包', 200);
    }

    $uploadCheckResult = Media::checkUpload($attach);
    if ($uploadCheckResult !== true) {
        Output::error($uploadCheckResult, 200);
    }

    $ret = '';
    upload2local($attach, $ret);
    if (empty($ret['success']) || empty($ret['file_info'])) {
        Output::error($ret['message'] ?? '上传失败', 200);
    }

    $Media_Model->addMedia($ret['file_info']);
    $filePath = DC_ROOT . '/' . $ret['file_info']['file_path'];
    $result = emUnZip($filePath, BLOG_TPLS_PATH, 'tpl');
    @unlink($filePath);

    switch ($result) {
        case 0:
            Output::ok();
            break;
        case 1:
        case 2:
            Output::error('安装失败，目录不可写');
            break;
        case 3:
            Output::error('安装失败，缺少Zip扩展');
            break;
        default:
            Output::error('安装失败');
    }
}

// 底部导航模板上传安装
if ($action === 'bottom_nav_upload') {
    Output::demoCheck();
    $Media_Model = new Media_Model();
    $attach = isset($_FILES['file']) ? $_FILES['file'] : '';

    if (empty($attach) || !is_array($attach) || empty($attach['name'])) {
        Output::error('请选择要上传的模板压缩包', 200);
    }

    $uploadCheckResult = Media::checkUpload($attach);
    if ($uploadCheckResult !== true) {
        Output::error($uploadCheckResult, 200);
    }

    $ret = '';
    upload2local($attach, $ret);
    if (empty($ret['success']) || empty($ret['file_info'])) {
        Output::error($ret['message'] ?? '上传失败', 200);
    }

    $Media_Model->addMedia($ret['file_info']);

    $filePath = DC_ROOT . '/' . $ret['file_info']['file_path'];
    $unzip_path = BOTTOM_NAV_TPLS_PATH;
    $result = emUnZip($filePath, $unzip_path, 'tpl');
    @unlink($filePath);

    switch ($result) {
        case 0:
            Output::ok();
            break;
        case 1:
        case 2:
            Output::error('安装失败，目录不可写');
            break;
        case 3:
            Output::error('安装失败，缺少Zip扩展');
            break;
        default:
            Output::error('安装失败');
    }
}



