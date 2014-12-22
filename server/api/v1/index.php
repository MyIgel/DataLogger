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
header('Content-type: application/json');

/** Kernfunktionen laden */
include_once ('../../include/config.php');
include_once ('../../include/functions.php');
include_once ('../../include/request.class.php');


$return = array('status' => 'err');

$apiKey = '';
if(Request::post('apikey')){
	$apiKey = Request::post('apikey');
} else if(Request::get('apikey')){
	$apiKey = Request::get('apikey');
} else {
	Request::match('(list|show)(.*)', function($m){
		global $apiKey, $api_key;
		$apiKey = $api_key;
	});
}

if (!empty($apiKey)){
	$log = new logger($database, $apiKey);
	
	
	/** 
	 * Daten hinzufügen
	 * @example http://log.server.com/api/v1/log/temp/SensorNo/SensorData&apikey=R4nd0MsE3dT8beChANgeD
	 */
	Request::match('log/([a-zA-Z]+)/(.+)/([0-9]+\.?[0-9]+?)', function($match){
		global $log, $return;
		$type = $match[0];
		$sensorNo = $match[1];
		$data = $match[2];
		
		$sensor = current($log->getSensor($sensorNo, $type));
		
		if ($log->data($sensor['id'], $data)){
			$return['status'] = 'ok';
		}
	});
	
	
	/**
	 * Sensoren auflisten
	 * @example http://log.server.com/api/v1/list[&apikey=R4nd0MsE3dT8beChANgeD]
	 */
	Request::match('list', function($match){
		global $log, $return;
		
		$sensoList = $log->getSensor();

		if ($sensoList){
			$return['status'] = 'ok';
			$return['sensorList'] = $sensoList;
		}
	});
	
	
	/**
	 * Daten auslesen
	 * @example http://log.server.com/api/v1/show/temp/SensorNo[/from][/to][&apikey=R4nd0MsE3dT8beChANgeD]
	 */
	Request::match('show/([a-zA-Z]+)/(.+)', function($match){
		global $log, $return;
		$type = $match[0];
		$data = explode('/', $match[1]);
		$sensorNo = $data[0];
		$from = @$data[1];
		$to = @$data[2];
		
		$data = false;
		$sensor = current($log->getSensor($sensorNo, $type));

		if ($sensor){
			
			if (!empty($from)){
				
				if (!empty($to)){
					
					$data = $log->get($sensor['id'], $from, $to);
				} else {
					
					$data = $log->get($sensor['id'], $from);
				}
				
			} else {
				
				$data = $log->get($sensor['id']);
			}
		}

		if ($data){
			$return['status'] = 'ok';
			$return['data'] = $data;
		}
	});
}

if ($return['status'] != 'ok')
{
	header('HTTP/1.0 404 Not Found');
}

echo json_encode($return);
