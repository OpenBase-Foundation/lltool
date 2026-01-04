FROM php:8.2-apache

# System dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpq-dev \
    libzip-dev \
    zlib1g-dev \
    libgd-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    gosu \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set workdir
WORKDIR /var/www/html

# Create necessary directories and set permissions
RUN mkdir -p /var/www/html/storage/photos /var/www/html/config && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 775 /var/www/html/storage /var/www/html/config

# Copy Apache configuration
RUN echo '<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/sites-available/000-default.conf

# Create entrypoint script
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
# Fix permissions on startup (run as root)\n\
echo "Setting up directories and permissions..."\n\
mkdir -p /var/www/html/storage/photos /var/www/html/config\n\
chown -R www-data:www-data /var/www/html/storage /var/www/html/config\n\
chmod -R 777 /var/www/html/storage /var/www/html/config\n\
\n\
# Fix Apache log directory permissions and create log files\n\
mkdir -p /var/log/apache2\n\
touch /var/log/apache2/error.log /var/log/apache2/access.log\n\
chown -R www-data:www-data /var/log/apache2\n\
chmod -R 755 /var/log/apache2\n\
chmod 666 /var/log/apache2/*.log\n\
\n\
# Set ServerName to suppress warning\n\
echo "ServerName localhost" >> /etc/apache2/apache2.conf\n\
\n\
echo "Permissions set successfully!"\n\
\n\
# Install/update composer dependencies if composer.json exists\n\
if [ -f "/var/www/html/composer.json" ]; then\n\
    echo "Installing/updating Composer dependencies..."\n\
    \n\
    # Always run update to ensure lock file is up to date\n\
    # This handles both new installs and outdated lock files\n\
    composer update --no-dev --no-interaction --optimize-autoloader\n\
    \n\
    echo "Dependencies installed successfully!"\n\
fi\n\
\n\
# Start Apache as www-data\n\
exec gosu www-data apache2-foreground' > /usr/local/bin/docker-entrypoint.sh

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Default command
CMD ["/usr/local/bin/docker-entrypoint.sh"]
