// DCSHOP faka - Pages 单文件入口 (由 worker.js+admin.js+lib.js 自动打包生成, 勿手改)

// lib.js
function esc(s) {
  if (s === null || s === void 0) return "";
  return String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#39;");
}
function fen2yuan(fen) {
  const n = Number(fen) || 0;
  return (n / 100).toFixed(2);
}
function ts2str(ts) {
  if (!ts) return "";
  const t = new Date(Number(ts) * 1e3);
  const p2 = (x) => String(x).padStart(2, "0");
  return t.getFullYear() + "-" + p2(t.getMonth() + 1) + "-" + p2(t.getDate()) + " " + p2(t.getHours()) + ":" + p2(t.getMinutes());
}
function now() {
  return Math.floor(Date.now() / 1e3);
}
async function hmacSha256(secret, data) {
  const enc = new TextEncoder();
  const key = await crypto.subtle.importKey("raw", enc.encode(secret), { name: "HMAC", hash: "SHA-256" }, false, ["sign"]);
  const sigBuf = await crypto.subtle.sign("HMAC", key, enc.encode(data));
  return Array.from(new Uint8Array(sigBuf)).map((b) => b.toString(16).padStart(2, "0")).join("");
}
function parseQuery(u) {
  const o = {};
  try {
    const q = (u.split("?")[1] || "").split("#")[0];
    if (!q) return o;
    for (const kv of q.split("&")) {
      if (!kv) continue;
      const i = kv.indexOf("=");
      const k = i >= 0 ? decodeURIComponent(kv.slice(0, i)) : decodeURIComponent(kv);
      const v = i >= 0 ? decodeURIComponent(kv.slice(i + 1)) : "";
      if (k in o) {
        if (!Array.isArray(o[k])) o[k] = [o[k]];
        o[k].push(v);
      } else o[k] = v;
    }
  } catch (e) {
  }
  return o;
}
async function getOptions(db) {
  const { results } = await db.prepare("SELECT option_name, option_value FROM dc_options").all();
  const m = {};
  for (const r of results) m[r.option_name] = r.option_value;
  return m;
}
function opt(opts, key, def) {
  const v = opts[key];
  return v === void 0 || v === "" ? def : v;
}
var TYPE_BADGE = {
  duli: "fk-tb-blue",
  guding: "fk-tb-pink",
  xuni: "fk-tb-purple",
  post: "fk-tb-orange",
  once: "fk-tb-orange",
  general: "fk-tb-green",
  service: "fk-tb-blue",
  physical: "fk-tb-gray"
};
var TYPE_NAME = {
  once: "\u4E00\u5361\u4E00\u5BC6",
  general: "\u901A\u7528\u5361\u5BC6",
  service: "\u865A\u62DF\u670D\u52A1",
  duli: "\u72EC\u7ACB\u5BF9\u63A5",
  guding: "\u56FA\u5B9A\u5361\u5BC6",
  physical: "\u5B9E\u7269\u5546\u54C1"
};
async function getSkus(db, goodsId) {
  const { results } = await db.prepare("SELECT * FROM dc_skus WHERE goods_id = ? ORDER BY id ASC").bind(goodsId).all();
  return results;
}
async function getSorts(db) {
  const { results } = await db.prepare("SELECT * FROM dc_sort WHERE type = 'goods' AND delete_time IS NULL ORDER BY taxis ASC, sid ASC").all();
  return results;
}
async function buildAttrSpec(db, skuStr) {
  if (!skuStr || skuStr === "0") return "";
  const ids = String(skuStr).split("-").filter((x) => x);
  if (!ids.length) return "";
  const placeholders = ids.map(() => "?").join(",");
  const { results } = await db.prepare("SELECT v.id, v.name, a.title FROM dc_sku_value v LEFT JOIN dc_sku_attr a ON a.id = v.attr_id WHERE v.id IN (" + placeholders + ")").bind(...ids).all();
  const map = {};
  for (const r of results) map[r.id] = r;
  let s = "";
  for (const id of ids) {
    const r = map[id];
    if (r) s += esc(r.title) + "\uFF1A" + esc(r.name) + "\uFF1B";
  }
  return s;
}
function pageHead(opts) {
  const siteName = opts.blogname || opts.shop_name || "ACG\u53D1\u5361";
  const title = opts.title ? opts.title + " - " + siteName : siteName;
  const kw = opts.keywords || "\u81EA\u52A8\u53D1\u5361,\u865A\u62DF\u5546\u54C1,\u5361\u5BC6,ACG";
  const desc = opts.description || "";
  const bg = opt(opts, "background_url", "") || opt(opts, "bg_img", "");
  const bgStyle = bg ? `background-size:cover;background-image:linear-gradient(180deg,rgb(255 255 255/0%),rgb(255 255 255/71%)),url('${esc(bg)}');` : "background:#f4f6fa;";
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
${opts.extraHead || ""}
</head>
<body style="${bgStyle}">
`;
}
function headerHtml(env, opts, navItems) {
  const siteName = opts.blogname || opts.shop_name || "ACG\u53D1\u5361";
  const navLis = (navItems || []).map((n) => `<li class="nav-item"><a class="nav-link ${n.active ? "active" : ""}" href="${esc(n.url)}"${n.newtab ? ' target="_blank"' : ""}>${esc(n.name)}</a></li>`).join("");
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
<input class="form-control item-search-input" type="search" id="acgSearchInput" placeholder="\u641C\u7D22\u5546\u54C1\u5173\u952E\u8BCD.." aria-label="Search">
</div>
</div>
</div>
<div class="ms-2 user-login-box">
<a class="btn btn-outline-secondary btn-sm br-12" href="/?action=order_query"><i class="fa-duotone fa-regular fa-magnifying-glass nav-icon"></i>\u67E5\u8BE2\u8BA2\u5355</a>
<a class="btn btn-primary btn-sm br-12" href="/?action=help"><i class="fa-duotone fa-regular fa-circle-question nav-icon"></i>\u4E70\u5BB6\u5E2E\u52A9</a>
</div>
</div>
</nav>
<div id="pjax-container">
`;
}
function footerHtml(env, opts) {
  const icp = opt(opts, "icp", "");
  return `</div>
${icp ? `<footer class="text-center text-muted py-3" style="font-size:13px;">${esc(icp)}</footer>` : ""}
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
      if (!kw) { layer.msg('\u8BF7\u8F93\u5165\u8981\u641C\u7D22\u7684\u5546\u54C1\u540D\u79F0\u5173\u952E\u8BCD'); return; }
      location.href = '/?action=search&q=' + encodeURIComponent(kw);
    }
  });
});
</script>
`;
}
function pageFoot() {
  return `</body>
</html>`;
}
function layout(env, opts, navItems, body) {
  return pageHead(opts) + headerHtml(env, opts, navItems) + body + footerHtml(env, opts) + pageFoot();
}
async function buildNav(db, activePath) {
  const items = [{ name: "\u9996\u9875", url: "/", active: activePath === "/" }];
  try {
    const sorts = await getSorts(db);
    for (const s of sorts) {
      items.push({
        name: s.sortname,
        url: "/?sort_id=" + s.sid,
        active: activePath === "/?sort_id=" + s.sid || activePath === "?" + s.sid
      });
    }
  } catch (e) {
  }
  return items;
}
function paginationHtml(baseUrl, page, pages, total) {
  if (pages <= 1) return "";
  const sep = baseUrl.includes("?") ? "&" : "?";
  const link = (p2) => `<li class="page-item ${p2 === page ? "active" : ""}"><a class="page-link" href="${baseUrl}${sep}page=${p2}">${p2}</a></li>`;
  let html = '<nav class="mt-3"><ul class="pagination justify-content-center mb-0">';
  if (page > 1) html += `<li class="page-item"><a class="page-link" href="${baseUrl}${sep}page=${page - 1}">\u4E0A\u4E00\u9875</a></li>`;
  for (let i = 1; i <= pages; i++) html += link(i);
  if (page < pages) html += `<li class="page-item"><a class="page-link" href="${baseUrl}${sep}page=${page + 1}">\u4E0B\u4E00\u9875</a></li>`;
  html += `</ul></nav>`;
  return html;
}

