<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 12:34 PM
 */

class CurrencyModel extends Model
{
    public $table_name = "currency";
    public $primary_key = array("currency_code");
    public function __construct(){
        parent::__construct();
    }

    public function get($rq_data,$select = "*"){
        $this->db->select($select);
        if(isset($rq_data['currency_code']) && !empty($rq_data['currency_code'])){
            $this->db->{(is_array($rq_data['currency_code'])?"in":"where")}("currency_code",$rq_data['currency_code']);
        }
        //if not array return 1 array
        if(isset($rq_data['currency_code']) && !empty($rq_data['currency_code'])){
            if(!is_array($rq_data['currency_code'])){
                return $this->selectOne();
            }
        }
        return $this->select();
    }
}