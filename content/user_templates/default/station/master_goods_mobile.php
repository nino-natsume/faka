<?php
defined('DC_ROOT') || exit('access denied!');
?>
<style>
    .uc-site-footer{display:none!important}
    .station-goods-app,.station-goods-app *{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
    .station-goods-app{--sgm-primary:var(--theme-primary,#667eea);--sgm-primary-rgb:var(--tp-rgb,102,126,234);--sgm-soft:rgba(var(--sgm-primary-rgb),.10);min-height:100vh;padding:12px 12px calc(28px + env(safe-area-inset-bottom,0px));background:#f5f6f8;color:#20242c;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif}
    .sgm-filter-wrap{position:sticky;top:calc(50px + env(safe-area-inset-top,0px));z-index:10;margin:-10px -12px 12px;padding:10px 12px 8px;background:rgba(245,245,246,.96);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px)}
    .sgm-filter-card{padding:12px;border-radius:10px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;box-shadow:var(--shadow-primary)}
    .sgm-search{display:grid;grid-template-columns:minmax(0,1fr) 82px;gap:8px}.sgm-input{width:100%;height:40px;padding:0 12px;border:1px solid #edf0f5;border-radius:13px;background:#f8f9fb;color:#20242c;font-size:16px!important;outline:none;-webkit-appearance:none;appearance:none}.sgm-input::placeholder{color:#9aa3af;font-size:13px}.sgm-input:focus{border-color:rgba(var(--sgm-primary-rgb),.35);box-shadow:0 0 0 3px rgba(var(--sgm-primary-rgb),.08)}.sgm-search-btn{height:40px;border:0;border-radius:13px;background:linear-gradient(135deg,var(--theme-primary,#667eea),var(--theme-secondary,#764ba2));color:#fff;font-size:13px;font-weight:900;display:flex;align-items:center;justify-content:center;gap:5px;white-space:nowrap;box-shadow:0 8px 18px rgba(var(--sgm-primary-rgb),.16)}
    .sgm-search-field{position:relative;min-width:0}.sgm-search-field .sgm-input{padding-right:40px}.sgm-search-clear{position:absolute;right:7px;top:50%;width:28px;height:28px;margin-top:-14px;border:0;border-radius:50%;background:#e9edf3;color:#8b95a5;font-size:14px;display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;pointer-events:none;transition:opacity .16s ease,visibility .16s ease,background .16s ease,color .16s ease,transform .16s ease}.sgm-search-clear.is-show{opacity:1;visibility:visible;pointer-events:auto}.sgm-search-clear:active{transform:scale(.92);background:var(--sgm-soft);color:var(--sgm-primary)}
    .sgm-toolbar{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:7px;margin-top:9px}.sgm-tool-btn{height:36px;border:0;border-radius:13px;background:var(--sgm-soft);color:var(--sgm-primary);font-size:12px;font-weight:900;display:flex;align-items:center;justify-content:center;gap:5px;white-space:nowrap}.sgm-tool-btn.is-primary{background:linear-gradient(135deg,var(--theme-primary,#667eea),var(--theme-secondary,#764ba2));color:#fff;box-shadow:0 8px 18px rgba(var(--sgm-primary-rgb),.16)}.sgm-tool-btn.is-success{background:#ecfdf5;color:#059669}.sgm-tool-btn.is-danger{background:#fff1f2;color:#e11d48}.sgm-tool-btn.is-plain{background:#f8f9fb;color:#697180}.sgm-tool-btn:disabled{opacity:.55}
    .sgm-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:7px;margin-bottom:12px}.sgm-stat{min-width:0;padding:10px 6px;border-radius:10px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;box-shadow:var(--shadow-primary);text-align:center}.sgm-stat-value{display:block;color:#20242c;font-size:16px;font-weight:900;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.sgm-stat-label{display:block;margin-top:4px;color:#8b95a5;font-size:10px;font-weight:800;white-space:nowrap}
    .sgm-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;touch-action:auto}.sgm-empty{grid-column:1/-1;padding:30px 16px;border-radius:10px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;text-align:center;color:#8b95a5;font-size:12px;box-shadow:var(--shadow-primary)}.sgm-card{position:relative;overflow:hidden;border-radius:10px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;box-shadow:var(--shadow-primary);transition:border-color .18s ease,box-shadow .18s ease}.sgm-card.is-hide{opacity:.86}.sgm-cover{position:relative;display:flex;align-items:center;justify-content:center;width:100%;aspect-ratio:1/1;overflow:hidden;background:linear-gradient(135deg,#eef2ff,#f8fafc);color:#c1c9d5;text-decoration:none}.sgm-cover img{width:100%;height:100%;object-fit:cover;display:block}.sgm-cover-default{width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;background:linear-gradient(135deg,#eef2ff,#f8fafc);color:#b8c1d0;font-size:11px;font-weight:900}.sgm-cover-default i{font-size:30px;color:#c1c9d5}.sgm-cover-stats{position:absolute;left:8px;right:8px;bottom:8px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:6px;z-index:2}.sgm-cover-stat{min-width:0;height:34px;padding:4px 6px;border-radius:12px;background:rgba(255,255,255,.88);color:#20242c;box-shadow:0 6px 16px rgba(15,23,42,.12);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);display:flex;align-items:center;justify-content:center;gap:5px}.sgm-cover-stat i{flex:0 0 auto;color:var(--sgm-primary);font-size:12px}.sgm-cover-stat.is-stock i{color:#059669}.sgm-cover-stat-text{min-width:0;line-height:1.05}.sgm-cover-stat-label{display:block;color:#7c8797;font-size:9px;font-weight:900}.sgm-cover-stat-value{display:block;color:#20242c;font-size:11px;font-weight:900;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.sgm-cover-fallback{font-size:30px;color:#c1c9d5}.sgm-status{position:absolute;left:8px;top:8px;z-index:2;display:inline-flex;align-items:center;height:22px;padding:0 7px;border-radius:999px;background:rgba(15,23,42,.56);color:#fff;font-size:10px;font-weight:900;backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px)}.sgm-status.is-show{background:rgba(5,150,105,.88)}.sgm-status.is-hide{background:rgba(225,29,72,.86)}
    .sgm-body{padding:10px}.sgm-title{display:block;margin:0;color:#20242c;font-size:13px;font-weight:900;line-height:1.35;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.sgm-custom{margin-top:5px;color:#8b95a5;font-size:11px;line-height:1.35;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.sgm-price-grid{display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:8px}.sgm-price-box{min-width:0;padding:7px 6px;border-radius:12px;background:#f8f9fb}.sgm-price-label{display:block;color:#8b95a5;font-size:10px;font-weight:800;line-height:1.1}.sgm-price-value{display:block;margin-top:4px;color:#20242c;font-size:12px;font-weight:900;line-height:1.15;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.sgm-price-value.is-profit{color:#059669}.sgm-mini-meta{display:grid;grid-template-columns:1fr 1fr;gap:5px;margin-top:7px}.sgm-mini-pill{min-width:0;height:24px;padding:0 6px;border-radius:999px;background:var(--sgm-soft);color:var(--sgm-primary);font-size:10px;font-weight:900;display:flex;align-items:center;justify-content:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.sgm-mini-pill.is-gray{background:#f8f9fb;color:#8b95a5}
    .sgm-actions{display:grid;grid-template-columns:1fr;gap:7px;margin-top:8px}.sgm-switch{height:32px;width:100%;padding:0 8px;border:1px solid rgba(var(--sgm-primary-rgb),.10);border-radius:13px;background:#f8f9fb;color:#7c8797;display:flex;align-items:center;justify-content:space-between;gap:6px;font-size:11px;font-weight:900}.sgm-switch-track{position:relative;flex:0 0 auto;width:28px;height:16px;border-radius:999px;background:#d7dde8}.sgm-switch-track:after{content:'';position:absolute;left:2px;top:2px;width:12px;height:12px;border-radius:999px;background:#fff;box-shadow:0 1px 4px rgba(15,23,42,.18);transition:left .18s ease}.sgm-switch.is-on{background:var(--sgm-soft);border-color:rgba(var(--sgm-primary-rgb),.18);color:var(--sgm-primary)}.sgm-switch.is-on .sgm-switch-track{background:var(--sgm-primary)}.sgm-switch.is-on .sgm-switch-track:after{left:14px}.sgm-switch:disabled{opacity:.72}.sgm-edit{height:32px;width:100%;border:0;border-radius:13px;background:var(--sgm-soft);color:var(--sgm-primary);font-size:12px;font-weight:900;display:flex;align-items:center;justify-content:center;gap:5px}
    .sgm-pager{display:none;margin-top:12px;padding:0;background:transparent;box-shadow:none}.sgm-pager.is-visible{display:block}.sgm-pager-row{display:grid;grid-template-columns:72px minmax(0,1fr) 72px;gap:8px;align-items:center}.sgm-page-btn{height:32px;border:0;border-radius:999px;background:#fff;color:var(--sgm-primary);font-size:12px;font-weight:900;display:flex;align-items:center;justify-content:center;gap:4px;box-shadow:0 4px 14px rgba(31,52,88,.06)}.sgm-page-btn:disabled{background:#f8f9fb;color:#c0c7d2;box-shadow:none}.sgm-page-current{height:32px;border-radius:999px;background:#fff;color:#20242c;font-size:12px;font-weight:900;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(31,52,88,.06)}.sgm-page-numbers{display:flex;justify-content:center;gap:5px;flex-wrap:wrap;margin-top:8px}.sgm-page-num,.sgm-page-ellipsis{min-width:26px;height:26px;padding:0 8px;border:0;border-radius:999px;background:transparent;color:#8b95a5;font-size:11px;font-weight:900;display:inline-flex;align-items:center;justify-content:center}.sgm-page-num.is-active{background:var(--sgm-soft);color:var(--sgm-primary)}.layui-form-switch{box-sizing:content-box}.layui-form-onswitch{border-color:var(--sgm-primary)!important;background-color:var(--sgm-primary)!important}
    .sgm-cover-meta{position:absolute;left:0px;bottom:0px;z-index:2;height:26px;padding:0 8px;border-radius:0;background:#ff6b57cf;color:#fff;font-size:11px;font-weight:500;line-height:26px;letter-spacing:.01em;box-shadow:0 6px 14px rgba(255,107,87,.25);border-radius: 0px 10px 0px 0px;}
    .sgm-app-modal-mask{position:fixed;inset:0;z-index:19999;background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:opacity .25s,visibility .25s}.sgm-app-modal-mask.is-show{opacity:1;visibility:visible}.sgm-app-modal{width:min(88vw,340px);background:#fff;border-radius:20px;overflow:hidden;transform:translateY(24px) scale(.96);transition:transform .28s cubic-bezier(.22,.61,.36,1);box-shadow:0 20px 50px rgba(0,0,0,.18)}.sgm-app-modal-mask.is-show .sgm-app-modal{transform:translateY(0) scale(1)}.sgm-app-modal-header{padding:22px 22px 0;text-align:center}.sgm-app-modal-icon{width:52px;height:52px;margin:0 auto 12px;border-radius:50%;background:linear-gradient(135deg,#f8dfad,#e8bb72);display:flex;align-items:center;justify-content:center;font-size:24px;color:#7a5a2b}.sgm-app-modal-title{font-size:17px;font-weight:800;color:#252d3b}.sgm-app-modal-body{padding:16px 22px 6px;text-align:center}.sgm-app-modal-text{padding:2px 0 8px;color:#5f6673;font-size:13px;line-height:1.8}.sgm-app-modal-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f2f4f7;font-size:13px;color:#5f6673}.sgm-app-modal-row:last-child{border-bottom:none}.sgm-app-modal-row b{color:#252d3b;font-weight:700}.sgm-app-modal-foot{display:flex;gap:10px;padding:10px 22px 22px}.sgm-app-modal-btn{flex:1;height:44px;border:none;border-radius:14px;font-size:15px;font-weight:700;cursor:pointer;transition:.15s}.sgm-app-modal-btn:disabled{opacity:.65}.sgm-app-modal-cancel{background:#f3f4f6;color:#5f6673}.sgm-app-modal-cancel:active{background:#e8ebf0}.sgm-app-modal-confirm{background:linear-gradient(135deg,#f1ca86,#e4ad5e);color:#3d2a13;box-shadow:0 6px 16px rgba(222,174,92,.22)}.sgm-app-modal-confirm:active{transform:scale(.97)}.sgm-app-premium-field{text-align:left}.sgm-app-premium-label{display:flex;align-items:center;gap:6px;margin-bottom:9px;color:#252d3b;font-size:13px;font-weight:800}.sgm-app-premium-input{width:100%;height:44px;padding:0 14px;border:1px solid #edf0f5;border-radius:14px;background:#f8f9fb;color:#252d3b;font-size:16px!important;outline:none;-webkit-appearance:none;appearance:none}.sgm-app-premium-input:focus{border-color:#e8bb72;box-shadow:0 0 0 4px rgba(232,187,114,.14);background:#fff}.sgm-app-premium-tips{display:block;margin-top:8px;color:#77808f;font-size:12px;line-height:1.65}.sgm-app-premium-tips b{color:#252d3b}.sgm-app-modal-warn{margin-top:12px;padding:10px 12px;border-radius:13px;background:#fff7ed;border:1px solid #fed7aa;color:#9a5b14;font-size:12px;line-height:1.65;text-align:left}
    .sgm-app-modal-mask{--sgm-primary:var(--theme-primary,#667eea);--sgm-primary-rgb:var(--tp-rgb,102,126,234);--sgm-soft:rgba(var(--sgm-primary-rgb),.10)}.sgm-app-modal-icon{background:linear-gradient(135deg,var(--theme-primary,#667eea),var(--theme-secondary,#764ba2));color:#fff}.sgm-app-modal-confirm{background:linear-gradient(135deg,var(--theme-primary,#667eea),var(--theme-secondary,#764ba2));color:#fff;box-shadow:0 6px 16px rgba(var(--sgm-primary-rgb),.22)}.sgm-app-premium-input:focus{border-color:rgba(var(--sgm-primary-rgb),.35);box-shadow:0 0 0 4px rgba(var(--sgm-primary-rgb),.12);background:#fff}.sgm-app-modal-warn{background:#fff7ed;border-color:#fed7aa;color:#9a5b14}.sgm-app-modal-notice{margin-top:12px;padding:12px;border-radius:13px;background:#eef5ff;border:1px solid #dbeafe;color:#5f6673;font-size:12px;line-height:1.75;text-align:left}.sgm-app-modal-notice-title{display:flex;align-items:center;gap:6px;margin-bottom:6px;color:#252d3b;font-size:13px;font-weight:900}.sgm-app-modal-notice-text b{color:#252d3b;font-weight:900}
    .sgm-app-premium-field + .sgm-app-premium-field{margin-top:12px}.sgm-app-premium-input[readonly]{background:#f3f4f6;color:#8b95a5;-webkit-text-fill-color:#8b95a5}
    @media(max-width:360px){.station-goods-app{padding-left:10px;padding-right:10px}.sgm-list{gap:8px}.sgm-card{position:relative;overflow:hidden;border-radius:10px;background:linear-gradient(0deg, #fff, #f3f5f8);border:2px solid #fff;box-shadow:var(--shadow-primary);transition:border-color .18s ease,box-shadow .18s ease}.sgm-body{padding:9px}.sgm-toolbar{gap:6px}.sgm-tool-btn{font-size:11px}.sgm-stats{gap:6px}.sgm-stat-value{font-size:14px}.sgm-title{font-size:12px}.sgm-switch,.sgm-edit{height:30px;font-size:11px}.sgm-price-grid{gap:5px}.sgm-price-value{font-size:11px}}
</style>

<main class="station-goods-app">
    <div class="sgm-filter-wrap">
        <section class="sgm-filter-card">
            <form id="sgmSearch" class="layui-form">
                <div class="sgm-search">
                    <div class="sgm-search-field">
                        <input type="search" name="title" id="sgmSearchTitle" class="sgm-input" placeholder="搜索商品名称 / 自定义名称">
                        <button type="button" class="sgm-search-clear" id="sgmSearchClear" aria-label="清空搜索"><i class="fa fa-times-circle"></i></button>
                    </div>
                    <button type="submit" class="sgm-search-btn"><i class="fa fa-search"></i> 搜索</button>
                </div>
                <div class="sgm-toolbar">
                    <button type="button" class="sgm-tool-btn is-success" id="sgmBatchShow"><i class="fa fa-eye"></i> 批量上架</button>
                    <button type="button" class="sgm-tool-btn is-danger" id="sgmBatchHide"><i class="fa fa-eye-slash"></i> 批量下架</button>
                    <button type="button" class="sgm-tool-btn" id="sgmPremium"><i class="fa fa-percent"></i> 一键调价</button>
                </div>
            </form>
        </section>
    </div>

    <section class="sgm-stats">
        <div class="sgm-stat"><span class="sgm-stat-value" id="sgmTotal">-</span><span class="sgm-stat-label">总数</span></div>
        <div class="sgm-stat"><span class="sgm-stat-value" id="sgmVisible">-</span><span class="sgm-stat-label">上架</span></div>
        <div class="sgm-stat"><span class="sgm-stat-value" id="sgmHidden">-</span><span class="sgm-stat-label">下架</span></div>
        <div class="sgm-stat"><span class="sgm-stat-value" id="sgmAvgPremium">-</span><span class="sgm-stat-label">均加价</span></div>
    </section>

    <section class="sgm-list" id="goodsMobileList"></section>
    <nav class="sgm-pager" id="sgmPager">
        <div class="sgm-pager-row">
            <button type="button" class="sgm-page-btn" id="sgmPrevPage"><i class="fa fa-angle-left"></i> 上一页</button>
            <div class="sgm-page-current" id="sgmPageCurrent">1 / 1</div>
            <button type="button" class="sgm-page-btn" id="sgmNextPage">下一页 <i class="fa fa-angle-right"></i></button>
        </div>
     
    </nav>
</main>

<div class="sgm-app-modal-mask" id="sgmBatchModal">
    <div class="sgm-app-modal">
        <div class="sgm-app-modal-header">
            <div class="sgm-app-modal-icon" id="sgmBatchIcon"><i class="fa fa-check"></i></div>
            <div class="sgm-app-modal-title" id="sgmBatchTitle">批量操作</div>
        </div>
        <div class="sgm-app-modal-body">
            <div class="sgm-app-modal-text" id="sgmBatchText">确定要批量操作所有商品吗？</div>
            <div class="sgm-app-modal-row"><span>操作范围</span><b id="sgmBatchScope">全部商品</b></div>
            <div class="sgm-app-modal-row"><span>商品数量</span><b id="sgmBatchCount">0 个</b></div>
        </div>
        <div class="sgm-app-modal-foot">
            <button type="button" class="sgm-app-modal-btn sgm-app-modal-cancel">取消</button>
            <button type="button" class="sgm-app-modal-btn sgm-app-modal-confirm" id="sgmBatchConfirm">确认</button>
        </div>
    </div>
</div>

<div class="sgm-app-modal-mask" id="sgmPremiumModal">
    <div class="sgm-app-modal">
        <div class="sgm-app-modal-header">
            <div class="sgm-app-modal-icon"><i class="fa fa-percent"></i></div>
            <div class="sgm-app-modal-title">一键调价</div>
        </div>
        <form id="sgmPremiumForm">
            <div class="sgm-app-modal-body">
                <div class="sgm-app-premium-field">
                    <label class="sgm-app-premium-label"><i class="fa fa-percent"></i> 加价比例</label>
                    <input type="number" name="premium" id="sgmPremiumValue" class="sgm-app-premium-input" value="" placeholder="例如：25" min="0" step="0.01">
                    <span class="sgm-app-premium-tips">直接填百分比数字。填 <b>25</b> 表示加价 <b>25%</b>，填 <b>0</b> 表示不加价。</span>
                </div>
                <div class="sgm-app-modal-warn"><i class="fa fa-exclamation-triangle"></i> 注意：此操作会统一修改你店铺里<b>所有商品</b>的加价比例，之前单独设置过的商品也会被覆盖。</div>
                <div class="sgm-app-modal-notice">
                    <div class="sgm-app-modal-notice-title"><i class="fa fa-info-circle"></i> 为什么我自己看到的价格和设置的不一样？</div>
                    <div class="sgm-app-modal-notice-text">加价对所有访客都生效，但不同会员等级的“底价”不同。<b>您自己登录后看到的价格 = 您的等级价 + 加价</b>，会比普通顾客的售价偏低，这是正常的。商品管理页显示的“前台售价”是普通顾客（未登录）看到的价格。</div>
                </div>
            </div>
            <div class="sgm-app-modal-foot">
                <button type="button" class="sgm-app-modal-btn sgm-app-modal-cancel">取消</button>
                <button type="submit" class="sgm-app-modal-btn sgm-app-modal-confirm" id="sgmPremiumSubmit">保存</button>
            </div>
        </form>
    </div>
</div>

<div class="sgm-app-modal-mask" id="sgmEditModal">
    <div class="sgm-app-modal">
        <div class="sgm-app-modal-header">
            <div class="sgm-app-modal-title">编辑商品</div>
        </div>
        <form id="sgmEditForm">
            <div class="sgm-app-modal-body">
                <div class="sgm-app-premium-field">
                    <label class="sgm-app-premium-label"><i class="fa fa-cube"></i> 主站商品名</label>
                    <input type="text" id="sgmEditTitle" class="sgm-app-premium-input" value="" readonly>
                    <span class="sgm-app-premium-tips">这是主站设置的原始商品名称，仅供参考，无法修改。</span>
                </div>
                <div class="sgm-app-premium-field">
                    <label class="sgm-app-premium-label"><i class="fa fa-pencil"></i> 自定义名称</label>
                    <input type="text" name="custom_name" id="sgmEditCustomName" class="sgm-app-premium-input" value="" placeholder="留空则使用主站商品名">
                    <span class="sgm-app-premium-tips">给这个商品起一个你自己的名字，顾客在你的店铺看到的将是这个名称。</span>
                </div>
                <div class="sgm-app-premium-field">
                    <label class="sgm-app-premium-label"><i class="fa fa-percent"></i> 加价比例</label>
                    <input type="number" name="premium" id="sgmEditPremium" class="sgm-app-premium-input" value="" placeholder="例如：25" min="0" step="0.01">
                    <span class="sgm-app-premium-tips">直接填百分比数字。填 <b>25</b> 表示加价 <b>25%</b>，填 <b>0</b> 表示不加价。</span>
                </div>
                <input type="hidden" name="goods_id" id="sgmEditGoodsId" value="">
                <input type="hidden" name="token" value="<?= LoginAuth::genToken() ?>">
            </div>
            <div class="sgm-app-modal-foot">
                <button type="button" class="sgm-app-modal-btn sgm-app-modal-cancel">取消</button>
                <button type="submit" class="sgm-app-modal-btn sgm-app-modal-confirm" id="sgmEditSubmit">保存</button>
            </div>
        </form>
    </div>
</div>

<script>
layui.use(['form', 'layer'], function(){
    var form = layui.form;
    var layer = layui.layer;
    var nullImg = '<?= DC_URL ?>admin/views/images/null.png';
    var state = { page: 1, limit: 10, total: 0, loading: false, items: [], title: '' };
    var pendingBatch = null;
    var premiumSubmitting = false;
    var editSubmitting = false;

    function safeText(value) {
        return $('<div>').text(value == null || value === '' ? '' : String(value)).html();
    }

    function getPremiumValue(value) {
        if (value === undefined || value === null || value === '' || value === 'undefined') return 10;
        var num = parseFloat(value);
        return isNaN(num) ? 10 : num;
    }

    function defaultCoverHtml() {
        return '<div class="sgm-cover-default"><i class="fa fa-picture-o"></i><span>未设置商品图</span></div>';
    }

    window.sgmCoverFallback = function(img) {
        if (!img || !img.parentNode) return;
        img.onerror = null;
        img.style.display = 'none';
        if (!img.parentNode.querySelector('.sgm-cover-default')) {
            $(img).after(defaultCoverHtml());
        }
    };

    function coverHtml(src) {
        src = src || '';
        if (!src || /(?:^|\/)(null|theme)\.png(?:\?|$)/i.test(src)) {
            return defaultCoverHtml();
        }
        return '<img class="sgm-cover-img" src="' + safeText(src) + '" alt="" onerror="sgmCoverFallback(this)">';
    }

    function goodsSwitchHtml(id, isShow) {
        return '<button type="button" class="sgm-switch ' + (isShow ? 'is-on' : 'is-off') + '" data-action="toggle" data-id="' + safeText(id) + '" data-next="' + (isShow ? 'n' : 'y') + '"><span>' + (isShow ? '上架中' : '已下架') + '</span><span class="sgm-switch-track"></span></button>';
    }

    function renderList(list) {
        var $list = $('#goodsMobileList');
        if (!list.length) {
            $list.html('<div class="sgm-empty">当前没有可管理的主站商品</div>');
            updateMoreState();
            return;
        }
        var html = list.map(function(item){
            var isShow = item.is_show === 'y';
            var premium = getPremiumValue(item.premium);
            var cover = item.cover || nullImg;
            return '<article class="sgm-card ' + (isShow ? 'is-show' : 'is-hide') + '">' +
                '<a href="javascript:;" class="sgm-cover" data-action="img" data-cover="' + safeText(cover) + '" data-title="' + safeText(item.title || '未命名商品') + '">' +
                    coverHtml(cover) +
                    '<span class="sgm-status ' + (isShow ? 'is-show' : 'is-hide') + '">' + (isShow ? '上架' : '下架') + '</span>' +
                    '<div class="sgm-cover-meta">销量:' + safeText(item.sales || 0) + '  |  库存:' + safeText(item.stock || 0) + '</div>' +
                '</a>' +
                '<div class="sgm-body">' +
                    '<h3 class="sgm-title">' + safeText(item.title || '未命名商品') + '</h3>' +
                    '<div class="sgm-custom">自定义名称：' + safeText(item.custom_name || '未设置') + '</div>' +
                    '<div class="sgm-price-grid">' +
                        '<div class="sgm-price-box"><span class="sgm-price-label">售价</span><span class="sgm-price-value">&yen;' + safeText(item.sell_yuan || '0.00') + '</span></div>' +
                        '<div class="sgm-price-box"><span class="sgm-price-label">利润</span><span class="sgm-price-value is-profit">+&yen;' + safeText(item.profit_yuan || '0.00') + '</span></div>' +
                    '</div>' +
                    '<div class="sgm-mini-meta">' +
                        '<span class="sgm-mini-pill is-gray">成本 &yen;' + safeText(item.cost_yuan || '0.00') + '</span>' +
                        '<span class="sgm-mini-pill">加价 ' + safeText(premium) + '%</span>' +
                    '</div>' +
                    '<div class="sgm-actions">' +
                        goodsSwitchHtml(item.id, isShow) +
                        '<button type="button" class="sgm-edit" data-action="edit" data-id="' + safeText(item.id) + '"><i class="fa fa-pencil"></i> 编辑</button>' +
                    '</div>' +
                '</div>' +
            '</article>';
        }).join('');
        $list.html(html);
        updateMoreState();
    }

    function updateMoreState() {
        var totalPages = Math.max(1, Math.ceil((parseInt(state.total, 10) || 0) / state.limit));
        var hasData = state.total > 0;
        if (state.page > totalPages) state.page = totalPages;
        $('#sgmPager').toggleClass('is-visible', hasData);
        $('#sgmPrevPage').prop('disabled', state.loading || state.page <= 1);
        $('#sgmNextPage').prop('disabled', state.loading || state.page >= totalPages);
        $('#sgmPageCurrent').text(state.page + ' / ' + totalPages);
        renderPageNumbers(totalPages);
    }

    function renderPageNumbers(totalPages) {
        var $nums = $('#sgmPageNumbers');
        if (!state.total || totalPages <= 1) {
            $nums.html('');
            return;
        }
        var pages = [];
        pages.push(1);
        var start = Math.max(2, state.page - 1);
        var end = Math.min(totalPages - 1, state.page + 1);
        if (start > 2) pages.push('...');
        for (var i = start; i <= end; i++) pages.push(i);
        if (end < totalPages - 1) pages.push('...');
        if (totalPages > 1) pages.push(totalPages);
        var html = pages.map(function(p){
            if (p === '...') return '<span class="sgm-page-ellipsis">...</span>';
            return '<button type="button" class="sgm-page-num' + (p === state.page ? ' is-active' : '') + '" data-page="' + p + '">' + p + '</button>';
        }).join('');
        $nums.html(html);
    }

    function readFilters() {
        state.title = $.trim($('#sgmSearch [name="title"]').val() || '');
    }

    function loadGoods(reset) {
        if (state.loading) return;
        readFilters();
        if (reset) {
            state.page = 1;
            state.total = 0;
            state.items = [];
            $('#goodsMobileList').html('<div class="sgm-empty">正在加载商品...</div>');
            updateMoreState();
        }
        state.loading = true;
        updateMoreState();
        $.ajax({
            url: '?action=master_goods_index',
            type: 'GET',
            dataType: 'json',
            data: { page: state.page, limit: state.limit, title: state.title },
            success: function(res){
                if (!res || (res.code && res.code != 0)) {
                    $('#goodsMobileList').html('<div class="sgm-empty">' + safeText(res && res.msg ? res.msg : '商品加载失败') + '</div>');
                    return;
                }
                var data = res.data || [];
                state.total = parseInt(res.count || res.total || data.length || 0, 10) || 0;
                state.items = data;
                renderList(state.items);
            },
            error: function(err){
                $('#goodsMobileList').html('<div class="sgm-empty">' + safeText(err.responseJSON && err.responseJSON.msg ? err.responseJSON.msg : '请求失败，请重试') + '</div>');
            },
            complete: function(){
                state.loading = false;
                updateMoreState();
            }
        });
    }

    function updateStats() {
        $.ajax({
            url: '?action=master_goods_stats',
            dataType: 'json',
            success: function(res) {
                if (!res || res.code !== 0) return;
                $('#sgmTotal').text((parseInt(res.visible, 10) || 0) + (parseInt(res.hidden, 10) || 0));
                $('#sgmVisible').text(res.visible);
                $('#sgmHidden').text(res.hidden);
                $('#sgmAvgPremium').text(res.avg_premium + '%');
            }
        });
    }

    function showAppModal(selector) {
        $(selector).addClass('is-show');
    }

    function hideAppModal(selector) {
        $(selector).removeClass('is-show');
    }

    function openPremiumDialog() {
        $('#sgmPremiumValue').val('');
        $('#sgmPremiumSubmit').prop('disabled', false).text('保存');
        showAppModal('#sgmPremiumModal');
        setTimeout(function(){ $('#sgmPremiumValue').trigger('focus'); }, 180);
    }

    function batchToggle(url, text) {
        var count = parseInt(state.total, 10) || 0;
        if (!count) {
            layer.msg('当前没有可批量操作的商品');
            return;
        }
        var scopeText = state.title ? '全部搜索结果' : '全部商品';
        pendingBatch = { url: url, text: text, scope: 'all', title: state.title || '', count: count };
        $('#sgmBatchTitle').text('批量' + text);
        $('#sgmBatchText').text('确定要批量' + text + (state.title ? '当前搜索结果中的所有商品吗？' : '所有商品吗？'));
        $('#sgmBatchScope').text(scopeText);
        $('#sgmBatchCount').text(count + ' 个');
        $('#sgmBatchConfirm').prop('disabled', false).text('确认' + text);
        $('#sgmBatchIcon').html(text === '上架' ? '<i class="fa fa-eye"></i>' : '<i class="fa fa-eye-slash"></i>');
        showAppModal('#sgmBatchModal');
    }

    function submitBatchToggle() {
        if (!pendingBatch) {
            hideAppModal('#sgmBatchModal');
            layer.msg('当前没有可批量操作的商品');
            return;
        }
        var batch = pendingBatch;
        hideAppModal('#sgmBatchModal');
        var loadBatch = layer.load(2);
        $('#sgmBatchShow,#sgmBatchHide,#sgmBatchConfirm').prop('disabled', true);
        $.ajax({
            url: batch.url,
            type: 'POST',
            dataType: 'json',
            data: { scope: batch.scope || 'all', title: batch.title || '', token: '<?= LoginAuth::genToken() ?>' },
            success: function(e) {
                if (e && e.code == 400) {
                    return layer.msg(e.msg || '操作失败');
                }
                var doneCount = e && e.data && e.data.count !== undefined ? parseInt(e.data.count, 10) : 0;
                layer.msg(doneCount > 0 ? ('已批量' + batch.text + doneCount + ' 个商品') : ('已批量' + batch.text));
                loadGoods(false);
                updateStats();
            },
            error: function(err) {
                layer.msg(err.responseJSON && err.responseJSON.msg ? err.responseJSON.msg : '请求失败，请重试');
            },
            complete: function() {
                layer.close(loadBatch);
                $('#sgmBatchShow,#sgmBatchHide,#sgmBatchConfirm').prop('disabled', false);
                pendingBatch = null;
            }
        });
    }

    function submitPremium() {
        if (premiumSubmitting) return;
        var premium = $.trim($('#sgmPremiumValue').val() || '');
        if (premium === '') {
            layer.msg('请填写加价比例');
            $('#sgmPremiumValue').trigger('focus');
            return;
        }
        var num = parseFloat(premium);
        if (isNaN(num) || num < 0) {
            layer.msg('加价比例不能小于 0');
            $('#sgmPremiumValue').trigger('focus');
            return;
        }
        premiumSubmitting = true;
        $('#sgmPremiumSubmit').prop('disabled', true).text('保存中...');
        var loadPremium = layer.load(2);
        $.ajax({
            type: 'POST',
            url: '?action=master_goods_premium_ajax',
            dataType: 'json',
            data: { premium: premium, token: '<?= LoginAuth::genToken() ?>' },
            success: function(e) {
                if (e && e.code == 400) {
                    return layer.msg(e.msg || '保存失败');
                }
                hideAppModal('#sgmPremiumModal');
                layer.msg('加价设置已保存');
                loadGoods(false);
                updateStats();
            },
            error: function(err) {
                layer.msg(err.responseJSON && err.responseJSON.msg ? err.responseJSON.msg : '请求失败，请重试');
            },
            complete: function() {
                layer.close(loadPremium);
                premiumSubmitting = false;
                $('#sgmPremiumSubmit').prop('disabled', false).text('保存');
            }
        });
    }

    function openEditDialog(goodsId) {
        var id = String(goodsId || '');
        var item = null;
        $.each(state.items || [], function(_, row) {
            if (String(row.id) === id) {
                item = row;
                return false;
            }
        });
        if (!item) {
            layer.msg('商品信息不存在，请刷新后重试');
            return;
        }
        $('#sgmEditGoodsId').val(item.id || '');
        $('#sgmEditTitle').val(item.title || '未命名商品');
        $('#sgmEditCustomName').val(item.custom_name || '');
        $('#sgmEditPremium').val(getPremiumValue(item.premium));
        $('#sgmEditSubmit').prop('disabled', false).text('保存');
        showAppModal('#sgmEditModal');
        setTimeout(function(){ $('#sgmEditCustomName').trigger('focus'); }, 180);
    }

    function submitEdit() {
        if (editSubmitting) return;
        var premium = $.trim($('#sgmEditPremium').val() || '');
        if (premium === '') {
            layer.msg('请填写加价比例');
            $('#sgmEditPremium').trigger('focus');
            return;
        }
        var num = parseFloat(premium);
        if (isNaN(num) || num < 0) {
            layer.msg('加价比例不能小于 0');
            $('#sgmEditPremium').trigger('focus');
            return;
        }
        editSubmitting = true;
        $('#sgmEditSubmit').prop('disabled', true).text('保存中...');
        var loadEdit = layer.load(2);
        $.ajax({
            type: 'POST',
            url: '?action=master_goods_edit_ajax',
            dataType: 'json',
            data: $('#sgmEditForm').serialize(),
            success: function(e) {
                if (e && e.code == 400) {
                    return layer.msg(e.msg || '保存失败');
                }
                hideAppModal('#sgmEditModal');
                layer.msg('商品设置已保存');
                loadGoods(false);
                updateStats();
            },
            error: function(err) {
                layer.msg(err.responseJSON && err.responseJSON.msg ? err.responseJSON.msg : '请求失败，请重试');
            },
            complete: function() {
                layer.close(loadEdit);
                editSubmitting = false;
                $('#sgmEditSubmit').prop('disabled', false).text('保存');
            }
        });
    }

    function updateSearchClear() {
        $('#sgmSearchClear').toggleClass('is-show', $.trim($('#sgmSearchTitle').val() || '') !== '');
    }

    window.table = { reload: function(){ loadGoods(false); updateStats(); } };

    $('#sgmSearch').on('submit', function(e){ e.preventDefault(); updateSearchClear(); loadGoods(true); return false; });
    $('#sgmSearchTitle').on('input propertychange', updateSearchClear);
    $('#sgmSearchClear').on('click', function(){
        var hadSearch = $.trim(state.title || '') !== '';
        $('#sgmSearchTitle').val('');
        updateSearchClear();
        $('#sgmSearchTitle').trigger('focus');
        if (hadSearch) loadGoods(true);
    });
    $('#sgmBatchShow').on('click', function(){ batchToggle('?action=master_goods_show', '上架'); });
    $('#sgmBatchHide').on('click', function(){ batchToggle('?action=master_goods_hide', '下架'); });
    $('#sgmPremium').on('click', openPremiumDialog);
    $('#sgmBatchConfirm').on('click', submitBatchToggle);
    $('#sgmPremiumForm').on('submit', function(e){ e.preventDefault(); submitPremium(); return false; });
    $('#sgmEditForm').on('submit', function(e){ e.preventDefault(); submitEdit(); return false; });
    $('.sgm-app-modal-cancel').on('click', function(){
        var $mask = $(this).closest('.sgm-app-modal-mask');
        if ($mask.attr('id') === 'sgmBatchModal') pendingBatch = null;
        hideAppModal('#' + $mask.attr('id'));
    });
    $('.sgm-app-modal-mask').on('click', function(e){
        if (e.target !== this) return;
        if (this.id === 'sgmBatchModal') pendingBatch = null;
        hideAppModal('#' + this.id);
    });
    $('#sgmPrevPage').on('click', function(){ if(state.page > 1 && !state.loading){ state.page -= 1; loadGoods(false); } });
    $('#sgmNextPage').on('click', function(){
        var totalPages = Math.max(1, Math.ceil((parseInt(state.total, 10) || 0) / state.limit));
        if(state.page < totalPages && !state.loading){ state.page += 1; loadGoods(false); }
    });
    $('#sgmPageNumbers').on('click', '.sgm-page-num', function(){
        var page = parseInt($(this).data('page'), 10);
        if(page > 0 && page !== state.page && !state.loading){ state.page = page; loadGoods(false); }
    });

    $('#goodsMobileList').on('click', '[data-action="img"]', function(){
        var src = $(this).data('cover') || nullImg;
        layer.photos({ photos: { title: $(this).data('title') || '商品图片', start: 0, data: [{ alt: $(this).data('title') || '商品图片', pid: 1, src: src }] } });
    });

    $('#goodsMobileList').on('click', '[data-action="edit"]', function(){ openEditDialog($(this).data('id')); });

    $('#goodsMobileList').on('click', '[data-action="toggle"]', function(){
        var $btn = $(this);
        if ($btn.prop('disabled')) return;
        var id = $btn.data('id');
        var next = $btn.data('next');
        $btn.prop('disabled', true);
        var loadSwitch = layer.load(2);
        $.ajax({
            url: '?action=master_goods_switch', type: 'POST', dataType: 'json',
            data: { id: id, is_show: next, token: '<?= LoginAuth::genToken() ?>' },
            success: function(e) {
                if(e.code == 400) return layer.msg(e.msg);
                layer.msg(next === 'y' ? '商品已上架' : '商品已下架');
                loadGoods(false);
                updateStats();
            },
            error: function(err) { layer.msg(err.responseJSON && err.responseJSON.msg ? err.responseJSON.msg : '请求失败，请重试'); },
            complete: function() { layer.close(loadSwitch); $btn.prop('disabled', false); }
        });
    });

    updateStats();
    loadGoods(true);
});
</script>

<script>
    $('#menu-station').addClass('open');
    $('#menu-station > ul').css('display', 'block');
    $('#menu-station > a > i.nav_right').attr('class', 'fa fa-angle-down nav_right');
    $('#menu-station-master_goods').addClass('menu-current');
</script>
