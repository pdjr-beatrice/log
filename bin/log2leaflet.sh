#!/bin/bash

LOGDIR=/var/log/beatrice/

DATE=/bin/date
GREP=/bin/grep
HEAD=/usr/bin/head
JQ=/usr/bin/jq
TAIL=/usr/bin/tail
WC=/usr/bin/wc

# Get start and end labels
#
if [ "${1}" == "-s" ] ; then shift ; START=${1}; shift ; fi
if [ "${1}" == "-e" ] ; then shift ; END=${1}; shift ; fi
START=${START:-Start}
END=${END:-End}

LOGFILE=${LOGDIR}${1:-$(${DATE} +%Y%m%d)}


if [ -f "${LOGFILE}" ] ; then
    POSITIONS=$(${GREP} '^POSITION:' ${LOGFILE}) 
    TANKLEVELS=$(${GREP} '^TANKLEVEL:' ${LOGFILE})
    POSITIONCOUNT=$(echo "${POSITIONS}" | ${GREP} -c '^')

    # Output a map
    #
    if [ ${POSITIONCOUNT} -gt 1 ] ; then
        echo '[leaflet-map fitbounds]'
    else 
        read -r token label value <<<"$(echo "${POSITIONS}" | ${HEAD} -1)"
        lat=$(echo ${value} | ${JQ} -j '.latitude')
        lon=$(echo ${value} | ${JQ} -j '.longitude')
        echo "[leaflet-map zoom='15' lat='${lat}' lng='${lon}']"
    fi
    
    # Output a line representing the logged route
    #
    if [ ${POSITIONCOUNT} -gt 1 ] ; then
        echo -n '[leaflet-line latlngs="'
        while read -r token label value; do
            latlon=$(echo ${value} | ${JQ} -j '.latitude,",",.longitude')
            echo -n "${latlon};"
        done <<< "${POSITIONS}"
        echo '"]'
    fi 

    # Output a marker at the start of the route
    #
    if [ ${POSITIONCOUNT} -gt 0 ] ; then
        read -r token label value <<<"$(echo "${POSITIONS}" | ${HEAD} -1)"
        lat=$(echo ${value} | ${JQ} -j '.latitude')
        lon=$(echo ${value} | ${JQ} -j '.longitude')
        echo "[leaflet-marker lat='${lat}' lng='${lon}' message='${START}']"
    fi
fi
