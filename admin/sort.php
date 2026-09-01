<?php
/**
 * sort manager
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

/**
 * @var string $action
 * @var object $CACHE
 */

require_once 'globals.php';

$Sort_Model = new Sort_Model();

if(empty($action)) {

    $type = Input::getStrVar('type', 'goods');
    if (!in_array($type, ['goods', 'blog'], true)) {
        $type = 'goods';
    }
    $sorts = $Sort_Model->getSorts($type);



    $Template_Model = new Template_Model();
    $customTemplates = $type == 'blog' ? $Template_Model->getBlogCustomTemplates('sort') : $Template_Model->getCustomTemplates('sort');

    if($type == 'goods'){
        $br = '<a href="./">数据中心</a><a href="./goods.php">商品管理</a><a><cite>商品分类</cite></a>';
    }else{
        $br = '<a href="./">数据中心</a><a href="./article.php">博客管理</a><a><cite>文章分类</cite></a>';
    }


    include View::getAdmView('header');
    require_once View::getAdmView('templates/default/sort/index');
    include View::getAdmView('footer');
    View::output();
}
if($action == 'index'){
    $type = Input::getStrVar('type', 'goods');
    if (!in_array($type, ['goods', 'blog'], true)) {
        $type = 'goods';
    }
    $sorts = $Sort_Model->getSorts($type);
    foreach ($sorts as $sid => &$sort) {
        $pid = (int)($sort['pid'] ?? 0);
        $sort['parent_sortname'] = $pid > 0 && isset($sorts[$pid]) ? $sorts[$pid]['sortname'] : '无';
        $sort['alias'] = htmlspecialchars($sort['alias'] ?? '');
        $sort['title_admin'] = htmlspecialchars($sort['title_origin'] ?? '');
    }
    unset($sort);
    $sorts = array_values($sorts);
    output::data($sorts, count($sorts));
}
if($action == 'add'){
    $type = Input::getStrVar('type', 'goods');
    if (!in_array($type, ['goods', 'blog'], true)) {
        $type = 'goods';
    }
    $sorts = $Sort_Model->getSorts($type);
    $Template_Model = new Template_Model();
    $customTemplates = $type == 'blog' ? $Template_Model->getBlogCustomTemplates('sort') : $Template_Model->getCustomTemplates('sort');

    include View::getAdmView('open_head');
    require_once View::getAdmView('templates/default/sort/add');
    include View::getAdmView('open_foot');
    View::output();
}

if($action == 'edit'){
    $type = Input::getStrVar('type', 'goods');
    if (!in_array($type, ['goods', 'blog'], true)) {
        $type = 'goods';
    }
    $sorts = $Sort_Model->getSorts($type);
    $Template_Model = new Template_Model();
    $customTemplates = $type == 'blog' ? $Template_Model->getBlogCustomTemplates('sort') : $Template_Model->getCustomTemplates('sort');
    $id = Input::getIntVar('id');

    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;

    $sql = "select * from {$db_prefix}sort where sid={$id} and type='{$type}'";
    $data = $db->once_fetch_array($sql);
    if (!$data) {
        Output::error('分类不存在');
    }

    include View::getAdmView('open_head');
    require_once View::getAdmView('templates/default/sort/edit');
    include View::getAdmView('open_foot');
    View::output();
}

if ($action == 'taxis') {
    LoginAuth::checkToken();
    $type = Input::postStrVar('type', Input::getStrVar('type', 'goods'));
    if (!in_array($type, ['goods', 'blog'], true)) {
        $type = 'goods';
    }
    $sort = isset($_POST['sort']) ? $_POST['sort'] : '';

    if (empty($sort)) {
        Output::error('没有可排序的分类');
    }

    foreach ($sort as $key => $value) {
        $value = (int)$value;
        $key = (int)$key;
        $Sort_Model->updateSort(array('taxis' => $key), $value);
    }

    if ($type == 'goods') {
        $CACHE->updateCache(['sort', 'logsort', 'navi']);
    } else {
        $CACHE->updateCache(['blog_sort', 'logsort']);
    }
    Output::ok();
}

