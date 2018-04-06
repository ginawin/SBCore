<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:38 AM
 */

class HotelCancelationModel extends Model
{
    public $table_name = "hotel_cancelation";
    public $primary_key = array("hotel_cancel_id");
    public function __construct(){
        parent::__construct();
    }

    public function get($rq_data,$select = "*"){
        $this->db->join('hotel_policy_benefit',array('hotel_policy_benefit.hotel_policy_benefit_id'=>'hotel_cancelation.hotel_policy_benefit_id'));

        if(isset($rq_data['hotel_cancel_id']) && !empty($rq_data['hotel_cancel_id'])){
            $this->db->{(is_array($rq_data['hotel_cancel_id'])?"in":"where")}("hotel_cancel_id",$rq_data['hotel_cancel_id']);
        }
        if(isset($rq_data['hotel_id']) && !empty($rq_data['hotel_id'])){
            $this->db->{(is_array($rq_data['hotel_id'])?"in":"where")}($this->table_name.".hotel_id",$rq_data['hotel_id']);
        }
        if(isset($rq_data['checkin_date']) && !empty($rq_data['checkin_date'])){
            $this->db->where('hotel_policy_benefit_begin_date <= ',$rq_data['checkin_date']);
        }

        if(isset($rq_data['checkout_date']) && !empty($rq_data['checkout_date'])){
            $this->db->where('hotel_policy_benefit_end_date >= ',$rq_data['checkout_date']);
        }
        if(isset($rq_data['hotel_cancel_id']) && !empty($rq_data['hotel_cancel_id'])){
            if(!is_array($rq_data['hotel_cancel_id'])){
                return $this->selectOne();
            }
        }

        return $this->select();
    }
}