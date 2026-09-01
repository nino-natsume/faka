<?php
/**
 * 侧边栏组件、页面模块
 */
defined('DC_ROOT') || exit('access denied!');



?>

<?php

/**
 * 页顶：导航
 */
function blog_navi() {
    global $CACHE;
    $navi_cache = $CACHE->readCache('navi');
    $_front_tpl_options = _g();
    $_front_tpl_options = is_array($_front_tpl_options) ? $_front_tpl_options : [];
    $nav_items = array_key_exists('nav_items', $_front_tpl_options) ? $_front_tpl_options['nav_items'] : [
        ['name' => '首页', 'url' => '/',     'ri' => 'ri-home-4-line',  'ri_color' => '', 'newtab' => 'n'],
        ['name' => '博客', 'url' => '/blog', 'ri' => 'ri-article-line', 'ri_color' => '', 'newtab' => 'n'],
    ];
    $nav_items = is_array($nav_items) ? $nav_items : [];
    
    $current_uri = $_SERVER['REQUEST_URI'] ?? '/';
    $current_path = parse_url($current_uri, PHP_URL_PATH);
    $current_path = empty($current_path) ? '/' : $current_path;
    $current_path = rtrim($current_path, '/');
    $current_path = $current_path === '' ? '/' : $current_path;
    $current_query = [];
    parse_str((string)parse_url($current_uri, PHP_URL_QUERY), $current_query);

    ?>
        <?php if (!empty($nav_items) && is_array($nav_items)): ?>
            <?php foreach ($nav_items as $index => $item): ?>
                <?php
                $name = isset($item['name']) ? trim((string)$item['name']) : '';
                if ($name === '') {
                    continue;
                }
                $raw_url = isset($item['url']) ? trim((string)$item['url']) : '/';
                if ($raw_url === '') {
                    $raw_url = '/';
                }
                if (!preg_match('#^(https?:)?//#i', $raw_url) && strpos($raw_url, 'javascript:') !== 0) {
                    if ($raw_url[0] !== '/') {
                        $raw_url = '/' . ltrim($raw_url, '/');
                    }
                }
                $newtab = (!empty($item['newtab']) && $item['newtab'] === 'y') ? 'target="_blank"' : '';
                $nav_icon = isset($item['ri']) ? trim((string)$item['ri']) : '';
                $nav_icon_color = isset($item['ri_color']) ? trim((string)$item['ri_color']) : '';
                $icon_style = $nav_icon !== '' ? ' style="margin-right:5px;' . ($nav_icon_color !== '' ? 'color:' . htmlspecialchars($nav_icon_color, ENT_QUOTES) . ';' : '') . '"' : '';
                $is_active = false;
                if (!preg_match('#^(https?:)?//#i', $raw_url)) {
                    $nav_path = parse_url($raw_url, PHP_URL_PATH);
                    $nav_path = empty($nav_path) ? '/' : $nav_path;
                    $nav_path = rtrim($nav_path, '/');
                    $nav_path = $nav_path === '' ? '/' : $nav_path;
                    $nav_query = [];
                    parse_str((string)parse_url($raw_url, PHP_URL_QUERY), $nav_query);
                    if (($nav_path === '/' || $nav_path === '/index.php') && empty($nav_query)) {
                        $is_active = ($current_path === '/' || $current_path === '/index.php') && empty($current_query['sort_id']) && empty($current_query['q']);
                    } elseif (isset($nav_query['sort_id'])) {
                        $is_active = isset($current_query['sort_id']) && (string)$current_query['sort_id'] === (string)$nav_query['sort_id'];
                    } elseif (!empty($nav_query)) {
                        $is_active = ($current_path === $nav_path && http_build_query($current_query) === http_build_query($nav_query));
                    } else {
                        $is_active = ($current_path === $nav_path);
                    }
                }
                ?>
                <li class="li-cate-3 <?= $is_active ? 'active' : '' ?>" id="fk-nav-item-<?= (int)$index ?>">
                    <a href="<?= htmlspecialchars($raw_url, ENT_QUOTES) ?>" <?= $newtab ?>>
                        <?php if ($nav_icon !== ''): ?>
                            <i class="<?= htmlspecialchars($nav_icon, ENT_QUOTES) ?>"<?= $icon_style ?>></i>
                        <?php endif; ?>
                        <?= htmlspecialchars($name) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
        <?php
        foreach ($navi_cache as $value):
            if ($value['pid'] != 0) {
                continue;
            }
            $newtab = $value['newtab'] == 'y' ? 'target="_blank"' : '';
            $nav_url = $value['isdefault'] == 'y' ? DC_URL . $value['url'] : trim((string)$value['url']);
            if ($nav_url !== '' && !preg_match('#^(https?:)?//#i', $nav_url) && strpos($nav_url, 'javascript:') !== 0 && $nav_url[0] !== '/') {
                $nav_url = '/' . ltrim($nav_url, '/');
            }
            
            $is_home_nav = ($value['url'] == '/' || $value['url'] == '' || $value['url'] == 'index.php');
            
            $is_active = false;
            
            if ($is_home_nav) {
                $is_active = ($current_path === '/' || $current_path === '/index.php') && empty($current_query['sort_id']) && empty($current_query['q']);
            } else if (!empty($current_query['sort_id'])) {
                if (isset($value['typeId']) && $value['typeId'] == (int)$current_query['sort_id']) {
                    $is_active = true;
                }
                if (!$is_active && !empty($value['children'])) {
                    foreach ($value['children'] as $child) {
                        if (isset($child['sid']) && $child['sid'] == (int)$current_query['sort_id']) {
                            $is_active = true;
                            break;
                        }
                    }
                }
            }
            
            $active_class = $is_active ? 'active' : '';
            ?>
            <?php if (!empty($value['children']) || !empty($value['childnavi'])) : ?>
            <li class="li-cate-3 <?= $active_class ?>">
                <a href="<?= $nav_url ?>" <?= $newtab ?>>
                    <?php if (!empty($value['naviicon'])): ?>
                        <i class="<?= $value['naviicon'] ?>" style="margin-right: 5px;"></i>
                    <?php endif; ?>
                    <?= $value['naviname'] ?>
                </a>
                <?php if (!empty($value['children'])): ?>
                    <ul class="sub-menu">
                        <?php foreach ($value['children'] as $row) {
                            $child_active = (!empty($current_query['sort_id']) && $current_query['sort_id'] == $row['sid']) ? 'active' : '';
                            echo '<li class="li-cate-8 ' . $child_active . '"><a href="' . Url::sort($row['sid']) . '">' . $row['sortname'] . '</a></li>';
                        } ?>
                    </ul>
                <?php endif ?>
                <?php if (!empty($value['childnavi'])) : ?>
                    <ul class="sub-menu">
                        <?php foreach ($value['childnavi'] as $row) {
                            $newtab = $row['newtab'] == 'y' ? 'target="_blank"' : '';
                            $child_icon = !empty($row['naviicon']) ? '<i class="' . $row['naviicon'] . '" style="margin-right: 5px;"></i>' : '';
                            echo '<li class="li-cate-8"><a href="' . $row['url'] . "\" $newtab >" . $child_icon . $row['naviname'] . '</a></li>';
                        } ?>
                    </ul>
                <?php endif ?>
            </li>
            <?php else: ?>
                <li class="li-cate-3 <?= $active_class ?>">
                    <a href="<?= $nav_url ?>" <?= $newtab ?>>
                        <?php if (!empty($value['naviicon'])): ?>
                            <i class="<?= $value['naviicon'] ?>" style="margin-right: 5px;"></i>
                        <?php endif; ?>
                        <?= $value['naviname'] ?>
                    </a>
                </li>
            <?php endif ?>
        <?php endforeach ?>
        <?php endif; ?>
<?php } ?>
<?php
/**
 * 文章列出卡片：置顶标志
 */
