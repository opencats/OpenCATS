#!/bin/bash
set -ev

if [ "${TRAVIS_TAG}" != "false" ]; then
	tar -czf /tmp/opencats-$TRAVIS_TAG-full.tar.gz --exclude=INSTALL_BLOCK --exclude=docker/data --exclude=docker/persist --exclude=attachments --exclude=code/tests/_output --exclude=code/var/cache --exclude=code/var/logs --exclude=code/var/sessions --exclude=code/src/AppBundle/tests/_output --exclude=code/src/AppBundle/tests/_support/_generated -C $TRAVIS_BUILD_DIR .
	zip -v -q -x INSTALL_BLOCK -x docker/data -x=docker/persist -x attachments -x code/tests/_output -x code/var/cache -x code/var/logs -x code/var/sessions -x code/src/AppBundle/tests/_output -x code/src/AppBundle/tests/_support/_generated -r /tmp/opencats-$TRAVIS_TAG-full.zip $TRAVIS_BUILD_DIR
	cp /tmp/opencats-$TRAVIS_TAG-full.tar.gz $TRAVIS_BUILD_DIR
	cp /tmp/opencats-$TRAVIS_TAG-full.zip $TRAVIS_BUILD_DIR
fi
