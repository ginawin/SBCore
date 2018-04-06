<?php
/**
 * Class Array
 * Contain all method for array processing.
 * @category Library
 * @package Array
 * @author LP (le van phu) <vanphupc50@gmail.com>
 * @copyright 2018 SB Group
 * @version 1.0
 */

class SBArray
{
    private static $__instance = null;

    /**
     * Positionize elements of array as a circle.
     * @param  array  $array
     * @param  integer $resetAtIndex - Move the element at index to position 0.
     * @return array
     */
    public static function rotate($array, $resetAtIndex = 0) {
        $count = count($array);
        $sliceElements = self::slice($array, 0, $resetAtIndex, null, true);
        $repositionElements = self::slice($array, $resetAtIndex, null, true);
        foreach($sliceElements as $key => $element) {
            $repositionElements[$key] = $element;
        }

        return $repositionElements;
    }
    /**
     * Reverse array order.
     * @param  array  $array
     * @return array
     */
    public static function reverse($array = array()) {
        return array_reverse($array);
    }

    /**
     * Get last element of an array.
     * @param  array $array
     * @return mixed
     */
    public static function last($array) {
        return end($array);
    }

    /**
     * Check if a key exist in an array.
     * @param  string  $key
     * @param  array  $array
     * @return boolean
     */
    public static function has($key, $array) {
        return isset($array[$key]);
    }

    /**
     * Check if a key exist in an array.
     * @param  string  $key
     * @param  array  $array
     * @return boolean
     */
    public static function hasValue($value, $array) {
        return in_array($value, $array);
    }

    /**
     * Count number of element of an array.
     * @param  array  $array
     * @return integer
     */
    public static function count($array = array()) {
        return count($array);
    }

    /**
     * Check if an object is an array.
     * @param  mixed $array
     * @return boolean
     */
    public static function valid($array = null) {
        return is_array($array);
    }
    /**
     * Get first element of an array.
     * @param  array $array
     * @return mixed
     * @see http://php.net/manual/en/function.reset.php
     */
    public static function first($array) {
        return reset($array);
    }

    /**
     * Extract the slice of an array.
     * @param  array  $array
     * @param  integer  $offset
     * @param  integer  $length
     * @param  boolean $preserveKeys
     * @return Returns the slice. If the offset is larger than the size of the array then returns an empty array.
     * @see http://php.net/manual/en/function.array_slice.php
     */
    public static function slice($array, $offset, $length = null, $preserveKeys = false) {
        return array_slice($array, intval($offset), $length, $preserveKeys);
    }

    /**
     * Pop the element off the end of array.
     * Same as php array_pop but have option to preserve key.
     * @param  array $array
     * @return mixed       - Returns the last value of array. If array is empty (or is not an array), NULL will be returned.
     * @see http://php.net/manual/en/function.array-pop.php
     */
    public static function pop(&$array = array(), $preserveKeys = true) {
        if(empty($array) || !self::valid($array)) return null;

        if(false === $preserveKeys) return array_pop($array);

        $total = self::count($array);
        $count = 1;
        foreach($array as $key => $value) {
            if($count == $total) {
                self::remove($array, $key);
                return $value;
            }

            $count++;
        }
    }

    /**
     * Remove an element of array by it's key.
     * @param  array $array
     * @param  string $key
     */
    public static function remove(&$array, $key) {
        unset($array[$key]);

        return $array;
    }

    /**
     * Shift an element off the beginning of array.
     * Same as php array_shift but have option to preserve keys.
     * @param  array $array
     * @return mixed       - Returns the shifted value, or NULL if array is empty or is not an array.
     * @see http://php.net/manual/en/function.array-shift.php
     */
    public static function shift(&$array = array(), $preserveKeys = true) {
        if(empty($array) || !self::valid($array)) return null;

        if(false === $preserveKeys) return array_shift($array);

        foreach($array as $key => $value) {
            unset($array[$key]);
            return $value;
        }
    }

    /**
     * Remove elements of array by specific keys.
     * @param  array $array
     * @param  array  $keys - List of keys to remove.
     * @return array
     */
    public static function removeKeys($array, $keys = array()) {
        if(empty($keys)) return $array;

        foreach($keys as $key) {
            if(self::has($key, $array)) self::remove($array, $key);
        }

        return $array;
    }


    /**
     * Trim each elements of an array.
     * @param  array  $array
     * @return array
     * @see http://php.net/manual/en/function.trim.php
     */
    public static function trim(&$array = array(), $character_mask=" \t\n\r\0\x0B") {
        foreach($array as $key => $value) {
            $array[$key] = trim($value);
        }

        return $array;
    }

    /**
     * Remove all empty value of elements in array.
     * @param  array  $array
     * @return array
     */
    public static function removeEmpty(&$array = array(), $recursive = false) {
        if(!self::valid($array)) return $array;

        foreach($array as $key => $element) {
            if($recursive && self::valid($element)) $element = self::removeEmpty($element,$recursive);
            if(empty($element)) self::remove($array, $key);
        }

        return $array;
    }

