FROM php:8.3-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    nodejs \
    npm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd pdo_pgsql

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Install dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

# Build frontend assets if package.json exists
RUN if [ -f package.json ]; then npm install && npm run build; fi

# Set up environment and SQLite database
RUN cp .env.example .env \
    && sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env \
    && touch database/database.sqlite \
    && php artisan key:generate

# Start Laravel development server
# Render uses the PORT environment variable
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
