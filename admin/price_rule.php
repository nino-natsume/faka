<?php
/**
 * 加价规则（批量加价规则 + 单商品规则 合并页）
 * 菜单 id：menu-goods-price-rule
 *
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

/**
 * @var string $action
 * @var object $CACHE
 */

require_once 'globals.php';

$tab = Input::getStrVar('tab');
if ($tab !== 'single') $tab = 'profit';

if ($tab === 'single') {
    $ruleModel = new Single_Rule_Model();
    $memberModel = new Member_Model();

    if (empty($action)) {
        $br = '<a href="./">数据中心</a><a href="./goods.php">商品管理</a><a><cite>加价规则</cite></a>';
        include View::getAdmView('header');
        require_once View::getAdmView('templates/default/price_rule/index');
        include View::getAdmView('footer');
        View::output();
    }

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
            $rules = json_decode($row['rules'] ?? '[]', true);
            $row['level_count'] = is_array($rules) ? count($rules) : 0;
            $row['create_time_str'] = !empty($row['create_time']) ? date('Y-m-d H:i', (int)$row['create_time']) : '--';
        }
        unset($row);
        output::data($res['list'], $res['total']);
    }

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
        $levels = $memberModel->getActiveList();
        include View::getAdmView('open_head');
        require_once View::getAdmView('templates/default/single_rule/edit');
        include View::getAdmView('open_foot');
        View::output();
    }

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

    if ($action === 'toggle_state') {
        LoginAuth::checkToken();
        $id = Input::postIntVar('id');
        $state = Input::postIntVar('state');
        if ($id <= 0) output::error('参数错误');
        $ruleModel->setState($id, $state);
        output::ok();
    }

    if ($action === 'del') {
        LoginAuth::checkToken();
        $force = (bool)Input::postIntVar('force', 0);
        $ids = Input::postStrVar('ids');
        $ids = array_filter(array_map('intval', explode(',', $ids)));
        if (empty($ids)) output::error('请选择要删除的规则');
        foreach ($ids as $id) {
            $ret = $ruleModel->delete($id, $force);
            if (intval($ret['code'] ?? 0) !== 1) {
                output::error($ret['msg'] ?? '删除失败');
            }
        }
        output::ok();
    }

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
}

$ruleModel = new Profit_Rule_Model();

if (empty($action)) {
    $br = '<a href="./">数据中心</a><a href="./goods.php">商品管理</a><a><cite>加价规则</cite></a>';
    include View::getAdmView('header');
    require_once View::getAdmView('templates/default/price_rule/index');
    include View::getAdmView('footer');
    View::output();
}

if ($action === 'index') {
    $page = Input::getIntVar('page', 1);
    $limit = Input::getIntVar('limit', 20);
    $keyword = Input::getStrVar('keyword');
    $res = $ruleModel->getList($page, $limit, $keyword);
    foreach ($res['list'] as &$row) {
        $usage = $ruleModel->getUsageCount((int)$row['id']);
        $row['usage_goods'] = $usage['goods'];
        $row['usage_level'] = $usage['level'];
        $row['rules_html'] = Profit_Rule_Model::formatRulesHtml($row['rules']);
        $row['state'] = (int)$row['state'];
        $row['create_time_str'] = !empty($row['create_time']) ? date('Y-m-d H:i', (int)$row['create_time']) : '--';
    }
    unset($row);
    output::data($res['list'], $res['total']);
}

if ($action === 'edit') {
    $id = Input::getIntVar('id');
    $info = $id > 0 ? $ruleModel->getById($id) : null;
    if ($id > 0 && empty($info)) {
        echo '规则不存在';
        exit;
    }
    if (empty($info)) {
        $info = ['id' => 0, 'name' => '', 'rules' => '[]', 'state' => 1];
    }
    include View::getAdmView('open_head');
    require_once View::getAdmView('templates/default/profit_rule/edit');
    include View::getAdmView('open_foot');
    View::output();
}

if ($action === 'save_ajax') {
    LoginAuth::checkToken();
    $id = Input::postIntVar('id');
    $name = trim(Input::postStrVar('name'));
    $rulesRaw = isset($_POST['rules']) ? $_POST['rules'] : '[]';
    if ($name === '') output::error('请填写规则名称');
    $rules = json_decode($rulesRaw, true);
    if (!is_array($rules) || empty($rules)) output::error('请至少配置一条成本区间');
    if ($id > 0) {
        $ret = $ruleModel->update($id, ['name' => $name, 'rules' => $rules]);
    } else {
        $ret = $ruleModel->create(['name' => $name, 'rules' => $rules]);
    }
    if (intval($ret['code'] ?? 0) !== 1) {
        output::error($ret['msg'] ?? '保存失败');
    }
    output::ok();
}

if ($action === 'create_default') {
    LoginAuth::checkToken();
    $ret = $ruleModel->createDefault();
    if (intval($ret['code'] ?? 0) !== 1) {
        output::error($ret['msg'] ?? '生成失败');
    }
    output::ok();
}

if ($action === 'toggle_state') {
    LoginAuth::checkToken();
    $id = Input::postIntVar('id');
    $state = Input::postIntVar('state');
    if ($id <= 0) output::error('参数错误');
    $ruleModel->setState($id, $state);
    output::ok();
}

if ($action === 'del') {
    LoginAuth::checkToken();
    $force = (bool)Input::postIntVar('force', 0);
    $ids = Input::postStrVar('ids');
    $ids = array_filter(array_map('intval', explode(',', $ids)));
    if (empty($ids)) output::error('请选择要删除的规则');
    foreach ($ids as $id) {
        $ret = $ruleModel->delete($id, $force);
        if (intval($ret['code'] ?? 0) !== 1) {
            output::error($ret['msg'] ?? '删除失败');
        }
    }
    output::ok();
}

if ($action === 'usage') {
    $id = Input::getIntVar('id');
    $usage = $ruleModel->getUsageCount($id);
    $rule = $ruleModel->getById($id);
    if (empty($rule)) output::error('规则不存在');
    $db = Database::getInstance();
    $goods = $db->fetch_all("SELECT id, title FROM " . DB_PREFIX . "goods WHERE profit_rule_id={$id} AND (delete_time IS NULL OR delete_time=0) LIMIT 50");
    $levels = $db->fetch_all("SELECT id, name FROM " . DB_PREFIX . "member WHERE profit_rule_id={$id} LIMIT 50");
    output::ok([
        'rule' => $rule,
        'usage' => $usage,
        'goods' => $goods ?: [],
        'levels' => $levels ?: [],
    ]);
}

output::error('未知操作');
