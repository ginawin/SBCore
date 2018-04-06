<?php
/**
 * Class String
 * @category Library
 * @package String
 * @author LP (Le Van Phu) <vanphupc50@gmail.com>
 * @copyright 2004-2016 SB Group
 * @version 1.0
 */

require_once dirname(dirname(__DIR__)) . '/vendor/Stringy/src/Stringy.php';
use Stringy\Stringy as S;

class SBString
{
    private static $__instance = null;

    /**
     * GetInstance
     * @return $__instance
     */
    public static function getInstance()
    {
        if (null === self::$__instance) {
            self::$__instance = new self();
        }

        return self::$__instance;
    }


    public static function valid($value = "") {
        return is_string($value);
    }
    
    public static function rightTrim($string = "", $characterMask = " \t\n\r\0\x0B") {
        return rtrim($string, $characterMask);
    }

    public static function trim($string = "", $characterMask = " \t\n\r\0\x0B") {
        return trim($string, $characterMask);
    }

    public static function length($string = "") {
        return strlen($string);
    }

    public static function replace($string = "",$needle  = "",$replacement = "") {
        return str_replace($needle,$replacement,$string);
    }

    /**
     * Check if a string has a substring.
     * @param  string  $string - String to check.
     * @param  string  $needle  - Sub-string to check.
     * @param boolean $beforeNeedle - Return portion before or after needle.
     * @return mixed         - Portion of string, or false if needle is not found.
     * @see http://php.net/manual/en/function.strstr.php
     */
    public static function has($string = "", $needle = "", $beforeNeedle = false) {
        return strstr($string,$needle,$beforeNeedle);
    }

    public static function toTitle($string = "") {
        return ucwords($string);
    }

    public static function toLower($string = "") {
        return strtolower($string);
    }

    public static function toUpper($string = "") {
        return strtoupper($string);
    }

    public static function cleanNumericInput($string = "") {
        $string = str_replace(",","",$string);
        $parts = explode(" ",$string);
        $number = 0;
        foreach($parts as $part) {
            if(is_numeric($part)) {
                $number = $part;
                break;
            }
        }

        return $number;
    }

    /**
     * Removing last element of a string separated by a character.
     * @param  string $string - String to remove.
     * @param  string $sep    - Separated character.
     * @return string         - String after removed.
     */
    public static function removeLast($string = "", $sep = ".")
    {
        $lpos = strrpos($string, $sep);
        if ($lpos === false) {
            return $string;
        }

        return substr($string, 0, $lpos);
    }

    /**
     * Removing first element of a string separated by a character.
     * @param  string $string - String to remove.
     * @param  string $sep    - Separated character.
     * @return string         - String after removed.
     */
    public static function removeFirst($string = "", $sep = ".", $notEmpty = false)
    {
        $subString = trim(strstr($string, $sep), $sep);
        if(empty($subString) && $notEmpty) return $string;

        return $subString;
    }

    /**
     * Getting first element of a string separated by a character.
     * @param $string (string) - string to get its first element.
     * @param $sep (string) - separated characters.
     * @return string - first element of string.
     */
    public static function getFirst($string = "", $sep = "/")
    {
        $str = strstr($string, $sep, true);
        if ($str === false) {
            $str = $string;
        }

        return $str;
    }

    /**
     * Getting last element of a string separated by a characters.
     * @param $string (string) - string to get its last element.
     * @param $sep (string) - separated characters.
     * @return string - last element of string.
     */
    public static function getLast($string = "", $sep = "/")
    {
        $lpos = strrpos($string, $sep);
        if ($lpos === false) {
            return $string;
        }

        return substr($string, $lpos + strlen($sep));
    }

    public static function explode($string = "", $sep = "/") {
        return explode($sep, $string);
    }

    /**
     * Like php implode() function but can wrap with format.
     * @param $sep (string) - Separator string.
     * @param $values (array) - array of values.
     * @param $format (string) - Format of string, must have %s as variable will be replaced with values.
     * @return string - String wrapped with prefix.
     */
    public static function implode($sep = "", $values = array(), $format = "")
    {
        if(empty($format)) return implode($sep,$values);

        $count  = count($values);
        $string = trim(str_repeat($format . $sep, $count), $sep);
        $string = vsprintf($string, $values);

        return $string;
    }

