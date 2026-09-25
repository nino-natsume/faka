// ============================================================
// DCSHOP (acg-faka) Cloudflare 部署版 - Worker 主入口
// 路由: 首页 / 商品详情 / 下单 / 支付 / 自动发货 / 订单查询 / 帮助 / 后台
// 数据: D1 (SQLite), 静态资源: Pages Assets (public/)
// ============================================================
import { esc, fen2yuan, ts2str, now, parseQuery, getOptions, getSkus, getSorts, buildAttrSpec, buildNav, layout, headerHtml, footerHtml, pageHead, pageFoot, paginationHtml, TYPE_BADGE, TYPE_NAME, opt, ASSET, hmacSha256 } from './lib.js';
import { handleAdmin } from './admin.js';

export default {
  async fetch(request, env, ctx) {
    try {
      return await router(request, env);
    } catch (e) {
      return new Response('Internal Error: ' + esc(e.message), { status: 500, headers: { 'content-type': 'text/plain; charset=utf-8' } });
    }
  },
};

const JSON_HEADERS = { 'content-type': 'application/json; charset=utf-8' };
const HTML_HEADERS = { 'content-type': 'text/html; charset=utf-8' };

// 静态资源优先交给 Pages Assets
async function serveAsset(request, env) {
  if (env.ASSETS) {
    const res = await env.ASSETS.fetch(request);
    if (res.status !== 404) return res;
  }
  return null;
}

async function router(request, env) {
  const url = new URL(request.url);
  const path = url.pathname;
  const q = parseQuery(url.search);

  // --- 静态资源 ---
  if (/^\/(css|js|img|vendor|favicon\.ico)/.test(path)) {
    const a = await serveAsset(request, env);
    if (a) return a;
    return new Response('Not Found', { status: 404 });
  }

  // --- 后台 ---
  if (path === '/admin' || path.startsWith('/admin/')) {
    return handleAdmin(request, env);
  }

  // --- API / 动作 ---
  const action = q.action || '';

  if (request.method === 'POST') {
    if (action === 'xiadan') return apiXiadan(request, env);
    if (action === 'pay_submit') return apiPaySubmit(request, env);
    if (action === 'order_cancel') return apiOrderCancel(request, env);
    if (action === 'order_query') return apiOrderQuery(request, env);
    if (action === 'login') return apiLogin(request, env);
  }

  if (request.method === 'GET') {
    if (action === 'goods') return pageGoods(request, env, q);
    if (action === 'pay') return pagePay(request, env, q);
    if (action === 'order_result') return pageOrderResult(request, env, q);
    if (action === 'order_kami') return apiKami(request, env, q);
    if (action === 'order_query') return q && (q.q || '').trim() ? apiOrderQuery(request, env) : pageOrderQuery(request, env, q);
    if (action === 'help') return pageHelp(request, env, q);
    if (action === 'search') return pageHome(request, env, q, true);
    if (action === 'user') return pageUser(request, env, q);
    if (action === 'rss') return pageRss(request, env, q);
  }

  // 默认首页
  return pageHome(request, env, q, false);
}

// ============================================================
// 公共：站点上下文
// ============================================================
async function ctx(env) {
  const opts = await getOptions(env.DB);
  if (env) env._opts = opts;
  const navItems = await buildNav(env.DB, '/');
  return { opts, navItems };
}

// ============================================================
// 首页（商品列表，对应 goods_list.php 普通模式）
// ============================================================
async function pageHome(request, env, q, isSearch) {
  const { opts, navItems } = await ctx(env);
  const page = Math.max(1, parseInt(q.page) || 1);
  const pageSize = 12;
  const sortId = parseInt(q.sort_id) || 0;
  const kw = (q.q || '').trim();
  const order = q.order || 'default';

  let where = 'WHERE g.delete_time IS NULL AND g.is_on_shelf = 1';
  const binds = [];
  if (sortId > 0) {
    where += ' AND (g.sort_id = ? OR g.sort_id IN (SELECT sid FROM dc_sort WHERE pid = ?))';
    binds.push(sortId, sortId);
  }
  if (kw) {
    where += ' AND (g.title LIKE ? OR g.des LIKE ?)';
    binds.push('%' + kw + '%', '%' + kw + '%');
  }

  // 排序
  const orderMap = {
    default: 'ORDER BY g.index_top DESC, g.sort_num DESC, g.id DESC',
    sales: 'ORDER BY g.sales DESC',
    sales_asc: 'ORDER BY g.sales ASC',
    price_asc: 'ORDER BY g.id ASC',
    price_desc: 'ORDER BY g.id DESC',
    stock: 'ORDER BY g.stock DESC',
    stock_asc: 'ORDER BY g.stock ASC',
  };
  const orderSql = orderMap[order] || orderMap.default;

  const { results: goods } = await env.DB
    .prepare(
      'SELECT g.*, s.sortname FROM dc_goods g LEFT JOIN dc_sort s ON s.sid = g.sort_id ' +
        where + ' ' + orderSql + ' LIMIT ? OFFSET ?'
    )
    .bind(...binds, pageSize, (page - 1) * pageSize)
    .all();

  const { results: cnt } = await env.DB.prepare('SELECT COUNT(*) AS c FROM dc_goods g ' + where).bind(...binds).all();
  const total = cnt[0] ? cnt[0].c : 0;
  const pages = Math.max(1, Math.ceil(total / pageSize));

  // 分类与筛选条
  const sorts = await getSorts(env.DB);
  const categories = sorts.slice(0, 12);

  // banner 是否可用
  let bannerShow = true;
  if (env.ASSETS) {
    const b = await env.ASSETS.fetch(new URL('/img/Banner.png', request.url));
    if (b.status === 404) bannerShow = false;
  }

  const orderChips = [
    { key: 'default', label: '综合', icon: 'ri-apps-2-line' },
    { key: 'sales', label: '销量', icon: 'ri-fire-line' },
    { key: 'price_asc', label: '价格', icon: 'ri-arrow-up-down-line' },
    { key: 'stock', label: '库存', icon: 'ri-stack-line' },
  ];

  const baseUrl = '/?action=' + (isSearch ? 'search' : 'index') + (sortId ? '&sort_id=' + sortId : '') + (kw ? '&q=' + encodeURIComponent(kw) : '');

  // 商品卡片
  let gridHtml = '';
  for (const g of goods) {
    gridHtml += goodsCardHtml(g, env);
  }
  if (!goods.length) {
    gridHtml = `<div class="empty-container"><i class="ri-inbox-2-line"></i><p>暂无商品${kw ? '（搜索无结果）' : ''}</p></div>`;
  }

  const rollBulletin = opt(opts, 'roll_bulletin', '');
  const homeBulletin = opt(opts, 'home_bulletin', '');

  const heroRow = `
  <div class="fk-hero-row ${bannerShow ? '' : 'no-banner'}">
    ${bannerShow ? `<div class="fk-banner-wrap"><div class="fk-banner" id="fk-banner">
      <div class="fk-banner-track is-fade" id="fk-banner-track">
        <div class="fk-banner-slide"><a href="/"><img src="/img/Banner.png" alt="banner"></a></div>
      </div>
      <div class="fk-banner-dots" id="fk-banner-dots"><button class="fk-banner-dot active"></button></div>
      <button class="fk-banner-arrow fk-banner-prev" type="button"><i class="ri-arrow-left-s-line"></i></button>
      <button class="fk-banner-arrow fk-banner-next" type="button"><i class="ri-arrow-right-s-line"></i></button>
    </div></div>` : ''}
    ${homeBulletin ? `<div class="fk-notice-col"><div class="notice-card">
      <div class="notice-card-header"><i class="layui-icon layui-icon-notice"></i><span>网站公告</span></div>
      <div class="notice-card-content">${homeBulletin}</div>
    </div></div>` : ''}
  </div>`;

  const rollHtml = rollBulletin
    ? `<div class="roll-notice-bar"><i class="layui-icon layui-icon-speaker"></i><div class="roll-notice-content">${esc(rollBulletin)}</div></div>`
    : '';

  let categoryHtml = '';
  if (sorts.length) {
    const items = categories
      .map(
        (s) => `<a class="category-item" href="/?sort_id=${s.sid}"><div class="category-icon">${s.sorticon ? `<i class="${esc(s.sorticon)}"></i>` : `<i class="ri-store-2-line"></i>`}</div><span>${esc(s.sortname)}</span></a>`
      )
      .join('');
    categoryHtml = `<div class="category-section"><div class="category-grid">${items}</div></div>`;
  }

  const filterHtml = `
  <div class="fk-filter-bar">
    ${orderChips
      .map(
        (c) =>
          `<a class="fk-filter-chip ${order === c.key ? 'active' : ''}" href="${baseUrl.replace('/?action=search', '/?action=index').replace('/?action=index', '/?action=index')}&order=${c.key}"><i class="ri ${c.icon} fk-chip-ri"></i><label>${c.label}</label></a>`
      )
      .join('')}
  </div>`;

  const body = `
  <main class="blog-container">
    ${heroRow}
    ${rollHtml}
    ${categoryHtml}
    ${filterHtml}
    <div class="goods-list-section" id="fk-goods-list-section">
      <div class="goods-grid goods-grid-layout-grid">
        ${gridHtml}
      </div>
    </div>
    ${paginationHtml(baseUrl, page, pages, total)}
  </main>
  <style>
  .fk-filter-bar a.fk-filter-chip.active{color:var(--theme-primary);border-color:var(--theme-primary);}
  .category-section{margin:18px auto 6px;max-width:1200px;padding:0 12px;}
  .empty-container{text-align:center;padding:60px 0 80px;color:#999;}
  .empty-container i{font-size:48px;display:block;margin-bottom:12px;}
  .goods-pagination{text-align:center;padding:24px 0 40px;}
  .goods-pagination a{display:inline-block;margin:0 4px;padding:6px 12px;border:1px solid #e5e5e5;border-radius:6px;color:#666;}
  .goods-pagination a.current{background:var(--theme-primary);color:#fff;border-color:var(--theme-primary);}
  </style>`;

  return new Response(layout(env, { ...opts, title: kw ? '搜索 - ' + kw : opt(opts, 'site_title', 'DCSHOP发卡系统') }, navItems, body), { headers: HTML_HEADERS });
}

