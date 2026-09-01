<?php defined('DC_ROOT') || exit('access denied!'); ?>
<?php if(User::isAdmin()): ?>
<?php
// 获取订单信息
$db = Database::getInstance();
$db_prefix = DB_PREFIX;

// 获取商品信息
$goods_info = $db->once_fetch_array("SELECT g.title, g.cover FROM {$db_prefix}goods g WHERE g.id = {$child_order['goods_id']}");

// 获取下单必填项
$order_required = $db->fetch_all("SELECT * FROM {$db_prefix}order_required WHERE order_id = {$order['id']}");

// 获取快捷短语配置
$plugin_storage = Storage::getInstance('goods_service');
$quick_phrases = $plugin_storage->getValue('quick_phrases');
if (empty($quick_phrases)) {
    $quick_phrases = "服务已完成\n问题已解决\n已处理完毕\n充值成功\n代购完成";
}
$phrases_arr = array_filter(array_map('trim', explode("\n", $quick_phrases)));
?>

<style>
html,
body {
    height: 100%;
    margin: 0;
    overflow: hidden;
    box-sizing: border-box;
}
*,
*::before,
*::after {
    box-sizing: inherit;
}
.deliver-form {
    height: 100vh;
    min-height: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.deliver-container {
    padding: 20px;
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    overflow-x: hidden;
    box-sizing: border-box;
}

/* 订单信息卡片 */
.order-info-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 20px;
    color: #fff;
    margin-bottom: 20px;
}
.order-info-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}
.order-goods-cover {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    object-fit: cover;
    background: rgba(255,255,255,0.2);
}
.order-goods-info {
    flex: 1;
}
.order-goods-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 5px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.order-goods-spec {
    font-size: 13px;
    opacity: 0.9;
}
.order-info-details {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    padding-top: 15px;
    border-top: 1px solid rgba(255,255,255,0.2);
}
.order-detail-item {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
}
.order-detail-label {
    opacity: 0.8;
}
.order-detail-value {
    font-weight: 500;
    min-width: 0;
    word-break: break-all;
}

/* 下单必填项 */
.order-required-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}
.order-required-title {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.order-required-title i {
    color: #667eea;
}
.order-required-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.order-required-item {
    display: flex;
    font-size: 13px;
    padding: 8px 12px;
    background: #fff;
    border-radius: 6px;
    border: 1px solid #eee;
}
.order-required-item .label {
    color: #666;
    min-width: 80px;
}
.order-required-item .value {
    color: #333;
    font-weight: 500;
    word-break: break-all;
}

/* 发货内容区 */
.deliver-content-section {
    margin-bottom: 20px;
}
.section-title {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.section-title i {
    color: #667eea;
}
.deliver-textarea {
    display: block;
    width: 100%;
    max-width: 100%;
    min-height: 120px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 12px;
    font-size: 14px;
    resize: vertical;
    transition: border-color 0.2s;
}
.deliver-textarea:focus {
    border-color: #667eea;
    outline: none;
}

/* 快捷短语 */
.quick-phrases {
    margin-top: 12px;
}
.quick-phrases-title {
    font-size: 12px;
    color: #999;
    margin-bottom: 8px;
}
.quick-phrases-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.quick-phrase-btn {
    padding: 6px 12px;
    background: #f0f2f5;
    border: 1px solid #e0e0e0;
    border-radius: 15px;
    font-size: 12px;
    color: #666;
    cursor: pointer;
    transition: all 0.2s;
}
.quick-phrase-btn:hover {
    background: #667eea;
    border-color: #667eea;
    color: #fff;
}

/* 提示信息 */
.deliver-tips {
    background: #fff7e6;
    border: 1px solid #ffd591;
    border-radius: 8px;
    padding: 12px 15px;
    margin-bottom: 20px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}
.deliver-tips i {
    color: #fa8c16;
    font-size: 16px;
    margin-top: 2px;
}
.deliver-tips-text {
    font-size: 13px;
    color: #873800;
    line-height: 1.6;
}

/* 按钮区 */
#form-btn {
    background: #fff;
    border-top: 1px solid #f0f0f0;
    padding: 12px 20px 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    position: sticky;
    bottom: 0;
    width: 100%;
    z-index: 20;
    box-shadow: 0 -4px 14px rgba(15, 23, 42, .06);
    flex: 0 0 auto;
    min-height: 72px;
}
#form-btn .layui-btn-container { margin: 0; flex-wrap: wrap; }
#form-btn .layui-btn {
    min-width: 120px;
    height: 34px;
    line-height: 34px;
    padding: 0 18px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    border: none;
    transition: all .2s;
    margin: 0;
}
#form-btn .layui-btn i {
    margin-right: 4px;
    font-size: 14px;
}
#form-btn .layui-btn:hover {
    opacity: .85;
}
#form-btn .layui-bg-blue {
    background: #1677ff;
    color: #fff;
}

