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
./vendor/bin/behat -vv -c ./test/behat.yml --suite="default"
./vendor/bin/behat -vv -c ./test/behat.yml --suite="security"



RETRIES=5
until mysql --host=opencatsdb --user=dev --password=dev -e "SHOW DATABASES" || [ $RETRIES -eq 0 ]; do
    echo "Waiting for opencatsdb to be ready..."
    RETRIES=$((RETRIES-1))
    sleep 5
done

if [ $RETRIES -eq 0 ]; then
    echo "ERROR: Unable to connect to opencatsdb."
    exit 1
fi
