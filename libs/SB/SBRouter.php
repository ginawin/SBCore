<?php
/**
 * Class Router Load Interface
 * @category Library
 * @package Router
 * @author LVP [levanphu.info] <vanphupc50@gmail.com>
 * @copyright 2018 LP Group
 * @version 1.0
 */
class SBRouter 
{
	
	function __construct()
	{
		$this->__instance();
	}
	public function __instance()
	{
		require_once CONFIG_PATH . "Config.php";

		if(isset($config["database"]) && !empty($config["database"]))
		{

		    foreach($config["database"] as $key => $val){

		        Database::instance($key,$val);

		    }

		}
		/**
		 * Bootstrap to load MVC construct and require to class
		 *
		 */
		foreach($config["modules"] as $dir) 
		{
			App::loadDir($dir."/");
		}
	
	}
	public static function getUrl()
	{
		// Start Controller Load
		if(isset($_SERVER["REQUEST_URI"])&&!empty($_SERVER["REQUEST_URI"]))
		{
		    $arr_url = explode("/",trim(str_replace("?".$_SERVER["QUERY_STRING"],"",$_SERVER["REQUEST_URI"]),"/"));
		}
		return $arr_url;
	}
	
	public function load()
	{
		$method  = DEFAULT_METHOD;
		$module  = DEFAULT_CONTROLLER;
		$arr_url = $this->getUrl();
		
		foreach($arr_url as $id=>$arr)
		{
		    if(!empty($arr))
		    {
                if($arr == SITE_DOMAIN){
                    unset($arr_url[$id]);
	            	continue;
                }else{
                    $module = ucfirst($arr);
		            unset($arr_url[$id]);
		            break;
                }
		    }
		}
    	/**
    	 * [$class is Class Pref name]
    	 * @var $class = class name
    	 * @var  $module is Module name
    	 * @see  Name of Class is <Module_Action> [<User_Controller>]
    	 */
    	$class = $module ."Controller";

    	if(!class_exists($class)){
    	    echo json_encode(array('error' => true, 'data' => 'Invalid API Method.' ));
    	    die;
    	}
        //$view = "";
        if(!empty($arr_url)){

    	    $arr_url = array_values($arr_url);
    
    	    $method = $arr_url[0];
    
    	    unset($arr_url[0]);
            
            //$view = $module."/".$method;
    
    	} else {
    
    	    $method = "index";
            
            //$view = $module."/".$method;
    	}
    	
    	// New object of Controller Class
    	$controler = new $class;
    	if(method_exists($class,$method))
    	{
    		/**
    		 * [$ReflectionMethod description]
    		 * @see <http://php.net/manual/en/class.reflectionmethod.php>
    		 */
    	    $ReflectionMethod = new ReflectionMethod($class, $method);
    
    	    if ($ReflectionMethod->isPublic())
    	    {
    
    	        if(!empty($arr_url))
    	        {
    	            eval('$controler->{$method}("'.implode('","', $arr_url).'");');
    
    	        } else {
    	            $controler->{$method}();
    	        }
                
                /*if(empty($controler->template->content)){
                    $controler->template->content = new View($view);
                }*/
                    
                    
    	    } else {
    
    	        throw new SBException("Function '$method' is not exist.", 1);
    	    }
    	} else {
    	        throw new SBException("Function '$method' is not exist.", 1);
   		}
        /*if (method_exists($controler,"setMessage")) {
            $controler->setMessage();
        }*/
    	// End of  Controller
                                
        //START VIEW
        /*$sess_client = false;
        if(isset($_SESSION["client_login"])&&!empty($_SESSION["client_login"])){
            $sess_client = $_SESSION["client_login"];
        }
        
        $sess_admin = false;
        if(isset($_SESSION["admin_login"])&&!empty($_SESSION["admin_login"])){
            $sess_admin = $_SESSION["admin_login"];
        }
        
        //set content value
        $content = '';
        if(isset($controler->template->content)&&!empty($controler->template->content)){
            if(is_a($controler->template->content,"View")){
                foreach($controler->template->content as $key=>$val){
                    if($key=='tsf_filename' || $key=='tsf_filetype'){
                        continue;
                    }
                    Eval("$".$key."=\$val".";");
                    
                }
                $ref = new ReflectionClass($class);
        		$fileName = dirname(dirname($ref->getFileName()))."/views/".strtolower($controler->template->content->tsf_filename).".".strtolower($controler->template->content->tsf_filetype);
                if(file_exists($fileName)){
                    ob_start();
                    require $fileName;
                    $content = ob_get_clean();
                }else{
                    //ERROR
                    throw new SBException("Content View Not Exists", 1);
                }
            }else{
                $content = $controler->template->content;
            }
        }
        
        
        
        //set template value
        if(isset($controler->template)&&!empty($controler->template)){
            if(isset($controler->template_param)&&!empty($controler->template_param)){
                foreach($controler->template_param as $key=>$val){
                    if(strtolower($key)!='content'){
                        Eval("$".$key."=\$val".";");
                    }
                }
            }
            foreach($controler->template as $key=>$val){
                if(strtolower($key)!='content'){
                    Eval("$".$key."=\$val".";");
                }
            }

            if(file_exists("template/".$controler->template->tsf_filename.".".$controler->template->tsf_filetype)){
                require "template/".$controler->template->tsf_filename.".".$controler->template->tsf_filetype;
            }else{
                //ERROR
                throw new SBException("Template View Not Exists", 1);
            }
        }*/
	}
}
?>