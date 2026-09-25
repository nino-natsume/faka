// ============================================================
// acg-faka Cloudflare 部署版 - 管理后台 (Material 皮肤)
// 登录: env.ADMIN_USERNAME / env.ADMIN_PASSWORD (默认 admin / admin123)
// ============================================================
import { esc, fen2yuan, ts2str, now, parseQuery, getOptions, opt, hmacSha256 } from './lib.js';

// 本地 admin token 校验（避免与 worker.js 循环依赖）
async function verifyAdminToken(env, token) {
  try {
    const dec = atob(token);
    const parts = dec.split('.');
    if (parts.length !== 3) return 0;
    const secret = env.SECRET || 'acg-faka-secret';
    const data = parts[0] + '.' + parts[1];
    const sig = await hmacSha256(secret, data);
    if (sig !== parts[2]) return 0;
    return parseInt(parts[0]);
  } catch (e) {
    return 0;
  }
}

const JSON_HEADERS = { 'content-type': 'application/json; charset=utf-8' };
const HTML_HEADERS = { 'content-type': 'text/html; charset=utf-8' };
const DEFAULT_ADMIN = { u: 'admin', p: 'admin123' };

export async function handleAdmin(request, env) {
  const url = new URL(request.url);
  const path = url.pathname;
  const q = parseQuery(url.search);
  try {
    const opts = await getOptions(env.DB);
    env._shopName = opt(opts, 'blogname', 'ACG发卡');
  } catch (e) {
    env._shopName = 'ACG发卡';
  }

  if (request.method === 'POST') {
    const form = await request.formData();
    if (path === '/admin/login') return doLogin(form, env);
    if (path === '/admin/logout') return doLogout();
    if (!(await isAdmin(request, env))) return json({ code: 1, msg: '未登录或登录已过期' });
    if (path === '/admin/goods/save') return saveGoods(form, env);
    if (path === '/admin/goods/delete') return del('dc_goods', 'id', form, env);
    if (path === '/admin/sort/save') return saveSort(form, env);
    if (path === '/admin/sort/delete') return del('dc_sort', 'sid', form, env);
    if (path === '/admin/kami/add') return addKami(form, env);
    if (path === '/admin/kami/import') return importKami(form, env);
    if (path === '/admin/kami/delete') return delKami(form, env);
    if (path === '/admin/order/refund') return refundOrder(form, env);
    if (path === '/admin/order/delete') return del('dc_order', 'id', form, env);
    if (path === '/admin/settings/save') return saveSettings(form, env);
    return json({ code: 1, msg: '未知接口' });
  }

  if (!(await isAdmin(request, env))) {
    return new Response(loginHtml(env._shopName), { headers: HTML_HEADERS });
  }

  if (path === '/admin') return dashboard(request, env);
  if (path === '/admin/goods') return goodsList(request, env, q);
  if (path === '/admin/goods/edit') return goodsEdit(request, env, q);
  if (path === '/admin/kami') return kamiManage(request, env, q);
  if (path === '/admin/orders') return ordersList(request, env, q);
  if (path === '/admin/sorts') return sortsList(request, env, q);
  if (path === '/admin/settings') return settingsPage(request, env, q);
  if (path === '/admin/logout') return redir('/admin');

  return adminPage(request, env, '后台', '<p>未知页面</p><a href="/admin">返回</a>');
}

function json(o) {
  return new Response(JSON.stringify(o), { headers: JSON_HEADERS });
}
function redir(to) {
  return new Response('', { status: 302, headers: { location: to } });
}

async function isAdmin(request, env) {
  const cookie = parseCookie(request.headers.get('Cookie') || '');
  const t = cookie.acg_admin_token;
  if (!t) return false;
  try {
    const dec = atob(t);
    const [user, ts, sig] = dec.split('.');
    if (!user || !ts || !sig) return false;
    const secret = env.SECRET || 'acg-faka-secret';
    const expect = await hmacSha256(secret, user + '.' + ts);
    if (sig !== expect) return false;
    const cfg = { u: env.ADMIN_USERNAME || DEFAULT_ADMIN.u, p: env.ADMIN_PASSWORD || DEFAULT_ADMIN.p };
    return user === cfg.u;
  } catch (e) {
    return false;
  }
}

async function doLogin(form, env) {
  const u = form.get('username') || '';
  const p = form.get('password') || '';
  const cfg = { u: env.ADMIN_USERNAME || DEFAULT_ADMIN.u, p: env.ADMIN_PASSWORD || DEFAULT_ADMIN.p };
  if (u !== cfg.u || p !== cfg.p) return json({ code: 1, msg: '账号或密码错误' });
  const ts = String(now());
  const secret = env.SECRET || 'acg-faka-secret';
  const sig = await hmacSha256(secret, u + '.' + ts);
  const token = btoa(u + '.' + ts + '.' + sig);
  const res = json({ code: 0 });
  res.headers.append('Set-Cookie', 'acg_admin_token=' + token + '; Path=/; HttpOnly; Max-Age=86400');
  return res;
}

function doLogout() {
  const res = redir('/admin');
  res.headers.append('Set-Cookie', 'acg_admin_token=; Path=/; HttpOnly; Max-Age=0');
  return res;
}

function parseCookie(c) {
  const o = {};
  for (const kv of c.split(';')) {
    const i = kv.indexOf('=');
    if (i > 0) o[kv.slice(0, i).trim()] = kv.slice(i + 1).trim();
  }
  return o;
}

