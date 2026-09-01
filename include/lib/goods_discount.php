<?php
/**
 * 商品营销优惠（三类叠加共存）
 *
 * dc_discount.type：
 *   1 = 每件优惠（amount 为每件减价，单位：元，内部存分）
 *   2 = 订单优惠（amount 为整单固定减价，单位：元，内部存分）
 *   3 = 订单折扣（amount 为折扣数，单位：折，10 折 = 不打折；9.5 折 = 95%）
 *
 * 三种类型独立生效，顺序：先按件优惠 → 得到小计后再减订单优惠 → 再按订单折扣打折
 */
if (!defined('DC_ROOT')) {
    exit('error');
}

class Goods_Discount
{
    /**
     * 确保 dc_discount.type 字段存在（老环境自动补）
     */
    public static function ensureSchema()
    {
        static $done = false;
        if ($done) return;
        $db = Database::getInstance();
        $pfx = DB_PREFIX;
        $chk = $db->query("SHOW COLUMNS FROM `{$pfx}discount` LIKE 'type'");
        if ($db->num_rows($chk) == 0) {
            $db->query("ALTER TABLE `{$pfx}discount` ADD `type` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '1=每件优惠(元) 2=订单优惠(元) 3=订单折扣(折,0-100)'");
        }
        $chkSku = $db->query("SHOW COLUMNS FROM `{$pfx}discount` LIKE 'sku'");
        if ($db->num_rows($chkSku) == 0) {
            $db->query("ALTER TABLE `{$pfx}discount` ADD `sku` VARCHAR(100) NOT NULL DEFAULT '0' AFTER `goods_id`");
        }
        // 商品表自定义优惠标题
        $chkTitle = $db->query("SHOW COLUMNS FROM `{$pfx}goods` LIKE 'discount_title'");
        if ($db->num_rows($chkTitle) == 0) {
            $db->query("ALTER TABLE `{$pfx}goods` ADD `discount_title` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '批量购买优惠自定义标题(空=批发优惠)'");
        }
        $done = true;
    }

    /**
     * 取商品的自定义优惠标题（空则回退到默认）
     */
    public static function titleOf($goods, $default = '批发优惠')
    {
        if (is_array($goods) && !empty($goods['discount_title'])) {
            return $goods['discount_title'];
        }
        return $default;
    }

    /**
     * 获取商品的优惠规则列表，按 (当前SKU优先，数量降序) 排好
     * @return array list of rows with keys: type, sku, quantity, amount
     */
    public static function fetchRules($goods_id, $sku_str)
    {
        self::ensureSchema();
        $db = Database::getInstance();
        $pfx = DB_PREFIX;
        $goods_id = (int)$goods_id;
        $sku_str = $db->escape_string($sku_str);
        $sql = "SELECT type, sku, quantity, amount FROM `{$pfx}discount`
                WHERE goods_id={$goods_id} AND (sku='{$sku_str}' OR sku='0')
                ORDER BY type ASC, sku DESC, quantity DESC";
        return $db->fetch_all($sql) ?: [];
    }

    /**
     * 根据规则列表挑选每类的最佳匹配（满足数量阈值、SKU专属优先）
     * @return array ['item_cents' => int, 'order_cents' => int, 'percent' => float (0-100, 100=不打折)]
     */
    public static function pickBest($rules, $quantity)
    {
        $qty = (int)$quantity;
        $res = ['item_cents' => 0, 'order_cents' => 0, 'percent' => 100.0];
        $foundByType = [1 => false, 2 => false, 3 => false];
        $foundSkuByType = [1 => false, 2 => false, 3 => false];
        foreach ($rules as $r) {
            $t = (int)$r['type'];
            if (!in_array($t, [1, 2, 3], true)) continue;
            if ($foundByType[$t] && $foundSkuByType[$t] && $r['sku'] == '0') continue;
            if ($foundByType[$t] && !$foundSkuByType[$t] && $r['sku'] != '0') {
                // 已有通用优惠，但现在遇到SKU专属的——允许覆盖（规则列表本身按 sku DESC 排序，理论上不会走这条）
            }
            if ($qty < (int)$r['quantity']) continue;
            // 命中
            if ($foundByType[$t]) continue; // 同类型只取第一个（数量降序后=满足条件的最大阈值）
            $amount = (float)$r['amount']; // 数据库里 type 1/2 存的是分；type 3 存的是折*10 整数（如 95 表示 9.5 折）
            if ($t == 1) {
                $res['item_cents'] = (int)round($amount);
            } elseif ($t == 2) {
                $res['order_cents'] = (int)round($amount);
            } elseif ($t == 3) {
                // amount 存的是折数 * 10（9.5 折 -> 95），转换为百分比：95 -> 95%
                $res['percent'] = max(0.0, min(100.0, (float)$amount));
            }
            $foundByType[$t] = true;
            if ($r['sku'] != '0') $foundSkuByType[$t] = true;
        }
        return $res;
    }

    /**
     * 计算订单最终价格（分）
     * @param int $unit_price_cents 单件基础价（分，已含会员等级/加价规则等运算结果）
     * @param int $quantity 数量
     * @param array $best pickBest() 返回值
     * @return array ['unit_price_after_item' => int, 'count_price' => int, 'discount_total' => int]
     */
    public static function apply($unit_price_cents, $quantity, $best)
    {
        $unit = (int)$unit_price_cents;
        $qty = (int)$quantity;
        $item = (int)$best['item_cents'];
        $order = (int)$best['order_cents'];
        $percent = (float)$best['percent'];

        $unit_after_item = max(0, $unit - $item);
        $subtotal = $unit_after_item * $qty;
        $subtotal = max(0, $subtotal - $order);
        $final = (int)round($subtotal * $percent / 100);
        if ($final < 0) $final = 0;
        $original = $unit * $qty;
        $discount_total = max(0, $original - $final);
        return [
            'unit_price_after_item' => $unit_after_item,
            'count_price' => $final,
            'discount_total' => $discount_total,
            'item_discount_total' => $item * $qty,
            'order_discount' => $order,
            'percent' => $percent,
        ];
    }
}
