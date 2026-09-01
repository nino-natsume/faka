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
$_domain2Full = (!empty($userStation['domain_2_prefix']) && !empty($userStation['domain_2_suffix'])) ? $userStation['domain_2_prefix'] . $userStation['domain_2_suffix'] : '';
$_domainFull = trim((string)($userStation['domain'] ?? ''));
$_shareLink = $stationSlug !== '' ? rtrim(Option::get('blogurl'), '/') . '/s/' . $stationSlug : '';
$_domainDone = 0;
if ($_domain2Full !== '') $_domainDone++;
if ($_domainFull !== '') $_domainDone++;
if ($_shareLink !== '') $_domainDone++;
?>
<style>
    .uc-site-footer{display:none!important}.sdm-page,.sdm-page *{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
    .sdm-page{--sdm-primary:var(--theme-primary,#667eea);--sdm-primary-rgb:var(--tp-rgb,102,126,234);--sdm-soft:rgba(var(--sdm-primary-rgb),.10);min-height:100vh;padding:12px 12px calc(76px + env(safe-area-inset-bottom,0px));background:#f5f6f8;color:#20242c;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif}
    .sdm-card{display:block;margin-bottom:12px;padding:16px;border-radius:10px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;box-shadow:var(--shadow-primary);text-decoration:none;color:inherit}.sdm-card-head{display:flex;align-items:flex-start;gap:9px;margin-bottom:13px}.sdm-card-icon{width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:var(--sdm-soft);color:var(--sdm-primary);font-size:15px;flex-shrink:0}.sdm-card-title{margin:0;color:#20242c;font-size:14px;font-weight:900;line-height:1.2}.sdm-card-desc{margin:4px 0 0;color:#8b95a5;font-size:12px;line-height:1.6;font-weight:500}.sdm-fields{display:grid;gap:9px}.sdm-field{display:block;padding:11px 12px;border-radius:14px;background:#f8f9fb}.sdm-label{display:block;margin-bottom:8px;color:#7c8797;font-size:12px;font-weight:800}.ss-input,.ss-select{width:100%;height:42px;padding:0 12px;border:1px solid #edf0f5;border-radius:12px;background:#fff;color:#20242c;font-size:16px;outline:none;box-shadow:none}.ss-input:focus,.ss-select:focus{border-color:rgba(var(--sdm-primary-rgb),.35);box-shadow:0 0 0 3px rgba(var(--sdm-primary-rgb),.08)}.sdm-tip{display:block;margin-top:7px;color:#8b95a5;font-size:12px;line-height:1.65}.sdm-inline{display:flex;gap:8px;align-items:center}.sdm-inline .ss-input{flex:1;min-width:0}.sdm-inline .ss-select{width:120px!important;min-width:120px!important;flex:0 0 120px!important}.sdm-prefix{height:42px;padding:0 10px;border-radius:12px;background:#fff;color:#8b95a5;font-size:12px;display:flex;align-items:center;white-space:nowrap;border:1px solid #edf0f5}.sdm-empty{padding:11px 12px;border-radius:14px;background:#f8f9fb;border:1px dashed #d8dee8;color:#697180;font-size:12px;line-height:1.7}.sdm-warn{margin-top:0;margin-bottom:12px;padding:11px 12px;border-radius:14px;background:#fffbeb;color:#92400e;font-size:12px;line-height:1.7}.sdm-cname{margin-top:10px;padding:11px 12px;border-radius:14px;background:#ecfdf5;color:#047857;font-size:12px;line-height:1.7;word-break:break-all}.sdm-savebar{position:fixed;left:0;right:0;bottom:0;z-index:30;padding:10px 12px calc(10px + env(safe-area-inset-bottom,0px));background:rgba(255,255,255,.96);border-top:1px solid #edf0f5;backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px)}.sdm-save{width:100%;height:46px;border:0;border-radius:16px;background:linear-gradient(135deg,var(--theme-primary,#667eea),var(--theme-secondary,#764ba2));color:#fff;font-size:14px;font-weight:900;box-shadow:0 10px 24px rgba(var(--sdm-primary-rgb),.18)}
    .sdm-app-modal-mask,.sdm-app-modal-mask *{box-sizing:border-box;-webkit-tap-highlight-color:transparent}.sdm-app-modal-mask{--sdm-primary:var(--theme-primary,#667eea);--sdm-primary-rgb:var(--tp-rgb,102,126,234);position:fixed;inset:0;z-index:19999;background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:opacity .25s,visibility .25s}.sdm-app-modal-mask.is-show{opacity:1;visibility:visible}.sdm-app-modal{width:min(88vw,340px);background:#fff;border-radius:20px;overflow:hidden;transform:translateY(24px) scale(.96);transition:transform .28s cubic-bezier(.22,.61,.36,1);box-shadow:0 20px 50px rgba(0,0,0,.18)}.sdm-app-modal-mask.is-show .sdm-app-modal{transform:translateY(0) scale(1)}.sdm-app-modal-header{padding:22px 22px 0;text-align:center}.sdm-app-modal-icon{width:52px;height:52px;margin:0 auto 12px;border-radius:50%;background:linear-gradient(135deg,var(--theme-primary,#667eea),var(--theme-secondary,#764ba2));display:flex;align-items:center;justify-content:center;font-size:24px;color:#fff}.sdm-app-modal-title{font-size:17px;font-weight:800;color:#252d3b}.sdm-app-modal-body{padding:16px 22px 6px;text-align:center}.sdm-app-modal-text{padding:2px 0 8px;color:#5f6673;font-size:13px;line-height:1.8}.sdm-app-modal-row{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f2f4f7;font-size:13px;color:#5f6673}.sdm-app-modal-row:last-child{border-bottom:none}.sdm-app-modal-row b{min-width:0;color:#252d3b;font-weight:700;text-align:right;word-break:break-all}.sdm-app-modal-price{color:#d97706!important;font-weight:900!important}.sdm-app-modal-notice{margin-top:12px;padding:12px;border-radius:13px;background:#fff7ed;border:1px solid #fed7aa;color:#9a5b14;font-size:12px;line-height:1.75;text-align:left}.sdm-app-modal-foot{display:flex;gap:10px;padding:10px 22px 22px}.sdm-app-modal-btn{flex:1;height:44px;border:none;border-radius:14px;font-size:15px;font-weight:700;cursor:pointer;transition:.15s}.sdm-app-modal-btn:disabled{opacity:.65}.sdm-app-modal-cancel{background:#f3f4f6;color:#5f6673}.sdm-app-modal-cancel:active{background:#e8ebf0}.sdm-app-modal-confirm{background:linear-gradient(135deg,var(--theme-primary,#667eea),var(--theme-secondary,#764ba2));color:#fff;box-shadow:0 6px 16px rgba(var(--sdm-primary-rgb),.22)}.sdm-app-modal-confirm:active{transform:scale(.97)}
    @media(max-width:360px){.sdm-page{padding-left:10px;padding-right:10px}.sdm-field{padding-left:10px;padding-right:10px}.sdm-inline{flex-wrap:wrap}.sdm-inline .ss-select{width:100%!important;flex:1 1 100%!important}}
</style>

<main class="sdm-page">
<form id="form-domain">
        <section class="sdm-card">
            <div class="sdm-card-head"><div class="sdm-card-icon"><i class="fa fa-link"></i></div><div><h2 class="sdm-card-title">二级域名</h2><p class="sdm-card-desc">选择平台开放的域名后缀，生成专属访问地址。</p></div></div>
            <div class="sdm-fields">
                <div class="sdm-field">
                    <span class="sdm-label">域名前缀</span>
                    <?php if (!empty($_validDomains)): ?>
                    <div class="sdm-inline">
                        <input type="text" value="<?= htmlspecialchars($userStation['domain_2_prefix']) ?>" name="domain_2_prefix" placeholder="例如: dcshop" class="ss-input">
                        <select name="domain_2_suffix" class="ss-select">
                            <?php foreach($_validDomains as $val): ?>
                            <option value=".<?= htmlspecialchars($val) ?>" <?= $userStation['domain_2_suffix'] == '.' . $val ? 'selected' : '' ?>>.<?= htmlspecialchars($val) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <span class="sdm-tip">填写前缀后会自动拼成完整地址，例如 dcshop<?= !empty($_validDomains[0]) ? '.' . htmlspecialchars($_validDomains[0]) : '' ?>。</span>
                    <?php else: ?>
                    <div class="sdm-empty"><i class="fa fa-info-circle"></i> 管理员暂未开放二级域名功能。如需使用，请联系管理员添加可选域名后缀。</div>
                    <input type="hidden" name="domain_2_prefix" value=""><input type="hidden" name="domain_2_suffix" value="">
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <?php if (!empty($_cnameDomain)): ?>
        <section class="sdm-card">
            <div class="sdm-card-head"><div class="sdm-card-icon"><i class="fa fa-external-link"></i></div><div><h2 class="sdm-card-title">独立域名</h2><p class="sdm-card-desc">绑定自己购买的域名，展示更独立的品牌入口。</p></div></div>
            <label class="sdm-field"><span class="sdm-label">独立域名</span><input type="text" name="domain" value="<?= htmlspecialchars($userStation['domain']) ?>" placeholder="例如: shop.example.com" class="ss-input"><span class="sdm-tip">请先在域名管理后台添加 CNAME 记录后再保存。</span></label>
            <div class="sdm-cname"><i class="fa fa-info-circle"></i> CNAME 指向：<?= htmlspecialchars($_cnameDomain) ?></div>
        </section>
        <?php else: ?>
        <input type="hidden" name="domain" value="">
        <?php endif; ?>

        <?php if ($station_slug_mode === '1'): ?>
        <section class="sdm-card">
            <div class="sdm-card-head"><div class="sdm-card-icon"><i class="fa fa-tag"></i></div><div><h2 class="sdm-card-title">店铺短链接</h2><p class="sdm-card-desc">不绑定域名也可以用短链接分享给客户。</p></div></div>
            <div class="sdm-inline"><span class="sdm-prefix"><?= htmlspecialchars(rtrim(Option::get('blogurl'), '/')) ?>/s/</span><input type="text" name="slug" value="<?= htmlspecialchars($stationSlug) ?>" placeholder="例如: shop1" class="ss-input" maxlength="50"></div>
            <span class="sdm-tip">只能包含英文字母、数字、下划线、连字符，长度 2-50 位。</span>
        </section>
        <?php endif; ?>

        <?php if ($_domainChangePrice > 0): ?>
        <div class="sdm-warn"><i class="fa fa-exclamation-triangle"></i> 已绑定后再次修改域名需要支付 ¥<?= number_format($_domainChangePrice, 2) ?> 手续费，首次绑定免费。</div>
        <?php endif; ?>
    </form>
    <div class="sdm-savebar"><button type="button" class="sdm-save" id="btn-save-domain"><i class="fa fa-check"></i> 保存域名配置</button></div>
</main>

<div class="sdm-app-modal-mask" id="sdmDomainConfirmModal">
    <div class="sdm-app-modal">
        <div class="sdm-app-modal-header">
            <div class="sdm-app-modal-icon"><i class="fa fa-exclamation-triangle"></i></div>
            <div class="sdm-app-modal-title">域名修改扣费确认</div>
        </div>
        <div class="sdm-app-modal-body">
            <div class="sdm-app-modal-text">检测到你修改了已绑定的域名，确认保存后将扣除手续费。</div>
            <div class="sdm-app-modal-row"><span>修改项目</span><b id="sdmConfirmItems">-</b></div>
            <div class="sdm-app-modal-row"><span>扣费金额</span><b class="sdm-app-modal-price" id="sdmConfirmPrice">¥0.00</b></div>
            <div class="sdm-app-modal-notice"><i class="fa fa-info-circle"></i> 提示：首次绑定免费，店铺短链接修改不收费。</div>
        </div>
        <div class="sdm-app-modal-foot">
            <button type="button" class="sdm-app-modal-btn sdm-app-modal-cancel" id="sdmDomainConfirmCancel">取消</button>
            <button type="button" class="sdm-app-modal-btn sdm-app-modal-confirm" id="sdmDomainConfirmOk">确认保存</button>
        </div>
    </div>
</div>

<script>
(function() {
    function boot() {
        if (!window.jQuery || !window.layui || !window.layui.use) { setTimeout(boot, 80); return; }
        layui.use(['layer'], function() {
            var layer = layui.layer;
            var _domainChangePrice = <?= json_encode($_domainChangePrice) ?>;
            var _oldD2 = <?= json_encode(trim(($userStation['domain_2_prefix'] ?? '') . ($userStation['domain_2_suffix'] ?? ''))) ?>;
            var _oldDomain = <?= json_encode(trim($userStation['domain'] ?? '')) ?>;

            function showAppModal(selector) {
                $(selector).addClass('is-show');
            }

            function hideAppModal(selector) {
                $(selector).removeClass('is-show');
            }

            function openDomainConfirm(items) {
                $('#sdmConfirmItems').text(items.join('、'));
                $('#sdmConfirmPrice').text('¥' + _domainChangePrice.toFixed(2));
                $('#sdmDomainConfirmOk').prop('disabled', false).text('确认保存');
                showAppModal('#sdmDomainConfirmModal');
            }

            function _doSave() {
                var data = $('#form-domain').serialize();
                var $btn = $('#btn-save-domain');
                $btn.prop('disabled', true).text('保存中...');
                $.ajax({
                    type: 'POST', url: '?action=setting_domain_ajax', data: data, dataType: 'json',
                    success: function(e) {
                        $btn.prop('disabled', false).html('<i class="fa fa-check"></i> 保存域名配置');
                        if (e.code == 400) { layer.msg(e.msg, {icon: 2}); }
                        else { layer.msg(e.msg || '保存成功', {icon: 1}); _oldD2 = ($('#form-domain [name=domain_2_prefix]').val() || '') + ($('#form-domain [name=domain_2_suffix]').val() || ''); _oldDomain = $('#form-domain [name=domain]').val() || ''; }
                    },
                    error: function() { $btn.prop('disabled', false).html('<i class="fa fa-check"></i> 保存域名配置'); layer.msg('网络请求发生错误，请重试', {icon: 2}); }
                });
            }
            $('#btn-save-domain').on('click', function() {
                var _newD2 = ($('#form-domain [name=domain_2_prefix]').val() || '') + ($('#form-domain [name=domain_2_suffix]').val() || '');
                var _newDomain = $('#form-domain [name=domain]').val() || '';
                var _d2Changed = _newD2 !== _oldD2 && _oldD2 !== '';
                var _dChanged = _newDomain !== _oldDomain && _oldDomain !== '';
                if (_domainChangePrice > 0 && (_d2Changed || _dChanged)) {
                    var items = []; if (_d2Changed) items.push('二级域名'); if (_dChanged) items.push('独立域名');
                    openDomainConfirm(items);
                } else { _doSave(); }
            });
            $('#sdmDomainConfirmOk').on('click', function() {
                hideAppModal('#sdmDomainConfirmModal');
                _doSave();
            });
            $('#sdmDomainConfirmCancel').on('click', function() {
                hideAppModal('#sdmDomainConfirmModal');
            });
            $('#sdmDomainConfirmModal').on('click', function(e) {
                if (e.target === this) hideAppModal('#sdmDomainConfirmModal');
            });
        });
    }
    boot();
})();
</script>

