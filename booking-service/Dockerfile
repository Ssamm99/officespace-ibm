FROM php:8.3-cli
RUN docker-php-ext-install mysqli pdo pdo_mysql
WORKDIR /app
CMD ["php", "-S", "0.0.0.0:8002", "-t", "/app/booking-service"]