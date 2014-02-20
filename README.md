DataLogger
==========
Ein einfacher Datenlogger

Um das Repostitory vollständig zu clonen, muss nach dem `git clone` noch ein `git submodule update --init` ausgeführt werden

Client
------
Zum aktivieren von 1Wire auf dem RaspberryPi müssen noch die Kernelmodule `w1-gpio` und `w1-therm` (einer pro Zeile) in der Datei `/etc/modules` eingetragen werden, nach einem Neustart sollten sie geladen sein, was man mit `lsmod` überprüfen kann.

Die Sensoren werden Parallel an den GPIO4 des Pi angreschlossen, wobei die Datenleitung am ersten Sensor einen 4,7K Pullup braucht.
Strom bekommen sie vom Pi, wobei man da nur den 3.3V Ausgang nehmen darf, da sonst der Pi abraucht...

Zum Testen der Sensoren einfach mal `cat /sys/bus/w1/devices/10-*/w1_slave` in der Console eingeben, dann sollte pro Sensor 2 Zeilen Ausgegeben werden.

Läuft alles wie gewollt, muss man nur noch einen Cronjob mit `crontab -e` einrichten, wobei sich ein Aufruf alle 5 Min bewährt hat:
`*/5 * * * * /pfad/zur/templog.sh > /dev/null`

Server
------
Auf dem Server muss php und mysql laufen und htaccess aktiv sein, was aber normalerweise bei allen Hostern der Fall ist (und auf dem Pi sowieso ;) )

Zuerst muss diem MySQL Datenbank angelegt werden:
```mysql
CREATE TABLE `datalog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` varchar(20) NOT NULL,
  `type` varchar(10) NOT NULL,
  `data` text NOT NULL,
  `sensor` varchar(15) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```
und die Zugangsdaten in der `include/config.php` eingetragen werden, dann können die Dateien auf den Server hochgeladen werden.

Das Frontend benutzt [Bootstrap](https://github.com/twbs/bootstrap "Twitter Bootstrap") als Theme, [Flot](https://github.com/flot/flot) zum anzeigen der Daten und [FlotTooltip](https://github.com/krzysu/flot.tooltip).


API:
------------
Die API befindet sich unter `http://log.server.com/v1/`

Hinzufügen von Einträgen:
* `http://log.server.com/v1/log/temp/[sensorid]/[data]&apikey=[authkey]`

Anzeigen der Einträge:
* `http://log.server.com/v1/show/temp/[sensorid]&apikey=[authkey]`

