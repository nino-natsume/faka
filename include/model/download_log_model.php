<?php
/**
 * Download Log Model
 * 下载日志数据模型
 * @package DCSHOP
 */

class Download_Log_Model {

    private $db;
    private $table;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->table = DB_PREFIX . 'download_logs';
    }

    /**
     * 记录下载日志
     */
    public function logDownload($plugin_id, $user_id, $user_ip, $file_size = 0, $success = true, $user_agent = '') {
        $timestamp = time();
        $success_int = $success ? 1 : 0;
        
        $sql = "INSERT INTO {$this->table} (plugin_id, user_id, user_ip, download_time, file_size, success, user_agent) 
                VALUES ('$plugin_id', '$user_id', '$user_ip', $timestamp, $file_size, $success_int, '$user_agent')";
        
        $this->db->query($sql);
        return $this->db->insert_id();
    }

    /**
     * 获取下载日志
     */
    public function getDownloadLogs($plugin_id = '', $user_id = '', $limit = 100, $offset = 0) {
        $conditions = [];
        
        if ($plugin_id) {
            $conditions[] = "plugin_id='$plugin_id'";
        }
        
        if ($user_id) {
            $conditions[] = "user_id='$user_id'";
        }
        
        $where_clause = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);
        $sql = "SELECT * FROM {$this->table} $where_clause ORDER BY download_time DESC LIMIT $limit OFFSET $offset";
        
        $res = $this->db->query($sql);
        $logs = [];
        while ($row = $this->db->fetch_array($res)) {
            $row['download_time_formatted'] = date('Y-m-d H:i:s', $row['download_time']);
            $row['file_size_formatted'] = $this->formatFileSize($row['file_size']);
            $logs[] = $row;
        }
        return $logs;
    }

    /**
     * 获取下载统计
     */
    public function getDownloadStats($plugin_id = '', $days = 30) {
        $timestamp_start = time() - ($days * 24 * 3600);
        $where_clause = "WHERE download_time >= $timestamp_start";
        
        if ($plugin_id) {
            $where_clause .= " AND plugin_id='$plugin_id'";
        }
        
        $stats = [];
        
        // 总下载次数
        $total_result = $this->db->once_fetch_array("SELECT COUNT(*) as total FROM {$this->table} $where_clause");
        $stats['total_downloads'] = $total_result['total'];
        
        // 成功下载次数
        $success_result = $this->db->once_fetch_array("SELECT COUNT(*) as success FROM {$this->table} $where_clause AND success=1");
        $stats['successful_downloads'] = $success_result['success'];
        
        // 失败下载次数
        $stats['failed_downloads'] = $stats['total_downloads'] - $stats['successful_downloads'];
        
        // 成功率
        $stats['success_rate'] = $stats['total_downloads'] > 0 ? 
            round(($stats['successful_downloads'] / $stats['total_downloads']) * 100, 2) : 0;
        
        // 总下载流量
        $traffic_result = $this->db->once_fetch_array("SELECT SUM(file_size) as total_traffic FROM {$this->table} $where_clause AND success=1");
        $stats['total_traffic'] = $traffic_result['total_traffic'] ?: 0;
        $stats['total_traffic_formatted'] = $this->formatFileSize($stats['total_traffic']);
        
        return $stats;
    }

    /**
     * 清理旧日志
     */
    public function cleanOldLogs($days = 90) {
        $timestamp_cutoff = time() - ($days * 24 * 3600);
        $sql = "DELETE FROM {$this->table} WHERE download_time < $timestamp_cutoff";
        $this->db->query($sql);
    }

    /**
     * 验证日志数据
     */
    public function validateLogData($data) {
        $errors = [];
        
        if (empty($data['plugin_id'])) {
            $errors[] = '插件ID不能为空';
        }
        
        if (empty($data['user_id'])) {
            $errors[] = '用户ID不能为空';
        }
        
        if (empty($data['user_ip']) || !filter_var($data['user_ip'], FILTER_VALIDATE_IP)) {
            $errors[] = '无效的IP地址';
        }
        
        if (isset($data['file_size']) && (!is_numeric($data['file_size']) || $data['file_size'] < 0)) {
            $errors[] = '文件大小必须为非负数';
        }
        
        return $errors;
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