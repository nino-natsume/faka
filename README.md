# DCSHOP 发卡系统 - Cloudflare 部署版

基于原项目 `D:\Desktop\za\faka`（DCSHOP / acg-faka PHP 发卡系统）改造的 Cloudflare
部署版本。**前台页面样式与原项目完全一致**（复用原项目全部 CSS/JS/字体/图片资源），
后端由 Cloudflare Workers + D1 实现商品浏览、下单、支付、自动发货、订单查询与管理后台。

## 目录结构

```
d:/desktop/faka/
├── worker.js         # Worker 入口：路由 + 页面渲染 + 下单/支付/发货 API
├── admin.js          # 管理后台（商品/卡密/订单/分类/设置）
├── lib.js            # 公共库（页面骨架/工具/导航）
├── schema.sql        # D1 数据库初始化（核心表，SQLite 方言，幂等）
├── seed.sql          # 演示种子数据（INSERT OR IGNORE，幂等，可重复执行）
├── wrangler.toml     # Cloudflare 部署配置
├── .github/workflows/deploy.yml  # GitHub Actions 自动部署
├── .gitignore
└── public/           # 静态资源（来自原项目，未改动）
    ├── css/          # header.css / em.css / goods-layout.css / style.css / theme.css
    ├── js/           # header.js / qrcode.min.js
    ├── img/          # 模板图片（Banner 等）
    ├── vendor/       # jquery / font-awesome / remixicon / layui
    └── favicon.ico
```

> `theme.css` 由原模板 `css/theme.php` 的默认主题色（`#2196F3 / #ff6600 / #2f69d9 / #ff9800`）
> 静态化生成，与动态版输出一致。后台"站点设置"中可修改主题主色。

## 功能范围（与原项目对应的核心闭环）

| 功能 | 路由 | 说明 |
|---|---|---|
| 首页商品列表/分类/排序/搜索 | `/?` `/?sort_id=` `/?order=` `/?q=` | 普通模式，含公告/滚动公告/分类快捷入口/筛选条，与原模板 DOM 一致 |
| 商品详情 + 多规格购买 | `/?action=goods&id=` | SKU 选择、价格联动、数量、购买字段、支付方式；一卡一密/通用卡密/虚拟服务 |
| 下单 | `POST /?action=xiadan` | 校验库存 → 计价 → 生成订单（`out_trade_no = YmdHis + 4随机`）→ 写 order/order_list/order_required |
| 支付 | `/?action=pay&out_trade_no=` | 测试支付（演示）/ 余额支付 / 易支付网关（配置后启用） |
| 自动发货 | `POST /?action=pay_submit` | 支付成功 → 一卡一密打 `sale_time`、通用/服务卡密写 `*_sale` 表 → 原子扣库存 → 订单置已完成 |
| 订单结果 | `/?action=order_result&out_trade_no=` | 卡密展示 + 一键复制/单条复制 |
| 订单查询 | `/?action=order_query` | 订单号/联系方式查询（服务端渲染结果） |
| 帮助页 | `/?action=help` | FAQ 常见问题 |
| 用户中心 | `/?action=user` | 登录（演示）/ 余额 / 个人订单 |
| RSS | `/?action=rss` | 最新商品 |
| 管理后台 | `/admin` | 仪表盘 / 商品 / 卡密 / 订单 / 分类 / 站点设置 |

## 部署方式一：GitHub Actions（推荐）

仓库 `nino-natsume/faka` 已内置 CI（`.github/workflows/deploy.yml`）：
每次 push 到 `main` 或手动触发 `Actions → Deploy to Cloudflare Workers → Run workflow` 时，
自动完成 **D1 创建（幂等）→ 初始化 schema → 灌入种子数据 → 部署 Worker**。

只需在仓库配置以下 Secrets 即可：

| Secret | 必填 | 说明 |
|---|---|---|
| `CLOUDFLARE_API_TOKEN` | 是 | Cloudflare API Token（权限： Workers Scripts: Edit、D1: Edit、Account Settings: Read） |
| `CLOUDFLARE_ACCOUNT_ID` | 是 | Cloudflare 账号 ID（右侧边栏可见） |
| `FK_SECRET` | 否 | 签名密钥，建议 32+ 位随机串 |
| `FK_ADMIN_USERNAME` | 否 | 后台用户名（默认 `admin`） |
| `FK_ADMIN_PASSWORD` | 否 | 后台密码（默认 `admin123`，**必改**） |

