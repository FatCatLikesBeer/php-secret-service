FROM php:8.4-apache

WORKDIR /var/www/html/
COPY . .

# Server engine
RUN a2enmod rewrite

RUN service apache2 restart

# Clear DB
RUN echo "" > /var/www/html/models/my_base.sqlite

# Copy repo to httpd server directory
# RUN cp -r ./php-secret-service/* /var/www/html

# Change permissions for DB
RUN chmod 777 /var/www/html/models/
RUN chmod 666 /var/www/html/models/my_base.sqlite

# Copy Server Config
RUN cat /var/www/html/apache2.conf > /etc/apache2/apache2.conf

# Expose port
EXPOSE 3000
