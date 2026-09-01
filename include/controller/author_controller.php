<?php
/**
 * author's articles
 *
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

class Author_Controller {
    function display($params) {
        $Log_Model = new Log_Model();
        $User_Model = new User_Model();

        $CACHE = Cache::getInstance();
        $options_cache = Option::getAll();
        extract($options_cache);

        $page = isset($params[4]) && $params[4] == 'page' ? abs((int)$params[5]) : 1;
        $page = max(1, $page);
        $author = isset($params[1]) && $params[1] == 'author' ? (int)$params[2] : '';

        $pageurl = '';

        $user_info = $User_Model->getOneUser($author);
        if (empty($user_info)) {
            show_404_page();
        }
        $author_name = $user_info['nickname'];

        //page meta
        $site_title = $author_name . ' - ' . $site_title;

        $sqlSegmentBase = "and author=$author";
        $sqlSegment = $sqlSegmentBase . " order by date desc";
        $lognum = $Log_Model->getLogNum('n', $sqlSegmentBase);

        $total_pages = $lognum > 0 ? ceil($lognum / $index_lognum) : 1;
        if ($page > $total_pages) {
            show_404_page();
        }
        $pageurl .= Url::author($author, 'page');

        $logs = $Log_Model->getLogsForHome($sqlSegment, $page, $index_lognum);
        $page_url = pagination($lognum, $index_lognum, $page, $pageurl);

        include View::getBlogView('header');
        include View::getBlogView('log_list');
    }
}
