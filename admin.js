// ============================================================
// acg-faka Worker 移植 - admin.js (管理后台: 会话 + 认证 + 仪表盘 API)
// 对齐原版:
//   ManageSessionManager (acg_manage_session 表 + MANAGE_USER cookie = base64(JWT))
//   /admin/api/authentication/login
//   /admin/api/dashboard/{overview,trend,data}
//   /admin/api/app/ad
// ============================================================
import {
  now, htmlEscape, md5hex, sha1hex, parseCookies, requestInfo,
  loadConfig, dbRows, dbFirst, dbRun, dbInsert, dbUpdate,
  generatePassword, verifyPassword, throttle, throttleClear,
  queryListPage, dtString,
} from './lib.js';
import { PAY_PLUGINS, findPayPlugin, isPayPluginHandle } from './pay-plugins.js';
import { captchaVerify } from './api.js';

export const MANAGE_SESSION = 'MANAGE_USER';

const enc = new TextEncoder();
const dec = new TextDecoder();

// ---------- 管理会话 (ManageSessionManager 等价) ----------
// acg_manage_session 列: id, manage_id, session_hash, device_type, device_name,
//   user_agent, login_ip, last_ip, created_time, last_seen_time, expires_time, revoked_time
const b64url2 = (buf) => btoa(String.fromCharCode(...new Uint8Array(buf)))
  .replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');

async function sha256hex(input) {
  const buf = typeof input === 'string' ? enc.encode(input) : input;
  const d = await crypto.subtle.digest('SHA-256', buf);
  return [...new Uint8Array(d)].map(b => b.toString(16).padStart(2, '0')).join('');
}

async function hmacSign(key, data) {
  const k = await crypto.subtle.importKey('raw', enc.encode(key), { name: 'HMAC', hash: 'SHA-256' }, false, ['sign']);
  const sig = await crypto.subtle.sign('HMAC', k, enc.encode(data));
  return new Uint8Array(sig);
}

function b64urlDecodeBytes(str) {
  const s = str.replace(/-/g, '+').replace(/_/g, '/');
  const pad = s.length % 4 === 0 ? '' : '='.repeat(4 - (s.length % 4));
  const bin = atob(s + pad);
  return Uint8Array.from(bin, c => c.charCodeAt(0));
}

export async function manageJwtSign(manage, payload, ttl) {
  const header = { alg: 'HS256', typ: 'JWT', mid: Number(manage.id) };
  const nowT = Math.floor(Date.now() / 1000);
  const body = { mid: Number(manage.id), ...payload, iat: nowT, exp: nowT + ttl };
  const h = b64url2(enc.encode(JSON.stringify(header)));
  const p = b64url2(enc.encode(JSON.stringify(body)));
  const sig = await hmacSign(String(manage.password), `${h}.${p}`);
  return `${h}.${p}.${b64url2(sig)}`;
}

export async function manageJwtVerify(token, secret) {
  try {
    const parts = token.split('.');
    if (parts.length !== 3) return null;
    const expected = await hmacSign(secret, `${parts[0]}.${parts[1]}`);
    const got = b64urlDecodeBytes(parts[2]);
    if (expected.length !== got.length) return null;
    let diff = 0;
    for (let i = 0; i < expected.length; i++) diff |= expected[i] ^ got[i];
    if (diff !== 0) return null;
    const payload = JSON.parse(dec.decode(Uint8Array.from(atob(parts[1].replace(/-/g, '+').replace(/_/g, '/') + (parts[1].length % 4 === 0 ? '' : '='.repeat(4 - parts[1].length % 4))), c => c.charCodeAt(0))));
    if (payload.exp && payload.exp < Math.floor(Date.now() / 1000)) return null;
    return payload;
  } catch (e) { return null; }
}

function randomIdentifier() {
  const bytes = crypto.getRandomValues(new Uint8Array(32));
  return b64url2(bytes);
}

