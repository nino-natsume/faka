// ============================================================
// acg-faka Worker 移植 - lib.js (公共内核)
// 对齐原版 Kernel: 密码算法/路由/会话/配置/DB 封装/模板渲染基础
// ============================================================

// ---------- 基础工具 ----------
export const now = () => Math.floor(Date.now() / 1000);

export const randStr = (len = 32) => {
  const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  let s = '';
  const arr = new Uint32Array(len);
  crypto.getRandomValues(arr);
  for (let i = 0; i < len; i++) s += chars[arr[i] % chars.length];
  return s;
};

export const htmlEscape = (s) => String(s == null ? '' : s)
  .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
  .replace(/"/g, '&quot;').replace(/'/g, '&#039;');

const enc = new TextEncoder();
const dec = new TextDecoder();

// ---------- MD5 (纯 JS, RFC1321, Blueimp 风格实现) ----------
function md5Add(x, y) { return (x + y) & 0xffffffff; }
function md5Rotl(num, cnt) { return (num << cnt) | (num >>> (32 - cnt)); }
function md5Cmn(q, a, b, x, s, t) {
  a = md5Add(a, q);
  a = md5Add(a, md5Add(x, t));
  return md5Add((a << s) | (a >>> (32 - s)), b);
}
function md5Ff(a, b, c, d, x, s, t) { return md5Cmn((b & c) | (~b & d), a, b, x, s, t); }
function md5Gg(a, b, c, d, x, s, t) { return md5Cmn((b & d) | (c & ~d), a, b, x, s, t); }
function md5Hh(a, b, c, d, x, s, t) { return md5Cmn(b ^ c ^ d, a, b, x, s, t); }
function md5Ii(a, b, c, d, x, s, t) { return md5Cmn(c ^ (b | ~d), a, b, x, s, t); }

export function md5hex(input) {
  const bytes = typeof input === 'string' ? Array.from(enc.encode(input)) : Array.from(input);
  const bitLen = bytes.length * 8;

  const padded = bytes.slice();
  padded.push(0x80);
  while (padded.length % 64 !== 56) padded.push(0);
  const lo = bitLen >>> 0, hi = Math.floor(bitLen / 0x100000000) >>> 0;
  padded.push(lo & 0xff, (lo >>> 8) & 0xff, (lo >>> 16) & 0xff, (lo >>> 24) & 0xff);
  padded.push(hi & 0xff, (hi >>> 8) & 0xff, (hi >>> 16) & 0xff, (hi >>> 24) & 0xff);

  let a0 = 0x67452301, b0 = 0xefcdab89, c0 = 0x98badcfe, d0 = 0x10325476;
  const X = new Array(16);

  const S = [
    7, 12, 17, 22, 7, 12, 17, 22, 7, 12, 17, 22, 7, 12, 17, 22,
    5, 9, 14, 20, 5, 9, 14, 20, 5, 9, 14, 20, 5, 9, 14, 20,
    4, 11, 16, 23, 4, 11, 16, 23, 4, 11, 16, 23, 4, 11, 16, 23,
    6, 10, 15, 21, 6, 10, 15, 21, 6, 10, 15, 21, 6, 10, 15, 21,
  ];
  const T = [];
  for (let j = 0; j < 64; j++) T[j] = Math.floor(Math.abs(Math.sin(j + 1)) * 0x100000000);

  for (let off = 0; off < padded.length; off += 64) {
    for (let j = 0; j < 16; j++) {
      X[j] = padded[off + j * 4] | (padded[off + j * 4 + 1] << 8) | (padded[off + j * 4 + 2] << 16) | (padded[off + j * 4 + 3] << 24);
    }
    let A = a0, B = b0, C = c0, D = d0;

    A = md5Ff(A, B, C, D, X[0], S[0], T[0]); D = md5Ff(D, A, B, C, X[1], S[1], T[1]); C = md5Ff(C, D, A, B, X[2], S[2], T[2]); B = md5Ff(B, C, D, A, X[3], S[3], T[3]); A = md5Ff(A, B, C, D, X[4], S[4], T[4]); D = md5Ff(D, A, B, C, X[5], S[5], T[5]); C = md5Ff(C, D, A, B, X[6], S[6], T[6]); B = md5Ff(B, C, D, A, X[7], S[7], T[7]); A = md5Ff(A, B, C, D, X[8], S[8], T[8]); D = md5Ff(D, A, B, C, X[9], S[9], T[9]); C = md5Ff(C, D, A, B, X[10], S[10], T[10]); B = md5Ff(B, C, D, A, X[11], S[11], T[11]); A = md5Ff(A, B, C, D, X[12], S[12], T[12]); D = md5Ff(D, A, B, C, X[13], S[13], T[13]); C = md5Ff(C, D, A, B, X[14], S[14], T[14]); B = md5Ff(B, C, D, A, X[15], S[15], T[15]);
    A = md5Gg(A, B, C, D, X[1], S[16], T[16]); D = md5Gg(D, A, B, C, X[6], S[17], T[17]); C = md5Gg(C, D, A, B, X[11], S[18], T[18]); B = md5Gg(B, C, D, A, X[0], S[19], T[19]); A = md5Gg(A, B, C, D, X[5], S[20], T[20]); D = md5Gg(D, A, B, C, X[10], S[21], T[21]); C = md5Gg(C, D, A, B, X[15], S[22], T[22]); B = md5Gg(B, C, D, A, X[4], S[23], T[23]); A = md5Gg(A, B, C, D, X[9], S[24], T[24]); D = md5Gg(D, A, B, C, X[14], S[25], T[25]); C = md5Gg(C, D, A, B, X[3], S[26], T[26]); B = md5Gg(B, C, D, A, X[8], S[27], T[27]); A = md5Gg(A, B, C, D, X[13], S[28], T[28]); D = md5Gg(D, A, B, C, X[2], S[29], T[29]); C = md5Gg(C, D, A, B, X[7], S[30], T[30]); B = md5Gg(B, C, D, A, X[12], S[31], T[31]);
    A = md5Hh(A, B, C, D, X[5], S[32], T[32]); D = md5Hh(D, A, B, C, X[8], S[33], T[33]); C = md5Hh(C, D, A, B, X[11], S[34], T[34]); B = md5Hh(B, C, D, A, X[14], S[35], T[35]); A = md5Hh(A, B, C, D, X[1], S[36], T[36]); D = md5Hh(D, A, B, C, X[4], S[37], T[37]); C = md5Hh(C, D, A, B, X[7], S[38], T[38]); B = md5Hh(B, C, D, A, X[10], S[39], T[39]); A = md5Hh(A, B, C, D, X[13], S[40], T[40]); D = md5Hh(D, A, B, C, X[0], S[41], T[41]); C = md5Hh(C, D, A, B, X[3], S[42], T[42]); B = md5Hh(B, C, D, A, X[6], S[43], T[43]); A = md5Hh(A, B, C, D, X[9], S[44], T[44]); D = md5Hh(D, A, B, C, X[12], S[45], T[45]); C = md5Hh(C, D, A, B, X[15], S[46], T[46]); B = md5Hh(B, C, D, A, X[2], S[47], T[47]);
    A = md5Ii(A, B, C, D, X[0], S[48], T[48]); D = md5Ii(D, A, B, C, X[7], S[49], T[49]); C = md5Ii(C, D, A, B, X[14], S[50], T[50]); B = md5Ii(B, C, D, A, X[5], S[51], T[51]); A = md5Ii(A, B, C, D, X[12], S[52], T[52]); D = md5Ii(D, A, B, C, X[3], S[53], T[53]); C = md5Ii(C, D, A, B, X[10], S[54], T[54]); B = md5Ii(B, C, D, A, X[1], S[55], T[55]); A = md5Ii(A, B, C, D, X[8], S[56], T[56]); D = md5Ii(D, A, B, C, X[15], S[57], T[57]); C = md5Ii(C, D, A, B, X[6], S[58], T[58]); B = md5Ii(B, C, D, A, X[13], S[59], T[59]); A = md5Ii(A, B, C, D, X[4], S[60], T[60]); D = md5Ii(D, A, B, C, X[11], S[61], T[61]); C = md5Ii(C, D, A, B, X[2], S[62], T[62]); B = md5Ii(B, C, D, A, X[9], S[63], T[63]);

    a0 = md5Add(a0, A); b0 = md5Add(b0, B); c0 = md5Add(c0, C); d0 = md5Add(d0, D);
  }

  // MD5 摘要输出约定: 每个 32bit word 按 little-endian 字节序 (低字节在前)
  const b = (n) => [n & 0xff, (n >>> 8) & 0xff, (n >>> 16) & 0xff, (n >>> 24) & 0xff];
  return [...b(a0), ...b(b0), ...b(c0), ...b(d0)].map(x => x.toString(16).padStart(2, '0')).join('');
}

// ---------- SHA1 (WebCrypto 支持) ----------
export async function sha1hex(input) {
  const buf = typeof input === 'string' ? enc.encode(input) : input;
  const digest = await crypto.subtle.digest('SHA-1', buf);
  return [...new Uint8Array(digest)].map(b => b.toString(16).padStart(2, '0')).join('');
}

// ---------- SHA256 / HMAC (JWT) ----------
async function hmacSha256(key, data) {
  const k = await crypto.subtle.importKey('raw', enc.encode(key), { name: 'HMAC', hash: 'SHA-256' }, false, ['sign']);
  const sig = await crypto.subtle.sign('HMAC', k, enc.encode(data));
  return new Uint8Array(sig);
}

const b64url = (buf) => btoa(String.fromCharCode(...new Uint8Array(buf)))
  .replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');

function b64urlDecode(str) {
  const s = str.replace(/-/g, '+').replace(/_/g, '/');
  const pad = s.length % 4 === 0 ? '' : '='.repeat(4 - (s.length % 4));
  const bin = atob(s + pad);
  return dec.decode(Uint8Array.from(bin, c => c.charCodeAt(0)));
}

function b64urlDecodeBytes(str) {
  const s = str.replace(/-/g, '+').replace(/_/g, '/');
  const pad = s.length % 4 === 0 ? '' : '='.repeat(4 - (s.length % 4));
  const bin = atob(s + pad);
  return Uint8Array.from(bin, c => c.charCodeAt(0));
}

// ---------- JWT HS256 ----------
export async function jwtSign(payload, secret, ttl = 604800) {
  const header = { alg: 'HS256', typ: 'JWT' };
  const nowT = Math.floor(Date.now() / 1000);
  const body = { ...payload, iat: nowT, exp: nowT + ttl };
  const h = b64url(enc.encode(JSON.stringify(header)));
  const p = b64url(enc.encode(JSON.stringify(body)));
  const sig = await hmacSha256(secret, `${h}.${p}`);
  return `${h}.${p}.${b64url(sig)}`;
}

export async function jwtVerify(token, secret) {
  try {
    const parts = token.split('.');
    if (parts.length !== 3) return null;
    const expected = await hmacSha256(secret, `${parts[0]}.${parts[1]}`);
    const got = b64urlDecodeBytes(parts[2]);
    const exp = new Uint8Array(expected);
    if (exp.length !== got.length) return null;
    let diff = 0;
    for (let i = 0; i < exp.length; i++) diff |= exp[i] ^ got[i];
    if (diff !== 0) return null;
    const payload = JSON.parse(b64urlDecode(parts[1]));
    if (payload.exp && payload.exp < Math.floor(Date.now() / 1000)) return null;
    return payload;
  } catch (e) { return null; }
}

// ---------- 原版密码算法 ----------
// Str::generatePassword = sha1(md5(md5($pass) . md5($salt)))
export async function generatePassword(pass, salt) {
  const h1 = md5hex(String(pass));
  const h2 = md5hex(String(salt));
  return sha1hex(md5hex(h1 + h2));
}

export async function verifyPassword(stored, pass, salt) {
  const cur = await generatePassword(pass, salt);
  return cur === stored;
}

// ---------- DB 封装 (D1) ----------
export const dbRows = async (env, sql, ...params) => {
  const stmt = env.DB.prepare(sql);
  const res = params.length ? await stmt.bind(...params).all() : await stmt.all();
  return res.results || [];
};

export const dbFirst = async (env, sql, ...params) => {
  const rows = await dbRows(env, sql, ...params);
  return rows[0] || null;
};

export const dbRun = async (env, sql, ...params) => {
  const stmt = env.DB.prepare(sql);
  return params.length ? await stmt.bind(...params).run() : await stmt.run();
};

const dbLastId = async (env) => {
  const r = await dbFirst(env, 'SELECT last_insert_rowid() AS id');
  return r ? r.id : 0;
};

// 简易 insert: {table, data} -> id
export const dbInsert = async (env, table, data) => {
  const keys = Object.keys(data);
  const cols = keys.map(k => `"${k}"`).join(',');
  const marks = keys.map(() => '?').join(',');
  await dbRun(env, `INSERT INTO ${table} (${cols}) VALUES (${marks})`, ...keys.map(k => data[k]));
  return dbLastId(env);
};

// 简易 update: {table, data, where}
export const dbUpdate = async (env, table, data, whereSql, ...whereParams) => {
  const sets = Object.keys(data).map(k => `"${k}"=?`).join(',');
  await dbRun(env, `UPDATE ${table} SET ${sets} WHERE ${whereSql}`, ...Object.values(data), ...whereParams);
};

// ---------- 配置 ----------
let configCache = null;
export async function loadConfig(env) {
  if (configCache) return configCache;
  const rows = await dbRows(env, 'SELECT key, value FROM acg_config');
  const cfg = {};
  for (const r of rows) cfg[r.key] = r.value;
  cfg.currency_symbol = cfg.currency_symbol || '¥';
  configCache = cfg;
  return cfg;
}
export const resetConfigCache = () => { configCache = null; };

// ---------- 简单数值 ----------
export const dec2 = (n) => String(Math.round((Number(n) || 0) * 100) / 100);

// ---------- 分类树 (对齐 Tree::generate 平层->树) ----------
export function buildCategoryTree(list) {
  const map = {};
  const roots = [];
  list.forEach(c => map[c.id] = { ...c });
  list.forEach(c => {
    const pid = c.pid ? Number(c.pid) : 0;
    if (pid && map[pid]) {
      (map[pid].children ||= []).push(map[c.id]);
    } else {
      roots.push(map[c.id]);
    }
  });
  const sortDeep = (arr) => {
    arr.sort((a, b) => (Number(a.sort) || 0) - (Number(b.sort) || 0));
    arr.forEach(n => n.children && sortDeep(n.children));
    return arr;
  };
  return sortDeep(roots);
}

// ---------- Cookie 解析 ----------
export function parseCookies(header) {
  const out = {};
  if (!header) return out;
  header.split(';').forEach(p => {
    const i = p.indexOf('=');
    if (i < 0) return;
    const k = p.slice(0, i).trim();
    const v = decodeURIComponent(p.slice(i + 1).trim());
    if (k) out[k] = v;
  });
  return out;
}

// ---------- 请求信息 ----------
export function requestInfo(request, url) {
  const ua = request.headers.get('user-agent') || '';
  const cf = request.cf || {};
  const ip = (cf && (cf.connectingIp || cf.ip)) ||
    (request.headers.get('cf-connecting-ip')) ||
    (request.headers.get('x-forwarded-for') || '').split(',')[0].trim() || '127.0.0.1';
  const isMobile = /Mobi|Android|iPhone|iPad|iPod/i.test(ua);
  return { ua, ip, isMobile };
}

// ---------- Cookie 设置 ----------
export function setCookie(name, value, opts = {}) {
  let s = `${name}=${encodeURIComponent(value)}; Path=/; HttpOnly; SameSite=Lax`;
  if (opts.maxAge != null) s += `; Max-Age=${opts.maxAge}`;
  if (opts.secure) s += '; Secure';
  return s;
}

// ---------- 视图工具 ----------
export async function getCurrentUser(env, request, cfg) {
  // 原版: USER_SESSION JWT HS256, 密钥 = 用户密码; 本阶段用户体系未接入时返回 null
  return null;
}

export async function userHeaderNav() {
  return [
    { name: '首页', url: '/', match: '/', target: '_self' },
    { name: '公告', url: '/', match: '/', target: '_self' },
  ];
}

// 语言菜单(简化, 单语言)
export function langMenu() {
  return [{ code: 'zh-cn', name: '简体中文' }];
}

export function langDictScript() {
  return '';
}

// index_var(): 注入前端 JS 全局变量 (对齐 Helper::index_var)
export function indexVar(catId, cfg) {
  const data = {
    DEBUG: false,
    LANG: 'zh-cn',
    LANGS: langMenu(),
    CURRENCY: { code: cfg.currency_code || 'CNY', symbol: cfg.currency_symbol || '¥', rate: Number(cfg.currency_rate || 1), decimals: Number(cfg.currency_decimals || 2) },
    CAT_ID: Number(catId) || 0,
  };
  return `<script>window._data_var=${JSON.stringify(data)};</script>${langDictScript()}`;
}

export function itemVar(item) {
  return `<script>window._data_var._var_item=${JSON.stringify(item)};</script>`;
}

// ---------- 交易号 (对齐 Str::generateTradeNo: 18 位随机数字) ----------
export function generateTradeNo() {
  let s = String(1 + Math.floor(Math.random() * 9));
  for (let i = 0; i < 17; i++) s += String(Math.floor(Math.random() * 10));
  return s;
}

// ---------- 定点两位小数 ----------
export class Decimal {
  constructor(v) {
    this.cent = Math.round((Number(v) || 0) * 100);
  }
  add(o) { return new Decimal((this.cent + new Decimal(o).cent) / 100); }
  sub(o) { return new Decimal((this.cent - new Decimal(o).cent) / 100); }
  mul(o) { return new Decimal((this.cent * new Decimal(o).cent) / 10000); }
  div(o) { const d = new Decimal(o).cent; return d === 0 ? new Decimal(0) : new Decimal(this.cent / d); }
  getAmount() { return (this.cent / 100).toFixed(2); }
}

// ---------- 易支付签名 (标准: k=v&...&key=密钥 后 md5) ----------
export function epaySign(params, key) {
  const keys = Object.keys(params).filter(k => k !== 'sign' && k !== 'sign_type' && params[k] !== '' && params[k] != null).sort();
  const str = keys.map(k => `${k}=${params[k]}`).join('&') + `&key=${key}`;
  return md5hex(str);
}

// ---------- 会员组 (对齐 UserGroup::get: 充值额向下取最近等级) ----------
export async function getUserGroup(env, recharge) {
  const rows = await dbRows(env, 'SELECT * FROM acg_user_group ORDER BY recharge ASC');
  let group = null;
  for (const r of rows) {
    if ((Number(recharge) || 0) >= Number(r.recharge)) group = r;
  }
  return group;
}

// ---------- 余额流水 (对齐 Bill::create) ----------
const BILL_TYPE_ADD = 1, BILL_TYPE_SUB = 2;
export async function billCreate(env, userId, amount, log, type) {
  const user = await dbFirst(env, 'SELECT id, balance FROM acg_user WHERE id=?', userId);
  if (!user) throw new Error('用户不存在');
  const delta = type === BILL_TYPE_ADD ? Number(amount) : -Number(amount);
  const balance = Math.round(((Number(user.balance) || 0) + delta) * 100) / 100;
  await dbRun(env, 'UPDATE acg_user SET balance=? WHERE id=?', balance, userId);
  await dbInsert(env, 'acg_bill', {
    owner: userId, amount: Number(amount), balance, type, currency: 0, log, create_time: now(),
  });
  return balance;
}
export const BILL = { ADD: BILL_TYPE_ADD, SUB: BILL_TYPE_SUB };

// ---------- 用户会话 (对齐 UserSSO: JWT header=uid, key=用户密码, 整体 base64) ----------
export async function userSessionCookie(user, remember = false, cfg = {}) {
  const sessionExpire = Number(cfg.session_expire) > 0 ? Number(cfg.session_expire) : 604800;
  const ttl = remember ? 86400 * 365 : sessionExpire;
  const payload = { expire: now() + ttl, loginTime: now() };
  const jwt = await jwtSignHeader(payload, user.password, { uid: user.id });
  // base64_encode(jwt)
  return { value: btoa(UTF8.encode(jwt)), maxAge: ttl, raw: jwt, payload };
}

export async function jwtSignHeader(payload, secret, headerExtra = {}, ttl) {
  const header = { alg: 'HS256', typ: 'JWT', ...headerExtra };
  const nowT = Math.floor(Date.now() / 1000);
  let body;
  if (payload.expire) {
    body = payload;
  } else {
    body = { ...payload, iat: nowT, exp: nowT + (ttl || 604800) };
  }
  const h = b64url(enc.encode(JSON.stringify(header)));
  const p = b64url(enc.encode(JSON.stringify(body)));
  const sig = await hmacSha256(secret, `${h}.${p}`);
  return `${h}.${p}.${b64url(sig)}`;
}

export async function verifyUserSession(env, cookieValue) {
  try {
    let raw = decodeURIComponent(cookieValue);
    const jwt = UTF8.decode(atob(raw));
    const parts = jwt.split('.');
    if (parts.length !== 3) return null;
    const header = JSON.parse(b64urlDecode(parts[0]));
    const uid = header.uid;
    if (!uid) return null;
    const user = await dbFirst(env, 'SELECT * FROM acg_user WHERE id=?', uid);
    if (!user) return null;
    const payload = await jwtVerify(jwt, user.password);
    if (!payload) return null;
    if (payload.expire && payload.expire < now()) return null;
    return user;
  } catch (e) { return null; }
}

export const UTF8 = {
  encode: (s) => { const b = enc.encode(s); return String.fromCharCode(...b); },
  decode: (s) => dec.decode(Uint8Array.from(s, c => c.charCodeAt(0))),
};

// 轻量内存限流 (per-isolate, 供登录等使用)
const throttleMap = new Map();
export function throttle(key, limit, windowSec) {
  const t = now();
  const rec = throttleMap.get(key) || { count: 0, reset: t + windowSec };
  if (t > rec.reset) { rec.count = 0; rec.reset = t + windowSec; }
  rec.count++;
  throttleMap.set(key, rec);
  if (throttleMap.size > 5000) { for (const k of throttleMap.keys()) { if (throttleMap.get(k).reset < t) throttleMap.delete(k); } }
  return rec.count > limit;
}
export function throttleClear(key) { throttleMap.delete(key); }