<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:35 AM
 */

class BookingModel extends Model
{
    public $table_name = 'booking';
    public $primary_key = array('booking_id');
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getAutoBKCode($agent_code = '', $date = false){
        if ($date == false) {
            $s_date = SBDate::Str2Int(date('Y/m/d')
                , 'Y/m/d', '/', 0, 0, 0);
            $e_date = SBDate::Str2Int(date('Y/m/d')
                , 'Y/m/d', '/', 23, 59, 59);
        } else {
            $s_date = SBDate::Str2Int($date, 'Y/m/d', '/', 0, 0, 0);
            $e_date = SBDate::Str2Int($date, 'Y/m/d', '/', 23, 59, 59);
        }
        $date = strtotime($date);

        $this->db->where(array('booking.agent_code' => $agent_code));
        $this->db->where(array('booking.booking_date >=' => $s_date));
        $this->db->where(array('booking.booking_date <=' => $e_date));
        $this->db->where(array('booking.booking_type_change =' => 0));
        $item = $this->select();
        if (isset($item) && !empty($item)) {
            $arr_booking_code = array();
            foreach ($item as $one_item) {
                $arr_booking_code[] = trim($one_item['booking_code']);
            }
            $text = 'A';
            for ($i = 0; $i < 2000; $i++) {
                $version = date('md', $s_date) . $text . $agent_code;
                if (in_array($version, $arr_booking_code)) {
                    $text++;
                    continue;
                } else {
                    return $version;
                }
            }
        } else {
            $text = 'A';
            $version = date('md', $s_date) . $text . $agent_code;
            return $version;
        }
    }
}