<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:38 AM
 */

class HotelFileModel extends Model
{
    public $table_name = "hotel_file";
    public $primary_key = array("file_id");
    public function __construct(){
        parent::__construct();
    }

    public function get_for_list($rq_data,$select = "*"){
        $this->db->select($select);
        if(isset($rq_data['file_id']) && !empty($rq_data['file_id'])){
            $this->db->{(is_array($rq_data['file_id'])?"in":"where")}("file_id",$rq_data['file_id']);
        }
        if(isset($rq_data['hotel_id']) && !empty($rq_data['hotel_id'])){
            $this->db->{(is_array($rq_data['hotel_id'])?"in":"where")}("hotel_id",$rq_data['hotel_id']);
        }
        $this->db->groupby('hotel_id');
        $this->db->orderby('file_sort_order');

        //if not array return 1 array
        if(isset($rq_data['file_id']) && !empty($rq_data['file_id'])){
            if(!is_array($rq_data['file_id'])){
                return $this->selectOne();
            }
        }
        return $this->select();
    }

    public function get_for_detail($rq_data,$select = "*"){
        $this->db->select($select);
        if(isset($rq_data['file_id']) && !empty($rq_data['file_id'])){
            $this->db->{(is_array($rq_data['file_id'])?"in":"where")}("file_id",$rq_data['file_id']);
        }
        if(isset($rq_data['hotel_id']) && !empty($rq_data['hotel_id'])){
            $this->db->{(is_array($rq_data['hotel_id'])?"in":"where")}("hotel_id",$rq_data['hotel_id']);
        }
        $this->db->orderby('file_sort_order');

        //if not array return 1 array
        if(isset($rq_data['file_id']) && !empty($rq_data['file_id'])){
            if(!is_array($rq_data['file_id'])){
                return $this->selectOne();
            }
        }
        return $this->select();
    }
}