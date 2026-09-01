<?php
/**
 * The productf management
 *
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

/**
 * @var string $action
 * @var object $CACHE
 */

require_once 'globals.php';
//require_once '../init.php';

$orderModel = new Order_Model();
$Sort_Model = new Sort_Model();
$User_Model = new User_Model();
$MediaSort_Model = new MediaSort_Model();
$Template_Model = new Template_Model();

$sta_cache = $CACHE->readCache('sta');
$user_cache = $CACHE->readCache('user');
$action = Input::getStrVar('action');

$db = Database::getInstance();
$db_prefix = DB_PREFIX;

function stationEnsureExtraColumns($db, $db_prefix) {
    static $checked = false;
    if ($checked) return;
    $checked = true;
    $table = $db_prefix . 'station';
    $cols = ['site_description','site_key','log_title_style','icp','footer_info','user_agreement','privacy_policy','logo','favicon'];
    $defs = [
        'site_description' => "text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'SEO描述'",
        'site_key'         => "varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'SEO关键字'",
        'log_title_style'  => "tinyint(1) DEFAULT 0 COMMENT '详情页标题方案'",
        'icp'              => "varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ICP备案号'",
        'footer_info'      => "text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '首页底部信息'",
        'user_agreement'   => "mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '用户服务协议'",
        'privacy_policy'   => "mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '隐私政策'",
        'logo'             => "varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '网站Logo'",
        'favicon'          => "varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '网站Favicon'",
    ];
    foreach ($cols as $col) {
        $row = $db->once_fetch_array("SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$table}' AND COLUMN_NAME = '{$col}'");
        if (!$row || (int)$row['cnt'] === 0) {
            $db->query("ALTER TABLE `{$table}` ADD COLUMN `{$col}` {$defs[$col]}");
        }
    }
}

function stationGetSwitchSlug($db, $db_prefix, $stationId, $type, $field) {
    $stationId = (int)$stationId;
    $type = addslashes(trim((string)$type));
    $field = $field === 'tel_switch' ? 'tel_switch' : 'pc_switch';
    $row = $db->once_fetch_array("SELECT plugin_name_en FROM {$db_prefix}station_plugin WHERE station_id={$stationId} AND type='{$type}' AND {$field}='y' LIMIT 1");
    if (empty($row['plugin_name_en']) || ($type !== 'bottom_nav' && $row['plugin_name_en'] === 'em_null_tpl')) {
        return 'default';
    }
    return trim((string)$row['plugin_name_en']);
}

function stationUpsertSwitchState($db, $db_prefix, $stationId, $type, $field, $pluginName) {
    $stationId = (int)$stationId;
    $type = trim((string)$type);
    $field = $field === 'tel_switch' ? 'tel_switch' : 'pc_switch';
    $pluginName = trim((string)$pluginName);
    if ($pluginName === '') {
        return;
    }
    $safeType = addslashes($type);
    $safePluginName = addslashes($pluginName);
    $existRow = $db->once_fetch_array("SELECT plugin_name_en FROM {$db_prefix}station_plugin WHERE station_id={$stationId} AND type='{$safeType}' AND plugin_name_en='{$safePluginName}' LIMIT 1");
    if (empty($existRow)) {
        $data = [
            'station_id' => $stationId,
            'plugin_name_en' => $pluginName,
            'plugin_name_cn' => '',
            'type' => $type,
            'pc_switch' => 'n',
            'tel_switch' => 'n',
        ];
        $data[$field] = 'y';
        $db->add('station_plugin', $data);
        return;
    }
    $db->update('station_plugin', [$field => 'y'], [
        'station_id' => $stationId,
        'type' => $type,
        'plugin_name_en' => $pluginName,
    ]);
}

function stationTemplateLicensePrefix($type) {
    switch ($type) {
        case 'user_tpl':
            return 'user_template:';
        case 'bottom_nav':
            return 'bottom_nav_template:';
        case 'tpl':
        default:
            return 'template:';
    }
}

function stationTemplateLicenseInvalid($status) {
    return in_array((string)$status, ['expired', 'blocked', 'unauthorized', 'tampered'], true);
}

function stationTemplateLicenseMessage($status) {
    switch ((string)$status) {
        case 'unauthorized':
            return '模板未授权，请联系站长';
        case 'expired':
            return '模板已到期，请联系站长续期';
        case 'blocked':
            return '模板已被禁用，请联系站长';
        case 'tampered':
            return '模板授权异常，请联系站长';
        default:
            return '模板授权状态异常，请联系站长';
    }
}

function stationTemplateLicenseCache($licenseKey) {
    $cacheFile = DC_ROOT . '/content/cache/plugin_license.php';
    if (!file_exists($cacheFile)) {
        return null;
    }
    $content = @file_get_contents($cacheFile);
    if (!$content) {
        return null;
    }
    $content = str_replace(['<?php exit;?>', '<?php exit; ?>'], '', $content);
    $cache = @json_decode(trim($content), true);
    return is_array($cache) && isset($cache[$licenseKey]) ? $cache[$licenseKey] : null;
}

function stationAttachTemplateLicenseInfo(&$list, $type) {
    require_once DC_ROOT . '/include/lib/plugin_license.php';
    $prefix = stationTemplateLicensePrefix($type);
    $licenseSlugs = [];
    foreach ($list as $val) {
        if (!empty($val['tplfile'])) {
            $licenseSlugs[] = $prefix . $val['tplfile'];
        }
    }
    if (!empty($licenseSlugs)) {
        // 分站独立域名 / 备用域名不单独校验授权，统一沿用主站 blogurl 域名的模板授权状态。
        // 主站已购买或续期的模板，主站下所有分站域名都应可用。
        PluginLicense::batchVerify(array_values(array_unique($licenseSlugs)));
    }
    foreach ($list as $key => $val) {
        $licenseKey = $prefix . ($val['tplfile'] ?? '');
        $status = PluginLicense::getStatus($licenseKey);
        $cache = stationTemplateLicenseCache($licenseKey);
        $list[$key]['license_status'] = $status;
        $list[$key]['buy_type'] = !empty($val['buy_type']) ? $val['buy_type'] : PluginLicense::getBuyType($licenseKey);
        $list[$key]['expire_time'] = $cache['expire_time'] ?? ($val['expire_time'] ?? '');
        $list[$key]['license_invalid'] = stationTemplateLicenseInvalid($status) ? 'y' : 'n';
        $list[$key]['license_msg'] = stationTemplateLicenseInvalid($status) ? stationTemplateLicenseMessage($status) : '';
    }
}

function stationDeactivateInvalidTemplateSwitches($db, $db_prefix, $stationId, $type, &$pcSlug, &$telSlug, $list) {
    $stationId = (int)$stationId;
    $changed = false;
    foreach ($list as $val) {
        $slug = trim((string)($val['tplfile'] ?? ''));
        if ($slug === '' || ($val['license_invalid'] ?? 'n') !== 'y') {
            continue;
        }
        $didDeactivate = false;
        if ($pcSlug === $slug) {
            $db->update('station_plugin', ['pc_switch' => 'n'], [
                'station_id' => $stationId,
                'type' => $type,
                'plugin_name_en' => $slug,
            ]);
            $didDeactivate = true;
            $changed = true;
        }
        if ($telSlug !== null && $telSlug === $slug) {
            $db->update('station_plugin', ['tel_switch' => 'n'], [
                'station_id' => $stationId,
                'type' => $type,
                'plugin_name_en' => $slug,
            ]);
            $didDeactivate = true;
            $changed = true;
        }
        if ($type === 'bottom_nav' && $didDeactivate) {
            stationUpsertSwitchState($db, $db_prefix, $stationId, 'bottom_nav', 'pc_switch', 'em_null_tpl');
        }
    }
    if ($changed) {
        $pcSlug = stationGetSwitchSlug($db, $db_prefix, $stationId, $type, 'pc_switch');
        if ($telSlug !== null) {
            $telSlug = stationGetSwitchSlug($db, $db_prefix, $stationId, $type, 'tel_switch');
        }
    }
}

function stationVerifyTemplateLicenseOrError($type, $tplName) {
    require_once DC_ROOT . '/include/lib/plugin_license.php';
    $licenseKey = stationTemplateLicensePrefix($type) . $tplName;
    if (!PluginLicense::verify($licenseKey, true)) {
        Ret::error(stationTemplateLicenseMessage(PluginLicense::getStatus($licenseKey)));
    }
}

if (!empty($userData['station']) && array_key_exists('status', $userData['station']) && (int)$userData['station']['status'] === 0) {
    $isAjaxStationAction = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
        || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
        || preg_match('/(_ajax|_index|_stats)$/i', (string)$action)
        || preg_match('/^(get_|use)/i', (string)$action);
    if ($isAjaxStationAction) {
        Ret::error('当前分店已被平台暂停使用，请联系管理员');
    }
    emMsg('当前分店已被平台暂停使用，请联系管理员', DC_URL . 'user/');
}