// 商品卡片
function goodsCardHtml(g, env) {
  const typeBadgeCls = TYPE_BADGE[g.type] || '';
  const soldOut = parseInt(g.stock) <= 0;
  const cover = g.cover && !g.cover.startsWith('../') ? g.cover : '';
  const imgSrc = cover ? esc(cover) : '';
  return `<div class="goods-grid-item layui-anim layui-anim-scaleSpring">
  <a class="goods-card ${soldOut ? 'goods-card-soldout' : ''}" href="/?action=goods&id=${g.id}">
    ${imgSrc ? `<div class="goods-img-box"><img class="goods-img lazy" src="${imgSrc}" alt="${esc(g.title)}">${typeBadgeCls ? `<span class="fk-type-badge ${typeBadgeCls}">${TYPE_NAME[g.type] || g.type}</span>` : ''}${soldOut ? `<div class="goods-soldout-mask"><span>已售空</span></div>` : ''}</div>` : `<div class="goods-img-box"><img class="goods-img lazy" src="/img/logo.apng" alt="${esc(g.title)}" style="object-fit:contain;background:#f7f8fa;">${typeBadgeCls ? `<span class="fk-type-badge ${typeBadgeCls}">${TYPE_NAME[g.type] || g.type}</span>` : ''}${soldOut ? `<div class="goods-soldout-mask"><span>已售空</span></div>` : ''}</div>`}
    <div class="goods-info">
      <div class="goods-title row-2-hidden">${esc(g.title)}</div>
      ${g.des ? `<div class="goods-desc">${esc(String(g.des).slice(0, 60))}</div>` : ''}
      <div class="goods-meta">
        ${opt(env._opts, 'stock_switch', 'y') === 'y' ? `<span class="goods-meta-stock">库存 ${g.stock}</span>` : ''}
        ${opt(env._opts, 'sales_switch', 'y') === 'y' ? `<span class="goods-meta-sales">已售 ${g.sales}</span>` : ''}
      </div>
      <div class="goods-price-row">
        <div class="price-wrap">
          <span class="price-current"><i>¥</i>${fen2yuan(g._price || 0)}</span>
        </div>
        <div class="buy-btn">购买</div>
      </div>
    </div>
  </a>
</div>`;
}

