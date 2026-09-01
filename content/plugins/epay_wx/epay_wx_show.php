<?php
defined('DC_ROOT') || exit('access denied!');

/*
 * 插件前台展示页面
 * 前台显示地址为：https://yourdomain/?plugin=alipay
 */

$out_trade_no = Input::getStrVar('out_trade_no');
$orderModel = new Order_Model();
$order_info = $orderModel->getOrderInfo($out_trade_no);
$qr_code = Input::getStrVar('qr_code');
?>
<!--some html code here-->
<!doctype html>
<html lang="zh-cn" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>支付宝扫码支付</title>
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>
    <link href="<?= empty(_g('favicon')) ? DC_URL . 'favicon.ico' : _g('favicon'); ?>" rel="icon">
    <link rel="alternate" title="RSS" href="<?= DC_URL ?>rss.php" type="application/rss+xml"/>

    <link rel="stylesheet" href="../../../admin/views/css/bootstrap.min.css">
    <script src="../../../admin/views/js/jquery.min.3.5.1.js"></script>
    <script src="../../../admin/views/js/bootstrap.bundle.min.4.6.js?t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>

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
            /*width: 40px;*/
            height: 50px;
            margin-bottom: 15px;
        }

        .title {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 20px;
            color: #1677ff;
        }

        .amount {
            font-size: 25px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #ff4d4f ;
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

        .order-details {
            text-align: left;
            width: 80%;
            margin: 0 auto 20px;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 6px;
        }
        .order-details p {
            margin-bottom: 8px;
        }
        .qrcode img{
            width: 100%;
        }
    </style>
</head>
<body>
<div class="payment-container">
    <img src="../content/plugins/alipay/zhifubao.jpeg" alt="支付宝" class="logo">
<!--    <h1 class="title">请使用手机【--><?php //= $order_info['pay_name'] ?><!--】扫码支付</h1>-->
    <div class="amount">¥ <?= $order_info['amount'] ?></div>

    <p class="order-info" style="font-size: 13px;">订单编号：<?= $order_info['out_trade_no'] ?></p>
    <p class="order-info" style="margin-left: -9px; font-size: 13px;">创建时间：<?= date("Y-m-d    H:i:s", $order_info['create_time']) ?></p>

    <div class="qrcode-container">
        <div class="qrcode" id="qrcode"></div>
    </div>

    <p class="instructions">请打开支付宝扫一扫<br>扫描二维码完成支付</p>

    <div class="footer">
        支付完成后，页面将自动刷新
    </div>
</div>


<script src="<?= TEMPLATE_URL ?>js/qrcode.min.js?t=<?= Option::DC_VERSION_TIMESTAMP ?>"></script>

<script>
    new QRCode(document.getElementById("qrcode"), "<?= $qr_code ?>", {
        width: 220,
        height: 220,
    });

    var out_trade_no = '<?= $order_info['out_trade_no'] ?>';

    function checkPay(){
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