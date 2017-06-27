#!/bin/bash
set -ev
echo $TRAVIS_TAG
if [ "${TRAVIS_TAG}" != "" ]; then
	tar -czf /tmp/opencats-$TRAVIS_TAG-full.tar.gz --exclude=INSTALL_BLOCK -C $TRAVIS_BUILD_DIR .
	zip -q -x INSTALL_BLOCK -r /tmp/opencats-$TRAVIS_TAG-full.zip $TRAVIS_BUILD_DIR
	cp /tmp/opencats-$TRAVIS_TAG-full.tar.gz $TRAVIS_BUILD_DIR
	cp /tmp/opencats-$TRAVIS_TAG-full.zip $TRAVIS_BUILD_DIR
fi
