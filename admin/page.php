<?php
/**
 * page
 */

/**
 * @var string $action
 * @var object $CACHE
 */


//admin_article_perpage_num

require_once 'globals.php';

if (empty($action)) {

    $br = '<a href="./">数据中心</a><a href="./page.php">外观设置</a><a><cite>页面管理</cite></a>';

    include View::getAdmView('header');
    require_once(View::getAdmView('page'));
    include View::getAdmView('footer');
    View::output();
}

if ($action == 'index') {

    $emPage = new Log_Model();

    $page = Input::getIntVar('page', 1);
    $page = max(1, $page);
    $keyword = Input::getStrVar('keyword');
    $hide = Input::getStrVar('hide');
    $order = Input::getStrVar('order');
    $perpage_num = Input::getIntVar('limit');
    if ($perpage_num <= 0) {
        $perpage_num = Option::get('admin_article_perpage_num');
    }

    $conditions = [];
    if ($keyword) {
        $keyword = str_replace(array('%', '_'), array('\%', '\_'), addslashes($keyword));
        $conditions[] = "title like '%$keyword%'";
    }
    $condition = $conditions ? 'and ' . implode(' and ', $conditions) : '';
    $hide_state = in_array($hide, ['y', 'n'], true) ? $hide : '';

    $orderBy = ' ORDER BY ';
    switch ($order) {
        case 'view':
            $orderBy .= 'views DESC';
            break;
        case 'comm':
            $orderBy .= 'comnum DESC';
            break;
        default:
            $orderBy .= 'date DESC';
            break;
    }

    $pages = $emPage->getLogsForAdmin($condition . $orderBy, $hide_state, $page, 'page', $perpage_num);
    $pageNum = $emPage->getLogNum($hide_state, $condition, 'page', 1);
    $homePageId = (int)Option::get('home_page_id');
    foreach ($pages as &$pageItem) {
        $pageItem['page_url'] = Url::art($pageItem['gid']);
        $pageItem['alias'] = htmlspecialchars($pageItem['alias'] ?? '');
        $pageItem['template'] = !empty($pageItem['template']) ? htmlspecialchars($pageItem['template']) : 'page';
        $pageItem['status_text'] = $pageItem['hide'] === 'y' ? '隐藏' : '公开';
        $pageItem['home_page'] = $homePageId === (int)$pageItem['gid'] ? 'y' : 'n';
        $pageItem['allow_remark_text'] = $pageItem['allow_remark'] === 'y' ? '允许' : '关闭';
    }
    unset($pageItem);


    output::data($pages, $pageNum);
}

if ($action == 'new') {
    $pageData = array(
        'containertitle'  => '新建页面',
        'pageId'          => -1,
        'title'           => '',
        'content'         => '',
        'alias'           => '',
        'hide'            => 'n',
        'template'        => 'page',
        'is_allow_remark' => '',
        'is_home_page'    => '',
        'is_hide'         => '',
        'att_frame_url'   => 'attachment.php?action=selectFile',
        'link'            => '',
        'cover'           => '',
    );
    extract($pageData);

    $MediaSort_Model = new MediaSort_Model();
    $mediaSorts = $MediaSort_Model->getSorts();

    $Template_Model = new Template_Model();
    $customTemplates = $Template_Model->getBlogCustomTemplates('page');



    include View::getAdmView('open_head');
    require_once(View::getAdmView('page_create'));
    include View::getAdmView('open_foot');
    View::output();
}

if ($action == 'mod') {
    $emPage = new Log_Model();

    $Template_Model = new Template_Model();
    $customTemplates = $Template_Model->getBlogCustomTemplates('page');

    $containertitle = '编辑页面';
    $pageId = isset($_GET['id']) ? (int)$_GET['id'] : '';
    $pageData = $emPage->getOneLogForAdmin($pageId);
    if (!$pageData || $pageData['type'] !== 'page') {
        Output::error('页面不存在');
    }
    extract($pageData);

    //media
    $Media_Model = new Media_Model();
    $medias = $Media_Model->getMedias();

    $MediaSort_Model = new MediaSort_Model();
    $mediaSorts = $MediaSort_Model->getSorts();

    $is_allow_remark = $allow_remark == 'y' ? 'checked="checked"' : '';
    $is_home_page = Option::get('home_page_id') == $pageId ? 'checked="checked"' : '';
    $is_hide = $hide == 'y' ? 'checked="checked"' : '';



    include View::getAdmView('open_head');
    require_once(View::getAdmView('page_create'));
    include View::getAdmView('open_foot');
    View::output();
}

