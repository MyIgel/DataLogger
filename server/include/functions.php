<?php defined('_API') or die();
/**
 * Grundfunktionen
 *
 * Hier komen die Funktionen hin, für die es keine eigene Klasse gibt
 *
 * @package        DataLogger\Core
 * @author         Igor Scheller <igor.scheller@igorshp.de>
 * @license        http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

/** Logger Grundfunktion laden */
require_once __DIR__ . '/Logger.php';

/**
 * Gibt die Daten des Sensors zurück
 *
 * @param string     $sensor Sensor ID
 * @param int        $from   (optional) Timestamp ab dem die Daten ausgegeben werden
 * @param int|string $to     (optional) Timestamp bis zu diesem werden die Daten ausgegeben
 * @return array|bool Gibt die Daten im Erfolgsfall einem Array zurück, ansonsten false
 */
function getData($sensor, $from = 0, $to = "NOW")
{
    global $log;
    $data = $log->get($sensor, $from, $to);

    if (is_array($data)) {
        return $data;
    }
    return false;
}
