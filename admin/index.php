<?php
/**
 * control panel
 */

/**
 * @var string $action
 * @var object $CACHE
 */

require_once 'globals.php';

if (empty($action)) {
    $db = Database::getInstance();

    $db_prefix = DB_PREFIX;




    $avatar = empty($user_cache[UID]['avatar']) ? './views/images/avatar.svg' : '../' . $user_cache[UID]['avatar'];
    $name = $user_cache[UID]['name'];
    $br = '<a><cite>数据中心</cite></a>';

    // 销售额 - 使用更安全的查询
    try {
        // 今日销售额
        $today_start = strtotime(date('Y-m-d 00:00:00'));
        $today_end = strtotime(date('Y-m-d 23:59:59'));
        $sql = "SELECT COALESCE(SUM(amount), 0) AS today_sales_amount FROM `" . DB_PREFIX . "order` WHERE delete_time IS NULL AND pay_time IS NOT NULL AND (device IS NULL OR device != 'balance_recharge') AND create_time >= {$today_start} AND create_time <= {$today_end}";
        $row = $db->once_fetch_array($sql);
        $today_sales_amount = number_format(($row['today_sales_amount'] ?: 0) / 100, 2);
        
        // 昨日销售额
        $yesterday_start = strtotime(date('Y-m-d 00:00:00', strtotime('-1 day')));
        $yesterday_end = strtotime(date('Y-m-d 23:59:59', strtotime('-1 day')));
        $sql = "SELECT COALESCE(SUM(amount), 0) AS yesterday_sales_amount FROM `" . DB_PREFIX . "order` WHERE delete_time IS NULL AND pay_time IS NOT NULL AND (device IS NULL OR device != 'balance_recharge') AND create_time >= {$yesterday_start} AND create_time <= {$yesterday_end}";
        $row = $db->once_fetch_array($sql);
        $yesterday_sales_amount = number_format(($row['yesterday_sales_amount'] ?: 0) / 100, 2);
        
        // 本月销售额
        $month_start = strtotime(date('Y-m-01 00:00:00'));
        $month_end = strtotime(date('Y-m-t 23:59:59'));
        $sql = "SELECT COALESCE(SUM(amount), 0) AS current_month_sales_amount FROM `" . DB_PREFIX . "order` WHERE delete_time IS NULL AND pay_time IS NOT NULL AND (device IS NULL OR device != 'balance_recharge') AND create_time >= {$month_start} AND create_time <= {$month_end}";
        $row = $db->once_fetch_array($sql);
        $current_month_sales_amount = number_format(($row['current_month_sales_amount'] ?: 0) / 100, 2);
    } catch (Exception $e) {
        // 如果查询失败，使用默认值
        $today_sales_amount = '0.00';
        $yesterday_sales_amount = '0.00';
        $current_month_sales_amount = '0.00';
    }
    // 用户数量 - 使用更安全的查询
    try {
        // 今日注册量
        $today_start = strtotime(date('Y-m-d 00:00:00'));
        $today_end = strtotime(date('Y-m-d 23:59:59'));
        $sql = "SELECT COUNT(*) AS today_registrations FROM `{$db_prefix}user` WHERE create_time >= {$today_start} AND create_time <= {$today_end}";
        $row = $db->once_fetch_array($sql);
        $today_registrations = $row['today_registrations'] ?: 0;
        
        // 昨日注册量
        $yesterday_start = strtotime(date('Y-m-d 00:00:00', strtotime('-1 day')));
        $yesterday_end = strtotime(date('Y-m-d 23:59:59', strtotime('-1 day')));
        $sql = "SELECT COUNT(*) AS yesterday_registrations FROM `{$db_prefix}user` WHERE create_time >= {$yesterday_start} AND create_time <= {$yesterday_end}";
        $row = $db->once_fetch_array($sql);
        $yesterday_registrations = $row['yesterday_registrations'] ?: 0;
        
        // 本月注册量
        $month_start = strtotime(date('Y-m-01 00:00:00'));
        $month_end = strtotime(date('Y-m-t 23:59:59'));
        $sql = "SELECT COUNT(*) AS month_registrations FROM `{$db_prefix}user` WHERE create_time >= {$month_start} AND create_time <= {$month_end}";
        $row = $db->once_fetch_array($sql);
        $month_registrations = $row['month_registrations'] ?: 0;
        
        $user_panel = [
            'today_registrations' => $today_registrations,
            'yesterday_registrations' => $yesterday_registrations,
            'month_registrations' => $month_registrations
        ];
    } catch (Exception $e) {
        // 如果查询失败，使用默认值
        $user_panel = [
            'today_registrations' => 0,
            'yesterday_registrations' => 0,
            'month_registrations' => 0
        ];
    }

    // 订单数量 - 使用更安全的查询
    try {
        // 今日订单量
        $today_start = strtotime(date('Y-m-d 00:00:00'));
        $today_end = strtotime(date('Y-m-d 23:59:59'));
        $sql = "SELECT COUNT(*) AS today_orders FROM `{$db_prefix}order` WHERE delete_time IS NULL AND pay_time IS NOT NULL AND (device IS NULL OR device != 'balance_recharge') AND create_time >= {$today_start} AND create_time <= {$today_end}";
        $row = $db->once_fetch_array($sql);
        $today_orders = $row['today_orders'] ?: 0;
        
        // 昨日订单量
        $yesterday_start = strtotime(date('Y-m-d 00:00:00', strtotime('-1 day')));
        $yesterday_end = strtotime(date('Y-m-d 23:59:59', strtotime('-1 day')));
        $sql = "SELECT COUNT(*) AS yesterday_orders FROM `{$db_prefix}order` WHERE delete_time IS NULL AND pay_time IS NOT NULL AND (device IS NULL OR device != 'balance_recharge') AND create_time >= {$yesterday_start} AND create_time <= {$yesterday_end}";
        $row = $db->once_fetch_array($sql);
        $yesterday_orders = $row['yesterday_orders'] ?: 0;
        
        // 本月订单量
        $month_start = strtotime(date('Y-m-01 00:00:00'));
        $month_end = strtotime(date('Y-m-t 23:59:59'));
        $sql = "SELECT COUNT(*) AS month_orders FROM `{$db_prefix}order` WHERE delete_time IS NULL AND pay_time IS NOT NULL AND (device IS NULL OR device != 'balance_recharge') AND create_time >= {$month_start} AND create_time <= {$month_end}";
        $row = $db->once_fetch_array($sql);
        $month_orders = $row['month_orders'] ?: 0;
        
        $order_panel = [
            'today_orders' => $today_orders,
            'yesterday_orders' => $yesterday_orders,
            'month_orders' => $month_orders
        ];
    } catch (Exception $e) {
        // 如果查询失败，使用默认值
        $order_panel = [
            'today_orders' => 0,
            'yesterday_orders' => 0,
            'month_orders' => 0
        ];
    }

    // 待处理售后数量
    // 检查售后插件是否启用
    $activePlugins = Option::get('active_plugins') ?: [];
    $isAftersalePluginActive = in_array('aftersale/aftersale.php', $activePlugins);
    
    $pending_aftersale_count = 0;
    $today_aftersale_count = 0;
    
    if ($isAftersalePluginActive) {
        try {
            $sql = "SELECT COUNT(*) AS pending_count FROM `{$db_prefix}aftersale` WHERE status IN (0, 1)";
            $row = $db->once_fetch_array($sql);
            $pending_aftersale_count = $row['pending_count'] ?: 0;
            
            // 今日新增售后
            $sql = "SELECT COUNT(*) AS today_count FROM `{$db_prefix}aftersale` WHERE create_time >= {$today_start} AND create_time <= {$today_end}";
            $row = $db->once_fetch_array($sql);
            $today_aftersale_count = $row['today_count'] ?: 0;
        } catch (Exception $e) {
            $pending_aftersale_count = 0;
            $today_aftersale_count = 0;
        }
    }

    // 库存预警 - 直接查询商品表的stock字段
    try {
        // 库存不足（0 < stock < 10）的商品数量
        $sql = "SELECT COUNT(*) AS low_stock_count 
                FROM `{$db_prefix}goods` 
                WHERE delete_time IS NULL 
                AND is_on_shelf = 1 
                AND stock > 0 AND stock < 10";
        $row = $db->once_fetch_array($sql);
        $low_stock_count = $row['low_stock_count'] ?: 0;
        
        // 无库存（stock = 0）的商品数量
        $sql = "SELECT COUNT(*) AS no_stock_count 
                FROM `{$db_prefix}goods` 
                WHERE delete_time IS NULL 
                AND is_on_shelf = 1 
                AND stock = 0";
        $row = $db->once_fetch_array($sql);
        $no_stock_count = $row['no_stock_count'] ?: 0;
    } catch (Exception $e) {
        $low_stock_count = 0;
        $no_stock_count = 0;
    }
//    d($order_panel);die;

    // server info
    $server_app = $_SERVER['SERVER_SOFTWARE'];
    $DB = Database::getInstance();
    $mysql_ver = $DB->getMysqlVersion();
    $max_execution_time = ini_get('max_execution_time') ?: '';
    $max_upload_size = ini_get('upload_max_filesize') ?: '';
    $php_ver = PHP_VERSION . ', ' . $max_execution_time . 's,' . $max_upload_size;
    $os = php_uname('s') . ' ' . php_uname('m');

    if (extension_loaded('curl')) {
        $c = curl_version();
        $php_ver .= ',curl';
    }
    if (class_exists('ZipArchive', false)) {
        $php_ver .= ',zip';
    }
    if (extension_loaded('gd')) {
        $php_ver .= ',gd';
    }




    include View::getAdmView('header');
    require_once(View::getAdmView('templates/default/index/index'));
    include View::getAdmView('footer');
    View::output();





}

