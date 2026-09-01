<?php 
defined('DC_ROOT') || exit('access denied!');
// 获取撤回时限配置（分钟）
$aftersale_recall_minutes = Option::get('aftersale_recall_minutes');
$aftersale_recall_minutes = ($aftersale_recall_minutes === '' || $aftersale_recall_minutes === null) ? 2 : intval($aftersale_recall_minutes);
// 演示站状态
$isDemoSite = Register::isDemoSite();
?>

<style>
/* 红点角标，小圆点 */
.tab-red-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    background: #ff4d4f;
    border-radius: 50%;
    margin-left: 4px;
    vertical-align: top;
    margin-top: 1px;
}

/* 深色模式下胶囊 Tab 颜色混合 */
html[data-theme="dark"] .order-tabs-wrapper .layui-tabs-header {
    background: rgba(255, 255, 255, 0.08);
}
html[data-theme="dark"] .order-tabs-wrapper .layui-tabs-header li {
    color: #b0b0b0;
}
html[data-theme="dark"] .order-tabs-wrapper .layui-tabs-header li:hover {
    background: rgba(255, 255, 255, 0.08);
    color: #d0d0d0;
}
html[data-theme="dark"] .order-tabs-wrapper .layui-tabs-header li.layui-this {
    background: rgba(255, 255, 255, 0.12);
    color: #e0e0e0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}
/* 聊天弹窗样式 */
.chat-dialog-wrap {
    display: flex;
    flex-direction: column;
    height: 100%;
    background: #f5f5f5;
}
.chat-dialog-header {
    padding: 15px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
}
.chat-dialog-header .order-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}
.chat-dialog-header .order-no {
    font-weight: bold;
    font-size: 15px;
}
.chat-dialog-header .status-tag {
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 12px;
    background: rgba(255,255,255,0.2);
}
.chat-dialog-header .goods-name {
    font-size: 13px;
    opacity: 0.9;
}
.chat-messages-area {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f5f5f5;
}
.chat-date-divider {
    text-align: center;
    margin: 20px 0;
}
.chat-date-divider span {
    background: #e0e0e0;
    padding: 4px 15px;
    border-radius: 12px;
    font-size: 12px;
    color: #666;
}
.chat-message {
    margin-bottom: 20px;
    display: flex;
    align-items: flex-start;
}
.chat-message.from-user {
    flex-direction: row;
}
.chat-message.from-admin {
    flex-direction: row-reverse;
}
.chat-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: bold;
    flex-shrink: 0;
}
.chat-message.from-user .chat-avatar {
    background: linear-gradient(135deg, #52c41a 0%, #73d13d 100%);
    color: #fff;
    margin-right: 12px;
}
.chat-message.from-admin .chat-avatar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    margin-left: 12px;
}
.chat-content-wrap {
    max-width: 70%;
}
.chat-sender-info {
    font-size: 12px;
    color: #999;
    margin-bottom: 6px;
}
.chat-message.from-admin .chat-sender-info {
    text-align: right;
}
.chat-bubble {
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 14px;
    line-height: 1.6;
    word-break: break-word;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.chat-message.from-user .chat-bubble {
    background: #fff;
    color: #333;
    border-top-left-radius: 4px;
}
.chat-message.from-admin .chat-bubble {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border-top-right-radius: 4px;
}
.chat-bubble img {
    max-width: 200px;
    max-height: 200px;
    border-radius: 8px;
    margin-top: 8px;
    cursor: pointer;
    display: block;
}
.chat-message.from-admin .chat-content-wrap {
    position: relative;
}
.chat-recall-btn {
    display: none;
    position: absolute;
    right: 100%;
    top: 50%;
    transform: translateY(-50%);
    margin-right: 8px;
    padding: 4px 8px;
    background: #f5f5f5;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 12px;
    color: #999;
    cursor: pointer;
    white-space: nowrap;
}
.chat-recall-btn:hover {
    background: #ff4d4f;
    border-color: #ff4d4f;
    color: #fff;
}
.chat-message.from-admin:hover .chat-recall-btn {
    display: block;
}
.chat-recalled {
    color: #999 !important;
    font-style: italic;
    background: #f0f0f0 !important;
}
.chat-input-area {
    padding: 15px 20px;
    background: #fff;
    border-top: 1px solid #e8e8e8;
    display: flex;
    gap: 12px;
    align-items: center;
}
.chat-input-area input[type="text"] {
    flex: 1;
    height: 42px;
    border: 1px solid #e0e0e0;
    border-radius: 21px;
    padding: 0 20px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.3s;
}
.chat-input-area input[type="text"]:focus {
    border-color: #667eea;
}
.chat-img-btn {
    width: 42px;
    height: 42px;
    border: 1px solid #e0e0e0;
    border-radius: 50%;
    background: #fff;
    color: #666;
    font-size: 18px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
}
.chat-img-btn:hover {
    border-color: #667eea;
    color: #667eea;
}
.chat-send-btn {
    height: 42px;
    padding: 0 25px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border: none;
    border-radius: 21px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s;
}
.chat-send-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}
.chat-empty {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}
.chat-empty i {
    font-size: 50px;
    color: #ddd;
    margin-bottom: 15px;
    display: block;
}
/* 聊天弹窗关闭按钮 - 白色可见 */
.chat-layer-skin .layui-layer-setwin {
    top: 12px;
    right: 12px;
}
.chat-layer-skin .layui-layer-setwin a {
    background: rgba(255,255,255,0.3) !important;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    line-height: 30px;
    font-size: 20px;
    text-align: center;
    transition: all 0.3s;
}
.chat-layer-skin .layui-layer-setwin a:hover {
    background: rgba(255,255,255,0.5) !important;
}
.chat-layer-skin .layui-layer-setwin .layui-layer-close1::before,
.chat-layer-skin .layui-layer-setwin .layui-layer-close2::before {
    color: #fff !important;
}

