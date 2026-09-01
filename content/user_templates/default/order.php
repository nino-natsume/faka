<?php
defined('DC_ROOT') || exit('access denied!');

$order_required = getOrderRequired();

$rq = '';
foreach($order_required as $key => $val){
    $rq .= '/' . $val['name'];
}

?>
<style>
    .content-container {
        margin: 0 auto;
        padding: 0 15px;
    }

    .page-title {
        text-align: center;
        margin-bottom: 10px;
        font-size: 28px;
        color: #333;
        font-weight: 600;
    }

    .page-desc {
        text-align: center;
        color: #666;
        margin-bottom: 10px;
        font-size: 14px;
    }
    /* 订单列表容器 */
    .order-list {
        padding: 10px 15px;
    }

    /* 订单卡片 */
    .order-card {
        background-color: #fff;
        border-radius: 8px;
        margin-bottom: 15px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    /* 订单头部信息 */
    .order-header-info {
        padding: 15px;
        border-bottom: 1px solid #f2f2f2;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .order-no {
        color: #666;
        font-size: 13px;
    }

    .order-status {
        font-weight: 500;
    }

    /* 订单商品列表 */
    .order-goods {
        padding: 10px 15px;
    }

    .goods-item {
        display: flex;
        padding: 10px 0;
        border-bottom: 1px solid #f9f9f9;
    }

    .goods-img {
        width: 80px;
        height: 80px;
        border-radius: 4px;
        object-fit: cover;
        margin-right: 10px;
        background-color: #f5f5f5;
    }

    .goods-info {
        flex: 1;
        min-width: 0;
    }

    .goods-name {
        font-size: 14px;
        color: #333;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 5px;
    }

    .goods-spec {
        font-size: 12px;
        color: #999;
        margin-bottom: 5px;
    }

    .goods-price-count {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .goods-price {
        color: #f53f3f;
        font-weight: 500;
    }

    .goods-count {
        color: #666;
        font-size: 13px;
    }

    /* 订单金额信息 */
    .order-amount {
        padding: 12px 30px;
        text-align: right;
        border-top: 1px solid #f2f2f2;
    }

    .amount-text {
        color: #666;
        margin-bottom: 5px;
    }

    .amount-value {
        color: #f53f3f;
        font-size: 15px;
        font-weight: 500;
    }

    /* 订单操作按钮区 */
    .order-actions {
        padding: 12px 15px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        border-top: 1px solid #f2f2f2;
    }

    .action-btn {
        padding: 6px 15px;
        border-radius: 4px;
        font-size: 14px;
        cursor: pointer;
        border: 1px solid #ddd;
        background-color: #fff;
        color: #666;
    }

    .action-btn.primary {
        background-color: #1677ff;
        color: #fff;
        border-color: #1677ff;
    }

    .action-btn.danger {
        background-color: #fff;
        color: #f53f3f;
        border-color: #f53f3f;
    }

    /* 空状态 */
    .empty-order {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }

    .empty-icon {
        font-size: 60px;
        margin-bottom: 20px;
        color: #ddd;
    }
    .pay-time-text{
        text-align: left;
    }

    /* 适配移动端 */
    @media screen and (max-width: 768px) {
        .order-actions {
            flex-wrap: wrap;
        }
        .pay-time-text{
            text-align: right;
        }

        .action-btn {
            flex: 1;
            min-width: 120px;
            text-align: center;
        }

        .goods-img {
            width: 70px;
            height: 70px;
        }
    }
</style>

<!-- 主内容区 -->
<main class="main-content">
    <div class="content-container">
        <h1 class="page-title">会员订单列表</h1>
        <p class="page-desc">本页面适用于展示登录用户的下单记录</p>
    </div>

    <div class="order-list" id="orderContainer">
        <?php foreach($list as $val): ?>
            <div class="order-card" data-order-id="1000">
                <div class="order-header-info">
                    <div class="order-no">订单编号: <?= $val['out_trade_no'] ?></div>
                    <div class="order-status" style="color: #ff7d00"><?= $val['status_text'] ?></div>
                </div>
                <div class="order-goods">

                    <div class="goods-item">
                        <a href="<?=  $val['url'] ?>" target="_blank">
                            <img src="<?= $val['cover'] ?>" class="goods-img" alt="<?= $val['title'] ?>">
                        </a>

                        <div class="goods-info">
                            <div class="goods-name"><a target="_blank" href="<?=  $val['url'] ?>"><?= $val['title'] ?></a></div>
                            <div class="goods-spec"><?= $val['attr_spec'] ?></div>
                            <div class="goods-spec"><?= $val['attach_user_text'] ?></div>
                        </div>
                    </div>

                </div>
                <div class="order-amount layui-row">

                    <div class="amount-text pay-time-text layui-col-md6 layui-col-sm12" >
                        <?php if(!empty($val['pay_time'])): ?>
                            付款时间：<?= $val['pay_time_text'] ?>
                        <?php endif; ?>
                    </div>
                    <div class="layui-col-md6 layui-col-sm12">
                        <span class="amount-text">共<?= $val['quantity']  ?>件商品 合计:</span>
                        <span class="amount-value">￥<?= $val['amount'] ?></span>
                    </div>
                </div>
                <div class="order-actions">
                    <?php doAction('user_order_list_btn', $val, $val); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</main>

<script>
    $(function () {
        $('.pay-btn').click(function(){
            let key = $(this).data('key');
            let pay_plugin = $(this).data('pay-plugin');
            let pay_name = $(this).data('pay-name');
            $('#pay_plugin-' + key).val(pay_plugin);
            $('#pay_name-' + key).val(pay_name);
            $('#payment-' + key).val(pay_name);
            $('.go-pay-' + key).click();
        })
    });
</script>

<script>
    $('#menu-order').addClass('open');
    $('#menu-order > ul').css('display', 'block');
    $('#menu-order > a > i.nav_right').attr('class', 'fa fa-angle-down nav_right');
    $('#menu-order-user').addClass('menu-current');
</script>