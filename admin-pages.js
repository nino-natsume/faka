// ============================================================
// acg-faka Worker 移植 - admin-pages.js
// 后台页面渲染（原版 assets + 等价 HTML，对齐原版模板结构）
//   /admin/authentication/login  → renderAdminLoginPage
//   /admin/**  → renderAdminShell + 具体页面
//   /admin/dashboard/index       → renderAdminDashboardPage
// ============================================================
import { htmlEscape } from './lib.js';
import { MANAGE_SESSION } from './admin.js';

// ---------- admin_var() 等价: setVar 注入 ----------
export function adminVar(cfg = {}) {
  const langs = [{ code: 'zh-cn', name: '简体中文' }];
  const vars = {
    DEBUG: false,
    LANG: 'zh-cn',
    LANGS: langs,
    CURRENCY: { code: 'CNY', symbol: '¥', rate: 1, decimals: 2 },
    HACK_ROUTE_TABLE_COLUMNS: [],
    HACK_SUBMIT_FORM: [],
    HACK_SUBMIT_TAB: [],
    HACK_ROUTE_TABLE_SEARCH: [],
  };
  let s = '<script>';
  for (const [k, v] of Object.entries(vars)) {
    s += `setVar(${JSON.stringify(k)}, ${JSON.stringify(v)});`;
  }
  s += '</script>';
  return s;
}

const cssLinks = (paths) => paths.map(p => `<link rel="stylesheet" href="${p}"/>`).join('\n');
const jsScripts = (paths) => paths.map(p => `<script src="${p}"></script>`).join('\n');

// ---------- 登录页 (对齐 Authentication/Login.html) ----------
export function renderAdminLoginPage(cfg = {}) {
  const bg = cfg.background_url || '/assets/admin/img/bg.jpg';
  const shopName = cfg.shop_name || 'acg-faka';
  const captcha = String(cfg.admin_login_verification) !== '0'
    ? `<div class="ay-field has-ico">
          <input id="ay-captcha" name="captcha" class="ay-input" type="text" inputmode="numeric"
                 maxlength="4" autocomplete="off" placeholder=" " required>
          <span class="ay-label">验证码</span>
          <span class="ay-ico" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
            </svg>
          </span>
          <img id="ay-captcha-img" class="ay-captcha" src="/user/captcha/image?action=adminLogin"
               data-acg-refresh="/user/captcha/image?action=adminLogin"
               title="看不清？点我刷新" alt="验证码">
        </div>`
    : '';
  return `<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>登录 - ${htmlEscape(shopName)}</title>
    <script>(function(){try{var p=localStorage.getItem('admin-theme')||'auto';var d=p==='auto'?(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'):p;var e=document.documentElement;e.setAttribute('data-theme',d);e.setAttribute('data-theme-pref',p);}catch(_){document.documentElement.setAttribute('data-theme','light');}})();</script>
    ${cssLinks([
      '/assets/common/css/_.css',
      '/assets/admin/css/auth.css',
      '/assets/admin/css/_material-auth.css',
      '/assets/admin/css/style.bundle.css',
      '/assets/common/css/font.min.css',
      '/assets/common/js/layui/css/layui.css',
      '/assets/common/css/select2.min.css',
      '/assets/common/css/component.css',
      '/assets/common/css/toastr.min.css',
      '/assets/common/js/table/bootstrap-table.css',
      '/assets/common/js/layer/theme/default/layer.css',
      '/assets/admin/css/auth.css',
      '/assets/common/css/md-tokens.css',
      '/assets/admin/css/material-auth.css'
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
        <button type="button" class="ay-theme" id="ay-theme" aria-label="切换明暗主题" title="切换明暗主题">
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
            <h1 id="ay-title" class="ay-title">欢迎回来，指挥官</h1>
            <p id="ay-sub" class="ay-sub">正在验证您的管理员身份</p>
        </header>

        <div class="ay-body">
            <form id="ay-form" method="post" novalidate>
                <div class="ay-field has-ico">
                    <input id="ay-user" name="username" class="ay-input" type="text" placeholder=" "
                           autocomplete="username" autofocus required>
                    <span class="ay-label">邮箱</span>
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
                    <span class="ay-label">密码</span>
                    <span class="ay-ico" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </span>
                    <button type="button" class="ay-eye" id="ay-eye" aria-label="显示密码" aria-controls="ay-pass">
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
                    <span class="ay-caps" id="ay-caps" hidden>大写锁定已开启</span>
                </div>
                ${captcha}
                <div class="ay-field has-ico ay-2fa is-hidden">
                    <input id="ay-code" name="code" class="ay-input" type="text" inputmode="numeric"
                           autocomplete="one-time-code" maxlength="6" placeholder=" ">
                    <span class="ay-label">谷歌验证码</span>
                    <span class="ay-ico" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0 3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                        </svg>
                    </span>
                </div>

                <div class="ay-row">
                    <label class="ay-check"><input type="checkbox" id="ay-remember" name="remember" value="1">保持登录(365天)</label>
                    <a class="ay-link" href="javascript:void(0)" data-acg-action="message.info" data-acg-args='["查看官方文档重置密码方法"]'>忘记密码？</a>
                </div>

                <button class="ay-btn" type="submit" id="ay-submit">确认登入</button>
            </form>
            <div class="ay-foot">© ${htmlEscape(shopName)}</div>
        </div>
    </section>
</main>

<script>ready("/assets/admin/controller/auth/login.js");</script>
${jsScripts([
  '/assets/common/js/_.js',
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
  '/assets/common/js/component/decimal.js'
])}
</body>
</html>`;
}

// ---------- 通用 CRUD 列表页 (自绘, 复用原版 assets) ----------
export function renderCrudPage({ cfg, manage, title, activePath, toolbar = null, body, readyJs = '' }) {
  return renderAdminShell({
    cfg, manage, title, activePath, toolbar,
    body: `${body}<script>${readyJs}</script>`,
  });
}

// 分类管理页
export function renderAdminCategoryPage(cfg, manage) {
  const body = `
<div class="card mb-5 mb-xl-8">
  <div class="card-header border-0">
    <div class="card-toolbar">
      <button class="btn btn-sm btn-light-primary crud-add me-3"><i class="fa-duotone fa-regular fa-circle-plus"></i> 添加分类</button>
      <button class="btn btn-sm btn-light-success crud-status-on me-3"><i class="fa-duotone fa-regular fa-circle-play"></i> 启用选中</button>
      <button class="btn btn-sm btn-light-dark crud-status-off me-3"><i class="fa-duotone fa-regular fa-circle-stop"></i> 停用选中</button>
      <button class="btn btn-sm btn-light-danger crud-del me-3"><i class="fa-duotone fa-regular fa-trash-can"></i> 移除选中</button>
    </div>
  </div>
  <div class="card-body py-3">
    <div class="table-responsive">
      <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3 crud-table" id="category-table">
        <thead><tr class="fw-bold text-muted">
          <th style="width:40px"><input type="checkbox" class="crud-check-all"></th>
          <th>ID</th><th>名称</th><th>父级</th><th>排序</th><th>状态</th><th>操作</th>
        </tr></thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>
<div class="modal fade" tabindex="-1" id="catModal"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header py-3"><h5 class="modal-title">编辑分类</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <form class="modal-form"><div class="modal-body">
    <input type="hidden" name="id">
    <div class="mb-3"><label class="form-label">上级分类</label>
      <select class="form-select" name="pid"><option value="0">顶级</option></select></div>
    <div class="mb-3"><label class="form-label">分类名称</label>
      <input class="form-control" name="name" required></div>
    <div class="mb-3"><label class="form-label">排序</label>
      <input class="form-control" name="sort" type="number" value="0"></div>
    <div class="mb-3 form-check"><label class="form-check-label">
      <input class="form-check-input" type="checkbox" name="status" value="1" checked> 启用</label></div>
    <div class="mb-3 form-check"><label class="form-check-label">
      <input class="form-check-input" type="checkbox" name="hide" value="1"> 隐藏</label></div>
  </div><div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
    <button type="submit" class="btn btn-primary">保存</button>
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
          sel.innerHTML = '<option value="0">顶级</option>' + cats.filter(c => Number(c.id) !== curId).map(c => '<option value="' + c.id + '">' + c.name + '</option>').join('');
          cats.forEach(c => {
            const tr = document.createElement('tr');
            tr.dataset.id = c.id; tr.dataset.name = c.name; tr.dataset.pid = c.pid || 0;
            tr.dataset.sort = c.sort || 0; tr.dataset.status = c.status; tr.dataset.hide = c.hide || 0;
            const indent = Number(c.pid) ? '&nbsp;&nbsp;└ ' : '';
            tr.innerHTML = '<td><input type="checkbox" class="crud-check"></td>' +
              '<td>' + c.id + '</td>' +
              '<td>' + indent + c.name + '</td>' +
              '<td>' + (Number(c.pid) ? '#' + c.pid : '-') + '</td>' +
              '<td>' + c.sort + '</td>' +
              '<td>' + (Number(c.status) === 1 ? '<span class="badge badge-light-success">启用</span>' : '<span class="badge badge-light-danger">停用</span>') + '</td>' +
              '<td><button class="btn btn-sm btn-light-primary me-2 row-edit">编辑</button>' +
              '<button class="btn btn-sm btn-light-danger row-del">删除</button></td>';
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
        message.confirm && message.confirm ? message.confirm({ title: '确认删除分类 ' + tr.dataset.name + ' ?', done: () => gotoDel([tr.dataset.id]) }) : (confirm('确认删除分类 ' + tr.dataset.name + ' ?') && gotoDel([tr.dataset.id]));
      }
    });
    function gotoDel(ids) {
      // 简单删除(不强制预览 token; 兼容原版前端则需 deleteImpact+del)
      util.post({ url: API + 'del', data: { list: ids.join(','), preview_token: '' },
        done: () => { message.success && message.success(res => res) && load(); load(); toastr && toastr.success('已删除'); load(); },
        error: res => { if (res.msg.indexOf('预览') >= 0) { message.error(res.msg); } else message.error(res.msg); } });
      load();
    }
    document.querySelector('.crud-del').addEventListener('click', () => {
      const ids = selected(); if (!ids.length) return message.error('请先勾选分类');
      gotoDel(ids);
    });
    document.querySelector('.crud-status-on').addEventListener('click', () => {
      const ids = selected(); if (!ids.length) return message.error('请先勾选分类');
      util.post({ url: API + 'status', data: { list: ids.join(','), status: '1' }, done: load, error: res => message.error(res.msg) });
    });
    document.querySelector('.crud-status-off').addEventListener('click', () => {
      const ids = selected(); if (!ids.length) return message.error('请先勾选分类');
      util.post({ url: API + 'status', data: { list: ids.join(','), status: '0' }, done: load, error: res => message.error(res.msg) });
    });
    document.querySelector('.modal-form').addEventListener('submit', e => {
      e.preventDefault();
      const data = util.serializeObject ? util.serializeObject('.modal-form') : Object.fromEntries(new FormData(e.target).entries());
      if (typeof data.status === 'undefined') data.status = '1';
      if (typeof data.hide === 'undefined') data.hide = '0';
      data.name = e.target.querySelector('input[name="name"]').value.trim();
      if (!data.name) return message.error('分类名称不能为空');
      util.post({ url: API + 'save', data, done: () => { message.success && message.success('保存成功'); load();
        if (window.bootstrap) bootstrap.Modal.getInstance(document.getElementById('catModal'))?.hide(); },
        error: res => message.error(res.msg) });
    });
    load();
  });
  `;
  return renderCrudPage({ cfg, manage, title: '分类管理', activePath: '/admin/category/index', body, readyJs: js });
}

// 商品管理页
export function renderAdminCommodityPage(cfg, manage) {
  const body = `
<div class="card mb-5 mb-xl-8">
  <div class="card-header border-0">
    <div class="card-toolbar">
      <button class="btn btn-sm btn-light-primary crud-add me-3"><i class="fa-duotone fa-regular fa-circle-plus"></i> 添加商品</button>
      <button class="btn btn-sm btn-light-success crud-status-on me-3"><i class="fa-duotone fa-regular fa-circle-play"></i> 启用选中</button>
      <button class="btn btn-sm btn-light-dark crud-status-off me-3"><i class="fa-duotone fa-regular fa-circle-stop"></i> 停用选中</button>
      <button class="btn btn-sm btn-light-danger crud-del me-3"><i class="fa-duotone fa-regular fa-trash-can"></i> 移除选中</button>
    </div>
  </div>
  <div class="card-body py-3">
    <div class="mb-3"><input class="form-control" id="commodity-search" placeholder="搜索商品名称…" style="max-width:280px"></div>
    <div class="table-responsive">
      <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3" id="commodity-table">
        <thead><tr class="fw-bold text-muted">
          <th style="width:40px"><input type="checkbox" class="crud-check-all"></th>
          <th>ID</th><th>封面</th><th>名称</th><th>分类</th><th>价格</th><th>库存</th><th>状态</th><th>操作</th>
        </tr></thead>
        <tbody></tbody>
      </table>
      <div class="d-flex justify-content-end align-items-center mt-3">
        <button class="btn btn-sm btn-secondary crud-prev me-2">上一页</button>
        <span class="crud-pageinfo me-2"></span>
        <button class="btn btn-sm btn-secondary crud-next">下一页</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" tabindex="-1" id="commodityModal"><div class="modal-dialog modal-lg"><div class="modal-content">
  <div class="modal-header py-3"><h5 class="modal-title">编辑商品</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <form class="modal-form"><div class="modal-body">
    <input type="hidden" name="id">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">商品名称</label><input class="form-control" name="name" required></div>
      <div class="col-md-6"><label class="form-label">分类</label><select class="form-select" name="category_id"></select></div>
      <div class="col-md-4"><label class="form-label">售价</label><input class="form-control" name="price" type="number" step="0.01" value="0"></div>
      <div class="col-md-4"><label class="form-label">会员价</label><input class="form-control" name="user_price" type="number" step="0.01" value="0"></div>
      <div class="col-md-4"><label class="form-label">成本价</label><input class="form-control" name="factory_price" type="number" step="0.01" value="0"></div>
      <div class="col-md-6"><label class="form-label">封面 URL</label><input class="form-control" name="cover" placeholder="/favicon.ico"></div>
      <div class="col-md-6"><label class="form-label">商品编码</label><input class="form-control" name="code"></div>
      <div class="col-md-4"><label class="form-label">发货方式</label>
        <select class="form-select" name="delivery_way">
          <option value="0">自动发货(卡密)</option><option value="1">手动发货</option><option value="2">API 供货(预留)</option>
        </select></div>
      <div class="col-md-4"><label class="form-label">自动发货模式</label>
        <select class="form-select" name="delivery_auto_mode"><option value="0">顺序</option><option value="1">随机</option></select></div>
      <div class="col-md-4"><label class="form-label">排序</label><input class="form-control" name="sort" type="number" value="0"></div>
      <div class="col-md-6"><label class="form-label">联系方式类型</label>
        <select class="form-select" name="contact_type"><option value="0">无</option><option value="1">QQ</option><option value="2">邮箱</option><option value="3">手机号</option><option value="4">任意</option></select></div>
      <div class="col-md-6"><label class="form-label">密码状态</label>
        <select class="form-select" name="password_status"><option value="0">无密码</option><option value="1">页面设置密码</option></select></div>
      <div class="col-12"><label class="form-label">发货说明/卡密提示</label><textarea class="form-control" name="delivery_message" rows="2"></textarea></div>
      <div class="col-12"><label class="form-label">商品介绍</label><textarea class="form-control" name="description" rows="3"></textarea></div>
      <div class="col-md-3 form-check"><label class="form-check-label">
        <input class="form-check-input" type="checkbox" name="status" value="1" checked> 启用</label></div>
      <div class="col-md-3 form-check"><label class="form-check-label">
        <input class="form-check-input" type="checkbox" name="api_status" value="1"> API开放</label></div>
      <div class="col-md-3 form-check"><label class="form-check-label">
        <input class="form-check-input" type="checkbox" name="only_user" value="1"> 仅会员</label></div>
      <div class="col-md-3 form-check"><label class="form-check-label">
        <input class="form-check-input" type="checkbox" name="recommend" value="1"> 推荐</label></div>
    </div>
  </div><div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
    <button type="submit" class="btn btn-primary">保存</button>
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
              '<td>￥' + c.price + '</td>' +
              '<td>' + (c.card_count !== undefined ? c.card_count : c.stock ?? '-') + '</td>' +
              '<td>' + (Number(c.status) === 1 ? '<span class="badge badge-light-success">启用</span>' : '<span class="badge badge-light-danger">停用</span>') + '</td>' +
              '<td><button class="btn btn-sm btn-light-primary me-2 row-edit">编辑</button>' +
              '<button class="btn btn-sm btn-light-danger row-del">删除</button>' +
              '<a class="btn btn-sm btn-light-info row-cards" href="/admin/card/index?commodity_id=' + c.id + '">卡密</a></td>';
            tbody.appendChild(tr);
          });
          document.querySelector('.crud-pageinfo').textContent = '第 ' + page + ' 页 / 共 ' + res.data.count + ' 条';
        },
        error: res => message.error(res.msg) });
    }
    function selected() { return [...tbody.querySelectorAll('.crud-check:checked')].map(x => x.closest('tr').dataset.id); }
    document.querySelector('.crud-check-all').addEventListener('change', e => tbody.querySelectorAll('.crud-check').forEach(x => x.checked = e.target.checked));
    function refreshCats() {
      util.post({ url: '/admin/api/category/data', loader: false, done: res => {
        const sel = document.querySelector('select[name="category_id"]');
        const cur = sel.dataset.cur || '';
        sel.innerHTML = '<option value="">请选择</option>' + (res.data.list || []).map(c => '<option value="' + c.id + '">' + c.name + '</option>').join('');
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
        if (confirm('确认删除商品 ' + tr.dataset.name + ' ?')) {
          util.post({ url: API + 'del', data: { list: tr.dataset.id }, done: load, error: res => message.error(res.msg) });
        }
      }
    });
    function batch(field, val) {
      const ids = selected(); if (!ids.length) return message.error('请先勾选商品');
      util.post({ url: API + field, data: { list: ids.join(','), status: val }, done: load, error: res => message.error(res.msg) });
    }
    document.querySelector('.crud-status-on').addEventListener('click', () => batch('status', '1'));
    document.querySelector('.crud-status-off').addEventListener('click', () => batch('status', '0'));
    document.querySelector('.crud-del').addEventListener('click', () => {
      const ids = selected(); if (!ids.length) return message.error('请先勾选商品');
      if (confirm('确认删除选中 ' + ids.length + ' 个商品？相关卡密/订单引用会被清理')) {
        util.post({ url: API + 'del', data: { list: ids.join(',') }, done: load, error: res => message.error(res.msg) });
      }
    });
    document.getElementById('commodity-search').addEventListener('input', () => { page = 1; load(); });
    document.querySelector('.crud-prev').addEventListener('click', () => { if (page > 1) { page--; load(); } });
    document.querySelector('.crud-next').addEventListener('click', () => { page++; load(); });
    document.querySelector('#commodityModal .modal-form').addEventListener('submit', e => {
      e.preventDefault();
      const data = Object.fromEntries(new FormData(e.target).entries());
      if (!data.name.trim()) return message.error('商品名称不能为空');
      if (!data.category_id) return message.error('请选择商品分类');
      ['status','api_status','only_user','recommend'].forEach(n => { if (typeof data[n] === 'undefined') data[n] = '0'; });
      util.post({ url: API + 'save', data, done: () => { message.success('保存成功'); load();
        if (window.bootstrap) bootstrap.Modal.getInstance(document.getElementById('commodityModal'))?.hide(); },
        error: res => message.error(res.msg) });
    });
    load();
  });
  `;
  return renderCrudPage({ cfg, manage, title: '商品管理', activePath: '/admin/commodity/index', body, readyJs: js });
}

