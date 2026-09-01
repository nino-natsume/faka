<?php
defined('DC_ROOT') || exit('access denied!');
?>
<style>
    .card-list {
        margin-top: 15px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .card-item {
        display: flex;
        align-items: center;
        padding: 18px 20px;
        background-color: #fff;
        border-bottom: 1px solid #f0f2f5;
        transition: all 0.2s ease;
    }

    .card-item:last-child {
        border-bottom: none;
    }

    .card-item:hover {
        background-color: #fcfcff;
        transform: translateX(3px);
        box-shadow: 0 2px 8px rgba(22, 119, 255, 0.08);
    }

    .card-index {
        width: 36px;
        height: 36px;
        line-height: 36px;
        text-align: center;
        background: linear-gradient(135deg, #1677ff, #0f62d8);
        color: #fff;
        border-radius: 6px;
        margin-right: 20px;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(22, 119, 255, 0.15);
        user-select: none;
    }

    .card-secret {
        flex: 1;
        font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace;
        letter-spacing: 0.5px;
        padding: 12px 15px;
        background-color: #f5f7fa;
        border-radius: 6px;
        word-break: break-all;
        color: #333;
        border: 1px solid #e5e6eb;
        transition: all 0.2s;
    }

    .card-item:hover .card-secret {
        border-color: #d0e0ff;
        background-color: #f0f5ff;
    }

    .card-action {
        margin-left: 20px;
        white-space: nowrap;
    }

    .copy-btn {
        transition: all 0.2s !important;
    }

    .copy-btn:hover {
        background-color: #e6f7ff !important;
        color: #1890ff !important;
        border-color: #91d5ff !important;
    }

    .copy-btn:active {
        background-color: #bae7ff !important;
    }

    .empty-state {
        text-align: center;
        padding: 80px 0;
        color: #8c8c8c;
        background-color: #fff;
        border-radius: 8px;
    }

    .empty-state i {
        font-size: 60px;
        margin-bottom: 20px;
        color: #e5e6eb;
    }

    .empty-state p {
        font-size: 16px;
        margin: 0;
    }

    @media (max-width: 768px) {
        .card-item {
            flex-wrap: wrap;
            padding: 15px;
        }

        .card-index {
            margin-bottom: 10px;
        }

        .card-action {
            margin-left: 0;
            margin-top: 12px;
            width: 100%;
            text-align: right;
        }
    }
</style>

<main class="main-content">
    <?php if(!empty($goods['pay_content'])): ?>
        <div class="layui-panel">
            <div style="padding: 15px;"><?= $goods['pay_content'] ?></div>
        </div>
    <?php endif; ?>


    <blockquote class="layui-elem-quote layui-quote-nm" style="word-wrap: break-word; white-space: normal; overflow-wrap: break-word;">
        订单号：<?= $order['out_trade_no'] ?>；
    </blockquote>


    <!-- 卡密列表容器 -->
    <div class="card-list" id="cardList">
        <!-- 卡密项将通过JS动态生成 -->
    </div>
</main>





<script>
    layui.use(['layer'], function() {
        var layer = layui.layer;

        $('#download-all').click(function(){
            $.ajax({
                type: "POST",
                url: "<?= DC_URL ?>/user/order.php?action=get_order_serect",
                data: { out_trade_no : '<?= $order['out_trade_no'] ?>' },
                dataType: "json",
                success: function (e) {
                    if(e.code == 200){
                        // 1. 提取并拼接content（同复制逻辑）
                        const contentList = e.data.list.map(item => item.content);
                        const allContent = contentList.join('\n'); // 换行分隔

                        // 2. 创建Blob对象（表示TXT文件内容）
                        const blob = new Blob([allContent], { type: 'text/plain;charset=utf-8' });

                        // 3. 生成下载链接
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        // 设置文件名（可自定义，例如包含订单号）
                        a.download = `订单${<?= $order['out_trade_no'] ?>}_卡密.txt`;

                        // 4. 触发下载并清理
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        URL.revokeObjectURL(url); // 释放内存
                    } else {
                        layer.msg('数据获取失败，无法下载');
                    }
                },
                error: function (xhr) {
                    layer.msg('下载出错啦~');
                }
            });
        });


        $('#copy-all').click(function(){
            $.ajax({
                type: "POST",
                url: "<?= DC_URL ?>/user/order.php?action=get_order_serect",
                data: { out_trade_no : '<?= $order['out_trade_no'] ?>' },
                dataType: "json",
                success: function (e) {
                    if(e.code == 200){
                        const contentList = e.data.list.map(item => item.content);
                        const allContent = contentList.join('\n'); // 例如拼接为 "4\n5"
                        copyToClipboard(allContent, function(success) {
                            if (success) {
                                layer.msg('复制成功');
                            } else {
                                layer.msg('复制失败，您的浏览器暂不支持该操作');
                            }
                        });
                    }else{

                    }

                },
                error: function (xhr) {
                    layer.msg('出错啦~');
                }
            });
        })

        // 卡密数据
        var cardData = [];

        $.ajax({
            type: "POST",
            url: "<?= DC_URL ?>/user/order.php?action=get_order_serect",
            data: { out_trade_no : '<?= $order['out_trade_no'] ?>', limit: 500 },
            dataType: "json",
            success: function (e) {
                if(e.code == 200){
                    $('#kami-count').html(e.data.count)
                    e.data.list.forEach(item => {
                        cardData.push({ secret: item.content });
                    });
                    // 初始渲染
                    renderCardList(cardData);
                }else{

                }

            },
            error: function (xhr) {
                layer.msg('出错啦~');
            }
        });



        // 渲染卡密列表
        function renderCardList(cards) {
            var container = document.getElementById('cardList');
            container.innerHTML = '';

            if (cards.length === 0) {
                container.innerHTML = `
            <div class="empty-state">
              <i class="fa fa-inbox"></i>
              <p>没有卡密数据</p>
            </div>
          `;
                return;
            }

            cards.forEach((card, index) => {
                var cardItem = document.createElement('div');
                cardItem.className = 'card-item';
                // 序号从1开始
                cardItem.innerHTML = `
            <div class="card-secret">${card.secret}</div>
            <div class="card-action">
              <button class="layui-btn layui-btn-primary layui-btn-sm copy-btn" data-secret="${card.secret}">
                <i class="fa fa-copy"></i> 复制卡密
              </button>
            </div>
          `;
                container.appendChild(cardItem);
            });

            // 绑定复制卡密事件
            document.querySelectorAll('.copy-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    var secret = this.getAttribute('data-secret');
                    copyToClipboard(secret);
                    // 复制成功动画反馈
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fa fa-check"></i> 已复制';
                    this.style.backgroundColor = '#f0f9eb';
                    this.style.color = '#52c41a';
                    this.style.borderColor = '#b7eb8f';

                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.style.backgroundColor = '';
                        this.style.color = '';
                        this.style.borderColor = '';
                    }, 1500);
                });
            });
        }

        // 复制到剪贴板
        function copyToClipboard(text) {
            // 现代浏览器剪贴板API
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(text);
            } else {
                // 兼容旧浏览器
                var textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.style.position = 'absolute';
                textarea.style.left = '-9999px';
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
            }
        }


    });
</script>
<script>
    $('#menu-order').addClass('open');
    $('#menu-order > ul').css('display', 'block');
    $('#menu-order > a > i.nav_right').attr('class', 'fa fa-angle-down nav_right');
    $('#menu-order-visitors').addClass('menu-current');
</script>