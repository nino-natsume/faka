<?php
defined('DC_ROOT') || exit('access denied!');

$action = Input::getStrVar('action');

$db = Database::getInstance();
$db_prefix = DB_PREFIX;

// 支付回调捕获（公开接口，无需登录）
if($action == 'pay_callback_capture'){
    $db->query("CREATE TABLE IF NOT EXISTS `{$db_prefix}pay_callback_log` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `gateway` varchar(64) NOT NULL DEFAULT '' COMMENT '支付网关',
      `order_no` varchar(64) NOT NULL DEFAULT '' COMMENT '订单号',
      `amount` varchar(32) NOT NULL DEFAULT '' COMMENT '金额',
      `status` varchar(32) NOT NULL DEFAULT '' COMMENT '回调状态',
      `raw_data` text COMMENT '原始回调数据',
      `ip` varchar(64) NOT NULL DEFAULT '' COMMENT '来源IP',
      `create_time` int(11) NOT NULL DEFAULT 0 COMMENT '接收时间',
      PRIMARY KEY (`id`),
      KEY `idx_order_no` (`order_no`),
      KEY `idx_create_time` (`create_time`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='支付回调日志'");
    $gateway  = Input::getStrVar('gateway') ?: 'unknown';
    $raw_post = (string)@file_get_contents('php://input');
    $all_data = array_merge($_GET, $_POST);
    unset($all_data['plugin'], $all_data['action'], $all_data['gateway']);
    $order_no = (string)($all_data['out_trade_no'] ?? $all_data['trade_no'] ?? $all_data['order_no'] ?? '');
    $amount   = (string)($all_data['total_fee'] ?? $all_data['total_amount'] ?? $all_data['money'] ?? '');
    $status   = (string)($all_data['trade_state'] ?? $all_data['trade_status'] ?? $all_data['result_code'] ?? $all_data['return_code'] ?? '');
    $payload  = $raw_post !== '' ? $raw_post : json_encode($all_data, JSON_UNESCAPED_UNICODE);
    $ip       = (string)($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '');
    @$db->query("INSERT INTO `{$db_prefix}pay_callback_log` (gateway, order_no, amount, status, raw_data, ip, create_time) VALUES (
        '" . $db->escape_string(substr($gateway, 0, 64)) . "',
        '" . $db->escape_string(substr($order_no, 0, 64)) . "',
        '" . $db->escape_string(substr($amount, 0, 32)) . "',
        '" . $db->escape_string(substr($status, 0, 32)) . "',
        '" . $db->escape_string($payload) . "',
        '" . $db->escape_string(substr($ip, 0, 64)) . "',
        " . time() . "
    )");
    echo 'success';
    exit;
}

if(!User::isAdmin()) exit('access denied!');

// 获取系统统计数据
if($action == 'get_stats'){
    $goods_count   = $db->once_fetch_array("SELECT COUNT(*) as cnt FROM {$db_prefix}goods WHERE delete_time IS NULL");
    $order_count   = $db->once_fetch_array("SELECT COUNT(*) as cnt FROM {$db_prefix}order");
    $user_count    = $db->once_fetch_array("SELECT COUNT(*) as cnt FROM {$db_prefix}user");
    $kami_count    = $db->once_fetch_array("SELECT COUNT(*) as cnt FROM {$db_prefix}goods_once");
    $today_start   = strtotime(date('Y-m-d'));
    $today_orders  = $db->once_fetch_array("SELECT COUNT(*) as cnt FROM {$db_prefix}order WHERE create_time >= {$today_start}");
    $pending_orders= $db->once_fetch_array("SELECT COUNT(*) as cnt FROM {$db_prefix}order WHERE status = 0 AND pay_time IS NULL");

    Output::ok([
        'goods'          => $goods_count['cnt'] ?? 0,
        'orders'         => $order_count['cnt'] ?? 0,
        'users'          => $user_count['cnt'] ?? 0,
        'kami'           => $kami_count['cnt'] ?? 0,
        'today_orders'   => $today_orders['cnt'] ?? 0,
        'pending_orders' => $pending_orders['cnt'] ?? 0,
    ]);
}

// 演示站检查 - 拦截写操作
$_repair_write_actions = ['repair_goods_stock_ajax', 'repair_all_stock_ajax', 'repair_order_status_ajax', 'repair_user_stats_ajax', 'clear_cache_ajax', 'clean_expired_orders_ajax', 'optimize_db_ajax', 'clean_orphan_kami_ajax', 'orphan_order_clean_ajax', 'fix_negative_stock_ajax', 'pay_callback_delete_ajax', 'pay_callback_clear_ajax'];
if (in_array($action, $_repair_write_actions) && Register::isDemoSite()) {
    Output::error('演示站暂不支持此修复操作');
}

// 单个商品库存修复
if($action == 'repair_goods_stock_ajax'){
    $goods_id = Input::postIntVar('goods_id');
    if(empty($goods_id)){
        Output::error('请选择要修复库存的商品');
    }
    
    $goods = $db->once_fetch_array("SELECT * FROM {$db_prefix}goods WHERE id = {$goods_id}");
    if(empty($goods)){
        Output::error('商品不存在');
    }
    
    if($goods['type'] == 'once'){
        repairOnceGoodsStock($db, $db_prefix, $goods_id, $goods['is_sku']);
        Output::ok();
    } else {
        Output::error('该商品类型暂不支持自动修复库存');
    }
}

// 批量修复所有卡密商品库存
if($action == 'repair_all_stock_ajax'){
    $goods_list = $db->fetch_all("SELECT id, is_sku FROM {$db_prefix}goods WHERE type = 'once' AND delete_time IS NULL");
    $count = 0;
    
    foreach($goods_list as $goods){
        repairOnceGoodsStock($db, $db_prefix, $goods['id'], $goods['is_sku']);
        $count++;
    }
    
    Output::ok(['count' => $count]);
}

// 修复订单状态（清理过期未支付订单）
if($action == 'repair_order_status_ajax'){
    $expire_time = time() - 86400; // 24小时前
    
    // 获取过期未支付订单
    $expired_orders = $db->fetch_all("SELECT id FROM {$db_prefix}order WHERE status = 0 AND create_time < {$expire_time}");
    $cleaned = count($expired_orders);
    
    if($cleaned > 0){
        // 删除订单相关数据
        $order_ids = array_column($expired_orders, 'id');
        $ids_str = implode(',', $order_ids);
        
        $db->query("DELETE FROM {$db_prefix}order_list WHERE order_id IN ({$ids_str})");
        $db->query("DELETE FROM {$db_prefix}order_required WHERE order_id IN ({$ids_str})");
        $db->query("DELETE FROM {$db_prefix}order WHERE id IN ({$ids_str})");
    }
    
    Output::ok(['cleaned' => $cleaned]);
}

// 修复用户统计数据
if($action == 'repair_user_stats_ajax'){
    $users = $db->fetch_all("SELECT uid FROM {$db_prefix}user");
    $count = 0;
    
    foreach($users as $user){
        $uid = $user['uid'];
        
        // 计算用户消费总额（amount单位为分，expend单位为元）
        $total = $db->once_fetch_array("SELECT COALESCE(SUM(amount), 0) as total FROM {$db_prefix}order WHERE user_id = {$uid} AND status = 1");
        $total_amount = round(($total['total'] ?? 0) / 100, 2);
        
        // 更新用户消费总额
        $db->query("UPDATE {$db_prefix}user SET expend = {$total_amount} WHERE uid = {$uid}");
        
        $count++;
    }
    
    Output::ok(['count' => $count]);
}

// 清理系统缓存
if($action == 'clear_cache_ajax'){
    $cache = Cache::getInstance();
    
    // 清理缓存
    $cache_files = glob(DC_ROOT . '/content/cache/*.php');
    foreach($cache_files as $file){
        @unlink($file);
    }
    
    // 重建缓存
    $cache->updateCache();
    
    Output::ok();
}

// 清理过期订单
if($action == 'clean_expired_orders_ajax'){
    $days = Input::postIntVar('days') ?: 7;
    $expire_time = time() - ($days * 86400);
    
    // 获取过期未支付订单
    $expired_orders = $db->fetch_all("SELECT id FROM {$db_prefix}order WHERE status = 0 AND create_time < {$expire_time}");
    $count = count($expired_orders);
    
    if($count > 0){
        $order_ids = array_column($expired_orders, 'id');
        $ids_str = implode(',', $order_ids);
        
        $db->query("DELETE FROM {$db_prefix}order_list WHERE order_id IN ({$ids_str})");
        $db->query("DELETE FROM {$db_prefix}order_required WHERE order_id IN ({$ids_str})");
        $db->query("DELETE FROM {$db_prefix}order WHERE id IN ({$ids_str})");
    }
    
    Output::ok(['count' => $count]);
}

// 数据完整性检查
if($action == 'check_integrity_ajax'){
    $results = [];
    
    // 检查商品表
    $goods_check = $db->once_fetch_array("SELECT COUNT(*) as cnt FROM {$db_prefix}goods WHERE delete_time IS NULL");
    $results[] = [
        'name' => '商品数据',
        'status' => 'ok',
        'message' => "共 {$goods_check['cnt']} 个商品"
    ];
    
    // 检查孤立的订单详情
    $orphan_list = $db->once_fetch_array("SELECT COUNT(*) as cnt FROM {$db_prefix}order_list ol LEFT JOIN {$db_prefix}order o ON ol.order_id = o.id WHERE o.id IS NULL");
    if($orphan_list['cnt'] > 0){
        $results[] = [
            'name' => '订单详情',
            'status' => 'error',
            'message' => "发现 {$orphan_list['cnt']} 条孤立数据"
        ];
    } else {
        $results[] = [
            'name' => '订单详情',
            'status' => 'ok',
            'message' => '数据完整'
        ];
    }
    
    // 检查库存异常的商品
    $stock_issue = $db->once_fetch_array("SELECT COUNT(*) as cnt FROM {$db_prefix}goods WHERE stock < 0 AND delete_time IS NULL");
    if($stock_issue['cnt'] > 0){
        $results[] = [
            'name' => '商品库存',
            'status' => 'error',
            'message' => "发现 {$stock_issue['cnt']} 个负库存商品"
        ];
    } else {
        $results[] = [
            'name' => '商品库存',
            'status' => 'ok',
            'message' => '库存正常'
        ];
    }
    
    // 检查未关联商品的卡密
    $orphan_kami = $db->once_fetch_array("SELECT COUNT(*) as cnt FROM {$db_prefix}goods_once go LEFT JOIN {$db_prefix}goods g ON go.goods_id = g.id WHERE g.id IS NULL");
    if($orphan_kami['cnt'] > 0){
        $results[] = [
            'name' => '卡密数据',
            'status' => 'error',
            'message' => "发现 {$orphan_kami['cnt']} 条孤立卡密"
        ];
    } else {
        $results[] = [
            'name' => '卡密数据',
            'status' => 'ok',
            'message' => '数据完整'
        ];
    }
    
    // 检查缓存目录
    $cache_dir = DC_ROOT . '/content/cache/';
    if(is_writable($cache_dir)){
        $results[] = [
            'name' => '缓存目录',
            'status' => 'ok',
            'message' => '可写入'
        ];
    } else {
        $results[] = [
            'name' => '缓存目录',
            'status' => 'error',
            'message' => '不可写入'
        ];
    }
    
    Output::ok($results);
}

// 系统环境自检（只读）
if($action == 'env_check_ajax'){
    $results = [];

    // PHP 版本
    $results[] = [
        'name' => 'PHP 版本',
        'status' => 'ok',
        'message' => PHP_VERSION,
    ];

    // 关键扩展
    $exts = [
        'curl' => function_exists('curl_init'),
        'zip' => class_exists('ZipArchive'),
        'zlib' => function_exists('gzdeflate'),
        'mbstring' => extension_loaded('mbstring'),
    ];
    foreach($exts as $name => $ok){
        $results[] = [
            'name' => '扩展：' . $name,
            'status' => $ok ? 'ok' : 'error',
            'message' => $ok ? '已启用' : '未启用',
        ];
    }

    // 目录权限
    $paths = [
        '缓存目录' => DC_ROOT . '/content/cache/',
        '上传目录' => DC_ROOT . '/content/uploadfile/',
    ];

    foreach($paths as $label => $path){
        if(!is_dir($path)){
            $results[] = [
                'name' => $label,
                'status' => 'error',
                'message' => '目录不存在：' . $path,
            ];
            continue;
        }
        $writable = is_writable($path);
        $results[] = [
            'name' => $label,
            'status' => $writable ? 'ok' : 'error',
            'message' => $writable ? '可写入' : '不可写入',
        ];
    }

    // 核心配置文件
    $coreFiles = [
        'config.php' => DC_ROOT . '/config.php',
        'base.php' => DC_ROOT . '/base.php',
    ];
    foreach($coreFiles as $name => $path){
        $results[] = [
            'name' => '核心文件：' . $name,
            'status' => file_exists($path) ? 'ok' : 'error',
            'message' => file_exists($path) ? '存在' : '不存在',
        ];
    }

    Output::ok($results);
}

// 定时任务健康检查（只读）
if($action == 'cron_health_ajax'){
    $results = [];

    // 核心提示：站点 cron 的典型方式是直接访问插件入口（如 auto_clean_order?action=cron&cron_key=xxx）
    $storage = Storage::getInstance('auto_clean_order');
    $enabled = $storage->getValue('enabled') ?: '0';
    $auto_clean = $storage->getValue('auto_clean') ?: '0';
    $cron_key = (string)($storage->getValue('cron_key') ?: '');
    $last_clean = (int)($storage->getValue('last_clean_time') ?: 0);
    $interval = (int)($storage->getValue('clean_interval') ?: 24);

    $cronUrl = DC_URL . 'content/plugins/auto_clean_order/auto_clean_order.php?action=cron&cron_key=';
    $cronUrlShown = $cronUrl . ($cron_key ? $cron_key : '');

    $results[] = [
        'name' => '自动清理订单插件',
        'status' => ($enabled === '1') ? 'ok' : 'error',
        'message' => ($enabled === '1') ? '已启用' : '未启用（cron 不会执行）',
    ];

    $results[] = [
        'name' => '访问触发模式（auto_clean）',
        'status' => ($auto_clean === '1') ? 'ok' : 'error',
        'message' => ($auto_clean === '1') ? '已开启' : '未开启（仅 cron 方式可执行）',
    ];

    $results[] = [
        'name' => 'cron_key 配置',
        'status' => ($cron_key !== '') ? 'ok' : 'error',
        'message' => ($cron_key !== '') ? '已设置' : '未设置（cron 入口会拒绝执行）',
    ];

    $results[] = [
        'name' => '建议 cron 访问链接',
        'status' => 'ok',
        'message' => $cronUrlShown,
    ];

    $lastCleanMsg = $last_clean > 0 ? date('Y-m-d H:i:s', $last_clean) : '从未执行';
    $results[] = [
        'name' => '上次清理时间',
        'status' => ($last_clean > 0) ? 'ok' : 'error',
        'message' => $lastCleanMsg,
    ];

    $results[] = [
        'name' => '清理间隔（小时）',
        'status' => 'ok',
        'message' => (string)$interval,
    ];

    Output::ok($results);
}

// 孤立订单数据扫描（order_list / order_required 中无主数据）
if($action == 'orphan_order_report_ajax'){
    $orphan_order_list = $db->once_fetch_array("SELECT COUNT(*) as cnt FROM {$db_prefix}order_list ol LEFT JOIN {$db_prefix}order o ON ol.order_id = o.id WHERE o.id IS NULL");
    $orphan_order_required = $db->once_fetch_array("SELECT COUNT(*) as cnt FROM {$db_prefix}order_required orq LEFT JOIN {$db_prefix}order o ON orq.order_id = o.id WHERE o.id IS NULL");

    $cnt_list = intval($orphan_order_list['cnt'] ?? 0);
    $cnt_req = intval($orphan_order_required['cnt'] ?? 0);

    Output::ok([
        'orphan_order_list' => $cnt_list,
        'orphan_order_required' => $cnt_req,
        'total' => $cnt_list + $cnt_req,
    ]);
}

// 清理孤立订单数据（无主 order_list / order_required）
if($action == 'orphan_order_clean_ajax'){
    $before_list = $db->once_fetch_array("SELECT COUNT(*) as cnt FROM {$db_prefix}order_list ol LEFT JOIN {$db_prefix}order o ON ol.order_id = o.id WHERE o.id IS NULL");
    $before_req = $db->once_fetch_array("SELECT COUNT(*) as cnt FROM {$db_prefix}order_required orq LEFT JOIN {$db_prefix}order o ON orq.order_id = o.id WHERE o.id IS NULL");

    $cnt_list = intval($before_list['cnt'] ?? 0);
    $cnt_req = intval($before_req['cnt'] ?? 0);

    if(($cnt_list + $cnt_req) > 0){
        $db->query("DELETE ol FROM {$db_prefix}order_list ol LEFT JOIN {$db_prefix}order o ON ol.order_id = o.id WHERE o.id IS NULL");
        $db->query("DELETE orq FROM {$db_prefix}order_required orq LEFT JOIN {$db_prefix}order o ON orq.order_id = o.id WHERE o.id IS NULL");
    }

    Output::ok(['deleted' => $cnt_list + $cnt_req]);
}

// 负库存修复（仅修复卡密类商品 once）
if($action == 'fix_negative_stock_ajax'){
    $goods_list = $db->fetch_all("SELECT id, is_sku FROM {$db_prefix}goods WHERE stock < 0 AND type = 'once' AND delete_time IS NULL");
    $count = 0;

    foreach($goods_list as $goods){
        $gid = intval($goods['id'] ?? 0);
        if($gid <= 0) continue;
        $is_sku = $goods['is_sku'] ?? 'n';
        repairOnceGoodsStock($db, $db_prefix, $gid, $is_sku);
        $count++;
    }

    Output::ok(['count' => $count]);
}

// 获取系统资源指标
if($action == 'get_sys_metrics'){
    $metrics = [];
    // 磁盘
    $disk_total = @disk_total_space(DC_ROOT);
    $disk_free  = @disk_free_space(DC_ROOT);
    if($disk_total > 0){
        $metrics['disk_used_pct'] = round(($disk_total - $disk_free) / $disk_total * 100, 1);
        $metrics['disk_free']     = _sysFormatBytes($disk_free);
        $metrics['disk_total']    = _sysFormatBytes($disk_total);
    } else {
        $metrics['disk_used_pct'] = null;
        $metrics['disk_free']     = 'N/A';
        $metrics['disk_total']    = 'N/A';
    }
    // 内存（Linux /proc/meminfo）
    if(is_readable('/proc/meminfo')){
        $mi = (string)@file_get_contents('/proc/meminfo');
        preg_match('/MemTotal:\s+(\d+)/i', $mi, $mt);
        preg_match('/MemAvailable:\s+(\d+)/i', $mi, $ma);
        $mem_total = isset($mt[1]) ? intval($mt[1]) * 1024 : 0;
        $mem_avail = isset($ma[1]) ? intval($ma[1]) * 1024 : 0;
        if($mem_total > 0){
            $metrics['mem_used_pct'] = round(($mem_total - $mem_avail) / $mem_total * 100, 1);
            $metrics['mem_total']    = _sysFormatBytes($mem_total);
        } else {
            $metrics['mem_used_pct'] = null;
            $metrics['mem_total']    = 'N/A';
        }
    } else {
        $metrics['mem_used_pct'] = null;
        $metrics['mem_total']    = 'N/A';
    }
    // CPU 负载均值
    if(function_exists('sys_getloadavg')){
        $load = sys_getloadavg();
        $metrics['cpu_load'] = round($load[0], 2);
    } else {
        $metrics['cpu_load'] = null;
    }
    Output::ok($metrics);
}

// 支付回调日志列表
if($action == 'pay_callback_list_ajax'){
    $db->query("CREATE TABLE IF NOT EXISTS `{$db_prefix}pay_callback_log` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `gateway` varchar(64) NOT NULL DEFAULT '' COMMENT '支付网关',
      `order_no` varchar(64) NOT NULL DEFAULT '' COMMENT '订单号',
      `amount` varchar(32) NOT NULL DEFAULT '' COMMENT '金额',
      `status` varchar(32) NOT NULL DEFAULT '' COMMENT '回调状态',
      `raw_data` text COMMENT '原始回调数据',
      `ip` varchar(64) NOT NULL DEFAULT '' COMMENT '来源IP',
      `create_time` int(11) NOT NULL DEFAULT 0 COMMENT '接收时间',
      PRIMARY KEY (`id`),
      KEY `idx_order_no` (`order_no`),
      KEY `idx_create_time` (`create_time`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='支付回调日志'");
    $page    = max(1, intval(Input::getStrVar('page') ?: 1));
    $limit   = 20;
    $offset  = ($page - 1) * $limit;
    $gateway = Input::getStrVar('gateway');
    $where   = $gateway ? "WHERE gateway = '" . $db->escape_string($gateway) . "'" : '';
    $total_row = $db->once_fetch_array("SELECT COUNT(*) as cnt FROM `{$db_prefix}pay_callback_log` {$where}");
    $list      = $db->fetch_all("SELECT id, gateway, order_no, amount, status, ip, create_time FROM `{$db_prefix}pay_callback_log` {$where} ORDER BY id DESC LIMIT {$offset}, {$limit}");
    foreach($list as &$row) $row['time_str'] = date('Y-m-d H:i:s', $row['create_time']);
    unset($row);
    Output::ok(['list' => $list, 'total' => intval($total_row['cnt'] ?? 0), 'page' => $page, 'limit' => $limit]);
}

// 支付回调日志详情
if($action == 'pay_callback_detail_ajax'){
    $id  = intval(Input::getStrVar('id'));
    $row = $db->once_fetch_array("SELECT * FROM `{$db_prefix}pay_callback_log` WHERE id = {$id}");
    if(!$row) Output::error('记录不存在');
    $row['time_str'] = date('Y-m-d H:i:s', $row['create_time']);
    Output::ok($row);
}

// 支付回调日志删除
if($action == 'pay_callback_delete_ajax'){
    $id = Input::postIntVar('id');
    $db->query("DELETE FROM `{$db_prefix}pay_callback_log` WHERE id = {$id}");
    Output::ok();
}

// 支付回调日志清空
if($action == 'pay_callback_clear_ajax'){
    $db->query("TRUNCATE TABLE `{$db_prefix}pay_callback_log`");
    Output::ok();
}

// 清理孤立卡密（关联商品已被删除的卡密）
if($action == 'clean_orphan_kami_ajax'){
    // 查找并删除关联商品不存在的卡密
    $orphan_count = $db->once_fetch_array("SELECT COUNT(*) as cnt FROM {$db_prefix}goods_once go LEFT JOIN {$db_prefix}goods g ON go.goods_id = g.id WHERE g.id IS NULL");
    $count = intval($orphan_count['cnt'] ?? 0);
    
    if($count > 0){
        $db->query("DELETE go FROM {$db_prefix}goods_once go LEFT JOIN {$db_prefix}goods g ON go.goods_id = g.id WHERE g.id IS NULL");
    }
    
    Output::ok(['count' => $count]);
}

// 优化数据库
if($action == 'optimize_db_ajax'){
    $tables = $db->fetch_all("SHOW TABLES");
    
    foreach($tables as $table){
        $table_name = array_values($table)[0];
        $db->query("OPTIMIZE TABLE `{$table_name}`");
    }
    
    Output::ok();
}

// 字节格式化辅助函数
function _sysFormatBytes($bytes){
    if($bytes >= 1073741824) return round($bytes / 1073741824, 1) . ' GB';
    if($bytes >= 1048576)    return round($bytes / 1048576, 1) . ' MB';
    return round($bytes / 1024, 1) . ' KB';
}

// 修复卡密商品库存的函数
function repairOnceGoodsStock($db, $db_prefix, $goods_id, $is_sku){
    if($is_sku == 'n'){
        // 无规格商品
        $res = $db->once_fetch_array("SELECT COUNT(id) as kami_count FROM {$db_prefix}goods_once WHERE goods_id={$goods_id} AND sku='0' AND sale_time IS NULL");
        $kami_count = $res['kami_count'] ?? 0;
        
        $db->query("DELETE FROM {$db_prefix}skus WHERE sku != '0' AND goods_id = {$goods_id}");
        $db->query("UPDATE {$db_prefix}skus SET stock = {$kami_count} WHERE goods_id = {$goods_id} AND sku = '0'");
        $db->query("UPDATE {$db_prefix}goods SET stock = {$kami_count} WHERE id = {$goods_id}");
    } else {
        // 有规格商品
        $db->query("DELETE FROM {$db_prefix}skus WHERE sku = '0' AND goods_id = {$goods_id}");
        $skus = $db->fetch_all("SELECT * FROM {$db_prefix}skus WHERE goods_id={$goods_id}");
        $total = 0;
        
        foreach($skus as $val){
            $res = $db->once_fetch_array("SELECT COUNT(id) as kami_count FROM {$db_prefix}goods_once WHERE goods_id={$goods_id} AND sku='{$val['sku']}' AND sale_time IS NULL");
            $kami_count = $res['kami_count'] ?? 0;
            $db->query("UPDATE {$db_prefix}skus SET stock = {$kami_count} WHERE goods_id = {$goods_id} AND sku = '{$val['sku']}'");
            $total += $kami_count;
        }
        
        $db->query("UPDATE {$db_prefix}goods SET stock = {$total} WHERE id = {$goods_id}");
    }
}

// 单个商品库存修复页面
if($action == 'repair_goods_stock'):
    // 只查询卡密类型的商品
    $goods = $db->fetch_all("SELECT id, title FROM {$db_prefix}goods WHERE type = 'once' AND delete_time IS NULL ORDER BY id DESC");
?>
<!doctype html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script>
        // 最早执行：检测深色模式并立即设置背景
        (function(){
            var isDark = false;
            try {
                isDark = parent.document.documentElement.getAttribute('data-theme') === 'dark' ||
                         parent.document.body.classList.contains('dark-mode');
            } catch(e){}
            if(isDark) {
                document.documentElement.setAttribute('data-theme', 'dark');
                // 立即写入样式，不等CSS加载
                document.write('<style>html,body{background:#1a1a1a!important}</style>');
            }
        })();
    </script>
    <link rel="stylesheet" href="/admin/views/layui-v2.11.6/layui/css/layui.css">
    <link rel="stylesheet" href="/admin/views/assets/remixicon/remixicon.css">
    <script src="/admin/views/layui-v2.11.6/layui/layui.js"></script>
    <script src="/admin/views/js/jquery.min.3.5.1.js"></script>
    <style>
        /* 基础样式 */
        body { background: #f5f5f5; min-height: 280px; }
        .repair-form { background: #fff; border-radius: 8px; margin: 20px; padding: 25px; }
        .layui-form-label { width: 100px; color: #333; }
        .layui-input-block { margin-left: 130px; }
        .repair-tip { 
            color: #999; 
            font-size: 12px; 
            margin-top: 8px; 
            padding-left: 130px;
        }
        .repair-tip i { color: #16baaa; margin-right: 4px; }

        /* 深色模式 */
        html[data-theme="dark"] body { background: #1a1a1a !important; }
        html[data-theme="dark"] .repair-form { background: #2a2a2a !important; }
        html[data-theme="dark"] .layui-form-label { color: #e0e0e0 !important; }
        html[data-theme="dark"] .layui-input,
        html[data-theme="dark"] .layui-select { background: #333 !important; border-color: #444 !important; color: #e0e0e0 !important; }
        html[data-theme="dark"] .layui-form-select .layui-input { background: #333 !important; border-color: #444 !important; color: #e0e0e0 !important; }
        html[data-theme="dark"] .layui-form-select dl { background: #333 !important; border-color: #444 !important; }
        html[data-theme="dark"] .layui-form-select dl dd { color: #e0e0e0 !important; }
        html[data-theme="dark"] .layui-form-select dl dd.layui-this { background: #16baaa !important; }
        html[data-theme="dark"] .layui-form-select dl dd:hover { background: #3a3a3a !important; }
        html[data-theme="dark"] .repair-tip { color: #888 !important; }
    </style>
</head>
<body>
    <div class="repair-form">
        <form class="layui-form" action="/?plugin=repair&action=repair_goods_stock_ajax" id="form">
            <div class="layui-form-item">
                <label class="layui-form-label">选择商品</label>
                <div class="layui-input-block">
                    <select name="goods_id" lay-search="">
                        <option value="">请选择要修复的商品</option>
                        <?php foreach($goods as $val): ?>
                        <option value="<?= $val['id'] ?>">[ID:<?= $val['id'] ?>] <?= htmlspecialchars($val['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="repair-tip">
                    <i class="ri-information-line"></i> 仅显示卡密类型商品，共 <?= count($goods) ?> 个
                </div>
            </div>
            <?php if(count($goods) > 0): ?>
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button type="submit" class="layui-btn" lay-submit lay-filter="submit">
                        <i class="ri-refresh-line"></i> 立即修复
                    </button>
                    <button type="reset" class="layui-btn layui-btn-primary">
                        <i class="ri-restart-line"></i> 重置
                    </button>
                </div>
            </div>
            <?php else: ?>
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <div style="color: #999; padding: 10px 0;">
                        <i class="ri-information-line"></i> 暂无卡密类型商品需要修复
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <script>
    layui.use(['form'], function(){
        var $ = layui.$;
        var form = layui.form;
        
        form.on('submit(submit)', function(data){
            var field = data.field;
            var url = $('#form').attr('action');
            var loadIdx = layer.load(2);
            
            $.ajax({
                type: "POST",
                url: url,
                data: field,
                dataType: "json",
                success: function(e){
                    layer.close(loadIdx);
                    if(e.code == 0){
                        parent.layer.close('repair');
                        parent.layer.msg('库存修复成功', {icon: 1});
                    } else {
                        layer.msg(e.msg, {icon: 2});
                    }
                },
                error: function(xhr){
                    layer.close(loadIdx);
                    var msg = '请求失败';
                    try { msg = JSON.parse(xhr.responseText).msg; } catch(e){}
                    layer.msg(msg, {icon: 2});
                }
            });
            return false;
        });
    });
    </script>
</body>
</html>
<?php endif; ?>
