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
        购买数量：<?= $child_order['quantity'] ?>；
        发货数量：<span id="kami-count"></span>；
        <div style="margin-top: 10px;">
            <span class="layui-btn" id="copy-all">复制全部卡密</span>
            <span class="layui-btn" id="download-all">下载全部卡密</span>
        </div>
    </blockquote>


    <!-- 卡密列表容器 -->
    <div class="card-list" id="cardList">
        <!-- 卡密项将通过JS动态生成 -->
    </div>
</main>





<script>
    layui.use(['layer'], function() {
        var layer = layui.layer;

        var kami_all = '';

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
            const textarea = document.createElement('textarea');
            textarea.value = kami_all;
            textarea.style.position = 'fixed';
            textarea.style.top = '50%';
            textarea.style.left = '50%';
            textarea.style.transform = 'translate(-50%, -50%)';
            textarea.style.width = '200px';
            textarea.style.height = '100px';
            textarea.style.opacity = '0';
            textarea.style.zIndex = '99999';
            document.body.appendChild(textarea);

            try {
                textarea.focus({ preventScroll: true });
                textarea.setSelectionRange(0, kami_all.length);
                setTimeout(() => {
                    const success = document.execCommand('copy') || navigator.clipboard.writeText(kami_all).then(() => true).catch(() => false);
                    success ? layer.msg('复制成功') : layer.msg('复制失败')
                }, 50);
            } catch (err) {
                console.log(err);
            } finally {
                setTimeout(() => document.body.removeChild(textarea), 300);
            }
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
                        kami_all += '\n' + item.content;
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
            <div class="card-index">${index + 1}</div>
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
                btn.addEventListener('click', async function() {
                    const secret = this.getAttribute('data-secret');
                    const originalText = this.innerHTML;
                    const originalStyle = {
                        bg: this.style.backgroundColor,
                        color: this.style.color,
                        border: this.style.borderColor
                    };

                    try {
                        // 执行复制（等待结果）
                        await copyToClipboard(secret);

                        // 复制成功：显示成功动画
                        this.innerHTML = '<i class="fa fa-check"></i> 已复制';
                        this.style.backgroundColor = '#f0f9eb';
                        this.style.color = '#52c41a';
                        this.style.borderColor = '#b7eb8f';
                    } catch (err) {
                        // 复制失败：显示失败提示 + 手动复制兜底
                        this.innerHTML = '<i class="fa fa-exclamation-circle"></i> 复制失败';
                        this.style.backgroundColor = '#fff1f0';
                        this.style.color = '#ff4d4f';
                        this.style.borderColor = '#ffccc7';

                        // 弹出手动复制框（依赖 layer 插件）
                        setTimeout(() => {
                            layer.open({
                                title: '请手动复制',
                                content: `<textarea style="width:100%;height:120px;padding:10px;border:1px solid #eee;border-radius:4px;" readonly>${secret}</textarea>`,
                                btn: '关闭',
                                success: function(layero) {
                                    // 自动选中内容，方便用户复制
                                    layero.find('textarea')[0].select();
                                }
                            });
                        }, 500);
                    }

                    // 恢复按钮原始状态
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.style.backgroundColor = originalStyle.bg;
                        this.style.color = originalStyle.color;
                        this.style.borderColor = originalStyle.border;
                    }, 2000);
                });
            });
        }

        // 复制到剪贴板
        function copyToClipboard(text) {
            return new Promise((resolve, reject) => {
                if (!text || !text.trim()) {
                    reject(new Error('无复制内容'));
                    return;
                }

                // 微信浏览器特殊处理：提前创建 textarea 并保留到复制完成
                const textarea = document.createElement('textarea');
                textarea.value = text;
                // 微信适配样式：fixed + 透明 + 高层级
                textarea.style.position = 'fixed';
                textarea.style.top = '50%'; // 放在视口内（但透明，不影响视觉）
                textarea.style.left = '50%';
                textarea.style.transform = 'translate(-50%, -50%)';
                textarea.style.width = '200px';
                textarea.style.height = '100px';
                textarea.style.opacity = '0'; // 透明隐藏
                textarea.style.zIndex = '99999';
                textarea.style.border = 'none';
                textarea.style.outline = 'none';
                textarea.style.background = 'transparent';
                document.body.appendChild(textarea);

                try {
                    // 强制聚焦 + 选中（移动端核心）
                    textarea.focus({ preventScroll: true }); // 阻止滚动
                    textarea.setSelectionRange(0, text.length);

                    // 兼容微信：延迟 50ms 执行复制（给内核反应时间）
                    setTimeout(() => {
                        const isSuccess = document.execCommand('copy');
                        if (isSuccess) {
                            resolve();
                        } else {
                            // 复制失败时，尝试二次选中（微信偶尔选中失效）
                            textarea.select();
                            const secondTry = document.execCommand('copy');
                            secondTry ? resolve() : reject(new Error('复制失败'));
                        }
                    }, 50);
                } catch (err) {
                    reject(err);
                } finally {
                    // 延迟 300ms 移除（确保微信复制完成）
                    setTimeout(() => {
                        document.body.removeChild(textarea);
                    }, 300);
                }
            });
        }


    });
</script>
<script>
    $('#menu-order').addClass('open');
    $('#menu-order > ul').css('display', 'block');
    $('#menu-order > a > i.nav_right').attr('class', 'fa fa-angle-down nav_right');
    $('#menu-order-visitors').addClass('menu-current');
</script>