#! /bin/bash
#
# Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
#

find_whitespace_files() {
	grep -rE '[[:space:]]+$' . | sed 's/:.*//' | \
        grep -E '(\.php|\.tpl|\.js|INSTALL|DEVELOPMENT-GUIDELINES|CHANGELOG|AUTHORS)$' | sort | uniq
}

if [ ! -f "modules/login/LoginUI.php" ]; then
	echo "Error: You are not in the CATS directory."
	exit 1
fi

if uname -a | grep 'Darwin' >/dev/null; then
    echo 'Error: This script is broken under Mac OS X; aborting.'
    exit 1
fi

COUNT=0
for i in $(find_whitespace_files); do
	COUNT=$(($COUNT + 1))
	echo -n "${i}... "
	sed -e 's:[ \t]*$::g' $i > ${i}_
	mv ${i}_ $i
	echo "done."
done

echo "Found/fixed ${COUNT} files."
