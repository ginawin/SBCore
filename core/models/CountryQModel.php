<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 12:34 PM
 */

class CountryQModel extends Model
{
    public $table_name = 'country_q';
    public $primary_key = 'country_id';

    public function __construct(){
        parent::__construct();
    }

    public function get($id = '')
    {
        $this->db->select($this->table_name.'.*');

        if($id != '')
            $this->db->{is_array($id)?'in':'where'}($this->table_name.'.'.$this->primary_key, $id);


        $this->db->orderby($this->primary_key,'desc');

        $result = $this->db->get($this->table_name)->result_array(false);

        if($id != '' && !is_array($id))
            return $result[0];

        return $result;
    }
}