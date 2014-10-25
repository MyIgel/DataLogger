<?php defined('_API') or die();
/**
 * DataLogger Klasse
 *
 * Das "Hirn" des Loggers, hier passiert das wichtigste
 *
 * @package		DataLogger\Core\LoggerBrain
 * @author		Igor Scheller <igor.scheller@igorshp.de>
 * @license		http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

/**
 * DataLogger Klasse
 *
 * Speichert Daten, Liest sie wieder aus und erzeugt statistische Informationen
 */
class logger
{
	/** MySQLi Dantenbankverbindung */
	private $db;
	/** User API-Key */
	private $user = '';
	/** Enthält den zuletzt aufgetretenen Fehler */
	var $err = false;

	/**
	 * Verbindung zur Datenbank herstellen
	 *
	 * @param array $database Configuration der MySQL Datenbank
	 * @param string $user API-Key des Users
	 */
	function __construct($database, $user)
	{
		$this->db = @new mysqli($database['host'], $database['user'], $database['password'], $database['database']);

		if (mysqli_connect_errno())
		{
			$this->err = 'The database hates me :( Error: ' . mysqli_connect_errno() . ' : ' . mysqli_connect_error();
			die('{"status":"err","err":"' . $this->err . '"}');
		}
		$this->db->set_charset("utf8");
		$this->user = $user;
	}

	/**
	 * Neue Daten hinzufügen
	 *
	 * @param int $sensor ID des Sensors
	 * @param string|int|float $data Sensorwert
	 * @return bool
	 */
	function data($sensor, $data)
	{
		$query = "INSERT INTO data (sensorID, data)
					VALUES ('" . $this->db->real_escape_string($sensor) . "',
							'" . $this->db->real_escape_string($data) . "'
						   )";

		return $this->db->query($query);
	}

	/**
	 * Sensoren auslesen
	 *
	 * Liest alle Sensoren des Benutzers aus.
	 * Kann auf eine Sensorid und/oder auf einen sensortyp beschränkt werden
	 *
	 * @param int $sensor (optional) ID des Sensors
	 * @param string $type (optional) Art des Sensors
	 * @return array Informationen über die Sensoren/den Sensor
	 */
	function getSensor($sensor = false, $type = false)
	{
		$query = "SELECT * FROM sensor WHERE user = '" . $this->db->real_escape_string($this->user) . "'";
		$query.= ($sensor) ? ' AND sid = "' . $this->db->real_escape_string($sensor) . '"' : '';
		$query.= ($type) ? ' AND type = "' . $this->db->real_escape_string($type) . '"' : '';

		if ($result = $this->db->query($query))
		{
			$sensors = array();

			while ($row = $result->fetch_array(MYSQL_ASSOC))
			{
				$sensors[$row['id']] = $row;
			}

			return $sensors;
		}

		return false;
	}

	/**
	 * Daten auslesen
	 *
	 * Liest Die Daten des angegebenen Sensors aus.
	 * Kann auf eine Zeitspanne (von/bis) beschränkt werden
	 *
	 * @param int $sensor ID des Sensors
	 * @param int $from (optional) Timestamp ab welchem die daten ausgelesen werden
	 * @param int|string $to (optional) Timestamp bis wo die Daten ausgelesen werden. Kann auch NOW sein
	 * @return array Daten
	 */
	function get($sensor, $from = 0, $to = "NOW")
	{
		$to = ($to == "NOW") ? time() : $to;
		$query = "SELECT (UNIX_TIMESTAMP(time)*1000) AS time,data FROM `data`
				  WHERE sensorID = '" . $this->db->real_escape_string($sensor) . "'
				  AND time BETWEEN
					FROM_UNIXTIME(" . $this->db->real_escape_string($from) . ")
					AND FROM_UNIXTIME(" . $this->db->real_escape_string($to) . ")";

		if ($result = $this->db->query($query))
		{
			$data = array();

			while ($row = $result->fetch_array(MYSQL_ASSOC))
			{
				$data[$row['time']] = $row['data'];
			}

			return $data;
		}

		return false;
	}

	/**
	 * Benutzungsstatistiken
	 *
	 * Liest alle Sensoren des Benutzers aus.
	 * Kann auf eine Sensorid und/oder auf einen sensortyp beschränkt werden
	 *
	 * @param string $type Art der Statistiken, aktuell nur total
	 * @return int Anzahl der Daten
	 */
	function stats($type)
	{
		if ($type == "total")
		{
			$result = $this->db->query("SELECT count(id) AS total FROM `data`");

			if ($result)
			{
				$data = $result->fetch_array(MYSQL_ASSOC);
				return $data['total'];
			}
		}

		return false;
	}
}
