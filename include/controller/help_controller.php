<?php
/**
 * 买家帮助控制器
 */

class Help_Controller {

    function display($params) {
        $CACHE = Cache::getInstance();
        $options_cache = $CACHE->readCache('options');
        extract($options_cache);
        
        // 设置页面标题
        $site_title = '买家帮助 - ' . $blogname;
        
        include View::getCommonView('header');
        include View::getView('help');
        include View::getCommonView('footer');
    }
}
