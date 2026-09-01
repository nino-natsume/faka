#!/usr/bin/env bash
# =============================================================
#  faka (DCSHOP 多财商城) Debian 13 一键部署脚本
#  -----------------------------------------------------------
#  适用 : Debian 13 (trixie) x86_64 (其它版本自动降级适配)
#  性能 : 专为 1H/512M/30GB 最小服务器优化 (worker=1, FPM=2, MariaDB lowmem)
#  用法 : bash deploy13.sh [域名]      # 域名可省略, 默认 _
#  说明 : 脚本需与源码放在同一目录上传到服务器
#         目录中需包含 index.php 与 shujuku7777777.sql
#  数据库: 库名/用户 = dcshop (默认), 密码自动随机生成 (可用环境变量覆盖)
#
#  HTTPS (可选, Cloudflare API 自动申请源站证书):
#    使用前请注入凭据 (不含任何明文密钥, 无泄露风险):
#    CF_API_TOKEN=xxx CF_ZONE_ID=yyy bash deploy13.sh 你的域名.com
#    或在脚本下方变量区修改第 37-38 行默认值
#    - CF_API_TOKEN 需要权限: Zone > SSL and Certificates > Edit
#    - 域名需在 Cloudflare 托管并开启代理(橙色云朵)
#    - 申请成功后记得在 CF 后台把 SSL/TLS 模式设为 Full (strict)
# =============================================================
set -euo pipefail

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[0;33m'; NC='\033[0m'
info() { echo -e "${GREEN}[INFO]${NC}  $*"; }
warn() { echo -e "${YELLOW}[WARN]${NC}  $*"; }
err()  { echo -e "${RED}[ERROR]${NC}  $*"; exit 1; }

WEB_ROOT="/var/www/faka"
# 数据库默认值 (可通过环境变量 DB_NAME/DB_USER/DB_PASS 覆盖)
DB_NAME="${DB_NAME:-dcshop}"
DB_USER="${DB_USER:-dcshop}"
DB_PASS="${DB_PASS:-}"
DOMAIN="${1:-_}"

# Cloudflare 凭据 (自定义变量: 传入环境变量或修改此处默认值, 避免明文入库)
CF_TOKEN="${CF_API_TOKEN:-}"
CF_ZONE="${CF_ZONE_ID:-}"
CF_EMAIL="${CF_EMAIL:-}"
CF_KEY="${CF_API_KEY:-}"
CF_API="https://api.cloudflare.com/client/v4"

MIN="--no-install-recommends"

# ---------- 0. 前置检查 ----------
[ "$(id -u)" = "0" ] || err "请以 root 运行: sudo bash $0"
case "$(uname -m)" in x86_64|amd64) ;; *) err "仅支持 x86_64 架构" ;; esac
command -v apt-get >/dev/null 2>&1 || err "仅支持 Debian/Ubuntu 系系统"

# ---------- 1. 定位源码根目录 ----------
SRC_DIR="$(cd "$(dirname "$0")" && pwd)"
if [ ! -f "$SRC_DIR/index.php" ] && [ -f "$SRC_DIR/faka/index.php" ]; then
  SRC_DIR="$SRC_DIR/faka"
fi
[ -f "$SRC_DIR/index.php" ] || err "未找到 index.php, 请将脚本与源码放在同一目录"
SQL_FILE="$(find "$SRC_DIR" -maxdepth 2 -name '*.sql' | head -1 || true)"
[ -n "$SQL_FILE" ] || warn "未找到 .sql 数据库文件, 将跳过导入(需手动导库)"

# ---------- 2. 系统信息 / 更新 / Swap (512M 小内存必开) ----------
. /etc/os-release
info "系统: $NAME $VERSION_ID ($VERSION_CODENAME)"
info "内存: $(free -m | awk '/Mem:/{print $2}')MB"

export DEBIAN_FRONTEND=noninteractive
apt-get update -y
apt-get upgrade -y ${MIN}
# 最小化基础包: 不装 vim/git 等大件, 需要时再 apt-get install
apt-get install -y ${MIN} curl wget rsync openssl ca-certificates unzip gnupg2 jq

# 数据库密码: 未指定环境变量则自动随机生成 (部署完成信息里会显示)
DB_PASS="${DB_PASS:-$(openssl rand -hex 12)}"

