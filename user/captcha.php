<?php
/**
 * 简单验证码生成
 */
session_start();

$action = isset($_GET['action']) ? $_GET['action'] : '';

// 验证验证码
if ($action == 'check') {
    header('Content-Type: application/json');
    $code = isset($_POST['code']) ? trim($_POST['code']) : '';
    
    if (empty($code) || !isset($_SESSION['order_captcha']) || $code !== $_SESSION['order_captcha']) {
        echo json_encode(['code' => 400, 'msg' => '验证码错误']);
    } else {
        unset($_SESSION['order_captcha']);
        echo json_encode(['code' => 0, 'msg' => '验证成功']);
    }
    exit;
}

// 生成4位数字验证码
$code = '';
for ($i = 0; $i < 4; $i++) {
    $code .= mt_rand(0, 9);
}
$_SESSION['order_captcha'] = $code;

// 创建图片
$width = 100;
$height = 40;
$img = imagecreatetruecolor($width, $height);

// 背景色
$bgColor = imagecolorallocate($img, 255, 255, 255);
imagefill($img, 0, 0, $bgColor);

// 添加干扰线
for ($i = 0; $i < 3; $i++) {
    $lineColor = imagecolorallocate($img, mt_rand(150, 220), mt_rand(150, 220), mt_rand(150, 220));
    imageline($img, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $lineColor);
}

// 添加干扰点
for ($i = 0; $i < 50; $i++) {
    $pixelColor = imagecolorallocate($img, mt_rand(150, 220), mt_rand(150, 220), mt_rand(150, 220));
    imagesetpixel($img, mt_rand(0, $width), mt_rand(0, $height), $pixelColor);
}

// 绘制验证码文字
$colors = [
    imagecolorallocate($img, 31, 119, 180),
    imagecolorallocate($img, 255, 127, 14),
    imagecolorallocate($img, 44, 160, 44),
    imagecolorallocate($img, 214, 39, 40)
];

for ($i = 0; $i < 4; $i++) {
    $textColor = $colors[$i];
    $x = 15 + $i * 20;
    $y = mt_rand(25, 32);
    imagestring($img, 5, $x, $y - 15, $code[$i], $textColor);
}

// 输出图片
header('Content-Type: image/png');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
imagepng($img);
imagedestroy($img);
