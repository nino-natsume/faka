<?php
/**
 * 订单查询页面
 */
defined('DC_ROOT') || exit('access denied!');

// 获取模板配置
$footer_show = _g('footer_show') ?: 'y';
$single_page_mode = _g('single_page_mode') ?: 'n';
// 头部按钮显示开关
$header_menu_show = _g('header_menu_show') ?: 'y';
$header_search_show = _g('header_search_show') ?: 'y';
$header_user_show = _g('header_user_show') ?: 'y';
$header_order_show = _g('header_order_show') ?: 'n';

// 获取商城头部自定义样式
$_shop_header_bg = _g('shop_header_bg') ?: '';
$_shop_title_color = _g('shop_title_color') ?: '';
$_shop_subtitle_color = _g('shop_subtitle_color') ?: '';
$_shop_nav_active_color = _g('shop_nav_active_color') ?: '';
$_shop_nav_active_bg = _g('shop_nav_active_bg') ?: '';
$_has_shop_header_setting = !empty($_shop_header_bg);
?>

<style>
    <?php if($footer_show != 'y'): ?>
    .main-footer, .footer-nav.tel-footer {
        display: none !important;
    }
    <?php endif; ?>
    
    <?php if($single_page_mode == 'y'): ?>
    /* 单页购买模式隐藏底部导航 */
    .footer-nav {
        display: none !important;
    }
    <?php endif; ?>
    
    /* 移动端给底部导航留出空间 */
    @media (max-width: 1200px) {
        <?php if($single_page_mode != 'y'): ?>
        .order-body {
            padding-bottom: 70px;
        }
        <?php endif; ?>
    }
    
    <?php if($_has_shop_header_setting): ?>
    /* 使用后台设置的商城头部样式 */
    .h-fix {
        background: <?= htmlspecialchars($_shop_header_bg) ?> !important;
    }
    <?php if (!empty($_shop_title_color)): ?>
    .logo-brand .brand-title { color: <?= htmlspecialchars($_shop_title_color) ?> !important; }
    <?php else: ?>
    .logo-brand .brand-title { color: #fff !important; }
    <?php endif; ?>
    <?php if (!empty($_shop_subtitle_color)): ?>
    .logo-brand .brand-subtitle { color: <?= htmlspecialchars($_shop_subtitle_color) ?> !important; }
    <?php else: ?>
    .logo-brand .brand-subtitle { color: rgba(255,255,255,0.8) !important; }
    <?php endif; ?>
    .header .nav-bar li a { color: #fff !important; }
    .header .nav-bar li a:hover { background: rgba(255,255,255,0.15) !important; }
    <?php if (!empty($_shop_nav_active_color)): ?>
    .header .nav-bar li a.active { color: <?= htmlspecialchars($_shop_nav_active_color) ?> !important; }
    <?php endif; ?>
    <?php if (!empty($_shop_nav_active_bg)): ?>
    .header .nav-bar li a.active { background: <?= htmlspecialchars($_shop_nav_active_bg) ?> !important; border-radius: 4px; }
    <?php endif; ?>
    .search i.fa, .header-user i.fa, .m-btn i.fa { color: #fff !important; }
    .header-search-order-btn .a { background-color: rgba(255,255,255,0.2) !important; }
    .header-search-order-btn .a:hover { background-color: rgba(255,255,255,0.3) !important; }
    <?php else: ?>
    /* 默认蓝色主题（后台未设置时） */
    .h-fix {
        background: #0c6be1 !important;
    }
    .logo-text a span {
        color: #fff !important;
    }
    .logo-brand .brand-title {
        color: #fff !important;
    }
    .logo-brand .brand-subtitle {
        color: rgba(255,255,255,0.8) !important;
    }
    .header .nav-bar li a {
        color: #fff !important;
    }
    .header .nav-bar li a:hover {
        color: #fff !important;
        background: rgba(255,255,255,0.15) !important;
    }
    .header .nav-bar li.active > a {
        color: #fff !important;
        background: rgba(255,255,255,0.2) !important;
    }
    .search i.fa, .header-user i.fa, .m-btn i.fa {
        color: #fff !important;
    }
    .search i.fa:hover, .header-user i.fa:hover, .m-btn i.fa:hover {
        background: rgba(255,255,255,0.15) !important;
        color: #fff !important;
    }
    .header-search-order-btn .a {
        background-color: rgba(255,255,255,0.2) !important;
    }
    .header-search-order-btn .a:hover {
        background-color: rgba(255,255,255,0.3) !important;
    }
    <?php endif; ?>
    <?php if($header_menu_show != 'y'): ?>
    .m-btn { display: none !important; }
    <?php endif; ?>
    <?php if($header_search_show != 'y'): ?>
    .search { display: none !important; }
    <?php endif; ?>
    <?php if($header_user_show != 'y'): ?>
    .header-user { display: none !important; }
    <?php endif; ?>
    <?php if($header_order_show != 'y'): ?>
    .header-search-order-btn { display: none !important; }
    <?php endif; ?>
    
    /* 订单查询页面隐藏买家帮助按钮，显示返回帮助按钮 */
    .header-help-mobile { display: none !important; }
    .header-back-home {
        display: flex !important;
        align-items: center;
        height: 72px;
        margin-left: 10px;
    }
    .header-back-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 36px;
        border-radius: 4px;
        background-color: rgba(255,255,255,0.2);
        color: #fff;
        font-size: 14px;
        padding: 0 1rem;
        text-decoration: none;
        transition: all 0.3s ease;
        white-space: nowrap;
    }
    .header-back-btn:hover {
        background-color: rgba(255,255,255,0.3);
        text-decoration: none;
        color: #fff;
    }
    @media (max-width: 1200px) {
        .header-back-home {
            margin-left: 0;
            margin-right: 5px;
        }
    }
    
    /* logo和标题可点击跳转首页 */
    .logo-brand a {
        cursor: pointer;
    }
    
    /* 替换logo图片 */
    .logo-brand .brand-logo {
        content: url('<?= TEMPLATE_URL ?>img/chaxunxitong1.png');
    }
    
    /* 覆盖头部标题和副标题 */
    .logo-brand .brand-title {
        font-size: 17.5px !important;
    }
    .logo-brand .brand-subtitle {
        font-size: 12px !important;
    }

    /* 查询卡片 */
    .query-card {
        background: rgba(248, 248, 248, 0.5);
        border: 2px solid #fff;
        border-radius: 8px;
        padding: 0;
        margin-top: 20px;
        position: relative;
        z-index: 10;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    .query-card-bg {
        background: url('<?= TEMPLATE_URL ?>img/order_bg.001acbb8.jpeg') no-repeat center;
        background-size: cover;
        padding: 40px 25px;
        text-align: center;
    }
    .query-card-content {
        padding: 25px;
    }
    
    /* 主体内容 */
    .order-body {
        flex: 1 0 auto;
        padding: 0 15px;
        max-width: 900px;
        margin: 0 auto;
        padding-bottom: 30px;
        width: 100%;
        box-sizing: border-box;
    }
    
    .query-title {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        text-align: center;
        margin-bottom: 15px;
    }
    .query-subtitle {
        font-size: 14px;
        color: #999;
        text-align: center;
        margin-bottom: 25px;
    }
    
    /* 搜索框 */
    .search-box {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }
    .search-input {
        flex: 1;
        height: 46px;
        border: 1px solid #eee;
        border-radius: 8px;
        padding: 0 15px;
        font-size: 14px;
        color: #333;
        outline: none;
        transition: border-color 0.2s;
        background: #fff;
        -webkit-appearance: none;
        appearance: none;
    }
    .search-input:focus {
        border-color: var(--theme-primary, #0c6be1);
    }
    .search-input::placeholder {
        color: #ccc;
    }
    .search-btn {
        height: 46px;
        padding: 0 25px;
        background: var(--theme-primary, #0c6be1);
        border: none;
        border-radius: 6px;
        color: #fff;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }
    .search-btn:hover {
        background: var(--theme-primary-dark, #0a5bc4);
    }
    .search-btn i {
        font-size: 16px;
    }
    
    /* 免责声明 */
    .disclaimer {
        font-size: 13px;
        color: #666;
        line-height: 1.8;
        margin-bottom: 20px;
        padding: 0 5px;
        display: flex;
        align-items: flex-start;
    }
    .disclaimer .icon {
        color: var(--theme-accent, #ff9800);
        margin-right: 5px;
    }
    .disclaimer-icon {
        width: 20px;
        height: 20px;
        margin-right: 8px;
        flex-shrink: 0;
        margin-top: 2px;
    }
    
    /* 防骗提醒 */
    .warning-box {
        border: 1px solid #e8f4fd;
        border-radius: 8px;
        padding: 15px;
        background: #fafcff;
    }
    .warning-title {
        font-size: 14px;
        font-weight: bold;
        color: var(--theme-price, #ff6600);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .warning-title i {
        font-size: 16px;
    }
    .warning-content {
        font-size: 13px;
        color: #666;
        line-height: 1.8;
        padding: 0 5px;
    }
    .warning-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .warning-list li {
        font-size: 13px;
        color: #666;
        line-height: 2;
        padding-left: 5px;
    }
    .warning-footer {
        text-align: center;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px dashed #e8f4fd;
    }
    .warning-footer a {
        color: var(--theme-price, #ff6600);
        font-size: 13px;
        text-decoration: none;
    }
    .warning-footer a:hover {
        text-decoration: underline;
    }
    
    /* 查询结果区域 */
    .result-area {
        margin-top: 20px;
        display: none;
    }
    .result-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
    }
    
    /* 移动端适配 */
    @media (max-width: 768px) {
        /* 移动端隐藏底部信息 */
        .main-footer {
            display: none !important;
        }
        .query-card {
            margin-top: 15px;
        }
        .query-card-bg {
            padding: 30px 20px;
        }
        .query-card-content {
            padding: 20px;
        }
        .query-title {
            font-size: 20px;
        }
        .query-subtitle {
            font-size: 13px;
        }
        .search-box {
            flex-direction: row;
            gap: 8px;
        }
        .search-input {
            flex: 1 !important;
            min-width: 0;
            height: 44px;
            box-sizing: border-box;
            -webkit-appearance: none;
            appearance: none;
            border-radius: 8px;
        }
        .search-btn {
            width: auto;
            height: 44px;
            padding: 0 15px;
            white-space: nowrap;
            flex-shrink: 0;
            box-sizing: border-box;
        }
    }
    /* 验证码弹窗样式 */
    .captcha-popup {
        padding: 25px;
        text-align: center;
    }
    .captcha-title {
        font-size: 16px;
        font-weight: bold;
        color: #333;
        margin-bottom: 20px;
    }
    .captcha-box {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 20px;
    }
    .captcha-input {
        width: 120px;
        height: 44px;
        border: 1px solid #eee;
        border-radius: 8px;
        padding: 0 15px;
        font-size: 16px;
        text-align: center;
        outline: none;
    }
    .captcha-input:focus {
        border-color: var(--theme-primary, #0c6be1);
    }
    .captcha-img {
        height: 44px;
        border-radius: 8px;
        cursor: pointer;
    }
    .captcha-tip {
        font-size: 12px;
        color: #999;
        margin-bottom: 15px;
    }
    .captcha-btn {
        width: 100%;
        height: 44px;
        background: var(--theme-primary, #0c6be1);
        border: none;
        border-radius: 8px;
        color: #fff;
        font-size: 15px;
        cursor: pointer;
    }
    .captcha-btn:hover {
        background: var(--theme-primary-dark, #0a5bc4);
    }

    /* 缓存联系方式 */
    .cache-contacts {
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }
    .cache-label {
        font-size: 13px;
        color: #999;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .cache-list {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .cache-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        background: #f0f7ff;
        border: 1px solid #d4e8fc;
        border-radius: 20px;
        font-size: 13px;
        color: #333;
        cursor: pointer;
        transition: all 0.2s;
    }
    .cache-item:hover {
        background: #dceefb;
        border-color: var(--theme-primary, #0c6be1);
    }
    .cache-item .cache-text {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .cache-item .cache-del {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: rgba(0,0,0,0.08);
        color: #999;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    .cache-item .cache-del:hover {
        background: #ff4d4f;
        color: #fff;
    }
</style>

<!-- 主体内容 -->
<div class="order-body">
    <!-- 查询卡片 -->
    <div class="query-card">
        <!-- 背景图区域 -->
        <div class="query-card-bg">
            <div class="query-title" style="color: #333;">轻松查询订单，即刻享受自动交易</div>
        </div>
        
        <!-- 内容区域 -->
        <div class="query-card-content">
            <!-- 搜索框 -->
            <div class="search-box">
                <input type="text" class="search-input" id="queryInput" placeholder="请输入预留联系方式/订单号">
                <button class="search-btn" onclick="queryOrder()">
                    <i class="fa fa-search"></i> 查询订单
                </button>
            </div>

            <!-- 浏览器缓存的联系方式 -->
            <div class="cache-contacts" id="cacheContacts" style="display:none;">
                <span class="cache-label"><i class="fa fa-history"></i> 浏览器缓存：</span>
                <span class="cache-list" id="cacheList"></span>
            </div>
            
            <!-- 查单提醒 -->
            <div class="warning-box">
                <div class="warning-title">
                    <i class="fa fa-exclamation-circle"></i> 查单提醒
                </div>
                <div class="warning-content">
                    联系信息不要泄露给他人，订单编号可在手机付款记录中查看，如遗忘请联系客服找回！
                </div>
                <div class="warning-footer">
                    <a href="<?= DC_URL ?>?action=help">如遇售后问题，请联系客服处理！</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 查询结果区域 -->
    <div class="result-area" id="resultArea">
        <div class="result-card" id="resultCard">
            <!-- 查询结果将在这里显示 -->
        </div>
    </div>
</div>

<script>
// 修改头部标题和副标题
$(function() {
    // 修改标题
    $('.logo-brand .brand-title').text('已购卡密查询系统');
    // 修改副标题
    $('.logo-brand .brand-subtitle').text('仅显示一个月内的购买记录');
    // 如果没有副标题元素，则添加
    if ($('.logo-brand .brand-subtitle').length === 0) {
        $('.logo-brand .brand-text').append('<span class="brand-subtitle">仅显示一个月内的购买记录</span>');
    }
    
    // 在头部右侧添加返回帮助按钮
    var backBtn = '<div class="header-back-home"><a href="<?= DC_URL ?>?action=help" class="header-back-btn">返回帮助</a></div>';
    $('.header-help-mobile').after(backBtn);
});

// 显示验证码弹窗
function showCaptchaPopup() {
    var keyword = $('#queryInput').val().trim();
    if (!keyword) {
        layer.msg('请输入联系方式或订单号');
        return;
    }
    
    layer.open({
        type: 1,
        title: false,
        closeBtn: 1,
        shadeClose: true,
        area: ['300px', 'auto'],
        skin: 'captcha-layer',
        content: `
            <div class="captcha-popup">
                <div class="captcha-title">请输入验证码</div>
                <div class="captcha-box">
                    <input type="text" class="captcha-input" id="captchaInput" maxlength="4" placeholder="验证码">
                    <img src="<?= DC_URL ?>user/captcha.php?t=${Math.random()}" class="captcha-img" id="captchaImg" onclick="this.src='<?= DC_URL ?>user/captcha.php?t='+Math.random()" alt="验证码">
                </div>
                <div class="captcha-tip">点击图片可刷新验证码</div>
                <button class="captcha-btn" onclick="submitQuery()">确认查询</button>
            </div>
        `
    });
    
    // 自动聚焦到验证码输入框
    setTimeout(function() {
        $('#captchaInput').focus();
    }, 100);
}

// 提交查询
function submitQuery() {
    var keyword = $('#queryInput').val().trim();
    var captcha = $('#captchaInput').val().trim();
    
    if (!captcha) {
        layer.msg('请输入验证码');
        return;
    }
    
    var loadIndex = layer.load(2);
    
    // 先验证验证码
    $.ajax({
        url: '<?= DC_URL ?>user/captcha.php?action=check',
        type: 'POST',
        data: {
            code: captcha
        },
        dataType: 'json',
        success: function(res) {
            layer.close(loadIndex);
            if (res.code == 0) {
                // 验证码正确，跳转到新的查询结果页面
                layer.closeAll();
                window.location.href = '<?= DC_URL ?>?action=order_query&contact=' + encodeURIComponent(keyword);
            } else {
                layer.msg(res.msg || '验证码错误');
                // 刷新验证码
                $('#captchaImg').attr('src', '<?= DC_URL ?>user/captcha.php?t=' + Math.random());
                $('#captchaInput').val('').focus();
            }
        },
        error: function() {
            layer.close(loadIndex);
            layer.msg('验证失败，请重试');
            // 刷新验证码
            $('#captchaImg').attr('src', '<?= DC_URL ?>user/captcha.php?t=' + Math.random());
            $('#captchaInput').val('').focus();
        }
    });
}

function queryOrder() {
    showCaptchaPopup();
}

// 回车查询
$('#queryInput').on('keypress', function(e) {
    if (e.which == 13) {
        queryOrder();
    }
});

// 验证码输入框回车提交
$(document).on('keypress', '#captchaInput', function(e) {
    if (e.which == 13) {
        submitQuery();
    }
});

// 加载浏览器缓存的联系方式
function loadCacheContacts() {
    try {
        var cached = JSON.parse(localStorage.getItem('dc_order_contacts') || '[]');
        if (cached.length > 0) {
            var html = '';
            for (var i = 0; i < cached.length; i++) {
                html += '<div class="cache-item" data-index="' + i + '">';
                html += '<span class="cache-text">' + $('<span>').text(cached[i]).html() + '</span>';
                html += '<span class="cache-del" title="删除"><i class="fa fa-times"></i></span>';
                html += '</div>';
            }
            $('#cacheList').html(html);
            $('#cacheContacts').show();
        } else {
            $('#cacheContacts').hide();
        }
    } catch(e) {
        $('#cacheContacts').hide();
    }
}
loadCacheContacts();

// 点击缓存联系方式快速填入并自动弹出验证码查询
$(document).on('click', '.cache-item .cache-text', function() {
    var idx = $(this).closest('.cache-item').data('index');
    try {
        var cached = JSON.parse(localStorage.getItem('dc_order_contacts') || '[]');
        if (cached[idx] !== undefined) {
            $('#queryInput').val(cached[idx]);
            // 自动触发查询，直接进入验证码弹窗
            queryOrder();
        }
    } catch(e) {}
});

// 删除缓存联系方式
$(document).on('click', '.cache-item .cache-del', function(e) {
    e.stopPropagation();
    var $item = $(this).closest('.cache-item');
    var idx = $item.data('index');
    layer.confirm('确定要删除这条缓存记录吗？', {
        btn: ['确定', '取消'],
        title: false,
        closeBtn: 0
    }, function(confirmIdx) {
        layer.close(confirmIdx);
        try {
            var cached = JSON.parse(localStorage.getItem('dc_order_contacts') || '[]');
            cached.splice(idx, 1);
            localStorage.setItem('dc_order_contacts', JSON.stringify(cached));
        } catch(ex) {}
        loadCacheContacts();
    });
});
</script>

