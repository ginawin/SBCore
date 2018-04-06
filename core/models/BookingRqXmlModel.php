<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:40 AM
 */

class BookingRqXmlModel extends Model
{
    public $table_name = 'booking_rq_xml';
    public $primary_key = array('booking_id');

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
        $this->db->orderby($this->table_name . '.' .$this->primary_key, 'desc');
        $result = $this->select();
        if ($id != '' && !is_array($id))
            return (isset($result)&&!empty($result))?$result[0]:false;

        return (isset($result)&&!empty($result))?$result:false;
    }

    public function delete($id = '', $col = 'booking_id') {
        $this->db->{is_array($id) ? 'in' : 'where'}($col, $id);
        return count($this->db->delete($this->table_name));
    }
}