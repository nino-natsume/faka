-- ============================================================
-- acg-faka v3.7.9 完整移植 - Cloudflare D1/SQLite Schema
-- 30 张表, 表名/字段与原版 Install.sql 完全一致 (前缀 acg_)
-- 转换规则: datetime -> INTEGER(unix秒), json -> TEXT,
--           decimal/float -> REAL, 去 FOREIGN KEY/ENGINE/CHARSET
-- 使用: wrangler d1 execute <db> --file=./schema.sql
-- ============================================================

-- 余额流水
CREATE TABLE IF NOT EXISTS acg_bill (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  owner INTEGER NOT NULL,
  amount REAL NOT NULL,
  balance REAL NOT NULL,
  type INTEGER NOT NULL,
  currency INTEGER NOT NULL DEFAULT 0,
  log TEXT NOT NULL,
  create_time INTEGER NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_bill_owner ON acg_bill(owner);
CREATE INDEX IF NOT EXISTS idx_bill_type ON acg_bill(type);

-- 店铺(商户)信息
CREATE TABLE IF NOT EXISTS acg_business (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  shop_name TEXT,
  title TEXT,
  notice TEXT,
  service_qq TEXT,
  service_url TEXT,
  subdomain TEXT,
  topdomain TEXT,
  master_display INTEGER NOT NULL DEFAULT 0,
  create_time INTEGER NOT NULL
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_business_user ON acg_business(user_id);
CREATE UNIQUE INDEX IF NOT EXISTS uq_business_subdomain ON acg_business(subdomain);
CREATE UNIQUE INDEX IF NOT EXISTS uq_business_topdomain ON acg_business(topdomain);

-- 商户等级
CREATE TABLE IF NOT EXISTS acg_business_level (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  icon TEXT,
  cost REAL NOT NULL DEFAULT 0.00,
  accrual REAL NOT NULL DEFAULT 0.00,
  substation INTEGER NOT NULL DEFAULT 0,
  top_domain INTEGER NOT NULL DEFAULT 0,
  price REAL NOT NULL DEFAULT 0.00,
  supplier INTEGER NOT NULL DEFAULT 1
);

-- 卡密
CREATE TABLE IF NOT EXISTS acg_card (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  owner INTEGER NOT NULL DEFAULT 0,
  commodity_id INTEGER NOT NULL,
  draft TEXT,
  secret TEXT NOT NULL,
  create_time INTEGER NOT NULL,
  purchase_time INTEGER,
  order_id INTEGER,
  status INTEGER NOT NULL DEFAULT 0,
  note TEXT,
  race TEXT,
  sku TEXT,
  draft_premium REAL,
  cost REAL NOT NULL DEFAULT 0
);
CREATE INDEX IF NOT EXISTS idx_card_owner ON acg_card(owner);
CREATE INDEX IF NOT EXISTS idx_card_commodity ON acg_card(commodity_id);
CREATE INDEX IF NOT EXISTS idx_card_order ON acg_card(order_id);
CREATE INDEX IF NOT EXISTS idx_card_secret ON acg_card(secret);
CREATE INDEX IF NOT EXISTS idx_card_status ON acg_card(status);
CREATE INDEX IF NOT EXISTS idx_card_note ON acg_card(note);
CREATE INDEX IF NOT EXISTS idx_card_race ON acg_card(race);

-- 提现
CREATE TABLE IF NOT EXISTS acg_cash (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  amount REAL NOT NULL,
  type INTEGER NOT NULL DEFAULT 0,
  card INTEGER NOT NULL,
  create_time INTEGER NOT NULL,
  arrive_time INTEGER,
  cost REAL NOT NULL DEFAULT 0.00,
  status INTEGER NOT NULL,
  message TEXT
);
CREATE INDEX IF NOT EXISTS idx_cash_user ON acg_cash(user_id);
CREATE INDEX IF NOT EXISTS idx_cash_type ON acg_cash(type);
CREATE INDEX IF NOT EXISTS idx_cash_message ON acg_cash(message);

-- 商品分类
CREATE TABLE IF NOT EXISTS acg_category (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  sort INTEGER NOT NULL DEFAULT 0,
  create_time INTEGER NOT NULL,
  owner INTEGER NOT NULL DEFAULT 0,
  icon TEXT,
  status INTEGER NOT NULL DEFAULT 0,
  hide INTEGER NOT NULL DEFAULT 0,
  user_level_config TEXT,
  pid INTEGER
);
CREATE INDEX IF NOT EXISTS idx_category_owner ON acg_category(owner);
CREATE INDEX IF NOT EXISTS idx_category_pid ON acg_category(pid);
CREATE INDEX IF NOT EXISTS idx_category_sort ON acg_category(sort);

-- 商品
CREATE TABLE IF NOT EXISTS acg_commodity (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  category_id INTEGER NOT NULL,
  name TEXT NOT NULL,
  description TEXT,
  cover TEXT,
  factory_price REAL NOT NULL DEFAULT 0.00,
  price REAL NOT NULL DEFAULT 0.00,
  user_price REAL NOT NULL DEFAULT 0.00,
  status INTEGER NOT NULL DEFAULT 0,
  owner INTEGER NOT NULL DEFAULT 0,
  create_time INTEGER NOT NULL,
  api_status INTEGER NOT NULL DEFAULT 0,
  code TEXT NOT NULL,
  delivery_way INTEGER NOT NULL DEFAULT 0,
  delivery_auto_mode INTEGER NOT NULL DEFAULT 0,
  delivery_message TEXT,
  contact_type INTEGER NOT NULL DEFAULT 0,
  password_status INTEGER NOT NULL DEFAULT 0,
  sort INTEGER NOT NULL DEFAULT 0,
  coupon INTEGER NOT NULL DEFAULT 0,
  shared_id INTEGER,
  shared_code TEXT,
  shared_premium REAL,
  shared_stock TEXT,
  stock INTEGER,
  shared_premium_type INTEGER DEFAULT 0,
  shared_premium_template INTEGER NOT NULL DEFAULT 0,
  seckill_status INTEGER NOT NULL DEFAULT 0,
  seckill_start_time INTEGER,
  seckill_end_time INTEGER,
  draft_status INTEGER NOT NULL DEFAULT 0,
  draft_premium REAL NOT NULL DEFAULT 0.00,
  inventory_hidden INTEGER NOT NULL DEFAULT 0,
  leave_message TEXT,
  recommend INTEGER DEFAULT 0,
  send_email INTEGER NOT NULL DEFAULT 0,
  only_user INTEGER NOT NULL DEFAULT 0,
  purchase_count INTEGER NOT NULL DEFAULT 0,
  widget TEXT,
  tags TEXT,
  level_price TEXT,
  level_disable INTEGER NOT NULL DEFAULT 0,
  minimum INTEGER NOT NULL DEFAULT 0,
  maximum INTEGER NOT NULL DEFAULT 0,
  shared_sync INTEGER NOT NULL DEFAULT 0,
  config TEXT,
  hide INTEGER NOT NULL DEFAULT 0,
  inventory_sync INTEGER NOT NULL DEFAULT 0,
  shared_amount_sync INTEGER DEFAULT 0,
  shared_config_sync INTEGER DEFAULT 0
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_commodity_code ON acg_commodity(code);
CREATE INDEX IF NOT EXISTS idx_commodity_owner ON acg_commodity(owner);
CREATE INDEX IF NOT EXISTS idx_commodity_status ON acg_commodity(status);
CREATE INDEX IF NOT EXISTS idx_commodity_sort ON acg_commodity(sort);
CREATE INDEX IF NOT EXISTS idx_commodity_category ON acg_commodity(category_id);
CREATE INDEX IF NOT EXISTS idx_commodity_shared ON acg_commodity(shared_id);
CREATE INDEX IF NOT EXISTS idx_commodity_api ON acg_commodity(api_status);
CREATE INDEX IF NOT EXISTS idx_commodity_recommend ON acg_commodity(recommend);

-- 站点配置
CREATE TABLE IF NOT EXISTS acg_config (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  key TEXT NOT NULL,
  value TEXT NOT NULL
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_config_key ON acg_config(key);

-- 优惠券
CREATE TABLE IF NOT EXISTS acg_coupon (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  code TEXT NOT NULL,
  commodity_id INTEGER NOT NULL,
  owner INTEGER NOT NULL DEFAULT 0,
  create_time INTEGER NOT NULL,
  expire_time INTEGER,
  service_time INTEGER,
  money REAL NOT NULL,
  status INTEGER NOT NULL DEFAULT 0,
  trade_no TEXT,
  note TEXT,
  mode INTEGER DEFAULT 0,
  category_id INTEGER DEFAULT 0,
  life INTEGER NOT NULL DEFAULT 1,
  use_life INTEGER NOT NULL DEFAULT 0,
  race TEXT,
  sku TEXT
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_coupon_code ON acg_coupon(code);
CREATE INDEX IF NOT EXISTS idx_coupon_commodity ON acg_coupon(commodity_id);
CREATE INDEX IF NOT EXISTS idx_coupon_owner ON acg_coupon(owner);
CREATE INDEX IF NOT EXISTS idx_coupon_status ON acg_coupon(status);

-- 管理员
CREATE TABLE IF NOT EXISTS acg_manage (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  email TEXT NOT NULL,
  password TEXT NOT NULL,
  security_password TEXT,
  nickname TEXT,
  salt TEXT NOT NULL,
  avatar TEXT,
  status INTEGER NOT NULL DEFAULT 0,
  type INTEGER NOT NULL DEFAULT 0,
  create_time INTEGER NOT NULL,
  login_time INTEGER,
  last_login_time INTEGER,
  login_ip TEXT,
  last_login_ip TEXT,
  note TEXT,
  google_secret TEXT
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_manage_email ON acg_manage(email);

-- 管理员会话
CREATE TABLE IF NOT EXISTS acg_manage_session (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  manage_id INTEGER NOT NULL,
  session_hash TEXT NOT NULL,
  device_type TEXT NOT NULL,
  device_name TEXT NOT NULL,
  user_agent TEXT NOT NULL,
  login_ip TEXT NOT NULL,
  last_ip TEXT NOT NULL,
  created_time INTEGER NOT NULL,
  last_seen_time INTEGER NOT NULL,
  expires_time INTEGER NOT NULL,
  revoked_time INTEGER
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_manage_session_hash ON acg_manage_session(session_hash);
CREATE INDEX IF NOT EXISTS idx_manage_session_active ON acg_manage_session(manage_id, revoked_time, expires_time);
CREATE INDEX IF NOT EXISTS idx_manage_session_seen ON acg_manage_session(last_seen_time);

-- 订单
CREATE TABLE IF NOT EXISTS acg_order (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  owner INTEGER NOT NULL DEFAULT 0,
  user_id INTEGER NOT NULL DEFAULT 0,
  trade_no TEXT NOT NULL,
  amount REAL NOT NULL,
  commodity_id INTEGER NOT NULL,
  card_id INTEGER,
  card_num INTEGER NOT NULL DEFAULT 0,
  pay_id INTEGER NOT NULL,
  create_time INTEGER NOT NULL,
  create_ip TEXT NOT NULL,
  create_device INTEGER NOT NULL,
  pay_time INTEGER,
  status INTEGER NOT NULL DEFAULT 0,
  secret TEXT,
  password TEXT,
  contact TEXT,
  delivery_status INTEGER NOT NULL DEFAULT 0,
  pay_url TEXT,
  coupon_id INTEGER,
  cost REAL NOT NULL DEFAULT 0.00,
  "from" INTEGER,
  premium REAL DEFAULT 0.00,
  widget TEXT,
  leave_message TEXT,
  rent REAL NOT NULL DEFAULT 0.00,
  race TEXT,
  rebate REAL DEFAULT 0.00,
  pay_cost REAL DEFAULT 0.00,
  gateway_amount REAL,
  sku TEXT,
  divide_amount REAL,
  substation_user_id INTEGER,
  request_no TEXT
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_order_trade_no ON acg_order(trade_no);
CREATE UNIQUE INDEX IF NOT EXISTS uq_order_request_no ON acg_order(request_no);
CREATE INDEX IF NOT EXISTS idx_order_commodity ON acg_order(commodity_id);
CREATE INDEX IF NOT EXISTS idx_order_pay ON acg_order(pay_id);
CREATE INDEX IF NOT EXISTS idx_order_contact ON acg_order(contact);
CREATE INDEX IF NOT EXISTS idx_order_create_ip ON acg_order(create_ip);
CREATE INDEX IF NOT EXISTS idx_order_owner ON acg_order(owner);
CREATE INDEX IF NOT EXISTS idx_order_from ON acg_order("from");
CREATE INDEX IF NOT EXISTS idx_order_user ON acg_order(user_id);
CREATE INDEX IF NOT EXISTS idx_order_card ON acg_order(card_id);
CREATE INDEX IF NOT EXISTS idx_order_time ON acg_order(create_time);

-- 订单自定义信息
CREATE TABLE IF NOT EXISTS acg_order_option (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  order_id INTEGER NOT NULL,
  option TEXT
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_order_option_order ON acg_order_option(order_id);

-- 加价模板
CREATE TABLE IF NOT EXISTS acg_price_template (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  base INTEGER NOT NULL DEFAULT 0,
  guest_type INTEGER NOT NULL DEFAULT 1,
  guest_value REAL NOT NULL DEFAULT 0.00,
  user_type INTEGER NOT NULL DEFAULT 1,
  user_value REAL NOT NULL DEFAULT 0.00,
  level_config TEXT,
  rounding INTEGER NOT NULL DEFAULT 0,
  create_time INTEGER NOT NULL
);

-- 支付方式
CREATE TABLE IF NOT EXISTS acg_pay (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  icon TEXT,
  code TEXT NOT NULL,
  commodity INTEGER NOT NULL DEFAULT 0,
  recharge INTEGER NOT NULL DEFAULT 0,
  create_time INTEGER NOT NULL,
  handle TEXT NOT NULL,
  pay_config_id INTEGER NOT NULL DEFAULT 0,
  sort INTEGER NOT NULL DEFAULT 0,
  equipment INTEGER NOT NULL DEFAULT 0,
  cost REAL DEFAULT 0.000,
  cost_type INTEGER DEFAULT 0,
  archived INTEGER NOT NULL DEFAULT 0
);
CREATE INDEX IF NOT EXISTS idx_pay_commodity ON acg_pay(commodity);
CREATE INDEX IF NOT EXISTS idx_pay_recharge ON acg_pay(recharge);
CREATE INDEX IF NOT EXISTS idx_pay_sort ON acg_pay(sort);

-- 支付插件配置(每插件多配置档)
CREATE TABLE IF NOT EXISTS acg_pay_config (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  handle TEXT NOT NULL,
  name TEXT NOT NULL,
  config TEXT,
  sort INTEGER NOT NULL DEFAULT 0,
  create_time INTEGER NOT NULL,
  update_time INTEGER
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_pay_config_handle_name ON acg_pay_config(handle, name);
CREATE INDEX IF NOT EXISTS idx_pay_config_handle ON acg_pay_config(handle);

-- 支付插件日志(对齐原版写文件行为, 这里用 D1 存一行)
CREATE TABLE IF NOT EXISTS acg_pay_plugin_log (
  handle TEXT PRIMARY KEY,
  content TEXT,
  update_time INTEGER
);

-- 店铺共享(开店模板)
CREATE TABLE IF NOT EXISTS acg_shared (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  type INTEGER NOT NULL DEFAULT 0,
  name TEXT NOT NULL,
  domain TEXT NOT NULL,
  app_id TEXT NOT NULL,
  app_key TEXT NOT NULL,
  create_time INTEGER NOT NULL,
  balance REAL NOT NULL DEFAULT 0.00,
  currency TEXT NOT NULL DEFAULT 'CNY',
  currency_rate REAL NOT NULL DEFAULT 0.000000,
  protocol INTEGER NOT NULL DEFAULT 0
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_shared_domain ON acg_shared(domain);

-- 会员
CREATE TABLE IF NOT EXISTS acg_user (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT NOT NULL,
  email TEXT,
  phone TEXT,
  qq TEXT,
  password TEXT NOT NULL,
  salt TEXT NOT NULL,
  app_key TEXT NOT NULL,
  avatar TEXT,
  balance REAL NOT NULL DEFAULT 0.00,
  coin REAL NOT NULL DEFAULT 0.00,
  integral INTEGER NOT NULL DEFAULT 0,
  create_time INTEGER NOT NULL,
  login_time INTEGER,
  last_login_time INTEGER,
  login_ip TEXT,
  last_login_ip TEXT,
  pid INTEGER DEFAULT 0,
  recharge REAL NOT NULL DEFAULT 0.00,
  total_coin REAL NOT NULL DEFAULT 0.00,
  status INTEGER NOT NULL DEFAULT 0,
  business_level INTEGER,
  nicename TEXT,
  alipay TEXT,
  wechat TEXT,
  wallet_address TEXT,
  settlement INTEGER NOT NULL DEFAULT 0
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_user_username ON acg_user(username);
CREATE UNIQUE INDEX IF NOT EXISTS uq_user_email ON acg_user(email);
CREATE UNIQUE INDEX IF NOT EXISTS uq_user_phone ON acg_user(phone);
CREATE INDEX IF NOT EXISTS idx_user_pid ON acg_user(pid);
CREATE INDEX IF NOT EXISTS idx_user_business_level ON acg_user(business_level);
CREATE INDEX IF NOT EXISTS idx_user_coin ON acg_user(coin);

-- 商户-分类映射
CREATE TABLE IF NOT EXISTS acg_user_category (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  category_id INTEGER NOT NULL,
  name TEXT,
  status INTEGER NOT NULL DEFAULT 0
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_user_category ON acg_user_category(user_id, category_id);
CREATE INDEX IF NOT EXISTS idx_user_category_status ON acg_user_category(status);
CREATE INDEX IF NOT EXISTS idx_user_category_cat ON acg_user_category(category_id);

-- 商户-商品映射
CREATE TABLE IF NOT EXISTS acg_user_commodity (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  commodity_id INTEGER NOT NULL,
  premium REAL DEFAULT 0.00,
  rounding INTEGER NOT NULL DEFAULT 0,
  name TEXT,
  description TEXT,
  status INTEGER NOT NULL DEFAULT 0
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_user_commodity ON acg_user_commodity(user_id, commodity_id);
CREATE INDEX IF NOT EXISTS idx_user_commodity_cid ON acg_user_commodity(commodity_id);
CREATE INDEX IF NOT EXISTS idx_user_commodity_status ON acg_user_commodity(status);

-- 会员等级
CREATE TABLE IF NOT EXISTS acg_user_group (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  icon TEXT,
  discount_config TEXT,
  cost REAL NOT NULL DEFAULT 0.00,
  recharge REAL NOT NULL
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_user_group_recharge ON acg_user_group(recharge);

-- 充值订单
CREATE TABLE IF NOT EXISTS acg_user_recharge (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  trade_no TEXT NOT NULL,
  user_id INTEGER NOT NULL,
  amount REAL NOT NULL,
  pay_cost REAL NOT NULL DEFAULT 0.00,
  gateway_amount REAL,
  pay_id INTEGER NOT NULL,
  status INTEGER NOT NULL DEFAULT 0,
  create_time INTEGER NOT NULL,
  create_ip TEXT NOT NULL,
  pay_url TEXT,
  pay_time INTEGER,
  option TEXT
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_user_recharge_trade_no ON acg_user_recharge(trade_no);
CREATE INDEX IF NOT EXISTS idx_user_recharge_user ON acg_user_recharge(user_id);
CREATE INDEX IF NOT EXISTS idx_user_recharge_pay ON acg_user_recharge(pay_id);
CREATE INDEX IF NOT EXISTS idx_user_recharge_status ON acg_user_recharge(status);

-- 管理员操作日志
CREATE TABLE IF NOT EXISTS acg_manage_log (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  email TEXT NOT NULL,
  nickname TEXT NOT NULL,
  content TEXT NOT NULL,
  create_time INTEGER NOT NULL,
  create_ip TEXT NOT NULL,
  ua TEXT,
  risk INTEGER NOT NULL DEFAULT 0
);
CREATE INDEX IF NOT EXISTS idx_manage_log_ip ON acg_manage_log(create_ip);
CREATE INDEX IF NOT EXISTS idx_manage_log_time ON acg_manage_log(create_time);
CREATE INDEX IF NOT EXISTS idx_manage_log_risk ON acg_manage_log(risk);

-- 商品组
CREATE TABLE IF NOT EXISTS acg_commodity_group (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  commodity_list TEXT
);

-- 上传文件
CREATE TABLE IF NOT EXISTS acg_upload (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER,
  hash TEXT NOT NULL,
  type TEXT NOT NULL,
  path TEXT NOT NULL,
  create_time INTEGER NOT NULL,
  note TEXT
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_upload_hash ON acg_upload(hash);
CREATE INDEX IF NOT EXISTS idx_upload_user ON acg_upload(user_id);
CREATE INDEX IF NOT EXISTS idx_upload_type ON acg_upload(type);
CREATE INDEX IF NOT EXISTS idx_upload_time ON acg_upload(create_time);

-- 工单
CREATE TABLE IF NOT EXISTS acg_ticket (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  ticket_no TEXT NOT NULL,
  user_id INTEGER NOT NULL,
  type INTEGER NOT NULL,
  priority INTEGER NOT NULL DEFAULT 1,
  status INTEGER NOT NULL DEFAULT 0,
  title TEXT NOT NULL,
  commodity_id INTEGER,
  commodity_name TEXT,
  order_id INTEGER,
  order_trade_no TEXT,
  order_source INTEGER NOT NULL DEFAULT 0,
  proof_upload_id INTEGER,
  proof_path TEXT,
  last_message_id INTEGER,
  last_sender_type INTEGER,
  last_message_excerpt TEXT,
  last_message_time INTEGER,
  user_unread INTEGER NOT NULL DEFAULT 0,
  manage_unread INTEGER NOT NULL DEFAULT 0,
  closed_by INTEGER,
  closed_time INTEGER,
  create_time INTEGER NOT NULL,
  update_time INTEGER NOT NULL
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_ticket_no ON acg_ticket(ticket_no);
CREATE INDEX IF NOT EXISTS idx_ticket_user_status ON acg_ticket(user_id, status, last_message_time);
CREATE INDEX IF NOT EXISTS idx_ticket_status_priority ON acg_ticket(status, priority, last_message_time);
CREATE INDEX IF NOT EXISTS idx_ticket_commodity ON acg_ticket(commodity_id);
CREATE INDEX IF NOT EXISTS idx_ticket_order ON acg_ticket(order_id);

-- 工单消息
CREATE TABLE IF NOT EXISTS acg_ticket_message (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  ticket_id INTEGER NOT NULL,
  sender_type INTEGER NOT NULL,
  sender_id INTEGER,
  sender_name TEXT NOT NULL,
  kind INTEGER NOT NULL DEFAULT 0,
  content TEXT,
  create_ip TEXT NOT NULL,
  create_time INTEGER NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_ticket_message ON acg_ticket_message(ticket_id, id);
CREATE INDEX IF NOT EXISTS idx_ticket_message_sender ON acg_ticket_message(sender_type, sender_id);
CREATE INDEX IF NOT EXISTS idx_ticket_message_time ON acg_ticket_message(create_time);

-- 系统消息
CREATE TABLE IF NOT EXISTS acg_system_message (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  audience_type INTEGER NOT NULL,
  audience_id INTEGER,
  audience_name TEXT NOT NULL,
  title TEXT NOT NULL,
  content TEXT NOT NULL,
  summary TEXT NOT NULL,
  jump_url TEXT,
  recipient_count INTEGER NOT NULL DEFAULT 0,
  created_by INTEGER,
  updated_by INTEGER,
  manage_name TEXT NOT NULL,
  update_manage_name TEXT NOT NULL,
  create_time INTEGER NOT NULL,
  update_time INTEGER NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_system_message_audience ON acg_system_message(audience_type, audience_id);
CREATE INDEX IF NOT EXISTS idx_system_message_time ON acg_system_message(create_time);
CREATE INDEX IF NOT EXISTS idx_system_message_created ON acg_system_message(created_by);

-- 用户消息
CREATE TABLE IF NOT EXISTS acg_user_message (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  message_id INTEGER NOT NULL,
  user_id INTEGER NOT NULL,
  read_time INTEGER,
  create_time INTEGER NOT NULL
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_user_message ON acg_user_message(message_id, user_id);
CREATE INDEX IF NOT EXISTS idx_user_message ON acg_user_message(user_id, id);
CREATE INDEX IF NOT EXISTS idx_user_message_read ON acg_user_message(user_id, read_time, id);

-- 多语言翻译库
CREATE TABLE IF NOT EXISTS acg_lang (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  hash TEXT NOT NULL,
  source TEXT NOT NULL,
  lang TEXT NOT NULL,
  text TEXT,
  scene TEXT NOT NULL DEFAULT '',
  status INTEGER NOT NULL DEFAULT 0,
  create_time INTEGER,
  update_time INTEGER
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_lang_hash ON acg_lang(hash, lang);
CREATE INDEX IF NOT EXISTS idx_lang_status ON acg_lang(lang, status);