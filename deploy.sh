#!/bin/bash
set -e

cd /var/www/kayan.site

echo "========================================"
echo "🚀 Deploy started at $(date)"
echo "🏠 Mode: Local Inventory (non-SaaS)"
echo "========================================"

# 1) Remove stale cache FILES (no artisan yet — wrong branch may cause class errors)
echo "🧹 Removing stale cache files..."
rm -f bootstrap/cache/packages.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/routes-v7.php
rm -f bootstrap/cache/events.php

# 2) Unlock platform_check.php
echo "🔓 Unlocking platform_check.php..."
chattr -i vendor/composer/platform_check.php 2>/dev/null || true
rm -f vendor/composer/platform_check.php 2>/dev/null

# 3) Git — switch to local inventory branch FIRST (before any artisan)
echo "📥 Switching to local-inventory-version branch..."
git fetch origin
git checkout -- .
git checkout local-inventory-version
git pull origin local-inventory-version
git clean -fd database/migrations/

# 4) Composer install — installs correct packages for this branch (no stancl/tenancy)
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# 5) Write platform_check workaround stub
echo "🔒 Writing platform_check workaround..."
cat > vendor/composer/platform_check.php <<'STUB_EOF'
<?php // PHP 8.2 workaround (composer platform-check disabled)
STUB_EOF

# 6) Regenerate package discovery
echo "🔍 Regenerating package discovery..."
php artisan package:discover --ansi

# 7) Maintenance mode (NOW safe — correct branch + packages loaded)
echo "🔒 Maintenance mode ON..."
php artisan down --retry=60 || true

# 8) Clear caches via artisan
echo "🧹 Clearing caches..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# 9) Build production caches
echo "⚡ Building production caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 10) Fresh migrations (drops all tables first — clean slate)
echo "🗄️  Running fresh migrations..."
php artisan migrate:fresh --force

# 11) Seed default data
echo "🌱 Seeding default data..."
php artisan db:seed --force

# 12) Go live
echo "🚀 Going live..."
php artisan up

echo ""
echo "========================================"
echo "✅ Deploy completed at $(date)"
echo "🏠 Running: Local Inventory Version"
echo "========================================"