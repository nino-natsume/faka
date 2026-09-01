<?php
/**
 * 认证页（登录/注册/找回密码）读取后台模板主题配置并输出 CSS 变量
 * 用法: 在 <style> 标签内的 :root {} 之前 include 本文件，
 *       然后在 :root 里使用 <?= $_auth_primary ?> 等变量
 */
defined('DC_ROOT') || exit('access denied!');

if (!isset($_auth_theme_loaded)) {
    $_auth_theme_loaded = true;

    // 读取用户后台模板配置
    $_auth_ut = [];
    if (class_exists('TplOptions') && function_exists('userTplSettingKey')) {
        $_auth_ut = TplOptions::getInstance()->getTemplateOptions(userTplSettingKey('default'));
    }

    $_auth_primary = !empty($_auth_ut['theme_primary']) ? $_auth_ut['theme_primary'] : '#2196F3';

    // 计算 RGB 分量
    $_ap_hex = ltrim($_auth_primary, '#');
    if (strlen($_ap_hex) === 3) $_ap_hex = $_ap_hex[0].$_ap_hex[0].$_ap_hex[1].$_ap_hex[1].$_ap_hex[2].$_ap_hex[2];
    $_ap_r = hexdec(substr($_ap_hex, 0, 2));
    $_ap_g = hexdec(substr($_ap_hex, 2, 2));
    $_ap_b = hexdec(substr($_ap_hex, 4, 2));
    $_auth_primary_rgb = "$_ap_r,$_ap_g,$_ap_b";

    // 暗色变体（与 _adaptive_header.php 同算法 ×0.82）
    $_auth_primary_dark = '#' . str_pad(dechex(max(0, (int)($_ap_r * 0.82))), 2, '0', STR_PAD_LEFT)
                                . str_pad(dechex(max(0, (int)($_ap_g * 0.82))), 2, '0', STR_PAD_LEFT)
                                . str_pad(dechex(max(0, (int)($_ap_b * 0.82))), 2, '0', STR_PAD_LEFT);

    // shadow 用的 rgba 字符串（0.25 透明度）
    $_auth_shadow_25 = "rgba($_ap_r,$_ap_g,$_ap_b,.25)";
    $_auth_shadow_30 = "rgba($_ap_r,$_ap_g,$_ap_b,.3)";
    $_auth_shadow_35 = "rgba($_ap_r,$_ap_g,$_ap_b,.35)";

    // 移动端卡片渐变
    $_auth_mobile_gradient = !empty($_auth_ut['mobile_card_gradient']) ? $_auth_ut['mobile_card_gradient'] : 'linear-gradient(135deg, rgba(255,255,255,0.92), rgba(255,255,255,0.76))';
}
?>
