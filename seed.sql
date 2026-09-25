-- ============================================================
-- acg-faka v3.7.9 移植 - Cloudflare D1 Seed 数据
-- 管理员默认账号: admin / admin123  (密码算法 sha1(md5(md5(pass).md5(salt))))
-- 使用: wrangler d1 execute <db> --file=./seed.sql
-- ============================================================

-- 站点配置 (61 项, 对齐原版 Install.sql)
INSERT OR IGNORE INTO acg_config (id, key, value) VALUES
(1,  'shop_name', '异次元店铺'),
(2,  'title', '异次元店铺 - 最适合你的个人店铺系统！'),
(3,  'description', ''),
(4,  'keywords', ''),
(14, 'user_theme', 'Cartoon'),
(5,  'registered_state', '1'),
(6,  'registered_type', '0'),
(7,  'registered_verification', '1'),
(8,  'registered_phone_verification', '0'),
(9,  'registered_email_verification', '0'),
(10, 'sms_config', '{"accessKeyId":"","accessKeySecret":"","signName":"","templateCode":""}'),
(11, 'email_config', '{"smtp":"","port":"","username":"","password":""}'),
(12, 'login_verification', '1'),
(13, 'forget_type', '0'),
(15, 'notice', '<p><b><font color="#f9963b">本程序为开源程序，使用者造成的一切法律后果与作者无关。</font></b></p>'),
(16, 'trade_verification', '1'),
(17, 'recharge_welfare', '0'),
(18, 'recharge_welfare_config', ''),
(19, 'promote_rebate_v1', '0.1'),
(20, 'promote_rebate_v2', '0.2'),
(21, 'promote_rebate_v3', '0.3'),
(22, 'substation_display', '1'),
(24, 'domain', ''),
(25, 'service_qq', ''),
(26, 'service_url', ''),
(27, 'cash_type_alipay', '1'),
(28, 'cash_type_wechat', '1'),
(29, 'cash_cost', '5'),
(30, 'cash_min', '100'),
(31, 'cname', ''),
(32, 'background_url', '/assets/admin/images/login/bg.jpg'),
(33, 'default_category', '0'),
(34, 'substation_display_list', '[]'),
(35, 'closed', '0'),
(36, 'closed_message', '我们正在升级，请耐心等待完成。'),
(37, 'recharge_min', '10'),
(38, 'recharge_max', '1000'),
(39, 'user_mobile_theme', '0'),
(40, 'commodity_recommend', '0'),
(41, 'commodity_name', '推荐'),
(42, 'background_mobile_url', ''),
(43, 'username_len', '6'),
(44, 'cash_type_balance', '0'),
(45, 'callback_domain', ''),
(46, 'session_expire', '0'),
(47, 'cash_type_usdt', '1'),
(48, 'user_center_theme', 'MountFuji'),
(49, 'user_center_mobile_theme', '0'),
(50, 'callback_ip_whitelist', '0'),
(51, 'callback_ip_whitelist_rules', ''),
(52, 'force_login', '0'),
(53, 'admin_login_verification', '1'),
(54, 'request_log', '0'),
(55, 'admin_entrance', ''),
(56, 'lang_version', '0'),
(57, 'currency_code', 'CNY'),
(58, 'currency_symbol', '¥'),
(59, 'currency_rate', '1'),
(60, 'currency_decimals', '2'),
(61, 'csp_mode', 'enforce');

-- 商户等级
INSERT OR IGNORE INTO acg_business_level (id, name, icon, cost, accrual, substation, top_domain, price, supplier) VALUES
(1, '体验版', '/assets/static/images/business/v1.png', 0.30, 0.10, 1, 0, 188.00, 1),
(3, '普通版', '/assets/static/images/business/v2.png', 0.25, 0.15, 1, 0, 288.00, 1),
(4, '专业版', '/assets/static/images/business/v3.png', 0.20, 0.20, 1, 1, 388.00, 1);

-- 会员等级
INSERT OR IGNORE INTO acg_user_group (id, name, icon, discount_config, cost, recharge) VALUES
(1, '一贫如洗', '/assets/static/images/group/ic_user level_1.png', NULL, 0.30, 0.00),
(2, '小康之家', '/assets/static/images/group/ic_user level_2.png', NULL, 0.25, 50.00),
(3, '腰缠万贯', '/assets/static/images/group/ic_user level_3.png', NULL, 0.20, 100.00),
(4, '富甲一方', '/assets/static/images/group/ic_user level_4.png', NULL, 0.15, 200.00),
(5, '富可敌国', '/assets/static/images/group/ic_user level_5.png', NULL, 0.10, 300.00),
(6, '至尊', '/assets/static/images/group/ic_user level_6.png', NULL, 0.05, 500.00);

-- 演示分类
INSERT OR IGNORE INTO acg_category (id, name, sort, create_time, owner, icon, status, hide, pid) VALUES
(1, 'DEMO', 1, 1700000000, 0, '/favicon.ico', 1, 0, NULL);

-- 演示商品
INSERT OR IGNORE INTO acg_commodity (id, category_id, name, description, cover, factory_price, price, user_price, status, owner, create_time, api_status, code, delivery_way, delivery_auto_mode, contact_type, password_status, sort, coupon, stock, seckill_status, draft_status, inventory_hidden, recommend, only_user, minimum, maximum) VALUES
(1, 1, 'DEMO', '<p>该商品是演示商品</p>', '/favicon.ico', 0.00, 1.00, 0.90, 1, 0, 1700000000, 1, '8AE80574F3CA98BE', 1, 0, 0, 0, 1, 1, 999999, 0, 0, 0, 0, 0, 0, 0);

-- 管理员 (admin / admin123)
INSERT OR IGNORE INTO acg_manage (id, email, password, security_password, nickname, salt, avatar, status, type, create_time, note) VALUES
(1, 'admin', '8601ff2b97eeffff68cb37ea6da3511933e94eb6', NULL, '管理员', 'AcgFaka2026WorkerD1Salt0123456789', '/favicon.ico', 1, 0, 1700000000, '系统初始管理员');

-- 支付方式 (余额 + 易支付支付宝)
INSERT OR IGNORE INTO acg_pay (id, name, icon, code, commodity, recharge, create_time, handle, pay_config_id, sort, equipment, cost, cost_type, archived) VALUES
(1, '余额', '/assets/static/images/wallet.png', '#system', 1, 0, 1700000000, '#system', 0, 999, 0, 0.000, 0, 0),
(2, '支付宝', '/assets/user/images/cash/alipay.png', 'alipay', 1, 1, 1700000000, 'Epay', 0, 1, 0, 0.000, 0, 0);

-- 会员 uid 从 1000 开始 (对齐原版, 防枚举; 若 D1 报错可忽略此行)
UPDATE sqlite_sequence SET seq = 1000 WHERE name = 'acg_user';