<?php namespace Caleano\DataLogger;

defined('_API') or die('NOPE');

/**
 * Class Template
 */
class Template
{
    /**
     * Renders a template
     *
     * @param string   $name
     * @param string[] $replacements
     * @return string
     */
    public static function render($name, $replacements = [])
    {
        $template = self::getPart($name);

        $from = array_keys($replacements);
        $to = array_values($replacements);

        $template = str_replace($from, $to, $template);

        return $template;
    }

    /**
     * Load a template part
     *
     * @param string $name
     * @return string
     */
    public static function getPart($name)
    {
        $templateFile = __DIR__ . '/../templates/' . $name . '.html';
        $jsTemplateFile = __DIR__ . '/../templates/' . $name . '.js';

        if (!is_readable($templateFile)) {
            $templateFile = $jsTemplateFile;
            if (!is_readable($templateFile)) {
                return '';
            }
        }

        $template = file_get_contents($templateFile);
        return $template;
    }
}
