<?php
/**
 * store
 */

// 强制刷新本文件的OPCache缓存（确保修改立即生效）
if (function_exists('opcache_invalidate')) @opcache_invalidate(__FILE__, true);

/**
 * @var string $action
 * @var object $CACHE
 */

require_once 'globals.php';

$Store_Model = new Store_Model();

if ($action === 'refresh_auth_cache') {
    $authResult = Register::checkDomain(true);
    Output::ok([
        'authorized' => !empty($authResult['authorized']),
        'account_bound' => !empty($authResult['account_bound']),
        'msg' => $authResult['msg'] ?? ''
    ]);
}

// 应用商店页面：授权已移除，不再检查绑定状态（原弹窗逻辑已禁用）
$_storeBindPopup = '';

// 分类列表只在渲染页面时获取，AJAX 分页接口不额外请求分类
$categories = [];
$plugin_type_arr = [['id' => 0, 'title' => '全部插件']];

if (empty($action)) {
    // 移除授权检查
    // Register::isRegServer();
    $emkey = getMyEmKey();
    $categories = $Store_Model->getCategories('plugin');
    $plugin_type_arr = [['id' => 0, 'title' => '全部插件']];
    foreach ($categories as $cat) {
        $plugin_type_arr[] = ['id' => $cat['id'], 'title' => $cat['name']];
    }
    $br = '<a href="./">数据中心</a><a href="./store.php">应用商店</a><a><cite>全部应用</cite></a>';
    include View::getAdmView('header');
    require_once(View::getAdmView('store'));
    include View::getAdmView('footer');
    echo $_storeBindPopup;
    View::output();
}
if ($action === 'plu') {
    // 移除授权检查
    // Register::isRegServer();
    $emkey = getMyEmKey();
    $br = '<a href="./">数据中心</a><a href="./store.php">应用商店</a><a><cite>扩展插件</cite></a>';

    $plugin_type = Input::getStrVar('plugin_type', 0);
    $categories = $Store_Model->getCategories('plugin');
    $plugin_type_arr = [['id' => 0, 'title' => '全部插件']];
    foreach ($categories as $cat) {
        $plugin_type_arr[] = ['id' => $cat['id'], 'title' => $cat['name']];
    }
    // 支持用 category_slug 参数按分类标识符筛选
    $category_slug = Input::getStrVar('category');
    if ($category_slug) {
        foreach ($categories as $cat) {
            if ($cat['slug'] === $category_slug) {
                $plugin_type = $cat['id'];
                break;
            }
        }
    }
    $title = Input::getStrVar('title');
    include View::getAdmView('header');
    require_once(View::getAdmView('store_plu'));
    include View::getAdmView('footer');
    echo $_storeBindPopup;
    View::output();
}
if ($action === 'tpl') {
    // 移除授权检查
    // Register::isRegServer();
    $emkey = getMyEmKey();
    $br = '<a href="./">数据中心</a><a href="./store.php">应用商店</a><a><cite>模板主题</cite></a>';

    $template_type = Input::getStrVar('template_type', 'all');
    $template_type_aliases = [
        '' => 'all',
        '0' => 'all',
        'tpl' => 'template',
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
    if (isset($template_type_aliases[$template_type])) {
        $template_type = $template_type_aliases[$template_type];
    }
    if (!in_array($template_type, ['all', 'template', 'user_template', 'bottom_nav_template', 'blog_template'], true)) {
        $template_type = 'all';
    }

    // 获取模板分类
    $tpl_categories = $Store_Model->getCategories('template', $template_type);

    $tpl_type_arr = [['id' => 0, 'title' => '全部模板']];
    foreach ($tpl_categories as $cat) {
        $tpl_type_arr[] = ['id' => $cat['id'], 'title' => $cat['name']];
    }
    $tpl_type = Input::getStrVar('tpl_type', 0);

    include View::getAdmView('header');
    require_once(View::getAdmView('store_tpl'));
    include View::getAdmView('footer');
    echo $_storeBindPopup;
    View::output();
}

/**
 * 获取全部应用
 */
if($action == 'index'){
    $type = 'all';
    $page = Input::getIntVar('page', 1);
    $sid = Input::getStrVar('sid');
    $keyword = Input::getStrVar('keyword');
    $pageNum = Input::getIntVar('limit');
    $store = $Store_Model->getList($type, $page, $pageNum, $keyword, $sid);
    $apps = storeHandleData($store['list']);
    $count = $store['count'];
    output::data($apps, $count);
}

if($action == 'tpl_ajax'){
    $type = 'template';
    $page = Input::getIntVar('page', 1);
    $sid = Input::getStrVar('tpl_type');  // 分类ID
    $keyword = Input::getStrVar('keyword');
    $pageNum = Input::getIntVar('limit');
    $templateType = Input::getStrVar('template_type', 'all');
    $store = $Store_Model->getList($type, $page, $pageNum, $keyword, $sid, $templateType);
    $apps = storeHandleData($store['list']);
    $count = $store['count'];
    output::data($apps, $count);
}



if($action == 'plu_ajax'){
    $type = 'plugin';
    $page = Input::getIntVar('page', 1);
    $sid = Input::getStrVar('plugin_type');
    $keyword = Input::getStrVar('keyword');
    $pageNum = Input::getIntVar('limit');
    $store = $Store_Model->getList($type, $page, $pageNum, $keyword, $sid);
    $apps = storeHandleData($store['list']);
    $count = $store['count'];
    output::data($apps, $count);
}



if ($action === 'mine') {
    $addons = $Store_Model->getMyAddon();
    $sub_title = '我的已购';

    include View::getAdmView('header');
    require_once(View::getAdmView('store_mine'));
    include View::getAdmView('footer');
    echo $_storeBindPopup;
    View::output();
}

if ($action === 'purchased') {
    // 移除授权检查
    // Register::isRegServer();
    $emkey = getMyEmKey();
    $br = '<a href="./">数据中心</a><a href="./store.php">应用商店</a><a><cite>已购买</cite></a>';
    
    include View::getAdmView('header');
    require_once(View::getAdmView('store_purchased'));
    include View::getAdmView('footer');
    echo $_storeBindPopup;
    View::output();
}

/**
 * 获取已购买应用
 */
if($action == 'purchased_ajax'){
    $page = Input::getIntVar('page', 1);
    $pageNum = Input::getIntVar('limit', 9999);
    $filter = Input::getStrVar('filter', 'all'); // all, permanent, monthly, expired, uninstalled
    $keyword = Input::getStrVar('keyword', '');
    
    // 先获取全部数据用于统计待安装数量
    $allStore = $Store_Model->getMyPurchasedApps(1, 9999, 'all');
    $allApps = storeHandleData($allStore['list']);
    $stats = $allStore['stats'] ?? ['all' => 0, 'permanent' => 0, 'monthly' => 0, 'expired' => 0, 'uninstalled' => 0];
    
    // 统计待安装数量（已购买但未安装，且未过期）
    $uninstalledCount = 0;
    foreach ($allApps as $app) {
        if ($app['is_install'] === 'n' && $app['is_expired'] != 1) {
            $uninstalledCount++;
        }
    }
    $stats['uninstalled'] = $uninstalledCount;
    
    // 根据筛选条件过滤数据
    if ($filter === 'uninstalled') {
        // 待安装：未安装且未过期
        $filteredApps = [];
        foreach ($allApps as $app) {
            if ($app['is_install'] === 'n' && $app['is_expired'] != 1) {
                $filteredApps[] = $app;
            }
        }
        $apps = $filteredApps;
        $count = count($apps);
    } else if ($filter === 'all') {
        $apps = $allApps;
        $count = count($apps);
    } else {
        // 其他筛选条件，从API获取
        $store = $Store_Model->getMyPurchasedApps($page, $pageNum, $filter);
        $apps = storeHandleData($store['list']);
        $count = $store['count'];
    }
    
    // 关键词过滤
    if (!empty($keyword)) {
        $keyword = strtolower($keyword);
        $apps = array_filter($apps, function($app) use ($keyword) {
            $name = strtolower($app['name'] ?? '');
            $desc = strtolower($app['description'] ?? '');
            return strpos($name, $keyword) !== false || strpos($desc, $keyword) !== false;
        });
        $apps = array_values($apps);
        $count = count($apps);
    }
    
    // 自定义输出，包含stats
    header('Content-Type: application/json; charset=UTF-8');
    $result = [
        'code' => 0,
        'msg'  => 'ok',
        'data' => $apps,
        'count' => $count,
        'stats' => $stats
    ];
    die(json_encode($result, JSON_UNESCAPED_UNICODE));
}

if ($action === 'svip') {
    // 检查是否已授权
    if (!Register::isRegLocal()) {
        header('Location: ./auth.php');
        exit;
    }
    
    // 获取用户账户信息
    $userInfo = $Store_Model->getUserInfo();
    $balance = 0;
    $username = '';
    $smsBalance = 0;
    $smsPackages = [];
    if ($userInfo['code'] == 200 && isset($userInfo['data'])) {
        $balance = $userInfo['data']['balance'] ?? 0;
        $smsBalance = $userInfo['data']['sms_balance'] ?? 0;
        $smsPackages = $userInfo['data']['sms_packages'] ?? [];
        $username = $userInfo['data']['username'] ?? '';
        // 账号脱敏：保留前2位和后2位，中间用****替代
        if (mb_strlen($username) > 4) {
            $username = mb_substr($username, 0, 2) . '****' . mb_substr($username, -2);
        } elseif (mb_strlen($username) > 2) {
            $username = mb_substr($username, 0, 1) . '****' . mb_substr($username, -1);
        }
    }
    $br = '<a href="./">数据中心</a><a href="./store.php">应用商店</a><a><cite>余额充值</cite></a>';

    include View::getAdmView('header');
    require_once(View::getAdmView('store_svip'));
    include View::getAdmView('footer');
    echo $_storeBindPopup;
    View::output();
}

// ========== 充值相关 AJAX 代理 ==========

if ($action === 'recharge_methods') {
    $result = $Store_Model->getPayMethods();
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode($result, JSON_UNESCAPED_UNICODE));
}