    /**
     * GetLibraryPath
     * @param  string $libName
     * @return string
     */
    public static function getLibraryPath($libName = "")
    {
        return LIBS_PATH . $libName . DS;
    }

    

    /**
     * [filterDataKey Use for setting and language key]
     * @param  string $key data key need filter
     * @return string return data key safe
     */
    public static function filterDataKey($key)
    {
        return preg_replace("/[^a-zA-Z0-9_]+/", '_', $key);
    }

    /**
     * Convert string to alpha numeric and unicode characters.
     * @param $string - String to convert.
     * @return string - Clean string with only alpha numeric and unicode characters.
     */
    public static function unicodeAlphaNumeric($string)
    {
        $clean = preg_replace("/[^\p{L}|\p{N}]+/u", " ", $string);
        $clean = preg_replace("/[\p{Z}]{2,}/u", " ", $clean);

        return $clean;
    }

    /**
     * Filter a string and only allow alphanumeric characters.
     * @param  string $string - String to filter.
     * @return string - String after filtered.
     */
    public static function alphaNumeric($string)
    {
        return preg_replace('~[^a-z0-9]+~i', '', $string);
    }

    /**
     * ConvertDataKey2Humanize
     * @param  string $s
     * @return string
     */
    public static function convertDataKey2Humanize($s)
    {
        if (empty($s)) {
            return '';
        }

        return S::create($s)->humanize()->__toString();
    }

    public static function htmlEncode($string, $flag = ENT_QUOTES)
    {
        return S::create($string)->htmlEncode($flag)->__toString();
    }

    public static function htmlDecode($string, $flag = ENT_QUOTES)
    {
        return S::create($string)->htmlDecode($flag)->__toString();
    }

    /**
     * [convertStr2ListOption convert data string to array list option, use for setting]
     * @param  string $s [string need convert, ex:
     * "one = 1
     * two = 2
     * three = 3"]
     * @return array    [array list option, ex:
     * array(
     *     'one' => 1,
     *     'two' => 2,
     *     'three' => 3
     * )]
     */
    public static function convertStr2ListOption($s)
    {
        if (empty($s)) {
            return '';
        }

        $arrList = explode('<br>', nl2br($s, false));
        $arr     = array();

        foreach ($arrList as $k => $v) {
            if (empty($v)) {
                unset($arrList[$k]);
            } else {
                $tmp = explode('=', str_replace(' ', '', trim($v)));

                if (count($tmp) > 1) {
                    $arr[$tmp[0]] = $tmp[1];
                }
            }
        }

        return $arr;
    }

    /**
     * RemoveLeft
     * @param  string $string
     * @param  string $substring
     * @return string
     */
    public static function removeLeft($string = '', $substring = '')
    {
        return S::create($string)->removeLeft($substring);
    }

    public static function matchUrls($str = '')
    {
        $result = Match::getUrls($str);

        return $result;
    }

    public static function matchImages($str = '')
    {
        $result = Match::getImages($str);

        return $result;
    }

    public static function matchEmails($str = '')
    {
        $result = Match::getEmails($str);

        return $result;
    }

    public static function stripUnicode($str)
    {
        if (!$str) {
            return false;
        }

        $unicode = array(
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'd' => 'đ',
            'D' => 'Đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
            'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ'
        );
        foreach ($unicode as $nonUnicode => $uni) {
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
        }

        return $str;
    }

