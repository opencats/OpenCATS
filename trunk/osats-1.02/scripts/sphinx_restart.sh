#!/bin/bash
#
# CATS Sphinx Full Index Updater
#
# $Id: sphinx_restart.sh 2987 2007-08-31 20:42:15Z will $

if [ -f ~/ENVIRONMENT.conf ];
then
    CATS_PATH=`cat ~/ENVIRONMENT.conf | grep "CATS_PATH=\"" | sed -e "s/CATS_PATH=\"//" | sed -e "s/\"//"`
    PHP_PATH=`cat ~/ENVIRONMENT.conf | grep "PHP_PATH=\"" | sed -e "s/PHP_PATH=\"//" | sed -e "s/\"//"`
    SPHINX_BIN=`cat ~/ENVIRONMENT.conf | grep "SPHINX_BIN=\"" | sed -e "s/SPHINX_BIN=\"//" | sed -e "s/\"//"`
    SPHINX_CONFIG=`cat ~/ENVIRONMENT.conf | grep "SPHINX_CONFIG=\"" | sed -e "s/SPHINX_CONFIG=\"//" | sed -e "s/\"//"`
else
    CATS_PATH="/usr/local/www/catsone.com/data"
    PHP_PATH="/usr/local/bin/php"
    SPHINX_BIN="/usr/local/bin"
    SPHINX_CONFIG="/usr/local/www/catsone.net/data/lib/sphinx/sphinx-www.conf"
fi

if [ ! -f "${SPHINX_BIN}/searchd" ];
then
    echo "${SPHINX_BIN}/searchd does not exist." >2
    exit 1
fi

if [ ! -f $SPHINX_CONFIG ];
then
    echo "$SPHINX_CONFIG does not exist." >2
    exit 1
fi

if ps auxww | grep 'sear[c]hd' >/dev/null; then
    # Sphinx running.
    exit 0
else
    ${SPHINX_BIN}/searchd --config ${SPHINX_CONFIG}
    echo "Sphinx not running; restarted." >2
fi

