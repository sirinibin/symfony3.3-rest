Symfony3.3-rest
============

This is a RESTful API developed in Symfony3.3 which talks to Elastic search via RabbitMQ


INSTALLATION
-------------------
```
Step1: cd /var/www &&
git clone https://github.com/sirinibin/symfony3.3-rest.git symfony3-rest

Step2: Create a database named "symfony_rest"

Note: Make sure you have php-bcmath and php-curl extensions installed already if not just run

 supo apt-get install php7.0-bcmath
 supo apt-get install php-curl

Step3:cd symfony3-rest & composer install
Note:Db details will be asked during the installation process.


Step4:Generate the db schema
php bin/console doctrine:schema:create

Step5: Point your API end point to /var/www/symfony3-rest/web

Step6: cd /var/www/symfony3-rest/web

Step7: Run workers

         php worker.php

  Note: You can create any no.of workers as you need

```
