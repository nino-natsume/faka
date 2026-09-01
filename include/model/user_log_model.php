<?php
/**
 * 用户操作日志模型
 */

class User_Log_Model {

    private $db;
    private $table;
    private $db_prefix;
    private static $tableChecked = false;

    // 操作类型常量
    const TYPE_REGISTER       = 'register';
    const TYPE_LOGIN          = 'login';
    const TYPE_LOGOUT         = 'logout';
    const TYPE_PASSWORD_RESET = 'password_reset';
    const TYPE_PASSWORD_CHANGE = 'password_change';
    const TYPE_PROFILE_UPDATE = 'profile_update';
    const TYPE_BALANCE_INC    = 'balance_inc';
    const TYPE_BALANCE_DEC    = 'balance_dec';
    const TYPE_WITHDRAW_APPLY = 'withdraw_apply';
    const TYPE_WITHDRAW_PASS  = 'withdraw_pass';
    const TYPE_WITHDRAW_REJECT = 'withdraw_reject';
    const TYPE_ORDER_CREATE   = 'order_create';
    const TYPE_ORDER_PAY      = 'order_pay';
    const TYPE_LEVEL_CHANGE   = 'level_change';
    const TYPE_STATION_OPEN   = 'station_open';
    const TYPE_USER_FORBID    = 'user_forbid';
    const TYPE_USER_UNFORBID  = 'user_unforbid';
    const TYPE_ADMIN_CREATE   = 'admin_create';
    const TYPE_ADMIN_EDIT     = 'admin_edit';
    const TYPE_ADMIN_MONEY    = 'admin_money';
    const TYPE_ADMIN_CREDITS  = 'admin_credits';
    const TYPE_ADMIN_DELETE   = 'admin_delete';
    const TYPE_BALANCE_RECHARGE = 'balance_recharge';
    const TYPE_ORDER_REPAY    = 'order_repay';
    const TYPE_CARD_REDEEM   = 'card_redeem';
    const TYPE_WITHDRAW_RECEIPT_IMAGE_UPDATE = 'withdraw_receipt_image_update';
    const TYPE_STATION_UPGRADE    = 'station_upgrade';
    const TYPE_SUPERIOR_BIND      = 'superior_bind';
    const TYPE_SUPERIOR_BIND_MANUAL = 'superior_bind_manual';
    const TYPE_AVATAR_UPDATE      = 'avatar_update';
    const TYPE_AUTO_UPGRADE       = 'auto_upgrade';
    const TYPE_STATION_AUTO_UPGRADE = 'station_auto_upgrade';
    const TYPE_LEVEL_UPGRADE      = 'level_upgrade';
    const TYPE_BALANCE_COMMISSION = 'balance_commission';
    const TYPE_STATION_COMMISSION = 'station_commission';
    const TYPE_UPGRADE_COMMISSION = 'upgrade_commission';
    const TYPE_COMMISSION_REFUND  = 'commission_refund';
    const TYPE_COMMISSION_FAIL    = 'commission_fail';
    const TYPE_UPGRADE_COMMISSION_FAIL = 'upgrade_commission_fail';
    const TYPE_COMMISSION_REFUND_FAIL  = 'commission_refund_fail';
    const TYPE_ADMIN_RESTORE      = 'admin_restore';
    const TYPE_ADMIN_PERMANENT_DELETE = 'admin_permanent_delete';

    // 操作类型中文映射
    public static $typeNames = [
        'register'        => '用户注册',
        'login'           => '用户登录',
        'logout'          => '退出登录',
        'password_reset'  => '密码重置',
        'password_change' => '修改密码',
        'profile_update'  => '修改资料',
        'avatar_update'   => '更新头像',
        'api_whitelist_update' => '更新接口白名单',
        'api_key_reset'   => '重置接口密钥',
        'api_key_generate'=> '生成接口密钥',
        'balance_inc'     => '余额增加',
        'balance_dec'     => '余额扣减',
        'balance_recharge'=> '余额充值',
        'withdraw_apply'  => '申请提现',
        'withdraw_pass'   => '提现通过',
        'withdraw_reject' => '提现拒绝',
        'withdraw_receipt_image_update' => '更新收款码',
        'order_create'    => '创建订单',
        'order_pay'       => '订单支付',
        'order_repay'     => '订单补单',
        'order_delete'    => '删除订单',
        'level_change'    => '等级变更',
        'level_upgrade'   => '等级开通/续费',
        'auto_upgrade'    => '自动升级',
        'station_open'    => '开通分店',
        'station_upgrade' => '分店升级',
        'station_auto_upgrade' => '分店自动升级',
        'superior_bind'   => '绑定上级',
        'superior_bind_manual' => '后台改上级',
        'balance_commission'   => '订单分成',
        'station_commission'   => '分店佣金',
        'upgrade_commission'   => '升级奖励',
        'commission_refund'    => '分成退回',
        'commission_fail'      => '分成失败',
        'upgrade_commission_fail'  => '升级奖励失败',
        'commission_refund_fail'   => '退款反扣失败',
        'user_forbid'     => '账户禁用',
        'user_unforbid'   => '账户解禁',
        'admin_create'    => '后台建号',
        'admin_edit'      => '后台编辑',
        'admin_money'     => '后台调账',
        'admin_credits'   => '后台调积分',
        'admin_delete'    => '删除用户',
        'admin_restore'   => '后台恢复用户',
        'admin_permanent_delete' => '后台彻底删除',
        'card_redeem'     => '卡密兑换',
    ];

