<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:35 AM
 */

class TourModel extends Model
{
    public $table_name = "tour";
    public $primary_key = array("tour_id");
    public function __construct(){
        parent::__construct();
    }

    public function get_for_list($rq_data,$select = "*"){
        $this->db->select($select);
        if(isset($rq_data['city_cd'])&&!empty($rq_data['city_cd'])){
            $this->db->where('place_code',$rq_data['city_cd']);
        }

        if(isset($rq_data['id'])&&!empty($rq_data['id'])){
            $this->db->where('tour.tour_id IN ('.$rq_data['id'].')');
        }

        if(isset($rq_data['date'])&&!empty($rq_data['date'])){
            $this->db->where('begin_date<=',$rq_data['date']);
            $this->db->where('end_date>=',$rq_data['date']);
        }

        if(isset($rq_data['keyword'])&&!empty($rq_data['keyword'])){
            $this->db->where('('
                . '(LCASE(tour_price.tour_price_name) LIKE "%' . mb_strtolower(trim($rq_data['keyword'])) . '%") '
                . 'OR '
                . '(LCASE(tour_price.tour_price_name_en) LIKE "%' . mb_strtolower(trim($rq_data['keyword'])) . '%")'
                . 'OR '
                . '(LCASE(tour_detail_summary) LIKE "%' . mb_strtolower(trim($rq_data['keyword'])) . '%")'
                . 'OR '
                . '(LCASE(tour_detail_summary_jp) LIKE "%' . mb_strtolower(trim($rq_data['keyword'])) . '%")'
                . 'OR '
                . '(LCASE(tour_detail_description) LIKE "%' . mb_strtolower(trim($rq_data['keyword'])) . '%")'
                . 'OR '
                . '(LCASE(tour_detail_description_jp) LIKE "%' . mb_strtolower(trim($rq_data['keyword'])) . '%")'
                . ')'
            );
        }

        if(isset($rq_data['tour_cd'])&&!empty($rq_data['tour_cd'])){
            $arr_tour_cd = explode(',',trim($rq_data['tour_cd'], ","));
            $this->db->in('tour.tour_code',$arr_tour_cd);
        }

        if(isset($rq_data['price_fr'])&&!empty($rq_data['price_fr'])){
            if(isset($rq_data['car_type'])&&$rq_data['car_type']==0){
                $this->db->where('tour_price.tour_price >=',$rq_data['price_fr']);
            }else{
                $this->db->where('tour_price.tour_price_prc >=',$rq_data['price_fr']);
            }
        }

        if(isset($rq_data['price_to'])&&!empty($rq_data['price_to'])){
            if(isset($rq_data['car_type'])&&$rq_data['car_type']==0){
                $this->db->where('tour_price.tour_price <=',$rq_data['price_to']);
            }else{
                $this->db->where('tour_price.tour_price_prc <=',$rq_data['price_to']);
            }
        }

        if(isset($rq_data['car_type'])&&$rq_data['car_type']==0){
            $this->db->where('tour_price.tour_price >0');
        }

        if(isset($rq_data['car_type'])&&$rq_data['car_type']==1){
            $this->db->where('tour_price.tour_price_prc >0');
        }

        if(isset($rq_data['limit'])&&!empty($rq_data['limit'])&&isset($rq_data['offset'])){
            if($rq_data['limit']!=-1){
                $this->db->limit($rq_data['limit'],$rq_data['offset']);
            }
        }

        $order_by = '';
        if(isset($rq_data['order_by'])&&!empty($rq_data['order_by'])&&isset($rq_data['order_type'])&&!empty($rq_data['order_type'])){
            switch(strtoupper($rq_data['order_by'])){
                case 'PRICE':
                    $order_by = 'tour_price.tour_price';
                    break;
                case 'NAME':
                    $order_by = 'tour_price.tour_price_name';
                    break;
            }
            $this->db->orderby($order_by,$rq_data['order_type']);
        }

        $this->db->where('tour_status',1);
        $this->db->join('tour_price',array('tour.tour_id'=>'tour_price.tour_id'));
        $this->db->join('tour_detail',array('tour_price.tour_price_id'=>'tour_detail.tour_price_id'),'','LEFT');
        $this->db->groupby('tour.tour_id');
        $this->db->where('(tour_price.tour_price <> 0 OR tour_price.tour_price_prc <> 0)');
        $this->db->where('(tour_kamiki_flg = 1 OR tour_shimoki_flg = 1 OR tour_option_tariff = 1)');

        return $this->select();
    }

