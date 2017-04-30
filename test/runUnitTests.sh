#!/bin/sh -x
cd /var/www/
dockerize -wait tcp://docker_unittestdb_1:3306 -timeout 60s
cd /var/www/code
./vendor/bin/codecept run -c src/AppBundle/ unit
