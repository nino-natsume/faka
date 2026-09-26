// ============================================================
// p2h 冒烟测试 — 后台 提现管理 (admin cash)
// 依赖: test/p2h-seed.sql 已灌入本地 D1
// 用法: node test/p2h-smoke.mjs [baseUrl]
//   默认 http://127.0.0.1:8787
// 退出码 0 = 全通过, 1 = 有失败
// ============================================================

const BASE = (process.argv[2] || 'http://127.0.0.1:8787').replace(/\/$/, '');
const ADMIN_USER = 'admin';
const ADMIN_PASS = 'admin123';

// ---------- 迷你 cookie jar ----------
const jar = new Map();
function cookieHeader() {
  return [...jar.entries()].map(([k, v]) => `${k}=${v}`).join('; ');
}
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
    if (v === '' || /Max-Age=0/i.test(line)) jar.delete(k);
    else jar.set(k, v);
  }
}

// ---------- 请求 ----------
async function raw(path, opts = {}) {
  const headers = Object.assign({ Cookie: cookieHeader() }, opts.headers || {});
  const res = await fetch(BASE + path, { ...opts, headers, redirect: 'manual' });
  absorb(res);
  return res;
}
async function getJson(path) {
  const res = await raw(path);
  let json = null;
  try { json = JSON.parse(await res.text()); } catch (e) { /* 非 JSON */ }
  return { status: res.status, loc: res.headers.get('location') || '', json };
}
async function post(path, body) {
  const res = await raw(path, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(body || {}),
  });
  let json = null;
  try { json = JSON.parse(await res.text()); } catch (e) { /* 非 JSON */ }
  return { status: res.status, json };
}
const API = '/admin/api/cash/';
const postCash = (act, body) => post(API + act, body);
const getCash = (qs) => getJson(API + 'data' + (qs ? '?' + qs : ''));

// ---------- 断言 ----------
let pass = 0;
const fails = [];
function ok(cond, name, extra) {
  if (cond) { pass++; console.log('  \u2714 ' + name); }
  else { fails.push(name + (extra ? ' -> ' + extra : '')); console.log('  \u2718 ' + name + (extra ? '  [' + extra + ']' : '')); }
}
function section(t) { console.log('\n== ' + t + ' =='); }

// ---------- 工具 ----------
const code200 = (r) => r && r.json && r.json.code === 200;
const errMsg = (r) => (r && r.json && r.json.msg) || '(no msg)';
async function userRow(id) {
  const r = await getJson('/admin/api/user/data?limit=100&equal-id=' + id);
  return r.json && r.json.data && r.json.data.list && r.json.data.list[0];
}
async function cashRow(id) {
  const r = await postCash('data', { limit: 100, 'equal-id': id });
  const d = r.json && r.json.data;
  return d && d.list && d.list[0];
}

