<?php
/*
Plugin Name: 模板设置
Version: 1.0.0
Plugin URL:
Description: 为模板增加丰富的设置功能，详见官网文档-模板开发。
Author: DuoCai
Author URL:
*/

defined('DC_ROOT') || exit('access denied!');

/**
 * 模板设置类
 */
class TplOptions
{

    //插件标识
    const ID = 'tpl_options';
    const NAME = '模板设置';
    const VERSION = '1.0.0';

    //数据表前缀
    private $_prefix = 'tpl_options_';

    //数据表
    private $_tables = array(
        'data',
    );


    //实例
    private static $_instance;

    //是否初始化
    private $_inited = false;

    //模板参数
    private $_templateOptions;

    private $_dataTableChecked = false;

    

    //数据库连接实例
    private $_db;

    //插件模板目录
    private $_view;

 

    //页面
    private $_pages;

    //文章
    private $_posts;

    /**
     * 单例入口
     * @return TplOptions
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 私有构造函数，保证单例
     */
    private function __construct() {}

    /**
     * 初始化函数
     * @return void
     */
    public function init()
    {
        if ($this->_inited === true) {
            return;
        }
        $this->_inited = true;

        //初始化各个数据表名
        $tables = array();
        foreach ($this->_tables as $name => $table) {
            $tables[$table] = $this->getTableName($table);
        }
        $this->_tables = $tables;

        
    }

    

    

    /**
     * 获取数据表
     * @param mixed $table 表名缩写，可选，若不设置则返回所有表，否则返回对应表
     * @return mixed 返回数组或字符串
     */
    public function getTable($table = null)
    {
        return $table === null ? $this->_tables : (isset($this->_tables[$table]) ? $this->_tables[$table] : '');
    }

    /**
     * 获取数据表名
     * @param string $table 表名缩写
     * @return string 表全名
     */
    private function getTableName($table)
    {
        return DB_PREFIX . $this->_prefix . $table;
    }

    /**
     * 获取模板参数数据，默认获取当前模板
     * @param mixed $template 模板名称，可选
     * @return array 模板参数
     */
    public function getTemplateOptions($template = null) {
        global $stationData;
//        d($stationData);die;


        if (!$this->isStationContext()) { // 主站配置
            if ($template === null) {
                $slug = isMobile() ? Option::get('nonce_templet_tel') : Option::get('nonce_templet');
                $template = 'front_' . $slug;
            }

            if (isset($this->_templateOptions[$template])) {
                return $this->_templateOptions[$template];
            }
            $_data = $this->queryAll('data', array(
                'template' => $template,
            ), '*', '`id` ASC');
            $templateOptions = array();



            foreach ($_data as $row) {
                extract($row);
                $data = unserialize($data);
                $templateOptions[$name] = $data;
            }

        }else{ // 子站配置
            $stationId = $this->getStationContextId();
            $template = $this->resolveStationContextTemplate($template);
//            d($res);die;
            $templateOptions = array();
            foreach ($this->getStationTemplateAliases($template) as $tplName) {
                $res = $this->queryAll('station_storage', [
                    'station_id' => $stationId,
                    'plugin_name' => $tplName,
                    'type' => 'tpl'
                ]);
                foreach($res as $val){
                    $data = unserialize($val['option_value']);
                    $templateOptions[$val['option_name']] = $data;
                }
            }

        }

//        d($templateOptions);die;

        return $templateOptions;
    }

    public function getStationTemplateOptions($station_id, $template, $name) {
        global $stationData;
//        d($stationData);die;

        $template = $this->resolveStationContextTemplate($template);

        $data = '';
        $station_id = (int)$station_id;
        if ($station_id <= 0 && !empty($stationData['id'])) {
            $station_id = (int)$stationData['id'];
        }
        foreach ($this->getStationTemplateAliases($template) as $tplName) {
            $res = $this->queryAll('station_storage', [
                'station_id' => $station_id,
                'plugin_name' => $tplName,
                'type' => 'tpl'
            ]);
            foreach($res as $val){
                if($val['option_name'] == $name){
                    $data = unserialize($val['option_value']);
                }
            }
        }
        return $data;

    }

