<?php
defined('DC_ROOT') || exit('access denied!');
?>
<style>
    /* ============ 页面容器 ============ */
    .visitor-page {
        display: flex;
        flex-direction: column;
        gap: 18px;
        padding: 6px 0 18px;
    }

    /* ============ Hero 查询卡 ============ */
    .visitor-hero-card {
        padding: 24px 28px;
        border-radius: 10px;
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        box-shadow: 0 1px 18px #12345b0a;
    }

    .visitor-hero-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 28px;
    }
    .visitor-hero-left { flex: 1; min-width: 0; }

    .visitor-hero-eyebrow {
        font-size: 13px;
        color: #64748b;
        letter-spacing: 1px;
        margin-bottom: 10px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .visitor-hero-title {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 8px;
        line-height: 1.3;
    }
    .visitor-hero-desc {
        font-size: 14px;
        color: #64748b;
        margin: 0;
        line-height: 1.6;
    }

    /* 搜索框（白胶囊，右侧）*/
    .visitor-search-box {
        display: flex;
        align-items: center;
        gap: 6px;
        background: #fff;
        border-radius: 999px;
        padding: 6px;
        width: 650px;
        flex-shrink: 0;
        border: 1px solid var(--tp-light);
        box-shadow: 0 4px 12px rgba(var(--tp-rgb),.04);
    }
    .visitor-search-box .search-input {
        flex: 1;
        min-width: 0;
        height: 44px;
        border: 0;
        padding: 0 18px;
        font-size: 14px;
        color: #1f2937;
        background: transparent;
        outline: none;
    }
    .visitor-search-box .search-input::placeholder { color: #9ca3af; }
    .visitor-search-box .search-btn {
        height: 44px;
        padding: 0 22px;
        background: linear-gradient(135deg, var(--theme-primary), var(--tp-light));
        color: #fff;
        border: none;
        border-radius: 999px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: transform .15s, box-shadow .2s;
        white-space: nowrap;
    }
    .visitor-search-box .search-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 20px rgba(var(--tp-rgb), .32);
    }
    .visitor-search-box .search-btn i { font-size: 14px; }

    /* ============ 功能卡片网格（最近查询 + 查单提醒）============ */
    .visitor-card-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }
    .visitor-card {
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        border-radius: 10px;
        box-shadow: 0 1px 18px #12345b0a;
        padding: 22px 24px;
        display: flex;
        flex-direction: column;
    }
    .visitor-card-title {
        font-size: 16px;
        font-weight: 800;
        color: #1f2937;
        margin: 0 0 6px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .visitor-card-title i { color: var(--theme-primary); }
    .visitor-card-desc {
        margin: 0 0 14px;
        color: #6b7280;
        font-size: 13px;
        line-height: 1.8;
    }
    .visitor-card-body { flex: 1; }
    .visitor-card-footer {
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px dashed #eef0f5;
    }
    .visitor-card-link {
        color: var(--theme-primary);
        font-size: 13px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
    }
    .visitor-card-link:hover { text-decoration: underline; color: var(--tp-dark); }

    /* 最近查询胶囊 */
    .cache-list { display: flex; flex-wrap: wrap; gap: 8px; }
    .cache-empty { color: #9ca3af; font-size: 13px; }
    .cache-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: rgba(var(--tp-rgb),.06);
        border: 1px solid rgba(var(--tp-rgb),.18);
        border-radius: 20px;
        font-size: 13px;
        color: var(--tp-dark);
        cursor: pointer;
        transition: all 0.2s;
    }
    .cache-item:hover { background: rgba(var(--tp-rgb),.12); border-color: var(--theme-primary); }
    .cache-item .cache-text {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .cache-item .cache-del {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: rgba(0,0,0,0.06);
        color: #9ca3af;
        font-size: 11px;
        cursor: pointer;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    .cache-item .cache-del:hover { background: #ef4444; color: #fff; }

    /* 提醒卡内部要点 */
    .visitor-tip-list { margin: 0; padding: 0; list-style: none; }
    .visitor-tip-list li {
        display: flex; align-items: flex-start; gap: 8px;
        font-size: 13px; color: #4b5563; line-height: 1.8;
        padding: 4px 0;
    }
    .visitor-tip-list li i { color: var(--theme-primary); margin-top: 4px; flex-shrink: 0; }

    /* ============ 验证码弹窗 ============ */
    .captcha-popup { padding: 24px 22px 22px; text-align: center; }
    .captcha-title {
        font-size: 16px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 18px;
    }
    .captcha-box {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 12px;
    }
    .captcha-input {
        width: 120px;
        height: 42px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 0 14px;
        font-size: 16px;
        text-align: center;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
    }
    .captcha-input:focus {
        border-color: var(--theme-primary);
        box-shadow: 0 0 0 3px rgba(var(--tp-rgb), 0.1);
    }
    .captcha-img { height: 42px; border-radius: 8px; cursor: pointer; }
    .captcha-tip { font-size: 12px; color: #9ca3af; margin-bottom: 14px; }
    .captcha-btn {
        width: 100%;
        height: 44px;
        background: linear-gradient(135deg, var(--theme-primary), var(--tp-light));
        border: none;
        border-radius: 8px;
        color: #fff;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: box-shadow .2s;
    }
    .captcha-btn:hover { box-shadow: 0 10px 20px rgba(var(--tp-rgb), 0.28); }

    /* ============ 移动端适配 ============ */
    @media (max-width: 900px) {
        .visitor-card-grid { grid-template-columns: 1fr; }
        .visitor-hero-inner { flex-direction: column; align-items: stretch; gap: 16px; }
        .visitor-search-box { width: 100%; }
    }
    @media (max-width: 768px) {
        .visitor-hero-card { padding: 22px 20px; }
        .visitor-hero-title { font-size: 20px; }
        .visitor-hero-desc { font-size: 13px; }
        .visitor-search-box { border-radius: 12px; padding: 5px; }
        .visitor-search-box .search-input { height: 42px; padding: 0 14px; }
        .visitor-search-box .search-btn {
            height: 42px; padding: 0 14px; font-size: 13px;
            border-radius: 8px;
        }
        .visitor-card { padding: 18px; }
        .cache-item .cache-text { max-width: 140px; }
    }
</style>

<main class="visitor-page">
    <!-- Hero 查询卡（蓝色渐变 + 白胶囊搜索框） -->
    <section class="visitor-hero-card">
        <div class="visitor-hero-inner">
            <div class="visitor-hero-left">
                <h2 class="visitor-hero-title">游客轻松查单</h2>
                <p class="visitor-hero-desc">输入订单编号或下单时预留的联系方式即可查询</p>
            </div>
            <form id="form" autocomplete="off" onsubmit="return false;">
                <div class="visitor-search-box">
                    <input type="text" class="search-input" id="queryInput" name="out_trade_no" placeholder="请输入联系方式 / 订单编号">
                    <button type="button" class="search-btn" onclick="queryOrder()">
                        <i class="fa fa-search"></i> 查询
                    </button>
                </div>
                <input name="token" value="<?= LoginAuth::genToken() ?>" type="hidden">
            </form>
        </div>
    </section>

    <!-- 功能卡片网格 -->
    <div class="visitor-card-grid">
        <!-- 最近查询（独立白卡） -->
        <section class="visitor-card" id="cacheContacts">
            <h3 class="visitor-card-title"><i class="fa fa-history"></i> 最近查询</h3>
            <p class="visitor-card-desc">点击下方胶囊可快速填入近期查询过的联系方式</p>
            <div class="visitor-card-body">
                <div class="cache-list" id="cacheList"></div>
            </div>
        </section>

        <!-- 查单提醒（独立白卡） -->
        <section class="visitor-card">
            <h3 class="visitor-card-title"><i class="fa fa-exclamation-circle"></i> 查单提醒</h3>
            <p class="visitor-card-desc">为保障您的订单安全，请仔细阅读以下提醒事项</p>
            <div class="visitor-card-body">
                <ul class="visitor-tip-list">
                    <li><i class="fa fa-check-circle"></i><span>联系信息不要泄露给他人，避免私人订单泄漏</span></li>
                    <li><i class="fa fa-check-circle"></i><span>订单编号可在手机付款记录中查看</span></li>
                    <li><i class="fa fa-check-circle"></i><span>如遗忘订单信息，请联系客服找回</span></li>
                </ul>
            </div>
            <div class="visitor-card-footer">
                <a href="<?= DC_URL ?>?action=help" class="visitor-card-link">
                    <i class="fa fa-headphones"></i> 如遇售后问题，请联系客服处理
                </a>
            </div>
        </section>
    </div>
</main>

<script>
layui.use(['layer', 'jquery'], function(){
    var $ = layui.$;
    var layer = layui.layer;

    // 显示验证码弹窗
    window.queryOrder = function() {
        var keyword = $('#queryInput').val().trim();
        if (!keyword) {
            layer.msg('请输入联系方式或订单号', {icon: 0, time: 2000});
            $('#queryInput').focus();
            return;
        }

        layer.open({
            type: 1,
            title: false,
            closeBtn: 1,
            shadeClose: true,
            area: ['300px', 'auto'],
            skin: 'captcha-layer',
            content: '<div class="captcha-popup">' +
                '<div class="captcha-title">请输入验证码</div>' +
                '<div class="captcha-box">' +
                '<input type="text" class="captcha-input" id="captchaInput" maxlength="4" placeholder="验证码">' +
                '<img src="<?= DC_URL ?>user/captcha.php?t=' + Math.random() + '" class="captcha-img" id="captchaImg" alt="验证码">' +
                '</div>' +
                '<div class="captcha-tip">点击图片可刷新验证码</div>' +
                '<button type="button" class="captcha-btn" id="captchaSubmitBtn">确认查询</button>' +
                '</div>'
        });

        setTimeout(function() { $('#captchaInput').focus(); }, 100);
    };

    // 提交查询（校验验证码 → 查询订单 → 跳转）
    function submitQuery() {
        var keyword = $('#queryInput').val().trim();
        var captcha = $('#captchaInput').val().trim();
        if (!captcha) {
            layer.msg('请输入验证码', {icon: 0, time: 2000});
            return;
        }

        var loadIndex = layer.load(2);

        // 先验证验证码
        $.ajax({
            url: '<?= DC_URL ?>user/captcha.php?action=check',
            type: 'POST',
            data: { code: captcha },
            dataType: 'json',
            success: function(res) {
                if (res.code != 0) {
                    layer.close(loadIndex);
                    layer.msg(res.msg || '验证码错误', {icon: 2, time: 2500});
                    refreshCaptcha();
                    return;
                }

                // 验证码通过，调用后端接口查询订单数量
                $.ajax({
                    type: 'POST',
                    url: '<?= DC_URL ?>user/visitors.php?action=visitors_search_order_count&origin=local',
                    data: { out_trade_no: keyword, token: '<?= LoginAuth::genToken() ?>' },
                    dataType: 'json',
                    success: function(e) {
                        layer.close(loadIndex);
                        if (e.code == 200) {
                            if (e.data.order_count > 0) {
                                saveContactCache(keyword);
                                var searchJson = JSON.stringify(e.data._search);
                                var base64Search = btoa(unescape(encodeURIComponent(searchJson)));
                                layer.closeAll();
                                window.location.href = '<?= DC_URL ?>user/visitors.php?action=get_visitors_order&_search=' + base64Search;
                            } else {
                                layer.msg('没有查找到任何订单', {icon: 2, time: 2500});
                                refreshCaptcha();
                            }
                        } else {
                            layer.msg(e.msg || '查询失败', {icon: 2, time: 2500});
                            refreshCaptcha();
                        }
                    },
                    error: function() {
                        layer.close(loadIndex);
                        layer.msg('查询失败，请稍后重试', {icon: 2, time: 2500});
                        refreshCaptcha();
                    }
                });
            },
            error: function() {
                layer.close(loadIndex);
                layer.msg('验证失败，请重试', {icon: 2, time: 2500});
                refreshCaptcha();
            }
        });
    }

    function refreshCaptcha() {
        $('#captchaImg').attr('src', '<?= DC_URL ?>user/captcha.php?t=' + Math.random());
        $('#captchaInput').val('').focus();
    }

    // 事件绑定
    $(document).on('click', '#captchaImg', function() {
        this.src = '<?= DC_URL ?>user/captcha.php?t=' + Math.random();
    });
    $(document).on('click', '#captchaSubmitBtn', submitQuery);
    $(document).on('keypress', '#captchaInput', function(e) {
        if (e.which == 13) submitQuery();
    });
    $('#queryInput').on('keypress', function(e) {
        if (e.which == 13) queryOrder();
    });

    // ===== 浏览器缓存联系方式 =====
    var CACHE_KEY = 'dc_order_contacts';
    var CACHE_LIMIT = 6;

    function loadCacheContacts() {
        var cached = [];
        try { cached = JSON.parse(localStorage.getItem(CACHE_KEY) || '[]'); } catch(e) {}
        if (!cached.length) {
            $('#cacheList').html('<span class="cache-empty">暂无查询记录</span>');
            return;
        }
        var html = '';
        for (var i = 0; i < cached.length; i++) {
            html += '<span class="cache-item" data-index="' + i + '">' +
                    '<span class="cache-text">' + $('<span>').text(cached[i]).html() + '</span>' +
                    '<span class="cache-del" title="删除"><i class="fa fa-times"></i></span>' +
                    '</span>';
        }
        $('#cacheList').html(html);
        $('#cacheContacts').show();
    }

    function saveContactCache(val) {
        if (!val) return;
        var cached = [];
        try { cached = JSON.parse(localStorage.getItem(CACHE_KEY) || '[]'); } catch(e) {}
        cached = cached.filter(function(v){ return v !== val; });
        cached.unshift(val);
        if (cached.length > CACHE_LIMIT) cached = cached.slice(0, CACHE_LIMIT);
        try { localStorage.setItem(CACHE_KEY, JSON.stringify(cached)); } catch(e) {}
    }

    $(document).on('click', '.cache-item .cache-text', function() {
        var idx = $(this).closest('.cache-item').data('index');
        var cached = [];
        try { cached = JSON.parse(localStorage.getItem(CACHE_KEY) || '[]'); } catch(e) {}
        if (cached[idx] !== undefined) {
            $('#queryInput').val(cached[idx]);
            // 填入后自动触发查询，直接弹出验证码
            queryOrder();
        }
    });

    $(document).on('click', '.cache-item .cache-del', function(e) {
        e.stopPropagation();
        var $item = $(this).closest('.cache-item');
        var idx = $item.data('index');
        layer.confirm('确定要删除这条记录吗？', {
            btn: ['确定', '取消'],
            title: false,
            closeBtn: 0
        }, function(confirmIdx) {
            layer.close(confirmIdx);
            var cached = [];
            try { cached = JSON.parse(localStorage.getItem(CACHE_KEY) || '[]'); } catch(e) {}
            cached.splice(idx, 1);
            try { localStorage.setItem(CACHE_KEY, JSON.stringify(cached)); } catch(ex) {}
            loadCacheContacts();
        });
    });

    loadCacheContacts();
});
</script>

<?php include __DIR__ . '/_pc_page_footer.php'; ?>
<script>
    $('#menu-visitors').addClass('menu-current');
</script>
