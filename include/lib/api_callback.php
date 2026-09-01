<?php
/**
 * API Webhook Callback Handler
 */

if (!defined('DC_ROOT')) exit;

/**
 * Ensure the notify_url column exists in the order table
 */
function ensure_notify_url_column() {
    static $done = false;
    if ($done) return;
    $done = true;

    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $columns = $db->query("SHOW COLUMNS FROM `{$db_prefix}order` LIKE 'notify_url'");
    if (!$columns || $db->num_rows($columns) == 0) {
        $db->query("ALTER TABLE `{$db_prefix}order` ADD COLUMN `notify_url` varchar(255) DEFAULT ''");
    }
}

/**
 * Trigger secure Webhook notification
 */
function api_trigger_webhook($order_id, $status, $deliver_content = null) {
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;

    // Ensure notify_url column exists (auto-creates if missing on older databases)
    ensure_notify_url_column();

    $order = $db->once_fetch_array("SELECT id, out_trade_no, up_no, user_id, notify_url FROM {$db_prefix}order WHERE id = " . (int)$order_id . " LIMIT 1");
    if (empty($order) || empty($order['notify_url'])) {
        return;
    }

    // Retrieve order's API user
    $user = $db->once_fetch_array("SELECT api_key FROM {$db_prefix}user WHERE uid = " . (int)$order['user_id'] . " LIMIT 1");
    $api_key = $user ? $user['api_key'] : '';
    if (empty($api_key)) {
        return;
    }

    // Determine deliver content if not provided
    if ($deliver_content === null) {
        $child = $db->once_fetch_array("SELECT * FROM {$db_prefix}order_list WHERE order_id = {$order['id']} LIMIT 1");
        if ($child) {
            $goods = $db->once_fetch_array("SELECT id, type FROM {$db_prefix}goods WHERE id = {$child['goods_id']}");
            $dockingTable = $db_prefix . 'docking_goods';
            $hasDockingTable = $db->once_fetch_array("SHOW TABLES LIKE '" . $db->escape_string($dockingTable) . "'");
            $is_docking = $hasDockingTable ? $db->once_fetch_array("SELECT id FROM `{$dockingTable}` WHERE goods_id = {$child['goods_id']} LIMIT 1") : false;
            $goodsType = (!empty($is_docking)) ? 'docking' : ($goods['type'] ?? 'general');
            
            $cardList = [];
            $func = "plugin_goods_{$goodsType}_get_order_serect";
            if (function_exists($func)) {
                $serect = $func($db, $db_prefix, $goods, $order, $child, 0);
                if (!empty($serect['list'])) {
                    foreach ($serect['list'] as $kItem) {
                        $cardList[] = $kItem['content'];
                    }
                }
            }
            if (empty($cardList)) {
                $delivers = $db->fetch_all("SELECT content FROM {$db_prefix}deliver WHERE order_list_id = {$child['id']}");
                foreach ($delivers as $d) {
                    $cardList[] = $d['content'];
                }
            }
            $deliver_content = implode("\n", $cardList);
        } else {
            $deliver_content = '';
        }
    }

    $params = [
        'order_id'     => $order['out_trade_no'],
        'out_trade_no' => $order['up_no'],
        'status'       => (int)$status,
        'content'      => $deliver_content,
        'timestamp'    => time()
    ];

    // Compute signature: sort keys alphabetically, build query string, append api_key
    ksort($params);
    $signStr = '';
    foreach ($params as $k => $v) {
        if ($v !== '' && $v !== null) {
            $signStr .= $k . '=' . trim($v) . '&';
        }
    }
    $signStr .= 'key=' . $api_key;
    $params['sign'] = md5($signStr);

    // Send cURL POST callback
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $order['notify_url']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Log the callback status
    $logMsg = date('[Y-m-d H:i:s]') . " [API Webhook] Order ID: {$order['out_trade_no']} | Target: {$order['notify_url']} | HTTP Code: {$httpCode}";
    if ($curlError) {
        $logMsg .= " | Error: " . $curlError;
    } else {
        $logMsg .= " | Response: " . mb_substr(trim($response), 0, 100);
    }
    error_log($logMsg);
    @file_put_contents(DC_ROOT . '/content/cache/webhook_debug.log', $logMsg . "\n", FILE_APPEND);
}

// Hook registrations
addAction('deliver_complete', 'api_webhook_deliver_hook', 10, 1);
function api_webhook_deliver_hook($plugin_data) {
    $order = $plugin_data['order'] ?? null;
    if ($order && isset($order['id'])) {
        api_trigger_webhook((int)$order['id'], 2, $plugin_data['deliver_content'] ?? null);
    }
}

addAction('hand_deliver_complete', 'api_webhook_hand_deliver_hook', 10, 2);
function api_webhook_hand_deliver_hook($order_id, $remark) {
    api_trigger_webhook((int)$order_id, 2);
}

addAction('aftersale_status_change', 'api_webhook_aftersale_hook', 10, 2);
function api_webhook_aftersale_hook($aftersale_id, $data) {
    if (isset($data['new_status']) && (int)$data['new_status'] === 2) {
        $order_id = (int)$data['order_id'];
        api_trigger_webhook($order_id, 3); // 3 represents refunded/closed
    }
}
