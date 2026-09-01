<?php


require_once 'globals.php';

$db = Database::getInstance();
$db_prefix = DB_PREFIX;
$timestamp = time();

function stationEnsureSlugColumn($db, $db_prefix) {
    $table = $db_prefix . 'station';
    $has_slug_col = $db->once_fetch_array("SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$table}' AND COLUMN_NAME = 'slug'");
    if ($has_slug_col && (int)$has_slug_col['cnt'] > 0) {
        return true;
    }

    $db->query("ALTER TABLE `{$table}` ADD COLUMN `slug` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '路径后缀标识（/s/{slug}）'");
    return true;
}

function stationEnsureSiteSubtitleColumn($db, $db_prefix) {
    $table = $db_prefix . 'station';
    $has_col = $db->once_fetch_array("SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$table}' AND COLUMN_NAME = 'site_subtitle'");
    if ($has_col && (int)$has_col['cnt'] > 0) {
        return true;
    }

    $db->query("ALTER TABLE `{$table}` ADD COLUMN `site_subtitle` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '网站副标题' AFTER `title`");
    return true;
}

function stationEnsureStatusColumn($db, $db_prefix) {
    $table = $db_prefix . 'station';
    $has_col = $db->once_fetch_array("SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$table}' AND COLUMN_NAME = 'status'");
    if ($has_col && (int)$has_col['cnt'] > 0) {
        return true;
    }

    $db->query("ALTER TABLE `{$table}` ADD COLUMN `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '分店状态：1启用 0停用' AFTER `level_id`");
    return true;
}

function stationEnsureLevelIconColumns($db, $db_prefix) {
    static $checked = false;
    if ($checked) return true;
    $checked = true;

    $table = $db_prefix . 'station_level';
    $colRows = $db->fetch_all("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$table}' AND COLUMN_NAME IN ('icon','icon_image')");
    $cols = [];
    foreach ($colRows as $row) {
        $cols[] = $row['COLUMN_NAME'];
    }
    if (!in_array('icon', $cols, true)) {
        $db->query("ALTER TABLE `{$table}` ADD COLUMN `icon` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ri-store-2-line' COMMENT 'Remix Icon图标' AFTER `name`");
    }
    if (!in_array('icon_image', $cols, true)) {
        $db->query("ALTER TABLE `{$table}` ADD COLUMN `icon_image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分店等级图片图标' AFTER `icon`");
    }
    return true;
}

function stationNormalizeLevelIcon($icon) {
    $icon = trim((string)$icon);
    if ($icon === '') $icon = 'ri-store-2-line';
    if (!preg_match('/^ri-[a-z0-9-]+-(line|fill)$/', $icon)) $icon = 'ri-store-2-line';
    return $icon;
}

// 演示站：分站管理允许浏览与查看配置，但禁止提交编辑保存，避免改动演示数据。
if (Register::isDemoSite() && in_array($action, ['lists_edit_ajax', 'level_edit_ajax'], true)) {
    Ret::error('演示站点无法进行该操作！');
}

if ($action == 'lists') {
    stationEnsureStatusColumn($db, $db_prefix);
    $br = '<a href="./">数据中心</a><a><cite>分店管理</cite></a>';
    include View::getAdmView('header');
    require_once View::getAdmView('station/lists');
    include View::getAdmView('footer');
    View::output();
}

