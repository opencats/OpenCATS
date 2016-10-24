#!/bin/sh
cd /var/www/public/
grep -H -n Scenario test/features/* | cut -d : -f 1,2