if (empty($action)) {
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    global $userData;

    $station_id = $userData['station']['id'];
    $level_id = $userData['station']['level_id'];

    $station = $db->once_fetch_array("select * from {$db_prefix}station_level where id = {$level_id}");

    // 当前分店等级 & 下一级升级信息
    $stationModel = new Station_Model();
    $currentStationLevel = $stationModel->getStationLevel($level_id);
    $nextUpgradeLevel = null;

    // 当前分店运营实际数据
    $myStats = [
        'sales'  => $stationModel->sumStationSales($station_id),
        'orders' => $stationModel->countStationOrders($station_id),
        'days'   => $stationModel->countStationDays($station_id),
        'subs'   => $stationModel->countSubStations($station_id),
    ];

    $stationPriceMode = Option::get('station_upgrade_price_mode') ?: 'diff';
    if ($currentStationLevel) {
        $allLevels = $stationModel->getAllLevels(false);
        $curSort = (int)$currentStationLevel['sort'];
        foreach ($allLevels as $_lv) {
            if ((int)$_lv['sort'] > $curSort) {
                $_lv['diff_price'] = ($stationPriceMode === 'full') ? (float)$_lv['price'] : max(0, round((float)$_lv['price'] - (float)$currentStationLevel['price'], 2));
                // 自动升级条件
                $upParts = [];
                if ((float)($_lv['upgrade_sales_amount'] ?? 0) > 0) $upParts[] = '销售≥' . (float)$_lv['upgrade_sales_amount'] . '元';
                if ((int)($_lv['upgrade_order_count'] ?? 0) > 0) $upParts[] = '订单≥' . (int)$_lv['upgrade_order_count'] . '单';
                if ((int)($_lv['upgrade_days'] ?? 0) > 0) $upParts[] = '运营≥' . (int)$_lv['upgrade_days'] . '天';
                if ((int)($_lv['upgrade_sub_count'] ?? 0) > 0) $upParts[] = '下级≥' . (int)$_lv['upgrade_sub_count'] . '店';
                $_lv['upgrade_desc'] = !empty($upParts) ? implode('、', $upParts) : '';
                $_lv['upgrade_mode_text'] = ($_lv['upgrade_mode'] ?? 'any') === 'all' ? '全部满足' : '任一满足';
                $_lv['has_auto_upgrade'] = !empty($upParts);
                $nextUpgradeLevel = $_lv;
                break;
            }
        }
    }

    // 获取订单统计数据

    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $month_start = date('Y-m-01');
    
    // 今日订单数
    $today_sql = "SELECT COUNT(*) as count FROM {$db_prefix}order 
                  WHERE station_id = {$station_id} AND delete_time IS NULL AND pay_time IS NOT NULL AND pay_time > 0
                  AND DATE(FROM_UNIXTIME(pay_time)) = '{$today}'";
    $today_data = $db->once_fetch_array($today_sql);
    $today_orders = $today_data['count'];
    
    // 昨日订单数
    $yesterday_sql = "SELECT COUNT(*) as count FROM {$db_prefix}order 
                      WHERE station_id = {$station_id} AND delete_time IS NULL AND pay_time IS NOT NULL AND pay_time > 0
                      AND DATE(FROM_UNIXTIME(pay_time)) = '{$yesterday}'";
    $yesterday_data = $db->once_fetch_array($yesterday_sql);
    $yesterday_orders = $yesterday_data['count'];
    
    // 本月订单数
    $month_sql = "SELECT COUNT(*) as count FROM {$db_prefix}order 
                  WHERE station_id = {$station_id} AND delete_time IS NULL AND pay_time IS NOT NULL AND pay_time > 0
                  AND DATE(FROM_UNIXTIME(pay_time)) >= '{$month_start}'";
    $month_data = $db->once_fetch_array($month_sql);
    $month_orders = $month_data['count'];
    
    // 总订单数
    $total_sql = "SELECT COUNT(*) as count FROM {$db_prefix}order 
                  WHERE station_id = {$station_id} AND delete_time IS NULL AND pay_time IS NOT NULL AND pay_time > 0";
    $total_data = $db->once_fetch_array($total_sql);
    $total_orders = $total_data['count'];

    // 分佣收入（从 dc_user_log 中按 station_commission 类型汇总，单位已经是元）
    $station_owner_uid = (int)UID;
    $log_table = "{$db_prefix}user_log";
    $commission_type = 'station_commission';

    $today_c = $db->once_fetch_array("SELECT COALESCE(SUM(amount), 0) as c FROM {$log_table} WHERE uid={$station_owner_uid} AND type='{$commission_type}' AND DATE(FROM_UNIXTIME(create_time))='{$today}'");
    $today_amount = (float)($today_c['c'] ?? 0);

    $yesterday_c = $db->once_fetch_array("SELECT COALESCE(SUM(amount), 0) as c FROM {$log_table} WHERE uid={$station_owner_uid} AND type='{$commission_type}' AND DATE(FROM_UNIXTIME(create_time))='{$yesterday}'");
    $yesterday_amount = (float)($yesterday_c['c'] ?? 0);

    $month_c = $db->once_fetch_array("SELECT COALESCE(SUM(amount), 0) as c FROM {$log_table} WHERE uid={$station_owner_uid} AND type='{$commission_type}' AND DATE(FROM_UNIXTIME(create_time))>='{$month_start}'");
    $month_amount = (float)($month_c['c'] ?? 0);

    $total_c = $db->once_fetch_array("SELECT COALESCE(SUM(amount), 0) as c FROM {$log_table} WHERE uid={$station_owner_uid} AND type='{$commission_type}'");
    $total_amount = (float)($total_c['c'] ?? 0);
    
    // 直推粉丝统计数据
    $station_owner_uid = (int)UID;
    $today_user_sql = "SELECT COUNT(*) as count FROM {$db_prefix}user 
                       WHERE superior = {$station_owner_uid} 
                       AND DATE(FROM_UNIXTIME(create_time)) = '{$today}'";
    $today_user_data = $db->once_fetch_array($today_user_sql);
    $today_users = $today_user_data['count'];
    
    $yesterday_user_sql = "SELECT COUNT(*) as count FROM {$db_prefix}user 
                           WHERE superior = {$station_owner_uid} 
                           AND DATE(FROM_UNIXTIME(create_time)) = '{$yesterday}'";
    $yesterday_user_data = $db->once_fetch_array($yesterday_user_sql);
    $yesterday_users = $yesterday_user_data['count'];
    
    $month_user_sql = "SELECT COUNT(*) as count FROM {$db_prefix}user 
                       WHERE superior = {$station_owner_uid} 
                       AND DATE(FROM_UNIXTIME(create_time)) >= '{$month_start}'";
    $month_user_data = $db->once_fetch_array($month_user_sql);
    $month_users = $month_user_data['count'];
    
    $total_user_sql = "SELECT COUNT(*) as count FROM {$db_prefix}user 
                       WHERE superior = {$station_owner_uid}";
    $total_user_data = $db->once_fetch_array($total_user_sql);
    $total_users = $total_user_data['count'];

    $uc_app_mode = isMobile();
    $uc_page_title = '店铺概览';
    include View::getUserView('_adaptive_header');
    require_once View::getUserView(isMobile() ? 'station/adaptive_index_mobile' : 'station/adaptive_index');
    include View::getUserView('_adaptive_footer');
    View::output();
}

if ($action == 'setting') {
    stationEnsureExtraColumns($db, $db_prefix);
    $sql = "select * from {$db_prefix}station_level where id = {$userData['station']['level_id']}";
    $station = $db->once_fetch_array($sql);
    // 重新从数据库读取分站数据，确保新字段可用
    $userStation = $db->once_fetch_array("SELECT * FROM {$db_prefix}station WHERE id = " . (int)$userData['station']['id']);

    $options_cache = $CACHE->readCache('options');
    $station_domain = isset($options_cache['station_domain']) ? $options_cache['station_domain'] : '';
    $station_domain = preg_split("/\r?\n/", $station_domain);
    $station_slug_mode = isset($options_cache['station_slug_mode']) ? $options_cache['station_slug_mode'] : '0';

    // 读取模板文件头的 Template Name
    $_resolveSlugName = function($basePath, $slug) {
        $mainFile = getTemplateBootstrapFile($basePath, $slug);
        if ($mainFile && is_file($mainFile)) {
            $head = @file_get_contents($mainFile, false, null, 0, 1024);
            if ($head && preg_match('/Template Name:\s*(.+)/i', $head, $m)) {
                return trim($m[1]);
            }
        }
        return $slug;
    };
    $_sid = (int)$userStation['id'];
    // 前台模板
    $_tplPcSlug  = stationGetSwitchSlug($db, $db_prefix, $_sid, 'tpl', 'pc_switch');
    $_tplTelSlug = stationGetSwitchSlug($db, $db_prefix, $_sid, 'tpl', 'tel_switch');
    $_tplPcName  = $_resolveSlugName(TPLS_PATH, $_tplPcSlug);
    $_tplTelName = $_resolveSlugName(TPLS_PATH, $_tplTelSlug);
    // 用户后台模板
    $_ucPcSlug   = stationGetSwitchSlug($db, $db_prefix, $_sid, 'user_tpl', 'pc_switch');
    $_ucTelSlug  = stationGetSwitchSlug($db, $db_prefix, $_sid, 'user_tpl', 'tel_switch');
    $_ucPcName   = $_resolveSlugName(USER_TPLS_PATH, $_ucPcSlug);
    $_ucTelName  = $_resolveSlugName(USER_TPLS_PATH, $_ucTelSlug);
    // 底部导航模板
    $_bnSlug     = stationGetSwitchSlug($db, $db_prefix, $_sid, 'bottom_nav', 'pc_switch');
    if ($_bnSlug === 'em_null_tpl') {
        $_bnName = '未启用';
    } else {
        $_bnName = $_resolveSlugName(BOTTOM_NAV_TPLS_PATH, $_bnSlug);
    }

    $uc_app_mode = isMobile();
    $uc_page_title = '店铺配置';
    include View::getUserView('_adaptive_header');
    require_once View::getUserView(isMobile() ? 'station/adaptive_setting_mobile' : 'station/adaptive_setting');
    include View::getUserView('_adaptive_footer');
    View::output();
}

if ($action == 'setting_basic') {
    stationEnsureExtraColumns($db, $db_prefix);
    $userStation = $db->once_fetch_array("SELECT * FROM {$db_prefix}station WHERE id = " . (int)$userData['station']['id']);
    $uc_app_mode = isMobile();
    $uc_page_title = '基础信息';
    include View::getUserView('_adaptive_header');
    require_once View::getUserView(isMobile() ? 'station/setting_basic_mobile' : 'station/setting_basic');
    include View::getUserView('_adaptive_footer');
    View::output();
}

if ($action == 'setting_notice') {
    $userStation = $db->once_fetch_array("SELECT * FROM {$db_prefix}station WHERE id = " . (int)$userData['station']['id']);
    $uc_app_mode = isMobile();
    $uc_page_title = '站内公告';
    include View::getUserView('_adaptive_header');
    require_once View::getUserView(isMobile() ? 'station/setting_notice_mobile' : 'station/setting_notice');
    include View::getUserView('_adaptive_footer');
    View::output();
}

if ($action == 'setting_domain') {
    $userStation = $db->once_fetch_array("SELECT * FROM {$db_prefix}station WHERE id = " . (int)$userData['station']['id']);
    $options_cache = $CACHE->readCache('options');
    $station_domain = isset($options_cache['station_domain']) ? $options_cache['station_domain'] : '';
    $station_domain = preg_split("/\r?\n/", $station_domain);
    $station_slug_mode = isset($options_cache['station_slug_mode']) ? $options_cache['station_slug_mode'] : '0';
    $uc_app_mode = isMobile();
    $uc_page_title = '域名配置';
    include View::getUserView('_adaptive_header');
    require_once View::getUserView(isMobile() ? 'station/setting_domain_mobile' : 'station/setting_domain');
    include View::getUserView('_adaptive_footer');
    View::output();
}