if ($action == 'lists_index') {
    stationEnsureStatusColumn($db, $db_prefix);
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    $level_filter = isset($_GET['level_id']) ? intval($_GET['level_id']) : 0;

    // 检测 slug 列是否存在
    $has_slug = $db->once_fetch_array("SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$db_prefix}station' AND COLUMN_NAME = 'slug'");
    $has_slug = $has_slug && $has_slug['cnt'] > 0;

    $where = "s.delete_time is null";
    if ($keyword !== '') {
        $kw = addslashes($keyword);
        $slug_search = $has_slug ? " OR s.slug LIKE '%{$kw}%'" : '';
        $where .= " AND (s.id = '{$kw}' OR s.name LIKE '%{$kw}%' OR s.domain LIKE '%{$kw}%' OR s.domain_2 LIKE '%{$kw}%'{$slug_search} OR u.tel LIKE '%{$kw}%' OR u.email LIKE '%{$kw}%' OR u.username LIKE '%{$kw}%')";
    }
    if ($level_filter > 0) {
        $where .= " AND s.level_id = {$level_filter}";
    }

    $slug_col = $has_slug ? 's.slug,' : '';
    $sql = "SELECT s.id, s.user_id, s.name, s.title, s.level_id, s.status, {$slug_col}
                   sl.name AS level_name,
                   u.tel, u.email, u.username,
                   s.domain, s.domain_2, s.domain_2_prefix, s.domain_2_suffix,
                   s.master_sort, s.master_goods, s.create_time,
                   IFNULL((SELECT COUNT(*) FROM {$db_prefix}order o WHERE o.station_id = s.id AND o.pay_status = 1 AND o.delete_time IS NULL AND (o.device IS NULL OR o.device NOT IN ('level_upgrade','balance_recharge','station_upgrade'))), 0) AS order_count,
                   IFNULL((SELECT SUM(amount) FROM {$db_prefix}user_log ul WHERE ul.uid = s.user_id AND ul.type = 'station_commission'), 0) AS total_commission
            FROM {$db_prefix}station s 
            LEFT JOIN {$db_prefix}station_level sl ON s.level_id = sl.id 
            LEFT JOIN {$db_prefix}user u ON s.user_id = u.uid 
            WHERE {$where}
            ORDER BY s.id DESC";
    $list = $db->fetch_all($sql);

    foreach ($list as &$val) {
        $val['create_time'] = date('Y-m-d H:i', $val['create_time']);
        $val['user_info'] = $val['username'] ?: ($val['tel'] ?: ($val['email'] ?: '-'));
        $val['order_count'] = (int)($val['order_count'] ?? 0);
        $val['total_commission'] = number_format((float)($val['total_commission'] ?? 0), 2);
        $val['status'] = (int)($val['status'] ?? 1);
        if (!$has_slug) $val['slug'] = '';
    }

    output::data($list, count($list));
}

if($action == 'lists_del'){
    $ids = Input::postStrVar('ids');
    $ids = explode(',', $ids);
    foreach($ids as $id){
        $update = [
            'delete_time' => time()
        ];
        $db->update('station', $update, ['id' => $id]);
    }
    Ret::success('删除成功');
}

if ($action == 'lists_switch_status') {
    LoginAuth::checkToken();
    stationEnsureStatusColumn($db, $db_prefix);
    $id = Input::postIntVar('id');
    $status = Input::postStrVar('status') === '1' ? 1 : 0;
    if ($id <= 0) {
        Ret::error('参数错误');
    }
    $station = $db->once_fetch_array("SELECT id FROM {$db_prefix}station WHERE id = {$id} AND delete_time IS NULL LIMIT 1");
    if (empty($station)) {
        Ret::error('分店不存在或已删除');
    }
    $db->update('station', ['status' => $status], ['id' => $id]);
    Ret::success('操作成功');
}

if ($action == 'lists_add_ajax') {
    LoginAuth::checkToken();
    stationEnsureStatusColumn($db, $db_prefix);
    $user_id = Input::postIntVar('user_id');
    $level_id = Input::postIntVar('level_id');
    $name = Input::postStrVar('name');
    $title = Input::postStrVar('title');
    $site_subtitle = Input::postStrVar('site_subtitle');

    if ($user_id <= 0) Ret::error('请输入用户 UID');
    $user = $db->once_fetch_array("SELECT uid, username FROM {$db_prefix}user WHERE uid = {$user_id}");
    if (empty($user)) Ret::error('用户 UID 不存在');

    // 检查是否已有分店
    $exist = $db->once_fetch_array("SELECT id FROM {$db_prefix}station WHERE user_id = {$user_id} AND delete_time IS NULL");
    if ($exist) Ret::error('该用户已拥有分店（ID:' . $exist['id'] . '）');

    if (empty($name)) $name = $user['username'] . '的店铺';
    if (empty($title)) $title = $name;
    if ($level_id <= 0) Ret::error('请选择分店等级');
    try {
        $has_site_subtitle_col = stationEnsureSiteSubtitleColumn($db, $db_prefix);
    } catch (Throwable $e) {
        Ret::error('网站副标题字段初始化失败，请先执行数据库迁移');
    }

    $station_unique = md5(uniqid(mt_rand(), true));
    $data = [
        'user_id' => $user_id,
        'level_id' => $level_id,
        'name' => $name,
        'title' => $title,
        'status' => 1,
        'station_unique' => $station_unique,
        'create_time' => $timestamp,
    ];
    if ($has_site_subtitle_col) {
        $data['site_subtitle'] = $site_subtitle;
    }
    $db->add('station', $data);
    Ret::success('添加成功');
}

