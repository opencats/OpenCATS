#!/bin/bash
set -ev
if [ "${TRAVIS_TAG}" != "false" ]; then
	tar -cvzf /tmp/$TRAVIS_TAG-full.tar.gz -C $TRAVIS_BUILD_DIR .
	cp /tmp/$TRAVIS_TAG-full.tar.gz $TRAVIS_BUILD_DIR/$TRAVIS_TAG-full.tar.gz
fi