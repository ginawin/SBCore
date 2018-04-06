<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:33 AM
 */

class HotelModel extends Model
{
    public $table_name = "hotel";
    public $primary_key = array("hotel_id");
    public function __construct(){
        parent::__construct();
    }

    public function get($rq_data,$select = "*") {
        $this->db->select($select);
        if(isset($rq_data['hotel_id']) && !empty($rq_data['hotel_id'])){
            $this->db->{(is_array($rq_data['hotel_id'])?"in":"where")}("hotel_id",$rq_data['hotel_id']);
        }
        $this->db->where("hotel_status",1);

        if(isset($rq_data['hotel_id']) && !empty($rq_data['hotel_id'])){
            if(!is_array($rq_data['hotel_id'])){
                return $this->selectOne();
            }
        }
        return $this->select();
    }

    public function getTBK($rq_data,$select = "*") {
        $this->db->select($select);
        if(isset($rq_data['hotel_id']) && !empty($rq_data['hotel_id'])){
            $this->db->where($this->table_name . '.avion_real_code LIKE "%'.$rq_data['hotel_id'].',%"');
        }
        $this->db->where("hotel_status",1);

        if(isset($rq_data['hotel_id']) && !empty($rq_data['hotel_id'])){
            if(!is_array($rq_data['hotel_id'])){
                return $this->selectOne();
            }
        }
        return $this->select();
    }

    public function get_tariff_full($rq_data,$select = "*"){
        $this->db->select($select);

        if (isset($rq_data['search_star']) && !empty($rq_data['search_star'])) {
            $this->db->{(is_array($rq_data['search_star'])?"in":"where")}("hotel_star",$rq_data['search_star']);
        }
        if (isset($rq_data['search_price_fr']) && !empty($rq_data['search_price_fr'])) {
            $this->db->where('hotel_price_twb_ov >=', $rq_data['search_price_fr']);
        }

        if (isset($rq_data['search_price_to']) && !empty($rq_data['search_price_to'])) {
            $this->db->where('hotel_price_twb_ov <=', $rq_data['search_price_to']);
        }

        if (isset($rq_data['search_option']) && !empty($rq_data['search_option'])) {
            if ($rq_data['search_option'] == 5) {
                $this->db->where('((hotel_price_free_lco18 = 1) OR (hotel_price_free_lco18_2 = 1))');
            }
        }
        if (isset($rq_data['hotel_queue']) && !empty($rq_data['hotel_queue'])) {
            if($rq_data['hotel_queue'] != "All"){
                $this->db->where("hotel.hotel_queue IS NOT NULL");
                $this->db->where("hotel.hotel_queue",$rq_data['hotel_queue']);
            }
        }
        if (isset($rq_data['begin_date']) && !empty($rq_data['begin_date'])) {
            $this->db->where('hotel_price_begin_date >=', $rq_data['begin_date']);
        }
        if (isset($rq_data['end_date']) && !empty($rq_data['end_date'])) {
            $this->db->where('hotel_price_end_date <=', $rq_data['end_date']);
        }
        if (isset($rq_data['limit_period']) && !empty($rq_data['limit_period'])) {
            $this->db->where('hotel_price_end_date <=', $rq_data['limit_period']);
        }
        if (isset($rq_data['search_place']) && !empty($rq_data['search_place'])) {
            $this->db->{(is_array($rq_data['search_place'])?"in":"where")}("hotel.place_id",$rq_data['search_place']);
        }

        $this->db->where('hotel_price.check_show_period', 1);
        $this->db->where('hotel_status', 1);
        $this->db->join('hotel_price', array('hotel.hotel_id' => 'hotel_price.hotel_id'));
        if (isset($rq_data['search_tariff']) && $rq_data['search_tariff'] == 1) {
            $this->db->groupby('hotel.hotel_id');
            $this->db->groupby('hotel_price_begin_date');
        }
        $this->db->groupby("hotel_price.hotel_price_id");
        $this->db->orderby('hotel_star', 'ASC');
        $this->db->orderby('booking_room_most', 'DESC');
        $this->db->orderby('hotel_name', 'ASC');
        $this->db->orderby('hotel_price_begin_date', 'ASC');
        $this->db->orderby('hotel_price_twb_2n', 'ASC');

        return $this->select();
    }

