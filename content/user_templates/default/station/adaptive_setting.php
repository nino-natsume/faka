<?php
defined('DC_ROOT') || exit('access denied!');

$domain2Full = '';
if (!empty($userStation['domain_2_prefix']) && !empty($userStation['domain_2_suffix'])) {
    $domain2Full = htmlspecialchars($userStation['domain_2_prefix'] . $userStation['domain_2_suffix']);
}
$domainFull = !empty($userStation['domain']) ? htmlspecialchars($userStation['domain']) : '';
$_slugVal = $userStation['slug'] ?? '';
if ($_slugVal === 'NULL' || $_slugVal === null) $_slugVal = '';
$stationSlug = $_slugVal;
$stationShareLink = $stationSlug !== '' ? rtrim(Option::get('blogurl'), '/') . '/s/' . $stationSlug : '';
$_rollNoticePreview = mb_strlen($userStation['roll_notice'] ?? '') > 60 ? mb_substr($userStation['roll_notice'], 0, 60) . '...' : ($userStation['roll_notice'] ?? '');
$_homeNoticePreview = strip_tags($userStation['home_notice'] ?? '');
$_homeNoticePreview = mb_strlen($_homeNoticePreview) > 60 ? mb_substr($_homeNoticePreview, 0, 60) . '...' : $_homeNoticePreview;
$_domainChangePrice = (float)(Option::get('station_domain_change_price') ?: 0);
$_cnameDomain = trim((string)Option::get('station_cname_domain'));
$_stationLogo = $userStation['logo'] ?? '';
$_stationFavicon = $userStation['favicon'] ?? '';
$_siteDesc = $userStation['site_description'] ?? '';
$_siteKey = $userStation['site_key'] ?? '';
$_logTitleStyle = (int)($userStation['log_title_style'] ?? 0);
$_icp = $userStation['icp'] ?? '';
$_footerInfo = $userStation['footer_info'] ?? '';
$_userAgreement = $userStation['user_agreement'] ?? '';
$_privacyPolicy = $userStation['privacy_policy'] ?? '';
$_agreementSiteName = htmlspecialchars($userStation['name'] ?: Option::get('blogname') ?: '本站');
if (empty($_userAgreement)) {
    $_userAgreement = '<h2>用户服务协议</h2>'
        . '<p>欢迎使用 <strong>' . $_agreementSiteName . '</strong>（以下简称"本站"）提供的虚拟商品自动发卡服务。请您在注册或使用本站服务之前，仔细阅读本协议。注册即表示您已充分阅读、理解并同意接受本协议的全部内容。</p>'
        . '<h3>一、服务说明</h3>'
        . '<p>本站是一个虚拟商品在线交易平台，提供卡密、兑换码、账号、充值服务等数字商品的自动化销售与发放服务。用户可通过本站选购商品并在线支付，系统将自动发送商品信息至用户。</p>'
        . '<h3>二、用户账号</h3>'
        . '<ol><li>用户注册时应提供真实、准确的信息，并妥善保管账号及密码。</li>'
        . '<li>用户应对其账号下的一切行为和交易负责。因账号被盗、密码泄露等非本站原因导致的损失，由用户自行承担。</li>'
        . '<li>用户不得将账号转让、出借给他人使用。</li>'
        . '<li>本站有权对异常账号（如批量注册、恶意下单等）进行限制或封禁。</li></ol>'
        . '<h3>三、交易规则</h3>'
        . '<ol><li>用户下单前应仔细阅读商品描述、使用说明及注意事项，下单付款即视为认可商品内容。</li>'
        . '<li>虚拟商品具有可复制性和不可回收性，<strong>一经发货（卡密发出），原则上不支持退款</strong>。</li>'
        . '<li>如遇卡密无效、重复发放等质量问题，请在收货后 24 小时内联系客服，经核实后可进行补发或退款。</li>'
        . '<li>因用户自身原因（如填写错误、未及时提取、泄露卡密等）造成的损失，本站不承担责任。</li>'
        . '<li>用户不得利用本站进行任何违法交易或将购买的商品用于违法用途。</li></ol>'
        . '<h3>四、用户行为规范</h3>'
        . '<p>用户在使用本站服务时，应遵守国家法律法规，不得从事以下行为：</p>'
        . '<ol><li>利用本站从事欺诈、洗钱、赌博或其他违法违规活动。</li>'
        . '<li>恶意下单、刷单、利用系统漏洞套取利益。</li>'
        . '<li>干扰或破坏本站正常运营，包括但不限于攻击服务器、爬取数据等。</li>'
        . '<li>发布虚假信息、恶意差评或损害本站声誉的行为。</li></ol>'
        . '<h3>五、知识产权</h3>'
        . '<p>本站的界面设计、程序代码、商标标识等知识产权归本站所有。商品内容的知识产权归原始权利人所有，用户购买后应在授权范围内使用。</p>'
        . '<h3>六、免责声明</h3>'
        . '<ol><li>本站作为交易平台，不对第三方商品的实际效果和后续服务承担担保责任。</li>'
        . '<li>因不可抗力（如网络故障、支付平台异常、政策变化等）导致的服务中断或交易延迟，本站不承担赔偿责任，但将尽力协助解决。</li>'
        . '<li>用户因违反本协议或法律法规产生的一切后果由用户自行承担。</li></ol>'
        . '<h3>七、协议变更</h3>'
        . '<p>本站有权根据运营需要修改本协议内容，修改后将在本站公布。继续使用本站服务即视为同意变更后的协议。</p>'
        . '<h3>八、争议解决</h3>'
        . '<p>本协议受中华人民共和国法律管辖。如发生争议，双方应友好协商解决；协商不成的，提交本站所在地有管辖权的人民法院处理。</p>';
}
if (empty($_privacyPolicy)) {
    $_privacyPolicy = '<h2>隐私政策</h2>'
        . '<p><strong>' . $_agreementSiteName . '</strong>（以下简称"本站"）非常重视用户的隐私保护。本隐私政策说明我们在您使用虚拟商品自动发卡服务过程中，如何收集、使用、存储和保护您的个人信息。</p>'
        . '<h3>一、信息收集</h3>'
        . '<p>我们可能收集以下类型的信息：</p>'
        . '<ol><li><strong>注册信息：</strong>用户名、邮箱地址、手机号码等您在注册账号时主动提供的信息。</li>'
        . '<li><strong>订单信息：</strong>商品名称、订单号、支付金额、支付方式、下单时间、收货邮箱/手机等与交易直接相关的数据。</li>'
        . '<li><strong>设备与日志信息：</strong>IP 地址、浏览器类型、操作系统、访问时间等用于安全防护和服务优化的技术信息。</li>'
        . '<li><strong>客服沟通记录：</strong>您与客服交流时提供的信息，用于处理售后和纠纷。</li></ol>'
        . '<h3>二、信息使用</h3>'
        . '<p>我们收集的信息仅用于以下目的：</p>'
        . '<ol><li>处理您的订单、完成商品发放和交易结算。</li>'
        . '<li>提供客服支持和售后服务。</li>'
        . '<li>发送订单通知、支付确认等服务类消息。</li>'
        . '<li>监测异常交易，防范欺诈和滥用行为。</li>'
        . '<li>改善平台功能和用户体验。</li>'
        . '<li>履行法律法规规定的义务。</li></ol>'
        . '<h3>三、信息存储与保护</h3>'
        . '<ol><li>您的个人信息存储在安全的服务器中，我们采取加密传输、访问控制等合理的安全措施保护您的数据。</li>'
        . '<li>用户密码经过不可逆加密存储，即使数据库泄露也无法直接获取明文密码。</li>'
        . '<li>我们仅在业务需要和法律要求的最短期限内保留您的信息。</li>'
        . '<li>尽管我们尽力保护您的信息安全，但互联网并非绝对安全的环境，请您也注意保管好自己的账号和密码。</li></ol>'
        . '<h3>四、信息共享</h3>'
        . '<p>我们不会向第三方出售您的个人信息。仅在以下情况下可能共享必要信息：</p>'
        . '<ol><li>为完成支付，向合作的第三方支付机构传递必要的订单和金额信息。</li>'
        . '<li>根据法律法规、政府监管要求或司法裁定。</li>'
        . '<li>在获得您明确同意的情况下。</li></ol>'
        . '<h3>五、Cookie 与本地存储</h3>'
        . '<p>本站使用 Cookie 记住您的登录状态和偏好设置。您可以通过浏览器设置管理或清除 Cookie，但这可能导致需要重新登录或部分功能无法正常使用。</p>'
        . '<h3>六、用户权利</h3>'
        . '<p>您有权：</p>'
        . '<ol><li>查看、修改您的个人资料和账号信息。</li>'
        . '<li>请求删除您的账号及关联数据（删除后不可恢复，历史订单记录将按法规要求保留必要期限）。</li>'
        . '<li>对我们的信息处理方式提出疑问或投诉。</li></ol>'
        . '<h3>七、未成年人保护</h3>'
        . '<p>本站服务面向具有完全民事行为能力的用户。如您是未满 18 周岁的未成年人，请在监护人的陪同和同意下使用本站服务。</p>'
        . '<h3>八、政策变更</h3>'
        . '<p>我们可能根据业务发展和法规变化更新本隐私政策，更新后将在本站公布。继续使用本站服务即视为同意变更后的隐私政策。</p>'
        . '<h3>九、联系我们</h3>'
        . '<p>如您对本隐私政策有任何疑问或需要行使上述权利，请通过本站提供的联系方式与我们取得联系，我们将在 15 个工作日内处理您的请求。</p>';
}
$_siteDescPreview = mb_strlen($_siteDesc) > 50 ? mb_substr($_siteDesc, 0, 50) . '...' : $_siteDesc;
$_siteKeyPreview = mb_strlen($_siteKey) > 50 ? mb_substr($_siteKey, 0, 50) . '...' : $_siteKey;
$_logTitleLabels = ['商品名称', '商品名称 - 站点标题', '商品名称 - 浏览器标题'];
$_logTitleLabel = $_logTitleLabels[$_logTitleStyle] ?? $_logTitleLabels[0];

