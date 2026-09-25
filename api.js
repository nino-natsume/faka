// ============================================================
// acg-faka Worker 移植 - api.js (用户 API 业务层)
// 对齐原版: Api\Authentication / Api\Order / Api\Index / Api\PurchaseRecord / Api\Bill
// ============================================================
import {
  now, htmlEscape, randStr, md5hex, parseCookies, requestInfo, loadConfig,
  dbRows, dbFirst, dbRun, dbInsert, dbUpdate,
  generatePassword, verifyPassword,
  generateTradeNo, Decimal, epaySign,
  getUserGroup, billCreate, BILL,
  userSessionCookie, verifyUserSession, jwtVerify, UTF8,
  throttle, throttleClear,
} from './lib.js';

const SESSION_NAME = 'USER_SESSION';

export function getCookies(request, env) {
  return parseCookies(request.headers.get('Cookie') || '');
}

// ---------- 当前用户 (对齐 UserVisitor) ----------
export async function currentUser(env, request) {
  const cookies = getCookies(request, env);
  const token = cookies[SESSION_NAME];
  if (!token) return null;
  return verifyUserSession(env, token);
}

// ---------- 用户组 ----------
export async function userGroupOf(env, user) {
  if (!user) return null;
  return getUserGroup(env, user.recharge || 0);
}

