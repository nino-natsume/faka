-- ============================================================
-- p2i 冒烟测试数据 — 账单管理 (admin user bill) + 操作日志 (admin log)
-- 用法: npx wrangler d1 execute faka --local --file=test/p2i-seed.sql
-- 固定 ID 段: 用户 90011-90012 / 账单 92001-92006 / 管理日志 93001-93005
-- create_time 用固定 epoch 1700000000 ~ 1700010000 (存整数, 与时区无关)
--   1700000000 / 1700002000 / 1700004000 / 1700006000 / 1700008000 / 1700010000
--   时间区间筛选测试请在用例侧按运行时本地时区渲染成 'YYYY-MM-DD HH:mm:ss' 再发送
--   (前端 laydate 就是发这种串, parseTimeValue 也按本地时区构造, 两者对称)
-- ============================================================

-- 先清干净(可重复执行)
DELETE FROM acg_manage_log WHERE id BETWEEN 93001 AND 93099;
DELETE FROM acg_bill      WHERE id BETWEEN 92001 AND 92099;
DELETE FROM acg_user      WHERE id BETWEEN 90011 AND 90099;

-- 测试用户 2 名 (账单 owner 关联)
INSERT INTO acg_user (id, username, password, salt, app_key, avatar, balance, coin, integral,
                      create_time, login_time, pid, recharge, total_coin, status, nicename,
                      alipay, wechat, wallet_address, settlement) VALUES
(90011, 'p2i_u1', 'x', 'AcgFaka2026WorkerD1Salt0123456789', 'p2ikey1', NULL, 20.00, 0.00, 0,
 1700000000, 1700000000, 0, 0.00, 0.00, 1, '账单一',
 'p2i_alipay@qq.com', NULL, NULL, 1),
(90012, 'p2i_u2', 'x', 'AcgFaka2026WorkerD1Salt0123456789', 'p2ikey2', NULL, 30.00, 0.00, 0,
 1700000000, 1700000000, 0, 0.00, 0.00, 1, '账单二',
 NULL, 'wx_p2i_001', NULL, 2);

-- 账单
-- 92001: u1 / 消费 1 / CNY  / p2i 关键字 / 最早
-- 92002: u1 / 充值 2 / CNY  / p2i 关键字
-- 92003: u1 / 退款 3 / USD  (原版测试用货币2)
-- 92004: u2 / 消费 1 / CNY  / p2i 关键字
-- 92005: u2 / 佣金 4 / CNY  / 混入非 p2i 关键字(用于 search 负例)
-- 92006: u1 / 消费 1 / CNY  / 最新
INSERT INTO acg_bill (id, owner, amount, balance, type, currency, log, create_time) VALUES
(92001, 90011,  1.00,  19.00, 1, 0, 'p2i 购买商品',     1700000000),
(92002, 90011, 10.00,  29.00, 2, 0, 'p2i 账户充值',     1700002000),
(92003, 90011, -1.00, 28.00, 3, 1, 'p2i 订单退款',     1700004000),
(92004, 90012,  2.00,  28.00, 1, 0, 'p2i 购买商品 B',   1700006000),
(92005, 90012,  0.50,  27.50, 4, 0, '商家结算收益',     1700008000),
(92006, 90011,  3.00,  30.00, 1, 0, 'p2i 再次消费',     1700010000);

-- 管理日志
-- 93001: email 含 p2i / risk=0 / content 含 p2i / 最早
-- 93002: email 含 p2i / risk=1 (风险操作)
-- 93003: email 含 p2i / risk=0
-- 93004: email 不含 p2i (负例) / content 含 p2i
-- 93005: email 含 p2i / content 不含 p2i (负例)
INSERT INTO acg_manage_log (id, email, nickname, content, create_time, create_ip, ua, risk) VALUES
(93001, 'p2i_admin1@test.com', '管理员一', 'p2i 登录后台',         1700000000, '10.0.0.1', 'p2i-ua/1.0', 0),
(93002, 'p2i_admin1@test.com', '管理员一', 'p2i 删除商品 id=1',    1700002000, '10.0.0.2', 'p2i-ua/1.0', 1),
(93003, 'p2i_admin2@test.com', '管理员二', 'p2i 修改配置',         1700004000, '10.0.0.3', 'p2i-ua/2.0', 0),
(93004, 'other@test.com',       '其他账号', 'p2i 越权尝试',         1700006000, '10.0.0.4', 'other-ua/1',  1),
(93005, 'p2i_admin3@test.com', '管理员三', '普通操作无关键字',     1700008000, '10.0.0.5', 'p2i-ua/3.0', 0);
