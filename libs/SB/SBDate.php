<?php
/**
 * Class Array
 * Contain all method for array processing.
 * @category Library
 * @package Array
 * @author LP (le van phu) <vanphupc50@gmail.com>
 * @copyright 2018 SB Group
 * @version 1.0
 */

class SBDate
{
	public static function getCreateTime($format = "Y/m/d H:i:s")
    {
        $today = new DateTime();
        $create_time = $today->format($format);
        return $create_time;
    }

    /**
     * [addDay description]
     * @param [type] $date [description]
     * @param [type] $num  [description]
     */
    public static function addDay($date,$num)
    {
    	$date = new DateTime($date);
    	$date->modify('+'.$num.' day');
		
		return $date->format('Y-m-d')." 23:59:59" ;
    }
    
    public static function Now2Str($format = "Y/m/d H:i:s")
    {
        $today = new DateTime();
        $create_time = $today->format($format);
        return $create_time;
    }
    
    public static function Now2Int()
    {
        return time();
    }
    
    public static function Str2Int($str_date, $str_format = 'Y/m/d', $str_sep='/', $h=0, $mi=0, $s=0) {
        if (!$str_date)
            return false;
        
        $arr = explode($str_sep, $str_date);
        
        switch ($str_format) {
            case 'Y/m/d': list($y, $m, $d) = $arr;
                break;
            
            case 'm/d/Y': list($m, $d, $y) = $arr;
                break;
            
            case 'n/j/Y': list($m, $d, $y) = $arr;
                break;
            
            case 'd/m/Y': list($d, $m, $y) = $arr;
                break;
        }
        return mktime($h, $mi, $s, $m, $d, $y);
    }
}