// 卡密管理页
export function renderAdminCardPage(cfg, manage, commodityId = 0) {
  const body = `
<div class="card mb-5 mb-xl-8">
  <div class="card-header border-0">
    <div class="card-toolbar">
      <button class="btn btn-sm btn-light-primary crud-add me-3"><i class="fa-duotone fa-regular fa-circle-plus"></i> 添加卡密</button>
      <button class="btn btn-sm btn-light-warning crud-lock me-3"><i class="fa-duotone fa-regular fa-lock"></i> 锁定选中</button>
      <button class="btn btn-sm btn-light-info crud-unlock me-3"><i class="fa-duotone fa-regular fa-unlock"></i> 解锁选中</button>
      <button class="btn btn-sm btn-light-danger crud-del me-3"><i class="fa-duotone fa-regular fa-trash-can"></i> 移除选中</button>
    </div>
  </div>
  <div class="card-body py-3">
    <div class="row g-2 mb-3">
      <div class="col-md-4"><select class="form-select" id="card-commodity">
        <option value="">选择商品</option></select></div>
      <div class="col-md-2"><select class="form-select" id="card-status">
        <option value="">全部状态</option><option value="0">未售</option><option value="1">已售</option><option value="2">锁定</option></select></div>
      <div class="col-md-4"><input class="form-control" id="card-search" placeholder="搜索卡密…"></div>
    </div>
    <div class="table-responsive">
      <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3" id="card-table">
        <thead><tr class="fw-bold text-muted">
          <th style="width:40px"><input type="checkbox" class="crud-check-all"></th>
          <th>ID</th><th>商品</th><th>卡密</th><th>状态</th><th>售价成本</th><th>购买时间/订单</th><th>操作</th>
        </tr></thead>
        <tbody></tbody>
      </table>
      <div class="d-flex justify-content-end align-items-center mt-3">
        <button class="btn btn-sm btn-secondary crud-prev me-2">上一页</button>
        <span class="crud-pageinfo me-2"></span>
        <button class="btn btn-sm btn-secondary crud-next">下一页</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" tabindex="-1" id="cardModal"><div class="modal-dialog modal-lg"><div class="modal-content">
  <div class="modal-header py-3"><h5 class="modal-title">添加卡密</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <form class="modal-form"><div class="modal-body">
    <div class="mb-3"><label class="form-label">商品</label><select class="form-select" name="commodity_id"></select></div>
    <div class="mb-3"><label class="form-label">卡密内容 <span class="text-muted">(每行一条，支持批量)</span></label>
      <textarea class="form-control" name="secret" rows="6" required placeholder="CARD-AAAA-1111&#10;CARD-BBBB-2222"></textarea></div>
    <div class="mb-3"><label class="form-label">成本价</label><input class="form-control" name="cost" type="number" step="0.01" value="0"></div>
  </div><div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
    <button type="submit" class="btn btn-primary">导入</button>
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
        select.innerHTML = '<option value="">选择商品</option>' + opts;
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
            const stText = Number(c.status) === 0 ? '<span class="badge badge-light-success">未售</span>'
              : Number(c.status) === 1 ? '<span class="badge badge-light-secondary">已售</span>'
              : '<span class="badge badge-light-warning">锁定</span>';
            const tr = document.createElement('tr');
            tr.dataset.id = c.id; tr.dataset.secretKey = c.secret; tr.dataset.cost = c.cost || 0;
            tr.innerHTML = '<td><input type="checkbox" class="crud-check"></td>' +
              '<td>' + c.id + '</td>' +
              '<td>' + (c.commodity ? c.commodity.name : ('#' + c.commodity_id)) + '</td>' +
              '<td><code>' + c.secret + '</code></td>' +
              '<td>' + stText + '</td>' +
              '<td>￥' + (c.cost || 0) + '</td>' +
              '<td>' + (c.purchase_time ? new Date(c.purchase_time * 1000).toLocaleString() : '-') + (c.order ? ' <a href="#">#' + c.order.trade_no + '</a>' : '') + '</td>' +
              '<td><button class="btn btn-sm btn-light-danger row-del">删除</button></td>';
            tbody.appendChild(tr);
          });
          document.querySelector('.crud-pageinfo').textContent = '第 ' + page + ' 页 / 共 ' + res.data.count + ' 条';
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
      if (confirm('确认删除卡密 ID ' + tr.dataset.id + ' ?')) {
        util.post({ url: API + 'del', data: { list: tr.dataset.id }, done: load, error: res => message.error(res.msg) });
      }
    });
    document.querySelector('.crud-lock').addEventListener('click', () => {
      const ids = selected(); if (!ids.length) return message.error('请先勾选卡密');
      util.post({ url: API + 'lock', data: { list: ids.join(',') }, done: load, error: res => message.error(res.msg) });
    });
    document.querySelector('.crud-unlock').addEventListener('click', () => {
      const ids = selected(); if (!ids.length) return message.error('请先勾选卡密');
      util.post({ url: API + 'unlock', data: { list: ids.join(',') }, done: load, error: res => message.error(res.msg) });
    });
    document.querySelector('.crud-del').addEventListener('click', () => {
      const ids = selected(); if (!ids.length) return message.error('请先勾选卡密');
      if (confirm('确认删除选中 ' + ids.length + ' 条未售卡密？')) {
        util.post({ url: API + 'del', data: { list: ids.join(',') }, done: load, error: res => message.error(res.msg) });
      }
    });
    document.querySelector('#cardModal .modal-form').addEventListener('submit', e => {
      e.preventDefault();
      const data = Object.fromEntries(new FormData(e.target).entries());
      if (!data.commodity_id) return message.error('请选择商品');
      if (!data.secret.trim()) return message.error('卡密不能为空');
      util.post({ url: API + 'save', data, done: () => { message.success('导入成功'); load();
        e.target.querySelector('textarea[name="secret"]').value = '';
        if (window.bootstrap) bootstrap.Modal.getInstance(document.getElementById('cardModal'))?.hide(); },
        error: res => message.error(res.msg) });
    });
    load();
  });
  `;
  return renderCrudPage({ cfg, manage, title: '卡密管理', activePath: '/admin/card/index', body, readyJs: js });
}
function adminMenu(activePath) {
  // ============================================================
  // 后台菜单 — 严格对齐原版 View/Admin/Header.html:
  //   分组顺序 Main → User → Trade → Shared → Config、菜单名称、SVG 图标 path 全部逐条照抄
  //   高亮语义同原版 kernel/Helper.php::active() —— str_starts_with 前缀匹配
  //   未实现的入口保留在菜单里，点击落到 worker.js 的「建设中」兜底页（与原版路由占位一致）
  // ============================================================
  const items = [
    // ---------- Main ----------
    { icon: '<path d="M19 5v2h-4V5h4M9 5v6H5V5h4m10 8v6h-4v-6h4M9 17v2H5v-2h4M21 3h-8v6h8V3zM11 3H3v10h8V3zm10 8h-8v10h8V11zm-10 4H3v6h8v-6z"/>', name: '控制台', url: '/admin/dashboard/index', section: 'Main' },
    // ---------- User ----------
    { icon: '<path d="M9 13.75c-2.34 0-7 1.17-7 3.5V19h14v-1.75c0-2.33-4.66-3.5-7-3.5zM4.34 17c.84-.58 2.87-1.25 4.66-1.25s3.82.67 4.66 1.25H4.34zM9 12c1.93 0 3.5-1.57 3.5-3.5S10.93 5 9 5S5.5 6.57 5.5 8.5S7.07 12 9 12zm0-5c.83 0 1.5.67 1.5 1.5S9.83 10 9 10s-1.5-.67-1.5-1.5S8.17 7 9 7zm7.04 6.81c1.16.84 1.96 1.96 1.96 3.44V19h4v-1.75c0-2.02-3.5-3.17-5.96-3.44zM15 12c1.93 0 3.5-1.57 3.5-3.5S16.93 5 15 5c-.54 0-1.04.13-1.5.35c.63.89 1 1.98 1 3.15s-.37 2.26-1 3.15c.46.22.96.35 1.5.35z"/>', name: '会员管理', url: '/admin/user/index', section: 'User' },
    { icon: '<path d="M20 12a2 2 0 0 0-2-2V7c0-1.1-.9-2-2-2H4a2 2 0 0 0-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2v-3a2 2 0 0 0 2-2zM4 7h12v3.17A3 3 0 0 0 15 12c0 .77.29 1.47.76 2H16v3H4V7zm14 6a1 1 0 1 1 0-2a1 1 0 0 1 0 2z"/><path d="M6 9h6v2H6zm0 4h6v2H6z"/>', name: '工单管理', url: '/admin/ticket/index', section: 'User', badge: 'md-ticket-menu-badge ticket-admin-badge' },
    { icon: '<path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM7 9h10v2H7V9zm6 5H7v-2h6v2zm4-6H7V6h10v2z"/>', name: '消息管理', url: '/admin/message/index', section: 'User' },
    { icon: '<path d="M19.5 3.5L18 2l-1.5 1.5L15 2l-1.5 1.5L12 2l-1.5 1.5L9 2L7.5 3.5L6 2v14H3v3c0 1.66 1.34 3 3 3h12c1.66 0 3-1.34 3-3V2l-1.5 1.5zM15 20H6c-.55 0-1-.45-1-1v-1h10v2zm4-1c0 .55-.45 1-1 1s-1-.45-1-1v-3H8V5h11v14z"/><path d="M9 7h6v2H9zm7 0h2v2h-2zm-7 3h6v2H9zm7 0h2v2h-2z"/>', name: '充值订单', url: '/admin/recharge/order', section: 'User' },
    { icon: '<path d="M8 16h8v2H8zm0-4h8v2H8zm6-10H6c-1.1 0-2 .9-2 2v16c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm4 18H6V4h7v5h5v11z"/>', name: '账单管理', url: '/admin/user/bill', section: 'User' },
    { icon: '<path d="M9.68 13.69L12 11.93l2.31 1.76l-.88-2.85L15.75 9h-2.84L12 6.19L11.09 9H8.25l2.31 1.84l-.88 2.85zM20 10c0-4.42-3.58-8-8-8s-8 3.58-8 8c0 2.03.76 3.87 2 5.28V23l6-2l6 2v-7.72A7.96 7.96 0 0 0 20 10zm-8-6c3.31 0 6 2.69 6 6s-2.69 6-6 6s-6-2.69-6-6s2.69-6 6-6zm0 15l-4 1.02v-3.1c1.18.68 2.54 1.08 4 1.08s2.82-.4 4-1.08v3.1L12 19z"/>', name: '会员等级', url: '/admin/user/group', section: 'User' },
    { icon: '<path d="M18.36 9l.6 3H5.04l.6-3h12.72M20 4H4v2h16V4zm0 3H4l-1 5v2h1v6h10v-6h4v6h2v-6h1v-2l-1-5zM6 18v-4h6v4H6z"/>', name: '商户等级', url: '/admin/user/businessLevel', section: 'User' },
    { icon: '<path d="M21 7.28V5c0-1.1-.9-2-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2v-2.28A2 2 0 0 0 22 15V9a2 2 0 0 0-1-1.72zM20 9v6h-7V9h7zM5 19V5h14v2h-6c-1.1 0-2 .9-2 2v6c0 1.1.9 2 2 2h6v2H5z"/><circle cx="16" cy="12" r="1.5"/>', name: '提现管理', url: '/admin/cash/index', section: 'User' },
    // ---------- Trade ----------
    { icon: '<path d="M12 2l-5.5 9h11L12 2zm0 3.84L13.93 9h-3.87L12 5.84zM17.5 13c-2.49 0-4.5 2.01-4.5 4.5s2.01 4.5 4.5 4.5s4.5-2.01 4.5-4.5s-2.01-4.5-4.5-4.5zm0 7a2.5 2.5 0 0 1 0-5a2.5 2.5 0 0 1 0 5zM3 21.5h8v-8H3v8zm2-6h4v4H5v-4z"/>', name: '分类管理', url: '/admin/category/index', section: 'Trade' },
    { icon: '<path d="M20 2H4c-1 0-2 .9-2 2v3.01c0 .72.43 1.34 1 1.69V20c0 1.1 1.1 2 2 2h14c.9 0 2-.9 2-2V8.7c.57-.35 1-.97 1-1.69V4c0-1.1-1-2-2-2zm-1 18H5V9h14v11zm1-13H4V4h16v3z"/><path d="M9 12h6v2H9z"/>', name: '商品管理', url: '/admin/commodity/index', section: 'Trade' },
    { icon: '<path d="M22 19h-6v-4h-2.68c-1.14 2.42-3.6 4-6.32 4c-3.86 0-7-3.14-7-7s3.14-7 7-7c2.72 0 5.17 1.58 6.32 4H24v6h-2v4zm-4-2h2v-4h2v-2H11.94l-.23-.67C11.01 8.34 9.11 7 7 7c-2.76 0-5 2.24-5 5s2.24 5 5 5c2.11 0 4.01-1.34 4.71-3.33l.23-.67H18v4zM7 15c-1.65 0-3-1.35-3-3s1.35-3 3-3s3 1.35 3 3s-1.35 3-3 3zm0-4c-.55 0-1 .45-1 1s.45 1 1 1s1-.45 1-1s-.45-1-1-1z"/>', name: '卡密管理', url: '/admin/card/index', section: 'Trade' },
    { icon: '<path d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.42l9 9c.36.36.86.58 1.41.58s1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41s-.23-1.06-.59-1.42zM13 20.01L4 11V4h7v-.01l9 9l-7 7.02z"/><circle cx="6.5" cy="6.5" r="1.5"/>', name: '优惠券', url: '/admin/coupon/index', section: 'Trade' },
    { icon: '<path d="M15.55 13c.75 0 1.41-.41 1.75-1.03l3.58-6.49A.996.996 0 0 0 20.01 4H5.21l-.94-2H1v2h2l3.6 7.59l-1.35 2.44C4.52 15.37 5.48 17 7 17h12v-2H7l1.1-2h7.45zM6.16 6h12.15l-2.76 5H8.53L6.16 6zM7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2s-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2s2-.9 2-2s-.9-2-2-2z"/>', name: '商品订单', url: '/admin/order/index', section: 'Trade' },
    // ---------- Shared ----------
    { icon: '<path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81c1.66 0 3-1.34 3-3s-1.34-3-3-3s-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65c0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92c0-1.61-1.31-2.92-2.92-2.92zM18 4c.55 0 1 .45 1 1s-.45 1-1 1s-1-.45-1-1s.45-1 1-1zM6 13c-.55 0-1-.45-1-1s.45-1 1-1s1 .45 1 1s-.45 1-1 1zm12 7.02c-.55 0-1-.45-1-1s.45-1 1-1s1 .45 1 1s-.45 1-1 1z"/>', name: '店铺共享', url: '/admin/store/index', section: 'Shared' },
    { icon: '<path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"/><path d="M7.5 7h5v1.5h-5zM13.8 15.2l1.1-1.1 1.1 1.1 1.1-1.1-1.1-1.1 1.1-1.1-1.1-1.1-1.1 1.1-1.1-1.1-1.1 1.1 1.1 1.1-1.1 1.1zM7.25 11.5h5V13h-5zM7.25 14.5h5V16h-5z"/>', name: '加价模板', url: '/admin/store/priceTemplate', section: 'Shared' },
    // ---------- Config ----------
    { icon: '<path d="M19.43 12.98c.04-.32.07-.64.07-.98c0-.34-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46a.5.5 0 0 0-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65A.488.488 0 0 0 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1a.566.566 0 0 0-.18-.03c-.17 0-.34.09-.43.25l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98c0 .33.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.07.49-.12.64l-2.11 1.65c-.04.32-.07.65-.07.98c0 .33.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.07.49-.12.64l-2.11 1.65c.03 1 .07.65.07.98c0 .33.03.66.07.98l2.11 1.65c.19.15.24.42.12.64l-2 3.46a.5.5 0 0 0 .61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.58 1.69-.98l2.49 1a.5.5 0 0 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65c.04-.32.07-.65.07-.98c0-.33-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46a.5.5 0 0 0-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65A.488.488 0 0 0 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1a.566.566 0 0 0-.18-.03c-.17 0-.34.09-.43.25l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98c0 .33.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.07.49-.12.64l-2.11 1.65zm-1.98-1.71c.04.31.05.52.05.73c0 .21-.02.43-.05.73l-.14 1.13l.89.7l1.08.84l-.7 1.21l-1.27-.51l-1.04-.42l-.9.68c-.43.32-.84.56-1.25.73l-1.06.43l-.16 1.13l-.2 1.35h-1.4l-.19-1.35l-.16-1.13l-1.06-.43c-.43-.18-.83-.41-1.23-.71l-.91-.7l-1.06.43l-1.27.51l-.7-1.21l1.08-.84l.89-.7l-.14-1.13l-.03-.31l-.05-.54l-.05-.74s.02-.43.05-.73l.14-1.13l-.89-.7l-1.08-.84l.7-1.21l1.27.51l1.04.42l.89-.68c.43-.32.84-.56 1.25-.73l1.06-.43l.16-1.13l.2-1.35h1.39l.19 1.35l.16 1.13l1.06.43c.43.18.83.41 1.23.71l.91.7l1.06-.43l1.27-.51l.7 1.21l-1.07.85l-.89.7l.14 1.13zM12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4s4-1.79 4-4s-1.79-4-4-4zm0 6c-1.1 0-2-.9-2-2s.9-2 2-2s2 .9 2 2s-.9 2-2 2z"/>', name: '网站设置', url: '/admin/config/index', section: 'Config', match: '/admin/config' },
    { icon: '<circle cx="17" cy="15.5" r="1.12" fill-rule="evenodd"/><path d="M17 17.5c-.73 0-2.19.36-2.24 1.08c.5.71 1.32 1.17 2.24 1.17s1.74-.46 2.24-1.17c-.05-.72-1.51-1.08-2.24-1.08z" fill-rule="evenodd"/><path d="M18 11.09V6.27L10.5 3L3 6.27v4.91c0 4.54 3.2 8.79 7.5 9.82c.55-.13 1.08-.32 1.6-.55A5.973 5.973 0 0 0 17 23c3.31 0 6-2.69 6-6c0-2.97-2.16-5.43-5-5.91zM11 17c0 .56.08 1.11.23 1.62c-.24.11-.48.22-.73.3c-3.17-1-5.5-4.24-5.5-7.74v-3.6l5.5-2.4l5.5 2.4v3.51c-2.84.48-5 2.94-5 5.91zm6 4c-2.21 0-4-1.79-4-4s1.79-4 4-4s4 1.79 4 4s-1.79 4-4 4z" fill-rule="evenodd"/>', name: '管理员', url: '/admin/manage/index', section: 'Config' },
    { icon: '<path d="M10.5 4.5c.28 0 .5.22.5.5v2h6v6h2c.28 0 .5.22.5.5s-.22.5-.5.5h-2v6h-2.12c-.68-1.75-2.39-3-4.38-3s-3.7 1.25-4.38 3H4v-2.12c1.75-.68 3-2.39 3-4.38c0-1.99-1.24-3.7-2.99-4.38L4 7h6V5c0-.28.22-.5.5-.5m0-2A2.5 2.5 0 0 0 8 5H4c-1.1 0-1.99.9-1.99 2v3.8h.29c1.49 0 2.7 1.21 2.7 2.7s-1.21 2.7-2.7 2.7H2V20c0 1.1.9 2 2 2h3.8v-.3c0-1.49 1.21-2.7 2.7-2.7s2.7 1.21 2.7 2.7v.3H17c1.1 0 2-.9 2-2v-4a2.5 2.5 0 0 0 0-5V7c0-1.1-.9-2-2-2h-4a2.5 2.5 0 0 0-2.5-2.5z"/>', name: '通用插件', url: '/admin/plugin/index', section: 'Config' },
    { icon: '<path d="M19 14V6c0-1.1-.9-2-2-2H3c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zm-2 0H3V6h14v8zm-7-7c-1.66 0-3 1.34-3 3s1.34 3 3 3s3-1.34 3-3s-1.34-3-3-3zm13 0v11c0 1.1-.9 2-2 2H4v-2h17V7h2z"/>', name: '支付管理', section: 'Config', match: '/admin/pay', children: [
      { name: '支付插件', url: '/admin/pay/plugin' },
      { name: '支付接口', url: '/admin/pay/index' },
    ] },
    { icon: '<path d="M9.17 6l2 2H20v10H4V6h5.17M10 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/>', name: '文件管理', url: '/admin/file/index', section: 'Config' },
    { icon: '<path d="m12.87 15.07l-2.54-2.51l.03-.03A17.52 17.52 0 0 0 14.07 6H17V4h-7V2H8v2H1v2h11.17C11.5 7.92 10.44 9.75 9 11.35C8.07 10.32 7.3 9.19 6.69 8h-2c.73 1.63 1.73 3.17 2.98 4.56l-5.09 5.02L4 19l5-5l3.11 3.11l.76-2.04zM18.5 10h-2L12 22h2l1.12-3h4.75L21 22h2l-4.5-12zm-2.62 7l1.62-4.33L19.12 17h-3.24z"/>', name: '语言翻译', url: '/admin/lang/index', section: 'Config' },
    { icon: '<path d="M13 3a9 9 0 0 0-9 9H1l3.89 3.89l.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7s-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42A8.954 8.954 0 0 0 13 21a9 9 0 0 0 0-18zm-1 5v5l4.25 2.52l.77-1.28l-3.52-2.09V8z"/>', name: '操作日志', url: '/admin/log/index', section: 'Config' },
  ];
  let html = '';
  let lastSection = '';
  // 同原版 active(): 前缀匹配 (str_starts_with)
  const isActive = (prefix) => (activePath && prefix && activePath.indexOf(prefix) === 0 ? 'active' : '');
  for (const it of items) {
    if (it.section !== lastSection) {
      html += `<div class="menu-content pt-8 pb-2"><span class="menu-section text-muted text-uppercase fs-8 ls-1">${it.section}</span></div>`;
      lastSection = it.section;
    }
    // 折叠子菜单 (对齐原版 data-kt-menu-trigger="click" + menu-accordion)
    if (it.children) {
      const st = isActive(it.match || '');
      html += `<div data-kt-menu-trigger="click" class="menu-item menu-accordion ${st ? 'here show' : ''}">
          <span class="menu-link">
            <span class="menu-icon"><svg class="menu-svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">${it.icon}</svg></span>
            <span class="menu-title">${it.name}</span>
            <span class="menu-arrow"></span>
          </span>
          <div class="menu-sub menu-sub-accordion menu-active-bg">
            ${it.children.map(c => `<div class="menu-item">
                <a class="menu-link ${isActive(c.url)}" href="${c.url}">
                  <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                  <span class="menu-title">${c.name}</span>
                </a>
              </div>`).join('\n            ')}
          </div>
        </div>`;
      continue;
    }
    const active = isActive(it.match || it.url);
    const badge = it.badge ? `<span class="${it.badge}" hidden>0</span>` : '';
    html += `<div class="menu-item">
        <a class="menu-link ${active}" href="${it.url}">
          <span class="menu-icon"><svg class="menu-svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">${it.icon}</svg></span>
          <span class="menu-title">${it.name}${badge}</span>
        </a>
      </div>`;
  }
  return html;
}

