<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/10/2018
 * Time: 3:22 PM
 */

class ContactModel extends Model
{
    public $table_name = "contact";
    public $primary_key = array("contact_id");
    public function __construct(){
        parent::__construct();
    }
}