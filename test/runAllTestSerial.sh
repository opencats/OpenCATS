#!/bin/bash -x
cd ../docker
for TEST in $(docker-compose -f docker-compose-test.yml run php /var/www/public/test/getTestList.sh | tr -d '\r')
do
    echo "Testing $TEST"
    docker-compose -f docker-compose-isolated-test.yml up -d
    docker-compose -f docker-compose-isolated-test.yml exec php /var/www/public/test/runTest.sh $TEST
    docker-compose -f docker-compose-isolated-test.yml down
done
