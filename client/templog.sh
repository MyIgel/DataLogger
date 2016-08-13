#! /bin/bash

# TempLogger Client für den RaspberryPi um 1Wire Sensoren (z.B. DS18B20 oder DS18S20) auszulesen.

#==============================================
#   CONFIG

API_KEY='R4nd0MsE3dT8beChANgeD'
SERVER='http://log.server.com'



#=============================================

getTemp(){
    for file in /sys/bus/w1/devices/10-*/w1_slave; do

        # Temperatur des Sensors auslesen
        temp=$(grep 't=' "${file}" | awk -F't=' '{print $2}')
        temp=$(echo "scale=2; $temp / 1000" | bc)
        if [ '-1,25' = "{$temp}" ]; then
            # Workaround, wenn der Außensensor einen falschen Wert zurückgibt (hier -1.25)
            temp=$(grep 't=' "${file}" | awk -F't=' '{print $2}')
            temp=$(echo "scale=2; $temp / 1000" | bc)
        fi

        sensor=$(echo "${file}" |tail -c25|head -c15)

        # Wert ausgeben
        echo "Gemessene Temperatur des Sensors ${sensor}: ${temp} °C"

        if [ "$#" == '0' ]; then
            # API abfragen
            request="${SERVER}/api/v1/log/temp/${sensor}/${temp}&apikey=${API_KEY}"
            status=$(curl -X GET "${request}" 2> /dev/null | python -c 'import json,sys;obj=json.load(sys.stdin);print obj["status"]' 2> /dev/null)
        elif [ "$1" == 'show' ]; then
            status='ok'
        fi

        if [ "${status}" != 'ok' ]; then
            echo "Err: ${file} | ${request}" >&2
            exit 1
        fi

    done
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