if ($action == 'lists_edit') {
    $id = Input::getIntVar('id');
    $sql = "SELECT s.*, sl.name AS level_name, u.username, u.tel, u.email
            FROM {$db_prefix}station s
            LEFT JOIN {$db_prefix}station_level sl ON s.level_id = sl.id
            LEFT JOIN {$db_prefix}user u ON s.user_id = u.uid
            WHERE s.id = {$id}";
    $info = $db->once_fetch_array($sql);
    if (empty($info)) { echo '分店不存在'; exit; }

    $levels = $db->fetch_all("SELECT id, name FROM {$db_prefix}station_level ORDER BY sort ASC, id ASC");

    $options_cache = $CACHE->readCache('options');
    $station_domain_list = isset($options_cache['station_domain']) ? preg_split("/\r?\n/", $options_cache['station_domain']) : [];
    $station_slug_mode = isset($options_cache['station_slug_mode']) ? $options_cache['station_slug_mode'] : '0';

    include View::getAdmView('open_head');
    require_once(View::getAdmView('station/lists_edit'));
    include View::getAdmView('open_foot');
    View::output();
}

if ($action == 'lists_edit_ajax') {
    ob_end_clean(); // clear any buffered output
    header('Content-Type: application/json; charset=UTF-8');
    LoginAuth::checkToken();
    $id = Input::postIntVar('id');
    if ($id <= 0) Ret::error('参数错误');

    $old = $db->once_fetch_array("SELECT * FROM {$db_prefix}station WHERE id = {$id} AND delete_time IS NULL");
    if (empty($old)) Ret::error('分店不存在');

    $name = Input::postStrVar('name');
    $title = Input::postStrVar('title');
    $site_subtitle = Input::postStrVar('site_subtitle');
    $level_id = Input::postIntVar('level_id');
    $domain = trim(Input::postStrVar('domain'));
    $domain_2_prefix = trim(Input::postStrVar('domain_2_prefix'));
    $domain_2_suffix = trim(Input::postStrVar('domain_2_suffix'));
    $slug = trim(Input::postStrVar('slug'));
    $roll_notice = Input::postStrVar('roll_notice');
    $home_notice = Input::postStrVar('home_notice');

    if (empty($name)) Ret::error('店铺名称不能为空');

    try {
        $has_slug_col = stationEnsureSlugColumn($db, $db_prefix);
        $has_site_subtitle_col = stationEnsureSiteSubtitleColumn($db, $db_prefix);
    } catch (Throwable $e) {
        Ret::error('分店字段初始化失败，请先执行数据库迁移');
    }

    // slug 唯一性校验
    if ($has_slug_col && !empty($slug)) {
        if (!preg_match('/^[a-zA-Z0-9_-]{2,50}$/', $slug)) {
            Ret::error('标识码只能包含字母、数字、下划线和连字符，长度 2-50 位');
        }
        $dup = $db->once_fetch_array("SELECT id FROM {$db_prefix}station WHERE slug = '" . $db->escape_string($slug) . "' AND id != {$id}");
        if ($dup) Ret::error('标识码已被其他分店使用');
    }

    // 二级域名前缀唯一性
    if (!empty($domain_2_prefix) && !empty($domain_2_suffix)) {
        $dup = $db->once_fetch_array("SELECT id FROM {$db_prefix}station WHERE domain_2_prefix = '" . addslashes($domain_2_prefix) . "' AND domain_2_suffix = '" . addslashes($domain_2_suffix) . "' AND id != {$id}");
        if ($dup) Ret::error('二级域名前缀已被占用');
    }

    $update = [
        'name' => $name,
        'title' => $title,
        'level_id' => $level_id,
        'domain' => $domain,
        'domain_2_prefix' => $domain_2_prefix,
        'domain_2_suffix' => $domain_2_suffix,
        'domain_2' => $domain_2_prefix . $domain_2_suffix,
        'roll_notice' => $roll_notice,
        'home_notice' => $home_notice,
    ];
    if ($has_site_subtitle_col) {
        $update['site_subtitle'] = $site_subtitle;
    }
    // slug 列可能尚未迁移
    if ($has_slug_col) {
        $update['slug'] = $slug !== '' ? $slug : null;
    }
    $db->update('station', $update, ['id' => $id]);
    Ret::success('保存成功');
}

if ($action == 'level') {
    $br = '<a href="./">数据中心</a><a href="./station.php">分店管理</a><a><cite>分店等级</cite></a>';
    include View::getAdmView('header');
    require_once View::getAdmView('station/level');
    include View::getAdmView('footer');
    View::output();
}