// ---------------- 页面框架 (acg-faka 后台 Material 皮肤) ----------------
function adminPage(request, env, title, content) {
  const shopName = (env && env._shopName) || 'ACG发卡';
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
            <div class="menu-item"><a class="menu-link  ${p('/admin', pathOf(request))}" href="/admin"><span class="menu-icon"><svg class="menu-svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M19 5v2h-4V5h4M9 5v6H5V5h4m10 8v6h-4v-6h4M9 17v2H5v-2h4M21 3h-8v6h8V3zM11 3H3v10h8V3zm10 8h-8v10h8V11zm-10 4H3v6h8v-6z"/></svg></span><span class="menu-title">仪表盘</span></a></div>
            <div class="menu-item"><div class="menu-content pt-8 pb-2"><span class="menu-section text-muted text-uppercase fs-8 ls-1">Trade</span></div></div>
            <div class="menu-item"><a class="menu-link  ${p('/admin/goods', pathOf(request))}" href="/admin/goods"><span class="menu-icon"><svg class="menu-svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M20 2H4c-1 0-2 .9-2 2v3.01c0 .72.43 1.34 1 1.69V20c0 1.1 1.1 2 2 2h14c.9 0 2-.9 2-2V8.7c.57-.35 1-.97 1-1.69V4c0-1.1-1-2-2-2zm-1 18H5V9h14v11zm1-13H4V4h16v3z"/><path d="M9 12h6v2H9z"/></svg></span><span class="menu-title">商品管理</span></a></div>
            <div class="menu-item"><a class="menu-link  ${p('/admin/kami', pathOf(request))}" href="/admin/kami"><span class="menu-icon"><svg class="menu-svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M22 19h-6v-4h-2.68c-1.14 2.42-3.6 4-6.32 4c-3.86 0-7-3.14-7-7s3.14-7 7-7c2.72 0 5.17 1.58 6.32 4H24v6h-2v4zm-4-2h2v-4h2v-2H11.94l-.23-.67C11.01 8.34 9.11 7 7 7c-2.76 0-5 2.24-5 5s2.24 5 5 5c2.11 0 4.01-1.34 4.71-3.33l.23-.67H18v4z"/></svg></span><span class="menu-title">卡密管理</span></a></div>
            <div class="menu-item"><a class="menu-link  ${p('/admin/orders', pathOf(request))}" href="/admin/orders"><span class="menu-icon"><svg class="menu-svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M15.55 13c.75 0 1.41-.41 1.75-1.03l3.58-6.49A.996.996 0 0 0 20.01 4H5.21l-.94-2H1v2h2l3.6 7.59l-1.35 2.44C4.52 15.37 5.48 17 7 17h12v-2H7l1.1-2h7.45zM6.16 6h12.15l-2.76 5H8.53L6.16 6zM7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2s-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2s2-.9 2-2s-.9-2-2-2z"/></svg></span><span class="menu-title">商品订单</span></a></div>
            <div class="menu-item"><a class="menu-link  ${p('/admin/sorts', pathOf(request))}" href="/admin/sorts"><span class="menu-icon"><svg class="menu-svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg></span><span class="menu-title">分类管理</span></a></div>
            <div class="menu-item"><div class="menu-content pt-8 pb-2"><span class="menu-section text-muted text-uppercase fs-8 ls-1">Config</span></div></div>
            <div class="menu-item"><a class="menu-link  ${p('/admin/settings', pathOf(request))}" href="/admin/settings"><span class="menu-icon"><svg class="menu-svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M19.43 12.98c.04-.32.07-.64.07-.98c0-.34-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46a.5.5 0 0 0-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65A.488.488 0 0 0 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1a.566.566 0 0 0-.18-.03c-.17 0-.34.09-.43.25l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98c0 .33.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46a.5.5 0 0 0 .61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.06.02.12.03.18.03c.17 0 .34-.09.43-.25l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zm-1.98-1.71c.04.31.05.52.05.73c0 .21-.02.43-.05.73l-.14 1.13l.89.7l1.08.84l-.7 1.21l-1.27-.51l-1.04-.42l-.9.68c-.43.32-.84.56-1.25.73l-1.06.43l-.16 1.13l-.2 1.35h-1.4l-.19-1.35l-.16-1.13l-1.06-.43c-.43-.18-.83-.41-1.23-.71l-.91-.7l-1.06.43l-1.27.51l-.7-1.21l1.08-.84l.89-.7l-.14-1.13zm-5.45-3.27a4 4 0 1 0 0 8a4 4 0 0 0 0-8z"/></svg></span><span class="menu-title">网站设置</span></a></div>
            <div class="menu-item"><div class="menu-content pt-8 pb-2"><span class="menu-section text-muted text-uppercase fs-8 ls-1">Account</span></div></div>
            <div class="menu-item"><a class="menu-link" href="/admin/logout"><span class="menu-icon"><svg class="menu-svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg></span><span class="menu-title">退出登录</span></a></div>
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
                  <div class="menu-item me-lg-1 app-store"><a class="py-3 text-primary fw-bold" href="/"><i class="fa-duotone fa-regular fa-house"></i> 返回前台</a></div>
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
  return cur === target ? 'active' : '';
}

