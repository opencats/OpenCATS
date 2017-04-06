<?php

/**
 * Job Order Statuses Library
 * @package OpenCATS
 * @subpackage Library
 * @copyright (C) OpenCats
 */

class JobOrderStatuses
{
    private static $_defaultStatuses = array(
        'Open' => array ('Active', 'On Hold', 'Full'),
        'Closed' => array('Closed', 'Canceled'),
        'Other' => array('Upcoming', 'Prospective / Lead')
    );
    private static $_defaultFilters = array(
        'Active / On Hold / Full',
        'Active',
        'On Hold / Full',
        'Closed / Canceled',
        'Upcoming / Lead'
    );
    private static $_defaultSharingStatuses = array('Active');
    private static $_defaultStatisticsStatuses = array('Active', 'OnHold', 'Full', 'Closed');
    private static $_defaultNewStatus = 'Active';

    /**
     * Returns job order statuses from config or default
     *
     * @return array job order statuses from config or if undefined, then default
     */
    public static function getAll()
    {
        if(defined('JOB_ORDER_STATUS_LIST'))
        {
            return JOB_ORDER_STATUS_LIST;
        }
        else
        {
            return self::$_defaultStatuses;
        }
    }
    /**
     * Returns job order searches from config or default
     *
     * @return array job order searches from config or if undefined, then default
     */
    public static function getFilters(){
        if(defined('JOB_ORDER_STATUS_FILTERING'))
        {
            return JOB_ORDER_STATUS_FILTERING;
        }
        else
        {
            return self::$_defaultFilters;
        }
    }

    /**
     * Returns job order statuses for sharing (XML, RSS, Career portal) in a format for MySQL IN() query
     */
    public static function getShareStatusSQL(){
        $result = "";
        if(!defined( 'JOB_ORDER_STATUS_SHARING')){
            $array = self::$_defaultSharingStatuses;
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
    public static function getStatisticsStatusSQL(){
        $result = "";
        if(!defined( 'JOB_ORDER_STATUS_STATISTICS')){
            $array = self::$_defaultStatisticsStatuses;
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
    public static function getOpenStatusSQL(){
        $result = "";
        $array = self::getAll()['Open'];
        foreach($array as $status){
            $result .= "'".$status."',";
        }
        if(strlen($result) > 0){
            $result = substr($result, 0, strlen($result) - 1);
            $result = "(".$result.")";
        }
        return $result;
    }

    public static function getDefaultNewStatus(){
        if(defined('JOB_ORDER_NEW_STATUS')){
            return JOB_ORDER_NEW_STATUS;
        } else {
            return self::$_defaultNewStatus;
        }
    }
}

