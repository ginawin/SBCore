<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 3/22/2018
 * Time: 9:03 AM
 */

class SBHtml
{
    /**
     * @param $text
     * @param array $attr
     * @return string
     */
    public static function link($text,$attr = array()){
        return "<a ".SBArray::mapKeyVal($attr).">".$text."</a>";
    }

    /**
     * @param $text
     * @param $for
     * @param array $attr
     * @return string
     */
    public static function label($text,$attr = array()){
        return "<label ".SBArray::mapKeyVal($attr).">".$text."</label>";
    }

    /**
     * @param array $attr
     * @return string
     */
    public static function input($attr = array()){
        return "<input ".SBArray::mapKeyVal($attr)."/>";
    }

    /**
     * @param array $attr
     * @param bool $checked
     * @return string
     */
    public static function checkbox($attr = array(),$checked = false){

        return "<input type='checkbox' ".SBArray::mapKeyVal($attr).(($checked)?" checked":"")." />";
    }

    /**
     * @param array $attr
     * @return string
     */
    public static function datepicker($attr = array()){
        $for = isset($attr['id'])&&!empty($attr['id'])?('for="'.$attr['id'].'"'):'';
        $attr['class'] .= " datepk";
        $str_ext = '<div class="input-group-append"><label '.$for.' class="input-group-text cur-pointer"><i class="fas fa-calendar-alt"></i></label></div>';
        return "<div class='input-group'><input ".SBArray::mapKeyVal($attr)."/>".$str_ext."</div>";
    }

    /**
     * @param $text
     * @param array $attr
     * @return string
     */
    public static function button($text,$attr = array()){
        return "<button ".SBArray::mapKeyVal($attr).">".$text."</button>";
    }

    /**
     * @param $data
     * @param $selected
     * @param array $attr
     * @return string
     * $data[] = array("text"=>"Thanh1","value"=>1,"attr"=>array("class"=>"abc"));
        $data[] = array("text"=>"Thanh2","value"=>2,"attr"=>array("class"=>"abc"));
        $data[] = array("text"=>"Thanh3","value"=>3,"attr"=>array("class"=>"abc"));
     */
    public static function select($data,$selected,$attr = array(),$allFlg = true,$allText = ""){
        $html = "<select ".SBArray::mapKeyVal($attr).">\n";
        if($allFlg){
            $html .= "<option value=''>$allText</option>";
        }
        if(!empty($data) && SBArray::valid($data)){
            foreach($data as $keyData => $valData){
                $html .= "<option ".(($valData['value'] == $selected)?"selected":"")." value='".$valData['value']."' ". (SBArray::valid($valData['attr'])?SBArray::mapKeyVal($valData['attr']):"") .">".$valData['text']."</option>\n";
            }
        }
        $html .= "</select>";
        return $html;
    }

    // /**
    //  * [input create auto]
    //  * @param  array  $attr - array( "type" => "text", "name"=>"name", "id"=>"id", class => "form-control col" )
    //  * @return [type]       [description]
    //  */
    // public static function input($attr = array()){
    //     $str_attr = "";
    //     $str_ext = "";
    //     if(!empty($attr)){
    //         $str_attr = implode(" ",array_map(function($v,$k){
    //             return sprintf("%s='%s'", $k, $v);
    //         },$attr,SBArray::keys($attr)));
        
