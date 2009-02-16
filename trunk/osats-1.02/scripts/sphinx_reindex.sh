#!/bin/sh
#
# CATS Sphinx Full Index Updater
#
# $Id: sphinx_reindex.sh 2987 2007-08-31 20:42:15Z will $

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

if [ ! -f "${SPHINX_BIN}/indexer" ];
then
    echo "${SPHINX_BIN}/indexer does not exist."
    exit 1
fi

if [ ! -f $SPHINX_CONFIG ];
then
    echo "$SPHINX_CONFIG does not exist."
    exit 1
fi

${SPHINX_BIN}/indexer --all --config ${SPHINX_CONFIG}