const adminFooterScripts = () => jsScripts([
  '/assets/common/js/_.js',
  '/assets/admin/js/_admin.js',
  '/assets/admin/js/_material.js',
  '/assets/static/codemirror/lib/codemirror.js',
  '/assets/static/codemirror/mode/markdown/markdown.js',
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
  '/assets/admin/js/dict.js',
  '/assets/admin/js/menu.js',
  '/assets/admin/controller/global.js',
  '/assets/admin/js/material.js',
]);

export function renderAdminShell(opts = {}) {
  const { cfg = {}, manage = {}, title = '控制台', activePath = '/admin/dashboard/index', toolbar = null } = opts;
  const shopName = cfg.shop_name || 'acg-faka';
  const avatar = manage.avatar || '/favicon.ico';
  const nickname = manage.nickname || manage.email || '管理员';
  const email = manage.email || '';

  const tb = toolbar && toolbar.length
    ? `<nav class="md-tabs">${toolbar.map(t => `<a href="${t.url}" class="md-tab">${t.name}</a>`).join('')}</nav>`
    : '';

  return `<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <script>(function(){var e=document.documentElement;try{var p=localStorage.getItem('admin-theme')||'auto';var d=p==='auto'?(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'):p;e.setAttribute('data-theme',d);e.setAttribute('data-theme-pref',p);var m=localStorage.getItem('admin-layout-mode')==='desktop'?'desktop':((window.innerWidth||screen.width)<992?'mobile':'desktop');e.setAttribute('data-admin-layout',m);}catch(_){e.setAttribute('data-theme','light');e.setAttribute('data-admin-layout',(window.innerWidth||screen.width)<992?'mobile':'desktop');}})();</script>
    <title>${htmlEscape(title)}-${htmlEscape(shopName)}</title>
    <link rel="shortcut icon" href="/favicon.ico"/>
    ${cssLinks([
      '/assets/admin/css/_admin.css',
      '/assets/common/css/_.css',
      '/assets/static/codemirror/lib/codemirror.css',
      '/assets/common/css/_material.css',
      '/assets/common/fonts/material-icons.css',
      '/assets/admin/css/_material.css',
      '/assets/admin/css/_mobile.css',
      '/assets/admin/css/style.bundle.css',
      '/assets/common/css/font.min.css',
      '/assets/common/js/layui/css/layui.css',
      '/assets/common/css/select2.min.css',
      '/assets/common/css/component.css',
      '/assets/common/css/toastr.min.css',
      '/assets/common/js/table/bootstrap-table.css',
      '/assets/common/js/layer/theme/default/layer.css',
      '/assets/common/css/md-tokens.css',
      '/assets/common/css/md-components.css',
      '/assets/admin/css/material.css',
      '/assets/common/fonts/material-icons.css',
      '/assets/common/css/mdicon.css',
      '/assets/admin/css/mobile.css'
    ])}
    <script src="/assets/common/js/ready.js"></script>
    ${adminVar(cfg)}
</head>
<body id="kt_body"
      class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed aside-enabled aside-fixed"
      style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px;background: url('${htmlEscape(cfg.background_url || '')}') fixed no-repeat;background-size: cover;">
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
                                <span class="menu-title">退出登录</span>
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
                                        <button type="button" id="md-theme-toggle" class="btn btn-icon w-30px h-30px w-md-40px h-md-40px" title="主题" aria-label="切换主题">
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
                            ${opts.body || ''}
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

// ---------- Dashboard 页 (对齐原版 Dashboard/Index.html) ----------
export function renderAdminDashboardPage(cfg, manage) {
  const body = `
<script src="/assets/static/echarts.min.js"></script>
<div class="dash">
  <div class="dash__grid">
    <aside class="dash__side">
      <section class="dash-card dash-news" aria-labelledby="dash-news-title">
        <header class="dash-card__head">
          <h2 class="dash-card__title dash-news__title" id="dash-news-title"><span class="material-icons-outlined" aria-hidden="true">campaign</span>官方公告</h2>
          <button type="button" class="dash-news__toggle" aria-expanded="true" aria-controls="dash-news-list" aria-label="收起官方公告">
            <span class="material-icons-outlined" aria-hidden="true">expand_less</span>
          </button>
        </header>
        <div class="dash-news__list" id="dash-news-list" data-dash-news>
          <span class="dash-news__item"><span class="dash-skel dash-skel--line"></span></span>
        </div>
      </section>

      <section class="dash-card dash-account" aria-label="登录信息">
        <div class="dash-account__who">
          <img src="${htmlEscape(manage.avatar || '/favicon.ico')}" alt="" class="dash-account__avatar">
          <div class="dash-account__id">
            <strong class="dash-account__name">${htmlEscape(manage.nickname || manage.email || '管理员')}</strong>
            <span class="dash-account__email">${htmlEscape(manage.email || '')}</span>
          </div>
        </div>
        <dl class="dash-account__list">
          <div class="dash-account__row">
            <dt>本次登录 IP</dt>
            <dd class="dash-num">${htmlEscape(manage.login_ip || '-')}</dd>
          </div>
          <div class="dash-account__row">
            <dt>上次登录</dt>
            <dd class="dash-num">${htmlEscape(manage.last_login_ip || '暂无记录')}${manage.last_login_time ? '<small>' + htmlEscape(manage.last_login_time) + '</small>' : ''}</dd>
          </div>
        </dl>
      </section>
    </aside>
    <div class="dash__main">
      <section class="dash-card dash-earn" aria-label="利润">
        <div class="dash-feedback" data-dash-feedback="overview" role="status" aria-live="polite" hidden></div>
        <div class="dash-earn__grid" data-dash-earn-grid aria-busy="true">
          <div class="dash-earn__item dash-earn__item--today" data-dash-earn="today">
            <div class="dash-earn__head"><span class="dash-earn__label">今日利润</span></div>
            <strong class="dash-earn__value dash-num" data-dash-value><span class="dash-skel dash-skel--value"></span></strong>
            <span class="dash-earn__delta" data-dash-delta><span class="dash-skel"></span></span>
            <span class="dash-earn__meta" data-dash-meta></span>
          </div>
          <div class="dash-earn__item" data-dash-earn="yesterday">
            <div class="dash-earn__head"><span class="dash-earn__label">昨日利润</span></div>
            <strong class="dash-earn__value dash-num" data-dash-value><span class="dash-skel dash-skel--value"></span></strong>
            <span class="dash-earn__delta" data-dash-delta><span class="dash-skel"></span></span>
            <span class="dash-earn__meta" data-dash-meta></span>
          </div>
          <div class="dash-earn__item" data-dash-earn="month">
            <div class="dash-earn__head">
              <span class="dash-earn__label">本月利润</span>
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
          <h2 class="dash-card__title" id="dash-todo-title">待处理</h2>
        </header>
        <ul class="dash-todo__list" data-dash-todo aria-busy="true">
          <li class="dash-todo__item"><span class="dash-todo__row"><span class="dash-skel dash-skel--line"></span></span></li>
        </ul>
      </section>

      <section class="dash-card dash-trend" aria-labelledby="dash-trend-title">
        <header class="dash-card__head">
          <div class="dash-card__heading">
            <h2 class="dash-card__title" id="dash-trend-title">趋势</h2>
            <span class="dash-card__caption dash-num" data-trend-caption></span>
          </div>
          <div class="dash-seg" role="group" aria-label="时间范围">
            <button type="button" class="dash-seg__btn is-active" data-trend-days="7" aria-pressed="true">7 天</button>
            <button type="button" class="dash-seg__btn" data-trend-days="30" aria-pressed="false">30 天</button>
          </div>
        </header>
        <div class="dash-feedback" data-dash-feedback="trend" role="status" aria-live="polite" hidden></div>
        <div class="dash-trend__tabs" role="tablist" aria-label="指标">
          <button type="button" role="tab" class="dash-trend__tab is-active" data-trend-metric="profit" aria-selected="true">
            <span class="dash-trend__tab-label">利润</span>
            <span class="dash-trend__tab-value dash-num" data-trend-value="profit"><span class="dash-skel"></span></span>
          </button>
          <button type="button" role="tab" class="dash-trend__tab" data-trend-metric="turnover" aria-selected="false" tabindex="-1">
            <span class="dash-trend__tab-label">成交额</span>
            <span class="dash-trend__tab-value dash-num" data-trend-value="turnover"><span class="dash-skel"></span></span>
          </button>
          <button type="button" role="tab" class="dash-trend__tab" data-trend-metric="orders" aria-selected="false" tabindex="-1">
            <span class="dash-trend__tab-label">订单</span>
            <span class="dash-trend__tab-value dash-num" data-trend-value="orders"><span class="dash-skel"></span></span>
          </button>
          <button type="button" role="tab" class="dash-trend__tab" data-trend-metric="recharge" aria-selected="false" tabindex="-1">
            <span class="dash-trend__tab-label">充值</span>
            <span class="dash-trend__tab-value dash-num" data-trend-value="recharge"><span class="dash-skel"></span></span>
          </button>
        </div>
        <div class="dash-trend__stage">
          <div class="dash-trend__chart" data-trend-chart data-chart aria-hidden="true"></div>
          <p class="dash-trend__empty" data-trend-empty hidden>暂无数据</p>
        </div>
      </section>

      <section class="dash-card dash-data" aria-labelledby="dash-data-title">
        <header class="dash-card__head">
          <div class="dash-card__heading">
            <h2 class="dash-card__title" id="dash-data-title">经营数据</h2>
            <span class="dash-card__caption dash-num" data-dash-range></span>
          </div>
          <div class="dash-seg" role="tablist" aria-label="统计周期" data-dash-periods>
            <button type="button" role="tab" class="dash-seg__btn is-active" data-period="0" aria-selected="true">今日</button>
            <button type="button" role="tab" class="dash-seg__btn" data-period="1" aria-selected="false" tabindex="-1">昨日</button>
            <button type="button" role="tab" class="dash-seg__btn" data-period="2" aria-selected="false" tabindex="-1">本周</button>
            <button type="button" role="tab" class="dash-seg__btn" data-period="3" aria-selected="false" tabindex="-1">本月</button>
            <button type="button" role="tab" class="dash-seg__btn" data-period="4" aria-selected="false" tabindex="-1">全部</button>
          </div>
        </header>
        <div class="dash-feedback" data-dash-feedback="data" role="status" aria-live="polite" hidden></div>
        <div class="dash-data__body" data-dash-detail aria-busy="true">
          <div class="dash-kpis">
            <div class="dash-kpi">
              <span class="dash-kpi__label">成交额</span>
              <strong class="dash-kpi__value dash-num" data-kpi="turnover"><span class="dash-skel dash-skel--kpi"></span></strong>
              <span class="dash-kpi__sub" data-kpi-sub="turnover"></span>
            </div>
            <div class="dash-kpi">
              <span class="dash-kpi__label">利润</span>
              <strong class="dash-kpi__value dash-num" data-kpi="profit"><span class="dash-skel dash-skel--kpi"></span></strong>
              <span class="dash-kpi__sub" data-kpi-sub="profit"></span>
            </div>
            <div class="dash-kpi">
              <span class="dash-kpi__label">成交订单</span>
              <strong class="dash-kpi__value dash-num" data-kpi="orders"><span class="dash-skel dash-skel--kpi"></span></strong>
              <span class="dash-kpi__sub" data-kpi-sub="orders"></span>
            </div>
            <div class="dash-kpi">
              <span class="dash-kpi__label">客单价</span>
              <strong class="dash-kpi__value dash-num" data-kpi="avg"><span class="dash-skel dash-skel--kpi"></span></strong>
              <span class="dash-kpi__sub" data-kpi-sub="avg"></span>
            </div>
          </div>

          <div class="dash-panels">
            <section class="dash-panel" aria-labelledby="dash-flow-title">
              <h3 class="dash-panel__title" id="dash-flow-title">利润构成</h3>
              <table class="dash-bars dash-bars--flow">
                <tbody>
                  <tr class="dash-bars__row" data-flow="whole" data-flow-field="turnover">
                    <th scope="row" class="dash-bars__name">成交额</th>
                    <td class="dash-bars__amount dash-num" data-flow-amount>–</td>
                    <td class="dash-bars__bar" aria-hidden="true"><span class="dash-bars__track"><span class="dash-bars__fill" data-flow-fill></span></span></td>
                    <td class="dash-bars__pct dash-num" data-flow-pct></td>
                  </tr>
                  <tr class="dash-bars__row" data-flow="deduct" data-flow-field="pay_cost">
                    <th scope="row" class="dash-bars__name">支付手续费</th>
                    <td class="dash-bars__amount dash-num" data-flow-amount>–</td>
                    <td class="dash-bars__bar" aria-hidden="true"><span class="dash-bars__track"><span class="dash-bars__fill" data-flow-fill></span></span></td>
                    <td class="dash-bars__pct dash-num" data-flow-pct></td>
                  </tr>
                  <tr class="dash-bars__row" data-flow="deduct" data-flow-field="rent">
                    <th scope="row" class="dash-bars__name">成本</th>
                    <td class="dash-bars__amount dash-num" data-flow-amount>–</td>
                    <td class="dash-bars__bar" aria-hidden="true"><span class="dash-bars__track"><span class="dash-bars__fill" data-flow-fill></span></span></td>
                    <td class="dash-bars__pct dash-num" data-flow-pct></td>
                  </tr>
                  <tr class="dash-bars__row" data-flow="deduct" data-flow-field="rebate_merchant">
                    <th scope="row" class="dash-bars__name">商户分成</th>
                    <td class="dash-bars__amount dash-num" data-flow-amount>–</td>
                    <td class="dash-bars__bar" aria-hidden="true"><span class="dash-bars__track"><span class="dash-bars__fill" data-flow-fill></span></span></td>
                    <td class="dash-bars__pct dash-num" data-flow-pct></td>
                  </tr>
                  <tr class="dash-bars__row" data-flow="deduct" data-flow-field="rebate_substation">
                    <th scope="row" class="dash-bars__name">分站分成</th>
                    <td class="dash-bars__amount dash-num" data-flow-amount>–</td>
                    <td class="dash-bars__bar" aria-hidden="true"><span class="dash-bars__track"><span class="dash-bars__fill" data-flow-fill></span></span></td>
                    <td class="dash-bars__pct dash-num" data-flow-pct></td>
                  </tr>
                  <tr class="dash-bars__row" data-flow="deduct" data-flow-field="divide_amount">
                    <th scope="row" class="dash-bars__name">推广佣金</th>
                    <td class="dash-bars__amount dash-num" data-flow-amount>–</td>
                    <td class="dash-bars__bar" aria-hidden="true"><span class="dash-bars__track"><span class="dash-bars__fill" data-flow-fill></span></span></td>
                    <td class="dash-bars__pct dash-num" data-flow-pct></td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr class="dash-bars__row" data-flow="profit" data-flow-field="profit">
                    <th scope="row" class="dash-bars__name"><span data-dash-result-label>利润</span></th>
                    <td class="dash-bars__amount dash-num" data-flow-amount>–</td>
                    <td class="dash-bars__bar" aria-hidden="true"><span class="dash-bars__track"><span class="dash-bars__fill" data-flow-fill></span></span></td>
                    <td class="dash-bars__pct dash-num" data-flow-pct></td>
                  </tr>
                </tfoot>
              </table>
              <p class="dash-panel__note" data-dash-commission hidden></p>
            </section>

            <div class="dash-panel-stack">
              <section class="dash-panel" aria-labelledby="dash-pay-title" data-dash-channels>
                <h3 class="dash-panel__title" id="dash-pay-title">支付通道</h3>
                <table class="dash-bars dash-bars--channels" data-dash-channel-table></table>
                <p class="dash-panel__empty" data-dash-channel-empty hidden>暂无收款</p>
              </section>

              <section class="dash-panel" aria-labelledby="dash-minis-title">
                <h3 class="dash-panel__title" id="dash-minis-title">会员与提现</h3>
                <dl class="dash-minis">
                  <div class="dash-mini"><dt>新增会员</dt><dd class="dash-num" data-dash-field="user_register_num" data-dash-kind="count">–</dd></div>
                  <div class="dash-mini"><dt>新开分站</dt><dd class="dash-num" data-dash-field="business" data-dash-kind="count">–</dd></div>
                  <div class="dash-mini"><dt>会员充值</dt><dd class="dash-num" data-dash-field="recharge_amount">–</dd></div>
                  <div class="dash-mini"><dt>提现打款</dt><dd class="dash-num" data-dash-field="cash_paid_out">–</dd></div>
                  <div class="dash-mini"><dt>兑现到余额</dt><dd class="dash-num" data-dash-field="cash_to_balance">–</dd></div>
                  <div class="dash-mini"><dt>提现手续费</dt><dd class="dash-num" data-dash-field="cash_fee_income">–</dd></div>
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
  return renderAdminShell({ cfg, manage, title: '控制台', activePath: '/admin/dashboard/index', body });
}

