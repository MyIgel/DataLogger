<?php
/*
* Data Logging API
* 
*/

define('_API', 1);

include_once('include/config.php');
include_once('include/functions.php');

$log = new logger( $database, $api_key );

?><!DOCTYPE html>
<html lang="de">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Data Logger Frontend">
	<meta name="author" content="Igor Scheller">
	<meta http-equiv="refresh" content="120">

	<title>Datalogger</title>

	<!-- Bootstrap core CSS -->
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">


	<link rel="stylesheet" href="css/style.css">

	<!-- Page Specific CSS -->
	<link rel="stylesheet" href="http://cdn.oesmith.co.uk/morris-0.4.3.min.css">
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
				<li><a>&nbsp;</a>
				</li>
				<li class="active"><a href="<?=$_SERVER['PHP_SELF'];?>"><i class="glyphicon glyphicon-signal"></i> Logger</a>
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
						<li><a href="<?=$_SERVER['PHP_SELF'];?>">Alle Daten</a></li>
					</ul>
				</li>
			</ul>
		</div>
		<!-- /.navbar-collapse -->
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
					<li class="list-group-item">Gesamt Datensätze: <span class="badge"><?=$log->stats( 'total' );?></span>
					</li>
				</ul>
			</div>
		</div>
		<!-- /.row -->

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
		</div>
		<!-- /.row -->

	</div>
	<!-- / container -->

<!-- JavaScript -->
<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>

<!-- Page Specific Plugins -->
<script src="js/flot/jquery.flot.js"></script>
<script src="js/flottooltip/js/jquery.flot.tooltip.min.js"></script>
<script src="js/flot/jquery.flot.resize.js"></script>
<script src="js/flot/jquery.flot.time.js"></script>
<script src="js/flot/jquery.flot.selection.js"></script>

<script>

<?php

$aussen = getData('10-000802aa9e03');
$innen  = getData('10-000802ab8ec2');
$minka = min(array_keys($aussen));
$maxka = max(array_keys($aussen));
$minki = min(array_keys($innen));
$maxki = max(array_keys($innen));

$minva = min($aussen);
$maxva = max($aussen);
$minvi = min($innen);
$maxvi = max($innen);

$maxx = ( $maxka > $maxki ) ? $maxka : $maxki;
$minx = (( $minka < $minki ) ? $minka : $minki);
if(isset($_GET['day'])){
	$minx = $maxx-1000*60*60*24*$_GET['day']; // 24 Std.
} else if(isset($_GET['hour'])){
	$minx = $maxx-1000*60*60*$_GET['hour']; // x Std.
}

$miny = ceil(( $minva < $minvi ) ? $minva : $minvi)-3;
$maxy = floor(( $maxva > $maxvi ) ? $maxva : $maxvi)+3;
?>

var aussen = [<?=arrToVar($aussen)?>];
var innen  = [<?=arrToVar($innen)?>];


var plot = $.plot($("#flottemp"), 
  [
    {label: "Innentemperaur", data: innen, points: { symbol: "circle", fillColor: "#AA4643" }, color: '#AA4643'},
    {label: "Au&szlig;entemperatur", data: aussen, points: { symbol: "circle", fillColor: "#058DC7" }, color: '#058DC7'},
  ],
  {
    xaxis: { min: <?=$minx?>, max: <?=$maxx?>, mode: "time", timeformat: "%d.%m.%y, %H:%M:%S", },
    yaxis: {min: <?=$miny?>, max: <?=$maxy?> },
    grid: { hoverable: true, clickable: true },
    tooltip: true, tooltipOpts: { content: "%s am %x: %y.2°C", shifts: { x: -60, y: 25 } },
    series: {lines: {show: true, fill: true}}
  }
);


</script>

</body>
</html>