    public static function stripUnicodeLatin($str){
        if(!$str) return '';
        //$str = str_replace($a, $b, $str);
        $unicode = array(
            'a'=>'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ|å|ä|æ|ā|ą|ǻ|ǎ',
            'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ|Å|Ä|Æ|Ā|Ą|Ǻ|Ǎ',
            'ae'=>'ǽ',
            'AE'=>'Ǽ',
            'c'=>'ć|ç|ĉ|ċ|č',
            'C'=>'Ć|Ĉ|Ĉ|Ċ|Č',
            'd'=>'đ|ď',
            'D'=>'Đ|Ď',
            'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ|ë|ē|ĕ|ę|ė',
            'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ|Ë|Ē|Ĕ|Ę|Ė',
            'f'=>'ƒ',
            'F'=>'',
            'g'=>'ĝ|ğ|ġ|ģ',
            'G'=>'Ĝ|Ğ|Ġ|Ģ',
            'h'=>'ĥ|ħ',
            'H'=>'Ĥ|Ħ',
            'i'=>'í|ì|ỉ|ĩ|ị|î|ï|ī|ĭ|ǐ|į|ı',   
            'I'=>'Í|Ì|Ỉ|Ĩ|Ị|Î|Ï|Ī|Ĭ|Ǐ|Į|İ',
            'ij'=>'ĳ',    
            'IJ'=>'Ĳ',
            'j'=>'ĵ',     
            'J'=>'Ĵ',
            'k'=>'ķ',     
            'K'=>'Ķ',
            'l'=>'ĺ|ļ|ľ|ŀ|ł',     
            'L'=>'Ĺ|Ļ|Ľ|Ŀ|Ł',
            'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ|ö|ø|ǿ|ǒ|ō|ŏ|ő',
            'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ|Ö|Ø|Ǿ|Ǒ|Ō|Ŏ|Ő',
            'Oe'=>'œ',
            'OE'=>'Œ',
            'n'=>'ñ|ń|ņ|ň|ŉ',
            'N'=>'Ñ|Ń|Ņ|Ň',
            'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự|û|ū|ŭ|ü|ů|ű|ų|ǔ|ǖ|ǘ|ǚ|ǜ',
            'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự|Û|Ū|Ŭ|Ü|Ů|Ű|Ų|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ',
            's'=>'ŕ|ŗ|ř',
            'R'=>'Ŕ|Ŗ|Ř',
            's'=>'ß|ſ|ś|ŝ|ş|š',
            'S'=>'Ś|Ŝ|Ş|Š',
            't'=>'ţ|ť|ŧ',
            'T'=>'Ţ|Ť|Ŧ',
            'w'=>'ŵ',
            'W'=>'Ŵ',
            'y'=>'ý|ỳ|ỷ|ỹ|ỵ|ÿ|ŷ',
            'Y'=>'Ý|Ỳ|Ỷ|Ỹ|Ỵ|Ÿ|Ŷ',
            'z'=>'ź|ż|ž',
            'Z'=>'Ź|Ż|Ž'
        );
        foreach($unicode as $khongdau=>$codau) {
            $arr=explode("|",$codau);
            $str = str_replace($arr,$khongdau,$str);
        }
        return $str;
    }

    public static function action($funcName = '', $inputData = array())
    {
        if (!function_exists($funcName)) {
            return false;
        }

        $inputData = array_map($funcName, $inputData);

        return $inputData;
    }

    public static function encrypt($pure_string, $secretKey = '')
    {
        $secretKey = isset($secretKey[5]) ? $secretKey : ENCRYPT_SECRET_KEY;

        $iv_size          = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv               = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $secretKey, utf8_encode($pure_string), MCRYPT_MODE_ECB, $iv);

        $encrypted_string = base64_encode($encrypted_string);

