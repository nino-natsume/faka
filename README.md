# DCSHOP 发卡系统 (faka)

DCSHOP 多财商城发卡系统源码（PHP + MySQL），含前台商城、后台管理、多支付插件。随仓附赠 Debian 一键部署/卸载脚本。

## 目录结构

```
├── index.php              前台入口
├── admin/                 后台管理
├── user/                  用户中心
├── include/               核心库（模型/控制器/服务）
├── content/               模板与插件（前台/用户/博客模板、支付插件）
├── deploy.sh              Debian 11/12 部署脚本
├── deploy13.sh            Debian 13 一键部署脚本（推荐）
├── uninstall.sh           卸载脚本
└── *.sql                  数据库导出备份
```

## 环境要求

- 操作系统：Debian 13 (trixie) x86_64（其余版本自动降级适配）
- 配置建议：1 核 / 512MB / 30GB 以上
- 软件栈：Nginx + PHP 8.2（SURY 源）+ MariaDB（脚本自动安装）

## 一键部署（推荐）

```bash
# 1. 将整个源码目录上传到服务器（含 deploy13.sh、index.php、*.sql）
scp -r faka/ root@服务器IP:/root/

# 2. 进入目录执行（域名可省略）
cd /root/faka
bash deploy13.sh 你的域名.com
```

部署完成后访问：

- 前台：`http://你的域名.com/`
- 后台：`http://你的域名.com/admin/`

### 启用 HTTPS（Cloudflare 源站证书）

脚本支持通过 Cloudflare API 自动申请 15 年源站证书，需要两个环境变量：

```bash
CF_API_TOKEN=你的Token CF_ZONE_ID=你的ZoneID bash deploy13.sh 你的域名.com
```

要求：

- 域名已在 Cloudflare 托管并开启代理（橙色云朵）
- API Token 需具备 `Zone > SSL and Certificates > Edit` 权限
- 申请成功后到 Cloudflare 控制台将 SSL/TLS 模式设为 `Full (strict)`

不传凭据时自动仅部署 HTTP，不影响主流程。

### 数据库说明

- 默认库名/用户：`dcshop`
- 密码：未指定则**自动随机生成**，部署完成信息中会显示
- 可通过环境变量覆盖：`DB_NAME=xxx DB_USER=xxx DB_PASS=xxx bash deploy13.sh 域名`

### 管理员账号

数据库导入后管理员位于 `dc_user` 表（`uid=1000` 且 `role='admin'` 的记录）。密码为 bcrypt 哈希，无法反查，请在服务器上重置：

```bash
NEWHASH=$(php -r "echo password_hash('你的新密码', PASSWORD_BCRYPT), PHP_EOL;")
mysql dcshop -e "UPDATE dc_user SET password='$NEWHASH' WHERE uid=1000 AND role='admin';"
```

## 卸载

```bash
sh uninstall.sh
# 按提示输入 YES 确认
```

## 安全说明

- `config.php`（含数据库密码）由部署脚本生成，已通过 `.gitignore` 排除，不进仓库
- 部署脚本自动排除 `.sql`/`.zip`/`.sh`/`.txt` 至站点目录，防止备份文件泄露
- Nginx 已配置禁止执行缓存/上传/模板目录中的 PHP，防 WebShell
- 部署后请务必修改管理员密码

## 运行截图 / 说明文档

见 `README.txt`（系统自带）。