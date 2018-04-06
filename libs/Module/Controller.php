<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 3/13/2018
 * Time: 5:03 PM
 */

class Controller
{
    function __construct(){
        $this->result = array("code"=>"","msg"=>"");
        //$this->user = $this->login();
        $this->user = array(
            'agent_code' => "TBKT"
        );
    }
    
    public function login(){
        if(isset($_POST['rq_user']) && !empty($_POST['rq_user'])){
        
            $rq_user = $_POST['rq_user'];
            if(isset($rq_user['b2b_flg']) && $rq_user['b2b_flg'] == 1){
            
            }else{
                if(isset($rq_user['token']) && !empty($rq_user['token'])){
    
                }elseif(!(isset($rq_user['user_pass']) && !empty($rq_user['user_pass']))){
                
                }else{
                
                }
            }
            
            
            /*if(isset($rq_user['token']) && !empty($rq_user['token'])){
                //B2C
            
            }elseif(!(isset($rq_user['user_pass']) && !empty($rq_user['user_pass']))){
                //No-login
                $rq_user['agent_code'] = "OLNT";
                $rq_user['user_email'] = "tanthanh@ginawin.com";
                $rq_user['user_name'] = "Thanh San";
            
                $this->user_info = $this->_initB2BUser();
                $this->user_info['user_id'] = 1;
                $this->user_info['user_name'] = $rq_user['user_name'];
                $this->user_info["agent_staff_name"] = $rq_user['user_name'];
                $this->user_info["agent_staff_email"] = $rq_user['user_email'];
            }else{
                //B2B
                $rq_user['user_cd'] = "tanthanh";
                $rq_user['user_pass'] = "4f1a681b8f16ff18c9b1fa0e7b324dcf";
                $rq_user['user_email'] = "tanthanh@ginawin.com";
                $rq_user['user_name'] = "Thanh San";
                $rq_user['agent_code'] = "OLNT";
            
                $iData = array(
                    'user_name' => $rq_user['user_cd'],
                    'user_pass' => $rq_user['user_pass']
                );
                $this->user_info = $this->UserModel->getOne($iData);
            
                $this->user_info["agent_staff_email"] = !empty($rq_user['user_email'])?$rq_user['user_email']:"";
                $this->user_info["agent_staff_name"] = !empty($rq_user['user_name'])?$rq_user['user_name']:(!empty($this->user_info['agent_staff_name'])?$this->user_info['agent_staff_name']:$this->user_info['user_name']);
            
                if($this->user_info['user_level'] != 4){
                    if(!empty($rq_user['agent_code'])){
                        $iData = array(
                            "agent_code" => $rq_user['agent_code']
                        );
                        $mAgent = $this->AgentModel->getOne($iData,"agent_id,agent_code");
                        if(!empty($mAgent)){
                            $this->user_info['agent_id'] = $mAgent['agent_id'];
                            $this->user_info['agent_code'] = $mAgent['agent_code'];
                        }else{
                            $this->user_info['agent_id'] = 0;
                            $this->user_info['agent_code'] = $rq_user['agent_code'];
                        }
                    }
                }
            }*/
        }
    }

    public function res($code,$data = array()){
        $msg = "";
        switch($code){
            case 1:
                $str = "Success";
                break;
            default:
                $str = "Error";
                break;
        }
        $res = array(
            "result" => array("code" => $code,"msg" => $str)
        );
        if(!empty($data)){
            $res["data"] = $data;
        }else{
            if($code == 1){
                $res["data"] = array();
            }
        }
        echo json_encode($res);
        die;
    }

    public function getLastQuery(){
        $db = Database::instance();
        return $db->last_query;
    }
    public function getLastSQLError(){
        $db = Database::instance();
        return $db->last_error;
    }
    public function getUpdateId(){
        $db = Database::instance();
        return $db->last_update_id;
    }

    public function loadUtil(){
        $utils = func_get_args();
        if(!empty($utils)){
            foreach($utils as $key => $val){
                App::loadFile(UTIL_PATH.$val.PHP_EXT);
                $this->{$val} = new $val;
            }
        }
    }

}