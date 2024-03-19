<?php

/**
 * Varios métodos de utilidad
 */
final class Utils
{

    private function __construct()
    {
    }

    /**
     * Elimina el exceso de espacios en blanco de una cadena.
     */
    public static function strip($str)
    {
        return preg_replace('/\s\s+/', ' ', trim($str));
    }


    /**
     * Find the position of the second occurrence of a substring in a string
     */
    public static function strsecpos($str, $char)
    {

        $pos = strpos($str, $char) + 1;

        return strpos(substr($str,  $pos), $char) + $pos;
    }
}
