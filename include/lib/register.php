<?php

// 强制刷新本文件的OPCache缓存（确保修改立即生效）
if (function_exists('opcache_invalidate')) @opcache_invalidate(__FILE__, true);

/**
 * Register 类 - 授权验证桩类（已移除授权检查）
 * 所有方法均返回"已授权/已注册"状态，系统不再进行任何远程授权验证。
 */

class Register {

    /**
     * 检查域名是否已授权（始终返回已授权）
     */
    public static function checkDomain($forceRefresh = false, $domain = '') {
        return [
            'authorized'        => true,
            'reg_type'          => 3, // 至尊会员
            'type_name'         => '至尊会员',
            'agent_qq'          => '',
            'agent_link'        => '',
            'expire_time'       => '2099-12-31',
            'license_key'       => '',
            'is_bound'          => true,
            'account_bound'     => true,  // 服务端账号已绑定
            'account_username'  => 'local_admin',  // 显示的账号名（脱敏后显示）
            'claim_register_url'=> '',  // 不需要注册/登录链接
            'claim_login_url'   => '',
            'msg'               => '',
        ];
    }

    /**
     * 是否已在本地注册（始终返回 true）
     */
    public static function isRegLocal() {
        return true;
    }

    /**
     * 是否在远程授权服务器注册（始终返回 true）
     */
    public static function isRegServer() {
        return true;
    }

    /**
     * 获取注册类型（始终返回 3 = 至尊会员）
     */
    public static function getRegType() {
        return 3;
    }

    /**
     * 激活域名授权（直接返回成功）
     */
    public static function activateDomain($licenseKey, $domain) {
        return ['success' => true, 'msg' => '授权激活成功'];
    }

    /**
     * 是否为演示站（始终返回 false）
     */
    public static function isDemoSite() {
        return false;
    }

    /**
     * 清除授权演示缓存（空操作）
     */
    public static function clearDemoCache() {
        // 无操作
    }

    /**
     * 获取授权中心URL（返回空字符串，不再需要）
     */
    public static function getAuthCenterUrl() {
        return '';
    }
}
