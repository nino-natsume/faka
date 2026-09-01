<?php
defined('DC_ROOT') || exit('access denied!');
global $userData;
$_myWechat = trim((string)($myWechat ?? ($userData['wechat'] ?? '')));
$_hasWechat = $_myWechat !== '';
$_superiorWechat = !empty($mySuperior) ? trim((string)($mySuperior['wechat'] ?? '')) : '';
?>
<style>
    .fans-page {
        display: flex;
        flex-direction: column;
        gap: 22px;
        padding: 8px 0 18px;
    }

    .fans-hero {
        padding: 24px 28px;
        border-radius: 10px;
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        box-shadow: 0 1px 18px #12345b0a;
    }

    .fans-hero-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .fans-hero-left { flex: 1; min-width: 0; }

    .fans-hero-title {
        font-size: 22px;
        font-weight: 800;
        margin-bottom: 4px;
        color: #0f172a;
    }

    .fans-hero-desc {
        font-size: 13px;
        color: #64748b;
    }

    .fans-hero-invite-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 18px;
        background: linear-gradient(135deg, var(--theme-primary), var(--tp-dark, var(--theme-primary)));
        color: #fff;
        border: 1px solid var(--theme-primary);
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all .2s;
        text-decoration: none;
        flex-shrink: 0;
    }
    .fans-hero-invite-btn:hover { background: var(--tp-dark, var(--theme-primary)); color: #fff; box-shadow: 0 4px 14px rgba(var(--tp-rgb),.25); text-decoration: none; }

    .fans-hero-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 12px;
    }
    .fans-hero-stat {
        display: block;
        background: #f1f5f9;
        border-radius: 10px;
        padding: 14px 12px;
        text-align: center;
        color: inherit;
        cursor: pointer;
        transition: all .25s;
        text-decoration: none;
    }
    .fans-hero-stat:hover { background: #e2e8f0; color: inherit; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(15,23,42,.06); text-decoration: none; }
    .fans-hero-stat-num {
        font-size: 22px;
        font-weight: 700;
        line-height: 1.2;
        color: #0f172a;
        display: flex;
        align-items: baseline;
        justify-content: center;
        gap: 5px;
    }
    .fans-hero-stat-num .fans-view-tag {
        font-size: 10px;
        font-weight: 400;
        color: #94a3b8;
        transition: color .2s;
    }
    .fans-hero-stat:hover .fans-view-tag { color: var(--theme-primary); }
    .fans-hero-stat-label { font-size: 11px; color: #94a3b8; margin-top: 2px; }

    .fans-section {
        padding: 24px 28px;
        border-radius: 10px;
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        box-shadow: 0 1px 18px #12345b0a;
    }
    .fans-section-title {
        font-size: 15px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .fans-section-title i { color: var(--theme-primary); font-size: 16px; }

    .fans-stats-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    .fans-stat-card {
        background: rgba(var(--tp-rgb),.03);
        border-radius: 10px;
        padding: 18px;
        border: 1px solid rgba(var(--tp-rgb),.08);
    }
    .fans-stat-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
    }
    .fans-stat-card-title { font-size: 14px; font-weight: 600; color: #1a1a1a; }
    .fans-stat-card-badge {
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 10px;
        background: rgba(var(--tp-rgb),.08);
        color: var(--theme-primary);
        font-weight: 500;
    }
    .fans-stat-card-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }
    .fans-stat-item {
        display: block;
        text-align: center;
        color: inherit;
        cursor: pointer;
        padding: 10px 4px;
        border-radius: 10px;
        transition: all .25s;
        background: rgba(255,255,255,.6);
        text-decoration: none;
    }
    .fans-stat-item:hover { background: #fff; color: inherit; box-shadow: 0 4px 12px rgba(var(--tp-rgb),.1); transform: translateY(-2px); text-decoration: none; }
    .fans-stat-item-num { font-size: 22px; font-weight: 700; color: #1a1a1a; line-height: 1.3; }
    .fans-stat-item-label { font-size: 12px; color: #86909c; margin-top: 2px; }
    .fans-stat-item-arrow { font-size: 10px; color: rgba(var(--tp-rgb),.45); margin-top: 4px; }
    .fans-stat-item:hover .fans-stat-item-arrow { color: var(--theme-primary); }

    .fans-referrer-card {
        background: rgba(var(--tp-rgb),.03);
        border-radius: 10px;
        padding: 18px 20px;
        border: 1px solid rgba(var(--tp-rgb),.08);
    }
    .fans-referrer-info {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .fans-referrer-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #e5e7eb;
        object-fit: cover;
        flex-shrink: 0;
        border: 2px solid #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
    }
    .fans-referrer-meta { flex: 1; min-width: 0; }
    .fans-referrer-name { font-size: 15px; font-weight: 600; color: #1a1a1a; }
    .fans-referrer-detail { font-size: 12px; color: #86909c; margin-top: 3px; }
    .fans-referrer-badge {
        font-size: 11px;
        padding: 3px 10px;
        border-radius: 10px;
        background: rgba(5,150,105,.08);
        color: #059669;
        font-weight: 500;
    }

    .fans-bind-form {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .fans-bind-input {
        flex: 1;
        height: 42px;
        border: 1px solid #e5e6eb;
        border-radius: 8px;
        padding: 0 14px;
        font-size: 14px;
        background: #fff;
        color: #1a1a1a;
        outline: none;
        transition: border-color .2s;
    }
    .fans-bind-input:focus { border-color: var(--theme-primary); }
    .fans-bind-btn {
        height: 42px;
        padding: 0 20px;
        background: var(--theme-primary);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all .2s;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .fans-bind-btn:hover { background: var(--tp-dark); box-shadow: 0 4px 12px rgba(var(--tp-rgb),.3); }
    .fans-bind-btn:active { transform: scale(.97); }
    .fans-bind-hint {
        font-size: 12px;
        color: #86909c;
        margin-top: 10px;
    }

    .fans-wechat-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 18px 20px;
        border-radius: 10px;
        background: rgba(7,193,96,.05);
        border: 1px solid rgba(7,193,96,.13);
    }
    .fans-wechat-main {
        flex: 1;
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .fans-wechat-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #07c160;
        color: #fff;
        font-size: 20px;
        flex: 0 0 auto;
    }
    .fans-wechat-title { color:#1a1a1a;font-size:14px;font-weight:700; }
    .fans-wechat-desc { margin-top:3px;color:#86909c;font-size:12px;line-height:1.6; }
    .fans-wechat-value { color:#07a452;font-weight:700;word-break:break-all; }
    .fans-wechat-value.is-empty { color:#94a3b8;font-weight:500; }
    .fans-wechat-actions {
        display:flex;
        align-items:center;
        gap:8px;
        flex-shrink:0;
    }
    .fans-wechat-btn,
    .fans-referrer-contact {
        height:34px;
        padding:0 14px;
        border:0;
        border-radius:8px;
        background:#07c160;
        color:#fff;
        font-size:12px;
        font-weight:600;
        cursor:pointer;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:5px;
        transition:all .2s;
    }
    .fans-wechat-btn:hover,
    .fans-referrer-contact:hover { background:#06ad56;color:#fff;box-shadow:0 4px 12px rgba(7,193,96,.22); }
    .fans-wechat-btn.is-plain {
        background:#fff;
        color:#07a452;
        border:1px solid rgba(7,193,96,.25);
    }
    .fans-wechat-btn.is-plain:hover { background:#f0fdf4;box-shadow:none; }
    .fans-referrer-wechat {
        margin-top:6px;
        color:#64748b;
        font-size:12px;
    }
    .fans-referrer-wechat b { color:#07a452;font-weight:700; }
    .fans-referrer-contact.is-disabled {
        background:#f3f4f6;
        color:#9ca3af;
        cursor:default;
        box-shadow:none;
    }

    @media (max-width: 768px) {
        .fans-page { gap: 14px; padding: 0; }
        .fans-hero { border-radius: 0; padding: 18px 16px; }
        .fans-hero-grid { grid-template-columns: repeat(2, 1fr); }
        .fans-hero-stat-num { font-size: 18px; }
        .fans-section { border-radius: 0; padding: 18px 16px; }
        .fans-stats-row { grid-template-columns: 1fr; }
        .fans-stat-card-grid { gap: 6px; }
        .fans-stat-item-num { font-size: 18px; }
        .fans-bind-form { flex-direction: column; }
        .fans-bind-input { width: 100%; }
        .fans-bind-btn { width: 100%; justify-content: center; }
        .fans-wechat-card { flex-direction:column;align-items:flex-start; }
        .fans-wechat-actions { width:100%; }
        .fans-wechat-btn { flex:1; }
    }
</style>

<main class="fans-page">
    <div class="fans-hero">
        <div class="fans-hero-head">
            <div class="fans-hero-left">
                <div class="fans-hero-title">我的粉丝</div>
                <div class="fans-hero-desc">邀请好友注册，拓展您的粉丝团队</div>
            </div>
            <a href="./account.php?action=invite" class="fans-hero-invite-btn"><i class="fa fa-share-alt"></i> 邀请好友</a>
        </div>
        <div class="fans-hero-grid">
            <a class="fans-hero-stat" href="./account.php?action=fans_detail&type=total">
                <div class="fans-hero-stat-num"><?= $totalFansCount ?><span class="fans-view-tag">查看 <i class="fa fa-chevron-right"></i></span></div>
                <div class="fans-hero-stat-label">总粉丝</div>
            </a>
            <a class="fans-hero-stat" href="./account.php?action=fans_detail&type=direct">
                <div class="fans-hero-stat-num"><?= $directFansCount ?><span class="fans-view-tag">查看 <i class="fa fa-chevron-right"></i></span></div>
                <div class="fans-hero-stat-label">直邀粉丝</div>
            </a>
            <a class="fans-hero-stat" href="./account.php?action=fans_detail&type=active">
                <div class="fans-hero-stat-num"><?= $activeFansCount ?><span class="fans-view-tag">查看 <i class="fa fa-chevron-right"></i></span></div>
                <div class="fans-hero-stat-label">有效粉丝</div>
            </a>
            <a class="fans-hero-stat" href="./account.php?action=fans_detail&type=potential">
                <div class="fans-hero-stat-num"><?= $potentialFansCount ?><span class="fans-view-tag">查看 <i class="fa fa-chevron-right"></i></span></div>
                <div class="fans-hero-stat-label">潜在粉丝</div>
            </a>
            <a class="fans-hero-stat" href="./account.php?action=fans_detail&type=referral">
                <div class="fans-hero-stat-num"><?= $referralFansCount ?><span class="fans-view-tag">查看 <i class="fa fa-chevron-right"></i></span></div>
                <div class="fans-hero-stat-label">推荐粉丝</div>
            </a>
        </div>
    </div>

    <section class="fans-section">
        <div class="fans-section-title"><i class="fa fa-weixin"></i> 我的微信号</div>
        <div class="fans-wechat-card">
            <div class="fans-wechat-main">
                <div class="fans-wechat-icon"><i class="fa fa-weixin"></i></div>
                <div>
                    <div class="fans-wechat-title">团队联系微信</div>
                    <div class="fans-wechat-desc">
                        当前：<span class="fans-wechat-value<?= $_hasWechat ? '' : ' is-empty' ?>" id="fansMyWechatValue"><?= $_hasWechat ? htmlspecialchars($_myWechat) : '未设置' ?></span>
                        <br>设置后会展示给您的上级成员和团队成员，方便沟通交流。
                    </div>
                </div>
            </div>
            <div class="fans-wechat-actions">
                <button type="button" class="fans-wechat-btn is-plain" id="fansCopyMyWechat" onclick="copyFansText(fansWechatValue)" style="<?= $_hasWechat ? '' : 'display:none;' ?>"><i class="fa fa-copy"></i> 复制</button>
                <button type="button" class="fans-wechat-btn" id="fansWechatEditBtn" onclick="openFansWechatSetting()"><i class="fa fa-pencil"></i> <span><?= $_hasWechat ? '修改微信号' : '设置微信号' ?></span></button>
            </div>
        </div>
    </section>

    <section class="fans-section">
        <div class="fans-section-title">今日数据</div>
        <div class="fans-stats-row">
            <div class="fans-stat-card">
                <div class="fans-stat-card-head">
                    <span class="fans-stat-card-title">直邀粉丝</span>
                    <span class="fans-stat-card-badge">共 <?= $directFansCount ?> 人</span>
                </div>
                <div class="fans-stat-card-grid">
                    <a class="fans-stat-item" href="./account.php?action=fans_detail&type=direct">
                        <div class="fans-stat-item-num"><?= $directTodayNew ?></div>
                        <div class="fans-stat-item-label">今日新增</div>
                        <div class="fans-stat-item-arrow"><i class="fa fa-list"></i></div>
                    </a>
                    <a class="fans-stat-item" href="./account.php?action=fans_detail&type=direct">
                        <div class="fans-stat-item-num"><?= $directTodayActive ?></div>
                        <div class="fans-stat-item-label">今日活跃</div>
                        <div class="fans-stat-item-arrow"><i class="fa fa-list"></i></div>
                    </a>
                    <a class="fans-stat-item" href="./account.php?action=fans_detail&type=direct">
                        <div class="fans-stat-item-num"><?= $directTodayOrders ?></div>
                        <div class="fans-stat-item-label">今日下单</div>
                        <div class="fans-stat-item-arrow"><i class="fa fa-list"></i></div>
                    </a>
                </div>
            </div>
            <div class="fans-stat-card">
                <div class="fans-stat-card-head">
                    <span class="fans-stat-card-title">推荐粉丝</span>
                    <span class="fans-stat-card-badge">共 <?= $referralFansCount ?> 人</span>
                </div>
                <div class="fans-stat-card-grid">
                    <a class="fans-stat-item" href="./account.php?action=fans_detail&type=referral">
                        <div class="fans-stat-item-num"><?= $referralTodayNew ?></div>
                        <div class="fans-stat-item-label">今日新增</div>
                        <div class="fans-stat-item-arrow"><i class="fa fa-list"></i></div>
                    </a>
                    <a class="fans-stat-item" href="./account.php?action=fans_detail&type=referral">
                        <div class="fans-stat-item-num"><?= $referralTodayActive ?></div>
                        <div class="fans-stat-item-label">今日活跃</div>
                        <div class="fans-stat-item-arrow"><i class="fa fa-list"></i></div>
                    </a>
                    <a class="fans-stat-item" href="./account.php?action=fans_detail&type=referral">
                        <div class="fans-stat-item-num"><?= $referralTodayOrders ?></div>
                        <div class="fans-stat-item-label">今日下单</div>
                        <div class="fans-stat-item-arrow"><i class="fa fa-list"></i></div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="fans-section">
        <div class="fans-section-title">我的推荐人</div>
        <?php if ($mySuperior): ?>
        <div class="fans-referrer-card">
            <div class="fans-referrer-info">
                <img class="fans-referrer-avatar" src="<?= htmlspecialchars(User::getAvatar($mySuperior['photo'] ?? '')) ?>" alt="">
                <div class="fans-referrer-meta">
                    <div class="fans-referrer-name">
                        <?= htmlspecialchars($mySuperior['nickname'] ?: $mySuperior['username']) ?>
                        <?php if (!empty($mySuperior['level_name'])): ?>
                        <span style="display:inline-block;font-size:11px;padding:1px 7px;border-radius:4px;background:rgba(var(--tp-rgb),.1);color:var(--theme-primary);font-weight:500;margin-left:6px;vertical-align:middle;"><?= htmlspecialchars($mySuperior['level_name']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($mySuperior['has_station'])): ?>
                        <span style="display:inline-block;font-size:11px;padding:1px 7px;border-radius:4px;background:rgba(5,150,105,.1);color:#059669;font-weight:500;margin-left:4px;vertical-align:middle;"><i class="fa fa-home" style="margin-right:2px;"></i><?= !empty($mySuperior['station_level_name']) ? htmlspecialchars($mySuperior['station_level_name']) : '分站' ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="fans-referrer-detail">UID: <?= $mySuperior['uid'] ?> · 加入时间: <?= date('Y-m-d', $mySuperior['create_time']) ?></div>
                    <div class="fans-referrer-wechat">微信号：
                        <?php if ($_superiorWechat !== ''): ?>
                            <b><?= htmlspecialchars($_superiorWechat) ?></b>
                        <?php else: ?>
                            <span style="color:#94a3b8;">推荐人暂未设置微信号</span>
                        <?php endif; ?>
                    </div>
                </div>
                <span class="fans-referrer-badge">已绑定</span>
                <?php if ($_superiorWechat !== ''): ?>
                <button type="button" class="fans-referrer-contact" onclick="copyFansText(fansSuperiorWechat)"><i class="fa fa-weixin"></i> 复制微信</button>
                <?php else: ?>
                <span class="fans-referrer-contact is-disabled"><i class="fa fa-weixin"></i> 未设置</span>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="fans-referrer-card">
            <p style="font-size:13px;color:#6b7280;margin:0 0 12px;">您还未绑定推荐人，输入邀请码即可绑定</p>
            <div class="fans-bind-form">
                <input type="text" class="fans-bind-input" id="bindInviteCode" placeholder="输入推荐人邀请码" maxlength="16">
                <button type="button" class="fans-bind-btn" onclick="bindSuperior()"><i class="fa fa-link"></i> 绑定</button>
            </div>
            <div class="fans-bind-hint">绑定后不可更改，请确认邀请码正确</div>
        </div>
        <?php endif; ?>
    </section>
</main>

<script>
    $('#menu-fans').addClass('menu-current');
    var fansWechatValue = <?= json_encode($_myWechat, JSON_UNESCAPED_UNICODE) ?>;
    var fansWechatToken = '<?= LoginAuth::genToken() ?>';
    var fansSuperiorWechat = <?= json_encode($_superiorWechat, JSON_UNESCAPED_UNICODE) ?>;

    function copyFansText(text) {
        text = String(text || '');
        if (!text) { layui.layer.msg('暂无可复制的微信号', {icon: 0}); return; }
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function(){ layui.layer.msg('已复制到剪贴板', {icon: 1}); });
        } else {
            var ta = document.createElement('textarea');
            ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
            document.body.appendChild(ta); ta.select(); document.execCommand('copy');
            document.body.removeChild(ta); layui.layer.msg('已复制到剪贴板', {icon: 1});
        }
    }

    function openFansWechatSetting() {
        var html = '<div style="padding:22px 24px 8px;">'
            + '<div style="display:flex;align-items:center;gap:8px;color:#1f2937;font-size:14px;font-weight:700;margin-bottom:10px;"><i class="fa fa-weixin" style="color:#07c160;"></i> 微信号</div>'
            + '<input id="fansWechatInput" type="text" maxlength="40" value="' + $('<div>').text(fansWechatValue || '').html() + '" placeholder="请输入微信号，可留空" style="width:100%;height:42px;padding:0 12px;border:1px solid #e5e7eb;border-radius:8px;outline:none;box-sizing:border-box;">'
            + '<div style="margin-top:10px;color:#94a3b8;font-size:12px;line-height:1.7;">支持 3-40 位字母、数字、下划线或中划线；留空可清空微信号。</div>'
            + '</div>';
        layui.layer.open({
            type: 1,
            title: '设置微信号',
            area: ['380px', 'auto'],
            shadeClose: true,
            content: html,
            btn: ['保存设置', '取消'],
            yes: function(idx, layero) {
                var val = $.trim(layero.find('#fansWechatInput').val() || '').replace(/\s+/g, '');
                if (val !== '' && !/^[A-Za-z0-9_\-]{3,40}$/.test(val)) {
                    return layui.layer.msg('微信号仅支持3-40位字母、数字、下划线或中划线', {icon: 0});
                }
                $.ajax({
                    url: './account.php?action=fans_save_wechat',
                    type: 'POST',
                    dataType: 'json',
                    data: {wechat: val, token: fansWechatToken},
                    success: function(res) {
                        if (res && res.code === 0) {
                            fansWechatValue = val;
                            $('#fansMyWechatValue').text(val || '未设置').toggleClass('is-empty', val === '');
                            $('#fansCopyMyWechat').toggle(!!val);
                            $('#fansWechatEditBtn span').text(val ? '修改微信号' : '设置微信号');
                            layui.layer.close(idx);
                            layui.layer.msg(res.data || '微信号已保存', {icon: 1});
                        } else {
                            layui.layer.msg((res && res.msg) || '保存失败', {icon: 2});
                        }
                    },
                    error: function(xhr) {
                        var res = xhr.responseJSON || {};
                        layui.layer.msg(res.msg || '保存失败，请稍后重试', {icon: 2});
                    }
                });
            }
        });
    }

    function bindSuperior() {
        var code = $('#bindInviteCode').val().trim();
        if (!code) { layui.layer.msg('请输入邀请码'); return; }
        layui.layer.confirm('确认绑定该邀请码为您的推荐人？绑定后不可更改。', { title: '绑定确认', btn: ['确认绑定', '取消'] }, function(idx) {
            layui.layer.close(idx);
            $.post('./account.php?action=fans_bind_superior', { invite_code: code }, function(res) {
                if (res.code === 0) {
                    layui.layer.msg(res.data || '已成功绑定推荐人', { icon: 1 });
                    setTimeout(function() { location.reload(); }, 1200);
                } else {
                    layui.layer.msg(res.msg || '绑定失败', { icon: 2 });
                }
            }, 'json');
        });
    }
</script>
<?php include __DIR__ . '/_pc_page_footer.php'; ?>
