# DCSHOP 发卡系统 - Cloudflare 部署版

基于原项目（DCSHOP / acg-faka PHP 发卡系统）改造的 Cloudflare 版本。
前台样式与原版完全一致（复用原项目全部静态资源），后端由 **Workers + D1** 实现
商品浏览、下单、支付、自动发货、订单查询与管理后台，前台/后台/订单闭环开箱即用。

## 快速部署（GitHub Actions）

仓库内置 CI（`.github/workflows/deploy.yml`），**仅手动触发**：
`Actions → Deploy to Cloudflare Workers → Run workflow`，自动完成
D1 创建（幂等）→ 初始化 → 灌种子数据 → 部署。

在仓库 `Settings → Secrets and variables → Actions` 配置：

| Secret | 必填 | 说明 |
|---|---|---|
| `CLOUDFLARE_API_TOKEN` | 是 | Cloudflare API Token（My Profile → API Tokens 创建）。所需权限：Account › Workers Scripts › Edit、Account › D1 › Edit、Account › Account Settings › Read（如需绑定自定义域名，另加 Zone › Zone › Read 与 Zone › Workers Routes › Edit） |
| `CLOUDFLARE_ACCOUNT_ID` | 是 | Cloudflare Account ID（Cloudflare dashboard 右侧边栏） |
| `FK_SECRET` | 否 | 签名密钥（建议 32+ 位随机串） |
| `FK_ADMIN_USERNAME` | 否 | 后台用户名（默认 `admin`） |
| `FK_ADMIN_PASSWORD` | 否 | 后台密码（默认 `admin123`，**必改**） |

> 首次部署会自动创建 D1 并把 `database_id` 写回 `wrangler.toml`，请提交该改动。

## 本地部署

```bash
npm i -g wrangler && wrangler login
wrangler d1 create faka              # 把输出的 database_id 填入 wrangler.toml
wrangler d1 execute faka --file=./schema.sql
wrangler d1 execute faka --file=./seed.sql
wrangler dev                          # 本地预览
wrangler deploy
```

## 应用变量

Worker 使用 `SECRET`、`ADMIN_USERNAME`、`ADMIN_PASSWORD` 三个变量（代码内置默认值）。
有两种配置方式，任选其一：GitHub Secrets（`FK_*` 前缀，部署时自动注入）或
Cloudflare dashboard 的 `Variables and Secrets`（同名变量）。`wrangler.toml` 不做硬编码。
上线前务必修改默认后台密码。

## 使用说明

- 前台即商城首页，自带演示商品（种子数据，`请勿下单`）。
- 后台 `/admin`，默认账号 `admin / admin123`。
- 支付：默认**测试支付**（模拟支付成功并自动发货）；登录用户可选**余额支付**；
  配置易支付网关后出现微信/支付宝。
- 与原版差异：发货时原子扣减库存、实时校验一卡一密库存；会员等级/分销/分店等扩展未包含。