<?php
/**
 * 后台AJAX接口
 */
require_once 'globals.php';

$action = Input::getStrVar('action');

switch ($action) {
    // 检测更新（已禁用）
    case 'check_update':
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['code' => 200, 'msg' => '当前已是最新版本', 'data' => ['has_update' => false, 'latest_version' => Option::DC_VERSION]], JSON_UNESCAPED_UNICODE);
        break;

    // 下载升级包（已禁用）
    case 'download_upgrade':
        Output::error('在线升级功能已关闭');
        break;

    // 查询下载进度
    case 'download_progress':
        downloadProgress();
        break;
    
    // 解压升级包（已禁用）
    case 'extract_upgrade':
        Output::error('在线升级功能已关闭');
        break;

    // 执行升级SQL（已禁用）
    case 'execute_upgrade_sql':
        Output::error('在线升级功能已关闭');
        break;
    
    // 删除安装文件
    case 'delete_install_file':
        deleteInstallFile();
        break;
    
    // 删除单个资源文件
    case 'resource_delete':
        resourceDelete();
        break;
    
    // 批量删除资源文件
    case 'resource_batch_delete':
        resourceBatchDelete();
        break;
    
    // 删除空目录
    case 'resource_delete_dir':
        resourceDeleteDir();
        break;
    
    // 文件校准 - 下载（异步，CDN兼容）
    case 'calibrate_files':
        calibrateFiles();
        break;
    
    // 文件校准 - 查询下载进度
    case 'calibrate_progress':
        calibrateProgress();
        break;
    
    // 文件校准 - 解压覆盖
    case 'calibrate_apply':
        calibrateApply();
        break;
    
    // 恢复校准备份
    case 'restore_calibrate_backup':
        restoreCalibrateBackup();
        break;
    
    // 删除校准备份
    case 'delete_calibrate_backup':
        deleteCalibrateBackup();
        break;
    
    default:
        Output::error('无效的请求');
}

/**
 * 删除安装文件
 */
function deleteInstallFile() {
    // 只有管理员可以操作
    if (ROLE !== 'admin') {
        Output::error('无权限操作');
    }
    
    $installFile = DC_ROOT . '/install.php';
    
    if (!file_exists($installFile)) {
        Output::error('文件不存在');
    }
    
    if (@unlink($installFile)) {
        Output::ok('删除成功');
    } else {
        Output::error('删除失败，请检查文件权限或手动删除');
    }
}

/**
 * 检测更新（已禁用 - 不再调用远程API）
 */
function checkUpdate() {
    Output::ok(['has_update' => false, 'latest_version' => Option::DC_VERSION]);
}

/**
 * 查询下载进度（配合异步下载使用）
 */
function downloadProgress() {
    if (ROLE !== 'admin') {
        Output::error('无权限操作');
    }
    $taskFile = DC_ROOT . '/content/upgrade/.download_task.json';
    if (!file_exists($taskFile)) {
        Output::ok(['status' => 'idle']);
    }
    $task = @json_decode(@file_get_contents($taskFile), true);
    if (!$task) {
        Output::ok(['status' => 'idle']);
    }
    // 超过10分钟的任务视为过期
    if (isset($task['started_at']) && time() - $task['started_at'] > 600) {
        @unlink($taskFile);
        Output::ok(['status' => 'expired', 'msg' => '下载任务已超时']);
    }
    Output::ok($task);
}

/**
 * 下载升级包（已禁用）
 */
function downloadUpgrade() {
    Output::error('在线升级功能已关闭');
}

/**
 * 解压升级包
 * 支持完整安装包作为升级包使用，智能跳过用户配置和数据
 */
