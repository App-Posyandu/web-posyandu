#!/bin/sh
set -e

# 1. Wait for PostgreSQL to be ready using PHP PDO
echo "Waiting for PostgreSQL database to be online..."
php -r "
\$host = getenv('DB_HOST') ?: '127.0.0.1';
\$port = getenv('DB_PORT') ?: '5432';
\$user = getenv('DB_USERNAME') ?: 'postgres';
\$pass = getenv('DB_PASSWORD') ?: '';
\$db = getenv('DB_DATABASE') ?: 'sapa_posyandu';

\$max_tries = 30;
for (\$i = 0; \$i < \$max_tries; \$i++) {
    try {
        \$pdo = new PDO(\"pgsql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass);
        echo \"Database connection successful!\n\";
        exit(0);
    } catch (PDOException \$e) {
        echo \"Waiting for database (\$i/\$max_tries) - \" . \$e->getMessage() . \"\n\";
        sleep(2);
    }
}
exit(1);
"

# 2. Check if .env file exists, copy example if it doesn't
if [ ! -f .env ]; then
    echo "Copying .env.example to .env..."
    cp .env.example .env
fi

# 3. Check for APP_KEY and generate if empty
if ! grep -q "APP_KEY=base64" .env && [ -z "$APP_KEY" ]; then
    echo "Generating Laravel App Key..."
    php artisan key:generate --force
fi

# 4. Clear compiled bootstrap cache so stale local dev package discovery is regenerated
echo "Clearing compiled bootstrap cache..."
php artisan clear-compiled 2>/dev/null || rm -f bootstrap/cache/packages.php bootstrap/cache/services.php bootstrap/cache/config.php bootstrap/cache/routes-v7.php

# 5. Run Laravel Migrations
echo "Running database migrations..."
php artisan migrate --force

# 6. Conditionally run database seeding (default: false to prevent duplicate seedings on restart)
if [ "$RUN_SEEDERS" = "true" ]; then
    echo "Seeding database..."
    php artisan db:seed --force
fi

# 7. Optimize Laravel configurations
# Config and route caching always on (major perf win on Docker/Windows volume mounts).
# View cache only in production to allow Blade edits in dev without container restart.
echo "Caching Laravel config and routes..."
php artisan config:cache
php artisan route:cache

if [ "$APP_ENV" = "production" ]; then
    echo "Caching views for production..."
    php artisan view:cache
else
    php artisan view:clear
fi

# 8. Correct permissions for Laravel Storage & Cache
echo "Setting storage and bootstrap/cache permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 9. Start supervisord (which starts nginx & php-fpm)
echo "Starting application services (Nginx, PHP-FPM, Queue Worker)..."
exec "$@"
