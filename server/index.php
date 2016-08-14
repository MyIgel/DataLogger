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
require_once __DIR__ . '/src/functions.php';
require_once __DIR__ . '/src/Logger.php';
require_once __DIR__ . '/src/Request.php';
require_once __DIR__ . '/src/Template.php';

$config = @include __DIR__ . '/config.php';
if (!is_array($config)) {
    die('Not configured');
}

$replace = [];

$success = Request::match('overview', function () use ($config, &$replace) {
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

    $replace['/*SCRIPT*/'] = Template::render('script', ['/*FROM*/' => $from]);

    $replace['%CONTENT%'] = Template::render('overview', [
        '%TOTAL%'  => $log->stats('total'),
        '/*FROM*/' => $from,
    ]);
});

if (!$success) {
    header('HTTP/1.0 404 Not Found');
}

echo Template::render('index', array_merge([
    '%STATS%'    => '/overview',
    '%CONTENT%'  => '404: Site not found',
    '/*SCRIPT*/' => '',
], $replace));
