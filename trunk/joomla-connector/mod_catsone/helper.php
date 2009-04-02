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
	function getList()
	{
		global $mainframe;
		//$user		=& JFactory::getUser();
		jimport('joomla.database.database');
		jimport( 'joomla.database.table' );
		$conf =& JFactory::getConfig();
		// TODO: Cache on the fingerprint of the arguments
		//$db			=& JFactory::getDBO();
		/*
		$host 		= $conf->getValue('config.host');
		$user 		= $conf->getValue('config.user');
		$password 	= $conf->getValue('config.password');
		$database	= $conf->getValue('config.db');
		$prefix 	= $conf->getValue('config.dbprefix');
		$driver 	= $conf->getValue('config.dbtype');
		$debug 		= $conf->getValue('config.debug');
		$options	= array ( 'driver' => $driver, 'host' => $host, 'user' => $user, 'password' => $password, 'database' => 'chasejob_cats', 'prefix' => "" );
		*/
		$host 		= $conf->getValue('config.host');
		$driver 	= $conf->getValue('config.dbtype');
	$options	= array ( 'driver' => $driver, 'host' => 'yourcatsservername', 'user' => 'yourcatsdbusername', 'password' => '$yourcatsdbpassword', 'database' => 'yourcatsdbname', 'prefix' => "" );
		$db =& JDatabase::getInstance( $options );
		$query = "Select joborder.*,extra_field.*,user.user_id,user.last_name,company.company_id,company.name from joborder,extra_field,user,company where extra_field.data_item_id = joborder.joborder_id and joborder.status = 'active' and joborder.entered_by = user.user_id  and joborder.company_id = company.company_id ";
		//echo $query;
		$db->setQuery($query);
		$lists = $db->loadObjectList();
		return $lists;
	}
}