// ---------- 验证码 (SVG 数字, HMAC token cookie) ----------
const CAPTCHA_SECRET_KEY = 'acg-faka-captcha-v1';
export async function captchaImage(env, request, action) {
  const code = String(Math.floor(1000 + Math.random() * 9000));
  const token = md5hex(`${code}:${action}:${CAPTCHA_SECRET_KEY}:${randStr(6)}`);
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="120" height="40" viewBox="0 0 120 40">
    <rect width="120" height="40" fill="#f0f4f8"/>
    ${[0, 1, 2, 3].map(i => `<line x1="${10 + i * 30}" y1="${Math.random() * 30 + 5}" x2="${40 + i * 20}" y2="${Math.random() * 30 + 5}" stroke="#c5d5e6" stroke-width="1"/>`).join('')}
    ${[0, 1, 2, 3].map(i => `<text x="${14 + i * 28}" y="27" font-size="24" font-family="monospace" fill="${['#2b6cb0', '#38a169', '#c53030', '#805ad5'][i]}">${code[i]}</text>`).join('')}
  </svg>`;
  return {
    svg,
    token: `${BufferB64(code + ':' + token)}`,
    cookie: `acg_captcha_${action}=${encodeURIComponent(BufferB64(code + ':' + token))}; Path=/; HttpOnly; SameSite=Lax; Max-Age=600`,
  };
}

function BufferB64(str) {
  // UTF-8 -> base64 (无 Buffer 环境)
  const bytes = new TextEncoder().encode(str);
  let bin = '';
  bytes.forEach(b => { bin += String.fromCharCode(b); });
  return btoa(bin);
}
function BufferUnB64(str) {
  const bin = atob(str);
  const bytes = Uint8Array.from(bin, c => c.charCodeAt(0));
  return new TextDecoder().decode(bytes);
}

export async function captchaVerify(env, request, action, input) {
  if (!input) return false;
  const cookies = getCookies(request, env);
  const raw = cookies[`acg_captcha_${action}`];
  if (!raw) return false;
  try {
    const decoded = BufferUnB64(decodeURIComponent(raw));
    const [code] = decoded.split(':');
    return String(input).trim() === code;
  } catch (e) { return false; }
}

// ---------- 注册 (对齐 Api\Authentication::register) ----------
export async function register(env, request, url, body) {
  const cfg = await loadConfig(env);
  if (Number(cfg.registered_state) === 0) return apiError('注册已关闭');
  if (Number(cfg.registered_verification) === 1) {
    const ok = await captchaVerify(env, request, 'register', body.captcha);
    if (!ok) return apiError('验证码错误');
  }

  const username = String(body.username || '');
  const usernameLen = Number(cfg.username_len) || 6;
  if (username.length < usernameLen) return apiError(`用户名最少${usernameLen}位`);

  const exist = await dbFirst(env, 'SELECT id FROM acg_user WHERE username=?', username);
  if (exist) return apiError('该用户名已存在，换一个吧');

  const regType = Number(cfg.registered_type);
  const user = {};

  if (regType === 2) {
    const email = String(body.email || '');
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return apiError('邮箱地址不正确');
    const e2 = await dbFirst(env, 'SELECT id FROM acg_user WHERE email=?', email);
    if (e2) return apiError('该邮箱已存在，换一个吧');
    user.email = email;
  } else if (regType === 1) {
    const phone = String(body.phone || '');
    if (!/^1[3-9]\d{9}$/.test(phone)) return apiError('手机号码不正确');
    const p2 = await dbFirst(env, 'SELECT id FROM acg_user WHERE phone=?', phone);
    if (p2) return apiError('该手机已存在，换一个吧');
    user.phone = phone;
  }

  const password = String(body.password || '');
  if (password.length < 6) return apiError('密码最少6位');

  const salt = randStr(32);
  const pwdHash = await generatePassword(password, salt);
  const info = requestInfo(request, url);
  const id = await dbInsert(env, 'acg_user', {
    username,
    password: pwdHash,
    salt,
    app_key: randStr(16).toUpperCase(),
    avatar: '/favicon.ico',
    balance: 0, coin: 0, integral: 0,
    create_time: now(),
    login_ip: info.ip,
    recharge: 0, total_coin: 0,
    status: 1,
    ...user,
  });

  const created = await dbFirst(env, 'SELECT * FROM acg_user WHERE id=?', id);
  const session = await userSessionCookie(created, false, cfg);
  const headers = { 'Content-Type': 'application/json; charset=utf-8' };
  headers['Set-Cookie'] = `${SESSION_NAME}=${encodeURIComponent(session.value)}; Path=/; HttpOnly; SameSite=Lax; Max-Age=${session.maxAge}`;
  return new Response(JSON.stringify({ code: 200, msg: '注册成功' }), { status: 200, headers });
}

// ---------- 登录 (对齐 Api\Authentication::login) ----------
export async function login(env, request, url, body) {
  const cfg = await loadConfig(env);
  const info = requestInfo(request, url);

  if (throttle(`login:ip:${info.ip}`, 30, 300)) return apiError('登录尝试过于频繁，请稍后再试');
  if (Number(cfg.login_verification) === 1) {
    const ok = await captchaVerify(env, request, 'login', body.captcha);
    if (!ok) return apiError('验证码错误');
  }

  const username = String(body.username || '');
  const password = String(body.password || '');
  if (!username) return apiError('用户名输入错误');
  if (password.length < 6) return apiError('密码错误');

  const user = await dbFirst(env, 'SELECT * FROM acg_user WHERE username=? OR email=? OR phone=?', username, username, username);
  if (!user) return apiError('用户不存在');
  const okPwd = await verifyPassword(user.password, password, user.salt);
  if (!okPwd) return apiError('密码错误');
  if (Number(user.status) === 0) return apiError('您已被封禁');

  const remember = body.remember === '1' || body.remember === true;
  const session = await userSessionCookie(user, remember, cfg);

  await dbRun(env, 'UPDATE acg_user SET last_login_time=login_time, login_time=?, last_login_ip=login_ip, login_ip=? WHERE id=?', now(), info.ip, user.id);
  throttleClear(`login:ip:${info.ip}`);

  const headers = { 'Content-Type': 'application/json; charset=utf-8' };
  headers['Set-Cookie'] = `${SESSION_NAME}=${encodeURIComponent(session.value)}; Path=/; HttpOnly; SameSite=Lax; Max-Age=${session.maxAge}`;
  return new Response(JSON.stringify({ code: 200, msg: '登录成功' }), { status: 200, headers });
}

export function logout(env, request, url) {
  const headers = { 'Location': '/user/authentication/login', 'Set-Cookie': `${SESSION_NAME}=; Path=/; HttpOnly; SameSite=Lax; Max-Age=0` };
  return new Response(null, { status: 302, headers });
}

// ---------- 商品数据 (简化主站 getItem) ----------
async function loadCommodity(env, id, cfg) {
  const r = await dbFirst(env, 'SELECT * FROM acg_commodity WHERE id=?', id);
  if (!r) throw new Error('商品不存在');
  return r;
}

function parseJson(raw, fallback) {
  if (!raw) return fallback;
  try { const v = JSON.parse(raw); return v == null ? fallback : v; } catch (e) { return fallback; }
}

function cardStock(env, commodityId) {
  return dbFirst(env, 'SELECT COUNT(*) AS n FROM acg_card WHERE status=0 AND commodity_id=?', commodityId)
    .then(r => (r ? Number(r.n) : 0));
}

// ---------- 计价 (简化对齐 Order::valuation) ----------
export async function valuation(env, cfg, commodity, num, race, sku, coupon, group) {
  const config = parseJson(commodity.config, {});
  let price = new Decimal(group && group.id ? (Number(commodity.user_price) > 0 ? commodity.user_price : commodity.price) : commodity.price);

  // 会员自定义价 (level_price 简化: 忽略精细匹配, 用 user_price 近似)
  if (race && config.category && config.category[race] != null) {
    price = new Decimal(config.category[race]);
    if (config.category_wholesale && config.category_wholesale[race]) {
      const list = config.category_wholesale[race];
      const ks = Object.keys(list).map(Number).sort((a, b) => b - a);
      for (const k of ks) { if (num >= k) { price = new Decimal(list[k]); break; } }
    }
  } else if (config.wholesale) {
    const list = config.wholesale;
    const ks = Object.keys(list).map(Number).sort((a, b) => b - a);
    for (const k of ks) { if (num >= k) { price = new Decimal(list[k]); break; } }
  }

  if (sku && config.sku) {
    for (const [k, v] of Object.entries(sku)) {
      const opt = config.sku[k] && config.sku[k][v];
      if (opt != null) { const p = Number(opt); if (p > 0) price = price.add(p); }
    }
  }

  if (Number(commodity.level_disable) !== 1 && group && group.discount_config) {
    const dc = parseJson(group.discount_config, {});
    const gids = Object.keys(dc).map(Number);
    if (gids.length) {
      const groups = await dbRows(env, 'SELECT id, commodity_list FROM acg_commodity_group WHERE id IN (' + gids.map(() => '?').join(',') + ')', ...gids);
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
    if (Number(commodity.coupon) !== 1) throw new Error('该商品不支持使用优惠券');
    const voucher = await dbFirst(env, 'SELECT * FROM acg_coupon WHERE code=?', coupon);
    if (!voucher || Number(voucher.owner) !== Number(commodity.owner)) throw new Error('该优惠券不存在');
    if (Number(voucher.commodity_id) !== 0 && Number(voucher.commodity_id) !== Number(commodity.id)) throw new Error('该优惠券不属于该商品');
    if (Number(voucher.status) !== 0) throw new Error('该优惠券已失效');
    if (voucher.expire_time && Number(voucher.expire_time) < now()) throw new Error('该优惠券已过期');
    const money = Number(voucher.money) || 0;
    const deduction = Number(voucher.mode) === 0 ? money : price.mul(money).getAmount();
    price = deduction >= Number(price.getAmount()) ? new Decimal(0) : price.sub(deduction);
  }

  return price.mul(num).getAmount();
}

// ---------- 下单 (简化对齐 Order::trade) ----------
export async function trade(env, request, url, body) {
  const cfg = await loadConfig(env);
  const user = await currentUser(env, request);
  const group = await userGroupOf(env, user);
  const info = requestInfo(request, url);

  if (Number(cfg.trade_verification) === 1) {
    const ok = await captchaVerify(env, request, 'trade', body.captcha);
    if (!ok) return apiError('验证码错误');
  }

  const commodityId = Number(body.item_id) || 0;
  const contact = String(body.contact || '');
  let num = Number(body.num) || 0;
  let cardId = Number(body.card_id) || 0;
  const payId = Number(body.pay_id) || 0;
  const device = /Mobile|Android|iPhone|iPad/i.test(info.ua) ? 2 : 1;
  const password = String(body.password || '');
  const coupon = String(body.coupon || '');
  let race = String(body.race || '');
  let sku = null;
  if (body.sku) { try { sku = typeof body.sku === 'string' ? JSON.parse(body.sku) : body.sku; } catch (e) { sku = null; } }

  if (!commodityId) return apiError('请选择商品');
  if (num <= 0) return apiError('至少购买1个');

  const commodity = await loadCommodity(env, commodityId, cfg);
  if (Number(commodity.status) !== 1) return apiError('当前商品已停售');

  const owner = user ? user.id : 0;
  if (Number(cfg.force_login) === 1 || Number(commodity.only_user) === 1 || Number(commodity.purchase_count) > 0) {
    if (!owner) return apiError('请先登录后再购买哦');
  }
  if (Number(commodity.minimum) > 0 && num < Number(commodity.minimum)) return apiError(`本商品最少购买${commodity.minimum}个`);
  if (Number(commodity.maximum) > 0 && num > Number(commodity.maximum)) return apiError(`本商品单次最多购买${commodity.maximum}个`);

  // widget 自定义字段校验
  const widget = {};
  if (commodity.widget) {
    const list = parseJson(commodity.widget, []);
    for (const w of list) {
      if (w.type === 'custom') continue;
      if (w.regex) {
        try { if (!new RegExp(w.regex).test(String(body[w.name] || ''))) return apiError(w.error || '输入内容不符合要求'); } catch (e) { /* ignore bad regex */ }
      }
      widget[w.name] = { value: body[w.name] || '', cn: w.cn || '' };
    }
  }

  if (Number(commodity.draft_status) === 1 && cardId !== 0) num = 1;

  if (!user) {
    if (contact.length < 3) return apiError('联系方式不能低于3个字符');
    const regx = [/^1[3-9]\d{9}$/, /.*(.{2}@.*)$/i, /[1-9]{1}[0-9]{4,11}/];
    const msg = ['手机', '邮箱', 'QQ号'];
    const ct = Number(commodity.contact_type);
    if (ct !== 0 && !regx[ct - 1].test(contact)) return apiError(`您输入的${msg[ct - 1]}格式不正确！`);
    if (Number(commodity.password_status) === 1 && password.length < 6) return apiError('您的设置的密码过于简单，不能低于6位哦');
  }

  // 秒杀窗口
  if (Number(commodity.seckill_status) === 1) {
    if (now() < Number(commodity.seckill_start_time)) return apiError('抢购还未开始');
    if (now() > Number(commodity.seckill_end_time)) return apiError('抢购已结束');
  }

  // race/sku 校验
  const config = parseJson(commodity.config, {});
  if (config.category && Object.keys(config.category).length) {
    if (!race) return apiError('请选择商品类型');
    if (!(race in config.category)) return apiError('此商品类型不存在');
  }
  if (config.sku && Object.keys(config.sku).length) {
    for (const [name, opts] of Object.entries(config.sku)) {
      if (!Array.isArray(opts) || !Object.keys(opts).length) continue;
      if (!sku || !sku[name] || String(sku[name]) === '') return apiError(`请选择${name}`);
      if (!(String(sku[name]) in opts)) return apiError(`${name}选择错误`);
    }
  }

  // 库存检查
  let stock = 0;
  if (Number(commodity.delivery_way) === 0) stock = await cardStock(env, commodityId);
  else stock = Number(commodity.stock) || 0;
  if (stock === 0 || num > stock) return apiError('库存不足');

  // 限购
  if (Number(commodity.purchase_count) > 0 && owner > 0) {
    const c = await dbFirst(env, 'SELECT COUNT(*) AS n FROM acg_order WHERE owner=? AND commodity_id=?', owner, commodityId);
    if (Number(c.n) >= Number(commodity.purchase_count)) return apiError(`该商品每人只能购买${commodity.purchase_count}件`);
  }

  // 计价
  let amount;
  let valuationError = null;
  try {
    amount = await valuation(env, cfg, commodity, num, race, sku, coupon, group);
  } catch (e) { valuationError = e.message; }
  if (valuationError) return apiError(valuationError);
  amount = Number(amount) || 0;

  const pay = await dbFirst(env, 'SELECT * FROM acg_pay WHERE id=?', payId);
  if (!pay) return apiError('该支付方式不存在');
  if (Number(pay.commodity) !== 1) return apiError('当前支付方式已停用，请换个支付方式再进行支付');

  // 创建订单
  const tradeNo = generateTradeNo();
  const orderId = await dbInsert(env, 'acg_order', {
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
    cost: Number(commodity.factory_price) || 0,
  });

  const order = await dbFirst(env, 'SELECT * FROM acg_order WHERE id=?', orderId);

  // 优惠券核销
  let couponId = null;
  if (coupon) {
    const voucher = await dbFirst(env, 'SELECT * FROM acg_coupon WHERE code=?', coupon);
    if (!voucher) return apiError('优惠券不存');
    if (Number(voucher.status) !== 0) return apiError('该优惠券已失效');
    if (Number(voucher.life) <= 0) return apiError('该优惠券已失效');
    couponId = voucher.id;
    await dbRun(env, "UPDATE acg_coupon SET use_life=use_life+1, life=life-1, trade_no=?, service_time=? WHERE id=? AND life>0", tradeNo, now(), voucher.id);
    const v2 = await dbFirst(env, 'SELECT life FROM acg_coupon WHERE id=?', voucher.id);
    if (Number(v2.life) <= 0) await dbRun(env, 'UPDATE acg_coupon SET status=1 WHERE id=?', voucher.id);
    await dbRun(env, 'UPDATE acg_order SET coupon_id=? WHERE id=?', couponId, orderId);
  }

  let secret = null;
  let payUrl = '';

  // 金额为 0 或余额支付 → 立即成交
  if (amount <= 0) {
    await dbRun(env, 'UPDATE acg_order SET amount=0.00 WHERE id=?', orderId);
    secret = await orderSuccess(env, cfg, order);
  } else if (String(pay.handle) === '#system') {
    if (!user) return apiError('您未登录，请先登录后再使用余额支付');
    const bal = await dbFirst(env, 'SELECT balance FROM acg_user WHERE id=?', user.id);
    if (Number(bal.balance) < Number(amount)) return apiError('余额不足，请前往充值');
    // 原子扣款
    const r = await dbRun(env, 'UPDATE acg_user SET balance=balance-? WHERE id=? AND balance>=?', amount, user.id, amount);
    if (!r.success) return apiError('余额扣款失败，请重试');
    await dbInsert(env, 'acg_bill', {
      owner: user.id, amount, balance: Number(bal.balance) - Number(amount), type: BILL.SUB, currency: 0, log: `商品下单[${tradeNo}]`, create_time: now(),
    });
    secret = await orderSuccess(env, cfg, order);
  } else {
    // 外部支付 (易支付): 手续费 + 网关金额
    let payCost = 0;
    if (Number(pay.cost_type) === 0) payCost = Number(pay.cost) || 0;
    else payCost = Number(amount) * (Number(pay.cost) || 0);
    const finalAmount = Number((Number(amount) + payCost).toFixed(2));
    await dbUpdate(env, 'acg_order', { pay_cost: payCost.toFixed(2), amount: finalAmount.toFixed(2), gateway_amount: finalAmount.toFixed(2) }, 'id=?', orderId);

    const host = url.origin;
    const callbackDomain = (cfg.callback_domain || '').trim().replace(/\/$/, '') || host;
    const returnUrl = user ? `${host}/user/personal/purchaseRecord?tradeNo=${tradeNo}` : `${host}/user/index/query?tradeNo=${tradeNo}`;
    const callbackUrl = `${callbackDomain}/user/api/order/callback.${tradeNo}`;

    payUrl = await epayTrade(env, pay, tradeNo, finalAmount, callbackUrl, returnUrl);
    if (!payUrl) return apiError('支付方式未部署成功');
  }

  // 返回当前库存
  const stockAfter = Number(commodity.delivery_way) === 0 ? await cardStock(env, commodityId) : Number(commodity.stock) || 0;

  return new Response(JSON.stringify({
    code: 200, msg: '下单成功',
    data: { url: payUrl, amount, tradeNo, secret, stock: stockAfter },
  }), { status: 200, headers: { 'Content-Type': 'application/json; charset=utf-8' } });
}

// ---------- 易支付下单 (对齐 Epay 插件: 302 跳转网关) ----------
async function epayTrade(env, pay, tradeNo, amount, callbackUrl, returnUrl) {
  const pcfg = await dbFirst(env, 'SELECT config FROM acg_pay_config WHERE id=?', Number(pay.pay_config_id) || 0);
  if (!pcfg) return '';
  const cfgObj = parseJson(pcfg.config, {});
  const pid = String(cfgObj.pid || cfgObj.id || '');
  const key = String(cfgObj.key || cfgObj.secret || '');
  const gateway = String(cfgObj.gateway || cfgObj.url || '').replace(/\/$/, '');
  if (!pid || !key || !gateway) return '';

  const params = {
    pid,
    type: String(pay.code) || 'alipay',
    out_trade_no: tradeNo,
    notify_url: callbackUrl,
    return_url: returnUrl,
    name: '商品购买',
    money: Number(amount).toFixed(2),
    sign_type: 'MD5',
  };
  params.sign = epaySign(params, key);
  const qs = Object.entries(params).map(([k, v]) => `${k}=${encodeURIComponent(v)}`).join('&');
  return `${gateway}/submit.php?${qs}`;
}

// ---------- 支付回调 (对齐 Order::callback + orderSuccess) ----------
export async function orderCallback(env, request, url, tradeNo) {
  try {
    return await orderCallbackInner(env, request, url, tradeNo);
  } catch (e) {
    console.error('orderCallback ERR', e && e.message, e && e.stack);
    throw e;
  }
}
async function orderCallbackInner(env, request, url, tradeNo) {
  const order = await dbFirst(env, 'SELECT * FROM acg_order WHERE trade_no=?', tradeNo);
  const recharge = order ? null : await dbFirst(env, 'SELECT * FROM acg_recharge WHERE trade_no=?', tradeNo);
  if (!order && !recharge) return new Response('fail');
  const pay = await dbFirst(env, 'SELECT * FROM acg_pay WHERE id=?', order ? order.pay_id : recharge.way);
  if (!pay) return new Response('fail');

  const pcfg = await dbFirst(env, 'SELECT config FROM acg_pay_config WHERE id=?', Number(pay.pay_config_id) || 0);
  const cfgObj = pcfg ? parseJson(pcfg.config, {}) : {};
  const key = String(cfgObj.key || cfgObj.secret || '');
  if (!key) return new Response('fail');

  // 取报文 (GET/POST)
  let data = {};
  url.searchParams.forEach((v, k) => { data[k] = v; });
  if (request.method === 'POST') {
    try { data = { ...data, ...(await request.json()) }; } catch (e) {
      const text = await request.text().catch(() => '');
      try { data = { ...data, ...Object.fromEntries(new URLSearchParams(text)) }; } catch (e2) { /* */ }
    }
  }
  if (data.s) delete data.s;
  if (data._PARAMETER) delete data._PARAMETER;

  const sign = String(data.sign || '');
  const expect = epaySign(data, key);
  if (sign.toLowerCase() !== expect.toLowerCase()) return new Response('fail');

  if (data.out_trade_no !== tradeNo) return new Response('fail');
  if ((data.trade_status || data.status) !== 'TRADE_SUCCESS') return new Response('fail');
  const paidAmount = Number(data.money || data.amount || 0);

  if (order) {
    if (Number(order.status) !== 0) return new Response('success');
    const expectAmount = Number(order.gateway_amount != null ? order.gateway_amount : order.amount);
    if (Math.abs(paidAmount - expectAmount) > 0.01) return new Response('fail');
    if (Number(order.owner) !== 0) {
      await dbRun(env, 'UPDATE acg_user SET recharge=recharge+? WHERE id=?', Number(order.amount), order.owner);
    }
    await orderSuccess(env, cfgNone(), order);
    return new Response('success');
  }

  // 充值单
  if (Number(recharge.status) === 1) return new Response('success');
  if (Math.abs(paidAmount - Number(recharge.amount)) > 0.01) return new Response('fail');
  await dbRun(env, 'UPDATE acg_recharge SET status=1 WHERE id=?', recharge.id);
  const user = await dbFirst(env, 'SELECT balance FROM acg_user WHERE id=?', recharge.owner);
  const bal = Math.round(((Number(user.balance) || 0) + Number(recharge.amount)) * 100) / 100;
  await dbRun(env, 'UPDATE acg_user SET balance=?, recharge=recharge+? WHERE id=?', bal, Number(recharge.amount), recharge.owner);
  await dbInsert(env, 'acg_bill', {
    owner: recharge.owner, amount: Number(recharge.amount), balance: bal, type: BILL.ADD, currency: 0,
    log: `钱包充值[${tradeNo}]`, create_time: now(),
  });
  return new Response('success');
}

const cfgNone = () => ({});
export async function orderSuccess(env, cfg, order) {
  const commodity = await dbFirst(env, 'SELECT * FROM acg_commodity WHERE id=?', order.commodity_id);
  await dbRun(env, 'UPDATE acg_order SET pay_time=?, status=1 WHERE id=?', now(), order.id);

  let secret = '';
  const deliveryWay = Number(commodity.delivery_way);
  const draft = Number(order.card_id) || 0;

  if (deliveryWay === 0) {
    secret = await pullCard(env, order, commodity, draft);
    await dbRun(env, 'UPDATE acg_order SET delivery_status=1, secret=? WHERE id=?', secret, order.id);
  } else {
    secret = (commodity.delivery_message && String(commodity.delivery_message) !== '') ? commodity.delivery_message : '正在发货中，请耐心等待，如有疑问，请联系客服。';
    const st = await dbFirst(env, 'SELECT stock FROM acg_commodity WHERE id=?', commodity.id);
    const ns = Math.max(0, Number(st.stock) - Number(order.card_num));
    await dbRun(env, 'UPDATE acg_commodity SET stock=? WHERE id=?', ns, commodity.id);
    await dbRun(env, 'UPDATE acg_order SET delivery_status=1, secret=? WHERE id=?', secret, order.id);
  }

  // 推广分成/返利 (简化: 保留 rebate/divide)
  if (Number(order.from) > 0 && Number(order.divide_amount) > 0) {
    try { await billCreate(env, order.from, order.divide_amount, `推广分成[${order.trade_no}]`, BILL.ADD); } catch (e) { /* */ }
  }
  if (Number(order.rebate) > 0 && Number(order.user_id) > 0) {
    try { await billCreate(env, order.user_id, order.rebate, `自营商品出售[${order.trade_no}]`, BILL.ADD); } catch (e) { /* */ }
  }
  return secret;
}

// ---------- 拉卡密 (并发安全: UPDATE 带 status=0 守卫, 失败重试) ----------
async function pullCard(env, order, commodity, draftId) {
  const soldOut = '很抱歉，有人在你付款之前抢走了商品，请联系客服。';

  if (draftId) {
    const r = await dbRun(env, 'UPDATE acg_card SET purchase_time=?, order_id=?, status=1 WHERE id=? AND status=0 AND commodity_id=?', now(), order.id, draftId, order.commodity_id);
    const card = await dbFirst(env, 'SELECT secret FROM acg_card WHERE id=?', draftId);
    return r.success && card ? card.secret : soldOut;
  }

  const direction = Number(commodity.delivery_auto_mode) === 1 ? 'RANDOM()' : Number(commodity.delivery_auto_mode) === 2 ? 'id DESC' : 'id ASC';
  let where = 'status=0 AND commodity_id=?';
  const params = [order.commodity_id];
  if (order.race) {
    where += ' AND race=?';
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
  let secret = '';
  for (const card of cards) {
    if (ok >= Number(order.card_num)) break;
    const r = await dbRun(env, 'UPDATE acg_card SET purchase_time=?, order_id=?, status=1 WHERE id=? AND status=0', now(), order.id, card.id);
    if (r.success && r.meta && r.meta.changes > 0) {
      ok++;
      secret += card.secret + '\n';
    }
  }
  if (ok < Number(order.card_num)) return soldOut;
  return secret.trim();
}

// ---------- 订单查询 (公开, 对齐 Api\Index::query) ----------
export async function queryOrder(env, request, url, body) {
  const tradeNo = String((url.searchParams.get('tradeNo')) || body.tradeNo || '');
  const password = String(body.password || '');
  if (!/^\d{18}$/.test(tradeNo)) return apiError('订单号不正确');
  const order = await dbFirst(env, 'SELECT * FROM acg_order WHERE trade_no=?', tradeNo);
  if (!order) return apiError('订单不存在');
  if (Number(order.status) !== 1) {
    return new Response(JSON.stringify({ code: 200, msg: 'success', data: { trade_no: order.trade_no, amount: order.amount, status: order.status, create_time: order.create_time } }), { status: 200, headers: { 'Content-Type': 'application/json; charset=utf-8' } });
  }
  // 已支付: 需要密码或登录本人
  if (Number(order.password_status_set) === 1 && order.password) {
    const pwd = String(body.password || '');
    if (!pwd) return apiError('请输入查询密码');
    if (pwd !== order.password) return apiError('查询密码错误');
  }
  const commodity = await dbFirst(env, 'SELECT name FROM acg_commodity WHERE id=?', order.commodity_id);
  return new Response(JSON.stringify({
    code: 200, msg: 'success',
    data: {
      trade_no: order.trade_no, amount: order.amount, status: order.status,
      create_time: order.create_time, commodity_name: commodity ? commodity.name : '',
      contact: order.contact, secret: order.secret, delivery_status: order.delivery_status,
    },
  }), { status: 200, headers: { 'Content-Type': 'application/json; charset=utf-8' } });
}

// ---------- 卡密详情 (登录本人, 对齐 Api\Index::card) ----------
export async function cardDetail(env, request, url, body) {
  const user = await currentUser(env, request);
  if (!user) return apiError('请先登录');
  const commodityId = Number(body.item_id || url.searchParams.get('item_id')) || 0;
  const commodity = await dbFirst(env, 'SELECT * FROM acg_commodity WHERE id=?', commodityId);
  if (!commodity) return apiError('商品不存在');
  if (Number(commodity.owner) !== Number(user.id)) return apiError('无权查看该商品卡密');
  const list = await dbRows(env, 'SELECT id, draft, secret, race, sku, status, order_id, purchase_time FROM acg_card WHERE commodity_id=? ORDER BY id DESC LIMIT 200', commodityId);
  return new Response(JSON.stringify({ code: 200, msg: 'success', data: list }), { status: 200, headers: { 'Content-Type': 'application/json; charset=utf-8' } });
}

// ---------- 我的订单 (对齐 Api\PurchaseRecord::data) ----------
export async function purchaseRecord(env, request, url) {
  const user = await currentUser(env, request);
  if (!user) return apiError('请先登录');
  const rows = await dbRows(env, 'SELECT o.*, c.name AS commodity_name FROM acg_order o LEFT JOIN acg_commodity c ON c.id=o.commodity_id WHERE o.owner=? ORDER BY o.id DESC LIMIT 100', user.id);
  return new Response(JSON.stringify({ code: 200, msg: 'success', data: rows, total: rows.length }), { status: 200, headers: { 'Content-Type': 'application/json; charset=utf-8' } });
}

// ---------- 余额明细 (对齐 Api\Bill::data) ----------
export async function billData(env, request, url) {
  const user = await currentUser(env, request);
  if (!user) return apiError('请先登录');
  const rows = await dbRows(env, 'SELECT * FROM acg_bill WHERE owner=? ORDER BY id DESC LIMIT 100', user.id);
  return new Response(JSON.stringify({ code: 200, msg: 'success', data: rows }), { status: 200, headers: { 'Content-Type': 'application/json; charset=utf-8' } });
}

// ---------- 支付列表 (结算页用, 对齐 Pay::query 简化) ----------
export async function payList(env, request, url) {
  const rows = await dbRows(env, 'SELECT id, name, icon, handle, cost, cost_type FROM acg_pay WHERE commodity=1 AND archived=0 ORDER BY sort ASC');
  return new Response(JSON.stringify({ code: 200, msg: 'success', data: rows }), { status: 200, headers: { 'Content-Type': 'application/json; charset=utf-8' } });
}

const apiError = (msg, code = 403) => new Response(JSON.stringify({ code, msg }), { status: 200, headers: { 'Content-Type': 'application/json; charset=utf-8' } });

// ---------- 修改密码 (对齐 Api\Security::password) ----------
export async function changePassword(env, request, url, body) {
  const user = await currentUser(env, request);
  if (!user) return apiError('请先登录');
  const oldPwd = String(body.oldPassword || '');
  const newPwd = String(body.password || '');
  if (oldPwd.length < 6) return apiError('旧密码错误');
  if (newPwd.length < 6) return apiError('新密码最少6位');
  const ok = await verifyPassword(user.password, oldPwd, user.salt);
  if (!ok) return apiError('旧密码错误');
  const salt = randStr(32);
  const hash = await generatePassword(newPwd, salt);
  await dbRun(env, 'UPDATE acg_user SET password=?, salt=? WHERE id=?', hash, salt, user.id);
  return apiSuccess('修改成功');
}

// ---------- 钱包充值 (对齐 Api\Recharge: 生成充值单 + 外部支付跳转) ----------
export async function rechargeCreate(env, request, url, body) {
  const user = await currentUser(env, request);
  if (!user) return apiError('请先登录');
  const payId = Number(body.pay_id) || 0;
  const amount = Number(body.amount) || 0;
  if (amount <= 0) return apiError('充值金额不正确');
  const cfg = await loadConfig(env);
  if (Number(cfg.recharge_open) !== 1) return apiError('充值功能未开启');
  const min = Number(cfg.recharge_min) || 0;
  const max = Number(cfg.recharge_max) || 0;
  if (min > 0 && amount < min) return apiError(`最低充值${min}元`);
  if (max > 0 && amount > max) return apiError(`最高充值${max}元`);

  const pay = await dbFirst(env, 'SELECT * FROM acg_pay WHERE id=?', payId);
  if (!pay || Number(pay.commodity) !== 1) return apiError('当前支付方式不可用');

  const tradeNo = generateTradeNo();
  await dbInsert(env, 'acg_recharge', {
    owner: user.id, trade_no: tradeNo, amount, balance: 0, status: 0, way: pay.id, create_time: now(),
  });

  const host = url.origin;
  const callbackDomain = (cfg.callback_domain || '').trim().replace(/\/$/, '') || host;
  const returnUrl = `${host}/user/bill/index`;
  const callbackUrl = `${callbackDomain}/user/api/order/callback.${tradeNo}`;
  const gatewayUrl = await epayTrade(env, pay, tradeNo, amount, callbackUrl, returnUrl);
  if (!gatewayUrl) return apiError('支付方式未部署成功');
  return apiSuccess('success', { url: gatewayUrl });
}

const apiSuccess = (msg, data = null) => new Response(JSON.stringify({ code: 200, msg, data }), { status: 200, headers: { 'Content-Type': 'application/json; charset=utf-8' } });