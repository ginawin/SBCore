<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 3/13/2018
 * Time: 5:29 PM
 */

class Model1
{
    public $db = 'conn2';
    public function __construct(){
        if ( ! is_object($this->db)){
            // Load the default database
            $this->db = Database::instance($this->db);
        }
    }

    //process for 1 records
    public function selectOne(){
        //$this->db->select($select);
        if(isset($where)&&!empty($where)){
            foreach($where as $key=>$val){
                $this->db->where($key,$val);
            }
        }
        $this->db->limit(1);
        $result = $this->db->doSelect($this->table_name);
        return isset($result[0])?$result[0]:false;
    }
    public function saveOne($data){
        $this->db->set($data);
        $result = $this->db->doSave($this->table_name);

        if(is_array($this->primary_key)){

        }else if(isset($data[$this->primary_key]) && !empty($data[$this->primary_key])){
            $this->db->last_update_id = $data[$this->primary_key];
        }else{
            $this->db->last_update_id = $this->lastInsertId();
        }
        return $result;
    } //insert into on dupplicate update
    public function insertOne($data){
        $this->db->set($data);
        $result = $this->db->doInsert($this->table_name);
        $this->db->last_update_id = $this->lastInsertId();
        return $result;
    } // insert
    public function updateOne($data){
        if(is_array($this->primary_key)){
            if(!empty($this->primary_key)){
                foreach($this->primary_key as $val){
                    if(isset($data[$val]))
                        $this->db->where($val,$data[$val]);
                }
            }
        }else{
            if(isset($data[$this->primary_key]))
                $this->db->where($this->primary_key,$data[$this->primary_key]);
        }
        $this->db->set($data);
        $result = $this->db->doUpdate($this->table_name);
        if(is_array($this->primary_key)){

        }else{
            $this->db->last_update_id = isset($data[$this->primary_key])?$data[$this->primary_key]:"";
        }
        return $result;
    } // update
    public function deleteOne($data){
        if(is_array($this->primary_key)){
            if(!empty($this->primary_key)){
                foreach($this->primary_key as $val){
                    if(isset($data[$val]))
                        $this->db->where($val,$data[$val]);
                }
            }
        }else{
            if(isset($data[$this->primary_key]))
                $this->db->where($this->primary_key,$data[$this->primary_key]);
        }
        return $this->db->doDelete($this->table_name);
    } // delete

    //process for many records
    public function select(){
        //$this->db->select($select);
        if(isset($where)&&!empty($where)){
            foreach($where as $key=>$val){
                $this->db->where($key,$val);
            }
        }
        if(!empty($limit))
            $this->db->limit($limit);
        if(!empty($offset))
            $this->db->offset($offset);
        $result = $this->db->doSelect($this->table_name);

        return (!empty($result) && is_array($result)) ?$result:false;
    }
    public function save($iData){
        $rs = false;
        if(!empty($iData) && is_array($iData)){
            foreach($iData as $val){
                $rs = $this->saveOne($val);
                if($rs == false)
                    break;
            }
        }
        return $rs;
    }
    public function insert($iData){
        $rs = false;
        if(!empty($iData) && is_array($iData)){
            foreach($iData as $val){
                $rs = $this->insertOne($val);
                if($rs == false)
                    break;
            }
        }
        return $rs;
    }
    public function update($iData){
        $rs = false;
        if(!empty($iData) && is_array($iData)){
            foreach($iData as $val){
                $rs = $this->updateOne($val);
                if($rs == false)
                    break;
            }
        }
        return $rs;
    }
    public function delete($iData){
        $rs = false;
        if(!empty($iData) && is_array($iData)){
            foreach($iData as $val){
                $rs = $this->deleteOne($val);
                if($rs == false)
                    break;
            }
        }
        return $rs;
    }

    public  function selectCount($where){
        $str = "";
        if(is_array($this->primary_key)){
            if(!empty($this->primary_key)){
                $str = implode(",", $this->primary_key);
            }
        }else{
            $str = $this->primary_key;
        }
        $this->db->select("count($str) AS num_row");
        if(isset($where)&&!empty($where)){
            foreach($where as $key=>$val){
                $this->db->where($key,$val);
            }
        }
        $result = $this->db->doSelect($this->table_name);
        return isset($result[0]["num_row"])?$result[0]["num_row"]:false;
    }
    public function selectNext($where , $select = "*"){
        if(!(isset($where[$this->primary_key])&&!empty($where[$this->primary_key])))
            return false;
        $this->db->select($select);
        if(isset($where)&&!empty($where)){
            foreach($where as $key=>$val){
                if($key == $this->primary_key){
                    $this->db->where($key,"(SELECT MIN(".$this->primary_key.") FROM ".$this->table_name." "
                        . "WHERE ".$this->primary_key." > $val LIMIT 1)");
                }else{
                    $this->db->where($key,$val);
                }
            }
        }
        $this->db->limit(1);
        $result = $this->db->doSelect($this->table_name);
        return isset($result[0])?$result[0]:false;
    }
    public function selectPrev($where , $select = "*"){
        if(!(isset($where[$this->primary_key])&&!empty($where[$this->primary_key])))
            return false;
        $this->db->select($select);
        if(isset($where)&&!empty($where)){
            foreach($where as $key=>$val){
                if($key == $this->primary_key){
                    $this->db->where($key,"(SELECT MAX(".$this->primary_key.") FROM ".$this->table_name." "
                        . "WHERE ".$this->primary_key." < $val LIMIT 1)");
                }else{
                    $this->db->where($key,$val);
                }
            }
        }
        $this->db->limit(1);
        $result = $this->db->doSelect($this->table_name);
        return isset($result[0])?$result[0]:false;
    }
    public function lastInsertId(){
        return $this->db->lastInsertId();
    }
}