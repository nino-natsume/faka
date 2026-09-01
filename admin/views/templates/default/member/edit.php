<?php defined('DC_ROOT') || exit('access denied!'); $memberIcon = !empty($info['icon']) ? $info['icon'] : 'ri-vip-diamond-line'; $memberIconImage = !empty($info['icon_image']) ? $info['icon_image'] : ''; ?>
<style>
    body{ overflow: hidden; background: #fff; }
    #open-box { background: #f8fafc; }
    .ml-tip {
        background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a;
        padding: 14px 16px; border-radius: 10px; font-size: 13px; line-height: 1.8; margin-bottom: 18px;
    }
    .ml-guide-title { font-size: 14px; font-weight: 600; margin-bottom: 6px; }
    .ml-guide-text { margin-top: 2px; color: #1e3a8a; }
    .ml-guide-box {
        margin-top: 10px; padding: 10px 12px; background: #fff; border: 1px solid #dbeafe;
        border-radius: 8px; line-height: 1.9;
    }
    .ml-rule-map {
        display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin: 0 0 14px;
    }
    .ml-rule-card {
        padding: 12px 14px; border-radius: 12px; border: 1px solid #dbeafe; background: #f8fbff;
    }
    .ml-rule-card.is-warn {
        border-color: #fde68a; background: #fffbeb;
    }
    .ml-rule-card-title {
        font-size: 13px; font-weight: 600; color: #1e3a8a; margin-bottom: 4px;
    }
    .ml-rule-card-desc {
        font-size: 12px; color: #475569; line-height: 1.8;
    }
    .ml-section-title {
        margin: 20px 0 8px; padding-left: 10px; border-left: 3px solid #2563eb;
        font-size: 14px; font-weight: 600; color: #111827;
    }
    .ml-section-desc { margin: 0 0 12px; font-size: 12px; color: #64748b; line-height: 1.8; }
    .ml-note {
        margin: -2px 0 14px; padding: 10px 12px; background: #f8fafc; border: 1px solid #e2e8f0;
        border-radius: 8px; font-size: 12px; color: #475569; line-height: 1.8;
    }
    .ml-section-card {
        background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 18px 18px 4px;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04); margin-bottom: 16px;
    }
    .ml-section-card .layui-form-item {
        margin-bottom: 14px; padding: 14px 14px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;
    }
    .ml-section-card .layui-form-label {
        float: none; display: block; width: auto; padding: 0; margin-bottom: 8px; text-align: left;
        font-size: 14px; font-weight: 600; color: #111827; line-height: 1.6;
    }
    .ml-section-card .layui-input-block { margin-left: 0; }
    .ml-field-tag {
        display: inline-block; margin-bottom: 8px; padding: 3px 8px; border-radius: 999px;
        background: #dbeafe; color: #1d4ed8; font-size: 12px; line-height: 1.6;
    }
    .ml-field-desc {
        margin: -4px 0 10px; font-size: 12px; color: #64748b; line-height: 1.8;
    }
    .ml-section-card .layui-input,
    .ml-section-card .layui-select-title input,
    .ml-section-card .layui-textarea {
        border-radius: 10px; min-height: 42px;
    }
    .ml-section-card .layui-textarea { min-height: 96px; }
    .ml-section-card .layui-form-switch { margin-top: 4px; }
    .ml-section-card .form-tips {
        display: block; margin-top: 8px; padding: 8px 10px; background: #fff; border-radius: 8px; color: #475569; line-height: 1.8;
    }
    #form-btn {
        background: #fff; border-top: 1px solid #e5e7eb; padding: 14px 25px 0; margin-top: 16px;
    }
    #form-btn .layui-input-block {
        margin-left: 0 !important; display: flex; align-items: center; justify-content: center;
        gap: 10px; flex-wrap: wrap;
    }
    #form-btn .layui-btn { min-width: 120px; }
    .ml-icon-picker { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
    .ml-icon-choice { width: 38px; height: 38px; border: 1px solid #dbeafe; border-radius: 10px; background: #fff; color: #2563eb; font-size: 20px; cursor: pointer; }
    .ml-icon-choice.is-active { border-color: #2563eb; background: #eff6ff; box-shadow: 0 0 0 2px rgba(37,99,235,.08); }
    .ml-icon-image-row { display: flex; gap: 8px; }
    .ml-icon-preview { width: 54px; height: 54px; margin-top: 10px; border-radius: 16px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 28px; overflow: hidden; }
    .ml-icon-preview img { width: 100%; height: 100%; object-fit: cover; }

    @media screen and (max-width: 768px) {
        .ml-rule-map { grid-template-columns: 1fr; }
        .ml-section-card { padding: 16px 14px 2px; border-radius: 12px; }
        .ml-section-card .layui-form-item { padding: 12px; }
        #form-btn { padding: 12px 0 0; margin-top: 14px; }
        #form-btn .layui-btn { flex: 1; }
    }
</style>

<form class="layui-form" action="?action=edit_ajax" id="form">
    <div style="padding: 25px;" id="open-box">
        <div class="ml-tip">
            <div class="ml-guide-title">📝 每个等级 = 一个"会员套餐"</div>
            <div class="ml-guide-text">设好价格和加价比例，大多数商品会自动按规则卖。不做分销、不做限时会员的话，第 3、4 块保持默认就行。</div>
        </div>
        <div class="ml-section-card">
        <div class="ml-section-title">1. 等级名称与图标</div>
        <div class="ml-section-desc">用户在前台看到的等级名字和图标，图标可用 Remix Icon 或上传图片。</div>
        <div class="layui-form-item">
            <label class="layui-form-label">等级名称</label>
            <div class="layui-input-block">
                <div class="ml-field-tag">必填</div>
                <input type="text" name="name" class="layui-input" value="<?= htmlspecialchars($info['name']) ?>" placeholder="例如：普通用户、银牌会员、金牌代理" lay-verify="required">
                <span class="form-tips">用户在前台能看到这个名字，用简单易懂的中文就行。</span>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">Remix Icon 图标</label>
            <div class="layui-input-block">
                <div class="ml-field-tag">默认图标</div>
                <div class="ml-icon-picker">
                    <?php foreach (['ri-user-smile-line','ri-vip-crown-line','ri-vip-diamond-line','ri-medal-line','ri-award-line','ri-shield-star-line','ri-team-line','ri-star-smile-line'] as $_icon): ?>
                    <button type="button" class="ml-icon-choice<?= $_icon === $memberIcon ? ' is-active' : '' ?>" data-icon="<?= $_icon ?>"><i class="<?= $_icon ?>"></i></button>
                    <?php endforeach; ?>
                </div>
                <input type="text" name="icon" id="ml-icon" class="layui-input" value="<?= htmlspecialchars($memberIcon) ?>" placeholder="例如：ri-vip-diamond-line">
                <span class="form-tips">填写 Remix Icon 类名即可。未上传图片时，前台显示这里设置的图标。</span>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">图片图标</label>
            <div class="layui-input-block">
                <div class="ml-field-tag">可选，优先显示</div>
                <div class="ml-icon-image-row">
                    <input type="text" name="icon_image" id="ml-icon-image" class="layui-input" value="<?= htmlspecialchars($memberIconImage) ?>" placeholder="上传后自动填入图片地址">
                    <button type="button" class="layui-btn" id="ml-icon-upload">上传图片</button>
                    <button type="button" class="layui-btn layui-btn-primary" id="ml-icon-clear">清除</button>
                </div>
                <div class="ml-icon-preview" id="ml-icon-preview"><?= $memberIconImage ? '<img src="' . htmlspecialchars($memberIconImage) . '" alt="">' : '<i class="' . htmlspecialchars($memberIcon) . '"></i>' ?></div>
                <span class="form-tips">如果上传了图片，前台会优先显示图片；留空则显示 Remix Icon。</span>
            </div>
        </div>
        </div>
        <div class="ml-section-card">
        <div class="ml-section-title">2. 价格与加价规则</div>
        <div class="ml-section-desc">设好这里后，你的大部分商品会自动按这个规则来卖，省得一个个改。</div>
        <div class="ml-rule-map">
            <div class="ml-rule-card">
                <div class="ml-rule-card-title">① 你在这里设好加价比例</div>
                <div class="ml-rule-card-desc">比如填 50%，意思是成本价的基础上再加 50% 卖给用户。</div>
            </div>
            <div class="ml-rule-card">
                <div class="ml-rule-card-title">② 大部分商品自动按这个卖</div>
                <div class="ml-rule-card-desc">你在商品页只需填成本价，系统会自动算出这个等级的售价。</div>
            </div>
            <div class="ml-rule-card is-warn">
                <div class="ml-rule-card-title">③ 个别商品可以单独改价</div>
                <div class="ml-rule-card-desc">某个商品想卖不一样的价？直接去商品页单独设，优先用商品自己的价。</div>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">开通/升级价格</label>
            <div class="layui-input-block">
                <div class="ml-field-tag">这个等级卖多少钱</div>
                <input type="number" name="price" class="layui-input" value="<?= number_format((float)$info['price'], 2, '.', '') ?>" min="0" step="0.01" placeholder="0.00">
                <span class="form-tips">单位：元。填 99 = 用户花 99 元开通。填 0 = 免费等级。</span>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">默认加价比例</label>
            <div class="layui-input-block">
                <div class="ml-field-tag">核心设置</div>
                <input type="number" name="markup_ratio" class="layui-input" value="<?= (float)$info['markup_ratio'] ?>" min="0" step="0.01" placeholder="0">
                <span class="form-tips">单位：%。成本 10 元 → 填 50 = 卖 15 元，填 100 = 卖 20 元，填 0 = 按成本价卖。</span>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">按成本自动调节规则</label>
            <div class="layui-input-block">
                <div class="ml-field-tag">新手可跳过</div>
                <select name="profit_rule_id">
                    <option value="0">不使用（直接用上面的固定加价比例）</option>
                    <?php foreach (($profitRules ?? []) as $r): ?>
                        <option value="<?= (int)$r['id'] ?>" <?= (int)($info['profit_rule_id'] ?? 0) === (int)$r['id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['name']) ?> (#<?= (int)$r['id'] ?>)</option>
                    <?php endforeach; ?>
                </select>
                <span class="form-tips">根据成本高低自动微调加价幅度，不懂就选“不使用”。<a href="<?= DC_URL ?>admin/price_rule.php?tab=profit" target="_blank">管理规则 →</a></span>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">积分兑换倍数</label>
            <div class="layui-input-block">
                <div class="ml-field-tag">没有积分功能可跳过</div>
                <input type="number" name="exchange_ratio" class="layui-input" value="<?= (float)$info['exchange_ratio'] ?>" min="0" step="0.01" placeholder="0">
                <span class="form-tips">成本价 × 倍数 = 所需积分。举例：成本 10 元，填 100 = 需要 1000 积分兑换。</span>
            </div>
        </div>
        </div>
        <div class="ml-section-card">
        <div class="ml-section-title">3. 分销佣金设置</div>
        <div class="ml-section-desc">不做分销？这一块全部保持默认就行，不用改。做分销的话，认真看下面的说明。</div>
        <div class="layui-form-item">
            <label class="layui-form-label">直属上级先拿比例</label>
            <div class="layui-input-block">
                <div class="ml-field-tag">不做分销填 0</div>
                <input type="number" name="actual_profit" class="layui-input" value="<?= (float)$info['actual_profit'] ?>" min="0" max="100" step="0.01" placeholder="0">
                <span class="form-tips">
                    单位：%。这里只管「商品订单佣金」，与「升级奖励」无关。<br>
                    填 0 = <b>不分佣</b>，上级拿不到任何商品订单佣金 ← 不做分销选这个<br>
                    填 100 = 直推上级拿走全部佣金，不再往上分（一级分销）<br>
                    填 60 = 直推上级先拿 60%，剩下 40% 继续往更上级分（多级分销）
                </span>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">停止往上分的门槛</label>
            <div class="layui-input-block">
                <div class="ml-field-tag">不懂就填 0</div>
                <input type="number" name="profit_threshold" class="layui-input" value="<?= (float)$info['profit_threshold'] ?>" min="0" max="100" step="0.01" placeholder="0">
                <span class="form-tips">
                    单位：%。举例：一笔订单金额 100 元，这里填 5 →<br>
                    如果某一层分到的钱不超过 100×5% = 5 元，系统就停止，不再继续往上分了。<br>
                    填 0 = 不设门槛，只要还有钱就继续往上分。
                </span>
            </div>
        </div>
        <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:6px;padding:10px 14px;margin:8px 0;font-size:12px;color:#0c4a6e;line-height:1.8;">
            <b style="color:#0369a1;">💡 这里和「用户配置 → 分销分成」有什么区别？</b><br>
            <b>「用户配置 → 分销分成」是全局总控</b>：决定每笔订单拿多少钱出来参与分销（蛋糕有多大）。<br>
            <b>这里是"分法"</b>：决定上级从蛋糕里拿走多少、剩下的继续往上分多少。<br>
            流程：订单完成 → 全局设置算出分佣总额（蛋糕大小）→ 这里的比例决定怎么逐级分 → 分完或到上限就停。<br>
            <a href="./setting.php?action=user#commission-section" target="_blank" style="color:#2563eb;font-weight:500;">前往「用户配置 → 分销分成」设置全局分佣规则 →</a>
        </div>
        </div>
        <div class="ml-section-card">
        <div class="ml-section-title">4. 有效期设置</div>
        <div class="ml-section-desc">只在你需要“限时会员 / 续费会员”时设置；不做时效会员时保持默认即可。</div>
        <div class="layui-form-item">
            <label class="layui-form-label">有效期天数</label>
            <div class="layui-input-block">
                <div class="ml-field-tag">限时会员</div>
                <input type="number" name="duration_days" class="layui-input" value="<?= (int)$info['duration_days'] ?>" min="0" step="1" placeholder="0">
                <span class="form-tips">单位：天。填 0 = 跟随全局默认值；填 30 = 本等级单独有效 30 天。</span>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">同等级续费收费比例</label>
            <div class="layui-input-block">
                <div class="ml-field-tag">续费折扣</div>
                <input type="number" name="renew_ratio" class="layui-input" value="<?= (float)$info['renew_ratio'] ?>" min="0" max="100" step="0.01" placeholder="0">
                <span class="form-tips">单位：%。填 0 = 跟随全局默认值；填 100 = 原价续费；填 50 = 半价续费。</span>
            </div>
        </div>
        </div>
        <div class="ml-section-card">
        <div class="ml-section-title">5. 自动升级条件</div>
        <div class="ml-section-desc">除了付费开通外，用户满足以下条件也可自动升级到本等级。全部填 0 = 只能付费升级。</div>
        <div class="layui-form-item">
            <label class="layui-form-label">判断模式</label>
            <div class="layui-input-block">
                <div class="ml-field-tag">多条件时的逻辑</div>
                <select name="upgrade_mode">
                    <option value="any" <?= ($info['upgrade_mode'] ?? 'any') === 'any' ? 'selected' : '' ?>>满足任一条件即可升级（OR）</option>
                    <option value="all" <?= ($info['upgrade_mode'] ?? 'any') === 'all' ? 'selected' : '' ?>>必须全部满足才能升级（AND）</option>
                </select>
                <span class="form-tips">
                    如果下面只开了一个条件，选哪个都一样。<br>
                    开了多个条件时：选「任一」= 达到其中一个就自动升级；选「全部」= 每个都要达标才升级。
                </span>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">直推粉丝数</label>
            <div class="layui-input-block">
                <div class="ml-field-tag">直接邀请的下级</div>
                <input type="number" name="upgrade_direct_count" class="layui-input" value="<?= (int)($info['upgrade_direct_count'] ?? 0) ?>" min="0" step="1" placeholder="0">
                <span class="form-tips">填 0 = 不启用此条件。填 10 = 用户直接邀请了 10 个人后自动升级。只统计直接推荐的一级下线。</span>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">自身累计消费</label>
            <div class="layui-input-block">
                <div class="ml-field-tag">用户自己花的钱</div>
                <input type="number" name="upgrade_consume_amount" class="layui-input" value="<?= number_format((float)($info['upgrade_consume_amount'] ?? 0), 2, '.', '') ?>" min="0" step="0.01" placeholder="0.00">
                <span class="form-tips">单位：元。填 0 = 不启用此条件。填 500 = 用户累计消费满 500 元后自动升级。统计已付款的商品订单总额。</span>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">团队总人数</label>
            <div class="layui-input-block">
                <div class="ml-field-tag">直推 + 间接下级</div>
                <input type="number" name="upgrade_team_count" class="layui-input" value="<?= (int)($info['upgrade_team_count'] ?? 0) ?>" min="0" step="1" placeholder="0">
                <span class="form-tips">填 0 = 不启用此条件。填 50 = 用户的整个团队（直推 + 所有间接下级）达到 50 人后自动升级。</span>
            </div>
        </div>
        </div>
        <div class="ml-section-card">
        <div class="ml-section-title">6. 等级说明</div>
        <div class="ml-section-desc">写一句话告诉用户这个等级有啥好处。</div>
        <input type="hidden" name="state" id="ml-state" value="<?= (int)$info['state'] ?>">
        <div class="layui-form-item">
            <label class="layui-form-label">等级说明</label>
            <div class="layui-input-block">
                <div class="ml-field-tag">给用户看的介绍</div>
                <textarea name="content" class="layui-textarea" placeholder="例如：开通后享受全站最低价，适合长期批发的用户"><?= htmlspecialchars($info['content']) ?></textarea>
                <span class="form-tips">写 1-2 句话，说清楚这个等级有啥好处，让用户一看就想升级。</span>
            </div>
        </div>
        </div>
        <input type="hidden" name="id" value="<?= (int)$info['id'] ?>"/>
        <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
        <div id="form-btn">
            <div class="layui-input-block">
            <button type="submit" class="layui-btn" lay-submit lay-filter="submit">保存修改</button>
            <button type="reset" class="layui-btn layui-btn-primary">恢复当前内容</button>
            </div>
        </div>
    </div>
</form>

<script>
layui.use(['form','upload'], function(){
    var $ = layui.$;
    var form = layui.form;
    var upload = layui.upload;

    function refreshIconPreview(){
        var icon = $('#ml-icon').val() || 'ri-vip-diamond-line';
        var img = $('#ml-icon-image').val();
        $('.ml-icon-choice').removeClass('is-active').filter('[data-icon="' + icon + '"]').addClass('is-active');
        $('#ml-icon-preview').html(img ? '<img src="' + img + '" alt="">' : '<i class="' + icon + '"></i>');
    }

    $('.ml-icon-choice').on('click', function(){
        $('#ml-icon').val($(this).data('icon'));
        refreshIconPreview();
    });
    $('#ml-icon,#ml-icon-image').on('input', refreshIconPreview);
    $('#ml-icon-clear').on('click', function(){
        $('#ml-icon-image').val('');
        refreshIconPreview();
    });
    upload.render({
        elem: '#ml-icon-upload',
        field: 'image',
        url: '?action=upload_icon',
        data: { token: function(){ return $('#token').val(); } },
        done: function(res){
            if (res.code > 0) return layer.msg(res.msg || '上传失败');
            $('#ml-icon-image').val(res.data || '');
            refreshIconPreview();
        }
    });
    refreshIconPreview();

    form.on('submit(submit)', function(data){
        var field = data.field;
        field.state = $('#ml-state').val();
        var url = $('#form').attr('action');
        $.ajax({
            type: 'POST', url: url, data: field, dataType: 'json',
            success: function(e){
                if (e.code == 400) return layer.msg(e.msg);
                parent.layer.close('edit');
                parent.layer.msg('编辑成功');
                window.parent.table.reload();
            },
            error: function(xhr){
                layer.msg(JSON.parse(xhr.responseText).msg);
            }
        });
        return false;
    });

    form.render();
});

var maxHeight = $(window.parent).innerHeight() * 0.75;
$("#open-box").css({ "max-height": maxHeight + "px", "overflow-y": "auto" });
</script>
