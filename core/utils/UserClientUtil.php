<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/4/2018
 * Time: 2:09 PM
 */

class UserClientUtil extends Util
{
    public function __construct(){
        parent::__construct();
        $this->loadModel("UserClientModel");
    }

    public function registerValid($iData){
        return true;
    }
    
    public function register($iData,$iUser)
    {
        if(!$this->registerValid($iData)){
            return false;
        }
        $oData = $this->UserClientModel->insertOne($iData);
        return $oData;
    }
    
    public function loginValid($iData){
        return true;
    }

    public function login($iData,$iUser)
    {
        if(!$this->loginValid($iData)){
            return false;
        }
        
        $temp = array('user_email' => $iData['user_email']);
        
        $oData =  $this->UserClientModel->getOne($temp);
        if (Password::verify($iData['user_pass'], $oData['user_pass'])) {
            return $oData;
        }
        return false;
    }
    
}