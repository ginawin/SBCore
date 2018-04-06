<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:40 AM
 */

class BookingHotelToModel extends Model
{
    public $table_name = 'booking_hotel_to';
    public $primary_key = array('booking_hotel_to_id');

    public function __construct(){
        parent::__construct();
    }
}