/* 深色模式 */
.dark-mode .deliver-container,
html.dark-mode .deliver-container {
    background: #1a1a2e;
}
.dark-mode .order-required-section,
html.dark-mode .order-required-section {
    background: #16213e;
}
.dark-mode .order-required-title,
.dark-mode .section-title,
html.dark-mode .order-required-title,
html.dark-mode .section-title {
    color: #e2e8f0;
}
.dark-mode .order-required-item,
html.dark-mode .order-required-item {
    background: #2d3748;
    border-color: #4a5568;
}
.dark-mode .order-required-item .label,
html.dark-mode .order-required-item .label {
    color: #a0aec0;
}
.dark-mode .order-required-item .value,
html.dark-mode .order-required-item .value {
    color: #e2e8f0;
}
.dark-mode .deliver-textarea,
html.dark-mode .deliver-textarea {
    background: #2d3748;
    border-color: #4a5568;
    color: #e2e8f0;
}
.dark-mode .deliver-textarea:focus,
html.dark-mode .deliver-textarea:focus {
    border-color: #16baaa;
}
.dark-mode .quick-phrase-btn,
html.dark-mode .quick-phrase-btn {
    background: #2d3748;
    border-color: #4a5568;
    color: #a0aec0;
}
.dark-mode .quick-phrase-btn:hover,
html.dark-mode .quick-phrase-btn:hover {
    background: #16baaa;
    border-color: #16baaa;
    color: #fff;
}
.dark-mode .deliver-tips,
html.dark-mode .deliver-tips {
    background: #2d3748;
    border-color: #4a5568;
}
.dark-mode .deliver-tips-text,
html.dark-mode .deliver-tips-text {
    color: #ffa940;
}
.dark-mode #form-btn,
html.dark-mode #form-btn {
    background: #16213e;
    border-color: #4a5568;
}

/* 隐藏滚动条 */
html, body { margin: 0; padding: 0; height: 100%; overflow: hidden !important; }
</style>