if($action == 'jujue_mianze_ajax'){
    if(Input::postStrVar('jujue')){
        Option::updateOption('mianze', 0);
        $CACHE->updateCache('options');
        output::ok();
    }
    output::error('非法请求！');
}

if($action == 'mianze_ajax'){
    if(Input::postStrVar('mianze')){
        Option::updateOption('mianze', time());
        $CACHE->updateCache('options');
        output::ok();
    }
    output::error('非法请求！');
}

if($action == 'mianze'){

    $chakan = Input::getIntVar('chakan');

    include View::getAdmView('open_head');
    require_once View::getAdmView('templates/default/index/mianze');
    include View::getAdmView('open_foot');
    View::output();
}

if ($action === 'add_shortcut') {
    $shortcut = Input::postStrArray('shortcut');
    $shortcutSet = [];
    foreach ($shortcut as $item) {
        $item = explode('||', $item);
        $shortcutSet[] = [
            'name' => $item[0],
            'url'  => $item[1]
        ];
    }
    Option::updateOption('shortcut', json_encode($shortcutSet, JSON_UNESCAPED_UNICODE));
    $CACHE->updateCache('options');
    emDirect("./index.php?add_shortcut_suc=1");
}

if($action == 'delete_cache'){
    $dir = DC_ROOT . "/content/cache";
    // 检查目录是否存在
    if (!is_dir($dir)) {
        output::error('缓存目录不存在');
    }

    // 打开目录
    $handle = opendir($dir);
    if (!$handle) {
        output::error('缓存目录无权限');
    }

    // 遍历目录
    while (false !== ($file = readdir($handle))) {
        // 跳过当前目录（.）和上级目录（..）
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        // 保留插件授权缓存文件，避免 API 不可用时影响付费插件
        if ($file === 'plugin_license.php') {
            continue;
        }

        $filePath = $dir . DIRECTORY_SEPARATOR . $file;

        // 如果是文件则删除
        if (is_file($filePath)) {
            unlink($filePath); // 删除文件
        }
        // 如果是子目录，可以根据需求决定是否递归删除（这里仅删除文件，不处理子目录）
        // else if (is_dir($filePath)) {
        //     // 递归删除子目录下的文件（可选）
        //     deleteDirFiles($filePath);
        // }
    }

    // 关闭目录句柄
    closedir($handle);
    output::ok();
}

