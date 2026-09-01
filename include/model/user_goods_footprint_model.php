<?php
/**
 * 用户商品浏览足迹模型
 */
defined('DC_ROOT') || exit('access denied!');

class User_Goods_Footprint_Model {
    private $db;
    private $table;
    private static $schemaReady = false;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->table = DB_PREFIX . 'user_goods_footprint';
        $this->ensureSchema();
    }

    public function ensureSchema() {
        if (self::$schemaReady) {
            return;
        }
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `user_id` int unsigned NOT NULL DEFAULT '0',
            `goods_id` int unsigned NOT NULL DEFAULT '0',
            `station_id` int unsigned NOT NULL DEFAULT '0',
            `view_count` int unsigned NOT NULL DEFAULT '1',
            `first_view_time` int unsigned NOT NULL DEFAULT '0',
            `last_view_time` int unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_user_goods_station` (`user_id`,`goods_id`,`station_id`),
            KEY `idx_user_last` (`user_id`,`last_view_time`),
            KEY `idx_goods` (`goods_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->db->query($sql);
        self::$schemaReady = true;
    }

    public function record($userId, $goodsId, $stationId = 0) {
        $userId = (int)$userId;
        $goodsId = (int)$goodsId;
        $stationId = (int)$stationId;
        if ($userId <= 0 || $goodsId <= 0) {
            return false;
        }
        $now = time();
        $sql = "INSERT INTO `{$this->table}` (`user_id`, `goods_id`, `station_id`, `view_count`, `first_view_time`, `last_view_time`) VALUES ({$userId}, {$goodsId}, {$stationId}, 1, {$now}, {$now})
            ON DUPLICATE KEY UPDATE `view_count` = `view_count` + 1, `last_view_time` = VALUES(`last_view_time`)";
        $this->db->query($sql);
        return true;
    }

    public function getCount($userId) {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return 0;
        }
        $row = $this->db->once_fetch_array("SELECT COUNT(*) AS total FROM `{$this->table}` WHERE `user_id`={$userId}");
        return (int)($row['total'] ?? 0);
    }

    public function getSummary($userId) {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return ['total' => 0, 'total_views' => 0, 'last_time' => 0];
        }
        $row = $this->db->once_fetch_array("SELECT COUNT(*) AS total, COALESCE(SUM(`view_count`),0) AS total_views, COALESCE(MAX(`last_view_time`),0) AS last_time FROM `{$this->table}` WHERE `user_id`={$userId}");
        return [
            'total' => (int)($row['total'] ?? 0),
            'total_views' => (int)($row['total_views'] ?? 0),
            'last_time' => (int)($row['last_time'] ?? 0),
        ];
    }

    public function getList($userId, $page = 1, $perPage = 20) {
        $userId = (int)$userId;
        $page = max(1, (int)$page);
        $perPage = max(1, min(100, (int)$perPage));
        if ($userId <= 0) {
            return [];
        }
        if (class_exists('Level_Price') && method_exists('Level_Price', 'ensureSchema')) {
            Level_Price::ensureSchema();
        }
        $start = ($page - 1) * $perPage;
        $level = defined('LEVEL') ? (int)LEVEL : -1;
        $goodsTable = DB_PREFIX . 'goods';
        $skuTable = DB_PREFIX . 'skus';
        $memberPriceTable = DB_PREFIX . 'member_price';
        $stationGoodsTable = DB_PREFIX . 'station_goods';
        $skuOrderSql = class_exists('Goods_Model') ? Goods_Model::getSkuOrderSql('sku_order') : 'sku_order.sku ASC';
        $sql = "SELECT
                f.`id` AS footprint_id, f.`user_id`, f.`goods_id`, f.`station_id` AS viewed_station_id,
                f.`view_count`, f.`first_view_time`, f.`last_view_time`,
                g.`id` AS gid, g.`title`, g.`cover`, g.`des`, g.`type`, g.`sales`, g.`stock`,
                g.`is_on_shelf`, g.`delete_time`, g.`station_id` AS goods_station_id,
                g.`profit_rule_id`, g.`profit_ratio`, g.`single_rule_id`,
                sku.`sku`, sku.`guest_price`, sku.`user_price`, sku.`market_price`, sku.`cost_price`,
                COALESCE(mp_sku.`price`, mp_default.`price`) AS member_price, sg.`custom_name`, sg.`premium`
            FROM `{$this->table}` f
            LEFT JOIN `{$goodsTable}` g ON g.`id` = f.`goods_id`
            LEFT JOIN `{$skuTable}` sku ON sku.`goods_id` = f.`goods_id` AND sku.`sku` = (
                SELECT sku_order.`sku` FROM `{$skuTable}` sku_order WHERE sku_order.`goods_id` = f.`goods_id` ORDER BY {$skuOrderSql} LIMIT 1
            )
            LEFT JOIN `{$memberPriceTable}` mp_sku ON mp_sku.`goods_id` = f.`goods_id` AND mp_sku.`sku` = sku.`sku` AND mp_sku.`member_level`={$level}
            LEFT JOIN `{$memberPriceTable}` mp_default ON mp_default.`goods_id` = f.`goods_id` AND mp_default.`sku` = '0' AND mp_default.`member_level`={$level}
            LEFT JOIN `{$stationGoodsTable}` sg ON sg.`goods_id` = f.`goods_id` AND sg.`station_id` = f.`station_id`
            WHERE f.`user_id`={$userId}
            ORDER BY f.`last_view_time` DESC, f.`id` DESC
            LIMIT {$start}, {$perPage}";
        $rows = $this->db->fetch_all($sql);
        foreach ($rows as &$row) {
            $row = $this->normalizeRow($row);
        }
        unset($row);
        return $rows;
    }

    private function normalizeRow($row) {
        $exists = !empty($row['gid']);
        $goodsId = (int)($row['goods_id'] ?? 0);
        $title = $exists ? (string)($row['title'] ?? '') : '商品已删除';
        if ($exists && !empty($row['custom_name']) && (int)($row['viewed_station_id'] ?? 0) > 0 && (int)($row['goods_station_id'] ?? 0) === 0) {
            $title = (string)$row['custom_name'];
        }
        $row['title'] = $title !== '' ? $title : '未命名商品';
        $row['available'] = $exists && (int)($row['is_on_shelf'] ?? 0) === 1 && empty($row['delete_time']);
        $row['url'] = $exists ? Url::goods($goodsId) : 'javascript:void(0);';
        $row['last_view_time_text'] = !empty($row['last_view_time']) ? date('Y-m-d H:i', (int)$row['last_view_time']) : '-';
        $row['first_view_time_text'] = !empty($row['first_view_time']) ? date('Y-m-d H:i', (int)$row['first_view_time']) : '-';
        $row['sales'] = (int)($row['sales'] ?? 0);
        $row['stock'] = (int)($row['stock'] ?? 0);
        $row['view_count'] = (int)($row['view_count'] ?? 0);
        $row['price'] = $this->calcPrice($row);
        $row['market_price'] = round(((float)($row['market_price'] ?? 0)) / 100, 2);
        return $row;
    }

    private function calcPrice($row) {
        if (empty($row['gid'])) {
            return 0.00;
        }
        $guest = (int)($row['guest_price'] ?? 0);
        $user = (int)($row['user_price'] ?? 0);
        $member = (int)($row['member_price'] ?? 0);
        $base = (defined('LEVEL') && (int)LEVEL === -1) ? $guest : ($member > 0 ? $member : $user);
        if (class_exists('Level_Price')) {
            $stationPremium = ((int)($row['viewed_station_id'] ?? 0) > 0 && (int)($row['goods_station_id'] ?? 0) === 0) ? (float)($row['premium'] ?? 0.10) : 0.0;
            $skuRow = [
                'sku' => isset($row['sku']) ? (string)$row['sku'] : '0',
                'cost_price' => (int)($row['cost_price'] ?? 0),
                'guest_price' => $guest,
                'user_price' => $user,
                'market_price' => (int)($row['market_price'] ?? 0),
            ];
            $goodsRow = [
                'id' => (int)($row['goods_id'] ?? 0),
                'profit_rule_id' => (int)($row['profit_rule_id'] ?? 0),
                'profit_ratio' => (float)($row['profit_ratio'] ?? 100),
                'single_rule_id' => (int)($row['single_rule_id'] ?? 0),
            ];
            $base = Level_Price::calculate($skuRow, $goodsRow, defined('LEVEL') ? (int)LEVEL : -1, $stationPremium);
        }
        return round($base / 100, 2);
    }

    public function deleteOne($userId, $footprintId) {
        $userId = (int)$userId;
        $footprintId = (int)$footprintId;
        if ($userId <= 0 || $footprintId <= 0) {
            return false;
        }
        $this->db->query("DELETE FROM `{$this->table}` WHERE `id`={$footprintId} AND `user_id`={$userId} LIMIT 1");
        return true;
    }

    public function clear($userId) {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return false;
        }
        $this->db->query("DELETE FROM `{$this->table}` WHERE `user_id`={$userId}");
        return true;
    }
}