    /**
     * Remove all null value (null or '') of element in array.
     * @param  array  $array
     * @return array
     */
    public static function removeNull(&$array = array(), $recursive = false) {
        if(!self::valid($array)) return $array;

        foreach($array as $key => $element) {
            if($recursive && self::valid($element)) {
                $element = self::removeNull($element,$recursive);
                if(empty($element)) self::remove($array, $key);
                continue;

                $array[$key] = $element;
                continue;
            }

            if(is_null($element) || $element == '') self::remove($array, $key);
        }

        return $array;
    }

    /**
     * Make an array unique value.
     * @param  array  $array
     * @return array
     * @see http://php.net/manual/en/function.array-unique.php
     */
    public static function unique($array = array(), $sortFlag = SORT_STRING) {
        return array_unique($array, $sortFlag);
    }

    /**
     * Parse all element's value of array to integer.
     * @param  array  $array - Array to parse its elements.
     * @return array        - Array with all value parsed to int.
     */
    public static function parseInt($array = array(),$removeNull = true, $unique = false) {
        if(!self::valid($array)) return false;

        foreach($array as $k => $v) {
            if($removeNull && empty($v)) {
                self::remove($k, $array);
                continue;
            }
            $array[$k] = intval($v);
        }

        if($unique === true) $array = self::unique($array);

        return $array;
    }


    /**
     * Get values of an array.
     * @param  array  $array - Array to get its values.
     * @return array        - Values of array.
     */
    public static function values($array = array()) {
        return array_values($array);
    }

    /**
     * Get list keys of an array.
     * @param  array  $array - Array to get its keys.
     * @return array        - List of keys.
     */
    public static function keys($array = array()) {
        return array_keys($array);
    }

    public static function add() {
        $arguments = func_get_args();
        $array = array();
        foreach($arguments as $argument) {
            if(self::valid($argument)) $array += $argument;
            elseif($argument !== null) $array += array($argument);
        }

        return $array;
    }

    /**
     * Merge all arguments to an array.
     * @return array - Array after merged.
     */
    public static function merge() {
        $arguments = func_get_args();
        $array = array();
        foreach($arguments as $argument) {
            if(self::valid($argument)) $array = array_merge($array,$argument);
            else $array = array_merge($array,array($argument));
        }

        return $array;
    }

    /**
     * Flatten a multi-dimensional array into a one dimensional array.
     * @param  array   $array         The array to flatten
     * @param  boolean $preserveKeys  Whether or not to preserve array keys.
     *                                Keys from deeply nested arrays will
     *                                overwrite keys from shallowy nested arrays
     * @return array
     */
    public static function flatten(array $array, $preserveKeys = true)
    {
        $mainArray = array();
        //foreach($array as $
    }

    /**
     * Make a simple array consisting of key=>value pairs, that can be used
     * in select-boxes in forms.
     *
     * @param array  $array
     * @param string $key
     * @param string $value
     *
     * @return array
     */
    public static function makeValuePairs($array, $key, $value)
    {
        $tempArray = array();

        if (self::valid($array)) {
            foreach ($array as $item) {
                if (empty($key)) {
                    $tempArray[] = $item[$value];
                } else {
                    $tempArray[$item[$key]] = $item[$value];
                }
            }
        }

        return $tempArray;
    }

    /**
     * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('new value'));
     *
     * Parameters are passed by reference, though only for performance reasons. They're not
     * altered by this function.
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     *
     * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     * @author Bob den Otter for Bolt-specific excludes
     */
    public static function mergeRecursiveDistinct(array &$array1, array &$array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            // if $key = 'accept_file_types, don't merge.
            if ($key == 'accept_file_types') {
                $merged[$key] = $array2[$key];
                continue;
            }

            if (self::valid($value) && isset($merged[$key]) && self::valid($merged[$key])) {
                $merged[$key] = static::mergeRecursiveDistinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Check if an array is indexed or associative.
     *
     * @param array $arr
     *
     * @return boolean True if indexed, false if associative
     */
    public static function isIndexedArray(array $arr)
    {
        foreach ($arr as $key => $val) {
            if ($key !== (int) $key) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $arr
     * @return bool
     */
    public static function isEmpty($arr){

        $null_flg = true;

        foreach($arr as $v){

            if(!empty($v)){

                $null_flg = false;

                break;

            }

        }

        return $null_flg;

    }

    public static function mapKeyVal($attr){
        if(!empty($attr)){
            return implode(" ",array_map(function($v,$k){
                return sprintf("%s='%s'", $k, $v);
            },$attr,SBArray::keys($attr)));
        }
        return "";
    }

    public static function getValByKey($arr,$key){
        if (isset($arr))
            return isset($arr[$key])?$arr[$key]:false;
        return false;
    }

    public static function checkEmpty($arr){
        $null_flg = true;
        foreach($arr as $v){
            if(!empty($v)){
                $null_flg = false;
                break;
            }
        }
        return $null_flg;
    }

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

    private function __construct()
    {
    }
}