/**
 * 单条线路延迟检测（前端并行调用）
 */
if ($action == 'ping_single_line') {
    $lineKey = isset($_GET['line']) ? (int)$_GET['line'] : -1;
    if (!isset(DC_LINE[$lineKey])) {
        Ret::error('无效线路');
    }
    $line = DC_LINE[$lineKey];
    $url = rtrim($line['value'], '/') . '/api/dcshop.php?action=ping';
    $timeout = 3;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . '&t=' . microtime(true));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'DCShop-PingCheck');

    $start = microtime(true);
    $resp = curl_exec($ch);
    $elapsed = round((microtime(true) - $start) * 1000);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($resp === false || $httpCode === 0) {
        Ret::success('ok', [$lineKey => ['ms' => -1, 'avg_ms' => -1, 'text' => '超时', 'status' => 'timeout']]);
    } else {
        Ret::success('ok', [$lineKey => ['ms' => $elapsed, 'avg_ms' => $elapsed, 'text' => $elapsed . 'ms', 'status' => 'ok']]);
    }
}

/**
 * 服务器端线路延迟检测（批量，保留兼容）
 */
if ($action == 'ping_lines') {
    $rounds = 3; // 每条线路请求3轮取累计
    $timeout = 5; // 单次超时秒数
    $results = [];

    foreach (DC_LINE as $key => $line) {
        $url = rtrim($line['value'], '/') . '/api/dcshop.php?action=ping';
        $totalMs = 0;
        $failed = false;

        for ($r = 0; $r < $rounds; $r++) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . '&t=' . microtime(true) . '_' . $r);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'DCShop-PingCheck');

            $start = microtime(true);
            $resp = curl_exec($ch);
            $elapsed = (microtime(true) - $start) * 1000; // 毫秒
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);

            if ($resp === false || $httpCode === 0) {
                $failed = true;
                break;
            }
            $totalMs += $elapsed;
        }

        if ($failed) {
            $results[$key] = ['ms' => -1, 'text' => '超时', 'status' => 'timeout'];
        } else {
            $avgMs = round($totalMs / $rounds);
            $results[$key] = [
                'ms'     => round($totalMs),
                'avg_ms' => $avgMs,
                'text'   => round($totalMs) . 'ms',
                'status' => 'ok',
            ];
        }
    }

    Ret::success('ok', $results);
}

