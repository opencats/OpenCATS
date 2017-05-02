#! /bin/bash
#
# Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
#
# $Id: countcode.sh 3052 2007-09-19 20:48:36Z andrew $
CATS_ROOT_PATH_CHECK="modules/login/LoginUI.php"
SYM_LINKS_EXIST=1
REMOVE_SYM_LINKS_SCRIPT="./scripts/removeWebsiteSymLinks.sh"
CREATE_SYM_LINKS_SCRIPT="./scripts/makeWebsiteSymLinks.sh"

# Change path if user isn't in CATS root
while [ ! -f "${CATS_ROOT_PATH_CHECK}" ] && [ `pwd` != "/" ]; do
    echo -n "CATS not found in `pwd`... trying "
    cd ..
    echo -n "`pwd`"
    if [ -d "${CATS_ROOT_PATH_CHECK}" ];
    then
        echo " [OK]"
    else
        echo ", Nope..."
    fi
done
if [ ! -f "${CATS_ROOT_PATH_CHECK}" ];
then
    echo "Cannot find CATS. Please run this script "
    echo "from a path within a CATS installation."
    exit 1
fi

# Remove all website symlinks (we'll re-create them)
if [ $SYM_LINKS_EXIST -eq 1 ];
then
    if [ -f "${REMOVE_SYM_LINKS_SCRIPT}" ];
    then
        ${REMOVE_SYM_LINKS_SCRIPT} >/dev/null
    fi
fi

addcommas()
{
	sed -e ':a' -e 's/\(.*[0-9]\)\([0-9]\{3\}\)/\1,\2/;ta'
}

PHPFILES=$(find . -name '*.php' -print | grep -vE 'website|artichow|fpdf|simpletest|phpmailer|site_backup.php|zip/')
CSSFILES=$(find . -name '*.css' -print | grep -vE 'website|fpdf')
JSFILES=$(find . -name '*.js' -print | grep -vE 'website|calendarDate|sweetTitles.js')


if [ -f ./.countcode.tmp ]; then
	rm -f ./.countcode.tmp >/dev/null 2>&1
fi

cat $PHPFILES | scripts/countfilecode.awk | sed 's!^!PHP !' | tee -a ./.countcode.tmp | addcommas
echo ""

cat $CSSFILES | scripts/countfilecode.awk | sed 's!^!CSS !' | tee -a ./.countcode.tmp | addcommas
echo ""

cat $JSFILES | scripts/countfilecode.awk | sed 's!^!JS  !' | tee -a ./.countcode.tmp | addcommas
echo ""

SUMS=$(cat ./.countcode.tmp | grep 'Total lines' | cut -d':' -f2 | sed 's/[^0-9]+//g')
TOTAL=0

for i in $SUMS; do
	TOTAL=$(($TOTAL + $i))
done

TOTAL=$(echo $TOTAL | addcommas)

cat ./.countcode.tmp | grep "Total lines" | addcommas
echo -e "    Total lines  :\t${TOTAL}"

if [ -f ./.countcode.tmp ]; then
	rm -f ./.countcode.tmp >/dev/null 2>&1
fi

# Re-install the website sym links
if [ $SYM_LINKS_EXIST -eq 1 ];
then
    if [ -f "${CREATE_SYM_LINKS_SCRIPT}" ];
    then
        ${CREATE_SYM_LINKS_SCRIPT} -f >/dev/null
    fi
fi
