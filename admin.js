// ============================================================
// DCSHOP Cloudflare 部署版 - 管理后台
// 登录: env.ADMIN_USERNAME / env.ADMIN_PASSWORD (默认 admin / admin123)
// ============================================================
import { esc, fen2yuan, ts2str, now, parseQuery, getOptions, opt, hmacSha256 } from './lib.js';

// 本地 admin token 校验（避免与 worker.js 循环依赖）
async function verifyAdminToken(env, token) {
  try {
    const dec = atob(token);
    const parts = dec.split('.');
    if (parts.length !== 3) return 0;
    const secret = env.SECRET || 'dc-faka-secret';
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
    return adminPage(request, env, '后台登录', loginHtml());
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
  const t = cookie.dc_admin_token;
  if (!t) return false;
  try {
    const dec = atob(t);
    const [user, ts, sig] = dec.split('.');
    if (!user || !ts || !sig) return false;
    const secret = env.SECRET || 'dc-faka-secret';
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
  const secret = env.SECRET || 'dc-faka-secret';
  const sig = await hmacSha256(secret, u + '.' + ts);
  const token = btoa(u + '.' + ts + '.' + sig);
  const res = json({ code: 0 });
  res.headers.append('Set-Cookie', 'dc_admin_token=' + token + '; Path=/; HttpOnly; Max-Age=86400');
  return res;
}

function doLogout() {
  const res = redir('/admin');
  res.headers.append('Set-Cookie', 'dc_admin_token=; Path=/; HttpOnly; Max-Age=0');
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

// ---------------- 页面框架 ----------------
function adminPage(request, env, title, content) {
  return new Response(
    `<!DOCTYPE html><html lang="zh-cn"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>${esc(title)} - 后台管理</title>
<link rel="stylesheet" href="/vendor/remixicon/remixicon.css">
<script src="/vendor/jquery.min.js"></script>
<script src="/vendor/layui/layui.js"></script>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{background:#f2f4f7;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','PingFang SC','Microsoft YaHei',sans-serif;color:#333;}
a{color:#2f69d9;text-decoration:none;}
.wrap{display:flex;min-height:100vh;}
.side{width:200px;background:#1e293b;color:#cbd5e1;padding:16px 0;flex-shrink:0;position:sticky;top:0;height:100vh;}
.side .brand{padding:6px 18px 18px;font-size:16px;font-weight:600;color:#fff;border-bottom:1px solid #334155;white-space:nowrap;}
.side a{display:flex;align-items:center;gap:8px;padding:11px 18px;color:#cbd5e1;font-size:14px;}
.side a:hover,.side a.on{background:#334155;color:#fff;}
.side a i{font-size:16px;}
.main{flex:1;padding:22px 26px;min-width:0;}
.card{background:#fff;border-radius:10px;padding:20px 24px;margin-bottom:18px;box-shadow:0 1px 3px rgba(0,0,0,.05);}
h2{font-size:18px;margin-bottom:16px;}
table{width:100%;border-collapse:collapse;font-size:14px;}
th,td{padding:9px 10px;border-bottom:1px solid #eee;text-align:left;}
th{background:#f8fafc;color:#64748b;font-weight:600;}
tr:hover td{background:#fafbfc;}
.btn{display:inline-block;padding:6px 14px;border-radius:6px;background:var(--tc,#2f69d9);color:#fff;border:none;font-size:13px;cursor:pointer;}
.btn.gray{background:#94a3b8;}
.btn.red{background:#ef4444;}
.btn.green{background:#22c55e;}
.btn.ghost{background:#fff;color:#475569;border:1px solid #d1d5db;}
.inline{display:inline-block;vertical-align:middle;}
input[type=text],input[type=number],input[type=password],select,textarea{width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;margin-bottom:10px;}
textarea{min-height:80px;}
label{display:block;font-size:13px;color:#64748b;margin:8px 0 4px;}
.row{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;}
.stat{display:flex;gap:16px;flex-wrap:wrap;}
.stat .item{flex:1;min-width:160px;background:#fff;border-radius:10px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,.05);}
.stat .num{font-size:26px;font-weight:700;margin-top:6px;}
.muted{color:#94a3b8;font-size:12px;}
.msg{color:#ef4444;font-size:13px;margin-top:8px;}
.ok{color:#22c55e;}
</style>
</head><body>
<div class="wrap">
  <div class="side"><div class="brand">DCSHOP 后台</div>
    <a href="/admin"><i class="ri-dashboard-line"></i>仪表盘</a>
    <a href="/admin/goods"><i class="ri-shopping-bag-3-line"></i>商品管理</a>
    <a href="/admin/kami"><i class="ri-key-2-line"></i>卡密管理</a>
    <a href="/admin/orders"><i class="ri-file-list-3-line"></i>订单管理</a>
    <a href="/admin/sorts"><i class="ri-folders-line"></i>分类管理</a>
    <a href="/admin/settings"><i class="ri-settings-3-line"></i>站点设置</a>
    <a href="/admin/logout"><i class="ri-logout-box-line"></i>退出登录</a>
  </div>
  <div class="main"><h2>${esc(title)}</h2>${content}</div>
</div>
<script>
$(function(){ var p=location.pathname; $('.side a').each(function(){ if($(this).attr('href')===p) $(this).addClass('on'); }); });
</script>
</body></html>`,
    { headers: HTML_HEADERS }
  );
}

function loginHtml() {
  return `<div style="max-width:380px;margin:60px auto;">
<div class="card">
  <h2>后台登录</h2>
  <label>用户名</label><input type="text" id="lUser" placeholder="admin">
  <label>密码</label><input type="password" id="lPwd" placeholder="password">
  <button class="btn" id="btnLogin" style="width:100%;padding:11px;">登 录</button>
  <div class="msg" id="loginMsg"></div>
</div>
</div>
<script>
$('#btnLogin').on('click', function(){
  $.post('/admin/login', { username: $('#lUser').val(), password: $('#lPwd').val() }, function(res){
    if (res.code === 0) { location.href = '/admin'; } else { $('#loginMsg').text(res.msg); }
  }, 'json');
});
$(document).on('keydown', function(e){ if (e.key === 'Enter') $('#btnLogin').click(); });
</script>`;
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
  const content = `<div class="stat">
    <div class="item"><div class="muted">商品总数</div><div class="num" style="color:#2f69d9;">${s.goods}</div></div>
    <div class="item"><div class="muted">订单总数</div><div class="num" style="color:#f59e0b;">${s.orders}</div></div>
    <div class="item"><div class="muted">已支付订单</div><div class="num" style="color:#22c55e;">${s.paid}</div></div>
    <div class="item"><div class="muted">实收金额 (元)</div><div class="num" style="color:#ef4444;">${fen2yuan(s.income)}</div></div>
  </div>
  <div class="card" style="margin-top:18px;"><h2>最近订单</h2>
  <table><thead><tr><th>订单号</th><th>金额</th><th>时间</th><th>状态</th></tr></thead><tbody>${recentRows || '<tr><td colspan="4" style="text-align:center;color:#999;">暂无订单</td></tr>'}</tbody></table></div>`;
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
    <td>${g.is_on_shelf == 1 ? '<span class="ok">上架</span>' : '<span class="muted">下架</span>'}</td>
    <td><a class="btn" href="/admin/goods/edit?id=${g.id}" style="margin-right:6px;">编辑</a><a class="btn gray" href="/admin/kami?goods_id=${g.id}" style="margin-right:6px;">卡密</a><button class="btn red" onclick="delGoods(${g.id})">删除</button></td>
  </tr>`
    )
    .join('');
  const content = `<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
    <form action="/admin/goods" method="get" style="display:flex;gap:8px;"><input type="text" name="q" placeholder="搜索商品" value="${esc(kw)}" style="width:220px;margin:0;"><button class="btn ghost">搜索</button></form>
    <a class="btn green" href="/admin/goods/edit">+ 新增商品</a>
  </div>
  <table><thead><tr><th>ID</th><th>标题</th><th>分类</th><th>类型</th><th>库存</th><th>销量</th><th>状态</th><th>操作</th></tr></thead>
  <tbody>${rows || '<tr><td colspan="8" style="text-align:center;color:#999;">暂无商品</td></tr>'}</tbody></table>
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
  const typeMode = (t, def) => `<select name="type"><option value="once" ${gv('type', 'once') === 'once' ? 'selected' : ''}>一卡一密</option><option value="general" ${gv('type', 'general') === 'general' ? 'selected' : ''}>通用卡密</option><option value="service" ${gv('type', 'service') === 'service' ? 'selected' : ''}>虚拟服务</option></select>`;

  const content = `<div class="card" style="max-width:860px;">
  <form id="frm" method="post">
    <input type="hidden" name="id" value="${id}">
    <label>商品标题 *</label><input type="text" name="title" required value="${esc(gv('title', ''))}">
    <div class="row">
      <div><label>所属分类</label><select name="sort_id">${sortOptions}</select></div>
      <div><label>商品模式</label>${typeMode()}</div>
      <div><label>规格类型 (多规格时选择)</label><select name="attr_id"><option value="0">无</option>${typeOptions}</select></div>
      <div><label>是否多规格</label><select name="is_sku"><option value="n" ${gv('is_sku', 'n') === 'n' ? 'selected' : ''}>否</option><option value="y" ${gv('is_sku', 'y') === 'y' ? 'selected' : ''}>是</option></select></div>
    </div>
    <div class="row">
      <div><label>计量单位</label><input type="text" name="unit_name" value="${esc(gv('unit_name', '个'))}"></div>
      <div><label>初始库存</label><input type="number" name="stock" value="${gv('stock', 0)}" min="0"></div>
      <div><label>初始销量</label><input type="number" name="sales" value="${gv('sales', 0)}" min="0"></div>
      <div><label>上架状态</label><select name="is_on_shelf"><option value="1" ${gv('is_on_shelf', 1) == 1 ? 'selected' : ''}>上架</option><option value="0" ${gv('is_on_shelf', 0) == 0 ? 'selected' : ''}>下架</option></select></div>
    </div>
    <label>商品简介</label><textarea name="des">${esc(gv('des', ''))}</textarea>
    <label>商品详情 (HTML)</label><textarea name="content" style="min-height:140px;">${esc(gv('content', ''))}</textarea>
    <label>购买后说明 (HTML)</label><textarea name="pay_content">${esc(gv('pay_content', ''))}</textarea>
    <label>封面图 URL</label><input type="text" name="cover" value="${esc(gv('cover', ''))}">
    <label>规格/SKU 与价格 (每行: SKU组合|售价(分)|市场价(分)|库存, SKU组合如 1-5 或 0)</label>
    <textarea name="skus_text" style="min-height:120px;">${esc(skusText)}</textarea>
    <button type="submit" class="btn green" style="margin-top:10px;">保 存</button>
    <span class="msg" id="fmsg"></span>
  </form>
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
      .map((g) => `<tr><td>${g.id}</td><td>${esc(g.title)}</td><td>${TNAME[g.type]}</td><td><a class="btn" href="/admin/kami?goods_id=${g.id}">管理卡密</a></td></tr>`)
      .join('');
    return adminPage(request, env, '卡密管理', `<div class="card"><table><thead><tr><th>ID</th><th>商品</th><th>模式</th><th>操作</th></tr></thead><tbody>${rows || '<tr><td colspan="4" style="text-align:center;color:#999;">暂无卡密类商品</td></tr>'}</tbody></table></div>`);
  }

  let rows = '';
  if (goods.type === 'once') {
    const list = (await env.DB.prepare('SELECT * FROM dc_goods_once WHERE goods_id = ? ORDER BY id DESC LIMIT 200').bind(gid).all()).results;
    rows = list
      .map(
        (k) => `<tr><td>${k.id}</td><td>${k.sku}</td><td style="word-break:break-all;">${esc(k.content)}</td><td>${k.sale_time ? ts2str(k.sale_time) : '<span class="muted">未售出</span>'}</td><td><button class="btn red" onclick="delK(${k.id})">删除</button></td></tr>`
      )
      .join('');
  } else if (goods.type === 'general') {
    const list = (await env.DB.prepare('SELECT * FROM dc_goods_general WHERE goods_id = ? ORDER BY id DESC').bind(gid).all()).results;
    rows = list
      .map(
        (k) => `<tr><td>${k.id}</td><td>${k.sku}</td><td style="word-break:break-all;">${esc(k.content)}</td><td>${ts2str(k.create_time)}</td><td><button class="btn red" onclick="delG(${k.id})">删除</button></td></tr>`
      )
      .join('');
  }

  const content = `<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
    <h2 style="margin:0;">${esc(goods.title)} <span class="muted">(${TNAME[goods.type]})</span></h2>
    <a class="btn ghost" href="/admin/goods">返回商品</a>
  </div>
  ${goods.type === 'once' ? `<div class="row">
    <div><label>新增一卡一密 (每行一条卡密)</label><textarea id="newKami" placeholder="KAMI-0001&#10;KAMI-0002"></textarea><button class="btn green" onclick="addKami()">添加</button></div>
    <div><label>批量导入 (每行一条卡密)</label><textarea id="importKami" placeholder="一行一条"></textarea><button class="btn" onclick="impKami()">导入</button></div>
  </div>` : `<div class="row">
    <div><label>新增通用卡密 (SKU|内容)</label><textarea id="newKami" placeholder="0|VIP-2026-0001"></textarea><button class="btn green" onclick="addKami()">添加</button></div>
  </div>`}
  <table style="margin-top:14px;"><thead><tr><th>ID</th><th>SKU</th><th>内容</th>${goods.type === 'once' ? '<th>售出时间</th>' : '<th>添加时间</th>'}<th>操作</th></tr></thead><tbody>${rows || '<tr><td colspan="5" style="text-align:center;color:#999;">暂无卡密</td></tr>'}</tbody></table>
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
    <td><a class="btn gray" href="/?action=order_result&out_trade_no=${esc(o.out_trade_no)}" target="_blank">详情</a>
    ${o.pay_status == 1 ? `<button class="btn red" onclick="refund(${o.id},${JSON.stringify(o.out_trade_no).replace(/"/g, '&quot;')})">退款</button>` : ''}
    <button class="btn ghost" onclick="delO(${o.id})">删除</button></td>
  </tr>`
    )
    .join('');
  const content = `<div class="card">
  <form action="/admin/orders" method="get" style="display:flex;gap:8px;margin-bottom:12px;"><input type="text" name="q" placeholder="订单号/联系方式" value="${esc(kw)}" style="width:260px;margin:0;"><button class="btn ghost">搜索</button></form>
  <table><thead><tr><th>ID</th><th>订单号</th><th>金额</th><th>时间</th><th>支付</th><th>状态</th><th>操作</th></tr></thead>
  <tbody>${rows || '<tr><td colspan="7" style="text-align:center;color:#999;">暂无订单</td></tr>'}</tbody></table>
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
    .map((s) => `<tr><td>${s.sid}</td><td>${esc(s.sortname)}</td><td>${esc(s.alias)}</td><td>${s.taxis}</td><td>${esc(s.sorticon || '-')}</td><td><button class="btn red" onclick="delS(${s.sid})">删除</button></td></tr>`)
    .join('');
  const content = `<div class="card">
  <div class="row" style="margin-bottom:14px;">
    <div><label>分类名称</label><input type="text" id="sName"></div>
    <div><label>别名 (alias)</label><input type="text" id="sAlias"></div>
    <div><label>排序 (小=前)</label><input type="number" id="sTaxis" value="0"></div>
    <div><label>图标 (remixicon 类名)</label><input type="text" id="sIcon" placeholder="ri-gamepad-line"></div>
    <div><button class="btn green" onclick="addS()" style="margin-top:24px;">新增分类</button></div>
  </div>
  <table><thead><tr><th>ID</th><th>名称</th><th>别名</th><th>排序</th><th>图标</th><th>操作</th></tr></thead>
  <tbody>${rows || '<tr><td colspan="6" style="text-align:center;color:#999;">暂无分类</td></tr>'}</tbody></table>
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
  const content = `<div class="card" style="max-width:780px;">
  <form id="frm">
    <div class="row">
      <div><label>站点名称</label><input type="text" name="blogname" value="${esc(g('blogname', 'DCSHOP多财商城'))}"></div>
      <div><label>副标题</label><input type="text" name="site_subtitle" value="${esc(g('site_subtitle', ''))}"></div>
    </div>
    <label>页脚信息 (支持 HTML)</label><textarea name="footer_info" style="min-height:60px;">${esc(g('footer_info', ''))}</textarea>
    <label>滚动公告 (多行: 每行一条)</label><textarea name="roll_bulletin">${esc(g('roll_bulletin', ''))}</textarea>
    <label>首页公告 (HTML)</label><textarea name="home_bulletin" style="min-height:90px;">${esc(g('home_bulletin', ''))}</textarea>
    <div class="row">
      <div><label>查单必填设置 (JSON)</label><input type="text" name="order_required" value="${esc(g('order_required', ''))}"></div>
      <div><label>主题主色</label><input type="color" name="theme_primary" value="${esc(primary)}" style="padding:2px;height:38px;"></div>
    </div>
    <button type="submit" class="btn green" style="margin-top:10px;">保存设置</button>
    <span class="msg" id="fmsg"></span>
  </form>
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