if ($action == 'setting_tpl') {
    $uc_app_mode = isMobile();
    $uc_page_title = '模板配置';
    include View::getUserView('_adaptive_header');
    require_once View::getUserView(isMobile() ? 'station/setting_tpl_mobile' : 'station/setting_tpl');
    include View::getUserView('_adaptive_footer');
    View::output();
}
// 分站模板商店已移除：模板统一由后台管理安装，分站直接读取已安装模板即可
if ($action == 'store_tpl') {
    header('Location: ?action=setting_tpl');
    exit;
}
// store_tpl_ajax 已移除（分站模板商店功能已下线，模板统一由后台管理）
if($action == 'store_tpl_ajax'){
    Output::error('模板商店功能已下线，请在后台管理端安装模板');
}
if($action == 'get_tpl'){
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $station = $userData['station'];

    // 获取所有已安装模板（扫描 TPLS_PATH 目录）
    $list = $Template_Model->getStationTemplates();

    // 从 station_plugin 读取当前启用的 PC/手机端模板
    $nonce_template = stationGetSwitchSlug($db, $db_prefix, $station['id'], 'tpl', 'pc_switch');
    $nonce_template_tel = stationGetSwitchSlug($db, $db_prefix, $station['id'], 'tpl', 'tel_switch');

    $post_data = [
        'station_unique' => $station['station_unique'],
        'apps'  => [],
    ];

    foreach($list as $key => $val){
        $list[$key]['switch'] = ($nonce_template !== 'em_null_tpl' && $nonce_template == $val['tplfile']) ? 'y' : 'n';
        $list[$key]['tel_switch'] = ($nonce_template_tel !== 'em_null_tpl' && $nonce_template_tel == $val['tplfile']) ? 'y' : 'n';
        $post_data['apps'][] = [
            'name' => $val['tplfile'],
            'version' => $val['version']
        ];
    }
    stationAttachTemplateLicenseInfo($list, 'tpl');
    stationDeactivateInvalidTemplateSwitches($db, $db_prefix, $station['id'], 'tpl', $nonce_template, $nonce_template_tel, $list);
    $post_data['apps'] = json_encode($post_data['apps']);


    $emcurl = new EmCurl();
    $emcurl->setPost($post_data);
    $emcurl->request(DC_LINE[CURRENT_LINE]['value'] . 'api/template/upgrade');
    $retStatus = $emcurl->getHttpStatus();
    $update_data = [];
    if ($retStatus !== MSGCODE_SUCCESS) {
//        Output::error('请求更新失败，可能是网络问题');
    }
    $response = $emcurl->getRespone();
    $ret = json_decode($response, 1);
    if (empty($ret)) {
//        Output::error('请求更新失败，可能是网络问题');
    }
    if ($ret['code'] === MSGCODE_EMKEY_INVALID) {
//        Output::error('未注册的pro版本');
    }
    if($ret['code'] == 200){
        $update_data = $ret['data'];
    }
//    d($ret);die;

    foreach($list as $key => $val){
        $list[$key]['update'] = 'n';
        foreach($update_data as $k => $v){
            if($v['name'] == $val['tplfile']){
                $list[$key]['update'] = 'y';
            }
        }
        $list[$key]['switch'] = (($list[$key]['license_invalid'] ?? 'n') !== 'y' && $nonce_template !== 'em_null_tpl' && $nonce_template == $val['tplfile']) ? 'y' : 'n';
        $list[$key]['tel_switch'] = (($list[$key]['license_invalid'] ?? 'n') !== 'y' && $nonce_template_tel !== 'em_null_tpl' && $nonce_template_tel == $val['tplfile']) ? 'y' : 'n';
    }
    output::data($list, count($list));
}

if($action == 'get_user_tpl'){
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $station = $userData['station'];
    $list = $Template_Model->getUserCenterTemplates();
    $nonce_template = stationGetSwitchSlug($db, $db_prefix, $station['id'], 'user_tpl', 'pc_switch');
    $nonce_template_tel = stationGetSwitchSlug($db, $db_prefix, $station['id'], 'user_tpl', 'tel_switch');

    foreach($list as $key => $val){
        $list[$key]['switch'] = ($nonce_template !== 'em_null_tpl' && $nonce_template === $val['tplfile']) ? 'y' : 'n';
        $list[$key]['tel_switch'] = ($nonce_template_tel !== 'em_null_tpl' && $nonce_template_tel === $val['tplfile']) ? 'y' : 'n';
        $list[$key]['update'] = 'n';
    }
    stationAttachTemplateLicenseInfo($list, 'user_tpl');
    stationDeactivateInvalidTemplateSwitches($db, $db_prefix, $station['id'], 'user_tpl', $nonce_template, $nonce_template_tel, $list);
    foreach($list as $key => $val){
        $list[$key]['switch'] = (($list[$key]['license_invalid'] ?? 'n') !== 'y' && $nonce_template !== 'em_null_tpl' && $nonce_template === $val['tplfile']) ? 'y' : 'n';
        $list[$key]['tel_switch'] = (($list[$key]['license_invalid'] ?? 'n') !== 'y' && $nonce_template_tel !== 'em_null_tpl' && $nonce_template_tel === $val['tplfile']) ? 'y' : 'n';
    }
    output::data($list, count($list));
}

if($action == 'get_bottom_nav_tpl'){
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $station = $userData['station'];
    $list = $Template_Model->getBottomNavTemplates();
    $nonce_template = stationGetSwitchSlug($db, $db_prefix, $station['id'], 'bottom_nav', 'pc_switch');

    foreach($list as $key => $val){
        $list[$key]['switch'] = ($nonce_template !== 'em_null_tpl' && $nonce_template === $val['tplfile']) ? 'y' : 'n';
        $list[$key]['tel_switch'] = 'n';
        $list[$key]['update'] = 'n';
    }
    stationAttachTemplateLicenseInfo($list, 'bottom_nav');
    $bottomNavTelSlug = null;
    stationDeactivateInvalidTemplateSwitches($db, $db_prefix, $station['id'], 'bottom_nav', $nonce_template, $bottomNavTelSlug, $list);
    foreach($list as $key => $val){
        $list[$key]['switch'] = (($list[$key]['license_invalid'] ?? 'n') !== 'y' && $nonce_template !== 'em_null_tpl' && $nonce_template === $val['tplfile']) ? 'y' : 'n';
    }
    output::data($list, count($list));
}

if ($action === 'upgrade') {
    $alias = isset($_GET['alias']) ? trim($_GET['alias']) : '';

    $alias = Input::postStrVar('alias');
    $safeAlias = preg_replace('/^([\w-]+)$/i', '$1', $alias);
    if ($safeAlias !== $alias || !is_dir(TPLS_PATH . $safeAlias . '/') || !checkTemplateBootstrap(TPLS_PATH, $safeAlias)) {
        Ret::error('模板不存在或已损坏');
    }
    $alias = $safeAlias;


    $url = DC_LINE[CURRENT_LINE]['value'] . 'api/template/down?plugin=' . $alias;
    $temp_file = emFetchFile($url);
    if (!$temp_file) {
        Ret::error('更新文件路径错误');
    }
    $unzip_path = '../content/templates/';
    $ret = emUnZip($temp_file, $unzip_path, 'tpl');
    @unlink($temp_file);
    switch ($ret) {
        case 0:
            runTemplateCallback(TPLS_PATH, $alias, 'callback_up');
            output::ok();
            break;
        case 1:
        case 2:
            output::error('更新失败');
            break;
        case 3:
            output::error('更新失败');
            break;
        default:
            output::error('更新失败');
    }
}

if($action == 'use'){ // 启用模板（电脑端）
    global $userData;
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $status = Input::postStrVar('status');
    if($status != 1){
        Ret::error('前台模板只支持切换，不支持完全关闭');
    }
    $tpl = trim((string)Input::postStrVar('tpl'));
    $safeTpl = preg_replace('/^([\w-]+)$/i', '$1', $tpl);
    if ($safeTpl !== $tpl || !is_dir(TPLS_PATH . $safeTpl . '/') || !checkTemplateBootstrap(TPLS_PATH, $safeTpl)) {
        Ret::error('模板不存在或已损坏');
    }
    stationVerifyTemplateLicenseOrError('tpl', $safeTpl);
    // 校验通过后再切换，避免未授权模板把当前模板关闭
    $db->update('station_plugin', ['pc_switch' => 'n'], [
        'station_id' => $userData['station']['id'],
        'type' => 'tpl'
    ]);
    stationUpsertSwitchState($db, $db_prefix, $userData['station']['id'], 'tpl', 'pc_switch', $safeTpl);
    runTemplateCallback(TPLS_PATH, $safeTpl, 'callback_init');

    Ret::success('操作成功');
}

if($action == 'use_tel'){ // 启用模板（手机端）
    global $userData;
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $status = Input::postStrVar('status');
    if($status != 1){
        Ret::error('前台模板只支持切换，不支持完全关闭');
    }
    $tpl = trim((string)Input::postStrVar('tpl'));
    $safeTpl = preg_replace('/^([\w-]+)$/i', '$1', $tpl);
    if ($safeTpl !== $tpl || !is_dir(TPLS_PATH . $safeTpl . '/') || !checkTemplateBootstrap(TPLS_PATH, $safeTpl)) {
        Ret::error('模板不存在或已损坏');
    }
    stationVerifyTemplateLicenseOrError('tpl', $safeTpl);
    // 校验通过后再切换，避免未授权模板把当前模板关闭
    $db->update('station_plugin', ['tel_switch' => 'n'], [
        'station_id' => $userData['station']['id'],
        'type' => 'tpl'
    ]);
    stationUpsertSwitchState($db, $db_prefix, $userData['station']['id'], 'tpl', 'tel_switch', $safeTpl);
    runTemplateCallback(TPLS_PATH, $safeTpl, 'callback_init');

    Ret::success('操作成功');
}

if($action == 'use_user_tpl'){
    global $userData;
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $status = Input::postStrVar('status');
    if($status != 1){
        Ret::error('用户后台模板只支持切换，不支持完全关闭');
    }
    $tpl = trim((string)Input::postStrVar('tpl'));
    $safeTpl = preg_replace('/^([\w-]+)$/i', '$1', $tpl);
    if ($safeTpl !== $tpl || !is_dir(USER_TPLS_PATH . $safeTpl . '/') || !checkTemplateBootstrap(USER_TPLS_PATH, $safeTpl)) {
        Ret::error('用户后台模板不存在或已损坏');
    }
    stationVerifyTemplateLicenseOrError('user_tpl', $safeTpl);
    $db->update('station_plugin', ['pc_switch' => 'n'], [
        'station_id' => $userData['station']['id'],
        'type' => 'user_tpl'
    ]);
    stationUpsertSwitchState($db, $db_prefix, $userData['station']['id'], 'user_tpl', 'pc_switch', $safeTpl);
    runTemplateCallback(USER_TPLS_PATH, $safeTpl, 'callback_init');
    Ret::success('操作成功');
}

