<?php
/**
 * Captcha - Multi-type support
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 *
 * Supported types: num / alpha / mix / zh / math / random
 */

if (!isset($_SESSION)) {
    session_start();
}

// --- Read captcha_type from database ---
$captchaType = 'num';
try {
    require_once dirname(__DIR__, 1) . '/../config.php';
    $__pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASSWD,
        [PDO::ATTR_TIMEOUT => 2]
    );
    $__stmt = $__pdo->prepare("SELECT option_value FROM " . DB_PREFIX . "options WHERE option_name = 'captcha_type' LIMIT 1");
    $__stmt->execute();
    $__row = $__stmt->fetch(PDO::FETCH_ASSOC);
    if ($__row && $__row['option_value'] !== '') {
        $captchaType = $__row['option_value'];
    }
    $__pdo = null;
} catch (Exception $e) {
    $captchaType = 'num';
}

// random = pick one of the 5 types
if ($captchaType === 'random') {
    $__types = ['num', 'alpha', 'mix', 'zh', 'math'];
    $captchaType = $__types[array_rand($__types)];
}

$width = 120;
$height = 40;
$fontFile = __DIR__ . '/captcha.ttf';

// --- Generate captcha content ---
switch ($captchaType) {

    case 'alpha':
        $__pool = 'abcdefghkmnpqrstuvwxyzABCDEFGHKMNPQRSTUVWXYZ';
        $displayText = '';
        for ($i = 0; $i < 5; $i++) {
            $displayText .= $__pool[mt_rand(0, strlen($__pool) - 1)];
        }
        $sessionCode = strtoupper($displayText);
        break;

    case 'mix':
        $__pool = 'abcdefghkmnpqrstuvwxyzABCDEFGHKMNPQRSTUVWXYZ23456789';
        $displayText = '';
        for ($i = 0; $i < 5; $i++) {
            $displayText .= $__pool[mt_rand(0, strlen($__pool) - 1)];
        }
        $sessionCode = strtoupper($displayText);
        break;

    case 'zh':
        $__zhChars = ['天','地','人','和','风','云','山','水','日','月',
                      '星','花','鸟','鱼','龙','虎','春','夏','秋','冬',
                      '东','南','西','北','金','木','火','土','大','小',
                      '上','下','左','右','红','绿','蓝','白','黑','明',
                      '高','低','长','快','慢','新','好','美','乐','安'];
        $__keys = array_rand($__zhChars, 4);
        $displayText = '';
        foreach ($__keys as $k) { $displayText .= $__zhChars[$k]; }
        $sessionCode = $displayText;
        $width = 140;
        break;

    case 'math':
        $__ops = ['+', '-', 'x', '/'];
        $__op  = $__ops[mt_rand(0, 3)];
        switch ($__op) {
            case '+':
                $a = mt_rand(1, 20); $b = mt_rand(1, 20);
                $answer = $a + $b;
                break;
            case '-':
                $a = mt_rand(1, 20); $b = mt_rand(1, $a);
                $answer = $a - $b;
                break;
            case 'x':
                $a = mt_rand(1, 9); $b = mt_rand(1, 9);
                $answer = $a * $b;
                break;
            default: // '/'
                $b = mt_rand(1, 9); $answer = mt_rand(1, 9);
                $a = $b * $answer;
                break;
        }
        $displayText = "{$a} {$__op} {$b} = ?";
        $sessionCode = (string)$answer;
        $width = 160;
        break;

    default: // 'num'
        $__pool = '0123456789';
        $displayText = '';
        for ($i = 0; $i < 5; $i++) {
            $displayText .= $__pool[mt_rand(0, strlen($__pool) - 1)];
        }
        $sessionCode = $displayText;
        break;
}

$_SESSION['code'] = strtoupper($sessionCode);

// --- Render image ---
$img = imagecreate($width, $height);
$bgColor  = isset($_GET['mode']) && $_GET['mode'] == 't'
    ? imagecolorallocate($img, 245, 245, 245)
    : imagecolorallocate($img, 255, 255, 255);
$pixColor  = imagecolorallocate($img, mt_rand(30, 180), mt_rand(10, 100), mt_rand(40, 250));
$fontColor = imagecolorallocate($img, mt_rand(30, 180), mt_rand(10, 100), mt_rand(40, 250));

if ($captchaType === 'zh') {
    // Chinese needs a CJK-capable font
    $__zhFont = $fontFile;
    foreach ([
        'C:/Windows/Fonts/simhei.ttf',
        'C:/Windows/Fonts/msyh.ttc',
        'C:/Windows/Fonts/simsun.ttc',
        '/usr/share/fonts/truetype/wqy/wqy-zenhei.ttc',
        '/usr/share/fonts/wqy-zenhei/wqy-zenhei.ttc',
        '/usr/share/fonts/opentype/noto/NotoSansCJK-Regular.ttc',
    ] as $p) {
        if (file_exists($p)) { $__zhFont = $p; break; }
    }
    $__len = mb_strlen($displayText, 'UTF-8');
    $__cw  = $width / ($__len + 1);
    for ($i = 0; $i < $__len; $i++) {
        $ch = mb_substr($displayText, $i, 1, 'UTF-8');
        $x  = (int)($i * $__cw) + mt_rand(8, 14);
        $y  = mt_rand(22, 30);
        imagettftext($img, 16, mt_rand(-12, 12), $x, $y, $fontColor, $__zhFont, $ch);
    }
} elseif ($captchaType === 'math') {
    // Render expression as whole string, roughly centered
    $__bbox = imagettfbbox(16, 0, $fontFile, $displayText);
    $__tw   = abs($__bbox[2] - $__bbox[0]);
    $x = max(5, (int)(($width - $__tw) / 2));
    imagettftext($img, 16, 0, $x, 28, $fontColor, $fontFile, $displayText);
} else {
    // Standard per-character rendering (num / alpha / mix)
    $__len = strlen($displayText);
    $__cw  = $width / ($__len + 1);
    for ($i = 0; $i < $__len; $i++) {
        $x = (int)($i * $__cw) + mt_rand(5, 10);
        $y = mt_rand(20, 30);
        imagettftext($img, 18, mt_rand(-30, 30), $x, $y, $fontColor, $fontFile, $displayText[$i]);
    }
}

// Noise pixels
for ($j = 0; $j < 80; $j++) {
    imagesetpixel($img, mt_rand(0, $width), mt_rand(0, $height), $pixColor);
}
// Noise lines
for ($j = 0; $j < 4; $j++) {
    imageline($img, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $pixColor);
}

header('Content-Type: image/png');
imagepng($img);
imagedestroy($img);