function extractUpgrade() {
    if (ROLE !== 'admin') {
        Output::error('无权限操作');
    }
    
    LoginAuth::checkToken();
    
    $upgradeDir = DC_ROOT . '/content/upgrade/';
    $version = Input::postStrVar('version');
    if ($version && !preg_match('/^[0-9A-Za-z._-]+$/', $version)) {
        Output::error('版本号格式不正确');
    }
    
    if ($version) {
        $zipFile = $upgradeDir . 'update_' . $version . '.zip';
    } else {
        // 兼容旧前端：未传版本号时取最新的升级包
        $files = glob($upgradeDir . 'update_*.zip');
        if (empty($files)) {
            Output::error('未找到升级包，请先下载');
        }
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        $zipFile = $files[0];
    }
    
    if (!file_exists($zipFile)) {
        Output::error('升级包不存在');
    }
    
    // 解压到临时目录
    $extractDir = $upgradeDir . 'extract_' . time() . '/';
    if (!is_dir($extractDir)) {
        @mkdir($extractDir, 0755, true);
    }
    
    $extractResult = safeExtractZipFile($zipFile, $extractDir);
    if (!$extractResult['success']) {
        deleteDirectory($extractDir);
        Output::error($extractResult['error']);
    }
    
    // 检测解压后的目录结构
    // 如果是完整安装包，可能有一层父目录（如 dcshop/）
    $sourceDir = $extractDir;
    $subDirs = glob($extractDir . '*', GLOB_ONLYDIR);
    
    // 如果只有一个子目录，且包含典型的程序文件，则使用该子目录
    if (count($subDirs) === 1) {
        $subDir = $subDirs[0];
        if (file_exists($subDir . '/index.php') || file_exists($subDir . '/init.php')) {
            $sourceDir = $subDir . '/';
        }
    }
    
    // 复制文件到网站根目录（智能跳过用户数据）
    $backupDir = DC_ROOT . '/content/backup/upgrade/';
    if (!is_dir($backupDir)) {
        @mkdir($backupDir, 0755, true);
    }
    $backupFile = $backupDir . 'backup_' . date('Ymd_His') . '_v' . Option::DC_VERSION . '_to_' . ($version ?: 'latest') . '.zip';
    $backupResult = createUpgradeBackup($sourceDir, DC_ROOT, $backupFile);
    if (!$backupResult['success']) {
        deleteDirectory($extractDir);
        Output::error('备份当前文件失败：' . $backupResult['error']);
    }
    
    $result = copyDirectoryForUpgrade($sourceDir, DC_ROOT);
    
    if (!$result['success']) {
        restoreUpgradeBackupFile($backupFile);
        deleteDirectory($extractDir);
        Output::error('复制文件失败：' . $result['error']);
    }
    
    // 清理临时文件
    deleteDirectory($extractDir);
    @unlink($zipFile);
    
    Output::ok([
        'message' => '文件更新完成',
        'updated' => $result['updated'],
        'skipped' => $result['skipped'],
        'backup' => str_replace(DC_ROOT, '', $backupFile)
    ]);
}

/**
 * 执行升级SQL
 */
function executeUpgradeSql() {
    if (ROLE !== 'admin') {
        Output::error('无权限操作');
    }
    
    LoginAuth::checkToken();
    
    $version = Input::postStrVar('version');
    if (empty($version)) {
        Output::error('版本号不能为空');
    }
    if (!preg_match('/^[0-9A-Za-z._-]+$/', $version)) {
        Output::error('版本号格式不正确');
    }
    $currentVersion = Input::postStrVar('current_version');
    if ($currentVersion && !preg_match('/^[0-9A-Za-z._-]+$/', $currentVersion)) {
        Output::error('当前版本号格式不正确');
    }
    
    $domain = getTopHost();
    $licenseKey = getLicenseKey();
    
    // 获取SQL内容
    $apiUrl = Register::getAuthCenterUrl();
    $apiUrl = str_replace('/user/', '/api.php', $apiUrl);
    
    $params = [
        'action' => 'get_update_sql',
        'version' => $version,
        'from_version' => $currentVersion ?: Option::DC_VERSION,
        'license_key' => $licenseKey,
        'domain' => $domain
    ];
    
    $response = emCurl($apiUrl, http_build_query($params), true, [], 30);
    $data = json_decode($response, true);
    
    if (!$data || $data['code'] !== 0) {
        // 没有SQL也算成功（可能这个版本不需要SQL更新）
        Output::ok(['message' => '无需更新数据库']);
    }
    
    $sqlContent = $data['data']['sql'] ?? '';
    
    // 授权服务器对 SQL 内容做了 Base64 编码传输（避免 CDN/WAF 拦截），此处解码还原
    if (!empty($sqlContent) && !empty($data['data']['encoding']) && $data['data']['encoding'] === 'base64') {
        $decoded = base64_decode($sqlContent, true);
        if ($decoded !== false) {
            $sqlContent = $decoded;
        }
    }
    
    if (empty($sqlContent)) {
        Output::ok(['message' => '无需更新数据库']);
    }
    
    // 执行SQL
    $DB = Database::getInstance();
    
    // 替换表前缀
    $sqlContent = str_replace('dc_', DB_PREFIX, $sqlContent);
    
    // 分割SQL语句（先去除行注释，避免“注释+SQL”被整体跳过）
    $queries = splitUpgradeSqlStatements($sqlContent);
    
    $executed = 0;
    $errors = [];
    
    foreach ($queries as $query) {
        if (empty($query)) {
            continue;
        }
        
        $ret = @$DB->query($query, true);
        $errMsg = '';
        if ($ret === false || is_string($ret)) {
            if (is_string($ret)) {
                $errMsg = $ret;
            } elseif (method_exists($DB, 'getError')) {
                $dbErr = $DB->getError();
                $errMsg = is_array($dbErr) ? implode(' ', array_filter($dbErr)) : (string)$dbErr;
            } else {
                $errMsg = 'SQL执行失败';
            }
        }
        if ($errMsg !== '') {
            if (isIgnorableUpgradeSqlError($errMsg)) {
                continue;
            }
            $errors[] = function_exists('mb_substr') ? mb_substr($errMsg, 0, 300, 'UTF-8') : substr($errMsg, 0, 300);
        } else {
            $executed++;
        }
    }
    
    // 更新缓存（无论SQL是否全部成功都刷新，避免缓存残留旧结构）
    global $CACHE;
    $CACHE->updateCache();
    
    if (!empty($errors)) {
        if ($executed > 0) {
            // 部分成功部分失败：视为成功（有 ensureSchema 兜底），仅附带警告
            Output::ok([
                'message' => '数据库更新完成（部分语句已跳过）',
                'executed' => $executed,
                'errors' => count($errors),
                'warnings' => array_slice($errors, 0, 3)
            ]);
        } else {
            // 全部失败：返回 HTTP 200 + code=1，让前端 success 回调能展示 msg
            // 不用 Output::error()（HTTP 400），因为老版本浏览器 JS 的 error 回调不解析响应体
            Output::error('数据库更新失败：' . implode('；', array_slice($errors, 0, 3)), 200);
        }
    } else {
        Output::ok([
            'message' => '数据库更新完成',
            'executed' => $executed,
            'errors' => 0
        ]);
    }
}

