#!/bin/bash
# =========================================================
#  faka (DCSHOP) 一键卸载脚本
#  适用于 Debian 13 (trixie) + PHP 8.2 (SURY) + Nginx + MariaDB
#  用法: sh uninstall.sh
#  警告: 会删除站点代码 /var/www/faka、数据库 dcshop、相关软件包
# =========================================================
set -e

WEB_ROOT="/var/www/faka"
DB_NAME="dcshop"
DB_USER="dcshop"
PHP_VER="8.2"

echo "============================================="
echo "  即将卸载以下内容:"
echo "  - 网站代码: ${WEB_ROOT}"
echo "  - Nginx 站点配置"
echo "  - 数据库: ${DB_NAME} / 用户: ${DB_USER}"
echo "  - PHP ${PHP_VER} + SURY 源"
echo "  - Nginx / MariaDB / 相关扩展"
echo "  - Swap (swapfile)"
echo "============================================="
read -p "输入 YES 确认卸载: " CONFIRM
[ "${CONFIRM}" = "YES" ] || { echo "已取消。"; exit 1; }

echo ""
echo ">>> 1. 停止并禁用服务"
systemctl stop nginx 2>/dev/null || true
systemctl disable nginx 2>/dev/null || true
systemctl stop php${PHP_VER}-fpm 2>/dev/null || true
systemctl disable php${PHP_VER}-fpm 2>/dev/null || true
systemctl stop mariadb 2>/dev/null || true
systemctl disable mariadb 2>/dev/null || true

echo ">>> 2. 删除数据库 (${DB_NAME}) 与用户"
mysql -uroot <<EOF 2>/dev/null || true
DROP DATABASE IF EXISTS ${DB_NAME};
DROP USER IF EXISTS '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
EOF

echo ">>> 3. 删除网站代码"
rm -rf ${WEB_ROOT}

echo ">>> 4. 删除 Nginx 站点配置"
rm -f /etc/nginx/sites-available/faka
rm -f /etc/nginx/sites-enabled/faka

echo ">>> 5. 删除 PHP ${PHP_VER} 扩展"
apt purge -y php${PHP_VER}-* 2>/dev/null || true

echo ">>> 6. 删除 Nginx / MariaDB"
apt purge -y nginx nginx-common nginx-core mariadb-server mariadb-client mariadb-common 2>/dev/null || true

echo ">>> 7. 自动清理无用依赖"
apt autoremove -y 2>/dev/null || true

echo ">>> 8. 删除 SURY PHP 源"
rm -f /etc/apt/sources.list.d/php.sources.list /etc/apt/sources.list.d/php.sources 2>/dev/null || true
rm -f /usr/share/keyrings/sury-php.gpg 2>/dev/null || true
apt update 2>/dev/null || true

echo ">>> 9. 关闭并删除 Swap"
swapoff /swapfile 2>/dev/null || true
rm -f /swapfile 2>/dev/null || true
sed -i '/swapfile/d' /etc/fstab 2>/dev/null || true

echo ">>> 10. 清理 Let's Encrypt / certbot 相关（如存在）"
apt purge -y certbot python3-certbot-nginx 2>/dev/null || true
rm -rf /etc/letsencrypt 2>/dev/null || true

echo ""
echo "============================================="
echo "  卸载完成！"
echo "  已删除网站、数据库、Nginx、PHP/MariaDB、SURY源、Swap"
echo "  若想彻底移除 php8.2 的 SURY 依赖组, 可再执行:"
echo "    apt autoclean && apt --fix-broken install"
echo "============================================="
