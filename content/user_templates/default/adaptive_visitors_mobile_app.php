<?php
defined('DC_ROOT') || exit('access denied!');
?>
<style>
    .mvisitor-page { padding: 14px 14px calc(24px + env(safe-area-inset-bottom)); background: #f5f7fb; min-height: 100%; }
    .mvisitor-hero { background: linear-gradient(180deg, #2f63d6 0%, #2b58c8 100%); color: #fff; border-radius: 18px; padding: 18px 16px; box-shadow: 0 12px 28px rgba(47, 99, 214, 0.18); }
    .mvisitor-hero-title { font-size: 20px; font-weight: 700; margin-bottom: 8px; }
    .mvisitor-hero-desc { font-size: 13px; opacity: 0.82; line-height: 1.7; }
    .mvisitor-card { margin-top: 14px; background: linear-gradient(0deg, #fff, #f3f5f8); border: 2px solid #fff; border-radius: 10px; box-shadow: var(--shadow-primary); padding: 16px 14px; }
    .mvisitor-label { display: block; margin-bottom: 10px; color: #2f3a4d; font-size: 14px; font-weight: 600; }
    .mvisitor-input { width: 100%; height: 46px; border: 0; border-bottom: 1px solid #ebeff5; background: transparent; color: #20293a; font-size: 16px; outline: none; }
    .mvisitor-input::placeholder { color: #b8bfca; }
    .mvisitor-hint { margin-top: 10px; color: #7b8699; font-size: 12px; line-height: 1.8; }
    .mvisitor-btn { width: 100%; height: 46px; border: 0; border-radius: 12px; background: #2f75ff; color: #fff; font-size: 16px; font-weight: 600; margin-top: 18px; }
    .mvisitor-tips { margin-top: 14px; background: linear-gradient(0deg, #fff, #f3f5f8); border: 2px solid #fff; border-radius: 10px; box-shadow: var(--shadow-primary); padding: 14px; }
    .mvisitor-tips strong { display: block; margin-bottom: 10px; color: #20293a; font-size: 15px; }
    .mvisitor-tips div { color: #7b8699; font-size: 12px; line-height: 1.9; }
</style>
<div class='mvisitor-page'>
    <div class='mvisitor-hero'>
        <div class='mvisitor-hero-title'>游客查单</div>
        <div class='mvisitor-hero-desc'>未登录用户可通过订单编号或下单时填写的信息，快速查询订单状态与处理结果。</div>
    </div>
    <div class='mvisitor-card'>
        <form class='layui-form' id='form'>
            <label class='mvisitor-label' for='out_trade_no'>查询信息</label>
            <input id='out_trade_no' type='text' name='out_trade_no' placeholder='请输入订单编号或下单信息' class='mvisitor-input' autocomplete='off'>
            <div class='mvisitor-hint'>支持输入订单编号、联系方式或下单时填写的关联信息。</div>
            <input name='token' value='<?= LoginAuth::genToken() ?>' type='hidden'>
            <button type='submit' lay-submit lay-filter='submit' class='mvisitor-btn layui-btn'>立即查询</button>
        </form>
    </div>
    <div class='mvisitor-tips'>
        <strong>查询说明</strong>
        <div>1. 支持按订单编号或下单时填写的信息查询。</div>
        <div>2. 若查询不到结果，请核对输入信息后重试。</div>
        <div>3. 如仍无法查询，请联系网站客服协助处理。</div>
    </div>
</div>
<script>
layui.use(['form'], function(){
    var $ = layui.$;
    var form = layui.form;
    form.on('submit(submit)', function(data){
        var loadIndex = layer.load(2);
        $.ajax({
            type: 'POST',
            url: '<?= DC_URL ?>/user/visitors.php?action=visitors_search_order_count&origin=local',
            data: data.field,
            dataType: 'json',
            success: function(e){
                layer.close(loadIndex);
                if (e.code == 200) {
                    if (e.data.order_count > 0) {
                        var searchJson = JSON.stringify(e.data._search);
                        var base64Search = btoa(unescape(encodeURIComponent(searchJson)));
                        window.location.href = 'visitors.php?action=get_visitors_order&_search=' + base64Search;
                    } else {
                        layer.msg('没有查找到任何订单', {icon: 2, time: 3000});
                    }
                } else {
                    layer.msg(e.msg || '查询失败', {icon: 2, time: 3000});
                }
            },
            error: function(){
                layer.close(loadIndex);
                layer.msg('查询失败，请稍后重试', {icon: 2, time: 3000});
            }
        });
        return false;
    });
});
</script>
