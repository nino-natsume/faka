<?php
/**
 * 用户前台 - 会员等级中心
 * 路径：/user/level.php
 *
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

/**
 * @var string $action
 * @var object $CACHE
 */

require_once 'globals.php';

$memberModel = new Member_Model();
$levelOrderModel = new Level_Order_Model();
$userModel = new User_Model();
$action = Input::getStrVar('action');

// ===============================
// 默认页：等级中心
// ===============================
if (empty($action)) {
    $user = $userModel->getOneUser(UID);
    $status = $memberModel->getUserLevelStatus(UID);
    $levels = $memberModel->getLevelsForFront($status['level_id'], $status['expire_time']);

    // 支付成功跳转标志
    $paid = (int)Input::getStrVar('paid') === 1;

    // 用户近 10 条等级订单
    $levelOrders = $levelOrderModel->getByUser(UID, 10);

    $uc_app_mode = isMobile();
    $uc_page_title = '会员等级';

    include View::getUserView('_adaptive_header');
    if (isMobile() && !empty($uc_app_mode)) {
        require_once View::getUserView('adaptive_level_mobile_app');
    } elseif (isMobile()) {
        require_once View::getUserView('adaptive_level_mobile');
    } else {
        require_once View::getUserView('adaptive_level');
    }
    include View::getUserView('_adaptive_footer');

    View::output();
    return;
}

