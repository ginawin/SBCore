<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 11:07 AM
 */

class ShopUserClientModel extends Model1
{
    public $table_name = "m099_user_client";
    public $primary_key = array("user_id");
    public function __construct(){
        parent::__construct();
    }

    public function secureCheck($iData){
        $this->db->select("user_id");
        $this->db->where('SHA2(CONCAT("sOUtH3rNbRe3ze",user_email,api_key),256)',$iData['signature']);
        $result = $this->selectOne();
        return $result;
    }
}