// admin.js
var JSON_HEADERS = { "content-type": "application/json; charset=utf-8" };
var HTML_HEADERS = { "content-type": "text/html; charset=utf-8" };
var DEFAULT_ADMIN = { u: "admin", p: "admin123" };
async function handleAdmin(request, env) {
  const url = new URL(request.url);
  const path = url.pathname;
  const q = parseQuery(url.search);
  try {
    const opts = await getOptions(env.DB);
    env._shopName = opt(opts, "blogname", "ACG\u53D1\u5361");
  } catch (e) {
    env._shopName = "ACG\u53D1\u5361";
  }
  if (request.method === "POST") {
    const form = await request.formData();
    if (path === "/admin/login") return doLogin(form, env);
    if (path === "/admin/logout") return doLogout();
    if (!await isAdmin(request, env)) return json({ code: 1, msg: "\u672A\u767B\u5F55\u6216\u767B\u5F55\u5DF2\u8FC7\u671F" });
    if (path === "/admin/goods/save") return saveGoods(form, env);
    if (path === "/admin/goods/delete") return del("dc_goods", "id", form, env);
    if (path === "/admin/sort/save") return saveSort(form, env);
    if (path === "/admin/sort/delete") return del("dc_sort", "sid", form, env);
    if (path === "/admin/kami/add") return addKami(form, env);
    if (path === "/admin/kami/import") return importKami(form, env);
    if (path === "/admin/kami/delete") return delKami(form, env);
    if (path === "/admin/order/refund") return refundOrder(form, env);
    if (path === "/admin/order/delete") return del("dc_order", "id", form, env);
    if (path === "/admin/settings/save") return saveSettings(form, env);
    return json({ code: 1, msg: "\u672A\u77E5\u63A5\u53E3" });
  }
  if (!await isAdmin(request, env)) {
    return new Response(loginHtml(env._shopName), { headers: HTML_HEADERS });
  }
  if (path === "/admin") return dashboard(request, env);
  if (path === "/admin/goods") return goodsList(request, env, q);
  if (path === "/admin/goods/edit") return goodsEdit(request, env, q);
  if (path === "/admin/kami") return kamiManage(request, env, q);
  if (path === "/admin/orders") return ordersList(request, env, q);
  if (path === "/admin/sorts") return sortsList(request, env, q);
  if (path === "/admin/settings") return settingsPage(request, env, q);
  if (path === "/admin/logout") return redir("/admin");
  return adminPage(request, env, "\u540E\u53F0", '<p>\u672A\u77E5\u9875\u9762</p><a href="/admin">\u8FD4\u56DE</a>');
}
function json(o) {
  return new Response(JSON.stringify(o), { headers: JSON_HEADERS });
}
function redir(to) {
  return new Response("", { status: 302, headers: { location: to } });
}
async function isAdmin(request, env) {
  const cookie = parseCookie(request.headers.get("Cookie") || "");
  const t = cookie.acg_admin_token;
  if (!t) return false;
  try {
    const dec = atob(t);
    const [user, ts, sig] = dec.split(".");
    if (!user || !ts || !sig) return false;
    const secret = env.SECRET || "acg-faka-secret";
    const expect = await hmacSha256(secret, user + "." + ts);
    if (sig !== expect) return false;
    const cfg = { u: env.ADMIN_USERNAME || DEFAULT_ADMIN.u, p: env.ADMIN_PASSWORD || DEFAULT_ADMIN.p };
    return user === cfg.u;
  } catch (e) {
    return false;
  }
}
async function doLogin(form, env) {
  const u = form.get("username") || "";
  const p2 = form.get("password") || "";
  const cfg = { u: env.ADMIN_USERNAME || DEFAULT_ADMIN.u, p: env.ADMIN_PASSWORD || DEFAULT_ADMIN.p };
  if (u !== cfg.u || p2 !== cfg.p) return json({ code: 1, msg: "\u8D26\u53F7\u6216\u5BC6\u7801\u9519\u8BEF" });
  const ts = String(now());
  const secret = env.SECRET || "acg-faka-secret";
  const sig = await hmacSha256(secret, u + "." + ts);
  const token = btoa(u + "." + ts + "." + sig);
  const res = json({ code: 0 });
  res.headers.append("Set-Cookie", "acg_admin_token=" + token + "; Path=/; HttpOnly; Max-Age=86400");
  return res;
}
function doLogout() {
  const res = redir("/admin");
  res.headers.append("Set-Cookie", "acg_admin_token=; Path=/; HttpOnly; Max-Age=0");
  return res;
}
function parseCookie(c) {
  const o = {};
  for (const kv of c.split(";")) {
    const i = kv.indexOf("=");
    if (i > 0) o[kv.slice(0, i).trim()] = kv.slice(i + 1).trim();
  }
  return o;
}
function adminPage(request, env, title, content) {
  const shopName = env && env._shopName || "ACG\u53D1\u5361";
  return new Response(
    `<!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
<script>(function(){var e=document.documentElement;try{var p=localStorage.getItem('admin-theme')||'auto';var d=p==='auto'?(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'):p;e.setAttribute('data-theme',d);e.setAttribute('data-theme-pref',p);var m=localStorage.getItem('admin-layout-mode')==='desktop'?'desktop':((window.innerWidth||screen.width)<992?'mobile':'desktop');e.setAttribute('data-admin-layout',m);}catch(_){e.setAttribute('data-theme','light');e.setAttribute('data-admin-layout',(window.innerWidth||screen.width)<992?'mobile':'desktop');}})();</script>
<title>${esc(title)}-${esc(shopName)}</title>
<link rel="shortcut icon" href="/favicon.ico"/>
<link href="/assets/common/css/_.css" rel="stylesheet">
<link href="/assets/common/css/bootstrap.min.css" rel="stylesheet">
<link href="/assets/common/css/font.min.css" rel="stylesheet">
<link href="/assets/common/css/select2.min.css" rel="stylesheet">
<link href="/assets/common/css/component.css" rel="stylesheet">
<link href="/assets/common/css/toastr.min.css" rel="stylesheet">
<link href="/assets/common/js/layui/css/layui.css" rel="stylesheet">
<link href="/assets/common/js/layer/theme/default/layer.css" rel="stylesheet">
<link href="/assets/common/css/md-tokens.css" rel="stylesheet">
<link href="/assets/common/css/md-components.css" rel="stylesheet">
<link href="/assets/common/css/_material.css" rel="stylesheet">
<link href="/assets/common/fonts/material-icons.css" rel="stylesheet">
<link href="/assets/common/css/mdicon.css" rel="stylesheet">
<link href="/assets/admin/css/style.bundle.css" rel="stylesheet">
<link href="/assets/admin/css/material.css" rel="stylesheet">
</head>
<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed aside-enabled aside-fixed" style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px;">
<div class="d-flex flex-column flex-root">
  <div class="page d-flex flex-row flex-column-fluid">
    <div id="kt_aside" class="aside aside-light aside-hoverable">
      <div class="aside-menu flex-column-fluid">
        <div class="hover-scroll-overlay-y my-5 my-lg-5" id="kt_aside_menu_wrapper" style="overflow-x: hidden;">
          <div class="menu menu-column menu-title-gray-800 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500" id="kt_aside_menu">
            <div class="menu-item"><div class="menu-content pb-2"><span class="menu-section text-muted text-uppercase fs-8 ls-1">Main</span></div></div>
            <div class="menu-item"><a class="menu-link  ${p("/admin", pathOf(request))}" href="/admin"><span class="menu-icon"><svg class="menu-svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M19 5v2h-4V5h4M9 5v6H5V5h4m10 8v6h-4v-6h4M9 17v2H5v-2h4M21 3h-8v6h8V3zM11 3H3v10h8V3zm10 8h-8v10h8V11zm-10 4H3v6h8v-6z"/></svg></span><span class="menu-title">\u4EEA\u8868\u76D8</span></a></div>
            <div class="menu-item"><div class="menu-content pt-8 pb-2"><span class="menu-section text-muted text-uppercase fs-8 ls-1">Trade</span></div></div>
            <div class="menu-item"><a class="menu-link  ${p("/admin/goods", pathOf(request))}" href="/admin/goods"><span class="menu-icon"><svg class="menu-svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M20 2H4c-1 0-2 .9-2 2v3.01c0 .72.43 1.34 1 1.69V20c0 1.1 1.1 2 2 2h14c.9 0 2-.9 2-2V8.7c.57-.35 1-.97 1-1.69V4c0-1.1-1-2-2-2zm-1 18H5V9h14v11zm1-13H4V4h16v3z"/><path d="M9 12h6v2H9z"/></svg></span><span class="menu-title">\u5546\u54C1\u7BA1\u7406</span></a></div>
            <div class="menu-item"><a class="menu-link  ${p("/admin/kami", pathOf(request))}" href="/admin/kami"><span class="menu-icon"><svg class="menu-svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M22 19h-6v-4h-2.68c-1.14 2.42-3.6 4-6.32 4c-3.86 0-7-3.14-7-7s3.14-7 7-7c2.72 0 5.17 1.58 6.32 4H24v6h-2v4zm-4-2h2v-4h2v-2H11.94l-.23-.67C11.01 8.34 9.11 7 7 7c-2.76 0-5 2.24-5 5s2.24 5 5 5c2.11 0 4.01-1.34 4.71-3.33l.23-.67H18v4z"/></svg></span><span class="menu-title">\u5361\u5BC6\u7BA1\u7406</span></a></div>
            <div class="menu-item"><a class="menu-link  ${p("/admin/orders", pathOf(request))}" href="/admin/orders"><span class="menu-icon"><svg class="menu-svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M15.55 13c.75 0 1.41-.41 1.75-1.03l3.58-6.49A.996.996 0 0 0 20.01 4H5.21l-.94-2H1v2h2l3.6 7.59l-1.35 2.44C4.52 15.37 5.48 17 7 17h12v-2H7l1.1-2h7.45zM6.16 6h12.15l-2.76 5H8.53L6.16 6zM7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2s-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2s2-.9 2-2s-.9-2-2-2z"/></svg></span><span class="menu-title">\u5546\u54C1\u8BA2\u5355</span></a></div>
            <div class="menu-item"><a class="menu-link  ${p("/admin/sorts", pathOf(request))}" href="/admin/sorts"><span class="menu-icon"><svg class="menu-svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg></span><span class="menu-title">\u5206\u7C7B\u7BA1\u7406</span></a></div>
            <div class="menu-item"><div class="menu-content pt-8 pb-2"><span class="menu-section text-muted text-uppercase fs-8 ls-1">Config</span></div></div>
            <div class="menu-item"><a class="menu-link  ${p("/admin/settings", pathOf(request))}" href="/admin/settings"><span class="menu-icon"><svg class="menu-svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M19.43 12.98c.04-.32.07-.64.07-.98c0-.34-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46a.5.5 0 0 0-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65A.488.488 0 0 0 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1a.566.566 0 0 0-.18-.03c-.17 0-.34.09-.43.25l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98c0 .33.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46a.5.5 0 0 0 .61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.06.02.12.03.18.03c.17 0 .34-.09.43-.25l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zm-1.98-1.71c.04.31.05.52.05.73c0 .21-.02.43-.05.73l-.14 1.13l.89.7l1.08.84l-.7 1.21l-1.27-.51l-1.04-.42l-.9.68c-.43.32-.84.56-1.25.73l-1.06.43l-.16 1.13l-.2 1.35h-1.4l-.19-1.35l-.16-1.13l-1.06-.43c-.43-.18-.83-.41-1.23-.71l-.91-.7l-1.06.43l-1.27.51l-.7-1.21l1.08-.84l.89-.7l-.14-1.13zm-5.45-3.27a4 4 0 1 0 0 8a4 4 0 0 0 0-8z"/></svg></span><span class="menu-title">\u7F51\u7AD9\u8BBE\u7F6E</span></a></div>
            <div class="menu-item"><div class="menu-content pt-8 pb-2"><span class="menu-section text-muted text-uppercase fs-8 ls-1">Account</span></div></div>
            <div class="menu-item"><a class="menu-link" href="/admin/logout"><span class="menu-icon"><svg class="menu-svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg></span><span class="menu-title">\u9000\u51FA\u767B\u5F55</span></a></div>
          </div>
        </div>
      </div>
    </div>
    <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
      <div id="kt_header" class="header align-items-stretch">
        <div class="container-fluid d-flex align-items-stretch justify-content-between">
          <div class="aside-logo flex-column-auto d-none d-lg-flex" id="kt_aside_logo">
            <a href="/admin" class="d-flex align-items-center">
              <img style="border-radius: 50%;height: 22px;" src="/favicon.ico">
              <span class="logo fw-bolder ms-2 fs-4" style="color: #919191;">${esc(shopName)}</span>
            </a>
          </div>
          <div class="d-flex align-items-center d-lg-none ms-n3 me-1" title="Show aside menu">
            <div class="btn btn-icon btn-active-light-primary w-30px h-30px w-md-40px h-md-40px" onclick="document.body.classList.toggle('aside-mobile-minimized')">
              <span class="svg-icon svg-icon-2x"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M21 7H3C2.4 7 2 6.6 2 6V4C2 3.4 2.4 3 3 3H21C21.6 3 22 3.4 22 4V6C22 6.6 21.6 7 21 7Z" fill="black"/><path opacity="0.3" d="M21 14H3C2.4 14 2 13.6 2 13V11C2 10.4 2.4 10 3 10H21C21.6 10 22 10.4 22 11V13C22 13.6 21.6 14 21 14ZM22 20V18C22 17.4 21.6 17 21 17H3C2.4 17 2 17.4 2 18V20C2 20.6 2.4 21 3 21H21C21.6 21 22 20.6 22 20Z" fill="black"/></svg></span>
            </div>
          </div>
          <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0 d-hide show-mobile">
            <a href="/admin"><img height="25px" style="border-radius: 50%;" src="/favicon.ico"></a>
          </div>
          <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1">
            <div class="d-flex align-items-stretch" id="kt_header_nav">
              <div class="header-menu">
                <div class="menu me-4 menu-lg-rounded menu-column menu-lg-row menu-state-bg menu-title-gray-700 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-400 fw-bold my-5 my-lg-0 align-items-stretch">
                  <div class="menu-item me-lg-1 app-store"><a class="py-3 text-primary fw-bold" href="/"><i class="fa-duotone fa-regular fa-house"></i> \u8FD4\u56DE\u524D\u53F0</a></div>
                </div>
              </div>
            </div>
            <div class="d-flex align-items-stretch flex-shrink-0">
              <div class="d-flex align-items-center ms-1 ms-lg-3">
                <a href="/admin/logout"><div class="cursor-pointer symbol symbol-30px symbol-md-40px"><img src="/favicon.ico" alt="user"/></div></a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="pjax-container">
        <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
          <div class="toolbar" id="kt_toolbar">
            <div class="container-fluid d-flex flex-stack flex-wrap gap-2 align-items-center">
              <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 align-items-center flex-column justify-content-center my-0">${esc(title)}</h1>
              </div>
            </div>
          </div>
          <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-fluid">
              ${content}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="/assets/common/js/jquery.min.js"></script>
<script src="/assets/common/js/bootstrap/bootstrap.bundle.min.js"></script>
<script src="/assets/common/js/toastr.min.js"></script>
<script src="/assets/common/js/util.js"></script>
<script src="/assets/common/js/format.js"></script>
<script src="/assets/common/js/message.js"></script>
<script src="/assets/common/js/component.js"></script>
<script src="/assets/common/js/layui/layui.js"></script>
<script src="/assets/common/js/layer/layer.js"></script>
<script>$(function(){ var pa=location.pathname; $('.aside-menu a.menu-link').each(function(){ if($(this).attr('href')===pa) $(this).addClass('active'); }); });</script>
</body></html>`,
    { headers: HTML_HEADERS }
  );
}
function pathOf(request) {
  return new URL(request.url).pathname;
}
function p(target, cur) {
  return cur === target ? "active" : "";
}
function loginHtml(shopName) {
  return `<!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>\u767B\u5F55 - ${esc(shopName)}</title>
<script>(function(){try{var p=localStorage.getItem('admin-theme')||'auto';var d=p==='auto'?(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'):p;var e=document.documentElement;e.setAttribute('data-theme',d);e.setAttribute('data-theme-pref',p);}catch(_){document.documentElement.setAttribute('data-theme','light');}})();</script>
<link href="/assets/common/css/_.css" rel="stylesheet">
<link href="/assets/common/css/bootstrap.min.css" rel="stylesheet">
<link href="/assets/common/css/font.min.css" rel="stylesheet">
<link href="/assets/common/css/select2.min.css" rel="stylesheet">
<link href="/assets/common/css/component.css" rel="stylesheet">
<link href="/assets/common/css/toastr.min.css" rel="stylesheet">
<link href="/assets/common/js/layui/css/layui.css" rel="stylesheet">
<link href="/assets/common/js/layer/theme/default/layer.css" rel="stylesheet">
<link href="/assets/common/css/md-tokens.css" rel="stylesheet">
<link href="/assets/admin/css/style.bundle.css" rel="stylesheet">
<link href="/assets/admin/css/auth.css" rel="stylesheet">
<link href="/assets/admin/css/material-auth.css" rel="stylesheet">
</head>
<body class="ay-bg">
<div class="ay-dim" aria-hidden="true"></div>
<div class="ay-petals" aria-hidden="true">
    <i style="left:6%; top:-8vh; animation-duration:11s"></i>
    <i style="left:24%; top:-12vh; animation-duration:13s"></i>
    <i style="left:52%; top:-16vh; animation-duration:12s"></i>
    <i style="left:72%; top:-10vh; animation-duration:10s"></i>
    <i style="left:86%; top:-18vh; animation-duration:14s"></i>
</div>
<main class="ay-wrap">
    <section class="ay-card" role="dialog" aria-labelledby="ay-title" aria-describedby="ay-sub">
        <button type="button" class="ay-theme" id="ay-theme" aria-label="\u5207\u6362\u660E\u6697\u4E3B\u9898" title="\u5207\u6362\u660E\u6697\u4E3B\u9898">
            <svg class="ico-moon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/></svg>
            <svg class="ico-sun" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
        </button>
        <header class="ay-head">
            <div class="ay-logo" aria-hidden="true"></div>
            <h1 id="ay-title" class="ay-title">\u6B22\u8FCE\u56DE\u6765\uFF0C\u6307\u6325\u5B98</h1>
            <p id="ay-sub" class="ay-sub">\u6B63\u5728\u9A8C\u8BC1\u60A8\u7684\u7BA1\u7406\u5458\u8EAB\u4EFD</p>
        </header>
        <div class="ay-body">
            <form id="ay-form" method="post" novalidate>
                <div class="ay-field has-ico">
                    <input id="ay-user" name="username" class="ay-input" type="text" placeholder=" " autocomplete="username" autofocus required>
                    <span class="ay-label">\u7528\u6237\u540D</span>
                    <span class="ay-ico" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
                </div>
                <div class="ay-field has-ico">
                    <input id="ay-pass" name="password" class="ay-input" type="password" placeholder=" " autocomplete="current-password" required>
                    <span class="ay-label">\u5BC6\u7801</span>
                    <span class="ay-ico" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
                    <button type="button" class="ay-eye" id="ay-eye" aria-label="\u663E\u793A\u5BC6\u7801">
                        <svg class="ay-eye-open" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="ay-eye-shut" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><path d="m1 1 22 22"/></svg>
                    </button>
                    <span class="ay-caps" id="ay-caps" hidden>\u5927\u5199\u9501\u5B9A\u5DF2\u5F00\u542F</span>
                </div>
                <div class="ay-row">
                    <label class="ay-check"><input type="checkbox" id="ay-remember" name="remember" value="1">\u4FDD\u6301\u767B\u5F55(24\u5C0F\u65F6)</label>
                </div>
                <div class="ay-err" id="ay-err" hidden></div>
                <button class="ay-btn" type="submit" id="ay-submit">\u786E\u8BA4\u767B\u5165</button>
            </form>
            <div class="ay-foot">\xA9 ${esc(shopName)}</div>
        </div>
    </section>
</main>
<script src="/assets/common/js/jquery.min.js"></script>
<script src="/assets/common/js/toastr.min.js"></script>
<script src="/assets/common/js/layer/layer.js"></script>
<script>
$('#ay-form').on('submit', function(e){
  e.preventDefault();
  $('#ay-err').attr('hidden','').text('');
  $.post('/admin/login', { username: $('#ay-user').val(), password: $('#ay-pass').val() }, function(res){
    if (res.code === 0) { location.href = '/admin'; }
    else { $('#ay-err').removeAttr('hidden').text(res.msg || '\u767B\u5F55\u5931\u8D25'); }
  }, 'json');
});
$(function(){
  var t=localStorage.getItem('admin-theme')||'auto';
  var dark=t==='dark'||(t==='auto'&&matchMedia('(prefers-color-scheme: dark)').matches);
  $('html').attr('data-theme', dark?'dark':'light');
  $('#ay-theme').on('click', function(){
    dark=!dark; $('html').attr('data-theme', dark?'dark':'light');
    localStorage.setItem('admin-theme', dark?'dark':'light');
  });
  $('#ay-eye').on('click', function(){
    var p=$('#ay-pass'); var v=p.attr('type')==='password';
    p.attr('type', v?'text':'password');
    $(this).toggleClass('is-visible', v);
  });
});
</script>
</body></html>`;
}
async function dashboard(request, env) {
  const s = {
    goods: (await env.DB.prepare("SELECT COUNT(*) AS c FROM dc_goods WHERE delete_time IS NULL").first()).c,
    orders: (await env.DB.prepare("SELECT COUNT(*) AS c FROM dc_order").first()).c,
    paid: (await env.DB.prepare("SELECT COUNT(*) AS c FROM dc_order WHERE pay_status = 1").first()).c,
    income: (await env.DB.prepare("SELECT COALESCE(SUM(amount),0) AS s FROM dc_order WHERE pay_status = 1").first()).s
  };
  const recent = (await env.DB.prepare("SELECT * FROM dc_order ORDER BY id DESC LIMIT 8").all()).results;
  const statusText = { 0: "\u5F85\u652F\u4ED8", 1: "\u5DF2\u652F\u4ED8\u5F85\u53D1\u8D27", 2: "\u5DF2\u5B8C\u6210", 3: "\u5DF2\u53D6\u6D88" };
  const recentRows = recent.map((o) => `<tr><td>${esc(o.out_trade_no)}</td><td>\xA5${fen2yuan(o.amount)}</td><td>${ts2str(o.create_time)}</td><td>${statusText[o.status] || o.status}</td></tr>`).join("");
  const content = `<div class="row g-5 g-xl-8">
    <div class="col-xl-3 col-md-6"><div class="card card-flush h-xl-100"><div class="card-body pt-7">
      <div class="d-flex align-items-center"><span class="fs-7 fw-semibold text-muted">\u5546\u54C1\u603B\u6570</span></div>
      <div class="fs-2x fw-bolder pt-4" style="color:#2667d6;">${s.goods}</div>
    </div></div></div>
    <div class="col-xl-3 col-md-6"><div class="card card-flush h-xl-100"><div class="card-body pt-7">
      <div class="d-flex align-items-center"><span class="fs-7 fw-semibold text-muted">\u8BA2\u5355\u603B\u6570</span></div>
      <div class="fs-2x fw-bolder pt-4" style="color:#f59e0b;">${s.orders}</div>
    </div></div></div>
    <div class="col-xl-3 col-md-6"><div class="card card-flush h-xl-100"><div class="card-body pt-7">
      <div class="d-flex align-items-center"><span class="fs-7 fw-semibold text-muted">\u5DF2\u652F\u4ED8\u8BA2\u5355</span></div>
      <div class="fs-2x fw-bolder pt-4" style="color:#22c55e;">${s.paid}</div>
    </div></div></div>
    <div class="col-xl-3 col-md-6"><div class="card card-flush h-xl-100"><div class="card-body pt-7">
      <div class="d-flex align-items-center"><span class="fs-7 fw-semibold text-muted">\u5B9E\u6536\u91D1\u989D (\u5143)</span></div>
      <div class="fs-2x fw-bolder pt-4" style="color:#ef4444;">${fen2yuan(s.income)}</div>
    </div></div></div>
  </div>
  <div class="card mt-10">
    <div class="card-header"><h3 class="card-title">\u6700\u8FD1\u8BA2\u5355</h3></div>
    <div class="card-body py-4">
    <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3"><thead><tr class="fw-bold text-muted"><th>\u8BA2\u5355\u53F7</th><th>\u91D1\u989D</th><th>\u65F6\u95F4</th><th>\u72B6\u6001</th></tr></thead><tbody>${recentRows || '<tr><td colspan="4" class="text-center text-muted py-6">\u6682\u65E0\u8BA2\u5355</td></tr>'}</tbody></table>
    </div>
  </div>`;
  return adminPage(request, env, "\u4EEA\u8868\u76D8", content);
}
async function goodsList(request, env, q) {
  const kw = (q.q || "").trim();
  let sql = "SELECT g.*, s.sortname AS sort_name FROM dc_goods g LEFT JOIN dc_sort s ON s.sid = g.sort_id WHERE g.delete_time IS NULL";
  const binds = [];
  if (kw) {
    sql += " AND g.title LIKE ?";
    binds.push("%" + kw + "%");
  }
  sql += " ORDER BY g.id DESC LIMIT 100";
  const goods = (await env.DB.prepare(sql).bind(...binds).all()).results;
  const rows = goods.map(
    (g) => `<tr>
    <td>${g.id}</td>
    <td>${esc(g.title)}</td>
    <td>${esc(g.sort_name || "-")}</td>
    <td>${esc(TNAME[g.type] || g.type)}</td>
    <td>${g.stock}</td>
    <td>${g.sales}</td>
    <td>${g.is_on_shelf == 1 ? '<span class="text-success">\u4E0A\u67B6</span>' : '<span class="text-muted">\u4E0B\u67B6</span>'}</td>
    <td><a class="btn btn-light-primary btn-sm" href="/admin/goods/edit?id=${g.id}" style="margin-right:6px;">\u7F16\u8F91</a><a class="btn btn-secondary btn-sm" href="/admin/kami?goods_id=${g.id}" style="margin-right:6px;">\u5361\u5BC6</a><button class="btn btn-danger btn-sm" onclick="delGoods(${g.id})">\u5220\u9664</button></td>
  </tr>`
  ).join("");
  const content = `<div class="card">
  <div class="card-body py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <form action="/admin/goods" method="get" class="d-flex gap-2"><input type="text" name="q" class="form-control" placeholder="\u641C\u7D22\u5546\u54C1" value="${esc(kw)}" style="width:220px;"><button class="btn btn-outline-secondary">\u641C\u7D22</button></form>
    <a class="btn btn-success" href="/admin/goods/edit">+ \u65B0\u589E\u5546\u54C1</a>
  </div>
  <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3"><thead><tr class="fw-bold text-muted"><th>ID</th><th>\u6807\u9898</th><th>\u5206\u7C7B</th><th>\u7C7B\u578B</th><th>\u5E93\u5B58</th><th>\u9500\u91CF</th><th>\u72B6\u6001</th><th>\u64CD\u4F5C</th></tr></thead>
  <tbody>${rows || '<tr><td colspan="8" class="text-center text-muted py-6">\u6682\u65E0\u5546\u54C1</td></tr>'}</tbody></table>
  </div>
  </div>
  <script>function delGoods(id){ if(!confirm('\u786E\u8BA4\u5220\u9664\u8BE5\u5546\u54C1\uFF1F')) return; $.post('/admin/goods/delete',{id:id},function(r){ if(r.code===0) location.reload(); else alert(r.msg); },'json'); }</script>`;
  return adminPage(request, env, "\u5546\u54C1\u7BA1\u7406", content);
}
var TNAME = { once: "\u4E00\u5361\u4E00\u5BC6", general: "\u901A\u7528\u5361\u5BC6", service: "\u865A\u62DF\u670D\u52A1", duli: "\u72EC\u7ACB\u5BF9\u63A5", physical: "\u5B9E\u7269" };
async function goodsEdit(request, env, q) {
  const id = parseInt(q.id) || 0;
  let g = null;
  let gSkus = [];
  if (id) {
    g = await env.DB.prepare("SELECT * FROM dc_goods WHERE id = ?").bind(id).first();
    gSkus = (await env.DB.prepare("SELECT * FROM dc_skus WHERE goods_id = ? ORDER BY id ASC").bind(id).all()).results;
  }
  const sorts = (await env.DB.prepare("SELECT * FROM dc_sort WHERE type = 'goods' ORDER BY taxis ASC").all()).results;
  const types = (await env.DB.prepare("SELECT * FROM dc_goods_type WHERE delete_time IS NULL").all()).results;
  const gv = (k, def) => g ? g[k] : def;
  const skusText = gSkus.length ? gSkus.map((s) => `${s.sku}|${s.guest_price}|${s.market_price || 0}|${s.stock}`).join("\n") : "0|1000|1200|100";
  const sortOptions = sorts.map((s) => `<option value="${s.sid}" ${g && g.sort_id == s.sid ? "selected" : ""}>${esc(s.sortname)}</option>`).join("");
  const typeOptions = types.map((t) => `<option value="${t.id}" ${g && g.attr_id == t.id ? "selected" : ""}>${esc(t.name)}</option>`).join("");
  const typeMode = (t, def) => `<select name="type" class="form-select"><option value="once" ${gv("type", "once") === "once" ? "selected" : ""}>\u4E00\u5361\u4E00\u5BC6</option><option value="general" ${gv("type", "general") === "general" ? "selected" : ""}>\u901A\u7528\u5361\u5BC6</option><option value="service" ${gv("type", "service") === "service" ? "selected" : ""}>\u865A\u62DF\u670D\u52A1</option></select>`;
  const content = `<div class="card" style="max-width:920px;">
  <div class="card-body py-5">
  <form id="frm" method="post">
    <input type="hidden" name="id" value="${id}">
    <label class="form-label">\u5546\u54C1\u6807\u9898 *</label><input type="text" class="form-control" name="title" required value="${esc(gv("title", ""))}">
    <div class="row g-3 mt-1">
      <div class="col-md-3"><label class="form-label">\u6240\u5C5E\u5206\u7C7B</label><select class="form-select" name="sort_id">${sortOptions}</select></div>
      <div class="col-md-3"><label class="form-label">\u5546\u54C1\u6A21\u5F0F</label>${typeMode()}</div>
      <div class="col-md-3"><label class="form-label">\u89C4\u683C\u7C7B\u578B (\u591A\u89C4\u683C\u65F6\u9009\u62E9)</label><select class="form-select" name="attr_id"><option value="0">\u65E0</option>${typeOptions}</select></div>
      <div class="col-md-3"><label class="form-label">\u662F\u5426\u591A\u89C4\u683C</label><select class="form-select" name="is_sku"><option value="n" ${gv("is_sku", "n") === "n" ? "selected" : ""}>\u5426</option><option value="y" ${gv("is_sku", "y") === "y" ? "selected" : ""}>\u662F</option></select></div>
    </div>
    <div class="row g-3 mt-1">
      <div class="col-md-3"><label class="form-label">\u8BA1\u91CF\u5355\u4F4D</label><input type="text" class="form-control" name="unit_name" value="${esc(gv("unit_name", "\u4E2A"))}"></div>
      <div class="col-md-3"><label class="form-label">\u521D\u59CB\u5E93\u5B58</label><input type="number" class="form-control" name="stock" value="${gv("stock", 0)}" min="0"></div>
      <div class="col-md-3"><label class="form-label">\u521D\u59CB\u9500\u91CF</label><input type="number" class="form-control" name="sales" value="${gv("sales", 0)}" min="0"></div>
      <div class="col-md-3"><label class="form-label">\u4E0A\u67B6\u72B6\u6001</label><select class="form-select" name="is_on_shelf"><option value="1" ${gv("is_on_shelf", 1) == 1 ? "selected" : ""}>\u4E0A\u67B6</option><option value="0" ${gv("is_on_shelf", 0) == 0 ? "selected" : ""}>\u4E0B\u67B6</option></select></div>
    </div>
    <label class="form-label mt-3">\u5546\u54C1\u7B80\u4ECB</label><textarea class="form-control" name="des">${esc(gv("des", ""))}</textarea>
    <label class="form-label">\u5546\u54C1\u8BE6\u60C5 (HTML)</label><textarea class="form-control" name="content" style="min-height:140px;">${esc(gv("content", ""))}</textarea>
    <label class="form-label">\u8D2D\u4E70\u540E\u8BF4\u660E (HTML)</label><textarea class="form-control" name="pay_content">${esc(gv("pay_content", ""))}</textarea>
    <label class="form-label">\u5C01\u9762\u56FE URL</label><input type="text" class="form-control" name="cover" value="${esc(gv("cover", ""))}">
    <label class="form-label">\u89C4\u683C/SKU \u4E0E\u4EF7\u683C (\u6BCF\u884C: SKU\u7EC4\u5408|\u552E\u4EF7(\u5206)|\u5E02\u573A\u4EF7(\u5206)|\u5E93\u5B58, SKU\u7EC4\u5408\u5982 1-5 \u6216 0)</label>
    <textarea class="form-control" name="skus_text" style="min-height:120px;">${esc(skusText)}</textarea>
    <button type="submit" class="btn btn-success mt-4">\u4FDD \u5B58</button>
    <span class="text-danger" id="fmsg"></span>
  </form>
  </div>
  </div>
  <script>
  $('#frm').on('submit', function(e){
    e.preventDefault();
    $.post('/admin/goods/save', $(this).serialize(), function(r){
      if (r.code === 0) { location.href = '/admin/goods'; } else { $('#fmsg').text(r.msg); }
    }, 'json');
  });
  </script>`;
  return adminPage(request, env, id ? "\u7F16\u8F91\u5546\u54C1 #" + id : "\u65B0\u589E\u5546\u54C1", content);
}
async function saveGoods(form, env) {
  const id = parseInt(form.get("id")) || 0;
  const title = (form.get("title") || "").trim();
  if (!title) return json({ code: 1, msg: "\u6807\u9898\u4E0D\u80FD\u4E3A\u7A7A" });
  const sort_id = parseInt(form.get("sort_id")) || 0;
  const type = form.get("type") || "once";
  const attr_id = parseInt(form.get("attr_id")) || 0;
  const is_sku = form.get("is_sku") === "y" ? "y" : "n";
  const unit_name = (form.get("unit_name") || "\u4E2A").trim();
  const stock = parseInt(form.get("stock")) || 0;
  const sales = parseInt(form.get("sales")) || 0;
  const is_on_shelf = parseInt(form.get("is_on_shelf")) || 0;
  const des = form.get("des") || "";
  const content = form.get("content") || "";
  const pay_content = form.get("pay_content") || "";
  const cover = form.get("cover") || "";
  const t = now();
  if (id) {
    await env.DB.prepare("UPDATE dc_goods SET title=?, sort_id=?, type=?, attr_id=?, is_sku=?, unit_name=?, stock=?, sales=?, is_on_shelf=?, des=?, content=?, pay_content=?, cover=? WHERE id=?").bind(title, sort_id, type, attr_id, is_sku, unit_name, stock, sales, is_on_shelf, des, content, pay_content, cover, id).run();
    const gid = id;
    await env.DB.prepare("DELETE FROM dc_skus WHERE goods_id = ?").bind(gid).run();
    await insertSkus(env, gid, form.get("skus_text") || "");
  } else {
    const r = await env.DB.prepare("INSERT INTO dc_goods (title, sort_id, type, attr_id, is_sku, unit_name, stock, sales, is_on_shelf, des, content, pay_content, cover, create_time) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)").bind(title, sort_id, type, attr_id, is_sku, unit_name, stock, sales, is_on_shelf, des, content, pay_content, cover, t).run();
    const gid = r.meta.last_row_id;
    await insertSkus(env, gid, form.get("skus_text") || "0|1000|1200|100");
  }
  return json({ code: 0 });
}
async function insertSkus(env, gid, text) {
  const lines = String(text).split("\n").map((l) => l.trim()).filter((l) => l);
  const batch = [];
  for (const line of lines) {
    const p2 = line.split("|");
    if (p2.length < 1) continue;
    const sku = p2[0].trim();
    const guest = parseInt(p2[1]) || 0;
    const market = parseInt(p2[2]) || 0;
    const st = parseInt(p2[3]) || 0;
    batch.push(env.DB.prepare("INSERT INTO dc_skus (goods_id, sku, guest_price, market_price, stock) VALUES (?,?,?,?,?)").bind(gid, sku, guest, market, st));
  }
  if (batch.length) await env.DB.batch(batch);
}
async function kamiManage(request, env, q) {
  const gid = parseInt(q.goods_id) || 0;
  const goods = gid ? await env.DB.prepare("SELECT * FROM dc_goods WHERE id = ?").bind(gid).first() : null;
  if (!goods) {
    const goodsList2 = (await env.DB.prepare("SELECT id, title, type FROM dc_goods WHERE delete_time IS NULL AND type IN ('once','general') ORDER BY id DESC LIMIT 50").all()).results;
    const rows2 = goodsList2.map((g) => `<tr><td>${g.id}</td><td>${esc(g.title)}</td><td>${TNAME[g.type]}</td><td><a class="btn btn-primary btn-sm" href="/admin/kami?goods_id=${g.id}">\u7BA1\u7406\u5361\u5BC6</a></td></tr>`).join("");
    return adminPage(request, env, "\u5361\u5BC6\u7BA1\u7406", `<div class="card"><div class="card-body py-5"><div class="table-responsive"><table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3"><thead><tr class="fw-bold text-muted"><th>ID</th><th>\u5546\u54C1</th><th>\u6A21\u5F0F</th><th>\u64CD\u4F5C</th></tr></thead><tbody>${rows2 || '<tr><td colspan="4" class="text-center text-muted py-6">\u6682\u65E0\u5361\u5BC6\u7C7B\u5546\u54C1</td></tr>'}</tbody></table></div></div></div>`);
  }
  let rows = "";
  if (goods.type === "once") {
    const list = (await env.DB.prepare("SELECT * FROM dc_goods_once WHERE goods_id = ? ORDER BY id DESC LIMIT 200").bind(gid).all()).results;
    rows = list.map(
      (k) => `<tr><td>${k.id}</td><td>${k.sku}</td><td style="word-break:break-all;">${esc(k.content)}</td><td>${k.sale_time ? ts2str(k.sale_time) : '<span class="text-muted">\u672A\u552E\u51FA</span>'}</td><td><button class="btn btn-danger btn-sm" onclick="delK(${k.id})">\u5220\u9664</button></td></tr>`
    ).join("");
  } else if (goods.type === "general") {
    const list = (await env.DB.prepare("SELECT * FROM dc_goods_general WHERE goods_id = ? ORDER BY id DESC").bind(gid).all()).results;
    rows = list.map(
      (k) => `<tr><td>${k.id}</td><td>${k.sku}</td><td style="word-break:break-all;">${esc(k.content)}</td><td>${ts2str(k.create_time)}</td><td><button class="btn btn-danger btn-sm" onclick="delG(${k.id})">\u5220\u9664</button></td></tr>`
    ).join("");
  }
  const content = `<div class="card">
    <div class="card-body py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="card-title mb-0">${esc(goods.title)} <span class="text-muted fs-6">(${TNAME[goods.type]})</span></h3>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/goods">\u8FD4\u56DE\u5546\u54C1</a>
    </div>
    ${goods.type === "once" ? `<div class="row g-3 mb-4">
      <div class="col-md-6"><label class="form-label">\u65B0\u589E\u4E00\u5361\u4E00\u5BC6 (\u6BCF\u884C\u4E00\u6761\u5361\u5BC6)</label><textarea class="form-control" id="newKami" placeholder="KAMI-0001&#10;KAMI-0002"></textarea><button class="btn btn-success mt-2" onclick="addKami()">\u6DFB\u52A0</button></div>
      <div class="col-md-6"><label class="form-label">\u6279\u91CF\u5BFC\u5165 (\u6BCF\u884C\u4E00\u6761\u5361\u5BC6)</label><textarea class="form-control" id="importKami" placeholder="\u4E00\u884C\u4E00\u6761"></textarea><button class="btn btn-primary mt-2" onclick="impKami()">\u5BFC\u5165</button></div>
    </div>` : `<div class="row g-3 mb-4">
      <div class="col-md-6"><label class="form-label">\u65B0\u589E\u901A\u7528\u5361\u5BC6 (SKU|\u5185\u5BB9)</label><textarea class="form-control" id="newKami" placeholder="0|VIP-2026-0001"></textarea><button class="btn btn-success mt-2" onclick="addKami()">\u6DFB\u52A0</button></div>
    </div>`}
    <div class="table-responsive">
    <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3"><thead><tr class="fw-bold text-muted"><th>ID</th><th>SKU</th><th>\u5185\u5BB9</th>${goods.type === "once" ? "<th>\u552E\u51FA\u65F6\u95F4</th>" : "<th>\u6DFB\u52A0\u65F6\u95F4</th>"}<th>\u64CD\u4F5C</th></tr></thead><tbody>${rows || '<tr><td colspan="5" class="text-center text-muted py-6">\u6682\u65E0\u5361\u5BC6</td></tr>'}</tbody></table>
    </div>
    </div>
    </div>
  <script>
  function addKami(){ $.post('/admin/kami/add',{goods_id:${gid},text:$('#newKami').val()},function(r){ if(r.code===0) location.reload(); else alert(r.msg); },'json'); }
  function impKami(){ $.post('/admin/kami/import',{goods_id:${gid},text:$('#importKami').val()},function(r){ if(r.code===0) location.reload(); else alert(r.msg); },'json'); }
  function delK(id){ if(!confirm('\u786E\u8BA4\u5220\u9664\u8BE5\u5361\u5BC6\uFF1F')) return; $.post('/admin/kami/delete',{id:id,goods_id:${gid},type:'once'},function(r){ if(r.code===0) location.reload(); else alert(r.msg); },'json'); }
  function delG(id){ if(!confirm('\u786E\u8BA4\u5220\u9664\u8BE5\u5361\u5BC6\uFF1F')) return; $.post('/admin/kami/delete',{id:id,goods_id:${gid},type:'general'},function(r){ if(r.code===0) location.reload(); else alert(r.msg); },'json'); }
  </script>`;
  return adminPage(request, env, "\u5361\u5BC6\u7BA1\u7406", content);
}
async function addKami(form, env) {
  const gid = parseInt(form.get("goods_id")) || 0;
  const text = (form.get("text") || "").trim();
  if (!gid || !text) return json({ code: 1, msg: "\u53C2\u6570\u9519\u8BEF" });
  const goods = await env.DB.prepare("SELECT * FROM dc_goods WHERE id = ?").bind(gid).first();
  if (!goods) return json({ code: 1, msg: "\u5546\u54C1\u4E0D\u5B58\u5728" });
  const t = now();
  const batch = [];
  if (goods.type === "once") {
    for (const line of text.split("\n")) {
      const v = line.trim();
      if (v) batch.push(env.DB.prepare("INSERT INTO dc_goods_once (goods_id, sku, batch_no, content, create_time, order_list_id) VALUES (?,?,?,?,?,0)").bind(gid, "0", "manual", v, t));
    }
  } else if (goods.type === "general") {
    for (const line of text.split("\n")) {
      const v = line.trim();
      if (!v) continue;
      const p2 = v.split("|");
      const sku = p2.length > 1 ? p2[0].trim() : "0";
      const content = p2.length > 1 ? p2.slice(1).join("|").trim() : v;
      batch.push(env.DB.prepare("INSERT INTO dc_goods_general (goods_id, sku, content, create_time) VALUES (?,?,?,?)").bind(gid, sku, content, t));
    }
  }
  if (batch.length) {
    await env.DB.batch(batch);
    await recalcStock(env, gid);
  }
  return json({ code: 0, count: batch.length });
}
async function importKami(form, env) {
  return addKami(form, env);
}
async function delKami(form, env) {
  const id = parseInt(form.get("id")) || 0;
  const type = form.get("type") || "once";
  const gid = parseInt(form.get("goods_id")) || 0;
  if (type === "once") await env.DB.prepare("DELETE FROM dc_goods_once WHERE id = ?").bind(id).run();
  else await env.DB.prepare("DELETE FROM dc_goods_general WHERE id = ?").bind(id).run();
  if (gid) await recalcStock(env, gid);
  return json({ code: 0 });
}
async function recalcStock(env, gid) {
  const goods = await env.DB.prepare("SELECT type FROM dc_goods WHERE id = ?").bind(gid).first();
  if (!goods) return;
  let stock = 0;
  if (goods.type === "once") stock = (await env.DB.prepare("SELECT COUNT(*) AS c FROM dc_goods_once WHERE goods_id = ? AND sale_time IS NULL").bind(gid).first()).c;
  if (goods.type === "general") stock = (await env.DB.prepare("SELECT COUNT(*) AS c FROM dc_goods_general WHERE goods_id = ?").bind(gid).first()).c;
  await env.DB.prepare("UPDATE dc_goods SET stock = ? WHERE id = ?").bind(stock, gid).run();
  const skus = (await env.DB.prepare("SELECT * FROM dc_skus WHERE goods_id = ?").bind(gid).all()).results;
  if (skus.length === 1) await env.DB.prepare("UPDATE dc_skus SET stock = ? WHERE id = ?").bind(stock, skus[0].id);
}
async function ordersList(request, env, q) {
  const kw = (q.q || "").trim();
  let sql = "SELECT o.* FROM dc_order o WHERE 1=1";
  const binds = [];
  if (kw) {
    sql += " AND (o.out_trade_no LIKE ? OR o.tel LIKE ? OR o.email LIKE ?)";
    binds.push("%" + kw + "%", "%" + kw + "%", "%" + kw + "%");
  }
  sql += " ORDER BY o.id DESC LIMIT 100";
  const orders = (await env.DB.prepare(sql).bind(...binds).all()).results;
  const statusText = { 0: "\u5F85\u652F\u4ED8", 1: "\u5DF2\u652F\u4ED8\u5F85\u53D1\u8D27", 2: "\u5DF2\u5B8C\u6210", 3: "\u5DF2\u53D6\u6D88" };
  const rows = orders.map(
    (o) => `<tr>
    <td>${o.id}</td><td>${esc(o.out_trade_no)}</td><td>\xA5${fen2yuan(o.amount)}</td>
    <td>${ts2str(o.create_time)}</td><td>${esc(o.payment || "-")}</td>
    <td>${statusText[o.status] || o.status}</td>
    <td><a class="btn btn-light-primary btn-sm" href="/?action=order_result&out_trade_no=${esc(o.out_trade_no)}" target="_blank">\u8BE6\u60C5</a>
    ${o.pay_status == 1 ? `<button class="btn btn-danger btn-sm" onclick="refund(${o.id},${JSON.stringify(o.out_trade_no).replace(/"/g, "&quot;")})">\u9000\u6B3E</button>` : ""}
    <button class="btn btn-outline-secondary btn-sm" onclick="delO(${o.id})">\u5220\u9664</button></td>
  </tr>`
  ).join("");
  const content = `<div class="card">
  <div class="card-body py-5">
  <form action="/admin/orders" method="get" class="d-flex gap-2 mb-4"><input type="text" name="q" class="form-control" placeholder="\u8BA2\u5355\u53F7/\u8054\u7CFB\u65B9\u5F0F" value="${esc(kw)}" style="width:260px;"><button class="btn btn-outline-secondary">\u641C\u7D22</button></form>
  <div class="table-responsive">
  <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3"><thead><tr class="fw-bold text-muted"><th>ID</th><th>\u8BA2\u5355\u53F7</th><th>\u91D1\u989D</th><th>\u65F6\u95F4</th><th>\u652F\u4ED8</th><th>\u72B6\u6001</th><th>\u64CD\u4F5C</th></tr></thead>
  <tbody>${rows || '<tr><td colspan="7" class="text-center text-muted py-6">\u6682\u65E0\u8BA2\u5355</td></tr>'}</tbody></table>
  </div>
  </div>
  </div>
  <script>
  function refund(id){ if(!confirm('\u786E\u8BA4\u9000\u6B3E\uFF1F')) return; $.post('/admin/order/refund',{id:id},function(r){ if(r.code===0) location.reload(); else alert(r.msg); },'json'); }
  function delO(id){ if(!confirm('\u786E\u8BA4\u5220\u9664\u8BA2\u5355\uFF1F')) return; $.post('/admin/order/delete',{id:id},function(r){ if(r.code===0) location.reload(); else alert(r.msg); },'json'); }
  </script>`;
  return adminPage(request, env, "\u8BA2\u5355\u7BA1\u7406", content);
}
async function refundOrder(form, env) {
  const id = parseInt(form.get("id")) || 0;
  const order = await env.DB.prepare("SELECT * FROM dc_order WHERE id = ?").bind(id).first();
  if (!order) return json({ code: 1, msg: "\u8BA2\u5355\u4E0D\u5B58\u5728" });
  if (order.pay_status != 1) return json({ code: 1, msg: "\u4EC5\u5DF2\u652F\u4ED8\u8BA2\u5355\u53EF\u9000\u6B3E" });
  const t = now();
  if (order.pay_plugin === "balance" && order.user_id) {
    await env.DB.prepare("UPDATE dc_user SET money = round((money * 100 + ?) / 100.0, 2) WHERE uid = ?").bind(order.amount, order.user_id).run();
  }
  await env.DB.prepare("UPDATE dc_order SET status = 3, delete_time = ? WHERE id = ?").bind(t, id).run();
  return json({ code: 0, msg: "\u5DF2\u9000\u6B3E" });
}
async function del(table, key, form, env) {
  const id = parseInt(form.get("id")) || 0;
  if (!id) return json({ code: 1, msg: "\u53C2\u6570\u9519\u8BEF" });
  await env.DB.prepare("DELETE FROM " + table + " WHERE " + key + " = ?").bind(id).run();
  return json({ code: 0 });
}
async function sortsList(request, env, q) {
  const sorts = (await env.DB.prepare("SELECT * FROM dc_sort WHERE type = 'goods' AND delete_time IS NULL ORDER BY taxis ASC").all()).results;
  const rows = sorts.map((s) => `<tr><td>${s.sid}</td><td>${esc(s.sortname)}</td><td>${esc(s.alias)}</td><td>${s.taxis}</td><td>${esc(s.sorticon || "-")}</td><td><button class="btn btn-danger btn-sm" onclick="delS(${s.sid})">\u5220\u9664</button></td></tr>`).join("");
  const content = `<div class="card">
  <div class="card-body py-5">
  <div class="row g-3 mb-4 align-items-end">
    <div class="col-md-3"><label class="form-label">\u5206\u7C7B\u540D\u79F0</label><input type="text" class="form-control" id="sName"></div>
    <div class="col-md-3"><label class="form-label">\u522B\u540D (alias)</label><input type="text" class="form-control" id="sAlias"></div>
    <div class="col-md-2"><label class="form-label">\u6392\u5E8F (\u5C0F=\u524D)</label><input type="number" class="form-control" id="sTaxis" value="0"></div>
    <div class="col-md-3"><label class="form-label">\u56FE\u6807 (remixicon \u7C7B\u540D)</label><input type="text" class="form-control" id="sIcon" placeholder="ri-gamepad-line"></div>
    <div class="col-md-1"><button class="btn btn-success w-100" onclick="addS()">\u65B0\u589E\u5206\u7C7B</button></div>
  </div>
  <div class="table-responsive">
  <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3"><thead><tr class="fw-bold text-muted"><th>ID</th><th>\u540D\u79F0</th><th>\u522B\u540D</th><th>\u6392\u5E8F</th><th>\u56FE\u6807</th><th>\u64CD\u4F5C</th></tr></thead>
  <tbody>${rows || '<tr><td colspan="6" class="text-center text-muted py-6">\u6682\u65E0\u5206\u7C7B</td></tr>'}</tbody></table>
  </div>
  </div>
  </div>
  <script>
  function addS(){ $.post('/admin/sort/save',{name:$('#sName').val(),alias:$('#sAlias').val(),taxis:$('#sTaxis').val(),icon:$('#sIcon').val()},function(r){ if(r.code===0) location.reload(); else alert(r.msg); },'json'); }
  function delS(id){ if(!confirm('\u786E\u8BA4\u5220\u9664\u8BE5\u5206\u7C7B\uFF1F')) return; $.post('/admin/sort/delete',{sid:id},function(r){ if(r.code===0) location.reload(); else alert(r.msg); },'json'); }
  </script>`;
  return adminPage(request, env, "\u5206\u7C7B\u7BA1\u7406", content);
}
async function saveSort(form, env) {
  const name = (form.get("name") || "").trim();
  if (!name) return json({ code: 1, msg: "\u5206\u7C7B\u540D\u4E0D\u80FD\u4E3A\u7A7A" });
  const alias = (form.get("alias") || "").trim();
  const taxis = parseInt(form.get("taxis")) || 0;
  const icon = (form.get("icon") || "").trim();
  await env.DB.prepare("INSERT INTO dc_sort (type, sortname, alias, taxis, sorticon, station_id) VALUES ('goods',?,?,?,?,0)").bind(name, alias, taxis, icon).run();
  return json({ code: 0 });
}
async function settingsPage(request, env) {
  const opts = await getOptions(env.DB);
  const g = (k, def) => opt(opts, k, def);
  const theme = await env.DB.prepare("SELECT data FROM dc_tpl_options_data WHERE template = 'front_default' AND name = 'theme_primary' LIMIT 1").first();
  let primary = "#2196F3";
  try {
    if (theme && theme.data) primary = JSON.parse(theme.data.replace(/^s:\d+:"|";$/, (m) => m[0] === "s" ? "" : ""));
  } catch (e) {
  }
  const content = `<div class="card" style="max-width:820px;">
  <div class="card-body py-5">
  <form id="frm">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">\u7AD9\u70B9\u540D\u79F0</label><input type="text" class="form-control" name="blogname" value="${esc(g("blogname", "ACG\u53D1\u5361"))}"></div>
      <div class="col-md-6"><label class="form-label">\u526F\u6807\u9898</label><input type="text" class="form-control" name="site_subtitle" value="${esc(g("site_subtitle", ""))}"></div>
    </div>
    <label class="form-label mt-3">\u9875\u811A\u4FE1\u606F (\u652F\u6301 HTML)</label><textarea class="form-control" name="footer_info" style="min-height:60px;">${esc(g("footer_info", ""))}</textarea>
    <label class="form-label">\u6EDA\u52A8\u516C\u544A (\u591A\u884C: \u6BCF\u884C\u4E00\u6761)</label><textarea class="form-control" name="roll_bulletin">${esc(g("roll_bulletin", ""))}</textarea>
    <label class="form-label">\u9996\u9875\u516C\u544A (HTML)</label><textarea class="form-control" name="home_bulletin" style="min-height:90px;">${esc(g("home_bulletin", ""))}</textarea>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">\u67E5\u5355\u5FC5\u586B\u8BBE\u7F6E (JSON)</label><input type="text" class="form-control" name="order_required" value="${esc(g("order_required", ""))}"></div>
      <div class="col-md-6"><label class="form-label">\u4E3B\u9898\u4E3B\u8272</label><input type="color" class="form-control form-control-color" name="theme_primary" value="${esc(primary)}" style="height:38px;width:60px;padding:2px;"></div>
    </div>
    <button type="submit" class="btn btn-success mt-4">\u4FDD\u5B58\u8BBE\u7F6E</button>
    <span class="text-danger ms-3" id="fmsg"></span>
  </form>
  </div>
  </div>
  <script>
  $('#frm').on('submit', function(e){
    e.preventDefault();
    $.post('/admin/settings/save', $(this).serialize(), function(r){
      if (r.code === 0) { $('#fmsg').text('\u5DF2\u4FDD\u5B58').addClass('ok'); setTimeout(function(){ location.reload(); }, 500); }
      else { $('#fmsg').text(r.msg); }
    }, 'json');
  });
  </script>`;
  return adminPage(request, env, "\u7AD9\u70B9\u8BBE\u7F6E", content);
}
async function saveSettings(form, env) {
  const keys = ["blogname", "site_subtitle", "footer_info", "roll_bulletin", "home_bulletin", "order_required"];
  const batch = [];
  for (const k of keys) {
    const v = form.get(k) || "";
    await env.DB.prepare("INSERT INTO dc_options (option_name, option_value) VALUES (?, ?) ON CONFLICT(option_name) DO UPDATE SET option_value = excluded.option_value").bind(k, v).run();
  }
  const themePrimary = form.get("theme_primary") || "#2196F3";
  await env.DB.prepare("UPDATE dc_tpl_options_data SET data = ? WHERE template = 'front_default' AND name = 'theme_primary'").bind("s:" + themePrimary.length + ':"' + themePrimary + '";').run();
  return json({ code: 0 });
}

// worker.js
var worker_default = {
  async fetch(request, env, ctx2) {
    try {
      return await router(request, env);
    } catch (e) {
      return new Response("Internal Error: " + esc(e.message), { status: 500, headers: { "content-type": "text/plain; charset=utf-8" } });
    }
  }
};
var JSON_HEADERS2 = { "content-type": "application/json; charset=utf-8" };
var HTML_HEADERS2 = { "content-type": "text/html; charset=utf-8" };
async function serveAsset(request, env) {
  if (env.ASSETS) {
    const res = await env.ASSETS.fetch(request);
    if (res.status !== 404) return res;
  }
  return null;
}
async function router(request, env) {
  const url = new URL(request.url);
  const path = url.pathname;
  const q = parseQuery(url.search);
  if (/^\/(css|js|img|vendor|assets|favicon\.ico|robots\.txt)/.test(path)) {
    const a = await serveAsset(request, env);
    if (a) return a;
    return new Response("Not Found", { status: 404 });
  }
  if (path === "/admin" || path.startsWith("/admin/")) {
    return handleAdmin(request, env);
  }
  const action = q.action || "";
  if (request.method === "POST") {
    if (action === "xiadan") return apiXiadan(request, env);
    if (action === "pay_submit") return apiPaySubmit(request, env);
    if (action === "order_cancel") return apiOrderCancel(request, env);
    if (action === "order_query") return apiOrderQuery(request, env);
    if (action === "login") return apiLogin(request, env);
  }
  if (request.method === "GET") {
    if (action === "goods") return pageGoods(request, env, q);
    if (action === "pay") return pagePay(request, env, q);
    if (action === "order_result") return pageOrderResult(request, env, q);
    if (action === "order_kami") return apiKami(request, env, q);
    if (action === "order_query") return q && (q.q || "").trim() ? apiOrderQuery(request, env) : pageOrderQuery(request, env, q);
    if (action === "help") return pageHelp(request, env, q);
    if (action === "search") return pageHome(request, env, q, true);
    if (action === "user") return pageUser(request, env, q);
    if (action === "rss") return pageRss(request, env, q);
  }
  return pageHome(request, env, q, false);
}
async function ctx(env) {
  const opts = await getOptions(env.DB);
  if (env) env._opts = opts;
  const navItems = await buildNav(env.DB, "/");
  return { opts, navItems };
}
async function pageHome(request, env, q, isSearch) {
  const { opts, navItems } = await ctx(env);
  const page = Math.max(1, parseInt(q.page) || 1);
  const pageSize = 12;
  const sortId = parseInt(q.sort_id) || 0;
  const kw = (q.q || "").trim();
  const order = q.order || "default";
  let where = "WHERE g.delete_time IS NULL AND g.is_on_shelf = 1";
  const binds = [];
  if (sortId > 0) {
    where += " AND (g.sort_id = ? OR g.sort_id IN (SELECT sid FROM dc_sort WHERE pid = ?))";
    binds.push(sortId, sortId);
  }
  if (kw) {
    where += " AND (g.title LIKE ? OR g.des LIKE ?)";
    binds.push("%" + kw + "%", "%" + kw + "%");
  }
  const orderMap = {
    default: "ORDER BY g.index_top DESC, g.sort_num DESC, g.id DESC",
    sales: "ORDER BY g.sales DESC",
    sales_asc: "ORDER BY g.sales ASC",
    price_asc: "ORDER BY g.id ASC",
    price_desc: "ORDER BY g.id DESC",
    stock: "ORDER BY g.stock DESC",
    stock_asc: "ORDER BY g.stock ASC"
  };
  const orderSql = orderMap[order] || orderMap.default;
  const { results: goods } = await env.DB.prepare(
    "SELECT g.*, s.sortname, (SELECT MIN(guest_price) FROM dc_skus WHERE goods_id = g.id) AS _price FROM dc_goods g LEFT JOIN dc_sort s ON s.sid = g.sort_id " + where + " " + orderSql + " LIMIT ? OFFSET ?"
  ).bind(...binds, pageSize, (page - 1) * pageSize).all();
  const { results: cnt } = await env.DB.prepare("SELECT COUNT(*) AS c FROM dc_goods g " + where).bind(...binds).all();
  const total = cnt[0] ? cnt[0].c : 0;
  const pages = Math.max(1, Math.ceil(total / pageSize));
  const sorts = await getSorts(env.DB);
  const categories = sorts.slice(0, 12);
  let gridHtml = "";
  for (const g of goods) {
    gridHtml += goodsCardHtml(g, env);
  }
  if (!goods.length) {
    gridHtml = `<div class="item-message">${kw ? "\u6CA1\u6709\u641C\u7D22\u5230\u76F8\u5173\u5546\u54C1" : "\u6682\u65E0\u5546\u54C1"}</div>`;
  }
  const homeBulletin = opt(opts, "home_bulletin", "");
  const allChip = `<a data-id="0" class="switch-category chip ${sortId === 0 ? "is-primary" : ""}" href="/"><span class="chip-icon"><i class="fa-duotone fa-regular fa-shapes"></i></span>\u5168\u90E8</a>`;
  const sortChips = sorts.map(
    (s) => `<a data-id="${s.sid}" class="switch-category chip ${sortId === s.sid ? "is-primary" : ""}" href="/?sort_id=${s.sid}">${s.sorticon ? `<span class="chip-icon"><i class="${esc(s.sorticon)}"></i></span>` : ""}${esc(s.sortname)}</a>`
  ).join("");
  const body = `
  <main class="container py-4">
    ${homeBulletin ? `<div class="panel">
      <div class="panel-header">
        <span class="icon"><i class="fa-duotone fa-regular fa-bullhorn"></i></span>
        <h6 class="panel-title">\u516C\u544A</h6>
      </div>
      <div class="panel-body">${homeBulletin}</div>
    </div>` : ""}
    <div class="panel">
      <div class="panel-header">
        <span class="icon"><i class="fa-duotone fa-regular fa-cart-shopping"></i></span>
        <h6 class="panel-title">\u8D2D\u4E70</h6>
      </div>
      <div class="panel-body">
        <div class="mb-3">
          <div class="chip-list">${allChip}${sortChips}</div>
        </div>
        <div class="row item-list">${gridHtml}</div>
        ${paginationHtml("/?action=index" + (sortId ? "&sort_id=" + sortId : "") + (kw ? "&q=" + encodeURIComponent(kw) : ""), page, pages, total)}
      </div>
    </div>
  </main>`;
  return new Response(layout(env, { ...opts, title: kw ? "\u641C\u7D22 - " + kw : opt(opts, "site_title", "ACG\u53D1\u5361\u7CFB\u7EDF") }, navItems, body), { headers: HTML_HEADERS2 });
}
function goodsCardHtml(g, env) {
  const typeBadgeCls = TYPE_BADGE[g.type] || "";
  const soldOut = parseInt(g.stock) <= 0;
  const cover = g.cover && !g.cover.startsWith("../") ? g.cover : "";
  const stockSwitch = opt(env._opts, "stock_switch", "y") === "y";
  const salesSwitch = opt(env._opts, "sales_switch", "y") === "y";
  return `<a href="${soldOut ? "javascript:void(0);" : "/?action=goods&id=" + g.id}" class="col-12 col-md-6 col-lg-3 mb-3" data-id="${g.id}">
  <div class="acg-card ${soldOut ? "soldout" : ""} h-100">
    <div class="acg-thumb" style="background: url('${esc(cover)}') center/cover no-repeat;"></div>
    <div class="p-3">
      <div class="tags">
        ${typeBadgeCls ? `<span class="badge-soft badge-soft-primary">${TYPE_NAME[g.type] || g.type}</span>` : ""}
        <span class="badge-soft badge-soft-success">\u81EA\u52A8\u53D1\u8D27</span>
        ${parseInt(g.index_top) > 0 ? '<span class="badge-soft badge-soft-primary">\u63A8\u8350</span>' : ""}
      </div>
      <p class="goods-title">${esc(g.title)}</p>
      <div class="stat-row mb-1">
        <div class="price"><span class="unit">\xA5</span>${fen2yuan(g._price || 0)}</div>
      </div>
      <div class="stat-bottom">
        ${stockSwitch ? `<span>\u5E93\u5B58\uFF1A${g.stock}</span>` : ""}
        ${salesSwitch ? `<span>\u5DF2\u552E\uFF1A${g.sales}</span>` : ""}
      </div>
    </div>
    ${soldOut ? '<div class="soldout-ribbon">\u552E\u7F44</div>' : ""}
  </div>