/* 移动端适配 */
@media screen and (max-width: 768px) {
    .chat-dialog-header {
        padding: 12px 15px;
    }
    .chat-dialog-header .order-no {
        font-size: 13px;
    }
    .chat-dialog-header .goods-name {
        font-size: 12px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .chat-messages-area {
        padding: 15px;
    }
    .chat-avatar {
        width: 32px;
        height: 32px;
        font-size: 11px;
    }
    .chat-message.from-user .chat-avatar {
        margin-right: 8px;
    }
    .chat-message.from-admin .chat-avatar {
        margin-left: 8px;
    }
    .chat-content-wrap {
        max-width: 75%;
    }
    .chat-bubble {
        padding: 10px 12px;
        font-size: 13px;
    }
    .chat-bubble img {
        max-width: 150px;
        max-height: 150px;
    }
    .chat-input-area {
        padding: 10px 12px;
        gap: 8px;
    }
    .chat-input-area input {
        height: 38px;
        padding: 0 15px;
        font-size: 13px;
    }
    .chat-send-btn {
        height: 38px;
        padding: 0 18px;
        font-size: 13px;
    }
}
</style>

<!-- 状态分类 Tab（统一胶囊结构：.order-tabs-wrapper，padding 挂在内部 <span>，保证整个胶囊都是点击热区） -->
<div class="layui-tabs order-tabs-wrapper" lay-options="{trigger: false}">
    <ul class="layui-tabs-header">
        <li class="layui-this aftersale-tab" data-status=""><span>全部</span></li>
        <li class="aftersale-tab" data-status="0,1"><span>待处理<span class="tab-red-dot" id="dot_pending" style="display:none;"></span></span></li>
        <li class="aftersale-tab" data-status="2"><span>已完成</span></li>
        <li class="aftersale-tab" data-status="4"><span>已拒绝</span></li>
        <li class="aftersale-tab" data-status="3"><span>用户关闭</span></li>
        <li class="aftersale-tab" data-status="" data-reopen="1"><span>重开申请<span class="tab-red-dot" id="dot_reopen" style="display:none;"></span></span></li>
    </ul>
</div>

<!-- 表格 -->
<table id="aftersaleTable" lay-filter="aftersaleTable"></table>
<script type="text/html" id="aftersaleToolbar">
    <div style="display:flex;align-items:center;justify-content:center;position:relative;padding:10px 0px 15px;border-bottom:1px solid #f0f0f0;">
        <span style="position:absolute;left:0;top:50%;transform:translateY(-50%);display:flex;gap:6px;">
            <i style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></i>
            <i style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></i>
        </span>
        <span style="color:#667797;font-size:14px;font-weight:500;">售后订单</span>
    </div>
    <div style="display:flex;align-items:center;justify-content:flex-end;width:100%;">
        <form class="layui-form" style="display:flex;align-items:center;gap:6px;margin:0;">
            <div class="layui-input-inline layui-input-wrap" style="width:160px;margin:0;">
                <input type="text" name="out_trade_no" id="search_out_trade_no" placeholder="订单编号" lay-affix="clear" class="layui-input">
            </div>
            <button type="button" class="layui-btn layui-btn-sm" onclick="searchList()">搜索</button>
            <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" onclick="resetSearch()">重置</button>
        </form>
    </div>
</script>

<!-- 状态列模板 -->
<script type="text/html" id="statusTpl">
    {{# if(d.status == 0){ }}
    <span class="layui-badge layui-bg-orange">待处理</span>
    {{# } else if(d.status == 1){ }}
    <span class="layui-badge layui-bg-blue">处理中</span>
    {{# } else if(d.status == 2){ }}
    <span class="layui-badge layui-bg-green">已完成</span>
    {{# } else if(d.status == 3){ }}
    <span class="layui-badge layui-bg-gray">用户已关闭</span>
    {{# } else if(d.status == 4){ }}
    <span class="layui-badge layui-bg-gray">已拒绝</span>
    {{# } }}
    {{# if(d.reopen_status == 1){ }}
    <span class="layui-badge layui-bg-orange" style="margin-left:4px;">重开待审</span>
    {{# } else if(d.reopen_status == 2){ }}
    <span class="layui-badge layui-bg-cyan" style="margin-left:4px;">已重开</span>
    {{# } else if(d.reopen_status == 3){ }}
    <span class="layui-badge layui-bg-gray" style="margin-left:4px;">重开拒绝</span>
    {{# } }}
</script>

<!-- 操作列模板 -->
<script type="text/html" id="actionTpl">
    <a class="layui-btn layui-btn-xs layui-btn-warm" lay-event="chat">协商历史</a>
    {{# if(d.status == 0 || d.status == 1){ }}
    <a class="layui-btn layui-btn-xs layui-btn-normal" lay-event="handle">处理</a>
    {{# } else if(d.status == 2 || d.status == 3 || d.status == 4){ }}
    {{# if(d.reopen_status == 1){ }}
    <a class="layui-btn layui-btn-xs layui-btn-normal" lay-event="approve_reopen" title="批准重开">批准重开</a>
    <a class="layui-btn layui-btn-xs layui-btn-primary" lay-event="reject_reopen" title="拒绝重开">拒绝</a>
    {{# } else { }}
    <a class="layui-btn layui-btn-xs" lay-event="reopen">重开</a>
    {{# } }}
    {{# } }}
    <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除</a>
</script>

<script>
// 全局变量
var currentAftersaleId = 0;
var statusTextMap = {0: '待处理', 1: '处理中', 2: '已完成', 3: '用户已关闭', 4: '已拒绝'};
var recallMinutes = <?= $aftersale_recall_minutes ?>; // 撤回时限（分钟）
var recallSeconds = recallMinutes * 60; // 撤回时限（秒）
var isDemoSite = <?= $isDemoSite ? 'true' : 'false' ?>; // 演示站模式

// 新消息提示音
var notifySound = new Audio('<?= DC_URL ?>content/plugins/aftersale/1.wav');

// 播放提示音
function playNotifySound() {
    try {
        notifySound.currentTime = 0;
        notifySound.play();
    } catch(e) {}
}

// 格式化聊天内容
function formatChatContent(content) {
    if (!content) return '';
    // 转义HTML
    var escaped = content.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    // 图片标签转换
    escaped = escaped.replace(/\[图片\](https?:\/\/[^\s<]+)/g, '<img src="$1" onclick="window.open(\'$1\')">');
    // 换行
    escaped = escaped.replace(/\n/g, '<br>');
    return escaped;
}

// 加载聊天记录
function loadChatMessages(aftersaleId) {
    currentAftersaleId = aftersaleId;
    var $area = $('#chatMessagesArea');
    $area.html('<div class="dc-loading-area"><i class="ri-loader-4-line"></i><span>加载中...</span></div>');
    
    $.ajax({
        url: '<?= DC_URL ?>admin/aftersale.php?action=get_chat',
        type: 'GET',
        data: {id: aftersaleId},
        dataType: 'json',
        success: function(res) {
            console.log('聊天记录响应:', res);
            // code为0表示成功
            if (res.code == 0 && res.data && res.data.length > 0) {
                renderChatMessages(res.data);
            } else {
                $area.html('<div class="chat-empty"><i class="ri-chat-3-line"></i><br>暂无聊天记录</div>');
            }
        },
        error: function(xhr, status, error) {
            console.log('请求失败:', status, error);
            $area.html('<div class="chat-empty"><i class="ri-error-warning-line"></i><br>加载失败</div>');
        }
    });
}

// 渲染聊天记录
function renderChatMessages(messages) {
    var html = '';
    var lastDate = '';
    var currentTime = Math.floor(Date.now() / 1000);
    
    for (var i = 0; i < messages.length; i++) {
        var msg = messages[i];
        var isAdmin = msg.sender_type == 'admin';
        var isSystem = msg.sender_type == 'system';
        var isRecalled = msg.is_recalled == 1;
        var timeParts = msg.create_time_text.split(' ');
        var msgDate = timeParts[0];
        var msgTime = timeParts[1];
        
        // 日期分隔
        if (msgDate != lastDate) {
            html += '<div class="chat-date-divider"><span>' + msgDate + '</span></div>';
            lastDate = msgDate;
        }
        
        if (isSystem) {
            // 系统消息居中显示
            html += '<div class="chat-date-divider"><span style="background:#fff3cd;color:#856404;">' + msg.content.replace(/【.*?】/, '') + '</span></div>';
        } else {
            html += '<div class="chat-message ' + (isAdmin ? 'from-admin' : 'from-user') + '">';
            html += '<div class="chat-avatar">' + (isAdmin ? '客服' : '买家') + '</div>';
            html += '<div class="chat-content-wrap">';
            html += '<div class="chat-sender-info">' + msgTime + '</div>';
            
            if (isRecalled) {
                html += '<div class="chat-bubble chat-recalled">该消息已撤回</div>';
            } else {
                html += '<div class="chat-bubble">' + formatChatContent(msg.content) + '</div>';
                // 管理员消息且在撤回时限内可撤回
                if (isAdmin && recallSeconds > 0 && (currentTime - msg.create_time) < recallSeconds) {
                    html += '<button class="chat-recall-btn" onclick="recallMessage(' + msg.id + ')">撤回</button>';
                }
            }
            html += '</div>';
            html += '</div>';
        }
    }
    
    $('#chatMessagesArea').html(html);
    
    // 滚动到底部
    var chatBox = document.getElementById('chatMessagesArea');
    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
}

// 发送消息
function sendChatMessage() {
    if (isDemoSite) { layer.msg('演示站暂不支持发送消息', {icon: 0}); return; }
    var content = $('#chatInputField').val().trim();
    if (!content) {
        layer.msg('请输入回复内容');
        return;
    }
    
    var $btn = $('.chat-send-btn');
    $btn.prop('disabled', true).text('发送中...');
    
    $.ajax({
        url: '<?= DC_URL ?>admin/aftersale.php?action=send_chat',
        type: 'POST',
        data: {
            id: currentAftersaleId,
            content: content
        },
        dataType: 'json',
        success: function(res) {
            $btn.prop('disabled', false).text('发送');
            if (res.code == 0) {
                $('#chatInputField').val('');
                // 直接追加消息到聊天框，不重新加载
                appendAdminMessage(content);
            } else {
                layer.msg(res.msg || '发送失败');
            }
        },
        error: function() {
            $btn.prop('disabled', false).text('发送');
            layer.msg('发送失败，请重试');
        }
    });
}

// 追加管理员消息到聊天框
function appendAdminMessage(content) {
    var now = new Date();
    var timeStr = ('0' + now.getHours()).slice(-2) + ':' + ('0' + now.getMinutes()).slice(-2) + ':' + ('0' + now.getSeconds()).slice(-2);
    
    var html = '<div class="chat-message from-admin">';
    html += '<div class="chat-avatar">客服</div>';
    html += '<div class="chat-content-wrap">';
    html += '<div class="chat-sender-info">' + timeStr + '</div>';
    html += '<div class="chat-bubble">' + formatChatContent(content) + '</div>';
    html += '</div>';
    html += '</div>';
    
    var $area = $('#chatMessagesArea');
    // 移除空状态提示
    $area.find('.chat-empty').remove();
    // 追加消息
    $area.append(html);
    // 滚动到底部
    $area.scrollTop($area[0].scrollHeight);
}

// 上传聊天图片
function uploadChatImage(input) {
    if (isDemoSite) { layer.msg('演示站暂不支持上传图片', {icon: 0}); input.value = ''; return; }
    if (!input.files || !input.files[0]) return;
    
    var file = input.files[0];
    
    // 验证文件类型
    if (!file.type.match(/^image\/(jpeg|jpg|png|gif|webp)$/i)) {
        layer.msg('只支持 JPG、PNG、GIF、WEBP 格式的图片');
        input.value = '';
        return;
    }
    
    // 验证文件大小（5MB）
    if (file.size > 5 * 1024 * 1024) {
        layer.msg('图片大小不能超过 5MB');
        input.value = '';
        return;
    }
    
    // 创建预览URL
    var previewUrl = URL.createObjectURL(file);
    
    // 确认发送弹窗
    layer.open({
        type: 1,
        title: '发送图片',
        area: ['400px', 'auto'],
        content: '<div style="padding:20px;text-align:center;"><img src="' + previewUrl + '" style="max-width:100%;max-height:300px;border-radius:8px;"><p style="margin-top:15px;color:#666;">确定发送这张图片吗？</p></div>',
        btn: ['发送', '取消'],
        yes: function(index) {
            layer.close(index);
            doUploadImage(file);
        },
        btn2: function() {
            input.value = '';
            URL.revokeObjectURL(previewUrl);
        },
        cancel: function() {
            input.value = '';
            URL.revokeObjectURL(previewUrl);
        }
    });
}

// 执行图片上传
function doUploadImage(file) {
    var formData = new FormData();
    formData.append('file', file);
    
    var loadIndex = layer.load(1, {shade: [0.3, '#000']});
    
    $.ajax({
        url: '<?= DC_URL ?>admin/aftersale.php?action=upload_chat_image',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(res) {
            layer.close(loadIndex);
            document.getElementById('chatImageInput').value = '';
            
            if (res.code == 0 && res.data && res.data.url) {
                // 发送图片消息
                var content = '[图片]' + res.data.url;
                $.ajax({
                    url: '<?= DC_URL ?>admin/aftersale.php?action=send_chat',
                    type: 'POST',
                    data: {
                        id: currentAftersaleId,
                        content: content
                    },
                    dataType: 'json',
                    success: function(res2) {
                        if (res2.code == 0) {
                            // 直接追加图片消息
                            appendAdminMessage(content);
                        } else {
                            layer.msg(res2.msg || '发送失败');
                        }
                    }
                });
            } else {
                layer.msg(res.msg || '上传失败');
            }
        },
        error: function() {
            layer.close(loadIndex);
            document.getElementById('chatImageInput').value = '';
            layer.msg('上传失败，请重试');
        }
    });
}

// 撤回消息
function recallMessage(msgId) {
    if (isDemoSite) { layer.msg('演示站暂不支持撤回消息', {icon: 0}); return; }
    layer.confirm('确定撤回这条消息吗？', {icon: 3, title: '提示'}, function(index) {
        layer.close(index);
        $.ajax({
            url: '<?= DC_URL ?>admin/aftersale.php?action=recall_chat',
            type: 'POST',
            data: {id: msgId},
            dataType: 'json',
            success: function(res) {
                if (res.code == 0) {
                    layer.msg('已撤回');
                    loadChatMessages(currentAftersaleId);
                } else {
                    layer.msg(res.msg || '撤回失败');
                }
            }
        });
    });
}

// 显示聊天对话框
var chatPollingTimer = null;

function showChatDialog(data) {
    var html = '<div class="chat-dialog-wrap">';
    // 头部
    html += '<div class="chat-dialog-header">';
    html += '<div class="order-info">';
    html += '<span class="order-no">订单：' + data.out_trade_no + '</span>';
    html += '<span class="status-tag">' + statusTextMap[data.status] + '</span>';
    html += '</div>';
    html += '<div class="goods-name">商品：' + data.goods_title + '</div>';
    html += '</div>';
    // 聊天区域
    html += '<div class="chat-messages-area" id="chatMessagesArea">';
    html += '<div class="dc-loading-area"><i class="ri-loader-4-line"></i><span>加载中...</span></div>';
    html += '</div>';
    // 输入区域
    html += '<div class="chat-input-area">';
    html += '<input type="file" id="chatImageInput" accept="image/*" style="display:none;" onchange="uploadChatImage(this)">';
    html += '<button type="button" class="chat-img-btn" onclick="document.getElementById(\'chatImageInput\').click()" title="发送图片"><i class="ri-image-line"></i></button>';
    html += '<input type="text" id="chatInputField" placeholder="输入回复内容...">';
    html += '<button class="chat-send-btn" onclick="sendChatMessage()">发送</button>';
    html += '</div>';
    html += '</div>';
    
    // 根据屏幕宽度设置弹窗尺寸
    var isMobile = window.innerWidth <= 768;
    var dialogWidth = isMobile ? (window.innerWidth - 20) + 'px' : '600px';
    var dialogHeight = isMobile ? (window.innerHeight - 40) + 'px' : '600px';
    
    layer.open({
        type: 1,
        title: false,
        closeBtn: 1,
        skin: 'chat-layer-skin',
        area: [dialogWidth, dialogHeight],
        content: html,
        success: function() {
            loadChatMessages(data.id);
            // 回车发送
            $('#chatInputField').on('keypress', function(e) {
                if (e.keyCode == 13) {
                    e.preventDefault();
                    sendChatMessage();
                }
            });
            // 启动轮询（每5秒刷新一次）
            chatPollingTimer = setInterval(function() {
                loadChatMessagesQuiet(currentAftersaleId);
            }, 5000);
        },
        end: function() {
            // 关闭弹窗时停止轮询
            if (chatPollingTimer) {
                clearInterval(chatPollingTimer);
                chatPollingTimer = null;
            }
        }
    });
}

// 静默加载聊天记录（不显示loading）
function loadChatMessagesQuiet(aftersaleId) {
    if (!aftersaleId) return;
    $.ajax({
        url: '<?= DC_URL ?>admin/aftersale.php?action=get_chat',
        type: 'GET',
        data: {id: aftersaleId},
        dataType: 'json',
        success: function(res) {
            if (res.code == 0 && res.data) {
                // 计算消息签名（包含撤回状态）
                var newSignature = calcMessagesSignature(res.data);
                var $area = $('#chatMessagesArea');
                var currentSignature = $area.data('msg-signature') || '';
                // 签名变化才刷新
                if (newSignature != currentSignature) {
                    // 检查是否有来自用户的新消息（播放提示音）
                    var hasNewUserMsg = checkNewUserMessage(res.data, $area.data('last-user-msg-id') || 0);
                    if (hasNewUserMsg) {
                        playNotifySound();
                    }
                    renderChatMessages(res.data);
                    $area.data('msg-signature', newSignature);
                    // 更新最后一条用户消息ID
                    $area.data('last-user-msg-id', getLastUserMsgId(res.data));
                }
            }
        }
    });
}

// 检查是否有新的用户消息
function checkNewUserMessage(messages, lastUserMsgId) {
    for (var i = messages.length - 1; i >= 0; i--) {
        if (messages[i].sender_type == 'user' && messages[i].id > lastUserMsgId) {
            return true;
        }
    }
    return false;
}

// 获取最后一条用户消息ID
function getLastUserMsgId(messages) {
    for (var i = messages.length - 1; i >= 0; i--) {
        if (messages[i].sender_type == 'user') {
            return messages[i].id;
        }
    }
    return 0;
}

// 计算消息列表签名（用于检测变化）
function calcMessagesSignature(messages) {
    if (!messages || messages.length == 0) return '';
    var parts = [];
    for (var i = 0; i < messages.length; i++) {
        parts.push(messages[i].id + '_' + (messages[i].is_recalled || 0));
    }
    return parts.join(',');
}

layui.use(['table', 'form', 'layer'], function(){
    var table = layui.table;
    var form = layui.form;
    var currentTabStatus = ''; // 当前选中的Tab状态
    
    // 加载各状态数量
    function loadStatusCounts() {
        $.ajax({
            url: '<?= DC_URL ?>admin/aftersale.php?action=get_counts',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.code == 0 && res.data) {
                    $('#dot_pending')[(res.data.pending > 0) ? 'show' : 'hide']();
                    $('#dot_reopen')[(res.data.reopen > 0) ? 'show' : 'hide']();
                }
            }
        });
    }
    
    // 初始加载数量
    loadStatusCounts();
    
    // Tab 点击事件（具体切换逻辑挂在 <li class="aftersale-tab"> 上，用统一的 layui-this 表示选中态）
    $(document).on('click', '.aftersale-tab', function() {
        var $this = $(this);
        if ($this.hasClass('layui-this')) return;

        $('.aftersale-tab').removeClass('layui-this');
        $this.addClass('layui-this');
        
        currentTabStatus = $this.data('status');
        var reopenFilter = $this.data('reopen') || '';
        
        // 重新加载表格
        table.reload('aftersaleTable', {
            where: {
                out_trade_no: $('#search_out_trade_no').val(),
                status: currentTabStatus,
                reopen_status: reopenFilter
            },
            page: {curr: 1}
        });
    });
    
    // 渲染表格
    table.render({
        elem: '#aftersaleTable',
        url: '<?= DC_URL ?>admin/aftersale.php?action=index',
        toolbar: '#aftersaleToolbar',
        defaultToolbar: [],
        page: true,
        limit: 10,
        limits: [10, 20, 50, 100],
        cols: [[
            {type: 'checkbox', fixed: 'left'},
            {field: 'id', title: 'ID', width: 60, sort: true},
            {field: 'out_trade_no', title: '订单编号', width: 180},
            {field: 'goods_title', title: '商品名称', minWidth: 160},
            {field: 'type_text', title: '售后类型', width: 90},
            {field: 'reason', title: '问题描述', minWidth: 173},
            {field: 'contact', title: '联系方式', width: 150},
            {field: 'status', title: '状态', width: 108, templet: '#statusTpl'},
            {field: 'create_time_text', title: '申请时间', width: 160},
            {fixed: 'right', title: '操作', width: 200, templet: '#actionTpl'}
        ]],
        parseData: function(res){
            return {
                "code": res.code == 200 ? 0 : res.code,
                "msg": res.msg,
                "count": res.count,
                "data": res.data
            };
        },
        done: function() {
            // 表格加载完成后刷新数量
            loadStatusCounts();
        }
    });
    
    // 监听工具条
    table.on('tool(aftersaleTable)', function(obj){
        var data = obj.data;
        var event = obj.event;
        
        if (event === 'chat') {
            showChatDialog(data);
        } else if (event === 'handle') {
            showHandleForm(data);
        } else if (event === 'del') {
            layer.confirm('确定删除该售后记录吗？', function(index){
                layer.close(index);
                if (isDemoSite) { layer.msg('演示站暂不支持删除操作', {icon: 0}); return; }
                $.ajax({
                    url: '<?= DC_URL ?>admin/aftersale.php?action=del',
                    type: 'POST',
                    data: {ids: data.id},
                    dataType: 'json',
                    success: function(res){
                        if(res.code == 0){
                            layer.msg(res.data || '删除成功');
                            table.reload('aftersaleTable');
                        } else {
                            layer.msg(res.msg || '删除失败');
                        }
                    }
                });
            });
        } else if (event === 'reopen' || event === 'approve_reopen') {
            var title = data.reopen_status == 1 ? '批准重开申请' : '重开售后';
            var msg = data.reopen_status == 1 
                ? '用户申请理由：' + (data.reopen_reason || '无') + '\n\n确定批准并重新开启售后吗？' 
                : '确定重新开启该售后吗？状态将变为「处理中」';
            layer.confirm(msg, {icon: 3, title: title}, function(index){
                layer.close(index);
                if (isDemoSite) { layer.msg('演示站暂不支持此操作', {icon: 0}); return; }
                $.ajax({
                    url: '<?= DC_URL ?>admin/aftersale.php?action=reopen',
                    type: 'POST',
                    data: {id: data.id},
                    dataType: 'json',
                    success: function(res){
                        if(res.code == 0){
                            layer.msg(res.data || '已重新开启');
                            table.reload('aftersaleTable');
                        } else {
                            layer.msg(res.msg || '操作失败');
                        }
                    }
                });
            });
        } else if (event === 'reject_reopen') {
            layer.prompt({
                formType: 2,
                title: '拒绝重开申请 — 理由：' + (data.reopen_reason || '无'),
                placeholder: '请输入拒绝理由（可选）',
                area: ['400px', '120px']
            }, function(value, index){
                layer.close(index);
                if (isDemoSite) { layer.msg('演示站暂不支持此操作', {icon: 0}); return; }
                $.ajax({
                    url: '<?= DC_URL ?>admin/aftersale.php?action=reject_reopen',
                    type: 'POST',
                    data: {id: data.id, remark: value},
                    dataType: 'json',
                    success: function(res){
                        if(res.code == 0){
                            layer.msg(res.data || '已拒绝');
                            table.reload('aftersaleTable');
                        } else {
                            layer.msg(res.msg || '操作失败');
                        }
                    }
                });
            });
        }
    });
    
    // 搜索
    window.searchList = function(){
        table.reload('aftersaleTable', {
            where: {
                out_trade_no: $('#search_out_trade_no').val(),
                status: currentTabStatus
            },
            page: {curr: 1}
        });
    };
    
    // 重置搜索
    window.resetSearch = function(){
        $('#search_out_trade_no').val('');
        // 重置到全部Tab
        $('.aftersale-tab').removeClass('layui-this');
        $('.aftersale-tab[data-status=""]').addClass('layui-this');
        currentTabStatus = '';
        table.reload('aftersaleTable', {
            where: {},
            page: {curr: 1}
        });
    };
    
    // 处理售后
    window.showHandleForm = function(data){
        var html = '<div style="padding: 20px;">';
        html += '<div class="layui-form-item"><label class="layui-form-label">订单编号</label><div class="layui-input-block"><input type="text" class="layui-input" value="' + data.out_trade_no + '" disabled></div></div>';
        html += '<div class="layui-form-item"><label class="layui-form-label">问题描述</label><div class="layui-input-block"><textarea class="layui-textarea" disabled>' + data.reason + '</textarea></div></div>';
        html += '<div class="layui-form-item"><label class="layui-form-label">处理状态</label><div class="layui-input-block">';
        html += '<select id="handle_status" class="layui-input">';
        html += '<option value="1"' + (data.status == 1 ? ' selected' : '') + '>处理中</option>';
        html += '<option value="2"' + (data.status == 2 ? ' selected' : '') + '>已完成</option>';
        html += '<option value="4"' + (data.status == 4 ? ' selected' : '') + '>已拒绝</option>';
        html += '</select></div></div>';
        html += '<div class="layui-form-item"><label class="layui-form-label">处理备注</label><div class="layui-input-block">';
        html += '<div class="quick-words" style="margin-bottom:10px;display:flex;flex-wrap:wrap;gap:8px;">';
        html += '<span class="quick-word" data-text="已为您补发，请查收">已补发</span>';
        html += '<span class="quick-word" data-text="已为您办理退款，请注意查收">已退款</span>';
        html += '<span class="quick-word" data-text="经核实商品有效，请按说明使用">商品有效</span>';
        html += '<span class="quick-word" data-text="已为您更换新卡密，请重新查看">已换卡</span>';
        html += '<span class="quick-word" data-text="感谢您的反馈，问题已解决">已解决</span>';
        html += '<span class="quick-word" data-text="经核实不符合售后条件，申请已拒绝">不符合条件</span>';
        html += '<span class="quick-word" data-text="商品为虚拟物品，一经售出概不退换">虚拟商品</span>';
        html += '<span class="quick-word" data-text="请提供更多信息以便我们处理">需补充信息</span>';
        html += '</div>';
        html += '<textarea id="handle_remark" class="layui-textarea" placeholder="请输入处理备注，点击上方快捷词可快速填入">' + (data.handle_remark || '') + '</textarea></div></div>';
        html += '</div>';
        html += '<style>.quick-word{display:inline-block;padding:4px 10px;background:#f0f5ff;color:#1890ff;border-radius:4px;font-size:12px;cursor:pointer;transition:all .2s;border:1px solid #d6e4ff;}.quick-word:hover{background:#1890ff;color:#fff;border-color:#1890ff;}</style>';
        
        layer.open({
            type: 1,
            title: '处理售后',
            area: ['500px', 'auto'],
            content: html,
            btn: ['确认', '取消'],
            success: function(){
                // 快捷词点击事件
                $('.quick-word').on('click', function(){
                    var text = $(this).data('text');
                    var $textarea = $('#handle_remark');
                    var current = $textarea.val();
                    if (current) {
                        $textarea.val(current + '\n' + text);
                    } else {
                        $textarea.val(text);
                    }
                    $textarea.focus();
                });
            },
            yes: function(index){
                if (isDemoSite) { layer.msg('演示站暂不支持提交处理', {icon: 0}); return; }
                var status = $('#handle_status').val();
                var remark = $('#handle_remark').val();
                
                $.ajax({
                    url: '<?= DC_URL ?>admin/aftersale.php?action=handle',
                    type: 'POST',
                    data: {
                        id: data.id,
                        status: status,
                        handle_remark: remark
                    },
                    dataType: 'json',
                    success: function(res){
                        if(res.code == 0){
                            layer.msg(res.data || '处理成功');
                            layer.close(index);
                            table.reload('aftersaleTable');
                        } else {
                            layer.msg(res.msg || '处理失败');
                        }
                    }
                });
            }
        });
        
        form.render('select');
    };
});
</script>
