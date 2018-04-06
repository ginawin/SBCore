<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 3/13/2018
 * Time: 5:30 PM
 */
require_once dirname(dirname(__DIR__)) . "/vendor/drivers/Mysql.php";

class Database extends PDO
{

    // Database instances

    public static $instances = array();



    // Configuration

    protected $config = array

    (

        'benchmark'     => TRUE,

        'persistent'    => FALSE,

        'connection'    => '',

        'character_set' => 'utf8',

        'table_prefix'  => '',

        'object'        => TRUE,

        'cache'         => FALSE,

        'escape'        => TRUE,

    );



    // Database driver object

    protected $driver;



    // Un-compiled parts of the SQL query

    protected $select     = array();

    //protected $set        = array();

    protected $from       = array();

    protected $join       = array();

    protected $where      = array();

    protected $orderby    = array();

    //protected $order      = array();

    protected $groupby    = array();

    protected $having     = array();

    protected $distinct   = FALSE;

    protected $limit      = FALSE;

    protected $offset     = FALSE;

    public $last_query = '';

    public $last_error = '';

    public $last_update_id = '';



    // Stack of queries for push/pop

    protected $query_history = array();



    public static function & instance($name = 'conn1', $config = NULL)

    {

        if ( ! isset(Database::$instances[$name]))

        {

            // Create a new instance

            Database::$instances[$name] = new Database($config === NULL ? $name : $config);

        }



        return Database::$instances[$name];

    }



    public function __construct($config)

    {

        try{

            if(empty($config))

                echo new Error("Database Config Error");



            $this->config = array_merge($this->config, $config);



            $conn_str = '';

            if(isset($this->config['type'])&&!empty($this->config['type'])){

                $conn_str.=$this->config['type'].':';

            }

            if(isset($this->config['host'])&&!empty($this->config['host'])){

                $conn_str.='host='.$this->config['host'].';';

            }

            if(isset($this->config['database'])&&!empty($this->config['database'])){

                $conn_str.='dbname='.$this->config['database'].';';

            }

            if(isset($this->config['character_set'])&&!empty($this->config['character_set'])){

                $conn_str.='charset='.$this->config['character_set'].';';

            }

            if(isset($this->config['port'])&&!empty($this->config['port'])){

                $conn_str.='port='.$this->config['port'].';';

            }

            parent::__construct($conn_str,$this->config['user'],$this->config['pass']);



            $this->driver = new Database_Mysql_Driver($this->config);



        }catch(Exception $e){

            return  new Exception("There is no database selected");

        }

    }



    /**

     * Generates the JOIN portion of the query.

     *

     * @param   string        table name

     * @param   string|array  where array of key => value pairs

     * @param   string        type of join

     * @return  Database_Core        This Database object.

     */

