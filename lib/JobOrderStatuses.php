<?php

/**
 * Job Order Statuses Library
 * @package OpenCATS
 * @subpackage Library
 * @copyright (C) OpenCats
 */

class JobOrderStatuses
{
    private $_defaultStatuses;
    private $_defaultFilters;
    private $_defaultSharingStatuses;
    private $_defaultStatisticsStatuses;

    public function __construct() {
        $this->_defaultStatuses = array(
            'Open' => array ('Active', 'On Hold', 'Full'),
            'Closed' => array('Closed', 'Canceled'),
            'Other' => array('Upcoming', 'Prospective / Lead')
        );
        $this->_defaultFilters = array(
            'Active / On Hold / Full',
            'Active',
            'On Hold / Full',
            'Closed / Canceled',
            'Upcoming / Lead'
        );
        $this->_defaultSharingStatuses = array('Active');
        $this->_defaultStatisticsStatuses = array('Active', 'OnHold', 'Full', 'Closed');
    }

    /**
     * Returns job order statuses from config or default
     *
     * @return array job order statuses from config or if undefined, then default
     */
    public function getAll()
    {
        if(defined('JOB_ORDER_STATUS_LIST'))
        {
            return JOB_ORDER_STATUS_LIST;
        }
        else
        {
            return $this->_defaultStatuses;
        }
    }
    /**
     * Returns job order searches from config or default
     *
     * @return array job order searches from config or if undefined, then default
     */
    public function getFilters(){
        if(defined('JOB_ORDER_STATUS_FILTERING'))
        {
            return JOB_ORDER_STATUS_FILTERING;
        }
        else
        {
            return $this->_defaultFilters;
        }
    }

    /**
     * Returns job order statuses for sharing (XML, RSS, Career portal) in a format for MySQL IN() query
     */
    public function getShareStatusSQL(){
        $result = "";
        if(!defined( 'JOB_ORDER_STATUS_SHARING')){
            $array = $this->_defaultSharingStatuses;
        } else {
            $array = JOB_ORDER_STATUS_SHARING;
        }
        foreach($array as $status){
            $result .= "'".$status."',";
        }
        if(strlen($result) > 0){
            $result = substr($result, 0, strlen($result) - 1);
            $result = "(".$result.")";
        }
        return $result;
    }

    /**
     * Returns job order statuses for statistics (submission/placement) in a format for MySQL IN() query
     */
    public function getStatisticsStatusSQL(){
        $result = "";
        if(!defined( 'JOB_ORDER_STATUS_STATISTICS')){
            $array = $this->_defaultStatisticsStatuses;
        } else {
            $array = JOB_ORDER_STATUS_STATISTICS;
        }
        foreach($array as $status){
            $result .= "'".$status."',";
        }
        if(strlen($result) > 0){
            $result = substr($result, 0, strlen($result) - 1);
            $result = "(".$result.")";
        }
        return $result;
    }

    /**
     * Returns job order statuses for important candidates on home page in a format for MySQL IN() query
     */
    public function getOpenStatusSQL(){
        $result = "";
        $array = $this->getAll()['Open'];
        foreach($array as $status){
            $result .= "'".$status."',";
        }
        if(strlen($result) > 0){
            $result = substr($result, 0, strlen($result) - 1);
            $result = "(".$result.")";
        }
        return $result;
    }
}

