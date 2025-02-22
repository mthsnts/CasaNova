#!/bin/bash

# Set ownership of all files to sail user
chown -R sail:sail /var/www/html

# Set directory permissions
find /var/www/html -type d -exec chmod 777 {} \;

# Set file permissions
find /var/www/html -type f -exec chmod 666 {} \;

# Make shell scripts executable
find /var/www/html -type f -name "*.sh" -exec chmod +x {} \;

# Ensure storage and cache directories are writable
chmod -R 777 /var/www/html/storage
chmod -R 777 /var/www/html/bootstrap/cache

# Make artisan executable
chmod +x /var/www/html/artisan 