if ($action == 'level_index') {
    stationEnsureLevelIconColumns($db, $db_prefix);
    $sql = "select * from {$db_prefix}station_level order by sort asc, id asc";
    $list = $db->fetch_all($sql);

    $memberMap = [];
    $memberRows = $db->fetch_all("SELECT id, name FROM {$db_prefix}member");
    foreach ($memberRows as $mr) { $memberMap[(int)$mr['id']] = $mr['name']; }

    foreach($list as &$val){
        $val['service_change_fmt'] = ($val['service_change'] * 100) . '%';
        $gate = intval($val['member_gate'] ?? 0);
        $val['member_gate'] = $gate;
        $val['member_gate_name'] = $gate > 0 ? ($memberMap[$gate] ?? '未知#'.$gate) : '不限制';
        // 自动升级摘要
        $upParts = [];
        if ((float)($val['upgrade_sales_amount'] ?? 0) > 0) $upParts[] = '销售≥' . (float)$val['upgrade_sales_amount'] . '元';
        if ((int)($val['upgrade_order_count'] ?? 0) > 0) $upParts[] = '订单≥' . (int)$val['upgrade_order_count'] . '单';
        if ((int)($val['upgrade_days'] ?? 0) > 0) $upParts[] = '运营≥' . (int)$val['upgrade_days'] . '天';
        if ((int)($val['upgrade_sub_count'] ?? 0) > 0) $upParts[] = '下级≥' . (int)$val['upgrade_sub_count'] . '店';
        if (!empty($upParts)) {
            $modeText = ($val['upgrade_mode'] ?? 'any') === 'all' ? '全部满足' : '任一满足';
            $val['upgrade_desc'] = implode('、', $upParts) . '（' . $modeText . '）';
        } else {
            $val['upgrade_desc'] = '未配置';
        }
    }

    output::data($list, count($list));
}
if($action == 'level_add'){
    stationEnsureLevelIconColumns($db, $db_prefix);
    $memberModel = new Member_Model();
    $memberLevels = $memberModel->getMembersAll();
    include View::getAdmView('open_head');
    require_once(View::getAdmView('station/level_add'));
    include View::getAdmView('open_foot');
    View::output();
}
if($action == 'level_edit'){
    stationEnsureLevelIconColumns($db, $db_prefix);
    $id = Input::getIntVar('id');
    $sql = "select * from {$db_prefix}station_level where id={$id}";
    $info = $db->once_fetch_array($sql);
    $memberModel = new Member_Model();
    $memberLevels = $memberModel->getMembersAll();

    include View::getAdmView('open_head');
    require_once(View::getAdmView('station/level_edit'));
    include View::getAdmView('open_foot');
    View::output();
}
if ($action == 'level_upload_icon') {
    LoginAuth::checkToken();
    $ret = uploadCropImg();
    $mediaModel = new Media_Model();
    $mediaModel->addMedia($ret['file_info']);
    Output::ok($ret['file_info']['file_path']);
}
if($action == 'level_switch_using'){
    $id = Input::postIntVar('id');
    $status = Input::postStrVar('status');
    $data = [
        'using' => $status === 'y' ? 'y' : 'n',
        'update_time' => $timestamp
    ];
    $db->update('station_level', $data, ['id' => $id]);
    Ret::success('操作成功');
}
if($action == 'level_switch_isdomain'){
    $id = Input::postIntVar('id');
    $status = Input::postStrVar('status');
    $data = [
        'is_domain' => $status,
        'update_time' => $timestamp
    ];
    $db->update('station_level', $data, ['id' => $id]);
    Ret::success('操作成功');
}
if($action == 'level_del'){
    $ids = Input::postStrVar('ids');
    $db->del('station_level', $ids);
    Ret::success('操作成功');
}

