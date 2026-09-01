<?php
/**
 * File Validator
 * 文件验证器组件
 * @package DCSHOP
 */

class FileValidator {

    // 允许的插件文件扩展名
    private static $allowed_extensions = ['zip'];
    
    // 允许的MIME类型
    private static $allowed_mime_types = [
        'application/zip',
        'application/x-zip-compressed',
        'multipart/x-zip'
    ];
    
    // 最大文件大小 (50MB)
    private static $max_file_size = 52428800;
    
    // 危险文件模式
    private static $dangerous_patterns = [
        '\.php$',
        '\.exe$',
        '\.bat$',
        '\.cmd$',
        '\.com$',
        '\.scr$',
        '\.pif$',
        '\.vbs$',
        '\.js$',
        '\.jar$'
    ];

    /**
     * 验证插件文件
     */
    public static function validatePluginFile($file_path) {
        $result = [
            'valid' => false,
            'errors' => [],
            'warnings' => [],
            'file_info' => []
        ];

        // 检查文件是否存在
        if (!file_exists($file_path)) {
            $result['errors'][] = '文件不存在';
            return $result;
        }

        // 获取文件信息
        $file_info = self::getFileInfo($file_path);
        $result['file_info'] = $file_info;

        // 验证文件扩展名
        if (!self::validateExtension($file_info['extension'])) {
            $result['errors'][] = '不支持的文件扩展名: ' . $file_info['extension'];
        }

        // 验证文件大小
        if (!self::validateFileSize($file_info['size'])) {
            $result['errors'][] = '文件大小超过限制 (' . self::formatFileSize(self::$max_file_size) . ')';
        }

        // 验证MIME类型
        if (!self::validateMimeType($file_path)) {
            $result['warnings'][] = 'MIME类型验证失败，可能不是有效的ZIP文件';
        }

        // 验证ZIP文件结构
        $zip_validation = self::validateZipStructure($file_path);
        if (!$zip_validation['valid']) {
            $result['errors'] = array_merge($result['errors'], $zip_validation['errors']);
        } else {
            $result['file_info']['zip_info'] = $zip_validation['info'];
        }

        // 安全扫描
        $security_scan = self::scanForMalware($file_path);
        if (!$security_scan['safe']) {
            $result['errors'] = array_merge($result['errors'], $security_scan['threats']);
        }
        if (!empty($security_scan['warnings'])) {
            $result['warnings'] = array_merge($result['warnings'], $security_scan['warnings']);
        }

        // 如果没有错误，则验证通过
        $result['valid'] = empty($result['errors']);

        return $result;
    }

    /**
     * 检查文件完整性
     */
    public static function checkFileIntegrity($file_path, $expected_hash = null) {
        if (!file_exists($file_path)) {
            return ['valid' => false, 'error' => '文件不存在'];
        }

        $current_hash = hash_file('sha256', $file_path);
        
        if ($expected_hash && $current_hash !== $expected_hash) {
            return [
                'valid' => false, 
                'error' => '文件哈希值不匹配',
                'expected' => $expected_hash,
                'actual' => $current_hash
            ];
        }

        return [
            'valid' => true,
            'hash' => $current_hash,
            'algorithm' => 'sha256'
        ];
    }

