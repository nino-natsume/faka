<?php defined('DC_ROOT') || exit('access denied!'); ?>

<style>
.calibrate-wrapper {
    max-width: 800px;
    margin: 20px auto;
    padding: 0 20px;
}

.calibrate-card {
    background: linear-gradient(0deg,#fff,#f3f5f8);
    border: 2px solid #fff;
    border-radius: 6px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    overflow: hidden;
    margin-bottom: 20px;
}

.calibrate-card .card-header {
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    height: auto;
}
.mac-dots { position:absolute;left:15px;top:50%;transform:translateY(-50%);display:flex;gap:6px; }
.mac-dots i { width:12px;height:12px;border-radius:50%;display:inline-block; }
.mac-dots .dot-r { background:#ff5f57; }
.mac-dots .dot-y { background:#febc2e; }
.mac-dots .dot-g { background:#28c840; }
.card-header .card-extra { position:absolute;right:15px;top:50%;transform:translateY(-50%);font-size:12px;color:#94A3B8; }

.calibrate-card .card-title {
    font-size: 14px;
    font-weight: 500;
    color: #667797;
}

.calibrate-card .card-body {
    padding: 25px;
}

.calibrate-hero {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 20px;
}

.calibrate-hero-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #E0F2FE, #BAE6FD);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.calibrate-hero-icon i {
    font-size: 24px;
    color: #0284C7;
}

.calibrate-hero-title {
    font-size: 15px;
    font-weight: 600;
    color: #1E293B;
    margin-bottom: 6px;
}

.calibrate-hero-desc {
    font-size: 13px;
    color: #64748B;
    line-height: 1.8;
}

.calibrate-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    height: 44px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.calibrate-btn.primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
}

.calibrate-btn.primary:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

.calibrate-btn.secondary {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}

.calibrate-btn.secondary:hover {
    background: #e2e8f0;
}

.calibrate-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* 警告框 */
.calibrate-warning {
    background: #FEF2F2;
    border: 1px solid #FECACA;
    border-radius: 8px;
    padding: 14px 18px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.calibrate-warning i {
    color: #DC2626;
    font-size: 20px;
    flex-shrink: 0;
}

.calibrate-warning-text {
    font-size: 13px;
    color: #991B1B;
    line-height: 1.6;
}

/* 备份列表 */
.backup-list {
    width: 100%;
}

.backup-empty {
    text-align: center;
    padding: 40px 20px;
    color: #94A3B8;
}

.backup-empty i {
    font-size: 36px;
    margin-bottom: 10px;
    display: block;
}

.backup-item {
    display: flex;
    align-items: center;
    padding: 14px 0;
    border-bottom: 1px solid #f1f5f9;
    gap: 12px;
}

.backup-item:last-child {
    border-bottom: none;
}

.backup-item-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: #F1F5F9;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.backup-item-icon i {
    font-size: 18px;
    color: #64748B;
}

.backup-item-info {
    flex: 1;
    min-width: 0;
}

.backup-item-name {
    font-size: 13px;
    font-weight: 500;
    color: #1E293B;
    word-break: break-all;
}

.backup-item-meta {
    font-size: 12px;
    color: #94A3B8;
    margin-top: 2px;
}

.backup-item-actions {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
}

.backup-item-actions button {
    padding: 5px 12px;
    border-radius: 6px;
    font-size: 12px;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
}

.backup-item-actions .btn-restore {
    background: #DBEAFE;
    color: #1D4ED8;
}

.backup-item-actions .btn-restore:hover {
    background: #BFDBFE;
}

.backup-item-actions .btn-delete {
    background: #FEE2E2;
    color: #DC2626;
}

.backup-item-actions .btn-delete:hover {
    background: #FECACA;
}

/* 深色模式 */
html[data-theme="dark"] .calibrate-card {
    background: linear-gradient(0deg, #1a1a1a, #222);
    border-color: #333;
}

html[data-theme="dark"] .calibrate-card .card-header {
    border-color: #333;
}

html[data-theme="dark"] .calibrate-card .card-title {
    color: #e0e0e0;
}

html[data-theme="dark"] .calibrate-hero-title {
    color: #e0e0e0;
}

html[data-theme="dark"] .calibrate-hero-desc {
    color: #888;
}

html[data-theme="dark"] .calibrate-btn.secondary {
    background: #333;
    color: #b0b0b0;
    border-color: #444;
}

html[data-theme="dark"] .backup-item {
    border-color: #333;
}

html[data-theme="dark"] .backup-item-name {
    color: #e0e0e0;
}

html[data-theme="dark"] .backup-item-icon {
    background: #2a2a2a;
}

html[data-theme="dark"] .backup-empty {
    color: #555;
}
</style>

<div class="calibrate-wrapper">
    <!-- 文件校准 -->
    <div class="calibrate-card">
        <div class="card-header">
            <span class="mac-dots"><i class="dot-r"></i><i class="dot-y"></i><i class="dot-g"></i></span>
            <span class="card-title">文件校准</span>
        </div>
        <div class="card-body">
            <div class="calibrate-hero">
                <div class="calibrate-hero-icon">
                    <i class="ri-file-shield-2-line"></i>
                </div>
                <div>
                    <div class="calibrate-hero-title">程序文件完整性校准</div>
                    <div class="calibrate-hero-desc">
                        此功能可以确保程序核心文件的完整性，防止被第三方插件或其他原因修改后导致无法正常使用。<br>
                        校准时会自动从官方服务器下载原始文件并覆盖，<strong>校准前会自动备份当前文件</strong>，您可以随时恢复。
                    </div>
                </div>
            </div>

            <?php if (defined('DC_LICENSE_TAMPERED')): ?>
            <div class="calibrate-warning">
                <i class="ri-error-warning-fill"></i>
                <div class="calibrate-warning-text">
                    <strong>检测到核心文件异常</strong> — 部分系统文件完整性校验失败，可能已被第三方修改或损坏，部分功能已受限。请立即执行文件校准修复。
                </div>
            </div>
            <?php endif; ?>

            <div id="calibrateResult"></div>

            <?php if ($authResult['authorized']): ?>
            <button type="button" class="calibrate-btn <?= defined('DC_LICENSE_TAMPERED') ? 'primary' : 'secondary' ?>" id="btnCalibrate" onclick="doCalibrate()">
                <i class="ri-shield-check-line"></i> <?= defined('DC_LICENSE_TAMPERED') ? '立即校准修复' : '执行文件校准' ?>
            </button>
            <?php else: ?>
            <div style="text-align:center;color:#999;padding:10px;">
                <i class="ri-lock-line" style="font-size:20px;display:block;margin-bottom:6px;"></i>
                请先完成授权验证后才能使用文件校准功能
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 备份记录 -->
    <div class="calibrate-card">
        <div class="card-header">
            <span class="mac-dots"><i class="dot-r"></i><i class="dot-y"></i><i class="dot-g"></i></span>
            <span class="card-title">备份记录</span>
            <span class="card-extra">共 <?= count($backups) ?> 条记录</span>
        </div>
        <div class="card-body">
            <div class="backup-list" id="backupList">
                <?php if (empty($backups)): ?>
                <div class="backup-empty">
                    <i class="ri-folder-open-line"></i>
                    暂无备份记录
                </div>
                <?php else: ?>
                <?php foreach ($backups as $bk): ?>
                <div class="backup-item" data-filename="<?= htmlspecialchars($bk['name']) ?>">
                    <div class="backup-item-icon">
                        <i class="ri-file-zip-line"></i>
                    </div>
                    <div class="backup-item-info">
                        <div class="backup-item-name"><?= htmlspecialchars($bk['name']) ?></div>
                        <div class="backup-item-meta">
                            <?= date('Y-m-d H:i:s', $bk['time']) ?> · <?= $bk['size'] >= 1048576 ? round($bk['size'] / 1048576, 2) . ' MB' : round($bk['size'] / 1024, 1) . ' KB' ?>
                        </div>
                    </div>
                    <div class="backup-item-actions">
                        <button class="btn-restore" onclick="restoreBackup('<?= htmlspecialchars($bk['name']) ?>')">
                            <i class="ri-arrow-go-back-line"></i> 恢复
                        </button>
                        <button class="btn-delete" onclick="deleteBackup('<?= htmlspecialchars($bk['name']) ?>')">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
var CALIBRATE_TOKEN = '<?= LoginAuth::genToken() ?>';

// 文件校准（异步下载 + 轮询进度 + 解压覆盖）
function doCalibrate() {
    layer.confirm('确定要执行文件校准吗？<br><br><span style="color:#666;font-size:13px;">系统将从官方服务器下载原始文件并覆盖本地文件，校准前会自动备份当前文件。</span>', {
        icon: 3,
        title: '文件校准确认',
        btn: ['确定校准', '取消']
    }, function(index) {
        layer.close(index);
        startCalibrate();
    });
}

function startCalibrate() {
    var btn = document.getElementById('btnCalibrate');
    var result = document.getElementById('calibrateResult');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="ri-loader-4-line dc-spin"></i> 正在下载安装包...';
    result.innerHTML = '';
    
    // 步骤1: 发起异步下载
    $.ajax({
        url: 'ajax.php?action=calibrate_files',
        type: 'POST',
        data: { token: CALIBRATE_TOKEN },
        dataType: 'json',
        timeout: 30000,
        success: function(res) {
            if (res.code !== 0) {
                resetCalibrateBtn();
                showCalibrateError(res.msg || '请求失败');
                return;
            }
            // 开始轮询下载进度
            pollCalibrateProgress();
        },
        error: function(xhr) {
            resetCalibrateBtn();
            var msg = '网络请求失败';
            try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e) {}
            if (xhr.status) msg += ' (HTTP ' + xhr.status + ')';
            showCalibrateError(msg);
        }
    });
}

var _calibratePollTimer = null;
var _calibratePollCount = 0;

function pollCalibrateProgress() {
    _calibratePollCount = 0;
    if (_calibratePollTimer) clearInterval(_calibratePollTimer);
    _calibratePollTimer = setInterval(function() {
        _calibratePollCount++;
        if (_calibratePollCount > 120) {
            clearInterval(_calibratePollTimer);
            resetCalibrateBtn();
            showCalibrateError('下载超时，请重试');
            return;
        }
        
        $.ajax({
            url: 'ajax.php?action=calibrate_progress',
            type: 'GET',
            dataType: 'json',
            timeout: 5000,
            success: function(res) {
                if (res.code !== 0) return;
                var task = res.data || {};
                var btn = document.getElementById('btnCalibrate');
                
                if (task.status === 'completed') {
                    clearInterval(_calibratePollTimer);
                    var sizeMB = task.size ? (task.size / 1024 / 1024).toFixed(1) + 'MB' : '';
                    btn.innerHTML = '<i class="ri-loader-4-line dc-spin"></i> 下载完成' + (sizeMB ? '(' + sizeMB + ')' : '') + '，正在校准文件...';
                    doCalibrateApply();
                } else if (task.status === 'failed') {
                    clearInterval(_calibratePollTimer);
                    resetCalibrateBtn();
                    showCalibrateError('下载失败: ' + (task.error || '未知错误'));
                } else if (task.status === 'expired') {
                    clearInterval(_calibratePollTimer);
                    resetCalibrateBtn();
                    showCalibrateError('下载超时，请重试');
                } else if (task.status === 'downloading') {
                    var pct = task.dl_percent || 0;
                    var txt = '正在下载安装包... ' + pct + '%';
                    if (task.dl_total > 0) {
                        var dlMB = (task.dl_now / 1024 / 1024).toFixed(1);
                        var totalMB = (task.dl_total / 1024 / 1024).toFixed(1);
                        txt = '正在下载安装包... ' + pct + '%（' + dlMB + '/' + totalMB + 'MB）';
                    }
                    btn.innerHTML = '<i class="ri-loader-4-line dc-spin"></i> ' + txt;
                }
            }
        });
    }, 2000);
}

// 步骤2: 解压覆盖
function doCalibrateApply() {
    $.ajax({
        url: 'ajax.php?action=calibrate_apply',
        type: 'POST',
        data: { token: CALIBRATE_TOKEN },
        dataType: 'json',
        timeout: 120000,
        success: function(res) {
            resetCalibrateBtn();
            if (res.code === 0) {
                var result = document.getElementById('calibrateResult');
                var failedHtml = '';
                if (res.data.failed > 0) {
                    var files = (res.data.failed_files || []).join('<br>');
                    failedHtml = '<div style="background:#FEF3C7;border:1px solid #FDE68A;border-radius:6px;padding:10px 14px;margin-top:10px;font-size:12px;color:#92400E;">' +
                        '<strong>⚠ ' + res.data.failed + ' 个文件覆盖失败（不影响主要功能）：</strong><br>' +
                        '<code style="font-size:11px;line-height:1.8;">' + files + '</code></div>';
                }
                result.innerHTML = '<div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:8px;padding:16px 20px;margin-bottom:20px;">' +
                    '<div style="display:flex;align-items:flex-start;gap:12px;">' +
                    '<i class="ri-checkbox-circle-fill" style="color:#16A34A;font-size:22px;flex-shrink:0;margin-top:1px;"></i>' +
                    '<div style="flex:1;">' +
                    '<div style="font-size:15px;font-weight:600;color:#166534;margin-bottom:6px;">文件校准完成</div>' +
                    '<div style="font-size:13px;color:#15803D;line-height:1.8;">' +
                    '已成功覆盖 ' + (res.data.updated || 0) + ' 个文件，备份文件：<code style="background:#DCFCE7;padding:1px 5px;border-radius:3px;">' + (res.data.backup || '') + '</code><br>' +
                    '页面将在 3 秒后自动刷新...' +
                    '</div>' + failedHtml + '</div></div></div>';
                setTimeout(function() { location.reload(); }, 3000);
            } else {
                showCalibrateError(res.msg || '校准失败，请稍后重试');
            }
        },
        error: function(xhr) {
            resetCalibrateBtn();
            var msg = '校准请求失败';
            try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e) {}
            if (xhr.status) msg += ' (HTTP ' + xhr.status + ')';
            showCalibrateError(msg);
        }
    });
}

function resetCalibrateBtn() {
    var btn = document.getElementById('btnCalibrate');
    btn.disabled = false;
    btn.innerHTML = '<i class="ri-shield-check-line"></i> 执行文件校准';
}

function showCalibrateError(msg) {
    document.getElementById('calibrateResult').innerHTML = '<div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:8px;padding:16px 20px;margin-bottom:20px;">' +
        '<div style="display:flex;align-items:center;gap:10px;">' +
        '<i class="ri-close-circle-fill" style="color:#DC2626;font-size:20px;flex-shrink:0;"></i>' +
        '<div style="font-size:13px;color:#991B1B;">' + msg + '</div>' +
        '</div></div>';
}

// 恢复备份
function restoreBackup(filename) {
    layer.confirm('确定要恢复此备份吗？<br><br><span style="color:#666;font-size:13px;">当前的系统核心文件将被此备份文件覆盖。</span>', {
        icon: 3,
        title: '恢复备份确认',
        btn: ['确定恢复', '取消']
    }, function(index) {
        layer.close(index);
        var loadIdx = layer.load(2);
        
        $.ajax({
            url: 'ajax.php?action=restore_calibrate_backup',
            type: 'POST',
            data: { filename: filename, token: CALIBRATE_TOKEN },
            dataType: 'json',
            timeout: 120000,
            success: function(res) {
                layer.close(loadIdx);
                if (res.code === 0) {
                    var msg = '已恢复 ' + (res.data.updated || 0) + ' 个文件';
                    if (res.data.failed > 0) {
                        msg += '，' + res.data.failed + ' 个文件恢复失败';
                    }
                    layer.msg(msg + '，页面即将刷新', {icon: 1, time: 2000});
                    setTimeout(function() { location.reload(); }, 2000);
                } else {
                    layer.msg(res.msg || '恢复失败', {icon: 2});
                }
            },
            error: function(xhr) {
                layer.close(loadIdx);
                var msg = '请求失败';
                try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e) {}
                layer.msg(msg, {icon: 2});
            }
        });
    });
}

// 删除备份
function deleteBackup(filename) {
    layer.confirm('确定要删除此备份文件吗？<br><span style="color:#999;font-size:13px;">删除后将无法恢复。</span>', {
        icon: 3,
        title: '删除确认',
        btn: ['确定删除', '取消']
    }, function(index) {
        layer.close(index);
        var loadIdx = layer.load(2);
        
        $.ajax({
            url: 'ajax.php?action=delete_calibrate_backup',
            type: 'POST',
            data: { filename: filename, token: CALIBRATE_TOKEN },
            dataType: 'json',
            timeout: 30000,
            success: function(res) {
                layer.close(loadIdx);
                if (res.code === 0) {
                    // 移除 DOM 元素
                    var item = document.querySelector('.backup-item[data-filename="' + filename + '"]');
                    if (item) {
                        item.style.transition = 'all 0.3s';
                        item.style.opacity = '0';
                        item.style.height = '0';
                        item.style.padding = '0';
                        item.style.overflow = 'hidden';
                        setTimeout(function() { item.remove(); updateBackupCount(); }, 300);
                    }
                    layer.msg('备份已删除', {icon: 1});
                } else {
                    layer.msg(res.msg || '删除失败', {icon: 2});
                }
            },
            error: function(xhr) {
                layer.close(loadIdx);
                var msg = '请求失败';
                try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e) {}
                layer.msg(msg, {icon: 2});
            }
        });
    });
}

function updateBackupCount() {
    var items = document.querySelectorAll('.backup-item');
    var countEl = document.querySelector('.calibrate-card:last-child .card-header div:last-child');
    if (countEl) countEl.textContent = '共 ' + items.length + ' 条记录';
    if (items.length === 0) {
        document.getElementById('backupList').innerHTML = '<div class="backup-empty"><i class="ri-folder-open-line"></i>暂无备份记录</div>';
    }
}

// 菜单高亮
$("#menu-system").addClass('open');
$("#menu-system > ul").css('display', 'block');
$("#menu-system > .link > .admin-arrow").addClass('active');
$("#menu-calibrate").addClass('active');
</script>
