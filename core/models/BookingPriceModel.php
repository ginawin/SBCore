<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:36 AM
 */

class BookingPriceModel extends Model
{
    public $table_name = 'booking_price';
    public $foregin_key = 'booking_id';
    public $primary_key = array('booking_price_id');

    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = '', $select = '') {
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

    public function getForegin($id,$select = '*'){
        $this->db->select($select);
        $this->db->where($this->table_name.'.'.$this->foregin_key,$id);
        $this->db->orderby($this->primary_key, 'asc');
        $result = $this->select();
        return isset($result[0]) ? $result : false;
    }

    public function delete($id = '', $col = 'booking_price_id') {
        $this->db->{is_array($id) ? 'in' : 'where'}($col, $id);
        return count($this->db->delete($this->table_name));
    }
}