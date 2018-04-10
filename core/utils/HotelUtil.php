<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:34 AM
 */

class HotelUtil extends Util
{
    public function __construct()
    {
        parent::__construct();

        $this->loadModel("HotelModel","HotelPriceModel","HotelRateModel",
            "HotelFileModel","HotelCancelationModel","HotelPolicyBenefitModel",
            "DotoclassModel","HotelPricePromotionModel","TourModel","PlaceModel",
            "CurrencyModel","ConfigurationModel","HotelPriceHistoryModel");
        
        $this->mTBK = array('TBK', 'TBKF', 'TBKN', 'TBKO', 'TBKT', 'TBKS','TBKV');
        $this->TOUR_CHARGE = 0.85;
    }

    public function getListValid($rq_data){
        return true;
    }

    public function getList($rq_data,$rq_user){

        //validation
        if(!$this->getListValid($rq_data)){
            return false;
        }
        
        //process data
        $rq_data['tbk_flg'] = false;
        if(in_array($rq_user["agent_code"],$this->mTBK)){
            $rq_data['tbk_flg'] = true;
        }

        $data = array();
        $mlist = array();

        $checkin_date = new DateTime($rq_data['checkin_date']);
        $checkout_date = new DateTime($rq_data['checkout_date']);
        $rq_data['checkin_date'] = strtotime($rq_data['checkin_date']);
        $rq_data['checkout_date'] = strtotime($rq_data['checkout_date']);

        $night = $checkout_date->diff($checkin_date);
        $night = $night->days;

        $cus_num = 0;
        if(isset($rq_data['rq_rooms'])&&!empty($rq_data['rq_rooms'])){
            $rq_data['rq_rooms'] = json_decode($rq_data['rq_rooms'],true);
            if(!empty($rq_data['rq_rooms'])){
                foreach($rq_data['rq_rooms'] as $rq_room){
                    $cus_num += $rq_room['adult_num'];
                }
            }
        }

        $currency_rate = 1;
        if(isset($rq_data['curr_cd'])&&!empty($rq_data['curr_cd'])&&in_array(strtoupper($rq_data['curr_cd']),$this->arr_currency)&&strtoupper($rq_data['curr_cd'])!='USD'){
            $rq_temp = array(
                "currency_code" => $rq_data['curr_cd']
            );
            $currency_info = $this->CurrencyModel->get($rq_temp);
            if(isset($currency_info['currency_rate'])&&!empty($currency_info['currency_rate'])){
                $currency_rate = $currency_info['currency_rate'];
            }
        }

        if($rq_data["tbk_flg"]){
            $select = 'SELECT hotel.hotel_id,hotel.place_id,hotel.place_code,hotel.hotel_name,hotel.hotel_name_jp,
                    hotel.hotel_star,hotel.hotel_address,hotel.hotel_summary,hotel.hotel_summary_jp,
                    hotel.hotel_phone,hotel.hotel_visit,hotel_price_id,hotel_price_category,hotel_price_begin_date,hotel_price_end_date,
                    hotel_price_sgl_ov,hotel_price_twb_ov,hotel_price_trp_ov,
                    hotel_price_trf,hotel_policy_benefit.hotel_price_promotion,hotel_policy_benefit.hotel_price_promotion_jp,
                    hotel_policy_benefit.hotel_price_cancel_policy,hotel_policy_benefit.hotel_price_cancel_policy_jp,
                    hotel_policy_benefit.hotel_price_git_cancel_policy,hotel_policy_benefit.hotel_price_git_cancel_policy_jp,
                    hotel_policy_benefit.hotel_price_benefit,hotel_policy_benefit.hotel_price_benefit_jp,
                    hotel_policy_benefit.hotel_price_deposit,hotel_policy_benefit.hotel_price_deposit_jp,
                    hotel_price_free_lco18,hotel_price_min_night_charge';
        }else{
            $select = 'SELECT hotel.hotel_id,hotel.place_id,hotel.place_code,hotel.hotel_name,hotel.hotel_name_jp,
                    hotel.hotel_star,hotel.hotel_address,hotel.hotel_summary,hotel.hotel_summary_jp,
                    hotel.hotel_phone,hotel.hotel_visit,hotel_price_id,hotel_price_category,hotel_price_begin_date,hotel_price_end_date,
                    hotel_price2_sgl_ov AS hotel_price_sgl_ov,hotel_price2_twb_ov AS hotel_price_twb_ov,hotel_price2_trp_ov AS hotel_price_trp_ov,
                    hotel_price_trf,hotel_policy_benefit.hotel_price_promotion,hotel_policy_benefit.hotel_price_promotion_jp,
                    hotel_policy_benefit.hotel_price_cancel_policy,hotel_policy_benefit.hotel_price_cancel_policy_jp,
                    hotel_policy_benefit.hotel_price_git_cancel_policy,hotel_policy_benefit.hotel_price_git_cancel_policy_jp,
                    hotel_policy_benefit.hotel_price_benefit,hotel_policy_benefit.hotel_price_benefit_jp,
                    hotel_policy_benefit.hotel_price_deposit,hotel_policy_benefit.hotel_price_deposit_jp,
                    hotel_price_free_lco18,hotel_price_min_night_charge';
        }

        $mHotel = $this->HotelPriceModel->get_for_list($rq_data,$select);

        if(isset($rq_data['trf_type'])&&!empty($rq_data['trf_type'])){
            $car_type = (isset($rq_data['car_type'])&&!empty($rq_data['car_type']))?$rq_data['car_type']:0;
            if(isset($cus_num)&&$cus_num>0){
                $rq_temp = array(
                    "tour_code" => $rq_data['city_cd'].'T02',
                    "checkin_date" => $rq_data['checkin_date']
                );
                $select = 'tour_price.tour_price_id,tour.tour_name,tour.tour_name_en,tour_price.tour_price4seats,tour_price.tour_price15seats,tour_price.tour_price29seats,tour_price.tour_price35seats,tour_price.tour_price45seats,tour_price.tour_pricefit_guide,tour_price.tour_pricegit_guide,tour_price.tour_price_other';
                $trf_info = $this->TourModel->get_tour_price($rq_temp,$select);
            }
        }
        //get array hotel id
        if(isset($mHotel)&&!empty($mHotel)){
            $arr_hotel_id = array();
            foreach($mHotel as $hotel){
                if(!in_array($hotel['hotel_id'],$arr_hotel_id)){
                    $arr_hotel_id[] = $hotel['hotel_id'];
                }
            }
        }

        if(isset($arr_hotel_id)&&!empty($arr_hotel_id)){
            //get hotel allotment and format
            $rq_temp = array(
                "hotel_id" => $arr_hotel_id,
                "checkin_date" => $rq_data['checkin_date'],
                "checkout_date" => $rq_data['checkout_date']
            );
            $select = "hotel_id,category,hotel_rate_date,(room_level_kis + room_level_fsc + room_level_tbk + room_level_wap + room_level_tmc + room_level_ali) AS room_level,cut_off_date,active,active2";
            $hotel_rates = $this->HotelRateModel->get($rq_temp,$select);
            if(isset($hotel_rates)&&!empty($hotel_rates)){
                $arr_allot = array();
                foreach($hotel_rates as $hotel_rate){
                    $date = date('Y/m/d',$hotel_rate['hotel_rate_date']);
                    $_ht_cat = trim($hotel_rate['category']);
                    $arr_allot[$hotel_rate['hotel_id']][$_ht_cat][$date]['room_level'] = $hotel_rate['room_level'];
                    $arr_allot[$hotel_rate['hotel_id']][$_ht_cat][$date]['cut_off_date'] = $hotel_rate['cut_off_date'];
                }
            }
            //get hotel file and format
            $rq_temp = array(
                "hotel_id" => $arr_hotel_id
            );
            $select = 'hotel_id,file_name,MIN(file_sort_order) as `file_sort_order`';
            $hotel_files = $this->HotelFileModel->get_for_list($rq_temp,$select);
            if(isset($hotel_files)&&!empty($hotel_files)){
                $arr_file = array();
                foreach($hotel_files as $hotel_file){
                    $thumb_url='http://www.toursystem.biz/uploads/product/thumb_'.$hotel_file['file_name'];
                    $file_url='http://www.toursystem.biz/uploads/product/'.$hotel_file['file_name'];

                    $arr_file[$hotel_file['hotel_id']]['file_url'] = $file_url;
                    $arr_file[$hotel_file['hotel_id']]['thumb_url'] = $thumb_url;
                    $arr_file[$hotel_file['hotel_id']]['file_sort_order'] =  $hotel_file['file_sort_order'];
                }
            }
        }

        //format list
        if(isset($mHotel)&&!empty($mHotel)){
            foreach($mHotel as $hotel){
                if($night<=$hotel['hotel_price_min_night_charge']){
                    $night = $hotel['hotel_price_min_night_charge'];
                }
                $mlist[$hotel['hotel_id']]['hotel_cd'] = $hotel['hotel_id'];
                //$mlist[$hotel['hotel_id']]['city_id'] = $hotel['place_id'];
                $mlist[$hotel['hotel_id']]['city_cd'] = $hotel['place_code'];
                $mlist[$hotel['hotel_id']]['hotel_name'] = $hotel['hotel_name'];
                $mlist[$hotel['hotel_id']]['hotel_name_jp'] = $hotel['hotel_name_jp'];
                $mlist[$hotel['hotel_id']]['hotel_star'] = $hotel['hotel_star'];
                $mlist[$hotel['hotel_id']]['hotel_address'] = $hotel['hotel_address'];
                $mlist[$hotel['hotel_id']]['hotel_summary'] = $hotel['hotel_summary'];
                $mlist[$hotel['hotel_id']]['hotel_summary_jp'] = $hotel['hotel_summary_jp'];
                $mlist[$hotel['hotel_id']]['hotel_phone'] = $hotel['hotel_phone'];
                $mlist[$hotel['hotel_id']]['curr_cd'] = (isset($rq_data['curr_cd'])&&!empty($rq_data['curr_cd'])&&in_array(strtoupper($rq_data['curr_cd']),$this->arr_currency))?strtoupper($rq_data['curr_cd']):'USD';
                //update later
                //$mlist[$hotel['hotel_id']]['total_price'] = 0;
                $hotel['hotel_price_category'] = trim($hotel['hotel_price_category']);
                $price_name = $hotel['hotel_price_category'];
                if(strpos(strtoupper($price_name),'SUP')!=-1){
                    if(strpos(strtoupper($price_name),'SUPERRIOR')!=-1){
                        $price_name = str_replace('SUP','SUPERIOR',strtoupper($price_name));
                    }
                }
                if(strpos(strtoupper($price_name),'DLX')!=-1){
                    $price_name = str_replace('DLX','DELUXE',strtoupper($price_name));
                }
                if(strpos(strtoupper($price_name),'STD')!=-1){
                    $price_name = str_replace('STD','STANDARD',strtoupper($price_name));
                }

                $cancel_policy_remark = "";
                if(!empty($hotel['hotel_price_cancel_policy']))
                    $cancel_policy_remark.=$hotel['hotel_price_cancel_policy']."\n";
                if(!empty($hotel['hotel_price_git_cancel_policy']))
                    $cancel_policy_remark.=$hotel['hotel_price_git_cancel_policy']."\n";

                $cancel_policy_remark_jp = "";
                if(!empty($hotel['hotel_price_cancel_policy_jp']))
                    $cancel_policy_remark_jp.=$hotel['hotel_price_cancel_policy_jp']."\n";
                if(!empty($hotel['hotel_price_git_cancel_policy_jp']))
                    $cancel_policy_remark_jp.=$hotel['hotel_price_git_cancel_policy_jp']."\n";

                $hotel_price_summary = array(
                    'price_cd'=>$hotel['hotel_price_category'],
                    'price_name'=>$price_name,
                    'hotel_price_begin_date'=>date('Y/m/d',$hotel['hotel_price_begin_date']),
                    'hotel_price_end_date'=>date('Y/m/d',$hotel['hotel_price_end_date']),
                    'meal_flg'=>1,
                    'meal_name'=>'Buffet - Breakfast',
                    //update later
                    'avail_flg'=>0,
                    'lco_flg'=>isset($hotel['hotel_price_free_lco18'])?$hotel['hotel_price_free_lco18']:0,
                    'promotion_remark'=>nl2br($hotel['hotel_price_promotion']),
                    'promotion_remark_jp'=>nl2br($hotel['hotel_price_promotion_jp']),
                    'benefit_remark'=>nl2br($hotel['hotel_price_benefit']),
                    'benefit_remark_jp'=>nl2br($hotel['hotel_price_benefit_jp']),
                    'deposit_remark'=>nl2br($hotel['hotel_price_deposit']),
                    'deposit_remark_jp'=>nl2br($hotel['hotel_price_deposit_jp']),
                    'cancel_policy_remark'=>nl2br($cancel_policy_remark),
                    'cancel_policy_remark_jp'=>nl2br($cancel_policy_remark_jp),
                    'min_night_charge'=>$hotel['hotel_price_min_night_charge'],
                    //update later
                    'average_price'=>0,
                    'total_price'=>0,
                    'sgl_price'=>ceil($hotel['hotel_price_sgl_ov']*$currency_rate),
                    'twb_price'=>ceil($hotel['hotel_price_twb_ov']*$currency_rate)
                );
                if(isset($hotel['hotel_price_trp_ov'])&&!empty($hotel['hotel_price_trp_ov'])){
                    $hotel_price_summary['trp_price'] = ceil($hotel['hotel_price_trp_ov']*$currency_rate);
                }
                
                if(isset($rq_data['rq_rooms'])&&!empty($rq_data['rq_rooms'])){
                    $sum_avail_flg = 1;
                    foreach($rq_data['rq_rooms'] as $key=>$rq_room){
                        $hotel_price_summary['hotel_price_details'][$key]['meal_flg'] = 1;
                        $hotel_price_summary['hotel_price_details'][$key]['meal_name'] = 'Buffet - Breakfast';
                        $hotel_price_summary['hotel_price_details'][$key]['adult_num'] = $rq_room['adult_num'];
                        if(!empty($rq_room['child_num'])){
                            $hotel_price_summary['hotel_price_details'][$key]['child_num'] = $rq_room['child_num'];
                        }

                        if(isset($rq_room['child_num'])&&!empty($rq_room['child_num'])){
                            for($i=1;$i<=$rq_room['child_num'];$i++){
                                $hotel_price_summary['hotel_price_details'][$key]["child_age_$i"] = $rq_room["child_age_$i"];
                            }
                        }
                        $avail_flg = 1;
                        for($i=0;$i<$night;$i++){
                            $date = strtotime("+$i day",$rq_data['checkin_date']);
                            if($date>=$hotel['hotel_price_begin_date']&&$date<=strtotime("+1 day",$hotel['hotel_price_end_date'])){
                                $_avil_flg = 0;

                                $temp = isset($arr_allot[$hotel['hotel_id']][$hotel['hotel_price_category']])?$arr_allot[$hotel['hotel_id']][$hotel['hotel_price_category']]:array();
                                if(!empty($temp)){
                                    $today = strtotime("+$i day",strtotime(date('Y/m/d',time())));
                                    $_date = date('Y/m/d',$date);
                                    if(isset($temp[$_date]['cut_off_date'])){
                                        if(strtotime('+'.$temp[$_date]['cut_off_date'].' day',$today)<=$date){
                                            if(isset($temp[$_date])&&!empty($temp[$_date])){
                                                if(($temp[$_date]['room_level']-$key)>0&&strtotime('-'.$temp[$_date]['room_level'].' day',$rq_data['checkin_date'])>=strtotime(date('Y/m/d',time()))){
                                                    $_avil_flg = 1;
                                                }else{
                                                    $_avil_flg = 0;
                                                }
                                            }
                                        }
                                    }
                                }

                                $avail_flg &= $_avil_flg;
                                $price_key = '';
                                switch(intval($rq_room['adult_num'])){
                                    case 1:
                                        $price_key = 'hotel_price_sgl_ov';
                                        break;
                                    case 2:
                                        $price_key = 'hotel_price_twb_ov';
                                        break;
                                    case 3:
                                        $price_key = 'hotel_price_trp_ov';
                                        break;
                                }
                                if ($rq_room['adult_num']) {
                                    $night_charge = array(
                                        'date'=>date('Y/m/d',$date),
                                        'price'=>ceil($hotel["$price_key"]*$currency_rate)*$rq_room['adult_num'],
                                        'avail_flg'=>$_avil_flg
                                    );
                                    $hotel_price_summary['hotel_price_details'][$key]['night_charges'][$i] = $night_charge;
                                }
                            }
                        }
                        $sum_avail_flg&=$avail_flg;
                        //update average price later
                        $hotel_price_summary['hotel_price_details'][$key]['average_price'] = 0;
                        $hotel_price_summary['hotel_price_details'][$key]['total_price'] = 0;
                        $hotel_price_summary['hotel_price_details'][$key]['avail_flg'] = $avail_flg;
                        $hotel_price_summary['hotel_price_details'][$key]['lco_flg'] = isset($hotel['hotel_price_free_lco18'])?$hotel['hotel_price_free_lco18']:0;
                    }
                    $hotel_price_summary['avail_flg'] = $sum_avail_flg;
                }


                //update trf combine price
                if(isset($rq_data['trf_type'])&&!empty($rq_data['trf_type'])){
                    $car_type = (isset($rq_data['car_type'])&&!empty($rq_data['car_type']))?$rq_data['car_type']:0;
                    //trf private car
                    $trf_total_price = 0;
                    $transfer_price_summary = array(
                        'trf_total_price'=>0,
                        'trf_average_price'=>0,
                        'trf_name'=>'Transfer Roundtrip Airport to Hotel'
                    );

                    if($cus_num>30){
                        $car_num = intval($cus_num/30);
                        $remain_cus = intval($cus_num%30);
                        if(isset($trf_info)&&!empty($trf_info)){
                            $guide_price = (!empty($trf_info['tour_pricegit_guide'])?$trf_info['tour_pricegit_guide']:$trf_info['tour_pricefit_guide']);
                            $trf_total_price = $car_num*(($trf_info['tour_price45seats']+$guide_price)/$this->TOUR_CHARGE);
                        }
                        $car_detail[0] = array(
                            'car_type'=>($car_type==1)?'Private':'Combine',
                            'car_name'=>'Samco',
                            'car_seat'=>45,
                            'car_num'=>$car_num,
                            'cus_num'=>30,
                            'price'=>ceil($trf_total_price*$currency_rate)
                        );

                        $car_seat = $this->getCarSeat($remain_cus);
                        if(isset($trf_info)&&!empty($trf_info)){
                            if($car_seat<29){
                                //FIT
                                if($car_type==1){
                                    //private car
                                    $guide_price = $trf_info['tour_pricefit_guide'];
                                    $trf_more_price = (($trf_info["tour_price".$car_seat."seats"]+$guide_price)/$this->TOUR_CHARGE);
                                }else{
                                    //combine car
                                    $trf_more_price = ($hotel['hotel_price_trf']*$remain_cus);
                                }
                            }else{
                                //GIT
                                //private car
                                $guide_price = (!empty($trf_info['tour_pricegit_guide'])?$trf_info['tour_pricegit_guide']:$trf_info['tour_pricefit_guide']);
                                $trf_more_price = (($trf_info["tour_price".$car_seat."seats"]+$guide_price)/$this->TOUR_CHARGE);
                            }
                            $car_detail[1] = array(
                                'car_type'=>($car_type==1)?'Private':'Combine',
                                'car_name'=>'Samco',
                                'car_seat'=>$car_seat,
                                'car_num'=>1,
                                'cus_num'=>$remain_cus,
                                'price'=>ceil($trf_more_price*$currency_rate)
                            );
                            $trf_total_price+=$trf_more_price;
                        }
                    }else{
                        $car_seat = $this->getCarSeat($cus_num);
                        if(isset($trf_info)&&!empty($trf_info)){
                            if($car_seat<29){
                                //FIT
                                if($car_type==1){
                                    //private car
                                    $guide_price = $trf_info['tour_pricefit_guide'];
                                    $trf_total_price = (($trf_info["tour_price".$car_seat."seats"]+$guide_price)/$this->TOUR_CHARGE);
                                }else{
                                    //combine car
                                    $trf_total_price = ($hotel['hotel_price_trf']*$cus_num);
                                }
                            }else{
                                //GIT
                                //private car
                                $guide_price = (!empty($trf_info['tour_pricegit_guide'])?$trf_info['tour_pricegit_guide']:$trf_info['tour_pricefit_guide']);
                                $trf_total_price = (($trf_info["tour_price".$car_seat."seats"]+$guide_price)/$this->TOUR_CHARGE);
                            }
                            $car_detail[0] = array(
                                'car_type'=>($car_type==1)?'Private':'Combine',
                                'car_name'=>'Samco',
                                'car_seat'=>$car_seat,
                                'car_num'=>1,
                                'cus_num'=>$cus_num,
                                'price'=>ceil($trf_total_price*$currency_rate)
                            );
                        }
                    }
                    $transfer_price_summary['trf_total_price'] = ceil($trf_total_price*$currency_rate);
                    if ($cus_num != 0) {
                        $transfer_price_summary['trf_average_price'] = ceil($transfer_price_summary['trf_total_price']/$cus_num);
                    }
                    if(isset($car_detail)&&!empty($car_detail)){
                        $transfer_price_summary['transfer_price_details'] = $car_detail;
                    }
                    $hotel_price_summary['transfer_price_summary'] = $transfer_price_summary;
                }
                $mlist[$hotel['hotel_id']]['hotel_price_summaries'][] = $hotel_price_summary;

                if(isset($arr_file[$hotel['hotel_id']])&&!empty($arr_file[$hotel['hotel_id']])){
                    $mlist[$hotel['hotel_id']]['hotel_files'][] = $arr_file[$hotel['hotel_id']];
                }

                if(isset($rq_data['avail_flg'])&&!empty($rq_data['avail_flg'])){
                    if($avail_flg==0){unset($mlist[$hotel['hotel_id']]);}
                }
            }

            //update average price
            if(isset($mlist)&&!empty($mlist)){
                foreach($mlist as $list){
                    //update total price
                    if(isset($list['hotel_price_summaries'])&&!empty($list['hotel_price_summaries'])){
                        foreach($list['hotel_price_summaries'] as $sm_key=>$sm_list){
                            if(isset($sm_list['hotel_price_details'])&&!empty($sm_list['hotel_price_details'])){
                                $total_price = 0;
                                foreach($sm_list['hotel_price_details'] as $dt_key=>$hotel_price_detail){
                                    $total_rom_price = 0;
                                    if(isset($hotel_price_detail['night_charges'])&&!empty($hotel_price_detail['night_charges'])){
                                        foreach($hotel_price_detail['night_charges'] as $night_charge){
                                            $total_rom_price+= $night_charge['price'];
                                            $total_price+=$night_charge['price'];
                                        }
                                        $sm_list['hotel_price_details'][$dt_key]['average_price'] = ceil($total_rom_price/count($hotel_price_detail['night_charges']));
                                        $sm_list['hotel_price_details'][$dt_key]['total_price'] = $total_rom_price;
                                    }

                                }
                                $total_price+=isset($sm_list['transfer_price_summary']['trf_total_price'])?$sm_list['transfer_price_summary']['trf_total_price']:0;

                                $list['hotel_price_summaries'][$sm_key] = $sm_list;
                                $list['hotel_price_summaries'][$sm_key]['total_price'] =$total_price;
                                if ($cus_num > 0) {
                                    $list['hotel_price_summaries'][$sm_key]['average_price'] =ceil($total_price/$cus_num);
                                }
                            }
                        }
                    }
                    $data[] = $list;
                }
            }
        }
        return $data;
    }