// ============================================================
// 商品详情页（对应 goods.php 结构 + 购买表单）
// ============================================================
async function pageGoods(request, env, q) {
  const { opts, navItems } = await ctx(env);
  const id = parseInt(q.id) || 0;
  if (!id) return new Response('商品不存在', { status: 404, headers: HTML_HEADERS });

  const g = (await env.DB.prepare('SELECT * FROM dc_goods WHERE id = ? AND delete_time IS NULL LIMIT 1').bind(id).first());
  if (!g) return new Response('<h2 style="text-align:center;padding:120px 0;">商品不存在或已下架</h2><p style="text-align:center;"><a href="/">返回首页</a></p>', { status: 404, headers: HTML_HEADERS });

  const skus = await getSkus(env.DB, id);
  const price = skus.length ? Math.min(...skus.map((s) => s.guest_price || 0)) : 0;
  const stock = skus.length ? skus.reduce((a, s) => a + (parseInt(s.stock) || 0), 0) : 0;
  const gallery = (() => {
    try {
      const arr = JSON.parse(g.gallery || '[]');
      return arr.filter((x) => x && !x.startsWith('../')).map((x) => x);
    } catch (e) {
      return [];
    }
  })();
  const cover = g.cover && !g.cover.startsWith('../') ? g.cover : gallery[0] || '';
  const attachUser = (() => {
    try {
      return JSON.parse(g.attach_user || '[]');
    } catch (e) {
      return [];
    }
  })();
  const isSku = g.is_sku === 'y';

  // 规格组
  const specGroups = [];
  if (isSku && g.attr_id) {
    const { results: attrs } = await env.DB.prepare('SELECT * FROM dc_sku_attr WHERE type_id = ? AND delete_time IS NULL ORDER BY id ASC').bind(g.attr_id).all();
    const { results: vals } = await env.DB.prepare('SELECT * FROM dc_sku_value WHERE delete_time IS NULL ORDER BY id ASC').all();
    for (const a of attrs) {
      const group = { title: a.title, options: [] };
      for (const v of vals) {
        if (v.attr_id === a.id) group.options.push({ id: v.id, name: v.name });
      }
      if (group.options.length) specGroups.push(group);
    }
  }

  const orderRequired = (() => {
    try {
      return JSON.parse(opt(opts, 'order_required', '[]') || '[]');
    } catch (e) {
      return [];
    }
  })();

  const specsData = skus.map((s) => ({ sku: s.sku, price: s.guest_price, market: s.market_price, stock: s.stock, sales: s.sales }));

  const specHtml = specGroups
    .map(
      (grp, gi) => `<div class="spec-group">
      <div class="spec-group-title">${esc(grp.title)}</div>
      <div class="spec-options">
        ${grp.options.map((o) => `<div class="spec-option" data-id="${o.id}" data-group="${gi}"><span>${esc(o.name)}</span></div>`).join('')}
      </div>
    </div>`
    )
    .join('');

  const attachHtml = attachUser
    .map(
      (f, i) => `<div class="input-field-row">
      <div class="input-field-header"><div class="section-label">${f.required ? '<span class="required-star">*</span>' : ''}${esc(f.name)}</div>
      <input class="input-field-input ${f.required ? 'required-input' : ''}" name="attach[${esc(f.name)}]" placeholder="${esc(f.placeholder || '')}" data-validate-type="${esc(f.type || 'string')}">
      </div>
      ${f.tip ? `<div class="input-field-note">${esc(f.tip)}</div>` : ''}
    </div>`
    )
    .join('');

  const requiredHtml = orderRequired
    .map(
      (f, i) => `<div class="input-field-row">
      <div class="input-field-header"><div class="section-label"><span class="required-star">*</span>${esc(f.name || '联系信息')}</div>
      <input class="input-field-input required-input" name="required[${esc(f.name || '联系信息')}]" placeholder="${esc(f.placeholder || '')}" data-validate-type="${esc(f.type || 'string')}">
      </div>
    </div>`
    )
    .join('');

  const paymentMethods = paymentMethodsHtml(opts);

  const body = `
  <main class="goods-detail-container">
    <div class="kami-page-header">
      <a class="kami-back-btn" href="javascript:history.back()"><i class="fa fa-chevron-left"></i></a>
      <div class="kami-page-title">商品详情</div>
      <a class="kami-share-btn" id="goodsShareBtn" href="javascript:;">分享</a>
    </div>
    <div class="kami-page-header-placeholder"></div>
    <div class="main-card">
      <div class="goods-layout">
        <div class="goods-left">
          ${cover ? `<div class="goods-cover-section" id="goodsGallerySection">
            <div class="goods-cover-main cover-arrows-hidden" id="goodsCoverMain">
              <img id="goodsCoverImg" src="${esc(cover)}" alt="${esc(g.title)}">
              <span class="cover-arrow cover-arrow-prev"><i class="ri-arrow-left-s-line"></i></span>
              <span class="cover-arrow cover-arrow-next"><i class="ri-arrow-right-s-line"></i></span>
            </div>
          </div>` : `<div class="goods-cover-section"><div class="goods-cover-main"><img id="goodsCoverImg" src="/img/logo.apng" alt="" style="object-fit:contain;background:#f7f8fa;"></div></div>`}
        </div>
        <div class="goods-right">
          <div class="goods-title-section">
            <h1 class="goods-title">${esc(g.title)}</h1>
            <div class="goods-meta">已售 ${g.sales} <span style="margin-left:10px;">库存 <span id="goodsStock">${stock}</span></span></div>
          </div>
          <div class="spec-section" id="specSection">
            ${specHtml}
          </div>
          <form id="buyFormSection" class="buy-form-section layui-form" data-dc-physical-goods="">
            <div class="price-quantity-row">
              <div class="price-section">
                <span class="section-label">价格</span>
                <span class="section-value"><span class="unit-price" id="unitPrice">¥${fen2yuan(price)}</span><span class="unit">/${esc(g.unit_name || '个')}</span>
                <span class="market-price" id="marketPrice" style="text-decoration:line-through;color:#bbb;font-size:13px;margin-left:6px;"></span>
                </span>
              </div>
              <div class="quantity-section">
                <span class="section-label">数量</span>
                <div class="quantity-selector">
                  <button type="button" class="quantity-btn" id="qtyMinus">-</button>
                  <input type="number" class="quantity-input" id="qtyInput" value="1" min="1">
                  <button type="button" class="quantity-btn" id="qtyPlus">+</button>
                </div>
              </div>
            </div>
            <div class="stock-row"><span>库存：</span><span class="stock-value" id="goodsStock2">${stock}</span></div>
            <div id="inputFields">
              ${attachHtml}
              ${requiredHtml}
            </div>
            ${paymentMethods}
            <div class="drawer-footer">
              <div class="pay-bar">
                <span class="pay-amount">合计：<span class="dynamic-price" id="totalPrice">¥${fen2yuan(price)}</span></span>
                <button type="button" class="pay-btn" id="submitPayBtn">立即购买</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    ${g.content ? `<section class="goods-detail-section">
      <div class="goods-detail-header"><span class="goods-detail-bar"></span><span class="goods-detail-title">商品详情</span></div>
      <div class="goods-detail-body"><div class="intro" id="goodsDesc">${g.content}</div></div>
    </section>` : ''}
  </main>
  <script>
  (function(){
    var GOODS = ${JSON.stringify({ id: g.id, is_sku: isSku, attrs: specGroups, skus: specsData, unit: g.unit_name || '个', stock: stock, min_price: price })};
    var selected = [];
    function currentSku(){
      if (!GOODS.is_sku) return '0';
      return selected.join('-') || '0';
    }
    function findSku(sk){
      for (var i=0;i<GOODS.skus.length;i++){ if (GOODS.skus[i].sku === sk) return GOODS.skus[i]; }
      return null;
    }
    function updateSku(){
      var sk = currentSku();
      var row = findSku(sk);
      var price = row ? (row.price||0) : (GOODS.is_sku ? 0 : GOODS.min_price);
      $('#unitPrice').text('¥' + (price/100).toFixed(2));
      $('#marketPrice').text(row && row.market ? '¥' + (row.market/100).toFixed(2) : '');
      var st = row ? (parseInt(row.stock)||0) : GOODS.stock;
      $('#goodsStock').text(st); $('#goodsStock2').text(st);
      var qty = parseInt($('#qtyInput').val()) || 1;
      $('#totalPrice').text('¥' + (price*qty/100).toFixed(2));
      if (st > 0 && qty > st) { $('#qtyInput').val(st); $('#totalPrice').text('¥' + (price*st/100).toFixed(2)); }
    }
    $('.spec-option').on('click', function(){
      var $t = $(this);
      var gid = $t.data('group');
      $('.spec-option[data-group="'+gid+'"]').removeClass('active');
      $t.addClass('active');
      var g = GOODS.attrs[gid];
      var idx = selected.length;
      if (g.options) {
        while (selected.length <= gid) { selected.push(undefined); }
        selected[gid] = String($t.data('id'));
      }
      // 合并已选值
      var skArr = [];
      for (var i=0;i<GOODS.attrs.length;i++){
        var v = selected[i];
        if (v === undefined || v === null) { skArr = []; break; }
        skArr.push(v);
      }
      selected = skArr;
      updateSku();
    });
    $('#qtyMinus').on('click', function(){ var v = parseInt($('#qtyInput').val())||1; if (v>1){ $('#qtyInput').val(v-1); } updateSku(); });
    $('#qtyPlus').on('click', function(){ var v = parseInt($('#qtyInput').val())||1; var st = parseInt($('#goodsStock').text())||9999; if (v<st){ $('#qtyInput').val(v+1); } updateSku(); });
    $('#qtyInput').on('change', updateSku);
    var paying = false;
    $('#submitPayBtn').on('click', function(){
      if (paying) return; paying = true;
      var qty = parseInt($('#qtyInput').val())||1;
      var sku = currentSku();
      var fd = { goods_id: GOODS.id, quantity: qty, sku_ids: sku === '0' ? [] : sku.split('-') };
      $('.payment-item.active').each(function(){ fd.payment_plugin = $(this).data('method'); });
      $('#inputFields input').each(function(){
        var n = $(this).attr('name'); if (!n) return;
        if (n.indexOf('attach[') === 0) {
          var key = n.replace('attach[','').replace(']','');
          if (!fd.attach) fd.attach = {};
          fd.attach[key] = $(this).val();
        } else if (n.indexOf('required[') === 0) {
          var key2 = n.replace('required[','').replace(']','');
          if (!fd.required) fd.required = {};
          fd.required[key2] = $(this).val();
        }
      });
      // 简单校验必填
      var miss = [];
      $('#inputFields .required-input').each(function(){ if (!$(this).val()) miss.push($(this).attr('name')); });
      if (miss.length) { paying=false; layer.msg('请填写必填项'); return; }
      $.post('/?action=xiadan', fd, function(res){
        if (res.code === 0) { location.href = '/?action=pay&out_trade_no=' + res.out_trade_no; }
        else { paying=false; layer.msg(res.msg || '下单失败'); }
      }, 'json').fail(function(){ paying=false; layer.msg('网络错误'); });
    });
  })();
  </script>`;

  return new Response(layout(env, { ...opts, title: g.title }, navItems, body), { headers: HTML_HEADERS });
}

