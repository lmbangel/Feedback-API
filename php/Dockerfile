FROM php:8.2-cli

# Install system packages and PHP extensions
RUN apt-get update && apt-get install -y \
    sqlite3 \
    libsqlite3-dev \
    unzip \
    && docker-php-ext-install pdo pdo_sqlite

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy app files
COPY . .

# Install PHP dependencies
RUN composer install

# Expose port
EXPOSE 8080

# Run built-in PHP server
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]