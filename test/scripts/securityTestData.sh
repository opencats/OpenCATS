#!/bin/sh
echo =====================================
echo    Data for security test

mysql --host=opencatsdb --user=dev --password=dev cats_test < /db/cats_schema.sql
mysql --host=opencatsdb --user=dev --password=dev cats_test < /test/data/securityTests.sql

echo    Imported
echo =====================================
