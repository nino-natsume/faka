<?php
/**
 * 单商品加价规则管理（按等级 × 固定/百分比加价）
 * 菜单 id：menu-user-single-rule
 *
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

/**
 * @var string $action
 * @var object $CACHE
 */

require_once 'globals.php';

$ruleModel = new Single_Rule_Model();
$memberModel = new Member_Model();

// =======================================
// 默认：列表页
// =======================================
if (empty($action)) {
    $isEmbed = (int)Input::getIntVar('embed', 0) === 1;
    if ($isEmbed) {
        include View::getAdmView('open_head');
        require_once View::getAdmView('templates/default/single_rule/index');
        include View::getAdmView('open_foot');
    } else {
        $br = '<a href="./">数据中心</a><a href="./goods.php">商品管理</a><a href="./price_rule.php">加价规则</a><a><cite>单商品加价规则</cite></a>';
        include View::getAdmView('header');
        require_once View::getAdmView('templates/default/single_rule/index');
        include View::getAdmView('footer');
    }
    View::output();
}

// =======================================
// 列表数据
// =======================================
if ($action === 'index') {
    $page = Input::getIntVar('page', 1);
    $limit = Input::getIntVar('limit', 20);
    $keyword = Input::getStrVar('keyword');
    $res = $ruleModel->getList($page, $limit, $keyword);

    foreach ($res['list'] as &$row) {
        $row['usage'] = $ruleModel->getUsageCount((int)$row['id']);
        $row['type_text'] = intval($row['type']) === Single_Rule_Model::TYPE_PERCENT ? '百分比加价' : '固定加价';
        $row['type'] = (int)$row['type'];
        $row['state'] = (int)$row['state'];
        // 解析配置了多少个等级
        $rules = json_decode($row['rules'] ?? '[]', true);
        $row['level_count'] = is_array($rules) ? count($rules) : 0;
        $row['create_time_str'] = !empty($row['create_time']) ? date('Y-m-d H:i', (int)$row['create_time']) : '--';
    }
    unset($row);

    output::data($res['list'], $res['total']);
}

// =======================================
// 添加/编辑弹窗页
// =======================================
if ($action === 'edit') {
    $id = Input::getIntVar('id');
    $info = $id > 0 ? $ruleModel->getById($id) : null;
    if ($id > 0 && empty($info)) {
        echo '规则不存在';
        exit;
    }
    if (empty($info)) {
        $info = ['id' => 0, 'name' => '', 'type' => Single_Rule_Model::TYPE_FIXED, 'rules' => '{}', 'state' => 1];
    }
    // 读取所有启用的等级供配置
    $levels = $memberModel->getActiveList();

    include View::getAdmView('open_head');
    require_once View::getAdmView('templates/default/single_rule/edit');
    include View::getAdmView('open_foot');
    View::output();
}

// =======================================
// 保存
// =======================================
if ($action === 'save_ajax') {
    LoginAuth::checkToken();
    $id = Input::postIntVar('id');
    $name = trim(Input::postStrVar('name'));
    $type = Input::postIntVar('type', Single_Rule_Model::TYPE_FIXED);
    $rulesRaw = isset($_POST['rules']) ? $_POST['rules'] : '{}';

    if ($name === '') output::error('请填写规则名称');
    $rules = json_decode($rulesRaw, true);
    if (!is_array($rules)) $rules = [];

    if ($id > 0) {
        $ret = $ruleModel->update($id, ['name' => $name, 'type' => $type, 'rules' => $rules]);
    } else {
        $ret = $ruleModel->create(['name' => $name, 'type' => $type, 'rules' => $rules]);
    }
    if (intval($ret['code'] ?? 0) !== 1) {
        output::error($ret['msg'] ?? '保存失败');
    }
    output::ok();
}

// =======================================
// 启停
// =======================================
if ($action === 'toggle_state') {
    LoginAuth::checkToken();
    $id = Input::postIntVar('id');
    $state = Input::postIntVar('state');
    if ($id <= 0) output::error('参数错误');
    $ruleModel->setState($id, $state);
    output::ok();
}

// =======================================
// 删除
// =======================================
if ($action === 'del') {
    LoginAuth::checkToken();
    $ids = Input::postStrVar('ids');
    $ids = array_filter(array_map('intval', explode(',', $ids)));
    if (empty($ids)) output::error('请选择要删除的规则');
    foreach ($ids as $id) {
        $ret = $ruleModel->delete($id);
        if (intval($ret['code'] ?? 0) !== 1) {
            output::error($ret['msg'] ?? '删除失败');
        }
    }
    output::ok();
}

// =======================================
// 引用详情
// =======================================
if ($action === 'usage') {
    $id = Input::getIntVar('id');
    $rule = $ruleModel->getById($id);
    if (empty($rule)) output::error('规则不存在');

    $db = Database::getInstance();
    $goods = $db->fetch_all("SELECT id, title FROM " . DB_PREFIX . "goods WHERE single_rule_id={$id} AND (delete_time IS NULL OR delete_time=0) LIMIT 100");
    output::ok([
        'rule' => $rule,
        'goods' => $goods ?: [],
    ]);
}

output::error('未知操作');
