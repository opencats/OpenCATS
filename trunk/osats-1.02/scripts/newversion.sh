#! /bin/bash
#
# Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
#
# $Id: newversion.sh 1510 2007-01-18 20:17:05Z will $

show_usage() {
	echo "Usage: `basename $0` [-hs] [version]"
	echo ""
	echo "  version - New version (example: `basename $0` '1.0 BETA')."
	echo ""
	echo "  -h, --help - Print this help and exit."
	echo "  -s, --show - Print current version and exit."
	exit 1
}

if [ ! -f "modules/login/LoginUI.php" ]; then
	echo "Error: You are not in the OSATS directory."
	exit 1
fi

if [ "x${1}" = "x" ] || [ "x${1}" = "x-h" ] || [ "x${1}" = "x--help" ]; then
	show_usage
else
	VERSION_STRING=$1
fi

umask 022

echo -n 'Finding existing version number... '
OLD_OSATSVERSION=$(grep 'OSATS Version' index.php | sed -e 's!^.*OSATS Version: !!g')

if test "x${OLD_OSATSVERSION}" = "x"; then
	echo "Error: Could not detect current version number."
	exit 1
fi

echo $OLD_OSATSVERSION

NEW_OSATSVERSION=$(echo $1)

echo ${OLD_OSATSVERSION} \-\> ${NEW_OSATSVERSION}... 
echo ""

# Update constants.php
echo constants.php
sed -r "s/define\('OSATS_VERSION', .+\);/define('OSATS_VERSION', '${NEW_OSATSVERSION}');/g" constants.php > constants.php_
mv constants.php_ constants.php

# Update all files that contain OSATS Version: in the comments.
for i in $(grep -r 'OSATS Version:' * | grep -vE '\.svn|newversion' | cut -d':' -f1); do
    echo $i
    sed -r "s/OSATS Version: .+/OSATS Version: ${NEW_OSATSVERSION}/g" $i > ${i}_
    mv ${i}_ $i
done

echo ""
echo "Complete."