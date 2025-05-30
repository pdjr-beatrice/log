#!/bin/bash

cd loghost
for dir in * ; do
  targetdir="/${dir//\.//}"
  pushd "${dir}" > /dev/null
  for name in * ; do
    sourcename=$( realpath ${name} )
    pushd "${targetdir}" > /dev/null
    ln -s "${sourcename}" .
    chmod 755 "${name}"
    popd > /dev/null
  done
  popd > /dev/null
done
