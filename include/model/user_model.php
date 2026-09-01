<?php
/**
 * user model
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

class User_Model {

    private $db;
    private $table;
    private static $schemaChecked = false;
    private static $userIdStartChecked = false;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->table = DB_PREFIX . 'user';
        $this->ensureSchema();
    }

    private function ensureSchema() {
        if (self::$schemaChecked) {
            return;
        }
        self::$schemaChecked = true;
        $columns = [];
        $res = $this->db->query("SHOW COLUMNS FROM `{$this->table}`");
        while ($row = $this->db->fetch_array($res)) {
            $columns[$row['Field']] = true;
        }
        if (!isset($columns['withdraw_receipt_image'])) {
            $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `withdraw_receipt_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '默认提现收款码' AFTER `photo`");
        }
        if (!isset($columns['admin_group_id'])) {
            $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `admin_group_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '后台账户用户组ID' AFTER `role`");
        }
        if (!isset($columns['delete_time'])) {
            $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `delete_time` bigint(16) DEFAULT NULL COMMENT '删除时间' AFTER `update_time`");
        }
        $this->ensureUserIdStart();
    }

    private function ensureUserIdStart() {
        if (self::$userIdStartChecked) {
            return;
        }
        self::$userIdStartChecked = true;
        $userIdStart = class_exists('User') ? (int)User::USER_ID_START : 1000;
        if ($userIdStart <= 0) {
            $userIdStart = 1000;
        }
        try {
            $tableName = addslashes($this->table);
            $schemaName = addslashes(DB_NAME);
            $row = $this->db->once_fetch_array("SELECT AUTO_INCREMENT AS next_id FROM information_schema.TABLES WHERE TABLE_SCHEMA='{$schemaName}' AND TABLE_NAME='{$tableName}' LIMIT 1");
            $nextId = (int)($row['next_id'] ?? 0);
            if ($nextId <= 0 || $nextId < $userIdStart) {
                $this->db->query("ALTER TABLE `{$this->table}` AUTO_INCREMENT={$userIdStart}");
            }
        } catch (Throwable $e) {
        }
    }

    public function expendInc($user_id, $money){
        $sql = "update " . DB_PREFIX . "user set expend = expend + {$money} where uid={$user_id}";
        $this->db->query($sql);
    }

    public function getUsers($email = '', $nickname = '', $admin = '', $page = 1) {
        $condition = $limit = '';
        if ($email) {
            $condition = " and email like '$email%'";
        }
        if ($nickname) {
            $condition = " and nickname like '%$nickname%'";
        }
        if ($admin) {
            $condition = " and role IN('admin','editor')";
        }
        if ($page) {
            $perpage_num = Option::get('admin_article_perpage_num');
            $startId = ($page - 1) * $perpage_num;
            $limit = "LIMIT $startId, " . $perpage_num;
        }
        $res = $this->db->query("SELECT u.*, m.name level_name FROM $this->table u left join " . DB_PREFIX . "member m on u.level=m.id  where 1=1 and u.delete_time IS NULL $condition order by uid desc $limit");
        $users = [];
        while ($row = $this->db->fetch_array($res)) {
            $row['name'] = htmlspecialchars($row['nickname']);
            $row['login'] = htmlspecialchars($row['username']);
            $row['email'] = htmlspecialchars($row['email']);
            $row['description'] = htmlspecialchars($row['description']);
            $row['create_time'] = smartDate($row['create_time']);
            $row['update_time'] = smartDate($row['update_time']);
            $row['role'] = User::getRoleName($row['role'], (int)$row['uid']);
            $users[] = $row;
        }
        return $users;
    }

    public function getOneUser($uid) {
        $uid = (int)$uid;
        $row = $this->db->once_fetch_array("select * from $this->table where uid=$uid and delete_time IS NULL");

        if (empty($row)) {
            return [];
        }

        $row['username'] = htmlspecialchars($row['username']);
        $row['nickname'] = htmlspecialchars(empty($row['nickname']) ? $row['username'] : $row['nickname']);
        $row['name_orig'] = $row['nickname'];
        $row['email'] = htmlspecialchars($row['email']);
        $row['photo'] = htmlspecialchars($row['photo']);
        $row['withdraw_receipt_image'] = htmlspecialchars((string)($row['withdraw_receipt_image'] ?? ''));
        $row['description'] = htmlspecialchars($row['description']);
        $row['state'] = (int)$row['state'];
        $row['credits'] = (int)$row['credits'];
        $row['level'] = (int)$row['level'];
        $row['level_expire_time'] = (int)($row['level_expire_time'] ?? 0);

        return $row;
    }

    public function updateUser($userData, $uid) {
        $utctimestamp = time();
        $Item = ["update_time=$utctimestamp"];
        foreach ($userData as $key => $data) {
            $Item[] = "$key='$data'";
        }
        $upStr = implode(',', $Item);
        $this->db->query("update $this->table set $upStr where uid=$uid");
        
        // 触发用户资料更新钩子
        doAction('user_profile_update', $uid, $userData);
    }

    public function updateUserByMail($userData, $mail) {
        $timestamp = time();
        $Item = ["update_time=$timestamp"];
        foreach ($userData as $key => $data) {
            $Item[] = "$key='$data'";
        }
        $upStr = implode(',', $Item);
        $this->db->query("update $this->table set $upStr where email='$mail'");
    }

    public function addUser($username, $mail_tel, $password, $reg_ip, $role, $type) {
        $timestamp = time();
        $nickname = getRandStr(8, false);
        if($type == 'tel'){
            $sql = "insert into $this->table (username, reg_ip, tel,password,nickname,role,create_time,update_time) values('$username','{$reg_ip}','$mail_tel','$password','$nickname','$role',$timestamp,$timestamp)";
        }
        if($type == 'email'){
            $sql = "insert into $this->table (username, reg_ip, email,password,nickname,role,create_time,update_time) values('$username','{$reg_ip}','$mail_tel','$password','$nickname','$role',$timestamp,$timestamp)";
        }
        if($type == 'username'){
            $sql = "insert into $this->table (username, reg_ip, password,nickname,role,create_time,update_time) values('$username','{$reg_ip}','$password','$nickname','$role',$timestamp,$timestamp)";
        }

        $this->db->query($sql);
    }

    public function deleteUser($uid) {
        $founderUid = class_exists('User') ? (int)User::getFounderUid() : 1;
        if ($founderUid <= 0) {
            $founderUid = 1;
        }
        $this->db->query("update " . DB_PREFIX . "blog set author={$founderUid}, checked='y' where author=$uid");
        $this->db->query("update $this->table set delete_time=" . time() . " where uid=$uid and delete_time IS NULL");
    }

    public function forbidUser($uid) {
        $this->db->query("update $this->table set state=1 where uid=$uid");
    }

    public function unforbidUser($uid) {
        $this->db->query("update $this->table set state=0 where uid=$uid");
    }

    /**
     * check the username exists
     *
     * @param string $user_name
     * @param int $uid 兼容更新作者资料时用户名未变更情况
     * @return boolean
     */
    public function isUserExist($user_name, $uid = '') {
        if (empty($user_name)) {
            return false;
        }
        $subSql = $uid ? 'and uid!=' . $uid : '';
        $data = $this->db->once_fetch_array("SELECT COUNT(*) AS total FROM $this->table WHERE username='$user_name' $subSql");
        return $data['total'] > 0;
    }

    public function isNicknameExist($nickname, $uid = '') {
        if (empty($nickname)) {
            return FALSE;
        }
        $subSql = $uid ? 'and uid!=' . $uid : '';
        $data = $this->db->once_fetch_array("SELECT COUNT(*) AS total FROM $this->table WHERE nickname='$nickname' $subSql");
        return $data['total'] > 0;
    }

    public function isMailExist($mail, $uid = '') {
        if (empty($mail)) {
            return FALSE;
        }
        $subSql = $uid ? 'and uid!=' . $uid : '';
        $data = $this->db->once_fetch_array("SELECT COUNT(*) AS total FROM $this->table WHERE email='$mail' $subSql");
        return $data['total'] > 0;
    }

    public function isTelExist($tel, $uid = '') {
        if (empty($tel)) {
            return FALSE;
        }
        $subSql = $uid ? 'and uid!=' . $uid : '';
        $data = $this->db->once_fetch_array("SELECT COUNT(*) AS total FROM $this->table WHERE tel='$tel' $subSql");
        return $data['total'] > 0;
    }

    public function getCount() {
        $sql = "SELECT COUNT(*) AS total FROM {$this->table} where 1=1";
        $res = $this->db->once_fetch_array($sql);
        return $res['total'];
    }

    public function getUserCount($email = '', $nickname = '', $admin = '')
    {
        $condition = '';
        if ($email) {
            $condition = " and email like '$email%'";
        }
        if ($nickname) {
            $condition = " and nickname like '%$nickname%'";
        }
        if ($admin) {
            $condition = " and role IN('admin','editor')";
        }
        $data = $this->db->once_fetch_array("SELECT COUNT(*) AS total FROM $this->table where 1=1 $condition");
        return $data['total'];
    }

    /**
     * 增加用户的积分
     */
    public function addCredits($uid, $count) {
        $uid = (int)$uid;
        $count = (int)$count;
        if ($count < 0) {
            $count = 0;
        }
        $this->db->query("UPDATE $this->table SET credits=credits+$count WHERE uid=$uid");
        return true;
    }

    /**
     * 减少用户的积分
     */
    public function reduceCredits($uid, $count) {
        $uid = (int)$uid;
        $count = (int)$count;
        if ($count < 0) {
            $count = 0;
        }
        $this->db->query("UPDATE $this->table SET credits = IF(credits >= $count, credits - $count, 0) WHERE uid = $uid");
        return true;
    }

    /**
     * 生成唯一的邀请码（大写字母+数字，8 位）
     * 失败时返回空串，调用方需自行处理
     */
    public static function generateInviteCode($uid = 0) {
        $db = Database::getInstance();
        $table = DB_PREFIX . 'user';
        for ($i = 0; $i < 10; $i++) {
            $candidate = strtoupper(substr(md5($uid . '|' . microtime(true) . '|' . mt_rand()), 0, 8));
            $exist = $db->once_fetch_array("SELECT uid FROM {$table} WHERE invite_code='{$candidate}' LIMIT 1");
            if (empty($exist)) {
                return $candidate;
            }
        }
        return '';
    }

}
