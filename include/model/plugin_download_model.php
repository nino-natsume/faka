<?php
/**
 * Plugin Download Model
 * 插件下载数据模型
 * @package DCSHOP
 */

class Plugin_Download_Model {

    private $db;
    private $table;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->table = DB_PREFIX . 'plugin_downloads';
    }

    /**
     * 创建下载记录
     */
    public function createDownload($plugin_id, $user_id, $plugin_name = '', $file_path = '') {
        $timestamp = time();
        $status = 'pending';
        $progress = 0;
        
        $sql = "INSERT INTO {$this->table} (plugin_id, user_id, plugin_name, file_path, status, progress, created_at) 
                VALUES ('$plugin_id', '$user_id', '$plugin_name', '$file_path', '$status', $progress, $timestamp)";
        
        $this->db->query($sql);
        return $this->db->insert_id();
    }

    /**
     * 更新下载状态
     */
    public function updateDownloadStatus($download_id, $status, $progress = null, $error_message = '') {
        $timestamp = time();
        $updates = ["status='$status'"];
        
        if ($progress !== null) {
            $updates[] = "progress=$progress";
        }
        
        if ($error_message) {
            $updates[] = "error_message='$error_message'";
        }
        
        if ($status === 'completed') {
            $updates[] = "completed_at=$timestamp";
        }
        
        $update_str = implode(', ', $updates);
        $sql = "UPDATE {$this->table} SET $update_str WHERE id=$download_id";
        
        $this->db->query($sql);
    }

    /**
     * 获取下载记录
     */
    public function getDownload($download_id) {
        $download_id = (int)$download_id;
        return $this->db->once_fetch_array("SELECT * FROM {$this->table} WHERE id=$download_id");
    }

    /**
     * 获取用户的下载记录
     */
    public function getUserDownloads($user_id, $limit = 50) {
        $user_id = (int)$user_id;
        $sql = "SELECT * FROM {$this->table} WHERE user_id=$user_id ORDER BY created_at DESC LIMIT $limit";
        
        $res = $this->db->query($sql);
        $downloads = [];
        while ($row = $this->db->fetch_array($res)) {
            $downloads[] = $row;
        }
        return $downloads;
    }

    /**
     * 删除下载记录
     */
    public function deleteDownload($download_id) {
        $download_id = (int)$download_id;
        $this->db->query("DELETE FROM {$this->table} WHERE id=$download_id");
    }

    /**
     * 验证下载数据
     */
    public function validateDownloadData($data) {
        $errors = [];
        
        if (empty($data['plugin_id'])) {
            $errors[] = '插件ID不能为空';
        }
        
        if (empty($data['user_id'])) {
            $errors[] = '用户ID不能为空';
        }
        
        if (!in_array($data['status'] ?? '', ['pending', 'processing', 'completed', 'failed'])) {
            $errors[] = '无效的下载状态';
        }
        
        $progress = $data['progress'] ?? 0;
        if (!is_numeric($progress) || $progress < 0 || $progress > 100) {
            $errors[] = '进度值必须在0-100之间';
        }
        
        return $errors;
    }
}