TOTAL_MEM=$(free -m | awk '/Mem:/{print $2}')
if ! swapon --show | grep -q swapfile; then
  if [ "${TOTAL_MEM:-0}" -lt 1536 ]; then
    info "创建 2G Swap (512M 内存必做) ..."
    fallocate -l 2G /swapfile 2>/dev/null || dd if=/dev/zero of=/swapfile bs=1M count=2048 status=none
    chmod 600 /swapfile
    mkswap /swapfile >/dev/null
    swapon /swapfile
    grep -q '^/swapfile' /etc/fstab || echo '/swapfile none swap sw 0 0' >> /etc/fstab
    echo 'vm.swappiness=10' > /etc/sysctl.d/99-swap.conf
    sysctl -w vm.swappiness=10 >/dev/null 2>&1 || true
  fi
fi

# ---------- 3. 选 PHP 版本 (优先 8.2, 老代码兼容; Debian 13 官方无 8.2 走 SURY) ----------
choose_php() {
  for v in 8.2 8.3 8.4; do
    if apt-cache show "php${v}-fpm" >/dev/null 2>&1; then
      echo "$v"; return
    fi
  done
  echo ""
}

if [ "${VERSION_ID}" = "13" ]; then
  if ! apt-cache show php8.2-fpm >/dev/null 2>&1; then
    info "Debian 13 官方源无 php8.2, 添加 SURY 官方 PHP 源 ..."
    curl -fsSL https://packages.sury.org/php/apt.gpg | gpg --dearmor -o /usr/share/keyrings/sury-php.gpg
    echo "deb [signed-by=/usr/share/keyrings/sury-php.gpg] https://packages.sury.org/php/ ${VERSION_CODENAME} main" \
      > /etc/apt/sources.list.d/php.sources.list
    apt-get update -y
  fi
fi

PHP_VER="$(choose_php)"
[ -n "$PHP_VER" ] || err "未找到可用 PHP 版本"
info "选用 PHP ${PHP_VER}"

# ---------- 4. 安装 Nginx + PHP + MariaDB (最小依赖) ----------
PHP_PKGS="php${PHP_VER}-fpm php${PHP_VER}-mysql php${PHP_VER}-mbstring \
  php${PHP_VER}-gd php${PHP_VER}-curl php${PHP_VER}-xml \
  php${PHP_VER}-zip php${PHP_VER}-opcache php${PHP_VER}-bcmath php${PHP_VER}-gmp"
REAL_PKGS=""
for p in ${PHP_PKGS}; do
  if apt-cache show "${p}" >/dev/null 2>&1; then
    REAL_PKGS="${REAL_PKGS} ${p}"
  else
    info "跳过(源中不存在): ${p}"
  fi
done
apt-get install -y ${MIN} nginx ${REAL_PKGS} mariadb-server
systemctl enable --now nginx php${PHP_VER}-fpm mariadb 2>/dev/null || true
# 立即释放安装包缓存
apt-get clean

# ---------- 5. PHP-FPM 瘦身 (512M: static 2 进程, 防泄漏) ----------
FPM_POOL="/etc/php/${PHP_VER}/fpm/pool.d/www.conf"
if [ -f "${FPM_POOL}" ]; then
  tee "${FPM_POOL}" > /dev/null <<EOF
[www]
user = www-data
group = www-data
listen = /run/php/php${PHP_VER}-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660
pm = static
pm.max_children = 2
pm.max_requests = 200
listen.backlog = 64
request_terminate_timeout = 60
EOF
fi
PHP_INI="/etc/php/${PHP_VER}/fpm/php.ini"
if [ -f "${PHP_INI}" ]; then
  sed -i 's/^memory_limit = .*/memory_limit = 128M/' "${PHP_INI}"
  sed -i 's/^;opcache.enable = .*/opcache.enable = 1/' "${PHP_INI}"
  sed -i 's/^;opcache.memory_consumption = .*/opcache.memory_consumption = 32/' "${PHP_INI}"
  sed -i 's/^;opcache.max_accelerated_files = .*/opcache.max_accelerated_files = 4000/' "${PHP_INI}"
  sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 20M/' "${PHP_INI}"
  sed -i 's/^post_max_size = .*/post_max_size = 20M/' "${PHP_INI}"
fi

# ---------- 6. MariaDB 瘦身 (512M: 16M 缓冲池, 关日志/性能库) ----------
tee /etc/mysql/mariadb.conf.d/60-lowmem.cnf > /dev/null <<'EOF'
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
innodb_use_native_aio=0
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
systemctl restart mariadb
sleep 2

# ---------- 7. 建库 + 导入 ----------
info "创建数据库 ${DB_NAME} 并导入 ..."
mysql <<EOF
CREATE DATABASE IF NOT EXISTS ${DB_NAME} DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
EOF
if [ -n "$SQL_FILE" ]; then
  mysql --default-character-set=utf8mb4 "${DB_NAME}" < "$SQL_FILE"
  info "已导入: ${SQL_FILE}"