// 支付方式 HTML
function paymentMethodsHtml(opts) {
  const balanceSwitch = opt(opts, 'balance_switch', 'y');
  return `<div class="payment-methods">
  ${balanceSwitch === 'y' ? `<div class="payment-item" data-method="balance"><i class="ri-wallet-3-line payment-icon"></i><div class="payment-info"><span class="payment-name">余额支付</span></div><i class="ri-checkbox-circle-fill payment-checked"></i></div>` : ''}
  <div class="payment-item active" data-method="test"><i class="ri-bank-card-line payment-icon"></i><div class="payment-info"><span class="payment-name">测试支付（模拟）</span></div><i class="ri-checkbox-circle-fill payment-checked"></i></div>
  ${epayConfig(opts) ? `<div class="payment-item" data-method="epay_wx"><i class="ri-wechat-pay-line payment-icon"></i><div class="payment-info"><span class="payment-name">易支付/微信</span></div><i class="ri-checkbox-circle-fill payment-checked"></i></div>
  <div class="payment-item" data-method="epay_ali"><i class="ri-alipay-line payment-icon"></i><div class="payment-info"><span class="payment-name">易支付/支付宝</span></div><i class="ri-checkbox-circle-fill payment-checked"></i></div>` : ''}
  </div>
  <style>.payment-methods .payment-item{cursor:pointer;}</style>
  <script>$(function(){ $('.payment-methods .payment-item').on('click', function(){ $('.payment-methods .payment-item').removeClass('active'); $(this).addClass('active'); }); });</script>`;
}

function epayConfig(opts) {
  try {
    const s = JSON.parse(opt(opts, '_epay_config', 'null') || 'null');
    return s && s.api_url ? s : null;
  } catch (e) {
    return null;
  }
}

// ============================================================
// 下单 API (POST /?action=xiadan)
// 对应 Api::xiadan: 校验 -> 计价 -> 写库
// ============================================================
async function apiXiadan(request, env) {
  const form = await request.formData();
  const goodsId = parseInt(form.get('goods_id')) || 0;
  const qty = Math.max(1, parseInt(form.get('quantity')) || 1);
  let skuIds = form.getAll('sku_ids[]');
  if (!skuIds.length && form.get('sku_ids')) skuIds = String(form.get('sku_ids')).split(',');
  const sku = skuIds.filter((x) => x).join('-') || '0';
  const payPlugin = form.get('payment_plugin') || 'test';
  const attachRaw = {};
  const requiredRaw = {};
  for (const [k, v] of form.entries()) {
    if (k.startsWith('attach[')) attachRaw[k.slice(7, -1)] = String(v);
    if (k.startsWith('required[')) requiredRaw[k.slice(9, -1)] = String(v);
  }

  const g = await env.DB.prepare('SELECT * FROM dc_goods WHERE id = ? AND is_on_shelf = 1 AND delete_time IS NULL LIMIT 1').bind(goodsId).first();
  if (!g) return json({ code: 1, msg: '商品不存在或已下架' });

  // 价格
  const skuRow = await env.DB.prepare('SELECT * FROM dc_skus WHERE goods_id = ? AND sku = ? LIMIT 1').bind(goodsId, sku).first();
  if (!skuRow) return json({ code: 1, msg: '规格不存在' });
  const unitPrice = skuRow.user_price && skuRow.user_price > 0 ? skuRow.user_price : skuRow.guest_price;

  // 库存
  const stockNow = parseInt(skuRow.stock) || 0;
  let avail = stockNow > 0 ? stockNow : 999999;
  if (g.type === 'once') {
    const cnt = await env.DB.prepare('SELECT COUNT(*) AS c FROM dc_goods_once WHERE goods_id = ? AND sku = ? AND sale_time IS NULL').bind(goodsId, sku).first();
    avail = Math.min(avail, parseInt(cnt.c) || 0);
  }
  if (g.type === 'general') {
    const r = await env.DB.prepare('SELECT COUNT(*) AS c FROM dc_goods_general WHERE goods_id = ? AND sku = ?').bind(goodsId, sku).first();
    if (!parseInt(r.c)) return json({ code: 1, msg: '该规格无卡密库存' });
    avail = stockNow > 0 ? Math.min(avail, stockNow) : avail;
  }
  if (g.type === 'service') {
    const r = await env.DB.prepare('SELECT COUNT(*) AS c FROM dc_goods_service WHERE goods_id = ? AND sku = ?').bind(goodsId, sku).first();
    if (!parseInt(r.c)) return json({ code: 1, msg: '该规格无效' });
  }
  if (qty > avail) return json({ code: 1, msg: '库存不足' });

  const amount = unitPrice * qty;
  const outTradeNo = dateStr() + String(Math.floor(Math.random() * 9000) + 1000);
  const t = now();
  const orderId = Date.now() * 1000 + Math.floor(Math.random() * 1000);
  const clientIp = request.headers.get('CF-Connecting-IP') || request.headers.get('x-forwarded-for') || '';

  // 批量优惠（简化：dc_discount type1 每件优惠）
  let discount = 0;
  const dis = await env.DB.prepare('SELECT * FROM dc_discount WHERE goods_id = ? AND sku = ? AND quantity <= ? ORDER BY quantity DESC LIMIT 1').bind(goodsId, sku, qty).first();
  if (dis) {
    if (dis.type === 1) discount = dis.amount * qty; // 每件优惠(分)
    else if (dis.type === 2) discount = dis.amount; // 订单优惠(分)
    else if (dis.type === 3) discount = Math.round(amount * (100 - dis.amount) / 100); // 折扣
  }
  const finalAmount = Math.max(1, amount - discount);

  await env.DB.batch([
    env.DB.prepare('INSERT INTO dc_order (id, station_id, client_ip, user_id, out_trade_no, amount, create_time, payment, pay_plugin, pay_time, expire_time, pay_status, status) VALUES (?,0,?,0,?,?,?,?,?,?,?,0,0)')
      .bind(orderId, clientIp, outTradeNo, finalAmount, t, '待支付', payPlugin, t + (parseInt(opt(await getOptions(env.DB), 'continue_pay_timeout', '30')) || 30) * 60),
    env.DB.prepare('INSERT INTO dc_order_list (order_id, goods_id, sku, attr_spec, attach_user, quantity, unit_price, price, status) VALUES (?,?,?,?,?,?,?,?,0)')
      .bind(orderId, goodsId, sku, JSON.stringify(await buildAttrSpec(env.DB, sku)), JSON.stringify(attachRaw), qty, unitPrice, finalAmount),
    ...Object.keys(requiredRaw).map((k) =>
      env.DB.prepare('INSERT INTO dc_order_required (order_id, name, type, content) VALUES (?,?,?,?)').bind(orderId, k, 'string', requiredRaw[k])
    ),
  ]);

  return json({ code: 0, out_trade_no: outTradeNo });
}

