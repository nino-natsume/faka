-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2026-06-16 12:37:42
-- 服务器版本： 5.7.44-log
-- PHP 版本： 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `shujuku7777777`
--

-- --------------------------------------------------------

--
-- 表的结构 `dc_admin_group`
--

CREATE TABLE `dc_admin_group` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `menu_permissions` text COLLATE utf8mb4_unicode_ci,
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_admin_group`
--

INSERT INTO `dc_admin_group` (`id`, `name`, `menu_permissions`, `create_time`, `update_time`) VALUES
(1, '默认后台组', '[\"menu-dashboard\",\"menu-goods-list\",\"menu-sort-list\",\"menu-sku-list\",\"menu-stock-list\",\"menu-goods-price-rule\",\"menu-goods-recycle\",\"menu-order-goods\",\"menu-order-recharge\",\"menu-user-default\",\"menu-user-recycle\",\"menu-user-member\",\"menu-user-withdraw\",\"menu-user-log\",\"menu-user-recharge-card\",\"menu-blog-list\",\"menu-blog-comment\",\"menu-blog-sort\",\"menu-blog-page\",\"menu-blog-widgets\",\"menu-blog-link\",\"menu-station-lists\",\"menu-station-level\",\"menu-template\",\"menu-plugin-all\",\"menu-plugin-on\",\"menu-plugin-off\",\"menu-plugin-update\",\"menu-store-list\",\"menu-store-recharge\",\"menu-setting\",\"menu-shop\",\"menu-manage-account\",\"menu-resources\",\"menu-calibrate\",\"menu-upgrade\",\"menu-auth\",\"menu-repair\"]', 0, 1781583318);

-- --------------------------------------------------------

--
-- 表的结构 `dc_aftersale`
--

CREATE TABLE `dc_aftersale` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单ID',
  `order_list_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单子项ID',
  `out_trade_no` varchar(64) NOT NULL DEFAULT '' COMMENT '订单编号',
  `goods_title` varchar(255) NOT NULL DEFAULT '' COMMENT '商品名称',
  `type` varchar(32) NOT NULL DEFAULT 'other' COMMENT '售后类型',
  `reason` text COMMENT '问题描述',
  `contact` varchar(255) NOT NULL DEFAULT '' COMMENT '联系方式',
  `images` text COMMENT '补充图片JSON',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '申请时间',
  `handle_time` int(11) DEFAULT NULL COMMENT '处理时间',
  `handle_remark` text COMMENT '处理备注',
  `reopen_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '重开状态:0无,1待审核,2已批准,3已拒绝',
  `reopen_reason` text COMMENT '重开申请理由',
  `reopen_time` int(11) DEFAULT NULL COMMENT '重开申请时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='售后订单表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_aftersale_chat`
--

