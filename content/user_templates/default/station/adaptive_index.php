<?php
defined('DC_ROOT') || exit('access denied!');

// 获取当前模板
?>

<?php
$stationName = !empty($userData['station']['name']) ? $userData['station']['name'] : (!empty($station['name']) ? $station['name'] : '未命名店铺');
$stationTitle = !empty($userData['station']['title']) ? $userData['station']['title'] : '';
$stationDomain = !empty($userData['station']['domain']) ? $userData['station']['domain'] : '';
$stationSubDomain = (!empty($userData['station']['domain_2_prefix']) && !empty($userData['station']['domain_2_suffix'])) ? $userData['station']['domain_2_prefix'] . $userData['station']['domain_2_suffix'] : '';
$stationSlug = !empty($userData['station']['slug']) ? $userData['station']['slug'] : '';
if ($stationSlug === 'NULL') $stationSlug = '';
$stationShareLink = $stationSlug !== '' ? rtrim(Option::get('blogurl'), '/') . '/s/' . $stationSlug : '';
$stationCreateTime = !empty($userData['station']['create_time']) ? intval($userData['station']['create_time']) : 0;
$stationOpenDays = $stationCreateTime > 0 ? max(1, intval(floor((time() - $stationCreateTime) / 86400)) + 1) : 0;
$averageOrderAmount = $total_orders > 0 ? round($total_amount / $total_orders, 2) : 0;
$userOrderRatio = $total_users > 0 ? round($total_orders / $total_users, 2) : 0;
$monthOrderRatio = $month_users > 0 ? round($month_orders / $month_users, 2) : 0;
?>