// ============================================================
// 订单管理页 (对齐 Admin/Api/Order: data/save/clear/exportImpact/export)
// ============================================================
export function renderAdminOrderPage(cfg, manage) {
  const body = `
<div class="card mb-5 mb-xl-8">
  <div class="card-header border-0">
    <div class="card-toolbar d-flex flex-wrap">
      <button class="btn btn-sm btn-light-primary order-export me-3"><i class="fa-duotone fa-regular fa-file-export"></i> 导出订单</button>
      <button class="btn btn-sm btn-light-danger order-clear me-3"><i class="fa-duotone fa-regular fa-trash-can"></i> 清理未支付</button>
      <span class="align-self-center text-muted me-3 order-stat">共 <b class="order_count">0</b> 条，销售额 <b class="order_amount">￥0.00</b>，成本 <b class="order_cost">￥0.00</b></span>
    </div>
  </div>
  <div class="card-body py-3">
    <div class="row g-2 mb-3">
      <div class="col-md-3"><input class="form-control order-f-trade" placeholder="订单号"></div>
      <div class="col-md-2"><input class="form-control order-f-owner" placeholder="会员ID，0=访客" inputmode="numeric"></div>
      <div class="col-md-2"><select class="form-select order-f-status"><option value="">全部支付状态</option><option value="0">未支付</option><option value="1">已支付</option></select></div>
      <div class="col-md-2"><select class="form-select order-f-delivery"><option value="">全部发货状态</option><option value="0">未发货</option><option value="1">已发货</option></select></div>
      <div class="col-md-3"><input class="form-control order-f-secret" placeholder="卡密信息(模糊)"></div>
    </div>
    <div class="table-responsive">
      <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3" id="order-table">
        <thead><tr class="fw-bold text-muted">
          <th style="width:40px"><input type="checkbox" class="crud-check-all"></th>
          <th>订单号/下单时间</th><th>客户</th><th>商品</th><th>数量/金额</th><th>发货方式</th><th>支付</th><th>状态</th><th>操作</th>
        </tr></thead>
        <tbody></tbody>
      </table>
      <div class="d-flex justify-content-end align-items-center mt-3">
        <button class="btn btn-sm btn-secondary crud-prev me-2">上一页</button>
        <span class="crud-pageinfo me-2"></span>
        <button class="btn btn-sm btn-secondary crud-next">下一页</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" tabindex="-1" id="orderDeliverModal"><div class="modal-dialog modal-lg"><div class="modal-content">
  <div class="modal-header py-3"><h5 class="modal-title">手动发货</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <form class="modal-form"><div class="modal-body">
    <div class="alert alert-warning mb-3 order-deliver-warn" hidden>此订单已有发货记录，本次提交会覆盖现有发货内容。</div>
    <div class="mb-3"><label class="form-label">发货内容</label>
      <textarea class="form-control" name="secret" rows="8" required placeholder="填写要发货的信息"></textarea></div>
  </div><div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
    <button type="submit" class="btn btn-primary">核对并发货</button>
  </div></form>
</div></div></div>
<div class="modal fade" tabindex="-1" id="orderExportModal"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header py-3"><h5 class="modal-title">导出订单</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <form class="export-form"><div class="modal-body">
    <div class="alert alert-warning mb-3">系统会先通过 POST 精确预览当前筛选范围，再生成文件。单次最多 5000 笔；选择“永久删除”后还必须完成高危确认。</div>
    <div class="mb-3"><label class="form-label">导出数量 <span class="text-muted">(0 或留空表示全部，最多 5000 笔)</span></label>
      <input class="form-control" name="export_num" type="number" min="0" max="5000" value="0"></div>
    <div class="mb-3"><label class="form-label">导出后执行</label>
      <select class="form-select" name="export_status"><option value="0">不执行任何操作</option><option value="1">删除导出的订单（高危/物理删除）</option></select></div>
    <div class="order-export-preview mt-2"></div>
  </div><div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
    <button type="submit" class="btn btn-primary">预览导出范围</button>
  </div></form>
</div></div></div>`;

  const js = `
  ready(() => {
    const tbody = document.getElementById('order-table').querySelector('tbody');
    const API = '/admin/api/order/';
    let page = 1, pageSize = 10, deliverRow = null;
    const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    const sym = '￥';
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
            const statusTxt = Number(o.status) === 1 ? '<span class="badge badge-light-success">已支付</span>' : '<span class="badge badge-light-secondary">未支付</span>';
            const dlvTxt = Number(o.delivery_status) === 1 ? '<span class="badge badge-light-success">已发货</span>' : '<span class="badge badge-light-warning">未发货</span>';
            const dlvWay = Number(c.delivery_way) === 1 ? '手动' : '自动';
            let ops = '';
            if (Number(c.delivery_way) === 0 && Number(o.delivery_status) === 1) {
              ops += '<button class="btn btn-sm btn-light-primary row-secret me-1">查看卡密</button>';
            }
            if (Number(c.delivery_way) === 1 && Number(o.status) === 1) {
              ops += '<button class="btn btn-sm btn-light-success row-deliver me-1">手动发货</button>';
            }
            const tr = document.createElement('tr');
            tr.dataset.id = o.id; tr.dataset.tradeNo = o.trade_no; tr.dataset.ownerName = (u.username || '-'); tr.dataset.contact = o.contact || '';
            tr.innerHTML = '<td><input type="checkbox" class="crud-check"></td>' +
              '<td><div>' + esc(o.trade_no) + '</div><small class="text-muted">' + (o.create_time ? new Date(o.create_time * 1000).toLocaleString() : '-') + '</small></td>' +
              '<td>' + (o.owner_id ? esc(u.username || ('#'+o.owner_id)) : esc(o.contact || '游客')) + '</td>' +
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
          document.querySelector('.crud-pageinfo').textContent = '第 ' + page + ' 页 / 共 ' + (res.data.count || 0) + ' 条';
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

    // 手动发货
    tbody.addEventListener('click', e => {
      const tr = e.target.closest('tr'); if (!tr) return;
      if (e.target.closest('.row-secret')) {
        const sec = tr.querySelector('small[data-secret]');
        const txt = sec ? sec.dataset.secret : '';
        if (!txt) { return util.post({ url: API + 'data', data: { page:1, limit:1, 'equal-trade_no': tr.dataset.tradeNo }, loader: false, done: r => { const row = (r.data.list||[])[0]; if (row) window.prompt('卡密内容', row.secret); }, error: () => {} }); }
        window.prompt('卡密内容', txt);
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
        done: res => { (window.bootstrap && bootstrap.Modal.getInstance(document.getElementById('orderDeliverModal'))).hide(); message.alert(res.msg || '订单发货信息已保存。', 'success'); load(); },
        error: res => message.error(res.msg) });
    });

    // 清理未支付
    document.querySelector('.order-clear').addEventListener('click', () => {
      if (!confirm('只会物理删除 30 分钟前仍未支付的订单；已支付订单不会受影响。确认清理吗？')) return;
      util.post({ url: API + 'clear', done: res => { message.success(res.msg); load(); }, error: res => message.error(res.msg) });
    });

    // 导出订单
    let previewToken = '', previewCount = 0, previewData = null, exportFilter = {};
    function downloadExport(deleteConfirmation) {
      const payload = Object.assign({}, exportFilter, { expected_count: previewCount, preview_token: previewToken });
      if (deleteConfirmation) payload.delete_confirmation = deleteConfirmation;
      fetch(API + 'export', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) })
        .then(r => {
          const ct = r.headers.get('content-type') || '';
          if (ct.includes('application/json')) return r.json().then(j => { throw new Error(j.msg || '导出失败'); });
          return r.blob().then(blob => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; a.download = '订单导出-' + previewCount + '-' + new Date().toISOString().slice(0,10) + '.csv';
            document.body.appendChild(a); a.click(); a.remove();
            setTimeout(() => URL.revokeObjectURL(url), 1000);
            message.success('已导出 ' + previewCount + ' 笔订单');
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
        document.querySelector('.order-export-preview').innerHTML = '<div class="alert alert-info">共命中 <b>' + previewCount + '</b> 笔（已支付 ' + (impact.paid_count||0) + '、未支付 ' + (impact.unpaid_count||0) + '）。</div>';
        if (!previewToken || previewCount < 1) return;
        (window.bootstrap && bootstrap.Modal.getOrCreateInstance(document.getElementById('orderExportModal'))).hide();
        if (Number(st) === 1) {
          const phrase = '确认永久删除' + previewCount + '笔订单';
          if (!confirm('下载请求成功后，系统会物理删除上述 ' + previewCount + ' 笔订单及其历史记录，无法恢复。\\n\\n请输入确认短语：' + phrase)) return;
          const typed = window.prompt('请输入：' + phrase);
          if (typed !== phrase) { message.error('确认短语不匹配，已取消删除'); return; }
          downloadExport(phrase);
        } else {
          if (confirm('本次只下载 CSV，不修改或删除订单。确认导出 ' + previewCount + ' 笔？')) downloadExport('');
        }
      }, error: res => message.error(res.msg) });
    });
    document.querySelector('.order-export').addEventListener('click', () => {
      document.querySelector('.order-export-preview').innerHTML = '';
      (window.bootstrap && bootstrap.Modal.getOrCreateInstance(document.getElementById('orderExportModal'))).show();
    });

    load();
  });`;

  return renderCrudPage({ cfg, manage, title: '订单管理', activePath: '/admin/order/index', body, readyJs: js });
}