if($action == 'level_edit_ajax'){
    stationEnsureLevelIconColumns($db, $db_prefix);
    $id = Input::postIntVar('id');

    $name = Input::postStrVar('name');
    $price = Input::postStrVar('price');
    $sort = Input::postIntVar('sort');
    $description = Input::postStrVar('description');
    $service_change = Input::postStrVar('service_change');
    if(empty($name)){
        Ret::error('请输入分店等级名称');
    }
    $price = empty($price) || $price < 0 ? 0 : $price;
    $sort = max(0, (int)$sort);
    $service_change = empty($service_change) || $service_change < 0 ? 0 : $service_change;

    $permFields = ['is_domain','perm_setprice','perm_goodsstate','perm_tpl','perm_config'];
    $data = [
        'name' => $name,
        'icon' => stationNormalizeLevelIcon(Input::postStrVar('icon', 'ri-store-2-line')),
        'icon_image' => Input::postStrVar('icon_image'),
        'sort' => $sort,
        'price' => $price,
        'description' => $description,
        'member_gate' => max(0, Input::postIntVar('member_gate', 0)),
        'service_change' => $service_change,
        'upgrade_mode' => in_array(Input::postStrVar('upgrade_mode'), ['any','all']) ? Input::postStrVar('upgrade_mode') : 'any',
        'upgrade_sales_amount' => max(0, (float)Input::postStrVar('upgrade_sales_amount')),
        'upgrade_order_count' => max(0, Input::postIntVar('upgrade_order_count')),
        'upgrade_days' => max(0, Input::postIntVar('upgrade_days')),
        'upgrade_sub_count' => max(0, Input::postIntVar('upgrade_sub_count')),
        'update_time' => $timestamp
    ];
    foreach ($permFields as $f) {
        $data[$f] = Input::postStrVar($f) === 'y' ? 'y' : 'n';
    }
    $db->update('station_level', $data, ['id' => $id]);
    Ret::success('编辑成功');
}

if($action == 'level_add_ajax'){
    stationEnsureLevelIconColumns($db, $db_prefix);
    $name = Input::postStrVar('name');
    $price = Input::postStrVar('price');
    $sort = Input::postIntVar('sort');
    $description = Input::postStrVar('description');
    $service_change = Input::postStrVar('service_change');
    if(empty($name)){
        Ret::error('请输入分店等级名称');
    }
    $price = empty($price) || $price < 0 ? 0 : $price;
    $sort = max(0, (int)$sort);
    $service_change = empty($service_change) || $service_change < 0 ? 0 : $service_change;

    $permFields = ['is_domain','perm_setprice','perm_goodsstate','perm_tpl','perm_config'];
    $data = [
        'name' => $name,
        'icon' => stationNormalizeLevelIcon(Input::postStrVar('icon', 'ri-store-2-line')),
        'icon_image' => Input::postStrVar('icon_image'),
        'sort' => $sort,
        'price' => $price,
        'description' => $description,
        'member_gate' => max(0, Input::postIntVar('member_gate', 0)),
        'service_change' => $service_change,
        'using' => 'y',
        'upgrade_mode' => in_array(Input::postStrVar('upgrade_mode'), ['any','all']) ? Input::postStrVar('upgrade_mode') : 'any',
        'upgrade_sales_amount' => max(0, (float)Input::postStrVar('upgrade_sales_amount')),
        'upgrade_order_count' => max(0, Input::postIntVar('upgrade_order_count')),
        'upgrade_days' => max(0, Input::postIntVar('upgrade_days')),
        'upgrade_sub_count' => max(0, Input::postIntVar('upgrade_sub_count')),
        'create_time' => $timestamp
    ];
    foreach ($permFields as $f) {
        $data[$f] = Input::postStrVar($f) === 'y' ? 'y' : 'n';
    }
    $db->add('station_level', $data);

    Ret::success('添加成功');
}


