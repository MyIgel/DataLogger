#! /bin/bash

# TempLogger Client für den RaspberryPi um 1Wire Sensoren (z.B. DS18B20 oder DS18S20) auszulesen.

#==============================================
#   CONFIG

apikey="R4nd0MsE3dT8beChANgeD"
server="log.server.com"



#=============================================


for file in /sys/bus/w1/devices/10-*/w1_slave ; do

# Temperatur des Sensors auslesen
temp=`grep 't=' $file | awk -F't=' '{print $2}'`
temp2=`echo "scale=2; $temp / 1000" | bc`
if [ $temp2 == -1.25 ]; then  # Workaround, da mein Außensensor manchmal diesen Wert zurückgibt, dann wird nochmal gemessen und er stimmt
	temp=`grep 't=' $file | awk -F't=' '{print $2}'`
	temp2=`echo "scale=2; $temp / 1000" | bc`
fi


# Wert ausgeben
echo "Gemessene Temperatur des Sensors $file: $temp2°C"

sensor=`echo $file |tail -c25|head -c15`

# API abfragen
status=`curl -X GET "http://$server/v1/log/temp/$sensor/$temp2&apikey=$apikey" 2> /dev/null | python -c 'import json,sys;obj=json.load(sys.stdin);print obj["status"]'`


if [ "$status" != 'ok' ]; then
	echo "Err" $file
	exit 1
fi

done
