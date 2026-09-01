<?php
/**
 * 会员等级模型
 * 表结构见 install.sql `dc_member`
 */

class Member_Model {

    private $db;
    private $table;
    private static $columnsChecked = false;

    // 会员等级字段列表（对应数据库列）
    const FIELDS = [
        'id', 'name', 'icon', 'icon_image', 'price', 'markup_ratio', 'exchange_ratio',
        'actual_profit', 'profit_threshold', 'profit_rule_id',
        'duration_days', 'renew_ratio', 'content', 'sort', 'state',
        'is_default', 'upgrade_mode', 'upgrade_direct_count',
        'upgrade_consume_amount', 'upgrade_team_count',
        'create_time', 'update_time'
    ];
    const DEFAULT_OPTION_KEY = 'level_default_grade';

    public function __construct() {
        $this->db = Database::getInstance();
        $this->table = DB_PREFIX . 'member';
        $this->ensureColumns();
    }

    /**
     * 自动迁移：为老旧安装环境补齐新字段
     */
    private function ensureColumns() {
        if (self::$columnsChecked) return;
        self::$columnsChecked = true;

        $columns = $this->_listColumns();
        $alters = [];

        if (!in_array('price', $columns)) {
            $alters[] = "ADD COLUMN `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '等级开通价格' AFTER `name`";
        }
        if (!in_array('icon', $columns)) {
            $alters[] = "ADD COLUMN `icon` varchar(100) NOT NULL DEFAULT 'ri-vip-diamond-line' COMMENT 'Remix Icon图标' AFTER `name`";
        }
        if (!in_array('icon_image', $columns)) {
            $alters[] = "ADD COLUMN `icon_image` varchar(255) NOT NULL DEFAULT '' COMMENT '等级图片图标' AFTER `icon`";
        }
        if (!in_array('markup_ratio', $columns)) {
            $alters[] = "ADD COLUMN `markup_ratio` decimal(6,2) NOT NULL DEFAULT '0.00' COMMENT '加价比例(%)'";
        }
        if (!in_array('exchange_ratio', $columns)) {
            $alters[] = "ADD COLUMN `exchange_ratio` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '积分兑换倍数'";
        }
        if (!in_array('actual_profit', $columns)) {
            $alters[] = "ADD COLUMN `actual_profit` decimal(5,2) NOT NULL DEFAULT '100.00' COMMENT '绝对利润(%)'";
        }
        if (!in_array('profit_threshold', $columns)) {
            $alters[] = "ADD COLUMN `profit_threshold` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '分成阈值(%)'";
        }
        if (!in_array('profit_rule_id', $columns)) {
            $alters[] = "ADD COLUMN `profit_rule_id` int(10) NOT NULL DEFAULT '0' COMMENT '绑定加价规则ID'";
        }
        if (!in_array('duration_days', $columns)) {
            $alters[] = "ADD COLUMN `duration_days` int(10) NOT NULL DEFAULT '0' COMMENT '有效期天数,0=永久'";
        }
        if (!in_array('renew_ratio', $columns)) {
            $alters[] = "ADD COLUMN `renew_ratio` decimal(5,2) NOT NULL DEFAULT '100.00' COMMENT '续期百分比(%)'";
        }
        if (!in_array('content', $columns)) {
            $alters[] = "ADD COLUMN `content` text COMMENT '等级公告'";
        }
        if (!in_array('sort', $columns)) {
            $alters[] = "ADD COLUMN `sort` int(10) NOT NULL DEFAULT '0' COMMENT '排序'";
        }
        if (!in_array('state', $columns)) {
            $alters[] = "ADD COLUMN `state` tinyint(1) NOT NULL DEFAULT '1' COMMENT '启用状态'";
        }
        if (!in_array('is_default', $columns)) {
            $alters[] = "ADD COLUMN `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否默认等级 1=是'";
        }
        if (!in_array('upgrade_mode', $columns)) {
            $alters[] = "ADD COLUMN `upgrade_mode` varchar(10) NOT NULL DEFAULT 'any' COMMENT '自动升级判断模式: any=任一, all=全部'";
        }
        if (!in_array('upgrade_direct_count', $columns)) {
            $alters[] = "ADD COLUMN `upgrade_direct_count` int(10) NOT NULL DEFAULT '0' COMMENT '自动升级-直推粉丝数'";
        }
        if (!in_array('upgrade_consume_amount', $columns)) {
            $alters[] = "ADD COLUMN `upgrade_consume_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '自动升级-累计消费金额'";
        }
        if (!in_array('upgrade_team_count', $columns)) {
            $alters[] = "ADD COLUMN `upgrade_team_count` int(10) NOT NULL DEFAULT '0' COMMENT '自动升级-团队总人数'";
        }
        if (!in_array('create_time', $columns)) {
            $alters[] = "ADD COLUMN `create_time` bigint(16) DEFAULT NULL COMMENT '创建时间'";
        }
        if (!in_array('update_time', $columns)) {
            $alters[] = "ADD COLUMN `update_time` bigint(16) DEFAULT NULL COMMENT '更新时间'";
        }

        if (!empty($alters)) {
            try {
                $sql = "ALTER TABLE `{$this->table}` " . implode(', ', $alters);
                $this->db->query($sql);
            } catch (Exception $e) {
                // 静默处理，避免阻塞业务
            }
        }

        // 同步检查 dc_user.level_expire_time 字段
        $this->_ensureUserColumn();

        // 确保至少有一个默认等级
        $this->_ensureDefaultLevel();
    }

    /**
     * 获取当前表列名
     */
    private function _listColumns() {
        $columns = [];
        try {
            $res = $this->db->query("SHOW COLUMNS FROM `{$this->table}`");
            while ($row = $this->db->fetch_array($res)) {
                $columns[] = $row['Field'];
            }
        } catch (Exception $e) {}
        return $columns;
    }

