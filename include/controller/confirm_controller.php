<?php
/**
 * homepage & article detail
 *
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

class Confirm_Controller {
    function display() {

        $CACHE = Cache::getInstance();
        $options_cache = Option::getAll();
        extract($options_cache);

        $params = Input::postStrArray('product');
//        d($params); die;
        $goodsModel = new Goods_Model();
        $orderModel = new Order_Model();

        $timestamp = time();
        $order_insert = [
            'out_trade_no' => date('YmdHis', $timestamp) . mt_rand(1000, 9999),
            'create_time' => $timestamp,
            'amount' => 0
        ];
        $order_id = 0;

        $order_list_insert = [];

//        d($params);die;
        foreach($params as $val){
            if(empty($val['sku'])){
                $goods = $goodsModel->getOneGoodsForHome($val['goods_id']);
                $specification = '';
                $specification_value = '';
                $attr_spec = '';
                $sku = $orderModel->getSku($val['goods_id'], '0');
            }else{
                $specification_value_data = $orderModel->getSpecificationValue($val['sku']);
                $specification_value = array_column($specification_value_data, 'name');

                $specification_ids = array_column($specification_value_data, 'attr_id');
                $specification_data = $orderModel->getSpecification($specification_ids);
                $specification = array_column($specification_data, 'title');
                $sku = $orderModel->getSku($val['goods_id'], $val['sku']);
                $goods = $goodsModel->getOneGoodsForHome($val['goods_id']);
                $attr_spec = '';
                foreach($specification as $k => $v){
                    $attr_spec .= $v . '：' . $specification_value[$k] . '；';
                }

                $specification = implode(';', $specification);
                $specification_value = implode(';', $specification_value);
            }


            $val['quantity'] = (int) $val['quantity'];
            $val['quantity'] = $val['quantity'] < 1 ? 1 : $val['quantity'];

            $order_list_insert[] = [
                'goods' => $goods,
                'order_id' => $order_id,
                'goods_id' => $val['goods_id'],
                'sku' => $val['sku'],
                'attr' => $specification, // sku属性
                'specification' => $specification_value, // sku属性值
                'quantity' => $val['quantity'],
                'unit_price' => $sku['price'],
                'price' => $sku['price'] * $val['quantity'],
                'attr_spec' => $attr_spec
            ];
            $order_insert['amount'] += $sku['price'] * 100 * $val['quantity'];

        }

//        dd($order_list_insert);die;

        // 对接商品且有同步输入框、实物商品有独立收货模型时，隐藏本站全局 order_required
        // 避免重复显示全局输入框，与非实物商品链路产生混乱
        $db = Database::getInstance();
        $db_prefix = DB_PREFIX;
        $hide_global_required = false;
        foreach ($order_list_insert as $oli) {
            $gid = (int)$oli['goods_id'];
            if (($oli['goods']['type'] ?? '') === 'physical') {
                $hide_global_required = true;
                break;
            }
            $hasDockingMapping = function($table, $goodsId) use ($db, $db_prefix) {
                $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $db_prefix . $table);
                if ($tableName === '') return false;
                $tableRow = $db->once_fetch_array("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$tableName}' LIMIT 1");
                if (empty($tableRow)) return false;
                return (bool)$db->once_fetch_array("SELECT id FROM `{$tableName}` WHERE goods_id = " . (int)$goodsId . " LIMIT 1");
            };
            $dockRow = $hasDockingMapping('docking_goods', $gid);
            $qingjiuRow = $hasDockingMapping('qingjiu_goods', $gid);
            $xiaoqingRow = $hasDockingMapping('xiaoqing_goods', $gid);
            $yiciyuanRow = $hasDockingMapping('yiciyuan_goods', $gid);
            if (($dockRow || $qingjiuRow || $xiaoqingRow || $yiciyuanRow) && !empty($oli['goods']['attach_user'])) {
                $hide_global_required = true;
                break;
            }
        }
        if ($hide_global_required) {
            $order_required = '[]'; // 清空全局必填项JSON字符串
        }
        // 将 order_required JSON 解码为数组供模板使用
        if (is_string($order_required ?? '')) {
            $order_required = json_decode($order_required, true) ?: [];
        }

        include View::getCommonView('header');
        include View::getView('confirm');
    }

    function displayContent($params) {
        $comment_page = isset($params[4]) && $params[4] == 'comment-page' ? (int)$params[5] : 1;

        $Product_Model = new Product_Model();
        $CACHE = Cache::getInstance();

        $options_cache = $CACHE->readCache('options');
        extract($options_cache);

        $logid = 0;
        if (isset($params[1])) {
            if ($params[1] == 'post') {
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

        $logData = $Product_Model->getOneProductForHome($logid, true, true);
        if (!$logData) {
            show_404_page();
        }

        // 作者和管理可以预览草稿及待审核文章
        if (($logData['hide'] === 'y' || $logData['checked'] === 'n') && $logData['author'] != UID && !User::haveEditPermission()) {
            show_404_page();
        }

        doMultiAction('article_content_echo', $logData, $logData);

        extract($logData);

//        d($logData);

        // password
        if (!empty($password)) {
            $postpwd = isset($_POST['logpwd']) ? addslashes(trim($_POST['logpwd'])) : '';
            $cookiepwd = isset($_COOKIE['em_logpwd_' . $logid]) ? addslashes(trim($_COOKIE['em_logpwd_' . $logid])) : '';
            $Log_Model->AuthPassword($postpwd, $cookiepwd, $password, $logid);
        }
        // tdk
        $site_title = $this->setSiteTitle($log_title_style, $log_title, $blogname, $site_title, $logid);
        $site_description = $this->setSiteDes($site_description, $log_content, $logid);
        $site_key = $this->setSiteKey($tags, $site_key, $logid);

        //comments
        $Comment_Model = new Comment_Model();
        $verifyCode = ISLOGIN == false && $comment_code == 'y' ? "<img src=\"" . DC_URL . "include/lib/checkcode.php\" id=\"captcha\" /><input name=\"imgcode\" type=\"text\" class=\"input\" size=\"5\" tabindex=\"5\" />" : '';
        $ckname = isset($_COOKIE['commentposter']) ? htmlspecialchars(stripslashes($_COOKIE['commentposter'])) : '';
        $ckmail = isset($_COOKIE['postermail']) ? htmlspecialchars($_COOKIE['postermail']) : '';
        $ckurl = isset($_COOKIE['posterurl']) ? htmlspecialchars($_COOKIE['posterurl']) : '';
        $comments = $Comment_Model->getComments($logid, 'n', $comment_page);

        $Product_Model->updateViewCount($logid);

        if (filter_var($link, FILTER_VALIDATE_URL)) {
            doAction('log_direct_link', $link);
            emDirect($link);
        }

        include View::getView('header');
        if ($type === 'product') {
            $neighborLog = $Product_Model->neighborLog($timestamp);
            $template = !empty($template) && file_exists(TEMPLATE_PATH . $template . '.php') ? $template : 'echo_product';
            include View::getView($template);
        } elseif ($type === 'page') {
            $template = !empty($template) && file_exists(TEMPLATE_PATH . $template . '.php') ? $template : 'page';
            include View::getView($template);
        }
    }

    private function setSiteDes($siteDescription, $logContent, $logId) {
        if ($this->isHomePage($logId)) {
            return $siteDescription;
        }

        return extractHtmlData($logContent, 90);
    }

    private function setSiteKey($tagIdStr, $siteKey, $logId) {
        if ($this->isHomePage($logId)) {
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

    private function setSiteTitle($logTitleStyle, $logTitle, $blogName, $siteTitle, $logId) {
        if ($this->isHomePage($logId)) {
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

    private function isHomePage($logId) {
        $homePageId = Option::get('home_page_id');
        if ($homePageId && $homePageId == $logId) {
            return true;
        }
        return false;
    }

}
