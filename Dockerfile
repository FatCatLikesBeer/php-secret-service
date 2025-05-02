FROM php:8.4-apache

WORKDIR /

# Server engine
RUN a2enmod rewrite

# Clear DB
RUN echo "" > ./php-secret-service/models/my_base.sqlite

# Copy repo to httpd server directory
RUN cp -r ./php-secret-service/* /var/www/html

# Change permissions for DB
RUN chmod 777 /var/www/html/models/
RUN chmod 666 /var/www/html/models/my_base.sqlite

# Copy Server Config
RUN cat ./php-secret-service/apache2.conf > /etc/apache2/apache2.conf

# Expose port
EXPose 3000