// ============================================================
// 支付页 (GET /?action=pay&out_trade_no=...)
// ============================================================
async function pagePay(request, env, q) {
  const { opts, navItems } = await ctx(env);
  const no = q.out_trade_no || '';
  const order = await env.DB.prepare('SELECT * FROM dc_order WHERE out_trade_no = ? LIMIT 1').bind(no).first();
  if (!order) return new Response('订单不存在', { status: 404, headers: HTML_HEADERS });

  const list = await env.DB.prepare('SELECT ol.*, g.title, g.cover FROM dc_order_list ol LEFT JOIN dc_goods g ON g.id = ol.goods_id WHERE ol.order_id = ?').bind(order.id).all();

  const paid = order.pay_status == 1;
  const expired = order.status === 3;
  const statusText = paid ? '已支付' : expired ? '已取消' : '待支付';

  // 余额信息
  let balance = 0;
  const uid = await getUserUid(request, env);
  let user = null;
  if (uid) user = await env.DB.prepare('SELECT * FROM dc_user WHERE uid = ?').bind(uid).first();

  const countDown = Math.max(0, (parseInt(order.expire_time) || 0) - now());

  const itemsHtml = list.results
    .map((l) => `<tr><td class="item-image">${l.cover && !l.cover.startsWith('../') ? `<img src="${esc(l.cover)}" alt="">` : ''}</td><td class="item-info"><div class="title">${esc(l.title)}</div><div class="spec">${l.attr_spec || ''}</div></td><td class="item-price">¥${fen2yuan(l.unit_price)}</td><td class="item-quantity">×${l.quantity}</td><td class="item-total">¥${fen2yuan(l.price)}</td></tr>`)
    .join('');

  const body = `
  <main class="container order-container" style="max-width:960px;margin:24px auto;padding:0 16px;">
    <div class="panel">
      <h2 style="font-size:18px;margin:0 0 14px;">订单支付</h2>
      <div class="pay-order-status" style="padding:14px;background:#f6f8fa;border-radius:8px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;">
        <span>订单号：<b>${esc(order.out_trade_no)}</b></span>
        <span>状态：<b style="color:${paid ? '#4caf50' : expired ? '#999' : '#ff9800'}">${statusText}</b></span>
      </div>
      <table class="order-items" style="width:100%;border-collapse:collapse;margin-bottom:18px;">
        <thead><tr><th style="text-align:left;padding:8px;">商品</th><th>单价</th><th>数量</th><th>小计</th></tr></thead>
        <tbody>${itemsHtml}</tbody>
      </table>
      <div class="pay-amount-row" style="text-align:right;font-size:16px;margin-bottom:18px;">应付金额：<b style="color:#ff6600;font-size:22px;">¥${fen2yuan(order.amount)}</b></div>
      ${!paid && !expired ? `
      <div class="payment-buttons" style="display:flex;gap:12px;flex-wrap:wrap;">
        <button class="action-btn primary" id="btnMockPay" style="flex:1;min-width:200px;">模拟支付成功（测试）</button>
        ${opt(opts, 'balance_switch', 'y') === 'y' && user ? `<button class="action-btn" id="btnBalancePay" style="flex:1;min-width:200px;">余额支付（余额 ¥${fen2yuan(user.money * 100)}）</button>` : ''}
      </div>
      ${epayConfig(opts) ? `<div class="epay-tip" style="margin-top:12px;color:#888;font-size:13px;">已配置易支付网关，请在支付分页中选择微信/支付宝。</div>` : ''}
      <div style="margin-top:16px;color:#888;font-size:13px;">${countDown > 0 ? `请在 <b>${Math.ceil(countDown / 60)}</b> 分钟内完成支付，超时订单将自动取消。` : '订单已超时，请重新下单。'}</div>
      ` : paid ? `<a class="action-btn primary" href="/?action=order_result&out_trade_no=${esc(order.out_trade_no)}">查看订单结果</a>` : `<a class="action-btn" href="/">返回首页</a>`}
    </div>
  </main>
  <script>
  var NO = ${JSON.stringify(order.out_trade_no)};
  function doPay(plugin){
    $.post('/?action=pay_submit', { out_trade_no: NO, plugin: plugin }, function(res){
      if (res.code === 0) { location.href = '/?action=order_result&out_trade_no=' + NO; }
      else { layer.msg(res.msg || '支付失败'); }
    }, 'json');
  }
  $('#btnMockPay').on('click', function(){ doPay('test'); });
  $('#btnBalancePay').on('click', function(){ doPay('balance'); });
  </script>`;

  return new Response(layout(env, { ...opts, title: '订单支付' }, navItems, body), { headers: HTML_HEADERS });
}

// ============================================================
// 支付提交 (POST /?action=pay_submit) -> 发卡
// ============================================================
async function apiPaySubmit(request, env) {
  const form = await request.formData();
  const no = form.get('out_trade_no') || '';
  const plugin = form.get('plugin') || 'test';
  const order = await env.DB.prepare('SELECT * FROM dc_order WHERE out_trade_no = ? LIMIT 1').bind(no).first();
  if (!order) return json({ code: 1, msg: '订单不存在' });
  if (order.pay_status == 1) return json({ code: 0, msg: '已支付' });

  const t = now();
  if (plugin === 'balance') {
    const uid = await getUserUid(request, env);
    if (!uid) return json({ code: 1, msg: '请先登录' });
    const user = await env.DB.prepare('SELECT * FROM dc_user WHERE uid = ?').bind(uid).first();
    const moneyFen = Math.round((user.money || 0) * 100);
    if (moneyFen < order.amount) return json({ code: 1, msg: '余额不足' });
    await env.DB.batch([
      env.DB.prepare('UPDATE dc_user SET money = round((money * 100 - ?) / 100.0, 2) WHERE uid = ?').bind(order.amount, uid),
      env.DB.prepare('UPDATE dc_order SET pay_status = 1, status = 1, pay_time = ?, payment = ?, pay_plugin = ?, up_no = ? WHERE id = ?').bind(t, '余额支付', 'balance', no + 'B', order.id),
    ]);
  } else {
    await env.DB.prepare('UPDATE dc_order SET pay_status = 1, status = 1, pay_time = ?, payment = ?, pay_plugin = ?, up_no = ? WHERE id = ?').bind(t, '测试支付', 'test', no + 'T', order.id);
  }

  await deliver(env, order.id);
  return json({ code: 0 });
}

// 自动发卡：order_list -> once/general/service
async function deliver(env, orderId) {
  const list = (await env.DB.prepare('SELECT * FROM dc_order_list WHERE order_id = ?').bind(orderId).all()).results;
  const t = now();
  const batch = [];
  for (const ol of list) {
    const g = await env.DB.prepare('SELECT * FROM dc_goods WHERE id = ?').bind(ol.goods_id).first();
    if (!g) continue;
    if (g.type === 'once') {
      const rows = (await env.DB.prepare('SELECT id FROM dc_goods_once WHERE goods_id = ? AND sku = ? AND sale_time IS NULL ORDER BY id ASC LIMIT ?').bind(g.goods_id || ol.goods_id, ol.sku, ol.quantity).all()).results;
      if (rows.length) {
        const ids = rows.map((r) => r.id);
        batch.push(env.DB.prepare('UPDATE dc_goods_once SET sale_time = ?, order_list_id = ? WHERE id IN (' + ids.map(() => '?').join(',') + ')').bind(t, ol.id, ...ids));
      }
      batch.push(env.DB.prepare('UPDATE dc_order_list SET status = ? WHERE id = ?').bind(rows.length >= ol.quantity ? 2 : 1, ol.id));
    } else if (g.type === 'general') {
      const row = await env.DB.prepare('SELECT * FROM dc_goods_general WHERE goods_id = ? AND sku = ? LIMIT 1').bind(ol.goods_id, ol.sku).first();
      if (row) {
        batch.push(env.DB.prepare('INSERT INTO dc_goods_general_sale (goods_id, order_list_id, sku, content, num, create_time) VALUES (?,?,?,?,?,?)').bind(ol.goods_id, ol.id, ol.sku, row.content, ol.quantity, t));
        batch.push(env.DB.prepare('UPDATE dc_order_list SET status = 2 WHERE id = ?').bind(ol.id));
      }
    } else if (g.type === 'service') {
      const row = await env.DB.prepare('SELECT * FROM dc_goods_service WHERE goods_id = ? AND sku = ? LIMIT 1').bind(ol.goods_id, ol.sku).first();
      if (row) {
        batch.push(env.DB.prepare('INSERT INTO dc_goods_service_sale (goods_id, order_list_id, sku, content, num, is_default, create_time) VALUES (?,?,?,?,?,?,?)').bind(ol.goods_id, ol.id, ol.sku, row.content, ol.quantity, 'y', t));
        batch.push(env.DB.prepare('UPDATE dc_order_list SET status = 2 WHERE id = ?').bind(ol.id));
      }
    }
    // 扣库存 + 加销量（原子）
    if (parseInt(g.stock) > 0) {
      batch.push(env.DB.prepare('UPDATE dc_skus SET stock = MAX(0, stock - ?), sales = sales + ? WHERE goods_id = ? AND sku = ?').bind(ol.quantity, ol.quantity, ol.goods_id, ol.sku));
      batch.push(env.DB.prepare('UPDATE dc_goods SET stock = MAX(0, stock - ?), sales = sales + ? WHERE id = ?').bind(ol.quantity, ol.quantity, ol.goods_id));
    }
  }
  if (batch.length) {
    batch.push(env.DB.prepare('UPDATE dc_order SET status = 2, update_time = ? WHERE id = ?').bind(t, orderId));
    await env.DB.batch(batch);
  } else {
    await env.DB.prepare('UPDATE dc_order SET status = 1, update_time = ? WHERE id = ?').bind(t, orderId);
  }
}

