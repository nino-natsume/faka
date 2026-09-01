<?php defined('DC_ROOT') || exit('access denied!'); ?>
<style>
    body{
        overflow: hidden;
    }
    .agreement-section {
        margin-bottom: 15px;
    }
    .agreement-section h4 {
        color: #333;
        margin: 12px 0 8px 0;
        font-size: 14px;
    }
    .agreement-section p {
        color: #666;
        line-height: 1.8;
        margin: 8px 0;
    }
    .agreement-section ul {
        margin: 8px 0 8px 20px;
    }
    .agreement-section ul li {
        color: #666;
        line-height: 1.8;
        margin: 5px 0;
    }
    .warning-text {
        color: #e74c3c;
        font-weight: 500;
    }
    .highlight-box {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 6px;
        padding: 12px 15px;
        margin: 10px 0;
    }
    .danger-box {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        border-radius: 6px;
        padding: 12px 15px;
        margin: 10px 0;
    }
</style>

<form class="layui-form" action="sort.php?action=save" id="form">
    <div style="padding: 25px;" id="open-box">
        <h1 style="text-align: center; margin-bottom: 10px;">DCSHOP商城用户协议</h1>
        <p style="text-align: center;" class="form-tips">欢迎使用DCSHOP开源商城系统（严禁用于违法违规行为）</p>
        <p style="text-align: center;" class="form-tips">协议版本：v2.0 &nbsp;|&nbsp; 生效日期：2026年1月3日</p>
        
        <div class="layui-timeline">
            <!-- 协议概述 -->
            <div class="layui-timeline-item">
                <i class="layui-icon layui-timeline-axis"></i>
                <div class="layui-timeline-content layui-text agreement-section">
                    <h3 class="layui-timeline-title">协议概述</h3>
                    <p>DCSHOP商城（以下简称"本系统"）是一款完全开源的数字产品自动发卡销售系统，致力于为用户提供安全、高效、便捷的在线商城解决方案。</p>
                    <p>本协议是您（以下简称"用户"）与DCSHOP开发团队（以下简称"我们"）之间关于使用本系统的法律协议。<span class="warning-text">在安装、部署或使用本系统前，请您务必仔细阅读并充分理解本协议的全部内容。</span></p>
                    <p>一旦您开始使用本系统，即表示您已阅读、理解并同意接受本协议的全部条款约束。如您不同意本协议的任何条款，请立即停止使用本系统。</p>
                </div>
            </div>

            <!-- 一、服务说明 -->
            <div class="layui-timeline-item">
                <i class="layui-icon layui-timeline-axis"></i>
                <div class="layui-timeline-content layui-text agreement-section">
                    <h3 class="layui-timeline-title">一、服务说明</h3>
                    
                    <h4>1.1 系统介绍</h4>
                    <p>DCSHOP商城是一个基于 PHP + MySQL 开发的数字产品自动发卡销售系统，主要功能包括：</p>
                    <ul>
                        <li>商品管理：支持多种商品类型（一卡一密、通用卡密、虚拟服务等）</li>
                        <li>订单管理：自动发货、订单查询、售后处理等</li>
                        <li>用户系统：会员注册、登录、余额管理、消费记录等</li>
                        <li>支付集成：支持多种主流支付方式接入</li>
                        <li>分店系统：支持多级分店、独立域名绑定</li>
                        <li>应用市场：提供丰富的模板和插件扩展</li>
                        <li>数据统计：销售报表、用户分析等</li>
                    </ul>
                    
                    <h4>1.2 开源协议</h4>
                    <p>本系统采用 <strong>GNU General Public License v3.0（GPLv3）</strong> 开源协议发布，所有核心代码均为开源代码。根据该协议：</p>
                    <ul>
                        <li>您可以自由使用、复制、修改和分发本系统</li>
                        <li>基于本系统的修改、衍生作品或与其他代码组合后的整体，必须同样采用 GPLv3 协议开源，且需公开完整源代码</li>
                        <li>禁止代码贡献者以专利诉讼威胁其他用户</li>
                        <li>禁止通过技术手段（如数字签名、硬件限制）阻止用户修改或安装自定义版本</li>
                        <li>分发时必须保留原始版权声明和许可证信息</li>
                    </ul>
                    
                    <h4>1.3 授权说明</h4>
                    <p>本系统提供免费版和授权版两种使用方式：</p>
                    <ul>
                        <li><strong>免费版：</strong>可免费使用系统基础功能，但部分高级功能受限</li>
                        <li><strong>授权版：</strong>通过购买授权码解锁全部功能，包括在线升级、应用市场、技术支持等</li>
                    </ul>
                    <p>授权码与域名绑定，一个授权码仅限一个域名使用。授权类型包括VIP会员、SVIP会员、至尊会员等，不同类型享有不同权益。</p>
                </div>
            </div>

            <!-- 二、用户权利与义务 -->
            <div class="layui-timeline-item">
                <i class="layui-icon layui-timeline-axis"></i>
                <div class="layui-timeline-content layui-text agreement-section">
                    <h3 class="layui-timeline-title">二、用户权利与义务</h3>
                    
                    <h4>2.1 用户权利</h4>
                    <p>在遵守本协议的前提下，您享有以下权利：</p>
                    <ul>
                        <li>免费使用系统基础功能，无需支付任何费用</li>
                        <li>获得社区技术支持和帮助文档</li>
                        <li>参与系统改进，提交Bug反馈和功能建议</li>
                        <li>在遵守GPLv3协议的前提下进行二次开发和定制</li>
                        <li>授权用户可享受在线升级、应用市场、专属技术支持等服务</li>
                        <li>对系统提出合理的改进建议和需求</li>
                    </ul>
                    
                    <h4>2.2 用户义务</h4>
                    <p>使用本系统时，您必须遵守以下义务：</p>
                    <ul>
                        <li>严格遵守中华人民共和国相关法律法规及当地法律</li>
                        <li>不得利用本系统从事任何违法违规活动</li>
                        <li>妥善保护终端用户的隐私和数据安全</li>
                        <li>不得恶意攻击、破坏或干扰系统正常运行</li>
                        <li>不得利用系统漏洞进行非法操作或牟利</li>
                        <li>不得将授权码转让、出租、出借给第三方</li>
                        <li>不得破解、反编译或绕过系统授权验证机制</li>
                        <li>定期备份数据，做好安全防护措施</li>
                    </ul>
                    
                    <h4>2.3 账户安全</h4>
                    <p>您应当妥善保管您的账户信息和密码：</p>
                    <ul>
                        <li>使用强密码，定期更换密码</li>
                        <li>不得将账户信息泄露给他人</li>
                        <li>发现账户异常应立即修改密码并通知我们</li>
                        <li>因您自身原因导致的账户安全问题，由您自行承担责任</li>
                    </ul>
                </div>
            </div>

            <!-- 三、禁止行为 -->
            <div class="layui-timeline-item">
                <i class="layui-icon layui-timeline-axis"></i>
                <div class="layui-timeline-content layui-text agreement-section">
                    <h3 class="layui-timeline-title">三、禁止行为（重要）</h3>
                    
                    <div class="danger-box">
                        <p><strong>⚠️ 严正声明：</strong>本系统仅供合法商业用途，严禁用于任何违法违规活动。违反以下规定者，我们有权立即终止服务并保留追究法律责任的权利。</p>
                    </div>
                    
                    <h4>3.1 严禁销售的商品类型</h4>
                    <p>您不得使用本系统销售以下商品或服务：</p>
                    <ul>
                        <li><strong>游戏外挂/作弊工具：</strong>包括但不限于游戏辅助、自动脚本、修改器、透视挂、加速器等破坏游戏公平性的工具</li>
                        <li><strong>破解软件/盗版内容：</strong>未经授权的软件破解版、序列号生成器、盗版影视音乐等侵犯知识产权的内容</li>
                        <li><strong>黄赌毒相关：</strong>色情内容、赌博平台、毒品及相关信息</li>
                        <li><strong>诈骗工具/服务：</strong>钓鱼网站源码、诈骗话术、虚假投资平台等</li>
                        <li><strong>非法支付通道：</strong>洗钱工具、非法第四方支付、跑分平台等</li>
                        <li><strong>个人隐私数据：</strong>非法获取的个人信息、社工库、查档服务等</li>
                        <li><strong>黑客工具：</strong>木马病毒、DDoS攻击服务、漏洞利用工具等</li>
                        <li><strong>虚假证件：</strong>假证、假学历、假资质等</li>
                        <li><strong>违禁物品：</strong>管制刀具、仿真枪支、违禁药品等</li>
                        <li><strong>其他违法内容：</strong>任何违反法律法规或公序良俗的商品和服务</li>
                    </ul>
                    
                    <h4>3.2 禁止的运营行为</h4>
                    <ul>
                        <li>利用本系统搭建传销、资金盘、庞氏骗局等非法平台</li>
                        <li>进行洗钱、非法资金转移等金融犯罪活动</li>
                        <li>发布虚假广告、进行虚假宣传欺骗消费者</li>
                        <li>恶意刷单、虚假交易、操纵评价等不正当竞争行为</li>
                        <li>收集、买卖用户个人信息</li>
                        <li>利用系统漏洞攻击其他用户或第三方</li>
                    </ul>
                    
                    <h4>3.3 违规处理</h4>
                    <p>一经发现用户存在上述违规行为，我们将采取以下措施：</p>
                    <ul>
                        <li>立即终止授权，停止一切技术支持服务</li>
                        <li>将违规信息向相关执法部门举报</li>
                        <li>配合司法机关调查取证</li>
                        <li>保留追究法律责任的权利</li>
                    </ul>
                </div>
            </div>

            <!-- 四、知识产权 -->
            <div class="layui-timeline-item">
                <i class="layui-icon layui-timeline-axis"></i>
                <div class="layui-timeline-content layui-text agreement-section">
                    <h3 class="layui-timeline-title">四、知识产权</h3>
                    
                    <h4>4.1 系统版权</h4>
                    <p>本系统的著作权、商标权及其他知识产权归DCSHOP开发团队所有。虽然本系统采用开源协议，但这不意味着放弃知识产权。</p>
                    
                    <h4>4.2 商标使用</h4>
                    <ul>
                        <li>"DCSHOP"名称及Logo为我们的注册商标</li>
                        <li>未经书面授权，不得将我们的商标用于商业推广</li>
                        <li>不得以任何方式暗示您的产品或服务与我们存在关联</li>
                    </ul>
                    
                    <h4>4.3 用户内容</h4>
                    <p>您通过本系统发布的内容（商品信息、图片等）的知识产权归您所有，但您需保证：</p>
                    <ul>
                        <li>拥有发布内容的合法权利</li>
                        <li>不侵犯任何第三方的知识产权</li>
                        <li>如因侵权引发纠纷，由您自行承担全部责任</li>
                    </ul>
                </div>
            </div>

            <!-- 五、隐私保护 -->
            <div class="layui-timeline-item">
                <i class="layui-icon layui-timeline-axis"></i>
                <div class="layui-timeline-content layui-text agreement-section">
                    <h3 class="layui-timeline-title">五、隐私保护</h3>
                    
                    <h4>5.1 数据收集</h4>
                    <p>本系统在运行过程中可能收集以下信息：</p>
                    <ul>
                        <li>系统版本信息（用于检测更新）</li>
                        <li>域名信息（用于授权验证）</li>
                        <li>基本运行日志（用于问题排查）</li>
                    </ul>
                    <p>我们承诺不会收集您的商业数据、用户信息或其他敏感内容。</p>
                    
                    <h4>5.2 数据安全</h4>
                    <p>作为系统使用者，您有责任保护终端用户的数据安全：</p>
                    <ul>
                        <li>采取必要的技术措施保护用户数据</li>
                        <li>制定并公示隐私政策</li>
                        <li>不得非法收集、使用、泄露用户信息</li>
                        <li>遵守《个人信息保护法》等相关法规</li>
                    </ul>
                    
                    <h4>5.3 数据备份</h4>
                    <p>您应当定期备份系统数据，因未备份导致的数据丢失，我们不承担任何责任。</p>
                </div>
            </div>

            <!-- 六、免责声明 -->
            <div class="layui-timeline-item">
                <i class="layui-icon layui-timeline-axis"></i>
                <div class="layui-timeline-content layui-text agreement-section">
                    <h3 class="layui-timeline-title">六、免责声明</h3>
                    
                    <h4>6.1 服务可用性</h4>
                    <p>我们努力确保系统的稳定性和可用性，但无法保证：</p>
                    <ul>
                        <li>系统100%无故障、无中断运行</li>
                        <li>所有功能完全符合您的特定需求</li>
                        <li>系统与所有第三方服务完全兼容</li>
                        <li>及时修复所有已知问题</li>
                    </ul>
                    
                    <h4>6.2 责任限制</h4>
                    <p>在法律允许的最大范围内，我们不对以下情况承担责任：</p>
                    <ul>
                        <li>因不可抗力（自然灾害、战争、政策变化等）导致的服务中断</li>
                        <li>因您的操作不当、配置错误导致的问题</li>
                        <li>因第三方服务（支付接口、服务器等）故障导致的损失</li>
                        <li>因您违反本协议导致的任何损失</li>
                        <li>因黑客攻击、病毒入侵等安全事件导致的损失</li>
                        <li>任何间接的、附带的、特殊的、惩罚性的损害赔偿</li>
                    </ul>
                    
                    <h4>6.3 法律责任归属</h4>
                    <div class="highlight-box">
                        <p><strong>重要提示：</strong>您使用本系统进行的一切商业活动及其产生的法律后果，均由您自行承担。我们仅提供技术工具，不对您的经营行为负责。如因您的违法行为导致我们遭受损失，您应当赔偿我们的全部损失。</p>
                    </div>
                </div>
            </div>

            <!-- 七、服务变更与终止 -->
            <div class="layui-timeline-item">
                <i class="layui-icon layui-timeline-axis"></i>
                <div class="layui-timeline-content layui-text agreement-section">
                    <h3 class="layui-timeline-title">七、服务变更与终止</h3>
                    
                    <h4>7.1 服务变更</h4>
                    <p>我们保留以下权利：</p>
                    <ul>
                        <li>随时修改、升级系统功能</li>
                        <li>调整服务内容和收费标准</li>
                        <li>暂停或终止部分服务</li>
                    </ul>
                    <p>重大变更将提前通过官方渠道通知。</p>
                    
                    <h4>7.2 服务终止</h4>
                    <p>以下情况我们可能终止对您的服务：</p>
                    <ul>
                        <li>您违反本协议的任何条款</li>
                        <li>您从事违法违规活动</li>
                        <li>您的行为损害我们或其他用户的利益</li>
                        <li>您主动申请终止服务</li>
                    </ul>
                    
                    <h4>7.3 终止后果</h4>
                    <p>服务终止后：</p>
                    <ul>
                        <li>您的授权将被撤销</li>
                        <li>无法继续使用授权功能</li>
                        <li>已支付的费用不予退还</li>
                        <li>您应自行备份所需数据</li>
                    </ul>
                </div>
            </div>

            <!-- 八、协议变更 -->
            <div class="layui-timeline-item">
                <i class="layui-icon layui-timeline-axis"></i>
                <div class="layui-timeline-content layui-text agreement-section">
                    <h3 class="layui-timeline-title">八、协议变更</h3>
                    <p>我们保留随时修改本协议的权利。协议变更时，我们将通过以下方式通知您：</p>
                    <ul>
                        <li>在系统后台重新弹出本协议</li>
                        <li>在官方网站发布更新公告</li>
                        <li>通过系统内消息通知</li>
                    </ul>
                    <p>协议变更后，如您继续使用本系统，即表示您同意接受修改后的协议。如您不同意修改后的协议，应立即停止使用本系统。</p>
                </div>
            </div>

            <!-- 九、争议解决 -->
            <div class="layui-timeline-item">
                <i class="layui-icon layui-timeline-axis"></i>
                <div class="layui-timeline-content layui-text agreement-section">
                    <h3 class="layui-timeline-title">九、争议解决</h3>
                    
                    <h4>9.1 协商解决</h4>
                    <p>因本协议引起的或与本协议有关的任何争议，双方应首先通过友好协商解决。</p>
                    
                    <h4>9.2 法律适用</h4>
                    <p>本协议的订立、执行、解释及争议解决均适用中华人民共和国法律（不包括港澳台地区）。</p>
                    
                    <h4>9.3 管辖法院</h4>
                    <p>协商不成的，任何一方均可向我方所在地有管辖权的人民法院提起诉讼。</p>
                </div>
            </div>

            <!-- 十、其他条款 -->
            <div class="layui-timeline-item">
                <i class="layui-icon layui-timeline-axis"></i>
                <div class="layui-timeline-content layui-text agreement-section">
                    <h3 class="layui-timeline-title">十、其他条款</h3>
                    
                    <h4>10.1 完整协议</h4>
                    <p>本协议构成您与我们之间关于使用本系统的完整协议，取代之前的所有口头或书面协议。</p>
                    
                    <h4>10.2 可分割性</h4>
                    <p>如本协议的任何条款被认定为无效或不可执行，该条款应在最小必要范围内被修改或删除，其余条款仍然有效。</p>
                    
                    <h4>10.3 权利保留</h4>
                    <p>我们未行使或延迟行使本协议项下的任何权利，不构成对该权利的放弃。</p>
                    
                    <h4>10.4 联系方式</h4>
                    <p>如您对本协议有任何疑问，可通过以下方式联系我们：</p>
                    <ul>
                        <li>官方网站：访问DCSHOP官网获取最新信息</li>
                        <li>技术支持：授权用户可加入专属QQ群获取支持</li>
                        <li>问题反馈：通过GitHub提交Issue</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div style="text-align: center; padding: 20px 0; color: #999; font-size: 12px; border-top: 1px solid #eee; margin-top: 20px;">
            <p>© 2024-2026 DCSHOP. All Rights Reserved.</p>
            <p>本协议最终解释权归DCSHOP开发团队所有</p>
        </div>
    </div>
    
    <div style="width: 100%; height: 50px;"></div>
    <div id="form-btn">
        <div class="layui-input-block" style="margin: 0 auto;">
            <?php if($chakan): ?>
                <button type="button" class="layui-btn layui-btn-danger" lay-submit lay-filter="jujue">不再遵守本协议</button>
                <button type="button" class="layui-btn layui-btn-primary layui-border-blue" lay-submit lay-filter="close">关闭窗口</button>
            <?php else: ?>
                <button type="submit" class="layui-btn" lay-submit lay-filter="submit">我已完全阅读并同意此协议</button>
                <button type="button" class="layui-btn layui-btn-primary" id="jujue">不同意</button>
            <?php endif; ?>
        </div>
    </div>
