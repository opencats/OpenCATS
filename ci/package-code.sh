#!/bin/bash
set -ev
if [ "${TRAVIS_TAG}" != "false" ]; then
	tar -cvzf /tmp/$TRAVIS_TAG.tar.gz $TRAVIS_BUILD_DIR
	cp /tmp/$TRAVIS_TAG.tar.gz $TRAVIS_BUILD_DIR/$TRAVIS_TAG.tar.gz
fi