// ============================================================
// 订单结果页 (GET /?action=order_result&out_trade_no=...)
// ============================================================
async function pageOrderResult(request, env, q) {
  const { opts, navItems } = await ctx(env);
  const no = q.out_trade_no || '';
  const order = await env.DB.prepare('SELECT * FROM dc_order WHERE out_trade_no = ? LIMIT 1').bind(no).first();
  if (!order) return new Response('订单不存在', { status: 404, headers: HTML_HEADERS });

  const lists = (await env.DB.prepare('SELECT ol.*, g.title, g.cover, g.pay_content FROM dc_order_list ol LEFT JOIN dc_goods g ON g.id = ol.goods_id WHERE ol.order_id = ?').bind(order.id).all()).results;

  // 收集卡密
  let kamiLines = [];
  for (const l of lists) {
    if (l.status < 2) continue;
    const once = await env.DB.prepare('SELECT content FROM dc_goods_once WHERE order_list_id = ? ORDER BY id ASC').bind(l.id).all();
    for (const r of once.results) kamiLines.push({ content: r.content });
    const general = await env.DB.prepare('SELECT content, num FROM dc_goods_general_sale WHERE order_list_id = ?').bind(l.id).all();
    for (const r of general.results) for (let i = 0; i < r.num; i++) kamiLines.push({ content: r.content });
    const service = await env.DB.prepare('SELECT content, is_default FROM dc_goods_service_sale WHERE order_list_id = ?').bind(l.id).all();
    for (const r of service.results) kamiLines.push({ content: r.content });
  }

  const statusMap = { 0: ['待支付', '#ff9800'], 1: ['已支付待发货', '#ff9800'], 2: ['已完成', '#4caf50'], 3: ['已取消', '#999'] };
  const [statusText, statusColor] = statusMap[order.status] || ['未知', '#999'];

  const orderCards = lists
    .map(
      (l) => `<div class="order-card gift-card">
    <div class="order-header-info">
      <span class="order-no">${esc(order.out_trade_no)}</span>
      <span class="order-status paid" style="color:${statusColor}">${statusText}</span>
    </div>
    <div class="order-goods">
      <div class="goods-item"><div class="goods-info"><div class="goods-name">${esc(l.title)}</div>${l.attr_spec ? `<div class="goods-spec">${l.attr_spec}</div>` : ''}</div></div>
    </div>
    <div class="order-amount">
      <div class="pay-time">${order.pay_time ? ts2str(order.pay_time) : ''}</div>
      <div class="amount-info">共${l.quantity}件，合计 <b style="color:#ff6600;">¥${fen2yuan(l.price)}</b></div>
    </div>
  </div>`
    )
    .join('');

  const kamiHtml =
    kamiLines.length > 0
      ? buildKamiHtml(kamiLines)
      : '';
  const body = `
  <main class="result-body" style="max-width:860px;margin:24px auto;padding:0 16px;">
    <div class="order-list-page" id="orderListPage">
      <div class="result-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div class="result-title" style="font-size:18px;font-weight:600;">订单结果</div>
        <div class="result-count"><span>共找到 ${1} 个订单</span></div>
      </div>
      <div class="order-list">${orderCards}</div>
      ${kamiHtml}
      <div style="margin-top:20px;text-align:center;">
        <a class="action-btn" href="/?action=order_query">查询其他订单</a>
        <a class="btn-rebuy" href="/" style="display:inline-block;margin-left:10px;">再买一单</a>
      </div>
    </div>
  </main>`;

  return new Response(layout(env, { ...opts, title: '订单结果' }, navItems, body), { headers: HTML_HEADERS });
}

// ============================================================
// 卡密 API (GET /?action=order_kami&out_trade_no=...)
// ============================================================
async function apiKami(request, env, q) {
  const no = q.out_trade_no || '';
  const order = await env.DB.prepare('SELECT * FROM dc_order WHERE out_trade_no = ? LIMIT 1').bind(no).first();
  if (!order) return json({ code: 1, msg: '订单不存在' });
  const lists = (await env.DB.prepare('SELECT * FROM dc_order_list WHERE order_id = ?').bind(order.id).all()).results;
  const out = [];
  for (const l of lists) {
    if (l.status < 2) continue;
    const once = await env.DB.prepare('SELECT content FROM dc_goods_once WHERE order_list_id = ?').bind(l.id).all();
    for (const r of once.results) out.push({ content: r.content });
    const general = await env.DB.prepare('SELECT content, num FROM dc_goods_general_sale WHERE order_list_id = ?').bind(l.id).all();
    for (const r of general.results) for (let i = 0; i < r.num; i++) out.push({ content: r.content });
    const service = await env.DB.prepare('SELECT content, is_default FROM dc_goods_service_sale WHERE order_list_id = ?').bind(l.id).all();
    for (const r of service.results) out.push({ content: r.content });
  }
  return json({ code: 0, status: order.status, list: out });
}

// ============================================================
// 订单查询 (GET /?action=order_query & POST)
// ============================================================
async function pageOrderQuery(request, env, q) {
  const { opts, navItems } = await ctx(env);
  const body = `
  <main class="order-body" style="max-width:760px;margin:40px auto;padding:0 16px;">
    <div class="query-card" style="background:#fff;border:1px solid #eee;border-radius:14px;overflow:hidden;">
      <div class="query-card-bg" style="padding:40px 24px 16px;text-align:center;">
        <div class="query-title" style="font-size:20px;color:#333;">轻松查询订单...</div>
      </div>
      <div class="query-card-content" style="padding:16px 24px 28px;">
        <div class="search-box" style="display:flex;gap:10px;">
          <input id="queryInput" type="text" class="search-input" style="flex:1;padding:12px 16px;border:1px solid #ddd;border-radius:8px;" placeholder="请输入订单号 或 下单时填写的联系方式">
          <button class="search-btn" id="queryOrder" style="padding:12px 22px;background:var(--theme-primary);color:#fff;border:none;border-radius:8px;cursor:pointer;">查询</button>
        </div>
        <div class="warning-box" style="margin-top:18px;background:#fff8e6;border-radius:8px;padding:14px 16px;font-size:13px;color:#8a6d3b;">
          <div class="warning-title" style="font-weight:600;margin-bottom:4px;">温馨提示</div>
          <div class="warning-content">请使用下单时填写的联系方式（手机号/邮箱/QQ号）或订单号查询订单。如无法查到，请联系客服协助处理。</div>
        </div>
      </div>
    </div>
    <div class="result-area" id="resultArea" style="margin-top:20px;"></div>
  </main>
  <script>
  function doQuery(){
    var v = ($('#queryInput').val()||'').trim();
    if (!v) { layer.msg('请输入订单号或联系方式'); return; }
    $('#resultArea').html('<div style="text-align:center;padding:30px;color:#888;">查询中...</div>');
    $.get('/?action=order_query&q=' + encodeURIComponent(v), function(res){
      if (res.code !== 0) { $('#resultArea').html('<div class="empty-order" style="text-align:center;padding:30px;color:#888;background:#fff;border:1px solid #eee;border-radius:10px;">' + res.msg + '</div>'); return; }
      var html = '';
      for (var i=0;i<res.list.length;i++){
        var o = res.list[i];
        html += '<div class="order-card gift-card" style="background:#fff;border:1px solid #eee;border-radius:10px;padding:16px;margin-bottom:14px;">' +
          '<div style="display:flex;justify-content:space-between;align-items:center;"><span style="color:#333;">' + o.out_trade_no + '</span><span style="color:#4caf50;">' + o.status_text + '</span></div>' +
          '<div style="margin:10px 0;color:#555;font-size:14px;">' + o.title_html + '</div>' +
          '<div style="display:flex;justify-content:space-between;align-items:center;font-size:14px;color:#888;"><span>' + o.create_time_text + '</span><span>共' + o.count + '件 合计 <b style="color:#ff6600;">¥' + o.amount + '</b></span></div>' +
          (o.can_view ? '<div style="margin-top:12px;text-align:right;"><a class="action-btn primary" href="/?action=order_result&out_trade_no=' + o.out_trade_no + '" style="display:inline-block;padding:8px 18px;background:var(--theme-primary);color:#fff;border-radius:6px;text-decoration:none;">查看订单</a></div>' : '') +
        '</div>';
      }
      $('#resultArea').html('<div class="result-header" style="margin-bottom:12px;color:#555;">共找到 ' + res.list.length + ' 个订单</div>' + html);
    }, 'json');
  }
  $('#queryOrder').on('click', doQuery);
  $('#queryInput').on('keydown', function(e){ if (e.key === 'Enter') doQuery(); });
  </script>`;
  return new Response(layout(env, { ...opts, title: '订单查询' }, navItems, body), { headers: HTML_HEADERS });
}

