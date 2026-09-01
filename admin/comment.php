<?php
/**
 * comments
 *
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

/**
 * @var string $action
 * @var object $CACHE
 */

require_once 'globals.php';

$Comment_Model = new Comment_Model();

if (!$action) {
    $br = '<a href="./">数据中心</a><a href="./article.php">博客管理</a><a><cite>评论管理</cite></a>';

    include View::getAdmView(User::haveEditPermission() ? 'header' : 'uc_header');
    require_once(View::getAdmView('comment'));
    include View::getAdmView(User::haveEditPermission() ? 'footer' : 'uc_footer');
    View::output();
}

// layui table AJAX data
if ($action === 'index') {
    $blogId = Input::getIntVar('gid');
    $uid = Input::getIntVar('uid');
    $hide = Input::getStrVar('hide');
    $keyword = trim((string)Input::getStrVar('keyword'));
    if (!in_array($hide, ['y', 'n'], true)) {
        $hide = '';
    }
    $page = Input::getIntVar('page', 1);
    $page = max(1, $page);
    $perpage_num = Input::getIntVar('limit');
    if ($perpage_num <= 0) {
        $perpage_num = (int)Option::get('admin_article_perpage_num');
        if ($perpage_num <= 0) $perpage_num = 20;
    }

    $comment = $Comment_Model->getCommentsForAdmin($blogId, $uid, $hide, $page, $keyword, $perpage_num);
    $cmnum = $Comment_Model->getCommentNum($blogId, $uid, $hide, $keyword);

    $data = [];
    foreach ($comment as $value) {
        $cid = (int)($value['cid'] ?? 0);
        $gid = (int)($value['gid'] ?? 0);
        $posterName = $value['poster'] ?? '';
        $mailVal = htmlspecialchars($value['mail'] ?? '', ENT_QUOTES);
        $ipVal = htmlspecialchars(trim((string)($value['ip'] ?? '')), ENT_QUOTES);
        $data[] = [
            'cid'       => $cid,
            'gid'       => $gid,
            'comment'   => $value['comment'] ?? '',
            'poster'    => $posterName,
            'uid'       => (int)($value['uid'] ?? 0),
            'mail'      => $mailVal,
            'ip'        => $ipVal,
            'os'        => htmlspecialchars((string)($value['os'] ?? ''), ENT_QUOTES),
            'browse'    => htmlspecialchars((string)($value['browse'] ?? ''), ENT_QUOTES),
            'title'     => htmlspecialchars(subString($value['title'] ?? '', 0, 42), ENT_QUOTES),
            'log_url'   => Url::log($gid),
            'date'      => htmlspecialchars((string)($value['date'] ?? ''), ENT_QUOTES),
            'hide'      => ($value['hide'] ?? 'n') === 'y' ? 'y' : 'n',
            'top'       => ($value['top'] ?? 'n') === 'y' ? 'y' : 'n',
        ];
    }
    Output::data($data, (int)$cmnum);
}

// AJAX batch operations
if ($action === 'batch') {
    LoginAuth::checkToken();
    $operate = Input::postStrVar('operate');
    $ids = Input::postStrVar('ids');
    $ids = array_filter(array_map('intval', explode(',', $ids)));

    if (empty($ids)) {
        Output::error('请选择要操作的评论');
    }

    switch ($operate) {
        case 'del':
            $Comment_Model->batchComment('delcom', $ids);
            $CACHE->updateCache(array('sta', 'comment'));
            Output::ok('删除成功');
            break;
        case 'hide':
            $Comment_Model->batchComment('hidecom', $ids);
            $CACHE->updateCache(array('sta', 'comment'));
            Output::ok('隐藏成功');
            break;
        case 'pub':
            $Comment_Model->batchComment('showcom', $ids);
            $CACHE->updateCache(array('sta', 'comment'));
            Output::ok('审核成功');
            break;
        case 'top':
            $Comment_Model->batchComment('top', $ids);
            $CACHE->updateCache('comment');
            Output::ok('置顶成功');
            break;
        case 'untop':
            $Comment_Model->batchComment('untop', $ids);
            $CACHE->updateCache('comment');
            Output::ok('取消置顶成功');
            break;
        default:
            Output::error('未知操作');
    }
}

// AJAX reply
if ($action === 'ajaxreply') {
    LoginAuth::checkToken();
    $reply = Input::postStrVar('reply');
    $commentId = Input::postIntVar('cid');
    $hideVal = Input::postStrVar('hide', 'n');
    if (!in_array($hideVal, ['y', 'n'], true)) {
        $hideVal = 'n';
    }
    if (empty($reply)) {
        Output::error('回复内容不能为空');
    }
    if (strlen($reply) > 60000) {
        Output::error('内容过长');
    }
    $Comment_Model->isYoursComment($commentId);
    $comment = $Comment_Model->getOneComment($commentId);
    if (!$comment) {
        Output::error('评论不存在');
    }
    if ($hideVal == 'y') {
        $Comment_Model->showComment($commentId);
        $hideVal = 'n';
    }
    $blogId = isset($comment['gid']) ? (int)$comment['gid'] : null;
    $content = '@' . addslashes($comment['poster']) . '：' . $reply;
    $Comment_Model->replyComment($blogId, $commentId, $content, $hideVal);
    notice::sendNewCommentMail($reply, $blogId, $commentId);
    $CACHE->updateCache('comment');
    $CACHE->updateCache('sta');
    doAction('comment_reply', $commentId, $reply);
    Output::ok('回复成功');
}

