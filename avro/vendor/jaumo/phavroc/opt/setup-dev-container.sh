#!/usr/bin/env bash
set -x

# We need wget for composer installation
apt-get update && apt-get install -y wget libzip-dev unzip

# Enable extensions
docker-php-ext-install zip

# Disable memory-limit
echo 'memory_limit = -1' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini

# Install composer
wget https://raw.githubusercontent.com/composer/getcomposer.org/76a7060ccb93902cd7576b67264ad91c8a2700e2/web/installer -O - -q | php -- --install-dir=/usr/local/bin --filename=composer

# Install behat
composer require --dev behat/behat
