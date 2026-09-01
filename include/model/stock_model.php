<?php
/**
 * article and page model
 *
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

class Stock_Model {

    private $db;
    private $Parsedown;
    private $table;
    private $table_user;
    private $table_sort;
    private $db_prefix;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->db_prefix = DB_PREFIX;
        $this->table = DB_PREFIX . 'stock';
        $this->table_user = DB_PREFIX . 'user';
        $this->table_sort = DB_PREFIX . 'sort';
        $this->Parsedown = new Parsedown();
        $this->Parsedown->setBreaksEnabled(true); //automatic line wrapping
    }

    /**
     * 删除库存记录
     */
    public function deleteStock($where){
        $sql = "DELETE FROM $this->table where $where";
        $this->db->query($sql);
    }

    /**
     * 创建主订单
     */
    public function addOrder($productData) {
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
     * 添加库存
     */
    public function addStock($goods_id, $sku, $content) {

        $timestamp = time();
//        echo "INSERT INTO $this->table (goods_id, sku, content, create_time) VALUES ({$goods_id}, '{$sku}', '{$content}', '{$timestamp}')";die;
        $this->db->query("INSERT INTO $this->table (goods_id, sku, content, create_time) VALUES ({$goods_id}, '{$sku}', '{$content}', '{$timestamp}')");
    }

    public function updateStock($update, $goods_id, $sku) {
        $sets = [];
        foreach ($update as $key => $val) {
            $sets[] = "`$key` = '$val'";
        }
        $sql = "UPDATE " . DB_PREFIX . "stock SET " . implode(',', $sets) . " WHERE goods_id = $goods_id and sku='{$sku}'";
        return $this->db->query($sql);
    }
    public function isStock($goods_id, $sku){
        $sql = "SELECT * FROM " . DB_PREFIX . "stock where goods_id=$goods_id and sku='{$sku}'";
        $stock = $this->db->once_fetch_array($sql);
        if($stock){
            return $stock;
        }else{
            return false;
        }
    }

    /**
     * 获取产品sku信息
     */
    public function getSku($goods_id, $sku){
        $sql = "SELECT * FROM " . DB_PREFIX . "goods_sku where goods_id={$goods_id} and specification='{$sku}'";
//        echo $sql;die;
        $res = $this->db->query($sql);
        $row = $this->db->fetch_array($res);
        $row['price'] /= 100;
        return $row;
    }

    /**
     * 获取sku的规格属性
     */
    public function getSpecification($specification_ids){
        if (empty($specification_ids)) {
            return false;
        }
        $data = [];
        foreach($specification_ids as $val){
            $sql = "SELECT * FROM " . DB_PREFIX . "sku_attr where id={$val}";
            $res = $this->db->query($sql);
            $row = $this->db->fetch_array($res);
            $data[] = $row;
        }
//        d($data);
        return $data;
    }

    /**
     * 获取sku的规格属性值
     */
    public function getSpecificationValue($specification) {
        if (empty($specification)) {
            return false;
        }
        $specification = explode('-', $specification);
        $data = [];
        foreach($specification as $val){
            $sql = "SELECT * FROM " . DB_PREFIX . "sku_value where id={$val}";
            $res = $this->db->query($sql);
            $row = $this->db->fetch_array($res);
            $data[] = $row;
        }
//        d($data);
        return $data;
    }

    /**
     * 获取商品某个规格的库存数量
     */
    public function getSkuStock($goods_id, $sku){
        $res = $this->db->once_fetch_array("select stock from {$this->db_prefix}skus where sku='{$sku}' and goods_id={$goods_id}");
        return $res['stock'];
    }
    /**
     * 获取商品下的全部库存
     */
    public function getStockCount($goods_id){
        $goods = $this->db->once_fetch_array("select * from {$this->db_prefix}goods where id = {$goods_id}");
        if($goods['type'] == 'duli'){
            $res = $this->db->once_fetch_array("select count(id) stock_count from {$this->db_prefix}stock where goods_id={$goods_id}");
        }else{
            $res = $this->db->once_fetch_array("select sum(stock) stock_count from {$this->db_prefix}skus where goods_id={$goods_id}");
        }
        return $res['stock_count'];
    }
    /**
     * 更新sku的库存数量
     */
    public function updateSkuStock($goods_id, $sku, $count){
        $this->db->query("update {$this->db_prefix}skus set stock = {$count} where goods_id={$goods_id} and sku='{$sku}'");
    }
    /**
     * 更新商品的库存数量
     */
    public function updateGoodsStock($goods_id, $count){
        $this->db->query("update {$this->db_prefix}goods set stock = {$count} where id={$goods_id}");
    }
    /**
     * 更新库存内容
     */
    public function updateStockContent($stock_id, $content){
        $this->db->query("update {$this->db_prefix}stock set content = '{$content}' where id={$stock_id}");
    }
}
