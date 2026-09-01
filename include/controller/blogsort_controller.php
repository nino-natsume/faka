<?php

/**
 * sort

 */

class Blogsort_Controller
{
    function display($params)
    {
        $Log_Model = new Log_Model();
        $CACHE = Cache::getInstance();
        $options_cache = Option::getAll();
        extract($options_cache);

        $page = Input::getIntVar('page', 1);
        if (isset($params[5]) && $params[5] === 'page') {
            $page = abs((int)$params[6]);
        } elseif (isset($params[4]) && $params[4] === 'page') {
            $page = abs((int)$params[5]);
        }
        $page = max(1, $page);

        $sortid = '';
        $sort_cache = $CACHE->readCache('blog_sort');
        $parent_index = '';
        $sort_index = '';
        if (!empty($params[2])) {
            $sort_index = trim((string)$params[2]);
            if (!empty($params[3]) && !preg_match('/^[&?]/', (string)$params[3]) && $params[3] !== 'page') {
                $parent_index = $sort_index;
                $sort_index = trim((string)$params[3]);
            }
            if (is_numeric($sort_index)) {
                $sortid = (int)$sort_index;
            } else {
                $alias = urldecode(trim($sort_index));
                foreach ($sort_cache as $key => $value) {
                    $sort_alias = isset($value['alias']) ? (string)$value['alias'] : '';
                    $sort_name = isset($value['sortname']) ? html_entity_decode((string)$value['sortname'], ENT_QUOTES, 'UTF-8') : '';
                    if (($sort_alias !== '' && $alias === $sort_alias) || $alias === $sort_name) {
                        $sortid = $key;
                        break;
                    }
                }
            }
        }

        $pageurl = '';

        if (!isset($sort_cache[$sortid])) {
            show_404_page();
        }
        $sort = $sort_cache[$sortid];
        if ($parent_index !== '') {
            $pid = (int)($sort['pid'] ?? 0);
            if ($pid <= 0 || !isset($sort_cache[$pid])) {
                show_404_page();
            }
            $parent_index = urldecode(trim($parent_index));
            $parent_sort = $sort_cache[$pid];
            $parent_alias = isset($parent_sort['alias']) ? (string)$parent_sort['alias'] : '';
            $parent_name = isset($parent_sort['sortname']) ? html_entity_decode((string)$parent_sort['sortname'], ENT_QUOTES, 'UTF-8') : '';
            if ($parent_index !== (string)$pid && $parent_index !== $parent_alias && $parent_index !== $parent_name) {
                show_404_page();
            }
        }
        $sortPid = isset($sort['pid']) ? $sort['pid'] : 0;
        $collectChildren = function ($sid) use (&$collectChildren, $sort_cache) {
            $children = [];
            if (!empty($sort_cache[$sid]['children'])) {
                foreach ($sort_cache[$sid]['children'] as $childId) {
                    $childId = (int)$childId;
                    $children[] = $childId;
                    $children = array_merge($children, $collectChildren($childId));
                }
            }
            return $children;
        };
        $sortChildren = $collectChildren((int)$sortid);
        $sortName = isset($sort['sortname']) ? $sort['sortname'] : '';
        $sortTitle = isset($sort['title']) ? trim((string)$sort['title']) : '';
        $sortTitlePlain = html_entity_decode($sortTitle, ENT_QUOTES, 'UTF-8');
        $sortTitlePlaceholderTexts = [
            '标题（用于分类页的 title）',
            '标题（用于分类页的 title）支持变量: {{site_title}}, {{site_name}}, {{sort_name}}',
        ];
        if (in_array($sortTitlePlain, $sortTitlePlaceholderTexts, true)) {
            $sortTitle = '';
        }
        $sortKw = isset($sort['kw']) ? $sort['kw'] : '';
        $sortDesc = isset($sort['description']) ? $sort['description'] : '';
        $sortPageCount = isset($sort['page_count']) ? $sort['page_count'] : 0;
        if ($sortPageCount > 0) {
            $index_lognum = $sortPageCount;
        }
        //page meta
        if ($sortTitle) {
            $site_title = $sortTitle;
        } else {
            $site_title = $sortName . ' - ' . $site_title;
        }
        if ($sortDesc) {
            $site_description = $sortDesc;
        }
        if ($sortKw) {
            $site_key = $sortKw;
        }
        if ($sortPid || empty($sortChildren)) {
            $sqlSegment = "and sortid=$sortid";
        } else {
            $sortids = array_merge(array($sortid), $sortChildren);
            $sqlSegment = "and sortid in (" . implode(',', $sortids) . ")";
        }
        $orderBy = " order by sortop desc, date desc";
        $lognum = $Log_Model->getLogNum('n', $sqlSegment);
        $total_pages = $lognum > 0 ? ceil($lognum / $index_lognum) : 1;
        if ($page > $total_pages) {
            show_404_page();
        }
        $pageurl .= Url::blogSort($sortid, 'page');

        $logs = $Log_Model->getLogsForHome($sqlSegment . $orderBy, $page, $index_lognum);
        $page_url = pagination($lognum, $index_lognum, $page, $pageurl);

        $template = !empty($sort['template']) && file_exists(TEMPLATE_PATH . $sort['template'] . '.php') ? $sort['template'] : 'log_list';

        include View::getBlogView('header');
        include View::getBlogView($template);
    }
}
