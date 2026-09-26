// ============================================================
// 全量回归 — 后台全部页面 + 只读 API + 前台公开页
// 用途: 每次改动 admin-pages.js / worker.js 后跑一遍, 确认无回归
// 用法: node test/regress.mjs [baseUrl]
//   默认 http://127.0.0.1:8787
// 退出码 0 = 全通过, 1 = 有失败
// ============================================================

const BASE = (process.argv[2] || 'http://127.0.0.1:8787').replace(/\/$/, '');

const jar = new Map();
const cookieHeader = () => [...jar.entries()].map(([k, v]) => `${k}=${v}`).join('; ');
function absorb(res) {
  const raw = typeof res.headers.getSetCookie === 'function'
    ? res.headers.getSetCookie()
    : (res.headers.get('set-cookie') ? [res.headers.get('set-cookie')] : []);
  for (const line of raw) {
    const [pair] = String(line).split(';');
    const i = pair.indexOf('=');
    if (i < 0) continue;
    const k = pair.slice(0, i).trim();
    const v = pair.slice(i + 1).trim();
    if (v === '' || /Max-Age=0/i.test(line)) jar.delete(k); else jar.set(k, v);
  }
}
async function req(path, opts = {}) {
  const res = await fetch(BASE + path, {
    ...opts,
    headers: Object.assign({ Cookie: cookieHeader() }, opts.headers || {}),
    redirect: 'manual',
  });
  absorb(res);
  return res;
}
async function getText(path) {
  const r = await req(path);
  return { status: r.status, text: await r.text(), loc: r.headers.get('location') || '' };
}
async function getJson(path) {
  const r = await req(path);
  try { return { status: r.status, json: JSON.parse(await r.text()) }; } catch (e) { return { status: r.status, json: null }; }
}
async function postJson(path, body) {
  const r = await req(path, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body || {}) });
  try { return { status: r.status, json: JSON.parse(await r.text()) }; } catch (e) { return { status: r.status, json: null }; }
}

let pass = 0; const fails = [];
const ok = (c, n, x) => { if (c) { pass++; console.log('  \u2714 ' + n); } else { fails.push(n + (x ? ' -> ' + x : '')); console.log('  \u2718 ' + n + (x ? '  [' + x + ']' : '')); } };
const sec = (t) => console.log('\n== ' + t + ' ==');
const isOk = (r) => r.json && r.json.code === 200;

// 后台页面: [路径, 必须出现的标记]
const ADMIN_PAGES = [
  ['/admin/dashboard/index', '控制台'],
  ['/admin/category/index', '分类'],
  ['/admin/commodity/index', '商品'],
  ['/admin/card/index', '卡密'],
  ['/admin/order/index', '订单'],
  ['/admin/user/index', '会员'],
  ['/admin/recharge/order', '充值'],
  ['/admin/coupon/index', '优惠券'],
  ['/admin/ticket/index', '工单'],
  ['/admin/message/index', '消息'],
  ['/admin/cash/index', '提现'],
  ['/admin/user/bill', '账单'],
  ['/admin/log/index', '日志'],
  ['/admin/pay/plugin', '支付插件'],
  ['/admin/pay/index', '支付接口'],
];
// 后台只读 API
const ADMIN_APIS = [
  '/admin/api/dashboard/overview',
  '/admin/api/dashboard/trend?days=7',
  '/admin/api/dashboard/data?type=0',
  '/admin/api/category/data?limit=5',
  '/admin/api/commodity/data?limit=5',
  '/admin/api/card/data?limit=5',
  '/admin/api/order/data?limit=5',
  '/admin/api/user/data?limit=5',
  '/admin/api/user/statistics?id=1',
  '/admin/api/recharge/data?limit=5',
  '/admin/api/coupon/data?limit=5',
  '/admin/api/ticket/data?limit=5',
  '/admin/api/ticket/badge',
  '/admin/api/message/data?limit=5',
  '/admin/api/message/groups',
  '/admin/api/message/users?keyword=a',
  '/admin/api/cash/data?limit=5',
  '/admin/api/bill/data?limit=5',
  '/admin/api/log/data?limit=15',
  '/admin/api/pay/data?limit=5',
  '/admin/api/pay/getPlugins',
  '/admin/api/pay/getPluginConfigs?handle=Epay',
  '/admin/api/pay/getPluginLog?handle=Epay',
  '/admin/api/app/ad',
];
// 前台公开页: [路径, 标记, 允许 302 到登录]
const USER_PAGES = [
  ['/user/index/index', '<html'],
  ['/user/index/query', '<html'],
  ['/user/authentication/login', '<html'],
  ['/user/authentication/register', '<html'],
];
// 前台需登录页 (未登录应 302)
const USER_GUARD = [
  '/user/dashboard/index', '/user/bill/index', '/user/recharge/index',
  '/user/security/personal', '/user/personal/purchaseRecord',
];

