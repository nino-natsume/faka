<?php
/**
 * 资源管理 - 管理上传的文件
 */
require_once 'globals.php';

$br = '<a href="./">数据中心</a><a href="./setting.php">系统管理</a><a><cite>资源管理</cite></a>';

$DB = Database::getInstance();
$baseDir = DC_ROOT;
$uploadDir = $baseDir . '/content/uploadfile';

// 获取所有上传目录（按月份）
$directories = [];
if (is_dir($uploadDir)) {
    $items = scandir($uploadDir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $uploadDir . '/' . $item;
        if (is_dir($path)) {
            if (preg_match('/^\d{6}$/', $item)) {
                $year = substr($item, 0, 4);
                $month = substr($item, 4, 2);
                $name = $year . '年' . $month . '月';
            } else {
                $name = $item;
            }
            $directories[$item] = ['name' => $name, 'path' => $path];
        }
    }
}
krsort($directories);

// 统计信息
$stats = [];
$totalSize = 0;
$totalFiles = 0;
foreach ($directories as $key => $dir) {
    $size = 0;
    $count = 0;
    if (is_dir($dir['path'])) {
        $files = glob($dir['path'] . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                $size += filesize($file);
                $count++;
            }
        }
    }
    $stats[$key] = ['size' => $size, 'count' => $count];
    $totalSize += $size;
    $totalFiles += $count;
}

// 获取数据库中引用的文件
$usedFiles = [];

function normalizeFilePath($path) {
    if (empty($path)) return '';
    $path = ltrim($path, '/');
    while (strpos($path, '../') === 0) {
        $path = substr($path, 3);
    }
    return $path;
}

function addUsedFile(&$usedFiles, $path) {
    if (!empty($path) && strpos($path, 'http') !== 0) {
        $normalized = normalizeFilePath($path);
        if (!empty($normalized) && strpos($normalized, 'content/uploadfile/') !== false) {
            $usedFiles[] = $normalized;
        }
    }
}

function extractImagesFromContent(&$usedFiles, $content) {
    if (empty($content)) return;
    if (preg_match_all('/content\/uploadfile\/([^\s"\'<>\)\]]+)/', $content, $matches)) {
        foreach ($matches[0] as $match) {
            addUsedFile($usedFiles, $match);
        }
    }
}

// 1. 商品封面图
$rows = $DB->query("SELECT cover FROM " . DB_PREFIX . "goods WHERE cover != '' AND cover IS NOT NULL");
foreach ($rows as $row) { addUsedFile($usedFiles, $row['cover']); }

// 2. 商品详情内容
$rows = $DB->query("SELECT content FROM " . DB_PREFIX . "goods WHERE content LIKE '%/content/uploadfile/%'");
foreach ($rows as $row) { extractImagesFromContent($usedFiles, $row['content']); }

// 3. 商品支付后内容
$rows = $DB->query("SELECT pay_content FROM " . DB_PREFIX . "goods WHERE pay_content LIKE '%/content/uploadfile/%'");
foreach ($rows as $row) { extractImagesFromContent($usedFiles, $row['pay_content']); }

// 4. 商品简介
$rows = $DB->query("SELECT des FROM " . DB_PREFIX . "goods WHERE des LIKE '%/content/uploadfile/%'");
foreach ($rows as $row) { extractImagesFromContent($usedFiles, $row['des']); }

// 5. 文章封面图
$rows = $DB->query("SELECT cover FROM " . DB_PREFIX . "blog WHERE cover != '' AND cover IS NOT NULL");
foreach ($rows as $row) { addUsedFile($usedFiles, $row['cover']); }

// 6. 文章内容
$rows = $DB->query("SELECT content FROM " . DB_PREFIX . "blog WHERE content LIKE '%/content/uploadfile/%'");
foreach ($rows as $row) { extractImagesFromContent($usedFiles, $row['content']); }

// 7. 文章摘要
$rows = $DB->query("SELECT excerpt FROM " . DB_PREFIX . "blog WHERE excerpt LIKE '%/content/uploadfile/%'");
foreach ($rows as $row) { extractImagesFromContent($usedFiles, $row['excerpt']); }