/**
 * 获取授权码
 */
function getLicenseKey() {
    // 从数据库获取授权码
    $db = Database::getInstance();
    $domain = getTopHost();
    $sql = "SELECT emkey FROM " . DB_PREFIX . "authorization WHERE domain = '" . $db->escape_string($domain) . "'";
    $res = $db->once_fetch_array($sql);
    return $res ? $res['emkey'] : '';
}

/**
 * 自动同步授权码到本地
 * 授权端通过域名查到授权后，会把 license_key 回传给客户端
 * 客户端保存到本地 dc_authorization 表，实现重装后自动恢复授权
 */
function syncLicenseKey($domain, $licenseKey) {
    if (empty($domain) || empty($licenseKey)) return;
    try {
        $db = Database::getInstance();
        $escapedDomain = $db->escape_string($domain);
        $escapedKey = $db->escape_string($licenseKey);
        // 检查是否已存在
        $sql = "SELECT emkey FROM " . DB_PREFIX . "authorization WHERE domain = '$escapedDomain'";
        $existing = $db->once_fetch_array($sql);
        if ($existing) {
            // 已有记录，更新授权码（如果不同）
            if ($existing['emkey'] !== $licenseKey) {
                $db->query("UPDATE " . DB_PREFIX . "authorization SET emkey = '$escapedKey' WHERE domain = '$escapedDomain'");
            }
        } else {
            // 无记录，插入新的
            $db->query("INSERT INTO " . DB_PREFIX . "authorization (domain, emkey) VALUES ('$escapedDomain', '$escapedKey')");
        }
    } catch (\Exception $e) {
        // 同步失败不影响主流程
    }
}

/**
 * 升级时需要跳过的文件和目录
 * 这些是用户配置和数据，不应被覆盖
 */
function getSkipList() {
    return [
        // 配置文件
        'config.php',
        
        // 用户数据目录
        'content/uploadfile',      // 用户上传的文件
        'content/cache',           // 缓存目录
        'content/templates',       // 用户可能自定义的模板（保留）
        'content/plugins',         // 用户安装的插件（保留）
        'content/upgrade',         // 升级临时目录
        'content/backup',          // 备份目录
        
        // 安装文件（升级时不需要）
        'install.php',
        'install.sql',
        
        // 授权中心（如果在同一目录）
        'license_server',
        'auth_server',
        
        // 其他
        '.git',
        '.gitignore',
        '.htaccess',               // 用户可能自定义的规则
        'nginx.conf',
        'nginx.htaccess',
    ];
}

/**
 * 检查路径是否应该跳过
 */
function shouldSkip($relativePath) {
    $skipList = getSkipList();
    $relativePath = ltrim($relativePath, '/\\');
    
    foreach ($skipList as $skip) {
        // 精确匹配
        if ($relativePath === $skip) {
            return true;
        }
        // 目录前缀匹配
        if (strpos($relativePath, $skip . '/') === 0 || strpos($relativePath, $skip . '\\') === 0) {
            return true;
        }
    }
    
    return false;
}

/**
 * 递归复制目录（升级专用，智能跳过用户数据）
 */
