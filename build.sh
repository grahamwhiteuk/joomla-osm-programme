#!/bin/bash

ZIPFILE="joomla-osm-module.zip"

cd mod_osm || exit 1
rm -rf ../${ZIPFILE}
zip -r ../${ZIPFILE} *
cd ..
sha256sum ${ZIPFILE}
sha384sum ${ZIPFILE}
sha512sum ${ZIPFILE}