</a>`;
}
async function pageGoods(request, env, q) {
  const { opts, navItems } = await ctx(env);
  const id = parseInt(q.id) || 0;
  if (!id) return new Response("\u5546\u54C1\u4E0D\u5B58\u5728", { status: 404, headers: HTML_HEADERS2 });
  const g = await env.DB.prepare("SELECT * FROM dc_goods WHERE id = ? AND delete_time IS NULL LIMIT 1").bind(id).first();
  if (!g) return new Response('<h2 style="text-align:center;padding:120px 0;">\u5546\u54C1\u4E0D\u5B58\u5728\u6216\u5DF2\u4E0B\u67B6</h2><p style="text-align:center;"><a href="/">\u8FD4\u56DE\u9996\u9875</a></p>', { status: 404, headers: HTML_HEADERS2 });
  const skus = await getSkus(env.DB, id);
  const price = skus.length ? Math.min(...skus.map((s) => s.guest_price || 0)) : 0;
  const stock = skus.length ? skus.reduce((a, s) => a + (parseInt(s.stock) || 0), 0) : 0;
  const gallery = (() => {
    try {
      const arr = JSON.parse(g.gallery || "[]");
      return arr.filter((x) => x && !x.startsWith("../")).map((x) => x);
    } catch (e) {
      return [];
    }
  })();
  const cover = g.cover && !g.cover.startsWith("../") ? g.cover : gallery[0] || "";
  const attachUser = (() => {
    try {
      return JSON.parse(g.attach_user || "[]");
    } catch (e) {
      return [];
    }
  })();
  const isSku = g.is_sku === "y";
  const specGroups = [];
  if (isSku && g.attr_id) {
    const { results: attrs } = await env.DB.prepare("SELECT * FROM dc_sku_attr WHERE type_id = ? AND delete_time IS NULL ORDER BY id ASC").bind(g.attr_id).all();
    const { results: vals } = await env.DB.prepare("SELECT * FROM dc_sku_value WHERE delete_time IS NULL ORDER BY id ASC").all();
    for (const a of attrs) {
      const group = { title: a.title, options: [] };
      for (const v of vals) {
        if (v.attr_id === a.id) group.options.push({ id: v.id, name: v.name });
      }
      if (group.options.length) specGroups.push(group);
    }
  }
  const orderRequired = (() => {
    try {
      return JSON.parse(opt(opts, "order_required", "[]") || "[]");
    } catch (e) {
      return [];
    }
  })();
  const specsData = skus.map((s) => ({ sku: s.sku, price: s.guest_price, market: s.market_price, stock: s.stock, sales: s.sales }));
  const specHtml = specGroups.map(
    (grp, gi) => `<div>
      <label class="form-label mb-1">${esc(grp.title)}</label>
      <div class="sku-list">
        ${grp.options.map((o, oi) => `<a class="switch-race sku spec-option ${oi === 0 ? "is-primary" : ""}" data-id="${o.id}" data-group="${gi}" href="javascript:void(0);">${esc(o.name)}</a>`).join("")}
      </div>
    </div>`
  ).join("");
  const attachHtml = attachUser.map(
    (f, i) => `<div>
      <label class="form-label mb-1">${f.required ? '<span style="color:#f56c6c;">*</span>' : ""}${esc(f.name)}</label>
      <input class="form-control ${f.required ? "required-input" : ""}" name="attach[${esc(f.name)}]" placeholder="${esc(f.placeholder || "")}" data-validate-type="${esc(f.type || "string")}">
      ${f.tip ? `<div class="form-text">${esc(f.tip)}</div>` : ""}
    </div>`
  ).join("");
  const requiredHtml = orderRequired.map(
    (f, i) => `<div>
      <label class="form-label mb-1"><span style="color:#f56c6c;">*</span>${esc(f.name || "\u8054\u7CFB\u4FE1\u606F")}</label>
      <input class="form-control required-input" name="required[${esc(f.name || "\u8054\u7CFB\u4FE1\u606F")}]" placeholder="${esc(f.placeholder || "")}" data-validate-type="${esc(f.type || "string")}">
    </div>`
  ).join("");
  const paymentMethods = paymentMethodsHtml(opts);
  const body = `
  <main class="container py-4">
    <div class="panel mt-3">
      <div class="panel-body">
        <div class="row g-4 align-items-stretch">
          <div class="col-12 col-lg-6 d-flex">
            <div class="acg-card h-100 w-100 flex-fill acg-cover">
              ${cover ? `<img src="${esc(cover)}" class="item-cover" alt="${esc(g.title)}">` : `<div class="d-flex align-items-center justify-content-center h-100 text-muted"><i class="fa-duotone fa-regular fa-image" style="font-size:64px;"></i></div>`}
            </div>
          </div>
          <div class="col-12 col-lg-6 d-flex">
            <div class="flex-fill">
              <h4>${esc(g.title)}</h4>
              <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <span class="badge-soft badge-soft-success">\u81EA\u52A8\u53D1\u8D27</span>
                <span class="badge-soft badge-soft-primary">\u5DF2\u552E ${g.sales}</span>
                <span class="badge-soft badge-soft-success item-stock">\u5E93\u5B58 <span id="goodsStock">${stock}</span></span>
              </div>
              <div class="d-flex align-items-baseline gap-2 mb-3 abacus">
                <div class="price"><span class="unit">\xA5</span><span id="unitPrice">${fen2yuan(price)}</span></div>
                <del class="text-muted" id="marketPrice" style="font-size:14px;"></del>
              </div>
              <form method="post" class="vstack gap-3" id="buyFormSection">
                ${specHtml}
                <div id="inputFields">
                  ${attachHtml}
                  ${requiredHtml}
                </div>
                <div>
                  <label class="form-label mb-1">\u8D2D\u4E70\u6570\u91CF</label>
                  <div class="input-group qty-group" style="width:170px;">
                    <button type="button" class="btn btn-outline-secondary change-num-sub" id="qtyMinus">-</button>
                    <input type="number" class="form-control text-center" id="qtyInput" name="num" value="1" min="1">
                    <button type="button" class="btn btn-outline-secondary change-num-add" id="qtyPlus">+</button>
                  </div>
                </div>
                <div class="cash-pay p-2" style="border:1px dashed #dee2e6;border-radius:12px;">
                  <label class="form-label mb-2"><i class="fa-duotone fa-regular fa-cart-shopping"></i> \u4ED8\u6B3E</label>
                  ${paymentMethods}
                </div>
                <div>
                  <button type="button" class="btn btn-primary br-12 w-100" id="submitPayBtn" style="padding:12px;font-size:16px;">\u7ACB\u5373\u8D2D\u4E70\uFF08\u5408\u8BA1 \xA5<span id="totalPrice">${fen2yuan(price)}</span>\uFF09</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    ${g.content ? `<div class="panel mt-3 item-detail">
      <div class="panel-header">
        <span class="icon"><i class="fa-duotone fa-regular fa-memo-circle-info"></i></span>
        <h6 class="panel-title">\u5B9D\u8D1D\u8BE6\u60C5</h6>
      </div>
      <div class="panel-body">${g.content}</div>
    </div>` : ""}
  </main>
  <script>
  (function(){
    var GOODS = ${JSON.stringify({ id: g.id, is_sku: isSku, attrs: specGroups, skus: specsData, unit: g.unit_name || "\u4E2A", stock, min_price: price })};
    var selected = [];
    function currentSku(){
      if (!GOODS.is_sku) return '0';
      return selected.join('-') || '0';
    }
    function findSku(sk){
      for (var i=0;i<GOODS.skus.length;i++){ if (GOODS.skus[i].sku === sk) return GOODS.skus[i]; }
      return null;
    }
    function updateSku(){
      var sk = currentSku();
      var row = findSku(sk);
      var price = row ? (row.price||0) : (GOODS.is_sku ? 0 : GOODS.min_price);
      $('#unitPrice').text((price/100).toFixed(2));
      $('#marketPrice').text(row && row.market ? '\xA5' + (row.market/100).toFixed(2) : '');
      var st = row ? (parseInt(row.stock)||0) : GOODS.stock;
      $('#goodsStock').text(st);
      var qty = parseInt($('#qtyInput').val()) || 1;
      if (st > 0 && qty > st) { qty = st; $('#qtyInput').val(st); }
      $('#totalPrice').text((price*qty/100).toFixed(2));
    }
    $('.spec-option').on('click', function(){
      var $t = $(this);
      var gid = $t.data('group');
      $('.spec-option[data-group="'+gid+'"]').removeClass('is-primary');
      $t.addClass('is-primary');
      while (selected.length <= gid) { selected.push(undefined); }
      selected[gid] = String($t.data('id'));
      var skArr = [];
      for (var i=0;i<GOODS.attrs.length;i++){
        var v = selected[i];
        if (v === undefined || v === null) { skArr = []; break; }
        skArr.push(v);
      }
      selected = skArr;
      updateSku();
    });
    $('#qtyMinus').on('click', function(){ var v = parseInt($('#qtyInput').val())||1; if (v>1){ $('#qtyInput').val(v-1); } updateSku(); });
    $('#qtyPlus').on('click', function(){ var v = parseInt($('#qtyInput').val())||1; var st = parseInt($('#goodsStock').text())||9999; if (v<st){ $('#qtyInput').val(v+1); } updateSku(); });
    $('#qtyInput').on('change', updateSku);
    var paying = false;
    $('#submitPayBtn').on('click', function(){
      if (paying) return; paying = true;
      var qty = parseInt($('#qtyInput').val())||1;
      var sku = currentSku();
      var fd = { goods_id: GOODS.id, quantity: qty, sku_ids: sku === '0' ? [] : sku.split('-') };
      $('.pay-list .pay.is-primary').each(function(){ fd.payment_plugin = $(this).data('method'); });
      $('#inputFields input').each(function(){
        var n = $(this).attr('name'); if (!n) return;
        if (n.indexOf('attach[') === 0) {
          var key = n.replace('attach[','').replace(']','');
          if (!fd.attach) fd.attach = {};
          fd.attach[key] = $(this).val();
        } else if (n.indexOf('required[') === 0) {
          var key2 = n.replace('required[','').replace(']','');
          if (!fd.required) fd.required = {};
          fd.required[key2] = $(this).val();
        }
      });
      // \u7B80\u5355\u6821\u9A8C\u5FC5\u586B
      var miss = [];
      $('#inputFields .required-input').each(function(){ if (!$(this).val()) miss.push($(this).attr('name')); });
      if (miss.length) { paying=false; layer.msg('\u8BF7\u586B\u5199\u5FC5\u586B\u9879'); return; }
      $.post('/?action=xiadan', fd, function(res){
        if (res.code === 0) { location.href = '/?action=pay&out_trade_no=' + res.out_trade_no; }
        else { paying=false; layer.msg(res.msg || '\u4E0B\u5355\u5931\u8D25'); }
      }, 'json').fail(function(){ paying=false; layer.msg('\u7F51\u7EDC\u9519\u8BEF'); });
    });
  })();
  </script>`;
  return new Response(layout(env, { ...opts, title: g.title }, navItems, body), { headers: HTML_HEADERS2 });
}
function paymentMethodsHtml(opts) {
  const balanceSwitch = opt(opts, "balance_switch", "y");
  const pays = [];
  if (balanceSwitch === "y") pays.push({ method: "balance", name: "\u4F59\u989D\u652F\u4ED8", icon: "fa-duotone fa-regular fa-wallet" });
  pays.push({ method: "test", name: "\u6D4B\u8BD5\u652F\u4ED8\uFF08\u6A21\u62DF\uFF09", icon: "fa-duotone fa-regular fa-shield-halved" });
  if (epayConfig(opts)) {
    pays.push({ method: "epay_wx", name: "\u5FAE\u4FE1\u652F\u4ED8", img: "/assets/user/images/cash/wechat.png" });
    pays.push({ method: "epay_ali", name: "\u652F\u4ED8\u5B9D", img: "/assets/user/images/cash/alipay.png" });
  }
  const items = pays.map((p2, i) => `<a class="pay ${i === 0 ? "is-primary" : ""}" data-method="${p2.method}">${p2.img ? `<img src="${p2.img}" alt="">` : `<i class="${p2.icon}"></i>`}<span>${p2.name}</span></a>`).join("");
  return `<div class="pay-list">
  ${items}
  </div>
  <style>.pay-list .pay{cursor:pointer;}</style>
  <script>$(function(){ $('.pay-list .pay').on('click', function(){ $('.pay-list .pay').removeClass('is-primary'); $(this).addClass('is-primary'); }); });</script>`;
}
function epayConfig(opts) {
  try {
    const s = JSON.parse(opt(opts, "_epay_config", "null") || "null");
    return s && s.api_url ? s : null;
  } catch (e) {
    return null;
  }
}
async function apiXiadan(request, env) {
  const form = await request.formData();
  const goodsId = parseInt(form.get("goods_id")) || 0;
  const qty = Math.max(1, parseInt(form.get("quantity")) || 1);
  let skuIds = form.getAll("sku_ids[]");
  if (!skuIds.length && form.get("sku_ids")) skuIds = String(form.get("sku_ids")).split(",");
  const sku = skuIds.filter((x) => x).join("-") || "0";
  const payPlugin = form.get("payment_plugin") || "test";
  const attachRaw = {};
  const requiredRaw = {};
  for (const [k, v] of form.entries()) {
    if (k.startsWith("attach[")) attachRaw[k.slice(7, -1)] = String(v);
    if (k.startsWith("required[")) requiredRaw[k.slice(9, -1)] = String(v);
  }
  const g = await env.DB.prepare("SELECT * FROM dc_goods WHERE id = ? AND is_on_shelf = 1 AND delete_time IS NULL LIMIT 1").bind(goodsId).first();
  if (!g) return json2({ code: 1, msg: "\u5546\u54C1\u4E0D\u5B58\u5728\u6216\u5DF2\u4E0B\u67B6" });
  const skuRow = await env.DB.prepare("SELECT * FROM dc_skus WHERE goods_id = ? AND sku = ? LIMIT 1").bind(goodsId, sku).first();
  if (!skuRow) return json2({ code: 1, msg: "\u89C4\u683C\u4E0D\u5B58\u5728" });
  const unitPrice = skuRow.user_price && skuRow.user_price > 0 ? skuRow.user_price : skuRow.guest_price;
  const stockNow = parseInt(skuRow.stock) || 0;
  let avail = stockNow > 0 ? stockNow : 999999;
  if (g.type === "once") {
    const cnt = await env.DB.prepare("SELECT COUNT(*) AS c FROM dc_goods_once WHERE goods_id = ? AND sku = ? AND sale_time IS NULL").bind(goodsId, sku).first();
    avail = Math.min(avail, parseInt(cnt.c) || 0);
  }
  if (g.type === "general") {
    const r = await env.DB.prepare("SELECT COUNT(*) AS c FROM dc_goods_general WHERE goods_id = ? AND sku = ?").bind(goodsId, sku).first();
    if (!parseInt(r.c)) return json2({ code: 1, msg: "\u8BE5\u89C4\u683C\u65E0\u5361\u5BC6\u5E93\u5B58" });
    avail = stockNow > 0 ? Math.min(avail, stockNow) : avail;
  }
  if (g.type === "service") {
    const r = await env.DB.prepare("SELECT COUNT(*) AS c FROM dc_goods_service WHERE goods_id = ? AND sku = ?").bind(goodsId, sku).first();
    if (!parseInt(r.c)) return json2({ code: 1, msg: "\u8BE5\u89C4\u683C\u65E0\u6548" });
  }
  if (qty > avail) return json2({ code: 1, msg: "\u5E93\u5B58\u4E0D\u8DB3" });
  const amount = unitPrice * qty;
  const outTradeNo = dateStr() + String(Math.floor(Math.random() * 9e3) + 1e3);
  const t = now();
  const orderId = Date.now() * 1e3 + Math.floor(Math.random() * 1e3);
  const clientIp = request.headers.get("CF-Connecting-IP") || request.headers.get("x-forwarded-for") || "";
  let discount = 0;
  const dis = await env.DB.prepare("SELECT * FROM dc_discount WHERE goods_id = ? AND sku = ? AND quantity <= ? ORDER BY quantity DESC LIMIT 1").bind(goodsId, sku, qty).first();
  if (dis) {
    if (dis.type === 1) discount = dis.amount * qty;
    else if (dis.type === 2) discount = dis.amount;
    else if (dis.type === 3) discount = Math.round(amount * (100 - dis.amount) / 100);
  }
  const finalAmount = Math.max(1, amount - discount);
  await env.DB.batch([
    env.DB.prepare("INSERT INTO dc_order (id, station_id, client_ip, user_id, out_trade_no, amount, create_time, payment, pay_plugin, pay_time, expire_time, pay_status, status) VALUES (?,0,?,0,?,?,?,?,?,?,?,0,0)").bind(orderId, clientIp, outTradeNo, finalAmount, t, "\u5F85\u652F\u4ED8", payPlugin, t + (parseInt(opt(await getOptions(env.DB), "continue_pay_timeout", "30")) || 30) * 60),
    env.DB.prepare("INSERT INTO dc_order_list (order_id, goods_id, sku, attr_spec, attach_user, quantity, unit_price, price, status) VALUES (?,?,?,?,?,?,?,?,0)").bind(orderId, goodsId, sku, JSON.stringify(await buildAttrSpec(env.DB, sku)), JSON.stringify(attachRaw), qty, unitPrice, finalAmount),
    ...Object.keys(requiredRaw).map(
      (k) => env.DB.prepare("INSERT INTO dc_order_required (order_id, name, type, content) VALUES (?,?,?,?)").bind(orderId, k, "string", requiredRaw[k])
    )
  ]);
  return json2({ code: 0, out_trade_no: outTradeNo });
}
async function pagePay(request, env, q) {
  const { opts, navItems } = await ctx(env);
  const no = q.out_trade_no || "";
  const order = await env.DB.prepare("SELECT * FROM dc_order WHERE out_trade_no = ? LIMIT 1").bind(no).first();
  if (!order) return new Response("\u8BA2\u5355\u4E0D\u5B58\u5728", { status: 404, headers: HTML_HEADERS2 });
  const list = await env.DB.prepare("SELECT ol.*, g.title, g.cover FROM dc_order_list ol LEFT JOIN dc_goods g ON g.id = ol.goods_id WHERE ol.order_id = ?").bind(order.id).all();
  const paid = order.pay_status == 1;
  const expired = order.status === 3;
  const statusText = paid ? "\u5DF2\u652F\u4ED8" : expired ? "\u5DF2\u53D6\u6D88" : "\u5F85\u652F\u4ED8";
  let balance = 0;
  const uid = await getUserUid(request, env);
  let user = null;
  if (uid) user = await env.DB.prepare("SELECT * FROM dc_user WHERE uid = ?").bind(uid).first();
  const countDown = Math.max(0, (parseInt(order.expire_time) || 0) - now());
  const itemsHtml = list.results.map((l) => `<tr><td class="item-image">${l.cover && !l.cover.startsWith("../") ? `<img src="${esc(l.cover)}" alt="">` : ""}</td><td class="item-info"><div class="title">${esc(l.title)}</div><div class="spec">${l.attr_spec || ""}</div></td><td class="item-price">\xA5${fen2yuan(l.unit_price)}</td><td class="item-quantity">\xD7${l.quantity}</td><td class="item-total">\xA5${fen2yuan(l.price)}</td></tr>`).join("");
  const body = `
  <main class="container py-4" style="max-width:960px;">
    <div class="panel">
      <div class="panel-header">
        <span class="icon"><i class="fa-duotone fa-regular fa-money-check-dollar"></i></span>
        <h6 class="panel-title">\u8BA2\u5355\u652F\u4ED8</h6>
      </div>
      <div class="panel-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3" style="padding:14px;background:#f6f8fa;border-radius:8px;">
          <span>\u8BA2\u5355\u53F7\uFF1A<b>${esc(order.out_trade_no)}</b></span>
          <span>\u72B6\u6001\uFF1A<b style="color:${paid ? "#4caf50" : expired ? "#999" : "#ff9800"}">${statusText}</b></span>
        </div>
        <table class="table">
          <thead><tr><th>\u5546\u54C1</th><th>\u5355\u4EF7</th><th>\u6570\u91CF</th><th>\u5C0F\u8BA1</th></tr></thead>
          <tbody>${itemsHtml}</tbody>
        </table>
        <div class="text-end mb-3" style="font-size:16px;">\u5E94\u4ED8\u91D1\u989D\uFF1A<b style="color:#ff6600;font-size:22px;">\xA5${fen2yuan(order.amount)}</b></div>
        ${!paid && !expired ? `
        <div class="d-flex gap-2 flex-wrap">
          <button class="btn btn-primary br-12" id="btnMockPay">\u6A21\u62DF\u652F\u4ED8\u6210\u529F\uFF08\u6D4B\u8BD5\uFF09</button>
          ${opt(opts, "balance_switch", "y") === "y" && user ? `<button class="btn btn-outline-success br-12" id="btnBalancePay">\u4F59\u989D\u652F\u4ED8\uFF08\u4F59\u989D \xA5${fen2yuan(user.money * 100)}\uFF09</button>` : ""}
        </div>
        ${epayConfig(opts) ? `<div class="mt-3 text-muted" style="font-size:13px;">\u5DF2\u914D\u7F6E\u6613\u652F\u4ED8\u7F51\u5173\uFF0C\u8BF7\u5728\u652F\u4ED8\u5206\u9875\u4E2D\u9009\u62E9\u5FAE\u4FE1/\u652F\u4ED8\u5B9D\u3002</div>` : ""}
        <div class="mt-3 text-muted" style="font-size:13px;">${countDown > 0 ? `\u8BF7\u5728 <b>${Math.ceil(countDown / 60)}</b> \u5206\u949F\u5185\u5B8C\u6210\u652F\u4ED8\uFF0C\u8D85\u65F6\u8BA2\u5355\u5C06\u81EA\u52A8\u53D6\u6D88\u3002` : "\u8BA2\u5355\u5DF2\u8D85\u65F6\uFF0C\u8BF7\u91CD\u65B0\u4E0B\u5355\u3002"}</div>
        ` : paid ? `<a class="btn btn-primary br-12" href="/?action=order_result&out_trade_no=${esc(order.out_trade_no)}">\u67E5\u770B\u8BA2\u5355\u7ED3\u679C</a>` : `<a class="btn btn-outline-secondary br-12" href="/">\u8FD4\u56DE\u9996\u9875</a>`}
      </div>
    </div>
  </main>
  <script>
  var NO = ${JSON.stringify(order.out_trade_no)};
  function doPay(plugin){
    $.post('/?action=pay_submit', { out_trade_no: NO, plugin: plugin }, function(res){
      if (res.code === 0) { location.href = '/?action=order_result&out_trade_no=' + NO; }
      else { layer.msg(res.msg || '\u652F\u4ED8\u5931\u8D25'); }
    }, 'json');
  }
  $('#btnMockPay').on('click', function(){ doPay('test'); });
  $('#btnBalancePay').on('click', function(){ doPay('balance'); });
  </script>`;
  return new Response(layout(env, { ...opts, title: "\u8BA2\u5355\u652F\u4ED8" }, navItems, body), { headers: HTML_HEADERS2 });
}
async function apiPaySubmit(request, env) {
  const form = await request.formData();
  const no = form.get("out_trade_no") || "";
  const plugin = form.get("plugin") || "test";
  const order = await env.DB.prepare("SELECT * FROM dc_order WHERE out_trade_no = ? LIMIT 1").bind(no).first();
  if (!order) return json2({ code: 1, msg: "\u8BA2\u5355\u4E0D\u5B58\u5728" });
  if (order.pay_status == 1) return json2({ code: 0, msg: "\u5DF2\u652F\u4ED8" });
  const t = now();
  if (plugin === "balance") {
    const uid = await getUserUid(request, env);
    if (!uid) return json2({ code: 1, msg: "\u8BF7\u5148\u767B\u5F55" });
    const user = await env.DB.prepare("SELECT * FROM dc_user WHERE uid = ?").bind(uid).first();
    const moneyFen = Math.round((user.money || 0) * 100);
    if (moneyFen < order.amount) return json2({ code: 1, msg: "\u4F59\u989D\u4E0D\u8DB3" });
    await env.DB.batch([
      env.DB.prepare("UPDATE dc_user SET money = round((money * 100 - ?) / 100.0, 2) WHERE uid = ?").bind(order.amount, uid),
      env.DB.prepare("UPDATE dc_order SET pay_status = 1, status = 1, pay_time = ?, payment = ?, pay_plugin = ?, up_no = ? WHERE id = ?").bind(t, "\u4F59\u989D\u652F\u4ED8", "balance", no + "B", order.id)
    ]);
  } else {
    await env.DB.prepare("UPDATE dc_order SET pay_status = 1, status = 1, pay_time = ?, payment = ?, pay_plugin = ?, up_no = ? WHERE id = ?").bind(t, "\u6D4B\u8BD5\u652F\u4ED8", "test", no + "T", order.id);
  }
  await deliver(env, order.id);
  return json2({ code: 0 });
}
async function deliver(env, orderId) {
  const list = (await env.DB.prepare("SELECT * FROM dc_order_list WHERE order_id = ?").bind(orderId).all()).results;
  const t = now();
  const batch = [];
  for (const ol of list) {
    const g = await env.DB.prepare("SELECT * FROM dc_goods WHERE id = ?").bind(ol.goods_id).first();
    if (!g) continue;
    if (g.type === "once") {
      const rows = (await env.DB.prepare("SELECT id FROM dc_goods_once WHERE goods_id = ? AND sku = ? AND sale_time IS NULL ORDER BY id ASC LIMIT ?").bind(g.goods_id || ol.goods_id, ol.sku, ol.quantity).all()).results;
      if (rows.length) {
        const ids = rows.map((r) => r.id);
        batch.push(env.DB.prepare("UPDATE dc_goods_once SET sale_time = ?, order_list_id = ? WHERE id IN (" + ids.map(() => "?").join(",") + ")").bind(t, ol.id, ...ids));
      }
      batch.push(env.DB.prepare("UPDATE dc_order_list SET status = ? WHERE id = ?").bind(rows.length >= ol.quantity ? 2 : 1, ol.id));
    } else if (g.type === "general") {
      const row = await env.DB.prepare("SELECT * FROM dc_goods_general WHERE goods_id = ? AND sku = ? LIMIT 1").bind(ol.goods_id, ol.sku).first();
      if (row) {
        batch.push(env.DB.prepare("INSERT INTO dc_goods_general_sale (goods_id, order_list_id, sku, content, num, create_time) VALUES (?,?,?,?,?,?)").bind(ol.goods_id, ol.id, ol.sku, row.content, ol.quantity, t));
        batch.push(env.DB.prepare("UPDATE dc_order_list SET status = 2 WHERE id = ?").bind(ol.id));
      }
    } else if (g.type === "service") {
      const row = await env.DB.prepare("SELECT * FROM dc_goods_service WHERE goods_id = ? AND sku = ? LIMIT 1").bind(ol.goods_id, ol.sku).first();
      if (row) {
        batch.push(env.DB.prepare("INSERT INTO dc_goods_service_sale (goods_id, order_list_id, sku, content, num, is_default, create_time) VALUES (?,?,?,?,?,?,?)").bind(ol.goods_id, ol.id, ol.sku, row.content, ol.quantity, "y", t));
        batch.push(env.DB.prepare("UPDATE dc_order_list SET status = 2 WHERE id = ?").bind(ol.id));
      }
    }
    if (parseInt(g.stock) > 0) {
      batch.push(env.DB.prepare("UPDATE dc_skus SET stock = MAX(0, stock - ?), sales = sales + ? WHERE goods_id = ? AND sku = ?").bind(ol.quantity, ol.quantity, ol.goods_id, ol.sku));
      batch.push(env.DB.prepare("UPDATE dc_goods SET stock = MAX(0, stock - ?), sales = sales + ? WHERE id = ?").bind(ol.quantity, ol.quantity, ol.goods_id));
    }
  }
  if (batch.length) {
    batch.push(env.DB.prepare("UPDATE dc_order SET status = 2, update_time = ? WHERE id = ?").bind(t, orderId));
    await env.DB.batch(batch);
  } else {
    await env.DB.prepare("UPDATE dc_order SET status = 1, update_time = ? WHERE id = ?").bind(t, orderId);
  }
}
async function pageOrderResult(request, env, q) {
  const { opts, navItems } = await ctx(env);
  const no = q.out_trade_no || "";
  const order = await env.DB.prepare("SELECT * FROM dc_order WHERE out_trade_no = ? LIMIT 1").bind(no).first();
  if (!order) return new Response("\u8BA2\u5355\u4E0D\u5B58\u5728", { status: 404, headers: HTML_HEADERS2 });
  const lists = (await env.DB.prepare("SELECT ol.*, g.title, g.cover, g.pay_content FROM dc_order_list ol LEFT JOIN dc_goods g ON g.id = ol.goods_id WHERE ol.order_id = ?").bind(order.id).all()).results;
  let kamiLines = [];
  for (const l of lists) {
    if (l.status < 2) continue;
    const once = await env.DB.prepare("SELECT content FROM dc_goods_once WHERE order_list_id = ? ORDER BY id ASC").bind(l.id).all();
    for (const r of once.results) kamiLines.push({ content: r.content });
    const general = await env.DB.prepare("SELECT content, num FROM dc_goods_general_sale WHERE order_list_id = ?").bind(l.id).all();
    for (const r of general.results) for (let i = 0; i < r.num; i++) kamiLines.push({ content: r.content });
    const service = await env.DB.prepare("SELECT content, is_default FROM dc_goods_service_sale WHERE order_list_id = ?").bind(l.id).all();
    for (const r of service.results) kamiLines.push({ content: r.content });
  }
  const statusMap = { 0: ["\u5F85\u652F\u4ED8", "#ff9800"], 1: ["\u5DF2\u652F\u4ED8\u5F85\u53D1\u8D27", "#ff9800"], 2: ["\u5DF2\u5B8C\u6210", "#4caf50"], 3: ["\u5DF2\u53D6\u6D88", "#999"] };
  const [statusText, statusColor] = statusMap[order.status] || ["\u672A\u77E5", "#999"];
  const orderCards = lists.map(
    (l) => `<div class="panel mb-3"><div class="panel-body" style="padding:16px;">
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <span class="text-muted" style="font-size:13px;">${esc(order.out_trade_no)}</span>
      <span style="color:${statusColor}">${statusText}</span>
    </div>
    <div style="margin:10px 0;color:#333;">${esc(l.title)}${l.attr_spec ? `<span class="text-muted" style="font-size:13px;margin-left:8px;">${l.attr_spec}</span>` : ""}</div>
    <div style="display:flex;justify-content:space-between;align-items:center;font-size:13px;color:#888;">
      <span>${order.pay_time ? ts2str(order.pay_time) : ""}</span>
      <span>\u5171${l.quantity}\u4EF6\uFF0C\u5408\u8BA1 <b style="color:#ff6600;">\xA5${fen2yuan(l.price)}</b></span>
    </div>
  </div></div>`
  ).join("");
  const kamiHtml = kamiLines.length > 0 ? buildKamiHtml(kamiLines) : "";
  const body = `
  <main class="container py-4" style="max-width:860px;">
    <div class="panel">
      <div class="panel-header">
        <span class="icon"><i class="fa-duotone fa-regular fa-gift"></i></span>
        <h6 class="panel-title">\u8BA2\u5355\u7ED3\u679C</h6>
      </div>
      <div class="panel-body">
        ${orderCards}
        ${kamiHtml}
        <div class="mt-4 text-center">
          <a class="btn btn-outline-secondary br-12" href="/?action=order_query">\u67E5\u8BE2\u5176\u4ED6\u8BA2\u5355</a>
          <a class="btn btn-primary br-12" href="/" style="margin-left:10px;">\u518D\u4E70\u4E00\u5355</a>
        </div>
      </div>
    </div>
  </main>`;
  return new Response(layout(env, { ...opts, title: "\u8BA2\u5355\u7ED3\u679C" }, navItems, body), { headers: HTML_HEADERS2 });
}
async function apiKami(request, env, q) {
  const no = q.out_trade_no || "";
  const order = await env.DB.prepare("SELECT * FROM dc_order WHERE out_trade_no = ? LIMIT 1").bind(no).first();
  if (!order) return json2({ code: 1, msg: "\u8BA2\u5355\u4E0D\u5B58\u5728" });
  const lists = (await env.DB.prepare("SELECT * FROM dc_order_list WHERE order_id = ?").bind(order.id).all()).results;
  const out = [];
  for (const l of lists) {
    if (l.status < 2) continue;
    const once = await env.DB.prepare("SELECT content FROM dc_goods_once WHERE order_list_id = ?").bind(l.id).all();
    for (const r of once.results) out.push({ content: r.content });
    const general = await env.DB.prepare("SELECT content, num FROM dc_goods_general_sale WHERE order_list_id = ?").bind(l.id).all();
    for (const r of general.results) for (let i = 0; i < r.num; i++) out.push({ content: r.content });
    const service = await env.DB.prepare("SELECT content, is_default FROM dc_goods_service_sale WHERE order_list_id = ?").bind(l.id).all();
    for (const r of service.results) out.push({ content: r.content });
  }
  return json2({ code: 0, status: order.status, list: out });
}
async function pageOrderQuery(request, env, q) {
  const { opts, navItems } = await ctx(env);
  const body = `
  <main class="container py-4" style="max-width:760px;">
    <div class="panel">
      <div class="panel-header">
        <span class="icon"><i class="fa-duotone fa-regular fa-magnifying-glass"></i></span>
        <h6 class="panel-title">\u8BA2\u5355\u67E5\u8BE2</h6>
      </div>
      <div class="panel-body">
        <div class="d-flex justify-content-center align-items-center gap-3 flex-wrap">
          <div style="width:300px;max-width:100%;">
            <input type="text" class="form-control" id="queryInput" placeholder="\u8BA2\u5355\u53F7/\u8054\u7CFB\u65B9\u5F0F">
          </div>
          <div>
            <button type="button" class="btn btn-primary br-12" id="queryOrder"><i class="fa-duotone fa-regular fa-search me-2"></i>\u67E5\u8BE2\u8BA2\u5355</button>
          </div>
        </div>
      </div>
    </div>
    <div id="resultArea" class="mt-4"></div>
  </main>
  <script>
  function doQuery(){
    var v = ($('#queryInput').val()||'').trim();
    if (!v) { layer.msg('\u8BF7\u8F93\u5165\u8BA2\u5355\u53F7\u6216\u8054\u7CFB\u65B9\u5F0F'); return; }
    $('#resultArea').html('<div style="text-align:center;padding:30px;color:#888;">\u67E5\u8BE2\u4E2D...</div>');
    $.get('/?action=order_query&q=' + encodeURIComponent(v), function(res){
      if (res.code !== 0) { $('#resultArea').html('<div class="panel pt-3"><div class="panel-body text-center"><div class="mb-3"><i class="fa-duotone fa-regular fa-search" style="font-size:3rem;color:#6b7280;"></i></div><h6 class="text-muted">' + res.msg + '</h6></div></div>'); return; }
      var html = '';
      for (var i=0;i<res.list.length;i++){
        var o = res.list[i];
        html += '<div class="panel"><div class="panel-body">' +
          '<div class="d-flex justify-content-between align-items-center"><span style="color:#333;">' + o.out_trade_no + '</span><span style="color:#4caf50;">' + o.status_text + '</span></div>' +
          '<div class="my-2" style="color:#555;font-size:14px;">' + o.title_html + '</div>' +
          '<div class="d-flex justify-content-between align-items-center" style="font-size:14px;color:#888;"><span>' + o.create_time_text + '</span><span>\u5171' + o.count + '\u4EF6 \u5408\u8BA1 <b style="color:#ff6600;">\xA5' + o.amount + '</b></span></div>' +
          (o.can_view ? '<div class="mt-3 text-end"><a class="btn btn-primary btn-sm br-12" href="/?action=order_result&out_trade_no=' + o.out_trade_no + '">\u67E5\u770B\u8BA2\u5355</a></div>' : '') +
        '</div></div>';
      }
      $('#resultArea').html('<div class="text-muted mb-2">\u5171\u627E\u5230 ' + res.list.length + ' \u4E2A\u8BA2\u5355</div>' + html);
    }, 'json');
  }
  $('#queryOrder').on('click', doQuery);
  $('#queryInput').on('keydown', function(e){ if (e.key === 'Enter') doQuery(); });
  </script>`;
  return new Response(layout(env, { ...opts, title: "\u8BA2\u5355\u67E5\u8BE2" }, navItems, body), { headers: HTML_HEADERS2 });
}
async function apiOrderQuery(request, env, q) {
  const url = new URL(request.url);
  let query = (url.searchParams.get("q") || "").trim();
  if (!query && request.method === "POST") {
    try {
      const fd = await request.formData();
      query = (fd.get("q") || "").trim();
    } catch (e) {
    }
  }
  if (!query) return json2({ code: 1, msg: "\u8BF7\u8F93\u5165\u67E5\u8BE2\u5185\u5BB9" });
  const orders = (await env.DB.prepare(
    `SELECT DISTINCT o.* FROM dc_order o
       LEFT JOIN dc_order_list ol ON ol.order_id = o.id
       LEFT JOIN dc_order_required r ON r.order_id = o.id
       WHERE o.delete_time IS NULL AND (o.out_trade_no LIKE ? OR o.up_no LIKE ? OR ol.attach_user LIKE ? OR r.content = ?)
       ORDER BY o.id DESC LIMIT 20`
  ).bind("%" + query + "%", "%" + query + "%", "%" + query + "%", query).all()).results;
  const statusText = { 0: "\u5F85\u652F\u4ED8", 1: "\u5DF2\u652F\u4ED8/\u5F85\u53D1\u8D27", 2: "\u5DF2\u5B8C\u6210", 3: "\u5DF2\u53D6\u6D88" };
  const out = [];
  for (const o of orders) {
    const lines = (await env.DB.prepare("SELECT ol.*, g.title FROM dc_order_list ol LEFT JOIN dc_goods g ON g.id = ol.goods_id WHERE ol.order_id = ?").bind(o.id).all()).results;
    const titleHtml = lines.map((l) => "<div>" + esc(l.title) + (l.attr_spec ? ' <span style="color:#999;">' + l.attr_spec + "</span>" : "") + " \xD7" + l.quantity + "</div>").join("");
    const count = lines.reduce((a, l) => a + (l.quantity || 0), 0);
    out.push({
      out_trade_no: o.out_trade_no,
      status_text: statusText[o.status] || "\u672A\u77E5",
      status: o.status,
      title_html: titleHtml,
      create_time_text: ts2str(o.create_time),
      amount: fen2yuan(o.amount),
      count,
      can_view: true
    });
  }
  if (!out.length) return json2({ code: 1, msg: "\u672A\u67E5\u8BE2\u5230\u76F8\u5173\u8BA2\u5355\uFF0C\u8BF7\u68C0\u67E5\u8F93\u5165\u5185\u5BB9" });
  return json2({ code: 0, list: out });
}
async function pageHelp(request, env, q) {
  const { opts, navItems } = await ctx(env);
  const faqs = [
    ["\u5982\u4F55\u8D2D\u4E70\u5546\u54C1\uFF1F", "\u9009\u62E9\u5FC3\u4EEA\u5546\u54C1 \u2192 \u8FDB\u5165\u8BE6\u60C5\u9875\u9009\u62E9\u89C4\u683C\u548C\u6570\u91CF \u2192 \u70B9\u51FB\u7ACB\u5373\u8D2D\u4E70 \u2192 \u5B8C\u6210\u652F\u4ED8 \u2192 \u5728\u8BA2\u5355\u7ED3\u679C\u9875\u67E5\u770B\u5361\u5BC6\u3002"],
    ["\u4ED8\u6B3E\u540E\u591A\u4E45\u53D1\u8D27\uFF1F", "\u672C\u7AD9\u5168\u90E8\u5546\u54C1 7\xD724 \u5C0F\u65F6\u5168\u81EA\u52A8\u53D1\u8D27\uFF0C\u652F\u4ED8\u6210\u529F\u540E\u8BA2\u5355\u7ED3\u679C\u9875\u5373\u523B\u5C55\u793A\u5361\u5BC6\u3002"],
    ["\u5982\u4F55\u67E5\u8BE2\u8BA2\u5355\uFF1F", "\u70B9\u51FB\u9876\u90E8\u300C\u67E5\u8BE2\u8BA2\u5355\u300D\uFF0C\u8F93\u5165\u4E0B\u5355\u65F6\u586B\u5199\u7684\u8054\u7CFB\u65B9\u5F0F\u6216\u8BA2\u5355\u53F7\u5373\u53EF\u67E5\u8BE2\u3002"],
    ["\u5361\u5BC6\u65E0\u6CD5\u4F7F\u7528\u600E\u4E48\u529E\uFF1F", "\u8BF7\u5148\u901A\u8FC7\u8BA2\u5355\u9875\u590D\u5236\u5B8C\u6574\u5361\u5BC6\uFF0C\u4ECD\u65E0\u6CD5\u4F7F\u7528\u8BF7\u8054\u7CFB\u5BA2\u670D\u5E76\u63D0\u4F9B\u8BA2\u5355\u53F7\uFF0C\u6211\u4EEC\u4F1A\u5C3D\u5FEB\u4E3A\u60A8\u5904\u7406\u3002"],
    ["\u652F\u6301\u54EA\u4E9B\u652F\u4ED8\u65B9\u5F0F\uFF1F", "\u652F\u6301\u5FAE\u4FE1\u3001\u652F\u4ED8\u5B9D\uFF08\u6613\u652F\u4ED8\u7F51\u5173\uFF09\u53CA\u7AD9\u5185\u4F59\u989D\u652F\u4ED8\u3002"]
  ];
  const body = `
  <main class="container py-4" style="max-width:860px;">
    <div class="panel">
      <div class="panel-header">
        <span class="icon"><i class="fa-duotone fa-regular fa-circle-question"></i></span>
        <h6 class="panel-title">\u5E38\u89C1\u95EE\u9898</h6>
      </div>
      <div class="panel-body">
        ${faqs.map(
    (f, i) => `<div class="faq-row" style="border-bottom:1px dashed #eee;padding:14px 0;">
        <div class="faq-q" style="font-weight:500;color:#333;cursor:pointer;display:flex;justify-content:space-between;"><span><span style="color:#139655;margin-right:8px;">${i + 1}.</span>${f[0]}</span><span class="faq-arrow">+</span></div>
        <div class="faq-a" style="color:#777;font-size:14px;line-height:1.8;margin-top:10px;display:none;">${f[1]}</div>
      </div>`
  ).join("")}
      </div>
    </div>
    <div class="panel mt-3">
      <div class="panel-header">
        <span class="icon"><i class="fa-duotone fa-regular fa-headset"></i></span>
        <h6 class="panel-title">\u8054\u7CFB\u65B9\u5F0F</h6>
      </div>
      <div class="panel-body" style="color:#555;font-size:14px;line-height:2;">\u5DE5\u4F5C\u65F6\u95F4\uFF1A\u6BCF\u65E5 9:00 - 22:00<br>\u5982\u6709\u95EE\u9898\u8BF7\u63D0\u4F9B\u8BA2\u5355\u53F7\u54A8\u8BE2\u5728\u7EBF\u5BA2\u670D\u3002</div>
    </div>
  </main>
  <script>
  $(function(){
    $('.faq-q').on('click', function(){ var a=$(this).next(); a.slideToggle(150); $(this).find('.faq-arrow').text(a.is(':visible')?'-':'+'); });
  });
  </script>`;
  return new Response(layout(env, { ...opts, title: "\u4E70\u5BB6\u5E2E\u52A9" }, navItems, body), { headers: HTML_HEADERS2 });
}
async function pageUser(request, env, q) {
  const { opts, navItems } = await ctx(env);
  const uid = await getUserUid(request, env);
  const user = uid ? await env.DB.prepare("SELECT * FROM dc_user WHERE uid = ?").bind(uid).first() : null;
  let inner;
  if (user) {
    const orders = (await env.DB.prepare("SELECT * FROM dc_order WHERE user_id = ? ORDER BY id DESC LIMIT 20").bind(user.uid).all()).results;
    const statusText = { 0: "\u5F85\u652F\u4ED8", 1: "\u5DF2\u652F\u4ED8/\u5F85\u53D1\u8D27", 2: "\u5DF2\u5B8C\u6210", 3: "\u5DF2\u53D6\u6D88" };
    const orderRows = orders.map(
      (o) => `<tr><td>${esc(o.out_trade_no)}</td><td>\xA5${fen2yuan(o.amount)}</td><td>${ts2str(o.create_time)}</td><td>${statusText[o.status] || "\u672A\u77E5"}</td><td>${o.pay_status == 1 ? `<a href="/?action=order_result&out_trade_no=${esc(o.out_trade_no)}">\u67E5\u770B</a>` : `<a href="/?action=pay&out_trade_no=${esc(o.out_trade_no)}">\u652F\u4ED8</a>`}</td></tr>`
    ).join("");
    inner = `<div class="panel">
      <div class="panel-body">
        <h4 class="mb-1">\u4F60\u597D\uFF0C${esc(user.nickname || user.username)}</h4>
        <div class="text-muted mb-3" style="font-size:14px;">\u4F59\u989D\uFF1A<b style="color:#ff6600;">\xA5${fen2yuan(Math.round((user.money || 0) * 100))}</b></div>
        <div class="table-responsive"><table class="table table-hover"><thead><tr><th>\u8BA2\u5355\u53F7</th><th>\u91D1\u989D</th><th>\u65F6\u95F4</th><th>\u72B6\u6001</th><th>\u64CD\u4F5C</th></tr></thead><tbody>${orderRows || '<tr><td colspan="5" style="padding:20px;text-align:center;color:#999;">\u6682\u65E0\u8BA2\u5355</td></tr>'}</tbody></table></div>
        <div class="mt-3"><a href="/?action=user&logout=1" style="color:#e53e3e;">\u9000\u51FA\u767B\u5F55</a></div>
      </div>
    </div>`;
  } else {
    inner = `<div class="panel" style="max-width:420px;margin:0 auto;">
      <div class="panel-header">
        <span class="icon"><i class="fa-duotone fa-regular fa-right-to-bracket"></i></span>
        <h6 class="panel-title">\u4F1A\u5458\u767B\u5F55</h6>
      </div>
      <div class="panel-body">
        <div class="mb-3"><input id="lUser" class="form-control" placeholder="\u7528\u6237\u540D"></div>
        <div class="mb-3"><input id="lPwd" type="password" class="form-control" placeholder="\u5BC6\u7801"></div>
        <button id="btnLogin" class="btn btn-primary br-12 w-100" style="padding:10px;">\u767B \u5F55</button>
        <div class="mt-2 text-muted" style="font-size:13px;">\u6E38\u5BA2\u53EF\u76F4\u63A5\u4E0B\u5355\uFF0C\u767B\u5F55\u540E\u53EF\u67E5\u770B\u4F59\u989D\u4E0E\u8BA2\u5355\u3002</div>
      </div>
    </div>
    <script>
    $('#btnLogin').on('click', function(){
      $.post('/?action=login', { username: $('#lUser').val(), password: $('#lPwd').val() }, function(res){
        if (res.code === 0) { location.reload(); } else { layer.msg(res.msg || '\u767B\u5F55\u5931\u8D25'); }
      }, 'json');
    });
    </script>`;
  }
  const body = `<main class="container py-4" style="max-width:960px;">${inner}</main>`;
  return new Response(layout(env, { ...opts, title: "\u4F1A\u5458\u4E2D\u5FC3" }, navItems, body), { headers: HTML_HEADERS2 });
}
async function apiLogin(request, env) {
  const form = await request.formData();
  const username = form.get("username") || "";
  const password = form.get("password") || "";
  const user = await env.DB.prepare("SELECT * FROM dc_user WHERE username = ? AND delete_time IS NULL LIMIT 1").bind(username).first();
  if (!user) return json2({ code: 1, msg: "\u7528\u6237\u4E0D\u5B58\u5728" });
  if (!user.password || user.password !== password) return json2({ code: 1, msg: "\u5BC6\u7801\u9519\u8BEF" });
  const token = await makeToken(env, user.uid);
  const res = json2({ code: 0 });
  res.headers.append("Set-Cookie", "dc_token=" + token + "; Path=/; HttpOnly; Max-Age=604800");
  return res;
}
async function makeToken(env, uid) {
  const secret = env.SECRET || "dc-faka-secret";
  const data = String(uid) + "." + String(Math.floor(Date.now() / 1e3));
  const sig = await hmacSha256(secret, data);
  return btoa(urlsafe(data + "." + sig));
}
async function getUserUid(request, env) {
  const cookies = parseCookies(request.headers.get("Cookie") || "");
  const t = cookies.dc_token;
  if (!t) return 0;
  try {
    const decoded = atob(t);
    const parts = decoded.split(".");
    if (parts.length !== 3) return 0;
    const data = parts[0] + "." + parts[1];
    const sig = await hmacSha256(env.SECRET || "dc-faka-secret", data);
    if (sig !== parts[2]) return 0;
    return parseInt(parts[0]) || 0;
  } catch (e) {
    return 0;
  }
}
async function verifyToken(env, token) {
  try {
    const decoded = atob(token);
    const parts = decoded.split(".");
    if (parts.length !== 3) return 0;
    const secret = env.SECRET || "dc-faka-secret";
    const data = parts[0] + "." + parts[1];
    const sig = await hmacSha256(secret, data);
    if (sig !== parts[2]) return 0;
    return parseInt(parts[0]);
  } catch (e) {
    return 0;
  }
}
function parseCookies(c) {
  const o = {};
  for (const kv of c.split(";")) {
    const i = kv.indexOf("=");
    if (i > 0) o[kv.slice(0, i).trim()] = kv.slice(i + 1).trim();
  }
  return o;
}
async function pageRss(request, env, q) {
  const { opts } = await ctx(env);
  const goods = (await env.DB.prepare("SELECT * FROM dc_goods WHERE delete_time IS NULL AND is_on_shelf = 1 ORDER BY id DESC LIMIT 20").all()).results;
  const siteName = opt(opts, "blogname", "ACG\u53D1\u5361");
  const url = request.url;
  let items = "";
  for (const g of goods) {
    const link = url.split("?")[0] + "/?action=goods&id=" + g.id;
    items += `<item><title>${esc(g.title)}</title><link>${link}</link><description>${esc(g.des || "")}</description></item>`;
  }
  return new Response(
    `<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>${esc(siteName)}</title><link>${url.split("?")[0]}</link><description>${esc(siteName)} - \u6700\u65B0\u5546\u54C1</description>${items}</channel></rss>`,
    { headers: { "content-type": "application/rss+xml; charset=utf-8" } }
  );
}
async function apiOrderCancel(request, env) {
  const form = await request.formData();
  const no = form.get("out_trade_no") || "";
  const order = await env.DB.prepare("SELECT * FROM dc_order WHERE out_trade_no = ? LIMIT 1").bind(no).first();
  if (!order) return json2({ code: 1, msg: "\u8BA2\u5355\u4E0D\u5B58\u5728" });
  if (order.pay_status == 1) return json2({ code: 1, msg: "\u5DF2\u652F\u4ED8\u8BA2\u5355\u4E0D\u53EF\u53D6\u6D88" });
  await env.DB.prepare("UPDATE dc_order SET status = 3 WHERE id = ? AND status = 0").bind(order.id).run();
  return json2({ code: 0 });
}
function buildKamiHtml(kamiLines) {
  const items = kamiLines.map(
    (k, i) => '<div class="kami-item"><span class="kami-index">' + (i + 1) + "</span><code>" + esc(k.content) + '</code><button class="kami-item-copy" data-c="' + esc(k.content) + '">\u590D\u5236</button></div>'
  ).join("");
  const allText = JSON.stringify(kamiLines.map((k) => k.content).join("\n"));
  return '<div class="kami-list" id="kamiList"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;"><b>\u5361\u5BC6\u4FE1\u606F\uFF08\u5171 ' + kamiLines.length + ' \u6761\uFF09</b><button class="btn btn-primary btn-sm br-12" id="btnCopyAll">\u4E00\u952E\u590D\u5236</button></div>' + items + "</div><script>$('#btnCopyAll').on('click', function(){  var t = " + allText + ";  if (navigator.clipboard) navigator.clipboard.writeText(t).then(function(){ layer.msg('\u5DF2\u590D\u5236\u5168\u90E8'); });  else { var ta=document.createElement('textarea'); ta.value=t; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); layer.msg('\u5DF2\u590D\u5236\u5168\u90E8'); }});$(document).on('click', '.kami-item-copy', function(){  var t = $(this).data('c');  if (navigator.clipboard) navigator.clipboard.writeText(t).then(function(){ layer.msg('\u5DF2\u590D\u5236'); });  else { var ta=document.createElement('textarea'); ta.value=t; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); layer.msg('\u5DF2\u590D\u5236'); }});</script><style>.kami-list{margin-top:18px;background:#fff;border:1px solid #eee;border-radius:10px;padding:18px;}.kami-item{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px dashed #eee;}.kami-item:last-child{border-bottom:none;}.kami-index{width:24px;height:24px;border-radius:50%;background:#139655;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;}.kami-item code{flex:1;word-break:break-all;color:#333;}.kami-item-copy{color:#139655;cursor:pointer;background:none;border:none;font-size:13px;}</style>";
}
function json2(o) {
  return new Response(JSON.stringify(o), { headers: JSON_HEADERS2 });
}
function dateStr() {
  const d = /* @__PURE__ */ new Date();
  const p2 = (x) => String(x).padStart(2, "0");
  return "" + d.getFullYear() + p2(d.getMonth() + 1) + p2(d.getDate()) + p2(d.getHours()) + p2(d.getMinutes()) + p2(d.getSeconds());
}
export {
  getOptions as _getOptions,
  worker_default as default,
  getUserUid,
  makeToken,
  parseCookies,
  verifyToken
};
