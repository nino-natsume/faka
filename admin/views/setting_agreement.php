<?php defined('DC_ROOT') || exit('access denied!'); ?>

<div class="layui-tabs order-tabs-wrapper" style="display:flex;align-items:center;justify-content:space-between;" lay-options="{trigger: false}">
    <ul class="layui-tabs-header">
        <li><a href="./setting.php">系统配置</a></li>
        <li><a href="./setting.php?action=blog">博客配置</a></li>
        <li class="layui-this"><a href="./setting.php?action=agreement">协议管理</a></li>
        <li><a href="./setting.php?action=seo">SEO设置</a></li>
        <li><a href="./setting.php?action=mail">邮箱配置</a></li>
    </ul>
</div>
<div class="layui-table-view" style="border-radius:8px;overflow:hidden;">
    <div class="layui-card-header" style="display:flex;align-items:center;justify-content:center;position:relative;height:auto;padding:12px 15px;">
        <span style="position:absolute;left:15px;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">协议管理</span>
    </div>
    <div class="layui-card-body" style="padding:20px;">
        <blockquote class="layui-elem-quote">
            <i class="ri-information-line"></i> 配置注册页面的《用户服务协议》和《隐私政策》内容。留空则前端对应链接不可点击。
        </blockquote>

        <form id="agreement-form" method="post">
            <div class="layui-form-item" style="margin-bottom:24px;">
                <label class="layui-form-label" style="width:auto;padding:0 0 8px;font-weight:600;font-size:14px;color:#111827;">
                    <i class="ri-file-text-line" style="color:#2563eb;"></i> 用户服务协议
                </label>
                <div class="layui-input-block" style="margin-left:0;">
                    <textarea id="editor_agreement" name="user_agreement"><?= htmlspecialchars($user_agreement) ?></textarea>
                </div>
            </div>

            <div class="layui-form-item" style="margin-bottom:24px;">
                <label class="layui-form-label" style="width:auto;padding:0 0 8px;font-weight:600;font-size:14px;color:#111827;">
                    <i class="ri-shield-check-line" style="color:#2563eb;"></i> 隐私政策
                </label>
                <div class="layui-input-block" style="margin-left:0;">
                    <textarea id="editor_privacy" name="privacy_policy"><?= htmlspecialchars($privacy_policy) ?></textarea>
                </div>
            </div>

            <input type="hidden" name="token" value="<?= LoginAuth::genToken() ?>">
            <div style="text-align:center;margin-top:10px;padding-bottom:10px;">
                <button type="submit" class="layui-btn"><i class="ri-save-line"></i> 保存协议</button>
                <button type="button" class="layui-btn layui-btn-normal" id="btn-fill-default"><i class="ri-draft-line"></i> 填充默认内容</button>
            </div>
        </form>
    </div>
</div>