if($action == 'use_user_tpl_tel'){
    global $userData;
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $status = Input::postStrVar('status');
    if($status != 1){
        Ret::error('用户后台模板只支持切换，不支持完全关闭');
    }
    $tpl = trim((string)Input::postStrVar('tpl'));
    $safeTpl = preg_replace('/^([\w-]+)$/i', '$1', $tpl);
    if ($safeTpl !== $tpl || !is_dir(USER_TPLS_PATH . $safeTpl . '/') || !checkTemplateBootstrap(USER_TPLS_PATH, $safeTpl)) {
        Ret::error('用户后台模板不存在或已损坏');
    }
    stationVerifyTemplateLicenseOrError('user_tpl', $safeTpl);
    $db->update('station_plugin', ['tel_switch' => 'n'], [
        'station_id' => $userData['station']['id'],
        'type' => 'user_tpl'
    ]);
    stationUpsertSwitchState($db, $db_prefix, $userData['station']['id'], 'user_tpl', 'tel_switch', $safeTpl);
    runTemplateCallback(USER_TPLS_PATH, $safeTpl, 'callback_init');
    Ret::success('操作成功');
}

if($action == 'use_bottom_nav'){
    global $userData;
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $status = Input::postStrVar('status');
    if($status == 1){
        $tpl = trim((string)Input::postStrVar('tpl'));
        $safeTpl = preg_replace('/^([\w-]+)$/i', '$1', $tpl);
        if ($safeTpl !== $tpl || !is_dir(BOTTOM_NAV_TPLS_PATH . $safeTpl . '/') || !checkTemplateBootstrap(BOTTOM_NAV_TPLS_PATH, $safeTpl) || !file_exists(BOTTOM_NAV_TPLS_PATH . $safeTpl . '/render.php')) {
            Ret::error('底部导航模板不存在或已损坏');
        }
        stationVerifyTemplateLicenseOrError('bottom_nav', $safeTpl);
        $db->update('station_plugin', ['pc_switch' => 'n'], [
            'station_id' => $userData['station']['id'],
            'type' => 'bottom_nav'
        ]);
        stationUpsertSwitchState($db, $db_prefix, $userData['station']['id'], 'bottom_nav', 'pc_switch', $safeTpl);
        runTemplateCallback(BOTTOM_NAV_TPLS_PATH, $safeTpl, 'callback_init');
    } else {
        $db->update('station_plugin', ['pc_switch' => 'n'], [
            'station_id' => $userData['station']['id'],
            'type' => 'bottom_nav'
        ]);
        stationUpsertSwitchState($db, $db_prefix, $userData['station']['id'], 'bottom_nav', 'pc_switch', 'em_null_tpl');
    }
    Ret::success('操作成功');
}

