from php:8.4-apache

# Install Git and sqlite3
run apt update
run apt install git -y

# Server engine
run a2enmod rewrite

# Clear DB
run echo "" > ./php-secret-service/models/my_base.sqlite

# Copy repo to httpd server directory
run cp -r ./php-secret-service/* /var/www/html

# Change permissions for DB
run chmod 777 /var/www/html/models/
run chmod 666 /var/www/html/models/my_base.sqlite

# Copy Server Config
run cat ./php-secret-service/apache2.conf > /etc/apache2/apache2.conf

# Expose port
expose 3000
