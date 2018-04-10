<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/3/2018
 * Time: 10:34 AM
 */

class TourUtil extends Util
{
    public function __construct()
    {
        parent::__construct();
        $this->loadModel("TourModel","TourPriceModel","TourFileModel","TourTariffNoteModel","CurrencyModel");
        $this->onlAgent = array('BLT');
    }

    public function getListValid($rq_data){
        return true;
    }

    public function getList($rq_data,$rq_user)
    {
        if(!$this->getListValid($rq_data)){
            return false;
        }
        
        $rq_data['tbk_flg'] = true;

        $data = false;


        $rq_data['date'] = strtotime($rq_data['date']);
        $currency_rate = 1;
        if(isset($rq_data['curr_cd'])&&!empty($rq_data['curr_cd'])&&in_array(strtoupper($rq_data['curr_cd']),$this->arr_currency)&&strtoupper($rq_data['curr_cd'])!='USD'){
            $currency_info = $this->CurrencyModel->get($rq_data['curr_cd']);
            if(isset($currency_info['currency_rate'])&&!empty($currency_info['currency_rate'])){
                $currency_rate = $currency_info['currency_rate'];
            }
        }

        $select = 'tour.tour_id,tour.tour_code,place_id,place_code,tour_price_name
            ,tour_price_name_en,tour_price.tour_price_id,begin_date,end_date
            ,tour_price.tour_price4seats,tour_price.tour_price15seats,tour_price.tour_price29seats
            ,tour_price.tour_price35seats,tour_price.tour_price45seats,tour_price.tour_pricefit_guide
            ,tour_price.tour_pricegit_guide,tour_price.tour_price_lunch,tour_price.tour_price_dinner,tour_price.tour_price_boat
            ,tour_price.tour_price_ticket,tour_price.tour_price_other,tour_price.tour_price,tour_price.tour_price_prc
            ,tour_detail_hour,tour_detail_meal,tour_detail_lunch,tour_detail_dinner,tour_detail_guide
            ,tour_price.tour_price1pax_prc,tour_price.tour_price2pax_prc,tour_price.tour_price3pax_prc
            ,tour_price.tour_price1pax,tour_price.tour_price2pax,tour_price.tour_price3pax
            ,tour_detail_summary,tour_detail_summary_jp,tour_detail_description,tour_detail_description_jp
            ,tour_detail_surcharge_info,0 AS br,IF(tour_price.tour_price_lunch>0,1,0) AS lu,IF(tour_price.tour_price_dinner>0,1,0) AS dn';

        $mTour = $this->TourModel->get_for_list($rq_data,$select);

        //format data
        if(isset($mTour)&&!empty($mTour)){
            $array_id = array();
            foreach($mTour as $tour){
                $array_id[] = $tour['tour_id'];
            }
 
            $rq_temp = array(
                "tour_id" => $array_id
            );
            $select = 'tour_id,file_name,MIN(file_sort_order) as `file_sort_order`';
            $mFile = $this->TourFileModel->get_for_list($rq_temp,$select);
            $arr_file = array();
            if(isset($mFile)&&!empty($mFile)){
                foreach($mFile as $file){

                    $thumb_url='http://www.toursystem.biz/uploads/product/thumb_'.$file['file_name'];
                    $file_url='http://www.toursystem.biz/uploads/product/'.$file['file_name'];
                    $arr_file[$file['tour_id']][] = array(
                        'thumb_url'=>$thumb_url,
                        'file_url'=>$file_url,
                        'file_sort_order'=>$file['file_sort_order']
                    );
                }
            }

            $cus_num = $rq_data['adult_num']+$rq_data['child_num'];

            //get transfer price
            $select = 'tour.tour_id,tour.tour_code,place_id,place_code,tour_price_name
            ,tour_price_name_en,tour_price.tour_price_id,begin_date,end_date
            ,tour_price.tour_price4seats,tour_price.tour_price15seats,tour_price.tour_price29seats
            ,tour_price.tour_price35seats,tour_price.tour_price45seats,tour_price.tour_pricefit_guide
            ,tour_price.tour_pricegit_guide,tour_price.tour_price_lunch,tour_price.tour_price_dinner,tour_price.tour_price_boat
            ,tour_price.tour_price_ticket,tour_price.tour_price_other,tour_price.tour_price,tour_price.tour_price_prc
            ,tour_price.tour_price1pax_prc,tour_price.tour_price2pax_prc,tour_price.tour_price3pax_prc
            ,tour_price.tour_price1pax,tour_price.tour_price2pax,tour_price.tour_price3pax';

            $rq_temp = array(
                "city_cd" => $rq_data['city_cd'],
                "date" => $rq_data['date'],
                "meal_flg" => 1
            );
            $mTrf = $this->TourModel->get_trf_price($rq_temp,$select);

            $rq_temp = array(
                "city_cd" => $rq_data['city_cd'],
                "date" => $rq_data['date'],
                "meal_flg" => 0
            );
            $mSTrf = $this->TourModel->get_trf_price($rq_temp,$select);

            foreach($mTour as $key=>$tour){

                $price = $this->__getPrice($tour, $cus_num, $rq_data['car_type'],$currency_rate);

                $tour_key = substr($tour['tour_code'],3,1);

                // cộng giá đón tiễn cho các tour ăn và cafe, spa
                if($rq_data['trf_type']==1){
                    if($tour_key=='D'||$tour_key=='L'||$tour_key=='A'){
                        if(isset($mTrf[0])&&!empty($mTrf[0])){
                            $price += $this->__getPrice($mTrf[0], $cus_num, $rq_data['car_type'],$currency_rate);
                        }
                    }else if($tour_key=='S'){
                        if(isset($mSTrf[0])&&!empty($mSTrf[0])){
                            $price += $this->__getPrice($mSTrf[0], $cus_num, $rq_data['car_type'],$currency_rate);
                        }
                    }
                }

                $data[$key]['tour_cd'] = $tour['tour_code'];
                $data[$key]['city_cd'] = $tour['place_code'];
                $data[$key]['tour_name'] = $tour['tour_price_name_en'];
                $data[$key]['tour_name_jp'] = $tour['tour_price_name'];
                $data[$key]['tour_summary'] = $tour['tour_detail_summary'];
                $data[$key]['tour_summary_jp'] = $tour['tour_detail_summary_jp'];
                $data[$key]['tour_schedule'] = $tour['tour_detail_description'];
                $data[$key]['tour_schedule_jp'] = $tour['tour_detail_description_jp'];
                $data[$key]['tour_schedule_jp'] = $tour['tour_detail_description_jp'];
                $data[$key]['tour_surcharge_remark'] = $tour['tour_detail_surcharge_info'];
                $data[$key]['br'] = $tour['br'];
                $data[$key]['lu'] = $tour['lu'];
                $data[$key]['dn'] = $tour['dn'];
                $data[$key]['adult_num'] = $rq_data['adult_num'];
                $data[$key]['child_num'] = $rq_data['child_num'];
                $data[$key]['infant_num'] = $rq_data['infant_num'];
                if($rq_data['car_type']===""){
                    $data[$key]['tour_price_summaries'] = array();
                    $data[$key]['tour_price_summaries'][0] = array(
                        'car_type'=>0,
                        'car_type_name'=>'Combine',
                        'car_name'=>'Samco',
                        'car_seat'=>$this->getCarSeat($cus_num),
                        'total_price'=>(!empty($price)&&$price>0)?$price*$cus_num:"N/A",
                        'average_price'=>(!empty($price)&&$price>0)?$price:"N/A"
                    );
                    $data[$key]['tour_price_summaries'][1] = array(
                        'car_type'=>1,
                        'car_type_name'=>'Private',
                        'car_name'=>'Samco',
                        'car_seat'=>$this->getCarSeat($cus_num),
                        'total_price'=>(!empty($price_prc)&&$price_prc>0)?$price_prc*$cus_num:"N/A",
                        'average_price'=>(!empty($price_prc)&&$price_prc>0)?$price_prc:"N/A"
                    );
                }else{
                    $data[$key]['tour_price_summaries'][0] = array(
                        'car_type'=>$rq_data['car_type'],
                        'car_type_name'=>($rq_data['car_type']==1)?'Private':'Combine',
                        'car_name'=>'Samco',
                        'car_seat'=>$this->getCarSeat($cus_num),
                        'total_price'=>(!empty($price)&&$price>0)?$price*$cus_num:"N/A",
                        'average_price'=>(!empty($price)&&$price>0)?$price:"N/A"
                    );
                }
                $data[$key]['option_files'] = isset($arr_file[$tour['tour_id']])?$arr_file[$tour['tour_id']]:array();
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
        
        //$BK_FLG = $rq_data['bk_flg'];
        $rq_data['tbk_flg'] = false;
    
        $data = false;
    
        $cnt = 1;
        if(!empty($rq_data["date"])&&!empty($rq_data["date2"])){
            $date = new DateTime($rq_data["date"]);
            $date2 = new DateTime($rq_data["date2"]);
            $diff = $date->diff($date2)->days;
            if($diff>=0){
                $cnt = $diff + 1;
            }
        }
    
        $rq_data['date'] = strtotime($rq_data['date']);
        $currency_rate = 1;
        if(isset($rq_data['curr_cd'])&&!empty($rq_data['curr_cd'])&&in_array(strtoupper($rq_data['curr_cd']),$this->arr_currency)&&strtoupper($rq_data['curr_cd'])!='USD'){
            $currency_info = $this->CurrencyModel->get($rq_data['curr_cd']);
            if(isset($currency_info['currency_rate'])&&!empty($currency_info['currency_rate'])){
                $currency_rate = $currency_info['currency_rate'];
            }
        }
    
        $isFreeTime = false;
        if(strpos($rq_data['tour_cd'],"@@FREETIME")!==false){
            //free time tour
            $isFreeTime = true;
        }
    
        $select = "tour.tour_id,tour.tour_code,place_id,place_code,tour_name_en,tour_name,tour_summary
					,tour_summary_jp,tour_description,tour_description_jp,tour_hour,tour_breakfast AS br
					,tour_lunch AS lu,tour_dinner AS dn";
        $mTour = $this->TourModel->get_for_detail($rq_data,$select);
    
        if(!empty($mTour)){
        
            $rq_temp = array(
                "tour_id" => $mTour["tour_id"]
            );
            $select = "tour_id,file_name,file_sort_order";
            $mFile = $this->TourFileModel->get_for_detail($rq_temp,$select);
            $arr_file = array();
            if(isset($mFile)&&!empty($mFile)){
                foreach($mFile as $key=>$file){
                    $thumb_url='http://www.toursystem.biz/uploads/product/thumb_'.$file['file_name'];
                    $file_url='http://www.toursystem.biz/uploads/product/'.$file['file_name'];
                
                    $arr_file[$key]['thumb_url'] = $thumb_url;
                    $arr_file[$key]['file_url'] = $file_url;
                    $arr_file[$key]['file_sort_order'] = $file['file_sort_order'];
                }
            }
        
            $rq_temp = array(
                "tour_id" => $mTour["tour_id"],
                "date" => $rq_data['date'],
                "car_type" => $rq_data['car_type']
            );
            $select = 'tour_price_name,tour_price_name_en,tour_price.tour_price_id,begin_date,end_date
				,tour_price.tour_price4seats,tour_price.tour_price15seats,tour_price.tour_price29seats
				,tour_price.tour_price35seats,tour_price.tour_price45seats,tour_price.tour_pricefit_guide
				,tour_price.tour_pricegit_guide,tour_price.tour_price_lunch,tour_price.tour_price_dinner,tour_price.tour_price_boat
				,tour_price.tour_price_ticket,tour_price.tour_price_other,tour_price.tour_price,tour_price.tour_price_prc
				,tour_detail_hour,tour_detail_meal,tour_detail_lunch,tour_detail_dinner,tour_detail_guide
				,tour_price.tour_price1pax_prc,tour_price.tour_price2pax_prc,tour_price.tour_price3pax_prc
				,tour_price.tour_price1pax,tour_price.tour_price2pax,tour_price.tour_price3pax
				,tour_detail_summary,tour_detail_summary_jp,tour_detail_description,tour_detail_description_jp
				,tour_detail_surcharge_info,0 AS br,IF(tour_price.tour_price_lunch>0,1,0) AS lu,IF(tour_price.tour_price_dinner>0,1,0) AS dn';
        
            $mTourPrice = $this->TourPriceModel->get_for_detail($rq_temp,$select);
        
            $cus_num = $rq_data['adult_num']+$rq_data['child_num'];
        
            $price = 0;
            $price_prc = 0;
            $tour_price = array();
            if(!empty($mTourPrice)){
                $tour_price = $mTourPrice[0];
                $tour_price['tour_code'] = $mTour['tour_code'];
            
                if($rq_data['car_type']==""){
                    $price = $this->__getPrice($tour_price, $cus_num, 0,$currency_rate);
                    $price_prc = $this->__getPrice($tour_price, $cus_num, 1,$currency_rate);
                }else{
                    $price = $this->__getPrice($tour_price, $cus_num, $rq_data['car_type'],$currency_rate);
                }
            
                $tour_key = substr($mTour['tour_code'],3,1);
            
                $select = 'tour.tour_id,tour.tour_code,place_id,place_code,tour_price_name
				,tour_price_name_en,tour_price.tour_price_id,begin_date,end_date
				,tour_price.tour_price4seats,tour_price.tour_price15seats,tour_price.tour_price29seats
				,tour_price.tour_price35seats,tour_price.tour_price45seats,tour_price.tour_pricefit_guide
				,tour_price.tour_pricegit_guide,tour_price.tour_price_lunch,tour_price.tour_price_dinner,tour_price.tour_price_boat
				,tour_price.tour_price_ticket,tour_price.tour_price_other,tour_price.tour_price,tour_price.tour_price_prc
				,tour_price.tour_price1pax_prc,tour_price.tour_price2pax_prc,tour_price.tour_price3pax_prc
				,tour_price.tour_price1pax,tour_price.tour_price2pax,tour_price.tour_price3pax';
            
                // cộng giá đón tiễn cho các tour ăn và cafe, spa
                if($rq_data['trf_type']==1){
                    if($tour_key=='D'||$tour_key=='L'||$tour_key=='A'){
                    
                        $rq_temp = array(
                            "city_cd" => $mTour['place_code'],
                            "date" => $rq_data['date'],
                            "meal_flg" => 1
                        );
                        $mTrf = $this->TourModel->get_trf_price($rq_temp,$select);
                    
                        if(isset($mTrf[0])&&!empty($mTrf[0])){
                            if($rq_data['car_type']==""){
                                $price += $this->__getPrice($mTrf[0], $cus_num, 0,$currency_rate);
                                $price_prc += $this->__getPrice($mTrf[0], $cus_num, 1,$currency_rate);
                            }else{
                                $price += $this->__getPrice($mTrf[0], $cus_num, $rq_data['car_type'],$currency_rate);
                            }
                        }
                    }else if($tour_key=='S'){
                    
                        $rq_temp = array(
                            "city_cd" => $mTour['place_code'],
                            "date" => $rq_data['date'],
                            "meal_flg" => 0
                        );
                        $mSTrf = $this->TourModel->get_trf_price($rq_temp,$select);
                    
                        if(isset($mTrf[0])&&!empty($mTrf[0])){
                            if($rq_data['car_type']==""){
                                $price += $this->__getPrice($mSTrf[0], $cus_num, 0,$currency_rate);
                                $price_prc += $this->__getPrice($mSTrf[0], $cus_num, 1,$currency_rate);
                            }else{
                                $price += $this->__getPrice($mSTrf[0], $cus_num, $rq_data['car_type'],$currency_rate);
                            }
                        }
                    }
                }
            }
        
            if($BK_FLG){
                $data['tour_id'] = $mTour['tour_id'];
                $data['city_id'] = $mTour['place_id'];
            }
        
            $data['tour_cd'] = $mTour['tour_code'];
            $data['city_cd'] = ($isFreeTime)?"":$mTour['place_code'];
            $data['tour_name'] = isset($tour_price['tour_price_name_en'])?$tour_price['tour_price_name_en']:$mTour["tour_name_en"];
            $data['tour_name_jp'] = isset($tour_price['tour_price_name'])?$tour_price['tour_price_name']:$mTour["tour_name"];
            $data['tour_summary'] = isset($tour_price['tour_detail_summary'])?$tour_price['tour_detail_summary']:$mTour["tour_summary"];
            $data['tour_summary_jp'] = isset($tour_price['tour_detail_summary_jp'])?$tour_price['tour_detail_summary_jp']:$mTour["tour_summary_jp"];
            $data['tour_schedule'] = isset($tour_price['tour_detail_description'])?$tour_price['tour_detail_description']:$mTour["tour_description"];
            $data['tour_schedule_jp'] = isset($tour_price['tour_detail_description_jp'])?$tour_price['tour_detail_description_jp']:$mTour["tour_description_jp"];
            $data['tour_surcharge_remark'] = isset($tour_price['tour_detail_surcharge_info'])?$tour_price['tour_detail_surcharge_info']:"";
            $data['tour_cancel_policy'] = "取消し\nFITキャンセルポリシー  取消料\n前日（15時迄） 無料\n前日（１5時以降） 地上費総額の100%\n".'"'."*　航空券・列車・ホテル・船・ゴルフ・スパ等の手配が\n含まれる場合は実費が請求されます。".'"';
            $data['br'] = isset($tour_price['br'])?$tour_price['br']:$mTour['br'];
            $data['lu'] = isset($tour_price['lu'])?$tour_price['lu']:$mTour['lu'];
            $data['dn'] = isset($tour_price['dn'])?$tour_price['dn']:$mTour['dn'];
            $data['adult_num'] = $rq_data['adult_num'];
            $data['child_num'] = $rq_data['child_num'];
            $data['infant_num'] = $rq_data['infant_num'];
            $data['curr_cd'] = (isset($rq_data['curr_cd'])&&!empty($rq_data['curr_cd'])&&in_array(strtoupper($rq_data['curr_cd']),$this->arr_currency))?strtoupper($rq_data['curr_cd']):'USD';
            if($rq_data['car_type']===""){
                $data['tour_price_summaries'] = array();
                $data['tour_price_summaries'][0] = array(
                    'car_type'=>0,
                    'car_type_name'=>'Combine',
                    'car_name'=>'Samco',
                    'car_seat'=>$this->getCarSeat($cus_num),
                    'total_price'=>(!empty($price)&&$price>0)?$price*$cus_num*$cnt:"N/A",
                    'average_price'=>(!empty($price)&&$price>0)?$price*$cnt:"N/A"
                );
                $data['tour_price_summaries'][1] = array(
                    'car_type'=>1,
                    'car_type_name'=>'Private',
                    'car_name'=>'Samco',
                    'car_seat'=>$this->getCarSeat($cus_num),
                    'total_price'=>(!empty($price_prc)&&$price_prc>0)?$price_prc*$cus_num*$cnt:"N/A",
                    'average_price'=>(!empty($price_prc)&&$price_prc>0)?$price_prc*$cnt:"N/A"
                );
            }else{
                $data['tour_price_summaries'][0] = array(
                    'car_type'=>$rq_data['car_type'],
                    'car_type_name'=>($rq_data['car_type']==1)?'Private':'Combine',
                    'car_name'=>'Samco',
                    'car_seat'=>$this->getCarSeat($cus_num),
                    'total_price'=>(!empty($price)&&$price>0)?$price*$cus_num*$cnt:"N/A",
                    'average_price'=>(!empty($price)&&$price>0)?$price*$cnt:"N/A"
                );
            }
            $data['tour_files'] = $arr_file;
        }

        return $data;
    }

    private function __getPrice($dt_data,$cus_num=2,$car_type,$currency_rate){

        $tour_price4seats = $dt_data['tour_price4seats'];
        $tour_price15seats = $dt_data['tour_price15seats'];
        $tour_price29seats = $dt_data['tour_price29seats'];
        $tour_price35seats = $dt_data['tour_price35seats'];
        $tour_price45seats = $dt_data['tour_price45seats'];
        $tour_pricefit_guide = $dt_data['tour_pricefit_guide'];
        $tour_pricegit_guide =$dt_data['tour_pricegit_guide'];
        $tour_price_boat = $dt_data['tour_price_boat'];
        $tour_price_boat2 = $dt_data['tour_price_boat'];
        $tour_price_boat3 = $dt_data['tour_price_boat'];
        //fit
        $tour_price_lunch = $dt_data['tour_price_lunch'];
        $tour_price_dinner = $dt_data['tour_price_dinner'];
        $tour_price_ticket = $dt_data['tour_price_ticket'];
        $tour_price_other = $dt_data['tour_price_other'];

        $car_charge = 0.8;
        $charge8_14 = 0.7;
        $charge15_30 = 0.8;
        $price = 0;

        if(isset($dt_data)&&!empty($dt_data)){

            $tour_key = substr($dt_data['tour_code'],3,1);
            $price_key = ($car_type==1)?'tour_price_prc':'tour_price';

            //giá tour ăn và cafe, spa = nhau hết ko chia trường hợp số lượng khách
            if($tour_key=='D'||$tour_key=='L'||$tour_key=='A'){
                $price = (isset($dt_data["$price_key"])?$dt_data["$price_key"]:0);
                return $price;
            }else if($tour_key=='S'){
                $price = (isset($dt_data["$price_key"])?$dt_data["$price_key"]:0);
                return $price;
            }

            if($car_type==1){
                if($cus_num >= 1 && $cus_num <= 3){
                    switch($cus_num){
                        case 1:
                            $price = $dt_data['tour_price1pax_prc'] == 0 ? $dt_data['tour_price_prc'] * 1.9 : $dt_data['tour_price1pax_prc'];
                            break;
                        case 2:
                            $price = $dt_data['tour_price_prc'] ? $dt_data['tour_price_prc'] : 0;
                            break;
                        case 3:
                            $price = $dt_data['tour_price3pax_prc'] == 0 ? $dt_data['tour_price_prc'] * 0.95 : $dt_data['tour_price3pax_prc'];
                            break;
                    }
                }else if($cus_num >= 4 && $cus_num <= 7){
                    $price = $dt_data['tour_price3pax_prc'] == 0 ? $dt_data['tour_price_prc'] * 0.95 : $dt_data['tour_price3pax_prc'];
                    for($i=4;$i<=$cus_num;$i++){
                        $price*=0.95;
                    }
                }else{
                    $tour_price_fit = $tour_price_lunch+$tour_price_dinner+$tour_price_ticket;

                    //8-14 pax: car 29 seat
                    if($cus_num >= 8 && $cus_num <= 14){
                        if($dt_data['tour_code']=='HANF03'||$dt_data['tour_code']=='HANO01'||$dt_data['tour_code']=='HANO02'){
                            $boat_price = $tour_price_boat2*2;
                            $price = ceil(((($tour_pricegit_guide+($tour_price29seats/$car_charge)+$tour_price_other+$boat_price)/$cus_num)+$tour_price_fit)/$charge8_14);
                        }else{
                            $price = ceil(((($tour_pricegit_guide+($tour_price29seats/$car_charge)+$tour_price_other)/$cus_num)+$tour_price_fit+$tour_price_boat2)/$charge8_14);
                        }
                    }

                    //15-19 pax: car 35 seats
                    if($cus_num >= 15 && $cus_num <= 19){
                        if($dt_data['tour_code']=='HANF03'||$dt_data['tour_code']=='HANO01'||$dt_data['tour_code']=='HANO02'){
                            $boat_price = $tour_price_boat2*2;
                            $price = ceil(((($tour_pricegit_guide+($tour_price35seats/$car_charge)+$tour_price_other+$boat_price)/$cus_num)+$tour_price_fit)/$charge15_30);
                        }else{
                            $price = ceil(((($tour_pricefit_guide+($tour_price35seats/$car_charge)+$tour_price_other)/$cus_num)+$tour_price_fit+$tour_price_boat2)/$charge15_30);
                        }
                    }

                    //20-30 pax: car 45 seats
                    if($cus_num >= 20 && $cus_num <= 30){
                        if($dt_data['tour_code']=='HANF03'||$dt_data['tour_code']=='HANO01'||$dt_data['tour_code']=='HANO02'){
                            $boat_price = $tour_price_boat2*2;
                            $price = ceil(((($tour_pricegit_guide+($tour_price45seats/$car_charge)+$tour_price_other+$boat_price)/$cus_num)+$tour_price_fit)/$charge15_30);
                        }else{
                            $price = ceil(((($tour_pricefit_guide+($tour_price45seats/$car_charge)+$tour_price_other)/$cus_num)+$tour_price_fit+$tour_price_boat2)/$charge15_30);
                        }
                    }
                }

            }else{
                if($cus_num<=3){
                    $price = (isset($dt_data["tour_price$cus_num"."pax"])&&!empty($dt_data["tour_price$cus_num"."pax"]))?$dt_data["tour_price$cus_num"."pax"]:$dt_data['tour_price'];
                }else{
                    $price = $dt_data["tour_price"];
                }
            }
        }
        return $price*$currency_rate;
    }

    public function getTariffValid($rq_data,$rq_user){
        return true;
    }

    public function getTariff($rq_data,$rq_user){
        
        if(!$this->getTariffValid($rq_data)){
            return false;
        }
        //$this->db->beginTransaction();

        /*$data = false;
        $mlist = array();
        if(isset($rq_user["agent_code"]) && !empty($rq_user["agent_code"])){
            $rq_data["agent_code"] = $rq_user["agent_code"];
        }else{
            $rq_data["agent_code"] = $rq_data["search_agent"];
        }

        $rq_data["onl_flg"] = false;
        if (in_array($rq_data["agent_code"], $this->onlAgent)) {
            $rq_data["onl_flg"] = true;
        }

        if(isset($rq_user['search_season']) && !empty($rq_user['search_season'])){
            $season = json_decode($rq_user['search_season'],true);
        }
        $season  = explode(',', $_GET['search_season']);


        if (isset($season) && !empty($season))
        {
            if (isset($rq_data['search_year']) && !empty($rq_data['search_year'])) {
                if (count($season) == 2) {
                    $begin_date = strtotime($rq_data['search_year'] . '/04/01');
                    $end_date = strtotime(($rq_data['search_year'] + 1) . '/03/31');
                } else {
                    if ($season[0] == 0) {
                        $begin_date = strtotime($rq_data['search_year'] . '/04/01');
                        $end_date = strtotime($rq_data['search_year'] . '/09/30');
                    } else if ($season[0] == 1) {
                        $begin_date = strtotime($rq_data['search_year'] . '/10/01');
                        $end_date = strtotime(($rq_data['search_year'] + 1) . '/03/31');
                    }
                }
                //local
                $begin_date = $begin_date - 86400;
                $end_date = $end_date + 86400;

                $rq_data["begin_date"] = $begin_date;
                $rq_data["end_date"] = $end_date;
            }
        }

        //get tour tariff price and format
        $select_agent = '';
        if($rq_data["onl_flg"]){
            $select_agent = 'tour_price.tour_price_blt AS tour_price,tour_price.tour_price_blt_prc AS tour_price_prc';
        }else{
            $select_agent = 'tour_price.tour_price,tour_price.tour_price_prc';
        }
        $select = 'tour.place_id,tour.tour_id,tour.tour_code,tour_price_name,tour_price_name_en,
                    begin_date,end_date,' . $select_agent . ',tour_detail_hour,tour_detail_meal,tour_detail_lunch,
                    tour_detail_dinner,tour_detail_guide,tour_option_simple_tariff,tour_detail_surcharge_info,
					tour_new_flg,tour_kamiki_flg,tour_shimoki_flg,tour_kamiki_basic,tour_shimoki_basic,
                    tour_price_inter,tour_detail_id';
        $mlist = $this->TourModel->get_tariff($rq_data,$select);

        $aTRF = array();
        $aSTRF = array();
        $select = "place_id,place_code,tour_price.tour_price,tour_price.tour_price_prc";
        $rq_temp = array(
            "meal_flg" => 0,
            "begin_date" => $rq_data["begin_date"],
            "end_date" => $rq_data["end_date"],
            "search_place" => $rq_data["search_place"]
        );
        $mSTrf = $this->TourModel->get_tariff_trf_price($rq_temp,$select);
        var_dump($mSTrf);die;
        if(!empty($mSTrf)){
            foreach($mSTrf as $key => $val){
                $aSTRF[$val['place_id']]['tour_price'] = (isset($val['tour_price']) && !empty($val['tour_price'])) ? $val['tour_price'] : 0;
                $aSTRF[$val['place_id']]['tour_price_prc'] = (isset($val['tour_price_prc']) && !empty($val['tour_price_prc'])) ? $val['tour_price_prc'] : 0;
            }
            unset($mSTrf);
        }

        $rq_temp = array(
            "meal_flg" => 1,
            "begin_date" => $rq_data["begin_date"],
            "end_date" => $rq_data["end_date"],
            "search_place" => $rq_data["search_place"]
        );
        $mTrf = $this->TourModel->get_tariff_trf_price($rq_temp,$select);
        if(!empty($mTrf)){
            foreach($mTrf as $key => $val){
                $aTRF[$val['place_id']]['tour_price'] = (isset($val['tour_price']) && !empty($val['tour_price'])) ? $val['tour_price'] : 0;
                $aTRF[$val['place_id']]['tour_price_prc'] = (isset($val['tour_price_prc']) && !empty($val['tour_price_prc'])) ? $val['tour_price_prc'] : 0;
            }
            unset($mTrf);
        }

        if (isset($mlist) && !empty($mlist)) {
            foreach ($mlist as $id_tour => $tour) {
                if (substr($tour['tour_code'], 3, 1) == 'L' || substr($tour['tour_code'], 3, 1) == 'D' || substr($tour['tour_code'], 3, 1) == 'A') {
                    if (isset($tour['tour_price']) && !empty($tour['tour_price']) && isset($aTRF[$tour['place_id']]['tour_price']) && !empty($aTRF[$tour['place_id']]['tour_price']) )
                        $tour['tour_price'] += $aTRF[$tour['place_id']]['tour_price'];
                    if (isset($tour['tour_price_prc']) && !empty($tour['tour_price_prc']) && isset($aTRF[$tour['place_id']]['tour_price_prc']) && !empty($aTRF[$tour['place_id']]['tour_price_prc']) )
                        $tour['tour_price_prc'] += $aTRF[$tour['place_id']]['tour_price_prc'];
                    $mlist[$id_tour] = $tour;
                }

                if (substr($tour['tour_code'], 3, 1) == 'S') {
                    if (isset($tour['tour_price']) && !empty($tour['tour_price']) && isset($aSTRF[$tour['place_id']]['tour_price']) && !empty($aSTRF[$tour['place_id']]['tour_price']) )
                        $tour['tour_price'] += $aSTRF[$tour['place_id']]['tour_price'];
                    if (isset($tour['tour_price_prc']) && !empty($tour['tour_price_prc']) && isset($aSTRF[$tour['place_id']]['tour_price_prc']) && !empty($aTRF[$tour['place_id']]['tour_price_prc']) )
                        $tour['tour_price_prc'] += $aSTRF[$tour['place_id']]['tour_price_prc'];
                    $mlist[$id_tour] = $tour;
                }
            }
        }

        $mNote = array();
        if(isset($rq_data["search_place"])&&!empty($rq_data["search_place"])&&is_array($rq_data["search_place"])){
            $rq_temp = array(
                "place_id" => $rq_data["search_place"],
                "begin_date" => $rq_data["begin_date"],
                "end_date" => $rq_data["end_date"]
            );
            $mTariffNote = $this->TourTariffNoteModel->get_tariff($rq_temp,"place_id,place_code,begin_date,end_date,tour_tariff_note_desc");
            if(!empty($mTariffNote)){
                foreach($mTariffNote as $key=>$val){
                    $mNote[$val["place_code"]][] = array(
                        "begin_date" => $val['begin_date'],
                        "end_date" => $val['end_date'],
                        "content" => $val['tour_tariff_note_desc']
                    );
                }
                unset($mTariffNote);
            }else{
                $rq_temp = array(
                    "place_id" => $rq_data["search_place"]
                );
                $mPlace = $this->PlaceModel->get($rq_temp,"place_id,place_code");
                if(!empty($mPlace)){
                    foreach($mPlace as $key=>$val){
                        $mNote[$val["place_code"]][0] = array(
                            "begin_date" => "",
                            "end_date" => "",
                            "content" => ""
                        );
                        if($val["place_code"] != 'REP'){
                            $mNote[$val["place_code"]][0]['content'].='<p>※黄色で塗り潰したツアー以外に１名様でご参加される場合はタリフの料金に1.8倍の料金を加算させて頂きます。（混乗で複数名となった場合もご返金はございませんのでご了承ください）</p>
                                                        <p>但し、プロモーションツアー（黄色で塗り潰したツアー）については、1名様申込でも追加料金は頂きません。また、REPのみ条件が異なります。</p>
                                                        <p>※専用車は従来どおり、2名様参加時の1名様あたりの料金とさせていただき、1名様申込の場合は2名様分（2倍）の料金を徴収させていただきます。</p>';
                            if ($val["place_code"] == 'HAN' || $val["place_code"] == 'HUI' || $val["place_code"] == 'DAD' ||
                                $val["place_code"] == 'HOIA' || $val["place_code"] == 'NHA' || $val["place_code"] == 'SGN' ||
                                $val["place_code"] == 'DLI' || $val["place_code"] == 'HALO' || $val["place_code"] == 'PQC' ||
                                $val["place_code"] == 'SAPA' || $val["place_code"] == 'PTT'){
                                $mNote[$val["place_code"]][0]['content'].='<p style="color: red;">※ゴールデンウィーク中は専用車と専用ガイドの手配が難しい場合がございますので、お問い合わせ下さい。</p>';
                            }
                        }else{
                            $mNote[$val["place_code"]][0]['content'].='<p>黄色部分の混乗車料金はプロモーション価格です。(混乗車ツアーのみプロモーションとしてお一人の場合でも追加料金は頂きません。）</p>
                                                    <p style="color: red;">*REPT01、REPT02、REPF13、REPH08、REPH09、REPH10、REPH19以外の混乗車ツアーは2015年下期より御一人で参加の場合、1.8倍の料金を徴収させて頂きます。</p>
                                                    <p style="color: red;">（専用車ツアーは従来どおり御一人で参加の場合2倍の料金となりますが専用車一人用価格が入っているものはその価格にて催行いたします。）</p>
                                                    <p style="color: red;">*アンコールチケットが必要なツアーはチケット料金1日20$、2～3日40$、4日以上7日以内60$を加えて計算下さいませ。 </p>
                                                    <p style="color: red;">*お食事の往復送迎をご希望の場合は料金にお食事の往復送迎料金を加算下さいませ。（ガイドと車をお付けいたします）</p>
                                                    <p style="color: red;">*尚、料理内容は予告なく変更の場合もございますので予めご了承下さいます様お願い申し上げます。</p>';
                        }
                    }
                }
            }
        }

        $data = array("data"=>$mlist,"note"=>$mNote);*/

        $mlist = array();

        if(isset($rq_user["agent_code"]) && !empty($rq_user["agent_code"])){
            $rq_data["agent_code"] = $rq_user["agent_code"];
        }else{
            $rq_data["agent_code"] = $rq_data["search_agent"];
        }

        $rq_data["onl_flg"] = false;
        if (in_array($rq_data["agent_code"], $this->onlAgent)) {
            $rq_data["onl_flg"] = true;
        }

        if(isset($rq_user['search_season']) && !empty($rq_user['search_season'])){
            $season = json_decode($rq_user['search_season'],true);
        }

        if($rq_data["onl_flg"]){
            $select_agent = 'tour_price.tour_price_blt AS tour_price,tour_price.tour_price_blt_prc AS tour_price_prc';
        }else{
            $select_agent = 'tour_price.tour_price,tour_price.tour_price_prc';
        }

        $select = 'tour.tour_id,tour.tour_code,tour_price_name,tour_price_name_en,
        begin_date,end_date,' . $select_agent . ',tour_detail_hour,tour_detail_meal,tour_detail_lunch,
        tour_detail_dinner,tour_detail_guide,tour_option_simple_tariff,tour_detail_surcharge_info,
        tour_new_flg,tour_kamiki_flg,tour_shimoki_flg,tour_kamiki_basic,tour_shimoki_basic,
        tour_price_inter,tour_detail_id,tour_price_inter_2pax';

        if (isset($sess_search['search_season']) && !empty($sess_search['search_season'])) {
            foreach ($sess_search['search_season'] as $id_season => $search_season) {
                if (isset($sess_search['search_place']) && !empty($sess_search['search_place'])) {
                    foreach ($sess_search['search_place'] as $id_place => $search_place) {
                        if (isset($sess_search['search_year']) && !empty($sess_search['search_year'])) {
                            if (isset($sess_search['search_year']) && !empty($sess_search['search_year'])) {
                                if (isset($sess_search['search_tariff'])) {
                                    $mPlace = $this->PlaceModel->get($search_place);
                                    if ($search_season == 0) {
                                        $begin_date = strtotime($sess_search['search_year'] . '/04/01 0:0:0');
                                        $end_date   = strtotime($sess_search['search_year'] . '/09/30 23:59:59');
                                        $this->db->where('tour_kamiki_flg', 1);
                                    } else {
                                        $begin_date = strtotime($sess_search['search_year'] . '/10/01 0:0:0');
                                        $end_date   = strtotime(($sess_search['search_year'] + 1) . '/03/31 23:59:59');
                                        $this->db->where('tour_shimoki_flg', 1);
                                    }
                                    $this->db->join('tour_price', array('tour.tour_id' => 'tour_price.tour_id'));
                                    $this->db->join('tour_detail', array('tour_price.tour_price_id' => 'tour_detail.tour_price_id'), "", "LEFT");
                                    $this->db->where('tour.place_id', $search_place);
                                    $this->db->where('begin_date >=', ($begin_date ));
                                    $this->db->where('end_date <=', ($end_date ));
                                    if($begin_date>strtotime('2018/3/31 23:59:59')){
                                        $this->db->where('(LOWER(tour.tour_code) NOT LIKE "%'.strtolower($mPlace['place_code']).'l%" and LOWER(tour.tour_code) NOT LIKE "%'.strtolower($mPlace['place_code']).'b%" and LOWER(tour.tour_code) NOT LIKE "%'.strtolower($mPlace['place_code']).'d%")');
                                    }

                                    $this->db->where('(tour_price.tour_price <> 0 OR tour_price.tour_price_prc <> 0 OR tour_price.tour_price_inter <> 0)');
                                    $this->db->where('tour_status', 1);
                                    $this->db->orderby('tour.tour_code', 'ASC');
                                    $mTour = $this->tour_model->get_('', $select);
                                    // echo $this->db->last_query();die;
                                    $aTRF  = array();
                                    $aSTRF = array();
                                    $sql   = 'SELECT place_id,place_code,tour_price.tour_price,tour_price.tour_price_prc
                                    FROM tour INNER JOIN tour_price ON tour.tour_id = tour_price.tour_id
                                    WHERE tour_code LIKE "%TRF" AND begin_date>=' . ($begin_date) . ' AND end_date<=' . ($end_date ) . ' AND place_id = ' . $search_place;
                                    $mTrf = $this->db->query($sql)->result_array(false);

                                    $sql = 'SELECT place_id,place_code,tour_price.tour_price,tour_price.tour_price_prc
                                    FROM tour INNER JOIN tour_price ON tour.tour_id = tour_price.tour_id
                                    WHERE tour_code LIKE "%STF" AND begin_date>=' . ($begin_date ) . ' AND end_date<=' . ($end_date ) . ' AND place_id = ' . $search_place;
                                    $mSTrf = $this->db->query($sql)->result_array(false);
                                    // echo $this->db->last_query();die;
                                    //if(isset($mTrf)&&!empty($mTrf)){
                                    $aTRF['tour_price']     = (isset($mTrf[0]['tour_price']) && !empty($mTrf[0]['tour_price'])) ? $mTrf[0]['tour_price'] : 0;
                                    $aTRF['tour_price_prc'] = (isset($mTrf[0]['tour_price_prc']) && !empty($mTrf[0]['tour_price_prc'])) ? $mTrf[0]['tour_price_prc'] : 0;

                                    $aSTRF['tour_price']     = (isset($mSTrf[0]['tour_price']) && !empty($mSTrf[0]['tour_price'])) ? $mSTrf[0]['tour_price'] : 0;
                                    $aSTRF['tour_price_prc'] = (isset($mSTrf[0]['tour_price_prc']) && !empty($mSTrf[0]['tour_price_prc'])) ? $mSTrf[0]['tour_price_prc'] : 0;

                                    if (isset($mTour) && !empty($mTour)) {
                                        foreach ($mTour as $id_tour => $tour) {
                                            if (substr($tour['tour_code'], 3, 1) == 'L' || substr($tour['tour_code'], 3, 1) == 'D' || substr($tour['tour_code'], 3, 1) == 'A') {
                                                if (isset($tour['tour_price']) && !empty($tour['tour_price'])) {
                                                    $tour['tour_price'] += $aTRF['tour_price'];
                                                }

                                                if (isset($tour['tour_price_prc']) && !empty($tour['tour_price_prc'])) {
                                                    $tour['tour_price_prc'] += $aTRF['tour_price_prc'];
                                                }

                                                $mTour[$id_tour] = $tour;
                                            }

                                            if (substr($tour['tour_code'], 3, 1) == 'S') {
                                                if (isset($tour['tour_price']) && !empty($tour['tour_price'])) {
                                                    $tour['tour_price'] += $aSTRF['tour_price'];
                                                }

                                                if (isset($tour['tour_price_prc']) && !empty($tour['tour_price_prc'])) {
                                                    $tour['tour_price_prc'] += $aSTRF['tour_price_prc'];
                                                }

                                                $mTour[$id_tour] = $tour;
                                            }
                                        }
                                    }
                                    //}

                                    $mlist[$search_season][$mPlace['place_code']] = $mTour;
                                }
                            }
                        }
                    }
                }
            }
        }

        $mNote = array();

        $data = array("data"=>$mlist,"note"=>$mNote);

        return $data;
    }

}