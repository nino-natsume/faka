<?php
/**
 * The productf management
 *
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

/**
 * @var string $action
 * @var object $CACHE
 */

require_once 'globals.php';

$goodsModel = new Goods_Model();
$stockModel = new Stock_Model();
$User_Model = new User_Model();
$MediaSort_Model = new MediaSort_Model();
$Template_Model = new Template_Model();

function adm_stock_excluded_goods_tables() {
    $tables = [];
    if (function_exists('doMultiAction')) doMultiAction('adm_stock_excluded_goods_tables', $tables, $tables);
    $result = [];
    foreach ((array)$tables as $table) {
        $table = trim((string)$table);
        if ($table !== '' && preg_match('/^[a-zA-Z0-9_]+$/', $table)) $result[$table] = true;
    }
    return array_keys($result);
}

function adm_stock_table_exists($db, $table) {
    static $cache = [];
    $table = trim((string)$table);
    if ($table === '' || !preg_match('/^[a-zA-Z0-9_]+$/', $table)) return false;
    if (isset($cache[$table])) return $cache[$table];
    $row = $db->once_fetch_array("SHOW TABLES LIKE '" . $db->escape_string($table) . "'");
    $cache[$table] = !empty($row);
    return $cache[$table];
}

function adm_stock_goods_exclude_where($goodsAlias = 'g') {
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $goodsAlias = preg_match('/^[a-zA-Z0-9_]+$/', (string)$goodsAlias) ? $goodsAlias : 'g';
    $where = [];
    foreach (adm_stock_excluded_goods_tables() as $table) {
        if (!adm_stock_table_exists($db, $db_prefix . $table)) continue;
        $where[] = "NOT EXISTS (SELECT 1 FROM {$db_prefix}{$table} adm_stock_dock WHERE adm_stock_dock.goods_id = {$goodsAlias}.id LIMIT 1)";
    }
    return empty($where) ? '1=1' : implode(' AND ', $where);
}

function adm_goods_is_any_docking($db, $db_prefix, $goods_id) {
    $goods_id = (int)$goods_id;
    if ($goods_id <= 0) return false;
    foreach (adm_stock_excluded_goods_tables() as $table) {
        if (!adm_stock_table_exists($db, $db_prefix . $table)) continue;
        if ($db->once_fetch_array("SELECT id FROM {$db_prefix}{$table} WHERE goods_id = {$goods_id} LIMIT 1")) return true;
    }
    return false;
}

function adm_stock_block_docking_goods($goods_id, $message = '对接商品库存由货源站管理，不能在本地添加或编辑库存') {
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    if (!adm_goods_is_any_docking($db, $db_prefix, (int)$goods_id)) return;
    if (class_exists('output')) output::error($message);
    emMsg($message, 'javascript:history.back();');
}

if($action == 'stock_add_new'){
    $goods_id = Input::getIntVar('goods_id');
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $goods = $db->once_fetch_array("SELECT * FROM `{$db_prefix}goods` WHERE `id` = {$goods_id}");
    adm_stock_block_docking_goods($goods_id);
    $skus = $db->fetch_all("SELECT goods_id, sku, stock as stock_count FROM {$db_prefix}skus WHERE goods_id={$goods_id} GROUP BY sku");
    $sku = $db->fetch_all("select * from {$db_prefix}sku_value");

    $sku_list = [];
    foreach($skus as $key => $val){
        $sku_list[$key]['sku_name'] = '';
        $sku_list[$key]['stock_count'] = $val['stock_count'];
        if($val['sku'] == 0){
            continue;
        }
        $sku_list[$key]['goods_id'] = $val['goods_id'];
        $sku_list[$key]['sku'] = $val['sku'];
        $sku_list[$key]['sku_name'] = '';
        $s = explode('-', $val['sku']);
        foreach($sku as $v){
            foreach($s as $sv){
                if($v['id'] == $sv){
                    $sku_list[$key]['sku_name'] .= $v['name'] . "；";
                }
            }
        }
    }


    include View::getAdmView('open_head');
    require_once View::getAdmView('stock_add_new');
    include View::getAdmView('open_foot');
    View::output();
}

if($action == 'edit'){
    $stock_id = Input::getIntVar('stock_id');
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $stock = $db->once_fetch_array("select * from {$db_prefix}stock where id={$stock_id}");
    adm_stock_block_docking_goods((int)($stock['goods_id'] ?? 0));
    $goods = $db->once_fetch_array("SELECT * FROM `{$db_prefix}goods` WHERE `id` = {$stock['goods_id']}");
    $skus = $db->once_fetch_array("select * from {$db_prefix}skus where goods_id={$stock['goods_id']} and sku='{$stock['sku']}'");
    include View::getAdmView('open_head');
    require_once View::getAdmView('stock_edit');
    include View::getAdmView('open_foot');
    View::output();
}

if ($action == 'stock_popup') {
    $isPopup = true;
    include View::getAdmView('open_head');
    require_once View::getAdmView('stock');
    include View::getAdmView('open_foot');
    View::output();
}

