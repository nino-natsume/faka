// ============================================================
// acg-faka Worker 移植 - pages.js (认证 + 会员中心页面模板)
// 对齐 Cartoon 主题 Authentication/*.html 与原版结构
// ============================================================
import { htmlEscape, langMenu, indexVar } from './lib.js';

const CSS_AUTH = [
  '/assets/common/css/bootstrap.min.css',
  '/assets/common/css/_.css',
  '/assets/common/css/font.min.css',
  '/assets/common/js/layui/css/layui.css',
  '/assets/common/css/select2.min.css',
  '/assets/common/css/component.css',
  '/assets/common/js/table/bootstrap-table.css',
  '/assets/common/js/layer/theme/default/layer.css',
  '/assets/common/css/toastr.min.css',
  '/assets/user/css/_auth.css',
  '/assets/user/css/auth.css',
];

const JS_AUTH = [
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

// ---------- 认证页布局 (对齐 Authentication/Header.html + Footer.html) ----------
export function renderAuthHeader(v) {
  const { config, title, favicon, app } = v;
  return `<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta name="keywords" content="${htmlEscape(config.keywords || '')}"/>
    <meta name="description" content="${htmlEscape(config.description || '')}"/>
    <link href="${favicon}?v=${app.version}" rel="icon">
    <title>${htmlEscape(title)} - ${htmlEscape(config.shop_name)}</title>
    ${CSS_AUTH.map(f => `<link href="${f}" rel="stylesheet">`).join('')}
    <script src="/assets/common/js/ready.js"></script>
    ${indexVar(0, config)}
</head>
<body style="background-size: cover;background-image: linear-gradient(180deg, rgb(255 255 255 / 0%), rgb(255 255 255 / 71%)), url('${htmlEscape(config.background_url || '')}')">`;
}

export function renderAuthFooter() {
  return `${JS_AUTH.map(f => `<script src="${f}"></script>`).join('')}
</body>
</html>`;
}

// ---------- 登录页 (对齐 Authentication/Login.html) ----------
export function pageLogin(v) {
  const { config } = v;
  const captcha = Number(config.login_verification) === 1 ? `
                <div class="row mb-4">
                    <div class="col-sm-6 col-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="image-captcha" name="captcha" placeholder="请输入验证码">
                            <label class="form-label" for="image-captcha">图形验证码</label>
                        </div>
                    </div>
                    <div class="col-sm-6 col-6 d-flex align-items-center">
                        <img src="/user/captcha/image?action=login" data-acg-refresh="/user/captcha/image?action=login" class="image-code" alt="更换验证码">
                    </div>
                </div>` : '';
  const forgetUrl = Number(config.forget_type) === 0 ? '/user/authentication/emailForget' : '/user/authentication/phoneForget';
  const regLink = Number(config.registered_state) === 1 ? `
        <p class="text-center small mt-3 mb-0">
            还没有账号？
            <a class="text-link" href="/user/authentication/register">立即注册</a>
        </p>` : '';
  return `<main class="auth-wrapper">
    <div class="auth-card">

        <div class="brand-header">
            <div class="brand-logo">
                <img src="/favicon.ico" alt="Logo" class="brand-icon">
            </div>

        </div>
        <p class="auth-subtitle small mb-3">登入<a class="text-link" href="/">${htmlEscape(config.shop_name)}</a>，获取更多折扣！</p>

        <form method="post" class="needs-validation">
            <div class="form-floating mb-4">
                <input type="text" class="form-control" name="username" placeholder="用户名/手机号/邮箱" required>
                <label for="username">用户名/手机号/邮箱</label>
            </div>

            <div class="form-floating mb-4">
                <input type="password" class="form-control" name="password" placeholder="••••••••" minlength="6" required>
                <label for="password">密码</label>
            </div>

            ${captcha}

            <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
                <div class="form-check">
                    <input id="rememberMe" name="remember" type="checkbox" value="1">
                    <label for="rememberMe">保持会话</label>
                </div>
                <a href="${forgetUrl}" class="text-link small">忘记密码？</a>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-gradient btn-lg">登录</button>
            </div>
        </form>

        ${regLink}
    </div>
</main>
<script src="/assets/user/controller/auth/login.js"></script>`;
}

// ---------- 注册页 (对齐 Authentication/Register.html) ----------
export function pageRegister(v) {
  const { config } = v;
  const regType = Number(config.registered_type); // 0=任意 1=手机 2=邮箱
  let extraField = '';
  if (regType === 2) {
    extraField = `
                <div class="form-floating mb-4">
                    <input type="email" class="form-control" name="email" placeholder="邮箱" required>
                    <label for="email">邮箱</label>
                </div>
                ${Number(config.registered_email_verification) === 1 ? `
                    <div class="row mb-4">
                        <div class="col-sm-8 col-8">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="email_captcha" name="email_captcha" placeholder="邮箱验证码">
                                <label class="form-label" for="email_captcha">邮箱验证码</label>
                            </div>
                        </div>
                        <div class="col-sm-4 col-4">
                            <button type="button" class="w-100 btn btn-outline-primary py-3 send-email-code">发送验证码</button>
                        </div>
                    </div>` : ''}`;
  } else if (regType === 1) {
    extraField = `
                <div class="form-floating mb-4">
                    <input type="number" class="form-control" name="phone" placeholder="手机号" required>
                    <label for="phone">手机号</label>
                </div>
                ${Number(config.registered_phone_verification) === 1 ? `
                    <div class="row mb-4">
                        <div class="col-sm-8 col-8">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="phone_captcha" name="phone_captcha" placeholder="手机验证码">
                                <label class="form-label" for="phone_captcha">手机验证码</label>
                            </div>
                        </div>
                        <div class="col-sm-4 col-4">
                            <button type="button" class="w-100 btn btn-outline-primary py-3 send-phone-captcha">发送验证码</button>
                        </div>
                    </div>` : ''}`;
  }

  const captcha = Number(config.registered_verification) === 1 ? `
                <div class="row mb-4">
                    <div class="col-sm-6 col-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="image-captcha" name="captcha" placeholder="请输入验证码">
                            <label class="form-label" for="image-captcha">图形验证码</label>
                        </div>
                    </div>
                    <div class="col-sm-6 col-6 d-flex align-items-center">
                        <img src="/user/captcha/image?action=register" data-acg-refresh="/user/captcha/image?action=register" class="image-code" alt="更换验证码">
                    </div>
                </div>` : '';

  return `<main class="auth-wrapper">
    <div class="auth-card">

        <div class="brand-header">
            <div class="brand-logo">
                <img src="/favicon.ico" alt="Logo" class="brand-icon">
            </div>

        </div>
        <p class="auth-subtitle small mb-3">注册<a class="text-link" href="/">${htmlEscape(config.shop_name)}</a>，获取更多折扣！</p>

        <form class="needs-validation">
            <div class="form-floating mb-4">
                <input type="text" class="form-control" name="username" placeholder="用户名，支持中文" required>
                <label for="username">用户名，支持中文</label>
            </div>

            <div class="form-floating mb-4">
                <input type="text" class="form-control" name="password" placeholder="设置登录密码" minlength="6" required>
                <label for="password">设置登录密码</label>
            </div>

            ${extraField}
            ${captcha}

            <div class="d-grid">
                <button type="submit" class="btn btn-gradient btn-lg">注册</button>
            </div>
        </form>

        <p class="text-center small mt-3 mb-0">
            已有账号？
            <a class="text-link" href="/user/authentication/login">前往登录</a>
        </p>

    </div>
</main>
<script src="/assets/user/controller/auth/register.js"></script>`;
}

// ---------- 会员中心布局 (简单 panel 页, 服务端渲染) ----------
export function userCenterShell(v, body) {
  const { config, user } = v;
  if (!user) return '';
  const menu = [
    { url: '/user/dashboard/index', name: '个人中心', icon: 'fa-duotone fa-regular fa-user' },
    { url: '/user/personal/purchaseRecord', name: '我的订单', icon: 'fa-duotone fa-regular fa-receipt' },
    { url: '/user/recharge/index', name: '钱包充值', icon: 'fa-duotone fa-regular fa-wallet' },
    { url: '/user/bill/index', name: '余额明细', icon: 'fa-duotone fa-regular fa-list' },
    { url: '/user/security/personal', name: '账号设置', icon: 'fa-duotone fa-regular fa-gear' },
  ];
  const active = (u) => v.route && v.route.startsWith(u) ? ' active' : '';
  const menuHtml = menu.map(m => `<a class="list-group-item list-group-item-action${active(m.url)}" href="${m.url}"><i class="${m.icon} me-2"></i>${m.name}</a>`).join('');

  return `<main class="container py-4">
  <div class="row g-4">
    <div class="col-12 col-lg-3">
      <div class="panel">
        <div class="panel-body">
          <div class="d-flex align-items-center mb-3">
            <img src="${htmlEscape(user.avatar || '/favicon.ico')}" class="rounded-circle me-3" style="width:56px;height:56px;object-fit:cover;background:#f8f9fa">
            <div>
              <div class="fw-bold">${htmlEscape(user.username)}</div>
              <div class="text-muted small">余额: <span class="text-success">${htmlEscape(config.currency_symbol || '¥')}${user.balance}</span></div>
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

// ---------- 个人中心 (简化版 Dashboard) ----------
export function pageDashboard(v) {
  const { user, config } = v;
  const groupName = v.group ? htmlEscape(v.group.name) : '普通会员';
  const body = `
    <div class="row">
      <div class="col-12 col-md-6 mb-3">
        <div class="panel">
          <div class="panel-header"><span class="icon"><i class="fa-duotone fa-regular fa-id-card"></i></span><h6 class="panel-title">账号信息</h6></div>
          <div class="panel-body">
            <table class="table table-sm table-borderless">
              <tr><td class="text-muted">用户名</td><td>${htmlEscape(user.username)}</td></tr>
              <tr><td class="text-muted">会员等级</td><td>${groupName}</td></tr>
              <tr><td class="text-muted">余额</td><td class="text-success fw-bold">${htmlEscape(config.currency_symbol || '¥')}${user.balance}</td></tr>
              <tr><td class="text-muted">累计充值</td><td>${htmlEscape(config.currency_symbol || '¥')}${user.recharge}</td></tr>
              <tr><td class="text-muted">注册时间</td><td>${v.fmt ? v.fmt(user.create_time) : user.create_time}</td></tr>
            </table>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 mb-3">
        <div class="panel">
          <div class="panel-header"><span class="icon"><i class="fa-duotone fa-regular fa-bolt"></i></span><h6 class="panel-title">快捷入口</h6></div>
          <div class="panel-body">
            <div class="d-grid gap-2">
              <a class="btn btn-primary" href="/user/recharge/index"><i class="fa-duotone fa-regular fa-wallet me-2"></i>钱包充值</a>
              <a class="btn btn-outline-secondary" href="/user/personal/purchaseRecord"><i class="fa-duotone fa-regular fa-receipt me-2"></i>我的订单</a>
              <a class="btn btn-outline-secondary" href="/user/security/personal"><i class="fa-duotone fa-regular fa-gear me-2"></i>账号设置</a>
            </div>
          </div>
        </div>
      </div>
    </div>`;
  return userCenterShell(v, body);
}

// ---------- 订单记录 (简化版 PurchaseRecord, 列表由前端 controller 拉取) ----------
export function pagePurchaseRecord(v) {
  const body = `
    <div class="panel">
      <div class="panel-header"><span class="icon"><i class="fa-duotone fa-regular fa-receipt"></i></span><h6 class="panel-title">我的订单</h6></div>
      <div class="panel-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle" id="purchase-record-table">
            <thead>
              <tr>
                <th>订单号</th><th>商品</th><th>金额</th><th>状态</th><th>创建时间</th><th>操作</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
        <div id="purchase-record-empty" class="text-center text-muted py-4" style="display:none">暂无订单记录</div>
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
          var st = o.status == 1 ? '<span class="badge-soft badge-soft-success">已支付</span>' : '<span class="badge-soft badge-soft-warning">未支付</span>';
          var act = o.status == 1 ? '<a class="btn btn-sm btn-outline-primary" href="/user/index/query?tradeNo=' + o.trade_no + '">查看</a>' : '';
          return '<tr><td>' + o.trade_no + '</td><td>' + o.commodity_name + '</td><td>' + o.amount + '</td><td>' + st + '</td><td>' + new Date(o.create_time * 1000).toLocaleString() + '</td><td>' + act + '</td></tr>';
        }).join('');
        tbody.innerHTML = html;
      });
    })();
    </script>`;
  return userCenterShell(v, body);
}

// ---------- 钱包充值 (对齐 Recharge/Index.html 简化) ----------
export function pageRecharge(v) {
  const { config } = v;
  const payParams = (config.recharge_paylist || '1').split(',').map(Number).filter(n => n > 0);
  const payRows = v.payList || [];
  const payHtml = payRows.filter(p => payParams.includes(Number(p.id))).map((p, i) => `
      <div class="form-check pay-check ${i === 0 ? 'is-primary' : ''}" data-pay-id="${p.id}">
        <input class="form-check-input" type="radio" name="pay_id" id="pay-${p.id}" value="${p.id}" ${i === 0 ? 'checked' : ''}>
        <label class="form-check-label d-flex align-items-center w-100" for="pay-${p.id}">
          <img src="${htmlEscape(p.icon || '/favicon.ico')}" style="width:36px;height:36px;object-fit:cover" class="me-2 rounded">
          <span>${htmlEscape(p.name)}</span>
        </label>
      </div>`).join('');

  const body = `
    <div class="panel">
      <div class="panel-header"><span class="icon"><i class="fa-duotone fa-regular fa-wallet"></i></span><h6 class="panel-title">钱包充值</h6></div>
      <div class="panel-body">
        ${Number(config.recharge_open) === 1 ? `
        <form id="rechargeForm" class="vstack gap-3">
          <div>
            <label class="form-label mb-1">充值金额</label>
            <div class="input-group">
              <span class="input-group-text">${htmlEscape(config.currency_symbol || '¥')}</span>
              <input type="number" class="form-control" name="amount" min="${htmlEscape(config.recharge_min || 1)}" max="${htmlEscape(config.recharge_max || 0) || ''}" placeholder="请输入充值金额" value="${htmlEscape(config.recharge_min || '')}">
            </div>
            <div class="small text-muted mt-1">最低 ${htmlEscape(config.currency_symbol || '¥')}${htmlEscape(config.recharge_min || 1)}，最高 ${htmlEscape(config.currency_symbol || '¥')}${htmlEscape(config.recharge_max || '不限')}</div>
          </div>
          <div>
            <label class="form-label mb-1">支付方式</label>
            <div class="vstack gap-2">
              ${payHtml || '<div class="text-muted">暂无可用支付方式</div>'}
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-block">立即充值</button>
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
              else { alert(res.msg || '充值失败'); btn.disabled = false; }
            })
            .catch(function(){ alert('网络错误'); btn.disabled = false; });
        });
        </script>` : `<div class="text-muted">充值功能未开启</div>`}
      </div>
    </div>`;
  return userCenterShell(v, body);
}

// ---------- 账号设置 (对齐 Security/Personal.html 简化) ----------
export function pageSecurity(v) {
  const { user, config } = v;
  const body = `
    <div class="panel mb-3">
      <div class="panel-header"><span class="icon"><i class="fa-duotone fa-regular fa-user"></i></span><h6 class="panel-title">基本信息</h6></div>
      <div class="panel-body">
        <table class="table table-sm table-borderless">
          <tr><td class="text-muted" style="width:120px">用户名</td><td>${htmlEscape(user.username)}</td></tr>
          <tr><td class="text-muted">邮箱</td><td>${htmlEscape(user.email || '未绑定')}</td></tr>
          <tr><td class="text-muted">手机</td><td>${htmlEscape(user.phone || '未绑定')}</td></tr>
          <tr><td class="text-muted">余额</td><td class="text-success">${htmlEscape(config.currency_symbol || '¥')}${user.balance}</td></tr>
        </table>
      </div>
    </div>
    <div class="panel">
      <div class="panel-header"><span class="icon"><i class="fa-duotone fa-regular fa-key"></i></span><h6 class="panel-title">修改密码</h6></div>
      <div class="panel-body">
        <form id="pwdForm" class="vstack gap-3" style="max-width:420px">
          <div>
            <label class="form-label mb-1">当前密码</label>
            <input type="password" class="form-control" name="oldPassword" placeholder="请输入当前密码" autocomplete="current-password">
          </div>
          <div>
            <label class="form-label mb-1">新密码</label>
            <input type="password" class="form-control" name="password" placeholder="最少6位" autocomplete="new-password">
          </div>
          <button type="submit" class="btn btn-primary btn-block">保存修改</button>
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
              if (res.code === 200) { alert(res.msg || '修改成功'); } else { alert(res.msg || '修改失败'); }
            })
            .catch(function(){ btn.disabled = false; alert('网络错误'); });
        });
        </script>
      </div>
    </div>`;
  return userCenterShell(v, body);
}

// ---------- 余额明细 (简化版 Bill) ----------
export function pageBill(v) {
  const body = `
    <div class="panel">
      <div class="panel-header"><span class="icon"><i class="fa-duotone fa-regular fa-list"></i></span><h6 class="panel-title">余额明细</h6></div>
      <div class="panel-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle" id="bill-table">
            <thead><tr><th>时间</th><th>类型</th><th>金额</th><th>余额</th><th>说明</th></tr></thead>
            <tbody></tbody>
          </table>
        </div>
        <div id="bill-empty" class="text-center text-muted py-4" style="display:none">暂无余额变动记录</div>
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
          return '<tr><td>' + new Date(b.create_time * 1000).toLocaleString() + '</td><td>' + (b.type == 1 ? '收入' : '支出') + '</td><td class="' + sign + '">' + sym + b.amount + '</td><td>' + b.balance + '</td><td>' + b.log + '</td></tr>';
        }).join('');
        tbody.innerHTML = html;
      });
    })();
    </script>`;
  return userCenterShell(v, body);
}