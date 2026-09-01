<?php defined('DC_ROOT') || exit('access denied!'); ?>
<style>
    .lv-set-section { background: #fff; border: 1px solid #eef1f4; border-radius: 8px; padding: 18px 20px; margin-bottom: 14px; }
    .lv-set-title { font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; }
    .lv-set-title i { color: #2563eb; }
    .lv-row { display: grid; grid-template-columns: 160px 1fr; gap: 10px; align-items: center; padding: 8px 0; }
    .lv-row > label { color: #374151; font-weight: 500; }
    .lv-row .layui-input-inline { width: 260px; }
    .lv-tip { color: #6b7280; font-size: 12px; line-height: 1.7; }
    .lv-tip b { color: #2563eb; }
    .lv-hint {
        background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a;
        padding: 10px 14px; border-radius: 8px; font-size: 13px; line-height: 1.7; margin-bottom: 14px;
    }
</style>

<div class="layui-tabs order-tabs-wrapper" style="display:flex;align-items:center;justify-content:space-between;" lay-options="{trigger: false}">
    <ul class="layui-tabs-header">
        <li><a href="./setting.php">系统配置</a></li>
        <li><a href="./setting.php?action=user">用户设置</a></li>
        <li class="layui-this"><a href="./setting.php?action=level">等级权限</a></li>
        <li><a href="./setting.php?action=seo">SEO设置</a></li>
        <li><a href="./setting.php?action=mail">邮箱配置</a></li>
    </ul>
</div>

<div class="layui-card" style="border-radius:8px;overflow:hidden;">
    <div class="layui-card-header" style="display:flex;align-items:center;justify-content:center;position:relative;height:auto;padding:12px 15px;">
        <span style="position:absolute;left:15px;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">等级权限</span>
    </div>
    <div class="layui-card-body" style="padding:20px;">
        <div class="lv-hint">
            <b>提示：</b>所有门槛项的值为<a href="<?= DC_URL ?>admin/member.php" target="_blank">会员等级</a>的 ID；设为 <b>0</b> 表示不限制。
            升级奖励、分销分成配置保存后即时生效。
        </div>

        <form class="layui-form" method="post" id="lv-set-form">

            <!-- 新用户默认等级 -->
            <div class="lv-set-section">
                <div class="lv-set-title"><i class="ri-user-add-line"></i> 新用户默认</div>
                <?php $defaultGradeValue = intval($levelSettings[Level_Service::OPT_DEFAULT_GRADE]); ?>
                <?php if ($defaultGradeValue <= 0 && !empty($memberLevels[0]['id'])) $defaultGradeValue = (int)$memberLevels[0]['id']; ?>
                <div class="lv-row">
                    <label>新注册默认等级</label>
                    <div>
                        <div class="layui-input-inline">
                            <select name="level_default_grade">
                                <?php foreach ($memberLevels as $lv): ?>
                                    <option value="<?= (int)$lv['id'] ?>" <?= $defaultGradeValue === (int)$lv['id'] ? 'selected' : '' ?>><?= htmlspecialchars($lv['name']) ?> (#<?= (int)$lv['id'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="lv-tip">新用户注册时会自动分配到这里选择的会员等级；建议默认指定为“普通用户”会员等级。</div>
                    </div>
                </div>
            </div>

            <!-- 功能门槛 -->
            <div class="lv-set-section">
                <div class="lv-set-title"><i class="ri-shield-keyhole-line"></i> 功能门槛（等级 ≥ 设定值才可使用）</div>

                <?php
                $gateFields = [
                    'level_deposit_grade'         => ['label' => '使用提现功能', 'tip' => '用户需要达到该等级才能把余额提现到自己的账户。分店用户的提现权限由分店等级控制。'],
                ];
                foreach ($gateFields as $key => $meta):
                    $current = intval($levelSettings[$key] ?? 0);
                ?>
                <div class="lv-row">
                    <label><?= htmlspecialchars($meta['label']) ?></label>
                    <div>
                        <div class="layui-input-inline">
                            <select name="<?= $key ?>">
                                <option value="0" <?= $current === 0 ? 'selected' : '' ?>>不限制</option>
                                <?php foreach ($memberLevels as $lv): ?>
                                    <option value="<?= (int)$lv['id'] ?>" <?= $current === (int)$lv['id'] ? 'selected' : '' ?>><?= htmlspecialchars($lv['name']) ?> (#<?= (int)$lv['id'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="lv-tip"><?= htmlspecialchars($meta['tip']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- 分销分成 -->
            <div class="lv-set-section">
                <div class="lv-set-title"><i class="ri-hand-coin-line"></i> 分销分成</div>
                <div class="lv-row">
                    <label>升级奖励百分比</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="number" name="level_upgrade_profit" class="layui-input" min="0" max="100" step="0.01" value="<?= (float)$levelSettings[Level_Service::OPT_UPGRADE_PROFIT] ?>">
                        </div>
                        <div class="lv-tip">单位 %，范围 0-100。<b>0</b> = 关闭；下级升级时，上级可得 <b>升级基础价 × 该比例</b> 奖励。</div>
                    </div>
                </div>
                <div class="lv-row">
                    <label>升级奖励等级门槛</label>
                    <div>
                        <div class="layui-input-inline" style="width:220px;">
                            <select name="upgrade_level_check" class="layui-select">
                                <?php $ulc = (int)$levelSettings[Level_Service::OPT_UPGRADE_LEVEL_CHECK]; ?>
                                <option value="1" <?= $ulc === 1 ? 'selected' : '' ?>>要求上级等级 ≥ 目标等级</option>
                                <option value="0" <?= $ulc === 0 ? 'selected' : '' ?>>不检查（任何上级都能拿）</option>
                                <option value="2" <?= $ulc === 2 ? 'selected' : '' ?>>站长豁免（站长不限，其他仍检查）</option>
                            </select>
                        </div>
                        <div class="lv-tip">控制上级是否需要高于下级新等级才能拿到升级奖励。<b>不检查</b>：所有上级都能拿；<b>站长豁免</b>：站长不受等级限制，普通推荐关系仍需等级达标。</div>
                    </div>
                </div>
                <div class="lv-row">
                    <label>分店订单分成模式</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="level_commits_distribute" value="1" lay-skin="switch" lay-text="上级优先|站长优先" <?= intval($levelSettings[Level_Service::OPT_COMMITS_DIST]) === 1 ? 'checked' : '' ?>>
                        </div>
                        <div class="lv-tip">
                            <b>站长优先</b>（默认）：分店订单佣金给站长（差价），不走等级链。<br>
                            <b>上级优先</b>：分店订单佣金走等级链给买家的上级，不给站长差价。无上级时自动回落给站长。<br>
                            主站订单不受此开关影响。
                        </div>
                    </div>
                </div>
                <div class="lv-row">
                    <label>无限级分成</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="level_infinite_division" value="1" lay-skin="switch" lay-text="启用|关闭" <?= intval($levelSettings[Level_Service::OPT_INFINITE_DIV]) === 1 ? 'checked' : '' ?>>
                        </div>
                        <div class="lv-tip">开启后：当上级等级不足以分成时，自动跳过并继续向更上级传递；否则分成链终止。</div>
                    </div>
                </div>
            </div>

            <!-- 分店功能 -->
            <div class="lv-set-section">
                <div class="lv-set-title"><i class="ri-store-2-line"></i> 分店功能</div>
                <div class="lv-row">
                    <label>分店功能总开关</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="station_switch" value="1" lay-skin="switch" lay-text="开启|关闭" <?= intval($levelSettings[Level_Service::OPT_STATION_SWITCH]) === 1 ? 'checked' : '' ?>>
                        </div>
                        <div class="lv-tip">开启后，用户可以开通自己的分店店铺来卖货赚钱。关闭后，所有用户都无法开通分店。</div>
                    </div>
                </div>
            </div>

            <!-- 提现设置 -->
            <div class="lv-set-section">
                <div class="lv-set-title"><i class="ri-money-cny-box-line"></i> 提现设置</div>
                <div class="lv-row">
                    <label>提现功能总开关</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="withdraw_switch" value="1" lay-skin="switch" lay-text="开启|关闭" <?= intval($levelSettings[Level_Service::OPT_WITHDRAW_SWITCH]) === 1 ? 'checked' : '' ?>>
                        </div>
                        <div class="lv-tip">开启后，用户可以把账户余额提现到自己的银行卡、微信等账户。关闭后，所有用户都无法提现。</div>
                    </div>
                </div>
                <div class="lv-row">
                    <label>提现手续费</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="number" name="withdraw_fee_rate" class="layui-input" min="0" max="100" step="0.01" value="<?= (float)$levelSettings[Level_Service::OPT_WITHDRAW_FEE] ?>">
                        </div>
                        <div class="lv-tip">单位 %。例如填 10，用户申请提现 100 元时，用户余额会先扣 100 元，手续费 10 元，实际到账 90 元。填 0 表示不收手续费。</div>
                    </div>
                </div>
                <div class="lv-row">
                    <label>最低提现金额</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="number" name="withdraw_min_amount" class="layui-input" min="0" step="0.01" value="<?= (float)$levelSettings[Level_Service::OPT_WITHDRAW_MIN] ?>">
                        </div>
                        <div class="lv-tip">单位：元。用户每次提现至少需要达到这个金额才能提交申请。填 0 表示不限制。</div>
                    </div>
                </div>
            </div>

            <!-- 余额充值 -->
            <div class="lv-set-section">
                <div class="lv-set-title"><i class="ri-wallet-3-line"></i> 余额充值</div>
                <div class="lv-row">
                    <label>最小充值金额</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="number" name="balance_recharge_min" class="layui-input" min="0.01" step="0.01" value="<?= (float)$levelSettings[Level_Service::OPT_RECHARGE_MIN] ?>">
                        </div>
                        <div class="lv-tip">单位：元。用户在线充值余额时，单笔最少充多少钱。</div>
                    </div>
                </div>
                <div class="lv-row">
                    <label>最大充值金额</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="number" name="balance_recharge_max" class="layui-input" min="1" step="0.01" value="<?= (float)$levelSettings[Level_Service::OPT_RECHARGE_MAX] ?>">
                        </div>
                        <div class="lv-tip">单位：元。用户在线充值余额时，单笔最多充多少钱。</div>
                    </div>
                </div>
            </div>

            <!-- 注册设置 -->
            <div class="lv-set-section">
                <div class="lv-set-title"><i class="ri-user-add-line"></i> 注册设置</div>
                <div class="lv-row">
                    <label>强制邀请注册</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="invite_register_only" value="1" lay-skin="switch" lay-text="开启|关闭" <?= intval($levelSettings[Level_Service::OPT_INVITE_ONLY]) === 1 ? 'checked' : '' ?>>
                        </div>
                        <div class="lv-tip">开启后，新用户<b>必须</b>通过老用户分享的邀请链接才能注册，不能自己直接注册。关闭后，任何人都可以自由注册。</div>
                    </div>
                </div>
            </div>

            <input name="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
            <div class="layui-form-item" style="text-align:center;margin-top:10px;">
                <button type="submit" class="layui-btn" lay-submit lay-filter="lv-save"><i class="ri-save-line"></i> 保存设置</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </form>
    </div>
</div>

<script>
layui.use(['form'], function(){
    var form = layui.form;
    form.on('submit(lv-save)', function(data){
        $.ajax({
            type: 'POST', url: 'setting.php?action=level_save', data: data.field, dataType: 'json',
            success: function(e){
                if (e.code == 400 || e.code == 1) { layer.msg(e.msg || '保存失败', { icon: 2 }); return; }
                layer.msg('等级设置已保存', { icon: 1 });
            },
            error: function(){ layer.msg('网络错误', { icon: 2 }); }
        });
        return false;
    });
});
</script>
