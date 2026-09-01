<?php
defined('DC_ROOT') || exit('access denied!');

$footprints = isset($footprintList) && is_array($footprintList) ? $footprintList : [];
$summary = isset($footprintSummary) && is_array($footprintSummary) ? $footprintSummary : ['total' => 0, 'total_views' => 0, 'last_time' => 0];
$page = isset($page) ? max(1, (int)$page) : 1;
$totalPages = isset($totalPages) ? max(1, (int)$totalPages) : 1;
$footprintToken = isset($footprintToken) ? $footprintToken : LoginAuth::genToken();

$fpCover = function($cover) {
    $cover = trim((string)$cover);
    if ($cover === '') {
        return DC_URL . 'admin/views/images/cover.svg';
    }
    if (preg_match('#^(https?:)?//#i', $cover)) {
        return $cover;
    }
    return rtrim(DC_URL, '/') . '/' . ltrim($cover, '/');
};
$fpText = function($text, $len = 54) {
    $text = trim(strip_tags(html_entity_decode((string)$text, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    if ($text === '') return '这个商品还没有填写简介，去详情页看看更多内容吧。';
    if (function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > $len) {
        return mb_substr($text, 0, $len, 'UTF-8') . '...';
    }
    return strlen($text) > $len * 2 ? substr($text, 0, $len * 2) . '...' : $text;
};
$lastTimeText = !empty($summary['last_time']) ? date('Y-m-d H:i', (int)$summary['last_time']) : '暂无记录';
?>

<style>
.footprint-page { display:flex; flex-direction:column; gap:18px; padding:8px 0 22px; }
.fp-hero { position:relative; overflow:hidden; border-radius:10px; padding:24px 28px; background:var(--pc-card-bg); border:2px solid #fff; box-shadow:0 1px 18px #12345b0a; }
.fp-hero:before { content:""; position:absolute; right:-70px; top:-70px; width:210px; height:210px; border-radius:50%; background:rgba(var(--tp-rgb),.08); }
.fp-hero-inner { position:relative; z-index:1; display:grid; grid-template-columns:minmax(0,1fr) auto; gap:18px; align-items:center; }
.fp-kicker { display:inline-flex; align-items:center; gap:7px; margin-bottom:10px; padding:5px 10px; border-radius:999px; color:var(--theme-primary); background:rgba(var(--tp-rgb),.08); font-size:12px; font-weight:700; }
.fp-title { margin:0; color:var(--text-main,#1f2937); font-size:24px; font-weight:900; letter-spacing:-.4px; }
.fp-subtitle { margin:8px 0 0; color:#64748b; font-size:13px; line-height:1.7; }
.fp-stats { display:grid; grid-template-columns:repeat(3, minmax(98px,1fr)); gap:10px; min-width:360px; }
.fp-stat { padding:13px 14px; border-radius:10px; background:#fff; border:1px solid #eef2f7; box-shadow:0 8px 22px rgba(15,23,42,.04); }
.fp-stat b { display:block; color:#0f172a; font-size:20px; line-height:1.2; }
.fp-stat span { display:block; margin-top:5px; color:#94a3b8; font-size:12px; }
.fp-toolbar { display:flex; align-items:center; justify-content:space-between; gap:14px; padding:14px 16px; border-radius:10px; background:#fff; border:1px solid #eef2f7; box-shadow:0 1px 14px rgba(15,23,42,.04); }
.fp-toolbar-info { color:#64748b; font-size:13px; }
.fp-toolbar-info strong { color:var(--theme-primary); }
.fp-btn { display:inline-flex; align-items:center; justify-content:center; gap:7px; height:36px; padding:0 14px; border-radius:10px; border:1px solid #e2e8f0; background:#fff; color:#64748b; font-size:13px; font-weight:700; cursor:pointer; text-decoration:none; transition:.18s; }
.fp-btn:hover { color:var(--theme-primary); border-color:rgba(var(--tp-rgb),.35); background:rgba(var(--tp-rgb),.04); text-decoration:none; }
.fp-btn.danger { color:#ef4444; border-color:#fee2e2; background:#fff7f7; }
.fp-btn.danger:hover { color:#dc2626; border-color:#fecaca; background:#fee2e2; }
.fp-grid { display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:16px; }
.fp-card { position:relative; overflow:hidden; border-radius:10px; background:#fff; border:1px solid #eef2f7; box-shadow:0 1px 16px rgba(15,23,42,.05); transition:transform .18s, box-shadow .18s, border-color .18s; }
.fp-card:hover { transform:translateY(-2px); box-shadow:0 12px 30px rgba(15,23,42,.09); border-color:rgba(var(--tp-rgb),.22); }
.fp-cover { display:block; position:relative; height:158px; overflow:hidden; background:#f3f4f6; text-decoration:none; }
.fp-cover img { width:100%; height:100%; object-fit:cover; display:block; transition:transform .28s; }
.fp-card:hover .fp-cover img { transform:scale(1.04); }
.fp-badge { position:absolute; left:10px; top:10px; display:inline-flex; align-items:center; gap:5px; padding:5px 9px; border-radius:999px; color:#fff; background:rgba(15,23,42,.72); backdrop-filter:blur(8px); font-size:12px; font-weight:700; }
.fp-badge.is-off { background:rgba(239,68,68,.86); }
.fp-body { padding:14px 14px 12px; }
.fp-name { display:block; color:#0f172a; font-size:15px; font-weight:800; line-height:1.45; text-decoration:none; min-height:42px; }
.fp-name:hover { color:var(--theme-primary); text-decoration:none; }
.fp-desc { margin:8px 0 12px; min-height:38px; color:#64748b; font-size:12px; line-height:1.65; }
.fp-meta { display:flex; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:12px; }
.fp-meta span { display:inline-flex; align-items:center; gap:5px; height:24px; padding:0 8px; border-radius:999px; background:#f8fafc; color:#64748b; font-size:12px; }
.fp-price-row { display:flex; align-items:flex-end; justify-content:space-between; gap:10px; padding-top:11px; border-top:1px dashed #e5e7eb; }
.fp-price { color:#ef4444; font-size:12px; font-weight:700; }
.fp-price strong { font-size:20px; line-height:1; }
.fp-time { color:#94a3b8; font-size:12px; text-align:right; line-height:1.5; }
.fp-actions { display:flex; align-items:center; gap:8px; padding:0 14px 14px; }
.fp-go { flex:1; height:36px; border-radius:10px; border:0; color:#fff; background:linear-gradient(135deg,var(--theme-primary),var(--theme-secondary,var(--tp-dark))); box-shadow:0 8px 18px rgba(var(--tp-rgb),.18); }
.fp-go:hover { color:#fff; background:linear-gradient(135deg,var(--tp-dark),var(--theme-primary)); }
.fp-delete { width:38px; padding:0; flex:0 0 38px; }
.fp-card.is-offline { opacity:.78; }
.fp-card.is-offline .fp-cover img { filter:grayscale(25%); }
.fp-empty { padding:54px 20px; border-radius:10px; background:#fff; border:1px dashed #dbe3ef; text-align:center; color:#94a3b8; }
.fp-empty i { display:block; color:rgba(var(--tp-rgb),.45); font-size:46px; }
.fp-empty h3 { margin:0 0 15px; color:#334155; font-size:18px; }
.fp-empty p { margin:0 0 18px; font-size:13px; }
.fp-pagination { display:flex; align-items:center; justify-content:center; gap:8px; margin-top:4px; }
.fp-page-link { min-width:36px; height:36px; padding:0 12px; display:inline-flex; align-items:center; justify-content:center; border-radius:10px; border:1px solid #e2e8f0; background:#fff; color:#64748b; text-decoration:none; font-size:13px; font-weight:700; }
.fp-page-link:hover, .fp-page-link.is-active { color:#fff; border-color:var(--theme-primary); background:var(--theme-primary); text-decoration:none; }
@media (max-width:1180px){ .fp-grid{grid-template-columns:repeat(2,minmax(0,1fr));} .fp-hero-inner{grid-template-columns:1fr;} .fp-stats{min-width:0;} }
</style>

<main class="footprint-page" id="footprintPage">
    <section class="fp-hero">
        <div class="fp-hero-inner">
            <div>
                <h1 class="fp-title">浏览足迹</h1>
                <p class="fp-subtitle">这里会记录你登录后看过的商品，方便快速找回刚刚心动的宝贝。</p>
            </div>
            <div class="fp-stats">
                <div class="fp-stat"><b><?= (int)($summary['total'] ?? 0) ?></b><span>看过商品</span></div>
                <div class="fp-stat"><b><?= (int)($summary['total_views'] ?? 0) ?></b><span>累计浏览</span></div>
                <div class="fp-stat"><b style="font-size:13px;line-height:1.5;"><?= htmlspecialchars($lastTimeText) ?></b><span>最近浏览</span></div>
            </div>
        </div>
    </section>

    <div class="fp-toolbar">
        <div class="fp-toolbar-info">共 <strong><?= (int)$total ?></strong> 条足迹，按最近浏览时间排序</div>
        <?php if (!empty($footprints)): ?>
        <button type="button" class="fp-btn danger" id="fpClearBtn"><i class="fa fa-trash-o"></i> 清空足迹</button>
        <?php endif; ?>
    </div>

    <?php if (empty($footprints)): ?>
    <div class="fp-empty">
        <i class="ri-footprint-line"></i>
        <p>暂无浏览足迹</p>
        <a class="fp-btn" href="<?= DC_URL ?>"><i class="fa fa-shopping-bag"></i> 去商城逛逛</a>
    </div>
    <?php else: ?>
    <div class="fp-grid">
        <?php foreach ($footprints as $item): ?>
        <?php $available = !empty($item['available']); ?>
        <article class="fp-card<?= $available ? '' : ' is-offline' ?>" data-id="<?= (int)$item['footprint_id'] ?>">
            <a class="fp-cover" href="<?= $available ? htmlspecialchars($item['url']) : 'javascript:void(0);' ?>">
                <img src="<?= htmlspecialchars($fpCover($item['cover'] ?? '')) ?>" alt="<?= htmlspecialchars($item['title']) ?>" onerror="this.src='<?= DC_URL ?>admin/views/images/cover.svg';">
                <span class="fp-badge<?= $available ? '' : ' is-off' ?>"><i class="fa <?= $available ? 'fa-eye' : 'fa-ban' ?>"></i><?= $available ? '浏览 ' . (int)$item['view_count'] . ' 次' : '已失效' ?></span>
            </a>
            <div class="fp-body">
                <a class="fp-name" href="<?= $available ? htmlspecialchars($item['url']) : 'javascript:void(0);' ?>"><?= htmlspecialchars($item['title']) ?></a>
                <div class="fp-desc"><?= htmlspecialchars($fpText($item['des'] ?? '')) ?></div>
                <div class="fp-meta">
                    <span><i class="fa fa-line-chart"></i> 已售 <?= (int)$item['sales'] ?></span>
                    <span><i class="fa fa-cubes"></i> 库存 <?= (int)$item['stock'] ?></span>
                </div>
                <div class="fp-price-row">
                    <div class="fp-price">¥ <strong><?= number_format((float)$item['price'], 2) ?></strong></div>
                    <div class="fp-time">最近浏览<br><?= htmlspecialchars($item['last_view_time_text']) ?></div>
                </div>
            </div>
            <div class="fp-actions">
                <?php if ($available): ?>
                <a class="fp-btn fp-go" href="<?= htmlspecialchars($item['url']) ?>"><i class="fa fa-shopping-cart"></i> 查看商品</a>
                <?php else: ?>
                <span class="fp-btn fp-go" style="background:#cbd5e1;box-shadow:none;cursor:not-allowed;"><i class="fa fa-ban"></i> 暂不可访问</span>
                <?php endif; ?>
                <button type="button" class="fp-btn fp-delete" data-id="<?= (int)$item['footprint_id'] ?>" title="删除"><i class="fa fa-trash-o"></i></button>
            </div>
        </article>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="fp-pagination">
        <?php if ($page > 1): ?><a class="fp-page-link" href="/user/footprint.php?page=<?= $page - 1 ?>">上一页</a><?php endif; ?>
        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
        <a class="fp-page-link<?= $i === $page ? ' is-active' : '' ?>" href="/user/footprint.php?page=<?= $i ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?><a class="fp-page-link" href="/user/footprint.php?page=<?= $page + 1 ?>">下一页</a><?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</main>

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
    $('#fpClearBtn').on('click', function(){
        var run = function(){ post('clear', {}, function(){ location.reload(); }); };
        if (window.layer && layer.confirm) layer.confirm('确定清空全部浏览足迹吗？', {title:'清空确认'}, run);
        else if (confirm('确定清空全部浏览足迹吗？')) run();
    });
    $('.fp-delete').on('click', function(){
        var $card = $(this).closest('.fp-card');
        post('delete', {id: $(this).data('id')}, function(){
            $card.fadeOut(180, function(){ $(this).remove(); if (!$('.fp-card').length) location.reload(); });
        });
    });
})();
</script>