    public function join($table, $key, $value = NULL, $type = '') {

        $join = array();



        if (!empty($type)) {

            $type = strtoupper(trim($type));



            if (!in_array($type, array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'), TRUE)) {

                $type = '';

            } else {

                $type .= ' ';

            }

        }



        $cond = array();

        $keys = is_array($key) ? $key : array($key => $value);

        if(!empty($keys)){

            foreach ($keys as $key => $value) {

                $key = (strpos($key, '.') !== FALSE) ? $this->config['table_prefix'] . $key : $key;

                if (is_string($value)) {

                    // Only escape if it's a string

                    $value = (strpos($value, '.') !== FALSE) ? $this->driver->escape_column($this->config['table_prefix'] . $value):$this->config['table_prefix'] . $value;

                }

                $cond[] = $this->driver->where($key, $value, 'AND ', count($cond), FALSE);

            }

        }

        if (!is_array($this->join)) {

            $this->join = array();

        }



        if (!is_array($table)) {

            $table = array($table);

        }



        foreach ($table as $t) {

            if (is_string($t)) {

                // TODO: Temporary solution, this should be moved to database driver (AS is checked for twice)

                if (stripos($t, ' AS ') !== FALSE) {

                    $t = str_ireplace(' AS ', ' AS ', $t);



                    list($table, $alias) = explode(' AS ', $t);



                    // Attach prefix to both sides of the AS

                    $t = $this->config['table_prefix'] . $table . ' AS ' . $this->config['table_prefix'] . $alias;

                } else {

                    $t = $this->config['table_prefix'] . $t;

                }

            }



            $join['tables'][] = $this->driver->escape_column($t);

        }



        $join['conditions'] = '(' . trim(implode(' ', $cond)) . ')';

        $join['type'] = $type;



        $this->join[] = $join;



        return $this;

    }



    /**

     * Selects the limit section of a query.

     *

     * @param   integer  number of rows to limit result to

     * @param   integer  offset in result to start returning rows from

     * @return  Database_Core   This Database object.

     */

    public function limit($limit, $offset = NULL) {

        $this->limit = (int) $limit;



        if ($offset !== NULL OR ! is_int($this->offset)) {

            $this->offset($offset);

        }



        return $this;

    }



    /**

     * Sets the offset portion of a query.

     *

     * @param   integer  offset value

     * @return  Database_Core   This Database object.

     */

    public function offset($value) {

        $this->offset = (int) $value;



        return $this;

    }



    /**

     * Adds an "IN" condition to the where clause

     *

     * @param   string  Name of the column being examined

     * @param   mixed   An array or string to match against

     * @param   bool    Generate a NOT IN clause instead

     * @return  Database_Core  This Database object.

     */

    public function in($field, $values, $not = FALSE) {

        if (is_array($values)) {

            $escaped_values = array();

            foreach ($values as $v) {

                if (is_numeric($v)) {

                    $escaped_values[] = $v;

                } else {

                    $escaped_values[] = "'" . $this->driver->escape_str($v) . "'";

                }

            }

            $values = implode(",", $escaped_values);

        }



        $where = $this->driver->escape_column(((strpos($field, '.') !== FALSE) ? $this->config['table_prefix'] : '') . $field) . ' ' . ($not === TRUE ? 'NOT ' : '') . 'IN (' . $values . ')';

        $this->where[] = $this->driver->where($where, '', 'AND ', count($this->where), -1);



        return $this;

    }



    /**

     * Adds a "NOT IN" condition to the where clause

     *

     * @param   string  Name of the column being examined

     * @param   mixed   An array or string to match against

     * @return  Database_Core  This Database object.

     */

    public function notin($field, $values) {

        return $this->in($field, $values, TRUE);

    }



    /**

     * Selects the where(s) for a database query.

     *

     * @param   string|array  key name or array of key => value pairs

     * @param   string        value to match with key

     * @param   boolean       disable quoting of WHERE clause

     * @return  Database_Core        This Database object.

     */

    public function where($key, $value = NULL, $quote = TRUE) {



        $quote = (func_num_args() < 2 AND ! is_array($key)) ? -1 : $quote;

        if (is_object($key)) {

            $keys = array((string) $key => '');

        } elseif (!is_array($key)) {

            if(is_array($value)){

                $this->in($key, $value);

                return $this;

            }else

                $keys = array($key => $value);

        } else {

            $keys = $key;

        }



        foreach ($keys as $key => $value) {

            $key = (strpos($key, '.') !== FALSE) ? $this->config['table_prefix'] . $key : $key;

            $this->where[] = $this->driver->where($key, $value, 'AND ', count($this->where), $quote);

        }

        return $this;

    }



    /**

     * Selects the or where(s) for a database query.

     *

     * @param   string|array  key name or array of key => value pairs

     * @param   string        value to match with key

     * @param   boolean       disable quoting of WHERE clause

     * @return  Database_Core        This Database object.

     */

    public function orwhere($key, $value = NULL, $quote = TRUE) {

        $quote = (func_num_args() < 2 AND ! is_array($key)) ? -1 : $quote;

        if (is_object($key)) {

            $keys = array((string) $key => '');

        } elseif (!is_array($key)) {

            $keys = array($key => $value);

        } else {

            $keys = $key;

        }



        foreach ($keys as $key => $value) {

            $key = (strpos($key, '.') !== FALSE) ? $this->config['table_prefix'] . $key : $key;

            $this->where[] = $this->driver->where($key, $value, 'OR ', count($this->where), $quote);

        }



        return $this;

    }



    /**

     * Chooses which column(s) to order the select query by.

     *

     * @param   string|array  column(s) to order on, can be an array, single column, or comma seperated list of columns

     * @param   string        direction of the order

     * @return  Database_Core        This Database object.

     */

    public function orderby($orderby, $direction = NULL) {

        if (!is_array($orderby)) {

            $orderby = array($orderby => $direction);

        }



        foreach ($orderby as $column => $direction) {

            $direction = strtoupper(trim($direction));



            // Add a direction if the provided one isn't valid

            if (!in_array($direction, array('ASC', 'DESC', 'RAND()', 'RANDOM()', 'NULL'))) {

                $direction = 'ASC';

            }



            // Add the table prefix if a table.column was passed

            if (strpos($column, '.')) {

                $column = $this->config['table_prefix'] . $column;

            }



            $this->orderby[] = $this->driver->escape_column($column) . ' ' . $direction;

        }



        return $this;

    }



    /**

     * Chooses the column to group by in a select query.

     *

     * @param   string  column name to group by

     * @return  Database_Core  This Database object.

     */

    public function groupby($by) {

        if (!is_array($by)) {

            $by = explode(',', (string) $by);

        }



        foreach ($by as $val) {

            $val = trim($val);



            if ($val != '') {

                // Add the table prefix if we are using table.column names

                if (strpos($val, '.')) {

                    $val = $this->config['table_prefix'] . $val;

                }



                $this->groupby[] = $this->driver->escape_column($val);

            }

        }



        return $this;

    }



    /**

     * Selects the having(s) for a database query.

     *

     * @param   string|array  key name or array of key => value pairs

     * @param   string        value to match with key

     * @param   boolean       disable quoting of WHERE clause

     * @return  Database_Core        This Database object.

     */

    public function having($key, $value = '', $quote = TRUE) {

        $this->having[] = $this->driver->where($key, $value, 'AND', count($this->having), TRUE);

        return $this;

    }



    /**

     * Selects the or having(s) for a database query.

     *

     * @param   string|array  key name or array of key => value pairs

     * @param   string        value to match with key

     * @param   boolean       disable quoting of WHERE clause

     * @return  Database_Core        This Database object.

     */

    public function orhaving($key, $value = '', $quote = TRUE) {

        $this->having[] = $this->driver->where($key, $value, 'OR', count($this->having), TRUE);

        return $this;

    }



    /**

     * Selects the or having(s) for a database query.

     *

     * @param   string|array  key name or array of key => value pairs

     * @param   string        value to match with key

     * @return  Database_Core        This Database object.

     */

    public function set($key, $value = '') {



        if(is_array($key)){

            $this->set = $key;

        }else{

            $this->set[$key] = $value;

        }

        return $this;

    }



    /**

     * Selects the column names for a database query.

     *

     * @param   string  string or array of column names to select

     * @return  Database_Core  This Database object.

     */

    public function select($sql = '*') {

        if (func_num_args() > 1) {

            $sql = func_get_args();

        } elseif (is_string($sql)) {

            $sql = explode(',', $sql);

        } else {

            $sql = (array) $sql;

        }



        foreach ($sql as $val) {

            if (($val = trim($val)) === '')

                continue;



            if (strpos($val, '(') === FALSE AND $val !== '*') {

                if (preg_match('/^DISTINCT\s++(.+)$/i', $val, $matches)) {

                    // Only prepend with table prefix if table name is specified

                    $val = (strpos($matches[1], '.') !== FALSE) ? $this->config['table_prefix'] . $matches[1] : $matches[1];



                    $this->distinct = TRUE;

                } else {

                    $val = (strpos($val, '.') !== FALSE) ? $this->config['table_prefix'] . $val : $val;

                }



                $val = $val;

            }



            $this->select[] = $val;

        }



        return $this;

    }





    public function doSelect($table_name){

        $this->from[] = $table_name;

        $database['select'] = $this->select;

        $database['from'] = $this->from;

        $database['join'] = $this->join;

        $database['where'] = $this->where;

        $database['orderby'] = $this->orderby;

        $database['groupby'] = $this->groupby;

        $database['having'] = $this->having;

        $database['distinct'] = $this->distinct;

        $database['limit'] = $this->limit;

        $database['offset'] = $this->offset;

        $sql = $this->driver->compile_select($database);

        if(!empty($sql)){

            $stmt = $this->prepare($sql);

            $this->reset_select();

            $result = $this->_execute($table_name,$stmt,"Select");

            return $result;

        }

        return false;

    }



    public function doSave($table_name){

        $table_name = $this->driver->escape_table($table_name);

        //init set string

        $str_set = $this->_setParam($this->set);

        if(!empty($str_set)){

            $sql = "INSERT INTO $table_name SET $str_set ON DUPLICATE KEY UPDATE $str_set;";

            $stmt = $this->prepare($sql);

            $this->_bindParam($stmt, $this->set,$table_name);

            $this->reset_write();

            $result = $this->_execute($table_name,$stmt,"Save");

            return $result;

        }

        return false;

    }



    public function doInsert($table_name){

        $table_name = $this->driver->escape_table($table_name);

        //init set string

        $str_set = $this->_setParam($this->set);

        if(!empty($str_set)){

            $sql = "INSERT INTO $table_name SET $str_set;";

            $stmt = $this->prepare($sql);

            $this->_bindParam($stmt, $this->set);

            $this->reset_write();

            $result = $this->_execute($table_name,$stmt,"Insert");

            return $result;

        }

        return false;

    }



    public function doUpdate($table_name){

        $table_name = $this->driver->escape_table($table_name);

        //init set string

        $str_set = $this->_setParam($this->set);

        //init where string

        $str_where = $this->_setWhere();



        if(!empty($str_set)){

            $sql = "UPDATE $table_name SET $str_set ".(!empty($str_where)?$str_where:"").";";

            $stmt = $this->prepare($sql);

            $this->_bindParam($stmt, $this->set);

            $this->reset_write();

            $result = $this->_execute($table_name,$stmt,"Update");

            return $result;

        }

        return false;

    }



    public function doDelete($table_name){

        $table_name = $this->driver->escape_table($table_name);

        $str_where = $this->_setWhere();

        if(!empty($str_where)){

            $sql = "DELETE FROM $table_name $str_where;";

            $stmt = $this->prepare($sql);

            $this->reset_write();

            $result = $this->_execute($table_name,$stmt,"Delete");

            return $result;

        }

        return false;

    }



    private function _setWhere(){

        if (!empty($this->where) && is_array($this->where)) {

            $str = "\nWHERE ".implode("\n", $this->where);

            return $str;

        }

        return "";

    }



    private function _setParam($arr){

        if (!empty($arr) && is_array($arr)) {

            $temp = array();

            foreach($arr as $key => $val){

                $temp[] = $key.' = :'.$key;

            }

            $str = implode(',',$temp);

            unset($temp);

            return $str;

        }

        return "";

    }



    private function _bindParam(&$stmt,$arr,$table_name=""){

        if (!empty($arr) && is_array($arr)) {

            $time = time();

            foreach($arr as $key=>$val){

                $_key = $key.$time;

                $stmt->bindParam(":$key", $$_key);

                $$_key = $val;

            }

        }

    }



    private function _execute($table_name,$stmt,$func){

        $stmt->execute();

        $sqlError = $stmt->errorInfo();

        $this->last_query = $stmt->queryString;

        if(is_array($sqlError)&&isset($sqlError[0])&&$sqlError[0]!='0000'){

            $this->last_error = $sqlError[2];

            $result = false;

            Logs::wr($table_name." $func ".$sqlError[2]);

        }else{

            /*switch($func){

                case "Save":

                    $this->last_update_id = $this->lastInsertId();

                    break;

                case "Insert":

                    $this->last_update_id = $this->lastInsertId();

                    break;

                case "Update":

                    $this->last_update_id = $this->lastInsertId();

                    break;

            }*/

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if(empty($result))

                $result = true;

        }

        return $result;

    }



    /**

     * Resets all private select variables.

     *

     * @return  void

     */

    public function reset_select()

    {

        $this->select   = array();

        $this->from     = array();

        $this->join     = array();

        $this->where    = array();

        $this->orderby  = array();

        $this->groupby  = array();

        $this->having   = array();

        $this->distinct = FALSE;

        $this->limit    = FALSE;

        $this->offset   = FALSE;

    }



    /**

     * Resets all private insert and update variables.

     *

     * @return  void

     */

    protected function reset_write()

    {

        $this->set   = array();

        $this->from  = array();

        $this->where = array();

    }



    public function callStore($storeName,$data){



        if(empty($storeName)){

            throw new SBException("Store cannot empty");

        }

        if(empty($data) && !is_array($data)){

            throw new SBException("Data Error");

        }



        $arrKey = '';

        foreach($data as $key=>$val){

            $arrKey[] = ':'.$key;

        }

        $strKey = implode(',',$arrKey);

        $stmt = $this->prepare("CALL $storeName($strKey);");

        foreach($data as $key=>$val){

            $stmt->bindParam(':'.$key, $$key);

            $$key = $val;

        }

        $stmt->execute();

        $sqlError = $stmt->errorInfo();

        if(is_array($sqlError)&&isset($sqlError[0])&&$sqlError[0]!='0000'){

            $this->last_error = $sqlError[2];

            $result = false;

        }else{

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if(empty($result))

                $result = true;

        }

        return $result;

    }

    public function getTableColumns($tableName){

        try{

            $stmt = $this->prepare("CALL SP_0000_GET_TABLE_COLUMNS(:tableName);");

            $stmt->bindParam(':tableName', $tableName);

            $rs = $stmt->execute();

            $sqlError = $stmt->errorInfo();

            if(is_array($sqlError)&&isset($sqlError[0])&&$sqlError[0]!='0000'){

                throw new SBException((isset($sqlError[2]))?('Code: '.$sqlError[1].'<br/>Type: MySQL Error<br/>Fatal Error: '.$sqlError[2]):'SQL Error.');

            }

            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if(empty($columns) && !is_array($columns)){

                throw new SBException("Table <i>$tableName</i> Has Error");

            }

            /*$data = array();

            foreach($columns as $col){

                $data[$col['COLUMN_NAME']] = '';

            }*/

            return $columns;

        }catch(Exception $e){

            throw new SBException('Fatal Error: '.$e->getMessage().' File: '.$e->getFile().' Line: '.$e->getLine());

        }

    }

}