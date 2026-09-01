<?php
/**
 * 阅读文章页面
 */
defined('DC_ROOT') || exit('access denied!');

$mode_payment = getPayment();
$order_required = isset($order_required) && is_array($order_required) ? $order_required : getOrderRequired();

?>

<style>
    /* 基础样式 */
    .order-container {
        max-width: 1200px;
        margin: 0 auto;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    /* 面板样式 */
    .panel {
        background: #fff;
        border: 1px solid #eaeaea;
        border-radius: 4px;
        margin-bottom: 20px;
        overflow: hidden;
    }

    .panel-heading {
        padding: 15px;
        font-size: 16px;
        font-weight: 500;
        color: #333;
        border-bottom: 1px solid #f0f0f0;
        background-color: #fafafa;
    }

    /* 商品表格布局 */
    .order-items {
        width: 100%;
        border-collapse: collapse;
    }

    .order-items th {
        text-align: left;
        padding: 12px 15px;
        font-weight: 500;
        color: #666;
        background: #f9f9f9;
        border-bottom: 1px solid #eee;
    }

    .order-items td {
        padding: 15px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }

    /* 商品图片 */
    .item-image img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 3px;
        border: 1px solid #f0f0f0;
    }

    /* 商品信息 */
    .item-info .title {
        font-size: 15px;
        color: #333;
        font-weight: 500;
        margin-bottom: 5px;
    }

    .item-info .spec {
        font-size: 13px;
        color: #888;
    }

    /* 价格样式 */
    /*.item-price, .item-total {*/
    /*    color: #f03d37;*/
    /*    font-weight: 500;*/
    /*    white-space: nowrap;*/
    /*}*/
    .item-price, .item-total {
        color: #f03d37;
        font-weight: 500;
        white-space: nowrap;
        width: 150px; /* 添加固定宽度 */
    }

    .item-quantity{
        width: 200px;
    }
    .item-image{
        width: 150px;
    }

    .item-price .currency, .item-total .currency {
        font-size: 13px;
    }

    /* 数量选择器 */
    .quantity-selector {
        display: inline-flex;
        align-items: center;
        border: 1px solid #ddd;
        border-radius: 3px;
        overflow: hidden;
    }

    .quantity-selector .btn {
        width: 30px;
        height: 30px;
        background: #f8f8f8;
        border: none;
        font-size: 16px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #666;
    }

    .quantity-selector .btn:hover {
        background: #eee;
    }

    .quantity-selector input {
        width: 40px;
        height: 30px;
        border: none;
        text-align: center;
        font-size: 14px;
        -moz-appearance: textfield;
    }

    .quantity-selector input::-webkit-outer-spin-button,
    .quantity-selector input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* 支付方式 */
    .mode-payment {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        padding: 15px !important;
    }

    .select-pay {
        display: inline-flex;
        align-items: center;
        padding: 8px 12px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .select-pay img {
        width: 24px;
        height: 24px;
        margin-right: 8px;
    }

    .select-pay.active,
    .select-pay:hover {
        border-color: #1980ff;
    }

    /* 提交按钮 */
    .submit-order {
        display: flex;
        justify-content: flex-end;
        padding: 10px 15px;
        border-top: 1px solid #f0f0f0;
    }

    #sku-buy-btn {
        padding: 7px 30px;
        background: #1980ff;
        border: none;
        border-radius: 4px;
        color: white;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s;
    }

    #sku-buy-btn:hover {
        background: #0c6fe4;
    }

    /* 响应式调整 */
    @media (max-width: 768px) {
        .order-items thead {
            display: none;
        }

        .order-items, .order-items tbody, .order-items tr, .order-items td {
            display: block;
            width: 100%;
        }

        .order-items tr {
            position: relative;
            padding: 15px 0;
        }

        .order-items td {
            padding: 5px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-items td::before {
            content: attr(data-label);
            font-weight: 500;
            color: #666;
            margin-right: 15px;
        }

        .item-image img {
            width: 80px;
            height: 80px;
        }

        .mode-payment {
            flex-direction: column;
            align-items: flex-start;
        }

        .submit-order {
            justify-content: center;
        }

        #sku-buy-btn {
            width: 100%;
        }
    }
</style>