<form class="layui-form deliver-form" action="/?plugin=goods_service&action=deliver_ajax" id="form">
    <div class="deliver-container" id="open-box">
        <!-- 订单信息卡片 -->
        <div class="order-info-card">
            <div class="order-info-header">
                <?php if(!empty($goods_info['cover'])): ?>
                <img src="<?= $goods_info['cover'] ?>" class="order-goods-cover" alt="">
                <?php endif; ?>
                <div class="order-goods-info">
                    <div class="order-goods-title"><?= htmlspecialchars($goods_info['title'] ?? '商品') ?></div>
                    <?php if(!empty($child_order['attr_spec'])): ?>
                    <div class="order-goods-spec"><?= htmlspecialchars($child_order['attr_spec']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="order-info-details">
                <div class="order-detail-item">
                    <span class="order-detail-label">订单编号</span>
                    <span class="order-detail-value"><?= $order['out_trade_no'] ?></span>
                </div>
                <div class="order-detail-item">
                    <span class="order-detail-label">订单金额</span>
                    <span class="order-detail-value">￥<?= number_format($order['amount'] / 100, 2) ?></span>
                </div>
                <div class="order-detail-item">
                    <span class="order-detail-label">购买数量</span>
                    <span class="order-detail-value"><?= $child_order['quantity'] ?> 件</span>
                </div>
                <div class="order-detail-item">
                    <span class="order-detail-label">支付时间</span>
                    <span class="order-detail-value"><?= date('m-d H:i', $order['pay_time']) ?></span>
                </div>
            </div>
        </div>

        <?php if(!empty($order_required)): ?>
        <!-- 下单必填项 -->
        <div class="order-required-section">
            <div class="order-required-title">
                <i class="ri-file-list-3-line"></i>
                下单必填项
            </div>
            <div class="order-required-list">
                <?php foreach($order_required as $req): ?>
                <div class="order-required-item">
                    <span class="label"><?= htmlspecialchars($req['name']) ?>：</span>
                    <span class="value"><?= htmlspecialchars($req['content']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- 发货内容 -->
        <div class="deliver-content-section">
            <div class="section-title">
                <i class="ri-edit-line"></i>
                发货内容
            </div>
            <textarea id="deliver-content" name="content" placeholder="请填写发货内容，如充值账号、处理结果等..." class="deliver-textarea">服务已完成</textarea>
            
            <div class="quick-phrases">
                <div class="quick-phrases-title">快捷短语（点击填入）</div>
                <div class="quick-phrases-list">
                    <?php foreach($phrases_arr as $phrase): ?>
                    <span class="quick-phrase-btn" data-text="<?= htmlspecialchars($phrase) ?>"><?= htmlspecialchars($phrase) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- 提示信息 -->
        <div class="deliver-tips">
            <i class="ri-information-line"></i>
            <div class="deliver-tips-text">
                确认发货后，订单状态将变为"已完成"，买家可在订单详情中查看发货内容。
            </div>
        </div>

        <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
        <input type="hidden" value="<?= $order['id'] ?>" name="order_id"/>
        <input type="hidden" value="<?= $child_order['id'] ?>" name="order_list_id"/>
        
        <div style="height: 16px;"></div>
    </div>

    <div id="form-btn">
        <div class="layui-btn-container" style="display: inline-flex; gap: 10px; justify-content: center; width: 100%;">
            <button type="submit" class="layui-btn layui-bg-blue" lay-submit lay-filter="submit">
                <i class="layui-icon layui-icon-ok"></i> 手动确认发货
            </button>
            <button type="reset" class="layui-btn layui-btn-primary">
                <i class="layui-icon layui-icon-refresh"></i> 重置
            </button>
        </div>
    </div>
</form>

<script>
layui.use(['form'], function(){
    var $ = layui.$;
    var form = layui.form;
    
    // 快捷短语点击
    $('.quick-phrase-btn').on('click', function() {
        var text = $(this).data('text');
        var $textarea = $('#deliver-content');
        var current = $textarea.val();
        
        // 如果当前内容为空或是默认内容，直接替换
        if (!current || current === '服务已完成') {
            $textarea.val(text);
        } else {
            // 否则追加
            $textarea.val(current + '\n' + text);
        }
        $textarea.focus();
    });
    
    // 表单提交
    form.on('submit(submit)', function(data){
        var field = data.field;
        var url = $('#form').attr('action');
        
        // 验证内容
        if (!field.content || !field.content.trim()) {
            layer.msg('请填写发货内容');
            return false;
        }
        
        $.ajax({
            type: "POST",
            url: url,
            data: field,
            dataType: "json",
            success: function(e) {
                if (e.code == 400) {
                    layer.msg(e.msg);
                    return;
                }
                parent.layer.close('deliver');
                parent.layer.msg('发货成功');
                window.parent.table.reload();
            },
            error: function(xhr) {
                var msg = '操作失败';
                try {
                    msg = JSON.parse(xhr.responseText).msg;
                } catch(e) {}
                layer.msg(msg);
            }
        });
        return false;
    });
});
</script>
<?php endif; ?>
