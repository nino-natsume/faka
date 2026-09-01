<?php
/**
 * View control
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

class View {
    public static function getView($template, $ext = '.php') {
        global $stationData;
//        d($stationData);die;
        if($stationData['id'] == 0){
            $nonce_templet = isMobile() ? Option::get('nonce_templet_tel') : Option::get('nonce_templet');
            if ($nonce_templet === 'em_null_tpl' || empty($nonce_templet)) {
                emMsg('当前未启用任何模板，请登录后台启用模板。', DC_URL . 'admin/template.php');
            }
            if (!is_dir(TEMPLATE_PATH) || !checkTemplateBootstrap(TPLS_PATH, $nonce_templet) || !loadTemplateBootstrap(TPLS_PATH, $nonce_templet)) {
                emMsg('当前使用的模板已被删除或损坏，请登录后台更换其他模板。', DC_URL . 'admin/template.php');
            }
            return TEMPLATE_PATH . $template . $ext;
        }else{
            $nonce_templet = isMobile() ? $stationData['tel_tpl'] : $stationData['pc_tpl'];
            if ($nonce_templet === 'em_null_tpl' || empty($nonce_templet)) {
                $nonce_templet = 'default';
            }
            if (!checkTemplateBootstrap(TPLS_PATH, $nonce_templet) || !loadTemplateBootstrap(TPLS_PATH, $nonce_templet)) {
                emMsg('当前使用的模板已被删除或损坏，请更换其他模板。', DC_URL . 'user/station.php?action=setting_tpl');
            }
            return TPLS_PATH . $nonce_templet . '/' . $template . $ext;
        }

    }

    public static function getCommonView($template, $ext = '.php') {
        if (!is_dir(COMMON_TEMPLATE_PATH)) {
            emMsg('当前使用的模板已被删除或损坏，请登录后台更换其他模板。', DC_URL . 'admin/template.php');
        }
        return COMMON_TEMPLATE_PATH . $template . $ext;
    }

    public static function getBlogView($template, $ext = '.php') {
        $blogTpl = self::getCurrentBlogTemplateSlug();
        if ($blogTpl === 'em_null_tpl' || $blogTpl === '') {
            emMsg('当前未启用博客模板，请登录后台启用模板。', DC_URL . 'admin/template.php?tab=blog');
        }
        $safeBlogTpl = preg_replace('/^([\w-]+)$/i', '$1', $blogTpl);
        if ($safeBlogTpl !== $blogTpl || !is_dir(BLOG_TPLS_PATH . $safeBlogTpl . '/') || !checkTemplateBootstrap(BLOG_TPLS_PATH, $safeBlogTpl) || !loadTemplateBootstrap(BLOG_TPLS_PATH, $safeBlogTpl)) {
            $safeBlogTpl = 'default';
            if (!is_dir(BLOG_TPLS_PATH . $safeBlogTpl . '/') || !checkTemplateBootstrap(BLOG_TPLS_PATH, $safeBlogTpl) || !loadTemplateBootstrap(BLOG_TPLS_PATH, $safeBlogTpl)) {
                emMsg('当前使用的博客模板已被删除或损坏，请登录后台更换其他模板。', DC_URL . 'admin/template.php?tab=blog');
            }
        }
        $filePath = BLOG_TPLS_PATH . $safeBlogTpl . '/' . $template . $ext;
        if (!file_exists($filePath)) {
            emMsg('当前博客模板缺少必要文件，请更换其他模板。', DC_URL . 'admin/template.php?tab=blog');
        }
        return $filePath;
    }

    public static function getAdmView($template, $ext = '.php') {
        if (!is_dir(ADMIN_TEMPLATE_PATH)) {
            emMsg('后台模板已损坏', DC_URL);
        }
        return ADMIN_TEMPLATE_PATH . $template . $ext;
    }

    public static function getCurrentUserTemplateSlug()
    {
        global $stationData;
        if (function_exists('dcIsDefaultUserViewportMobileMode') && dcIsDefaultUserViewportMobileMode()) {
            return 'default';
        }
        if (!empty($stationData['id']) && (int)$stationData['id'] > 0) {
            $userTpl = trim((string)(isMobile() ? ($stationData['user_tpl_tel'] ?? '') : ($stationData['user_tpl'] ?? '')));
            if ($userTpl !== '' && $userTpl !== 'em_null_tpl') {
                return $userTpl;
            }
            return 'default';
        }
        return trim((string)(isMobile() ? Option::get('nonce_user_tpl_tel') : Option::get('nonce_user_tpl')));
    }

    public static function getCurrentBottomNavTemplateSlug()
    {
        global $stationData;
        if (!empty($stationData['id']) && (int)$stationData['id'] > 0) {
            $bottomNavTpl = trim((string)($stationData['bottom_nav_tpl'] ?? ''));
            if ($bottomNavTpl !== '') {
                return $bottomNavTpl;
            }
            return 'default';
        }
        return trim((string)Option::get('nonce_bottom_nav_tpl'));
    }

    public static function getCurrentBlogTemplateSlug()
    {
        $blogTpl = trim((string)(isMobile() ? Option::get('nonce_blog_tpl_tel') : Option::get('nonce_blog_tpl')));
        if ($blogTpl === '') {
            $blogTpl = trim((string)(Option::get('nonce_blog_tpl') ?: 'default'));
        }
        return $blogTpl;
    }

    public static function getUserView($template, $ext = '.php') {
        global $stationData;
        $userTpl = self::getCurrentUserTemplateSlug();
        if ($userTpl === '') {
            emMsg('当前未启用任何模板，请先启用模板。', !empty($stationData['id']) ? (DC_URL . 'user/station.php?action=setting_tpl') : (DC_URL . 'admin/template.php?tab=user'));
        }
        $safeUserTpl = preg_replace('/^([\w-]+)$/i', '$1', $userTpl);
        $tplPath = USER_TPLS_PATH . $safeUserTpl . '/';
        if ($safeUserTpl !== $userTpl || !is_dir($tplPath) || !checkTemplateBootstrap(USER_TPLS_PATH, $safeUserTpl) || !loadTemplateBootstrap(USER_TPLS_PATH, $safeUserTpl)) {
            emMsg('当前使用的模板已被删除或损坏，请更换其他模板。', !empty($stationData['id']) ? (DC_URL . 'user/station.php?action=setting_tpl') : (DC_URL . 'admin/template.php?tab=user'));
        }
        $filePath = $tplPath . $template . $ext;
        if (!file_exists($filePath) && isMobile() && substr($template, -7) === '_mobile') {
            $fallbackTemplate = substr($template, 0, -7);
            $fallbackFilePath = $tplPath . $fallbackTemplate . $ext;
            if (file_exists($fallbackFilePath)) {
                $filePath = $fallbackFilePath;
            }
        }
        if (!file_exists($filePath)) {
            emMsg('当前用户后台模板缺少必要文件，请更换其他模板。', !empty($stationData['id']) ? (DC_URL . 'user/station.php?action=setting_tpl') : (DC_URL . 'admin/template.php?tab=user'));
        }
        return $filePath;
    }

    public static function getBottomNavView($template = 'render', $ext = '.php') {
        $bottomNavTpl = self::getCurrentBottomNavTemplateSlug();
        // 处理明确关闭的情况
        if ($bottomNavTpl === 'em_null_tpl' || $bottomNavTpl === '') {
            return false;
        }
        $safeBottomNavTpl = preg_replace('/^([\w-]+)$/i', '$1', $bottomNavTpl);
        if ($safeBottomNavTpl !== $bottomNavTpl || !is_dir(BOTTOM_NAV_TPLS_PATH . $safeBottomNavTpl . '/') || !checkTemplateBootstrap(BOTTOM_NAV_TPLS_PATH, $safeBottomNavTpl) || !loadTemplateBootstrap(BOTTOM_NAV_TPLS_PATH, $safeBottomNavTpl)) {
            $safeBottomNavTpl = 'default';
            loadTemplateBootstrap(BOTTOM_NAV_TPLS_PATH, $safeBottomNavTpl);
        }
        $tplPath = BOTTOM_NAV_TPLS_PATH . $safeBottomNavTpl . '/';
        $filePath = $tplPath . $template . $ext;
        if (!file_exists($filePath)) {
            loadTemplateBootstrap(BOTTOM_NAV_TPLS_PATH, 'default');
            $filePath = BOTTOM_NAV_TPLS_PATH . 'default/' . $template . $ext;
        }
        return $filePath;
    }

    public static function isTplExist($template, $ext = '.php') {
        if (file_exists(TEMPLATE_PATH . $template . $ext)) {
            return true;
        }
        return false;
    }

    public static function output() {
        $content = ob_get_clean();
        ob_start();
        echo $content;
        ob_end_flush();
        exit;
    }

}
