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
    const money = Number(voucher.money) || 0;
    const deduction = Number(voucher.mode) === 0 ? money : price.mul(money).getAmount();
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
    return pageRes('<!DOCTYPE html><html><head><meta charset="utf-8"><title>\u656C\u8BF7\u671F\u5F85</title></head><body style="font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#f5f6fa;color:#333"><div style="text-align:center"><h1 style="font-size:2rem;margin-bottom:.5rem">\u7BA1\u7406\u540E\u53F0</h1><p>\u540E\u53F0\u7BA1\u7406\u529F\u80FD\u8FC1\u79FB\u4E2D\uFF0C\u656C\u8BF7\u671F\u5F85 (P2 \u9636\u6BB5)</p></div></body></html>');
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