    public function __construct() {
        $this->db = Database::getInstance();
        $this->db_prefix = DB_PREFIX;
        $this->table = DB_PREFIX . 'user_log';
        $this->ensureTable();
    }

    /**
     * 自动建表
     */
    private function ensureTable() {
        if (self::$tableChecked) return;
        self::$tableChecked = true;

        $tables = $this->db->listTables();
        if (!in_array($this->table, $tables)) {
            $sql = "CREATE TABLE `{$this->table}` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `uid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户ID',
                `type` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '操作类型',
                `amount` DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT '涉及数量/金额',
                `content` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '日志内容',
                `ip` VARCHAR(50) NOT NULL DEFAULT '' COMMENT 'IP地址',
                `create_time` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '时间戳',
                INDEX `idx_uid` (`uid`),
                INDEX `idx_type` (`type`),
                INDEX `idx_create_time` (`create_time`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户操作日志'";
            $this->db->query($sql);
        }
    }

    /**
     * 写入日志（静态方法，方便全局调用）
     */
    public static function log($uid, $type, $content = '', $amount = 0, $ip = '') {
        try {
            $db = Database::getInstance();
            $prefix = DB_PREFIX;
            $table = $prefix . 'user_log';
            if (!self::$tableChecked) {
                $tables = $db->listTables();
                if (!in_array($table, $tables)) {
                    $sql = "CREATE TABLE `{$table}` (
                        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        `uid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户ID',
                        `type` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '操作类型',
                        `amount` DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT '涉及数量/金额',
                        `content` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '日志内容',
                        `ip` VARCHAR(50) NOT NULL DEFAULT '' COMMENT 'IP地址',
                        `create_time` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '时间戳',
                        INDEX `idx_uid` (`uid`),
                        INDEX `idx_type` (`type`),
                        INDEX `idx_create_time` (`create_time`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户操作日志'";
                    $db->query($sql);
                }
                self::$tableChecked = true;
            }
            if (empty($ip)) {
                $ip = function_exists('getIp') ? getIp() : ($_SERVER['REMOTE_ADDR'] ?? '');
            }
            $uid = intval($uid);
            $type = addslashes($type);
            $amount = floatval($amount);
            $content = addslashes($content);
            $ip = addslashes($ip);
            $time = time();
            $sql = "INSERT INTO `{$table}` (`uid`,`type`,`amount`,`content`,`ip`,`create_time`) VALUES ({$uid},'{$type}',{$amount},'{$content}','{$ip}',{$time})";
            $db->query($sql);
        } catch (Exception $e) {
            // 静默失败，不影响业务
        }
    }

    /**
     * 获取日志总数
     */
    public function getLogCount($where = '') {
        $sql = "SELECT COUNT(*) AS total FROM `{$this->table}` l WHERE 1=1 {$where}";
        $row = $this->db->once_fetch_array($sql);
        return intval($row['total']);
    }

    /**
     * 获取日志列表
     */
    public function getLogList($start, $limit, $where = '', $orderBy = 'ORDER BY l.id DESC') {
        $sql = "SELECT l.*, u.username AS user_username, u.nickname AS user_nickname, u.email AS user_email, u.tel AS user_tel
                FROM `{$this->table}` l
                LEFT JOIN `{$this->db_prefix}user` u ON l.uid = u.uid
                WHERE 1=1 {$where}
                {$orderBy}
                LIMIT {$start}, {$limit}";
        $res = $this->db->query($sql);
        $list = [];
        while ($row = $this->db->fetch_array($res)) {
            $row['type_name'] = isset(self::$typeNames[$row['type']]) ? self::$typeNames[$row['type']] : $row['type'];
            $row['create_time_text'] = date('Y-m-d H:i:s', $row['create_time']);
            $row['user_username'] = trim((string)($row['user_username'] ?? ''));
            $row['user_display'] = $row['user_nickname'] ?: ($row['user_username'] ?: ($row['user_tel'] ?: 'UID:' . $row['uid']));
            $list[] = $row;
        }
        return $list;
    }

    /**
     * 删除日志
     */
    public function deleteLog($ids) {
        if (empty($ids)) return;
        $ids = preg_replace('/[^0-9,]/', '', $ids);
        $this->db->query("DELETE FROM `{$this->table}` WHERE id IN ({$ids})");
    }

    /**
     * 清空指定用户的日志
     */
    public function clearByUid($uid) {
        $uid = intval($uid);
        $this->db->query("DELETE FROM `{$this->table}` WHERE uid = {$uid}");
    }

    /**
     * 获取所有已记录的操作类型（用于筛选下拉）
     */
    public function getLogTypes() {
        $sql = "SELECT DISTINCT type FROM `{$this->table}` ORDER BY type";
        $res = $this->db->query($sql);
        $types = [];
        while ($row = $this->db->fetch_array($res)) {
            $types[] = [
                'type' => $row['type'],
                'name' => isset(self::$typeNames[$row['type']]) ? self::$typeNames[$row['type']] : $row['type']
            ];
        }
        return $types;
    }
}
