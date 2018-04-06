<?php
/**
 * Class Request
 * @category Library
 * @package Request
 * @author LVP [levanphu.info] <vanphupc50@gmail.com>
 * @copyright 2018 SB Group
 * @version 1.0
 */

class Request
{
    private static $__instance = null;
    private static $__post     = null;

    /**
     * The key that indicates an 'overridden' request method.
     * Typically used to support methods over a REST API. e.g. PUT.
     *
     * @var string
     */
    const OVERRIDE = 'HTTP_X_HTTP_METHOD_OVERRIDE';

    /**
     * An array of URI resolvers.
     *
     * @var array
     */
    protected static $resolvers = array();

    /**
     * All input data for the request. Used as a cache.
     *
     * @var array
     */
    protected static $input = array();

    /**
     * GetInstance
     * @return $__instance
     */
    public static function getInstance()
    {
        if (null === self::$__instance) {
            self::$__instance = new self();
        }

        return self::$__instance;
    }

    public static function getQueryUri()
    {
        $params = func_get_args();

        $queryString = self::server('QUERY_STRING');
        $queryArray  = explode('&', $queryString);

        foreach ($queryArray as $k => $v) {
            $keyValue = explode('=', $v);

            if (count($keyValue) > 1) {
                if (in_array($keyValue[0], $params) || empty($keyValue[1])) {
                    // remove param key in query uri string
                    unset($queryArray[$k]);
                }
            }
        }

        return implode('&', $queryArray);
    }
    /**
     * isGET
     * @return boolean
     */
    public static function isGET()
    {
        return (self::method() == 'GET') ? true : false;
    }

    /**
     * isPOST
     * @return boolean
     */
    public static function isPOST()
    {
        return (self::method() == 'POST') ? true : false;
    }
    /**
     * Get the request method. e.g. GET, POST.
     *
     * This method can be overridden to support non-browser request
     * methods. e.g. PUT, DELETE.
     *
     * @return string
     */
    public static function method()
    {
        $method = static::overridden() ?
        (isset($_POST[static::OVERRIDE]) ?
            $_POST[static::OVERRIDE] : $_SERVER[static::OVERRIDE]) :
        $_SERVER['REQUEST_METHOD'];
        return strtoupper($method);
    }
    /**
     * Get the address of the request's referrer.
     *
     * @param  string $default The default value.
     * @return string
     */
    public static function referrer($default = null)
    {
        return self::server('HTTP_REFERER', $default);
    }
    
    /**
     * Check if the request method has been overriden.
     *
     * @return bool
     */
    protected static function overridden()
    {
        return isset($_POST[static::OVERRIDE]) ||
        isset($_SERVER[static::OVERRIDE]);
    }


    /**
     * Get the requested URL. e.g. http://a.com/bar?q=foo
     *
     * @return string
     */
    public static function url()
    {
        return static::scheme(true) . static::host()
        . static::port(true) . static::uri() . static::query(true);
    }

    /**
     * Get the request URI. e.g. /blog/item/10
     *
     * Excludes query strings.
     *
     * @return string
     */
    public static function uri()
    {
        foreach (static::resolvers() as $key => $resolver) {
            $key = is_numeric($key) ? $resolver : $key;
            if (isset($_SERVER[$key])) {
                if (is_callable($resolver)) {
                    $uri = $resolver($_SERVER[$key]);
                    if ($uri !== false) {
                        return $uri;
                    }

                } else {
                    return $_SERVER[$key];
                }
            }
        }
    }

    /**
     * Get $_POST data with index name
     *
     * @return string
     */
    public static function post($name){
        return isset($_POST[$name])?$_POST[$name]:false;
    }

    /**
     * Get $_GET data with index name
     *
     * @return string
     */
    public static function get($name){
        return isset($_GET[$name])?$_GET[$name]:false;
    }
}
