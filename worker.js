// ============================================================
// acg-faka Worker 移植 - worker.js (入口 + 路由 + 前台浏览链路)
// 路由语义对齐原版 kernel/Kernel.php:
//   /item/{id}            -> user/index/item (mid)
//   /cat/{id|recommend}   -> user/index/index (cid)
//   / 或 /user/index/index -> 首页
//   /user/api/index/*     -> JSON API
// 模板结构 100% 还原 Cartoon 主题 (app/View/User/Theme/Cartoon)
// ============================================================
import {
  now, htmlEscape, md5hex, parseCookies, requestInfo, langMenu,
  loadConfig, dbRows, dbFirst, buildCategoryTree, indexVar, itemVar,
} from './lib.js';
import * as api from './api.js';
import {
  authenticateManage, revokeManageSession, adminEndpoint,
} from './admin.js';
import {
  renderAdminLoginPage, renderAdminShell, renderAdminDashboardPage,
  renderAdminCategoryPage, renderAdminCommodityPage, renderAdminCardPage, renderAdminOrderPage, renderAdminUserPage, renderAdminRechargePage, renderAdminCouponPage, renderAdminTicketPage, renderAdminMessagePage, renderAdminCashPage, renderAdminBillPage, renderAdminLogPage,
} from './admin-pages.js';
import {
  renderAuthHeader, renderAuthFooter, pageLogin, pageRegister,
  userCenterShell, pageDashboard, pagePurchaseRecord, pageBill,
  pageRecharge, pageSecurity,
} from './pages.js';

const APP_VERSION = '3.7.9';

// ---------- 视图变量上下文 ----------
async function viewContext(env, request, url, pageTitle, extra = {}) {
  const cfg = await loadConfig(env);
  const info = requestInfo(request, url);
  return {
    cfg,
    info,
    config: cfg,
    title: pageTitle,
    favicon: '/favicon.ico',
    app: { version: APP_VERSION },
    user: null,
    langs: langMenu(),
    nav: [
      { name: '购物', url: '/', icon: 'fa-duotone fa-regular fa-cart-shopping', target: '_self', match: '/user/index/index' },
      { name: '订单查询', url: '/user/index/query', icon: 'fa-duotone fa-regular fa-folders', target: '_self', match: '/user/index/query' },
    ],
    setting: { icp: '' },
    ...extra,
  };
}

// ---------- CSS/JS 清单 (对齐原版模板) ----------
const CSS_FILES = [
  '/assets/common/css/bootstrap.min.css',
  '/assets/common/css/_.css',
  '/assets/common/css/font.min.css',
  '/assets/common/js/layui/css/layui.css',
  '/assets/common/css/select2.min.css',
  '/assets/common/css/component.css',
  '/assets/common/js/table/bootstrap-table.css',
  '/assets/common/js/layer/theme/default/layer.css',
  '/assets/common/css/toastr.min.css',
  '/assets/user/css/index.css',
];

const JS_FILES = [
  '/assets/common/js/_.js',
  '/assets/user/js/_index.js',
  '/assets/common/js/util/dict.js',
  '/assets/common/js/jquery.min.js',
  '/assets/common/js/toastr.min.js',
  '/assets/common/js/component/loading.js',
  '/assets/common/js/util.js',
  '/assets/common/js/layer/layer.js',
  '/assets/common/js/jquery.pjax.min.js',
  '/assets/common/js/jquery.qrcode.min.js',
  '/assets/common/js/format.js',
  '/assets/common/js/message.js',
  '/assets/common/js/component.js',
  '/assets/common/js/layui/layui.js',
  '/assets/common/js/jquery.treegrid.min.js',
  '/assets/common/js/bootstrap/bootstrap.bundle.min.js',
  '/assets/common/js/table/bootstrap-table.min.js',
  '/assets/common/js/table/bootstrap-table-treegrid.min.js',
  '/assets/common/js/component/form.js',
  '/assets/common/js/component/search.js',
  '/assets/common/js/component/xm-select.js',
  '/assets/common/js/component/tree.select.js',
  '/assets/common/js/component/authtree.js',
  '/assets/common/js/component/table.js',
  '/assets/common/js/component/select2.min.js',
  '/assets/common/js/cache.js',
  '/assets/common/js/editor/editor.js',
  '/assets/common/js/editor/code/code.js',
  '/assets/common/js/component/decimal.js',
  '/assets/user/js/trade.js',
  '/assets/user/js/treasure.js',
];

