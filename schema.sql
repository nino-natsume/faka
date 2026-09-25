-- ============================================================
-- DCSHOP (acg-faka) Cloudflare 部署版 - D1/SQLite 初始化
-- 使用: wrangler d1 execute faka-db --file=./schema.sql
-- ============================================================

-- 站点配置 (key-value)
CREATE TABLE IF NOT EXISTS dc_options (
  option_id INTEGER PRIMARY KEY AUTOINCREMENT,
  option_name TEXT NOT NULL UNIQUE,
  option_value TEXT NOT NULL
);

-- 模板配置 (主题色等)
CREATE TABLE IF NOT EXISTS dc_tpl_options_data (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  template TEXT NOT NULL DEFAULT '',
  name TEXT NOT NULL DEFAULT '',
  depend TEXT NOT NULL DEFAULT '',
  data TEXT NOT NULL DEFAULT ''
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_tpl_options ON dc_tpl_options_data(template, name, depend);

-- 商品分类
CREATE TABLE IF NOT EXISTS dc_sort (
  sid INTEGER PRIMARY KEY AUTOINCREMENT,
  type TEXT NOT NULL DEFAULT 'goods',
  sortname TEXT NOT NULL DEFAULT '',
  alias TEXT NOT NULL DEFAULT '',
  taxis INTEGER NOT NULL DEFAULT 0,
  pid INTEGER NOT NULL DEFAULT 0,
  description TEXT NOT NULL DEFAULT '',
  kw TEXT NOT NULL DEFAULT '',
  title TEXT NOT NULL DEFAULT '',
  template TEXT NOT NULL DEFAULT '',
  sortimg TEXT NOT NULL DEFAULT '',
  sorticon TEXT NOT NULL DEFAULT '',
  page_count INTEGER NOT NULL DEFAULT 0,
  station_id INTEGER NOT NULL DEFAULT 0,
  delete_time INTEGER
);

-- 商品主表
CREATE TABLE IF NOT EXISTS dc_goods (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  station_id INTEGER NOT NULL DEFAULT 0,
  des TEXT,
  sort_num INTEGER NOT NULL DEFAULT 0,
  sort_top INTEGER NOT NULL DEFAULT 0,
  index_top INTEGER NOT NULL DEFAULT 0,
  type TEXT,
  attr_id INTEGER DEFAULT 0,
  is_sku TEXT DEFAULT 'n',
  title TEXT NOT NULL DEFAULT '',
  unit_name TEXT NOT NULL DEFAULT '个',
  is_on_shelf INTEGER NOT NULL DEFAULT 1,
  allow_dock INTEGER NOT NULL DEFAULT 1,
  attach_user TEXT,
  create_time INTEGER NOT NULL DEFAULT 0,
  content TEXT NOT NULL DEFAULT '',
  pay_content TEXT,
  cover TEXT NOT NULL DEFAULT '',
  gallery TEXT,
  sort_id INTEGER NOT NULL DEFAULT -1,
  sales INTEGER NOT NULL DEFAULT 0,
  stock INTEGER NOT NULL DEFAULT 0,
  password TEXT NOT NULL DEFAULT '',
  template TEXT NOT NULL DEFAULT '',
  tags TEXT,
  link TEXT NOT NULL DEFAULT '',
  delete_time INTEGER,
  profit_rule_id INTEGER NOT NULL DEFAULT 0,
  profit_ratio REAL NOT NULL DEFAULT 100.00,
  single_rule_id INTEGER NOT NULL DEFAULT 0,
  accuracy INTEGER NOT NULL DEFAULT 2,
  discount_title TEXT NOT NULL DEFAULT ''
);
CREATE INDEX IF NOT EXISTS idx_goods_sort ON dc_goods(sort_id);
CREATE INDEX IF NOT EXISTS idx_goods_on_shelf ON dc_goods(is_on_shelf, delete_time);

-- 商品类型 (规格组)
CREATE TABLE IF NOT EXISTS dc_goods_type (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT,
  delete_time INTEGER,
  hide TEXT NOT NULL DEFAULT 'n'
);

-- SKU 价格库存
CREATE TABLE IF NOT EXISTS dc_skus (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  code TEXT,
  goods_id INTEGER NOT NULL,
  sku TEXT NOT NULL DEFAULT '0',
  market_price INTEGER,
  cost_price INTEGER,
  content TEXT,
  guest_price INTEGER,
  user_price INTEGER,
  post_url TEXT,
  stock INTEGER NOT NULL DEFAULT 0,
  sales INTEGER NOT NULL DEFAULT 0
);
CREATE INDEX IF NOT EXISTS idx_skus_goods ON dc_skus(goods_id);
CREATE UNIQUE INDEX IF NOT EXISTS uq_skus_goods_sku ON dc_skus(goods_id, sku);

-- 规格属性
CREATE TABLE IF NOT EXISTS dc_sku_attr (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  type_id INTEGER DEFAULT 0,
  title TEXT,
  delete_time INTEGER
);

-- 规格属性值
CREATE TABLE IF NOT EXISTS dc_sku_value (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  attr_id INTEGER DEFAULT 0,
  name TEXT,
  delete_time INTEGER
);

-- 一卡一密库存
CREATE TABLE IF NOT EXISTS dc_goods_once (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  goods_id INTEGER NOT NULL DEFAULT 0,
  sku TEXT NOT NULL DEFAULT '0',
  batch_no TEXT,
  content TEXT,
  create_time INTEGER,
  update_time INTEGER,
  sale_time INTEGER,
  order_list_id INTEGER NOT NULL DEFAULT 0
);
CREATE INDEX IF NOT EXISTS idx_once_goods_sku ON dc_goods_once(goods_id, sku);
CREATE INDEX IF NOT EXISTS idx_once_sale ON dc_goods_once(sale_time);
CREATE UNIQUE INDEX IF NOT EXISTS uq_once_goods_sku_content ON dc_goods_once(goods_id, sku, content);

-- 通用卡密 (固定内容)
CREATE TABLE IF NOT EXISTS dc_goods_general (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  goods_id INTEGER NOT NULL DEFAULT 0,
  sku TEXT NOT NULL DEFAULT '0',
  content TEXT,
  create_time INTEGER,
  update_time INTEGER
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_general_goods_sku ON dc_goods_general(goods_id, sku);

-- 通用卡密发货记录
CREATE TABLE IF NOT EXISTS dc_goods_general_sale (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  goods_id INTEGER NOT NULL DEFAULT 0,
  order_list_id INTEGER NOT NULL DEFAULT 0,
  sku TEXT NOT NULL DEFAULT '0',
  content TEXT,
  num INTEGER NOT NULL DEFAULT 0,
  create_time INTEGER,
  update_time INTEGER
);

-- 虚拟服务说明
CREATE TABLE IF NOT EXISTS dc_goods_service (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  goods_id INTEGER NOT NULL DEFAULT 0,
  content TEXT,
  sku TEXT NOT NULL DEFAULT '0',
  create_time INTEGER,
  update_time INTEGER
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_service_goods_sku ON dc_goods_service(goods_id, sku);

-- 虚拟服务发货记录
CREATE TABLE IF NOT EXISTS dc_goods_service_sale (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  goods_id INTEGER NOT NULL DEFAULT 0,
  order_list_id INTEGER NOT NULL DEFAULT 0,
  sku TEXT NOT NULL DEFAULT '0',
  content TEXT,
  num INTEGER NOT NULL DEFAULT 0,
  is_default TEXT DEFAULT 'y',
  create_time INTEGER,
  update_time INTEGER
);

-- 卡密库存 (自定义类型 dc_stock 使用)
CREATE TABLE IF NOT EXISTS dc_stock (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  code TEXT,
  goods_id INTEGER NOT NULL,
  sku TEXT NOT NULL DEFAULT '0',
  content TEXT,
  create_time INTEGER
);

-- 订单主表
CREATE TABLE IF NOT EXISTS dc_order (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  station_id INTEGER NOT NULL DEFAULT 0,
  client_ip TEXT,
  user_id INTEGER DEFAULT 0,
  out_trade_no TEXT,
  tel TEXT,
  email TEXT,
  amount INTEGER,
  create_time INTEGER,
  payment TEXT,
  pay_plugin TEXT,
  pay_time INTEGER,
  update_time INTEGER,
  qr_code TEXT,
  expire_time INTEGER,
  device TEXT,
  pay_name TEXT,
  pay_status INTEGER DEFAULT 0,
  delete_time INTEGER,
  service_status INTEGER DEFAULT 0,
  status INTEGER DEFAULT 0,
  pwd TEXT,
  up_no TEXT,
  last_pay_init_time INTEGER DEFAULT 0,
  notify_url TEXT DEFAULT ''
);
CREATE INDEX IF NOT EXISTS idx_order_no ON dc_order(out_trade_no);

-- 订单商品明细
CREATE TABLE IF NOT EXISTS dc_order_list (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  order_id INTEGER DEFAULT 0,
  goods_id INTEGER DEFAULT 0,
  sku TEXT DEFAULT '0',
  attr_spec TEXT DEFAULT '',
  attach_user TEXT DEFAULT '[]',
  quantity INTEGER DEFAULT 1,
  unit_price INTEGER DEFAULT 0,
  price INTEGER DEFAULT 0,
  status INTEGER DEFAULT 0,
  cost_price INTEGER DEFAULT 0
);
CREATE INDEX IF NOT EXISTS idx_ol_order ON dc_order_list(order_id);

-- 订单必填信息
CREATE TABLE IF NOT EXISTS dc_order_required (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  order_id INTEGER NOT NULL DEFAULT 0,
  name TEXT,
  type TEXT DEFAULT 'string',
  content TEXT
);

-- 购物车
CREATE TABLE IF NOT EXISTS dc_cart (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  is_local INTEGER DEFAULT 0,
  eb_local TEXT,
  user_id INTEGER DEFAULT 0,
  goods_id INTEGER DEFAULT 0,
  sku TEXT DEFAULT '0',
  quantity INTEGER DEFAULT 1,
  create_time INTEGER,
  update_time INTEGER
);

-- 优惠券 (可选)
CREATE TABLE IF NOT EXISTS dc_coupon (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL DEFAULT '',
  code TEXT NOT NULL DEFAULT '',
  type INTEGER NOT NULL DEFAULT 1,
  value REAL NOT NULL DEFAULT 0,
  min_amount REAL NOT NULL DEFAULT 0,
  max_discount REAL,
  total_count INTEGER NOT NULL DEFAULT 0,
  used_count INTEGER NOT NULL DEFAULT 0,
  per_user_limit INTEGER NOT NULL DEFAULT 1,
  user_type INTEGER NOT NULL DEFAULT 0,
  goods_type INTEGER NOT NULL DEFAULT 0,
  goods_ids TEXT,
  time_limit INTEGER DEFAULT 0,
  first_check_time INTEGER,
  category_ids TEXT,
  start_time INTEGER,
  end_time INTEGER,
  status INTEGER NOT NULL DEFAULT 1,
  create_time INTEGER NOT NULL DEFAULT 0
);

-- 优惠券使用日志
CREATE TABLE IF NOT EXISTS dc_coupon_log (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  coupon_id INTEGER NOT NULL DEFAULT 0,
  user_id INTEGER NOT NULL DEFAULT 0,
  order_id INTEGER DEFAULT 0,
  order_no TEXT DEFAULT '',
  discount_amount REAL NOT NULL DEFAULT 0,
  client_ip TEXT DEFAULT '',
  create_time INTEGER NOT NULL DEFAULT 0
);

-- 批量优惠
CREATE TABLE IF NOT EXISTS dc_discount (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  goods_id INTEGER NOT NULL DEFAULT 0,
  sku TEXT NOT NULL DEFAULT '0',
  quantity INTEGER DEFAULT 0,
  amount INTEGER DEFAULT 0,
  type INTEGER NOT NULL DEFAULT 1
);

-- 用户
CREATE TABLE IF NOT EXISTS dc_user (
  uid INTEGER PRIMARY KEY AUTOINCREMENT,
  station_id INTEGER NOT NULL DEFAULT 0,
  expend REAL DEFAULT 0,
  username TEXT NOT NULL DEFAULT '',
  password TEXT NOT NULL DEFAULT '',
  money REAL DEFAULT 0,
  nickname TEXT NOT NULL DEFAULT '',
  level INTEGER DEFAULT 0,
  role TEXT NOT NULL DEFAULT '',
  admin_group_id INTEGER NOT NULL DEFAULT 0,
  ischeck TEXT NOT NULL DEFAULT 'n',
  photo TEXT NOT NULL DEFAULT '',
  email TEXT NOT NULL DEFAULT '',
  tel TEXT,
  wechat TEXT NOT NULL DEFAULT '',
  description TEXT NOT NULL DEFAULT '',
  ip TEXT NOT NULL DEFAULT '',
  reg_ip TEXT,
  state INTEGER NOT NULL DEFAULT 0,
  credits INTEGER NOT NULL DEFAULT 0,
  level_expire_time INTEGER NOT NULL DEFAULT 0,
  create_time INTEGER NOT NULL DEFAULT 0,
  update_time INTEGER NOT NULL DEFAULT 0,
  delete_time INTEGER,
  superior INTEGER NOT NULL DEFAULT 0,
  invite_code TEXT NOT NULL DEFAULT ''
);

-- 分站 (单站部署默认为主站)
CREATE TABLE IF NOT EXISTS dc_station (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER DEFAULT 0,
  pid INTEGER DEFAULT 0,
  level_id INTEGER DEFAULT 0,
  status INTEGER NOT NULL DEFAULT 1,
  amount REAL,
  domain TEXT,
  domain_2 TEXT,
  create_time INTEGER,
  name TEXT,
  title TEXT,
  site_subtitle TEXT,
  master_sort INTEGER DEFAULT 0,
  master_goods INTEGER DEFAULT 0,
  roll_notice TEXT,
  home_notice TEXT,
  delete_time INTEGER,
  slug TEXT,
  site_description TEXT,
  site_key TEXT,
  icp TEXT,
  footer_info TEXT
);