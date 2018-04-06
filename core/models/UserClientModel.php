<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:35 AM
 */

class UserClientModel extends Model
{
    public $table_name = "user_client";
    public $primary_key = array("user_id");
    public function __construct(){
        parent::__construct();
    }

    public function get($rq_data,$select = "*"){
        $this->db->select($select);
        $result = $this->select();
        return $result;
    }
    public function login($email)
    {
        $this->db->where('user_email',$email);
        $data =  $this->selectOne();
        return $data;
    }
    public function register($data)
    {
        $status = '0';
        $password ='';
        $email    = addslashes($data['user_email']);
        $username = addslashes($data['user_name']);
        $token    = addslashes($data['user_token']);
        if (!empty($data['user_pass'])) {
            $password = Password::hash($data['user_pass']);
        }
        $phone    = $data['user_phone'];
        $type     = addslashes($data['user_type']);
        switch ($type) {
            case 'NM':
                $data = array(
                    'user_email' => $email,
                    'user_name'  => $username,
                    'user_pass'  => $password,
                    'user_phone' => $phone,
                    'user_type'  => $type
                );
                break;
            case 'FB':
                $data = array(
                    'user_email' => $email,
                    'user_name'  => $username,
                    'user_type'  => $type,
                );
                break;
            case 'GM':
                $data = array(
                    'user_email' => $email,
                    'user_name'  => $username,
                    'user_type'  => $type
                );
                break;
            default:
                throw new Exception("Type user not found", 1);
                break;
        }
        $status = $this->insertOne($data);
        return $status;

    }
    public function isExists($email, $type = '')
    {
        $result = 0;
        $this->db->where('user_email', $email);
        if (isset($type) && !empty($type)) {
            $this->db->where('user_type', $type);
        }
        $data = $this->select();
        if ($data) {
            $result = 1;
        }
        return $result;
    }
}