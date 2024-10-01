#!/bin/sh -x
cd /var/www/public/
dockerize -wait tcp://opencats_test_mariadb:3306 -wait http://opencats_test_web:80 -timeout 30s

# Check if MySQL is accessible
if ! mysql --host=opencatsdb --user=dev --password=dev -e "SHOW DATABASES"; then
    echo "ERROR: Unable to connect to opencatsdb."
    exit 1
fi

php modules/tests/waitForDb.php

# Check config.php for potential issues
cat config.php

./vendor/bin/phpunit src/OpenCATS/Tests/IntegrationTests
./vendor/bin/behat -v -c ./test/behat.yml --suite="default"
./vendor/bin/behat -v -c ./test/behat.yml --suite="security"