function copyDirectoryForUpgrade($src, $dst, $basePath = '') {
    $result = [
        'success' => true,
        'error' => '',
        'updated' => 0,
        'skipped' => 0,
        'failed' => 0,
        'failed_files' => []
    ];
    
    $dir = @opendir($src);
    if (!$dir) {
        $result['success'] = false;
        $result['error'] = '无法打开源目录';
        return $result;
    }
    
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') continue;
        
        $srcPath = $src . '/' . $file;
        $dstPath = $dst . '/' . $file;
        $relativePath = $basePath ? $basePath . '/' . $file : $file;
        
        // 检查是否应该跳过
        if (shouldSkip($relativePath)) {
            $result['skipped']++;
            continue;
        }
        
        if (is_dir($srcPath)) {
            // 目录：递归处理
            if (!is_dir($dstPath)) {
                @mkdir($dstPath, 0755, true);
            }
            
            $subResult = copyDirectoryForUpgrade($srcPath, $dstPath, $relativePath);
            $result['updated'] += $subResult['updated'];
            $result['skipped'] += $subResult['skipped'];
            $result['failed'] += $subResult['failed'];
            if (!$subResult['success'] && empty($result['error']) && !empty($subResult['error'])) {
                $result['error'] = $subResult['error'];
            }
            if (!empty($subResult['failed_files'])) {
                $result['failed_files'] = array_merge($result['failed_files'], $subResult['failed_files']);
            }
        } else {
            // 文件：确保目标目录存在
            $dstDir = dirname($dstPath);
            if (!is_dir($dstDir)) {
                @mkdir($dstDir, 0755, true);
            }
            
            // 尝试复制，失败则用 file_put_contents 重试
            $copied = @copy($srcPath, $dstPath);
            if (!$copied) {
                @chmod($dstPath, 0644);
                @unlink($dstPath);
                $copied = @file_put_contents($dstPath, file_get_contents($srcPath)) !== false;
            }
            
            if ($copied) {
                $result['updated']++;
            } else {
                $result['failed']++;
                $result['failed_files'][] = $relativePath;
            }
        }
    }
    
    closedir($dir);
    $result['success'] = ($result['failed'] === 0);
    if (!$result['success'] && empty($result['error'])) {
        $result['error'] = '部分文件复制失败：' . implode(',', array_slice($result['failed_files'], 0, 5));
    }
    return $result;
}


/**
 * 递归删除目录
 */
function deleteDirectory($dir) {
    if (!is_dir($dir)) return;
    
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? deleteDirectory($path) : @unlink($path);
    }
    @rmdir($dir);
}

/**
 * 安全解压 ZIP，防止升级包路径穿越覆盖站点外文件
 */
function safeExtractZipFile($zipFile, $extractDir) {
    $zip = new ZipArchive();
    if ($zip->open($zipFile) !== true) {
        return ['success' => false, 'error' => '无法打开升级包'];
    }
    
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entry = $zip->getNameIndex($i);
        $entry = str_replace('\\', '/', (string)$entry);
        if ($entry === '' || strpos($entry, "\0") !== false) {
            $zip->close();
            return ['success' => false, 'error' => '升级包包含非法文件名'];
        }
        if ($entry[0] === '/' || preg_match('/^[A-Za-z]:\//', $entry) || strpos($entry, '../') !== false || preg_match('#(^|/)\.\.($|/)#', $entry)) {
            $zip->close();
            return ['success' => false, 'error' => '升级包包含非法路径：' . $entry];
        }
    }
    
    if (!$zip->extractTo($extractDir)) {
        $zip->close();
        return ['success' => false, 'error' => '解压失败，请检查目录权限'];
    }
    
    $zip->close();
    return ['success' => true, 'error' => ''];
}

/**
 * 创建升级前备份：只备份本次升级将覆盖的程序文件
 */
function createUpgradeBackup($sourceDir, $rootDir, $backupFile) {
    $result = ['success' => true, 'error' => '', 'count' => 0];
    
    $backupParent = dirname($backupFile);
    if (!is_dir($backupParent)) {
        @mkdir($backupParent, 0755, true);
    }
    
    $zip = new ZipArchive();
    if ($zip->open($backupFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return ['success' => false, 'error' => '无法创建备份文件', 'count' => 0];
    }
    
    $sourceRoot = rtrim(str_replace('\\', '/', realpath($sourceDir) ?: $sourceDir), '/') . '/';
    $rootDir = rtrim($rootDir, '/\\');
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $item) {
        if (!$item->isFile()) {
            continue;
        }
        $srcPath = str_replace('\\', '/', $item->getPathname());
        $relativePath = ltrim(str_replace($sourceRoot, '', $srcPath), '/');
        if ($relativePath === '' || shouldSkip($relativePath)) {
            continue;
        }
        $currentFile = $rootDir . '/' . $relativePath;
        if (is_file($currentFile)) {
            $zip->addFile($currentFile, $relativePath);
            $result['count']++;
        }
    }
    
    $zip->close();
    return $result;
}

/**
 * 升级复制失败时尝试恢复文件备份
 */
function restoreUpgradeBackupFile($backupFile) {
    if (!is_file($backupFile)) {
        return false;
    }
    $tempDir = DC_ROOT . '/content/upgrade/restore_upgrade_' . time() . '/';
    @mkdir($tempDir, 0755, true);
    $extract = safeExtractZipFile($backupFile, $tempDir);
    if (!$extract['success']) {
        deleteDirectory($tempDir);
        return false;
    }
    copyDirectoryForUpgrade($tempDir, DC_ROOT);
    deleteDirectory($tempDir);
    return true;
}