(async () => {
  console.log('p2h 冒烟测试 · 提现管理 · ' + BASE);

  // ---------- 1. 鉴权 ----------
  section('1 鉴权');
  {
    const r = await raw('/admin/cash/index');
    ok(r.status === 302 && (r.headers.get('location') || '').includes('/admin/authentication/login'),
      '未登录访问页面 302 跳登录', 'status=' + r.status);
    const a = await getCash('limit=5');
    ok(a.status === 200 && a.json && a.json.code === 0, '未登录访问 API 返回拒绝', errMsg(a));
  }

  // ---------- 2. 登录 ----------
  section('2 登录');
  {
    const cap = await raw('/user/captcha/image?action=adminLogin');
    ok(cap.status === 200, '验证码图片 200', 'status=' + cap.status);
    const cv = jar.get('acg_captcha_adminLogin') || '';
    const code = cv ? Buffer.from(decodeURIComponent(cv), 'base64').toString('utf8').split(':')[0] : '';
    ok(/^\d{4}$/.test(code), '验证码 cookie 可解码出 4 位数字', 'got=' + code);

    const bad = await post('/admin/api/authentication/login', { username: ADMIN_USER, password: 'wrong-pass', captcha: code });
    ok(!code200(bad), '错误密码登录失败', errMsg(bad));

    await raw('/user/captcha/image?action=adminLogin');
    const cv2 = jar.get('acg_captcha_adminLogin') || '';
    const code2 = cv2 ? Buffer.from(decodeURIComponent(cv2), 'base64').toString('utf8').split(':')[0] : '';
    const good = await post('/admin/api/authentication/login', { username: ADMIN_USER, password: ADMIN_PASS, captcha: code2 });
    ok(code200(good), '正确账号密码登录成功', errMsg(good));
    ok(!!jar.get('MANAGE_USER'), '下发后台会话 Cookie', [...jar.keys()].join(','));
  }

  // ---------- 3. 页面 ----------
  section('3 提现管理页面');
  {
    const r = await raw('/admin/cash/index');
    const html = await r.text();
    ok(r.status === 200, '页面 200', 'status=' + r.status);
    ok(html.includes('提现管理'), '标题含「提现管理」');
    ok(html.includes('cash-table'), '含列表表格 #cash-table');
    ok(html.includes('row-pass') && html.includes('row-reject'), '含通过/驳回按钮绑定');
    ok(html.includes('cash-settle'), '含一键自动结算按钮');
    ok(html.includes('/admin/cash/index'), '菜单高亮指向当前页');
    const menu = await raw('/admin/dashboard/index');
    const mh = await menu.text();
    ok(mh.includes('提现管理') && mh.includes('/admin/cash/index'), '后台菜单含「提现管理」入口');
  }

  // ---------- 4. 列表 ----------
  section('4 列表与统计');
  {
    const r = await postCash('data', { page: 1, limit: 10 });
    ok(code200(r), '列表接口成功', errMsg(r));
    const d = r.json.data || {};
    ok(d.total >= 4, '总数 >= 4 条种子数据', 'total=' + d.total);
    ok(d.page === 1 && d.limit === 10, '分页回显', JSON.stringify({ page: d.page, limit: d.limit }));
    ok(d.amount > 0 && d.cost > 0, '合计金额/费用返回', 'amount=' + d.amount + ' cost=' + d.cost);
    const first = (d.list || [])[0];
    ok(!!first && first.user && first.user.username, '列表内联用户信息(username)', JSON.stringify(first && first.user));
    ok(d.list.every((x) => typeof x.id === 'number' && 'status' in x), '每行含 id/status 字段');
  }

  // ---------- 5. 筛选 ----------
  section('5 筛选');
  {
    const s0 = await postCash('data', { 'equal-status': '0', limit: 100 });
    const l0 = (s0.json.data || {}).list || [];
    ok(code200(s0) && l0.length === 2, '状态=待处理 筛出 2 条', 'n=' + l0.length);
    ok(l0.every((x) => Number(x.status) === 0), '待处理结果 status 全为 0');

    const s1 = await postCash('data', { 'equal-status': '2', limit: 100 });
    ok(((s1.json.data || {}).list || []).length === 1, '状态=已驳回 筛出 1 条');

    const t1 = await postCash('data', { 'equal-type': '1', limit: 100 });
    const lt = (t1.json.data || {}).list || [];
    ok(lt.length === 1 && Number(lt[0].type) === 1, '类型=佣金提现 筛出 1 条', 'n=' + lt.length);

    const kw = await postCash('data', { keyword: 'p2h_u2', limit: 100 });
    const lk = (kw.json.data || {}).list || [];
    ok(lk.length === 1 && lk[0].user.username === 'p2h_u2', '关键词搜用户名命中 1 条', 'n=' + lk.length);

    const kwa = await postCash('data', { keyword: 'p2h_alipay', limit: 100 });
    const la = (kwa.json.data || {}).list || [];
    ok(la.length === 3 && la.every((x) => x.user && x.user.username === 'p2h_u1'),
      '关键词搜支付宝账号命中 3 条(均属 u1)', 'n=' + la.length);

    const kwm = await postCash('data', { keyword: '历史驳回记录', limit: 100 });
    ok(((kwm.json.data || {}).list || []).length === 1, '关键词搜驳回理由命中 1 条');

    const none = await postCash('data', { keyword: '不存在的关键字zzz', limit: 100 });
    const dn = none.json.data || {};
    ok(dn.total === 0 && (dn.list || []).length === 0, '无匹配返回空列表', 'total=' + dn.total);

    const tr = await postCash('data', { 'betweenStart-create_time': '2023/01/01', 'betweenEnd-create_time': '2024/01/01', limit: 100 });
    ok(((tr.json.data || {}).list || []).length === 4, '时间区间 2023~2024 命中 4 条');
    const tr2 = await postCash('data', { 'betweenStart-create_time': '2030/01/01', limit: 100 });
    ok(((tr2.json.data || {}).list || []).length === 0, '时间区间 2030 起命中 0 条');
  }

  // ---------- 6. 分页 ----------
  section('6 分页');
  {
    const p1 = await postCash('data', { page: 1, limit: 2 });
    const p2 = await postCash('data', { page: 2, limit: 2 });
    const l1 = (p1.json.data || {}).list || [];
    const l2 = (p2.json.data || {}).list || [];
    ok(l1.length === 2 && l2.length === 2, '每页 2 条各满页', `${l1.length}/${l2.length}`);
    ok(l1.length && l2.length && l1[0].id !== l2[0].id, '两页数据不重复');
    const big = await postCash('data', { page: 1, limit: 9999 });
    ok(Number((big.json.data || {}).limit) === 100, 'limit 上限收敛到 100', 'limit=' + ((big.json.data || {}).limit));
    const zero = await postCash('data', { page: 0, limit: 0 });
    const dz = zero.json.data || {};
    ok(dz.page === 1 && dz.limit === 10, '非法分页回落到默认 1/10', JSON.stringify({ page: dz.page, limit: dz.limit }));
  }

  // ---------- 7. 审批参数校验 ----------
  section('7 审批参数校验');
  {
    const a = await postCash('decide', { id: 0, status: 0 });
    ok(!code200(a) && errMsg(a).includes('请求参数'), 'id=0 拒绝', errMsg(a));
    const b = await postCash('decide', { id: 91001, status: 9 });
    ok(!code200(b), 'status=9 非法枚举拒绝', errMsg(b));
    const c = await postCash('decide', { id: 91001, status: 1, message: '' });
    ok(!code200(c) && errMsg(c).includes('驳回理由'), '驳回缺理由拒绝', errMsg(c));
    const d = await postCash('decide', { id: 91001, status: 1, message: 'x'.repeat(65) });
    ok(!code200(d) && errMsg(d).includes('64'), '驳回理由超 64 字拒绝', errMsg(d));
    const e = await postCash('decide', { id: 999999, status: 0 });
    ok(!code200(e) && errMsg(e).includes('不存在'), '不存在的记录拒绝', errMsg(e));
    const still = await cashRow(91001);
    ok(Number(still.status) === 0, '校验失败未污染数据(status 仍 0)', 'status=' + (still && still.status));
  }

  // ---------- 8. 审批通过 ----------
  section('8 审批通过 (91001)');
  {
    const r = await postCash('decide', { id: 91001, status: 0 });
    ok(code200(r), '通过成功', errMsg(r));
    const row = await cashRow(91001);
    ok(Number(row.status) === 1, '状态变为已通过(1)', 'status=' + row.status);
    ok(!!row.arrive_time && Number(row.arrive_time) > 0, '写入处理时间 arrive_time', String(row.arrive_time));
    const u1 = await userRow(90001);
    ok(Math.abs(Number(u1.balance) - 10.00) < 0.001, '通过不退款余额不变(10.00)', 'balance=' + u1.balance);
    const again = await postCash('decide', { id: 91001, status: 0 });
    ok(!code200(again) && errMsg(again).includes('无法操作'), '已处理记录不可重复操作', errMsg(again));
  }

  // ---------- 9. 审批驳回 + 退款 ----------
  section('9 审批驳回退款 (91002)');
  {
    const before = await userRow(90002);
    const b0 = Number(before.balance);
    ok(Math.abs(b0) < 0.001, '驳回前 u2 余额为 0', 'balance=' + b0);
    const r = await postCash('decide', { id: 91002, status: 1, message: '冒烟测试驳回' });
    ok(code200(r), '驳回成功', errMsg(r));
    const row = await cashRow(91002);
    ok(Number(row.status) === 2, '状态变为已驳回(2)', 'status=' + row.status);
    ok(row.message === '冒烟测试驳回', '驳回理由落库', String(row.message));
    const after = await userRow(90002);
    ok(Math.abs(Number(after.balance) - 305.00) < 0.001, '退款 = 余额 + 金额300 + 费用5 = 305', 'balance=' + after.balance);
    const again = await postCash('decide', { id: 91002, status: 0 });
    ok(!code200(again) && errMsg(again).includes('无法操作'), '已驳回记录不可再操作', errMsg(again));
  }

  // ---------- 10. 一键自动结算 ----------
  section('10 一键自动结算');
  {
    const bad0 = await postCash('settlement', { amount: 0 });
    ok(!code200(bad0) && errMsg(bad0).includes('大于 0'), 'amount=0 拒绝', errMsg(bad0));
    const badn = await postCash('settlement', { amount: -5 });
    ok(!code200(badn), 'amount=-5 拒绝', errMsg(badn));
    const bads = await postCash('settlement', { amount: 'abc' });
    ok(!code200(bads), 'amount=非数字拒绝', errMsg(bads));

    const before = await cashRow(91004);
    const totalBefore = (await postCash('data', { limit: 1 })).json.data.total;

    const r = await postCash('settlement', { amount: 50 });
    ok(code200(r), '结算执行成功', errMsg(r));
    const cnt = r.json.data && Number(r.json.data.count);
    ok(cnt >= 1, '生成结算单 >= 1', 'count=' + cnt);

    const u3 = await userRow(90003);
    ok(Number(u3.coin) === 0, 'u3 coin 被清零', 'coin=' + u3.coin);
    const list = (await postCash('data', { 'equal-user_id': 90003, limit: 10 })).json.data || {};
    const mine = list.list || [];
    ok(mine.length >= 1 && Number(mine[0].amount) === 88.88, 'u3 生成 88.88 提现单且待处理',
      'n=' + mine.length + ' amt=' + (mine[0] && mine[0].amount) + ' st=' + (mine[0] && mine[0].status));
    const totalAfter = (await postCash('data', { limit: 1 })).json.data.total;
    ok(totalAfter === totalBefore + cnt, '总数增加 = 生成单数', `${totalBefore} -> ${totalAfter}`);

    const r2 = await postCash('settlement', { amount: 50 });
    ok(code200(r2) && Number(r2.json.data.count) === 0, '无余额用户时结算 0 单(幂等)', 'count=' + (r2.json.data && r2.json.data.count));
  }

  // ---------- 11. 汇总 ----------
  console.log('\n' + '='.repeat(46));
  console.log(`通过 ${pass} / ${pass + fails.length}`);
  if (fails.length) {
    console.log('失败明细:');
    fails.forEach((f, i) => console.log(`  ${i + 1}. ${f}`));
    process.exit(1);
  }
  console.log('\u5168\u90e8\u901a\u8fc7 \u2665');
})().catch((e) => {
  console.error('\n\u6d4b\u8bd5\u811a\u672c\u5f02\u5e38: ' + (e && e.stack ? e.stack : e));
  process.exit(1);
});
