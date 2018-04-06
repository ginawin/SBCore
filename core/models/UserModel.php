<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:35 AM
 */

class UserModel extends Model
{
    public $table_name = 'user';
    public $primary_key = array('user_id');
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

    public function getUserAPI($user_name,$user_pass,$select = "*"){
        $this->db->select($select);
        $this->db->where("user_name",addslashes($user_name));
        $this->db->where("user_pass",addslashes($user_pass));
        $this->db->orderby($this->table_name . '.user_id', 'desc');
        $result = $this->selectOne();
        return $result;
    }
}