// ============================================================
// 会员管理页 (对齐 Admin/Api/User: data/save/recharge/coin/statistics/del)
// ============================================================
export function renderAdminUserPage(cfg, manage) {
  const body = `
<div class="card mb-5 mb-xl-8">
  <div class="card-header border-0">
    <div class="card-toolbar d-flex flex-wrap">
      <button class="btn btn-sm btn-light-danger user-del me-3"><i class="fa-duotone fa-regular fa-trash-can"></i> 移除选中</button>
      <span class="align-self-center text-muted me-3 user-stat">共 <b class="user_count">0</b> 人，余额合计 <b class="user_balance">￥0.00</b>，充值合计 <b class="user_recharge">￥0.00</b></span>
    </div>
  </div>
  <div class="card-body py-3">
    <div class="row g-2 mb-3">
      <div class="col-md-3"><input class="form-control user-f-username" placeholder="用户名(模糊)"></div>
      <div class="col-md-2"><input class="form-control user-f-id" placeholder="UID" inputmode="numeric"></div>
      <div class="col-md-2"><input class="form-control user-f-email" placeholder="邮箱"></div>
      <div class="col-md-2"><input class="form-control user-f-qq" placeholder="QQ号" inputmode="numeric"></div>
      <div class="col-md-2"><select class="form-select user-f-status"><option value="">全部状态</option><option value="1">正常</option><option value="0">已封禁</option></select></div>
    </div>
    <div class="table-responsive">
      <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3" id="user-table">
        <thead><tr class="fw-bold text-muted">
          <th style="width:40px"><input type="checkbox" class="crud-check-all"></th>
          <th>UID/用户名</th><th>余额</th><th>硬币</th><th>充值累计</th><th>状态</th><th>注册时间/IP</th><th>操作</th>
        </tr></thead>
        <tbody></tbody>
      </table>
      <div class="d-flex justify-content-end align-items-center mt-3">
        <button class="btn btn-sm btn-secondary crud-prev me-2">上一页</button>
        <span class="crud-pageinfo me-2"></span>
        <button class="btn btn-sm btn-secondary crud-next">下一页</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" tabindex="-1" id="userAdjustModal"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header py-3"><h5 class="modal-title">余额/硬币调整</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <form class="adjust-form"><div class="modal-body">
    <div class="mb-3"><label class="form-label">操作</label>
      <select class="form-select" name="action"><option value="1">增加</option><option value="2">扣减</option></select></div>
    <div class="mb-3"><label class="form-label">类型</label>
      <select class="form-select" name="currency"><option value="0">余额</option><option value="1">硬币</option></select></div>
    <div class="mb-3"><label class="form-label">数量</label>
      <input class="form-control" name="amount" type="number" step="0.01" min="0.01" required></div>
    <div class="mb-3"><label class="form-label">操作原因</label>
      <input class="form-control" name="log" required maxlength="64" placeholder="2-64 个字"></div>
    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="total" value="1" id="adj-total">
      <label class="form-check-label" for="adj-total">计入累计充值/硬币 (仅增加时)</label></div>
  </div><div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
    <button type="submit" class="btn btn-primary">确认操作</button>
  </div></form>
</div></div></div>
<div class="modal fade" tabindex="-1" id="userStatModal"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header py-3"><h5 class="modal-title">会员交易统计</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <div class="modal-body user-stat-body py-3"></div>
  <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button></div>
</div></div></div>`;

  const js = `
  ready(() => {
    const tbody = document.getElementById('user-table').querySelector('tbody');
    const API = '/admin/api/user/';
    let page = 1, pageSize = 10, adjustId = 0;
    const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    const sym = '￥';
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
            const stTxt = Number(u.status) === 1 ? '<span class="badge badge-light-success">正常</span>' : '<span class="badge badge-light-danger">已封禁</span>';
            const tr = document.createElement('tr');
            tr.dataset.id = u.id; tr.dataset.name = u.username;
            tr.innerHTML = '<td><input type="checkbox" class="crud-check"></td>' +
              '<td><div>' + u.id + ' / ' + esc(u.username) + '</div><small class="text-muted">' + esc(u.email || '') + '</small></td>' +
              '<td>' + sym + Number(u.balance || 0).toFixed(2) + '</td>' +
              '<td>' + Number(u.coin || 0) + '</td>' +
              '<td>' + sym + Number(u.recharge || 0).toFixed(2) + (Number(u.total_coin) > 0 ? ' / 币' + u.total_coin : '') + '</td>' +
              '<td>' + stTxt + '</td>' +
              '<td><small>' + (u.create_time ? new Date(u.create_time * 1000).toLocaleString() : '-') + '</small><br><small class="text-muted">' + esc(u.login_ip || '') + '</small></td>' +
              '<td><button class="btn btn-sm btn-light-primary row-adjust me-1">调整</button>' +
              '<button class="btn btn-sm btn-light-info row-stat me-1">统计</button>' +
              (Number(u.status) === 1 ? '<button class="btn btn-sm btn-light-warning row-ban me-1">封禁</button>' : '<button class="btn btn-sm btn-light-success row-unban me-1">解封</button>') +
              '<button class="btn btn-sm btn-light-danger row-del">删除</button></td>';
            tbody.appendChild(tr);
          });
          document.querySelector('.user_count').textContent = res.data.count || 0;
          document.querySelector('.user_balance').textContent = sym + Number(res.data.balance || 0).toFixed(2);
          document.querySelector('.user_recharge').textContent = sym + Number(res.data.recharge || 0).toFixed(2);
          document.querySelector('.crud-pageinfo').textContent = '第 ' + page + ' 页 / 共 ' + (res.data.count || 0) + ' 条';
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
            '<tr><td>今日交易额</td><td>' + sym + d.today_order_amount + '</td></tr>' +
            '<tr><td>昨日交易额</td><td>' + sym + d.yesterday_order_amount + '</td></tr>' +
            '<tr><td>本周交易额</td><td>' + sym + d.week_order_amount + '</td></tr>' +
            '<tr><td>本月交易额</td><td>' + sym + d.month_order_amount + '</td></tr>' +
            '<tr><td>累计交易额</td><td>' + sym + d.total_order_amount + '</td></tr>' +
            '</tbody></table>';
          (window.bootstrap && bootstrap.Modal.getOrCreateInstance(document.getElementById('userStatModal'))).show();
        });
      } else if (e.target.closest('.row-ban')) {
        if (!confirm('确认封禁用户 ' + tr.dataset.name + ' ?')) return;
        util.post({ url: API + 'save', data: { id: tr.dataset.id, status: 0 }, done: load, error: res => message.error(res.msg) });
      } else if (e.target.closest('.row-unban')) {
        if (!confirm('确认解封用户 ' + tr.dataset.name + ' ?')) return;
        util.post({ url: API + 'save', data: { id: tr.dataset.id, status: 1 }, done: load, error: res => message.error(res.msg) });
      } else if (e.target.closest('.row-del')) {
        if (!confirm('确认永久删除用户 ' + tr.dataset.name + ' ?（将同步清理其下级分站等关联数据）')) return;
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
      if (!ids.length) { message.error('请至少勾选 1 个会员！'); return; }
      if (!confirm('确认永久删除选中的 ' + ids.length + ' 个会员？')) return;
      util.post({ url: API + 'del', data: { list: ids.join(',') }, done: res => { message.success(res.msg); load(); }, error: res => message.error(res.msg) });
    });

    load();
  });`;

  return renderCrudPage({ cfg, manage, title: '会员管理', activePath: '/admin/user/index', body, readyJs: js });
}

// ============================================================
// 充值订单管理页 (对齐 Admin/Api/RechargeOrder: data/success/clear)
// ============================================================
export function renderAdminRechargePage(cfg, manage) {
  const body = `
<div class="card mb-5 mb-xl-8">
  <div class="card-header border-0">
    <div class="card-toolbar d-flex flex-wrap">
      <button class="btn btn-sm btn-light-danger recharge-clear me-3"><i class="fa-duotone fa-regular fa-trash-can"></i> 清理未支付</button>
      <span class="align-self-center text-muted me-3 recharge-stat">共 <b class="recharge_count">0</b> 单，金额 <b class="recharge_amount">￥0.00</b></span>
    </div>
  </div>
  <div class="card-body py-3">
    <div class="row g-2 mb-3">
      <div class="col-md-3"><input class="form-control rc-f-trade" placeholder="订单号"></div>
      <div class="col-md-2"><input class="form-control rc-f-user" placeholder="会员ID" inputmode="numeric"></div>
      <div class="col-md-2"><select class="form-select rc-f-status"><option value="">全部状态</option><option value="0">未支付</option><option value="1">已支付</option></select></div>
      <div class="col-md-2"><input class="form-control rc-f-ip" placeholder="IP地址"></div>
    </div>
    <div class="table-responsive">
      <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3" id="recharge-table">
        <thead><tr class="fw-bold text-muted">
          <th>订单号</th><th>会员</th><th>金额</th><th>支付</th><th>下单时间</th><th>IP</th><th>状态</th><th>支付时间</th><th>操作</th>
        </tr></thead>
        <tbody></tbody>
      </table>
      <div class="d-flex justify-content-end align-items-center mt-3">
        <button class="btn btn-sm btn-secondary crud-prev me-2">上一页</button>
        <span class="crud-pageinfo me-2"></span>
        <button class="btn btn-sm btn-secondary crud-next">下一页</button>
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
    const sym = '￥';
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
            const stTxt = Number(o.status) === 1 ? '<span class="badge badge-light-success">已支付</span>' : '<span class="badge badge-light-secondary">未支付</span>';
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
              '<td>' + (Number(o.status) === 0 ? '<button class="btn btn-sm btn-light-success row-supplement">补单</button>' : '') + '</td>';
            tbody.appendChild(tr);
          });
          document.querySelector('.recharge_count').textContent = res.data.count || 0;
          document.querySelector('.recharge_amount').textContent = sym + Number(res.data.order_amount || 0).toFixed(2);
          document.querySelector('.crud-pageinfo').textContent = '第 ' + page + ' 页 / 共 ' + (res.data.count || 0) + ' 条';
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
      if (!confirm('补单会把充值订单标记为已支付，并立即增加会员余额。\\n\\n订单号：' + tr.dataset.tradeNo + '\\n会员：' + tr.dataset.userName + '\\n金额：' + sym + tr.dataset.amount + '\\n\\n该操作会真实入账且无法在本页面撤销。')) return;
      util.post({ url: API + 'success', data: { id: tr.dataset.id }, done: res => { message.success(res.msg); load(); }, error: res => message.error(res.msg) });
    });

    document.querySelector('.recharge-clear').addEventListener('click', () => {
      if (!confirm('只会物理删除 30 分钟前仍未支付的充值订单；已支付订单不会受影响。确认清理吗？')) return;
      util.post({ url: API + 'clear', done: res => { message.success(res.msg); load(); }, error: res => message.error(res.msg) });
    });

    load();
  });`;

  return renderCrudPage({ cfg, manage, title: '充值订单', activePath: '/admin/recharge/order', body, readyJs: js });
}

