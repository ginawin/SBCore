<?php
/**
 * Class Number
 * @category Library
 * @package Number
 * @author LP (Le Van Phu) <vanphupc50@gmail.com>
 * @copyright 2018 SB Group
 * @version 1.0
 */

class SBNumber {
    public static function format($number = 0, $decimals = 0, $decPoint = ".", $thousandsSep = ",") {
        return number_format($number, $decimals, $decPoint, $thousandsSep);
    }

    public static function parseFloat($number = 0) {
        return floatval($number);
    }

    public static function parseInt($number = 0) {
        return intval(SBString::replace($number,","));
    }

    public static function roundUp($number = 0) {
        return ceil($number);
    }

    public static function roundDown($number = 0) {
        return floor($number);
    }
}
