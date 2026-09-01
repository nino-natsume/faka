<?php
/**
 * 会员等级管理
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

/**
 * @var string $action
 * @var object $CACHE
 */

require_once 'globals.php';

$memberModel = new Member_Model();

if (empty($action)) {
    $br = '<a href="./">数据中心</a><a href="./user.php">用户管理</a><a><cite>会员等级</cite></a>';
    $db = Database::getInstance();
    $memberResetLevelCountRow = $db->once_fetch_array("SELECT COUNT(*) AS c FROM " . DB_PREFIX . "member");
    $memberResetUserCountRow = $db->once_fetch_array("SELECT COUNT(*) AS c FROM " . DB_PREFIX . "user WHERE level > 0");
    $memberResetLevelCount = (int)($memberResetLevelCountRow['c'] ?? 0);
    $memberResetUserCount = (int)($memberResetUserCountRow['c'] ?? 0);
    $memberPresetOptions = Member_Model::getPresetOptions();
    include View::getAdmView('header');
    require_once View::getAdmView('templates/default/member/index');
    include View::getAdmView('footer');
    View::output();
}

if ($action == 'index') {
    $list = $memberModel->getMembersAll();
    // 格式化数值展示
    foreach ($list as &$row) {
        $row['price'] = number_format((float)$row['price'], 2, '.', '');
        $row['markup_ratio'] = (float)$row['markup_ratio'];
        $row['exchange_ratio'] = (float)$row['exchange_ratio'];
        $row['actual_profit'] = (float)$row['actual_profit'];
        $row['profit_threshold'] = (float)$row['profit_threshold'];
        $row['renew_ratio'] = (float)$row['renew_ratio'];
        $row['duration_days'] = (int)$row['duration_days'];
        $row['state'] = (int)$row['state'];
        $row['is_default'] = (int)($row['is_default'] ?? 0);
        $row['upgrade_mode'] = (string)($row['upgrade_mode'] ?? 'any');
        $row['upgrade_direct_count'] = (int)($row['upgrade_direct_count'] ?? 0);
        $row['upgrade_consume_amount'] = (float)($row['upgrade_consume_amount'] ?? 0);
        $row['upgrade_team_count'] = (int)($row['upgrade_team_count'] ?? 0);
    }
    unset($row);
    output::data($list, count($list));
}

if ($action == 'add_ajax') {
    LoginAuth::checkToken();
    $data = _collectLevelPost();
    if (empty($data['name'])) {
        output::error('请填写等级名称');
    }
    $memberModel->create($data);
    output::ok();
}

if ($action == 'upload_icon') {
    LoginAuth::checkToken();
    $ret = uploadCropImg();
    $mediaModel = new Media_Model();
    $mediaModel->addMedia($ret['file_info']);
    Output::ok($ret['file_info']['file_path']);
}

if ($action == 'add') {
    // 批量加价规则下拉选项
    $profitRuleModel = new Profit_Rule_Model();
    $profitRules = $profitRuleModel->getActiveOptions();
    include View::getAdmView('open_head');
    require_once View::getAdmView('templates/default/member/add');
    include View::getAdmView('open_foot');
    View::output();
}

if ($action == 'edit_ajax') {
    LoginAuth::checkToken();
    $id = Input::postIntVar('id');
    if ($id <= 0) output::error('参数错误');
    $data = _collectLevelPost();
    if (empty($data['name'])) {
        output::error('请填写等级名称');
    }
    $memberModel->updateById($id, $data);
    output::ok();
}

if ($action == 'edit') {
    $id = Input::getIntVar('id');
    $info = $memberModel->getById($id);
    if (empty($info)) {
        echo '等级不存在';
        exit;
    }
    // 批量加价规则下拉选项
    $profitRuleModel = new Profit_Rule_Model();
    $profitRules = $profitRuleModel->getActiveOptions();
    include View::getAdmView('open_head');
    require_once View::getAdmView('templates/default/member/edit');
    include View::getAdmView('open_foot');
    View::output();
}

if ($action == 'del') {
    LoginAuth::checkToken();
    $ids = Input::postStrVar('ids');
    $ids = array_filter(array_map('intval', explode(',', $ids)));
    foreach ($ids as $id) {
        $row = $memberModel->getById($id);
        if ($row && (int)($row['is_default'] ?? 0) === 1) {
            output::error('默认等级不允许删除，请先将其他等级设为默认');
        }
        $memberModel->del($id);
    }
    output::ok();
}

if ($action == 'toggle_state') {
    LoginAuth::checkToken();
    $id = Input::postIntVar('id');
    $state = Input::postIntVar('state');
    if ($state == 0) {
        $row = $memberModel->getById($id);
        if ($row && (int)($row['is_default'] ?? 0) === 1) {
            output::error('默认等级不允许停用');
        }
    }
    $memberModel->setState($id, $state);
    output::ok();
}

if ($action == 'set_default') {
    LoginAuth::checkToken();
    $id = Input::postIntVar('id');
    if ($id <= 0) output::error('参数错误');
    $memberModel->setDefault($id);
    output::ok();
}

if ($action == 'sort_move') {
    LoginAuth::checkToken();
    $id = Input::postIntVar('id');
    $type = Input::postIntVar('type');
    if (!$memberModel->sortMove($id, $type)) {
        output::error('排序失败');
    }
    output::ok();
}

if ($action == 'reset_default') {
    LoginAuth::checkToken();
    if (Input::postIntVar('confirm_reset') !== 1) {
        output::error('重置会员等级属于高风险操作，请阅读提示并在倒计时结束后确认');
    }
    $preset = Input::postStrVar('preset', 'a');
    if (!in_array($preset, ['a', 'b', 'c'], true)) $preset = 'a';
    if (!$memberModel->resetDefault($preset)) {
        output::error('安全重置会员等级失败，请稍后重试或查看日志');
    }
    output::ok();
}

/**
 * 收集等级表单 POST 数据
 */
function _collectLevelPost() {
    $mode = Input::postStrVar('upgrade_mode', 'any');
    if (!in_array($mode, ['any', 'all'], true)) $mode = 'any';
    $icon = trim(Input::postStrVar('icon', 'ri-vip-diamond-line'));
    if ($icon === '') $icon = 'ri-vip-diamond-line';
    if (!preg_match('/^ri-[a-z0-9-]+-(line|fill)$/', $icon)) $icon = 'ri-vip-diamond-line';
    return [
        'name' => Input::postStrVar('name'),
        'icon' => $icon,
        'icon_image' => Input::postStrVar('icon_image'),
        'price' => Input::postStrVar('price'),
        'markup_ratio' => Input::postStrVar('markup_ratio'),
        'exchange_ratio' => Input::postStrVar('exchange_ratio'),
        'actual_profit' => Input::postStrVar('actual_profit'),
        'profit_threshold' => Input::postStrVar('profit_threshold'),
        'profit_rule_id' => Input::postIntVar('profit_rule_id'),
        'duration_days' => Input::postIntVar('duration_days'),
        'renew_ratio' => Input::postStrVar('renew_ratio'),
        'content' => isset($_POST['content']) ? $_POST['content'] : '',
        'state' => Input::postIntVar('state', 1),
        'upgrade_mode' => $mode,
        'upgrade_direct_count' => max(0, Input::postIntVar('upgrade_direct_count', 0)),
        'upgrade_consume_amount' => max(0, (float)Input::postStrVar('upgrade_consume_amount', '0')),
        'upgrade_team_count' => max(0, Input::postIntVar('upgrade_team_count', 0)),
    ];
}
