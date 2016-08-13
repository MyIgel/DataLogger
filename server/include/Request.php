<?php defined('_API') or die();

/**
 * Request Klasse
 *
 * @package        DataLogger\Core\Request
 * @author         Igor Scheller <igor.scheller@igorshp.de>
 * @license        http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */
class Request
{
    /**
     * Url Match
     *
     * Führt einen Regex auf die aufgerufene Url aus
     *
     * @example
     *  ([a-zA-Z]+)/([0-9]{,3}) für eine beliebige Zeichenkette und eine max. dreistellige id
     *
     * @param string   $path     Regex der Url
     * @param callable $function Aufzurufende Funktion
     * @return bool
     */
    public static function match($path, callable $function)
    {
        $url = trim(self::get('url', ''), '/ ');
        $path = str_replace('/', '\\/', $path);

        preg_match('/^' . $path . '$/', $url, $match);

        if ($match) {
            array_shift($match);
            $function($match);
            return true;
        }

        return false;
    }

    /**
     * GET Parameter
     *
     * Wenn vorhanden wird der GET-Parameter zurückgegeben, sonst der Standartwert
     *
     * @param   string $key
     * @param   mixed  $default = null
     * @returns mixed
     */
    public static function get($key, $default = null)
    {
        if (!empty($_GET[$key])) {
            return $_GET[$key];
        }
        return $default;
    }

    /**
     * POST Parameter
     *
     * Wenn vorhanden wird der POST-Parameter zurückgegeben, sonst der Standartwert
     *
     * @param   string $key
     * @param   mixed  $default = null
     * @returns mixed
     */
    public static function post($key, $default = null)
    {
        if (!empty($_POST[$key])) {
            return $_POST[$key];
        }
        return $default;
    }
}