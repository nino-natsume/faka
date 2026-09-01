<?php
/**
 * article sort model
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

class Sort_Model {

    private $db;
    private $table;
    private $table_blog;

    function __construct() {
        $this->table = DB_PREFIX . 'sort';
        $this->table_blog = DB_PREFIX . 'blog';
        $this->table_goods = DB_PREFIX . 'goods';
        $this->table_station_sort = DB_PREFIX . 'station_sort';
        $this->table_station_goods = DB_PREFIX . 'station_goods';
        $this->db = Database::getInstance();
    }

    /**
     * 前台 - 获取全部商品分类
     */
    function getHomeAllGoodsSort(){
        global $stationData;
//        d($stationData);
        $field = "s.sid, s.sortname, COUNT(g.id) AS goods_count, s.sortimg, s.sorticon";
        $group_by = "group by s.sid";
        $order_by = "order by s.taxis desc, sid asc";
        $where = "where s.type='goods'";

        $sql = "select {$field} from {$this->table} s 
                LEFT JOIN {$this->table_goods} g ON s.sid = g.sort_id and g.delete_time is null and g.is_on_shelf = 1";
        $join = "";
        if($stationData['id'] == 0){
            $where .= " and s.station_id=0";
            $sql .= " and g.station_id=0";
        }else{
            if($stationData['master_sort'] == 1){ // 全部显示
                $where .= " and (s.station_id=0 or s.station_id={$stationData['id']})";
                $_mg = (int)($stationData['master_goods'] ?? 0);
                if($_mg == 2){
                    $sql .= " and (g.station_id={$stationData['id']})";
                } elseif($_mg == 3){
                    // mode 3 由下方独立 master_goods==3 处理
                } else {
                    // master_goods=0 或 1：显示主站+分站商品，过滤已下架
                    $sid = (int)$stationData['id'];
                    $join .= " left join {$this->table_station_goods} sg2 on sg2.goods_id=g.id and sg2.station_id={$sid}";
                    $sql .= " and ((g.station_id={$sid}) or (g.station_id=0 and (sg2.is_show is null or sg2.is_show='y')))";
                }
            }
            if($stationData['master_sort'] == 2){ // 全部隐藏
                $where .= " and s.station_id={$stationData['id']}";
            }
            if($stationData['master_sort'] == 3){ // 自定义
                $join .= " inner join {$this->table_station_sort} ss on ss.sort_id=s.sid and ss.is_show='y'";
            }

            if((int)($stationData['master_goods'] ?? 0) === 3){
                $sid = (int)$stationData['id'];
                $join .= " inner join {$this->table_station_goods} sg on sg.goods_id=g.id and sg.station_id={$sid} and sg.is_show='y'";
            }
        }
        $sql .= " {$join} {$where} {$group_by} {$order_by}";
//        echo $sql;
        $res = $this->db->fetch_all($sql);
        foreach($res as &$val){
            $val['sort_url'] = Url::sort($val['sid']);
        }
        return $res;
    }

    function getSorts($type) {
        if (!in_array($type, ['goods', 'blog'], true)) {
            $type = 'goods';
        }
        $sorts = [];
        $query = $this->db->query("SELECT * FROM $this->table where `type`='{$type}' ORDER BY taxis desc, sid asc");
        while ($row = $this->db->fetch_array($query)) {
            $sid = (int)$row['sid'];
            if ($type === 'blog') {
                $data = $this->db->once_fetch_array("SELECT COUNT(*) AS total FROM $this->table_blog WHERE sortid=$sid AND hide='n' AND checked='y' AND type='blog'");
            } else {
                $data = $this->db->once_fetch_array("SELECT COUNT(*) AS total FROM $this->table_goods WHERE sort_id=$sid AND delete_time IS NULL");
            }
            $logNum = (int)$data['total'];
            $sortData = array(
                'type' => $row['type'],
                'lognum'       => $logNum,
                'direct_lognum' => $logNum,
                'sortname'     => htmlspecialchars($row['sortname']),
                'alias'        => $row['alias'],
                'description'  => htmlspecialchars($row['description']),
                'kw'           => htmlspecialchars($row['kw']),
                'title_origin' => $row['title'],
                'title'        => htmlspecialchars(Sort::formatSortTitle($row['title'], $row['sortname'])),
                'sid'          => $sid,
                'taxis'        => (int)$row['taxis'],
                'pid'          => (int)$row['pid'],
                'template'     => htmlspecialchars($row['template']),
                'sortimg'      => htmlspecialchars($row['sortimg']),
                'sorticon'     => htmlspecialchars($row['sorticon'] ?? ''),
                'page_count'   => (int)($row['page_count'] ?? 0),
                'children'     => []
            );
            $sorts[$sid] = $sortData;
        }

        foreach ($sorts as $sid => $sortData) {
            $pid = (int)$sortData['pid'];
            if ($pid > 0 && isset($sorts[$pid])) {
                $sorts[$pid]['children'][] = $sid;
            }
        }

        $calcLognum = function ($sid, &$calcLognum, &$visited = []) use (&$sorts) {
            if (isset($visited[$sid])) {
                return (int)$sorts[$sid]['direct_lognum'];
            }
            $visited[$sid] = true;
            $total = (int)$sorts[$sid]['direct_lognum'];
            foreach ($sorts[$sid]['children'] as $childId) {
                if (isset($sorts[$childId])) {
                    $total += $calcLognum($childId, $calcLognum, $visited);
                }
            }
            $sorts[$sid]['lognum'] = $total;
            unset($visited[$sid]);
            return $total;
        };
        foreach (array_keys($sorts) as $sid) {
            $visited = [];
            $calcLognum($sid, $calcLognum, $visited);
        }
        return $sorts;
    }

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
        $sid = (int)$sid;
        if ($sid <= 0) {
            return;
        }
        $this->db->query("update $this->table_goods set sort_id=-1 where sort_id={$sid}");
        $this->db->query("update $this->table_blog set sortid=-1 where sortid={$sid}");
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
                'type'         => $row['type'],
                'alias'        => $row['alias'],
                'pid'          => $row['pid'],
                'title_origin' => $row['title'],
                'title'        => htmlspecialchars(Sort::formatSortTitle($row['title'], $row['sortname'])),
                'kw'           => htmlspecialchars($row['kw']),
                'description'  => htmlspecialchars(trim($row['description'])),
                'template'     => !empty($row['template']) ? htmlspecialchars(trim($row['template'])) : 'log_list',
                'sortimg'      => htmlspecialchars(trim($row['sortimg'])),
                'sorticon'     => htmlspecialchars(trim($row['sorticon'] ?? '')),
                'page_count'   => (int)($row['page_count'] ?? 0),
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