if ($action === 'create_recharge') {
    $amount = (float)($_POST['amount'] ?? 0);
    $payType = $_POST['pay_type'] ?? '';
    $payIndex = (int)($_POST['pay_index'] ?? 0);
    $returnUrl = $_POST['return_url'] ?? '';
    $result = $Store_Model->createRecharge($amount, $payType, $payIndex, $returnUrl);
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode($result, JSON_UNESCAPED_UNICODE));
}

if ($action === 'check_recharge') {
    $outTradeNo = $_POST['out_trade_no'] ?? $_GET['out_trade_no'] ?? '';
    $payType = $_POST['pay_type'] ?? $_GET['pay_type'] ?? '';
    $payIndex = (int)($_POST['idx'] ?? $_GET['idx'] ?? 0);
    $result = $Store_Model->checkRecharge($outTradeNo, $payType, $payIndex);
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode($result, JSON_UNESCAPED_UNICODE));
}

if ($action === 'buy_sms_pack') {
    $packIndex = (int)($_POST['pack_index'] ?? -1);
    $result = $Store_Model->buySmsPackage($packIndex);
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode($result, JSON_UNESCAPED_UNICODE));
}

if ($action === 'top') {
    $addons = $Store_Model->getTopAddon();
    output::ok($addons);
}