fi

# ---------- 8. 部署代码到 Web 根目录 ----------
info "部署代码 → ${WEB_ROOT}"
mkdir -p "$WEB_ROOT"
rsync -a --exclude='.git' --exclude='*.zip' --exclude='*.sql' \
  --exclude='*.sh' --exclude='*.txt' \
  "${SRC_DIR}/" "${WEB_ROOT}/"

# config.php (变量注入: 数据库密码 + 随机认证密钥, 每次部署全新)
AUTH_KEY="$(openssl rand -hex 24)"
AUTH_COOKIE_NAME="DC_AUTHCOOKIE_$(echo -n "$AUTH_KEY" | md5sum | cut -c1-16)"

tee "${WEB_ROOT}/config.php" > /dev/null <<EOF
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

# 目录权限
chown -R www-data:www-data "$WEB_ROOT"
find "$WEB_ROOT" -type d -exec chmod 755 {} \;
find "$WEB_ROOT" -type f -exec chmod 644 {} \;
chmod -R 775 "$WEB_ROOT/content" "$WEB_ROOT/include"

# 删除安装向导, 防止被恶意重装
rm -f "$WEB_ROOT/install.php"

# ---------- 9. Cloudflare Origin CA 证书 (可选) ----------
USE_SSL=0
SSL_DIR="/etc/ssl/cloudflare"
HAVE_CF_AUTH=0
if [ "${DOMAIN}" != "_" ] && [ -z "$CF_TOKEN" ] && [ -z "$CF_KEY" ]; then
  warn "未提供 CF_API_TOKEN/CF_ZONE_ID, 将仅部署 HTTP (需要 HTTPS 时: CF_API_TOKEN=xx CF_ZONE_ID=yy bash $0 $DOMAIN)"
fi
if [ -n "$CF_TOKEN" ]; then
  HAVE_CF_AUTH=1
  CF_CURL_AUTH=( -H "Authorization: Bearer ${CF_TOKEN}" )
elif [ -n "$CF_KEY" ] && [ -n "$CF_EMAIL" ]; then
  HAVE_CF_AUTH=1
  CF_CURL_AUTH=( -H "X-Auth-Email: ${CF_EMAIL}" -H "X-Auth-Key: ${CF_KEY}" )
fi

if [ "$HAVE_CF_AUTH" = "1" ] && [ "${DOMAIN}" != "_" ]; then
  if [ -z "$CF_ZONE" ]; then
    warn "未提供 CF_ZONE_ID, 跳过 HTTPS; 如需 HTTPS 请设置 CF_ZONE_ID"
  else
    info "通过 Cloudflare API 申请 Origin CA 证书 → ${DOMAIN} + *.${DOMAIN} (15年) ..."
    BODY=$(jq -cn --arg d "$DOMAIN" \
      '{hostnames: [$d, ("*." + $d)], requested_validity: 5475, request_type: "origin-rsa", csr: ""}')
    if RESP=$(curl -fsSL --max-time 40 -X POST "${CF_API}/certificates" \
      "${CF_CURL_AUTH[@]}" \
      -H "Content-Type: application/json" \
      --data "$BODY" 2>/dev/null); then
      if [ "$(echo "$RESP" | jq -r .success)" = "true" ]; then
        mkdir -p "$SSL_DIR"
        echo "$RESP" | jq -r .result.certificate > "${SSL_DIR}/fullchain.pem"
        echo "$RESP" | jq -r .result.private_key > "${SSL_DIR}/privkey.pem"
        chmod 644 "${SSL_DIR}/fullchain.pem"
        chmod 600 "${SSL_DIR}/privkey.pem"
        USE_SSL=1
        info "证书已保存: ${SSL_DIR}/fullchain.pem"
      else
        MSG=$(echo "$RESP" | jq -r '.errors[0].message // "未知错误"')
        warn "Cloudflare API 申请失败: ${MSG} (继续 HTTP 部署)"
      fi
    else
      warn "Cloudflare API 请求失败(网络/权限), 继续 HTTP 部署"
    fi
  fi
elif [ "$HAVE_CF_AUTH" = "1" ]; then
  warn "CF 凭据已设置但未传域名参数, 跳过 HTTPS (用法: CF_API_TOKEN=xx CF_ZONE_ID=yy bash $0 域名)"
fi

# ---------- 10. Nginx 瘦身 + 站点配置 ----------
# 1核 CPU: 固定 worker_processes=1; 隐藏版本号
sed -i 's/^worker_processes.*/worker_processes 1;/' /etc/nginx/nginx.conf
grep -q 'server_tokens off' /etc/nginx/nginx.conf || \
  sed -i '/^http {/a \    server_tokens off;' /etc/nginx/nginx.conf

