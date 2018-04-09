<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 11:02 AM
 */

class ShopBookingModel extends Model1
{
    public $table_name = "m002_booking";
    public $primary_key = array("booking_id");
    public function __construct(){
        parent::__construct();
    }

    public function secureCheck($iData){
        $this->db->select("m002_booking.`booking_id`,m002_booking.`server_ip`,m002_booking_module.`expire_from`,m002_booking_module.`expire_to`");
        $this->db->join("m002_booking_module",array("m002_booking.booking_id" => "m002_booking_module.booking_id"));
        $this->db->where("module_id",$iData['module_id']);
        $this->db->where("user_id",$iData['user_id']);
        $this->db->where("paid_flg",1);
        //$this->db->where("server_ip",$iData['server']);
        $this->db->where("expire_from <= ",$iData['date']);
        $this->db->where("expire_to >= ",$iData['date']);
        $result = $this->selectOne();
        return $result;
    }

}