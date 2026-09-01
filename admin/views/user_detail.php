<?php defined('DC_ROOT') || exit('access denied!'); ?>
<style>
    body{
        margin: 0;
        background: #f6f7f9;
        color: #1f2937;
        font-family: -apple-system, BlinkMacSystemFont, 'PingFang SC', 'Microsoft YaHei', sans-serif;
        overflow: hidden;
    }
    .ud-shell{
        height: 100vh;
        display: flex;
        flex-direction: column;
        background: #f6f7f9;
    }
    /* ===== 头部：极简身份条 ===== */
    .ud-header{
        flex-shrink: 0;
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 20px;
        background: #ffffff;
        border-bottom: 1px solid #e5e7eb;
    }
    .ud-header-avatar{
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        flex-shrink: 0;
    }
    .ud-header-main{
        flex: 1;
        min-width: 0;
    }
    .ud-header-name{
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .ud-header-nickname{
        font-size: 16px;
        font-weight: 600;
        color: #111827;
        line-height: 1.4;
    }
    .ud-header-uid{
        font-size: 12px;
        color: #9ca3af;
        font-weight: 400;
    }
    .ud-header-sub{
        margin-top: 2px;
        font-size: 12px;
        color: #6b7280;
        line-height: 1.5;
    }
    .ud-header-tags{
        display: flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
    }
    .ud-tag{
        display: inline-flex;
        align-items: center;
        height: 22px;
        padding: 0 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        border: 1px solid transparent;
        box-sizing: border-box;
        line-height: 1;
    }
    .ud-tag.is-normal{ background: #ecfdf5; border-color: #a7f3d0; color: #059669; }
    .ud-tag.is-disabled{ background: #fef2f2; border-color: #fecaca; color: #dc2626; }
    .ud-tag.is-role{ background: #eff6ff; border-color: #bfdbfe; color: #2563eb; }

    /* ===== 主内容：左右两栏 ===== */
    .ud-body{
        flex: 1;
        display: flex;
        min-height: 0;
        overflow: hidden;
    }
    .ud-side{
        width: 260px;
        flex-shrink: 0;
        padding: 16px;
        overflow-y: auto;
        border-right: 1px solid #e5e7eb;
        background: #ffffff;
        box-sizing: border-box;
    }
    .ud-main{
        flex: 1;
        overflow-y: auto;
        padding: 16px;
        box-sizing: border-box;
    }

    /* ===== 左栏：指标卡 ===== */
    .ud-metric-group{
        margin-bottom: 14px;
    }
    .ud-metric-group:last-child{ margin-bottom: 0; }
    .ud-metric-title{
        font-size: 12px;
        color: #9ca3af;
        font-weight: 500;
        margin-bottom: 8px;
        padding-left: 2px;
    }
    .ud-metric{
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 12px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        margin-bottom: 6px;
    }
    .ud-metric:last-child{ margin-bottom: 0; }
    .ud-metric-label{
        font-size: 13px;
        color: #6b7280;
    }
    .ud-metric-value{
        font-size: 14px;
        font-weight: 600;
        color: #111827;
    }
    .ud-metric-value.is-money{ color: #16baaa; }
    .ud-metric-value.is-credits{ color: #ff7a45; }

    /* ===== 右栏：档案分组 ===== */
    .ud-card{
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        margin-bottom: 12px;
        overflow: hidden;
    }
    .ud-card:last-child{ margin-bottom: 0; }
    .ud-card-head{
        display: flex;
        align-items: center;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        background: #fafafa;
        border-bottom: 1px solid #e5e7eb;
    }
    .ud-card-head::before{
        content: '';
        display: inline-block;
        width: 3px;
        height: 12px;
        background: #3b82f6;
        border-radius: 2px;
        margin-right: 8px;
    }
    .ud-card-body{
        padding: 4px 14px;
    }
    .ud-row{
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 10px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .ud-row:last-child{ border-bottom: none; }
    .ud-row-label{
        min-width: 80px;
        flex-shrink: 0;
        color: #6b7280;
        font-size: 13px;
        line-height: 1.6;
    }
    .ud-row-value{
        flex: 1;
        min-width: 0;
        color: #111827;
        font-size: 13px;
        font-weight: 500;
        line-height: 1.6;
        text-align: right;
        word-break: break-all;
    }
    .ud-row-value.is-muted{ color: #9ca3af; font-weight: 400; }

    /* ===== 收款码预览 ===== */
    .ud-receipt{
        padding: 12px 0 8px;
        text-align: center;
    }
    .ud-receipt-img{
        width: 120px;
        height: 120px;
        border-radius: 6px;
        object-fit: cover;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        cursor: zoom-in;
    }
    .ud-receipt-empty{
        padding: 16px;
        border: 1px dashed #e5e7eb;
        border-radius: 6px;
        background: #fafafa;
        color: #9ca3af;
        font-size: 12px;
        text-align: center;
    }

    /* ===== 备注 ===== */
    .ud-note{
        padding: 10px 0;
        color: #374151;
        font-size: 13px;
        line-height: 1.7;
        white-space: pre-wrap;
        word-break: break-word;
    }
    .ud-note.is-empty{ color: #9ca3af; }

    /* ===== 底部 ===== */
    .ud-footer{
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        padding: 10px 20px;
        background: #ffffff;
        border-top: 1px solid #e5e7eb;
    }
    .ud-footer .layui-btn{
        min-width: 96px;
    }

    /* ===== 响应式 ===== */
    @media screen and (max-width: 900px){
        .ud-body{ flex-direction: column; overflow-y: auto; }
        .ud-side{
            width: 100%;
            border-right: none;
            border-bottom: 1px solid #e5e7eb;
            overflow: visible;
        }
        .ud-main{ overflow: visible; }
        .ud-header{ flex-wrap: wrap; }
    }
    @media screen and (max-width: 560px){
        .ud-row{ flex-direction: column; gap: 2px; }
        .ud-row-value{ text-align: left; }
    }
</style>

<div class="ud-shell">
    <!-- 头部：极简身份条 -->
    <div class="ud-header">
        <img src="<?= $user_detail['avatar_url'] ?>" alt="头像" class="ud-header-avatar" onerror="this.onerror=null;this.src='<?= $user_detail['default_avatar_url'] ?>';">
        <div class="ud-header-main">
            <div class="ud-header-name">
                <span class="ud-header-nickname"><?= $user_detail['nickname'] ?></span>
                <span class="ud-header-uid">UID <?= $user_detail['uid'] ?></span>
            </div>
            <div class="ud-header-sub">登录账号：<?= $user_detail['username'] ?></div>
        </div>
        <div class="ud-header-tags">
            <span class="ud-tag is-role"><?= $user_detail['role_name'] ?></span>
            <span class="ud-tag <?= $user_detail['state_badge_class'] ?>"><?= $user_detail['state_text'] ?></span>
        </div>
    </div>

    <!-- 主内容：左右两栏 -->
    <div class="ud-body">
        <!-- 左栏：关键指标 -->
        <div class="ud-side">
            <div class="ud-metric-group">
                <div class="ud-metric-title">资金</div>
                <div class="ud-metric">
                    <span class="ud-metric-label">账户余额</span>
                    <span class="ud-metric-value is-money">¥ <?= $user_detail['money'] ?></span>
                </div>
                <div class="ud-metric">
                    <span class="ud-metric-label">账户积分</span>
                    <span class="ud-metric-value is-credits"><?= $user_detail['credits'] ?></span>
                </div>
                <div class="ud-metric">
                    <span class="ud-metric-label">累计消费</span>
                    <span class="ud-metric-value">¥ <?= $user_detail['expend'] ?></span>
                </div>
            </div>

            <div class="ud-metric-group">
                <div class="ud-metric-title">订单</div>
                <div class="ud-metric">
                    <span class="ud-metric-label">订单总数</span>
                    <span class="ud-metric-value"><?= $user_detail['order_total'] ?></span>
                </div>
                <div class="ud-metric">
                    <span class="ud-metric-label">已支付</span>
                    <span class="ud-metric-value"><?= $user_detail['paid_order_total'] ?></span>
                </div>
            </div>

            <div class="ud-metric-group">
                <div class="ud-metric-title">提现</div>
                <div class="ud-metric">
                    <span class="ud-metric-label">申请总数</span>
                    <span class="ud-metric-value"><?= $user_detail['withdraw_total'] ?></span>
                </div>
                <div class="ud-metric">
                    <span class="ud-metric-label">待处理</span>
                    <span class="ud-metric-value"><?= $user_detail['pending_withdraw_total'] ?></span>
                </div>
            </div>
        </div>

        <!-- 右栏：档案分组 -->
        <div class="ud-main">
            <div class="ud-card">
                <div class="ud-card-head">账户资料</div>
                <div class="ud-card-body">
                    <div class="ud-row">
                        <div class="ud-row-label">账号类型</div>
                        <div class="ud-row-value"><?= $user_detail['role_name'] ?></div>
                    </div>
                    <div class="ud-row">
                        <div class="ud-row-label">账号状态</div>
                        <div class="ud-row-value"><?= $user_detail['state_text'] ?></div>
                    </div>
                    <div class="ud-row">
                        <div class="ud-row-label">会员等级</div>
                        <div class="ud-row-value"><?= $user_detail['level_name'] ?></div>
                    </div>
                    <div class="ud-row">
                        <div class="ud-row-label">等级到期</div>
                        <div class="ud-row-value"><?= $user_detail['level_expire_time'] ?></div>
                    </div>
                    <div class="ud-row">
                        <div class="ud-row-label">邀请码</div>
                        <div class="ud-row-value"><?= $user_detail['invite_code'] ?></div>
                    </div>
                    <div class="ud-row">
                        <div class="ud-row-label">上级信息</div>
                        <div class="ud-row-value"><?= $user_detail['superior_text'] ?></div>
                    </div>
                    <div class="ud-row">
                        <div class="ud-row-label">所属站点</div>
                        <div class="ud-row-value"><?= $user_detail['station_id'] > 0 ? '分店ID ' . $user_detail['station_id'] : '主站用户' ?></div>
                    </div>
                    <div class="ud-row">
                        <div class="ud-row-label">内容审核</div>
                        <div class="ud-row-value"><?= $user_detail['ischeck_text'] ?></div>
                    </div>
                </div>
            </div>

            <div class="ud-card">
                <div class="ud-card-head">联系与登录</div>
                <div class="ud-card-body">
                    <div class="ud-row">
                        <div class="ud-row-label">手机号码</div>
                        <div class="ud-row-value"><?= $user_detail['tel'] ?></div>
                    </div>
                    <div class="ud-row">
                        <div class="ud-row-label">邮箱地址</div>
                        <div class="ud-row-value"><?= $user_detail['email'] ?></div>
                    </div>
                    <div class="ud-row">
                        <div class="ud-row-label">微信号</div>
                        <div class="ud-row-value"><?= $user_detail['wechat'] ?></div>
                    </div>
                    <div class="ud-row">
                        <div class="ud-row-label">当前 IP</div>
                        <div class="ud-row-value"><?= $user_detail['current_ip'] ?></div>
                    </div>
                    <div class="ud-row">
                        <div class="ud-row-label">注册 IP</div>
                        <div class="ud-row-value"><?= $user_detail['reg_ip'] ?></div>
                    </div>
                </div>
            </div>

            <div class="ud-card">
                <div class="ud-card-head">默认提现资料</div>
                <div class="ud-card-body">
                    <div class="ud-row">
                        <div class="ud-row-label">提现方式</div>
                        <div class="ud-row-value"><?= $user_detail['withdraw_method_text'] ?></div>
                    </div>
                    <div class="ud-row">
                        <div class="ud-row-label">收款姓名</div>
                        <div class="ud-row-value"><?= $user_detail['withdraw_realname'] ?></div>
                    </div>
                    <div class="ud-row">
                        <div class="ud-row-label">收款账号</div>
                        <div class="ud-row-value"><?= $user_detail['withdraw_account'] ?></div>
                    </div>
                    <div class="ud-row">
                        <div class="ud-row-label">默认收款码</div>
                        <div class="ud-row-value">
                            <?php if ($user_detail['withdraw_receipt_url'] !== ''): ?>
                                <a href="<?= $user_detail['withdraw_receipt_url'] ?>" target="_blank">
                                    <img src="<?= $user_detail['withdraw_receipt_url'] ?>" alt="收款码" class="ud-receipt-img">
                                </a>
                            <?php else: ?>
                                <span class="is-muted">未上传</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ud-card">
                <div class="ud-card-head">时间信息</div>
                <div class="ud-card-body">
                    <div class="ud-row">
                        <div class="ud-row-label">注册时间</div>
                        <div class="ud-row-value"><?= $user_detail['create_time'] ?></div>
                    </div>
                    <div class="ud-row">
                        <div class="ud-row-label">最后更新</div>
                        <div class="ud-row-value"><?= $user_detail['update_time'] ?></div>
                    </div>
                    <div class="ud-row">
                        <div class="ud-row-label">最近日志</div>
                        <div class="ud-row-value"><?= $user_detail['last_log_time'] ?></div>
                    </div>
                </div>
            </div>

            <div class="ud-card">
                <div class="ud-card-head">备注说明</div>
                <div class="ud-card-body">
                    <div class="ud-note<?= trim($user_detail['description']) === '暂无备注说明' ? ' is-empty' : '' ?>"><?= $user_detail['description'] ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 底部 -->
    <div class="ud-footer">
        <button type="button" class="layui-btn layui-btn-primary" id="ud-close-btn">关闭</button>
    </div>
</div>

<script>
    layui.use(['layer'], function(){
        var $ = layui.$;
        var layer = layui.layer;
        $('#ud-close-btn').on('click', function(){
            var frameIndex = parent.layer.getFrameIndex(window.name);
            parent.layer.close(frameIndex);
        });
        // 收款码点击放大预览
        $(document).on('click', '.ud-receipt-img', function(e){
            e.preventDefault();
            var src = $(this).attr('src');
            if(!src) return;
            layer.open({
                type: 1,
                title: false,
                closeBtn: 1,
                area: ['auto', 'auto'],
                shadeClose: true,
                skin: 'layui-layer-nobg',
                content: '<img src="' + src + '" style="max-width:80vw;max-height:80vh;display:block;border-radius:6px;background:#fff;">'
            });
        });
    });
</script>
