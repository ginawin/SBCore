<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:39 AM
 */

class HotelPricePromotionModel extends Model
{
    public $table_name = "hotel_price_promotion";
    public $primary_key = array("hotel_price_promotion_id");
    public function __construct(){
        parent::__construct();
    }

    public function get($rq_data,$select = "*"){
        $this->db->select($select);
        if(isset($rq_data['hotel_price_promotion_id']) && !empty($rq_data['hotel_price_promotion_id'])){
            $this->db->{(is_array($rq_data['hotel_price_promotion_id'])?"in":"where")}("hotel_price_promotion_id",$rq_data['hotel_price_promotion_id']);
        }
        if(isset($rq_data['hotel_id']) && !empty($rq_data['hotel_id'])){
            $this->db->{(is_array($rq_data['hotel_id'])?"in":"where")}("hotel_id",$rq_data['hotel_id']);
        }
        if(isset($rq_data['checkin_date']) && !empty($rq_data['checkin_date'])){
            $this->db->where("DATE_FORMAT(FROM_UNIXTIME(begin_date+3601),'%Y/%m/%d') >= '".date('Y/m/d',$rq_data['checkin_date'])."'");
        }
        if(isset($rq_data['checkout_date']) && !empty($rq_data['checkout_date'])){
            $this->db->where("DATE_FORMAT(FROM_UNIXTIME(end_date+3601),'%Y/%m/%d') <= '".date('Y/m/d',$rq_data['checkout_date'])."'");
        }

        //if not array return 1 array
        if(isset($rq_data['hotel_rate_id']) && !empty($rq_data['hotel_rate_id'])){
            if(!is_array($rq_data['hotel_rate_id'])){
                return $this->selectOne();
            }
        }

        return $this->select();
    }

    public function get_tariff_full($rq_data,$select = "*"){
        $this->db->select($select);
        $this->db->join('hotel',array('hotel.hotel_id'=>'hotel_price_promotion.hotel_id'));
        if(isset($rq_data['hotel_price_promotion_id']) && !empty($rq_data['hotel_price_promotion_id'])){
            $this->db->{(is_array($rq_data['hotel_price_promotion_id'])?"in":"where")}("hotel_price_promotion_id",$rq_data['hotel_price_promotion_id']);
        }
        if(isset($rq_data['hotel_id']) && !empty($rq_data['hotel_id'])){
            $this->db->{(is_array($rq_data['hotel_id'])?"in":"where")}("hotel_price_promotion.hotel_id",$rq_data['hotel_id']);
        }
        if(isset($rq_data['checkin_date']) && !empty($rq_data['checkin_date'])){
            $this->db->where("DATE_FORMAT(FROM_UNIXTIME(begin_date+3601),'%Y/%m/%d') >= '".date('Y/m/d',$rq_data['checkin_date'])."'");
        }
        if(isset($rq_data['checkout_date']) && !empty($rq_data['checkout_date'])){
            $this->db->where("DATE_FORMAT(FROM_UNIXTIME(end_date+3601),'%Y/%m/%d') <= '".date('Y/m/d',$rq_data['checkout_date'])."'");
        }

        //if not array return 1 array
        if(isset($rq_data['hotel_price_promotion_id']) && !empty($rq_data['hotel_price_promotion_id'])){
            if(!is_array($rq_data['hotel_price_promotion_id'])){
                return $this->selectOne();
            }
        }

        return $this->select();
    }
}