<style>
    /* ===== 店铺概览页 ===== */
    .station-overview-page {
        display: flex;
        flex-direction: column;
        gap: 22px;
        padding: 8px 0 18px;
    }

    /* Hero */
    .station-overview-hero {
        padding: 24px 28px;
        border-radius: 10px;
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        box-shadow: 0 1px 18px #12345b0a;
    }

    .station-overview-hero-content {
        display: grid;
        grid-template-columns: minmax(0,1fr) auto;
        gap: 18px;
        align-items: center;
    }
    .station-overview-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        background: rgba(var(--tp-rgb),.06);
        color: var(--theme-primary);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .08em;
    }
    .station-overview-title {
        margin: 14px 0 10px;
        color: #0f172a;
        font-size: 22px;
        line-height: 1.2;
        font-weight: 800;
    }
    .station-overview-desc {
        max-width: 760px;
        margin: 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.9;
    }
    .station-overview-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 10px;
    }
    .station-overview-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-width: 110px;
        min-height: 42px;
        padding: 0 18px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #475569;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: .18s ease;
    }
    .station-overview-btn:hover {
        color: #1e293b;
        text-decoration: none;
        border-color: #cbd5e1;
        box-shadow: 0 2px 8px rgba(15,23,42,.06);
    }
    .station-overview-btn.is-primary {
        background: var(--theme-primary);
        border-color: var(--theme-primary);
        color: #fff;
    }
    .station-overview-btn.is-primary:hover { background: var(--tp-dark); border-color: var(--tp-dark); color: #fff; box-shadow: 0 4px 14px rgba(var(--tp-rgb),.25); }

    /* 指标卡片 */
    .station-overview-metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(0,1fr));
        gap: 16px;
    }
    .station-overview-metric {
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        box-shadow: 0 1px 18px #12345b0a;
        border-radius: 10px;
        padding: 22px;
        transition: transform .2s, box-shadow .2s;
    }
    .station-overview-metric:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(var(--tp-rgb),.1);
    }
    .station-overview-metric-label {
        color: var(--text-sub, #6b7280);
        font-size: 13px;
        font-weight: 600;
    }
    .station-overview-metric-value {
        margin-top: 10px;
        color: var(--text-main, #1f2937);
        font-size: 26px;
        font-weight: 800;
        line-height: 1.25;
    }
    .station-overview-metric-note {
        margin-top: 8px;
        color: var(--text-sub, #6b7280);
        font-size: 12px;
        line-height: 1.7;
    }

    /* 等级卡片 */
    .level-card {
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        box-shadow: 0 1px 18px #12345b0a;
        border-radius: 10px;
        padding: 22px 24px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }
    .level-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 999px;
        background: linear-gradient(135deg,#f59e0b,#f97316);
        color: #fff;
        font-size: 14px;
        font-weight: 700;
    }
    .level-badge-icon {
        width: 24px; height: 24px; margin-left: -7px; border-radius: 999px;
        display: inline-flex; align-items: center; justify-content: center;
        background: rgba(255,255,255,.18); overflow: hidden; flex-shrink: 0;
    }
    .level-badge-icon img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .level-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 12px;
    }
    .level-perm-tag {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }
    .level-perm-tag.on {
        background: rgba(16,185,129,.1);
        color: #059669;
    }
    .level-perm-tag.off {
        background: rgba(156,163,175,.1);
        color: #9ca3af;
        text-decoration: line-through;
    }
    .level-upgrade-area {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 8px;
        flex-shrink: 0;
    }
    .level-next {
        font-size: 13px;
        color: var(--text-sub, #6b7280);
    }
    .btn-lv-upgrade {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 18px;
        border-radius: 10px;
        background: linear-gradient(135deg,var(--theme-primary),var(--tp-light));
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(var(--tp-rgb),.25);
        transition: transform .18s, box-shadow .18s;
    }
    .btn-lv-upgrade:hover {
        color: #fff;
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(var(--tp-rgb),.35);
    }

    /* 自动升级进度 */
    .level-auto-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-main, #1f2937);
        margin-bottom: 14px;
    }
    .level-auto-mode {
        font-size: 12px;
        font-weight: 500;
        color: var(--text-sub, #6b7280);
    }
    .level-auto-items { display:flex; flex-direction:column; gap:10px; }
    .level-auto-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .level-auto-label {
        min-width: 60px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-sub, #6b7280);
    }
    .level-auto-bar {
        flex: 1;
        height: 8px;
        background: #f1f5f9;
        border-radius: 999px;
        overflow: hidden;
    }
    .level-auto-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg,var(--tp-light),var(--theme-primary));
        transition: width .4s ease;
    }
    .level-auto-num {
        min-width: 120px;
        text-align: right;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-main, #1f2937);
    }

    /* 数据面板双栏 */
    .station-overview-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0,1fr));
        gap: 16px;
    }
    .station-overview-panel {
        background: var(--pc-card-bg);
        border: 2px solid #fff;
        box-shadow: 0 1px 18px #12345b0a;
        border-radius: 10px;
        overflow: hidden;
    }
    .station-overview-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 22px 0;
    }
    .station-overview-panel-title {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: var(--text-main, #1f2937);
    }
    .station-overview-list {
        padding: 10px 0;
    }
    .station-overview-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 22px;
        transition: background .15s;
    }
    .station-overview-item:hover {
        background: rgba(var(--tp-rgb),.03);
    }
    .station-overview-item-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-sub, #6b7280);
    }
    .station-overview-item-value {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-main, #1f2937);
        text-align: right;
    }
    .station-overview-link {
        color: var(--theme-primary);
        text-decoration: none;
        font-weight: 700;
    }
    .station-overview-link:hover {
        text-decoration: underline;
    }
    .station-overview-empty {
        color: #9ca3af;
        font-size: 12px;
        font-style: italic;
    }

    .station-snapshot-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0,1fr));
        gap: 12px;
        padding: 14px 18px 18px;
    }
    .station-snapshot-card {
        border: 1px solid rgba(var(--tp-rgb),.08);
        border-radius: 10px;
        background: rgba(255,255,255,.72);
        padding: 14px 16px;
    }
    .station-snapshot-label {
        color: var(--text-sub, #6b7280);
        font-size: 12px;
        font-weight: 600;
    }
    .station-snapshot-value {
        margin-top: 8px;
        color: var(--text-main, #1f2937);
        font-size: 22px;
        font-weight: 800;
        line-height: 1.25;
    }
    .station-snapshot-sub {
        margin-top: 6px;
        color: var(--text-sub, #6b7280);
        font-size: 12px;
        line-height: 1.6;
    }
    .station-snapshot-money {
        color: #059669;
    }

    /* 响应式 */
    @media (max-width: 768px) {
        .station-overview-hero { padding:24px 20px; }
        .station-overview-hero-content { grid-template-columns:1fr; }
        .station-overview-actions { justify-content:flex-start; }
        .station-overview-title { font-size:24px; }
        .station-overview-metrics { grid-template-columns:1fr; }
        .station-overview-grid { grid-template-columns:1fr; }
        .level-card { flex-direction:column; }
        .level-upgrade-area { align-items:flex-start; }
    }
</style>

<main class="station-overview-page">
    <section class="station-overview-hero">
        <div class="station-overview-hero-content">
            <div>
                <h1 class="station-overview-title">店铺名：<?= htmlspecialchars($stationName) ?></h1>
                <p class="station-overview-desc">
                    <?php if ($stationOpenDays > 0): ?>您已运营 <?= $stationOpenDays ?> 天 · <?php endif; ?>今日 <?= $today_orders ?> 单 · 佣金 ¥<?= number_format($today_amount, 2) ?><?php if ($total_amount > 0): ?> · 累计佣金 ¥<?= number_format($total_amount, 2) ?><?php endif; ?>
                </p>
            </div>
            <div class="station-overview-actions">
                <a class="station-overview-btn is-primary" href="/user/station.php?action=setting"><i class="fa fa-cog"></i> 店铺配置</a>
                <a class="station-overview-btn" href="/user/station.php?action=master_goods"><i class="fa fa-cube"></i> 商品管理</a>
            </div>
        </div>
    </section>

    <!-- 分店等级卡 -->
    <?php if (!empty($currentStationLevel)): ?>
    <?php
        $_stationLevelIcon = trim((string)($currentStationLevel['icon'] ?? ''));
        if ($_stationLevelIcon === '') $_stationLevelIcon = 'ri-store-2-line';
        $_stationLevelIconImage = trim((string)($currentStationLevel['icon_image'] ?? ''));
    ?>
    <section class="level-card">
        <div>
            <div class="level-badge"><span class="level-badge-icon"><?= $_stationLevelIconImage !== '' ? '<img src="' . htmlspecialchars($_stationLevelIconImage) . '" alt="">' : '<i class="' . htmlspecialchars($_stationLevelIcon) . '"></i>' ?></span> <?= htmlspecialchars($currentStationLevel['name']) ?></div>
            <?php if (!empty($currentStationLevel['description'])): ?>
                <div style="margin-top:8px;font-size:13px;color:var(--text-sub);line-height:1.7;"><?= htmlspecialchars($currentStationLevel['description']) ?></div>
            <?php endif; ?>
            <div class="level-meta">
                <?php foreach (Station_Model::PERM_MAP as $f => $l): ?>
                    <span class="level-perm-tag <?= ($currentStationLevel[$f] ?? 'n') === 'y' ? 'on' : 'off' ?>"><?= $l ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="level-upgrade-area">
            <?php if ($nextUpgradeLevel): ?>
                <div class="level-next">下一级：<?= htmlspecialchars($nextUpgradeLevel['name']) ?>（<?= ($stationPriceMode ?? 'diff') === 'full' ? '' : '补 ' ?>¥<?= number_format($nextUpgradeLevel['diff_price'], 2) ?>）</div>
                <a href="/user/station.php?action=open" class="btn-lv-upgrade"><i class="fa fa-rocket"></i> 升级分店</a>
            <?php else: ?>
                <div class="level-next" style="color:#10b981;font-weight:600;"><i class="fa fa-check-circle"></i> 已达最高等级</div>
            <?php endif; ?>
        </div>
    </section>
    <?php if ($nextUpgradeLevel && !empty($nextUpgradeLevel['has_auto_upgrade'])): ?>
    <section style="background:var(--pc-card-bg);border:2px solid #fff;box-shadow:0 1px 18px #12345b0a;border-radius:10px;padding:20px 24px;">
        <div class="level-auto-title"><i class="fa fa-arrow-up"></i> 下一级「<?= htmlspecialchars($nextUpgradeLevel['name']) ?>」自动升级进度 <span class="level-auto-mode">（<?= htmlspecialchars($nextUpgradeLevel['upgrade_mode_text']) ?>）</span></div>
        <div class="level-auto-items">
            <?php
            $conditions = [
                ['key'=>'upgrade_sales_amount','stat'=>'sales','label'=>'销售额','unit'=>'元','fmt'=>true],
                ['key'=>'upgrade_order_count','stat'=>'orders','label'=>'订单量','unit'=>'单','fmt'=>false],
                ['key'=>'upgrade_days','stat'=>'days','label'=>'运营天数','unit'=>'天','fmt'=>false],
                ['key'=>'upgrade_sub_count','stat'=>'subs','label'=>'下级分店','unit'=>'店','fmt'=>false],
            ];
            foreach ($conditions as $c):
                $target = $c['fmt'] ? (float)($nextUpgradeLevel[$c['key']] ?? 0) : (int)($nextUpgradeLevel[$c['key']] ?? 0);
                if ($target <= 0) continue;
                $current = $c['fmt'] ? (float)($myStats[$c['stat']] ?? 0) : (int)($myStats[$c['stat']] ?? 0);
                $pct = min(100, round($current / $target * 100));
                $done = $current >= $target;
            ?>
            <div class="level-auto-row">
                <span class="level-auto-label"><?= $c['label'] ?></span>
                <div class="level-auto-bar"><div class="level-auto-fill" style="width:<?= $pct ?>%<?= $done ? ';background:linear-gradient(90deg,#10b981,#059669)' : '' ?>"></div></div>
                <span class="level-auto-num"><?= $c['fmt'] ? number_format($current,2) : $current ?> / <?= $c['fmt'] ? number_format($target,2) : $target ?><?= $c['unit'] ?><?= $done ? ' ✓' : '' ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    <?php endif; ?>

    <section class="station-overview-grid">
        <div class="station-overview-panel">
            <div class="station-overview-panel-head">
                <div>
                    <h3 class="station-overview-panel-title">运营快照</h3>
                </div>
            </div>
            <div class="station-snapshot-grid">
                <div class="station-snapshot-card">
                    <div class="station-snapshot-label">今日订单</div>
                    <div class="station-snapshot-value"><?= (int)$today_orders ?> 单</div>
                    <div class="station-snapshot-sub">昨日 <?= (int)$yesterday_orders ?> 单</div>
                </div>
                <div class="station-snapshot-card">
                    <div class="station-snapshot-label">今日佣金</div>
                    <div class="station-snapshot-value station-snapshot-money">¥<?= number_format($today_amount, 2) ?></div>
                    <div class="station-snapshot-sub">昨日 ¥<?= number_format($yesterday_amount, 2) ?></div>
                </div>
                <div class="station-snapshot-card">
                    <div class="station-snapshot-label">直推粉丝</div>
                    <div class="station-snapshot-value"><?= (int)$total_users ?> 人</div>
                    <div class="station-snapshot-sub">今日 <?= (int)$today_users ?> 人 · 本月 <?= (int)$month_users ?> 人</div>
                </div>
                <div class="station-snapshot-card">
                    <div class="station-snapshot-label">累计佣金</div>
                    <div class="station-snapshot-value station-snapshot-money">¥<?= number_format($total_amount, 2) ?></div>
                    <div class="station-snapshot-sub">本月 ¥<?= number_format($month_amount, 2) ?> · 昨日 ¥<?= number_format($yesterday_amount, 2) ?></div>
                </div>
            </div>
        </div>

        <div class="station-overview-panel">
            <div class="station-overview-panel-head">
                <div>
                    <h3 class="station-overview-panel-title">店铺信息</h3>
                </div>
            </div>
            <div class="station-overview-list">
                <div class="station-overview-item">
                    <div class="station-overview-item-label">独立域名</div>
                    <div class="station-overview-item-value">
                        <?php if(empty($stationDomain)): ?>
                            <span class="station-overview-empty">未配置</span>
                        <?php else: ?>
                            <a href="//<?= htmlspecialchars($stationDomain) ?>" class="station-overview-link" target="_blank"><?= htmlspecialchars($stationDomain) ?></a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="station-overview-item">
                    <div class="station-overview-item-label">二级域名</div>
                    <div class="station-overview-item-value">
                        <?php if(empty($stationSubDomain)): ?>
                            <span class="station-overview-empty">未配置</span>
                        <?php else: ?>
                            <a href="//<?= htmlspecialchars($stationSubDomain) ?>" class="station-overview-link" target="_blank"><?= htmlspecialchars($stationSubDomain) ?></a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="station-overview-item">
                    <div class="station-overview-item-label">店铺标识</div>
                    <div class="station-overview-item-value">
                        <?php if(empty($stationShareLink)): ?>
                            <span class="station-overview-empty">未生成</span>
                        <?php else: ?>
                            <a href="<?= htmlspecialchars($stationShareLink) ?>" class="station-overview-link" target="_blank"><?= htmlspecialchars($stationShareLink) ?></a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="station-overview-item">
                    <div class="station-overview-item-label">店铺名称</div>
                    <div class="station-overview-item-value">
                        <?php if(empty($stationName)): ?>
                            <span class="station-overview-empty">未配置</span>
                        <?php else: ?>
                            <?= htmlspecialchars($stationName) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="station-overview-item">
                    <div class="station-overview-item-label">网站标题</div>
                    <div class="station-overview-item-value">
                        <?php if(empty($stationTitle)): ?>
                            <span class="station-overview-empty">未配置</span>
                        <?php else: ?>
                            <?= htmlspecialchars($stationTitle) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="station-overview-item">
                    <div class="station-overview-item-label">开通时间</div>
                    <div class="station-overview-item-value"><?= $stationCreateTime > 0 ? date('Y-m-d H:i', $stationCreateTime) : '暂无记录' ?></div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../_pc_page_footer.php'; ?>
<script>
    $('#menu-station').addClass('menu-current');
</script>