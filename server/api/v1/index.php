<?php
/**
 * Data Logging API
 *
 * @todo		Header Auth, header err response, requests (GET,POST,DELETE...) ...
 * @package		DataLogger\Core\LoggerBrain
 * @author		Igor Scheller <igor.scheller@igorshp.de>
 * @license		http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

/**
 * Wird von der aufgerufenen Seite gesetzt
 *
 * Dadurch kann geprüft werden, ob die Datei inkludiert oder direkt aufgerufen wurde
 *
 * @var int
 */
define('_API', 1);

/** Kernfunktionen laden */
include_once ('../../include/config.php');
include_once ('../../include/functions.php');

$return = array('status' => 'err');
$url = explode("/", @trim($_GET['url'], "/"));
$action = @$url[0];
$type = @$url[1];
$sensorNo = @$url[2];

$apiKey = '';
if(!empty($_POST['apikey'])){
	$apiKey = $_POST['apikey'];
} else if(!empty($_GET['apikey'])){
	$apiKey = $_GET['apikey'];
} else if($action == 'show'){
	$apiKey = $api_key;
}


if (!empty($apiKey) && !empty($action))
{
	$log = new logger($database, $apiKey);

	/** Daten hinzufügen */
	if ($action == 'log') // http://log.server.com/api/v1/log/temp/[SensorNo]/[SensorData]&apikey=R4nd0MsE3dT8beChANgeD
	{
		$data = @$url[3];
		
		if (!empty($type) && !empty($sensorNo) && !empty($data))
		{
			$sensor = current($log->getSensor($sensorNo, $type));

			if ($log->data($sensor['id'], $data))
			{
				$return['status'] = 'ok';
			}
		}
	}
	/** Daten ausgeben */
	else if ($action == 'show') // http://log.server.com/api/v1/show/temp/[SensorNo][/from][/to]
	{
		$from = @$url[3];
		$to = @$url[4];
		
		if (!empty($type) && !empty($sensorNo))
		{
			$data = false;

			if ($sensor = current($log->getSensor($sensorNo, $type)))
			{
				if (!empty($from))
				{
					if (!empty($to))
					{
						$data = $log->get($sensor['id'], $from, $to);
					} else {
						$data = $log->get($sensor['id'], $from);
					}
				} else {
					$data = $log->get($sensor['id']);
				}
			}

			if ($data)
			{
				$return['status'] = 'ok';
				$return['data'] = $data;
			}
		}
	}
}

if ($return['status'] != 'ok')
{
	header('HTTP/1.0 404 Not Found');
}

echo json_encode($return);
