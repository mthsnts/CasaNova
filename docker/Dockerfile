FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Add user and group with specific IDs
RUN groupadd -g 1000 sail && \
    useradd -u 1000 -g sail -m sail

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libzip-dev \
    libonig-dev \
    libwebp-dev \
    libgd-dev \
    supervisor \
    nodejs \
    npm \
    libpq-dev  # Required for PostgreSQL

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql pgsql mbstring zip exif pcntl  # Changed from pdo_mysql to pdo_pgsql
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-install gd

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set up composer directory for sail user
RUN mkdir -p /home/sail/.composer && \
    chown -R sail:sail /home/sail/.composer

# Install Node.js and npm
RUN curl -sL https://deb.nodesource.com/setup_20.x | bash -
RUN apt-get install -y nodejs
RUN npm install -g npm

# Copy composer files first for better caching
COPY ../composer.json ../composer.lock ./
RUN composer install --no-scripts --no-autoloader

# Copy the rest of the application code
COPY .. /var/www/html

# Copy permission script
COPY /docker/set-permissions.sh /usr/local/bin/set-permissions
RUN chmod +x /usr/local/bin/set-permissions

# Set proper permissions for Laravel
RUN mkdir -p /var/www/html/storage/framework/{sessions,views,cache}
RUN mkdir -p /var/www/html/storage/logs
RUN mkdir -p /var/www/html/bootstrap/cache

# Set permissions script to run on container start
COPY /docker/start-container /usr/local/bin/start-container
RUN chmod +x /usr/local/bin/start-container

# Copy supervisor configuration
COPY /docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy custom php.ini
COPY /docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Set working directory permissions
RUN chown -R sail:sail /var/www/html
RUN chmod -R 777 /var/www/html/storage
RUN chmod -R 777 /var/www/html/bootstrap/cache
RUN chmod -R 777 /var/www/html

# Create supervisor log directory
RUN mkdir -p /var/log/supervisor

# Switch to non-root user
USER sail

# Generate autoloader and run scripts
RUN composer dump-autoload --optimize

# Install project dependencies
RUN npm install

# Build assets
RUN npm run build

# Switch back to root for entrypoint
USER root

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Start supervisor
ENTRYPOINT ["start-container"] 