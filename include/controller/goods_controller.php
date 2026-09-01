<?php


class Goods_Controller {

    function display($params) {

        $options_cache = Option::getAll();
        extract($options_cache);
        Api::local_init();
        $sort = Api::getSortAll();
        $sortid = null;
        $goods_list = Api::getGoodsList([
            'keyword' => Input::getStrVar('q'),
            'sort_id' => Input::getStrVar('sort_id')
        ]);

        // 允许插件过滤商品列表
        doMultiAction('home_goods_list', $goods_list, $goods_list);
        
        doAction('home_goods_list_control');

        include View::getCommonView('header');
        include View::getView('goods_list');
        include View::getCommonView('footer');
    }

    function displayContent($params) {
        global $stationData;

        if($params[1] == 'art'){
            $Log_Model = new Log_Model();
            $Log_Model = new Log_Model();
            $CACHE = Cache::getInstance();

            $options_cache = $CACHE->readCache('options');
            extract($options_cache);



            $logid = 0;
            if (isset($params[1])) {
                if ($params[1] == 'art') {
                    $logid = isset($params[2]) ? (int)$params[2] : 0;
                } elseif (is_numeric($params[1])) {
                    $logid = (int)$params[1];
                } else {
                    $logalias_cache = $CACHE->readCache('logalias');
                    if (!empty($logalias_cache)) {
                        $alias = addslashes(urldecode(trim($params[1])));
                        $logid = array_search($alias, $logalias_cache);
                        if (!$logid) {
                            show_404_page();
                        }
                    }
                }
            }

            $logData = $Log_Model->getOneLogForHome($logid, true, true);

            if (!$logData) {
                show_404_page();
            }

            // 作者和管理可以预览草稿及待审核文章
            if (($logData['hide'] === 'y' || $logData['checked'] === 'n' || $logData['timestamp'] > time()) && $logData['author'] != UID && !User::haveEditPermission()) {
                show_404_page();
            }

            // tdk
            $logData['site_title'] = $this->setSiteTitle($log_title_style, $logData['log_title'], $blogname, $site_title, $logid);
            $logData['site_description'] = $this->setSiteDes($site_description, $logData['log_content'], $logData['excerpt'], $logid);
            $logData['site_key'] = $this->setSiteKey($logData['tags'], $site_key, $logid);

            doMultiAction('article_content_echo', $logData, $logData);

            extract($logData);


            // password
            if (!empty($password)) {
                $postpwd = Input::postStrVar('logpwd');
                $cookiepwd = isset($_COOKIE['em_logpwd_' . $logid]) ? addslashes(trim($_COOKIE['em_logpwd_' . $logid])) : '';
                $Log_Model->AuthPassword($postpwd, $cookiepwd, $password, $logid);
            }

            //comments
            $Comment_Model = new Comment_Model();
            $verifyCode = ISLOGIN == false && $comment_code == 'y' ? "<img src=\"" . DC_URL . "include/lib/checkcode.php\" id=\"captcha\" class=\"captcha\" /><input name=\"imgcode\" type=\"text\" class=\"captcha_input\" size=\"5\" tabindex=\"5\" />" : '';
            $ckname = isset($_COOKIE['commentposter']) ? htmlspecialchars(stripslashes($_COOKIE['commentposter'])) : '';
            $ckmail = isset($_COOKIE['postermail']) ? htmlspecialchars($_COOKIE['postermail']) : '';
            $ckurl = isset($_COOKIE['posterurl']) ? htmlspecialchars($_COOKIE['posterurl']) : '';

            $Log_Model->updateViewCount($logid);

            if (filter_var($link, FILTER_VALIDATE_URL)) {
                doAction('log_direct_link', $link);
                emDirect($link);
            }

            include View::getCommonView('header');

            $template = !empty($template) && file_exists(TEMPLATE_PATH . $template . '.php') ? $template : 'page';
            include View::getView($template);
            die;
        }


        $Goods_Model = new Goods_Model();
        $CACHE = Cache::getInstance();

        $options_cache = $CACHE->readCache('options');
        if($stationData['id'] != 0){
            $options_cache['site_title'] = $stationData['title'];
            $options_cache['blogname'] = $stationData['name'];
            if (!empty($stationData['site_key']))         $options_cache['site_key'] = $stationData['site_key'];
            if (!empty($stationData['site_description'])) $options_cache['site_description'] = $stationData['site_description'];
            if (!empty($stationData['logo']))              $options_cache['logo'] = $stationData['logo'];
            if (!empty($stationData['favicon']))           $options_cache['admin_favicon'] = $stationData['favicon'];
            if (isset($stationData['log_title_style']))    $options_cache['log_title_style'] = $stationData['log_title_style'];
        }

        extract($options_cache);
//        d($options_cache);die;

        $goods_id = 0;
        if (isset($params[1])) {
            if ($params[1] == 'post') {
                $goods_id = isset($params[2]) ? (int)$params[2] : 0;
            } elseif (is_numeric($params[1])) {
                $goods_id = (int)$params[1];
            } else {
                $goods_alias_cache = $CACHE->readCache('goods_alias');
                if (!empty($goods_alias_cache)) {
                    $alias = addslashes(urldecode(trim($params[1])));
                    $goods_id = array_search($alias, $goods_alias_cache);
                    if (!$goods_id) {
                        show_404_page();
                    }
                }
            }
        }
//        $goods = $Goods_Model->getOneGoodsForHome($goods_id);

        Api::local_init();

        $goods = Api::getGoodsInfo([
            'goods_id' => $goods_id
        ]);

        // 登录用户浏览商品详情时记录「浏览足迹」
        if (defined('ISLOGIN') && ISLOGIN && $goods_id > 0 && !empty($goods)) {
            try {
                $footprintModel = new User_Goods_Footprint_Model();
                $footprintModel->record((int)UID, (int)$goods_id, (int)($stationData['id'] ?? 0));
            } catch (Throwable $e) {
                error_log('Record goods footprint failed: ' . $e->getMessage());
            }
        }

        $discount = Api::getGoodsDiscount([
            'goods_id' => $goods_id
        ]);



        doMultiAction('goods_content_echo', $goods, $goods);

//        d($goods);die;

        $order_required = Option::get('order_required');
        $order_required = json_decode($order_required, true);

//        d($order_required);die;
//        var_dump($site_title);die;

        // tdk
        $site_title = $this->setSiteTitle($log_title_style, $goods['title'], $blogname, $site_title, $goods_id);
        $site_description = $this->setSiteDes($site_description, $goods['content'], $goods['id']);

        $meta = [
            'goods_id' => $goods_id,
            'site_title' => $site_title,
            'site_key' => $site_key,
            'site_description' => $site_description,
        ];
        doOnceAction('goods_meta', $meta, $meta);
        extract($meta);

        include View::getCommonView('header');
        $template = !empty($template) && file_exists(TEMPLATE_PATH . $template . '.php') ? $template : 'goods';
        include View::getView($template);
    }

