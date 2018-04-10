<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/10/2018
 * Time: 3:17 PM
 */

class ContactUtil extends Util
{
    public function __construct()
    {
        parent::__construct();
        $this->loadModel("ContactModel");
        $this->loadUtil("MailUtil");
    }
    
    private function __createValid($iData){
        return true;
    }
    
    public function create($iData,$iUser){
        if(!$this->__createValid($iData)){
            return false;
        }
        $oData = $this->ContactModel->insertOne($iData);
        
        if($oData !== false){
            $email['add'][] = array('name'=>SYSTEM_SUPPORT_NAME,"email"=>SYSTEM_SUPPORT_EMAIL);
            if(isset($iData['contact_email']) && !empty($iData['contact_email'])){
                $email['cc'][] = array(
                    'name'=>isset($iData['contact_name'])&&!empty($iData['contact_name'])?$iData['contact_name']:$iData['contact_email'],
                    'email'=>$iData['contact_email']
                );
            }
            $this->MailUtil->send($email,$iData['contact_title'],$iData['contact_content']);
        }
        
        return $oData;
    }
}