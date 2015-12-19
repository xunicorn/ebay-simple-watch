# ebay-simple-watching
This project is about eBay searching & grouping results in watch lists. 
You could simple watching for yours favourite items from lists: bids count, current price, lefted time. 

#Installing
Project was written and tested on: Apache 2.2.16, MySQL 5.1.49, PHP 5.3.3

This project is written in Yii 1. Therefore you have to change DB connection in /protected/config/main.php - components - db.

Also you have to create DB. DB file - /protected/data/ebay.sql

chmod -R 777 for dirs: /assets, /install, /protected/runtime

chown -R www-data:www-data /install

#Using
Installing script will run after DB creating (install.php). It has form for admin user creation.

Admin ID ALWAYS == 1

Your service is ready for use.