<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/13/2018
 * Time: 10:21 AM
 * @see https://github.com/vlucas/valitron
 */

class SBValidator extends \Valitron\Validator
{
    public function __construct(array $data = array(), array $fields = array(), $lang = null, $langDir = null)
    {
        parent::__construct($data, $fields, $lang, $langDir);
    }
}