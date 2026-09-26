-- ============================================================
-- p2i 冒烟测试数据清理 — 账单 + 操作日志
-- 用法: npx wrangler d1 execute faka --local --file=test/p2i-clean.sql
-- ============================================================
DELETE FROM acg_manage_log WHERE id BETWEEN 93001 AND 93099;
DELETE FROM acg_bill      WHERE id BETWEEN 92001 AND 92099;
DELETE FROM acg_user      WHERE id BETWEEN 90011 AND 90099;
