<?php
/**
 * Data Logging API
 *
 * @todo           access_token, header err response, requests (GET,POST,DELETE...) ...
 * @package        DataLogger\Core\LoggerBrain
 * @author         Igor Scheller <igor.scheller@igorshp.de>
 * @license        http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
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
require_once __DIR__ . '/../../src/functions.php';
require_once __DIR__ . '/../../src/Logger.php';
require_once __DIR__ . '/../../src/Request.php';

$return = ['status' => 'err'];
$config = @include __DIR__ . '/../../config.php';
if (!is_array($config)) {
    header('HTTP/1.0 500 Internal Server Error');
    json_encode(['status' => 'error', 'message' => 'Not configured']);
    exit;
}

$apiKey = '';
if (Request::post('apikey')) {
    $apiKey = Request::post('apikey');
} elseif (Request::header('apikey')) {
    $apiKey = Request::header('apikey');
} elseif (Request::get('apikey')) {
    $apiKey = Request::get('apikey');
} else {
    Request::match('(list|show)(.*)', function () use ($config) {
        global $apiKey;
        $apiKey = $config['apiKey'];
    });
}

if (empty($apiKey)) {
    header('HTTP/1.0 401 Unauthorized');
    json_encode(['status' => 'error', 'message' => 'Not authorized']);
}

$log = new Logger($config['database'], $config['apiKey']);

/**
 * Daten hinzufügen
 *
 * @example http://log.server.com/api/v1/log/temp/SensorNo/SensorData&apikey=R4nd0MsE3dT8beChANgeD
 */
Request::match('log/([a-zA-Z]+)/(.+)/([0-9]+\.?[0-9]+?)', function ($match) {
    global $log, $return;
    $type = $match[0];
    $sensorNo = $match[1];
    $data = $match[2];

    $sensor = current($log->getSensor($sensorNo, $type));

    if ($log->data($sensor['id'], $data)) {
        $return['status'] = 'ok';
    }
});

/**
 * Sensoren auflisten
 *
 * @example http://log.server.com/api/v1/list[&apikey=R4nd0MsE3dT8beChANgeD]
 */
Request::match('list', function () {
    global $log, $return;

    $sensorList = $log->getSensor();

    if ($sensorList) {
        $return['status'] = 'ok';
        $return['sensorList'] = $sensorList;
    }
});

/**
 * Daten auslesen
 *
 * @example http://log.server.com/api/v1/show/temp/SensorNo[/from][/to][&apikey=R4nd0MsE3dT8beChANgeD]
 */
Request::match('show/([a-zA-Z]+)/(.+)', function ($match) {
    global $log, $return;
    $type = $match[0];
    $data = explode('/', $match[1]);
    $sensorNo = $data[0];
    $from = @$data[1];
    $to = @$data[2];

    $data = false;
    $sensor = current($log->getSensor($sensorNo, $type));

    if ($sensor) {
        if (!empty($from)) {
            if (!empty($to)) {
                $data = $log->get($sensor['id'], $from, $to);
            } else {
                $data = $log->get($sensor['id'], $from);
            }
        } else {
            $data = $log->get($sensor['id']);
        }
    }

    if ($data) {
        $return['status'] = 'ok';
        $return['data'] = $data;
    }
});

if ($return['status'] != 'ok') {
    header('HTTP/1.0 404 Not Found');
}

echo json_encode($return);
