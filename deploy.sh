#!/bin/bash
# =========================================================
#  faka (DCSHOP) 一键精简部署 - 1核512M 专用
#  适用于: Debian 11 (bullseye) / Debian 12 (bookworm)
#  用法: sh deploy.sh [域名]
#  数据库: 库名/用户 = dcshop (默认), 密码请按需修改
# =========================================================
set -e

WEB_ROOT="/var/www/faka"
DB_NAME="dcshop"
DB_USER="dcshop"
DB_PASS="dcshop@123"
DOMAIN="${1:-_}"
AUTH_KEY="dcshop_change_me"
AUTH_COOKIE_NAME="DC_AUTHCOOKIE"

SRC_DIR="$(cd "$(dirname "$0")" && pwd)"

echo ">>> 0/8 检查源码目录（需要看到 index.php）"
[ -f "$SRC_DIR/index.php" ] || { echo "未找到 index.php，请 cd 到解压后的 faka 目录再运行"; exit 1; }

# ---------- 检测 Debian 版本 & PHP 版本 ----------
echo ">>> 探测系统/PHP"
. /etc/os-release
echo "系统: $NAME $VERSION_ID"
case "${VERSION_ID}" in
    13) PHP_VER="8.2" ;;   # Debian 13 (trixie) 默认8.4，但按需求装 8.2（需 SURY 源，见步骤2）
    12) PHP_VER="8.2" ;;
    11) PHP_VER="7.4" ;;
    *)  PHP_VER="8.2" ;;   # 兜底
esac
echo "选用 PHP: ${PHP_VER}"

# ---------- 1. 系统更新 & 基础包 ----------
echo ">>> 1/8 更新系统 & 安装基础包"
sudo apt update -y
sudo apt upgrade -y
sudo apt install -y curl wget git vim rsync openssl ca-certificates

# 512M 加 2G swap（防OOM关键）
if ! swapon --show | grep -q swapfile; then
  sudo fallocate -l 2G /swapfile || sudo dd if=/dev/zero of=/swapfile bs=1M count=2048
  sudo chmod 600 /swapfile
  sudo mkswap /swapfile && sudo swapon /swapfile
  echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
fi

# ---------- 2. 安装 Nginx + PHP + MariaDB ----------
echo ">>> 2/8 安装 Nginx + PHP${PHP_VER} + MariaDB"

# 若 Debian 13 需要非默认 PHP（8.2），需先加 SURY 官方 PHP 源
NEED_SURY=0
if [ "${VERSION_ID}" = "13" ]; then
    # Debian 13 官方只有 8.4；需要其它版本时走 SURY
    if ! apt-cache show "php${PHP_VER}-fpm" >/dev/null 2>&1; then
        NEED_SURY=1
    fi
fi
if [ "${NEED_SURY}" = "1" ]; then
    echo ">>> Debian 13 无 PHP${PHP_VER}，添加 SURY 官方 PHP 源..."
    sudo apt install -y curl lsb-release ca-certificates apt-transport-https gnupg2
    curl -fsSL https://packages.sury.org/php/apt.gpg | sudo gpg --dearmor -o /usr/share/keyrings/sury-php.gpg
    echo "deb [signed-by=/usr/share/keyrings/sury-php.gpg] https://packages.sury.org/php/ ${VERSION_CODENAME} main" \
        | sudo tee /etc/apt/sources.list.d/php.sources.list > /dev/null
    sudo apt update -y
    echo ">>> SURY 源已添加"
fi

echo ">>> 预检 PHP${PHP_VER} 扩展可用性..."
PHP_PKGS="php${PHP_VER}-fpm php${PHP_VER}-mysql php${PHP_VER}-mbstring \
  php${PHP_VER}-gd php${PHP_VER}-curl php${PHP_VER}-xml \
  php${PHP_VER}-zip php${PHP_VER}-opcache php${PHP_VER}-bcmath php${PHP_VER}-gmp"
# 过滤掉源里不存在的扩展，只装可用的（避免整条 apt 因单个包失败）
REAL_PKGS=""
for p in ${PHP_PKGS}; do
  if apt-cache show "${p}" >/dev/null 2>&1; then
    REAL_PKGS="${REAL_PKGS} ${p}"
  else
    echo "  跳过(源中不存在): ${p}"
  fi
done
sudo apt install -y nginx ${REAL_PKGS} mariadb-server

# ---------- 3. PHP-FPM 极致瘦身 ----------
echo ">>> 3/8 PHP-FPM 精简为 2 进程 (static)"
FPM_POOL="/etc/php/${PHP_VER}/fpm/pool.d/www.conf"
if [ -f "${FPM_POOL}" ]; then
  sudo tee "${FPM_POOL}" > /dev/null <<EOF
