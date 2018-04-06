<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:40 AM
 */

class BookingContactModel extends Model
{
    public $table_name = 'contact_booking';
    public $primary_key = array('contact_booking_id');

    public function __construct()
    {
        parent::__construct();
    }

    function getRowWithCountry($country_id){
        $this->db->select('contact_booking.*')
            ->from('contact_booking');
        $this->db->where('contact_booking_country=',$country_id); //Booking
        $result = $this->select();
        return $result;
    }

    function getRowWithCity($city_id){
        $this->db->select('contact_booking.*')
            ->from('contact_booking');
        $this->db->where('contact_booking_place=',$city_id); //Booking
        $result = $this->selectOne();
        return $result ;
    }
}