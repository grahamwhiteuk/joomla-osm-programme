#!/bin/bash

ZIPFILE="joomla-osm-programme.zip"

cd mod_osm || exit 1
rm -f ../${ZIPFILE}
zip -r ../${ZIPFILE} *
cd ..
sha256sum ${ZIPFILE}
sha384sum ${ZIPFILE}
sha512sum ${ZIPFILE}
