#!/bin/sh
set -e

if [ ! -f "vendor/autoload.php" ]; then
  echo "Instal·lant dependències Composer..."
  composer install --no-interaction --prefer-dist --optimize-autoloader
fi

if [ ! -f ".env" ] && [ -f ".env.example" ]; then
  cp .env.example .env
fi

if [ -f ".env" ] && ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
  php artisan key:generate --force
fi

exec php artisan serve --host=0.0.0.0 --port=8000
