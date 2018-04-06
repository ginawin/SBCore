<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:38 AM
 */

class HotelPolicyBenefitModel extends Model
{
    public $table_name = "hotel_policy_benefit";
    public $primary_key = array("hotel_policy_benefit_id");
    public function __construct(){
        parent::__construct();
    }

    public function get($rq_data,$select="*"){
        $this->db->select($select);
        if (isset($rq_data['hotel_policy_benefit_id']) && !empty($rq_data['hotel_policy_benefit_id'])) {
            $this->db->{(is_array($rq_data['hotel_policy_benefit_id'])?"in":"where")}("hotel_policy_benefit_id",$rq_data['hotel_policy_benefit_id']);
        }
        if (isset($rq_data['begin_date']) && !empty($rq_data['begin_date'])) {
            $this->db->where('hotel_policy_benefit_begin_date >=', $rq_data['begin_date']);
        }
        if (isset($rq_data['end_date']) && !empty($rq_data['end_date'])) {
            $this->db->where('hotel_policy_benefit_end_date <=', $rq_data['end_date']);
        }
        if (isset($rq_data['hotel_id']) && !empty($rq_data['hotel_id'])) {
            $this->db->{(is_array($rq_data['hotel_id'])?"in":"where")}("hotel_id",$rq_data['hotel_id']);
        }
        if(isset($rq_data['hotel_policy_benefit_id']) && !empty($rq_data['hotel_policy_benefit_id'])){
            if(!is_array($rq_data['hotel_policy_benefit_id'])){
                return $this->selectOne();
            }
        }
        return $this->select();
    }
}