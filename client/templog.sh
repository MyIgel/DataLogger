#! /bin/bash

# TempLogger Client für den RaspberryPi um 1Wire Sensoren (z.B. DS18B20 oder DS18S20) auszulesen.

#==============================================
#   CONFIG

API_KEY='R4nd0MsE3dT8beChANgeD'
SERVER='http://log.server.com'



#=============================================

readTemp(){
    local file=$1
    # Temperatur des Sensors auslesen
    local temp=$(grep 't=' "${file}" | awk -F't=' '{print $2}')
    temp=$(echo "scale=2; $temp / 1000" | bc)

    echo "${temp}"
}

getTemp(){
    local success=true
    for file in /sys/bus/w1/devices/10-*/w1_slave; do

        # Temperatur des Sensors auslesen
        local temp=$(readTemp "${file}")
        if [ '-1,25' = "{$temp}" ]; then
            # Workaround, wenn der Außensensor einen falschen Wert zurückgibt (hier -1.25)
            temp=$(readTemp "${file}")
        fi

        local sensor=$(echo "${file}" | awk -F'/' '{print $6}')

        # Wert ausgeben
        echo "Gemessene Temperatur des Sensors ${sensor}: ${temp} °C"

        local request="${SERVER}/api/v1/log/temp/${sensor}/${temp}&apikey=${API_KEY}"
        local status='error'
        if [ "$#" == '0' ]; then
            # API abfragen
            status=$(curl -X GET "${request}" 2> /dev/null | python -c 'import json,sys;obj=json.load(sys.stdin);print obj["status"]' 2> /dev/null)
        elif [ "$1" == 'show' ]; then
            status='ok'
        fi

        if [ "${status}" != 'ok' ]; then
            echo "Error  ${file} (${request})" >&2
            success=false
        fi
    done

    if [ "${success}" != true ]; then
        exit 1
    else
        exit 0
    fi
}

if [ "$#" == '0' ]; then
    getTemp
    exit 0
fi

while getopts ':hs' options; do
    case "${options}" in
        h)
            echo "
TempLogger Client für 1Wire Temperatursensoren (z.B. DS18B20 oder DS18S20).
 Sendet die Daten an ${SERVER}

  Optionen:
    -h  Diese Hilfe
    -s  Temperaturen nur anzeigen
"
            exit
            ;;
        s)
            getTemp show
            ;;
        \?)
            echo "Invalid option: -${OPTARG}" >&2
            exit 1
            ;;
    esac
done