if ($action == 'level_reset_ajax') {
    header('Content-Type: application/json; charset=UTF-8');
    stationEnsureLevelIconColumns($db, $db_prefix);
    // token 校验（AJAX 场景避免 emMsg 输出 HTML）
    $reqToken = isset($_REQUEST['token']) ? addslashes($_REQUEST['token']) : '';
    $sesToken = LoginAuth::getToken();
    if (!empty($sesToken) && $reqToken !== $sesToken) {
        output::error('安全token校验失败，请刷新页面重试');
    }
    if (Input::postIntVar('confirm_reset') !== 1) output::error('请确认操作');
    $preset = Input::postStrVar('preset', 'a');
    if (!in_array($preset, ['a', 'b', 'c'], true)) $preset = 'a';

    $presets = [
        // 预设 A：基础版（2个等级）
        'a' => [
            ['name' => '基础分店', 'icon' => 'ri-store-2-line', 'price' => 0, 'sort' => 1, 'description' => '免费开通，可更换模板和配置店铺信息，手续费5%', 'service_change' => 0.05, 'member_gate_pos' => 0,
             'is_domain' => 'n', 'perm_setprice' => 'n', 'perm_goodsstate' => 'n', 'perm_tpl' => 'y', 'perm_config' => 'y',             'upgrade_mode' => 'any', 'upgrade_sales_amount' => 0, 'upgrade_order_count' => 0, 'upgrade_days' => 0, 'upgrade_sub_count' => 0],
            ['name' => '高级分店', 'icon' => 'ri-vip-crown-line', 'price' => 99, 'sort' => 2, 'description' => '解锁独立域名、自定义价格、商品上下架等全部权限，手续费降至3%', 'service_change' => 0.03, 'member_gate_pos' => 2,
             'is_domain' => 'y', 'perm_setprice' => 'y', 'perm_goodsstate' => 'y', 'perm_tpl' => 'y', 'perm_config' => 'y',             'upgrade_mode' => 'any', 'upgrade_sales_amount' => 500, 'upgrade_order_count' => 30, 'upgrade_days' => 0, 'upgrade_sub_count' => 0],
        ],
        // 预设 B：标准版（3个等级）
        'b' => [
            ['name' => '体验版', 'icon' => 'ri-store-2-line', 'price' => 0, 'sort' => 1, 'description' => '免费开通，支持更换模板和配置店铺，手续费8%，适合新手体验', 'service_change' => 0.08, 'member_gate_pos' => 0,
             'is_domain' => 'n', 'perm_setprice' => 'n', 'perm_goodsstate' => 'n', 'perm_tpl' => 'y', 'perm_config' => 'y',             'upgrade_mode' => 'any', 'upgrade_sales_amount' => 0, 'upgrade_order_count' => 0, 'upgrade_days' => 0, 'upgrade_sub_count' => 0],
            ['name' => '专业版', 'icon' => 'ri-building-2-line', 'price' => 99, 'sort' => 2, 'description' => '解锁独立域名、自定义商品价格和上下架管理，手续费降至5%', 'service_change' => 0.05, 'member_gate_pos' => 2,
             'is_domain' => 'y', 'perm_setprice' => 'y', 'perm_goodsstate' => 'y', 'perm_tpl' => 'y', 'perm_config' => 'y',             'upgrade_mode' => 'any', 'upgrade_sales_amount' => 500, 'upgrade_order_count' => 30, 'upgrade_days' => 0, 'upgrade_sub_count' => 0],
            ['name' => '旗舰版', 'icon' => 'ri-vip-diamond-line', 'price' => 299, 'sort' => 3, 'description' => '全部权限解锁，手续费仅2%，适合高销量站长长期运营', 'service_change' => 0.02, 'member_gate_pos' => 3,
             'is_domain' => 'y', 'perm_setprice' => 'y', 'perm_goodsstate' => 'y', 'perm_tpl' => 'y', 'perm_config' => 'y',             'upgrade_mode' => 'any', 'upgrade_sales_amount' => 2000, 'upgrade_order_count' => 100, 'upgrade_days' => 30, 'upgrade_sub_count' => 3],
        ],
        // 预设 C：完整版（5个等级）
        'c' => [
            ['name' => '免费版', 'icon' => 'ri-store-line', 'price' => 0, 'sort' => 1, 'description' => '零门槛开通，仅支持配置店铺基础信息，手续费10%', 'service_change' => 0.1, 'member_gate_pos' => 0,
             'is_domain' => 'n', 'perm_setprice' => 'n', 'perm_goodsstate' => 'n', 'perm_tpl' => 'n', 'perm_config' => 'y',             'upgrade_mode' => 'any', 'upgrade_sales_amount' => 0, 'upgrade_order_count' => 0, 'upgrade_days' => 0, 'upgrade_sub_count' => 0],
            ['name' => '入门版', 'icon' => 'ri-rocket-line', 'price' => 29, 'sort' => 2, 'description' => '解锁店铺模板更换，手续费降至8%，可个性化店铺外观', 'service_change' => 0.08, 'member_gate_pos' => 0,
             'is_domain' => 'n', 'perm_setprice' => 'n', 'perm_goodsstate' => 'n', 'perm_tpl' => 'y', 'perm_config' => 'y',             'upgrade_mode' => 'any', 'upgrade_sales_amount' => 200, 'upgrade_order_count' => 10, 'upgrade_days' => 0, 'upgrade_sub_count' => 0],
            ['name' => '标准版', 'icon' => 'ri-building-2-line', 'price' => 99, 'sort' => 3, 'description' => '解锁独立域名、自定义商品价格和上下架管理，手续费5%', 'service_change' => 0.05, 'member_gate_pos' => 2,
             'is_domain' => 'y', 'perm_setprice' => 'y', 'perm_goodsstate' => 'y', 'perm_tpl' => 'y', 'perm_config' => 'y',             'upgrade_mode' => 'any', 'upgrade_sales_amount' => 500, 'upgrade_order_count' => 30, 'upgrade_days' => 0, 'upgrade_sub_count' => 0],
            ['name' => '专业版', 'icon' => 'ri-vip-crown-line', 'price' => 199, 'sort' => 4, 'description' => '全部权限解锁，手续费仅3%，利润空间更大', 'service_change' => 0.03, 'member_gate_pos' => 3,
             'is_domain' => 'y', 'perm_setprice' => 'y', 'perm_goodsstate' => 'y', 'perm_tpl' => 'y', 'perm_config' => 'y',             'upgrade_mode' => 'all', 'upgrade_sales_amount' => 2000, 'upgrade_order_count' => 50, 'upgrade_days' => 30, 'upgrade_sub_count' => 0],
            ['name' => '旗舰版', 'icon' => 'ri-vip-diamond-line', 'price' => 399, 'sort' => 5, 'description' => '全部权限解锁，手续费低至1%，适合大卖家长期深度运营', 'service_change' => 0.01, 'member_gate_pos' => 4,
             'is_domain' => 'y', 'perm_setprice' => 'y', 'perm_goodsstate' => 'y', 'perm_tpl' => 'y', 'perm_config' => 'y',             'upgrade_mode' => 'all', 'upgrade_sales_amount' => 5000, 'upgrade_order_count' => 200, 'upgrade_days' => 60, 'upgrade_sub_count' => 3],
        ],
    ];

    $items = $presets[$preset];
    $existing = $db->fetch_all("SELECT id FROM {$db_prefix}station_level ORDER BY sort ASC, id ASC");
    $now = time();
    $permFields = ['is_domain','perm_setprice','perm_goodsstate','perm_tpl','perm_config'];

    // 自动补齐旧库可能缺失的列
    $needCols = ['description','member_gate','using','upgrade_mode','upgrade_sales_amount','upgrade_order_count','upgrade_days','upgrade_sub_count'];
    $colCheck = $db->fetch_all("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$db_prefix}station_level' AND COLUMN_NAME IN ('" . implode("','", $needCols) . "')");
    $existCols = [];
    foreach ($colCheck as $_c) { $existCols[] = $_c['COLUMN_NAME']; }
    if (!in_array('description', $existCols))
        $db->query("ALTER TABLE `{$db_prefix}station_level` ADD COLUMN `description` varchar(500) DEFAULT '' COMMENT '等级描述' AFTER `price`");
    if (!in_array('member_gate', $existCols))
        $db->query("ALTER TABLE `{$db_prefix}station_level` ADD COLUMN `member_gate` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '开通门槛' AFTER `description`");
    if (!in_array('using', $existCols))
        $db->query("ALTER TABLE `{$db_prefix}station_level` ADD COLUMN `using` varchar(10) DEFAULT 'y' COMMENT '是否启用'");
    if (!in_array('upgrade_mode', $existCols))
        $db->query("ALTER TABLE `{$db_prefix}station_level` ADD COLUMN `upgrade_mode` varchar(10) NOT NULL DEFAULT 'any' COMMENT '自动升级判断模式'");
    if (!in_array('upgrade_sales_amount', $existCols))
        $db->query("ALTER TABLE `{$db_prefix}station_level` ADD COLUMN `upgrade_sales_amount` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '自动升级-累计销售额'");
    if (!in_array('upgrade_order_count', $existCols))
        $db->query("ALTER TABLE `{$db_prefix}station_level` ADD COLUMN `upgrade_order_count` int(10) NOT NULL DEFAULT 0 COMMENT '自动升级-累计订单量'");
    if (!in_array('upgrade_days', $existCols))
        $db->query("ALTER TABLE `{$db_prefix}station_level` ADD COLUMN `upgrade_days` int(10) NOT NULL DEFAULT 0 COMMENT '自动升级-运营天数'");
    if (!in_array('upgrade_sub_count', $existCols))
        $db->query("ALTER TABLE `{$db_prefix}station_level` ADD COLUMN `upgrade_sub_count` int(10) NOT NULL DEFAULT 0 COMMENT '自动升级-下级分店数'");

    // 动态解析 member_gate_pos → 真实的 dc_member.id
    $memberLevels = $db->fetch_all("SELECT id FROM {$db_prefix}member WHERE state=1 ORDER BY sort ASC, id ASC");
    $memberIdByPos = []; // pos(1-based) => member.id
    foreach ($memberLevels as $_mi => $_mr) {
        $memberIdByPos[$_mi + 1] = (int)$_mr['id'];
    }

    // 解析 member_gate_pos → 真实 member.id 的映射函数
    $resolveGate = function($row) use ($memberIdByPos) {
        $pos = isset($row['member_gate_pos']) ? (int)$row['member_gate_pos'] : 0;
        return ($pos > 0 && isset($memberIdByPos[$pos])) ? $memberIdByPos[$pos] : 0;
    };

    // 构建单条预设数据
    $buildData = function($row, $sortIdx) use ($resolveGate, $permFields, $now) {
        $data = [
            'name'           => $row['name'],
            'icon'           => stationNormalizeLevelIcon($row['icon'] ?? 'ri-store-2-line'),
            'icon_image'     => $row['icon_image'] ?? '',
            'price'          => $row['price'],
            'sort'           => $sortIdx,
            'description'    => $row['description'],
            'member_gate'    => $resolveGate($row),
            'service_change' => $row['service_change'],
            'using'          => 'y',
            'upgrade_mode'          => $row['upgrade_mode'] ?? 'any',
            'upgrade_sales_amount'  => (float)($row['upgrade_sales_amount'] ?? 0),
            'upgrade_order_count'   => (int)($row['upgrade_order_count'] ?? 0),
            'upgrade_days'          => (int)($row['upgrade_days'] ?? 0),
            'upgrade_sub_count'     => (int)($row['upgrade_sub_count'] ?? 0),
            'update_time'    => $now,
        ];
        foreach ($permFields as $f) {
            $data[$f] = isset($row[$f]) ? $row[$f] : 'n';
        }
        return $data;
    };

    $presetCount  = count($items);
    $existCount   = count($existing);

    if (empty($existing)) {
        // 空表：直接插入全部预设
        foreach ($items as $i => $row) {
            $data = $buildData($row, $i + 1);
            $data['create_time'] = $now;
            $db->add('station_level', $data);
        }
    } else {
        $updateCount = min($presetCount, $existCount);

        // 覆盖已有等级（按 sort 顺序一一对应）
        for ($i = 0; $i < $updateCount; $i++) {
            $data = $buildData($items[$i], $i + 1);
            $db->update('station_level', $data, ['id' => (int)$existing[$i]['id']]);
        }

        // 预设多于已有：新增
        for ($i = $existCount; $i < $presetCount; $i++) {
            $data = $buildData($items[$i], $i + 1);
            $data['create_time'] = $now;
            $db->add('station_level', $data);
        }

        // 已有多于预设：停用多余等级（保留数据，不删除）
        for ($i = $presetCount; $i < $existCount; $i++) {
            $db->update('station_level', [
                'sort'        => $i + 1,
                'using'       => 'n',
                'update_time' => $now,
            ], ['id' => (int)$existing[$i]['id']]);
        }
    }
    $presetNames = ['a' => '基础版', 'b' => '标准版', 'c' => '完整版'];
    output::ok('已应用「' . ($presetNames[$preset] ?? $preset) . '」预设方案，共 ' . count($items) . ' 个等级');
}

