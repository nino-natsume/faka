<?php defined('DC_ROOT') || exit('access denied!'); ?>
<style>
    .ucfg-section { background: #ffffff85; border: 1px solid #eef1f4; border-radius: 8px; padding: 18px 20px; margin-bottom: 14px; }
    .ucfg-title { font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; }
    .ucfg-title i { color: #2563eb; }
    .ucfg-row { display: grid; grid-template-columns: 160px 1fr; gap: 10px; align-items: center; padding: 8px 0; }
    .ucfg-row > label { color: #374151; font-weight: 500; }
    .ucfg-row .layui-input-inline { width: 260px; }
    .ucfg-row .layui-form-select { width: 100%; }
    .ucfg-tip { color: #6b7280; font-size: 12px; line-height: 1.7; }
    .ucfg-tip b { color: #2563eb; }
    .ucfg-radio-line { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding: 2px 0; }
    .ucfg-option-group { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding: 2px 0; }
    .ucfg-option {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 0 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
        color: #374151;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all .18s ease;
        user-select: none;
    }
    .ucfg-option input { display: none; }
    .ucfg-option:hover {
        border-color: rgba(37, 99, 235, .35);
        color: #2563eb;
        background: #f8fbff;
    }
    .ucfg-option.is-active {
        border-color: #2563eb;
        color: #2563eb;
        background: rgba(37, 99, 235, .08);
        box-shadow: 0 0 0 2px rgba(37, 99, 235, .08);
    }

    @media (max-width: 768px) {
        .layui-card-body { padding: 12px !important; }
        .ucfg-row { grid-template-columns: 1fr; gap: 4px; }
        .ucfg-row > label { padding-top: 0; font-size: 13px; }
        .ucfg-section { padding: 14px 12px; }
        .ucfg-row .layui-input-inline { width: 100% !important; }
        .ucfg-row .layui-input,
        .ucfg-row .layui-form-select,
        .ucfg-row select { max-width: 100%; }
    }
</style>

<div class="layui-tabs order-tabs-wrapper" style="display:flex;align-items:center;justify-content:space-between;" lay-options="{trigger: false}">
    <ul class="layui-tabs-header">
        <li class="layui-this"><a href="./shop.php">商城配置</a></li>
        <li><a href="./shop.php?action=gg">公告设置</a></li>
        <li><a href="./shop.php?action=btx">下单输入框</a></li>
        <li><a href="./shop.php?action=user">用户配置</a></li>
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
        <span style="color:#667797;font-size:14px;font-weight:500;">商城配置</span>
    </div>
    <div class="layui-card-body" style="padding:20px;">
        <form action="shop.php?action=index_save" method="post" name="setting_form" id="setting_form" class="layui-form">
            <div class="ucfg-section">
                <div class="ucfg-title"><i class="ri-bank-card-line"></i> 支付设置</div>
                <div class="ucfg-row">
                    <label>余额支付</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" value="y" name="balance_switch" lay-skin="switch" lay-text="开启|关闭" <?= ($balance_switch ?? 'y') == 'y' ? 'checked' : '' ?>>
                        </div>
                        <div class="ucfg-tip">开启后，用户下单时可使用账户余额抵扣支付。</div>
                    </div>
                </div>

                <div class="ucfg-row">
                    <label>继续付款</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" value="y" name="continue_pay_switch" lay-skin="switch" lay-text="开启|关闭" lay-filter="continuePaySwitch" <?= ($continue_pay_switch ?? 'y') == 'y' ? 'checked' : '' ?>>
                        </div>
                        <div class="ucfg-tip">开启后，未支付订单在超时前可继续付款。</div>
                    </div>
                </div>

                <div class="ucfg-row" id="continuePayTimeoutItem" <?= ($continue_pay_switch ?? 'y') != 'y' ? 'style="display:none"' : '' ?>>
                    <label>付款超时时间</label>
                    <div>
                        <div class="layui-input-inline" style="width:120px;">
                            <input type="number" name="continue_pay_timeout" class="layui-input" value="<?= intval($continue_pay_timeout ?? 30) ?>" min="1" max="1440" placeholder="30">
                        </div>
                        <div class="ucfg-tip">单位：分钟。建议 15～60，默认 30。超时未支付的订单将自动取消。</div>
                    </div>
                </div>

                <div class="ucfg-row">
                    <label>支付后跳转</label>
                    <div>
                        <div class="ucfg-option-group">
                            <label class="ucfg-option <?= ($pay_redirect ?? 'list') == 'list' ? 'is-active' : '' ?>">
                                <input type="radio" name="pay_redirect" value="list" lay-ignore <?= ($pay_redirect ?? 'list') == 'list' ? 'checked' : '' ?>>
                                <span>订单列表</span>
                            </label>
                            <label class="ucfg-option <?= ($pay_redirect ?? 'list') == 'kami' ? 'is-active' : '' ?>">
                                <input type="radio" name="pay_redirect" value="kami" lay-ignore <?= ($pay_redirect ?? 'list') == 'kami' ? 'checked' : '' ?>>
                                <span>订单详情页</span>
                            </label>
                        </div>
                        <div class="ucfg-tip">支付成功后自动跳转并打开订单详情。卡密类显示卡密，人工发货类显示订单进度。</div>
                    </div>
                </div>
            </div>

            <div class="ucfg-section">
                <div class="ucfg-title"><i class="ri-coins-line"></i> 虚拟资产设置</div>
                <div class="ucfg-row">
                    <label>货币名称</label>
                    <div>
                        <div class="layui-input-inline" style="width:180px;">
                            <input type="text" name="virtual_currency_name" class="layui-input" value="<?= htmlspecialchars($virtual_currency_name ?? '积分') ?>" maxlength="12" placeholder="积分">
                        </div>
                        <div class="ucfg-tip">用于前端展示虚拟货币名称，例如：积分、金币。留空默认“积分”。</div>
                    </div>
                </div>
            </div>

            <div class="ucfg-section">
                <div class="ucfg-title"><i class="ri-file-list-3-line"></i> 订单设置</div>
                <div class="ucfg-row">
                    <label>商品图显示</label>
                    <div>
                        <div class="layui-input-inline">
                            <input type="checkbox" value="y" name="order_goods_img_switch" lay-skin="switch" lay-text="开启|关闭" <?= ($order_goods_img_switch ?? 'n') == 'y' ? 'checked' : '' ?>>
                        </div>
                        <div class="ucfg-tip">控制订单列表中是否显示商品封面图。</div>
                    </div>
                </div>

                <div class="ucfg-row">
                    <label>卡密发货顺序</label>
                    <div>
                        <div class="ucfg-option-group">
                            <label class="ucfg-option <?= ($kami_order ?? 'asc') == 'asc' ? 'is-active' : '' ?>">
                                <input type="radio" name="kami_order" value="asc" lay-ignore <?= ($kami_order ?? 'asc') == 'asc' ? 'checked' : '' ?>>
                                <span>先售旧卡</span>
                            </label>
                            <label class="ucfg-option <?= ($kami_order ?? 'asc') == 'desc' ? 'is-active' : '' ?>">
                                <input type="radio" name="kami_order" value="desc" lay-ignore <?= ($kami_order ?? 'asc') == 'desc' ? 'checked' : '' ?>>
                                <span>先售新卡</span>
                            </label>
                            <label class="ucfg-option <?= ($kami_order ?? 'asc') == 'random' ? 'is-active' : '' ?>">
                                <input type="radio" name="kami_order" value="random" lay-ignore <?= ($kami_order ?? 'asc') == 'random' ? 'checked' : '' ?>>
                                <span>随机售卡</span>
                            </label>
                        </div>
                        <div class="ucfg-tip">卡密类商品发货时的卡密取出顺序。默认“先售旧卡”。</div>
                    </div>
                </div>
            </div>

            <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
            <div class="layui-form-item" style="text-align:center;margin-top:10px;">
                <button type="submit" class="layui-btn" lay-submit lay-filter="shop-config-save"><i class="ri-save-line"></i> 保存设置</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </form>
    </div>
</div>

<div style="height: 96px;"></div>

<script>
layui.use(['form'], function(){
    var form = layui.form;

    form.on('submit(shop-config-save)', function(){
        submitForm("#setting_form");
        return false;
    });

    form.on('switch(continuePaySwitch)', function(data){
        $('#continuePayTimeoutItem')[data.elem.checked ? 'slideDown' : 'slideUp'](200);
    });

    $(document).on('change', '.ucfg-option input[type="radio"]', function(){
        var name = this.name;
        $('.ucfg-option input[type="radio"][name="' + name + '"]').closest('.ucfg-option').removeClass('is-active');
        $(this).closest('.ucfg-option').addClass('is-active');
    });
});
</script>
