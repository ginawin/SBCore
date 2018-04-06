<?php
///**
// * Class Debug
// * @category Library
// * @package Debug
// * @author LVP [levanphu.info] <vanphupc50@gmail.com>
// * @copyright 2018 LP Group
// * @version 1.0
// */
//
//require_once 'phar://' . VENDOR_PATH . 'tracy' . DS . 'tracy.phar';
//use Tracy\Debugger;
//
//class Debug
//{
//    /**
//     * Enable VSDebug.
//     */
//    public static function enable()
//    {
//        error_reporting(E_ALL);
//        ini_set("log_errors", 1);
//        ini_set('display_errors', TRUE); // Error display
//        if (DEBUG_BAR) {
//            Debugger::enable(Debugger::DEVELOPMENT, LOGS_PATH);
//        } else {
//            error_reporting(0);
//            Debugger::enable(Debugger::PRODUCTION, LOGS_PATH);
//        }
//    }
//
//    public static function log($string)
//    {
//        Debugger::fireLog($string);
//        Debugger::log($string);
//    }
//
//    public static function logError($string)
//    {
//        Debugger::log($string, Debugger::ERROR);
//    }
//
//    public static function startTimer($string = '')
//    {
//        Debugger::timer($string);
//    }
//
//    public static function endTimer($string = '')
//    {
//        echo Debugger::timer($string);
//    }
//}
