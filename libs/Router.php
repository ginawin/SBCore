<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 3/14/2018
 * Time: 4:08 PM
 */

class Router
{
    public static $routes = array();

    public static function setRoute($url,$route)
    {
        if(!(isset($route['module']) && !empty($route['module']))){
            throw new SBException("$url route config module wrong");
        }
        if(!(isset($route['controller']) && !empty($route['controller']))){
            throw new SBException("$url route config controller wrong");
        }

        if(strpos($url,":action")!== false && (isset($route['action'])&&!empty($route['action']))){
            throw new SBException("$url route config action wrong");
        }

        if(strpos($url,":action") === false && !(isset($route['action'])&&!empty($route['action'])) ){
            throw new SBException("$url route config action wrong");
        }
        /*if(!(isset($config['action']) && !empty($config['action']))){
            throw new SBException("$url route config action wrong");
        }*/
        if($url!="/"){
            $url = trim($url,"/");
        }
        self::$routes[$url] = $route;
    }

    public static function getRoute($path)
    {
        $pt = array('/\/:action/','/\/\*/');
        $routeKey = '/';
        $route = '';

        //$flg = false;
        if(!empty(self::$routes)){
            foreach(self::$routes as $key => $val){
                $route = preg_replace($pt,"",$key);
                $temp = Url::segmentUri($path);
                if(!isset($temp[0])){
                    continue;
                }
                if(!empty($route) && $route != "/" && $temp[0] == $route){
                    $routeKey = $key;
                    //$flg = true;
                    break;
                }
            }
        }

        //if(!$flg){
            //$routeKey = "";
        //}

        $temp = array(
            ":action" => !empty(self::$routes[$routeKey]['action'])?self::$routes[$routeKey]['action']:"",
            "*" => ""
        );

        if($route != $routeKey && $route != "/"){
            //replace route vs routeKey
            $from = '/'.preg_quote($route, '/').'/';
            $seg1 = Url::segmentUri(preg_replace($from,"",$routeKey,1));
            $limit = 1;
            if(in_array(":action",$seg1)){
                $limit = 2;
            }
            
            //replace route vs path
            $seg2 = Url::segmentUri(preg_replace($from,"",$path,1),$limit);
            $i = 0;
            foreach($temp as $key => $val){
                if(empty($val)){
                    if(in_array($key,$seg1)){
                        $temp[$key] = isset($seg2[$i])?$seg2[$i]:"";
                        $i++;
                    }
                }
            }
        }

        if(isset(self::$routes[$routeKey]) && !empty(self::$routes[$routeKey])){
            $data = array(
                "module" => !empty(self::$routes[$routeKey]['module'])?self::$routes[$routeKey]['module']:"",
                "controller" => !empty(self::$routes[$routeKey]['controller'])?self::$routes[$routeKey]['controller']:"",
                "action" => !empty($temp[':action'])?$temp[':action']:DEFAULT_METHOD,
                "*" => !empty($temp['*'])?$temp['*']:""
            );
            return $data;
        }

        return false;
    }

    public static function getUrl($route){
        if(!(isset($route['module']) && !empty($route['module']))){
            throw new SBException("Module config empty");
        }
        if(!(isset($route['controller']) && !empty($route['controller']))){
            throw new SBException("Controller config empty");
        }
        if(!(isset($route['action']) && !empty($route['action']))){
            throw new SBException("Action config empty");
        }
        //$lastSegment = (isset($route['*']) && !empty($route['*']))?$route['*']:"";
        $temp = "/";
        if(!empty(self::$routes)){
            foreach(self::$routes as $key => $val){
                if ($val['module'] == $route['module'] && $val['controller'] == $route['controller']){
                    if(isset($route['action']) && !empty($route['action'])){
                        $temp = $key;
                        break;
                    }elseif(strpos($key,":action") !== false){
                        $temp = $key;
                        break;
                    }
                }
            }
        }

        if($temp == "/"){
            return BASE_URL;
        }

        $pt = array('/\/:action/','/\/\*/');
        $url = preg_replace($pt,"",$temp);
        
        if(!empty($url)){
            $url = BASE_URL.$url;
            if($route['action']!="index"){
                $url.="/".$route['action'];
            }
            if(isset($route['*']) && !empty($route['*'])){
                $url.="/".$route['*'];
            }
            return $url;
        }
        return BASE_URL;

    }




}