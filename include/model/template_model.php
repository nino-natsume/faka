<?php
/**
 * Template model

 */

class Template_Model {

    function getTemplates()
    {
        $nonce_template = Option::get('nonce_templet');

        $templates = [];
        $handle = @opendir(TPLS_PATH) or die('DCSHOP template path error!');
        while ($file = @readdir($handle)) {
            if ($file === '.' || $file === '..' || !is_dir(TPLS_PATH . $file) || !checkTemplateBootstrap(TPLS_PATH, $file)) {
                continue;
            }
            $mainFile = getTemplateBootstrapFile(TPLS_PATH, $file);
            $tplData = @file_get_contents($mainFile, false, null, 0, 2048);
            if ($tplData === false) {
                continue;
            }
            preg_match("/Template Name:(.*)/i", $tplData, $tplName);
            preg_match("/Template Url:(.*)/i", $tplData, $tplUrl);
            preg_match("/Version:(.*)/i", $tplData, $tplVersion);
            preg_match("/Author:(.*)/i", $tplData, $author);
            preg_match("/Description:(.*)/i", $tplData, $tplDes);
            preg_match("/Author Url:(.*)/i", $tplData, $authorUrl);
            $tplInfo = [
                'tplfile'    => $file,
                'tplname'    => !empty($tplName[1]) ? subString(strip_tags(trim($tplName[1])), 0, 16) : $file,
                'version'    => !empty($tplVersion[1]) ? subString(strip_tags(trim($tplVersion[1])), 0, 16) : '',
                'tplurl'     => !empty($tplUrl[1]) ? subString(strip_tags(trim($tplUrl[1])), 0, 75) : '',
                'tpldes'     => !empty($tplDes[1]) ? subString(strip_tags(trim($tplDes[1])), 0, 40) : '',
                'author'     => !empty($author[1]) ? subString(strip_tags(trim($author[1])), 0, 16) : '',
                'author_url' => !empty($authorUrl[1]) ? subString(strip_tags(trim($authorUrl[1])), 0, 75) : '',
            ];

            $previewPath = TPLS_PATH . $file . '/preview.jpg';
            $tplInfo['preview'] = file_exists($previewPath) ? (TPLS_URL . $file . '/preview.jpg') : './views/images/theme.png';
            $tplInfo['has_setting'] = hasTemplateSettingFile(TPLS_PATH, $file) ? 'y' : 'n';

            $templates[] = $tplInfo;
        }
//        ksort($templates);
        closedir($handle);
        return $templates;
    }

    function getStationTemplates($userData = null){
        $templates = [];
        $handle = @opendir(TPLS_PATH);
        if ($handle === false) {
            die('模板路径错误');
        }
        while ($file = @readdir($handle)) {
            if ($file === '.' || $file === '..' || !is_dir(TPLS_PATH . $file) || !checkTemplateBootstrap(TPLS_PATH, $file)) {
                continue;
            }
            $mainFile = getTemplateBootstrapFile(TPLS_PATH, $file);
            $tplData = @file_get_contents($mainFile, false, null, 0, 2048);
            if ($mainFile === false || $tplData === false) {
                continue;
            }
            preg_match("/Template Name:(.*)/i", $tplData, $tplName);
            preg_match("/Template Url:(.*)/i", $tplData, $tplUrl);
            preg_match("/Version:(.*)/i", $tplData, $tplVersion);
            preg_match("/Author:(.*)/i", $tplData, $author);
            preg_match("/Description:(.*)/i", $tplData, $tplDes);
            preg_match("/Author Url:(.*)/i", $tplData, $authorUrl);
            $tplInfo = [
                'tplfile'    => $file,
                'tplname'    => !empty($tplName[1]) ? subString(strip_tags(trim($tplName[1])), 0, 16) : $file,
                'version'    => !empty($tplVersion[1]) ? subString(strip_tags(trim($tplVersion[1])), 0, 16) : '',
                'tplurl'     => !empty($tplUrl[1]) ? subString(strip_tags(trim($tplUrl[1])), 0, 75) : '',
                'tpldes'     => !empty($tplDes[1]) ? subString(strip_tags(trim($tplDes[1])), 0, 40) : '',
                'author'     => !empty($author[1]) ? subString(strip_tags(trim($author[1])), 0, 16) : '',
                'author_url' => !empty($authorUrl[1]) ? subString(strip_tags(trim($authorUrl[1])), 0, 75) : '',
            ];

            $previewPath = TPLS_PATH . $file . '/preview.jpg';
            $tplInfo['preview'] = file_exists($previewPath) ? (DC_URL . 'content/templates/' . $file . '/preview.jpg') : './views/images/theme.png';
            $tplInfo['has_setting'] = hasTemplateSettingFile(TPLS_PATH, $file) ? 'y' : 'n';

            $templates[] = $tplInfo;
        }
        closedir($handle);
        return $templates;
    }

