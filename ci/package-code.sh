#!/bin/bash
set -ev
bundle exec rake:units
if [ "${TRAVIS_TAG}" != "false" ]; then
	tar -cvzf $TRAVIS_TAG.tar.gz $TRAVIS_BUILD_DIR
fi