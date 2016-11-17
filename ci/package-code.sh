#!/bin/bash
set -ev

if [ "${TRAVIS_TAG}" != "false" ]; then
	tar -cvzf /tmp/opencats-$TRAVIS_TAG-full.tar.gz -C $TRAVIS_BUILD_DIR .
	zip -r /tmp/opencats-$TRAVIS_TAG-full.zip $TRAVIS_BUILD_DIR
	cp /tmp/opencats-$TRAVIS_TAG-full.tar.gz $TRAVIS_BUILD_DIR
	cp /tmp/opencats-$TRAVIS_TAG-full.zip $TRAVIS_BUILD_DIR
fi