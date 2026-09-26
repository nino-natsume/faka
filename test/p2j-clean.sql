-- ============================================================
-- p2j clean — 清除 test/p2j-seed.sql 写入的数据
-- 顺序: 先订单(被引用方), 再支付接口, 再配置档, 最后用户/日志
-- ============================================================
DELETE FROM acg_order          WHERE id BETWEEN 94301 AND 94399;
DELETE FROM acg_user_recharge  WHERE pay_id BETWEEN 94101 AND 94199;
DELETE FROM acg_pay            WHERE id BETWEEN 94101 AND 94199;
DELETE FROM acg_pay_config     WHERE id BETWEEN 94201 AND 94299;
DELETE FROM acg_pay_plugin_log WHERE handle IN ('Epay', '#system');
DELETE FROM acg_user           WHERE id BETWEEN 94001 AND 94099;

-- p2j 会在 acg_manage_log 留下操作日志(新增/修改/归档/配置档等),
-- 不清掉会挤占 log/data 的 15 条分页窗口, 影响 p2i 的日志断言
DELETE FROM acg_manage_log WHERE content LIKE '%9410%' OR content LIKE '%9420%'
  OR content LIKE '%p2j%' OR content LIKE '%支付插件(Epay)%';
