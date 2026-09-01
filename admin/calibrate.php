<?php
/**
 * 文件校准管理
 */

require_once 'globals.php';

if (ROLE !== 'admin') {
    emMsg('无权限访问');
}

$br = '<a href="./">数据中心</a><a><cite>文件校准</cite></a>';

// 获取授权信息
$authResult = Register::checkDomain();

// 获取备份记录
$backupDir = DC_ROOT . '/content/backup/calibrate/';
$backups = [];
if (is_dir($backupDir)) {
    $files = scandir($backupDir, SCANDIR_SORT_DESCENDING);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        if (pathinfo($file, PATHINFO_EXTENSION) !== 'zip') continue;
        
        $filePath = $backupDir . $file;
        $backups[] = [
            'name' => $file,
            'size' => filesize($filePath),
            'time' => filemtime($filePath),
        ];
    }
}

include View::getAdmView('header');
require_once(View::getAdmView('calibrate'));
include View::getAdmView('footer');
View::output();
