<?php
defined('DC_ROOT') || exit('access denied!');

$footprints = isset($footprintList) && is_array($footprintList) ? $footprintList : [];
$summary = isset($footprintSummary) && is_array($footprintSummary) ? $footprintSummary : ['total' => 0, 'total_views' => 0, 'last_time' => 0];
$page = isset($page) ? max(1, (int)$page) : 1;
$totalPages = isset($totalPages) ? max(1, (int)$totalPages) : 1;
$footprintToken = isset($footprintToken) ? $footprintToken : LoginAuth::genToken();

$fpCover = function($cover) {
    $cover = trim((string)$cover);
    if ($cover === '') return DC_URL . 'admin/views/images/cover.svg';
    if (preg_match('#^(https?:)?//#i', $cover)) return $cover;
    return rtrim(DC_URL, '/') . '/' . ltrim($cover, '/');
};
$fpText = function($text, $len = 42) {
    $text = trim(strip_tags(html_entity_decode((string)$text, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    if ($text === '') return '暂无商品简介';
    if (function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > $len) return mb_substr($text, 0, $len, 'UTF-8') . '...';
    return strlen($text) > $len * 2 ? substr($text, 0, $len * 2) . '...' : $text;
};
?>
<style>
.mfp-page { min-height:100vh; padding:12px 12px calc(18px + env(safe-area-inset-bottom)); background:#f4f6fa; }
.uc-site-footer { display:none !important; }
.mfp-toolbar { margin:0 0 12px; padding:10px 12px; display:flex; align-items:center; justify-content:space-between; gap:10px; background:linear-gradient(0deg, #fff, #f3f5f8); border:2px solid #fff; border-radius:10px; box-shadow:var(--shadow-primary); }
.mfp-count { color:#64748b; font-size:12px; }
.mfp-count strong { color:var(--theme-primary); }
.mfp-clear { height:32px; padding:0 12px; border:0; border-radius:999px; background:#fff0f0; color:#ef4444; font-size:12px; font-weight:800; }
.mfp-list { display:flex; flex-direction:column; gap:10px; }
.mfp-item { display:grid; grid-template-columns:92px minmax(0,1fr); gap:12px; padding:10px; border-radius:16px; background:linear-gradient(0deg,#fff,#f8fafc); border:1px solid rgba(255,255,255,.9); box-shadow:0 8px 22px rgba(15,23,42,.06); }
.mfp-cover { position:relative; display:block; width:92px; height:92px; border-radius:13px; overflow:hidden; background:#eef2f7; }
.mfp-cover img { width:100%; height:100%; object-fit:cover; display:block; }
.mfp-off { position:absolute; inset:auto 6px 6px; height:22px; line-height:22px; border-radius:999px; background:rgba(239,68,68,.88); color:#fff; font-size:11px; font-weight:800; text-align:center; }
.mfp-main { min-width:0; display:flex; flex-direction:column; }
.mfp-title { color:#0f172a; font-size:14px; font-weight:900; line-height:1.42; text-decoration:none; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.mfp-desc { margin-top:5px; color:#94a3b8; font-size:11px; line-height:1.5; display:-webkit-box; -webkit-line-clamp:1; -webkit-box-orient:vertical; overflow:hidden; }
.mfp-meta { margin-top:7px; display:flex; align-items:center; flex-wrap:wrap; gap:6px; }
.mfp-meta span { display:inline-flex; align-items:center; gap:4px; height:21px; padding:0 7px; border-radius:999px; background:#f1f5f9; color:#64748b; font-size:11px; }
.mfp-bottom { margin-top:auto; display:flex; align-items:flex-end; justify-content:space-between; gap:8px; padding-top:8px; }
.mfp-price { color:#ef4444; font-size:11px; font-weight:800; }
.mfp-price strong { font-size:17px; }
.mfp-time { color:#94a3b8; font-size:10px; line-height:1.35; text-align:right; }
.mfp-actions { grid-column:1 / -1; display:grid; grid-template-columns:1fr 42px; gap:8px; }
.mfp-go,.mfp-del { height:36px; border-radius:12px; display:flex; align-items:center; justify-content:center; gap:6px; font-size:13px; font-weight:900; text-decoration:none; }
.mfp-go { border:0; color:#fff; background:linear-gradient(135deg,var(--theme-primary,#667eea),var(--theme-secondary,#764ba2)); }
.mfp-go.is-disabled { background:#cbd5e1; color:#fff; }
.mfp-del { border:0; background:#f8fafc; color:#94a3b8; }
.mfp-empty { margin-top:12px; padding:60px 16px; border-radius:10px; background:linear-gradient(0deg, #fff, #f3f5f8); border:2px solid #fff; text-align:center; color:#bbb; font-size:13px; box-shadow:var(--shadow-primary); }
.mfp-empty svg { display:block; width:140px; height:auto; margin:0 auto 10px; }
.mfp-empty h3 { margin:0 0 8px; color:#334155; font-size:17px; }
.mfp-empty p { margin:0 0 16px; font-size:12px; }
.mfp-empty a { display:inline-flex; align-items:center; justify-content:center; height:38px; padding:0 18px; border-radius:999px; color:#fff; background:var(--theme-primary); font-size:13px; font-weight:900; text-decoration:none; }
.mo-pager { display:none; margin-top:12px; padding-bottom:8px; }
.mo-pager.is-visible { display:block; }
.mo-pager-row { display:grid; grid-template-columns:72px minmax(0,1fr) 72px; gap:8px; align-items:center; }
.mo-page-btn { height:32px; border:0; border-radius:999px; background:#fff; color:var(--theme-primary, #667eea); font-size:12px; font-weight:900; display:flex; align-items:center; justify-content:center; gap:4px; box-shadow:0 4px 14px rgba(31,52,88,0.06); }
.mo-page-btn:disabled { background:#f8f9fb; color:#c0c7d2; box-shadow:none; }
.mo-page-current { height:32px; border-radius:999px; background:#fff; color:#20242c; font-size:12px; font-weight:900; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 14px rgba(31,52,88,0.06); }
@media (max-width:360px){ .mfp-item{grid-template-columns:82px minmax(0,1fr);} .mfp-cover{width:82px;height:82px;} }
</style>

<div class="mfp-page">
    <div class="mfp-toolbar">
        <div class="mfp-count">共 <strong><?= (int)$total ?></strong> 条足迹</div>
        <?php if (!empty($footprints)): ?><button type="button" class="mfp-clear" id="mfpClearBtn"><i class="fa fa-trash-o"></i> 清空</button><?php endif; ?>
    </div>

    <?php if (empty($footprints)): ?>
    <div class="mfp-empty">
        <?php include __DIR__ . '/_svg_empty.php'; ?>
        <p>暂无浏览足迹</p>
        <a href="<?= DC_URL ?>"><i class="fa fa-shopping-bag"></i>&nbsp;去商城逛逛</a>
    </div>
    <?php else: ?>
    <div class="mfp-list">
        <?php foreach ($footprints as $item): ?>
        <?php $available = !empty($item['available']); ?>
        <article class="mfp-item" data-id="<?= (int)$item['footprint_id'] ?>">
            <a class="mfp-cover" href="<?= $available ? htmlspecialchars($item['url']) : 'javascript:void(0);' ?>">
                <img src="<?= htmlspecialchars($fpCover($item['cover'] ?? '')) ?>" alt="<?= htmlspecialchars($item['title']) ?>" onerror="this.src='<?= DC_URL ?>admin/views/images/cover.svg';">
                <?php if (!$available): ?><span class="mfp-off">已失效</span><?php endif; ?>
            </a>
            <div class="mfp-main">
                <a class="mfp-title" href="<?= $available ? htmlspecialchars($item['url']) : 'javascript:void(0);' ?>"><?= htmlspecialchars($item['title']) ?></a>
                <div class="mfp-desc"><?= htmlspecialchars($fpText($item['des'] ?? '')) ?></div>
                <div class="mfp-meta"><span><i class="fa fa-eye"></i><?= (int)$item['view_count'] ?>次</span><span><i class="fa fa-line-chart"></i>售<?= (int)$item['sales'] ?></span><span><i class="fa fa-cubes"></i><?= (int)$item['stock'] ?></span></div>
                <div class="mfp-bottom"><div class="mfp-price">¥ <strong><?= number_format((float)$item['price'], 2) ?></strong></div><div class="mfp-time">最近浏览<br><?= htmlspecialchars($item['last_view_time_text']) ?></div></div>
            </div>
            <div class="mfp-actions">
                <?php if ($available): ?><a class="mfp-go" href="<?= htmlspecialchars($item['url']) ?>"><i class="fa fa-shopping-cart"></i> 查看商品</a><?php else: ?><span class="mfp-go is-disabled"><i class="fa fa-ban"></i> 暂不可访问</span><?php endif; ?>
                <button type="button" class="mfp-del" data-id="<?= (int)$item['footprint_id'] ?>"><i class="fa fa-trash-o"></i></button>
            </div>
        </article>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <nav class="mo-pager is-visible" id="mfpPager">
        <div class="mo-pager-row">
            <button type="button" class="mo-page-btn" id="mfpPrevPage" <?= $page <= 1 ? 'disabled' : '' ?> data-url="/user/footprint.php?page=<?= max(1, $page - 1) ?>"><i class="fa fa-angle-left"></i> 上一页</button>
            <div class="mo-page-current" id="mfpPageCurrent"><?= (int)$page ?> / <?= (int)$totalPages ?></div>
            <button type="button" class="mo-page-btn" id="mfpNextPage" <?= $page >= $totalPages ? 'disabled' : '' ?> data-url="/user/footprint.php?page=<?= min($totalPages, $page + 1) ?>">下一页 <i class="fa fa-angle-right"></i></button>
        </div>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
</div>

<script>
(function(){
    var token = <?= json_encode($footprintToken) ?>;
    function msg(text){ if (window.layer && layer.msg) layer.msg(text); else alert(text); }
    function post(action, data, done){
        data = data || {}; data.token = token;
        $.post('/user/footprint.php?action=' + action, data, function(res){
            if (res && res.code === 200) { done && done(res); return; }
            msg((res && res.msg) || '操作失败');
        }, 'json').fail(function(){ msg('网络异常，请稍后重试'); });
    }
    $('#mfpClearBtn').on('click', function(){
        var run = function(){ post('clear', {}, function(){ location.reload(); }); };
        if (window.layer && layer.confirm) layer.confirm('确定清空全部浏览足迹吗？', {title:'清空确认'}, run);
        else if (confirm('确定清空全部浏览足迹吗？')) run();
    });
    $('#mfpPrevPage,#mfpNextPage').on('click', function(){
        if ($(this).prop('disabled')) return;
        var url = $(this).data('url');
        if (url) window.location.href = url;
    });
    $('.mfp-del').on('click', function(){
        var $item = $(this).closest('.mfp-item');
        post('delete', {id: $(this).data('id')}, function(){
            $item.fadeOut(160, function(){ $(this).remove(); if (!$('.mfp-item').length) location.reload(); });
        });
    });
})();
</script>
