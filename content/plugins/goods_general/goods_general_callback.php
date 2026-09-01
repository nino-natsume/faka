<?php
defined('DC_ROOT') || exit('access denied!');

// 开启插件时执行该函数
function callback_init() {
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $tables = $db->listTables();
    if(!in_array($db_prefix . "goods_general", $tables)){
        $sql = <<<sql
CREATE TABLE `{$db_prefix}goods_general`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `goods_id` int(10) NOT NULL DEFAULT 0 COMMENT '商品ID',
  `sku` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '规格',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '卡密',
  `create_time` bigint(16) NULL DEFAULT NULL COMMENT '添加时间',
  `update_time` bigint(16) NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Compact;
sql;
        $db->query($sql);
        $sql = <<<sql
CREATE TABLE `{$db_prefix}goods_general_sale`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `goods_id` int(10) NOT NULL DEFAULT 0 COMMENT '商品ID',
  `order_list_id` int(10) NOT NULL DEFAULT 0 COMMENT '子订单ID',
  `sku` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '规格',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '卡密',
  `num` int(10) NOT NULL DEFAULT 0 COMMENT '购买数量',
  `create_time` bigint(16) NULL DEFAULT NULL COMMENT '添加时间',
  `update_time` bigint(16) NULL DEFAULT NULL COMMENT '编辑时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Compact;
sql;
        $db->query($sql);
    }
}

// 删除插件时执行该函数
function callback_rm() {
    $plugin_storage = Storage::getInstance('tips');
    $plugin_storage->deleteAllName('YES'); // 删除时清理插件的设置信息
}

// 更新插件时执行该函数
function callback_up() {
    // do something
    
}
