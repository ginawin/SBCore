<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/10/2018
 * Time: 12:19 PM
 */

class OrdersModel extends Model
{
    public $table_name = "orders";
    public $primary_key = array("orders_id");
    public function __construct(){
        parent::__construct();
    }
}