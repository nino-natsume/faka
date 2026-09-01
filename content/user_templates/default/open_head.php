<?php defined('DC_ROOT') || exit('access denied!'); ?>
<!doctype html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name=renderer content=webkit>
    <?php include __DIR__ . '/_viewport_mobile_switch.php'; ?>
    <link rel="stylesheet" href="<?= DC_URL ?>admin/views/layui-v2.11.6//layui/css/layui.css">
    <script src="<?= DC_URL ?>admin/views/layui-v2.11.6//layui/layui.js"></script>
    <link rel="stylesheet" type="text/css" href="<?= DC_URL ?>admin/views/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="<?= DC_URL ?>admin/views/remixicon/remixicon.css">

    <!-- jquery v3.5.1 -->
    <script src="<?= DC_URL ?>admin/views/js/jquery.min.3.5.1.js"></script>

    <link rel="stylesheet" href="<?= DC_URL ?>/content/common/css/style.css">

    <?php doAction('open_head') ?>

<style>
    .layui-badge-rim, .layui-border, .layui-colla-content, .layui-colla-item, .layui-collapse, .layui-elem-field, .layui-form-pane .layui-form-item[pane], .layui-form-pane .layui-form-label, .layui-input, .layui-input-split, .layui-panel, .layui-quote-nm, .layui-select, .layui-tab-bar, .layui-tab-card, .layui-tab-title, .layui-tab-title .layui-this:after, .layui-textarea{
        border-color: #c5c5c5;
    }
    <?php if (isMobile()): ?>
    /* iOS Safari：移动端弹窗/iframe 内输入框聚焦时字号不足 16px 会触发自动放大 */
    input:not([type="checkbox"]):not([type="radio"]),
    textarea,
    select,
    .layui-input,
    .layui-textarea,
    .layui-form-select,
    .layui-form-select .layui-input,
    .layui-form-select input,
    .layui-form-select dl dd {
        font-size: 16px !important;
    }
    input:not([type="checkbox"]):not([type="radio"])::placeholder,
    textarea::placeholder {
        font-size: 16px !important;
    }
    <?php endif; ?>
</style>


</head>
<body>


