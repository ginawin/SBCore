<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 12:34 PM
 */

class ConfigurationModel extends Model
{
    public $table_name = "configuration";
    public $primary_key = array("configuration_id");
    public function __construct(){
        parent::__construct();
    }

    public function get($rq_data,$select = "*"){
        $this->db->select($select);
        if(isset($rq_data['configuration_id']) && !empty($rq_data['configuration_id'])){
            $this->db->{(is_array($rq_data['configuration_id'])?"in":"where")}("configuration_id",$rq_data['configuration_id']);
        }
        //if not array return 1 array
        if(isset($rq_data['configuration_id']) && !empty($rq_data['configuration_id'])){
            if(!is_array($rq_data['configuration_id'])){
                return $this->selectOne();
            }
        }
        return $this->select();
    }

    public function get_value_by_key($key){
        $this->select('configuration_value');
        $this->db->where('configuration_key',$key);
        $rs = $this->selectOne();
        if(isset($rs["configuration_value"])&&!empty($rs["configuration_value"])){
            return $rs["configuration_value"];
        }
        return false;
    }
}