// ---------- Header / Footer (对齐 Cartoon Index/Header.html, Footer.html) ----------
function renderHeader(v, extraScripts = '') {
  const { config, user, nav, langs, title, favicon, app } = v;
  const currency = config.currency_symbol || '¥';
  const navItems = nav.map(n => {
    const active = v.route && n.match && (v.route === n.match || v.route.startsWith(n.match === '/' ? n.match : n.match + '/') || (n.match === '/user/index/index' && (v.route === '/' || v.route.startsWith('/cat') || v.route.startsWith('/item')))) ? ' active' : '';
    return `<li class="nav-item"><a class="nav-link${active}" href="${htmlEscape(n.url)}" target="${htmlEscape(n.target)}"><i class="${htmlEscape(n.icon)} nav-icon"></i>${htmlEscape(n.name)}</a></li>`;
  }).join('');

  const userBox = !user ? `
            <div class="ms-2 user-login-box">
                <a class="btn btn-outline-secondary btn-sm br-12" href="/user/authentication/login"><i class="fa-duotone fa-regular fa-right-to-bracket nav-icon"></i>登录</a>
                <a class="btn btn-primary btn-sm br-12" href="/user/authentication/register"><i class="fa-duotone fa-regular fa-user-plus nav-icon"></i>创建账号</a>
            </div>` : `
            <div class="ms-2 user-info-box">
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <img id="user-avatar" src="${htmlEscape(user.avatar || '/favicon.ico')}" alt="用户头像" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover; background-color: #f8f9fa;">
                        <div class="d-flex flex-column align-items-start me-2">
                            <span id="username" class="fw-bold text-dark" style="font-size: 14px; line-height: 1.2;">${htmlEscape(user.username)}</span>
                            <span id="user-balance" class="text-muted" style="font-size: 12px; line-height: 1.2;">余额: <span class="text-success">${currency}${user.balance}</span></span>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="/user/dashboard/index"><i class="fa-duotone fa-regular fa-user me-2"></i>个人中心</a></li>
                        <li><a class="dropdown-item" href="/user/recharge/index"><i class="fa-duotone fa-regular fa-wallet me-2"></i>钱包充值</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/user/personal/purchaseRecord"><i class="fa-duotone fa-regular fa-receipt me-2"></i>我的订单</a></li>
                        <li><a class="dropdown-item" href="/user/security/personal"><i class="fa-duotone fa-regular fa-gear me-2"></i>设置</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/user/authentication/logout"><i class="fa-duotone fa-regular fa-sign-out me-2"></i>退出登录</a></li>
                    </ul>
                </div>
            </div>`;

  const langItems = langs.map(l => `<li><a class="dropdown-item" href="javascript:;" data-lang-value="${htmlEscape(l.code)}">${htmlEscape(l.name)}</a></li>`).join('');

  return `<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="keywords" content="${htmlEscape(config.keywords || '')}"/>
    <meta name="description" content="${htmlEscape(config.description || '')}"/>
    <link href="${favicon}?v=${app.version}" rel="icon">
    <title>${htmlEscape(title)} - ${htmlEscape(config.shop_name)}</title>
    ${CSS_FILES.map(f => `<link href="${f}" rel="stylesheet">`).join('')}
    <script src="/assets/common/js/ready.js"></script>
    ${extraScripts}
</head>
<body style="background-size: cover;background-image: linear-gradient(180deg, rgb(255 255 255 / 0%), rgb(255 255 255 / 71%)), url('${htmlEscape(config.background_url || '')}')">
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
                    <input class="form-control item-search-input" type="search" placeholder="搜索商品关键词.." aria-label="Search">
                </div>
            </div>
        </div>

        <div class="ms-2 dropdown lang-switch">
            <button class="btn lang-switch__btn" type="button" id="langDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="语言">
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
${v.setting && v.setting.icp ? `<footer>${htmlEscape(v.setting.icp)}</footer>` : ''}
${JS_FILES.map(f => `<script src="${f}"></script>`).join('')}
</body>
</html>`;
}

// ---------- 商品列表数据层 (对齐 Shop::getCategory + Api\Index::commodity 主站分支) ----------
async function getVisibleCategories(env, cfg) {
  const rows = await dbRows(env, `SELECT * FROM acg_category WHERE status=1 AND hide=0 ORDER BY sort ASC`);
  const list = rows.map(c => ({
    id: c.id, name: c.name, sort: c.sort, icon: c.icon || '/favicon.ico', pid: c.pid ? Number(c.pid) : 0, owner: c.owner || 0,
  }));
  return buildCategoryTree(list);
}

