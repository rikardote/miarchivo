FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    ca-certificates \
    gnupg

# Install Node.js 22 from nodesource
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl sockets

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copiar configuración personalizada de PHP
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

# Copiar el script de inicio
COPY docker/php/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port 9000 and start php-fpm server via entrypoint
EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]
