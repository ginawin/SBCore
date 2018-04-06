<?php
/**
 * Class System
 * Contain all system or common method.
 * @category Module
 * @package System
 * @author LP (Le Van Phu) <vanphupc50@gmail.com>
 * @version 1.0
 */

class SBSystem
{
    /**
     * Check if a variable is empty.
     * @param  mixed  $var
     * @return boolean
     */
    public static function isEmpty($var = null) {
        return empty($var);
    }

    /**
     * Check variable is null
     * @param  mixed  $var
     * @return boolean
     */
    public static function isNull($var = null) {
        return is_null($var);
    }
}
