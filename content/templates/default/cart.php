<?php
/**
 * 阅读文章页面
 */
defined('DC_ROOT') || exit('access denied!');
?>

<style>
    .edit-sku-btn{
        color: #00C3DA;
    }
</style>

<article class="container log-con blog-container">
    <span class="back-top mh" onclick="history.go(-1);">&laquo;</span>

    <div style="text-align: right">
        <form id="myForm" action="?action=confirm_order" method="post">
            <!-- 隐藏字段将在这里动态添加 -->
            <button type="submit" class="btn" id="settle-btn">立即结算</button>
        </form>
    </div>

    <hr class="bottom-5"/>
    <div action="?action=mode" method="post">

        <?php foreach($carts as $key => $val){ ?>
            <div id="cart-<?= $val['cart_id'] ?>">
                <h1>
                    <?= $val['title'] ?>
                    <?php if(empty($val['sku_exist'])): ?>
                    <span style="color: red;">已失效</span>
                    <?php endif ?>
                    <input type="checkbox" class="select-cart" data-cart_id="<?= $val['cart_id'] ?>" />

                </h1>
                <p><?= $val['attr_sku'] ?> <a href="javascript:;" class="edit-sku-btn" data-cart_id="<?= $val['cart_id'] ?>">修改规格</a></p>
                <p>单价：<?= number_format($val['price'], 2) ?></p>
                <p>数量：1</p>
                <div>
                    <input id="cart_<?= $val['cart_id'] ?>_goods_id" name="product[<?= $key ?>][goods_id]" type="hidden" value="<?= $val['goods_id'] ?>" />
                    <input id="cart_<?= $val['cart_id'] ?>_quantity" name="product[<?= $key ?>][quantity]" type="hidden" value="1" />
                    <input id="cart_<?= $val['cart_id'] ?>_sku" name="product[<?= $key ?>][sku]" type="text" value="<?= $val['sku'] ?>" />

                </div>
                <hr class="bottom-5"/>
            </div>
        <?php } ?>

    </div>

    <div id="edit-sku-popup" style="display: none; width: 400px; height: 500px; background: #fff; border: 2px solid #ccc; border-radius: 3px; position: fixed; top: 50%; margin-top: -250px; left: 50%; margin-left: -200px;">
        <div style="padding: 20px; width: 100%; height: 430px;" id="edit-sku-content">

        </div>

        <div style="width: 100%; height: 60px; border-top: 2px solid #ccc; position: absolute; bottom: 0; text-align: center;">
            <span>
                <input id="select-sku-status" type="hidden" value="1" />
                <input id="sku-input" type="hidden" value="" />
                <input id="cart-id" type="hidden" value="" />
                <button href="javascript:;" class="btn" id="edit-sku-cancel">取消</button>
                <button href="javascript:;" class="btn" id="save-cart-sku-btn">保存</button>
            </span>

        </div>
    </div>


    <?php doAction('cart') ?>

    <div style="clear:both;"></div>
</article>

<script>
    var keys = {};
    var data = {}
</script>
<script src="<?= TEMPLATE_URL ?>js/sku.js?v=<?= $version ?>&t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>

<script>
    $('#settle-btn').click(function(){
        if(select_cart.length <= 0) {
            alert('请选择要结算的商品');
            return false;
        }

        // 动态创建隐藏字段
        select_cart.forEach((item, index) => {
            const goodsIdInput = document.createElement('input');
            goodsIdInput.type = 'hidden';
            goodsIdInput.name = `product[${index}][goods_id]`;
            goodsIdInput.value = item.goods_id;
            document.getElementById('myForm').appendChild(goodsIdInput);
            const quantityInput = document.createElement('input');
            quantityInput.type = 'hidden';
            quantityInput.name = `product[${index}][quantity]`;
            quantityInput.value = item.quantity;
            document.getElementById('myForm').appendChild(quantityInput);

            const skuInput = document.createElement('input');
            skuInput.type = 'hidden';
            skuInput.name = `product[${index}][sku]`;
            skuInput.value = item.sku;
            document.getElementById('myForm').appendChild(skuInput);
        });

    })

    let select_cart = [];
    // 选择购物车商品
    $('.select-cart').change(function(){
        let cart_id = $(this).data('cart_id');
        let goods_id = $('#cart_' + cart_id + '_goods_id').val();
        let quantity = $('#cart_' + cart_id + '_quantity').val();
        let sku = $('#cart_' + cart_id + '_sku').val();
        if($(this).is(':checked')){ // 添加数据
            select_cart.push({
                cart_id: cart_id,
                goods_id: goods_id,
                quantity: quantity,
                sku: sku
            })
        }else{
            select_cart = select_cart.filter(item => item.cart_id !== cart_id);
        }
        console.log(select_cart)
    })


    // 加入购物车
    $('#save-cart-sku-btn').click(function(){
        if($('#select-sku-status').val() == 0){
            alert('请选择您要购买的商品信息');
            return false;
        }
        let cart_id = $('#cart-id').val();
        let sku = $('#sku-input').val();
        var params = {
            cart_id: cart_id,
            sku: sku
        };
        var api = "<?= DC_URL ?>?rest-api=save_cart_sku_btn";
        $.post(api, params, function(e){
            if(e.code == 200){
                $('#cart_' + cart_id + '_sku').val(sku)
                $('#edit-sku-popup').fadeOut(300);
            }
        }, "json")
    })
</script>

<script>
    $('.edit-sku-btn').click(function(e){
        $('#edit-sku-popup').fadeIn(300);
        let cart_id = $(this).data('cart_id');
        let goods_id = $('#cart_' + cart_id + '_goods_id').val();
        let sku = $('#cart_' + cart_id + '_sku').val();
        sku = sku.split('-');
        let url = "<?= DC_URL ?>?rest-api=getOneGoods";


        $('#cart-id').val(cart_id);

        $.post(url, { goods_id: goods_id }, function(e){

            let click_id = 0;
            let html = `<div>`;
            for(var i = 0; i < e.data.specification.length; i++){
                html += `
                    <div style="margin-bottom: 10px;">
                        ${e.data.specification[i].title}：
                `;
                for(var j = 0; j < e.data.specification[i].attr.length; j++){
                    if(sku.includes(e.data.specification[i].attr[j].id)){
                        click_id++;
                        html += `<input id="click-${click_id}" type="button" class="sku bh-sku-selected" attr_id="${e.data.specification[i].attr[j].id}" value="${e.data.specification[i].attr[j].name}"/>`
                    }else{
                        html += `<input type="button" class="sku" attr_id="${e.data.specification[i].attr[j].id}" value="${e.data.specification[i].attr[j].name}"/>`
                    }

                }
                html += `</div>`;
            }
            html += `价格： <span id="price">--</span></div>`;


            $('#edit-sku-content').html(html);

            keys = JSON.parse(e.data.specification_attr_json);
            data = JSON.parse(e.data.goods_sku_json);

            initSKU();
            initPrices()
            initDisabled()

            clickSku($('#click-' + click_id));


        }, "json")
    })
    $('#edit-sku-cancel').click(function(){
        $('#edit-sku-popup').fadeOut(300, 'linear');
    })




</script>
