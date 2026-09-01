<?php
/**
 * 博客导航模型
 * @package DCSHOP
 */

class Blog_Navi_Model {

    private $db;
    private $table;

    const navitype_custom = 0;    // 自定义
    const navitype_home = 1;      // 首页
    const navitype_blog = 7;      // 博客
    const navitype_blogsort = 6;  // 文章分类
    const navitype_page = 5;      // 页面

    function __construct() {
        $this->db = Database::getInstance();
        $this->table = DB_PREFIX . 'blog_navi';
        $this->checkTable();
    }

    /**
     * 检查并创建数据表
     */
    function checkTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `naviname` varchar(255) NOT NULL DEFAULT '',
            `url` varchar(512) NOT NULL DEFAULT '',
            `newtab` char(1) NOT NULL DEFAULT 'n',
            `hide` char(1) NOT NULL DEFAULT 'n',
            `taxis` int(11) NOT NULL DEFAULT '0',
            `pid` int(11) NOT NULL DEFAULT '0',
            `type` tinyint(4) NOT NULL DEFAULT '0',
            `type_id` int(11) NOT NULL DEFAULT '0',
            `isdefault` char(1) NOT NULL DEFAULT 'n',
            `naviicon` varchar(100) NOT NULL DEFAULT '',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->db->query($sql);
        
