<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/12/2018
 * Time: 1:23 PM
 */

class SBCache
{
    public static function get($key){
        return apcu_fetch($key);
    }
    
    public static function set($key,$val,$ttl = CACHE_TTL){
        apcu_add($key,$val,$ttl);
    }
    
    public static function exists($key){
        return apcu_exists($key);
    }
    
    public static function delete($key){
        apcu_delete($key);
    }
    
    public static function clear(){
        apcu_clear_cache();
    }
}