// ============================================================
// 优惠券管理页 (对齐 Admin/Api/Coupon)
// ============================================================
export function renderAdminCouponPage(cfg, manage) {
  const body = `
<div class="card mb-5 mb-xl-8">
  <div class="card-header border-0">
    <div class="card-toolbar d-flex flex-wrap">
      <button class="btn btn-sm btn-light-primary crud-add me-3"><i class="fa-duotone fa-regular fa-ticket"></i> 批量生成</button>
      <button class="btn btn-sm btn-light-warning coupon-lock me-3"><i class="fa-duotone fa-regular fa-lock"></i> 锁定选中</button>
      <button class="btn btn-sm btn-light-info coupon-unlock me-3"><i class="fa-duotone fa-regular fa-unlock"></i> 解锁选中</button>
      <button class="btn btn-sm btn-light-danger coupon-del me-3"><i class="fa-duotone fa-regular fa-trash-can"></i> 移除选中</button>
      <button class="btn btn-sm btn-light-primary coupon-export me-3"><i class="fa-duotone fa-regular fa-file-export"></i> 导出券码</button>
    </div>
  </div>
  <div class="card-body py-3">
    <div class="row g-2 mb-3">
      <div class="col-md-2"><input class="form-control cp-f-code" placeholder="券码"></div>
      <div class="col-md-2"><select class="form-select cp-f-status"><option value="">全部状态</option><option value="0">未使用</option><option value="1">已使用</option><option value="2">已锁定</option></select></div>
      <div class="col-md-2"><input class="form-control cp-f-money" placeholder="券面值" inputmode="numeric"></div>
      <div class="col-md-2"><input class="form-control cp-f-owner" placeholder="会员ID" inputmode="numeric"></div>
      <div class="col-md-2"><input class="form-control cp-f-note" placeholder="备注信息"></div>
      <div class="col-md-2"><input class="form-control cp-f-commodity" placeholder="商品ID" inputmode="numeric"></div>
    </div>
    <div class="table-responsive">
      <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3" id="coupon-table">
        <thead><tr class="fw-bold text-muted">
          <th style="width:40px"><input type="checkbox" class="crud-check-all"></th>
          <th>ID</th><th>券码</th><th>抵扣范围</th><th>面值</th><th>剩余/已用次数</th><th>状态</th><th>过期时间</th><th>生成时间</th><th>最后订单号</th><th>备注</th><th>操作</th>
        </tr></thead>
        <tbody></tbody>
      </table>
      <div class="d-flex justify-content-end align-items-center mt-3">
        <button class="btn btn-sm btn-secondary crud-prev me-2">上一页</button>
        <span class="crud-pageinfo me-2"></span>
        <button class="btn btn-sm btn-secondary crud-next">下一页</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" tabindex="-1" id="couponModal"><div class="modal-dialog modal-lg"><div class="modal-content">
  <div class="modal-header py-3"><h5 class="modal-title">批量生成优惠券</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <form class="coupon-form"><div class="modal-body">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">前缀 <span class="text-muted">(可选, 1-16位 数字字母_-)</span></label>
        <input class="form-control" name="prefix" maxlength="16" placeholder="如 VIP"></div>
      <div class="col-md-6"><label class="form-label">数量</label>
        <input class="form-control" name="num" type="number" min="1" max="1000" value="10" required></div>
      <div class="col-md-6"><label class="form-label">抵扣模式</label>
        <select class="form-select" name="mode"><option value="0">金额抵扣</option><option value="1">百分比抵扣(0-1)</option></select></div>
      <div class="col-md-6"><label class="form-label">优惠金额 / 比例</label>
        <input class="form-control" name="money" type="number" step="0.01" value="1" required></div>
      <div class="col-md-6"><label class="form-label">可用次数</label>
        <input class="form-control" name="life" type="number" min="1" max="1000000" value="1" required></div>
      <div class="col-md-6"><label class="form-label">过期时间 <span class="text-muted">(可选)</span></label>
        <input class="form-control" name="expire_time" type="datetime-local"></div>
      <div class="col-md-6"><label class="form-label">抵扣范围 <span class="text-muted">(商品ID 或 分类ID 选一)</span></label>
        <input class="form-control" name="commodity_id" type="number" min="0" value="0"></div>
      <div class="col-md-6"><label class="form-label">商品分类ID <span class="text-muted">(0 不限)</span></label>
        <input class="form-control" name="category_id" type="number" min="0" value="0"></div>
      <div class="col-md-6"><label class="form-label">商品种类 race</label>
        <input class="form-control" name="race" maxlength="32"></div>
      <div class="col-md-6"><label class="form-label">备注</label>
        <input class="form-control" name="note" maxlength="32"></div>
    </div>
    <div class="mt-3"><label class="form-label">生成结果</label>
      <textarea class="form-control coupon-result" rows="6" readonly placeholder="生成后券码将显示在此"></textarea></div>
  </div><div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
    <button type="submit" class="btn btn-primary">立即生成</button>
  </div></form>
</div></div></div>`;

  const js = `
  ready(() => {
    const tbody = document.getElementById('coupon-table').querySelector('tbody');
    const API = '/admin/api/coupon/';
    let page = 1, pageSize = 10;
    const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    const sym = '￥';
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
            const stTxt = Number(c.status) === 0 ? '<span class="badge badge-light-success">未使用</span>'
              : Number(c.status) === 1 ? '<span class="badge badge-light-secondary">已使用</span>'
              : '<span class="badge badge-light-warning">已锁定</span>';
            const modeTxt = Number(c.mode) === 1 ? (c.money * 100 + '%') : (sym + c.money);
            const scope = c.category ? ('分类:' + esc(c.category.name)) : c.commodity ? ('商品:' + esc(c.commodity.name)) : '不限';
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
              '<td><button class="btn btn-sm btn-light-danger row-del me-1">删除</button>' +
              (Number(c.status) === 0 ? '<button class="btn btn-sm btn-light-warning row-lock">锁定</button>'
                : Number(c.status) === 2 ? '<button class="btn btn-sm btn-light-info row-unlock">解锁</button>' : '') +
              '</td>';
            tbody.appendChild(tr);
          });
          document.querySelector('.crud-pageinfo').textContent = '第 ' + page + ' 页 / 共 ' + (res.data.count || 0) + ' 条';
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
        message.alert(res.msg || '生成完毕', 'success');
        document.querySelector('.coupon-result').value = (res.data && res.data.code) || '';
        load();
      }, error: res => message.error(res.msg) });
    });

    // 批量锁定/解锁/删除
    function batch(act, msg, confirmTxt) {
      const ids = selected();
      if (!ids.length) { message.error('请至少勾选 1 张优惠卷！'); return; }
      if (!confirm(confirmTxt + ' ' + ids.length + ' 张?')) return;
      util.post({ url: API + act, data: { list: ids }, done: res => { message.success(res.msg); load(); }, error: res => message.error(res.msg) });
    }
    document.querySelector('.coupon-lock').addEventListener('click', () => batch('lock', '锁定', '确认锁定选中的'));
    document.querySelector('.coupon-unlock').addEventListener('click', () => batch('unlock', '解锁', '确认解锁选中的'));
    document.querySelector('.coupon-del').addEventListener('click', () => {
      const ids = selected();
      if (!ids.length) { message.error('请至少勾选 1 张优惠卷！'); return; }
      util.post({ url: API + 'deleteImpact', data: { list: ids }, loader: false, done: res => {
        const d = res.data || {};
        const msg = '所选 ' + d.coupon_count + ' 张：未使用 ' + d.normal_count + '、已使用 ' + d.used_count + '、锁定 ' + d.locked_count + '。';
        if (!d.can_delete) { message.alert(msg + ' 包含已使用/带订单号/被订单引用，已阻止删除。', 'error'); return; }
        if (!confirm(msg + '\\n\\n确认永久删除？')) return;
        util.post({ url: API + 'del', data: { list: ids }, done: r => { message.success(r.msg); load(); }, error: r => message.error(r.msg) });
      }, error: res => message.error(res.msg) });
    });

    tbody.addEventListener('click', e => {
      const tr = e.target.closest('tr'); if (!tr) return;
      const id = tr.dataset.id;
      if (e.target.closest('.row-del')) {
        util.post({ url: API + 'deleteImpact', data: { list: id }, loader: false, done: res => {
          const d = res.data || {};
          if (!d.can_delete) { message.alert('该券已被使用或引用，无法删除。', 'error'); return; }
          if (!confirm('确认删除券码 ' + tr.querySelector('code').textContent + ' ?')) return;
          util.post({ url: API + 'del', data: { list: id }, done: r => { message.success(r.msg); load(); }, error: r => message.error(r.msg) });
        }, error: res => message.error(res.msg) });
      } else if (e.target.closest('.row-lock')) {
        util.post({ url: API + 'lock', data: { list: id }, done: load, error: res => message.error(res.msg) });
      } else if (e.target.closest('.row-unlock')) {
        util.post({ url: API + 'unlock', data: { list: id }, done: load, error: res => message.error(res.msg) });
      }
    });

    // 导出券码
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
        if (total < 1) { message.error('当前筛选没有可导出的优惠卷'); return; }
        if (!confirm('当前筛选共 ' + total + ' 张优惠券（未使用 ' + d.normal_count + '、已使用 ' + d.used_count + '、锁定 ' + d.locked_count + '）。\\n确认导出券码为 txt 文件？')) return;
        const dl = Object.assign({}, payload, { expected_count: total });
        fetch(API + 'export', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(dl) })
          .then(r => {
            const ct = r.headers.get('content-type') || '';
            if (ct.includes('application/json')) return r.json().then(j => { throw new Error(j.msg || '导出失败'); });
            return r.blob().then(blob => {
              const url = URL.createObjectURL(blob);
              const a = document.createElement('a');
              a.href = url; a.download = 'coupons-' + total + '-' + new Date().toISOString().slice(0,10) + '.txt';
              document.body.appendChild(a); a.click(); a.remove();
              setTimeout(() => URL.revokeObjectURL(url), 1000);
              message.success('已导出 ' + total + ' 张优惠券');
            });
          })
          .catch(err => message.error(err.message));
      }, error: res => message.error(res.msg) });
    });

    load();
  });`;

  return renderCrudPage({ cfg, manage, title: '优惠券', activePath: '/admin/coupon/index', body, readyJs: js });
}

// 工单管理
export function renderAdminTicketPage(cfg, manage) {
  const body = `
<div class="row g-5 mb-5 gx-5 gy-3">
  <div class="col-sm-6 col-xl-2"><div class="ticket-stat-card pending_admin card card-flush py-4 px-4">
    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center">
      <div><div class="fs-7 fw-bold text-muted">待客服回复</div><div class="fs-2hx fw-bolder text-danger ticket-stat-num" data-stat="pending_admin">0</div></div>
      <div class="symbol symbol-32px symbol-circle bg-light-danger text-danger"><span class="fs-3 fw-bolder">待</span></div>
    </div></div></div>
  <div class="col-sm-6 col-xl-2"><div class="ticket-stat-card pending_user card card-flush py-4 px-4">
    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center">
      <div><div class="fs-7 fw-bold text-muted">待用户回复</div><div class="fs-2hx fw-bolder text-warning ticket-stat-num" data-stat="pending_user">0</div></div>
      <div class="symbol symbol-32px symbol-circle bg-light-warning text-warning"><span class="fs-3 fw-bolder">待</span></div>
    </div></div></div>
  <div class="col-sm-6 col-xl-2"><div class="ticket-stat-card resolved card card-flush py-4 px-4">
    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center">
      <div><div class="fs-7 fw-bold text-muted">已解决</div><div class="fs-2hx fw-bolder text-success ticket-stat-num" data-stat="resolved">0</div></div>
      <div class="symbol symbol-32px symbol-circle bg-light-success text-success"><span class="fs-3 fw-bolder">解</span></div>
    </div></div></div>
  <div class="col-sm-6 col-xl-2"><div class="ticket-stat-card closed card card-flush py-4 px-4">
    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center">
      <div><div class="fs-7 fw-bold text-muted">已关闭</div><div class="fs-2hx fw-bolder text-muted ticket-stat-num" data-stat="closed">0</div></div>
      <div class="symbol symbol-32px symbol-circle bg-light-secondary text-secondary"><span class="fs-3 fw-bolder">关</span></div>
    </div></div></div>
  <div class="col-sm-6 col-xl-2"><div class="ticket-stat-card today card card-flush py-4 px-4">
    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center">
      <div><div class="fs-7 fw-bold text-muted">今日新增</div><div class="fs-2hx fw-bolder text-primary ticket-stat-num" data-stat="today">0</div></div>
      <div class="symbol symbol-32px symbol-circle bg-light-primary text-primary"><span class="fs-3 fw-bolder">今</span></div>
    </div></div></div>
  <div class="col-sm-6 col-xl-2"><div class="ticket-stat-card total card card-flush py-4 px-4">
    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center">
      <div><div class="fs-7 fw-bold text-muted">工单总数</div><div class="fs-2hx fw-bolder ticket-stat-num" data-stat="total">0</div></div>
      <div class="symbol symbol-32px symbol-circle bg-light-info text-info"><span class="fs-3 fw-bolder">总</span></div>
    </div></div></div>
</div>
<div class="card mb-5 mb-xl-8">
  <div class="card-header border-0 py-4">
    <div class="card-title">
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <select class="form-select form-select-sm w-auto tk-f-status"><option value="">全部状态</option><option value="0">待客服回复</option><option value="1">待用户回复</option><option value="2">已解决</option><option value="3">已关闭</option></select>
        <select class="form-select form-select-sm w-auto tk-f-type"><option value="">全部类型</option><option value="0">售前咨询</option><option value="1">售后支持</option></select>
        <select class="form-select form-select-sm w-auto tk-f-priority"><option value="">全部优先级</option><option value="2">高</option><option value="1">中</option><option value="0">低</option></select>
        <select class="form-select form-select-sm w-auto tk-f-uid" title="按用户ID"><option value="">全部用户</option></select>
        <input class="form-control form-control-sm w-auto tk-f-keyword" placeholder="单号/标题/商品/订单号/用户名">
        <button class="btn btn-sm btn-light-primary tk-search"><i class="fa-duotone fa-regular fa-magnifying-glass"></i> 搜索</button>
      </div>
    </div>
    <div class="card-toolbar">
      <button class="btn btn-sm btn-light-danger tk-del-all me-3"><i class="fa-duotone fa-regular fa-trash-can"></i> 删除选中</button>
      <button class="btn btn-sm btn-light-primary tk-refresh"><i class="fa-duotone fa-regular fa-rotate"></i> 刷新</button>
    </div>
  </div>
  <div class="card-body py-3">
    <div class="table-responsive">
      <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3" id="ticket-table">
        <thead><tr class="fw-bold text-muted">
          <th style="width:40px"><input type="checkbox" class="crud-check-all"></th>
          <th>工单号</th><th>标题</th><th>用户</th><th>类型</th><th>优先级</th><th>状态</th><th>最后消息</th><th>最后时间</th><th>操作</th>
        </tr></thead>
        <tbody></tbody>
      </table>
    </div>
    <div class="d-flex flex-stack flex-wrap pt-5">
      <div class="fs-7 fw-bold text-muted crud-pageinfo">第 1 页 / 共 0 条</div>
      <div class="d-flex align-items-center">
        <button class="btn btn-sm btn-light crud-prev me-2">上一页</button>
        <button class="btn btn-sm btn-light crud-next">下一页</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" tabindex="-1" id="ticketModal"><div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content">
  <div class="modal-header py-3">
    <h5 class="modal-title ticket-title">工单详情</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <div class="modal-body">
    <div class="row g-3 mb-4">
      <div class="col-md-3"><label class="form-label text-muted">工单号</label><div class="fw-bolder ticket-meta-no">-</div></div>
      <div class="col-md-3"><label class="form-label text-muted">用户</label><div class="fw-bolder ticket-meta-user">-</div></div>
      <div class="col-md-2"><label class="form-label text-muted">类型</label><div class="fw-bolder ticket-meta-type">-</div></div>
      <div class="col-md-2"><label class="form-label text-muted">优先级</label><div class="fw-bolder ticket-meta-priority">-</div></div>
      <div class="col-md-2"><label class="form-label text-muted">状态</label><div class="fw-bolder ticket-meta-status">-</div></div>
      <div class="col-md-6"><label class="form-label text-muted">商品</label><div class="ticket-meta-commodity">-</div></div>
      <div class="col-md-6"><label class="form-label text-muted">关联订单</label><div class="ticket-meta-order">-</div></div>
      <div class="col-md-6"><label class="form-label text-muted">创建时间</label><div class="ticket-meta-created">-</div></div>
      <div class="col-md-6"><label class="form-label text-muted">关闭时间</label><div class="ticket-meta-closed">-</div></div>
    </div>
    <div class="separator border-2 my-4"></div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="fs-6 fw-bold">会话消息</div>
      <div><button class="btn btn-sm btn-light-primary tk-history-prev">加载更早</button></div>
    </div>
    <div class="ticket-messages bg-light rounded p-3 mb-4" style="max-height:420px;overflow-y:auto"></div>
    <div class="separator border-2 my-4"></div>
    <label class="form-label fw-bold">回复内容</label>
    <textarea class="form-control ticket-reply-content mb-3" rows="3" placeholder="请输入回复内容..."></textarea>
    <div class="text-muted fs-8 mb-3">允许少量 HTML；回复图片功能在当前环境不可用。</div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-light-danger ticket-delete me-auto" data-bs-dismiss="modal">删除工单</button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
    <button type="button" class="btn btn-light-warning ticket-close">关闭工单</button>
    <button type="button" class="btn btn-light-success ticket-resolve">回复并解决</button>
    <button type="button" class="btn btn-primary ticket-reply">回复</button>
  </div>
</div></div></div>`;

  const js = `
  ready(() => {
    const tbody = document.getElementById('ticket-table').querySelector('tbody');
    const API = '/admin/api/ticket/';
    let page = 1, pageSize = 20, currentId = 0;
    const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    const badge = (txt, kind) => '<span class="badge badge-light-' + kind + '">' + txt + '</span>';
    const statusBadge = st => Number(st) === 0 ? badge('待客服回复', 'danger') : Number(st) === 1 ? badge('待用户回复', 'warning') : Number(st) === 2 ? badge('已解决', 'success') : badge('已关闭', 'secondary');
    const typeBadge = t => Number(t) === 1 ? badge('售后支持', 'info') : badge('售前咨询', 'primary');
    const priorityBadge = p => Number(p) === 2 ? badge('高', 'danger') : Number(p) === 1 ? badge('中', 'warning') : badge('低', 'secondary');
    const senderBadge = s => Number(s) === 1 ? badge('管理员', 'primary') : Number(s) === 2 ? badge('系统', 'dark') : badge('用户', 'info');
    const filters = () => {
      const d = { page, limit: pageSize };
      const st = document.querySelector('.tk-f-status').value;
      const ty = document.querySelector('.tk-f-type').value;
      const pr = document.querySelector('.tk-f-priority').value;
      const uid = document.querySelector('.tk-f-uid').value;
      const kw = document.querySelector('.tk-f-keyword').value.trim();
      if (st !== '') d['equal-status'] = st;
      if (ty !== '') d['equal-type'] = ty;
      if (pr !== '') d['equal-priority'] = pr;
      if (uid !== '') d['equal-user_id'] = uid;
      if (kw) d.keyword = kw;
      return d;
    };
    function load() {
      util.post({ url: API + 'data', data: filters(), loader: false,
        done: res => {
          const d = res.data || {};
          const st = d.stats || {};
          [['pending_admin','pending_admin'],['pending_user','pending_user'],['resolved','resolved'],['closed','closed'],['today','today']].forEach(([attr]) => {
            document.querySelector('.ticket-stat-num[data-stat="' + attr + '"]').textContent = Number(st[attr] || 0);
          });
          document.querySelector('.ticket-stat-num[data-stat="total"]').textContent = Number(d.count || 0);
          tbody.innerHTML = '';
          (d.list || []).forEach(t => {
            const tr = document.createElement('tr');
            tr.dataset.id = t.id;
            tr.innerHTML = '<td><input type="checkbox" class="crud-check"></td>' +
              '<td><code>' + esc(t.ticket_no) + '</code></td>' +
              '<td class="fw-bold">' + esc(t.title) + '</td>' +
              '<td>' + (t.user ? esc(t.user.username) : '#' + t.user_id) + '</td>' +
              '<td>' + typeBadge(t.type) + '</td>' +
              '<td>' + priorityBadge(t.priority) + '</td>' +
              '<td>' + statusBadge(t.status) + (Number(t.manage_unread) > 0 ? ' <span class="badge badge-light-dark">新</span>' : '') + '</td>' +
              '<td><div class="text-truncate" style="max-width:240px">' + (t.last_sender_text ? senderBadge(t.last_sender_type) + ' ' : '') + esc(t.last_message_excerpt || '-') + '</div></td>' +
              '<td><small>' + (t.last_message_time ? new Date(t.last_message_time * 1000).toLocaleString() : '-') + '</small></td>' +
              '<td><div class="d-flex gap-1">' +
              '<button class="btn btn-sm btn-light-primary row-view">详情</button>' +
              '<button class="btn btn-sm btn-light-danger row-del">删除</button>' +
              '</div></td>';
            tbody.appendChild(tr);
          });
          document.querySelector('.crud-pageinfo').textContent = '第 ' + page + ' 页 / 共 ' + (d.count || 0) + ' 条';
        },
        error: res => message.error(res.msg) });
    }
    function selected() { return [...tbody.querySelectorAll('.crud-check:checked')].map(x => x.closest('tr').dataset.id); }
    function messageItem(m) {
      const wrap = document.createElement('div');
      wrap.className = 'd-flex gap-2 mb-3' + (Number(m.sender_type) === 1 ? ' flex-row-reverse' : '');
      wrap.innerHTML = '<div class="border rounded p-3 bg-white" style="max-width:80%">' +
        '<div class="d-flex justify-content-between align-items-center gap-3 mb-1">' +
        '<span class="fw-bold fs-8">' + senderBadge(m.sender_type) + ' ' + esc(m.sender_name) + '</span>' +
        '<small class="text-muted">' + new Date(Number(m.create_time) * 1000).toLocaleString() + '</small></div>' +
        '<div class="ticket-message-content">' + (m.content || '') + '</div></div>';
      return wrap;
    }
    function openDetail(id) {
      currentId = Number(id);
      util.post({ url: API + 'detail', data: { id: currentId, limit: 30 }, loader: false, done: res => {
        const t = res.data.ticket;
        document.querySelector('.ticket-title').textContent = '工单详情 - ' + t.ticket_no;
        document.querySelector('.ticket-meta-no').textContent = t.ticket_no;
        document.querySelector('.ticket-meta-user').textContent = t.user_id + (t.user ? ' (' + esc(t.user.username) + ')' : '');
        document.querySelector('.ticket-meta-type').textContent = t.type_text;
        document.querySelector('.ticket-meta-priority').textContent = t.priority_text;
        document.querySelector('.ticket-meta-status').innerHTML = statusBadge(t.status);
        document.querySelector('.ticket-meta-commodity').textContent = t.commodity_name ? esc(t.commodity_name) + (t.commodity ? ' (ID ' + t.commodity.id + ')' : '') : '无';
        document.querySelector('.ticket-meta-order').innerHTML = t.order ? (esc(t.order.trade_no) + '<small class="text-muted ms-2">¥' + Number(t.order.amount || 0).toFixed(2) + '</small>') : (t.order_trade_no ? esc(t.order_trade_no) + ' <span class="badge badge-light-warning">' + (t.order_verification_pending ? '待核验' : '') + '</span>' : '无关联订单');
        document.querySelector('.ticket-meta-created').textContent = t.create_time ? new Date(t.create_time * 1000).toLocaleString() : '-';
        document.querySelector('.ticket-meta-closed').textContent = t.closed_time ? new Date(t.closed_time * 1000).toLocaleString() : '-';
        const box = document.querySelector('.ticket-messages');
        box.innerHTML = '';
        (res.data.messages || []).forEach(m => box.appendChild(messageItem(m)));
        box.scrollTop = box.scrollHeight;
        document.querySelector('.tk-history-prev').style.display = res.data.has_more ? '' : 'none';
        document.querySelector('.ticket-reply').disabled = Number(t.status) >= 2;
        document.querySelector('.ticket-resolve').disabled = Number(t.status) >= 2;
        document.querySelector('.ticket-close').disabled = Number(t.status) >= 2;
        document.querySelector('.ticket-delete').dataset.id = t.id;
        (window.bootstrap && bootstrap.Modal.getOrCreateInstance(document.getElementById('ticketModal'))).show();
      }, error: res => message.error(res.msg) });
    }
    function loadHistory() {
      if (!currentId) return;
      const box = document.querySelector('.ticket-messages');
      const first = box.querySelector('.ticket-message-content');
      let beforeId = 0;
      const firstMsg = box.querySelector('[data-mid]');
      if (firstMsg) beforeId = Number(firstMsg.dataset.mid);
      util.post({ url: API + 'messages', data: { id: currentId, before_id: beforeId, limit: 30 }, loader: false, done: res => {
        const items = res.data.list || [];
        const list = document.createElement('div');
        let before = 0;
        items.forEach(m => {
          const el = messageItem(m);
          el.setAttribute('data-mid', m.id);
          list.appendChild(el);
          before = m.id;
        });
        box.insertBefore(list.firstChild ? list : document.createTextNode(''), box.firstChild);
        if (items.length) {
          const all = box.querySelectorAll('[data-mid]');
          all.forEach(el => el.removeAttribute('data-mid'));
          const arr = [...all].map(el => Number(el.dataset.mid));
        }
        document.querySelector('.tk-history-prev').style.display = res.data.has_more ? '' : 'none';
        box.scrollTop = box.scrollHeight;
      }, error: res => message.error(res.msg) });
    }
    function setReplyDraft(kind) {
      if (kind === 'resolve') document.querySelector('.ticket-reply-content').value = '';
    }
    function doReply(mode) {
      const content = document.querySelector('.ticket-reply-content').value.trim();
      if (!currentId) return;
      if (!content && mode !== 'resolve') { message.error('请输入回复内容'); return; }
      util.post({ url: API + 'reply', data: { id: currentId, content, mode }, done: res => {
        message.success(res.msg);
        document.querySelector('.ticket-reply-content').value = '';
        openDetail(currentId);
        load();
      }, error: res => message.error(res.msg) });
    }
    function confirmDel(ids, tip) {
      if (!ids.length) { message.error('请选择要删除的工单'); return; }
      if (!confirm((tip || '确认删除 ') + ids.length + ' 个工单（含全部消息）？')) return;
      util.post({ url: API + 'del', data: { list: ids }, done: r => { message.success(r.msg); if (currentId) { currentId = 0; bootstrap.Modal.getOrCreateInstance(document.getElementById('ticketModal')).hide(); } load(); }, error: r => message.error(r.msg) });
    }
    document.querySelector('.crud-check-all').addEventListener('change', e => tbody.querySelectorAll('.crud-check').forEach(x => x.checked = e.target.checked));
    document.querySelector('.tk-search').addEventListener('click', () => { page = 1; load(); });
    document.querySelector('.tk-refresh').addEventListener('click', () => load());
    document.querySelector('.tk-f-keyword').addEventListener('keydown', e => { if (e.key === 'Enter') { page = 1; load(); } });
    document.querySelector('.crud-prev').addEventListener('click', () => { if (page > 1) { page--; load(); } });
    document.querySelector('.crud-next').addEventListener('click', () => { page++; load(); });
    document.querySelector('.tk-del-all').addEventListener('click', () => confirmDel(selected(), '确认删除选中工单 '));
    document.querySelector('.ticket-reply').addEventListener('click', () => doReply('reply'));
    document.querySelector('.ticket-resolve').addEventListener('click', () => { if (confirm('回复并标记为已解决？')) doReply('resolve'); });
    document.querySelector('.ticket-close').addEventListener('click', () => {
      if (!confirm('确认关闭工单？关闭后用户无法继续回复。')) return;
      util.post({ url: API + 'close', data: { id: currentId }, done: r => { message.success(r.msg); openDetail(currentId); load(); }, error: r => message.error(r.msg) });
    });
    document.querySelector('.ticket-delete').addEventListener('click', () => confirmDel([currentId], '确认删除该工单 '));
    document.querySelector('.tk-history-prev').addEventListener('click', () => loadHistory());
    tbody.addEventListener('click', e => {
      const tr = e.target.closest('tr'); if (!tr) return;
      const id = tr.dataset.id;
      if (e.target.closest('.row-view')) openDetail(id);
      else if (e.target.closest('.row-del')) confirmDel([id], '确认删除工单 ');
    });
    load();
  });`;

  return renderCrudPage({ cfg, manage, title: '工单管理', activePath: '/admin/ticket/index', body, readyJs: js });
}

