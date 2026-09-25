#!/bin/bash
set -e

cd /var/www/kayan.site

echo "========================================"
echo "🚀 Deploy started at $(date)"
echo "🏠 Mode: Local Inventory (non-SaaS)"
echo "========================================"

# 0) PRE-CLEANUP — remove stale caches before any artisan command
echo "🧹 Pre-cleanup: removing stale caches..."
rm -f bootstrap/cache/packages.php 2>/dev/null
rm -f bootstrap/cache/services.php 2>/dev/null
rm -f bootstrap/cache/config.php 2>/dev/null
rm -f bootstrap/cache/routes-v7.php 2>/dev/null
rm -f bootstrap/cache/events.php 2>/dev/null

# 1) Maintenance mode
echo "🔒 Maintenance mode ON..."
php artisan down --retry=60 || true

# 2) Clear stale caches via artisan
echo "🧹 Clearing stale caches via artisan..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# 3) Defensive: unlock platform_check.php
echo "🔓 Defensive: unlock platform_check.php..."
chattr -i vendor/composer/platform_check.php 2>/dev/null || true
rm -f vendor/composer/platform_check.php 2>/dev/null

# 4) Git — switch to local inventory branch (non-SaaS)
echo "📥 Switching to local-inventory-version branch..."
git fetch origin
git checkout -- .
git checkout local-inventory-version
git pull origin local-inventory-version

# 5) Composer install (--no-dev for production)
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# 6) Write platform_check workaround stub
echo "🔒 Writing platform_check workaround..."
cat > vendor/composer/platform_check.php <<'STUB_EOF'
<?php // PHP 8.2 workaround (composer platform-check disabled)
STUB_EOF

# 7) Regenerate package discovery cache
echo "🔍 Regenerating package discovery..."
php artisan package:discover --ansi

# 8) Build production caches
echo "⚡ Building production caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 9) Run migrations (fresh start — DB was cleared)
echo "🗄️  Running migrations..."
php artisan migrate --force

# 10) Seed default data
echo "🌱 Seeding default data..."
php artisan db:seed --force

# 11) Exit maintenance mode
echo "🚀 Going live..."
php artisan up

echo ""
echo "========================================"
echo "✅ Deploy completed at $(date)"
echo "🏠 Running: Local Inventory Version"
echo "========================================"