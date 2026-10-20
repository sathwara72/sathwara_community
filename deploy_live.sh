#!/bin/bash
set -e

# Colors
GREEN='\033[0;32m'
CYAN='\033[0;36m'
YELLOW='\033[1;33m'
BOLD='\033[1m'
NC='\033[0m'

echo ""
echo -e "${BOLD}==========================================${NC}"
echo -e "${BOLD}  🚀 Satwara Community — Local Deployment${NC}"
echo -e "${BOLD}==========================================${NC}"

echo -e "\n${CYAN}[1/5] Pulling latest code...${NC}"
git pull origin main || git pull

echo -e "\n${CYAN}[2/6] Installing PHP dependencies...${NC}"
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-interaction --prefer-dist --optimize-autoloader

echo -e "\n${CYAN}[3/6] Running database migrations...${NC}"
php artisan migrate --force

echo -e "\n${CYAN}[4/6] Clearing and optimizing caches...${NC}"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo -e "\n${CYAN}[5/6] Building frontend assets...${NC}"
if [ -f "package.json" ]; then
    if command -v npm &> /dev/null; then
        npm install
        npm run build
    else
        echo -e "${YELLOW}npm not found, skipping asset build.${NC}"
    fi
fi

echo -e "\n${CYAN}[6/6] Setting storage & cache permissions...${NC}"
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
chown -R www-data:www-data storage bootstrap/cache public 2>/dev/null || true

echo ""
echo -e "${GREEN}${BOLD}==========================================${NC}"
echo -e "${GREEN}${BOLD}  Deployment Successfully Completed!${NC}"
echo -e "${GREEN}${BOLD}==========================================${NC}"
echo ""
