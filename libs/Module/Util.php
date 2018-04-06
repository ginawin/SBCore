<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 3/14/2018
 * Time: 9:41 AM
 */

class Util
{
    protected $db;
    function __construct(){
        $this->arr_currency = array('AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM',
            'BBD', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BTC', 'BTN', 'BWP', 'BYR', 'BZD', 'CAD', 'CDF', 'CHF', 'CLF',
            'CLP', 'CNY', 'COP', 'CRC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EGP', 'ERN', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL',
            'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'IQD', 'IRR', 'ISK', 'JMD', 'JOD',
            'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LTL', 'LVL', 'LYD', 'MAD',
            'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MRO', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD',
            'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SDG', 'SEK', 'SGD',
            'SHP', 'SLL', 'SOS', 'SRD', 'STD', 'SVC', 'SYP', 'SZL', 'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX',
            'USD', 'UYU', 'UZS', 'VEF', 'VND', 'VUV', 'WST', 'XAF', 'XAG', 'XAU', 'XCD', 'XDR', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMK', 'ZMW', 'ZWL');
        $this->setConn();
        
        $this->loadModel("UserModel","UserClientModel","AgentModel");
    }
    public function execute($func){
        $args = func_get_args();
        $oData = false;
        
        $this->db->beginTransaction();
        $len = count($args);
        $strArgs = '';
        if($len>1){
            for ($i = 1;$i < $len; $i++){
                $strArgs .= ',$args['.$i.']';
            }
        }
        $strArgs = trim($strArgs,',');
        eval('$oData = $this->{$func}('.$strArgs.');');
        $flg = (!empty($this->db->last_error))?false:true;
        if($flg){
            $this->db->commit();
        }else{
            $this->db->rollback();
            return false;
        }
        return $oData;
    }
    public function setConn($name = "conn1"){
        $this->db = Database::instance($name);
    }
    public function loadModel(){
        $models = func_get_args();
        if(!empty($models)){
            foreach($models as $key => $val){
                if(file_exists(MODEL_PATH.$val.PHP_EXT)){
                    App::loadFile(MODEL_PATH.$val.PHP_EXT);
                }else{
                    App::loadFile(MODEL1_PATH.$val.PHP_EXT);
                }
                $this->{$val} = new $val;
            }
        }
    }
    
    public function loadUtil(){
        $utils = func_get_args();
        if(!empty($utils)){
            foreach($utils as $key => $val){
                App::loadFile(UTIL_PATH.$val.PHP_EXT);
                $this->{$val} = new $val;
            }
        }
    }

    protected function getCarSeat($cus_num){
        $car_seat = 0;
        if ($cus_num >= 1 && $cus_num <= 2) {
            $car_seat = 4;
        } else if ($cus_num >= 3 && $cus_num <= 6) {
            $car_seat = 15;
        } else if ($cus_num >= 7 && $cus_num <= 9) {
            $car_seat = 29;
        } else if ($cus_num >= 10 && $cus_num <= 19) {
            $car_seat = 35;
        } else if ($cus_num >= 20 && $cus_num <= 29) {
            $car_seat = 45;
        }
        return $car_seat;
    }

    protected function isUrlExist($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($code == 200) {
            $status = true;
        } else {
            $status = false;
        }
        curl_close($ch);
        return $status;
    }

    public function markUp($price, $mark_value, $mark_type, $currency = '') {
        if (!empty($price)) {
            $mark_value = (!empty($mark_value)) ? $mark_value : 0;
            $mark_type = (!empty($mark_type)) ? $mark_type : 1;
            if ($mark_type == 1) {
                $price += $mark_value;
            } else if ($mark_type == 2) {
                if (((100 - $mark_value) / 100) != 0 && $price > 0) {
                    $price = ceil($price / ((100 - $mark_value) / 100));
                }
            }
            if (!empty($currency)) {
                $price = ceil(($price * $currency) / 100) * 100;
            }
        }
        return number_format($price);
    }
    
    public function U2Y($p,$r){
        return ceil(($p * $r) / 100) * 100;
    }
}