<?php
defined('DC_ROOT') || exit('access denied!');

// 校验动作是否属于当前处理范围
if (!in_array($action, ['api_key_get', 'api_key_reset', 'api_whitelist_save'], true)) {
    return;
}

if (!ISLOGIN) {
    Output::error('请先登录');
}

$db = Database::getInstance();
$db_prefix = DB_PREFIX;

// 自动检查并创建缺少的 API 对接相关数据库字段，实现自愈
if (!function_exists('ensureApiKeyColumns')) {
    function ensureApiKeyColumns($db, $table) {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;
        
        // 检查并添加 api_key
        $col = $db->once_fetch_array("SHOW COLUMNS FROM `{$table}` LIKE 'api_key'");
        if (empty($col)) {
            $db->query("ALTER TABLE `{$table}` ADD COLUMN `api_key` varchar(64) NOT NULL DEFAULT '' COMMENT 'API对接密钥'");
        }
        
        // 检查并添加 api_key_hash
        $col = $db->once_fetch_array("SHOW COLUMNS FROM `{$table}` LIKE 'api_key_hash'");
        if (empty($col)) {
            $db->query("ALTER TABLE `{$table}` ADD COLUMN `api_key_hash` varchar(64) NOT NULL DEFAULT '' COMMENT 'API密钥Hash'");
        }
        
        // 检查并添加 api_key_create_time
        $col = $db->once_fetch_array("SHOW COLUMNS FROM `{$table}` LIKE 'api_key_create_time'");
        if (empty($col)) {
            $db->query("ALTER TABLE `{$table}` ADD COLUMN `api_key_create_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'API密钥创建时间'");
        }
        
        // 检查并添加 api_whitelist
        $col = $db->once_fetch_array("SHOW COLUMNS FROM `{$table}` LIKE 'api_whitelist'");
        if (empty($col)) {
            $db->query("ALTER TABLE `{$table}` ADD COLUMN `api_whitelist` text DEFAULT NULL COMMENT 'API白名单IP'");
        }
        
        // 检查并添加 api_whitelist_enabled
        $col = $db->once_fetch_array("SHOW COLUMNS FROM `{$table}` LIKE 'api_whitelist_enabled'");
        if (empty($col)) {
            $db->query("ALTER TABLE `{$table}` ADD COLUMN `api_whitelist_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否启用API白名单'");
        }
    }
}

ensureApiKeyColumns($db, $db_prefix . 'user');

// 1. 获取密钥信息
if ($action == 'api_key_get') {
    $u = $db->once_fetch_array("SELECT api_key, api_key_create_time, api_whitelist, api_whitelist_enabled FROM {$db_prefix}user WHERE uid = " . UID . " LIMIT 1");
    if (empty($u)) {
        Output::error('用户不存在');
    }

    $apiKey = $u['api_key'];
    $apiWhitelist = $u['api_whitelist'] ?? '';
    $apiWhitelistEnabled = (int)($u['api_whitelist_enabled'] ?? 0);
    $createTime = (int)($u['api_key_create_time'] ?? 0);

    // 如果密钥为空，则自动生成一个
    if (empty($apiKey)) {
        try {
            $apiKey = bin2hex(random_bytes(16)); // 32位影视级随机十六进制密钥
        } catch (Exception $e) {
            $apiKey = md5(uniqid(microtime(true), true));
        }
        $apiKeyHash = md5($apiKey);
        $createTime = time();

        $db->query("UPDATE {$db_prefix}user SET api_key = '{$apiKey}', api_key_hash = '{$apiKeyHash}', api_key_create_time = {$createTime} WHERE uid = " . UID);
        
        // 记录日志
        if (class_exists('User_Log_Model')) {
            User_Log_Model::log(UID, 'api_key_generate', '自动生成API对接密钥');
        }
    }

    // 对密钥进行安全掩码处理，既保护安全又能展示存在状态
    $keyMasked = '';
    if (!empty($apiKey)) {
        $len = strlen($apiKey);
        if ($len > 8) {
            $keyMasked = substr($apiKey, 0, 4) . str_repeat('*', $len - 8) . substr($apiKey, -4);
        } else {
            $keyMasked = '********';
        }
    }

    Output::ok([
        'has_key' => !empty($apiKey),
        'api_key' => $apiKey,
        'key_masked' => $keyMasked,
        'key_create_time' => $createTime,
        'whitelist' => $apiWhitelist,
        'whitelist_enabled' => $apiWhitelistEnabled
    ]);
}

// 2. 重置密钥
if ($action == 'api_key_reset') {
    LoginAuth::checkToken(); // 防CSRF

    try {
        $apiKey = bin2hex(random_bytes(16)); // 32位十六进制密钥
    } catch (Exception $e) {
        $apiKey = md5(uniqid(microtime(true), true));
    }
    $apiKeyHash = md5($apiKey);
    $now = time();

    $db->query("UPDATE {$db_prefix}user SET api_key = '{$apiKey}', api_key_hash = '{$apiKeyHash}', api_key_create_time = {$now} WHERE uid = " . UID);
    
    // 记录日志
    if (class_exists('User_Log_Model')) {
        User_Log_Model::log(UID, 'api_key_reset', '重置API对接密钥');
    }

    Output::ok([
        'api_key' => $apiKey
    ]);
}

// 3. 保存白名单配置
if ($action == 'api_whitelist_save') {
    LoginAuth::checkToken(); // 防CSRF

    $whitelist = trim(Input::postStrVar('whitelist'));

    // 清洗白名单输入，确保格式正确
    $ips = [];
    if (!empty($whitelist)) {
        // 统一换行符
        $whitelist = str_replace("\r\n", "\n", $whitelist);
        $whitelist = str_replace("\r", "\n", $whitelist);
        $rawIps = explode("\n", $whitelist);
        foreach ($rawIps as $ip) {
            $ip = trim($ip);
            if (empty($ip)) continue;
            $ips[] = addslashes($ip);
        }
    }
    $whitelistClean = implode("\n", $ips);
    // 有IP则自动启用白名单，留空则关闭
    $enabled = !empty($ips) ? 1 : 0;

    $db->query("UPDATE {$db_prefix}user SET api_whitelist = '{$whitelistClean}', api_whitelist_enabled = {$enabled} WHERE uid = " . UID);

    // 记录日志
    if (class_exists('User_Log_Model')) {
        User_Log_Model::log(UID, 'api_whitelist_update', '更新API白名单配置，开启状态：' . ($enabled ? '是' : '否'));
    }

    Output::ok('保存成功');
}