    /**
     * 设置模板参数数据
     * @param string $template 模板名称
     * @param array $options 模板参数
     * @return boolean
     */
    public function setTemplateOptions($template, $options)
    {
        if ($options === array()) {
            return true;
        }
        $data = array();
        foreach ($options as $name => $option) {
            $data[] = array(
                'template' => $template,
                'name'     => $name,
                'depend'   => $option['depend'],
                'data'     => serialize($option['data']),
            );
        }
        return $this->insert('data', $data, true);
    }

    

    /**
     * 获取数据库连接
     */
    public function getDb()
    {
        if ($this->_db !== null) {
            return $this->_db;
        }
        $this->_db = Database::getInstance();
        return $this->_db;
    }

    /**
     * 从表中查询出所有数据
     * @param string $table 表名缩写
     * @param mixed $condition 字符串或数组条件
     * @return array 结果数据
     */
    private function queryAll($table, $condition = '', $select = '*', $orderBy = '')
    {
        $table = $this->getTable($table) ? $this->getTable($table) : DB_PREFIX . $table;
        $subSql = $this->buildQuerySql($condition);
        $sql = "SELECT $select FROM `$table`";
        if ($subSql) {
            $sql .= " WHERE $subSql";
        }
        if ($orderBy !== '') {
            $sql .= " ORDER BY $orderBy";
        }
        $query = $this->getDb()->query($sql);
        $data = array();
        while ($row = $this->getDb()->fetch_array($query)) {
            $data[] = $row;
        }
        return $data;
    }