function loginHtml(shopName) {
  return `<!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>登录 - ${esc(shopName)}</title>
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
        <button type="button" class="ay-theme" id="ay-theme" aria-label="切换明暗主题" title="切换明暗主题">
            <svg class="ico-moon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/></svg>
            <svg class="ico-sun" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
        </button>
        <header class="ay-head">
            <div class="ay-logo" aria-hidden="true"></div>
            <h1 id="ay-title" class="ay-title">欢迎回来，指挥官</h1>
            <p id="ay-sub" class="ay-sub">正在验证您的管理员身份</p>
        </header>
        <div class="ay-body">
            <form id="ay-form" method="post" novalidate>
                <div class="ay-field has-ico">
                    <input id="ay-user" name="username" class="ay-input" type="text" placeholder=" " autocomplete="username" autofocus required>
                    <span class="ay-label">用户名</span>
                    <span class="ay-ico" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
                </div>
                <div class="ay-field has-ico">
                    <input id="ay-pass" name="password" class="ay-input" type="password" placeholder=" " autocomplete="current-password" required>
                    <span class="ay-label">密码</span>
                    <span class="ay-ico" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
                    <button type="button" class="ay-eye" id="ay-eye" aria-label="显示密码">
                        <svg class="ay-eye-open" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="ay-eye-shut" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><path d="m1 1 22 22"/></svg>
                    </button>
                    <span class="ay-caps" id="ay-caps" hidden>大写锁定已开启</span>
                </div>
                <div class="ay-row">
                    <label class="ay-check"><input type="checkbox" id="ay-remember" name="remember" value="1">保持登录(24小时)</label>
                </div>
                <div class="ay-err" id="ay-err" hidden></div>
                <button class="ay-btn" type="submit" id="ay-submit">确认登入</button>
            </form>
            <div class="ay-foot">© ${esc(shopName)}</div>
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
    else { $('#ay-err').removeAttr('hidden').text(res.msg || '登录失败'); }
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

// ---------------- 仪表盘 ----------------
async function dashboard(request, env) {
  const s = {
    goods: (await env.DB.prepare('SELECT COUNT(*) AS c FROM dc_goods WHERE delete_time IS NULL').first()).c,
    orders: (await env.DB.prepare('SELECT COUNT(*) AS c FROM dc_order').first()).c,
    paid: (await env.DB.prepare('SELECT COUNT(*) AS c FROM dc_order WHERE pay_status = 1').first()).c,
    income: (await env.DB.prepare('SELECT COALESCE(SUM(amount),0) AS s FROM dc_order WHERE pay_status = 1').first()).s,
  };
  const recent = (await env.DB.prepare('SELECT * FROM dc_order ORDER BY id DESC LIMIT 8').all()).results;
  const statusText = { 0: '待支付', 1: '已支付待发货', 2: '已完成', 3: '已取消' };
  const recentRows = recent
    .map((o) => `<tr><td>${esc(o.out_trade_no)}</td><td>¥${fen2yuan(o.amount)}</td><td>${ts2str(o.create_time)}</td><td>${statusText[o.status] || o.status}</td></tr>`)
    .join('');
  const content = `<div class="row g-5 g-xl-8">
    <div class="col-xl-3 col-md-6"><div class="card card-flush h-xl-100"><div class="card-body pt-7">
      <div class="d-flex align-items-center"><span class="fs-7 fw-semibold text-muted">商品总数</span></div>
      <div class="fs-2x fw-bolder pt-4" style="color:#2667d6;">${s.goods}</div>
    </div></div></div>
    <div class="col-xl-3 col-md-6"><div class="card card-flush h-xl-100"><div class="card-body pt-7">
      <div class="d-flex align-items-center"><span class="fs-7 fw-semibold text-muted">订单总数</span></div>
      <div class="fs-2x fw-bolder pt-4" style="color:#f59e0b;">${s.orders}</div>
    </div></div></div>
    <div class="col-xl-3 col-md-6"><div class="card card-flush h-xl-100"><div class="card-body pt-7">
      <div class="d-flex align-items-center"><span class="fs-7 fw-semibold text-muted">已支付订单</span></div>
      <div class="fs-2x fw-bolder pt-4" style="color:#22c55e;">${s.paid}</div>
    </div></div></div>
    <div class="col-xl-3 col-md-6"><div class="card card-flush h-xl-100"><div class="card-body pt-7">
      <div class="d-flex align-items-center"><span class="fs-7 fw-semibold text-muted">实收金额 (元)</span></div>
      <div class="fs-2x fw-bolder pt-4" style="color:#ef4444;">${fen2yuan(s.income)}</div>
    </div></div></div>
  </div>
  <div class="card mt-10">
    <div class="card-header"><h3 class="card-title">最近订单</h3></div>
    <div class="card-body py-4">
    <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3"><thead><tr class="fw-bold text-muted"><th>订单号</th><th>金额</th><th>时间</th><th>状态</th></tr></thead><tbody>${recentRows || '<tr><td colspan="4" class="text-center text-muted py-6">暂无订单</td></tr>'}</tbody></table>
    </div>
  </div>`;
  return adminPage(request, env, '仪表盘', content);
}

// ---------------- 商品管理 ----------------
async function goodsList(request, env, q) {
  const kw = (q.q || '').trim();
  let sql = 'SELECT g.*, s.sortname AS sort_name FROM dc_goods g LEFT JOIN dc_sort s ON s.sid = g.sort_id WHERE g.delete_time IS NULL';
  const binds = [];
  if (kw) {
    sql += ' AND g.title LIKE ?';
    binds.push('%' + kw + '%');
  }
  sql += ' ORDER BY g.id DESC LIMIT 100';
  const goods = (await env.DB.prepare(sql).bind(...binds).all()).results;
  const rows = goods
    .map(
      (g) => `<tr>
    <td>${g.id}</td>
    <td>${esc(g.title)}</td>
    <td>${esc(g.sort_name || '-')}</td>
    <td>${esc(TNAME[g.type] || g.type)}</td>
    <td>${g.stock}</td>
    <td>${g.sales}</td>
    <td>${g.is_on_shelf == 1 ? '<span class="text-success">上架</span>' : '<span class="text-muted">下架</span>'}</td>
    <td><a class="btn btn-light-primary btn-sm" href="/admin/goods/edit?id=${g.id}" style="margin-right:6px;">编辑</a><a class="btn btn-secondary btn-sm" href="/admin/kami?goods_id=${g.id}" style="margin-right:6px;">卡密</a><button class="btn btn-danger btn-sm" onclick="delGoods(${g.id})">删除</button></td>
  </tr>`
    )
    .join('');
  const content = `<div class="card">
  <div class="card-body py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <form action="/admin/goods" method="get" class="d-flex gap-2"><input type="text" name="q" class="form-control" placeholder="搜索商品" value="${esc(kw)}" style="width:220px;"><button class="btn btn-outline-secondary">搜索</button></form>
    <a class="btn btn-success" href="/admin/goods/edit">+ 新增商品</a>
  </div>
  <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3"><thead><tr class="fw-bold text-muted"><th>ID</th><th>标题</th><th>分类</th><th>类型</th><th>库存</th><th>销量</th><th>状态</th><th>操作</th></tr></thead>
  <tbody>${rows || '<tr><td colspan="8" class="text-center text-muted py-6">暂无商品</td></tr>'}</tbody></table>
  </div>
  </div>
  <script>function delGoods(id){ if(!confirm('确认删除该商品？')) return; $.post('/admin/goods/delete',{id:id},function(r){ if(r.code===0) location.reload(); else alert(r.msg); },'json'); }</script>`;
  return adminPage(request, env, '商品管理', content);
}

const TNAME = { once: '一卡一密', general: '通用卡密', service: '虚拟服务', duli: '独立对接', physical: '实物' };

async function goodsEdit(request, env, q) {
  const id = parseInt(q.id) || 0;
  let g = null;
  let gSkus = [];
  if (id) {
    g = await env.DB.prepare('SELECT * FROM dc_goods WHERE id = ?').bind(id).first();
    gSkus = (await env.DB.prepare('SELECT * FROM dc_skus WHERE goods_id = ? ORDER BY id ASC').bind(id).all()).results;
  }
  const sorts = (await env.DB.prepare("SELECT * FROM dc_sort WHERE type = 'goods' ORDER BY taxis ASC").all()).results;
  const types = (await env.DB.prepare('SELECT * FROM dc_goods_type WHERE delete_time IS NULL').all()).results;
  const gv = (k, def) => (g ? g[k] : def);
  const skusText = gSkus.length
    ? gSkus.map((s) => `${s.sku}|${s.guest_price}|${s.market_price || 0}|${s.stock}`).join('\n')
    : '0|1000|1200|100';

  const sortOptions = sorts.map((s) => `<option value="${s.sid}" ${g && g.sort_id == s.sid ? 'selected' : ''}>${esc(s.sortname)}</option>`).join('');
  const typeOptions = types.map((t) => `<option value="${t.id}" ${g && g.attr_id == t.id ? 'selected' : ''}>${esc(t.name)}</option>`).join('');
  const typeMode = (t, def) => `<select name="type" class="form-select"><option value="once" ${gv('type', 'once') === 'once' ? 'selected' : ''}>一卡一密</option><option value="general" ${gv('type', 'general') === 'general' ? 'selected' : ''}>通用卡密</option><option value="service" ${gv('type', 'service') === 'service' ? 'selected' : ''}>虚拟服务</option></select>`;

  const content = `<div class="card" style="max-width:920px;">
  <div class="card-body py-5">
  <form id="frm" method="post">
    <input type="hidden" name="id" value="${id}">
    <label class="form-label">商品标题 *</label><input type="text" class="form-control" name="title" required value="${esc(gv('title', ''))}">
    <div class="row g-3 mt-1">
      <div class="col-md-3"><label class="form-label">所属分类</label><select class="form-select" name="sort_id">${sortOptions}</select></div>
      <div class="col-md-3"><label class="form-label">商品模式</label>${typeMode()}</div>
      <div class="col-md-3"><label class="form-label">规格类型 (多规格时选择)</label><select class="form-select" name="attr_id"><option value="0">无</option>${typeOptions}</select></div>
      <div class="col-md-3"><label class="form-label">是否多规格</label><select class="form-select" name="is_sku"><option value="n" ${gv('is_sku', 'n') === 'n' ? 'selected' : ''}>否</option><option value="y" ${gv('is_sku', 'y') === 'y' ? 'selected' : ''}>是</option></select></div>
    </div>
    <div class="row g-3 mt-1">
      <div class="col-md-3"><label class="form-label">计量单位</label><input type="text" class="form-control" name="unit_name" value="${esc(gv('unit_name', '个'))}"></div>
      <div class="col-md-3"><label class="form-label">初始库存</label><input type="number" class="form-control" name="stock" value="${gv('stock', 0)}" min="0"></div>
      <div class="col-md-3"><label class="form-label">初始销量</label><input type="number" class="form-control" name="sales" value="${gv('sales', 0)}" min="0"></div>
      <div class="col-md-3"><label class="form-label">上架状态</label><select class="form-select" name="is_on_shelf"><option value="1" ${gv('is_on_shelf', 1) == 1 ? 'selected' : ''}>上架</option><option value="0" ${gv('is_on_shelf', 0) == 0 ? 'selected' : ''}>下架</option></select></div>
    </div>
    <label class="form-label mt-3">商品简介</label><textarea class="form-control" name="des">${esc(gv('des', ''))}</textarea>
    <label class="form-label">商品详情 (HTML)</label><textarea class="form-control" name="content" style="min-height:140px;">${esc(gv('content', ''))}</textarea>
    <label class="form-label">购买后说明 (HTML)</label><textarea class="form-control" name="pay_content">${esc(gv('pay_content', ''))}</textarea>
    <label class="form-label">封面图 URL</label><input type="text" class="form-control" name="cover" value="${esc(gv('cover', ''))}">
    <label class="form-label">规格/SKU 与价格 (每行: SKU组合|售价(分)|市场价(分)|库存, SKU组合如 1-5 或 0)</label>
    <textarea class="form-control" name="skus_text" style="min-height:120px;">${esc(skusText)}</textarea>
    <button type="submit" class="btn btn-success mt-4">保 存</button>
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
  return adminPage(request, env, id ? '编辑商品 #' + id : '新增商品', content);
}

