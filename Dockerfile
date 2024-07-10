# Use the official PHP image with Apache
FROM php:8.2-apache

# Set the working directory inside the container
WORKDIR /var/www/html

# Install necessary PHP extensions (adjust based on your needs)
RUN docker-php-ext-install pdo pdo_mysql

# Copy the composer.json and composer.lock files to the working directory
COPY composer.json composer.lock ./

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PHP dependencies
#RUN composer install

# PHP Extension
RUN docker-php-ext-install gettext intl pdo_mysql gd

RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Expose the port the web server will run on
EXPOSE 88

# Configure Apache to listen on port 88
RUN sed -i 's/Listen 80/Listen 88/' /etc/apache2/ports.conf && \
    sed -i 's/:80/:88/' /etc/apache2/sites-available/000-default.conf

# Start Apache in the foreground (this is the default behavior of php:apache image)
CMD ["apache2-foreground"]
