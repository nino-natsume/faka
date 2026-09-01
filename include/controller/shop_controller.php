<?php


class Shop_Controller {

    private $db;
    private $db_prefix;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->db_prefix = DB_PREFIX;
    }

    /**
     * 获取所有商品分类
     */
    public function getAllCategory(){

        $q = Input::getStrVar('q');
        $where = "";
        if(!empty($q)){
            $where .= " and g.title like '%{$q}%'";
        }

        $sql = "
SELECT 
    s.sid, s.sortname, COUNT(g.id) AS goods_count, s.sortimg
FROM 
    {$this->db_prefix}sort s
LEFT JOIN 
    {$this->db_prefix}goods g ON s.sid = g.sort_id and g.delete_time is null and g.is_on_shelf = 1 {$where}
WHERE 
    s.type = 'goods'
GROUP BY  s.sid, s.sortname
ORDER BY 
    s.taxis desc, s.sid asc;";
//        echo $sql;die;

        $res = $this->db->fetch_all($sql);
//d($res);die;
        $sql = "select count(id) as goods_count from {$this->db_prefix}goods g where g.sort_id = -1 and g.delete_time is null and g.is_on_shelf = 1 {$where}";
        $noCategoryGoods = $this->db->once_fetch_array($sql);
        if($noCategoryGoods['goods_count'] > 0){
            $res[] = [
                'sid' => -1,
                'sortname' => '未分类',
                'goods_count' => $noCategoryGoods['goods_count'],
                'sortimg' => './content/common/img/wu.png',
                'pid' => 0,
            ];
        }
//        d($res);die;
        return $res;
    }

    public function getAllGoods($sort_id = 0){
        $q = Input::getStrVar('q');
        $where = "";
        if(!empty($q)){
            $where .= " and g.title like '%{$q}%'";
        }

        if($sort_id != 0){
            $where .= " and g.sort_id={$sort_id}";
        }

        // 确保 dc_goods 新字段存在
        Level_Price::ensureSchema();

        $sql = "SELECT 
            g.title, g.cover, g.des, g.type, g.id goods_id, g.des, g.sales, g.sort_id, g.id goods_id, 
            g.profit_rule_id as g_profit_rule_id, g.profit_ratio as g_profit_ratio, g.single_rule_id as g_single_rule_id,
            sku.sku, sku.guest_price, sku.user_price, sku.cost_price, g.stock stock_num, g.stock, 
            mp.member_level mp_level, mp.price mp_price
        FROM {$this->db_prefix}goods g
        LEFT JOIN {$this->db_prefix}skus sku ON sku.goods_id = g.id
        LEFT JOIN {$this->db_prefix}member_price mp ON mp.goods_id = g.id
        WHERE g.is_on_shelf = 1 AND g.delete_time IS NULL {$where}
        GROUP BY sku.sku, sku.goods_id, mp.member_level
        ORDER BY g.index_top DESC, g.sort_top DESC, g.sort_num DESC, g.id asc";

        $data = $this->db->fetch_all($sql);
        $goods = [];
        foreach($data as $val){
            if(isset($goods[$val['goods_id']])){
                $goods[$val['goods_id']]['mp'][$val['mp_level']] = $val['mp_price'];
            }else{
                $goods[$val['goods_id']] = $val;
                $goods[$val['goods_id']]['url'] = Url::goods($val['goods_id']);
                $goods[$val['goods_id']]['mp'][$val['mp_level']] = $val['mp_price'];
            }
        }

        foreach($goods as $key => $val){
            // 五层价格计算
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
            $price = Level_Price::calculate($skuRow, $goodsRow, (int)LEVEL);
            $goods[$key]['price'] = number_format($price / 100, 2);
        }

        doMultiAction('home_goods_list', $goods, $goods);

//        d($goods);die;
        return $goods;


    }

}