        return $encrypted_string;
    }

    public static function decrypt($encrypted_string, $secretKey = '')
    {
        $secretKey = isset($secretKey[5]) ? $secretKey : ENCRYPT_SECRET_KEY;

        $encrypted_string = base64_decode($encrypted_string);

        $iv_size          = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv               = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $secretKey, $encrypted_string, MCRYPT_MODE_ECB, $iv);
        return $decrypted_string;

    }

    public static function encode($str)
    {
        $str = addslashes($str);

        return $str;
    }

    public static function decode($text)
    {
        $text = stripslashes($text);

        return $text;
    }

    public static function trimLines($Str = '')
    {
        $parseStr = explode("\r\n", $Str);

        $totalLines = count($parseStr);

        $strResult = '';

        for ($i = 0; $i < $totalLines; $i++) {
            if ($parseStr[$i] != '') {
                $strResult .= trim($parseStr[$i]) . "\r\n";
            }

        }

        return $strResult;
    }

    public static function clearSpace($Str = '')
    {
        if (isset($Str[1])) {
            preg_match_all('/([\w\S]+)/i', $Str, $matches);

            $strResult = implode(' ', $matches[1]);

            return $strResult;
        }

        return $Str;
    }

    public static function utf8ToLower($inputData)
    {
        $inputData = mb_convert_case(trim($inputData), MB_CASE_LOWER, "UTF-8");
    }

    public static function utf8ToUpper($inputData)
    {
        $inputData = mb_convert_case(trim($inputData), MB_CASE_UPPER, "UTF-8");
    }

    public static function utf8ToTitle($inputData)
    {
        $inputData = mb_convert_case(trim($inputData), MB_CASE_TITLE, "UTF-8");
    }

    public static function split($Char = '', $Str = '')
    {
        $strResult = explode($Char, $Str);

        return $strResult;
    }

    public static function randNumber($len = 10)
    {
        $str = '012010123456789234560123450123456789234560123456789789345012345601234567892345601234567897893450123456678978934501234567896789';

        $str = substr(str_shuffle($str), 0, $len);

        return $str;

    }

    public static function randAlpha($len = 10)
    {
        $str = 'abcdefghijklmnopfghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUqrstufghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $str = substr(str_shuffle($str), 0, $len);

        return $str;
    }

    public static function randomText($len = 10)
    {
        $str = 'abcdefghijkl123456789mnpqrstuvwxyzhijklmnpqrs123456789tuvwxyzABCDEFGHIJKLM123456789NPQRSTUVWXYZ1234567ABCDEFGHIJKLMNPQRSTUVWXYZ123456789';

        $str = substr(str_shuffle($str), 0, $len);

        return $str;
    }

    public static function random($length = 10)
    {
        $key  = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));

        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }

        return $key;
    }

   

    public static function safeTruncate($string, $length, $substr = '...')
    {
        return S::create($string)->safeTruncate($length, $substr)->__toString();
    }

    public static function truncate($string, $length, $substr = '...')
    {
        return S::create($string)->truncate($length, $substr)->__toString();
    }

    /*-------------------------------------------------------------------------*/
    //
    // Create a random 8 character password
    //
    /*-------------------------------------------------------------------------*/
    public static function makePassword()
    {
        $pass  = "";
        $chars = array(
            "1", "2", "3", "4", "5", "6", "7", "8", "9", "0",
            "a", "A", "b", "B", "c", "C", "d", "D", "e", "E", "f", "F", "g", "G", "h", "H", "i", "I", "j", "J",
            "k", "K", "l", "L", "m", "M", "n", "N", "o", "O", "p", "P", "q", "Q", "r", "R", "s", "S", "t", "T",
            "u", "U", "v", "V", "w", "W", "x", "X", "y", "Y", "z", "Z");

        $count = count($chars) - 1;

        srand((double) microtime() * 1000000);

        for ($i = 0; $i < 8; $i++) {
            $pass .= $chars[rand(0, $count)];
        }

        return ($pass);
    }

    public static function ipAddress($ipaddress = '')
    {
        if (getenv('HTTP_CLIENT_IP')) {
            $ipaddress = getenv('HTTP_CLIENT_IP');
        } else if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('HTTP_X_FORWARDED')) {
            $ipaddress = getenv('HTTP_X_FORWARDED');
        } else if (getenv('HTTP_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        } else if (getenv('HTTP_FORWARDED')) {
            $ipaddress = getenv('HTTP_FORWARDED');
        } else if (getenv('REMOTE_ADDR')) {
            $ipaddress = getenv('REMOTE_ADDR');
        } else {
            $ipaddress = 'UNKNOWN';
        }

        return $ipaddress;
    }
}