if ($action == 'setting') {
    header('Location: ./shop.php?action=station_setting');
    exit;
}

if ($action == 'setting_save') {
    LoginAuth::checkToken();
    $station_domain_strict_val = isset($_POST['station_domain_strict']) ? '1' : '0';
    $data = [
        'station_domain'               => Input::postStrVar('station_domain'),
        'station_cname_domain'         => Input::postStrVar('station_cname_domain'),
        'station_domain_retain'        => Input::postStrVar('station_domain_retain'),
        'station_domain_change_price'  => Input::postStrVar('station_domain_change_price'),
        'station_domain_strict'        => $station_domain_strict_val,
        'station_extra_domains'        => Input::postStrVar('station_extra_domains'),
        'station_slug_mode'            => isset($_POST['station_slug_mode']) ? '1' : '0',
        Level_Service::OPT_STATION_SWITCH => isset($_POST['station_switch']) ? '1' : '0',
        'station_upgrade_price_mode' => in_array(Input::postStrVar('station_upgrade_price_mode', 'diff'), ['diff', 'full']) ? Input::postStrVar('station_upgrade_price_mode', 'diff') : 'diff',
    ];

    foreach ($data as $key => $val) {
        Option::updateOption($key, $val);
    }

    $CACHE->updateCache('options');
    Output::ok();
}
