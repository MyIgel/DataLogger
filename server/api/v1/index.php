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

/** Daten des Standardusers benutzen */
if (isset($url['0']) && $url['0'] == 'show' && empty($_GET['apikey']))
{
	$_GET['apikey'] = $api_key;
}

if (isset($_GET['apikey']) && $_GET['apikey'] == $api_key && isset($url['0']))
{
	$log = new logger($database, $_GET['apikey']);

	/** Daten hinzufügen */
	if ($url['0'] == 'log') // http://log.server.com/api/v1/log/temp/SenSorNo/[SenSorData]&apikey=R4nd0MsE3dT8beChANgeD
	{
		if (isset($url['1']) && isset($url['2']) && isset($url['3']))
		{
			$sensor = current($log->getSensor($url['2'], $url['1']));

			if ($log->data($sensor['id'], $url['3']))
			{
				$return['status'] = 'ok';
			}

		}
	}
	/** Daten ausgeben */
	else if ($url['0'] == 'show') // http://log.server.com/api/v1/show/temp/SenSorNo[/from][/to]
	{
		if (isset($url['1']) && isset($url['2']))
		{
			$data = false;

			if ($sensor = current($log->getSensor($url['2'], $url['1'])))
			{
				if (isset($url['3']))
				{
					if (isset($url['4']))
					{
						$data = $log->get($sensor['id'], $url['3'], $url['4']);
					}
					else
					{
						$data = $log->get($sensor['id'], $url['3']);
					}
				}
				else
				{
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
