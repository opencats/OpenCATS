#!/bin/sh
set -eu

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
rm -f composer-setup.php