<article class="container order-container" style="margin-top: 85px;">
    <form action="<?= empty($mode_payment) ? 'javascript:;' : '/?action=pay' ?>" method="post">
        <div class="panel">
            <table class="order-items">
                <thead>
                <tr>
                    <th>商品</th>
                    <th>规格</th>
                    <th>单价</th>
                    <th>数量</th>
                    <th>小计</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($order_list_insert as $key => $val): ?>
                    <tr>
                        <!-- 商品图片 -->
                        <td class="item-image" data-label="商品">
                            <img src="<?= !empty($val['goods']['cover']) ? $val['goods']['cover'] : DC_URL . 'admin/views/images/cover.svg' ?>" alt="封面图" onerror="this.src='<?= DC_URL ?>admin/views/images/cover.svg';">
                        </td>

                        <!-- 商品名称和规格 -->
                        <td class="item-info" data-label="规格">
                            <div class="title"><?= $val['goods']['title'] ?></div>
                            <div class="spec"><?= $val['attr_spec'] ?></div>
                        </td>

                        <!-- 单价 -->
                        <td class="item-price" data-label="单价">
                            <span class="currency">¥</span>
                            <span><?= $val['unit_price'] ?></span>
                        </td>

                        <!-- 数量选择器 -->
                        <td class="item-quantity" data-label="数量">
                            <div class="quantity-selector">
                                <span type="button" class="btn decrease">-</span>
                                <input type="number" name="product[<?= $key ?>][quantity]"
                                       value="<?= $val['quantity'] ?>" min="1" class="quantity-input">
                                <span type="button" class="btn increase">+</span>
                            </div>
                        </td>

                        <!-- 小计 -->
                        <td class="item-total" data-label="小计">
                            <span class="currency">¥</span>
                            <span class="total-price"><?= $val['unit_price'] * $val['quantity'] ?></span>
                        </td>

                        <!-- 隐藏字段 -->
                        <input type="hidden" name="product[<?= $key ?>][goods_id]" value="<?= $val['goods_id'] ?>">
                        <input type="hidden" name="product[<?= $key ?>][sku]" value="<?= $val['sku'] ?>">
                    </tr>
                    <?php if(!empty($val['goods']['attach_user'])): ?>
                        <tr>
                           <td colspan="5">
                               <div class="form-row">
                                   <?php foreach($val['goods']['attach_user'] as $au_item): ?>
                                       <?php if(is_array($au_item) && isset($au_item['name'])): ?>
                                           <div class="form-group col-md-3">
                                               <label><?= $au_item['name'] ?></label>
                                               <input name="product[<?= $key ?>][attach_user][<?= $au_item['name'] ?>]" type="<?= ($au_item['type'] ?? 'text') === 'email' ? 'email' : (($au_item['type'] ?? '') === 'tel' ? 'tel' : (($au_item['type'] ?? '') === 'num' ? 'number' : 'text')) ?>" class="form-control" placeholder="<?= $au_item['placeholder'] ?? '' ?>" <?= (!isset($au_item['required']) || $au_item['required']) ? 'required' : '' ?>>
                                               <?php if(!empty($au_item['tip'])): ?><small style="color:#999;"><?= $au_item['tip'] ?></small><?php endif; ?>
                                           </div>
                                       <?php endif; ?>
                                   <?php endforeach; ?>
                               </div>
                           </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <?php if($mode_payment): ?>
            <div class="panel-body mode-payment">
                <input type="hidden" name="payment" id="payment">
                <input type="hidden" name="pay_plugin" id="pay_plugin">
                <input type="hidden" name="pay_name" id="pay_name">

                <?php foreach($mode_payment as $key => $val): ?>
                    <?php if (!ISLOGIN && $val['plugin_name'] === 'balance') continue; ?>
                    <span class="select-pay <?= isset($val['active']) ? 'active' : '' ?>"
                          data-pay_plugin="<?= $val['plugin_name'] ?>"
                          data-pay_name="<?= $val['name'] ?>"
                          data-payment="<?= $val['title'] ?>">
                        <img src="<?= $val['icon'] ?>" alt="">
                        <span><?= $val['title'] ?></span>
                    </span>
                <?php endforeach; ?>
            </div>


            <div class="submit-order" style="display: block;">

                <?php if(!empty($order_required)): ?>
                <?php foreach($order_required as $val): ?>
                    <div class="form-group form-inline ml-3" style="float: right;">
                        <label><?= $val['name'] ?>：</label>
                        <input type="text" <?= (!isset($val['required']) || $val['required']) ? 'required' : '' ?> name="order_required[<?= $val['name'] . '_type_' . $val['type'] ?>]" class="form-control" placeholder="<?= $val['placeholder'] ?>">
                    </div>
                <?php endforeach; ?>
                <?php endif; ?>

                <div style="text-align: right; clear: both;">
                    <input type="submit" value="提交订单" id="sku-buy-btn">
                </div>
            </div>




            <?php else: ?>
            <div class="card-body">暂未配置收款方式，请联系客服人员进行配置~</div>

                <div class="submit-order">
                    <button type="button" class="btn btn-primary" disabled>未配置收款方式</button>
                </div>
            <?php endif; ?>
        </div>
    </form>

    <?php doAction('confirm') ?>
</article>

<script>
    // 数量选择器功能
    document.querySelectorAll('.quantity-selector').forEach(selector => {
        const input = selector.querySelector('input');
        const decrease = selector.querySelector('.decrease');
        const increase = selector.querySelector('.increase');
        const row = selector.closest('tr');

        // 减少数量
        decrease.addEventListener('click', () => {
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
                updateTotalPrice();
            }
        });

        // 增加数量
        increase.addEventListener('click', () => {
            input.value = parseInt(input.value) + 1;
            updateTotalPrice();
        });

        // 输入验证
        input.addEventListener('change', () => {
            if (parseInt(input.value) < 1) input.value = 1;
            updateTotalPrice();
        });

        // 更新小计
        function updateTotalPrice() {
            const price = parseFloat(row.querySelector('.item-price span:last-child').textContent);
            const total = price * parseInt(input.value);
            row.querySelector('.total-price').textContent = total.toFixed(2);
        }
    });

    // 支付方式选择
    document.querySelectorAll('.select-pay').forEach(item => {
        item.addEventListener('click', () => {
            document.querySelectorAll('.select-pay').forEach(i => i.classList.remove('active'));
            item.classList.add('active');
            document.getElementById('payment').value = item.dataset.payment;
            document.getElementById('pay_plugin').value = item.dataset.pay_plugin;
            document.getElementById('pay_name').value = item.dataset.pay_name;
        });
    });
</script>


<script>

    $('#pay_plugin').val($('.select-pay.active').data('pay_plugin'))
    $('#payment').val($('.select-pay.active').data('payment'))
    $('#pay_name').val($('.select-pay.active').data('pay_name'))

    $('.select-pay').click(function(){
        //选中自己，兄弟节点取消选中
        $(this).addClass('active').siblings().removeClass('active');
        $('#payment').val($(this).data('payment'))
        $('#pay_plugin').val($(this).data('pay_plugin'))
        $('#pay_name').val($(this).data('pay_name'))
    })
</script>

<?php include View::getCommonView('footer') ?>
