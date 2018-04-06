<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:38 AM
 */

class HotelGaladinnerModel extends Model
{
    public $table_name = "hotel_galadinner";
    public $primary_key = array("hotel_galadinner_id");
    public function __construct(){
        parent::__construct();
    }

    public function get($rq_data,$select="*"){
        $this->db->select($select);
        if (isset($rq_data['hotel_galadinner_id']) && !empty($rq_data['hotel_galadinner_id'])) {
            $this->db->{(is_array($rq_data['hotel_galadinner_id'])?"in":"where")}("hotel_galadinner_id",$rq_data['hotel_galadinner_id']);
        }
        if (isset($rq_data['begin_date']) && !empty($rq_data['begin_date'])) {
            $this->db->where('hotel_galadinner_begin >=', $rq_data['begin_date']);
        }
        if (isset($rq_data['end_date']) && !empty($rq_data['end_date'])) {
            $this->db->where('hotel_galadinner_end <=', $rq_data['end_date']);
        }
        if (isset($rq_data['hotel_id']) && !empty($rq_data['hotel_id'])) {
            $this->db->{(is_array($rq_data['hotel_id'])?"in":"where")}("hotel_id",$rq_data['hotel_id']);
        }
        if(isset($rq_data['hotel_galadinner_id']) && !empty($rq_data['hotel_galadinner_id'])){
            if(!is_array($rq_data['hotel_galadinner_id'])){
                return $this->selectOne();
            }
        }
        return $this->select();
    }
}