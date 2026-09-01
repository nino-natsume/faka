<?php
defined('DC_ROOT') || exit('access denied!');
$orderList = isset($list) && is_array($list) ? $list : [];
require __DIR__ . '/visitors_order_mobile_app.php';
return;
