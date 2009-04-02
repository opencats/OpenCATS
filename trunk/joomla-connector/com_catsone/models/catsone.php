<?php
/**
 * @version		$Id: contact.php 10094 2008-03-02 04:35:10Z instance $
 * @package		Joomla
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

/**
 * @package		Joomla
 * @subpackage	Contact
 */
class catsoneModelCatsone extends JModel
{
	/**
	 * Builds the query to select contact items
	 * @param array
	 * @return string
	 * @access protected
	 */
	function _getCatsoneQuery( $jobType )
	{
		jimport('joomla.database.database');
		jimport( 'joomla.database.table' );
		$conf =& JFactory::getConfig();
		// TODO: Cache on the fingerprint of the arguments
		//$db			=& JFactory::getDBO();
		/*
		$user 		= $conf->getValue('config.user');
		$password 	= $conf->getValue('config.password');
		$database	= $conf->getValue('config.db');
		$prefix 	= $conf->getValue('config.dbprefix');
		
		$debug 		= $conf->getValue('config.debug');
		*/
		$driver 	= $conf->getValue('config.dbtype');
		$host 		= $conf->getValue('config.host');
		//$options	= array ( 'driver' => $driver, 'host' => $host, 'user' => $user, 'password' => $password, 'database' => 'chasejob_cats', 'prefix' => "" );
	$options	= array ( 'driver' => $driver, 'host' => 'yourcatsservername', 'user' => 'yourcatsdbusername', 'password' => '$yourcatsdbpassword', 'database' => 'yourcatsdbname', 'prefix' => "" );
		$db =& JDatabase::getInstance( $options );
		$query = "Select joborder.*,extra_field.* from joborder,extra_field where extra_field.data_item_id = joborder.joborder_id";
		if($jobType)
		{
			$query.=" and extra_field.value like '$jobType'";
		}
		return $query;
	}
	/**
	 * Gets a list of categories
	 * @param array
	 * @return mixed Object or null
	 */
	function getCatsone( $optionJobType )
	{
		jimport('joomla.database.database');
		jimport( 'joomla.database.table' );
		$conf =& JFactory::getConfig();
		// TODO: Cache on the fingerprint of the arguments
		//$db			=& JFactory::getDBO();
		/*
		$user 		= $conf->getValue('config.user');
		$password 	= $conf->getValue('config.password');
		$database	= $conf->getValue('config.db');
		$prefix 	= $conf->getValue('config.dbprefix');
		
		$debug 		= $conf->getValue('config.debug');
		*/
		$host 		= $conf->getValue('config.host');
		$driver 	= $conf->getValue('config.dbtype');
		//$link = mysql_connect('yourcatsservername', 'yourcatsdbusername', '$yourcatsdbpassword');
//				$link2 = mysql_select_db('yourcatsdbname', $link);
 		$options	= array ( 'driver' => $driver, 'host' => 'yourcatsservername', 'user' => 'yourcatsdbusername', 'password' => '$yourcatsdbpassword', 'database' => 'yourcatsdbname', 'prefix' => "" );
		//$options	= array ( 'driver' => $driver, 'host' => $host, 'user' => $user, 'password' => $password, 'database' => 'chasejob_cats', 'prefix' => "" );
		$db =& JDatabase::getInstance( $options );
		$query = "Select joborder.*,extra_field.*,user.user_id,user.last_name,company.company_id,company.name from joborder,extra_field,user,company where extra_field.data_item_id = joborder.joborder_id and joborder.status = 'active' and joborder.entered_by = user.user_id  and joborder.company_id = company.company_id and joborder.public = '1'";
		
		if($optionJobType['jobType']!="")
		{
			$query.=" and extra_field.value like '".$optionJobType['jobType']."'";
		}
		if($optionJobType['order']!="")
		{
			$query .= " order by joborder.".$optionJobType['order'];
		}
//$result = mysql_query($query,@$options['limitstart'], @$options['limit']);
	 $db->setQuery($query,@$options['limitstart'], @$options['limit']);
		
 	$result = $db->loadObjectList();
	
		return $result;
	}
}