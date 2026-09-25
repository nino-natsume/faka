# ACG_Faka-cloud

基于 Acg-faka 改造的 Cloudflare 版本。
前台样式与原版完全一致（复用原项目全部静态资源），后端由 **Workers + D1** 实现
商品浏览、下单、支付、自动发货、订单查询与管理后台，前台/后台/订单闭环开箱即用。

## 纯浏览器部署（Cloudflare Pages，零命令行 · 推荐）

> 仓库中的 `public/_worker.js` 已把全部后端逻辑打包为单文件，配合 `public/`
> 静态资源即可直接传上 Cloudflare Pages。全程浏览器操作，无需安装任何东西。

1. 打开 [Cloudflare dashboard](https://dash.cloudflare.com) 并登录。
2. **创建数据库**：左侧 `D1` → `Create database` → 名称填 `faka` → 创建。
3. **初始化数据库**：进入刚创建的 `faka` → `Console`（或 `Import`），
   依次粘贴执行 [schema.sql](schema.sql) 与 [seed.sql](seed.sql) 的内容（均可重复执行）。
4. **上传站点**：`Workers & Pages` → `Create` → `Pages` → `Upload assets` →
   把本仓库的 `public/` 文件夹拖入上传区 → `Deploy`（完成后得到 `https://xxx.pages.dev` 域名）。
5. **绑定数据库**：在刚创建的 Pages 项目 → `Settings` → `Functions` → `Bindings` →
   `Add binding` → 类型 `D1 Database` → 选择 `faka` → 变量名填 `DB` → `Save`。
6. **配置变量**：`Settings` → `Environment variables` → `Add`，
   添加三项（类型选 Secret）：
   - `SECRET`：一段 32+ 位随机串
   - `ADMIN_USERNAME`：后台用户名（默认 `admin`）
   - `ADMIN_PASSWORD`：后台密码（默认 `admin123`，**必改**）
7. **完成**：访问 `https://xxx.pages.dev` 查看商城；`/admin` 进入后台。
   再次部署只需重复第 4 步（更新代码后重新上传）。

> ✅ 修改 `worker.js` / `admin.js` / `lib.js` **无需手动操作**：代码 push 到 `main`
> 后，`Build Pages Bundle` 工作流会自动重新打包 `public/_worker.js` 并提交。
> 浏览器上传前确认使用的是最新 `main` 分支代码即可（可在本机执行 `git pull`）。

## 快速部署（GitHub Actions）

在仓库 `Settings → Secrets and variables → Actions` 配置：

| Secret | 必填 | 说明 |
|---|---|---|
| `CLOUDFLARE_API_TOKEN` | 是 | Cloudflare API Token（My Profile → API Tokens 创建）。<br>所需权限：<br>Account › Workers Scripts › Edit<br>Account › D1 › Edit<br>Account › Account Settings › Read<br>以下可选<br>Zone › Zone › Read<br>Zone › Workers Routes › Edit |
| `CLOUDFLARE_ACCOUNT_ID` | 是 | Cloudflare Account ID（Cloudflare 账户ID） |
| `FK_SECRET` | 否 | 签名密钥（建议 32+ 位随机串） |
| `FK_ADMIN_USERNAME` | 否 | 后台用户名（默认 `admin`） |
| `FK_ADMIN_PASSWORD` | 否 | 后台密码（默认 `admin123`，**必改**） |

> 首次部署会自动创建 D1 并把 `database_id` 写回 `wrangler.toml`

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
