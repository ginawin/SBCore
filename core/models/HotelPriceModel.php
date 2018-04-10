<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:33 AM
 */

class HotelPriceModel extends Model
{
    public $table_name = "hotel_price";
    public $primary_key = array("hotel_price_id");
    public function __construct(){
        parent::__construct();
    }

    //for hotel list API
    public function get_for_list($rq_data , $select = "*"){

        $where = ' AND hotel_status = 1';

        if(isset($rq_data['city_cd'])&&!empty($rq_data['city_cd'])){
            $where .=' AND place_iso_code = "'.$rq_data['city_cd'].'"';
            //$this->db->where('place_iso_code',$rq_data['city_cd']);
        }

        if(isset($rq_data['checkin_date'])&&!empty($rq_data['checkin_date'])&&isset($rq_data['checkout_date'])&&!empty($rq_data['checkout_date'])){

            $where .=' AND (
            (hotel_price.hotel_price_begin_date<='.$rq_data['checkin_date'].' AND hotel_price.hotel_price_end_date>='.$rq_data['checkin_date'].')
            OR
            (hotel_price.hotel_price_begin_date<='.$rq_data['checkout_date'].' AND hotel_price.hotel_price_end_date>='.$rq_data['checkout_date'].')
            OR
            (hotel_price.hotel_price_begin_date>='.$rq_data['checkin_date'].' AND hotel_price.hotel_price_end_date<='.$rq_data['checkout_date'].')
            )';

            /*$this->db->where('(
            (hotel_price.hotel_price_begin_date<='.$rq_data['checkin_date'].' AND hotel_price.hotel_price_end_date>='.$rq_data['checkin_date'].')
            OR
            (hotel_price.hotel_price_begin_date<='.$rq_data['checkout_date'].' AND hotel_price.hotel_price_end_date>='.$rq_data['checkout_date'].')
            OR
            (hotel_price.hotel_price_begin_date>='.$rq_data['checkin_date'].' AND hotel_price.hotel_price_end_date<='.$rq_data['checkout_date'].')
            )');*/
        }

        if(isset($rq_data['star'])&&!empty($rq_data['star'])){
            $arr_star = explode(',',trim($rq_data['star'],','));
            $str_star = "'".implode("','",$arr_star)."'";
            $where = ' AND hotel.hotel_star IN('.$str_star.')';
            //$this->db->in('hotel.hotel_star',$arr_star);
        }

        if(isset($rq_data['hotel_cd'])&&!empty($rq_data['hotel_cd'])){
            $arr_hotel_cd = explode(',',trim($rq_data['hotel_cd'], ","));
            $str_hotel_cd = "'".implode("','",$arr_hotel_cd)."'";
            $where = ' AND hotel.hotel_id IN('.$str_hotel_cd.')';
            //$this->db->in('hotel.hotel_id',$arr_hotel_cd);
        }

        if(isset($rq_data['keyword'])&&!empty($rq_data['keyword'])){
            $where .=' AND ((LCASE(hotel.hotel_name) LIKE "%' . mb_strtolower(trim($rq_data['keyword'])) . '%") OR (LCASE(hotel.hotel_name_jp) LIKE "%' . mb_strtolower(trim($rq_data['keyword'])) . '%"))';
            //$this->db->where('((LCASE(hotel.hotel_name) LIKE "%' . mb_strtolower(trim($rq_data['keyword'])) . '%") OR (LCASE(hotel.hotel_name_jp) LIKE "%' . mb_strtolower(trim($rq_data['keyword'])) . '%"))');
        }

        if(isset($rq_data['price_fr'])&&!empty($rq_data['price_fr'])){
            $where .=' AND `hotel_price`.`hotel_price_sgl_ov` >= '.$rq_data['price_fr'];
            //$this->db->where('hotel_price.hotel_price_sgl_ov >='.$rq_data['price_fr']);
        }

        if(isset($rq_data['price_to'])&&!empty($rq_data['price_to'])){
            $where .=' AND `hotel_price`.`hotel_price_sgl_ov` <= '.$rq_data['price_to'];
            //$this->db->where('hotel_price.hotel_price_sgl_ov <='.$rq_data['price_to']);
        }

        if(isset($rq_data['curr_cd'])&&!empty($rq_data['curr_cd'])){

        }

        if(isset($rq_data['promotion_flg'])&&$rq_data['promotion_flg']==1){
            $where .= ' AND ((hotel_policy_benefit.hotel_price_promotion IS NOT NULL AND hotel_policy_benefit.hotel_price_promotion <> "") OR 
                            (hotel_policy_benefit.hotel_price_promotion_jp IS NOT NULL AND hotel_policy_benefit.hotel_price_promotion_jp <> "") OR
                            (hotel_policy_benefit.hotel_price_benefit IS NOT NULL AND hotel_policy_benefit.hotel_price_benefit <> "") OR
                            (hotel_policy_benefit.hotel_price_benefit_jp IS NOT NULL AND hotel_policy_benefit.hotel_price_benefit_jp <> ""))';

