<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 11:06 AM
 */

class ShopPermissionModel extends Model1
{
    public $table_name = "m001_permission";
    public $primary_key = array("perm_id");
    public function __construct(){
        parent::__construct();
    }

    public function secureCheck($iData){
        $this->db->select("module_id");
        $this->db->where("perm_url",$iData['permission']);
        $result = $this->selectOne();
        return $result;
    }
}