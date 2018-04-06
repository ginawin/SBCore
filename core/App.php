<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 3/13/2018
 * Time: 3:27 PM
 */
class App{

    protected static $modules = array('client'=>array(),'admin'=>array());

    public static function loadDir($dir){
        foreach (scandir($dir) as $file){
            // directory?
            if (is_dir( $dir.$file ) && substr( $file, 0, 1 ) !== '.' && strpos($file,"views") === false){
                self::loadDir( $dir.$file.'/' );
            }
            // php file?
            if (preg_match( "/.php$/i" , $file ) )
            {
                self::loadFile($dir . $file);
            }
        }
    }

    public static function loadFile($path){
        if(file_exists($path)){
            require_once $path;
        }else{
            throw new SBException("$path is not exists");
        }
    }

    public static function autoload(){

        require_once CONFIG_PATH . 'Module.php';

        require_once CONFIG_PATH . 'Route.php';

        require_once CORE_CONFIG_PATH . "Config.php";

        if(isset($config["database"]) && !empty($config["database"]))
        {
            foreach($config["database"] as $key => $val){
                Database::instance($key,$val);
            }
        }

        $path = Url::getPathInfo();
        $router = Router::getRoute($path);

        if($router !== false){
            $module  = $router['module'];
            $controller  = $router['controller'];
            $method = $router['action'];
            $segments = false;
            if(!empty($router["*"])){
                $segments = Url::segmentUri($router["*"]);
            }
        }else{
            $module  = DEFAULT_MODULE;
            $controller  = DEFAULT_CONTROLLER;
            $method  = DEFAULT_METHOD;

            $segments = Url::segmentUri();

            if(!empty($segments)){
                foreach($segments as $key => $val){
                    switch ($key){
                        case 1:
                            $module = $segments[$key];
                            break;
                        case 2:
                            $controller = $segments[$key];
                            break;
                        case 3:
                            $method = $segments[$key];
                            break;
                    }
                    if($key <= 3){
                        unset($segments[$key]);
                    }
                }
            }
        }
        
        if(!(in_array($module,App::$modules['client']) || in_array($module,App::$modules['admin']))){
            throw new SBException("Module $module is not register");
        }
        
        App::loadDir(MODULES_PATH.$module.DS);
        $class = ucfirst($controller) ."Controller";

        if(!class_exists($class)){
            throw new SBException("$class is not exists");
        }
        
        if(!method_exists($class,$method))
        {
            throw new SBException("Function '$method' is not exist.");
        }

        $ReflectionMethod = new ReflectionMethod($class, $method);
        if (!$ReflectionMethod->isPublic())
        {
            throw new SBException("Function '$method' is not exist.");
        }



        $view = $controller."/".$method;
        $controller = new $class();



        if(!empty($segments))
        {
            eval('$controller->{$method}("'.implode('","', $segments).'");');

        } else {
            $controller->{$method}();
        }
        if(empty($controller->template->content)){
            $controller->template->content = new View($view);
        }
        if (method_exists($controller,"setMessage")) {
            $controller->setMessage();
        }
        //START VIEW
        $sess_client = false;
        if(isset($_SESSION["sess_client"])&&!empty($_SESSION["sess_client"])){
            $sess_client = $_SESSION["sess_client"];
        }

        $sess_admin = false;
        if(isset($_SESSION["sess_admin"])&&!empty($_SESSION["sess_admin"])){
            $sess_admin = $_SESSION["sess_admin"];
        }

        //set content value
        $content = '';
        if(isset($controller->template->content)&&!empty($controller->template->content)){
            if(is_a($controller->template->content,"View")){
                foreach($controller->template->content as $key=>$val){
                    if($key=='tsf_filename' || $key=='tsf_filetype'){
                        continue;
                    }
                    Eval("$".$key."=\$val".";");
                }
                $ref = new ReflectionClass($class);
                $fileName = dirname(dirname($ref->getFileName()))."/views/".strtolower($controller->template->content->tsf_filename).".".strtolower($controller->template->content->tsf_filetype);
                if(file_exists($fileName)){
                    ob_start();
                    require $fileName;
                    $content = ob_get_clean();
                }else{
                    //ERROR
                    throw new SBException("Content View Not Exists", 1);
                }
            }else{
                $content = $controller->template->content;
            }
        }



        //set template value
        if(isset($controller->template)&&!empty($controller->template)){
            if(isset($controller->template_param)&&!empty($controller->template_param)){
                foreach($controller->template_param as $key=>$val){
                    if(strtolower($key)!='content'){
                        Eval("$".$key."=\$val".";");
                    }
                }
            }
            foreach($controller->template as $key=>$val){
                if(strtolower($key)!='content'){
                    Eval("$".$key."=\$val".";");
                }
            }

            if(file_exists("template/".$controller->template->tsf_filename.".".$controller->template->tsf_filetype)){
                require "template/".$controller->template->tsf_filename.".".$controller->template->tsf_filetype;
            }else{
                //ERROR
                throw new SBException("Template View Not Exists", 1);
            }
        }
    }
    
    public static function addModule($name,$type = "client")
    {
        if($type == "admin"){
            self::$modules['admin'][] = $name;
        }else{
            self::$modules['client'][] = $name;
        }
    }

    public static function checkModule($name,$type)
    {
        if($type != "admin"){
            $type = "client";
        }
        return in_array($name,self::$modules[$type]);
    }


}