# 伪静态: 支付回调 /action/ 路由依赖 try_files
if [ "$USE_SSL" = "1" ]; then
tee /etc/nginx/sites-available/faka > /dev/null <<EOF
# HTTP → HTTPS 跳转
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};
    return 301 https://\$host\$request_uri;
}

server {
    listen 443 ssl;
    listen [::]:443 ssl;
    http2 on;
    server_name ${DOMAIN};
    root ${WEB_ROOT};
    index index.php index.html;

    ssl_certificate ${SSL_DIR}/fullchain.pem;
    ssl_certificate_key ${SSL_DIR}/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 1d;

    charset utf-8;
    client_max_body_size 20m;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php${PHP_VER}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
    }

    # 禁止执行缓存/上传/模板/插件/日志目录中的 PHP (防 webshell)
    location ~* ^/(content/(cache|upload|templates|user_templates|plugins|blog_templates|bottom_nav_templates|common|backup|log)|include)/.*\.php\$ { deny all; }

    # 隐藏点文件
    location ~ /\. { deny all; }

    # 静态资源缓存
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|webp|woff2?)$ { expires 7d; access_log off; }
}
EOF
else
tee /etc/nginx/sites-available/faka > /dev/null <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};
    root ${WEB_ROOT};
    index index.php index.html;

    charset utf-8;
    client_max_body_size 20m;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php${PHP_VER}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
    }

    # 禁止执行缓存/上传/模板/插件/日志目录中的 PHP (防 webshell)
    location ~* ^/(content/(cache|upload|templates|user_templates|plugins|blog_templates|bottom_nav_templates|common|backup|log)|include)/.*\.php\$ { deny all; }

    # 隐藏点文件
    location ~ /\. { deny all; }

    # 静态资源缓存
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|webp|woff2?)$ { expires 7d; access_log off; }
}
EOF
fi
ln -sf /etc/nginx/sites-available/faka /etc/nginx/sites-enabled/faka
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl restart nginx php${PHP_VER}-fpm

# ---------- 11. 磁盘瘦身 (30GB 小盘) ----------
info "磁盘瘦身: 清理 apt 缓存 / 旧内核 / 日志 ..."
apt-get autoremove --purge -y >/dev/null 2>&1 || true
apt-get clean
journalctl --vacuum-size=50M >/dev/null 2>&1 || true
rm -f /var/log/nginx/*.log.* /var/log/nginx/error.log.1 2>/dev/null || true

# ---------- 12. 防火墙 (可选) ----------
if command -v ufw >/dev/null 2>&1; then
  ufw allow 80/tcp >/dev/null 2>&1 || true
  [ "$USE_SSL" = "1" ] && ufw allow 443/tcp >/dev/null 2>&1 || true
  info "ufw 已放行所需端口"
fi

# ---------- 13. 完成 ----------
IP=$(hostname -I 2>/dev/null | awk '{print $1}')
UNAME_TIME="$(date '+%Y-%m-%d %H:%M')"
echo ""
echo "=============================================="
echo "  DCSHOP faka 部署完成! 部署时间: ${UNAME_TIME}"
echo "----------------------------------------------"
if [ "$USE_SSL" = "1" ]; then
  echo "  前台地址 : https://${DOMAIN}/"
  echo "  后台地址 : https://${DOMAIN}/admin/"
  echo "  SSL     : Cloudflare Origin CA (Full strict 模式)"
  echo "  证书路径 : ${SSL_DIR}/fullchain.pem"
else
  echo "  前台地址 : http://${IP}/"
  echo "  后台地址 : http://${IP}/admin/"
  echo "  SSL     : 未启用(HTTP)"
fi
echo "  PHP     : ${PHP_VER} (Nginx + FPM)"
echo "  数据库  : ${DB_NAME} / ${DB_USER} / ${DB_PASS}"
echo "  站点目录: ${WEB_ROOT}"
echo "  资源优化: worker=1 / FPM=2 / MariaDB lowmem / Swap=2G"
echo "----------------------------------------------"
echo "  提示:"
if [ "$USE_SSL" = "1" ]; then
  echo "  - Cloudflare SSL/TLS 模式请设为 Full (strict)"
  echo "  - 确认域名已开启 Cloudflare 代理(橙色云朵)"
fi
echo "  - 管理员账号为数据库中原有账号 (可查 dc_admin 表)"
echo "  - 域名解析: 将域名 A 记录指向 ${IP} 后改 server_name"
echo "=============================================="