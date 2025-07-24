# FileManager Pro - Modern File Management System
# Multi-stage Docker build for production deployment

FROM php:8.1-apache as base

# Metadata
LABEL maintainer="whympxx <whympxx@github.com>"
LABEL version="1.2.0"
LABEL description="FileManager Pro - Modern File Management System"

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    unzip \
    curl \
    vim \
    nano \
    htop \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        gd \
        fileinfo \
        mbstring \
        zip \
        xml \
        bcmath \
        curl

# Enable Apache modules
RUN a2enmod rewrite headers ssl expires deflate

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Development stage
FROM base as development

# Set PHP configuration for development
RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && mkdir -p uploads logs \
    && chmod -R 755 uploads logs \
    && chown -R www-data:www-data uploads logs

# Copy development Apache config
COPY docker/apache-dev.conf /etc/apache2/sites-available/000-default.conf

# Expose port
EXPOSE 80

# Production stage
FROM base as production

# Set PHP configuration for production
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Add custom PHP configuration
RUN echo "upload_max_filesize = 100M" >> $PHP_INI_DIR/conf.d/uploads.ini \
    && echo "post_max_size = 105M" >> $PHP_INI_DIR/conf.d/uploads.ini \
    && echo "max_execution_time = 300" >> $PHP_INI_DIR/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> $PHP_INI_DIR/conf.d/uploads.ini \
    && echo "max_file_uploads = 20" >> $PHP_INI_DIR/conf.d/uploads.ini

# Enable OPcache for production
RUN echo "opcache.enable=1" >> $PHP_INI_DIR/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> $PHP_INI_DIR/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=8" >> $PHP_INI_DIR/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=4000" >> $PHP_INI_DIR/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=2" >> $PHP_INI_DIR/conf.d/opcache.ini \
    && echo "opcache.fast_shutdown=1" >> $PHP_INI_DIR/conf.d/opcache.ini

# Set working directory
WORKDIR /var/www/html

# Copy application files (excluding development files)
COPY --chown=www-data:www-data . .

# Remove development files
RUN rm -rf \
    .git* \
    docker-compose*.yml \
    Dockerfile* \
    *.md \
    tests/ \
    docs/ \
    .env.example \
    push_docs.*

# Set secure permissions
RUN find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && chmod -R 700 config \
    && chmod 600 config/*.php \
    && mkdir -p uploads/{files,thumbnails,temp} logs \
    && chmod -R 755 uploads logs \
    && chown -R www-data:www-data uploads logs

# Copy production Apache config
COPY docker/apache-prod.conf /etc/apache2/sites-available/000-default.conf

# Add health check script
COPY docker/health-check.php /var/www/html/health

# Create health check endpoint
RUN echo '<?php' > /var/www/html/health.php \
    && echo 'header("Content-Type: application/json");' >> /var/www/html/health.php \
    && echo 'echo json_encode(["status" => "ok", "version" => "1.2.0", "timestamp" => date("c")]);' >> /var/www/html/health.php

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health.php || exit 1

# Expose ports
EXPOSE 80 443

# Start Apache
CMD ["apache2-foreground"]

# Build information
RUN echo "FileManager Pro v1.2.0" > /var/www/html/.version \
    && echo "Built on: $(date)" >> /var/www/html/.version \
    && echo "Image: filemanager-pro:latest" >> /var/www/html/.version
