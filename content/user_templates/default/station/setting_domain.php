<?php
defined('DC_ROOT') || exit('access denied!');

$_domainChangePrice = (float)(Option::get('station_domain_change_price') ?: 0);
$_cnameDomain = trim((string)Option::get('station_cname_domain'));
$_slugVal = $userStation['slug'] ?? '';
if ($_slugVal === 'NULL' || $_slugVal === null) $_slugVal = '';
$stationSlug = $_slugVal;

$_validDomains = [];
if (!empty($station_domain)) {
    foreach ($station_domain as $_dv) {
        if (trim((string)$_dv) !== '') $_validDomains[] = $_dv;
    }
}
?>

<style>
    .ss-page { display: flex; flex-direction: column; gap: 22px; padding: 8px 0 18px; }
    .ss-page-header { display: flex; align-items: center; gap: 14px; }
    .ss-back-btn { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 12px; border: 1.5px solid var(--card-border, #e2e8f0); background: var(--card-bg, #fff); color: var(--text-sub, #64748b); font-size: 16px; cursor: pointer; transition: .18s; text-decoration: none; flex-shrink: 0; }
    .ss-back-btn:hover { color: var(--theme-primary); border-color: rgba(var(--tp-rgb),.22); background: rgba(var(--tp-rgb),.06); text-decoration: none; }
    .ss-page-title { margin: 0; font-size: 22px; font-weight: 800; color: var(--text-main, #1e293b); }
    .ss-page-desc { margin: 2px 0 0; font-size: 13px; color: var(--text-sub, #64748b); }

    .ss-form-card { background: var(--pc-card-bg); border: 2px solid #fff; border-radius: 14px; box-shadow: 0 1px 18px #12345b0a; overflow: hidden; }
    .ss-form-body { padding: 28px; }
    .ss-form-grid { display: grid; grid-template-columns: 1fr; gap: 18px; }
    .ss-form-item { margin-bottom: 0; }
    .ss-label { display: flex; align-items: center; gap: 6px; margin-bottom: 10px; color: var(--text-main, #1e293b); font-size: 14px; font-weight: 600; }
    .ss-input, .ss-textarea, .ss-select {
        width: 100%; border: 1.5px solid var(--input-border, #e2e8f0); background: var(--input-bg, #fff);
        color: var(--text-main); border-radius: 10px; padding: 13px 16px; font-size: 14px;
        transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
    }
    .ss-input:focus, .ss-textarea:focus, .ss-select:focus {
        outline: none; border-color: rgba(var(--tp-rgb),.5); box-shadow: 0 0 0 4px rgba(var(--tp-rgb),.08); background: #fff;
    }
    .ss-tips { display: block; margin-top: 8px; color: var(--text-sub, #64748b); font-size: 12px; line-height: 1.7; }
    .ss-inline-form { display: flex; gap: 10px; align-items: center; }
    .ss-inline-form .ss-input { flex: 1; min-width: 0; }
    .ss-inline-form .ss-select { flex: 0 0 auto; width: 200px; }
    .ss-slug-prefix { display: flex; align-items: center; color: #888; white-space: nowrap; padding-right: 6px; font-size: 13px; line-height: 38px; }
    .ss-domain-warn { margin-top: 12px; padding: 10px 14px; border-radius: 8px; background: rgba(234,179,8,.08); border: 1px solid rgba(234,179,8,.2); color: #92400e; font-size: 12px; line-height: 1.7; }

    .ss-form-footer { display: flex; align-items: center; justify-content: flex-end; gap: 10px; padding: 16px 28px; border-top: 1px solid var(--card-border, #e2e8f0); background: var(--bg-secondary, #f8fafc); }
    .ss-btn-save { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-width: 140px; height: 44px; padding: 0 22px; border: 0; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; transition: .18s; background: linear-gradient(135deg, var(--tp-dark), var(--tp-light)); color: #fff; box-shadow: 0 8px 20px rgba(var(--tp-rgb),.18); }
    .ss-btn-save:hover { transform: translateY(-1px); box-shadow: 0 12px 28px rgba(var(--tp-rgb),.24); }

    @media (max-width: 768px) {
        .ss-form-body { padding: 20px; }
        .ss-form-footer { padding: 14px 20px; }
        .ss-inline-form { flex-wrap: wrap; }
        .ss-inline-form .ss-select { width: 100%; }
    }
</style>

<main class="ss-page">
    <div class="ss-page-header">
        <a href="?action=setting" class="ss-back-btn"><i class="fa fa-arrow-left"></i></a>
        <div>
            <h1 class="ss-page-title">域名配置</h1>
            <p class="ss-page-desc">二级域名、独立域名与店铺标识</p>
        </div>
    </div>

    <div class="ss-form-card">
        <form id="form-domain">
            <div class="ss-form-body">
                <div class="ss-form-grid">
                    <div class="ss-form-item">
                        <label class="ss-label"><i class="fa fa-link" style="color:#0b897a;"></i> 二级域名</label>
                        <?php if (!empty($_validDomains)): ?>
                        <div class="ss-inline-form">
                            <input type="text" value="<?= htmlspecialchars($userStation['domain_2_prefix']) ?>" name="domain_2_prefix" placeholder="例如: dcshop" class="ss-input">
                            <select name="domain_2_suffix" class="ss-select" style="width:200px;min-width:200px;flex:0 0 200px;">
                                <?php foreach($_validDomains as $val): ?>
                                <option value=".<?= htmlspecialchars($val) ?>" <?= $userStation['domain_2_suffix'] == '.' . $val ? 'selected' : '' ?>>.<?= htmlspecialchars($val) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <span class="ss-tips">填写你想要的前缀，再选择后面的域名后缀，系统会自动拼成一个完整的访问地址。例如填写 <b>dcshop</b>，选择 <b>.<?= htmlspecialchars($_validDomains[0]) ?></b>，最终地址就是 <b>myshop.<?= htmlspecialchars($_validDomains[0]) ?></b></span>
                        <?php else: ?>
                        <div style="padding:14px 16px;border-radius:10px;background:rgba(100,116,139,.06);border:1.5px dashed #cbd5e1;color:#64748b;font-size:13px;line-height:1.7;">
                            <i class="fa fa-info-circle" style="color:#94a3b8;margin-right:6px;"></i>
                            管理员暂未开放二级域名功能。如需使用，请联系管理员在后台「分店设置」中添加可选的域名后缀。
                        </div>
                        <input type="hidden" name="domain_2_prefix" value="">
                        <input type="hidden" name="domain_2_suffix" value="">
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($_cnameDomain)): ?>
                    <div class="ss-form-item">
                        <label class="ss-label"><i class="fa fa-external-link" style="color:#0b897a;"></i> 独立域名</label>
                        <input type="text" name="domain" value="<?= htmlspecialchars($userStation['domain']) ?>" placeholder="例如: shop.example.com" class="ss-input">
                        <span class="ss-tips">如果你有自己购买的域名，可以填在这里。填写前需要先去你的域名管理后台，添加一条 <b>CNAME 记录</b>，指向 <b><?= htmlspecialchars($_cnameDomain) ?></b>，解析生效后即可使用。</span>
                    </div>
                    <?php else: ?>
                    <input type="hidden" name="domain" value="">
                    <?php endif; ?>
                    <?php if ($station_slug_mode === '1'): ?>
                    <div class="ss-form-item">
                        <label class="ss-label"><i class="fa fa-tag" style="color:#0b897a;"></i> 店铺短链接</label>
                        <div class="ss-inline-form">
                            <span class="ss-slug-prefix"><?= htmlspecialchars(rtrim(Option::get('blogurl'), '/')) ?>/s/</span>
                            <input type="text" name="slug" value="<?= htmlspecialchars($stationSlug) ?>" placeholder="例如: shop1" class="ss-input" maxlength="50" style="flex:1;">
                        </div>
                        <span class="ss-tips">不想配置域名也没关系！填一个好记的标识（只能用英文字母、数字、下划线、连字符），系统会自动生成一个专属链接，把链接发给客户就能直接访问你的店铺。</span>
                    </div>
                    <?php endif; ?>
                    <?php if ($_domainChangePrice > 0): ?>
                    <div class="ss-domain-warn"><i class="fa fa-exclamation-triangle"></i> 注意：如果你之前已经绑定过域名，再次修改需要支付 ¥<?= number_format($_domainChangePrice, 2) ?> 手续费（首次绑定免费）。</div>
                    <?php endif; ?>
                </div>
            </div>
        </form>
        <div class="ss-form-footer">
            <button type="button" class="ss-btn-save" id="btn-save-domain"><i class="fa fa-check"></i> 保存</button>
        </div>
    </div>
</main>

<script>
(function() {
    function boot() {
        if (!window.jQuery || !window.layui || !window.layui.use) { setTimeout(boot, 80); return; }
        layui.use(['layer'], function() {
            var layer = layui.layer;
            var _domainChangePrice = <?= json_encode($_domainChangePrice) ?>;
            var _oldD2 = <?= json_encode(trim(($userStation['domain_2_prefix'] ?? '') . ($userStation['domain_2_suffix'] ?? ''))) ?>;
            var _oldDomain = <?= json_encode(trim($userStation['domain'] ?? '')) ?>;

            function _doSave() {
                var data = $('#form-domain').serialize();
                var $btn = $('#btn-save-domain');
                $btn.prop('disabled', true).text('保存中...');
                $.ajax({
                    type: 'POST', url: '?action=setting_domain_ajax', data: data, dataType: 'json',
                    success: function(e) {
                        $btn.prop('disabled', false).html('<i class="fa fa-check"></i> 保存');
                        if (e.code == 400) { layer.msg(e.msg, {icon: 2}); }
                        else {
                            layer.msg(e.msg || '保存成功', {icon: 1});
                            _oldD2 = ($('#form-domain [name=domain_2_prefix]').val() || '') + ($('#form-domain [name=domain_2_suffix]').val() || '');
                            _oldDomain = $('#form-domain [name=domain]').val() || '';
                        }
                    },
                    error: function() {
                        $btn.prop('disabled', false).html('<i class="fa fa-check"></i> 保存');
                        layer.msg('网络请求发生错误，请重试', {icon: 2});
                    }
                });
            }

            // 保存（含域名修改扣费确认）
            $('#btn-save-domain').on('click', function() {
                var _newD2 = ($('#form-domain [name=domain_2_prefix]').val() || '') + ($('#form-domain [name=domain_2_suffix]').val() || '');
                var _newDomain = $('#form-domain [name=domain]').val() || '';
                var _d2Changed = _newD2 !== _oldD2 && _oldD2 !== '';
                var _dChanged = _newDomain !== _oldDomain && _oldDomain !== '';

                if (_domainChangePrice > 0 && (_d2Changed || _dChanged)) {
                    var items = [];
                    if (_d2Changed) items.push('二级域名');
                    if (_dChanged) items.push('独立域名');
                    layer.confirm(
                        '<div style="padding:12px 0;font-size:14px;line-height:1.8;">'
                        + '<p>检测到你修改了 <b>' + items.join('、') + '</b>，将扣除手续费 <b style="color:#d97706;">¥' + _domainChangePrice.toFixed(2) + '</b>。</p>'
                        + '<p style="color:#64748b;font-size:12px;margin-top:6px;">提示：首次绑定免费，店铺短链接修改不收费。</p>'
                        + '</div>',
                        { title: '域名修改扣费确认', btn: ['确认保存', '取消'], icon: 0, area: ['420px'] },
                        function(idx) { layer.close(idx); _doSave(); }
                    );
                } else {
                    _doSave();
                }
            });
        });
    }
    boot();
})();
</script>
