<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:35 AM
 */

class TourPriceModel extends Model
{
    public $table_name = "tour_price";
    public $primary_key = array("tour_price_id");
    public function __construct(){
        parent::__construct();
    }

    public function get_for_detail($rq_data,$select = "*"){
        $this->db->select($select);
        if(isset($rq_data['tour_id'])&&!empty($rq_data['tour_id'])){
            $this->db->where("tour_price.tour_id",$rq_data["tour_id"]);
        }
        if(isset($rq_data['date'])&&!empty($rq_data['date'])){
            $this->db->where('begin_date<=',$rq_data['date']);
            $this->db->where('end_date>=',$rq_data['date']);
        }

        if(isset($rq_data['car_type'])&&$rq_data['car_type']!=""){
            if(isset($rq_data['car_type'])&&$rq_data['car_type']==0){
                $this->db->where('tour_price.tour_price >0');
            }

            if(isset($rq_data['car_type'])&&$rq_data['car_type']==1){
                $this->db->where('tour_price.tour_price_prc >0');
            }
        }

        $this->db->join('tour_detail',array('tour_price.tour_price_id'=>'tour_detail.tour_price_id'),'','LEFT');
        $this->db->groupby('tour_price.tour_id');
        $this->db->where('(tour_price.tour_price <> 0 OR tour_price.tour_price_prc <> 0)');
        
        $result = $this->select();
        return $result;
    }
}