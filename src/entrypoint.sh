#!/bin/sh
set -e

# Set permissions. This will succeed on a clean application structure.
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Check if the .env file has been mounted and if the APP_KEY is missing.
if [ -f "/var/www/html/.env" ] && ! grep -q "APP_KEY=base64:" "/var/www/html/.env"; then
    echo "Generating Laravel application key..."
    su -s /bin/sh -c "php artisan key:generate --force" www-data
fi

# Now that the app is confirmed to be working, cache everything for performance.
echo "Caching Laravel configuration and routes for performance..."
su -s /bin/sh -c "php artisan config:cache && php artisan route:cache && php artisan view:cache" www-data

# Start php-fpm in the background.
php-fpm &

# Prepare for and execute gcsfuse as the main container process.
GCS_MOUNT_DIR="/var/www/html/storage/app/gcs"
mkdir -p "$GCS_MOUNT_DIR"
chown -R www-data:www-data "$GCS_MOUNT_DIR"

echo "Attempting to mount GCS bucket '$GCS_BUCKET_NAME' to '$GCS_MOUNT_DIR'..."
exec gcsfuse \
  --key-file /etc/gcp/gcs-key.json \
  --implicit-dirs \
  --foreground \
  -o allow_other \
  --uid $(id -u www-data) \
  --gid $(id -g www-data) \
  "$GCS_BUCKET_NAME" "$GCS_MOUNT_DIR"