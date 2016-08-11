#!/bin/sh
echo =====================================
echo    Data for security test

mysql --host=opencatsdb --user=dev --password=dev cats_test < data/test.sql
mysql --host=opencatsdb --user=dev --password=dev cats_test < data/securityTests.sql

echo    Imported
echo =====================================