[www]
user = www-data
group = www-data
listen = /run/php/php${PHP_VER}-fpm.sock
pm = static
pm.max_children = 2
listen.backlog = 64
request_terminate_timeout = 60
EOF
else
  echo "警告: 未找到 ${FPM_POOL}，跳过 fpm 配置"
fi

# php.ini 调优
PHP_INI="/etc/php/${PHP_VER}/fpm/php.ini"
if [ -f "${PHP_INI}" ]; then
  sudo sed -i 's/^memory_limit = .*/memory_limit = 128M/' "${PHP_INI}"
  sudo sed -i 's/^;opcache.enable = .*/opcache.enable = 1/' "${PHP_INI}"
  sudo sed -i 's/^;opcache.memory_consumption = .*/opcache.memory_consumption = 48/' "${PHP_INI}"
  sudo sed -i 's/^;opcache.max_accelerated_files = .*/opcache.max_accelerated_files = 4000/' "${PHP_INI}"
fi

# ---------- 4. MariaDB 极致瘦身 ----------
echo ">>> 4/8 MariaDB 缓存压到最低"
sudo tee /etc/mysql/mariadb.conf.d/60-lowmem.cnf > /dev/null <<'EOF'
[mysqld]
bind-address=127.0.0.1
max_connections=20
performance_schema=OFF
skip-name-resolve
innodb_buffer_pool_size=16M
innodb_log_buffer_size=512K
innodb_log_file_size=8M
innodb_flush_log_at_trx_commit=2
innodb_flush_method=O_DIRECT
skip-log-bin
key_buffer_size=4M
max_heap_table_size=4M
tmp_table_size=4M
table_open_cache=128
thread_cache_size=4
max_allowed_packet=16M
sort_buffer_size=256K
join_buffer_size=256K
bulk_insert_buffer_size=1M
EOF
sudo systemctl restart mariadb

# ---------- 5. 建库导库 ----------
echo ">>> 5/8 创建数据库 ${DB_NAME} 并导入"
sudo mysql <<EOF
CREATE DATABASE IF NOT EXISTS ${DB_NAME} DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
EOF
# 导入表结构（shujuku7777777.sql）
if [ -f "$SRC_DIR/shujuku7777777.sql" ]; then
  sudo mysql ${DB_NAME} < "$SRC_DIR/shujuku7777777.sql"
  echo "已导入 shujuku7777777.sql"
else
  echo "警告: 未找到 shujuku7777777.sql，跳过建表（需手动导入）"
fi

# ---------- 6. 部署代码 ----------
echo ">>> 6/8 部署代码 到 ${WEB_ROOT}"
sudo mkdir -p ${WEB_ROOT}
# 用 cp 更通用（rsync 已装，但保险用 cp -a 也行）
sudo cp -a "${SRC_DIR}/." "${WEB_ROOT}/"

# 写 config.php（与上方数据库变量一致）
sudo tee ${WEB_ROOT}/config.php > /dev/null <<EOF
<?php
//MySQL database host
const DB_HOST = 'localhost';
//Database username
const DB_USER = '${DB_USER}';
//Database user password
const DB_PASSWD = '${DB_PASS}';
//Database name
const DB_NAME = '${DB_NAME}';
//Database Table Prefix
const DB_PREFIX = 'dc_';
//Auth key
const AUTH_KEY = '${AUTH_KEY}';
//Cookie name
const AUTH_COOKIE_NAME = '${AUTH_COOKIE_NAME}';
EOF

# 权限
sudo chown -R www-data:www-data ${WEB_ROOT}
sudo chmod -R 755 ${WEB_ROOT}
sudo chmod -R 775 ${WEB_ROOT}/content ${WEB_ROOT}/include

# 删除危险的 install.php
sudo rm -f ${WEB_ROOT}/install.php

# ---------- 7. Nginx ----------
echo ">>> 7/8 配置 Nginx"
sudo tee /etc/nginx/sites-available/faka > /dev/null <<EOF
server {
    listen 80;
    server_name ${DOMAIN};
    root ${WEB_ROOT};
    index index.php index.html;
    client_max_body_size 20m;
    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php${PHP_VER}-fpm.sock;
    }
    location ~* ^/(content/(cache|upload|templates|user_templates)|include)/.*\.php\$ { deny all; }
    location ~ /\. { deny all; }
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff2?)$ { expires 7d; access_log off; }
}
EOF
sudo ln -sf /etc/nginx/sites-available/faka /etc/nginx/sites-enabled/faka
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx
sudo systemctl restart php${PHP_VER}-fpm

# ---------- 8. 收尾 ----------
echo ">>> 8/8 完成"
echo ""
echo "============================================="
echo " 部署完成！"
echo " 站点:   http://${DOMAIN}/"
echo " PHP:    ${PHP_VER}"
echo " 数据库: ${DB_NAME} / ${DB_USER} / ${DB_PASS}"
echo " 内存:   $(free -m | awk '/Mem:/{print $3"MB / "$2"MB"}')"
echo "============================================="
free -m