if($action == 'index_ws'){
    $goods_id = Input::getIntVar('goods_id');
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $goods = $db->once_fetch_array("SELECT * FROM `{$db_prefix}goods` WHERE `id` = {$goods_id}");
    adm_stock_block_docking_goods($goods_id);
    $skus = $db->fetch_all("select goods_id, sku, stock stock_count from {$db_prefix}skus where goods_id={$goods_id}");
    $sku = $db->fetch_all("select * from {$db_prefix}sku_value");
//    d($skus);die;
    $sku_list = [];
    foreach($skus as $key => $val){
        $sku_list[$key]['sku_name'] = '';
        $sku_list[$key]['stock_count'] = $val['stock_count'];
        if($val['sku'] == 0){
            continue;
        }
        $sku_list[$key]['goods_id'] = $val['goods_id'];
        $sku_list[$key]['sku'] = $val['sku'];
        $sku_list[$key]['sku_name'] = '';
        $s = explode('-', $val['sku']);
        foreach($sku as $v){
            foreach($s as $sv){
                if($v['id'] == $sv){
                    $sku_list[$key]['sku_name'] .= $v['name'] . "；";
                }
            }
        }
    }

    $goods['stock_page'] = "stock_index_ws";

    doMultiAction("adm_stock_page_ws", $goods, $goods);

    include View::getAdmView('open_head');
    require_once View::getAdmView($goods['stock_page']);
    include View::getAdmView('open_foot');
    View::output();
}
if($action == 'index_ws_ajax'){
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;

    $order_by = "order by ";
    $field = Input::getStrVar('field');
    $order_type = Input::getStrVar('order');
    if(!empty($order_type)){
        $order_by .= "s.{$field} {$order_type}, ";
    }

    $order_by .= "s.id asc";

    $sql = "SELECT * FROM `{$db_prefix}goods` g where g.delete_time is null and g.is_on_shelf=1 and " . adm_stock_goods_exclude_where('g') . " order by g.id asc";
    $goods_json = [];
    $goods = $db->fetch_all($sql);
    foreach($goods as $val){
        $goods_json[] = [
            'text' => $val['title'],
            'value' => $val['id']
        ];
    }

    $goods_json = json_encode($goods_json);
    $goods_id = Input::getIntVar('goods_id');
    $keyword = Input::getStrVar('keyword');
    $sku = Input::getStrVar('sku');


    $page = Input::getIntVar('page', 1);
    $limit = Input::getIntVar('limit');

    $where = "g.id={$goods_id} AND " . adm_stock_goods_exclude_where('g');

    if(!empty($sku)){
        $where .= " and s.sku = '{$sku}'";
    }
    if(!empty($keyword)){
        $where .= " and s.content like '%{$keyword}%'";
    }


    $start_limit = !empty($page) ? ($page - 1) * $limit : 0;
    $limit = "LIMIT $start_limit, " . $limit;

    $sql = "SELECT 
            g.title, s.content, s.create_time, g.delete_time, g.type, 
            sku.stock quantity, s.sku, s.id stock_id, g.type goods_type 
            FROM {$db_prefix}stock as s
            left join {$db_prefix}skus sku on s.sku=sku.sku and s.goods_id=sku.goods_id  
            JOIN {$db_prefix}goods as g 
                ON s.goods_id = g.id 
            WHERE 
                {$where}
            GROUP BY s.id 
            {$order_by} {$limit} ";

    $list = $db->fetch_all($sql);

    $sku = $db->fetch_all("select * from {$db_prefix}sku_value");

    foreach($list as $key => $val){
        $list[$key]['sku_name'] = '';
        $list[$key]['create_time'] = date('Y-m-d H:i', $val['create_time']);
        if($val['sku'] == 0){
            continue;
        }
        $s = explode('-', $val['sku']);
        foreach($sku as $v){
            foreach($s as $sv){
                if($v['id'] == $sv){
                    $list[$key]['sku_name'] .= $v['name'] . "；";
                }
            }
        }
    }
    $res = $db->once_fetch_array("SELECT 
    COUNT(DISTINCT s.id) AS total 
FROM {$db_prefix}stock as s 
JOIN {$db_prefix}goods as g 
    ON s.goods_id = g.id 
WHERE 
    {$where}
    ; ");

    $total = $res['total'];
    output::data($list, $res['total']);
}

if ($action == 'index_ys_ajax') {

    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;

    $sql = "SELECT * FROM `{$db_prefix}goods` g where g.delete_time is null and g.is_on_shelf=1 and " . adm_stock_goods_exclude_where('g') . " order by g.id asc";
    $goods_json = [];
    $goods = $db->fetch_all($sql);
    foreach($goods as $val){
        $goods_json[] = [
            'text' => $val['title'],
            'value' => $val['id']
        ];
    }

    $goods_json = json_encode($goods_json);
    $goods_id = Input::getIntVar('goods_id', 0);
    $keyword = Input::getStrVar('keyword', '');

    $page = Input::getIntVar('page', 1);


    $where = "g.type NOT IN ('post', 'xuni') and d.delete_time is null";
    if(!empty($goods_id)){
        $where .= " and g.id={$goods_id}";
    }
    if(!empty($keyword)){
        $where .= " and (d.content LIKE '%{$keyword}%' or skv.name LIKE '%{$keyword}%')";
    }


    $start_limit = !empty($page) ? ($page - 1) * Option::get('admin_article_perpage_num') : 0;
    $limit = "LIMIT $start_limit, " . Option::get('admin_article_perpage_num');

    if(empty($keyword) && empty($keyword)){
        $sql = "select 
                d.*, g.title, g.delete_time goods_delete_time, g.type goods_type, ol.sku
            from " . DB_PREFIX . "deliver d
            left join " . DB_PREFIX . "order_list ol on d.order_list_id=ol.id 
            left join " . DB_PREFIX . "goods g on ol.goods_id=g.id
            where {$where}
            order by id desc {$limit}";
    }else{
        $sql = "SELECT 
    d.*, 
    g.title, 
    g.delete_time goods_delete_time, 
    g.type goods_type, ol.sku, 
    GROUP_CONCAT(DISTINCT skv.name SEPARATOR ', ') AS sku_names
FROM " . DB_PREFIX . "deliver d
LEFT JOIN " . DB_PREFIX . "order_list ol ON d.order_list_id = ol.id 
LEFT JOIN " . DB_PREFIX . "goods g ON ol.goods_id = g.id
LEFT JOIN " . DB_PREFIX . "sku_value skv 
    ON FIND_IN_SET(skv.id, REPLACE(ol.sku, '-', ',')) > 0
WHERE {$where}
GROUP BY d.id
ORDER BY d.id DESC {$limit};";
    }


    $list = $db->fetch_all($sql);



    $sku = $db->fetch_all("select * from " . DB_PREFIX . "sku_value");

    foreach($list as $key => $val){
        $list[$key]['sku_name'] = '';
        $list[$key]['create_time'] = date('Y-m-d H:i', $val['create_time']);
        if(empty($val['sku'])){
            continue;
        }
        $s = explode('-', $val['sku']);
        foreach($sku as $v){
            foreach($s as $sv){
                if($v['id'] == $sv){
                    $list[$key]['sku_name'] .= $v['name'] . "；";
                }
            }
        }
    }

//    d($list);die;

    if(empty($keyword) && empty($keyword)){
        $res = $db->once_fetch_array("select 
                count(d.id) total
            from " . DB_PREFIX . "deliver d
            left join " . DB_PREFIX . "order_list ol on d.order_list_id=ol.id 
            left join " . DB_PREFIX . "goods g on ol.goods_id=g.id
            where {$where}");
    }else{
        $sql = "select 
                count(d.id) total
            from " . DB_PREFIX . "deliver d
            left join " . DB_PREFIX . "order_list ol on d.order_list_id=ol.id 
            left join " . DB_PREFIX . "goods g on ol.goods_id=g.id
            LEFT JOIN " . DB_PREFIX . "sku_value skv ON FIND_IN_SET(skv.id, REPLACE(ol.sku, '-', ',')) > 0
            where {$where}";
//echo $sql;die;

        $res = $db->once_fetch_array($sql);
    }

    output::data($list, $res['total']);



}

if($action == 'export_page'){
    $goods_id = Input::getIntVar('goods_id');
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $sql = "SELECT * FROM `{$db_prefix}goods` WHERE `id` = {$goods_id}";
    $goods = $db->once_fetch_array($sql);
    adm_stock_block_docking_goods($goods_id);
    include View::getAdmView('open_head');
    require_once View::getAdmView('stock_export');
    include View::getAdmView('open_foot');
    View::output();
}

if($action == 'export_log'){
    $goods_id = Input::getIntVar('goods_id');
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $sql = "SELECT * FROM `{$db_prefix}goods` WHERE `id` = {$goods_id}";
    $goods = $db->once_fetch_array($sql);
    adm_stock_block_docking_goods($goods_id);
    include View::getAdmView('open_head');
    require_once View::getAdmView('stock_export_log');
    include View::getAdmView('open_foot');
    View::output();
}
if($action == 'export_log_ajax'){
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $page = Input::getIntVar('page', 1);
    $page_num = Input::getIntVar('limit', 10);
    $start = ($page - 1) * $page_num;
    $limit = "limit {$start}, {$page_num}";
    $order_field = Input::getStrVar('field', 'id');
    $order_type = Input::getStrVar('order', 'desc');
    $order_by = "order by l.{$order_field} {$order_type}";

    $sql = "select l.*, g.title from {$db_prefix}stock_export_log as l 
left join {$db_prefix}goods as g on g.id=l.goods_id 
 {$order_by} {$limit}";
    $list = $db->fetch_all($sql);
    foreach($list as $key => $val){
        $list[$key]['create_time'] = date('Y-m-d H:i:s', $val['create_time']);
    }
    $sql = "select count(distinct id) as count from {$db_prefix}stock_export_log";
    $total = $db->once_fetch_array($sql)['count'];

    output::data($list, $total);
}

if($action == 'index_ys'){
    $goods_id = Input::getIntVar('goods_id');
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $sql = "SELECT * FROM `{$db_prefix}goods` WHERE `id` = {$goods_id}";
    $goods = $db->once_fetch_array($sql);
    adm_stock_block_docking_goods($goods_id);
    $goods['stock_page'] = "stock_index_ys";

    doMultiAction("adm_stock_page_ys", $goods, $goods);

    include View::getAdmView('open_head');

    require_once View::getAdmView($goods['stock_page']);
    include View::getAdmView('open_foot');
    View::output();
}

if(empty($action) || $action == 'sales'){
    $br = '<a href="./">数据中心</a><a href="./goods.php">商品管理</a><a><cite>库存管理</cite></a>';
}

if (empty($action)) {

    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;

    $sql = "SELECT * FROM `{$db_prefix}goods` g where g.delete_time is null and g.is_on_shelf=1 and " . adm_stock_goods_exclude_where('g') . " order by g.id asc";
    $goods_json = [];
    $goods = $db->fetch_all($sql);
    foreach($goods as $val){
        $goods_json[] = [
            'text' => $val['title'],
            'value' => $val['id']
        ];
    }

    $goods_json = json_encode($goods_json);
    $goods_id = Input::getIntVar('goods_id', 0);
    $keyword = Input::getStrVar('keyword', '');


	$page = Input::getIntVar('page', 1);


    $where = "";
    $stockGoodsWhere = adm_stock_goods_exclude_where('g');
    if(!empty($goods_id)){
        if (adm_goods_is_any_docking($db, $db_prefix, $goods_id)) {
            $where .= "g.id=0";
        } else {
            $where .= "g.id={$goods_id} and {$stockGoodsWhere}";
        }
    } else {
        $where .= $stockGoodsWhere;
    }
    if(!empty($keyword)){
        if(empty($where)){
            $where .= "s.content LIKE '%{$keyword}%' or skv.name LIKE '%{$keyword}%'";
        }else{
            $where .= " and (s.content LIKE '%{$keyword}%' or skv.name LIKE '%{$keyword}%')";
        }
    }
//echo $where;die;

    $start_limit = !empty($page) ? ($page - 1) * Option::get('admin_article_perpage_num') : 0;
    $limit = "LIMIT $start_limit, " . Option::get('admin_article_perpage_num');
    if(empty($goods_id) && empty($keyword)){
        $sql = "SELECT s.create_time, g.title, s.content, g.delete_time, g.type goods_type, s.sku, s.id stock_id FROM " . DB_PREFIX . "stock as s join " . DB_PREFIX . "goods as g on s.goods_id=g.id where {$stockGoodsWhere} order by s.id desc {$limit}";
    }else{

        $sql = "SELECT 
    g.title, s.content, s.create_time, g.delete_time, g.type, s.sku, s.id stock_id, g.type goods_type 
FROM " . DB_PREFIX . "stock as s 
JOIN " . DB_PREFIX . "goods as g 
    ON s.goods_id = g.id 
LEFT JOIN " . DB_PREFIX . "sku_value as skv 
    ON FIND_IN_SET(skv.id, REPLACE(s.sku, '-', ',')) > 0 
WHERE 
    {$where}
GROUP BY s.id 
ORDER BY s.id DESC {$limit} ";
    }
//echo $sql;die;
//    OR s.content LIKE '%{$keyword}%'
//    OR skv.name LIKE '%{$keyword}%'

    $list = $db->fetch_all($sql);
//echo 1;die;

    $sku = $db->fetch_all("select * from " . DB_PREFIX . "sku_value");

    foreach($list as $key => $val){
        $list[$key]['sku_name'] = '';
        if($val['sku'] == 0){
            continue;
        }
        $s = explode('-', $val['sku']);
        foreach($sku as $v){
            foreach($s as $sv){
                if($v['id'] == $sv){
                    $list[$key]['sku_name'] .= $v['name'] . "；";
                }
            }
        }
    }
    if(empty($goods_id) && empty($keyword)){
        $res = $db->once_fetch_array("SELECT count(s.id) total FROM " . DB_PREFIX . "stock as s join " . DB_PREFIX . "goods as g on s.goods_id=g.id where {$stockGoodsWhere}");
    }else{
        $res = $db->once_fetch_array("SELECT 
    COUNT(DISTINCT s.id) AS total 
FROM " . DB_PREFIX . "stock as s 
JOIN " . DB_PREFIX . "goods as g 
    ON s.goods_id = g.id 
LEFT JOIN " . DB_PREFIX . "sku_value as skv 
    ON FIND_IN_SET(skv.id, REPLACE(s.sku, '-', ',')) > 0 
WHERE 
    {$where}
    ; ");
    }

    $total = $res['total'];


    $subPage = '';
    foreach ($_GET as $key => $val) {
        $subPage .= $key != 'page' ? "&$key=$val" : '';
    }
    $pageurl = pagination($total, Option::get('admin_article_perpage_num'), $page, "stock.php?{$subPage}&page=");

    $Sort_Model = new Sort_Model();
    $sorts = $Sort_Model->getSorts('goods');
    $goods = $db->fetch_all("select * from " . DB_PREFIX . "goods g where g.delete_time is null and " . adm_stock_goods_exclude_where('g') . " order by g.id desc");

    $skus = $db->fetch_all("select goods_id, sku from " . DB_PREFIX . "skus");

    $sku_list = [];
    foreach($skus as $key => $val){
        $sku_list[$key]['sku_name'] = '';
        if($val['sku'] == 0){
            continue;
        }
        $sku_list[$key]['goods_id'] = $val['goods_id'];
        $sku_list[$key]['sku'] = $val['sku'];
        $sku_list[$key]['sku_name'] = '';
        $s = explode('-', $val['sku']);
        foreach($sku as $v){
            foreach($s as $sv){
                if($v['id'] == $sv){
                    $sku_list[$key]['sku_name'] .= $v['name'] . "；";
                }
            }
        }
    }


    include View::getAdmView(User::haveEditPermission() ? 'header' : 'uc_header');
    require_once View::getAdmView('stock');
    include View::getAdmView(User::haveEditPermission() ? 'footer' : 'uc_footer');
    View::output();
}

if($action == 'export_ajax'){
    $goods_id = Input::postStrVar('goods_id');
    $sku = Input::postStrVar('sku');
    $export_range = Input::postStrVar('export_range');
    $export_num = Input::postIntVar('export_num', 0);
    $is_delete = Input::postStrVar('is_delete');

    $start_time = Input::postStrVar('start_time', null);
    $end_time = Input::postStrVar('end_time', null);


    if($export_range == 'time'){
        if(empty($start_time) || empty($end_time)){
            output::error('请选择卡密添加时间');
        }
        if($start_time > $end_time){
            output::error('开始时间不能大于截止时间');
        }
    }

    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;

    $where = '';
    if($sku != 0 && !empty($sku)){
        $where .= " and sku='{$sku}'";
    }
    if($export_range == 'time'){
        $start_time .= ' 00:00:00';
        $end_time .= ' 23:59:59';
        $where .= " and create_time BETWEEN UNIX_TIMESTAMP('{$start_time}') and UNIX_TIMESTAMP('{$end_time}')";
    }

    $sql = "select * from {$db_prefix}stock where goods_id={$goods_id} {$where} order by id asc;";



    $stock = $db->fetch_all($sql);

    if(empty($stock)){
        emMsg('暂无库存', 'javascript:window.close();');
    }

    $sku_value = $db->fetch_all("select * from {$db_prefix}sku_value");

    $data = [];
    foreach($stock as $val){
        if($val['sku'] == 0){
            if($export_range == 'num'){
                if(!empty($data['默认规格']) && count($data['默认规格']) >= $export_num){
                    continue;
                }
            }
            $data['默认规格'][] = [
                'content' => $val['content'],
                'id' => $val['id'],
                'sku' => $val['sku'],
                'goods_id' => $val['goods_id']
            ];
        }else{


            $temp = explode('-', $val['sku']);
            $sku_name = "";
            foreach($temp as $v){
                foreach($sku_value as $sv){
                    if($v == $sv['id']){
                        $sku_name .= $sv['name'] . "；";
                    }
                }
            }

            if($export_range == 'num'){
                if(!empty($data[$sku_name]) && count($data[$sku_name]) >= $export_num){
                    continue;
                }
            }

            $data[$sku_name][] = [
                'content' => $val['content'],
                'id' => $val['id'],
                'quantity' => $val['quantity'],
                'sku' => $val['sku'],
                'goods_id' => $val['goods_id']
            ];
        }
    }
    $goods = $db->once_fetch_array("select * from {$db_prefix}goods where id = {$goods_id}");

    $timestamp = time();

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    $filename = "导出卡密_{$timestamp}_{$goods_id}.txt";
    $saveDir = DC_ROOT . '/content/em_temp/';
    if (!is_dir($saveDir)) {
        mkdir($saveDir, 0755, true);
    }
    $filePath = $saveDir . $filename;
    // 循环数组并写入文件
    $fileHandle = fopen($filePath, 'w');
    if ($fileHandle) {
        // 遍历数组，将每个卡密写入文件
        foreach ($data as $key => $val) {
            fwrite($fileHandle, "---- " . $key . "\n");
            foreach ($val as $v) {
                fwrite($fileHandle, $v['content'] . "\n");
            }
        }
        fclose($fileHandle);


        // 删除卡密
        try {
            $db->beginTransaction();

            if($is_delete == 1){

                foreach($data as $val){
                    $ids = [];
                    foreach($val as $v){
                        $ids[] = $v['id'];
                    }
                    $ids = implode(',', $ids);
                    $db->query("delete from {$db_prefix}stock where id in({$ids})");

                    $goods_stock_count = $stockModel->getStockCount($v['goods_id']);
                    $goods_sku_stock = $stockModel->getSkuStock($v['goods_id'], $v['sku']);
                    $stockModel->updateSkuStock($v['goods_id'], $v['sku'], $goods_sku_stock);
                    $stockModel->updateGoodsStock($v['goods_id'], $goods_stock_count);
                }




            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollback();
            output::error($e->getMessage());
        }

        // 保存库存导出记录

        $sql = "INSERT INTO `{$db_prefix}stock_export_log` (`filename`, `goods_id`, `create_time`) VALUES ('{$filename}', {$goods_id}, {$timestamp})";
        $db->query($sql);

        // 生成下载地址
        output::ok(DC_URL . 'admin/download.php?filename=' . $filename);
    } else {
        output::error('文件权限不足，请设置网站目录权限为755');
    }


}



if($action == 'del'){
    $ids = Input::postStrVar('ids');
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $goods_id = Input::postIntVar('goods_id');
    try {
        $db->beginTransaction();
        $goods = $db->once_fetch_array("select * from {$db_prefix}goods where id = {$goods_id}");
        $res = $db->fetch_all("select * from {$db_prefix}stock where id in ({$ids})");
        $db->query("DELETE FROM {$db_prefix}stock WHERE id IN ({$ids})");
        if($goods['is_sku'] == 'n'){
            $sku = '0';
            if($goods['type'] != 'duli'){
                $stockModel->updateSkuStock($goods_id, $sku, 0);
            }
            $sku_stock_count = $stockModel->getStockCount($goods_id);
            $goods_stock_count = $sku_stock_count;

            $stockModel->updateSkuStock($goods_id, $sku, $sku_stock_count);
            $stockModel->updateGoodsStock($goods_id, $goods_stock_count);
        }
        if($goods['is_sku'] == 'y'){
            foreach($res as $val){
                if($goods['type'] != 'duli'){
                    $stockModel->updateSkuStock($goods_id, $val['sku'], 0);
                }
//                d($val);
                $sku_stock_count = $stockModel->getSkuStock($goods_id, $val['sku']);
//                var_dump($sku_stock_count);die;
                $goods_stock_count = $stockModel->getStockCount($goods_id);

                $stockModel->updateSkuStock($goods_id, $val['sku'], $sku_stock_count);
                $stockModel->updateGoodsStock($goods_id, $goods_stock_count);
            }
        }



        $db->commit();
    } catch (Exception $e) {
        $db->rollback();
        output::error($e->getMessage());
    }



    output::ok();
}
if($action == 'del_export_log'){
    $ids = Input::postStrVar('ids');
    $sql = "DELETE FROM " . DB_PREFIX . "stock_export_log WHERE id IN ({$ids})";
    $db = Database::getInstance();
    $db->query($sql);
    output::ok();
}

if($action == 'delete_sales'){
    $ids = Input::postStrVar('ids');
    $timestamp = time();
    $sql = "update " . DB_PREFIX . "deliver set delete_time = '{$timestamp}' WHERE id IN ({$ids})";

    $db = Database::getInstance();
    $db->query($sql);
    output::ok();
}

if ($action === 'add_ajax') {
    $goods_id = Input::postIntVar('goods_id', null);


    if($goods_id){
        $timestamp = time();
        $goods = $goodsModel->getOneGoodsForAdmin($goods_id);
        $db = Database::getInstance();
        $db_prefix = DB_PREFIX;
        adm_stock_block_docking_goods($goods_id);

        try {
            $db->beginTransaction();

            if($goods['is_sku'] == 'n'){
                $sku = '0';
                $content = Input::postStrVar('content');
                $quantity = Input::postIntVar('quantity');
                $skus = $db->once_fetch_array("select * from {$db_prefix}skus where goods_id={$goods_id} and sku='{$sku}'");
                if($goods['type'] == 'guding'){ // 固定卡密
                    $is_stock = $stockModel->isStock($goods_id, $sku);
                    if($is_stock){
                        $stockModel->updateStockContent($is_stock['id'], $content);
                    }else{
                        $stockModel->addStock($goods_id, $sku, $content);
                    }
                    // 更新sku表和商品表的库存数量
                    $stockModel->updateSkuStock($goods_id, $sku, $quantity);
                    $goods_stock_count = $stockModel->getStockCount($goods_id, $sku);
                    $stockModel->updateGoodsStock($goods_id, $goods_stock_count);
                }
                if($goods['type'] == 'xuni'){ // 虚拟服务
                    $is_stock = $stockModel->isStock($goods_id, $sku);
                    if($is_stock){
                        $stockModel->updateStockContent($is_stock['id'], null);
                    }else{
                        $stockModel->addStock($goods_id, $sku, null);
                    }
                    // 更新sku表和商品表的库存数量
                    $stockModel->updateSkuStock($goods_id, $sku, $quantity);
                    $goods_stock_count = $stockModel->getStockCount($goods_id, $sku);
                    $stockModel->updateGoodsStock($goods_id, $goods_stock_count);
                }
                if($goods['type'] == 'post'){ // 自定义访问接口
                    $is_stock = $stockModel->isStock($goods_id, $sku);
                    if($is_stock){
                        $update = [
                            'create_time' => $timestamp,
                            'quantity' => Input::postIntVar('quantity', 0),
                        ];
                        $stockModel->updateStock($update, $goods_id, $sku);
                        $add_stock_count += Input::postIntVar('quantity', 0) - $is_stock['quantity'];
                    }else{
                        $insert = [
                            'goods_id' => $goods_id,
                            'sku' => $sku,
                            'create_time' => $timestamp,
                            'quantity' => Input::postIntVar('quantity'),
                        ];
                        $stockModel->addStock($insert);
                        $add_stock_count += Input::postIntVar('quantity');
                    }
                }
                if($goods['type'] == 'duli'){ // 一卡一密
                    $stock = Input::postStrVar('content');
                    $stock = array_filter(explode("\n", $stock));

                    if(!empty($stock)){
                        // 每批次插入数量（可根据实际情况调整，建议500-2000之间）
                        $batchSize = 1000;
                        $total = count($stock);
                        $batches = array_chunk($stock, $batchSize); // 分割数组为多个批次
                        foreach($batches as $batch){
                            $content = [];
                            foreach($batch as $v){
                                $content[] = "({$goods_id}, '0', '{$v}', '{$timestamp}')";
                            }
                            // 执行当前批次的批量插入
                            $db->query("INSERT INTO {$db_prefix}stock (goods_id, sku, content, create_time) VALUES " . implode(',', $content));
                            // 释放当前批次内存
                            unset($content, $batch);
                        }
                        // 更新sku表和商品表的库存数量
                        $goods_stock_count = $stockModel->getStockCount($goods_id, $sku);
                        $sku_stock_count = $goods_stock_count;
                        $stockModel->updateSkuStock($goods_id, $sku, $sku_stock_count);
                        $stockModel->updateGoodsStock($goods_id, $goods_stock_count);
                    }
                }

            }
            if($goods['is_sku'] == 'y'){
                $quantity = Input::postIntVar('quantity');
                $sku = Input::postStrVar('sku');
                $content = Input::postStrVar('content');
                if(empty($sku)){
                    throw new Exception('请选择商品规格');
                }
                if($goods['type'] == 'duli'){
                    $stock = Input::postStrVar('content');
                    $stock = array_filter(explode("\n", $stock));
                    if(!empty($stock)){
                        foreach($stock as $v){
                            $stockModel->addStock($goods_id, $sku, $v);
                        }
                    }
                    // 更新sku表和商品表的库存数量
                    $goods_stock_count = $stockModel->getStockCount($goods_id, $sku);
                    $sku_stock_count = $stockModel->getSkuStock($goods_id, $sku);
                    $stockModel->updateSkuStock($goods_id, $sku, $sku_stock_count);
                    $stockModel->updateGoodsStock($goods_id, $goods_stock_count);
                }
                if($goods['type'] == 'guding'){
                    $is_stock = $stockModel->isStock($goods_id, $sku);
                    if($is_stock){
                        $stockModel->updateStockContent($is_stock['id'], $content);
                    }else{
                        $stockModel->addStock($goods_id, $sku, $content);
                    }
                    // 更新sku表和商品表的库存数量
                    $stockModel->updateSkuStock($goods_id, $sku, $quantity);
                    $goods_stock_count = $stockModel->getStockCount($goods_id);
                    $stockModel->updateGoodsStock($goods_id, $goods_stock_count);
                }
                if($goods['type'] == 'xuni'){
                    $is_stock = $stockModel->isStock($goods_id, $sku);
                    if($is_stock){
                        $stockModel->updateStockContent($is_stock['id'], null);
                    }else{
                        $stockModel->addStock($goods_id, $sku, null);
                    }
                    // 更新sku表和商品表的库存数量
                    $stockModel->updateSkuStock($goods_id, $sku, $quantity);
                    $goods_stock_count = $stockModel->getStockCount($goods_id);
                    $stockModel->updateGoodsStock($goods_id, $goods_stock_count);
                }
                if($goods['type'] == 'post'){
                    $sku = Input::postStrVar('sku');
                    $is_stock = $stockModel->isStock($goods_id, $sku);
                    if($is_stock){
                        $update = [
                            'quantity' => Input::postIntVar('quantity'),
                        ];
                        $stockModel->updateStock($update, $goods_id, $sku);
                        $add_stock_count += Input::postIntVar('quantity') - $is_stock['quantity'];
                    }else{
                        $insert = [
                            'goods_id' => $goods_id,
                            'sku' => $sku,
                            'create_time' => $timestamp,
                            'quantity' => Input::postIntVar('quantity'),
                        ];
                        $stockModel->addStock($insert);
                        $add_stock_count += Input::postIntVar('quantity');
                    }
                }
            }
            $db->commit();
            
            // 触发库存添加钩子
            doAction('stock_added', $goods_id, $goods);
            
        } catch (Exception $e) {
            $db->rollback();
            output::error($e->getMessage());
        }
        output::ok();
    }
}

if($action == 'repair'){
    $goods_id = Input::postStrVar('goods_id');
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $skus = $db->fetch_all("select * from {$db_prefix}skus where goods_id = {$goods_id}");
    $goods_stock = 0;

    try {
        $db->beginTransaction();
        foreach($skus as $val){
            $stock = $db->fetch_all("select * from {$db_prefix}skus where goods_id = {$goods_id} and sku = '{$val['sku']}'");
            $count = 0;
            foreach($stock as $v){
                $count += $v['quantity'];
            }
            $db->query("update {$db_prefix}goods_trade set stock = {$count} where goods_id = {$goods_id} and sku = '{$val['sku']}'");
            $goods_stock += $count;
        }
        $db->query("update {$db_prefix}goods set stock = $goods_stock where id = {$goods_id}");
        $db->commit();
    } catch (Exception $e) {
        $db->rollback();
        output::error($e->getMessage());
    }
    output::ok();
}

// ================= 库存管理二级菜单页面 - Ajax 数据 =================
/**
 * 根据商品类型选择库存源表：
 * - once     → dc_goods_once（sale_time IS NULL 为未使用）
 * - general  → dc_goods_general（通用卡密不区分已/未使用）
 * - service  → dc_goods_service（虚拟服务模板，不区分已/未使用）
 * - 其他     → dc_stock（duli / guding / xuni 等核心类型）
 */
function stock_v2_source_table ($goodsType) {
    if ($goodsType === 'once')    return ['table' => 'goods_once',    'unused_where' => 'sale_time IS NULL'];
    if ($goodsType === 'general') return ['table' => 'goods_general', 'unused_where' => ''];
    if ($goodsType === 'service') return ['table' => 'goods_service',  'unused_where' => ''];
    return ['table' => 'stock', 'unused_where' => ''];
}

function stock_v2_used_source_table ($goodsType) {
    if ($goodsType === 'once') {
        return ['table' => 'goods_once', 'join_key' => 's.order_list_id', 'extra' => 's.sale_time IS NOT NULL', 'time_field' => 's.sale_time', 'goods_where' => 's.goods_id'];
    }
    if ($goodsType === 'general') {
        return ['table' => 'goods_general_sale', 'join_key' => 's.order_list_id', 'extra' => '', 'time_field' => 's.create_time', 'goods_where' => 's.goods_id'];
    }
    if ($goodsType === 'service') {
        return ['table' => 'goods_service_sale', 'join_key' => 's.order_list_id', 'extra' => "s.is_default = 'n'", 'time_field' => 's.create_time', 'goods_where' => 's.goods_id'];
    }
    return ['table' => 'deliver', 'join_key' => 's.order_list_id', 'extra' => 's.delete_time IS NULL', 'time_field' => 's.create_time', 'goods_where' => 'ol.goods_id'];
}

function stock_v2_import_supported ($goodsType) {
    return in_array($goodsType, ['duli', 'once'], true);
}

function stock_v2_import_insert_one ($db, $db_prefix, $goods_id, $skuEsc, $batchNoEsc, $content, $timestamp, $isOnceType) {
    $contentEsc = addslashes($content);
    if ($isOnceType) {
        $sql = "INSERT INTO {$db_prefix}goods_once (goods_id, sku, batch_no, content, create_time) VALUES ({$goods_id}, '{$skuEsc}', '{$batchNoEsc}', '{$contentEsc}', {$timestamp})";
    } else {
        $sql = "INSERT INTO {$db_prefix}stock (goods_id, sku, content, create_time) VALUES ({$goods_id}, '{$skuEsc}', '{$contentEsc}', {$timestamp})";
    }
    $db->query($sql);
}

if ($action == 'stock_ajax') {
    $page     = Input::getIntVar('page', 1);
    $limit    = Input::getIntVar('limit', 10);
    $goods_id = Input::getIntVar('goods_id', 0);
    $keyword  = addslashes(trim(Input::getStrVar('keyword', '')));
    $start    = ($page - 1) * $limit;

    if ($goods_id <= 0) {
        Output::data([], 0);
    }

    $db        = Database::getInstance();
    $db_prefix = DB_PREFIX;

    $goods = $db->once_fetch_array("SELECT id, title, type, is_sku, delete_time FROM {$db_prefix}goods WHERE id = {$goods_id}");
    if (empty($goods)) Output::data([], 0);
    if (adm_goods_is_any_docking($db, $db_prefix, $goods_id)) Output::data([], 0);

    if ($goods['type'] === 'service' && function_exists('plugin_goods_service_ensure_templates')) {
        plugin_goods_service_ensure_templates($goods_id, $goods);
    }

    $srcCfg = stock_v2_source_table($goods['type']);
    $srcTbl = $db_prefix . $srcCfg['table'];
    $isOnceType = ($goods['type'] === 'once');
    $batchField = $isOnceType ? 's.batch_no' : "''";

    $where = "s.goods_id = {$goods_id}";
    if (!empty($srcCfg['unused_where'])) {
        $where .= " AND s.{$srcCfg['unused_where']}";
    }
    $skuFilter = addslashes(trim(Input::getStrVar('sku', '')));
    if ($skuFilter !== '') {
        $where .= " AND s.sku = '{$skuFilter}'";
    }
    if ($keyword !== '') {
        $kwInt = intval($keyword);
        $kwParts = ["s.content LIKE '%{$keyword}%' "];
        if ($isOnceType) {
            $kwParts[] = "s.batch_no LIKE '%{$keyword}%'";
        }
        if ($kwInt > 0) {
            $kwParts[] = "s.id = {$kwInt}";
        }
        $where .= ' AND (' . implode(' OR ', $kwParts) . ')';
    }

    $sql = "SELECT s.id AS stock_id, s.sku, {$batchField} AS batch_no, s.content, s.create_time
              FROM {$srcTbl} AS s
             WHERE {$where}
          ORDER BY s.id DESC
             LIMIT {$start}, {$limit}";
    $list = $db->fetch_all($sql);

    $totalRes = $db->once_fetch_array("SELECT COUNT(*) AS total FROM {$srcTbl} AS s WHERE {$where}");

    $skuValues = $db->fetch_all("SELECT id, name FROM {$db_prefix}sku_value");
    $skuMap = [];
    foreach ($skuValues as $sv) $skuMap[$sv['id']] = $sv['name'];

    // 模板类（general/service）：每条卡密绑定一个 skus.stock（可用次数）
    $templateTypes  = ['general', 'service'];
    $isTemplateType = in_array($goods['type'], $templateTypes, true);
    $skuStockMap = [];
    if ($isTemplateType) {
        $skuRowsAll = $db->fetch_all("SELECT sku, stock FROM {$db_prefix}skus WHERE goods_id = {$goods_id}");
        foreach ($skuRowsAll as $ss) $skuStockMap[$ss['sku']] = (int)$ss['stock'];
    }

    foreach ($list as $k => $v) {
        $content = isset($v['content']) ? (string)$v['content'] : '';
        if ($content !== '' && !mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
        }
        $batchNo = isset($v['batch_no']) ? (string)$v['batch_no'] : '';
        if ($batchNo !== '' && !mb_check_encoding($batchNo, 'UTF-8')) {
            $batchNo = mb_convert_encoding($batchNo, 'UTF-8', 'UTF-8');
        }
        $list[$k]['goods_id']        = (int)$goods['id'];
        $list[$k]['title']           = htmlspecialchars((string)$goods['title'], ENT_QUOTES, 'UTF-8');
        $list[$k]['goods_type']      = $goods['type'];
        $list[$k]['is_sku']          = $goods['is_sku'];
        $list[$k]['is_deleted']      = !empty($goods['delete_time']) ? 1 : 0;
        $list[$k]['batch_no']        = $batchNo;
        $list[$k]['content']         = $content;
        $list[$k]['content_short']   = mb_substr($content, 0, 40, 'UTF-8');
        $list[$k]['goods_type_text'] = stock_v2_type_text($goods['type']);
        $list[$k]['create_time_fmt'] = !empty($v['create_time']) ? date('Y-m-d H:i', $v['create_time']) : '';
        // quantity：模板类从 skus.stock 读取可用次数；一卡一密恒为 1
        if ($isTemplateType) {
            $skKey = (string)($v['sku'] ?? '');
            $list[$k]['quantity'] = isset($skuStockMap[$skKey]) ? $skuStockMap[$skKey] : 0;
        } else {
            $list[$k]['quantity'] = 1;
        }
        $list[$k]['sku_name']        = '';
        if (!empty($v['sku']) && $v['sku'] != '0') {
            $parts = explode('-', $v['sku']);
            $names = [];
            foreach ($parts as $p) if (isset($skuMap[$p])) $names[] = $skuMap[$p];
            $list[$k]['sku_name'] = implode(' / ', $names);
        }
    }

    Output::data($list, (int)$totalRes['total']);
}

// 跨商品批量删除库存
if ($action == 'del_ajax') {
    if (!User::haveEditPermission()) Output::error('权限不足');
    $idsRaw = Input::postStrVar('ids');
    $ids    = array_filter(array_map('intval', explode(',', $idsRaw)));
    if (empty($ids)) Output::error('请选择要删除的库存');

    $db        = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $idList    = implode(',', $ids);

    $rows   = $db->fetch_all("SELECT id, goods_id, sku FROM {$db_prefix}stock WHERE id IN ({$idList})");
    $groups = [];
    foreach ($rows as $r) $groups[$r['goods_id']][] = $r;

    try {
        $db->beginTransaction();
        foreach ($groups as $gid => $stockRows) {
            $goods = $db->once_fetch_array("SELECT * FROM {$db_prefix}goods WHERE id = {$gid}");
            if (empty($goods)) continue;

            $stockIds    = array_map(function ($r) { return (int)$r['id']; }, $stockRows);
            $stockIdsStr = implode(',', $stockIds);
            $db->query("DELETE FROM {$db_prefix}stock WHERE id IN ({$stockIdsStr})");

            if ($goods['is_sku'] == 'n') {
                $sku_stock_count = $stockModel->getStockCount($gid);
                if ($goods['type'] != 'duli') {
                    $stockModel->updateSkuStock($gid, '0', $sku_stock_count);
                }
                $stockModel->updateGoodsStock($gid, $sku_stock_count);
            } else {
                foreach ($stockRows as $sr) {
                    $sku_stock_count = $stockModel->getSkuStock($gid, $sr['sku']);
                    if ($goods['type'] != 'duli') {
                        $stockModel->updateSkuStock($gid, $sr['sku'], $sku_stock_count);
                    }
                }
                $stockModel->updateGoodsStock($gid, $stockModel->getStockCount($gid));
            }
        }
        $db->commit();
    } catch (Exception $e) {
        $db->rollback();
        Output::error('删除失败: ' . $e->getMessage());
    }

    Output::ok();
}

// 编辑库存
if ($action === 'edit_ajax') {
    $stock_id  = Input::postIntVar('stock_id');
    $sku_stock = Input::postIntVar('quantity');
    $content   = Input::postStrVar('content');
    $db        = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $stockModel = new Stock_Model();
    try {
        $db->beginTransaction();
        // 查询库存条目
        $stock = $db->once_fetch_array("select * from {$db_prefix}stock where id = $stock_id");
        adm_stock_block_docking_goods((int)($stock['goods_id'] ?? 0));
        $goods = $db->once_fetch_array("select * from {$db_prefix}goods where id = {$stock['goods_id']}");

        // 获取当前规格下的库存数量
        if ($goods['type'] == 'duli') {
            $sku_stock = $stockModel->getSkuStock($stock['goods_id'], $stock['sku']);
        }
        // 修改规格下的库存数量
        $stockModel->updateSkuStock($stock['goods_id'], $stock['sku'], $sku_stock);
        // 获取当前商品下所有的库存数量
        $stock_count = $stockModel->getStockCount($stock['goods_id']);
        // 修改库存内容
        $stockModel->updateStockContent($stock_id, $content);
        // 修改商品表的库存数量
        $stockModel->updateGoodsStock($stock['goods_id'], $stock_count);
        $db->commit();
    } catch (Exception $e) {
        $db->rollback();
        output::error($e->getMessage());
    }

    Output::ok();
}

// ================= 库存管理 v2 专用接口 =================

/**
 * 构建"商品类型 → 显示名"映射：合并内置类型 + 插件 adm_add_goods_goodsinfo 钩子注入的类型
 */
function stock_v2_type_map () {
    static $cache = null;
    if ($cache !== null) return $cache;
    $map = [];
    // 内置 4 种
    foreach (['duli' => '一卡一密', 'guding' => '固定卡密', 'xuni' => '虚拟服务', 'post' => '接口类型'] as $k => $v) {
        $map[$k] = $v;
    }
    // 插件扩展（如 general / once / service）
    $tmpGoods = ['goods_type_all' => []];
    if (function_exists('doMultiAction')) {
        doMultiAction('adm_add_goods_goodsinfo', $tmpGoods, $tmpGoods);
    }
    if (!empty($tmpGoods['goods_type_all']) && is_array($tmpGoods['goods_type_all'])) {
        foreach ($tmpGoods['goods_type_all'] as $t) {
            if (!empty($t['value'])) $map[$t['value']] = $t['name'] ?? $t['value'];
        }
    }
    $cache = $map;
    return $cache;
}

/**
 * 统一的类型文本解析：先查 map，再兜底
 */
function stock_v2_type_text ($type) {
    $map = stock_v2_type_map();
    if (isset($map[$type])) return $map[$type];
    if (function_exists('goodsTypeText')) {
        $txt = goodsTypeText($type);
        if ($txt && $txt !== '未知类型') return $txt;
    }
    return $type ?: '未知类型';
}

// 商品选择器（带搜索/分页，支持 cover + type + 剩余库存）
if ($action == 'goods_picker_ajax') {
    $db        = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $page      = Input::getIntVar('page', 1);
    $limit     = Input::getIntVar('limit', 12);
    $keyword   = addslashes(trim(Input::getStrVar('keyword', '')));
    $start     = ($page - 1) * $limit;

    $where = 'g.delete_time IS NULL AND ' . adm_stock_goods_exclude_where('g');
    if ($keyword !== '') {
        $where .= " AND (g.title LIKE '%{$keyword}%'" . (ctype_digit($keyword) ? " OR g.id = {$keyword}" : '') . ")";
    }

    $sql = "SELECT g.id, g.title, g.cover, g.type, g.is_sku, g.stock, g.sales
            FROM {$db_prefix}goods g
            WHERE {$where}
            ORDER BY g.id DESC
            LIMIT {$start}, {$limit}";
    $list = $db->fetch_all($sql);

    foreach ($list as $k => $v) {
        $list[$k]['type_text'] = stock_v2_type_text($v['type']);
        // 按商品类型统计剩余：
        // - 一卡一密 (duli/once) = 源表行数（每行 = 1 张卡）
        // - 模板类 (general/service) = goods.stock（可用次数总和）
        // - 其他 = goods.stock
        $cfg = stock_v2_source_table($v['type']);
        if (in_array($v['type'], ['duli', 'once'], true)) {
            $w = "goods_id = {$v['id']}";
            if (!empty($cfg['unused_where'])) $w .= " AND {$cfg['unused_where']}";
            $cnt = $db->once_fetch_array("SELECT COUNT(*) c FROM {$db_prefix}{$cfg['table']} WHERE {$w}");
            $list[$k]['remaining'] = (int)$cnt['c'];
        } else {
            $list[$k]['remaining'] = (int)$v['stock'];
        }
        $title = (string)$v['title'];
        if ($title !== '' && !mb_check_encoding($title, 'UTF-8')) {
            $title = mb_convert_encoding($title, 'UTF-8', 'UTF-8');
        }
        $list[$k]['title'] = $title;
    }

    $totalRow = $db->once_fetch_array("SELECT COUNT(*) c FROM {$db_prefix}goods g WHERE {$where}");
    Output::data($list, (int)$totalRow['c']);
}

// 商品信息卡（类型、封面、规格剩余库存）
if ($action == 'goods_info_ajax') {
    $goods_id = Input::getIntVar('goods_id', 0);
    if ($goods_id <= 0) Output::error('无效的商品ID');

    $db        = Database::getInstance();
    $db_prefix = DB_PREFIX;

    $goods = $db->once_fetch_array("SELECT * FROM {$db_prefix}goods WHERE id = {$goods_id}");
    if (!$goods) Output::error('商品不存在');
    adm_stock_block_docking_goods($goods_id);

    if ($goods['type'] === 'service' && function_exists('plugin_goods_service_ensure_templates')) {
        plugin_goods_service_ensure_templates($goods_id, $goods);
    }

    $templateStatus = [
        'enabled'          => in_array($goods['type'], ['general', 'service'], true),
        'has_existing'     => false,
        'all_locked'       => false,
        'configured_count' => 0,
        'available_count'  => 0,
        'total_slots'      => $goods['is_sku'] == 'y' ? 0 : 1,
        'single_stock_id'  => 0,
    ];
    $templateSkuMap = [];
    if ($templateStatus['enabled']) {
        $templateTable = $goods['type'] === 'service' ? "{$db_prefix}goods_service" : "{$db_prefix}goods_general";
        $templateRows = $db->fetch_all("SELECT id, sku FROM {$templateTable} WHERE goods_id = {$goods_id} ORDER BY id ASC");
        foreach ($templateRows as $templateRow) {
            $mapSku = (string)$templateRow['sku'];
            if (!isset($templateSkuMap[$mapSku])) {
                $templateSkuMap[$mapSku] = (int)$templateRow['id'];
            }
        }
        $templateStatus['has_existing'] = !empty($templateSkuMap);
        $templateStatus['configured_count'] = count($templateSkuMap);
        if ($goods['is_sku'] != 'y' && isset($templateSkuMap['0'])) {
            $templateStatus['single_stock_id'] = (int)$templateSkuMap['0'];
        }
    }

    // 源表配置（once → goods_once 未售；general → goods_general；其他 → stock）
    $srcCfg = stock_v2_source_table($goods['type']);
    $srcTbl = $db_prefix . $srcCfg['table'];
    $srcWhereRemain = "goods_id = {$goods_id}";
    if (!empty($srcCfg['unused_where'])) {
        $srcWhereRemain .= " AND {$srcCfg['unused_where']}";
    }

    // 剩余库存：一卡一密用源表行数；模板类和其他用 goods.stock（可用次数）
    if (in_array($goods['type'], ['duli', 'once'], true)) {
        $cnt = $db->once_fetch_array("SELECT COUNT(*) c FROM {$srcTbl} WHERE {$srcWhereRemain}");
        $remaining = (int)$cnt['c'];
    } else {
        $remaining = (int)$goods['stock'];
    }

    // 已售数量：按类型取对应来源
    if ($goods['type'] === 'once') {
        $soldRow = $db->once_fetch_array("SELECT COUNT(*) c FROM {$db_prefix}goods_once WHERE goods_id = {$goods_id} AND sale_time IS NOT NULL");
    } elseif ($goods['type'] === 'general') {
        $soldRow = $db->once_fetch_array("SELECT COUNT(*) c FROM {$db_prefix}goods_general_sale WHERE goods_id = {$goods_id}");
    } elseif ($goods['type'] === 'service') {
        $soldRow = $db->once_fetch_array("SELECT COUNT(*) c FROM {$db_prefix}goods_service_sale WHERE goods_id = {$goods_id} AND is_default = 'n'");
    } else {
        $soldRow = $db->once_fetch_array("
            SELECT COUNT(*) c
              FROM {$db_prefix}deliver d
         LEFT JOIN {$db_prefix}order_list ol ON d.order_list_id = ol.id
             WHERE ol.goods_id = {$goods_id} AND d.delete_time IS NULL
        ");
    }
    $sold = (int)$soldRow['c'];

    // 规格列表
    $skus = [];
    if ($goods['is_sku'] == 'y') {
        $skuRows   = $db->fetch_all("SELECT sku, stock FROM {$db_prefix}skus WHERE goods_id = {$goods_id}");
        $skuValues = $db->fetch_all("SELECT id, name FROM {$db_prefix}sku_value");
        $map = [];
        foreach ($skuValues as $sv) $map[$sv['id']] = $sv['name'];

        foreach ($skuRows as $sr) {
            if ($sr['sku'] === '0' || $sr['sku'] === '') continue;
            $parts = explode('-', $sr['sku']);
            $names = [];
            foreach ($parts as $p) if (isset($map[$p])) $names[] = $map[$p];

            $stockNum = (int)$sr['stock'];
            // 一卡一密（duli/once）按源表条目数实时统计；模板类（general/service）直接用 skus.stock
            if (in_array($goods['type'], ['duli', 'once'], true)) {
                $skuEsc = addslashes($sr['sku']);
                $whereSku = "goods_id = {$goods_id} AND sku = '{$skuEsc}'";
                if (!empty($srcCfg['unused_where'])) $whereSku .= " AND {$srcCfg['unused_where']}";
                $c2 = $db->once_fetch_array("SELECT COUNT(*) c FROM {$srcTbl} WHERE {$whereSku}");
                $stockNum = (int)$c2['c'];
            }
            $skuKey = (string)$sr['sku'];
            $skus[] = [
                'sku'               => $sr['sku'],
                'sku_name'          => implode(' / ', $names),
                'stock'             => $stockNum,
                'template_exists'   => isset($templateSkuMap[$skuKey]),
                'template_stock_id' => isset($templateSkuMap[$skuKey]) ? (int)$templateSkuMap[$skuKey] : 0,
            ];
        }
    }

    if ($templateStatus['enabled']) {
        if ($goods['is_sku'] == 'y') {
            $templateStatus['total_slots'] = count($skus);
            $templateStatus['available_count'] = max(0, $templateStatus['total_slots'] - $templateStatus['configured_count']);
            $templateStatus['all_locked'] = $templateStatus['total_slots'] > 0 && $templateStatus['available_count'] === 0;
        } else {
            $templateStatus['available_count'] = isset($templateSkuMap['0']) ? 0 : 1;
            $templateStatus['all_locked'] = isset($templateSkuMap['0']);
        }
    }

    $title = (string)$goods['title'];
    if ($title !== '' && !mb_check_encoding($title, 'UTF-8')) {
        $title = mb_convert_encoding($title, 'UTF-8', 'UTF-8');
    }

    Output::data([
        'id'        => (int)$goods['id'],
        'title'     => $title,
        'cover'     => $goods['cover'],
        'type'      => $goods['type'],
        'type_text' => stock_v2_type_text($goods['type']),
        'is_sku'    => $goods['is_sku'],
        'remaining' => $remaining,
        'sold'      => $sold,
        'skus'      => $skus,
        'template_status' => $templateStatus,
    ]);
}

// 已使用列表（按类型分别查 dc_deliver / dc_goods_once / dc_goods_general_sale）
if ($action == 'used_ajax') {
    $goods_id = Input::getIntVar('goods_id', 0);
    if ($goods_id <= 0) Output::data([], 0);

    $db        = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $page      = Input::getIntVar('page', 1);
    $limit     = Input::getIntVar('limit', 10);
    $keyword   = addslashes(trim(Input::getStrVar('keyword', '')));
    $skuFilter = addslashes(trim(Input::getStrVar('sku', '')));
    $start     = ($page - 1) * $limit;

    $goodsRow = $db->once_fetch_array("SELECT id, type, title FROM {$db_prefix}goods WHERE id = {$goods_id}");
    if (empty($goodsRow)) Output::data([], 0);
    if (adm_goods_is_any_docking($db, $db_prefix, $goods_id)) Output::data([], 0);
    $goodsType = $goodsRow['type'];
    $isOnceType = ($goodsType === 'once');

    // 源表切换：once → goods_once(已售)，general → goods_general_sale，service → goods_service_sale(is_default=n)，其他 → deliver
    $pluginTypes = ['once', 'general', 'service'];
    if ($goodsType === 'once') {
        $srcExpr    = "{$db_prefix}goods_once";
        $srcJoinKey = 's.order_list_id';
        $srcExtra   = 's.sale_time IS NOT NULL';
        $timeField  = 's.sale_time';
    } elseif ($goodsType === 'general') {
        $srcExpr    = "{$db_prefix}goods_general_sale";
        $srcJoinKey = 's.order_list_id';
        $srcExtra   = '';
        $timeField  = 's.create_time';
    } elseif ($goodsType === 'service') {
        $srcExpr    = "{$db_prefix}goods_service_sale";
        $srcJoinKey = 's.order_list_id';
        $srcExtra   = "s.is_default = 'n'";
        $timeField  = 's.create_time';
    } else {
        $srcExpr    = "{$db_prefix}deliver";
        $srcJoinKey = 's.order_list_id';
        $srcExtra   = 's.delete_time IS NULL';
        $timeField  = 's.create_time';
    }

    // 通用 where：插件表直接用 s.goods_id；deliver 走 order_list 再 join
    $where = in_array($goodsType, $pluginTypes, true)
        ? "s.goods_id = {$goods_id}"
        : "ol.goods_id = {$goods_id}";
    if (!empty($srcExtra)) $where .= " AND {$srcExtra}";

    if ($skuFilter !== '') {
        $where .= in_array($goodsType, $pluginTypes, true)
            ? " AND s.sku = '{$skuFilter}'"
            : " AND ol.sku = '{$skuFilter}'";
    }
    if ($keyword !== '') {
        $kwInt = intval($keyword);
        $kwParts = [
            "s.content LIKE '%{$keyword}%'",
            "o.out_trade_no LIKE '%{$keyword}%'",
            "o.up_no LIKE '%{$keyword}%'",
            "o.client_ip LIKE '%{$keyword}%'",
            "o.email LIKE '%{$keyword}%'",
            "o.tel LIKE '%{$keyword}%'",
            "u.nickname LIKE '%{$keyword}%'",
            "u.username LIKE '%{$keyword}%'",
            "u.email LIKE '%{$keyword}%'",
            "u.tel LIKE '%{$keyword}%'",
        ];
        if ($isOnceType) {
            $kwParts[] = "s.batch_no LIKE '%{$keyword}%'";
        }
        if ($kwInt > 0) {
            $kwParts[] = "o.id = {$kwInt}";
            $kwParts[] = "s.id = {$kwInt}";
            $kwParts[] = "o.user_id = {$kwInt}";
        }
        $where .= ' AND (' . implode(' OR ', $kwParts) . ')';
    }

    $batchField = $isOnceType ? 's.batch_no' : "''";

    $sql = "SELECT s.id AS deliver_id, {$batchField} AS batch_no, s.content, {$timeField} AS deliver_time,
                   ol.order_id, ol.id AS order_list_id,
                   ol.sku, ol.quantity, ol.unit_price,
                   o.out_trade_no, o.up_no, o.client_ip, o.pay_time, o.status,
                   o.tel AS order_tel, o.email AS order_email, o.user_id,
                   u.nickname AS user_nickname, u.username AS user_username, u.email AS user_email, u.tel AS user_tel,
                   g.title, g.type AS goods_type
              FROM {$srcExpr} AS s
         LEFT JOIN {$db_prefix}order_list AS ol ON ol.id = {$srcJoinKey}
         LEFT JOIN {$db_prefix}order AS o ON o.id = ol.order_id
         LEFT JOIN {$db_prefix}user AS u ON u.uid = o.user_id
         LEFT JOIN {$db_prefix}goods AS g ON g.id = {$goods_id}
             WHERE {$where}
          ORDER BY s.id DESC
             LIMIT {$start}, {$limit}";
    $list = $db->fetch_all($sql);

    $skuValues = $db->fetch_all("SELECT id, name FROM {$db_prefix}sku_value");
    $skuMap = [];
    foreach ($skuValues as $sv) $skuMap[$sv['id']] = $sv['name'];

    foreach ($list as $k => $v) {
        $content = isset($v['content']) ? (string)$v['content'] : '';
        if ($content !== '' && !mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
        }
        $batchNo = isset($v['batch_no']) ? (string)$v['batch_no'] : '';
        if ($batchNo !== '' && !mb_check_encoding($batchNo, 'UTF-8')) {
            $batchNo = mb_convert_encoding($batchNo, 'UTF-8', 'UTF-8');
        }
        $list[$k]['batch_no']         = $batchNo;
        $list[$k]['content']          = $content;
        $list[$k]['content_short']    = mb_substr($content, 0, 40, 'UTF-8');
        $list[$k]['deliver_time_fmt'] = !empty($v['deliver_time']) ? date('Y-m-d H:i', $v['deliver_time']) : '';
        $list[$k]['pay_time_fmt']     = !empty($v['pay_time']) ? date('Y-m-d H:i', $v['pay_time']) : '';

        $userId = (int)$v['user_id'];
        $userNickname = isset($v['user_nickname']) ? trim((string)$v['user_nickname']) : '';
        if ($userNickname !== '' && !mb_check_encoding($userNickname, 'UTF-8')) {
            $userNickname = mb_convert_encoding($userNickname, 'UTF-8', 'UTF-8');
        }
        $userUsername = isset($v['user_username']) ? trim((string)$v['user_username']) : '';
        if ($userUsername !== '' && !mb_check_encoding($userUsername, 'UTF-8')) {
            $userUsername = mb_convert_encoding($userUsername, 'UTF-8', 'UTF-8');
        }
        $userEmail = isset($v['user_email']) ? trim((string)$v['user_email']) : '';
        if ($userEmail === '') {
            $userEmail = isset($v['order_email']) ? trim((string)$v['order_email']) : '';
        }
        if ($userEmail !== '' && !mb_check_encoding($userEmail, 'UTF-8')) {
            $userEmail = mb_convert_encoding($userEmail, 'UTF-8', 'UTF-8');
        }
        $userTel = isset($v['user_tel']) ? trim((string)$v['user_tel']) : '';
        if ($userTel === '') {
            $userTel = isset($v['order_tel']) ? trim((string)$v['order_tel']) : '';
        }
        if ($userTel !== '' && !mb_check_encoding($userTel, 'UTF-8')) {
            $userTel = mb_convert_encoding($userTel, 'UTF-8', 'UTF-8');
        }

        $buyerName = $userId > 0
            ? ($userNickname !== '' ? $userNickname : ($userUsername !== '' ? $userUsername : '已注册用户'))
            : '游客身份';
        $buyerContact = $userEmail !== '' ? $userEmail : $userTel;
        $buyerDisplay = '【游客订单】';
        if ($userId > 0) {
            $buyerDisplayParts = [];
            if ($userNickname !== '') {
                $buyerDisplayParts[] = $userNickname;
            }
            if ($userUsername !== '' && $userUsername !== $userNickname) {
                $buyerDisplayParts[] = $userUsername;
            }
            if (empty($buyerDisplayParts)) {
                $buyerDisplayParts[] = '已注册用户';
            }
            $buyerDisplay = '【' . implode(' / ', $buyerDisplayParts) . '】';
        }

        $list[$k]['buyer_name']       = $buyerName;
        $list[$k]['buyer_contact']    = $buyerContact;
        $list[$k]['buyer_display']    = $buyerDisplay;
        $list[$k]['buyer_uid_text']   = $userId > 0 ? ('UID：' . $userId) : '';
        $list[$k]['buyer_guest_text'] = $userId > 0 ? '' : '游客订单';
        $list[$k]['client_ip']        = isset($v['client_ip']) ? (string)$v['client_ip'] : '';
        $list[$k]['user_id']          = $userId;
        $userKeyword = $userUsername !== '' ? $userUsername : (string)$userId;
        $list[$k]['user_list_url']    = $userId > 0 ? ('user.php?keyword=' . rawurlencode($userKeyword)) : '';
        $list[$k]['order_list_url']   = !empty($v['out_trade_no']) ? ('order.php?out_trade_no=' . rawurlencode((string)$v['out_trade_no'])) : '';
        $list[$k]['sku_name']         = '';
        if (!empty($v['sku']) && $v['sku'] != '0') {
            $parts = explode('-', $v['sku']);
            $names = [];
            foreach ($parts as $p) if (isset($skuMap[$p])) $names[] = $skuMap[$p];
            $list[$k]['sku_name'] = implode(' / ', $names);
        }
    }

    $totalRow = $db->once_fetch_array("
        SELECT COUNT(*) c
          FROM {$srcExpr} AS s
     LEFT JOIN {$db_prefix}order_list AS ol ON ol.id = {$srcJoinKey}
     LEFT JOIN {$db_prefix}order AS o ON o.id = ol.order_id
     LEFT JOIN {$db_prefix}user AS u ON u.uid = o.user_id
         WHERE {$where}
    ");
    Output::data($list, (int)$totalRow['c']);
}
// 导入 txt 卡密（普通 / 去重）
if ($action == 'import_ajax') {
    if (!User::haveEditPermission()) Output::error('权限不足');
    Output::demoCheck();

    $goods_id = Input::postIntVar('goods_id', 0);
    $sku      = Input::postStrVar('sku', '0');
    $batch_no = trim(Input::postStrVar('batch_no', ''));
    $dedup    = Input::postIntVar('dedup', 0);
    if ($goods_id <= 0) Output::error('请先选择商品');

    $file = isset($_FILES['file']) ? $_FILES['file'] : null;
    if (empty($file) || !is_array($file) || empty($file['name'])) Output::error('请选择要导入的 txt 文件');
    if (!empty($file['error']) && $file['error'] != 0) Output::error('文件上传失败，错误码：' . $file['error']);

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'txt') Output::error('只支持 .txt 格式文件，其他格式无法识别');

    $raw = @file_get_contents($file['tmp_name']);
    if ($raw === false || $raw === '') Output::error('文件读取失败或内容为空');
    if (!mb_check_encoding($raw, 'UTF-8')) {
        $raw = mb_convert_encoding($raw, 'UTF-8', 'GBK,GB2312,UTF-8');
    }
    $raw = str_replace(["\r\n", "\r"], "\n", $raw);
    $lines = explode("\n", $raw);

    $clean = [];
    foreach ($lines as $l) {
        $l = trim($l);
        if ($l !== '') $clean[] = $l;
    }
    if (empty($clean)) Output::error('文件内没有有效卡密（空内容或全为空行）');

    $db        = Database::getInstance();
    $db_prefix = DB_PREFIX;

    $goods = $db->once_fetch_array("SELECT * FROM {$db_prefix}goods WHERE id = {$goods_id}");
    if (!$goods) Output::error('商品不存在');
    adm_stock_block_docking_goods($goods_id);
    if (!stock_v2_import_supported($goods['type'])) {
        Output::error('当前商品类型暂不支持批量导入，请使用添加库存或编辑功能处理');
    }

    $sku = ($goods['is_sku'] == 'y' && $sku !== '' && $sku !== '0') ? $sku : '0';
    $skuEsc = addslashes($sku);
    $batchNoEsc = addslashes($batch_no);
    $isOnceType = ($goods['type'] === 'once');

    // 去重模式
    $failed  = [];
    $toInsert = $clean;
    $fileDupCount = 0;

    if ($dedup) {
        // 文件内去重
        $seen = [];
        $uniq = [];
        foreach ($clean as $c) {
            if (isset($seen[$c])) { $fileDupCount++; continue; }
            $seen[$c] = 1;
            $uniq[] = $c;
        }
        $toInsert = $uniq;

        // 数据库内去重
        if (!empty($toInsert)) {
            $chunks = array_chunk($toInsert, 500);
            $dbDup = [];
            foreach ($chunks as $chunk) {
                $esc = array_map(function ($x) { return "'" . addslashes($x) . "'"; }, $chunk);
                $inList = implode(',', $esc);
                if ($isOnceType) {
                    $rows = $db->fetch_all("SELECT content FROM {$db_prefix}goods_once WHERE goods_id = {$goods_id} AND sku = '{$skuEsc}' AND content IN ({$inList})");
                } else {
                    $rows = $db->fetch_all("SELECT content FROM {$db_prefix}stock WHERE goods_id = {$goods_id} AND sku = '{$skuEsc}' AND content IN ({$inList})");
                }
                foreach ($rows as $r) $dbDup[$r['content']] = 1;
            }
            if (!empty($dbDup)) {
                $keep = [];
                foreach ($toInsert as $c) {
                    if (isset($dbDup[$c])) {
                        $failed[] = ['content' => $c, 'reason' => '数据库已存在该卡密'];
                    } else {
                        $keep[] = $c;
                    }
                }
                $toInsert = $keep;
            }
        }
    }

    // 写入
    $inserted = 0;
    if (!empty($toInsert)) {
        $stockModel = new Stock_Model();
        $writeError = '';
        try {
            $db->beginTransaction();
            $ts = time();
            $batches = array_chunk($toInsert, 1000);
            foreach ($batches as $batch) {
                $values = [];
                foreach ($batch as $c) {
                    if ($isOnceType) {
                        $values[] = "({$goods_id}, '{$skuEsc}', '{$batchNoEsc}', '" . addslashes($c) . "', {$ts})";
                    } else {
                        $values[] = "({$goods_id}, '{$skuEsc}', '" . addslashes($c) . "', {$ts})";
                    }
                }
                try {
                    if ($isOnceType) {
                        $db->query("INSERT INTO {$db_prefix}goods_once (goods_id, sku, batch_no, content, create_time) VALUES " . implode(',', $values));
                    } else {
                        $db->query("INSERT INTO {$db_prefix}stock (goods_id, sku, content, create_time) VALUES " . implode(',', $values));
                    }
                    $inserted += count($batch);
                } catch (Exception $batchEx) {
                    foreach ($batch as $c) {
                        try {
                            stock_v2_import_insert_one($db, $db_prefix, $goods_id, $skuEsc, $batchNoEsc, $c, $ts, $isOnceType);
                            $inserted++;
                        } catch (Exception $singleEx) {
                            $failed[] = ['content' => $c, 'reason' => $singleEx->getMessage() ?: '写入失败'];
                            if ($writeError === '') {
                                $writeError = $singleEx->getMessage();
                            }
                        }
                    }
                }
            }

            // 同步 skus / goods 库存数字
            if ($goods['type'] == 'duli') {
                if ($goods['is_sku'] == 'y') {
                    $skuCnt = $db->once_fetch_array("SELECT COUNT(*) c FROM {$db_prefix}stock WHERE goods_id = {$goods_id} AND sku = '{$skuEsc}'");
                    $stockModel->updateSkuStock($goods_id, $sku, (int)$skuCnt['c']);
                } else {
                    $allCnt = $db->once_fetch_array("SELECT COUNT(*) c FROM {$db_prefix}stock WHERE goods_id = {$goods_id}");
                    $stockModel->updateSkuStock($goods_id, '0', (int)$allCnt['c']);
                }
                $totalCnt = $db->once_fetch_array("SELECT COUNT(*) c FROM {$db_prefix}stock WHERE goods_id = {$goods_id}");
                $stockModel->updateGoodsStock($goods_id, (int)$totalCnt['c']);
            } elseif ($goods['type'] == 'once') {
                if ($goods['is_sku'] == 'y') {
                    $skuCnt = $db->once_fetch_array("SELECT COUNT(*) c FROM {$db_prefix}goods_once WHERE goods_id = {$goods_id} AND sku = '{$skuEsc}' AND sale_time IS NULL");
                    $stockModel->updateSkuStock($goods_id, $sku, (int)$skuCnt['c']);
                } else {
                    $allCnt = $db->once_fetch_array("SELECT COUNT(*) c FROM {$db_prefix}goods_once WHERE goods_id = {$goods_id} AND sale_time IS NULL");
                    $stockModel->updateSkuStock($goods_id, '0', (int)$allCnt['c']);
                }
                $totalCnt = $db->once_fetch_array("SELECT COUNT(*) c FROM {$db_prefix}goods_once WHERE goods_id = {$goods_id} AND sale_time IS NULL");
                $stockModel->updateGoodsStock($goods_id, (int)$totalCnt['c']);
            }
            $db->commit();
            doAction('stock_added', $goods_id, $goods);
        } catch (Exception $e) {
            $db->rollback();
            Output::error(json_encode([
                'message' => '写入失败：' . $e->getMessage(),
                'failed' => array_slice($failed, 0, 100),
                'failed_total' => count($failed),
            ], JSON_UNESCAPED_UNICODE));
        }

        if ($inserted <= 0 && !empty($failed)) {
            Output::error(json_encode([
                'message' => '导入失败，失败卡密已在弹窗中列出' . ($writeError !== '' ? '：' . $writeError : ''),
                'failed' => array_slice($failed, 0, 100),
                'failed_total' => count($failed),
            ], JSON_UNESCAPED_UNICODE));
        }
    }

    Output::data([
        'total'          => count($clean),
        'inserted'       => $inserted,
        'file_dup'       => $fileDupCount,
        'db_dup'         => count($failed),
        'failed'         => $failed,
        'dedup'          => (int)$dedup,
    ], 0);
}

// 清空已使用（方案 D：按时间清理 dc_deliver 软删）
if ($action == 'clear_used_ajax') {
    if (!User::haveEditPermission()) Output::error('权限不足');
    Output::demoCheck();

    $goods_id = Input::postIntVar('goods_id', 0);
    $days     = Input::postIntVar('days', 180);
    if ($goods_id <= 0) Output::error('请先选择商品');
    if ($days < 1)      Output::error('清理天数不能少于 1 天');
    if ($days > 3650)   Output::error('清理天数不能超过 3650 天');

    $db        = Database::getInstance();
    $db_prefix = DB_PREFIX;

    $cutoff = time() - $days * 86400;
    $now    = time();
    $goods  = $db->once_fetch_array("SELECT id, type FROM {$db_prefix}goods WHERE id = {$goods_id}");
    if (!$goods) Output::error('商品不存在');
    adm_stock_block_docking_goods($goods_id);
    $usedCfg = stock_v2_used_source_table($goods['type']);
    $srcTbl  = $db_prefix . $usedCfg['table'];
    $where   = $usedCfg['goods_where'] === 's.goods_id'
        ? "s.goods_id = {$goods_id}"
        : "ol.goods_id = {$goods_id}";
    if (!empty($usedCfg['extra'])) {
        $where .= " AND {$usedCfg['extra']}";
    }
    $where .= " AND o.pay_time < {$cutoff}";

    // 统计受影响
    $idRows = $db->fetch_all("
        SELECT s.id
          FROM {$srcTbl} AS s
     LEFT JOIN {$db_prefix}order_list AS ol ON ol.id = {$usedCfg['join_key']}
     LEFT JOIN {$db_prefix}order AS o ON o.id = ol.order_id
         WHERE {$where}
    ");
    $ids = [];
    foreach ($idRows as $row) {
        $ids[] = (int)$row['id'];
    }
    $affected = count($ids);

    if ($affected == 0) {
        Output::data(['affected' => 0], 0);
    }

    $idList = implode(',', $ids);
    try {
        $db->beginTransaction();
        if ($usedCfg['table'] === 'deliver') {
            $db->query("UPDATE {$srcTbl} SET delete_time = {$now} WHERE id IN ({$idList})");
        } else {
            $db->query("DELETE FROM {$srcTbl} WHERE id IN ({$idList})");
        }
        $db->commit();
    } catch (Exception $e) {
        $db->rollback();
        Output::error('清理失败：' . $e->getMessage());
    }

    Output::data(['affected' => $affected], 0);
}

// 清空全部（该商品下所有未使用库存）
if ($action == 'clear_all_ajax') {
    if (!User::haveEditPermission()) Output::error('权限不足');
    Output::demoCheck();

    $goods_id = Input::postIntVar('goods_id', 0);
    if ($goods_id <= 0) Output::error('请先选择商品');

    $db        = Database::getInstance();
    $db_prefix = DB_PREFIX;

    $goods = $db->once_fetch_array("SELECT * FROM {$db_prefix}goods WHERE id = {$goods_id}");
    if (!$goods) Output::error('商品不存在');
    adm_stock_block_docking_goods($goods_id);
    $srcCfg = stock_v2_source_table($goods['type']);
    $srcTbl = $db_prefix . $srcCfg['table'];
    $where  = "goods_id = {$goods_id}";
    if (!empty($srcCfg['unused_where'])) {
        $where .= " AND {$srcCfg['unused_where']}";
    }

    try {
        $db->beginTransaction();
        $db->query("DELETE FROM {$srcTbl} WHERE {$where}");
        // 同步清零
        $db->query("UPDATE {$db_prefix}skus SET stock = 0 WHERE goods_id = {$goods_id}");
        $db->query("UPDATE {$db_prefix}goods SET stock = 0 WHERE id = {$goods_id}");
        $db->commit();
    } catch (Exception $e) {
        $db->rollback();
        Output::error('清空失败：' . $e->getMessage());
    }

    Output::ok();
}

// 导出未使用（txt，一行一卡密）
if ($action == 'export_unused') {
    if (!User::haveEditPermission()) { echo '权限不足'; exit; }

    $goods_id = Input::getIntVar('goods_id', 0);
    if ($goods_id <= 0) { echo '参数错误'; exit; }

    $db        = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $goods = $db->once_fetch_array("SELECT id, title, type FROM {$db_prefix}goods WHERE id = {$goods_id}");
    if (!$goods) { echo '商品不存在'; exit; }
    adm_stock_block_docking_goods($goods_id);
    $srcCfg = stock_v2_source_table($goods['type']);
    $srcTbl = $db_prefix . $srcCfg['table'];
    $where  = "goods_id = {$goods_id}";
    if (!empty($srcCfg['unused_where'])) {
        $where .= " AND {$srcCfg['unused_where']}";
    }

    $rows = $db->fetch_all("SELECT content FROM {$srcTbl} WHERE {$where} ORDER BY id ASC");

    $filename = '未使用卡密_' . preg_replace('/[^\w\-]+/u', '_', $goods['title']) . '_' . date('YmdHis') . '.txt';
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    foreach ($rows as $r) {
        echo $r['content'] . "\r\n";
    }
    exit;
}

// 导出已使用 / 全部（csv，含订单信息）
if ($action == 'export_used' || $action == 'export_all') {
    if (!User::haveEditPermission()) { echo '权限不足'; exit; }

    $goods_id = Input::getIntVar('goods_id', 0);
    if ($goods_id <= 0) { echo '参数错误'; exit; }

    $db        = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $goods = $db->once_fetch_array("SELECT id, title, type FROM {$db_prefix}goods WHERE id = {$goods_id}");
    if (!$goods) { echo '商品不存在'; exit; }
    adm_stock_block_docking_goods($goods_id);
    $usedCfg = stock_v2_used_source_table($goods['type']);
    $usedTbl = $db_prefix . $usedCfg['table'];

    $skuValues = $db->fetch_all("SELECT id, name FROM {$db_prefix}sku_value");
    $skuMap = [];
    foreach ($skuValues as $sv) $skuMap[$sv['id']] = $sv['name'];

    $resolveSku = function ($skuStr) use ($skuMap) {
        if (empty($skuStr) || $skuStr == '0') return '';
        $parts = explode('-', $skuStr);
        $names = [];
        foreach ($parts as $p) if (isset($skuMap[$p])) $names[] = $skuMap[$p];
        return implode(' / ', $names);
    };

    // 已使用数据
    $usedWhere = $usedCfg['goods_where'] === 's.goods_id'
        ? "s.goods_id = {$goods_id}"
        : "ol.goods_id = {$goods_id}";
    if (!empty($usedCfg['extra'])) {
        $usedWhere .= " AND {$usedCfg['extra']}";
    }

    $used = $db->fetch_all("
        SELECT s.content, {$usedCfg['time_field']} AS deliver_time,
               ol.sku,
               o.id AS order_id, o.out_trade_no, o.up_no, o.client_ip, o.pwd,
               o.pay_time, o.tel, o.email, o.user_id
          FROM {$usedTbl} AS s
     LEFT JOIN {$db_prefix}order_list AS ol ON ol.id = {$usedCfg['join_key']}
     LEFT JOIN {$db_prefix}order AS o ON o.id = ol.order_id
         WHERE {$usedWhere}
      ORDER BY s.id ASC
    ");

    $unused = [];
    if ($action == 'export_all') {
        $srcCfg = stock_v2_source_table($goods['type']);
        $srcTbl = $db_prefix . $srcCfg['table'];
        $unusedWhere = "goods_id = {$goods_id}";
        if (!empty($srcCfg['unused_where'])) {
            $unusedWhere .= " AND {$srcCfg['unused_where']}";
        }
        $unused = $db->fetch_all("SELECT content, sku, create_time FROM {$srcTbl} WHERE {$unusedWhere} ORDER BY id ASC");
    }

    $mode = ($action == 'export_all') ? '全部' : '已使用';
    $filename = $mode . '卡密_' . preg_replace('/[^\w\-]+/u', '_', $goods['title']) . '_' . date('YmdHis') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    fputcsv($out, ['卡密内容', '规格', '状态', '订单号', '外部订单号', '买家', 'IP', '提卡密码', '支付时间', '发货时间']);

    foreach ($used as $r) {
        $buyer = !empty($r['user_id']) ? ('用户#' . $r['user_id']) : (!empty($r['email']) ? $r['email'] : (!empty($r['tel']) ? $r['tel'] : '游客'));
        fputcsv($out, [
            (string)$r['content'],
            $resolveSku($r['sku']),
            '已使用',
            $r['order_id'],
            $r['out_trade_no'] ?: $r['up_no'],
            $buyer,
            $r['client_ip'],
            $r['pwd'],
            !empty($r['pay_time']) ? date('Y-m-d H:i:s', $r['pay_time']) : '',
            !empty($r['deliver_time']) ? date('Y-m-d H:i:s', $r['deliver_time']) : '',
        ]);
    }

    foreach ($unused as $r) {
        fputcsv($out, [
            (string)$r['content'],
            $resolveSku($r['sku']),
            '未使用',
            '', '', '', '', '',
            '',
            !empty($r['create_time']) ? date('Y-m-d H:i:s', $r['create_time']) : '',
        ]);
    }
    fclose($out);
    exit;
}