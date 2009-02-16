#! /bin/bash
#
# Copyright (C) 2006 - 2007 Cognizo Technologies, Inc.
#
# $Id: svnkeywords.sh 3552 2007-11-11 21:56:02Z will $

if [ ! -f "modules/login/LoginUI.php" ]; then
	echo "Error: You are not in the CATS directory."
	exit 1
fi

find .  -name '*.tpl'      \
	-or -name '*.php'  \
        -or -name '*.js'   \
        -or -name '*.sh'   \
        -or -name '*.pl' | \
        grep -v 'attachments/' | \
xargs svn propset svn:keywords 'Author Date Id Revision'

if [ -f "doc/DEVELOPMENT-GUIDELINES" ]; then
	svn propdel svn:keywords doc/DEVELOPMENT-GUIDELINES
fi

echo "Complete."
