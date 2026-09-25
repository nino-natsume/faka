-- ============================================================
-- DCSHOP Cloudflare 部署版 - 种子数据 (演示)
-- 使用: wrangler d1 execute faka-db --file=./seed.sql
-- 数据源自原项目 shujuku7777777.sql 的演示内容
-- ============================================================

-- 站点配置 ====================================================
INSERT OR IGNORE INTO dc_options (option_name, option_value) VALUES
('blogname', 'DCSHOP多财商城'),
('blogurl', '/'),
('site_title', 'DCSHOP发卡系统'),
('site_subtitle', '订单问题请查看买家帮助'),
('site_key', '自动发卡,DCSHOP,虚拟商品'),
('site_description', 'DCSHOP 多财商城 - Cloudflare 部署版发卡系统'),
('footer_info', 'Powered by DuoCai | DCSHOP提供技术支持'),
('timezone', 'Asia/Shanghai'),
('sales_switch', 'y'),
('stock_switch', 'y'),
('balance_switch', 'y'),
('virtual_currency_name', '积分'),
('order_goods_img_switch', 'y'),
('pay_redirect', 'list'),
('kami_order', 'asc'),
('continue_pay_timeout', '30'),
('order_required', '[{"name":"联系信息","placeholder":"请输入手机号，或字母数字组合","type":"string"}]'),
('icp', ''),
('logo', ''),
('admin_favicon', ''),
('roll_bulletin', '欢迎来到 DCSHOP 多财商城，专业的自动发卡平台！'),
('home_bulletin', '<p style="margin:0 0 10px;line-height:1.85;color:#333;font-size:14px;"><i class="ri-megaphone-line" style="color:#ff6600;margin-right:6px;font-size:16px;"></i><strong style="color:#222;">欢迎来到 DCSHOP 多财商城！</strong>本站基于 DCSHOP 自动发卡系统搭建，全部商品 <strong style="color:#e53e3e;">7×24 小时全自动发货</strong>，下单即时收货。</p>'),
('login_switch', 'y'),
('register_switch', 'y'),
('withdraw_switch', '1'),
('detect_url', 'n'),
('station_slug_mode', '1');

-- 模板配置 (默认主题色 = 静态 theme.css 一致) =================
INSERT OR IGNORE INTO dc_tpl_options_data (template, name, depend, data) VALUES
('front_default', 'theme_primary', '', 's:7:"#2196F3";'),
('front_default', 'theme_price', '', 's:7:"#ff6600";'),
('front_default', 'theme_button', '', 's:7:"#2f69d9";'),
('front_default', 'theme_accent', '', 's:7:"#ff9800";');

-- 商品分类 =====================================================
INSERT OR IGNORE INTO dc_sort (sid, type, sortname, alias, taxis, pid, description, sorticon) VALUES
(1, 'goods', '示例·游戏点卡', 'demo-game', 1, 0, '游戏点卡示例分类（DCSHOP 演示）', 'ri-gamepad-line'),
(2, 'goods', '示例·会员服务', 'demo-vip', 2, 0, '会员服务示例分类（DCSHOP 演示）', 'ri-vip-crown-line'),
(3, 'goods', '示例·软件激活', 'demo-software', 3, 0, '软件激活示例分类（DCSHOP 演示）', 'ri-key-2-line'),
(4, 'goods', '示例·影音会员', 'demo-movie', 4, 0, '影音会员示例分类（DCSHOP 演示）', 'ri-movie-line');

-- 商品类型 =====================================================
INSERT OR IGNORE INTO dc_goods_type (id, name, hide) VALUES
(1, '充值面值', 'n'),
(2, '会员时长', 'n'),
(3, '收货地址', 'n');

-- 规格属性 =====================================================
INSERT OR IGNORE INTO dc_sku_attr (id, type_id, title) VALUES
(1, 1, '面值'),
(2, 2, '时长'),
(3, 3, '地址');

INSERT OR IGNORE INTO dc_sku_value (id, attr_id, name) VALUES
(1, 1, '10元'), (2, 1, '30元'), (3, 1, '50元'), (4, 1, '100元'),
(5, 2, '月卡'), (6, 2, '季卡'), (7, 2, '年卡');