if ($action == 'master_sort') {
    $uc_app_mode = isMobile();
    $uc_page_title = '店铺配置';
    include View::getUserView('_adaptive_header');
    require_once View::getUserView('station/master_sort');
    include View::getUserView('_adaptive_footer');
    View::output();
}
if ($action == 'master_goods') {
    $uc_app_mode = isMobile();
    $uc_page_title = '商品管理';
    include View::getUserView('_adaptive_header');
    require_once View::getUserView(isMobile() ? 'station/master_goods_mobile' : 'station/master_goods');
    include View::getUserView('_adaptive_footer');
    View::output();
}
if($action == 'master_goods_index'){
    global $userData;
    $page_num = Input::getIntVar('limit');
    $page = Input::getIntVar('page');
    $start = ($page - 1) * $page_num;
    $station_id = (int)($userData['station']['id'] ?? 0);

    // 站长会员等级（用于按"我的等级"计算拿货价，与 Order_Model::addCommission 保持同口径）
    $stationOwnerUid = (int)($userData['station']['user_id'] ?? UID);
    $stationOwner = $stationOwnerUid > 0 ? $User_Model->getOneUser($stationOwnerUid) : [];
    $stationLevel = null;
    if (!empty($stationOwner) && class_exists('Level_Service')) {
        $stationLevel = Level_Service::getActiveLevelId($stationOwner);
    }
    if ($stationLevel === null || (int)$stationLevel <= 0) {
        try {
            $memberModel = new Member_Model();
            $stationLevel = (int)$memberModel->getDefaultLevelId();
        } catch (Throwable $_e) {
            $stationLevel = -1;
        }
    }

    // 确保 dc_goods 五层价格新字段存在（老库自动补齐）
    if (class_exists('Level_Price')) {
        Level_Price::ensureSchema();
    }

    // 搜索条件
    $searchTitle = trim(Input::getStrVar('title', ''));
    $searchStatus = trim(Input::getStrVar('status', ''));

    // 限定当前分店的 station_goods.premium / custom_name / is_show
    $whereExtra = '';
    if ($searchTitle !== '') {
        $safeTitle = addslashes($searchTitle);
        $whereExtra .= " and (g.title like '%{$safeTitle}%' or sg.custom_name like '%{$safeTitle}%')";
    }
    if ($searchStatus === 'y') {
        $whereExtra .= " and sg.is_show='y'";
    } elseif ($searchStatus === 'n') {
        $whereExtra .= " and (sg.is_show='n' or sg.is_show is null)";
    }

    $sql = "select g.id, g.title, g.cover, g.sales, g.stock,
             g.profit_rule_id, g.profit_ratio, g.single_rule_id,
             sg.custom_name, sg.is_show, sg.premium
         from {$db_prefix}goods g
         left join {$db_prefix}station_goods sg on g.id=sg.goods_id and sg.station_id={$station_id}
         where g.is_on_shelf=1 and g.delete_time is null and g.station_id=0{$whereExtra} order by g.id desc
         limit $start, $page_num";
    $list = $db->fetch_all($sql);

    if (!empty($list)) {
        $ids = array_map('intval', array_column($list, 'id'));
        $idsStr = implode(',', $ids);
        $skuRows = $db->fetch_all(
            "SELECT goods_id, sku, cost_price, user_price, guest_price FROM {$db_prefix}skus WHERE goods_id IN ({$idsStr})"
        );
        $skusByGoods = [];
        foreach ($skuRows as $sr) {
            $skusByGoods[(int)$sr['goods_id']][] = $sr;
        }

        foreach ($list as &$val) {
            // premium 默认 10%（与已有 fallback 行为一致），统一以"百分比"返回前端
            $premiumPct = ($val['premium'] === null || $val['premium'] === '')
                ? 10.0
                : (float)$val['premium'] * 100;
            $val['premium'] = $premiumPct;
            $val['is_show'] = $val['is_show'] ?? 'y';

            $goodsRow = [
                'id'             => (int)$val['id'],
                'profit_rule_id' => (int)($val['profit_rule_id'] ?? 0),
                'profit_ratio'   => (float)($val['profit_ratio'] ?? 100),
                'single_rule_id' => (int)($val['single_rule_id'] ?? 0),
            ];
            $skus = $skusByGoods[(int)$val['id']] ?? [];

            // 按 SKU 逐一算"前台游客售价"（含分店加价）和"站长拿货价"，取售价最低的那个 SKU 作为代表
            $bestSell = null;
            $bestCost = 0;
            foreach ($skus as $sku) {
                $skuRow = [
                    'sku'         => isset($sku['sku']) ? (string)$sku['sku'] : '0',
                    'cost_price'  => (int)$sku['cost_price'],
                    'user_price'  => (int)$sku['user_price'],
                    'guest_price' => (int)$sku['guest_price'],
                ];
                // 站长按自己等级计算的拿货价（不含加价，与下单分佣口径一致）
                $costCents = (int)Level_Price::calculate($skuRow, $goodsRow, (int)$stationLevel);
                // 前台游客看到的售价（含分站加价，已整合在 Level_Price::calculate 内部）
                $sellCents = (int)Level_Price::calculate($skuRow, $goodsRow, -1, $premiumPct / 100);

                if ($bestSell === null || $sellCents < $bestSell) {
                    $bestSell = $sellCents;
                    $bestCost = $costCents;
                }
            }
            $bestSell = $bestSell === null ? 0 : $bestSell;
            $val['cost_yuan']   = number_format($bestCost / 100, 2, '.', '');
            $val['sell_yuan']   = number_format($bestSell / 100, 2, '.', '');
            $val['profit_yuan'] = number_format(max(0, $bestSell - $bestCost) / 100, 2, '.', '');
        }
        unset($val);
    }

    $totalRow = $db->once_fetch_array(
        "select count(g.id) total from {$db_prefix}goods g
         left join {$db_prefix}station_goods sg on g.id=sg.goods_id and sg.station_id={$station_id}
         where g.is_on_shelf=1 and g.delete_time is null and g.station_id=0{$whereExtra}"
    );
    output::data($list, (int)($totalRow['total'] ?? 0));
}
if ($action == 'master_goods_stats') {
    global $userData;
    $station_id = (int)($userData['station']['id'] ?? 0);
    // 总主站商品数
    $totalRow = $db->once_fetch_array(
        "SELECT COUNT(g.id) AS total FROM {$db_prefix}goods g WHERE g.is_on_shelf=1 AND g.delete_time IS NULL AND g.station_id=0"
    );
    $totalGoods = (int)($totalRow['total'] ?? 0);
    // 已下架（station_goods.is_show='n' 的才算下架，无记录或 is_show='y' 算上架）
    $hiddenRow = $db->once_fetch_array(
        "SELECT COUNT(*) AS cnt FROM {$db_prefix}goods g
         INNER JOIN {$db_prefix}station_goods sg ON sg.goods_id=g.id AND sg.station_id={$station_id}
         WHERE g.is_on_shelf=1 AND g.delete_time IS NULL AND g.station_id=0 AND sg.is_show='n'"
    );
    $hiddenCount = (int)($hiddenRow['cnt'] ?? 0);
    $visibleCount = $totalGoods - $hiddenCount;
    // 平均加价比例（所有 station_goods 记录，无记录默认 10%）
    $premRow = $db->once_fetch_array(
        "SELECT AVG(IFNULL(sg.premium, 0.10) * 100) AS avg_premium
         FROM {$db_prefix}goods g
         LEFT JOIN {$db_prefix}station_goods sg ON sg.goods_id=g.id AND sg.station_id={$station_id}
         WHERE g.is_on_shelf=1 AND g.delete_time IS NULL AND g.station_id=0"
    );
    $avgPremium = $premRow && $premRow['avg_premium'] !== null ? round((float)$premRow['avg_premium'], 1) : 10.0;
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['code' => 0, 'visible' => $visibleCount, 'hidden' => $hiddenCount, 'avg_premium' => $avgPremium]);
    exit;
}
if ($action == 'order') {
    $uc_app_mode = isMobile();
    $uc_page_title = '分店订单';
    include View::getUserView('_adaptive_header');
    require_once View::getUserView(isMobile() ? 'station/order_mobile' : 'station/order');
    include View::getUserView('_adaptive_footer');
    View::output();
}
if($action == 'order_index'){
    $station_id = $userData['station']['id'];
    $page = Input::getIntVar('page', 1);
    $limit = Input::getIntVar('limit', 10);
    $start = ($page - 1) * $limit;
    $sort1 = Input::getStrVar('field', 'uid');
    $sort2 = Input::getStrVar('order', 'desc');
    $order_by = "order by {$sort1} {$sort2}";


    $where  = [];
    $where['email_username'] = Input::getStrVar('email_username');
    $where['out_trade_no'] = Input::getStrVar('out_trade_no');
    $where['goods_title'] = Input::getStrVar('goods_title');
    $where['client_ip'] = Input::getStrVar('client_ip');
    $where['order_required'] = Input::getStrVar('order_required');
    $where['station_id'] = $station_id;

    $orderNum = $orderModel->getOrderNum($where);
    $order = $orderModel->getOrderForAdmin($start, $limit, $where);

    // 批量查询本页订单的分佣金额
    $tradeNos = [];
    foreach ($order as $val) {
        if (!empty($val['out_trade_no'])) $tradeNos[] = $val['out_trade_no'];
    }
    $commissionMap = [];
    if (!empty($tradeNos)) {
        $station_owner_uid = (int)UID;
        $logTable = "{$db_prefix}user_log";
        $likeParts = [];
        foreach ($tradeNos as $_no) {
            $likeParts[] = "content LIKE '%" . addslashes($_no) . "%'";
        }
        $commRows = $db->fetch_all("SELECT amount, content FROM {$logTable} WHERE uid={$station_owner_uid} AND type='station_commission' AND (" . implode(' OR ', $likeParts) . ")");
        foreach ($commRows as $_r) {
            foreach ($tradeNos as $_no) {
                if (strpos($_r['content'], $_no) !== false) {
                    $commissionMap[$_no] = number_format((float)$_r['amount'], 2);
                    break;
                }
            }
        }
    }

    foreach($order as $key => $val){
        $order[$key]['pay_time'] = empty($val['pay_time']) ? '' : date('Y-m-d H:i:s', $val['pay_time']);
        $order[$key]['amount'] = number_format($val['amount'], 2);
        $order[$key]['commission'] = isset($commissionMap[$val['out_trade_no']]) ? $commissionMap[$val['out_trade_no']] : '0.00';
    }

    // 全局统计（不受分页影响）
    $statAll = $db->once_fetch_array("SELECT COUNT(id) cnt, IFNULL(SUM(amount),0) amt FROM {$db_prefix}order WHERE station_id={$station_id} AND delete_time IS NULL");
    $statPaid = $db->once_fetch_array("SELECT COUNT(id) cnt FROM {$db_prefix}order WHERE station_id={$station_id} AND delete_time IS NULL AND pay_time IS NOT NULL AND pay_time > 0");
    $station_owner_uid_g = (int)UID;
    $statComm = $db->once_fetch_array("SELECT IFNULL(SUM(amount),0) amt FROM {$db_prefix}user_log WHERE uid={$station_owner_uid_g} AND type='station_commission' AND amount > 0");

    $extra = [
        'stat_total'      => (int)($statAll['cnt'] ?? 0),
        'stat_amount'     => number_format(($statAll['amt'] ?? 0) / 100, 2),
        'stat_paid'       => (int)($statPaid['cnt'] ?? 0),
        'stat_commission' => number_format((float)($statComm['amt'] ?? 0), 2),
    ];
    output::data($order, $orderNum, $extra);
}
if ($action == 'master_sort_edit') {
    global $userData;
    $station_id = $userData['station']['id'];
    $sort_id = Input::getIntVar('sort_id');
    $sql = "select * from {$db_prefix}station_sort where station_id=$station_id and sort_id=$sort_id";
    $station_sort = $db->once_fetch_array($sql);
    $sort = $db->once_fetch_array("select * from {$db_prefix}sort where sid = $sort_id");
    include View::getUserView('open_head');
    require_once View::getUserView('station/master_sort_edit');
    include View::getUserView('open_foot');
    View::output();
}
if ($action == 'master_goods_edit') {
    global $userData;
    $station_id = $userData['station']['id'];
    $goods_id = Input::getIntVar('goods_id');
    $sql = "select * from {$db_prefix}station_goods where station_id=$station_id and goods_id=$goods_id";
    $station_goods = $db->once_fetch_array($sql);
    $goods = $db->once_fetch_array("select * from {$db_prefix}goods where id = $goods_id");
    include View::getUserView('open_head');
    require_once View::getUserView('station/master_goods_edit');
    include View::getUserView('open_foot');
    View::output();
}
if ($action == 'master_goods_premium') {
    global $userData;
    include View::getUserView('open_head');
    require_once View::getUserView('station/master_goods_premium');
    include View::getUserView('open_foot');
    View::output();
}
if($action == 'master_sort_hide'){
    global $userData;
    $station_id = $userData['station']['id'];
    $ids = Input::postStrVar('ids');
    $sql = "select * from {$db_prefix}station_sort where station_id=$station_id and sort_id in($ids)";
    $station_sort = $db->fetch_all($sql);
    $sql = "select * from {$db_prefix}sort where sid in($ids) and station_id=0";
    $sort = $db->fetch_all($sql);
    foreach($sort as $val){
        $is_exists = false;
        foreach($station_sort as $v){
            if($val['sid'] == $v['sort_id']){
                $is_exists = true;
            }
        }
        if($is_exists){
            $db->update('station_sort', [
                'is_show' => 'n'
            ], ['station_id' => $station_id, 'sort_id' => $val['sid']]);
        }else{
            $db->add('station_sort', [
                'station_id' => $station_id,
                'sort_id' => $val['sid'],
                'type' => 'goods',
                'is_show' => 'n',
            ]);
        }
    }
    Ret::success();
}
if($action == 'master_goods_hide'){
    global $userData;
    $stationModel = new Station_Model();
    if (!empty($userData['station']) && !$stationModel->checkPerm($userData['station'], 'perm_goodsstate')) {
        Ret::error($stationModel->permTip('perm_goodsstate', $userData['station']));
    }
    $station_id = (int)$userData['station']['id'];
    $scope = trim(Input::postStrVar('scope', ''));
    $searchTitle = trim(Input::postStrVar('title', ''));
    if ($scope === 'all') {
        $whereExtra = '';
        if ($searchTitle !== '') {
            $safeTitle = addslashes($searchTitle);
            $whereExtra .= " and (g.title like '%{$safeTitle}%' or sg.custom_name like '%{$safeTitle}%')";
        }
        $sql = "select g.id from {$db_prefix}goods g
            left join {$db_prefix}station_goods sg on g.id=sg.goods_id and sg.station_id={$station_id}
            where g.is_on_shelf=1 and g.delete_time is null and g.station_id=0{$whereExtra}";
        $goods = $db->fetch_all($sql);
    } else {
        $idsRaw = Input::postStrVar('ids');
        $ids = array_filter(array_unique(array_map('intval', explode(',', $idsRaw))));
        if (empty($ids)) Ret::error('请选择要操作的商品');
        $idsStr = implode(',', $ids);
        $sql = "select id from {$db_prefix}goods where id in($idsStr) and station_id=0 and is_on_shelf=1 and delete_time is null";
        $goods = $db->fetch_all($sql);
    }
    if (empty($goods)) Ret::success('success', ['count' => 0]);
    $goodsIds = array_map('intval', array_column($goods, 'id'));
    $idsStr = implode(',', $goodsIds);
    $sql = "select goods_id from {$db_prefix}station_goods where station_id=$station_id and goods_id in($idsStr)";
    $station_goods = $db->fetch_all($sql);
    $existsIds = [];
    foreach($station_goods as $v){
        $existsIds[(int)$v['goods_id']] = true;
    }
    foreach($goods as $val){
        $goods_id = (int)$val['id'];
        if(isset($existsIds[$goods_id])){
            $db->update('station_goods', [
                'is_show' => 'n'
            ], ['station_id' => $station_id, 'goods_id' => $goods_id]);
        }else{
            $db->add('station_goods', [
                'station_id' => $station_id,
                'goods_id' => $goods_id,
                'premium' => 0.1,
                'is_show' => 'n',
            ]);
        }
    }
    Ret::success('success', ['count' => count($goods)]);
}
if($action == 'master_sort_show'){
    global $userData;
    $station_id = $userData['station']['id'];
    $ids = Input::postStrVar('ids');
    $sql = "select * from {$db_prefix}station_sort where station_id=$station_id and sort_id in($ids)";
    $station_sort = $db->fetch_all($sql);
    $sql = "select * from {$db_prefix}sort where sid in($ids) and station_id=0";
    $sort = $db->fetch_all($sql);
    foreach($sort as $val){
        $is_exists = false;
        foreach($station_sort as $v){
            if($val['sid'] == $v['sort_id']){
                $is_exists = true;
            }
        }
        if($is_exists){
            $db->update('station_sort', [
                'is_show' => 'y'
            ], ['station_id' => $station_id, 'sort_id' => $val['sid']]);
        }else{
            $db->add('station_sort', [
                'station_id' => $station_id,
                'sort_id' => $val['sid'],
                'type' => 'goods',
                'is_show' => 'y',
            ]);
        }
    }
    Ret::success();
}
if($action == 'master_goods_show'){
    global $userData;
    $stationModel = new Station_Model();
    if (!empty($userData['station']) && !$stationModel->checkPerm($userData['station'], 'perm_goodsstate')) {
        Ret::error($stationModel->permTip('perm_goodsstate', $userData['station']));
    }
    $station_id = (int)$userData['station']['id'];
    $scope = trim(Input::postStrVar('scope', ''));
    $searchTitle = trim(Input::postStrVar('title', ''));
    if ($scope === 'all') {
        $whereExtra = '';
        if ($searchTitle !== '') {
            $safeTitle = addslashes($searchTitle);
            $whereExtra .= " and (g.title like '%{$safeTitle}%' or sg.custom_name like '%{$safeTitle}%')";
        }
        $sql = "select g.id from {$db_prefix}goods g
            left join {$db_prefix}station_goods sg on g.id=sg.goods_id and sg.station_id={$station_id}
            where g.is_on_shelf=1 and g.delete_time is null and g.station_id=0{$whereExtra}";
        $goods = $db->fetch_all($sql);
    } else {
        $idsRaw = Input::postStrVar('ids');
        $ids = array_filter(array_unique(array_map('intval', explode(',', $idsRaw))));
        if (empty($ids)) Ret::error('请选择要操作的商品');
        $idsStr = implode(',', $ids);
        $sql = "select id from {$db_prefix}goods where id in($idsStr) and station_id=0 and is_on_shelf=1 and delete_time is null";
        $goods = $db->fetch_all($sql);
    }
    if (empty($goods)) Ret::success('success', ['count' => 0]);
    $goodsIds = array_map('intval', array_column($goods, 'id'));
    $idsStr = implode(',', $goodsIds);
    $sql = "select goods_id from {$db_prefix}station_goods where station_id=$station_id and goods_id in($idsStr)";
    $station_goods = $db->fetch_all($sql);
    $existsIds = [];
    foreach($station_goods as $v){
        $existsIds[(int)$v['goods_id']] = true;
    }
    foreach($goods as $val){
        $goods_id = (int)$val['id'];
        if(isset($existsIds[$goods_id])){
            $db->update('station_goods', [
                'is_show' => 'y'
            ], ['station_id' => $station_id, 'goods_id' => $goods_id]);
        }else{
            $db->add('station_goods', [
                'station_id' => $station_id,
                'goods_id' => $goods_id,
                'premium' => 0.1,
                'is_show' => 'y',
            ]);
        }
    }
    Ret::success('success', ['count' => count($goods)]);
}
if($action == 'master_goods_premium_ajax'){
    // 分店等级权限：修改商品价格
    global $userData;
    $stationModel = new Station_Model();
    if (!empty($userData['station']) && !$stationModel->checkPerm($userData['station'], 'perm_setprice')) {
        Ret::error($stationModel->permTip('perm_setprice', $userData['station']));
    }
    $station_id = $userData['station']['id'];
    $premiumInput = (float)Input::postStrVar('premium', 0);
    $premium = $premiumInput / 100;
    $sql = "select * from {$db_prefix}station_goods where station_id=$station_id";
    $station_goods = $db->fetch_all($sql);
    $sql = "select * from {$db_prefix}goods where station_id=0 and is_on_shelf=1 and delete_time is null";
    $goods = $db->fetch_all($sql);
    foreach($goods as $val){
        $is_exists = false;
        foreach($station_goods as $v){
            if($val['id'] == $v['goods_id']){
                $is_exists = true;
            }
        }
        if($is_exists){
            $db->update('station_goods', [
                'premium' => $premium
            ], ['station_id' => $station_id, 'goods_id' => $val['id']]);
        }else{
            $db->add('station_goods', [
                'station_id' => $station_id,
                'goods_id' => $val['id'],
                'premium' => $premium,
                'is_show' => 'n',
            ]);
        }
    }
    Ret::success();
}
if($action == 'master_sort_edit_ajax'){
    $sort_id = Input::postIntVar('sort_id');
    $custom_name = Input::postStrVar('custom_name');
    global $userData;
    $station_id = $userData['station']['id'];
    $sql = "select * from {$db_prefix}station_sort where station_id=$station_id and sort_id=$sort_id";
    $station_sort = $db->once_fetch_array($sql);
    if($station_sort){
        $db->update('station_sort', [
            'custom_name' => $custom_name
        ], ['id' => $station_sort['id']]);
    }else{
        $db->add('station_sort', [
            'station_id' => $station_id,
            'sort_id' => $sort_id,
            'type' => 'goods',
            'is_show' => 'n',
            'custom_name' => $custom_name
        ]);
    }
    Ret::success();
}
if($action == 'master_goods_edit_ajax'){
    // 分店等级权限：修改商品价格（含此处的 premium 加价）
    global $userData;
    $stationModel = new Station_Model();
    if (!empty($userData['station']) && !$stationModel->checkPerm($userData['station'], 'perm_setprice')) {
        Ret::error($stationModel->permTip('perm_setprice', $userData['station']));
    }
    $goods_id = Input::postIntVar('goods_id');
    $custom_name = Input::postStrVar('custom_name');
    $premiumInput = (float)Input::postStrVar('premium', 0);
    $premium = $premiumInput / 100;
    $station_id = $userData['station']['id'];
    $sql = "select * from {$db_prefix}station_goods where station_id=$station_id and goods_id=$goods_id";
    $station_goods = $db->once_fetch_array($sql);
    if($station_goods){
        $db->update('station_goods', [
            'custom_name' => $custom_name,
            'premium' => $premium
        ], ['id' => $station_goods['id']]);
    }else{
        $db->add('station_goods', [
            'station_id' => $station_id,
            'goods_id' => $goods_id,
            'is_show' => 'n',
            'custom_name' => $custom_name,
            'premium' => $premium
        ]);
    }
    Ret::success();
}