/**
 * 分割升级SQL：去除注释并避免简单字符串中的分号误切
 * 
 * 增强：不仅按行首去除 -- 注释，还处理以下场景：
 * 1. 文件上传/API传输后换行符丢失，注释和SQL挤在同一行
 * 2. 行内 -- 注释（不在引号内的 -- 后内容全部丢弃）
 * 3. UTF-8 BOM 在文件中间（多版本SQL合并时）
 */
function splitUpgradeSqlStatements($sql) {
    // 去除所有 UTF-8 BOM（可能出现在合并的多版本SQL中间）
    $sql = str_replace("\xEF\xBB\xBF", '', (string)$sql);
    // 去除 /* ... */ 块注释
    $sql = preg_replace('/\/\*[\s\S]*?\*\//', '', $sql);
    
    // 按行去除整行注释
    $lines = preg_split('/\R/', $sql);
    $cleanLines = [];
    foreach ($lines as $line) {
        $trim = ltrim($line);
        if ($trim === '' || strpos($trim, '--') === 0 || strpos($trim, '#') === 0) {
            continue;
        }
        $cleanLines[] = $line;
    }
    $sql = implode("\n", $cleanLines);
    
    $statements = [];
    $buffer = '';
    $quote = '';
    $escaped = false;
    $inLineComment = false;
    $length = strlen($sql);
    for ($i = 0; $i < $length; $i++) {
        $ch = $sql[$i];
        
        // 行内注释状态：跳过直到换行或分号
        if ($inLineComment) {
            if ($ch === "\n" || $ch === "\r") {
                $inLineComment = false;
            }
            // 分号仍然作为语句分隔符（注释后紧跟分号的情况）
            if ($ch === ';') {
                $inLineComment = false;
                $statement = trim($buffer);
                if ($statement !== '') {
                    $statements[] = $statement;
                }
                $buffer = '';
            }
            continue;
        }
        
        if ($escaped) {
            $buffer .= $ch;
            $escaped = false;
            continue;
        }
        if ($ch === '\\' && ($quote === '\'' || $quote === '"')) {
            $buffer .= $ch;
            $escaped = true;
            continue;
        }
        if ($quote !== '') {
            $buffer .= $ch;
            if ($ch === $quote) {
                $quote = '';
            }
            continue;
        }
        
        // 不在引号内，检测 -- 行注释
        if ($ch === '-' && $i + 1 < $length && $sql[$i + 1] === '-') {
            $inLineComment = true;
            $i++; // 跳过第二个 -
            continue;
        }
        // 不在引号内，检测 # 行注释
        if ($ch === '#') {
            $inLineComment = true;
            continue;
        }
        
        $buffer .= $ch;
        
        if ($ch === '\'' || $ch === '"' || $ch === '`') {
            $quote = $ch;
            continue;
        }
        if ($ch === ';') {
            $statement = trim(substr($buffer, 0, -1));
            if ($statement !== '') {
                $statements[] = $statement;
            }
            $buffer = '';
        }
    }
    $last = trim($buffer);
    if ($last !== '') {
        $statements[] = $last;
    }
    
    return $statements;
}

/**
 * 可忽略的幂等SQL错误（发布SQL仍建议使用 IF NOT EXISTS）
 */
function isIgnorableUpgradeSqlError($errMsg) {
    $errMsg = strtolower((string)$errMsg);
    return strpos($errMsg, 'duplicate column') !== false
        || strpos($errMsg, 'duplicate key name') !== false
        || strpos($errMsg, 'already exists') !== false
        || strpos($errMsg, '1060') !== false   // Duplicate column name
        || strpos($errMsg, '1061') !== false   // Duplicate key name
        || strpos($errMsg, '1050') !== false   // Table already exists
        || strpos($errMsg, '1064') !== false   // Syntax error (e.g. IF NOT EXISTS on MySQL 5.7)
        || strpos($errMsg, 'if not exists') !== false; // 兼容含 IF NOT EXISTS 的语法错误描述
}

/**
 * 删除单个资源文件
 */
function resourceDelete() {
    // 只有管理员可以操作
    if (ROLE !== 'admin') {
        Output::error('无权限操作');
    }
    
    $path = Input::postStrVar('path');
    if (!$path) {
        Output::error('参数错误');
    }
    
    // 安全检查：只允许删除 content/uploadfile 目录下的文件
    if (strpos($path, 'content/uploadfile/') !== 0 || strpos($path, '..') !== false) {
        Output::error('非法路径');
    }
    
    $filePath = DC_ROOT . '/' . $path;
    
    if (!file_exists($filePath)) {
        Output::error('文件不存在');
    }
    
    if (@unlink($filePath)) {
        // 同步清理 attachment 表中的记录
        $DB = Database::getInstance();
        $pathVariants = [
            $path,
            '../' . $path,
            '/' . $path,
        ];
        foreach ($pathVariants as $p) {
            $DB->query("DELETE FROM " . DB_PREFIX . "attachment WHERE filepath = '" . addslashes($p) . "'");
        }
        
        Output::ok('删除成功');
    } else {
        Output::error('删除失败');
    }
}