    /**
     * 基本安全扫描
     */
    public static function scanForMalware($file_path) {
        $result = [
            'safe' => true,
            'threats' => [],
            'warnings' => []
        ];

        if (!file_exists($file_path)) {
            $result['threats'][] = '文件不存在';
            $result['safe'] = false;
            return $result;
        }

        // 检查文件是否为ZIP格式
        if (!self::isValidZip($file_path)) {
            $result['threats'][] = '文件不是有效的ZIP格式';
            $result['safe'] = false;
            return $result;
        }

        // 扫描ZIP内容
        $zip = new ZipArchive();
        if ($zip->open($file_path) === TRUE) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $file_info = $zip->statIndex($i);
                $filename = $file_info['name'];
                
                // 检查危险文件名模式
                foreach (self::$dangerous_patterns as $pattern) {
                    if (preg_match('/' . $pattern . '/i', $filename)) {
                        $result['warnings'][] = "发现可疑文件: $filename";
                    }
                }
                
                // 检查路径遍历攻击
                if (strpos($filename, '../') !== false || strpos($filename, '..\\') !== false) {
                    $result['threats'][] = "检测到路径遍历攻击: $filename";
                    $result['safe'] = false;
                }
                
                // 检查文件名长度
                if (strlen($filename) > 255) {
                    $result['warnings'][] = "文件名过长: $filename";
                }
            }
            $zip->close();
        } else {
            $result['threats'][] = '无法打开ZIP文件进行安全扫描';
            $result['safe'] = false;
        }

        return $result;
    }

    /**
     * 提取插件元数据
     */
    public static function extractMetadata($file_path) {
        $metadata = [
            'plugin_id' => '',
            'name' => '',
            'version' => '',
            'description' => '',
            'author' => '',
            'type' => 'plugin'
        ];

        if (!self::isValidZip($file_path)) {
            return $metadata;
        }

        $zip = new ZipArchive();
        if ($zip->open($file_path) === TRUE) {
            // 查找主插件文件
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $file_info = $zip->statIndex($i);
                $filename = $file_info['name'];
                
                // 查找.php文件
                if (preg_match('/([^\/]+)\.php$/', $filename, $matches)) {
                    $plugin_name = $matches[1];
                    
                    // 读取文件内容
                    $content = $zip->getFromIndex($i);
                    if ($content) {
                        $extracted_meta = self::parsePluginHeader($content);
                        if (!empty($extracted_meta['Name'])) {
                            $metadata['plugin_id'] = $plugin_name;
                            $metadata['name'] = $extracted_meta['Name'];
                            $metadata['version'] = $extracted_meta['Version'] ?? '1.0.0';
                            $metadata['description'] = $extracted_meta['Description'] ?? '';
                            $metadata['author'] = $extracted_meta['Author'] ?? '';
                            break;
                        }
                    }
                }
            }
            $zip->close();
        }

        return $metadata;
    }

    /**
     * 获取文件信息
     */
    private static function getFileInfo($file_path) {
        $info = pathinfo($file_path);
        $size = filesize($file_path);
        
        return [
            'name' => $info['basename'],
            'extension' => strtolower($info['extension'] ?? ''),
            'size' => $size,
            'size_formatted' => self::formatFileSize($size),
            'modified_time' => filemtime($file_path),
            'mime_type' => mime_content_type($file_path)
        ];
    }

    /**
     * 验证文件扩展名
     */
    private static function validateExtension($extension) {
        return in_array(strtolower($extension), self::$allowed_extensions);
    }

    /**
     * 验证文件大小
     */
    private static function validateFileSize($size) {
        return $size <= self::$max_file_size;
    }

    /**
     * 验证MIME类型
     */
    private static function validateMimeType($file_path) {
        $mime_type = mime_content_type($file_path);
        return in_array($mime_type, self::$allowed_mime_types);
    }

    /**
     * 验证ZIP文件结构
     */
    private static function validateZipStructure($file_path) {
        $result = [
            'valid' => false,
            'errors' => [],
            'info' => []
        ];

        if (!self::isValidZip($file_path)) {
            $result['errors'][] = '不是有效的ZIP文件';
            return $result;
        }

        $zip = new ZipArchive();
        if ($zip->open($file_path) === TRUE) {
            $result['info'] = [
                'num_files' => $zip->numFiles,
                'comment' => $zip->comment
            ];
            
            // 检查是否包含必要的插件文件
            $has_php_file = false;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $file_info = $zip->statIndex($i);
                $filename = $file_info['name'];
                
                if (preg_match('/\.php$/', $filename)) {
                    $has_php_file = true;
                    break;
                }
            }
            
            if (!$has_php_file) {
                $result['errors'][] = 'ZIP文件中未找到PHP插件文件';
            } else {
                $result['valid'] = true;
            }
            
            $zip->close();
        } else {
            $result['errors'][] = '无法打开ZIP文件';
        }

        return $result;
    }

    /**
     * 检查是否为有效的ZIP文件
     */
    private static function isValidZip($file_path) {
        $zip = new ZipArchive();
        $result = $zip->open($file_path);
        if ($result === TRUE) {
            $zip->close();
            return true;
        }
        return false;
    }

    /**
     * 解析插件头部信息
     */
    private static function parsePluginHeader($content) {
        $metadata = [];
        
        // 提取插件头部注释中的信息
        preg_match('/Plugin Name:\s*(.+)/i', $content, $name_match);
        preg_match('/Version:\s*(.+)/i', $content, $version_match);
        preg_match('/Description:\s*(.+)/i', $content, $desc_match);
        preg_match('/Author:\s*(.+)/i', $content, $author_match);
        
        if (!empty($name_match[1])) {
            $metadata['Name'] = trim($name_match[1]);
        }
        if (!empty($version_match[1])) {
            $metadata['Version'] = trim($version_match[1]);
        }
        if (!empty($desc_match[1])) {
            $metadata['Description'] = trim($desc_match[1]);
        }
        if (!empty($author_match[1])) {
            $metadata['Author'] = trim($author_match[1]);
        }
        
        return $metadata;
    }

    /**
     * 格式化文件大小
     */
    private static function formatFileSize($bytes) {
        if ($bytes == 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));
        
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }

    /**
     * 获取最大允许文件大小
     */
    public static function getMaxFileSize() {
        return self::$max_file_size;
    }

    /**
     * 获取允许的文件扩展名
     */
    public static function getAllowedExtensions() {
        return self::$allowed_extensions;
    }

    /**
     * 获取允许的MIME类型
     */
    public static function getAllowedMimeTypes() {
        return self::$allowed_mime_types;
    }
}