    private function setSiteDes($siteDescription, $logContent, $goods_id) {
        if ($this->isHomePage($goods_id)) {
            return $siteDescription;
        }

        return extractHtmlData($logContent, 90);
    }

    private function setSiteKey($tagIdStr, $siteKey, $goods_id) {
        if ($this->isHomePage($goods_id)) {
            return $siteKey;
        }

        if (empty($tagIdStr)) {
            return $siteKey;
        }

        $tagNames = '';
        $tagModel = new Tag_Model();
        $ids = explode(',', $tagIdStr);

        if ($ids) {
            $tags = $tagModel->getNamesFromIds($ids);
            $tagNames = implode(',', $tags);
        }

        return $tagNames;
    }

    private function setSiteTitle($logTitleStyle, $logTitle, $blogName, $siteTitle, $goods_id) {
        if ($this->isHomePage($goods_id)) {
            return $siteTitle ?: $blogName;
        }

        switch ($logTitleStyle) {
            case '0':
                $articleSeoTitle = $logTitle;
                break;
            case '1':
                $articleSeoTitle = $logTitle . ' - ' . $blogName;
                break;
            case '2':
            default:
                $articleSeoTitle = $logTitle . ' - ' . $siteTitle;
                break;
        }

        return $articleSeoTitle;
    }

    private function isHomePage($goods_id) {
        $homePageId = Option::get('home_page_id');
        if ($homePageId && $homePageId == $goods_id) {
            return true;
        }
        return false;
    }

}