if($action == 'master_sort_index'){
    $sql = "select s.*, ss.custom_name, ss.is_show from {$db_prefix}sort s  
         left join {$db_prefix}station_sort ss on s.sid=ss.sort_id 
         where s.type='goods' and s.station_id=0 order by s.taxis desc, s.sid asc";
    $list = $db->fetch_all($sql);
    output::data($list, count($list));
}
if($action == 'master_goods_switch'){
    // 分店等级权限：商品上下架
    global $userData;
    $stationModel = new Station_Model();
    if (!empty($userData['station']) && !$stationModel->checkPerm($userData['station'], 'perm_goodsstate')) {
        Ret::error($stationModel->permTip('perm_goodsstate', $userData['station']));
    }
    $goods_id = Input::postIntVar('id');
    $is_show = Input::postStrVar('is_show');
    $station_id = $userData['station']['id'];
    $sql = "select id from {$db_prefix}station_goods where goods_id=$goods_id and station_id=$station_id";
    $station_sort = $db->once_fetch_array($sql);
    if($station_sort){
        $db->update('station_goods', [
            'is_show' => $is_show
        ], ['id' => $station_sort['id']]);
    }else{
        $db->add('station_goods', [
            'station_id' => $station_id,
            'goods_id' => $goods_id,
            'premium' => 0.1,
            'is_show' => $is_show
        ]);
    }
    Ret::success();
}
if($action == 'master_sort_switch'){
    global $userData;
    $sort_id = Input::postIntVar('id');
    $is_show = Input::postStrVar('is_show');
    $station_id = $userData['station']['id'];
    $sql = "select id from {$db_prefix}station_sort where sort_id=$sort_id and station_id=$station_id";
    $station_sort = $db->once_fetch_array($sql);
    if($station_sort){
        $db->update('station_sort', [
            'is_show' => $is_show
        ], ['id' => $station_sort['id']]);
    }else{
        $db->add('station_sort', [
            'station_id' => $station_id,
            'sort_id' => $sort_id,
            'type' => 'goods',
            'is_show' => $is_show
        ]);
    }
    Ret::success();
}

if($action == 'setting_ajax'){
    $name = Input::postStrVar('name');
    $title = Input::postStrVar('title');
    $master_sort = Input::postIntVar('master_sort');
    $master_goods = Input::postIntVar('master_goods');
    $domain_2_prefix = Input::postStrVar('domain_2_prefix');
    $domain_2_suffix = Input::postStrVar('domain_2_suffix');

    // 读取后台配置
    $options_cache = $CACHE->readCache('options');
    $retain_str = isset($options_cache['station_domain_retain']) ? trim($options_cache['station_domain_retain']) : '';
    $domain_change_price = isset($options_cache['station_domain_change_price']) ? (float)$options_cache['station_domain_change_price'] : 0;

    // 保留前缀校验
    if(!empty($domain_2_prefix) && !empty($retain_str)){
        $retain_list = array_map('trim', explode(',', strtolower($retain_str)));
        $prefix_lower = strtolower(trim($domain_2_prefix));
        if(in_array($prefix_lower, $retain_list, true)){
            Ret::error('域名前缀 "' . htmlspecialchars($domain_2_prefix) . '" 为系统保留字，不可使用');
        }
    }

    if(!empty($domain_2_prefix)){
        if(empty($domain_2_suffix)){
            Ret::error('主站未配置二级域名后缀，暂无法设置二级域名');
        }else{
            $res = $db->once_fetch_array("select * from {$db_prefix}station where domain_2_prefix != '' and domain_2_prefix is not null and domain_2_prefix = '{$domain_2_prefix}' and domain_2_suffix = '{$domain_2_suffix}' and id != {$userData['station']['id']}");
            if(!empty($res)){
                Ret::error('该二级域名的前缀已被占用，请填写其他前缀或选择其他后缀');
            }
        }

    }


    $domain = Input::postStrVar('domain');
    $slug = trim(Input::postStrVar('slug'));
    $site_subtitle = Input::postStrVar('site_subtitle');
    $roll_notice = Input::postStrVar('roll_notice');
    $home_notice = Input::postStrVar('home_notice');

    // slug（路径后缀标识）校验
    $slug_mode = isset($options_cache['station_slug_mode']) ? $options_cache['station_slug_mode'] : '0';
    if(!empty($slug) && $slug_mode === '1'){
        if(!preg_match('/^[a-zA-Z0-9_-]{2,50}$/', $slug)){
            Ret::error('店铺标识只能包含字母、数字、下划线和连字符，长度 2-50 位');
        }
        // 保留字检查（复用域名保留前缀列表）
        if(!empty($retain_str)){
            if(in_array(strtolower($slug), $retain_list ?? array_map('trim', explode(',', strtolower($retain_str))), true)){
                Ret::error('店铺标识 "' . htmlspecialchars($slug) . '" 为系统保留字，不可使用');
            }
        }
        // 唯一性检查
        $slug_exists = $db->once_fetch_array("select id from {$db_prefix}station where slug = '{$slug}' and id != {$userData['station']['id']}");
        if(!empty($slug_exists)){
            Ret::error('店铺标识 "' . htmlspecialchars($slug) . '" 已被其他分店使用');
        }
    }

    // 修改域名扣费逻辑（首次绑定免费，修改时收费）
    if($domain_change_price > 0){
        $old_station = $db->once_fetch_array("select domain_2_prefix, domain_2_suffix, domain from {$db_prefix}station where id = {$userData['station']['id']}");
        $old_d2 = trim(($old_station['domain_2_prefix'] ?? '') . ($old_station['domain_2_suffix'] ?? ''));
        $old_d  = trim($old_station['domain'] ?? '');
        $new_d2 = trim($domain_2_prefix . $domain_2_suffix);
        $new_d  = trim($domain);

        // 二级域名或独立域名发生变更（且原值不为空 = 非首次绑定）
        $d2_changed = ($new_d2 !== $old_d2 && !empty($old_d2));
        $d_changed  = ($new_d !== $old_d && !empty($old_d));

        if($d2_changed || $d_changed){
            $user_row = $db->once_fetch_array("select money from {$db_prefix}user where uid = " . UID);
            $user_balance = (float)($user_row['money'] ?? 0);
            if($user_balance < $domain_change_price){
                Ret::error('修改域名需扣除 ¥' . number_format($domain_change_price, 2) . '，您的余额不足（当前 ¥' . number_format($user_balance, 2) . '）');
            }
            $balanceModel = new Balance_Model();
            $balanceModel->dec(UID, $domain_change_price, '修改分店域名扣费');
        }
    }

    // 前缀为空时，后缀和完整二级域名一起清空
    if (empty($domain_2_prefix)) {
        $domain_2_suffix = '';
    }
    $update = [
        'name' => $name,
        'title' => $title,
        'site_subtitle' => $site_subtitle,
        'master_sort' => $master_sort,
        'master_goods' => $master_goods,
        'domain_2_prefix' => $domain_2_prefix,
        'domain_2_suffix' => $domain_2_suffix,
        'domain_2' => ($domain_2_prefix !== '' && $domain_2_suffix !== '') ? $domain_2_prefix . $domain_2_suffix : '',
        'domain' => $domain,
        'slug' => ($slug_mode === '1' && $slug !== '') ? $slug : null,
        'roll_notice' => $roll_notice,
        'home_notice' => $home_notice
    ];
    $db->update('station', $update, ['id' => $userData['station']['id']]);
    if($master_sort == 1){

    }
    if($master_goods == 1){

    }

    Ret::success('已保存');
}

