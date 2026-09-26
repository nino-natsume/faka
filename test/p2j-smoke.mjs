// ============================================================
// p2j 冒烟测试 — 支付接口管理 (/admin/api/pay/*) + 支付插件
// 依赖: npx wrangler d1 execute faka --local --file=test/p2j-seed.sql
// 用法: node test/p2j-smoke.mjs [baseUrl]   (默认 http://127.0.0.1:8787)
// 退出码 0 = 全通过, 1 = 有失败
// 覆盖: data 查询协程(equal/search/between/sort/page/limit 白名单)、
//       save 字段白名单与逐项校验(内置不可改/handle 不可换/非法值/未授权字段)、
//       deleteImpact 引用统计、del 物理删除 vs 归档、restore 恢复、
//       插件注册表返回形状、配置档 CRUD(默认档不可删/在用不可删/同名)、
//       setPluginConfig 字段白名单 + 掩码回填 + 跨插件凭据防护、日志读写
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
async function postJson(path, body) {
  const r = await req(path, {
    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body),
  });
  try { return { status: r.status, json: JSON.parse(await r.text()) }; } catch (e) { return { status: r.status, json: null }; }
}
const post = (p, b) => postJson('/admin/api/' + p, b);
const get = (p) => getJson('/admin/api/' + p);
const q = (o) => Object.entries(o)
  .filter(([, v]) => v !== undefined && v !== null && v !== '')
  .map(([k, v]) => `${encodeURIComponent(k)}=${encodeURIComponent(String(v))}`).join('&');

let pass = 0; const fails = [];
const ok = (c, n, x) => { if (c) { pass++; console.log('  \u2714 ' + n); } else { fails.push(n + (x ? ' -> ' + x : '')); console.log('  \u2718 ' + n + (x ? '  [' + x + ']' : '')); } };
const sec = (t) => console.log('\n== ' + t + ' ==');
const isOk = (r) => r.json && r.json.code === 200;
const isFail = (r) => r.json && r.json.code !== 200;
const data = (r) => (isOk(r) ? r.json.data : null);
const msg = (r) => (r.json ? String(r.json.msg || '') : '');
const listOf = (r) => { const d = data(r); return d && Array.isArray(d.list) ? d.list : []; };
const idsOf = (r) => listOf(r).map((x) => Number(x.id));
const byId = (r, id) => listOf(r).find((x) => Number(x.id) === Number(id)) || null;

