-- ============================================================
-- p2j 冒烟测试数据 — 支付接口管理 (admin pay) + 支付插件 (pay plugin)
-- 用法: npx wrangler d1 execute faka --local --file=test/p2j-seed.sql
-- 固定 ID 段: 用户 94001 / 支付接口 94101-94105 / 配置档 94201-94203 / 订单 94301-94302
-- create_time 固定 epoch (存整数, 与时区无关)
--   测试自己的数据用 94xxx 段, 与 p2i 的 90xxx/92xxx/93xxx 完全不重叠
-- ============================================================

-- 先清干净(可重复执行)
DELETE FROM acg_order          WHERE id BETWEEN 94301 AND 94399;
DELETE FROM acg_pay            WHERE id BETWEEN 94101 AND 94199;
DELETE FROM acg_pay_config     WHERE id BETWEEN 94201 AND 94299;
DELETE FROM acg_pay_plugin_log WHERE handle = 'Epay';
DELETE FROM acg_user           WHERE id BETWEEN 94001 AND 94099;

-- 支付配置档(多套, 覆盖 默认档 / in_use / 同名校验)
-- 94201 是 id 最小的一套 → 默认档(不可删)
INSERT INTO acg_pay_config (id, handle, name, config, sort, create_time, update_time) VALUES
(94201, 'Epay', '支付宝配置', '{"gateway":"https://pay-a.example.com","pid":"9001","key":"secret-alpha-123456"}', 1, 1700002000, 1700002000),
(94202, 'Epay', '微信配置',   '{"gateway":"https://pay-b.example.com","pid":"9002","key":"secret-bravo-654321"}', 2, 1700003000, 1700003000),
(94203, 'Epay', '备用配置',   '{"gateway":"https://pay-c.example.com","pid":"9003","key":"secret-charlie-000000"}', 3, 1700004000, 1700004000);

-- 支付接口
-- 94101: 完全空闲 + 停用 → 可物理删除
-- 94102: 有历史订单 + 停用 → 只能归档
-- 94103: 仍启用商品下单 → 删除被阻断
-- 94104: 仍启用余额充值 → 删除被阻断
-- 94105: 已归档 + 停用 → 出现在已归档列表, 可恢复
INSERT INTO acg_pay (id, name, icon, code, commodity, recharge, create_time, handle, pay_config_id, sort, equipment, cost, cost_type, archived) VALUES
(94101, 'p2j空闲测试', '/assets/user/images/cash/alipay.png', 'alipay', 0, 0, 1700002000, 'Epay', 94201, 9001, 0, 0.000, 0, 0),
(94102, 'p2j有订单测试', '/assets/user/images/cash/wechat.png', 'wxpay',  0, 0, 1700003000, 'Epay', 94202, 9002, 0, 0.000, 0, 0),
(94103, 'p2j启用下单测试', '/assets/user/images/cash/alipay.png', 'alipay', 1, 0, 1700004000, 'Epay', 94201, 9003, 0, 0.000, 0, 0),
(94104, 'p2j启用充值测试', '/assets/user/images/cash/wechat.png', 'wxpay',  0, 1, 1700005000, 'Epay', 94201, 9004, 0, 0.000, 0, 0),
(94105, 'p2j已归档测试', '/assets/user/images/cash/alipay.png', 'qqpay', 0, 0, 1700006000, 'Epay', 94203, 9005, 0, 0.000, 0, 1);

-- 历史订单引用 94102(区分已完成 / 待付款)
INSERT INTO acg_user (id, username, password, salt, app_key, avatar, balance, coin, integral,
                     create_time, login_time, pid, recharge, total_coin, status, nicename,
                     alipay, wechat, wallet_address, settlement) VALUES
(94001, 'p2j_u1', 'x', 'AcgFaka2026WorkerD1Salt0123456789', 'p2jkey1', NULL, 50.00, 0.00, 0,
 1700001000, 1700001000, 0, 0.00, 0.00, 1, '支付测试一',
 NULL, NULL, NULL, 1);

INSERT INTO acg_order (id, trade_no, owner, user_id, amount, commodity_id, pay_id, create_time,
                       create_ip, create_device, status, cost, pay_cost) VALUES
(94301, 'P2J2024010000001', 94001, 94001, 10.00, 9001, 94102, 1700003000, '127.0.0.1', 1, 1, 0.00, 0.00),
(94302, 'P2J2024010000002', 94001, 94001, 20.00, 9001, 94102, 1700003000, '127.0.0.1', 1, 0, 0.00, 0.00);
