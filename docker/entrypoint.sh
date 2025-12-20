#!/bin/sh
set -e

cd /var/www/html

# If .env does not exist, create it
if [ ! -f ".env" ]; then
  echo "📄 .env not found. Creating from .env.docker..."
  cp .env.docker .env
fi

# If APP_KEY is missing, generate it
if ! grep -q "^APP_KEY=" .env || grep -q "^APP_KEY=$" .env; then
  echo "🔐 APP_KEY missing. Generating..."
  php artisan key:generate --force
fi

# Clear cached config (important)
php artisan optimize:clear

echo "✅ Laravel initialization complete"

exec "$@"
