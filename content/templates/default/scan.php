<?php
/**
 *
 */
defined('DC_ROOT') || exit('access denied!');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>支付宝扫码支付1</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "PingFang SC", "Helvetica Neue", Arial, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }

        .payment-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            width: 320px;
            padding: 30px;
            text-align: center;
        }

        .logo {
            width: 40px;
            height: 40px;
            margin-bottom: 15px;
        }

        .title {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 20px;
            color: #1677ff;
        }

        .amount {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 25px;
        }

        .qrcode-container {
            padding: 15px;
            margin: 0 auto 25px;
            border: 1px solid #eee;
            border-radius: 8px;
            display: inline-block;
            background-color: white;
        }

        .qrcode {
            width: 180px;
            height: 180px;
            background-color: #1677ff;
            margin: 0 auto;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 14px;
        }

        .instructions {
            font-size: 14px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .footer {
            font-size: 12px;
            color: #999;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="payment-container">
    <img src="https://img.alicdn.com/tfs/TB13DzOjXP7gK0jSZFjXXc5aXXa-212-48.png" alt="支付宝" class="logo">
    <h1 class="title">支付宝扫码支付</h1>
    <div class="amount">¥ 100.00</div>

    <div class="qrcode-container">
        <div class="qrcode">[二维码图片]</div>
    </div>

    <p class="instructions">请打开支付宝扫一扫<br>扫描二维码完成支付</p>

    <div class="footer">
        支付完成后，页面将自动刷新
    </div>
</div>


<script src="../../../admin/views/components/zui-1.10.0/lib/jquery/jquery.js"></script>

<script src="<?= TEMPLATE_URL ?>js/qrcode.min.js?v=<?= $version ?>&t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>
<script type="text/javascript">

    $('#is_pay').hide();

    new QRCode(document.getElementById("qrcode"), "<?= $order_info['qr_code'] ?>", {
        width: 220,
        height: 220,
    });

    var out_trade_no = '<?= $order_info['out_trade_no'] ?>';

    function checkPay(){
        $('#is_pay').hide();
        $('#check').show();
        setTimeout(function(){
            $.ajax({
                url: "?action=is_pay",
                type: "POST",
                data: { out_trade_no: out_trade_no },
                dataType: "json",
                success: function(e) {
                    if(e.data.is_pay){
                        alert('支付完成，点击跳转到订单页');
                        location.href = e.data.url;
                    }else{
                        setTimeout(function(){
                            checkPay()
                        }, 5000);
                    }
                },
                error: function(xhr, status, error) {
                    $('#check').hide();
                    $('#is_pay').show();
                }
            });
        }, 800);

    }
    setTimeout(function(){
        checkPay();
    }, 3000);



</script>
</body>
</html>
<!---->
<!---->
<!--<article class="container log-con scan-container">-->
<!--    <div class="panel goods-attr">-->
<!--        <div class="panel-heading">扫码支付</div>-->
<!--        <div class="panel-body" style="padding: 10px 0;">-->
<!--            <div class="qrcode">-->
<!--                <div id="qrcode"></div>-->
<!--            </div>-->
<!--            <div class="des">-->
<!--                <h1 class="goods-title" style="margin-top: 10px; margin-bottom: 5px; font-size: 16px;">请使用手机【--><?php //= $order_info['pay_name'] ?><!--】扫码支付</h1>-->
<!---->
<!--                <dl class="dl-inline" style="margin-bottom: 5px; margin-top: 15px;">-->
<!--                    <dt>订单编号：--><?php //= $order_info['out_trade_no'] ?><!--</dt>-->
<!--                </dl>-->
<!--                <dl class="dl-inline" style="margin-bottom: 5px; margin-top: 5px;">-->
<!--                    <dt>订单金额：--><?php //= $order_info['amount'] ?><!--</dt>-->
<!--                </dl>-->
<!--                <dl class="dl-inline" style="margin-bottom: 5px; margin-top: 5px;">-->
<!--                    <dt>创建时间：--><?php //= date('Y-m-d H:i:s', $order_info['create_time']) ?><!--</dt>-->
<!--                </dl>-->
<!--                <div data-loading="支付结果检测中" class="load-indicator loading" style="" id="check"></div>-->
<!---->
<!--                <a href="javascript: checkPay();" id="is_pay" style="color: #ff5f5f; font-weight: bold; margin-top: 20px; display: inline-block;">检测失败，点我重新发起检测</a>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!---->
<!---->
<!---->
<!---->
<!--    <div style="text-align: center; margin-top: 20px;">-->
<!--        <div>-->
<!--            <a href="javascript:;" id="is_pay" data-out_trade_no="--><?php //= $order_info['out_trade_no'] ?><!--" data-loading="n" style="color: #ff5f5f; font-weight: bold">支付完成后点我验证</a>-->
<!--        </div>-->
<!--    </div>-->
<!---->
<!--</article>-->


