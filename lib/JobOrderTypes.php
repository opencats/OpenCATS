<?php

/**
* Job Order Types Library
* @package OpenCATS
* @subpackage Library
* @copyright (C) OpenCats
*/

class JobOrderTypes
{
    private $_defaultTypes;
        

    public function __construct() {
        $this->_defaultTypes = array(
            'C' => 'Contract',
            'C2H' => 'Contract To Hire',
            'FL' => 'Freelance',
            'H' => 'Hire'
        );
    }

    /**
     * Returns job order types from config or default
     *
     * @return job order types from config or if undefined, then default
     */
    public function getAll()
    {
        if(defined('JOB_TYPES_LIST'))
        {
            return JOB_TYPES_LIST;
        } 
        else 
        {
            return $this->_defaultTypes;
        }
    }
}

