<?php

/**
* Job Order Types Library
* @package OpenCATS
* @subpackage Library
* @copyright (C) OpenCats
* @license GNU/GPL, see license.txt
* OpenCATS is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License 2
* as published by the Free Software Foundation.
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