?>

<style>
    .ss-page { display: flex; flex-direction: column; gap: 22px; padding: 8px 0 18px; }

    /* Hero */
    .ss-hero { padding: 24px 28px; border-radius: 10px; background: var(--pc-card-bg); border: 2px solid #fff; box-shadow: 0 1px 18px #12345b0a; }
    .ss-hero-inner { display: grid; grid-template-columns: minmax(0,1fr) auto; gap: 18px; align-items: center; }
    .ss-title { margin: 14px 0 10px; color: #0f172a; font-size: 22px; line-height: 1.2; font-weight: 800; }
    .ss-desc { max-width: 760px; margin: 0; color: #64748b; font-size: 14px; line-height: 1.9; }
    .ss-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 10px; }
    .ss-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-width: 110px; min-height: 42px; padding: 0 18px; border-radius: 10px; border: 1px solid #e2e8f0; background: #fff; color: #475569; font-size: 13px; font-weight: 600; text-decoration: none; transition: .18s ease; }
    .ss-btn:hover { color: #1e293b; text-decoration: none; border-color: #cbd5e1; box-shadow: 0 2px 8px rgba(15,23,42,.06); }
    .ss-btn.is-primary { background: var(--theme-primary); color: #fff; border-color: var(--theme-primary); }
    .ss-btn.is-primary:hover { background: var(--tp-dark); border-color: var(--tp-dark); color: #fff; box-shadow: 0 4px 14px rgba(var(--tp-rgb),.25); }

    /* Card grid */
    .ss-card-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .ss-card { background: var(--pc-card-bg); border: 2px solid #fff; box-shadow: 0 1px 18px #12345b0a; border-radius: 10px; cursor: pointer; transition: border-color .18s, box-shadow .18s, transform .18s; }
    .ss-card:hover { border-color: rgba(var(--tp-rgb),.22); box-shadow: 0 12px 32px rgba(var(--tp-rgb),.12); transform: translateY(-2px); }
    .ss-card.is-full { grid-column: 1 / -1; }
    .ss-card.is-tall { grid-row: span 2; }
    .ss-card-inner { padding: 28px; display: flex; flex-direction: column; gap: 16px; height: 100%; }
    .ss-card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; }
    .ss-card-left { display: flex; align-items: flex-start; gap: 14px; }
    .ss-card-icon { display: flex; align-items: center; justify-content: center; flex-shrink: 0; width: 48px; height: 48px; border-radius: 14px; font-size: 20px; }
    .ss-card-icon.is-blue { background: rgba(var(--tp-rgb),.1); color: var(--theme-primary); }
    .ss-card-icon.is-teal { background: rgba(11,137,122,.1); color: #0b897a; }
    .ss-card-icon.is-amber { background: rgba(217,119,6,.1); color: #d97706; }
    .ss-card-icon.is-indigo { background: rgba(99,102,241,.1); color: #6366f1; }
    .ss-card-icon.is-slate { background: rgba(100,116,139,.1); color: #64748b; }
    .ss-card-icon.is-rose { background: rgba(244,63,94,.1); color: #f43f5e; }
    .ss-card-title { margin: 0; color: var(--text-main); font-size: 17px; font-weight: 800; }
    .ss-card-desc { margin: 4px 0 0; color: var(--text-sub); font-size: 13px; line-height: 1.7; }
    .ss-card-edit { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 999px; background: rgba(var(--tp-rgb),.06); color: var(--theme-primary); font-size: 13px; font-weight: 700; white-space: nowrap; transition: background .18s; flex-shrink: 0; }
    .ss-card:hover .ss-card-edit { background: rgba(var(--tp-rgb),.12); }

    .ss-card-body { flex: 1; }
    .ss-preview-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 10px; }
    .ss-preview-item { display: flex; align-items: baseline; gap: 10px; font-size: 13px; line-height: 1.7; }
    .ss-preview-label { color: var(--text-sub); white-space: nowrap; min-width: 64px; flex-shrink: 0; }
    .ss-preview-value { color: var(--text-main); font-weight: 600; word-break: break-all; }
    .ss-preview-value.is-empty { color: var(--text-sub); font-weight: 400; font-style: italic; }
    .ss-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; white-space: nowrap; }
    .ss-badge.is-green { background: rgba(22,163,74,.08); color: #16a34a; }
    .ss-badge.is-gray { background: rgba(100,116,139,.08); color: #64748b; }

    /* Popup form styles */
    .ss-popup-form { padding: 24px 28px; }
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
    .ss-textarea { min-height: 112px; resize: vertical; }
    .ss-tips { display: block; margin-top: 8px; color: var(--text-sub, #64748b); font-size: 12px; line-height: 1.7; }
    .ss-inline-form { display: flex; gap: 10px; align-items: center; }
    .ss-inline-form .ss-input { flex: 1; min-width: 0; }
    .ss-inline-form .ss-select { flex: 0 0 auto; width: 200px; }
    .ss-slug-prefix { display: flex; align-items: center; color: #888; white-space: nowrap; padding-right: 6px; font-size: 13px; line-height: 38px; }
    .ss-popup-footer { display: flex; align-items: center; justify-content: flex-end; gap: 10px; padding: 16px 28px; border-top: 1px solid var(--card-border, #e2e8f0); background: var(--bg-secondary, #f8fafc); border-radius: 0 0 10px 10px; }
    .ss-popup-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-width: 120px; height: 44px; padding: 0 22px; border: 0; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; transition: .18s; }
    .ss-popup-btn.is-primary { background: linear-gradient(135deg, var(--tp-dark), var(--tp-light)); color: #fff; box-shadow: 0 8px 20px rgba(var(--tp-rgb),.18); }
    .ss-popup-btn.is-primary:hover { transform: translateY(-1px); box-shadow: 0 12px 28px rgba(var(--tp-rgb),.24); }
    .ss-popup-btn.is-default { background: #fff; color: var(--text-main, #1e293b); border: 1.5px solid var(--input-border, #e2e8f0); }
    .ss-popup-btn.is-default:hover { background: var(--bg-secondary, #f5f7fa); }
    .ss-domain-warn { margin-top: 12px; padding: 10px 14px; border-radius: 8px; background: rgba(234,179,8,.08); border: 1px solid rgba(234,179,8,.2); color: #92400e; font-size: 12px; line-height: 1.7; }

    /* Upload widget */
    .ss-upload-row { display: flex; align-items: center; gap: 12px; }
    .ss-upload-row .ss-input { flex: 1; min-width: 0; }
    .ss-upload-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; flex-shrink: 0; height: 46px; padding: 0 16px; border: 1.5px solid var(--input-border, #e2e8f0); border-radius: 10px; background: var(--bg-secondary, #f8fafc); color: var(--text-main, #1e293b); font-size: 13px; font-weight: 600; cursor: pointer; transition: background .18s, border-color .18s; white-space: nowrap; }
    .ss-upload-btn:hover { background: rgba(var(--tp-rgb),.06); border-color: rgba(var(--tp-rgb),.22); }
    .ss-upload-preview { display: flex; align-items: center; gap: 10px; margin-top: 8px; }
    .ss-upload-preview img { max-height: 40px; max-width: 180px; border-radius: 6px; border: 1px solid var(--input-border, #e2e8f0); background: #fff; object-fit: contain; }
    .ss-upload-preview .ss-upload-clear { font-size: 12px; color: #ef4444; cursor: pointer; border: none; background: none; padding: 0; }
    .ss-upload-preview .ss-upload-clear:hover { text-decoration: underline; }

    /* Popup tabs */
    .ss-tabs { display: flex; gap: 4px; padding: 0 28px; border-bottom: 1.5px solid var(--card-border, #e2e8f0); background: var(--bg-secondary, #f8fafc); }
    .ss-tab { display: inline-flex; align-items: center; gap: 6px; padding: 13px 18px; margin-bottom: -1.5px; border: none; background: none; color: var(--text-sub, #64748b); font-size: 13px; font-weight: 600; cursor: pointer; border-bottom: 2.5px solid transparent; transition: color .18s, border-color .18s; white-space: nowrap; }
    .ss-tab:hover { color: var(--text-main, #1e293b); }
    .ss-tab.is-active { color: var(--theme-primary); border-bottom-color: var(--theme-primary); }
    .ss-tab-panel { display: none; }
    .ss-tab-panel.is-active { display: block; }

    .tox-tinymce { border-radius: 10px !important; border: 1.5px solid var(--input-border, #e2e8f0) !important; overflow: hidden; }

    @media (max-width: 1100px) {
        .ss-hero-inner { grid-template-columns: 1fr; }
        .ss-actions { justify-content: flex-start; }
    }
    @media (max-width: 960px) {
        .ss-card-grid { grid-template-columns: 1fr; }
        .ss-card.is-full { grid-column: auto; }
        .ss-card.is-tall { grid-row: auto; }
        .ss-inline-form { flex-wrap: wrap; }
        .ss-inline-form .ss-select { width: 100%; }
    }
    @media (max-width: 768px) {
        .ss-hero { padding: 24px 20px; }
        .ss-title { font-size: 26px; }
        .ss-actions { display: grid; grid-template-columns: 1fr 1fr; width: 100%; }
        .ss-btn { min-width: 0; width: 100%; }
        .ss-card-inner { padding: 20px; }
        .ss-card-icon { display: none; }
        .ss-popup-form { padding: 20px; }
        .ss-popup-footer { padding: 14px 20px; }
    }
    @media (max-width: 560px) {
        .ss-actions { grid-template-columns: 1fr; }
    }
</style>

<main class="ss-page">
    <section class="ss-hero">
        <div class="ss-hero-inner">
            <div>
                <h1 class="ss-title">店铺配置</h1>
                <p class="ss-desc">管理店铺名称、域名绑定、公告、SEO、底部信息与协议，点击卡片即可修改。</p>
            </div>
            <div class="ss-actions">
                <a href="?action=master_goods" class="ss-btn"><i class="fa fa-cubes"></i> 主站商品</a>
                <a href="?action=order" class="ss-btn"><i class="fa fa-file-text-o"></i> 商品订单</a>
              <!--  <a href="?action=store_tpl" class="ss-btn is-primary"><i class="fa fa-shopping-bag"></i> 模板商店</a>-->
            </div>
        </div>
    </section>

    <div class="ss-card-grid">
        <!-- 基础信息 -->
        <a href="?action=setting_basic" class="ss-card is-tall" id="card-basic" style="text-decoration:none;color:inherit;">
            <div class="ss-card-inner">
                <div class="ss-card-head">
                    <div class="ss-card-left">
                        <div class="ss-card-icon is-blue"><i class="fa fa-id-card-o"></i></div>
                        <div>
                            <h2 class="ss-card-title">基础信息</h2>
                            <p class="ss-card-desc">店铺名称、标题、SEO、底部信息与协议</p>
                        </div>
                    </div>
                    <span class="ss-card-edit"><i class="fa fa-pencil"></i> 修改</span>
                </div>
                <div class="ss-card-body">
                    <ul class="ss-preview-list">
                        <li class="ss-preview-item">
                            <span class="ss-preview-label">店铺名称</span>
                            <span class="ss-preview-value" id="pv-name"><?= htmlspecialchars($userStation['name'] ?: '未设置') ?></span>
                        </li>
                        <li class="ss-preview-item">
                            <span class="ss-preview-label">网站标题</span>
                            <span class="ss-preview-value<?= empty($userStation['title']) ? ' is-empty' : '' ?>" id="pv-title"><?= htmlspecialchars($userStation['title'] ?: '未设置') ?></span>
                        </li>
                        <li class="ss-preview-item">
                            <span class="ss-preview-label">网站副标题</span>
                            <span class="ss-preview-value<?= empty($userStation['site_subtitle'] ?? '') ? ' is-empty' : '' ?>" id="pv-subtitle"><?= htmlspecialchars(($userStation['site_subtitle'] ?? '') ?: '未设置') ?></span>
                        </li>
                        <li class="ss-preview-item">
                            <span class="ss-preview-label">网站 Logo</span>
                            <span class="ss-preview-value<?= empty($_stationLogo) ? ' is-empty' : '' ?>" id="pv-logo"><?php if (!empty($_stationLogo)): ?><img src="<?= htmlspecialchars(getFileUrl($_stationLogo)) ?>" style="max-height:28px;max-width:120px;vertical-align:middle;border-radius:4px;"><?php else: ?>未设置<?php endif; ?></span>
                        </li>
                        <li class="ss-preview-item">
                            <span class="ss-preview-label">Favicon</span>
                            <span class="ss-preview-value<?= empty($_stationFavicon) ? ' is-empty' : '' ?>" id="pv-favicon"><?php if (!empty($_stationFavicon)): ?><img src="<?= htmlspecialchars(getFileUrl($_stationFavicon)) ?>" style="max-height:24px;max-width:24px;vertical-align:middle;border-radius:4px;"><?php else: ?>未设置<?php endif; ?></span>
                        </li>
                        <li class="ss-preview-item">
                            <span class="ss-preview-label">站点关键字</span>
                            <span class="ss-preview-value<?= empty($_siteKey) ? ' is-empty' : '' ?>" id="pv-sitekey"><?= htmlspecialchars($_siteKeyPreview ?: '未设置') ?></span>
                        </li>
                        <li class="ss-preview-item">
                            <span class="ss-preview-label">站点描述</span>
                            <span class="ss-preview-value<?= empty($_siteDesc) ? ' is-empty' : '' ?>" id="pv-sitedesc"><?= htmlspecialchars($_siteDescPreview ?: '未设置') ?></span>
                        </li>
                        <li class="ss-preview-item">
                            <span class="ss-preview-label">标题方案</span>
                            <span class="ss-preview-value" id="pv-titlestyle"><?= htmlspecialchars($_logTitleLabel) ?></span>
                        </li>
                        <li class="ss-preview-item">
                            <span class="ss-preview-label">ICP备案号</span>
                            <span class="ss-preview-value<?= empty($_icp) ? ' is-empty' : '' ?>" id="pv-icp"><?= htmlspecialchars($_icp ?: '未设置') ?></span>
                        </li>
                        <li class="ss-preview-item">
                            <span class="ss-preview-label">服务协议</span>
                            <span class="ss-preview-value<?= empty($_userAgreement) ? ' is-empty' : '' ?>" id="pv-agreement"><?= empty($_userAgreement) ? '未设置' : '已配置（' . mb_strlen(strip_tags($_userAgreement)) . '字）' ?></span>
                        </li>
                        <li class="ss-preview-item">
                            <span class="ss-preview-label">隐私政策</span>
                            <span class="ss-preview-value<?= empty($_privacyPolicy) ? ' is-empty' : '' ?>" id="pv-privacy"><?= empty($_privacyPolicy) ? '未设置' : '已配置（' . mb_strlen(strip_tags($_privacyPolicy)) . '字）' ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </a>

        <!-- 站内公告 -->
        <a href="?action=setting_notice" class="ss-card" id="card-notice" style="text-decoration:none;color:inherit;">
            <div class="ss-card-inner">
                <div class="ss-card-head">
                    <div class="ss-card-left">
                        <div class="ss-card-icon is-amber"><i class="fa fa-bullhorn"></i></div>
                        <div>
                            <h2 class="ss-card-title">站内公告</h2>
                            <p class="ss-card-desc">滚动公告和内容公告，向访客展示重要信息</p>
                        </div>
                    </div>
                    <span class="ss-card-edit"><i class="fa fa-pencil"></i> 修改</span>
                </div>
                <div class="ss-card-body">
                    <ul class="ss-preview-list">
                        <li class="ss-preview-item">
                            <span class="ss-preview-label">滚动公告</span>
                            <span class="ss-preview-value<?= empty($userStation['roll_notice']) ? ' is-empty' : '' ?>" id="pv-roll"><?= htmlspecialchars($_rollNoticePreview ?: '未设置') ?></span>
                        </li>
                        <li class="ss-preview-item">
                            <span class="ss-preview-label">内容公告</span>
                            <span class="ss-preview-value<?= empty($userStation['home_notice']) ? ' is-empty' : '' ?>" id="pv-home"><?= htmlspecialchars($_homeNoticePreview ?: '未设置') ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </a>

        <!-- 模板配置 -->
        <a href="?action=setting_tpl" class="ss-card" id="card-tpl" style="text-decoration:none;color:inherit;">
            <div class="ss-card-inner">
                <div class="ss-card-head">
                    <div class="ss-card-left">
                        <div class="ss-card-icon is-indigo"><i class="fa fa-paint-brush"></i></div>
                        <div>
                            <h2 class="ss-card-title">模板配置</h2>
                            <p class="ss-card-desc">管理分店前端模板、切换电脑端与手机端模板</p>
                        </div>
                    </div>
                    <span class="ss-card-edit"><i class="fa fa-pencil"></i> 管理</span>
                </div>
                <div class="ss-card-body">
                    <ul class="ss-preview-list">
                        <li class="ss-preview-item">
                            <span class="ss-preview-label">前台模板</span>
                            <span class="ss-preview-value">电脑端：<?= htmlspecialchars($_tplPcName) ?> &nbsp; 手机端：<?= htmlspecialchars($_tplTelName) ?></span>
                        </li>
                        <li class="ss-preview-item">
                            <span class="ss-preview-label">后台模板</span>
                            <span class="ss-preview-value">电脑端：<?= htmlspecialchars($_ucPcName) ?> &nbsp; 手机端：<?= htmlspecialchars($_ucTelName) ?></span>
                        </li>
                        <li class="ss-preview-item">
                            <span class="ss-preview-label">底部导航</span>
                            <span class="ss-preview-value"><?= htmlspecialchars($_bnName) ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </a>

        <!-- 域名配置 -->
        <a href="?action=setting_domain" class="ss-card is-full" id="card-domain" style="text-decoration:none;color:inherit;">
            <div class="ss-card-inner">
                <div class="ss-card-head">
                    <div class="ss-card-left">
                        <div class="ss-card-icon is-teal"><i class="fa fa-globe"></i></div>
                        <div>
                            <h2 class="ss-card-title">域名配置</h2>
                            <p class="ss-card-desc">二级域名、独立域名与店铺标识</p>
                        </div>
                    </div>
                    <?php if ($domain2Full || $domainFull): ?>
                    <span class="ss-badge is-green"><i class="fa fa-check-circle"></i> 已配置</span>
                    <?php else: ?>
                    <span class="ss-badge is-gray"><i class="fa fa-minus-circle"></i> 未配置</span>
                    <?php endif; ?>
                </div>
                <div class="ss-card-body">
                    <ul class="ss-preview-list">
                        <li class="ss-preview-item">
                            <span class="ss-preview-label">二级域名</span>
                            <span class="ss-preview-value<?= empty($domain2Full) ? ' is-empty' : '' ?>" id="pv-domain2"><?= $domain2Full ?: '未绑定' ?></span>
                        </li>
                        <?php if (!empty($_cnameDomain)): ?>
                        <li class="ss-preview-item">
                            <span class="ss-preview-label">独立域名</span>
                            <span class="ss-preview-value<?= empty($domainFull) ? ' is-empty' : '' ?>" id="pv-domain"><?= $domainFull ?: '未绑定' ?></span>
                        </li>
                        <?php endif; ?>
                        <?php if ($station_slug_mode === '1'): ?>
                        <li class="ss-preview-item">
                            <span class="ss-preview-label">店铺标识</span>
                            <span class="ss-preview-value<?= empty($stationSlug) ? ' is-empty' : '' ?>" id="pv-slug"><?= $stationSlug ? htmlspecialchars($stationShareLink) : '未设置' ?></span>
                        </li>
                        <?php endif; ?>
                        <?php if ($_domainChangePrice > 0): ?>
                        <li class="ss-preview-item">
                            <span class="ss-preview-label">修改费用</span>
                            <span class="ss-preview-value" style="color:#d97706;">¥<?= number_format($_domainChangePrice, 2) ?>/次（首次绑定免费）</span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </a>
    </div>
</main>
<?php include __DIR__ . '/../_pc_page_footer.php'; ?>
