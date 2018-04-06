<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 12:34 PM
 */

class PlaceModel extends Model
{
    public $table_name = "place";
    public $primary_key = array("place_id");
    public function __construct(){
        parent::__construct();
    }

    public function get($rq_data,$select = "*"){
        $this->db->select($select);
        if(isset($rq_data['place_id']) && !empty($rq_data['place_id'])){
            $this->db->{(SBArray::valid($rq_data['place_id'])?"in":"where")}("place_id",$rq_data['place_id']);
        }
        //if not array return 1 array
        if(isset($rq_data['place_id']) && !empty($rq_data['place_id'])){
            if(!SBArray::valid($rq_data['place_id'])){
                return $this->selectOne();
            }
        }
        return $this->select();
    }

    public function getList4Tour($iData){
        $sql = "SELECT B.place_id,B.place_code,B.place_name,IF(IFNULL(B.place_name_jp,'')<>'',B.place_name_jp,B.place_name) AS place_name_jp
FROM (SELECT place_id FROM tour WHERE tour_status = 1 AND (tour_kamiki_flg = 1 OR tour_shimoki_flg = 1) GROUP BY place_id) AS A INNER JOIN place AS B ON A.place_id = B.place_id 
WHERE B.place_code <> '' ";

        $result = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    public function getList4Hotel($iData,$select = "*"){
        $sql = "SELECT B.place_id,B.place_code,B.place_name,IF(IFNULL(B.place_name_jp,'')<>'',B.place_name_jp,B.place_name) AS place_name_jp
FROM (SELECT place_id FROM hotel WHERE hotel_status = 1 GROUP BY place_id) AS A INNER JOIN place AS B ON A.place_id = B.place_id 
WHERE B.place_code <> '' ";

        $result = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    public function getIdByCode($code){
        $this->db->select($this->table_name.'.place_id');
        $this->db->where($this->table_name.'.place_code', $code);
        $this->db->orderby('place_id','desc');
        $result = $this->select();
        return (isset($result[0]['place_id'])?$result[0]['place_id']:0);
    }
}