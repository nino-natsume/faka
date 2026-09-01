<?php
/**
 * URL
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

class Url {

    /**
     * Get article links
     */
    static function log($blogId) {
        $urlMode = Option::get('isurlrewrite');
        $logUrl = '';
        $blogBaseUrl = function_exists('dcGetBlogBaseUrl') ? dcGetBlogBaseUrl() : DC_URL;

        //开启文章别名
        if (Option::get('isalias') == 'y') {
            $Log_Model = new Log_Model();
            $logInfo = $Log_Model->getDetail($blogId);
            $sortName = isset($logInfo['sortname']) ? $logInfo['sortname'] : '';
            $sortAlias = isset($logInfo['sort_alias']) ? $logInfo['sort_alias'] : '';
            $logAlias = isset($logInfo['alias']) ? $logInfo['alias'] : '';
            if (!empty($logAlias)) {
                $sort = '';
                //分类模式下的url /category/1.html
                if (3 == $urlMode && $sortName) {
                    $sort = !empty($sortAlias) ? $sortAlias : $sortName;
                    $sort .= '/';
                }
                $logUrl = $blogBaseUrl . $sort . urlencode($logAlias);
                //开启别名html后缀
                if (Option::get('isalias_html') == 'y') {
                    $logUrl .= '.html';
                }
                return $logUrl;
            }
        }

        switch ($urlMode) {
            case '0'://默认：动态
                $logUrl = $blogBaseUrl . '?blog=' . $blogId;
                break;
            case '1'://静态
                $logUrl = $blogBaseUrl . 'blog-' . $blogId . '.html';
                break;
            case '2'://目录
                $logUrl = $blogBaseUrl . 'blog/' . $blogId;
                break;
            case '3'://分类
                $Log_Model = new Log_Model();
                $logInfo = $Log_Model->getDetail($blogId);
                $sortName = isset($logInfo['sortname']) ? $logInfo['sortname'] : '';
                $sortAlias = isset($logInfo['sort_alias']) ? $logInfo['sort_alias'] : '';
                if (!empty($sortAlias)) {
                    $logUrl = $blogBaseUrl . $sortAlias . '/' . $blogId;
                } elseif (!empty($sortName)) {
                    $logUrl = $blogBaseUrl . $sortName . '/' . $blogId;
                } else {
                    $logUrl = $blogBaseUrl . $blogId;
                }
                $logUrl .= '.html';
                break;
        }
        return $logUrl;
    }

    static function goods($blogId) {
        $urlMode = Option::get('isurlrewrite');
        $logUrl = '';

        //开启文章别名
        if (Option::get('isalias') == 'y') {
            $Log_Model = new Log_Model();
            $logInfo = $Log_Model->getDetail($blogId);
            $sortName = isset($logInfo['sortname']) ? $logInfo['sortname'] : '';
            $sortAlias = isset($logInfo['sort_alias']) ? $logInfo['sort_alias'] : '';
            $logAlias = isset($logInfo['alias']) ? $logInfo['alias'] : '';
            if (!empty($logAlias)) {
                $sort = '';
                //分类模式下的url /category/1.html
                if (3 == $urlMode && $sortName) {
                    $sort = !empty($sortAlias) ? $sortAlias : $sortName;
                    $sort .= '/';
                }
                $logUrl = DC_URL . $sort . urlencode($logAlias);
                //开启别名html后缀
                if (Option::get('isalias_html') == 'y') {
                    $logUrl .= '.html';
                }
                return $logUrl;
            }
        }

        switch ($urlMode) {
            case '0'://默认：动态
                $logUrl = DC_URL . '?post=' . $blogId;
                break;
            case '1'://静态
                $logUrl = DC_URL . 'post-' . $blogId . '.html';
                break;
            case '2'://目录
                $logUrl = DC_URL . 'post/' . $blogId;
                break;
            case '3'://分类
                $Log_Model = new Log_Model();
                $logInfo = $Log_Model->getDetail($blogId);
                $sortName = isset($logInfo['sortname']) ? $logInfo['sortname'] : '';
                $sortAlias = isset($logInfo['sort_alias']) ? $logInfo['sort_alias'] : '';
                if (!empty($sortAlias)) {
                    $logUrl = DC_URL . $sortAlias . '/' . $blogId;
                } elseif (!empty($sortName)) {
                    $logUrl = DC_URL . $sortName . '/' . $blogId;
                } else {
                    $logUrl = DC_URL . $blogId;
                }
                $logUrl .= '.html';
                break;
        }
        return $logUrl;
    }

    static function art($blogId) {
        $urlMode = Option::get('isurlrewrite');
        $logUrl = '';
        $pageBaseUrl = defined('DC_URL') ? DC_URL : (function_exists('realUrl') ? realUrl() : '');

        //开启文章别名
        if (Option::get('isalias') == 'y') {
            $Log_Model = new Log_Model();
            $logInfo = $Log_Model->getDetail($blogId);
            $sortName = isset($logInfo['sortname']) ? $logInfo['sortname'] : '';
            $sortAlias = isset($logInfo['sort_alias']) ? $logInfo['sort_alias'] : '';
            $logAlias = isset($logInfo['alias']) ? $logInfo['alias'] : '';
            if (!empty($logAlias)) {
                $sort = '';
                //分类模式下的url /category/1.html
                if (3 == $urlMode && $sortName) {
                    $sort = !empty($sortAlias) ? $sortAlias : $sortName;
                    $sort .= '/';
                }
                $logUrl = $pageBaseUrl . $sort . urlencode($logAlias);
                //开启别名html后缀
                if (Option::get('isalias_html') == 'y') {
                    $logUrl .= '.html';
                }
                return $logUrl;
            }
        }

        switch ($urlMode) {
            case '0'://默认：动态
                $logUrl = $pageBaseUrl . '?blog=' . $blogId;
                break;
            case '1'://静态
                $logUrl = $pageBaseUrl . 'blog-' . $blogId . '.html';
                break;
            case '2'://目录
                $logUrl = $pageBaseUrl . 'blog/' . $blogId;
                break;
            case '3'://分类
                $Log_Model = new Log_Model();
                $logInfo = $Log_Model->getDetail($blogId);
                $sortName = isset($logInfo['sortname']) ? $logInfo['sortname'] : '';
                $sortAlias = isset($logInfo['sort_alias']) ? $logInfo['sort_alias'] : '';
                if (!empty($sortAlias)) {
                    $logUrl = $pageBaseUrl . $sortAlias . '/' . $blogId;
                } elseif (!empty($sortName)) {
                    $logUrl = $pageBaseUrl . $sortName . '/' . $blogId;
                } else {
                    $logUrl = $pageBaseUrl . $blogId;
                }
                $logUrl .= '.html';
                break;
        }
        return $logUrl;
    }

    static function entry($blogId) {
        $Log_Model = new Log_Model();
        $logInfo = $Log_Model->getDetail($blogId);
        if ($logInfo && isset($logInfo['type']) && $logInfo['type'] === 'page') {
            return self::art($blogId);
        }
        return self::log($blogId);
    }

    static function record($record, $page = null) {
        $blogBaseUrl = function_exists('dcGetBlogBaseUrl') ? dcGetBlogBaseUrl() : DC_URL;
        switch (Option::get('isurlrewrite')) {
            case '0':
                $recordUrl = $blogBaseUrl . '?record=' . $record;
                if ($page) {
                    $recordUrl .= '&page=';
                }
                break;
            default:
                $recordUrl = $blogBaseUrl . 'record/' . $record;
                if ($page) {
                    $recordUrl = $blogBaseUrl . 'record/' . $record . '/page/';
                }
                break;
        }
        return $recordUrl;
    }

    static function sort($sortId, $page = null) {
        $CACHE = Cache::getInstance();
        $sort_cache = $CACHE->readCache('sort');
        $sortInfo = isset($sort_cache[$sortId]) ? $sort_cache[$sortId] : [];
        $sort_index = !empty($sortInfo['alias']) ? $sortInfo['alias'] : $sortId;

        $pid = $sortInfo && !empty($sortInfo['pid']) ? $sortInfo['pid'] : 0; //   父分类ID
        $pAlias = $pid && !empty($sort_cache[$pid]['alias']) ? $sort_cache[$pid]['alias'] : ''; // 父分类别名

        switch (Option::get('isurlrewrite')) {
            case '0':
                $sortUrl = DC_URL . '?sort=' . $sortId;
                if ($page) {
                    $sortUrl .= '&page=';
                }
                break;
            default:
                if (is_numeric($sort_index)) {
                    $sortUrl = DC_URL . 'sort/' . $sort_index;
                } else {
                    if ($pAlias) {
                        $sortUrl = DC_URL . 'sort/' . $pAlias . '/' . $sort_index;
                    } else {
                        $sortUrl = DC_URL . 'sort/' . $sort_index;
                    }
                }

                if ($page) {
                    $sortUrl .= '/page/';
                }
                break;
        }
        return $sortUrl;
    }

    static function blogSort($sortId, $page = null) {
        $CACHE = Cache::getInstance();
        $sort_cache = $CACHE->readCache('blog_sort');
        $sortInfo = isset($sort_cache[$sortId]) ? $sort_cache[$sortId] : [];
        $sort_index = !empty($sortInfo['alias']) ? $sortInfo['alias'] : $sortId;
        $blogBaseUrl = function_exists('dcGetBlogBaseUrl') ? dcGetBlogBaseUrl() : DC_URL;

        $pid = $sortInfo && !empty($sortInfo['pid']) ? $sortInfo['pid'] : 0; //   父分类ID
        $pAlias = $pid && !empty($sort_cache[$pid]['alias']) ? $sort_cache[$pid]['alias'] : ''; // 父分类别名

        switch (Option::get('isurlrewrite')) {
            case '0':
                $sortUrl = $blogBaseUrl . '?blogsort=' . $sortId;
                if ($page) {
                    $sortUrl .= '&page=';
                }
                break;
            default:
                if (is_numeric($sort_index)) {
                    $sortUrl = $blogBaseUrl . 'blogsort/' . $sort_index;
                } else {
                    if ($pAlias) {
                        $sortUrl = $blogBaseUrl . 'blogsort/' . $pAlias . '/' . $sort_index;
                    } else {
                        $sortUrl = $blogBaseUrl . 'blogsort/' . $sort_index;
                    }
                }

                if ($page) {
                    $sortUrl .= '/page/';
                }
                break;
        }
        return $sortUrl;
    }

    static function blogPage() {
        $hasIndependentDomain = function_exists('dcGetBlogIndependentDomains') && !empty(dcGetBlogIndependentDomains());
        $blogBaseUrl = function_exists('dcGetBlogBaseUrl') ? dcGetBlogBaseUrl() : DC_URL;
        if ($hasIndependentDomain) {
            return $blogBaseUrl . 'page/';
        }
        switch (Option::get('isurlrewrite')) {
            case '0':
                $blogPageUrl = DC_URL . 'blog?page=';
                break;
            default:
                $blogPageUrl = DC_URL . 'blog/page/';
                break;
        }
        return $blogPageUrl;
    }

    static function author($authorId, $page = null) {
        $blogBaseUrl = function_exists('dcGetBlogBaseUrl') ? dcGetBlogBaseUrl() : DC_URL;
        switch (Option::get('isurlrewrite')) {
            case '0':
                $authorUrl = $blogBaseUrl . '?author=' . $authorId;
                if ($page) {
                    $authorUrl .= '&page=';
                }
                break;
            default:
                $authorUrl = $blogBaseUrl . 'author/' . $authorId;
                if ($page) {
                    $authorUrl = $blogBaseUrl . 'author/' . $authorId . '/page/';
                }
                break;
        }
        return $authorUrl;
    }

    static function tag($tag, $page = null) {
        $blogBaseUrl = function_exists('dcGetBlogBaseUrl') ? dcGetBlogBaseUrl() : DC_URL;
        switch (Option::get('isurlrewrite')) {
            case '0':
                $tagUrl = $blogBaseUrl . '?tag=' . $tag;
                if ($page) {
                    $tagUrl .= '&page=';
                }
                break;
            default:
                $tagUrl = $blogBaseUrl . 'tag/' . $tag;
                if ($page) {
                    $tagUrl = $blogBaseUrl . 'tag/' . $tag . '/page/';
                }
                break;
        }
        return $tagUrl;
    }

    static function logPage() {
        $posts = Option::get('home_page_id') > 0 ? 'posts/' : '';
        switch (Option::get('isurlrewrite')) {
            case '0':
                $logPageUrl = DC_URL . $posts . '?page=';
                break;
            default:
                $logPageUrl = DC_URL . $posts . 'page/';
                break;
        }
        return $logPageUrl;
    }

    static function comment($blogId, $pageId, $cid) {
        $commentUrl = Url::entry($blogId);
        if ($pageId > 1) {
            if (Option::get('isurlrewrite') == 0 && strpos($commentUrl, '=') !== false) {
                $commentUrl .= '&comment-page=';
            } else {
                $commentUrl .= '/comment-page-';
            }
            $commentUrl .= $pageId;
        }
        $commentUrl .= '#' . $cid;
        return $commentUrl;
    }

    /**
     * 获取导航链接
     */
    static function navi($type, $typeId, $url) {
        switch ($type) {
            case Navi_Model::navitype_custom:
            case Navi_Model::navitype_home:
            case Navi_Model::navitype_t:
            case Navi_Model::navitype_admin:
            case Navi_Model::navitype_blog:
                break;
            case Navi_Model::navitype_sort:
                $url = self::sort($typeId);
                break;
            case Navi_Model::navitype_blogsort:
                $url = self::blogSort($typeId);
                break;
            case Navi_Model::navitype_page:
                $url = self::art($typeId);
                break;
            default:
                $url = (strpos($url, 'http') === 0 ? '' : DC_URL) . $url;
                break;
        }
        return $url;
    }

}
