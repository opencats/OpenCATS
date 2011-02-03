<?php
/**
* @version		$Id: helper.php 10079 2008-02-28 13:39:08Z ircmaxell $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

class modCatsone
{
	function getList(&$params){
		global $mainframe;

		jimport('joomla.database.database');
		jimport( 'joomla.database.table' );
		$conf =& JFactory::getConfig();

		$host 		= $conf->getValue('config.host');
		$driver 	= $conf->getValue('config.dbtype');
		$options	= array ( 'driver' => $driver, 'host' => $params->get('dbhost'), 'user' => $params->get('dbuser'), 'password' => $params->get('dbpass'), 'database' => $params->get('dbname'), 'prefix' => "" );
		$db =& JDatabase::getInstance( $options );
		$query = "SELECT value, count(value) as count FROM extra_field,joborder,user,company WHERE field_name LIKE('Job Orders') AND extra_field.data_item_id = joborder.joborder_id AND joborder.status = 'active' AND joborder.entered_by = user.user_id  AND joborder.company_id = company.company_id AND joborder.public = '1' GROUP BY value;";

		$db->setQuery($query);
		$lists = $db->loadAssocList();
		return $lists;
	}
	function getAllAmount(&$params){
		global $mainframe;

		jimport('joomla.database.database');
		jimport( 'joomla.database.table' );
		$conf =& JFactory::getConfig();

		$host 		= $conf->getValue('config.host');
		$driver 	= $conf->getValue('config.dbtype');
		$options	= array ( 'driver' => $driver, 'host' => $params->get('dbhost'), 'user' => $params->get('dbuser'), 'password' => $params->get('dbpass'), 'database' => $params->get('dbname'), 'prefix' => "" );
		$db =& JDatabase::getInstance( $options );
		$query = "SELECT count(joborder.joborder_id) AS count FROM joborder,extra_field,user,company WHERE field_name LIKE('Job Orders') AND  extra_field.data_item_id = joborder.joborder_id AND joborder.status = 'active' AND joborder.entered_by = user.user_id  AND joborder.company_id = company.company_id AND joborder.public = '1'";

		$db->setQuery($query);
		$lists = $db->loadResult();
		return $lists;
	}
}