if ($action == 'save') {
    $emPage = new Log_Model();
    $Navi_Model = new Navi_Model();

    $title = isset($_POST['title']) ? addslashes(trim($_POST['title'])) : '';
    $content = isset($_POST['pagecontent']) ? addslashes(trim($_POST['pagecontent'])) : '';
    $alias = isset($_POST['alias']) ? addslashes(trim($_POST['alias'])) : '';
    $pageId = isset($_POST['pageid']) ? (int)trim($_POST['pageid']) : -1;
    $ishide = Input::postStrVar('ishide', 'n');
    if (!in_array($ishide, ['y', 'n'], true)) {
        $ishide = 'n';
    }
    $template = isset($_POST['template']) && $_POST['template'] != 'page' ? addslashes(trim($_POST['template'])) : '';
    $allow_remark = isset($_POST['allow_remark']) ? addslashes(trim($_POST['allow_remark'])) : 'n';
    $home_page = isset($_POST['home_page']) ? addslashes(trim($_POST['home_page'])) : 'n';
    $link = Input::postStrVar('link');
    $cover = Input::postStrVar('cover');

    $postTime = time();

    if ($title === '') {
        Output::error('请填写页面标题');
    }

    if (!empty($alias)) {
        $logalias_cache = $CACHE->readCache('logalias');
        $alias = $emPage->checkAlias($alias, $logalias_cache, $pageId);
    }

    if ($pageId > 0) {
        $pageDetail = $emPage->getOneLogForAdmin($pageId);
        if (!$pageDetail || $pageDetail['type'] !== 'page') {
            Output::error('页面不存在');
        }
    }

    $logData = array(
        'title'        => $title,
        'content'      => $content,
        'excerpt'      => '',
        'date'         => $postTime,
        'allow_remark' => $allow_remark,
        'hide'         => $ishide,
        'alias'        => $alias,
        'type'         => 'page',
        'template'     => $template,
        'link'         => $link,
        'cover'        => $cover,
    );

    $directUrl = '';
    if ($pageId > 0) {
        $emPage->updateLog($logData, $pageId);
        $directUrl = './page.php?active_pubpage=1';
    } else {
        $pageId = $emPage->addlog($logData);
        $directUrl = './page.php?active_hide_n=1';
    }

    if ($home_page === 'y') {
        Option::updateOption('home_page_id', $pageId);
    } elseif (Option::get('home_page_id') == $pageId) {
        Option::updateOption('home_page_id', 0);
    }

    $CACHE->updateCache(array('options', 'logalias'));

    output::ok();

    switch ($action) {
        case 'autosave':
            echo "autosave_gid:{$pageId}_df:0_";
            break;
        case 'save':
            emDirect($directUrl);
            break;
    }
}

if($action == 'del'){
    $ids = Input::postStrVar('ids');
    $ids = explode(',', $ids);
    $emPage = new Log_Model();
    $home_page_id = Option::get('home_page_id');
    foreach ($ids as $value) {
        $pageData = $emPage->getOneLogForAdmin((int)$value);
        if (!$pageData || $pageData['type'] !== 'page') {
            continue;
        }
        $emPage->deleteLog($value);
        // 如果被删除的页面是首页，需要恢复默认首页
        if ($home_page_id == $value) {
            Option::updateOption('home_page_id', 0);
        }
    }
    $CACHE->updateCache(array('options', 'sta', 'comment', 'logalias'));
    output::ok();
}

if ($action == 'operate_page') {
    $operate = isset($_POST['operate']) ? $_POST['operate'] : '';
    $pages = isset($_POST['page']) ? array_map('intval', $_POST['page']) : array();

    LoginAuth::checkToken();

    $emPage = new Log_Model();

    switch ($operate) {
        case 'hide':
        case 'pub':
            $ishide = $operate == 'hide' ? 'y' : 'n';
            foreach ($pages as $value) {
                $emPage->hideSwitch($value, $ishide);
            }
            $CACHE->updateCache(array('options', 'sta', 'comment'));
            emDirect("./page.php?active_hide_" . $ishide . "=1");
            break;
    }
}
