<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:41 AM
 */

class BookingTbkModel extends Model
{
    public $table_name = 'booking_tbk';
    public $primary_key = array('booking_tbk_id');

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
        $this->db->orderby($this->table_name . '.' .$this->primary_key, 'desc');
        $result = $this->select();
        if ($id != '' && !is_array($id))
            return (isset($result)&&!empty($result))?$result[0]:false;

        return (isset($result)&&!empty($result))?$result:false;
    }
}