if ($action === 'error') {
    $keyword = '';
    $sub_title = '';
    $sid = '';

    $br = '<ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="./">数据中心</a></li>
        <li class="breadcrumb-item"><a href="./store.php">应用商店</a></li>
        <li class="breadcrumb-item active" aria-current="page">全部应用</li>
    </ol>';

    include View::getAdmView('header');
    require_once(View::getAdmView('store'));
    include View::getAdmView('footer');
    echo $_storeBindPopup;
    View::output();
}

/**
 * 购买应用
 */
if ($action === 'buy') {
    $app_id = Input::postIntVar('app_id');
    $buy_type = Input::postStrVar('buy_type', 'permanent'); // permanent=买断, monthly=月付, trial=试用
    $months = Input::postIntVar('months', 1);
    
    if (!$app_id) {
        output::error('参数错误');
    }
    
    // 验证购买类型
    if (!in_array($buy_type, ['permanent', 'monthly', 'trial'])) {
        $buy_type = 'permanent';
    }
    
    $result = $Store_Model->buyApp($app_id, $buy_type, $months);

    // 购买接口统一返回 HTTP 200 的业务 JSON，避免 $.post 在余额不足等业务错误时进入 fail，
    // 同时保留授权端返回的 data（如 need_recharge、balance、price），供前端弹出充值提示。
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode([
        'code' => (isset($result['code']) && (int)$result['code'] === 200) ? 0 : 1,
        'msg'  => $result['msg'] ?? ((isset($result['code']) && (int)$result['code'] === 200) ? '购买成功' : '购买失败'),
        'data' => $result['data'] ?? []
    ], JSON_UNESCAPED_UNICODE));
}