    /**
     * 确保 dc_user 表有 level_expire_time 字段
     */
    private function _ensureUserColumn() {
        $userTable = DB_PREFIX . 'user';
        try {
            $rows = $this->db->fetch_all("SHOW COLUMNS FROM `{$userTable}` LIKE 'level_expire_time'");
            if (empty($rows)) {
                $this->db->query("ALTER TABLE `{$userTable}` ADD COLUMN `level_expire_time` bigint(16) NOT NULL DEFAULT '0' COMMENT '会员等级到期时间' AFTER `credits`");
            }
        } catch (Exception $e) {}
    }

    /**
     * 获取等级列表（分页，管理后台用）
     */
    public function getMembers($page = 1) {
        $limit = '';
        if ($page) {
            $perpage_num = Option::get('admin_article_perpage_num');
            $startId = ($page - 1) * $perpage_num;
            $limit = "LIMIT $startId, " . $perpage_num;
        }
        return $this->db->fetch_all("SELECT * FROM {$this->table} ORDER BY sort ASC, id ASC $limit");
    }

    /**
     * 获取全部等级（按排序）
     */
    public function getMembersAll() {
        return $this->db->fetch_all("SELECT * FROM {$this->table} ORDER BY sort ASC, id ASC");
    }

    /**
     * 获取启用的等级（按排序升序，供价格计算使用）
     */
    public function getActiveList() {
        return $this->db->fetch_all("SELECT * FROM {$this->table} WHERE state=1 ORDER BY sort ASC, id ASC");
    }

    /**
     * 根据等级ID获取单个等级
     */
    public function getById($id) {
        $id = intval($id);
        if ($id <= 0) return null;
        return $this->db->once_fetch_array("SELECT * FROM {$this->table} WHERE id={$id} LIMIT 1");
    }

    public function getLevelSort($id, $activeOnly = false) {
        $id = intval($id);
        if ($id <= 0) return 0;
        $where = $activeOnly ? ' AND state=1' : '';
        $row = $this->db->once_fetch_array("SELECT sort FROM {$this->table} WHERE id={$id}{$where} LIMIT 1");
        return $row ? intval($row['sort']) : null;
    }

    /**
     * 创建等级
     */
    public function create($data) {
        $fields = $this->_filterFields($data, true);
        $now = time();
        $fields['create_time'] = $now;
        $fields['update_time'] = $now;
        if (!isset($fields['sort']) || $fields['sort'] <= 0) {
            $max = $this->db->once_fetch_array("SELECT MAX(sort) AS m FROM {$this->table}");
            $fields['sort'] = (intval($max['m'] ?? 0)) + 1;
        }
        $res = $this->db->add('member', $fields);
        if ($res) {
            $this->_ensureDefaultLevel();
        }
        return $res;
    }

    /**
     * 更新等级
     */
    public function updateById($id, $data) {
        $id = intval($id);
        if ($id <= 0) return false;
        $fields = $this->_filterFields($data, false);
        $fields['update_time'] = time();
        return $this->db->update('member', $fields, ['id' => $id]);
    }

    /**
     * 删除等级（禁止删除默认等级）
     */
    public function del($id) {
        $id = intval($id);
        if ($id <= 0) return false;
        // 禁止删除默认等级
        $row = $this->getById($id);
        if ($row && (int)($row['is_default'] ?? 0) === 1) {
            return false;
        }
        return $this->db->query("DELETE FROM {$this->table} WHERE id={$id}");
    }

    /**
     * 切换启用/停用（默认等级不允许停用）
     */
    public function setState($id, $state) {
        $id = intval($id);
        $state = intval($state) == 1 ? 1 : 0;
        if ($id <= 0) return false;
        if ($state === 0) {
            $row = $this->getById($id);
            if ($row && (int)($row['is_default'] ?? 0) === 1) {
                return false;
            }
        }
        return $this->db->query("UPDATE {$this->table} SET state={$state}, update_time=" . time() . " WHERE id={$id}");
    }

    /**
     * 排序操作：type 1=置顶 2=上移 3=下移 4=置底
     */
    public function sortMove($id, $type) {
        $id = intval($id);
        $type = intval($type);
        if ($id <= 0 || !in_array($type, [1, 2, 3, 4])) return false;

        $list = $this->db->fetch_all("SELECT id, sort FROM {$this->table} ORDER BY sort ASC, id ASC");
        $idx = -1;
        foreach ($list as $k => $v) {
            if (intval($v['id']) === $id) { $idx = $k; break; }
        }
        if ($idx < 0) return false;

        // 重新排列
        $item = $list[$idx];
        array_splice($list, $idx, 1);
        if ($type == 1) {
            array_unshift($list, $item);
        } elseif ($type == 2) {
            array_splice($list, max(0, $idx - 1), 0, [$item]);
        } elseif ($type == 3) {
            array_splice($list, min(count($list), $idx + 1), 0, [$item]);
        } else {
            $list[] = $item;
        }

        // 写回新的 sort 值
        $now = time();
        foreach ($list as $k => $v) {
            $newSort = $k + 1;
            $this->db->query("UPDATE {$this->table} SET sort={$newSort}, update_time={$now} WHERE id=" . intval($v['id']));
        }
        return true;
    }

