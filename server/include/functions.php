<?php defined('_API') or die();
/**
 * Grundfunktionen
 *
 * Hier komen die Funktionen hin, für die es keine eigene Klasse gibt
 *
 * @package		DataLogger\Core
 * @author		Igor Scheller <igor.scheller@igorshp.de>
 * @license		http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

/** Logger Grundfunktion laden */
include_once ('logger.class.php');

/**
 * Gibt die Daten des Sensors zurück
 *
 * @param string $sensor Sensor ID
 * @param int $from (optional) Timestamp ab dem die Daten ausgegeben werden
 * @param int $to (optional) Timestamp bis zu diesem werden die Daten ausgegeben
 * @return array|bool Gibt die Daten im Erfolgsfall einem Array zurück, ansonsten false
 */
function getData($sensor, $from = 0, $to = "NOW")
{
	global $log;
	$data = $log->get($sensor, $from, $to);

	if (is_array($data))
	{
		return $data;
	}
	return false;
}

/**
 * Wandelt ein Array in ein javascript Array um
 *
 * @param array $data Ein Array mit Daten
 * @return string Gibt die Daten als string zurück, welcher als javascript Array formatiert ist
 */
function jsArray($data)
{
	if (is_array($data) && $data = json_encode($data, true))
	{
		$data = str_replace('","', "],[", $data);
		$data = str_replace('{"', '[', $data);
		$data = str_replace('"}', ']', $data);
		$data = str_replace('":"', ',', $data);
		return $data;
	}
	return false;
}
