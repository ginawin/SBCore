<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:41 AM
 */

class TourTariffNoteModel extends Model
{
    public $table_name = "tour_tariff_note";
    public $primary_key = array("tour_tariff_note_id");
    public function __construct(){
        parent::__construct();
    }

    public function get_tariff($rq_data,$select = "*"){
        $this->db->select($select);
        if (isset($rq_data['begin_date']) && !empty($rq_data['begin_date'])) {
            $this->db->where('begin_date >=', $rq_data['begin_date']);
        }
        if (isset($rq_data['end_date']) && !empty($rq_data['end_date'])) {
            $this->db->where('end_date <=', $rq_data['end_date']);
        }
        if (isset($rq_data['place_id']) && !empty($rq_data['place_id'])) {
            $this->db->{(is_array($rq_data['place_id'])?"in":"where")}("place_id",$rq_data['place_id']);
        }
        return $this->select();
    }
}