    public function getDetailValid($rq_data){
        return true;
    }

    public function getDetail($rq_data,$rq_user,$BK_FLG = false){
        
        if(!$this->getDetailValid($rq_data)){
            return false;
        }
        
        $rq_data['tbk_flg'] = false;
        if(in_array($rq_user["agent_code"],$this->mTBK)){
            $rq_data['tbk_flg'] = true;
        }
    
        $lco12 = strtotime("12:00");
        $lco18 = strtotime("18:00");
    
        $mHotel = false;
    
        $select = 'hotel_id,hotel_code AS hotel_cd,place_id AS city_id,place_code AS city_cd,hotel_name,hotel_name_jp,hotel_summary,hotel_summary_jp,hotel_room,
                    hotel_star,hotel_meter,hotel_address,hotel_phone,hotel_fax,area_id,
                    hotel_area,hotel_contact_name,hotel_email,hotel_website,hotel_air_condition,
                    hotel_telephone,hotel_television,hotel_alarm_clock,hotel_video_dvd,hotel_internet,
                    hotel_minibar,hotel_refrigerator,hotel_kettle,hotel_hairdryer,hotel_safetybox,
                    hotel_iron,hotel_sliper,hotel_toothbrush,hotel_amenity_goods,hotel_bath_robe,
                    hotel_bathtub,hotel_japanese_guide,hotel_room_service,hotel_laundy,hotel_baby_sitter,
                    hotel_kids_club,hotel_japanese_staff,hotel_bussiness,hotel_wake_up,hotel_tennis_court,
                    hotel_convenience_store,hotel_health_gym,hotel_message,hotel_parking_lot,
                    hotel_restaurant,hotel_bar,hotel_conference_room,hotel_handicapped_equipment,
                    hotel_swinming_pool,hotel_chilren_pool,hotel_reception,hotel_concierge,
                    hotel_room_room,hotel_out_side_call,hotel_international_call,hotel_room_service_no,
                    hotel_house_keeping,hotel_spa_massage,hotel_map,hotel_git_room,hotel_git_foc,
                    hotel_allot,hotel_visit,hotel_general,hotel_rom_internet,hotel_rom_parking,
                    hotel_rom_amenities,hotel_room_dining,hotel_room_entertaiment,hotel_room_recreation,
                    hotel_room_spa,hotel_room_accessibility,hotel_rom_checkin,hotel_rom_checkout,
                    hotel_rom_pets,hotel_rom_polici,hotel_rom_fees,hotel_rom_additional,
                    hotel_built,hotel_no_of_floor,hotel_bulding,hotel_img_dining,hotel_img_entertainment,
                    hotel_img_recreation,hotel_img_spa,hotel_img_accessibility,hotel_facility_other,
                    hotel_name_contact_1,hotel_name_contact_2,hotel_name_contact_3,hotel_name_contact_4,
                    hotel_name_phone_1,hotel_name_phone_2,hotel_name_phone_3,hotel_name_phone_4,
                    hotel_name_email_1,hotel_name_email_2,hotel_name_email_3,hotel_name_email_4,
                    hotel_breakfast_time,hotel_breakfast_content,lat,lon';
    
        $rq_temp = array(
            "hotel_id" => $rq_data['hotel_cd']
        );
        if($rq_data['tbk_flg']){
            $mHotel = $this->HotelModel->getTBK($rq_temp,$select);
        }else{
            $mHotel = $this->HotelModel->get($rq_temp,$select);
        }
    
        if(isset($mHotel)&&!empty($mHotel)){
        
            $mHotel['curr_cd'] = (isset($rq_data['curr_cd'])&&!empty($rq_data['curr_cd'])&&in_array(strtoupper($rq_data['curr_cd']),$this->arr_currency))?strtoupper($rq_data['curr_cd']):'USD';
        
            $mlist = array();
        
            $checkin_date = new DateTime($rq_data['checkin_date']);
            $checkout_date = new DateTime($rq_data['checkout_date']);
            $rq_data['checkin_date'] = strtotime($rq_data['checkin_date']);
            $rq_data['checkout_date'] = strtotime($rq_data['checkout_date']);
        
            $night = $checkout_date->diff($checkin_date);
            $night = $night->days;
        
            $cus_num = 0;
            if(isset($rq_data['rq_rooms'])&&!empty($rq_data['rq_rooms'])){
                $rq_data['rq_rooms'] = json_decode($rq_data['rq_rooms'],true);
                if(!empty($rq_data['rq_rooms'])){
                    foreach($rq_data['rq_rooms'] as $rq_room){
                        $cus_num += $rq_room['adult_num'];
                    }
                }
            }
        
            if(!(isset($rq_data['checkin_date'])&&!empty($rq_data['checkin_date']))||!(isset($rq_data['checkout_date'])&&!empty($rq_data['checkout_date'])))
                return $mHotel;
        
            //get currency rate
            $currency_rate = 1;
            if(isset($rq_data['curr_cd'])&&!empty($rq_data['curr_cd'])&&in_array(strtoupper($rq_data['curr_cd']),$this->arr_currency)&&strtoupper($rq_data['curr_cd'])!='USD'){
                $rq_temp = array(
                    "currency_code" => $rq_data['curr_cd']
                );
                $currency_info = $this->CurrencyModel->get($rq_temp);
                if(isset($currency_info['currency_rate'])&&!empty($currency_info['currency_rate'])){
                    $currency_rate = $currency_info['currency_rate'];
                }
            }
        
            //get transfer price
            if(isset($rq_data['trf_type'])&&!empty($rq_data['trf_type'])){
                $car_type = (isset($rq_data['car_type'])&&!empty($rq_data['car_type']))?$rq_data['car_type']:0;
                if(isset($cus_num)&&$cus_num>0){
                    $rq_temp = array(
                        "tour_code" => $mHotel['city_cd'].'T02',
                        "checkin_date" => $rq_data['checkin_date']
                    );
                    $select = 'tour_price.tour_price_id,tour.tour_name,tour.tour_name_en,tour_price.tour_price4seats,tour_price.tour_price15seats,tour_price.tour_price29seats,tour_price.tour_price35seats,tour_price.tour_price45seats,tour_price.tour_pricefit_guide,tour_price.tour_pricegit_guide,tour_price.tour_price_other';
                    $trf_info = $this->TourModel->get_tour_price($rq_temp,$select);
                }
            }
        
            //get hotel allotment and format
            $rq_temp = array(
                "hotel_id" => $mHotel['hotel_id'],
                "checkin_date" => $rq_data['checkin_date'],
                "checkout_date" => $rq_data['checkout_date']
            );
            $select = "hotel_id,category,hotel_rate_date,(room_level_kis + room_level_fsc + room_level_tbk + room_level_wap + room_level_tmc + room_level_ali) AS room_level,cut_off_date,active,active2";
            $hotel_rates = $this->HotelRateModel->get($rq_temp,$select);
        
            if(isset($hotel_rates)&&!empty($hotel_rates)){
                $arr_allot = array();
                foreach($hotel_rates as $hotel_rate){
                    $date = date('Y/m/d',$hotel_rate['hotel_rate_date']);
                    $_ht_cat = trim($hotel_rate['category']);
                    $arr_allot[$hotel_rate['hotel_id']][$_ht_cat][$date]['room_level'] = $hotel_rate['room_level'];
                    $arr_allot[$hotel_rate['hotel_id']][$_ht_cat][$date]['cut_off_date'] = $hotel_rate['cut_off_date'];
                }
            }
        
            //get hotel file and format
            $rq_temp = array(
                "hotel_id" => $mHotel['hotel_id']
            );
            $select = 'file_name AS file_url,file_sort_order';
            $hotel_files = $this->HotelFileModel->get_for_detail($rq_temp,$select);
            if(isset($hotel_files)&&!empty($hotel_files)){
                foreach($hotel_files as $index=>$hotel_file){
                    $thumb_url='http://www.toursystem.biz/uploads/product/thumb_'.$hotel_file['file_url'];
                    $file_url='http://www.toursystem.biz/uploads/product/'.$hotel_file['file_url'];
                
                    $hotel_file['thumb_url'] = $thumb_url;
                    $hotel_file['file_url'] = $file_url;
                    $hotel_files[$index] = $hotel_file;
                }
            }
            $mHotel['hotel_files'] = $hotel_files;
        
            //get hotel cancelation and format
            $mCancel = array();
            if(isset($rq_data['hotel_cd'])&&!empty($rq_data['hotel_cd'])&&isset($rq_data['checkin_date'])&&!empty($rq_data['checkin_date'])&&isset($rq_data['checkout_date'])&&!empty($rq_data['checkout_date'])){
                $rq_temp = array(
                    "hotel_id" => $rq_data['hotel_cd'],
                    "checkin_date" => $rq_data['checkin_date'],
                    "checkout_date" => $rq_data['checkout_date']
                );
                $mCancel = $this->HotelCancelationModel->get($rq_temp);
                $hotel_cancel_begin_date = 0;
                $arr_cancel_temp =array();
                if(isset($mCancel)&&!empty($mCancel)){
                    foreach($mCancel as $cancel){
                        if(!empty($cancel['hotel_cancel_begin_date'])&&!empty($cancel['hotel_cancel_end_date'])){
                            if($cancel['hotel_cancel_begin_date']<=$rq_data['checkin_date']&&$cancel['hotel_cancel_end_date']>=$rq_data['checkout_date']){
                                if($cancel['hotel_cancel_begin_date']>=$hotel_cancel_begin_date){
                                    $hotel_cancel_begin_date = $cancel['hotel_cancel_begin_date'];
                                }
                            }
                        }else{
                            $arr_cancel_temp[] = $cancel;
                        }
                    }
                
                    if(!empty($hotel_cancel_begin_date)){
                        foreach($mCancel as $cancel){
                            if($cancel['hotel_cancel_begin_date']==$hotel_cancel_begin_date){
                                $arr_cancel_temp[] = $cancel;
                            }
                        }
                    }
                }
            }
        
        
        
            //get all price category
            $rq_data['hotel_id'] = $mHotel['hotel_id'];
            if($rq_data["tbk_flg"]){
                $select = 'hotel_price_id,hotel_price_category,hotel_price_begin_date,hotel_price_end_date,
                hotel_price_sgl,hotel_price_twb,hotel_price_extra,hotel_price_sgl_ov,hotel_price_twb_ov,hotel_price_trp_ov,hotel_price_min_night_charge,
                hotel_price_sgl_1n,hotel_price_twb_1n,hotel_price_trp_1n,hotel_price_sgl_2n,hotel_price_twb_2n,hotel_price_trp_2n,
                hotel_price_sgl_3n,hotel_price_twb_3n,hotel_price_trp_3n,hotel_price_trf_prc,
                hotel_price_trf,hotel_policy_benefit.hotel_price_promotion,hotel_policy_benefit.hotel_price_promotion_jp,
                hotel_policy_benefit.hotel_price_cancel_policy,hotel_policy_benefit.hotel_price_cancel_policy_jp,
                hotel_policy_benefit.hotel_price_git_cancel_policy,hotel_policy_benefit.hotel_price_git_cancel_policy_jp,
                hotel_policy_benefit.hotel_price_benefit,hotel_policy_benefit.hotel_price_benefit_jp,hotel_policy_benefit.hotel_price_deposit,
                hotel_policy_benefit.hotel_price_deposit_jp,hotel_price_free_lco18';
            }else{
                $select = 'hotel_price_id,hotel_price_category,hotel_price_begin_date,hotel_price_end_date,hotel_price_min_night_charge,
                hotel_price2_sgl AS hotel_price_sgl,hotel_price2_twb AS hotel_price_twb,hotel_price2_extra AS hotel_price_extra,
                hotel_price2_sgl_ov AS hotel_price_sgl_ov,hotel_price2_twb_ov AS hotel_price_twb_ov,hotel_price2_trp_ov AS hotel_price_trp_ov,
                hotel_price2_sgl_1n AS hotel_price_sgl_1n,hotel_price2_twb_1n AS hotel_price_twb_1n,hotel_price2_trp_1n AS hotel_price_trp_1n,
                hotel_price2_sgl_2n AS hotel_price_sgl_2n,hotel_price2_twb_2n AS hotel_price_twb_2n,hotel_price2_trp_2n AS hotel_price_trp_2n,
                hotel_price2_sgl_3n AS hotel_price_sgl_3n,hotel_price2_twb_3n AS hotel_price_twb_3n,hotel_price2_trp_3n AS hotel_price_trp_3n,
                hotel_price_trf_prc AS hotel_price_trf_prc,hotel_price2_trf AS hotel_price_trf,
                hotel_policy_benefit.hotel_price_promotion,hotel_policy_benefit.hotel_price_promotion_jp,
                hotel_policy_benefit.hotel_price_cancel_policy,hotel_policy_benefit.hotel_price_cancel_policy_jp,
                hotel_policy_benefit.hotel_price_git_cancel_policy,hotel_policy_benefit.hotel_price_git_cancel_policy_jp,
                hotel_policy_benefit.hotel_price_benefit,hotel_policy_benefit.hotel_price_benefit_jp,hotel_policy_benefit.hotel_price_deposit,
                hotel_policy_benefit.hotel_price_deposit_jp,hotel_price_free_lco18';
            }
            $mHotelPrice = $this->HotelPriceModel->get_for_detail($rq_data,$select);
        
            if(isset($mHotelPrice)&&!empty($mHotelPrice)){
                $mHotel['hotel_price_summaries'] = array();
                foreach($mHotelPrice as $index=>$ht_price){
                    $ht_price['hotel_price_category'] = trim($ht_price['hotel_price_category']);
                    $skip_flg = false;
                    foreach($rq_data['rq_rooms'] as $rq_room){
                        $temp = 'hotel_price_twb_ov';
                        if ($rq_room['adult_num']) {
                            if($rq_room['adult_num']==1){
                                $temp = 'hotel_price_sgl_ov';
                            }
                            if($rq_room['adult_num']==2){
                                $temp = 'hotel_price_twb_ov';
                            }
                            if($rq_room['adult_num']==3){
                                $temp = 'hotel_price_trp_ov';
                            }
                        }
                        if(!(isset($ht_price[$temp])&&!empty($ht_price[$temp]))){
                            $skip_flg = true;
                        }
                    }
                
                    if($skip_flg)
                        continue;
                
                    if($night<=$ht_price['hotel_price_min_night_charge']){
                        $night = $ht_price['hotel_price_min_night_charge'];
                    }
                
                    $price_name = $ht_price['hotel_price_category'];
                    if(strpos(strtoupper($price_name),'SUP')!=-1){
                        if(strpos(strtoupper($price_name),'SUPERRIOR')!=-1){
                            $price_name = str_replace('SUP','SUPERIOR',strtoupper($price_name));
                        }
                    }
                    if(strpos(strtoupper($price_name),'DLX')!=-1){
                        $price_name = str_replace('DLX','DELUXE',strtoupper($price_name));
                    }
                    if(strpos(strtoupper($price_name),'STD')!=-1){
                        $price_name = str_replace('STD','STANDARD',strtoupper($price_name));
                    }
                
                    $cancel_policy_remark = "";
                    if(!empty($ht_price['hotel_price_cancel_policy']))
                        $cancel_policy_remark.=$ht_price['hotel_price_cancel_policy']."\n";
                    if(!empty($ht_price['hotel_price_git_cancel_policy']))
                        $cancel_policy_remark.=$ht_price['hotel_price_git_cancel_policy']."\n";
                
                    $cancel_policy_remark_jp = "";
                    if(!empty($ht_price['hotel_price_cancel_policy_jp']))
                        $cancel_policy_remark_jp.=$ht_price['hotel_price_cancel_policy_jp']."\n";
                    if(!empty($ht_price['hotel_price_git_cancel_policy_jp']))
                        $cancel_policy_remark_jp.=$ht_price['hotel_price_git_cancel_policy_jp']."\n";
                
                    $hotel_price_summary = array(
                        'price_id'=>$ht_price['hotel_price_id'],
                        'price_cd'=>$ht_price['hotel_price_category'],
                        'price_name'=>trim($price_name),
                        'checkin'=>'',
                        'checkout'=>'',
                        'night'=>'',
                        'lco'=>'',
                        'hotel_price_begin_date'=>date('Y/m/d',$ht_price['hotel_price_begin_date']),
                        'hotel_price_end_date'=>date('Y/m/d',$ht_price['hotel_price_end_date']),
                        'meal_flg'=>1,
                        'meal_name'=>'Buffet - Breakfast',
                        //update later
                        'avail_flg'=>0,
                        'lco_flg'=>isset($ht_price['hotel_price_free_lco18'])?$ht_price['hotel_price_free_lco18']:0,
                        'promotion_remark'=>$ht_price['hotel_price_promotion'],
                        'promotion_remark_jp'=>nl2br($ht_price['hotel_price_promotion_jp']),
                        'benefit_remark'=>nl2br($ht_price['hotel_price_benefit']),
                        'benefit_remark_jp'=>nl2br($ht_price['hotel_price_benefit_jp']),
                        'deposit_remark'=>nl2br($ht_price['hotel_price_deposit']),
                        'deposit_remark_jp'=>nl2br($ht_price['hotel_price_deposit_jp']),
                        'cancel_policy_remark'=>nl2br($cancel_policy_remark),
                        'cancel_policy_remark_jp'=>nl2br($cancel_policy_remark_jp),
                        'min_night_charge'=>$ht_price['hotel_price_min_night_charge'],
                        //update later
                        'average_price'=>0,
                        'total_price'=>0,
                        'sgl_price'=>ceil($ht_price['hotel_price_sgl_ov']*$currency_rate),
                        'twb_price'=>ceil($ht_price['hotel_price_twb_ov']*$currency_rate)
                    );
                
                    if(!$BK_FLG){
                        unset($hotel_price_summary['price_id']);
                    }
                
                    if(isset($ht_price['hotel_price_trp_ov'])&&!empty($ht_price['hotel_price_trp_ov'])){
                        $hotel_price_summary['trp_price'] = ceil($ht_price['hotel_price_trp_ov']*$currency_rate);
                    }
                
                    if($BK_FLG===true){
                        $hotel_price_summary['sgl_price_ct'] = $ht_price['hotel_price_sgl']*$currency_rate;
                        $hotel_price_summary['twb_price_ct'] = $ht_price['hotel_price_twb']*$currency_rate;
                        $hotel_price_summary['extra_price_ct'] = $ht_price['hotel_price_extra']*$currency_rate;
                    }
                
                    //hotel cancelation
                    if(isset($arr_cancel_temp)&&!empty($arr_cancel_temp)){
                        $hotel_price_summary['cancelations'] = array();
                    }
                    $checkin = '';
                    $checkout = '';
                
                    if(isset($rq_data['rq_rooms'])&&!empty($rq_data['rq_rooms'])){
                        $sum_avail_flg = 1;
                        foreach($rq_data['rq_rooms'] as $key=>$rq_room){
                            $hotel_price_summary['hotel_price_details'][$key]['meal_flg'] = 1;
                            $hotel_price_summary['hotel_price_details'][$key]['meal_name'] = 'Buffet - Breakfast';
                            $hotel_price_summary['hotel_price_details'][$key]['adult_num'] = isset($rq_room['adult_num'])&&!empty($rq_room['adult_num'])?$rq_room['adult_num']:0;
                            $hotel_price_summary['hotel_price_details'][$key]['child_num'] = isset($rq_room['child_num'])&&!empty($rq_room['child_num'])?$rq_room['child_num']:0;
                            if(isset($rq_room['child_num'])&&!empty($rq_room['child_num'])){
                                for($i=1;$i<=$rq_room['child_num'];$i++){
                                    $hotel_price_summary['hotel_price_details'][$key]["child_age_$i"] = $rq_room["child_age_$i"];
                                }
                            }
                            $avail_flg = 1;
                            //caculate price
                            $_ng = 0;
                        
                            $price_key = '';
                            switch(intval($rq_room['adult_num'])){
                                case 1:
                                    $price_key = 'hotel_price_sgl_ov';
                                    break;
                                case 2:
                                    $price_key = 'hotel_price_twb_ov';
                                    break;
                                case 3:
                                    $price_key = 'hotel_price_trp_ov';
                                    break;
                            }
                        
                            for($i=0;$i<$night;$i++){
                                $date = strtotime("+$i day",$rq_data['checkin_date']);
                                if($date>=$ht_price['hotel_price_begin_date']&&$date<=(strtotime("+1 day",$ht_price['hotel_price_end_date'])-3601)){
                                
                                    if(empty($checkin)){
                                        $checkin = date('Y/m/d',$date);
                                    }
                                    $checkout = date('Y/m/d',strtotime("+1 day",$date));
                                
                                    $_ng++;
                                    $_avil_flg = 0;
                                    $temp = isset($arr_allot[$mHotel['hotel_id']][$ht_price['hotel_price_category']])?$arr_allot[$mHotel['hotel_id']][$ht_price['hotel_price_category']]:array();
                                    if(!empty($temp)){
                                        $today = strtotime("+$i day",strtotime(date('Y/m/d',time())));
                                        $_date = date('Y/m/d',$date);
                                        if(isset($temp[$_date]['cut_off_date'])){
                                            if(strtotime('+'.$temp[$_date]['cut_off_date'].' day',$today)<=$date){
                                                if(isset($temp[$_date])&&!empty($temp[$_date])){
                                                    if(($temp[$_date]['room_level']-$key)>0&&strtotime('-'.$temp[$_date]['room_level'].' day',$rq_data['checkin_date'])>=strtotime(date('Y/m/d',time()))){
                                                        $_avil_flg = 1;
                                                    }else{
                                                        $_avil_flg = 0;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                
                                    $avail_flg &= $_avil_flg;
                                
                                    $night_charge = array(
                                        'date'=>date('Y/m/d',$date),
                                        'price'=>ceil($ht_price["$price_key"]*$currency_rate)*$rq_room['adult_num'],
                                        'avail_flg'=>$_avil_flg
                                    );
                                    $hotel_price_summary['hotel_price_details'][$key]['night_charges'][] = $night_charge;
                                }
                            }
                        
                            $rq_lco = $lco12;
                            if(isset($rq_data["lco"])&&strpos($rq_data["lco"],":")!==false){
                                $rq_lco = strtotime($rq_data["lco"]);
                            }else{
                                $rq_lco = strtotime($rq_data["lco"].":00");
                            }
                        
                            if($rq_lco>$lco12 && $rq_lco<=$lco18){
                                $night_charge = array(
                                    'date'=>$night_charge["date"],
                                    'price'=>ceil($night_charge["price"]/2),
                                    'avail_flg'=>$night_charge["avail_flg"]
                                );
                                $hotel_price_summary['hotel_price_details'][$key]['night_charges'][] = $night_charge;
                            }else if($rq_lco>$lco18){
                                $hotel_price_summary['hotel_price_details'][$key]['night_charges'][] = $night_charge;
                            }
                        
                            $sum_avail_flg&=$avail_flg;
                            //update average price later
                            $hotel_price_summary['hotel_price_details'][$key]['average_price'] = 0;
                            $hotel_price_summary['hotel_price_details'][$key]['total_price'] = 0;
                            $hotel_price_summary['hotel_price_details'][$key]['avail_flg'] = $avail_flg;
                            $hotel_price_summary['hotel_price_details'][$key]['lco_flg'] = isset($ht_price['hotel_price_free_lco18'])?($ht_price['hotel_price_free_lco18']):0;
                        }
                        $hotel_price_summary['avail_flg'] = $sum_avail_flg;
                        $hotel_price_summary['checkin'] = $checkin;
                        $hotel_price_summary['checkout'] = $checkout;
                        $hotel_price_summary['night'] = $_ng;
                        $hotel_price_summary['lco'] = isset($rq_data['lco'])?$rq_data['lco']:12;
                    }
                
                    //update trf combine price
                    if(isset($rq_data['trf_type'])&&!empty($rq_data['trf_type'])){
                        $car_type = (isset($rq_data['car_type'])&&!empty($rq_data['car_type']))?$rq_data['car_type']:0;
                        //trf private car
                        $trf_total_price = 0;
                        $transfer_price_summary = array(
                            'trf_total_price'=>0,
                            'trf_average_price'=>0,
                            'trf_name'=>'Transfer Roundtrip Airport to Hotel',
                        );
                    
                        if($cus_num>30){
                            $car_num = intval($cus_num/30);
                            $remain_cus = intval($cus_num%30);
                            if(isset($trf_info)&&!empty($trf_info)){
                                $guide_price = (!empty($trf_info['tour_pricegit_guide'])?$trf_info['tour_pricegit_guide']:$trf_info['tour_pricefit_guide']);
                                $trf_total_price = $car_num*ceil(($trf_info['tour_price45seats']+$guide_price)/$this->TOUR_CHARGE);
                            }
                            $car_detail[0] = array(
                                'car_type'=>($car_type==1)?'Private':'Combine',
                                'car_name'=>'Samco',
                                'car_seat'=>45,
                                'car_num'=>$car_num,
                                'cus_num'=>30,
                                'price'=>ceil($trf_total_price*$currency_rate)
                            );
                        
                            $car_seat = $this->getCarSeat($remain_cus);
                            if(isset($trf_info)&&!empty($trf_info)){
                                if($car_seat<29){
                                    //FIT
                                    if($car_type==1){
                                        //private car
                                        $guide_price = $trf_info['tour_pricefit_guide'];
                                        $trf_more_price = (($trf_info["tour_price".$car_seat."seats"]+$guide_price)/$this->TOUR_CHARGE);
                                    }else{
                                        //combine car
                                        $trf_more_price = ($ht_price['hotel_price_trf']*$remain_cus);
                                    }
                                }else{
                                    //GIT
                                    //private car
                                    $guide_price = (!empty($trf_info['tour_pricegit_guide'])?$trf_info['tour_pricegit_guide']:$trf_info['tour_pricefit_guide']);
                                    $trf_more_price = (($trf_info["tour_price".$car_seat."seats"]+$guide_price)/$this->TOUR_CHARGE);
                                }
                                $car_detail[1] = array(
                                    'car_type'=>($car_type==1)?'Private':'Combine',
                                    'car_name'=>'Samco',
                                    'car_seat'=>$car_seat,
                                    'car_num'=>1,
                                    'cus_num'=>$remain_cus,
                                    'price'=>ceil($trf_more_price*$currency_rate)
                                );
                                $trf_total_price+=$trf_more_price;
                            }
                        }else{
                            $car_seat = $this->getCarSeat($cus_num);
                            //wrong
                            /*if(isset($trf_info)&&!empty($trf_info)){
                                if($car_seat<29){
                                    //FIT
                                    if($car_type==1){
                                        //private car
                                        $guide_price = $trf_info['tour_pricefit_guide'];
                                        $trf_total_price = (($trf_info["tour_price".$car_seat."seats"]+$guide_price)/TOUR_CHARGE);
                                    }else{
                                        //combine car
                                        $trf_total_price = ($ht_price['hotel_price_trf']*$cus_num);
                                    }
                                }else{
                                    //GIT
                                    //private car
                                    $guide_price = (!empty($trf_info['tour_pricegit_guide'])?$trf_info['tour_pricegit_guide']:$trf_info['tour_pricefit_guide']);
                                    $trf_total_price = (($trf_info["tour_price".$car_seat."seats"]+$guide_price)/TOUR_CHARGE);
                                }
                                $car_detail[0] = array(
                                    'car_type'=>($car_type==1)?'Private':'Combine',
                                    'car_name'=>'Samco',
                                    'car_seat'=>$car_seat,
                                    'car_num'=>1,
                                    'cus_num'=>$cus_num,
                                    'price'=>ceil($trf_total_price*$currency_rate)
                                );
                            }*/
                        
                            if($car_type==1){
                                //private car
                                $trf_total_price = ((!empty($ht_price['hotel_price_trf_prc'])?$ht_price['hotel_price_trf_prc']:$ht_price['hotel_price_trf'])*$cus_num);
                            }else{
                                //combine car
                                $trf_total_price = ($ht_price['hotel_price_trf']*$cus_num);
                            }
                        }
                    
                        $transfer_price_summary['trf_total_price'] = ceil($trf_total_price*$currency_rate);
                        $transfer_price_summary['trf_average_price'] = ceil($transfer_price_summary['trf_total_price']/$cus_num);
                    
                        if(isset($car_detail)&&!empty($car_detail)){
                            //trf cancelation
                            $transfer_price_summary['cancelations'][0] = array(
                                'remark'=>'No show: 100% of full charge',
                                'remark_jp'=>'いいえ泊：フル充電の100％',
                                'from_date'=>date('Y/m/d',$rq_data['checkin_date']),
                                'to_date'=>date('Y/m/d',$rq_data['checkin_date']),
                                'price'=>$transfer_price_summary['trf_total_price'],
                                'for'=>'FIT,GIT'
                            );
                        
                            $transfer_price_summary['transfer_price_details'] = $car_detail;
                        }
                        $hotel_price_summary['transfer_price_summary'] = $transfer_price_summary;
                    }
                    $mHotel['hotel_price_summaries'][] = $hotel_price_summary;
                }
            }
        
            if(isset($mHotel['hotel_price_summaries'])&&!empty($mHotel['hotel_price_summaries'])){
                foreach($mHotel['hotel_price_summaries'] as $sm_key=>$sm_list){
                
                    if(isset($sm_list['hotel_price_details'])&&!empty($sm_list['hotel_price_details'])){
                        $isValid = true;
                        $total_price = 0;
                    
                        foreach($sm_list['hotel_price_details'] as $dt_key=>$hotel_price_detail){
                            $total_rom_price = 0;
                            if(isset($hotel_price_detail['night_charges'])&&!empty($hotel_price_detail['night_charges'])){
                                foreach($hotel_price_detail['night_charges'] as $night_charge){
                                    $total_rom_price+= $night_charge['price'];
                                    $total_price+=$night_charge['price'];
                                }
                            }else{
                                unset($mHotel['hotel_price_summaries'][$sm_key]);
                                $isValid = false;
                                break;
                            }
                            $sm_list['hotel_price_details'][$dt_key]['average_price'] = ceil($total_rom_price/$cus_num);
                            $sm_list['hotel_price_details'][$dt_key]['total_price'] = ceil($total_rom_price);
                        }
                    
                        //no price summaries clear
                        if(!$isValid)
                            continue;
                    
                        //cong them tien don tien
                        $total_price+=isset($sm_list['transfer_price_summary']['trf_total_price'])?$sm_list['transfer_price_summary']['trf_total_price']:0;
                        $mHotel['hotel_price_summaries'][$sm_key] = $sm_list;
                        $mHotel['hotel_price_summaries'][$sm_key]['average_price'] = ceil($total_price/$cus_num);
                        $mHotel['hotel_price_summaries'][$sm_key]['total_price'] = ceil($total_price);
                    
                        if(isset($arr_cancel_temp)&&!empty($arr_cancel_temp)){
                            foreach($arr_cancel_temp as $cc_key=>$cancel){
                                if($cancel['night_charge']==0){
                                    $charge_price = $total_price*($cancel['charge']/100);
                                }else{
                                    $room_per_night = $total_price/$night;
                                    $charge_price = ($room_per_night*$cancel['night_charge'])*($cancel['charge']/100);
                                }
                            
                                $str = 'FIT,GIT';
                                if(isset($cancel['isGIT'])){
                                    switch($cancel['isGIT']){
                                        case 0:
                                            $str = 'FIT';
                                            break;
                                        case 1:
                                            $str = 'GIT';
                                            break;
                                        case 2:
                                            $str = 'FIT,GIT';
                                            break;
                                    }
                                }
                            
                                $mHotel['hotel_price_summaries'][$sm_key]['cancelations'][$cc_key] = array(
                                    'remark'=>$cancel['hotel_cancel_remark'],
                                    'remark_jp'=>$cancel['hotel_cancel_remark_jp'],
                                    'from_date'=>(isset($cancel['max_day'])&&$cancel['max_day']!=-1)?date('Y/m/d',strtotime('-'.$cancel['max_day'].' day',$rq_data['checkin_date'])):'All Date',
                                    'to_date'=>(isset($cancel['min_day'])&&$cancel['min_day']!=-1)?date('Y/m/d',strtotime('-'.$cancel['min_day'].' day',$rq_data['checkin_date'])):'All Date',
                                    'price'=>ceil($charge_price),
                                    'for'=>$str
                                );
                            }
                        }
                    }
                }
            }
            if(isset($mHotel['hotel_id'])){
                if(!$BK_FLG){
                    $mHotel['hotel_cd'] = $mHotel['hotel_id'];
                    unset($mHotel['hotel_id']);
                    unset($mHotel['city_id']);
                }
            }
        }
        return $mHotel;
    }

    public function getTariffValid($rq_data){
        return true;
    }

    public function getTariff($rq_data,$rq_user){
        
        if(!$this->getTariffValid($rq_data)){
            return false;
        }
        
        $mlist = array();

        if(isset($rq_user["agent_code"]) && !empty($rq_user["agent_code"])){
            $rq_data["agent_code"] = $rq_user["agent_code"];
        }else{
            $rq_data["agent_code"] = $rq_data["search_agent"];
        }

        $rq_data['tbk_flg'] = false;
        if(in_array($rq_user["agent_code"],$this->mTBK)){
            $rq_data['tbk_flg'] = true;
        }

        if(isset($rq_data['search_season']) && !empty($rq_data['search_season'])){
            $rq_data['search_season'] = json_decode($rq_data['search_season'],true);
        }

        if(isset($rq_data['search_place']) && !empty($rq_data['search_place'])){
            $rq_data['search_place'] = json_decode($rq_data['search_place'],true);
        }

        if(isset($rq_data['search_star']) && !empty($rq_data['search_star'])){
            $rq_data['search_star'] = json_decode($rq_data['search_star'],true);
        }

        $rq_data["limit_period"] = strtotime($this->ConfigurationModel->get_value_by_key("LIMIT_PERIOD_HOTEL_AGENT"))+82800;
        if (isset($rq_data['search_season']) && !empty($rq_data['search_season'])) {
            if (isset($rq_data['search_year']) && !empty($rq_data['search_year'])) {
                if (count($rq_data['search_season']) == 2) {
                    $begin_date = strtotime($rq_data['search_year'] . '/04/01');
                    $end_date = strtotime(($rq_data['search_year'] + 1) . '/03/31');
                } else {
                    if ($rq_data['search_season'][0] == 0) {
                        $begin_date = strtotime($rq_data['search_year'] . '/04/01');
                        $end_date = strtotime($rq_data['search_year'] . '/09/30');
                    } else if ($rq_data['search_season'][0] == 1) {
                        $begin_date = strtotime($rq_data['search_year'] . '/10/01');
                        $end_date = strtotime(($rq_data['search_year'] + 1) . '/03/31');
                    }
                }
                //local
                $begin_date = $begin_date - 86400;
                $end_date = $end_date + 86400;

                $rq_data["begin_date"] = $begin_date;
                $rq_data["end_date"] = $end_date;

                $rq_data["begin_date_ka"] = strtotime($rq_data['search_year'] . '/04/01') - 86400;
                $rq_data["end_date_ka"] = strtotime($rq_data['search_year'] . '/09/30') + 86400;
            }
        }

        switch ($rq_data['search_tariff']) {
            case 0:
                //get full price
                $mlist = $this->__getTariffFull($rq_data);
                break;
            case 1:
                //get basic price order star
                $mlist = $this->__getTariffBasic($rq_data);
                break;
            case 2:
                //get hotel has promotion
                $rq_data["key"] = 1;
                $mlist = $this->__getTariffPolicyBenefit($rq_data);
                break;
            case 3:
                //get hotel has benefit
                $rq_data["key"] = 2;
                $mlist = $this->__getTariffPolicyBenefit($rq_data);
                break;
            case 4:
                //get hotel has cancel policy
                $rq_data["key"] = 3;
                $mlist = $this->__getTariffPolicyBenefit($rq_data);
                break;
            case 5:
                //get hotel has galadinner
                $mlist = $this->__getTariffGaladinner($rq_data);
                break;
            case 6:
                //get hotel has family price
                $mlist = $this->__getTariffFamily($rq_data);
                break;
            case 8:
                //get basic price order by price (min to max)
                $rq_data["order_rate"] = true;
                $mlist = $this->__getTariffBasic($rq_data);
                break;
        }

        $mNote = array();
        if ($rq_data["search_tariff"] == 0 || $rq_data["search_tariff"] == 1 || $rq_data["search_tariff"] == 8) {
            if(isset($rq_data["search_place"])&&!empty($rq_data["search_place"])&&is_array($rq_data["search_place"])){
                $this->db->in("place_id",$rq_data["search_place"]);
                $mPlace = $this->PlaceModel->get("","place_id,place_code");
                if(!empty($mPlace)){
                    foreach($mPlace as $key=>$val){
                        $mNote[$val["place_code"]] = '<p style="font-size:1em;"><b>&lt;料金表について&gt;</b></p>';

                        if ($val["place_code"] == 'VTAU' || $val["place_code"] == 'VCA' || $val["place_code"] == 'SAPA') {
                            $mNote[$val["place_code"]] .= '<p style="font-size:1em;color:red;font-weight: bold">
                                                                ① ２名１室または３名１室利用時のお一人様あたりのホテル代金のみとなります。（空港～ホテルまでの往復送迎代金、ガイド代金は含みません）
                                                            </p>';
                        }else{
                            $mNote[$val["place_code"]] .= '<p style="font-size:1em;">
                                ① 空港～ホテルまでの往復混乗送迎を含む２名１室または３名１室利用時のお一人様あたりの代金になります';
                            if ($val["place_code"] == 'DLI' || $val["place_code"] == 'PQC' || $val["place_code"] == 'KOS'){
                                $mNote[$val["place_code"]] .= '<span class="note">(往復送迎はホテルドライバーがご案内します)</span>';
                            }
                            $mNote[$val["place_code"]] .= '</p>';
                        }
                        $mNote[$val["place_code"]] .= '<p style="font-size:1em;">② TWBから一人部屋希望の場合は宿泊数×TWB延泊代を加算下さい。</p>';

                        if($val["place_code"] == 'DAD'){
                            $mNote[$val["place_code"]] .= '<p style="font-size:1em;">
															③ ダナンのホテルにてレイトチェックアウト１８時迄をご希望する場合、混雑状況により１泊分チャージになる場合が御座いますので、ご希望されるお客様は事前に予約スタッフにご確認下さい<br>
															<span style="color:red;font-weight:bold">※ホテルカテゴリーは弊社（サザンブリーズ）基準です。</span>
														</p>';
                        }else{
                            $mNote[$val["place_code"]] .= '<p style="font-size:1em;">
															③ 18時までのレイトチェックアウトは延泊料金の半額、出発まで部屋利用の場合は1泊分が必要となります(SGL利用の場合は倍額)<br>';
                            if ($val["place_code"] == 'VTAU'){
                                $mNote[$val["place_code"]] .= '④ホーチミンの空港および市内からブンタウエリアホテルまでの往復送迎打金（ドライバーのみ）は、90USD/PAXとなります。（1名様の場合は、180USD/PAXとなります)。<br>';
                            }
                            $mNote[$val["place_code"]] .= '<span style="color:red;font-weight:bold">※ホテルカテゴリーは弊社（サザンブリーズ）基準です。</span>
														</p>';
                        }

                        if($val["place_code"] == 'RGN'){
                            $mNote[$val["place_code"]] .= '<p style="font-size:1em;color:red;">KA250,KA252でご到着されるお客様は、+25Uusd/グループの追加代金が発生します。（片道のみ）</p>';
                        }
                        if ($val["place_code"] == 'NYU') {
                            $mNote[$val["place_code"]] .= '<p style="font-size:1em;color:red;">ヤンゴン～バガン間の往復航空券代金は260Usd/Pax(諸経費込）となります。</p>';
                        }
                        if ($val["place_code"] == 'HEH') {
                            $mNote[$val["place_code"]] .= '<p style="font-size:1em;color:red;">RGN-HEHの国内線は往復230Usd/Paxとなります。<br/>送迎は英語ガイドでのご案内となります。</p>';
                        }
                        if ($val["place_code"] == 'SGN') {
                            $mNote[$val["place_code"]] .= '<p style="font-size:1em;color:red;"> "専用車での往復空港送迎をご希望の場合は、お一人様につき+Usd30/Paxとなります。<br/>
																※１名参加の場合は+60Usd/Paxとなります<br />
																国内線をご利用される場合はお一人様につき７ドルのサーチャージを加算致します。"</p>';
                        }
                        if ($val["place_code"] == 'HAN') {
                            $mNote[$val["place_code"]] .= '<p style="font-size:1em;color:red;"> "専用車での往復空港送迎をご希望の場合は、お一人様につき+Usd40/Paxとなります。 <br/>
															※１名参加の場合は+80Usd/Paxとなります<br />
															国内線をご利用される場合はお一人様につき７ドルのサーチャージを加算致します。"
														</p>';
                        }
                        if ($val["place_code"] == 'DAD') {
                            $mNote[$val["place_code"]] .= '<p style="font-size:1em;color:red;"> "専用車での往復空港送迎をご希望の場合は、お一人様につき+Usd25/Paxとなります。<br/>
															※１名参加の場合は+50Usd/Paxとなります<br />
															国内線をご利用される場合はお一人様につき７ドルのサーチャージを加算致します。"
														</p>';
                            $mNote[$val["place_code"]] .= '<p style="font-size:1em;color:red;"> "ピークシーズンやベトナム祭日、花火大会開催日の際は別途料金でサーチャージが発生します。<br/>
                                                            その期間のご予約は予約事業部のスタッフにお問い合わせを頂ますようお願い申し上げます。<br />
                                                            2018年のダナン花火大会開催予定日は、04月30日、05月26日、06月02日、06月09日、06月30日となりますが、スケジュールは変更となる場合がございます。<br/>
                                                            また、ホテルによってサーチャージ発生日は異なります為、ご予約時にご確認をお願いいたします。"
                                                        </p>';
                        }
                        if ($val["place_code"] == 'HOIA') {
                            $mNote[$val["place_code"]] .= '<p style="font-size:1em;color:red;"> "専用車での往復空港送迎をご希望の場合は、お一人様につき+Usd20/Paxとなります。 <br/>
															※１名参加の場合は+40Usd/Paxとなります"
														</p>';
                        }
                        if ($val["place_code"] == 'HUI') {
                            $mNote[$val["place_code"]] .= '<p style="font-size:1em;color:red;"> "専用車での往復空港送迎をご希望の場合は、お一人様につき+Usd20/Paxとなります。<br/>
															※１名参加の場合は+40Usd/Paxとなります"
														</p>';
                        }
                        if ($val["place_code"] == 'NHA') {
                            $mNote[$val["place_code"]] .= '<p style="font-size:1em;color:red;"> "専用車での往復空港送迎をご希望の場合は、お一人様につき+Usd15/Paxとなります。<br/>
															※１名参加の場合は+30Usd/Paxとなります"
														</p>';
                        }
                        if ($val["place_code"] == 'REP') {
                            $mNote[$val["place_code"]] .= '<p style="font-size:1em;color:red;"> "専用車での往復空港送迎をご希望の場合は、お一人様につき+Usd15/Paxとなります。 <br/>
															※１名参加の場合は+30Usd/Paxとなります"
														</p>';
                        }
                        if ($val["place_code"] == 'PNH') {
                            $mNote[$val["place_code"]] .= '<p style="font-size:1em;color:red;"> "専用車での往復空港送迎をご希望の場合は、お一人様につき+Usd17/Paxとなります。 <br/>
															※１名参加の場合は+34Usd/Paxとなります"
														</p>';
                        }
                        if ($val["place_code"] == 'VTE') {
                            $mNote[$val["place_code"]] .= '<p style="font-size:1em;color:red;"> "専用車での往復空港送迎をご希望の場合は、お一人様につき+Usd28/Paxとなります。<br/>
															※１名参加の場合は+56Usd/Paxとなります"
														</p>';
                        }
                        if ($val["place_code"] == 'LPQ') {
                            $mNote[$val["place_code"]] .= '<p style="font-size:1em;color:red;"> "専用車での往復空港送迎をご希望の場合は、お一人様につき+Usd20/Paxとなります。<br/>
															※１名参加の場合は+40Usd/Paxとなります"
														</p>';
                        }
                    }
                }
            }

        }

        $data = array(
            "list"=>(isset($mlist['list'])&&!empty($mlist['list'])?$mlist['list']:false),
            "history"=>(isset($mlist['history'])&&!empty($mlist['history'])?$mlist['history']:false),
            "note"=>(isset($mNote)&&!empty($mNote)?$mNote:false)
        );
        return $data;
    }

    private function __getTariffBasic($sess_search){
        $mlist = array();

        //get and format dotoclass
        $mdoto = $this->DotoclassModel->get_tariff_full($sess_search);
        if (isset($mdoto) && !empty($mdoto)) {
            foreach ($mdoto as $id_doto => $doto) {
                $arr = array(
                    'hotel_id' => ((-1) * $doto['doto_class_category']),
                    'place_id' => $doto['doto_class_place_id'],
                    'hotel_star' => $doto['doto_class_category'],
                    'place_code' => $doto['doto_class_place_code'],
                    'hotel_name' => $doto['doto_class_category_name'],
                    'hotel_temp_name' => $doto['doto_class_hotel_name'],
                    'hotel_name_jp' => $doto['doto_class_category_name'],
                    'hotel_price_begin_date' => $doto['doto_class_begin'],
                    'hotel_price_end_date' => $doto['doto_class_end'],
                    'hotel_price_sgl_1n' => $this->markup($doto['doto_class_rate_sgl_1n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                    'hotel_price_twb_1n' => $this->markup($doto['doto_class_rate_twb_1n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                    'hotel_price_trp_1n' => $this->markup($doto['doto_class_rate_trp_1n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                    'hotel_price_sgl_2n' => $this->markup($doto['doto_class_rate_sgl_2n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                    'hotel_price_twb_2n' => $this->markup($doto['doto_class_rate_twb_2n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                    'hotel_price_trp_2n' => $this->markup($doto['doto_class_rate_trp_2n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                    'hotel_price_sgl_3n' => $this->markup($doto['doto_class_rate_sgl_3n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                    'hotel_price_twb_3n' => $this->markup($doto['doto_class_rate_twb_3n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                    'hotel_price_trp_3n' => $this->markup($doto['doto_class_rate_trp_3n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                    'hotel_price_sgl_ov' => $this->markup($doto['doto_class_rate_sgl_ov'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                    'hotel_price_twb_ov' => $this->markup($doto['doto_class_rate_twb_ov'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                    'hotel_price_trp_ov' => $this->markup($doto['doto_class_rate_trp_ov'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                    'hotel_price_extra' => -1,
                    'hotel_price_min_night_charge' => 1,
                    'hotel_price_free_lco18' => 0,
                    'hotel_price_free_lco18_2' => 0,
                    'hotel_price_category' => 'ROH',
                    'hotel_price_benefit' => '',
                    'hotel_price_promotion' => '',
                    'hotel_price_cancel_policy' => '',
                    'hotel_price_git_cancel_policy' => '',
                    'hotel_price_deposit' => '',
                    'hotel_price_promotion_flg'=>0,
                    'hotel_price_promotion'=>'',
                    'hotel_price_promotion_for_jp'=>'',
                    'hotel_price_promotion_file_url'=>'',
                    'hotel_price_note' => '<p style="font-size:1em;color:red;">ピークシーズンやベトナム祭日、花火大会開催日の際は別途料金でサーチャージが発生します。</br>
                                            その期間のご予約は予約事業部のスタッフにお問い合わせを頂ますようお願い申し上げます。</p>'
                );
                $mlist[] = $arr;
            }
        }

        //get and format hotel

        if (in_array($sess_search['search_agent'], $this->mTBK)) {
            $select_agent = 'hotel_price_extra,hotel_price_sgl_1n,hotel_price_twb_1n,hotel_price_trp_1n,
        hotel_price_sgl_2n,hotel_price_twb_2n,hotel_price_trp_2n,
        hotel_price_sgl_3n,hotel_price_twb_3n,hotel_price_trp_3n,
        hotel_price_sgl_only,hotel_price_twb_only,hotel_price_trp_only,
        hotel_price_sgl_ov,hotel_price_twb_ov,hotel_price_trp_ov,
        hotel_price_free_lco18,hotel_price_free_lco18_2';
        } else {
            $select_agent = 'hotel_price_extra,
        hotel_price_sgl_1n AS hotel_price_sgl_1n,hotel_price_twb_1n AS hotel_price_twb_1n,hotel_price_trp_1n AS hotel_price_trp_1n,
        hotel_price_sgl_2n AS hotel_price_sgl_2n,hotel_price_twb_2n AS hotel_price_twb_2n,hotel_price_trp_2n AS hotel_price_trp_2n,
        hotel_price_sgl_3n AS hotel_price_sgl_3n,hotel_price_twb_3n AS hotel_price_twb_3n,hotel_price_trp_3n AS hotel_price_trp_3n,
        hotel_price_sgl_only AS hotel_price_sgl_only,hotel_price_twb_only AS hotel_price_twb_only,hotel_price_trp_only AS hotel_price_trp_only,
        hotel_price_sgl_ov AS hotel_price_sgl_ov,hotel_price_twb_ov AS hotel_price_twb_ov,hotel_price_trp_ov AS hotel_price_trp_ov,
        hotel_price_free_lco18,hotel_price_free_lco18_2';
        }

        $select = 'hotel.hotel_id,hotel.place_id,hotel.hotel_star,hotel.place_code,hotel_name,hotel_name_jp,hotel_price.hotel_price_id,
        hotel_price_begin_date,hotel_price_end_date,hotel_price_min_night_charge,' . $select_agent . ',hotel_price_category,promotion,booking_room_most'
            .',promotion_flg,file_url,hotel_price_promotion_category,hotel_price_promotion,hotel_price_promotion_for_jp';

        $mHotel = $this->HotelModel->get_tariff_basic($sess_search, $select);
        //format policy
        $arr_ht_id = array();
        if (isset($mHotel) && !empty($mHotel)) {
            foreach ($mHotel as $hotel) {
                $hotel['hotel_temp_name'] = '';
                $hotel['hotel_price_child_foc'] = '';
                $hotel['hotel_price_child_charge_bf'] = '';
                $hotel['hotel_price_child_amount'] = '';
                if (in_array($hotel['hotel_id'], $arr_ht_id)) {
                    continue;
                } else {
                    $arr_ht_id[] = $hotel['hotel_id'];
                }
            }
        }
        $select_policy = 'hotel_policy_benefit.hotel_price_child_foc,hotel_policy_benefit.hotel_price_child_charge_bf,hotel_policy_benefit.hotel_price_child_amount';
        $rq_temp = array(
            "hotel_id" => $arr_ht_id,
            "begin_date" => $sess_search["begin_date"],
            "end_date" => $sess_search["end_date"],
        );
        $mPolicy = $this->HotelPolicyBenefitModel->get($rq_temp);
        $arr_policy = array();
        if (isset($mPolicy) && !empty($mPolicy)) {
            foreach ($mPolicy as $ht_policy) {
                if($ht_policy['hotel_policy_benefit_begin_date']>=$sess_search['begin_date_ka']
                    && $ht_policy['hotel_policy_benefit_end_date']<=$sess_search['end_date_ka']){
                    $arr_policy[$ht_policy['hotel_id']]['ka'] = $ht_policy;
                }else{
                    $arr_policy[$ht_policy['hotel_id']]['shi'] = $ht_policy;
                }
            }
        }

        $arr_price_id = array();
        if (isset($mHotel) && !empty($mHotel)) {
            //$arr_id = array();
            foreach ($mHotel as $key=>$hotel) {
                $arr_price_id[] = $hotel['hotel_price_id'];
                $hotel_price_child_foc = "";
                $hotel_price_child_charge_bf = "";
                $hotel_price_child_amount = 0;

                $note = "";

                if($hotel['promotion_flg'] == 0){

                    if(intval($hotel['hotel_price_extra']) == 0){
                        $note .= "TRP不可". '<br/>';
                    }

                    if($hotel['hotel_price_begin_date']>=$sess_search['begin_date_ka']
                        && $hotel['hotel_price_end_date']<=$sess_search['end_date_ka']){
                        if (isset($arr_policy[$hotel['hotel_id']]['ka'])) {
                            $hotel_price_child_foc = $arr_policy[$hotel['hotel_id']]['ka']['hotel_price_child_foc'];
                            $hotel_price_child_charge_bf = $arr_policy[$hotel['hotel_id']]['ka']['hotel_price_child_charge_bf'];
                            $hotel_price_child_amount = $arr_policy[$hotel['hotel_id']]['ka']['hotel_price_child_amount'];
                        }
                    }else{
                        if (isset($arr_policy[$hotel['hotel_id']]['shi'])) {
                            $hotel_price_child_foc = $arr_policy[$hotel['hotel_id']]['shi']['hotel_price_child_foc'];
                            $hotel_price_child_charge_bf = $arr_policy[$hotel['hotel_id']]['shi']['hotel_price_child_charge_bf'];
                            $hotel_price_child_amount = $arr_policy[$hotel['hotel_id']]['shi']['hotel_price_child_amount'];
                        }
                    }
                    if(!empty($hotel_price_child_foc)){
                        $note .= '朝食 ' . ($hotel_price_child_foc . ': FOC' . '<br/>');
                    }
                    if(!empty($hotel_price_child_charge_bf)){
                        $note .= '朝食 ' . ($hotel_price_child_charge_bf . ': ' . ceil($hotel_price_child_amount / 0.9) . '$' . '<br/>');
                    }
                }

                if($hotel['hotel_price_free_lco18_2'] == 1){
                    $note .= 'LCO 18:00 from 2 nights up<br/>';
                }

                if($hotel['hotel_price_min_night_charge'] >1 ){
                    $note .= 'COMPULSORY MINIMUM <b style="color:black;">' . intval($hotel['hotel_price_min_night_charge']) . '</b> NIGHTS<br/>';
                }

                if(!empty($hotel['hotel_price_note'])){
                    $note .= $hotel['hotel_price_note'];
                }

                $hotel['hotel_price_begin_date'] = date('m/d', $hotel['hotel_price_begin_date']);
                $hotel['hotel_price_end_date'] = date('m/d', $hotel['hotel_price_end_date']);
                $hotel['hotel_price_remarks'] = $note;
                $hotel['hotel_price_child_foc'] = $hotel_price_child_foc;
                $hotel['hotel_price_child_charge_bf'] = $hotel_price_child_charge_bf;
                $hotel['hotel_price_child_amount'] = $hotel_price_child_amount;
                $ck_onl = !empty($sess_search['cb_hotel_only']) ? "only" : "ov";
                $hotel['hotel_temp_name'] = '';
                $hotel['hotel_price_sgl_1n'] = $this->markup($hotel['hotel_price_sgl_1n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                $hotel['hotel_price_twb_1n'] = $this->markup($hotel['hotel_price_twb_1n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                $hotel['hotel_price_trp_1n'] = $this->markup($hotel['hotel_price_trp_1n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                $hotel['hotel_price_sgl_2n'] = $this->markup($hotel['hotel_price_sgl_2n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                $hotel['hotel_price_twb_2n'] = $this->markup($hotel['hotel_price_twb_2n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                $hotel['hotel_price_trp_2n'] = $this->markup($hotel['hotel_price_trp_2n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                $hotel['hotel_price_sgl_3n'] = $this->markup($hotel['hotel_price_sgl_3n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                $hotel['hotel_price_twb_3n'] = $this->markup($hotel['hotel_price_twb_3n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                $hotel['hotel_price_trp_3n'] = $this->markup($hotel['hotel_price_trp_3n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                $hotel['hotel_price_sgl_ov'] = $this->markup($hotel['hotel_price_sgl_' . $ck_onl], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                $hotel['hotel_price_twb_ov'] = $this->markup($hotel['hotel_price_twb_' . $ck_onl], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                $hotel['hotel_price_trp_ov'] = $this->markup($hotel['hotel_price_trp_' . $ck_onl], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                //$mlist[$mPlace['place_code']][$hotel['hotel_star']][$hotel['hotel_id']]['hotel'][] = $hotel;
                //$mlist[$mPlace['place_code']][$hotel['hotel_star']][$hotel['hotel_id']]['size'] = count($mlist[$mPlace['place_code']][$hotel['hotel_star']][$hotel['hotel_id']]['hotel']);
                $hotel['hotel_price_promotion_flg'] = $hotel['promotion_flg'];
                $hotel['hotel_price_promotion_category'] = $hotel['hotel_price_promotion_category'];
                $hotel['hotel_price_promotion_for_en'] = $hotel['hotel_price_promotion'];
                $hotel['hotel_price_promotion_for_jp'] = $hotel['hotel_price_promotion_for_jp'];
                $hotel['hotel_price_promotion_file_url'] = $hotel['file_url'];
                $mlist['list'][] = $hotel;
            }
        }

        //price history
        if(!empty($arr_price_id)){
            $sess_search['hotel_price_id'] = $arr_price_id;
            $select_his = 'hotel.hotel_name,hotel.hotel_name_jp,hotel.place_id,hotel.place_code,hotel.hotel_star,hotel_price_history.*';
            $mHistory = $this->HotelPriceHistoryModel->get_tariff($sess_search,$select_his);
            if(isset($mHistory) && !empty($mHistory)){
                foreach($mHistory as $key => $val){
                    $arr = array(
                        'hotel_id' => $val['hotel_id'],
                        'hotel_price_id' => $val['hotel_price_id'],
                        'place_id' => $val['place_id'],
                        'hotel_star' => $val['hotel_star'],
                        'place_code' => $val['place_code'],
                        'hotel_name' => $val['hotel_name'],
                        'hotel_temp_name' =>'',
                        'hotel_name_jp' => $val['hotel_name_jp'],
                        'hotel_price_begin_date' => '',
                        'hotel_price_end_date' => '',
                        'hotel_price_sgl_1n' => $this->markup($val['hotel_price_sgl_1n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_twb_1n' => $this->markup($val['hotel_price_twb_1n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_trp_1n' => $this->markup($val['hotel_price_trp_1n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_sgl_2n' => $this->markup($val['hotel_price_sgl_2n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_twb_2n' => $this->markup($val['hotel_price_twb_2n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_trp_2n' => $this->markup($val['hotel_price_trp_2n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_sgl_3n' => $this->markup($val['hotel_price_sgl_3n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_twb_3n' => $this->markup($val['hotel_price_twb_3n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_trp_3n' => $this->markup($val['hotel_price_trp_3n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_sgl_ov' => $this->markup($val['hotel_price_sgl_ov'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_twb_ov' => $this->markup($val['hotel_price_twb_ov'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_trp_ov' => $this->markup($val['hotel_price_trp_ov'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_sgl_only' => $this->markup($val['hotel_price_sgl_ho'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_twb_only' => $this->markup($val['hotel_price_sgl_ho'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_trp_only' => $this->markup($val['hotel_price_sgl_ho'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_extra' => -1,
                        'hotel_price_min_night_charge' => 1,
                        'hotel_price_free_lco18' => 0,
                        'hotel_price_free_lco18_2' => 0,
                        'hotel_price_category' => '',
                        'hotel_price_benefit' => '',
                        'hotel_price_promotion' => '',
                        'hotel_price_cancel_policy' => '',
                        'hotel_price_git_cancel_policy' => '',
                        'hotel_price_deposit' => '',
                        'hotel_price_note' => '',
                        'updated_time' => $val['updated_at'],
                        'hotel_price_promotion_flg'=>0,
                        'hotel_price_promotion_category'=>'',
                        'hotel_price_promotion_for_en'=>'',
                        'hotel_price_promotion_for_jp'=>'',
                        'hotel_price_promotion_file_url'=>''
                    );
                    $mlist['history'][] = $arr;
                }
            }
        }

        return $mlist;
    }

    private function __getTariffFull($sess_search){
        $mlist = array();

        //get and format dotoclass
        $mdoto = $this->DotoclassModel->get_tariff_full($sess_search);

        //format data dotoclass
        if (isset($mdoto) && !empty($mdoto)) {
            foreach ($mdoto as $id_doto => $doto) {
                $arr = array(
                    'hotel_id' => ((-1) * $doto['doto_class_category']),
                    'place_id' => $doto['doto_class_place_id'],
                    'hotel_star' => $doto['doto_class_category'],
                    'place_code' => $doto['doto_class_place_code'],
                    'hotel_name' => $doto['doto_class_category_name'],
                    'hotel_temp_name' => $doto['doto_class_hotel_name'],
                    'hotel_name_jp' => $doto['doto_class_category_name'],
                    'hotel_price_begin_date' => $doto['doto_class_begin'],
                    'hotel_price_end_date' => $doto['doto_class_end'],
                    'hotel_price_sgl_1n' => $this->markup($doto['doto_class_rate_sgl_1n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                    'hotel_price_twb_1n' => $this->markup($doto['doto_class_rate_twb_1n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                    'hotel_price_trp_1n' => $this->markup($doto['doto_class_rate_trp_1n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                    'hotel_price_sgl_2n' => $this->markup($doto['doto_class_rate_sgl_2n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                    'hotel_price_twb_2n' => $this->markup($doto['doto_class_rate_twb_2n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                    'hotel_price_trp_2n' => $this->markup($doto['doto_class_rate_trp_2n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                    'hotel_price_sgl_3n' => $this->markup($doto['doto_class_rate_sgl_3n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                    'hotel_price_twb_3n' => $this->markup($doto['doto_class_rate_twb_3n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                    'hotel_price_trp_3n' => $this->markup($doto['doto_class_rate_trp_3n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                    'hotel_price_sgl_ov' => $this->markup($doto['doto_class_rate_sgl_ov'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                    'hotel_price_twb_ov' => $this->markup($doto['doto_class_rate_twb_ov'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                    'hotel_price_trp_ov' => $this->markup($doto['doto_class_rate_trp_ov'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                    'hotel_price_sgl_only' => 0,
                    'hotel_price_twb_only' => 0,
                    'hotel_price_trp_only' => 0,
                    'hotel_price_extra' => -1,
                    'hotel_price_min_night_charge' => 1,
                    'hotel_price_free_lco18' => 0,
                    'hotel_price_free_lco18_2' => 0,
                    'hotel_price_category' => 'ROH',
                    'hotel_price_benefit' => '',
                    'hotel_price_promotion' => '',
                    'hotel_price_cancel_policy' => '',
                    'hotel_price_git_cancel_policy' => '',
                    'hotel_price_deposit' => '',
                    'hotel_price_note' => '<p style="font-size:1em;color:red;">ピークシーズンやベトナム祭日、花火大会開催日の際は別途料金でサーチャージが発生します。</br>
                                            その期間のご予約は予約事業部のスタッフにお問い合わせを頂ますようお願い申し上げます。</p>',
                    'hotel_price_promotion_flg'=>0,
                    'hotel_price_promotion'=>'',
                    'hotel_price_promotion_for_jp'=>'',
                    'hotel_price_promotion_file_url'=>''
                );
                $mlist[] = $arr;
            }
        }

        //get and format hotel
        if (in_array($sess_search['search_agent'], $this->mTBK)) {
            $select_agent = 'hotel_price_extra,hotel_price_sgl_1n,hotel_price_twb_1n,hotel_price_trp_1n,
        hotel_price_sgl_2n,hotel_price_twb_2n,hotel_price_trp_2n,
        hotel_price_sgl_3n,hotel_price_twb_3n,hotel_price_trp_3n,
        hotel_price_sgl_only,hotel_price_twb_only,hotel_price_trp_only,
        hotel_price_sgl_ov,hotel_price_twb_ov,hotel_price_trp_ov,
        hotel_price_free_lco18,hotel_price_free_lco18_2';
        } else {
            $select_agent = 'hotel_price_extra,
        hotel_price_sgl_1n AS hotel_price_sgl_1n,hotel_price_twb_1n AS hotel_price_twb_1n,hotel_price_trp_1n AS hotel_price_trp_1n,
        hotel_price_sgl_2n AS hotel_price_sgl_2n,hotel_price_twb_2n AS hotel_price_twb_2n,hotel_price_trp_2n AS hotel_price_trp_2n,
        hotel_price_sgl_3n AS hotel_price_sgl_3n,hotel_price_twb_3n AS hotel_price_twb_3n,hotel_price_trp_3n AS hotel_price_trp_3n,
        hotel_price_sgl_only AS hotel_price_sgl_only,hotel_price_twb_only AS hotel_price_twb_only,hotel_price_trp_only AS hotel_price_trp_only,
        hotel_price_sgl_ov AS hotel_price_sgl_ov,hotel_price_twb_ov AS hotel_price_twb_ov,hotel_price_trp_ov AS hotel_price_trp_ov,
        hotel_price_free_lco18,hotel_price_free_lco18_2';
        }

        $select = 'hotel.hotel_id,hotel.place_id,hotel.hotel_star,hotel.place_code,hotel_name,hotel_name_jp,hotel_price.hotel_price_id,
        hotel_price_begin_date,hotel_price_end_date,hotel_price_min_night_charge,' . $select_agent . ',hotel_price_category,promotion,booking_room_most'
            .',promotion_flg,file_url,hotel_price_promotion_category,hotel_price_promotion,hotel_price_promotion_for_jp';
        $mHotel = $this->HotelModel->get_tariff_full($sess_search, $select);
        //format policy
        $arr_ht_id = array();
        if (isset($mHotel) && !empty($mHotel)) {
            foreach ($mHotel as $hotel) {
                $hotel['hotel_temp_name'] = '';
                $hotel['hotel_price_child_foc'] = '';
                $hotel['hotel_price_child_charge_bf'] = '';
                $hotel['hotel_price_child_amount'] = '';
                if (in_array($hotel['hotel_id'], $arr_ht_id)) {
                    continue;
                } else {
                    $arr_ht_id[] = $hotel['hotel_id'];
                }
            }
        }

        $select_policy = 'hotel_policy_benefit_begin_date,hotel_policy_benefit_end_date,hotel_policy_benefit.hotel_price_child_foc,hotel_policy_benefit.hotel_price_child_charge_bf,hotel_policy_benefit.hotel_price_child_amount';
        $rq_temp = array(
            "hotel_id" => $arr_ht_id,
            "begin_date" => $sess_search["begin_date"],
            "end_date" => $sess_search["end_date"]
        );
        $mPolicy = $this->HotelPolicyBenefitModel->get($rq_temp);
        $arr_policy = array();
        if (isset($mPolicy) && !empty($mPolicy)) {
            foreach ($mPolicy as $ht_policy) {
                if($ht_policy['hotel_policy_benefit_begin_date']>=$sess_search['begin_date_ka']
                    && $ht_policy['hotel_policy_benefit_end_date']<=$sess_search['end_date_ka']){
                    $arr_policy[$ht_policy['hotel_id']]['ka'] = $ht_policy;
                }else{
                    $arr_policy[$ht_policy['hotel_id']]['shi'] = $ht_policy;
                }
            }
        }

        $arr_price_id = array();
        if (isset($mHotel) && !empty($mHotel)) {
            //$arr_id = array();
            foreach ($mHotel as $key=>$hotel) {
                $arr_price_id[] = $hotel['hotel_price_id'];
                $hotel_price_child_foc = "";
                $hotel_price_child_charge_bf = "";
                $hotel_price_child_amount = 0;

                $note = "";

                if($hotel['promotion_flg'] == 0){

                    if(intval($hotel['hotel_price_extra']) == 0){
                        $note .= "TRP不可". '<br/>';
                    }

                    if($hotel['hotel_price_begin_date']>=$sess_search['begin_date_ka']
                        && $hotel['hotel_price_end_date']<=$sess_search['end_date_ka']){
                        if (isset($arr_policy[$hotel['hotel_id']]['ka'])) {
                            $hotel_price_child_foc = $arr_policy[$hotel['hotel_id']]['ka']['hotel_price_child_foc'];
                            $hotel_price_child_charge_bf = $arr_policy[$hotel['hotel_id']]['ka']['hotel_price_child_charge_bf'];
                            $hotel_price_child_amount = $arr_policy[$hotel['hotel_id']]['ka']['hotel_price_child_amount'];
                        }
                    }else{
                        if (isset($arr_policy[$hotel['hotel_id']]['shi'])) {
                            $hotel_price_child_foc = $arr_policy[$hotel['hotel_id']]['shi']['hotel_price_child_foc'];
                            $hotel_price_child_charge_bf = $arr_policy[$hotel['hotel_id']]['shi']['hotel_price_child_charge_bf'];
                            $hotel_price_child_amount = $arr_policy[$hotel['hotel_id']]['shi']['hotel_price_child_amount'];
                        }
                    }
                    if(!empty($hotel_price_child_foc)){
                        $note .= '朝食 ' . ($hotel_price_child_foc . ': FOC' . '<br/>');
                    }
                    if(!empty($hotel_price_child_charge_bf)){
                        $note .= '朝食 ' . ($hotel_price_child_charge_bf . ': ' . ceil($hotel_price_child_amount / 0.9) . '$' . '<br/>');
                    }
                }

                if($hotel['hotel_price_free_lco18_2'] == 1){
                    $note .= 'LCO 18:00 from 2 nights up<br/>';
                }

                if($hotel['hotel_price_min_night_charge'] >1 ){
                    $note .= 'COMPULSORY MINIMUM <b style="color:black;">' . intval($hotel['hotel_price_min_night_charge']) . '</b> NIGHTS<br/>';
                }

                if(!empty($hotel['hotel_price_note'])){
                    $note .= $hotel['hotel_price_note'];
                }

                $hotel['hotel_price_begin_date'] = date('m/d', $hotel['hotel_price_begin_date']);
                $hotel['hotel_price_end_date'] = date('m/d', $hotel['hotel_price_end_date']);
                $hotel['hotel_price_remarks'] = $note;
                $hotel['hotel_price_child_foc'] = $hotel_price_child_foc;
                $hotel['hotel_price_child_charge_bf'] = $hotel_price_child_charge_bf;
                $hotel['hotel_price_child_amount'] = $hotel_price_child_amount;
                $ck_onl = !empty($sess_search['cb_hotel_only']) ? "only" : "ov";
                $hotel['hotel_temp_name'] = '';
                $hotel['hotel_price_sgl_1n'] = $this->markup($hotel['hotel_price_sgl_1n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                $hotel['hotel_price_twb_1n'] = $this->markup($hotel['hotel_price_twb_1n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                $hotel['hotel_price_trp_1n'] = $this->markup($hotel['hotel_price_trp_1n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                $hotel['hotel_price_sgl_2n'] = $this->markup($hotel['hotel_price_sgl_2n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                $hotel['hotel_price_twb_2n'] = $this->markup($hotel['hotel_price_twb_2n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                $hotel['hotel_price_trp_2n'] = $this->markup($hotel['hotel_price_trp_2n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                $hotel['hotel_price_sgl_3n'] = $this->markup($hotel['hotel_price_sgl_3n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                $hotel['hotel_price_twb_3n'] = $this->markup($hotel['hotel_price_twb_3n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                $hotel['hotel_price_trp_3n'] = $this->markup($hotel['hotel_price_trp_3n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                $hotel['hotel_price_sgl_ov'] = $this->markup($hotel['hotel_price_sgl_' . $ck_onl], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                $hotel['hotel_price_twb_ov'] = $this->markup($hotel['hotel_price_twb_' . $ck_onl], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                $hotel['hotel_price_trp_ov'] = $this->markup($hotel['hotel_price_trp_' . $ck_onl], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                //$mlist[$mPlace['place_code']][$hotel['hotel_star']][$hotel['hotel_id']]['hotel'][] = $hotel;
                //$mlist[$mPlace['place_code']][$hotel['hotel_star']][$hotel['hotel_id']]['size'] = count($mlist[$mPlace['place_code']][$hotel['hotel_star']][$hotel['hotel_id']]['hotel']);
                $hotel['hotel_price_promotion_flg'] = $hotel['promotion_flg'];
                $hotel['hotel_price_promotion_category'] = $hotel['hotel_price_promotion_category'];
                $hotel['hotel_price_promotion_for_en'] = $hotel['hotel_price_promotion'];
                $hotel['hotel_price_promotion_for_jp'] = $hotel['hotel_price_promotion_for_jp'];
                $hotel['hotel_price_promotion_file_url'] = $hotel['file_url'];
                $mlist['list'][] = $hotel;
            }
        }

        //price history
        if(!empty($arr_price_id)){
            $sess_search['hotel_price_id'] = $arr_price_id;
            $select_his = 'hotel.hotel_name,hotel.hotel_name_jp,hotel.place_id,hotel.place_code,hotel.hotel_star,hotel_price_history.*';
            $mHistory = $this->HotelPriceHistoryModel->get_tariff($sess_search,$select_his);
            if(isset($mHistory) && !empty($mHistory)){
                foreach($mHistory as $key => $val){
                    $arr = array(
                        'hotel_id' => $val['hotel_id'],
                        'hotel_price_id' => $val['hotel_price_id'],
                        'place_id' => $val['place_id'],
                        'hotel_star' => $val['hotel_star'],
                        'place_code' => $val['place_code'],
                        'hotel_name' => $val['hotel_name'],
                        'hotel_temp_name' =>'',
                        'hotel_name_jp' => $val['hotel_name_jp'],
                        'hotel_price_begin_date' => '',
                        'hotel_price_end_date' => '',
                        'hotel_price_sgl_1n' => $this->markup($val['hotel_price_sgl_1n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_twb_1n' => $this->markup($val['hotel_price_twb_1n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_trp_1n' => $this->markup($val['hotel_price_trp_1n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_sgl_2n' => $this->markup($val['hotel_price_sgl_2n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_twb_2n' => $this->markup($val['hotel_price_twb_2n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_trp_2n' => $this->markup($val['hotel_price_trp_2n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_sgl_3n' => $this->markup($val['hotel_price_sgl_3n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_twb_3n' => $this->markup($val['hotel_price_twb_3n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_trp_3n' => $this->markup($val['hotel_price_trp_3n'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_sgl_ov' => $this->markup($val['hotel_price_sgl_ov'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_twb_ov' => $this->markup($val['hotel_price_twb_ov'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_trp_ov' => $this->markup($val['hotel_price_trp_ov'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_sgl_only' => $this->markup($val['hotel_price_sgl_ho'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_twb_only' => $this->markup($val['hotel_price_sgl_ho'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_trp_only' => $this->markup($val['hotel_price_sgl_ho'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']),
                        'hotel_price_extra' => -1,
                        'hotel_price_min_night_charge' => 1,
                        'hotel_price_free_lco18' => 0,
                        'hotel_price_free_lco18_2' => 0,
                        'hotel_price_category' => '',
                        'hotel_price_benefit' => '',
                        'hotel_price_promotion' => '',
                        'hotel_price_cancel_policy' => '',
                        'hotel_price_git_cancel_policy' => '',
                        'hotel_price_deposit' => '',
                        'hotel_price_note' => '',
                        'updated_time' => $val['updated_at'],
                        'hotel_price_promotion_flg'=>0,
                        'hotel_price_promotion_category'=>'',
                        'hotel_price_promotion_for_en'=>'',
                        'hotel_price_promotion_for_jp'=>'',
                        'hotel_price_promotion_file_url'=>''
                    );
                    $mlist['history'][] = $arr;
                }
            }
        }
        return $mlist;
    }

    private function __getTariffPolicyBenefit($sess_search){
        //get data from database
        $select = 'hotel.hotel_id,hotel.place_id,hotel.hotel_star,hotel.place_code,hotel_name,hotel_name_jp,
                    hotel_price_benefit_jp AS hotel_price_benefit,hotel_price_promotion_jp AS hotel_price_promotion,hotel_price_cancel_policy,
                    hotel_price_git_cancel_policy,hotel_price_deposit,hotel_price_note,hotel_policy_benefit_begin_date,hotel_policy_benefit_end_date';
        $mlist['list'] = $this->HotelModel->get_tariff_policy_benefit($sess_search, $select);

        return $mlist;
    }

    private function __getTariffGaladinner($sess_search){
        $mlist = array();

        //get data from database
        $select = 'hotel.hotel_id,hotel.place_id,hotel.hotel_star,hotel.place_code,hotel_name,hotel_name_jp,hotel_galadinner.*';
        $mGala = $this->HotelModel->get_tariff_galadinner($sess_search, $select);

        //format data
        if (isset($mGala) && !empty($mGala)) {
            foreach ($mGala as $key=>$gala) {
                if ($gala['hotel_galadinner_charge_type'] == 0) {
                    $gala['adult_sell_price'] = ceil($gala['hotel_galadinner_adult_price'] / ((100 - $gala['hotel_galadinner_charge_value']) / 100));
                    $gala['child_sell_price'] = ceil($gala['hotel_galadinner_child_price'] / ((100 - $gala['hotel_galadinner_charge_value']) / 100));
                } else {
                    $gala['adult_sell_price'] = ceil($gala['hotel_galadinner_adult_price'] + $gala['hotel_galadinner_charge_value']);
                    $gala['child_sell_price'] = ceil($gala['hotel_galadinner_child_price'] + $gala['hotel_galadinner_charge_value']);
                }
                $gala['adult_sell_price'] = $this->markup($gala['adult_sell_price'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                $gala['child_sell_price'] = $this->markup($gala['child_sell_price'], $sess_search['mark_value'], $sess_search['mark_type'], $sess_search['currency']);
                $mlist['list'][] = $gala;
            }
        }
        return $mlist;
    }

    private function __getTariffFamily(){
        return array();
    }
}