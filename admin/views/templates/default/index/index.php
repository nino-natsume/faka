<?php defined('DC_ROOT') || exit('access denied!'); ?>
<?php $mianze_date = strtotime(date('2025-10-25 01:12:00')) ?>
<style>
    /* 现代卡片样式 */
    .auth-card {
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }


    /* 渐变标题栏（改为蓝紫色调） */
    .auth-header {
        background: linear-gradient(135deg, #4F63EB 0%, #6C5CE7 100%);
        color: white;
        padding: 18px 24px;
        font-size: 18px;
        font-weight: 500;
    }

    /* 内容区域优化 */
    .auth-body {
        padding: 28px 24px;
    }
    .intro-text {
        font-size: 16px;
        color: #4A5568; /* 深灰色文字，提升可读性 */
        line-height: 1.6;
        margin-bottom: 24px;
    }

    /* 功能列表美化（调整图标颜色） */
    .feature-list {
        margin-bottom: 30px;
        padding-left: 0;
    }
    .feature-item {
        list-style: none;
        padding: 12px 0;
        padding-left: 32px;
        position: relative;
        border-bottom: 1px dashed #EDF2F7;
        color: #4A5568;
        transition: color 0.2s ease;
    }
    .feature-item:last-child {
        border-bottom: none;
    }
    .feature-item:before {
        content: "✓";
        position: absolute;
        left: 0;
        top: 12px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #38B2AC; /* 青绿色对勾，清新且不刺眼 */
        color: white;
        font-size: 12px;
        text-align: center;
        line-height: 20px;
    }
    .feature-item:hover {
        color: #38B2AC;
    }

    /* 按钮样式优化（匹配主色调） */
    .btn-group {
        display: flex;
        gap: 16px;
    }
    .layui-btn-primary-2 {
        background: #F7FAFC;
        color: #4A5568;
        border: none;
        padding: 0 24px;
        height: 42px;
        border-radius: 6px;
        transition: all 0.2s ease;
    }
    .layui-btn-primary-2:hover {
        background: #EDF2F7;
    }
    .primary-btn {
        background: #4F63EB;
        color: white;
        border: none;
        padding: 0 24px;
        height: 42px;
        border-radius: 6px;
        transition: all 0.2s ease;
    }
    .primary-btn:hover {
        background: #434190;
        box-shadow: 0 4px 12px rgba(79, 99, 235, 0.2);
    }
    .primary-btn i {
        margin-left: 8px;
    }
    
    /* 旋转动画 */
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    /* 隐藏更新弹窗的默认背景 */
    .layui-layer-nobg {
        background: transparent !important;
        box-shadow: none !important;
    }
    .layui-layer-nobg .layui-layer-content {
        background: #fff;
        border-radius: 16px;
    }
</style>

<?php
    // 读取配色插件设置（仅在插件启用时生效）
    $activePlugins = Option::get('active_plugins') ?: [];
    $isAdminColorActive = in_array('admin_color/admin_color.php', $activePlugins);
    
    if ($isAdminColorActive) {
        $colorStorage = Storage::getInstance('admin_color');
        $tplPrimaryColor = $colorStorage->getValue('primary_color') ?: '#4C7D71';
        $tplPrimaryColorLight = $colorStorage->getValue('primary_color_light') ?: '#EDF2F1';
        $tplPrimaryColorDark = $colorStorage->getValue('primary_color_dark') ?: '#4C7D71';
        // 渐变色时用于边框/文字的纯色
        $tplPrimarySolid = (strpos($tplPrimaryColor, 'gradient') !== false) ? $tplPrimaryColorDark : $tplPrimaryColor;
    } else {
        // 默认颜色
        $tplPrimaryColor = '#4C7D71';
        $tplPrimaryColorLight = '#EDF2F1';
        $tplPrimaryColorDark = '#4C7D71';
        $tplPrimarySolid = '#4C7D71';
    }
?>
<style>
    /* 统计卡片样式优化 */
    .index-kp .layui-card {
        border-radius: 6px;
        box-shadow: 8px 8px 20px 0 rgba(55,99,170,.1);
        transition: all 0.3s ease;
        margin-bottom: 15px;
        background-image: linear-gradient(0deg, #fff, #f3f5f8);
        border: 2px solid #fff;
        height: 100%;
    }
    .index-kp .layui-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px <?php echo $tplPrimaryColor; ?>26;
        border-color: <?php echo $tplPrimaryColor; ?>;
    }

    .index-kp .layui-card-header {
        border-bottom: 1px solid #EDF2F1;
        padding: 0 20px;
        font-weight: 600;
        color: #374151;
        background: transparent;
        height: 50px;
        line-height: 50px;
    }

    .index-kp .layui-card-body {
        padding: 24px 20px;
        text-align: center;
    }

    .index-kp .font-strong {
        font-size: 24px;
        font-weight: 700;
        color: <?php echo $tplPrimaryColor; ?>;
        line-height: 1.2;
        margin-bottom: 8px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        height: auto;
    }
    
    .index-kp .info {
        margin-top: 0 !important;
    }

    .index-kp .layui-card-body div.pb-3 {
        color: #9CA3AF;
        font-size: 13px;
        margin-top: 8px !important;
    }

    .index-kp .layui-card-footer {
        padding: 12px 20px;
        border-top: 1px solid #EDF2F1;
        background: #f1f2f5;
        color: #6B7280;
        font-size: 13px;
    }

    /* 徽标样式优化 */
    .index-kp .layui-badge {
        background: <?php echo $tplPrimaryColor; ?> !important;
        color: #fff !important;
        border: 1px solid <?php echo $tplPrimaryColor; ?>;
        border-radius: 4px;
        height: 22px;
        line-height: 22px;
        padding: 0 8px;
        font-size: 13px;
        padding-top: 2px;
        margin-top: 12px;
        font-weight: normal;
    }
    .index-kp .layui-badge.layui-bg-orange {
        background: linear-gradient(135deg, #430000 0%, #bf9500 100%)!important;
        color: #FCD34D!important;
        border: 1px solid #6e1a00;
        box-shadow: 0 2px 4px #c89200;
    }
    
    /* 授权状态专属样式 - 不影响其他徽标 */
    .index-kp .auth-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 10px;
        height: 24px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        letter-spacing: 0.5px;
        border: 1px solid transparent;
        margin-top: 13px;
    }
    .index-kp .auth-badge i {
        margin-right: 3px;
        font-size: 12px;
    }
    
    /* 状态颜色定义 */
    /* 未授权 - 灰色 */
    .auth-badge.unauth { background: #F3F4F6; color: #6B7280; border-color: #E5E7EB; }

    /* VIP - 青铜风格 */
    .auth-badge.vip { 
        background: linear-gradient(135deg, #804A00 0%, #CD7F32 50%, #B87333 100%);
        color: #FFECD2;
        border: 1px solid #996515;
        box-shadow: 0 2px 4px rgba(205, 127, 50, 0.5);
    }
    .auth-badge.vip:hover {
        box-shadow: 0 4px 8px rgba(205, 127, 50, 0.6);
        transform: translateY(-1px);
    }
 
    /* SVIP - 白银风格 */
    .auth-badge.svip {
        background: linear-gradient(135deg, #5C5C5C 0%, #C0C0C0 50%, #A8A8A8 100%);
        color: #FFFFFF;
        border: 1px solid #808080;
        box-shadow: 0 2px 4px rgba(192, 192, 192, 0.5);
    }
    .auth-badge.svip:hover {
        box-shadow: 0 4px 8px rgba(192, 192, 192, 0.6);
        transform: translateY(-1px);
    }

    /* 至尊会员 - 黑金色 */
    .auth-badge.supreme { 
        background: linear-gradient(135deg, #430000 0%, #bf9500 100%);
        color: #FCD34D;
        border: 1px solid #6e1a00;
        box-shadow: 0 2px 4px #c89200;
    }

    /* 错误状态 - 橘色 */
    .auth-badge.unauth { background: #F3F4F6; color: #6B7280; border-color: #E5E7EB; }

    /* 按钮样式优化 */
    .index-kp .layui-btn-primary {
        border-color: <?php echo $tplPrimaryColor; ?> !important;
        color: <?php echo $tplPrimaryColor; ?> !important;
        background: transparent;
        border-radius: 4px;
    }
    .index-kp .layui-btn-primary:hover {
        background: <?php echo $tplPrimaryColor; ?> !important;
        color: #fff !important;
    }
    
    /* 链接样式 */
    .index-kp a.label {
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 12px;
        text-decoration: none;
    }
    .index-kp a.label.primary {
        background: <?php echo $tplPrimaryColorLight; ?>;
        color: <?php echo $tplPrimaryColor; ?>;
    }
    .index-kp a.label.success {
        background: #DEF7EC;
        color: #03543F;
    }
</style>



<style>
    .update-modal .layui-layer-title{background:linear-gradient(0deg,#fff,#f3f5f8); color:#4C7D71}
    .update-modal .layui-layer-content{padding:0}
    .update-card{padding:24px}
    .update-header{font-size:18px; font-weight:600; margin-bottom:8px; color:#1F2937; text-align:center}
    .update-hint{background:#fffbeb; color:#92400e; border-left:3px solid #FDBA74; padding:10px 12px; border-radius:6px}
    .update-modal .layui-layer-btn{padding:14px}
    .update-modal .layui-layer-btn a{border-radius:6px; height:36px; line-height:36px; padding:0 18px}
    .update-modal .layui-layer-btn .layui-layer-btn0{background:#4C7D71; border-color:#4C7D71; color:#fff}
    .update-modal .layui-layer-btn .layui-layer-btn1{background:#F7FAFC; border-color:#E2E8F0; color:#4A5568}
</style>

<style>
    /* ==========================================================================
       EM Modal Pro - 自定义弹窗样式系统
       ========================================================================== */
    
    /* 覆盖 Layui 默认弹窗容器 */
    .em-modal-skin {
        border-radius: 20px !important;
        background: transparent !important;
        box-shadow: none !important;
    }
    
    .em-modal-skin .layui-layer-content {
        overflow: visible !important;
        background: transparent !important;
        border-radius: 20px;
    }
    
    /* 核心容器 */
    .em-modal-box {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        overflow: hidden;
        position: relative;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    
    /* 顶部装饰条 (可选) */
    .em-modal-box::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(90deg, #4C7D71, #6BA596);
    }
    
    /* 自定义关闭按钮 */
    .em-modal-close-btn {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #F3F4F6;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 10;
        color: #9CA3AF;
    }
    .em-modal-close-btn:hover {
        background: #E5E7EB;
        color: #4B5563;
        transform: rotate(90deg);
    }
    .em-modal-close-btn svg {
        width: 18px;
        height: 18px;
        fill: currentColor;
    }

    /* 头部区域 */
    .em-modal-header {
        padding: 40px 32px 24px;
        text-align: center;
        background: #fff;
    }
    
    .em-modal-icon-wrapper {
        width: 64px;
        height: 64px;
        background: #EDF2F1;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        color: #4C7D71;
    }
    .em-modal-icon-wrapper i {
        font-size: 32px;
    }
    
    .em-modal-title {
        font-size: 24px;
        font-weight: 800;
        color: #111827;
        margin-bottom: 8px;
        letter-spacing: -0.5px;
    }
    
    .em-modal-desc {
        font-size: 14px;
        color: #6B7280;
        line-height: 1.5;
        max-width: 80%;
        margin: 0 auto;
    }

    /* 内容区域 */
    .em-modal-body {
        padding: 0 32px 40px;
        background: #fff;
    }
    
    .em-modal-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .em-modal-item {
        display: flex;
        align-items: center;
        padding: 16px 20px;
        background: #F9FAFB;
        border: 1px solid #F3F4F6;
        border-radius: 12px;
        transition: all 0.2s;
        text-decoration: none;
        position: relative;
    }
    
    .em-modal-item:hover {
        background: #fff;
        border-color: #4C7D71;
        box-shadow: 0 8px 20px rgba(76, 125, 113, 0.1);
        transform: translateY(-2px);
    }
    
    .em-item-icon {
        width: 40px;
        height: 40px;
        background: #fff;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4C7D71;
        margin-right: 16px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        flex-shrink: 0;
    }
    
    .em-item-content {
        flex: 1;
        min-width: 0;
    }
    
    .em-item-title {
        font-size: 15px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 2px;
        display: block;
    }
    
    .em-item-sub {
        font-size: 12px;
        color: #555;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-family: monospace;
    }
    
    .em-item-action {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #D1D5DB;
        transition: all 0.2s;
    }
    
    .em-modal-item:hover .em-item-action {
        color: #4C7D71;
        transform: translateX(4px);
    }

    /* 移动端适配 */
    @media (max-width: 640px) {
        .em-modal-header { padding: 32px 24px 20px; }
        .em-modal-body { padding: 0 24px 32px; }
        .em-modal-title { font-size: 20px; }
        .em-modal-item { padding: 14px; }
    }

    .em-link-item:hover {
        border-color: #4C7D71;
        box-shadow: 0 8px 24px rgba(76, 125, 113, 0.12);
        transform: translateY(-2px);
    }
    /* 左侧图标装饰 */
    .em-link-icon {
        width: 44px;
        height: 44px;
        background: #EDF2F1;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 16px;
        flex-shrink: 0;
        transition: all 0.3s;
    }
    .em-link-icon i {
        color: #4C7D71;
        font-size: 22px;
    }
    .em-link-item:hover .em-link-icon {
        background: #4C7D71;
    }
    .em-link-item:hover .em-link-icon i {
        color: #fff;
    }

    /* 中间文本区域 */
    .em-link-content {
        flex: 1;
        min-width: 0;
        margin-right: 16px;
    }
    .em-link-name {
        font-size: 15px;
        font-weight: 600;
        color: #1F2937;
        margin-bottom: 4px;
        display: block;
    }
    .em-link-url {
        font-size: 13px;
        color: #9CA3AF;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
        font-family: 'Monaco', 'Menlo', 'Ubuntu', 'Consolas', 'source-code-pro', monospace;
    }

    /* 右侧按钮 */
    .em-link-action a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 36px;
        padding: 0 20px;
        background: #fff;
        color: #4C7D71;
        border: 1px solid #4C7D71;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .em-link-action a:hover {
        background: #4C7D71;
        color: #fff;
        box-shadow: 0 4px 12px rgba(76, 125, 113, 0.25);
    }
    
    @media (max-width: 767px) {
        .em-link-item {
            flex-wrap: wrap;
            padding: 16px;
        }
        .em-link-icon {
            width: 36px;
            height: 36px;
            margin-right: 12px;
        }
        .em-link-icon i { font-size: 18px; }
        .em-link-content {
            width: calc(100% - 60px);
            margin-right: 0;
        }
        .em-link-action {
            width: 100%;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px dashed #EDF2F1;
        }
        .em-link-action a {
            width: 100%;
        }
    }
</style>



<?php doAction('adm_main_top') ?>

<?php if (file_exists(DC_ROOT . '/install.php')): ?>
<div style="margin: 20px 0;">
    <div style="background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%); border: 1px solid #F87171; border-radius: 12px; padding: 20px 24px; display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; background: #DC2626; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i class="ri-error-warning-line" style="font-size: 24px; color: #fff;"></i>
        </div>
        <div style="flex: 1;">
            <div style="font-size: 16px; font-weight: 600; color: #991B1B; margin-bottom: 4px;">安全警告</div>
            <div style="font-size: 14px; color: #B91C1C;">检测到 install.php 安装文件仍然存在，这可能导致您的网站被恶意重装！请立即删除该文件。</div>
        </div>
        <a href="javascript:;" onclick="deleteInstallFile()" style="background: #DC2626; color: #fff; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 500; text-decoration: none; white-space: nowrap;">立即删除</a>
    </div>
</div>
<script>
function deleteInstallFile() {
    layer.confirm('确定要删除 install.php 文件吗？', {icon: 3, title: '安全提示'}, function(index) {
        layer.close(index);
        $.post('./ajax.php?action=delete_install_file', function(res) {
            if (res.code === 0 || res.code === 200) {
                layer.msg('安装文件已删除', {icon: 1});
                setTimeout(function() { location.reload(); }, 1000);
            } else {
                layer.msg(res.msg || '删除失败，请手动删除', {icon: 2});
            }
        }, 'json').fail(function() {
            layer.msg('请求失败，请手动删除 install.php 文件', {icon: 2});
        });
    });
}
</script>
<?php endif; ?>

<div class="mt-4 shadow-lg" style="margin-bottom: 20px;">
    <?php if (!Register::isRegLocal()) : ?>
        <div style="margin-top: 25px;">
            <div class="auth-promo-card">
                <div class="auth-promo-side">
                    <div class="auth-promo-dots"><i class="dot-r"></i><i class="dot-y"></i><i class="dot-g"></i></div>
                </div>
                <div class="promo-main">
                    <div class="promo-headline">
                        <h3>系统授权提示：</h3>
                        <p>您安装的 DCSHOP 系统尚未授权，授权后可解锁全部功能</p>
                    </div>
                    <div class="promo-bottom">
                        <div class="promo-list">
                            <div class="list-item">
                                <i class="ri-checkbox-circle-line"></i>
                                <span>支持在线升级，及时获取最新版本与安全补丁</span>
                            </div>
                            <div class="list-item">
                                <i class="ri-checkbox-circle-line"></i>
                                <span>开放应用商店，自由安装模板、插件等扩展应用</span>
                            </div>
                            <div class="list-item">
                                <i class="ri-checkbox-circle-line"></i>
                                <span>解除全部功能限制，享受官方专属技术支持</span>
                            </div>
                        </div>
                        <div class="promo-actions">
                            <a href="auth.php" class="promo-btn primary">去授权 <i class="ri-arrow-right-line" style="font-size: 12px;"></i></a>
                            <span class="promo-btn secondary get-em-buy-info">获取授权码</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .auth-promo-card {
                background: url('https://cloudcache.tencentcs.cn/qcloud/ui/activity-v2/build/LatestActivity/images/latest_card_bg_type1.png') center/cover no-repeat;
                border-radius: 8px;
                padding: 0;
                display: flex;
                align-items: stretch;
                box-shadow: 0 2px 12px rgba(0,0,0,0.04);
                border: 2px solid #fff;
                position: relative;
                overflow: hidden;
            }
            /* 左侧竖排三点 */
            .auth-promo-side {
                display: flex;
                align-items: center;
                padding: 0 16px;
                background: #f0f1f4;
                border-right: 1px solid #e8eaed;
                flex-shrink: 0;
            }
            .auth-promo-dots { display: flex; flex-direction: column; gap: 5px; }
            .auth-promo-dots i { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
            .auth-promo-dots .dot-r { background: #ff5f57; }
            .auth-promo-dots .dot-y { background: #febc2e; }
            .auth-promo-dots .dot-g { background: #28c840; }
            
            .promo-main {
                display: flex;
                flex-direction: column;
                flex: 1;
                padding: 18px 24px;
                gap: 0px;
            }
            
            .promo-headline {
                display: flex;
                align-items: baseline;
                gap: 6px;
                flex: 1;
                min-width: 0;
            }
            .promo-headline h3 {
                font-size: 15px;
                font-weight: 400;
                color: #1F2937;
                white-space: nowrap;
                flex-shrink: 0;
            }
            .promo-headline p {
                font-size: 13px;
                color: #9CA3AF;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            
            .promo-bottom {
                display: flex;
                align-items: flex-end;
                justify-content: space-between;
                gap: 20px;
            }
            .promo-list {
                display: flex;
                flex-wrap: wrap;
                gap: 6px 24px;
                flex: 1;
            }
            .list-item {
                display: flex;
                align-items: center;
                gap: 6px;
                font-size: 13px;
                color: #6B7280;
            }
            .list-item i {
                color: <?php echo $tplPrimaryColor; ?>;
                font-size: 14px;
                flex-shrink: 0;
            }
            
            .promo-actions {
                display: flex;
                gap: 10px;
                flex-shrink: 0;
            }
            .promo-btn {
                padding: 8px 18px;
                border-radius: 6px;
                font-size: 13px;
                font-weight: 400;
                cursor: pointer;
                text-decoration: none;
                transition: all 0.2s;
                text-align: center;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 5px;
            }
            .promo-btn.primary {
                background: <?php echo $tplPrimaryColor; ?>;
                color: #fff;
                box-shadow: 0 4px 12px <?php echo $tplPrimaryColor; ?>33;
            }
            .promo-btn.primary:hover {
                background: <?php echo $tplPrimaryColorDark; ?>;
                transform: translateY(-1px);
                box-shadow: 0 6px 16px <?php echo $tplPrimaryColor; ?>4d;
                color: #fff;
            }
            .promo-btn.secondary {
                background: <?php echo $tplPrimaryColorLight; ?>;
                color: <?php echo $tplPrimarySolid; ?>;
                border: 1px solid <?php echo $tplPrimarySolid; ?>33;
            }
            .promo-btn.secondary:hover {
                background: <?php echo $tplPrimaryColor; ?>;
                color: #fff;
            }
            
            @media (max-width: 992px) {
                .auth-promo-card { flex-direction: column; }
                .auth-promo-side {
                    border-right: none;
                    border-bottom: 1px solid #e8eaed;
                    padding: 10px 16px;
                    justify-content: flex-start;
                }
                .auth-promo-dots { flex-direction: row; gap: 6px; }
                .auth-promo-dots i { width: 10px; height: 10px; }
                .promo-main { padding: 16px 18px; gap: 12px; }
                .promo-headline { flex-direction: column; gap: 2px; }
                .promo-bottom { flex-direction: column; align-items: stretch; gap: 12px; }
                .promo-list { flex-direction: column; gap: 6px; }
                .promo-actions { width: 100%; }
                .promo-btn { flex: 1; }
            }
        </style>
    <?php endif ?>


</div>

<div class="index-kp">
    <div class="grid-cols-xs-2 grid-cols-sm-2 grid-cols-xl-5 grid-cols-lg-3 grid-cols-md-3  mb-3 grid-gap-10" style="width: 100%;">
        <!-- 第一行：系统版本 - 授权码 - 用户协议 - 下载安装包 - 库存预警 -->
        <div class="layui-card" id="system-version-card">
            <div class="layui-card-header" style="display: flex; justify-content: space-between;">
                <span>系统版本</span>
                <?php if (!Register::isRegLocal()) : ?>
                    <a href="<?= DC_LINE[CURRENT_LINE]['value'] ?>" target="_blank" class="auth-badge unauth">未授权</a>
                <?php else: ?>
                    <?php if (Register::getRegType() == 2): ?>
                        <a href="<?= DC_LINE[CURRENT_LINE]['value'] ?>" target="_blank" class="auth-badge svip"><i class="ri-vip-diamond-line"></i>SVIP会员</a>
                    <?php elseif (Register::getRegType() == 1): ?>
                        <a href="<?= DC_LINE[CURRENT_LINE]['value'] ?>" target="_blank" class="auth-badge vip">VIP会员</a>
                    <?php elseif (Register::getRegType() == 3): ?>
                        <a href="<?= DC_LINE[CURRENT_LINE]['value'] ?>" target="_blank" class="auth-badge supreme"><i class="ri-vip-crown-line"></i>至尊会员</a>
                    <?php else: ?>
                        <a href="<?= DC_LINE[CURRENT_LINE]['value'] ?>" target="_blank" class="auth-badge error">错误状态</a>
                    <?php endif ?>
                <?php endif; ?>
            </div>
            <div class="layui-card-body">
                <p class="info mt-5 font-strong">v<?= ucfirst(Option::DC_VERSION) ?></p>
                <div class="pb-3 mt-2">DCSHOP</div>
            </div>
            <div class="layui-card-footer" style="display: flex; justify-content: space-between;">
                <span>版本状态</span>
                <span id="check-update">
                    <span class="layui-badge layui-bg-green">已是最新</span>
                </span>
            </div>
        </div>
        <div class="layui-card">
            <div class="layui-card-header" style="display: flex; justify-content: space-between;">
                <span>授权码</span>
                <span class="layui-badge layui-bg-orange">官方</span>
            </div>
            <div class="layui-card-body">
                <?php if (Register::isRegLocal()) : ?>
                <p class="info mt-5 font-strong" style="font-size: 22px;">正版授权</p>
                <div class="pb-3 mt-2">点击下方按钮获取正版授权</div>
                <?php else: ?>
                <p class="info mt-5 font-strong" style="font-size: 22px; color: #9CA3AF;">未授权</p>
                <div class="pb-3 mt-2">点击下方按钮获取正版授权</div>
                <?php endif; ?>
            </div>
            <div class="layui-card-footer" style="display: flex; justify-content: space-between;">
                <span>获取官方正版授权</span>
                <span><button type="button" class="layui-btn layui-btn-primary layui-btn-xs layui-border-blue get-em-buy-info">获取</button></span>
            </div>
        </div>
        <?php if(Option::get('mianze') > $mianze_date): ?>
        <div class="layui-card">
            <div class="layui-card-header" style="display: flex; justify-content: space-between;">
                <span>用户协议</span>
                <span class="layui-badge layui-bg-green">同意</span>
            </div>
            <div class="layui-card-body">
                <p class="info mt-5 font-strong"><?= date('Y-m-d', Option::get('mianze')) ?></p>
                <div class="pb-3 mt-2">签署日期</div>
            </div>
            <div class="layui-card-footer" style="display: flex; justify-content: space-between;">
                <span>查看协议</span>
                <span><button type="button" id="chakan-mianze" class="layui-btn  layui-btn-primary layui-btn-xs layui-border-blue">点击查看</button></span>
            </div>
        </div>
        <?php endif; ?>
        <div class="layui-card">
            <div class="layui-card-header" style="display: flex; justify-content: space-between;">
                <span>下载安装包</span>
                <span class="layui-badge layui-bg-orange">官方</span>
            </div>
            <div class="layui-card-body">
                <p class="info mt-5 font-strong" style="font-size: 22px;">DCSHOP</p>
                <div class="pb-3 mt-2">点击下方按钮获取下载链接</div>
            </div>
            <div class="layui-card-footer" style="display: flex; justify-content: space-between;">
                <span>获取下载信息</span>
                <span><button type="button" class="layui-btn layui-btn-primary layui-btn-xs layui-border-blue get-download-info">获取</button></span>
            </div>
        </div>
        <div class="layui-card">
            <div class="layui-card-header" style="display: flex; justify-content: space-between;">
                <span>库存预警</span>
                <?php if($low_stock_count > 0): ?>
                <span class="layui-badge">预警</span>
                <?php else: ?>
                <span class="layui-badge layui-bg-green">正常</span>
                <?php endif; ?>
            </div>
            <div class="layui-card-body">
                <p class="info mt-5 font-strong"><?= $low_stock_count ?></p>
                <div class="pb-3 mt-2">库存不足10的商品</div>
            </div>
            <div class="layui-card-footer" style="display: flex; justify-content: space-between;">
                <span>无库存商品</span>
                <span style="color: <?= $no_stock_count > 0 ? '#f56c6c' : 'inherit' ?>"><?= $no_stock_count ?> 个</span>
            </div>
        </div>

        <!-- 第二行：新增用户 - 订单数量 - 销售额 - 客单价 - 待处理售后 -->
        <div class="layui-card">
            <div class="layui-card-header" style="display: flex; justify-content: space-between;">
                <span>新增用户</span>
                <span class="layui-badge layui-bg-green">今日</span>
            </div>
            <div class="layui-card-body">
                <p class="info mt-5 font-strong"><?= $user_panel['today_registrations'] ?></p>
                <div class="pb-3 mt-2">昨日 <?= $user_panel['yesterday_registrations'] ?> 人</div>
            </div>
            <div class="layui-card-footer" style="display: flex; justify-content: space-between;">
                <span>本月新增用户</span>
                <span><?= $user_panel['month_registrations'] ?> 人</span>
            </div>
        </div>
        <div class="layui-card">
            <div class="layui-card-header" style="display: flex; justify-content: space-between;">
                <span>订单数量</span>
                <span class="layui-badge layui-bg-green">今日</span>
            </div>
            <div class="layui-card-body">
                <p class="info mt-5 font-strong"><?= $order_panel['today_orders'] ?></p>
                <div class="pb-3 mt-2">昨日 <?= $order_panel['yesterday_orders'] ?> 单</div>
            </div>
            <div class="layui-card-footer" style="display: flex; justify-content: space-between;">
                <span>本月订单量</span>
                <span><?= $order_panel['month_orders'] ?> 单</span>
            </div>
        </div>
        <div class="layui-card">
            <div class="layui-card-header" style="display: flex; justify-content: space-between;">
                <span>销售额</span>
                <span class="layui-badge layui-bg-green">今日</span>
            </div>
            <div class="layui-card-body">
                <p class="info mt-5 font-strong"><?= $today_sales_amount ?></p>
                <div class="pb-3 mt-2">昨日 <?= $yesterday_sales_amount ?> 元</div>
            </div>
            <div class="layui-card-footer" style="display: flex; justify-content: space-between;">
                <span>本月销售额</span>
                <span><?= $current_month_sales_amount ?> 元</span>
            </div>
        </div>
        <div class="layui-card">
            <div class="layui-card-header" style="display: flex; justify-content: space-between;">
                <span>客单价</span>
                <span class="layui-badge layui-bg-green">今日</span>
            </div>
            <div class="layui-card-body">
                <p class="info mt-5 font-strong">
                    <?= empty($today_sales_amount) || empty($order_panel['today_orders']) ? number_format(0, 2) : number_format($today_sales_amount / $order_panel['today_orders'], 2) ?>
                </p>
                <div class="pb-3 mt-2">
                    昨日 <?= empty($yesterday_sales_amount) || empty($order_panel['yesterday_orders']) ? number_format(0, 2) : number_format($yesterday_sales_amount / $order_panel['yesterday_orders'], 2) ?> 元
                </div>
            </div>
            <div class="layui-card-footer" style="display: flex; justify-content: space-between;">
                <span>本月客单价</span>
                <span>
                    <?= empty($current_month_sales_amount) || empty($order_panel['month_orders']) ? number_format(0, 2) : number_format(str_replace(',', '', $current_month_sales_amount) / $order_panel['month_orders'], 2) ?> 元
                </span>
            </div>
        </div>
        <?php if($isAftersalePluginActive): ?>
        <div class="layui-card">
            <div class="layui-card-header" style="display: flex; justify-content: space-between;">
                <span>待处理售后</span>
                <?php if($pending_aftersale_count > 0): ?>
                <span class="layui-badge">待处理</span>
                <?php else: ?>
                <span class="layui-badge layui-bg-green">无</span>
                <?php endif; ?>
            </div>
            <div class="layui-card-body">
                <p class="info mt-5 font-strong"><?= $pending_aftersale_count ?></p>
                <div class="pb-3 mt-2">今日新增 <?= $today_aftersale_count ?> 条</div>
            </div>
            <div class="layui-card-footer" style="display: flex; justify-content: space-between;">
                <span>处理售后</span>
                <span><a href="./aftersale.php">立即处理</a></span>
            </div>
        </div>
        <?php endif; ?>
    </div>




</div>







<?php if (User::isAdmin()): ?>
        <?php doAction('adm_main_content') ?>
<?php endif; ?>

<script>
    $('#chakan-mianze').click(function(){
        let isMobile = window.innerWidth < 768;
        let area = isMobile ? ['98%', 'auto']  : ['700px', 'auto'];
        layer.open({
            type: 2,
            id: 'xieyi',
            title: "DCSHOP商城用户协议",
            area: area,
            closeBtn: true,
            maxmin: true,
            content: 'index.php?action=mianze&chakan=1',
            shadeClose: true,
            scrollbar: false,
            skin: 'dc-layer-modern',
            fixed: false, // 不固定
            success: function(layero, index, that){
                layer.iframeAuto(index); // 让 iframe 高度自适应
                that.offset(); // 重新自适应弹层坐标
            }
        });
    })
</script>

<?php if(Option::get('mianze') < $mianze_date): ?>
<script>
    let isMobile = window.innerWidth < 768;
    let area = isMobile ? ['98%', 'auto']  : ['700px', 'auto'];
    layer.open({
        type: 2,
        title: "DCSHOP商城用户协议",
        area: area,
        closeBtn: false,
        content: 'index.php?action=mianze',
        shadeClose: false,
        scrollbar: false,
        skin: 'dc-layer-modern',
        fixed: false, // 不固定
        success: function(layero, index, that){
            layer.iframeAuto(index); // 让 iframe 高度自适应
            that.offset(); // 重新自适应弹层坐标
        }
    });
</script>
<?php endif; ?>

<script>
    // 在线更新功能已关闭

    $("#menu-dashboard").addClass('active');

    $('.get-download-info').click(function(){
        loadIndex = layer.load(2);
        $.ajax({
            url: '?action=get_download_url',
            type: 'GET',
            dataType: 'json',
            success: function(e){
                if(e.code == 200){
                    var d = e.data || {};
                    var version = d.version || '';
                    var size = d.size || '';
                    var list = [];
                    if (Array.isArray(d.buy_url)) { list = d.download_url; }
                    if (Array.isArray(d.mirrors)) { list = d.mirrors; }
                    if (d.url) { list.push({ name: '官方下载', url: d.url }); }
                    var clean = function(s){ return String(s || '').replace(/[`'"\s]/g,'').trim(); };
                    var linksHtml = '';
                    list.forEach(function(it){
                        var name = it.name || '下载地址';
                        var url = clean(it.url || it.link || '');
                        if(url){
                            linksHtml += `
                                <a href="${url}" target="_blank" rel="noopener" class="em-modal-item">
                                    <div class="em-item-icon"><i class="ri-link"></i></div>
                                    <div class="em-item-content">
                                        <span class="em-item-title">${name}</span>
                                        <span class="em-item-sub">${url}</span>
                                    </div>
                                    <div class="em-item-action"><i class="ri-arrow-right-s-line"></i></div>
                                </a>`;
                        }
                    });
                    var isMobile = window.innerWidth < 640;
                    var area = isMobile ? ['90%', 'auto'] : ['520px', 'auto'];
                    var desc = (version ? ('版本：'+ version + ' ') : '') + (size ? (' · 大小：'+ size) : '');
                    
                    var content = `
                        <div class="em-modal-box">
                            <div class="em-modal-close-btn" onclick="layer.close(layer.index)">
                                <svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                            </div>
                            <div class="em-modal-header">
                                <div class="em-modal-icon-wrapper"><i class="ri-download-cloud-line"></i></div>
                                <div class="em-modal-title">下载安装包</div>
                                <div class="em-modal-desc">${desc || '请选择下载线路'}</div>
                            </div>
                            <div class="em-modal-body">
                                <div class="em-modal-list">
                                    ${linksHtml}
                                </div>
                            </div>
                        </div>
                    `;
                    layer.open({
                        type: 1,
                        title: false,
                        closeBtn: 0,
                        area: area,
                        shadeClose: true,
                        skin: 'em-modal-skin',
                        btn: false,
                        content: content
                    });
                } else {
                    layer.alert(e.msg);
                }
            },
            error: function(xhr, textStatus, errorThrown){
                var msg = '';
                if (xhr && xhr.responseJSON && xhr.responseJSON.msg) {
                    msg = xhr.responseJSON.msg;
                } else if (xhr && xhr.responseText) {
                    msg = (xhr.status || '') + ' ' + (xhr.statusText || '') + ' ' + xhr.responseText.slice(0, 200);
                } else {
                    msg = '获取下载信息失败：' + (errorThrown || textStatus || '未知错误');
                }
                layer.alert(msg);
            },
            complete: function(){
                layer.close(loadIndex);
            }
        });
    })
</script>