async function apiOrderQuery(request, env, q) {
  const url = new URL(request.url);
  let query = (url.searchParams.get('q') || '').trim();
  if (!query && request.method === 'POST') {
    try {
      const fd = await request.formData();
      query = (fd.get('q') || '').trim();
    } catch (e) {}
  }
  if (!query) return json({ code: 1, msg: '请输入查询内容' });

  const orders = (
    await env.DB.prepare(
      `SELECT DISTINCT o.* FROM dc_order o
       LEFT JOIN dc_order_list ol ON ol.order_id = o.id
       LEFT JOIN dc_order_required r ON r.order_id = o.id
       WHERE o.delete_time IS NULL AND (o.out_trade_no LIKE ? OR o.up_no LIKE ? OR ol.attach_user LIKE ? OR r.content = ?)
       ORDER BY o.id DESC LIMIT 20`
    )
      .bind('%' + query + '%', '%' + query + '%', '%' + query + '%', query)
      .all()
  ).results;

  const statusText = { 0: '待支付', 1: '已支付/待发货', 2: '已完成', 3: '已取消' };
  const out = [];
  for (const o of orders) {
    const lines = (
      await env.DB.prepare('SELECT ol.*, g.title FROM dc_order_list ol LEFT JOIN dc_goods g ON g.id = ol.goods_id WHERE ol.order_id = ?').bind(o.id).all()
    ).results;
    const titleHtml = lines.map((l) => '<div>' + esc(l.title) + (l.attr_spec ? ' <span style="color:#999;">' + l.attr_spec + '</span>' : '') + ' ×' + l.quantity + '</div>').join('');
    const count = lines.reduce((a, l) => a + (l.quantity || 0), 0);
    out.push({
      out_trade_no: o.out_trade_no,
      status_text: statusText[o.status] || '未知',
      status: o.status,
      title_html: titleHtml,
      create_time_text: ts2str(o.create_time),
      amount: fen2yuan(o.amount),
      count: count,
      can_view: true,
    });
  }
  if (!out.length) return json({ code: 1, msg: '未查询到相关订单，请检查输入内容' });
  return json({ code: 0, list: out });
}

// ============================================================
// 帮助页
// ============================================================
async function pageHelp(request, env, q) {
  const { opts, navItems } = await ctx(env);
  const faqs = [
    ['如何购买商品？', '选择心仪商品 → 进入详情页选择规格和数量 → 点击立即购买 → 完成支付 → 在订单结果页查看卡密。'],
    ['付款后多久发货？', '本站全部商品 7×24 小时全自动发货，支付成功后订单结果页即刻展示卡密。'],
    ['如何查询订单？', '点击顶部「查询订单」，输入下单时填写的联系方式或订单号即可查询。'],
    ['卡密无法使用怎么办？', '请先通过订单页复制完整卡密，仍无法使用请联系客服并提供订单号，我们会尽快为您处理。'],
    ['支持哪些支付方式？', '支持微信、支付宝（易支付网关）及站内余额支付。'],
  ];
  const body = `
  <main class="help-container" style="max-width:860px;margin:28px auto;padding:0 16px;">
    <div class="help-section" style="background:#fff;border:1px solid #eee;border-radius:12px;padding:22px 24px;">
      <div class="section-title" style="font-size:17px;font-weight:600;border-left:3px solid var(--theme-primary);padding-left:10px;margin-bottom:16px;">常见问题</div>
      ${faqs
        .map(
          (f, i) => `<div class="faq-item" style="border-bottom:1px dashed #eee;padding:14px 0;">
        <div class="faq-title" style="font-weight:500;color:#333;cursor:pointer;display:flex;justify-content:space-between;"><span><span class="faq-num" style="color:var(--theme-price);margin-right:8px;">${i + 1}.</span>${f[0]}</span><span class="faq-arrow">+</span></div>
        <div class="faq-answer" style="color:#777;font-size:14px;line-height:1.8;margin-top:10px;display:none;">${f[1]}</div>
      </div>`
        )
        .join('')}
    </div>
    <div class="contact-card" style="background:#fff;border:1px solid #eee;border-radius:12px;padding:22px 24px;margin-top:16px;">
      <div class="section-title" style="font-size:17px;font-weight:600;border-left:3px solid var(--theme-primary);padding-left:10px;margin-bottom:12px;">联系方式</div>
      <div style="color:#555;font-size:14px;line-height:2;">工作时间：每日 9:00 - 22:00<br>如有问题请提供订单号咨询在线客服。</div>
    </div>
  </main>
  <script>
  $(function(){
    $('.faq-title').on('click', function(){ var a=$(this).next(); a.slideToggle(150); $(this).find('.faq-arrow').text(a.is(':visible')?'-':'+'); });
  });
  </script>`;
  return new Response(layout(env, { ...opts, title: '买家帮助' }, navItems, body), { headers: HTML_HEADERS });
}

// ============================================================
// 简单用户中心
// ============================================================
async function pageUser(request, env, q) {
  const { opts, navItems } = await ctx(env);
  const uid = await getUserUid(request, env);
  const user = uid ? await env.DB.prepare('SELECT * FROM dc_user WHERE uid = ?').bind(uid).first() : null;

  let inner;
  if (user) {
    const orders = (
      await env.DB.prepare('SELECT * FROM dc_order WHERE user_id = ? ORDER BY id DESC LIMIT 20').bind(user.uid).all()
    ).results;
    const statusText = { 0: '待支付', 1: '已支付/待发货', 2: '已完成', 3: '已取消' };
    const orderRows = orders
      .map(
        (o) => `<tr><td>${esc(o.out_trade_no)}</td><td>¥${fen2yuan(o.amount)}</td><td>${ts2str(o.create_time)}</td><td>${statusText[o.status] || '未知'}</td><td>${o.pay_status == 1 ? `<a href="/?action=order_result&out_trade_no=${esc(o.out_trade_no)}">查看</a>` : `<a href="/?action=pay&out_trade_no=${esc(o.out_trade_no)}">支付</a>`}</td></tr>`
      )
      .join('');
    inner = `<div style="background:#fff;border:1px solid #eee;border-radius:12px;padding:20px 24px;">
      <h2 style="font-size:18px;margin:0 0 10px;">你好，${esc(user.nickname || user.username)}</h2>
      <div style="color:#777;font-size:14px;margin-bottom:18px;">余额：<b style="color:#ff6600;">¥${fen2yuan(Math.round((user.money || 0) * 100))}</b></div>
      <table style="width:100%;border-collapse:collapse;font-size:14px;"><thead><tr style="background:#f6f8fa;"><th style="padding:8px;text-align:left;">订单号</th><th style="padding:8px;text-align:left;">金额</th><th style="padding:8px;text-align:left;">时间</th><th style="padding:8px;text-align:left;">状态</th><th style="padding:8px;text-align:left;">操作</th></tr></thead><tbody>${orderRows || '<tr><td colspan="5" style="padding:20px;text-align:center;color:#999;">暂无订单</td></tr>'}</tbody></table>
      <div style="margin-top:18px;"><a href="/?action=user&logout=1" style="color:#e53e3e;">退出登录</a></div>
    </div>`;
  } else {
    inner = `<div style="background:#fff;border:1px solid #eee;border-radius:12px;padding:30px 24px;max-width:420px;margin:0 auto;">
      <h2 style="font-size:18px;margin:0 0 16px;">会员登录</h2>
      <div style="margin-bottom:12px;"><input id="lUser" class="search-input" style="width:100%;padding:11px 14px;border:1px solid #ddd;border-radius:8px;box-sizing:border-box;" placeholder="用户名"></div>
      <div style="margin-bottom:16px;"><input id="lPwd" type="password" class="search-input" style="width:100%;padding:11px 14px;border:1px solid #ddd;border-radius:8px;box-sizing:border-box;" placeholder="密码"></div>
      <button id="btnLogin" style="width:100%;padding:12px;background:var(--theme-primary);color:#fff;border:none;border-radius:8px;cursor:pointer;">登 录</button>
      <div style="margin-top:10px;color:#999;font-size:13px;">游客可直接下单，登录后可查看余额与订单。</div>
    </div>
    <script>
    $('#btnLogin').on('click', function(){
      $.post('/?action=login', { username: $('#lUser').val(), password: $('#lPwd').val() }, function(res){
        if (res.code === 0) { location.reload(); } else { layer.msg(res.msg || '登录失败'); }
      }, 'json');
    });
    </script>`;
  }

  const body = `<main class="blog-container" style="max-width:960px;margin:30px auto;padding:0 16px;">${inner}</main>`;
  return new Response(layout(env, { ...opts, title: '会员中心' }, navItems, body), { headers: HTML_HEADERS });
}

