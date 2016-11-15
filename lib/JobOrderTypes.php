<?php

/**
 * CATS
 * Job Order Types Library
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * @package    CATS
 * @subpackage Library
 * @copyright 
 * @version    $Id: JobOrderTypes.php  2016-11-15 17:17:46Z Kixy25 $
 */

/**
 *	Job Order Types Library
 *	@package    CATS
 *	@subpackage Library
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