async function getCommodityList(env, cfg, params) {
  const { keywords = '', limit = 0, page = 1, categoryId = 0 } = params;
  const lmt = Math.max(0, Number(limit) || 0);
  const pg = Math.max(1, Number(page) || 1);

  // 可见分类 id 集合
  const cats = await getVisibleCategories(env, cfg);
  const catIds = [];
  const flat = (arr) => arr.forEach(c => { catIds.push(String(c.id)); c.children && flat(c.children); });
  flat(cats);

  // 主站可见商品: owner=0 (substation_display=0 简化为主站)
  let where = `status=1 AND owner=0`;
  const bind = [];
  if (String(categoryId) === 'recommend') {
    where += ` AND recommend=1`;
  } else if (Number(categoryId) !== 0) {
    where += ` AND category_id=?`;
    bind.push(Number(categoryId));
  }
  if (keywords !== '') {
    where += ` AND name LIKE ?`;
    bind.push(`%${keywords}%`);
  }

  const rows = await dbRows(env, `SELECT id, name, cover, status, delivery_way, price, user_price, level_disable, level_price, hide, owner, inventory_hidden, recommend, category_id, stock, shared_id, tags, seckill_status, seckill_start_time, seckill_end_time, config FROM acg_commodity WHERE ${where} ORDER BY sort ASC`, ...bind);

  const list = [];
  for (const r of rows) {
    if (!catIds.includes(String(r.category_id))) continue;      // 隐藏分类中的商品不展示
    if (r.hide == 1) continue;                                   // 商品自身隐藏

    const item = {
      id: r.id,
      name: r.name,
      cover: r.cover || '/favicon.ico',
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
      has_wholesale: false,
    };

    // 已售件数 (delivery_status=1)
    const soldRow = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_order WHERE commodity_id=? AND delivery_status=1`, r.id);
    item.order_sold = soldRow ? Number(soldRow.n) : 0;

    // 库存: 自动发货统计卡密
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
  } catch (e) { return []; }
}

function stockState(stock) {
  stock = Number(stock) || 0;
  return stock <= 0 ? 0 : stock <= 5 ? 1 : stock <= 20 ? 2 : stock <= 100 ? 3 : 4;
}

function hideStockText(stock) {
  stock = Number(stock) || 0;
  return stock <= 0 ? '已售罄' : stock <= 5 ? '即将售罄' : stock <= 20 ? '一般' : stock <= 100 ? '充足' : '非常多';
}

// ---------- 商品详情数据层 (对齐 Shop::getItem 主站简化) ----------
async function getItem(env, cfg, id) {
  const r = await dbFirst(env, `SELECT id, name, description, only_user, purchase_count, category_id, cover, price, user_price, status, owner, delivery_way, contact_type, password_status, level_price, level_disable, coupon, shared_id, shared_code, shared_premium, shared_premium_type, seckill_status, seckill_start_time, seckill_end_time, draft_status, draft_premium, inventory_hidden, widget, minimum, maximum, shared_sync, config, stock, code, shared_amount_sync, shared_config_sync, tags FROM acg_commodity WHERE id=?`, id);
  if (!r) throw new Error('商品不存在');
  if (r.status != 1) throw new Error('该商品暂未上架');

  let cfgObj = {};
  try { cfgObj = r.config ? JSON.parse(r.config) : {}; } catch (e) { cfgObj = {}; }

  // 库存: 自动发货统计卡密
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
    description: r.description || '',
    only_user: r.only_user || 0,
    purchase_count: r.purchase_count || 0,
    category_id: r.category_id,
    cover: r.cover || '/favicon.ico',
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
    service_url: cfg.service_url || '',
    service_qq: cfg.service_qq || '',
    tags: parseTags(r.tags),
    config: {
      category: cfgObj.category || {},
      sku: cfgObj.sku || {},
    },
    widget: r.widget ? parseTags(r.widget) : [],
    share_url: '',
    login: false,
    trade_captcha: Number(cfg.trade_verification) || 0,
  };

  const soldRow = await dbFirst(env, `SELECT COUNT(*) AS n FROM acg_order WHERE commodity_id=? AND delivery_status=1`, r.id);
  item.order_sold = soldRow ? Number(soldRow.n) : 0;

  return item;
}

// ---------- widget 渲染 (对齐 Helper::widget_render) ----------
function renderWidget(widgets) {
  if (!Array.isArray(widgets) || widgets.length === 0) return '';
  let html = '';
  for (const w of widgets) {
    if ((w.type || '') === 'custom') {
      const n = htmlEscape(w.name || '');
      if (n !== '') html += `<div class="acg-widget-custom acg-widget-custom-${n}" data-widget-custom="${n}"></div>`;
      continue;
    }
    const dict = {};
    if (w.dict) {
      for (const pair of String(w.dict).split(',')) {
        const [k, v] = pair.split('=').map(s => s.trim());
        if (k !== '' && v !== '') dict[v] = k;
      }
    }
    html += `<div><label class="form-label mb-1">${htmlEscape(w.cn || '')}</label>`;
    if (['text', 'password', 'number'].includes(w.type)) {
      html += `<input type="${htmlEscape(w.type)}" class="form-control" name="${htmlEscape(w.name || '')}" placeholder="${htmlEscape(w.placeholder || '')}">`;
    } else if (w.type === 'select') {
      let opt = `<option value="">${htmlEscape(w.placeholder || '')}</option>`;
      for (const [k, val] of Object.entries(dict)) opt += `<option value="${htmlEscape(k)}">${htmlEscape(val)}</option>`;
      html += `<select class="form-control" name="${htmlEscape(w.name || '')}">${opt}</select>`;
    } else if (w.type === 'checkbox' || w.type === 'radio') {
      html += '<div>';
      for (const [k, val] of Object.entries(dict)) {
        const checked = w.type === 'radio' && k === Object.keys(dict)[0] ? ' checked' : '';
        html += `<div class="form-check form-check-inline">
  <input class="form-check-input" name="${htmlEscape(w.name || '')}${w.type === 'checkbox' ? '[]' : ''}" type="${w.type}" id="${w.type}-${htmlEscape(k)}" value="${htmlEscape(k)}"${checked}>
  <label class="form-check-label" for="${w.type}-${htmlEscape(k)}">${htmlEscape(val)}</label>
</div>`;
      }
      html += '</div>';
    } else if (w.type === 'textarea') {
      html += `<textarea class="form-control" name="${htmlEscape(w.name || '')}" rows="3"></textarea>`;
    }
    html += '</div>';
  }
  return html;
}

function contactTypeMsg(type) {
  return Number(type) === 1 ? '手机号' : Number(type) === 2 ? '邮箱地址' : Number(type) === 3 ? 'QQ号' : '联系方式';
}

// ---------- 页面: 首页 (对齐 Index/Index.html) ----------
function pageIndex(v) {
  const { config } = v;
  const chips = v.category.map((c, i) => {
    const active = (i === 0 && Number(v.categoryId) === 0) || Number(v.categoryId) === c.id;
    const icon = String(c.id) !== 'recommend' ? `<span class="chip-icon" style="background: url('${htmlEscape(c.icon)}') center/cover no-repeat;"></span>` : '';
    return `<a data-id="${c.id}" class="switch-category chip ${active ? 'is-primary' : ''}" href="javascript:void(0);">${icon}${htmlEscape(c.name)}</a>`;
  }).join('');

  return `<main class="container py-4">
  <!-- 公告面板 -->
  <div class="panel">
    <div class="panel-header">
      <span class="icon"><i class="fa-duotone fa-regular fa-bullhorn"></i></span>
      <h6 class="panel-title">公告</h6>
    </div>
    <div class="panel-body">
        ${config.notice || ''}
    </div>
  </div>

  <!-- 购买面板 -->
  <div class="panel">
    <div class="panel-header">
      <span class="icon"><i class="fa-duotone fa-regular fa-cart-shopping"></i></span>
      <h6 class="panel-title">购买</h6>
    </div>
    <div class="panel-body">
      <div class="mb-3">
        <div class="chip-list">
            ${chips}
        </div>
      </div>
      <div class="row item-list">
          <div class="item-message">努力加载中..</div>
      </div>
    </div>
  </div>
</main>
<script src="/assets/user/controller/index/index.js"></script>`;
}

// ---------- 页面: 商品详情 (对齐 Index/Item.html) ----------
function pageItem(v) {
  const { item, config } = v;
  const deliveryText = item.delivery_way == 0 ? '自动发货' : '在线发货';
  const stockText = item.inventory_hidden == 1 ? item.stock : `${item.stock}`;

  let raceHtml = '';
  if (item.config && item.config.category && Object.keys(item.config.category).length) {
    const races = Object.entries(item.config.category);
    raceHtml = `<div>
        <label class="form-label mb-1">宝贝类型</label>
        <div class="sku-list">
          ${races.map(([race, price], i) => `<a class="switch-race sku ${i === 0 ? 'is-primary' : ''}" data-sku="${htmlEscape(race)}" data-price="${htmlEscape(price)}" href="javascript:void(0);">${htmlEscape(race)}<span class="badge-money">${config.currency_symbol || '¥'}${htmlEscape(price)}</span></a>`).join('')}
        </div>
    </div>`;
  }

  let skuHtml = '';
  if (item.config && item.config.sku && Object.keys(item.config.sku).length) {
    skuHtml = Object.entries(item.config.sku).map(([name, sku]) => {
      const options = Object.entries(sku);
      return `<div>
        <label class="form-label mb-1">${htmlEscape(name)}</label>
        <div class="sku-list">
          ${options.map(([key, price], i) => `<a class="switch-sku sku ${i === 0 ? 'is-primary' : ''}" data-sku="${htmlEscape(name)}" data-value="${htmlEscape(key)}" data-price="${htmlEscape(price)}" href="javascript:void(0);">${htmlEscape(key)}${Number(price) > 0 ? `<span class="badge-money">+${config.currency_symbol || '¥'}${htmlEscape(price)}</span>` : ''}</a>`).join('')}
        </div>
    </div>`;
    }).join('');
  }

  const draftHtml = item.draft_status == 1 ? `<div>
        <input type="hidden" name="card_id" class="form-control">
        <label class="form-label mb-1">自选账号</label>
        <button type="button" class="optional-card">未自选,将随机发货</button>
    </div>` : '';

  const contactHtml = !v.user ? `<div>
        <label class="form-label mb-1">${contactTypeMsg(item.contact_type)}</label>
        <input type="text" name="contact" class="form-control" placeholder="请输入您的${contactTypeMsg(item.contact_type)}">
    </div>` : '';

  const couponHtml = item.coupon == 1 ? `<div>
        <label class="form-label mb-1">优惠券</label>
        <input type="text" class="form-control" name="coupon" placeholder="优惠券代码，没有则不填">
    </div>` : '';

  const widgetHtml = renderWidget(item.widget || []);

  const pwdHtml = item.password_status == 1 ? `<div>
        <label class="form-label mb-1">查询密码</label>
        <input type="password" class="form-control" name="password" placeholder="设置查询订单的密码">
    </div>` : '';

  const numValue = item.minimum > 0 ? item.minimum : 1;

  const captchaHtml = item.trade_captcha == 1 ? `<div>
        <label class="form-label mb-1">人机验证</label>
        <div class="input-group" style="width: 240px;">
            <input type="text" class="form-control captcha-input" placeholder="图形验证码" name="captcha">
            <div class="input-group-append">
                <img class="input-group-text captcha-img" src="/user/captcha/image?action=trade"/>
            </div>
        </div>
    </div>` : '';

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
                            ${item.seckill_status == 1 ? `<span class="badge-soft snap-up" style="display: none;"></span>` : ''}
                            <span class="badge-soft badge-soft-success">${deliveryText}</span>
                            <span class="badge-soft badge-soft-primary">已售 ${item.order_sold}</span>
                            <span class="badge-soft badge-soft-success item-stock">库存 ${stockText}</span>
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
                                <label class="form-label mb-1">购买数量</label>
                                <div class="input-group qty-group">
                                    <button type="button" class="change-num-sub">-</button>
                                    <input type="number" class="form-control text-center" name="num" value="${numValue}">
                                    <button type="button" class="change-num-add">+</button>
                                </div>
                            </div>

                            ${captchaHtml}

                            <div class="cash-pay p-2" style="display: none;">
                                <label class="form-label mb-2"><i class="fa-duotone fa-regular fa-cart-shopping"></i> 付款</label>
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
            <h6 class="panel-title">宝贝详情</h6>
        </div>
        <div class="panel-body">
            ${item.description}
        </div>
    </div>


</main>
<script src="/assets/user/controller/index/item.js"></script>`;
}

// ---------- 页面: 订单查询 (对齐 Index/Query.html) ----------
function pageQuery(v) {
  return `<main class="container py-4">
    <div class="panel">
        <div class="panel-header">
            <span class="icon"><i class="fa-duotone fa-regular fa-folders"></i></span>
            <h6 class="panel-title">订单查询</h6>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-12 col-lg-6 offset-lg-3">
                    <form class="vstack gap-3" id="queryForm">
                        <div>
                            <label class="form-label mb-1">订单号</label>
                            <input type="text" class="form-control" name="tradeNo" value="${htmlEscape(v.tradeNo || '')}" placeholder="请输入订单号">
                        </div>
                        <div id="queryResult"></div>
                        <button type="button" class="btn btn-primary btn-block query-submit">查询</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="/assets/user/controller/index/query.js"></script>`;
}

// ---------- 页面: 维护 (对齐 Index/Closed.html 简化) ----------
function pageClosed(v) {
  return `<main class="container py-5">
    <div class="panel">
        <div class="panel-body text-center py-5">
            <i class="fa-duotone fa-regular fa-screwdriver-wrench fa-3x mb-3"></i>
            <h4>${htmlEscape(v.config.closed_message || '我们正在升级，请耐心等待完成。')}</h4>
        </div>
    </div>
</main>`;
}

// ---------- API 响应 ----------
const jsonRes = (data, status = 200) => new Response(JSON.stringify(data), {
  status, headers: { 'Content-Type': 'application/json; charset=utf-8', 'X-Content-Type-Options': 'nosniff' },
});

async function readBody(request) {
  if (request.method === 'GET' || request.method === 'HEAD') return {};
  try {
    const ct = (request.headers.get('Content-Type') || '').toLowerCase();
    if (ct.includes('application/json')) return await request.json();
    if (ct.includes('application/x-www-form-urlencoded') || ct.includes('multipart/form-data')) {
      const form = await request.formData();
      const out = {};
      form.forEach((v, k) => { out[k] = v; });
      return out;
    }
    const text = await request.text();
    if (text) { try { return JSON.parse(text); } catch (e) { /* fallthrough */ } }
    return {};
  } catch (e) { return {}; }
}

const pageRes = (html) => new Response(html, {
  status: 200,
  headers: {
    'Content-Type': 'text/html; charset=utf-8',
    'X-Content-Type-Options': 'nosniff',
    'X-Frame-Options': 'SAMEORIGIN',
    'Referrer-Policy': 'strict-origin-when-cross-origin',
  },
});

// ---------- 路由 (对齐 Kernel) ----------
async function route(env, request, url, ctx) {
  const pathname = url.pathname;
  let s = pathname;
  let mid = null;
  let cid = null;

  // 伪静态改写
  const itemM = pathname.match(/^\/item\/(\d+)\/?$/);
  if (itemM) { s = '/user/index/item'; mid = Number(itemM[1]); }
  const catM = pathname.match(/^\/cat\/(\d+|recommend)\/?$/);
  if (catM) { s = '/user/index/index'; cid = catM[1]; }

  if (!s || s === '/') s = '/user/index/index';
  if (s === '/admin') return new Response(null, { status: 302, headers: { Location: '/admin/authentication/login' } });

  const q = url.searchParams;
  const cfg = await loadConfig(env);

  // ---- 后台 ----
  if (s.startsWith('/admin/')) {
    ctx.route = s;

    // 登出
    if (s === '/admin/authentication/logout') {
      await revokeManageSession(env, request);
      return new Response(null, {
        status: 302,
        headers: {
          Location: '/admin/authentication/login',
          'Set-Cookie': 'MANAGE_USER=; Path=/; HttpOnly; SameSite=Lax; Max-Age=0',
        },
      });
    }

    // 登录页
    if (s === '/admin/authentication/login') {
      return pageRes(renderAdminLoginPage(cfg));
    }

    // 后台 API
    if (s.startsWith('/admin/api/')) {
      const rest = s.replace('/admin/api/', '');   // 如 authentication/login, dashboard/overview
      const body = await request.text().catch(() => '');
      let parsed = {};
      try { parsed = body ? JSON.parse(body) : {}; } catch (e) {
        if (body) { try { parsed = Object.fromEntries(new URLSearchParams(body)); } catch (e2) {} }
      }
      // GET 查询参数并入 body(e.g. /admin/api/card/data?status=2)
      if (!body) {
        for (const [k, v] of url.searchParams) {
          if (!(k in parsed)) parsed[k] = v;
        }
      }
      const res = await adminEndpoint(env, request, url, ...rest.split('/'), parsed);
      return res;
    }

    // 后台页面
    const manage = await authenticateManage(env, request);
    if (!manage) {
      const qLogin = `?goto=${encodeURIComponent(s)}`;
      return new Response(null, { status: 302, headers: { Location: '/admin/authentication/login' + qLogin } });
    }
    if (s === '/admin/dashboard/index' || s === '/admin/dashboard') {
      return pageRes(renderAdminDashboardPage(cfg, manage));
    }
    if (s === '/admin/category/index') {
      return pageRes(renderAdminCategoryPage(cfg, manage));
    }
    if (s === '/admin/commodity/index') {
      return pageRes(renderAdminCommodityPage(cfg, manage));
    }
    if (s === '/admin/card/index') {
      const cid = Number(url.searchParams.get('commodity_id')) || 0;
      return pageRes(renderAdminCardPage(cfg, manage, cid));
    }
    if (s === '/admin/order/index') {
      return pageRes(renderAdminOrderPage(cfg, manage));
    }
    if (s === '/admin/user/index') {
      return pageRes(renderAdminUserPage(cfg, manage));
    }
    if (s === '/admin/recharge/order') {
      return pageRes(renderAdminRechargePage(cfg, manage));
    }
    if (s === '/admin/coupon/index') {
      return pageRes(renderAdminCouponPage(cfg, manage));
    }
    if (s === '/admin/ticket/index') {
      return pageRes(renderAdminTicketPage(cfg, manage));
    }
    if (s === '/admin/message/index') {
      return pageRes(renderAdminMessagePage(cfg, manage));
    }
    if (s === '/admin/cash/index') {
      return pageRes(renderAdminCashPage(cfg, manage));
    }
    if (s === '/admin/user/bill') {
      return pageRes(renderAdminBillPage(cfg, manage));
    }
    if (s === '/admin/log/index') {
      return pageRes(renderAdminLogPage(cfg, manage));
    }
    return pageRes(renderAdminShell({ cfg, manage, title: '建设中', activePath: s }, 'text/html'));
  }

  // ---- 验证码 ----
  if (pathname.startsWith('/user/captcha/image')) {
    const action = q.get('action') || 'login';
    const cap = await api.captchaImage(env, request, action);
    return new Response(cap.svg, {
      status: 200,
      headers: { 'Content-Type': 'image/svg+xml; charset=utf-8', 'Cache-Control': 'no-store', 'Set-Cookie': cap.cookie },
    });
  }

  // ---- 认证页面 ----
  if (s === '/user/authentication/login') {
    if (Number(cfg.closed) === 1) return pageRes(renderAuthHeader(await viewContext(env, request, url, '登录')) + `<main class="auth-wrapper"><div class="auth-card"><p class="text-center">店铺维护中，暂时无法登录</p></div></main>` + renderAuthFooter());
    const v = await viewContext(env, request, url, '登录');
    v.route = s;
    return pageRes(renderAuthHeader(v) + pageLogin(v) + renderAuthFooter());
  }
  if (s === '/user/authentication/register') {
    if (Number(cfg.registered_state) === 0) return pageRes(renderAuthHeader(await viewContext(env, request, url, '提示')) + `<main class="auth-wrapper"><div class="auth-card"><p class="text-center">注册已关闭</p></div></main>` + renderAuthFooter());
    const v = await viewContext(env, request, url, '注册');
    v.route = s;
    return pageRes(renderAuthHeader(v) + pageRegister(v) + renderAuthFooter());
  }
  if (s === '/user/authentication/logout') return api.logout(env, request, url);

  // ---- 需要登录的会员中心页面 ----
  const memberPages = {
    '/user/dashboard/index': pageDashboard,
    '/user/personal/purchaseRecord': pagePurchaseRecord,
    '/user/bill/index': pageBill,
    '/user/recharge/index': pageRecharge,
    '/user/security/personal': pageSecurity,
  };
  const memberPrefixes = ['/user/dashboard', '/user/personal', '/user/security', '/user/recharge', '/user/bill', '/user/cash', '/user/coupon', '/user/ticket', '/user/message', '/user/promote', '/user/order', '/user/card', '/user/commodity', '/user/category', '/user/business', '/user/share'];
  if (memberPrefixes.some(p => s.startsWith(p))) {
    const user = await api.currentUser(env, request);
    if (!user) return new Response(null, { status: 302, headers: { Location: '/user/authentication/login' } });
    const v = await viewContext(env, request, url, '会员中心', { user });
    v.route = s;
    v.group = await api.userGroupOf(env, user);
    if (s === '/user/recharge/index') {
      const pays = await api.payList(env, request, url).then(r => r.json()).then(j => j.data || []);
      v.payList = pays;
    }
    if (memberPages[s]) return pageRes(renderHeader(v, indexVar(0, cfg)) + memberPages[s](v) + renderFooter(v));
    // 未实现的会员页面
    return pageRes(renderHeader(v, indexVar(0, cfg)) + userCenterShell(v, `
      <div class="panel">
        <div class="panel-header"><span class="icon"><i class="fa-duotone fa-regular fa-right-from-bracket"></i></span><h6 class="panel-title">敬请期待</h6></div>
        <div class="panel-body">
          <p class="text-muted mb-3">功能开发中，敬请期待 (P2 阶段)</p>
          <a class="btn btn-primary" href="/user/dashboard/index"><i class="fa-duotone fa-regular fa-arrow-left me-2"></i>返回个人中心</a>
        </div>
      </div>`) + renderFooter(v));
  }

  // ---- API ----
  if (s.startsWith('/user/api/')) {
    const parts = s.replace(/^\/user\/api\//, '').split('/');
    const apiCtl = parts[0] || '';
    const apiAct = parts[1] || '';
    const body = await readBody(request);

    if (apiCtl === 'index') {
      if (apiAct === 'data') {
        const cats = await getVisibleCategories(env, cfg);
        return jsonRes({ code: 200, msg: 'success', data: cats });
      }
      if (apiAct === 'commodity') {
        const { data, total } = await getCommodityList(env, cfg, {
          keywords: q.get('keywords') || '',
          limit: q.get('limit') || 0,
          page: q.get('page') || 1,
          categoryId: q.get('categoryId') ?? cid ?? 0,
        });
        return jsonRes({ code: 200, msg: 'success', data, total });
      }
      if (apiAct === 'commodityDetail') {
        try {
          const item = await getItem(env, cfg, Number(q.get('commodityId') || body.item_id));
          item.stock_state = stockState(item.stock);
          if (item.inventory_hidden == 1) item.stock = hideStockText(item.stock);
          return jsonRes({ code: 200, msg: 'success', data: item });
        } catch (e) {
          return jsonRes({ code: 403, msg: e.message }, 200);
        }
      }
      if (apiAct === 'query') return api.queryOrder(env, request, url, body);
      if (apiAct === 'card') return api.cardDetail(env, request, url, body);
    }
    if (apiCtl === 'authentication') {
      if (apiAct === 'register') return api.register(env, request, url, body);
      if (apiAct === 'login') return api.login(env, request, url, body);
    }
    if (apiCtl === 'order') {
      if (apiAct === 'trade') return api.trade(env, request, url, body);
      if (apiAct.startsWith('callback')) {
        const tradeNo = apiAct.split('.')[1] || '';
        return api.orderCallback(env, request, url, tradeNo);
      }
    }
    if (apiCtl === 'purchaseRecord') {
      if (apiAct === 'data') return api.purchaseRecord(env, request, url);
    }
    if (apiCtl === 'bill') {
      if (apiAct === 'data') return api.billData(env, request, url);
    }
    if (apiCtl === 'pay') {
      if (apiAct === 'data' || apiAct === 'index') return api.payList(env, request, url);
    }
    if (apiCtl === 'recharge') {
      if (apiAct === 'data') return api.payList(env, request, url);
      if (apiAct === 'index') return api.rechargeCreate(env, request, url, body);
    }
    if (apiCtl === 'security') {
      if (apiAct === 'password') return api.changePassword(env, request, url, body);
    }
    return jsonRes({ code: 404, msg: '接口不存在' }, 404);
  }

  // ---- 页面 ----
  const parts = s.split('/').filter(p => p !== '');
  const ctl = (parts[1] || '');
  const act = (parts[2] || (parts[0] || 'index'));

  if (s === '/user/index/index') {
    if (Number(cfg.closed) === 1) {
      const v = await viewContext(env, request, url, '店铺正在维护');
      v.route = s;
      return pageRes(renderHeader(v, indexVar(0, cfg)) + pageClosed(v) + renderFooter(v));
    }
    const category = await getVisibleCategories(env, cfg);
    const categoryId = cid != null ? cid : (Number(cfg.default_category) || 0);
    const v = await viewContext(env, request, url, '购物', { category, categoryId });
    v.route = s;
    return pageRes(renderHeader(v, indexVar(categoryId, cfg)) + pageIndex(v) + renderFooter(v));
  }

  if (s === '/user/index/item') {
    try {
      const item = await getItem(env, cfg, mid != null ? mid : Number(q.get('mid')));
      const v = await viewContext(env, request, url, item.name, { item, commodityId: item.id });
      v.route = s;
      const js = itemVar({ ...item, description: undefined });
      return pageRes(renderHeader(v, js) + pageItem(v) + renderFooter(v));
    } catch (e) {
      return pageRes(`<!DOCTYPE html><html><head><meta charset="utf-8"><title>提示</title></head><body style="font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#f5f6fa;color:#333"><div style="text-align:center"><h3>${htmlEscape(e.message)}</h3><p><a href="/">返回首页</a></p></div></body></html>`);
    }
  }

  if (s === '/user/index/query') {
    const v = await viewContext(env, request, url, '订单查询', { tradeNo: q.get('tradeNo') || '' });
    v.route = s;
    return pageRes(renderHeader(v, indexVar(0, cfg)) + pageQuery(v) + renderFooter(v));
  }

  if (s === '/404.html' || pathname === '/404.html') {
    return pageRes(`<!DOCTYPE html><html><head><meta charset="utf-8"><title>404 Not Found</title></head><body style="font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#f5f6fa;color:#333"><div style="text-align:center"><h1 style="font-size:4rem;color:#dc3545">404</h1><p>页面不存在</p><p><a href="/">返回首页</a></p></div></body></html>`);
  }

  // 静态资源回退 (开发模式)
  if (/\.(js|css|png|jpg|jpeg|gif|ico|svg|woff2?|ttf|eot|map|webp)$/i.test(pathname) && env.ASSETS) {
    try { return await env.ASSETS.fetch(request); } catch (e) { /* fallthrough */ }
  }

  return pageRes(`<!DOCTYPE html><html><head><meta charset="utf-8"><title>404 Not Found</title></head><body style="font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#f5f6fa;color:#333"><div style="text-align:center"><h1 style="font-size:4rem;color:#dc3545">404</h1><p>页面不存在: ${htmlEscape(pathname)}</p><p><a href="/">返回首页</a></p></div></body></html>`);
}

export default {
  async fetch(request, env, ctx) {
    const url = new URL(request.url);
    if (url.pathname === '/favicon.ico') {
      if (env.ASSETS) { try { return await env.ASSETS.fetch(request); } catch (e) { /* */ } }
    }
    try {
      return await route(env, request, url, {});
    } catch (e) {
      return pageRes(`<!DOCTYPE html><html><head><meta charset="utf-8"><title>服务器错误</title></head><body style="font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#f5f6fa;color:#333"><div style="text-align:center"><h3>服务器开小差了 (500)</h3><p style="color:#999">${htmlEscape(e && e.message ? e.message : String(e))}</p></div></body></html>`, 500);
    }
  },
};

// 供测试引用
export { pageIndex, pageItem, pageQuery, pageClosed, renderHeader, renderFooter, getCommodityList, getItem };