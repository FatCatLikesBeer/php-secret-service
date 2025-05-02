FROM php:8.4-apache

# Copy files
WORKDIR /var/www/html/
COPY . .

# Apt stuff
RUN apt update
RUN apt install sqlite3 vim -y

# Server engine
RUN a2enmod rewrite

# Clear DB
RUN echo "" > /var/www/html/models/my_base.sqlite

# Change permissions for DB
RUN chmod 777 /var/www/html/models/
RUN chmod 666 /var/www/html/models/my_base.sqlite

# Copy Server Config
RUN cat ./apache2.conf > /etc/apache2/apache2.conf

# Expose port
EXPOSE 80
