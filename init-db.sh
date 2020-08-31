#!/bin/bash
sudo service mysql start || systemctl status mysql.service
cd /var/www/battleship.blaeul.de/api
echo 'CREATE DATABASE `eos.uptrade-coding-challenge`; CREATE USER "doctrine"@"localhost" IDENTIFIED BY "hutapo"; GRANT ALL PRIVILEGES ON *.* TO "doctrine"@"localhost";' | mysql
php bin/console doctrine:schema:update --force
