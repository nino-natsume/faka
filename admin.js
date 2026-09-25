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
  loadConfig, dbRows, dbFirst, dbRun, dbInsert,
  generatePassword, verifyPassword, throttle, throttleClear,
} from './lib.js';
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
  return apiErr('接口不存在', 404);
}