</form>

<script>
    $('#jujue').click(function(){
        parent.location.href="account.php?action=logout"
    })

    layui.use(['table'], function(){
        var $ = layui.$;
        var form = layui.form;
        
        form.on('submit(submit)', function(data){
            layer.confirm('您确定已仔细阅读并同意《DCSHOP商城用户协议》的所有条款？', {
                btn: ['我确定', '去阅读'],
                title: '温馨提示'
            }, function(){
                $.ajax({
                    type: "POST",
                    url: "index.php?action=mianze_ajax",
                    data: {mianze: 1},
                    dataType: "json",
                    success: function (e) {
                        parent.location.reload();
                    },
                    error: function (xhr) {
                        layer.msg(JSON.parse(xhr.responseText).msg);
                    }
                });
            });
            return false;
        });

        form.on('submit(close)', function(data){
            parent.layer.close('xieyi')
            return false;
        });

        form.on('submit(jujue)', function(data){
            $.ajax({
                type: "POST",
                url: "index.php?action=jujue_mianze_ajax",
                data: {jujue: 1},
                dataType: "json",
                success: function (e) {
                    if(e.code == 400){
                        layer.msg(e.msg)
                    }else{
                        parent.location.href="account.php?action=logout"
                    }
                },
                error: function (xhr) {
                    layer.msg(JSON.parse(xhr.responseText).msg);
                }
            });
            return false;
        });
    })

    var maxHeight = $(window.parent).innerHeight() * 0.65;
    $("#open-box").css({
        "max-height": maxHeight + "px",
        "overflow-y": "auto"
    });
</script>
