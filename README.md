# DCSHOP 发卡系统 (faka)

**注意：已锁定版本，严禁更新，若更新将导致无法使用！！**

**本服务不提供技术支持，仅提供代码源码**

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

## 部署方式

本服务提供两种部署方式，详情点击查看

[服务器部署](https://github.com/nino-natsume/faka/blob/main/server.md) [代码部署](https://github.com/nino-natsume/faka/blob/main/code.md)

