<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/5/2018
 * Time: 10:04 AM
 */

class AgentModel extends Model
{
    public $table_name = 'agent';
    public $primary_key = array('agent_id');
    
    public function __construct()
    {
        parent::__construct();
    }
    
}

