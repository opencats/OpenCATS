#!/bin/sh
#
# OSATS Sphinx Delta Index Updater
#
# $Id: sphinx_update_delta.sh 2987 2007-08-31 20:42:15Z will $

if [ -f ~/ENVIRONMENT.conf ];
then
    OSATS_PATH=`cat ~/ENVIRONMENT.conf | grep "OSATS_PATH=\"" | sed -e "s/OSATS_PATH=\"//" | sed -e "s/\"//"`
    PHP_PATH=`cat ~/ENVIRONMENT.conf | grep "PHP_PATH=\"" | sed -e "s/PHP_PATH=\"//" | sed -e "s/\"//"`
    SPHINX_BIN=`cat ~/ENVIRONMENT.conf | grep "SPHINX_BIN=\"" | sed -e "s/SPHINX_BIN=\"//" | sed -e "s/\"//"`
    SPHINX_CONFIG=`cat ~/ENVIRONMENT.conf | grep "SPHINX_CONFIG=\"" | sed -e "s/SPHINX_CONFIG=\"//" | sed -e "s/\"//"`
else
    OSATS_PATH="/usr/local/www/OSATSone.com/data"
    PHP_PATH="/usr/local/bin/php"
    SPHINX_BIN="/usr/local/bin"
    SPHINX_CONFIG="/usr/local/www/OSATSone.net/data/lib/sphinx/sphinx-www.conf"
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

${SPHINX_BIN}/indexer --rotate --config ${SPHINX_CONFIG} OSATSdelta
