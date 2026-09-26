-- ============================================================
-- p2h 冒烟测试数据清理
-- 用法: npx wrangler d1 execute faka --local --file=test/p2h-clean.sql
-- ============================================================
DELETE FROM acg_cash       WHERE id BETWEEN 91001 AND 91099;
DELETE FROM acg_bill       WHERE owner BETWEEN 90001 AND 90099;
DELETE FROM acg_manage_log WHERE content LIKE '%p2h%';
DELETE FROM acg_user       WHERE id BETWEEN 90001 AND 90099;
