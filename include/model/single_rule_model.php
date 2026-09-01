<?php
/**
 * 单商品加价规则模型
 * 表结构见 install.sql `dc_single_rule`
 *
 * rules JSON 结构（按等级ID索引）：
 *   {"1": {"price": 0.5, "profits": 100}, "2": {"price": 1.0, "profits": 200}, ...}
 *   - price: 加价金额（type=1 固定元数；type=2 百分比%）
 *   - profits: 积分加价（与 price 同义，仅积分价场景使用）
 */

class Single_Rule_Model {

    const TYPE_FIXED = 1;      // 固定加价（元）
    const TYPE_PERCENT = 2;    // 百分比加价（%）

    private $db;
    private $table;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->table = DB_PREFIX . 'single_rule';
        // 表结构由 Level_Price::ensureSchema 统一保证
        if (class_exists('Level_Price')) {
            Level_Price::ensureSchema();
        }
    }

    /**
     * 分页列表
     */
    public function getList($page = 1, $limit = 20, $keyword = '') {
        $page = max(1, intval($page));
        $limit = max(1, intval($limit));
        $offset = ($page - 1) * $limit;

        $where = '1=1';
        if ($keyword !== '') {
            $safe = addslashes($keyword);
            $where .= " AND (name LIKE '%{$safe}%' OR id='{$safe}')";
        }

        $rows = $this->db->fetch_all("SELECT * FROM {$this->table} WHERE {$where} ORDER BY id DESC LIMIT {$offset},{$limit}");
        $total = $this->db->once_fetch_array("SELECT COUNT(*) AS cnt FROM {$this->table} WHERE {$where}");

        return [
            'list' => $rows ?: [],
            'total' => intval($total['cnt'] ?? 0),
        ];
    }

    /**
     * 全部启用规则（供商品编辑下拉）
     */
    public function getActiveOptions() {
        $rows = $this->db->fetch_all("SELECT id, name, type FROM {$this->table} WHERE state=1 ORDER BY id DESC");
        return $rows ?: [];
    }

    /**
     * 取单条
     */
    public function getById($id) {
        $id = intval($id);
        if ($id <= 0) return null;
        $row = $this->db->once_fetch_array("SELECT * FROM {$this->table} WHERE id={$id} LIMIT 1");
        return $row ?: null;
    }

    /**
     * 新建
     */
    public function create($data) {
        $name = isset($data['name']) ? trim((string)$data['name']) : '';
        $type = intval($data['type'] ?? self::TYPE_FIXED);
        $rules = $data['rules'] ?? [];
        if ($name === '') return ['code' => 0, 'msg' => '规则名称不能为空'];
        if (!in_array($type, [self::TYPE_FIXED, self::TYPE_PERCENT])) $type = self::TYPE_FIXED;
        $rulesJson = $this->normalizeRules($rules, $type);
        if ($rulesJson === false) return ['code' => 0, 'msg' => '规则数据格式不正确'];

        $t = time();
        $this->db->add('single_rule', [
            'name' => $name,
            'type' => $type,
            'rules' => $rulesJson,
            'state' => 1,
            'create_time' => $t,
            'update_time' => $t,
        ]);
        return ['code' => 1, 'id' => $this->db->insert_id()];
    }

    /**
     * 编辑
     */
    public function update($id, $data) {
        $id = intval($id);
        if ($id <= 0) return ['code' => 0, 'msg' => '非法ID'];
        $exists = $this->getById($id);
        if (!$exists) return ['code' => 0, 'msg' => '规则不存在'];

        $update = [];
        if (isset($data['name'])) {
            $n = trim((string)$data['name']);
            if ($n === '') return ['code' => 0, 'msg' => '规则名称不能为空'];
            $update['name'] = $n;
        }
        if (isset($data['type'])) {
            $t = intval($data['type']);
            if (!in_array($t, [self::TYPE_FIXED, self::TYPE_PERCENT])) $t = self::TYPE_FIXED;
            $update['type'] = $t;
        }
        if (isset($data['rules'])) {
            $useType = $update['type'] ?? intval($exists['type']);
            $json = $this->normalizeRules($data['rules'], $useType);
            if ($json === false) return ['code' => 0, 'msg' => '规则数据格式不正确'];
            $update['rules'] = $json;
        }
        if (empty($update)) return ['code' => 1, 'msg' => '无改动'];
        $update['update_time'] = time();
        $this->db->update('single_rule', $update, ['id' => $id]);
        return ['code' => 1];
    }

    /**
     * 启用/停用
     */
    public function setState($id, $state) {
        $id = intval($id);
        if ($id <= 0) return false;
        $state = intval($state) === 1 ? 1 : 0;
        $this->db->update('single_rule', ['state' => $state, 'update_time' => time()], ['id' => $id]);
        return true;
    }

    /**
     * 删除
     */
    public function delete($id, $force = false) {
        $id = intval($id);
        if ($id <= 0) return ['code' => 0, 'msg' => '非法ID'];
        // 检查是否被商品引用（排除已删除商品）
        $inUse = $this->db->once_fetch_array("SELECT COUNT(*) AS cnt FROM " . DB_PREFIX . "goods WHERE single_rule_id={$id} AND (delete_time IS NULL OR delete_time=0)");
        $cnt = intval($inUse['cnt'] ?? 0);
        if ($cnt > 0 && !$force) {
            return ['code' => 0, 'msg' => '规则正被 ' . $cnt . ' 个商品引用，请先解除绑定', 'usage' => $cnt];
        }
        if ($cnt > 0) {
            $this->db->query("UPDATE " . DB_PREFIX . "goods SET single_rule_id=0 WHERE single_rule_id={$id}");
        }
        $this->db->query("DELETE FROM {$this->table} WHERE id={$id}");
        return ['code' => 1];
    }

    /**
     * 指定规则被多少个商品引用
     */
    public function getUsageCount($id) {
        $id = intval($id);
        if ($id <= 0) return 0;
        $row = $this->db->once_fetch_array("SELECT COUNT(*) AS cnt FROM " . DB_PREFIX . "goods WHERE single_rule_id={$id} AND (delete_time IS NULL OR delete_time=0)");
        return intval($row['cnt'] ?? 0);
    }

    /**
     * 规则数据归一化并返回 JSON 字符串；失败返回 false
     */
    private function normalizeRules($rules, $type) {
        if (is_string($rules)) {
            $decoded = json_decode($rules, true);
            if (!is_array($decoded)) return false;
            $rules = $decoded;
        }
        if (!is_array($rules)) return false;
        $out = [];
        foreach ($rules as $levelId => $item) {
            $levelId = intval($levelId);
            if ($levelId <= 0) continue;
            $price = isset($item['price']) ? (float)$item['price'] : 0;
            $profits = isset($item['profits']) ? (float)$item['profits'] : 0;
            if ($type === self::TYPE_PERCENT) {
                if ($price < 0) $price = 0;
                if ($profits < 0) $profits = 0;
            }
            $out[(string)$levelId] = [
                'price' => $price,
                'profits' => $profits,
            ];
        }
        return json_encode($out, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 对指定等级应用单商品规则，返回加价后售价（元单位）
     * @param int   $ruleId     规则ID
     * @param float $costYuan   商品成本（元）
     * @param int   $levelId    用户等级ID
     * @param int   $field      取哪个字段：1=price（购买价），2=profits（积分价）
     * @return float|null  null 表示规则未命中该等级，应回退下一层
     */
    public function applyTo($ruleId, $costYuan, $levelId, $field = 1) {
        $ruleId = intval($ruleId);
        $levelId = intval($levelId);
        if ($ruleId <= 0 || $levelId <= 0) return null;

        $rule = $this->getById($ruleId);
        if (empty($rule) || intval($rule['state']) !== 1) return null;
        $rules = json_decode($rule['rules'], true);
        if (!is_array($rules)) return null;
        $key = (string)$levelId;
        if (!isset($rules[$key])) return null;

        $bump = ($field === 2) ? ($rules[$key]['profits'] ?? 0) : ($rules[$key]['price'] ?? 0);
        $bump = (float)$bump;
        $cost = (float)$costYuan;

        if (intval($rule['type']) === self::TYPE_FIXED) {
            return $cost + $bump;
        }
        // 百分比
        return $cost + ($cost * $bump / 100);
    }
}
