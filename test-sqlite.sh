docker run --rm php:8.3-cli bash -c "apt-get update && apt-get install -y libsqlite3-dev && docker-php-ext-install pdo_sqlite && php -m | grep sqlite"
