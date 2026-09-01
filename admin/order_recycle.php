<?php
/**
 * 订单回收站管理
 */

ob_start();
require_once 'globals.php';

// 订单回收站列表页面（已从 auto_clean_order 插件分离为内建功能）
if (empty($action)) {
    $popup = !empty($_GET['popup']);
    if ($popup) {
        include View::getAdmView('open_head');
        require_once View::getAdmView('order_recycle');
        include View::getAdmView('open_foot');
    } else {
        $br = '<a href="./">数据中心</a><a href="./order.php">订单管理</a><a><cite>订单回收站</cite></a>';
        include View::getAdmView(User::haveEditPermission() ? 'header' : 'uc_header');
        require_once View::getAdmView('order_recycle');
        include View::getAdmView(User::haveEditPermission() ? 'footer' : 'uc_footer');
    }
    View::output();
}

// 获取回收站订单列表（AJAX）
if ($action == 'index') {
    $db = Database::getInstance();
    $prefix = DB_PREFIX;
    
    $page = Input::getIntVar('page', 1);
    $limit = Input::getIntVar('limit', 10);
    $start = ($page - 1) * $limit;
    
    // 搜索条件
    $email_username = Input::getStrVar('email_username');
    $out_trade_no = Input::getStrVar('out_trade_no');
    $goods_title = Input::getStrVar('goods_title');
    $client_ip = Input::getStrVar('client_ip');
    $order_required = Input::getStrVar('order_required');
    $kami_content = Input::getStrVar('kami_content');
    $status = Input::getStrVar('status');
    
    $where = "o.delete_time IS NOT NULL";
    
    // 用户邮箱/昵称/手机
    if (!empty($email_username)) {
        $email_username = $db->escape_string($email_username);
        $where .= " AND (u.email LIKE '%{$email_username}%' OR u.username LIKE '%{$email_username}%' OR u.nickname LIKE '%{$email_username}%' OR u.tel LIKE '%{$email_username}%')";
    }
    
    // 订单号
    if (!empty($out_trade_no)) {
        $out_trade_no = $db->escape_string($out_trade_no);
        $where .= " AND (o.out_trade_no LIKE '%{$out_trade_no}%' OR o.up_no LIKE '%{$out_trade_no}%')";
    }
    
    // 商品名
    if (!empty($goods_title)) {
        $goods_title = $db->escape_string($goods_title);
        $where .= " AND g.title LIKE '%{$goods_title}%'";
    }
    
    // 下单IP
    if (!empty($client_ip)) {
        $client_ip = $db->escape_string($client_ip);
        $where .= " AND o.client_ip LIKE '%{$client_ip}%'";
    }
    
    // 下单必填项
    if (!empty($order_required)) {
        $order_required = $db->escape_string($order_required);
        $where .= " AND orq.content LIKE '%{$order_required}%'";
    }
    
    // 卡密反查
    if (!empty($kami_content)) {
        $kami_content = $db->escape_string($kami_content);
        $where .= " AND (
            EXISTS (SELECT 1 FROM {$prefix}deliver d WHERE d.order_id = o.id AND d.content LIKE '%{$kami_content}%')
            OR EXISTS (SELECT 1 FROM {$prefix}goods_once go JOIN {$prefix}order_list ol2 ON go.order_list_id = ol2.id WHERE ol2.order_id = o.id AND go.content LIKE '%{$kami_content}%')
            OR EXISTS (SELECT 1 FROM {$prefix}goods_general_sale ggs JOIN {$prefix}order_list ol3 ON ggs.order_list_id = ol3.id WHERE ol3.order_id = o.id AND ggs.content LIKE '%{$kami_content}%')
            OR EXISTS (SELECT 1 FROM {$prefix}goods_service_sale gss JOIN {$prefix}order_list ol4 ON gss.order_list_id = ol4.id WHERE ol4.order_id = o.id AND gss.content LIKE '%{$kami_content}%')
        )";
    }
    
    // 订单状态
    if ($status !== '' && $status !== null && isset($_GET['status']) && $_GET['status'] !== '') {
        if ($status === 'refunding') {
            $where .= " AND EXISTS (SELECT 1 FROM `{$prefix}aftersale` af WHERE af.order_id = o.id AND af.status IN (0, 1))";
        } else {
            $status = (int)$status;
            $where .= " AND o.status = {$status}";
        }
    }
    
    // 获取总数
    $count_sql = "SELECT COUNT(DISTINCT o.id) as cnt 
                  FROM `{$prefix}order` o
                  LEFT JOIN `{$prefix}user` u ON o.user_id = u.uid
                  LEFT JOIN `{$prefix}order_list` ol ON o.id = ol.order_id
                  LEFT JOIN `{$prefix}goods` g ON ol.goods_id = g.id
                  LEFT JOIN `{$prefix}order_required` orq ON o.id = orq.order_id
                  WHERE {$where}";
    $total_row = $db->once_fetch_array($count_sql);
    $total = $total_row ? (int)$total_row['cnt'] : 0;
    
    // 获取主订单列表
    $sql = "SELECT o.*, u.email as user_email, u.tel as user_tel, u.nickname as user_nickname
            FROM `{$prefix}order` o
            LEFT JOIN `{$prefix}user` u ON o.user_id = u.uid
            LEFT JOIN `{$prefix}order_list` ol ON o.id = ol.order_id
            LEFT JOIN `{$prefix}goods` g ON ol.goods_id = g.id
            LEFT JOIN `{$prefix}order_required` orq ON o.id = orq.order_id
            WHERE {$where}
            GROUP BY o.id
            ORDER BY o.delete_time DESC 
            LIMIT {$start}, {$limit}";
    
    $result = $db->query($sql);
    $orders = [];
    $order_ids = [];
    
    $status_map = [0 => '待付款', 1 => '待收货', 2 => '已完成', 3 => '已取消'];
    
    while ($row = $db->fetch_array($result)) {
        $row['amount'] = number_format($row['amount'] / 100, 2);
        $row['delete_time_text'] = date('Y-m-d H:i:s', $row['delete_time']);
        $row['pay_time'] = empty($row['pay_time']) ? '' : date('Y-m-d H:i:s', $row['pay_time']);
        $row['status_text'] = $status_map[$row['status']] ?? '未知';
        $row['user_nickname'] = $row['user_id'] == 0 ? '游客身份' : ($row['user_nickname'] ?: '-');
        $row['user_email'] = $row['user_email'] ?: '';
        $row['user_tel'] = $row['user_tel'] ?: '';
        $orders[] = $row;
        $order_ids[] = $row['id'];
    }
    
    // 获取子订单列表（包括赠品）
    if (!empty($order_ids)) {
        $order_ids_str = implode(',', array_unique($order_ids));
        $list_sql = "SELECT ol.*, g.title, g.type, g.cover
                     FROM `{$prefix}order_list` ol
                     LEFT JOIN `{$prefix}goods` g ON ol.goods_id = g.id
                     WHERE ol.order_id IN ({$order_ids_str})";
        $list_result = $db->query($list_sql);
        
        while ($row = $db->fetch_array($list_result)) {
            foreach ($orders as $key => $order) {
                if ($order['id'] == $row['order_id']) {
                    $row['unit_price'] = $row['unit_price'] / 100;
                    $row['attr_spec'] = empty($row['attr_spec']) ? '默认规格' : $row['attr_spec'];
                    $orders[$key]['list'][] = $row;
                }
            }
        }
        
        // 批量检测退款中状态
        $refunding_res = $db->fetch_all("SELECT DISTINCT order_id FROM `{$prefix}aftersale` WHERE order_id IN ({$order_ids_str}) AND status IN (0, 1)");
        $refunding_ids = [];
        if ($refunding_res) {
            foreach ($refunding_res as $r) {
                $refunding_ids[$r['order_id']] = true;
            }
        }
        foreach ($orders as $key => $val) {
            if (isset($refunding_ids[$val['id']])) {
                $orders[$key]['status_text'] = '退款中';
            }
        }
    }
    
    Output::data($orders, $total);
}

