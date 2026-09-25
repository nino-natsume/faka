// lib.js
var htmlEscape = (s) => String(s == null ? "" : s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
var enc = new TextEncoder();
var dec = new TextDecoder();
var MD5_PAD = new Uint8Array(64);
var dbRows = async (env, sql, ...params) => {
  const stmt = env.DB.prepare(sql);
  const res = params.length ? await stmt.bind(...params).all() : await stmt.all();
  return res.results || [];
};
var dbFirst = async (env, sql, ...params) => {
  const rows = await dbRows(env, sql, ...params);
  return rows[0] || null;
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
  return `<script>window._data_var=${JSON.stringify(data)};<\/script>${langDictScript()}`;
}
function itemVar(item) {
  return `<script>window._data_var._var_item=${JSON.stringify(item)};<\/script>`;
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
    <script src="/assets/common/js/ready.js"><\/script>
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
${JS_FILES.map((f) => `<script src="${f}"><\/script>`).join("")}
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
<script src="/assets/user/controller/index/index.js"><\/script>`;
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
<script src="/assets/user/controller/index/item.js"><\/script>`;
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
<script src="/assets/user/controller/index/query.js"><\/script>`;
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
  if (s.startsWith("/user/dashboard") || s.startsWith("/user/authentication") || s.startsWith("/user/personal") || s.startsWith("/user/security") || s.startsWith("/user/recharge") || s.startsWith("/user/bill") || s.startsWith("/user/cash") || s.startsWith("/user/coupon") || s.startsWith("/user/ticket") || s.startsWith("/user/message") || s.startsWith("/user/promote") || s.startsWith("/user/order") || s.startsWith("/user/card") || s.startsWith("/user/commodity") || s.startsWith("/user/category") || s.startsWith("/user/business") || s.startsWith("/user/share")) {
    ctx.route = s;
    return pageRes(`<!DOCTYPE html><html><head><meta charset="utf-8"><title>\u656C\u8BF7\u671F\u5F85</title></head><body style="font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#f5f6fa;color:#333"><div style="text-align:center"><h1 style="font-size:2rem;margin-bottom:.5rem">${htmlEscape(s)}</h1><p>\u4F1A\u5458\u4E2D\u5FC3\u529F\u80FD\u8FC1\u79FB\u4E2D\uFF0C\u656C\u8BF7\u671F\u5F85 (P1 \u9636\u6BB5)</p></div></body></html>`);
  }
  if (s.startsWith("/user/api/")) {
    const parts2 = s.replace(/^\/user\/api\//, "").split("/");
    const apiCtl = parts2[0] || "";
    const apiAct = parts2[1] || "";
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
          const item = await getItem(env, cfg, Number(q.get("commodityId")));
          item.stock_state = stockState(item.stock);
          if (item.inventory_hidden == 1) item.stock = hideStockText(item.stock);
          return jsonRes({ code: 200, msg: "success", data: item });
        } catch (e) {
          return jsonRes({ code: 403, msg: e.message }, 200);
        }
      }
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