<script src="./tinymce/tinymce.min.js?t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>
<script>
$(function(){
    var siteName = '<?= addslashes(Option::get('blogname') ?: '本站') ?>';

    var defaultAgreement = '<h2>用户服务协议</h2>'
        + '<p>欢迎使用 <strong>' + siteName + '</strong>（以下简称"本站"）提供的虚拟商品自动发卡服务。请您在注册或使用本站服务之前，仔细阅读本协议。注册即表示您已充分阅读、理解并同意接受本协议的全部内容。</p>'
        + '<h3>一、服务说明</h3>'
        + '<p>本站是一个虚拟商品在线交易平台，提供卡密、兑换码、账号、充值服务等数字商品的自动化销售与发放服务。用户可通过本站选购商品并在线支付，系统将自动发送商品信息至用户。</p>'
        + '<h3>二、用户账号</h3>'
        + '<ol>'
        + '<li>用户注册时应提供真实、准确的信息，并妥善保管账号及密码。</li>'
        + '<li>用户应对其账号下的一切行为和交易负责。因账号被盗、密码泄露等非本站原因导致的损失，由用户自行承担。</li>'
        + '<li>用户不得将账号转让、出借给他人使用。</li>'
        + '<li>本站有权对异常账号（如批量注册、恶意下单等）进行限制或封禁。</li>'
        + '</ol>'
        + '<h3>三、交易规则</h3>'
        + '<ol>'
        + '<li>用户下单前应仔细阅读商品描述、使用说明及注意事项，下单付款即视为认可商品内容。</li>'
        + '<li>虚拟商品具有可复制性和不可回收性，<strong>一经发货（卡密发出），原则上不支持退款</strong>。</li>'
        + '<li>如遇卡密无效、重复发放等质量问题，请在收货后 24 小时内联系客服，经核实后可进行补发或退款。</li>'
        + '<li>因用户自身原因（如填写错误、未及时提取、泄露卡密等）造成的损失，本站不承担责任。</li>'
        + '<li>用户不得利用本站进行任何违法交易或将购买的商品用于违法用途。</li>'
        + '</ol>'
        + '<h3>四、用户行为规范</h3>'
        + '<p>用户在使用本站服务时，应遵守国家法律法规，不得从事以下行为：</p>'
        + '<ol>'
        + '<li>利用本站从事欺诈、洗钱、赌博或其他违法违规活动。</li>'
        + '<li>恶意下单、刷单、利用系统漏洞套取利益。</li>'
        + '<li>干扰或破坏本站正常运营，包括但不限于攻击服务器、爬取数据等。</li>'
        + '<li>发布虚假信息、恶意差评或损害本站声誉的行为。</li>'
        + '</ol>'
        + '<h3>五、知识产权</h3>'
        + '<p>本站的界面设计、程序代码、商标标识等知识产权归本站所有。商品内容的知识产权归原始权利人所有，用户购买后应在授权范围内使用。</p>'
        + '<h3>六、免责声明</h3>'
        + '<ol>'
        + '<li>本站作为交易平台，不对第三方商品的实际效果和后续服务承担担保责任。</li>'
        + '<li>因不可抗力（如网络故障、支付平台异常、政策变化等）导致的服务中断或交易延迟，本站不承担赔偿责任，但将尽力协助解决。</li>'
        + '<li>用户因违反本协议或法律法规产生的一切后果由用户自行承担。</li>'
        + '</ol>'
        + '<h3>七、协议变更</h3>'
        + '<p>本站有权根据运营需要修改本协议内容，修改后将在本站公布。继续使用本站服务即视为同意变更后的协议。</p>'
        + '<h3>八、争议解决</h3>'
        + '<p>本协议受中华人民共和国法律管辖。如发生争议，双方应友好协商解决；协商不成的，提交本站所在地有管辖权的人民法院处理。</p>';

    var defaultPrivacy = '<h2>隐私政策</h2>'
        + '<p><strong>' + siteName + '</strong>（以下简称"本站"）非常重视用户的隐私保护。本隐私政策说明我们在您使用虚拟商品自动发卡服务过程中，如何收集、使用、存储和保护您的个人信息。</p>'
        + '<h3>一、信息收集</h3>'
        + '<p>我们可能收集以下类型的信息：</p>'
        + '<ol>'
        + '<li><strong>注册信息：</strong>用户名、邮箱地址、手机号码等您在注册账号时主动提供的信息。</li>'
        + '<li><strong>订单信息：</strong>商品名称、订单号、支付金额、支付方式、下单时间、收货邮箱/手机等与交易直接相关的数据。</li>'
        + '<li><strong>设备与日志信息：</strong>IP 地址、浏览器类型、操作系统、访问时间等用于安全防护和服务优化的技术信息。</li>'
        + '<li><strong>客服沟通记录：</strong>您与客服交流时提供的信息，用于处理售后和纠纷。</li>'
        + '</ol>'
        + '<h3>二、信息使用</h3>'
        + '<p>我们收集的信息仅用于以下目的：</p>'
        + '<ol>'
        + '<li>处理您的订单、完成商品发放和交易结算。</li>'
        + '<li>提供客服支持和售后服务。</li>'
        + '<li>发送订单通知、支付确认等服务类消息。</li>'
        + '<li>监测异常交易，防范欺诈和滥用行为。</li>'
        + '<li>改善平台功能和用户体验。</li>'
        + '<li>履行法律法规规定的义务。</li>'
        + '</ol>'
        + '<h3>三、信息存储与保护</h3>'
        + '<ol>'
        + '<li>您的个人信息存储在安全的服务器中，我们采取加密传输、访问控制等合理的安全措施保护您的数据。</li>'
        + '<li>用户密码经过不可逆加密存储，即使数据库泄露也无法直接获取明文密码。</li>'
        + '<li>我们仅在业务需要和法律要求的最短期限内保留您的信息。</li>'
        + '<li>尽管我们尽力保护您的信息安全，但互联网并非绝对安全的环境，请您也注意保管好自己的账号和密码。</li>'
        + '</ol>'
        + '<h3>四、信息共享</h3>'
        + '<p>我们不会向第三方出售您的个人信息。仅在以下情况下可能共享必要信息：</p>'
        + '<ol>'
        + '<li>为完成支付，向合作的第三方支付机构传递必要的订单和金额信息。</li>'
        + '<li>根据法律法规、政府监管要求或司法裁定。</li>'
        + '<li>在获得您明确同意的情况下。</li>'
        + '</ol>'
        + '<h3>五、Cookie 与本地存储</h3>'
        + '<p>本站使用 Cookie 记住您的登录状态和偏好设置。您可以通过浏览器设置管理或清除 Cookie，但这可能导致需要重新登录或部分功能无法正常使用。</p>'
        + '<h3>六、用户权利</h3>'
        + '<p>您有权：</p>'
        + '<ol>'
        + '<li>查看、修改您的个人资料和账号信息。</li>'
        + '<li>请求删除您的账号及关联数据（删除后不可恢复，历史订单记录将按法规要求保留必要期限）。</li>'
        + '<li>对我们的信息处理方式提出疑问或投诉。</li>'
        + '</ol>'
        + '<h3>七、未成年人保护</h3>'
        + '<p>本站服务面向具有完全民事行为能力的用户。如您是未满 18 周岁的未成年人，请在监护人的陪同和同意下使用本站服务。</p>'
        + '<h3>八、政策变更</h3>'
        + '<p>我们可能根据业务发展和法规变化更新本隐私政策，更新后将在本站公布。继续使用本站服务即视为同意变更后的隐私政策。</p>'
        + '<h3>九、联系我们</h3>'
        + '<p>如您对本隐私政策有任何疑问或需要行使上述权利，请通过本站提供的联系方式与我们取得联系，我们将在 15 个工作日内处理您的请求。</p>';

    // 图片上传
    var imageUploadHandler = function(blobInfo, progress) {
        return new Promise(function(resolve, reject){
            var xhr = new XMLHttpRequest();
            xhr.withCredentials = false;
            xhr.open('POST', '/admin/article.php?action=upload_cover2');
            xhr.upload.onprogress = function(e){ progress(e.loaded / e.total * 100); };
            xhr.onload = function(){
                if(xhr.status < 200 || xhr.status >= 300){ reject('HTTP Error: ' + xhr.status); return; }
                var json = JSON.parse(xhr.responseText);
                if(!json || typeof json.location != 'string'){ reject('Invalid JSON'); return; }
                resolve(json.location);
            };
            xhr.onerror = function(){ reject('Upload failed'); };
            var fd = new FormData();
            fd.append('image', blobInfo.blob(), blobInfo.filename());
            xhr.send(fd);
        });
    };

    tinymce.init({
        selector: '#editor_agreement, #editor_privacy',
        language: 'zh_CN',
        height: 500,
        images_upload_handler: imageUploadHandler,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'wordcount', 'autosave'
        ],
        autosave_ask_before_unload: false,
        toolbar: 'undo redo | blocks | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }',
        setup: function(editor){
            editor.on('input change undo redo cut paste', function(){ editor.save(); });
        }
    });

    // 表单提交
    $('#agreement-form').on('submit', function(e){
        e.preventDefault();
        tinymce.triggerSave();
        var loadIdx = layer.load(2);
        $.ajax({
            type: 'POST',
            url: 'setting.php?action=agreement_save',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res){
                layer.close(loadIdx);
                if(res.code == 0) layer.msg('保存成功', {icon: 1});
                else layer.msg(res.msg || '保存失败', {icon: 2});
            },
            error: function(){
                layer.close(loadIdx);
                layer.msg('网络错误', {icon: 2});
            }
        });
    });

    // 填充默认内容
    $('#btn-fill-default').on('click', function(){
        layer.confirm('将用默认模板覆盖当前编辑器内容，确定？', {icon: 3, title: '填充默认内容'}, function(idx){
            tinymce.get('editor_agreement').setContent(defaultAgreement);
            tinymce.get('editor_privacy').setContent(defaultPrivacy);
            layer.close(idx);
            layer.msg('已填充默认内容，请检查后保存', {icon: 1});
        });
    });
});
</script>