(async () => {
  console.log('全量回归 · ' + BASE);

  sec('0 登录');
  {
    await req('/user/captcha/image?action=adminLogin');
    const cv = jar.get('acg_captcha_adminLogin') || '';
    const code = cv ? Buffer.from(decodeURIComponent(cv), 'base64').toString('utf8').split(':')[0] : '';
    const r = await postJson('/admin/api/authentication/login', { username: 'admin', password: 'admin123', captcha: code });
    ok(isOk(r), '后台登录成功', r.json && r.json.msg);
  }

  sec('1 后台页面');
  for (const [path, mark] of ADMIN_PAGES) {
    const r = await getText(path);
    ok(r.status === 200 && r.text.toLowerCase().includes('<html'), path + ' 200', 'status=' + r.status);
    ok(r.status === 200 && r.text.includes(mark), path + ' 含「' + mark + '」');
  }

  sec('2 后台只读 API');
  for (const path of ADMIN_APIS) {
    const r = await getJson(path);
    ok(isOk(r), path, 'code=' + (r.json && r.json.code) + ' msg=' + (r.json && r.json.msg));
  }

  sec('3 后台未知路由兜底');
  {
    const r = await getText('/admin/does/not/exist');
    ok(r.status === 200 && r.text.includes('建设中'), '未知后台路由渲染「建设中」', 'status=' + r.status);
  }

  sec('4 前台公开页');
  for (const [path, mark] of USER_PAGES) {
    const r = await getText(path);
    ok(r.status === 200 && r.text.includes(mark), path + ' 200', 'status=' + r.status);
  }

  sec('5 前台登录守卫');
  for (const path of USER_GUARD) {
    const r = await getText(path);
    ok(r.status === 302 && r.loc.includes('/user/authentication/login'), path + ' 未登录 302', 'status=' + r.status);
  }

  sec('6 静态资源');
  {
    for (const p of ['/favicon.ico', '/vendor/layui/css/layui.css', '/js/qrcode.min.js', '/css/em.css']) {
      const r = await getText(p);
      ok(r.status === 200, p + ' 200', 'status=' + r.status);
    }
  }

  sec('7 错误处理');
  {
    const bad = await postJson('/admin/api/cash/decide', { id: 999999, status: 0 });
    ok(!isOk(bad) && bad.status === 200, '不存在的提现单返回业务错误而非 5xx', 'status=' + bad.status);
    const nostat = await getJson('/admin/api/user/statistics');
    ok(!isOk(nostat) && String(nostat.json && nostat.json.msg).includes('用户不存在'),
      'user/statistics 缺 id 被拒', 'msg=' + (nostat.json && nostat.json.msg));
    const nf = await getText('/no/such/page/at/all');
    ok(nf.status === 404 || nf.status === 200, '未知前台路径不 500', 'status=' + nf.status);
  }

  console.log('\n' + '='.repeat(46));
  console.log(`通过 ${pass} / ${pass + fails.length}`);
  if (fails.length) {
    console.log('失败明细:');
    fails.forEach((f, i) => console.log(`  ${i + 1}. ${f}`));
    process.exit(1);
  }
  console.log('\u5168\u90e8\u901a\u8fc7 \u2665');
})().catch((e) => { console.error('\n\u5f02\u5e38: ' + (e && e.stack ? e.stack : e)); process.exit(1); });
