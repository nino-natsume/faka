// ============================================================
// p2i 冒烟测试 — 账单管理 (/admin/api/bill/data) + 操作日志 (/admin/api/log/data)
// 依赖: npx wrangler d1 execute faka --local --file=test/p2i-seed.sql
// 用法: node test/p2i-smoke.mjs [baseUrl]   (默认 http://127.0.0.1:8787)
// 退出码 0 = 全通过, 1 = 有失败
// 覆盖: 原版 Bind\Query 筛选协议(equal/search/betweenStart|betweenEnd)、sort_field/sort_rule、
//       page/limit、limit 白名单(15/30/50)、datetime 输出格式、owner 关联、列白名单与注入防护
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
async function getJson(path) {
  const r = await req(path);
  try { return { status: r.status, json: JSON.parse(await r.text()) }; } catch (e) { return { status: r.status, json: null }; }
}
const q = (o) => Object.entries(o)
  .filter(([, v]) => v !== undefined && v !== null && v !== '')
  .map(([k, v]) => `${encodeURIComponent(k)}=${encodeURIComponent(String(v))}`).join('&');

let pass = 0; const fails = [];
const ok = (c, n, x) => { if (c) { pass++; console.log('  \u2714 ' + n); } else { fails.push(n + (x ? ' -> ' + x : '')); console.log('  \u2718 ' + n + (x ? '  [' + x + ']' : '')); } };
const sec = (t) => console.log('\n== ' + t + ' ==');
const isOk = (r) => r.json && r.json.code === 200;
const data = (r) => (isOk(r) ? r.json.data : null);
const ids = (r) => (data(r) ? data(r).list.map((x) => x.id) : null);