function topflg($top, $sortop = 'n', $sortid = null) {
    $ishome_flg = '<span class="log-topflg" >置顶</span>';
    $issort_flg = '<span class="log-topflg" >分类置顶</span>';
    if (blog_tool_ishome()) {
        echo $top == 'y' ? $ishome_flg : '';
    } elseif ($sortid) {
        echo $sortop == 'y' ? $issort_flg : '';
    }
}

?>
<?php
/**
 * 文章详情页：编辑链接
 */
function editflg($logid, $author) {
    $editflg = User::haveEditPermission() || $author == UID ? '<a href="' . DC_URL . 'admin/article.php?action=edit&gid=' . $logid . '" target="_blank"><span class="iconfont icon-edit"></span></a>' : '';
    echo $editflg;
}

?>
<?php
/**
 * 文章详情页：分类
 */
function blog_sort($sortID) {
    $Sort_Model = new Sort_Model();
    $r = $Sort_Model->getOneSortById($sortID);
    $sortName = isset($r['sortname']) ? $r['sortname'] : '';
    ?>
    <?php if (!empty($sortName)) { ?>
        <a href="<?= Url::sort($sortID) ?>"><?= $sortName ?></a>
    <?php }
} ?>
<?php
/**
 * 首页文章列表：分类
 */
