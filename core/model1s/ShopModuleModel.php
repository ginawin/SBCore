<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 11:05 AM
 */

class ShopModuleModel extends Model1
{
    public $table_name = "m001_module";
    public $primary_key = array("module_id");
    public function __construct(){
        parent::__construct();
    }
}