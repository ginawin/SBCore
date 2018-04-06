<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:34 AM
 */

class BookingUtil extends Util
{
    public function __construct()
    {
        parent::__construct();
        
        $this->loadModel("HotelModel","HotelPriceModel","TourModel","TourPriceModel","BookingModel","BookingTbkModel",
            "BookingHotelModel","BookingHotelToModel", "BookingTourModel","BookingTourDetailsModel","BookingPriceModel",
            "BookingHistoryModel","HotelCancelationModel","BookingContactModel","CountryQModel","PlaceModel","BookingRqXmlModel");
    
        $this->loadUtil("HotelUtil","TourUtil");

        $this->booking_type = array(
            "booking_hotel_only"
            ,"booking_tour_only"
            ,"booking_trf_only"
            ,"booking_alert_only"
            ,"booking_lou_only"
            ,"booking_stand_day"
            ,"booking_golf_bag"
            ,"booking_vip"
            ,"booking_repeat_guest"
            ,"booking_guide_during_tour"
            ,"booking_inspection_tour"
            ,"booking_transfer_airport_pickup"
            ,"booking_business"
            ,"booking_no_smk_room"
            ,"booking_discus"
            ,"booking_photo"
            ,"booking_sight_see"
            ,"booking_private_car"
            ,"booking_halong_boat"
            ,"booking_bus_tour"
            ,"booking_business_class_flight"
            ,"booking_shuttle_bus"
            ,""//"chk_guide"
            ,"booking_handicapped_person"
            ,"booking_k6_air_flight"
            ,""//"chk_restaurant_booking"
        );

        $this->fake_place = array(
            "HOI" => "HOIA"
        );
        
        $this->RATE_D2Y = 122;
    
        $rq_user['user_cd'] = "tanthanh";
        $rq_user['user_pass'] = "4f1a681b8f16ff18c9b1fa0e7b324dcf";
        $rq_user['user_email'] = "tanthanh@ginawin.com";
        $rq_user['user_name'] = "Thanh San";
        $rq_user['agent_code'] = "OLNT";
    
        $iData = array(
            'user_name' => $rq_user['user_cd'],
            'user_pass' => $rq_user['user_pass']
        );
        $this->user_info = $this->UserModel->getOne($iData);
    
        $this->user_info["agent_staff_email"] = !empty($rq_user['user_email'])?$rq_user['user_email']:"";
        $this->user_info["agent_staff_name"] = !empty($rq_user['user_name'])?$rq_user['user_name']:(!empty($this->user_info['agent_staff_name'])?$this->user_info['agent_staff_name']:$this->user_info['user_name']);
    
        if($this->user_info['user_level'] != 4){
            if(!empty($rq_user['agent_code'])){
                $iData = array(
                    "agent_code" => $rq_user['agent_code']
                );
                $mAgent = $this->AgentModel->getOne($iData,"agent_id,agent_code");
                if(!empty($mAgent)){
                    $this->user_info['agent_id'] = $mAgent['agent_id'];
                    $this->user_info['agent_code'] = $mAgent['agent_code'];
                }else{
                    $this->user_info['agent_id'] = 0;
                    $this->user_info['agent_code'] = $rq_user['agent_code'];
                }
            }
        }
    }
    
    public function createValid($rq_data){
        return true;
    }

    public function create($rq_data)
    {
        //fake city booking
        if(!empty($rq_data['stay_city'])){
            $arr_city = explode(",",trim($rq_data['stay_city']));
            $arr_city_tbk = array_keys($this->fake_place);
            if(!empty($arr_city)){
                foreach($arr_city as $key => $val){
                    if(in_array($val,$arr_city_tbk)){
                        $arr_city[$key] = $this->_get_LPN_place($val);
                    }
                }
            }
            $rq_data['stay_city'] = implode(",",$arr_city);
        }

        if(isset($rq_data['rq_hotels']) && !empty($rq_data['rq_hotels'])){
            foreach($rq_data['rq_hotels'] as $key => $val){
                $rq_data['rq_hotels'][$key]['city_cd'] = $this->_get_LPN_place($rq_data['rq_hotels'][$key]['city_cd']);
            }
        }

        if(isset($rq_data['rq_tours']) && !empty($rq_data['rq_tours'])){
            foreach($rq_data['rq_tours'] as $key => $val){
                if(isset($val['booking_tour_details']) && !emppty($val['booking_tour_details'])){
                    foreach($val['booking_tour_details'] as $key1 => $val1){
                        $rq_data['rq_tours'][$key]['rq_tour_details'][$key1]['city_cd'] = $this->_get_LPN_place($rq_data['rq_tours'][$key]['rq_tour_details'][$key1]['city_cd']);
                    }
                }
            }
        }

        if(isset($rq_data['rq_flights']) && !empty($rq_data['rq_flights'])){
            foreach($rq_data['rq_flights'] as $key => $val){
                $rq_data['rq_flights'][$key]['dep'] = $this->_get_LPN_place($rq_data['rq_flights'][$key]['dep']);
                $rq_data['rq_flights'][$key]['arr'] = $this->_get_LPN_place($rq_data['rq_flights'][$key]['arr']);
            }
        }

        if(isset($rq_data['rq_places']) && !empty($rq_data['rq_places'])){
            foreach($rq_data['rq_places'] as $key => $val){
                $rq_data['rq_places'][$key]['city_cd'] = $this->_get_LPN_place($rq_data['rq_places'][$key]['city_cd']);
                $rq_data['rq_places'][$key]['place_cd'] = $this->_get_LPN_place($rq_data['rq_places'][$key]['place_cd']);
            }
        }

        //////////////////////////////////////////////////

        $booking_code = '';

        if(isset($rq_data['booking_cd'])&&!empty($rq_data['booking_cd'])){
            //delete old booking
            $booking_code = $rq_data['booking_cd'];
            $del_flg = $this->__delete($booking_code,$this->user_info);
            if(!$del_flg){
                return false;
            }
        }
        

        if(isset($rq_data['agent_booking_cd'])&&!empty($rq_data['agent_booking_cd'])){
            if($this->user_info['user_level'] == 4){
                $this->db->where("agent_code",$this->user_info['user_name']);
            }
            $this->db->where("agent_booking_code",$rq_data['agent_booking_cd']);
            $mBK = $this->BookingModel->get("","booking_id,booking_code,booking_status");
            //future maybe change to edit function
            if(!empty($mBK)){

                $data = array(
                    'booking_id'=>$mBK[0]["booking_id"],
                    'booking_cd'=>$mBK[0]["booking_code"],
                    'booking_status'=>self::getBKStatus($mBK[0]["booking_status"]),
                    'booking_status_id'=>$mBK[0]["booking_status"],
                    'message'=>'Booking is already exist',
                );

                $this->db->where("booking_id",$mBK[0]["booking_id"]);
                $mBKHotel = $this->BookingHotelModel->get("","rsv_arr_id,hotel_code AS item_code,SF_GET_BK_CONFIRM_STATUS(bh_request_status) AS status");
                if(!empty($mBKHotel))
                    $data["booking_hotel"] = $mBKHotel;

                $this->db->where("booking_id",$mBK[0]["booking_id"]);
                $mBKTour = $this->BookingTourModel->get("","booking_tour_id");
                if(!empty($mBKTour)){
                    $arr_bk_tour_id = array();
                    foreach($mBKTour as $key=>$val){
                        $arr_bk_tour_id[] = $val["booking_tour_id"];
                    }
                    if(!empty($arr_bk_tour_id)){
                        $this->db->in("booking_tour_id",$arr_bk_tour_id);
                        $this->db->where("(rsv_arr_id <> '' AND rsv_arr_id IS NOT NULL)");
                        $mBKTourDetail = $this->BookingTourDetailsModel->get("","rsv_arr_id,tour_code AS item_code,'OK' AS status");
                        if(!empty($mBKTourDetail))
                            $data["booking_tour"] = $mBKTourDetail;
                    }
                }
                return $data;
            }
        }

        $str_cus = '';
        if(isset($rq_data['rq_passengers'])&&!empty($rq_data['rq_passengers'])){
            foreach($rq_data['rq_passengers'] as $rq_passenger){
                $str_cus .=  strtoupper($rq_passenger['first_name']).' '.strtoupper($rq_passenger['middle_name']).' '
                    .strtoupper($rq_passenger['last_name']).strtoupper((($rq_passenger['gender_type']=='M')?' MR':' MS'))
                    ."&zwj;" .' '. $rq_passenger['age'] . "&zwj;&#10;";
            }
        }

        $isHtOnly = 0;
        if(isset($rq_data['rq_hotels'])&&!empty($rq_data['rq_hotels'])){
            if(!(isset($rq_data['rq_tours'])&&!empty($rq_data['rq_tours']))){
                $isHtOnly = 1;
            }
        }

        $isToOnly = 0;
        if(isset($rq_data['rq_places'])&&!empty($rq_data['rq_places'])){
            $isToOnly = 1;
        }

        if($booking_code==''){
            $booking_code = $this->BookingModel->getAutoBKCode((!empty($this->user_info['agent_code'])?$this->user_info['agent_code']:'OLNH'),$rq_data['booking_date']);
        }

        $arr_city = array();
        if(isset($rq_data['stay_city'])&&!empty($rq_data['stay_city'])){
            $arr_city = explode(",",trim($rq_data['stay_city']));
        }

        $booking_from5 = array();
        $booking_from5_hide = array();
        if(!empty($arr_city)){
            foreach($arr_city as $city){
                $city_id = $this->PlaceModel->getIdByCode($city);

                if(empty($city_id))
                    continue;
                $booking_from5[] = $city_id."_".$city;
                $booking_from5_hide[] = $city_id."_".$city;
            }
        }

        $booking = array(
            "user_id" => $this->user_info['user_id'],
            "user_name" => $this->user_info['user_name'],
            "user_id_modified" => $this->user_info['user_id'],
            "user_name_modified" => $this->user_info['user_name'],
            "date_modified" => SBDate::Now2Int(),
            "agent_id" => $this->user_info['agent_id'],
            "agent_code" => $this->user_info['agent_code'],
            "agent_staff_email" => $this->user_info['agent_staff_email'],
            "agent_staff_name" => $this->user_info['agent_staff_name'],
            "booking_code" => $booking_code,
            "agent_booking_code" => $rq_data['agent_booking_cd'],
            "booking_title" => $rq_data['booking_title'],
            "booking_date" => SBDate::Str2Int($rq_data['booking_date'], 'Y/m/d', '/', 0, 0, 0),
            "customers_name" => $str_cus,
            "booking_num_person" => $rq_data['adult_num'],
            "booking_num_child11" => $rq_data['child_num'],
            "booking_note" => $rq_data['note'],
            "booking_status" => 1,
            "booking_type_change" => 0,
            "booking_date_create" => SBDate::Now2Int(),
            "booking_tour_only" => $isToOnly,
            "booking_hotel_only" => $isHtOnly,
            "rq_passenger" => isset($rq_data['rq_passengers'])?json_encode($rq_data['rq_passengers']):"",
            "booking_from5" => !empty($booking_from5)?implode(",",$booking_from5):"",
            "booking_from5_hide" => !empty($booking_from5_hide)?implode(",",$booking_from5_hide):"",
            "is_from_agent" => 1
        );
        if(isset($rq_data['booking_type'])&&!empty($rq_data['booking_type'])){
            $temp = explode(",",$rq_data['booking_type']);
            if(!empty($temp)){
                foreach($temp as $key=>$val){
                    $field = isset($this->booking_type[$val])?$this->booking_type[$val]:"";
                    $booking[$field] = 1;
                }
            }
        }
        $this->BookingModel->insertOne($booking);

        $bk_id = $this->db->lastInsertId();
        
        $temp_xml = array();
        $temp_xml['booking_id'] = $bk_id;
        $temp_xml['booking_code'] = $booking_code;
        $temp_xml['rq_xml'] = (isset($_POST['xml']) && !empty($_POST['xml']))?$_POST['xml']:"";
        $this->BookingRqXmlModel->insertOne($temp_xml);


        //for TBK only
        $RsvArrId = array();

        $rsv_hotel = array();
        $rsv_tour = array();

        //format flight date groupby date
        $temp_flights = array();
        if(isset($rq_data['rq_flights'])&&!empty($rq_data['rq_flights'])){
            foreach($rq_data['rq_flights'] as $rq_flight){
                $temp_flights[$rq_flight['date']][] = $rq_flight;
            }
        }

        //insert hotels
        if(isset($rq_data['rq_hotels'])&&!empty($rq_data['rq_hotels'])){
            foreach($rq_data['rq_hotels'] as $rq_hotel){
                if(!empty($rq_hotel["rsv_arr_id"])){
                    if(!in_array($rq_hotel["rsv_arr_id"],$RsvArrId))
                        $RsvArrId[] = $rq_hotel["rsv_arr_id"];
                }
                $rq_hotel['curr_cd'] = $rq_data['curr_cd'];

                $bk_hotel = $this->_INSERT_BKHotel($bk_id,$rq_hotel,$rsv_hotel,$temp_flights);
            }
        }
        
        //insert flight schedule
        if(isset($rq_data['rq_flights'])&&!empty($rq_data['rq_flights'])){
            foreach($rq_data['rq_flights'] as $rq_flight){
                $bk_flight = $this->_INSERT_BKFlight($bk_id,$rq_flight);
            }
        }
        
        //insert tours
        if(isset($rq_data['rq_tours'])&&!empty($rq_data['rq_tours'])){
            foreach($rq_data['rq_tours'] as $rq_tour){

                if(isset($rq_tour['rq_tour_details'])&&!empty($rq_tour['rq_tour_details'])){
                    foreach($rq_tour['rq_tour_details'] as $rq_tour_detail){
                        if(!empty($rq_tour_detail["rsv_arr_id"])){
                            if(!in_array($rq_tour_detail["rsv_arr_id"],$RsvArrId))
                                $RsvArrId[] = $rq_tour_detail["rsv_arr_id"];
                        }
                    }
                }
                $rq_tour['curr_cd'] = $rq_data['curr_cd'];
                $bk_tour = $this->_INSERT_BKTour($bk_id,$rq_tour,$rsv_tour,$temp_flights);
            }
        }

        //insert place for transfer only or tour only
        if(isset($rq_data['rq_places'])&&!empty($rq_data['rq_places'])){
            foreach($rq_data['rq_places'] as $rq_place){
                $bk_place = $this->_INSERT_BKPlace($bk_id,$rq_place);
            }
        }

        //update history
        $booking_history = array(
            "booking_id" => $bk_id,
            "booking_status" => 1,
            "user_id" => $this->user_info['user_id'],
            "user_name" => $this->user_info['user_name'],
            "booking_date" => SBDate::Now2Int(),
            "booking_note" => 'Created',
            "booking_code" => $booking_code
        );
        $this->BookingHistoryModel->insertOne($booking_history);

        if(!empty($RsvArrId)){
            $booking_tbk = $this->BookingTbkModel->get($rq_data['agent_booking_cd']);
            if(!empty($booking_tbk)){

            }else{
                $booking_tbk = array(
                    "booking_tbk_id"=>$rq_data['agent_booking_cd'],
                    "booking_id"=>$bk_id,
                    "booking_code"=>$booking_code,
                    "booking_tbk_rsvarr_id"=>json_encode($RsvArrId),
                    "agent_id"=>$this->user_info['agent_id'],
                    "agent_code"=>$this->user_info['agent_code'],
                    "staff_name"=>!empty($this->user_info['agent_staff_name'])?$this->user_info['agent_staff_name']:$this->user_info['user_name'],
                    "staff_email"=>$this->user_info['user_email'],
                    "create_time"=>time()
                );
                $this->BookingTbkModel->insertOne($booking_tbk);
            }
        }
        
        $this->_refresh_price($bk_id);

        $data = array(
            'booking_id'=>$bk_id,
            'booking_cd'=>$booking_code,
            'booking_status'=>self::getBKStatus($booking['booking_status']),
            'booking_status_id'=>$booking['booking_status'],
        );

        if(isset($rsv_hotel) && !empty($rsv_hotel))
            $data["booking_hotel"] = $rsv_hotel;
        if(isset($rsv_tour) && !empty($rsv_tour))
            $data["booking_tour"] = $rsv_tour;
    
        
        return $data;
    }