// 种子 epoch(固定值, 与时区无关): 1700000000 ~ 1700010000
// 注意: 断言里的时间字符串必须按**运行时本地时区**渲染 —— 前端 laydate 传的就是这种串,
// parseTimeValue 也按本地时区构造, 两者对称, 这样测试在任何时区下都成立
const fmtLocal = (ts) => {
  const d = new Date(ts * 1000);
  const p = (x) => String(x).padStart(2, '0');
  return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())} ${p(d.getHours())}:${p(d.getMinutes())}:${p(d.getSeconds())}`;
};
const T1 = fmtLocal(1700002000);   // 区间下界(含) -> 命中 92002/92003/92004/92005
const T2 = fmtLocal(1700008000);   // 区间上界(含)
const DT_RE = /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/;
const hasAll = (r, want) => {
  const got = ids(r) || [];
  return want.every((x) => got.includes(x));
};
const hasNone = (r, nope) => {
  const got = ids(r) || [];
  return !nope.some((x) => got.includes(x));
};
// 精确 ID 集合 —— 不能用 id 范围过滤: 种子插入后 max(id) 抬高, 系统自动生成的账单/日志
// (登录、充值等业务动作) 会占用紧邻 ID 落进范围, 导致用例随环境漂移
const SEED_BILL = [92001, 92002, 92003, 92004, 92005, 92006];
const SEED_LOG = [93001, 93002, 93003, 93004, 93005];
const onlySeed = (r, seed) => (data(r) ? data(r).list : []).filter((x) => seed.includes(x.id));

(async () => {
  console.log('p2i 冒烟 · 账单 + 操作日志 · ' + BASE);

  sec('0 登录');
  {
    await req('/user/captcha/image?action=adminLogin');
    const cv = jar.get('acg_captcha_adminLogin') || '';
    const code = cv ? Buffer.from(decodeURIComponent(cv), 'base64').toString('utf8').split(':')[0] : '';
    const r = await req('/admin/api/authentication/login', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username: 'admin', password: 'admin123', captcha: code }),
    });
    let j = null; try { j = JSON.parse(await r.text()); } catch (e) { /* noop */ }
    ok(j && j.code === 200, '后台登录成功', j && j.msg);
  }

  sec('1 账单管理 /admin/api/bill/data');
  {
    // 注: 本地库可能残留 seed.sql 的历史账单, 断言一律用「种子 ID 的包含关系」而非绝对 total
    const r = await getJson('/admin/api/bill/data?limit=100');
    if (!hasAll(r, SEED_BILL)) {
      console.error('\n缺少种子数据, 请先执行:');
      console.error('  npx wrangler d1 execute faka --local --file=test/p2i-seed.sql');
      process.exit(1);
    }
    ok(isOk(r), '基本列表 code=200', 'code=' + (r.json && r.json.code) + ' msg=' + (r.json && r.json.msg));
    ok(data(r) && data(r).total >= 6, 'total>=6(含种子 6 条)', 'total=' + (data(r) && data(r).total));
    ok(hasAll(r, SEED_BILL), '种子 6 条全部返回', JSON.stringify(ids(r)));
    const l = onlySeed(r, SEED_BILL);
    ok(l.length === 6 && l.every((x) => DT_RE.test(x.create_time)),
      'create_time 为 datetime 字符串', JSON.stringify(l.map((x) => x.create_time)));
    const one = l.find((x) => x.id === 92001) || {};
    ok(one.owner && one.owner.username === 'p2i_u1' && 'avatar' in one.owner,
      'owner 关联 User(id,username,avatar)', JSON.stringify(one.owner));
    ok('type' in one && 'currency' in one && 'log' in one && 'balance' in one,
      '返回原版字段 type/currency/log/balance');

    const byType = await getJson('/admin/api/bill/data?limit=100&' + q({ 'equal-type': 1 }));
    ok(hasAll(byType, [92001, 92004, 92006]) && hasNone(byType, [92002, 92003, 92005]),
      'equal-type=1 命中 92001/92004/92006', JSON.stringify(ids(byType)));

    const byOwner = await getJson('/admin/api/bill/data?limit=100&' + q({ 'equal-owner': 90011 }));
    ok(hasAll(byOwner, [92001, 92002, 92003, 92006]) && hasNone(byOwner, [92004, 92005]),
      'equal-owner=90011 命中 4 条', JSON.stringify(ids(byOwner)));

    const byLog = await getJson('/admin/api/bill/data?limit=100&' + q({ 'search-log': 'p2i' }));
    ok(hasAll(byLog, [92001, 92002, 92003, 92004, 92006]) && hasNone(byLog, [92005]),
      'search-log=p2i 命中 5 条(排除 92005)', JSON.stringify(ids(byLog)));

    const byCur = await getJson('/admin/api/bill/data?limit=100&' + q({ 'equal-currency': 1 }));
    ok(hasAll(byCur, [92003]), 'equal-currency=1 命中 92003', JSON.stringify(ids(byCur)));

    const byTime = await getJson('/admin/api/bill/data?limit=100&' + q({ 'betweenStart-create_time': T1, 'betweenEnd-create_time': T2 }));
    ok(hasAll(byTime, [92002, 92003, 92004, 92005]) && hasNone(byTime, [92001, 92006]),
      '时间区间命中 92002-92005', JSON.stringify(ids(byTime)));
    const byStart = await getJson('/admin/api/bill/data?limit=100&' + q({ 'betweenStart-create_time': fmtLocal(1700010000) }));
    ok(hasAll(byStart, [92006]) && hasNone(byStart, [92001, 92005]),
      '仅 betweenStart 也能收窄', JSON.stringify(ids(byStart)));

    const asc = await getJson('/admin/api/bill/data?limit=100&sort_field=amount&sort_rule=asc');
    const ascA = (data(asc) ? data(asc).list : []).map((x) => Number(x.amount));
    ok(isOk(asc) && ascA.length > 1 && ascA.every((v, i) => i === 0 || ascA[i - 1] <= v),
      'sort_field=amount&sort_rule=asc 非降序', JSON.stringify(ascA.slice(0, 6)));
    const desc = await getJson('/admin/api/bill/data?limit=100&sort_field=amount&sort_rule=desc');
    const descA = (data(desc) ? data(desc).list : []).map((x) => Number(x.amount));
    ok(isOk(desc) && descA.length > 1 && descA.every((v, i) => i === 0 || descA[i - 1] >= v),
      'sort_rule=desc 非升序', JSON.stringify(descA.slice(0, 6)));

    const badSort = await getJson('/admin/api/bill/data?limit=100&' + q({ sort_field: 'amount; DROP TABLE acg_bill--', sort_rule: 'sideways' }));
    ok(isOk(badSort), '非法 sort_field/sort_rule 回落而非报错', 'code=' + (badSort.json && badSort.json.code));
    ok(data(badSort) && data(badSort).list[0] && data(badSort).list[0].id === 92006,
      '回落为 id desc(首条为最新种子)', 'first=' + (data(badSort) && data(badSort).list[0] && data(badSort).list[0].id));

    const p2 = await getJson('/admin/api/bill/data?limit=2&page=2');
    ok(data(p2) && data(p2).list.length === 2 && data(p2).page === 2 && data(p2).limit === 2,
      'page=2&limit=2 分页', JSON.stringify(data(p2) && { p: data(p2).page, l: data(p2).limit, n: data(p2).list.length }));

    const inject = await getJson('/admin/api/bill/data?limit=100&' + q({ 'equal-type': '1 OR 1=1' }));
    ok(isOk(inject), '注入型筛选值不 500', 'code=' + (inject.json && inject.json.code));
    ok(hasNone(inject, [92002]), '注入型值按字面比较(未命中 type!=1 的行)', JSON.stringify(ids(inject)));
    const base = await getJson('/admin/api/bill/data?limit=100');
    const unknownCol = await getJson('/admin/api/bill/data?limit=100&' + q({ 'equal-nosuchcol': 'y', 'search-log': '' }));
    ok(isOk(unknownCol) && data(unknownCol).total === data(base).total,
      '非白名单列被忽略(不报错不生效)', 'total=' + (data(unknownCol) && data(unknownCol).total));
    const stillThere = await getJson('/admin/api/bill/data?limit=100&equal-type=1');
    ok(isOk(stillThere) && data(stillThere).total > 0, '注入尝试后表仍完好');
  }

  sec('2 操作日志 /admin/api/log/data');
  {
    const r = await getJson('/admin/api/log/data?limit=100');
    ok(isOk(r), '基本列表 code=200', 'code=' + (r.json && r.json.code) + ' msg=' + (r.json && r.json.msg));
    ok(data(r) && data(r).total >= 5, 'total>=5(含种子 5 条)', 'total=' + (data(r) && data(r).total));
    ok(hasAll(r, SEED_LOG), '种子 5 条全部返回', JSON.stringify(ids(r)));
    const l = onlySeed(r, SEED_LOG);
    ok(l.length === 5 && l.every((x) => DT_RE.test(x.create_time)),
      'create_time 为 datetime 字符串', JSON.stringify(l.map((x) => x.create_time)));
    ok(l.every((x) => typeof x.risk === 'number'), 'risk 为数字');
    ok(l.length > 0 && 'ua' in l[0] && 'create_ip' in l[0] && 'nickname' in l[0],
      '返回原版字段 ua/create_ip/nickname');

    const byEmail = await getJson('/admin/api/log/data?limit=100&' + q({ 'equal-email': 'p2i_admin1@test.com' }));
    ok(hasAll(byEmail, [93001, 93002]) && hasNone(byEmail, [93003, 93004, 93005]),
      'equal-email 命中 93001/93002', JSON.stringify(ids(byEmail)));

    const byRisk = await getJson('/admin/api/log/data?limit=100&' + q({ 'equal-risk': 1 }));
    ok(hasAll(byRisk, [93002, 93004]) && hasNone(byRisk, [93001, 93003, 93005]),
      'equal-risk=1 命中 93002/93004', JSON.stringify(ids(byRisk)));

    const byContent = await getJson('/admin/api/log/data?limit=100&' + q({ 'search-content': 'p2i' }));
    ok(hasAll(byContent, [93001, 93002, 93003, 93004]) && hasNone(byContent, [93005]),
      'search-content=p2i 命中 4 条(排除 93005)', JSON.stringify(ids(byContent)));

    const byIp = await getJson('/admin/api/log/data?limit=100&' + q({ 'equal-create_ip': '10.0.0.3' }));
    ok(hasAll(byIp, [93003]) && hasNone(byIp, [93001, 93002]),
      'equal-create_ip 命中 93003', JSON.stringify(ids(byIp)));

    const byNick = await getJson('/admin/api/log/data?limit=100&' + q({ 'search-nickname': '管理员一' }));
    ok(hasAll(byNick, [93001, 93002]), 'search-nickname 命中 93001/93002', JSON.stringify(ids(byNick)));

    const byTime = await getJson('/admin/api/log/data?limit=100&' + q({ 'betweenStart-create_time': T1, 'betweenEnd-create_time': T2 }));
    ok(hasAll(byTime, [93002, 93003, 93004, 93005]) && hasNone(byTime, [93001]),
      '时间区间命中 93002-93005', JSON.stringify(ids(byTime)));

    const combo = await getJson('/admin/api/log/data?limit=100&' + q({ 'equal-risk': 1, 'search-content': 'p2i' }));
    ok(hasAll(combo, [93002, 93004]) && hasNone(combo, [93001, 93003, 93005]),
      '组合筛选 risk=1 + content LIKE p2i', JSON.stringify(ids(combo)));

    // 原版 Api\Log::data 只允许 limit 15/30/50
    const badLimit = await getJson('/admin/api/log/data?limit=7');
    ok(data(badLimit) && data(badLimit).limit === 15, 'limit=7 回落 15(原版白名单)', 'limit=' + (data(badLimit) && data(badLimit).limit));
    const okLimit = await getJson('/admin/api/log/data?limit=30');
    ok(data(okLimit) && data(okLimit).limit === 30, 'limit=30 生效', 'limit=' + (data(okLimit) && data(okLimit).limit));
    const over = await getJson('/admin/api/log/data?limit=999');
    ok(data(over) && data(over).limit === 15, 'limit=999 回落 15', 'limit=' + (data(over) && data(over).limit));
    const pg = await getJson('/admin/api/log/data?limit=15&page=2');
    ok(data(pg) && data(pg).page === 2 && data(pg).list.length === 15,
      'page=2 每页 15 条', JSON.stringify(data(pg) && { p: data(pg).page, n: data(pg).list.length }));
  }

  sec('3 页面渲染');
  {
    for (const [path, mark, tableId] of [
      ['/admin/user/bill', '账单管理', 'bill-table'],
      ['/admin/log/index', '操作日志', 'manage-log-table'],
    ]) {
      const r = await req(path);
      const t = await r.text();
      ok(r.status === 200 && t.toLowerCase().includes('<html'), path + ' 200', 'status=' + r.status);
      ok(t.includes(mark), path + ' 含「' + mark + '」');
      ok(t.includes(tableId), path + ' 含原版表格容器 #' + tableId);
      ok(t.includes('/assets/admin/controller/'), path + ' 加载原版 controller JS');
    }
  }

  sec('4 菜单对齐 (原版 Header.html)');
  {
    const r = await req('/admin/dashboard/index');
    const t = await r.text();
    const groups = ['Main', 'User', 'Trade', 'Shared', 'Config'];
    let last = -1, ordered = true;
    for (const g of groups) {
      const i = t.indexOf('>' + g + '<');
      if (i < 0) { ordered = false; break; }
      if (i < last) { ordered = false; break; }
      last = i;
    }
    ok(ordered, '分组顺序 Main→User→Trade→Shared→Config');
    for (const n of ['控制台', '会员管理', '工单管理', '消息管理', '充值订单', '账单管理', '会员等级',
      '商户等级', '提现管理', '分类管理', '商品管理', '卡密管理', '优惠券', '商品订单',
      '店铺共享', '加价模板', '网站设置', '管理员', '通用插件', '支付管理', '支付插件',
      '支付接口', '文件管理', '语言翻译', '操作日志']) {
      ok(t.includes('>' + n + '<') || t.includes('>' + n + '</span>'), '菜单含「' + n + '」');
    }
    ok(t.includes('menu-accordion') && t.includes('支付插件'), '支付管理为折叠子菜单');
    ok(!t.includes('>系统配置<'), '原「系统配置」已按原版改为「网站设置」');
  }

  sec('5 未登录守卫');
  {
    const saved = new Map(jar);
    jar.clear();
    for (const p of ['/admin/user/bill', '/admin/log/index']) {
      const r = await req(p);
      ok(r.status === 302 && (r.headers.get('location') || '').includes('login'), p + ' 未登录 302', 'status=' + r.status);
    }
    for (const p of ['/admin/api/bill/data', '/admin/api/log/data']) {
      const r = await getJson(p);
      ok(!isOk(r) || r.status === 401 || r.status === 302, p + ' 未登录被拒', 'status=' + r.status);
    }
    for (const [k, v] of saved) jar.set(k, v);
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