            /*$this->db->where('((hotel_policy_benefit.hotel_price_promotion IS NOT NULL AND hotel_policy_benefit.hotel_price_promotion <> "") OR
                            (hotel_policy_benefit.hotel_price_promotion_jp IS NOT NULL AND hotel_policy_benefit.hotel_price_promotion_jp <> "") OR
                            (hotel_policy_benefit.hotel_price_benefit IS NOT NULL AND hotel_policy_benefit.hotel_price_benefit <> "") OR
                            (hotel_policy_benefit.hotel_price_benefit_jp IS NOT NULL AND hotel_policy_benefit.hotel_price_benefit_jp <> ""))');*/
        }

        if(isset($rq_data['cancel_flg'])&&$rq_data['cancel_flg']==1){
            $where .= ' AND ((hotel_policy_benefit.hotel_price_cancel_policy IS NOT NULL AND hotel_policy_benefit.hotel_price_cancel_policy <> "") OR 
                            (hotel_policy_benefit.hotel_price_cancel_policy_jp IS NOT NULL AND hotel_policy_benefit.hotel_price_cancel_policy_jp <> "") OR
                            (hotel_policy_benefit.hotel_price_git_cancel_policy IS NOT NULL AND hotel_policy_benefit.hotel_price_git_cancel_policy <> "") OR
                            (hotel_policy_benefit.hotel_price_git_cancel_policy_jp IS NOT NULL AND hotel_policy_benefit.hotel_price_git_cancel_policy_jp <> ""))';

            /*$this->db->where('((hotel_policy_benefit.hotel_price_cancel_policy IS NOT NULL AND hotel_policy_benefit.hotel_price_cancel_policy <> "") OR
                            (hotel_policy_benefit.hotel_price_cancel_policy_jp IS NOT NULL AND hotel_policy_benefit.hotel_price_cancel_policy_jp <> "") OR
                            (hotel_policy_benefit.hotel_price_git_cancel_policy IS NOT NULL AND hotel_policy_benefit.hotel_price_git_cancel_policy <> "") OR
                            (hotel_policy_benefit.hotel_price_git_cancel_policy_jp IS NOT NULL AND hotel_policy_benefit.hotel_price_git_cancel_policy_jp <> ""))');*/
        }

        if(isset($rq_data['rq_rooms'])&&!empty($rq_data['rq_rooms'])){
            foreach($rq_data['rq_rooms'] as $rq_room){
                if (isset($rq_room['adult_num'])) {
                    if($rq_room['adult_num']==1){
                        //$this->db->where('(hotel_price.hotel_price_sgl_ov > 0 AND hotel_price.hotel_price_sgl_ov IS NOT NULL)');
                        $where .= ' AND (hotel_price.hotel_price_sgl_ov > 0 AND hotel_price.hotel_price_sgl_ov IS NOT NULL)';
                    }
                    if($rq_room['adult_num']==2){
                        //$this->db->where('(hotel_price.hotel_price_twb_ov > 0 AND hotel_price.hotel_price_twb_ov IS NOT NULL)');
                        $where .= ' AND (hotel_price.hotel_price_twb_ov > 0 AND hotel_price.hotel_price_twb_ov IS NOT NULL)';
                    }
                    if($rq_room['adult_num']==3){
                        //$this->db->where('(hotel_price.hotel_price_trp_ov > 0 AND hotel_price.hotel_price_trp_ov IS NOT NULL)');
                        $where .= ' AND (hotel_price.hotel_price_trp_ov > 0 AND hotel_price.hotel_price_trp_ov IS NOT NULL)';
                    }
                }
            }
        }

        $limit = '';
        if(isset($rq_data['limit'])&&!empty($rq_data['limit'])&&isset($rq_data['offset'])){
            if($rq_data['limit']!=-1){
                $limit = $rq_data['offset'].','.$rq_data['limit'];
            }
            //$this->db->limit($rq_data['limit'],$rq_data['offset']);
        }
        $order = '';
        if(isset($rq_data['order_by'])&&!empty($rq_data['order_by'])&&isset($rq_data['order_type'])&&!empty($rq_data['order_type'])){
            $order_by = '`hotel_price`.`hotel_price_sgl_ov`';
            switch(strtoupper($rq_data['order_by'])){
                case 'PRICE':
                    $order_by = '`hotel_price`.`hotel_price_sgl_ov`';
                    //$this->db->orderby('hotel_price.hotel_price_sgl_ov');
                    break;
                case 'NAME':
                    $order_by = '`hotel`.`hotel_name`';
                    //$this->db->orderby('hotel.hotel_name');
                    break;
                case 'STAR':
                    $order_by = '`hotel`.`hotel_star`';
                    //$this->db->orderby('hotel.hotel_star');
                    break;
            }
            $order = $order_by.' '.$rq_data['order_type'];
        }


        //get hotel with min category
        $select1 = 'SELECT MIN(hotel_price.hotel_price_twb_ov) AS min_price,hotel.hotel_id';


        $from1 = $from = ' FROM hotel INNER JOIN `hotel_price` ON (hotel.hotel_id = `hotel_price`.`hotel_id`)';
        //only main query
        $left_join = ' LEFT JOIN `hotel_policy_benefit` ON ((hotel_price.hotel_id = `hotel_policy_benefit`.`hotel_id`) AND (		
			(hotel_policy_benefit.hotel_policy_benefit_begin_date<='.$rq_data['checkin_date'].' AND hotel_policy_benefit.hotel_policy_benefit_end_date>='.$rq_data['checkin_date'].')
			OR
			(hotel_policy_benefit.hotel_policy_benefit_begin_date<='.$rq_data['checkout_date'].' AND hotel_policy_benefit.hotel_policy_benefit_end_date>='.$rq_data['checkout_date'].')
			OR
			(hotel_policy_benefit.hotel_policy_benefit_begin_date>='.$rq_data['checkin_date'].' AND hotel_policy_benefit.hotel_policy_benefit_end_date<='.$rq_data['checkout_date'].')
		))';

        $where1='';$group1='';$order1='';$limit1='';
        if(!empty($where))
            $where1 = $where = ' WHERE '.trim($where,' AND ');
        //if(!empty($group))
        $group1 = $group = ' GROUP BY hotel.hotel_id';
        if(!empty($order))
            $order1 = $order = ' ORDER BY '.$order;
        if (!empty($limit))
            $limit1 = $limit = ' LIMIT '.$limit;

        //get min price AS table T
        $sub_query = "(".$select1.$from1.$where1.$group1.$order1.") AS T ";
        //join T and hotel_policy_benefit
        $from = $from." INNER JOIN ".$sub_query. " ON T.hotel_id = hotel.hotel_id AND T.min_price = hotel_price.hotel_price_twb_ov ".$left_join;
        $query = $select.$from.$where.$group.$order.$limit;
        $mHotel = $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
        return $mHotel;
    }

    //for hotel detail API
    public function get_for_detail($rq_data , $select = "*"){

        $this->db->select($select);

        if(isset($rq_data['checkin_date'])&&!empty($rq_data['checkin_date'])&&isset($rq_data['checkout_date'])&&!empty($rq_data['checkout_date'])){
            $this->db->where('(
            (hotel_price.hotel_price_begin_date<='.$rq_data['checkin_date'].' AND hotel_price.hotel_price_end_date>='.$rq_data['checkin_date'].')
            OR
            (hotel_price.hotel_price_begin_date<='.$rq_data['checkout_date'].' AND hotel_price.hotel_price_end_date>='.$rq_data['checkout_date'].')
            OR
            (hotel_price.hotel_price_begin_date>='.$rq_data['checkin_date'].' AND hotel_price.hotel_price_end_date<='.$rq_data['checkout_date'].')
            )');
        }

        if(isset($rq_data['category'])&&!empty($rq_data['category'])){
            if($rq_data["tbk_flg"] && trim($rq_data['category'])=="部屋指定無"){
                $this->db->orderby("hotel_price_sgl","ASC");
                $this->db->limit(1);
            }else{
                $this->db->where('LCASE(hotel_price_category) = "'.strtolower($rq_data['category']).'"');
            }
        }

        if(isset($rq_data['rq_rooms'])&&!empty($rq_data['rq_rooms'])){
            $cus_num = 0;
            foreach($rq_data['rq_rooms'] as $rq_room){
                $cus_num+=$rq_room['adult_num'];
            }
        }

        $this->db->where('hotel_price.hotel_id',$rq_data['hotel_id']);
        $this->db->orderby('hotel_price.hotel_price_begin_date','ASC');
        $this->db->orderby('hotel_price_sgl_ov','ASC');

        $this->db->join('hotel_policy_benefit','(hotel_price.hotel_id = `hotel_policy_benefit`.`hotel_id`) AND (		
            (hotel_policy_benefit.hotel_policy_benefit_begin_date<='.$rq_data['checkin_date'].' AND hotel_policy_benefit.hotel_policy_benefit_end_date>='.$rq_data['checkin_date'].')
            OR
            (hotel_policy_benefit.hotel_policy_benefit_begin_date<='.$rq_data['checkout_date'].' AND hotel_policy_benefit.hotel_policy_benefit_end_date>='.$rq_data['checkout_date'].')
            OR
            (hotel_policy_benefit.hotel_policy_benefit_begin_date>='.$rq_data['checkin_date'].' AND hotel_policy_benefit.hotel_policy_benefit_end_date<='.$rq_data['checkout_date'].')
        )','','LEFT');

        $this->db->where('hotel_price_date_of_week LIKE "%'.date('N',$rq_data['checkin_date']).'%"');
        $this->db->groupby('hotel_price_category,hotel_price.hotel_price_begin_date,hotel_price.hotel_price_end_date,hotel_price_date_of_week');

        return $this->select();
    }
}