FROM php:8.2-apache

RUN a2enmod rewrite

# Рабочая директория, где лежат файлы
WORKDIR /var/www/html

# Копируем все файлы
COPY . /var/www/html/

# Создаём символическую ссылку, если папка includes есть в проекте
RUN ls -la /var/www/html/

EXPOSE 80