// ======== 分离保存：基础信息（含 SEO + 底部信息） ========
if ($action == 'setting_basic_ajax') {
    stationEnsureExtraColumns($db, $db_prefix);
    $name = Input::postStrVar('name');
    $title = Input::postStrVar('title');
    $site_subtitle = Input::postStrVar('site_subtitle');
    $master_sort = Input::postIntVar('master_sort');
    $master_goods = Input::postIntVar('master_goods');
    $site_description = Input::postStrVar('site_description');
    $site_key = Input::postStrVar('site_key');
    $log_title_style = Input::postStrVar('log_title_style', '0');
    if (!in_array($log_title_style, ['0', '1', '2'])) $log_title_style = '0';
    $icp = Input::postStrVar('icp');
    $footer_info = isset($_POST['footer_info']) ? $_POST['footer_info'] : '';
    $user_agreement = isset($_POST['user_agreement']) ? $_POST['user_agreement'] : '';
    $privacy_policy = isset($_POST['privacy_policy']) ? $_POST['privacy_policy'] : '';
    $logo = Input::postStrVar('logo');
    $favicon = Input::postStrVar('favicon');

    $update = [
        'name' => $name,
        'title' => $title,
        'site_subtitle' => $site_subtitle,
        'master_sort' => $master_sort,
        'master_goods' => $master_goods,
        'site_description' => $site_description,
        'site_key' => $site_key,
        'log_title_style' => (int)$log_title_style,
        'icp' => $icp,
        'footer_info' => $footer_info,
        'user_agreement' => $user_agreement,
        'privacy_policy' => $privacy_policy,
        'logo' => $logo,
        'favicon' => $favicon,
    ];
    $db->update('station', $update, ['id' => $userData['station']['id']]);
    Ret::success('基础信息已保存');
}

// ======== 分离保存：域名配置 ========
if ($action == 'setting_domain_ajax') {
    $domain_2_prefix = Input::postStrVar('domain_2_prefix');
    $domain_2_suffix = Input::postStrVar('domain_2_suffix');
    $domain = Input::postStrVar('domain');
    $slug = trim(Input::postStrVar('slug'));

    $options_cache = $CACHE->readCache('options');
    $retain_str = isset($options_cache['station_domain_retain']) ? trim($options_cache['station_domain_retain']) : '';
    $domain_change_price = isset($options_cache['station_domain_change_price']) ? (float)$options_cache['station_domain_change_price'] : 0;

    // 保留前缀校验
    if (!empty($domain_2_prefix) && !empty($retain_str)) {
        $retain_list = array_map('trim', explode(',', strtolower($retain_str)));
        $prefix_lower = strtolower(trim($domain_2_prefix));
        if (in_array($prefix_lower, $retain_list, true)) {
            Ret::error('域名前缀 "' . htmlspecialchars($domain_2_prefix) . '" 为系统保留字，不可使用');
        }
    }
    if (!empty($domain_2_prefix)) {
        if (empty($domain_2_suffix)) {
            Ret::error('主站未配置二级域名后缀，暂无法设置二级域名');
        } else {
            $res = $db->once_fetch_array("select * from {$db_prefix}station where domain_2_prefix != '' and domain_2_prefix is not null and domain_2_prefix = '{$domain_2_prefix}' and domain_2_suffix = '{$domain_2_suffix}' and id != {$userData['station']['id']}");
            if (!empty($res)) {
                Ret::error('该二级域名的前缀已被占用，请填写其他前缀或选择其他后缀');
            }
        }
    }

    // slug 校验
    $slug_mode = isset($options_cache['station_slug_mode']) ? $options_cache['station_slug_mode'] : '0';
    if (!empty($slug) && $slug_mode === '1') {
        if (!preg_match('/^[a-zA-Z0-9_-]{2,50}$/', $slug)) {
            Ret::error('店铺标识只能包含字母、数字、下划线和连字符，长度 2-50 位');
        }
        if (!empty($retain_str)) {
            if (in_array(strtolower($slug), $retain_list ?? array_map('trim', explode(',', strtolower($retain_str))), true)) {
                Ret::error('店铺标识 "' . htmlspecialchars($slug) . '" 为系统保留字，不可使用');
            }
        }
        $slug_exists = $db->once_fetch_array("select id from {$db_prefix}station where slug = '{$slug}' and id != {$userData['station']['id']}");
        if (!empty($slug_exists)) {
            Ret::error('店铺标识 "' . htmlspecialchars($slug) . '" 已被其他分店使用');
        }
    }

    // 修改域名扣费逻辑
    if ($domain_change_price > 0) {
        $old_station = $db->once_fetch_array("select domain_2_prefix, domain_2_suffix, domain from {$db_prefix}station where id = {$userData['station']['id']}");
        $old_d2 = trim(($old_station['domain_2_prefix'] ?? '') . ($old_station['domain_2_suffix'] ?? ''));
        $old_d  = trim($old_station['domain'] ?? '');
        $new_d2 = trim($domain_2_prefix . $domain_2_suffix);
        $new_d  = trim($domain);
        $d2_changed = ($new_d2 !== $old_d2 && !empty($old_d2));
        $d_changed  = ($new_d !== $old_d && !empty($old_d));
        if ($d2_changed || $d_changed) {
            $user_row = $db->once_fetch_array("select money from {$db_prefix}user where uid = " . UID);
            $user_balance = (float)($user_row['money'] ?? 0);
            if ($user_balance < $domain_change_price) {
                Ret::error('修改域名需扣除 ¥' . number_format($domain_change_price, 2) . '，您的余额不足（当前 ¥' . number_format($user_balance, 2) . '）');
            }
            $balanceModel = new Balance_Model();
            $balanceModel->dec(UID, $domain_change_price, '修改分店域名扣费');
        }
    }

    // 前缀为空时，后缀和完整二级域名一起清空
    if (empty($domain_2_prefix)) {
        $domain_2_suffix = '';
    }
    $update = [
        'domain_2_prefix' => $domain_2_prefix,
        'domain_2_suffix' => $domain_2_suffix,
        'domain_2' => ($domain_2_prefix !== '' && $domain_2_suffix !== '') ? $domain_2_prefix . $domain_2_suffix : '',
        'domain' => $domain,
        'slug' => ($slug_mode === '1' && $slug !== '') ? $slug : null,
    ];
    $db->update('station', $update, ['id' => $userData['station']['id']]);
    Ret::success('域名配置已保存');
}

// ======== 分离保存：站内公告 ========
if ($action == 'setting_notice_ajax') {
    $roll_notice = Input::postStrVar('roll_notice');
    $home_notice = Input::postStrVar('home_notice');

    $update = [
        'roll_notice' => $roll_notice,
        'home_notice' => $home_notice,
    ];
    $db->update('station', $update, ['id' => $userData['station']['id']]);
    Ret::success('站内公告已保存');
}


