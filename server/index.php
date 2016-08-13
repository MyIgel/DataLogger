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
 * @package        DataLogger
 * @version        0.1.1
 * @author         Igor Scheller <igor.scheller@igorshp.de>
 * @copyright      2014 Igor Scheller
 * @license        http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */
use Caleano\DataLogger\Template;

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
require_once __DIR__ . '/include/functions.php';
require_once __DIR__ . '/include/Logger.php';
require_once __DIR__ . '/include/Request.php';
require_once __DIR__ . '/include/Template.php';

$config = include __DIR__ . '/include/config.php';
if (!is_array($config)) {
    die('Not configured');
}

/** Logginginstanz initialisieren */
$log = new Logger($config['database'], $config['apiKey']);

/** Zeitspanne berechnen, welche angezeigt werden soll */
if (Request::get('day')) {
    $from = (int)(time() - (1.01 * 60 * 60 * 24 * Request::get('day'))); // 24 Std.
} elseif (Request::get('hour')) {
    $from = (int)(time() - (1.1 * 60 * 60 * Request::get('hour'))); // x Std.
} elseif (Request::get('time') && Request::get('time') == 'all') {
    $from = 1;
} else {
    $from = (int)(time() - (1.1 * 60 * 60 * 24 * 3)); // 3 Tage
}

$script = Template::render('script', ['/*FROM*/' => $from]);

echo Template::render('index', [
    '%SELF%'     => $_SERVER['PHP_SELF'],
    '%TOTAL%'    => $log->stats('total'),
    '/*FROM*/'   => $from,
    '/*SCRIPT*/' => $script,
]);