/**
 * 批量删除资源文件
 */
function resourceBatchDelete() {
    // 只有管理员可以操作
    if (ROLE !== 'admin') {
        Output::error('无权限操作');
    }
    
    $paths = Input::postStrVar('paths');
    if (!$paths) {
        Output::error('参数错误');
    }
    
    $DB = Database::getInstance();
    $pathList = explode(',', $paths);
    $deleted = 0;
    $failed = 0;
    
    foreach ($pathList as $path) {
        $path = trim($path);
        if (!$path) continue;
        
        // 安全检查
        if (strpos($path, 'content/uploadfile/') !== 0 || strpos($path, '..') !== false) {
            $failed++;
            continue;
        }
        
        $filePath = DC_ROOT . '/' . $path;
        if (file_exists($filePath) && @unlink($filePath)) {
            // 同步清理 attachment 表中的记录
            $pathVariants = [
                $path,
                '../' . $path,
                '/' . $path,
            ];
            foreach ($pathVariants as $p) {
                $DB->query("DELETE FROM " . DB_PREFIX . "attachment WHERE filepath = '" . addslashes($p) . "'");
            }
            $deleted++;
        } else {
            $failed++;
        }
    }
    
    Output::ok("清理完成：成功 {$deleted} 个，失败 {$failed} 个");
}

/**
 * 删除空目录
 */
function resourceDeleteDir() {
    // 只有管理员可以操作
    if (ROLE !== 'admin') {
        Output::error('无权限操作');
    }
    
    $dir = Input::postStrVar('dir');
    if (!$dir) {
        Output::error('参数错误');
    }
    
    // 安全检查：只允许删除日期格式的目录（如 202601）
    if (!preg_match('/^\d{6}$/', $dir)) {
        Output::error('只能删除日期格式的空目录');
    }
    
    $dirPath = DC_ROOT . '/content/uploadfile/' . $dir;
    
    if (!is_dir($dirPath)) {
        Output::error('目录不存在');
    }
    
    // 检查目录是否为空
    $files = array_diff(scandir($dirPath), ['.', '..']);
    if (!empty($files)) {
        Output::error('目录不为空，无法删除');
    }
    
    if (@rmdir($dirPath)) {
        Output::ok('删除成功');
    } else {
        Output::error('删除目录失败');
    }
}

/**
 * 文件校准 - 查询下载进度
 */
function calibrateProgress() {
    if (ROLE !== 'admin') {
        Output::error('无权限操作');
    }
    $taskFile = DC_ROOT . '/content/upgrade/.calibrate_task.json';
    if (!file_exists($taskFile)) {
        Output::ok(['status' => 'idle']);
    }
    $task = @json_decode(@file_get_contents($taskFile), true);
    if (!$task) {
        Output::ok(['status' => 'idle']);
    }
    if (isset($task['started_at']) && time() - $task['started_at'] > 600) {
        @unlink($taskFile);
        Output::ok(['status' => 'expired', 'msg' => '下载任务已超时']);
    }
    Output::ok($task);
}

/**
 * 文件校准 - 解压备份覆盖（下载完成后调用）
 */
