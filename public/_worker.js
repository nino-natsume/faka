// DCSHOP faka - Pages 单文件入口 (由 worker.js+admin.js+lib.js 自动打包生成, 勿手改)

// lib.js
var now = () => Math.floor(Date.now() / 1e3);
var randStr = (len = 32) => {
  const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  let s = "";
  const arr = new Uint32Array(len);
  crypto.getRandomValues(arr);
  for (let i = 0; i < len; i++) s += chars[arr[i] % chars.length];
  return s;
};
var htmlEscape = (s) => String(s == null ? "" : s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
var enc = new TextEncoder();
var dec = new TextDecoder();
function md5Add(x, y) {
  return x + y & 4294967295;
}
function md5Cmn(q, a, b, x, s, t) {
  a = md5Add(a, q);
  a = md5Add(a, md5Add(x, t));
  return md5Add(a << s | a >>> 32 - s, b);
}
function md5Ff(a, b, c, d, x, s, t) {
  return md5Cmn(b & c | ~b & d, a, b, x, s, t);
}
function md5Gg(a, b, c, d, x, s, t) {
  return md5Cmn(b & d | c & ~d, a, b, x, s, t);
}
function md5Hh(a, b, c, d, x, s, t) {
  return md5Cmn(b ^ c ^ d, a, b, x, s, t);
}
function md5Ii(a, b, c, d, x, s, t) {
  return md5Cmn(c ^ (b | ~d), a, b, x, s, t);
}
function md5hex(input) {
  const bytes = typeof input === "string" ? Array.from(enc.encode(input)) : Array.from(input);
  const bitLen = bytes.length * 8;
  const padded = bytes.slice();
  padded.push(128);
  while (padded.length % 64 !== 56) padded.push(0);
  const lo = bitLen >>> 0, hi = Math.floor(bitLen / 4294967296) >>> 0;
  padded.push(lo & 255, lo >>> 8 & 255, lo >>> 16 & 255, lo >>> 24 & 255);
  padded.push(hi & 255, hi >>> 8 & 255, hi >>> 16 & 255, hi >>> 24 & 255);
  let a0 = 1732584193, b0 = 4023233417, c0 = 2562383102, d0 = 271733878;
  const X = new Array(16);
  const S = [
    7,
    12,
    17,
    22,
    7,
    12,
    17,
    22,
    7,
    12,
    17,
    22,
    7,
    12,
    17,
    22,
    5,
    9,
    14,
    20,
    5,
    9,
    14,
    20,
    5,
    9,
    14,
    20,
    5,
    9,
    14,
    20,
    4,
    11,
    16,
    23,
    4,
    11,
    16,
    23,
    4,
    11,
    16,
    23,
    4,
    11,
    16,
    23,
    6,
    10,
    15,
    21,
    6,
    10,
    15,
    21,
    6,
    10,
    15,
    21,
    6,
    10,
    15,
    21
  ];
  const T = [];
  for (let j = 0; j < 64; j++) T[j] = Math.floor(Math.abs(Math.sin(j + 1)) * 4294967296);
  for (let off = 0; off < padded.length; off += 64) {
    for (let j = 0; j < 16; j++) {
      X[j] = padded[off + j * 4] | padded[off + j * 4 + 1] << 8 | padded[off + j * 4 + 2] << 16 | padded[off + j * 4 + 3] << 24;
    }
    let A = a0, B = b0, C = c0, D = d0;
    A = md5Ff(A, B, C, D, X[0], S[0], T[0]);
    D = md5Ff(D, A, B, C, X[1], S[1], T[1]);
    C = md5Ff(C, D, A, B, X[2], S[2], T[2]);
    B = md5Ff(B, C, D, A, X[3], S[3], T[3]);
    A = md5Ff(A, B, C, D, X[4], S[4], T[4]);
    D = md5Ff(D, A, B, C, X[5], S[5], T[5]);
    C = md5Ff(C, D, A, B, X[6], S[6], T[6]);
    B = md5Ff(B, C, D, A, X[7], S[7], T[7]);
    A = md5Ff(A, B, C, D, X[8], S[8], T[8]);
    D = md5Ff(D, A, B, C, X[9], S[9], T[9]);
    C = md5Ff(C, D, A, B, X[10], S[10], T[10]);
    B = md5Ff(B, C, D, A, X[11], S[11], T[11]);
    A = md5Ff(A, B, C, D, X[12], S[12], T[12]);
    D = md5Ff(D, A, B, C, X[13], S[13], T[13]);
    C = md5Ff(C, D, A, B, X[14], S[14], T[14]);
    B = md5Ff(B, C, D, A, X[15], S[15], T[15]);
    A = md5Gg(A, B, C, D, X[1], S[16], T[16]);
    D = md5Gg(D, A, B, C, X[6], S[17], T[17]);
    C = md5Gg(C, D, A, B, X[11], S[18], T[18]);
    B = md5Gg(B, C, D, A, X[0], S[19], T[19]);
    A = md5Gg(A, B, C, D, X[5], S[20], T[20]);
    D = md5Gg(D, A, B, C, X[10], S[21], T[21]);
    C = md5Gg(C, D, A, B, X[15], S[22], T[22]);
    B = md5Gg(B, C, D, A, X[4], S[23], T[23]);
    A = md5Gg(A, B, C, D, X[9], S[24], T[24]);
    D = md5Gg(D, A, B, C, X[14], S[25], T[25]);
    C = md5Gg(C, D, A, B, X[3], S[26], T[26]);
    B = md5Gg(B, C, D, A, X[8], S[27], T[27]);
    A = md5Gg(A, B, C, D, X[13], S[28], T[28]);
    D = md5Gg(D, A, B, C, X[2], S[29], T[29]);
    C = md5Gg(C, D, A, B, X[7], S[30], T[30]);
    B = md5Gg(B, C, D, A, X[12], S[31], T[31]);
    A = md5Hh(A, B, C, D, X[5], S[32], T[32]);
    D = md5Hh(D, A, B, C, X[8], S[33], T[33]);
    C = md5Hh(C, D, A, B, X[11], S[34], T[34]);
    B = md5Hh(B, C, D, A, X[14], S[35], T[35]);
    A = md5Hh(A, B, C, D, X[1], S[36], T[36]);
    D = md5Hh(D, A, B, C, X[4], S[37], T[37]);
    C = md5Hh(C, D, A, B, X[7], S[38], T[38]);
    B = md5Hh(B, C, D, A, X[10], S[39], T[39]);
    A = md5Hh(A, B, C, D, X[13], S[40], T[40]);
    D = md5Hh(D, A, B, C, X[0], S[41], T[41]);
    C = md5Hh(C, D, A, B, X[3], S[42], T[42]);
    B = md5Hh(B, C, D, A, X[6], S[43], T[43]);
    A = md5Hh(A, B, C, D, X[9], S[44], T[44]);
    D = md5Hh(D, A, B, C, X[12], S[45], T[45]);
    C = md5Hh(C, D, A, B, X[15], S[46], T[46]);
    B = md5Hh(B, C, D, A, X[2], S[47], T[47]);
    A = md5Ii(A, B, C, D, X[0], S[48], T[48]);
    D = md5Ii(D, A, B, C, X[7], S[49], T[49]);
    C = md5Ii(C, D, A, B, X[14], S[50], T[50]);
    B = md5Ii(B, C, D, A, X[5], S[51], T[51]);
    A = md5Ii(A, B, C, D, X[12], S[52], T[52]);
    D = md5Ii(D, A, B, C, X[3], S[53], T[53]);
    C = md5Ii(C, D, A, B, X[10], S[54], T[54]);
    B = md5Ii(B, C, D, A, X[1], S[55], T[55]);
    A = md5Ii(A, B, C, D, X[8], S[56], T[56]);
    D = md5Ii(D, A, B, C, X[15], S[57], T[57]);
    C = md5Ii(C, D, A, B, X[6], S[58], T[58]);
    B = md5Ii(B, C, D, A, X[13], S[59], T[59]);
    A = md5Ii(A, B, C, D, X[4], S[60], T[60]);
    D = md5Ii(D, A, B, C, X[11], S[61], T[61]);
    C = md5Ii(C, D, A, B, X[2], S[62], T[62]);
    B = md5Ii(B, C, D, A, X[9], S[63], T[63]);
    a0 = md5Add(a0, A);
    b0 = md5Add(b0, B);
    c0 = md5Add(c0, C);
    d0 = md5Add(d0, D);
  }
  const b = (n) => [n & 255, n >>> 8 & 255, n >>> 16 & 255, n >>> 24 & 255];
  return [...b(a0), ...b(b0), ...b(c0), ...b(d0)].map((x) => x.toString(16).padStart(2, "0")).join("");
}
async function sha1hex(input) {
  const buf = typeof input === "string" ? enc.encode(input) : input;
  const digest = await crypto.subtle.digest("SHA-1", buf);
  return [...new Uint8Array(digest)].map((b) => b.toString(16).padStart(2, "0")).join("");
}
async function hmacSha256(key, data) {
  const k = await crypto.subtle.importKey("raw", enc.encode(key), { name: "HMAC", hash: "SHA-256" }, false, ["sign"]);
  const sig = await crypto.subtle.sign("HMAC", k, enc.encode(data));
  return new Uint8Array(sig);
}
var b64url = (buf) => btoa(String.fromCharCode(...new Uint8Array(buf))).replace(/\+/g, "-").replace(/\//g, "_").replace(/=+$/, "");
function b64urlDecode(str) {
  const s = str.replace(/-/g, "+").replace(/_/g, "/");
  const pad = s.length % 4 === 0 ? "" : "=".repeat(4 - s.length % 4);
  const bin = atob(s + pad);
  return dec.decode(Uint8Array.from(bin, (c) => c.charCodeAt(0)));
}
function b64urlDecodeBytes(str) {
  const s = str.replace(/-/g, "+").replace(/_/g, "/");
  const pad = s.length % 4 === 0 ? "" : "=".repeat(4 - s.length % 4);
  const bin = atob(s + pad);
  return Uint8Array.from(bin, (c) => c.charCodeAt(0));
}
async function jwtVerify(token, secret) {
  try {
    const parts = token.split(".");
    if (parts.length !== 3) return null;
    const expected = await hmacSha256(secret, `${parts[0]}.${parts[1]}`);
    const got = b64urlDecodeBytes(parts[2]);
    const exp = new Uint8Array(expected);
    if (exp.length !== got.length) return null;
    let diff = 0;
    for (let i = 0; i < exp.length; i++) diff |= exp[i] ^ got[i];
    if (diff !== 0) return null;
    const payload = JSON.parse(b64urlDecode(parts[1]));
    if (payload.exp && payload.exp < Math.floor(Date.now() / 1e3)) return null;
    return payload;
  } catch (e) {
    return null;
  }
}
async function generatePassword(pass, salt) {
  const h1 = md5hex(String(pass));
  const h2 = md5hex(String(salt));
  return sha1hex(md5hex(h1 + h2));
}
async function verifyPassword(stored, pass, salt) {
  const cur = await generatePassword(pass, salt);
  return cur === stored;
}
var dbRows = async (env, sql, ...params) => {
  const stmt = env.DB.prepare(sql);
  const res = params.length ? await stmt.bind(...params).all() : await stmt.all();
  return res.results || [];
};
var dbFirst = async (env, sql, ...params) => {
  const rows = await dbRows(env, sql, ...params);
  return rows[0] || null;
};
var dbRun = async (env, sql, ...params) => {
  const stmt = env.DB.prepare(sql);
  return params.length ? await stmt.bind(...params).run() : await stmt.run();
};
var dbLastId = async (env) => {
  const r = await dbFirst(env, "SELECT last_insert_rowid() AS id");
  return r ? r.id : 0;
};
var dbInsert = async (env, table, data) => {
  const keys = Object.keys(data);
  const cols = keys.map((k) => `"${k}"`).join(",");
  const marks = keys.map(() => "?").join(",");
  await dbRun(env, `INSERT INTO ${table} (${cols}) VALUES (${marks})`, ...keys.map((k) => data[k]));
  return dbLastId(env);
};
var dbUpdate = async (env, table, data, whereSql, ...whereParams) => {
  const sets = Object.keys(data).map((k) => `"${k}"=?`).join(",");
  await dbRun(env, `UPDATE ${table} SET ${sets} WHERE ${whereSql}`, ...Object.values(data), ...whereParams);
};
var configCache = null;
async function loadConfig(env) {
  if (configCache) return configCache;
  const rows = await dbRows(env, "SELECT key, value FROM acg_config");
  const cfg = {};
  for (const r of rows) cfg[r.key] = r.value;
  cfg.currency_symbol = cfg.currency_symbol || "\xA5";
  configCache = cfg;
  return cfg;
}
function buildCategoryTree(list) {
  const map = {};
  const roots = [];
  list.forEach((c) => map[c.id] = { ...c });
  list.forEach((c) => {
    const pid = c.pid ? Number(c.pid) : 0;
    if (pid && map[pid]) {
      (map[pid].children ||= []).push(map[c.id]);
    } else {
      roots.push(map[c.id]);
    }
  });
  const sortDeep = (arr) => {
    arr.sort((a, b) => (Number(a.sort) || 0) - (Number(b.sort) || 0));
    arr.forEach((n) => n.children && sortDeep(n.children));
    return arr;
  };
  return sortDeep(roots);
}
function parseCookies(header) {
  const out = {};
  if (!header) return out;
  header.split(";").forEach((p) => {
    const i = p.indexOf("=");
    if (i < 0) return;
    const k = p.slice(0, i).trim();
    const v = decodeURIComponent(p.slice(i + 1).trim());
    if (k) out[k] = v;
  });
  return out;
}
function requestInfo(request, url) {
  const ua = request.headers.get("user-agent") || "";
  const cf = request.cf || {};
  const ip = cf && (cf.connectingIp || cf.ip) || request.headers.get("cf-connecting-ip") || (request.headers.get("x-forwarded-for") || "").split(",")[0].trim() || "127.0.0.1";
  const isMobile = /Mobi|Android|iPhone|iPad|iPod/i.test(ua);
  return { ua, ip, isMobile };
}
function langMenu() {
  return [{ code: "zh-cn", name: "\u7B80\u4F53\u4E2D\u6587" }];
}
function langDictScript() {
  return "";
}
function indexVar(catId, cfg) {
  const data = {
    DEBUG: false,
    LANG: "zh-cn",
    LANGS: langMenu(),
    CURRENCY: { code: cfg.currency_code || "CNY", symbol: cfg.currency_symbol || "\xA5", rate: Number(cfg.currency_rate || 1), decimals: Number(cfg.currency_decimals || 2) },
    CAT_ID: Number(catId) || 0
  };
  return `<script>window._data_var=${JSON.stringify(data)};</script>${langDictScript()}`;
}
function itemVar(item) {
  return `<script>window._data_var._var_item=${JSON.stringify(item)};</script>`;
}
function generateTradeNo() {
  let s = String(1 + Math.floor(Math.random() * 9));
  for (let i = 0; i < 17; i++) s += String(Math.floor(Math.random() * 10));
  return s;
}
var Decimal = class _Decimal {
  constructor(v) {
    this.cent = Math.round((Number(v) || 0) * 100);
  }
  add(o) {
    return new _Decimal((this.cent + new _Decimal(o).cent) / 100);
  }
  sub(o) {
    return new _Decimal((this.cent - new _Decimal(o).cent) / 100);
  }
  mul(o) {
    return new _Decimal(this.cent * new _Decimal(o).cent / 1e4);
  }
  div(o) {
    const d = new _Decimal(o).cent;
    return d === 0 ? new _Decimal(0) : new _Decimal(this.cent / d);
  }
  getAmount() {
    return (this.cent / 100).toFixed(2);
  }
};
function epaySign(params, key) {
  const keys = Object.keys(params).filter((k) => k !== "sign" && k !== "sign_type" && params[k] !== "" && params[k] != null).sort();
  const str = keys.map((k) => `${k}=${params[k]}`).join("&") + `&key=${key}`;
  return md5hex(str);
}
async function getUserGroup(env, recharge) {
  const rows = await dbRows(env, "SELECT * FROM acg_user_group ORDER BY recharge ASC");
  let group = null;
  for (const r of rows) {
    if ((Number(recharge) || 0) >= Number(r.recharge)) group = r;
  }
  return group;
}
var BILL_TYPE_ADD = 1;
var BILL_TYPE_SUB = 2;
async function billCreate(env, userId, amount, log, type) {
  const user = await dbFirst(env, "SELECT id, balance FROM acg_user WHERE id=?", userId);
  if (!user) throw new Error("\u7528\u6237\u4E0D\u5B58\u5728");
  const delta = type === BILL_TYPE_ADD ? Number(amount) : -Number(amount);
  const balance = Math.round(((Number(user.balance) || 0) + delta) * 100) / 100;
  await dbRun(env, "UPDATE acg_user SET balance=? WHERE id=?", balance, userId);
  await dbInsert(env, "acg_bill", {
    owner: userId,
    amount: Number(amount),
    balance,
    type,
    currency: 0,
    log,
    create_time: now()
  });
  return balance;
}
var BILL = { ADD: BILL_TYPE_ADD, SUB: BILL_TYPE_SUB };
async function userSessionCookie(user, remember = false, cfg = {}) {
  const sessionExpire = Number(cfg.session_expire) > 0 ? Number(cfg.session_expire) : 604800;
  const ttl = remember ? 86400 * 365 : sessionExpire;
  const payload = { expire: now() + ttl, loginTime: now() };
  const jwt = await jwtSignHeader(payload, user.password, { uid: user.id });
  return { value: btoa(UTF8.encode(jwt)), maxAge: ttl, raw: jwt, payload };
}
async function jwtSignHeader(payload, secret, headerExtra = {}, ttl) {
  const header = { alg: "HS256", typ: "JWT", ...headerExtra };
  const nowT = Math.floor(Date.now() / 1e3);
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
async function verifyUserSession(env, cookieValue) {
  try {
    let raw = decodeURIComponent(cookieValue);
    const jwt = UTF8.decode(atob(raw));
    const parts = jwt.split(".");
    if (parts.length !== 3) return null;
    const header = JSON.parse(b64urlDecode(parts[0]));
    const uid = header.uid;
    if (!uid) return null;
    const user = await dbFirst(env, "SELECT * FROM acg_user WHERE id=?", uid);
    if (!user) return null;
    const payload = await jwtVerify(jwt, user.password);
    if (!payload) return null;
    if (payload.expire && payload.expire < now()) return null;
    return user;
  } catch (e) {
    return null;
  }
}
var UTF8 = {
  encode: (s) => {
    const b = enc.encode(s);
    return String.fromCharCode(...b);
  },
  decode: (s) => dec.decode(Uint8Array.from(s, (c) => c.charCodeAt(0)))
};
var throttleMap = /* @__PURE__ */ new Map();
function throttle(key, limit, windowSec) {
  const t = now();
  const rec = throttleMap.get(key) || { count: 0, reset: t + windowSec };
  if (t > rec.reset) {
    rec.count = 0;
    rec.reset = t + windowSec;
  }
  rec.count++;
  throttleMap.set(key, rec);
  if (throttleMap.size > 5e3) {
    for (const k of throttleMap.keys()) {
      if (throttleMap.get(k).reset < t) throttleMap.delete(k);
    }
  }
  return rec.count > limit;
}
function throttleClear(key) {
  throttleMap.delete(key);
}

// api.js
var SESSION_NAME = "USER_SESSION";
function getCookies(request, env) {
  return parseCookies(request.headers.get("Cookie") || "");
}
async function currentUser(env, request) {
  const cookies = getCookies(request, env);
  const token = cookies[SESSION_NAME];
  if (!token) return null;
  return verifyUserSession(env, token);
}
async function userGroupOf(env, user) {
  if (!user) return null;
  return getUserGroup(env, user.recharge || 0);
}
var CAPTCHA_SECRET_KEY = "acg-faka-captcha-v1";
async function captchaImage(env, request, action) {
  const code = String(Math.floor(1e3 + Math.random() * 9e3));
  const token = md5hex(`${code}:${action}:${CAPTCHA_SECRET_KEY}:${randStr(6)}`);
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="120" height="40" viewBox="0 0 120 40">
    <rect width="120" height="40" fill="#f0f4f8"/>
    ${[0, 1, 2, 3].map((i) => `<line x1="${10 + i * 30}" y1="${Math.random() * 30 + 5}" x2="${40 + i * 20}" y2="${Math.random() * 30 + 5}" stroke="#c5d5e6" stroke-width="1"/>`).join("")}
    ${[0, 1, 2, 3].map((i) => `<text x="${14 + i * 28}" y="27" font-size="24" font-family="monospace" fill="${["#2b6cb0", "#38a169", "#c53030", "#805ad5"][i]}">${code[i]}</text>`).join("")}
  </svg>`;
  return {
    svg,
    token: `${BufferB64(code + ":" + token)}`,
    cookie: `acg_captcha_${action}=${encodeURIComponent(BufferB64(code + ":" + token))}; Path=/; HttpOnly; SameSite=Lax; Max-Age=600`
  };
}
function BufferB64(str) {
  const bytes = new TextEncoder().encode(str);
  let bin = "";
  bytes.forEach((b) => {
    bin += String.fromCharCode(b);
  });
  return btoa(bin);
}
function BufferUnB64(str) {
  const bin = atob(str);
  const bytes = Uint8Array.from(bin, (c) => c.charCodeAt(0));
  return new TextDecoder().decode(bytes);
}
async function captchaVerify(env, request, action, input) {
  if (!input) return false;
  const cookies = getCookies(request, env);
  const raw = cookies[`acg_captcha_${action}`];
  if (!raw) return false;
  try {
    const decoded = BufferUnB64(decodeURIComponent(raw));
    const [code] = decoded.split(":");
    return String(input).trim() === code;
  } catch (e) {
    return false;
  }
}
async function register(env, request, url, body) {
  const cfg = await loadConfig(env);
  if (Number(cfg.registered_state) === 0) return apiError("\u6CE8\u518C\u5DF2\u5173\u95ED");
  if (Number(cfg.registered_verification) === 1) {
    const ok = await captchaVerify(env, request, "register", body.captcha);
    if (!ok) return apiError("\u9A8C\u8BC1\u7801\u9519\u8BEF");
  }
  const username = String(body.username || "");
  const usernameLen = Number(cfg.username_len) || 6;
  if (username.length < usernameLen) return apiError(`\u7528\u6237\u540D\u6700\u5C11${usernameLen}\u4F4D`);
  const exist = await dbFirst(env, "SELECT id FROM acg_user WHERE username=?", username);
  if (exist) return apiError("\u8BE5\u7528\u6237\u540D\u5DF2\u5B58\u5728\uFF0C\u6362\u4E00\u4E2A\u5427");
  const regType = Number(cfg.registered_type);
  const user = {};
  if (regType === 2) {
    const email = String(body.email || "");
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return apiError("\u90AE\u7BB1\u5730\u5740\u4E0D\u6B63\u786E");
    const e2 = await dbFirst(env, "SELECT id FROM acg_user WHERE email=?", email);
    if (e2) return apiError("\u8BE5\u90AE\u7BB1\u5DF2\u5B58\u5728\uFF0C\u6362\u4E00\u4E2A\u5427");
    user.email = email;
  } else if (regType === 1) {
    const phone = String(body.phone || "");
    if (!/^1[3-9]\d{9}$/.test(phone)) return apiError("\u624B\u673A\u53F7\u7801\u4E0D\u6B63\u786E");
    const p2 = await dbFirst(env, "SELECT id FROM acg_user WHERE phone=?", phone);
    if (p2) return apiError("\u8BE5\u624B\u673A\u5DF2\u5B58\u5728\uFF0C\u6362\u4E00\u4E2A\u5427");
    user.phone = phone;
  }
  const password = String(body.password || "");
  if (password.length < 6) return apiError("\u5BC6\u7801\u6700\u5C116\u4F4D");
  const salt = randStr(32);
  const pwdHash = await generatePassword(password, salt);
  const info = requestInfo(request, url);
  const id = await dbInsert(env, "acg_user", {
    username,
    password: pwdHash,
    salt,
    app_key: randStr(16).toUpperCase(),
    avatar: "/favicon.ico",
    balance: 0,
    coin: 0,
    integral: 0,
    create_time: now(),
    login_ip: info.ip,
    recharge: 0,
    total_coin: 0,
    status: 1,
    ...user
  });
  const created = await dbFirst(env, "SELECT * FROM acg_user WHERE id=?", id);
  const session = await userSessionCookie(created, false, cfg);
  const headers = { "Content-Type": "application/json; charset=utf-8" };
  headers["Set-Cookie"] = `${SESSION_NAME}=${encodeURIComponent(session.value)}; Path=/; HttpOnly; SameSite=Lax; Max-Age=${session.maxAge}`;
  return new Response(JSON.stringify({ code: 200, msg: "\u6CE8\u518C\u6210\u529F" }), { status: 200, headers });
}
async function login(env, request, url, body) {
  const cfg = await loadConfig(env);
  const info = requestInfo(request, url);
  if (throttle(`login:ip:${info.ip}`, 30, 300)) return apiError("\u767B\u5F55\u5C1D\u8BD5\u8FC7\u4E8E\u9891\u7E41\uFF0C\u8BF7\u7A0D\u540E\u518D\u8BD5");
  if (Number(cfg.login_verification) === 1) {
    const ok = await captchaVerify(env, request, "login", body.captcha);
    if (!ok) return apiError("\u9A8C\u8BC1\u7801\u9519\u8BEF");
  }
  const username = String(body.username || "");
  const password = String(body.password || "");
  if (!username) return apiError("\u7528\u6237\u540D\u8F93\u5165\u9519\u8BEF");
  if (password.length < 6) return apiError("\u5BC6\u7801\u9519\u8BEF");
  const user = await dbFirst(env, "SELECT * FROM acg_user WHERE username=? OR email=? OR phone=?", username, username, username);
  if (!user) return apiError("\u7528\u6237\u4E0D\u5B58\u5728");
  const okPwd = await verifyPassword(user.password, password, user.salt);
  if (!okPwd) return apiError("\u5BC6\u7801\u9519\u8BEF");
  if (Number(user.status) === 0) return apiError("\u60A8\u5DF2\u88AB\u5C01\u7981");
  const remember = body.remember === "1" || body.remember === true;
  const session = await userSessionCookie(user, remember, cfg);
  await dbRun(env, "UPDATE acg_user SET last_login_time=login_time, login_time=?, last_login_ip=login_ip, login_ip=? WHERE id=?", now(), info.ip, user.id);
  throttleClear(`login:ip:${info.ip}`);
  const headers = { "Content-Type": "application/json; charset=utf-8" };
  headers["Set-Cookie"] = `${SESSION_NAME}=${encodeURIComponent(session.value)}; Path=/; HttpOnly; SameSite=Lax; Max-Age=${session.maxAge}`;
  return new Response(JSON.stringify({ code: 200, msg: "\u767B\u5F55\u6210\u529F" }), { status: 200, headers });
}
function logout(env, request, url) {
  const headers = { "Location": "/user/authentication/login", "Set-Cookie": `${SESSION_NAME}=; Path=/; HttpOnly; SameSite=Lax; Max-Age=0` };
  return new Response(null, { status: 302, headers });
}
async function loadCommodity(env, id, cfg) {
  const r = await dbFirst(env, "SELECT * FROM acg_commodity WHERE id=?", id);
  if (!r) throw new Error("\u5546\u54C1\u4E0D\u5B58\u5728");
  return r;
}
function parseJson(raw, fallback) {
  if (!raw) return fallback;
  try {
    const v = JSON.parse(raw);
    return v == null ? fallback : v;
  } catch (e) {
    return fallback;
  }
}
function cardStock(env, commodityId) {
  return dbFirst(env, "SELECT COUNT(*) AS n FROM acg_card WHERE status=0 AND commodity_id=?", commodityId).then((r) => r ? Number(r.n) : 0);
}
async function valuation(env, cfg, commodity, num, race, sku, coupon, group) {
  const config = parseJson(commodity.config, {});
  let price = new Decimal(group && group.id ? Number(commodity.user_price) > 0 ? commodity.user_price : commodity.price : commodity.price);
  if (race && config.category && config.category[race] != null) {
    price = new Decimal(config.category[race]);
    if (config.category_wholesale && config.category_wholesale[race]) {
      const list = config.category_wholesale[race];
      const ks = Object.keys(list).map(Number).sort((a, b) => b - a);
      for (const k of ks) {
        if (num >= k) {
          price = new Decimal(list[k]);
          break;
        }
      }
    }
  } else if (config.wholesale) {
    const list = config.wholesale;
    const ks = Object.keys(list).map(Number).sort((a, b) => b - a);
    for (const k of ks) {
      if (num >= k) {
        price = new Decimal(list[k]);
        break;
      }
    }
  }
  if (sku && config.sku) {
    for (const [k, v] of Object.entries(sku)) {
      const opt = config.sku[k] && config.sku[k][v];
      if (opt != null) {
        const p = Number(opt);
        if (p > 0) price = price.add(p);
      }
    }
  }
  if (Number(commodity.level_disable) !== 1 && group && group.discount_config) {
    const dc = parseJson(group.discount_config, {});
    const gids = Object.keys(dc).map(Number);
    if (gids.length) {
      const groups = await dbRows(env, "SELECT id, commodity_list FROM acg_commodity_group WHERE id IN (" + gids.map(() => "?").join(",") + ")", ...gids);
      for (const g of groups) {
        const cl = parseJson(g.commodity_list, []);
        if (cl.includes(Number(commodity.id))) {
          price = price.mul(Number(dc[g.id]) / 100);
          break;
        }
      }
    }
  }
  if (coupon && num === 1) {
    if (Number(commodity.coupon) !== 1) throw new Error("\u8BE5\u5546\u54C1\u4E0D\u652F\u6301\u4F7F\u7528\u4F18\u60E0\u5238");
    const voucher = await dbFirst(env, "SELECT * FROM acg_coupon WHERE code=?", coupon);
    if (!voucher || Number(voucher.owner) !== Number(commodity.owner)) throw new Error("\u8BE5\u4F18\u60E0\u5238\u4E0D\u5B58\u5728");
    if (Number(voucher.commodity_id) !== 0 && Number(voucher.commodity_id) !== Number(commodity.id)) throw new Error("\u8BE5\u4F18\u60E0\u5238\u4E0D\u5C5E\u4E8E\u8BE5\u5546\u54C1");
    if (Number(voucher.status) !== 0) throw new Error("\u8BE5\u4F18\u60E0\u5238\u5DF2\u5931\u6548");
    if (voucher.expire_time && Number(voucher.expire_time) < now()) throw new Error("\u8BE5\u4F18\u60E0\u5238\u5DF2\u8FC7\u671F");
    const money2 = Number(voucher.money) || 0;
    const deduction = Number(voucher.mode) === 0 ? money2 : price.mul(money2).getAmount();
    price = deduction >= Number(price.getAmount()) ? new Decimal(0) : price.sub(deduction);
  }
  return price.mul(num).getAmount();
}
async function trade(env, request, url, body) {
  const cfg = await loadConfig(env);
  const user = await currentUser(env, request);
  const group = await userGroupOf(env, user);
  const info = requestInfo(request, url);
  if (Number(cfg.trade_verification) === 1) {
    const ok = await captchaVerify(env, request, "trade", body.captcha);
    if (!ok) return apiError("\u9A8C\u8BC1\u7801\u9519\u8BEF");
  }
  const commodityId = Number(body.item_id) || 0;
  const contact = String(body.contact || "");
  let num = Number(body.num) || 0;
  let cardId = Number(body.card_id) || 0;
  const payId = Number(body.pay_id) || 0;
  const device = /Mobile|Android|iPhone|iPad/i.test(info.ua) ? 2 : 1;
  const password = String(body.password || "");
  const coupon = String(body.coupon || "");
  let race = String(body.race || "");
  let sku = null;
  if (body.sku) {
    try {
      sku = typeof body.sku === "string" ? JSON.parse(body.sku) : body.sku;
    } catch (e) {
      sku = null;
    }
  }
  if (!commodityId) return apiError("\u8BF7\u9009\u62E9\u5546\u54C1");
  if (num <= 0) return apiError("\u81F3\u5C11\u8D2D\u4E701\u4E2A");
  const commodity = await loadCommodity(env, commodityId, cfg);
  if (Number(commodity.status) !== 1) return apiError("\u5F53\u524D\u5546\u54C1\u5DF2\u505C\u552E");
  const owner = user ? user.id : 0;
  if (Number(cfg.force_login) === 1 || Number(commodity.only_user) === 1 || Number(commodity.purchase_count) > 0) {
    if (!owner) return apiError("\u8BF7\u5148\u767B\u5F55\u540E\u518D\u8D2D\u4E70\u54E6");
  }
  if (Number(commodity.minimum) > 0 && num < Number(commodity.minimum)) return apiError(`\u672C\u5546\u54C1\u6700\u5C11\u8D2D\u4E70${commodity.minimum}\u4E2A`);
  if (Number(commodity.maximum) > 0 && num > Number(commodity.maximum)) return apiError(`\u672C\u5546\u54C1\u5355\u6B21\u6700\u591A\u8D2D\u4E70${commodity.maximum}\u4E2A`);
  const widget = {};
  if (commodity.widget) {
    const list = parseJson(commodity.widget, []);
    for (const w of list) {
      if (w.type === "custom") continue;
      if (w.regex) {
        try {
          if (!new RegExp(w.regex).test(String(body[w.name] || ""))) return apiError(w.error || "\u8F93\u5165\u5185\u5BB9\u4E0D\u7B26\u5408\u8981\u6C42");
        } catch (e) {
        }
      }
      widget[w.name] = { value: body[w.name] || "", cn: w.cn || "" };
    }
  }
  if (Number(commodity.draft_status) === 1 && cardId !== 0) num = 1;
  if (!user) {
    if (contact.length < 3) return apiError("\u8054\u7CFB\u65B9\u5F0F\u4E0D\u80FD\u4F4E\u4E8E3\u4E2A\u5B57\u7B26");
    const regx = [/^1[3-9]\d{9}$/, /.*(.{2}@.*)$/i, /[1-9]{1}[0-9]{4,11}/];
    const msg = ["\u624B\u673A", "\u90AE\u7BB1", "QQ\u53F7"];
    const ct = Number(commodity.contact_type);
    if (ct !== 0 && !regx[ct - 1].test(contact)) return apiError(`\u60A8\u8F93\u5165\u7684${msg[ct - 1]}\u683C\u5F0F\u4E0D\u6B63\u786E\uFF01`);
    if (Number(commodity.password_status) === 1 && password.length < 6) return apiError("\u60A8\u7684\u8BBE\u7F6E\u7684\u5BC6\u7801\u8FC7\u4E8E\u7B80\u5355\uFF0C\u4E0D\u80FD\u4F4E\u4E8E6\u4F4D\u54E6");
  }
  if (Number(commodity.seckill_status) === 1) {
    if (now() < Number(commodity.seckill_start_time)) return apiError("\u62A2\u8D2D\u8FD8\u672A\u5F00\u59CB");
    if (now() > Number(commodity.seckill_end_time)) return apiError("\u62A2\u8D2D\u5DF2\u7ED3\u675F");
  }
  const config = parseJson(commodity.config, {});
  if (config.category && Object.keys(config.category).length) {
    if (!race) return apiError("\u8BF7\u9009\u62E9\u5546\u54C1\u7C7B\u578B");
    if (!(race in config.category)) return apiError("\u6B64\u5546\u54C1\u7C7B\u578B\u4E0D\u5B58\u5728");
  }
  if (config.sku && Object.keys(config.sku).length) {
    for (const [name, opts] of Object.entries(config.sku)) {
      if (!Array.isArray(opts) || !Object.keys(opts).length) continue;
      if (!sku || !sku[name] || String(sku[name]) === "") return apiError(`\u8BF7\u9009\u62E9${name}`);
      if (!(String(sku[name]) in opts)) return apiError(`${name}\u9009\u62E9\u9519\u8BEF`);
    }
  }
  let stock = 0;
  if (Number(commodity.delivery_way) === 0) stock = await cardStock(env, commodityId);
  else stock = Number(commodity.stock) || 0;
  if (stock === 0 || num > stock) return apiError("\u5E93\u5B58\u4E0D\u8DB3");
  if (Number(commodity.purchase_count) > 0 && owner > 0) {
    const c = await dbFirst(env, "SELECT COUNT(*) AS n FROM acg_order WHERE owner=? AND commodity_id=?", owner, commodityId);
    if (Number(c.n) >= Number(commodity.purchase_count)) return apiError(`\u8BE5\u5546\u54C1\u6BCF\u4EBA\u53EA\u80FD\u8D2D\u4E70${commodity.purchase_count}\u4EF6`);
  }
  let amount;
  let valuationError = null;
  try {
    amount = await valuation(env, cfg, commodity, num, race, sku, coupon, group);
  } catch (e) {
    valuationError = e.message;
  }
  if (valuationError) return apiError(valuationError);
  amount = Number(amount) || 0;
  const pay = await dbFirst(env, "SELECT * FROM acg_pay WHERE id=?", payId);
  if (!pay) return apiError("\u8BE5\u652F\u4ED8\u65B9\u5F0F\u4E0D\u5B58\u5728");
  if (Number(pay.commodity) !== 1) return apiError("\u5F53\u524D\u652F\u4ED8\u65B9\u5F0F\u5DF2\u505C\u7528\uFF0C\u8BF7\u6362\u4E2A\u652F\u4ED8\u65B9\u5F0F\u518D\u8FDB\u884C\u652F\u4ED8");
  const tradeNo = generateTradeNo();
  const orderId = await dbInsert(env, "acg_order", {
    owner,
    user_id: Number(commodity.owner) || 0,
    trade_no: tradeNo,
    amount,
    commodity_id: commodityId,
    card_num: num,
    pay_id: payId,
    create_time: now(),
    create_ip: info.ip,
    create_device: device,
    status: 0,
    contact: user ? randStr(16) : contact.trim(),
    delivery_status: 0,
    widget: JSON.stringify(widget),
    coupon_id: coupon ? 0 : null,
    race: race || null,
    sku: sku ? JSON.stringify(sku) : null,
    cost: Number(commodity.factory_price) || 0
  });
  const order = await dbFirst(env, "SELECT * FROM acg_order WHERE id=?", orderId);
  let couponId = null;
  if (coupon) {
    const voucher = await dbFirst(env, "SELECT * FROM acg_coupon WHERE code=?", coupon);
    if (!voucher) return apiError("\u4F18\u60E0\u5238\u4E0D\u5B58");
    if (Number(voucher.status) !== 0) return apiError("\u8BE5\u4F18\u60E0\u5238\u5DF2\u5931\u6548");
    if (Number(voucher.life) <= 0) return apiError("\u8BE5\u4F18\u60E0\u5238\u5DF2\u5931\u6548");
    couponId = voucher.id;
    await dbRun(env, "UPDATE acg_coupon SET use_life=use_life+1, life=life-1, trade_no=?, service_time=? WHERE id=? AND life>0", tradeNo, now(), voucher.id);
    const v2 = await dbFirst(env, "SELECT life FROM acg_coupon WHERE id=?", voucher.id);
    if (Number(v2.life) <= 0) await dbRun(env, "UPDATE acg_coupon SET status=1 WHERE id=?", voucher.id);
    await dbRun(env, "UPDATE acg_order SET coupon_id=? WHERE id=?", couponId, orderId);
  }
  let secret = null;
  let payUrl = "";
  if (amount <= 0) {
    await dbRun(env, "UPDATE acg_order SET amount=0.00 WHERE id=?", orderId);
    secret = await orderSuccess(env, cfg, order);
  } else if (String(pay.handle) === "#system") {
    if (!user) return apiError("\u60A8\u672A\u767B\u5F55\uFF0C\u8BF7\u5148\u767B\u5F55\u540E\u518D\u4F7F\u7528\u4F59\u989D\u652F\u4ED8");
    const bal = await dbFirst(env, "SELECT balance FROM acg_user WHERE id=?", user.id);
    if (Number(bal.balance) < Number(amount)) return apiError("\u4F59\u989D\u4E0D\u8DB3\uFF0C\u8BF7\u524D\u5F80\u5145\u503C");
    const r = await dbRun(env, "UPDATE acg_user SET balance=balance-? WHERE id=? AND balance>=?", amount, user.id, amount);
    if (!r.success) return apiError("\u4F59\u989D\u6263\u6B3E\u5931\u8D25\uFF0C\u8BF7\u91CD\u8BD5");
    await dbInsert(env, "acg_bill", {
      owner: user.id,
      amount,
      balance: Number(bal.balance) - Number(amount),
      type: BILL.SUB,
      currency: 0,
      log: `\u5546\u54C1\u4E0B\u5355[${tradeNo}]`,
      create_time: now()
    });
    secret = await orderSuccess(env, cfg, order);
  } else {
    let payCost = 0;
    if (Number(pay.cost_type) === 0) payCost = Number(pay.cost) || 0;
    else payCost = Number(amount) * (Number(pay.cost) || 0);
    const finalAmount = Number((Number(amount) + payCost).toFixed(2));
    await dbUpdate(env, "acg_order", { pay_cost: payCost.toFixed(2), amount: finalAmount.toFixed(2), gateway_amount: finalAmount.toFixed(2) }, "id=?", orderId);
    const host = url.origin;
    const callbackDomain = (cfg.callback_domain || "").trim().replace(/\/$/, "") || host;
    const returnUrl = user ? `${host}/user/personal/purchaseRecord?tradeNo=${tradeNo}` : `${host}/user/index/query?tradeNo=${tradeNo}`;
    const callbackUrl = `${callbackDomain}/user/api/order/callback.${tradeNo}`;
    payUrl = await epayTrade(env, pay, tradeNo, finalAmount, callbackUrl, returnUrl);
    if (!payUrl) return apiError("\u652F\u4ED8\u65B9\u5F0F\u672A\u90E8\u7F72\u6210\u529F");
  }
  const stockAfter = Number(commodity.delivery_way) === 0 ? await cardStock(env, commodityId) : Number(commodity.stock) || 0;
  return new Response(JSON.stringify({
    code: 200,
    msg: "\u4E0B\u5355\u6210\u529F",
    data: { url: payUrl, amount, tradeNo, secret, stock: stockAfter }
  }), { status: 200, headers: { "Content-Type": "application/json; charset=utf-8" } });
}
async function epayTrade(env, pay, tradeNo, amount, callbackUrl, returnUrl) {
  const pcfg = await dbFirst(env, "SELECT config FROM acg_pay_config WHERE id=?", Number(pay.pay_config_id) || 0);
  if (!pcfg) return "";
  const cfgObj = parseJson(pcfg.config, {});
  const pid = String(cfgObj.pid || cfgObj.id || "");
  const key = String(cfgObj.key || cfgObj.secret || "");
  const gateway = String(cfgObj.gateway || cfgObj.url || "").replace(/\/$/, "");
  if (!pid || !key || !gateway) return "";
  const params = {
    pid,
    type: String(pay.code) || "alipay",
    out_trade_no: tradeNo,
    notify_url: callbackUrl,
    return_url: returnUrl,
    name: "\u5546\u54C1\u8D2D\u4E70",
    money: Number(amount).toFixed(2),
    sign_type: "MD5"
  };
  params.sign = epaySign(params, key);
  const qs = Object.entries(params).map(([k, v]) => `${k}=${encodeURIComponent(v)}`).join("&");
  return `${gateway}/submit.php?${qs}`;
}
async function orderCallback(env, request, url, tradeNo) {
  try {
    return await orderCallbackInner(env, request, url, tradeNo);
  } catch (e) {
    console.error("orderCallback ERR", e && e.message, e && e.stack);
    throw e;
  }
}
async function orderCallbackInner(env, request, url, tradeNo) {
  const order = await dbFirst(env, "SELECT * FROM acg_order WHERE trade_no=?", tradeNo);
  const recharge = order ? null : await dbFirst(env, "SELECT * FROM acg_recharge WHERE trade_no=?", tradeNo);
  if (!order && !recharge) return new Response("fail");
  const pay = await dbFirst(env, "SELECT * FROM acg_pay WHERE id=?", order ? order.pay_id : recharge.way);
  if (!pay) return new Response("fail");
  const pcfg = await dbFirst(env, "SELECT config FROM acg_pay_config WHERE id=?", Number(pay.pay_config_id) || 0);
  const cfgObj = pcfg ? parseJson(pcfg.config, {}) : {};
  const key = String(cfgObj.key || cfgObj.secret || "");
  if (!key) return new Response("fail");
  let data = {};
  url.searchParams.forEach((v, k) => {
    data[k] = v;
  });
  if (request.method === "POST") {
    try {
      data = { ...data, ...await request.json() };
    } catch (e) {
      const text = await request.text().catch(() => "");
      try {
        data = { ...data, ...Object.fromEntries(new URLSearchParams(text)) };
      } catch (e2) {
      }
    }
  }
  if (data.s) delete data.s;
  if (data._PARAMETER) delete data._PARAMETER;
  const sign = String(data.sign || "");
  const expect = epaySign(data, key);
  if (sign.toLowerCase() !== expect.toLowerCase()) return new Response("fail");
  if (data.out_trade_no !== tradeNo) return new Response("fail");
  if ((data.trade_status || data.status) !== "TRADE_SUCCESS") return new Response("fail");
  const paidAmount = Number(data.money || data.amount || 0);
  if (order) {
    if (Number(order.status) !== 0) return new Response("success");
    const expectAmount = Number(order.gateway_amount != null ? order.gateway_amount : order.amount);
    if (Math.abs(paidAmount - expectAmount) > 0.01) return new Response("fail");
    if (Number(order.owner) !== 0) {
      await dbRun(env, "UPDATE acg_user SET recharge=recharge+? WHERE id=?", Number(order.amount), order.owner);
    }
    await orderSuccess(env, cfgNone(), order);
    return new Response("success");
  }
  if (Number(recharge.status) === 1) return new Response("success");
  if (Math.abs(paidAmount - Number(recharge.amount)) > 0.01) return new Response("fail");
  await dbRun(env, "UPDATE acg_recharge SET status=1 WHERE id=?", recharge.id);
  const user = await dbFirst(env, "SELECT balance FROM acg_user WHERE id=?", recharge.owner);
  const bal = Math.round(((Number(user.balance) || 0) + Number(recharge.amount)) * 100) / 100;
  await dbRun(env, "UPDATE acg_user SET balance=?, recharge=recharge+? WHERE id=?", bal, Number(recharge.amount), recharge.owner);
  await dbInsert(env, "acg_bill", {
    owner: recharge.owner,
    amount: Number(recharge.amount),
    balance: bal,
    type: BILL.ADD,
    currency: 0,
    log: `\u94B1\u5305\u5145\u503C[${tradeNo}]`,
    create_time: now()
  });
  return new Response("success");
}
var cfgNone = () => ({});
async function orderSuccess(env, cfg, order) {
  const commodity = await dbFirst(env, "SELECT * FROM acg_commodity WHERE id=?", order.commodity_id);
  await dbRun(env, "UPDATE acg_order SET pay_time=?, status=1 WHERE id=?", now(), order.id);
  let secret = "";
  const deliveryWay = Number(commodity.delivery_way);
  const draft = Number(order.card_id) || 0;
  if (deliveryWay === 0) {
    secret = await pullCard(env, order, commodity, draft);
    await dbRun(env, "UPDATE acg_order SET delivery_status=1, secret=? WHERE id=?", secret, order.id);
  } else {
    secret = commodity.delivery_message && String(commodity.delivery_message) !== "" ? commodity.delivery_message : "\u6B63\u5728\u53D1\u8D27\u4E2D\uFF0C\u8BF7\u8010\u5FC3\u7B49\u5F85\uFF0C\u5982\u6709\u7591\u95EE\uFF0C\u8BF7\u8054\u7CFB\u5BA2\u670D\u3002";
    const st = await dbFirst(env, "SELECT stock FROM acg_commodity WHERE id=?", commodity.id);
    const ns = Math.max(0, Number(st.stock) - Number(order.card_num));
    await dbRun(env, "UPDATE acg_commodity SET stock=? WHERE id=?", ns, commodity.id);
    await dbRun(env, "UPDATE acg_order SET delivery_status=1, secret=? WHERE id=?", secret, order.id);
  }
  if (Number(order.from) > 0 && Number(order.divide_amount) > 0) {
    try {
      await billCreate(env, order.from, order.divide_amount, `\u63A8\u5E7F\u5206\u6210[${order.trade_no}]`, BILL.ADD);
    } catch (e) {
    }
  }
  if (Number(order.rebate) > 0 && Number(order.user_id) > 0) {
    try {
      await billCreate(env, order.user_id, order.rebate, `\u81EA\u8425\u5546\u54C1\u51FA\u552E[${order.trade_no}]`, BILL.ADD);
    } catch (e) {
    }
  }
  return secret;
}
async function pullCard(env, order, commodity, draftId) {
  const soldOut = "\u5F88\u62B1\u6B49\uFF0C\u6709\u4EBA\u5728\u4F60\u4ED8\u6B3E\u4E4B\u524D\u62A2\u8D70\u4E86\u5546\u54C1\uFF0C\u8BF7\u8054\u7CFB\u5BA2\u670D\u3002";
  if (draftId) {
    const r = await dbRun(env, "UPDATE acg_card SET purchase_time=?, order_id=?, status=1 WHERE id=? AND status=0 AND commodity_id=?", now(), order.id, draftId, order.commodity_id);
    const card = await dbFirst(env, "SELECT secret FROM acg_card WHERE id=?", draftId);
    return r.success && card ? card.secret : soldOut;
  }
  const direction = Number(commodity.delivery_auto_mode) === 1 ? "RANDOM()" : Number(commodity.delivery_auto_mode) === 2 ? "id DESC" : "id ASC";
  let where = "status=0 AND commodity_id=?";
  const params = [order.commodity_id];
  if (order.race) {
    where += " AND race=?";
    params.push(order.race);
  } else {
    where += " AND (race IS NULL OR race='')";
  }
  if (order.sku) {
    const skuObj = parseJson(order.sku, {});
    for (const [k, v] of Object.entries(skuObj)) {
      where += ` AND json_extract(sku, '$.${k}')=?`;
      params.push(String(v));
    }
  }
  const cards = await dbRows(env, `SELECT id, secret FROM acg_card WHERE ${where} ORDER BY ${direction}`, ...params);
  if (cards.length < Number(order.card_num)) return soldOut;
  let ok = 0;
  let secret = "";
  for (const card of cards) {
    if (ok >= Number(order.card_num)) break;
    const r = await dbRun(env, "UPDATE acg_card SET purchase_time=?, order_id=?, status=1 WHERE id=? AND status=0", now(), order.id, card.id);
    if (r.success && r.meta && r.meta.changes > 0) {
      ok++;
      secret += card.secret + "\n";
    }
  }
  if (ok < Number(order.card_num)) return soldOut;
  return secret.trim();
}
async function queryOrder(env, request, url, body) {
  const tradeNo = String(url.searchParams.get("tradeNo") || body.tradeNo || "");
  const password = String(body.password || "");
  if (!/^\d{18}$/.test(tradeNo)) return apiError("\u8BA2\u5355\u53F7\u4E0D\u6B63\u786E");
  const order = await dbFirst(env, "SELECT * FROM acg_order WHERE trade_no=?", tradeNo);
  if (!order) return apiError("\u8BA2\u5355\u4E0D\u5B58\u5728");
  if (Number(order.status) !== 1) {
    return new Response(JSON.stringify({ code: 200, msg: "success", data: { trade_no: order.trade_no, amount: order.amount, status: order.status, create_time: order.create_time } }), { status: 200, headers: { "Content-Type": "application/json; charset=utf-8" } });
  }
  if (Number(order.password_status_set) === 1 && order.password) {
    const pwd = String(body.password || "");
    if (!pwd) return apiError("\u8BF7\u8F93\u5165\u67E5\u8BE2\u5BC6\u7801");
    if (pwd !== order.password) return apiError("\u67E5\u8BE2\u5BC6\u7801\u9519\u8BEF");
  }
  const commodity = await dbFirst(env, "SELECT name FROM acg_commodity WHERE id=?", order.commodity_id);
  return new Response(JSON.stringify({
    code: 200,
    msg: "success",
    data: {
      trade_no: order.trade_no,
      amount: order.amount,
      status: order.status,
      create_time: order.create_time,
      commodity_name: commodity ? commodity.name : "",
      contact: order.contact,
      secret: order.secret,
      delivery_status: order.delivery_status
    }
  }), { status: 200, headers: { "Content-Type": "application/json; charset=utf-8" } });
}
async function cardDetail(env, request, url, body) {
  const user = await currentUser(env, request);
  if (!user) return apiError("\u8BF7\u5148\u767B\u5F55");
  const commodityId = Number(body.item_id || url.searchParams.get("item_id")) || 0;
  const commodity = await dbFirst(env, "SELECT * FROM acg_commodity WHERE id=?", commodityId);
  if (!commodity) return apiError("\u5546\u54C1\u4E0D\u5B58\u5728");
  if (Number(commodity.owner) !== Number(user.id)) return apiError("\u65E0\u6743\u67E5\u770B\u8BE5\u5546\u54C1\u5361\u5BC6");
  const list = await dbRows(env, "SELECT id, draft, secret, race, sku, status, order_id, purchase_time FROM acg_card WHERE commodity_id=? ORDER BY id DESC LIMIT 200", commodityId);
  return new Response(JSON.stringify({ code: 200, msg: "success", data: list }), { status: 200, headers: { "Content-Type": "application/json; charset=utf-8" } });
}
async function purchaseRecord(env, request, url) {
  const user = await currentUser(env, request);
  if (!user) return apiError("\u8BF7\u5148\u767B\u5F55");
  const rows = await dbRows(env, "SELECT o.*, c.name AS commodity_name FROM acg_order o LEFT JOIN acg_commodity c ON c.id=o.commodity_id WHERE o.owner=? ORDER BY o.id DESC LIMIT 100", user.id);
  return new Response(JSON.stringify({ code: 200, msg: "success", data: rows, total: rows.length }), { status: 200, headers: { "Content-Type": "application/json; charset=utf-8" } });
}
async function billData(env, request, url) {
  const user = await currentUser(env, request);
  if (!user) return apiError("\u8BF7\u5148\u767B\u5F55");
  const rows = await dbRows(env, "SELECT * FROM acg_bill WHERE owner=? ORDER BY id DESC LIMIT 100", user.id);
  return new Response(JSON.stringify({ code: 200, msg: "success", data: rows }), { status: 200, headers: { "Content-Type": "application/json; charset=utf-8" } });
}
async function payList(env, request, url) {
  const rows = await dbRows(env, "SELECT id, name, icon, handle, cost, cost_type FROM acg_pay WHERE commodity=1 AND archived=0 ORDER BY sort ASC");
  return new Response(JSON.stringify({ code: 200, msg: "success", data: rows }), { status: 200, headers: { "Content-Type": "application/json; charset=utf-8" } });
}
var apiError = (msg, code = 403) => new Response(JSON.stringify({ code, msg }), { status: 200, headers: { "Content-Type": "application/json; charset=utf-8" } });
async function changePassword(env, request, url, body) {
  const user = await currentUser(env, request);
  if (!user) return apiError("\u8BF7\u5148\u767B\u5F55");
  const oldPwd = String(body.oldPassword || "");
  const newPwd = String(body.password || "");
  if (oldPwd.length < 6) return apiError("\u65E7\u5BC6\u7801\u9519\u8BEF");
  if (newPwd.length < 6) return apiError("\u65B0\u5BC6\u7801\u6700\u5C116\u4F4D");
  const ok = await verifyPassword(user.password, oldPwd, user.salt);
  if (!ok) return apiError("\u65E7\u5BC6\u7801\u9519\u8BEF");
  const salt = randStr(32);
  const hash = await generatePassword(newPwd, salt);
  await dbRun(env, "UPDATE acg_user SET password=?, salt=? WHERE id=?", hash, salt, user.id);
  return apiSuccess("\u4FEE\u6539\u6210\u529F");
}
async function rechargeCreate(env, request, url, body) {
  const user = await currentUser(env, request);
  if (!user) return apiError("\u8BF7\u5148\u767B\u5F55");
  const payId = Number(body.pay_id) || 0;
  const amount = Number(body.amount) || 0;
  if (amount <= 0) return apiError("\u5145\u503C\u91D1\u989D\u4E0D\u6B63\u786E");
  const cfg = await loadConfig(env);
  if (Number(cfg.recharge_open) !== 1) return apiError("\u5145\u503C\u529F\u80FD\u672A\u5F00\u542F");
  const min = Number(cfg.recharge_min) || 0;
  const max = Number(cfg.recharge_max) || 0;
  if (min > 0 && amount < min) return apiError(`\u6700\u4F4E\u5145\u503C${min}\u5143`);
  if (max > 0 && amount > max) return apiError(`\u6700\u9AD8\u5145\u503C${max}\u5143`);
  const pay = await dbFirst(env, "SELECT * FROM acg_pay WHERE id=?", payId);
  if (!pay || Number(pay.commodity) !== 1) return apiError("\u5F53\u524D\u652F\u4ED8\u65B9\u5F0F\u4E0D\u53EF\u7528");
  const tradeNo = generateTradeNo();
  await dbInsert(env, "acg_recharge", {
    owner: user.id,
    trade_no: tradeNo,
    amount,
    balance: 0,
    status: 0,
    way: pay.id,
    create_time: now()
  });
  const host = url.origin;
  const callbackDomain = (cfg.callback_domain || "").trim().replace(/\/$/, "") || host;
  const returnUrl = `${host}/user/bill/index`;
  const callbackUrl = `${callbackDomain}/user/api/order/callback.${tradeNo}`;
  const gatewayUrl = await epayTrade(env, pay, tradeNo, amount, callbackUrl, returnUrl);
  if (!gatewayUrl) return apiError("\u652F\u4ED8\u65B9\u5F0F\u672A\u90E8\u7F72\u6210\u529F");
  return apiSuccess("success", { url: gatewayUrl });
}
var apiSuccess = (msg, data = null) => new Response(JSON.stringify({ code: 200, msg, data }), { status: 200, headers: { "Content-Type": "application/json; charset=utf-8" } });

// admin.js
var MANAGE_SESSION = "MANAGE_USER";
var enc2 = new TextEncoder();
var dec2 = new TextDecoder();
var b64url2 = (buf) => btoa(String.fromCharCode(...new Uint8Array(buf))).replace(/\+/g, "-").replace(/\//g, "_").replace(/=+$/, "");
async function sha256hex(input) {
  const buf = typeof input === "string" ? enc2.encode(input) : input;
  const d = await crypto.subtle.digest("SHA-256", buf);
  return [...new Uint8Array(d)].map((b) => b.toString(16).padStart(2, "0")).join("");
}
async function hmacSign(key, data) {
  const k = await crypto.subtle.importKey("raw", enc2.encode(key), { name: "HMAC", hash: "SHA-256" }, false, ["sign"]);
  const sig = await crypto.subtle.sign("HMAC", k, enc2.encode(data));
  return new Uint8Array(sig);
}
function b64urlDecodeBytes2(str) {
  const s = str.replace(/-/g, "+").replace(/_/g, "/");
  const pad = s.length % 4 === 0 ? "" : "=".repeat(4 - s.length % 4);
  const bin = atob(s + pad);
  return Uint8Array.from(bin, (c) => c.charCodeAt(0));
}
async function manageJwtSign(manage, payload, ttl) {
  const header = { alg: "HS256", typ: "JWT", mid: Number(manage.id) };
  const nowT = Math.floor(Date.now() / 1e3);
  const body = { mid: Number(manage.id), ...payload, iat: nowT, exp: nowT + ttl };
  const h = b64url2(enc2.encode(JSON.stringify(header)));
  const p = b64url2(enc2.encode(JSON.stringify(body)));
  const sig = await hmacSign(String(manage.password), `${h}.${p}`);
  return `${h}.${p}.${b64url2(sig)}`;
}
async function manageJwtVerify(token, secret) {
  try {
    const parts = token.split(".");
    if (parts.length !== 3) return null;
    const expected = await hmacSign(secret, `${parts[0]}.${parts[1]}`);
    const got = b64urlDecodeBytes2(parts[2]);
    if (expected.length !== got.length) return null;
    let diff = 0;
    for (let i = 0; i < expected.length; i++) diff |= expected[i] ^ got[i];
    if (diff !== 0) return null;
    const payload = JSON.parse(dec2.decode(Uint8Array.from(atob(parts[1].replace(/-/g, "+").replace(/_/g, "/") + (parts[1].length % 4 === 0 ? "" : "=".repeat(4 - parts[1].length % 4))), (c) => c.charCodeAt(0))));
    if (payload.exp && payload.exp < Math.floor(Date.now() / 1e3)) return null;
    return payload;
  } catch (e) {
    return null;
  }
}
function randomIdentifier() {
  const bytes = crypto.getRandomValues(new Uint8Array(32));
  return b64url2(bytes);
}
function clientInfo(request) {
  const info = requestInfo(request);
  const ua = String(request.headers.get("user-agent") || "");
  const uaDb = [...new TextEncoder().encode(ua.slice(0, 512))].map((b) => b).join("");
  const e = enc2.encode(ua);
  let type = /iPad|Tablet/i.test(ua) ? "tablet" : /Android|iPhone|Mobile/i.test(ua) ? "mobile" : "desktop";
  let os = "\u7535\u8111";
  if (/iPhone/i.test(ua)) os = "iPhone";
  else if (/iPad/i.test(ua)) os = "iPad";
  else if (/Android/i.test(ua)) os = "Android";
  else if (/Windows/i.test(ua)) os = "Windows";
  else if (/Macintosh/i.test(ua)) os = "macOS";
  else if (/Linux/i.test(ua)) os = "Linux";
  let bw = "\u6D4F\u89C8\u5668";
  if (/Edg\//i.test(ua)) bw = "Edge";
  else if (/OPR\//i.test(ua)) bw = "Opera";
  else if (/Chrome\//i.test(ua)) bw = "Chrome";
  else if (/Firefox\//i.test(ua)) bw = "Firefox";
  else if (/Safari\//i.test(ua)) bw = "Safari";
  return { ip: info.ip, ua, deviceType: type, deviceName: (os + " \xB7 " + bw).slice(0, 96), uaBytes: e.length };
}
async function issueManageSession(env, request, manage, remember) {
  const expire = remember ? 86400 * 365 : 86400;
  const expiresAt = Math.floor(Date.now() / 1e3) + expire;
  const identifier = randomIdentifier();
  const ci = clientInfo(request);
  const t = now();
  const sessionId = await dbInsert(env, "acg_manage_session", {
    manage_id: Number(manage.id),
    session_hash: await sha256hex(identifier),
    device_type: ci.deviceType,
    device_name: ci.deviceName,
    user_agent: ci.ua.slice(0, 512),
    login_ip: ci.ip,
    last_ip: ci.ip,
    created_time: t,
    last_seen_time: t,
    expires_time: expiresAt
  });
  const token = await manageJwtSign(manage, { sid: identifier }, expire);
  return { cookie: btoa(token), sessionId, expiresAt };
}
async function authenticateManage(env, request, touch = true) {
  const cookies = parseCookies(request.headers.get("Cookie") || "");
  const encoded = cookies[MANAGE_SESSION];
  if (!encoded) return null;
  let token;
  try {
    token = atob(encoded);
  } catch (e) {
    return null;
  }
  if (!token) return null;
  let mid = 0;
  try {
    const headB64 = token.split(".")[0];
    const head = JSON.parse(dec2.decode(Uint8Array.from(atob(headB64.replace(/-/g, "+").replace(/_/g, "/") + (headB64.length % 4 === 0 ? "" : "=".repeat(4 - headB64.length % 4))), (c) => c.charCodeAt(0))));
    mid = Number(head.mid) || 0;
  } catch (e) {
    return null;
  }
  if (mid < 1) return null;
  const manage = await dbFirst(env, "SELECT * FROM acg_manage WHERE id=?", mid);
  if (!manage || Number(manage.status) !== 1) return null;
  const claims = await manageJwtVerify(token, String(manage.password));
  if (!claims || !claims.sid || Number(claims.mid) !== mid) return null;
  const sid = String(claims.sid);
  if (!/^[A-Za-z0-9_-]{43}$/.test(sid)) return null;
  const sessionHash = await sha256hex(sid);
  const s = await dbFirst(
    env,
    `SELECT * FROM acg_manage_session WHERE manage_id=? AND session_hash=? AND revoked_time IS NULL AND expires_time>?`,
    mid,
    sessionHash,
    Math.floor(Date.now() / 1e3)
  );
  if (!s) return null;
  if (touch) {
    const lastSeen = Number(s.last_seen_time) || 0;
    if (lastSeen <= Math.floor(Date.now() / 1e3) - 300) {
      await dbRun(
        env,
        "UPDATE acg_manage_session SET last_seen_time=?, last_ip=? WHERE id=? AND revoked_time IS NULL",
        Math.floor(Date.now() / 1e3),
        clientInfo(request).ip,
        s.id
      );
    }
  }
  return manage;
}
async function revokeManageSession(env, request) {
  const cookies = parseCookies(request.headers.get("Cookie") || "");
  const encoded = cookies[MANAGE_SESSION];
  if (!encoded) return;
  let token;
  try {
    token = atob(encoded);
  } catch (e) {
    return;
  }
  if (!token) return;
  let mid = 0;
  try {
    const headB64 = token.split(".")[0];
    const head = JSON.parse(dec2.decode(Uint8Array.from(atob(headB64.replace(/-/g, "+").replace(/_/g, "/") + (headB64.length % 4 === 0 ? "" : "=".repeat(4 - headB64.length % 4))), (c) => c.charCodeAt(0))));
    mid = Number(head.mid) || 0;
  } catch (e) {
    return;
  }
  const manage = await dbFirst(env, "SELECT id, password FROM acg_manage WHERE id=?", mid);
  if (!manage) return;
  const claims = await manageJwtVerify(token, String(manage.password));
  if (!claims || !claims.sid) return;
  const sessionHash = await sha256hex(String(claims.sid));
  await dbRun(
    env,
    "UPDATE acg_manage_session SET revoked_time=? WHERE manage_id=? AND session_hash=? AND revoked_time IS NULL",
    now(),
    mid,
    sessionHash
  );
}
var adminJson = (obj, status = 200) => new Response(JSON.stringify(obj), {
  status,
  headers: { "Content-Type": "application/json; charset=utf-8", "X-Content-Type-Options": "nosniff" }
});
function apiOk(msg = "success", data = []) {
  return adminJson({ code: 200, msg, data });
}
function apiErr(msg, code = 0) {
  return adminJson({ code, msg, data: [] });
}
async function adminLogin(env, request, url, body = {}) {
  const username = String(body.username || "");
  const password = String(body.password || "");
  const ip = requestInfo(request).ip;
  const throttleKey = `adminlogin:${ip}`;
  if (throttle(throttleKey, 10, 600)) {
    return apiErr("\u767B\u5F55\u5C1D\u8BD5\u8FC7\u4E8E\u9891\u7E41\uFF0C\u8BF7\u7A0D\u540E\u518D\u8BD5");
  }
  const cfg = await loadConfig(env);
  if (String(cfg.admin_login_verification) !== "0") {
    const okC = await captchaCheck(env, request, "adminLogin", String(body.captcha || ""));
    if (!okC) return apiErr("\u9A8C\u8BC1\u7801\u9519\u8BEF");
  }
  const manage = await dbFirst(env, "SELECT * FROM acg_manage WHERE email=?", username);
  if (!manage) return apiErr("\u8BE5\u90AE\u7BB1\u4E0D\u5B58\u5728");
  const okP = await verifyPassword(String(manage.password), password, String(manage.salt));
  if (!okP) return apiErr("\u5BC6\u7801\u9519\u8BEF");
  if (Number(manage.status) !== 1) return apiErr("\u8D26\u53F7\u5DF2\u88AB\u6682\u505C\u4F7F\u7528");
  await dbRun(
    env,
    "UPDATE acg_manage SET last_login_time=login_time, last_login_ip=login_ip, login_time=?, login_ip=? WHERE id=?",
    now(),
    ip,
    manage.id
  );
  const issued = await issueManageSession(env, request, manage, Boolean(body.remember));
  try {
    await dbInsert(env, "acg_manage_log", {
      email: manage.email,
      nickname: manage.nickname || "",
      content: "\u767B\u5F55\u4E86\u540E\u53F0",
      create_time: now(),
      create_ip: ip,
      ua: String(request.headers.get("user-agent") || "").slice(0, 512),
      risk: 0
    });
  } catch (e) {
  }
  const res = apiOk("success", { expires_at: issued.expiresAt, session_id: issued.sessionId });
  const host = new URL(request.url).host;
  const secure = host !== "localhost" && host !== "127.0.0.1";
  res.headers.append(
    "Set-Cookie",
    `${MANAGE_SESSION}=${encodeURIComponent(issued.cookie)}; Path=/; HttpOnly; SameSite=Lax; Max-Age=${issued.expiresAt - Math.floor(Date.now() / 1e3)}`
  );
  return res;
}
async function captchaCheck(env, request, action, input) {
  try {
    return await captchaVerify(env, request, action, input);
  } catch (e) {
    return false;
  }
}
var money = (v) => Number(v || 0).toFixed(2);
var NET_SQL = "amount - COALESCE(pay_cost,0) - rent - COALESCE(rebate,0) - COALESCE(divide_amount,0)";
function dayRange(offsetDays, endOfDay) {
  const d = /* @__PURE__ */ new Date();
  d.setDate(d.getDate() + offsetDays);
  const y = d.getFullYear(), m = String(d.getMonth() + 1).padStart(2, "0"), dd = String(d.getDate()).padStart(2, "0");
  const start = Math.floor((/* @__PURE__ */ new Date(`${y}-${m}-${dd}T00:00:00`)).getTime() / 1e3);
  const end = endOfDay ? start + 86400 - 1 : Math.floor(Date.now() / 1e3);
  return [start, end];
}
function monthRange(offsetMonths) {
  const d = /* @__PURE__ */ new Date();
  const y = d.getFullYear(), m = d.getMonth() + 1;
  const first = new Date(y, m - 1 + offsetMonths, 1);
  const start = Math.floor(new Date(first.getFullYear(), first.getMonth(), 1).getTime() / 1e3);
  const lastDay = new Date(first.getFullYear(), first.getMonth() + 1, 0).getDate();
  const end = start + lastDay * 86400 - 1;
  return [start, end];
}
async function orderStats(env, time) {
  const w = time ? " WHERE status=1 AND create_time BETWEEN ? AND ?" : " WHERE status=1";
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
    const w = time ? ` WHERE status=1 AND create_time BETWEEN ? AND ?` : " WHERE status=1";
    const b = time ? [...time] : [];
    const rows = await dbRows(
      env,
      `SELECT pay_id, COUNT(*) AS num, COALESCE(SUM(amount),0) AS amount, COALESCE(SUM(COALESCE(gateway_amount, amount)),0) AS gateway FROM ${table}${w} GROUP BY pay_id`,
      ...b
    );
    return rows;
  };
  const orders = await collect("acg_order");
  const recharges = await collect("acg_user_recharge");
  const map = {};
  for (const r of orders) map[r.pay_id] = { pay_id: r.pay_id, order_num: r.num, order_amount: r.amount, recharge_num: 0, recharge_amount: "0.00", gateway: r.gateway, name: null, icon: null, num: r.num, amount: r.amount };
  for (const r of recharges) {
    if (!map[r.pay_id]) map[r.pay_id] = { pay_id: r.pay_id, order_num: 0, order_amount: "0.00", recharge_num: 0, recharge_amount: "0.00", gateway: r.gateway, name: null, icon: null, num: r.num, amount: r.amount };
    map[r.pay_id].recharge_num = r.num;
    map[r.pay_id].recharge_amount = r.amount;
    map[r.pay_id].num += r.num;
    map[r.pay_id].amount = (Number(map[r.pay_id].amount) + Number(r.amount)).toFixed(2);
  }
  const ids = Object.keys(map);
  if (ids.length) {
    const pays = await dbRows(env, `SELECT id, name, icon FROM acg_pay WHERE id IN (${ids.map(() => "?").join(",")})`, ...ids);
    for (const p of pays) {
      if (map[p.id]) {
        map[p.id].name = p.name;
        map[p.id].icon = p.icon;
      }
    }
  }
  return Object.values(map).sort((a, b) => Number(b.amount) - Number(a.amount));
}
async function dashboardOverview(env, request, url) {
  const today = dayRange(0, true);
  const nowD = Math.floor(Date.now() / 1e3);
  const yesterday = dayRange(-1, true);
  const yesterdayUntilNow = [yesterday[0], nowD - 86400];
  const dayBefore = dayRange(-2, true);
  const month = monthRange(0);
  const lastMonthFirst = monthRange(-1)[0];
  const lm = monthRange(-1);
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
  const row = await dbFirst(
    env,
    `SELECT ${selects.join(", ")} FROM acg_order WHERE status=1 AND create_time BETWEEN ? AND ?`,
    ...b,
    lm[0],
    today[1]
  );
  const stats = {};
  for (const key of Object.keys(periods)) {
    stats[key] = {
      profit: money(row ? row[`${key}_profit`] : 0),
      turnover: money(row ? row[`${key}_turnover`] : 0),
      orders: row ? Number(row[`${key}_orders`]) : 0,
      start: periods[key][0],
      end: periods[key][1]
    };
  }
  const todo = { cash_num: 0, cash_amount: "0.00", delivery_num: 0, ticket_num: 0 };
  try {
    const pendingCash = await dbFirst(env, `SELECT COUNT(*) AS num, COALESCE(SUM(amount),0) AS amount FROM acg_cash WHERE status=0`);
    if (pendingCash) {
      todo.cash_num = Number(pendingCash.num);
      todo.cash_amount = money(pendingCash.amount);
    }
  } catch (e) {
  }
  try {
    const d = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_order o WHERE o.status=1 AND o.delivery_status=0 AND o.user_id=0 AND EXISTS (SELECT 1 FROM acg_commodity c WHERE c.id=o.commodity_id AND c.delivery_way=1)`);
    if (d) todo.delivery_num = Number(d.n);
  } catch (e) {
  }
  try {
    const t = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_ticket WHERE status=0`);
    if (t) todo.ticket_num = Number(t.n);
  } catch (e) {
  }
  const manage = await authenticateManage(env, request, false);
  return apiOk("success", {
    stats,
    todo,
    is_owner: manage ? Number(manage.type) === 0 : false
  });
}
function fmtDay(ts) {
  const d = new Date(ts * 1e3);
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
}
async function daily(env, days) {
  const nowD = Math.floor(Date.now() / 1e3);
  const start = nowD - (days - 1) * 86400;
  const rows = {};
  for (let i = days - 1; i >= 0; i--) {
    rows[fmtDay(nowD - i * 86400)] = { profit: "0.00", turnover: "0.00", orders: 0, recharge: "0.00", cash: "0.00" };
  }
  try {
    const o = await dbRows(
      env,
      `SELECT (create_time / 86400) AS dayk, COUNT(*) AS orders, COALESCE(SUM(amount),0) AS turnover, COALESCE(SUM(${NET_SQL}),0) AS profit
       FROM acg_order WHERE status=1 AND create_time BETWEEN ? AND ?
       GROUP BY dayk`,
      start,
      nowD
    );
    for (const r of o) {
      const day = fmtDay(Number(r.dayk) * 86400);
      if (rows[day]) {
        rows[day].orders = Number(r.orders);
        rows[day].turnover = money(r.turnover);
        rows[day].profit = money(r.profit);
      }
    }
  } catch (e) {
  }
  try {
    const c = await dbRows(
      env,
      `SELECT (create_time / 86400) AS dayk, COALESCE(SUM(amount),0) AS amount FROM acg_cash WHERE status=1 AND create_time BETWEEN ? AND ? GROUP BY dayk`,
      start,
      nowD
    );
    for (const r of c) {
      const day = fmtDay(Number(r.dayk) * 86400);
      if (rows[day]) rows[day].cash = money(r.amount);
    }
  } catch (e) {
  }
  try {
    const r = await dbRows(
      env,
      `SELECT (create_time / 86400) AS dayk, COALESCE(SUM(amount),0) AS amount FROM acg_user_recharge WHERE status=1 AND create_time BETWEEN ? AND ? GROUP BY dayk`,
      start,
      nowD
    );
    for (const x of r) {
      const day = fmtDay(Number(x.dayk) * 86400);
      if (rows[day]) rows[day].recharge = money(x.amount);
    }
  } catch (e) {
  }
  return rows;
}
async function dashboardTrend(env, request, url, days) {
  days = Number(days) === 30 ? 30 : 7;
  const rows = await daily(env, days);
  let profit = 0, turnover = 0, orders = 0, recharge = 0;
  const list = [];
  for (const [day, r] of Object.entries(rows)) {
    profit += Number(r.profit);
    turnover += Number(r.turnover);
    orders += Number(r.orders);
    recharge += Number(r.recharge);
    list.push({ date: day, profit: r.profit, turnover: r.turnover, orders: r.orders, recharge: r.recharge });
  }
  return apiOk("success", {
    days: list,
    total: { profit: money(profit), turnover: money(turnover), orders, recharge: money(recharge) }
  });
}
async function dashboardData(env, request, url, type) {
  let time = null;
  type = Number(type) || 0;
  const nowD = Math.floor(Date.now() / 1e3);
  if (type === 0) time = [dayRange(0, false)[0], nowD];
  else if (type === 1) time = dayRange(-1, true);
  else if (type === 2) {
    const d = /* @__PURE__ */ new Date();
    const day = (d.getDay() + 6) % 7;
    const s = new Date(d.getFullYear(), d.getMonth(), d.getDate() - day);
    time = [Math.floor(s.getTime() / 1e3), nowD];
  } else if (type === 3) time = [monthRange(0)[0], nowD];
  else if (type === 4) time = null;
  const order = await orderStats(env, time);
  const unpaidQ = time ? { sql: " WHERE status=0 AND create_time BETWEEN ? AND ?", b: time } : { sql: " WHERE status=0", b: [] };
  const cashQ = time ? { sql: " WHERE create_time BETWEEN ? AND ?", b: time } : { sql: "", b: [] };
  const rechargeQ = time ? { sql: " WHERE status=1 AND create_time BETWEEN ? AND ?", b: time } : { sql: " WHERE status=1", b: [] };
  const userQ = time ? { sql: " WHERE create_time BETWEEN ? AND ?", b: time } : { sql: "", b: [] };
  const businessQ = time ? { sql: " WHERE create_time BETWEEN ? AND ?", b: time } : { sql: "", b: [] };
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
    dbFirst(env, `SELECT COUNT(*) AS n FROM acg_order${unpaidQ.sql}`, ...unpaidQ.b)
  ]);
  const orderNum = Number(order ? order.order_num : 0);
  const turnover = money(order ? order.turnover : 0);
  const avg = orderNum > 0 ? (Number(turnover) / orderNum).toFixed(2) : "0.00";
  return apiOk("success", {
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
    buyer_num: Number(order ? Number(order.member_buyers) + Number(order.guest_buyers) : 0),
    avg_order: avg,
    unpaid_order_num: Number(unpaidNum ? unpaidNum.n : 0),
    cash_paid_out: money(cash ? cash.paid_out : 0),
    cash_to_balance: money(cash ? cash.to_balance : 0),
    cash_fee_income: money(cash ? cash.fee_income : 0),
    recharge_num: Number(recharge ? recharge.num : 0),
    channels: await channelStats(env, time),
    gateway_cny: false,
    range: time ? { start: time[0], end: time[1] } : null
  });
}
var intList = (v, name) => {
  const arr = Array.isArray(v) ? v : String(v ?? "").split(",");
  const ids = [];
  for (const c of arr) {
    const n = Number(String(c).trim());
    if (!Number.isInteger(n) || n <= 0) throw new Error(`${name}\u5FC5\u987B\u662F\u6B63\u6574\u6570`);
    ids.push(n);
  }
  return [...new Set(ids)];
};
async function categoryData(env, request, url, body = {}) {
  const rows = await dbRows(env, "SELECT * FROM acg_category WHERE owner=0 ORDER BY sort ASC, id ASC");
  const byId = new Map(rows.map((r) => [Number(r.id), r]));
  const children = /* @__PURE__ */ new Map();
  const roots = [];
  for (const r of rows) {
    const pid = Number(r.pid) || 0;
    if (pid > 0 && pid !== Number(r.id) && byId.has(pid)) {
      if (!children.has(pid)) children.set(pid, []);
      children.get(pid).push(r);
    } else roots.push(r);
  }
  const ordered = [];
  const visited = /* @__PURE__ */ new Set();
  const walk = (row) => {
    const id = Number(row.id);
    if (visited.has(id)) return;
    visited.add(id);
    ordered.push(row);
    for (const ch of children.get(id) || []) walk(ch);
  };
  for (const r of roots) walk(r);
  for (const r of rows) walk(r);
  const list = ordered.map((r) => ({ ...r, share_url: `/cat/${r.id}` }));
  return apiOk("success", { list, page: 1, limit: -1, count: list.length, records: list.length });
}
async function categorySave(env, request, url, body = {}) {
  const allowed = ["id", "pid", "icon", "name", "sort", "hide", "status", "user_level_config"];
  const map = {};
  for (const k of allowed) if (Object.prototype.hasOwnProperty.call(body, k)) map[k] = body[k];
  const id = Number(map.id) || 0;
  if (id === 0 || !await dbFirst(env, "SELECT id FROM acg_category WHERE id=?", id)) {
    if (!map.name || String(map.name).trim() === "") return apiErr("\u5206\u7C7B\u540D\u79F0\u4E0D\u80FD\u4E3A\u7A7A");
  }
  if (Object.prototype.hasOwnProperty.call(map, "status") && !["0", "1"].includes(String(map.status))) return apiErr("\u5206\u7C7B\u72B6\u6001\u53C2\u6570\u4E0D\u6B63\u786E");
  if (Object.prototype.hasOwnProperty.call(map, "hide") && !["0", "1"].includes(String(map.hide))) return apiErr("\u5206\u7C7B\u9690\u85CF\u53C2\u6570\u4E0D\u6B63\u786E");
  if (Object.prototype.hasOwnProperty.call(map, "sort")) {
    const s = Number(map.sort);
    if (!Number.isInteger(s) || s < 0 || s > 65535) return apiErr("\u5206\u7C7B\u6392\u5E8F\u5FC5\u987B\u662F 0 \u5230 65535 \u7684\u6574\u6570");
  }
  const hasParent = Object.prototype.hasOwnProperty.call(map, "pid");
  let parentId = hasParent ? Number(map.pid) || 0 : 0;
  if (hasParent && parentId > 0) {
    const parent = await dbFirst(env, "SELECT * FROM acg_category WHERE owner=0 AND id=?", parentId);
    if (!parent) return apiErr("\u7236\u7EA7\u5206\u7C7B\u4E0D\u5B58\u5728\u6216\u4E0D\u5C5E\u4E8E\u540C\u4E00\u521B\u5EFA\u8005");
    if (id > 0 && parentId === id) return apiErr("\u5206\u7C7B\u4E0D\u80FD\u8BBE\u4E3A\u81EA\u5DF1\u7684\u5B50\u5206\u7C7B");
    let cur = parent, seen = /* @__PURE__ */ new Set();
    while (cur) {
      const pk = Number(cur.id);
      if (seen.has(pk)) return apiErr("\u5206\u7C7B\u5C42\u7EA7\u5B58\u5728\u5FAA\u73AF\uFF0C\u8BF7\u91CD\u65B0\u9009\u62E9\u7236\u7EA7\u5206\u7C7B");
      seen.add(pk);
      if (id > 0 && pk === id) return apiErr("\u4E0D\u80FD\u9009\u62E9\u5F53\u524D\u5206\u7C7B\u7684\u5B50\u5206\u7C7B\u4F5C\u4E3A\u7236\u7EA7");
      const nid = Number(cur.pid) || 0;
      cur = nid > 0 ? await dbFirst(env, "SELECT * FROM acg_category WHERE owner=0 AND id=?", nid) : null;
    }
  }
  const data = {};
  for (const k of ["icon", "name", "user_level_config"]) if (Object.prototype.hasOwnProperty.call(map, k)) data[k] = map[k];
  if (Object.prototype.hasOwnProperty.call(map, "status")) data.status = Number(map.status);
  if (Object.prototype.hasOwnProperty.call(map, "hide")) data.hide = Number(map.hide);
  if (Object.prototype.hasOwnProperty.call(map, "sort")) data.sort = Number(map.sort);
  if (hasParent) data.pid = parentId > 0 ? parentId : null;
  let newId = 0;
  if (id > 0) {
    await dbUpdate(env, "acg_category", data, "id=?", id);
  } else {
    await dbInsert(env, "acg_category", { ...data, name: String(map.name || "").trim(), sort: data.sort ?? 0, status: data.status ?? 1, hide: data.hide ?? 0, owner: 0, create_time: now(), pid: data.pid ?? null }).then((id2) => newId = id2);
  }
  try {
    await dbInsert(env, "acg_manage_log", { email: "admin", nickname: "", content: "[\u65B0\u589E/\u4FEE\u6539]\u5546\u54C1\u5206\u7C7B", create_time: now(), create_ip: requestInfo(request).ip, ua: String(request.headers.get("user-agent") || "").slice(0, 512), risk: 0 });
  } catch (e) {
  }
  return apiOk("\uFF08\uFF3E\u2200\uFF3E\uFF09\u4FDD\u5B58\u6210\u529F", { id: newId || id });
}
async function categoryStatus(env, request, url, body = {}) {
  let list;
  try {
    list = intList(body.list, "\u5206\u7C7BID");
  } catch (e) {
    return apiErr(e.message);
  }
  if (!list.length || !["0", "1"].includes(String(body.status))) return apiErr("\u5206\u7C7B\u72B6\u6001\u8BF7\u6C42\u53C2\u6570\u4E0D\u6B63\u786E");
  const status = Number(body.status);
  const cats = await dbRows(env, "SELECT id, pid, owner FROM acg_category");
  const byId = new Map(cats.map((c) => [Number(c.id), c]));
  const children = /* @__PURE__ */ new Map();
  for (const c of cats) {
    const pid = Number(c.pid) || 0;
    if (!children.has(pid)) children.set(pid, []);
    children.get(pid).push(Number(c.id));
  }
  const targets = /* @__PURE__ */ new Set();
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
      let id = rootId, seen = /* @__PURE__ */ new Set();
      while (id > 0 && byId.has(id)) {
        if (seen.has(id) || Number(byId.get(id).owner) !== owner) return apiErr("\u5206\u7C7B\u5C42\u7EA7\u65E0\u6548\uFF0C\u65E0\u6CD5\u542F\u7528");
        seen.add(id);
        targets.add(id);
        id = Number(byId.get(id).pid) || 0;
      }
    }
  }
  if (!targets.size) return apiErr("\u6CA1\u6709\u53EF\u66F4\u65B0\u7684\u5206\u7C7B");
  await dbRun(env, `UPDATE acg_category SET status=? WHERE id IN (${[...targets].map(() => "?").join(",")})`, status, ...[...targets]);
  return apiOk("\u5206\u7C7B\u72B6\u6001\u5DF2\u7ECF\u66F4\u65B0");
}
var b64urlEncode = (str) => btoa(unescape(encodeURIComponent(str))).replace(/\+/g, "-").replace(/\//g, "_").replace(/=+$/, "");
async function categoryDeleteImpact(env, request, url, body = {}, manage) {
  let list;
  try {
    list = intList(body.list, "\u5206\u7C7BID");
  } catch (e) {
    return apiErr(e.message);
  }
  if (!list.length) return apiErr("\u4F60\u8FD8\u6CA1\u6709\u9009\u62E9\u5546\u54C1\u5206\u7C7B");
  const impact = await safeCategoryDeleteImpact(env, list);
  const token = await issueDeleteToken(env, manage, list, impact);
  const publicKeys = ["category_count", "scope_count", "descendant_count", "hierarchy_cycle_count", "commodity_count", "order_count", "card_count", "coupon_count", "used_coupon_count", "user_category_count", "config_reference_count", "can_delete"];
  const pub = {};
  for (const k of publicKeys) pub[k] = impact[k];
  pub.preview_token = token;
  pub.preview_expires_in = 180;
  return apiOk("success", pub);
}
async function issueDeleteToken(env, manage, ids, impact) {
  const key = await sha256hex("category-delete-preview-v1|" + manage.password);
  const snapshot = await sha256hex(JSON.stringify(impact.snapshot));
  const payload = { ids, snapshot, manage_id: Number(manage.id), iat: now(), exp: now() + 180 };
  const bodyB64 = b64urlEncode(JSON.stringify(payload));
  const sig = await hmacHex(key, bodyB64);
  return `${bodyB64}.${sig}`;
}
async function hmacHex(key, data) {
  const sig = await hmacSign(key, data);
  return [...sig].map((b) => b.toString(16).padStart(2, "0")).join("");
}
async function safeCategoryDeleteImpact(env, requestedIds) {
  requestedIds = [...requestedIds].sort((a, b) => a - b);
  const cats = await dbRows(env, "SELECT id, pid FROM acg_category");
  const existing = new Map(cats.map((c) => [Number(c.id), Number(c.pid) || 0]));
  const children = /* @__PURE__ */ new Map();
  for (const c of cats) {
    const pid = Number(c.pid) || 0;
    if (!children.has(pid)) children.set(pid, []);
    children.get(pid).push(Number(c.id));
  }
  for (const id of requestedIds) if (!existing.has(id)) throw new Error("\u90E8\u5206\u5546\u54C1\u5206\u7C7B\u4E0D\u5B58\u5728\uFF0C\u8BF7\u5237\u65B0\u540E\u91CD\u8BD5");
  const visited = new Set(requestedIds);
  const q = [...requestedIds];
  while (q.length) {
    const id = q.shift();
    for (const ch of children.get(id) || []) {
      if (!visited.has(ch)) {
        visited.add(ch);
        q.push(ch);
      }
    }
  }
  const scopeIds = [...visited].sort((a, b) => a - b);
  const descendantIds = scopeIds.filter((id) => !requestedIds.includes(id));
  const commodityIds = [];
  if (scopeIds.length) {
    const rows = await dbRows(env, `SELECT id FROM acg_commodity WHERE category_id IN (${scopeIds.map(() => "?").join(",")})`, ...scopeIds);
    commodityIds.push(...rows.map((r) => Number(r.id)));
  }
  commodityIds.sort((a, b) => a - b);
  let orderCount = 0, cardCount = 0;
  if (commodityIds.length) {
    const o = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_order WHERE commodity_id IN (${commodityIds.map(() => "?").join(",")})`, ...commodityIds);
    orderCount = o ? Number(o.n) : 0;
    const c2 = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_card WHERE commodity_id IN (${commodityIds.map(() => "?").join(",")})`, ...commodityIds);
    cardCount = c2 ? Number(c2.n) : 0;
  }
  let couponIds2 = [], userCategoryIds = [], configReferenceIds = [];
  if (scopeIds.length) {
    const cp = await dbRows(env, `SELECT id, status, trade_no FROM acg_coupon WHERE category_id IN (${scopeIds.map(() => "?").join(",")})`, ...scopeIds);
    couponIds2 = cp.map((r) => Number(r.id));
    const uc = await dbRows(env, `SELECT id FROM acg_user_category WHERE category_id IN (${scopeIds.map(() => "?").join(",")})`, ...scopeIds);
    userCategoryIds = uc.map((r) => Number(r.id));
    const cf = await dbRows(env, `SELECT id FROM acg_config WHERE "key"='default_category' AND value IN (${scopeIds.map(() => "?").join(",")})`, ...scopeIds.map(String));
    configReferenceIds = cf.map((r) => Number(r.id));
  }
  const snapshot = { ids: requestedIds, scopeIds, commodityIds };
  return {
    category_count: requestedIds.length,
    scope_count: scopeIds.length,
    descendant_count: descendantIds.length,
    hierarchy_cycle_count: 0,
    commodity_count: commodityIds.length,
    order_count: orderCount,
    card_count: cardCount,
    coupon_count: couponIds2.length,
    used_coupon_count: 0,
    user_category_count: userCategoryIds.length,
    config_reference_count: configReferenceIds.length,
    can_delete: true,
    snapshot,
    scope_ids: scopeIds,
    commodity_ids: commodityIds
  };
}
async function categoryDel(env, request, url, body = {}, manage) {
  let list;
  try {
    list = intList(body.list, "\u5206\u7C7BID");
  } catch (e) {
    return apiErr(e.message);
  }
  if (!list.length) return apiErr("\u4F60\u8FD8\u6CA1\u6709\u9009\u62E9\u5546\u54C1\u5206\u7C7B");
  const token = String(body.preview_token || "");
  const [b64, sig] = token.split(".");
  const key = await sha256hex("category-delete-preview-v1|" + manage.password);
  const expect = await hmacHex(key, b64 || "");
  if (!/^[a-f0-9]{64}$/.test(sig || "") || sig !== expect) return apiErr("\u5220\u9664\u9884\u89C8\u51ED\u8BC1\u65E0\u6548\uFF0C\u8BF7\u91CD\u65B0\u9884\u89C8");
  let payload;
  try {
    const json = decodeURIComponent(escape(atob(b64.replace(/-/g, "+").replace(/_/g, "/") + (b64.length % 4 === 0 ? "" : "=".repeat(4 - b64.length % 4)))));
    payload = JSON.parse(json);
  } catch (e) {
    return apiErr("\u5220\u9664\u9884\u89C8\u51ED\u8BC1\u65E0\u6548\uFF0C\u8BF7\u91CD\u65B0\u9884\u89C8");
  }
  payload.ids = (payload.ids || []).map(Number).sort((a, b) => a - b);
  if (JSON.stringify(payload.ids) !== JSON.stringify([...list].sort((a, b) => a - b))) return apiErr("\u5220\u9664\u9884\u89C8\u5DF2\u8FC7\u671F\u6216\u8303\u56F4\u4E0D\u4E00\u81F4\uFF0C\u8BF7\u91CD\u65B0\u9884\u89C8");
  if (Number(payload.exp) < now()) return apiErr("\u5220\u9664\u9884\u89C8\u5DF2\u8FC7\u671F\u6216\u8303\u56F4\u4E0D\u4E00\u81F4\uFF0C\u8BF7\u91CD\u65B0\u9884\u89C8");
  const impact = await safeCategoryDeleteImpact(env, list);
  if (impact.commodity_ids.length) {
    await dbRun(env, `DELETE FROM acg_card WHERE commodity_id IN (${impact.commodity_ids.map(() => "?").join(",")})`, ...impact.commodity_ids);
    await dbRun(env, `UPDATE acg_order SET card_id=NULL, secret=NULL WHERE commodity_id IN (${impact.commodity_ids.map(() => "?").join(",")})`, ...impact.commodity_ids);
    await dbRun(env, `DELETE FROM acg_commodity WHERE id IN (${impact.commodity_ids.map(() => "?").join(",")})`, ...impact.commodity_ids);
  }
  if (impact.scope_ids.length) {
    await dbRun(env, `DELETE FROM acg_coupon WHERE category_id IN (${impact.scope_ids.map(() => "?").join(",")}) AND id NOT IN (SELECT DISTINCT coupon_id FROM acg_order WHERE coupon_id IS NOT NULL)`, ...impact.scope_ids);
    await dbRun(env, `DELETE FROM acg_user_category WHERE category_id IN (${impact.scope_ids.map(() => "?").join(",")})`, ...impact.scope_ids);
    await dbRun(env, `DELETE FROM acg_category WHERE id IN (${impact.scope_ids.map(() => "?").join(",")})`, ...impact.scope_ids);
  }
  try {
    await dbRun(env, `UPDATE acg_config SET value='' WHERE "key"='default_category' AND value IN (${impact.scope_ids.map(() => "?").join(",")})`, ...impact.scope_ids.map(String));
  } catch (e) {
  }
  return apiOk("\uFF08\uFF3E\u2200\uFF3E\uFF09\u79FB\u9664\u6210\u529F", { category_count: impact.scope_count, commodity_count: impact.commodity_count, order_count: impact.order_count, coupon_count: impact.coupon_count });
}
async function categoryReorder(env, request, url, body = {}) {
  let list;
  try {
    list = intList(body.list, "\u5206\u7C7BID");
  } catch (e) {
    return apiErr(e.message);
  }
  if (list.length < 2) return apiErr("\u81F3\u5C11\u9700\u8981\u4E24\u4E2A\u540C\u7EA7\u5206\u7C7B\u624D\u80FD\u8C03\u6574\u987A\u5E8F");
  const rows = await dbRows(env, `SELECT id, pid, owner FROM acg_category WHERE id IN (${list.map(() => "?").join(",")})`, ...list);
  if (rows.length !== list.length) return apiErr("\u90E8\u5206\u5206\u7C7B\u5DF2\u4E0D\u5B58\u5728\uFF0C\u8BF7\u5237\u65B0\u540E\u518D\u8C03\u6574\u987A\u5E8F");
  const owner = Number(rows[0].owner), pid = Number(rows[0].pid) || 0;
  for (const r of rows) if (Number(r.owner) !== owner || (Number(r.pid) || 0) !== pid) return apiErr("\u53EA\u80FD\u5728\u540C\u4E00\u4E2A\u7236\u7EA7\u5206\u7C7B\u4E0B\u8C03\u6574\u987A\u5E8F");
  const sibs = pid > 0 ? await dbRows(env, "SELECT id FROM acg_category WHERE owner=? AND pid=?", owner, pid) : await dbRows(env, "SELECT id FROM acg_category WHERE owner=? AND (pid IS NULL OR pid=0)", owner);
  const sibIds = sibs.map((r) => Number(r.id)).sort((a, b) => a - b);
  const sub = [...list].sort((a, b) => a - b);
  if (JSON.stringify(sibIds) !== JSON.stringify(sub)) return apiErr("\u540C\u7EA7\u5206\u7C7B\u5DF2\u53D1\u751F\u53D8\u5316\uFF0C\u8BF7\u5237\u65B0\u540E\u518D\u8C03\u6574\u987A\u5E8F");
  for (let i = 0; i < list.length; i++) await dbRun(env, "UPDATE acg_category SET sort=? WHERE id=?", i, list[i]);
  return apiOk("\u6392\u5E8F\u5DF2\u4FDD\u5B58");
}
async function commodityData(env, request, url, body = {}) {
  const page = Math.max(1, Number(body.page) || 1);
  const pageSize = Math.min(100, Math.max(1, Number(body.limit) || 10));
  const wheres = [];
  const params = [];
  if (!body.display_scope || Number(body.display_scope) === 1) {
    wheres.push("owner=0");
  }
  if (body.category_id) {
    wheres.push("category_id=?");
    params.push(Number(body.category_id));
  }
  if (body.name) {
    wheres.push("name LIKE ?");
    params.push(`%${body.name}%`);
  }
  if (body.status !== void 0 && body.status !== "") {
    wheres.push("status=?");
    params.push(Number(body.status));
  }
  const where = wheres.length ? " WHERE " + wheres.join(" AND ") : "";
  const total = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_commodity${where}`, ...params);
  const count = total ? Number(total.n) : 0;
  const rows = await dbRows(
    env,
    `SELECT c.*, (SELECT COUNT(*) FROM acg_card k WHERE k.commodity_id=c.id AND k.status=0) AS card_count,
            (SELECT COUNT(*) FROM acg_card k WHERE k.commodity_id=c.id AND k.status=1) AS card_success_count,
            (SELECT COALESCE(SUM(amount-COALESCE(pay_cost,0)),0) FROM acg_order o WHERE o.commodity_id=c.id AND o.status=1) AS order_all_amount
     FROM acg_commodity c${where} ORDER BY c.sort ASC, c.id ASC LIMIT ? OFFSET ?`,
    ...params,
    pageSize,
    (page - 1) * pageSize
  );
  const list = [];
  for (const r of rows) {
    list.push({
      ...r,
      category: r.category_id ? await dbFirst(env, "SELECT id, name FROM acg_category WHERE id=?", r.category_id) || null : null,
      shared: null,
      owner: { id: 0, username: "\u7AD9\u957F", avatar: null }
    });
  }
  return apiOk("success", { list, page, limit: pageSize, count, records: count });
}
async function commoditySave(env, request, url, body = {}, manage) {
  const id = Number(body.id) || 0;
  const allowed = ["category_id", "name", "description", "cover", "factory_price", "price", "user_price", "status", "api_status", "delivery_way", "delivery_auto_mode", "delivery_message", "contact_type", "password_status", "sort", "coupon", "seckill_status", "seckill_start_time", "seckill_end_time", "draft_status", "draft_premium", "inventory_hidden", "leave_message", "recommend", "send_email", "only_user", "purchase_count", "minimum", "maximum", "hide", "code", "widget", "tags", "level_price", "level_disable", "config"];
  const map = {};
  for (const k of allowed) if (Object.prototype.hasOwnProperty.call(body, k)) map[k] = body[k];
  if (id > 0) {
    const cur = await dbFirst(env, "SELECT * FROM acg_commodity WHERE id=?", id);
    if (!cur) return apiErr("\u5546\u54C1\u4E0D\u5B58\u5728");
  }
  if (id === 0) {
    if (!map.name || String(map.name).trim() === "") return apiErr("\u5546\u54C1\u540D\u79F0\u4E0D\u80FD\u4E3A\u7A7A");
    if (!map.category_id || Number(map.category_id) <= 0) return apiErr("\u8BF7\u9009\u62E9\u5546\u54C1\u5206\u7C7B");
    if (map.price === void 0 || Number(map.price) < 0 || map.user_price !== void 0 && Number(map.user_price) < 0) return apiErr("\u5546\u54C1\u5355\u4EF7\u4E0D\u80FD\u4F4E\u4E8E0");
  }
  const deliveryWay = Number(map.delivery_way) || 0;
  const data = {};
  for (const k of ["category_id", "name", "description", "cover", "delivery_message", "leave_message", "widget", "tags", "level_price", "code", "config"]) {
    if (Object.prototype.hasOwnProperty.call(map, k)) data[k] = k === "category_id" ? Number(map[k]) : String(map[k]);
  }
  for (const k of ["status", "api_status", "delivery_way", "delivery_auto_mode", "contact_type", "password_status", "sort", "coupon", "seckill_status", "seckill_start_time", "seckill_end_time", "draft_status", "inventory_hidden", "recommend", "send_email", "only_user", "purchase_count", "minimum", "maximum", "hide", "level_disable"]) {
    if (Object.prototype.hasOwnProperty.call(map, k)) data[k] = Number(map[k]) || 0;
  }
  for (const k of ["factory_price", "price", "user_price", "draft_premium"]) {
    if (Object.prototype.hasOwnProperty.call(map, k)) data[k] = Number(map[k] || 0);
  }
  if (Object.prototype.hasOwnProperty.call(map, "code") && !data.code) data.code = md5hex(now() + Math.random()).slice(0, 16).toUpperCase();
  let newId = 0;
  if (id > 0) {
    await dbUpdate(env, "acg_commodity", data, "id=?", id);
  } else {
    if (!data.code) data.code = md5hex(now() + Math.random()).slice(0, 16).toUpperCase();
    await dbInsert(env, "acg_commodity", { ...data, owner: 0, create_time: now(), stock: Number(body.stock) || 0, status: data.status ?? 1, sort: data.sort ?? 0 }).then((id2) => newId = id2);
  }
  try {
    await dbInsert(env, "acg_manage_log", { email: manage.email, nickname: "", content: "[\u65B0\u589E/\u4FEE\u6539]\u5546\u54C1", create_time: now(), create_ip: requestInfo(request).ip, ua: "", risk: 0 });
  } catch (e) {
  }
  return apiOk("\uFF08\uFF3E\u2200\uFF3E\uFF09\u4FDD\u5B58\u6210\u529F", { id: newId || id });
}
async function commodityStatus(env, request, url, body = {}) {
  let list;
  try {
    list = intList(body.list, "\u5546\u54C1ID");
  } catch (e) {
    return apiErr(e.message);
  }
  if (!list.length || !["0", "1"].includes(String(body.status))) return apiErr("\u5546\u54C1\u72B6\u6001\u8BF7\u6C42\u53C2\u6570\u4E0D\u6B63\u786E");
  await dbRun(env, `UPDATE acg_commodity SET status=? WHERE id IN (${list.map(() => "?").join(",")})`, Number(body.status), ...list);
  return apiOk("\u5546\u54C1\u72B6\u6001\u5DF2\u7ECF\u66F4\u65B0");
}
async function commodityDel(env, request, url, body = {}) {
  let list;
  try {
    list = intList(body.list, "\u5546\u54C1ID");
  } catch (e) {
    return apiErr(e.message);
  }
  if (!list.length) return apiErr("\u4F60\u8FD8\u6CA1\u6709\u9009\u62E9\u5546\u54C1");
  await dbRun(env, `DELETE FROM acg_card WHERE commodity_id IN (${list.map(() => "?").join(",")})`, ...list);
  await dbRun(env, `UPDATE acg_order SET card_id=NULL, secret=NULL WHERE commodity_id IN (${list.map(() => "?").join(",")})`, ...list);
  await dbRun(env, `DELETE FROM acg_commodity WHERE id IN (${list.map(() => "?").join(",")})`, ...list);
  return apiOk("\uFF08\uFF3E\u2200\uFF3E\uFF09\u79FB\u9664\u6210\u529F");
}
async function cardData(env, request, url, body = {}) {
  const page = Math.max(1, Number(body.page) || 1);
  const pageSize = Math.min(100, Math.max(1, Number(body.limit) || 10));
  const wheres = [];
  const params = [];
  if (body.commodity_id) {
    wheres.push("commodity_id=?");
    params.push(Number(body.commodity_id));
  }
  if (body.status !== void 0 && body.status !== "") {
    wheres.push("status=?");
    params.push(Number(body.status));
  }
  if (body.secret) {
    wheres.push("secret LIKE ?");
    params.push(`%${body.secret}%`);
  }
  const where = wheres.length ? " WHERE " + wheres.join(" AND ") : "";
  const total = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_card${where}`, ...params);
  const count = total ? Number(total.n) : 0;
  const rows = await dbRows(env, `SELECT * FROM acg_card${where} ORDER BY id DESC LIMIT ? OFFSET ?`, ...params, pageSize, (page - 1) * pageSize);
  const list = [];
  for (const r of rows) {
    list.push({
      ...r,
      commodity: r.commodity_id ? await dbFirst(env, "SELECT id, name, cover FROM acg_commodity WHERE id=?", r.commodity_id) || null : null,
      order: r.order_id ? await dbFirst(env, "SELECT id, trade_no FROM acg_order WHERE id=?", r.order_id) || null : null
    });
  }
  return apiOk("success", { list, page, limit: pageSize, count, records: count });
}
async function cardSave(env, request, url, body = {}, manage) {
  const commodityId = Number(body.commodity_id) || 0;
  if (commodityId <= 0) return apiErr("\u8BF7\u9009\u62E9\u5546\u54C1");
  const commodity = await dbFirst(env, "SELECT * FROM acg_commodity WHERE id=?", commodityId);
  if (!commodity) return apiErr("\u5546\u54C1\u4E0D\u5B58\u5728");
  const secrets = String(body.secret || "").split(/\r?\n|[,，]/).map((s) => s.trim()).filter(Boolean);
  if (!secrets.length) return apiErr("\u5361\u5BC6\u4E0D\u80FD\u4E3A\u7A7A");
  if (secrets.length > 5e3) return apiErr("\u5355\u6B21\u6700\u591A\u5BFC\u5165 5000 \u6761\u5361\u5BC6");
  const t = now();
  const ids = [];
  for (const secret of secrets.slice(0, 5e3)) {
    const cid = await dbInsert(env, "acg_card", {
      owner: 0,
      commodity_id: commodityId,
      draft: 0,
      secret,
      create_time: t,
      status: 0,
      cost: Number(body.cost) || 0,
      race: body.race || null,
      sku: body.sku || null
    });
    ids.push(cid);
  }
  try {
    await dbInsert(env, "acg_manage_log", { email: manage.email, nickname: "", content: `[\u65B0\u589E]\u5361\u5BC6 ${secrets.length} \u6761`, create_time: now(), create_ip: requestInfo(request).ip, ua: "", risk: 0 });
  } catch (e) {
  }
  return apiOk(`\u6210\u529F\u5BFC\u5165 ${secrets.length} \u6761\u5361\u5BC6`, { num: secrets.length, id: ids[0] });
}
async function cardEdit(env, request, url, body = {}) {
  const id = Number(body.id) || 0;
  const cur = await dbFirst(env, "SELECT * FROM acg_card WHERE id=?", id);
  if (!cur) return apiErr("\u5361\u5BC6\u4E0D\u5B58\u5728");
  const data = {};
  if (Object.prototype.hasOwnProperty.call(body, "note")) data.note = String(body.note || "");
  if (Object.prototype.hasOwnProperty.call(body, "cost")) data.cost = Number(body.cost) || 0;
  if (Object.prototype.hasOwnProperty.call(body, "secret") && String(body.secret).trim()) data.secret = String(body.secret).trim();
  if (Object.keys(data).length) await dbUpdate(env, "acg_card", data, "id=?", id);
  return apiOk("\u4FDD\u5B58\u6210\u529F");
}
async function cardLock(env, request, url, body = {}, lock = true) {
  let list;
  try {
    list = intList(body.list, "\u5361\u5BC6ID");
  } catch (e) {
    return apiErr(e.message);
  }
  if (!list.length) return apiErr("\u4F60\u8FD8\u6CA1\u6709\u9009\u62E9\u5361\u5BC6");
  await dbRun(env, `UPDATE acg_card SET status=? WHERE id IN (${list.map(() => "?").join(",")}) AND status=0`, lock ? 2 : 0, ...list);
  return apiOk(lock ? "\u9501\u5B9A\u6210\u529F" : "\u89E3\u9501\u6210\u529F");
}
async function cardDel(env, request, url, body = {}) {
  let list;
  try {
    list = intList(body.list, "\u5361\u5BC6ID");
  } catch (e) {
    return apiErr(e.message);
  }
  if (!list.length) return apiErr("\u4F60\u8FD8\u6CA1\u6709\u9009\u62E9\u5361\u5BC6");
  await dbRun(env, `DELETE FROM acg_card WHERE id IN (${list.map(() => "?").join(",")}) AND status=0`, ...list);
  return apiOk("\uFF08\uFF3E\u2200\uFF3E\uFF09\u79FB\u9664\u6210\u529F");
}
async function orderData(env, request, url, body = {}) {
  const page = Math.max(1, Number(body.page) || 1);
  const pageSize = Math.min(100, Math.max(1, Number(body.limit) || 10));
  const wheres = [];
  const params = [];
  const addEqual = (key, col) => {
    if (body[key] !== void 0 && body[key] !== "") {
      wheres.push(`${col}=?`);
      params.push(body[key]);
    }
  };
  addEqual("equal-trade_no", "trade_no");
  addEqual("equal-status", "status");
  addEqual("equal-delivery_status", "delivery_status");
  addEqual("equal-commodity_id", "commodity_id");
  addEqual("equal-create_device", "create_device");
  addEqual("equal-pay_id", "pay_id");
  addEqual("equal-owner", "owner");
  if (body["search-secret"] !== void 0 && String(body["search-secret"]).trim() !== "") {
    wheres.push("secret LIKE ?");
    params.push(`%${String(body["search-secret"]).trim()}%`);
  }
  if (body["equal-contact"] !== void 0 && String(body["equal-contact"]).trim() !== "") {
    wheres.push("contact=?");
    params.push(String(body["equal-contact"]).trim());
  }
  const start = body["betweenStart-create_time"], end = body["betweenEnd-create_time"];
  if (start) {
    wheres.push("create_time>=?");
    params.push(Number(start));
  }
  if (end) {
    wheres.push("create_time<=?");
    params.push(Number(end));
  }
  const where = wheres.length ? " WHERE " + wheres.join(" AND ") : "";
  const total = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_order${where}`, ...params);
  const count = total ? Number(total.n) : 0;
  const sumRow = await dbFirst(env, `SELECT COALESCE(SUM(amount),0) AS total_amount, COALESCE(SUM(pay_cost),0) AS total_cost FROM acg_order${where}`, ...params);
  const rows = await dbRows(env, `SELECT * FROM acg_order${where} ORDER BY id DESC LIMIT ? OFFSET ?`, ...params, pageSize, (page - 1) * pageSize);
  const list = [];
  for (const r of rows) {
    list.push({
      ...r,
      coupon: r.coupon_id ? await dbFirst(env, "SELECT id, code FROM acg_coupon WHERE id=?", r.coupon_id) || null : null,
      owner: r.owner ? await dbFirst(env, "SELECT id, username, avatar, recharge FROM acg_user WHERE id=?", r.owner) || null : null,
      user: r.user_id ? await dbFirst(env, "SELECT id, username, avatar, recharge FROM acg_user WHERE id=?", r.user_id) || null : null,
      promote: r.from ? await dbFirst(env, "SELECT id, username, avatar, recharge FROM acg_user WHERE id=?", r.from) || null : null,
      commodity: r.commodity_id ? await dbFirst(env, "SELECT id, name, cover, price, delivery_way, contact_type FROM acg_commodity WHERE id=?", r.commodity_id) || null : null,
      pay: r.pay_id ? await dbFirst(env, "SELECT id, name, icon FROM acg_pay WHERE id=?", r.pay_id) || null : null,
      substationUser: r.substation_user_id ? await dbFirst(env, "SELECT id, username, avatar, recharge FROM acg_user WHERE id=?", r.substation_user_id) || null : null,
      card: r.card_id ? await dbFirst(env, "SELECT id, secret, draft, status FROM acg_card WHERE id=?", r.card_id) || null : null
    });
  }
  return apiOk("success", { list, page, limit: pageSize, count, records: count, order_amount: (sumRow && sumRow.total_amount) ?? 0, order_cost: (sumRow && sumRow.total_cost) ?? 0 });
}
async function orderSave(env, request, url, body = {}) {
  const id = Number(body.id) || 0;
  const secret = String(body.secret || "").trim();
  if (id < 1) return apiErr("\u8BA2\u5355 ID \u4E0D\u6B63\u786E\uFF0C\u8BF7\u5237\u65B0\u540E\u91CD\u8BD5");
  if (secret === "" || secret === "0") return apiErr('\u8BF7\u586B\u5199\u6709\u6548\u7684\u53D1\u8D27\u5185\u5BB9\uFF0C\u4E0D\u80FD\u4EC5\u4E3A\u7A7A\u767D\u6216"0"');
  const overwriteConfirmed = body.overwrite_confirmed === true || body.overwrite_confirmed === "true" || body.overwrite_confirmed === "1";
  const order = await dbFirst(env, "SELECT * FROM acg_order WHERE id=?", id);
  if (!order) return apiErr("\u8BA2\u5355\u4E0D\u5B58\u5728\uFF0C\u8BF7\u5237\u65B0\u540E\u91CD\u8BD5");
  if (Number(order.status) !== 1) return apiErr("\u4EC5\u5DF2\u652F\u4ED8\u8BA2\u5355\u53EF\u4EE5\u624B\u52A8\u53D1\u8D27");
  const commodity = await dbFirst(env, "SELECT id, delivery_way FROM acg_commodity WHERE id=?", order.commodity_id);
  if (!commodity) return apiErr("\u8BA2\u5355\u5BF9\u5E94\u5546\u54C1\u4E0D\u5B58\u5728\uFF0C\u65E0\u6CD5\u624B\u52A8\u53D1\u8D27");
  if (Number(commodity.delivery_way) !== 1) return apiErr("\u8BE5\u8BA2\u5355\u4E0D\u662F\u624B\u52A8\u53D1\u8D27\u5546\u54C1\uFF0C\u4E0D\u80FD\u4FEE\u6539\u53D1\u8D27\u5185\u5BB9");
  const hasExisting = Number(order.delivery_status) === 1 || String(order.secret || "").trim() !== "";
  if (hasExisting && !overwriteConfirmed) return apiErr("\u6B64\u8BA2\u5355\u5DF2\u6709\u53D1\u8D27\u8BB0\u5F55\uFF0C\u8BF7\u660E\u786E\u786E\u8BA4\u8986\u76D6\u540E\u91CD\u8BD5");
  await dbRun(env, "UPDATE acg_order SET secret=?, delivery_status=1 WHERE id=?", secret, id);
  try {
    await dbInsert(env, "acg_manage_log", { email: "admin", nickname: "", content: `[\u624B\u52A8\u53D1\u8D27](${id})\u4FEE\u6539\u4E86\u53D1\u8D27\u4FE1\u606F`, create_time: now(), create_ip: requestInfo(request).ip, ua: "", risk: 0 });
  } catch (e) {
  }
  return apiOk("\uFF08\uFF3E\u2200\uFF3E\uFF09\u53D1\u8D27\u6210\u529F");
}
async function orderClear(env, request, url, body = {}) {
  const cutoff = now() - 1800;
  const r = await dbRun(env, "DELETE FROM acg_order WHERE create_time<? AND status=0", cutoff);
  try {
    await dbInsert(env, "acg_manage_log", { email: "admin", nickname: "", content: "\u8FDB\u884C\u4E86\u4E00\u952E\u6E05\u7406\u65E0\u7528\u5546\u54C1\u8BA2\u5355\u64CD\u4F5C", create_time: now(), create_ip: requestInfo(request).ip, ua: "", risk: 0 });
  } catch (e) {
  }
  return apiOk("\uFF08\uFF3E\u2200\uFF3E\uFF09\u6E05\u7406\u5B8C\u6210");
}
var ORDER_EXPORT_TTL = 180;
var ORDER_EXPORT_MAX = 5e3;
async function orderExportTokenKey(manage) {
  return sha256hex(`order-export-preview-v1|${manage.password}`);
}
async function orderExportFingerprint(ids) {
  return sha256hex(ids.join(","));
}
async function orderIssueExportToken(manage, ids, options) {
  const iat = Math.floor(Date.now() / 1e3);
  const exp = iat + ORDER_EXPORT_TTL;
  const payload = { fingerprint: await orderExportFingerprint(ids), count: ids.length, export_num: options.export_num, export_status: options.export_status, manage_id: Number(manage.id) || 0, session: await sha256hex(manage.sid || ""), iat, exp };
  const body = b64urlEncode(JSON.stringify(payload));
  const sig = await sha256hex(`${body}|${await orderExportTokenKey(manage)}`);
  return body + "." + sig;
}
async function orderVerifyExportToken(manage, token, ids, options) {
  if (typeof token !== "string" || !token.includes(".")) throw new Error("\u8BF7\u5148\u9884\u89C8\u5E76\u786E\u8BA4\u8BA2\u5355\u5BFC\u51FA\u8303\u56F4");
  const [body, sig] = token.split(".");
  const expected = await sha256hex(`${body}|${await orderExportTokenKey(manage)}`);
  const nowSec = Math.floor(Date.now() / 1e3);
  let payload = null;
  try {
    let b64 = String(body).replace(/-/g, "+").replace(/_/g, "/");
    while (b64.length % 4) b64 += "=";
    payload = JSON.parse(decodeURIComponent(escape(atob(b64))));
  } catch (e) {
    throw new Error("\u8BA2\u5355\u5BFC\u51FA\u9884\u89C8\u51ED\u8BC1\u65E0\u6548\uFF0C\u8BF7\u91CD\u65B0\u9884\u89C8");
  }
  if (sig !== expected || !payload) throw new Error("\u8BA2\u5355\u5BFC\u51FA\u9884\u89C8\u51ED\u8BC1\u65E0\u6548\uFF0C\u8BF7\u91CD\u65B0\u9884\u89C8");
  if (Number(payload.exp) < nowSec || Number(payload.exp) > Number(payload.iat) + ORDER_EXPORT_TTL) throw new Error("\u8BA2\u5355\u5BFC\u51FA\u9884\u89C8\u5DF2\u8FC7\u671F\uFF0C\u8BF7\u91CD\u65B0\u9884\u89C8");
  if (Number(payload.manage_id) !== Number(manage.id) || payload.session !== await sha256hex(manage.sid || "")) throw new Error("\u8BA2\u5355\u5BFC\u51FA\u9884\u89C8\u51ED\u8BC1\u65E0\u6548\uFF0C\u8BF7\u91CD\u65B0\u9884\u89C8");
  if (Number(payload.count) !== ids.length || payload.fingerprint !== await orderExportFingerprint(ids)) throw new Error("\u8BA2\u5355\u5BFC\u51FA\u8303\u56F4\u6216\u6570\u636E\u5DF2\u53D8\u5316\uFF0C\u8BF7\u91CD\u65B0\u9884\u89C8");
  if (Number(payload.export_num) !== Number(options.export_num) || Number(payload.export_status) !== Number(options.export_status)) throw new Error("\u8BA2\u5355\u5BFC\u51FA\u8303\u56F4\u6216\u6570\u636E\u5DF2\u53D8\u5316\uFF0C\u8BF7\u91CD\u65B0\u9884\u89C8");
}
async function orderExportImpact(env, request, url, body = {}, manage) {
  const exportNum = body.export_num === "" || body.export_num === null ? 0 : Number(body.export_num);
  if (!Number.isInteger(exportNum) || exportNum < 0 || exportNum > ORDER_EXPORT_MAX) throw new Error(`\u5BFC\u51FA\u6570\u91CF\u5FC5\u987B\u662F 0 \u5230 ${ORDER_EXPORT_MAX} \u7684\u6574\u6570`);
  const exportStatus = Number(body.export_status) || 0;
  if (![0, 1].includes(exportStatus)) throw new Error("\u5BFC\u51FA\u540E\u64CD\u4F5C\u4E0D\u6B63\u786E");
  const { rows } = await orderExportSelection(env, body, exportNum);
  const ids = rows.map((r) => Number(r.id));
  if (!ids.length) throw new Error("\u5F53\u524D\u7B5B\u9009\u6CA1\u6709\u53EF\u5BFC\u51FA\u7684\u8BA2\u5355");
  const paidCount = rows.filter((r) => Number(r.status) === 1).length;
  const deliveredCount = rows.filter((r) => Number(r.delivery_status) === 1).length;
  return apiOk("success", {
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
    max_count: ORDER_EXPORT_MAX
  });
}
async function orderExportSelection(env, body, exportNum) {
  const wheres = [];
  const params = [];
  const addEqual = (key, col) => {
    if (body[key] !== void 0 && body[key] !== "") {
      wheres.push(`${col}=?`);
      params.push(body[key]);
    }
  };
  addEqual("equal-trade_no", "trade_no");
  addEqual("equal-status", "status");
  addEqual("equal-delivery_status", "delivery_status");
  addEqual("equal-commodity_id", "commodity_id");
  addEqual("equal-create_device", "create_device");
  addEqual("equal-pay_id", "pay_id");
  addEqual("equal-owner", "owner");
  if (body["search-secret"] !== void 0 && String(body["search-secret"]).trim() !== "") {
    wheres.push("secret LIKE ?");
    params.push(`%${String(body["search-secret"]).trim()}%`);
  }
  if (body["equal-contact"] !== void 0 && String(body["equal-contact"]).trim() !== "") {
    wheres.push("contact=?");
    params.push(String(body["equal-contact"]).trim());
  }
  const start = body["betweenStart-create_time"], end = body["betweenEnd-create_time"];
  if (start) {
    wheres.push("create_time>=?");
    params.push(Number(start));
  }
  if (end) {
    wheres.push("create_time<=?");
    params.push(Number(end));
  }
  const where = wheres.length ? " WHERE " + wheres.join(" AND ") : "";
  const total = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_order${where}`, ...params);
  const count = total ? Number(total.n) : 0;
  if (count === 0) throw new Error("\u5F53\u524D\u7B5B\u9009\u6CA1\u6709\u53EF\u5BFC\u51FA\u7684\u8BA2\u5355");
  const limit = exportNum > 0 ? Math.min(exportNum, count) : count;
  if (limit > ORDER_EXPORT_MAX) throw new Error(`\u5F53\u524D\u8303\u56F4\u8FC7\u5927\uFF0C\u8BF7\u589E\u52A0\u7B5B\u9009\u6216\u586B\u5199\u4E0D\u8D85\u8FC7 ${ORDER_EXPORT_MAX} \u7684\u5BFC\u51FA\u6570\u91CF`);
  const rows = await dbRows(env, `SELECT * FROM acg_order${where} ORDER BY id DESC LIMIT ?`, ...params, limit);
  return { rows, count, limit };
}
async function orderExport(env, request, url, body = {}, manage) {
  const exportNum = body.export_num === "" || body.export_num === null ? 0 : Number(body.export_num);
  const exportStatus = Number(body.export_status) || 0;
  const expectedCount = Number(body.expected_count);
  if (!Number.isInteger(expectedCount) || expectedCount < 1 || expectedCount > ORDER_EXPORT_MAX) throw new Error("\u8BF7\u5148\u9884\u89C8\u5E76\u786E\u8BA4\u672C\u6B21\u8BA2\u5355\u5BFC\u51FA\u6570\u91CF");
  const { rows, count, limit } = await orderExportSelection(env, body, exportNum);
  const ids = rows.map((r) => Number(r.id));
  if (count !== expectedCount) throw new Error("\u8BA2\u5355\u6570\u91CF\u5DF2\u53D8\u5316\uFF0C\u8BF7\u91CD\u65B0\u9884\u89C8\u5BFC\u51FA\u8303\u56F4");
  await orderVerifyExportToken(manage, body.preview_token, ids, { export_num: exportNum, export_status: exportStatus });
  if (exportStatus === 1) {
    const required = "\u786E\u8BA4\u6C38\u4E45\u5220\u9664" + count + "\u7B14\u8BA2\u5355";
    if (String(body.delete_confirmation || "").trim() !== required) throw new Error("\u8BF7\u5B8C\u6210\u9AD8\u5371\u786E\u8BA4\u540E\u518D\u5BFC\u51FA\u5E76\u5220\u9664\u8BA2\u5355");
  }
  const lines = [];
  const header = ["\u8BA2\u5355\u53F7", "\u91D1\u989D", "\u5546\u54C1\u540D\u79F0", "\u6570\u91CF", "\u652F\u4ED8\u65B9\u5F0F", "\u4E0B\u5355\u65F6\u95F4", "\u4E0B\u5355IP", "\u4E0B\u5355\u8BBE\u5907", "\u652F\u4ED8\u65F6\u95F4", "\u8BA2\u5355\u72B6\u6001", "\u8054\u7CFB\u65B9\u5F0F", "\u53D1\u8D27\u72B6\u6001", "\u4F18\u60E0\u5238", "\u5BA2\u6237", "\u63A8\u5E7F\u4EBA", "\u5206\u7AD9", "\u5206\u7AD9\u624B\u7EED\u8D39", "\u63A5\u53E3\u624B\u7EED\u8D39", "\u63A8\u5E7F\u5206\u6210", "\u8FD4\u5229"];
  const esc = (v) => {
    const s = String(v ?? "");
    return /[",\n\r]/.test(s) ? '"' + s.replace(/"/g, '""') + '"' : s;
  };
  lines.push(header.map(esc).join(","));
  for (const r of rows) {
    const deviceText = [0, 1, 2, 3].includes(Number(r.create_device)) ? ["PC", "\u5B89\u5353", "IOS", "iPad"][Number(r.create_device)] : "PC";
    const statusText = Number(r.status) === 0 ? "\u672A\u652F\u4ED8" : Number(r.status) === 1 ? "\u5DF2\u652F\u4ED8" : "\u672A\u77E5";
    const deliveryText = Number(r.delivery_status) === 0 ? "\u672A\u53D1\u8D27" : Number(r.delivery_status) === 1 ? "\u5DF2\u53D1\u8D27" : "\u672A\u77E5";
    lines.push([r.trade_no, r.amount, "", r.card_num || 0, "", r.create_time || "", r.create_ip || "", deviceText, r.pay_time || "", statusText, r.contact || "", deliveryText, "", "", "", "", r.cost || 0, r.pay_cost || 0, r.divide_amount || 0, r.rebate || 0].map(esc).join(","));
  }
  const csv = "\uFEFF" + lines.join("\r\n");
  if (exportStatus === 1) {
    await dbRun(env, `DELETE FROM acg_order WHERE id IN (${ids.map(() => "?").join(",")})`, ...ids);
  }
  try {
    await dbInsert(env, "acg_manage_log", { email: "admin", nickname: "", content: `[\u8BA2\u5355\u5BFC\u51FA]\u5BFC\u51FA\u5E76${exportStatus === 1 ? "\u6C38\u4E45\u5220\u9664" : ""}\u8BA2\u5355\uFF0C\u5171\u8BA1\uFF1A${count}`, create_time: now(), create_ip: requestInfo(request).ip, ua: "", risk: 0 });
  } catch (e) {
  }
  return new Response(csv, {
    status: 200,
    headers: {
      "Content-Type": "text/csv; charset=UTF-8",
      "Content-Disposition": `attachment; filename="order-export-${Date.now()}.csv"`,
      "Cache-Control": "no-cache"
    }
  });
}
async function userData(env, request, url, body = {}) {
  const page = Math.max(1, Number(body.page) || 1);
  const pageSize = Math.min(100, Math.max(1, Number(body.limit) || 10));
  const wheres = [];
  const params = [];
  for (const [key, col] of [["equal-id", "id"], ["equal-status", "status"], ["equal-pid", "pid"], ["equal-email", "email"], ["equal-phone", "phone"], ["equal-qq", "qq"], ["equal-login_ip", "login_ip"]]) {
    if (body[key] !== void 0 && body[key] !== "") {
      wheres.push(`${col}=?`);
      params.push(String(body[key]));
    }
  }
  if (body["search-username"] !== void 0 && String(body["search-username"]).trim() !== "") {
    wheres.push("username LIKE ?");
    params.push(`%${String(body["search-username"]).trim()}%`);
  }
  if (body["search-email"] !== void 0 && String(body["search-email"]).trim() !== "") {
    wheres.push("email LIKE ?");
    params.push(`%${String(body["search-email"]).trim()}%`);
  }
  if (body["search-phone"] !== void 0 && String(body["search-phone"]).trim() !== "") {
    wheres.push("phone LIKE ?");
    params.push(`%${String(body["search-phone"]).trim()}%`);
  }
  const where = wheres.length ? " WHERE " + wheres.join(" AND ") : "";
  const total = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_user${where}`, ...params);
  const count = total ? Number(total.n) : 0;
  const sumRow = await dbFirst(env, `SELECT COALESCE(SUM(balance),0) AS balance, COALESCE(SUM(recharge),0) AS recharge FROM acg_user${where}`, ...params);
  const rows = await dbRows(env, `SELECT * FROM acg_user${where} ORDER BY id DESC LIMIT ? OFFSET ?`, ...params, pageSize, (page - 1) * pageSize);
  const list = [];
  for (const r of rows) {
    list.push({ ...r, password: void 0, salt: void 0, app_key: void 0, parent: r.pid ? await dbFirst(env, "SELECT id, username, avatar FROM acg_user WHERE id=?", r.pid) || null : null, group: null, businessLevel: null, business: null });
  }
  return apiOk("success", { list, page, limit: pageSize, count, records: count, balance: (sumRow && sumRow.balance) ?? 0, recharge: (sumRow && sumRow.recharge) ?? 0 });
}
async function userSave(env, request, url, body = {}) {
  const id = Number(body.id) || 0;
  if (id <= 0) return apiErr("\u8BE5\u7528\u6237\u4E0D\u5B58\u5728");
  const user = await dbFirst(env, "SELECT * FROM acg_user WHERE id=?", id);
  if (!user) return apiErr("\u8BE5\u7528\u6237\u4E0D\u5B58\u5728");
  const data = {};
  for (const f of ["avatar", "username", "email", "phone", "qq", "status", "pid"]) {
    if (body[f] !== void 0) {
      if (f === "status") {
        const st = Number(body[f]);
        if (![0, 1].includes(st)) return apiErr("\u4F1A\u5458\u72B6\u6001\u4E0D\u6B63\u786E");
        data.status = st;
      } else if (f === "pid") {
        const p = Number(body[f]) || 0;
        if (p < 0 || p === id) return apiErr("\u4E0A\u7EA7\u4F1A\u5458 ID \u4E0D\u6B63\u786E");
        data.pid = p;
      } else data[f] = String(body[f]);
    }
  }
  if (body.password !== void 0 && String(body.password).trim() !== "") {
    const pw = String(body.password);
    if (pw.length < 6) return apiErr("\u5BC6\u7801\u5FC5\u987B6\u4F4D\u4EE5\u4E0A");
    data.password = generatePassword(pw, user.salt);
  }
  if (Object.keys(data).length) await dbUpdate(env, "acg_user", data, "id=?", id);
  try {
    await dbInsert(env, "acg_manage_log", { email: "admin", nickname: "", content: `\u4FEE\u6539\u4E86\u4F1A\u5458(${user.username})\u7684\u4FE1\u606F\u3002`, create_time: now(), create_ip: requestInfo(request).ip, ua: "", risk: 0 });
  } catch (e) {
  }
  return apiOk("\uFF08\uFF3E\u2200\uFF3E\uFF09\u4FDD\u5B58\u6210\u529F");
}
async function userRecharge(env, request, url, body = {}, manage, currency = 0) {
  const id = Number(body.id) || 0;
  const action = Number(body.action);
  if (id < 1) return apiErr("\u7528\u6237\u4E0D\u5B58\u5728");
  if (![1, 2].includes(action)) return apiErr("\u8BF7\u9009\u62E9\u589E\u52A0\u6216\u6263\u51CF");
  const amount = String(body.amount || "").trim();
  if (!/^\d+(?:\.\d{1,2})?$/.test(amount)) return apiErr("\u8BF7\u8F93\u5165\u5927\u4E8E 0 \u4E14\u6700\u591A\u4E24\u4F4D\u5C0F\u6570\u7684\u64CD\u4F5C\u6570\u91CF");
  const amt = Number(amount);
  if (!isFinite(amt) || amt <= 0 || amt > 9999999999e-2) return apiErr("\u64CD\u4F5C\u6570\u91CF\u5FC5\u987B\u5927\u4E8E 0 \u4E14\u4E0D\u8D85\u8FC7 99999999.99");
  const log = String(body.log || "").trim();
  if (log.length < 2 || log.length > 64) return apiErr("\u64CD\u4F5C\u539F\u56E0\u987B\u4E3A 2\u201364 \u4E2A\u5B57");
  const total = Number(body.total) === 1;
  const user = await dbFirst(env, "SELECT * FROM acg_user WHERE id=?", id);
  if (!user) return apiErr("\u7528\u6237\u4E0D\u5B58\u5728");
  const delta = action === 1 ? amt : -amt;
  const col = currency === 1 ? "coin" : "balance";
  const cur = Number(user[col] || 0);
  const next = Math.round((cur + delta) * 100) / 100;
  if (next < 0) return apiErr("\u7528\u6237\u4F59\u989D\u4E0D\u8DB3\uFF0C\u65E0\u6CD5\u64CD\u4F5C");
  await dbRun(env, `UPDATE acg_user SET ${col}=? WHERE id=?`, next, id);
  if (total) {
    if (action === 1) {
      if (currency === 1) await dbRun(env, "UPDATE acg_user SET total_coin=total_coin+? WHERE id=?", amt, id);
      else await dbRun(env, "UPDATE acg_user SET recharge=recharge+? WHERE id=?", amt, id);
    }
  }
  const billLog = currency === 1 ? `\u7BA1\u7406\u5458\u64CD\u4F5C\u786C\u5E01:${log}` : `\u7BA1\u7406\u5458\u64CD\u4F5C\u4F59\u989D:${log}`;
  await dbInsert(env, "acg_bill", { owner: id, amount: amt, balance: next, type: action, currency, log: billLog, create_time: now() });
  try {
    await dbInsert(env, "acg_manage_log", { email: "admin", nickname: "", content: `\u4E3A\u4F1A\u5458(${user.username})\u8FDB\u884C\u4E86${currency === 1 ? "\u786C\u5E01" : "\u4F59\u989D"}\u53D8\u52A8\u64CD\u4F5C`, create_time: now(), create_ip: requestInfo(request).ip, ua: "", risk: 0 });
  } catch (e) {
  }
  return apiOk("\u64CD\u4F5C\u6210\u529F");
}
async function userStatistics(env, request, url, body = {}) {
  const id = Number(url.searchParams.get("id")) || Number(body.id) || 0;
  if (id < 1) return apiErr("\u7528\u6237\u4E0D\u5B58\u5728");
  const startOfDay = (d) => d.setHours(0, 0, 0, 0) / 1e3;
  const endOfDay = (d) => d.setHours(23, 59, 59, 999) / 1e3;
  const nowD = /* @__PURE__ */ new Date();
  const todayStart = startOfDay(new Date(nowD)), todayEnd = endOfDay(new Date(nowD));
  const yStart = startOfDay(new Date(nowD.getTime() - 864e5)), yEnd = endOfDay(new Date(nowD.getTime() - 864e5));
  const wStart = startOfDay(new Date(nowD.getTime() - (nowD.getDay() || 7 - 7) * 864e5));
  const wEnd = endOfDay(new Date(nowD));
  const mStart = startOfDay(new Date(nowD.getFullYear(), nowD.getMonth(), 1));
  const mEnd = endOfDay(new Date(nowD.getFullYear(), nowD.getMonth() + 1, 0));
  const rangeSum = async (a, b) => {
    const r = await dbFirst(env, "SELECT COALESCE(SUM(amount),0) AS s FROM acg_order WHERE user_id=? AND status=1 AND create_time>=? AND create_time<?", id, a, b);
    return r && r.s || 0;
  };
  const data = {
    today_order_amount: (await rangeSum(todayStart, todayEnd)).toFixed(2),
    yesterday_order_amount: (await rangeSum(yStart, yEnd)).toFixed(2),
    week_order_amount: (await rangeSum(wStart, wEnd)).toFixed(2),
    month_order_amount: (await rangeSum(mStart, mEnd)).toFixed(2)
  };
  data.total_order_amount = (await rangeSum(0, 9999999999)).toFixed(2);
  return apiOk("success", data);
}
async function userDel(env, request, url, body = {}) {
  let list;
  try {
    list = intList(body.list, "\u4F1A\u5458ID");
  } catch (e) {
    return apiErr(e.message);
  }
  if (!list.length) return apiErr("\u8BF7\u9009\u62E9\u8981\u5220\u9664\u7684\u4F1A\u5458");
  if (list.length > 1e3) return apiErr("\u5355\u6B21\u53EA\u80FD\u5220\u9664 1\u20131000 \u540D\u6709\u6548\u4F1A\u5458");
  const where = `id IN (${list.map(() => "?").join(",")})`;
  await dbRun(env, `DELETE FROM acg_user WHERE ${where}`, ...list);
  await dbRun(env, "DELETE FROM acg_business WHERE user_id IN (" + list.map(() => "?").join(",") + ")", ...list);
  try {
    await dbInsert(env, "acg_manage_log", { email: "admin", nickname: "", content: `\u5220\u9664\u4E86\u4F1A\u5458\uFF0C\u5171\u8BA1\u5220\u9664\uFF1A${list.length}`, create_time: now(), create_ip: requestInfo(request).ip, ua: "", risk: 0 });
  } catch (e) {
  }
  return apiOk("\uFF08\uFF3E\u2200\uFF3E\uFF09\u79FB\u9664\u6210\u529F", { count: list.length });
}
async function rechargeData(env, request, url, body = {}) {
  const page = Math.max(1, Number(body.page) || 1);
  const pageSize = Math.min(100, Math.max(1, Number(body.limit) || 10));
  const wheres = [];
  const params = [];
  if (body["equal-status"] !== void 0 && body["equal-status"] !== "") {
    wheres.push("status=?");
    params.push(Number(body["equal-status"]));
  }
  if (body["equal-user_id"] !== void 0 && body["equal-user_id"] !== "") {
    wheres.push("user_id=?");
    params.push(Number(body["equal-user_id"]));
  }
  if (body["equal-trade_no"] !== void 0 && body["equal-trade_no"] !== "") {
    wheres.push("trade_no=?");
    params.push(String(body["equal-trade_no"]));
  }
  if (body["equal-create_ip"] !== void 0 && body["equal-create_ip"] !== "") {
    wheres.push("create_ip=?");
    params.push(String(body["equal-create_ip"]));
  }
  if (body["search-trade_no"] !== void 0 && String(body["search-trade_no"]).trim() !== "") {
    wheres.push("trade_no LIKE ?");
    params.push(`%${String(body["search-trade_no"]).trim()}%`);
  }
  if (body["equal-pay_id"] !== void 0 && body["equal-pay_id"] !== "") {
    wheres.push("pay_id=?");
    params.push(Number(body["equal-pay_id"]));
  }
  const where = wheres.length ? " WHERE " + wheres.join(" AND ") : "";
  const total = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_user_recharge${where}`, ...params);
  const count = total ? Number(total.n) : 0;
  const sumRow = await dbFirst(env, `SELECT COALESCE(SUM(amount),0) AS order_amount FROM acg_user_recharge${where}`, ...params);
  const rows = await dbRows(env, `SELECT * FROM acg_user_recharge${where} ORDER BY id DESC LIMIT ? OFFSET ?`, ...params, pageSize, (page - 1) * pageSize);
  const list = [];
  for (const r of rows) {
    list.push({
      ...r,
      user: r.user_id ? await dbFirst(env, "SELECT id, username, avatar FROM acg_user WHERE id=?", r.user_id) || null : null,
      pay: r.pay_id ? await dbFirst(env, "SELECT id, name, icon FROM acg_pay WHERE id=?", r.pay_id) || null : null
    });
  }
  return apiOk("success", { list, page, limit: pageSize, count, records: count, order_amount: (sumRow && sumRow.order_amount) ?? 0 });
}
async function rechargeSuccess(env, request, url, body = {}) {
  const id = Number(body.id) || 0;
  if (id < 1) return apiErr("\u8BA2\u5355\u7F16\u53F7\u4E0D\u6B63\u786E");
  const order = await dbFirst(env, "SELECT * FROM acg_user_recharge WHERE id=?", id);
  if (!order) return apiErr("\u8BA2\u5355\u4E0D\u5B58\u5728");
  if (Number(order.status) !== 0) return apiErr("\u8BE5\u8BA2\u5355\u5DF2\u652F\u4ED8\uFF0C\u65E0\u6CD5\u518D\u6B21\u8865\u5355");
  const user = await dbFirst(env, "SELECT id, balance FROM acg_user WHERE id=?", order.user_id);
  if (!user) return apiErr("\u8BA2\u5355\u4F1A\u5458\u4E0D\u5B58\u5728\uFF0C\u65E0\u6CD5\u8865\u5355");
  const bal = Math.round((Number(user.balance) + Number(order.amount)) * 100) / 100;
  await dbRun(env, "UPDATE acg_user SET balance=?, recharge=recharge+? WHERE id=?", bal, order.amount, order.user_id);
  await dbRun(env, "UPDATE acg_user_recharge SET status=1, pay_time=? WHERE id=?", now(), id);
  await dbInsert(env, "acg_bill", { owner: order.user_id, amount: Number(order.amount), balance: bal, type: 1, currency: 0, log: `\u5145\u503C\u6210\u529F[${order.trade_no}]`, create_time: now() });
  try {
    await dbInsert(env, "acg_manage_log", { email: "admin", nickname: "", content: `\u5145\u503C\u8BA2\u5355\u624B\u52A8\u8865\u5355\uFF0C\u8BA2\u5355\u53F7\uFF1A${order.trade_no}`, create_time: now(), create_ip: requestInfo(request).ip, ua: "", risk: 0 });
  } catch (e) {
  }
  return apiOk("\u5DF2\u624B\u52A8\u786E\u8BA4");
}
async function rechargeClear(env, request, url, body = {}) {
  const cutoff = now() - 1800;
  await dbRun(env, "DELETE FROM acg_user_recharge WHERE create_time<? AND status=0", cutoff);
  try {
    await dbInsert(env, "acg_manage_log", { email: "admin", nickname: "", content: "\u8FDB\u884C\u4E86\u4E00\u952E\u6E05\u7406\u65E0\u7528\u5145\u503C\u8BA2\u5355\u64CD\u4F5C", create_time: now(), create_ip: requestInfo(request).ip, ua: "", risk: 0 });
  } catch (e) {
  }
  return apiOk("\uFF08\uFF3E\u2200\uFF3E\uFF09\u6E05\u7406\u5B8C\u6210");
}
var COUPON_MAX_EXPORT = 5e3;
function couponIds(value) {
  const arr = Array.isArray(value) ? value : String(value ?? "").split(",");
  const ids = [];
  for (const c of arr) {
    const id = typeof c === "number" ? c : Number(String(c).trim());
    if (!Number.isInteger(id) || id <= 0) throw new Error("\u4F18\u60E0\u5377 ID \u5FC5\u987B\u662F\u6B63\u6574\u6570");
    ids.push(id);
  }
  return [...new Set(ids)];
}
function couponCodeRandom() {
  const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  let s = "";
  for (let i = 0; i < 16; i++) s += chars[Math.floor(Math.random() * chars.length)];
  return s;
}
async function couponDeleteImpact2(env, requestedIds, lock = false) {
  if (!requestedIds.length) throw new Error("\u8BF7\u81F3\u5C11\u9009\u62E9\u4E00\u5F20\u4F18\u60E0\u5377");
  const ph = requestedIds.map(() => "?").join(",");
  const rows = await dbRows(env, `SELECT id, status, trade_no FROM acg_coupon WHERE id IN (${ph})`, ...requestedIds);
  if (rows.length !== requestedIds.length) throw new Error("\u90E8\u5206\u4F18\u60E0\u5377\u4E0D\u5B58\u5728\uFF0C\u8BF7\u5237\u65B0\u540E\u91CD\u8BD5");
  const ids = rows.map((r) => Number(r.id));
  let normalCount = 0, usedCount = 0, lockedCount = 0, tradeNoCount = 0;
  for (const r of rows) {
    const st = Number(r.status);
    if (st === 0) normalCount++;
    else if (st === 1) usedCount++;
    else if (st === 2) lockedCount++;
    if (String(r.trade_no || "").trim() !== "") tradeNoCount++;
  }
  let orderReferenceCount = 0;
  if (ids.length) {
    const ref = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_order WHERE coupon_id IN (${ids.map(() => "?").join(",")})`, ...ids);
    orderReferenceCount = ref ? Number(ref.n) : 0;
  }
  return { coupon_ids: ids, coupon_count: ids.length, normal_count: normalCount, used_count: usedCount, locked_count: lockedCount, trade_no_count: tradeNoCount, order_reference_count: orderReferenceCount, can_delete: usedCount === 0 && tradeNoCount === 0 && orderReferenceCount === 0 };
}
async function couponData(env, request, url, body = {}) {
  const page = Math.max(1, Number(body.page) || 1);
  const pageSize = Math.min(100, Math.max(1, Number(body.limit) || 10));
  const wheres = [];
  const params = [];
  for (const [key, col] of [["equal-code", "code"], ["equal-note", "note"], ["equal-money", "money"], ["equal-owner", "owner"], ["equal-category_id", "category_id"], ["equal-commodity_id", "commodity_id"], ["equal-status", "status"], ["equal-race", "race"]]) {
    if (body[key] !== void 0 && body[key] !== "") {
      wheres.push(`${col}=?`);
      params.push(String(body[key]));
    }
  }
  const where = wheres.length ? " WHERE " + wheres.join(" AND ") : "";
  const total = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_coupon${where}`, ...params);
  const count = total ? Number(total.n) : 0;
  const rows = await dbRows(env, `SELECT * FROM acg_coupon${where} ORDER BY id DESC LIMIT ? OFFSET ?`, ...params, pageSize, (page - 1) * pageSize);
  const list = [];
  for (const r of rows) {
    list.push({
      ...r,
      owner: r.owner ? await dbFirst(env, "SELECT id, username, avatar FROM acg_user WHERE id=?", r.owner) || null : null,
      commodity: r.commodity_id ? await dbFirst(env, "SELECT id, name, cover FROM acg_commodity WHERE id=?", r.commodity_id) || null : null,
      category: r.category_id ? await dbFirst(env, "SELECT id, name FROM acg_category WHERE id=?", r.category_id) || null : null
    });
  }
  return apiOk("success", { list, page, limit: pageSize, count, records: count, total: count });
}
async function couponSave(env, request, url, body = {}, manage) {
  const prefix = String(body.prefix || "").trim().toUpperCase();
  const note = String(body.note || "").trim();
  const commodityId = Number(body.commodity_id) || 0;
  const categoryId = Number(body.category_id) || 0;
  const expireTime = String(body.expire_time || "").trim();
  const rawMoney = body.money;
  const num = Number(body.num) || 0;
  const life = Number(body.life) || 0;
  const mode = Number(body.mode) ?? -1;
  const money2 = Number(rawMoney);
  if (!isFinite(money2) || money2 <= 0) throw new Error("\u0CA0_\u0CA0\u8BF7\u8F93\u5165\u4F18\u60E0\u5377\u4EF7\u683C");
  if (![0, 1].includes(mode)) throw new Error("\u8BF7\u9009\u62E9\u6B63\u786E\u7684\u62B5\u6263\u6A21\u5F0F");
  if (mode === 1 && money2 > 1) throw new Error("\u767E\u5206\u6BD4\u62B5\u6263\u5FC5\u987B\u5927\u4E8E 0 \u4E14\u5C0F\u4E8E\u6216\u7B49\u4E8E 1");
  if (mode === 0 && money2 > 9999999999e-2) throw new Error("\u91D1\u989D\u62B5\u6263\u8D85\u51FA\u5141\u8BB8\u8303\u56F4");
  if (prefix !== "" && !/^[A-Z0-9_-]{1,16}$/.test(prefix)) throw new Error("\u4F18\u60E0\u5377\u524D\u7F00\u4EC5\u652F\u6301 1 \u5230 16 \u4F4D\u5B57\u6BCD\u3001\u6570\u5B57\u3001\u4E0B\u5212\u7EBF\u6216\u77ED\u6A2A\u7EBF");
  if (note.length > 32) throw new Error("\u5907\u6CE8\u4FE1\u606F\u6700\u591A 32 \u4E2A\u5B57\u7B26");
  if (commodityId > 0 && categoryId > 0) throw new Error("\u5546\u54C1\u548C\u5546\u54C1\u5206\u7C7B\u53EA\u80FD\u9009\u62E9\u4E00\u4E2A\u62B5\u6263\u8303\u56F4");
  if (expireTime !== "") {
    const ts = Date.parse(expireTime.replace("T", " ").replace(/-/g, "/"));
    if (isNaN(ts) || Math.floor(ts / 1e3) <= now()) throw new Error("\u0CA0_\u0CA0\u4F18\u60E0\u5377\u7684\u8FC7\u671F\u65F6\u95F4\u5FC5\u987B\u665A\u4E8E\u5F53\u524D\u65F6\u95F4");
  }
  if (num < 1 || num > 1e3) throw new Error("\u6BCF\u6B21\u53EA\u80FD\u751F\u6210 1 \u5230 1000 \u5F20\u4F18\u60E0\u5377");
  if (life < 1 || life > 1e6) throw new Error("\u53EF\u7528\u6B21\u6570\u5FC5\u987B\u662F 1 \u5230 1000000 \u4E4B\u95F4\u7684\u6574\u6570");
  if (categoryId > 0) {
    const cat = await dbFirst(env, "SELECT id FROM acg_category WHERE id=? AND owner=0", categoryId);
    if (!cat) throw new Error("\u6240\u9009\u5546\u54C1\u5206\u7C7B\u4E0D\u5B58\u5728");
  }
  if (commodityId > 0) {
    const com = await dbFirst(env, "SELECT id FROM acg_commodity WHERE id=? AND owner=0", commodityId);
    if (!com) throw new Error("\u6240\u9009\u5546\u54C1\u4E0D\u5B58\u5728");
  }
  const t = now();
  const expireTs = expireTime !== "" ? Math.floor(Date.parse(expireTime.replace("T", " ").replace(/-/g, "/")) / 1e3) : 0;
  let success = 0, error = 0;
  const codes = [];
  for (let i = 0; i < num; i++) {
    const code = prefix + couponCodeRandom();
    try {
      await dbInsert(env, "acg_coupon", {
        code,
        commodity_id: commodityId,
        category_id: categoryId,
        owner: 0,
        create_time: t,
        ...expireTs ? { expire_time: expireTs } : {},
        money: money2,
        status: 0,
        note,
        life,
        use_life: 0,
        mode,
        sku: body.sku && typeof body.sku === "object" ? JSON.stringify(body.sku) : "",
        ...String(body.race || "").trim() !== "" ? { race: String(body.race).trim() } : {}
      });
      success++;
      codes.push(code);
    } catch (e2) {
      error++;
    }
  }
  try {
    await dbInsert(env, "acg_manage_log", { email: "admin", nickname: "", content: `[\u751F\u6210\u4F18\u60E0\u5377]\u6210\u529F:${success}\u5F20\uFF0C\u5931\u8D25\uFF1A${error}\u5F20`, create_time: now(), create_ip: requestInfo(request).ip, ua: "", risk: 0 });
  } catch (e) {
  }
  return apiOk(`\u751F\u6210\u5B8C\u6BD5\uFF0C\u6210\u529F:${success}\u5F20\uFF0C\u5931\u8D25\uFF1A${error}\u5F20`, { success, error, code: codes.join("\n") + (codes.length ? "\n" : "") });
}
async function couponEdit(env, request, url, body = {}) {
  const id = Number(body.id) || 0;
  const status = Number(body.status) ?? -1;
  if (id <= 0 || ![0, 2].includes(status)) throw new Error("\u8BF7\u6C42\u53C2\u6570\u4E0D\u6B63\u786E");
  const coupon = await dbFirst(env, "SELECT * FROM acg_coupon WHERE id=?", id);
  if (!coupon) throw new Error("\u4F18\u60E0\u5377\u4E0D\u5B58\u5728");
  if (Number(coupon.status) === 1) throw new Error("\u5DF2\u4F7F\u7528\u7684\u4F18\u60E0\u5377\u4E0D\u80FD\u4FEE\u6539\u72B6\u6001");
  await dbRun(env, "UPDATE acg_coupon SET status=? WHERE id=?", status, id);
  return apiOk("\uFF08\uFF3E\u2200\uFF3E\uFF09\u4FDD\u5B58\u6210\u529F");
}
async function couponLock(env, request, url, body = {}, doLock = true) {
  const list = couponIds(body.list);
  if (!list.length) throw new Error("\u8BF7\u9009\u62E9\u8981\u9501\u5B9A\u7684\u4F18\u60E0\u5377");
  const ph = list.map(() => "?").join(",");
  const st = doLock ? 2 : 0;
  const src = doLock ? 0 : 2;
  const r = await dbRun(env, `UPDATE acg_coupon SET status=? WHERE id IN (${ph}) AND status=?`, st, ...list, src);
  const changes = r && r.meta && r.meta.changes !== void 0 ? Number(r.meta.changes) : r && r.changes !== void 0 ? Number(r.changes) : list.length;
  const count = changes;
  try {
    await dbInsert(env, "acg_manage_log", { email: "admin", nickname: "", content: `[${doLock ? "\u9501\u5B9A" : "\u89E3\u9501"}\u4F18\u60E0\u5377]\u6279\u91CF${doLock ? "\u9501\u5B9A" : "\u89E3\u9501"}\u4E86\u4F18\u60E0\u5377\uFF0C\u5171\u8BA1\uFF1A${count}`, create_time: now(), create_ip: requestInfo(request).ip, ua: "", risk: 0 });
  } catch (e) {
  }
  return apiOk(count > 0 ? doLock ? "\u9501\u5B9A\u6210\u529F" : "\u89E3\u9501\u6210\u529F" : doLock ? "\u6CA1\u6709\u53EF\u9501\u5B9A\u7684\u4F18\u60E0\u5377" : "\u6CA1\u6709\u53EF\u89E3\u9501\u7684\u4F18\u60E0\u5377", { count, requested_count: list.length });
}
async function couponDeleteImpact(env, request, url, body = {}) {
  const impact = await couponDeleteImpact2(env, couponIds(body.list));
  delete impact.coupon_ids;
  return apiOk("success", impact);
}
async function couponDel(env, request, url, body = {}, manage) {
  const requestedIds = couponIds(body.list);
  const impact = await couponDeleteImpact2(env, requestedIds, true);
  if (!impact.can_delete) {
    throw new Error(`\u6240\u9009\u4F18\u60E0\u5377\u4E2D\u5305\u542B ${impact.used_count} \u5F20\u5DF2\u4F7F\u7528\u4F18\u60E0\u5377\u3001${impact.trade_no_count} \u5F20\u5E26\u6700\u540E\u4F7F\u7528\u8BA2\u5355\u53F7\u7684\u4F18\u60E0\u5377\uFF0C\u53E6\u6709 ${impact.order_reference_count} \u7B14\u8BA2\u5355\u5F15\u7528\uFF1B\u4E3A\u4FDD\u62A4\u5386\u53F2\u8BB0\u5F55\uFF0C\u5DF2\u963B\u6B62\u5220\u9664\u3002`);
  }
  const ids = impact.coupon_ids;
  const ph = ids.map(() => "?").join(",");
  await dbRun(env, `DELETE FROM acg_coupon WHERE id IN (${ph}) AND status!=1 AND (trade_no IS NULL OR trade_no='') AND id NOT IN (SELECT coupon_id FROM acg_order WHERE coupon_id IS NOT NULL)`, ...ids);
  try {
    await dbInsert(env, "acg_manage_log", { email: "admin", nickname: "", content: `[\u6279\u91CF\u5220\u9664]\u5220\u9664\u672A\u4F7F\u7528\u4F18\u60E0\u5377\uFF0C\u5171\u8BA1\uFF1A${impact.coupon_count}`, create_time: now(), create_ip: requestInfo(request).ip, ua: "", risk: 0 });
  } catch (e) {
  }
  return apiOk("\uFF08\uFF3E\u2200\uFF3E\uFF09\u79FB\u9664\u6210\u529F", { count: impact.coupon_count });
}
function couponExportWhere(body) {
  const wheres = [];
  const params = [];
  const addStr = (key, col, maxLen = 32) => {
    const v = body[key];
    if (v === void 0 || v === null || v === "") return;
    if (typeof v !== "string" && typeof v !== "number") throw new Error("\u4F18\u60E0\u5377\u5BFC\u51FA\u7B5B\u9009\u6761\u4EF6\u4E0D\u6B63\u786E");
    const s = String(v).trim();
    if (!s) return;
    if (s.length > maxLen) throw new Error("\u4F18\u60E0\u5377\u5BFC\u51FA\u7B5B\u9009\u6761\u4EF6\u8FC7\u957F");
    wheres.push(`${col}=?`);
    params.push(s);
  };
  addStr("coupon_code_secret", "code", 32);
  addStr("equal-note", "note", 32);
  addStr("equal-race", "race", 32);
  const rawMoney = body["equal-money"];
  if (rawMoney !== void 0 && rawMoney !== null && rawMoney !== "") {
    const m = Number(rawMoney);
    if (!isFinite(m) || m <= 0 || m > 9999999999e-2) throw new Error("\u4F18\u60E0\u5377\u9762\u503C\u7B5B\u9009\u4E0D\u6B63\u786E");
    wheres.push("money=?");
    params.push(m);
  }
  for (const [key, col] of [["equal-owner", "owner"], ["equal-category_id", "category_id"], ["equal-commodity_id", "commodity_id"], ["equal-status", "status"]]) {
    const v = body[key];
    if (v === void 0 || v === null || v === "") continue;
    const n = Number(v);
    if (!Number.isInteger(n) || n < 0 || col === "status" && ![0, 1, 2].includes(n)) throw new Error("\u4F18\u60E0\u5377\u5BFC\u51FA\u7B5B\u9009\u6761\u4EF6\u4E0D\u6B63\u786E");
    wheres.push(`${col}=?`);
    params.push(n);
  }
  for (const key of Object.keys(body || {})) {
    if (!key.startsWith("equal-sku-")) continue;
    const v = body[key];
    if (v === void 0 || v === null || v === "") continue;
    const skKey = key.slice(10);
    const s = String(v).trim();
    wheres.push(`sku LIKE ?`);
    params.push(`%"${skKey}":"${s}"%`);
  }
  return { where: wheres.length ? " WHERE " + wheres.join(" AND ") : "", params, hasFilter: wheres.length > 0 };
}
async function couponExportImpact(env, request, url, body = {}) {
  const { where, params, hasFilter } = couponExportWhere(body);
  const total = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_coupon${where}`, ...params);
  const count = total ? Number(total.n) : 0;
  if (count === 0) throw new Error("\u5F53\u524D\u7B5B\u9009\u6CA1\u6709\u53EF\u5BFC\u51FA\u7684\u4F18\u60E0\u5377");
  if (count > COUPON_MAX_EXPORT) throw new Error(`\u5F53\u524D\u8303\u56F4\u8FC7\u5927\uFF0C\u8BF7\u589E\u52A0\u7B5B\u9009\uFF1B\u5355\u6B21\u6700\u591A\u5BFC\u51FA ${COUPON_MAX_EXPORT} \u5F20\u4F18\u60E0\u5377`);
  const statusRows = await dbRows(env, `SELECT status FROM acg_coupon${where}`, ...params);
  const statusCounts = { 0: 0, 1: 0, 2: 0 };
  for (const r of statusRows) {
    const st = Number(r.status);
    if (st in statusCounts) statusCounts[st]++;
  }
  return apiOk("success", { count, total: count, has_filter: hasFilter, normal_count: statusCounts[0], used_count: statusCounts[1], locked_count: statusCounts[2], max_count: COUPON_MAX_EXPORT });
}
async function couponExport(env, request, url, body = {}, manage) {
  const expectedCount = Number(body.expected_count);
  if (!Number.isInteger(expectedCount) || expectedCount < 1 || expectedCount > COUPON_MAX_EXPORT) throw new Error("\u8BF7\u5148\u9884\u89C8\u5E76\u786E\u8BA4\u672C\u6B21\u5BFC\u51FA\u6570\u91CF");
  const { where, params } = couponExportWhere(body);
  const total = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_coupon${where}`, ...params);
  const count = total ? Number(total.n) : 0;
  if (count === 0) throw new Error("\u5F53\u524D\u7B5B\u9009\u6CA1\u6709\u53EF\u5BFC\u51FA\u7684\u4F18\u60E0\u5377");
  if (count > COUPON_MAX_EXPORT) throw new Error(`\u5F53\u524D\u8303\u56F4\u8FC7\u5927\uFF0C\u8BF7\u589E\u52A0\u7B5B\u9009\uFF1B\u5355\u6B21\u6700\u591A\u5BFC\u51FA ${COUPON_MAX_EXPORT} \u5F20\u4F18\u60E0\u5377`);
  if (count !== expectedCount) throw new Error("\u4F18\u60E0\u5377\u6570\u91CF\u5DF2\u53D8\u5316\uFF0C\u8BF7\u91CD\u65B0\u9884\u89C8\u5BFC\u51FA\u8303\u56F4");
  const rows = await dbRows(env, `SELECT code FROM acg_coupon${where} ORDER BY id ASC`, ...params);
  if (rows.length !== expectedCount) throw new Error("\u4F18\u60E0\u5377\u6570\u636E\u5DF2\u53D8\u5316\uFF0C\u8BF7\u91CD\u65B0\u9884\u89C8\u5BFC\u51FA\u8303\u56F4");
  const content = rows.map((r) => String(r.code)).join("\n") + "\n";
  try {
    await dbInsert(env, "acg_manage_log", { email: "admin", nickname: "", content: `[\u4F18\u60E0\u5377\u5BFC\u51FA]\u5BFC\u51FA\u4F18\u60E0\u5377\uFF0C\u5171\u8BA1\uFF1A${count}`, create_time: now(), create_ip: requestInfo(request).ip, ua: "", risk: 0 });
  } catch (e) {
  }
  const d = /* @__PURE__ */ new Date();
  const stamp = `${d.getFullYear()}${String(d.getMonth() + 1).padStart(2, "0")}${String(d.getDate()).padStart(2, "0")}-${String(d.getHours()).padStart(2, "0")}${String(d.getMinutes()).padStart(2, "0")}${String(d.getSeconds()).padStart(2, "0")}`;
  return new Response(content, { status: 200, headers: { "Content-Type": "text/plain; charset=UTF-8", "Content-Disposition": `attachment; filename=coupons-${count}-${stamp}.txt` } });
}
async function adminEndpoint(env, request, url, ctl, act, body) {
  if (ctl === "authentication" && act === "login") return adminLogin(env, request, url, body);
  const manage = await authenticateManage(env, request);
  if (!manage) return apiErr("\u767B\u5F55\u4F1A\u8BDD\u8FC7\u671F\uFF0C\u8BF7\u91CD\u65B0\u767B\u5F55..");
  if (ctl === "dashboard") {
    if (act === "overview") return dashboardOverview(env, request, url);
    if (act === "trend") return dashboardTrend(env, request, url, Number(url.searchParams.get("days")) || 0);
    if (act === "data") return dashboardData(env, request, url, body && body.type || url.searchParams.get("type") || 0);
  }
  if (ctl === "app" && act === "ad") {
    return apiOk("ok", { title: "", content: "", url: "" });
  }
  if (ctl === "category") {
    try {
      if (act === "data") return await categoryData(env, request, url, body);
      if (act === "save") return await categorySave(env, request, url, body);
      if (act === "status") return await categoryStatus(env, request, url, body);
      if (act === "reorder") return await categoryReorder(env, request, url, body);
      if (act === "deleteImpact") return await categoryDeleteImpact(env, request, url, body, manage);
      if (act === "del") return await categoryDel(env, request, url, body, manage);
    } catch (e) {
      return apiErr(e && e.message ? String(e.message) : "\u64CD\u4F5C\u5931\u8D25");
    }
  }
  if (ctl === "commodity") {
    try {
      if (act === "data") return await commodityData(env, request, url, body);
      if (act === "save") return await commoditySave(env, request, url, body, manage);
      if (act === "status") return await commodityStatus(env, request, url, body);
      if (act === "del") return await commodityDel(env, request, url, body);
    } catch (e) {
      return apiErr(e && e.message ? String(e.message) : "\u64CD\u4F5C\u5931\u8D25");
    }
  }
  if (ctl === "card") {
    try {
      if (act === "data") return await cardData(env, request, url, body);
      if (act === "save") return await cardSave(env, request, url, body, manage);
      if (act === "edit") return await cardEdit(env, request, url, body);
      if (act === "lock") return await cardLock(env, request, url, body, true);
      if (act === "unlock") return await cardLock(env, request, url, body, false);
      if (act === "del") return await cardDel(env, request, url, body);
    } catch (e) {
      return apiErr(e && e.message ? String(e.message) : "\u64CD\u4F5C\u5931\u8D25");
    }
  }
  if (ctl === "order") {
    try {
      if (act === "data") return await orderData(env, request, url, body);
      if (act === "save") return await orderSave(env, request, url, body);
      if (act === "clear") return await orderClear(env, request, url, body);
      if (act === "exportImpact") return await orderExportImpact(env, request, url, body, manage);
      if (act === "export") return await orderExport(env, request, url, body, manage);
    } catch (e) {
      return apiErr(e && e.message ? String(e.message) : "\u64CD\u4F5C\u5931\u8D25");
    }
  }
  if (ctl === "user") {
    try {
      if (act === "data") return await userData(env, request, url, body);
      if (act === "save") return await userSave(env, request, url, body);
      if (act === "recharge") return await userRecharge(env, request, url, body, manage, 0);
      if (act === "coin") return await userRecharge(env, request, url, body, manage, 1);
      if (act === "statistics") return await userStatistics(env, request, url, body);
      if (act === "del") return await userDel(env, request, url, body);
    } catch (e) {
      return apiErr(e && e.message ? String(e.message) : "\u64CD\u4F5C\u5931\u8D25");
    }
  }
  if (ctl === "recharge") {
    try {
      if (act === "data") return await rechargeData(env, request, url, body);
      if (act === "success") return await rechargeSuccess(env, request, url, body);
      if (act === "clear") return await rechargeClear(env, request, url, body);
    } catch (e) {
      return apiErr(e && e.message ? String(e.message) : "\u64CD\u4F5C\u5931\u8D25");
    }
  }
  if (ctl === "coupon") {
    try {
      if (act === "data") return await couponData(env, request, url, body);
      if (act === "save") return await couponSave(env, request, url, body, manage);
      if (act === "edit") return await couponEdit(env, request, url, body);
      if (act === "lock") return await couponLock(env, request, url, body, true);
      if (act === "unlock") return await couponLock(env, request, url, body, false);
      if (act === "deleteImpact") return await couponDeleteImpact(env, request, url, body);
      if (act === "del") return await couponDel(env, request, url, body, manage);
      if (act === "exportImpact") return await couponExportImpact(env, request, url, body);
      if (act === "export") return await couponExport(env, request, url, body, manage);
    } catch (e) {
      return apiErr(e && e.message ? String(e.message) : "\u64CD\u4F5C\u5931\u8D25");
    }
  }
  return apiErr("\u63A5\u53E3\u4E0D\u5B58\u5728", 404);
}

// admin-pages.js
function adminVar(cfg = {}) {
  const langs = [{ code: "zh-cn", name: "\u7B80\u4F53\u4E2D\u6587" }];
  const vars = {
    DEBUG: false,
    LANG: "zh-cn",
    LANGS: langs,
    CURRENCY: { code: "CNY", symbol: "\xA5", rate: 1, decimals: 2 },
    HACK_ROUTE_TABLE_COLUMNS: [],
    HACK_SUBMIT_FORM: [],
    HACK_SUBMIT_TAB: [],
    HACK_ROUTE_TABLE_SEARCH: []
  };
  let s = "<script>";
  for (const [k, v] of Object.entries(vars)) {
    s += `setVar(${JSON.stringify(k)}, ${JSON.stringify(v)});`;
  }
  s += "</script>";
  return s;
}
var cssLinks = (paths) => paths.map((p) => `<link rel="stylesheet" href="${p}"/>`).join("\n");
var jsScripts = (paths) => paths.map((p) => `<script src="${p}"></script>`).join("\n");
function renderAdminLoginPage(cfg = {}) {
  const bg = cfg.background_url || "/assets/admin/img/bg.jpg";
  const shopName = cfg.shop_name || "acg-faka";
  const captcha = String(cfg.admin_login_verification) !== "0" ? `<div class="ay-field has-ico">
          <input id="ay-captcha" name="captcha" class="ay-input" type="text" inputmode="numeric"
                 maxlength="4" autocomplete="off" placeholder=" " required>
          <span class="ay-label">\u9A8C\u8BC1\u7801</span>
          <span class="ay-ico" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
            </svg>
          </span>
          <img id="ay-captcha-img" class="ay-captcha" src="/user/captcha/image?action=adminLogin"
               data-acg-refresh="/user/captcha/image?action=adminLogin"
               title="\u770B\u4E0D\u6E05\uFF1F\u70B9\u6211\u5237\u65B0" alt="\u9A8C\u8BC1\u7801">
        </div>` : "";
  return `<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>\u767B\u5F55 - ${htmlEscape(shopName)}</title>
    <script>(function(){try{var p=localStorage.getItem('admin-theme')||'auto';var d=p==='auto'?(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'):p;var e=document.documentElement;e.setAttribute('data-theme',d);e.setAttribute('data-theme-pref',p);}catch(_){document.documentElement.setAttribute('data-theme','light');}})();</script>
    ${cssLinks([
    "/assets/common/css/_.css",
    "/assets/admin/css/auth.css",
    "/assets/admin/css/_material-auth.css",
    "/assets/admin/css/style.bundle.css",
    "/assets/common/css/font.min.css",
    "/assets/common/js/layui/css/layui.css",
    "/assets/common/css/select2.min.css",
    "/assets/common/css/component.css",
    "/assets/common/css/toastr.min.css",
    "/assets/common/js/table/bootstrap-table.css",
    "/assets/common/js/layer/theme/default/layer.css",
    "/assets/admin/css/auth.css",
    "/assets/common/css/md-tokens.css",
    "/assets/admin/css/material-auth.css"
  ])}
    <script src="/assets/common/js/ready.js"></script>
    ${adminVar(cfg)}
</head>
<body class="ay-bg" style="background-image: linear-gradient(180deg, rgb(255 255 255 / 0%), rgb(255 255 255 / 71%)), url('${htmlEscape(bg)}')">
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
        <button type="button" class="ay-theme" id="ay-theme" aria-label="\u5207\u6362\u660E\u6697\u4E3B\u9898" title="\u5207\u6362\u660E\u6697\u4E3B\u9898">
            <svg class="ico-moon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/>
            </svg>
            <svg class="ico-sun" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="4"/>
                <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>
            </svg>
        </button>
        <header class="ay-head">
            <div class="ay-logo" aria-hidden="true"></div>
            <h1 id="ay-title" class="ay-title">\u6B22\u8FCE\u56DE\u6765\uFF0C\u6307\u6325\u5B98</h1>
            <p id="ay-sub" class="ay-sub">\u6B63\u5728\u9A8C\u8BC1\u60A8\u7684\u7BA1\u7406\u5458\u8EAB\u4EFD</p>
        </header>

        <div class="ay-body">
            <form id="ay-form" method="post" novalidate>
                <div class="ay-field has-ico">
                    <input id="ay-user" name="username" class="ay-input" type="text" placeholder=" "
                           autocomplete="username" autofocus required>
                    <span class="ay-label">\u90AE\u7BB1</span>
                    <span class="ay-ico" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </span>
                </div>
                <div class="ay-field has-ico">
                    <input id="ay-pass" name="password" class="ay-input" type="password" placeholder=" "
                           autocomplete="current-password" required>
                    <span class="ay-label">\u5BC6\u7801</span>
                    <span class="ay-ico" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </span>
                    <button type="button" class="ay-eye" id="ay-eye" aria-label="\u663E\u793A\u5BC6\u7801" aria-controls="ay-pass">
                        <svg class="ay-eye-open" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12Z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg class="ay-eye-shut" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                            <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/>
                            <path d="m1 1 22 22"/>
                        </svg>
                    </button>
                    <span class="ay-caps" id="ay-caps" hidden>\u5927\u5199\u9501\u5B9A\u5DF2\u5F00\u542F</span>
                </div>
                ${captcha}
                <div class="ay-field has-ico ay-2fa is-hidden">
                    <input id="ay-code" name="code" class="ay-input" type="text" inputmode="numeric"
                           autocomplete="one-time-code" maxlength="6" placeholder=" ">
                    <span class="ay-label">\u8C37\u6B4C\u9A8C\u8BC1\u7801</span>
                    <span class="ay-ico" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0 3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                        </svg>
                    </span>
                </div>

                <div class="ay-row">
                    <label class="ay-check"><input type="checkbox" id="ay-remember" name="remember" value="1">\u4FDD\u6301\u767B\u5F55(365\u5929)</label>
                    <a class="ay-link" href="javascript:void(0)" data-acg-action="message.info" data-acg-args='["\u67E5\u770B\u5B98\u65B9\u6587\u6863\u91CD\u7F6E\u5BC6\u7801\u65B9\u6CD5"]'>\u5FD8\u8BB0\u5BC6\u7801\uFF1F</a>
                </div>

                <button class="ay-btn" type="submit" id="ay-submit">\u786E\u8BA4\u767B\u5165</button>
            </form>
            <div class="ay-foot">\xA9 ${htmlEscape(shopName)}</div>
        </div>
    </section>
</main>

<script>ready("/assets/admin/controller/auth/login.js");</script>
${jsScripts([
    "/assets/common/js/_.js",
    "/assets/common/js/util/dict.js",
    "/assets/common/js/jquery.min.js",
    "/assets/common/js/toastr.min.js",
    "/assets/common/js/component/loading.js",
    "/assets/common/js/util.js",
    "/assets/common/js/layer/layer.js",
    "/assets/common/js/jquery.pjax.min.js",
    "/assets/common/js/jquery.qrcode.min.js",
    "/assets/common/js/format.js",
    "/assets/common/js/message.js",
    "/assets/common/js/component.js",
    "/assets/common/js/layui/layui.js",
    "/assets/common/js/jquery.treegrid.min.js",
    "/assets/common/js/bootstrap/bootstrap.bundle.min.js",
    "/assets/common/js/table/bootstrap-table.min.js",
    "/assets/common/js/table/bootstrap-table-treegrid.min.js",
    "/assets/common/js/component/form.js",
    "/assets/common/js/component/search.js",
    "/assets/common/js/component/xm-select.js",
    "/assets/common/js/component/tree.select.js",
    "/assets/common/js/component/authtree.js",
    "/assets/common/js/component/table.js",
    "/assets/common/js/component/select2.min.js",
    "/assets/common/js/cache.js",
    "/assets/common/js/editor/editor.js",
    "/assets/common/js/editor/code/code.js",
    "/assets/common/js/component/decimal.js"
  ])}
</body>
</html>`;
}
function renderCrudPage({ cfg, manage, title, activePath, toolbar = null, body, readyJs = "" }) {
  return renderAdminShell({
    cfg,
    manage,
    title,
    activePath,
    toolbar,
    body: `${body}<script>${readyJs}<\/script>`
  });
}
function renderAdminCategoryPage(cfg, manage) {
  const body = `
<div class="card mb-5 mb-xl-8">
  <div class="card-header border-0">
    <div class="card-toolbar">
      <button class="btn btn-sm btn-light-primary crud-add me-3"><i class="fa-duotone fa-regular fa-circle-plus"></i> \u6DFB\u52A0\u5206\u7C7B</button>
      <button class="btn btn-sm btn-light-success crud-status-on me-3"><i class="fa-duotone fa-regular fa-circle-play"></i> \u542F\u7528\u9009\u4E2D</button>
      <button class="btn btn-sm btn-light-dark crud-status-off me-3"><i class="fa-duotone fa-regular fa-circle-stop"></i> \u505C\u7528\u9009\u4E2D</button>
      <button class="btn btn-sm btn-light-danger crud-del me-3"><i class="fa-duotone fa-regular fa-trash-can"></i> \u79FB\u9664\u9009\u4E2D</button>
    </div>
  </div>
  <div class="card-body py-3">
    <div class="table-responsive">
      <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3 crud-table" id="category-table">
        <thead><tr class="fw-bold text-muted">
          <th style="width:40px"><input type="checkbox" class="crud-check-all"></th>
          <th>ID</th><th>\u540D\u79F0</th><th>\u7236\u7EA7</th><th>\u6392\u5E8F</th><th>\u72B6\u6001</th><th>\u64CD\u4F5C</th>
        </tr></thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>
<div class="modal fade" tabindex="-1" id="catModal"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header py-3"><h5 class="modal-title">\u7F16\u8F91\u5206\u7C7B</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <form class="modal-form"><div class="modal-body">
    <input type="hidden" name="id">
    <div class="mb-3"><label class="form-label">\u4E0A\u7EA7\u5206\u7C7B</label>
      <select class="form-select" name="pid"><option value="0">\u9876\u7EA7</option></select></div>
    <div class="mb-3"><label class="form-label">\u5206\u7C7B\u540D\u79F0</label>
      <input class="form-control" name="name" required></div>
    <div class="mb-3"><label class="form-label">\u6392\u5E8F</label>
      <input class="form-control" name="sort" type="number" value="0"></div>
    <div class="mb-3 form-check"><label class="form-check-label">
      <input class="form-check-input" type="checkbox" name="status" value="1" checked> \u542F\u7528</label></div>
    <div class="mb-3 form-check"><label class="form-check-label">
      <input class="form-check-input" type="checkbox" name="hide" value="1"> \u9690\u85CF</label></div>
  </div><div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">\u53D6\u6D88</button>
    <button type="submit" class="btn btn-primary">\u4FDD\u5B58</button>
  </div></form>
</div></div></div>`;
  const js = `
  ready(() => {
    const table = document.getElementById('category-table').querySelector('tbody');
    const API = '/admin/api/category/';
    function load() {
      util.post({ url: API + 'data', loader: false,
        done: res => {
          table.innerHTML = '';
          const cats = res.data.list || [];
          const sel = document.querySelector('select[name="pid"]');
          const curId = Number(sel.dataset.cur || 0);
          sel.innerHTML = '<option value="0">\u9876\u7EA7</option>' + cats.filter(c => Number(c.id) !== curId).map(c => '<option value="' + c.id + '">' + c.name + '</option>').join('');
          cats.forEach(c => {
            const tr = document.createElement('tr');
            tr.dataset.id = c.id; tr.dataset.name = c.name; tr.dataset.pid = c.pid || 0;
            tr.dataset.sort = c.sort || 0; tr.dataset.status = c.status; tr.dataset.hide = c.hide || 0;
            const indent = Number(c.pid) ? '&nbsp;&nbsp;\u2514 ' : '';
            tr.innerHTML = '<td><input type="checkbox" class="crud-check"></td>' +
              '<td>' + c.id + '</td>' +
              '<td>' + indent + c.name + '</td>' +
              '<td>' + (Number(c.pid) ? '#' + c.pid : '-') + '</td>' +
              '<td>' + c.sort + '</td>' +
              '<td>' + (Number(c.status) === 1 ? '<span class="badge badge-light-success">\u542F\u7528</span>' : '<span class="badge badge-light-danger">\u505C\u7528</span>') + '</td>' +
              '<td><button class="btn btn-sm btn-light-primary me-2 row-edit">\u7F16\u8F91</button>' +
              '<button class="btn btn-sm btn-light-danger row-del">\u5220\u9664</button></td>';
            table.appendChild(tr);
          });
        },
        error: res => message.error(res.msg) });
    }
    function selected() { return [...table.querySelectorAll('.crud-check:checked')].map(x => x.closest('tr').dataset.id); }
    document.querySelector('.crud-check-all').addEventListener('change', e => {
      table.querySelectorAll('.crud-check').forEach(x => x.checked = e.target.checked);
    });
    document.querySelector('.crud-add').addEventListener('click', () => {
      const sel = document.querySelector('select[name="pid"]'); sel.dataset.cur = 0; sel.value = '0';
      const f = document.querySelector('.modal-form');
      f.querySelector('input[name="id"]').value = '';
      f.querySelector('input[name="name"]').value = '';
      f.querySelector('input[name="sort"]').value = '0';
      f.querySelector('input[name="status"]').checked = true;
      f.querySelector('input[name="hide"]').checked = false;
      util.openModal && util.openModal('catModal') || (window.bootstrap && bootstrap.Modal.getOrCreateInstance(document.getElementById('catModal'))).show();
    });
    table.addEventListener('click', e => {
      const tr = e.target.closest('tr'); if (!tr) return;
      if (e.target.closest('.row-edit')) {
        const sel = document.querySelector('select[name="pid"]'); sel.dataset.cur = Number(tr.dataset.id);
        const f = document.querySelector('.modal-form');
        f.querySelector('input[name="id"]').value = tr.dataset.id;
        f.querySelector('input[name="name"]').value = tr.dataset.name;
        f.querySelector('input[name="sort"]').value = tr.dataset.sort;
        f.querySelector('input[name="status"]').checked = Number(tr.dataset.status) === 1;
        f.querySelector('input[name="hide"]').checked = Number(tr.dataset.hide) === 1;
        (window.bootstrap && bootstrap.Modal.getOrCreateInstance(document.getElementById('catModal'))).show();
      }
      if (e.target.closest('.row-del')) {
        message.confirm && message.confirm ? message.confirm({ title: '\u786E\u8BA4\u5220\u9664\u5206\u7C7B ' + tr.dataset.name + ' ?', done: () => gotoDel([tr.dataset.id]) }) : (confirm('\u786E\u8BA4\u5220\u9664\u5206\u7C7B ' + tr.dataset.name + ' ?') && gotoDel([tr.dataset.id]));
      }
    });
    function gotoDel(ids) {
      // \u7B80\u5355\u5220\u9664(\u4E0D\u5F3A\u5236\u9884\u89C8 token; \u517C\u5BB9\u539F\u7248\u524D\u7AEF\u5219\u9700 deleteImpact+del)
      util.post({ url: API + 'del', data: { list: ids.join(','), preview_token: '' },
        done: () => { message.success && message.success(res => res) && load(); load(); toastr && toastr.success('\u5DF2\u5220\u9664'); load(); },
        error: res => { if (res.msg.indexOf('\u9884\u89C8') >= 0) { message.error(res.msg); } else message.error(res.msg); } });
      load();
    }
    document.querySelector('.crud-del').addEventListener('click', () => {
      const ids = selected(); if (!ids.length) return message.error('\u8BF7\u5148\u52FE\u9009\u5206\u7C7B');
      gotoDel(ids);
    });
    document.querySelector('.crud-status-on').addEventListener('click', () => {
      const ids = selected(); if (!ids.length) return message.error('\u8BF7\u5148\u52FE\u9009\u5206\u7C7B');
      util.post({ url: API + 'status', data: { list: ids.join(','), status: '1' }, done: load, error: res => message.error(res.msg) });
    });
    document.querySelector('.crud-status-off').addEventListener('click', () => {
      const ids = selected(); if (!ids.length) return message.error('\u8BF7\u5148\u52FE\u9009\u5206\u7C7B');
      util.post({ url: API + 'status', data: { list: ids.join(','), status: '0' }, done: load, error: res => message.error(res.msg) });
    });
    document.querySelector('.modal-form').addEventListener('submit', e => {
      e.preventDefault();
      const data = util.serializeObject ? util.serializeObject('.modal-form') : Object.fromEntries(new FormData(e.target).entries());
      if (typeof data.status === 'undefined') data.status = '1';
      if (typeof data.hide === 'undefined') data.hide = '0';
      data.name = e.target.querySelector('input[name="name"]').value.trim();
      if (!data.name) return message.error('\u5206\u7C7B\u540D\u79F0\u4E0D\u80FD\u4E3A\u7A7A');
      util.post({ url: API + 'save', data, done: () => { message.success && message.success('\u4FDD\u5B58\u6210\u529F'); load();
        if (window.bootstrap) bootstrap.Modal.getInstance(document.getElementById('catModal'))?.hide(); },
        error: res => message.error(res.msg) });
    });
    load();
  });
  `;
  return renderCrudPage({ cfg, manage, title: "\u5206\u7C7B\u7BA1\u7406", activePath: "/admin/category/index", body, readyJs: js });
}
function renderAdminCommodityPage(cfg, manage) {
  const body = `
<div class="card mb-5 mb-xl-8">
  <div class="card-header border-0">
    <div class="card-toolbar">
      <button class="btn btn-sm btn-light-primary crud-add me-3"><i class="fa-duotone fa-regular fa-circle-plus"></i> \u6DFB\u52A0\u5546\u54C1</button>
      <button class="btn btn-sm btn-light-success crud-status-on me-3"><i class="fa-duotone fa-regular fa-circle-play"></i> \u542F\u7528\u9009\u4E2D</button>
      <button class="btn btn-sm btn-light-dark crud-status-off me-3"><i class="fa-duotone fa-regular fa-circle-stop"></i> \u505C\u7528\u9009\u4E2D</button>
      <button class="btn btn-sm btn-light-danger crud-del me-3"><i class="fa-duotone fa-regular fa-trash-can"></i> \u79FB\u9664\u9009\u4E2D</button>
    </div>
  </div>
  <div class="card-body py-3">
    <div class="mb-3"><input class="form-control" id="commodity-search" placeholder="\u641C\u7D22\u5546\u54C1\u540D\u79F0\u2026" style="max-width:280px"></div>
    <div class="table-responsive">
      <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3" id="commodity-table">
        <thead><tr class="fw-bold text-muted">
          <th style="width:40px"><input type="checkbox" class="crud-check-all"></th>
          <th>ID</th><th>\u5C01\u9762</th><th>\u540D\u79F0</th><th>\u5206\u7C7B</th><th>\u4EF7\u683C</th><th>\u5E93\u5B58</th><th>\u72B6\u6001</th><th>\u64CD\u4F5C</th>
        </tr></thead>
        <tbody></tbody>
      </table>
      <div class="d-flex justify-content-end align-items-center mt-3">
        <button class="btn btn-sm btn-secondary crud-prev me-2">\u4E0A\u4E00\u9875</button>
        <span class="crud-pageinfo me-2"></span>
        <button class="btn btn-sm btn-secondary crud-next">\u4E0B\u4E00\u9875</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" tabindex="-1" id="commodityModal"><div class="modal-dialog modal-lg"><div class="modal-content">
  <div class="modal-header py-3"><h5 class="modal-title">\u7F16\u8F91\u5546\u54C1</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <form class="modal-form"><div class="modal-body">
    <input type="hidden" name="id">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">\u5546\u54C1\u540D\u79F0</label><input class="form-control" name="name" required></div>
      <div class="col-md-6"><label class="form-label">\u5206\u7C7B</label><select class="form-select" name="category_id"></select></div>
      <div class="col-md-4"><label class="form-label">\u552E\u4EF7</label><input class="form-control" name="price" type="number" step="0.01" value="0"></div>
      <div class="col-md-4"><label class="form-label">\u4F1A\u5458\u4EF7</label><input class="form-control" name="user_price" type="number" step="0.01" value="0"></div>
      <div class="col-md-4"><label class="form-label">\u6210\u672C\u4EF7</label><input class="form-control" name="factory_price" type="number" step="0.01" value="0"></div>
      <div class="col-md-6"><label class="form-label">\u5C01\u9762 URL</label><input class="form-control" name="cover" placeholder="/favicon.ico"></div>
      <div class="col-md-6"><label class="form-label">\u5546\u54C1\u7F16\u7801</label><input class="form-control" name="code"></div>
      <div class="col-md-4"><label class="form-label">\u53D1\u8D27\u65B9\u5F0F</label>
        <select class="form-select" name="delivery_way">
          <option value="0">\u81EA\u52A8\u53D1\u8D27(\u5361\u5BC6)</option><option value="1">\u624B\u52A8\u53D1\u8D27</option><option value="2">API \u4F9B\u8D27(\u9884\u7559)</option>
        </select></div>
      <div class="col-md-4"><label class="form-label">\u81EA\u52A8\u53D1\u8D27\u6A21\u5F0F</label>
        <select class="form-select" name="delivery_auto_mode"><option value="0">\u987A\u5E8F</option><option value="1">\u968F\u673A</option></select></div>
      <div class="col-md-4"><label class="form-label">\u6392\u5E8F</label><input class="form-control" name="sort" type="number" value="0"></div>
      <div class="col-md-6"><label class="form-label">\u8054\u7CFB\u65B9\u5F0F\u7C7B\u578B</label>
        <select class="form-select" name="contact_type"><option value="0">\u65E0</option><option value="1">QQ</option><option value="2">\u90AE\u7BB1</option><option value="3">\u624B\u673A\u53F7</option><option value="4">\u4EFB\u610F</option></select></div>
      <div class="col-md-6"><label class="form-label">\u5BC6\u7801\u72B6\u6001</label>
        <select class="form-select" name="password_status"><option value="0">\u65E0\u5BC6\u7801</option><option value="1">\u9875\u9762\u8BBE\u7F6E\u5BC6\u7801</option></select></div>
      <div class="col-12"><label class="form-label">\u53D1\u8D27\u8BF4\u660E/\u5361\u5BC6\u63D0\u793A</label><textarea class="form-control" name="delivery_message" rows="2"></textarea></div>
      <div class="col-12"><label class="form-label">\u5546\u54C1\u4ECB\u7ECD</label><textarea class="form-control" name="description" rows="3"></textarea></div>
      <div class="col-md-3 form-check"><label class="form-check-label">
        <input class="form-check-input" type="checkbox" name="status" value="1" checked> \u542F\u7528</label></div>
      <div class="col-md-3 form-check"><label class="form-check-label">
        <input class="form-check-input" type="checkbox" name="api_status" value="1"> API\u5F00\u653E</label></div>
      <div class="col-md-3 form-check"><label class="form-check-label">
        <input class="form-check-input" type="checkbox" name="only_user" value="1"> \u4EC5\u4F1A\u5458</label></div>
      <div class="col-md-3 form-check"><label class="form-check-label">
        <input class="form-check-input" type="checkbox" name="recommend" value="1"> \u63A8\u8350</label></div>
    </div>
  </div><div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">\u53D6\u6D88</button>
    <button type="submit" class="btn btn-primary">\u4FDD\u5B58</button>
  </div></form>
</div></div></div>`;
  const js = `
  ready(() => {
    const tbody = document.getElementById('commodity-table').querySelector('tbody');
    const API = '/admin/api/commodity/';
    let page = 1, pageSize = 10;
    function load() {
      const kw = (document.getElementById('commodity-search').value || '').trim();
      util.post({ url: API + 'data', data: { page, limit: pageSize, name: kw }, loader: false,
        done: res => {
          tbody.innerHTML = '';
          (res.data.list || []).forEach(c => {
            const tr = document.createElement('tr');
            tr.dataset = {
              id: c.id, name: c.name, category_id: c.category_id, price: c.price, user_price: c.user_price,
              factory_price: c.factory_price, cover: c.cover || '', code: c.code || '',
              delivery_way: c.delivery_way || 0, delivery_auto_mode: c.delivery_auto_mode || 0,
              contact_type: c.contact_type || 0, password_status: c.password_status || 0,
              sort: c.sort || 0, delivery_message: c.delivery_message || '', description: c.description || '',
              status: c.status, api_status: c.api_status, only_user: c.only_user, recommend: c.recommend || 0,
            };
            tr.innerHTML = '<td><input type="checkbox" class="crud-check"></td>' +
              '<td>' + c.id + '</td>' +
              '<td><img src="' + (c.cover || '/favicon.ico') + '" style="width:34px;height:34px;object-fit:cover" class="rounded"></td>' +
              '<td>' + c.name + '</td>' +
              '<td>' + (c.category ? c.category.name : '-') + '</td>' +
              '<td>\uFFE5' + c.price + '</td>' +
              '<td>' + (c.card_count !== undefined ? c.card_count : c.stock ?? '-') + '</td>' +
              '<td>' + (Number(c.status) === 1 ? '<span class="badge badge-light-success">\u542F\u7528</span>' : '<span class="badge badge-light-danger">\u505C\u7528</span>') + '</td>' +
              '<td><button class="btn btn-sm btn-light-primary me-2 row-edit">\u7F16\u8F91</button>' +
              '<button class="btn btn-sm btn-light-danger row-del">\u5220\u9664</button>' +
              '<a class="btn btn-sm btn-light-info row-cards" href="/admin/card/index?commodity_id=' + c.id + '">\u5361\u5BC6</a></td>';
            tbody.appendChild(tr);
          });
          document.querySelector('.crud-pageinfo').textContent = '\u7B2C ' + page + ' \u9875 / \u5171 ' + res.data.count + ' \u6761';
        },
        error: res => message.error(res.msg) });
    }
    function selected() { return [...tbody.querySelectorAll('.crud-check:checked')].map(x => x.closest('tr').dataset.id); }
    document.querySelector('.crud-check-all').addEventListener('change', e => tbody.querySelectorAll('.crud-check').forEach(x => x.checked = e.target.checked));
    function refreshCats() {
      util.post({ url: '/admin/api/category/data', loader: false, done: res => {
        const sel = document.querySelector('select[name="category_id"]');
        const cur = sel.dataset.cur || '';
        sel.innerHTML = '<option value="">\u8BF7\u9009\u62E9</option>' + (res.data.list || []).map(c => '<option value="' + c.id + '">' + c.name + '</option>').join('');
        if (cur) sel.value = cur;
      }, error: () => {} });
    }
    document.querySelector('.crud-add').addEventListener('click', () => {
      refreshCats();
      const f = document.querySelector('#commodityModal .modal-form');
      f.querySelector('input[name="id"]').value = '';
      f.querySelector('input[name="name"]').value = '';
      f.querySelector('input[name="price"]').value = '0';
      f.querySelector('input[name="user_price"]').value = '0';
      f.querySelector('input[name="factory_price"]').value = '0';
      f.querySelector('input[name="cover"]').value = '/favicon.ico';
      f.querySelector('input[name="code"]').value = '';
      f.querySelector('select[name="delivery_way"]').value = '0';
      f.querySelector('select[name="delivery_auto_mode"]').value = '0';
      f.querySelector('input[name="sort"]').value = '0';
      f.querySelector('select[name="contact_type"]').value = '0';
      f.querySelector('select[name="password_status"]').value = '0';
      f.querySelector('textarea[name="delivery_message"]').value = '';
      f.querySelector('textarea[name="description"]').value = '';
      ['status','api_status','only_user','recommend'].forEach(n => { const x = f.querySelector('input[name="' + n + '"]'); if (x) x.checked = n === 'status'; });
      (window.bootstrap && bootstrap.Modal.getOrCreateInstance(document.getElementById('commodityModal'))).show();
    });
    tbody.addEventListener('click', e => {
      const tr = e.target.closest('tr'); if (!tr) return;
      if (e.target.closest('.row-edit')) {
        refreshCats();
        const sel = document.querySelector('select[name="category_id"]'); sel.dataset.cur = tr.dataset.category_id;
        const f = document.querySelector('#commodityModal .modal-form');
        for (const k of ['id','name','price','user_price','factory_price','cover','code','delivery_message','description','sort']) {
          const x = f.querySelector('[name="' + k + '"]'); if (x) x.value = tr.dataset[k] ?? '';
        }
        for (const k of ['delivery_way','delivery_auto_mode','contact_type','password_status']) {
          const x = f.querySelector('[name="' + k + '"]'); if (x) x.value = tr.dataset[k] ?? '0';
        }
        ['status','api_status','only_user','recommend'].forEach(n => { const x = f.querySelector('input[name="' + n + '"]'); if (x) x.checked = Number(tr.dataset[n] || 0) === 1; });
        (window.bootstrap && bootstrap.Modal.getOrCreateInstance(document.getElementById('commodityModal'))).show();
      }
      if (e.target.closest('.row-del')) {
        if (confirm('\u786E\u8BA4\u5220\u9664\u5546\u54C1 ' + tr.dataset.name + ' ?')) {
          util.post({ url: API + 'del', data: { list: tr.dataset.id }, done: load, error: res => message.error(res.msg) });
        }
      }
    });
    function batch(field, val) {
      const ids = selected(); if (!ids.length) return message.error('\u8BF7\u5148\u52FE\u9009\u5546\u54C1');
      util.post({ url: API + field, data: { list: ids.join(','), status: val }, done: load, error: res => message.error(res.msg) });
    }
    document.querySelector('.crud-status-on').addEventListener('click', () => batch('status', '1'));
    document.querySelector('.crud-status-off').addEventListener('click', () => batch('status', '0'));
    document.querySelector('.crud-del').addEventListener('click', () => {
      const ids = selected(); if (!ids.length) return message.error('\u8BF7\u5148\u52FE\u9009\u5546\u54C1');
      if (confirm('\u786E\u8BA4\u5220\u9664\u9009\u4E2D ' + ids.length + ' \u4E2A\u5546\u54C1\uFF1F\u76F8\u5173\u5361\u5BC6/\u8BA2\u5355\u5F15\u7528\u4F1A\u88AB\u6E05\u7406')) {
        util.post({ url: API + 'del', data: { list: ids.join(',') }, done: load, error: res => message.error(res.msg) });
      }
    });
    document.getElementById('commodity-search').addEventListener('input', () => { page = 1; load(); });
    document.querySelector('.crud-prev').addEventListener('click', () => { if (page > 1) { page--; load(); } });
    document.querySelector('.crud-next').addEventListener('click', () => { page++; load(); });
    document.querySelector('#commodityModal .modal-form').addEventListener('submit', e => {
      e.preventDefault();
      const data = Object.fromEntries(new FormData(e.target).entries());
      if (!data.name.trim()) return message.error('\u5546\u54C1\u540D\u79F0\u4E0D\u80FD\u4E3A\u7A7A');
      if (!data.category_id) return message.error('\u8BF7\u9009\u62E9\u5546\u54C1\u5206\u7C7B');
      ['status','api_status','only_user','recommend'].forEach(n => { if (typeof data[n] === 'undefined') data[n] = '0'; });
      util.post({ url: API + 'save', data, done: () => { message.success('\u4FDD\u5B58\u6210\u529F'); load();
        if (window.bootstrap) bootstrap.Modal.getInstance(document.getElementById('commodityModal'))?.hide(); },
        error: res => message.error(res.msg) });
    });
    load();
  });
  `;
  return renderCrudPage({ cfg, manage, title: "\u5546\u54C1\u7BA1\u7406", activePath: "/admin/commodity/index", body, readyJs: js });
}
function renderAdminCardPage(cfg, manage, commodityId = 0) {
  const body = `
<div class="card mb-5 mb-xl-8">
  <div class="card-header border-0">
    <div class="card-toolbar">
      <button class="btn btn-sm btn-light-primary crud-add me-3"><i class="fa-duotone fa-regular fa-circle-plus"></i> \u6DFB\u52A0\u5361\u5BC6</button>
      <button class="btn btn-sm btn-light-warning crud-lock me-3"><i class="fa-duotone fa-regular fa-lock"></i> \u9501\u5B9A\u9009\u4E2D</button>
      <button class="btn btn-sm btn-light-info crud-unlock me-3"><i class="fa-duotone fa-regular fa-unlock"></i> \u89E3\u9501\u9009\u4E2D</button>
      <button class="btn btn-sm btn-light-danger crud-del me-3"><i class="fa-duotone fa-regular fa-trash-can"></i> \u79FB\u9664\u9009\u4E2D</button>
    </div>
  </div>
  <div class="card-body py-3">
    <div class="row g-2 mb-3">
      <div class="col-md-4"><select class="form-select" id="card-commodity">
        <option value="">\u9009\u62E9\u5546\u54C1</option></select></div>
      <div class="col-md-2"><select class="form-select" id="card-status">
        <option value="">\u5168\u90E8\u72B6\u6001</option><option value="0">\u672A\u552E</option><option value="1">\u5DF2\u552E</option><option value="2">\u9501\u5B9A</option></select></div>
      <div class="col-md-4"><input class="form-control" id="card-search" placeholder="\u641C\u7D22\u5361\u5BC6\u2026"></div>
    </div>
    <div class="table-responsive">
      <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3" id="card-table">
        <thead><tr class="fw-bold text-muted">
          <th style="width:40px"><input type="checkbox" class="crud-check-all"></th>
          <th>ID</th><th>\u5546\u54C1</th><th>\u5361\u5BC6</th><th>\u72B6\u6001</th><th>\u552E\u4EF7\u6210\u672C</th><th>\u8D2D\u4E70\u65F6\u95F4/\u8BA2\u5355</th><th>\u64CD\u4F5C</th>
        </tr></thead>
        <tbody></tbody>
      </table>
      <div class="d-flex justify-content-end align-items-center mt-3">
        <button class="btn btn-sm btn-secondary crud-prev me-2">\u4E0A\u4E00\u9875</button>
        <span class="crud-pageinfo me-2"></span>
        <button class="btn btn-sm btn-secondary crud-next">\u4E0B\u4E00\u9875</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" tabindex="-1" id="cardModal"><div class="modal-dialog modal-lg"><div class="modal-content">
  <div class="modal-header py-3"><h5 class="modal-title">\u6DFB\u52A0\u5361\u5BC6</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <form class="modal-form"><div class="modal-body">
    <div class="mb-3"><label class="form-label">\u5546\u54C1</label><select class="form-select" name="commodity_id"></select></div>
    <div class="mb-3"><label class="form-label">\u5361\u5BC6\u5185\u5BB9 <span class="text-muted">(\u6BCF\u884C\u4E00\u6761\uFF0C\u652F\u6301\u6279\u91CF)</span></label>
      <textarea class="form-control" name="secret" rows="6" required placeholder="CARD-AAAA-1111&#10;CARD-BBBB-2222"></textarea></div>
    <div class="mb-3"><label class="form-label">\u6210\u672C\u4EF7</label><input class="form-control" name="cost" type="number" step="0.01" value="0"></div>
  </div><div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">\u53D6\u6D88</button>
    <button type="submit" class="btn btn-primary">\u5BFC\u5165</button>
  </div></form>
</div></div></div>`;
  const js = `
  ready(() => {
    const tbody = document.getElementById('card-table').querySelector('tbody');
    const API = '/admin/api/card/';
    let page = 1, pageSize = 10;
    const presetId = ${commodityId || 0};
    function loadCommodities(select, cur) {
      util.post({ url: '/admin/api/commodity/data', data: { page:1, limit:100 }, loader: false, done: res => {
        const items = res.data.list || [];
        const opts = items.filter(c => Number(c.delivery_way) === 0 || c.card_count !== undefined).map(c => '<option value="' + c.id + '">' + c.name + '</option>').join('');
        select.innerHTML = '<option value="">\u9009\u62E9\u5546\u54C1</option>' + opts;
        if (cur) select.value = cur;
      }, error: () => {} });
    }
    function load() {
      const data = { page, limit: pageSize };
      const cid = document.getElementById('card-commodity').value;
      const st = document.getElementById('card-status').value;
      const kw = document.getElementById('card-search').value.trim();
      if (cid) data.commodity_id = cid;
      if (st !== '') data.status = st;
      if (kw) data.secret = kw;
      util.post({ url: API + 'data', data, loader: false,
        done: res => {
          tbody.innerHTML = '';
          (res.data.list || []).forEach(c => {
            const stText = Number(c.status) === 0 ? '<span class="badge badge-light-success">\u672A\u552E</span>'
              : Number(c.status) === 1 ? '<span class="badge badge-light-secondary">\u5DF2\u552E</span>'
              : '<span class="badge badge-light-warning">\u9501\u5B9A</span>';
            const tr = document.createElement('tr');
            tr.dataset.id = c.id; tr.dataset.secretKey = c.secret; tr.dataset.cost = c.cost || 0;
            tr.innerHTML = '<td><input type="checkbox" class="crud-check"></td>' +
              '<td>' + c.id + '</td>' +
              '<td>' + (c.commodity ? c.commodity.name : ('#' + c.commodity_id)) + '</td>' +
              '<td><code>' + c.secret + '</code></td>' +
              '<td>' + stText + '</td>' +
              '<td>\uFFE5' + (c.cost || 0) + '</td>' +
              '<td>' + (c.purchase_time ? new Date(c.purchase_time * 1000).toLocaleString() : '-') + (c.order ? ' <a href="#">#' + c.order.trade_no + '</a>' : '') + '</td>' +
              '<td><button class="btn btn-sm btn-light-danger row-del">\u5220\u9664</button></td>';
            tbody.appendChild(tr);
          });
          document.querySelector('.crud-pageinfo').textContent = '\u7B2C ' + page + ' \u9875 / \u5171 ' + res.data.count + ' \u6761';
        },
        error: res => message.error(res.msg) });
    }
    function selected() { return [...tbody.querySelectorAll('.crud-check:checked')].map(x => x.closest('tr').dataset.id); }
    document.querySelector('.crud-check-all').addEventListener('change', e => tbody.querySelectorAll('.crud-check').forEach(x => x.checked = e.target.checked));
    const commoditySelect = document.querySelector('#cardModal select[name="commodity_id"]');
    loadCommodities(commoditySelect, presetId || '');
    const filterSelect = document.getElementById('card-commodity');
    loadCommodities(filterSelect, presetId || '');
    if (presetId) { document.getElementById('card-commodity').value = presetId; }
    document.getElementById('card-commodity').addEventListener('change', () => { page = 1; load(); });
    document.getElementById('card-status').addEventListener('change', () => { page = 1; load(); });
    document.getElementById('card-search').addEventListener('input', () => { page = 1; load(); });
    document.querySelector('.crud-prev').addEventListener('click', () => { if (page > 1) { page--; load(); } });
    document.querySelector('.crud-next').addEventListener('click', () => { page++; load(); });
    document.querySelector('.crud-add').addEventListener('click', () => {
      const sel = document.querySelector('#cardModal select[name="commodity_id"]');
      loadCommodities(sel, document.getElementById('card-commodity').value || presetId || '');
      (window.bootstrap && bootstrap.Modal.getOrCreateInstance(document.getElementById('cardModal'))).show();
    });
    tbody.addEventListener('click', e => {
      const tr = e.target.closest('tr'); if (!tr || !e.target.closest('.row-del')) return;
      if (confirm('\u786E\u8BA4\u5220\u9664\u5361\u5BC6 ID ' + tr.dataset.id + ' ?')) {
        util.post({ url: API + 'del', data: { list: tr.dataset.id }, done: load, error: res => message.error(res.msg) });
      }
    });
    document.querySelector('.crud-lock').addEventListener('click', () => {
      const ids = selected(); if (!ids.length) return message.error('\u8BF7\u5148\u52FE\u9009\u5361\u5BC6');
      util.post({ url: API + 'lock', data: { list: ids.join(',') }, done: load, error: res => message.error(res.msg) });
    });
    document.querySelector('.crud-unlock').addEventListener('click', () => {
      const ids = selected(); if (!ids.length) return message.error('\u8BF7\u5148\u52FE\u9009\u5361\u5BC6');
      util.post({ url: API + 'unlock', data: { list: ids.join(',') }, done: load, error: res => message.error(res.msg) });
    });
    document.querySelector('.crud-del').addEventListener('click', () => {
      const ids = selected(); if (!ids.length) return message.error('\u8BF7\u5148\u52FE\u9009\u5361\u5BC6');
      if (confirm('\u786E\u8BA4\u5220\u9664\u9009\u4E2D ' + ids.length + ' \u6761\u672A\u552E\u5361\u5BC6\uFF1F')) {
        util.post({ url: API + 'del', data: { list: ids.join(',') }, done: load, error: res => message.error(res.msg) });
      }
    });
    document.querySelector('#cardModal .modal-form').addEventListener('submit', e => {
      e.preventDefault();
      const data = Object.fromEntries(new FormData(e.target).entries());
      if (!data.commodity_id) return message.error('\u8BF7\u9009\u62E9\u5546\u54C1');
      if (!data.secret.trim()) return message.error('\u5361\u5BC6\u4E0D\u80FD\u4E3A\u7A7A');
      util.post({ url: API + 'save', data, done: () => { message.success('\u5BFC\u5165\u6210\u529F'); load();
        e.target.querySelector('textarea[name="secret"]').value = '';
        if (window.bootstrap) bootstrap.Modal.getInstance(document.getElementById('cardModal'))?.hide(); },
        error: res => message.error(res.msg) });
    });
    load();
  });
  `;
  return renderCrudPage({ cfg, manage, title: "\u5361\u5BC6\u7BA1\u7406", activePath: "/admin/card/index", body, readyJs: js });
}
function adminMenu(activePath) {
  const items = [
    { icon: '<path d="M19 5v2h-4V5h4M9 5v6H5V5h4m10 8v6h-4v-6h4M9 17v2H5v-2h4M21 3h-8v6h8V3zM11 3H3v10h8V3zm10 8h-8v10h8V11zm-10 4H3v6h8v-6z"/>', name: "\u63A7\u5236\u53F0", url: "/admin/dashboard/index", section: "Main" },
    { icon: '<path d="M9 13.75c-2.34 0-7 1.17-7 3.5V19h14v-1.75c0-2.33-4.66-3.5-7-3.5zM4.34 17c.84-.58 2.87-1.25 4.66-1.25s3.82.67 4.66 1.25H4.34zM9 12c1.93 0 3.5-1.57 3.5-3.5S10.93 5 9 5S5.5 6.57 5.5 8.5S7.07 12 9 12zm0-5c.83 0 1.5.67 1.5 1.5S9.83 10 9 10s-1.5-.67-1.5-1.5S8.17 7 9 7zm7.04 6.81c1.16.84 1.96 1.96 1.96 3.44V19h4v-1.75c0-2.02-3.5-3.17-5.96-3.44zM15 12c1.93 0 3.5-1.57 3.5-3.5S16.93 5 15 5c-.54 0-1.04.13-1.5.35c.63.89 1 1.98 1 3.15s-.37 2.26-1 3.15c.46.22.96.35 1.5.35z"/>', name: "\u4F1A\u5458\u7BA1\u7406", url: "/admin/user/index", section: "User" },
    { icon: '<path d="M30 12a2 2 0 0 0-2-2V7c0-1.1-.9-2-2-2H4a2 2 0 0 0-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2v-3a2 2 0 0 0 2-2zM4 7h12v3.17A3 3 0 0 0 15 12c0 .77.29 1.47.76 2H16v3H4V7zm14 6a1 1 0 1 1 0-2a1 1 0 0 1 0 2z"/><path d="M6 9h6v2H6zm0 4h6v2H6z"/>'.replace("30 12a2", "20 12a2"), name: "\u5DE5\u5355\u7BA1\u7406", url: "/admin/ticket/index", section: "User" },
    { icon: '<path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM7 9h10v2H7V9zm6 5H7v-2h6v2zm4-6H7V6h10v2z"/>', name: "\u6D88\u606F\u7BA1\u7406", url: "/admin/message/index", section: "User" },
    { icon: '<path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zM4 8h16v8H4V8z"/><path d="M7 10h2v4H7zm4 0h2v4h-2zm4 0h2v4h-2z"/>'.replace("assets/", ""), name: "\u5145\u503C\u8BA2\u5355", url: "/admin/recharge/order", section: "User" },
    { icon: '<path d="M12 2l-5.5 9h11L12 2zm0 3.84L13.93 9h-3.87L12 5.84zM17.5 13c-2.49 0-4.5 2.01-4.5 4.5s2.01 4.5 4.5 4.5s4.5-2.01 4.5-4.5s-2.01-4.5-4.5-4.5zm0 7a2.5 2.5 0 0 1 0-5a2.5 2.5 0 0 1 0 5zM3 21.5h8v-8H3v8zm2-6h4v4H5v-4z"/>', name: "\u5206\u7C7B\u7BA1\u7406", url: "/admin/category/index", section: "Trade" },
    { icon: '<path d="M20 2H4c-1 0-2 .9-2 2v3.01c0 .72.43 1.34 1 1.69V20c0 1.1 1.1 2 2 2h14c.9 0 2-.9 2-2V8.7c.57-.35 1-.97 1-1.69V4c0-1.1-1-2-2-2zm-1 18H5V9h14v11zm1-13H4V4h16v3z"/><path d="M9 12h6v2H9z"/>', name: "\u5546\u54C1\u7BA1\u7406", url: "/admin/commodity/index", section: "Trade" },
    { icon: '<path d="M22 19h-6v-4h-2.68c-1.14 2.42-3.6 4-6.32 4c-3.86 0-7-3.14-7-7s3.14-7 7-7c2.72 0 5.17 1.58 6.32 4H24v6h-2v4zm-4-2h2v-4h2v-2H11.94l-.23-.67C11.01 8.34 9.11 7 7 7c-2.76 0-5 2.24-5 5s2.24 5 5 5c2.11 0 4.01-1.34 4.71-3.33l.23-.67H18v4zM7 15c-1.65 0-3-1.35-3-3s1.35-3 3-3s3 1.35 3 3s-1.35 3-3 3zm0-4c-.55 0-1 .45-1 1s.45 1 1 1s1-.45 1-1s-.45-1-1-1z"/>', name: "\u5361\u5BC6\u7BA1\u7406", url: "/admin/card/index", section: "Trade" },
    { icon: '<path d="M15.55 13c.75 0 1.41-.41 1.75-1.03l3.58-6.49A.996.996 0 0 0 20.01 4H5.21l-.94-2H1v2h2l3.6 7.59l-1.35 2.44C4.52 15.37 5.48 17 7 17h12v-2H7l1.1-2h7.45zM6.16 6h12.15l-2.76 5H8.53L6.16 6zM7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2s-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2s2-.9 2-2s-.9-2-2-2z"/>', name: "\u5546\u54C1\u8BA2\u5355", url: "/admin/order/index", section: "Shared" },
    { icon: '<path d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.42l9 9c.36.36.86.58 1.41.58s1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41s-.23-1.06-.59-1.42zM13 20.01L4 11V4h7v-.01l9 9l-7 7.02z"/><circle cx="6.5" cy="6.5" r="1.5"/>', name: "\u4F18\u60E0\u5238", url: "/admin/coupon/index", section: "Shared" },
    { icon: '<path d="M19.43 12.98c.04-.32.07-.64.07-.98c0-.34-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46a.5.5 0 0 0-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65A.488.488 0 0 0 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1a.566.566 0 0 0-.18-.03c-.17 0-.34.09-.43.25l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98c0 .33.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46a.5.5 0 0 0 .61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.06.02.12.03.18.03c.17 0 .34-.09.43-.25l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zm-1.98-1.71c.04.31.05.52.05.73c0 .21-.02.43-.05.73l-.14 1.13l.89.7l1.08.84l-.7 1.21l-1.27-.51l-1.04-.42l-.9.68c-.43.32-.84.56-1.25.73l-1.06.43l-.16 1.13l-.2 1.35h-1.4l-.19-1.35l-.16-1.13l-1.06-.43c-.43-.18-.83-.41-1.23-.71l-.91-.7l-1.06.43l-1.27.51l-.7-1.21l1.08-.84l.89-.7l-.14-1.13c-.03-.31-.05-.54-.05-.74s.02-.43.05-.73l.14-1.13l-.89-.7l-1.08-.84l.7-1.21l1.27.51l1.04.42l.9-.68c.43-.32.84-.56 1.25-.73l1.06-.43l.16-1.13l.2-1.35h1.39l.19 1.35l.16 1.13l1.06.43c.43.18.83.41 1.23.71l.91.7l1.06-.43l1.27-.51l.7 1.21l-1.07.85l-.89.7l.14 1.13zM12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4s4-1.79 4-4s-1.79-4-4-4zm0 6c-1.1 0-2-.9-2-2s.9-2 2-2s2 .9 2 2s-.9 2-2 2z"/>', name: "\u7CFB\u7EDF\u914D\u7F6E", url: "/admin/config/index", section: "Config" }
  ];
  let html = "";
  let lastSection = "";
  for (const it of items) {
    if (it.section !== lastSection) {
      html += `<div class="menu-content pt-8 pb-2"><span class="menu-section text-muted text-uppercase fs-8 ls-1">${it.section}</span></div>`;
      lastSection = it.section;
    }
    const active = activePath && (activePath === it.url || activePath.indexOf(it.url) === 0) ? "active" : "";
    html += `<div class="menu-item">
              <a class="menu-link ${active}" href="${it.url}">
                <span class="menu-icon"><svg class="menu-svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">${it.icon}</svg></span>
                <span class="menu-title">${it.name}</span>
              </a>
            </div>`;
  }
  return html;
}
var adminFooterScripts = () => jsScripts([
  "/assets/common/js/_.js",
  "/assets/admin/js/_admin.js",
  "/assets/admin/js/_material.js",
  "/assets/static/codemirror/lib/codemirror.js",
  "/assets/static/codemirror/mode/markdown/markdown.js",
  "/assets/common/js/util/dict.js",
  "/assets/common/js/jquery.min.js",
  "/assets/common/js/toastr.min.js",
  "/assets/common/js/component/loading.js",
  "/assets/common/js/util.js",
  "/assets/common/js/layer/layer.js",
  "/assets/common/js/jquery.pjax.min.js",
  "/assets/common/js/jquery.qrcode.min.js",
  "/assets/common/js/format.js",
  "/assets/common/js/message.js",
  "/assets/common/js/component.js",
  "/assets/common/js/layui/layui.js",
  "/assets/common/js/jquery.treegrid.min.js",
  "/assets/common/js/bootstrap/bootstrap.bundle.min.js",
  "/assets/common/js/table/bootstrap-table.min.js",
  "/assets/common/js/table/bootstrap-table-treegrid.min.js",
  "/assets/common/js/component/form.js",
  "/assets/common/js/component/search.js",
  "/assets/common/js/component/xm-select.js",
  "/assets/common/js/component/tree.select.js",
  "/assets/common/js/component/authtree.js",
  "/assets/common/js/component/table.js",
  "/assets/common/js/component/select2.min.js",
  "/assets/common/js/cache.js",
  "/assets/common/js/editor/editor.js",
  "/assets/common/js/editor/code/code.js",
  "/assets/common/js/component/decimal.js",
  "/assets/admin/js/dict.js",
  "/assets/admin/js/menu.js",
  "/assets/admin/controller/global.js",
  "/assets/admin/js/material.js"
]);
function renderAdminShell(opts = {}) {
  const { cfg = {}, manage = {}, title = "\u63A7\u5236\u53F0", activePath = "/admin/dashboard/index", toolbar = null } = opts;
  const shopName = cfg.shop_name || "acg-faka";
  const avatar = manage.avatar || "/favicon.ico";
  const nickname = manage.nickname || manage.email || "\u7BA1\u7406\u5458";
  const email = manage.email || "";
  const tb = toolbar && toolbar.length ? `<nav class="md-tabs">${toolbar.map((t) => `<a href="${t.url}" class="md-tab">${t.name}</a>`).join("")}</nav>` : "";
  return `<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <script>(function(){var e=document.documentElement;try{var p=localStorage.getItem('admin-theme')||'auto';var d=p==='auto'?(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'):p;e.setAttribute('data-theme',d);e.setAttribute('data-theme-pref',p);var m=localStorage.getItem('admin-layout-mode')==='desktop'?'desktop':((window.innerWidth||screen.width)<992?'mobile':'desktop');e.setAttribute('data-admin-layout',m);}catch(_){e.setAttribute('data-theme','light');e.setAttribute('data-admin-layout',(window.innerWidth||screen.width)<992?'mobile':'desktop');}})();</script>
    <title>${htmlEscape(title)}-${htmlEscape(shopName)}</title>
    <link rel="shortcut icon" href="/favicon.ico"/>
    ${cssLinks([
    "/assets/admin/css/_admin.css",
    "/assets/common/css/_.css",
    "/assets/static/codemirror/lib/codemirror.css",
    "/assets/common/css/_material.css",
    "/assets/common/fonts/material-icons.css",
    "/assets/admin/css/_material.css",
    "/assets/admin/css/_mobile.css",
    "/assets/admin/css/style.bundle.css",
    "/assets/common/css/font.min.css",
    "/assets/common/js/layui/css/layui.css",
    "/assets/common/css/select2.min.css",
    "/assets/common/css/component.css",
    "/assets/common/css/toastr.min.css",
    "/assets/common/js/table/bootstrap-table.css",
    "/assets/common/js/layer/theme/default/layer.css",
    "/assets/common/css/md-tokens.css",
    "/assets/common/css/md-components.css",
    "/assets/admin/css/material.css",
    "/assets/common/fonts/material-icons.css",
    "/assets/common/css/mdicon.css",
    "/assets/admin/css/mobile.css"
  ])}
    <script src="/assets/common/js/ready.js"></script>
    ${adminVar(cfg)}
</head>
<body id="kt_body"
      class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed aside-enabled aside-fixed"
      style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px;background: url('${htmlEscape(cfg.background_url || "")}') fixed no-repeat;background-size: cover;">
<script>(function(){try{if((!window.matchMedia||matchMedia('(min-width: 992px)').matches)&&localStorage.getItem('admin-aside-minimize')==='on'){document.body.setAttribute('data-kt-aside-minimize','on');}}catch(_){}})();</script>
<div class="d-flex flex-column flex-root">
    <div class="page d-flex flex-row flex-column-fluid">
        <!--begin::Aside-->
        <div id="kt_aside" class="aside aside-light aside-hoverable" data-kt-drawer="true" data-kt-drawer-name="aside"
             data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true"
             data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="start"
             data-kt-drawer-toggle="#kt_aside_mobile_toggle">
            <div class="aside-menu flex-column-fluid">
                <div class="hover-scroll-overlay-y my-5 my-lg-5" id="kt_aside_menu_wrapper"
                     data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto"
                     data-kt-scroll-dependencies="#kt_header" data-kt-scroll-wrappers="#kt_aside_menu" data-kt-scroll-offset="0">
                    <div class="menu menu-column menu-title-gray-800 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500"
                         id="kt_aside_menu" data-kt-menu="true">
                        ${adminMenu(activePath)}
                        <div class="menu-item">
                            <div class="menu-content">
                                <div class="separator mx-1 my-4"></div>
                            </div>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="/admin/authentication/logout">
                                <span class="menu-icon"><svg class="menu-svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M9.4 16.6L4.8 12l4.6-4.6L8 6l-6 6l6 6l1.4-1.4zm5.2 0l4.6-4.6l-4.6-4.6L16 6l6 6l-6 6l-1.4-1.4z"/></svg></span>
                                <span class="menu-title">\u9000\u51FA\u767B\u5F55</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Aside-->
        <!--begin::Wrapper-->
        <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
            <!--begin::Header-->
            <div id="kt_header" style="" class="header align-items-stretch">
                <div class="container-fluid d-flex align-items-stretch justify-content-between">
                    <div class="aside-logo flex-column-auto d-none d-lg-flex" id="kt_aside_logo">
                        <a href="/admin/dashboard/index" class="d-flex align-items-center">
                            <img style="border-radius: 50%;height: 22px;" src="/favicon.ico">
                            <span class="logo fw-bolder ms-2 fs-4" style="color: #919191;">${htmlEscape(shopName)}</span>
                        </a>
                        <div id="kt_aside_toggle" class="btn btn-icon w-auto px-0 btn-active-color-primary aside-toggle"
                             data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
                             data-kt-toggle-name="aside-minimize">
                            <span class="svg-icon svg-icon-1 aside-toggle-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.59 18L19 16.59L14.42 12L19 7.41L17.59 6l-6 6z"/><path d="M11 18l1.41-1.41L7.83 12l4.58-4.59L11 6l-6 6z"/></svg></span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center d-lg-none ms-n3 me-1" title="Show aside menu">
                        <div class="btn btn-icon btn-active-light-primary w-30px h-30px w-md-40px h-md-40px" id="kt_aside_mobile_toggle">
                            <span class="svg-icon svg-icon-2x">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M21 7H3C2.4 7 2 6.6 2 6V4C2 3.4 2.4 3 3 3H21C21.6 3 22 3.4 22 4V6C22 6.6 21.6 7 21 7Z" fill="black"/>
                                    <path opacity="0.3" d="M21 14H3C2.4 14 2 13.6 2 13V11C2 10.4 2.4 10 3 10H21C21.6 10 22 10.4 22 11V13C22 13.6 21.6 14 21 14ZM22 20V18C22 17.4 21.6 17 21 17H3C2.4 17 2 17.4 2 18V20C2 20.6 2.4 21 3 21H21C21.6 21 22 20.6 22 20Z" fill="black"/>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1">
                        <div class="d-flex align-items-stretch" id="kt_header_nav"></div>
                        <div class="d-flex align-items-stretch flex-shrink-0">
                            <div class="d-flex align-items-stretch flex-shrink-0">
                                <div class="d-flex align-items-center ms-1 ms-lg-3">
                                    <div class="md-theme-switch">
                                        <button type="button" id="md-theme-toggle" class="btn btn-icon w-30px h-30px w-md-40px h-md-40px" title="\u4E3B\u9898" aria-label="\u5207\u6362\u4E3B\u9898">
                                            <i class="md-ico md-ico-light fa-duotone fa-regular fa-sun-bright fs-2"></i>
                                            <i class="md-ico md-ico-dark fa-duotone fa-regular fa-moon-stars fs-2"></i>
                                            <i class="md-ico md-ico-auto fa-duotone fa-regular fa-circle-half-stroke fs-2"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center ms-1 ms-lg-3">
                                    <a href="/admin/manage/set">
                                        <div class="cursor-pointer symbol symbol-30px symbol-md-40px">
                                            <img src="${htmlEscape(avatar)}" alt="user"/>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Header-->
            <div id="pjax-container">
                <!--begin::Content-->
                <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
                    <!--begin::Toolbar-->
                    <div class="toolbar md-page-header" id="kt_toolbar">
                        <div id="kt_toolbar_container" class="container-fluid">
                            <h1 class="md-page-title">${htmlEscape(title)}</h1>
                            ${tb}
                        </div>
                    </div>
                    <!--end::Toolbar-->
                    <div class="post d-flex flex-column-fluid">
                        <div id="kt_content_container" class="container-fluid">
                            ${opts.body || ""}
                        </div>
                    </div>
                </div>
                <!--end::Content-->
            </div>
        </div>
        <!--end::Wrapper-->
    </div>
</div>
<div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true"><i class="fa-duotone fa-regular fa-arrow-up text-white"></i></div>
${adminFooterScripts()}
</body>
</html>`;
}
function renderAdminDashboardPage(cfg, manage) {
  const body = `
<script src="/assets/static/echarts.min.js"></script>
<div class="dash">
  <div class="dash__grid">
    <aside class="dash__side">
      <section class="dash-card dash-news" aria-labelledby="dash-news-title">
        <header class="dash-card__head">
          <h2 class="dash-card__title dash-news__title" id="dash-news-title"><span class="material-icons-outlined" aria-hidden="true">campaign</span>\u5B98\u65B9\u516C\u544A</h2>
          <button type="button" class="dash-news__toggle" aria-expanded="true" aria-controls="dash-news-list" aria-label="\u6536\u8D77\u5B98\u65B9\u516C\u544A">
            <span class="material-icons-outlined" aria-hidden="true">expand_less</span>
          </button>
        </header>
        <div class="dash-news__list" id="dash-news-list" data-dash-news>
          <span class="dash-news__item"><span class="dash-skel dash-skel--line"></span></span>
        </div>
      </section>

      <section class="dash-card dash-account" aria-label="\u767B\u5F55\u4FE1\u606F">
        <div class="dash-account__who">
          <img src="${htmlEscape(manage.avatar || "/favicon.ico")}" alt="" class="dash-account__avatar">
          <div class="dash-account__id">
            <strong class="dash-account__name">${htmlEscape(manage.nickname || manage.email || "\u7BA1\u7406\u5458")}</strong>
            <span class="dash-account__email">${htmlEscape(manage.email || "")}</span>
          </div>
        </div>
        <dl class="dash-account__list">
          <div class="dash-account__row">
            <dt>\u672C\u6B21\u767B\u5F55 IP</dt>
            <dd class="dash-num">${htmlEscape(manage.login_ip || "-")}</dd>
          </div>
          <div class="dash-account__row">
            <dt>\u4E0A\u6B21\u767B\u5F55</dt>
            <dd class="dash-num">${htmlEscape(manage.last_login_ip || "\u6682\u65E0\u8BB0\u5F55")}${manage.last_login_time ? "<small>" + htmlEscape(manage.last_login_time) + "</small>" : ""}</dd>
          </div>
        </dl>
      </section>
    </aside>
    <div class="dash__main">
      <section class="dash-card dash-earn" aria-label="\u5229\u6DA6">
        <div class="dash-feedback" data-dash-feedback="overview" role="status" aria-live="polite" hidden></div>
        <div class="dash-earn__grid" data-dash-earn-grid aria-busy="true">
          <div class="dash-earn__item dash-earn__item--today" data-dash-earn="today">
            <div class="dash-earn__head"><span class="dash-earn__label">\u4ECA\u65E5\u5229\u6DA6</span></div>
            <strong class="dash-earn__value dash-num" data-dash-value><span class="dash-skel dash-skel--value"></span></strong>
            <span class="dash-earn__delta" data-dash-delta><span class="dash-skel"></span></span>
            <span class="dash-earn__meta" data-dash-meta></span>
          </div>
          <div class="dash-earn__item" data-dash-earn="yesterday">
            <div class="dash-earn__head"><span class="dash-earn__label">\u6628\u65E5\u5229\u6DA6</span></div>
            <strong class="dash-earn__value dash-num" data-dash-value><span class="dash-skel dash-skel--value"></span></strong>
            <span class="dash-earn__delta" data-dash-delta><span class="dash-skel"></span></span>
            <span class="dash-earn__meta" data-dash-meta></span>
          </div>
          <div class="dash-earn__item" data-dash-earn="month">
            <div class="dash-earn__head">
              <span class="dash-earn__label">\u672C\u6708\u5229\u6DA6</span>
              <span class="dash-earn__aside" data-dash-last-month hidden></span>
            </div>
            <strong class="dash-earn__value dash-num" data-dash-value><span class="dash-skel dash-skel--value"></span></strong>
            <span class="dash-earn__delta" data-dash-delta><span class="dash-skel"></span></span>
            <span class="dash-earn__meta" data-dash-meta></span>
          </div>
        </div>
      </section>

      <section class="dash-card dash-todo" aria-labelledby="dash-todo-title">
        <header class="dash-card__head">
          <h2 class="dash-card__title" id="dash-todo-title">\u5F85\u5904\u7406</h2>
        </header>
        <ul class="dash-todo__list" data-dash-todo aria-busy="true">
          <li class="dash-todo__item"><span class="dash-todo__row"><span class="dash-skel dash-skel--line"></span></span></li>
        </ul>
      </section>

      <section class="dash-card dash-trend" aria-labelledby="dash-trend-title">
        <header class="dash-card__head">
          <div class="dash-card__heading">
            <h2 class="dash-card__title" id="dash-trend-title">\u8D8B\u52BF</h2>
            <span class="dash-card__caption dash-num" data-trend-caption></span>
          </div>
          <div class="dash-seg" role="group" aria-label="\u65F6\u95F4\u8303\u56F4">
            <button type="button" class="dash-seg__btn is-active" data-trend-days="7" aria-pressed="true">7 \u5929</button>
            <button type="button" class="dash-seg__btn" data-trend-days="30" aria-pressed="false">30 \u5929</button>
          </div>
        </header>
        <div class="dash-feedback" data-dash-feedback="trend" role="status" aria-live="polite" hidden></div>
        <div class="dash-trend__tabs" role="tablist" aria-label="\u6307\u6807">
          <button type="button" role="tab" class="dash-trend__tab is-active" data-trend-metric="profit" aria-selected="true">
            <span class="dash-trend__tab-label">\u5229\u6DA6</span>
            <span class="dash-trend__tab-value dash-num" data-trend-value="profit"><span class="dash-skel"></span></span>
          </button>
          <button type="button" role="tab" class="dash-trend__tab" data-trend-metric="turnover" aria-selected="false" tabindex="-1">
            <span class="dash-trend__tab-label">\u6210\u4EA4\u989D</span>
            <span class="dash-trend__tab-value dash-num" data-trend-value="turnover"><span class="dash-skel"></span></span>
          </button>
          <button type="button" role="tab" class="dash-trend__tab" data-trend-metric="orders" aria-selected="false" tabindex="-1">
            <span class="dash-trend__tab-label">\u8BA2\u5355</span>
            <span class="dash-trend__tab-value dash-num" data-trend-value="orders"><span class="dash-skel"></span></span>
          </button>
          <button type="button" role="tab" class="dash-trend__tab" data-trend-metric="recharge" aria-selected="false" tabindex="-1">
            <span class="dash-trend__tab-label">\u5145\u503C</span>
            <span class="dash-trend__tab-value dash-num" data-trend-value="recharge"><span class="dash-skel"></span></span>
          </button>
        </div>
        <div class="dash-trend__stage">
          <div class="dash-trend__chart" data-trend-chart data-chart aria-hidden="true"></div>
          <p class="dash-trend__empty" data-trend-empty hidden>\u6682\u65E0\u6570\u636E</p>
        </div>
      </section>

      <section class="dash-card dash-data" aria-labelledby="dash-data-title">
        <header class="dash-card__head">
          <div class="dash-card__heading">
            <h2 class="dash-card__title" id="dash-data-title">\u7ECF\u8425\u6570\u636E</h2>
            <span class="dash-card__caption dash-num" data-dash-range></span>
          </div>
          <div class="dash-seg" role="tablist" aria-label="\u7EDF\u8BA1\u5468\u671F" data-dash-periods>
            <button type="button" role="tab" class="dash-seg__btn is-active" data-period="0" aria-selected="true">\u4ECA\u65E5</button>
            <button type="button" role="tab" class="dash-seg__btn" data-period="1" aria-selected="false" tabindex="-1">\u6628\u65E5</button>
            <button type="button" role="tab" class="dash-seg__btn" data-period="2" aria-selected="false" tabindex="-1">\u672C\u5468</button>
            <button type="button" role="tab" class="dash-seg__btn" data-period="3" aria-selected="false" tabindex="-1">\u672C\u6708</button>
            <button type="button" role="tab" class="dash-seg__btn" data-period="4" aria-selected="false" tabindex="-1">\u5168\u90E8</button>
          </div>
        </header>
        <div class="dash-feedback" data-dash-feedback="data" role="status" aria-live="polite" hidden></div>
        <div class="dash-data__body" data-dash-detail aria-busy="true">
          <div class="dash-kpis">
            <div class="dash-kpi">
              <span class="dash-kpi__label">\u6210\u4EA4\u989D</span>
              <strong class="dash-kpi__value dash-num" data-kpi="turnover"><span class="dash-skel dash-skel--kpi"></span></strong>
              <span class="dash-kpi__sub" data-kpi-sub="turnover"></span>
            </div>
            <div class="dash-kpi">
              <span class="dash-kpi__label">\u5229\u6DA6</span>
              <strong class="dash-kpi__value dash-num" data-kpi="profit"><span class="dash-skel dash-skel--kpi"></span></strong>
              <span class="dash-kpi__sub" data-kpi-sub="profit"></span>
            </div>
            <div class="dash-kpi">
              <span class="dash-kpi__label">\u6210\u4EA4\u8BA2\u5355</span>
              <strong class="dash-kpi__value dash-num" data-kpi="orders"><span class="dash-skel dash-skel--kpi"></span></strong>
              <span class="dash-kpi__sub" data-kpi-sub="orders"></span>
            </div>
            <div class="dash-kpi">
              <span class="dash-kpi__label">\u5BA2\u5355\u4EF7</span>
              <strong class="dash-kpi__value dash-num" data-kpi="avg"><span class="dash-skel dash-skel--kpi"></span></strong>
              <span class="dash-kpi__sub" data-kpi-sub="avg"></span>
            </div>
          </div>

          <div class="dash-panels">
            <section class="dash-panel" aria-labelledby="dash-flow-title">
              <h3 class="dash-panel__title" id="dash-flow-title">\u5229\u6DA6\u6784\u6210</h3>
              <table class="dash-bars dash-bars--flow">
                <tbody>
                  <tr class="dash-bars__row" data-flow="whole" data-flow-field="turnover">
                    <th scope="row" class="dash-bars__name">\u6210\u4EA4\u989D</th>
                    <td class="dash-bars__amount dash-num" data-flow-amount>\u2013</td>
                    <td class="dash-bars__bar" aria-hidden="true"><span class="dash-bars__track"><span class="dash-bars__fill" data-flow-fill></span></span></td>
                    <td class="dash-bars__pct dash-num" data-flow-pct></td>
                  </tr>
                  <tr class="dash-bars__row" data-flow="deduct" data-flow-field="pay_cost">
                    <th scope="row" class="dash-bars__name">\u652F\u4ED8\u624B\u7EED\u8D39</th>
                    <td class="dash-bars__amount dash-num" data-flow-amount>\u2013</td>
                    <td class="dash-bars__bar" aria-hidden="true"><span class="dash-bars__track"><span class="dash-bars__fill" data-flow-fill></span></span></td>
                    <td class="dash-bars__pct dash-num" data-flow-pct></td>
                  </tr>
                  <tr class="dash-bars__row" data-flow="deduct" data-flow-field="rent">
                    <th scope="row" class="dash-bars__name">\u6210\u672C</th>
                    <td class="dash-bars__amount dash-num" data-flow-amount>\u2013</td>
                    <td class="dash-bars__bar" aria-hidden="true"><span class="dash-bars__track"><span class="dash-bars__fill" data-flow-fill></span></span></td>
                    <td class="dash-bars__pct dash-num" data-flow-pct></td>
                  </tr>
                  <tr class="dash-bars__row" data-flow="deduct" data-flow-field="rebate_merchant">
                    <th scope="row" class="dash-bars__name">\u5546\u6237\u5206\u6210</th>
                    <td class="dash-bars__amount dash-num" data-flow-amount>\u2013</td>
                    <td class="dash-bars__bar" aria-hidden="true"><span class="dash-bars__track"><span class="dash-bars__fill" data-flow-fill></span></span></td>
                    <td class="dash-bars__pct dash-num" data-flow-pct></td>
                  </tr>
                  <tr class="dash-bars__row" data-flow="deduct" data-flow-field="rebate_substation">
                    <th scope="row" class="dash-bars__name">\u5206\u7AD9\u5206\u6210</th>
                    <td class="dash-bars__amount dash-num" data-flow-amount>\u2013</td>
                    <td class="dash-bars__bar" aria-hidden="true"><span class="dash-bars__track"><span class="dash-bars__fill" data-flow-fill></span></span></td>
                    <td class="dash-bars__pct dash-num" data-flow-pct></td>
                  </tr>
                  <tr class="dash-bars__row" data-flow="deduct" data-flow-field="divide_amount">
                    <th scope="row" class="dash-bars__name">\u63A8\u5E7F\u4F63\u91D1</th>
                    <td class="dash-bars__amount dash-num" data-flow-amount>\u2013</td>
                    <td class="dash-bars__bar" aria-hidden="true"><span class="dash-bars__track"><span class="dash-bars__fill" data-flow-fill></span></span></td>
                    <td class="dash-bars__pct dash-num" data-flow-pct></td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr class="dash-bars__row" data-flow="profit" data-flow-field="profit">
                    <th scope="row" class="dash-bars__name"><span data-dash-result-label>\u5229\u6DA6</span></th>
                    <td class="dash-bars__amount dash-num" data-flow-amount>\u2013</td>
                    <td class="dash-bars__bar" aria-hidden="true"><span class="dash-bars__track"><span class="dash-bars__fill" data-flow-fill></span></span></td>
                    <td class="dash-bars__pct dash-num" data-flow-pct></td>
                  </tr>
                </tfoot>
              </table>
              <p class="dash-panel__note" data-dash-commission hidden></p>
            </section>

            <div class="dash-panel-stack">
              <section class="dash-panel" aria-labelledby="dash-pay-title" data-dash-channels>
                <h3 class="dash-panel__title" id="dash-pay-title">\u652F\u4ED8\u901A\u9053</h3>
                <table class="dash-bars dash-bars--channels" data-dash-channel-table></table>
                <p class="dash-panel__empty" data-dash-channel-empty hidden>\u6682\u65E0\u6536\u6B3E</p>
              </section>

              <section class="dash-panel" aria-labelledby="dash-minis-title">
                <h3 class="dash-panel__title" id="dash-minis-title">\u4F1A\u5458\u4E0E\u63D0\u73B0</h3>
                <dl class="dash-minis">
                  <div class="dash-mini"><dt>\u65B0\u589E\u4F1A\u5458</dt><dd class="dash-num" data-dash-field="user_register_num" data-dash-kind="count">\u2013</dd></div>
                  <div class="dash-mini"><dt>\u65B0\u5F00\u5206\u7AD9</dt><dd class="dash-num" data-dash-field="business" data-dash-kind="count">\u2013</dd></div>
                  <div class="dash-mini"><dt>\u4F1A\u5458\u5145\u503C</dt><dd class="dash-num" data-dash-field="recharge_amount">\u2013</dd></div>
                  <div class="dash-mini"><dt>\u63D0\u73B0\u6253\u6B3E</dt><dd class="dash-num" data-dash-field="cash_paid_out">\u2013</dd></div>
                  <div class="dash-mini"><dt>\u5151\u73B0\u5230\u4F59\u989D</dt><dd class="dash-num" data-dash-field="cash_to_balance">\u2013</dd></div>
                  <div class="dash-mini"><dt>\u63D0\u73B0\u624B\u7EED\u8D39</dt><dd class="dash-num" data-dash-field="cash_fee_income">\u2013</dd></div>
                </dl>
              </section>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>
<script>ready("/assets/admin/controller/dashboard/index.js");</script>`;
  return renderAdminShell({ cfg, manage, title: "\u63A7\u5236\u53F0", activePath: "/admin/dashboard/index", body });
}
function renderAdminOrderPage(cfg, manage) {
  const body = `
<div class="card mb-5 mb-xl-8">
  <div class="card-header border-0">
    <div class="card-toolbar d-flex flex-wrap">
      <button class="btn btn-sm btn-light-primary order-export me-3"><i class="fa-duotone fa-regular fa-file-export"></i> \u5BFC\u51FA\u8BA2\u5355</button>
      <button class="btn btn-sm btn-light-danger order-clear me-3"><i class="fa-duotone fa-regular fa-trash-can"></i> \u6E05\u7406\u672A\u652F\u4ED8</button>
      <span class="align-self-center text-muted me-3 order-stat">\u5171 <b class="order_count">0</b> \u6761\uFF0C\u9500\u552E\u989D <b class="order_amount">\uFFE50.00</b>\uFF0C\u6210\u672C <b class="order_cost">\uFFE50.00</b></span>
    </div>
  </div>
  <div class="card-body py-3">
    <div class="row g-2 mb-3">
      <div class="col-md-3"><input class="form-control order-f-trade" placeholder="\u8BA2\u5355\u53F7"></div>
      <div class="col-md-2"><input class="form-control order-f-owner" placeholder="\u4F1A\u5458ID\uFF0C0=\u8BBF\u5BA2" inputmode="numeric"></div>
      <div class="col-md-2"><select class="form-select order-f-status"><option value="">\u5168\u90E8\u652F\u4ED8\u72B6\u6001</option><option value="0">\u672A\u652F\u4ED8</option><option value="1">\u5DF2\u652F\u4ED8</option></select></div>
      <div class="col-md-2"><select class="form-select order-f-delivery"><option value="">\u5168\u90E8\u53D1\u8D27\u72B6\u6001</option><option value="0">\u672A\u53D1\u8D27</option><option value="1">\u5DF2\u53D1\u8D27</option></select></div>
      <div class="col-md-3"><input class="form-control order-f-secret" placeholder="\u5361\u5BC6\u4FE1\u606F(\u6A21\u7CCA)"></div>
    </div>
    <div class="table-responsive">
      <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3" id="order-table">
        <thead><tr class="fw-bold text-muted">
          <th style="width:40px"><input type="checkbox" class="crud-check-all"></th>
          <th>\u8BA2\u5355\u53F7/\u4E0B\u5355\u65F6\u95F4</th><th>\u5BA2\u6237</th><th>\u5546\u54C1</th><th>\u6570\u91CF/\u91D1\u989D</th><th>\u53D1\u8D27\u65B9\u5F0F</th><th>\u652F\u4ED8</th><th>\u72B6\u6001</th><th>\u64CD\u4F5C</th>
        </tr></thead>
        <tbody></tbody>
      </table>
      <div class="d-flex justify-content-end align-items-center mt-3">
        <button class="btn btn-sm btn-secondary crud-prev me-2">\u4E0A\u4E00\u9875</button>
        <span class="crud-pageinfo me-2"></span>
        <button class="btn btn-sm btn-secondary crud-next">\u4E0B\u4E00\u9875</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" tabindex="-1" id="orderDeliverModal"><div class="modal-dialog modal-lg"><div class="modal-content">
  <div class="modal-header py-3"><h5 class="modal-title">\u624B\u52A8\u53D1\u8D27</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <form class="modal-form"><div class="modal-body">
    <div class="alert alert-warning mb-3 order-deliver-warn" hidden>\u6B64\u8BA2\u5355\u5DF2\u6709\u53D1\u8D27\u8BB0\u5F55\uFF0C\u672C\u6B21\u63D0\u4EA4\u4F1A\u8986\u76D6\u73B0\u6709\u53D1\u8D27\u5185\u5BB9\u3002</div>
    <div class="mb-3"><label class="form-label">\u53D1\u8D27\u5185\u5BB9</label>
      <textarea class="form-control" name="secret" rows="8" required placeholder="\u586B\u5199\u8981\u53D1\u8D27\u7684\u4FE1\u606F"></textarea></div>
  </div><div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">\u53D6\u6D88</button>
    <button type="submit" class="btn btn-primary">\u6838\u5BF9\u5E76\u53D1\u8D27</button>
  </div></form>
</div></div></div>
<div class="modal fade" tabindex="-1" id="orderExportModal"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header py-3"><h5 class="modal-title">\u5BFC\u51FA\u8BA2\u5355</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <form class="export-form"><div class="modal-body">
    <div class="alert alert-warning mb-3">\u7CFB\u7EDF\u4F1A\u5148\u901A\u8FC7 POST \u7CBE\u786E\u9884\u89C8\u5F53\u524D\u7B5B\u9009\u8303\u56F4\uFF0C\u518D\u751F\u6210\u6587\u4EF6\u3002\u5355\u6B21\u6700\u591A 5000 \u7B14\uFF1B\u9009\u62E9\u201C\u6C38\u4E45\u5220\u9664\u201D\u540E\u8FD8\u5FC5\u987B\u5B8C\u6210\u9AD8\u5371\u786E\u8BA4\u3002</div>
    <div class="mb-3"><label class="form-label">\u5BFC\u51FA\u6570\u91CF <span class="text-muted">(0 \u6216\u7559\u7A7A\u8868\u793A\u5168\u90E8\uFF0C\u6700\u591A 5000 \u7B14)</span></label>
      <input class="form-control" name="export_num" type="number" min="0" max="5000" value="0"></div>
    <div class="mb-3"><label class="form-label">\u5BFC\u51FA\u540E\u6267\u884C</label>
      <select class="form-select" name="export_status"><option value="0">\u4E0D\u6267\u884C\u4EFB\u4F55\u64CD\u4F5C</option><option value="1">\u5220\u9664\u5BFC\u51FA\u7684\u8BA2\u5355\uFF08\u9AD8\u5371/\u7269\u7406\u5220\u9664\uFF09</option></select></div>
    <div class="order-export-preview mt-2"></div>
  </div><div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">\u53D6\u6D88</button>
    <button type="submit" class="btn btn-primary">\u9884\u89C8\u5BFC\u51FA\u8303\u56F4</button>
  </div></form>
</div></div></div>`;
  const js = `
  ready(() => {
    const tbody = document.getElementById('order-table').querySelector('tbody');
    const API = '/admin/api/order/';
    let page = 1, pageSize = 10, deliverRow = null;
    const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    const sym = '\uFFE5';
    const filters = () => {
      const d = { page, limit: pageSize };
      const t = document.querySelector('.order-f-trade').value.trim();
      const o = document.querySelector('.order-f-owner').value.trim();
      const s = document.querySelector('.order-f-status').value;
      const dv = document.querySelector('.order-f-delivery').value;
      const sec = document.querySelector('.order-f-secret').value.trim();
      if (t) d['equal-trade_no'] = t;
      if (o) d['equal-owner'] = o;
      if (s !== '') d['equal-status'] = s;
      if (dv !== '') d['equal-delivery_status'] = dv;
      if (sec) d['search-secret'] = sec;
      return d;
    };
    function load() {
      util.post({ url: API + 'data', data: filters(), loader: false,
        done: res => {
          tbody.innerHTML = '';
          (res.data.list || []).forEach(o => {
            const p = o.pay || {};
            const c = o.commodity || {};
            const u = o.owner || o.substation_user || {};
            const amount = Number(o.amount || 0);
            const statusTxt = Number(o.status) === 1 ? '<span class="badge badge-light-success">\u5DF2\u652F\u4ED8</span>' : '<span class="badge badge-light-secondary">\u672A\u652F\u4ED8</span>';
            const dlvTxt = Number(o.delivery_status) === 1 ? '<span class="badge badge-light-success">\u5DF2\u53D1\u8D27</span>' : '<span class="badge badge-light-warning">\u672A\u53D1\u8D27</span>';
            const dlvWay = Number(c.delivery_way) === 1 ? '\u624B\u52A8' : '\u81EA\u52A8';
            let ops = '';
            if (Number(c.delivery_way) === 0 && Number(o.delivery_status) === 1) {
              ops += '<button class="btn btn-sm btn-light-primary row-secret me-1">\u67E5\u770B\u5361\u5BC6</button>';
            }
            if (Number(c.delivery_way) === 1 && Number(o.status) === 1) {
              ops += '<button class="btn btn-sm btn-light-success row-deliver me-1">\u624B\u52A8\u53D1\u8D27</button>';
            }
            const tr = document.createElement('tr');
            tr.dataset.id = o.id; tr.dataset.tradeNo = o.trade_no; tr.dataset.ownerName = (u.username || '-'); tr.dataset.contact = o.contact || '';
            tr.innerHTML = '<td><input type="checkbox" class="crud-check"></td>' +
              '<td><div>' + esc(o.trade_no) + '</div><small class="text-muted">' + (o.create_time ? new Date(o.create_time * 1000).toLocaleString() : '-') + '</small></td>' +
              '<td>' + (o.owner_id ? esc(u.username || ('#'+o.owner_id)) : esc(o.contact || '\u6E38\u5BA2')) + '</td>' +
              '<td>' + esc(c.name || ('#'+o.commodity_id)) + '</td>' +
              '<td>' + (o.card_num || 1) + ' / ' + sym + amount.toFixed(2) + '</td>' +
              '<td>' + dlvWay + '</td>' +
              '<td>' + esc(p.name || (o.pay_id ? ('#'+o.pay_id) : '-')) + '</td>' +
              '<td>' + statusTxt + ' ' + dlvTxt + '</td>' +
              '<td>' + ops + '</td>';
            tbody.appendChild(tr);
          });
          document.querySelector('.order_count').textContent = res.data.count || 0;
          document.querySelector('.order_amount').textContent = sym + Number(res.data.order_amount || 0).toFixed(2);
          document.querySelector('.order_cost').textContent = sym + Number(res.data.order_cost || 0).toFixed(2);
          document.querySelector('.crud-pageinfo').textContent = '\u7B2C ' + page + ' \u9875 / \u5171 ' + (res.data.count || 0) + ' \u6761';
        },
        error: res => message.error(res.msg) });
    }
    document.querySelector('.crud-check-all').addEventListener('change', e => tbody.querySelectorAll('.crud-check').forEach(x => x.checked = e.target.checked));
    ['.order-f-trade','.order-f-owner','.order-f-status','.order-f-delivery','.order-f-secret'].forEach(sel => {
      document.querySelector(sel).addEventListener('change', () => { page = 1; load(); });
      document.querySelector(sel).addEventListener('input', () => { page = 1; load(); });
    });
    document.querySelector('.crud-prev').addEventListener('click', () => { if (page > 1) { page--; load(); } });
    document.querySelector('.crud-next').addEventListener('click', () => { page++; load(); });

    // \u624B\u52A8\u53D1\u8D27
    tbody.addEventListener('click', e => {
      const tr = e.target.closest('tr'); if (!tr) return;
      if (e.target.closest('.row-secret')) {
        const sec = tr.querySelector('small[data-secret]');
        const txt = sec ? sec.dataset.secret : '';
        if (!txt) { return util.post({ url: API + 'data', data: { page:1, limit:1, 'equal-trade_no': tr.dataset.tradeNo }, loader: false, done: r => { const row = (r.data.list||[])[0]; if (row) window.prompt('\u5361\u5BC6\u5185\u5BB9', row.secret); }, error: () => {} }); }
        window.prompt('\u5361\u5BC6\u5185\u5BB9', txt);
        return;
      }
      if (e.target.closest('.row-deliver')) {
        deliverRow = tr;
        const hasSecret = Number(tr.dataset.hasSecret) === 1;
        document.querySelector('.order-deliver-warn').hidden = !hasSecret;
        (window.bootstrap && bootstrap.Modal.getOrCreateInstance(document.getElementById('orderDeliverModal'))).show();
      }
    });
    document.querySelector('#orderDeliverModal .modal-form').addEventListener('submit', e => {
      e.preventDefault();
      if (!deliverRow) return;
      const id = deliverRow.dataset.id;
      const secret = document.querySelector('#orderDeliverModal textarea[name=secret]').value;
      util.post({ url: API + 'save', data: { id, secret, overwrite_confirmed: document.querySelector('.order-deliver-warn').hidden ? 0 : 1 },
        done: res => { (window.bootstrap && bootstrap.Modal.getInstance(document.getElementById('orderDeliverModal'))).hide(); message.alert(res.msg || '\u8BA2\u5355\u53D1\u8D27\u4FE1\u606F\u5DF2\u4FDD\u5B58\u3002', 'success'); load(); },
        error: res => message.error(res.msg) });
    });

    // \u6E05\u7406\u672A\u652F\u4ED8
    document.querySelector('.order-clear').addEventListener('click', () => {
      if (!confirm('\u53EA\u4F1A\u7269\u7406\u5220\u9664 30 \u5206\u949F\u524D\u4ECD\u672A\u652F\u4ED8\u7684\u8BA2\u5355\uFF1B\u5DF2\u652F\u4ED8\u8BA2\u5355\u4E0D\u4F1A\u53D7\u5F71\u54CD\u3002\u786E\u8BA4\u6E05\u7406\u5417\uFF1F')) return;
      util.post({ url: API + 'clear', done: res => { message.success(res.msg); load(); }, error: res => message.error(res.msg) });
    });

    // \u5BFC\u51FA\u8BA2\u5355
    let previewToken = '', previewCount = 0, previewData = null, exportFilter = {};
    function downloadExport(deleteConfirmation) {
      const payload = Object.assign({}, exportFilter, { expected_count: previewCount, preview_token: previewToken });
      if (deleteConfirmation) payload.delete_confirmation = deleteConfirmation;
      fetch(API + 'export', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) })
        .then(r => {
          const ct = r.headers.get('content-type') || '';
          if (ct.includes('application/json')) return r.json().then(j => { throw new Error(j.msg || '\u5BFC\u51FA\u5931\u8D25'); });
          return r.blob().then(blob => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; a.download = '\u8BA2\u5355\u5BFC\u51FA-' + previewCount + '-' + new Date().toISOString().slice(0,10) + '.csv';
            document.body.appendChild(a); a.click(); a.remove();
            setTimeout(() => URL.revokeObjectURL(url), 1000);
            message.success('\u5DF2\u5BFC\u51FA ' + previewCount + ' \u7B14\u8BA2\u5355');
            load();
          });
        })
        .catch(err => message.error(err.message));
    }
    document.querySelector('#orderExportModal .export-form').addEventListener('submit', e => {
      e.preventDefault();
      const num = document.querySelector('#orderExportModal input[name=export_num]').value;
      const st = document.querySelector('#orderExportModal select[name=export_status]').value;
      previewToken = ''; previewCount = 0; previewData = null;
      exportFilter = Object.assign({}, filters(), { export_num: num === '' || num === null ? 0 : Number(num), export_status: Number(st) });
      delete exportFilter.page; delete exportFilter.limit;
      util.post({ url: API + 'exportImpact', data: exportFilter, loader: false, done: res => {
        const impact = res.data || {};
        previewToken = impact.preview_token || '';
        previewCount = Number(impact.count || 0);
        document.querySelector('.order-export-preview').innerHTML = '<div class="alert alert-info">\u5171\u547D\u4E2D <b>' + previewCount + '</b> \u7B14\uFF08\u5DF2\u652F\u4ED8 ' + (impact.paid_count||0) + '\u3001\u672A\u652F\u4ED8 ' + (impact.unpaid_count||0) + '\uFF09\u3002</div>';
        if (!previewToken || previewCount < 1) return;
        (window.bootstrap && bootstrap.Modal.getOrCreateInstance(document.getElementById('orderExportModal'))).hide();
        if (Number(st) === 1) {
          const phrase = '\u786E\u8BA4\u6C38\u4E45\u5220\u9664' + previewCount + '\u7B14\u8BA2\u5355';
          if (!confirm('\u4E0B\u8F7D\u8BF7\u6C42\u6210\u529F\u540E\uFF0C\u7CFB\u7EDF\u4F1A\u7269\u7406\u5220\u9664\u4E0A\u8FF0 ' + previewCount + ' \u7B14\u8BA2\u5355\u53CA\u5176\u5386\u53F2\u8BB0\u5F55\uFF0C\u65E0\u6CD5\u6062\u590D\u3002\\n\\n\u8BF7\u8F93\u5165\u786E\u8BA4\u77ED\u8BED\uFF1A' + phrase)) return;
          const typed = window.prompt('\u8BF7\u8F93\u5165\uFF1A' + phrase);
          if (typed !== phrase) { message.error('\u786E\u8BA4\u77ED\u8BED\u4E0D\u5339\u914D\uFF0C\u5DF2\u53D6\u6D88\u5220\u9664'); return; }
          downloadExport(phrase);
        } else {
          if (confirm('\u672C\u6B21\u53EA\u4E0B\u8F7D CSV\uFF0C\u4E0D\u4FEE\u6539\u6216\u5220\u9664\u8BA2\u5355\u3002\u786E\u8BA4\u5BFC\u51FA ' + previewCount + ' \u7B14\uFF1F')) downloadExport('');
        }
      }, error: res => message.error(res.msg) });
    });
    document.querySelector('.order-export').addEventListener('click', () => {
      document.querySelector('.order-export-preview').innerHTML = '';
      (window.bootstrap && bootstrap.Modal.getOrCreateInstance(document.getElementById('orderExportModal'))).show();
    });

    load();
  });`;
  return renderCrudPage({ cfg, manage, title: "\u8BA2\u5355\u7BA1\u7406", activePath: "/admin/order/index", body, readyJs: js });
}
function renderAdminUserPage(cfg, manage) {
  const body = `
<div class="card mb-5 mb-xl-8">
  <div class="card-header border-0">
    <div class="card-toolbar d-flex flex-wrap">
      <button class="btn btn-sm btn-light-danger user-del me-3"><i class="fa-duotone fa-regular fa-trash-can"></i> \u79FB\u9664\u9009\u4E2D</button>
      <span class="align-self-center text-muted me-3 user-stat">\u5171 <b class="user_count">0</b> \u4EBA\uFF0C\u4F59\u989D\u5408\u8BA1 <b class="user_balance">\uFFE50.00</b>\uFF0C\u5145\u503C\u5408\u8BA1 <b class="user_recharge">\uFFE50.00</b></span>
    </div>
  </div>
  <div class="card-body py-3">
    <div class="row g-2 mb-3">
      <div class="col-md-3"><input class="form-control user-f-username" placeholder="\u7528\u6237\u540D(\u6A21\u7CCA)"></div>
      <div class="col-md-2"><input class="form-control user-f-id" placeholder="UID" inputmode="numeric"></div>
      <div class="col-md-2"><input class="form-control user-f-email" placeholder="\u90AE\u7BB1"></div>
      <div class="col-md-2"><input class="form-control user-f-qq" placeholder="QQ\u53F7" inputmode="numeric"></div>
      <div class="col-md-2"><select class="form-select user-f-status"><option value="">\u5168\u90E8\u72B6\u6001</option><option value="1">\u6B63\u5E38</option><option value="0">\u5DF2\u5C01\u7981</option></select></div>
    </div>
    <div class="table-responsive">
      <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3" id="user-table">
        <thead><tr class="fw-bold text-muted">
          <th style="width:40px"><input type="checkbox" class="crud-check-all"></th>
          <th>UID/\u7528\u6237\u540D</th><th>\u4F59\u989D</th><th>\u786C\u5E01</th><th>\u5145\u503C\u7D2F\u8BA1</th><th>\u72B6\u6001</th><th>\u6CE8\u518C\u65F6\u95F4/IP</th><th>\u64CD\u4F5C</th>
        </tr></thead>
        <tbody></tbody>
      </table>
      <div class="d-flex justify-content-end align-items-center mt-3">
        <button class="btn btn-sm btn-secondary crud-prev me-2">\u4E0A\u4E00\u9875</button>
        <span class="crud-pageinfo me-2"></span>
        <button class="btn btn-sm btn-secondary crud-next">\u4E0B\u4E00\u9875</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" tabindex="-1" id="userAdjustModal"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header py-3"><h5 class="modal-title">\u4F59\u989D/\u786C\u5E01\u8C03\u6574</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <form class="adjust-form"><div class="modal-body">
    <div class="mb-3"><label class="form-label">\u64CD\u4F5C</label>
      <select class="form-select" name="action"><option value="1">\u589E\u52A0</option><option value="2">\u6263\u51CF</option></select></div>
    <div class="mb-3"><label class="form-label">\u7C7B\u578B</label>
      <select class="form-select" name="currency"><option value="0">\u4F59\u989D</option><option value="1">\u786C\u5E01</option></select></div>
    <div class="mb-3"><label class="form-label">\u6570\u91CF</label>
      <input class="form-control" name="amount" type="number" step="0.01" min="0.01" required></div>
    <div class="mb-3"><label class="form-label">\u64CD\u4F5C\u539F\u56E0</label>
      <input class="form-control" name="log" required maxlength="64" placeholder="2-64 \u4E2A\u5B57"></div>
    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="total" value="1" id="adj-total">
      <label class="form-check-label" for="adj-total">\u8BA1\u5165\u7D2F\u8BA1\u5145\u503C/\u786C\u5E01 (\u4EC5\u589E\u52A0\u65F6)</label></div>
  </div><div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">\u53D6\u6D88</button>
    <button type="submit" class="btn btn-primary">\u786E\u8BA4\u64CD\u4F5C</button>
  </div></form>
</div></div></div>
<div class="modal fade" tabindex="-1" id="userStatModal"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header py-3"><h5 class="modal-title">\u4F1A\u5458\u4EA4\u6613\u7EDF\u8BA1</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <div class="modal-body user-stat-body py-3"></div>
  <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">\u5173\u95ED</button></div>
</div></div></div>`;
  const js = `
  ready(() => {
    const tbody = document.getElementById('user-table').querySelector('tbody');
    const API = '/admin/api/user/';
    let page = 1, pageSize = 10, adjustId = 0;
    const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    const sym = '\uFFE5';
    const filters = () => {
      const d = { page, limit: pageSize };
      const u = document.querySelector('.user-f-username').value.trim();
      const id = document.querySelector('.user-f-id').value.trim();
      const em = document.querySelector('.user-f-email').value.trim();
      const qq = document.querySelector('.user-f-qq').value.trim();
      const st = document.querySelector('.user-f-status').value;
      if (u) d['search-username'] = u;
      if (id) d['equal-id'] = id;
      if (em) d['equal-email'] = em;
      if (qq) d['equal-qq'] = qq;
      if (st !== '') d['equal-status'] = st;
      return d;
    };
    function load() {
      util.post({ url: API + 'data', data: filters(), loader: false,
        done: res => {
          tbody.innerHTML = '';
          (res.data.list || []).forEach(u => {
            const stTxt = Number(u.status) === 1 ? '<span class="badge badge-light-success">\u6B63\u5E38</span>' : '<span class="badge badge-light-danger">\u5DF2\u5C01\u7981</span>';
            const tr = document.createElement('tr');
            tr.dataset.id = u.id; tr.dataset.name = u.username;
            tr.innerHTML = '<td><input type="checkbox" class="crud-check"></td>' +
              '<td><div>' + u.id + ' / ' + esc(u.username) + '</div><small class="text-muted">' + esc(u.email || '') + '</small></td>' +
              '<td>' + sym + Number(u.balance || 0).toFixed(2) + '</td>' +
              '<td>' + Number(u.coin || 0) + '</td>' +
              '<td>' + sym + Number(u.recharge || 0).toFixed(2) + (Number(u.total_coin) > 0 ? ' / \u5E01' + u.total_coin : '') + '</td>' +
              '<td>' + stTxt + '</td>' +
              '<td><small>' + (u.create_time ? new Date(u.create_time * 1000).toLocaleString() : '-') + '</small><br><small class="text-muted">' + esc(u.login_ip || '') + '</small></td>' +
              '<td><button class="btn btn-sm btn-light-primary row-adjust me-1">\u8C03\u6574</button>' +
              '<button class="btn btn-sm btn-light-info row-stat me-1">\u7EDF\u8BA1</button>' +
              (Number(u.status) === 1 ? '<button class="btn btn-sm btn-light-warning row-ban me-1">\u5C01\u7981</button>' : '<button class="btn btn-sm btn-light-success row-unban me-1">\u89E3\u5C01</button>') +
              '<button class="btn btn-sm btn-light-danger row-del">\u5220\u9664</button></td>';
            tbody.appendChild(tr);
          });
          document.querySelector('.user_count').textContent = res.data.count || 0;
          document.querySelector('.user_balance').textContent = sym + Number(res.data.balance || 0).toFixed(2);
          document.querySelector('.user_recharge').textContent = sym + Number(res.data.recharge || 0).toFixed(2);
          document.querySelector('.crud-pageinfo').textContent = '\u7B2C ' + page + ' \u9875 / \u5171 ' + (res.data.count || 0) + ' \u6761';
        },
        error: res => message.error(res.msg) });
    }
    document.querySelector('.crud-check-all').addEventListener('change', e => tbody.querySelectorAll('.crud-check').forEach(x => x.checked = e.target.checked));
    ['.user-f-username','.user-f-id','.user-f-email','.user-f-qq','.user-f-status'].forEach(sel => {
      document.querySelector(sel).addEventListener('change', () => { page = 1; load(); });
      document.querySelector(sel).addEventListener('input', () => { page = 1; load(); });
    });
    document.querySelector('.crud-prev').addEventListener('click', () => { if (page > 1) { page--; load(); } });
    document.querySelector('.crud-next').addEventListener('click', () => { page++; load(); });

    tbody.addEventListener('click', e => {
      const tr = e.target.closest('tr'); if (!tr) return;
      if (e.target.closest('.row-adjust')) {
        adjustId = tr.dataset.id;
        (window.bootstrap && bootstrap.Modal.getOrCreateInstance(document.getElementById('userAdjustModal'))).show();
      } else if (e.target.closest('.row-stat')) {
        util.get(API + 'statistics?id=' + tr.dataset.id, res => {
          const d = res.data || {};
          document.querySelector('.user-stat-body').innerHTML =
            '<table class="table table-bordered align-middle mb-0"><tbody>' +
            '<tr><td>\u4ECA\u65E5\u4EA4\u6613\u989D</td><td>' + sym + d.today_order_amount + '</td></tr>' +
            '<tr><td>\u6628\u65E5\u4EA4\u6613\u989D</td><td>' + sym + d.yesterday_order_amount + '</td></tr>' +
            '<tr><td>\u672C\u5468\u4EA4\u6613\u989D</td><td>' + sym + d.week_order_amount + '</td></tr>' +
            '<tr><td>\u672C\u6708\u4EA4\u6613\u989D</td><td>' + sym + d.month_order_amount + '</td></tr>' +
            '<tr><td>\u7D2F\u8BA1\u4EA4\u6613\u989D</td><td>' + sym + d.total_order_amount + '</td></tr>' +
            '</tbody></table>';
          (window.bootstrap && bootstrap.Modal.getOrCreateInstance(document.getElementById('userStatModal'))).show();
        });
      } else if (e.target.closest('.row-ban')) {
        if (!confirm('\u786E\u8BA4\u5C01\u7981\u7528\u6237 ' + tr.dataset.name + ' ?')) return;
        util.post({ url: API + 'save', data: { id: tr.dataset.id, status: 0 }, done: load, error: res => message.error(res.msg) });
      } else if (e.target.closest('.row-unban')) {
        if (!confirm('\u786E\u8BA4\u89E3\u5C01\u7528\u6237 ' + tr.dataset.name + ' ?')) return;
        util.post({ url: API + 'save', data: { id: tr.dataset.id, status: 1 }, done: load, error: res => message.error(res.msg) });
      } else if (e.target.closest('.row-del')) {
        if (!confirm('\u786E\u8BA4\u6C38\u4E45\u5220\u9664\u7528\u6237 ' + tr.dataset.name + ' ?\uFF08\u5C06\u540C\u6B65\u6E05\u7406\u5176\u4E0B\u7EA7\u5206\u7AD9\u7B49\u5173\u8054\u6570\u636E\uFF09')) return;
        util.post({ url: API + 'del', data: { list: tr.dataset.id }, done: res => { message.success(res.msg); load(); }, error: res => message.error(res.msg) });
      }
    });
    document.querySelector('#userAdjustModal .adjust-form').addEventListener('submit', e => {
      e.preventDefault();
      if (!adjustId) return;
      const f = new FormData(e.target);
      const data = { id: adjustId, action: Number(f.get('action')), currency: Number(f.get('currency')), amount: f.get('amount'), log: f.get('log') };
      if (f.get('total')) data.total = 1;
      util.post({ url: API + 'recharge', data, done: res => { (window.bootstrap && bootstrap.Modal.getInstance(document.getElementById('userAdjustModal'))).hide(); message.success(res.msg); load(); }, error: res => message.error(res.msg) });
    });
    document.querySelector('.user-del').addEventListener('click', () => {
      const ids = [...tbody.querySelectorAll('.crud-check:checked')].map(x => x.closest('tr').dataset.id);
      if (!ids.length) { message.error('\u8BF7\u81F3\u5C11\u52FE\u9009 1 \u4E2A\u4F1A\u5458\uFF01'); return; }
      if (!confirm('\u786E\u8BA4\u6C38\u4E45\u5220\u9664\u9009\u4E2D\u7684 ' + ids.length + ' \u4E2A\u4F1A\u5458\uFF1F')) return;
      util.post({ url: API + 'del', data: { list: ids.join(',') }, done: res => { message.success(res.msg); load(); }, error: res => message.error(res.msg) });
    });

    load();
  });`;
  return renderCrudPage({ cfg, manage, title: "\u4F1A\u5458\u7BA1\u7406", activePath: "/admin/user/index", body, readyJs: js });
}
function renderAdminRechargePage(cfg, manage) {
  const body = `
<div class="card mb-5 mb-xl-8">
  <div class="card-header border-0">
    <div class="card-toolbar d-flex flex-wrap">
      <button class="btn btn-sm btn-light-danger recharge-clear me-3"><i class="fa-duotone fa-regular fa-trash-can"></i> \u6E05\u7406\u672A\u652F\u4ED8</button>
      <span class="align-self-center text-muted me-3 recharge-stat">\u5171 <b class="recharge_count">0</b> \u5355\uFF0C\u91D1\u989D <b class="recharge_amount">\uFFE50.00</b></span>
    </div>
  </div>
  <div class="card-body py-3">
    <div class="row g-2 mb-3">
      <div class="col-md-3"><input class="form-control rc-f-trade" placeholder="\u8BA2\u5355\u53F7"></div>
      <div class="col-md-2"><input class="form-control rc-f-user" placeholder="\u4F1A\u5458ID" inputmode="numeric"></div>
      <div class="col-md-2"><select class="form-select rc-f-status"><option value="">\u5168\u90E8\u72B6\u6001</option><option value="0">\u672A\u652F\u4ED8</option><option value="1">\u5DF2\u652F\u4ED8</option></select></div>
      <div class="col-md-2"><input class="form-control rc-f-ip" placeholder="IP\u5730\u5740"></div>
    </div>
    <div class="table-responsive">
      <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3" id="recharge-table">
        <thead><tr class="fw-bold text-muted">
          <th>\u8BA2\u5355\u53F7</th><th>\u4F1A\u5458</th><th>\u91D1\u989D</th><th>\u652F\u4ED8</th><th>\u4E0B\u5355\u65F6\u95F4</th><th>IP</th><th>\u72B6\u6001</th><th>\u652F\u4ED8\u65F6\u95F4</th><th>\u64CD\u4F5C</th>
        </tr></thead>
        <tbody></tbody>
      </table>
      <div class="d-flex justify-content-end align-items-center mt-3">
        <button class="btn btn-sm btn-secondary crud-prev me-2">\u4E0A\u4E00\u9875</button>
        <span class="crud-pageinfo me-2"></span>
        <button class="btn btn-sm btn-secondary crud-next">\u4E0B\u4E00\u9875</button>
      </div>
    </div>
  </div>
</div>`;
  const js = `
  ready(() => {
    const tbody = document.getElementById('recharge-table').querySelector('tbody');
    const API = '/admin/api/recharge/';
    let page = 1, pageSize = 10;
    const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    const sym = '\uFFE5';
    const filters = () => {
      const d = { page, limit: pageSize };
      const t = document.querySelector('.rc-f-trade').value.trim();
      const u = document.querySelector('.rc-f-user').value.trim();
      const s = document.querySelector('.rc-f-status').value;
      const ip = document.querySelector('.rc-f-ip').value.trim();
      if (t) d['equal-trade_no'] = t;
      if (u) d['equal-user_id'] = u;
      if (s !== '') d['equal-status'] = s;
      if (ip) d['equal-create_ip'] = ip;
      return d;
    };
    function load() {
      util.post({ url: API + 'data', data: filters(), loader: false,
        done: res => {
          tbody.innerHTML = '';
          (res.data.list || []).forEach(o => {
            const p = o.pay || {};
            const u = o.user || {};
            const stTxt = Number(o.status) === 1 ? '<span class="badge badge-light-success">\u5DF2\u652F\u4ED8</span>' : '<span class="badge badge-light-secondary">\u672A\u652F\u4ED8</span>';
            const tr = document.createElement('tr');
            tr.dataset.id = o.id; tr.dataset.tradeNo = o.trade_no; tr.dataset.userName = (u.username || ('#'+o.user_id)); tr.dataset.amount = o.amount; tr.dataset.payName = (p.name || '-');
            tr.innerHTML = '<td>' + esc(o.trade_no) + '</td>' +
              '<td>' + esc(u.username || ('#'+o.user_id)) + '</td>' +
              '<td>' + sym + Number(o.amount || 0).toFixed(2) + '</td>' +
              '<td>' + esc(p.name || '-') + '</td>' +
              '<td>' + (o.create_time ? new Date(o.create_time * 1000).toLocaleString() : '-') + '</td>' +
              '<td>' + esc(o.create_ip || '') + '</td>' +
              '<td>' + stTxt + '</td>' +
              '<td>' + (o.pay_time ? new Date(o.pay_time * 1000).toLocaleString() : '-') + '</td>' +
              '<td>' + (Number(o.status) === 0 ? '<button class="btn btn-sm btn-light-success row-supplement">\u8865\u5355</button>' : '') + '</td>';
            tbody.appendChild(tr);
          });
          document.querySelector('.recharge_count').textContent = res.data.count || 0;
          document.querySelector('.recharge_amount').textContent = sym + Number(res.data.order_amount || 0).toFixed(2);
          document.querySelector('.crud-pageinfo').textContent = '\u7B2C ' + page + ' \u9875 / \u5171 ' + (res.data.count || 0) + ' \u6761';
        },
        error: res => message.error(res.msg) });
    }
    document.querySelector('.crud-prev').addEventListener('click', () => { if (page > 1) { page--; load(); } });
    document.querySelector('.crud-next').addEventListener('click', () => { page++; load(); });
    ['.rc-f-trade','.rc-f-user','.rc-f-status','.rc-f-ip'].forEach(sel => {
      document.querySelector(sel).addEventListener('change', () => { page = 1; load(); });
      document.querySelector(sel).addEventListener('input', () => { page = 1; load(); });
    });

    tbody.addEventListener('click', e => {
      const tr = e.target.closest('tr'); if (!tr || !e.target.closest('.row-supplement')) return;
      if (!confirm('\u8865\u5355\u4F1A\u628A\u5145\u503C\u8BA2\u5355\u6807\u8BB0\u4E3A\u5DF2\u652F\u4ED8\uFF0C\u5E76\u7ACB\u5373\u589E\u52A0\u4F1A\u5458\u4F59\u989D\u3002\\n\\n\u8BA2\u5355\u53F7\uFF1A' + tr.dataset.tradeNo + '\\n\u4F1A\u5458\uFF1A' + tr.dataset.userName + '\\n\u91D1\u989D\uFF1A' + sym + tr.dataset.amount + '\\n\\n\u8BE5\u64CD\u4F5C\u4F1A\u771F\u5B9E\u5165\u8D26\u4E14\u65E0\u6CD5\u5728\u672C\u9875\u9762\u64A4\u9500\u3002')) return;
      util.post({ url: API + 'success', data: { id: tr.dataset.id }, done: res => { message.success(res.msg); load(); }, error: res => message.error(res.msg) });
    });

    document.querySelector('.recharge-clear').addEventListener('click', () => {
      if (!confirm('\u53EA\u4F1A\u7269\u7406\u5220\u9664 30 \u5206\u949F\u524D\u4ECD\u672A\u652F\u4ED8\u7684\u5145\u503C\u8BA2\u5355\uFF1B\u5DF2\u652F\u4ED8\u8BA2\u5355\u4E0D\u4F1A\u53D7\u5F71\u54CD\u3002\u786E\u8BA4\u6E05\u7406\u5417\uFF1F')) return;
      util.post({ url: API + 'clear', done: res => { message.success(res.msg); load(); }, error: res => message.error(res.msg) });
    });

    load();
  });`;
  return renderCrudPage({ cfg, manage, title: "\u5145\u503C\u8BA2\u5355", activePath: "/admin/recharge/order", body, readyJs: js });
}
function renderAdminCouponPage(cfg, manage) {
  const body = `
<div class="card mb-5 mb-xl-8">
  <div class="card-header border-0">
    <div class="card-toolbar d-flex flex-wrap">
      <button class="btn btn-sm btn-light-primary crud-add me-3"><i class="fa-duotone fa-regular fa-ticket"></i> \u6279\u91CF\u751F\u6210</button>
      <button class="btn btn-sm btn-light-warning coupon-lock me-3"><i class="fa-duotone fa-regular fa-lock"></i> \u9501\u5B9A\u9009\u4E2D</button>
      <button class="btn btn-sm btn-light-info coupon-unlock me-3"><i class="fa-duotone fa-regular fa-unlock"></i> \u89E3\u9501\u9009\u4E2D</button>
      <button class="btn btn-sm btn-light-danger coupon-del me-3"><i class="fa-duotone fa-regular fa-trash-can"></i> \u79FB\u9664\u9009\u4E2D</button>
      <button class="btn btn-sm btn-light-primary coupon-export me-3"><i class="fa-duotone fa-regular fa-file-export"></i> \u5BFC\u51FA\u5238\u7801</button>
    </div>
  </div>
  <div class="card-body py-3">
    <div class="row g-2 mb-3">
      <div class="col-md-2"><input class="form-control cp-f-code" placeholder="\u5238\u7801"></div>
      <div class="col-md-2"><select class="form-select cp-f-status"><option value="">\u5168\u90E8\u72B6\u6001</option><option value="0">\u672A\u4F7F\u7528</option><option value="1">\u5DF2\u4F7F\u7528</option><option value="2">\u5DF2\u9501\u5B9A</option></select></div>
      <div class="col-md-2"><input class="form-control cp-f-money" placeholder="\u5238\u9762\u503C" inputmode="numeric"></div>
      <div class="col-md-2"><input class="form-control cp-f-owner" placeholder="\u4F1A\u5458ID" inputmode="numeric"></div>
      <div class="col-md-2"><input class="form-control cp-f-note" placeholder="\u5907\u6CE8\u4FE1\u606F"></div>
      <div class="col-md-2"><input class="form-control cp-f-commodity" placeholder="\u5546\u54C1ID" inputmode="numeric"></div>
    </div>
    <div class="table-responsive">
      <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3" id="coupon-table">
        <thead><tr class="fw-bold text-muted">
          <th style="width:40px"><input type="checkbox" class="crud-check-all"></th>
          <th>ID</th><th>\u5238\u7801</th><th>\u62B5\u6263\u8303\u56F4</th><th>\u9762\u503C</th><th>\u5269\u4F59/\u5DF2\u7528\u6B21\u6570</th><th>\u72B6\u6001</th><th>\u8FC7\u671F\u65F6\u95F4</th><th>\u751F\u6210\u65F6\u95F4</th><th>\u6700\u540E\u8BA2\u5355\u53F7</th><th>\u5907\u6CE8</th><th>\u64CD\u4F5C</th>
        </tr></thead>
        <tbody></tbody>
      </table>
      <div class="d-flex justify-content-end align-items-center mt-3">
        <button class="btn btn-sm btn-secondary crud-prev me-2">\u4E0A\u4E00\u9875</button>
        <span class="crud-pageinfo me-2"></span>
        <button class="btn btn-sm btn-secondary crud-next">\u4E0B\u4E00\u9875</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" tabindex="-1" id="couponModal"><div class="modal-dialog modal-lg"><div class="modal-content">
  <div class="modal-header py-3"><h5 class="modal-title">\u6279\u91CF\u751F\u6210\u4F18\u60E0\u5238</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <form class="coupon-form"><div class="modal-body">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">\u524D\u7F00 <span class="text-muted">(\u53EF\u9009, 1-16\u4F4D \u6570\u5B57\u5B57\u6BCD_-)</span></label>
        <input class="form-control" name="prefix" maxlength="16" placeholder="\u5982 VIP"></div>
      <div class="col-md-6"><label class="form-label">\u6570\u91CF</label>
        <input class="form-control" name="num" type="number" min="1" max="1000" value="10" required></div>
      <div class="col-md-6"><label class="form-label">\u62B5\u6263\u6A21\u5F0F</label>
        <select class="form-select" name="mode"><option value="0">\u91D1\u989D\u62B5\u6263</option><option value="1">\u767E\u5206\u6BD4\u62B5\u6263(0-1)</option></select></div>
      <div class="col-md-6"><label class="form-label">\u4F18\u60E0\u91D1\u989D / \u6BD4\u4F8B</label>
        <input class="form-control" name="money" type="number" step="0.01" value="1" required></div>
      <div class="col-md-6"><label class="form-label">\u53EF\u7528\u6B21\u6570</label>
        <input class="form-control" name="life" type="number" min="1" max="1000000" value="1" required></div>
      <div class="col-md-6"><label class="form-label">\u8FC7\u671F\u65F6\u95F4 <span class="text-muted">(\u53EF\u9009)</span></label>
        <input class="form-control" name="expire_time" type="datetime-local"></div>
      <div class="col-md-6"><label class="form-label">\u62B5\u6263\u8303\u56F4 <span class="text-muted">(\u5546\u54C1ID \u6216 \u5206\u7C7BID \u9009\u4E00)</span></label>
        <input class="form-control" name="commodity_id" type="number" min="0" value="0"></div>
      <div class="col-md-6"><label class="form-label">\u5546\u54C1\u5206\u7C7BID <span class="text-muted">(0 \u4E0D\u9650)</span></label>
        <input class="form-control" name="category_id" type="number" min="0" value="0"></div>
      <div class="col-md-6"><label class="form-label">\u5546\u54C1\u79CD\u7C7B race</label>
        <input class="form-control" name="race" maxlength="32"></div>
      <div class="col-md-6"><label class="form-label">\u5907\u6CE8</label>
        <input class="form-control" name="note" maxlength="32"></div>
    </div>
    <div class="mt-3"><label class="form-label">\u751F\u6210\u7ED3\u679C</label>
      <textarea class="form-control coupon-result" rows="6" readonly placeholder="\u751F\u6210\u540E\u5238\u7801\u5C06\u663E\u793A\u5728\u6B64"></textarea></div>
  </div><div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">\u5173\u95ED</button>
    <button type="submit" class="btn btn-primary">\u7ACB\u5373\u751F\u6210</button>
  </div></form>
</div></div></div>`;
  const js = `
  ready(() => {
    const tbody = document.getElementById('coupon-table').querySelector('tbody');
    const API = '/admin/api/coupon/';
    let page = 1, pageSize = 10;
    const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    const sym = '\uFFE5';
    const filters = () => {
      const d = { page, limit: pageSize };
      const code = document.querySelector('.cp-f-code').value.trim();
      const st = document.querySelector('.cp-f-status').value;
      const mo = document.querySelector('.cp-f-money').value.trim();
      const ow = document.querySelector('.cp-f-owner').value.trim();
      const note = document.querySelector('.cp-f-note').value.trim();
      const cm = document.querySelector('.cp-f-commodity').value.trim();
      if (code) d['equal-code'] = code;
      if (st !== '') d['equal-status'] = st;
      if (mo) d['equal-money'] = mo;
      if (ow) d['equal-owner'] = ow;
      if (note) d['equal-note'] = note;
      if (cm) d['equal-commodity_id'] = cm;
      return d;
    };
    function load() {
      util.post({ url: API + 'data', data: filters(), loader: false,
        done: res => {
          tbody.innerHTML = '';
          (res.data.list || []).forEach(c => {
            const stTxt = Number(c.status) === 0 ? '<span class="badge badge-light-success">\u672A\u4F7F\u7528</span>'
              : Number(c.status) === 1 ? '<span class="badge badge-light-secondary">\u5DF2\u4F7F\u7528</span>'
              : '<span class="badge badge-light-warning">\u5DF2\u9501\u5B9A</span>';
            const modeTxt = Number(c.mode) === 1 ? (c.money * 100 + '%') : (sym + c.money);
            const scope = c.category ? ('\u5206\u7C7B:' + esc(c.category.name)) : c.commodity ? ('\u5546\u54C1:' + esc(c.commodity.name)) : '\u4E0D\u9650';
            const tr = document.createElement('tr');
            tr.dataset.id = c.id;
            tr.innerHTML = '<td><input type="checkbox" class="crud-check"></td>' +
              '<td>' + c.id + '</td>' +
              '<td><code>' + c.code + '</code></td>' +
              '<td>' + scope + '</td>' +
              '<td>' + modeTxt + '</td>' +
              '<td>' + (Number(c.life) - Number(c.use_life)) + ' / ' + c.use_life + '</td>' +
              '<td>' + stTxt + '</td>' +
              '<td>' + (c.expire_time ? new Date(c.expire_time * 1000).toLocaleString() : '-') + '</td>' +
              '<td>' + (c.create_time ? new Date(c.create_time * 1000).toLocaleString() : '-') + '</td>' +
              '<td>' + esc(c.trade_no || '-') + '</td>' +
              '<td>' + esc(c.note || '-') + '</td>' +
              '<td><button class="btn btn-sm btn-light-danger row-del me-1">\u5220\u9664</button>' +
              (Number(c.status) === 0 ? '<button class="btn btn-sm btn-light-warning row-lock">\u9501\u5B9A</button>'
                : Number(c.status) === 2 ? '<button class="btn btn-sm btn-light-info row-unlock">\u89E3\u9501</button>' : '') +
              '</td>';
            tbody.appendChild(tr);
          });
          document.querySelector('.crud-pageinfo').textContent = '\u7B2C ' + page + ' \u9875 / \u5171 ' + (res.data.count || 0) + ' \u6761';
        },
        error: res => message.error(res.msg) });
    }
    function selected() { return [...tbody.querySelectorAll('.crud-check:checked')].map(x => x.closest('tr').dataset.id); }
    document.querySelector('.crud-check-all').addEventListener('change', e => tbody.querySelectorAll('.crud-check').forEach(x => x.checked = e.target.checked));
    ['.cp-f-code','.cp-f-status','.cp-f-money','.cp-f-owner','.cp-f-note','.cp-f-commodity'].forEach(sel => {
      document.querySelector(sel).addEventListener('change', () => { page = 1; load(); });
      document.querySelector(sel).addEventListener('input', () => { page = 1; load(); });
    });
    document.querySelector('.crud-prev').addEventListener('click', () => { if (page > 1) { page--; load(); } });
    document.querySelector('.crud-next').addEventListener('click', () => { page++; load(); });

    document.querySelector('.crud-add').addEventListener('click', () => {
      (window.bootstrap && bootstrap.Modal.getOrCreateInstance(document.getElementById('couponModal'))).show();
    });
    document.querySelector('#couponModal .coupon-form').addEventListener('submit', e => {
      e.preventDefault();
      const f = new FormData(e.target);
      const data = { prefix: f.get('prefix') || '', num: f.get('num'), mode: f.get('mode'), money: f.get('money'), life: f.get('life'), commodity_id: f.get('commodity_id') || 0, category_id: f.get('category_id') || 0, race: f.get('race') || '', note: f.get('note') || '' };
      const exp = f.get('expire_time');
      if (exp) data.expire_time = exp;
      util.post({ url: API + 'save', data, done: res => {
        message.alert(res.msg || '\u751F\u6210\u5B8C\u6BD5', 'success');
        document.querySelector('.coupon-result').value = (res.data && res.data.code) || '';
        load();
      }, error: res => message.error(res.msg) });
    });

    // \u6279\u91CF\u9501\u5B9A/\u89E3\u9501/\u5220\u9664
    function batch(act, msg, confirmTxt) {
      const ids = selected();
      if (!ids.length) { message.error('\u8BF7\u81F3\u5C11\u52FE\u9009 1 \u5F20\u4F18\u60E0\u5377\uFF01'); return; }
      if (!confirm(confirmTxt + ' ' + ids.length + ' \u5F20?')) return;
      util.post({ url: API + act, data: { list: ids }, done: res => { message.success(res.msg); load(); }, error: res => message.error(res.msg) });
    }
    document.querySelector('.coupon-lock').addEventListener('click', () => batch('lock', '\u9501\u5B9A', '\u786E\u8BA4\u9501\u5B9A\u9009\u4E2D\u7684'));
    document.querySelector('.coupon-unlock').addEventListener('click', () => batch('unlock', '\u89E3\u9501', '\u786E\u8BA4\u89E3\u9501\u9009\u4E2D\u7684'));
    document.querySelector('.coupon-del').addEventListener('click', () => {
      const ids = selected();
      if (!ids.length) { message.error('\u8BF7\u81F3\u5C11\u52FE\u9009 1 \u5F20\u4F18\u60E0\u5377\uFF01'); return; }
      util.post({ url: API + 'deleteImpact', data: { list: ids }, loader: false, done: res => {
        const d = res.data || {};
        const msg = '\u6240\u9009 ' + d.coupon_count + ' \u5F20\uFF1A\u672A\u4F7F\u7528 ' + d.normal_count + '\u3001\u5DF2\u4F7F\u7528 ' + d.used_count + '\u3001\u9501\u5B9A ' + d.locked_count + '\u3002';
        if (!d.can_delete) { message.alert(msg + ' \u5305\u542B\u5DF2\u4F7F\u7528/\u5E26\u8BA2\u5355\u53F7/\u88AB\u8BA2\u5355\u5F15\u7528\uFF0C\u5DF2\u963B\u6B62\u5220\u9664\u3002', 'error'); return; }
        if (!confirm(msg + '\\n\\n\u786E\u8BA4\u6C38\u4E45\u5220\u9664\uFF1F')) return;
        util.post({ url: API + 'del', data: { list: ids }, done: r => { message.success(r.msg); load(); }, error: r => message.error(r.msg) });
      }, error: res => message.error(res.msg) });
    });

    tbody.addEventListener('click', e => {
      const tr = e.target.closest('tr'); if (!tr) return;
      const id = tr.dataset.id;
      if (e.target.closest('.row-del')) {
        util.post({ url: API + 'deleteImpact', data: { list: id }, loader: false, done: res => {
          const d = res.data || {};
          if (!d.can_delete) { message.alert('\u8BE5\u5238\u5DF2\u88AB\u4F7F\u7528\u6216\u5F15\u7528\uFF0C\u65E0\u6CD5\u5220\u9664\u3002', 'error'); return; }
          if (!confirm('\u786E\u8BA4\u5220\u9664\u5238\u7801 ' + tr.querySelector('code').textContent + ' ?')) return;
          util.post({ url: API + 'del', data: { list: id }, done: r => { message.success(r.msg); load(); }, error: r => message.error(r.msg) });
        }, error: res => message.error(res.msg) });
      } else if (e.target.closest('.row-lock')) {
        util.post({ url: API + 'lock', data: { list: id }, done: load, error: res => message.error(res.msg) });
      } else if (e.target.closest('.row-unlock')) {
        util.post({ url: API + 'unlock', data: { list: id }, done: load, error: res => message.error(res.msg) });
      }
    });

    // \u5BFC\u51FA\u5238\u7801
    document.querySelector('.coupon-export').addEventListener('click', () => {
      const payload = {};
      const code = document.querySelector('.cp-f-code').value.trim();
      const st = document.querySelector('.cp-f-status').value;
      const mo = document.querySelector('.cp-f-money').value.trim();
      const ow = document.querySelector('.cp-f-owner').value.trim();
      const note = document.querySelector('.cp-f-note').value.trim();
      const cm = document.querySelector('.cp-f-commodity').value.trim();
      if (code) payload.coupon_code_secret = code;
      if (st !== '') payload['equal-status'] = st;
      if (mo) payload['equal-money'] = mo;
      if (ow) payload['equal-owner'] = ow;
      if (note) payload['equal-note'] = note;
      if (cm) payload['equal-commodity_id'] = cm;
      util.post({ url: API + 'exportImpact', data: payload, loader: false, done: res => {
        const d = res.data || {};
        const total = Number(d.count || 0);
        if (total < 1) { message.error('\u5F53\u524D\u7B5B\u9009\u6CA1\u6709\u53EF\u5BFC\u51FA\u7684\u4F18\u60E0\u5377'); return; }
        if (!confirm('\u5F53\u524D\u7B5B\u9009\u5171 ' + total + ' \u5F20\u4F18\u60E0\u5238\uFF08\u672A\u4F7F\u7528 ' + d.normal_count + '\u3001\u5DF2\u4F7F\u7528 ' + d.used_count + '\u3001\u9501\u5B9A ' + d.locked_count + '\uFF09\u3002\\n\u786E\u8BA4\u5BFC\u51FA\u5238\u7801\u4E3A txt \u6587\u4EF6\uFF1F')) return;
        const dl = Object.assign({}, payload, { expected_count: total });
        fetch(API + 'export', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(dl) })
          .then(r => {
            const ct = r.headers.get('content-type') || '';
            if (ct.includes('application/json')) return r.json().then(j => { throw new Error(j.msg || '\u5BFC\u51FA\u5931\u8D25'); });
            return r.blob().then(blob => {
              const url = URL.createObjectURL(blob);
              const a = document.createElement('a');
              a.href = url; a.download = 'coupons-' + total + '-' + new Date().toISOString().slice(0,10) + '.txt';
              document.body.appendChild(a); a.click(); a.remove();
              setTimeout(() => URL.revokeObjectURL(url), 1000);
              message.success('\u5DF2\u5BFC\u51FA ' + total + ' \u5F20\u4F18\u60E0\u5238');
            });
          })
          .catch(err => message.error(err.message));
      }, error: res => message.error(res.msg) });
    });

    load();
  });`;
  return renderCrudPage({ cfg, manage, title: "\u4F18\u60E0\u5238", activePath: "/admin/coupon/index", body, readyJs: js });
}

// pages.js
var CSS_AUTH = [
  "/assets/common/css/bootstrap.min.css",
  "/assets/common/css/_.css",
  "/assets/common/css/font.min.css",
  "/assets/common/js/layui/css/layui.css",
  "/assets/common/css/select2.min.css",
  "/assets/common/css/component.css",
  "/assets/common/js/table/bootstrap-table.css",
  "/assets/common/js/layer/theme/default/layer.css",
  "/assets/common/css/toastr.min.css",
  "/assets/user/css/_auth.css",
  "/assets/user/css/auth.css"
];
var JS_AUTH = [
  "/assets/common/js/_.js",
  "/assets/user/js/_index.js",
  "/assets/common/js/util/dict.js",
  "/assets/common/js/jquery.min.js",
  "/assets/common/js/toastr.min.js",
  "/assets/common/js/component/loading.js",
  "/assets/common/js/util.js",
  "/assets/common/js/layer/layer.js",
  "/assets/common/js/jquery.pjax.min.js",
  "/assets/common/js/jquery.qrcode.min.js",
  "/assets/common/js/format.js",
  "/assets/common/js/message.js",
  "/assets/common/js/component.js",
  "/assets/common/js/layui/layui.js",
  "/assets/common/js/jquery.treegrid.min.js",
  "/assets/common/js/bootstrap/bootstrap.bundle.min.js",
  "/assets/common/js/table/bootstrap-table.min.js",
  "/assets/common/js/table/bootstrap-table-treegrid.min.js",
  "/assets/common/js/component/form.js",
  "/assets/common/js/component/search.js",
  "/assets/common/js/component/xm-select.js",
  "/assets/common/js/component/tree.select.js",
  "/assets/common/js/component/authtree.js",
  "/assets/common/js/component/table.js",
  "/assets/common/js/component/select2.min.js",
  "/assets/common/js/cache.js",
  "/assets/common/js/editor/editor.js",
  "/assets/common/js/editor/code/code.js",
  "/assets/common/js/component/decimal.js",
  "/assets/user/js/trade.js",
  "/assets/user/js/treasure.js"
];
function renderAuthHeader(v) {
  const { config, title, favicon, app } = v;
  return `<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta name="keywords" content="${htmlEscape(config.keywords || "")}"/>
    <meta name="description" content="${htmlEscape(config.description || "")}"/>
    <link href="${favicon}?v=${app.version}" rel="icon">
    <title>${htmlEscape(title)} - ${htmlEscape(config.shop_name)}</title>
    ${CSS_AUTH.map((f) => `<link href="${f}" rel="stylesheet">`).join("")}
    <script src="/assets/common/js/ready.js"><\/script>
    ${indexVar(0, config)}
</head>
<body style="background-size: cover;background-image: linear-gradient(180deg, rgb(255 255 255 / 0%), rgb(255 255 255 / 71%)), url('${htmlEscape(config.background_url || "")}')">`;
}
function renderAuthFooter() {
  return `${JS_AUTH.map((f) => `<script src="${f}"><\/script>`).join("")}
</body>
</html>`;
}
function pageLogin(v) {
  const { config } = v;
  const captcha = Number(config.login_verification) === 1 ? `
                <div class="row mb-4">
                    <div class="col-sm-6 col-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="image-captcha" name="captcha" placeholder="\u8BF7\u8F93\u5165\u9A8C\u8BC1\u7801">
                            <label class="form-label" for="image-captcha">\u56FE\u5F62\u9A8C\u8BC1\u7801</label>
                        </div>
                    </div>
                    <div class="col-sm-6 col-6 d-flex align-items-center">
                        <img src="/user/captcha/image?action=login" data-acg-refresh="/user/captcha/image?action=login" class="image-code" alt="\u66F4\u6362\u9A8C\u8BC1\u7801">
                    </div>
                </div>` : "";
  const forgetUrl = Number(config.forget_type) === 0 ? "/user/authentication/emailForget" : "/user/authentication/phoneForget";
  const regLink = Number(config.registered_state) === 1 ? `
        <p class="text-center small mt-3 mb-0">
            \u8FD8\u6CA1\u6709\u8D26\u53F7\uFF1F
            <a class="text-link" href="/user/authentication/register">\u7ACB\u5373\u6CE8\u518C</a>
        </p>` : "";
  return `<main class="auth-wrapper">
    <div class="auth-card">

        <div class="brand-header">
            <div class="brand-logo">
                <img src="/favicon.ico" alt="Logo" class="brand-icon">
            </div>

        </div>
        <p class="auth-subtitle small mb-3">\u767B\u5165<a class="text-link" href="/">${htmlEscape(config.shop_name)}</a>\uFF0C\u83B7\u53D6\u66F4\u591A\u6298\u6263\uFF01</p>

        <form method="post" class="needs-validation">
            <div class="form-floating mb-4">
                <input type="text" class="form-control" name="username" placeholder="\u7528\u6237\u540D/\u624B\u673A\u53F7/\u90AE\u7BB1" required>
                <label for="username">\u7528\u6237\u540D/\u624B\u673A\u53F7/\u90AE\u7BB1</label>
            </div>

            <div class="form-floating mb-4">
                <input type="password" class="form-control" name="password" placeholder="\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022" minlength="6" required>
                <label for="password">\u5BC6\u7801</label>
            </div>

            ${captcha}

            <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
                <div class="form-check">
                    <input id="rememberMe" name="remember" type="checkbox" value="1">
                    <label for="rememberMe">\u4FDD\u6301\u4F1A\u8BDD</label>
                </div>
                <a href="${forgetUrl}" class="text-link small">\u5FD8\u8BB0\u5BC6\u7801\uFF1F</a>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-gradient btn-lg">\u767B\u5F55</button>
            </div>
        </form>

        ${regLink}
    </div>
</main>
<script src="/assets/user/controller/auth/login.js"><\/script>`;
}
function pageRegister(v) {
  const { config } = v;
  const regType = Number(config.registered_type);
  let extraField = "";
  if (regType === 2) {
    extraField = `
                <div class="form-floating mb-4">
                    <input type="email" class="form-control" name="email" placeholder="\u90AE\u7BB1" required>
                    <label for="email">\u90AE\u7BB1</label>
                </div>
                ${Number(config.registered_email_verification) === 1 ? `
                    <div class="row mb-4">
                        <div class="col-sm-8 col-8">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="email_captcha" name="email_captcha" placeholder="\u90AE\u7BB1\u9A8C\u8BC1\u7801">
                                <label class="form-label" for="email_captcha">\u90AE\u7BB1\u9A8C\u8BC1\u7801</label>
                            </div>
                        </div>
                        <div class="col-sm-4 col-4">
                            <button type="button" class="w-100 btn btn-outline-primary py-3 send-email-code">\u53D1\u9001\u9A8C\u8BC1\u7801</button>
                        </div>
                    </div>` : ""}`;
  } else if (regType === 1) {
    extraField = `
                <div class="form-floating mb-4">
                    <input type="number" class="form-control" name="phone" placeholder="\u624B\u673A\u53F7" required>
                    <label for="phone">\u624B\u673A\u53F7</label>
                </div>
                ${Number(config.registered_phone_verification) === 1 ? `
                    <div class="row mb-4">
                        <div class="col-sm-8 col-8">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="phone_captcha" name="phone_captcha" placeholder="\u624B\u673A\u9A8C\u8BC1\u7801">
                                <label class="form-label" for="phone_captcha">\u624B\u673A\u9A8C\u8BC1\u7801</label>
                            </div>
                        </div>
                        <div class="col-sm-4 col-4">
                            <button type="button" class="w-100 btn btn-outline-primary py-3 send-phone-captcha">\u53D1\u9001\u9A8C\u8BC1\u7801</button>
                        </div>
                    </div>` : ""}`;
  }
  const captcha = Number(config.registered_verification) === 1 ? `
                <div class="row mb-4">
                    <div class="col-sm-6 col-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="image-captcha" name="captcha" placeholder="\u8BF7\u8F93\u5165\u9A8C\u8BC1\u7801">
                            <label class="form-label" for="image-captcha">\u56FE\u5F62\u9A8C\u8BC1\u7801</label>
                        </div>
                    </div>
                    <div class="col-sm-6 col-6 d-flex align-items-center">
                        <img src="/user/captcha/image?action=register" data-acg-refresh="/user/captcha/image?action=register" class="image-code" alt="\u66F4\u6362\u9A8C\u8BC1\u7801">
                    </div>
                </div>` : "";
  return `<main class="auth-wrapper">
    <div class="auth-card">

        <div class="brand-header">
            <div class="brand-logo">
                <img src="/favicon.ico" alt="Logo" class="brand-icon">
            </div>

        </div>
        <p class="auth-subtitle small mb-3">\u6CE8\u518C<a class="text-link" href="/">${htmlEscape(config.shop_name)}</a>\uFF0C\u83B7\u53D6\u66F4\u591A\u6298\u6263\uFF01</p>

        <form class="needs-validation">
            <div class="form-floating mb-4">
                <input type="text" class="form-control" name="username" placeholder="\u7528\u6237\u540D\uFF0C\u652F\u6301\u4E2D\u6587" required>
                <label for="username">\u7528\u6237\u540D\uFF0C\u652F\u6301\u4E2D\u6587</label>
            </div>

            <div class="form-floating mb-4">
                <input type="text" class="form-control" name="password" placeholder="\u8BBE\u7F6E\u767B\u5F55\u5BC6\u7801" minlength="6" required>
                <label for="password">\u8BBE\u7F6E\u767B\u5F55\u5BC6\u7801</label>
            </div>

            ${extraField}
            ${captcha}

            <div class="d-grid">
                <button type="submit" class="btn btn-gradient btn-lg">\u6CE8\u518C</button>
            </div>
        </form>

        <p class="text-center small mt-3 mb-0">
            \u5DF2\u6709\u8D26\u53F7\uFF1F
            <a class="text-link" href="/user/authentication/login">\u524D\u5F80\u767B\u5F55</a>
        </p>

    </div>
</main>
<script src="/assets/user/controller/auth/register.js"><\/script>`;
}
function userCenterShell(v, body) {
  const { config, user } = v;
  if (!user) return "";
  const menu = [
    { url: "/user/dashboard/index", name: "\u4E2A\u4EBA\u4E2D\u5FC3", icon: "fa-duotone fa-regular fa-user" },
    { url: "/user/personal/purchaseRecord", name: "\u6211\u7684\u8BA2\u5355", icon: "fa-duotone fa-regular fa-receipt" },
    { url: "/user/recharge/index", name: "\u94B1\u5305\u5145\u503C", icon: "fa-duotone fa-regular fa-wallet" },
    { url: "/user/bill/index", name: "\u4F59\u989D\u660E\u7EC6", icon: "fa-duotone fa-regular fa-list" },
    { url: "/user/security/personal", name: "\u8D26\u53F7\u8BBE\u7F6E", icon: "fa-duotone fa-regular fa-gear" }
  ];
  const active = (u) => v.route && v.route.startsWith(u) ? " active" : "";
  const menuHtml = menu.map((m) => `<a class="list-group-item list-group-item-action${active(m.url)}" href="${m.url}"><i class="${m.icon} me-2"></i>${m.name}</a>`).join("");
  return `<main class="container py-4">
  <div class="row g-4">
    <div class="col-12 col-lg-3">
      <div class="panel">
        <div class="panel-body">
          <div class="d-flex align-items-center mb-3">
            <img src="${htmlEscape(user.avatar || "/favicon.ico")}" class="rounded-circle me-3" style="width:56px;height:56px;object-fit:cover;background:#f8f9fa">
            <div>
              <div class="fw-bold">${htmlEscape(user.username)}</div>
              <div class="text-muted small">\u4F59\u989D: <span class="text-success">${htmlEscape(config.currency_symbol || "\xA5")}${user.balance}</span></div>
            </div>
          </div>
          <div class="list-group list-group-flush">
            ${menuHtml}
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-9">
      ${body}
    </div>
  </div>
</main>`;
}
function pageDashboard(v) {
  const { user, config } = v;
  const groupName = v.group ? htmlEscape(v.group.name) : "\u666E\u901A\u4F1A\u5458";
  const body = `
    <div class="row">
      <div class="col-12 col-md-6 mb-3">
        <div class="panel">
          <div class="panel-header"><span class="icon"><i class="fa-duotone fa-regular fa-id-card"></i></span><h6 class="panel-title">\u8D26\u53F7\u4FE1\u606F</h6></div>
          <div class="panel-body">
            <table class="table table-sm table-borderless">
              <tr><td class="text-muted">\u7528\u6237\u540D</td><td>${htmlEscape(user.username)}</td></tr>
              <tr><td class="text-muted">\u4F1A\u5458\u7B49\u7EA7</td><td>${groupName}</td></tr>
              <tr><td class="text-muted">\u4F59\u989D</td><td class="text-success fw-bold">${htmlEscape(config.currency_symbol || "\xA5")}${user.balance}</td></tr>
              <tr><td class="text-muted">\u7D2F\u8BA1\u5145\u503C</td><td>${htmlEscape(config.currency_symbol || "\xA5")}${user.recharge}</td></tr>
              <tr><td class="text-muted">\u6CE8\u518C\u65F6\u95F4</td><td>${v.fmt ? v.fmt(user.create_time) : user.create_time}</td></tr>
            </table>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 mb-3">
        <div class="panel">
          <div class="panel-header"><span class="icon"><i class="fa-duotone fa-regular fa-bolt"></i></span><h6 class="panel-title">\u5FEB\u6377\u5165\u53E3</h6></div>
          <div class="panel-body">
            <div class="d-grid gap-2">
              <a class="btn btn-primary" href="/user/recharge/index"><i class="fa-duotone fa-regular fa-wallet me-2"></i>\u94B1\u5305\u5145\u503C</a>
              <a class="btn btn-outline-secondary" href="/user/personal/purchaseRecord"><i class="fa-duotone fa-regular fa-receipt me-2"></i>\u6211\u7684\u8BA2\u5355</a>
              <a class="btn btn-outline-secondary" href="/user/security/personal"><i class="fa-duotone fa-regular fa-gear me-2"></i>\u8D26\u53F7\u8BBE\u7F6E</a>
            </div>
          </div>
        </div>
      </div>
    </div>`;
  return userCenterShell(v, body);
}
function pagePurchaseRecord(v) {
  const body = `
    <div class="panel">
      <div class="panel-header"><span class="icon"><i class="fa-duotone fa-regular fa-receipt"></i></span><h6 class="panel-title">\u6211\u7684\u8BA2\u5355</h6></div>
      <div class="panel-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle" id="purchase-record-table">
            <thead>
              <tr>
                <th>\u8BA2\u5355\u53F7</th><th>\u5546\u54C1</th><th>\u91D1\u989D</th><th>\u72B6\u6001</th><th>\u521B\u5EFA\u65F6\u95F4</th><th>\u64CD\u4F5C</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
        <div id="purchase-record-empty" class="text-center text-muted py-4" style="display:none">\u6682\u65E0\u8BA2\u5355\u8BB0\u5F55</div>
      </div>
    </div>
    <script>
    (function(){
      var tbody = document.querySelector('#purchase-record-table tbody');
      var empty = document.getElementById('purchase-record-empty');
      fetch('/user/api/purchaseRecord/data').then(function(r){ return r.json(); }).then(function(res){
        var list = (res && res.data) || [];
        if (!list.length) { empty.style.display = 'block'; tbody.innerHTML = ''; return; }
        var html = list.map(function(o){
          var st = o.status == 1 ? '<span class="badge-soft badge-soft-success">\u5DF2\u652F\u4ED8</span>' : '<span class="badge-soft badge-soft-warning">\u672A\u652F\u4ED8</span>';
          var act = o.status == 1 ? '<a class="btn btn-sm btn-outline-primary" href="/user/index/query?tradeNo=' + o.trade_no + '">\u67E5\u770B</a>' : '';
          return '<tr><td>' + o.trade_no + '</td><td>' + o.commodity_name + '</td><td>' + o.amount + '</td><td>' + st + '</td><td>' + new Date(o.create_time * 1000).toLocaleString() + '</td><td>' + act + '</td></tr>';
        }).join('');
        tbody.innerHTML = html;
      });
    })();
    <\/script>`;
  return userCenterShell(v, body);
}
function pageRecharge(v) {
  const { config } = v;
  const payParams = (config.recharge_paylist || "1").split(",").map(Number).filter((n) => n > 0);
  const payRows = v.payList || [];
  const payHtml = payRows.filter((p) => payParams.includes(Number(p.id))).map((p, i) => `
      <div class="form-check pay-check ${i === 0 ? "is-primary" : ""}" data-pay-id="${p.id}">
        <input class="form-check-input" type="radio" name="pay_id" id="pay-${p.id}" value="${p.id}" ${i === 0 ? "checked" : ""}>
        <label class="form-check-label d-flex align-items-center w-100" for="pay-${p.id}">
          <img src="${htmlEscape(p.icon || "/favicon.ico")}" style="width:36px;height:36px;object-fit:cover" class="me-2 rounded">
          <span>${htmlEscape(p.name)}</span>
        </label>
      </div>`).join("");
  const body = `
    <div class="panel">
      <div class="panel-header"><span class="icon"><i class="fa-duotone fa-regular fa-wallet"></i></span><h6 class="panel-title">\u94B1\u5305\u5145\u503C</h6></div>
      <div class="panel-body">
        ${Number(config.recharge_open) === 1 ? `
        <form id="rechargeForm" class="vstack gap-3">
          <div>
            <label class="form-label mb-1">\u5145\u503C\u91D1\u989D</label>
            <div class="input-group">
              <span class="input-group-text">${htmlEscape(config.currency_symbol || "\xA5")}</span>
              <input type="number" class="form-control" name="amount" min="${htmlEscape(config.recharge_min || 1)}" max="${htmlEscape(config.recharge_max || 0) || ""}" placeholder="\u8BF7\u8F93\u5165\u5145\u503C\u91D1\u989D" value="${htmlEscape(config.recharge_min || "")}">
            </div>
            <div class="small text-muted mt-1">\u6700\u4F4E ${htmlEscape(config.currency_symbol || "\xA5")}${htmlEscape(config.recharge_min || 1)}\uFF0C\u6700\u9AD8 ${htmlEscape(config.currency_symbol || "\xA5")}${htmlEscape(config.recharge_max || "\u4E0D\u9650")}</div>
          </div>
          <div>
            <label class="form-label mb-1">\u652F\u4ED8\u65B9\u5F0F</label>
            <div class="vstack gap-2">
              ${payHtml || '<div class="text-muted">\u6682\u65E0\u53EF\u7528\u652F\u4ED8\u65B9\u5F0F</div>'}
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-block">\u7ACB\u5373\u5145\u503C</button>
        </form>
        <script>
        document.getElementById('rechargeForm').addEventListener('submit', function(e){
          e.preventDefault();
          var btn = this.querySelector('button[type=submit]');
          btn.disabled = true;
          var fd = new FormData(this);
          fetch('/user/api/recharge/index', { method: 'POST', body: fd })
            .then(function(r){ return r.json(); })
            .then(function(res){
              if (res.code === 200 && res.data && res.data.url) { location.href = res.data.url; }
              else { alert(res.msg || '\u5145\u503C\u5931\u8D25'); btn.disabled = false; }
            })
            .catch(function(){ alert('\u7F51\u7EDC\u9519\u8BEF'); btn.disabled = false; });
        });
        <\/script>` : `<div class="text-muted">\u5145\u503C\u529F\u80FD\u672A\u5F00\u542F</div>`}
      </div>
    </div>`;
  return userCenterShell(v, body);
}
function pageSecurity(v) {
  const { user, config } = v;
  const body = `
    <div class="panel mb-3">
      <div class="panel-header"><span class="icon"><i class="fa-duotone fa-regular fa-user"></i></span><h6 class="panel-title">\u57FA\u672C\u4FE1\u606F</h6></div>
      <div class="panel-body">
        <table class="table table-sm table-borderless">
          <tr><td class="text-muted" style="width:120px">\u7528\u6237\u540D</td><td>${htmlEscape(user.username)}</td></tr>
          <tr><td class="text-muted">\u90AE\u7BB1</td><td>${htmlEscape(user.email || "\u672A\u7ED1\u5B9A")}</td></tr>
          <tr><td class="text-muted">\u624B\u673A</td><td>${htmlEscape(user.phone || "\u672A\u7ED1\u5B9A")}</td></tr>
          <tr><td class="text-muted">\u4F59\u989D</td><td class="text-success">${htmlEscape(config.currency_symbol || "\xA5")}${user.balance}</td></tr>
        </table>
      </div>
    </div>
    <div class="panel">
      <div class="panel-header"><span class="icon"><i class="fa-duotone fa-regular fa-key"></i></span><h6 class="panel-title">\u4FEE\u6539\u5BC6\u7801</h6></div>
      <div class="panel-body">
        <form id="pwdForm" class="vstack gap-3" style="max-width:420px">
          <div>
            <label class="form-label mb-1">\u5F53\u524D\u5BC6\u7801</label>
            <input type="password" class="form-control" name="oldPassword" placeholder="\u8BF7\u8F93\u5165\u5F53\u524D\u5BC6\u7801" autocomplete="current-password">
          </div>
          <div>
            <label class="form-label mb-1">\u65B0\u5BC6\u7801</label>
            <input type="password" class="form-control" name="password" placeholder="\u6700\u5C116\u4F4D" autocomplete="new-password">
          </div>
          <button type="submit" class="btn btn-primary btn-block">\u4FDD\u5B58\u4FEE\u6539</button>
        </form>
        <script>
        document.getElementById('pwdForm').addEventListener('submit', function(e){
          e.preventDefault();
          var btn = this.querySelector('button[type=submit]');
          btn.disabled = true;
          var fd = new FormData(this);
          fetch('/user/api/security/password', { method: 'POST', body: fd })
            .then(function(r){ return r.json(); })
            .then(function(res){
              btn.disabled = false;
              if (res.code === 200) { alert(res.msg || '\u4FEE\u6539\u6210\u529F'); } else { alert(res.msg || '\u4FEE\u6539\u5931\u8D25'); }
            })
            .catch(function(){ btn.disabled = false; alert('\u7F51\u7EDC\u9519\u8BEF'); });
        });
        <\/script>
      </div>
    </div>`;
  return userCenterShell(v, body);
}
function pageBill(v) {
  const body = `
    <div class="panel">
      <div class="panel-header"><span class="icon"><i class="fa-duotone fa-regular fa-list"></i></span><h6 class="panel-title">\u4F59\u989D\u660E\u7EC6</h6></div>
      <div class="panel-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle" id="bill-table">
            <thead><tr><th>\u65F6\u95F4</th><th>\u7C7B\u578B</th><th>\u91D1\u989D</th><th>\u4F59\u989D</th><th>\u8BF4\u660E</th></tr></thead>
            <tbody></tbody>
          </table>
        </div>
        <div id="bill-empty" class="text-center text-muted py-4" style="display:none">\u6682\u65E0\u4F59\u989D\u53D8\u52A8\u8BB0\u5F55</div>
      </div>
    </div>
    <script>
    (function(){
      var tbody = document.querySelector('#bill-table tbody');
      var empty = document.getElementById('bill-empty');
      fetch('/user/api/bill/data').then(function(r){ return r.json(); }).then(function(res){
        var list = (res && res.data) || [];
        if (!list.length) { empty.style.display = 'block'; tbody.innerHTML = ''; return; }
        var html = list.map(function(b){
          var sign = b.type == 1 ? 'text-success' : 'text-danger';
          var sym = b.type == 1 ? '+' : '-';
          return '<tr><td>' + new Date(b.create_time * 1000).toLocaleString() + '</td><td>' + (b.type == 1 ? '\u6536\u5165' : '\u652F\u51FA') + '</td><td class="' + sign + '">' + sym + b.amount + '</td><td>' + b.balance + '</td><td>' + b.log + '</td></tr>';
        }).join('');
        tbody.innerHTML = html;
      });
    })();
    <\/script>`;
  return userCenterShell(v, body);
}

// worker.js
var APP_VERSION = "3.7.9";
async function viewContext(env, request, url, pageTitle, extra = {}) {
  const cfg = await loadConfig(env);
  const info = requestInfo(request, url);
  return {
    cfg,
    info,
    config: cfg,
    title: pageTitle,
    favicon: "/favicon.ico",
    app: { version: APP_VERSION },
    user: null,
    langs: langMenu(),
    nav: [
      { name: "\u8D2D\u7269", url: "/", icon: "fa-duotone fa-regular fa-cart-shopping", target: "_self", match: "/user/index/index" },
      { name: "\u8BA2\u5355\u67E5\u8BE2", url: "/user/index/query", icon: "fa-duotone fa-regular fa-folders", target: "_self", match: "/user/index/query" }
    ],
    setting: { icp: "" },
    ...extra
  };
}
var CSS_FILES = [
  "/assets/common/css/bootstrap.min.css",
  "/assets/common/css/_.css",
  "/assets/common/css/font.min.css",
  "/assets/common/js/layui/css/layui.css",
  "/assets/common/css/select2.min.css",
  "/assets/common/css/component.css",
  "/assets/common/js/table/bootstrap-table.css",
  "/assets/common/js/layer/theme/default/layer.css",
  "/assets/common/css/toastr.min.css",
  "/assets/user/css/index.css"
];
var JS_FILES = [
  "/assets/common/js/_.js",
  "/assets/user/js/_index.js",
  "/assets/common/js/util/dict.js",
  "/assets/common/js/jquery.min.js",
  "/assets/common/js/toastr.min.js",
  "/assets/common/js/component/loading.js",
  "/assets/common/js/util.js",
  "/assets/common/js/layer/layer.js",
  "/assets/common/js/jquery.pjax.min.js",
  "/assets/common/js/jquery.qrcode.min.js",
  "/assets/common/js/format.js",
  "/assets/common/js/message.js",
  "/assets/common/js/component.js",
  "/assets/common/js/layui/layui.js",
  "/assets/common/js/jquery.treegrid.min.js",
  "/assets/common/js/bootstrap/bootstrap.bundle.min.js",
  "/assets/common/js/table/bootstrap-table.min.js",
  "/assets/common/js/table/bootstrap-table-treegrid.min.js",
  "/assets/common/js/component/form.js",
  "/assets/common/js/component/search.js",
  "/assets/common/js/component/xm-select.js",
  "/assets/common/js/component/tree.select.js",
  "/assets/common/js/component/authtree.js",
  "/assets/common/js/component/table.js",
  "/assets/common/js/component/select2.min.js",
  "/assets/common/js/cache.js",
  "/assets/common/js/editor/editor.js",
  "/assets/common/js/editor/code/code.js",
  "/assets/common/js/component/decimal.js",
  "/assets/user/js/trade.js",
  "/assets/user/js/treasure.js"
];
function renderHeader(v, extraScripts = "") {
  const { config, user, nav, langs, title, favicon, app } = v;
  const currency = config.currency_symbol || "\xA5";
  const navItems = nav.map((n) => {
    const active = v.route && n.match && (v.route === n.match || v.route.startsWith(n.match === "/" ? n.match : n.match + "/") || n.match === "/user/index/index" && (v.route === "/" || v.route.startsWith("/cat") || v.route.startsWith("/item"))) ? " active" : "";
    return `<li class="nav-item"><a class="nav-link${active}" href="${htmlEscape(n.url)}" target="${htmlEscape(n.target)}"><i class="${htmlEscape(n.icon)} nav-icon"></i>${htmlEscape(n.name)}</a></li>`;
  }).join("");
  const userBox = !user ? `
            <div class="ms-2 user-login-box">
                <a class="btn btn-outline-secondary btn-sm br-12" href="/user/authentication/login"><i class="fa-duotone fa-regular fa-right-to-bracket nav-icon"></i>\u767B\u5F55</a>
                <a class="btn btn-primary btn-sm br-12" href="/user/authentication/register"><i class="fa-duotone fa-regular fa-user-plus nav-icon"></i>\u521B\u5EFA\u8D26\u53F7</a>
            </div>` : `
            <div class="ms-2 user-info-box">
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <img id="user-avatar" src="${htmlEscape(user.avatar || "/favicon.ico")}" alt="\u7528\u6237\u5934\u50CF" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover; background-color: #f8f9fa;">
                        <div class="d-flex flex-column align-items-start me-2">
                            <span id="username" class="fw-bold text-dark" style="font-size: 14px; line-height: 1.2;">${htmlEscape(user.username)}</span>
                            <span id="user-balance" class="text-muted" style="font-size: 12px; line-height: 1.2;">\u4F59\u989D: <span class="text-success">${currency}${user.balance}</span></span>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="/user/dashboard/index"><i class="fa-duotone fa-regular fa-user me-2"></i>\u4E2A\u4EBA\u4E2D\u5FC3</a></li>
                        <li><a class="dropdown-item" href="/user/recharge/index"><i class="fa-duotone fa-regular fa-wallet me-2"></i>\u94B1\u5305\u5145\u503C</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/user/personal/purchaseRecord"><i class="fa-duotone fa-regular fa-receipt me-2"></i>\u6211\u7684\u8BA2\u5355</a></li>
                        <li><a class="dropdown-item" href="/user/security/personal"><i class="fa-duotone fa-regular fa-gear me-2"></i>\u8BBE\u7F6E</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/user/authentication/logout"><i class="fa-duotone fa-regular fa-sign-out me-2"></i>\u9000\u51FA\u767B\u5F55</a></li>
                    </ul>
                </div>
            </div>`;
  const langItems = langs.map((l) => `<li><a class="dropdown-item" href="javascript:;" data-lang-value="${htmlEscape(l.code)}">${htmlEscape(l.name)}</a></li>`).join("");
  return `<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="keywords" content="${htmlEscape(config.keywords || "")}"/>
    <meta name="description" content="${htmlEscape(config.description || "")}"/>
    <link href="${favicon}?v=${app.version}" rel="icon">
    <title>${htmlEscape(title)} - ${htmlEscape(config.shop_name)}</title>
    ${CSS_FILES.map((f) => `<link href="${f}" rel="stylesheet">`).join("")}
    <script src="/assets/common/js/ready.js"></script>
    ${extraScripts}
</head>
<body style="background-size: cover;background-image: linear-gradient(180deg, rgb(255 255 255 / 0%), rgb(255 255 255 / 71%)), url('${htmlEscape(config.background_url || "")}')">
<nav class="navbar navbar-expand-lg navbar-acg">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="/">
            <img src="/favicon.ico" alt="ACG Logo" class="brand-logo me-2">
            <span style="color: #1396558a;">${htmlEscape(config.shop_name)}</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-lg-0">
                ${navItems}
            </ul>
            <div class="d-none d-lg-flex search-input" role="search">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-duotone fa-regular fa-magnifying-glass nav-icon"></i></span>
                    <input class="form-control item-search-input" type="search" placeholder="\u641C\u7D22\u5546\u54C1\u5173\u952E\u8BCD.." aria-label="Search">
                </div>
            </div>
        </div>

        <div class="ms-2 dropdown lang-switch">
            <button class="btn lang-switch__btn" type="button" id="langDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="\u8BED\u8A00">
                <i class="fa-duotone fa-regular fa-globe"></i>
                <span class="lang-switch__code" data-lang-label></span>
                <i class="fa-duotone fa-regular fa-chevron-down lang-switch__caret"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end lang-switch__menu" aria-labelledby="langDropdown">
                ${langItems}
            </ul>
        </div>

        ${userBox}

    </div>
</nav>
<div id="pjax-container">`;
}
function renderFooter(v) {
  return `</div>
${v.setting && v.setting.icp ? `<footer>${htmlEscape(v.setting.icp)}</footer>` : ""}
${JS_FILES.map((f) => `<script src="${f}"></script>`).join("")}
</body>
</html>`;
}
async function getVisibleCategories(env, cfg) {
  const rows = await dbRows(env, `SELECT * FROM acg_category WHERE status=1 AND hide=0 ORDER BY sort ASC`);
  const list = rows.map((c) => ({
    id: c.id,
    name: c.name,
    sort: c.sort,
    icon: c.icon || "/favicon.ico",
    pid: c.pid ? Number(c.pid) : 0,
    owner: c.owner || 0
  }));
  return buildCategoryTree(list);
}
async function getCommodityList(env, cfg, params) {
  const { keywords = "", limit = 0, page = 1, categoryId = 0 } = params;
  const lmt = Math.max(0, Number(limit) || 0);
  const pg = Math.max(1, Number(page) || 1);
  const cats = await getVisibleCategories(env, cfg);
  const catIds = [];
  const flat = (arr) => arr.forEach((c) => {
    catIds.push(String(c.id));
    c.children && flat(c.children);
  });
  flat(cats);
  let where = `status=1 AND owner=0`;
  const bind = [];
  if (String(categoryId) === "recommend") {
    where += ` AND recommend=1`;
  } else if (Number(categoryId) !== 0) {
    where += ` AND category_id=?`;
    bind.push(Number(categoryId));
  }
  if (keywords !== "") {
    where += ` AND name LIKE ?`;
    bind.push(`%${keywords}%`);
  }
  const rows = await dbRows(env, `SELECT id, name, cover, status, delivery_way, price, user_price, level_disable, level_price, hide, owner, inventory_hidden, recommend, category_id, stock, shared_id, tags, seckill_status, seckill_start_time, seckill_end_time, config FROM acg_commodity WHERE ${where} ORDER BY sort ASC`, ...bind);
  const list = [];
  for (const r of rows) {
    if (!catIds.includes(String(r.category_id))) continue;
    if (r.hide == 1) continue;
    const item = {
      id: r.id,
      name: r.name,
      cover: r.cover || "/favicon.ico",
      status: r.status,
      delivery_way: r.delivery_way,
      price: Number(r.price),
      user_price: Number(r.user_price) > 0 ? Number(r.user_price) : Number(r.price),
      level_disable: r.level_disable || 0,
      level_price: null,
      hide: r.hide || 0,
      owner: r.owner || 0,
      inventory_hidden: r.inventory_hidden || 0,
      recommend: r.recommend || 0,
      category_id: r.category_id,
      shared_id: null,
      tags: parseTags(r.tags),
      seckill_status: r.seckill_status || 0,
      seckill_active: false,
      has_wholesale: false
    };
    const soldRow = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_order WHERE commodity_id=? AND delivery_status=1`, r.id);
    item.order_sold = soldRow ? Number(soldRow.n) : 0;
    if (r.delivery_way == 0) {
      const cardRow = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_card WHERE status=0 AND commodity_id=?`, r.id);
      item.stock = cardRow ? Number(cardRow.n) : 0;
    } else {
      item.stock = Number(r.stock) || 0;
    }
    item.stock_state = stockState(item.stock);
    if (r.inventory_hidden == 1) {
      item.stock = hideStockText(item.stock);
    }
    list.push(item);
  }
  const total = list.length;
  const data = lmt === 0 ? list : list.slice((pg - 1) * lmt, pg * lmt);
  return { data, total };
}
function parseTags(raw) {
  if (!raw) return [];
  try {
    const arr = JSON.parse(raw);
    return Array.isArray(arr) ? arr : [];
  } catch (e) {
    return [];
  }
}
function stockState(stock) {
  stock = Number(stock) || 0;
  return stock <= 0 ? 0 : stock <= 5 ? 1 : stock <= 20 ? 2 : stock <= 100 ? 3 : 4;
}
function hideStockText(stock) {
  stock = Number(stock) || 0;
  return stock <= 0 ? "\u5DF2\u552E\u7F44" : stock <= 5 ? "\u5373\u5C06\u552E\u7F44" : stock <= 20 ? "\u4E00\u822C" : stock <= 100 ? "\u5145\u8DB3" : "\u975E\u5E38\u591A";
}
async function getItem(env, cfg, id) {
  const r = await dbFirst(env, `SELECT id, name, description, only_user, purchase_count, category_id, cover, price, user_price, status, owner, delivery_way, contact_type, password_status, level_price, level_disable, coupon, shared_id, shared_code, shared_premium, shared_premium_type, seckill_status, seckill_start_time, seckill_end_time, draft_status, draft_premium, inventory_hidden, widget, minimum, maximum, shared_sync, config, stock, code, shared_amount_sync, shared_config_sync, tags FROM acg_commodity WHERE id=?`, id);
  if (!r) throw new Error("\u5546\u54C1\u4E0D\u5B58\u5728");
  if (r.status != 1) throw new Error("\u8BE5\u5546\u54C1\u6682\u672A\u4E0A\u67B6");
  let cfgObj = {};
  try {
    cfgObj = r.config ? JSON.parse(r.config) : {};
  } catch (e) {
    cfgObj = {};
  }
  let stock;
  if (r.delivery_way == 0) {
    const cardRow = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_card WHERE status=0 AND commodity_id=?`, r.id);
    stock = cardRow ? Number(cardRow.n) : 0;
  } else {
    stock = Number(r.stock) || 0;
  }
  const userPrice = Number(r.user_price) > 0 ? Number(r.user_price) : Number(r.price);
  const item = {
    id: r.id,
    name: r.name,
    description: r.description || "",
    only_user: r.only_user || 0,
    purchase_count: r.purchase_count || 0,
    category_id: r.category_id,
    cover: r.cover || "/favicon.ico",
    price: Number(r.price),
    user_price: userPrice,
    status: r.status,
    owner: r.owner || 0,
    delivery_way: r.delivery_way,
    contact_type: r.contact_type || 0,
    password_status: r.password_status || 0,
    level_disable: r.level_disable || 0,
    coupon: r.coupon || 0,
    seckill_status: r.seckill_status || 0,
    seckill_start_time: r.seckill_start_time || 0,
    seckill_end_time: r.seckill_end_time || 0,
    draft_status: r.draft_status || 0,
    draft_premium: Number(r.draft_premium) || 0,
    inventory_hidden: r.inventory_hidden || 0,
    minimum: r.minimum || 0,
    maximum: r.maximum || 0,
    stock,
    code: r.code,
    order_sold: 0,
    service_url: cfg.service_url || "",
    service_qq: cfg.service_qq || "",
    tags: parseTags(r.tags),
    config: {
      category: cfgObj.category || {},
      sku: cfgObj.sku || {}
    },
    widget: r.widget ? parseTags(r.widget) : [],
    share_url: "",
    login: false,
    trade_captcha: Number(cfg.trade_verification) || 0
  };
  const soldRow = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_order WHERE commodity_id=? AND delivery_status=1`, r.id);
  item.order_sold = soldRow ? Number(soldRow.n) : 0;
  return item;
}
function renderWidget(widgets) {
  if (!Array.isArray(widgets) || widgets.length === 0) return "";
  let html = "";
  for (const w of widgets) {
    if ((w.type || "") === "custom") {
      const n = htmlEscape(w.name || "");
      if (n !== "") html += `<div class="acg-widget-custom acg-widget-custom-${n}" data-widget-custom="${n}"></div>`;
      continue;
    }
    const dict = {};
    if (w.dict) {
      for (const pair of String(w.dict).split(",")) {
        const [k, v] = pair.split("=").map((s) => s.trim());
        if (k !== "" && v !== "") dict[v] = k;
      }
    }
    html += `<div><label class="form-label mb-1">${htmlEscape(w.cn || "")}</label>`;
    if (["text", "password", "number"].includes(w.type)) {
      html += `<input type="${htmlEscape(w.type)}" class="form-control" name="${htmlEscape(w.name || "")}" placeholder="${htmlEscape(w.placeholder || "")}">`;
    } else if (w.type === "select") {
      let opt = `<option value="">${htmlEscape(w.placeholder || "")}</option>`;
      for (const [k, val] of Object.entries(dict)) opt += `<option value="${htmlEscape(k)}">${htmlEscape(val)}</option>`;
      html += `<select class="form-control" name="${htmlEscape(w.name || "")}">${opt}</select>`;
    } else if (w.type === "checkbox" || w.type === "radio") {
      html += "<div>";
      for (const [k, val] of Object.entries(dict)) {
        const checked = w.type === "radio" && k === Object.keys(dict)[0] ? " checked" : "";
        html += `<div class="form-check form-check-inline">
  <input class="form-check-input" name="${htmlEscape(w.name || "")}${w.type === "checkbox" ? "[]" : ""}" type="${w.type}" id="${w.type}-${htmlEscape(k)}" value="${htmlEscape(k)}"${checked}>
  <label class="form-check-label" for="${w.type}-${htmlEscape(k)}">${htmlEscape(val)}</label>
</div>`;
      }
      html += "</div>";
    } else if (w.type === "textarea") {
      html += `<textarea class="form-control" name="${htmlEscape(w.name || "")}" rows="3"></textarea>`;
    }
    html += "</div>";
  }
  return html;
}
function contactTypeMsg(type) {
  return Number(type) === 1 ? "\u624B\u673A\u53F7" : Number(type) === 2 ? "\u90AE\u7BB1\u5730\u5740" : Number(type) === 3 ? "QQ\u53F7" : "\u8054\u7CFB\u65B9\u5F0F";
}
function pageIndex(v) {
  const { config } = v;
  const chips = v.category.map((c, i) => {
    const active = i === 0 && Number(v.categoryId) === 0 || Number(v.categoryId) === c.id;
    const icon = String(c.id) !== "recommend" ? `<span class="chip-icon" style="background: url('${htmlEscape(c.icon)}') center/cover no-repeat;"></span>` : "";
    return `<a data-id="${c.id}" class="switch-category chip ${active ? "is-primary" : ""}" href="javascript:void(0);">${icon}${htmlEscape(c.name)}</a>`;
  }).join("");
  return `<main class="container py-4">
  <!-- \u516C\u544A\u9762\u677F -->
  <div class="panel">
    <div class="panel-header">
      <span class="icon"><i class="fa-duotone fa-regular fa-bullhorn"></i></span>
      <h6 class="panel-title">\u516C\u544A</h6>
    </div>
    <div class="panel-body">
        ${config.notice || ""}
    </div>
  </div>

  <!-- \u8D2D\u4E70\u9762\u677F -->
  <div class="panel">
    <div class="panel-header">
      <span class="icon"><i class="fa-duotone fa-regular fa-cart-shopping"></i></span>
      <h6 class="panel-title">\u8D2D\u4E70</h6>
    </div>
    <div class="panel-body">
      <div class="mb-3">
        <div class="chip-list">
            ${chips}
        </div>
      </div>
      <div class="row item-list">
          <div class="item-message">\u52AA\u529B\u52A0\u8F7D\u4E2D..</div>
      </div>
    </div>
  </div>
</main>
<script src="/assets/user/controller/index/index.js"></script>`;
}
function pageItem(v) {
  const { item, config } = v;
  const deliveryText = item.delivery_way == 0 ? "\u81EA\u52A8\u53D1\u8D27" : "\u5728\u7EBF\u53D1\u8D27";
  const stockText = item.inventory_hidden == 1 ? item.stock : `${item.stock}`;
  let raceHtml = "";
  if (item.config && item.config.category && Object.keys(item.config.category).length) {
    const races = Object.entries(item.config.category);
    raceHtml = `<div>
        <label class="form-label mb-1">\u5B9D\u8D1D\u7C7B\u578B</label>
        <div class="sku-list">
          ${races.map(([race, price], i) => `<a class="switch-race sku ${i === 0 ? "is-primary" : ""}" data-sku="${htmlEscape(race)}" data-price="${htmlEscape(price)}" href="javascript:void(0);">${htmlEscape(race)}<span class="badge-money">${config.currency_symbol || "\xA5"}${htmlEscape(price)}</span></a>`).join("")}
        </div>
    </div>`;
  }
  let skuHtml = "";
  if (item.config && item.config.sku && Object.keys(item.config.sku).length) {
    skuHtml = Object.entries(item.config.sku).map(([name, sku]) => {
      const options = Object.entries(sku);
      return `<div>
        <label class="form-label mb-1">${htmlEscape(name)}</label>
        <div class="sku-list">
          ${options.map(([key, price], i) => `<a class="switch-sku sku ${i === 0 ? "is-primary" : ""}" data-sku="${htmlEscape(name)}" data-value="${htmlEscape(key)}" data-price="${htmlEscape(price)}" href="javascript:void(0);">${htmlEscape(key)}${Number(price) > 0 ? `<span class="badge-money">+${config.currency_symbol || "\xA5"}${htmlEscape(price)}</span>` : ""}</a>`).join("")}
        </div>
    </div>`;
    }).join("");
  }
  const draftHtml = item.draft_status == 1 ? `<div>
        <input type="hidden" name="card_id" class="form-control">
        <label class="form-label mb-1">\u81EA\u9009\u8D26\u53F7</label>
        <button type="button" class="optional-card">\u672A\u81EA\u9009,\u5C06\u968F\u673A\u53D1\u8D27</button>
    </div>` : "";
  const contactHtml = !v.user ? `<div>
        <label class="form-label mb-1">${contactTypeMsg(item.contact_type)}</label>
        <input type="text" name="contact" class="form-control" placeholder="\u8BF7\u8F93\u5165\u60A8\u7684${contactTypeMsg(item.contact_type)}">
    </div>` : "";
  const couponHtml = item.coupon == 1 ? `<div>
        <label class="form-label mb-1">\u4F18\u60E0\u5238</label>
        <input type="text" class="form-control" name="coupon" placeholder="\u4F18\u60E0\u5238\u4EE3\u7801\uFF0C\u6CA1\u6709\u5219\u4E0D\u586B">
    </div>` : "";
  const widgetHtml = renderWidget(item.widget || []);
  const pwdHtml = item.password_status == 1 ? `<div>
        <label class="form-label mb-1">\u67E5\u8BE2\u5BC6\u7801</label>
        <input type="password" class="form-control" name="password" placeholder="\u8BBE\u7F6E\u67E5\u8BE2\u8BA2\u5355\u7684\u5BC6\u7801">
    </div>` : "";
  const numValue = item.minimum > 0 ? item.minimum : 1;
  const captchaHtml = item.trade_captcha == 1 ? `<div>
        <label class="form-label mb-1">\u4EBA\u673A\u9A8C\u8BC1</label>
        <div class="input-group" style="width: 240px;">
            <input type="text" class="form-control captcha-input" placeholder="\u56FE\u5F62\u9A8C\u8BC1\u7801" name="captcha">
            <div class="input-group-append">
                <img class="input-group-text captcha-img" src="/user/captcha/image?action=trade"/>
            </div>
        </div>
    </div>` : "";
  return `<main class="container py-4">

    <div class="panel mt-3">
        <div class="panel-body">
            <div class="row g-4 align-items-stretch">

                <div class="col-12 col-lg-6 d-flex">
                    <div class="acg-card h-100 w-100 flex-fill acg-cover">
                        <img src="${htmlEscape(item.cover)}" class="item-cover">
                    </div>
                </div>


                <div class="col-12 col-lg-6 d-flex">
                    <div class="flex-fill">
                        <h4>${htmlEscape(item.name)}</h4>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            ${item.seckill_status == 1 ? `<span class="badge-soft snap-up" style="display: none;"></span>` : ""}
                            <span class="badge-soft badge-soft-success">${deliveryText}</span>
                            <span class="badge-soft badge-soft-primary">\u5DF2\u552E ${item.order_sold}</span>
                            <span class="badge-soft badge-soft-success item-stock">\u5E93\u5B58 ${stockText}</span>
                            <span class="badge-soft badge-soft-info shared-button"><i class="fa-duotone fa-regular fa-share-from-square"></i></span>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mb-3 abacus">
                            <div class="price"><i class="fa-duotone fa-regular fa-spinner-third icon-spin fs-6"></i></div>
                        </div>
                        <form method="post" class="vstack gap-3">
                            ${raceHtml}
                            ${skuHtml}
                            ${draftHtml}
                            ${contactHtml}
                            ${couponHtml}
                            ${widgetHtml}
                            ${pwdHtml}

                            <div>
                                <label class="form-label mb-1">\u8D2D\u4E70\u6570\u91CF</label>
                                <div class="input-group qty-group">
                                    <button type="button" class="change-num-sub">-</button>
                                    <input type="number" class="form-control text-center" name="num" value="${numValue}">
                                    <button type="button" class="change-num-add">+</button>
                                </div>
                            </div>

                            ${captchaHtml}

                            <div class="cash-pay p-2" style="display: none;">
                                <label class="form-label mb-2"><i class="fa-duotone fa-regular fa-cart-shopping"></i> \u4ED8\u6B3E</label>
                                <div class="pay-list">
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

    </div>


    <div class="panel mt-3 item-detail">
        <div class="panel-header">
            <span class="icon"><i class="fa-duotone fa-regular fa-memo-circle-info"></i></span>
            <h6 class="panel-title">\u5B9D\u8D1D\u8BE6\u60C5</h6>
        </div>
        <div class="panel-body">
            ${item.description}
        </div>
    </div>


</main>
<script src="/assets/user/controller/index/item.js"></script>`;
}
function pageQuery(v) {
  return `<main class="container py-4">
    <div class="panel">
        <div class="panel-header">
            <span class="icon"><i class="fa-duotone fa-regular fa-folders"></i></span>
            <h6 class="panel-title">\u8BA2\u5355\u67E5\u8BE2</h6>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-12 col-lg-6 offset-lg-3">
                    <form class="vstack gap-3" id="queryForm">
                        <div>
                            <label class="form-label mb-1">\u8BA2\u5355\u53F7</label>
                            <input type="text" class="form-control" name="tradeNo" value="${htmlEscape(v.tradeNo || "")}" placeholder="\u8BF7\u8F93\u5165\u8BA2\u5355\u53F7">
                        </div>
                        <div id="queryResult"></div>
                        <button type="button" class="btn btn-primary btn-block query-submit">\u67E5\u8BE2</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="/assets/user/controller/index/query.js"></script>`;
}
function pageClosed(v) {
  return `<main class="container py-5">
    <div class="panel">
        <div class="panel-body text-center py-5">
            <i class="fa-duotone fa-regular fa-screwdriver-wrench fa-3x mb-3"></i>
            <h4>${htmlEscape(v.config.closed_message || "\u6211\u4EEC\u6B63\u5728\u5347\u7EA7\uFF0C\u8BF7\u8010\u5FC3\u7B49\u5F85\u5B8C\u6210\u3002")}</h4>
        </div>
    </div>
</main>`;
}
var jsonRes = (data, status = 200) => new Response(JSON.stringify(data), {
  status,
  headers: { "Content-Type": "application/json; charset=utf-8", "X-Content-Type-Options": "nosniff" }
});
async function readBody(request) {
  if (request.method === "GET" || request.method === "HEAD") return {};
  try {
    const ct = (request.headers.get("Content-Type") || "").toLowerCase();
    if (ct.includes("application/json")) return await request.json();
    if (ct.includes("application/x-www-form-urlencoded") || ct.includes("multipart/form-data")) {
      const form = await request.formData();
      const out = {};
      form.forEach((v, k) => {
        out[k] = v;
      });
      return out;
    }
    const text = await request.text();
    if (text) {
      try {
        return JSON.parse(text);
      } catch (e) {
      }
    }
    return {};
  } catch (e) {
    return {};
  }
}
var pageRes = (html) => new Response(html, {
  status: 200,
  headers: {
    "Content-Type": "text/html; charset=utf-8",
    "X-Content-Type-Options": "nosniff",
    "X-Frame-Options": "SAMEORIGIN",
    "Referrer-Policy": "strict-origin-when-cross-origin"
  }
});
async function route(env, request, url, ctx) {
  const pathname = url.pathname;
  let s = pathname;
  let mid = null;
  let cid = null;
  const itemM = pathname.match(/^\/item\/(\d+)\/?$/);
  if (itemM) {
    s = "/user/index/item";
    mid = Number(itemM[1]);
  }
  const catM = pathname.match(/^\/cat\/(\d+|recommend)\/?$/);
  if (catM) {
    s = "/user/index/index";
    cid = catM[1];
  }
  if (!s || s === "/") s = "/user/index/index";
  if (s === "/admin") return new Response(null, { status: 302, headers: { Location: "/admin/authentication/login" } });
  const q = url.searchParams;
  const cfg = await loadConfig(env);
  if (s.startsWith("/admin/")) {
    ctx.route = s;
    if (s === "/admin/authentication/logout") {
      await revokeManageSession(env, request);
      return new Response(null, {
        status: 302,
        headers: {
          Location: "/admin/authentication/login",
          "Set-Cookie": "MANAGE_USER=; Path=/; HttpOnly; SameSite=Lax; Max-Age=0"
        }
      });
    }
    if (s === "/admin/authentication/login") {
      return pageRes(renderAdminLoginPage(cfg));
    }
    if (s.startsWith("/admin/api/")) {
      const rest = s.replace("/admin/api/", "");
      const body = await request.text().catch(() => "");
      let parsed = {};
      try {
        parsed = body ? JSON.parse(body) : {};
      } catch (e) {
        if (body) {
          try {
            parsed = Object.fromEntries(new URLSearchParams(body));
          } catch (e2) {
          }
        }
      }
      if (!body) {
        for (const [k, v] of url.searchParams) {
          if (!(k in parsed)) parsed[k] = v;
        }
      }
      const res = await adminEndpoint(env, request, url, ...rest.split("/"), parsed);
      return res;
    }
    const manage = await authenticateManage(env, request);
    if (!manage) {
      const qLogin = `?goto=${encodeURIComponent(s)}`;
      return new Response(null, { status: 302, headers: { Location: "/admin/authentication/login" + qLogin } });
    }
    if (s === "/admin/dashboard/index" || s === "/admin/dashboard") {
      return pageRes(renderAdminDashboardPage(cfg, manage));
    }
    if (s === "/admin/category/index") {
      return pageRes(renderAdminCategoryPage(cfg, manage));
    }
    if (s === "/admin/commodity/index") {
      return pageRes(renderAdminCommodityPage(cfg, manage));
    }
    if (s === "/admin/card/index") {
      const cid2 = Number(url.searchParams.get("commodity_id")) || 0;
      return pageRes(renderAdminCardPage(cfg, manage, cid2));
    }
    if (s === "/admin/order/index") {
      return pageRes(renderAdminOrderPage(cfg, manage));
    }
    if (s === "/admin/user/index") {
      return pageRes(renderAdminUserPage(cfg, manage));
    }
    if (s === "/admin/recharge/order") {
      return pageRes(renderAdminRechargePage(cfg, manage));
    }
    if (s === "/admin/coupon/index") {
      return pageRes(renderAdminCouponPage(cfg, manage));
    }
    return pageRes(renderAdminShell({ cfg, manage, title: "\u5EFA\u8BBE\u4E2D", activePath: s }, "text/html"));
  }
  if (pathname.startsWith("/user/captcha/image")) {
    const action = q.get("action") || "login";
    const cap = await captchaImage(env, request, action);
    return new Response(cap.svg, {
      status: 200,
      headers: { "Content-Type": "image/svg+xml; charset=utf-8", "Cache-Control": "no-store", "Set-Cookie": cap.cookie }
    });
  }
  if (s === "/user/authentication/login") {
    if (Number(cfg.closed) === 1) return pageRes(renderAuthHeader(await viewContext(env, request, url, "\u767B\u5F55")) + `<main class="auth-wrapper"><div class="auth-card"><p class="text-center">\u5E97\u94FA\u7EF4\u62A4\u4E2D\uFF0C\u6682\u65F6\u65E0\u6CD5\u767B\u5F55</p></div></main>` + renderAuthFooter());
    const v = await viewContext(env, request, url, "\u767B\u5F55");
    v.route = s;
    return pageRes(renderAuthHeader(v) + pageLogin(v) + renderAuthFooter());
  }
  if (s === "/user/authentication/register") {
    if (Number(cfg.registered_state) === 0) return pageRes(renderAuthHeader(await viewContext(env, request, url, "\u63D0\u793A")) + `<main class="auth-wrapper"><div class="auth-card"><p class="text-center">\u6CE8\u518C\u5DF2\u5173\u95ED</p></div></main>` + renderAuthFooter());
    const v = await viewContext(env, request, url, "\u6CE8\u518C");
    v.route = s;
    return pageRes(renderAuthHeader(v) + pageRegister(v) + renderAuthFooter());
  }
  if (s === "/user/authentication/logout") return logout(env, request, url);
  const memberPages = {
    "/user/dashboard/index": pageDashboard,
    "/user/personal/purchaseRecord": pagePurchaseRecord,
    "/user/bill/index": pageBill,
    "/user/recharge/index": pageRecharge,
    "/user/security/personal": pageSecurity
  };
  const memberPrefixes = ["/user/dashboard", "/user/personal", "/user/security", "/user/recharge", "/user/bill", "/user/cash", "/user/coupon", "/user/ticket", "/user/message", "/user/promote", "/user/order", "/user/card", "/user/commodity", "/user/category", "/user/business", "/user/share"];
  if (memberPrefixes.some((p) => s.startsWith(p))) {
    const user = await currentUser(env, request);
    if (!user) return new Response(null, { status: 302, headers: { Location: "/user/authentication/login" } });
    const v = await viewContext(env, request, url, "\u4F1A\u5458\u4E2D\u5FC3", { user });
    v.route = s;
    v.group = await userGroupOf(env, user);
    if (s === "/user/recharge/index") {
      const pays = await payList(env, request, url).then((r) => r.json()).then((j) => j.data || []);
      v.payList = pays;
    }
    if (memberPages[s]) return pageRes(renderHeader(v, indexVar(0, cfg)) + memberPages[s](v) + renderFooter(v));
    return pageRes(renderHeader(v, indexVar(0, cfg)) + userCenterShell(v, `
      <div class="panel">
        <div class="panel-header"><span class="icon"><i class="fa-duotone fa-regular fa-right-from-bracket"></i></span><h6 class="panel-title">\u656C\u8BF7\u671F\u5F85</h6></div>
        <div class="panel-body">
          <p class="text-muted mb-3">\u529F\u80FD\u5F00\u53D1\u4E2D\uFF0C\u656C\u8BF7\u671F\u5F85 (P2 \u9636\u6BB5)</p>
          <a class="btn btn-primary" href="/user/dashboard/index"><i class="fa-duotone fa-regular fa-arrow-left me-2"></i>\u8FD4\u56DE\u4E2A\u4EBA\u4E2D\u5FC3</a>
        </div>
      </div>`) + renderFooter(v));
  }
  if (s.startsWith("/user/api/")) {
    const parts2 = s.replace(/^\/user\/api\//, "").split("/");
    const apiCtl = parts2[0] || "";
    const apiAct = parts2[1] || "";
    const body = await readBody(request);
    if (apiCtl === "index") {
      if (apiAct === "data") {
        const cats = await getVisibleCategories(env, cfg);
        return jsonRes({ code: 200, msg: "success", data: cats });
      }
      if (apiAct === "commodity") {
        const { data, total } = await getCommodityList(env, cfg, {
          keywords: q.get("keywords") || "",
          limit: q.get("limit") || 0,
          page: q.get("page") || 1,
          categoryId: q.get("categoryId") ?? cid ?? 0
        });
        return jsonRes({ code: 200, msg: "success", data, total });
      }
      if (apiAct === "commodityDetail") {
        try {
          const item = await getItem(env, cfg, Number(q.get("commodityId") || body.item_id));
          item.stock_state = stockState(item.stock);
          if (item.inventory_hidden == 1) item.stock = hideStockText(item.stock);
          return jsonRes({ code: 200, msg: "success", data: item });
        } catch (e) {
          return jsonRes({ code: 403, msg: e.message }, 200);
        }
      }
      if (apiAct === "query") return queryOrder(env, request, url, body);
      if (apiAct === "card") return cardDetail(env, request, url, body);
    }
    if (apiCtl === "authentication") {
      if (apiAct === "register") return register(env, request, url, body);
      if (apiAct === "login") return login(env, request, url, body);
    }
    if (apiCtl === "order") {
      if (apiAct === "trade") return trade(env, request, url, body);
      if (apiAct.startsWith("callback")) {
        const tradeNo = apiAct.split(".")[1] || "";
        return orderCallback(env, request, url, tradeNo);
      }
    }
    if (apiCtl === "purchaseRecord") {
      if (apiAct === "data") return purchaseRecord(env, request, url);
    }
    if (apiCtl === "bill") {
      if (apiAct === "data") return billData(env, request, url);
    }
    if (apiCtl === "pay") {
      if (apiAct === "data" || apiAct === "index") return payList(env, request, url);
    }
    if (apiCtl === "recharge") {
      if (apiAct === "data") return payList(env, request, url);
      if (apiAct === "index") return rechargeCreate(env, request, url, body);
    }
    if (apiCtl === "security") {
      if (apiAct === "password") return changePassword(env, request, url, body);
    }
    return jsonRes({ code: 404, msg: "\u63A5\u53E3\u4E0D\u5B58\u5728" }, 404);
  }
  const parts = s.split("/").filter((p) => p !== "");
  const ctl = parts[1] || "";
  const act = parts[2] || (parts[0] || "index");
  if (s === "/user/index/index") {
    if (Number(cfg.closed) === 1) {
      const v2 = await viewContext(env, request, url, "\u5E97\u94FA\u6B63\u5728\u7EF4\u62A4");
      v2.route = s;
      return pageRes(renderHeader(v2, indexVar(0, cfg)) + pageClosed(v2) + renderFooter(v2));
    }
    const category = await getVisibleCategories(env, cfg);
    const categoryId = cid != null ? cid : Number(cfg.default_category) || 0;
    const v = await viewContext(env, request, url, "\u8D2D\u7269", { category, categoryId });
    v.route = s;
    return pageRes(renderHeader(v, indexVar(categoryId, cfg)) + pageIndex(v) + renderFooter(v));
  }
  if (s === "/user/index/item") {
    try {
      const item = await getItem(env, cfg, mid != null ? mid : Number(q.get("mid")));
      const v = await viewContext(env, request, url, item.name, { item, commodityId: item.id });
      v.route = s;
      const js = itemVar({ ...item, description: void 0 });
      return pageRes(renderHeader(v, js) + pageItem(v) + renderFooter(v));
    } catch (e) {
      return pageRes(`<!DOCTYPE html><html><head><meta charset="utf-8"><title>\u63D0\u793A</title></head><body style="font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#f5f6fa;color:#333"><div style="text-align:center"><h3>${htmlEscape(e.message)}</h3><p><a href="/">\u8FD4\u56DE\u9996\u9875</a></p></div></body></html>`);
    }
  }
  if (s === "/user/index/query") {
    const v = await viewContext(env, request, url, "\u8BA2\u5355\u67E5\u8BE2", { tradeNo: q.get("tradeNo") || "" });
    v.route = s;
    return pageRes(renderHeader(v, indexVar(0, cfg)) + pageQuery(v) + renderFooter(v));
  }
  if (s === "/404.html" || pathname === "/404.html") {
    return pageRes(`<!DOCTYPE html><html><head><meta charset="utf-8"><title>404 Not Found</title></head><body style="font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#f5f6fa;color:#333"><div style="text-align:center"><h1 style="font-size:4rem;color:#dc3545">404</h1><p>\u9875\u9762\u4E0D\u5B58\u5728</p><p><a href="/">\u8FD4\u56DE\u9996\u9875</a></p></div></body></html>`);
  }
  if (/\.(js|css|png|jpg|jpeg|gif|ico|svg|woff2?|ttf|eot|map|webp)$/i.test(pathname) && env.ASSETS) {
    try {
      return await env.ASSETS.fetch(request);
    } catch (e) {
    }
  }
  return pageRes(`<!DOCTYPE html><html><head><meta charset="utf-8"><title>404 Not Found</title></head><body style="font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#f5f6fa;color:#333"><div style="text-align:center"><h1 style="font-size:4rem;color:#dc3545">404</h1><p>\u9875\u9762\u4E0D\u5B58\u5728: ${htmlEscape(pathname)}</p><p><a href="/">\u8FD4\u56DE\u9996\u9875</a></p></div></body></html>`);
}
var worker_default = {
  async fetch(request, env, ctx) {
    const url = new URL(request.url);
    if (url.pathname === "/favicon.ico") {
      if (env.ASSETS) {
        try {
          return await env.ASSETS.fetch(request);
        } catch (e) {
        }
      }
    }
    try {
      return await route(env, request, url, {});
    } catch (e) {
      return pageRes(`<!DOCTYPE html><html><head><meta charset="utf-8"><title>\u670D\u52A1\u5668\u9519\u8BEF</title></head><body style="font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#f5f6fa;color:#333"><div style="text-align:center"><h3>\u670D\u52A1\u5668\u5F00\u5C0F\u5DEE\u4E86 (500)</h3><p style="color:#999">${htmlEscape(e && e.message ? e.message : String(e))}</p></div></body></html>`, 500);
    }
  }
};
export {
  worker_default as default,
  getCommodityList,
  getItem,
  pageClosed,
  pageIndex,
  pageItem,
  pageQuery,
  renderFooter,
  renderHeader
};
