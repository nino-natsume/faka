// ============================================================
// DCSHOP Cloudflare 部署版 - 公共库
// 提供页面骨架/工具函数，样式 class 与原版模板保持一致
// ============================================================

// ---------- 基础工具 ----------
export function esc(s) {
  if (s === null || s === undefined) return '';
  return String(s)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

export function fen2yuan(fen) {
  const n = Number(fen) || 0;
  return (n / 100).toFixed(2);
}

export function yuan2fen(y) {
  return Math.round((Number(y) || 0) * 100);
}

export function ts2str(ts) {
  if (!ts) return '';
  const t = new Date(Number(ts) * 1000);
  const p = (x) => String(x).padStart(2, '0');
  return t.getFullYear() + '-' + p(t.getMonth() + 1) + '-' + p(t.getDate()) + ' ' + p(t.getHours()) + ':' + p(t.getMinutes());
}

export function now() {
  return Math.floor(Date.now() / 1000);
}

// ---------- HMAC (token 签名，Workers Web Crypto) ----------
export async function hmacSha256(secret, data) {
  const enc = new TextEncoder();
  const key = await crypto.subtle.importKey('raw', enc.encode(secret), { name: 'HMAC', hash: 'SHA-256' }, false, ['sign']);
  const sigBuf = await crypto.subtle.sign('HMAC', key, enc.encode(data));
  return Array.from(new Uint8Array(sigBuf))
    .map((b) => b.toString(16).padStart(2, '0'))
    .join('');
}

export function parseQuery(u) {
  const o = {};
  try {
    const q = (u.split('?')[1] || '').split('#')[0];
    if (!q) return o;
    for (const kv of q.split('&')) {
      if (!kv) continue;
      const i = kv.indexOf('=');
      const k = i >= 0 ? decodeURIComponent(kv.slice(0, i)) : decodeURIComponent(kv);
      const v = i >= 0 ? decodeURIComponent(kv.slice(i + 1)) : '';
      if (k in o) {
        if (!Array.isArray(o[k])) o[k] = [o[k]];
        o[k].push(v);
      } else o[k] = v;
    }
  } catch (e) {}
  return o;
}

// 静态资源前缀（部署后若非根路径可调整）
export const ASSET = (p) => p;

// ---------- 站点选项读取 ----------
export async function getOptions(db) {
  const { results } = await db
    .prepare('SELECT option_name, option_value FROM dc_options')
    .all();
  const m = {};
  for (const r of results) m[r.option_name] = r.option_value;
  return m;
}

export function opt(opts, key, def) {
  const v = opts[key];
  return v === undefined || v === '' ? def : v;
}

// ---------- 商品 & 规格辅助 ----------
export const TYPE_BADGE = {
  duli: 'fk-tb-blue',
  guding: 'fk-tb-pink',
  xuni: 'fk-tb-purple',
  post: 'fk-tb-orange',
  once: 'fk-tb-orange',
  general: 'fk-tb-green',
  service: 'fk-tb-blue',
  physical: 'fk-tb-gray',
};

export const TYPE_NAME = {
  once: '一卡一密',
  general: '通用卡密',
  service: '虚拟服务',
  duli: '独立对接',
  guding: '固定卡密',
  physical: '实物商品',
};

export async function getSkus(db, goodsId) {
  const { results } = await db
    .prepare('SELECT * FROM dc_skus WHERE goods_id = ? ORDER BY id ASC')
    .bind(goodsId)
    .all();
  return results;
}

export async function getSorts(db) {
  const { results } = await db
    .prepare("SELECT * FROM dc_sort WHERE type = 'goods' AND delete_time IS NULL ORDER BY taxis ASC, sid ASC")
    .all();
  return results;
}

// sku 组合串 '1-5' -> 属性值名称 '面值：10元；时长：月卡；'
export async function buildAttrSpec(db, skuStr) {
  if (!skuStr || skuStr === '0') return '';
  const ids = String(skuStr)
    .split('-')
    .filter((x) => x);
  if (!ids.length) return '';
  const placeholders = ids.map(() => '?').join(',');
  const { results } = await db
    .prepare('SELECT v.id, v.name, a.title FROM dc_sku_value v LEFT JOIN dc_sku_attr a ON a.id = v.attr_id WHERE v.id IN (' + placeholders + ')')
    .bind(...ids)
    .all();
  const map = {};
  for (const r of results) map[r.id] = r;
  let s = '';
  for (const id of ids) {
    const r = map[id];
    if (r) s += esc(r.title) + '：' + esc(r.name) + '；';
  }
  return s;
}

// ---------- 面页骨架 (ACG-faka Cartoon 主题) ----------
export function pageHead(opts) {
  const siteName = opts.blogname || opts.shop_name || 'ACG发卡';
  const title = opts.title ? opts.title + ' - ' + siteName : siteName;
  const kw = opts.keywords || '自动发卡,虚拟商品,卡密,ACG';
  const desc = opts.description || '';
  const bg = opt(opts, 'background_url', '') || opt(opts, 'bg_img', '');
  const bgStyle = bg ? `background-size:cover;background-image:linear-gradient(180deg,rgb(255 255 255/0%),rgb(255 255 255/71%)),url('${esc(bg)}');` : 'background:#f4f6fa;';
  return `<!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta name="keywords" content="${esc(kw)}">
<meta name="description" content="${esc(desc)}">
<link rel="icon" href="/favicon.ico">
<title>${esc(title)}</title>
<link href="/assets/common/css/bootstrap.min.css" rel="stylesheet">
<link href="/assets/common/css/_.css" rel="stylesheet">
<link href="/assets/common/css/font.min.css" rel="stylesheet">
<link href="/assets/common/js/layui/css/layui.css" rel="stylesheet">
<link href="/assets/common/css/component.css" rel="stylesheet">
<link href="/assets/common/css/toastr.min.css" rel="stylesheet">
<link href="/assets/common/js/layer/theme/default/layer.css" rel="stylesheet">
<link href="/assets/user/css/index.css" rel="stylesheet">
${opts.extraHead || ''}
</head>
<body style="${bgStyle}">
`;
}

// 头部 (对应 acg-faka Cartoon Index/Header.html)
export function headerHtml(env, opts, navItems) {
  const siteName = opts.blogname || opts.shop_name || 'ACG发卡';
  const navLis = (navItems || [])
    .map((n) => `<li class="nav-item"><a class="nav-link ${n.active ? 'active' : ''}" href="${esc(n.url)}"${n.newtab ? ' target="_blank"' : ''}>${esc(n.name)}</a></li>`)
    .join('');
  return `<nav class="navbar navbar-expand-lg navbar-acg">
<div class="container">
<a class="navbar-brand fw-bold d-flex align-items-center" href="/">
<img src="/favicon.ico" alt="ACG Logo" class="brand-logo me-2">
<span style="color:#1396558a;">${esc(siteName)}</span>
</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
<span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse" id="navbarNav">
<ul class="navbar-nav me-auto mb-lg-0">
${navLis}
</ul>
<div class="d-none d-lg-flex search-input" role="search">
<div class="input-group">
<span class="input-group-text"><i class="fa-duotone fa-regular fa-magnifying-glass nav-icon"></i></span>
<input class="form-control item-search-input" type="search" id="acgSearchInput" placeholder="搜索商品关键词.." aria-label="Search">
</div>
</div>
</div>
<div class="ms-2 user-login-box">
<a class="btn btn-outline-secondary btn-sm br-12" href="/?action=order_query"><i class="fa-duotone fa-regular fa-magnifying-glass nav-icon"></i>查询订单</a>
<a class="btn btn-primary btn-sm br-12" href="/?action=help"><i class="fa-duotone fa-regular fa-circle-question nav-icon"></i>买家帮助</a>
</div>
</div>
</nav>
<div id="pjax-container">
`;
}

// 尾部 (对应 acg-faka Cartoon Index/Footer.html)
export function footerHtml(env, opts) {
  const icp = opt(opts, 'icp', '');
  return `</div>
${icp ? `<footer class="text-center text-muted py-3" style="font-size:13px;">${esc(icp)}</footer>` : ''}
<script src="/assets/common/js/jquery.min.js"></script>
<script src="/assets/common/js/bootstrap/bootstrap.bundle.min.js"></script>
<script src="/assets/common/js/util/dict.js"></script>
<script src="/assets/common/js/toastr.min.js"></script>
<script src="/assets/common/js/layer/layer.js"></script>
<script src="/assets/common/js/util.js"></script>
<script src="/assets/common/js/format.js"></script>
<script src="/assets/common/js/message.js"></script>
<script src="/assets/common/js/component.js"></script>
<script src="/assets/common/js/cache.js"></script>
<script src="/assets/user/js/trade.js"></script>
<script src="/assets/user/js/treasure.js"></script>
<script src="/assets/user/js/_index.js"></script>
<script>
$(function(){
  $('#acgSearchInput').on('keypress', function(e){
    if (e.which === 13) {
      var kw = $(this).val().trim();
      if (!kw) { layer.msg('请输入要搜索的商品名称关键词'); return; }
      location.href = '/?action=search&q=' + encodeURIComponent(kw);
    }
  });
});
</script>
`;
}

export function pageFoot() {
  return `</body>
</html>`;
}

// 组装完整页面
export function layout(env, opts, navItems, body) {
  return (
    pageHead(opts) +
    headerHtml(env, opts, navItems) +
    body +
    footerHtml(env, opts) +
    pageFoot()
  );
}

// 默认导航（首页 + 分类）
export async function buildNav(db, activePath) {
  const items = [{ name: '首页', url: '/', active: activePath === '/' }];
  try {
    const sorts = await getSorts(db);
    for (const s of sorts) {
      items.push({
        name: s.sortname,
        url: '/?sort_id=' + s.sid,
        active: activePath === ('/?sort_id=' + s.sid) || activePath === ('?' + s.sid),
      });
    }
  } catch (e) {}
  return items;
}

// 简单 toast/alert 片段
export function toastScript(msg) {
  return `<script>layui.use(['layer'], function(){ layer.msg(${JSON.stringify(msg)}); });</script>`;
}

// ---------- 分页 ----------
export function paginationHtml(baseUrl, page, pages, total) {
  if (pages <= 1) return '';
  const sep = baseUrl.includes('?') ? '&' : '?';
  const link = (p) => `<li class="page-item ${p === page ? 'active' : ''}"><a class="page-link" href="${baseUrl}${sep}page=${p}">${p}</a></li>`;
  let html = '<nav class="mt-3"><ul class="pagination justify-content-center mb-0">';
  if (page > 1) html += `<li class="page-item"><a class="page-link" href="${baseUrl}${sep}page=${page - 1}">上一页</a></li>`;
  for (let i = 1; i <= pages; i++) html += link(i);
  if (page < pages) html += `<li class="page-item"><a class="page-link" href="${baseUrl}${sep}page=${page + 1}">下一页</a></li>`;
  html += `</ul></nav>`;
  return html;
}