<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:36 AM
 */

class BookingTourDetailsModel extends Model
{
    public $table_name = 'booking_tour_details';
    public $foregin_key = 'booking_tour_id';
    public $primary_key = array('booking_tour_details_id');

    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = '', $select = '')
    {
        if($select == '')
            $this->db->select($this->table_name . '.*');
        else
            $this->db->select($select);
        if ($id != '')
            $this->db->{is_array($id) ? 'in' : 'where'}($this->table_name . '.' . $this->primary_key, $id);
        $this->db->orderby($this->primary_key, 'desc');
        $result = $this->select();
        if ($id != '' && !is_array($id))
            return (isset($result)&&!empty($result))?$result[0]:false;

        return (isset($result)&&!empty($result))?$result:false;
    }

    public function getForegin($id,$select = '*')
    {
        $this->db->select($select);
        $this->db->{is_array($id) ? 'in' : 'where'}($this->table_name . '.' . $this->foregin_key, $id);
        $this->db->orderby($this->primary_key, 'asc');
        $result = $this->select();
        return isset($result[0]) ? $result : false;
    }

    public function getTourDetailWith($booking_tour_id,$flight_no,$place_code_from,$place_code_to,$tour_place_code){
        if(!empty($booking_tour_id))
            $this->db->where("booking_tour_id",$booking_tour_id);
        if(!empty($booking_tour_id))
            $this->db->where("flight_no",$flight_no);
        if(!empty($booking_tour_id))
            $this->db->where("place_code_from",$place_code_from);
        if(!empty($booking_tour_id))
            $this->db->where("place_code_to",$place_code_to);
        if(!empty($booking_tour_id))
            $this->db->where("tour_place_code",$tour_place_code);
        $result = $this->selectOne();
        return $result;
    }

    public function delete($id = '', $col = 'booking_tour_details_id')
    {
        $this->db->{is_array($id) ? 'in' : 'where'}($col, $id);
        return count($this->db->delete($this->table_name));
    }
}