// 8. 友情链接图标
$rows = $DB->query("SELECT icon FROM " . DB_PREFIX . "link WHERE icon != '' AND icon IS NOT NULL");
foreach ($rows as $row) { addUsedFile($usedFiles, $row['icon']); }

// 9. 分类图像
$rows = $DB->query("SELECT sortimg FROM " . DB_PREFIX . "sort WHERE sortimg != '' AND sortimg IS NOT NULL");
foreach ($rows as $row) { addUsedFile($usedFiles, $row['sortimg']); }

$userReceiptColumnExists = false;
$columnRows = $DB->query("SHOW COLUMNS FROM " . DB_PREFIX . "user LIKE 'withdraw_receipt_image'");
foreach ($columnRows as $row) {
    $userReceiptColumnExists = true;
    break;
}
if ($userReceiptColumnExists) {
    $rows = $DB->query("SELECT withdraw_receipt_image FROM " . DB_PREFIX . "user WHERE withdraw_receipt_image != '' AND withdraw_receipt_image IS NOT NULL");
    foreach ($rows as $row) { addUsedFile($usedFiles, $row['withdraw_receipt_image']); }
}

// 10. 微语图片
$rows = $DB->query("SELECT img FROM " . DB_PREFIX . "twitter WHERE img != '' AND img IS NOT NULL");
foreach ($rows as $row) { addUsedFile($usedFiles, $row['img']); }

// 11. 用户头像
$rows = $DB->query("SELECT photo FROM " . DB_PREFIX . "user WHERE photo != '' AND photo IS NOT NULL");
foreach ($rows as $row) { addUsedFile($usedFiles, $row['photo']); }

// 12. 订单二维码
$rows = $DB->query("SELECT qr_code FROM " . DB_PREFIX . "order WHERE qr_code != '' AND qr_code IS NOT NULL");
foreach ($rows as $row) { addUsedFile($usedFiles, $row['qr_code']); }

// 13. 售后图片
$rows = $DB->query("SELECT images FROM " . DB_PREFIX . "aftersale WHERE images != '' AND images IS NOT NULL");
foreach ($rows as $row) {
    if (!empty($row['images'])) {
        $images = json_decode($row['images'], true);
        if (is_array($images)) {
            foreach ($images as $img) { addUsedFile($usedFiles, $img); }
        }
    }
}

// 14. 售后聊天内容
$rows = $DB->query("SELECT content FROM " . DB_PREFIX . "aftersale_chat WHERE content LIKE '%content/uploadfile/%'");
foreach ($rows as $row) { extractImagesFromContent($usedFiles, $row['content']); }

// 15. 发货记录
$rows = $DB->query("SELECT content FROM " . DB_PREFIX . "deliver WHERE content LIKE '%/content/uploadfile/%'");
foreach ($rows as $row) { extractImagesFromContent($usedFiles, $row['content']); }

// 16. 站点配置
$options = $CACHE->readCache('options');
$configImages = ['site_logo', 'site_favicon', 'login_bg', 'shop_logo', 'shop_qrcode', 'wechat_qrcode', 'alipay_qrcode', 'pay_qrcode', 'wx_qrcode', 'logo', 'personal_center_icon', 'admin_favicon'];
foreach ($configImages as $key) {
    if (!empty($options[$key])) { addUsedFile($usedFiles, $options[$key]); }
}

// 17. 模板配置
$rows = $DB->query("SELECT data FROM " . DB_PREFIX . "tpl_options_data WHERE data LIKE '%content/uploadfile/%'");
foreach ($rows as $row) {
    if (!empty($row['data'])) {
        $data = @unserialize($row['data']);
        if (is_array($data)) {
            array_walk_recursive($data, function($val) use (&$usedFiles) {
                if (is_string($val) && strpos($val, 'content/uploadfile/') !== false) {
                    addUsedFile($usedFiles, $val);
                }
            });
        } elseif (is_string($data) && strpos($data, 'content/uploadfile/') !== false) {
            // 处理单个字符串值（如图片路径）
            addUsedFile($usedFiles, $data);
        }
    }
}

