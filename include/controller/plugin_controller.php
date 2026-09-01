<?php
/**
 * loading plug-in page
 *
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

class Plugin_Controller {
    function loadPluginShow($params) {
        $plugin = isset($params[1]) && $params[1] == 'plugin' ? addslashes($params[2]) : '';
        if (preg_match("/^[\w\-]+$/", $plugin) && file_exists(DC_ROOT . "/content/plugins/{$plugin}/{$plugin}_show.php")) {
            $show_file = DC_ROOT . "/content/plugins/{$plugin}/{$plugin}_show.php";
            
            // 检查是否使用旧版常量
            $content = @file_get_contents($show_file, false, null, 0, 500);
            if ($content && strpos($content, "defined('EM_ROOT')") !== false) {
                emMsg('插件页面使用了旧版常量，请联系开发者更新');
                return;
            }
            
            try {
                include_once($show_file);
            } catch (Throwable $e) {
                error_log("Plugin show error [{$plugin}]: " . $e->getMessage());
                emMsg('插件页面加载失败：' . htmlspecialchars($e->getMessage()));
            }
        }
    }
}