    public function get_tariff_basic($rq_data,$select = "*"){
        $sql_where = "";
        $sql_order = "";

        if (isset($rq_data['search_star']) && !empty($rq_data['search_star'])) {
            if(is_array($rq_data['search_star'])){
                $sql_where .=' AND hotel_star IN (' . implode(",",$rq_data['search_star']).')';
            }else{
                $sql_where .=' AND hotel_star = ' . $rq_data['search_star'];
            }
        }

        if (isset($rq_data['search_price_fr']) && !empty($rq_data['search_price_fr'])) {
            $sql_where .=' AND hotel_price_twb_ov >= ' . $rq_data['search_price_fr'];
        }

        if (isset($rq_data['search_price_to']) && !empty($rq_data['search_price_to'])) {
            $sql_where .=' AND hotel_price_twb_ov <= ' . $rq_data['search_price_to'];
        }

        if (isset($rq_data['search_option']) && !empty($rq_data['search_option'])) {
            if ($rq_data['search_option'] == 5) {
                $sql_where .=' AND ((hotel_price_free_lco18 = 1) OR (hotel_price_free_lco18_2 = 1))';
            }
        }

        if (isset($rq_data['hotel_queue']) && !empty($rq_data['hotel_queue'])) {
            if($rq_data['hotel_queue'] != "All"){
                $sql_where .= " AND hotel.hotel_queue IS NOT NULL";
                $sql_where .= " AND hotel.hotel_queue = '".$rq_data['hotel_queue']."'";
            }
        }
        if (isset($rq_data['begin_date']) && !empty($rq_data['begin_date'])) {
            $sql_where .=' AND hotel_price.hotel_price_begin_date >= '.($rq_data['begin_date']);
        }
        if (isset($rq_data['end_date']) && !empty($rq_data['end_date'])) {
            $sql_where .=' AND hotel_price.hotel_price_end_date <= '.($rq_data['end_date']);
        }
        if (isset($rq_data['limit_period']) && !empty($rq_data['limit_period'])) {
            $sql_where .=' AND hotel_price.hotel_price_end_date <= '.($rq_data['limit_period']);
        }
        if (isset($rq_data['search_place']) && !empty($rq_data['search_place'])) {
            if(is_array($rq_data['search_place'])){
                $sql_where .=' AND hotel.place_id IN (' . implode(",",$rq_data['search_place']).')';
            }else{
                $sql_where .=' AND hotel.place_id = ' . $rq_data['search_place'];
            }
        }

        //if ($this->sess_admin['level'] == 4) {
        $sql_where .=' AND hotel_price.check_show_period = 1';
        //}
        $sql_where .=' AND hotel_status = 1';
        $sql_where .=' AND `hotel_price_bbs_id` IS NULL';

        $sql_where = trim($sql_where," AND ");

        // Xếp giá tăng dần
        if (isset($rq_data['search_tariff']) && $rq_data['search_tariff'] == 8) {
            $sql_order = '`hotel_price_twb_ov` ASC,';
        }

        $sql = "SELECT " . $select . "
        FROM (`hotel`)
        JOIN hotel_price ON hotel.hotel_id = hotel_price.hotel_id
        JOIN (SELECT MIN(hotel_price_twb_2n) AS `min_price`,hotel.hotel_id FROM hotel_price INNER JOIN hotel ON hotel.hotel_id = hotel_price.hotel_id 
        WHERE ".$sql_where . "
        GROUP BY `hotel`.`hotel_id`, `hotel_price_begin_date`) AS min_table ON hotel.hotel_id = min_table.hotel_id" .
            //" JOIN `hotel_policy_benefit` ON (`hotel`.`hotel_id` = `hotel_policy_benefit`.`hotel_id`)
            " WHERE ". $sql_where . " AND hotel_price_twb_2n = min_price
        GROUP BY `hotel`.`hotel_id`, `hotel_price_begin_date`
        ORDER BY " . $sql_order . "`hotel_star` ASC,booking_room_most DESC,hotel_name ASC, `hotel_price_begin_date` ASC, `hotel_price_twb_2n` ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_tariff_policy_benefit($rq_data,$select = "*"){
        $this->db->select($select);

        $this->db->join('hotel_policy_benefit', array('hotel.hotel_id' => 'hotel_policy_benefit.hotel_id'));

        if (isset($rq_data['search_star']) && !empty($rq_data['search_star'])) {
            $this->db->{(is_array($rq_data['search_star'])?"in":"where")}("hotel_star",$rq_data['search_star']);
        }

        switch ($rq_data['key']) {
            case 1:
                $this->db->where('(hotel_price_promotion_jp <> "")');
                break;
            case 2:
                $this->db->where('(hotel_price_benefit_jp <> "")');
                break;
            case 3:
                $this->db->where('((hotel_price_cancel_policy <> "") OR (hotel_price_cancel_policy <> ""))');
                break;
        }
        if (isset($rq_data['hotel_queue']) && !empty($rq_data['hotel_queue'])) {
            if($rq_data['hotel_queue'] != "All"){
                $this->db->where("hotel.hotel_queue IS NOT NULL");
                $this->db->where("hotel.hotel_queue",$rq_data['hotel_queue']);
            }
        }

        if (isset($rq_data['begin_date']) && !empty($rq_data['begin_date'])) {
            $this->db->where('hotel_policy_benefit_begin_date >=', $rq_data['begin_date']);
        }

        if (isset($rq_data['end_date']) && !empty($rq_data['end_date'])) {
            $this->db->where('hotel_policy_benefit_end_date <=', $rq_data['end_date']);
        }

        if (isset($rq_data['limit_period']) && !empty($rq_data['limit_period'])) {
            $this->db->where('hotel_policy_benefit_end_date <=', $rq_data['limit_period']);
        }

        if (isset($rq_data['search_place']) && !empty($rq_data['search_place'])) {
            $this->db->{(is_array($rq_data['search_place'])?"in":"where")}("hotel.place_id",$rq_data['search_place']);
        }

        //if ($this->sess_admin['level'] == 4) {
        //$this->db->where('hotel_price.check_show_period', 1);
        //}
        $this->db->where('hotel_status', 1);

        if (isset($rq_data['order_rate']) && !empty($rq_data['order_rate'])) {
            $this->db->orderby('booking_room_most', 'DESC');
        }
        $this->db->orderby('hotel_star', 'ASC');
        $this->db->orderby('hotel_policy_benefit_begin_date', 'ASC');

        return $this->select();
    }

    public function get_tariff_galadinner($rq_data,$select = "*"){
        $this->db->select($select);

        $this->db->join('hotel_galadinner', array('hotel.hotel_id' => 'hotel_galadinner.hotel_id'));

        if (isset($rq_data['search_star']) && !empty($rq_data['search_star'])) {
            $this->db->{(is_array($rq_data['search_star'])?"in":"where")}("hotel_star",$rq_data['search_star']);
        }
        if (isset($rq_data['hotel_queue']) && !empty($rq_data['hotel_queue'])) {
            if($rq_data['hotel_queue'] != "All"){
                $this->db->where("hotel.hotel_queue IS NOT NULL");
                $this->db->where("hotel.hotel_queue",$rq_data['hotel_queue']);
            }
        }
        if (isset($rq_data['begin_date']) && !empty($rq_data['begin_date'])) {
            $this->db->where('hotel_galadinner_begin >=', $rq_data['begin_date']);
        }
        if (isset($rq_data['end_date']) && !empty($rq_data['end_date'])) {
            $this->db->where('hotel_galadinner_end <=', $rq_data['end_date']);
        }
        if (isset($rq_data['limit_period']) && !empty($rq_data['limit_period'])) {
            $this->db->where('hotel_galadinner_end <=', $rq_data['limit_period']);
        }
        if (isset($rq_data['search_place']) && !empty($rq_data['search_place'])) {
            $this->db->{(is_array($rq_data['search_place'])?"in":"where")}("hotel.place_id",$rq_data['search_place']);
        }

        $this->db->where('hotel_status', 1);
        $this->db->orderby('hotel_star', 'ASC');
        $this->db->orderby('hotel_name', 'ASC');

        return $this->select();
    }
    
    
    
}