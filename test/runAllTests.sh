#!/bin/sh -x
cd /var/www/
dockerize -wait tcp://opencats_test_mariadb:3306 -wait http://opencats_test_web:80 -timeout 60s
php modules/tests/waitForDb.php
cat config.php
cd /var/www/code
whoami
ls -la /var/www/
ls -la
ls -la /var/www/code/var
ls -la /var/www/code/var/cache
ls -la /var/www/code/var/cache/dev
./vendor/bin/codecept run acceptance activities.feature -vvv
