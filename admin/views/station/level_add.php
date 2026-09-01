<?php defined('DC_ROOT') || exit('access denied!'); ?>
<style>
    body { overflow: hidden; background: #fff; }
    #open-box { background: #f8fafc; }
    .sl-tip {
        background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a;
        padding: 14px 16px; border-radius: 10px; font-size: 13px; line-height: 1.8; margin-bottom: 18px;
    }
    .sl-guide-title { font-size: 14px; font-weight: 600; margin-bottom: 6px; }
    .sl-guide-text { margin-top: 2px; color: #1e3a8a; }
    .sl-section-card {
        background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 18px 18px 4px;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04); margin-bottom: 16px;
    }
    .sl-section-title {
        margin: 0 0 4px; padding-left: 10px; border-left: 3px solid #2563eb;
        font-size: 14px; font-weight: 600; color: #111827;
    }
    .sl-section-desc { margin: 0 0 12px; font-size: 12px; color: #64748b; line-height: 1.8; }
    .sl-section-card .layui-form-item {
        margin-bottom: 14px; padding: 14px 14px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;
    }
    .sl-section-card .layui-form-label {
        float: none; display: block; width: auto; padding: 0; margin-bottom: 8px; text-align: left;
        font-size: 14px; font-weight: 600; color: #111827; line-height: 1.6;
    }
    .sl-section-card .layui-input-block { margin-left: 0; }
    .sl-field-tag {
        display: inline-block; margin-bottom: 8px; padding: 3px 8px; border-radius: 999px;
        background: #dbeafe; color: #1d4ed8; font-size: 12px; line-height: 1.6;
    }
    .sl-section-card .layui-input,
    .sl-section-card .layui-select-title input,
    .sl-section-card .layui-textarea {
        border-radius: 10px; min-height: 42px;
    }
    .sl-section-card .layui-textarea { min-height: 80px; }
    .sl-section-card .layui-form-switch { margin-top: 4px; }
    .sl-section-card .form-tips {
        display: block; margin-top: 8px; padding: 8px 10px; background: #fff; border-radius: 8px; color: #475569; line-height: 1.8;
    }
    .sl-perm-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 16px; }
    .sl-info-box {
        background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px;
        padding: 10px 14px; margin: 0 0 14px; font-size: 12px; color: #0c4a6e; line-height: 1.8;
    }
    .sl-info-box.is-green {
        background: #f0fdf4; border-color: #86efac; color: #166534;
    }
    #form-btn {
        background: #fff; border-top: 1px solid #e5e7eb; padding: 14px 25px 0; margin-top: 16px;
    }
    #form-btn .layui-input-block {
        margin-left: 0 !important; display: flex; align-items: center; justify-content: center;
        gap: 10px; flex-wrap: wrap;
    }
    #form-btn .layui-btn { min-width: 120px; }
    .sl-icon-picker { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
    .sl-icon-choice { width: 38px; height: 38px; border: 1px solid #dbeafe; border-radius: 10px; background: #fff; color: #2563eb; font-size: 20px; cursor: pointer; }
    .sl-icon-choice.is-active { border-color: #2563eb; background: #eff6ff; box-shadow: 0 0 0 2px rgba(37,99,235,.08); }
    .sl-icon-image-row { display: flex; gap: 8px; }
    .sl-icon-preview { width: 54px; height: 54px; margin-top: 10px; border-radius: 16px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 28px; overflow: hidden; }
    .sl-icon-preview img { width: 100%; height: 100%; object-fit: cover; }
    @media screen and (max-width: 768px) {
        .sl-perm-grid { grid-template-columns: 1fr; }
        .sl-section-card { padding: 16px 14px 2px; border-radius: 12px; }
        .sl-section-card .layui-form-item { padding: 12px; }
        #form-btn { padding: 12px 0 0; margin-top: 14px; }
    }
</style>

<form class="layui-form" action="?action=level_add_ajax" id="form">
    <div style="padding: 25px;" id="open-box">
        <div class="sl-tip">
            <div class="sl-guide-title">📝 每个分店等级 = 一个"分店套餐"</div>
            <div class="sl-guide-text">设好价格、权限和手续费，用户开通后即拥有对应能力。不需要自动升级的话，第 4 块条件全填 0 就行。</div>
        </div>

        <div class="sl-section-card">
        <div class="sl-section-title">1. 基本信息与图标</div>
        <div class="sl-section-desc">等级名称、价格和图标是用户最先看到的内容，图标可用 Remix Icon 或上传图片。</div>
        <div class="layui-form-item">
            <label class="layui-form-label">等级名称</label>
            <div class="layui-input-block">
                <div class="sl-field-tag">必填</div>
                <input type="text" name="name" class="layui-input" value="" placeholder="例如：基础分店、高级分店、旗舰版" lay-verify="required">
                <span class="form-tips">用户在前台能看到这个名字，用简单易懂的中文就行。</span>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">Remix Icon 图标</label>
            <div class="layui-input-block">
                <div class="sl-field-tag">默认图标</div>
                <div class="sl-icon-picker">
                    <?php foreach (['ri-store-2-line','ri-store-line','ri-building-2-line','ri-building-4-line','ri-rocket-line','ri-vip-crown-line','ri-vip-diamond-line','ri-shield-star-line'] as $_icon): ?>
                    <button type="button" class="sl-icon-choice<?= $_icon === 'ri-store-2-line' ? ' is-active' : '' ?>" data-icon="<?= $_icon ?>"><i class="<?= $_icon ?>"></i></button>
                    <?php endforeach; ?>
                </div>
                <input type="text" name="icon" id="sl-icon" class="layui-input" value="ri-store-2-line" placeholder="例如：ri-store-2-line">
                <span class="form-tips">填写 Remix Icon 类名即可。未上传图片时，前台显示这里设置的图标。</span>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">图片图标</label>
            <div class="layui-input-block">
                <div class="sl-field-tag">可选，优先显示</div>
                <div class="sl-icon-image-row">
                    <input type="text" name="icon_image" id="sl-icon-image" class="layui-input" value="" placeholder="上传后自动填入图片地址">
                    <button type="button" class="layui-btn" id="sl-icon-upload">上传图片</button>
                    <button type="button" class="layui-btn layui-btn-primary" id="sl-icon-clear">清除</button>
                </div>
                <div class="sl-icon-preview" id="sl-icon-preview"><i class="ri-store-2-line"></i></div>
                <span class="form-tips">如果上传了图片，前台会优先显示图片；留空则显示 Remix Icon。</span>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">排序权重</label>
            <div class="layui-input-block">
                <div class="sl-field-tag">等级高低</div>
                <input type="number" name="sort" class="layui-input" value="0" min="0" step="1">
                <span class="form-tips">数值越大等级越高。升级时按此值排序，相同时按 ID 排。</span>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">开通/升级价格</label>
            <div class="layui-input-block">
                <div class="sl-field-tag">这个等级卖多少钱</div>
                <input type="number" name="price" class="layui-input" value="" min="0" step="0.01" placeholder="0.00">
                <span class="form-tips">单位：元。填 0 = 免费开通。填 99 = 用户花 99 元开通此分店等级。</span>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">开通门槛</label>
            <div class="layui-input-block">
                <div class="sl-field-tag">可选限制</div>
                <select name="member_gate">
                    <option value="0">不限制（任何人都能开）</option>
                    <?php foreach ($memberLevels as $lv): if ((int)($lv['state'] ?? 1) !== 1) continue; ?>
                    <option value="<?= (int)$lv['id'] ?>"><?= htmlspecialchars($lv['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="form-tips">用户需先达到指定会员等级，才允许开通该分店等级。选「不限制」= 任何注册用户都能开。</span>
            </div>
        </div>
        </div>

        <div class="sl-section-card">
        <div class="sl-section-title">2. 权限配置</div>
        <div class="sl-section-desc">每个开关控制站长能做什么。低等级关闭部分权限，高等级逐步解锁，可形成升级动力。</div>
        <div class="sl-perm-grid">
            <?php
            $permList = [
                'is_domain'       => ['label' => '独立域名',     'tip' => '允许绑定自己的域名访问店铺', 'tag' => '高级功能'],
                'perm_setprice'   => ['label' => '自定义商品价格', 'tip' => '允许自行修改商品的售价，关闭则统一使用主站价格', 'tag' => '定价能力'],
                'perm_goodsstate' => ['label' => '商品上下架',    'tip' => '允许控制哪些商品在自己店铺中显示或隐藏', 'tag' => '商品管理'],
                'perm_tpl'        => ['label' => '更换店铺模板',   'tip' => '允许切换店铺的外观风格，关闭则跟随主站模板', 'tag' => '外观'],
                'perm_config'     => ['label' => '配置店铺信息',   'tip' => '允许修改店铺名称、公告、标题等基础信息', 'tag' => '基础'],
            ];
            foreach ($permList as $field => $meta):
            ?>
            <div class="layui-form-item">
                <label class="layui-form-label"><?= $meta['label'] ?></label>
                <div class="layui-input-block">
                    <div class="sl-field-tag"><?= $meta['tag'] ?></div>
                    <input type="hidden" name="<?= $field ?>" value="n">
                    <input type="checkbox" name="<?= $field ?>" value="y" lay-skin="switch" lay-text="开|关">
                    <span class="form-tips"><?= $meta['tip'] ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        </div>

        <div class="sl-section-card">
        <div class="sl-section-title">3. 费率设置</div>
        <div class="sl-section-desc">平台从站长利润中抽取的比例。等级越高可以给越低的费率，形成升级吸引力。</div>
        <div class="layui-form-item">
            <label class="layui-form-label">供货手续费</label>
            <div class="layui-input-block">
                <div class="sl-field-tag">核心设置</div>
                <input type="text" name="service_change" class="layui-input" value="" placeholder="0">
                <span class="form-tips">
                    平台从分店站长的<b>每笔差价利润</b>中抽取的比例。填小数，如 <b>0.1</b> = 抽 10%。填 <b>0</b> = 不抽成。<br>
                    <span style="color:#6b7280;">举例：商品拿货价 80 元，分店卖 100 元，站长差价利润 20 元。手续费 0.1 → 平台抽 20×10% = 2 元，站长实际到手 18 元。</span>
                </span>
            </div>
        </div>
        <div class="sl-info-box">
            <b style="color:#0369a1;">💡 费率建议</b><br>
            低等级（如免费开通）可设 0.05~0.1（5%~10%），高等级（如付费旗舰）可设 0.01~0.03（1%~3%）。<br>
            等级间的费率差是用户升级的主要动力之一。
        </div>
        </div>

        <div class="sl-section-card">
        <div class="sl-section-title">4. 自动升级条件</div>
        <div class="sl-section-desc">除了付费升级外，分店满足以下条件也可自动升级到本等级。全部填 0 = 只能付费升级。</div>
        <div class="layui-form-item">
            <label class="layui-form-label">判断模式</label>
            <div class="layui-input-block">
                <div class="sl-field-tag">多条件时的逻辑</div>
                <select name="upgrade_mode">
                    <option value="any">满足任一条件即可升级（OR）</option>
                    <option value="all">必须全部满足才能升级（AND）</option>
                </select>
                <span class="form-tips">
                    如果下面只开了一个条件，选哪个都一样。<br>
                    开了多个条件时：选「任一」= 达到其中一个就自动升级；选「全部」= 每个都要达标才升级。
                </span>
            </div>
        </div>
        <div class="sl-perm-grid">
            <div class="layui-form-item">
                <label class="layui-form-label">累计销售额</label>
                <div class="layui-input-block">
                    <div class="sl-field-tag">订单总金额</div>
                    <input type="number" name="upgrade_sales_amount" class="layui-input" value="0" step="0.01" min="0" placeholder="0">
                    <span class="form-tips">单位：元。填 0 = 不启用此条件。填 500 = 分店累计已付款订单总金额达到 500 元后自动升级。</span>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">累计订单量</label>
                <div class="layui-input-block">
                    <div class="sl-field-tag">已付款笔数</div>
                    <input type="number" name="upgrade_order_count" class="layui-input" value="0" min="0" placeholder="0">
                    <span class="form-tips">填 0 = 不启用此条件。填 30 = 分店累计已付款订单达到 30 笔后自动升级。</span>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">运营天数</label>
                <div class="layui-input-block">
                    <div class="sl-field-tag">开通时长</div>
                    <input type="number" name="upgrade_days" class="layui-input" value="0" min="0" placeholder="0">
                    <span class="form-tips">填 0 = 不启用此条件。填 30 = 分店开通满 30 天后自动升级。从创建日起算。</span>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">下级分店数</label>
                <div class="layui-input-block">
                    <div class="sl-field-tag">裂变发展</div>
                    <input type="number" name="upgrade_sub_count" class="layui-input" value="0" min="0" placeholder="0">
                    <span class="form-tips">填 0 = 不启用此条件。填 3 = 发展 3 个下级分店后自动升级。</span>
                </div>
            </div>
        </div>
        <div class="sl-info-box is-green">
            所有条件都为 0 表示该等级不支持自动升级，只能通过付费升级。达标即免费升级，不扣余额。
        </div>
        </div>

        <div class="sl-section-card">
        <div class="sl-section-title">5. 等级说明</div>
        <div class="sl-section-desc">写一句话告诉用户这个分店等级有啥好处，展示在前台分店开通/升级页面。</div>
        <div class="layui-form-item">
            <label class="layui-form-label">等级描述</label>
            <div class="layui-input-block">
                <div class="sl-field-tag">给用户看的介绍</div>
                <textarea name="description" class="layui-textarea" placeholder="例如：免费开通，基础功能；或：解锁独立域名与自定义价格，低手续费"></textarea>
                <span class="form-tips">写 1-2 句话，说清楚这个等级的核心卖点，让站长一看就想升级。</span>
            </div>
        </div>
        </div>

        <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
        <div id="form-btn">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn" lay-submit lay-filter="submit">添加等级</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
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
            var icon = $('#sl-icon').val() || 'ri-store-2-line';
            var img = $('#sl-icon-image').val();
            $('.sl-icon-choice').removeClass('is-active').filter('[data-icon="' + icon + '"]').addClass('is-active');
            $('#sl-icon-preview').html(img ? '<img src="' + img + '" alt="">' : '<i class="' + icon + '"></i>');
        }
        $('.sl-icon-choice').on('click', function(){
            $('#sl-icon').val($(this).data('icon'));
            refreshIconPreview();
        });
        $('#sl-icon,#sl-icon-image').on('input', refreshIconPreview);
        $('#sl-icon-clear').on('click', function(){
            $('#sl-icon-image').val('');
            refreshIconPreview();
        });
        upload.render({
            elem: '#sl-icon-upload',
            field: 'image',
            url: '?action=level_upload_icon',
            data: { token: function(){ return $('#token').val(); } },
            done: function(res){
                if (res.code > 0) return layer.msg(res.msg || '上传失败');
                $('#sl-icon-image').val(res.data || '');
                refreshIconPreview();
            }
        });
        refreshIconPreview();
        form.render();
        form.on('submit(submit)', function(data){
            var field = data.field;
            var url = $('#form').attr('action');
            $.ajax({
                type: "POST",
                url: url,
                data: field,
                dataType: "json",
                success: function (e) {
                    if(e.code == 400) return layer.msg(e.msg);
                    parent.layer.close('add');
                    parent.layer.msg('添加成功');
                    window.parent.table.reload();
                },
                error: function (xhr) {
                    layer.msg(JSON.parse(xhr.responseText).msg);
                }
            });
            return false;
        });
    });

    var maxHeight = $(window.parent).innerHeight() * 0.75;
    $("#open-box").css({
        "max-height": maxHeight + "px",
        "overflow-y": "auto"
    });
</script>