        // 检查并添加 naviicon 字段（兼容已存在的表）
        $checkSql = "SHOW COLUMNS FROM {$this->table} LIKE 'naviicon'";
        $result = $this->db->query($checkSql);
        if ($this->db->num_rows($result) == 0) {
            $alterSql = "ALTER TABLE {$this->table} ADD `naviicon` varchar(100) NOT NULL DEFAULT '' COMMENT '导航图标'";
            $this->db->query($alterSql);
        }
    }

    /**
     * 获取所有导航
     */
    function getNavis() {
        $navis = [];
        $CACHE = Cache::getInstance();
        $blogSorts = $CACHE->readCache('blog_sort');
        $blogSorts = is_array($blogSorts) ? $blogSorts : [];
        $query = $this->db->query("SELECT * FROM {$this->table} ORDER BY pid ASC, taxis DESC");
        while ($row = $this->db->fetch_array($query)) {
            $url = $this->getNaviUrl($row['type'], $row['type_id'], $row['url']);
            $children = [];
            if ((int)$row['type'] === self::navitype_blogsort && !empty($blogSorts[(int)$row['type_id']]['children'])) {
                foreach ($blogSorts[(int)$row['type_id']]['children'] as $childSortId) {
                    if (isset($blogSorts[$childSortId])) {
                        $children[] = $blogSorts[$childSortId];
                    }
                }
            }
            $naviData = array(
                'id'        => (int)$row['id'],
                'naviname'  => htmlspecialchars(trim($row['naviname'])),
                'url'       => htmlspecialchars(trim($url)),
                'newtab'    => $row['newtab'],
                'isdefault' => $row['isdefault'],
                'type'      => (int)$row['type'],
                'typeId'    => (int)$row['type_id'],
                'taxis'     => (int)$row['taxis'],
                'hide'      => $row['hide'],
                'pid'       => (int)$row['pid'],
                'naviicon'  => isset($row['naviicon']) ? $row['naviicon'] : '',
                'isParent'  => false,
                'children'  => $children,
            );
            $navis[$row['id']] = $naviData;
        }
        foreach ($navis as $id => $naviData) {
            $pid = (int)$naviData['pid'];
            if ($pid > 0 && isset($navis[$pid]) && $naviData['hide'] == 'n') {
                $navis[$pid]['isParent'] = true;
                if (!isset($navis[$pid]['childnavi'])) {
                    $navis[$pid]['childnavi'] = [];
                }
                $navis[$pid]['childnavi'][] = $naviData;
            }
        }
        return $navis;
    }

    /**
     * 获取全部导航原始数据（后台配置页列表使用）
     */
    function getAllNavisRaw() {
        $navis = [];
        $query = $this->db->query("SELECT * FROM {$this->table} ORDER BY taxis DESC, id ASC");
        while ($row = $this->db->fetch_array($query)) {
            $navis[] = [
                'id'           => (int)$row['id'],
                'naviname'     => trim($row['naviname']),
                'url'          => trim($row['url']),
                'resolved_url' => $this->getNaviUrl((int)$row['type'], (int)$row['type_id'], $row['url']),
                'newtab'       => $row['newtab'],
                'isdefault'    => $row['isdefault'],
                'type'         => (int)$row['type'],
                'type_id'      => (int)$row['type_id'],
                'typeId'       => (int)$row['type_id'],
                'taxis'        => (int)$row['taxis'],
                'hide'         => $row['hide'],
                'pid'          => (int)$row['pid'],
                'naviicon'     => isset($row['naviicon']) ? trim($row['naviicon']) : '',
            ];
        }
        return $navis;
    }

    /**
     * 获取导航URL
     */
    function getNaviUrl($type, $typeId, $url) {
        $type = (int)$type;
        $typeId = (int)$typeId;
        $url = trim((string)$url);
        switch ($type) {
            case self::navitype_home:
                // 博客导航的首页指向博客首页
                return function_exists('dcGetBlogHomeUrl') ? dcGetBlogHomeUrl() : DC_URL . 'blog';
            case self::navitype_blog:
                // 博客首页
                return function_exists('dcGetBlogHomeUrl') ? dcGetBlogHomeUrl() : DC_URL . 'blog';
            case self::navitype_blogsort:
                return Url::blogSort($typeId);
            case self::navitype_page:
                return Url::art($typeId);
            case self::navitype_custom:
            default:
                if ($url === '') {
                    return '#';
                }
                if (preg_match('#^(https?:)?//#i', $url)) {
                    return $url;
                }
                if (preg_match('/^[a-z][a-z0-9+.-]*:/i', $url)) {
                    return '#';
                }
                // 博客导航中的相对链接应保持相对，不强制拼接主站域名。
                // 例如后台快捷选择保存 /?blogsort=2，前台在博客独立域名下点击时仍留在当前博客域名。
                if ($url[0] === '?' || $url[0] === '&') {
                    return '/' . $url;
                }
                if ($url[0] === '/' || $url[0] === '#' || substr($url, 0, 2) === './' || substr($url, 0, 3) === '../') {
                    return $url;
                }
                return '/' . ltrim($url, '/');
        }
    }

    /**
     * 添加导航
     */
    function addNavi($name, $url, $taxis, $pid, $newtab, $type = 0, $typeId = 0, $naviicon = '') {
        $name = addslashes(trim((string)$name));
        $url = addslashes(trim((string)$url));
        $naviicon = addslashes(trim((string)$naviicon));
        $taxis = (int)$taxis;
        $pid = max(0, (int)$pid);
        $type = (int)$type;
        $typeId = max(0, (int)$typeId);
        $newtab = $newtab === 'y' ? 'y' : 'n';
        if ($taxis > 30000 || $taxis < 0) {
            $taxis = 0;
        }
        $sql = "INSERT INTO {$this->table} (naviname, url, taxis, pid, newtab, type, type_id, naviicon) 
                VALUES('$name', '$url', $taxis, $pid, '$newtab', $type, $typeId, '$naviicon')";
        $this->db->query($sql);
    }

    /**
     * 更新导航
     */
    function updateNavi($naviData, $navid) {
        $navid = (int)$navid;
        if ($navid <= 0) {
            return false;
        }
        $allowKeys = ['naviname', 'url', 'taxis', 'pid', 'newtab', 'hide', 'type', 'type_id', 'naviicon'];
        $Item = [];
        foreach ($naviData as $key => $data) {
            if (!in_array($key, $allowKeys, true)) {
                continue;
            }
            if (in_array($key, ['taxis', 'pid', 'type', 'type_id'], true)) {
                $Item[] = "$key=" . (int)$data;
            } elseif (in_array($key, ['newtab', 'hide'], true)) {
                $Item[] = "$key='" . ($data === 'y' ? 'y' : 'n') . "'";
            } else {
                $Item[] = "$key='" . addslashes(trim((string)$data)) . "'";
            }
        }
        if (empty($Item)) {
            return false;
        }
        $upStr = implode(',', $Item);
        $this->db->query("UPDATE {$this->table} SET $upStr WHERE id=$navid");
        return true;
    }

    /**
     * 获取单个导航
     */
    function getOneNavi($navid) {
        $navid = (int)$navid;
        if ($navid <= 0) {
            return [];
        }
        $sql = "SELECT * FROM {$this->table} WHERE id=$navid";
        $res = $this->db->query($sql);
        $row = $this->db->fetch_array($res);
        $naviData = [];
        if ($row) {
            $naviData = array(
                'naviname'  => htmlspecialchars(trim($row['naviname'])),
                'url'       => htmlspecialchars(trim($row['url'])),
                'newtab'    => $row['newtab'],
                'isdefault' => $row['isdefault'],
                'type'      => (int)$row['type'],
                'type_id'   => (int)$row['type_id'],
                'pid'       => (int)$row['pid'],
                'taxis'     => (int)$row['taxis'],
                'hide'      => $row['hide'],
                'naviicon'  => isset($row['naviicon']) ? $row['naviicon'] : '',
            );
        }
        return $naviData;
    }

    /**
     * 删除导航
     */
    function deleteNavi($navid) {
        $navid = (int)$navid;
        if ($navid <= 0) {
            return false;
        }
        $this->db->query("DELETE FROM {$this->table} WHERE id=$navid");
        $this->db->query("UPDATE {$this->table} SET pid=0 WHERE pid=$navid");
        return true;
    }

    /**
     * 检查导航是否存在
     */
    function naviExists($type, $typeId) {
        $type = (int)$type;
        $typeId = (int)$typeId;
        $sql = "SELECT id FROM {$this->table} WHERE type=$type AND type_id=$typeId LIMIT 1";
        $res = $this->db->query($sql);
        return $this->db->num_rows($res) > 0;
    }

    private function getDefaultNavis() {
        return [
            [
                'name' => '博客首页',
                'url' => '',
                'taxis' => 300,
                'pid' => 0,
                'newtab' => 'n',
                'type' => self::navitype_home,
                'type_id' => 0,
                'naviicon' => 'ri-home-smile-line',
            ],
            [
                'name' => '商城首页',
                'url' => '/',
                'taxis' => 200,
                'pid' => 0,
                'newtab' => 'n',
                'type' => self::navitype_custom,
                'type_id' => 0,
                'naviicon' => 'ri-store-2-line',
            ],
            [
                'name' => 'DCSHOP多财商城官方默认链接',
                'url' => 'https://dcshop.xzsc.cc/',
                'taxis' => 100,
                'pid' => 0,
                'newtab' => 'y',
                'type' => self::navitype_custom,
                'type_id' => 0,
                'naviicon' => 'ri-links-line',
            ],
        ];
    }

    private function isDefaultNaviRow($row) {
        $type = (int)($row['type'] ?? 0);
        $typeId = (int)($row['type_id'] ?? 0);
        $url = trim((string)($row['url'] ?? ''));
        $name = trim((string)($row['naviname'] ?? ''));
        if (($type === self::navitype_home || $type === self::navitype_blog) && $typeId === 0 && $url === '' && in_array($name, ['首页', '博客首页'], true)) {
            return true;
        }
        if ($type === self::navitype_custom && $typeId === 0 && $url === '/' && $name === '商城首页') {
            return true;
        }
        if ($type === self::navitype_custom && $typeId === 0 && rtrim($url, '/') === 'https://dcshop.xzsc.cc' && $name === 'DCSHOP多财商城官方默认链接') {
            return true;
        }
        return false;
    }

    private function shouldEnsureDefaultNavis() {
        $rows = [];
        $query = $this->db->query("SELECT * FROM {$this->table} ORDER BY id ASC");
        while ($row = $this->db->fetch_array($query)) {
            $rows[] = $row;
        }
        if (empty($rows)) {
            return true;
        }
        foreach ($rows as $row) {
            if (!$this->isDefaultNaviRow($row)) {
                return false;
            }
        }
        return true;
    }

    private function defaultNaviExists($type, $typeId, $url) {
        $type = (int)$type;
        $typeId = (int)$typeId;
        if ($type === self::navitype_custom) {
            $url = addslashes(trim((string)$url));
            $sql = "SELECT id FROM {$this->table} WHERE type=$type AND type_id=$typeId AND url='$url' LIMIT 1";
        } else {
            $sql = "SELECT id FROM {$this->table} WHERE type=$type AND type_id=$typeId LIMIT 1";
        }
        $res = $this->db->query($sql);
        return $this->db->num_rows($res) > 0;
    }

    private function ensureDefaultNavi($navi) {
        if ($this->defaultNaviExists($navi['type'], $navi['type_id'], $navi['url'])) {
            return;
        }
        $this->addNavi($navi['name'], $navi['url'], $navi['taxis'], $navi['pid'], $navi['newtab'], $navi['type'], $navi['type_id'], $navi['naviicon']);
    }

    /**
     * 初始化默认导航
     */
    function initDefaultNavis() {
        // 仅在全新导航或旧版默认导航场景下补齐，避免覆盖用户自定义导航。
        if (!$this->shouldEnsureDefaultNavis()) {
            return;
        }

        foreach ($this->getDefaultNavis() as $navi) {
            $this->ensureDefaultNavi($navi);
        }
    }
}
