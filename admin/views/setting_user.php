<?php defined('DC_ROOT') || exit('access denied!'); ?>
<style>
    .ucfg-section { background: #ffffff85; border: 1px solid #eef1f4; border-radius: 8px; padding: 18px 20px; margin-bottom: 14px; }
    .ucfg-title { font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; }
    .ucfg-title i { color: #2563eb; }
    .ucfg-sub { font-size: 12px; font-weight: 600; color: #6b7280; margin: 14px 0 6px; padding: 6px 0 4px; border-bottom: 1px dashed #e5e7eb; display: flex; align-items: center; gap: 5px; }
    .ucfg-sub:first-child { margin-top: 0; }
    .ucfg-row { display: grid; grid-template-columns: 160px 1fr; gap: 10px; align-items: center; padding: 8px 0; }
    .ucfg-row > label { color: #374151; font-weight: 500; }
    .ucfg-row .layui-input-inline { width: 260px; }
    .ucfg-row .layui-form-select { width: 100%; }
    .ucfg-tip { color: #6b7280; font-size: 12px; line-height: 1.7; }
    .ucfg-tip b { color: #2563eb; }
    .ucfg-tip-summary { cursor: pointer; }
    .ucfg-tip-detail { display: none; margin-top: 2px; }
    .ucfg-tip-toggle { color: #2563eb; cursor: pointer; font-size: 11px; user-select: none; }
    .ucfg-tip-toggle:hover { text-decoration: underline; }

    @media (max-width: 768px) {
        .layui-card-body { padding: 12px !important; }
        .ucfg-row { grid-template-columns: 1fr; gap: 4px; }
        .ucfg-row > label { padding-top: 0; font-size: 13px; }
        .ucfg-section { padding: 14px 12px; }
        .ucfg-row .layui-input-inline { width: 100%; }
        .ucfg-row .layui-input,
        .ucfg-row .layui-textarea,
        .ucfg-row .layui-form-select,
        .ucfg-row select { max-width: 100%; }
        .ucfg-sub { font-size: 11px; }
    }
</style>

<div class="layui-tabs order-tabs-wrapper" style="display:flex;align-items:center;justify-content:space-between;" lay-options="{trigger: false}">
    <ul class="layui-tabs-header">
        <li><a href="./shop.php">商城配置</a></li>
        <li><a href="./shop.php?action=gg">公告设置</a></li>
        <li><a href="./shop.php?action=btx">下单输入框</a></li>
        <li class="layui-this"><a href="./shop.php?action=user">用户配置</a></li>
        <li><a href="./shop.php?action=station_setting">分店配置</a></li>
    </ul>
</div>
<div class="layui-table-view" style="border-radius:8px;overflow:hidden;">
    <div class="layui-card-header" style="display:flex;align-items:center;justify-content:center;position:relative;height:auto;padding:12px 15px;">
        <span style="position:absolute;left:15px;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">用户配置</span>
    </div>
    <div class="layui-card-body" style="padding:20px;">
        <form class="layui-form" method="post" id="user-config-form">
            <div class="ucfg-section">
                <div class="ucfg-title"><i class="ri-user-settings-line"></i> 登录与注册</div>
                <div class="ucfg-row">
                    <label>用户登录</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="login_switch" value="y" lay-skin="switch" lay-text="开启|关闭" <?= $login_switch == 'y' ? 'checked' : '' ?>>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">开启 = 显示登录入口。关闭 = 隐藏登录（临时维护用）。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail"><b>开启</b> = 前台显示登录入口，用户可以登录账号。<br><b>关闭</b> = 隐藏登录入口，所有用户都无法登录（适合临时维护时使用）。</span>
                        </div>
                    </div>
                </div>
                <div class="ucfg-row">
                    <label>用户注册</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="register_switch" value="y" lay-skin="switch" lay-text="开启|关闭" <?= $register_switch == 'y' ? 'checked' : '' ?>>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">开启 = 允许自助注册。关闭 = 仅管理员手动添加。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail"><b>开启</b> = 允许新用户自行注册账号。<br><b>关闭</b> = 不允许注册，只能由管理员在后台手动添加用户。</span>
                        </div>
                    </div>
                </div>
                <div class="ucfg-row">
                    <label>用户名注册</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="register_username_switch" value="y" lay-skin="switch" lay-text="开启|关闭" <?= $register_username_switch == 'y' ? 'checked' : '' ?>>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">开启 = 显示用户名注册选项。无需邮箱或手机。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail"><b>开启</b> = 注册页面显示「用户名注册」选项，用户仅需填写用户名和密码即可注册。<br><b>关闭</b> = 隐藏用户名注册入口。<br><span style="color:#6b7280;">用户名注册不需要对接邮箱或短信，适合简化注册流程。</span></span>
                        </div>
                    </div>
                </div>
                <div class="ucfg-row">
                    <label>邮箱注册</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="register_email_switch" value="y" lay-skin="switch" lay-text="开启|关闭" <?= $register_email_switch == 'y' ? 'checked' : '' ?>>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">开启 = 显示邮箱注册选项。需先配置 SMTP。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail"><b>开启</b> = 注册页面显示「邮箱注册」选项。<b>关闭</b> = 隐藏邮箱注册入口。<br>需要先配置好「邮箱配置」里的 SMTP 发件信息，否则无法发送验证码。</span>
                        </div>
                    </div>
                </div>
                <div class="ucfg-row">
                    <label>手机注册</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="register_tel_switch" value="y" lay-skin="switch" lay-text="开启|关闭" <?= $register_tel_switch == 'y' ? 'checked' : '' ?>>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">开启 = 显示手机注册选项。需先购买短信包。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail"><b>开启</b> = 注册页面显示「手机号注册」选项。<b>关闭</b> = 隐藏手机注册入口。<br>需先前往「<a href="./store.php?action=svip" style="color:#2563eb;">应用商店 → 余额充值</a>」购买短信包，否则无法发送验证码。</span>
                        </div>
                    </div>
                </div>
                <div class="ucfg-row">
                    <label>用户名登录</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="login_username_switch" value="y" lay-skin="switch" lay-text="开启|关闭" <?= $login_username_switch == 'y' ? 'checked' : '' ?>>
                        </div>
                        <div class="ucfg-tip">开启 = 用户可以用用户名 + 密码登录。关闭 = 不显示用户名登录方式。</div>
                    </div>
                </div>
                <div class="ucfg-row">
                    <label>邮箱登录</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="login_email_switch" value="y" lay-skin="switch" lay-text="开启|关闭" <?= $login_email_switch == 'y' ? 'checked' : '' ?>>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">开启 = 显示邮箱验证码登录。未注册邮箱自动创建账号。需配置 SMTP 邮件。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail"><b>开启</b> = 登录页面显示「邮箱登录」选项，使用邮箱验证码验证身份。已注册邮箱直接登录，未注册邮箱将自动创建账号并登录。<b>关闭</b> = 隐藏邮箱登录入口。<br>需先在「<a href="./setting.php?action=mail" style="color:#2563eb;">系统设置 → 邮件通知</a>」中配置 SMTP，否则无法发送验证码邮件。</span>
                        </div>
                    </div>
                </div>
                <div class="ucfg-row">
                    <label>手机登录</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="login_tel_switch" value="y" lay-skin="switch" lay-text="开启|关闭" <?= $login_tel_switch == 'y' ? 'checked' : '' ?>>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">开启 = 显示手机验证码登录。未注册手机号自动创建账号。需先购买短信包。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail"><b>开启</b> = 登录页面显示「手机登录」选项，使用短信验证码验证身份。已注册手机号直接登录，未注册手机号将自动创建账号并登录。<b>关闭</b> = 隐藏手机登录入口。<br>需先前往「<a href="./store.php?action=svip" style="color:#2563eb;">应用商店 → 余额充值</a>」购买短信包，否则无法发送验证码。</span>
                        </div>
                    </div>
                </div>
                <div class="ucfg-sub"><i class="ri-link"></i> 注册绑定</div>
                <div class="ucfg-row">
                    <label>强制绑定手机号</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="register_bind_tel" value="y" lay-skin="switch" lay-text="开启|关闭" <?= (isset($register_bind_tel) && $register_bind_tel == 'y') ? 'checked' : '' ?>>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">开启 = 用户名/邮箱注册时也需填写手机号。需开启「手机注册」才生效。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail"><b>开启</b> = 使用用户名或邮箱注册时，必须额外填写手机号。适合需要短信通知或实名认证的场景。<br><b>关闭</b> = 仅手机注册时才需要填手机号。<br><span style="color:#e65100;">⚠ 若「手机注册」已关闭，则此开关自动失效，前端不会显示手机号输入框。</span><br><span style="color:#6b7280;">需先前往「<a href="./store.php?action=svip" style="color:#2563eb;">应用商店 → 余额充值</a>」购买短信包，否则无法发送验证码。</span></span>
                        </div>
                    </div>
                </div>
                <div class="ucfg-row">
                    <label>强制绑定邮箱</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="register_bind_email" value="y" lay-skin="switch" lay-text="开启|关闭" <?= (isset($register_bind_email) && $register_bind_email == 'y') ? 'checked' : '' ?>>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">开启 = 用户名/手机注册时也需填写邮箱。需开启「邮箱注册」才生效。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail"><b>开启</b> = 使用用户名或手机注册时，必须额外填写邮箱地址。适合需要邮件通知或找回密码的场景。<br><b>关闭</b> = 仅邮箱注册时才需要填邮箱。<br><span style="color:#e65100;">⚠ 若「邮箱注册」已关闭，则此开关自动失效，前端不会显示邮箱输入框。</span><br><span style="color:#6b7280;">需要先配置好 SMTP 发件信息，否则无法发送验证码。</span></span>
                        </div>
                    </div>
                </div>
                <div class="ucfg-row">
                    <label>强制填写邀请码</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="register_bind_invite" value="y" lay-skin="switch" lay-text="开启|关闭" <?= (isset($register_bind_invite) && $register_bind_invite == 'y') ? 'checked' : '' ?>>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">开启 = 注册时必须填写邀请码，否则无法注册。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail"><b>开启</b> = 注册页显示邀请码输入框，用户必须填写有效的邀请码才能完成注册。通过邀请链接访问时自动填充。<br><b>关闭</b> = 注册页仍显示邀请码输入框（选填），用户可自愿填写。<br><span style="color:#6b7280;">与「强制邀请注册」的区别：强制邀请注册要求必须通过邀请链接进入，无链接直接拦截；本选项允许用户自行访问注册页但必须手动输入邀请码。</span></span>
                        </div>
                    </div>
                </div>
                <div class="ucfg-sub"><i class="ri-message-2-line"></i> 短信限制</div>
                <div class="ucfg-row">
                    <label>绑定手机每日限制</label>
                    <div>
                        <div class="layui-input-inline" style="width:120px;">
                            <input type="number" name="sms_bind_phone_daily_limit" class="layui-input" min="0" step="1" value="<?= intval($sms_bind_phone_daily_limit ?? 0) ?>">
                        </div>
                        <div class="ucfg-tip">每个用户每日绑定手机号时可发送的短信验证码次数上限。填 0 = 不限制。默认 5 次。</div>
                    </div>
                </div>
                <div class="ucfg-row">
                    <label>短信登录每日限制</label>
                    <div>
                        <div class="layui-input-inline" style="width:120px;">
                            <input type="number" name="sms_login_daily_limit" class="layui-input" min="0" step="1" value="<?= intval($sms_login_daily_limit ?? 0) ?>">
                        </div>
                        <div class="ucfg-tip">每个用户每日通过短信验证码登录时可发送的次数上限。填 0 = 不限制。默认 10 次。</div>
                    </div>
                </div>
                <div class="ucfg-sub"><i class="ri-settings-3-line"></i> 其他</div>
                <div class="ucfg-row">
                    <label>强制邀请注册</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="invite_register_only" value="1" lay-skin="switch" lay-text="开启|关闭" <?= intval($levelSettings[Level_Service::OPT_INVITE_ONLY]) === 1 ? 'checked' : '' ?>>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">开启 = 必须通过邀请链接才能注册。关闭 = 任何人可自助注册。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail"><b>开启</b> = 新用户<b>必须</b>通过老用户分享的邀请链接才能注册，没有链接就注册不了。适合做邀请制社群。<br><b>关闭</b> = 任何人都可以自己注册，不需要邀请。<br><span style="color:#6b7280;">即使关闭，用户通过邀请链接注册时，系统仍然会自动绑定上下级关系。</span></span>
                        </div>
                    </div>
                </div>
                <?php $defaultGradeValue = intval($levelSettings[Level_Service::OPT_DEFAULT_GRADE]); ?>
                <?php if ($defaultGradeValue <= 0 && !empty($memberLevels[0]['id'])) $defaultGradeValue = (int)$memberLevels[0]['id']; ?>
                <div class="ucfg-row">
                    <label>新注册默认等级</label>
                    <div>
                        <div class="layui-input-inline">
                            <select name="level_default_grade">
                                <?php foreach ($memberLevels as $lv): ?>
                                    <option value="<?= (int)$lv['id'] ?>" <?= $defaultGradeValue === (int)$lv['id'] ? 'selected' : '' ?>>会员等级：<?= htmlspecialchars($lv['name']) ?> (#<?= (int)$lv['id'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">注册成功后自动分配的等级。一般选最低等级。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail">新用户注册成功后，系统会自动把他分配到这个等级。<br>一般选最低的那个（比如「普通用户」），用户后续可以自己花钱升级到更高等级。</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ucfg-section">
                <div class="ucfg-title"><i class="ri-shield-keyhole-line"></i> 功能与门槛</div>
                <?php
                $activeLevels = array_filter($memberLevels, function($lv) { return (int)$lv['state'] === 1; });
                $hasActiveLevels = count($activeLevels) > 1;
                $currentDeposit = intval($levelSettings['level_deposit_grade'] ?? 0);
                ?>
                <div id="gate-fields-wrapper" style="<?= $hasActiveLevels ? '' : 'opacity:.45;pointer-events:none;' ?>">
                <?php if (!$hasActiveLevels): ?>
                <div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:6px;padding:8px 14px;margin-bottom:10px;font-size:12px;color:#92400e;">
                    <i class="fa fa-info-circle"></i> 当前仅有 1 个会员等级（默认等级），功能门槛无法生效。请先到 <a href="./member.php" style="color:#2563eb;font-weight:600;">会员等级管理</a> 启用多个等级后再配置。
                </div>
                <?php endif; ?>
                <div class="ucfg-row">
                    <label>使用提现</label>
                    <div>
                        <div class="layui-input-inline">
                            <select name="level_deposit_grade"<?= $hasActiveLevels ? '' : ' disabled' ?>>
                                <option value="0" <?= $currentDeposit === 0 ? 'selected' : '' ?>>不限制</option>
                                <?php foreach ($activeLevels as $lv): ?>
                                    <option value="<?= (int)$lv['id'] ?>" <?= $currentDeposit === (int)$lv['id'] ? 'selected' : '' ?>><?= htmlspecialchars($lv['name']) ?> (#<?= (int)$lv['id'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="ucfg-tip">达到此会员等级才能申请余额提现。选「不限制」= 任何人都能提现。分店用户的提现权限由分店等级控制。</div>
                    </div>
                </div>
                </div>
            </div>

            <div class="ucfg-section">
                <div class="ucfg-title" id="commission-section"><i class="ri-hand-coin-line"></i> 分销分成</div>

                <!-- ▸ 子分组 1：等级升级定价 -->
                <div class="ucfg-sub"><i class="ri-price-tag-3-line" style="color:#6b7280;font-size:13px;"></i> 等级升级定价</div>
                <div class="ucfg-row">
                    <label>升级计费方式</label>
                    <div>
                        <div class="layui-input-inline" style="width:200px;">
                            <select name="upgrade_price_mode" class="layui-select">
                                <option value="diff" <?= ($levelSettings[Level_Service::OPT_UPGRADE_PRICE_MODE] ?? 'diff') === 'diff' ? 'selected' : '' ?>>补差价</option>
                                <option value="full" <?= ($levelSettings[Level_Service::OPT_UPGRADE_PRICE_MODE] ?? 'diff') === 'full' ? 'selected' : '' ?>>按目标全额</option>
                            </select>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">补差价 = 只补差额，按钮显示"补 ¥XX 升级"。按目标全额 = 按目标原价收，按钮显示"¥XX 升级"。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail"><b>补差价</b>（推荐）= 只收目标等级价 − 当前等级价的差额。例：当前 50 元升到 100 元，只收 50 元，按钮显示"补 ¥50.00 升级"。<br><b>按目标全额</b> = 直接按目标等级原价收费。同样场景收 100 元，按钮显示"¥100.00 升级"。<br><span style="color:#6b7280;">此设置仅影响升级场景。续费按续费比例计价，首次开通按原价计价，均不受此设置影响。</span></span>
                        </div>
                    </div>
                </div>

                <!-- ▸ 子分组 2：升级奖励 -->
                <div class="ucfg-sub"><i class="ri-gift-line" style="color:#6b7280;font-size:13px;"></i> 升级奖励</div>
                <div class="ucfg-row">
                    <label>奖励比例</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="number" name="level_upgrade_profit" class="layui-input" min="0" max="100" step="0.01" value="<?= (float)$levelSettings[Level_Service::OPT_UPGRADE_PROFIT] ?>">
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">单位 %。下级付费购买等级后，上级按等级标价的此比例拿奖励。填 0 = 关闭。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail">用户 B 花钱购买等级后，上级 A 按等级标价的这个比例拿奖励。<br>例如填 10：等级标价 100 元，上级得 100×10% = <b>10 元</b>。填 0 = 关闭升级奖励，没人拿钱。</span>
                        </div>
                    </div>
                </div>
                <?php
                    $rewardTypes = (string)($levelSettings[Level_Service::OPT_UPGRADE_REWARD_TYPES] ?? 'open,upgrade,renew');
                    $rewardArr = array_map('trim', explode(',', $rewardTypes));
                ?>
                <div class="ucfg-row">
                    <label>触发场景</label>
                    <div>
                        <div style="display:flex;gap:18px;flex-wrap:wrap;padding:6px 0;">
                            <label style="display:inline-flex;align-items:center;gap:4px;cursor:pointer;">
                                <input type="checkbox" name="upgrade_reward_open" value="1" <?= in_array('open', $rewardArr) ? 'checked' : '' ?>> 首次开通
                            </label>
                            <label style="display:inline-flex;align-items:center;gap:4px;cursor:pointer;">
                                <input type="checkbox" name="upgrade_reward_upgrade" value="1" <?= in_array('upgrade', $rewardArr) ? 'checked' : '' ?>> 升级高等级
                            </label>
                            <label style="display:inline-flex;align-items:center;gap:4px;cursor:pointer;">
                                <input type="checkbox" name="upgrade_reward_renew" value="1" <?= in_array('renew', $rewardArr) ? 'checked' : '' ?>> 同级续费
                            </label>
                        </div>
                        <div class="ucfg-tip">勾选哪些付费场景触发升级奖励。不勾 = 该场景不发。奖励基数按等级标价，不按实付金额。</div>
                    </div>
                </div>
                <div class="ucfg-row">
                    <label>等级门槛</label>
                    <div>
                        <div class="layui-input-inline" style="width:220px;">
                            <?php $ulc = (int)($levelSettings[Level_Service::OPT_UPGRADE_LEVEL_CHECK] ?? 1); ?>
                            <select name="upgrade_level_check">
                                <option value="1" <?= $ulc === 1 ? 'selected' : '' ?>>上级等级 ≥ 目标等级（默认）</option>
                                <option value="0" <?= $ulc === 0 ? 'selected' : '' ?>>不检查（任何上级都能拿）</option>
                                <option value="2" <?= $ulc === 2 ? 'selected' : '' ?>>站长豁免（站长不限，其他仍检查）</option>
                            </select>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">上级等级低于下级新等级时是否仍然发放奖励。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail"><b>上级等级 ≥ 目标等级</b>：上级等级低于下级新等级则不发奖励。<br><b>不检查</b>：所有上级都能拿，不管等级高低。<br><b>站长豁免</b>：分店站长作为上级时不受等级限制，普通推荐关系仍需等级达标。</span>
                        </div>
                    </div>
                </div>

                <!-- ▸ 子分组 3：订单分佣 -->
                <div class="ucfg-sub"><i class="ri-exchange-funds-line" style="color:#6b7280;font-size:13px;"></i> 订单分佣</div>
                <div class="ucfg-row">
                    <label>分佣计算基数</label>
                    <div>
                        <div class="layui-input-inline">
                            <?php $commBase = $levelSettings[Level_Service::OPT_COMMISSION_BASE] ?? 'total'; ?>
                            <select name="commission_base">
                                <option value="total" <?= $commBase === 'total' ? 'selected' : '' ?>>按商品总价（默认）</option>
                                <option value="profit" <?= $commBase === 'profit' ? 'selected' : '' ?>>按利润（总价 - 成本价）</option>
                            </select>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">按总价 = 拿售价来算。按利润 = 只拿赚到的部分来算。两种都有安全兜底不会赔钱。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail"><b>按商品总价</b>（默认）：用户花了多少钱，就拿这个数来算佣金。<br>举例：成本价 10 元，卖 20 元，拿 <b>20 元</b>来算（但分出去总额不超利润 10 元）。<br><b>按利润</b>：只拿赚到的那部分来算，更保守。同例只拿利润 <b>10 元</b>来算。<br><span style="color:#e65100;">⚠ 不管选哪个，分出去的佣金总额永远不超过实际利润，不会赔钱。</span></span>
                        </div>
                    </div>
                </div>
                <div class="ucfg-row">
                    <label>参与分佣比例</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="number" name="commission_ratio" class="layui-input" min="0" max="100" step="0.01" value="<?= (float)($levelSettings[Level_Service::OPT_COMMISSION_RATIO] ?? 100) ?>">
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">单位 %。从上面的"蛋糕"里切多少出来分给上级。填 100 = 全分，填 0 = 关闭分佣。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail">填 <b>100</b>（默认）= 整个蛋糕都拿来分。填 <b>50</b> = 只切一半出来分，另一半是你的纯利润。<br><b>举例</b>：成本 10 元，卖 20 元（利润 10 元）：<br>选「按利润」+ 填 80 → 切出 10×80% = <b>8 元</b>分佣，你至少留 <b>2 元</b>。<br>选「按总价」+ 填 100 → 安全兜底，最多分 <b>10 元</b>（利润），不会赔钱。<br>填 <b>0</b> = 一分钱都不分，相当于关闭商品订单的分销佣金。</span>
                        </div>
                    </div>
                </div>
                <div class="ucfg-row">
                    <label>分店订单佣金归属</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="level_commits_distribute" value="1" lay-skin="switch" lay-text="上级优先|站长优先" <?= intval($levelSettings[Level_Service::OPT_COMMITS_DIST]) === 1 ? 'checked' : '' ?>>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">站长优先 = 佣金归站长。上级优先 = 佣金按邀请关系分给上级。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail"><b>站长优先</b>（默认推荐）：佣金直接归分店站长，按差价结算。<br><b>上级优先</b>：佣金不给站长，而是按邀请关系分给买家的上级。如果买家没有上级，钱自动回到站长。<br><span style="color:#6b7280;">主站（你自己的店）的订单不受这个开关影响，有上级就正常分佣。</span></span>
                        </div>
                    </div>
                </div>
                <div class="ucfg-row">
                    <label>无限级分佣</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="level_infinite_division" value="1" lay-skin="switch" lay-text="启用|关闭" <?= intval($levelSettings[Level_Service::OPT_INFINITE_DIV]) === 1 ? 'checked' : '' ?>>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">控制遇到"等级不够"的上级时是否继续往上找。关闭 = 遇到就停。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail">只要每一层上级的等级都 ≥ 下级，分佣链会一直往上分（不管这个开关）。<b>这个开关只控制遇到"等级不够"的上级时怎么处理：</b><br><b>关闭</b>（推荐新手）= 遇到等级不够的上级就停，不再继续往上分。<br><b>启用</b> = 遇到等级不够的上级时不停，继续往上找（具体行为见下方子选项）。<br><span style="color:#e65100;">⚠ 启用前请确认你的等级设置里「直属上级先拿比例」和「停止阈值」配置合理，否则利润可能被多层分完。</span></span>
                        </div>
                    </div>
                </div>
                <?php $infiniteSkip = intval($levelSettings[Level_Service::OPT_INFINITE_SKIP] ?? 1); ?>
                <div class="ucfg-row" id="infinite-skip-row" style="<?= intval($levelSettings[Level_Service::OPT_INFINITE_DIV]) === 1 ? '' : 'display:none;' ?>">
                    <label>等级不够时处理</label>
                    <div>
                        <div class="layui-input-inline" style="width:260px;">
                            <select name="level_infinite_skip">
                                <option value="1" <?= $infiniteSkip === 1 ? 'selected' : '' ?>>跳过不发，找更上级</option>
                                <option value="0" <?= $infiniteSkip === 0 ? 'selected' : '' ?>>照常发放，再继续往上</option>
                            </select>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">仅「无限级分佣」启用时生效。跳过 = 不拿钱直接找更上级。照常 = 拿完再继续。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail"><b>跳过不发</b>（默认）= 这个上级一分钱不拿，系统直接找他的更上级来分。<br><b>照常发放</b> = 这个上级照样拿钱，拿完后剩余的钱继续往上分。</span>
                        </div>
                    </div>
                </div>
                <div class="ucfg-row">
                    <label>等级门槛</label>
                    <div>
                        <div class="layui-input-inline" style="width:220px;">
                            <?php $olc = (int)($levelSettings[Level_Service::OPT_ORDER_LEVEL_CHECK] ?? 0); ?>
                            <select name="order_level_check">
                                <option value="0" <?= $olc === 0 ? 'selected' : '' ?>>不检查（默认）</option>
                                <option value="1" <?= $olc === 1 ? 'selected' : '' ?>>上级等级 ≥ 下级等级</option>
                            </select>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">上级等级低于下级时是否仍然给上级分佣。不检查 = 任何上级都能拿。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail"><b>不检查</b>（默认）：不管上级等级高低，只要是上级就能拿分佣。<br><b>上级等级 ≥ 下级等级</b>：上级等级低于下级时，触发无限级分佣的跳过/停止逻辑。</span>
                        </div>
                    </div>
                </div>
                <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:6px;padding:10px 14px;margin-top:8px;font-size:12px;color:#0c4a6e;line-height:1.8;">
                    <b style="color:#0369a1;">💡 这里 vs 会员等级里的分销设置</b><br>
                    <b>这里是"全局总控"</b>：决定每笔订单拿多少钱出来参与分销。<b>会员等级里的"分销佣金设置"是"分法"</b>：决定上级从中拿走多少。<br>
                    <a href="./member.php" style="color:#2563eb;font-weight:500;">前往「会员等级」设置每个等级的分销佣金 →</a>
                </div>
            </div>

            <div class="ucfg-section">
                <div class="ucfg-title"><i class="ri-money-cny-box-line"></i> 提现与充值</div>
                <div class="ucfg-sub"><i class="ri-bank-card-line" style="color:#6b7280;font-size:13px;"></i> 提现</div>
                <div class="ucfg-row">
                    <label>提现功能总开关</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="withdraw_switch" value="1" lay-skin="switch" lay-text="开启|关闭" <?= intval($levelSettings[Level_Service::OPT_WITHDRAW_SWITCH]) === 1 ? 'checked' : '' ?>>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">开启 = 允许提现到银行卡、微信等。关闭 = 隐藏提现功能。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail"><b>开启</b> = 用户可以把账户里赚到的佣金、余额提现到银行卡、微信等。<br><b>关闭</b> = 完全隐藏提现功能，用户只能用余额在站内消费，不能提现。</span>
                        </div>
                    </div>
                </div>
                <?php
                $withdrawMethodMap = Level_Service::getWithdrawMethodMap();
                $enabledWithdrawMethods = Level_Service::getWithdrawMethods($levelSettings[Level_Service::OPT_WITHDRAW_METHODS] ?? 'alipay,wechat,qq,bank');
                ?>
                <div class="ucfg-row">
                    <label>提现方式</label>
                    <div>
                        <div style="display:flex;gap:18px;flex-wrap:wrap;padding:6px 0;">
                            <?php foreach ($withdrawMethodMap as $methodKey => $methodName): ?>
                                <input type="checkbox" name="withdraw_method_<?= htmlspecialchars($methodKey) ?>" value="<?= htmlspecialchars($methodKey) ?>" title="<?= htmlspecialchars($methodName) ?>" lay-skin="primary" <?= in_array($methodKey, $enabledWithdrawMethods, true) ? 'checked' : '' ?>>
                            <?php endforeach; ?>
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">勾选后，用户在提现页面只能选择已启用的方式。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail"><b>提现方式</b> = 控制前台提现表单展示的收款方式，并会在提交提现时二次校验。<br>例如只勾选「支付宝、微信」，用户就不能选择 QQ 或银行卡提现。请至少保留一种方式。</span>
                        </div>
                    </div>
                </div>
                <div class="ucfg-row">
                    <label>提现手续费</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="number" name="withdraw_fee_rate" class="layui-input" min="0" max="100" step="0.01" value="<?= (float)$levelSettings[Level_Service::OPT_WITHDRAW_FEE] ?>">
                        </div>
                        <div class="ucfg-tip">
                            <span class="ucfg-tip-summary">单位 %。提现 100 元扣 10% = 实际到账 90 元。填 0 = 不收手续费。<span class="ucfg-tip-toggle">详细说明 ▾</span></span>
                            <span class="ucfg-tip-detail">平台从每笔提现中收取的手续费比例。<br>例如填 10：用户提现 100 元，扣掉 10% 手续费（10 元），实际到账 <b>90 元</b>。填 0 = 不收手续费。</span>
                        </div>
                    </div>
                </div>
                <div class="ucfg-row">
                    <label>最低提现金额</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="number" name="withdraw_min_amount" class="layui-input" min="0" step="0.01" value="<?= (float)$levelSettings[Level_Service::OPT_WITHDRAW_MIN] ?>">
                        </div>
                        <div class="ucfg-tip">单位：元。余额低于此金额不能提现。填 0 = 不限制。</div>
                    </div>
                </div>
                <div class="ucfg-sub"><i class="ri-wallet-3-line" style="color:#6b7280;font-size:13px;"></i> 充值</div>
                <div class="ucfg-row">
                    <label>最小充值金额</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="number" name="balance_recharge_min" class="layui-input" min="0.01" step="0.01" value="<?= (float)$levelSettings[Level_Service::OPT_RECHARGE_MIN] ?>">
                        </div>
                        <div class="ucfg-tip">单位：元。单笔最少充值金额。防止频繁充几分钱。</div>
                    </div>
                </div>
                <div class="ucfg-row">
                    <label>最大充值金额</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="number" name="balance_recharge_max" class="layui-input" min="1" step="0.01" value="<?= (float)$levelSettings[Level_Service::OPT_RECHARGE_MAX] ?>">
                        </div>
                        <div class="ucfg-tip">单位：元。单笔最大充值金额。防止误操作或异常大额充值。</div>
                    </div>
                </div>
            </div>

            <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
            <div class="layui-form-item" style="text-align:center;margin-top:10px;">
                <button type="submit" class="layui-btn" lay-submit lay-filter="user-config-save"><i class="ri-save-line"></i> 保存设置</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </form>
    </div>
</div>

<div style="height: 96px;"></div>

<script>
layui.use(['form'], function(){
    var form = layui.form;
    form.on('submit(user-config-save)', function(data){
        $.ajax({
            type: 'POST',
            url: 'shop.php?action=user_config_save',
            data: data.field,
            dataType: 'json',
            success: function(e){
                if (e.code == 400 || e.code == 1) { layer.msg(e.msg || '保存失败', { icon: 2 }); return; }
                layer.msg('用户设置已保存', { icon: 1 });
            },
            error: function(){
                layer.msg('网络错误', { icon: 2 });
            }
        });
        return false;
    });

    // 无限级开关 → 控制子选项显隐
    form.on('switch()', function(data){
        if (data.elem.name === 'level_infinite_division') {
            $('#infinite-skip-row')[data.elem.checked ? 'slideDown' : 'slideUp'](200);
        }
    });

    // tip 折叠详细说明
    $(document).on('click', '.ucfg-tip-toggle', function(){
        var $tip = $(this).closest('.ucfg-tip');
        var $summary = $tip.find('.ucfg-tip-summary');
        var $detail = $tip.find('.ucfg-tip-detail');
        var isOpen = $detail.is(':visible');
        if (isOpen) {
            $detail.slideUp(150);
            $summary.show();
            $(this).text('详细说明 ▾');
        } else {
            $summary.hide();
            $detail.slideDown(150);
            var $innerToggle = $detail.find('.ucfg-tip-toggle');
            if (!$innerToggle.length) {
                $detail.append(' <span class="ucfg-tip-toggle">收起 ▴</span>');
            } else {
                $innerToggle.text('收起 ▴');
            }
        }
    });

    // hash 滚动 + 闪烁高亮
    var hash = location.hash.replace('#', '');
    if (hash) {
        var $el = document.getElementById(hash);
        if ($el) {
            var $section = $($el).closest('.ucfg-section');
            setTimeout(function(){
                $el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                $section.css({ transition: 'box-shadow 0.3s, border-color 0.3s', boxShadow: '0 0 0 3px rgba(37,99,235,0.3)', borderColor: '#2563eb' });
                setTimeout(function(){
                    $section.css({ boxShadow: '', borderColor: '' });
                }, 2000);
            }, 300);
        }
    }
});
</script>
