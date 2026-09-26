-- ============================================================
-- p2h 冒烟测试数据 — 提现管理 (admin cash)
-- 用法: npx wrangler d1 execute faka --local --file=test/p2h-seed.sql
-- 固定 ID 段: 用户 90001-90003 / 提现单 91001-91009
-- ============================================================

-- 先清干净(可重复执行)
DELETE FROM acg_cash      WHERE id BETWEEN 91001 AND 91099;
DELETE FROM acg_bill      WHERE owner BETWEEN 90001 AND 90099;
DELETE FROM acg_manage_log WHERE content LIKE '%p2h%';
DELETE FROM acg_user      WHERE id BETWEEN 90001 AND 90099;

-- 测试用户 3 名
-- u1: 支付宝收款, balance=10.00   (待通过单 + 历史驳回单)
-- u2: 微信收款,   balance=0.00    (待驳回单, 用于验证退款 300+5)
-- u3: USDT 收款,  coin=88.88      (用于一键自动结算验证)
INSERT INTO acg_user (id, username, password, salt, app_key, avatar, balance, coin, integral,
                      create_time, login_time, pid, recharge, total_coin, status, nicename,
                      alipay, wechat, wallet_address, settlement) VALUES
(90001, 'p2h_u1', 'x', 'AcgFaka2026WorkerD1Salt0123456789', 'p2hkey1', NULL, 10.00, 0.00, 0,
 1700000000, 1700000000, 0, 0.00, 0.00, 1, '测试一',
 'p2h_alipay@qq.com', NULL, NULL, 1),
(90002, 'p2h_u2', 'x', 'AcgFaka2026WorkerD1Salt0123456789', 'p2hkey2', NULL, 0.00, 0.00, 0,
 1700000000, 1700000000, 0, 0.00, 0.00, 1, '测试二',
 NULL, 'wx_p2h_001', NULL, 2),
(90003, 'p2h_u3', 'x', 'AcgFaka2026WorkerD1Salt0123456789', 'p2hkey3', NULL, 0.00, 88.88, 0,
 1700000000, 1700000000, 0, 0.00, 0.00, 1, '测试三',
 NULL, NULL, '0xP2HADDRESS888', 3);

-- 提现单
-- 91001: 待处理 / 普通 / 支付宝 / 200 + 5费   -> 测试「通过」
-- 91002: 待处理 / 佣金 / 微信   / 300 + 5费   -> 测试「驳回退款」
-- 91003: 已驳回 / 普通 / 支付宝 / 100 + 5费   -> 状态筛选/合计
-- 91004: 已通过 / 普通 / 支付宝 / 150 + 5费   -> 状态筛选
INSERT INTO acg_cash (id, user_id, amount, type, card, create_time, arrive_time, cost, status, message) VALUES
(91001, 90001, 200.00, 0, 1, 1700000100, NULL,         5.00, 0, NULL),
(91002, 90002, 300.00, 1, 2, 1700000200, NULL,         5.00, 0, NULL),
(91003, 90001, 100.00, 0, 1, 1700000300, 1700000400,  5.00, 2, '历史驳回记录'),
(91004, 90001, 150.00, 0, 1, 1700000500, 1700000600,  5.00, 1, NULL);
