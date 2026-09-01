<?php defined('DC_ROOT') || exit('access denied!'); ?>
<!doctype html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <!-- viewport：加入 maximum-scale=1 与 user-scalable=no，用于禁用 iOS Safari 在点击输入框时（input font-size<16px 触发）的自动放大行为；
         登录/找回密码页为固定表单布局，不需要双指缩放，因此该限制无副作用。 -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name=renderer content=webkit>
    <title><?= $page_title ?></title>

    <link rel="stylesheet" href="./views/css/bootstrap.min.css">

    <!-- 字体 -->
    <link rel="stylesheet" type="text/css" href="./views/font-awesome-4.7.0/css/font-awesome.min.css">
    <!-- Remix Icon -->
    <link rel="stylesheet" type="text/css" href="./views/remixicon/remixicon.css">

    <link rel="stylesheet" href="./views/layui-v2.11.6//layui/css/layui.css">
    <script src="./views/layui-v2.11.6/layui/layui.js"></script>

    <script src="./views/js/jquery.min.3.5.1.js"></script>


    <script src="./views/js/common.js?v=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>
    <?php doAction('login_head') ?>
</head>
<body class="bg-gradient-primary">
