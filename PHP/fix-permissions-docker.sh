#!/bin/bash
# Fix permissions script for Docker
# Run this inside the container as root

echo "Fixing permissions in Docker container..."

# Create directories if they don't exist
mkdir -p /var/www/html/storage/photos
mkdir -p /var/www/html/config

# Set ownership
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/config

# Set permissions
chmod -R 777 /var/www/html/storage
chmod -R 777 /var/www/html/config

echo "Permissions fixed!"
echo "Storage writable: $(test -w /var/www/html/storage && echo 'YES' || echo 'NO')"
echo "Config writable: $(test -w /var/www/html/config && echo 'YES' || echo 'NO')"
echo "Storage/photos writable: $(test -w /var/www/html/storage/photos && echo 'YES' || echo 'NO')"
