<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:36 AM
 */

class BookingHotelModel extends Model
{
    public $table_name = 'booking_hotel';
    public $foregin_key = 'booking_id';
    public $primary_key = array('booking_hotel_id');

    public function __construct()
    {
        parent::__construct();
    }

    public function getForRefreshPrice($iData,$select = '*'){
        $this->db->orderby('booking_hotel_check_in_date','ASC');
        return $this->getList($iData,$select);
    }
}