function bloglist_sort($sortID) {
    $Sort_Model = new Sort_Model();
    $r = $Sort_Model->getOneSortById($sortID);
    $sortName = isset($r['sortname']) ? $r['sortname'] : '';
    ?>
    <?php if (!empty($sortName)) { ?>
        <span class="loglist-sort">
            <a href="<?= Url::sort($sortID) ?>"><?= $sortName ?></a>
        </span>
    <?php }
} ?>
<?php
/**
 * 首页文章列表和文章详情页：标签
 */
function blog_tag($blogid) {
    $tag_model = new Tag_Model();
    $tag_ids = $tag_model->getTagIdsFromBlogId($blogid);
    $tag_names = $tag_model->getNamesFromIds($tag_ids);
    if (!empty($tag_names)) {
        $tag = '';
        foreach ($tag_names as $value) {
            $tag .= "    <a href=\"" . Url::tag(rawurlencode($value)) . "\" class='tags' title='标签' >" . htmlspecialchars($value) . '</a>';
        }
        echo $tag;
    }
}

?>
<?php
/**
 * 首页文章列表和文章详情页：作者
 */
function blog_author($uid) {
    $User_Model = new User_Model();
    $user_info = $User_Model->getOneUser($uid);
    $author = $user_info['nickname'];
    echo '<a href="' . Url::author($uid) . "\">$author</a>";
}

?>
<?php
/**
 * 文章详情页：相邻文章
 */
function neighbor_log($neighborLog) {
    extract($neighborLog) ?>
    <?php if ($prevLog): ?>
        <span class="prev-log"><a href="<?= Url::log($prevLog['gid']) ?>" title="上一篇：<?= $prevLog['title'] ?>"><span class="iconfont icon-prev"></span></a></span>
    <?php endif ?>
    <?php if ($nextLog): ?>
        <span class="next-log"><a href="<?= Url::log($nextLog['gid']) ?>" title="下一篇：<?= $nextLog['title'] ?>"><span class="iconfont icon-next"></span></a></span>
    <?php endif ?>
<?php } ?>
<?php
/**
 * 文章详情页：评论列表
 */
function blog_comments($comments, $comnum) {
    extract($comments);
    if ($commentStacks): ?>
        <div class="comment-header"><b>收到<?= $comnum ?>条评论</b></div>
    <?php endif ?>
    <?php
    foreach ($commentStacks as $cid):
        $comment = $comments[$cid];
        ?>
        <div class="comment" id="<?= $comment['cid'] ?>">
            <?php
            $avatar = getEmUserAvatar($comment['uid'], $comment['mail']);
            ?>
            <div class="avatar"><img src="<?= $avatar ?>" alt="avatar"/></div>
            <div class="comment-infos">
                <div class="arrow"></div>
                <b><?= $comment['poster'] ?> </b><span class="comment-time"><?= $comment['date'] ?></span>
                <div class="comment-content"><?= $comment['content'] ?></div>
                <div class="comment-reply">
                    <span class="com-reply">回复</span>
                </div>
            </div>
            <?php blog_comments_children($comments, $comment['children']) ?>
        </div>
    <?php endforeach ?>
    <div id="pagenavi">
        <?= $commentPageUrl ?>
    </div>
<?php } ?>
<?php
/**
 * 文章详情页：子评论
 */
