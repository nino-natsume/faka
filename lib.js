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

// ---------- MD5 (纯 JS, RFC1321) ----------
const MD5_PAD = new Uint8Array(64);
export function md5hex(input) {
  const bytes = typeof input === 'string' ? enc.encode(input) : input;
  const s = new Uint8Array(bytes.length + 9);
  s.set(bytes);

  // 追加 0x80 与 0 填充
  let i = bytes.length;
  s[i++] = 0x80;
  while (i % 64 !== 56) s[i++] = 0;
  const bitLen = bytes.length * 8;
  const dv = new DataView(s.buffer);
  dv.setUint32(s.length - 8, bitLen >>> 0, true);
  dv.setUint32(s.length - 4, Math.floor(bitLen / 0x100000000), true);

  let a0 = 0x67452301, b0 = 0xefcdab89, c0 = 0x98badcfe, d0 = 0x10325476;

  const rotl = (x, c) => (x << c) | (x >>> (32 - c));
  const K = new Uint32Array(64);
  for (let j = 0; j < 64; j++) K[j] = Math.floor(Math.abs(Math.sin(j + 1)) * 0x100000000);

  for (let off = 0; off < s.length; off += 64) {
    const M = new DataView(s.buffer, off, 64);
    const X = new Uint32Array(16);
    for (let j = 0; j < 16; j++) X[j] = M.getUint32(j * 4, true);

    let A = a0, B = b0, C = c0, D = d0;
    const F = (j, x, y, z) => j < 16 ? ((y ^ z) & x) ^ z : j < 32 ? (z & y) | (~z & x) : j < 48 ? x ^ y ^ z : y ^ (x | ~z);
    const g = (j) => j < 16 ? j : j < 32 ? (5 * j + 1) % 16 : j < 48 ? (3 * j + 5) % 16 : (7 * j) % 16;

    for (let j = 0; j < 64; j++) {
      const Fv = F(j, A, B, C);
      const tmp = D;
      D = C;
      C = B;
      B = B + rotl((A + Fv + K[j] + X[g(j)]) >>> 0, [7, 12, 17, 22, 5, 9, 14, 20, 4, 11, 16, 23, 6, 10, 15, 21][Math.floor(j / 4) % 16]);
      A = tmp;
    }
    a0 = (a0 + A) >>> 0; b0 = (b0 + B) >>> 0; c0 = (c0 + C) >>> 0; d0 = (d0 + D) >>> 0;
  }

  const hex = (n) => (n >>> 0).toString(16).padStart(8, '0');
  return hex(a0) + hex(b0) + hex(c0) + hex(d0);
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
    const got = b64urlDecode(parts[2]);
    const exp = new Uint8Array(expected);
    const g = new Uint8Array(enc.encode(got));
    if (exp.length !== g.length) return null;
    let diff = 0;
    for (let i = 0; i < exp.length; i++) diff |= exp[i] ^ g[i];
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