    private function ensureDataTable()
    {
        if ($this->_dataTableChecked === true) {
            return true;
        }
        $this->_dataTableChecked = true;

        $table = $this->getTable('data');
        if ($table === '') {
            return false;
        }

        $db = $this->getDb();
        $createSql = "CREATE TABLE IF NOT EXISTS `$table` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `template` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
            `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
            `depend` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
            `data` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
            PRIMARY KEY (`id`) USING BTREE,
            UNIQUE KEY `template` (`template`,`name`) USING BTREE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        if ($db->query($createSql, true) === false) {
            return false;
        }

        $indexRows = $db->query("SHOW INDEX FROM `$table` WHERE Key_name = 'template'", true);
        if ($indexRows === false) {
            return false;
        }

        $keyExists = false;
        $isUnique = false;
        $columns = array();
        while ($row = $db->fetch_array($indexRows)) {
            $keyExists = true;
            if (isset($row['Non_unique']) && (int)$row['Non_unique'] === 0) {
                $isUnique = true;
            }
            if (isset($row['Seq_in_index'], $row['Column_name'])) {
                $columns[(int)$row['Seq_in_index']] = $row['Column_name'];
            }
        }
        ksort($columns);
        $columns = array_values($columns);
        $hasExpectedUnique = $keyExists && $isUnique && $columns === array('template', 'name');

        if (!$hasExpectedUnique) {
            $db->query("DELETE t1 FROM `$table` t1 INNER JOIN `$table` t2 ON t1.`template` = t2.`template` AND t1.`name` = t2.`name` AND t1.`id` < t2.`id`", true);
            if ($keyExists) {
                $db->query("ALTER TABLE `$table` DROP INDEX `template`", true);
            }
            if ($db->query("ALTER TABLE `$table` ADD UNIQUE KEY `template` (`template`,`name`) USING BTREE", true) === false) {
                return false;
            }
        }

        return true;
    }

    private function failSave($message)
    {
        if (class_exists('Output')) {
            Output::error($message);
        }

        header('Content-Type: application/json; charset=UTF-8');
        header('HTTP/1.1 400 Bad Request');
        die(json_encode(array(
            'code' => 1,
            'msg' => $message,
            'data' => ''
        ), JSON_UNESCAPED_UNICODE));
    }

    private function verifyDataRows($table, $data)
    {
        if (!is_array($data) || empty($data)) {
            return true;
        }

        $checks = array();
        if (array_key_exists(0, $data)) {
            foreach ($data as $row) {
                if (!isset($row['template'], $row['name'])) {
                    continue;
                }
                $checks[$row['template'] . "\n" . $row['name']] = array(
                    'template' => (string)$row['template'],
                    'name' => (string)$row['name'],
                );
            }
        } elseif (isset($data['template'], $data['name'])) {
            $checks[$data['template'] . "\n" . $data['name']] = array(
                'template' => (string)$data['template'],
                'name' => (string)$data['name'],
            );
        }

        if (empty($checks)) {
            return true;
        }

        foreach ($checks as $check) {
            $template = $this->getDb()->escape_string($check['template']);
            $name = $this->getDb()->escape_string($check['name']);
            $sql = "SELECT `id` FROM `$table` WHERE `template` = '$template' AND `name` = '$name' LIMIT 1";
            $query = $this->getDb()->query($sql, true);
            if ($query === false || !$this->getDb()->fetch_array($query)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 将数据插入数据表
     * @param string $table 表名缩写
     * @param array $data 数据
     * @return bool 结果数据
     */
    public function insert($table, $data, $replace = false)
    {

        if (!$this->isStationSettingRequest()) {
            $table = $this->getTable($table);
            if ($table === $this->getTable('data') && !$this->ensureDataTable()) {
                $this->failSave('模板配置保存失败：无法初始化模板配置数据表，请检查数据库权限或表结构');
            }
            $subSql = $this->buildInsertSql($data);
            if ($replace) {
                $sql = "REPLACE INTO `$table`";
            } else {
                $sql = "INSERT INTO `$table`";
            }
            $sql .= $subSql;
            $result = $this->getDb()->query($sql, true);
            if ($result === false && $table === $this->getTable('data')) {
                $this->_dataTableChecked = false;
                if ($this->ensureDataTable()) {
                    $result = $this->getDb()->query($sql, true);
                }
            }
            if ($result === false) {
                $error = method_exists($this->getDb(), 'getError') ? $this->getDb()->getError() : '';
                $this->failSave('模板配置保存失败：数据库写入失败' . ($error !== '' ? '，' . $error : ''));
            }
            if ($table === $this->getTable('data') && !$this->verifyDataRows($table, $data)) {
                $this->failSave('模板配置保存失败：写入后无法读取到配置记录，请检查数据库表前缀、表结构或账号权限');
            }
            return $result !== false;
        }else{
            $stationId = $this->getStationContextId();
            $db = Database::getInstance();
            foreach($data as $val){
                $pluginName = $this->normalizeStationTemplateName($val['template']);
                // 清理旧的 front_ 前缀残留行，避免读取时被覆盖
                $rawName = $pluginName; // 已经是 normalize 后的（无 front_ 前缀）
                $frontName = 'front_' . $rawName;
                if ($frontName !== $pluginName) {
                    $db->query("DELETE FROM " . DB_PREFIX . "station_storage WHERE station_id = " . (int)$stationId . " AND type = 'tpl' AND plugin_name = '" . $db->escape_string($frontName) . "' AND option_name = '" . $db->escape_string($val['name']) . "'");
                }
                $insert = [
                    'station_id' => $stationId,
                    'type' => 'tpl',
                    'plugin_name' => $pluginName,
                    'option_name' => $val['name'],
                    'option_value' => $val['data']
                ];
                $db->add('station_storage', $insert, true);
            }
            return true;

        }


    }

    /**
     * 根据条件构造WHERE子句
     * @param mixed $condition 字符串或数组条件
     * @return string 根据条件构造的查询子句
     */
    private function buildQuerySql($condition)
    {
        if (is_string($condition)) {
            return $condition;
        }
        $subSql = array();
        foreach ($condition as $key => $value) {
            $key = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$key);
            if ($key === '') {
                continue;
            }
            if (is_array($value)) {
                $subSql[] = "(`$key` IN (" . $this->implodeSqlArray($value) . '))';
                continue;
            }
            if ($value === null) {
                $subSql[] = "(`$key` IS NULL)";
                continue;
            }
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            } elseif (is_scalar($value)) {
                $value = (string)$value;
            } else {
                continue;
            }
            if (class_exists('mysqli', FALSE)) {
                $value = $this->getDb()->escape_string($value);
            }
            $subSql[] = "(`$key`='$value')";
        }
        return implode(' AND ', $subSql);
    }

    /**
     * 根据数据构造INSERT/REPLACE INTO子句
     * @param array $data 数据
     * @return string 根据数据构造的子句
     */
    private function buildInsertSql($data)
    {
        $subSql = array();
        if (array_key_exists(0, $data)) {
            $keys = array_keys($data[0]);
        } else {
            $keys = array_keys($data);
            $data = array(
                $data
            );
        }
        foreach ($data as $key => $value) {
            $subSql[] = '(' . $this->implodeSqlArray($value) . ')';
        }
        $subSql = implode(',', $subSql);
        $keys = '(`' . implode('`,`', $keys) . '`)';
        $subSql = "$keys VALUES $subSql";
        return $subSql;
    }

    /**
     * 将数组展开为可以供SQL使用的字符串
     * @param array $data 数据
     * @return string 形如('value1', 'value2')的字符串
     */
    private function implodeSqlArray($data)
    {
        return implode(',', array_map(function ($val) {
            if (class_exists('mysqli', FALSE)) {
                $val = $this->getDb()->escape_string($val);
            }
            return "'" . $val . "'";
        }, $data));
    }

    
    

    private function buildImageUrl($path)
    {
        if (empty($path)) {
            return '';
        }
        if (is_array($path)) {
            return array_map(array(
                $this,
                'buildImageUrl'
            ), $path);
        }
        return preg_match('{(https?|ftp)://}i', $path) ? $path : DC_URL . $path;
    }

    /**
     * 获取模板文件
     * @param string $view 模板名字
     * @param string $ext 模板后缀，默认为.php
     * @return string 模板文件全路径
     */
    public function view($view, $ext = '.php')
    {
        return $this->_view . $view . $ext;
    }

    /**
     * 根据参数构造url
     * @param array $params
     * @return string
     */
    public function url($params = array())
    {
        $baseUrl = './plugin.php?plugin=' . self::ID;
        $url = http_build_query($params);
        if ($url === '') {
            return $baseUrl;
        } else {
            return $baseUrl . '&' . $url;
        }
    }

    /**
     * 以json输出数据并结束
     * @param mixed $data
     * @return void
     */
    public function jsonReturn($data)
    {
        ob_clean();
        echo json_encode($data);
        exit;
    }

    /**
     * 从数组里取出数据，支持key.subKey的方式
     * @param array $array
     * @param string $key
     * @param mixed $default 默认值
     * @return mixed
     */
    public function arrayGet($array, $key, $default = null)
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }
        return $array;
    }

    /**
     * 魔术方法，用以获取模板设置
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
//        var_dump($name);die;
        if(!$this->isStationContext()){
            $object = new stdClass();
            $object->name = $name;
            $object->data = $this->arrayGet($this->getTemplateOptions(), $name);
            doAction('tpl_options_get', $object);
            return $object->data;
        }else{
            return $this->getStationTemplateOptions($this->getStationContextId(), '', $name);
        }

    }
    private function isStationContext()
    {
        global $stationData;
        return (!empty($stationData['id']) && (int)$stationData['id'] > 0) || $this->isStationSettingRequest();
    }

    private function isStationSettingRequest()
    {
        global $userData;
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        return substr($script, -18) === '/user/template.php' && !empty($userData['station']['id']);
    }

    private function getStationContextId()
    {
        global $stationData, $userData;
        if (!empty($stationData['id'])) {
            return (int)$stationData['id'];
        }
        if (!empty($userData['station']['id'])) {
            return (int)$userData['station']['id'];
        }
        return 0;
    }

    private function resolveStationContextTemplate($template = null)
    {
        global $stationData;
        $template = trim((string)$template);
        if ($template !== '') {
            return $template;
        }

        if ($this->isStationSettingRequest()) {
            $kind = isset($GLOBALS['__template_setting_kind']) ? trim((string)$GLOBALS['__template_setting_kind']) : 'front';
            $tpl = isset($GLOBALS['__template_setting_tpl']) ? trim((string)$GLOBALS['__template_setting_tpl']) : '';
            if ($tpl === '') {
                $tpl = isset($_REQUEST['tpl']) ? trim((string)$_REQUEST['tpl']) : '';
            }
            $tpl = preg_replace('/[^a-zA-Z0-9_-]/', '', $tpl);
            if ($tpl === '') {
                $tpl = 'default';
            }
            if ($kind === 'user_tpl') {
                return 'user_' . $tpl;
            }
            if ($kind === 'bottom_nav') {
                return 'bottom_nav_' . $tpl;
            }
            return 'front_' . $tpl;
        }

        return isMobile() ? ($stationData['tel_tpl'] ?? '') : ($stationData['pc_tpl'] ?? '');
    }

    private function normalizeStationTemplateName($template)
    {
        $template = (string)$template;
        if (strpos($template, 'front_') === 0) {
            $template = substr($template, 6);
        }
        return $template;
    }

    private function getStationTemplateAliases($template)
    {
        $template = (string)$template;
        $raw = $this->normalizeStationTemplateName($template);
        $front = 'front_' . $raw;
        return array_values(array_unique([$front, $raw]));
    }
}

function _g($name = null) {

    if ($name === null) {
        return TplOptions::getInstance()->getTemplateOptions();
    } else {
        if ($name === 'favicon') {
            $favicon = Option::get('admin_favicon');
            if (empty($favicon)) {
                return '';
            }
            if (preg_match('/^(https?:)?\/\//i', $favicon) || strpos($favicon, 'data:') === 0) {
                return $favicon;
            }
            if (strpos($favicon, '/') === 0) {
                return $favicon;
            }
            return DC_URL . ltrim($favicon, './');
        }

        return TplOptions::getInstance()->$name;
    }
}

function _em($name = null)
{
    if ($name === null) {
        return TplOptions::getInstance()->getTemplateOptions();
    } else {
        return TplOptions::getInstance()->$name;
    }
}

function userTplSettingKey($tpl = 'default')
{
    global $stationData;
    $tpl = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$tpl);
    $tpl = $tpl === '' ? 'default' : $tpl;
    $kind = isset($GLOBALS['__template_setting_kind']) ? trim((string)$GLOBALS['__template_setting_kind']) : '';
    if ((!empty($stationData['id']) && (int)$stationData['id'] > 0) || $kind === 'user_tpl') {
        return 'user_' . $tpl;
    }
    return $tpl;
}

function _getBlock($name = null, $type = 'content')
{
    $target = TplOptions::getInstance()->$name;
    $arr = [];
    if (!is_array($target))
        return $arr;
    if (empty($target[trim($type)]))
        return $arr;
    if (trim($type) != 'title' && trim($type) != 'content')
        return $arr;
    $result = array_filter($target, 'is_array');
    if (count($result) == count($target)) {
        foreach ($target[$type] as $val) {
            $arr[] = $val;
        }
    }
    return $arr;
}



