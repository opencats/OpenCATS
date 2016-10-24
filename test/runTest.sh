#!/bin/sh -xe
cd /var/www/public/
dockerize -wait tcp://opencats_test_mariadb:3306 -wait http://opencats_test_web:80 -timeout 60s
php modules/tests/waitForDb.php
cat test/behat.yml
pwd
./vendor/bin/behat -v -c test/behat.yml $1