function calibrateApply() {
    set_time_limit(300);
    ini_set('memory_limit', '512M');
    
    if (ROLE !== 'admin') {
        Output::error('无权限操作');
    }
    
    LoginAuth::checkToken();
    
    $currentVersion = Option::DC_VERSION;
    $upgradeDir = DC_ROOT . '/content/upgrade/';
    $taskFile = $upgradeDir . '.calibrate_task.json';
    $zipFile = $upgradeDir . 'calibrate_' . $currentVersion . '.zip';
    
    if (!file_exists($zipFile) || filesize($zipFile) <= 0) {
        Output::error('校准安装包不存在，请重新下载');
    }
    
    // 解压到临时目录
    $extractDir = $upgradeDir . 'calibrate_extract_' . time() . '/';
    @mkdir($extractDir, 0755, true);
    
    $extractResult = safeExtractZipFile($zipFile, $extractDir);
    if (!$extractResult['success']) {
        @unlink($zipFile);
        deleteDirectory($extractDir);
        Output::error($extractResult['error']);
    }
    @unlink($zipFile);
    @unlink($taskFile);
    
    // 检测解压后的目录结构
    $sourceDir = $extractDir;
    $subDirs = glob($extractDir . '*', GLOB_ONLYDIR);
    if (count($subDirs) === 1) {
        $subDir = $subDirs[0];
        if (file_exists($subDir . '/index.php') || file_exists($subDir . '/init.php')) {
            $sourceDir = $subDir . '/';
        }
    }
    
    // 备份当前程序文件
    $backupDir = DC_ROOT . '/content/backup/calibrate/';
    if (!is_dir($backupDir)) {
        @mkdir($backupDir, 0755, true);
    }
    
    $backupFile = $backupDir . 'backup_' . date('Ymd_His') . '_v' . $currentVersion . '.zip';
    $backupResult = createCalibrateBackup($sourceDir, DC_ROOT, $backupFile);
    if (!$backupResult['success']) {
        deleteDirectory($extractDir);
        Output::error('备份当前文件失败：' . $backupResult['error']);
    }
    
    // 覆盖文件
    $result = copyDirectoryForUpgrade($sourceDir, DC_ROOT);
    
    // 清理
    deleteDirectory($extractDir);
    
    Output::ok([
        'message' => '文件校准成功',
        'backup' => str_replace(DC_ROOT, '', $backupFile),
        'updated' => $result['updated'],
        'skipped' => $result['skipped'],
        'failed' => $result['failed'] ?? 0,
        'failed_files' => array_slice($result['failed_files'] ?? [], 0, 10),
        'version' => $currentVersion
    ]);
}

/**
 * 文件校准 - 异步下载安装包（CDN兼容）
 * 与 downloadUpgrade 同样的异步模式
 */
function calibrateFiles() {
    if (ROLE !== 'admin') {
        Output::error('无权限操作');
    }
    
    LoginAuth::checkToken();
    
    $currentVersion = Option::DC_VERSION;
    $domain = getTopHost();
    $licenseKey = getLicenseKey();
    
    // 调用授权服务器获取下载链接
    $apiUrl = Register::getAuthCenterUrl();
    $apiUrl = str_replace('/user/', '/api.php', $apiUrl);
    
    $params = [
        'action' => 'calibrate',
        'version' => $currentVersion,
        'license_key' => $licenseKey,
        'domain' => $domain
    ];
    
    $response = emCurl($apiUrl, http_build_query($params), true, [], 30);
    
    if (empty($response)) {
        Output::error('无法连接授权服务器，请检查网络');
    }
    
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        Output::error('服务器返回数据异常');
    }
    
    if (($data['code'] ?? -1) !== 0 || empty($data['data']['download_url'])) {
        Output::error($data['msg'] ?? '获取下载链接失败');
    }
    
    $downloadUrl = $data['data']['download_url'];
    
    // 创建目录
    $upgradeDir = DC_ROOT . '/content/upgrade/';
    if (!is_dir($upgradeDir)) {
        @mkdir($upgradeDir, 0755, true);
    }
    if (!is_dir($upgradeDir) || !is_writable($upgradeDir)) {
        Output::error('临时目录不可写，请检查 content/upgrade 权限');
    }
    
    $zipFile = $upgradeDir . 'calibrate_' . $currentVersion . '.zip';
    $taskFile = $upgradeDir . '.calibrate_task.json';
    
    // 写入任务状态
    $taskData = [
        'status' => 'downloading',
        'version' => $currentVersion,
        'started_at' => time(),
        'file' => $zipFile,
    ];
    file_put_contents($taskFile, json_encode($taskData));
    
    // ====== 立即返回响应（CDN兼容） ======
    ignore_user_abort(true);
    $jsonResp = json_encode(['code' => 0, 'data' => ['status' => 'downloading', 'version' => $currentVersion]]);
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Length: ' . strlen($jsonResp));
    header('Connection: close');
    while (ob_get_level()) {
        ob_end_clean();
    }
    echo $jsonResp;
    flush();
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }
    
    // ====== 后台下载 ======
    set_time_limit(600);
    ini_set('memory_limit', '512M');
    
    @unlink($zipFile);
    $ch = curl_init();
    $fp = fopen($zipFile, 'wb');
    if (!$fp) {
        $taskData['status'] = 'failed';
        $taskData['error'] = '无法创建文件';
        file_put_contents($taskFile, json_encode($taskData));
        exit;
    }
    
    curl_setopt($ch, CURLOPT_URL, $downloadUrl);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'DCSHOP/' . Option::DC_VERSION);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: */*']);
    
    // 进度回调
    $lastProgressWrite = 0;
    curl_setopt($ch, CURLOPT_NOPROGRESS, false);
    curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function($ch, $dlTotal, $dlNow, $ulTotal, $ulNow) use ($taskFile, &$taskData, &$lastProgressWrite) {
        $now = time();
        if ($dlTotal > 0 && $now - $lastProgressWrite >= 2) {
            $lastProgressWrite = $now;
            $taskData['dl_total'] = (int)$dlTotal;
            $taskData['dl_now'] = (int)$dlNow;
            $taskData['dl_percent'] = min(99, (int)round($dlNow / $dlTotal * 100));
            @file_put_contents($taskFile, json_encode($taskData));
        }
        return 0;
    });
    
    $ok = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    fclose($fp);
    
    $fileSize = file_exists($zipFile) ? filesize($zipFile) : 0;
    
    if (!$ok || $httpCode < 200 || $httpCode >= 300 || $fileSize <= 0) {
        $errMsg = '下载失败 HTTP=' . $httpCode;
        if ($curlError) $errMsg .= ' ' . $curlError;
        @unlink($zipFile);
        $taskData['status'] = 'failed';
        $taskData['error'] = $errMsg;
        file_put_contents($taskFile, json_encode($taskData));
        exit;
    }
    
    // 验证ZIP
    if ($fileSize < 4096) {
        $head = @file_get_contents($zipFile, false, null, 0, 4);
        if ($head !== false && substr($head, 0, 2) !== "PK") {
            @unlink($zipFile);
            $taskData['status'] = 'failed';
            $taskData['error'] = '下载的不是ZIP文件';
            file_put_contents($taskFile, json_encode($taskData));
            exit;
        }
    }
    
    // 下载成功
    $taskData['status'] = 'completed';
    $taskData['size'] = $fileSize;
    $taskData['finished_at'] = time();
    file_put_contents($taskFile, json_encode($taskData));
    exit;
}

/**
 * 创建校准备份 - 将当前网站所有程序文件打包为ZIP完整快照
 * 遍历网站根目录，跳过用户数据目录（skipList）
 */