function blog_comments_children($comments, $children) {
    foreach ($children as $child):
        $comment = $comments[$child];
        ?>
        <div class="comment comment-children" id="<?= $comment['cid'] ?>">
            <?php
            $avatar = getEmUserAvatar($comment['uid'], $comment['mail']);
            ?>
            <div class="avatar"><img src="<?= $avatar ?>" alt="commentator"/></div>
            <div class="comment-infos">
                <div class="arrow"></div>
                <b><?= $comment['poster'] ?> </b><span class="comment-time"><?= $comment['date'] ?></span>
                <div class="comment-content"><?= $comment['content'] ?></div>
                <?php if ($comment['level'] < 4): ?>
                    <div class="comment-reply">
                        <span class="com-reply comment-replay-btn">回复</span>
                    </div>
                <?php endif ?>
            </div>
            <?php blog_comments_children($comments, $comment['children']) ?>
        </div>
    <?php endforeach ?>
<?php } ?>
<?php
/**
 * 文章详情页：评论表单
 */
function blog_comments_post($logid, $ckname, $ckmail, $ckurl, $verifyCode, $allow_remark) {
    $isLoginComment = Option::get('login_comment');
    $commentLoginRedirect = function_exists('dcGetCurrentUrl') ? dcGetCurrentUrl() : DC_URL . ltrim($_SERVER['REQUEST_URI'] ?? '', '/');
    $commentLoginUrl = function_exists('dcGetUserLoginUrl') ? dcGetUserLoginUrl($commentLoginRedirect) : DC_URL . 'user/account.php?action=signin';
    if ($allow_remark == 'y'): ?>
        <div id="comments">
            <div class="comment-post" id="comment-post">
                <form class="commentform" method="post" name="commentform" action="<?= DC_URL ?>index.php?action=addcom" id="commentform">
                    <input type="hidden" name="gid" value="<?= $logid ?>"/>
                    <textarea class="form-control log_comment" name="comment" id="comment" rows="10" tabindex="4" placeholder="撰写评论" required></textarea>
                    <?php if (User::isVisitor() && $isLoginComment === 'n'): ?>
                        <div class="comment-info" id="comment-info">
                            <input class="form-control com_control comment-name" id="info_n" autocomplete="off" type="text" name="comname" maxlength="49"
                                   value="<?= $ckname ?>" size="22"
                                   tabindex="1" placeholder="昵称*" required/>
                            <input class="form-control com_control comment-mail" id="info_m" autocomplete="off" type="email" name="commail" maxlength="128"
                                   value="<?= $ckmail ?>" size="22"
                                   tabindex="2" placeholder="邮箱"/>
                        </div>
                    <?php endif ?>
                    <span class="com_submit_p">
                        <?php if (User::isVisitor() && $isLoginComment === 'y'): ?>
                            请先 <a href="<?= htmlspecialchars($commentLoginUrl, ENT_QUOTES) ?>">登录</a> 再评论
                        <?php else: ?>
                            <input class="btn"<?php if ($verifyCode != "") { ?> type="button" data-toggle="modal" data-target="#myModal"<?php } else { ?> type="submit" <?php } ?>
                               id="comment_submit" value="发布评论" tabindex="6"/>
                        <?php endif; ?>
                    </span>
                    <?php if ($verifyCode != "") { ?>
                        <div class="modal" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content" style="display: table-cell;">
                                    <input type="hidden" id="DC_URL" value="<?= DC_URL ?>"/>
                                    <div class="modal-header" style="border-bottom: 0;">输入验证码</div>
                                    <?= $verifyCode ?>
                                    <div class="modal-footer" style="border-top: 0;">
                                        <button type="button" class="btn" id="close-modal" data-dismiss="modal">关闭</button>
                                        <button type="submit" class="btn" id="comment_submit2">提交</button>
                                    </div>
                                </div>
                            </div>
                            <div class="lock-screen"></div>
                        </div>
                    <?php } ?>
                    <input type="hidden" name="pid" id="comment-pid" value="0" tabindex="1"/>
                </form>
            </div>
        </div>
    <?php endif ?>
<?php } ?>


<?php
/**
 * 判断函数：是否是首页
 */
function blog_tool_ishome() {
    if (DC_URL . trim(Dispatcher::setPath(), '/') == DC_URL) {
        return true;
    } else {
        return FALSE;
    }
}

?>
<?php
function getEmUserAvatar($uid, $mail) {
    $avatar = '';
    if ($uid) {
        $userModel = new User_Model();
        $user = $userModel->getOneUser($uid);
        $avatar = $user['photo'];
    } elseif ($mail) {
        $avatar = getGravatar($mail);
    }
    return $avatar ?: DC_URL . "admin/views/images/avatar.svg";
}

?>