-- 商品 =========================================================
INSERT OR IGNORE INTO dc_goods (id, station_id, des, sort_num, index_top, type, attr_id, is_sku, title, unit_name, is_on_shelf, create_time, content, pay_content, sort_id, sales, stock) VALUES
(1, 0, '【演示】游戏充值分类示例 · 多规格虚拟服务 · 请勿下单', 10, 1, 'service', 1, 'y', '【示例】游戏直充·多规格演示（请勿下单）', '次', 1, 1781527589, '<div style="padding:16px;line-height:1.85;color:#333;font-size:14px;"><div style="background:#fff3cd;border-left:4px solid #ff9800;padding:12px 14px;border-radius:6px;margin-bottom:18px;color:#856404;"><b>此商品仅为系统演示用，仅作展示，请勿下单！</b></div><p style="margin:0 0 14px;">DCSHOP 多财商城：专业的自动发卡商城系统，覆盖商品发布、订单管理、卡密自动发货等完整电商能力。</p></div>', '付款后请留意订单结果页，商家将按您填写的信息处理。', 1, 1, 99),
(2, 0, '【演示】平台会员分类示例 · 多规格通用卡密 · 请勿下单', 9, 1, 'general', 2, 'y', '【示例】平台会员订阅·套餐演示（请勿下单）', '张', 1, 1781527589, '<div style="padding:16px;line-height:1.85;color:#333;font-size:14px;"><div style="background:#fff3cd;border-left:4px solid #ff9800;padding:12px 14px;border-radius:6px;margin-bottom:18px;color:#856404;"><b>此商品仅为系统演示用，仅作展示，请勿下单！</b></div><p style="margin:0 0 14px;">通用卡密自动发货：付款后立即发放卡密，7×24 小时全自动。</p></div>', '卡密可在订单结果页查看与复制。', 2, 0, 100),
(3, 0, '【演示】软件激活分类示例 · 一卡一密自动发货 · 请勿下单', 8, 1, 'once', 0, 'n', '【示例】专业软件激活码·演示版（请勿下单）', '套', 1, 1781527589, '<div style="padding:16px;line-height:1.85;color:#333;font-size:14px;"><div style="background:#fff3cd;border-left:4px solid #ff9800;padding:12px 14px;border-radius:6px;margin-bottom:18px;color:#856404;"><b>此商品仅为系统演示用，仅作展示，请勿下单！</b></div><p style="margin:0 0 14px;">一卡一密：每笔订单发放唯一激活码，售出即标记。</p></div>', '激活码将在订单结果页展示。', 3, 0, 100),
(4, 0, '【演示】影音会员分类示例 · 通用卡密自动发货 · 请勿下单', 7, 1, 'general', 0, 'n', '【示例】影音平台月度会员·演示版（请勿下单）', '张', 1, 1781527589, '<div style="padding:16px;line-height:1.85;color:#333;font-size:14px;"><div style="background:#fff3cd;border-left:4px solid #ff9800;padding:12px 14px;border-radius:6px;margin-bottom:18px;color:#856404;"><b>此商品仅为系统演示用，仅作展示，请勿下单！</b></div><p style="margin:0 0 14px;">通用卡密自动发货。</p></div>', '卡密可在订单结果页查看与复制。', 4, 0, 100),
(5, 0, '测试商品，小K网源码网', 0, 0, 'service', 0, 'n', '小K网会员', '个', 1, 1781582967, '<div style="padding:16px;line-height:1.85;color:#333;font-size:14px;"><p style="margin:0;">人工服务示例商品。</p></div>', '', 2, 0, 0);

-- SKU ==========================================================
INSERT OR IGNORE INTO dc_skus (goods_id, sku, market_price, cost_price, guest_price, user_price, stock, sales) VALUES
(1, '1', 1200, 900, 1000, 0, 99, 1),
(1, '2', 3500, 2700, 3000, 0, 100, 0),
(1, '3', 5500, 4500, 5000, 0, 100, 0),
(1, '4', 11000, 9000, 10000, 0, 100, 0),
(2, '5', 1500, 700, 990, 0, 100, 0),
(2, '6', 3500, 1800, 2590, 0, 100, 0),
(2, '7', 12000, 6000, 8800, 0, 100, 0),
(3, '0', 3990, 1500, 2990, 0, 100, 0),
(4, '0', 2990, 1000, 1990, 0, 100, 0),
(5, '0', 0, 3000, 3100, 3000, 0, 0);

-- 一卡一密 =====================================================
INSERT OR IGNORE INTO dc_goods_once (goods_id, sku, batch_no, content, create_time, order_list_id) VALUES
(3, '0', 'DEMO-SOFT', 'DCSHOP-DEMO-SOFT-KEY-0001', 1781527589, 0),
(3, '0', 'DEMO-SOFT', 'DCSHOP-DEMO-SOFT-KEY-0002', 1781527589, 0),
(3, '0', 'DEMO-SOFT', 'DCSHOP-DEMO-SOFT-KEY-0003', 1781527589, 0),
(3, '0', 'DEMO-SOFT', 'DCSHOP-DEMO-SOFT-KEY-0004', 1781527589, 0),
(3, '0', 'DEMO-SOFT', 'DCSHOP-DEMO-SOFT-KEY-0005', 1781527589, 0);

-- 通用卡密 =====================================================
INSERT OR IGNORE INTO dc_goods_general (goods_id, sku, content, create_time) VALUES
(2, '5', 'DCSHOP-DEMO-平台会员-月卡-通用卡密（仅演示，请勿真实使用）', 1781527589),
(2, '6', 'DCSHOP-DEMO-平台会员-季卡-通用卡密（仅演示，请勿真实使用）', 1781527589),
(2, '7', 'DCSHOP-DEMO-平台会员-年卡-通用卡密（仅演示，请勿真实使用）', 1781527589),
(4, '0', 'DCSHOP-DEMO-月度会员-通用卡密（仅演示，请勿真实使用）', 1781527589);

-- 虚拟服务说明 =================================================
INSERT OR IGNORE INTO dc_goods_service (goods_id, content, sku, create_time) VALUES
(1, '【演示】游戏充值10元：付款后商家会按买家填写的信息处理，请勿真实下单。', '1', 1781527589),
(1, '【演示】游戏充值30元：付款后商家会按买家填写的信息处理，请勿真实下单。', '2', 1781527589),
(1, '【演示】游戏充值50元：付款后商家会按买家填写的信息处理，请勿真实下单。', '3', 1781527589),
(1, '【演示】游戏充值100元：付款后商家会按买家填写的信息处理，请勿真实下单。', '4', 1781527589);

-- 管理员示例 (admin / 需在 Worker 环境变量中设置 ADMIN_USERNAME/ADMIN_PASSWORD)
INSERT OR IGNORE INTO dc_user (uid, username, password, nickname, role, money, create_time, update_time) VALUES
(1000, 'admin', '', '管理员', 'admin', 0, 1781527590, 1781582772);