function clientInfo(request) {
  const info = requestInfo(request);
  const ua = String(request.headers.get('user-agent') || '');
  const uaDb = [...new TextEncoder().encode(ua.slice(0, 512))].map(b => b).join('');
  // 简单分类 (对齐原版 device)
  const e = enc.encode(ua);
  let type = /iPad|Tablet/i.test(ua) ? 'tablet' : /Android|iPhone|Mobile/i.test(ua) ? 'mobile' : 'desktop';
  let os = '电脑';
  if (/iPhone/i.test(ua)) os = 'iPhone';
  else if (/iPad/i.test(ua)) os = 'iPad';
  else if (/Android/i.test(ua)) os = 'Android';
  else if (/Windows/i.test(ua)) os = 'Windows';
  else if (/Macintosh/i.test(ua)) os = 'macOS';
  else if (/Linux/i.test(ua)) os = 'Linux';
  let bw = '浏览器';
  if (/Edg\//i.test(ua)) bw = 'Edge';
  else if (/OPR\//i.test(ua)) bw = 'Opera';
  else if (/Chrome\//i.test(ua)) bw = 'Chrome';
  else if (/Firefox\//i.test(ua)) bw = 'Firefox';
  else if (/Safari\//i.test(ua)) bw = 'Safari';
  return { ip: info.ip, ua, deviceType: type, deviceName: (os + ' · ' + bw).slice(0, 96), uaBytes: e.length };
}

// 签发管理会话: 返回 {cookie, sessionId}
export async function issueManageSession(env, request, manage, remember) {
  const expire = remember ? 86400 * 365 : 86400;
  const expiresAt = Math.floor(Date.now() / 1000) + expire;
  const identifier = randomIdentifier();
  const ci = clientInfo(request);
  const t = now();
  const sessionId = await dbInsert(env, 'acg_manage_session', {
    manage_id: Number(manage.id),
    session_hash: await sha256hex(identifier),
    device_type: ci.deviceType,
    device_name: ci.deviceName,
    user_agent: ci.ua.slice(0, 512),
    login_ip: ci.ip,
    last_ip: ci.ip,
    created_time: t,
    last_seen_time: t,
    expires_time: expiresAt,
  });
  const token = await manageJwtSign(manage, { sid: identifier }, expire);
  return { cookie: btoa(token), sessionId, expiresAt };
}

// 验证管理会话: 返回 manage | null
export async function authenticateManage(env, request, touch = true) {
  const cookies = parseCookies(request.headers.get('Cookie') || '');
  const encoded = cookies[MANAGE_SESSION];
  if (!encoded) return null;
  let token;
  try {
    token = atob(encoded);
  } catch (e) { return null; }
  if (!token) return null;

  // 头部提取 mid
  let mid = 0;
  try {
    const headB64 = token.split('.')[0];
    const head = JSON.parse(dec.decode(Uint8Array.from(atob(headB64.replace(/-/g, '+').replace(/_/g, '/') + (headB64.length % 4 === 0 ? '' : '='.repeat(4 - headB64.length % 4))), c => c.charCodeAt(0))));
    mid = Number(head.mid) || 0;
  } catch (e) { return null; }
  if (mid < 1) return null;

  const manage = await dbFirst(env, 'SELECT * FROM acg_manage WHERE id=?', mid);
  if (!manage || Number(manage.status) !== 1) return null;

  const claims = await manageJwtVerify(token, String(manage.password));
  if (!claims || !claims.sid || Number(claims.mid) !== mid) return null;
  const sid = String(claims.sid);
  if (!/^[A-Za-z0-9_-]{43}$/.test(sid)) return null;

  const sessionHash = await sha256hex(sid);
  const s = await dbFirst(env,
    `SELECT * FROM acg_manage_session WHERE manage_id=? AND session_hash=? AND revoked_time IS NULL AND expires_time>?`,
    mid, sessionHash, Math.floor(Date.now() / 1000));
  if (!s) return null;

  if (touch) {
    const lastSeen = Number(s.last_seen_time) || 0;
    if (lastSeen <= Math.floor(Date.now() / 1000) - 300) {
      await dbRun(env, 'UPDATE acg_manage_session SET last_seen_time=?, last_ip=? WHERE id=? AND revoked_time IS NULL',
        Math.floor(Date.now() / 1000), clientInfo(request).ip, s.id);
    }
  }
  return manage;
}

export async function revokeManageSession(env, request) {
  const cookies = parseCookies(request.headers.get('Cookie') || '');
  const encoded = cookies[MANAGE_SESSION];
  if (!encoded) return;
  let token;
  try { token = atob(encoded); } catch (e) { return; }
  if (!token) return;
  let mid = 0;
  try {
    const headB64 = token.split('.')[0];
    const head = JSON.parse(dec.decode(Uint8Array.from(atob(headB64.replace(/-/g, '+').replace(/_/g, '/') + (headB64.length % 4 === 0 ? '' : '='.repeat(4 - headB64.length % 4))), c => c.charCodeAt(0))));
    mid = Number(head.mid) || 0;
  } catch (e) { return; }
  const manage = await dbFirst(env, 'SELECT id, password FROM acg_manage WHERE id=?', mid);
  if (!manage) return;
  const claims = await manageJwtVerify(token, String(manage.password));
  if (!claims || !claims.sid) return;
  const sessionHash = await sha256hex(String(claims.sid));
  await dbRun(env, 'UPDATE acg_manage_session SET revoked_time=? WHERE manage_id=? AND session_hash=? AND revoked_time IS NULL',
    now(), mid, sessionHash);
}

const adminJson = (obj, status = 200) => new Response(JSON.stringify(obj), {
  status,
  headers: { 'Content-Type': 'application/json; charset=utf-8', 'X-Content-Type-Options': 'nosniff' },
});

function apiOk(msg = 'success', data = []) {
  return adminJson({ code: 200, msg, data });
}
function apiErr(msg, code = 0) {
  return adminJson({ code, msg, data: [] });
}

// (readBody 已由 worker.js route 层消费，直接传入)

// ---------- 登录 ----------
export async function adminLogin(env, request, url, body = {}) {
  const username = String(body.username || '');
  const password = String(body.password || '');
  const ip = requestInfo(request).ip;

  // 限流: adminlogin:{ip} 10次/600s
  const throttleKey = `adminlogin:${ip}`;
  if (throttle(throttleKey, 10, 600)) {
    return apiErr('登录尝试过于频繁，请稍后再试');
  }

  // 验证码
  const cfg = await loadConfig(env);
  if (String(cfg.admin_login_verification) !== '0') {
    const okC = await captchaCheck(env, request, 'adminLogin', String(body.captcha || ''));
    if (!okC) return apiErr('验证码错误');
  }

  const manage = await dbFirst(env, 'SELECT * FROM acg_manage WHERE email=?', username);
  if (!manage) return apiErr('该邮箱不存在');
  const okP = await verifyPassword(String(manage.password), password, String(manage.salt));
  if (!okP) return apiErr('密码错误');
  if (Number(manage.status) !== 1) return apiErr('账号已被暂停使用');

  await dbRun(env, 'UPDATE acg_manage SET last_login_time=login_time, last_login_ip=login_ip, login_time=?, login_ip=? WHERE id=?',
    now(), ip, manage.id);
  const issued = await issueManageSession(env, request, manage, Boolean(body.remember));

  // 审计日志
  try {
    await dbInsert(env, 'acg_manage_log', {
      email: manage.email, nickname: manage.nickname || '', content: '登录了后台',
      create_time: now(), create_ip: ip,
      ua: String(request.headers.get('user-agent') || '').slice(0, 512),
      risk: 0,
    });
  } catch (e) { /* */ }

  const res = apiOk('success', { expires_at: issued.expiresAt, session_id: issued.sessionId });
  const host = new URL(request.url).host;
  const secure = host !== 'localhost' && host !== '127.0.0.1';
  res.headers.append('Set-Cookie',
    `${MANAGE_SESSION}=${encodeURIComponent(issued.cookie)}; Path=/; HttpOnly; SameSite=Lax; Max-Age=${issued.expiresAt - Math.floor(Date.now() / 1000)}`);
  return res;
}

// captcha 校验 (复用前台验证码 cookie 机制)
async function captchaCheck(env, request, action, input) {
  try {
    return await captchaVerify(env, request, action, input);
  } catch (e) { return false; }
}

// ---------- 仪表盘 ----------
const money = (v) => Number(v || 0).toFixed(2);

const NET_SQL = 'amount - COALESCE(pay_cost,0) - rent - COALESCE(rebate,0) - COALESCE(divide_amount,0)';

function dayRange(offsetDays, endOfDay) {
  const d = new Date();
  d.setDate(d.getDate() + offsetDays);
  const y = d.getFullYear(), m = String(d.getMonth() + 1).padStart(2, '0'), dd = String(d.getDate()).padStart(2, '0');
  const start = Math.floor(new Date(`${y}-${m}-${dd}T00:00:00`).getTime() / 1000);
  const end = endOfDay ? start + 86400 - 1 : Math.floor(Date.now() / 1000);
  return [start, end];
}

function monthRange(offsetMonths) {
  const d = new Date();
  const y = d.getFullYear(), m = d.getMonth() + 1;
  const first = new Date(y, m - 1 + offsetMonths, 1);
  const start = Math.floor(new Date(first.getFullYear(), first.getMonth(), 1).getTime() / 1000);
  const lastDay = new Date(first.getFullYear(), first.getMonth() + 1, 0).getDate();
  const end = start + lastDay * 86400 - 1;
  return [start, end];
}

async function orderStats(env, time) {
  const w = time ? ' WHERE status=1 AND create_time BETWEEN ? AND ?' : ' WHERE status=1';
  const b = time ? [...time] : [];
  const sql = `SELECT
      COUNT(*) AS order_num,
      COALESCE(SUM(amount),0) AS turnover,
      COALESCE(SUM(COALESCE(pay_cost,0)),0) AS pay_cost,
      COALESCE(SUM(rent),0) AS rent,
      COALESCE(SUM(COALESCE(rebate,0)),0) AS rebate,
      COALESCE(SUM(CASE WHEN user_id > 0 THEN COALESCE(rebate,0) ELSE 0 END),0) AS rebate_merchant,
      COALESCE(SUM(CASE WHEN user_id = 0 THEN COALESCE(rebate,0) ELSE 0 END),0) AS rebate_substation,
      COALESCE(SUM(COALESCE(divide_amount,0)),0) AS divide_amount,
      COALESCE(SUM(${NET_SQL}),0) AS profit,
      COALESCE(SUM(CASE WHEN pay_id <> 1 THEN amount ELSE 0 END),0) AS online_amount,
      COALESCE(SUM(CASE WHEN pay_id = 1 THEN amount ELSE 0 END),0) AS balance_amount,
      COUNT(DISTINCT CASE WHEN owner > 0 THEN owner END) AS member_buyers,
      COUNT(DISTINCT CASE WHEN owner = 0 THEN contact END) AS guest_buyers
    FROM acg_order${w}`;
  return dbFirst(env, sql, ...b);
}

async function channelStats(env, time) {
  const collect = async (table) => {
    const w = time ? ` WHERE status=1 AND create_time BETWEEN ? AND ?` : ' WHERE status=1';
    const b = time ? [...time] : [];
    const rows = await dbRows(env,
      `SELECT pay_id, COUNT(*) AS num, COALESCE(SUM(amount),0) AS amount, COALESCE(SUM(COALESCE(gateway_amount, amount)),0) AS gateway FROM ${table}${w} GROUP BY pay_id`, ...b);
    return rows;
  };
  const orders = await collect('acg_order');
  const recharges = await collect('acg_user_recharge');
  const map = {};
  for (const r of orders) map[r.pay_id] = { pay_id: r.pay_id, order_num: r.num, order_amount: r.amount, recharge_num: 0, recharge_amount: '0.00', gateway: r.gateway, name: null, icon: null, num: r.num, amount: r.amount };
  for (const r of recharges) {
    if (!map[r.pay_id]) map[r.pay_id] = { pay_id: r.pay_id, order_num: 0, order_amount: '0.00', recharge_num: 0, recharge_amount: '0.00', gateway: r.gateway, name: null, icon: null, num: r.num, amount: r.amount };
    map[r.pay_id].recharge_num = r.num;
    map[r.pay_id].recharge_amount = r.amount;
    map[r.pay_id].num += r.num;
    map[r.pay_id].amount = (Number(map[r.pay_id].amount) + Number(r.amount)).toFixed(2);
  }
  const ids = Object.keys(map);
  if (ids.length) {
    const pays = await dbRows(env, `SELECT id, name, icon FROM acg_pay WHERE id IN (${ids.map(() => '?').join(',')})`, ...ids);
    for (const p of pays) {
      if (map[p.id]) { map[p.id].name = p.name; map[p.id].icon = p.icon; }
    }
  }
  return Object.values(map).sort((a, b) => Number(b.amount) - Number(a.amount));
}

export async function dashboardOverview(env, request, url) {
  const today = dayRange(0, true);
  const nowD = Math.floor(Date.now() / 1000);
  const yesterday = dayRange(-1, true);
  const yesterdayUntilNow = [yesterday[0], nowD - 86400];
  const dayBefore = dayRange(-2, true);
  const month = monthRange(0);
  const lastMonthFirst = monthRange(-1)[0];
  const lm = monthRange(-1);
  // 上月同日(截到此刻) 简化: 取上月全月初到"上月相同日期" 这里简化为上月全月
  const lastMonthSame = lm;

  const periods = { today, yesterday, yesterday_until_now: yesterdayUntilNow, day_before: dayBefore, month, last_month_same: lastMonthSame, last_month: lm };

  const selects = [];
  const b = [];
  for (const key of Object.keys(periods)) {
    const [s, e] = periods[key];
    selects.push(`COALESCE(SUM(CASE WHEN create_time BETWEEN ? AND ? THEN ${NET_SQL} ELSE 0 END),0) AS ${key}_profit`);
    selects.push(`COALESCE(SUM(CASE WHEN create_time BETWEEN ? AND ? THEN amount ELSE 0 END),0) AS ${key}_turnover`);
    selects.push(`COUNT(CASE WHEN create_time BETWEEN ? AND ? THEN 1 END) AS ${key}_orders`);
    b.push(s, e, s, e, s, e);
  }
  const row = await dbFirst(env,
    `SELECT ${selects.join(', ')} FROM acg_order WHERE status=1 AND create_time BETWEEN ? AND ?`,
    ...b, lm[0], today[1]);

  const stats = {};
  for (const key of Object.keys(periods)) {
    stats[key] = {
      profit: money(row ? row[`${key}_profit`] : 0),
      turnover: money(row ? row[`${key}_turnover`] : 0),
      orders: row ? Number(row[`${key}_orders`]) : 0,
      start: periods[key][0], end: periods[key][1],
    };
  }

  // 待处理
  const todo = { cash_num: 0, cash_amount: '0.00', delivery_num: 0, ticket_num: 0 };
  try {
    const pendingCash = await dbFirst(env, `SELECT COUNT(*) AS num, COALESCE(SUM(amount),0) AS amount FROM acg_cash WHERE status=0`);
    if (pendingCash) { todo.cash_num = Number(pendingCash.num); todo.cash_amount = money(pendingCash.amount); }
  } catch (e) { /* */ }
  try {
    const d = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_order o WHERE o.status=1 AND o.delivery_status=0 AND o.user_id=0 AND EXISTS (SELECT 1 FROM acg_commodity c WHERE c.id=o.commodity_id AND c.delivery_way=1)`);
    if (d) todo.delivery_num = Number(d.n);
  } catch (e) { /* */ }
  try {
    const t = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_ticket WHERE status=0`);
    if (t) todo.ticket_num = Number(t.n);
  } catch (e) { /* */ }

  const manage = await authenticateManage(env, request, false);
  return apiOk('success', {
    stats,
    todo,
    is_owner: manage ? Number(manage.type) === 0 : false,
  });
}

function fmtDay(ts) {
  const d = new Date(ts * 1000);
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

async function daily(env, days) {
  const nowD = Math.floor(Date.now() / 1000);
  const start = nowD - (days - 1) * 86400;
  const rows = {};
  for (let i = days - 1; i >= 0; i--) {
    rows[fmtDay(nowD - i * 86400)] = { profit: '0.00', turnover: '0.00', orders: 0, recharge: '0.00', cash: '0.00' };
  }
  try {
    const o = await dbRows(env,
      `SELECT (create_time / 86400) AS dayk, COUNT(*) AS orders, COALESCE(SUM(amount),0) AS turnover, COALESCE(SUM(${NET_SQL}),0) AS profit
       FROM acg_order WHERE status=1 AND create_time BETWEEN ? AND ?
       GROUP BY dayk`, start, nowD);
    for (const r of o) {
      const day = fmtDay(Number(r.dayk) * 86400);
      if (rows[day]) { rows[day].orders = Number(r.orders); rows[day].turnover = money(r.turnover); rows[day].profit = money(r.profit); }
    }
  } catch (e) { /* */ }
  try {
    const c = await dbRows(env,
      `SELECT (create_time / 86400) AS dayk, COALESCE(SUM(amount),0) AS amount FROM acg_cash WHERE status=1 AND create_time BETWEEN ? AND ? GROUP BY dayk`, start, nowD);
    for (const r of c) { const day = fmtDay(Number(r.dayk) * 86400); if (rows[day]) rows[day].cash = money(r.amount); }
  } catch (e) { /* */ }
  try {
    const r = await dbRows(env,
      `SELECT (create_time / 86400) AS dayk, COALESCE(SUM(amount),0) AS amount FROM acg_user_recharge WHERE status=1 AND create_time BETWEEN ? AND ? GROUP BY dayk`, start, nowD);
    for (const x of r) { const day = fmtDay(Number(x.dayk) * 86400); if (rows[day]) rows[day].recharge = money(x.amount); }
  } catch (e) { /* */ }
  return rows;
}

export async function dashboardTrend(env, request, url, days) {
  days = Number(days) === 30 ? 30 : 7;
  const rows = await daily(env, days);
  let profit = 0, turnover = 0, orders = 0, recharge = 0;
  const list = [];
  for (const [day, r] of Object.entries(rows)) {
    profit += Number(r.profit); turnover += Number(r.turnover); orders += Number(r.orders); recharge += Number(r.recharge);
    list.push({ date: day, profit: r.profit, turnover: r.turnover, orders: r.orders, recharge: r.recharge });
  }
  return apiOk('success', {
    days: list,
    total: { profit: money(profit), turnover: money(turnover), orders, recharge: money(recharge) },
  });
}

export async function dashboardData(env, request, url, type) {
  let time = null;
  type = Number(type) || 0;
  const nowD = Math.floor(Date.now() / 1000);
  if (type === 0) time = [dayRange(0, false)[0], nowD];
  else if (type === 1) time = dayRange(-1, true);
  else if (type === 2) { // 本周: 周一 00:00
    const d = new Date(); const day = (d.getDay() + 6) % 7;
    const s = new Date(d.getFullYear(), d.getMonth(), d.getDate() - day);
    time = [Math.floor(s.getTime() / 1000), nowD];
  } else if (type === 3) time = [monthRange(0)[0], nowD];
  else if (type === 4) time = null;

  const order = await orderStats(env, time);
  const unpaidQ = time ? { sql: ' WHERE status=0 AND create_time BETWEEN ? AND ?', b: time } : { sql: ' WHERE status=0', b: [] };
  const cashQ = time ? { sql: ' WHERE create_time BETWEEN ? AND ?', b: time } : { sql: '', b: [] };
  const rechargeQ = time ? { sql: ' WHERE status=1 AND create_time BETWEEN ? AND ?', b: time } : { sql: ' WHERE status=1', b: [] };
  const userQ = time ? { sql: ' WHERE create_time BETWEEN ? AND ?', b: time } : { sql: '', b: [] };
  const businessQ = time ? { sql: ' WHERE create_time BETWEEN ? AND ?', b: time } : { sql: '', b: [] };

  const [cash, recharge, userNum, businessNum, unpaidNum] = await Promise.all([
    dbFirst(env, `SELECT
        COUNT(CASE WHEN status = 0 THEN 1 END) AS pending_num,
        COALESCE(SUM(CASE WHEN status = 1 THEN amount ELSE 0 END),0) AS done_amount,
        COALESCE(SUM(CASE WHEN status = 1 AND card <> 2 THEN amount ELSE 0 END),0) AS paid_out,
        COALESCE(SUM(CASE WHEN status = 1 AND card = 2 THEN amount ELSE 0 END),0) AS to_balance,
        COALESCE(SUM(CASE WHEN status = 1 THEN cost ELSE 0 END),0) AS fee_income
      FROM acg_cash${cashQ.sql}`, ...cashQ.b),
    dbFirst(env, `SELECT COUNT(*) AS num, COALESCE(SUM(amount),0) AS amount FROM acg_user_recharge${rechargeQ.sql}`, ...rechargeQ.b),
    dbFirst(env, `SELECT COUNT(*) AS n FROM acg_user${userQ.sql}`, ...userQ.b),
    dbFirst(env, `SELECT COUNT(*) AS n FROM acg_business${businessQ.sql}`, ...businessQ.b),
    dbFirst(env, `SELECT COUNT(*) AS n FROM acg_order${unpaidQ.sql}`, ...unpaidQ.b),
  ]);

  const orderNum = Number(order ? order.order_num : 0);
  const turnover = money(order ? order.turnover : 0);
  const avg = orderNum > 0 ? (Number(turnover) / orderNum).toFixed(2) : '0.00';

  return apiOk('success', {
    turnover,
    order_num: orderNum,
    online_amout: money(order ? order.online_amount : 0),
    divide_amount: money(order ? order.divide_amount : 0),
    rebate: money(order ? order.rebate : 0),
    cost: money(order ? order.rebate_merchant : 0),
    profit: money(order ? order.profit : 0),
    business: Number(businessNum ? businessNum.n : 0),
    cash_status_0: Number(cash ? cash.pending_num : 0),
    cash_money_status_1: money(cash ? cash.done_amount : 0),
    recharge_amount: money(recharge ? recharge.amount : 0),
    user_register_num: Number(userNum ? userNum.n : 0),
    pay_cost: money(order ? order.pay_cost : 0),
    rent: money(order ? order.rent : 0),
    rebate_merchant: money(order ? order.rebate_merchant : 0),
    rebate_substation: money(order ? order.rebate_substation : 0),
    balance_amount: money(order ? order.balance_amount : 0),
    buyer_num: Number(order ? (Number(order.member_buyers) + Number(order.guest_buyers)) : 0),
    avg_order: avg,
    unpaid_order_num: Number(unpaidNum ? unpaidNum.n : 0),
    cash_paid_out: money(cash ? cash.paid_out : 0),
    cash_to_balance: money(cash ? cash.to_balance : 0),
    cash_fee_income: money(cash ? cash.fee_income : 0),
    recharge_num: Number(recharge ? recharge.num : 0),
    channels: await channelStats(env, time),
    gateway_cny: false,
    range: time ? { start: time[0], end: time[1] } : null,
  });
}

// ---------- 分类管理 (对齐 Admin/Api/Category) ----------
const intList = (v, name) => {
  const arr = Array.isArray(v) ? v : String(v ?? '').split(',');
  const ids = [];
  for (const c of arr) {
    const n = Number(String(c).trim());
    if (!Number.isInteger(n) || n <= 0) throw new Error(`${name}必须是正整数`);
    ids.push(n);
  }
  return [...new Set(ids)];
};

export async function categoryData(env, request, url, body = {}) {
  const rows = await dbRows(env, 'SELECT * FROM acg_category WHERE owner=0 ORDER BY sort ASC, id ASC');
  // 深度优先树序 (对齐 treeOrder)
  const byId = new Map(rows.map(r => [Number(r.id), r]));
  const children = new Map();
  const roots = [];
  for (const r of rows) {
    const pid = Number(r.pid) || 0;
    if (pid > 0 && pid !== Number(r.id) && byId.has(pid)) {
      if (!children.has(pid)) children.set(pid, []);
      children.get(pid).push(r);
    } else roots.push(r);
  }
  const ordered = [];
  const visited = new Set();
  const walk = (row) => {
    const id = Number(row.id);
    if (visited.has(id)) return;
    visited.add(id);
    ordered.push(row);
    for (const ch of children.get(id) || []) walk(ch);
  };
  for (const r of roots) walk(r);
  for (const r of rows) walk(r);

  const list = ordered.map(r => ({ ...r, share_url: `/cat/${r.id}` }));
  return apiOk('success', { list, page: 1, limit: -1, count: list.length, records: list.length });
}

export async function categorySave(env, request, url, body = {}) {
  const allowed = ['id', 'pid', 'icon', 'name', 'sort', 'hide', 'status', 'user_level_config'];
  const map = {};
  for (const k of allowed) if (Object.prototype.hasOwnProperty.call(body, k)) map[k] = body[k];
  const id = Number(map.id) || 0;
  if (id === 0 || !(await dbFirst(env, 'SELECT id FROM acg_category WHERE id=?', id))) {
    if (!map.name || String(map.name).trim() === '') return apiErr('分类名称不能为空');
  }
  if (Object.prototype.hasOwnProperty.call(map, 'status') && !['0', '1'].includes(String(map.status))) return apiErr('分类状态参数不正确');
  if (Object.prototype.hasOwnProperty.call(map, 'hide') && !['0', '1'].includes(String(map.hide))) return apiErr('分类隐藏参数不正确');
  if (Object.prototype.hasOwnProperty.call(map, 'sort')) {
    const s = Number(map.sort);
    if (!Number.isInteger(s) || s < 0 || s > 65535) return apiErr('分类排序必须是 0 到 65535 的整数');
  }
  const hasParent = Object.prototype.hasOwnProperty.call(map, 'pid');
  let parentId = hasParent ? Number(map.pid) || 0 : 0;
  if (hasParent && parentId > 0) {
    const parent = await dbFirst(env, 'SELECT * FROM acg_category WHERE owner=0 AND id=?', parentId);
    if (!parent) return apiErr('父级分类不存在或不属于同一创建者');
    if (id > 0 && parentId === id) return apiErr('分类不能设为自己的子分类');
    // 遍历父链防环
    let cur = parent, seen = new Set();
    while (cur) {
      const pk = Number(cur.id);
      if (seen.has(pk)) return apiErr('分类层级存在循环，请重新选择父级分类');
      seen.add(pk);
      if (id > 0 && pk === id) return apiErr('不能选择当前分类的子分类作为父级');
      const nid = Number(cur.pid) || 0;
      cur = nid > 0 ? await dbFirst(env, 'SELECT * FROM acg_category WHERE owner=0 AND id=?', nid) : null;
    }
  }
  const data = {};
  for (const k of ['icon', 'name', 'user_level_config']) if (Object.prototype.hasOwnProperty.call(map, k)) data[k] = map[k];
  if (Object.prototype.hasOwnProperty.call(map, 'status')) data.status = Number(map.status);
  if (Object.prototype.hasOwnProperty.call(map, 'hide')) data.hide = Number(map.hide);
  if (Object.prototype.hasOwnProperty.call(map, 'sort')) data.sort = Number(map.sort);
  if (hasParent) data.pid = parentId > 0 ? parentId : null;
  let newId = 0;
  if (id > 0) {
    await dbUpdate(env, 'acg_category', data, 'id=?', id);
  } else {
    await dbInsert(env, 'acg_category', { ...data, name: String(map.name || '').trim(), sort: data.sort ?? 0, status: data.status ?? 1, hide: data.hide ?? 0, owner: 0, create_time: now(), pid: data.pid ?? null }).then(id => newId = id);
  }
  try { await dbInsert(env, 'acg_manage_log', { email: 'admin', nickname: '', content: '[新增/修改]商品分类', create_time: now(), create_ip: requestInfo(request).ip, ua: String(request.headers.get('user-agent') || '').slice(0, 512), risk: 0 }); } catch (e) {}
  return apiOk('（＾∀＾）保存成功', { id: newId || id });
}

export async function categoryStatus(env, request, url, body = {}) {
  let list;
  try { list = intList(body.list, '分类ID'); } catch (e) { return apiErr(e.message); }
  if (!list.length || !['0', '1'].includes(String(body.status))) return apiErr('分类状态请求参数不正确');
  const status = Number(body.status);
  const cats = await dbRows(env, 'SELECT id, pid, owner FROM acg_category');
  const byId = new Map(cats.map(c => [Number(c.id), c]));
  const children = new Map();
  for (const c of cats) {
    const pid = Number(c.pid) || 0;
    if (!children.has(pid)) children.set(pid, []);
    children.get(pid).push(Number(c.id));
  }
  const targets = new Set();
  for (const rootId of list) {
    if (!byId.has(rootId)) continue;
    const owner = Number(byId.get(rootId).owner);
    if (status === 0) {
      const q = [rootId];
      while (q.length) {
        const id = q.shift();
        if (targets.has(id) || !byId.has(id) || Number(byId.get(id).owner) !== owner) continue;
        targets.add(id);
        for (const ch of children.get(id) || []) q.push(ch);
      }
    } else {
      let id = rootId, seen = new Set();
      while (id > 0 && byId.has(id)) {
        if (seen.has(id) || Number(byId.get(id).owner) !== owner) return apiErr('分类层级无效，无法启用');
        seen.add(id); targets.add(id);
        id = Number(byId.get(id).pid) || 0;
      }
    }
  }
  if (!targets.size) return apiErr('没有可更新的分类');
  await dbRun(env, `UPDATE acg_category SET status=? WHERE id IN (${[...targets].map(() => '?').join(',')})`, status, ...[...targets]);
  return apiOk('分类状态已经更新');
}

function deleteTokenKey(password) {
  return sha256hex(`category-delete-preview-v1|${password}`);
}
async function deleteTokenSign(password, body) {
  const key = await deleteTokenKey(password);
  const sig = await hmacSign(key, body);
  return [...sig].map(b => b.toString(16).padStart(2, '0')).join('');
}
const b64urlEncode = (str) => btoa(unescape(encodeURIComponent(str))).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');

export async function categoryDeleteImpact(env, request, url, body = {}, manage) {
  let list;
  try { list = intList(body.list, '分类ID'); } catch (e) { return apiErr(e.message); }
  if (!list.length) return apiErr('你还没有选择商品分类');
  const impact = await safeCategoryDeleteImpact(env, list);
  const token = await issueDeleteToken(env, manage, list, impact);
  const publicKeys = ['category_count', 'scope_count', 'descendant_count', 'hierarchy_cycle_count', 'commodity_count', 'order_count', 'card_count', 'coupon_count', 'used_coupon_count', 'user_category_count', 'config_reference_count', 'can_delete'];
  const pub = {};
  for (const k of publicKeys) pub[k] = impact[k];
  pub.preview_token = token;
  pub.preview_expires_in = 180;
  return apiOk('success', pub);
}

async function issueDeleteToken(env, manage, ids, impact) {
  const key = await sha256hex('category-delete-preview-v1|' + manage.password);
  const snapshot = await sha256hex(JSON.stringify(impact.snapshot));
  const payload = { ids, snapshot, manage_id: Number(manage.id), iat: now(), exp: now() + 180 };
  const bodyB64 = b64urlEncode(JSON.stringify(payload));
  const sig = await hmacHex(key, bodyB64);
  return `${bodyB64}.${sig}`;
}
async function hmacHex(key, data) {
  const sig = await hmacSign(key, data);
  return [...sig].map(b => b.toString(16).padStart(2, '0')).join('');
}

async function safeCategoryDeleteImpact(env, requestedIds) {
  requestedIds = [...requestedIds].sort((a, b) => a - b);
  const cats = await dbRows(env, 'SELECT id, pid FROM acg_category');
  const existing = new Map(cats.map(c => [Number(c.id), Number(c.pid) || 0]));
  const children = new Map();
  for (const c of cats) {
    const pid = Number(c.pid) || 0;
    if (!children.has(pid)) children.set(pid, []);
    children.get(pid).push(Number(c.id));
  }
  for (const id of requestedIds) if (!existing.has(id)) throw new Error('部分商品分类不存在，请刷新后重试');
  const visited = new Set(requestedIds);
  const q = [...requestedIds];
  while (q.length) {
    const id = q.shift();
    for (const ch of children.get(id) || []) { if (!visited.has(ch)) { visited.add(ch); q.push(ch); } }
  }
  const scopeIds = [...visited].sort((a, b) => a - b);
  const descendantIds = scopeIds.filter(id => !requestedIds.includes(id));
  const commodityIds = [];
  if (scopeIds.length) {
    const rows = await dbRows(env, `SELECT id FROM acg_commodity WHERE category_id IN (${scopeIds.map(() => '?').join(',')})`, ...scopeIds);
    commodityIds.push(...rows.map(r => Number(r.id)));
  }
  commodityIds.sort((a, b) => a - b);
  let orderCount = 0, cardCount = 0;
  if (commodityIds.length) {
    const o = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_order WHERE commodity_id IN (${commodityIds.map(() => '?').join(',')})`, ...commodityIds);
    orderCount = o ? Number(o.n) : 0;
    const c2 = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_card WHERE commodity_id IN (${commodityIds.map(() => '?').join(',')})`, ...commodityIds);
    cardCount = c2 ? Number(c2.n) : 0;
  }
  let couponIds = [], userCategoryIds = [], configReferenceIds = [];
  if (scopeIds.length) {
    const cp = await dbRows(env, `SELECT id, status, trade_no FROM acg_coupon WHERE category_id IN (${scopeIds.map(() => '?').join(',')})`, ...scopeIds);
    couponIds = cp.map(r => Number(r.id));
    const uc = await dbRows(env, `SELECT id FROM acg_user_category WHERE category_id IN (${scopeIds.map(() => '?').join(',')})`, ...scopeIds);
    userCategoryIds = uc.map(r => Number(r.id));
    const cf = await dbRows(env, `SELECT id FROM acg_config WHERE "key"='default_category' AND value IN (${scopeIds.map(() => '?').join(',')})`, ...scopeIds.map(String));
    configReferenceIds = cf.map(r => Number(r.id));
  }
  const snapshot = { ids: requestedIds, scopeIds, commodityIds };
  return {
    category_count: requestedIds.length, scope_count: scopeIds.length,
    descendant_count: descendantIds.length, hierarchy_cycle_count: 0,
    commodity_count: commodityIds.length, order_count: orderCount, card_count: cardCount,
    coupon_count: couponIds.length, used_coupon_count: 0, user_category_count: userCategoryIds.length,
    config_reference_count: configReferenceIds.length, can_delete: true,
    snapshot, scope_ids: scopeIds, commodity_ids: commodityIds,
  };
}

export async function categoryDel(env, request, url, body = {}, manage) {
  let list;
  try { list = intList(body.list, '分类ID'); } catch (e) { return apiErr(e.message); }
  if (!list.length) return apiErr('你还没有选择商品分类');
  const token = String(body.preview_token || '');
  const [b64, sig] = token.split('.');
  const key = await sha256hex('category-delete-preview-v1|' + manage.password);
  const expect = await hmacHex(key, b64 || '');
  if (!/^[a-f0-9]{64}$/.test(sig || '') || sig !== expect) return apiErr('删除预览凭证无效，请重新预览');
  let payload;
  try {
    const json = decodeURIComponent(escape(atob(b64.replace(/-/g, '+').replace(/_/g, '/') + (b64.length % 4 === 0 ? '' : '='.repeat(4 - b64.length % 4)))));
    payload = JSON.parse(json);
  } catch (e) { return apiErr('删除预览凭证无效，请重新预览'); }
  payload.ids = (payload.ids || []).map(Number).sort((a, b) => a - b);
  if (JSON.stringify(payload.ids) !== JSON.stringify([...list].sort((a, b) => a - b))) return apiErr('删除预览已过期或范围不一致，请重新预览');
  if (Number(payload.exp) < now()) return apiErr('删除预览已过期或范围不一致，请重新预览');
  const impact = await safeCategoryDeleteImpact(env, list);
  if (impact.commodity_ids.length) {
    await dbRun(env, `DELETE FROM acg_card WHERE commodity_id IN (${impact.commodity_ids.map(() => '?').join(',')})`, ...impact.commodity_ids);
    await dbRun(env, `UPDATE acg_order SET card_id=NULL, secret=NULL WHERE commodity_id IN (${impact.commodity_ids.map(() => '?').join(',')})`, ...impact.commodity_ids);
    await dbRun(env, `DELETE FROM acg_commodity WHERE id IN (${impact.commodity_ids.map(() => '?').join(',')})`, ...impact.commodity_ids);
  }
  if (impact.scope_ids.length) {
    await dbRun(env, `DELETE FROM acg_coupon WHERE category_id IN (${impact.scope_ids.map(() => '?').join(',')}) AND id NOT IN (SELECT DISTINCT coupon_id FROM acg_order WHERE coupon_id IS NOT NULL)`, ...impact.scope_ids);
    await dbRun(env, `DELETE FROM acg_user_category WHERE category_id IN (${impact.scope_ids.map(() => '?').join(',')})`, ...impact.scope_ids);
    await dbRun(env, `DELETE FROM acg_category WHERE id IN (${impact.scope_ids.map(() => '?').join(',')})`, ...impact.scope_ids);
  }
  try { await dbRun(env, `UPDATE acg_config SET value='' WHERE "key"='default_category' AND value IN (${impact.scope_ids.map(() => '?').join(',')})`, ...impact.scope_ids.map(String)); } catch (e) {}
  return apiOk('（＾∀＾）移除成功', { category_count: impact.scope_count, commodity_count: impact.commodity_count, order_count: impact.order_count, coupon_count: impact.coupon_count });
}

export async function categoryReorder(env, request, url, body = {}) {
  let list;
  try { list = intList(body.list, '分类ID'); } catch (e) { return apiErr(e.message); }
  if (list.length < 2) return apiErr('至少需要两个同级分类才能调整顺序');
  const rows = await dbRows(env, `SELECT id, pid, owner FROM acg_category WHERE id IN (${list.map(() => '?').join(',')})`, ...list);
  if (rows.length !== list.length) return apiErr('部分分类已不存在，请刷新后再调整顺序');
  const owner = Number(rows[0].owner), pid = Number(rows[0].pid) || 0;
  for (const r of rows) if (Number(r.owner) !== owner || (Number(r.pid) || 0) !== pid) return apiErr('只能在同一个父级分类下调整顺序');
  const sibs = pid > 0
    ? await dbRows(env, 'SELECT id FROM acg_category WHERE owner=? AND pid=?', owner, pid)
    : await dbRows(env, 'SELECT id FROM acg_category WHERE owner=? AND (pid IS NULL OR pid=0)', owner);
  const sibIds = sibs.map(r => Number(r.id)).sort((a, b) => a - b);
  const sub = [...list].sort((a, b) => a - b);
  if (JSON.stringify(sibIds) !== JSON.stringify(sub)) return apiErr('同级分类已发生变化，请刷新后再调整顺序');
  for (let i = 0; i < list.length; i++) await dbRun(env, 'UPDATE acg_category SET sort=? WHERE id=?', i, list[i]);
  return apiOk('排序已保存');
}

// ---------- 商品管理 (对齐 Admin/Api/Commodity) ----------
export async function commodityData(env, request, url, body = {}) {
  const page = Math.max(1, Number(body.page) || 1);
  const pageSize = Math.min(100, Math.max(1, Number(body.limit) || 10));
  const wheres = [];
  const params = [];
  if (!body.display_scope || Number(body.display_scope) === 1) {
    wheres.push('owner=0');
  }
  if (body.category_id) { wheres.push('category_id=?'); params.push(Number(body.category_id)); }
  if (body.name) { wheres.push('name LIKE ?'); params.push(`%${body.name}%`); }
  if (body.status !== undefined && body.status !== '') { wheres.push('status=?'); params.push(Number(body.status)); }
  const where = wheres.length ? ' WHERE ' + wheres.join(' AND ') : '';
  const total = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_commodity${where}`, ...params);
  const count = total ? Number(total.n) : 0;
  const rows = await dbRows(env,
    `SELECT c.*, (SELECT COUNT(*) FROM acg_card k WHERE k.commodity_id=c.id AND k.status=0) AS card_count,
            (SELECT COUNT(*) FROM acg_card k WHERE k.commodity_id=c.id AND k.status=1) AS card_success_count,
            (SELECT COALESCE(SUM(amount-COALESCE(pay_cost,0)),0) FROM acg_order o WHERE o.commodity_id=c.id AND o.status=1) AS order_all_amount
     FROM acg_commodity c${where} ORDER BY c.sort ASC, c.id ASC LIMIT ? OFFSET ?`,
    ...params, pageSize, (page - 1) * pageSize);
  const list = [];
  for (const r of rows) {
    list.push({
      ...r,
      category: r.category_id ? (await dbFirst(env, 'SELECT id, name FROM acg_category WHERE id=?', r.category_id)) || null : null,
      shared: null, owner: { id: 0, username: '站长', avatar: null },
    });
  }
  return apiOk('success', { list, page, limit: pageSize, count, records: count });
}

export async function commoditySave(env, request, url, body = {}, manage) {
  const id = Number(body.id) || 0;
  const allowed = ['category_id', 'name', 'description', 'cover', 'factory_price', 'price', 'user_price', 'status', 'api_status', 'delivery_way', 'delivery_auto_mode', 'delivery_message', 'contact_type', 'password_status', 'sort', 'coupon', 'seckill_status', 'seckill_start_time', 'seckill_end_time', 'draft_status', 'draft_premium', 'inventory_hidden', 'leave_message', 'recommend', 'send_email', 'only_user', 'purchase_count', 'minimum', 'maximum', 'hide', 'code', 'widget', 'tags', 'level_price', 'level_disable', 'config'];
  const map = {};
  for (const k of allowed) if (Object.prototype.hasOwnProperty.call(body, k)) map[k] = body[k];

  if (id > 0) {
    const cur = await dbFirst(env, 'SELECT * FROM acg_commodity WHERE id=?', id);
    if (!cur) return apiErr('商品不存在');
  }
  if (id === 0) {
    if (!map.name || String(map.name).trim() === '') return apiErr('商品名称不能为空');
    if (!map.category_id || Number(map.category_id) <= 0) return apiErr('请选择商品分类');
    if (map.price === undefined || Number(map.price) < 0 || (map.user_price !== undefined && Number(map.user_price) < 0)) return apiErr('商品单价不能低于0');
  }
  // delivery_way 自动发货必须有卡密字段
  const deliveryWay = Number(map.delivery_way) || 0;

  const data = {};
  for (const k of ['category_id', 'name', 'description', 'cover', 'delivery_message', 'leave_message', 'widget', 'tags', 'level_price', 'code', 'config']) {
    if (Object.prototype.hasOwnProperty.call(map, k)) data[k] = k === 'category_id' ? Number(map[k]) : String(map[k]);
  }
  for (const k of ['status', 'api_status', 'delivery_way', 'delivery_auto_mode', 'contact_type', 'password_status', 'sort', 'coupon', 'seckill_status', 'seckill_start_time', 'seckill_end_time', 'draft_status', 'inventory_hidden', 'recommend', 'send_email', 'only_user', 'purchase_count', 'minimum', 'maximum', 'hide', 'level_disable']) {
    if (Object.prototype.hasOwnProperty.call(map, k)) data[k] = Number(map[k]) || 0;
  }
  for (const k of ['factory_price', 'price', 'user_price', 'draft_premium']) {
    if (Object.prototype.hasOwnProperty.call(map, k)) data[k] = Number(map[k] || 0);
  }
  if (Object.prototype.hasOwnProperty.call(map, 'code') && !data.code) data.code = md5hex(now() + Math.random()).slice(0, 16).toUpperCase();

  let newId = 0;
  if (id > 0) {
    await dbUpdate(env, 'acg_commodity', data, 'id=?', id);
  } else {
    if (!data.code) data.code = md5hex(now() + Math.random()).slice(0, 16).toUpperCase();
    await dbInsert(env, 'acg_commodity', { ...data, owner: 0, create_time: now(), stock: Number(body.stock) || 0, status: data.status ?? 1, sort: data.sort ?? 0 }).then(id => newId = id);
  }
  try { await dbInsert(env, 'acg_manage_log', { email: manage.email, nickname: '', content: '[新增/修改]商品', create_time: now(), create_ip: requestInfo(request).ip, ua: '', risk: 0 }); } catch (e) {}
  return apiOk('（＾∀＾）保存成功', { id: newId || id });
}

export async function commodityStatus(env, request, url, body = {}) {
  let list;
  try { list = intList(body.list, '商品ID'); } catch (e) { return apiErr(e.message); }
  if (!list.length || !['0', '1'].includes(String(body.status))) return apiErr('商品状态请求参数不正确');
  await dbRun(env, `UPDATE acg_commodity SET status=? WHERE id IN (${list.map(() => '?').join(',')})`, Number(body.status), ...list);
  return apiOk('商品状态已经更新');
}

export async function commodityDel(env, request, url, body = {}) {
  let list;
  try { list = intList(body.list, '商品ID'); } catch (e) { return apiErr(e.message); }
  if (!list.length) return apiErr('你还没有选择商品');
  await dbRun(env, `DELETE FROM acg_card WHERE commodity_id IN (${list.map(() => '?').join(',')})`, ...list);
  await dbRun(env, `UPDATE acg_order SET card_id=NULL, secret=NULL WHERE commodity_id IN (${list.map(() => '?').join(',')})`, ...list);
  await dbRun(env, `DELETE FROM acg_commodity WHERE id IN (${list.map(() => '?').join(',')})`, ...list);
  return apiOk('（＾∀＾）移除成功');
}

// ---------- 卡密管理 (对齐 Admin/Api/Card) ----------
export async function cardData(env, request, url, body = {}) {
  const page = Math.max(1, Number(body.page) || 1);
  const pageSize = Math.min(100, Math.max(1, Number(body.limit) || 10));
  const wheres = [];
  const params = [];
  if (body.commodity_id) { wheres.push('commodity_id=?'); params.push(Number(body.commodity_id)); }
  if (body.status !== undefined && body.status !== '') { wheres.push('status=?'); params.push(Number(body.status)); }
  if (body.secret) { wheres.push('secret LIKE ?'); params.push(`%${body.secret}%`); }
  const where = wheres.length ? ' WHERE ' + wheres.join(' AND ') : '';
  const total = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_card${where}`, ...params);
  const count = total ? Number(total.n) : 0;
  const rows = await dbRows(env, `SELECT * FROM acg_card${where} ORDER BY id DESC LIMIT ? OFFSET ?`, ...params, pageSize, (page - 1) * pageSize);
const list = [];
  for (const r of rows) {
    list.push({
      ...r,
      commodity: r.commodity_id ? (await dbFirst(env, 'SELECT id, name, cover FROM acg_commodity WHERE id=?', r.commodity_id)) || null : null,
      order: r.order_id ? (await dbFirst(env, 'SELECT id, trade_no FROM acg_order WHERE id=?', r.order_id)) || null : null,
    });
  }
  return apiOk('success', { list, page, limit: pageSize, count, records: count });
}

export async function cardSave(env, request, url, body = {}, manage) {
  // 批量新增卡密: secret 支持多行/逗号
  const commodityId = Number(body.commodity_id) || 0;
  if (commodityId <= 0) return apiErr('请选择商品');
  const commodity = await dbFirst(env, 'SELECT * FROM acg_commodity WHERE id=?', commodityId);
  if (!commodity) return apiErr('商品不存在');
  const secrets = String(body.secret || '')
    .split(/\r?\n|[,，]/).map(s => s.trim()).filter(Boolean);
  if (!secrets.length) return apiErr('卡密不能为空');
  if (secrets.length > 5000) return apiErr('单次最多导入 5000 条卡密');
  const t = now();
  const ids = [];
  for (const secret of secrets.slice(0, 5000)) {
    const cid = await dbInsert(env, 'acg_card', {
      owner: 0, commodity_id: commodityId, draft: 0, secret, create_time: t,
      status: 0, cost: Number(body.cost) || 0, race: body.race || null, sku: body.sku || null,
    });
    ids.push(cid);
  }
  try { await dbInsert(env, 'acg_manage_log', { email: manage.email, nickname: '', content: `[新增]卡密 ${secrets.length} 条`, create_time: now(), create_ip: requestInfo(request).ip, ua: '', risk: 0 }); } catch (e) {}
  return apiOk(`成功导入 ${secrets.length} 条卡密`, { num: secrets.length, id: ids[0] });
}

export async function cardEdit(env, request, url, body = {}) {
  const id = Number(body.id) || 0;
  const cur = await dbFirst(env, 'SELECT * FROM acg_card WHERE id=?', id);
  if (!cur) return apiErr('卡密不存在');
  const data = {};
  if (Object.prototype.hasOwnProperty.call(body, 'note')) data.note = String(body.note || '');
  if (Object.prototype.hasOwnProperty.call(body, 'cost')) data.cost = Number(body.cost) || 0;
  if (Object.prototype.hasOwnProperty.call(body, 'secret') && String(body.secret).trim()) data.secret = String(body.secret).trim();
  if (Object.keys(data).length) await dbUpdate(env, 'acg_card', data, 'id=?', id);
  return apiOk('保存成功');
}

export async function cardLock(env, request, url, body = {}, lock = true) {
  let list;
  try { list = intList(body.list, '卡密ID'); } catch (e) { return apiErr(e.message); }
  if (!list.length) return apiErr('你还没有选择卡密');
  await dbRun(env, `UPDATE acg_card SET status=? WHERE id IN (${list.map(() => '?').join(',')}) AND status=0`, lock ? 2 : 0, ...list);
  return apiOk(lock ? '锁定成功' : '解锁成功');
}

export async function cardDel(env, request, url, body = {}) {
  let list;
  try { list = intList(body.list, '卡密ID'); } catch (e) { return apiErr(e.message); }
  if (!list.length) return apiErr('你还没有选择卡密');
  await dbRun(env, `DELETE FROM acg_card WHERE id IN (${list.map(() => '?').join(',')}) AND status=0`, ...list);
  return apiOk('（＾∀＾）移除成功');
}

// ============================================================
// 订单管理 (对齐 Admin/Api/Order)
// ============================================================
export async function orderData(env, request, url, body = {}) {
  const page = Math.max(1, Number(body.page) || 1);
  const pageSize = Math.min(100, Math.max(1, Number(body.limit) || 10));
  const wheres = [];
  const params = [];
  // 支持原版 SearchFilter 风格: equal-xxx / search-xxx / betweenStart-xxx / betweenEnd-xxx
  const addEqual = (key, col) => { if (body[key] !== undefined && body[key] !== '') { wheres.push(`${col}=?`); params.push(body[key]); } };
  addEqual('equal-trade_no', 'trade_no');
  addEqual('equal-status', 'status');
  addEqual('equal-delivery_status', 'delivery_status');
  addEqual('equal-commodity_id', 'commodity_id');
  addEqual('equal-create_device', 'create_device');
  addEqual('equal-pay_id', 'pay_id');
  addEqual('equal-owner', 'owner');
  if (body['search-secret'] !== undefined && String(body['search-secret']).trim() !== '') {
    wheres.push('secret LIKE ?'); params.push(`%${String(body['search-secret']).trim()}%`);
  }
  if (body['equal-contact'] !== undefined && String(body['equal-contact']).trim() !== '') {
    wheres.push('contact=?'); params.push(String(body['equal-contact']).trim());
  }
  const start = body['betweenStart-create_time'], end = body['betweenEnd-create_time'];
  if (start) { wheres.push('create_time>=?'); params.push(Number(start)); }
  if (end) { wheres.push('create_time<=?'); params.push(Number(end)); }
  const where = wheres.length ? ' WHERE ' + wheres.join(' AND ') : '';
  const total = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_order${where}`, ...params);
  const count = total ? Number(total.n) : 0;
  const sumRow = await dbFirst(env, `SELECT COALESCE(SUM(amount),0) AS total_amount, COALESCE(SUM(pay_cost),0) AS total_cost FROM acg_order${where}`, ...params);
  const rows = await dbRows(env, `SELECT * FROM acg_order${where} ORDER BY id DESC LIMIT ? OFFSET ?`, ...params, pageSize, (page - 1) * pageSize);
  const list = [];
  for (const r of rows) {
    list.push({
      ...r,
      coupon: r.coupon_id ? (await dbFirst(env, 'SELECT id, code FROM acg_coupon WHERE id=?', r.coupon_id)) || null : null,
      owner: r.owner ? (await dbFirst(env, 'SELECT id, username, avatar, recharge FROM acg_user WHERE id=?', r.owner)) || null : null,
      user: r.user_id ? (await dbFirst(env, 'SELECT id, username, avatar, recharge FROM acg_user WHERE id=?', r.user_id)) || null : null,
      promote: r.from ? (await dbFirst(env, 'SELECT id, username, avatar, recharge FROM acg_user WHERE id=?', r.from)) || null : null,
      commodity: r.commodity_id ? (await dbFirst(env, 'SELECT id, name, cover, price, delivery_way, contact_type FROM acg_commodity WHERE id=?', r.commodity_id)) || null : null,
      pay: r.pay_id ? (await dbFirst(env, 'SELECT id, name, icon FROM acg_pay WHERE id=?', r.pay_id)) || null : null,
      substationUser: r.substation_user_id ? (await dbFirst(env, 'SELECT id, username, avatar, recharge FROM acg_user WHERE id=?', r.substation_user_id)) || null : null,
      card: r.card_id ? (await dbFirst(env, 'SELECT id, secret, draft, status FROM acg_card WHERE id=?', r.card_id)) || null : null,
    });
  }
  return apiOk('success', { list, page, limit: pageSize, count, records: count, order_amount: (sumRow && sumRow.total_amount) ?? 0, order_cost: (sumRow && sumRow.total_cost) ?? 0 });
}

// 手动发货(仅已支付 + 手工发货商品)
export async function orderSave(env, request, url, body = {}) {
  const id = Number(body.id) || 0;
  const secret = String(body.secret || '').trim();
  if (id < 1) return apiErr('订单 ID 不正确，请刷新后重试');
  if (secret === '' || secret === '0') return apiErr('请填写有效的发货内容，不能仅为空白或"0"');
  const overwriteConfirmed = body.overwrite_confirmed === true || body.overwrite_confirmed === 'true' || body.overwrite_confirmed === '1';
  const order = await dbFirst(env, 'SELECT * FROM acg_order WHERE id=?', id);
  if (!order) return apiErr('订单不存在，请刷新后重试');
  if (Number(order.status) !== 1) return apiErr('仅已支付订单可以手动发货');
  const commodity = await dbFirst(env, 'SELECT id, delivery_way FROM acg_commodity WHERE id=?', order.commodity_id);
  if (!commodity) return apiErr('订单对应商品不存在，无法手动发货');
  if (Number(commodity.delivery_way) !== 1) return apiErr('该订单不是手动发货商品，不能修改发货内容');
  const hasExisting = Number(order.delivery_status) === 1 || String(order.secret || '').trim() !== '';
  if (hasExisting && !overwriteConfirmed) return apiErr('此订单已有发货记录，请明确确认覆盖后重试');
  await dbRun(env, 'UPDATE acg_order SET secret=?, delivery_status=1 WHERE id=?', secret, id);
  try { await dbInsert(env, 'acg_manage_log', { email: 'admin', nickname: '', content: `[手动发货](${id})修改了发货信息`, create_time: now(), create_ip: requestInfo(request).ip, ua: '', risk: 0 }); } catch (e) {}
  return apiOk('（＾∀＾）发货成功');
}

// 一键清理 30 分钟前未支付订单
export async function orderClear(env, request, url, body = {}) {
  const cutoff = now() - 1800;
  const r = await dbRun(env, 'DELETE FROM acg_order WHERE create_time<? AND status=0', cutoff);
  try { await dbInsert(env, 'acg_manage_log', { email: 'admin', nickname: '', content: '进行了一键清理无用商品订单操作', create_time: now(), create_ip: requestInfo(request).ip, ua: '', risk: 0 }); } catch (e) {}
  return apiOk('（＾∀＾）清理完成');
}

const ORDER_EXPORT_TTL = 180;
const ORDER_EXPORT_MAX = 5000;
async function orderExportTokenKey(manage) {
  return sha256hex(`order-export-preview-v1|${manage.password}`);
}
async function orderExportFingerprint(ids) { return sha256hex(ids.join(',')); }
async function orderIssueExportToken(manage, ids, options) {
  const iat = Math.floor(Date.now() / 1000);
  const exp = iat + ORDER_EXPORT_TTL;
  const payload = { fingerprint: await orderExportFingerprint(ids), count: ids.length, export_num: options.export_num, export_status: options.export_status, manage_id: Number(manage.id) || 0, session: await sha256hex(manage.sid || ''), iat, exp };
  const body = b64urlEncode(JSON.stringify(payload));
  const sig = await sha256hex(`${body}|${await orderExportTokenKey(manage)}`);
  return body + '.' + sig;
}
async function orderVerifyExportToken(manage, token, ids, options) {
  if (typeof token !== 'string' || !token.includes('.')) throw new Error('请先预览并确认订单导出范围');
  const [body, sig] = token.split('.');
  const expected = await sha256hex(`${body}|${await orderExportTokenKey(manage)}`);
  const nowSec = Math.floor(Date.now() / 1000);
  let payload = null;
  try {
    let b64 = String(body).replace(/-/g, '+').replace(/_/g, '/');
    while (b64.length % 4) b64 += '=';
    payload = JSON.parse(decodeURIComponent(escape(atob(b64))));
  } catch (e) { throw new Error('订单导出预览凭证无效，请重新预览'); }
  if (sig !== expected || !payload) throw new Error('订单导出预览凭证无效，请重新预览');
  if (Number(payload.exp) < nowSec || Number(payload.exp) > Number(payload.iat) + ORDER_EXPORT_TTL) throw new Error('订单导出预览已过期，请重新预览');
  if (Number(payload.manage_id) !== Number(manage.id) || payload.session !== await sha256hex(manage.sid || '')) throw new Error('订单导出预览凭证无效，请重新预览');
  if (Number(payload.count) !== ids.length || payload.fingerprint !== await orderExportFingerprint(ids)) throw new Error('订单导出范围或数据已变化，请重新预览');
  if (Number(payload.export_num) !== Number(options.export_num) || Number(payload.export_status) !== Number(options.export_status)) throw new Error('订单导出范围或数据已变化，请重新预览');
}

// 导出预览(只读影响) — 返回 count/total/paid/delivered + preview_token
export async function orderExportImpact(env, request, url, body = {}, manage) {
  const exportNum = body.export_num === '' || body.export_num === null ? 0 : Number(body.export_num);
  if (!Number.isInteger(exportNum) || exportNum < 0 || exportNum > ORDER_EXPORT_MAX) throw new Error(`导出数量必须是 0 到 ${ORDER_EXPORT_MAX} 的整数`);
  const exportStatus = Number(body.export_status) || 0;
  if (![0, 1].includes(exportStatus)) throw new Error('导出后操作不正确');
  // 构造与 data 一致的筛选范围
  const { rows } = await orderExportSelection(env, body, exportNum);
  const ids = rows.map(r => Number(r.id));
  if (!ids.length) throw new Error('当前筛选没有可导出的订单');
  const paidCount = rows.filter(r => Number(r.status) === 1).length;
  const deliveredCount = rows.filter(r => Number(r.delivery_status) === 1).length;
  return apiOk('success', {
    count: ids.length,
    total: ids.length,
    has_filter: true,
    paid_count: paidCount,
    unpaid_count: rows.length - paidCount,
    delivered_count: deliveredCount,
    undelivered_count: rows.length - deliveredCount,
    export_status: exportStatus,
    preview_token: await orderIssueExportToken(manage, ids, { export_num: exportNum, export_status: exportStatus }),
    expires_in: ORDER_EXPORT_TTL,
    max_count: ORDER_EXPORT_MAX,
  });
}

async function orderExportSelection(env, body, exportNum) {
  const wheres = [];
  const params = [];
  const addEqual = (key, col) => { if (body[key] !== undefined && body[key] !== '') { wheres.push(`${col}=?`); params.push(body[key]); } };
  addEqual('equal-trade_no', 'trade_no');
  addEqual('equal-status', 'status');
  addEqual('equal-delivery_status', 'delivery_status');
  addEqual('equal-commodity_id', 'commodity_id');
  addEqual('equal-create_device', 'create_device');
  addEqual('equal-pay_id', 'pay_id');
  addEqual('equal-owner', 'owner');
  if (body['search-secret'] !== undefined && String(body['search-secret']).trim() !== '') { wheres.push('secret LIKE ?'); params.push(`%${String(body['search-secret']).trim()}%`); }
  if (body['equal-contact'] !== undefined && String(body['equal-contact']).trim() !== '') { wheres.push('contact=?'); params.push(String(body['equal-contact']).trim()); }
  const start = body['betweenStart-create_time'], end = body['betweenEnd-create_time'];
  if (start) { wheres.push('create_time>=?'); params.push(Number(start)); }
  if (end) { wheres.push('create_time<=?'); params.push(Number(end)); }
  const where = wheres.length ? ' WHERE ' + wheres.join(' AND ') : '';
  const total = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_order${where}`, ...params);
  const count = total ? Number(total.n) : 0;
  if (count === 0) throw new Error('当前筛选没有可导出的订单');
  const limit = exportNum > 0 ? Math.min(exportNum, count) : count;
  if (limit > ORDER_EXPORT_MAX) throw new Error(`当前范围过大，请增加筛选或填写不超过 ${ORDER_EXPORT_MAX} 的导出数量`);
  const rows = await dbRows(env, `SELECT * FROM acg_order${where} ORDER BY id DESC LIMIT ?`, ...params, limit);
  return { rows, count, limit };
}

// 导出 CSV(可选删除)
export async function orderExport(env, request, url, body = {}, manage) {
  const exportNum = body.export_num === '' || body.export_num === null ? 0 : Number(body.export_num);
  const exportStatus = Number(body.export_status) || 0;
  const expectedCount = Number(body.expected_count);
  if (!Number.isInteger(expectedCount) || expectedCount < 1 || expectedCount > ORDER_EXPORT_MAX) throw new Error('请先预览并确认本次订单导出数量');
  const { rows, count, limit } = await orderExportSelection(env, body, exportNum);
  const ids = rows.map(r => Number(r.id));
  if (count !== expectedCount) throw new Error('订单数量已变化，请重新预览导出范围');
  await orderVerifyExportToken(manage, body.preview_token, ids, { export_num: exportNum, export_status: exportStatus });
  if (exportStatus === 1) {
    const required = '确认永久删除' + count + '笔订单';
    if (String(body.delete_confirmation || '').trim() !== required) throw new Error('请完成高危确认后再导出并删除订单');
  }
  // 生成 CSV
  const lines = [];
  const header = ['订单号', '金额', '商品名称', '数量', '支付方式', '下单时间', '下单IP', '下单设备', '支付时间', '订单状态', '联系方式', '发货状态', '优惠券', '客户', '推广人', '分站', '分站手续费', '接口手续费', '推广分成', '返利'];
  const esc = (v) => { const s = String(v ?? ''); return /[",\n\r]/.test(s) ? '"' + s.replace(/"/g, '""') + '"' : s; };
  lines.push(header.map(esc).join(','));
  for (const r of rows) {
    const deviceText = [0, 1, 2, 3].includes(Number(r.create_device)) ? ['PC', '安卓', 'IOS', 'iPad'][Number(r.create_device)] : 'PC';
    const statusText = Number(r.status) === 0 ? '未支付' : Number(r.status) === 1 ? '已支付' : '未知';
    const deliveryText = Number(r.delivery_status) === 0 ? '未发货' : Number(r.delivery_status) === 1 ? '已发货' : '未知';
    lines.push([r.trade_no, r.amount, '', r.card_num || 0, '', r.create_time || '', r.create_ip || '', deviceText, r.pay_time || '', statusText, r.contact || '', deliveryText, '', '', '', '', r.cost || 0, r.pay_cost || 0, r.divide_amount || 0, r.rebate || 0].map(esc).join(','));
  }
  const csv = '\uFEFF' + lines.join('\r\n');
  if (exportStatus === 1) {
    await dbRun(env, `DELETE FROM acg_order WHERE id IN (${ids.map(() => '?').join(',')})`, ...ids);
  }
  try { await dbInsert(env, 'acg_manage_log', { email: 'admin', nickname: '', content: `[订单导出]导出并${exportStatus === 1 ? '永久删除' : ''}订单，共计：${count}`, create_time: now(), create_ip: requestInfo(request).ip, ua: '', risk: 0 }); } catch (e) {}
  return new Response(csv, {
    status: 200,
    headers: {
      'Content-Type': 'text/csv; charset=UTF-8',
      'Content-Disposition': `attachment; filename="order-export-${Date.now()}.csv"`,
      'Cache-Control': 'no-cache',
    },
  });
}

// ============================================================
// 用户管理 (对齐 Admin/Api/User)
// ============================================================
export async function userData(env, request, url, body = {}) {
  const page = Math.max(1, Number(body.page) || 1);
  const pageSize = Math.min(100, Math.max(1, Number(body.limit) || 10));
  const wheres = [];
  const params = [];
  for (const [key, col] of [['equal-id', 'id'], ['equal-status', 'status'], ['equal-pid', 'pid'], ['equal-email', 'email'], ['equal-phone', 'phone'], ['equal-qq', 'qq'], ['equal-login_ip', 'login_ip']]) {
    if (body[key] !== undefined && body[key] !== '') { wheres.push(`${col}=?`); params.push(String(body[key])); }
  }
  if (body['search-username'] !== undefined && String(body['search-username']).trim() !== '') { wheres.push('username LIKE ?'); params.push(`%${String(body['search-username']).trim()}%`); }
  if (body['search-email'] !== undefined && String(body['search-email']).trim() !== '') { wheres.push('email LIKE ?'); params.push(`%${String(body['search-email']).trim()}%`); }
  if (body['search-phone'] !== undefined && String(body['search-phone']).trim() !== '') { wheres.push('phone LIKE ?'); params.push(`%${String(body['search-phone']).trim()}%`); }
  const where = wheres.length ? ' WHERE ' + wheres.join(' AND ') : '';
  const total = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_user${where}`, ...params);
  const count = total ? Number(total.n) : 0;
  const sumRow = await dbFirst(env, `SELECT COALESCE(SUM(balance),0) AS balance, COALESCE(SUM(recharge),0) AS recharge FROM acg_user${where}`, ...params);
  const rows = await dbRows(env, `SELECT * FROM acg_user${where} ORDER BY id DESC LIMIT ? OFFSET ?`, ...params, pageSize, (page - 1) * pageSize);
  const list = [];
  for (const r of rows) {
    list.push({ ...r, password: undefined, salt: undefined, app_key: undefined, parent: r.pid ? (await dbFirst(env, 'SELECT id, username, avatar FROM acg_user WHERE id=?', r.pid)) || null : null, group: null, businessLevel: null, business: null });
  }
  return apiOk('success', { list, page, limit: pageSize, count, records: count, balance: (sumRow && sumRow.balance) ?? 0, recharge: (sumRow && sumRow.recharge) ?? 0 });
}

// 修改会员资料(站长专属语义简化: 允许改资料/密码/状态/上级)
export async function userSave(env, request, url, body = {}) {
  const id = Number(body.id) || 0;
  if (id <= 0) return apiErr('该用户不存在');
  const user = await dbFirst(env, 'SELECT * FROM acg_user WHERE id=?', id);
  if (!user) return apiErr('该用户不存在');
  const data = {};
  for (const f of ['avatar', 'username', 'email', 'phone', 'qq', 'status', 'pid']) {
    if (body[f] !== undefined) {
      if (f === 'status') { const st = Number(body[f]); if (![0, 1].includes(st)) return apiErr('会员状态不正确'); data.status = st; }
      else if (f === 'pid') { const p = Number(body[f]) || 0; if (p < 0 || p === id) return apiErr('上级会员 ID 不正确'); data.pid = p; }
      else data[f] = String(body[f]);
    }
  }
  if (body.password !== undefined && String(body.password).trim() !== '') {
    const pw = String(body.password);
    if (pw.length < 6) return apiErr('密码必须6位以上');
    data.password = generatePassword(pw, user.salt);
  }
  if (Object.keys(data).length) await dbUpdate(env, 'acg_user', data, 'id=?', id);
  try { await dbInsert(env, 'acg_manage_log', { email: 'admin', nickname: '', content: `修改了会员(${user.username})的信息。`, create_time: now(), create_ip: requestInfo(request).ip, ua: '', risk: 0 }); } catch (e) {}
  return apiOk('（＾∀＾）保存成功');
}

// 余额变动 (currency 0=余额 1=硬币; 对齐 changeAccountBalance)
export async function userRecharge(env, request, url, body = {}, manage, currency = 0) {
  const id = Number(body.id) || 0;
  const action = Number(body.action);
  if (id < 1) return apiErr('用户不存在');
  if (![1, 2].includes(action)) return apiErr('请选择增加或扣减');
  const amount = String(body.amount || '').trim();
  if (!/^\d+(?:\.\d{1,2})?$/.test(amount)) return apiErr('请输入大于 0 且最多两位小数的操作数量');
  const amt = Number(amount);
  if (!isFinite(amt) || amt <= 0 || amt > 99999999.99) return apiErr('操作数量必须大于 0 且不超过 99999999.99');
  const log = String(body.log || '').trim();
  if (log.length < 2 || log.length > 64) return apiErr('操作原因须为 2–64 个字');
  const total = Number(body.total) === 1;
  const user = await dbFirst(env, 'SELECT * FROM acg_user WHERE id=?', id);
  if (!user) return apiErr('用户不存在');
  // user.status 1=正常; 直接按 id 更新
  const delta = action === 1 ? amt : -amt;
  const col = currency === 1 ? 'coin' : 'balance';
  const cur = Number(user[col] || 0);
  const next = Math.round((cur + delta) * 100) / 100;
  if (next < 0) return apiErr('用户余额不足，无法操作');
  await dbRun(env, `UPDATE acg_user SET ${col}=? WHERE id=?`, next, id);
  if (total) {
    if (action === 1) {
      if (currency === 1) await dbRun(env, 'UPDATE acg_user SET total_coin=total_coin+? WHERE id=?', amt, id);
      else await dbRun(env, 'UPDATE acg_user SET recharge=recharge+? WHERE id=?', amt, id);
    }
  }
  const billLog = currency === 1 ? `管理员操作硬币:${log}` : `管理员操作余额:${log}`;
  await dbInsert(env, 'acg_bill', { owner: id, amount: amt, balance: next, type: action, currency, log: billLog, create_time: now() });
  try { await dbInsert(env, 'acg_manage_log', { email: 'admin', nickname: '', content: `为会员(${user.username})进行了${currency === 1 ? '硬币' : '余额'}变动操作`, create_time: now(), create_ip: requestInfo(request).ip, ua: '', risk: 0 }); } catch (e) {}
  return apiOk('操作成功');
}

// 会员交易统计
export async function userStatistics(env, request, url, body = {}) {
  const id = Number(url.searchParams.get('id')) || Number(body.id) || 0;
  if (id < 1) return apiErr('用户不存在');
  const startOfDay = (d) => d.setHours(0, 0, 0, 0) / 1000;
  const endOfDay = (d) => d.setHours(23, 59, 59, 999) / 1000;
  const nowD = new Date();
  const todayStart = startOfDay(new Date(nowD)), todayEnd = endOfDay(new Date(nowD));
  const yStart = startOfDay(new Date(nowD.getTime() - 86400000)), yEnd = endOfDay(new Date(nowD.getTime() - 86400000));
  const wStart = startOfDay(new Date(nowD.getTime() - (nowD.getDay() || 7 - 7) * 86400000));
  const wEnd = endOfDay(new Date(nowD));
  const mStart = startOfDay(new Date(nowD.getFullYear(), nowD.getMonth(), 1));
  const mEnd = endOfDay(new Date(nowD.getFullYear(), nowD.getMonth() + 1, 0));
  const rangeSum = async (a, b) => { const r = await dbFirst(env, 'SELECT COALESCE(SUM(amount),0) AS s FROM acg_order WHERE user_id=? AND status=1 AND create_time>=? AND create_time<?', id, a, b); return (r && r.s) || 0; };
  const data = {
    today_order_amount: (await rangeSum(todayStart, todayEnd)).toFixed(2),
    yesterday_order_amount: (await rangeSum(yStart, yEnd)).toFixed(2),
    week_order_amount: (await rangeSum(wStart, wEnd)).toFixed(2),
    month_order_amount: (await rangeSum(mStart, mEnd)).toFixed(2),
  };
  data.total_order_amount = (await rangeSum(0, 9999999999)).toFixed(2);
  return apiOk('success', data);
}

// 删除会员
export async function userDel(env, request, url, body = {}) {
  let list;
  try { list = intList(body.list, '会员ID'); } catch (e) { return apiErr(e.message); }
  if (!list.length) return apiErr('请选择要删除的会员');
  if (list.length > 1000) return apiErr('单次只能删除 1–1000 名有效会员');
  const where = `id IN (${list.map(() => '?').join(',')})`;
  await dbRun(env, `DELETE FROM acg_user WHERE ${where}`, ...list);
  await dbRun(env, 'DELETE FROM acg_business WHERE user_id IN (' + list.map(() => '?').join(',') + ')', ...list);
  try { await dbInsert(env, 'acg_manage_log', { email: 'admin', nickname: '', content: `删除了会员，共计删除：${list.length}`, create_time: now(), create_ip: requestInfo(request).ip, ua: '', risk: 0 }); } catch (e) {}
  return apiOk('（＾∀＾）移除成功', { count: list.length });
}

// ============================================================
// 充值订单管理 (对齐 Admin/Api/RechargeOrder)
// ============================================================
export async function rechargeData(env, request, url, body = {}) {
  const page = Math.max(1, Number(body.page) || 1);
  const pageSize = Math.min(100, Math.max(1, Number(body.limit) || 10));
  const wheres = [];
  const params = [];
  if (body['equal-status'] !== undefined && body['equal-status'] !== '') { wheres.push('status=?'); params.push(Number(body['equal-status'])); }
  if (body['equal-user_id'] !== undefined && body['equal-user_id'] !== '') { wheres.push('user_id=?'); params.push(Number(body['equal-user_id'])); }
  if (body['equal-trade_no'] !== undefined && body['equal-trade_no'] !== '') { wheres.push('trade_no=?'); params.push(String(body['equal-trade_no'])); }
  if (body['equal-create_ip'] !== undefined && body['equal-create_ip'] !== '') { wheres.push('create_ip=?'); params.push(String(body['equal-create_ip'])); }
  if (body['search-trade_no'] !== undefined && String(body['search-trade_no']).trim() !== '') { wheres.push('trade_no LIKE ?'); params.push(`%${String(body['search-trade_no']).trim()}%`); }
  if (body['equal-pay_id'] !== undefined && body['equal-pay_id'] !== '') { wheres.push('pay_id=?'); params.push(Number(body['equal-pay_id'])); }
  const where = wheres.length ? ' WHERE ' + wheres.join(' AND ') : '';
  const total = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_user_recharge${where}`, ...params);
  const count = total ? Number(total.n) : 0;
  const sumRow = await dbFirst(env, `SELECT COALESCE(SUM(amount),0) AS order_amount FROM acg_user_recharge${where}`, ...params);
  const rows = await dbRows(env, `SELECT * FROM acg_user_recharge${where} ORDER BY id DESC LIMIT ? OFFSET ?`, ...params, pageSize, (page - 1) * pageSize);
  const list = [];
  for (const r of rows) {
    list.push({
      ...r,
      user: r.user_id ? (await dbFirst(env, 'SELECT id, username, avatar FROM acg_user WHERE id=?', r.user_id)) || null : null,
      pay: r.pay_id ? (await dbFirst(env, 'SELECT id, name, icon FROM acg_pay WHERE id=?', r.pay_id)) || null : null,
    });
  }
  return apiOk('success', { list, page, limit: pageSize, count, records: count, order_amount: (sumRow && sumRow.order_amount) ?? 0 });
}

// 手动补单(将 status 0 的充值订单标记为已支付并加余额)
export async function rechargeSuccess(env, request, url, body = {}) {
  const id = Number(body.id) || 0;
  if (id < 1) return apiErr('订单编号不正确');
  const order = await dbFirst(env, 'SELECT * FROM acg_user_recharge WHERE id=?', id);
  if (!order) return apiErr('订单不存在');
  if (Number(order.status) !== 0) return apiErr('该订单已支付，无法再次补单');
  const user = await dbFirst(env, 'SELECT id, balance FROM acg_user WHERE id=?', order.user_id);
  if (!user) return apiErr('订单会员不存在，无法补单');
  const bal = Math.round((Number(user.balance) + Number(order.amount)) * 100) / 100;
  await dbRun(env, 'UPDATE acg_user SET balance=?, recharge=recharge+? WHERE id=?', bal, order.amount, order.user_id);
  await dbRun(env, 'UPDATE acg_user_recharge SET status=1, pay_time=? WHERE id=?', now(), id);
  await dbInsert(env, 'acg_bill', { owner: order.user_id, amount: Number(order.amount), balance: bal, type: 1, currency: 0, log: `充值成功[${order.trade_no}]`, create_time: now() });
  try { await dbInsert(env, 'acg_manage_log', { email: 'admin', nickname: '', content: `充值订单手动补单，订单号：${order.trade_no}`, create_time: now(), create_ip: requestInfo(request).ip, ua: '', risk: 0 }); } catch (e) {}
  return apiOk('已手动确认');
}

// 清理 30 分钟前未支付充值订单
export async function rechargeClear(env, request, url, body = {}) {
  const cutoff = now() - 1800;
  await dbRun(env, 'DELETE FROM acg_user_recharge WHERE create_time<? AND status=0', cutoff);
  try { await dbInsert(env, 'acg_manage_log', { email: 'admin', nickname: '', content: '进行了一键清理无用充值订单操作', create_time: now(), create_ip: requestInfo(request).ip, ua: '', risk: 0 }); } catch (e) {}
  return apiOk('（＾∀＾）清理完成');
}

// ============================================================
// 优惠券管理 (对齐 Admin/Api/Coupon: data/save/edit/lock/unlock/deleteImpact/del/exportImpact/export)
// ============================================================
const COUPON_MAX_BATCH = 500;
const COUPON_MAX_EXPORT = 5000;

function couponIds(value) {
  const arr = Array.isArray(value) ? value : String(value ?? '').split(',');
  const ids = [];
  for (const c of arr) {
    const id = typeof c === 'number' ? c : Number(String(c).trim());
    if (!Number.isInteger(id) || id <= 0) throw new Error('优惠卷 ID 必须是正整数');
    ids.push(id);
  }
  return [...new Set(ids)];
}

function couponCodeRandom() {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  let s = '';
  for (let i = 0; i < 16; i++) s += chars[Math.floor(Math.random() * chars.length)];
  return s;
}

async function couponDeleteImpact2(env, requestedIds, lock = false) {
  if (!requestedIds.length) throw new Error('请至少选择一张优惠卷');
  const ph = requestedIds.map(() => '?').join(',');
  const rows = await dbRows(env, `SELECT id, status, trade_no FROM acg_coupon WHERE id IN (${ph})`, ...requestedIds);
  if (rows.length !== requestedIds.length) throw new Error('部分优惠卷不存在，请刷新后重试');
  const ids = rows.map(r => Number(r.id));
  let normalCount = 0, usedCount = 0, lockedCount = 0, tradeNoCount = 0;
  for (const r of rows) {
    const st = Number(r.status);
    if (st === 0) normalCount++;
    else if (st === 1) usedCount++;
    else if (st === 2) lockedCount++;
    if (String(r.trade_no || '').trim() !== '') tradeNoCount++;
  }
  let orderReferenceCount = 0;
  if (ids.length) {
    const ref = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_order WHERE coupon_id IN (${ids.map(() => '?').join(',')})`, ...ids);
    orderReferenceCount = ref ? Number(ref.n) : 0;
  }
  return { coupon_ids: ids, coupon_count: ids.length, normal_count: normalCount, used_count: usedCount, locked_count: lockedCount, trade_no_count: tradeNoCount, order_reference_count: orderReferenceCount, can_delete: usedCount === 0 && tradeNoCount === 0 && orderReferenceCount === 0 };
}

export async function couponData(env, request, url, body = {}) {
  const page = Math.max(1, Number(body.page) || 1);
  const pageSize = Math.min(100, Math.max(1, Number(body.limit) || 10));
  const wheres = [];
  const params = [];
  for (const [key, col] of [['equal-code', 'code'], ['equal-note', 'note'], ['equal-money', 'money'], ['equal-owner', 'owner'], ['equal-category_id', 'category_id'], ['equal-commodity_id', 'commodity_id'], ['equal-status', 'status'], ['equal-race', 'race']]) {
    if (body[key] !== undefined && body[key] !== '') { wheres.push(`${col}=?`); params.push(String(body[key])); }
  }
  const where = wheres.length ? ' WHERE ' + wheres.join(' AND ') : '';
  const total = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_coupon${where}`, ...params);
  const count = total ? Number(total.n) : 0;
  const rows = await dbRows(env, `SELECT * FROM acg_coupon${where} ORDER BY id DESC LIMIT ? OFFSET ?`, ...params, pageSize, (page - 1) * pageSize);
  const list = [];
  for (const r of rows) {
    list.push({
      ...r,
      owner: r.owner ? (await dbFirst(env, 'SELECT id, username, avatar FROM acg_user WHERE id=?', r.owner)) || null : null,
      commodity: r.commodity_id ? (await dbFirst(env, 'SELECT id, name, cover FROM acg_commodity WHERE id=?', r.commodity_id)) || null : null,
      category: r.category_id ? (await dbFirst(env, 'SELECT id, name FROM acg_category WHERE id=?', r.category_id)) || null : null,
    });
  }
  return apiOk('success', { list, page, limit: pageSize, count, records: count, total: count });
}

// 批量生成优惠卷
export async function couponSave(env, request, url, body = {}, manage) {
  const prefix = String(body.prefix || '').trim().toUpperCase();
  const note = String(body.note || '').trim();
  const commodityId = Number(body.commodity_id) || 0;
  const categoryId = Number(body.category_id) || 0;
  const expireTime = String(body.expire_time || '').trim();
  const rawMoney = body.money;
  const num = Number(body.num) || 0;
  const life = Number(body.life) || 0;
  const mode = Number(body.mode) ?? -1;

  const money = Number(rawMoney);
  if (!isFinite(money) || money <= 0) throw new Error('ಠ_ಠ请输入优惠卷价格');
  if (![0, 1].includes(mode)) throw new Error('请选择正确的抵扣模式');
  if (mode === 1 && money > 1) throw new Error('百分比抵扣必须大于 0 且小于或等于 1');
  if (mode === 0 && money > 99999999.99) throw new Error('金额抵扣超出允许范围');
  if (prefix !== '' && !/^[A-Z0-9_-]{1,16}$/.test(prefix)) throw new Error('优惠卷前缀仅支持 1 到 16 位字母、数字、下划线或短横线');
  if (note.length > 32) throw new Error('备注信息最多 32 个字符');
  if (commodityId > 0 && categoryId > 0) throw new Error('商品和商品分类只能选择一个抵扣范围');
  if (expireTime !== '') {
    const ts = Date.parse(expireTime.replace('T', ' ').replace(/-/g, '/'));
    if (isNaN(ts) || Math.floor(ts / 1000) <= now()) throw new Error('ಠ_ಠ优惠卷的过期时间必须晚于当前时间');
  }
  if (num < 1 || num > 1000) throw new Error('每次只能生成 1 到 1000 张优惠卷');
  if (life < 1 || life > 1000000) throw new Error('可用次数必须是 1 到 1000000 之间的整数');
  if (categoryId > 0) {
    const cat = await dbFirst(env, 'SELECT id FROM acg_category WHERE id=? AND owner=0', categoryId);
    if (!cat) throw new Error('所选商品分类不存在');
  }
  if (commodityId > 0) {
    const com = await dbFirst(env, 'SELECT id FROM acg_commodity WHERE id=? AND owner=0', commodityId);
    if (!com) throw new Error('所选商品不存在');
  }
  const t = now();
  const expireTs = expireTime !== '' ? Math.floor(Date.parse(expireTime.replace('T', ' ').replace(/-/g, '/')) / 1000) : 0;
  let success = 0, error = 0;
  const codes = [];
  for (let i = 0; i < num; i++) {
    const code = prefix + couponCodeRandom();
    try {
      await dbInsert(env, 'acg_coupon', {
        code, commodity_id: commodityId, category_id: categoryId, owner: 0, create_time: t,
        ...(expireTs ? { expire_time: expireTs } : {}),
        money, status: 0, note, life, use_life: 0, mode, sku: body.sku && typeof body.sku === 'object' ? JSON.stringify(body.sku) : '',
        ...(String(body.race || '').trim() !== '' ? { race: String(body.race).trim() } : {}),
      });
      success++;
      codes.push(code);
    } catch (e2) { error++; }
  }
  try { await dbInsert(env, 'acg_manage_log', { email: 'admin', nickname: '', content: `[生成优惠卷]成功:${success}张，失败：${error}张`, create_time: now(), create_ip: requestInfo(request).ip, ua: '', risk: 0 }); } catch (e) {}
  return apiOk(`生成完毕，成功:${success}张，失败：${error}张`, { success, error, code: codes.join('\n') + (codes.length ? '\n' : '') });
}

// 修改单个优惠卷状态 (0/2)，已使用(1)不能改
export async function couponEdit(env, request, url, body = {}) {
  const id = Number(body.id) || 0;
  const status = Number(body.status) ?? -1;
  if (id <= 0 || ![0, 2].includes(status)) throw new Error('请求参数不正确');
  const coupon = await dbFirst(env, 'SELECT * FROM acg_coupon WHERE id=?', id);
  if (!coupon) throw new Error('优惠卷不存在');
  if (Number(coupon.status) === 1) throw new Error('已使用的优惠卷不能修改状态');
  await dbRun(env, 'UPDATE acg_coupon SET status=? WHERE id=?', status, id);
  return apiOk('（＾∀＾）保存成功');
}

export async function couponLock(env, request, url, body = {}, doLock = true) {
  const list = couponIds(body.list);
  if (!list.length) throw new Error('请选择要锁定的优惠卷');
  const ph = list.map(() => '?').join(',');
  const st = doLock ? 2 : 0;
  const src = doLock ? 0 : 2;
  const r = await dbRun(env, `UPDATE acg_coupon SET status=? WHERE id IN (${ph}) AND status=?`, st, ...list, src);
  const changes = (r && r.meta && r.meta.changes !== undefined) ? Number(r.meta.changes) : (r && r.changes !== undefined ? Number(r.changes) : list.length);
  const count = changes;
  try { await dbInsert(env, 'acg_manage_log', { email: 'admin', nickname: '', content: `[${doLock ? '锁定' : '解锁'}优惠卷]批量${doLock ? '锁定' : '解锁'}了优惠卷，共计：${count}`, create_time: now(), create_ip: requestInfo(request).ip, ua: '', risk: 0 }); } catch (e) {}
  return apiOk(count > 0 ? (doLock ? '锁定成功' : '解锁成功') : (doLock ? '没有可锁定的优惠卷' : '没有可解锁的优惠卷'), { count, requested_count: list.length });
}

export async function couponDeleteImpact(env, request, url, body = {}) {
  const impact = await couponDeleteImpact2(env, couponIds(body.list));
  delete impact.coupon_ids;
  return apiOk('success', impact);
}

// 物理删除(保护已使用/带订单号/被订单引用的优惠卷)
export async function couponDel(env, request, url, body = {}, manage) {
  const requestedIds = couponIds(body.list);
  const impact = await couponDeleteImpact2(env, requestedIds, true);
  if (!impact.can_delete) {
    throw new Error(`所选优惠卷中包含 ${impact.used_count} 张已使用优惠卷、${impact.trade_no_count} 张带最后使用订单号的优惠卷，另有 ${impact.order_reference_count} 笔订单引用；为保护历史记录，已阻止删除。`);
  }
  const ids = impact.coupon_ids;
  const ph = ids.map(() => '?').join(',');
  await dbRun(env, `DELETE FROM acg_coupon WHERE id IN (${ph}) AND status!=1 AND (trade_no IS NULL OR trade_no='') AND id NOT IN (SELECT coupon_id FROM acg_order WHERE coupon_id IS NOT NULL)`, ...ids);
  try { await dbInsert(env, 'acg_manage_log', { email: 'admin', nickname: '', content: `[批量删除]删除未使用优惠卷，共计：${impact.coupon_count}`, create_time: now(), create_ip: requestInfo(request).ip, ua: '', risk: 0 }); } catch (e) {}
  return apiOk('（＾∀＾）移除成功', { count: impact.coupon_count });
}

// 构建导出筛选 (POST-only 白名单)
function couponExportWhere(body) {
  const wheres = [];
  const params = [];
  const addStr = (key, col, maxLen = 32) => {
    const v = body[key];
    if (v === undefined || v === null || v === '') return;
    if (typeof v !== 'string' && typeof v !== 'number') throw new Error('优惠卷导出筛选条件不正确');
    const s = String(v).trim();
    if (!s) return;
    if (s.length > maxLen) throw new Error('优惠卷导出筛选条件过长');
    wheres.push(`${col}=?`); params.push(s);
  };
  addStr('coupon_code_secret', 'code', 32);
  addStr('equal-note', 'note', 32);
  addStr('equal-race', 'race', 32);
  const rawMoney = body['equal-money'];
  if (rawMoney !== undefined && rawMoney !== null && rawMoney !== '') {
    const m = Number(rawMoney);
    if (!isFinite(m) || m <= 0 || m > 99999999.99) throw new Error('优惠卷面值筛选不正确');
    wheres.push('money=?'); params.push(m);
  }
  for (const [key, col] of [['equal-owner', 'owner'], ['equal-category_id', 'category_id'], ['equal-commodity_id', 'commodity_id'], ['equal-status', 'status']]) {
    const v = body[key];
    if (v === undefined || v === null || v === '') continue;
    const n = Number(v);
    if (!Number.isInteger(n) || n < 0 || (col === 'status' && ![0, 1, 2].includes(n))) throw new Error('优惠卷导出筛选条件不正确');
    wheres.push(`${col}=?`); params.push(n);
  }
  for (const key of Object.keys(body || {})) {
    if (!key.startsWith('equal-sku-')) continue;
    const v = body[key];
    if (v === undefined || v === null || v === '') continue;
    const skKey = key.slice(10);
    const s = String(v).trim();
    wheres.push(`sku LIKE ?`); params.push(`%"${skKey}":"${s}"%`);
  }
  return { where: wheres.length ? ' WHERE ' + wheres.join(' AND ') : '', params, hasFilter: wheres.length > 0 };
}

export async function couponExportImpact(env, request, url, body = {}) {
  const { where, params, hasFilter } = couponExportWhere(body);
  const total = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_coupon${where}`, ...params);
  const count = total ? Number(total.n) : 0;
  if (count === 0) throw new Error('当前筛选没有可导出的优惠卷');
  if (count > COUPON_MAX_EXPORT) throw new Error(`当前范围过大，请增加筛选；单次最多导出 ${COUPON_MAX_EXPORT} 张优惠卷`);
  const statusRows = await dbRows(env, `SELECT status FROM acg_coupon${where}`, ...params);
  const statusCounts = { 0: 0, 1: 0, 2: 0 };
  for (const r of statusRows) {
    const st = Number(r.status);
    if (st in statusCounts) statusCounts[st]++;
  }
  return apiOk('success', { count, total: count, has_filter: hasFilter, normal_count: statusCounts[0], used_count: statusCounts[1], locked_count: statusCounts[2], max_count: COUPON_MAX_EXPORT });
}

// 导出券码 (POST only, txt 文件)
export async function couponExport(env, request, url, body = {}, manage) {
  const expectedCount = Number(body.expected_count);
  if (!Number.isInteger(expectedCount) || expectedCount < 1 || expectedCount > COUPON_MAX_EXPORT) throw new Error('请先预览并确认本次导出数量');
  const { where, params } = couponExportWhere(body);
  const total = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_coupon${where}`, ...params);
  const count = total ? Number(total.n) : 0;
  if (count === 0) throw new Error('当前筛选没有可导出的优惠卷');
  if (count > COUPON_MAX_EXPORT) throw new Error(`当前范围过大，请增加筛选；单次最多导出 ${COUPON_MAX_EXPORT} 张优惠卷`);
  if (count !== expectedCount) throw new Error('优惠卷数量已变化，请重新预览导出范围');
  const rows = await dbRows(env, `SELECT code FROM acg_coupon${where} ORDER BY id ASC`, ...params);
  if (rows.length !== expectedCount) throw new Error('优惠卷数据已变化，请重新预览导出范围');
  const content = rows.map(r => String(r.code)).join('\n') + '\n';
  try { await dbInsert(env, 'acg_manage_log', { email: 'admin', nickname: '', content: `[优惠卷导出]导出优惠卷，共计：${count}`, create_time: now(), create_ip: requestInfo(request).ip, ua: '', risk: 0 }); } catch (e) {}
  const d = new Date();
  const stamp = `${d.getFullYear()}${String(d.getMonth() + 1).padStart(2, '0')}${String(d.getDate()).padStart(2, '0')}-${String(d.getHours()).padStart(2, '0')}${String(d.getMinutes()).padStart(2, '0')}${String(d.getSeconds()).padStart(2, '0')}`;
  return new Response(content, { status: 200, headers: { 'Content-Type': 'text/plain; charset=UTF-8', 'Content-Disposition': `attachment; filename=coupons-${count}-${stamp}.txt` } });
}

// ============================================================
// 工单管理 (后台) — 对齐原版 Ticket.php + Ticket Service
//   data/detail/messages/reply/close/del/badge/upload
// 状态: 0=待客服回复 1=待用户回复 2=已解决 3=已关闭
// 类型: 0=售前咨询 1=售后支持; 优先级: 0=低 1=中 2=高
// 排序: CASE status WHEN 0 THEN 0 WHEN 1 THEN 1 ELSE 2 END, priority DESC, last_message_time DESC, id DESC
// ============================================================
const TICKET_MAX_EXCERPTS = 120;

async function ticketStats(env, baseWhere = '', ...baseParams) {
  const counts = { pending_admin: 0, pending_user: 0, resolved: 0, closed: 0, today: 0 };
  const rows = await dbRows(env, `SELECT status, COUNT(*) AS n FROM acg_ticket${baseWhere} GROUP BY status`, ...baseParams);
  for (const r of rows) {
    const st = Number(r.status);
    if (st === 0) counts.pending_admin = Number(r.n);
    else if (st === 1) counts.pending_user = Number(r.n);
    else if (st === 2) counts.resolved = Number(r.n);
    else if (st === 3) counts.closed = Number(r.n);
  }
  const nowD = new Date();
  const dayStart = Math.floor(new Date(nowD.getFullYear(), nowD.getMonth(), nowD.getDate()).getTime() / 1000);
  const dayEnd = dayStart + 86400 - 1;
  const todayRow = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_ticket${baseWhere ? baseWhere + ' AND ' : ' WHERE '}create_time BETWEEN ? AND ?`, ...baseParams, dayStart, dayEnd);
  counts.today = todayRow ? Number(todayRow.n) : 0;
  return counts;
}

function ticketTypeText(t) { return Number(t) === 1 ? '售后支持' : '售前咨询'; }
function ticketPriorityText(p) { return Number(p) === 2 ? '高' : (Number(p) === 1 ? '中' : '低'); }
function ticketStatusText(s) {
  const v = Number(s);
  if (v === 1) return '待用户回复';
  if (v === 2) return '已解决';
  if (v === 3) return '已关闭';
  return '待客服回复';
}
function ticketSenderText(s) {
  const v = Number(s);
  if (v === 1) return '管理员';
  if (v === 2) return '系统';
  return '用户';
}
function ticketOrderSourceText(src) {
  if (Number(src) === 1) return '会员订单';
  if (Number(src) === 2) return '游客订单（待人工核验）';
  return '无关联订单';
}
function ticketExcerpt(content) {
  const c = String(content || '');
  const plain = c.replace(/<img\b[^>]*>/gi, ' [图片] ').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
  return plain.length > TICKET_MAX_EXCERPTS ? plain.slice(0, TICKET_MAX_EXCERPTS) : plain;
}
function ticketParseTime(v) {
  if (v === undefined || v === null || v === '') return null;
  const s = String(v).trim();
  if (/^\d{13}$/.test(s)) return Math.floor(Number(s) / 1000);
  if (/^\d{10}$/.test(s)) return Number(s);
  const ts = Date.parse(s.replace('T', ' ').replace(/-/g, '/'));
  return isNaN(ts) ? null : Math.floor(ts / 1000);
}

async function ticketApplyFilters(env, body = {}) {
  const wheres = [];
  const params = [];
  const statusVal = body['equal-status'] !== undefined && body['equal-status'] !== '' ? body['equal-status'] : body.status;
  if (statusVal !== undefined && statusVal !== '' && [0, 1, 2, 3].includes(Number(statusVal))) { wheres.push('status=?'); params.push(String(Number(statusVal))); }
  const typeVal = body['equal-type'] !== undefined && body['equal-type'] !== '' ? body['equal-type'] : body.type;
  if (typeVal !== undefined && typeVal !== '' && [0, 1].includes(Number(typeVal))) { wheres.push('type=?'); params.push(String(Number(typeVal))); }
  const priorityVal = body['equal-priority'] !== undefined && body['equal-priority'] !== '' ? body['equal-priority'] : body.priority;
  if (priorityVal !== undefined && priorityVal !== '' && [0, 1, 2].includes(Number(priorityVal))) { wheres.push('priority=?'); params.push(String(Number(priorityVal))); }
  const userIdVal = body['equal-user_id'] !== undefined && body['equal-user_id'] !== '' ? body['equal-user_id'] : body.user_id;
  if (userIdVal !== undefined && userIdVal !== '' && Number(userIdVal) > 0) { wheres.push('user_id=?'); params.push(String(Number(userIdVal))); }
  const tradeNo = String(body.order_trade_no || '').trim();
  if (tradeNo !== '') { wheres.push('order_trade_no LIKE ?'); params.push(`%${tradeNo}%`); }
  const startTs = ticketParseTime(body['betweenStart-create_time'] ?? body.create_time_start);
  if (startTs !== null) { wheres.push('create_time >= ?'); params.push(String(startTs)); }
  const endTs = ticketParseTime(body['betweenEnd-create_time'] ?? body.create_time_end);
  if (endTs !== null) { wheres.push('create_time <= ?'); params.push(String(endTs)); }
  const keyword = String(body.keyword ?? body.keywords ?? '').trim();
  if (keyword !== '') {
    const kw = `%${keyword}%`;
    wheres.push(`(ticket_no LIKE ? OR title LIKE ? OR commodity_name LIKE ? OR order_trade_no LIKE ? OR user_id IN (SELECT id FROM acg_user WHERE username LIKE ?))`);
    params.push(kw, kw, kw, kw, kw);
  }
  return { where: wheres.length ? ' WHERE ' + wheres.join(' AND ') : '', params };
}

async function ticketNormalize(env, r, { detail = false } = {}) {
  const user = r.user_id ? await dbFirst(env, 'SELECT id, username, avatar FROM acg_user WHERE id=?', r.user_id) : null;
  const commodity = r.commodity_id ? await dbFirst(env, 'SELECT id, name, cover FROM acg_commodity WHERE id=?', r.commodity_id) : null;
  const order = r.order_id ? await dbFirst(env, 'SELECT id, owner, trade_no, amount, card_num, status, delivery_status, create_time, pay_time FROM acg_order WHERE id=?', r.order_id) : null;
  const closedBy = r.closed_by ? await dbFirst(env, 'SELECT id, nickname, avatar FROM acg_manage WHERE id=?', r.closed_by) : null;
  const guestRedacted = Number(r.order_source) === 2;
  const commodityData = !guestRedacted && commodity ? { id: Number(commodity.id), name: String(r.commodity_name || commodity.name || ''), cover: String(commodity.cover || '') } : null;
  let orderData = null;
  if (order && !guestRedacted) {
    orderData = {
      id: Number(order.id), trade_no: String(order.trade_no), create_time: order.create_time,
      amount: Number(order.amount ?? 0), card_num: Number(order.card_num || 0),
      status: Number(order.status ?? 0), delivery_status: Number(order.delivery_status ?? 0), pay_time: order.pay_time,
    };
  }
  const d = {
    id: Number(r.id),
    ticket_no: String(r.ticket_no),
    user_id: Number(r.user_id),
    type: Number(r.type),
    type_text: ticketTypeText(r.type),
    priority: Number(r.priority ?? 1),
    priority_text: ticketPriorityText(r.priority),
    status: Number(r.status ?? 0),
    status_text: ticketStatusText(r.status),
    title: String(r.title || ''),
    commodity_id: guestRedacted ? null : (r.commodity_id ?? null),
    commodity_name: guestRedacted ? null : (r.commodity_name ?? null),
    order_id: guestRedacted ? null : (r.order_id ?? null),
    order_trade_no: String(r.order_trade_no || ''),
    order_source: Number(r.order_source ?? 0),
    order_source_text: ticketOrderSourceText(r.order_source),
    order_verification_pending: guestRedacted,
    last_message_id: r.last_message_id ?? null,
    last_sender_type: r.last_sender_type ?? null,
    last_sender_text: ticketSenderText(r.last_sender_type),
    last_message_excerpt: String(r.last_message_excerpt || ''),
    last_message_time: r.last_message_time ?? null,
    user_unread: Number(r.user_unread ?? 0),
    manage_unread: Number(r.manage_unread ?? 0),
    closed_by: r.closed_by ?? null,
    closed_time: r.closed_time ?? null,
    create_time: r.create_time,
    update_time: r.update_time,
    user: user ? { id: Number(user.id), username: String(user.username || ''), avatar: String(user.avatar || '') } : null,
    commodity: commodityData,
    order: orderData,
    context: {
      commodity: commodityData,
      commodity_name: guestRedacted ? null : (r.commodity_name ?? null),
      order: orderData,
      order_trade_no: String(r.order_trade_no || ''),
      order_source: Number(r.order_source ?? 0),
      order_source_text: ticketOrderSourceText(r.order_source),
      order_verification_pending: guestRedacted,
    },
    closed_by_manage: closedBy ? { id: Number(closedBy.id), nickname: String(closedBy.nickname || ''), avatar: String(closedBy.avatar || '') } : null,
  };
  if (detail) {
    d.proof_upload_id = r.proof_upload_id ?? null;
    d.proof_path = String(r.proof_path || '');
    d.proof = r.proof_path ? { upload_id: r.proof_upload_id ?? null, url: String(r.proof_path) } : null;
  }
  return d;
}

function ticketNormalizeMessage(m) {
  return {
    id: Number(m.id),
    sender_type: Number(m.sender_type),
    sender_name: String(m.sender_name || ''),
    kind: Number(m.kind ?? 0),
    content: String(m.content ?? ''),
    create_time: m.create_time,
  };
}

// 工单列表
export async function ticketData(env, request, url, body = {}) {
  const page = Math.max(1, Number(body.page) || 1);
  const pageSize = Math.min(100, Math.max(1, Number(body.limit) || 20));
  const { where, params } = await ticketApplyFilters(env, body);
  const stats = await ticketStats(env, where, ...params);
  const total = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_ticket${where}`, ...params);
  const count = total ? Number(total.n) : 0;
  const rows = await dbRows(env, `SELECT * FROM acg_ticket${where} ORDER BY CASE status WHEN 0 THEN 0 WHEN 1 THEN 1 ELSE 2 END, priority DESC, last_message_time DESC, id DESC LIMIT ? OFFSET ?`, ...params, pageSize, (page - 1) * pageSize);
  const list = [];
  for (const r of rows) list.push(await ticketNormalize(env, r));
  return apiOk('success', { list, page, limit: pageSize, count, records: count, total: count, stats });
}

// 工单详情 + 最近消息
export async function ticketDetail(env, request, url, body = {}) {
  const id = Number(body.id) || 0;
  if (id <= 0) throw new Error('请选择工单');
  const r = await dbFirst(env, 'SELECT * FROM acg_ticket WHERE id=?', id);
  if (!r) throw new Error('工单不存在');
  const limit = Math.min(100, Math.max(1, Number(body.limit) || 30));
  const msgRows = await dbRows(env, 'SELECT * FROM acg_ticket_message WHERE ticket_id=? ORDER BY id DESC LIMIT ?', id, limit + 1);
  let hasMore = msgRows.length > limit;
  if (hasMore) msgRows.pop();
  msgRows.reverse();
  await dbRun(env, 'UPDATE acg_ticket SET manage_unread=0 WHERE id=?', id);
  return apiOk('success', { ticket: await ticketNormalize(env, r, { detail: true }), messages: msgRows.map(ticketNormalizeMessage), has_more: hasMore });
}

// 增量消息
export async function ticketMessages(env, request, url, body = {}) {
  const id = Number(body.id) || 0;
  if (id <= 0) throw new Error('请选择工单');
  const r = await dbFirst(env, 'SELECT * FROM acg_ticket WHERE id=?', id);
  if (!r) throw new Error('工单不存在');
  const afterId = Math.max(0, Number(body.after_id) || 0);
  const beforeId = Math.max(0, Number(body.before_id) || 0);
  const limit = Math.min(100, Math.max(1, Number(body.limit) || 50));
  await dbRun(env, 'UPDATE acg_ticket SET manage_unread=0 WHERE id=?', id);
  if (beforeId > 0) {
    let items = await dbRows(env, 'SELECT * FROM acg_ticket_message WHERE ticket_id=? AND id < ? ORDER BY id DESC LIMIT ?', id, beforeId, limit + 1);
    let hasMore = items.length > limit;
    if (hasMore) items.pop();
    items.reverse();
    return apiOk('success', { list: items.map(ticketNormalizeMessage), status: Number(r.status ?? 0), last_message_time: r.last_message_time, has_more: hasMore });
  }
  const items = await dbRows(env, 'SELECT * FROM acg_ticket_message WHERE ticket_id=? AND id > ? ORDER BY id ASC LIMIT ?', id, afterId, limit);
  return apiOk('success', { list: items.map(ticketNormalizeMessage), status: Number(r.status ?? 0), last_message_time: r.last_message_time, has_more: false });
}

// 回复 / 解决
export async function ticketReply(env, request, url, body = {}, manage) {
  const id = Number(body.id) || 0;
  const content = String(body.content || '').trim();
  const mode = String(body.mode || 'reply').toLowerCase();
  if (id <= 0) throw new Error('请选择工单');
  if (!['reply', 'resolve'].includes(mode)) throw new Error('未知的回复方式');
  const ticket = await dbFirst(env, 'SELECT * FROM acg_ticket WHERE id=?', id);
  if (!ticket) throw new Error('工单不存在');
  if (Number(ticket.status) >= 2) throw new Error('工单已结束，无法继续回复');
  const clean = String(content).replace(/<img[^>]*src=(["'])(\/assets\/cache\/(?:user\/[0-9]+|general)\/ticket\/[A-Za-z0-9._-]+)\1[^>]*>/gi, '$2');
  if (content === '') throw new Error('回复内容为空或过长');
  const plain = ticketExcerpt(content);
  if (plain === '') throw new Error('请填写内容或插入图片');
  const name = String(manage.nickname || manage.email || '管理员');
  const kind = mode === 'resolve' ? 1 : 0;
  const lastId = await dbInsert(env, 'acg_ticket_message', { ticket_id: id, sender_type: 1, sender_id: Number(manage.id), sender_name: String(name).slice(0, 32), kind, content, create_ip: requestInfo(request).ip, create_time: now() });
  const newStatus = mode === 'resolve' ? 2 : 1;
  const closedBy = mode === 'resolve' ? Number(manage.id) : ticket.closed_by;
  const closedTime = mode === 'resolve' ? now() : ticket.closed_time;
  await dbUpdate(env, 'acg_ticket', { last_message_id: lastId, last_sender_type: 1, last_message_excerpt: ticketExcerpt(content), last_message_time: now(), update_time: now(), status: newStatus, closed_by: closedBy, closed_time: closedTime, manage_unread: 0, user_unread: Math.min(4294967295, Number(ticket.user_unread || 0) + 1) }, 'id=?', id);
  const fresh = await dbFirst(env, 'SELECT * FROM acg_ticket WHERE id=?', id);
  const msg = await dbFirst(env, 'SELECT * FROM acg_ticket_message WHERE id=?', lastId);
  try { await dbInsert(env, 'acg_manage_log', { email: 'admin', nickname: '', content: `[${mode === 'resolve' ? '回复并解决了' : '回复了'}工单(${String(ticket.ticket_no)})]`, create_time: now(), create_ip: requestInfo(request).ip, ua: '', risk: 0 }); } catch (e) {}
  return apiOk(mode === 'resolve' ? '回复并解决问题成功' : '回复成功', { message: ticketNormalizeMessage(msg), status: Number(fresh.status), last_message_time: fresh.last_message_time });
}

// 关闭工单
export async function ticketClose(env, request, url, body = {}, manage) {
  const id = Number(body.id) || 0;
  if (id <= 0) throw new Error('请选择工单');
  const ticket = await dbFirst(env, 'SELECT * FROM acg_ticket WHERE id=?', id);
  if (!ticket) throw new Error('工单不存在');
  if (Number(ticket.status) >= 2) throw new Error('工单已经结束');
  const name = String(manage.nickname || manage.email || '管理员');
  const content = '工单已由 ' + String(name).replace(/<[^>]+>/g, '') + ' 关闭。';
  const lastId = await dbInsert(env, 'acg_ticket_message', { ticket_id: id, sender_type: 1, sender_id: Number(manage.id), sender_name: String(name).slice(0, 32), kind: 2, content, create_ip: requestInfo(request).ip, create_time: now() });
  await dbUpdate(env, 'acg_ticket', { last_message_id: lastId, last_sender_type: 1, last_message_excerpt: ticketExcerpt(content), last_message_time: now(), update_time: now(), status: 3, closed_by: Number(manage.id), closed_time: now(), manage_unread: 0, user_unread: Math.min(4294967295, Number(ticket.user_unread || 0) + 1) }, 'id=?', id);
  const fresh = await dbFirst(env, 'SELECT * FROM acg_ticket WHERE id=?', id);
  const msg = await dbFirst(env, 'SELECT * FROM acg_ticket_message WHERE id=?', lastId);
  try { await dbInsert(env, 'acg_manage_log', { email: 'admin', nickname: '', content: `[关闭工单]关闭了工单(${String(ticket.ticket_no)})`, create_time: now(), create_ip: requestInfo(request).ip, ua: '', risk: 0 }); } catch (e) {}
  return apiOk('工单已关闭', { message: ticketNormalizeMessage(msg), status: Number(fresh.status), last_message_time: fresh.last_message_time });
}

// 删除工单 (连消息)
export async function ticketDel(env, request, url, body = {}) {
  const raw = body.list;
  const ids = Array.isArray(raw) ? raw.map(v => Number(v)).filter(v => Number.isInteger(v) && v > 0) : String(raw || '').split(',').map(v => Number(v.trim())).filter(v => Number.isInteger(v) && v > 0);
  const uniq = [...new Set(ids)];
  if (!uniq.length) throw new Error('请选择要删除的工单');
  if (uniq.length > 100) throw new Error('一次最多删除 100 个工单');
  const ph = uniq.map(() => '?').join(',');
  const found = await dbRows(env, `SELECT id, ticket_no FROM acg_ticket WHERE id IN (${ph})`, ...uniq);
  if (!found.length) throw new Error('工单不存在或已被删除');
  const foundIds = found.map(f => Number(f.id));
  const ph2 = foundIds.map(() => '?').join(',');
  const msgCountRow = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_ticket_message WHERE ticket_id IN (${ph2})`, ...foundIds);
  const msgCount = msgCountRow ? Number(msgCountRow.n) : 0;
  const msgRes = await dbRun(env, `DELETE FROM acg_ticket_message WHERE ticket_id IN (${ph2})`, ...foundIds);
  await dbRun(env, `DELETE FROM acg_ticket WHERE id IN (${ph2})`, ...foundIds);
  const ticketCount = foundIds.length;
  try { await dbInsert(env, 'acg_manage_log', { email: 'admin', nickname: '', content: `[删除工单]已删除 ${ticketCount} 个工单及 ${msgCount} 条消息`, create_time: now(), create_ip: requestInfo(request).ip, ua: '', risk: 0 }); } catch (e) {}
  return apiOk(`已删除 ${ticketCount} 个工单，关联 ${msgCount} 条消息`, { ticket_count: ticketCount, message_count: msgCount, file_count: 0, kept_count: 0 });
}

// 待客服工单数 (badge)
export async function ticketBadge(env, request, url) {
  const row = await dbFirst(env, 'SELECT COUNT(*) AS n FROM acg_ticket WHERE status=0');
  return apiOk('success', { count: row ? Number(row.n) : 0 });
}

// 工单图片上传 (当前环境无持久化文件存储)
export async function ticketUpload(env, request, url, body = {}, manage) {
  throw new Error('当前环境未提供持久化文件存储，暂不支持图片上传');
}

// ============================================================
// 系统消息管理 (后台) — 对齐原版 Message.php + Message Service
//   data/detail/save/del/users/audienceCount/upload
// 接收范围: 0=全体用户 1=会员等级(UserGroup) 2=指定用户
// ============================================================
function messageCleanTitle(title) {
  const t = String(title || '').replace(/<[^>]*>/g, '').trim();
  if (t === '' || t.length > 64) throw new Error('消息标题不能为空且不能超过 64 字');
  return t;
}
function messageCleanJumpUrl(v) {
  const s = String(v || '').trim();
  if (s === '') return null;
  if (!/^https?:\/\//i.test(s)) throw new Error('跳转链接必须以 http:// 或 https:// 开头');
  return s.slice(0, 500);
}
function messageSummary(content) {
  const c = String(content || '');
  const plain = c.replace(/<img[^>]*>/gi, ' [图片] ')
    .replace(/<[^>]+>/g, ' ')
    .replace(/&nbsp;/gi, ' ')
    .replace(/\s+/g, ' ')
    .trim();
  return plain.length > 120 ? plain.slice(0, 120) : plain;
}
function messageCleanContent(content) {
  const c = String(content || '').trim();
  if (c === '') throw new Error('消息内容不能为空');
  const plain = messageSummary(c);
  if (plain === '') throw new Error('消息内容不能为空');
  return c;
}
async function messageEmailAvailable(env) {
  try {
    const row = await dbFirst(env, `SELECT value FROM acg_config WHERE key='email_config'`);
    let cfg = { smtp: '' };
    if (row && row.value) { try { cfg = JSON.parse(row.value); } catch (e) {} }
    return !!(cfg && typeof cfg.smtp === 'string' && cfg.smtp !== '');
  } catch (e) { return false; }
}
// 接收范围: 返回 { sqlWhere, params, name }（status=1 的用户）
async function messageRecipientScope(env, audienceType, audienceId) {
  if (![0, 1, 2].includes(Number(audienceType))) throw new Error('请选择有效的接收范围');
  const wheres = ['status=1'];
  const params = [];
  if (Number(audienceType) === 0) {
    return { wheres, params, name: '全体用户' };
  }
  if (Number(audienceId) <= 0) {
    throw new Error(Number(audienceType) === 1 ? '请选择会员等级' : '请选择指定用户');
  }
  if (Number(audienceType) === 2) {
    const u = await dbFirst(env, 'SELECT id, username FROM acg_user WHERE id=? AND status=1', audienceId);
    if (!u) throw new Error('指定用户不存在或状态异常');
    wheres.push('id=?'); params.push(String(audienceId));
    return { wheres, params, name: String(u.username) };
  }
  const groups = await dbRows(env, 'SELECT id, name, recharge FROM acg_user_group ORDER BY recharge DESC');
  const idx = groups.findIndex(g => Number(g.id) === audienceId);
  if (idx < 0) throw new Error('会员等级不存在');
  const selected = groups[idx];
  const upper = idx > 0 ? Number(groups[idx - 1].recharge) : null;
  wheres.push('recharge >= ?'); params.push(String(Number(selected.recharge)));
  if (upper !== null) { wheres.push('recharge < ?'); params.push(String(upper)); }
  return { wheres, params, name: String(selected.name) };
}

function messageAdminItem(r, withContent = false) {
  const item = {
    id: Number(r.id),
    title: String(r.title || ''),
    summary: String(r.summary || ''),
    audience_type: Number(r.audience_type ?? 0),
    audience_id: r.audience_id === null || r.audience_id === undefined ? null : Number(r.audience_id),
    audience_name: String(r.audience_name || ''),
    recipient_count: Number(r.recipient_count || 0),
    jump_url: r.jump_url === null || r.jump_url === undefined ? null : String(r.jump_url),
    create_time: r.create_time,
    update_time: r.update_time,
    manage_name: String(r.manage_name || ''),
    update_manage_name: String(r.update_manage_name || ''),
  };
  if (withContent) item.content = String(r.content || '');
  return item;
}

// 列表
export async function messageData(env, request, url, body = {}) {
  const page = Math.max(1, Number(body.page) || 1);
  const pageSize = Math.min(100, Math.max(1, Number(body.limit) || 10));
  const wheres = [];
  const params = [];
  const keyword = String(body.keyword ?? body.title ?? '').trim();
  if (keyword !== '') { wheres.push('title LIKE ?'); params.push(`%${keyword}%`); }
  const at = body['equal-audience_type'] !== undefined && body['equal-audience_type'] !== '' ? body['equal-audience_type'] : body.audience_type;
  if (at !== undefined && at !== '' && [0, 1, 2].includes(Number(at))) { wheres.push('audience_type=?'); params.push(String(Number(at))); }
  const startTs = ticketParseTime(body['betweenStart-create_time'] ?? body.create_time_start);
  if (startTs !== null) { wheres.push('create_time >= ?'); params.push(String(startTs)); }
  const endTs = ticketParseTime(body['betweenEnd-create_time'] ?? body.create_time_end);
  if (endTs !== null) { wheres.push('create_time <= ?'); params.push(String(endTs)); }
  const where = wheres.length ? ' WHERE ' + wheres.join(' AND ') : '';
  const total = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_system_message${where}`, ...params);
  const count = total ? Number(total.n) : 0;
  const rows = await dbRows(env, `SELECT * FROM acg_system_message${where} ORDER BY id DESC LIMIT ? OFFSET ?`, ...params, pageSize, (page - 1) * pageSize);
  const list = rows.map(messageAdminItem);
  return apiOk('success', { list, page, limit: pageSize, count, records: count, total: count });
}

// 详情
export async function messageDetail(env, request, url, body = {}) {
  const id = Number(body.id) || 0;
  if (id <= 0) throw new Error('请选择消息');
  const r = await dbFirst(env, 'SELECT * FROM acg_system_message WHERE id=?', id);
  if (!r) throw new Error('消息不存在');
  return apiOk('success', messageAdminItem(r, true));
}

// 新建/编辑
export async function messageSave(env, request, url, body = {}, manage) {
  for (const key of ['id', 'title', 'content', 'jump_url', 'audience_type', 'audience_id', 'group_id', 'user_id', 'send_email']) {
    if (body[key] !== undefined && body[key] !== null && typeof body[key] === 'object') throw new Error('消息表单参数无效');
  }
  const id = Number(body.id) || 0;
  const hasSendEmail = body.send_email !== undefined;
  const sendEmail = hasSendEmail ? !!Number(body.send_email) : false;
  if (id > 0 && hasSendEmail) throw new Error('编辑消息不能重复发送邮件通知');
  if (sendEmail && !(await messageEmailAvailable(env))) throw new Error('邮件功能尚未配置完整，无法发送邮件通知');
  const title = messageCleanTitle(body.title);
  const jumpUrl = messageCleanJumpUrl(body.jump_url);
  const manageName = String(manage && (manage.nickname || manage.email) || '管理员');
  const ts = now();

  if (id > 0) {
    const exist = await dbFirst(env, 'SELECT id FROM acg_system_message WHERE id=?', id);
    if (!exist) throw new Error('消息不存在');
    const content = messageCleanContent(body.content);
    await dbUpdate(env, 'acg_system_message', { title, content, summary: messageSummary(content), jump_url: jumpUrl, updated_by: Number(manage.id), update_manage_name: manageName, update_time: ts }, 'id=?', id);
    try { await dbInsert(env, 'acg_manage_log', { email: 'admin', nickname: '', content: `[消息管理]编辑了消息(#${id})`, create_time: ts, create_ip: requestInfo(request).ip, ua: '', risk: 0 }); } catch (e) {}
    const r = await dbFirst(env, 'SELECT * FROM acg_system_message WHERE id=?', id);
    return apiOk('消息保存成功', messageAdminItem(r, true));
  }

  if (body.audience_type === undefined) throw new Error('请选择接收范围');
  const audienceType = Number(body.audience_type);
  let audienceId = Number(body.audience_id) || 0;
  if (audienceType === 1) {
    const groupId = Number(body.group_id) || 0;
    if (audienceId > 0 && groupId > 0 && audienceId !== groupId) throw new Error('接收对象与会员等级不一致');
    audienceId = audienceId > 0 ? audienceId : groupId;
  } else if (audienceType === 2) {
    const userId = Number(body.user_id) || 0;
    if (audienceId > 0 && userId > 0 && audienceId !== userId) throw new Error('接收对象与指定用户不一致');
    audienceId = audienceId > 0 ? audienceId : userId;
  } else if (audienceId > 0 || Number(body.group_id) > 0 || Number(body.user_id) > 0) {
    throw new Error('全体用户消息不能指定会员等级或用户');
  }
  const content = messageCleanContent(body.content);
  const scope = await messageRecipientScope(env, audienceType, audienceId);
  const w = scope.wheres.length ? ' WHERE ' + scope.wheres.join(' AND ') : '';
  const recipients = await dbRows(env, `SELECT id FROM acg_user${w}`, ...scope.params);
  if (!recipients.length) throw new Error('当前接收范围没有正常用户，消息未发送');
  const mid = await dbInsert(env, 'acg_system_message', {
    audience_type: audienceType,
    audience_id: audienceType === 0 ? null : audienceId,
    audience_name: scope.name,
    title, content, summary: messageSummary(content), jump_url: jumpUrl,
    recipient_count: 0,
    created_by: Number(manage.id), updated_by: Number(manage.id),
    manage_name: manageName, update_manage_name: manageName,
    create_time: ts, update_time: ts,
  });
  const umStmt = `INSERT INTO acg_user_message (message_id, user_id, create_time) VALUES ` + recipients.map(() => '(?,?,?)').join(',');
  const umParams = [];
  for (const u of recipients) umParams.push(mid, u.id, ts);
  await dbRun(env, umStmt, ...umParams);
  await dbUpdate(env, 'acg_system_message', { recipient_count: recipients.length }, 'id=?', mid);
  try { await dbInsert(env, 'acg_manage_log', { email: 'admin', nickname: '', content: `[消息管理]发送了消息(#${mid})，接收人数：${recipients.length}`, create_time: ts, create_ip: requestInfo(request).ip, ua: '', risk: 0 }); } catch (e) {}
  const r = await dbFirst(env, 'SELECT * FROM acg_system_message WHERE id=?', mid);
  return apiOk('消息发送成功', messageAdminItem(r, true));
}

// 删除消息
export async function messageDel(env, request, url, body = {}, manage) {
  const raw = body.list ?? body.ids;
  let ids;
  if (Array.isArray(raw)) ids = raw.map(v => Number(v)).filter(v => Number.isInteger(v) && v > 0);
  else if (raw !== undefined && raw !== '') ids = String(raw).split(',').map(v => Number(v.trim())).filter(v => Number.isInteger(v) && v > 0);
  else ids = [Number(body.id) || 0].filter(v => v > 0);
  const uniq = [...new Set(ids)].slice(0, 500);
  if (!uniq.length) throw new Error('请选择要删除的消息');
  const ph = uniq.map(() => '?').join(',');
  const exist = await dbRows(env, `SELECT id FROM acg_system_message WHERE id IN (${ph})`, ...uniq);
  if (!exist.length) throw new Error('消息不存在');
  const foundIds = exist.map(r => Number(r.id));
  const ph2 = foundIds.map(() => '?').join(',');
  await dbRun(env, `DELETE FROM acg_system_message WHERE id IN (${ph2})`, ...foundIds);
  await dbRun(env, `DELETE FROM acg_user_message WHERE message_id IN (${ph2})`, ...foundIds);
  const count = foundIds.length;
  try { await dbInsert(env, 'acg_manage_log', { email: 'admin', nickname: '', content: `[消息管理]删除了消息，共计：${count} 条`, create_time: now(), create_ip: requestInfo(request).ip, ua: '', risk: 0 }); } catch (e) {}
  return apiOk('消息删除成功', { count });
}

// 用户搜索 (用于选择接收用户)
export async function messageUsers(env, request, url, body = {}) {
  const keyword = String(body.keyword ?? body.q ?? body.search ?? '').trim();
  const limit = Math.min(20, Math.max(1, Number(body.limit) || 20));
  const wheres = ['status=1'];
  const params = [];
  if (keyword !== '') {
    const kw = `%${keyword}%`;
    if (/^\d+$/.test(keyword)) {
      wheres.push(`(username LIKE ? OR email LIKE ? OR phone LIKE ? OR id=?)`); params.push(kw, kw, kw, keyword);
    } else {
      wheres.push(`(username LIKE ? OR email LIKE ? OR phone LIKE ?)`); params.push(kw, kw, kw);
    }
  }
  const where = wheres.length ? ' WHERE ' + wheres.join(' AND ') : '';
  const groups = await dbRows(env, 'SELECT name, recharge FROM acg_user_group ORDER BY recharge DESC');
  const rows = await dbRows(env, `SELECT id, username, avatar, recharge FROM acg_user${where} ORDER BY id DESC LIMIT ?`, ...params, limit);
  const list = rows.map(u => {
    let groupName = '';
    for (const g of groups) {
      if (Number(u.recharge) >= Number(g.recharge)) { groupName = String(g.name); break; }
    }
    return { id: Number(u.id), username: String(u.username || ''), avatar: String(u.avatar || ''), group_name: groupName };
  });
  return apiOk('success', { list });
}

// 接收人数预览
export async function messageAudienceCount(env, request, url, body = {}) {
  const audienceType = Number(body.audience_type) || 0;
  let audienceId = Number(body.audience_id) || 0;
  if (audienceId <= 0) {
    audienceId = audienceType === 1 ? (Number(body.group_id) || 0) : (Number(body.user_id) || 0);
  }
  const scope = await messageRecipientScope(env, audienceType, audienceId);
  const w = scope.wheres.length ? ' WHERE ' + scope.wheres.join(' AND ') : '';
  const row = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_user${w}`, ...scope.params);
  return apiOk('success', { count: row ? Number(row.n) : 0 });
}

// 上传 (当前环境无持久化文件存储)
export async function messageUpload(env, request, url, body = {}, manage) {
  throw new Error('当前环境未提供持久化文件存储，暂不支持图片上传');
}

// ============================================================
// 提现管理 (后台) — 对齐原版 Cash.php
//   data/decide/settlement
// status: 0=待处理 1=已通过 2=已驳回
// ============================================================
// 提现列表
export async function cashData(env, request, url, body = {}) {
  const page = Math.max(1, Number(body.page) || 1);
  const pageSize = Math.min(100, Math.max(1, Number(body.limit) || 10));
  const wheres = [];
  const params = [];
  for (const [key, col] of [['equal-status', 'status'], ['equal-type', 'type'], ['equal-user_id', 'user_id'], ['equal-id', 'id']]) {
    if (body[key] !== undefined && body[key] !== '') { wheres.push(`${col}=?`); params.push(String(body[key])); }
  }
  const startTs = ticketParseTime(body['betweenStart-create_time'] ?? body.create_time_start);
  if (startTs !== null) { wheres.push('create_time >= ?'); params.push(String(startTs)); }
  const endTs = ticketParseTime(body['betweenEnd-create_time'] ?? body.create_time_end);
  if (endTs !== null) { wheres.push('create_time <= ?'); params.push(String(endTs)); }
  const keyword = String(body.keyword ?? '').trim();
  if (keyword !== '') {
    const kw = `%${keyword}%`;
    wheres.push(`(user_id IN (SELECT id FROM acg_user WHERE username LIKE ? OR alipay LIKE ? OR wechat LIKE ? OR wallet_address LIKE ?) OR message LIKE ?)`);
    params.push(kw, kw, kw, kw, kw);
  }
  const where = wheres.length ? ' WHERE ' + wheres.join(' AND ') : '';
  const total = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_cash${where}`, ...params);
  const count = total ? Number(total.n) : 0;
  const sumRow = await dbFirst(env, `SELECT COALESCE(SUM(amount),0) AS amount, COALESCE(SUM(cost),0) AS cost FROM acg_cash${where}`, ...params);
  const rows = await dbRows(env, `SELECT * FROM acg_cash${where} ORDER BY id DESC LIMIT ? OFFSET ?`, ...params, pageSize, (page - 1) * pageSize);
  const list = [];
  for (const r of rows) {
    list.push({
      ...r,
      user: r.user_id ? (await dbFirst(env, 'SELECT id, username, avatar, nicename, alipay, wechat, wallet_address FROM acg_user WHERE id=?', r.user_id)) || null : null,
    });
  }
  return apiOk('success', { list, page, limit: pageSize, count, records: count, total: count, amount: (sumRow && sumRow.amount) ?? 0, cost: (sumRow && sumRow.cost) ?? 0 });
}

// 提现审批: status 0=通过 1=驳回
export async function cashDecide(env, request, url, body = {}, manage) {
  const id = Number(body.id) || 0;
  const status = Number(body.status);
  const message = String(body.message || '').trim();
  if (id <= 0 || ![0, 1].includes(status)) throw new Error('请求参数不正确');
  if (status === 1 && message === '') throw new Error('请输入驳回理由');
  if (message.length > 64) throw new Error('驳回理由不能超过 64 个字');
  const cash = await dbFirst(env, 'SELECT * FROM acg_cash WHERE id=?', id);
  if (!cash) throw new Error('该记录不存在');
  if (Number(cash.status) !== 0) throw new Error('该记录无法操作');
  const ts = now();
  if (status === 0) {
    await dbUpdate(env, 'acg_cash', { status: 1, arrive_time: ts }, 'id=?', id);
    try { await dbInsert(env, 'acg_manage_log', { email: 'admin', nickname: '', content: `[提现管理]通过了用户ID(${cash.user_id})的提现`, create_time: ts, create_ip: requestInfo(request).ip, ua: '', risk: 0 }); } catch (e) {}
  } else {
    await dbUpdate(env, 'acg_cash', { status: 2, message, arrive_time: ts }, 'id=?', id);
    // 驳回退款: 余额 + amount + cost
    const user = await dbFirst(env, 'SELECT * FROM acg_user WHERE id=?', cash.user_id);
    if (user) {
      const refund = Math.round((Number(cash.amount) + Number(cash.cost || 0)) * 100) / 100;
      if (refund > 0) {
        const newBalance = Math.round((Number(user.balance) + refund) * 100) / 100;
        await dbUpdate(env, 'acg_user', { balance: newBalance }, 'id=?', user.id);
        await dbInsert(env, 'acg_bill', { owner: user.id, amount: refund, balance: newBalance, type: 1, currency: 0, log: '兑现被拒绝', create_time: ts });
      }
    }
    try { await dbInsert(env, 'acg_manage_log', { email: 'admin', nickname: '', content: `[提现管理]驳回了用户(${user ? user.username : cash.user_id})的提现`, create_time: ts, create_ip: requestInfo(request).ip, ua: '', risk: 0 }); } catch (e) {}
  }
  return apiOk('处理成功');
}

// 自动结算: coin >= amount 的用户自动生成提现单
export async function cashSettlement(env, request, url, body = {}, manage) {
  const rawAmount = Number(body.amount);
  if (!isFinite(rawAmount) || rawAmount <= 0) throw new Error('最低结算金额必须大于 0');
  const ts = now();
  const users = await dbRows(env, 'SELECT id, coin, settlement FROM acg_user WHERE coin >= ? AND coin > 0', rawAmount);
  let done = 0;
  for (const u of users) {
    const coin = Number(u.coin);
    if (!(coin >= rawAmount) || !(coin > 0)) continue;
    const usr = await dbFirst(env, 'SELECT id, coin, settlement FROM acg_user WHERE id=?', u.id);
    if (!usr || Number(usr.coin) < rawAmount || Number(usr.coin) <= 0) continue;
    await dbInsert(env, 'acg_cash', { user_id: usr.id, amount: coin, type: 0, card: usr.settlement ?? 0, create_time: ts, cost: 0, status: 0 });
    await dbUpdate(env, 'acg_user', { coin: 0 }, 'id=?', usr.id);
    await dbInsert(env, 'acg_bill', { owner: usr.id, amount: coin, balance: 0, type: 0, currency: 1, log: '自动结算', create_time: ts });
    done++;
  }
  try { await dbInsert(env, 'acg_manage_log', { email: 'admin', nickname: '', content: `[提现管理]进行了一键自动结算，金额上限：${rawAmount}，生成 ${done} 单`, create_time: ts, create_ip: requestInfo(request).ip, ua: '', risk: 0 }); } catch (e) {}
  return apiOk('结算完成', { count: done });
}

// ============================================================
// 账单管理 (后台) — 对齐原版 Api/Bill.php::data
//   /admin/api/bill/data   筛选: equal-owner/equal-type/equal-currency/search-log/between-create_time
//   原版 Bill::owner 关联 User(id, username, avatar)，create_time 为 datetime 字符串
// ============================================================
const BILL_COLUMNS = ['id', 'owner', 'amount', 'balance', 'type', 'currency', 'log', 'create_time'];
export async function billData(env, request, url, body = {}) {
  const page = await queryListPage(env, {
    table: 'acg_bill',
    columns: BILL_COLUMNS,
    timeColumns: ['create_time'],
    body,
    defaultSort: 'id',
  });
  const list = [];
  for (const r of page.list) {
    const owner = r.owner ? await dbFirst(env, 'SELECT id, username, avatar FROM acg_user WHERE id=?', r.owner) : null;
    list.push({ ...r, owner: owner || null, create_time: dtString(r.create_time) });
  }
  return apiOk('success', { list, total: page.total, page: page.page, limit: page.limit });
}

// ============================================================
// 操作日志 (后台) — 对齐原版 Api/Log.php::data
//   /admin/api/log/data
//   原版: 键名白名单 equal-email/equal-nickname/equal-create_ip/search-content/
//         between-create_time/equal-risk；limit 仅允许 15/30/50，默认 15
// ============================================================
const LOG_COLUMNS = ['id', 'email', 'nickname', 'content', 'create_time', 'create_ip', 'ua', 'risk'];
export async function logData(env, request, url, body = {}) {
  // 原版做了 array_intersect_key 白名单，这里用 columns 白名单等效
  const page = await queryListPage(env, {
    table: 'acg_manage_log',
    columns: LOG_COLUMNS,
    timeColumns: ['create_time'],
    body,
    defaultSort: 'id',
    limitWhitelist: [15, 30, 50],
  });
  const list = page.list.map((r) => ({ ...r, risk: Number(r.risk) || 0, create_time: dtString(r.create_time) }));
  return apiOk('success', { list, total: page.total, page: page.page, limit: page.limit });
}

// ============================================================
// 支付接口 + 支付插件 (后台) — 对齐原版 Controller/Admin/Api/Pay.php
//   /admin/api/pay/data            列表(归档默认隐藏, 默认 sort asc)
//   /admin/api/pay/save            新增/修改(严格字段白名单 + 逐字段校验)
//   /admin/api/pay/deleteImpact    删除影响预览(只读)
//   /admin/api/pay/del             无引用→物理删除, 有引用→归档
//   /admin/api/pay/restore         恢复归档(恢复后仍停用)
//   /admin/api/pay/getPlugins      插件列表(注册表 + 各配置档)
//   /admin/api/pay/getPluginConfigs / createPluginConfig /
//   /admin/api/pay/renamePluginConfig / delPluginConfig
//   /admin/api/pay/setPluginConfig  写插件(或指定配置档)的配置值
//   /admin/api/pay/getPluginLog / ClearPluginLog
// ============================================================
const PAY_SAVE_FIELDS = [  'name', 'icon', 'code', 'commodity', 'recharge', 'handle',
  'pay_config_id', 'sort', 'equipment', 'cost', 'cost_type',
];
const PAY_MAX_BATCH = 100;
const PAY_TEST_MAX_AMOUNT = 100;

const PAY_COLUMNS = [
  'id', 'name', 'icon', 'code', 'commodity', 'recharge', 'create_time',
  'handle', 'pay_config_id', 'sort', 'equipment', 'cost', 'cost_type', 'archived',
];

// ---- 与原版 private paymentIds() 同语义 ----
function payIds(value) {
  let list;
  if (typeof value === 'string') list = value.split(',');
  else if (Array.isArray(value)) list = value;
  else list = [value];
  const ids = [];
  for (const raw of list) {
    if (raw === '' || raw === null || raw === undefined) continue;
    const s = String(raw).trim();
    if (!/^\d+$/.test(s)) throw new Error('支付接口 ID 格式不正确');
    const id = Number(s);
    if (id < 1 || id > 4294967295) throw new Error('支付接口 ID 超出有效范围');
    ids.push(id);
  }
  const uniq = [...new Set(ids)];
  if (uniq.length > PAY_MAX_BATCH) throw new Error(`单次最多操作 ${PAY_MAX_BATCH} 个支付接口`);
  return uniq;
}

function payId(value) {
  if (value === '' || value === null || value === undefined || value === 0 || value === '0') return 0;
  return payIds([value])[0] || 0;
}

function payInt(value, label, min, max) {
  const s = String(value == null ? '' : value).trim();
  if (!/^-?\d+$/.test(s)) throw new Error(`${label}格式不正确`);
  const n = Number(s);
  if (n < min || n > max) throw new Error(`${label}超出有效范围`);
  return n;
}

function payStr(value, label) {
  if (value === null || value === undefined || typeof value === 'object') throw new Error(`${label}格式不正确`);
  return String(value).trim();
}

// 配置档是否存在且属于该 handle(否则就是拿别家的商户凭据去收款)
async function payConfigBelongs(env, handle, configId) {
  if (!(configId > 0)) return false;
  const row = await dbFirst(env, 'SELECT id FROM acg_pay_config WHERE id=? AND handle=?', configId, String(handle));
  return !!row;
}

// ---- 与原版 paymentSaveMap() 同语义 ----
async function paySaveMap(env, raw, existing) {
  const allowed = new Set(['id', ...PAY_SAVE_FIELDS]);
  for (const field of Object.keys(raw || {})) {
    if (!allowed.has(field)) throw new Error('支付接口保存请求包含未授权字段');
  }
  if (existing && Number(existing.id) === 1) throw new Error('系统内置余额接口无法修改');

  const src = { ...raw };
  delete src.id;

  // 已有支付接口的所属插件不可更改
  if (existing && Object.prototype.hasOwnProperty.call(src, 'handle')) {
    if (payStr(src.handle, '支付插件') !== String(existing.handle)) {
      throw new Error('已有支付接口的所属插件不可更改');
    }
    delete src.handle;
  }

  const map = {};
  if (Object.prototype.hasOwnProperty.call(src, 'name')) {
    const name = payStr(src.name, '支付名称');
    if (name === '' || name.length > 16 || /[\x00-\x1F\x7F<>]/.test(name)) {
      throw new Error('支付名称必须是 1–16 个不含 HTML 的字符');
    }
    map.name = name;
  }
  if (Object.prototype.hasOwnProperty.call(src, 'icon')) {
    const icon = payStr(src.icon, '支付图标');
    if (icon === '' || icon.length > 255 || /[\x00-\x20\x7F<>"']/.test(icon)
      || icon.startsWith('//') || !/^(?:\/|https?:\/\/)/i.test(icon)) {
      throw new Error('支付图标必须是站内绝对路径或 HTTP(S) 地址');
    }
    map.icon = icon;
  }
  if (Object.prototype.hasOwnProperty.call(src, 'handle')) {
    const handle = payStr(src.handle, '支付插件');
    if (!/^[A-Za-z][A-Za-z0-9_-]{0,63}$/.test(handle)) throw new Error('支付插件标识不正确');
    map.handle = handle;
  }
  if (Object.prototype.hasOwnProperty.call(src, 'code')) {
    const code = String(src.code == null ? '' : src.code);
    if (code.trim() === '' || code.length > 32 || /[\x00-\x1F\x7F<>"']/.test(code)) {
      throw new Error('支付方式代码不正确');
    }
    map.code = code;
  }
  for (const [field, label] of [['commodity', '商品下单状态'], ['recharge', '余额充值状态'], ['cost_type', '手续费模式']]) {
    if (Object.prototype.hasOwnProperty.call(src, field)) map[field] = payInt(src[field], label, 0, 1);
  }
  if (Object.prototype.hasOwnProperty.call(src, 'equipment')) map.equipment = payInt(src.equipment, '显示终端', 0, 2);
  if (Object.prototype.hasOwnProperty.call(src, 'sort')) map.sort = payInt(src.sort, '显示排序', 0, 65535);

  if (Object.prototype.hasOwnProperty.call(src, 'pay_config_id')) {
    const cid = payInt(src.pay_config_id, '支付配置', 0, 4294967295);
    const handle = String(map.handle ?? existing?.handle ?? '');
    if (cid > 0) {
      if (!(await payConfigBelongs(env, handle, cid))) throw new Error('支付配置不存在或不属于所选插件');
    }
    map.pay_config_id = cid;
  }
  if (Object.prototype.hasOwnProperty.call(src, 'cost')) {
    let cost = payStr(src.cost, '手续费');
    if (cost === '') cost = '0';
    if (!/^\d{1,7}(?:\.\d{1,3})?$/.test(cost) || Number(cost) > 9999999.999) {
      throw new Error('手续费必须是不超过 3 位小数的非负数');
    }
    map.cost = cost;
  }

  if (!existing) {
    for (const [field, label] of [['name', '支付名称'], ['icon', '支付图标'], ['handle', '支付插件'], ['code', '支付方式']]) {
      if (!Object.prototype.hasOwnProperty.call(map, field)) throw new Error(`请填写${label}`);
    }
    // 只补齐未传的字段默认值, 不能覆盖调用方已经传进来的值
    for (const [field, def] of Object.entries({
      commodity: 0, recharge: 0, sort: 0, equipment: 0, cost: '0', cost_type: 0, pay_config_id: 0,
    })) {
      if (!Object.prototype.hasOwnProperty.call(map, field)) map[field] = def;
    }
  }

  // 插件存在性校验: 新增或改 code 时校验(允许填插件没声明的 code, 原版也是放开的)
  if (!existing || Object.prototype.hasOwnProperty.call(map, 'code')) {
    const handle = String(map.handle ?? existing?.handle ?? '');
    if (!isPayPluginHandle(handle)) throw new Error('支付插件不存在');
  }

  const effCostType = Number(map.cost_type ?? existing?.cost_type ?? 0);
  const effCost = Number(map.cost ?? existing?.cost ?? 0);
  if (effCostType === 1 && effCost > 1) throw new Error('百分比手续费请使用 0–1 之间的小数');

  // 归档接口不允许重新启用, 避免出现「前台可用但列表看不见」的悬空状态
  if (existing && Number(existing.archived) === 1
    && (Number(map.commodity ?? 0) === 1 || Number(map.recharge ?? 0) === 1)) {
    throw new Error('该接口已归档，请先在「已归档」列表中恢复后再启用');
  }
  if (Object.keys(map).length === 0) throw new Error('没有可保存的支付接口字段');
  return map;
}

// 后台操作日志(与提现/商品等模块同一张 acg_manage_log, 失败不阻断主流程)
async function payLog(env, manage, request, content) {
  try {
    await dbInsert(env, 'acg_manage_log', {
      email: String(manage?.email || 'admin'), nickname: '', content: String(content || ''),
      create_time: now(), create_ip: requestInfo(request).ip, ua: '', risk: 0,
    });
  } catch (e) { /* 日志失败不影响主流程 */ }
}

// ---- 支付接口列表 ----
export async function payData(env, request, url, body = {}) {
  const b = { ...body };
  // 归档接口默认不出现在列表; 显式传 equal-archived=1 时才展示
  if (b['equal-archived'] === undefined || b['equal-archived'] === '') b['equal-archived'] = 0;
  const page = await queryListPage(env, {
    table: 'acg_pay', columns: PAY_COLUMNS, timeColumns: ['create_time'], body: b,
    // 原版 getOrderBy($map, "sort", "asc") —— 前台按 sort 升序展示
    defaultSort: 'sort', defaultRule: 'asc',
  });
  const list = page.list.map((r) => ({
    ...r,
    archived: Number(r.archived) || 0,
    commodity: Number(r.commodity) || 0,
    recharge: Number(r.recharge) || 0,
    create_time: dtString(r.create_time),
  }));
  return apiOk('success', { list, total: page.total, page: page.page, limit: page.limit });
}

// ---- 新增/修改支付接口 ----
export async function paySave(env, request, url, body = {}, manage) {
  const raw = body || {};
  const id = payId(raw.id ?? null);
  const created = id === 0;
  const existing = created ? null : await dbFirst(env, 'SELECT * FROM acg_pay WHERE id=?', id);
  if (!created && !existing) throw new Error('支付接口不存在');

  const map = await paySaveMap(env, raw, existing);
  if (created) {
    const row = { ...map, create_time: now() };
    await dbInsert(env, 'acg_pay', row);
    const saved = await dbFirst(env, 'SELECT id FROM acg_pay WHERE handle=? ORDER BY id DESC LIMIT 1', row.handle);
    await payLog(env, manage, request, `[新增]支付接口 ID：${saved ? saved.id : 0}`);
    return apiOk('（＾∀＾）保存成功', { id: saved ? saved.id : 0 });
  }
  const sets = Object.keys(map).map((k) => `${k}=?`).join(', ');
  await dbRun(env, `UPDATE acg_pay SET ${sets} WHERE id=?`, ...Object.values(map), id);
  await payLog(env, manage, request, `[修改]支付接口 ID：${id}`);
  return apiOk('（＾∀＾）保存成功', { id });
}

// ---- 删除影响分析(只读预览) ----
async function payDeleteImpactCalc(env, requestedIds) {
  if (!requestedIds.length) throw new Error('你还没有选择支付接口');
  const ph = requestedIds.map(() => '?').join(',');
  const pays = await dbRows(env,
    `SELECT id, name, commodity, recharge, archived FROM acg_pay WHERE id IN (${ph}) ORDER BY id`, ...requestedIds);
  const paymentIds = pays.map((p) => Number(p.id));

  const stat = async (table, column) => {
    if (!paymentIds.length) return new Map();
    const rows = await dbRows(env,
      `SELECT ${column} AS ref, COUNT(*) AS total, SUM(CASE WHEN status=1 THEN 1 ELSE 0 END) AS paid
       FROM ${table} WHERE ${column} IN (${paymentIds.map(() => '?').join(',')}) GROUP BY ${column}`,
      ...paymentIds);
    return new Map(rows.map((r) => [Number(r.ref), { total: Number(r.total) || 0, paid: Number(r.paid) || 0 }]));
  };
  const orderStats = await stat('acg_order', 'pay_id');
  const rechargeStats = await stat('acg_user_recharge', 'pay_id');

  const impact = {
    delete_ids: [], archive_ids: [], requested_count: requestedIds.length,
    payment_count: paymentIds.length, missing_count: requestedIds.length - paymentIds.length,
    names: pays.slice(0, 5).map((p) => String(p.name)),
    built_in_count: 0, order_count: 0, paid_order_count: 0, pending_order_count: 0,
    recharge_count: 0, paid_recharge_count: 0, pending_recharge_count: 0,
    commodity_enabled_count: 0, recharge_enabled_count: 0,
    delete_count: 0, delete_names: [], archive_count: 0, archive_names: [], already_archived_count: 0,
    can_proceed: false,
  };

  for (const p of pays) {
    const pid = Number(p.id);
    const orders = (orderStats.get(pid) || {}).total || 0;
    const recharges = (rechargeStats.get(pid) || {}).total || 0;
    const paidOrders = (orderStats.get(pid) || {}).paid || 0;
    const paidRecharges = (rechargeStats.get(pid) || {}).paid || 0;
    impact.order_count += orders;
    impact.paid_order_count += paidOrders;
    impact.pending_order_count += orders - paidOrders;
    impact.recharge_count += recharges;
    impact.paid_recharge_count += paidRecharges;
    impact.pending_recharge_count += recharges - paidRecharges;

    if (pid === 1) impact.built_in_count++;
    if (Number(p.commodity) === 1) impact.commodity_enabled_count++;
    if (Number(p.recharge) === 1) impact.recharge_enabled_count++;

    // 内置/仍启用的接口整体阻断, 不参与分组
    if (pid === 1 || Number(p.commodity) === 1 || Number(p.recharge) === 1) continue;
    if (orders === 0 && recharges === 0) {
      impact.delete_ids.push(pid);
      impact.delete_names.push(String(p.name));
    } else if (Number(p.archived) === 1) {
      impact.already_archived_count++;
    } else {
      impact.archive_ids.push(pid);
      impact.archive_names.push(String(p.name));
    }
  }
  impact.delete_count = impact.delete_ids.length;
  impact.archive_count = impact.archive_ids.length;
  impact.can_proceed = impact.missing_count === 0 && impact.built_in_count === 0
    && impact.commodity_enabled_count === 0 && impact.recharge_enabled_count === 0
    && (impact.delete_count + impact.archive_count) > 0;
  return impact;
}

export async function payDeleteImpact(env, request, url, body = {}) {
  const impact = await payDeleteImpactCalc(env, payIds(body.list ?? []));
  const { delete_ids, archive_ids, ...rest } = impact;      // 原版 unset 掉内部 id 列表
  return apiOk('success', rest);
}

export async function payDel(env, request, url, body = {}, manage) {
  const impact = await payDeleteImpactCalc(env, payIds(body.list ?? []));
  if (!impact.can_proceed) {
    if (impact.already_archived_count > 0 && impact.missing_count === 0 && impact.built_in_count === 0
      && impact.commodity_enabled_count === 0 && impact.recharge_enabled_count === 0) {
      throw new Error('所选接口均已归档，无需重复操作');
    }
    throw new Error(`已阻止操作：内置接口 ${impact.built_in_count} 个、不存在 ${impact.missing_count} 个、`
      + `仍启用商品下单 ${impact.commodity_enabled_count} 个、仍启用余额充值 ${impact.recharge_enabled_count} 个。请先停用接口再移除。`);
  }
  let deleted = 0;
  let archived = 0;
  if (impact.delete_ids.length) {
    const r = await dbRun(env,
      `DELETE FROM acg_pay WHERE id IN (${impact.delete_ids.map(() => '?').join(',')})
       AND id!=1 AND commodity=0 AND recharge=0`, ...impact.delete_ids);
    deleted = Number(r.meta?.changes ?? 0);
    if (deleted !== impact.delete_ids.length) throw new Error('支付接口状态或历史引用已变化，未执行操作，请重新预览');
  }
  if (impact.archive_ids.length) {
    const r = await dbRun(env,
      `UPDATE acg_pay SET archived=1 WHERE id IN (${impact.archive_ids.map(() => '?').join(',')})
       AND id!=1 AND commodity=0 AND recharge=0 AND archived=0`, ...impact.archive_ids);
    archived = Number(r.meta?.changes ?? 0);
    if (archived !== impact.archive_ids.length) throw new Error('支付接口状态或历史引用已变化，未执行操作，请重新预览');
  }
  await payLog(env, manage, request, `[移除]支付接口：物理删除 ${deleted} 个、归档 ${archived} 个`);
  const parts = [];
  if (deleted > 0) parts.push(`删除 ${deleted} 个`);
  if (archived > 0) parts.push(`归档 ${archived} 个`);
  return apiOk('（＾∀＾）已' + parts.join('、'), { deleted, archived });
}

export async function payRestore(env, request, url, body = {}, manage) {
  const ids = payIds(body.list ?? []);
  if (!ids.length) throw new Error('你还没有选择支付接口');
  const r = await dbRun(env, `UPDATE acg_pay SET archived=0 WHERE id IN (${ids.map(() => '?').join(',')}) AND archived=1`, ...ids);
  const count = Number(r.meta?.changes ?? 0);
  await payLog(env, manage, request, `[恢复]归档支付接口，共计：${count}`);
  return apiOk('（＾∀＾）已恢复，接口目前处于停用状态，可重新启用', { count });
}

// ============================================================
// 支付插件 —— 配置档存 D1, 元数据/表单定义来自 pay-plugins.js 注册表
// ============================================================
function parseCfg(json, fallback = {}) {
  if (json && typeof json === 'object') return json;
  try { const o = JSON.parse(String(json || '')); return o && typeof o === 'object' && !Array.isArray(o) ? o : fallback; }
  catch (e) { return fallback; }
}

// 配置档里的敏感值返回掩码, 与原版「密钥不再明文展示」一致
const MASKED_KEYS = ['key', 'secret', 'app_key', 'token', 'password'];
function maskPluginConfig(cfg) {
  const out = { ...cfg };
  for (const k of MASKED_KEYS) {
    if (typeof out[k] === 'string' && out[k] !== '') out[k] = '*'.repeat(Math.min(8, out[k].length));
  }
  return out;
}

// 默认档 = 该 handle 下 id 最小的那套(删除后新建支付接口就没得选, 原版同样拦住)
async function isDefaultProfile(env, handle, id) {
  const row = await dbFirst(env, 'SELECT id FROM acg_pay_config WHERE handle=? ORDER BY id ASC LIMIT 1', String(handle));
  return !!row && Number(row.id) === Number(id);
}

export async function payGetPlugins(env, request, url) {
  const rows = await dbRows(env, 'SELECT * FROM acg_pay_config ORDER BY handle ASC, sort ASC, id ASC');
  const byHandle = new Map();
  for (const r of rows) {
    if (!byHandle.has(r.handle)) byHandle.set(r.handle, []);
    byHandle.get(r.handle).push(r);
  }
  const list = [];
  for (const p of PAY_PLUGINS) {
    const profiles = byHandle.get(p.id) || [];
    // config.top 等插件级开关取默认档(该 handle 下 id 最小的那套)；
    // profiles 是按 handle/sort/id 排的, 新建的 sort=0 档可能排在最前, 不能直接取 [0]
    const defaultProfile = profiles.reduce(
      (min, r) => (min === null || Number(r.id) < Number(min.id) ? r : min), null);
    list.push({
      id: p.id,
      info: p.info,
      submit: p.submit,
      icon: '/favicon.ico',
      system: !!p.system,
      config: { top: 0, ...(defaultProfile ? parseCfg(defaultProfile.config) : {}) },
      have_update: false,
    });
  }
  // 原版排序: 置顶优先, 其次有更新优先
  list.sort((a, b) => (Number(b.config.top) || 0) - (Number(a.config.top) || 0));
  return apiOk('success', { list });
}

function payPluginHandle(body = {}) {
  const handle = body.handle ?? '';
  if (String(handle).trim() === '') throw new Error('插件不存在');
  if (!isPayPluginHandle(handle)) throw new Error('插件不存在');
  return String(handle).trim();
}

// 某插件的全部配置档 (对齐原版 Pay::listPluginConfigs)
export async function payGetPluginConfigs(env, request, url, body = {}) {
  const handle = payPluginHandle(body);
  const rows = await dbRows(env, 'SELECT * FROM acg_pay_config WHERE handle=? ORDER BY sort ASC, id ASC', handle);
  // in_use: 哪些支付接口在用这套配置(原版用于「这套配置正被 N 个接口使用」提示)
  const ids = rows.map((r) => Number(r.id));
  const usage = new Map();
  if (ids.length) {
    const used = await dbRows(env,
      `SELECT pay_config_id, id, name, icon FROM acg_pay WHERE pay_config_id IN (${ids.map(() => '?').join(',')}) ORDER BY id`,
      ...ids);
    for (const u of used) {
      if (!usage.has(Number(u.pay_config_id))) usage.set(Number(u.pay_config_id), []);
      usage.get(Number(u.pay_config_id)).push({ id: Number(u.id), name: String(u.name), icon: u.icon });
    }
  }
  // 默认档 = 该 handle 下 id 最小的那套(删除后新建支付接口就没得选, 原版同样拦住)
  // 注意不能拿 rows[0] 当默认: rows 是按 sort 排序的, 而新建档 sort=0 会排到最前,
  // 那样新档会冒充默认档, 真正的默认档反而变成可删的那个
  const oldest = rows.reduce((min, r) => (min === null || Number(r.id) < min ? Number(r.id) : min), null);
  return apiOk('success', {
    profiles: rows.map((r) => {
      const cfg = parseCfg(r.config);
      return {
        id: Number(r.id), handle: String(r.handle), name: String(r.name),
        is_default: Number(r.id) === oldest,
        sort: Number(r.sort) || 0,
        // 列表侧一律给掩码, 明文只在新建/改名这类不需要回显的场景下发
        config: maskPluginConfig(cfg),
        in_use: usage.get(Number(r.id)) || [],
      };
    }),
  });
}

export async function payCreatePluginConfig(env, request, url, body = {}, manage) {
  const handle = payPluginHandle(body);
  const name = payStr(body.name, '配置名称');
  if (name === '' || name.length > 16) throw new Error('配置名称必须是 1–16 个字符');
  if (await dbFirst(env, 'SELECT id FROM acg_pay_config WHERE handle=? AND name=?', handle, name)) {
    throw new Error('同名配置已存在');
  }
  const t = now();
  await dbInsert(env, 'acg_pay_config', { handle, name, config: '{}', sort: 0, create_time: t, update_time: t });
  const row = await dbFirst(env, 'SELECT id FROM acg_pay_config WHERE handle=? AND name=?', handle, name);
  await payLog(env, manage, request, `为支付插件(${handle})新增了配置档[${name}]`);
  return apiOk('添加成功', { id: row ? Number(row.id) : 0 });
}

export async function payRenamePluginConfig(env, request, url, body = {}, manage) {
  const handle = payPluginHandle(body);
  const id = payInt(body.config_id, '支付配置', 1, 4294967295);
  const name = payStr(body.name, '配置名称');
  if (name === '' || name.length > 16) throw new Error('配置名称必须是 1–16 个字符');
  const row = await dbFirst(env, 'SELECT id FROM acg_pay_config WHERE id=? AND handle=?', id, handle);
  if (!row) throw new Error('配置不存在');
  if (await dbFirst(env, 'SELECT id FROM acg_pay_config WHERE handle=? AND name=? AND id!=?', handle, name, id)) {
    throw new Error('同名配置已存在');
  }
  await dbRun(env, 'UPDATE acg_pay_config SET name=?, update_time=? WHERE id=?', name, now(), id);
  await payLog(env, manage, request, `把支付插件(${handle})的配置档#${id}改名为[${name}]`);
  return apiOk('修改成功');
}

export async function payDelPluginConfig(env, request, url, body = {}, manage) {
  const handle = payPluginHandle(body);
  const id = payInt(body.config_id, '支付配置', 1, 4294967295);
  const row = await dbFirst(env, 'SELECT id FROM acg_pay_config WHERE id=? AND handle=?', id, handle);
  if (!row) throw new Error('配置不存在');
  if (await isDefaultProfile(env, handle, id)) throw new Error('默认配置不能删除，新建支付接口需要它');
  const used = await dbFirst(env, 'SELECT id FROM acg_pay WHERE pay_config_id=?', id);
  if (used) throw new Error('该配置正被支付接口使用，需要先把它们改用其他配置');
  await dbRun(env, 'DELETE FROM acg_pay_config WHERE id=?', id);
  await payLog(env, manage, request, `删除了支付插件(${handle})的配置档#${id}`);
  return apiOk('删除成功');
}

// 写插件配置: 不传 config_id 就写默认档(保持插件列表页「配置」按钮的老行为)
export async function paySetPluginConfig(env, request, url, body = {}, manage) {
  const id = String(body.id ?? url.searchParams.get('id') ?? '').trim();
  if (id === '') throw new Error('插件不存在');
  if (!isPayPluginHandle(id)) throw new Error('插件不存在');
  const plugin = findPayPlugin(id);

  const raw = { ...body };
  delete raw.id;
  delete raw.config_id;

  // 只接受该插件 submit 里声明过的字段, 其余(数组型/嵌套)一律丢弃
  const allowed = new Set((plugin.submit || []).map((f) => f.name).filter(Boolean));
  const clean = {};
  for (const [k, v] of Object.entries(raw)) {
    if (!allowed.has(k)) continue;
    if (v === null || v === undefined) continue;
    if (typeof v === 'object') continue;
    clean[k] = String(v);
  }
  // 掩码回填: 前端把密钥显示成 ****, 原样提交等于没改, 这时保留原值
  const current = await dbFirst(env, 'SELECT * FROM acg_pay_config WHERE handle=? ORDER BY id ASC LIMIT 1', id);
  const oldCfg = current ? parseCfg(current.config) : {};
  for (const k of Object.keys(clean)) {
    if (MASKED_KEYS.includes(k) && /^\*+$/.test(clean[k])) delete clean[k];
  }
  const merged = { ...oldCfg, ...clean };

  const cidRaw = url.searchParams.get('config_id') ?? body.config_id;
  const cid = cidRaw !== null && cidRaw !== undefined && String(cidRaw).trim() !== ''
    ? payInt(cidRaw, '支付配置', 1, 4294967295) : null;

  if (cid) {
    const row = await dbFirst(env, 'SELECT id FROM acg_pay_config WHERE id=? AND handle=?', cid, id);
    if (!row) throw new Error('支付配置不存在或不属于所选插件');
    await dbRun(env, 'UPDATE acg_pay_config SET config=?, update_time=? WHERE id=?', JSON.stringify(merged), now(), cid);
    await payLog(env, manage, request, `修改了支付插件(${id})的配置信息[配置档#${cid}]`);
    return apiOk('修改成功');
  }
  if (current) {
    await dbRun(env, 'UPDATE acg_pay_config SET config=?, update_time=? WHERE id=?', JSON.stringify(merged), now(), current.id);
    await payLog(env, manage, request, `修改了支付插件(${id})的配置信息`);
    return apiOk('修改成功');
  }
  // 一套都没有就先建一套默认的(原版 openConfig 里同样是无感创建)
  const t = now();
  await dbInsert(env, 'acg_pay_config', { handle: id, name: '默认配置', config: JSON.stringify(merged), sort: 0, create_time: t, update_time: t });
  await payLog(env, manage, request, `修改了支付插件(${id})的配置信息`);
  return apiOk('修改成功');
}

// ---- 插件日志(原版写文件, 这里落 D1) ----
export async function payGetPluginLog(env, request, url, body = {}) {
  const handle = payPluginHandle(body);
  const row = await dbFirst(env, 'SELECT content FROM acg_pay_plugin_log WHERE handle=?', handle);
  return apiOk('success', { log: row ? String(row.content || '') : '' });
}

export async function payClearPluginLog(env, request, url, body = {}, manage) {
  const handle = payPluginHandle(body);
  await dbRun(env, 'DELETE FROM acg_pay_plugin_log WHERE handle=?', handle);
  await payLog(env, manage, request, `清空了支付插件(${handle})的日志`);
  return apiOk('success');
}

// 拨测: 需要真实网关, 本移植不接入第三方网络调用
export async function payTest(env, request, url, body = {}) {
  const pay = await dbFirst(env, 'SELECT * FROM acg_pay WHERE id=?', payId(body.id));
  if (!pay) throw new Error('支付接口不存在');
  if (Number(pay.id) === 1 || String(pay.handle) === '#system') {
    throw new Error('余额支付不经过第三方网关，无需拨测');
  }
  if (Number(pay.archived) === 1) throw new Error('已归档的接口无法拨测，请先恢复');
  throw new Error('拨测需要连接真实支付网关，本部署未启用第三方网关调用');
}

export async function adminEndpoint(env, request, url, ctl, act, body) {
  if (ctl === 'authentication' && act === 'login') return adminLogin(env, request, url, body);
  // 以下接口需要登录
  const manage = await authenticateManage(env, request);
  if (!manage) return apiErr('登录会话过期，请重新登录..');

  if (ctl === 'dashboard') {
    if (act === 'overview') return dashboardOverview(env, request, url);
    if (act === 'trend') return dashboardTrend(env, request, url, Number(url.searchParams.get('days')) || 0);
    if (act === 'data') return dashboardData(env, request, url, (body && body.type) || url.searchParams.get('type') || 0);
  }
  if (ctl === 'app' && act === 'ad') {
    return apiOk('ok', { title: '', content: '', url: '' });
  }
  // 分类
  if (ctl === 'category') {
    try {
      if (act === 'data') return await categoryData(env, request, url, body);
      if (act === 'save') return await categorySave(env, request, url, body);
      if (act === 'status') return await categoryStatus(env, request, url, body);
      if (act === 'reorder') return await categoryReorder(env, request, url, body);
      if (act === 'deleteImpact') return await categoryDeleteImpact(env, request, url, body, manage);
      if (act === 'del') return await categoryDel(env, request, url, body, manage);
    } catch (e) {
      return apiErr(e && e.message ? String(e.message) : '操作失败');
    }
  }
  // 商品
  if (ctl === 'commodity') {
    try {
      if (act === 'data') return await commodityData(env, request, url, body);
      if (act === 'save') return await commoditySave(env, request, url, body, manage);
      if (act === 'status') return await commodityStatus(env, request, url, body);
      if (act === 'del') return await commodityDel(env, request, url, body);
    } catch (e) {
      return apiErr(e && e.message ? String(e.message) : '操作失败');
    }
  }
  // 卡密
  if (ctl === 'card') {
    try {
      if (act === 'data') return await cardData(env, request, url, body);
      if (act === 'save') return await cardSave(env, request, url, body, manage);
      if (act === 'edit') return await cardEdit(env, request, url, body);
      if (act === 'lock') return await cardLock(env, request, url, body, true);
      if (act === 'unlock') return await cardLock(env, request, url, body, false);
      if (act === 'del') return await cardDel(env, request, url, body);
    } catch (e) {
      return apiErr(e && e.message ? String(e.message) : '操作失败');
    }
  }
  // 订单
  if (ctl === 'order') {
    try {
      if (act === 'data') return await orderData(env, request, url, body);
      if (act === 'save') return await orderSave(env, request, url, body);
      if (act === 'clear') return await orderClear(env, request, url, body);
      if (act === 'exportImpact') return await orderExportImpact(env, request, url, body, manage);
      if (act === 'export') return await orderExport(env, request, url, body, manage);
    } catch (e) {
      return apiErr(e && e.message ? String(e.message) : '操作失败');
    }
  }
  // 用户
  if (ctl === 'user') {
    try {
      if (act === 'data') return await userData(env, request, url, body);
      if (act === 'save') return await userSave(env, request, url, body);
      if (act === 'recharge') return await userRecharge(env, request, url, body, manage, 0);
      if (act === 'coin') return await userRecharge(env, request, url, body, manage, 1);
      if (act === 'statistics') return await userStatistics(env, request, url, body);
      if (act === 'del') return await userDel(env, request, url, body);
    } catch (e) {
      return apiErr(e && e.message ? String(e.message) : '操作失败');
    }
  }
  // 充值订单
  if (ctl === 'recharge') {
    try {
      if (act === 'data') return await rechargeData(env, request, url, body);
      if (act === 'success') return await rechargeSuccess(env, request, url, body);
      if (act === 'clear') return await rechargeClear(env, request, url, body);
    } catch (e) {
      return apiErr(e && e.message ? String(e.message) : '操作失败');
    }
  }
  // 优惠券
  if (ctl === 'coupon') {
    try {
      if (act === 'data') return await couponData(env, request, url, body);
      if (act === 'save') return await couponSave(env, request, url, body, manage);
      if (act === 'edit') return await couponEdit(env, request, url, body);
      if (act === 'lock') return await couponLock(env, request, url, body, true);
      if (act === 'unlock') return await couponLock(env, request, url, body, false);
      if (act === 'deleteImpact') return await couponDeleteImpact(env, request, url, body);
      if (act === 'del') return await couponDel(env, request, url, body, manage);
      if (act === 'exportImpact') return await couponExportImpact(env, request, url, body);
      if (act === 'export') return await couponExport(env, request, url, body, manage);
    } catch (e) {
      return apiErr(e && e.message ? String(e.message) : '操作失败');
    }
  }
  // �?
  if (ctl === 'ticket') {
    try {
      if (act === 'data') return await ticketData(env, request, url, body);
      if (act === 'detail') return await ticketDetail(env, request, url, body);
      if (act === 'messages') return await ticketMessages(env, request, url, body);
      if (act === 'reply') return await ticketReply(env, request, url, body, manage);
      if (act === 'close') return await ticketClose(env, request, url, body, manage);
      if (act === 'del') return await ticketDel(env, request, url, body, manage);
      if (act === 'badge') return await ticketBadge(env, request, url);
      if (act === 'upload') return await ticketUpload(env, request, url, body, manage);
    } catch (e) {
      return apiErr(e && e.message ? String(e.message) : '操作失败');
    }
  }
  // 系统消息
  if (ctl === 'message') {
    try {
      if (act === 'data') return await messageData(env, request, url, body);
      if (act === 'detail') return await messageDetail(env, request, url, body);
      if (act === 'save') return await messageSave(env, request, url, body, manage);
      if (act === 'del') return await messageDel(env, request, url, body, manage);
      if (act === 'users') return await messageUsers(env, request, url, body);
      if (act === 'audienceCount') return await messageAudienceCount(env, request, url, body);
      if (act === 'groups') {
        const groups = await dbRows(env, 'SELECT id, name, recharge FROM acg_user_group ORDER BY recharge DESC');
        return apiOk('success', { list: groups });
      }
      if (act === 'upload') return await messageUpload(env, request, url, body, manage);
    } catch (e) {
      return apiErr(e && e.message ? String(e.message) : '操作失败');
    }
  }
  if (ctl === 'cash') {
    try {
      if (act === 'data') return await cashData(env, request, url, body);
      if (act === 'decide') return await cashDecide(env, request, url, body, manage);
      if (act === 'settlement') return await cashSettlement(env, request, url, body, manage);
    } catch (e) {
      return apiErr(e && e.message ? String(e.message) : '操作失败');
    }
  }
  // 账单管理
  if (ctl === 'bill') {
    try {
      if (act === 'data') return await billData(env, request, url, body);
    } catch (e) {
      return apiErr(e && e.message ? String(e.message) : '操作失败');
    }
  }
  // 操作日志
  if (ctl === 'log') {
    try {
      if (act === 'data') return await logData(env, request, url, body);
    } catch (e) {
      return apiErr(e && e.message ? String(e.message) : '操作失败');
    }
  }
  // 支付接口 + 支付插件
  if (ctl === 'pay') {
    try {
      if (act === 'data') return await payData(env, request, url, body);
      if (act === 'save') return await paySave(env, request, url, body, manage);
      if (act === 'deleteImpact') return await payDeleteImpact(env, request, url, body);
      if (act === 'del') return await payDel(env, request, url, body, manage);
      if (act === 'restore') return await payRestore(env, request, url, body, manage);
      if (act === 'getPlugins') return await payGetPlugins(env, request, url);
      if (act === 'getPluginConfigs') return await payGetPluginConfigs(env, request, url, body);
      if (act === 'createPluginConfig') return await payCreatePluginConfig(env, request, url, body, manage);
      if (act === 'renamePluginConfig') return await payRenamePluginConfig(env, request, url, body, manage);
      if (act === 'delPluginConfig') return await payDelPluginConfig(env, request, url, body, manage);
      if (act === 'setPluginConfig') return await paySetPluginConfig(env, request, url, body, manage);
      if (act === 'getPluginLog') return await payGetPluginLog(env, request, url, body);
      if (act === 'ClearPluginLog') return await payClearPluginLog(env, request, url, body, manage);
      if (act === 'test') return await payTest(env, request, url, body);
      if (act === 'testState') return apiOk('success', { state: 0, msg: '拨测未启用' });
    } catch (e) {
      return apiErr(e && e.message ? String(e.message) : '操作失败');
    }
  }
  return apiErr('接口不存在', 404);
}