// ===============================
// 钱包余额支付开通等级（AJAX）
// ===============================
if ($action == 'upgrade_ajax') {
    // 清理任何已缓冲的输出，保证响应是纯 JSON
    while (ob_get_level() > 0) { ob_end_clean(); }
    ob_start();

    if (!ISLOGIN) {
        Ret::error('请先登录');
    }
    // AJAX 场景用布尔 token 校验，避免 checkToken() 失败时输出 HTML
    if (!LoginAuth::checkAjaxToken()) {
        Ret::error('安全token校验失败，请刷新页面重试');
    }

    try {
        $level_id = intval(Input::postStrVar('level_id'));
        if ($level_id <= 0) {
            Ret::error('请选择要开通的等级');
        }

        // 当前用户等级 + 有效期折算算价
        $status = $memberModel->getUserLevelStatus(UID);
        $targetCheck = Level_Service::validatePurchaseTarget($status['level_id'], $level_id);
        if ($targetCheck !== '') {
            Ret::error($targetCheck);
        }
        $price = Level_Service::calculateUpgradePrice(
            $status['level_id'],
            $level_id,
            (int)$status['expire_time']
        );
        if (empty($price)) {
            Ret::error('等级不存在或已禁用');
        }

        $amount = (float)$price['amount'];
        $basePrice = (float)$price['base'];
        $carry = (float)$price['carry'];
        $type = $price['type']; // open | renew | upgrade
        $target = $price['target'];
        $targetDuration = (int)$price['duration_days'];

        if ($amount <= 0) {
            Ret::error('该等级无需支付，请联系管理员');
        }

        // 校验余额
        $user = $userModel->getOneUser(UID);
        $balance = floatval($user['money'] ?? 0);
        if ($balance < $amount) {
            while (ob_get_level() > 0) { ob_end_clean(); }
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode([
                'code' => 402,
                'msg'  => '钱包余额不足',
                'data' => [
                    'balance' => $balance,
                    'required' => $amount,
                    'base' => $basePrice,
                    'carry' => $carry,
                    'shortage' => round($amount - $balance, 2),
                    'explain' => $price['explain'] ?? '',
                    'redirect' => DC_URL . 'user/balance.php',
                ],
            ], JSON_UNESCAPED_UNICODE));
        }

        $timestamp = time();
        global $stationData;
        $out_trade_no = date('YmdHis', $timestamp) . mt_rand(1000, 9999);

        $db = Database::getInstance();

        try {
            $db->beginTransaction();
            $finishResult = null;

            // 1. 创建 dc_order（直接标记已支付）
            $insertOrder = [
                'station_id' => isset($stationData['id']) ? intval($stationData['id']) : 0,
                'client_ip' => getClientIP(),
                'user_id' => UID,
                'out_trade_no' => $out_trade_no,
                'amount' => intval(round($amount * 100)),
                'payment' => '钱包余额',
                'pay_name' => '钱包余额',
                'pay_plugin' => 'balance',
                'pay_status' => 1,
                'status' => 2,
                'service_status' => 1,
                'pay_time' => $timestamp,
                'expire_time' => $timestamp + 600,
                'create_time' => $timestamp,
                'device' => 'level_upgrade',
            ];
            $db->add('order', $insertOrder);
            $order_id = intval($db->insert_id());

            // 2. 创建 dc_level_order（待完成）
            $levelOrderModel->createPending([
                'order_id' => $order_id,
                'out_trade_no' => $out_trade_no,
                'user_id' => UID,
                'level_id' => $level_id,
                'purchase_type' => $type,
                'duration_days' => $targetDuration,
                'amount' => $amount,
                'base_price' => $basePrice,
                'old_level_id' => $status['raw_level_id'],
                'old_expire_time' => $status['expire_time'],
            ]);

            // 3. 扣除钱包余额
            $typeTextMap = ['renew' => '续费', 'upgrade' => '升级', 'open' => '开通'];
            $typeText = $typeTextMap[$type] ?? '开通';
            $note = $typeText . '会员等级：' . $target['name'] . '（订单号：' . $out_trade_no . '）';
            if ($carry > 0) $note .= '，有效期抵扣 -¥' . number_format($carry, 2);
            $balanceModel = new Balance_Model();
            $balanceModel->dec(UID, $amount, $note);

            $finishResult = $levelOrderModel->applyFinish($out_trade_no, $timestamp, false);
            if ($finishResult === false) {
                throw new Exception('等级变更失败，请联系管理员核查订单：' . $out_trade_no);
            }

            $db->commit();

            if (!empty($finishResult) && empty($finishResult['already_done'])) {
                $levelOrderModel->writeFinishLog($finishResult);
            }

            // 3.5 分店等级购买：自动绑定站长为上级（与 Order_Model::deliver 同逻辑）
            if (!empty($stationData['id']) && (int)$stationData['id'] > 0) {
                $stationOwnerUid = (int)($stationData['user_id'] ?? 0);
                if ($stationOwnerUid > 0 && $stationOwnerUid !== (int)UID) {
                    $ownerOk = $db->once_fetch_array(
                        "SELECT uid FROM " . DB_PREFIX . "user WHERE uid={$stationOwnerUid} AND state=0 LIMIT 1"
                    );
                    if ($ownerOk) {
                        $db->query(
                            "UPDATE " . DB_PREFIX . "user SET superior={$stationOwnerUid}"
                            . " WHERE uid=" . (int)UID . " AND (superior IS NULL OR superior <= 0)"
                        );
                        if ($db->affected_rows() > 0 && class_exists('User_Log_Model')) {
                            User_Log_Model::log(
                                (int)UID,
                                'superior_bind',
                                '分店等级购买自动绑定上级，站长UID：' . $stationOwnerUid . '，订单号：' . $out_trade_no . '（来源：分店等级购买）'
                            );
                        }
                    }
                }
            }

            // 4. 触发升级奖励分成（异步容错，按后台配置的触发场景判断）
            try {
                $rewardTypes = array_map('trim', explode(',', (string)Level_Service::getSetting(Level_Service::OPT_UPGRADE_REWARD_TYPES, 'open,upgrade,renew')));
                if (in_array($type, $rewardTypes)) {
                    $commissionModel = new Commission_Model();
                    $commissionModel->payUpgrade(UID, $basePrice, $level_id, $out_trade_no);
                }
            } catch (Throwable $ce) {
                // 奖励失败不影响主流程
                if (class_exists('User_Log_Model')) {
                    User_Log_Model::log(UID, 'upgrade_commission_fail', '升级奖励分成调用失败：' . $ce->getMessage() . '（订单号：' . $out_trade_no . '）');
                }
            }

            // 清理缓冲再返回
            while (ob_get_level() > 0) { ob_end_clean(); }
            Ret::success('开通成功', [
                'out_trade_no' => $out_trade_no,
                'level_name' => $target['name'],
                'amount' => $amount,
                'base' => $basePrice,
                'carry' => $carry,
                'explain' => $price['explain'] ?? '',
                'type' => $type,
            ]);
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    } catch (Throwable $e) {
        while (ob_get_level() > 0) { ob_end_clean(); }
        Ret::error('开通失败：' . $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine());
    }
}

// ===============================
// 计价预览（AJAX）—— 支持有效期折算的升级价格公式
// ===============================
if ($action == 'quote_ajax') {
    while (ob_get_level() > 0) { ob_end_clean(); }
    if (!ISLOGIN) { Ret::error('请先登录'); }
    try {
        $level_id = intval(Input::postStrVar('level_id'));
        if ($level_id <= 0) Ret::error('参数错误');
        $status = $memberModel->getUserLevelStatus(UID);
        $targetCheck = Level_Service::validatePurchaseTarget($status['level_id'], $level_id);
        if ($targetCheck !== '') Ret::error($targetCheck);
        $price = Level_Service::calculateUpgradePrice($status['level_id'], $level_id, (int)$status['expire_time']);
        if (empty($price)) Ret::error('等级不存在或已禁用');
        Ret::success('', [
            'amount' => $price['amount'],
            'base' => $price['base'],
            'carry' => $price['carry'],
            'duration_days' => $price['duration_days'],
            'remain_days' => $price['remain_days'],
            'type' => $price['type'],
            'explain' => $price['explain'],
            'level_name' => $price['target']['name'] ?? '',
        ]);
    } catch (Throwable $e) {
        Ret::error('预览失败：' . $e->getMessage());
    }
}

// 未知 action
Ret::error('非法请求');
