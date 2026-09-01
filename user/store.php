<?php
/**
 * store
 */

/**
 * @var string $action
 * @var object $CACHE
 */

require_once 'globals.php';

$Store_Model = new Store_Model();




/**
 * 分站应用安装 - 异步下载 + 轮询进度（CDN兼容）
 */
if ($action === 'install') {
    $source = isset($_POST['source']) ? trim($_POST['source']) : '';
    $cdn_source = isset($_POST['cdn_source']) ? trim($_POST['cdn_source']) : '';
    $source_type = isset($_POST['type']) ? trim($_POST['type']) : '';
    $plugin_id = isset($_POST['plugin_id']) ? (int)$_POST['plugin_id'] : 0;
    $plugin_slug = isset($_POST['slug']) ? trim($_POST['slug']) : '';
    $source_type_aliases = [
        'home' => 'template',
        'home_template' => 'template',
        'station' => 'template',
        'station_template' => 'template',
        'user' => 'user_template',
        'user_tpl' => 'user_template',
        'bottom_nav' => 'bottom_nav_template',
        'bottom_nav_tpl' => 'bottom_nav_template',
        'blog' => 'blog_template',
        'blog_tpl' => 'blog_template',
    ];
    if (isset($source_type_aliases[$source_type])) {
        $source_type = $source_type_aliases[$source_type];
    }

    global $userData;

    // 确定下载URL
    $downloadUrl = '';
    if ($plugin_id > 0) {
        $r = $Store_Model->verifyDownload($plugin_id);
        if ($r == -1) {
            output::error('授权服务器请求失败，请重试');
        }
        if ($r == 2) {
            output::error('您当前未购买此应用，无法安装');
        }
        $downloadUrl = $Store_Model->getDownloadUrl($plugin_id);
    } elseif ($cdn_source) {
        $downloadUrl = $cdn_source;
    } else {
        if (empty($source)) {
            output::error('安装失败，插件文件源无效');
        }
        $downloadUrl = DC_LINE[CURRENT_LINE]['value'] . $source;
    }

    // 确定解压路径
    if ($source_type == 'plugin') {
        $unzip_path = DC_ROOT . '/content/plugins/';
        $suc_url = 'plugin.php';
        $unzip_type = 'plugin';
    } elseif ($source_type == 'user_template') {
        $unzip_path = DC_ROOT . '/content/user_templates/';
        $suc_url = DC_URL . 'user/station.php?action=setting_tpl';
        $unzip_type = 'tpl';
    } elseif ($source_type == 'bottom_nav_template') {
        $unzip_path = DC_ROOT . '/content/bottom_nav_templates/';
        $suc_url = DC_URL . 'user/station.php?action=setting_tpl';
        $unzip_type = 'tpl';
    } elseif ($source_type == 'blog_template') {
        $unzip_path = DC_ROOT . '/content/blog_templates/';
        $suc_url = DC_URL . 'admin/template.php?tab=blog';
        $unzip_type = 'tpl';
    } elseif ($source_type == 'template' || $source_type == 'tpl') {
        $unzip_path = DC_ROOT . '/content/templates/';
        $suc_url = DC_URL . 'user/station.php?action=setting_tpl';
        $unzip_type = 'tpl';
    } else {
        $unzip_path = DC_ROOT . '/content/templates/';
        $suc_url = DC_URL . 'user/station.php?action=setting';
        $unzip_type = 'tpl';
    }

    // 准备任务文件
    $cacheDir = DC_ROOT . '/content/cache/';
    if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
    $taskFile = $cacheDir . '.user_store_install_task.json';
    $zipFile = $cacheDir . 'user_store_install_' . ($plugin_id ?: md5($downloadUrl)) . '.zip';

    $taskData = [
        'status' => 'downloading',
        'plugin_id' => $plugin_id,
        'plugin_slug' => $plugin_slug,
        'source' => $source,
        'source_type' => $source_type,
        'unzip_path' => $unzip_path,
        'unzip_type' => $unzip_type,
        'suc_url' => $suc_url,
        'zip_file' => $zipFile,
        'started_at' => time(),
        'station_id' => (int)($userData['station']['id'] ?? 0),
    ];
    file_put_contents($taskFile, json_encode($taskData));

    // 立即返回响应（CDN兼容）
    ignore_user_abort(true);
    $jsonResp = json_encode(['code' => 0, 'data' => ['status' => 'downloading']]);
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Length: ' . strlen($jsonResp));
    header('Connection: close');
    while (ob_get_level()) ob_end_clean();
    echo $jsonResp;
    flush();
    if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();

    // 后台下载
    set_time_limit(300);
    @unlink($zipFile);
    $ch = curl_init();
    $fp = fopen($zipFile, 'wb');
    if (!$fp) {
        $taskData['status'] = 'failed';
        $taskData['error'] = '无法创建临时文件';
        file_put_contents($taskFile, json_encode($taskData));
        exit;
    }
    curl_setopt($ch, CURLOPT_URL, $downloadUrl);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'DCSHOP/' . Option::DC_VERSION);

    $lastProgressWrite = 0;
    curl_setopt($ch, CURLOPT_NOPROGRESS, false);
    curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function($ch, $dlTotal, $dlNow, $ulTotal, $ulNow) use ($taskFile, &$taskData, &$lastProgressWrite) {
        $now = time();
        if ($dlTotal > 0 && $now - $lastProgressWrite >= 1) {
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
        @unlink($zipFile);
        $taskData['status'] = 'failed';
        $taskData['error'] = '下载失败 HTTP=' . $httpCode . ($curlError ? ' ' . $curlError : '');
        file_put_contents($taskFile, json_encode($taskData));
        exit;
    }

    if (filesize($zipFile) < 100) {
        $content = @file_get_contents($zipFile);
        @unlink($zipFile);
        $taskData['status'] = 'failed';
        $taskData['error'] = '下载失败：' . substr($content, 0, 200);
        file_put_contents($taskFile, json_encode($taskData));
        exit;
    }

    // ====== 覆盖安装：先移除已存在的目标目录 ======
    // Windows 下 ZipArchive::extractTo() 无法覆盖被 opcache 或其他进程锁定的文件，
    // 策略：opcache_reset → 尝试删除 → 若删除失败则 rename 旧目录 → 解压后清理。
    $preZip = new ZipArchive();
    $_oldDirRenamed = '';
    if ($preZip->open($zipFile) === TRUE) {
        $firstEntry = $preZip->getNameIndex(0);
        $preZip->close();
        if ($firstEntry) {
            $parts = explode('/', $firstEntry, 2);
            $topDir = isset($parts[0]) ? $parts[0] : '';
            if ($topDir !== '' && is_dir($unzip_path . $topDir)) {
                if (function_exists('opcache_reset')) {
                    @opcache_reset();
                }
                @emDeleteFile($unzip_path . $topDir);
                if (is_dir($unzip_path . $topDir)) {
                    $_oldDirRenamed = $unzip_path . $topDir . '_old_' . time();
                    if (!@rename($unzip_path . $topDir, $_oldDirRenamed)) {
                        $_oldDirRenamed = '';
                    }
                }
            }
        }
    }

    $ret = emUnZip($zipFile, $unzip_path, $unzip_type);
    @unlink($zipFile);

    switch ($ret) {
        case 0:
            // station_plugin 记录
            $db = Database::getInstance();
            $stationTypeMap = [
                'plugin' => 'plugin',
                'template' => 'tpl',
                'tpl' => 'tpl',
                'user_template' => 'user_tpl',
                'bottom_nav_template' => 'bottom_nav',
            ];
            if (isset($stationTypeMap[$source_type])) {
                $pluginNameEn = $plugin_slug !== '' ? $plugin_slug : $source;
                if ($pluginNameEn !== '') {
                    $stationId = (int)($userData['station']['id'] ?? 0);
                    $stationType = $stationTypeMap[$source_type];
                    $safePluginNameEn = addslashes($pluginNameEn);
                    $safeStationType = addslashes($stationType);
                    $existRow = $db->once_fetch_array("SELECT id FROM " . DB_PREFIX . "station_plugin WHERE station_id={$stationId} AND type='{$safeStationType}' AND plugin_name_en='{$safePluginNameEn}' LIMIT 1");
                    if (empty($existRow)) {
                        $add = [
                            'plugin_name_en' => $pluginNameEn,
                            'type' => $stationType,
                            'pc_switch' => 'n',
                            'tel_switch' => 'n',
                            'station_id' => $stationId
                        ];
                        $db->add('station_plugin', $add);
                    }
                }
            }
            $taskData['status'] = 'completed';
            $taskData['finished_at'] = time();
            $taskData['suc_url'] = $suc_url;
            file_put_contents($taskFile, json_encode($taskData));
            // 清除管理后台更新角标缓存，避免侧边栏红点残留
            @unlink(DC_ROOT . '/content/cache/update_badge_count.php');
            // 安装成功后尝试清理被 rename 的旧目录
            if ($_oldDirRenamed !== '' && is_dir($_oldDirRenamed)) {
                @emDeleteFile($_oldDirRenamed);
            }
            break;
        case 1:
        case 2:
            $taskData['status'] = 'failed';
            $taskData['error'] = '安装失败，请检查content下目录是否可写';
            file_put_contents($taskFile, json_encode($taskData));
            // 解压失败时，如果旧目录被重命名了，尝试恢复
            if ($_oldDirRenamed !== '' && is_dir($_oldDirRenamed)) {
                $originalDir = preg_replace('/_old_\\d+$/', '', $_oldDirRenamed);
                if (!is_dir($originalDir)) {
                    @rename($_oldDirRenamed, $originalDir);
                }
            }
            break;
        case 3:
            $taskData['status'] = 'failed';
            $taskData['error'] = '安装失败，请安装php的Zip扩展';
            file_put_contents($taskFile, json_encode($taskData));
            break;
        default:
            $taskData['status'] = 'failed';
            $taskData['error'] = '安装失败，不是有效的安装包';
            file_put_contents($taskFile, json_encode($taskData));
            break;
    }
    exit;
}

/**
 * 分站应用安装 - 查询安装进度
 */
if ($action === 'install_progress') {
    $taskFile = DC_ROOT . '/content/cache/.user_store_install_task.json';
    if (!file_exists($taskFile)) {
        Output::ok(['status' => 'idle']);
    }
    $task = @json_decode(@file_get_contents($taskFile), true);
    if (!$task) {
        Output::ok(['status' => 'idle']);
    }
    if (isset($task['started_at']) && time() - $task['started_at'] > 300) {
        @unlink($taskFile);
        Output::ok(['status' => 'expired', 'msg' => '安装任务已超时']);
    }
    if (in_array($task['status'] ?? '', ['completed', 'failed'])) {
        @unlink($taskFile);
    }
    Output::ok($task);
}
