<?php
/**
 * 订单查询控制器
 */

class Order_Query_Controller {

    function display($params) {
        $CACHE = Cache::getInstance();
        $options_cache = $CACHE->readCache('options');
        extract($options_cache);
        
        // 检查是否有查询参数
        $contact = Input::getStrVar('contact');
        
        if (!empty($contact)) {
            // 显示查询结果页面
            $this->showResult($contact, $blogname);
        } else {
            // 显示查询表单页面
            $site_title = '订单查询 - ' . $blogname;
            
            include View::getCommonView('header');
            include View::getView('order_query');
            include View::getCommonView('footer');
        }
    }
    
    /**
     * 显示查询结果
     */
    private function showResult($contact, $blogname) {
        $CACHE = Cache::getInstance();
        $options_cache = $CACHE->readCache('options');
        extract($options_cache);
        // 初始化数据库连接
        $db = Database::getInstance();
        $db_prefix = DB_PREFIX;
        
        // 自动取消超时未支付的订单（时间可在后台商城配置中设置）
        $_cpt = intval(Option::get('continue_pay_timeout'));
        $_cpt = $_cpt > 0 ? $_cpt : 30;
        $expire_time = time() - $_cpt * 60;
        $db->query("UPDATE {$db_prefix}order SET status = 3 WHERE status = 0 AND create_time < {$expire_time} AND delete_time IS NULL");
        
        // 构建查询条件
        $where = "where o.delete_time is null and (";
        $where .= " (o.out_trade_no = '{$contact}' or o.up_no = '{$contact}') or";
        $where .= " ol.attach_user like '%:\"{$contact}\"%' or";
        $where .= " orq.content = '{$contact}'";
        $where .= ")";
        
        $sql = "SELECT DISTINCT o.id, ol.id as order_list_id, ol.goods_id, g.type, 
o.out_trade_no, o.up_no, g.title, o.create_time, o.pay_time, o.status, o.amount, ol.quantity, ol.attr_spec, 
ol.attach_user, ol.unit_price, ol.price as item_price, ol.cost_price, o.payment, o.pay_plugin, g.cover 
FROM {$db_prefix}order o 
INNER JOIN {$db_prefix}order_list ol ON o.id = ol.order_id 
LEFT JOIN {$db_prefix}goods g on ol.goods_id = g.id 
LEFT JOIN {$db_prefix}order_required orq ON o.id = orq.order_id 
{$where} order by o.id desc, ol.price desc";
        
        $res = $db->fetch_all($sql);
        if (!empty($res)) {
            $filtered = [];
            foreach ($res as $row) {
                if (($row['type'] ?? '') === 'physical' && !$this->isPhysicalOrderQueryAllowed($row, $contact)) {
                    continue;
                }
                $filtered[] = $row;
            }
            $res = $filtered;
        }
        $list = [];
        
        if (!empty($res)) {
            foreach($res as $key => $val){
                $_text = empty($val['attach_user']) ? [] : json_decode($val['attach_user']);
                $res[$key]['attach_user_text'] = '';
                if (!empty($_text)) {
                    foreach($_text as $k => $v){
                        $res[$key]['attach_user_text'] .= $k . "：" . $v . "；";
                    }
                }
                $res[$key]['amount'] = number_format($val['amount'] / 100, 2);
                $res[$key]['goods_url'] = Url::goods($val['goods_id']);
                $res[$key]['url'] = Url::goods($val['goods_id']);
                $res[$key]['pay_time_text'] = empty($val['pay_time']) ? '未付款' : date('Y-m-d H:i:s', $val['pay_time']);
                // 订单状态文本
                $status_map = [0 => '待付款', 1 => '待发货', 2 => '已完成', 3 => '已取消', 4 => '待收货'];
                $res[$key]['status_text'] = isset($status_map[$val['status']]) ? $status_map[$val['status']] : '未知';
                
                // 判断是否为赠品（item_price为0且attr_spec包含[赠品]）
                $res[$key]['is_gift'] = ($val['item_price'] == 0 && strpos($val['attr_spec'], '[赠品]') !== false);
                $res[$key]['unit_price_yuan'] = number_format($val['unit_price'] / 100, 2);
                if (empty($val['payment']) && !empty($val['pay_plugin'])) {
                    $res[$key]['payment'] = $val['pay_plugin'];
                }
                $res[$key]['is_refunding'] = false;
            }
            
            // 批量查询活跃售后，覆盖 status_text 和 is_refunding 标记
            $active_plugins = Option::get('active_plugins');
            $aftersale_enabled = is_array($active_plugins) && in_array('aftersale/aftersale.php', $active_plugins);
            if ($aftersale_enabled) {
                $_olIds = array_filter(array_column($res, 'order_list_id'));
                if (!empty($_olIds)) {
                    $_olIdsStr = implode(',', array_map('intval', $_olIds));
                    $_refRows = $db->fetch_all("SELECT DISTINCT order_list_id FROM {$db_prefix}aftersale WHERE order_list_id IN ({$_olIdsStr}) AND status IN (0, 1)");
                    $_refSet = [];
                    foreach ($_refRows as $_rr) { $_refSet[(int)$_rr['order_list_id']] = true; }
                    foreach ($res as $key => $val) {
                        if (isset($_refSet[(int)$val['order_list_id']])) {
                            $res[$key]['is_refunding'] = true;
                            $res[$key]['status_text'] = '售后中';
                        }
                    }
                }
            }
            
            $list = $res;
        }
        
        // 设置页面标题
        $site_title = '订单查询结果 - ' . $blogname;
        
        include View::getCommonView('header');
        include View::getView('order_result');
        include View::getCommonView('footer');
    }

    private function extractPhysicalOrderPhones($attachUser) {
        if (empty($attachUser)) {
            return [];
        }
        $data = json_decode($attachUser, true);
        if (!is_array($data)) {
            return [];
        }
        $phoneKeys = ['手机号', '手机号码', '收货手机号', '收货电话', '联系电话', 'phone', 'mobile'];
        $phones = [];
        foreach ($data as $key => $value) {
            if (!in_array((string)$key, $phoneKeys, true)) {
                continue;
            }
            if (is_scalar($value)) {
                $phones[] = (string)$value;
            }
        }
        return $phones;
    }

    private function normalizePhoneQueryValue($value) {
        return preg_replace('/\D+/', '', (string)$value);
    }

    private function isPhysicalOrderDirectNumberQuery($row, $contact) {
        $contact = (string)$contact;
        return $contact !== '' && (
            (string)($row['out_trade_no'] ?? '') === $contact ||
            (string)($row['up_no'] ?? '') === $contact
        );
    }

    private function isPhysicalOrderQueryAllowed($row, $contact) {
        if ($this->isPhysicalOrderDirectNumberQuery($row, $contact)) {
            return true;
        }
        $queryPhone = $this->normalizePhoneQueryValue($contact);
        if ($queryPhone === '') {
            return false;
        }
        foreach ($this->extractPhysicalOrderPhones($row['attach_user'] ?? '') as $phone) {
            if ($this->normalizePhoneQueryValue($phone) === $queryPhone) {
                return true;
            }
        }
        return false;
    }
}