async function apiLogin(request, env) {
  const form = await request.formData();
  const username = form.get('username') || '';
  const password = form.get('password') || '';
  const user = await env.DB.prepare('SELECT * FROM dc_user WHERE username = ? AND delete_time IS NULL LIMIT 1').bind(username).first();
  if (!user) return json({ code: 1, msg: '用户不存在' });
  if (!user.password || user.password !== password) return json({ code: 1, msg: '密码错误' });
  const token = await makeToken(env, user.uid);
  const res = json({ code: 0 });
  res.headers.append('Set-Cookie', 'dc_token=' + token + '; Path=/; HttpOnly; Max-Age=604800');
  return res;
}

// 简单 token: base64(uid.sig)
async function makeToken(env, uid) {
  const secret = env.SECRET || 'dc-faka-secret';
  const data = String(uid) + '.' + String(Math.floor(Date.now() / 1000));
  const sig = await hmacSha256(secret, data);
  return btoa(urlsafe(data + '.' + sig));
}

async function getUserUid(request, env) {
  const cookies = parseCookies(request.headers.get('Cookie') || '');
  const t = cookies.dc_token;
  if (!t) return 0;
  try {
    const decoded = atob(t);
    const parts = decoded.split('.');
    if (parts.length !== 3) return 0;
    const data = parts[0] + '.' + parts[1];
    const sig = await hmacSha256(env.SECRET || 'dc-faka-secret', data);
    if (sig !== parts[2]) return 0;
    return parseInt(parts[0]) || 0;
  } catch (e) {
    return 0;
  }
}

export async function verifyToken(env, token) {
  try {
    const decoded = atob(token);
    const parts = decoded.split('.');
    if (parts.length !== 3) return 0;
    const secret = env.SECRET || 'dc-faka-secret';
    const data = parts[0] + '.' + parts[1];
    const sig = await hmacSha256(secret, data);
    if (sig !== parts[2]) return 0;
    return parseInt(parts[0]);
  } catch (e) {
    return 0;
  }
}

function parseCookies(c) {
  const o = {};
  for (const kv of c.split(';')) {
    const i = kv.indexOf('=');
    if (i > 0) o[kv.slice(0, i).trim()] = kv.slice(i + 1).trim();
  }
  return o;
}

// ============================================================
// RSS
// ============================================================
async function pageRss(request, env, q) {
  const { opts } = await ctx(env);
  const goods = (
    await env.DB.prepare('SELECT * FROM dc_goods WHERE delete_time IS NULL AND is_on_shelf = 1 ORDER BY id DESC LIMIT 20').all()
  ).results;
  const siteName = opt(opts, 'blogname', 'DCSHOP');
  const url = request.url;
  let items = '';
  for (const g of goods) {
    const link = url.split('?')[0] + '/?action=goods&id=' + g.id;
    items += `<item><title>${esc(g.title)}</title><link>${link}</link><description>${esc(g.des || '')}</description></item>`;
  }
  return new Response(
    `<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>${esc(siteName)}</title><link>${url.split('?')[0]}</link><description>${esc(siteName)} - 最新商品</description>${items}</channel></rss>`,
    { headers: { 'content-type': 'application/rss+xml; charset=utf-8' } }
  );
}

// ============================================================
// 取消订单
// ============================================================
async function apiOrderCancel(request, env) {
  const form = await request.formData();
  const no = form.get('out_trade_no') || '';
  const order = await env.DB.prepare('SELECT * FROM dc_order WHERE out_trade_no = ? LIMIT 1').bind(no).first();
  if (!order) return json({ code: 1, msg: '订单不存在' });
  if (order.pay_status == 1) return json({ code: 1, msg: '已支付订单不可取消' });
  await env.DB.prepare('UPDATE dc_order SET status = 3 WHERE id = ? AND status = 0').bind(order.id).run();
  return json({ code: 0 });
}

// ============================================================
// 工具
// ============================================================
function buildKamiHtml(kamiLines) {
  const items = kamiLines
    .map(
      (k, i) =>
        '<div class="kami-item"><span class="kami-index">' +
        (i + 1) +
        '</span><code>' +
        esc(k.content) +
        '</code><button class="kami-item-copy" data-c="' +
        esc(k.content) +
        '">复制</button></div>'
    )
    .join('');
  const allText = JSON.stringify(kamiLines.map((k) => k.content).join('\n'));
  return (
    '<div class="kami-list" id="kamiList">' +
    '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">' +
    '<b>卡密信息（共 ' +
    kamiLines.length +
    ' 条）</b>' +
    '<button class="action-btn" id="btnCopyAll">一键复制</button></div>' +
    items +
    '</div>' +
    '<script>' +
    '$(\'#btnCopyAll\').on(\'click\', function(){' +
    '  var t = ' +
    allText +
    ';' +
    '  if (navigator.clipboard) navigator.clipboard.writeText(t).then(function(){ layer.msg(\'已复制全部\'); });' +
    '  else { var ta=document.createElement(\'textarea\'); ta.value=t; document.body.appendChild(ta); ta.select(); document.execCommand(\'copy\'); document.body.removeChild(ta); layer.msg(\'已复制全部\'); }' +
    '});' +
    '$(document).on(\'click\', \'.kami-item-copy\', function(){' +
    '  var t = $(this).data(\'c\');' +
    '  if (navigator.clipboard) navigator.clipboard.writeText(t).then(function(){ layer.msg(\'已复制\'); });' +
    '  else { var ta=document.createElement(\'textarea\'); ta.value=t; document.body.appendChild(ta); ta.select(); document.execCommand(\'copy\'); document.body.removeChild(ta); layer.msg(\'已复制\'); }' +
    '});' +
    '</script>' +
    '<style>' +
    '.kami-list{margin-top:18px;background:#fff;border:1px solid #eee;border-radius:10px;padding:18px;}' +
    '.kami-item{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px dashed #eee;}' +
    '.kami-item:last-child{border-bottom:none;}' +
    '.kami-index{width:24px;height:24px;border-radius:50%;background:var(--theme-primary);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;}' +
    '.kami-item code{flex:1;word-break:break-all;color:#333;}' +
    '.kami-item-copy{color:var(--theme-primary);cursor:pointer;background:none;border:none;font-size:13px;}' +
    '.action-btn{padding:8px 18px;border-radius:6px;border:1px solid #ddd;background:#fff;color:#555;cursor:pointer;}' +
    '.action-btn.primary{background:var(--theme-primary);border-color:var(--theme-primary);color:#fff;}' +
    '</style>'
  );
}

function json(o) {
  return new Response(JSON.stringify(o), { headers: JSON_HEADERS });
}

function dateStr() {
  const d = new Date();
  const p = (x) => String(x).padStart(2, '0');
  return '' + d.getFullYear() + p(d.getMonth() + 1) + p(d.getDate()) + p(d.getHours()) + p(d.getMinutes()) + p(d.getSeconds());
}

// 供 admin.js 使用
export { parseCookies, getUserUid, makeToken, getOptions as _getOptions };