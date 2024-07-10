# Sử dụng hình ảnh PHP 8.2 với Apache
FROM php:8.2-apache
# Thiết lập thư mục làm việc và vhost của Apache
WORKDIR /var/www/html
# Cài đặt các extensions cần thiết và ICU libraries
RUN apt-get update \
    && apt-get install -y \
        libicu-dev \
        zlib1g-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install gettext intl pdo_mysql gd

COPY ./Backend /var/www/html

# Thiết lập môi trường cho PHP
ENV TZ=Asia/Ho_Chi_Minh
ENV LANG=C.UTF-8

# Mở cổng cho Apache
EXPOSE 80

# Khởi động Apache
CMD ["apache2-foreground"]
