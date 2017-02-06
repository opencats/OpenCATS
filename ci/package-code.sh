#!/bin/bash
set -ev

if [ "${TRAVIS_TAG}" != "false" ]; then
	tar -czf /tmp/opencats-$TRAVIS_TAG-full.tar.gz --exclude=INSTALL_BLOCK --exclude=./code/var/cache --exclude=./code/var/logs --exclude=./code/var/sessions -C $TRAVIS_BUILD_DIR .
	zip -q -x INSTALL_BLOCK -x ./code/var/cache -x ./code/var/logs -x ./code/var/sessions -r /tmp/opencats-$TRAVIS_TAG-full.zip $TRAVIS_BUILD_DIR
	cp /tmp/opencats-$TRAVIS_TAG-full.tar.gz $TRAVIS_BUILD_DIR
	cp /tmp/opencats-$TRAVIS_TAG-full.zip $TRAVIS_BUILD_DIR
fi
