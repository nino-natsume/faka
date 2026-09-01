<?php
/**
 * sort
 *
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

class Sort_Controller {
    function display($params) {

        $CACHE = Cache::getInstance();
        $options_cache = Option::getAll();
        extract($options_cache);

        $page = isset($params[4]) && $params[4] == 'page' ? abs((int)$params[5]) : 1;

        $sortid = '';
        if (!empty($params[2])) {
            if (is_numeric($params[2])) {
                $sortid = (int)$params[2];
            } else {
                $sort_cache = $CACHE->readCache('sort');
                foreach ($sort_cache as $key => $value) {
                    $alias = addslashes(urldecode(trim($params[2])));
                    if (array_search($alias, $value, true)) {
                        $sortid = $key;
                        break;
                    }
                }
            }
        }

        $options_cache = Option::getAll();
        extract($options_cache);
        Api::local_init();
        $sort = Api::getSortAll();

        $goods_list = Api::getGoodsList([
            'keyword' => Input::getStrVar('keyword'),
            'sort_id' => $sortid
        ]);

        // 允许插件过滤商品列表
        doMultiAction('home_goods_list', $goods_list, $goods_list);

        $template = !empty($sort['template']) && file_exists(TEMPLATE_PATH . $sort['template'] . '.php') ? $sort['template'] : 'goods_list';

        include View::getCommonView('header');
        include View::getView($template);

        include View::getCommonView('footer');
    }
}