CREATE TABLE `dc_aftersale_chat` (
  `id` int(11) NOT NULL,
  `aftersale_id` int(11) NOT NULL DEFAULT '0' COMMENT '售后ID',
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单ID',
  `order_list_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单子项ID',
  `out_trade_no` varchar(64) NOT NULL DEFAULT '' COMMENT '订单编号',
  `sender_type` varchar(16) NOT NULL DEFAULT 'user' COMMENT '发送者类型',
  `content` text COMMENT '消息内容',
  `is_recalled` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已撤回',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '发送时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='售后聊天记录表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_attachment`
--

CREATE TABLE `dc_attachment` (
  `aid` int(11) UNSIGNED NOT NULL COMMENT '资源文件表',
  `alias` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '资源别名',
  `author` int(11) UNSIGNED NOT NULL DEFAULT '1' COMMENT '作者UID',
  `sortid` int(11) NOT NULL DEFAULT '0' COMMENT '分类ID',
  `blogid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '文章ID（已废弃）',
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件名',
  `filesize` int(11) NOT NULL DEFAULT '0' COMMENT '文件大小',
  `filepath` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件路径',
  `addtime` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `width` int(11) NOT NULL DEFAULT '0' COMMENT '图片宽度',
  `height` int(11) NOT NULL DEFAULT '0' COMMENT '图片高度',
  `mimetype` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件mime类型',
  `thumfor` int(11) NOT NULL DEFAULT '0' COMMENT '缩略图的原资源ID（已废弃）',
  `download_count` bigint(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '下载次数'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_attachment`
--

INSERT INTO `dc_attachment` (`aid`, `alias`, `author`, `sortid`, `blogid`, `filename`, `filesize`, `filepath`, `addtime`, `width`, `height`, `mimetype`, `thumfor`, `download_count`) VALUES
(1, 'DeHaHvIq9sZCIFQJ', 1000, 0, 0, '20260516172516fc8809029.jpg', 214985, '../content/uploadfile/202606/thum-d9d31781582809.jpg', 1781582809, 1024, 1024, 'image/jpeg', 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `dc_authorization`
--

CREATE TABLE `dc_authorization` (
  `emkey` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domain` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_balance_log`
--

CREATE TABLE `dc_balance_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) DEFAULT NULL,
  `plus` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `update_before` decimal(10,2) DEFAULT NULL,
  `money` decimal(10,2) DEFAULT '0.00',
  `description` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `create_time` bigint(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_blog`
--

CREATE TABLE `dc_blog` (
  `gid` int(11) UNSIGNED NOT NULL COMMENT '文章表',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文章标题',
  `date` bigint(20) NOT NULL COMMENT '发布时间',
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文章内容',
  `excerpt` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文章摘要',
  `cover` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '封面图',
  `alias` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文章别名',
  `author` int(11) NOT NULL DEFAULT '1' COMMENT '作者UID',
  `sortid` int(11) NOT NULL DEFAULT '-1' COMMENT '分类ID',
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'blog' COMMENT '文章OR页面',
  `views` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '阅读量',
  `comnum` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '评论数量',
  `attnum` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '附件数量（已废弃）',
  `top` enum('n','y') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'n' COMMENT '置顶',
  `sortop` enum('n','y') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'n' COMMENT '分类置顶',
  `hide` enum('n','y') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'n' COMMENT '草稿y',
  `checked` enum('n','y') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'y' COMMENT '文章是否审核',
  `allow_remark` enum('n','y') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'y' COMMENT '允许评论y',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '访问密码',
  `template` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '模板',
  `tags` text COLLATE utf8mb4_unicode_ci COMMENT '标签',
  `link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文章跳转链接',
  `feedback` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'audit feedback'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_blog`
--

INSERT INTO `dc_blog` (`gid`, `title`, `date`, `content`, `excerpt`, `cover`, `alias`, `author`, `sortid`, `type`, `views`, `comnum`, `attnum`, `top`, `sortop`, `hide`, `checked`, `allow_remark`, `password`, `template`, `tags`, `link`, `feedback`) VALUES
(1, 'DCSHOP 多财商城系统介绍 — 一站式自动发卡解决方案', 1781583107, '<div style=\"padding: 18px; line-height: 1.85; color: #333; font-size: 15px;\">\n<div style=\"background: linear-gradient(90deg,rgba(228,171,59,.20),transparent 28%,rgba(104,122,69,.14)),#fffaf0; color: #2d261f; padding: 30px 24px; border: 2px solid #2d261f; border-radius: 28px 20px 30px 22px; margin-bottom: 24px; text-align: center; box-shadow: 7px 8px 0 rgba(45,38,31,.14);\">\n<h1 style=\"margin: 0 0 10px; font-size: 28px; font-weight: bold; letter-spacing: 1px; color: #2d261f;\">DCSHOP&nbsp;小K网测试</h1>\n<div style=\"color: #665746; font-size: 15px; line-height: 1.6;\">一站式自动发卡解决方案 &middot; 让虚拟商品销售简单可靠</div>\n<div style=\"display: inline-block; margin-top: 14px; padding: 5px 12px; border: 1px dashed rgba(45,38,31,.35); border-radius: 999px; color: #8f4527; font-size: 13px; background: rgba(246,236,216,.68);\">https://dcshop.xzsc.cc/</div>\n</div>\n<h2 style=\"font-size: 20px; margin: 24px 0 12px; color: #222; border-left: 4px solid #2196F3; padding-left: 10px;\">什么是 DCSHOP？xkwo.com</h2>\n<p style=\"margin: 0 0 14px;\">DCSHOP 多财商城是一款专业的<strong>自动发卡商城系统</strong>，专为虚拟商品、数字内容、会员服务、游戏点卡等业务量身打造。系统覆盖<strong>商品发布、订单管理、卡密自动发货、会员体系、多级分销、分店开通</strong>等完整电商能力，开箱即用，三分钟上线一个属于你自己的发卡商城。</p>\n<p style=\"margin: 0 0 14px;\">无论你是个人卖家、工作室还是想搭建自助代理平台的团队，DCSHOP 都提供从前端模板、后台管理、营销玩法到分销代理的完整工具链。</p>\n<h2 style=\"font-size: 20px; margin: 24px 0 12px; color: #222; border-left: 4px solid #ff6600; padding-left: 10px;\">八大核心能力</h2>\n<div style=\"display: grid; grid-template-columns: repeat(auto-fit,minmax(280px,1fr)); gap: 12px; margin: 0 0 18px;\">\n<div style=\"background: #f7faff; border-left: 3px solid #2196F3; padding: 14px 16px; border-radius: 8px;\">\n<div style=\"font-weight: 600; color: #1976d2; margin-bottom: 6px; font-size: 15px;\">1. 自动发卡</div>\n<div style=\"color: #555; font-size: 13px; line-height: 1.7;\">支持<strong>一卡一密 / 固定卡密 / 虚拟服务 / 接口对接</strong>四种发货模式，覆盖所有数字商品业务场景，下单即时发货。</div>\n</div>\n<div style=\"background: #fff7f0; border-left: 3px solid #ff6600; padding: 14px 16px; border-radius: 8px;\">\n<div style=\"font-weight: 600; color: #e65100; margin-bottom: 6px; font-size: 15px;\">2. 会员体系</div>\n<div style=\"color: #555; font-size: 13px; line-height: 1.7;\"><strong>内置 8 档现成会员等级方案，不满足需求可自定义无限添加，灵活搭建会员体系 </strong> + 等级价 + 自动加价规则 + 独立会员定价，让老客户拿货成本更低，复购率自然上升。</div>\n</div>\n<div style=\"background: #fff5f7; border-left: 3px solid #e91e63; padding: 14px 16px; border-radius: 8px;\">\n<div style=\"font-weight: 600; color: #ad1457; margin-bottom: 6px; font-size: 15px;\">3. 分店系统</div>\n<div style=\"color: #555; font-size: 13px; line-height: 1.7;\"><strong>标配 5 档分店权限等级，不够随时自行增设</strong>，支持<strong>独立域名 / 路径后缀 / 二级域名</strong>三种部署，分店可继承商品、独立结算、自动升级。</div>\n</div>\n<div style=\"background: #f0fbff; border-left: 3px solid #00bcd4; padding: 14px 16px; border-radius: 8px;\">\n<div style=\"font-weight: 600; color: #00838f; margin-bottom: 6px; font-size: 15px;\">4. 营销玩法</div>\n<div style=\"color: #555; font-size: 13px; line-height: 1.7;\"><strong>每件优惠 + 订单优惠 + 订单折扣</strong>三种营销叠加，覆盖满减、阶梯价、批发优惠各类场景。</div>\n</div>\n<div style=\"background: #f3f4ff; border-left: 3px solid #3f51b5; padding: 14px 16px; border-radius: 8px;\">\n<div style=\"font-weight: 600; color: #283593; margin-bottom: 6px; font-size: 15px;\">5. 多端模板</div>\n<div style=\"color: #555; font-size: 13px; line-height: 1.7;\">PC + 移动端 <strong>一套模板自适应</strong>，支持深色模式、轮播图、分类图标、商品卡片样式 DIY 配置。</div>\n</div>\n<div style=\"background: #fff5f0; border-left: 3px solid #ff5722; padding: 14px 16px; border-radius: 8px;\">\n<div style=\"font-weight: 600; color: #bf360c; margin-bottom: 6px; font-size: 15px;\">6. 售后体系</div>\n<div style=\"color: #555; font-size: 13px; line-height: 1.7;\">售后插件 + 卡密补发 + 退款 + 在线客服 QQ/微信，全链路保障用户体验。</div>\n</div>\n<div style=\"background: #f5f3ee; border-left: 3px solid #795548; padding: 14px 16px; border-radius: 8px;\">\n<div style=\"font-weight: 600; color: #4e342e; margin-bottom: 6px; font-size: 15px;\">7. 数据看板</div>\n<div style=\"color: #555; font-size: 13px; line-height: 1.7;\">订单 / 销售额 / 用户 / 商品 多维度数据统计，业务情况一目了然。</div>\n</div>\n<div style=\"background: #f1f4f5; border-left: 3px solid #607d8b; padding: 14px 16px; border-radius: 8px;\">\n<div style=\"font-weight: 600; color: #37474f; margin-bottom: 6px; font-size: 15px;\">8. 多级分销</div>\n<div style=\"color: #555; font-size: 13px; line-height: 1.7;\"><strong>无限级分销</strong> + 等级返佣 + 升级奖励 + 分销返点，快速裂变拉新。</div>\n</div>\n</div>\n<h2 style=\"font-size: 20px; margin: 24px 0 12px; color: #222; border-left: 4px solid #4caf50; padding-left: 10px;\">四种商品类型，覆盖所有发卡场景</h2>\n<table style=\"width: 100%; border-collapse: collapse; margin: 0 0 18px; font-size: 14px;\">\n<thead>\n<tr style=\"background: #f5f5f5;\">\n<th style=\"padding: 10px; border: 1px solid #e0e0e0; text-align: left;\">类型</th>\n<th style=\"padding: 10px; border: 1px solid #e0e0e0; text-align: left;\">适用场景</th>\n<th style=\"padding: 10px; border: 1px solid #e0e0e0; text-align: left;\">典型商品</th>\n</tr>\n</thead>\n<tbody>\n<tr>\n<td style=\"padding: 10px; border: 1px solid #e0e0e0;\"><strong>一卡一密</strong></td>\n<td style=\"padding: 10px; border: 1px solid #e0e0e0;\">每张卡密只能用一次，库存递减</td>\n<td style=\"padding: 10px; border: 1px solid #e0e0e0;\">游戏激活码、兑换码</td>\n</tr>\n<tr style=\"background: #fafafa;\">\n<td style=\"padding: 10px; border: 1px solid #e0e0e0;\"><strong>固定卡密</strong></td>\n<td style=\"padding: 10px; border: 1px solid #e0e0e0;\">所有买家收到同一份内容</td>\n<td style=\"padding: 10px; border: 1px solid #e0e0e0;\">教程下载、文档资料</td>\n</tr>\n<tr>\n<td style=\"padding: 10px; border: 1px solid #e0e0e0;\"><strong>虚拟服务</strong></td>\n<td style=\"padding: 10px; border: 1px solid #e0e0e0;\">人工服务，无需卡密</td>\n<td style=\"padding: 10px; border: 1px solid #e0e0e0;\">代下单、咨询服务</td>\n</tr>\n<tr style=\"background: #fafafa;\">\n<td style=\"padding: 10px; border: 1px solid #e0e0e0;\"><strong>接口类型</strong></td>\n<td style=\"padding: 10px; border: 1px solid #e0e0e0;\">下单后回调上游接口出码</td>\n<td style=\"padding: 10px; border: 1px solid #e0e0e0;\">话费充值、油卡充值</td>\n</tr>\n</tbody>\n</table>\n<h2 style=\"font-size: 20px; margin: 24px 0 12px; color: #222; border-left: 4px solid #9c27b0; padding-left: 10px;\">会员等级 + 分店体系</h2>\n<p style=\"margin: 0 0 12px;\">DCSHOP 内置 <strong>内置 8 档现成会员等级方案，不满足需求可自定义无限添加，灵活搭建会员体系 </strong>（普通用户 / 铜牌 / 银牌 / 金牌 / 钻石 / 黑卡 / 至尊 VIP / 核心合伙人），每档对应不同的会员价、加价比例、提货成本和升级条件，支持<strong>消费金额、直推人数、团队规模</strong>等多维度自动升级。</p>\n<p style=\"margin: 0 0 12px;\">除会员体系外，系统还提供 <strong>标配 5 档分店权限等级，不够随时自行增设</strong>（免费版 / 入门版 / 标准版 / 专业版 / 旗舰版），每档对应不同的功能权限（独立域名 / 商品价格自定义 / 模板更换 / 充值开关），分店可<strong>按累计销售额、订单量、运营天数、下级分店数</strong>自动升级，让代理体系自我成长。</p>\n<h2 style=\"font-size: 20px; margin: 24px 0 12px; color: #222; border-left: 4px solid #ff5722; padding-left: 10px;\">开箱即用的默认配置</h2>\n<ul style=\"padding-left: 0; list-style: none; margin: 0 0 18px;\">\n<li style=\"padding: 6px 0;\">默认模板已配置：首页轮播图、12 个分类图标、4 个演示商品（已上架展示）、首页公告、首页置顶</li>\n<li style=\"padding: 6px 0;\">客服信息已预填：QQ <code>191955552</code>，售前售后链接已绑定 <code>https://dcshop.xzsc.cc/</code></li>\n<li style=\"padding: 6px 0;\">默认会员等级 8 档已就位，分店等级 5 档已就位，开通即可使用</li>\n<li style=\"padding: 6px 0;\">移动端顶部导航默认显示「搜索 + 买家帮助」，PC 端默认显示「搜索 + 个人中心 + 买家帮助」</li>\n<li style=\"padding: 6px 0;\">商品列表默认 PC 端每行 5 个卡片、移动端每屏 5&times;2 分类图标 + 横向滑动分页</li>\n</ul>\n<h2 style=\"font-size: 20px; margin: 24px 0 12px; color: #222; border-left: 4px solid #607d8b; padding-left: 10px;\">建议下一步操作</h2>\n<ol style=\"padding-left: 22px; margin: 0 0 18px;\">\n<li style=\"padding: 4px 0;\">登录后台：<strong>商品管理</strong> 删除 4 个演示商品后发布你自己的商品</li>\n<li style=\"padding: 4px 0;\">登录后台：<strong>站点配置 &rarr; 站点信息</strong> 修改商城名称、Logo、SEO 信息</li>\n<li style=\"padding: 4px 0;\">登录后台：<strong>支付管理</strong> 配置至少一种支付通道（微信 / 支付宝 / 在线收款）</li>\n<li style=\"padding: 4px 0;\">登录前台：<strong>买家帮助</strong> 修改客服 QQ/微信、售后须知</li>\n<li style=\"padding: 4px 0;\">登录后台：<strong>设计</strong> 调整模板配色、轮播图、分类图标，打造专属外观</li>\n</ol>\n<div style=\"background: linear-gradient(135deg,#fff8e1,#fff3cd); border: 1px solid #ffe082; border-radius: 10px; padding: 18px; margin: 18px 0;\">\n<div style=\"font-weight: 600; color: #e65100; font-size: 16px; margin-bottom: 8px;\">需要帮助？</div>\n<div style=\"color: #555; font-size: 14px; line-height: 1.8;\">官方主页：<a style=\"color: #2f69d9; text-decoration: none; font-weight: 500;\" href=\"https://dcshop.xzsc.cc/\" target=\"_blank\" rel=\"noopener\">https://dcshop.xzsc.cc/</a><br>DCSHOP官方交流群：<strong style=\"color: #222;\">649146439</strong><br>欢迎反馈使用建议、报告 BUG 或申请功能定制</div>\n</div>\n<p style=\"text-align: center; color: #999; font-size: 13px; margin: 24px 0 0;\">&mdash; 感谢选择 DCSHOP 多财商城，祝你生意兴隆 &mdash;</p>\n</div>', 'DCSHOP 多财商城是一款专业的自动发卡商城系统，覆盖商品发布、订单管理、卡密自动发货、会员体系（8 档）、多级分销、分店系统（5 档）等完整电商能力。本文一文读懂 DCSHOP 的核心能力、四种商品类型、营销玩法以及推荐的下一步操作。', '../content/blog_templates/default/images/img3.png', 'dcshop-intro', 1000, 5, 'blog', 1, 9, 0, 'y', 'y', 'n', 'y', 'y', '', '', '1,4,3,2,5', '', '');

-- --------------------------------------------------------

--
-- 表的结构 `dc_blog_fields`
--

CREATE TABLE `dc_blog_fields` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `gid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `field_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `field_value` longtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_blog_navi`
--

CREATE TABLE `dc_blog_navi` (
  `id` int(11) NOT NULL,
  `naviname` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(512) NOT NULL DEFAULT '',
  `newtab` char(1) NOT NULL DEFAULT 'n',
  `hide` char(1) NOT NULL DEFAULT 'n',
  `taxis` int(11) NOT NULL DEFAULT '0',
  `pid` int(11) NOT NULL DEFAULT '0',
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `type_id` int(11) NOT NULL DEFAULT '0',
  `isdefault` char(1) NOT NULL DEFAULT 'n',
  `naviicon` varchar(100) NOT NULL DEFAULT '' COMMENT '导航图标'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_blog_navi`
--

INSERT INTO `dc_blog_navi` (`id`, `naviname`, `url`, `newtab`, `hide`, `taxis`, `pid`, `type`, `type_id`, `isdefault`, `naviicon`) VALUES
(1, '博客首页', '', 'n', 'n', 300, 0, 1, 0, 'n', 'ri-home-smile-line'),
(2, '商城首页', '/', 'n', 'n', 200, 0, 0, 0, 'n', 'ri-store-2-line'),
(3, 'DCSHOP多财商城官方默认链接', 'https://dcshop.xzsc.cc/', 'y', 'n', 100, 0, 0, 0, 'n', 'ri-links-line');

-- --------------------------------------------------------

--
-- 表的结构 `dc_cart`
--

CREATE TABLE `dc_cart` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID',
  `is_local` tinyint(1) DEFAULT '0' COMMENT '是否已本地身份加入购物车',
  `eb_local` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '本地身份标识',
  `user_id` int(10) DEFAULT NULL COMMENT '用户ID',
  `goods_id` int(10) DEFAULT NULL COMMENT '商品ID',
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int(10) DEFAULT '0',
  `create_time` bigint(16) DEFAULT NULL,
  `update_time` bigint(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_comment`
--

CREATE TABLE `dc_comment` (
  `cid` int(11) UNSIGNED NOT NULL COMMENT '评论表',
  `gid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '文章ID',
  `pid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父级评论ID',
  `top` enum('n','y') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'n' COMMENT '置顶',
  `poster` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '发布人昵称',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '发布人UID',
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '评论内容',
  `mail` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'email',
  `url` varchar(75) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'homepage',
  `ip` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'ip address',
  `agent` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'user agent',
  `hide` enum('n','y') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'n' COMMENT '是否审核',
  `date` bigint(20) NOT NULL COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_comment`
--

INSERT INTO `dc_comment` (`cid`, `gid`, `pid`, `top`, `poster`, `uid`, `comment`, `mail`, `url`, `ip`, `agent`, `hide`, `date`) VALUES
(1, 1, 0, 'n', '代码猫', 0, '刚装完就能看到演示商品和博客，像是打开了一家已经摆好货架的小店。', 'cat@example.com', '', '', 'DCSHOP Demo Comment', 'n', 1781520389),
(2, 1, 0, 'n', '夜市店长', 0, '自动发卡最怕半夜出单没人处理，这个系统表示：老板睡吧，我来值夜班。', 'shop@example.com', '', '', 'DCSHOP Demo Comment', 'n', 1781521089),
(3, 1, 0, 'n', '像素旅人', 0, '分类图标和轮播默认就有，比空白首页友好多了。第一眼至少知道从哪里开始改。', 'pixel@example.com', '', '', 'DCSHOP Demo Comment', 'n', 1781521689),
(4, 1, 3, 'n', 'DCSHOP小助手', 1000, '默认数据只是引路牌，正式上线前记得替换商品、客服和支付配置。', 'admin@example.com', 'https://dcshop.xzsc.cc/', '', 'DCSHOP Demo Comment', 'n', 1781521989),
(5, 1, 0, 'n', '分店船长', 0, '分店系统有点像给代理一人发一艘小船，卖得好还能自动升级成大船。', 'captain@example.com', '', '', 'DCSHOP Demo Comment', 'n', 1781522589),
(6, 1, 5, 'n', '懒人运营', 0, '这个比手动给代理改权限省心，适合我这种只想喝茶看订单的人。', 'lazy@example.com', '', '', 'DCSHOP Demo Comment', 'n', 1781522889),
(7, 1, 0, 'n', '运营脑洞', 0, '看到会员 8 档和分店 5 档，我脑子里已经开始自动生成运营方案了。', 'idea@example.com', '', '', 'DCSHOP Demo Comment', 'n', 1781523689),
(8, 1, 0, 'n', '清单控', 0, '建议官方再来一篇：从零到第一单的 10 分钟开店清单。', 'todo@example.com', '', '', 'DCSHOP Demo Comment', 'n', 1781524289),
(9, 1, 8, 'n', 'DCSHOP小助手', 1000, '收到，下一篇就安排开店清单，把新手要改的地方一次讲清楚。', 'admin@example.com', 'https://dcshop.xzsc.cc/', '', 'DCSHOP Demo Comment', 'n', 1781524589);

-- --------------------------------------------------------

--
-- 表的结构 `dc_coupon`
--

CREATE TABLE `dc_coupon` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT '优惠券名称',
  `code` varchar(50) NOT NULL COMMENT '优惠码',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '类型：1=固定金额 2=折扣比例',
  `value` decimal(10,2) NOT NULL COMMENT '优惠值（金额或折扣）',
  `min_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '最低消费金额',
  `max_discount` decimal(10,2) DEFAULT NULL COMMENT '最大优惠金额（折扣券用）',
  `total_count` int(11) NOT NULL DEFAULT '0' COMMENT '发放总量（0=不限）',
  `used_count` int(11) NOT NULL DEFAULT '0' COMMENT '已使用数量',
  `per_user_limit` int(11) NOT NULL DEFAULT '1' COMMENT '每人限用次数',
  `user_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '用户类型：0=全部 1=新用户 2=老用户 3=会员',
  `goods_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '商品范围：0=全部 1=指定商品 2=指定分类',
  `goods_ids` text COMMENT '指定商品ID（逗号分隔）',
  `time_limit` int(11) DEFAULT '0' COMMENT '限时时间（分钟），0表示不限时',
  `first_check_time` int(11) DEFAULT NULL COMMENT '第一次验证时间',
  `category_ids` text COMMENT '指定分类ID（逗号分隔）',
  `start_time` int(11) DEFAULT NULL COMMENT '开始时间',
  `end_time` int(11) DEFAULT NULL COMMENT '结束时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：0=禁用 1=启用',
  `create_time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='优惠券表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_coupon_log`
--

CREATE TABLE `dc_coupon_log` (
  `id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL COMMENT '优惠券ID',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `order_id` int(11) DEFAULT NULL COMMENT '订单ID',
  `order_no` varchar(50) DEFAULT NULL COMMENT '订单号',
  `discount_amount` decimal(10,2) NOT NULL COMMENT '优惠金额',
  `client_ip` varchar(50) DEFAULT NULL COMMENT 'IP地址',
  `create_time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='优惠券使用记录' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_deliver`
--

CREATE TABLE `dc_deliver` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) DEFAULT NULL,
  `order_list_id` int(10) DEFAULT NULL,
  `content` text CHARACTER SET utf8,
  `create_time` bigint(16) DEFAULT NULL,
  `delete_time` bigint(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_discount`
--

CREATE TABLE `dc_discount` (
  `goods_id` int(10) NOT NULL,
  `sku` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `quantity` int(10) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=每件优惠(元) 2=订单优惠(元) 3=订单折扣(折,0-100)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_docking_category_map`
--

CREATE TABLE `dc_docking_category_map` (
  `id` int(10) UNSIGNED NOT NULL,
  `source_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '货源站ID',
  `source_cid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '货源站分类ID',
  `local_cid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '本地分类ID',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_docking_goods`
--

CREATE TABLE `dc_docking_goods` (
  `id` int(10) UNSIGNED NOT NULL,
  `goods_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '本地商品ID',
  `source_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '货源站ID',
  `source_goods_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '货源站商品ID',
  `source_goods_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '货源站商品名称',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `price_locked` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排除价格同步 1=排除'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_docking_sale`
--

CREATE TABLE `dc_docking_sale` (
  `id` int(10) UNSIGNED NOT NULL,
  `goods_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '本地商品ID',
  `order_list_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '子订单ID',
  `sku` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '规格',
  `content` text COLLATE utf8mb4_unicode_ci COMMENT '卡密内容',
  `num` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '数量',
  `source_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '货源站ID',
  `source_order_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '货源站订单ID',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_docking_sources`
--

CREATE TABLE `dc_docking_sources` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '货源站名称',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '货源站地址(末尾无斜杠)',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '货源站用户ID',
  `api_key` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '货源站API密钥',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用',
  `last_sync` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后同步时间',
  `auto_sync` tinyint(1) NOT NULL DEFAULT '1' COMMENT '自动同步',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `update_time` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_free_claim_log`
--

CREATE TABLE `dc_free_claim_log` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID',
  `ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '领取IP',
  `goods_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `order_id` int(10) NOT NULL DEFAULT '0' COMMENT '订单ID',
  `claim_time` bigint(16) DEFAULT NULL COMMENT '领取时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='免费领取记录表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_goods`
--

CREATE TABLE `dc_goods` (
  `id` int(11) UNSIGNED NOT NULL COMMENT 'ID',
  `station_id` int(10) NOT NULL DEFAULT '0' COMMENT '分站ID',
  `des` text COLLATE utf8mb4_unicode_ci COMMENT '商品简介',
  `sort_num` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `sort_top` tinyint(1) NOT NULL DEFAULT '0' COMMENT '分类置顶',
  `index_top` tinyint(1) NOT NULL DEFAULT '0' COMMENT '首页置顶',
  `type` text COLLATE utf8mb4_unicode_ci,
  `attr_id` int(10) DEFAULT NULL COMMENT '商品类型',
  `is_sku` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '是否是多规格',
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文章标题',
  `unit_name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '个',
  `is_on_shelf` tinyint(1) NOT NULL DEFAULT '1',
  `allow_dock` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否允许此商品被对接',
  `attach_user` text COLLATE utf8mb4_unicode_ci COMMENT '附加选项',
  `create_time` bigint(16) NOT NULL COMMENT '创建时间',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文章内容',
  `pay_content` text COLLATE utf8mb4_unicode_ci,
  `cover` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '封面图',
  `gallery` text COLLATE utf8mb4_unicode_ci COMMENT '商品图集(JSON数组)',
  `sort_id` int(11) NOT NULL DEFAULT '-1' COMMENT '分类ID',
  `sales` int(11) DEFAULT '0' COMMENT '已售数量',
  `stock` int(10) DEFAULT '0',
  `password` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '访问密码',
  `template` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '模板',
  `tags` text COLLATE utf8mb4_unicode_ci COMMENT '标签',
  `link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '跳转链接',
  `delete_time` bigint(16) DEFAULT NULL,
  `profit_rule_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品绑定的批量加价规则,0=不绑定',
  `profit_ratio` decimal(6,2) NOT NULL DEFAULT '100.00' COMMENT '商品利润比(%)',
  `single_rule_id` int(10) NOT NULL DEFAULT '0' COMMENT '单商品加价规则,0=不绑定',
  `accuracy` tinyint(1) NOT NULL DEFAULT '2' COMMENT '价格小数位数精度(0-8)',
  `discount_title` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '批量购买优惠自定义标题(空=批发优惠)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_goods`
--

INSERT INTO `dc_goods` (`id`, `station_id`, `des`, `sort_num`, `sort_top`, `index_top`, `type`, `attr_id`, `is_sku`, `title`, `unit_name`, `is_on_shelf`, `allow_dock`, `attach_user`, `create_time`, `content`, `pay_content`, `cover`, `gallery`, `sort_id`, `sales`, `stock`, `password`, `template`, `tags`, `link`, `delete_time`, `profit_rule_id`, `profit_ratio`, `single_rule_id`, `accuracy`, `discount_title`) VALUES
(1, 0, '【演示】DCSHOP 多财商城游戏充值分类示例 · 多规格虚拟服务 · 请勿下单', 10, 0, 1, 'service', 1, 'y', '【示例】游戏直充·多规格演示（请勿下单）', '次', 1, 1, NULL, 1781527589, '<div style=\"padding:16px;line-height:1.85;color:#333;font-size:14px;\"><div style=\"background:#fff3cd;border-left:4px solid #ff9800;padding:12px 14px;border-radius:6px;margin-bottom:18px;color:#856404;\"><i class=\"ri-error-warning-fill\" style=\"color:#ff9800;margin-right:6px;font-size:16px;\"></i><b>此商品仅为系统演示用，仅作展示，请勿下单！</b></div><h4 style=\"font-size:16px;margin:18px 0 10px;color:#222;border-left:3px solid #2196F3;padding-left:8px;\"><i class=\"ri-rocket-2-line\" style=\"color:#ff6600;margin-right:4px;\"></i>系统简介</h4><p style=\"margin:0 0 14px;\">DCSHOP 多财商城是一款专业的自动发卡商城系统，覆盖商品发布、订单管理、卡密自动发货、会员体系、分销返佣、分店开通等完整电商能力，专为虚拟商品、数字内容、会员服务等业务量身打造。</p><h4 style=\"font-size:16px;margin:18px 0 10px;color:#222;border-left:3px solid #ff6600;padding-left:8px;\"><i class=\"ri-flashlight-line\" style=\"color:#ff9800;margin-right:4px;\"></i>核心特性</h4><ul style=\"padding-left:0;margin:0;list-style:none;\"><li style=\"padding:6px 0;\"><i class=\"ri-shield-check-line\" style=\"color:#4caf50;margin-right:6px;\"></i><b>自动发卡</b>：一卡一密 / 固定卡密 / 虚拟服务 / 接口对接 四种模式，覆盖所有数字商品业务场景</li><li style=\"padding:6px 0;\"><i class=\"ri-medal-line\" style=\"color:#e91e63;margin-right:6px;\"></i><b>会员体系</b>：内置 8 档现成会员等级方案，不满足需求可自定义无限添加，灵活搭建会员体系   + 等级价 + 自动加价规则 + 独立会员定价，让老用户拿货更便宜</li><li style=\"padding:6px 0;\"><i class=\"ri-store-3-line\" style=\"color:#9c27b0;margin-right:6px;\"></i><b>分店系统</b>：标配 5 档分店权限等级，不够随时自行增设，支持独立域名 / 路径后缀 / 二级域名 三种分店部署方式</li><li style=\"padding:6px 0;\"><i class=\"ri-coupon-line\" style=\"color:#00bcd4;margin-right:6px;\"></i><b>批量优惠</b>：每件优惠、订单优惠、订单折扣 三种营销玩法自由组合，自动叠加结算</li><li style=\"padding:6px 0;\"><i class=\"ri-layout-grid-line\" style=\"color:#3f51b5;margin-right:6px;\"></i><b>多端自适应</b>：PC / 移动端 一套模板，深色模式 + 丰富 DIY 配置，开箱即用</li><li style=\"padding:6px 0;\"><i class=\"ri-customer-service-2-line\" style=\"color:#ff5722;margin-right:6px;\"></i><b>售后体系</b>：售后插件 + 卡密补发 + 退款 + 在线客服，全链路用户体验</li><li style=\"padding:6px 0;\"><i class=\"ri-bar-chart-2-line\" style=\"color:#795548;margin-right:6px;\"></i><b>数据看板</b>：订单 / 销售额 / 用户 / 商品 多维度数据统计，业务一目了然</li><li style=\"padding:6px 0;\"><i class=\"ri-shake-hands-line\" style=\"color:#607d8b;margin-right:6px;\"></i><b>多级分销</b>：支持无限级分销 + 等级返佣 + 升级奖励，快速裂变拉新</li></ul><h4 style=\"font-size:16px;margin:18px 0 10px;color:#222;border-left:3px solid #4caf50;padding-left:8px;\"><i class=\"ri-information-line\" style=\"color:#2196F3;margin-right:4px;\"></i>关于本商品</h4><p style=\"margin:0 0 14px;\">本商品为 <b>游戏点卡</b> 类目演示，展示 DCSHOP 在游戏直充业务下的<b>多规格商品</b>卡片样式、规格选择、价格展示、下单流程等前台呈现效果，<b style=\"color:#e53e3e;\">请勿真实下单</b>。如需上架自己的商品，请前往后台 <b>商品管理</b> 删除本演示商品后发布即可。</p><h4 style=\"font-size:16px;margin:18px 0 10px;color:#222;border-left:3px solid #9c27b0;padding-left:8px;\"><i class=\"ri-links-line\" style=\"color:#607d8b;margin-right:4px;\"></i>了解更多</h4><p style=\"margin:0;\">官网：<a href=\"https://dcshop.xzsc.cc/\" target=\"_blank\" style=\"color:#2f69d9;text-decoration:none;\">https://dcshop.xzsc.cc/</a> ｜ DCSHOP官方交流群：649146439</p></div>', NULL, '../content/static/img/yxzc.png', NULL, 1, 1, 399, '', '', NULL, '', NULL, 0, '100.00', 0, 2, ''),
(2, 0, '【演示】DCSHOP 多财商城平台会员分类示例 · 多规格通用卡密 · 请勿下单', 9, 0, 1, 'general', 2, 'y', '【示例】平台会员订阅·套餐演示（请勿下单）', '张', 1, 1, NULL, 1781527589, '<div style=\"padding:16px;line-height:1.85;color:#333;font-size:14px;\"><div style=\"background:#fff3cd;border-left:4px solid #ff9800;padding:12px 14px;border-radius:6px;margin-bottom:18px;color:#856404;\"><i class=\"ri-error-warning-fill\" style=\"color:#ff9800;margin-right:6px;font-size:16px;\"></i><b>此商品仅为系统演示用，仅作展示，请勿下单！</b></div><h4 style=\"font-size:16px;margin:18px 0 10px;color:#222;border-left:3px solid #2196F3;padding-left:8px;\"><i class=\"ri-rocket-2-line\" style=\"color:#ff6600;margin-right:4px;\"></i>系统简介</h4><p style=\"margin:0 0 14px;\">DCSHOP 多财商城是一款专业的自动发卡商城系统，覆盖商品发布、订单管理、卡密自动发货、会员体系、分销返佣、分店开通等完整电商能力，专为虚拟商品、数字内容、会员服务等业务量身打造。</p><h4 style=\"font-size:16px;margin:18px 0 10px;color:#222;border-left:3px solid #ff6600;padding-left:8px;\"><i class=\"ri-flashlight-line\" style=\"color:#ff9800;margin-right:4px;\"></i>核心特性</h4><ul style=\"padding-left:0;margin:0;list-style:none;\"><li style=\"padding:6px 0;\"><i class=\"ri-shield-check-line\" style=\"color:#4caf50;margin-right:6px;\"></i><b>自动发卡</b>：一卡一密 / 固定卡密 / 虚拟服务 / 接口对接 四种模式，覆盖所有数字商品业务场景</li><li style=\"padding:6px 0;\"><i class=\"ri-medal-line\" style=\"color:#e91e63;margin-right:6px;\"></i><b>会员体系</b>：内置 8 档现成会员等级方案，不满足需求可自定义无限添加，灵活搭建会员体系   + 等级价 + 自动加价规则 + 独立会员定价，让老用户拿货更便宜</li><li style=\"padding:6px 0;\"><i class=\"ri-store-3-line\" style=\"color:#9c27b0;margin-right:6px;\"></i><b>分店系统</b>：标配 5 档分店权限等级，不够随时自行增设，支持独立域名 / 路径后缀 / 二级域名 三种分店部署方式</li><li style=\"padding:6px 0;\"><i class=\"ri-coupon-line\" style=\"color:#00bcd4;margin-right:6px;\"></i><b>批量优惠</b>：每件优惠、订单优惠、订单折扣 三种营销玩法自由组合，自动叠加结算</li><li style=\"padding:6px 0;\"><i class=\"ri-layout-grid-line\" style=\"color:#3f51b5;margin-right:6px;\"></i><b>多端自适应</b>：PC / 移动端 一套模板，深色模式 + 丰富 DIY 配置，开箱即用</li><li style=\"padding:6px 0;\"><i class=\"ri-customer-service-2-line\" style=\"color:#ff5722;margin-right:6px;\"></i><b>售后体系</b>：售后插件 + 卡密补发 + 退款 + 在线客服，全链路用户体验</li><li style=\"padding:6px 0;\"><i class=\"ri-bar-chart-2-line\" style=\"color:#795548;margin-right:6px;\"></i><b>数据看板</b>：订单 / 销售额 / 用户 / 商品 多维度数据统计，业务一目了然</li><li style=\"padding:6px 0;\"><i class=\"ri-shake-hands-line\" style=\"color:#607d8b;margin-right:6px;\"></i><b>多级分销</b>：支持无限级分销 + 等级返佣 + 升级奖励，快速裂变拉新</li></ul><h4 style=\"font-size:16px;margin:18px 0 10px;color:#222;border-left:3px solid #4caf50;padding-left:8px;\"><i class=\"ri-information-line\" style=\"color:#2196F3;margin-right:4px;\"></i>关于本商品</h4><p style=\"margin:0 0 14px;\">本商品为 <b>会员订阅</b> 类目演示，展示 DCSHOP 在 SaaS 会员订阅业务下的<b>多规格商品</b>卡片样式、规格选择、价格展示、下单流程等前台呈现效果，<b style=\"color:#e53e3e;\">请勿真实下单</b>。如需上架自己的商品，请前往后台 <b>商品管理</b> 删除本演示商品后发布即可。</p><h4 style=\"font-size:16px;margin:18px 0 10px;color:#222;border-left:3px solid #9c27b0;padding-left:8px;\"><i class=\"ri-links-line\" style=\"color:#607d8b;margin-right:4px;\"></i>了解更多</h4><p style=\"margin:0;\">官网：<a href=\"https://dcshop.xzsc.cc/\" target=\"_blank\" style=\"color:#2f69d9;text-decoration:none;\">https://dcshop.xzsc.cc/</a> ｜ DCSHOP官方交流群：649146439</p></div>', NULL, '../content/static/img/pthy.png', NULL, 2, 0, 300, '', '', NULL, '', NULL, 0, '100.00', 0, 2, ''),
(3, 0, '【演示】DCSHOP 多财商城软件激活分类示例 · 一卡一密自动发货 · 请勿下单', 8, 0, 1, 'once', 0, 'n', '【示例】专业软件激活码·演示版（请勿下单）', '套', 1, 1, NULL, 1781527589, '<div style=\"padding:16px;line-height:1.85;color:#333;font-size:14px;\"><div style=\"background:#fff3cd;border-left:4px solid #ff9800;padding:12px 14px;border-radius:6px;margin-bottom:18px;color:#856404;\"><i class=\"ri-error-warning-fill\" style=\"color:#ff9800;margin-right:6px;font-size:16px;\"></i><b>此商品仅为系统演示用，仅作展示，请勿下单！</b></div><h4 style=\"font-size:16px;margin:18px 0 10px;color:#222;border-left:3px solid #2196F3;padding-left:8px;\"><i class=\"ri-rocket-2-line\" style=\"color:#ff6600;margin-right:4px;\"></i>系统简介</h4><p style=\"margin:0 0 14px;\">DCSHOP 多财商城是一款专业的自动发卡商城系统，覆盖商品发布、订单管理、卡密自动发货、会员体系、分销返佣、分店开通等完整电商能力，专为虚拟商品、数字内容、会员服务等业务量身打造。</p><h4 style=\"font-size:16px;margin:18px 0 10px;color:#222;border-left:3px solid #ff6600;padding-left:8px;\"><i class=\"ri-flashlight-line\" style=\"color:#ff9800;margin-right:4px;\"></i>核心特性</h4><ul style=\"padding-left:0;margin:0;list-style:none;\"><li style=\"padding:6px 0;\"><i class=\"ri-shield-check-line\" style=\"color:#4caf50;margin-right:6px;\"></i><b>自动发卡</b>：一卡一密 / 固定卡密 / 虚拟服务 / 接口对接 四种模式，覆盖所有数字商品业务场景</li><li style=\"padding:6px 0;\"><i class=\"ri-medal-line\" style=\"color:#e91e63;margin-right:6px;\"></i><b>会员体系</b>：内置 8 档现成会员等级方案，不满足需求可自定义无限添加，灵活搭建会员体系   + 等级价 + 自动加价规则 + 独立会员定价，让老用户拿货更便宜</li><li style=\"padding:6px 0;\"><i class=\"ri-store-3-line\" style=\"color:#9c27b0;margin-right:6px;\"></i><b>分店系统</b>：标配 5 档分店权限等级，不够随时自行增设，支持独立域名 / 路径后缀 / 二级域名 三种分店部署方式</li><li style=\"padding:6px 0;\"><i class=\"ri-coupon-line\" style=\"color:#00bcd4;margin-right:6px;\"></i><b>批量优惠</b>：每件优惠、订单优惠、订单折扣 三种营销玩法自由组合，自动叠加结算</li><li style=\"padding:6px 0;\"><i class=\"ri-layout-grid-line\" style=\"color:#3f51b5;margin-right:6px;\"></i><b>多端自适应</b>：PC / 移动端 一套模板，深色模式 + 丰富 DIY 配置，开箱即用</li><li style=\"padding:6px 0;\"><i class=\"ri-customer-service-2-line\" style=\"color:#ff5722;margin-right:6px;\"></i><b>售后体系</b>：售后插件 + 卡密补发 + 退款 + 在线客服，全链路用户体验</li><li style=\"padding:6px 0;\"><i class=\"ri-bar-chart-2-line\" style=\"color:#795548;margin-right:6px;\"></i><b>数据看板</b>：订单 / 销售额 / 用户 / 商品 多维度数据统计，业务一目了然</li><li style=\"padding:6px 0;\"><i class=\"ri-shake-hands-line\" style=\"color:#607d8b;margin-right:6px;\"></i><b>多级分销</b>：支持无限级分销 + 等级返佣 + 升级奖励，快速裂变拉新</li></ul><h4 style=\"font-size:16px;margin:18px 0 10px;color:#222;border-left:3px solid #4caf50;padding-left:8px;\"><i class=\"ri-information-line\" style=\"color:#2196F3;margin-right:4px;\"></i>关于本商品</h4><p style=\"margin:0 0 14px;\">本商品为 <b>软件激活码</b> 类目演示，展示 DCSHOP 在软件授权业务下的<b>单规格商品</b>卡片样式、价格展示、下单流程等前台呈现效果，<b style=\"color:#e53e3e;\">请勿真实下单</b>。如需上架自己的商品，请前往后台 <b>商品管理</b> 删除本演示商品后发布即可。</p><h4 style=\"font-size:16px;margin:18px 0 10px;color:#222;border-left:3px solid #9c27b0;padding-left:8px;\"><i class=\"ri-links-line\" style=\"color:#607d8b;margin-right:4px;\"></i>了解更多</h4><p style=\"margin:0;\">官网：<a href=\"https://dcshop.xzsc.cc/\" target=\"_blank\" style=\"color:#2f69d9;text-decoration:none;\">https://dcshop.xzsc.cc/</a> ｜ DCSHOP官方交流群：649146439</p></div>', NULL, '../content/static/img/yjjhm.png', NULL, 3, 0, 100, '', '', NULL, '', NULL, 0, '100.00', 0, 2, ''),
(4, 0, '【演示】DCSHOP 多财商城月度会员分类示例 · 通用卡密自动发货 · 请勿下单', 7, 0, 0, 'general', 0, 'n', '【示例】影音平台月度会员·演示版（请勿下单）', '张', 1, 1, NULL, 1781527589, '<div style=\"padding:16px;line-height:1.85;color:#333;font-size:14px;\"><div style=\"background:#fff3cd;border-left:4px solid #ff9800;padding:12px 14px;border-radius:6px;margin-bottom:18px;color:#856404;\"><i class=\"ri-error-warning-fill\" style=\"color:#ff9800;margin-right:6px;font-size:16px;\"></i><b>此商品仅为系统演示用，仅作展示，请勿下单！</b></div><h4 style=\"font-size:16px;margin:18px 0 10px;color:#222;border-left:3px solid #2196F3;padding-left:8px;\"><i class=\"ri-rocket-2-line\" style=\"color:#ff6600;margin-right:4px;\"></i>系统简介</h4><p style=\"margin:0 0 14px;\">DCSHOP 多财商城是一款专业的自动发卡商城系统，覆盖商品发布、订单管理、卡密自动发货、会员体系、分销返佣、分店开通等完整电商能力，专为虚拟商品、数字内容、会员服务等业务量身打造。</p><h4 style=\"font-size:16px;margin:18px 0 10px;color:#222;border-left:3px solid #ff6600;padding-left:8px;\"><i class=\"ri-flashlight-line\" style=\"color:#ff9800;margin-right:4px;\"></i>核心特性</h4><ul style=\"padding-left:0;margin:0;list-style:none;\"><li style=\"padding:6px 0;\"><i class=\"ri-shield-check-line\" style=\"color:#4caf50;margin-right:6px;\"></i><b>自动发卡</b>：一卡一密 / 固定卡密 / 虚拟服务 / 接口对接 四种模式，覆盖所有数字商品业务场景</li><li style=\"padding:6px 0;\"><i class=\"ri-medal-line\" style=\"color:#e91e63;margin-right:6px;\"></i><b>会员体系</b>：内置 8 档现成会员等级方案，不满足需求可自定义无限添加，灵活搭建会员体系   + 等级价 + 自动加价规则 + 独立会员定价，让老用户拿货更便宜</li><li style=\"padding:6px 0;\"><i class=\"ri-store-3-line\" style=\"color:#9c27b0;margin-right:6px;\"></i><b>分店系统</b>：标配 5 档分店权限等级，不够随时自行增设，支持独立域名 / 路径后缀 / 二级域名 三种分店部署方式</li><li style=\"padding:6px 0;\"><i class=\"ri-coupon-line\" style=\"color:#00bcd4;margin-right:6px;\"></i><b>批量优惠</b>：每件优惠、订单优惠、订单折扣 三种营销玩法自由组合，自动叠加结算</li><li style=\"padding:6px 0;\"><i class=\"ri-layout-grid-line\" style=\"color:#3f51b5;margin-right:6px;\"></i><b>多端自适应</b>：PC / 移动端 一套模板，深色模式 + 丰富 DIY 配置，开箱即用</li><li style=\"padding:6px 0;\"><i class=\"ri-customer-service-2-line\" style=\"color:#ff5722;margin-right:6px;\"></i><b>售后体系</b>：售后插件 + 卡密补发 + 退款 + 在线客服，全链路用户体验</li><li style=\"padding:6px 0;\"><i class=\"ri-bar-chart-2-line\" style=\"color:#795548;margin-right:6px;\"></i><b>数据看板</b>：订单 / 销售额 / 用户 / 商品 多维度数据统计，业务一目了然</li><li style=\"padding:6px 0;\"><i class=\"ri-shake-hands-line\" style=\"color:#607d8b;margin-right:6px;\"></i><b>多级分销</b>：支持无限级分销 + 等级返佣 + 升级奖励，快速裂变拉新</li></ul><h4 style=\"font-size:16px;margin:18px 0 10px;color:#222;border-left:3px solid #4caf50;padding-left:8px;\"><i class=\"ri-information-line\" style=\"color:#2196F3;margin-right:4px;\"></i>关于本商品</h4><p style=\"margin:0 0 14px;\">本商品为 <b>影音会员</b> 类目演示，展示 DCSHOP 在影视/音乐会员业务下的<b>单规格商品</b>卡片样式、价格展示、下单流程等前台呈现效果，<b style=\"color:#e53e3e;\">请勿真实下单</b>。如需上架自己的商品，请前往后台 <b>商品管理</b> 删除本演示商品后发布即可。</p><h4 style=\"font-size:16px;margin:18px 0 10px;color:#222;border-left:3px solid #9c27b0;padding-left:8px;\"><i class=\"ri-links-line\" style=\"color:#607d8b;margin-right:4px;\"></i>了解更多</h4><p style=\"margin:0;\">官网：<a href=\"https://dcshop.xzsc.cc/\" target=\"_blank\" style=\"color:#2f69d9;text-decoration:none;\">https://dcshop.xzsc.cc/</a> ｜ DCSHOP官方交流群：649146439</p></div>', NULL, '../content/static/img/ydhy.png', NULL, 4, 0, 100, '', '', NULL, '', NULL, 0, '100.00', 0, 2, ''),
(5, 0, '测试商品，小K网源码网', 0, 0, 0, 'service', 0, 'n', '小K网会员', '/个', 1, 1, '[{\"name\":\"收货地址\",\"placeholder\":\"\",\"type\":\"string\",\"required\":true,\"tip\":\"\"},{\"name\":\"手机号码\",\"placeholder\":\"\",\"type\":\"tel\",\"required\":true,\"tip\":\"\"},{\"name\":\"收货人姓名\",\"placeholder\":\"\",\"type\":\"string\",\"required\":true,\"tip\":\"\"}]', 1781582967, '', '', '../content/uploadfile/202606/d9d31781582809.jpg', '[\"../content/uploadfile/202606/d9d31781582809.jpg\"]', 2, 0, 0, '', '', NULL, '', NULL, 0, '100.00', 0, 2, '');

-- --------------------------------------------------------

--
-- 表的结构 `dc_goods_general`
--

CREATE TABLE `dc_goods_general` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID',
  `goods_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `sku` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '规格',
  `content` text COLLATE utf8mb4_unicode_ci COMMENT '卡密',
  `create_time` bigint(16) DEFAULT NULL COMMENT '添加时间',
  `update_time` bigint(16) DEFAULT NULL COMMENT '修改时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_goods_general`
--

INSERT INTO `dc_goods_general` (`id`, `goods_id`, `sku`, `content`, `create_time`, `update_time`) VALUES
(1, 2, '5', 'DCSHOP-DEMO-平台会员-月卡-通用卡密（仅演示，请勿真实使用）', 1781527589, NULL),
(2, 2, '6', 'DCSHOP-DEMO-平台会员-季卡-通用卡密（仅演示，请勿真实使用）', 1781527589, NULL),
(3, 2, '7', 'DCSHOP-DEMO-平台会员-年卡-通用卡密（仅演示，请勿真实使用）', 1781527589, NULL),
(4, 4, '0', 'DCSHOP-DEMO-月度会员-通用卡密（仅演示，请勿真实使用）', 1781527589, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `dc_goods_general_sale`
--

CREATE TABLE `dc_goods_general_sale` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID',
  `goods_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `order_list_id` int(10) NOT NULL DEFAULT '0' COMMENT '子订单ID',
  `sku` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '规格',
  `content` text COLLATE utf8mb4_unicode_ci COMMENT '卡密',
  `num` int(10) NOT NULL DEFAULT '0' COMMENT '购买数量',
  `create_time` bigint(16) DEFAULT NULL COMMENT '添加时间',
  `update_time` bigint(16) DEFAULT NULL COMMENT '编辑时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_goods_once`
--

CREATE TABLE `dc_goods_once` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID',
  `goods_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `sku` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '规格',
  `batch_no` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '批次号',
  `content` text COLLATE utf8mb4_unicode_ci COMMENT '卡密',
  `create_time` bigint(16) DEFAULT NULL COMMENT '添加时间',
  `update_time` bigint(16) DEFAULT NULL COMMENT '修改时间',
  `sale_time` bigint(16) DEFAULT NULL COMMENT '出售时间',
  `order_list_id` int(10) NOT NULL DEFAULT '0' COMMENT '子订单ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_goods_once`
--

INSERT INTO `dc_goods_once` (`id`, `goods_id`, `sku`, `batch_no`, `content`, `create_time`, `update_time`, `sale_time`, `order_list_id`) VALUES
(1, 3, '0', 'DEMO-SOFT', 'DCSHOP-DEMO-SOFT-KEY-0001', 1781527589, NULL, NULL, 0),
(2, 3, '0', 'DEMO-SOFT', 'DCSHOP-DEMO-SOFT-KEY-0002', 1781527589, NULL, NULL, 0),
(3, 3, '0', 'DEMO-SOFT', 'DCSHOP-DEMO-SOFT-KEY-0003', 1781527589, NULL, NULL, 0),
(4, 3, '0', 'DEMO-SOFT', 'DCSHOP-DEMO-SOFT-KEY-0004', 1781527589, NULL, NULL, 0),
(5, 3, '0', 'DEMO-SOFT', 'DCSHOP-DEMO-SOFT-KEY-0005', 1781527589, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- 表的结构 `dc_goods_service`
--

CREATE TABLE `dc_goods_service` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID',
  `goods_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `content` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sku` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '规格',
  `create_time` bigint(16) DEFAULT NULL COMMENT '添加时间',
  `update_time` bigint(16) DEFAULT NULL COMMENT '修改时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_goods_service`
--

INSERT INTO `dc_goods_service` (`id`, `goods_id`, `content`, `sku`, `create_time`, `update_time`) VALUES
(1, 1, '【演示】游戏充值10元：付款后商家会按买家填写的信息处理，请勿真实下单。', '1', 1781527589, NULL),
(2, 1, '【演示】游戏充值30元：付款后商家会按买家填写的信息处理，请勿真实下单。', '2', 1781527589, NULL),
(3, 1, '【演示】游戏充值50元：付款后商家会按买家填写的信息处理，请勿真实下单。', '3', 1781527589, NULL),
(4, 1, '【演示】游戏充值100元：付款后商家会按买家填写的信息处理，请勿真实下单。', '4', 1781527589, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `dc_goods_service_sale`
--

CREATE TABLE `dc_goods_service_sale` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID',
  `goods_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `order_list_id` int(10) NOT NULL DEFAULT '0' COMMENT '子订单ID',
  `sku` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '规格',
  `content` text COLLATE utf8mb4_unicode_ci COMMENT '卡密',
  `num` int(10) NOT NULL DEFAULT '0' COMMENT '购买数量',
  `is_default` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT 'y',
  `create_time` bigint(16) DEFAULT NULL COMMENT '添加时间',
  `update_time` bigint(16) DEFAULT NULL COMMENT '编辑时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_goods_service_sale`
--

INSERT INTO `dc_goods_service_sale` (`id`, `goods_id`, `order_list_id`, `sku`, `content`, `num`, `is_default`, `create_time`, `update_time`) VALUES
(1, 1, 1, '1', '【演示】游戏充值10元：付款后商家会按买家填写的信息处理，请勿真实下单。', 1, 'y', 1781528857, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `dc_goods_type`
--

CREATE TABLE `dc_goods_type` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delete_time` bigint(16) DEFAULT NULL,
  `hide` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'n'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_goods_type`
--

INSERT INTO `dc_goods_type` (`id`, `name`, `delete_time`, `hide`) VALUES
(1, '充值面值', NULL, 'n'),
(2, '会员时长', NULL, 'n'),
(3, '收货地址', NULL, 'n');

-- --------------------------------------------------------

--
-- 表的结构 `dc_level_order`
--

CREATE TABLE `dc_level_order` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `out_trade_no` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `level_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `purchase_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `duration_days` int(10) NOT NULL DEFAULT '0',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `base_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `old_level_id` int(10) NOT NULL DEFAULT '0',
  `old_expire_time` bigint(16) DEFAULT '0',
  `new_expire_time` bigint(16) DEFAULT '0',
  `state` tinyint(1) NOT NULL DEFAULT '0',
  `create_time` bigint(16) DEFAULT NULL,
  `complete_time` bigint(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='会员等级开通订单' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_link`
--

CREATE TABLE `dc_link` (
  `id` int(11) UNSIGNED NOT NULL COMMENT '链接表',
  `sitename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `siteurl` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '地址',
  `icon` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图标URL',
  `description` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注信息',
  `hide` enum('n','y') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'n' COMMENT '是否隐藏',
  `taxis` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序序号'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_link`
--

INSERT INTO `dc_link` (`id`, `sitename`, `siteurl`, `icon`, `description`, `hide`, `taxis`) VALUES
(1, 'DCSHOP多财商城系统', 'https://dcshop.xzsc.cc/', '', '', 'n', 0);

-- --------------------------------------------------------

--
-- 表的结构 `dc_mcy_goods`
--

CREATE TABLE `dc_mcy_goods` (
  `id` int(11) UNSIGNED NOT NULL,
  `source_id` int(11) NOT NULL DEFAULT '0' COMMENT '货源站ID',
  `remote_gid` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '萌次元商品编码',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '本地商品ID',
  `remote_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '远端发货类型',
  `race_json` text COLLATE utf8mb4_unicode_ci COMMENT '远端种类结构 JSON',
  `sku_json` text COLLATE utf8mb4_unicode_ci COMMENT '远端 SKU 结构 JSON',
  `widget_json` text COLLATE utf8mb4_unicode_ci COMMENT '远端控件结构 JSON',
  `remote_snapshot` mediumtext COLLATE utf8mb4_unicode_ci COMMENT '远端详情原始快照 JSON',
  `last_remote_stock` int(11) NOT NULL DEFAULT '0' COMMENT '最近一次远端库存',
  `last_remote_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '最近一次远端成本价',
  `sync_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal' COMMENT '同步状态',
  `last_sync_msg` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '最近一次同步摘要',
  `price_locked` tinyint(1) NOT NULL DEFAULT '0' COMMENT '价格锁定 1=只同步库存不同步价格',
  `markup_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed' COMMENT '加价模式 fixed/percent',
  `markup_val` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '加价值',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='萌次元对接商品关联' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_mcy_sale`
--

CREATE TABLE `dc_mcy_sale` (
  `id` int(11) UNSIGNED NOT NULL,
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '本地商品ID',
  `order_list_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单子项ID',
  `request_no` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '本站请求号',
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '本地SKU或远端规格组合',
  `content` text COLLATE utf8mb4_unicode_ci COMMENT '发货内容',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '购买份数',
  `source_id` int(11) NOT NULL DEFAULT '0' COMMENT '货源站ID',
  `source_order_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '萌次元订单号',
  `remote_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '远端订单状态',
  `remote_raw` mediumtext COLLATE utf8mb4_unicode_ci COMMENT '远端订单原始响应 JSON',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='萌次元对接订单记录' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_mcy_sku_map`
--

CREATE TABLE `dc_mcy_sku_map` (
  `id` int(11) UNSIGNED NOT NULL,
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '本地商品ID',
  `local_sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '本地SKU值ID组合',
  `remote_sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '萌次元远端SKU组合值',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='萌次元本地SKU与远端规格映射' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_mcy_sources`
--

CREATE TABLE `dc_mcy_sources` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '货源站名称',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '货源站域名',
  `protocol` tinyint(1) NOT NULL DEFAULT '1' COMMENT '协议 1=https 0=http',
  `app_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '萌次元 app_id',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '兼容字段',
  `api_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '萌次元 app_key',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用 1/0',
  `auto_sync` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否自动同步 1/0',
  `last_sync` int(11) NOT NULL DEFAULT '0' COMMENT '上次同步时间戳',
  `last_err` text COLLATE utf8mb4_unicode_ci COMMENT '最后一次错误信息',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='萌次元对接货源站' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_media_sort`
--

CREATE TABLE `dc_media_sort` (
  `id` int(11) UNSIGNED NOT NULL COMMENT '资源分类表',
  `sortname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分类名'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_member`
--

CREATE TABLE `dc_member` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ri-vip-diamond-line' COMMENT 'Remix Icon图标',
  `icon_image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '等级图片图标',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '等级开通价格',
  `markup_ratio` decimal(6,2) NOT NULL DEFAULT '0.00' COMMENT '加价比例(%)',
  `exchange_ratio` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '积分兑换倍数',
  `actual_profit` decimal(5,2) NOT NULL DEFAULT '100.00' COMMENT '绝对利润(%)',
  `profit_threshold` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '分成阈值(%)',
  `profit_rule_id` int(10) NOT NULL DEFAULT '0' COMMENT '绑定加价规则ID',
  `duration_days` int(10) NOT NULL DEFAULT '0' COMMENT '有效期天数,0=永久',
  `renew_ratio` decimal(5,2) NOT NULL DEFAULT '100.00' COMMENT '续期百分比(%)',
  `content` text COLLATE utf8mb4_unicode_ci COMMENT '等级公告',
  `sort` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `state` tinyint(1) NOT NULL DEFAULT '1' COMMENT '启用状态',
  `create_time` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否默认等级 1=是',
  `upgrade_mode` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'any' COMMENT '自动升级判断模式: any=任一, all=全部',
  `upgrade_direct_count` int(10) NOT NULL DEFAULT '0' COMMENT '自动升级-直推粉丝数',
  `upgrade_consume_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '自动升级-累计消费金额',
  `upgrade_team_count` int(10) NOT NULL DEFAULT '0' COMMENT '自动升级-团队总人数'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_member`
--

INSERT INTO `dc_member` (`id`, `name`, `icon`, `icon_image`, `price`, `markup_ratio`, `exchange_ratio`, `actual_profit`, `profit_threshold`, `profit_rule_id`, `duration_days`, `renew_ratio`, `content`, `sort`, `state`, `create_time`, `update_time`, `is_default`, `upgrade_mode`, `upgrade_direct_count`, `upgrade_consume_amount`, `upgrade_team_count`) VALUES
(1, '普通用户', 'ri-user-smile-line', '', '0.00', '50.00', '5000.00', '10.00', '3.00', 0, 0, '0.00', '注册即拥有。', 1, 1, 0, 1781584606, 1, 'any', 0, '0.00', 0),
(2, '铜牌会员', 'ri-coin-line', '', '9.90', '45.00', '4500.00', '12.00', '3.00', 0, 0, '0.00', '铜牌会员，解锁首档会员价。', 2, 1, 0, 0, 0, 'any', 2, '50.00', 0),
(3, '银牌会员', 'ri-medal-line', '', '19.90', '40.00', '4000.00', '14.00', '2.00', 0, 0, '0.00', '银牌会员，拿货成本更低。', 3, 1, 0, 0, 0, 'any', 5, '200.00', 0),
(4, '金牌会员', 'ri-award-line', '', '39.90', '35.00', '3500.00', '16.00', '2.00', 0, 0, '0.00', '金牌会员，主力销售等级。', 4, 1, 0, 0, 0, 'any', 10, '500.00', 30),
(5, '铂金代理', 'ri-vip-crown-line', '', '69.90', '30.00', '3000.00', '18.00', '2.00', 0, 0, '0.00', '铂金代理，适合开店经营。', 5, 1, 0, 0, 0, 'any', 20, '1000.00', 80),
(6, '钻石代理', 'ri-vip-diamond-line', '', '99.90', '25.00', '2500.00', '20.00', '1.00', 0, 0, '0.00', '钻石代理，更强价格优势。', 6, 1, 0, 0, 0, 'any', 35, '2000.00', 150),
(7, '黑金代理', 'ri-shield-star-line', '', '149.90', '22.00', '2200.00', '22.00', '1.00', 0, 0, '0.00', '黑金代理，核心运营等级。', 7, 1, 0, 0, 0, 'any', 50, '5000.00', 300),
(8, '核心合伙人', 'ri-star-smile-line', '', '199.90', '20.00', '2000.00', '25.00', '1.00', 0, 0, '0.00', '核心合伙人，最高等级。', 8, 1, 0, 0, 0, 'any', 100, '10000.00', 500);

-- --------------------------------------------------------

--
-- 表的结构 `dc_member_price`
--

CREATE TABLE `dc_member_price` (
  `goods_id` int(10) NOT NULL,
  `sku` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `member_level` tinyint(3) UNSIGNED NOT NULL,
  `price` bigint(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='会员价格表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_navi`
--

CREATE TABLE `dc_navi` (
  `id` int(11) UNSIGNED NOT NULL COMMENT '导航表',
  `naviname` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '导航名称',
  `url` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '导航地址',
  `newtab` enum('n','y') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'n' COMMENT '在新窗口打开',
  `hide` enum('n','y') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'n' COMMENT '是否隐藏',
  `taxis` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序序号',
  `pid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父级ID',
  `isdefault` enum('n','y') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'n' COMMENT '是否系统默认导航，如首页',
  `type` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '导航类型 0自定义 1首页 2微语 3后台管理 4分类 5页面',
  `type_id` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '导航类型对应ID',
  `naviicon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '导航图标'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_options`
--

CREATE TABLE `dc_options` (
  `option_id` int(11) UNSIGNED NOT NULL COMMENT '站点配置信息表',
  `option_name` varchar(75) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置项',
  `option_value` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置项值'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_options`
--

INSERT INTO `dc_options` (`option_id`, `option_name`, `option_value`) VALUES
(1, 'blogname', 'DCSHOP多财商城'),
(2, 'blogurl', 'https://xkwo.com/'),
(3, 'bloginfo', ''),
(4, 'blog_site_name', '小K网的小博客'),
(5, 'blog_site_desc', '与你同行，探索心灵之窗。'),
(6, 'blog_footer_custom_text', '© 2026 DCSHOP的博客。记录技术与生活。'),
(7, 'blog_footer_links', 'ri-links-line|DCSHOP多财商城官方默认链接|https://dcshop.xzsc.cc/'),
(8, 'blogger_show_nickname', 'y'),
(9, 'blogger_nickname', 'DCSHOP多财商城'),
(10, 'blogger_avatar', '/content/blog_templates/default/images/logo.png'),
(11, 'blogger_intro_show', 'y'),
(12, 'blogger_intro_text', '在多财小站里，记录技术灵感，也收藏生活微光。'),
(13, 'blogger_external_links', 'ri-links-line|DCSHOP多财商城官方默认链接|https://dcshop.xzsc.cc/'),
(14, 'widgets1', 'a:11:{i:0;s:7:\"blogger\";i:1;s:6:\"search\";i:2;s:8:\"calendar\";i:3;s:3:\"tag\";i:4;s:7:\"twitter\";i:5;s:4:\"sort\";i:6;s:7:\"archive\";i:7;s:7:\"newcomm\";i:8;s:6:\"newlog\";i:9;s:6:\"hotlog\";i:10;s:4:\"link\";}'),
(15, 'widget_title', 'a:12:{s:7:\"blogger\";s:12:\"个人资料\";s:8:\"calendar\";s:6:\"日历\";s:3:\"tag\";s:6:\"标签\";s:7:\"twitter\";s:6:\"微语\";s:4:\"sort\";s:6:\"分类\";s:7:\"archive\";s:6:\"存档\";s:7:\"newcomm\";s:12:\"最新评论\";s:6:\"newlog\";s:12:\"最新文章\";s:6:\"hotlog\";s:12:\"热门文章\";s:4:\"link\";s:12:\"友情链接\";s:6:\"search\";s:6:\"搜索\";s:11:\"custom_text\";s:15:\"自定义组件\";}'),
(16, 'custom_widget', 'a:0:{}'),
(17, 'login_switch', 'y'),
(18, 'register_switch', 'y'),
(19, 'login_username_switch', 'y'),
(20, 'register_username_switch', 'y'),
(21, 'register_email_switch', 'n'),
(22, 'register_tel_switch', 'n'),
(23, 'blog_independent_domain', ''),
(24, 'iscomment', 'y'),
(25, 'ischkcomment', 'n'),
(26, 'login_comment', 'n'),
(27, 'comment_code', 'n'),
(28, 'comment_interval', '15'),
(29, 'comment_paging', 'y'),
(30, 'comment_pnum', '10'),
(31, 'comment_order', 'newer'),
(32, 'rss_output_num', '10'),
(33, 'rss_output_fulltext', 'y'),
(34, 'isthumbnail', 'y'),
(35, 'att_imgmaxw', '420'),
(36, 'att_imgmaxh', '460'),
(37, 'site_title', 'DCSHOP发卡系统_小K网源码网'),
(38, 'site_subtitle', '订单问题请查看买家帮助'),
(39, 'site_key', '多财商城系统,DCSHOP,DuoCai商城'),
(40, 'site_description', '多财商城系统,DCSHOP,DuoCai商城'),
(41, 'footer_info', 'Powered by DuoCai | <a style=\"color:rgb(113 172 249)\" href=\"https://dcshop.xzsc.cc/\" target=\"_blank\">DCSHOP提供技术支持</a>'),
(42, 'timezone', 'Asia/Shanghai'),
(43, 'index_lognum', '10'),
(44, 'admin_article_perpage_num', '10'),
(45, 'isurlrewrite', '1'),
(46, 'sales_switch', 'y'),
(47, 'stock_switch', 'y'),
(48, 'balance_switch', 'y'),
(49, 'virtual_currency_name', '积分'),
(50, 'order_goods_img_switch', 'y'),
(51, 'pay_redirect', 'list'),
(52, 'kami_order', 'asc'),
(53, 'roll_bulletin', '欢迎来到 DCSHOP 多财商城，专业的自动发卡平台！\r\n本站全部商品 7×24 小时全自动发货，下单即时收货\r\n平台支持微信、支付宝、余额多种支付方式，安全便捷\r\n注册即享会员价，下单越多等级越高，拿货成本更低\r\n请勿轻信任何要求私下转账的客服，所有交易请通过本站完成\r\n首次访问可前往「买家帮助」页查看新手教程与售后须知\r\n关注官网 https://dcshop.xzsc.cc/ 获取系统更新与功能资讯'),
(54, 'home_bulletin', '<p style=\"margin:0 0 10px;line-height:1.85;color:#333;font-size:14px;\"><i class=\"ri-megaphone-line\" style=\"color:#ff6600;margin-right:6px;font-size:16px;\"></i><strong style=\"color:#222;\">欢迎来到 DCSHOP 多财商城！</strong>本站基于 DCSHOP 自动发卡系统搭建，全部商品 <strong style=\"color:#e53e3e;\">7×24 小时全自动发货</strong>，下单即时收货。我们提供 内置 8 档现成会员等级方案，不满足需求可自定义无限添加，灵活搭建会员体系  、批量优惠、完善售后与多端自适应购物体验，让虚拟商品交易更高效、更安心。</p>'),
(55, 'order_required', '[{\"name\":\"联系信息\",\"placeholder\":\"请输入手机号，或字母数字组合\",\"type\":\"string\"}]'),
(56, 'active_plugins', 'a:11:{i:0;s:25:\"goods_once/goods_once.php\";i:1;s:31:\"goods_service/goods_service.php\";i:2;s:31:\"goods_general/goods_general.php\";i:3;s:21:\"adm_home/adm_home.php\";i:4;s:13:\"tips/tips.php\";i:5;s:17:\"repair/repair.php\";i:6;s:19:\"epay_wx/epay_wx.php\";i:7;s:21:\"epay_ali/epay_ali.php\";i:8;s:17:\"alipay/alipay.php\";i:9;s:17:\"ynl_wx/ynl_wx.php\";i:10;s:19:\"ynl_ali/ynl_ali.php\";}'),
(57, 'nonce_templet', 'default'),
(58, 'nonce_templet_tel', 'default'),
(59, 'nonce_bottom_nav_tpl', 'default'),
(60, 'nonce_user_tpl', 'default'),
(61, 'nonce_user_tpl_tel', 'default'),
(62, 'nonce_blog_tpl', 'default'),
(63, 'nonce_blog_tpl_tel', 'default'),
(64, 'level_default_grade', '1'),
(65, 'level_station_grade', '0'),
(66, 'level_setprice_grade', '0'),
(67, 'level_goodsstate_grade', '0'),
(68, 'level_deposit_grade', '0'),
(69, 'level_upgrade_profit', '0'),
(70, 'upgrade_reward_types', 'open,upgrade,renew'),
(71, 'level_commits_distribute', '0'),
(72, 'level_infinite_division', '0'),
(73, 'level_infinite_skip', '1'),
(74, 'commission_base', 'total'),
(75, 'commission_ratio', '100'),
(76, 'level_permission_time', '0'),
(77, 'level_permission_length', '30'),
(78, 'level_permission_renew_ratio', '50'),
(79, 'system_founder_uid', '1000'),
(80, 'station_domain', ''),
(81, 'station_cname_domain', ''),
(82, 'station_domain_retain', 'www,m,api,admin,mail,ftp'),
(83, 'station_domain_change_price', '0'),
(84, 'station_domain_strict', '0'),
(85, 'station_extra_domains', ''),
(86, 'station_slug_mode', '1'),
(90, 'mianze', '1781527631'),
(91, 'plugin_show_in_admin', '[\"epay_wx\",\"epay_ali\"]'),
(93, 'dc_line', '0'),
(99, 'icp', ''),
(102, 'detect_url', 'n'),
(103, 'login_code', 'n'),
(104, 'captcha_type', 'num'),
(105, 'panel_menu_title', ''),
(106, 'personal_center_icon', ''),
(107, 'logo', ''),
(109, 'admin_favicon', ''),
(114, 'isalias', 'n'),
(115, 'isalias_html', 'n'),
(116, 'log_title_style', '0'),
(126, 'login_email_switch', 'y'),
(127, 'login_tel_switch', 'n'),
(130, 'register_bind_tel', 'n'),
(131, 'register_bind_email', 'n'),
(132, 'register_bind_invite', 'n'),
(135, 'sms_bind_phone_daily_limit', '5'),
(136, 'sms_login_daily_limit', '10'),
(145, 'upgrade_price_mode', 'diff'),
(146, 'upgrade_level_check', '2'),
(147, 'order_level_check', '0'),
(148, 'withdraw_switch', '1'),
(149, 'withdraw_fee_rate', '0'),
(150, 'withdraw_min_amount', '10'),
(151, 'withdraw_methods', 'alipay,wechat,qq,bank'),
(152, 'balance_recharge_min', '1'),
(153, 'balance_recharge_max', '10000'),
(154, 'invite_register_only', '0');

-- --------------------------------------------------------

--
-- 表的结构 `dc_order`
--

CREATE TABLE `dc_order` (
  `id` int(10) UNSIGNED NOT NULL,
  `station_id` int(10) NOT NULL DEFAULT '0',
  `client_ip` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '客户端ip',
  `user_id` int(10) DEFAULT '0',
  `out_trade_no` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` int(10) DEFAULT NULL,
  `create_time` bigint(16) DEFAULT NULL,
  `payment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '支付方式',
  `pay_plugin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '支付插件',
  `pay_time` bigint(16) DEFAULT NULL,
  `update_time` bigint(16) DEFAULT NULL,
  `qr_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expire_time` bigint(16) DEFAULT NULL,
  `device` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pay_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pay_status` int(10) DEFAULT '0',
  `delete_time` bigint(16) DEFAULT NULL,
  `service_status` tinyint(1) DEFAULT '0',
  `status` tinyint(1) DEFAULT '0',
  `pwd` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `up_no` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_pay_init_time` bigint(16) DEFAULT '0' COMMENT '上次发起支付时间（冷却防重复）',
  `docking_err_msg` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '对接下单失败原因',
  `qingjiu_err_msg` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '晴玖对接失败原因',
  `yiciyuan_err_msg` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '异次元对接失败原因',
  `mcy_err_msg` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '萌次元对接失败原因',
  `notify_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_order`
--

INSERT INTO `dc_order` (`id`, `station_id`, `client_ip`, `user_id`, `out_trade_no`, `tel`, `email`, `amount`, `create_time`, `payment`, `pay_plugin`, `pay_time`, `update_time`, `qr_code`, `expire_time`, `device`, `pay_name`, `pay_status`, `delete_time`, `service_status`, `status`, `pwd`, `up_no`, `last_pay_init_time`, `docking_err_msg`, `qingjiu_err_msg`, `yiciyuan_err_msg`, `mcy_err_msg`, `notify_url`) VALUES
(1, 0, '110.182.46.34', 1000, '202606152107211336', NULL, NULL, 1350, 1781528841, '易支付/微信', 'epay_wx', 1781528857, NULL, NULL, 1781530641, NULL, '易支付/微信', 1, NULL, 0, 1, NULL, '2026061521072566611', 0, '', '', '', '', ''),
(2, 0, '110.182.46.34', 1000, '202606152221129683', NULL, NULL, 1050, 1781533272, '易支付/微信', 'epay_wx', NULL, NULL, NULL, 1781535072, NULL, '易支付/微信', 0, NULL, 0, 3, NULL, NULL, 0, '', '', '', '', ''),
(3, 0, '110.182.46.34', 1000, '202606161217101588', NULL, NULL, 1350, 1781583430, '易支付/微信', 'epay_wx', NULL, NULL, NULL, 1781585230, NULL, '易支付/微信', 0, NULL, 0, 0, NULL, NULL, 0, '', '', '', '', '');

-- --------------------------------------------------------

--
-- 表的结构 `dc_order_list`
--

CREATE TABLE `dc_order_list` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) DEFAULT NULL,
  `goods_id` int(10) DEFAULT NULL,
  `sku` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attr_spec` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attach_user` varchar(800) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int(10) DEFAULT NULL,
  `unit_price` int(10) DEFAULT NULL,
  `price` int(10) DEFAULT NULL,
  `status` int(10) DEFAULT '0' COMMENT '0:未发货，1:部分发货，2:全部发货',
  `cost_price` int(10) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_order_list`
--

INSERT INTO `dc_order_list` (`id`, `order_id`, `goods_id`, `sku`, `attr_spec`, `attach_user`, `quantity`, `unit_price`, `price`, `status`, `cost_price`) VALUES
(1, 1, 1, '1', '面值：10元；', '[]', 1, 1350, 1350, 0, 900),
(2, 2, 2, '5', '时长：月卡；', '[]', 1, 1050, 1050, 0, 700),
(3, 3, 1, '1', '面值：10元；', '[]', 1, 1350, 1350, 0, 900);

-- --------------------------------------------------------

--
-- 表的结构 `dc_order_required`
--

CREATE TABLE `dc_order_required` (
  `order_id` int(10) NOT NULL,
  `name` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_order_required`
--

INSERT INTO `dc_order_required` (`order_id`, `name`, `type`, `content`) VALUES
(1, '联系信息', 'string', '123'),
(2, '联系信息', 'string', '2'),
(3, '联系信息', 'string', '1');

-- --------------------------------------------------------

--
-- 表的结构 `dc_pay_callback_log`
--

CREATE TABLE `dc_pay_callback_log` (
  `id` int(11) NOT NULL,
  `gateway` varchar(64) NOT NULL DEFAULT '' COMMENT '支付网关',
  `order_no` varchar(64) NOT NULL DEFAULT '' COMMENT '订单号',
  `amount` varchar(32) NOT NULL DEFAULT '' COMMENT '金额',
  `status` varchar(32) NOT NULL DEFAULT '' COMMENT '回调状态',
  `raw_data` text COMMENT '原始回调数据',
  `ip` varchar(64) NOT NULL DEFAULT '' COMMENT '来源IP',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '接收时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='支付回调日志' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_physical_address`
--

CREATE TABLE `dc_physical_address` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID',
  `uid` int(10) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `receiver_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '收货人',
  `receiver_phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '手机号',
  `region` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '省市区',
  `address` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '详细地址',
  `buyer_remark` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '买家备注',
  `create_time` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint(16) DEFAULT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_physical_goods_shipping`
--

CREATE TABLE `dc_physical_goods_shipping` (
  `goods_id` int(10) UNSIGNED NOT NULL COMMENT '商品ID',
  `template_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '运费模板ID',
  `create_time` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint(16) DEFAULT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_physical_sale`
--

CREATE TABLE `dc_physical_sale` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID',
  `goods_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `order_list_id` int(10) NOT NULL DEFAULT '0' COMMENT '子订单ID',
  `sku` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '规格',
  `num` int(10) NOT NULL DEFAULT '0' COMMENT '购买数量',
  `receiver_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '收货人',
  `receiver_phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '手机号',
  `region` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '省市区',
  `address` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '详细地址',
  `buyer_remark` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '买家备注',
  `logistics_company` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '物流公司',
  `logistics_no` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '快递单号',
  `deliver_remark` text COLLATE utf8mb4_unicode_ci COMMENT '发货备注',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0待发货 1已发货',
  `create_time` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `deliver_time` bigint(16) DEFAULT NULL COMMENT '发货时间',
  `update_time` bigint(16) DEFAULT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_physical_shipping_template`
--

CREATE TABLE `dc_physical_shipping_template` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '模板名称',
  `first_fee` int(10) NOT NULL DEFAULT '0' COMMENT '首件运费，分',
  `append_fee` int(10) NOT NULL DEFAULT '0' COMMENT '续件运费，分',
  `free_threshold` int(10) NOT NULL DEFAULT '0' COMMENT '满额包邮，分',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '说明',
  `create_time` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `delete_time` bigint(16) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_profit_rule`
--

CREATE TABLE `dc_profit_rule` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '规则ID',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '规则名称',
  `rules` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON: 成本区间与利润比',
  `state` tinyint(1) NOT NULL DEFAULT '1' COMMENT '启用状态',
  `create_time` bigint(16) DEFAULT NULL,
  `update_time` bigint(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='批量加价规则' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_qingjiu_category_map`
--

CREATE TABLE `dc_qingjiu_category_map` (
  `id` int(11) UNSIGNED NOT NULL,
  `source_id` int(11) NOT NULL DEFAULT '0' COMMENT '货源站ID',
  `remote_cid` int(11) NOT NULL DEFAULT '0' COMMENT '晴玖分类ID',
  `local_sid` int(11) NOT NULL DEFAULT '0' COMMENT '本地分类ID',
  `create_time` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='晴玖对接分类映射' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_qingjiu_goods`
--

CREATE TABLE `dc_qingjiu_goods` (
  `id` int(11) UNSIGNED NOT NULL,
  `source_id` int(11) NOT NULL DEFAULT '0' COMMENT '货源站ID',
  `remote_gid` int(11) NOT NULL DEFAULT '0' COMMENT '晴玖商品ID',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '本地商品ID',
  `price_locked` tinyint(1) NOT NULL DEFAULT '0' COMMENT '价格锁定 1=只同步库存不同步价格',
  `markup_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed' COMMENT '加价模式 fixed/percent',
  `markup_val` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '加价值',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='晴玖对接商品关联' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_qingjiu_sale`
--

CREATE TABLE `dc_qingjiu_sale` (
  `id` int(11) UNSIGNED NOT NULL,
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '本地商品ID',
  `order_list_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单子项ID',
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '本地SKU（晴玖多规格组合名）',
  `content` text COLLATE utf8mb4_unicode_ci COMMENT '卡密内容（来自 token 字段）',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '购买份数',
  `source_id` int(11) NOT NULL DEFAULT '0' COMMENT '货源站ID',
  `source_order_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '晴玖订单号',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='晴玖对接订单记录' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_qingjiu_sku_map`
--

CREATE TABLE `dc_qingjiu_sku_map` (
  `id` int(11) UNSIGNED NOT NULL,
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '本地商品ID',
  `local_sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '本地SKU值ID组合',
  `remote_sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '晴玖远端SKU组合值',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='晴玖本地SKU与远端规格映射' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_qingjiu_sources`
--

CREATE TABLE `dc_qingjiu_sources` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '货源站名称',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '货源站域名（不含协议头）',
  `protocol` tinyint(1) NOT NULL DEFAULT '1' COMMENT '协议 1=https 0=http',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '晴玖用户ID',
  `api_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '对接密钥',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用 1/0',
  `auto_sync` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否自动同步 1/0',
  `last_sync` int(11) NOT NULL DEFAULT '0' COMMENT '上次同步时间戳',
  `last_err` text COLLATE utf8mb4_unicode_ci COMMENT '最后一次错误信息',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='晴玖对接货源站' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_recharge_card`
--

CREATE TABLE `dc_recharge_card` (
  `id` int(10) UNSIGNED NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `card_key` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `batch_no` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `admin_uid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `use_time` bigint(16) NOT NULL DEFAULT '0',
  `create_time` bigint(16) NOT NULL DEFAULT '0',
  `update_time` bigint(16) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_single_rule`
--

CREATE TABLE `dc_single_rule` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '规则ID',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '规则名称',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '加价类型 1=固定加价 2=百分比加价',
  `rules` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON: 按等级索引的 {price,profits}',
  `state` tinyint(1) NOT NULL DEFAULT '1' COMMENT '启用状态',
  `create_time` bigint(16) DEFAULT NULL,
  `update_time` bigint(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='单商品加价规则' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_skus`
--

CREATE TABLE `dc_skus` (
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `goods_id` int(10) NOT NULL COMMENT '商品id',
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '规格组合',
  `market_price` bigint(18) DEFAULT NULL COMMENT '市场价',
  `cost_price` bigint(18) DEFAULT NULL COMMENT '成本价',
  `content` text COLLATE utf8mb4_unicode_ci,
  `guest_price` bigint(18) DEFAULT NULL COMMENT '游客价格',
  `user_price` bigint(18) DEFAULT NULL COMMENT '普通用户价格',
  `post_url` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stock` int(10) DEFAULT '0' COMMENT '库存',
  `sales` int(10) DEFAULT '0' COMMENT '销量'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_skus`
--

INSERT INTO `dc_skus` (`code`, `goods_id`, `sku`, `market_price`, `cost_price`, `content`, `guest_price`, `user_price`, `post_url`, `stock`, `sales`) VALUES
(NULL, 1, '1', 1200, 900, NULL, 1000, 0, NULL, 99, 1),
(NULL, 1, '2', 3500, 2700, NULL, 3000, 0, NULL, 100, 0),
(NULL, 1, '3', 5500, 4500, NULL, 5000, 0, NULL, 100, 0),
(NULL, 1, '4', 11000, 9000, NULL, 10000, 0, NULL, 100, 0),
(NULL, 2, '5', 1500, 700, NULL, 990, 0, NULL, 100, 0),
(NULL, 2, '6', 3500, 1800, NULL, 2590, 0, NULL, 100, 0),
(NULL, 2, '7', 12000, 6000, NULL, 8800, 0, NULL, 100, 0),
(NULL, 3, '0', 3990, 1500, NULL, 2990, 0, NULL, 100, 0),
(NULL, 4, '0', 2990, 1000, NULL, 1990, 0, NULL, 100, 0),
(NULL, 5, '0', 0, 3000, NULL, 3100, 3000, NULL, 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `dc_sku_attr`
--

CREATE TABLE `dc_sku_attr` (
  `id` int(10) UNSIGNED NOT NULL,
  `type_id` int(10) DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delete_time` bigint(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_sku_attr`
--

INSERT INTO `dc_sku_attr` (`id`, `type_id`, `title`, `delete_time`) VALUES
(1, 1, '面值', NULL),
(2, 2, '时长', NULL),
(3, 3, '地址', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `dc_sku_value`
--

CREATE TABLE `dc_sku_value` (
  `id` int(10) UNSIGNED NOT NULL,
  `attr_id` int(10) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delete_time` bigint(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_sku_value`
--

INSERT INTO `dc_sku_value` (`id`, `attr_id`, `name`, `delete_time`) VALUES
(1, 1, '10元', NULL),
(2, 1, '30元', NULL),
(3, 1, '50元', NULL),
(4, 1, '100元', NULL),
(5, 2, '月卡', NULL),
(6, 2, '季卡', NULL),
(7, 2, '年卡', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `dc_sort`
--

CREATE TABLE `dc_sort` (
  `sid` int(11) UNSIGNED NOT NULL COMMENT '分类表',
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'goods',
  `sortname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分类名',
  `alias` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '别名',
  `taxis` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序序号',
  `pid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父分类ID',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '备注',
  `kw` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '关键词',
  `title` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '页面标题',
  `template` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分类模板',
  `sortimg` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分类图像',
  `sorticon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '分类图标(Remix Icon类名)',
  `page_count` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '每页文章数量',
  `station_id` int(10) NOT NULL DEFAULT '0' COMMENT '分站ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_sort`
--

INSERT INTO `dc_sort` (`sid`, `type`, `sortname`, `alias`, `taxis`, `pid`, `description`, `kw`, `title`, `template`, `sortimg`, `sorticon`, `page_count`, `station_id`) VALUES
(1, 'goods', '示例·游戏点卡', 'demo-game', 1, 0, '游戏点卡示例分类（DCSHOP 演示）', '', '', '', '', 'ri-gamepad-line', 0, 0),
(2, 'goods', '示例·会员服务', 'demo-vip', 2, 0, '会员服务示例分类（DCSHOP 演示）', '', '', '', '', 'ri-vip-crown-line', 0, 0),
(3, 'goods', '示例·软件激活', 'demo-software', 3, 0, '软件激活示例分类（DCSHOP 演示）', '', '', '', '', 'ri-key-2-line', 0, 0),
(4, 'goods', '示例·影音会员', 'demo-movie', 4, 0, '影音会员示例分类（DCSHOP 演示）', '', '', '', '', 'ri-movie-line', 0, 0),
(5, 'blog', '系统介绍', 'intro', 1, 0, 'DCSHOP 多财商城系统介绍栏目', '', '', '', '', 'ri-information-2-line', 10, 0);

-- --------------------------------------------------------

--
-- 表的结构 `dc_station`
--

CREATE TABLE `dc_station` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID',
  `user_id` int(10) DEFAULT NULL COMMENT '用户ID',
  `pid` int(10) DEFAULT NULL COMMENT '上级站点ID',
  `level_id` int(10) DEFAULT NULL COMMENT '分站等级ID',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '分店状态：1启用 0停用',
  `amount` decimal(10,2) DEFAULT NULL COMMENT '开通价格',
  `domain` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '独立域名',
  `domain_2` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '二级域名',
  `create_time` bigint(16) DEFAULT NULL COMMENT '开通时间',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site_subtitle` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '网站副标题',
  `master_sort` tinyint(1) DEFAULT NULL,
  `master_goods` tinyint(1) DEFAULT NULL,
  `domain_2_prefix` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domain_2_suffix` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `roll_notice` text COLLATE utf8mb4_unicode_ci,
  `home_notice` text COLLATE utf8mb4_unicode_ci,
  `delete_time` bigint(16) DEFAULT NULL,
  `station_unique` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '路径后缀标识（/s/{slug}）',
  `site_description` text COLLATE utf8mb4_unicode_ci COMMENT 'SEO描述',
  `site_key` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'SEO关键字',
  `log_title_style` tinyint(1) DEFAULT '0' COMMENT '详情页标题方案',
  `icp` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ICP备案号',
  `footer_info` text COLLATE utf8mb4_unicode_ci COMMENT '首页底部信息',
  `user_agreement` mediumtext COLLATE utf8mb4_unicode_ci COMMENT '用户服务协议',
  `privacy_policy` mediumtext COLLATE utf8mb4_unicode_ci COMMENT '隐私政策',
  `logo` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '网站Logo',
  `favicon` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '网站Favicon'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='分站列表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_station_goods`
--

CREATE TABLE `dc_station_goods` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID',
  `station_id` int(10) DEFAULT NULL COMMENT '分站ID',
  `goods_id` int(10) DEFAULT NULL COMMENT '商品ID',
  `premium` decimal(10,2) DEFAULT NULL COMMENT '加价百分比',
  `is_show` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'y' COMMENT '是否显示',
  `custom_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '自定义商品名'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='分站商品控制表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_station_level`
--

CREATE TABLE `dc_station_level` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID',
  `name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '分站等级名称',
  `icon` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ri-store-2-line' COMMENT 'Remix Icon图标',
  `icon_image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分店等级图片图标',
  `sort` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序权重',
  `price` decimal(10,2) DEFAULT '0.00' COMMENT '分站开通价格',
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '等级描述',
  `member_gate` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '开通门槛：需达到的会员等级ID',
  `is_station` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '分站开通权限',
  `is_domain` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '分站独立域名',
  `is_goods` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '分站供货权限',
  `perm_setprice` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'n' COMMENT '改价权限',
  `perm_goodsstate` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'n' COMMENT '上下架权限',
  `perm_tpl` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'n' COMMENT '模板权限',
  `perm_config` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'n' COMMENT '配置权限',
  `perm_deposit` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'n' COMMENT '提现权限',
  `service_change` decimal(10,2) DEFAULT NULL COMMENT '分站供货手续费',
  `cash_change` decimal(10,2) DEFAULT NULL COMMENT '提现手续费',
  `using` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '是否启用该分站等级',
  `create_time` bigint(16) DEFAULT NULL COMMENT '添加时间',
  `update_time` bigint(16) DEFAULT NULL COMMENT '编辑时间',
  `upgrade_mode` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'any' COMMENT '自动升级判断模式',
  `upgrade_sales_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '自动升级-累计销售额',
  `upgrade_order_count` int(10) NOT NULL DEFAULT '0' COMMENT '自动升级-累计订单量',
  `upgrade_days` int(10) NOT NULL DEFAULT '0' COMMENT '自动升级-运营天数',
  `upgrade_sub_count` int(10) NOT NULL DEFAULT '0' COMMENT '自动升级-下级分店数'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='分站等级' ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_station_level`
--

INSERT INTO `dc_station_level` (`id`, `name`, `icon`, `icon_image`, `sort`, `price`, `description`, `member_gate`, `is_station`, `is_domain`, `is_goods`, `perm_setprice`, `perm_goodsstate`, `perm_tpl`, `perm_config`, `perm_deposit`, `service_change`, `cash_change`, `using`, `create_time`, `update_time`, `upgrade_mode`, `upgrade_sales_amount`, `upgrade_order_count`, `upgrade_days`, `upgrade_sub_count`) VALUES
(1, '免费版', 'ri-store-line', '', 1, '0.00', '零门槛开通，仅支持配置店铺基础信息，手续费10%', 0, 'n', 'n', 'n', 'n', 'n', 'n', 'y', 'n', '0.10', '0.20', 'y', 0, 0, 'any', '0.00', 0, 0, 0),
(2, '入门版', 'ri-rocket-line', '', 2, '29.00', '解锁店铺模板更换，手续费降至8%，可个性化店铺外观', 0, 'n', 'n', 'n', 'n', 'n', 'y', 'y', 'y', '0.08', '0.15', 'y', 0, 0, 'any', '200.00', 10, 0, 0),
(3, '标准版', 'ri-building-2-line', '', 3, '99.00', '解锁独立域名、自定义商品价格和上下架管理，手续费5%', 2, 'n', 'y', 'n', 'y', 'y', 'y', 'y', 'y', '0.05', '0.08', 'y', 0, 0, 'any', '500.00', 30, 0, 0),
(4, '专业版', 'ri-vip-crown-line', '', 4, '199.00', '全部权限解锁，手续费仅3%，利润空间更大', 3, 'n', 'y', 'y', 'y', 'y', 'y', 'y', 'y', '0.03', '0.05', 'y', 0, 0, 'all', '2000.00', 50, 30, 0),
(5, '旗舰版', 'ri-vip-diamond-line', '', 5, '399.00', '全部权限解锁，手续费低至1%，适合大卖家长期深度运营', 4, 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', '0.01', '0.02', 'y', 0, 0, 'all', '5000.00', 200, 60, 3);

-- --------------------------------------------------------

--
-- 表的结构 `dc_station_plugin`
--

CREATE TABLE `dc_station_plugin` (
  `station_id` int(10) DEFAULT NULL COMMENT '分站ID',
  `plugin_id` int(11) DEFAULT NULL,
  `plugin_name_cn` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plugin_name_en` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pc_switch` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tel_switch` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_station_sort`
--

CREATE TABLE `dc_station_sort` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID',
  `station_id` int(10) DEFAULT NULL COMMENT '分站ID',
  `sort_id` int(10) DEFAULT NULL COMMENT '分类ID',
  `custom_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '自定义分类名称',
  `type` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '分类类型',
  `is_show` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '是否显示'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='分站分类控制表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_station_storage`
--

CREATE TABLE `dc_station_storage` (
  `id` int(10) UNSIGNED NOT NULL,
  `station_id` int(10) NOT NULL COMMENT '分站ID',
  `type` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'tpl or plugin',
  `plugin_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `option_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `option_value` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='分站插件模板配置表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_stock`
--

CREATE TABLE `dc_stock` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `goods_id` int(10) NOT NULL,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `content` text CHARACTER SET utf8,
  `create_time` bigint(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_stock_export_log`
--

CREATE TABLE `dc_stock_export_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `goods_id` int(10) DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `create_time` bigint(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_storage`
--

CREATE TABLE `dc_storage` (
  `sid` int(8) NOT NULL COMMENT '对象存储表',
  `plugin` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '插件名',
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '对象名',
  `type` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '对象数据类型',
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '对象值',
  `createdate` int(11) NOT NULL COMMENT '创建时间',
  `lastupdate` int(11) NOT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_storage`
--

INSERT INTO `dc_storage` (`sid`, `plugin`, `name`, `type`, `value`, `createdate`, `lastupdate`) VALUES
(1, 'epay_wx', 'url', 'string', 'https://epay.1231888.com/', 1781528817, 1781528817),
(2, 'epay_wx', 'appid', 'string', '1000', 1781528817, 1781528817),
(3, 'epay_wx', 'private_key', 'string', '67zcM44PhCDeXca37PZ3Dh17p7F6MCD6', 1781528817, 1781528817),
(4, 'epay_wx', 'name', 'string', '易支付/微信', 1781528817, 1781528817),
(5, 'epay_ali', 'url', 'string', 'https://epay.1231888.com/', 1781528829, 1781528829),
(6, 'epay_ali', 'appid', 'string', '1000', 1781528829, 1781528829),
(7, 'epay_ali', 'private_key', 'string', '67zcM44PhCDeXca37PZ3Dh17p7F6MCD6', 1781528829, 1781528829),
(8, 'epay_ali', 'name', 'string', '易支付/支付宝', 1781528829, 1781528829);

-- --------------------------------------------------------

--
-- 表的结构 `dc_tag`
--

CREATE TABLE `dc_tag` (
  `tid` int(11) UNSIGNED NOT NULL COMMENT '标签表',
  `tagname` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标签名',
  `description` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '页面描述',
  `title` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '页面标题',
  `kw` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '关键词',
  `gid` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文章ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_tag`
--

INSERT INTO `dc_tag` (`tid`, `tagname`, `description`, `title`, `kw`, `gid`) VALUES
(1, 'DCSHOP', '', '', '', '1'),
(2, '自动发卡', '', '', '', '1'),
(3, '系统介绍', '', '', '', '1'),
(4, '多财商城', '', '', '', '1'),
(5, '虚拟商品', '', '', '', '1');

-- --------------------------------------------------------

--
-- 表的结构 `dc_tpl_options_data`
--

CREATE TABLE `dc_tpl_options_data` (
  `id` int(11) UNSIGNED NOT NULL,
  `template` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `depend` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `data` longtext COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_twitter`
--

CREATE TABLE `dc_twitter` (
  `id` int(11) NOT NULL COMMENT '微语笔记表',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '微语内容',
  `img` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '图片',
  `author` int(11) NOT NULL DEFAULT '1' COMMENT '作者UID',
  `date` bigint(20) NOT NULL COMMENT '创建时间',
  `replynum` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '回复数量',
  `private` enum('n','y') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'n' COMMENT '是否私密'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_user`
--

CREATE TABLE `dc_user` (
  `uid` int(11) UNSIGNED NOT NULL COMMENT '用户表',
  `station_id` int(10) NOT NULL DEFAULT '0',
  `expend` decimal(10,2) DEFAULT '0.00' COMMENT '总消费',
  `username` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户密码',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '用户余额',
  `nickname` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '昵称',
  `level` tinyint(1) DEFAULT '0' COMMENT '用户等级',
  `role` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户组',
  `admin_group_id` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '后台账户用户组ID',
  `ischeck` enum('n','y') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'n' COMMENT '内容是否需要管理员审核',
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '头像',
  `withdraw_receipt_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '默认提现收款码',
  `withdraw_realname` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '默认提现收款姓名',
  `withdraw_account` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '默认提现收款账号',
  `withdraw_method` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '默认提现方式',
  `email` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '邮箱',
  `tel` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '手机号码',
  `wechat` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信号',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `ip` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'ip地址',
  `reg_ip` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '注册IP',
  `state` tinyint(4) NOT NULL DEFAULT '0' COMMENT '用户状态 0正常 1禁用',
  `credits` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户积分',
  `level_expire_time` bigint(16) NOT NULL DEFAULT '0' COMMENT '会员等级到期时间',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  `delete_time` bigint(16) DEFAULT NULL COMMENT '删除时间',
  `superior` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '上级用户UID,0=无上级',
  `invite_code` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '专属邀请码',
  `wechat_openid` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信 OpenID',
  `wechat_unionid` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信 UnionID',
  `wechat_nickname` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信昵称',
  `wechat_avatar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信头像',
  `qq_openid` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'QQ OpenID',
  `qq_unionid` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'QQ UnionID',
  `qq_nickname` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'QQ昵称',
  `qq_avatar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'QQ头像',
  `api_key` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'API对接密钥',
  `api_key_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'API密钥Hash',
  `api_key_create_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'API密钥创建时间',
  `api_whitelist` text COLLATE utf8mb4_unicode_ci COMMENT 'API白名单IP',
  `api_whitelist_enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否启用API白名单'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_user`
--

INSERT INTO `dc_user` (`uid`, `station_id`, `expend`, `username`, `password`, `money`, `nickname`, `level`, `role`, `admin_group_id`, `ischeck`, `photo`, `withdraw_receipt_image`, `withdraw_realname`, `withdraw_account`, `withdraw_method`, `email`, `tel`, `wechat`, `description`, `ip`, `reg_ip`, `state`, `credits`, `level_expire_time`, `create_time`, `update_time`, `delete_time`, `superior`, `invite_code`, `wechat_openid`, `wechat_unionid`, `wechat_nickname`, `wechat_avatar`, `qq_openid`, `qq_unionid`, `qq_nickname`, `qq_avatar`, `api_key`, `api_key_hash`, `api_key_create_time`, `api_whitelist`, `api_whitelist_enabled`) VALUES
(1000, 0, '13.50', 'admin', '$2y$10$FqYrQrWpFA8tfa8xjFRzOOrHsmyNCQnzUeVCslFfe//MjRrC4SUFa', '0.00', '管理员', 1, 'admin', 0, 'n', '', NULL, NULL, NULL, NULL, '', NULL, '', '', '110.182.46.34', NULL, 0, 0, 0, 1781527590, 1781582772, NULL, 0, '', '', '', '', '', '', '', '', '', '', '', 0, NULL, 0);

-- --------------------------------------------------------

--
-- 表的结构 `dc_user_goods_footprint`
--

CREATE TABLE `dc_user_goods_footprint` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `goods_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `station_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `view_count` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `first_view_time` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `last_view_time` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_user_goods_footprint`
--

INSERT INTO `dc_user_goods_footprint` (`id`, `user_id`, `goods_id`, `station_id`, `view_count`, `first_view_time`, `last_view_time`) VALUES
(1, 1000, 3, 0, 1, 1781528533, 1781528533),
(2, 1000, 1, 0, 3, 1781528834, 1781583502),
(3, 1000, 2, 0, 2, 1781533266, 1781583406),
(5, 1000, 5, 0, 1, 1781583410, 1781583410);

-- --------------------------------------------------------

--
-- 表的结构 `dc_user_log`
--

CREATE TABLE `dc_user_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `uid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
  `type` varchar(50) NOT NULL DEFAULT '' COMMENT '操作类型',
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '涉及数量/金额',
  `content` varchar(500) NOT NULL DEFAULT '' COMMENT '日志内容',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP地址',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '时间戳'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户操作日志' ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `dc_user_log`
--

INSERT INTO `dc_user_log` (`id`, `uid`, `type`, `amount`, `content`, `ip`, `create_time`) VALUES
(1, 1000, 'order_create', '13.50', '创建商品订单，订单号: 202606152107211336，商品: 【示例】游戏直充·多规格演示（请勿下单），金额: ¥13.50', '110.182.46.34', 1781528841),
(2, 1000, 'order_pay', '13.50', '订单支付，订单号: 202606152107211336，金额: ¥13.5', '110.182.46.34', 1781528857),
(3, 1000, 'order_create', '10.50', '创建商品订单，订单号: 202606152221129683，商品: 【示例】平台会员订阅·套餐演示（请勿下单），金额: ¥10.50', '110.182.46.34', 1781533272),
(4, 1000, 'order_create', '13.50', '创建商品订单，订单号: 202606161217101588，商品: 【示例】游戏直充·多规格演示（请勿下单），金额: ¥13.50', '110.182.46.34', 1781583430);

-- --------------------------------------------------------

--
-- 表的结构 `dc_withdraw`
--

CREATE TABLE `dc_withdraw` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `realname` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remark` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receipt_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '收款码图片',
  `status` tinyint(1) DEFAULT NULL,
  `create_time` bigint(16) DEFAULT NULL,
  `service_change` decimal(10,2) DEFAULT NULL,
  `actual_amount` decimal(10,2) DEFAULT NULL COMMENT '实际处理金额',
  `handle_time` bigint(16) DEFAULT NULL COMMENT '处理时间',
  `handle_remark` text COLLATE utf8mb4_unicode_ci COMMENT '处理备注',
  `finish_time` bigint(16) DEFAULT NULL,
  `reject_time` bigint(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='提现记录表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_yiciyuan_goods`
--

CREATE TABLE `dc_yiciyuan_goods` (
  `id` int(11) UNSIGNED NOT NULL,
  `source_id` int(11) NOT NULL DEFAULT '0' COMMENT '货源站ID',
  `remote_gid` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '异次元商品编码',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '本地商品ID',
  `remote_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '远端发货类型',
  `race_json` text COLLATE utf8mb4_unicode_ci COMMENT '远端种类结构 JSON',
  `sku_json` text COLLATE utf8mb4_unicode_ci COMMENT '远端 SKU 结构 JSON',
  `widget_json` text COLLATE utf8mb4_unicode_ci COMMENT '远端控件结构 JSON',
  `remote_snapshot` mediumtext COLLATE utf8mb4_unicode_ci COMMENT '远端详情原始快照 JSON',
  `last_remote_stock` int(11) NOT NULL DEFAULT '0' COMMENT '最近一次远端库存',
  `last_remote_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '最近一次远端成本价',
  `sync_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal' COMMENT '同步状态',
  `last_sync_msg` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '最近一次同步摘要',
  `price_locked` tinyint(1) NOT NULL DEFAULT '0' COMMENT '价格锁定 1=只同步库存不同步价格',
  `markup_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed' COMMENT '加价模式 fixed/percent',
  `markup_val` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '加价值',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='异次元对接商品关联' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_yiciyuan_sale`
--

CREATE TABLE `dc_yiciyuan_sale` (
  `id` int(11) UNSIGNED NOT NULL,
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '本地商品ID',
  `order_list_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单子项ID',
  `request_no` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '本站请求号',
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '本地SKU或远端规格组合',
  `content` text COLLATE utf8mb4_unicode_ci COMMENT '发货内容',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '购买份数',
  `source_id` int(11) NOT NULL DEFAULT '0' COMMENT '货源站ID',
  `source_order_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '异次元订单号',
  `remote_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '远端订单状态',
  `remote_raw` mediumtext COLLATE utf8mb4_unicode_ci COMMENT '远端订单原始响应 JSON',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='异次元对接订单记录' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_yiciyuan_sku_map`
--

CREATE TABLE `dc_yiciyuan_sku_map` (
  `id` int(11) UNSIGNED NOT NULL,
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '本地商品ID',
  `local_sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '本地SKU值ID组合',
  `remote_sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '异次元远端SKU组合值',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='异次元本地SKU与远端规格映射' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `dc_yiciyuan_sources`
--

CREATE TABLE `dc_yiciyuan_sources` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '货源站名称',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '货源站域名',
  `protocol` tinyint(1) NOT NULL DEFAULT '1' COMMENT '协议 1=https 0=http',
  `app_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '异次元 app_id',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '兼容字段',
  `api_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '异次元 app_key',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用 1/0',
  `auto_sync` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否自动同步 1/0',
  `last_sync` int(11) NOT NULL DEFAULT '0' COMMENT '上次同步时间戳',
  `last_err` text COLLATE utf8mb4_unicode_ci COMMENT '最后一次错误信息',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='异次元对接货源站' ROW_FORMAT=DYNAMIC;

--
-- 转储表的索引
--

--
-- 表的索引 `dc_admin_group`
--
ALTER TABLE `dc_admin_group`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name` (`name`);

--
-- 表的索引 `dc_aftersale`
--
ALTER TABLE `dc_aftersale`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_out_trade_no` (`out_trade_no`),
  ADD KEY `idx_status` (`status`);

--
-- 表的索引 `dc_aftersale_chat`
--
ALTER TABLE `dc_aftersale_chat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_aftersale_id` (`aftersale_id`),
  ADD KEY `idx_order_id` (`order_id`);

--
-- 表的索引 `dc_attachment`
--
ALTER TABLE `dc_attachment`
  ADD PRIMARY KEY (`aid`) USING BTREE,
  ADD KEY `thum_uid` (`thumfor`,`author`) USING BTREE,
  ADD KEY `addtime` (`addtime`) USING BTREE;

--
-- 表的索引 `dc_balance_log`
--
ALTER TABLE `dc_balance_log`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `dc_blog`
--
ALTER TABLE `dc_blog`
  ADD PRIMARY KEY (`gid`) USING BTREE,
  ADD KEY `author` (`author`) USING BTREE,
  ADD KEY `views` (`views`) USING BTREE,
  ADD KEY `comnum` (`comnum`) USING BTREE,
  ADD KEY `sortid` (`sortid`) USING BTREE,
  ADD KEY `top` (`top`,`date`) USING BTREE,
  ADD KEY `date` (`date`) USING BTREE;

--
-- 表的索引 `dc_blog_fields`
--
ALTER TABLE `dc_blog_fields`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `gid` (`gid`) USING BTREE;

--
-- 表的索引 `dc_blog_navi`
--
ALTER TABLE `dc_blog_navi`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `dc_cart`
--
ALTER TABLE `dc_cart`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `dc_comment`
--
ALTER TABLE `dc_comment`
  ADD PRIMARY KEY (`cid`) USING BTREE,
  ADD KEY `gid` (`gid`) USING BTREE,
  ADD KEY `date` (`date`) USING BTREE,
  ADD KEY `hide` (`hide`) USING BTREE;

--
-- 表的索引 `dc_coupon`
--
ALTER TABLE `dc_coupon`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `status` (`status`);

--
-- 表的索引 `dc_coupon_log`
--
ALTER TABLE `dc_coupon_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coupon_id` (`coupon_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

--
-- 表的索引 `dc_deliver`
--
ALTER TABLE `dc_deliver`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `idx_deliver_order_list` (`order_list_id`,`delete_time`) USING BTREE;

--
-- 表的索引 `dc_docking_category_map`
--
ALTER TABLE `dc_docking_category_map`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_source` (`source_id`,`source_cid`),
  ADD KEY `idx_local_cid` (`local_cid`);

--
-- 表的索引 `dc_docking_goods`
--
ALTER TABLE `dc_docking_goods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_goods` (`goods_id`),
  ADD KEY `idx_source` (`source_id`);

--
-- 表的索引 `dc_docking_sale`
--
ALTER TABLE `dc_docking_sale`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_list` (`order_list_id`);

--
-- 表的索引 `dc_docking_sources`
--
ALTER TABLE `dc_docking_sources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`);

--
-- 表的索引 `dc_free_claim_log`
--
ALTER TABLE `dc_free_claim_log`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `idx_ip_time` (`ip`,`claim_time`) USING BTREE;

--
-- 表的索引 `dc_goods`
--
ALTER TABLE `dc_goods`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `sortid` (`sort_id`) USING BTREE,
  ADD KEY `top` (`create_time`) USING BTREE,
  ADD KEY `date` (`create_time`) USING BTREE;

--
-- 表的索引 `dc_goods_general`
--
ALTER TABLE `dc_goods_general`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `dc_goods_general_sale`
--
ALTER TABLE `dc_goods_general_sale`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `dc_goods_once`
--
ALTER TABLE `dc_goods_once`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `dc_goods_service`
--
ALTER TABLE `dc_goods_service`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `dc_goods_service_sale`
--
ALTER TABLE `dc_goods_service_sale`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `dc_goods_type`
--
ALTER TABLE `dc_goods_type`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `dc_level_order`
--
ALTER TABLE `dc_level_order`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `out_trade_no` (`out_trade_no`) USING BTREE,
  ADD KEY `user_id` (`user_id`) USING BTREE,
  ADD KEY `state` (`state`) USING BTREE;

--
-- 表的索引 `dc_link`
--
ALTER TABLE `dc_link`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `dc_mcy_goods`
--
ALTER TABLE `dc_mcy_goods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_source_goods` (`source_id`,`goods_id`),
  ADD KEY `idx_source_remote` (`source_id`,`remote_gid`);

--
-- 表的索引 `dc_mcy_sale`
--
ALTER TABLE `dc_mcy_sale`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_order_list` (`order_list_id`),
  ADD UNIQUE KEY `uk_request_no` (`request_no`),
  ADD KEY `idx_source_order` (`source_id`,`source_order_id`);

--
-- 表的索引 `dc_mcy_sku_map`
--
ALTER TABLE `dc_mcy_sku_map`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_goods_local` (`goods_id`,`local_sku`(120)),
  ADD KEY `idx_goods_remote` (`goods_id`,`remote_sku`(120));

--
-- 表的索引 `dc_mcy_sources`
--
ALTER TABLE `dc_mcy_sources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`);

--
-- 表的索引 `dc_media_sort`
--
ALTER TABLE `dc_media_sort`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `dc_member`
--
ALTER TABLE `dc_member`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `dc_navi`
--
ALTER TABLE `dc_navi`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `dc_options`
--
ALTER TABLE `dc_options`
  ADD PRIMARY KEY (`option_id`) USING BTREE,
  ADD UNIQUE KEY `option_name_uindex` (`option_name`) USING BTREE;

--
-- 表的索引 `dc_order`
--
ALTER TABLE `dc_order`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `dc_order_list`
--
ALTER TABLE `dc_order_list`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `order_id` (`order_id`) USING BTREE;

--
-- 表的索引 `dc_order_required`
--
ALTER TABLE `dc_order_required`
  ADD KEY `order_id` (`order_id`) USING BTREE;

--
-- 表的索引 `dc_pay_callback_log`
--
ALTER TABLE `dc_pay_callback_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_no` (`order_no`),
  ADD KEY `idx_create_time` (`create_time`);

--
-- 表的索引 `dc_physical_address`
--
ALTER TABLE `dc_physical_address`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `uniq_uid` (`uid`);

--
-- 表的索引 `dc_physical_goods_shipping`
--
ALTER TABLE `dc_physical_goods_shipping`
  ADD PRIMARY KEY (`goods_id`) USING BTREE,
  ADD KEY `idx_template_id` (`template_id`);

--
-- 表的索引 `dc_physical_sale`
--
ALTER TABLE `dc_physical_sale`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `uniq_order_list` (`order_list_id`),
  ADD KEY `idx_goods_id` (`goods_id`),
  ADD KEY `idx_status` (`status`);

--
-- 表的索引 `dc_physical_shipping_template`
--
ALTER TABLE `dc_physical_shipping_template`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `dc_profit_rule`
--
ALTER TABLE `dc_profit_rule`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `dc_qingjiu_category_map`
--
ALTER TABLE `dc_qingjiu_category_map`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_source_remote` (`source_id`,`remote_cid`),
  ADD KEY `idx_source_local` (`source_id`,`local_sid`);

--
-- 表的索引 `dc_qingjiu_goods`
--
ALTER TABLE `dc_qingjiu_goods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_source_goods` (`source_id`,`goods_id`),
  ADD KEY `idx_source_remote` (`source_id`,`remote_gid`);

--
-- 表的索引 `dc_qingjiu_sale`
--
ALTER TABLE `dc_qingjiu_sale`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_list` (`order_list_id`),
  ADD KEY `idx_source_order` (`source_id`,`source_order_id`);

--
-- 表的索引 `dc_qingjiu_sku_map`
--
ALTER TABLE `dc_qingjiu_sku_map`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_goods_local` (`goods_id`,`local_sku`(120)),
  ADD KEY `idx_goods_remote` (`goods_id`,`remote_sku`(120));

--
-- 表的索引 `dc_qingjiu_sources`
--
ALTER TABLE `dc_qingjiu_sources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`);

--
-- 表的索引 `dc_recharge_card`
--
ALTER TABLE `dc_recharge_card`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `uniq_card_key` (`card_key`) USING BTREE,
  ADD KEY `idx_status` (`status`) USING BTREE,
  ADD KEY `idx_batch_no` (`batch_no`) USING BTREE,
  ADD KEY `idx_admin_uid` (`admin_uid`) USING BTREE,
  ADD KEY `idx_user_id` (`user_id`) USING BTREE;

--
-- 表的索引 `dc_single_rule`
--
ALTER TABLE `dc_single_rule`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `dc_sku_attr`
--
ALTER TABLE `dc_sku_attr`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `dc_sku_value`
--
ALTER TABLE `dc_sku_value`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `dc_sort`
--
ALTER TABLE `dc_sort`
  ADD PRIMARY KEY (`sid`) USING BTREE;

--
-- 表的索引 `dc_station`
--
ALTER TABLE `dc_station`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `dc_station_goods`
--
ALTER TABLE `dc_station_goods`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `dc_station_level`
--
ALTER TABLE `dc_station_level`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `idx_sort` (`sort`);

--
-- 表的索引 `dc_station_sort`
--
ALTER TABLE `dc_station_sort`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `dc_station_storage`
--
ALTER TABLE `dc_station_storage`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `tpl` (`station_id`,`type`,`plugin_name`,`option_name`) USING BTREE;

--
-- 表的索引 `dc_stock`
--
ALTER TABLE `dc_stock`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `dc_stock_export_log`
--
ALTER TABLE `dc_stock_export_log`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `dc_storage`
--
ALTER TABLE `dc_storage`
  ADD PRIMARY KEY (`sid`) USING BTREE,
  ADD UNIQUE KEY `plugin` (`plugin`,`name`) USING BTREE;

--
-- 表的索引 `dc_tag`
--
ALTER TABLE `dc_tag`
  ADD PRIMARY KEY (`tid`) USING BTREE,
  ADD KEY `tagname` (`tagname`) USING BTREE;

--
-- 表的索引 `dc_tpl_options_data`
--
ALTER TABLE `dc_tpl_options_data`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `template` (`template`,`name`) USING BTREE;

--
-- 表的索引 `dc_twitter`
--
ALTER TABLE `dc_twitter`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `author` (`author`) USING BTREE;

--
-- 表的索引 `dc_user`
--
ALTER TABLE `dc_user`
  ADD PRIMARY KEY (`uid`) USING BTREE,
  ADD KEY `username` (`username`) USING BTREE,
  ADD KEY `email` (`email`) USING BTREE,
  ADD KEY `idx_superior` (`superior`),
  ADD KEY `idx_qq_openid` (`qq_openid`),
  ADD KEY `idx_wechat_openid` (`wechat_openid`),
  ADD KEY `idx_wechat_unionid` (`wechat_unionid`);

--
-- 表的索引 `dc_user_goods_footprint`
--
ALTER TABLE `dc_user_goods_footprint`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_goods_station` (`user_id`,`goods_id`,`station_id`),
  ADD KEY `idx_user_last` (`user_id`,`last_view_time`),
  ADD KEY `idx_goods` (`goods_id`);

--
-- 表的索引 `dc_user_log`
--
ALTER TABLE `dc_user_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_uid` (`uid`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_create_time` (`create_time`);

--
-- 表的索引 `dc_withdraw`
--
ALTER TABLE `dc_withdraw`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `dc_yiciyuan_goods`
--
ALTER TABLE `dc_yiciyuan_goods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_source_goods` (`source_id`,`goods_id`),
  ADD KEY `idx_source_remote` (`source_id`,`remote_gid`);

--
-- 表的索引 `dc_yiciyuan_sale`
--
ALTER TABLE `dc_yiciyuan_sale`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_order_list` (`order_list_id`),
  ADD UNIQUE KEY `uk_request_no` (`request_no`),
  ADD KEY `idx_source_order` (`source_id`,`source_order_id`);

--
-- 表的索引 `dc_yiciyuan_sku_map`
--
ALTER TABLE `dc_yiciyuan_sku_map`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_goods_local` (`goods_id`,`local_sku`(120)),
  ADD KEY `idx_goods_remote` (`goods_id`,`remote_sku`(120));

--
-- 表的索引 `dc_yiciyuan_sources`
--
ALTER TABLE `dc_yiciyuan_sources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `dc_admin_group`
--
ALTER TABLE `dc_admin_group`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `dc_aftersale`
--
ALTER TABLE `dc_aftersale`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_aftersale_chat`
--
ALTER TABLE `dc_aftersale_chat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_attachment`
--
ALTER TABLE `dc_attachment`
  MODIFY `aid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '资源文件表', AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `dc_balance_log`
--
ALTER TABLE `dc_balance_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_blog`
--
ALTER TABLE `dc_blog`
  MODIFY `gid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '文章表', AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `dc_blog_fields`
--
ALTER TABLE `dc_blog_fields`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_blog_navi`
--
ALTER TABLE `dc_blog_navi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `dc_cart`
--
ALTER TABLE `dc_cart`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- 使用表AUTO_INCREMENT `dc_comment`
--
ALTER TABLE `dc_comment`
  MODIFY `cid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '评论表', AUTO_INCREMENT=10;

--
-- 使用表AUTO_INCREMENT `dc_coupon`
--
ALTER TABLE `dc_coupon`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_coupon_log`
--
ALTER TABLE `dc_coupon_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_deliver`
--
ALTER TABLE `dc_deliver`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_docking_category_map`
--
ALTER TABLE `dc_docking_category_map`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_docking_goods`
--
ALTER TABLE `dc_docking_goods`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_docking_sale`
--
ALTER TABLE `dc_docking_sale`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_docking_sources`
--
ALTER TABLE `dc_docking_sources`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_free_claim_log`
--
ALTER TABLE `dc_free_claim_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- 使用表AUTO_INCREMENT `dc_goods`
--
ALTER TABLE `dc_goods`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID', AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `dc_goods_general`
--
ALTER TABLE `dc_goods_general`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID', AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `dc_goods_general_sale`
--
ALTER TABLE `dc_goods_general_sale`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- 使用表AUTO_INCREMENT `dc_goods_once`
--
ALTER TABLE `dc_goods_once`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID', AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `dc_goods_service`
--
ALTER TABLE `dc_goods_service`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID', AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `dc_goods_service_sale`
--
ALTER TABLE `dc_goods_service_sale`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID', AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `dc_goods_type`
--
ALTER TABLE `dc_goods_type`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `dc_level_order`
--
ALTER TABLE `dc_level_order`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_link`
--
ALTER TABLE `dc_link`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '链接表', AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `dc_mcy_goods`
--
ALTER TABLE `dc_mcy_goods`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_mcy_sale`
--
ALTER TABLE `dc_mcy_sale`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_mcy_sku_map`
--
ALTER TABLE `dc_mcy_sku_map`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_mcy_sources`
--
ALTER TABLE `dc_mcy_sources`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_media_sort`
--
ALTER TABLE `dc_media_sort`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '资源分类表';

--
-- 使用表AUTO_INCREMENT `dc_member`
--
ALTER TABLE `dc_member`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 使用表AUTO_INCREMENT `dc_navi`
--
ALTER TABLE `dc_navi`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '导航表';

--
-- 使用表AUTO_INCREMENT `dc_options`
--
ALTER TABLE `dc_options`
  MODIFY `option_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '站点配置信息表', AUTO_INCREMENT=157;

--
-- 使用表AUTO_INCREMENT `dc_order`
--
ALTER TABLE `dc_order`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `dc_order_list`
--
ALTER TABLE `dc_order_list`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `dc_pay_callback_log`
--
ALTER TABLE `dc_pay_callback_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_physical_address`
--
ALTER TABLE `dc_physical_address`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- 使用表AUTO_INCREMENT `dc_physical_sale`
--
ALTER TABLE `dc_physical_sale`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- 使用表AUTO_INCREMENT `dc_physical_shipping_template`
--
ALTER TABLE `dc_physical_shipping_template`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- 使用表AUTO_INCREMENT `dc_profit_rule`
--
ALTER TABLE `dc_profit_rule`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '规则ID';

--
-- 使用表AUTO_INCREMENT `dc_qingjiu_category_map`
--
ALTER TABLE `dc_qingjiu_category_map`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_qingjiu_goods`
--
ALTER TABLE `dc_qingjiu_goods`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_qingjiu_sale`
--
ALTER TABLE `dc_qingjiu_sale`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_qingjiu_sku_map`
--
ALTER TABLE `dc_qingjiu_sku_map`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_qingjiu_sources`
--
ALTER TABLE `dc_qingjiu_sources`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_recharge_card`
--
ALTER TABLE `dc_recharge_card`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_single_rule`
--
ALTER TABLE `dc_single_rule`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '规则ID';

--
-- 使用表AUTO_INCREMENT `dc_sku_attr`
--
ALTER TABLE `dc_sku_attr`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `dc_sku_value`
--
ALTER TABLE `dc_sku_value`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- 使用表AUTO_INCREMENT `dc_sort`
--
ALTER TABLE `dc_sort`
  MODIFY `sid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '分类表', AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `dc_station`
--
ALTER TABLE `dc_station`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- 使用表AUTO_INCREMENT `dc_station_goods`
--
ALTER TABLE `dc_station_goods`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- 使用表AUTO_INCREMENT `dc_station_level`
--
ALTER TABLE `dc_station_level`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID', AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `dc_station_sort`
--
ALTER TABLE `dc_station_sort`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- 使用表AUTO_INCREMENT `dc_station_storage`
--
ALTER TABLE `dc_station_storage`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_stock`
--
ALTER TABLE `dc_stock`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_stock_export_log`
--
ALTER TABLE `dc_stock_export_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_storage`
--
ALTER TABLE `dc_storage`
  MODIFY `sid` int(8) NOT NULL AUTO_INCREMENT COMMENT '对象存储表', AUTO_INCREMENT=9;

--
-- 使用表AUTO_INCREMENT `dc_tag`
--
ALTER TABLE `dc_tag`
  MODIFY `tid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '标签表', AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `dc_tpl_options_data`
--
ALTER TABLE `dc_tpl_options_data`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_twitter`
--
ALTER TABLE `dc_twitter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '微语笔记表';

--
-- 使用表AUTO_INCREMENT `dc_user`
--
ALTER TABLE `dc_user`
  MODIFY `uid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户表', AUTO_INCREMENT=1001;

--
-- 使用表AUTO_INCREMENT `dc_user_goods_footprint`
--
ALTER TABLE `dc_user_goods_footprint`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- 使用表AUTO_INCREMENT `dc_user_log`
--
ALTER TABLE `dc_user_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `dc_withdraw`
--
ALTER TABLE `dc_withdraw`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_yiciyuan_goods`
--
ALTER TABLE `dc_yiciyuan_goods`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_yiciyuan_sale`
--
ALTER TABLE `dc_yiciyuan_sale`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_yiciyuan_sku_map`
--
ALTER TABLE `dc_yiciyuan_sku_map`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dc_yiciyuan_sources`
--
ALTER TABLE `dc_yiciyuan_sources`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