    private function _INSERT_BKHotel($bk_id,$rq_data,&$rsv_hotel,$temp_flights = array()){
        if(isset($rq_data)&&!empty($rq_data)){
            $isTBK = false;
            if (strpos($this->user_info['user_name'],'TBK') !== false) {
                $isTBK = true;
            }
            $mHotel = $this->HotelUtil->getDetailMain($rq_data,$isTBK,true);
            
            if(isset($mHotel['hotel_price_summaries'])&&!empty($mHotel['hotel_price_summaries'])){
                $price_name = strtolower(trim($mHotel['hotel_price_summaries'][0]['price_name']));
                //đếm xem check-in va check-out qua mấy giai đoạn
                $count_season = 0;
                foreach($mHotel['hotel_price_summaries'] as $ht_price){
                    if(strtolower(trim($ht_price['price_name']))==$price_name){
                        $count_season++;
                    }
                }
                //$index_season <> $count_season thì ko add ngày check out
                //$index_season <> 1 thì ko add tour đón ở ngày đầu
                $index_season = 0;
                foreach($mHotel['hotel_price_summaries'] as $ht_price){
                    if(strtolower(trim($ht_price['price_name']))==$price_name){
                        $index_season++;
                        if(isset($ht_price['hotel_price_details'])&&!empty($ht_price['hotel_price_details'])){

                            $checkin = strtotime($ht_price['checkin']);
                            $checkout = strtotime($ht_price['checkout']);

                            if(isset($ht_price['hotel_price_details'])&&!empty($ht_price['hotel_price_details'])){
                                $order_hotel_sgl = $order_hotel_sgl_std = $order_hotel_sgl_plus = $order_hotel_twb =
                                $order_hotel_dbl = $order_hotel_trp = $order_hotel_trp2 = $accounting_hotel_sgl_ct =
                                $accounting_hotel_twb_ct = $accounting_hotel_dbl_ct = $accounting_hotel_trp_ct = '0|0';

                                $cus_num = 0;$child_num = 0;
                                $NUM_SGL_ROOM = $NUM_TWB_ROOM = $NUM_DBL_ROOM = $NUM_TRP_ROOM = 0;
                                //$str_cus = '';
                                foreach($ht_price['hotel_price_details'] as $ht_price_detail){
                                    switch($ht_price_detail['adult_num']){
                                        case 1:
                                            $NUM_SGL_ROOM++;
                                            break;
                                        case 2:
                                            $NUM_TWB_ROOM++;
                                            break;
                                        case 3:
                                            $NUM_TRP_ROOM++;
                                            break;
                                    }
                                    $cus_num += $ht_price_detail['adult_num'];
                                    $child_num += $ht_price_detail['child_num'];
                                }
                                //get sale price
                                if(isset($ht_price['sgl_price'])){
                                    $order_hotel_sgl_std_sale = (($ht_price['sgl_price'] * 2) * $NUM_SGL_ROOM)* $ht_price['night'];
                                    $order_hotel_sgl_sale = (($ht_price['sgl_price'] * 1) * $NUM_SGL_ROOM)* $ht_price['night'];
                                }else{
                                    $order_hotel_sgl_std_sale = 0;
                                    $order_hotel_sgl_sale = 0;
                                }

                                if(isset($ht_price['twb_price'])){
                                    $order_hotel_twb_sale = (($ht_price['twb_price'] * 2) * $NUM_TWB_ROOM)* $ht_price['night'];
                                    $order_hotel_dbl_sale = (($ht_price['twb_price'] * 2) * $NUM_DBL_ROOM)* $ht_price['night'];
                                    $order_hotel_sgl_plus_sale = (($ht_price['twb_price']) * $ht_price['night']);
                                }else{
                                    $order_hotel_twb_sale = 0;
                                    $order_hotel_dbl_sale = 0;
                                    $order_hotel_sgl_plus_sale = 0;
                                }

                                if(isset($ht_price['trp_price'])){
                                    $order_hotel_trp_sale = (($ht_price['trp_price'] * 3) * $NUM_TRP_ROOM)* $ht_price['night'];
                                }else{
                                    $order_hotel_trp_sale = 0;
                                }

                                //format sale price for accounting system
                                $order_hotel_sgl = (!empty($NUM_SGL_ROOM)) ? ($NUM_SGL_ROOM . '|' . $order_hotel_sgl_sale) : '0|0';
                                $order_hotel_sgl_std = (!empty($NUM_SGL_ROOM)) ? ($NUM_SGL_ROOM . '|' . $order_hotel_sgl_std_sale) : '0|0';
                                $order_hotel_sgl_plus = (!empty($NUM_SGL_ROOM)) ? ($NUM_SGL_ROOM . '|' . $order_hotel_sgl_plus_sale) : '0|0';
                                $order_hotel_twb = (!empty($NUM_TWB_ROOM)) ? ($NUM_TWB_ROOM . '|' . $order_hotel_twb_sale) : '0|0';
                                $order_hotel_dbl = (!empty($NUM_DBL_ROOM)) ? ($NUM_DBL_ROOM . '|' . $order_hotel_dbl_sale) : '0|0';
                                $order_hotel_trp = (!empty($NUM_TRP_ROOM)) ? ($NUM_TRP_ROOM . '|' . $order_hotel_trp_sale) : '0|0';
                                $order_hotel_trp2 = '0|0';

                                //get contract price
                                $order_hotel_sgl_ct = ($ht_price['sgl_price_ct'] * $NUM_SGL_ROOM * $ht_price['night']);
                                $order_hotel_twb_ct = ($ht_price['twb_price_ct'] * $NUM_TWB_ROOM * $ht_price['night']);
                                $order_hotel_dbl_ct = ($ht_price['twb_price_ct'] * $NUM_DBL_ROOM * $ht_price['night']);
                                $order_hotel_trp_ct = (($ht_price['twb_price_ct'] + $ht_price['extra_price_ct']) * $NUM_TRP_ROOM * $ht_price['night']);
                                $order_hotel_ext_ct = $ht_price['extra_price_ct'];

                                //format contract price for accounting system
                                $accounting_hotel_sgl_ct = (!empty($NUM_SGL_ROOM)) ? ($NUM_SGL_ROOM . '|' . $order_hotel_sgl_ct) : '0|0';
                                $accounting_hotel_twb_ct = (!empty($NUM_TWB_ROOM)) ? ($NUM_TWB_ROOM . '|' . $order_hotel_twb_ct) : '0|0';
                                $accounting_hotel_dbl_ct = (!empty($NUM_DBL_ROOM)) ? ($NUM_DBL_ROOM . '|' . $order_hotel_dbl_ct) : '0|0';
                                $accounting_hotel_trp_ct = (!empty($NUM_TRP_ROOM)) ? ($NUM_TRP_ROOM . '|' . $order_hotel_trp_ct) : '0|0';

                                //total hotel sale price
                                /*$total_hotel_amount_usd = ( $order_hotel_sgl_sale +
                                                            $order_hotel_twb_sale +
                                                            $order_hotel_dbl_sale +
                                                            $order_hotel_trp_sale);

								if(isset($ht_price['transfer_price_summary']['trf_total_price'])){
									if($rq_data['trf_type'] == 1){
										$total_hotel_amount_usd += $ht_price['transfer_price_summary']['trf_total_price'];
									}else if($rq_data['trf_type'] == 2 || $rq_data['trf_type'] == 3){
										$total_hotel_amount_usd += ceil($ht_price['transfer_price_summary']['trf_total_price']/2);
									}
								}

                                $total_hotel_price_usd = round($total_hotel_amount_usd / $cus_num);
                                $total_hotel_amount_yen = $this->U2Y($total_hotel_amount_usd,$this->RATE_D2Y);
                                $total_hotel_price_yen = $this->U2Y($total_hotel_price_usd,$this->RATE_D2Y);*/

                                $total_hotel_amount_usd = $ht_price['total_price'];
                                $total_hotel_price_usd = $ht_price['average_price'];
                                $total_hotel_amount_yen = $this->U2Y($total_hotel_amount_usd,$this->RATE_D2Y);
                                $total_hotel_price_yen = $this->U2Y($total_hotel_price_usd,$this->RATE_D2Y);

                                $booking_hotel = array();
                                $booking_hotel["booking_id"] = $bk_id;
                                $booking_hotel["user_id"] = $this->user_info['user_id'];
                                $booking_hotel["user_name"] = $this->user_info['user_name'];
                                $booking_hotel["user_id_modified"] = $this->user_info['user_id'];
                                $booking_hotel["user_name_modified"] = $this->user_info['user_name'];
                                $booking_hotel["agent_id"] = $this->user_info['agent_id'];
                                $booking_hotel["agent_code"] = $this->user_info['agent_code'];
                                $booking_hotel["place_id"] = $mHotel['city_id'];
                                $booking_hotel["place_code"] = $mHotel['city_cd'];
                                $booking_hotel["booking_hotel_check_in_date"] = $checkin;
                                $booking_hotel["booking_hotel_num_night"] = $ht_price['night'];
                                $booking_hotel["booking_hotel_check_out_date"] = $checkout;
                                $booking_hotel["booking_hotel_num_room"] = count($ht_price['hotel_price_details']);
                                $booking_hotel["booking_hotel_late_check_out_lco"] = $ht_price['lco'];
                                $booking_hotel["hotel_id"] = $mHotel['hotel_id'];
                                $booking_hotel["hotel_code"] = $mHotel['hotel_cd'];
                                $booking_hotel["hotel_place_code"] = $mHotel['city_cd'];
                                $booking_hotel["hotel_name"] = $mHotel['hotel_name'];
                                $booking_hotel["hotel_transfer"] = (isset($ht_price['transfer_price_summary']) && !empty($ht_price['transfer_price_summary']))?3:0;
                                $booking_hotel["hotel_price_id"] = $ht_price['price_id'];
                                $booking_hotel["hotel_category"] = $ht_price['price_cd'];
                                $booking_hotel["booking_hotel_sgl"] = $order_hotel_sgl;
                                $booking_hotel["booking_hotel_sgl_std"] = $order_hotel_sgl_std;
                                $booking_hotel["booking_hotel_sgl_plus"] = $order_hotel_sgl_plus;
                                $booking_hotel["booking_hotel_twb"] = $order_hotel_twb;
                                $booking_hotel["booking_hotel_dbl"] = $order_hotel_dbl;
                                $booking_hotel["booking_hotel_trp"] = $order_hotel_trp;
                                $booking_hotel["booking_hotel_trp2"] = $order_hotel_trp2;
                                $booking_hotel["booking_hotel_sgl_sale"] = $order_hotel_sgl_sale;
                                $booking_hotel["booking_hotel_sgl_plus_sale"] = $order_hotel_sgl_plus_sale;
                                $booking_hotel["booking_hotel_twb_sale"] = $order_hotel_twb_sale;
                                $booking_hotel["booking_hotel_trp_sale"] = $order_hotel_trp_sale;
                                $booking_hotel["booking_hotel_sgl_ct"] = $order_hotel_sgl_ct;
                                $booking_hotel["booking_hotel_twb_ct"] = $order_hotel_twb_ct;
                                $booking_hotel["booking_hotel_trp_ct"] = $order_hotel_trp_ct;
                                $booking_hotel["booking_hotel_ext_ct"] = $order_hotel_ext_ct;
                                $booking_hotel["booking_hotel_trf_ct"] = isset($ht_price['transfer_price_summary']['trf_total_price'])?$ht_price['transfer_price_summary']['trf_total_price']:0;
                                $booking_hotel["accounting_hotel_sgl_ct"] = $accounting_hotel_sgl_ct;
                                $booking_hotel["accounting_hotel_twb_ct"] = $accounting_hotel_twb_ct;
                                $booking_hotel["accounting_hotel_dbl_ct"] = $accounting_hotel_dbl_ct;
                                $booking_hotel["accounting_hotel_trp_ct"] = $accounting_hotel_trp_ct;
                                $booking_hotel["hotel_promotion"] = isset($ht_price['promotion_remark'])?$ht_price['promotion_remark']:'';
                                $booking_hotel["hotel_promotion_jp"] = isset($ht_price['promotion_remark_jp'])?$ht_price['promotion_remark_jp']:'';
                                $booking_hotel["hotel_cancel_policy"] = isset($ht_price['cancel_policy_remark'])?$ht_price['cancel_policy_remark']:'';
                                $booking_hotel["hotel_cancel_policy_jp"] = isset($ht_price['cancel_policy_remark_jp'])?$ht_price['cancel_policy_remark_jp']:'';
                                $booking_hotel["hotel_cancel_policy_git"] = '';
                                $booking_hotel["hotel_cancel_policy_git_jp"] = '';
                                $booking_hotel["hotel_benefit"] = isset($ht_price['benefit_remark'])?$ht_price['benefit_remark']:'';
                                $booking_hotel["hotel_benefit_jp"] = isset($ht_price['benefit_remark_jp'])?$ht_price['benefit_remark_jp']:'';
                                $booking_hotel["hotel_deposit"] = isset($ht_price['deposit_remark'])?$ht_price['deposit_remark']:'';
                                $booking_hotel["hotel_deposit_jp"] = isset($ht_price['deposit_remark_jp'])?$ht_price['deposit_remark_jp']:'';
                                $booking_hotel["booking_hotel_qty_person"] = $cus_num;
                                $booking_hotel["booking_hotel_price"] = $total_hotel_price_usd;
                                $booking_hotel["booking_hotel_amount"] = $total_hotel_amount_usd;
                                $booking_hotel["booking_hotel_status_confirm"] = 0;
                                $booking_hotel["promotion"] = isset($rq_data['promotion_cd'])?$rq_data['promotion_cd']:"";
                                $booking_hotel["bh_request_status"] = 1;
                                $booking_hotel["bh_booking_status"] = 1;
                                $booking_hotel["booking_hotel_lco_free"] = ($ht_price['lco_flg']) ? 1 : 0;
                                $booking_hotel["rsv_arr_id"] = $rq_data["rsv_arr_id"];
                                $this->BookingHotelModel->insertOne($booking_hotel);
                                $booking_hotel_id = $this->db->lastInsertId();
                                //update allotment
                                if($ht_price["avail_flg"]==1){
                                    $sql =  "UPDATE hotel_rate SET room_level_tbk = room_level_tbk - ".($NUM_SGL_ROOM+$NUM_TWB_ROOM+$NUM_DBL_ROOM+$NUM_TRP_ROOM).
                                        " WHERE FROM_UNIXTIME(hotel_rate_date+3601,'%Y/%m/%d') >= '".$ht_price['checkin']."' AND FROM_UNIXTIME(hotel_rate_date+3601,'%Y/%m/%d') < '".$ht_price['checkout']."' AND hotel_id = ".$mHotel['hotel_id']." AND category = '".trim($ht_price['price_cd'])."'";
                                    $this->db->query($sql);
                                }

                                //insert booking tour
                                $night = $ht_price['night'];

                                if ($night > 0) {
                                    for ($i = 0; $i <= $night; $i++) {
                                        //ko add ngày check out
                                        if($index_season!=$count_season && $count_season>1){
                                            if ($i == $night) {
                                                continue;
                                            }
                                        }
                                        $date = strtotime("+$i day", $checkin);
                                        $_date = date('Y/m/d',$date);
                                        $data = $this->_fm_arr_tour();
                                        $data['booking_id'] = $bk_id;
                                        $data['booking_tour_date'] = $_date;
                                        if ($i == $night) {
                                            $data['booking_hotel_id'] = '';
                                        }else{
                                            $data['booking_hotel_id'] = $booking_hotel_id;
                                        }

                                        $data['agent_id'] = $this->user_info['agent_id'];
                                        $data['agent_code'] = $this->user_info['agent_code'];
                                        $data['place_id_from'] = $mHotel['city_id'];
                                        $data['place_code_from'] = $mHotel['city_cd'];
                                        $booking_tour_id = $this->_add_tour($data);

                                        //2 way or 1 way from airport->hotel
                                        if($rq_data['trf_type'] == 1 || $rq_data['trf_type'] == 2){
                                            if($i==0){
                                                //ko add tour đón ngày check in
                                                if($index_season!=1 && $count_season>1){
                                                    continue;
                                                }
                                                $place_code = str_replace(' ','',$mHotel['city_cd']);
                                                $place_code = substr($place_code,0,3);

                                                //$this->db->where('tour_code',$place_code.'01A');
                                                $temp = array(
                                                    'tour_code' => $place_code.'01A'
                                                );
                                                $mTour = $this->TourModel->getList($temp,'tour_id,tour_name');
                                                
                                                //insert tour don
                                                $data = $this->_fm_arr_tour_detail();
                                                if(isset($temp_flights[$_date]) && !empty($temp_flights[$_date]) && count($temp_flights[$_date]) > 0){
                                                    $length = count($temp_flights[$_date]) - 1;
                                                    $flt = $temp_flights[$_date][$length];
                                                    $place_id_from = $this->PlaceModel->getIdByCode($flt['dep']);
                                                    $place_id_to = $this->PlaceModel->getIdByCode($flt['arr']);
                                                    $data['place_id_from'] = $place_id_from;
                                                    $data['place_code_from'] = $flt['dep'];
                                                    $data['place_id_to'] = $place_id_to;
                                                    $data['place_code_to'] = $flt['arr'];
                                                    $data['flight_no'] = $flt['flight'];
                                                    $data['flight_time'] = $flt['dep_time'];
                                                    $data['flight_time_coming'] = $flt['arr_time'];

                                                    $h = intval($flt['arr_time']);
                                                    $m = intval(substr($flt['arr_time'], -2 , 2))/60;
                                                    $check = $h + $m - 1.1;
                                                    if($check < 0){
                                                        //add tour xuat phat
                                                        $dt = $this->_fm_arr_tour();
                                                        $dt['booking_id'] = $bk_id;
                                                        $dt['booking_tour_date'] = date('Y/m/d',strtotime("-1 day", $date));
                                                        $bk_tour_id = $this->_add_tour($dt);

                                                        $dt = $this->_fm_arr_tour_detail();
                                                        $dt['place_id_from'] = $place_id_from;
                                                        $dt['place_code_from'] = $flt['dep'];
                                                        $dt['place_id_to'] = $place_id_to;
                                                        $dt['place_code_to'] = $flt['arr'];
                                                        $dt['flight_no'] = $flt['flight'];
                                                        $dt['flight_time'] = $flt['dep_time'];
                                                        $dt['flight_time_coming'] = $flt['arr_time'];
                                                        $dt['tour_id'] = 0;
                                                        $dt['tour_code'] = $flt['arr'].'0FREE';
                                                        $dt['tour_place_id'] = $mHotel['city_id'];
                                                        $dt['tour_place_code'] = $mHotel['city_cd'];
                                                        $dt['booking_tour_id'] = $bk_tour_id;
                                                        $dt['booking_tour_details_price'] = 0;
                                                        $dt['booking_tour_details_amount'] = 0;
                                                        $dt['tour_key'] = 8;
                                                        $dt['booking_tour_details_description'] = '出発日';
                                                        $dt['booking_tour_details_description_en'] = 'Departure';
                                                        $dt['adult_num'] = 0;
                                                        $dt['child_num'] = 0;
                                                        $dt['infant_num'] = 0;
                                                        $dt['booking_tour_details_order'] = 0;
                                                        $this->_add_tour_detail($dt);
                                                    }
                                                }else{
                                                    $data['place_id_to'] = $mHotel['city_id'];
                                                    $data['place_code_to'] = $mHotel['city_cd'];
                                                }
                                                $data['tour_id'] = isset($mTour[0]['tour_id'])?$mTour[0]['tour_id']:0;
                                                $data['tour_code'] = $place_code.'01A';
                                                $data['tour_place_id'] = $mHotel['city_id'];
                                                $data['tour_place_code'] = $mHotel['city_cd'];
                                                $data['booking_tour_id'] = $booking_tour_id;
                                                
                                                $data['booking_tour_details_price'] = 0;
                                                $data['booking_tour_details_amount'] = 0;
                                                $data['tour_key'] = 8;
                                                $data['booking_tour_details_description'] = '混載車にて空港からホテルへ送迎';
                                                $data['booking_tour_details_description_en'] = 'Transfer from Airport to Hotel by combine car';
                                                $data['adult_num'] = $cus_num;
                                                $data['child_num'] = $child_num;
                                                $data['infant_num'] = 0;
                                                $data['booking_tour_details_order'] = 1;
                                                $this->_add_tour_detail($data);
                                            }
                                        }

                                        //2 way or 1 way from hotel->airport
                                        if($rq_data['trf_type'] == 1 || $rq_data['trf_type'] == 3){
                                            if($i==$night){
                                                $place_code = str_replace(' ','',$mHotel['city_cd']);
                                                $place_code = substr($place_code,0,3);
                                                $temp = array(
                                                    "tour_code" => $place_code.'01B'
                                                );
                                                $mTour = $this->TourModel->getList($temp,'tour_id,tour_name');

                                                //insert tour tien
                                                $data = $this->_fm_arr_tour_detail();
                                                if(isset($temp_flights[$_date]) && !empty($temp_flights[$_date]) && count($temp_flights[$_date]) > 0){
                                                    $length = 0;
                                                    $flt = $temp_flights[$_date][$length];
                                                    $data['place_id_from'] = $this->place_model->getIdByCode($flt['dep']);
                                                    $data['place_code_from'] = $flt['dep'];
                                                    $data['place_id_to'] = $this->place_model->getIdByCode($flt['arr']);
                                                    $data['place_code_to'] = $flt['arr'];
                                                    $data['flight_no'] = $flt['flight'];
                                                    $data['flight_time'] = $flt['dep_time'];
                                                    $data['flight_time_coming'] = $flt['arr_time'];
                                                }else{
                                                    $data['place_id_to'] = $mHotel['city_id'];
                                                    $data['place_code_to'] = $mHotel['city_cd'];
                                                }
                                                $data['tour_id'] = isset($mTour[0]['tour_id'])?$mTour[0]['tour_id']:0;
                                                $data['tour_code'] = $place_code.'01B';
                                                $data['tour_place_id'] = $mHotel['city_id'];
                                                $data['tour_place_code'] = $mHotel['city_cd'];
                                                $data['booking_tour_id'] = $booking_tour_id;
                                                $data['booking_tour_details_price'] = 0;
                                                $data['booking_tour_details_amount'] = 0;
                                                $data['tour_key'] = 8;
                                                $data['booking_tour_details_description'] = '混載車にてホテルから空港へ送迎';
                                                $data['booking_tour_details_description_en'] = 'Transfer from Hotel to Airport by combine car';
                                                $data['adult_num'] = $cus_num;
                                                $data['child_num'] = $child_num;
                                                $data['infant_num'] = 0;
                                                $data['booking_tour_details_order'] = 0;
                                                $this->_add_tour_detail($data);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }else{
                //dont have hotel price
                $checkin_date = new DateTime($rq_data['checkin_date']);
                $checkout_date = new DateTime($rq_data['checkout_date']);
                $night = $checkout_date->diff($checkin_date)->days;

                $checkin = strtotime($rq_data['checkin_date']);
                $checkout = strtotime($rq_data['checkout_date']);

                $order_hotel_sgl = $order_hotel_sgl_std = $order_hotel_sgl_plus = $order_hotel_twb =
                $order_hotel_dbl = $order_hotel_trp = $order_hotel_trp2 = $accounting_hotel_sgl_ct =
                $accounting_hotel_twb_ct = $accounting_hotel_dbl_ct = $accounting_hotel_trp_ct = '0|0';

                $cus_num = 0;$child_num = 0;
                $NUM_SGL_ROOM = $NUM_TWB_ROOM = $NUM_DBL_ROOM = $NUM_TRP_ROOM = 0;

                foreach($rq_data['rq_rooms'] as $rq_room){
                    switch($rq_room['adult_num']){
                        case 1:
                            $NUM_SGL_ROOM++;
                            break;
                        case 2:
                            $NUM_TWB_ROOM++;
                            break;
                        case 3:
                            $NUM_TRP_ROOM++;
                            break;
                    }
                    $cus_num += $rq_room['adult_num'];
                    $child_num += $rq_room['child_num'];
                }

                //get sale price
                $order_hotel_sgl_std_sale = 0;
                $order_hotel_sgl_sale = 0;

                $order_hotel_twb_sale = 0;
                $order_hotel_dbl_sale = 0;
                $order_hotel_sgl_plus_sale = 0;

                $order_hotel_trp_sale = 0;

                //format sale price for accounting system
                $order_hotel_sgl = (!empty($NUM_SGL_ROOM)) ? ($NUM_SGL_ROOM . '|' . $order_hotel_sgl_sale) : '0|0';
                $order_hotel_sgl_std = (!empty($NUM_SGL_ROOM)) ? ($NUM_SGL_ROOM . '|' . $order_hotel_sgl_std_sale) : '0|0';
                $order_hotel_sgl_plus = (!empty($NUM_SGL_ROOM)) ? ($NUM_SGL_ROOM . '|' . $order_hotel_sgl_plus_sale) : '0|0';
                $order_hotel_twb = (!empty($NUM_TWB_ROOM)) ? ($NUM_TWB_ROOM . '|' . $order_hotel_twb_sale) : '0|0';
                $order_hotel_dbl = (!empty($NUM_DBL_ROOM)) ? ($NUM_DBL_ROOM . '|' . $order_hotel_dbl_sale) : '0|0';
                $order_hotel_trp = (!empty($NUM_TRP_ROOM)) ? ($NUM_TRP_ROOM . '|' . $order_hotel_trp_sale) : '0|0';
                $order_hotel_trp2 = '0|0';

                //get contract price
                $order_hotel_sgl_ct = 0;
                $order_hotel_twb_ct = 0;
                $order_hotel_dbl_ct = 0;
                $order_hotel_trp_ct = 0;
                $order_hotel_ext_ct = 0;

                //format contract price for accounting system
                $accounting_hotel_sgl_ct = (!empty($NUM_SGL_ROOM)) ? ($NUM_SGL_ROOM . '|' . $order_hotel_sgl_ct) : '0|0';
                $accounting_hotel_twb_ct = (!empty($NUM_TWB_ROOM)) ? ($NUM_TWB_ROOM . '|' . $order_hotel_twb_ct) : '0|0';
                $accounting_hotel_dbl_ct = (!empty($NUM_DBL_ROOM)) ? ($NUM_DBL_ROOM . '|' . $order_hotel_dbl_ct) : '0|0';
                $accounting_hotel_trp_ct = (!empty($NUM_TRP_ROOM)) ? ($NUM_TRP_ROOM . '|' . $order_hotel_trp_ct) : '0|0';

                //total hotel sale price
                $total_hotel_amount_usd = ( $order_hotel_sgl_sale +
                    $order_hotel_twb_sale +
                    $order_hotel_dbl_sale +
                    $order_hotel_trp_sale);

                $ht_price = array();
                if(isset($ht_price['transfer_price_summary']['trf_total_price'])){
                    if($rq_data['trf_type'] == 1){
                        $total_hotel_amount_usd += $ht_price['transfer_price_summary']['trf_total_price'];
                    }else if($rq_data['trf_type'] == 2 || $rq_data['trf_type'] == 3){
                        $total_hotel_amount_usd += ceil($ht_price['transfer_price_summary']['trf_total_price']/2);
                    }
                }
                $total_hotel_price_usd = round($total_hotel_amount_usd / $cus_num);

                $total_hotel_amount_yen = $this->U2Y($total_hotel_amount_usd,$this->RATE_D2Y);
                $total_hotel_price_yen = $this->U2Y($total_hotel_price_usd,$this->RATE_D2Y);

                $city_id = isset($mHotel['city_id'])?$mHotel['city_id']:$this->place_model->getIdByCode($rq_data['city_cd']);
                $city_cd = isset($mHotel['city_cd'])?$mHotel['city_cd']:$rq_data['city_cd'];

                $booking_hotel = array();
                $booking_hotel["booking_id"] = $bk_id;
                $booking_hotel["user_id"] = $this->user_info['user_id'];
                $booking_hotel["user_name"] = $this->user_info['user_name'];
                $booking_hotel["user_id_modified"] = $this->user_info['user_id'];
                $booking_hotel["user_name_modified"] = $this->user_info['user_name'];
                $booking_hotel["agent_id"] = $this->user_info['agent_id'];
                $booking_hotel["agent_code"] = $this->user_info['agent_code'];
                $booking_hotel["place_id"] = $city_id;
                $booking_hotel["place_code"] = $city_cd;
                $booking_hotel["booking_hotel_check_in_date"] = $checkin;
                $booking_hotel["booking_hotel_num_night"] = $night;
                $booking_hotel["booking_hotel_check_out_date"] = $checkout;
                $booking_hotel["booking_hotel_num_room"] = count($rq_data['rq_rooms']);
                $booking_hotel["booking_hotel_late_check_out_lco"] = $rq_data['lco'];
                $booking_hotel["hotel_id"] = isset($mHotel['hotel_id'])?$mHotel['hotel_id']:(isset($rq_data["hotel_cd"])?$rq_data["hotel_cd"]:"");
                $booking_hotel["hotel_code"] = isset($mHotel['hotel_cd'])?$mHotel['hotel_cd']:(isset($rq_data["hotel_cd"])?$rq_data["hotel_cd"]:"");
                $booking_hotel["hotel_place_code"] = isset($mHotel['city_cd'])?$mHotel['city_cd']:"";
                $booking_hotel["hotel_name"] = isset($mHotel['hotel_name'])?$mHotel['hotel_name']:(isset($rq_data["hotel_nm"])?$rq_data["hotel_nm"]:"");
                $booking_hotel["hotel_transfer"] = ($rq_data['trf_type']!=0)?3:0;
                $booking_hotel["hotel_price_id"] = 0;
                $booking_hotel["hotel_category"] = $rq_data['category'];
                $booking_hotel["booking_hotel_sgl"] = $order_hotel_sgl;
                $booking_hotel["booking_hotel_sgl_std"] = $order_hotel_sgl_std;
                $booking_hotel["booking_hotel_sgl_plus"] = $order_hotel_sgl_plus;
                $booking_hotel["booking_hotel_twb"] = $order_hotel_twb;
                $booking_hotel["booking_hotel_dbl"] = $order_hotel_dbl;
                $booking_hotel["booking_hotel_trp"] = $order_hotel_trp;
                $booking_hotel["booking_hotel_trp2"] = $order_hotel_trp2;
                $booking_hotel["booking_hotel_sgl_sale"] = $order_hotel_sgl_sale;
                $booking_hotel["booking_hotel_sgl_plus_sale"] = $order_hotel_sgl_plus_sale;
                $booking_hotel["booking_hotel_twb_sale"] = $order_hotel_twb_sale;
                $booking_hotel["booking_hotel_trp_sale"] = $order_hotel_trp_sale;
                $booking_hotel["booking_hotel_sgl_ct"] = $order_hotel_sgl_ct;
                $booking_hotel["booking_hotel_twb_ct"] = $order_hotel_twb_ct;
                $booking_hotel["booking_hotel_trp_ct"] = $order_hotel_trp_ct;
                $booking_hotel["booking_hotel_ext_ct"] = $order_hotel_ext_ct;
                $booking_hotel["booking_hotel_trf_ct"] = isset($ht_price['transfer_price_summary']['trf_total_price'])?$ht_price['transfer_price_summary']['trf_total_price']:0;
                $booking_hotel["accounting_hotel_sgl_ct"] = $accounting_hotel_sgl_ct;
                $booking_hotel["accounting_hotel_twb_ct"] = $accounting_hotel_twb_ct;
                $booking_hotel["accounting_hotel_dbl_ct"] = $accounting_hotel_dbl_ct;
                $booking_hotel["accounting_hotel_trp_ct"] = $accounting_hotel_trp_ct;
                $booking_hotel["hotel_promotion"] = isset($ht_price['promotion_remark'])?$ht_price['promotion_remark']:'';
                $booking_hotel["hotel_promotion_jp"] = isset($ht_price['promotion_remark_jp'])?$ht_price['promotion_remark_jp']:'';
                $booking_hotel["hotel_cancel_policy"] = isset($ht_price['cancelations'][0]['remark'])?$ht_price['cancelations'][0]['remark']:'';
                $booking_hotel["hotel_cancel_policy_jp"] = isset($ht_price['cancelations'][0]['remark'])?$ht_price['cancelations'][0]['remark_jp']:'';
                $booking_hotel["hotel_cancel_policy_git"] = '';
                $booking_hotel["hotel_cancel_policy_git_jp"] = '';
                $booking_hotel["hotel_benefit"] = isset($ht_price['benefit_remark'])?$ht_price['benefit_remark']:'';
                $booking_hotel["hotel_benefit_jp"] = isset($ht_price['benefit_remark_jp'])?$ht_price['benefit_remark_jp']:'';
                $booking_hotel["hotel_deposit"] = isset($ht_price['deposit_remark'])?$ht_price['deposit_remark']:'';
                $booking_hotel["hotel_deposit_jp"] = isset($ht_price['deposit_remark_jp'])?$ht_price['deposit_remark_jp']:'';
                $booking_hotel["booking_hotel_qty_person"] = $cus_num;
                $booking_hotel["booking_hotel_price"] = $total_hotel_price_usd;
                $booking_hotel["booking_hotel_amount"] = $total_hotel_amount_usd;
                $booking_hotel["booking_hotel_status_confirm"] = 0;
                $booking_hotel["promotion"] = isset($rq_data['promotion_cd'])?$rq_data['promotion_cd']:"";
                $booking_hotel["bh_request_status"] = (isset($ht_price["avail_flg"])&&$ht_price["avail_flg"]==1)?4:1;
                $booking_hotel["bh_booking_status"] = 1;
                $booking_hotel["booking_hotel_lco_free"] = isset($ht_price['lco_flg']) ? 1 : 0;
                $booking_hotel["rsv_arr_id"] = $rq_data["rsv_arr_id"];
                $this->bk_hotel_model->insertOne($booking_hotel);
                $booking_hotel_id = $this->db->last_update_id;

                if ($night > 0) {
                    for ($i = 0; $i <= $night; $i++) {
                        $date = strtotime("+$i day", $checkin);
                        $_date = date('Y/m/d',$date);

                        $data = $this->_fm_arr_tour();
                        $data['booking_id'] = $bk_id;
                        $data['booking_tour_date'] = $_date;
                        if ($i == $night) {
                            $data['booking_hotel_id'] = '';
                        }else{
                            $data['booking_hotel_id'] = $booking_hotel_id;
                        }
                        $data['agent_id'] = $this->user_info['agent_id'];
                        $data['agent_code'] = $this->user_info['agent_code'];
                        $data['place_id_from'] = $mHotel['city_id'];
                        $data['place_code_from'] = $mHotel['city_cd'];
                        $booking_tour_id = $this->_add_tour($data);

                        //2 way or 1 way from airport->hotel
                        if($rq_data['trf_type'] == 1 || $rq_data['trf_type'] == 2){
                            if($i==0){
                                $place_code = str_replace(' ','',$mHotel['city_cd']);
                                $place_code = substr($place_code,0,3);
                                $this->db->where('tour_code',$place_code.'01A');
                                $mTour = $this->tour_model->get('','tour_id,tour_name');

                                //insert tour don
                                $data = $this->_fm_arr_tour_detail();
                                if(isset($temp_flights[$_date]) && !empty($temp_flights[$_date]) && count($temp_flights[$_date]) > 0){
                                    $length = count($temp_flights[$_date]) - 1;
                                    $flt = $temp_flights[$_date][$length];
                                    $data['place_id_from'] = $this->place_model->getIdByCode($flt['dep']);
                                    $data['place_code_from'] = $flt['dep'];
                                    $data['place_id_to'] = $this->place_model->getIdByCode($flt['arr']);
                                    $data['place_code_to'] = $flt['arr'];
                                    $data['flight_no'] = $flt['flight'];
                                    $data['flight_time'] = $flt['dep_time'];
                                    $data['flight_time_coming'] = $flt['arr_time'];
                                }else{
                                    $data['place_id_to'] = $city_id;
                                    $data['place_code_to'] = $city_cd;
                                }
                                $data['tour_id'] = isset($mTour[0]['tour_id'])?$mTour[0]['tour_id']:0;
                                $data['tour_code'] = $place_code.'01A';
                                $data['tour_place_id'] = $city_id;
                                $data['tour_place_code'] = $city_cd;
                                $data['booking_tour_id'] = $booking_tour_id;
                                $data['booking_tour_details_price'] = 0;
                                $data['booking_tour_details_amount'] = 0;
                                $data['tour_key'] = 8;
                                $data['booking_tour_details_description'] = isset($mTour[0]['tour_name'])?$mTour[0]['tour_name']:'送迎：空港からホテルまで（片道）';
                                $data['booking_tour_details_description_en'] = isset($mTour[0]['tour_name'])?$mTour[0]['tour_name']:'Pick up Airport to Hotel';
                                $data['adult_num'] = $cus_num;
                                $data['child_num'] = $child_num;
                                $data['infant_num'] = 0;
                                $data['booking_tour_details_order'] = 1;
                                $this->_add_tour_detail($data);
                            }
                        }

                        //2 way or 1 way from hotel->airport
                        if($rq_data['trf_type'] == 1 || $rq_data['trf_type'] == 3){
                            if($i==$night){
                                $place_code = str_replace(' ','',$mHotel['city_cd']);
                                $place_code = substr($place_code,0,3);
                                $this->db->where('tour_code',$place_code.'01B');
                                $mTour = $this->tour_model->get('','tour_id,tour_name');

                                //insert tour tien
                                $data = $this->_fm_arr_tour_detail();
                                if(isset($temp_flights[$_date]) && !empty($temp_flights[$_date]) && count($temp_flights[$_date]) > 0){
                                    $length = 0;
                                    $flt = $temp_flights[$_date][$length];
                                    $data['place_id_from'] = $this->place_model->getIdByCode($flt['dep']);
                                    $data['place_code_from'] = $flt['dep'];
                                    $data['place_id_to'] = $this->place_model->getIdByCode($flt['arr']);
                                    $data['place_code_to'] = $flt['arr'];
                                    $data['flight_no'] = $flt['flight'];
                                    $data['flight_time'] = $flt['dep_time'];
                                    $data['flight_time_coming'] = $flt['arr_time'];
                                }else{
                                    $data['place_id_from'] = $city_id;
                                    $data['place_code_from'] = $city_cd;
                                }
                                $data['tour_id'] = isset($mTour[0]['tour_id'])?$mTour[0]['tour_id']:0;
                                $data['tour_code'] = $place_code.'01B';
                                $data['tour_place_id'] = $city_id;
                                $data['tour_place_code'] = $city_cd;
                                $data['booking_tour_id'] = $booking_tour_id;
                                $data['booking_tour_details_price'] = 0;
                                $data['booking_tour_details_amount'] = 0;
                                $data['tour_key'] = 8;
                                $data['booking_tour_details_description'] = isset($mTour[0]['tour_name'])?$mTour[0]['tour_name']:'転送：ホテルへの空港（片道）';
                                $data['booking_tour_details_description_en'] = isset($mTour[0]['tour_name'])?$mTour[0]['tour_name']:'Pick up Hotel to Airport';
                                $data['adult_num'] = $cus_num;
                                $data['child_num'] = $child_num;
                                $data['infant_num'] = 0;
                                $data['booking_tour_details_order'] = 0;
                                $this->_add_tour_detail($data);
                            }
                        }
                    }
                }
            }

            $rsv_hotel[] = array(
                "rsv_arr_id"=>$booking_hotel['rsv_arr_id'],
                "item_code"=>isset($mHotel['hotel_cd'])?$mHotel['hotel_cd']:(isset($rq_data["hotel_cd"])?$rq_data["hotel_cd"]:""),
                "status"=>$this->getBKConfirmStatus($booking_hotel["bh_request_status"])
            );
        }
    }
    
    private function __delete($booking_cd){
        if(!empty($booking_cd)){
            
            //SQL Conditions
            $iData = array();
            if($this->user_info['user_level']==4){
                $iData['user_name_modified'] = $this->user_info['user_name'];
                $iData['agent_code'] = $this->user_info['agent_code'];
            }
            $iData[] = 'booking_status NOT IN (4,5,7)';
            $iData[] = '(accounting_blocked = 0 OR accounting_blocked IS NULL)';
            $iData["booking_code"] = $booking_cd;
    
            //SQL Select
            $select = "booking_id";
            
            //SQL Execute
            $mlist = $this->getOne($iData,$select);
            
            if(isset($mlist)&&!empty($mlist)){
                $booking_id = $mlist['booking_id'];
                if(!empty($booking_id)){
                    //SQL Conditions
                    $iData = array(
                        "booking_id" => $booking_id
                    );
                    //SQL Select
                    $select = "booking_tour_id";
                    //SQL Execute
                    $mBKTour = $this->BookingTourModel->getList($iData,$select);
                    
                    if(!empty($mBKTour)){
                        $arr_bk_tour = array();
                        foreach($mBKTour as $bk_tour){
                            $arr_bk_tour[] = $bk_tour['booking_tour_id'];
                        }
                        $iData = array(
                            "booking_tour_id" => $arr_bk_tour
                        );
                        $this->BookingTourDetailsModel->deleteWhere($iData);
                    }
    
                    $iData = array(
                        "booking_id" => $booking_id
                    );
                    //delete booking_tour
                    $this->BookingTourModel->deleteWhere($iData);
                    //delete booking_hotel
                    $this->BookingHotelModel->deleteWhere($iData);
                    //delete booking_history
                    $this->BookingHistoryModel->deleteWhere($iData);
                    //delete booking_price
                    $this->BookingPriceModel->deleteWhere($iData);
                    //delete booking
                    $this->BookingModel->deleteWhere($iData);
                
                    return true;
                }
            }
        }
        return false;
    }

    private function _INSERT_BKTour($bk_id,$rq_data,&$rsv_tour,$temp_flights = array()){
        
        $data = $this->_fm_arr_tour();
        $data['booking_tour_date'] = $rq_data['date'];
        $data['booking_id'] = $bk_id;
        $booking_tour_id = $this->_add_tour($data);

        $sql = "SELECT COUNT(booking_tour_details_id) AS total 
				FROM booking_tour_details
				WHERE booking_tour_id = ".$booking_tour_id;
        $rs = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        
        $hasTourFlg = false;
        if(isset($rs[0]["total"]) && !empty($rs[0]["total"]) && $rs[0]["total"] > 0){
            $hasTourFlg = true;
        }
        
        if(($rq_data["tour_nm"]=="自由行動" || $rq_data["tour_nm"]=="自由時間" || $rq_data["tour_nm"]=="終日自由行動") && $hasTourFlg)
            return;
    
        $rq_data['date'] = $rq_data['date'];
        $rq_data['curr_cd'] = $rq_data['curr_cd'];
        
        //Free time case
        if($rq_data["tour_cd"]=="FREETIME"){
            $rq_data['tour_cd'] = $rq_data['city_cd']."0@@FREETIME";
        }
        
        $mlist = $this->TourUtil->getDetailMain($rq_data,true);
        
        //not register free time tour for city -> SGN0
        if(empty($mlist)&&$rq_data["tour_cd"]=="FREETIME"){
            $rq_data['tour_cd'] = "SGN0@@FREETIME";
            $mlist = $this->TourUtil->getDetailMain($rq_data,true);
            $city_id = $this->PlaceModel->getIdByCode($rq_data['city_cd']);
            $city_cd = $rq_data['city_cd'];
        }else{
            $city_id = !empty($mlist['city_id'])?$mlist['city_id']:$this->PlaceModel->getIdByCode($rq_data['city_cd']);
            $city_cd = !empty($mlist['city_cd'])?$mlist['city_cd']:$rq_data['city_cd'];
        }

        $key_code = substr($mlist['tour_cd'],3,1);
        $key = 8;
        switch ($key_code){
            case 'H';
                $key = 1;
                break;
            case 'F';
                $key = 3;
                break;
            case 'O';
                $key = 4;
                break;
            case 'N';
                $key = 5;
                break;
            case 'L';
                $key = 6;
                break;
            case 'D';
                $key = 7;
                break;
            case 'P';
                $key = 9;
                break;
        }

        $city_id = $this->PlaceModel->getIdByCode($rq_data['city_cd']);
        
        $data = $this->_fm_arr_tour_detail();
        $data['booking_tour_id'] = $booking_tour_id;
        $data['tour_id'] = $mlist['tour_id'];
        $data['tour_code'] = $mlist['tour_cd'];
        $data['tour_place_id'] = $city_id;
        $data['tour_place_code'] = $city_cd;
        $data['private_car'] = $rq_data['car_type'];
        $data['booking_tour_details_breakfast'] = $mlist['br'];
        $data['booking_tour_details_lunch'] = $mlist['lu'];
        $data['booking_tour_details_dinner'] = $mlist['dn'];
        if(isset($mlist["tour_price_summaries"][0])&&!empty($mlist["tour_price_summaries"][0])){
            $data['booking_tour_details_price'] = ($mlist["tour_price_summaries"][0]['average_price']!="N/A")?$mlist["tour_price_summaries"][0]['average_price']:0;
            $data['booking_tour_details_amount'] = ($mlist["tour_price_summaries"][0]['total_price']!="N/A")?$mlist["tour_price_summaries"][0]['total_price']:0;
        }
        $data['tour_key'] = $key;
        $data['place_id_to'] = $city_id;
        $data['place_code_to'] = $city_cd;
        $data['booking_tour_details_description'] = (isset($rq_data['tour_nm'])&&!empty($rq_data['tour_nm']))?$rq_data['tour_nm']:$mlist['tour_name_jp'];
        $data['booking_tour_details_description_en'] = (isset($rq_tour_detail['tour_nm'])&&!empty($rq_data['tour_nm']))?$rq_data['tour_nm']:$mlist['tour_name'];
        $data['adult_num'] = $rq_data['adult_num'];
        $data['child_num'] = $rq_data['child_num'];
        $data['infant_num'] = 0;
        $data['rsv_arr_id'] = $rq_data["rsv_arr_id"];
        $data['booking_tour_details_order'] = 2;
        $this->_add_tour_detail($data);
        
        

        $rsv_tour[] = array(
            "rsv_arr_id"=>$rq_data["rsv_arr_id"],
            "item_code"=>$mlist['tour_cd'],
            "status"=>$this->getBKConfirmStatus(4)
        );
    }

    private function _INSERT_BKFlight($bk_id,$rq_data){
        $data = $this->_fm_arr_tour();
        $data['booking_tour_date'] = $rq_data['date'];
        $data['booking_id'] = $bk_id;
        $booking_tour_id = $this->_add_tour($data);

        $place_id_from = $this->place_model->getIdByCode($rq_data['dep']);
        $place_id_to = $this->place_model->getIdByCode($rq_data['arr']);
        $data = $this->_fm_arr_tour_detail();
        $data['booking_tour_id'] = $booking_tour_id;
        $data['tour_id'] = 0;
        $data['tour_code'] = $rq_data['arr'].'0FREE';
        $data['tour_place_id'] = $place_id_to;
        $data['tour_place_code'] = $rq_data["arr"];
        $data['booking_tour_details_price'] = 0;
        $data['booking_tour_details_amount'] = 0;
        $data['tour_key'] = 8;
        $data['place_id_from'] = $place_id_from;
        $data['place_code_from'] = $rq_data['dep'];
        $data['place_id_to'] = $place_id_to;
        $data['place_code_to'] = $rq_data["arr"];
        $data['flight_no'] = $rq_data['flight'];
        $data['flight_time'] = $rq_data['dep_time'];
        $data['flight_time_coming'] = $rq_data['arr_time'];
        $data['booking_tour_details_order'] = 0;
        $data['booking_tour_details_description'] = '自由時間';
        $data['booking_tour_details_description_en'] = 'FREE TIME';
        $data['adult_num'] = 0;
        $data['child_num'] = 0;
        $data['infant_num'] = 0;
        $this->_add_tour_detail($data);
    }

    private function _INSERT_BKPlace($bk_id,$rq_data){
        $hour = intval(substr($rq_data["meeting_time"],0,2));
        $moment = ($hour>12 && $hour<>24)?"PM":"AM";
        if($hour>12)
            $hour = $hour - 12;
        if($hour==12)
            $hour = "00";
        $minute = substr($rq_data["meeting_time"],(strlen($rq_data["meeting_time"])-1),2);

        $date = strtotime($rq_data['checkin_date']);

        $booking_hotel_to = ORM::factory('booking_hotel_to_orm');
        $booking_hotel_to->booking_id = $bk_id;
        $booking_hotel_to->booking_hotel_to_date = $date;
        $booking_hotel_to->booking_hotel_to_hour = $hour;
        $booking_hotel_to->booking_hotel_to_minute = $minute;
        $booking_hotel_to->booking_hotel_to_moment = $moment;
        $booking_hotel_to->place_id = $this->place_model->getIdByCode($rq_data["city_cd"]);
        $booking_hotel_to->place_code = $rq_data["city_cd"];
        $booking_hotel_to->hotel_id = "";
        $booking_hotel_to->hotel_name = $rq_data["place_nm"]." ".$rq_data["place_add"]." ".$rq_data["place_tel"];
        $booking_hotel_to->booking_cb_hotel_to_other = 1;
        $booking_hotel_to->booking_paid_guide_to = 3;
        $booking_hotel_to->booking_paid_guide_price_to = "";
        $booking_hotel_to->save();

        $data = $this->_fm_arr_tour();
        $data['booking_tour_date'] = date('Y/m/d',$date);
        $data['booking_id'] = $bk_id;
        $booking_tour_id = $this->_add_tour($data);

        $mBKTourDetail = $this->bk_tour_details_model->getForegin($booking_tour_id,"booking_tour_details_id");
        if(empty($mBKTourDetail)){
            $data = $this->_fm_arr_tour_detail();
            $city_id = $this->place_model->getIdByCode($rq_data['city_cd']);
            $data['tour_id'] = 0;
            $data['tour_code'] = $rq_data["city_cd"].'0FREE';
            $data['tour_place_id'] = $city_id;
            $data['tour_place_code'] = $rq_data["city_cd"];
            $data['booking_tour_id'] = $booking_tour_id;
            $data['booking_tour_details_price'] = 0;
            $data['booking_tour_details_amount'] = 0;
            $data['tour_key'] = 8;
            $data['place_id_from'] = $city_id;
            $data['place_code_from'] = $rq_data["city_cd"];
            $data['booking_tour_details_description'] = '送迎';
            $data['booking_tour_details_description_en'] = 'Pick up';
            $data['adult_num'] = 0;
            $data['child_num'] = 0;
            $data['infant_num'] = 0;
            $this->_add_tour_detail($data);
        }
    }

    private function _refresh_price($bk_id){
        $iData = array(
            "booking_id" => $bk_id
        );
        $mBooking = $this->BookingModel->getOne($iData);
        if(isset($mBooking)&&!empty($mBooking)){
            $mBKPrice = $this->BookingPriceModel->getList($iData);
            if(isset($mBKPrice)&&!empty($mBKPrice)){
                $arr_del_price_id = array();
                foreach($mBKPrice as $bk_price){
                    $arr_del_price_id[] = $bk_price['booking_price_id'];
                }
                if(isset($arr_del_price_id)&&!empty($arr_del_price_id)){
                    $iData = array(
                        "booking_price_id" => $arr_del_price_id
                    );
                    $this->BookingPriceModel->deleteWhere($iData);
                }
            }
            $iData = array(
                "booking_id" => $bk_id
            );
            $mBKHotel = $this->BookingHotelModel->getForRefreshPrice($iData);
            $mBKTour = $this->BookingTourModel->getForRefreshPrice($iData);
            if(isset($mBKTour)&&!empty($mBKTour)){
                $arr_bk_tour_id = array();
                foreach($mBKTour as $bk_tour){
                    $arr_bk_tour_id[] = $bk_tour['booking_tour_id'];
                }
                if(isset($arr_bk_tour_id)&&!empty($arr_bk_tour_id)){
                    $iData = array(
                        "booking_tour_id" => $arr_bk_tour_id
                    );
                    $mBKTourDetail = $this->BookingTourDetailsModel->getList($iData);
                }
            }
            $arr_temp = array();
            if(isset($mBKHotel)&&!empty($mBKHotel)){
                foreach($mBKHotel as $bk_hotel){
                    $key = $bk_hotel['place_id'].'_'.$bk_hotel['place_code'];
                    if(isset($arr_temp[$key])&&!empty($arr_temp[$key])){
                        $arr_temp[$key] += intval($bk_hotel['booking_hotel_amount']);
                    }else{
                        $arr_temp[$key] = intval($bk_hotel['booking_hotel_amount']);
                    }
                }
            }

            if(isset($mBKTourDetail)&&!empty($mBKTourDetail)){
                foreach($mBKTourDetail as $bk_tour_detail){
                    $key = $bk_tour_detail['tour_place_id'].'_'.$bk_tour_detail['tour_place_code'];
                    if(isset($arr_temp[$key])&&!empty($arr_temp[$key])){
                        $arr_temp[$key] += intval($bk_tour_detail['booking_tour_details_amount']);
                    }else{
                        $arr_temp[$key] = intval($bk_tour_detail['booking_tour_details_amount']);
                    }
                }
            }

            //$rateD2Y = Configuration_Model::get_value('RATE_D2Y');
            $rateD2Y = 1;
            if(isset($arr_temp)&&!empty($arr_temp)){
                foreach($arr_temp as $place=>$price){
                    if($price<=0)
                        unset($arr_temp[$place]);
                }
                $iData = array(
                    'booking_id' => $mBooking['booking_id']
                );
                $booking_orm = $this->BookingModel->getOne($iData);
                $total = 0;
                $j = 0;
                foreach($arr_temp as $place=>$price){
                    $arr_place = explode('_',$place);
                    $place_id = $arr_place[0];
                    $place_code = $arr_place[1];
                    $total+=$price;

                    if($j==0){
                        $booking_orm["place_id"] = $place_id;
                        $booking_orm["place_code"] = $place_code;
                    }
                    switch ($place_code){
                        case "SGN":
                            $booking_orm["booking_price_sgn"] += $price;
                            break;
                        case "HAN":
                            $booking_orm["booking_price_han"] += $price;
                            break;
                        case "DAD":
                            $booking_orm["booking_price_dad"] += $price;
                            break;
                        case "NHA":
                            $booking_orm["booking_price_nha"] += $price;
                            break;
                        case "HUI":
                            $booking_orm["booking_price_hui"] += $price;
                            break;
                        case "PNH":
                            $booking_orm["booking_price_pnh"] += $price;
                            break;
                        case "REP":
                            $booking_orm["booking_price_rep"] += $price;
                            break;
                        case "LPQ":
                            $booking_orm["booking_price_lpq"] += $price;
                            break;
                        case "RGN":
                            $booking_orm["booking_price_rgn"] += $price;
                            break;
                        case "VTE":
                            $booking_orm["booking_price_vte"] += $price;
                            break;
                    }
                    //insert booking_price
                    $booking_price = array(
                        "booking_id" => $mBooking['booking_id'],
                        "place_code" => $place_code,
                        "place_id" => $place_id,
                        "booking_price_person" => $mBooking['booking_num_person'],
                        "booking_price_type" => 6,
                        "booking_price_name" => $place_code,
                        "booking_price_price" => ($mBooking['booking_num_person']!=0)?$price/$mBooking['booking_num_person']:0,
                        "booking_price_amount" => $price,
                        "booking_price_price_modified" => ($mBooking['booking_num_person']!=0)?$price/$mBooking['booking_num_person']:0,
                        "booking_price_amount_modified" => $price,
                    );
                    $this->BookingPriceModel->insertOne($booking_price);
                    $j++;
                }

                $booking_orm["booking_total_price"] = ($mBooking['booking_num_person']!=0)?$total/$mBooking['booking_num_person']:0;
                $booking_orm["booking_total_amount"] = $total;
                //$booking_orm->booking_total_amount_yen = $this->_format_D2Y($total,$rateD2Y);
                if(empty($booking_orm["booking_from5"]))
                    $booking_orm["booking_from5"] = implode(',',array_keys($arr_temp));
                if(empty($booking_orm["booking_from5_hide"]))
                    $booking_orm["booking_from5_hide"] = implode(',',array_keys($arr_temp));
                $booking_orm["booking_total_price_modified"] = ($mBooking['booking_num_person']!=0)?$total/$mBooking['booking_num_person']:0;
                $booking_orm["booking_total_amount_modified"] = $total;
                $this->BookingModel->updateOne($booking_orm);


            }else{
                $booking_orm = $this->BookingModel->get($mBooking['booking_id']);
                $booking_orm["booking_price_sgn"] = 0;
                $booking_orm["booking_price_han"] = 0;
                $booking_orm["booking_price_dad"] = 0;
                $booking_orm["booking_price_nha"] = 0;
                $booking_orm["booking_price_hui"] = 0;
                $booking_orm["booking_price_pnh"] = 0;
                $booking_orm["booking_price_rep"] = 0;
                $booking_orm["booking_price_lpq"] = 0;
                $booking_orm["booking_price_rgn"] = 0;
                $booking_orm["booking_price_vte"] = 0;
                $booking_orm["booking_total_price"] = 0;
                $booking_orm["booking_total_amount"] = 0;
                $booking_orm["booking_total_amount_yen"] = 0;
                $booking_orm["booking_from5"] = '';
                $booking_orm["booking_from5_hide"] = '';
                $booking_orm["booking_total_price_modified"] = 0;
                $booking_orm["booking_total_amount_modified"] = 0;
                $this->BookingModel->updateOne($booking_orm);
            }
        }
    }

    private function _get_LPN_place($tbk_place){
        return isset($this->fake_place[$tbk_place])?$this->fake_place[$tbk_place]:$tbk_place;
    }

    public static function getBKStatus($st){
        $str = "NEW";
        switch (intval($st)){
            case 1:
                $str = "NEW";
                break;
            case 3:
                $str = "AMD";
                break;
            case 4:
                $str = "FNL";
                break;
            case 5:
                $str = "CXL";
                break;
            case 6:
                $str = "NO-SHOW";
                break;
            case 7:
                $str = "CXL-CHARGE";
                break;
        }
        return $str;
    }

    public function getBKConfirmStatus($status){
        $rsv_arr_status = "RQ";
        //1,2,5,9 - RQ
        if($status==1||$status==2||$status==5||$status==9)
            $rsv_arr_status = "RQ";
        //3 - FULL
        else if($status==3)
            $rsv_arr_status = "FULL";
        //4,8 - OK
        else if($status==4||$status==8)
            $rsv_arr_status = "OK";
        //6,7 - CX
        else if($status==6||$status==7)
            $rsv_arr_status = "CX";
        return $rsv_arr_status;
    }

    private function _fm_arr_tour(){
        return array(
            'booking_id' => '',
            'booking_tour_date' => '',
            'booking_hotel_id' => '',
            'agent_id' => '',
            'agent_code' => '',
            'place_id_from' => '',
            'place_code_from' => ''
        );
    }

    private function _fm_arr_tour_detail(){
        return array(
            'booking_tour_id' => '',
            'tour_id' => '',
            'tour_code' => '',
            'tour_place_id' => '',
            'tour_place_code' => '',
            'booking_tour_details_price' => '',
            'booking_tour_details_amount' => '',
            'tour_key' => '',
            'place_id_from' => '',
            'place_code_from' => '',
            'place_id_to' => '',
            'place_code_to' => '',
            'flight_no' => '',
            'flight_time' => '',
            'flight_time_coming' => '',
            'booking_tour_details_description' => '',
            'booking_tour_details_description_en' => '',
            'adult_num' => 0,
            'child_num' => 0,
            'infant_num' => 0,
            'rsv_arr_id' => '',
            'booking_tour_details_order' => '',
            'private_car' => '',
            'booking_tour_details_breakfast'=>'',
            'booking_tour_details_lunch'=>'',
            'booking_tour_details_dinner'=>''
        );
    }

    private function _add_tour($data){
        $date = strtotime($data['booking_tour_date']);
        $iData = array(
            'FROM_UNIXTIME(booking_tour_date+3601,"%Y/%m/%d")' => $data['booking_tour_date'],
            'booking_id' => $data['booking_id']
        );
        $booking_tour = $this->BookingTourModel->getOne($iData);
        if(empty($booking_tour)){
            $booking_tour["booking_id"] = $data['booking_id'];
            $booking_tour["booking_tour_date"] = $date;
            $booking_tour["booking_hotel_id"] = $data['booking_hotel_id'];
            $booking_tour["agent_id"] = $data['agent_id'];
            $booking_tour["agent_code"] = $data['agent_code'];
            $booking_tour["place_id_from"] = $data['place_id_from'];
            $booking_tour["place_code_from"] = $data['place_code_from'];
            $this->BookingTourModel->insertOne($booking_tour);
            $booking_tour["booking_tour_id"] = $this->db->last_update_id;
        }

        if(isset($data['booking_hotel_id']) && !empty($data['booking_hotel_id'])){
            $booking_tour["booking_hotel_id"] = $data['booking_hotel_id'];
            $this->BookingTourModel->updateOne($booking_tour);
        }
        return $booking_tour["booking_tour_id"];
    }

    private function _add_tour_detail($data){
        $iData = array();
        if(isset($data['flight_no']) && !empty($data['flight_no'])){
            if(isset($data['booking_tour_id']) && !empty($data['booking_tour_id']))
                $iData['booking_tour_id'] = $data['booking_tour_id'];
            if(isset($data['flight_no']) && !empty($data['flight_no']))
                $iData['flight_no'] = $data['flight_no'];
            if(isset($data['place_code_from']) && !empty($data['place_code_from']))
                $iData['place_code_from'] = $data['place_code_from'];
            if(isset($data['place_code_to']) && !empty($data['place_code_to']))
                $iData['place_code_to'] = $data['place_code_to'];
            if(!strpos($data['tour_code'],"0FREE")!== false){
                if(isset($data['tour_place_code']) && !empty($data['tour_place_code']))
                    $iData['tour_place_code'] = $data['tour_place_code'];
            }
        }else{
            if(isset($data['booking_tour_id']) && !empty($data['booking_tour_id']))
                $iData['booking_tour_id'] = $data['booking_tour_id'];
        }
        $booking_tour_details = $this->BookingTourDetailsModel->getOne($iData);
        if(empty($booking_tour_details)){
            $booking_tour_details["tour_id"] = $data['tour_id'];
            $booking_tour_details["tour_code"] = $data['tour_code'];
            $booking_tour_details["tour_place_id"] = $data['tour_place_id'];
            $booking_tour_details["tour_place_code"] = $data['tour_place_code'];
            $booking_tour_details["booking_tour_id"] = $data['booking_tour_id'];
            $booking_tour_details["booking_tour_details_price"] = $data['booking_tour_details_price'];
            $booking_tour_details["booking_tour_details_amount"] = $data['booking_tour_details_amount'];
            $booking_tour_details["tour_key"] = $data['tour_key'];
            $booking_tour_details["place_id_from"] = $data['place_id_from'];
            $booking_tour_details["place_code_from"] = $data['place_code_from'];
            $booking_tour_details["place_id_to"] = $data['place_id_to'];
            $booking_tour_details["place_code_to"] = $data['place_code_to'];
            $booking_tour_details["flight_no"] = $data['flight_no'];
            $booking_tour_details["flight_time"] = $data['flight_time'];
            $booking_tour_details["flight_time_coming"] = $data['flight_time_coming'];
            $booking_tour_details["booking_tour_details_description"] = $data['booking_tour_details_description'];
            $booking_tour_details["booking_tour_details_description_en"] = $data['booking_tour_details_description_en'];
            $booking_tour_details["adult_num"] = $data['adult_num'];
            $booking_tour_details["child_num"] = $data['child_num'];
            $booking_tour_details["infant_num"] = $data['infant_num'];
            $booking_tour_details["rsv_arr_id"] = $data["rsv_arr_id"];
            $booking_tour_details["booking_tour_details_order"] = $data["booking_tour_details_order"];
            $booking_tour_details["private_car"] = $data["private_car"];
            $booking_tour_details["booking_tour_details_breakfast"] = $data["booking_tour_details_breakfast"];
            $booking_tour_details["booking_tour_details_lunch"] = $data["booking_tour_details_lunch"];
            $booking_tour_details["booking_tour_details_dinner"] = $data["booking_tour_details_dinner"];
            $this->BookingTourDetailsModel->insertOne($booking_tour_details);
            return $this->db->last_update_id;
        }
        return false;
    }

}