    /**
     * 重置为指定预设方案的等级（A=3个 / B=5个 / C=8个）
     * @param string $presetKey  预设方案: a / b / c
     */
    public function resetDefault($presetKey = 'a') {
        $defaults = self::getDefaultPresets($presetKey);
        $existing = $this->db->fetch_all("SELECT id, sort, is_default FROM {$this->table} ORDER BY sort ASC, id ASC");
        $now = time();
        $useTransaction = method_exists($this->db, 'beginTransaction');

        try {
            if ($useTransaction) {
                $this->db->beginTransaction();
            }

            if (empty($existing)) {
                foreach ($defaults as $i => $row) {
                    $row['is_default'] = $i === 0 ? 1 : 0;
                    $this->db->add('member', $row);
                }
            } else {
                $defaultCount = count($defaults);
                $existingCount = count($existing);
                $updateCount = min($defaultCount, $existingCount);

                $this->db->query("UPDATE {$this->table} SET is_default=0");

                for ($i = 0; $i < $updateCount; $i++) {
                    $preset = $defaults[$i];
                    $fields = [
                        'name' => $preset['name'],
                        'icon' => $preset['icon'] ?? 'ri-vip-diamond-line',
                        'icon_image' => $preset['icon_image'] ?? '',
                        'price' => $preset['price'],
                        'markup_ratio' => $preset['markup_ratio'],
                        'exchange_ratio' => $preset['exchange_ratio'],
                        'actual_profit' => $preset['actual_profit'],
                        'profit_threshold' => $preset['profit_threshold'],
                        'profit_rule_id' => 0,
                        'duration_days' => 0,
                        'renew_ratio' => 0,
                        'upgrade_mode' => $preset['upgrade_mode'] ?? 'any',
                        'upgrade_direct_count' => $preset['upgrade_direct_count'] ?? 0,
                        'upgrade_consume_amount' => $preset['upgrade_consume_amount'] ?? 0,
                        'upgrade_team_count' => $preset['upgrade_team_count'] ?? 0,
                        'content' => $preset['content'],
                        'sort' => $i + 1,
                        'state' => 1,
                        'is_default' => $i === 0 ? 1 : 0,
                        'update_time' => $now,
                    ];
                    $this->db->update('member', $fields, ['id' => intval($existing[$i]['id'])]);
                }

                for ($i = $existingCount; $i < $defaultCount; $i++) {
                    $row = $defaults[$i];
                    $row['is_default'] = 0;
                    $this->db->add('member', $row);
                }

                for ($i = $defaultCount; $i < $existingCount; $i++) {
                    $this->db->update('member', [
                        'sort' => $i + 1,
                        'state' => 0,
                        'is_default' => 0,
                        'update_time' => $now,
                    ], ['id' => intval($existing[$i]['id'])]);
                }
            }

            $firstRow = $this->db->once_fetch_array("SELECT id FROM {$this->table} WHERE state=1 ORDER BY sort ASC, id ASC LIMIT 1");
            if (!empty($firstRow['id'])) {
                $this->syncDefaultOption((int)$firstRow['id']);
            }

            $this->_ensureDefaultLevel();

            if ($useTransaction) {
                $this->db->commit();
            }

            return true;
        } catch (Throwable $e) {
            if ($useTransaction) {
                $this->db->rollback();
            }
            if (class_exists('Log')) {
                Log::error('安全重置会员等级失败：' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * 预设方案元信息（供前端展示选择卡片）
     */
    public static function getPresetOptions() {
        return [
            'a' => [
                'name'   => '直推奖励',
                'tag'    => '新手推荐',
                'icon'   => 'ri-user-heart-line',
                'color'  => '#16a34a',
                'levels' => 3,
                'desc'   => '只有 3 个等级，最简单的分销模式。推荐人赚全部差价，不传递给更上级。',
                'example' => [
                    'title' => '举例：成本 10 元的商品',
                    'lines' => [
                        '普通用户购买价 <b>12.5 元</b>（加价 25%）',
                        '推荐人赚 <b>2.5 元</b>（全部差价）',
                        '上上级：<b>0 元</b>',
                    ],
                ],
                'income' => '等级开通费收入：VIP 29.9 元、合伙人 99.9 元',
                'tip'    => '无需开启「无限级分成」。简单直接，适合刚开始做分销的商家。',
                'auto_upgrade' => '直推 5 人或消费 200 元 → VIP；直推 20 人或消费 1000 元 → 合伙人',
            ],
            'b' => [
                'name'   => '团队分销',
                'tag'    => '多数商家适用',
                'icon'   => 'ri-team-line',
                'color'  => '#2563eb',
                'levels' => 5,
                'desc'   => '5 个等级，推荐人赚大头，上上级也能赚一小笔。兼顾直推激励与团队发展。',
                'example' => [
                    'title' => '举例：成本 10 元的商品',
                    'lines' => [
                        '普通用户购买价 <b>13.5 元</b>（加价 35%）',
                        '1 级上级赚 <b>≈2.7 元</b>',
                        '2 级上级赚 <b>≈0.8 元</b>',
                        '更高层级：<b>0 元</b>（被阈值拦住）',
                    ],
                ],
                'income' => '等级开通费收入：19.9 / 49.9 / 99.9 / 199.9 元',
                'tip'    => '建议开启「无限级分成」，让链路能跳过低等级用户继续往上找。',
                'auto_upgrade' => '直推 / 消费 / 团队人数任一达标即可升级，高等级门槛含团队条件',
            ],
            'c' => [
                'name'   => '深度裂变',
                'tag'    => '社交电商',
                'icon'   => 'ri-share-circle-line',
                'color'  => '#ea580c',
                'levels' => 8,
                'desc'   => '8 个等级，4~5 层推荐都能赚钱。商品加价较高，但裂变动力最强。',
                'example' => [
                    'title' => '举例：成本 10 元的商品',
                    'lines' => [
                        '普通用户购买价 <b>15 元</b>（加价 50%）',
                        '1 级上级赚 <b>≈1.50 元</b>',
                        '2 级上级赚 <b>≈1.62 元</b>',
                        '3 级上级赚 <b>≈1.66 元</b>',
                        '4 级上级赚 <b>≈0.22 元</b>',
                    ],
                ],
                'income' => '等级开通费收入：9.9~199.9 元，共 7 档付费等级',
                'tip'    => '必须开启「无限级分成」。加价高但裂变能力强，适合社交电商、知识付费。',
                'auto_upgrade' => '8 档均可通过直推 / 消费 / 团队达标自动升级，最高需直推 100 人或团队 500 人',
            ],
        ];
    }

    /**
     * 返回指定预设方案的等级数据（A=3个 / B=5个 / C=8个）
     * @param string $presetKey  a / b / c
     */
    public static function getDefaultPresets($presetKey = 'a') {
        $now = time();
        $presets = [
            // ---- 预设 A：直推奖励（3 个等级，actual_profit=100%，只分1级）----
            'a' => [
                ['name' => '普通用户', 'icon' => 'ri-user-smile-line', 'price' => 0,    'markup_ratio' => 25, 'exchange_ratio' => 2500, 'actual_profit' => 100, 'profit_threshold' => 0, 'content' => '注册即拥有，享受基础会员价。', 'upgrade_mode' => 'any', 'upgrade_direct_count' => 0, 'upgrade_consume_amount' => 0, 'upgrade_team_count' => 0],
                ['name' => 'VIP会员',  'icon' => 'ri-vip-crown-line', 'price' => 29.9, 'markup_ratio' => 15, 'exchange_ratio' => 1500, 'actual_profit' => 100, 'profit_threshold' => 0, 'content' => 'VIP会员，解锁更低会员价。', 'upgrade_mode' => 'any', 'upgrade_direct_count' => 5, 'upgrade_consume_amount' => 200, 'upgrade_team_count' => 0],
                ['name' => '合伙人',   'icon' => 'ri-hand-heart-line', 'price' => 99.9, 'markup_ratio' => 8,  'exchange_ratio' => 800,  'actual_profit' => 100, 'profit_threshold' => 0, 'content' => '合伙人，最低拿货价，适合长期经营。', 'upgrade_mode' => 'any', 'upgrade_direct_count' => 20, 'upgrade_consume_amount' => 1000, 'upgrade_team_count' => 0],
            ],
            // ---- 预设 B：团队分销（5 个等级，actual_profit 20-55%，2级分成）----
            'b' => [
                ['name' => '普通用户', 'icon' => 'ri-user-smile-line', 'price' => 0,     'markup_ratio' => 35, 'exchange_ratio' => 3500, 'actual_profit' => 20, 'profit_threshold' => 8, 'content' => '注册即拥有。', 'upgrade_mode' => 'any', 'upgrade_direct_count' => 0, 'upgrade_consume_amount' => 0, 'upgrade_team_count' => 0],
                ['name' => '银牌会员', 'icon' => 'ri-medal-line', 'price' => 19.9,  'markup_ratio' => 28, 'exchange_ratio' => 2800, 'actual_profit' => 28, 'profit_threshold' => 6, 'content' => '银牌会员，解锁首档会员价。', 'upgrade_mode' => 'any', 'upgrade_direct_count' => 3, 'upgrade_consume_amount' => 100, 'upgrade_team_count' => 0],
                ['name' => '金牌会员', 'icon' => 'ri-award-line', 'price' => 49.9,  'markup_ratio' => 22, 'exchange_ratio' => 2200, 'actual_profit' => 35, 'profit_threshold' => 5, 'content' => '金牌会员，主力销售等级。', 'upgrade_mode' => 'any', 'upgrade_direct_count' => 10, 'upgrade_consume_amount' => 500, 'upgrade_team_count' => 30],
                ['name' => '钻石代理', 'icon' => 'ri-vip-diamond-line', 'price' => 99.9,  'markup_ratio' => 16, 'exchange_ratio' => 1600, 'actual_profit' => 45, 'profit_threshold' => 3, 'content' => '钻石代理，拿货价更低，适合开店。', 'upgrade_mode' => 'any', 'upgrade_direct_count' => 30, 'upgrade_consume_amount' => 2000, 'upgrade_team_count' => 100],
                ['name' => '合伙人',   'icon' => 'ri-team-line', 'price' => 199.9, 'markup_ratio' => 10, 'exchange_ratio' => 1000, 'actual_profit' => 55, 'profit_threshold' => 2, 'content' => '合伙人，最高等级，极致价格优势。', 'upgrade_mode' => 'any', 'upgrade_direct_count' => 50, 'upgrade_consume_amount' => 5000, 'upgrade_team_count' => 300],
            ],
            // ---- 预设 C：深度裂变（8 个等级，actual_profit 10-25%，4-5级分成）----
            'c' => [
                ['name' => '普通用户',   'icon' => 'ri-user-smile-line', 'price' => 0,     'markup_ratio' => 50, 'exchange_ratio' => 5000, 'actual_profit' => 10, 'profit_threshold' => 3, 'content' => '注册即拥有。', 'upgrade_mode' => 'any', 'upgrade_direct_count' => 0, 'upgrade_consume_amount' => 0, 'upgrade_team_count' => 0],
                ['name' => '铜牌会员',   'icon' => 'ri-coin-line', 'price' => 9.9,   'markup_ratio' => 45, 'exchange_ratio' => 4500, 'actual_profit' => 12, 'profit_threshold' => 3, 'content' => '铜牌会员，解锁首档会员价。', 'upgrade_mode' => 'any', 'upgrade_direct_count' => 2, 'upgrade_consume_amount' => 50, 'upgrade_team_count' => 0],
                ['name' => '银牌会员',   'icon' => 'ri-medal-line', 'price' => 19.9,  'markup_ratio' => 40, 'exchange_ratio' => 4000, 'actual_profit' => 14, 'profit_threshold' => 2, 'content' => '银牌会员，拿货成本更低。', 'upgrade_mode' => 'any', 'upgrade_direct_count' => 5, 'upgrade_consume_amount' => 200, 'upgrade_team_count' => 0],
                ['name' => '金牌会员',   'icon' => 'ri-award-line', 'price' => 39.9,  'markup_ratio' => 35, 'exchange_ratio' => 3500, 'actual_profit' => 16, 'profit_threshold' => 2, 'content' => '金牌会员，主力销售等级。', 'upgrade_mode' => 'any', 'upgrade_direct_count' => 10, 'upgrade_consume_amount' => 500, 'upgrade_team_count' => 30],
                ['name' => '铂金代理',   'icon' => 'ri-vip-crown-line', 'price' => 69.9,  'markup_ratio' => 30, 'exchange_ratio' => 3000, 'actual_profit' => 18, 'profit_threshold' => 2, 'content' => '铂金代理，适合开店经营。', 'upgrade_mode' => 'any', 'upgrade_direct_count' => 20, 'upgrade_consume_amount' => 1000, 'upgrade_team_count' => 80],
                ['name' => '钻石代理',   'icon' => 'ri-vip-diamond-line', 'price' => 99.9,  'markup_ratio' => 25, 'exchange_ratio' => 2500, 'actual_profit' => 20, 'profit_threshold' => 1, 'content' => '钻石代理，更强价格优势。', 'upgrade_mode' => 'any', 'upgrade_direct_count' => 35, 'upgrade_consume_amount' => 2000, 'upgrade_team_count' => 150],
                ['name' => '黑金代理',   'icon' => 'ri-shield-star-line', 'price' => 149.9, 'markup_ratio' => 22, 'exchange_ratio' => 2200, 'actual_profit' => 22, 'profit_threshold' => 1, 'content' => '黑金代理，核心运营等级。', 'upgrade_mode' => 'any', 'upgrade_direct_count' => 50, 'upgrade_consume_amount' => 5000, 'upgrade_team_count' => 300],
                ['name' => '核心合伙人', 'icon' => 'ri-star-smile-line', 'price' => 199.9, 'markup_ratio' => 20, 'exchange_ratio' => 2000, 'actual_profit' => 25, 'profit_threshold' => 1, 'content' => '核心合伙人，最高等级。', 'upgrade_mode' => 'any', 'upgrade_direct_count' => 100, 'upgrade_consume_amount' => 10000, 'upgrade_team_count' => 500],
            ],
        ];
        $list = $presets[$presetKey] ?? $presets['a'];
        $out = [];
        foreach ($list as $i => $row) {
            $row['profit_rule_id'] = 0;
            $row['duration_days'] = 0;
            $row['renew_ratio'] = 0;
            $row['upgrade_mode'] = $row['upgrade_mode'] ?? 'any';
            $row['upgrade_direct_count'] = $row['upgrade_direct_count'] ?? 0;
            $row['upgrade_consume_amount'] = $row['upgrade_consume_amount'] ?? 0;
            $row['upgrade_team_count'] = $row['upgrade_team_count'] ?? 0;
            $row['sort'] = $i + 1;
            $row['state'] = 1;
            $row['create_time'] = $now;
            $row['update_time'] = $now;
            $out[] = $row;
        }
        return $out;
    }

    /**
     * 获取默认等级（is_default=1 的等级行）
     * @return array|null
     */
    public function getDefaultLevel() {
        $defaultId = $this->getDefaultLevelId();
        if ($defaultId <= 0) return null;
        return $this->db->once_fetch_array("SELECT * FROM {$this->table} WHERE id={$defaultId} AND state=1 LIMIT 1");
    }

    /**
     * 获取默认等级 ID
     * @return int
     */
    public function getDefaultLevelId() {
        $optionValue = $this->getDefaultOptionValue();
        if ($optionValue > 0) {
            $row = $this->db->once_fetch_array("SELECT id FROM {$this->table} WHERE id={$optionValue} AND state=1 LIMIT 1");
            if ($row) {
                return (int)$row['id'];
            }
        }
        $row = $this->db->once_fetch_array("SELECT id FROM {$this->table} WHERE is_default=1 AND state=1 ORDER BY sort ASC, id ASC LIMIT 1");
        if ($row) {
            return (int)$row['id'];
        }
        return $this->getFirstLevelId();
    }

    private function getFirstLevelId() {
        $row = $this->db->once_fetch_array("SELECT id FROM {$this->table} WHERE state=1 ORDER BY sort ASC, id ASC LIMIT 1");
        if ($row) {
            return (int)$row['id'];
        }
        $row = $this->db->once_fetch_array("SELECT id FROM {$this->table} ORDER BY sort ASC, id ASC LIMIT 1");
        return $row ? (int)$row['id'] : 0;
    }

    /**
     * 设置某个等级为默认等级（并取消其他默认）
     */
    public function setDefault($id) {
        $id = intval($id);
        if ($id <= 0) {
            $id = $this->getFirstLevelId();
        }
        if ($id <= 0) return false;
        $row = $this->db->once_fetch_array("SELECT id FROM {$this->table} WHERE id={$id} LIMIT 1");
        if (empty($row)) {
            $id = $this->getFirstLevelId();
            if ($id <= 0) return false;
        }
        $this->db->query("UPDATE {$this->table} SET is_default=0 WHERE is_default=1");
        $this->db->query("UPDATE {$this->table} SET is_default=1, state=1, update_time=" . time() . " WHERE id={$id}");
        $this->syncDefaultOption($id);
        return true;
    }

    /**
     * 确保至少有一个默认等级，并将无效等级用户迁移到默认等级
     */
    private function _ensureDefaultLevel() {
        try {
            $optionValue = $this->getDefaultOptionValue();
            $defaultId = 0;
            if ($optionValue > 0) {
                $row = $this->db->once_fetch_array("SELECT id FROM {$this->table} WHERE id={$optionValue} AND state=1 LIMIT 1");
                if ($row) {
                    $defaultId = (int)$row['id'];
                }
            }
            if ($defaultId <= 0) {
                $row = $this->db->once_fetch_array("SELECT id FROM {$this->table} WHERE is_default=1 AND state=1 ORDER BY sort ASC, id ASC LIMIT 1");
                if ($row) {
                    $defaultId = (int)$row['id'];
                }
            }
            if ($defaultId <= 0) {
                $defaultId = $this->getFirstLevelId();
            }
            $this->db->query("UPDATE {$this->table} SET is_default=0 WHERE is_default=1");
            if ($defaultId > 0) {
                $this->db->query("UPDATE {$this->table} SET is_default=1, state=1, update_time=" . time() . " WHERE id={$defaultId}");
            }
            if ($defaultId > 0 && intval($optionValue) !== $defaultId) {
                $this->syncDefaultOption($defaultId);
            }
            if ($defaultId > 0) {
                $userTable = DB_PREFIX . 'user';
                $defaultExpire = class_exists('Level_Service') ? Level_Service::calculateDefaultAssignExpireTime(0, $defaultId) : 0;
                $this->db->query("UPDATE {$userTable} SET level={$defaultId}, level_expire_time={$defaultExpire} WHERE level<=0 OR level IS NULL");
                $this->db->query("UPDATE {$userTable} u LEFT JOIN {$this->table} m ON u.level = m.id SET u.level={$defaultId}, u.level_expire_time={$defaultExpire} WHERE u.level>0 AND m.id IS NULL");
                if ($defaultExpire === 0) {
                    $this->db->query("UPDATE {$userTable} SET level_expire_time=0 WHERE level={$defaultId} AND level_expire_time<>0");
                }
            }
        } catch (Exception $e) {}
    }

    private function getDefaultOptionValue() {
        $value = Option::get(self::DEFAULT_OPTION_KEY);
        if ($value === false || $value === null || $value === '') {
            return null;
        }
        return intval($value);
    }

    private function syncDefaultOption($id) {
        $id = intval($id);
        $current = Option::get(self::DEFAULT_OPTION_KEY);
        if ((string)$current === (string)$id) {
            return;
        }
        Option::updateOption(self::DEFAULT_OPTION_KEY, (string)$id);
        $cache = Cache::getInstance();
        $cache->updateCache('options');
        if (class_exists('Level_Service')) {
            Level_Service::clearSettingsCache();
        }
    }

    /**
     * 过滤输入字段，仅保留允许的列
     */
    private function _filterFields($data, $isCreate = false) {
        $allow = [
            'name' => 'string',
            'icon' => 'string',
            'icon_image' => 'string',
            'price' => 'float',
            'markup_ratio' => 'float',
            'exchange_ratio' => 'float',
            'actual_profit' => 'float',
            'profit_threshold' => 'float',
            'profit_rule_id' => 'int',
            'duration_days' => 'int',
            'renew_ratio' => 'float',
            'content' => 'string',
            'sort' => 'int',
            'state' => 'int',
            'upgrade_mode' => 'string',
            'upgrade_direct_count' => 'int',
            'upgrade_consume_amount' => 'float',
            'upgrade_team_count' => 'int',
        ];
        $out = [];
        foreach ($allow as $field => $type) {
            if (!array_key_exists($field, $data)) continue;
            $v = $data[$field];
            if ($type === 'int') $v = intval($v);
            elseif ($type === 'float') $v = floatval($v);
            else $v = is_string($v) ? $v : (string)$v;
            $out[$field] = $v;
        }
        if ($isCreate && !isset($out['state'])) {
            $out['state'] = 1;
        }
        return $out;
    }

    /**
     * 兼容旧调用：仅传名称的 add 与 edit
     */
    public function add($name) {
        return $this->create(['name' => $name]);
    }

    public function edit($id, $name) {
        return $this->updateById($id, ['name' => $name]);
    }

    /**
     * 兼容旧调用：判断用户是否存在
     */
    public function isUserExist($user_name, $uid = '') {
        if (empty($user_name)) return false;
        $subSql = $uid ? 'and uid!=' . intval($uid) : '';
        $data = $this->db->once_fetch_array("SELECT COUNT(*) AS total FROM {$this->table} WHERE name='" . addslashes($user_name) . "' $subSql");
        return $data['total'] > 0;
    }

    /**
     * 等级总数
     */
    public function getMemberCount() {
        $res = $this->db->once_fetch_array("SELECT COUNT(*) AS total FROM {$this->table}");
        return intval($res['total']);
    }

    // ==============================
    // 前台等级中心辅助方法
    // ==============================

    /**
     * 获取启用的可开通等级列表（带购买金额计算）
     * @param int|null $currentLevelId  当前有效等级ID，无有效等级时为空
     * @return array 每项含 id/name/price/open_price/renew_price/duration_days/content/is_current/is_higher
     */
    public function getLevelsForFront($currentLevelId = 0, $currentExpire = 0) {
        $list = $this->getActiveList();
        $currentLevelId = intval($currentLevelId);
        if ($currentLevelId <= 0) {
            $currentLevelId = null;
        }
        $currentExpire = intval($currentExpire);
        $out = [];
        foreach ($list as $row) {
            $price = floatval($row['price']);
            $renewRatio = class_exists('Level_Service') ? Level_Service::resolveEffectiveRenewRatio($row) : floatval($row['renew_ratio'] ?? 100);
            $renewPrice = round($price * $renewRatio / 100, 2);
            $row['open_price'] = $price;
            $row['renew_price'] = $renewPrice;
            $row['is_current'] = $currentLevelId !== null && (intval($row['id']) === $currentLevelId);
            $row['is_higher'] = (floatval($row['price']) > 0) && ($currentLevelId === null || intval($row['id']) !== $currentLevelId);
            $row['display_price'] = $price;  // 卡片展示价 = 等级原价，不随升级模式变
            $row['pay_amount'] = $price;      // 实付金额，默认 = 原价
            $row['purchase_type'] = 'open';
            $row['purchase_label'] = '开通';
            $row['can_purchase'] = !$row['is_current'] && $price > 0;
            $row['purchase_disabled_reason'] = '';
            if (!$row['is_current']) {
                $check = Level_Service::validatePurchaseTarget($currentLevelId, intval($row['id']));
                if ($check !== '') {
                    $row['can_purchase'] = false;
                    $row['purchase_disabled_reason'] = $check;
                    $row['purchase_label'] = '已拥有';
                } else {
                    $quote = Level_Service::calculateUpgradePrice($currentLevelId, intval($row['id']), $currentExpire);
                    if (!empty($quote)) {
                        $row['pay_amount'] = floatval($quote['amount']);
                        $row['purchase_type'] = (string)$quote['type'];
                        if ($quote['type'] === 'upgrade') {
                            $payAmt = floatval($quote['amount']);
                            $upgradeMode = (string)Level_Service::getSetting(Level_Service::OPT_UPGRADE_PRICE_MODE, 'diff');
                            if ($upgradeMode === 'diff' && $payAmt < $price && $payAmt > 0) {
                                $row['purchase_label'] = '补 ¥' . number_format($payAmt, 2) . ' 升级';
                            } else {
                                $row['purchase_label'] = '¥' . number_format($payAmt, 2) . ' 升级';
                            }
                        } elseif ($quote['type'] === 'renew') {
                            $row['purchase_label'] = '¥' . number_format(floatval($quote['amount']), 2) . ' 续费';
                        }
                        $row['can_purchase'] = floatval($quote['amount']) > 0;
                        if (!$row['can_purchase']) {
                            $row['purchase_label'] = '免费等级';
                        }
                    } else {
                        $row['can_purchase'] = false;
                        $row['purchase_disabled_reason'] = '等级不存在或已禁用';
                    }
                }
            }
            $out[] = $row;
        }
        return $out;
    }

    /**
     * 获取用户当前等级状态
     * @param int $uid
     * @return array [level_id, level_name, expire_time, expire_text, is_permanent, is_expired]
     */
    public function getUserLevelStatus($uid) {
        $uid = intval($uid);
        $userTable = DB_PREFIX . 'user';
        $user = $this->db->once_fetch_array("SELECT level, level_expire_time FROM {$userTable} WHERE uid={$uid} LIMIT 1");
        $levelId = intval($user['level'] ?? 0);
        $expire = intval($user['level_expire_time'] ?? 0);
        // level_expire_time=0 表示永久，>0 表示有到期时间

        $name = '未开通等级';
        $duration = 0;
        if ($levelId > 0) {
            $lv = $this->getById($levelId);
            if (!empty($lv)) {
                $name = $lv['name'];
                $duration = intval($lv['duration_days'] ?? 0);
            }
        }

        $now = time();
        $isPermanent = ($levelId > 0) && ($expire == 0);
        $isExpired = ($levelId > 0) && $expire > 0 && $expire < $now;

        $expireText = '';
        if ($levelId <= 0) {
            $expireText = '未开通等级';
        } elseif ($isPermanent) {
            $expireText = '永久有效';
        } elseif ($isExpired) {
            $expireText = '已过期（' . date('Y-m-d', $expire) . '）';
        } else {
            $remain = max(0, $expire - $now);
            $remainDays = ceil($remain / 86400);
            $expireText = date('Y-m-d', $expire) . ' 到期（剩余 ' . $remainDays . ' 天）';
        }

        // 过期等级视为无等级：购买验证/价格计算应按 open 处理，而非阻止降级购买
        $statusLevelId = ($levelId > 0 && !$isExpired) ? $levelId : null;

        return [
            'level_id' => $statusLevelId,
            'raw_level_id' => $levelId > 0 ? $levelId : null,
            'level_name' => $name,
            'expire_time' => $expire,
            'expire_text' => $expireText,
            'is_permanent' => $isPermanent,
            'is_expired' => $isExpired,
        ];
    }

    /**
     * 计算指定等级的购买价格与业务数据
     * @param int $levelId
     * @param int $currentLevelId  用户当前等级（用于判断 open/renew/upgrade）
     * @return array|null [level, purchase_type, amount, duration_days]
     */
    public function calculatePurchase($levelId, $currentLevelId = 0) {
        $level = $this->getById($levelId);
        if (empty($level) || intval($level['state']) !== 1) return null;

        $currentLevelId = intval($currentLevelId);
        if ($currentLevelId <= 0) {
            $currentLevelId = null;
        }
        $openPrice = floatval($level['price']);
        $renewRatio = class_exists('Level_Service') ? Level_Service::resolveEffectiveRenewRatio($level) : floatval($level['renew_ratio'] ?? 100);
        $renewPrice = round($openPrice * $renewRatio / 100, 2);
        $duration = class_exists('Level_Service') ? Level_Service::resolveEffectiveDuration($level) : intval($level['duration_days'] ?? 0);

        // 判断类型
        if ($currentLevelId !== null && $currentLevelId === intval($level['id'])) {
            $type = 'renew';
            $amount = $renewPrice;
        } else {
            $type = 'open';
            $amount = $openPrice;
        }

        return [
            'level' => $level,
            'purchase_type' => $type,
            'amount' => $amount,
            'duration_days' => $duration,
        ];
    }

    // ==============================
    // 自动升级检查
    // ==============================

    /**
     * 获取用户直推粉丝数（superior = uid 的用户数）
     */
    public function countDirectFans($uid) {
        $uid = (int)$uid;
        if ($uid <= 0) return 0;
        $userTable = DB_PREFIX . 'user';
        $row = $this->db->once_fetch_array("SELECT COUNT(*) AS c FROM {$userTable} WHERE superior={$uid} AND state=0");
        return (int)($row['c'] ?? 0);
    }

    /**
     * 获取用户累计消费金额（已付款的商品订单，排除 level_upgrade / balance_recharge 等非商品订单）
     * amount 字段单位是分，返回元
     */
    public function sumConsumeAmount($uid) {
        $uid = (int)$uid;
        if ($uid <= 0) return 0;
        $orderTable = DB_PREFIX . 'order';
        $row = $this->db->once_fetch_array(
            "SELECT COALESCE(SUM(amount), 0) AS total FROM {$orderTable} WHERE user_id={$uid} AND pay_status=1 AND (device IS NULL OR device NOT IN ('level_upgrade','balance_recharge'))"
        );
        return round((int)($row['total'] ?? 0) / 100, 2);
    }

    /**
     * 获取用户团队总人数（递归统计所有直推 + 间接下级）
     * 使用迭代 BFS 避免深递归爆栈
     */
    public function countTeamFans($uid) {
        $uid = (int)$uid;
        if ($uid <= 0) return 0;
        $userTable = DB_PREFIX . 'user';
        $total = 0;
        $queue = [$uid];
        $visited = [$uid => true];
        while (!empty($queue)) {
            $parentIds = implode(',', array_map('intval', $queue));
            $queue = [];
            $rows = $this->db->fetch_all("SELECT uid FROM {$userTable} WHERE superior IN ({$parentIds}) AND state=0");
            if (empty($rows)) break;
            foreach ($rows as $r) {
                $childUid = (int)$r['uid'];
                if (!isset($visited[$childUid])) {
                    $visited[$childUid] = true;
                    $total++;
                    $queue[] = $childUid;
                }
            }
        }
        return $total;
    }

    /**
     * 检查用户是否满足某个等级的自动升级条件
     * @param int $uid
     * @param array $level  等级行（含 upgrade_mode / upgrade_direct_count / upgrade_consume_amount / upgrade_team_count）
     * @return bool
     */
    public function checkAutoUpgradeConditions($uid, $level) {
        $directReq  = (int)($level['upgrade_direct_count'] ?? 0);
        $consumeReq = (float)($level['upgrade_consume_amount'] ?? 0);
        $teamReq    = (int)($level['upgrade_team_count'] ?? 0);

        // 没有设置任何自动升级条件
        if ($directReq <= 0 && $consumeReq <= 0 && $teamReq <= 0) {
            return false;
        }

        $mode = ($level['upgrade_mode'] ?? 'any') === 'all' ? 'all' : 'any';
        $conditions = []; // 收集启用的条件结果

        if ($directReq > 0) {
            $conditions[] = $this->countDirectFans($uid) >= $directReq;
        }
        if ($consumeReq > 0) {
            $conditions[] = $this->sumConsumeAmount($uid) >= $consumeReq;
        }
        if ($teamReq > 0) {
            $conditions[] = $this->countTeamFans($uid) >= $teamReq;
        }

        if (empty($conditions)) return false;

        if ($mode === 'all') {
            // 全部条件都必须满足
            foreach ($conditions as $c) {
                if (!$c) return false;
            }
            return true;
        } else {
            // 满足任一即可
            foreach ($conditions as $c) {
                if ($c) return true;
            }
            return false;
        }
    }

    /**
     * 尝试为用户自动升级（检查所有比当前等级更高的等级，从高到低匹配第一个满足条件的）
     * @param int $uid
     * @return array|null  升级成功返回 ['old_level_id'=>, 'new_level_id'=>, 'new_level_name'=>]；无需升级返回 null
     */
    public function tryAutoUpgrade($uid) {
        $uid = (int)$uid;
        if ($uid <= 0) return null;

        $userTable = DB_PREFIX . 'user';
        $user = $this->db->once_fetch_array("SELECT level, level_expire_time FROM {$userTable} WHERE uid={$uid} LIMIT 1");
        if (empty($user)) return null;

        $currentLevelId = (int)($user['level'] ?? 0);
        $currentSort = 0;
        if ($currentLevelId > 0) {
            $curLevel = $this->getById($currentLevelId);
            if ($curLevel) {
                $currentSort = (int)$curLevel['sort'];
            }
        }

        // 获取所有启用的等级，按 sort 降序（从高到低）
        $allLevels = $this->db->fetch_all("SELECT * FROM {$this->table} WHERE state=1 ORDER BY sort DESC, id DESC");
        if (empty($allLevels)) return null;

        foreach ($allLevels as $level) {
            $lvSort = (int)$level['sort'];
            $lvId = (int)$level['id'];
            // 只检查比当前更高的等级
            if ($lvSort <= $currentSort && $lvId !== $currentLevelId) continue;
            if ($lvId === $currentLevelId) continue; // 跳过当前等级

            if ($this->checkAutoUpgradeConditions($uid, $level)) {
                // 满足条件，执行升级
                $now = time();
                $newExpire = 0;
                if (class_exists('Level_Service')) {
                    $duration = Level_Service::resolveEffectiveDuration($level);
                    if ($duration > 0) {
                        $newExpire = $now + $duration * 86400;
                    }
                }
                $this->db->query("UPDATE {$userTable} SET level={$lvId}, level_expire_time={$newExpire}, update_time={$now} WHERE uid={$uid}");

                // 写日志
                if (class_exists('User_Log_Model')) {
                    $oldName = $currentLevelId > 0 ? ($curLevel['name'] ?? '未知') : '无等级';
                    User_Log_Model::log(
                        $uid,
                        'auto_upgrade',
                        "自动升级：{$oldName} → {$level['name']}（满足自动升级条件）"
                    );
                }

                return [
                    'old_level_id' => $currentLevelId,
                    'new_level_id' => $lvId,
                    'new_level_name' => $level['name'],
                ];
            }
        }

        return null;
    }
}