// 恢复订单
if ($action == 'restore') {
    $ids = Input::postStrVar('ids');
    if (empty($ids)) {
        Output::error('请选择要恢复的订单');
    }
    
    $ids = array_map('intval', explode(',', $ids));
    $ids_str = implode(',', $ids);
    
    $db = Database::getInstance();
    $db->query("UPDATE `" . DB_PREFIX . "order` SET delete_time = NULL WHERE id IN ({$ids_str}) AND delete_time IS NOT NULL");
    
    Output::ok(['count' => count($ids)]);
}

// 永久删除订单
if ($action == 'delete') {
    $ids = Input::postStrVar('ids');
    if (empty($ids)) {
        Output::error('请选择要删除的订单');
    }
    
    $ids = array_map('intval', explode(',', $ids));
    $ids_str = implode(',', $ids);
    
    $db = Database::getInstance();
    
    $prefix = DB_PREFIX;
    $stockCleaned = 0;
    
    // 删除相关数据
    foreach ($ids as $order_id) {
        // 获取子订单列表
        $order_list = $db->fetch_all("SELECT id, goods_id FROM `{$prefix}order_list` WHERE order_id = {$order_id}");
        foreach ($order_list as $ol) {
            // 清理已售卡密
            $db->query("DELETE FROM `{$prefix}goods_once` WHERE order_list_id = {$ol['id']} AND goods_id = {$ol['goods_id']}");
            $stockCleaned += $db->affected_rows();
            // 清理通用卡密销售记录
            $db->query("DELETE FROM `{$prefix}goods_general_sale` WHERE order_list_id = {$ol['id']}");
            // 清理人工发货销售记录
            $db->query("DELETE FROM `{$prefix}goods_service_sale` WHERE order_list_id = {$ol['id']}");
        }
        // 删除子订单
        $db->query("DELETE FROM `{$prefix}order_list` WHERE order_id = {$order_id}");
        // 删除下单必填项
        $db->query("DELETE FROM `{$prefix}order_required` WHERE order_id = {$order_id}");
        // 删除发货记录
        $db->query("DELETE FROM `{$prefix}deliver` WHERE order_id = {$order_id}");
    }
    
    // 删除主订单
    $db->query("DELETE FROM `{$prefix}order` WHERE id IN ({$ids_str}) AND delete_time IS NOT NULL");
    
    $msg = "成功删除 " . count($ids) . " 个订单";
    if ($stockCleaned > 0) {
        $msg .= "，同时清理了 {$stockCleaned} 条已售卡密记录";
    }
    Output::ok(['count' => count($ids), 'msg' => $msg]);
}

