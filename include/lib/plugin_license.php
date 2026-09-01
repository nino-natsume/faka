<?php

/**
 * PluginLicense 类 - 插件/模板授权验证桩类（已移除授权检查）
 * 所有验证方法始终返回 true/valid，系统不再对插件和模板进行授权验证。
 */

class PluginLicense {

    /**
     * 验证插件/模板授权（始终返回 true）
     */
    public static function verify($slug, $forceRefresh = false) {
        return true;
    }

    /**
     * 获取授权状态（始终返回 valid）
     */
    public static function getStatus($slug) {
        return 'valid';
    }

    /**
     * 获取购买类型（始终返回 buyout = 买断）
     */
    public static function getBuyType($slug) {
        return 'buyout';
    }

    /**
     * 批量验证授权（空操作，始终通过）
     */
    public static function batchVerify($slugs, $domain = '') {
        // 无操作
    }

    /**
     * 批量预热授权缓存（空操作）
     */
    public static function preWarm($plugins) {
        // 无操作
    }

    /**
     * 同步所有授权状态（空操作）
     */
    public static function syncAll() {
        return ['count' => 0, 'updated' => 0];
    }

    /**
     * 显示到期提示（空操作，不再提示）
     */
    public static function showExpiredNotice($slug, $name = '', $type = 'plugin') {
        // 无操作
    }
}
