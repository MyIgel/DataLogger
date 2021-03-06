<?php defined('_API') or die();
/**
 * Grundeinstellungen des DataLoggers
 *
 * Hier werden die wichtigsten Optionen festgelegt
 */

/**
 * API-Key des Users dessen Daten auf der Startseite angezeigt werden
 *
 * Durch die zufälllig erstellte Zeichenkette des API-Keys zu ersetzen
 */
$api_key = 'R4nd0MsE3dT8beChANgeD';

/** Datenbank Konfiguration */
$database = array(
	/** Die Adresse des MYSQL-Servers, normalerweise localhost */
	'host' => 'localhost',
	/** Benutzer der MySQL Datenbank */
	'user' => 'mysqlLogUser',
	/** Passwort für die MySQL Datenbank */
	'password' => 'mysqlLogPass',
	/** Name der MySQL Datenbank */
	'database' => 'mysqlLogDB',
);
