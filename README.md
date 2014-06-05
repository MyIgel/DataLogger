DataLogger
==========
Ein einfacher Datenlogger, basierend auf einem Client/Server-Aufbau mit API zugriff

Der Übersichtlichkeit halber wird in den Beispielen `http://log.server.com` als Serveraddresse angegeben, das muss natürlich angepast werden, genauso wie die Zugangsdaten ;)
Nach dem
`git clone https://github.com/MyIgel/DataLogger.git`
muss noch ein
`git submodule update --init`
ausgeführt werden, um die Abhängigkeiten von anderen Projekten aufzulösen.

Client
------
Zum aktivieren von 1Wire auf dem RaspberryPi müssen die Kernelmodule `w1-gpio` und `w1-therm` (einer pro Zeile) in der Datei `/etc/modules` eingetragen werden, welche nach einem Neustart geladen werden. Das kann man auch mit `lsmod` überprüfen.

Die Sensoren werden Parallel an den GPIO4 des Pi angreschlossen, wobei die Datenleitung am ersten Sensor einen 4,7K Pullup (also zu +) braucht.
Strom bekommen die Sensoren vom Pi, wobei man nur den 3.3V Ausgang nehmen darf, da sonst der Pi abraucht...

Zum Testen der Sensoren einfach mal `cat /sys/bus/w1/devices/10-*/w1_slave` (wenn die ID der Sensoren mit 10-... anfängt) in der Console eingeben, dann sollte pro Sensor 2 Zeilen Ausgegeben werden.

Läuft alles wie gewollt, muss man nur noch einen Cronjob mit `crontab -e` einrichten, der das Auslesescript aufruft (wobei sich ein Aufruf alle 5 Min bewährt hat):
`*/5 * * * * /pfad/zur/templog.sh > /dev/null`

Server
------
Auf dem Server muss PHP und MySQL laufen und htaccess aktiv sein, was aber normalerweise bei allen Hostern der Fall ist (und auf dem Pi sowieso ;) )

Zuerst müssen die MySQL Datenbanken angelegt werden:
```mysql
CREATE TABLE IF NOT EXISTS `data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sensorID` int(11) NOT NULL,
  `data` text NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```
```mysql
CREATE TABLE IF NOT EXISTS `sensor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `description` text NOT NULL,
  `sid` varchar(15) NOT NULL COMMENT 'Sensor ID',
  `unit` varchar(11) NOT NULL,
  `type` varchar(10) DEFAULT NULL,
  `options` text,
  `user` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

und die Zugangsdaten in der `include/config.php` eingetragen werden, dann können die Dateien auf den Server hochgeladen werden.


Als Frontend arbeitet [Bootstrap](https://github.com/twbs/bootstrap "Twitter Bootstrap"), mit den jQuery-Erweiterungen [Flot](https://github.com/flot/flot) und [FlotTooltip](https://github.com/krzysu/flot.tooltip) zum anzeigen der Daten.


API
------------
Die API befindet sich unter `http://log.server.com/v1/`

Hinzufügen von Einträgen:
* `http://log.server.com/v1/log/temp/[sensorid]/[data]&apikey=[authkey]`

Anzeigen der Einträge:
* `http://log.server.com/v1/show/temp/[sensorid][/von][/bis][&apikey=[authkey]]`