// 18. 插件存储
$rows = $DB->query("SELECT value FROM " . DB_PREFIX . "storage WHERE value LIKE '%content/uploadfile/%'");
foreach ($rows as $row) {
    if (!empty($row['value'])) {
        $data = @unserialize($row['value']);
        if (is_array($data)) {
            array_walk_recursive($data, function($val) use (&$usedFiles) {
                if (is_string($val) && strpos($val, 'content/uploadfile/') !== false) {
                    addUsedFile($usedFiles, $val);
                }
            });
        } elseif (is_string($row['value']) && strpos($row['value'], 'content/uploadfile/') !== false) {
            addUsedFile($usedFiles, $row['value']);
        }
    }
}

// 19. 提现收款码
$rows = $DB->query("SELECT receipt_image FROM " . DB_PREFIX . "withdraw WHERE receipt_image != '' AND receipt_image IS NOT NULL");
foreach ($rows as $row) { addUsedFile($usedFiles, $row['receipt_image']); }

// 20. 会员等级图片图标
$memberIconColumnExists = false;
$columnRows = $DB->query("SHOW COLUMNS FROM " . DB_PREFIX . "member LIKE 'icon_image'");
foreach ($columnRows as $row) {
    $memberIconColumnExists = true;
    break;
}
if ($memberIconColumnExists) {
    $rows = $DB->query("SELECT icon_image FROM " . DB_PREFIX . "member WHERE icon_image != '' AND icon_image IS NOT NULL");
    foreach ($rows as $row) { addUsedFile($usedFiles, $row['icon_image']); }
}

$usedFiles = array_unique($usedFiles);

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

$currentDir = $_GET['dir'] ?? 'all';
if ($currentDir !== 'all' && !isset($directories[$currentDir])) $currentDir = 'all';

$files = [];
if ($currentDir === 'all') {
    // 加载所有目录的文件
    foreach ($directories as $dirKey => $dirInfo) {
        if (is_dir($dirInfo['path'])) {
            $items = scandir($dirInfo['path']);
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                $filePath = $dirInfo['path'] . '/' . $item;
                if (is_file($filePath)) {
                    $relativePath = 'content/uploadfile/' . $dirKey . '/' . $item;
                    $files[] = [
                        'name' => $item,
                        'path' => $relativePath,
                        'size' => filesize($filePath),
                        'time' => filemtime($filePath),
                        'used' => in_array($relativePath, $usedFiles),
                        'ext' => strtolower(pathinfo($item, PATHINFO_EXTENSION)),
                        'dir' => $dirKey,
                    ];
                }
            }
        }
    }
} elseif (isset($directories[$currentDir])) {
    $dirPath = $directories[$currentDir]['path'];
    if (is_dir($dirPath)) {
        $items = scandir($dirPath);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $filePath = $dirPath . '/' . $item;
            if (is_file($filePath)) {
                $relativePath = 'content/uploadfile/' . $currentDir . '/' . $item;
                $files[] = [
                    'name' => $item,
                    'path' => $relativePath,
                    'size' => filesize($filePath),
                    'time' => filemtime($filePath),
                    'used' => in_array($relativePath, $usedFiles),
                    'ext' => strtolower(pathinfo($item, PATHINFO_EXTENSION)),
                    'dir' => $currentDir,
                ];
            }
        }
    }
}
usort($files, function($a, $b) { return $b['time'] - $a['time']; });

$unusedCount = 0;
$unusedSize = 0;
foreach ($files as $f) {
    if (!$f['used']) {
        $unusedCount++;
        $unusedSize += $f['size'];
    }
}

$imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'ico', 'svg'];

include View::getAdmView('header');
?>

