#!/bin/sh
set -e
echo =====================================
echo    Data for security test

# Check if database exists first
if mysql --host=opencatsdb --user=dev --password=dev -e "USE cats_test"; then
    echo "Database cats_test exists."
else
    echo "Database cats_test does not exist. Creating it now."
    mysql --host=opencatsdb --user=dev --password=dev -e "CREATE DATABASE cats_test"
fi

mysql --host=opencatsdb --user=dev --password=dev cats_test < data/test.sql
mysql --host=opencatsdb --user=dev --password=dev cats_test < data/securityTests.sql

echo    Imported
echo =====================================