/**
 * 更新线路
 */
if($action == 'update_line'){
    $line = Input::postIntVar('line');

    Option::updateOption('dc_line', $line);
    $CACHE->updateCache('options');
    // 切换线路后清除授权缓存，确保使用新线路重新验证
    Register::clearDemoCache();
    Ret::success('操作成功');
}

/**
 * 获取购买授权信息
 */
if($action == 'get_em_buy_info'){
    $default_token = '89Z78A9S7D8F9G7H8J9K8L7M9N8B7V8C9X8Z76T54R32E1WQ';
    $service_token = (defined('SERVICE_TOKEN') && SERVICE_TOKEN) ? SERVICE_TOKEN : $default_token;
    $data = [
        'service_token' => $service_token
    ];
    $res = emCurl(DC_LINE[CURRENT_LINE]['value'] . 'api/dcshop.php?action=get_em_buy_info', $data, true, [], 10);
    // var_dump($res);die;
    if(empty($res)){
        Ret::error('网络请求超时，请重试或更换其他线路');
    }
    $res = json_decode($res, true);
    $buyData = $res['data'] ?? [];
    
    // 如果当前域名的授权记录中代理设置了QQ或私域链接，则覆盖默认官方信息
    $authResult = Register::checkDomain();
    $isAgentInfo = false;
    if (!empty($authResult['agent_qq'])) {
        $buyData['service_qq'] = $authResult['agent_qq'];
        $isAgentInfo = true;
    }
    if (!empty($authResult['agent_link'])) {
        $buyData['buy_url'] = [['name' => '授权渠道', 'url' => $authResult['agent_link']]];
        $isAgentInfo = true;
    }
    $buyData['is_agent_info'] = $isAgentInfo;
    
    Ret::success('success', $buyData);
}

if($action == 'get_download_url'){
    $default_token = '89Z78A9S7D8F9G7H8J9K8L7M9N8B7V8C9X8Z76T54R32E1WQ';
    $service_token = (defined('SERVICE_TOKEN') && SERVICE_TOKEN) ? SERVICE_TOKEN : $default_token;
    $data = [
        'service_token' => $service_token
    ];
    $res = emCurl(DC_LINE[CURRENT_LINE]['value'] . 'api/dcshop.php?action=get_em_buy_info', $data, true, [], 10);
    if(empty($res)){
        Ret::error('网络请求超时，请重试或更换其他线路');
    }
    $res = json_decode($res, true);
    Ret::success('success', $res['data']);
}