    function getBottomNavTemplates()
    {
        $templates = [];
        $path = BOTTOM_NAV_TPLS_PATH;
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
            return $templates;
        }
        $handle = @opendir($path);
        if ($handle === false) {
            return $templates;
        }
        while ($file = @readdir($handle)) {
            if ($file === '.' || $file === '..' || !is_dir($path . $file)) {
                continue;
            }
            if (!checkTemplateBootstrap($path, $file)) {
                continue;
            }
            $metaFile = getTemplateBootstrapFile($path, $file);
            $renderFile = $path . $file . '/render.php';
            if ($metaFile === false || !file_exists($metaFile) || !file_exists($renderFile)) {
                continue;
            }
            $tplData = @file_get_contents($metaFile, false, null, 0, 2048);
            if ($tplData === false) {
                continue;
            }
            preg_match("/Template\s*Name\s*:(.*)/i", $tplData, $tplName);
            preg_match("/Template\s*URL?\s*:(.*)/i", $tplData, $tplUrl);
            preg_match("/Version\s*:(.*)/i", $tplData, $tplVersion);
            preg_match("/Author\s*:(.*)/i", $tplData, $author);
            preg_match("/Description\s*:(.*)/i", $tplData, $tplDes);
            preg_match("/Author\s*Url\s*:(.*)/i", $tplData, $authorUrl);
            $tplInfo = [
                'tplfile'    => $file,
                'tplname'    => !empty($tplName[1]) ? subString(strip_tags(trim($tplName[1])), 0, 16) : $file,
                'version'    => !empty($tplVersion[1]) ? subString(strip_tags(trim($tplVersion[1])), 0, 16) : '',
                'tplurl'     => !empty($tplUrl[1]) ? subString(strip_tags(trim($tplUrl[1])), 0, 75) : '',
                'tpldes'     => !empty($tplDes[1]) ? subString(strip_tags(trim($tplDes[1])), 0, 40) : '',
                'author'     => !empty($author[1]) ? subString(strip_tags(trim($author[1])), 0, 16) : '',
                'author_url' => !empty($authorUrl[1]) ? subString(strip_tags(trim($authorUrl[1])), 0, 75) : '',
            ];
            $previewPath = $path . $file . '/preview.jpg';
            $tplInfo['preview'] = file_exists($previewPath) ? (DC_URL . 'content/bottom_nav_templates/' . $file . '/preview.jpg') : './views/images/theme.png';
            $tplInfo['has_setting'] = hasTemplateSettingFile($path, $file) ? 'y' : 'n';

            require_once DC_ROOT . '/include/lib/plugin_license.php';
            // 底部导航模板使用带类型前缀的授权缓存键，避免与其他模板同名串授权。
            $licenseKey = 'bottom_nav_template:' . $file;
            $tplInfo['license_status'] = PluginLicense::getStatus($licenseKey);
            $tplInfo['buy_type'] = PluginLicense::getBuyType($licenseKey);
            $tplInfo['expire_time'] = '';
            $licenseCache = self::_getLicenseCache($licenseKey);
            if ($licenseCache) {
                $tplInfo['expire_time'] = $licenseCache['expire_time'] ?? '';
            }

            $templates[] = $tplInfo;
        }
        closedir($handle);
        return $templates;
    }

    /**
     * 获取博客模板列表
     * 检测方式：{slug}/{slug}.php（与其他独立模板一致）
     */
    function getBlogTemplates()
    {
        $templates = [];
        $path = BLOG_TPLS_PATH;
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
            return $templates;
        }
        $handle = @opendir($path);
        if ($handle === false) {
            return $templates;
        }
        while ($file = @readdir($handle)) {
            if ($file === '.' || $file === '..' || !is_dir($path . $file)) {
                continue;
            }
            if (!checkTemplateBootstrap($path, $file)) {
                continue;
            }
            $mainFile = getTemplateBootstrapFile($path, $file);
            if ($mainFile === false || !file_exists($mainFile)) {
                continue;
            }
            $tplData = @file_get_contents($mainFile, false, null, 0, 2048);
            if ($tplData === false) {
                continue;
            }
            preg_match("/Template\s*Name\s*:(.*)/i", $tplData, $tplName);
            preg_match("/Template\s*URL?\s*:(.*)/i", $tplData, $tplUrl);
            preg_match("/Version\s*:(.*)/i", $tplData, $tplVersion);
            preg_match("/Author\s*:(.*)/i", $tplData, $author);
            preg_match("/Description\s*:(.*)/i", $tplData, $tplDes);
            preg_match("/Author\s*Url\s*:(.*)/i", $tplData, $authorUrl);
            $tplInfo = [
                'tplfile'    => $file,
                'tplname'    => !empty($tplName[1]) ? subString(strip_tags(trim($tplName[1])), 0, 16) : $file,
                'version'    => !empty($tplVersion[1]) ? subString(strip_tags(trim($tplVersion[1])), 0, 16) : '',
                'tplurl'     => !empty($tplUrl[1]) ? subString(strip_tags(trim($tplUrl[1])), 0, 75) : '',
                'tpldes'     => !empty($tplDes[1]) ? subString(strip_tags(trim($tplDes[1])), 0, 40) : '',
                'author'     => !empty($author[1]) ? subString(strip_tags(trim($author[1])), 0, 16) : '',
                'author_url' => !empty($authorUrl[1]) ? subString(strip_tags(trim($authorUrl[1])), 0, 75) : '',
            ];
            $previewPath = $path . $file . '/preview.jpg';
            $tplInfo['preview'] = file_exists($previewPath) ? (DC_URL . 'content/blog_templates/' . $file . '/preview.jpg') : './views/images/theme.png';
            $tplInfo['has_setting'] = hasTemplateSettingFile($path, $file) ? 'y' : 'n';

            require_once DC_ROOT . '/include/lib/plugin_license.php';
            // 博客模板使用带类型前缀的授权缓存键，避免与首页/用户后台/底部导航模板同名串授权。
            $licenseKey = 'blog_template:' . $file;
            $tplInfo['license_status'] = PluginLicense::getStatus($licenseKey);
            $tplInfo['buy_type'] = PluginLicense::getBuyType($licenseKey);
            $tplInfo['expire_time'] = '';
            $licenseCache = self::_getLicenseCache($licenseKey);
            if ($licenseCache) {
                $tplInfo['expire_time'] = $licenseCache['expire_time'] ?? '';
            }

            $templates[] = $tplInfo;
        }
        closedir($handle);
        return $templates;
    }

    /**
     * 获取用户后台模板列表
     * 检测方式：{slug}/{slug}.php（与插件一致）
     */
    function getUserCenterTemplates()
    {
        $templates = [];
        $path = USER_TPLS_PATH;
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
            return $templates;
        }
        $handle = @opendir($path);
        if ($handle === false) {
            return $templates;
        }
        while ($file = @readdir($handle)) {
            if ($file === '.' || $file === '..' || !is_dir($path . $file)) {
                continue;
            }
            // 检测主文件：{slug}/{slug}.php（与插件检测方式一致）
            $mainFile = $path . $file . '/' . $file . '.php';
            if (!file_exists($mainFile) || !checkTemplateBootstrap($path, $file)) {
                continue;
            }
            // 只读取文件头部2KB解析元数据（避免加载整个文件）
            $tplData = @file_get_contents($mainFile, false, null, 0, 2048);
            if ($tplData === false) {
                continue;
            }
            preg_match("/Template\s*Name\s*:(.*)/i", $tplData, $tplName);
            preg_match("/Template\s*URL?\s*:(.*)/i", $tplData, $tplUrl);
            preg_match("/Version\s*:(.*)/i", $tplData, $tplVersion);
            preg_match("/Author\s*:(.*)/i", $tplData, $author);
            preg_match("/Description\s*:(.*)/i", $tplData, $tplDes);
            preg_match("/Author\s*Url\s*:(.*)/i", $tplData, $authorUrl);
            $tplInfo = [
                'tplfile'    => $file,
                'tplname'    => !empty($tplName[1]) ? subString(strip_tags(trim($tplName[1])), 0, 16) : $file,
                'version'    => !empty($tplVersion[1]) ? subString(strip_tags(trim($tplVersion[1])), 0, 16) : '',
                'tplurl'     => !empty($tplUrl[1]) ? subString(strip_tags(trim($tplUrl[1])), 0, 75) : '',
                'tpldes'     => !empty($tplDes[1]) ? subString(strip_tags(trim($tplDes[1])), 0, 40) : '',
                'author'     => !empty($author[1]) ? subString(strip_tags(trim($author[1])), 0, 16) : '',
                'author_url' => !empty($authorUrl[1]) ? subString(strip_tags(trim($authorUrl[1])), 0, 75) : '',
            ];

            $previewPath = $path . $file . '/preview.jpg';
            $tplInfo['preview'] = file_exists($previewPath) ? (DC_URL . 'content/user_templates/' . $file . '/preview.jpg') : './views/images/theme.png';
            $tplInfo['has_setting'] = hasTemplateSettingFile($path, $file) ? 'y' : 'n';

            require_once DC_ROOT . '/include/lib/plugin_license.php';
            // 用户后台模板使用带类型前缀的授权缓存键，避免与首页/底部导航/博客模板的 default 同名串授权。
            $licenseKey = 'user_template:' . $file;
            $tplInfo['license_status'] = PluginLicense::getStatus($licenseKey);
            $tplInfo['buy_type'] = PluginLicense::getBuyType($licenseKey);
            $tplInfo['expire_time'] = '';
            $licenseCache = self::_getLicenseCache($licenseKey);
            if ($licenseCache) {
                $tplInfo['expire_time'] = $licenseCache['expire_time'] ?? '';
            }

            $templates[] = $tplInfo;
        }
        closedir($handle);
        return $templates;
    }

    /**
     * 读取模板授权缓存信息
     */
    private static function _getLicenseCache($slug)
    {
        $cacheFile = DC_ROOT . '/content/cache/plugin_license.php';
        if (!file_exists($cacheFile)) {
            return null;
        }
        $content = @file_get_contents($cacheFile);
        if (!$content) {
            return null;
        }
        $content = str_replace(['<?php exit;?>', '<?php exit; ?>'], '', $content);
        $cache = @json_decode(trim($content), true);
        return is_array($cache) && isset($cache[$slug]) ? $cache[$slug] : null;
    }

    public static function getLicenseCache($slug)
    {
        return self::_getLicenseCache($slug);
    }

    function getCustomTemplates($type)
    {
        $nonce_template = Option::get('nonce_templet') . '/';
        return $this->getCustomTemplatesFromPath($type, TPLS_PATH . $nonce_template);
    }

    function getBlogCustomTemplates($type)
    {
        if (defined('BLOG_TEMPLATE_PATH')) {
            return $this->getCustomTemplatesFromPath($type, BLOG_TEMPLATE_PATH);
        }

        $nonce_blog_tpl = Option::get('nonce_blog_tpl') ?: 'default';
        return $this->getCustomTemplatesFromPath($type, BLOG_TPLS_PATH . $nonce_blog_tpl . '/');
    }

    private function getCustomTemplatesFromPath($type, $templatePath)
    {
        $templatePath = rtrim($templatePath, '/\\') . '/';
        if (!is_dir($templatePath)) {
            return false;
        }
        $files = scandir($templatePath);
        $php_files = [];
        foreach ($files as $file) {
            switch ($type) {
                case 'sort':
                    if (strpos($file, 'log_list_') === 0 && strpos($file, '.php') !== false) {
                        $php_files[] = [
                            'filename' => str_replace('.php', '', $file),
                            'comment'  => $this->getTemplateCommentFromPath($templatePath, $file),
                        ];
                    }
                    break;
                case 'page':
                    if (strpos($file, 'page_') === 0 && strpos($file, '.php') !== false) {
                        $php_files[] = [
                            'filename' => str_replace('.php', '', $file),
                            'comment'  => $this->getTemplateCommentFromPath($templatePath, $file),
                        ];
                    }
                    break;
                case 'log':
                    if (strpos($file, 'echo_log_') === 0 && strpos($file, '.php') !== false) {
                        $php_files[] = [
                            'filename' => str_replace('.php', '', $file),
                            'comment'  => $this->getTemplateCommentFromPath($templatePath, $file),
                        ];
                    }
                    break;
            }
        }
        return $php_files;
    }

    function getCustomFields()
    {
        $nonce_template = Option::get('nonce_templet') . '/';
        if (!is_dir(TPLS_PATH . $nonce_template)) {
            return false;
        }

        $customFieldsPath = TPLS_PATH . $nonce_template . 'custom_fields.php';
        if (file_exists($customFieldsPath)) {
            include $customFieldsPath;
            if (isset($custom_fields)) {
                return $custom_fields;
            }
        }

        return [];
    }

    function getTemplateComment($filename)
    {
        $nonce_template = Option::get('nonce_templet') . '/';
        return $this->getTemplateCommentFromPath(TPLS_PATH . $nonce_template, $filename);
    }

    private function getTemplateCommentFromPath($templatePath, $filename)
    {
        $templatePath = rtrim($templatePath, '/\\') . '/';
        $comment = '';
        $file = fopen($templatePath . $filename, 'rb');
        while (!feof($file)) {
            $line = fgets($file);
            if (strpos($line, "/*@name") !== false) {
                $start = strpos($line, "/*@name") + strlen("/*@name");
                $end = strpos($line, "*/", $start);
                $comment = trim(substr($line, $start, $end - $start));
                break;
            }
        }
        fclose($file);
        if (empty($comment)) {
            $comment = str_replace('.php', '', $filename);
        }
        return $comment;
    }

    // init callback
    public function initCallback($tplName)
    {
        runTemplateCallback(TPLS_PATH, $tplName, 'callback_init');
    }

    // delete callback
    public function rmCallback($tplName)
    {
        runTemplateCallback(TPLS_PATH, $tplName, 'callback_rm');
    }

    // upgrade callback
    public function upCallback($tplName)
    {
        runTemplateCallback(TPLS_PATH, $tplName, 'callback_up');
    }

}
