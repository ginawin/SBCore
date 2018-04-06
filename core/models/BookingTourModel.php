<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:36 AM
 */

class BookingTourModel extends Model
{
    public $table_name = 'booking_tour';
    public $foregin_key = 'booking_id';
    public $primary_key = array('booking_tour_id');

    public function __construct()
    {
        parent::__construct();
    }
    
    public function getForRefreshPrice($iData,$select = '*'){
        $this->db->orderby('booking_tour_date','ASC');
        return $this->getList($iData,$select);
    }

    /*public function getTourDate($booking_id,$date){
        $this->db->where('FROM_UNIXTIME(booking_tour_date+3601,"%Y/%m/%d")',$date);
        $this->db->where('booking_id',$booking_id);
        $result = $this->selectOne();
        return $result;
    }*/
    
}