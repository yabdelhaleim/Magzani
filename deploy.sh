#!/bin/bash
set -e

cd /var/www/kayan.site

echo "========================================"
echo "🚀 Deploy started at $(date)"
echo "========================================"

# 1) Maintenance mode
echo "🔒 Maintenance mode ON..."
php artisan down --retry=60 || true

# 2) Clear stale caches (CRITICAL — avoid class-not-found after composer install)
echo "🧹 Clearing stale caches..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
rm -f bootstrap/cache/packages.php 2>/dev/null
rm -f bootstrap/cache/services.php 2>/dev/null

# 3) Defensive: unlock platform_check.php if it was locked with chattr +i
echo "🔓 Defensive: unlock platform_check.php..."
chattr -i vendor/composer/platform_check.php 2>/dev/null || true
rm -f vendor/composer/platform_check.php 2>/dev/null

# 4) Git pull
echo "📥 Pulling latest..."
git pull origin main

# 5) Composer install (--no-dev for production)
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# 6) IMPORTANT: Write our workaround stub (NO chattr +i — keep file writable!)
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

# 9) Run migrations
echo "🗄️  Running migrations..."
php artisan migrate --force

# 10) Exit maintenance mode
echo "🚀 Going live..."
php artisan up

echo ""
echo "========================================"
echo "✅ Deploy completed at $(date)"
echo "========================================"