if ($action == 'open') {
    // 分店总开关
    if (class_exists('Level_Service') && (int)Level_Service::getSetting(Level_Service::OPT_STATION_SWITCH, 1) !== 1) {
        emMsg('分店功能已关闭，如有疑问请联系客服');
    }
    // 实例化模型会自动为旧库补齐分店等级 icon / icon_image 字段，确保前台能读取图标
    new Station_Model();
    $station = $db->fetch_all("SELECT * FROM {$db_prefix}station_level WHERE `using`='y' ORDER BY sort ASC, id ASC");

    // 注入会员门槛名称 + 自动升级摘要
    foreach ($station as &$_slv) {
        $gateId = (int)($_slv['member_gate'] ?? 0);
        if ($gateId > 0) {
            $gateRow = $db->once_fetch_array("SELECT name FROM {$db_prefix}member WHERE id={$gateId}");
            $_slv['member_gate_name'] = $gateRow ? $gateRow['name'] : '';
        } else {
            $_slv['member_gate_name'] = '';
        }
        // 自动升级摘要
        $upParts = [];
        if ((float)($_slv['upgrade_sales_amount'] ?? 0) > 0) $upParts[] = '销售≥' . (float)$_slv['upgrade_sales_amount'] . '元';
        if ((int)($_slv['upgrade_order_count'] ?? 0) > 0) $upParts[] = '订单≥' . (int)$_slv['upgrade_order_count'] . '单';
        if ((int)($_slv['upgrade_days'] ?? 0) > 0) $upParts[] = '运营≥' . (int)$_slv['upgrade_days'] . '天';
        if ((int)($_slv['upgrade_sub_count'] ?? 0) > 0) $upParts[] = '下级≥' . (int)$_slv['upgrade_sub_count'] . '店';
        $_slv['upgrade_desc'] = !empty($upParts) ? implode('、', $upParts) : '';
        $_slv['upgrade_mode_text'] = ($_slv['upgrade_mode'] ?? 'any') === 'all' ? '全部满足' : '任一满足';
    }
    unset($_slv);

    $uc_app_mode = isMobile();
    $uc_page_title = '开通分店';
    include View::getUserView('_adaptive_header');
    require_once View::getUserView(isMobile() ? 'station/adaptive_open_mobile' : 'station/adaptive_open');
    include View::getUserView('_adaptive_footer');
    View::output();
}
if($action == 'open_ajax'){
    $id = Input::postIntVar('id');
    if(empty($id)){
        Ret::error('参数不合法');
    }
    // 分店总开关
    if (class_exists('Level_Service') && (int)Level_Service::getSetting(Level_Service::OPT_STATION_SWITCH, 1) !== 1) {
        Ret::error('分店功能已关闭，如有疑问请联系客服');
    }
    $existStation = $db->once_fetch_array("select * from {$db_prefix}station where user_id = " . UID . " AND delete_time IS NULL");
    $user = $db->once_fetch_array("select * from {$db_prefix}user where uid = " . UID);
    if(empty($user)){
        Ret::error('用户余额查询失败');
    }
    $station_level = $db->once_fetch_array("SELECT * FROM {$db_prefix}station_level WHERE id = {$id} AND `using`='y'");
    if(empty($station_level)){
        Ret::error('套餐不存在或已下架');
    }
    // 按分店等级检查会员门槛
    $memberGate = intval($station_level['member_gate'] ?? 0);
    if ($memberGate > 0 && class_exists('Level_Service')) {
        $userLevelId = (int)Level_Service::getActiveLevelId($user);
        $gateLv = $db->once_fetch_array("SELECT sort FROM {$db_prefix}member WHERE id = {$memberGate}");
        $userLv = $db->once_fetch_array("SELECT sort FROM {$db_prefix}member WHERE id = {$userLevelId}");
        $gateSort = $gateLv ? (int)$gateLv['sort'] : 0;
        $userSort = $userLv ? (int)$userLv['sort'] : 0;
        if ($userSort < $gateSort) {
            $gateName = $db->once_fetch_array("SELECT name FROM {$db_prefix}member WHERE id = {$memberGate}");
            Ret::error('开通「' . htmlspecialchars($station_level['name']) . '」需要达到会员等级「' . htmlspecialchars($gateName['name'] ?? '') . '」');
        }
    }

    if (!empty($existStation)) {
        // 已有分店 → 走升级逻辑
        $currentLevelId = (int)$existStation['level_id'];
        if ($currentLevelId === (int)$station_level['id']) {
            Ret::error('您当前已是该等级，无需重复操作');
        }
        $currentLevel = $db->once_fetch_array("SELECT * FROM {$db_prefix}station_level WHERE id = {$currentLevelId}");
        $currentSort = $currentLevel ? (int)$currentLevel['sort'] : 0;
        $targetSort = (int)$station_level['sort'];
        if ($targetSort <= $currentSort) {
            Ret::error('不支持降级，请选择更高等级');
        }
        $priceMode = Option::get('station_upgrade_price_mode') ?: 'diff';
        $currentPrice = $currentLevel ? (float)$currentLevel['price'] : 0;
        $upgradeCost = ($priceMode === 'full') ? (float)$station_level['price'] : max(0, round((float)$station_level['price'] - $currentPrice, 2));
        if ($user['money'] < $upgradeCost) {
            $costLabel = ($priceMode === 'full') ? '全额 ¥' : '补差价 ¥';
            Ret::error('您的余额不足，升级需' . $costLabel . number_format($upgradeCost, 2) . '，请先充值');
        }
        if ($upgradeCost > 0) {
            $BalanceModel = new Balance_Model();
            $costDesc = ($priceMode === 'full') ? '（全额）' : '（补差价）';
            $BalanceModel->dec(UID, $upgradeCost, '升级分店：' . $station_level['name'] . $costDesc);
        }
        $db->query("UPDATE {$db_prefix}station SET level_id = {$station_level['id']} WHERE id = {$existStation['id']}");
        User_Log_Model::log(UID, 'station_upgrade', '升级分店至：' . $station_level['name'] . '，花费 ¥' . number_format($upgradeCost, 2), -$upgradeCost);
        Ret::success('分店升级成功');
    }

    // 新开通分店
    if($user['money'] < $station_level['price']){
        Ret::error('您的余额不足，请充值余额后再开通');
    }
    $BalanceModel = new Balance_Model();
    $BalanceModel->dec(UID, $station_level['price'], '开通分店：' . $station_level['name']);
    $StationModel = new Station_Model();
    $StationModel->create(UID, $station_level['id'], $station_level['price']);

    User_Log_Model::log(UID, 'station_open', '开通分店：' . $station_level['name'] . '，花费 ¥' . $station_level['price'], -$station_level['price']);

    Ret::success('分店开通成功');
}

// ======== 分店等级升级 ========
if ($action == 'upgrade') {
    global $userData;
    if (empty($userData['station'])) {
        emMsg('您还未开通分店，请先开通');
    }
    $stationModel = new Station_Model();
    $currentLevel = $stationModel->getStationLevel($userData['station']['level_id']);
    $allLevels = $stationModel->getAllLevels(false);
    $currentSort = $currentLevel ? (int)$currentLevel['sort'] : -1;

    // 当前分店的运营实际数据（用于进度展示）
    $stationId = (int)$userData['station']['id'];
    $myStats = [
        'sales'  => $stationModel->sumStationSales($stationId),
        'orders' => $stationModel->countStationOrders($stationId),
        'days'   => $stationModel->countStationDays($stationId),
        'subs'   => $stationModel->countSubStations($stationId),
    ];

    // 构建可升级的等级列表（sort > 当前）
    $stationPriceMode = Option::get('station_upgrade_price_mode') ?: 'diff';
    $upgradeLevels = [];
    foreach ($allLevels as $lv) {
        $lv['is_current'] = ($currentLevel && (int)$lv['id'] === (int)$currentLevel['id']);
        if ((int)$lv['sort'] > $currentSort) {
            $currentPrice = $currentLevel ? (float)$currentLevel['price'] : 0;
            $lv['diff_price'] = ($stationPriceMode === 'full') ? (float)$lv['price'] : max(0, round((float)$lv['price'] - $currentPrice, 2));
            $lv['can_upgrade'] = true;
        } else {
            $lv['diff_price'] = 0;
            $lv['can_upgrade'] = false;
        }
        // 自动升级摘要
        $upParts = [];
        if ((float)($lv['upgrade_sales_amount'] ?? 0) > 0) $upParts[] = '销售≥' . (float)$lv['upgrade_sales_amount'] . '元';
        if ((int)($lv['upgrade_order_count'] ?? 0) > 0) $upParts[] = '订单≥' . (int)$lv['upgrade_order_count'] . '单';
        if ((int)($lv['upgrade_days'] ?? 0) > 0) $upParts[] = '运营≥' . (int)$lv['upgrade_days'] . '天';
        if ((int)($lv['upgrade_sub_count'] ?? 0) > 0) $upParts[] = '下级≥' . (int)$lv['upgrade_sub_count'] . '店';
        $lv['upgrade_desc'] = !empty($upParts) ? implode('、', $upParts) : '';
        $lv['upgrade_mode_text'] = ($lv['upgrade_mode'] ?? 'any') === 'all' ? '全部满足' : '任一满足';
        $lv['has_auto_upgrade'] = !empty($upParts);
        $upgradeLevels[] = $lv;
    }

    $uc_app_mode = isMobile();
    $uc_page_title = '分店升级';
    include View::getUserView('_adaptive_header');
    require_once View::getUserView(isMobile() ? 'station/adaptive_upgrade_mobile' : 'station/adaptive_upgrade');
    include View::getUserView('_adaptive_footer');
    View::output();
}

if ($action == 'upgrade_ajax') {
    global $userData;
    if (empty($userData['station'])) {
        Ret::error('您还未开通分店');
    }
    $targetId = Input::postIntVar('id');
    if (empty($targetId)) {
        Ret::error('请选择要升级的等级');
    }

    $stationModel = new Station_Model();
    $currentLevel = $stationModel->getStationLevel($userData['station']['level_id']);
    $targetLevel = $stationModel->getStationLevel($targetId);

    if (empty($targetLevel) || ($targetLevel['using'] ?? 'y') !== 'y') {
        Ret::error('目标等级不存在或已下架');
    }

    // 检查目标等级 sort 必须高于当前
    $currentSort = $currentLevel ? (int)$currentLevel['sort'] : -1;
    if ((int)$targetLevel['sort'] <= $currentSort) {
        Ret::error('只能升级到更高等级');
    }

    // 计算升级费用
    $priceMode = Option::get('station_upgrade_price_mode') ?: 'diff';
    $currentPrice = $currentLevel ? (float)$currentLevel['price'] : 0;
    $upgradeCost = ($priceMode === 'full') ? (float)$targetLevel['price'] : max(0, round((float)$targetLevel['price'] - $currentPrice, 2));

    // 余额检查
    $user = $db->once_fetch_array("select * from {$db_prefix}user where uid = " . UID);
    if (empty($user)) {
        Ret::error('用户信息查询失败');
    }
    if ((float)$user['money'] < $upgradeCost) {
        $costLabel = ($priceMode === 'full') ? '全额 ¥' : '补差价 ¥';
        Ret::error('余额不足，升级需' . $costLabel . number_format($upgradeCost, 2) . '，当前余额 ¥' . number_format($user['money'], 2));
    }

    // 扣余额
    if ($upgradeCost > 0) {
        $BalanceModel = new Balance_Model();
        $costDesc = ($priceMode === 'full') ? '（全额）' : '（补差价）';
        $BalanceModel->dec(UID, $upgradeCost, '分店升级：' . $targetLevel['name'] . $costDesc);
    }

    // 更新分店等级
    $db->update('station', ['level_id' => $targetId], ['id' => $userData['station']['id']]);

    $fromName = $currentLevel ? $currentLevel['name'] : '无';
    User_Log_Model::log(UID, 'station_upgrade', '分店升级：' . $fromName . ' → ' . $targetLevel['name'] . '，花费 ¥' . number_format($upgradeCost, 2), -$upgradeCost);

    Ret::success('升级成功！已升级至「' . $targetLevel['name'] . '」');
}