// 清空回收站
if ($action == 'empty') {
    $db = Database::getInstance();
    
    // 获取所有已删除订单ID
    $result = $db->query("SELECT id FROM `" . DB_PREFIX . "order` WHERE delete_time IS NOT NULL");
    $order_ids = [];
    while ($row = $db->fetch_array($result)) {
        $order_ids[] = $row['id'];
    }
    
    if (!empty($order_ids)) {
        $ids_str = implode(',', $order_ids);
        $prefix = DB_PREFIX;
        $stockCleaned = 0;
        
        // 先清理各子订单关联的卡密
        $order_list = $db->fetch_all("SELECT id, goods_id FROM `{$prefix}order_list` WHERE order_id IN ({$ids_str})");
        foreach ($order_list as $ol) {
            $db->query("DELETE FROM `{$prefix}goods_once` WHERE order_list_id = {$ol['id']} AND goods_id = {$ol['goods_id']}");
            $stockCleaned += $db->affected_rows();
            $db->query("DELETE FROM `{$prefix}goods_general_sale` WHERE order_list_id = {$ol['id']}");
            $db->query("DELETE FROM `{$prefix}goods_service_sale` WHERE order_list_id = {$ol['id']}");
        }
        
        // 删除相关数据
        $db->query("DELETE FROM `{$prefix}order_list` WHERE order_id IN ({$ids_str})");
        $db->query("DELETE FROM `{$prefix}order_required` WHERE order_id IN ({$ids_str})");
        $db->query("DELETE FROM `{$prefix}deliver` WHERE order_id IN ({$ids_str})");
        
        // 删除主订单
        $db->query("DELETE FROM `{$prefix}order` WHERE delete_time IS NOT NULL");
    }
    
    $msg = "成功清空 " . count($order_ids) . " 个订单";
    Output::ok(['count' => count($order_ids), 'msg' => $msg]);
}
