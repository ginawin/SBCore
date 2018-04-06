<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 3/13/2018
 * Time: 3:42 PM
 */

class Date
{
    public static function getCreateTime($format = "Y/m/d H:i:s")
    {
        $today = new DateTime();
        $create_time = $today->format($format);
        return $create_time;
    }
}