<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:38 AM
 */

class DotoclassModel extends Model
{
    public $table_name = "doto_class";
    public $primary_key = array("doto_class_id");
    public function __construct(){
        parent::__construct();
    }

    public function get_tariff_full($rq_data,$select = "*"){
        $this->db->select($select);
        if (isset($rq_data['search_season']) && !empty($rq_data['search_season'])) {
            $this->db->{(is_array($rq_data['search_season'])?"in":"where")}("doto_class_season",$rq_data['search_season']);
        }
        if (isset($rq_data['begin_date']) && !empty($rq_data['begin_date'])) {
            $this->db->where('doto_class_begin >=', $rq_data['begin_date']);
        }
        if (isset($rq_data['end_date']) && !empty($rq_data['end_date'])) {
            $this->db->where('doto_class_end <=', $rq_data['end_date']);
        }
        if (isset($rq_data['search_place']) && !empty($rq_data['search_place'])) {
            $this->db->{(is_array($rq_data['search_place'])?"in":"where")}("doto_class_place_id",$rq_data['search_place']);
        }
        if (isset($rq_data['search_star']) && !empty($rq_data['search_star'])) {
            $this->db->{(is_array($rq_data['search_star'])?"in":"where")}("doto_class_category",$rq_data['search_star']);
        }
        if (isset($rq_data['search_price_fr']) && !empty($rq_data['search_price_fr'])) {
            $this->db->where('doto_class_rate_twb_ov >=', $rq_data['search_price_fr']);
        }
        if (isset($rq_data['search_price_to']) && !empty($rq_data['search_price_to'])) {
            $this->db->where('doto_class_rate_twb_ov <=', $rq_data['search_price_to']);
        }
        $this->db->orderby('doto_class_category', 'ASC');
        $this->db->orderby('doto_class_rate_twb_1n', 'ASC');

        return $this->select();

    }
}