    public function get_for_detail($rq_data,$select = "*"){
        $this->db->select($select);
        if(isset($rq_data['tour_cd'])&&!empty($rq_data['tour_cd'])){
            if(strpos($rq_data['tour_cd'],"@@FREETIME")===false){
                $this->db->where('(tour_kamiki_flg = 1 OR tour_shimoki_flg = 1 OR tour_option_tariff = 1)');
            }
            $this->db->where('tour.tour_code',$rq_data['tour_cd']);
        }
        $this->db->where('tour_status',1);

        return $this->selectOne();
    }

    public function get_tour_price($rq_data,$select = "*"){
        $this->db->select($select);
        $this->db->join('tour_price', 'tour.tour_id', 'tour_price.tour_id');
        if(isset($rq_data['tour_code']) && !empty($rq_data['tour_code'])){
            $this->db->where('tour.tour_code', $rq_data['tour_code']);
        }
        if(isset($rq_data['checkin_date']) && !empty($rq_data['checkin_date'])){
            $this->db->where('tour_price.begin_date <= ', $rq_data['checkin_date']);
            $this->db->where('tour_price.end_date >= ', $rq_data['checkin_date']);
        }
        return $this->selectOne();
    }

    public function get_trf_price($rq_data,$select = "*"){
        $this->db->select($select);
        $this->db->join('tour_price',array('tour.tour_id'=>'tour_price.tour_id'));
        $this->db->where('tour_status',1);
        if(isset($rq_data['meal_flg']) && $rq_data['meal_flg'] != 1){
            $this->db->where('tour.tour_code LIKE "%STF"');
        }else{
            $this->db->where('tour.tour_code LIKE "%TRF"');
        }
        if(isset($rq_data['checkin_date']) && !empty($rq_data['checkin_date'])){
            $this->db->where('tour_price.begin_date <= ', $rq_data['checkin_date']);
            $this->db->where('tour_price.end_date >= ', $rq_data['checkin_date']);
        }
        if(isset($rq_data['city_cd']) && !empty($rq_data['city_cd'])){
            $this->db->where('place_code',$rq_data['city_cd']);
        }
        return $this->select();
    }

    public function get_tariff_trf_price($rq_data,$select = "*"){
        $this->db->select($select);
        $this->db->join('tour_price', array('tour.tour_id' => 'tour_price.tour_id'));
        if(isset($rq_data['meal_flg']) && $rq_data['meal_flg'] != 1){
            $this->db->where('tour.tour_code LIKE "%STF"');
        }else{
            $this->db->where('tour.tour_code LIKE "%TRF"');
        }
        if (isset($rq_data['begin_date']) && !empty($rq_data['begin_date'])) {
            $this->db->where('begin_date >=', $rq_data['begin_date']);
        }
        if (isset($rq_data['end_date']) && !empty($rq_data['end_date'])) {
            $this->db->where('end_date <=', $rq_data['end_date']);
        }
        if (isset($rq_data['search_place']) && !empty($rq_data['search_place'])) {
            $this->db->{(is_array($rq_data['search_place'])?"in":"where")}("place_id",$rq_data['search_place']);
        }
        if(isset($rq_data['search_place']) && !empty($rq_data['search_place'])){
            if(!is_array($rq_data['search_place'])){
                return $this->select();
            }
        }
        return $this->select();
    }

    public function get_tariff($rq_data,$select = "*"){
        $this->db->select($select);
        $this->db->join('tour_price', array('tour.tour_id' => 'tour_price.tour_id'));
        $this->db->join('tour_detail',array('tour_price.tour_price_id'=>'tour_detail.tour_price_id'),"","LEFT");
        if (isset($rq_data["search_season"]) && $rq_data["search_season"] == 0) {
            $this->db->where('tour_kamiki_flg', 1);
        } else {
            $this->db->where('tour_shimoki_flg', 1);
        }
        if (isset($rq_data['begin_date']) && !empty($rq_data['begin_date'])) {
            $this->db->where('begin_date >=', $rq_data['begin_date']);
        }
        if (isset($rq_data['end_date']) && !empty($rq_data['end_date'])) {
            $this->db->where('end_date <=', $rq_data['end_date']);
        }
        if($rq_data['search_tariff']==1){
            $this->db->where('tour_option_simple_tariff',1);
        }
        if (isset($rq_data['search_place']) && !empty($rq_data['search_place'])) {
            $this->db->{(is_array($rq_data['search_place'])?"in":"where")}("tour.place_id",$rq_data['search_place']);
        }
        $this->db->where('(tour_price.tour_price <> 0 OR tour_price.tour_price_prc <> 0 OR tour_price.tour_price_inter <> 0)');
        $this->db->where('tour_status', 1);
        $this->db->orderby('tour.tour_code', 'ASC');
        return $this->select();
    }
}