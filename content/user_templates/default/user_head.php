<?php defined('DC_ROOT') || exit('access denied!'); ?>
<!doctype html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, maximum-scale=1, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name=renderer content=webkit>
    <title><?= $page_title ?></title>
    <?php include __DIR__ . '/_viewport_mobile_switch.php'; ?>

    <link href="<?= getSiteFaviconUrl('../admin/views/images/favicon.ico'); ?>" rel="shortcut icon">

    <link rel="stylesheet" href="../../../admin/views/layui-v2.11.6/layui/css/layui.css">
    <script src="../../../admin/views/layui-v2.11.6/layui/layui.js"></script>

    <script src="../../../admin/views/js/jquery.min.3.5.1.js"></script>
    <link rel="stylesheet" type="text/css" href="../../../admin/views/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../../../admin/views/remixicon/remixicon.css">

    <?php doAction('login_head') ?>
</head>
<body>
