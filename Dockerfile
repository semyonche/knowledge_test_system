FROM php:8.2-apache

# Включаем mod_rewrite
RUN a2enmod rewrite

# Копируем все файлы из проекта в корень веб-сервера
COPY . /var/www/html/

# Даём права на запись (если нужно для сессий или загрузки файлов)
RUN chmod -R 777 /var/www/html/tmp 2>/dev/null || true

EXPOSE 80