<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/9/2018
 * Time: 9:17 AM
 */

class CheckerUtil extends Util
{
    public function __construct()
    {
        parent::__construct();
        $this->loadModel("ShopUserClientModel","ShopPermissionModel","ShopBookingModel");
    }
    
    public function secureCheck($iData,$iUser){
        $flg = true;
        
        //user check
        $oData = $this->ShopUserClientModel->secureCheck($iData);
        if(!(isset($oData['user_id']) && !empty($oData['user_id']))){
            return false;
        }
        $iData['user_id'] = $oData['user_id'];
        
        //permission check
        $oData = $this->ShopPermissionModel->secureCheck($iData);
        if(!(isset($oData['module_id']) && !empty($oData['module_id']))){
            return false;
        }
        $iData['module_id'] = $oData['module_id'];
        
        //booking check
        $oData = $this->ShopBookingModel->secureCheck($iData);
        
        return $oData;
    }
}