function createCalibrateBackup($sourceDir, $rootDir, $backupFile) {
    $result = ['success' => true, 'error' => '', 'count' => 0];
    
    $zip = new ZipArchive();
    if ($zip->open($backupFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return ['success' => false, 'error' => '无法创建备份文件', 'count' => 0];
    }
    
    $rootDirNorm = rtrim(str_replace('\\', '/', $rootDir), '/') . '/';
    
    // 递归遍历当前网站根目录，备份所有程序文件
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($rootDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $item) {
        $pathname = str_replace('\\', '/', $item->getPathname());
        $relativePath = ltrim(str_replace($rootDirNorm, '', $pathname), '/');
        
        // 跳过用户数据和配置
        if (shouldSkip($relativePath)) {
            continue;
        }
        
        if ($item->isFile()) {
            $zip->addFile($item->getPathname(), $relativePath);
            $result['count']++;
        }
    }
    
    $zip->close();
    return $result;
}

/**
 * 恢复校准备份 - 从备份ZIP中提取所有文件覆盖到网站根目录
 */
function restoreCalibrateBackup() {
    set_time_limit(300);
    ini_set('memory_limit', '256M');
    
    if (ROLE !== 'admin') {
        Output::error('无权限操作');
    }
    
    LoginAuth::checkToken();
    
    $filename = Input::postStrVar('filename');
    if (empty($filename) || !preg_match('/^backup_\d{8}_\d{6}_v[\d.]+\.zip$/', $filename)) {
        Output::error('无效的备份文件名');
    }
    
    $backupFile = DC_ROOT . '/content/backup/calibrate/' . $filename;
    
    if (!file_exists($backupFile)) {
        Output::error('备份文件不存在');
    }
    
    // 解压到临时目录
    $tempDir = DC_ROOT . '/content/upgrade/restore_' . time() . '/';
    @mkdir($tempDir, 0755, true);
    
    $extractResult = safeExtractZipFile($backupFile, $tempDir);
    if (!$extractResult['success']) {
        deleteDirectory($tempDir);
        Output::error('解压备份失败：' . $extractResult['error']);
    }
    
    // 复制文件到网站根目录
    $result = copyDirectoryForUpgrade($tempDir, DC_ROOT);
    
    // 清理
    deleteDirectory($tempDir);
    
    $response = [
        'message' => '备份恢复成功',
        'updated' => $result['updated'],
        'failed' => $result['failed'] ?? 0,
        'failed_files' => array_slice($result['failed_files'] ?? [], 0, 10)
    ];
    
    Output::ok($response);
}

/**
 * 删除校准备份
 */
function deleteCalibrateBackup() {
    if (ROLE !== 'admin') {
        Output::error('无权限操作');
    }
    
    LoginAuth::checkToken();
    
    $filename = Input::postStrVar('filename');
    if (empty($filename) || !preg_match('/^backup_\d{8}_\d{6}_v[\d.]+\.zip$/', $filename)) {
        Output::error('无效的备份文件名');
    }
    
    $backupFile = DC_ROOT . '/content/backup/calibrate/' . $filename;
    
    if (!file_exists($backupFile)) {
        Output::error('备份文件不存在');
    }
    
    if (!@unlink($backupFile)) {
        Output::error('删除失败，请检查文件权限');
    }
    
    Output::ok('备份已删除');
}