    //         $str_ext = '<div class="input-group-append"><label for="'.$attr['id'].'" class="input-group-text cur-pointer"><i class="fas fa-calendar-alt"></i></label></div>';
    //     }
    //     return "<div class='input-group'><input ".$str_attr."/>".$str_ext."</div>";
    // }


//    public static function checkHtml($attr,$value="",$select = array()){
//        $cFun = "";
//        switch ($attr['control']) {
//            case 'input':
//                if(!empty($attr['controlType'])) {
//                    SBArray::remove($attr,'control');
//                    SBArray::remove($attr,'COLUMN_NAME');
//                    SBArray::remove($attr,'text');
//                    $attr['type']=$attr['controlType'];
//                    if(!empty($value)) $attr['value'] = $value;
//                    SBArray::remove($attr,'controlType');
//                    return self::{"__".$attr['type']}($attr);
//                }
//                break;
//
//            case 'textarea':
//                SBArray::remove($attr,'controlType');
//                return self::{"__".$attr['control']}($attr);
//                break;
//
//            case 'select':
//                SBArray::remove($attr,'controlType');
//                return self::{"__".$attr['control']}($attr,$value,$select);
//                break;
//
//            default:
//                # code...
//                break;
//        }
//        // if(!empty($cFun)) return self::{$cFun}($attr,$__config);
//
//
//    }
//
//
//    private static function __datepicker($attr = array()){
//        $str_attr = "";
//        $str_ext = "";
//        if(!empty($attr)){
//            $attr['class'] .= " datepk";
//            $str_attr = implode(" ",array_map(function($v,$k){
//                return sprintf("%s='%s'", $k, $v);
//            },$attr,SBArray::keys($attr)));
//
//            $str_ext = '<div class="input-group-append"><label for="'.$attr['id'].'" class="input-group-text cur-pointer"><i class="fas fa-calendar-alt"></i></label></div>';
//            return "<div class='input-group'><input ".$str_attr."/>".$str_ext."</div>";
//        }
//        return false;
//    }
//
//    private static function __number($attr){
//        $str_attr = "";
//        if(!empty($attr)){
//            $str_attr = implode(" ",array_map(function($v,$k){
//                return sprintf("%s='%s'", $k, $v);
//            },$attr,SBArray::keys($attr)));
//            return "<div class='input-group'><input ".$str_attr."/></div>";
//        }
//        return false;
//    }
//
//    private static function __text($attr){
//        $str_attr = "";
//        if(!empty($attr)){
//            $str_attr = implode(" ",array_map(function($v,$k){
//                return sprintf("%s='%s'", $k, $v);
//            },$attr,SBArray::keys($attr)));
//            return "<div class='input-group'><input ".$str_attr."/></div>";
//        }
//        return false;
//    }
//
//    private static function __email($attr){
//        $str_attr = "";
//        if(!empty($attr)){
//            $str_attr = implode(" ",array_map(function($v,$k){
//                return sprintf("%s='%s'", $k, $v);
//            },$attr,SBArray::keys($attr)));
//            return "<div class='input-group'><input ".$str_attr."/></div>";
//        }
//        return false;
//    }
//
//    private static function __password($attr){
//        $str_attr = "";
//        if(!empty($attr)){
//            $str_attr = implode(" ",array_map(function($v,$k){
//                return sprintf("%s='%s'", $k, $v);
//            },$attr,SBArray::keys($attr)));
//            return "<div class='input-group'><input ".$str_attr."/></div>";
//        }
//        return false;
//    }
//
//    private static function __checkbox($attr){
//        $str_attr = "";
//        if(!empty($attr)){
//            $str_attr = implode(" ",array_map(function($v,$k){
//                return sprintf("%s='%s'", $k, $v);
//            },$attr,SBArray::keys($attr)));
//            return "<div class='input-group'><input ".$str_attr."/></div>";
//        }
//        return false;
//    }
//
//    private static function __radio($attr){
//        $str_attr = "";
//        if(!empty($attr)){
//            $str_attr = implode(" ",array_map(function($v,$k){
//                return sprintf("%s='%s'", $k, $v);
//            },$attr,SBArray::keys($attr)));
//            return "<div class='input-group'><input ".$str_attr."/></div>";
//        }
//        return false;
//    }
//
//    private static function __textarea($attr){
//        $str_attr = "";
//        if(!empty($attr)){
//            $str_attr = implode(" ",array_map(function($v,$k){
//                return sprintf("%s='%s'", $k, $v);
//            },$attr,SBArray::keys($attr)));
//            return "<div class='input-group'><textarea ".$str_attr."/></textarea></div>";
//        }
//        return false;
//    }
//
//    private static function __select($attr,$value,$oData){
//        $str_attr = "";
//        $str_text = $attr['text'];
//        $arr_select = $attr['select'];
//        SBArray::remove($attr,'text');
//        SBArray::remove($attr,'COLUMN_NAME');
//        SBArray::remove($attr,'select');
//        $html = "";
//        if(!empty($attr)){
//            $str_attr = implode(" ",array_map(function($v,$k){
//                return sprintf("%s='%s'", $k, $v);
//            },$attr,SBArray::keys($attr)));
//            $html  = "<div class='input-group'><select ".$str_attr."/>";
//            $html .= "<option>---- ".$str_text." ----</option>";
//            foreach ($oData as $keyData => $valData) {
//                $selected = ($valData[$arr_select['value']]===$value)?"selected":'';
//                $html .= "<option value='".$valData[$arr_select['value']]."' ".$selected.">".$valData[$arr_select['text']]."</option>";
//            }
//            $html .= "</select></div>";
//            return $html;
//        }
//        return false;
//    }

}