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

// ---------- 面页骨架 ----------
export function pageHead(opts) {
  const title = opts.title || 'DCSHOP多财商城';
  const kw = opts.keywords || '自动发卡,虚拟商品,卡密';
  const desc = opts.description || '';
  return `<!DOCTYPE html>
<html lang="zh-cn" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<title>${esc(title)}</title>
<meta name="keywords" content="${esc(kw)}">
<meta name="description" content="${esc(desc)}">
<link rel="icon" href="/favicon.ico">
<script src="/vendor/jquery.min.js"></script>
<link rel="stylesheet" href="/vendor/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="/vendor/remixicon/remixicon.css">
<link rel="stylesheet" href="/vendor/layui/css/layui.css">
<script src="/vendor/layui/layui.js"></script>
<link rel="stylesheet" href="/css/header.css">
<script src="/js/header.js"></script>
<link rel="stylesheet" href="/css/em.css">
<link rel="stylesheet" href="/css/style.css">
<link rel="stylesheet" href="/css/goods-layout.css">
<link rel="stylesheet" href="/css/theme.css">
<style>
html,body{height:100%;}
body{display:flex;flex-direction:column;min-height:100vh;margin:0;}
#app-main{flex:1;}
</style>
${opts.extraHead || ''}
</head>
<body>
<div id="mask"></div>
`;
}

// 头部（对应 content/common/header.php 骨架）
export function headerHtml(env, opts, navItems) {
  const siteName = opts.blogname || 'DCSHOP多财商城';
  const subtitle = opt(opts, 'site_subtitle', '');
  const logo = opt(opts, 'logo', '');
  const loginSwitch = opt(opts, 'login_switch', 'y');
  const navLis = (navItems || [])
    .map((n) => `<li class="${n.active ? 'current' : ''}"><a href="${esc(n.url)}"${n.newtab ? ' target="_blank"' : ''}>${esc(n.name)}</a></li>`)
    .join('');
  return `<header class="header">
<div class="h-fix">
<div class="container">
<h1 class="logo-brand">
<a href="/">
${logo ? `<img class="brand-logo" src="${esc(logo)}" alt="${esc(siteName)}">` : `<img class="brand-logo" src="/img/logo.apng" alt="${esc(siteName)}" style="display:none;">`}
<div class="brand-text">
<span class="brand-title">${esc(siteName)}</span>
${subtitle ? `<span class="brand-subtitle">${esc(subtitle)}</span>` : ''}
</div>
</a>
</h1>
<div class="nav-container">
<nav class="nav-bar" id="nav-box">
<ul class="nav">
${navLis}
</ul>
</nav>
</div>
<div class="header-right">
<div class="header-right-btn">
<div class="search">
<button class="s-btn off" type="button"><i class="fa fa-search"></i></button>
<form id="headerSearchExpand" class="header-search-expand" action="/" method="get" style="display:none;">
<input type="hidden" name="action" value="search">
<input name="q" type="text" class="header-search-input" placeholder="搜索商品..." autocomplete="off">
<button type="submit" class="header-search-submit"><i class="fa fa-search"></i></button>
<span class="header-search-close"><i class="fa fa-times"></i></span>
</form>
</div>
${loginSwitch === 'y' ? `<div class="header-user"><a href="/?action=user"><i class="fa fa-user-o"></i></a></div>` : ''}
<div class="header-search-order-btn"><a href="/?action=order_query">查询订单</a></div>
<div class="header-help-mobile"><a class="header-help-btn" href="/?action=help">买家帮助</a></div>
<div id="m-btn" class="m-btn"><i class="fa fa-bars"></i></div>
</div>
</div>
</div>
</div>
</header>
`;
}

// 尾部（对应 content/common/footer.php 骨架）
export function footerHtml(env, opts) {
  const footerInfo = opt(opts, 'footer_info', 'Powered by DuoCai | DCSHOP提供技术支持');
  const icp = opt(opts, 'icp', '');
  return `<footer class="main-footer">
<div class="container">
<div class="footer-content">
<div class="footer-info">
<div class="copyright">
<span>${footerInfo}</span>${icp ? `&nbsp;<a class="icp-link" href="https://beian.miit.gov.cn/" target="_blank" rel="nofollow">${esc(icp)}</a>` : ''}
</div>
</div>
</div>
</div>
</footer>
<script>
if (window.tipsMsg === undefined) { window.tipsMsg = function(msg, type) { alert(msg); }; }
</script>
`;
}

export function pageFoot() {
  return `</div>\n</body>\n</html>`;
}

// 组装完整页面
export function layout(env, opts, navItems, body) {
  return (
    pageHead(opts) +
    headerHtml(env, opts, navItems) +
    '<div id="app-main">' +
    body +
    '</div>' +
    footerHtml(env, opts) +
    '<script src="/js/header.js"></script>' +
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
  const link = (p) => `<a href="${baseUrl}&page=${p}" class="${p === page ? 'current' : ''}">${p}</a>`;
  let html = '<div class="goods-pagination">';
  if (page > 1) html += `<a href="${baseUrl}&page=${page - 1}">上一页</a>`;
  for (let i = 1; i <= pages; i++) html += link(i);
  if (page < pages) html += `<a href="${baseUrl}&page=${page + 1}">下一页</a>`;
  html += `</div>`;
  return html;
}