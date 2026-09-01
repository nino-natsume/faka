<?php
class Admin_Group_Model {

    const DEFAULT_GROUP_NAME = '默认后台组';

    private $db;
    private $table;
    private static $schemaChecked = false;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->table = DB_PREFIX . 'admin_group';
        new User_Model();
        $this->ensureSchema();
    }

    private function ensureSchema() {
        if (self::$schemaChecked) {
            return;
        }
        self::$schemaChecked = true;
        $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
            `menu_permissions` text COLLATE utf8mb4_unicode_ci,
            `create_time` int(11) NOT NULL DEFAULT '0',
            `update_time` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            KEY `idx_name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $this->ensureDefaultGroup();
    }

    private function ensureDefaultGroup() {
        $defaultName = addslashes(self::DEFAULT_GROUP_NAME);
        $defaultPermissions = Admin_Permission_Service::encodePermissions(Admin_Permission_Service::getAllPermissionKeys());
        $row = $this->db->once_fetch_array("SELECT id, menu_permissions FROM {$this->table} WHERE name='{$defaultName}' ORDER BY id ASC LIMIT 1");
        if (empty($row)) {
            $now = time();
            $this->db->add('admin_group', [
                'name' => self::DEFAULT_GROUP_NAME,
                'menu_permissions' => $defaultPermissions,
                'create_time' => $now,
                'update_time' => $now,
            ]);
            return (int)$this->db->insert_id();
        }
        $currentId = (int)($row['id'] ?? 0);
        $currentPermissions = Admin_Permission_Service::encodePermissions($row['menu_permissions'] ?? []);
        if ($currentPermissions !== $defaultPermissions) {
            $this->db->update('admin_group', [
                'menu_permissions' => $defaultPermissions,
                'update_time' => time(),
            ], ['id' => $currentId]);
        }
        return $currentId;
    }

    public function getDefaultGroupId() {
        return (int)$this->ensureDefaultGroup();
    }

    public function isDefaultGroup($id) {
        $id = (int)$id;
        return $id > 0 && $id === $this->getDefaultGroupId();
    }

    public function getAll($withStats = false) {
        $defaultGroupId = $this->getDefaultGroupId();
        $rows = [];
        if ($withStats) {
            $userTable = DB_PREFIX . 'user';
            $sql = "SELECT g.*, COUNT(u.uid) AS account_count
                FROM {$this->table} g
                LEFT JOIN {$userTable} u ON u.admin_group_id = g.id AND u.role = 'admin'
                GROUP BY g.id
                ORDER BY g.id ASC";
        } else {
            $sql = "SELECT * FROM {$this->table} ORDER BY id ASC";
        }
        $res = $this->db->query($sql);
        while ($row = $this->db->fetch_array($res)) {
            $row['id'] = (int)$row['id'];
            $row['account_count'] = (int)($row['account_count'] ?? 0);
            $row['create_time'] = (int)$row['create_time'];
            $row['update_time'] = (int)$row['update_time'];
            $row['is_default_group'] = $row['id'] === $defaultGroupId ? 1 : 0;
            $rows[] = $row;
        }
        return $rows;
    }

    public function getById($id) {
        $id = (int)$id;
        if ($id <= 0) {
            return [];
        }
        $row = $this->db->once_fetch_array("SELECT * FROM {$this->table} WHERE id={$id} LIMIT 1");
        if (empty($row)) {
            return [];
        }
        $row['id'] = (int)$row['id'];
        $row['create_time'] = (int)$row['create_time'];
        $row['update_time'] = (int)$row['update_time'];
        $row['is_default_group'] = $this->isDefaultGroup($row['id']) ? 1 : 0;
        return $row;
    }

    public function isNameExist($name, $excludeId = 0) {
        $name = addslashes(trim($name));
        if ($name === '') {
            return false;
        }
        $excludeSql = $excludeId > 0 ? ' AND id<>' . (int)$excludeId : '';
        $row = $this->db->once_fetch_array("SELECT COUNT(*) AS total FROM {$this->table} WHERE name='{$name}'{$excludeSql}");
        return !empty($row['total']);
    }

    public function save($id, $name, $menuPermissions) {
        $id = (int)$id;
        $name = trim($name);
        if ($this->isDefaultGroup($id)) {
            $name = self::DEFAULT_GROUP_NAME;
            $permissions = Admin_Permission_Service::encodePermissions(Admin_Permission_Service::getAllPermissionKeys());
        } else {
            $permissions = Admin_Permission_Service::encodePermissions($menuPermissions);
        }
        $now = time();
        $data = [
            'name' => $name,
            'menu_permissions' => $permissions,
            'update_time' => $now,
        ];
        if ($id > 0) {
            $this->db->update('admin_group', $data, ['id' => $id]);
            return $id;
        }
        $data['create_time'] = $now;
        $this->db->add('admin_group', $data);
        return (int)$this->db->insert_id();
    }

    public function delete($id) {
        $id = (int)$id;
        if ($id <= 0) {
            return false;
        }
        if ($this->isDefaultGroup($id)) {
            return false;
        }
        $this->db->query("DELETE FROM {$this->table} WHERE id={$id}");
        return true;
    }

    public function getBindCount($id) {
        $id = (int)$id;
        $userTable = DB_PREFIX . 'user';
        $row = $this->db->once_fetch_array("SELECT COUNT(*) AS total FROM {$userTable} WHERE admin_group_id={$id} AND role='admin'");
        return (int)($row['total'] ?? 0);
    }
}