// 消息管理
export function renderAdminMessagePage(cfg, manage) {
  const body = `
<div class="card mb-5 mb-xl-8">
  <div class="card-header border-0 py-4">
    <div class="card-title">
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <select class="form-select form-select-sm w-auto ms-f-audience"><option value="">全部范围</option><option value="0">全体用户</option><option value="1">会员等级</option><option value="2">指定用户</option></select>
        <input class="form-control form-control-sm w-auto ms-f-keyword" placeholder="标题关键词">
        <button class="btn btn-sm btn-light-primary ms-search"><i class="fa-duotone fa-regular fa-magnifying-glass"></i> 搜索</button>
      </div>
    </div>
    <div class="card-toolbar">
      <button class="btn btn-sm btn-light-danger ms-del-all me-3"><i class="fa-duotone fa-regular fa-trash-can"></i> 删除选中</button>
      <button class="btn btn-sm btn-light-primary ms-add"><i class="fa-duotone fa-regular fa-circle-plus"></i> 发送消息</button>
    </div>
  </div>
  <div class="card-body py-3">
    <div class="table-responsive">
      <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3" id="message-table">
        <thead><tr class="fw-bold text-muted">
          <th style="width:40px"><input type="checkbox" class="crud-check-all"></th>
          <th>ID</th><th>标题</th><th>摘要</th><th>接收范围</th><th>接收人数</th><th>创建人</th><th>创建时间</th><th>操作</th>
        </tr></thead>
        <tbody></tbody>
      </table>
    </div>
    <div class="d-flex flex-stack flex-wrap pt-5">
      <div class="fs-7 fw-bold text-muted crud-pageinfo">第 1 页 / 共 0 条</div>
      <div class="d-flex align-items-center">
        <button class="btn btn-sm btn-light crud-prev me-2">上一页</button>
        <button class="btn btn-sm btn-light crud-next">下一页</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" tabindex="-1" id="msgModal"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
  <div class="modal-header py-3"><h5 class="modal-title">发送消息</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <form class="message-form"><div class="modal-body">
    <input type="hidden" name="id">
    <div class="row g-3">
      <div class="col-md-8"><label class="form-label"><span class="text-danger">*</span> 标题</label>
        <input class="form-control" name="title" maxlength="64" required></div>
      <div class="col-md-4"><label class="form-label"><span class="text-danger">*</span> 接收范围</label>
        <select class="form-select" name="audience_type" required><option value="0">全体用户</option><option value="1">会员等级</option><option value="2">指定用户</option></select></div>
      <div class="col-md-6 msg-audience-group d-none"><label class="form-label">会员等级</label>
        <select class="form-select" name="group_id"><option value="">请选择会员等级</option></select></div>
      <div class="col-md-6 msg-audience-user d-none"><label class="form-label">指定用户</label>
        <div class="input-group">
          <input class="form-control msg-user-search" placeholder="用户名/邮箱/手机/ID">
          <button type="button" class="btn btn-light-primary msg-user-search-btn">搜索</button>
        </div>
        <select class="form-select mt-2 d-none" name="user_id"></select>
        <div class="msg-user-result fs-8 text-muted mt-1"></div></div>
      <div class="col-md-6 msg-audience-count"><label class="form-label">预计接收人数</label>
        <div class="input-group">
          <input class="form-control msg-audience-num" readonly value="0">
          <button type="button" class="btn btn-light ms-audience-preview">预览</button>
        </div></div>
      <div class="col-md-6"><label class="form-label">跳转链接</label>
        <input class="form-control" name="jump_url" placeholder="https://..."></div>
      <div class="col-12"><label class="form-label"><span class="text-danger">*</span> 内容</label>
        <textarea class="form-control" name="content" rows="6" required placeholder="支持少量 HTML，如 <b>、<p>、<a href>"></textarea></div>
      <div class="form-check form-switch ms-3"><input class="form-check-input" type="checkbox" name="send_email" id="msg-send-email">
        <label class="form-check-label" for="msg-send-email">同时发送邮件通知（需已配置邮件服务）</label></div>
    </div>
  </div><div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
    <button type="submit" class="btn btn-primary">发送</button>
  </div></form>
</div></div></div>`;

  const js = `
  ready(() => {
    const tbody = document.getElementById('message-table').querySelector('tbody');
    const API = '/admin/api/message/';
    let page = 1, pageSize = 10;
    const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    const audienceBadge = at => Number(at) === 0 ? '<span class="badge badge-light-primary">全体用户</span>' : Number(at) === 1 ? '<span class="badge badge-light-warning">会员等级</span>' : '<span class="badge badge-light-info">指定用户</span>';
    const filters = () => {
      const d = { page, limit: pageSize };
      const at = document.querySelector('.ms-f-audience').value;
      const kw = document.querySelector('.ms-f-keyword').value.trim();
      if (at !== '') d['equal-audience_type'] = at;
      if (kw) d.keyword = kw;
      return d;
    };
    function auditTypeChange() {
      const at = Number(document.querySelector('.message-form [name=audience_type]').value);
      document.querySelector('.msg-audience-group').classList.toggle('d-none', at !== 1);
      document.querySelector('.msg-audience-user').classList.toggle('d-none', at !== 2);
      if (at !== 2) document.querySelector('.message-form [name=user_id]').value = '';
      document.querySelector('.msg-audience-num').value = '0';
    }
    function load() {
      util.post({ url: API + 'data', data: filters(), loader: false,
        done: res => {
          tbody.innerHTML = '';
          (res.data.list || []).forEach(m => {
            const tr = document.createElement('tr');
            tr.dataset.id = m.id;
            tr.innerHTML = '<td><input type="checkbox" class="crud-check"></td>' +
              '<td>' + m.id + '</td>' +
              '<td class="fw-bold">' + esc(m.title) + '</td>' +
              '<td><div class="text-truncate" style="max-width:260px">' + esc(m.summary || '-') + '</div></td>' +
              '<td>' + audienceBadge(m.audience_type) + ' <small class="text-muted">' + esc(m.audience_name || '') + '</small></td>' +
              '<td>' + m.recipient_count + '</td>' +
              '<td>' + esc(m.manage_name || '-') + '</td>' +
              '<td><small>' + (m.create_time ? new Date(m.create_time * 1000).toLocaleString() : '-') + '</small></td>' +
              '<td><div class="d-flex gap-1">' +
              '<button class="btn btn-sm btn-light-primary row-view">详情</button>' +
              '<button class="btn btn-sm btn-light-warning row-edit">编辑</button>' +
              '<button class="btn btn-sm btn-light-danger row-del">删除</button>' +
              '</div></td>';
            tbody.appendChild(tr);
          });
          document.querySelector('.crud-pageinfo').textContent = '第 ' + page + ' 页 / 共 ' + (res.data.count || 0) + ' 条';
        },
        error: res => message.error(res.msg) });
    }
    function selected() { return [...tbody.querySelectorAll('.crud-check:checked')].map(x => x.closest('tr').dataset.id); }
    function showModal(data) {
      const f = document.querySelector('.message-form');
      f.reset();
      f.querySelector('[name=id]').value = data && data.id ? data.id : '';
      f.querySelector('[name=title]').value = data ? (data.title || '') : '';
      f.querySelector('[name=content]').value = data ? (data.content || '') : '';
      f.querySelector('[name=jump_url]').value = data ? (data.jump_url || '') : '';
      f.querySelector('[name=audience_type]').value = data ? (data.audience_type || '0') : '0';
      f.querySelector('[name=group_id]').value = data && data.audience_id ? data.audience_id : '';
      f.querySelector('[name=user_id]').value = data && data.audience_id ? data.audience_id : '';
      document.querySelector('.msg-audience-group').classList.toggle('d-none', !data || Number(data.audience_type) !== 1);
      document.querySelector('.msg-audience-user').classList.toggle('d-none', !data || Number(data.audience_type) !== 2);
      f.querySelector('[name=send_email]').disabled = !!(data && data.id);
      document.querySelector('.msg-audience-num').value = data ? (data.recipient_count || '0') : '0';
      (window.bootstrap && bootstrap.Modal.getOrCreateInstance(document.getElementById('msgModal'))).show();
    }
    document.querySelector('.crud-check-all').addEventListener('change', e => tbody.querySelectorAll('.crud-check').forEach(x => x.checked = e.target.checked));
    document.querySelector('.ms-search').addEventListener('click', () => { page = 1; load(); });
    document.querySelector('.ms-add').addEventListener('click', () => showModal(null));
    document.querySelector('.crud-prev').addEventListener('click', () => { if (page > 1) { page--; load(); } });
    document.querySelector('.crud-next').addEventListener('click', () => { page++; load(); });
    document.querySelector('.ms-f-keyword').addEventListener('keydown', e => { if (e.key === 'Enter') { page = 1; load(); } });
    document.querySelector('.ms-del-all').addEventListener('click', () => {
      const ids = selected();
      if (!ids.length) { message.error('请选择要删除的消息'); return; }
      if (!confirm('确认删除选中 ' + ids.length + ' 条消息？')) return;
      util.post({ url: API + 'del', data: { list: ids }, done: r => { message.success(r.msg); load(); }, error: r => message.error(r.msg) });
    });
    document.querySelector('.message-form [name=audience_type]').addEventListener('change', auditTypeChange);
    document.querySelector('.ms-audience-preview').addEventListener('click', () => {
      const at = Number(document.querySelector('.message-form [name=audience_type]').value);
      const groupId = document.querySelector('.message-form [name=group_id]').value;
      const userId = document.querySelector('.message-form [name=user_id]').value;
      const data = { audience_type: at };
      if (at === 1 && groupId) data.group_id = groupId;
      if (at === 2 && userId) data.user_id = userId;
      util.post({ url: API + 'audienceCount', data, loader: false, done: r => { document.querySelector('.msg-audience-num').value = r.data.count || 0; }, error: r => message.error(r.msg) });
    });
    document.querySelector('.msg-user-search-btn').addEventListener('click', () => {
      const kw = document.querySelector('.msg-user-search').value.trim();
      const sel = document.querySelector('.message-form [name=user_id]');
      const box = document.querySelector('.msg-user-result');
      if (!kw) { message.error('请输入用户关键词'); return; }
      util.post({ url: API + 'users', data: { keyword: kw }, loader: false, done: r => {
        const list = r.data.list || [];
        sel.innerHTML = '<option value="">请选择用户</option>';
        list.forEach(u => {
          const o = document.createElement('option');
          o.value = u.id;
          o.textContent = '#' + u.id + ' ' + u.username + (u.group_name ? ' (' + u.group_name + ')' : '');
          sel.appendChild(o);
        });
        sel.classList.toggle('d-none', !list.length);
        box.textContent = list.length ? ('找到 ' + list.length + ' 个用户，请选择') : '未找到匹配用户';
      }, error: r => message.error(r.msg) });
    });
    document.querySelector('.message-form').addEventListener('submit', e => {
      e.preventDefault();
      const f = new FormData(e.target);
      const data = { title: f.get('title'), content: f.get('content'), jump_url: f.get('jump_url') || '', audience_type: f.get('audience_type') };
      const id = f.get('id');
      if (id) data.id = id;
      if (Number(data.audience_type) === 1) data.group_id = f.get('group_id') || 0;
      if (Number(data.audience_type) === 2) data.user_id = f.get('user_id') || 0;
      data.send_email = e.target.querySelector('[name=send_email]').checked ? 1 : 0;
      util.post({ url: API + 'save', data, done: r => {
        message.success(r.msg);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('msgModal')).hide();
        page = 1;
        load();
      }, error: r => message.error(r.msg) });
    });
    tbody.addEventListener('click', e => {
      const tr = e.target.closest('tr'); if (!tr) return;
      const id = tr.dataset.id;
      if (e.target.closest('.row-view')) {
        util.post({ url: API + 'detail', data: { id }, loader: false, done: r => {
          const m = r.data;
          const content = document.createElement('div');
          content.innerHTML = '<dl class="row mb-0">' +
            '<dt class="col-sm-3">标题</dt><dd class="col-sm-9">' + esc(m.title) + '</dd>' +
            '<dt class="col-sm-3">范围</dt><dd class="col-sm-9">' + audienceBadge(m.audience_type) + ' ' + esc(m.audience_name || '') + '（接收 ' + m.recipient_count + ' 人）</dd>' +
            '<dt class="col-sm-3">创建人</dt><dd class="col-sm-9">' + esc(m.manage_name || '-') + ' / ' + esc(m.update_manage_name || '-') + '</dd>' +
            '<dt class="col-sm-3">时间</dt><dd class="col-sm-9">' + (m.create_time ? new Date(m.create_time * 1000).toLocaleString() : '-') + '</dd>' +
            '</dl><hr><div class="border rounded p-3 bg-light">' + (m.content || '') + '</div>';
          message.alert(content.innerHTML, 'info', undefined, true);
        }, error: r => message.error(r.msg) });
      } else if (e.target.closest('.row-edit')) {
        util.post({ url: API + 'detail', data: { id }, loader: false, done: r => showModal(r.data), error: r => message.error(r.msg) });
      } else if (e.target.closest('.row-del')) {
        if (!confirm('确认删除该消息？')) return;
        util.post({ url: API + 'del', data: { list: [id] }, done: r => { message.success(r.msg); load(); }, error: r => message.error(r.msg) });
      }
    });
    // 载入会员等级选项
    util.post({ url: API + 'groups', data: {}, loader: false, done: r => {
      const list = (r.data && r.data.list) || (r.data || []);
      if (Array.isArray(list)) {
        const sel = document.querySelector('.message-form [name=group_id]');
        list.forEach(g => {
          const o = document.createElement('option');
          o.value = g.id;
          o.textContent = g.name;
          sel.appendChild(o);
        });
      }
    }, error: () => {} });
    load();
  });`;

  return renderCrudPage({ cfg, manage, title: '消息管理', activePath: '/admin/message/index', body, readyJs: js });
}

