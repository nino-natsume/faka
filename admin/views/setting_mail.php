<?php defined('DC_ROOT') || exit('access denied!'); ?>

<style>
    .mailcfg-section { background: #ffffff85; border: 1px solid #eef1f4; border-radius: 8px; padding: 18px 20px; margin-bottom: 14px; }
    .mailcfg-title { font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; }
    .mailcfg-title i { color: #2563eb; }
    .mailcfg-row { display: grid; grid-template-columns: 160px 1fr; gap: 10px; align-items: start; padding: 8px 0; }
    .mailcfg-row > label { color: #374151; font-weight: 500; padding-top: 10px; }
    .mailcfg-row .layui-input-block { margin-left: 0; }
    .mailcfg-row .layui-input,
    .mailcfg-row .layui-textarea { max-width: 860px; }
    .mailcfg-tip { color: #6b7280; font-size: 12px; line-height: 1.7; margin-top: 8px; }
    .mailcfg-quote {
        max-width: 860px; background: #f8fbff; border: 1px solid #dbeafe; border-left: 4px solid #2563eb;
        border-radius: 8px; padding: 14px 16px; color: #475569; line-height: 1.8;
    }
    .mailcfg-actions { text-align: center; margin-top: 10px; }

    @media (max-width: 768px) {
        .layui-card-body { padding: 12px !important; }
        .mailcfg-row { grid-template-columns: 1fr; gap: 4px; }
        .mailcfg-row > label { padding-top: 0; font-size: 13px; }
        .mailcfg-section { padding: 14px 12px; }
        .mailcfg-row .layui-input,
        .mailcfg-row .layui-textarea { max-width: 100%; }
        .mailcfg-quote { max-width: 100%; font-size: 12px; padding: 10px 12px; }
    }
</style>

<div class="layui-tabs order-tabs-wrapper" style="display:flex;align-items:center;justify-content:space-between;" lay-options="{trigger: false}">
    <ul class="layui-tabs-header">
        <li><a href="./setting.php">系统配置</a></li>
        <li><a href="./setting.php?action=blog">博客配置</a></li>
        <li><a href="./setting.php?action=agreement">协议管理</a></li>
        <li><a href="./setting.php?action=seo">SEO设置</a></li>
        <li class="layui-this"><a href="./setting.php?action=mail">邮箱配置</a></li>
    </ul>
</div>
<div class="layui-table-view" style="border-radius:8px;overflow:hidden;">
    <div class="layui-card-header" style="display:flex;align-items:center;justify-content:center;position:relative;height:auto;padding:12px 15px;">
        <span style="position:absolute;left:15px;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">邮箱配置</span>
    </div>
    <div class="layui-card-body" style="padding:20px;">
        <form action="setting.php?action=mail_save" method="post" name="setting_form" id="setting_form" class="layui-form">
            <div class="mailcfg-section">
                <div class="mailcfg-title"><i class="ri-mail-settings-line"></i>SMTP 配置</div>
                <div class="mailcfg-row">
                    <label>发送人邮箱</label>
                    <div>
                        <input type="email" class="layui-input" value="<?= $smtp_mail ?>" name="smtp_mail">
                        <div class="mailcfg-tip">请输入可正常发信的邮箱账号。</div>
                    </div>
                </div>
                <div class="mailcfg-row">
                    <label>SMTP 密码</label>
                    <div>
                        <input type="text" name="smtp_pw" class="layui-input" value="<?= $smtp_pw ?>" autocomplete="new-password">
                        <div class="mailcfg-tip">一般填写邮箱服务商生成的授权码，而不是登录密码。</div>
                    </div>
                </div>
                <div class="mailcfg-row">
                    <label>发送人名称</label>
                    <div>
                        <input type="text" class="layui-input" value="<?= $smtp_from_name ?>" name="smtp_from_name">
                        <div class="mailcfg-tip">选填，建议填写站点名称或发信人名称。</div>
                    </div>
                </div>
                <div class="mailcfg-row">
                    <label>SMTP 服务器</label>
                    <div>
                        <input class="layui-input" value="<?= $smtp_server ?>" name="smtp_server">
                        <div class="mailcfg-tip">如 QQ 邮箱常用 `smtp.qq.com`。</div>
                    </div>
                </div>
                <div class="mailcfg-row">
                    <label>端口</label>
                    <div>
                        <input class="layui-input" value="<?= $smtp_port ?>" name="smtp_port">
                        <div class="mailcfg-tip">`465` 常用于 SSL，`587` 常用于 STARTTLS（如 Outlook）。</div>
                    </div>
                </div>
            </div>

            <div class="mailcfg-section">
                <div class="mailcfg-title"><i class="ri-information-line"></i>参考示例</div>
                <div class="mailcfg-row">
                    <label>QQ邮箱示例</label>
                    <div>
                        <div class="mailcfg-quote">
                            <b>以 QQ 邮箱配置为例</b><br>
                            发送人邮箱：你的 QQ 邮箱<br>
                            SMTP密码：见 QQ 邮箱顶部设置 -> 账户 -> 开启 IMAP/SMTP 服务 -> 生成授权码（即为 SMTP 密码）<br>
                            发送人名称：你的姓名或者站点名称<br>
                            SMTP服务器：smtp.qq.com<br>
                            端口：465<br>
                        </div>
                    </div>
                </div>
            </div>

            <div id="testMailForm" class="layui-form" style="display:none;padding: 16px;">
                <div class="layui-form-item">
                    <label class="layui-form-label">接收邮箱</label>
                    <div class="layui-input-block">
                        <input class="layui-input" type="email" name="testTo" placeholder="输入接收邮箱">
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <div id="testMailMsg" style="margin-top: 6px;"></div>
                        <button type="button" class="layui-btn layui-bg-blue" id="testSendBtn">发送</button>
                        <button type="button" class="layui-btn layui-btn-primary" id="testCloseBtn">关闭</button>
                    </div>
                </div>
            </div>

            <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
            <div class="mailcfg-actions">
                <button type="submit" class="layui-btn" lay-submit lay-filter="demo1">保存设置</button>
                <input type="button" value="发送测试" class="layui-btn layui-bg-blue" id="testSendOpenBtn"/>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </form>
    </div>
</div>

<div style="height: 96px;"></div>


<script>

        layui.use(['layer'], function () {
            var baseLayer = layui.layer;
            var Layer = window.top && window.top.layui && window.top.layui.layer ? window.top.layui.layer : baseLayer;
            var testLayerIndex = -1;

            $("#testSendOpenBtn").on('click', function () {
                $("#testMailMsg").html("");
                var htmlSrc = $('#testMailForm')[0].outerHTML;
                var html = htmlSrc.replace('display:none', 'display:block');
                testLayerIndex = Layer.open({
                    type: 1,
                    title: '发送测试',
                    shadeClose: true,
                    shade: 0.3,
                    area: ['420px', '220px'],
                    content: html,
                    skin: 'em-layer-testmail',
                    zIndex: 19891015,
                    success: function(layero){
                        Layer.setTop(layero);
                        layero.find('#testMailForm').show();
                        layero.find('#testSendBtn').on('click', function(){
                            var msgEl = layero.find('#testMailMsg');
                            msgEl.html("<small class='text-secondary'>发送中...<small>");
                            var testToVal = layero.find('input[name="testTo"]').val();
                            var payload = $("#setting_form").serializeArray();
                            payload.push({name: 'testTo', value: testToVal});
                            $.post("setting.php?action=mail_test", $.param(payload), function (data) {
                                if (data === '') {
                                    msgEl.html("<small class='text-success'>发送成功</small>");
                                } else {
                                    msgEl.html(data);
                                }
                            });
                        });
                        layero.find('#testCloseBtn').on('click', function(){
                            Layer.close(testLayerIndex);
                        });
                    },
                    end: function(){
                        $('#testMailForm').hide();
                    }
                });
            });
        });


    $(function () {
        // menu
        // 提交表单
        $("#setting_form").submit(function (event) {
            event.preventDefault();
            submitForm("#setting_form");
        });

        
    });
</script>
<style>

</style>