async function saveGoods(form, env) {
  const id = parseInt(form.get('id')) || 0;
  const title = (form.get('title') || '').trim();
  if (!title) return json({ code: 1, msg: '标题不能为空' });
  const sort_id = parseInt(form.get('sort_id')) || 0;
  const type = form.get('type') || 'once';
  const attr_id = parseInt(form.get('attr_id')) || 0;
  const is_sku = form.get('is_sku') === 'y' ? 'y' : 'n';
  const unit_name = (form.get('unit_name') || '个').trim();
  const stock = parseInt(form.get('stock')) || 0;
  const sales = parseInt(form.get('sales')) || 0;
  const is_on_shelf = parseInt(form.get('is_on_shelf')) || 0;
  const des = form.get('des') || '';
  const content = form.get('content') || '';
  const pay_content = form.get('pay_content') || '';
  const cover = form.get('cover') || '';

  const t = now();
  if (id) {
    await env.DB.prepare('UPDATE dc_goods SET title=?, sort_id=?, type=?, attr_id=?, is_sku=?, unit_name=?, stock=?, sales=?, is_on_shelf=?, des=?, content=?, pay_content=?, cover=? WHERE id=?')
      .bind(title, sort_id, type, attr_id, is_sku, unit_name, stock, sales, is_on_shelf, des, content, pay_content, cover, id)
      .run();
    const gid = id;
    // 重新写入 SKU
    await env.DB.prepare('DELETE FROM dc_skus WHERE goods_id = ?').bind(gid).run();
    await insertSkus(env, gid, form.get('skus_text') || '');
  } else {
    const r = await env.DB.prepare('INSERT INTO dc_goods (title, sort_id, type, attr_id, is_sku, unit_name, stock, sales, is_on_shelf, des, content, pay_content, cover, create_time) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
      .bind(title, sort_id, type, attr_id, is_sku, unit_name, stock, sales, is_on_shelf, des, content, pay_content, cover, t)
      .run();
    const gid = r.meta.last_row_id;
    await insertSkus(env, gid, form.get('skus_text') || '0|1000|1200|100');
  }
  return json({ code: 0 });
}

async function insertSkus(env, gid, text) {
  const lines = String(text)
    .split('\n')
    .map((l) => l.trim())
    .filter((l) => l);
  const batch = [];
  for (const line of lines) {
    const p = line.split('|');
    if (p.length < 1) continue;
    const sku = p[0].trim();
    const guest = parseInt(p[1]) || 0;
    const market = parseInt(p[2]) || 0;
    const st = parseInt(p[3]) || 0;
    batch.push(env.DB.prepare('INSERT INTO dc_skus (goods_id, sku, guest_price, market_price, stock) VALUES (?,?,?,?,?)').bind(gid, sku, guest, market, st));
  }
  if (batch.length) await env.DB.batch(batch);
}

// ---------------- 卡密管理 ----------------
async function kamiManage(request, env, q) {
  const gid = parseInt(q.goods_id) || 0;
  const goods = gid ? await env.DB.prepare('SELECT * FROM dc_goods WHERE id = ?').bind(gid).first() : null;
  if (!goods) {
    const goodsList = (await env.DB.prepare("SELECT id, title, type FROM dc_goods WHERE delete_time IS NULL AND type IN ('once','general') ORDER BY id DESC LIMIT 50").all()).results;
    const rows = goodsList
      .map((g) => `<tr><td>${g.id}</td><td>${esc(g.title)}</td><td>${TNAME[g.type]}</td><td><a class="btn btn-primary btn-sm" href="/admin/kami?goods_id=${g.id}">管理卡密</a></td></tr>`)
      .join('');
    return adminPage(request, env, '卡密管理', `<div class="card"><div class="card-body py-5"><div class="table-responsive"><table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3"><thead><tr class="fw-bold text-muted"><th>ID</th><th>商品</th><th>模式</th><th>操作</th></tr></thead><tbody>${rows || '<tr><td colspan="4" class="text-center text-muted py-6">暂无卡密类商品</td></tr>'}</tbody></table></div></div></div>`);
  }

  let rows = '';
  if (goods.type === 'once') {
    const list = (await env.DB.prepare('SELECT * FROM dc_goods_once WHERE goods_id = ? ORDER BY id DESC LIMIT 200').bind(gid).all()).results;
    rows = list
      .map(
        (k) => `<tr><td>${k.id}</td><td>${k.sku}</td><td style="word-break:break-all;">${esc(k.content)}</td><td>${k.sale_time ? ts2str(k.sale_time) : '<span class="text-muted">未售出</span>'}</td><td><button class="btn btn-danger btn-sm" onclick="delK(${k.id})">删除</button></td></tr>`
      )
      .join('');
  } else if (goods.type === 'general') {
    const list = (await env.DB.prepare('SELECT * FROM dc_goods_general WHERE goods_id = ? ORDER BY id DESC').bind(gid).all()).results;
    rows = list
      .map(
        (k) => `<tr><td>${k.id}</td><td>${k.sku}</td><td style="word-break:break-all;">${esc(k.content)}</td><td>${ts2str(k.create_time)}</td><td><button class="btn btn-danger btn-sm" onclick="delG(${k.id})">删除</button></td></tr>`
      )
      .join('');
  }

  const content = `<div class="card">
    <div class="card-body py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="card-title mb-0">${esc(goods.title)} <span class="text-muted fs-6">(${TNAME[goods.type]})</span></h3>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/goods">返回商品</a>
    </div>
    ${goods.type === 'once' ? `<div class="row g-3 mb-4">
      <div class="col-md-6"><label class="form-label">新增一卡一密 (每行一条卡密)</label><textarea class="form-control" id="newKami" placeholder="KAMI-0001&#10;KAMI-0002"></textarea><button class="btn btn-success mt-2" onclick="addKami()">添加</button></div>
      <div class="col-md-6"><label class="form-label">批量导入 (每行一条卡密)</label><textarea class="form-control" id="importKami" placeholder="一行一条"></textarea><button class="btn btn-primary mt-2" onclick="impKami()">导入</button></div>
    </div>` : `<div class="row g-3 mb-4">
      <div class="col-md-6"><label class="form-label">新增通用卡密 (SKU|内容)</label><textarea class="form-control" id="newKami" placeholder="0|VIP-2026-0001"></textarea><button class="btn btn-success mt-2" onclick="addKami()">添加</button></div>
    </div>`}
    <div class="table-responsive">
    <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3"><thead><tr class="fw-bold text-muted"><th>ID</th><th>SKU</th><th>内容</th>${goods.type === 'once' ? '<th>售出时间</th>' : '<th>添加时间</th>'}<th>操作</th></tr></thead><tbody>${rows || '<tr><td colspan="5" class="text-center text-muted py-6">暂无卡密</td></tr>'}</tbody></table>
    </div>
    </div>
    </div>
  <script>
  function addKami(){ $.post('/admin/kami/add',{goods_id:${gid},text:$('#newKami').val()},function(r){ if(r.code===0) location.reload(); else alert(r.msg); },'json'); }
  function impKami(){ $.post('/admin/kami/import',{goods_id:${gid},text:$('#importKami').val()},function(r){ if(r.code===0) location.reload(); else alert(r.msg); },'json'); }
  function delK(id){ if(!confirm('确认删除该卡密？')) return; $.post('/admin/kami/delete',{id:id,goods_id:${gid},type:'once'},function(r){ if(r.code===0) location.reload(); else alert(r.msg); },'json'); }
  function delG(id){ if(!confirm('确认删除该卡密？')) return; $.post('/admin/kami/delete',{id:id,goods_id:${gid},type:'general'},function(r){ if(r.code===0) location.reload(); else alert(r.msg); },'json'); }
  </script>`;
  return adminPage(request, env, '卡密管理', content);
}

async function addKami(form, env) {
  const gid = parseInt(form.get('goods_id')) || 0;
  const text = (form.get('text') || '').trim();
  if (!gid || !text) return json({ code: 1, msg: '参数错误' });
  const goods = await env.DB.prepare('SELECT * FROM dc_goods WHERE id = ?').bind(gid).first();
  if (!goods) return json({ code: 1, msg: '商品不存在' });
  const t = now();
  const batch = [];
  if (goods.type === 'once') {
    for (const line of text.split('\n')) {
      const v = line.trim();
      if (v) batch.push(env.DB.prepare('INSERT INTO dc_goods_once (goods_id, sku, batch_no, content, create_time, order_list_id) VALUES (?,?,?,?,?,0)').bind(gid, '0', 'manual', v, t));
    }
  } else if (goods.type === 'general') {
    for (const line of text.split('\n')) {
      const v = line.trim();
      if (!v) continue;
      const p = v.split('|');
      const sku = p.length > 1 ? p[0].trim() : '0';
      const content = p.length > 1 ? p.slice(1).join('|').trim() : v;
      batch.push(env.DB.prepare('INSERT INTO dc_goods_general (goods_id, sku, content, create_time) VALUES (?,?,?,?)').bind(gid, sku, content, t));
    }
  }
  if (batch.length) {
    await env.DB.batch(batch);
    // 重新统计库存
    await recalcStock(env, gid);
  }
  return json({ code: 0, count: batch.length });
}

async function importKami(form, env) {
  return addKami(form, env);
}

async function delKami(form, env) {
  const id = parseInt(form.get('id')) || 0;
  const type = form.get('type') || 'once';
  const gid = parseInt(form.get('goods_id')) || 0;
  if (type === 'once') await env.DB.prepare('DELETE FROM dc_goods_once WHERE id = ?').bind(id).run();
  else await env.DB.prepare('DELETE FROM dc_goods_general WHERE id = ?').bind(id).run();
  if (gid) await recalcStock(env, gid);
  return json({ code: 0 });
}

async function recalcStock(env, gid) {
  const goods = await env.DB.prepare('SELECT type FROM dc_goods WHERE id = ?').bind(gid).first();
  if (!goods) return;
  let stock = 0;
  if (goods.type === 'once') stock = (await env.DB.prepare('SELECT COUNT(*) AS c FROM dc_goods_once WHERE goods_id = ? AND sale_time IS NULL').bind(gid).first()).c;
  if (goods.type === 'general') stock = (await env.DB.prepare('SELECT COUNT(*) AS c FROM dc_goods_general WHERE goods_id = ?').bind(gid).first()).c;
  await env.DB.prepare('UPDATE dc_goods SET stock = ? WHERE id = ?').bind(stock, gid).run();
  // 更新所有 sku 行的库存为统计值（单 sku 商品）
  const skus = (await env.DB.prepare('SELECT * FROM dc_skus WHERE goods_id = ?').bind(gid).all()).results;
  if (skus.length === 1) await env.DB.prepare('UPDATE dc_skus SET stock = ? WHERE id = ?').bind(stock, skus[0].id);
}

// ---------------- 订单管理 ----------------
async function ordersList(request, env, q) {
  const kw = (q.q || '').trim();
  let sql = 'SELECT o.* FROM dc_order o WHERE 1=1';
  const binds = [];
  if (kw) {
    sql += ' AND (o.out_trade_no LIKE ? OR o.tel LIKE ? OR o.email LIKE ?)';
    binds.push('%' + kw + '%', '%' + kw + '%', '%' + kw + '%');
  }
  sql += ' ORDER BY o.id DESC LIMIT 100';
  const orders = (await env.DB.prepare(sql).bind(...binds).all()).results;
  const statusText = { 0: '待支付', 1: '已支付待发货', 2: '已完成', 3: '已取消' };
  const rows = orders
    .map(
      (o) => `<tr>
    <td>${o.id}</td><td>${esc(o.out_trade_no)}</td><td>¥${fen2yuan(o.amount)}</td>
    <td>${ts2str(o.create_time)}</td><td>${esc(o.payment || '-')}</td>
    <td>${statusText[o.status] || o.status}</td>
    <td><a class="btn btn-light-primary btn-sm" href="/?action=order_result&out_trade_no=${esc(o.out_trade_no)}" target="_blank">详情</a>
    ${o.pay_status == 1 ? `<button class="btn btn-danger btn-sm" onclick="refund(${o.id},${JSON.stringify(o.out_trade_no).replace(/"/g, '&quot;')})">退款</button>` : ''}
    <button class="btn btn-outline-secondary btn-sm" onclick="delO(${o.id})">删除</button></td>
  </tr>`
    )
    .join('');
  const content = `<div class="card">
  <div class="card-body py-5">
  <form action="/admin/orders" method="get" class="d-flex gap-2 mb-4"><input type="text" name="q" class="form-control" placeholder="订单号/联系方式" value="${esc(kw)}" style="width:260px;"><button class="btn btn-outline-secondary">搜索</button></form>
  <div class="table-responsive">
  <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3"><thead><tr class="fw-bold text-muted"><th>ID</th><th>订单号</th><th>金额</th><th>时间</th><th>支付</th><th>状态</th><th>操作</th></tr></thead>
  <tbody>${rows || '<tr><td colspan="7" class="text-center text-muted py-6">暂无订单</td></tr>'}</tbody></table>
  </div>
  </div>
  </div>
  <script>
  function refund(id){ if(!confirm('确认退款？')) return; $.post('/admin/order/refund',{id:id},function(r){ if(r.code===0) location.reload(); else alert(r.msg); },'json'); }
  function delO(id){ if(!confirm('确认删除订单？')) return; $.post('/admin/order/delete',{id:id},function(r){ if(r.code===0) location.reload(); else alert(r.msg); },'json'); }
  </script>`;
  return adminPage(request, env, '订单管理', content);
}

async function refundOrder(form, env) {
  const id = parseInt(form.get('id')) || 0;
  const order = await env.DB.prepare('SELECT * FROM dc_order WHERE id = ?').bind(id).first();
  if (!order) return json({ code: 1, msg: '订单不存在' });
  if (order.pay_status != 1) return json({ code: 1, msg: '仅已支付订单可退款' });
  const t = now();
  // 退款：状态置已取消，若有余额支付则退回余额
  if (order.pay_plugin === 'balance' && order.user_id) {
    await env.DB.prepare('UPDATE dc_user SET money = round((money * 100 + ?) / 100.0, 2) WHERE uid = ?').bind(order.amount, order.user_id).run();
  }
  await env.DB.prepare("UPDATE dc_order SET status = 3, delete_time = ? WHERE id = ?").bind(t, id).run();
  return json({ code: 0, msg: '已退款' });
}

async function del(table, key, form, env) {
  const id = parseInt(form.get('id')) || 0;
  if (!id) return json({ code: 1, msg: '参数错误' });
  await env.DB.prepare('DELETE FROM ' + table + ' WHERE ' + key + ' = ?').bind(id).run();
  return json({ code: 0 });
}

// ---------------- 分类管理 ----------------
async function sortsList(request, env, q) {
  const sorts = (await env.DB.prepare("SELECT * FROM dc_sort WHERE type = 'goods' AND delete_time IS NULL ORDER BY taxis ASC").all()).results;
  const rows = sorts
    .map((s) => `<tr><td>${s.sid}</td><td>${esc(s.sortname)}</td><td>${esc(s.alias)}</td><td>${s.taxis}</td><td>${esc(s.sorticon || '-')}</td><td><button class="btn btn-danger btn-sm" onclick="delS(${s.sid})">删除</button></td></tr>`)
    .join('');
  const content = `<div class="card">
  <div class="card-body py-5">
  <div class="row g-3 mb-4 align-items-end">
    <div class="col-md-3"><label class="form-label">分类名称</label><input type="text" class="form-control" id="sName"></div>
    <div class="col-md-3"><label class="form-label">别名 (alias)</label><input type="text" class="form-control" id="sAlias"></div>
    <div class="col-md-2"><label class="form-label">排序 (小=前)</label><input type="number" class="form-control" id="sTaxis" value="0"></div>
    <div class="col-md-3"><label class="form-label">图标 (remixicon 类名)</label><input type="text" class="form-control" id="sIcon" placeholder="ri-gamepad-line"></div>
    <div class="col-md-1"><button class="btn btn-success w-100" onclick="addS()">新增分类</button></div>
  </div>
  <div class="table-responsive">
  <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3"><thead><tr class="fw-bold text-muted"><th>ID</th><th>名称</th><th>别名</th><th>排序</th><th>图标</th><th>操作</th></tr></thead>
  <tbody>${rows || '<tr><td colspan="6" class="text-center text-muted py-6">暂无分类</td></tr>'}</tbody></table>
  </div>
  </div>
  </div>
  <script>
  function addS(){ $.post('/admin/sort/save',{name:$('#sName').val(),alias:$('#sAlias').val(),taxis:$('#sTaxis').val(),icon:$('#sIcon').val()},function(r){ if(r.code===0) location.reload(); else alert(r.msg); },'json'); }
  function delS(id){ if(!confirm('确认删除该分类？')) return; $.post('/admin/sort/delete',{sid:id},function(r){ if(r.code===0) location.reload(); else alert(r.msg); },'json'); }
  </script>`;
  return adminPage(request, env, '分类管理', content);
}

async function saveSort(form, env) {
  const name = (form.get('name') || '').trim();
  if (!name) return json({ code: 1, msg: '分类名不能为空' });
  const alias = (form.get('alias') || '').trim();
  const taxis = parseInt(form.get('taxis')) || 0;
  const icon = (form.get('icon') || '').trim();
  await env.DB.prepare("INSERT INTO dc_sort (type, sortname, alias, taxis, sorticon, station_id) VALUES ('goods',?,?,?,?,0)").bind(name, alias, taxis, icon).run();
  return json({ code: 0 });
}

// ---------------- 站点设置 ----------------
async function settingsPage(request, env) {
  const opts = await getOptions(env.DB);
  const g = (k, def) => opt(opts, k, def);
  const theme = (await env.DB.prepare("SELECT data FROM dc_tpl_options_data WHERE template = 'front_default' AND name = 'theme_primary' LIMIT 1").first());
  let primary = '#2196F3';
  try {
    if (theme && theme.data) primary = JSON.parse(theme.data.replace(/^s:\d+:"|";$/, (m) => (m[0] === 's' ? '' : '')));
  } catch (e) {}
  const content = `<div class="card" style="max-width:820px;">
  <div class="card-body py-5">
  <form id="frm">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">站点名称</label><input type="text" class="form-control" name="blogname" value="${esc(g('blogname', 'ACG发卡'))}"></div>
      <div class="col-md-6"><label class="form-label">副标题</label><input type="text" class="form-control" name="site_subtitle" value="${esc(g('site_subtitle', ''))}"></div>
    </div>
    <label class="form-label mt-3">页脚信息 (支持 HTML)</label><textarea class="form-control" name="footer_info" style="min-height:60px;">${esc(g('footer_info', ''))}</textarea>
    <label class="form-label">滚动公告 (多行: 每行一条)</label><textarea class="form-control" name="roll_bulletin">${esc(g('roll_bulletin', ''))}</textarea>
    <label class="form-label">首页公告 (HTML)</label><textarea class="form-control" name="home_bulletin" style="min-height:90px;">${esc(g('home_bulletin', ''))}</textarea>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">查单必填设置 (JSON)</label><input type="text" class="form-control" name="order_required" value="${esc(g('order_required', ''))}"></div>
      <div class="col-md-6"><label class="form-label">主题主色</label><input type="color" class="form-control form-control-color" name="theme_primary" value="${esc(primary)}" style="height:38px;width:60px;padding:2px;"></div>
    </div>
    <button type="submit" class="btn btn-success mt-4">保存设置</button>
    <span class="text-danger ms-3" id="fmsg"></span>
  </form>
  </div>
  </div>
  <script>
  $('#frm').on('submit', function(e){
    e.preventDefault();
    $.post('/admin/settings/save', $(this).serialize(), function(r){
      if (r.code === 0) { $('#fmsg').text('已保存').addClass('ok'); setTimeout(function(){ location.reload(); }, 500); }
      else { $('#fmsg').text(r.msg); }
    }, 'json');
  });
  </script>`;
  return adminPage(request, env, '站点设置', content);
}

async function saveSettings(form, env) {
  const keys = ['blogname', 'site_subtitle', 'footer_info', 'roll_bulletin', 'home_bulletin', 'order_required'];
  const batch = [];
  for (const k of keys) {
    const v = form.get(k) || '';
    await env.DB.prepare('INSERT INTO dc_options (option_name, option_value) VALUES (?, ?) ON CONFLICT(option_name) DO UPDATE SET option_value = excluded.option_value').bind(k, v).run();
  }
  const themePrimary = form.get('theme_primary') || '#2196F3';
  await env.DB.prepare("UPDATE dc_tpl_options_data SET data = ? WHERE template = 'front_default' AND name = 'theme_primary'").bind('s:' + themePrimary.length + ':"' + themePrimary + '";').run();
  return json({ code: 0 });
}