// 提现管理页
export function renderAdminCashPage(cfg, manage) {
  const body = `
<div class="card mb-5 mb-xl-8">
  <div class="card-header border-0 py-4">
    <div class="card-title">
      <div class="d-flex align-items-center flex-wrap gap-3">
        <input type="text" class="form-control form-control-sm" style="width:180px" id="cash-keyword" placeholder="用户名/支付宝/微信/地址/理由">
        <select class="form-select form-select-sm" style="width:140px" id="cash-status">
          <option value="">全部状态</option>
          <option value="0">待处理</option>
          <option value="1">已通过</option>
          <option value="2">已驳回</option>
        </select>
        <select class="form-select form-select-sm" style="width:130px" id="cash-type">
          <option value="">全部类型</option>
          <option value="0">普通提现</option>
          <option value="1">佣金提现</option>
        </select>
        <button class="btn btn-sm btn-primary cash-search"><i class="fa-duotone fa-regular fa-magnifying-glass"></i> 搜索</button>
        <span class="text-muted" id="cash-summary"></span>
      </div>
    </div>
    <div class="card-toolbar">
      <div class="d-flex align-items-center gap-2">
        <input type="number" class="form-control form-control-sm" style="width:150px" id="cash-settle-amount" placeholder="最低结算金额" min="0">
        <button class="btn btn-sm btn-light-primary cash-settle"><i class="fa-duotone fa-regular fa-arrows-rotate"></i> 一键自动结算</button>
      </div>
    </div>
  </div>
  <div class="card-body py-3">
    <div class="table-responsive">
      <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3" id="cash-table">
        <thead><tr class="fw-bold text-muted">
          <th>ID</th><th>用户</th><th>金额</th><th>费用</th><th>类型</th><th>状态</th><th>收款方式</th><th>申请时间</th><th>处理时间</th><th>驳回理由</th><th>操作</th>
        </tr></thead>
        <tbody></tbody>
      </table>
      <div class="d-flex justify-content-between align-items-center mt-3">
        <span class="text-muted" id="cash-pageinfo"></span>
        <div class="btn-group btn-group-sm" id="cash-pager"></div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" tabindex="-1" id="cashModal"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header py-3"><h5 class="modal-title">提现处理</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <div class="modal-body">
    <div class="mb-3">
      <label class="form-label">处理方式</label>
      <select class="form-select" id="cash-mode">
        <option value="0">通过（已打款）</option>
        <option value="1">驳回（退款到余额）</option>
      </select>
    </div>
    <div class="mb-3 cash-reject-box">
      <label class="form-label">驳回理由 <span class="text-danger">*</span></label>
      <textarea class="form-control" id="cash-message" rows="3" maxlength="64" placeholder="请输入驳回理由（不超过64字）"></textarea>
    </div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
    <button type="button" class="btn btn-primary" id="cash-submit">确认处理</button>
  </div>
</div></div></div>`;

  const js = `
  ready(() => {
    const table = document.getElementById('cash-table').querySelector('tbody');
    const API = '/admin/api/cash/';
    const state = { page: 1, pageSize: 10, total: 0, id: 0 };
    const modal = new bootstrap.Modal(document.getElementById('cashModal'));
    const esc = s => String(s == null ? '' : s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    function fmtTime(ts) { if (!ts) return '-'; const d = new Date(Number(ts) * 1000); return d.toLocaleString('zh-CN'); }
    function statusBadge(s) { return s === 0 ? '<span class="badge badge-light-warning">待处理</span>' : s === 1 ? '<span class="badge badge-light-success">已通过</span>' : '<span class="badge badge-light-danger">已驳回</span>'; }
    function load() {
      util.post({ url: API + 'data', loader: false, data: {
        page: state.page, limit: state.pageSize,
        'equal-status': document.getElementById('cash-status').value,
        'equal-type': document.getElementById('cash-type').value,
        keyword: document.getElementById('cash-keyword').value.trim(),
      },
        done: res => {
          const d = res.data || {};
          state.total = Number(d.total) || 0;
          document.getElementById('cash-summary').textContent = d.amount != null ? ('合计金额 ¥' + Number(d.amount).toFixed(2) + ' / 费用 ¥' + Number(d.cost || 0).toFixed(2)) : '';
          table.innerHTML = '';
          (d.list || []).forEach(c => {
            const u = c.user || {};
            const pay = u.alipay ? ('支付宝: ' + esc(u.alipay)) : u.wechat ? ('微信: ' + esc(u.wechat)) : u.wallet_address ? ('地址: ' + esc(u.wallet_address)) : '-';
            const tr = document.createElement('tr');
            tr.innerHTML = '<td>' + c.id + '</td>' +
              '<td>' + (u.username ? '<div class="d-flex align-items-center gap-2">' + (u.avatar ? '<img src="' + esc(u.avatar) + '" class="rounded-circle" style="width:24px;height:24px;object-fit:cover">' : '') + '<span>' + esc(u.username) + (u.nicename ? ' <span class="text-muted">(' + esc(u.nicename) + ')</span>' : '') + '</span></div>' : '<span class="text-muted">#' + c.user_id + '</span>') + '</td>' +
              '<td class="fw-bold">¥' + Number(c.amount).toFixed(2) + '</td>' +
              '<td>¥' + Number(c.cost || 0).toFixed(2) + '</td>' +
              '<td>' + (Number(c.type) === 1 ? '佣金' : '普通') + '</td>' +
              '<td>' + statusBadge(Number(c.status)) + '</td>' +
              '<td>' + pay + '</td>' +
              '<td>' + fmtTime(c.create_time) + '</td>' +
              '<td>' + fmtTime(c.arrive_time) + '</td>' +
              '<td>' + esc(c.message) + '</td>' +
              '<td>' + (Number(c.status) === 0 ? '<button class="btn btn-sm btn-light-success me-1 row-pass" data-id="' + c.id + '">通过</button><button class="btn btn-sm btn-light-danger row-reject" data-id="' + c.id + '">驳回</button>' : '<span class="text-muted">-</span>') + '</td>';
            table.appendChild(tr);
          });
          renderPager();
        },
        error: res => message.error(res.msg) });
    }
    function renderPager() {
      const pages = Math.max(1, Math.ceil(state.total / state.pageSize));
      document.getElementById('cash-pageinfo').textContent = '共 ' + state.total + ' 条 / 第 ' + state.page + ' 页';
      const el = document.getElementById('cash-pager');
      el.innerHTML = '';
      const mk = (label, p, dis) => { const b = document.createElement('button'); b.className = 'btn ' + (p === state.page ? 'btn-primary' : 'btn-light'); b.textContent = label; b.disabled = !!dis; b.addEventListener('click', () => { state.page = p; load(); }); el.appendChild(b); };
      mk('上一页', state.page - 1, state.page <= 1);
      mk('下一页', state.page + 1, state.page >= pages);
    }
    document.querySelector('.cash-search').addEventListener('click', () => { state.page = 1; load(); });
    document.querySelector('.cash-settle').addEventListener('click', () => {
      const amount = document.getElementById('cash-settle-amount').value;
      if (!amount || Number(amount) <= 0) { message.error('请输入有效的最低结算金额'); return; }
      if (!confirm('确定对余额大于 ¥' + amount + ' 的用户执行一键自动结算？')) return;
      util.post({ url: API + 'settlement', data: { amount }, done: res => { message.success(res.msg); load(); }, error: res => message.error(res.msg) });
    });
    function openModal(id, mode) {
      state.id = id;
      document.getElementById('cash-mode').value = String(mode);
      document.getElementById('cash-message').value = '';
      document.querySelector('.cash-reject-box').style.display = mode === 1 ? '' : 'none';
      modal.show();
    }
    document.addEventListener('click', e => {
      const p = e.target.closest('.row-pass'); if (p) { openModal(Number(p.dataset.id), 0); return; }
      const r = e.target.closest('.row-reject'); if (r) { openModal(Number(r.dataset.id), 1); return; }
    });
    document.getElementById('cash-mode').addEventListener('change', e => {
      document.querySelector('.cash-reject-box').style.display = Number(e.target.value) === 1 ? '' : 'none';
    });
    document.getElementById('cash-submit').addEventListener('click', () => {
      const mode = Number(document.getElementById('cash-mode').value);
      const msg = document.getElementById('cash-message').value.trim();
      if (mode === 1 && !msg) { message.error('请输入驳回理由'); return; }
      util.post({ url: API + 'decide', data: { id: state.id, status: mode, message: msg },
        done: res => { message.success(res.msg); modal.hide(); load(); }, error: res => message.error(res.msg) });
    });
    load();
  });`;

  return renderCrudPage({ cfg, manage, title: '提现管理', activePath: '/admin/cash/index', body, readyJs: js });
}

// 加载原版 controller JS (与原版 #{ready("/assets/admin/controller/xxx.js")} 等价)
const loadOrigCtl = (path) => `
  ready(function () {
    var s = document.createElement('script');
    s.src = ${JSON.stringify(path)};
    s.async = false;
    document.body.appendChild(s);
  });`;

// ============================================================
// 账单管理页 — 对齐原版 View/Admin/User/Bill.html
//   容器 <table id="bill-table"> + 原版 controller user/bill.js
// ============================================================
export function renderAdminBillPage(cfg, manage) {
  const body = `
<div class="card mb-5 mb-xl-8">
    <div class="card-body py-3">
        <table id="bill-table"></table>
    </div>
</div>`;
  return renderCrudPage({
    cfg, manage, title: '账单管理', activePath: '/admin/user/bill',
    body, readyJs: loadOrigCtl('/assets/admin/controller/user/bill.js'),
  });
}

// ============================================================
// 操作日志页 — 对齐原版 View/Admin/Manage/Log.html
//   容器 <table id="manage-log-table"> + 原版 controller manage/log.js
// ============================================================
export function renderAdminLogPage(cfg, manage) {
  const body = `
<div class="card mb-5 mb-xl-8">
    <div class="card-body py-3 mt-4">
        <table id="manage-log-table"></table>
    </div>
</div>`;
  return renderCrudPage({
    cfg, manage, title: '操作日志', activePath: '/admin/log/index',
    body, readyJs: loadOrigCtl('/assets/admin/controller/manage/log.js'),
  });
}

// ============================================================
// 支付插件页 — 对齐原版 View/Admin/Config/PayPlugin.html
//   容器 <table id="pay-plugin-table"> + 原版 controller pay/plugin.js
//   顶部保留原版「安装更多插件」入口(指向插件商店, 本移植无市场服务)
// ============================================================
export function renderAdminPayPluginPage(cfg, manage) {
  const body = `
<div class="card mb-5 mb-xl-8">
    <div class="card-header border-0">
        <div class="card-toolbar">
            <a href="/admin/store/home" class="btn btn-sm btn-light-primary btn-app-create me-3"><i
                        class="fa-duotone fa-regular fa-rectangle-history-circle-plus"></i>
                安装更多插件
            </a>
        </div>
    </div>
    <div class="card-body py-3">
        <table id="pay-plugin-table"></table>
    </div>
</div>`;
  return renderCrudPage({
    cfg, manage, title: '支付插件', activePath: '/admin/pay/plugin',
    body, readyJs: loadOrigCtl('/assets/admin/controller/pay/plugin.js'),
  });
}

// ============================================================
// 支付接口页 — 对齐原版 View/Admin/Config/Pay.html
//   工具栏「添加支付」「移除选中支付」+ <table id="pay-table">
//   + 原版 controller pay/api.js
// ============================================================
export function renderAdminPayPage(cfg, manage) {
  const body = `
<div class="card mb-5 mb-xl-8">
    <div class="card-header border-0">
        <div class="card-toolbar">
            <button class="btn btn-sm btn-light-primary btn-app-create me-3"><i class="fa-duotone fa-regular fa-circle-plus"></i>
                添加支付
            </button>
            <button class="btn btn-sm btn-light-danger btn-app-del me-3"><i class="fa-duotone fa-regular fa-trash-can"></i> 移除选中支付
            </button>
        </div>
    </div>
    <div class="card-body py-3">
        <table id="pay-table"></table>
    </div>
</div>`;
  return renderCrudPage({
    cfg, manage, title: '支付接口', activePath: '/admin/pay/index',
    body, readyJs: loadOrigCtl('/assets/admin/controller/pay/api.js'),
  });
}
