<?php
/**
 * DataLogger
 *
 * Ein einfacher Datenlogger, basierend auf einem Client/Server-Aufbau mit API zugriff
 *
 *
 * LICENSE:
 *
 * Copyright 2014 Igor Scheller
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 *
 * @package		DataLogger
 * @version		0.1.1
 * @author		Igor Scheller <igor.scheller@igorshp.de>
 * @copyright	2014 Igor Scheller
 * @license		http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

/**
 * Wird von der aufgerufenen Seite gesetzt
 *
 * Dadurch kann geprüft werden, ob die Datei inkludiert oder direkt aufgerufen wurde
 *
 * @ignore
 * @var int
 */
define('_API', 1);

/** Kernfunktionen laden */
include_once ('include/config.php');
include_once ('include/functions.php');

/** Logginginstanz starten */
$log = new logger($database, $api_key);
?>
<!DOCTYPE html>
<html lang="de">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Data Logger Frontend">
	<meta http-equiv="refresh" content="120">

	<title>Datalogger</title>

	<!-- Bootstrap core CSS -->
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">

	<!-- Site CSS -->
	<link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

	<!-- Sidebar -->
	<nav class="navbar navbar-inverse navbar-static-top" role="navigation">
		<!-- Brand and toggle get grouped for better mobile display -->
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="#">Data Logger</a>
		</div>

		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse">
			<ul class="nav navbar-nav">
				<li>
					<a>&nbsp;</a>
				</li>
				<li class="active">
					<a href="<?=$_SERVER['PHP_SELF']; ?>"><i class="glyphicon glyphicon-signal"></i> Logger	</a>
				</li>
				<li class="dropdown messages-dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="glyphicon glyphicon-time"></i> Zeit <b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a href="?hour=1">1 Stunde</a></li>
						<li><a href="?hour=5">5 Stunden</a></li>
						<li><a href="?hour=12">12 Stunden</a></li>
						<li><a href="?hour=24">24 Stunden</a></li>
						<li class="divider"></li>
						<li><a href="?day=3">3 Tage</a></li>
						<li><a href="?day=7">1 Woche</a></li>
						<li><a href="?day=14">2 Wochen</a></li>
						<li><a href="?day=30">1 Monat</a></li>
						<li class="divider"></li>
						<li><a href="<?=$_SERVER['PHP_SELF']; ?>?time=all">Alle Daten</a></li>
					</ul>
				</li>
			</ul>
		</div><!-- /.navbar-collapse -->
	</nav>

	<div class="container">

		<div class="row">
			<div class="col-lg-8">
				<h1>Temp <small>@HOME</small></h1>
				<ol class="breadcrumb">
					<li><a href="#"><i class="glyphicon glyphicon-home"></i> Stats</a>
					</li>
					<li class="active"><i class="glyphicon glyphicon-signal"></i> Data</li>
				</ol>
			</div>
			<div class="col-lg-4">
				<ul class="list-group">
					<li class="list-group-item">Gesamt Datensätze: <span class="badge"><?=$log->stats('total'); ?></span>
					</li>
				</ul>
			</div>
		</div><!-- /.row -->

		<div class="row">
			<div class="col-lg-12">
				<h2>Temperatur</h2>
				<div class="panel panel-primary">
					<div class="panel-heading">
						<h3 class="panel-title"><i class="glyphicon glyphicon-signal"></i> </h3>
					</div>
					<div class="panel-body">
						<div class="flot-chart">
							<div class="flot-chart-content" id="flottemp"></div>
						</div>
					</div>
				</div>
			</div>
		</div><!-- /.row -->

	</div><!-- / container -->

<!-- JavaScript -->
<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>

<!-- Page Specific Plugins -->
<script src="assets/js/flot/jquery.flot.js"></script>
<script src="assets/js/flottooltip/js/jquery.flot.tooltip.min.js"></script>
<script src="assets/js/flot/jquery.flot.resize.js"></script>
<script src="assets/js/flot/jquery.flot.time.js"></script>
<script src="assets/js/flot/jquery.flot.selection.js"></script>

<script>
<?php
if ($sensors = $log->getSensor())
{
	$data = array();

	/** Zeitspanne berechnen, welche angezeigt werden soll */
	if (isset($_GET['day'])) {
		$from = time() - (1.01 * 60 * 60 * 24 * $_GET['day']); // 24 Std.
	} else if (isset($_GET['hour'])) {
		$from = time() - (1.1 * 60 * 60 * $_GET['hour']); // x Std.
	} else if(isset($_GET['time']) && $_GET['time'] == 'all') {
		$from = "0";
	} else {
		$from = time() - (1.1 * 60 * 60 * 24 * 3); // 3 Tage
	}

	/** Daten für jeden Sensor auslesen */
	foreach ($sensors as $sensor)
	{
		$data[$sensor['id']] = getData($sensor['id'], $from);
	}
?>


var plot = $.plot($("#flottemp"),
		[
<?php
	/** Daten ausgeben */
	foreach ($sensors as $sensor)
	{
		$options = json_decode($sensor['options'], true);
		echo '{label: "' . htmlentities($sensor['name']) . '", data: [' . jsArray($data[$sensor['id']]) . '], points: { symbol: "circle", fillColor: "#' . htmlentities($options['color']) . '" }, color: "#' . htmlentities($options['color']) . '"},' . "\n\n";
	}
?>
		],
		{
			xaxis: { mode: "time", timezone: "browser", timeformat: "%d.%m.%y, %H:%M:%S", },
			yaxis: { },
			grid: { hoverable: true, clickable: true },
			tooltip: true, tooltipOpts: { content: "%s am %x: %y.2°C", shifts: { x: -60, y: 25 } },
			series: {lines: {show: true, fill: true}}
		}
	);

<?php } ?>

</script>

</body>
</html>
