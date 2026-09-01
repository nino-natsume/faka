<?php defined('DC_ROOT') || exit('access denied!'); ?>
<style>
    *{margin:0;padding:0;box-sizing:border-box;}
    body{
        font-family:-apple-system,BlinkMacSystemFont,"PingFang SC","Helvetica Neue",Helvetica,Arial,sans-serif;
        min-height:100vh;display:flex;align-items:center;justify-content:center;
        background:radial-gradient(circle at 12% 55%,rgba(33,150,243,.12),transparent 25%),
                   radial-gradient(circle at 85% 33%,rgba(108,99,255,.12),transparent 25%),#f5f7fa;
        padding:20px;
    }
    .doc-card{
        width:800px;max-width:100%;
        background:rgba(255,255,255,.72);
        backdrop-filter:saturate(180%) blur(20px);-webkit-backdrop-filter:saturate(180%) blur(20px);
        border:1px solid rgba(255,255,255,.85);
        border-radius:16px;
        box-shadow:0 12px 40px rgba(0,0,0,.08);
        overflow:hidden;
        animation:docPop .45s cubic-bezier(.2,.8,.2,1);
    }
    @keyframes docPop{from{opacity:0;transform:translateY(30px) scale(.96);}to{opacity:1;transform:none;}}
    .doc-header{padding:28px 32px 16px;text-align:center;}
    .doc-header .doc-icon{font-size:36px;color:#2196F3;margin-bottom:6px;}
    .doc-header .doc-title{font-size:20px;font-weight:700;color:#1a1a2e;}
    .doc-body{padding:8px 32px 32px;line-height:1.8;color:#333;font-size:15px;}
    .doc-body h2{font-size:20px;font-weight:700;color:#1a1a2e;margin:0 0 16px;}
    .doc-body h3{font-size:16px;font-weight:600;color:#374151;margin:20px 0 8px;}
    .doc-body p{margin:8px 0;}
    .doc-body ol,.doc-body ul{padding-left:20px;margin:8px 0;}
    .doc-body li{margin:4px 0;}
    .doc-empty{text-align:center;padding:40px 20px;color:#999;font-size:15px;}
    .doc-back{text-align:center;padding:0 32px 28px;}
    .doc-back a{color:#2196F3;text-decoration:none;font-size:14px;}
    .doc-back a:hover{text-decoration:underline;}
    @media(max-width:640px){
        .doc-header{padding:20px 16px 12px;}
        .doc-body{padding:8px 16px 24px;}
        .doc-back{padding:0 16px 20px;}
    }
</style>

<div class="doc-card">
    <div class="doc-header">
        <div class="doc-icon"><i class="ri-file-text-line"></i></div>
        <div class="doc-title">用户服务协议</div>
    </div>
    <div class="doc-body">
        <?php if(!empty($agreement_content)): ?>
            <?= $agreement_content ?>
        <?php else: ?>
            <div class="doc-empty">暂未配置用户服务协议内容</div>
        <?php endif; ?>
    </div>
    <div class="doc-back"><a href="javascript:void(0);" onclick="window.close();location.href='account.php?action=signup';">&larr; 返回</a></div>
</div>
