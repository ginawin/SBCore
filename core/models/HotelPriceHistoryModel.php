<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 2:04 PM
 */

class HotelPriceHistoryModel extends Model
{
    public $table_name = "hotel_price_history";
    public $primary_key = array("hotel_price_history_id");
    public function __construct(){
        parent::__construct();
    }

    public function get_tariff($sess_search,$select = "*"){
        $this->db->select($select);
        $this->db->join('hotel',array('hotel.hotel_id'=>'hotel_price_history.hotel_id'));
        $this->db->{SBArray::valid($sess_search['hotel_price_id'])?"in":"where"}('hotel_price_id', $sess_search['hotel_price_id']);
        $this->db->where('status',1);
        if (strpos(strtoupper($sess_search['agent_code']), 'TBK') !== false) {
            $this->db->where("agent_id","94");
        }else{
            $this->db->where("agent_id","-1");
        }
        $result = $this->select();

        return $result;
    }
}