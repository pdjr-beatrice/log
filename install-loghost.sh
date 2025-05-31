#!/bin/bash

LOG_HOST_FOLDER=log-host

if [ -d "${LOG_HOST_FOLDER}" ] ; then
  cd "${LOG_HOST_FOLDER}"
  for dir in * ; do
    targetdir="/${dir//\.//}"
    pushd "${dir}" > /dev/null
    for name in * ; do
      sourcename=$( realpath ${name} )
      pushd "${targetdir}" > /dev/null
      rm $name
      ln -s "${sourcename}" .
      chmod 755 "${name}"
      popd > /dev/null
    done
    popd > /dev/null
  done
else
  echo "Execute this script in repository root."
  exit 1
fi
