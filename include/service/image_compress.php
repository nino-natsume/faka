<?php

/**
 * Service: ImageCompress
 * 图片压缩服务 - 处理图片压缩和优化
 */

class ImageCompress {

    /**
     * 压缩图片
     * @param string $sourcePath 源图片路径
     * @param string $targetPath 目标图片路径
     * @param int $quality 压缩质量 (1-100)
     * @param int $maxWidth 最大宽度
     * @param int $maxHeight 最大高度
     * @return bool
     */
    public static function compressImage($sourcePath, $targetPath = null, $quality = 80, $maxWidth = 1200, $maxHeight = 1200) {
        if (!file_exists($sourcePath)) {
            return false;
        }
        
        // 如果没有指定目标路径，覆盖原文件
        if ($targetPath === null) {
            $targetPath = $sourcePath;
        }
        
        // 获取图片信息
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }
        
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $type = $imageInfo[2];
        
        // 计算新尺寸
        $newSize = self::calculateNewSize($width, $height, $maxWidth, $maxHeight);
        $newWidth = $newSize['width'];
        $newHeight = $newSize['height'];
        
        // 如果尺寸没有变化且质量为100，直接复制文件
        if ($newWidth == $width && $newHeight == $height && $quality >= 100) {
            if ($sourcePath !== $targetPath) {
                return copy($sourcePath, $targetPath);
            }
            return true;
        }
        
        // 创建源图片资源
        $sourceImage = self::createImageFromFile($sourcePath, $type);
        if (!$sourceImage) {
            return false;
        }
        
        // 创建目标图片资源
        $targetImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // 处理透明背景
        self::handleTransparency($targetImage, $type);
        
        // 重新采样图片
        imagecopyresampled(
            $targetImage, $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight, $width, $height
        );
        
        // 保存图片
        $result = self::saveImage($targetImage, $targetPath, $type, $quality);
        
        // 释放内存
        imagedestroy($sourceImage);
        imagedestroy($targetImage);
        
        return $result;
    }
    
    /**
     * 批量压缩图片
     * @param array $files 文件路径数组
     * @param int $quality 压缩质量
     * @param int $maxWidth 最大宽度
     * @param int $maxHeight 最大高度
     * @return array 处理结果
     */
    public static function compressMultiple($files, $quality = 80, $maxWidth = 1200, $maxHeight = 1200) {
        $results = [];
        
        foreach ($files as $file) {
            $results[$file] = self::compressImage($file, null, $quality, $maxWidth, $maxHeight);
        }
        
        return $results;
    }
    
    /**
     * 计算新的图片尺寸
     * @param int $width 原宽度
     * @param int $height 原高度
     * @param int $maxWidth 最大宽度
     * @param int $maxHeight 最大高度
     * @return array
     */
    private static function calculateNewSize($width, $height, $maxWidth, $maxHeight) {
        // 如果图片尺寸小于最大限制，保持原尺寸
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return ['width' => $width, 'height' => $height];
        }
        
        // 计算缩放比例
        $widthRatio = $maxWidth / $width;
        $heightRatio = $maxHeight / $height;
        $ratio = min($widthRatio, $heightRatio);
        
        return [
            'width' => (int)($width * $ratio),
            'height' => (int)($height * $ratio)
        ];
    }
    
    /**
     * 从文件创建图片资源
     * @param string $filePath 文件路径
     * @param int $type 图片类型
     * @return resource|false
     */
    private static function createImageFromFile($filePath, $type) {
        switch ($type) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($filePath);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($filePath);
            case IMAGETYPE_GIF:
                return imagecreatefromgif($filePath);
            case IMAGETYPE_WEBP:
                if (function_exists('imagecreatefromwebp')) {
                    return imagecreatefromwebp($filePath);
                }
                break;
        }
        return false;
    }
    
    /**
     * 处理图片透明背景
     * @param resource $image 图片资源
     * @param int $type 图片类型
     */
    private static function handleTransparency($image, $type) {
        switch ($type) {
            case IMAGETYPE_PNG:
                imagealphablending($image, false);
                imagesavealpha($image, true);
                $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
                imagefill($image, 0, 0, $transparent);
                break;
            case IMAGETYPE_GIF:
                $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
                imagefill($image, 0, 0, $transparent);
                imagecolortransparent($image, $transparent);
                break;
        }
    }
    
    /**
     * 保存图片
     * @param resource $image 图片资源
     * @param string $filePath 保存路径
     * @param int $type 图片类型
     * @param int $quality 质量
     * @return bool
     */
    private static function saveImage($image, $filePath, $type, $quality) {
        // 确保目录存在
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        switch ($type) {
            case IMAGETYPE_JPEG:
                return imagejpeg($image, $filePath, $quality);
            case IMAGETYPE_PNG:
                // PNG质量范围是0-9，需要转换
                $pngQuality = (int)(9 - ($quality / 100) * 9);
                return imagepng($image, $filePath, $pngQuality);
            case IMAGETYPE_GIF:
                return imagegif($image, $filePath);
            case IMAGETYPE_WEBP:
                if (function_exists('imagewebp')) {
                    return imagewebp($image, $filePath, $quality);
                }
                break;
        }
        return false;
    }
    
    /**
     * 获取图片文件大小（字节）
     * @param string $filePath 文件路径
     * @return int|false
     */
    public static function getFileSize($filePath) {
        if (!file_exists($filePath)) {
            return false;
        }
        return filesize($filePath);
    }
    
    /**
     * 格式化文件大小显示
     * @param int $bytes 字节数
     * @return string
     */
    public static function formatFileSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
    
    /**
     * 检查GD库支持
     * @return array
     */
    public static function checkGDSupport() {
        $support = [
            'gd' => extension_loaded('gd'),
            'jpeg' => false,
            'png' => false,
            'gif' => false,
            'webp' => false
        ];
        
        if ($support['gd']) {
            $gdInfo = gd_info();
            $support['jpeg'] = $gdInfo['JPEG Support'] ?? false;
            $support['png'] = $gdInfo['PNG Support'] ?? false;
            $support['gif'] = $gdInfo['GIF Create Support'] ?? false;
            $support['webp'] = $gdInfo['WebP Support'] ?? false;
        }
        
        return $support;
    }
    
    /**
     * 压缩售后图片的便捷方法
     * @param string $filePath 文件路径
     * @return bool
     */
    public static function compressAftersaleImage($filePath) {
        // 售后图片压缩配置：质量75%，最大尺寸800x800
        return self::compressImage($filePath, null, 75, 800, 800);
    }
}