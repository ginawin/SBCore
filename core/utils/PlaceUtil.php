<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:34 AM
 */

class PlaceUtil extends Util
{
    public function __construct()
    {
        parent::__construct();
        $this->loadModel("PlaceModel");
    }

    public function getListValid($rq_data){
        return true;
    }

    public function getList4Tour($rq_data,$rq_user){
        if(!$this->getListValid($rq_data)){
            return false;
        }
        $select = "place_id,place_code,place_name,place_name_jp";
        $data = $this->PlaceModel->getList4Tour($rq_data,$select);
        return $data;
    }

    public function getList4Hotel($rq_data,$rq_user){
        if(!$this->getListValid($rq_data)){
            return false;
        }
        $select = "place_id,place_code,place_name,place_name_jp";
        $data = $this->PlaceModel->getList4Hotel($rq_data,$select);
        return $data;
    }
}