if ($action == 'save') {
    LoginAuth::checkToken();
    $sid = Input::postIntVar('sid');
    $sortname = Input::postStrVar('sortname');
    $alias = Input::postStrVar('alias');
    $pid = Input::postIntVar('pid');
    $template = isset($_POST['template']) && $_POST['template'] != 'log_list' ? addslashes(trim($_POST['template'])) : '';
    $description = Input::postStrVar('description');
    $kw = Input::postStrVar('kw');
    $title = trim(Input::postStrVar('title'));
    $titlePlaceholderTexts = [
        '标题（用于分类页的 title）',
        '标题（用于分类页的 title）支持变量: {{site_title}}, {{site_name}}, {{sort_name}}',
    ];
    if (in_array($title, $titlePlaceholderTexts, true)) {
        $title = '';
    }
    $sortimg = Input::postStrVar('sortimg');
    $sorticon = Input::postStrVar('sorticon');
    $page_count = Input::postIntVar('page_count', 0);
    $type = Input::postStrVar('type', 'goods');
    if (!in_array($type, ['goods', 'blog'], true)) {
        $type = 'goods';
    }
    $taxis = Input::postIntVar('taxis', 0);
    $page_count = max(0, $page_count);


    if (empty($sortname)) {
        output::error('请填写分类名');
    }

    if ($sid && $sid == $pid) {
        output::error('父分类选择错误');
    }

    $sorts = $Sort_Model->getSorts($type);
    if ($pid > 0 && !isset($sorts[$pid])) {
        output::error('父分类不存在或类型不匹配');
    }
    if ($sid && $pid > 0) {
        $ancestorPid = $pid;
        while ($ancestorPid > 0 && isset($sorts[$ancestorPid])) {
            if ($ancestorPid == $sid) {
                output::error('不能选择当前分类或其子分类作为父分类');
            }
            $ancestorPid = (int)$sorts[$ancestorPid]['pid'];
        }
    }

    if (!empty($alias)) {
        if (!preg_match("|^[\w-]+$|", $alias)) {
            output::error('别名错误');
        } elseif (preg_match("|^[0-9]+$|", $alias)) {
            output::error('别名错误');
        } elseif (in_array($alias, array('post', 'record', 'sort', 'tag', 'author', 'page', 'posts'))) {
            output::error('禁止使用此别名');
        } else {
            $sort_cache = $CACHE->readCache($type == 'blog' ? 'blog_sort' : 'sort');
            if (!is_array($sort_cache)) {
                $sort_cache = $Sort_Model->getSorts($type);
            }
            if ($sid) {
                unset($sort_cache[$sid]);
            }
            foreach ($sort_cache as $key => $value) {
                if ($alias == $value['alias']) {
                    output::error('此别名已被使用');
                }
            }
        }
    }

    $sort_data = [
        'sortname'    => $sortname,
        'pid'         => $pid,
        'template'    => $template,
        'description' => $description,
        'kw'          => $kw,
        'title'       => $title,
        'alias'       => $alias,
        'sortimg'     => $sortimg,
        'sorticon'    => $sorticon,
        'page_count'  => $page_count,
        'taxis' => $taxis,
        'type' => $type
    ];


    if ($sid) {
        $Sort_Model->updateSort($sort_data, $sid);
    } else {
        $sid = $Sort_Model->addSort($sort_data);
    }

    doAction('save_sort', $sid, $sort_data);

    if($type == 'goods'){
        $CACHE->updateCache(['sort', 'logsort', 'navi']);
    }else{
        $CACHE->updateCache(['blog_sort', 'logsort']);
    }

    output::ok();


}

if ($action == 'del') {
    LoginAuth::checkToken();
    $sid = Input::postStrVar('ids');
    $type = Input::postStrVar('type', 'goods');
    if (!in_array($type, ['goods', 'blog'], true)) {
        $type = 'goods';
    }
    $sid = array_filter(array_map('intval', explode(',', $sid)));
    if (empty($sid)) {
        Output::error('请选择要删除的分类');
    }
    foreach($sid as $val){
        $sortInfo = $Sort_Model->getOneSortById($val);
        if (empty($sortInfo) || ($sortInfo['type'] ?? '') !== $type) {
            Output::error('分类不存在或类型不匹配');
        }
        $Sort_Model->deleteSort($val);
    }


    if($type == 'goods'){
        $CACHE->updateCache(['sort', 'logsort', 'navi']);
    }else{
        $CACHE->updateCache(['blog_sort', 'logsort']);
    }
    output::ok();
}