/**
 * 应用安装 - 异步下载 + 轮询进度（CDN兼容）
 */
if ($action === 'install') {
    $plugin_id = Input::postStrVar('plugin_id');
    $source_type = Input::postStrVar('type');
    $source_type_aliases = [
        'tpl' => 'template',
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

    // 验证下载权限
    $r = $Store_Model->verifyDownload($plugin_id);
    if ($r == -1) {
        Output::error('授权服务器请求失败，请重试');
    }
    if ($r == 2) {
        Output::error('您当前未购买此应用，无法安装');
    }

    // 确定解压路径
    if ($source_type == 'plugin') {
        $unzip_path = DC_ROOT . '/content/plugins/';
        $unzip_type = 'plugin';
    } elseif ($source_type == 'user_template') {
        $unzip_path = DC_ROOT . '/content/user_templates/';
        $unzip_type = 'tpl';
    } elseif ($source_type == 'bottom_nav_template') {
        $unzip_path = DC_ROOT . '/content/bottom_nav_templates/';
        $unzip_type = 'tpl';
    } elseif ($source_type == 'blog_template') {
        $unzip_path = DC_ROOT . '/content/blog_templates/';
        $unzip_type = 'tpl';
    } elseif ($source_type == 'template') {
        $unzip_path = DC_ROOT . '/content/templates/';
        $unzip_type = 'tpl';
    } else {
        Output::error('安装失败，未知的应用类型');
    }

    // 获取下载URL
    $downloadUrl = $Store_Model->getDownloadUrl($plugin_id);

    // 准备任务文件
    $cacheDir = DC_ROOT . '/content/cache/';
    if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
    $taskFile = $cacheDir . '.store_install_task.json';
    $zipFile = $cacheDir . 'store_install_' . $plugin_id . '.zip';

    $taskData = [
        'status' => 'downloading',
        'plugin_id' => $plugin_id,
        'source_type' => $source_type,
        'unzip_path' => $unzip_path,
        'unzip_type' => $unzip_type,
        'zip_file' => $zipFile,
        'started_at' => time(),
    ];
    file_put_contents($taskFile, json_encode($taskData));

    // ====== 立即返回响应（CDN兼容） ======
    ignore_user_abort(true);
    $jsonResp = json_encode(['code' => 0, 'data' => ['status' => 'downloading']]);
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Length: ' . strlen($jsonResp));
    header('Connection: close');
    while (ob_get_level()) ob_end_clean();
    echo $jsonResp;
    flush();
    if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();

    // ====== 后台下载 ======
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
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: */*']);

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

    // 下载完成后直接解压安装。授权端业务错误通常会返回 JSON（HTTP 200），这里提前识别，
    // 避免把 JSON 当 ZIP 解压，导致前端只看到“不是有效安装包”的笼统提示。
    $zipHead = @file_get_contents($zipFile, false, null, 0, 512);
    if (strncmp((string)$zipHead, 'PK', 2) !== 0) {
        $content = @file_get_contents($zipFile);
        @unlink($zipFile);
        $json = @json_decode((string)$content, true);
        $taskData['status'] = 'failed';
        if (is_array($json) && !empty($json['msg'])) {
            $taskData['error'] = '下载失败：' . $json['msg'];
        } else {
            $taskData['error'] = '下载失败：授权端未返回有效ZIP安装包';
        }
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
    // 导致已安装（随安装包打包）的插件/模板更新时报"目录不可写"。
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
                // 1) 清除 opcache 缓存
                if (function_exists('opcache_reset')) {
                    @opcache_reset();
                }
                // 2) 尝试递归删除
                @emDeleteFile($unzip_path . $topDir);
                // 3) 如果删除失败（Windows 文件锁），改用 rename 移走旧目录
                if (is_dir($unzip_path . $topDir)) {
                    $_oldDirRenamed = $unzip_path . $topDir . '_old_' . time();
                    if (!@rename($unzip_path . $topDir, $_oldDirRenamed)) {
                        // rename 也失败：记录错误但继续尝试解压（可能部分文件能覆盖）
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
            $taskData['status'] = 'completed';
            $taskData['finished_at'] = time();
            file_put_contents($taskFile, json_encode($taskData));
            // 清除更新角标缓存，避免侧边栏红点残留
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
                $originalDir = preg_replace('/_old_\d+$/', '', $_oldDirRenamed);
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
 * 应用安装 - 查询安装进度
 */
if ($action === 'install_progress') {
    $taskFile = DC_ROOT . '/content/cache/.store_install_task.json';
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
    // 任务完成或失败后清理任务文件
    if (in_array($task['status'] ?? '', ['completed', 'failed'])) {
        @unlink($taskFile);
    }
    Output::ok($task);
}

function storeHandleData($apps){
    $Plugin_Model = new Plugin_Model();
    $p = $Plugin_Model->getPlugins();
    $install_plugin = [];
    foreach($p as $val){
        $install_plugin[] = 'plugin:' . $val['Plugin'];
    }

    $Template_Model = new Template_Model();
    $p = $Template_Model->getTemplates();
    foreach($p as $val){
        $install_plugin[] = 'template:' . $val['tplfile'];
    }

    $p = $Template_Model->getUserCenterTemplates();
    foreach($p as $val){
        $install_plugin[] = 'user_template:' . $val['tplfile'];
    }

    $p = $Template_Model->getBottomNavTemplates();
    foreach($p as $val){
        $install_plugin[] = 'bottom_nav_template:' . $val['tplfile'];
    }

    $p = $Template_Model->getBlogTemplates();
    foreach($p as $val){
        $install_plugin[] = 'blog_template:' . $val['tplfile'];
    }

    if (is_dir(TPLS_PATH)) {
        $handle = @opendir(TPLS_PATH);
        if ($handle) {
            while ($file = @readdir($handle)) {
                if ($file !== '.' && $file !== '..' && is_dir(TPLS_PATH . $file)) {
                    $install_plugin[] = 'template:' . $file;
                }
            }
            closedir($handle);
        }
    }

    $reg_type = Register::getRegType();

    foreach($apps as $key => $val){
        $apps[$key]['reg_type'] = $reg_type;
        $appType = $val['type'] ?? 'plugin';
        if (in_array($appType, ['home_template', 'station_template', 'tpl', 'home', 'station'], true)) {
            $appType = 'template';
        } elseif (in_array($appType, ['user', 'user_tpl'], true)) {
            $appType = 'user_template';
        } elseif (in_array($appType, ['bottom_nav', 'bottom_nav_tpl'], true)) {
            $appType = 'bottom_nav_template';
        } elseif (in_array($appType, ['blog', 'blog_tpl'], true)) {
            $appType = 'blog_template';
        }

        $installSlug = !empty($val['english_name']) ? $val['english_name'] : ($val['slug'] ?? '');
        $installKey = $appType . ':' . $installSlug;

        if(in_array($installKey, $install_plugin, true)){
            $apps[$key]['is_install'] = 'y';
        }else{
            $apps[$key]['is_install'] = 'n';
        }
    }
    return $apps;
}