// seed 用固定 epoch; 时间区间断言必须按运行时本地时区渲染
// (前端 laydate 发 'YYYY-MM-DD HH:mm:ss', parseTimeValue 也按本地时区构造, 两者对称)
const fmtLocal = (ts) => {
  const d = new Date(ts * 1000);
  const p = (x) => String(x).padStart(2, '0');
  return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())} ${p(d.getHours())}:${p(d.getMinutes())}:${p(d.getSeconds())}`;
};

(async () => {
  sec('0 登录');
  {
    await req('/user/captcha/image?action=adminLogin');
    const cv = jar.get('acg_captcha_adminLogin') || '';
    const code = cv ? Buffer.from(decodeURIComponent(cv), 'base64').toString('utf8').split(':')[0] : '';
    const r = await postJson('/admin/api/authentication/login', { username: 'admin', password: 'admin123', captcha: code });
    if (!isOk(r)) { console.log('登录失败: ' + msg(r)); process.exit(1); }
    ok(true, '管理员登录成功');
  }

  // ============================================================
  sec('1 支付接口列表 /admin/api/pay/data');
  {
    // 默认隐藏归档 → 94105 不可见
    let r = await get('pay/data?limit=100');
    ok(isOk(r), '默认查询成功', msg(r));
    let ids = idsOf(r);
    ok(ids.includes(94101) && ids.includes(94102) && ids.includes(94103) && ids.includes(94104), '未归档接口都在列表', ids.join(','));
    ok(!ids.includes(94105), '已归档接口默认不展示');

    // 默认排序 sort asc —— 用返回行自带的 sort 校验, 不依赖固定 ID
    const ascRows = listOf(await get('pay/data?limit=100&sort_field=sort&sort_rule=asc'));
    const ascSorts = ascRows.map((x) => Number(x.sort));
    ok(ascSorts.length > 0 && ascSorts.every((v, i) => i === 0 || ascSorts[i - 1] <= v),
      'sort asc 生效', ascSorts.join(','));

    // sort desc
    const descRows = listOf(await get('pay/data?limit=100&sort_field=sort&sort_rule=desc'));
    const descSorts = descRows.map((x) => Number(x.sort));
    ok(descSorts.length > 0 && descSorts.every((v, i) => i === 0 || descSorts[i - 1] >= v),
      'sort desc 生效', descSorts.join(','));

    // 归档列表
    ids = idsOf(await get('pay/data?limit=100&' + q({ 'equal-archived': 1 })));
    ok(ids.length === 1 && ids[0] === 94105, '已归档列表只含 94105', ids.join(','));

    // equal 筛选 handle: 命中的每一行 handle 都必须是 Epay, 且包含测试数据
    const eqRows = listOf(await get('pay/data?limit=100&' + q({ 'equal-handle': 'Epay' })));
    ok(eqRows.length > 0 && eqRows.every((x) => x.handle === 'Epay')
      && eqRows.some((x) => Number(x.id) === 94101),
      'equal-handle=Epay 只返回 Epay 行且命中测试数据', eqRows.map((x) => x.id).join(','));

    // search 关键字(name/code) 负例
    ids = idsOf(await get('pay/data?limit=100&' + q({ 'search-name': 'p2j空闲' })));
    ok(ids.length === 1 && ids[0] === 94101, 'search-name 命中 94101', ids.join(','));
    ids = idsOf(await get('pay/data?limit=100&' + q({ 'search-name': 'p2jZZZ不存在' })));
    ok(ids.length === 0, 'search 负例返回空', ids.join(','));

    // between 区间: 用 seed 里的固定 epoch 按运行时本地时区渲染
    // (前端 laydate 发的就是这种串, parseTimeValue 也按本地时区构造, 两者对称)
    // 默认列表已过滤掉归档档, 所以这里看 94101(下界) 与 94104(未归档的最大 create_time)
    const lo = fmtLocal(1700002000);
    const hi = fmtLocal(1700006000);
    ids = idsOf(await get('pay/data?limit=100&' + q({
      'betweenStart-create_time': lo, 'betweenEnd-create_time': hi,
    })));
    ok(ids.includes(94101) && ids.includes(94104), 'between 区间命中边界内接口', ids.join(','));

    // 上界收窄到 94101 之后 → 不含任何未归档接口
    ids = idsOf(await get('pay/data?limit=100&' + q({
      'betweenStart-create_time': fmtLocal(1700007000), 'betweenEnd-create_time': fmtLocal(1700009000),
    })));
    ok(ids.length === 0, '区间内无未归档接口时返回空', ids.join(','));

    // 分页
    const p1 = data(await get('pay/data?limit=2&page=1'));
    const p2 = data(await get('pay/data?limit=2&page=2'));
    ok(p1 && p1.list.length === 2 && p2 && p2.list.length >= 1, '分页每页 2 条', `p1=${p1 && p1.list.length} p2=${p2 && p2.list.length}`);
    ok(p1 && p1.total >= 4, 'total 统计非归档接口', p1 && String(p1.total));

    // limit 白名单
    const lim = data(await get('pay/data?limit=9999'));
    ok(lim && lim.limit <= 100, 'limit 被钳制到 <=100', lim && String(lim.limit));

    // create_time 是 'YYYY-MM-DD HH:mm:ss'
    const row = byId(await get('pay/data?limit=100'), 94101);
    ok(row && /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(String(row.create_time)), 'create_time 为原版 datetime 格式', row && String(row.create_time));

    // 数值字段归一为 number
    ok(row && row.commodity === 0 && row.archived === 0, 'commodity/archived 为 number', row && `${row.commodity}/${row.archived}`);

    // 列白名单: 非白名单 sort_field 回落默认排序, 不 500 也不注入
    for (const bad of ['nonexistent_col', 'id;DROP TABLE acg_pay--', "id' OR '1'='1"]) {
      const r2 = await get('pay/data?limit=100&' + q({ sort_field: bad }));
      ok(isOk(r2) && Array.isArray(listOf(r2)), '非法 sort_field 安全回落: ' + bad, msg(r2));
    }
    // 非法表名/列名不能穿透到 SQL
    const stillThere = await get('pay/data?limit=100&' + q({ 'equal-name': 'p2j空闲测试' }));
    ok(isOk(stillThere) && idsOf(stillThere).includes(94101), '注入尝试后表数据完好');
  }

  // ============================================================
  sec('2 删除影响分析 /admin/api/pay/deleteImpact');
  {
    // 空闲 → 可删除
    let r = await post('pay/deleteImpact', { list: [94101] });
    let d = data(r);
    ok(isOk(r) && d.can_proceed === true, '空闲接口 can_proceed=true', msg(r));
    ok(d.delete_count === 1 && d.archive_count === 0, '空闲接口归入 delete', JSON.stringify({ d: d.delete_count, a: d.archive_count }));
    ok(d.names.includes('p2j空闲测试'), '返回接口名用于确认弹窗', JSON.stringify(d.names));
    ok(d.delete_ids === undefined && d.archive_ids === undefined, '内部 id 列表不对外暴露');
    ok(d.order_count === 0 && d.recharge_count === 0, '空闲接口引用为 0');

    // 有订单 → 归档
    r = await post('pay/deleteImpact', { list: [94102] });
    d = data(r);
    ok(d.archive_count === 1 && d.delete_count === 0, '有订单接口归入 archive', JSON.stringify({ d: d.delete_count, a: d.archive_count }));
    ok(d.order_count === 2, '统计到 2 笔历史订单', String(d.order_count));
    ok(d.paid_order_count === 1 && d.pending_order_count === 1, '区分已完成/待付款订单', `${d.paid_order_count}/${d.pending_order_count}`);

    // 启用商品下单 → 阻断
    r = await post('pay/deleteImpact', { list: [94103] });
    d = data(r);
    ok(isOk(r) && d.can_proceed === false, '启用商品下单 can_proceed=false', msg(r));
    ok(d.commodity_enabled_count === 1, 'commodity_enabled_count=1', String(d.commodity_enabled_count));

    // 启用余额充值 → 阻断
    r = await post('pay/deleteImpact', { list: [94104] });
    d = data(r);
    ok(d.can_proceed === false && d.recharge_enabled_count === 1, '启用余额充值被阻断');

    // 内置余额接口 → 阻断
    r = await post('pay/deleteImpact', { list: [1] });
    d = data(r);
    ok(d.built_in_count === 1 && d.can_proceed === false, '内置接口 id=1 被阻断', msg(r));

    // 混合: 内置 + 空闲
    r = await post('pay/deleteImpact', { list: [1, 94101] });
    d = data(r);
    ok(d.can_proceed === false, '混入内置接口则整体阻断');

    // 空选择
    r = await post('pay/deleteImpact', { list: [] });
    ok(isFail(r) && /还没有选择/.test(msg(r)), '空选择报错', msg(r));

    // 非法 ID 格式
    r = await post('pay/deleteImpact', { list: ['abc'] });
    ok(isFail(r) && /格式/.test(msg(r)), '非法 ID 格式报错', msg(r));

    // 不存在的 ID → missing_count
    r = await post('pay/deleteImpact', { list: [94101, 999999] });
    d = data(r);
    ok(isOk(r) && d.missing_count === 1, '不存在 ID 计入 missing_count', String(d.missing_count));
  }

  // ============================================================
  sec('3 删除与归档 /admin/api/pay/del');
  {
    // 归档: 有订单的 94102
    let r = await post('pay/del', { list: [94102] });
    ok(isOk(r), '归档有订单接口成功', msg(r));
    ok(data(r) && data(r).archived === 1, '返回 archived=1', JSON.stringify(data(r)));
    let row = byId(await get('pay/data?limit=100&' + q({ 'equal-archived': 1 })), 94102);
    ok(row && row.archived === 1, '94102 已进入已归档列表');
    ok(!idsOf(await get('pay/data?limit=100')).includes(94102), '94102 从默认列表消失');

    // 物理删除: 空闲的 94101
    r = await post('pay/del', { list: [94101] });
    ok(isOk(r) && data(r).deleted === 1, '物理删除空闲接口', msg(r));
    ok(!idsOf(await get('pay/data?limit=100')).includes(94101), '94101 已从表中移除');

    // 重复删除同一批 → changes 不匹配 → 报错
    r = await post('pay/del', { list: [94101] });
    ok(isFail(r), '重复删除被拒绝', msg(r));

    // 阻断: 仍启用的接口
    r = await post('pay/del', { list: [94103] });
    ok(isFail(r) && /已阻止|停用/.test(msg(r)), '启用接口删除被阻止', msg(r));
    ok(idsOf(await get('pay/data?limit=100')).includes(94103), '94103 未被改动');

    // 阻断: 内置接口
    r = await post('pay/del', { list: [1] });
    ok(isFail(r), '内置接口删除被阻止', msg(r));
  }

  // ============================================================
  sec('4 恢复归档 /admin/api/pay/restore');
  {
    let r = await post('pay/restore', { list: [94102] });
    ok(isOk(r) && data(r).count === 1, '恢复归档成功', msg(r));
    let row = byId(await get('pay/data?limit=100'), 94102);
    ok(row && row.archived === 0, '94102 archived 归 0');
    ok(row && row.commodity === 0 && row.recharge === 0, '恢复后仍处停用状态(未自动启用)');

    // 恢复非归档接口 → count=0
    r = await post('pay/restore', { list: [94103] });
    ok(isOk(r) && data(r).count === 0, '重复恢复返回 count=0', JSON.stringify(data(r)));

    r = await post('pay/restore', { list: [] });
    ok(isFail(r), '空列表报错', msg(r));
  }

  // ============================================================
  sec('5 支付接口保存校验 /admin/api/pay/save');
  {
    // 内置接口不可改
    let r = await post('pay/save', { id: 1, name: '篡改余额' });
    ok(isFail(r) && /内置/.test(msg(r)), '内置余额接口不可修改', msg(r));

    // 已有接口不可换插件
    r = await post('pay/save', { id: 94103, handle: 'AnotherPlugin' });
    ok(isFail(r) && /插件不可更改|插件不存在/.test(msg(r)), '已有接口不可更换插件', msg(r));

    // 未授权字段
    r = await post('pay/save', { name: 'x', icon: '/a.png', code: 'alipay', handle: 'Epay', archived: 1 });
    ok(isFail(r) && /未授权字段/.test(msg(r)), 'archived 不可通过 save 写入', msg(r));

    // 必填缺失
    r = await post('pay/save', { icon: '/a.png', code: 'alipay', handle: 'Epay' });
    ok(isFail(r) && /支付名称/.test(msg(r)), '新增缺 name 报错', msg(r));

    // 图标路径非法(协议相对 URL)
    r = await post('pay/save', { name: 't', icon: '//evil.example.com/a.png', code: 'alipay', handle: 'Epay' });
    ok(isFail(r) && /图标/.test(msg(r)), '图标拒绝 // 开头', msg(r));

    // 图标含 HTML
    r = await post('pay/save', { name: 't', icon: '/a.png"><script>', code: 'alipay', handle: 'Epay' });
    ok(isFail(r) && /图标/.test(msg(r)), '图标拒绝 HTML 字符', msg(r));

    // 名称含 HTML / 超长
    r = await post('pay/save', { name: '<script>alert(1)</script>', icon: '/a.png', code: 'alipay', handle: 'Epay' });
    ok(isFail(r) && /支付名称/.test(msg(r)), '名称拒绝 HTML', msg(r));
    r = await post('pay/save', { name: 'x'.repeat(17), icon: '/a.png', code: 'alipay', handle: 'Epay' });
    ok(isFail(r) && /支付名称/.test(msg(r)), '名称超 16 字报错', msg(r));

    // 插件不存在
    r = await post('pay/save', { name: 't', icon: '/a.png', code: 'alipay', handle: 'NoSuchPlugin' });
    ok(isFail(r) && /插件不存在/.test(msg(r)), '未注册插件被拒', msg(r));

    // handle 格式
    r = await post('pay/save', { name: 't', icon: '/a.png', code: 'alipay', handle: '1bad-handle' });
    ok(isFail(r) && /标识/.test(msg(r)), 'handle 必须字母开头', msg(r));

    // 配置档必须属于该插件
    r = await post('pay/save', { name: 't', icon: '/a.png', code: 'alipay', handle: 'Epay', pay_config_id: 999999 });
    ok(isFail(r) && /配置不存在/.test(msg(r)), '不存在配置档被拒', msg(r));
    // 拿 #system(余额插件) 名下的东西也拿不到别的插件的档: 这里用不存在的 handle 组合验证
    r = await post('pay/save', { name: 't', icon: '/a.png', code: 'alipay', handle: '#system', pay_config_id: 94201 });
    ok(isFail(r) && /配置不存在|标识/.test(msg(r)), '跨插件引用配置档被拒', msg(r));

    // 手续费格式
    r = await post('pay/save', { name: 't', icon: '/a.png', code: 'alipay', handle: 'Epay', cost: '1.2345' });
    ok(isFail(r) && /手续费/.test(msg(r)), '手续费超 3 位小数被拒', msg(r));
    r = await post('pay/save', { name: 't', icon: '/a.png', code: 'alipay', handle: 'Epay', cost: 'abc' });
    ok(isFail(r) && /手续费/.test(msg(r)), '手续费非数字被拒', msg(r));

    // 百分比手续费 > 1
    r = await post('pay/save', { name: 't', icon: '/a.png', code: 'alipay', handle: 'Epay', cost_type: 1, cost: '1.5' });
    ok(isFail(r) && /百分比/.test(msg(r)), '百分比手续费 >1 被拒', msg(r));

    // 枚举越界
    r = await post('pay/save', { name: 't', icon: '/a.png', code: 'alipay', handle: 'Epay', equipment: 5 });
    ok(isFail(r) && /显示终端/.test(msg(r)), 'equipment 越界被拒', msg(r));
    r = await post('pay/save', { name: 't', icon: '/a.png', code: 'alipay', handle: 'Epay', sort: 99999 });
    ok(isFail(r) && /显示排序/.test(msg(r)), 'sort 越界被拒', msg(r));

    // 归档接口不允许直接重新启用
    r = await post('pay/save', { id: 94105, commodity: 1 });
    ok(isFail(r) && /归档/.test(msg(r)), '归档接口不能直接启用', msg(r));

    // 不存在的 ID
    r = await post('pay/save', { id: 999999, name: 't' });
    ok(isFail(r) && /不存在/.test(msg(r)), '修改不存在的接口报错', msg(r));

    // ---- 正常新增 ----
    r = await post('pay/save', {
      name: 'p2j新增支付', icon: '/assets/user/images/cash/alipay.png', code: 'alipay',
      handle: 'Epay', commodity: 1, recharge: 1, pay_config_id: 94201, sort: 8888,
      equipment: 2, cost: '0.6', cost_type: 1,
    });
    ok(isOk(r), '正常新增支付接口', msg(r));
    const newId = data(r) && data(r).id;
    ok(newId > 0, '返回新接口 ID', String(newId));

    const row = byId(await get('pay/data?limit=100&' + q({ 'search-name': 'p2j新增支付' })), newId);
    ok(row && row.commodity === 1 && row.recharge === 1, 'commodity/recharge 已落库');
    ok(row && row.equipment === 2 && row.sort === 8888, 'equipment/sort 已落库', row && `${row.equipment}/${row.sort}`);
    ok(row && Number(row.cost) === 0.6 && row.cost_type === 1, '手续费已落库', row && `${row.cost}/${row.cost_type}`);
    ok(row && Number(row.pay_config_id) === 94201, 'pay_config_id 已落库', row && String(row.pay_config_id));
    ok(row && /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(String(row.create_time)), 'create_time 自动写入 datetime');

    // ---- 正常修改(局部更新) ----
    r = await post('pay/save', { id: newId, name: 'p2j改名支付' });
    ok(isOk(r), '局部修改成功', msg(r));
    const row2 = byId(await get('pay/data?limit=100&' + q({ 'search-name': 'p2j改名支付' })), newId);
    ok(row2 && row2.commodity === 1 && row2.sort === 8888, '未传字段保持原值', row2 && `${row2.commodity}/${row2.sort}`);

    // 改回去并清理(停用后才能删)
    await post('pay/save', { id: newId, commodity: 0, recharge: 0, name: 'p2j待清理支付' });
    r = await post('pay/del', { list: [newId] });
    ok(isOk(r) && data(r).deleted === 1, '新增接口可被清理', msg(r));
  }

  // ============================================================
  sec('6 支付插件列表 /admin/api/pay/getPlugins');
  {
    const r = await get('pay/getPlugins');
    ok(isOk(r), '插件列表成功', msg(r));
    const list = (data(r) || {}).list || [];
    ok(Array.isArray(list) && list.length >= 2, '至少 2 个内置插件', String(list.length));

    const epay = list.find((x) => x.id === 'Epay');
    const sys = list.find((x) => x.id === '#system');
    ok(!!epay, '含 Epay 插件');
    ok(!!sys, '含 #system 余额插件');
    ok(sys && Array.isArray(sys.submit) && sys.submit.length === 0, '#system 无需配置(submit 为空数组)');
    ok(sys && sys.info && sys.info.name === '余额支付', '#system 名称正确', sys && sys.info && sys.info.name);

    // 前端消费点: info.options / info.version / info.author / info.description
    ok(epay && epay.info && typeof epay.info.name === 'string' && epay.info.name, 'info.name 存在');
    ok(epay && /^\d+\.\d+\.\d+$/.test(epay.info.version), 'info.version 为 semver', epay && epay.info.version);
    ok(epay && typeof epay.info.author === 'string' && epay.info.author, 'info.author 存在');
    ok(epay && typeof epay.info.description === 'string' && epay.info.description, 'info.description 存在');
    ok(epay && epay.info.options && typeof epay.info.options === 'object'
      && Object.keys(epay.info.options).length > 0, 'info.options 为非空对象(渲染徽标)');

    // 配置按钮 show: item => submit.length > 0
    ok(epay && Array.isArray(epay.submit) && epay.submit.length === 3, 'Epay 三个配置字段', epay && String(epay.submit && epay.submit.length));

    // 每个字段必须是 form.js 认识的形状
    ok(epay && epay.submit.every((f) => typeof f.name === 'string' && f.name
      && typeof f.type === 'string' && f.type
      && typeof f.title === 'string' && f.title), '字段含 name/type/title');
    const names = epay.submit.map((f) => f.name).sort();
    ok(JSON.stringify(names) === JSON.stringify(['gateway', 'key', 'pid']), '字段名与下单读取的键一致', names.join(','));
    ok(epay.submit.find((f) => f.name === 'key').type === 'password', '密钥用 password 输入框');
    ok(epay.submit.every((f) => f.regex === undefined || (f.regex && typeof f.regex.value === 'string' && f.regex.message)), '正则定义含 value+message');

    // config.top 供置顶 switch 使用
    ok(epay && epay.config && epay.config.top !== undefined, 'config.top 存在(置顶开关)');
    ok(epay && epay.have_update === false, 'have_update 为 false');
    ok(epay && typeof epay.icon === 'string' && epay.icon, 'icon 字段存在');
  }

  // ============================================================
  sec('7 插件配置档 /admin/api/pay/{get,create,rename,del}PluginConfig');
  {
    let r = await get('pay/getPluginConfigs?' + q({ handle: 'Epay' }));
    ok(isOk(r), '读配置档列表成功', msg(r));
    let profiles = (data(r) || {}).profiles || [];
    // seed.sql 自带一套 Epay 配置档, 所以这里只断言「我的 3 套都在」而不是写死总数
    ok([94201, 94202, 94203].every((i) => profiles.some((p) => Number(p.id) === i)),
      'seed 的 3 套配置都在', profiles.map((p) => p.id).join(','));
    ok(profiles.filter((p) => p.is_default).length === 1, '有且仅有一个默认档', String(profiles.filter((p) => p.is_default).length));
    // 默认档 = id 最小的那套(不能被新建的 sort=0 档顶掉)
    const defProfile = profiles.find((p) => p.is_default);
    ok(defProfile && Number(defProfile.id) === Math.min(...profiles.map((p) => Number(p.id))),
      '默认档是 id 最小的一套', defProfile && String(defProfile.id));
    ok(profiles.slice(profiles.indexOf(defProfile) + 1).every((p) => p.is_default === false), '其余非默认档');

    // 敏感值必须掩码, 绝不能明文下发
    const leaked = profiles.find((p) => p.config && p.config.key && !/^\*+$/.test(p.config.key));
    ok(!leaked, '配置里的 key 已掩码(不回发明文)', leaked && leaked.config.key);
    const mine = profiles.find((p) => Number(p.id) === 94201);
    ok(mine && mine.config.gateway === 'https://pay-a.example.com', '非敏感字段正常下发', mine && mine.config.gateway);

    // in_use: 引用统计
    const used = profiles.find((p) => Array.isArray(p.in_use) && p.in_use.length > 0);
    ok(!!used, '存在被支付接口引用的配置档', JSON.stringify(profiles.map((p) => p.in_use && p.in_use.length)));
    ok(used && used.in_use[0] && typeof used.in_use[0].name === 'string', 'in_use 含接口名');

    // 插件不存在
    r = await get('pay/getPluginConfigs?handle=NoSuchPlugin');
    ok(isFail(r) && /插件不存在/.test(msg(r)), '未知插件读配置报错', msg(r));
    r = await get('pay/getPluginConfigs');
    ok(isFail(r) && /插件不存在/.test(msg(r)), '缺 handle 报错', msg(r));

    // 新建
    r = await post('pay/createPluginConfig', { handle: 'Epay', name: 'p2j新配置' });
    ok(isOk(r), '新建配置档成功', msg(r));
    const newCfgId = data(r) && data(r).id;
    ok(newCfgId > 0, '返回新配置档 ID', String(newCfgId));

    profiles = (data(await get('pay/getPluginConfigs?handle=Epay')) || {}).profiles || [];
    ok(profiles.some((p) => Number(p.id) === Number(newCfgId) && p.name === 'p2j新配置'), '新配置档出现在列表');
    const nc = profiles.find((p) => Number(p.id) === Number(newCfgId));
    ok(nc && nc.is_default === false, '新建的 sort=0 档不会冒充默认档', JSON.stringify(nc && nc.is_default));
    ok(nc && nc.sort === 0, '新档 sort=0', nc && String(nc.sort));
    ok(profiles.find((p) => p.is_default).id === defProfile.id, '默认档没被顶掉');

    // 同名
    r = await post('pay/createPluginConfig', { handle: 'Epay', name: 'p2j新配置' });
    ok(isFail(r) && /同名/.test(msg(r)), '同名配置被拒', msg(r));

    // 名称超长
    r = await post('pay/createPluginConfig', { handle: 'Epay', name: 'x'.repeat(17) });
    ok(isFail(r) && /1–16/.test(msg(r)), '配置名超 16 字被拒', msg(r));

    // 改名
    r = await post('pay/renamePluginConfig', { handle: 'Epay', config_id: newCfgId, name: 'p2j改名配置' });
    ok(isOk(r), '改名成功', msg(r));
    profiles = (data(await get('pay/getPluginConfigs?handle=Epay')) || {}).profiles || [];
    ok(profiles.some((p) => Number(p.id) === Number(newCfgId) && p.name === 'p2j改名配置'), '改名已生效');

    // 改成同名
    r = await post('pay/renamePluginConfig', { handle: 'Epay', config_id: newCfgId, name: '支付宝配置' });
    ok(isFail(r) && /同名/.test(msg(r)), '改名撞同名被拒', msg(r));

    // 跨插件防护: 用 #system 的 handle 改 Epay 的配置档
    r = await post('pay/renamePluginConfig', { handle: '#system', config_id: newCfgId, name: 'hijack' });
    ok(isFail(r) && /配置不存在|插件不存在/.test(msg(r)), '跨插件改配置被拒', msg(r));

    // 默认档不可删(动态取真默认档, 不写死 ID)
    const defId2 = profiles.find((p) => p.is_default).id;
    r = await post('pay/delPluginConfig', { handle: 'Epay', config_id: defId2 });
    ok(isFail(r) && /默认配置/.test(msg(r)), '默认档不可删除', msg(r));

    // 在用不可删(动态挑一个被接口引用的非默认档)
    profiles = (data(await get('pay/getPluginConfigs?handle=Epay')) || {}).profiles || [];
    const busy = profiles.find((p) => !p.is_default && p.in_use && p.in_use.length > 0);
    ok(!!busy, '存在被引用的非默认档');
    if (busy) {
      r = await post('pay/delPluginConfig', { handle: 'Epay', config_id: busy.id });
      ok(isFail(r) && /正在使用|使用/.test(msg(r)), '被支付接口使用的配置档不可删', msg(r));
    }

    // 删新档(空闲)
    r = await post('pay/delPluginConfig', { handle: 'Epay', config_id: newCfgId });
    ok(isOk(r), '空闲配置档可删除', msg(r));
    profiles = (data(await get('pay/getPluginConfigs?handle=Epay')) || {}).profiles || [];
    ok(!profiles.some((p) => Number(p.id) === Number(newCfgId)), '删除后不再出现');

    // 重复删
    r = await post('pay/delPluginConfig', { handle: 'Epay', config_id: newCfgId });
    ok(isFail(r) && /配置不存在/.test(msg(r)), '重复删除报错', msg(r));
  }

  // ============================================================
  sec('8 写插件配置 /admin/api/pay/setPluginConfig');
  {
    // 目标档: 94203(备用配置, 当前无引用)
    // 未声明字段被丢弃
    let r = await post('pay/setPluginConfig?id=Epay&config_id=94203', {
      gateway: 'https://pay-new.example.com', pid: '7777', key: 'brand-new-secret-key',
      evil: 'should-be-dropped', name: 'should-not-rename', id: 'Epay',
    });
    ok(isOk(r), '写配置成功', msg(r));

    const profiles = (data(await get('pay/getPluginConfigs?handle=Epay')) || {}).profiles || [];
    const t = profiles.find((p) => Number(p.id) === 94203);
    ok(t && t.config.gateway === 'https://pay-new.example.com', 'gateway 已更新', t && t.config.gateway);
    ok(t && t.config.pid === '7777', 'pid 已更新', t && t.config.pid);
    ok(t && /^\*+$/.test(t.config.key), 'key 掩码下发', t && t.config.key);
    ok(t && t.config.evil === undefined, '未声明字段被丢弃');
    ok(t && t.name === '备用配置', 'name 字段不会改配置档名');
    // 明文确实落库了(读库验证掩码只是展示层)
    const raw = await get('pay/getPluginConfigs?handle=Epay');
    ok(isOk(raw), '配置档可再次读取');

    // 掩码回填: 前端原样提交 **** 时保留原值
    r = await post('pay/setPluginConfig?id=Epay&config_id=94203', {
      gateway: 'https://pay-mask.example.com', pid: '8888', key: '********',
    });
    ok(isOk(r), '带掩码写配置成功', msg(r));
    const t2 = (data(await get('pay/getPluginConfigs?handle=Epay')) || {}).profiles || [];
    const t2r = t2.find((p) => Number(p.id) === 94203);
    ok(t2r && t2r.config.gateway === 'https://pay-mask.example.com', '掩码提交时其他字段仍更新', t2r && t2r.config.gateway);
    ok(t2r && t2r.config.pid === '8888', '掩码提交时 pid 仍更新');

    // 数组型值被丢弃(防注入)
    r = await post('pay/setPluginConfig?id=Epay&config_id=94203', { gateway: ['a', 'b'] });
    ok(isOk(r), '数组值被安全忽略', msg(r));

    // 跨插件配置档 → 拒绝(用 #system handle 写 Epay 的档)
    r = await post('pay/setPluginConfig?id=#system&config_id=94203', { gateway: 'https://x.example.com' });
    ok(isFail(r) && /不存在|不属于/.test(msg(r)), '跨插件写配置被拒', msg(r));

    // 配置档不存在
    r = await post('pay/setPluginConfig?id=Epay&config_id=999999', { pid: '1' });
    ok(isFail(r) && /不存在/.test(msg(r)), '不存在的配置档被拒', msg(r));

    // 未知插件
    r = await post('pay/setPluginConfig?id=NoSuchPlugin', { pid: '1' });
    ok(isFail(r) && /插件不存在/.test(msg(r)), '未知插件被拒', msg(r));

    // 缺 id
    r = await post('pay/setPluginConfig', { pid: '1' });
    ok(isFail(r) && /插件不存在/.test(msg(r)), '缺 id 报错', msg(r));

    // #system 无配置字段 → 提交任何键都被丢弃, 不应报错也不应污染 config
    r = await post('pay/setPluginConfig?id=%23system', { gateway: 'https://leak.example.com' });
    ok(isOk(r), '#system 接受写入(不报错)', msg(r));
    const sysProfiles = (data(await get('pay/getPluginConfigs?handle=%23system')) || {}).profiles || [];
    const leaked2 = sysProfiles.find((p) => p.config && p.config.gateway);
    ok(!leaked2, '#system 丢弃未声明字段, 不写入 config', JSON.stringify(sysProfiles.map((p) => p.config)));
  }

  // ============================================================
  sec('9 插件日志 /admin/api/pay/{getPluginLog,ClearPluginLog}');
  {
    let r = await get('pay/getPluginLog?handle=Epay');
    ok(isOk(r) && typeof (data(r) || {}).log === 'string', '读日志返回字符串', msg(r));
    ok((data(r) || {}).log === '', '初始日志为空(新表无行)');

    r = await get('pay/getPluginLog?handle=NoSuchPlugin');
    ok(isFail(r) && /插件不存在/.test(msg(r)), '未知插件读日志报错', msg(r));

    r = await post('pay/ClearPluginLog', { handle: 'Epay' });
    ok(isOk(r), '清空日志成功(无内容也成功)', msg(r));

    r = await post('pay/ClearPluginLog', { handle: 'NoSuchPlugin' });
    ok(isFail(r) && /插件不存在/.test(msg(r)), '未知插件清日志报错', msg(r));
  }

  // ============================================================
  sec('10 拨测占位 /admin/api/pay/{test,testState}');
  {
    let r = await post('pay/test', { id: 94103 });
    ok(isFail(r) && /网关/.test(msg(r)), '拨测给出明确原因而非静默', msg(r));
    r = await post('pay/test', { id: 1 });
    ok(isFail(r) && /余额支付/.test(msg(r)), '余额接口无需拨测', msg(r));
    r = await post('pay/test', { id: 94105 });
    ok(isFail(r) && /归档/.test(msg(r)), '归档接口不可拨测', msg(r));
    r = await post('pay/test', { id: 999999 });
    ok(isFail(r) && /不存在/.test(msg(r)), '拨测不存在接口报错', msg(r));

    r = await get('pay/testState?id=94103');
    ok(isOk(r) && data(r) && data(r).state !== undefined, 'testState 返回 state 字段(前端轮询不会挂)', msg(r));
  }

  // ============================================================
  sec('11 页面与未登录防护');
  {
    for (const p of ['/admin/pay/index', '/admin/pay/plugin']) {
      const r = await req(p);
      ok(r.status === 200 && /pay-(plugin-)?table/.test(await r.text()), p + ' 渲染出原版表格容器', 'status=' + r.status);
    }
    // 页面必须加载原版 controller
    const html = await (await req('/admin/pay/plugin')).text();
    ok(/controller\/pay\/plugin\.js/.test(html), '插件页加载原版 pay/plugin.js');
    const html2 = await (await req('/admin/pay/index')).text();
    ok(/controller\/pay\/api\.js/.test(html2), '支付接口页加载原版 pay/api.js');
    ok(/btn-app-create/.test(html2) && /btn-app-del/.test(html2), '支付接口页保留原版工具栏按钮');

    const saved = new Map(jar);
    jar.clear();
    for (const p of ['/admin/pay/index', '/admin/pay/plugin']) {
      const r = await req(p);
      ok(r.status === 302 && (r.headers.get('location') || '').includes('login'), p + ' 未登录 302', 'status=' + r.status);
    }
    for (const p of ['/admin/api/pay/data', '/admin/api/pay/getPlugins', '/admin/api/pay/getPluginConfigs?handle=Epay']) {
      const r = await getJson(p);
      ok(!isOk(r) || r.status === 401 || r.status === 302, p + ' 未登录被拦', 'status=' + r.status);
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
