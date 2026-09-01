<?php
/**
 * article sort model
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

class Sku_Model {

    private $db;
    private $table;
    private $table_blog;

    function __construct() {
        $this->table = DB_PREFIX . 'sort';
        $this->table_blog = DB_PREFIX . 'blog';
        $this->db = Database::getInstance();
    }

    function addGoodsType($data){
        $kItem = $dItem = [];
        foreach ($data as $key => $val) {
            $kItem[] = $key;
            $dItem[] = $val;
        }
        $field = implode(',', $kItem);
        $values = "'" . implode("','", $dItem) . "'";
        $this->db->query("INSERT INTO " . DB_PREFIX . "goods_type ($field) VALUES ($values)");
        return $this->db->insert_id();
    }

    function addSkuAttr($data){
        $kItem = $dItem = [];
        foreach ($data as $key => $val) {
            $kItem[] = $key;
            $dItem[] = $val;
        }
        $field = implode(',', $kItem);
        $values = "'" . implode("','", $dItem) . "'";
        $this->db->query("INSERT INTO " . DB_PREFIX . "sku_attr ($field) VALUES ($values)");
        return $this->db->insert_id();
    }



    function getSkus() {
        $db_prefix = DB_PREFIX;
        $sql = "SELECT t.*, a.title attr_title, a.id sku_attr_id from {$db_prefix}goods_type as t
        left join {$db_prefix}sku_attr as a on a.type_id = t.id and a.delete_time is null 
        where t.hide = 'n' and t.delete_time is null order by id asc" ;

        $rows = $this->db->fetch_all($sql);

        $result = [];
        foreach ($rows as $row) {
            $goodsTypeId = $row['id'];  // 主表ID作为分组键

            // 首次处理该 goods_type 时，初始化主表数据
            if (!isset($result[$goodsTypeId])) {
                $result[$goodsTypeId] = [
                    'id' => $row['id'],
                    'name' => $row['name'],  // 主表其他字段
                    'sku_attrs' => []  // 用于存放关联的sku_attr数组
                ];
            }

            // 若存在关联的 sku_attr 数据（非NULL），则添加到子数组
            if ($row['sku_attr_id'] !== null) {
                $result[$goodsTypeId]['sku_attrs'][] = [
                    'title' => $row['attr_title'],
                    // 可添加其他sku_attr字段
                ];
            }
        }

// 可选：重置数组索引（从0开始连续）
        $result = array_values($result);

        return $result;
    }

    function getCate($type_id) {
        $sql = "select * from " . DB_PREFIX . "goods_type where id=$type_id";
        $res = $this->db->query($sql);
        $row = $this->db->fetch_array($res);
        return $row;
    }

    function getDetail($type_id) {
        $sql = "SELECT a.id, a.title, v.id value_id, v.name from " . DB_PREFIX . "sku_attr as a
        left join " . DB_PREFIX . "sku_value v on a.id=v.attr_id and v.delete_time is null
         where a.type_id={$type_id} and a.delete_time is null";
        $query = $this->db->query($sql);
        $data = [];
        while ($row = $this->db->fetch_array($query)) {
            if(array_key_exists($row['id'], $data)){
                $data[$row['id']]['value'][] = [
                    'id' => $row['value_id'], 'name' => $row['name']
                ];

            }else{
                $data[$row['id']] = [
                    'id' => $row['id'],
                    'name' => $row['title'],
                    'value' => []
                ];
                if($row['value_id']){
                    $data[$row['id']]['value'][] = ['id' => $row['value_id'], 'name' => $row['name']];
                }
            }
        }
        return $data;
    }

    function deleteSkuAttr($id){
        $timestamp = time();
        $sql = "UPDATE " . DB_PREFIX . "sku_attr set delete_time={$timestamp} where id=$id";
        $this->db->query($sql);
    }

    function deleteSkuCate($id){
        $timestamp = time();
        $sql = "UPDATE " . DB_PREFIX . "goods_type set delete_time={$timestamp} where id=$id";
        $this->db->query($sql);
    }

    function deleteSkuValue($value_id){
        $timestamp = time();
        $sql = "UPDATE " . DB_PREFIX . "sku_value set delete_time={$timestamp} where id=$value_id";
        $this->db->query($sql);

        $sql = "select * from " . DB_PREFIX . "sku_value where id=$value_id";
        $res = $this->db->query($sql);
        $sku_value = $this->db->fetch_array($res);

        $sql = "select * from " . DB_PREFIX . "sku_attr where id={$sku_value['attr_id']}";
        $res = $this->db->query($sql);
        $sku_attr = $this->db->fetch_array($res);
        return $sku_attr['type_id'];
    }

    function editSkuValue($id, $content) {
        $this->db->query("update " . DB_PREFIX . "sku_value set name='{$content}' where id=$id");
    }

    function updateSkuAttr($id, $content) {
        $this->db->query("update " . DB_PREFIX . "sku_attr set title='{$content}' where id=$id");
    }

    function updateSku($id, $content) {
        $this->db->query("update " . DB_PREFIX . "goods_type set name='{$content}' where id=$id");
    }

    function addSkuValue($id, $content) {
        $sql = "INSERT INTO " . DB_PREFIX . "sku_value (`attr_id`, `name`) VALUES ($id, '{$content}')";
        $this->db->query($sql);
    }


//    -------------------------------------



    function updateSort($sortData, $sid) {
        $Item = [];
        foreach ($sortData as $key => $data) {
            $Item[] = "$key='$data'";
        }
        $upStr = implode(',', $Item);
        $this->db->query("update $this->table set $upStr where sid=$sid");
    }

    public function addSort($data) {
        $kItem = $dItem = [];
        foreach ($data as $key => $val) {
            $kItem[] = $key;
            $dItem[] = $val;
        }
        $field = implode(',', $kItem);
        $values = "'" . implode("','", $dItem) . "'";
        $this->db->query("INSERT INTO $this->table ($field) VALUES ($values)");
        return $this->db->insert_id();
    }

    function deleteSort($sid) {
        $this->db->query("update $this->table_blog set sortid=-1 where sortid=$sid");
        $this->db->query("update $this->table set pid=0 where pid=$sid");
        $this->db->query("DELETE FROM $this->table where sid=$sid");
    }

    function getOneSortById($sid) {
        $sql = "select * from $this->table where sid=$sid";
        $res = $this->db->query($sql);
        $row = $this->db->fetch_array($res);
        $sortData = [];
        if ($row) {
            $sortData = array(
                'sortname'     => htmlspecialchars(trim($row['sortname'])),
                'alias'        => $row['alias'],
                'pid'          => $row['pid'],
                'title_origin' => $row['title'],
                'title'        => htmlspecialchars(Sort::formatSortTitle($row['title'], $row['sortname'])),
                'kw'           => htmlspecialchars($row['kw']),
                'description'  => htmlspecialchars(trim($row['description'])),
                'template'     => !empty($row['template']) ? htmlspecialchars(trim($row['template'])) : 'log_list',
                'sortimg'      => htmlspecialchars(trim($row['sortimg'])),
            );
        }
        return $sortData;
    }

    function getSortByAlias($alias) {
        if (empty($alias)) {
            return [];
        }
        $alias = addslashes($alias);
        $res = $this->db->query("SELECT * FROM $this->table WHERE alias = '$alias'");
        $row = $this->db->fetch_array($res);
        return $row;
    }

    function getSortName($sid) {
        if ($sid > 0) {
            $res = $this->db->query("SELECT sortname FROM $this->table WHERE sid = $sid");
            $row = $this->db->fetch_array($res);
            $sortName = htmlspecialchars($row['sortname']);
        } else {
            $sortName = '未分类';
        }
        return $sortName;
    }
}
