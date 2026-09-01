<?php
/**
 * search
 *
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

class Search_Controller {
    function display($params) {
        $options_cache = Option::getAll();
        extract($options_cache);

        $page = Input::getIntVar('page', 1);
        if (isset($params[4]) && $params[4] == 'page') {
            $page = abs((int)$params[5]);
        }
        $page = max(1, $page);
        $keywordRaw = isset($params[1]) && $params[1] == 'keyword' ? trim(urldecode($params[2])) : '';
        $keyword = str_replace(array('%', '_'), array('\%', '\_'), addslashes($keywordRaw));
        $keywordForTitle = htmlspecialchars($keywordRaw, ENT_QUOTES);

        if (Input::getStrVar('type') === 'blog') {
            $Log_Model = new Log_Model();
            $logs = [];
            $page_url = '';
            $lognum = 0;
            if ($keywordRaw !== '') {
                $sqlSegment = " and (title like '%$keyword%' or content like '%$keyword%' or excerpt like '%$keyword%')";
                $orderBy = ' order by top desc, sortop desc, date desc';
                $lognum = $Log_Model->getLogNum('n', $sqlSegment);
                $total_pages = $lognum > 0 ? ceil($lognum / $index_lognum) : 1;
                if ($page > $total_pages) {
                    show_404_page();
                }
                $blogSearchBaseUrl = function_exists('dcGetBlogBaseUrl') ? dcGetBlogBaseUrl() : DC_URL;
                $pageurl = $blogSearchBaseUrl . '?keyword=' . urlencode($keywordRaw) . '&type=blog&page=';
                $logs = $Log_Model->getLogsForHome($sqlSegment . $orderBy, $page, $index_lognum);
                $page_url = pagination($lognum, $index_lognum, $page, $pageurl);
            }
            $site_title = ($keywordRaw === '' ? '搜索' : '搜索：' . $keywordForTitle) . ' - ' . $site_title;

            include View::getBlogView('header');
            include View::getBlogView('log_list');
            return;
        }

        $Goods_Model = new Goods_Model();
        $sortModel = new Sort_Model();
        $sorts = $sortModel->getSorts('goods');
        $orderBy = 'g.taxis DESC, g.id DESC';
        $goods = [];
        $page_url = '';
        $lognum = 0;
        if ($keywordRaw !== '') {
            $countSegment = "title like '%$keyword%'";
            $sqlSegment = " g.title like '%$keyword%'";
            $lognum = $Goods_Model->getGoodsNum($countSegment);
            $total_pages = $lognum > 0 ? ceil($lognum / $index_lognum) : 1;
            if ($page > $total_pages) {
                $page = $total_pages;
            }
            $pageurl = DC_URL . '?keyword=' . urlencode($keywordRaw) . '&page=';
            $goods = $Goods_Model->getGoodsForHome($sqlSegment, $page, $index_lognum, $orderBy);
            $page_url = pagination($lognum, $index_lognum, $page, $pageurl);
        }
        $site_title = ($keywordRaw === '' ? '搜索' : '搜索：' . $keywordForTitle) . ' - ' . $site_title;

        include View::getView('header');
        include View::getView('goods_list');
    }
}
