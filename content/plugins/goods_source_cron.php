<?php

$root = dirname(dirname(__DIR__));
if (!is_file($root . '/init.php')) {
    $root = dirname(dirname(dirname(__DIR__)));
}
require_once $root . '/init.php';

$centralCronEntry = $root . '/content/plugins/goods_source_cron.php';
if (realpath(__FILE__) !== realpath($centralCronEntry) && is_file($centralCronEntry)) {
    require $centralCronEntry;
    exit;
}

if (!headers_sent()) header('Content-Type: text/plain; charset=utf-8');
@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', '0');
@set_time_limit(0);

function goods_source_cron_storage() {
    return Storage::getInstance('goods_source_cron');
}

function goods_source_cron_key() {
    $storage = goods_source_cron_storage();
    $key = $storage->getValue('cron_key');
    if (empty($key)) {
        $key = md5((defined('AUTH_KEY') ? AUTH_KEY : 'dcshop') . 'goods_source_cron');
        $storage->setValue('cron_key', $key);
    }
    return $key;
}

function goods_source_cron_print($text = '') {
    echo $text . "\n";
    if (function_exists('ob_get_level')) {
        while (ob_get_level() > 0) {
            @ob_flush();
            @flush();
            break;
        }
    }
    @flush();
}

function goods_source_cron_output_lines($lines) {
    $lines = is_array($lines) ? $lines : [];
    foreach ($lines as $line) {
        goods_source_cron_print($line);
    }
}

function goods_source_cron_save_result($storage, $lines, $running = false) {
    $storage->setValue('last_result', implode("\n", (array)$lines));
    $storage->setValue('last_status', $running ? 'running' : 'finished');
    $storage->setValue('last_update', time());
}

$cronKey = isset($_GET['cron_key']) ? (string)$_GET['cron_key'] : '';
if ($cronKey !== goods_source_cron_key()) {
    echo '密钥错误';
    exit;
}

$storage = goods_source_cron_storage();
$now = time();
$lockUntil = (int)$storage->getValue('lock_until');
if ($lockUntil > $now) {
    $lastResult = trim((string)$storage->getValue('last_result'));
    $lastUpdate = (int)$storage->getValue('last_update');
    goods_source_cron_print('计划任务正在执行中，请稍后再试');
    goods_source_cron_print('预计剩余：' . max(1, $lockUntil - $now) . ' 秒');
    if ($lastUpdate > 0) goods_source_cron_print('最近状态更新：' . date('Y-m-d H:i:s', $lastUpdate));
    if ($lastResult !== '') {
        goods_source_cron_print('');
        goods_source_cron_print('=== 最近一次计划任务状态 ===');
        goods_source_cron_print($lastResult);
    }
    exit;
}
$storage->setValue('lock_until', $now + 300);
$cronLines = [];
$cronLines[] = '=== 第三方货源统一计划任务开始 ===';
$cronLines[] = '执行时间：' . date('Y-m-d H:i:s');
goods_source_cron_save_result($storage, $cronLines, true);
register_shutdown_function(function() use ($storage, &$cronLines) {
    $storage->setValue('lock_until', 0);
    if (!empty($cronLines)) goods_source_cron_save_result($storage, $cronLines, false);
});

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
$limit = max(1, min(30, $limit));
$orderLimit = isset($_GET['order_limit']) ? (int)$_GET['order_limit'] : 10;
$orderLimit = max(1, min(50, $orderLimit));
$options = ['limit' => $limit, 'order_limit' => $orderLimit];
$cronLines[] = "本次商品批量：{$limit} 个";
$cronLines[] = "本次订单轮询：{$orderLimit} 单";

$tasks = [
    'goods_docking' => ['name' => '同系统对接', 'function' => 'goods_docking_run_cron'],
    'goods_qingjiu' => ['name' => '晴玖对接', 'function' => 'goods_qingjiu_run_cron'],
    'goods_xiaoqing' => ['name' => '小氢云对接', 'function' => 'goods_xiaoqing_run_cron'],
    'goods_yiciyuan' => ['name' => '异次元对接', 'function' => 'goods_yiciyuan_run_cron'],
    'goods_mcy' => ['name' => '萌次元对接', 'function' => 'goods_mcy_run_cron'],
];

$source = isset($_GET['source']) ? trim((string)$_GET['source']) : '';
if ($source !== '') {
    $tasks = isset($tasks[$source]) ? [$source => $tasks[$source]] : [];
    $cronLines[] = '指定货源：' . $source;
}

$activePlugins = Option::get('active_plugins');
$activePlugins = is_array($activePlugins) ? $activePlugins : [];
goods_source_cron_output_lines($cronLines);
goods_source_cron_save_result($storage, $cronLines, true);

$runCount = 0;
foreach ($tasks as $plugin => $task) {
    $pluginFile = $plugin . '/' . $plugin . '.php';
    $function = $task['function'];
    $name = $task['name'];
    if (!in_array($pluginFile, $activePlugins, true)) {
        continue;
    }
    if (!function_exists($function)) {
        continue;
    }

    $runCount++;
    $start = microtime(true);
    $cronLines[] = '';
    $cronLines[] = "--- 开始执行 {$name} ---";
    goods_source_cron_print('');
    goods_source_cron_print("--- 开始执行 {$name} ---");
    goods_source_cron_save_result($storage, $cronLines, true);
    try {
        $result = call_user_func($function, $options);
        $result = trim((string)$result);
        if ($result !== '') {
            $resultLines = preg_split('/\r\n|\r|\n/', $result);
            foreach ($resultLines as $resultLine) {
                $cronLines[] = $resultLine;
                goods_source_cron_print($resultLine);
            }
        }
    } catch (Throwable $e) {
        $cronLines[] = '=== ' . $plugin . ' 计划任务执行失败 ===';
        $cronLines[] = '错误：' . $e->getMessage();
        goods_source_cron_print('=== ' . $plugin . ' 计划任务执行失败 ===');
        goods_source_cron_print('错误：' . $e->getMessage());
        error_log('[goods_source_cron] ' . $plugin . ': ' . $e->getMessage());
    }
    $finishLine = "--- {$name} 执行结束，用时 " . round(microtime(true) - $start, 2) . " 秒 ---";
    $cronLines[] = $finishLine;
    goods_source_cron_print($finishLine);
    goods_source_cron_save_result($storage, $cronLines, true);
}

if ($runCount === 0) {
    $cronLines[] = '';
    $cronLines[] = '没有可执行的第三方货源任务';
    goods_source_cron_print('');
    goods_source_cron_print('没有可执行的第三方货源任务');
}

$cronLines[] = '';
$cronLines[] = '=== 第三方货源统一计划任务结束 ===';
goods_source_cron_print('');
goods_source_cron_print('=== 第三方货源统一计划任务结束 ===');
goods_source_cron_save_result($storage, $cronLines, false);