<style>
/* ========== 统计卡片 ========== */
.res-stats { display: flex; gap: 16px; margin-bottom: 20px; flex-wrap: wrap; }
.res-stat-card { flex: 1; min-width: 150px; padding: 20px; border-radius: 8px; background: #fff; border-left: 4px solid; }
.res-stat-card:nth-child(1) { border-color: #667eea; }
.res-stat-card:nth-child(2) { border-color: #16b777; }
.res-stat-card:nth-child(3) { border-color: #ff9800; }
.res-stat-card:nth-child(4) { border-color: #f56c6c; }
.res-stat-num { font-size: 26px; font-weight: 600; margin-bottom: 4px; }
.res-stat-card:nth-child(1) .res-stat-num { color: #667eea; }
.res-stat-card:nth-child(2) .res-stat-num { color: #16b777; }
.res-stat-card:nth-child(3) .res-stat-num { color: #ff9800; }
.res-stat-card:nth-child(4) .res-stat-num { color: #f56c6c; }
.res-stat-label { font-size: 13px; color: #999; }

/* ========== 提示信息 ========== */
.res-info { background: #fff; border: 1px solid #e8e8e8; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; font-size: 13px; color: #666; display: flex; align-items: center; gap: 8px; }
.res-info i { color: #409eff; font-size: 16px; }

/* ========== 目录标签 ========== */
.res-tabs { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
.res-tab { display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; background: #fff; border: 1px solid #e8e8e8; border-radius: 8px; color: #333; text-decoration: none; font-size: 14px; transition: all 0.2s; cursor: pointer; }
.res-tab:hover { border-color: #16b777; color: #16b777; }
.res-tab.active { background: #16b777; border-color: #16b777; color: #fff; }
.res-tab-count { background: rgba(0,0,0,0.08); padding: 2px 8px; border-radius: 10px; font-size: 12px; }
.res-tab.active .res-tab-count { background: rgba(255,255,255,0.25); }
.res-tab-del { margin-left: 4px; color: #f56c6c; opacity: 0.7; font-size: 14px; }
.res-tab-del:hover { opacity: 1; }
.res-tab.active .res-tab-del { color: #fff; }

/* ========== 内容卡片 ========== */
.res-card { background: #fff; border-radius: 8px; overflow: hidden; }
.res-card-header { padding: 16px 20px; border-bottom: 1px solid #e8e8e8; display: flex; justify-content: space-between; align-items: center; }
.res-card-title { font-size: 15px; font-weight: 500; color: #333; }
.res-card-extra { font-size: 13px; color: #999; }
.res-card-body { padding: 20px; }

/* ========== 工具栏 ========== */
.res-toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px; }
.res-warning { display: flex; align-items: center; gap: 8px; padding: 10px 16px; background: #fff7e6; border: 1px solid #ffd591; border-radius: 6px; color: #d46b08; font-size: 13px; }
.res-warning i { font-size: 16px; }

/* ========== 表格 ========== */
.res-table { width: 100%; border-collapse: collapse; }
.res-table th { padding: 12px 10px; text-align: left; font-weight: 500; font-size: 13px; color: #666; background: #fafafa; border-bottom: 1px solid #e8e8e8; }
.res-table td { padding: 12px 10px; font-size: 13px; color: #333; border-bottom: 1px solid #e8e8e8; vertical-align: middle; }
.res-table tbody tr:hover { background: #fafafa; }
.res-table tbody tr:last-child td { border-bottom: none; }

/* ========== 文件预览 ========== */
.res-thumb { width: 44px; height: 44px; object-fit: cover; border-radius: 6px; cursor: pointer; background: #f5f5f5; }
.res-file-icon { width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; background: #f5f5f5; border-radius: 6px; color: #999; font-size: 20px; }
.res-filename { max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* ========== 状态标签 ========== */
.res-status { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 4px; font-size: 12px; }
.res-status-used { background: #e8f5e9; color: #2e7d32; }
.res-status-unused { background: #ffebee; color: #c62828; }

/* ========== 操作按钮 ========== */
.res-actions { display: flex; gap: 6px; }
.res-btn { padding: 5px 12px; border: none; border-radius: 4px; font-size: 12px; cursor: pointer; transition: all 0.2s; }
.res-btn:hover { opacity: 0.85; }
.res-btn-primary { background: #409eff; color: #fff; }
.res-btn-success { background: #67c23a; color: #fff; }
.res-btn-danger { background: #f56c6c; color: #fff; }
.res-btn-default { background: #f5f5f5; color: #333; border: 1px solid #ddd; }

/* ========== 空状态 ========== */
.res-empty { text-align: center; padding: 60px 20px; color: #999; }
.res-empty i { font-size: 48px; margin-bottom: 12px; display: block; opacity: 0.5; }
.res-empty p { margin: 0 0 16px 0; }

/* ========================================
   深色模式适配 - 使用 .dark-mode 选择器
   ======================================== */
html.dark-mode .res-stat-card,
.dark-mode .res-stat-card { background: #16213e !important; }
html.dark-mode .res-stat-label,
.dark-mode .res-stat-label { color: #a0aec0 !important; }

html.dark-mode .res-info,
.dark-mode .res-info { background: #16213e !important; border-color: #4a5568 !important; color: #a0aec0 !important; }
html.dark-mode .res-info i,
.dark-mode .res-info i { color: #16baaa !important; }

html.dark-mode .res-tab,
.dark-mode .res-tab { background: #16213e !important; border-color: #4a5568 !important; color: #e2e8f0 !important; }
html.dark-mode .res-tab:hover,
.dark-mode .res-tab:hover { border-color: #16baaa !important; color: #16baaa !important; }
html.dark-mode .res-tab.active,
.dark-mode .res-tab.active { background: #16baaa !important; border-color: #16baaa !important; color: #fff !important; }
html.dark-mode .res-tab-count,
.dark-mode .res-tab-count { background: rgba(255,255,255,0.1) !important; }

html.dark-mode .res-card,
.dark-mode .res-card { background: #16213e !important; }
html.dark-mode .res-card-header,
.dark-mode .res-card-header { border-color: #4a5568 !important; }
html.dark-mode .res-card-title,
.dark-mode .res-card-title { color: #e2e8f0 !important; }
html.dark-mode .res-card-extra,
.dark-mode .res-card-extra { color: #a0aec0 !important; }

html.dark-mode .res-warning,
.dark-mode .res-warning { background: #2b2111 !important; border-color: #594214 !important; color: #ffc53d !important; }

html.dark-mode .res-table th,
.dark-mode .res-table th { background: #1a1a2e !important; color: #a0aec0 !important; border-color: #4a5568 !important; }
html.dark-mode .res-table td,
.dark-mode .res-table td { color: #e2e8f0 !important; border-color: #4a5568 !important; }
html.dark-mode .res-table tbody tr:hover,
.dark-mode .res-table tbody tr:hover { background: #2d3748 !important; }

html.dark-mode .res-thumb,
.dark-mode .res-thumb { background: #2d3748 !important; }
html.dark-mode .res-file-icon,
.dark-mode .res-file-icon { background: #2d3748 !important; color: #a0aec0 !important; }
html.dark-mode .res-filename,
.dark-mode .res-filename { color: #e2e8f0 !important; }

html.dark-mode .res-status-used,
.dark-mode .res-status-used { background: rgba(22, 183, 119, 0.15) !important; color: #16b777 !important; }
html.dark-mode .res-status-unused,
.dark-mode .res-status-unused { background: rgba(245, 108, 108, 0.15) !important; color: #f56c6c !important; }

html.dark-mode .res-btn-primary,
.dark-mode .res-btn-primary { background: #337ecc !important; }
html.dark-mode .res-btn-success,
.dark-mode .res-btn-success { background: #529b2e !important; }
html.dark-mode .res-btn-danger,
.dark-mode .res-btn-danger { background: #c45656 !important; }
html.dark-mode .res-btn-default,
.dark-mode .res-btn-default { background: #2d3748 !important; border-color: #4a5568 !important; color: #e2e8f0 !important; }

html.dark-mode .res-empty,
.dark-mode .res-empty { color: #a0aec0 !important; }
</style>

<div class="layui-card" style="border-radius:8px;overflow:hidden;">
    <div class="layui-card-header" style="display:flex;align-items:center;justify-content:center;position:relative;height:auto;padding:12px 15px;">
        <span style="position:absolute;left:15px;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">资源管理</span>
    </div>
    <div class="layui-card-body" style="padding:20px;">

    <!-- 统计卡片 -->
    <div class="res-stats">
        <div class="res-stat-card">
            <div class="res-stat-num"><?= $totalFiles ?></div>
            <div class="res-stat-label">文件总数</div>
        </div>
        <div class="res-stat-card">
            <div class="res-stat-num"><?= formatFileSize($totalSize) ?></div>
            <div class="res-stat-label">占用空间</div>
        </div>
        <div class="res-stat-card">
            <div class="res-stat-num"><?= count($usedFiles) ?></div>
            <div class="res-stat-label">已引用</div>
        </div>
        <div class="res-stat-card">
            <div class="res-stat-num"><?= $unusedCount ?></div>
            <div class="res-stat-label"><?= $currentDir === 'all' ? '未引用' : '当前目录未引用' ?></div>
        </div>
    </div>

    <?php if (!empty($directories)): ?>
    <!-- 检测说明 -->
    <div class="res-info">
        <i class="ri-information-line"></i>
        <span>引用检测范围：商品（封面/详情/支付后内容）、文章（封面/内容）、友情链接、分类图像、微语、用户头像、用户默认提现收款码、订单二维码、售后图片/聊天、发货记录、站点配置、模板配置、插件存储、提现收款码</span>
    </div>

    <!-- 目录标签 -->
    <div class="res-tabs">
        <a href="?dir=all" class="res-tab <?= $currentDir === 'all' ? 'active' : '' ?>">
            <i class="ri-folder-line"></i>
            <span>全部</span>
            <span class="res-tab-count"><?= $totalFiles ?></span>
        </a>
        <?php foreach ($directories as $key => $dir): ?>
        <a href="?dir=<?= $key ?>" class="res-tab <?= (string)$currentDir === (string)$key ? 'active' : '' ?>">
            <i class="ri-folder-3-line"></i>
            <span><?= $dir['name'] ?></span>
            <span class="res-tab-count"><?= $stats[$key]['count'] ?></span>
            <?php if ($stats[$key]['count'] == 0 && preg_match('/^\d{6}$/', $key)): ?>
            <i class="ri-close-line res-tab-del" data-dir="<?= $key ?>" title="删除空目录"></i>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- 文件列表 -->
    <div class="res-card">
        <div class="res-card-header">
            <span class="res-card-title"><?= $currentDir === 'all' ? '全部文件' : ($currentDir ? $directories[$currentDir]['name'] : '资源管理') ?></span>
            <span class="res-card-extra">共 <?= count($files) ?> 个文件<?php if ($currentDir === 'all'): ?>，占用 <?= formatFileSize($totalSize) ?><?php elseif ($currentDir): ?>，占用 <?= formatFileSize($stats[$currentDir]['size'] ?? 0) ?><?php endif; ?></span>
        </div>
        <div class="res-card-body">
            <?php if (empty($directories)): ?>
            <div class="res-empty">
                <i class="ri-folder-open-line"></i>
                <p>暂无上传文件</p>
            </div>
            <?php elseif (empty($files)): ?>
            <div class="res-empty">
                <i class="ri-folder-open-line"></i>
                <p>该目录暂无文件</p>
                <?php if ($currentDir && preg_match('/^\d{6}$/', $currentDir)): ?>
                <button class="res-btn res-btn-default" onclick="deleteEmptyDir('<?= $currentDir ?>')">
                    <i class="ri-delete-bin-line"></i> 删除此空目录
                </button>
                <?php endif; ?>
            </div>
            <?php else: ?>
                <?php if ($unusedCount > 0): ?>
                <div class="res-toolbar">
                    <div class="res-warning">
                        <i class="ri-error-warning-line"></i>
                        <span>发现 <?= $unusedCount ?> 个未引用文件（<?= formatFileSize($unusedSize) ?>），可安全删除</span>
                    </div>
                    <button class="res-btn res-btn-danger" onclick="cleanUnused()">
                        <i class="ri-delete-bin-line"></i> 清理未引用
                    </button>
                </div>
                <?php endif; ?>

                <table class="res-table">
                    <thead>
                        <tr>
                            <th style="width:60px;">预览</th>
                            <th>文件名</th>
                            <th style="width:100px;">大小</th>
                            <th style="width:150px;">上传时间</th>
                            <th style="width:90px;">状态</th>
                            <th style="width:160px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($files as $file): ?>
                        <tr data-path="<?= htmlspecialchars($file['path']) ?>" data-used="<?= $file['used'] ? '1' : '0' ?>">
                            <td>
                                <?php if (in_array($file['ext'], $imageExts)): ?>
                                <img src="../<?= htmlspecialchars($file['path']) ?>" class="res-thumb" onclick="viewFile('<?= htmlspecialchars($file['path']) ?>')">
                                <?php else: ?>
                                <div class="res-file-icon"><i class="ri-file-line"></i></div>
                                <?php endif; ?>
                            </td>
                            <td><div class="res-filename" title="<?= htmlspecialchars($file['name']) ?>"><?= htmlspecialchars($file['name']) ?></div></td>
                            <td><?= formatFileSize($file['size']) ?></td>
                            <td><?= date('Y-m-d H:i', $file['time']) ?></td>
                            <td>
                                <?php if ($file['used']): ?>
                                <span class="res-status res-status-used"><i class="ri-check-line"></i> 已引用</span>
                                <?php else: ?>
                                <span class="res-status res-status-unused"><i class="ri-close-line"></i> 未引用</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="res-actions">
                                    <?php if (in_array($file['ext'], $imageExts)): ?>
                                    <button class="res-btn res-btn-primary" onclick="viewFile('<?= htmlspecialchars($file['path']) ?>')">预览</button>
                                    <?php endif; ?>
                                    <button class="res-btn res-btn-success" onclick="copyPath('<?= htmlspecialchars($file['path']) ?>')">复制</button>
                                    <button class="res-btn res-btn-danger" onclick="deleteFile('<?= htmlspecialchars($file['path']) ?>')">删除</button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    </div>
</div>

<script>
layui.use(['layer'], function(){
    var layer = layui.layer;
    var $ = layui.$;

    // 菜单高亮
    $("#menu-system").addClass('open');
    $("#menu-system > ul").css('display', 'block');
    $("#menu-system > .link > .admin-arrow").addClass('active');
    $("#menu-resources").addClass('active');

    // 预览图片
    window.viewFile = function(path){
        layer.photos({ photos: { data: [{ src: '../' + path }] } });
    };

    // 复制路径
    window.copyPath = function(path){
        var fullPath = '/' + path;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(fullPath).then(function(){ layer.msg('已复制', {icon: 1}); });
        } else {
            var input = document.createElement('input');
            input.value = fullPath;
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
            layer.msg('已复制', {icon: 1});
        }
    };

    // 删除文件
    window.deleteFile = function(path){
        layer.confirm('确定删除此文件？', {icon: 3}, function(index){
            $.post('ajax.php?action=resource_delete', {path: path}, function(res){
                if(res.code === 0){
                    layer.msg('文件已删除', {icon: 1});
                    $('tr[data-path="' + path + '"]').fadeOut(300, function(){ $(this).remove(); });
                } else {
                    layer.msg(res.msg, {icon: 2});
                }
            }, 'json');
            layer.close(index);
        });
    };

    // 清理未引用
    window.cleanUnused = function(){
        var paths = [];
        $('tr[data-used="0"]').each(function(){ paths.push($(this).data('path')); });
        if(paths.length === 0){ layer.msg('没有未引用的文件'); return; }
        layer.confirm('确定删除 ' + paths.length + ' 个未引用文件？', {icon: 3}, function(index){
            $.post('ajax.php?action=resource_batch_delete', {paths: paths.join(',')}, function(res){
                if(res.code === 0){
                    layer.msg('清理完成', {icon: 1});
                    setTimeout(function(){ location.reload(); }, 800);
                } else {
                    layer.msg(res.msg, {icon: 2});
                }
            }, 'json');
            layer.close(index);
        });
    };

    // 删除空目录
    window.deleteEmptyDir = function(dir){
        layer.confirm('确定删除此空目录？', {icon: 3}, function(index){
            $.post('ajax.php?action=resource_delete_dir', {dir: dir}, function(res){
                if(res.code === 0){
                    layer.msg('目录已删除', {icon: 1});
                    setTimeout(function(){ location.href = 'resources.php'; }, 800);
                } else {
                    layer.msg(res.msg, {icon: 2});
                }
            }, 'json');
            layer.close(index);
        });
    };

    // 目录删除按钮
    $('.res-tab-del').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        deleteEmptyDir($(this).data('dir'));
    });
});
</script>

<?php include View::getAdmView('footer'); ?>
