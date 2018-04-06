<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 11:05 AM
 */

class ShopBookingModuleModel extends Model1
{
    public $table_name = "m002_booking_module";
    public $primary_key = array("booking_module_id");
    public function __construct(){
        parent::__construct();
    }
}