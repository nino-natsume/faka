<?php
/**
 * Local Plugin Model
 * 本地插件仓库数据模型
 * @package DCSHOP
 */

class Local_Plugin_Model {

    private $db;
    private $table;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->table = DB_PREFIX . 'local_plugins';
    }

    /**
     * 添加本地插件
     */
    public function addPlugin($plugin_data) {
        $validation_errors = $this->validatePluginData($plugin_data);
        if (!empty($validation_errors)) {
            return ['success' => false, 'errors' => $validation_errors];
        }

        $timestamp = time();
        $plugin_id = $plugin_data['plugin_id'];
        $name = $plugin_data['name'];
        $english_name = $plugin_data['english_name'] ?? '';
        $version = $plugin_data['version'] ?? '1.0.0';
        $type = $plugin_data['type'] ?? 'plugin';
        $price = $plugin_data['price'] ?? 0;
        $is_free = $plugin_data['is_free'] ?? 0;
        $file_path = $plugin_data['file_path'];
        $file_size = $plugin_data['file_size'] ?? 0;
        $file_hash = $plugin_data['file_hash'] ?? '';
        
        $sql = "INSERT INTO {$this->table} 
                (plugin_id, name, english_name, version, type, price, is_free, file_path, file_size, file_hash, upload_time, last_updated) 
                VALUES ('$plugin_id', '$name', '$english_name', '$version', '$type', $price, $is_free, '$file_path', $file_size, '$file_hash', $timestamp, $timestamp)";
        
        $this->db->query($sql);
        return ['success' => true, 'id' => $this->db->insert_id()];
    }

    /**
     * 更新插件信息
     */
    public function updatePlugin($plugin_id, $plugin_data) {
        $validation_errors = $this->validatePluginData($plugin_data, true);
        if (!empty($validation_errors)) {
            return ['success' => false, 'errors' => $validation_errors];
        }

        $timestamp = time();
        $updates = ["last_updated=$timestamp"];
        
        $allowed_fields = ['name', 'english_name', 'version', 'type', 'price', 'is_free', 'file_path', 'file_size', 'file_hash'];
        
        foreach ($plugin_data as $key => $value) {
            if (in_array($key, $allowed_fields)) {
                if (is_string($value)) {
                    $updates[] = "$key='$value'";
                } else {
                    $updates[] = "$key=$value";
                }
            }
        }
        
        $update_str = implode(', ', $updates);
        $sql = "UPDATE {$this->table} SET $update_str WHERE plugin_id='$plugin_id'";
        
        $this->db->query($sql);
        return ['success' => true];
    }

    /**
     * 删除插件
     */
    public function removePlugin($plugin_id) {
        $plugin = $this->getPlugin($plugin_id);
        if (!$plugin) {
            return ['success' => false, 'error' => '插件不存在'];
        }
        
        // 删除文件
        if (!empty($plugin['file_path']) && file_exists($plugin['file_path'])) {
            unlink($plugin['file_path']);
        }
        
        // 删除数据库记录
        $sql = "DELETE FROM {$this->table} WHERE plugin_id='$plugin_id'";
        $this->db->query($sql);
        
        return ['success' => true];
    }

    /**
     * 获取单个插件
     */
    public function getPlugin($plugin_id) {
        $sql = "SELECT * FROM {$this->table} WHERE plugin_id='$plugin_id'";
        $plugin = $this->db->once_fetch_array($sql);
        
        if ($plugin) {
            $plugin['upload_time_formatted'] = date('Y-m-d H:i:s', $plugin['upload_time']);
            $plugin['last_updated_formatted'] = date('Y-m-d H:i:s', $plugin['last_updated']);
            $plugin['file_size_formatted'] = $this->formatFileSize($plugin['file_size']);
            $plugin['file_exists'] = file_exists($plugin['file_path']);
        }
        
        return $plugin;
    }

    /**
     * 获取所有可用插件
     */
    public function listAvailablePlugins($type = '', $limit = 100, $offset = 0) {
        $where_clause = '';
        if ($type) {
            $where_clause = "WHERE type='$type'";
        }
        
        $sql = "SELECT * FROM {$this->table} $where_clause ORDER BY last_updated DESC LIMIT $limit OFFSET $offset";
        
        $res = $this->db->query($sql);
        $plugins = [];
        while ($row = $this->db->fetch_array($res)) {
            $row['upload_time_formatted'] = date('Y-m-d H:i:s', $row['upload_time']);
            $row['last_updated_formatted'] = date('Y-m-d H:i:s', $row['last_updated']);
            $row['file_size_formatted'] = $this->formatFileSize($row['file_size']);
            $row['file_exists'] = file_exists($row['file_path']);
            $plugins[] = $row;
        }
        return $plugins;
    }

    /**
     * 检查插件是否存在
     */
    public function pluginExists($plugin_id) {
        $result = $this->db->once_fetch_array("SELECT COUNT(*) as count FROM {$this->table} WHERE plugin_id='$plugin_id'");
        return $result['count'] > 0;
    }

    /**
     * 获取插件统计
     */
    public function getPluginStats() {
        $stats = [];
        
        // 总插件数
        $total_result = $this->db->once_fetch_array("SELECT COUNT(*) as total FROM {$this->table}");
        $stats['total_plugins'] = $total_result['total'];
        
        // 按类型统计
        $type_result = $this->db->query("SELECT type, COUNT(*) as count FROM {$this->table} GROUP BY type");
        $stats['by_type'] = [];
        while ($row = $this->db->fetch_array($type_result)) {
            $stats['by_type'][$row['type']] = $row['count'];
        }
        
        // 总文件大小
        $size_result = $this->db->once_fetch_array("SELECT SUM(file_size) as total_size FROM {$this->table}");
        $stats['total_size'] = $size_result['total_size'] ?: 0;
        $stats['total_size_formatted'] = $this->formatFileSize($stats['total_size']);
        
        return $stats;
    }

    /**
     * 验证插件数据
     */
    public function validatePluginData($data, $is_update = false) {
        $errors = [];
        
        if (!$is_update && empty($data['plugin_id'])) {
            $errors[] = '插件ID不能为空';
        }
        
        if (!$is_update && empty($data['name'])) {
            $errors[] = '插件名称不能为空';
        }
        
        if (!$is_update && empty($data['file_path'])) {
            $errors[] = '文件路径不能为空';
        }
        
        if (isset($data['type']) && !in_array($data['type'], ['plugin', 'template'])) {
            $errors[] = '插件类型必须是plugin或template';
        }
        
        if (isset($data['price']) && (!is_numeric($data['price']) || $data['price'] < 0)) {
            $errors[] = '价格必须为非负数';
        }
        
        if (isset($data['is_free']) && !in_array($data['is_free'], [0, 1])) {
            $errors[] = '是否免费必须为0或1';
        }
        
        if (isset($data['file_size']) && (!is_numeric($data['file_size']) || $data['file_size'] < 0)) {
            $errors[] = '文件大小必须为非负数';
        }
        
        // 检查文件是否存在
        if (isset($data['file_path']) && !empty($data['file_path']) && !file_exists($data['file_path'])) {
            $errors[] = '指定的文件不存在';
        }
        
        return $errors;
    }

    /**
     * 计算文件哈希值
     */
    public function calculateFileHash($file_path) {
        if (!file_exists($file_path)) {
            return false;
        }
        return hash_file('sha256', $file_path);
    }

    /**
     * 验证文件完整性
     */
    public function verifyFileIntegrity($plugin_id) {
        $plugin = $this->getPlugin($plugin_id);
        if (!$plugin) {
            return ['valid' => false, 'error' => '插件不存在'];
        }
        
        if (!file_exists($plugin['file_path'])) {
            return ['valid' => false, 'error' => '文件不存在'];
        }
        
        $current_hash = $this->calculateFileHash($plugin['file_path']);
        if ($current_hash !== $plugin['file_hash']) {
            return ['valid' => false, 'error' => '文件哈希值不匹配，文件可能已损坏'];
        }
        
        return ['valid' => true];
    }

    /**
     * 格式化文件大小
     */
    private function formatFileSize($bytes) {
        if ($bytes == 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));
        
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }
}