// AJAX delete by IP
if ($action === 'ajaxdelbyip') {
    LoginAuth::checkToken();
    if (!User::haveEditPermission()) {
        Output::error('权限不足');
    }
    $ip = Input::postStrVar('ip');
    if ($ip === '') {
        Output::error('IP不能为空');
    }
    $Comment_Model->delCommentByIp($ip);
    $CACHE->updateCache(array('sta', 'comment'));
    Output::ok('删除成功');
}

if (in_array($action, ['show', 'hide', 'top', 'untop'], true)) {
    LoginAuth::checkToken();
    $id = Input::getIntVar('id');
    if ($id <= 0) {
        emDirect("./comment.php?error_a=1");
    }
    if ($action === 'show') {
        $Comment_Model->showComment($id);
        $CACHE->updateCache(array('sta', 'comment'));
        emDirect("./comment.php?active_show=1");
    }
    if ($action === 'hide') {
        $Comment_Model->hideComment($id);
        $CACHE->updateCache(array('sta', 'comment'));
        emDirect("./comment.php?active_hide=1");
    }
    if ($action === 'top') {
        $Comment_Model->topComment($id);
        $CACHE->updateCache('comment');
        emDirect("./comment.php?active_top=1");
    }
    if ($action === 'untop') {
        $Comment_Model->topComment($id, 'n');
        $CACHE->updateCache('comment');
        emDirect("./comment.php?active_untop=1");
    }
}

if ($action === 'del') {
    $id = Input::getIntVar('id');

    LoginAuth::checkToken();

    $Comment_Model->delComment($id);
    $CACHE->updateCache(array('sta', 'comment'));
    emDirect("./comment.php?active_del=1");
}

if ($action === 'delbyip') {
    LoginAuth::checkToken();
    if (!User::haveEditPermission()) {
        emMsg('权限不足！', './');
    }
    $ip = Input::getStrVar('ip');
    if ($ip === '') {
        emDirect("./comment.php?error_a=1");
    }
    $Comment_Model->delCommentByIp($ip);
    $CACHE->updateCache(array('sta', 'comment'));
    emDirect("./comment.php?active_del=1");
}

if ($action === 'batch_operation') {
    LoginAuth::checkToken();
    $operate = Input::postStrVar('operate');
    $comments = isset($_POST['com']) ? array_map('intval', $_POST['com']) : [];

    if (empty($comments)) {
        emDirect("./comment.php?error_a=1");
    }

    switch ($operate) {
        case 'del' :
            $Comment_Model->batchComment('delcom', $comments);
            $CACHE->updateCache(array('sta', 'comment'));
            emDirect("./comment.php?active_del=1");
            break;
        case 'hide':
            $Comment_Model->batchComment('hidecom', $comments);
            $CACHE->updateCache(array('sta', 'comment'));
            emDirect("./comment.php?active_hide=1");
            break;
        case 'pub':
            $Comment_Model->batchComment('showcom', $comments);
            $CACHE->updateCache(array('sta', 'comment'));
            emDirect("./comment.php?active_show=1");
            break;
        case 'top':
            $Comment_Model->batchComment('top', $comments);
            $CACHE->updateCache('comment');
            emDirect("./comment.php?active_top=1");
            break;
        case 'untop':
            $Comment_Model->batchComment('untop', $comments);
            $CACHE->updateCache('comment');
            emDirect("./comment.php?active_untop=1");
            break;
        default:
            emDirect("./comment.php?error_b=1");
    }
}

if ($action === 'doreply') {
    LoginAuth::checkToken();
    $reply = Input::postStrVar('reply');
    $commentId = Input::postIntVar('cid');
    $hide = Input::postStrVar('hide', 'n');
    if (!in_array($hide, ['y', 'n'], true)) {
        $hide = 'n';
    }

    if (empty($reply)) {
        emDirect("./comment.php?error_c=1");
    }
    if (strlen($reply) > 60000) {
        emDirect("./comment.php?error_d=1");
    }

    $Comment_Model->isYoursComment($commentId);
    $comment = $Comment_Model->getOneComment($commentId);
    if (!$comment) {
        emDirect("./comment.php?error_a=1");
    }

    //回复一条待审核的评论，视为要将其公开（包括回复内容）
    if ($hide == 'y') {
        $Comment_Model->showComment($commentId);
        $hide = 'n';
    }

    $blogId = isset($comment['gid']) ? (int)$comment['gid'] : null;
    $content = '@' . addslashes($comment['poster']) . '：' . $reply;

    $Comment_Model->replyComment($blogId, $commentId, $content, $hide);
    notice::sendNewCommentMail($reply, $blogId, $commentId);

    $CACHE->updateCache('comment');
    $CACHE->updateCache('sta');
    doAction('comment_reply', $commentId, $reply);
    emDirect("./comment.php?active_rep=1");
}
