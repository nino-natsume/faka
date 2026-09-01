<?php
/**
 * article and page model
 *
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

class Goods_Model {

    private $db;
    private $Parsedown;
    private $table;
    private $table_user;
    private $table_sort;
    private $table_station_goods;
    private $table_station_sort;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->table = DB_PREFIX . 'goods';
        $this->table_user = DB_PREFIX . 'user';
        $this->table_sort = DB_PREFIX . 'sort';
        $this->table_skus = DB_PREFIX . 'skus';
        $this->table_station_sort = DB_PREFIX . 'station_sort';
        $this->table_station_goods = DB_PREFIX . 'station_goods';
        $this->table_member_price = DB_PREFIX . 'member_price';
        $this->Parsedown = new Parsedown();
        $this->Parsedown->setBreaksEnabled(true); //automatic line wrapping
    }

    /**
     * 老环境自动补齐 dc_goods 字段（gallery 多图列）
     */
    public static function ensureSchema() {
        static $checked = false;
        if ($checked) return;
        $checked = true;
        $db = Database::getInstance();
        $table = DB_PREFIX . 'goods';
        $r = $db->query("SHOW COLUMNS FROM `{$table}` LIKE 'gallery'");
        if ($db->num_rows($r) == 0) {
            $db->query("ALTER TABLE `{$table}` ADD `gallery` TEXT NULL COMMENT '商品图集(JSON数组)' AFTER `cover`");
        }
        $r2 = $db->query("SHOW COLUMNS FROM `{$table}` LIKE 'allow_dock'");
        if ($db->num_rows($r2) == 0) {
            $db->query("ALTER TABLE `{$table}` ADD `allow_dock` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '是否允许此商品被对接' AFTER `is_on_shelf`");
        }
    }

    /**
     * 解析 gallery 字段为 URL 数组，确保首张为 cover
     * @param array $goods 必含 cover、gallery 两个键
     * @return array 图片URL数组（首张为主图）
     */
    public static function parseGallery($goods) {
        $cover = isset($goods['cover']) ? trim((string)$goods['cover']) : '';
        $raw = isset($goods['gallery']) ? (string)$goods['gallery'] : '';
        $list = [];
        if ($raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                foreach ($decoded as $u) {
                    $u = trim((string)$u);
                    if ($u !== '' && !in_array($u, $list, true)) $list[] = $u;
                }
            }
        }
        if ($cover !== '') {
            if (!in_array($cover, $list, true)) {
                array_unshift($list, $cover);
            } elseif ($list[0] !== $cover) {
                // cover 应当作为首张
                $list = array_values(array_diff($list, [$cover]));
                array_unshift($list, $cover);
            }
        }
        return $list;
    }

    public static function getSkuOrderSql($alias = 'sku') {
        $field = $alias . '.sku';
        return "CAST(SUBSTRING_INDEX({$field}, '-', 1) AS UNSIGNED) ASC, CAST(SUBSTRING_INDEX(SUBSTRING_INDEX({$field}, '-', 2), '-', -1) AS UNSIGNED) ASC, CAST(SUBSTRING_INDEX(SUBSTRING_INDEX({$field}, '-', 3), '-', -1) AS UNSIGNED) ASC, CAST(SUBSTRING_INDEX(SUBSTRING_INDEX({$field}, '-', 4), '-', -1) AS UNSIGNED) ASC, {$field} ASC";
    }

    private static function compareSkuOrder($a, $b) {
        $aSku = isset($a['sku']) ? (string)$a['sku'] : '0';
        $bSku = isset($b['sku']) ? (string)$b['sku'] : '0';
        $aParts = array_map('intval', explode('-', $aSku));
        $bParts = array_map('intval', explode('-', $bSku));
        $max = max(count($aParts), count($bParts));
        for ($i = 0; $i < $max; $i++) {
            $av = isset($aParts[$i]) ? $aParts[$i] : -1;
            $bv = isset($bParts[$i]) ? $bParts[$i] : -1;
            if ($av == $bv) {
                continue;
            }
            return $av < $bv ? -1 : 1;
        }
        return strcmp($aSku, $bSku);
    }

    private static function sortSkusByDefaultOrder(&$skus) {
        if (!is_array($skus) || count($skus) < 2) {
            return;
        }
        usort($skus, function($a, $b) {
            return self::compareSkuOrder($a, $b);
        });
    }

    /**
     * create product
     */
    public function addProduct($productData) {
        $kItem = $dItem = [];
        foreach ($productData as $key => $data) {
            $kItem[] = $key;
            $dItem[] = $data;
        }
        $field = implode(',', $kItem);
        $values = "'" . implode("','", $dItem) . "'";
        $this->db->query("INSERT INTO $this->table ($field) VALUES ($values)");
        return $this->db->insert_id();
    }

    /**
     * update article
     */
    public function updateProduct($logData, $goods_id, $uid = UID) {
        $Item = [];
        foreach ($logData as $key => $data) {
            $Item[] = "$key='$data'";
        }
        $upStr = implode(',', $Item);
        $sql = "UPDATE $this->table SET $upStr WHERE id=$goods_id";
//        echo $sql;die;
        $this->db->query($sql);
    }

    public function getCount() {
        $sql = "SELECT count(*) as num FROM $this->table WHERE type='goods' and delete_time is null";
        $res = $this->db->once_fetch_array($sql);
        return $res['num'];
    }

    /**
     * Gets the number of articles for the specified condition
     *
     * @param int $spot 0:homepage 1:admin
     * @param string $hide
     * @param string $condition
     * @param string $type
     * @return int
     */
    public function getGoodsNum($condition = '') {

        $condition = $condition ? " and $condition" : '';
        $data = $this->db->once_fetch_array("SELECT COUNT(*) AS total FROM $this->table where delete_time is null $condition");
        return $data['total'];
    }

    public function getPostCountByUid($uid, $time = 0) {
        $date = '';
        if ($time) {
            $date = "and date > $time";
        }

        $data = $this->db->once_fetch_array("SELECT COUNT(*) AS total FROM $this->table WHERE type='blog' and author=$uid $date");
        return $data['total'];
    }

    public function getOneGoodsForAdmin($goods_id) {

        $sql = "select * from {$this->table} where id={$goods_id} limit 1";
        $goods = $this->db->once_fetch_array($sql);
        $sql = "select * from " . DB_PREFIX . "skus where goods_id={$goods_id}";
        $skus = $this->db->fetch_all($sql);
//        d($skus);die;
        foreach($skus as $key => $val){
            $sql = "select * from " . DB_PREFIX . "member_price where goods_id={$goods_id} and sku='" . $val['sku'] . "'";
            $member_prices = $this->db->fetch_all($sql);
            $skus[$key]['member_prices'] = $member_prices;
        }
        $goods['skus'] = $skus;

        if ($goods) {

            $goods['skus_json'] = json_encode($goods['skus']);

            $goods['title'] = htmlspecialchars($goods['title']);
            $goods['content'] = htmlspecialchars($goods['content']);
            $goods['password'] = htmlspecialchars($goods['password']);
            $goods['template'] = !empty($goods['template']) ? htmlspecialchars(trim($goods['template'])) : 'page';
            return $goods;
        }
        return false;
    }

    public function getGoodsSkusForAdmin($goods_id){

        $sql = "SELECT * FROM $this->table WHERE id=$goods_id";
        $res = $this->db->query($sql);
        $goods = $this->db->fetch_array($res);


        $sql = "SELECT * FROM `" . DB_PREFIX . "skus` where goods_id={$goods_id}";
        $result = $this->db->query($sql);
        $data = [];



        while ($row = $this->db->fetch_array($result)) {
            if($goods['is_sku'] == 'y'){
                $data["skus[{$row['sku']}][guest_price]"] = $row['guest_price'] / 100;
                // 固定价/划线价：存 0 表示未设置，回显留空，避免用户误以为 "0 元"
                $data["skus[{$row['sku']}][user_price]"] = empty($row['user_price']) ? '' : ($row['user_price'] / 100);
                $data["skus[{$row['sku']}][market_price]"] = empty($row['market_price']) ? '' : ($row['market_price'] / 100);
                $data["skus[{$row['sku']}][cost_price]"] = $row['cost_price'] / 100;

                $sql = "SELECT * FROM `" . DB_PREFIX . "member_price` where goods_id={$row['goods_id']}";
                $res = $this->db->query($sql);
                while ($r = $this->db->fetch_array($res)) {
                    if($row['sku'] == $r['sku']){
                        $data["skus[{$row['sku']}][member_{$r['member_level']}]"] = $r['price'] / 100;
                    }

                }

            }else{
                $data['guest_price'] = $row['guest_price'] / 100;
                $data['user_price'] = $row['user_price'] / 100;
                $data['market_price'] = $row['market_price'] / 100;
                $data['cost_price'] = $row['cost_price'] / 100;
            }
        }

//        d($data);die;


        return $data;
    }

    public function getDetail($goods_id) {
        if (empty($goods_id)) {
            return false;
        }
        $sql = "SELECT t1.*, t2.sid, t2.sortname, t2.alias as sort_alias FROM $this->table t1 LEFT JOIN $this->table_sort t2 ON t1.sortid=t2.sid WHERE t1.id=$goods_id";
        $res = $this->db->query($sql);
        $row = $this->db->fetch_array($res);
        if ($row) {
            return $row;
        }
        return false;
    }

    public function getDetails($goods_ids) {
        if (empty($goods_ids) || !is_array($goods_ids)) {
            return false;
        }
        $goods_idsString = implode(',', $goods_ids);
        $sql = "SELECT t1.*, t2.sid, t2.sortname, t2.alias as sort_alias FROM $this->table t1 LEFT JOIN $this->table_sort t2 ON t1.sortid=t2.sid WHERE t1.id IN ($goods_idsString)";
        $res = $this->db->query($sql);
        $rows = array();
        while ($row = $this->db->fetch_array($res)) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * get single article
     * @param $goods_id
     * @return array|false
     */
    public function getOneGoodsForHome($goods_id) {
        $db_prefix = DB_PREFIX;
        $sql = "select * from {$db_prefix}goods where id={$goods_id} limit 1";
        $goods = $this->db->once_fetch_array($sql);

        if($goods['is_on_shelf'] == 0){
            emMsg('该商品已下架！');
        }

        $sql = "select * from {$db_prefix}skus where goods_id={$goods_id}";
        $skus = $this->db->fetch_all($sql);
        self::sortSkusByDefaultOrder($skus);
        $sql ="select * from {$db_prefix}member_price where goods_id={$goods_id}";
        $member_price = $this->db->fetch_all($sql);


        $goods['attach_user'] = json_decode($goods['attach_user'], true);
        // 兼容旧格式 {"key":"value"} → 新格式 [{name,placeholder,type,required,tip}]
        if (!empty($goods['attach_user']) && !isset($goods['attach_user'][0])) {
            $converted = [];
            foreach ($goods['attach_user'] as $k => $v) {
                $converted[] = ['name' => $k, 'placeholder' => $v, 'type' => 'string', 'required' => true, 'tip' => ''];
            }
            $goods['attach_user'] = $converted;
        }

        if($goods['is_sku'] == 'y'){
            $sku_value_ids = [];
            $sku_attr_ids = [];
            $goods['have_stock_skus'] = [];
            $goods['sku_all'] = [];
            foreach($skus as $key => $val){

                $ids = explode('-', $val['sku']);
                $sku_value_ids = array_merge($sku_value_ids, $ids);

                // 五层价格计算
                $price = Level_Price::calculate($val, $goods, (int)LEVEL) / 100;
                $val['price'] = $price;

                $goods['sku_all'][$val['sku']] = $val;
                $goods['sku_all'][$val['sku']]['stock'] = $val['stock'];
                if($val['stock'] > 0){
                    $goods['have_stock_skus'][$val['sku']] = $val;
                }
                if(!isset($goods['price']) && $price != -1){
                    $goods['price'] = $price;
                }
                if(!isset($goods['market_price'])){
                    $goods['market_price'] = $val['market_price'] / 100;
                }
            }


            $sku_value_ids = array_unique($sku_value_ids);
            $sql = "SELECT * FROM {$db_prefix}sku_value WHERE id IN (" . implode(',', $sku_value_ids) . ") ORDER BY id ASC";
            $sku_values = $this->db->fetch_all($sql);
            foreach($sku_values as $val){
                $sku_attr_ids[] = $val['attr_id'];
            }
            $sku_attr_ids = array_unique($sku_attr_ids);
            $sql = "SELECT * FROM {$db_prefix}sku_attr WHERE id IN (" . implode(',', $sku_attr_ids) . ") ORDER BY id ASC";
            $sku_attr = $this->db->fetch_all($sql);

            $goods['spec'] = [];
            $goods['spec_attr'] = [];
            foreach($sku_attr as $key => $val){
                $goods['spec'][$key] = [
                    'sku_attr_id' => $val['id'],
                    'title' => $val['title'],
                    'sku_values' => []
                ];


                foreach($sku_values as $v){
                    if($val['id'] == $v['attr_id']){
                        $goods['spec'][$key]['sku_values'][] = [
                            'id' => $v['id'],
                            'name' => $v['name']
                        ];
                        $goods['spec_attr'][$key][] = $v['id'];
                    }

                }
            }

            $goods['skus_all_json'] = json_encode($goods['sku_all']);
            $goods['spec_attr_json'] = json_encode($goods['spec_attr']);
            $goods['have_stock_skus_json'] = json_encode($goods['have_stock_skus']);
        }else{
            // 无规格：五层等级价计算
            $price = -1;
            foreach($skus as $val){
                if (class_exists('Level_Price')) {
                    $price = Level_Price::calculate($val, $goods, LEVEL) / 100;
                } else if (LEVEL == -1) {
                    $price = $val['guest_price'] / 100;
                } else {
                    foreach($member_price as $v){
                        if($v['member_level'] == LEVEL && $val['sku'] == $v['sku']){
                            $price = $v['price'] / 100;
                        }
                    }
                    if ($price == -1) $price = $val['user_price'] / 100;
                }
            }
            $goods['spec_attr_json'] = json_encode([]);

            $goods['have_stock_skus'][] = array_merge($val, ['price' => $price, 'stock' => $goods['stock']]);

            $goods['have_stock_skus_json'] = json_encode($goods['have_stock_skus']);
            $goods['skus_all_json'] = json_encode([]);
        }

        $goods['content'] = $this->Parsedown->text($goods['content']);

        // 分店自定义商品名称覆盖主站标题
        global $stationData;
        if (!empty($stationData['id']) && isset($goods['station_id']) && $goods['station_id'] == 0) {
            $sgRow = $this->db->once_fetch_array(
                "SELECT custom_name FROM {$db_prefix}station_goods WHERE goods_id={$goods_id} AND station_id=" . (int)$stationData['id'] . " LIMIT 1"
            );
            if (!empty($sgRow['custom_name'])) {
                $goods['title'] = $sgRow['custom_name'];
            }
        }

        return $goods;
    }

    public function getGoodsForAdmin($order = '', $page = 1) {
        $perpage_num = Option::get('admin_article_perpage_num');
        $start_limit = !empty($page) ? ($page - 1) * $perpage_num : 0;
        $limit = "LIMIT $start_limit, " . $perpage_num;


        $sql = "
SELECT 
    g.*, COALESCE(SUM(s.quantity), 0) AS stock
FROM 
    {$this->table} g
LEFT JOIN 
    " . DB_PREFIX . "skus sk ON g.id = sk.goods_id
LEFT JOIN 
    " . DB_PREFIX . "stock s ON sk.goods_id = s.goods_id AND sk.sku = s.sku
where g.delete_time is null
GROUP BY 
    g.id
ORDER BY id desc
$limit;
";

        $res = $this->db->query($sql);
        $logs = [];
        while ($row = $this->db->fetch_array($res)) {
            $row['timestamp'] = $row['create_time'];
            $row['create_time'] = date("Y-m-d H:i", $row['create_time']);
            $row['title'] = !empty($row['title']) ? htmlspecialchars($row['title']) : '无标题';
            $logs[] = $row;
        }
        return $logs;
    }

    public function getGoodsForHome($condition = '', $page = 1, $perPageNum = 10, $order_by = '') {
        $start_limit = !empty($page) ? ($page - 1) * $perPageNum : 0;
        $condition = empty($condition) ? "where g.delete_time is null" : " where " . $condition . " and g.delete_time is null";
        $limit = $perPageNum ? "LIMIT $start_limit, $perPageNum" : '';
        $skuOrderSql = self::getSkuOrderSql('sk_order');
        $sql = "
SELECT 
    g.*, 
    COALESCE(SUM(s.quantity), 0) AS stock,
    sk_min.sku,
    sk_min.guest_price,
    sk_min.cost_price,
    sk_min.user_price,
    sk_min.market_price,
    COALESCE(mp_sku.price, mp_default.price) AS member_price
FROM 
    {$this->table} g
LEFT JOIN " . DB_PREFIX . "skus sk_min ON sk_min.goods_id = g.id AND sk_min.sku = (
    SELECT sk_order.sku
    FROM " . DB_PREFIX . "skus sk_order
    WHERE sk_order.goods_id = g.id
    ORDER BY {$skuOrderSql}
    LIMIT 1
)
LEFT JOIN " . DB_PREFIX . "member_price mp_sku ON mp_sku.goods_id = g.id AND mp_sku.sku = sk_min.sku AND mp_sku.member_level = " . LEVEL . "
LEFT JOIN " . DB_PREFIX . "member_price mp_default ON mp_default.goods_id = g.id AND mp_default.sku = '0' AND mp_default.member_level = " . LEVEL . "
LEFT JOIN 
    " . DB_PREFIX . "stock s ON g.id = s.goods_id AND s.sku = sk_min.sku
$condition AND g.is_on_shelf = 1
GROUP BY 
    g.id
ORDER BY 
    {$order_by}
$limit;
";
//echo $sql;die;
        $res = $this->db->query($sql);
        $goods = [];
        while ($row = $this->db->fetch_array($res)) {
            $row['url'] = Url::goods($row['id']);
            $cookiePassword = isset($_COOKIE['em_logpwd_' . $row['id']]) ? addslashes(trim($_COOKIE['em_logpwd_' . $row['id']])) : '';
            if (!empty($row['password']) && $cookiePassword != $row['password']) {
                $row['excerpt'] = '<p>[该商品已加密，请点击标题输入密码访问]</p>';
            }
            $row['log_description'] = $this->Parsedown->text(empty($row['excerpt']) ? $row['content'] : $row['excerpt']);
            // 五层等级价计算（Level_Price 接收"分"为单位的 SKU 数据）
            if (class_exists('Level_Price')) {
                $skuInput = [
                    'sku' => isset($row['sku']) ? (string)$row['sku'] : '0',
                    'cost_price' => (int)$row['cost_price'],
                    'guest_price' => (int)$row['guest_price'],
                    'user_price' => (int)$row['user_price'],
                    'market_price' => (int)$row['market_price'],
                ];
                $row['price'] = Level_Price::calculate($skuInput, $row, LEVEL) / 100;
            }

            $row['market_price'] /= 100;
            $row['cost_price'] /= 100;
            $row['guest_price'] /= 100;
            $row['user_price'] /= 100;

            if (!isset($row['price'])) {
                if (LEVEL == -1) {
                    $row['price'] = $row['guest_price'];
                } else {
                    $row['price'] = empty($row['member_price'])
                        ? $row['user_price']
                        : ($row['member_price'] / 100);
                }
            }





            $goods[] = $row;
        }
//        d($goods);die;
        return $goods;
    }


    /**
     * 删除商品
     */
    public function deleteGoods($goods_id) {
        $timestamp = time();
        $this->db->query("UPDATE $this->table set delete_time={$timestamp} where id=$goods_id");
        return true;
    }
    /**
     * 获取所有主站商品的ID
     */
    public function getAllMasterGoodsId(){
        $sql = "select id from {$this->table} where station=0 and delete_time is null and is_on_shelf=1";
        $masterGoodsIds = $this->db->fetch_all($sql);
        return $masterGoodsIds;
    }

    /**
     * 构建前台商品列表的 WHERE/JOIN 条件（分页和计数复用）
     */
    private function _buildGoodsFilter($post) {
        global $stationData;
        $sort_id = isset($post['sort_id']) ? $post['sort_id'] : '';
        $keyword = isset($post['keyword']) ? $post['keyword'] : '';
        $where = '';
        if (!empty($keyword)) {
            $where .= " and g.title like '%{$keyword}%'";
        }
        if (!empty($sort_id) && $sort_id != 0) {
            $where .= " and g.sort_id={$sort_id}";
        }
        $join = ''; $field = ''; $premium_select = '';
        if ($stationData['id'] == 0) {
            $where .= " and g.station_id=0";
        } else {
            $sid = (int)$stationData['id'];
            $mg = (int)($stationData['master_goods'] ?? 0);
            if ($mg == 2) {
                $where .= " and g.station_id={$sid}";
            } elseif ($mg == 3) {
                $join .= " inner join {$this->table_station_goods} sg on sg.goods_id=g.id and sg.station_id={$sid} and sg.is_show='y'";
                $field .= ", sg.custom_name as sg_custom_name";
                $premium_select = ", IFNULL(sg.premium, 0.10) as premium";
            } else {
                // master_goods=0 或 1：显示主站商品+分站商品，过滤掉分站已下架的主站商品
                $where .= " and ((g.station_id={$sid}) or (g.station_id=0 and (sg.is_show is null or sg.is_show='y')))";
                $join .= " left join {$this->table_station_goods} sg on sg.goods_id=g.id and sg.station_id={$sid}";
                $field .= ", sg.custom_name as sg_custom_name";
                $premium_select = ", IFNULL(sg.premium, 0.10) as premium";
            }
            if ($stationData['master_sort'] == 2) {
                $join .= " inner join {$this->table_sort} s on s.sid=g.sort_id and s.station_id={$sid}";
            }
            if ($stationData['master_sort'] == 3) {
                $join .= " inner join {$this->table_station_sort} ss on ss.sort_id=g.sort_id and ss.is_show='y'";
            }
        }
        return compact('where', 'join', 'field', 'premium_select');
    }

    /**
     * 前台 - 获取商品总数（用于分页）
     */
    public function getHomeAllGoodsCount($post) {
        $f = $this->_buildGoodsFilter($post);
        $sql = "SELECT COUNT(DISTINCT g.id) as total FROM {$this->table} g {$f['join']} WHERE g.is_on_shelf = 1 AND g.delete_time IS NULL {$f['where']}";
        $res = $this->db->once_fetch_array($sql);
        return (int)$res['total'];
    }

    /**
     * 前台 - 获取全部商品（支持分页）
     * @param array $post  可包含 sort_id, keyword, page, per_page
     */
    function getHomeAllGoods($post){
        global $stationData;
        $f = $this->_buildGoodsFilter($post);
        $where = $f['where'];
        $join = $f['join'];
        $field = $f['field'];
        $premium_select = $f['premium_select'];

        // 确保 dc_goods 新字段存在（老环境自动补齐）
        Level_Price::ensureSchema();

        // 分页：先取当前页的商品ID，再查详情
        $page = isset($post['page']) ? max(1, (int)$post['page']) : 0;
        $per_page = isset($post['per_page']) ? (int)$post['per_page'] : 0;
        $id_filter = '';
        if ($page > 0 && $per_page > 0) {
            $offset = ($page - 1) * $per_page;
            $id_sql = "SELECT DISTINCT g.id FROM {$this->table} g {$join} WHERE g.is_on_shelf = 1 AND g.delete_time IS NULL {$where} ORDER BY g.index_top DESC, g.sort_top DESC, g.sort_num DESC, g.id ASC LIMIT {$per_page} OFFSET {$offset}";
            $id_rows = $this->db->fetch_all($id_sql);
            if (empty($id_rows)) return [];
            $ids = array_column($id_rows, 'id');
            $id_filter = ' AND g.id IN (' . implode(',', $ids) . ')';
        }

        $skuOrderSql = self::getSkuOrderSql('sku');
        $sql = "SELECT 
            g.title, g.unit_name, g.cover, g.des, g.type, g.id as goods_id, g.des as goods_des, g.sales, g.sort_id, g.stock,
            g.index_top, g.sort_top, g.sort_num, g.station_id, g.create_time, g.is_on_shelf, g.delete_time, g.is_sku,
            g.profit_rule_id as g_profit_rule_id, g.profit_ratio as g_profit_ratio, g.single_rule_id as g_single_rule_id,
            sku.goods_id as sku_goods_id, sku.sku, sku.guest_price, sku.market_price, sku.user_price, sku.cost_price, sku.stock as sku_stock,
            mp.goods_id as mp_goods_id, mp.sku as mp_sku, mp.member_level as mp_level, mp.price as mp_price
            {$premium_select} {$field}
        FROM {$this->table} g
        LEFT JOIN {$this->table_skus} sku ON sku.goods_id = g.id
        LEFT JOIN {$this->table_member_price} mp ON mp.goods_id = g.id AND mp.sku = sku.sku 
        {$join}
        WHERE g.is_on_shelf = 1 AND g.delete_time IS NULL {$where}{$id_filter}
        ORDER BY g.index_top DESC, g.sort_top DESC, g.sort_num DESC, g.id asc, {$skuOrderSql}, mp.member_level ASC;";

        $data = $this->db->fetch_all($sql);
//        d($data);die;
        $goods = [];
        foreach($data as $val){
            if(isset($goods[$val['goods_id']])){
                if (!is_null($val['mp_level'])) {
                    $goods[$val['goods_id']]['mp'][$val['mp_level']] = $val['mp_price'];
                }
            }else{
                // 分店自定义商品名称覆盖主站标题
                if (!empty($val['sg_custom_name'])) {
                    $val['title'] = $val['sg_custom_name'];
                }
                $goods[$val['goods_id']] = $val;
                $goods[$val['goods_id']]['url'] = Url::goods($val['goods_id']);
                if (!is_null($val['mp_level'])) {
                    $goods[$val['goods_id']]['mp'][$val['mp_level']] = $val['mp_price'];
                } else {
                    $goods[$val['goods_id']]['mp'] = [];
                }
            }
        }
//        d($goods);die;
        foreach($goods as $key => $val){
            // 分站加价比例（小数，如 0.10 = 10%），已整合在 Level_Price::calculate 内部
            $isMasterGoods = isset($val['station_id']) && $val['station_id'] == 0;
            $stationPremium = 0.0;
            if(isset($stationData['id']) && $stationData['id'] != 0 && $isMasterGoods && isset($val['premium'])){
                $stationPremium = (float)$val['premium'];
            }

            // 使用五层价格库计算
            $goodsRow = [
                'id' => $val['goods_id'],
                'profit_rule_id' => isset($val['g_profit_rule_id']) ? (int)$val['g_profit_rule_id'] : 0,
                'profit_ratio'   => isset($val['g_profit_ratio']) ? (float)$val['g_profit_ratio'] : 100,
                'single_rule_id' => isset($val['g_single_rule_id']) ? (int)$val['g_single_rule_id'] : 0,
            ];
            $skuRow = [
                'sku'         => isset($val['sku']) ? (string)$val['sku'] : '0',
                'cost_price'  => (int)$val['cost_price'],
                'user_price'  => (int)$val['user_price'],
                'guest_price' => (int)$val['guest_price'],
            ];
            $price = Level_Price::calculate($skuRow, $goodsRow, (int)LEVEL, $stationPremium);

            $goods[$key]['price'] = $price / 100;
            $goods[$key]['market_price'] = $goods[$key]['market_price'] / 100;
        }

        doMultiAction('home_goods_list', $goods, $goods);

        return $goods;
    }


}