> `FK_*` 三个为可选：配置了就以 `--var` 注入；未配置则使用代码默认值或 Cloudflare Dashboard 已有变量。
> 首次部署会自动创建 D1 并把 ID 写回 `wrangler.toml`（请将该次改动一并提交，避免每次重复解析）。

## 部署方式二：本地 wrangler

前置：安装 [wrangler](https://developers.cloudflare.com/workers/wrangler/) 并登录 `wrangler login`。

```bash
cd d:/desktop/faka

# 1) 创建 D1 数据库
wrangler d1 create faka
# 输出中的 database_id 填入 wrangler.toml 的 database_id

# 2) 初始化数据库（可重复执行，均幂等）
wrangler d1 execute faka --file=./schema.sql
wrangler d1 execute faka --file=./seed.sql

# 3) 本地预览（可选）
wrangler dev

# 4) 部署
wrangler deploy
```

## 应用变量配置（两种方式任选）

Worker 需要三个应用变量：`SECRET`（签名密钥）、`ADMIN_USERNAME`、`ADMIN_PASSWORD`。
代码内置安全默认值（`admin / admin123`、固定 fallback 密钥），**上线前务必改为自定义值**。

> ⚠️ `wrangler.toml` 中**不再硬编码**这些变量，避免部署时覆盖你已经配置好的变量。

**方式 A：GitHub Secrets（配合 GitHub Actions）**

在仓库 `Settings → Secrets and variables → Actions` 中添加 `FK_SECRET`、
`FK_ADMIN_USERNAME`、`FK_ADMIN_PASSWORD`，部署时自动注入（见上方表格）。

**方式 B：Cloudflare Dashboard**

在 Cloudflare 控制台：`Workers & Pages → faka → Settings → Variables and Secrets`，
添加同名变量 `SECRET / ADMIN_USERNAME / ADMIN_PASSWORD`。
若同时使用 GitHub Actions 且配置了 GitHub Secrets，则 GitHub 注入的值会覆盖 Dashboard。

> 也可任选其一：只在 GitHub 配，或只在 Dashboard 配，或都不配（纯默认值）。

## 使用说明

- 前台访问域名即商城首页；演示商品数据与种子数据一致（`请勿下单`）。
- 后台地址 `/admin`，默认账号 `admin / admin123`（部署后请立即修改）。
- 支付方式：默认提供 **测试支付**（点击直接模拟支付成功并自动发货，便于验证全流程）；
  登录用户可选择**余额支付**（需先在 `dc_user` 中为账号充值余额）；
  若配置易支付网关（`dc_options._epay_config` 存 `{"api_url":"...","appid":"...","key":"..."}` JSON），
  前台自动出现微信/支付宝支付项。

## 与原 PHP 版的行为差异（重要）

1. **库存扣减时机**：原系统后台加卡密时维护 `stock`，发货不实时扣；本版在**发货成功时原子扣减**
   `dc_skus.stock` 与 `dc_goods.stock`，避免库存漂移。
2. **一卡一密的发卡校验**：下单时除 `stock` 外还会实时统计 `dc_goods_once` 未售出条数，不足即拦截。
3. **PHP 无法在 Workers 运行**：后端由 JS 重写，但前端 DOM 结构、CSS class、静态资源均取自原项目；
   多规格、价格联动、下单交互为等价实现。
4. **会员等级/分销/分店/易支付异步回调**等扩展能力未包含，如需可在此基础上继续移植
   （表结构 `schema.sql` 已覆盖核心，扩展表可增量添加）。

## 数据模型说明

核心表映射（源自原 `shujuku7777777.sql`，78 表精简为核心闭环所需）：

- 商品：`dc_sort` → `dc_goods` → `dc_skus` / `dc_goods_type` / `dc_sku_attr` / `dc_sku_value`
- 卡密：`dc_goods_once`(一卡一密, sale_time 标记售出) / `dc_goods_general`(通用) / `dc_goods_service`(虚拟服务) / `dc_stock`
- 订单：`dc_order` / `dc_order_list` / `dc_order_required`
- 站点：`dc_options` / `dc_tpl_options_data` / `dc_station`
- 用户：`dc_user` / `dc_cart` / `dc_coupon` / `dc_discount`

金额单位：与源系统一致，**分**（`dc_skus.guest_price`、`dc_order.amount` 等均为整数分）。