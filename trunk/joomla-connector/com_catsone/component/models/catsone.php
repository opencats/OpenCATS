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

	var $CatsDb;

	function __construct()
	{
		parent::__construct();
		//pull settings from admin
		$db =& JFactory::getDBO();
		$db->setQuery("SELECT * From #__catonesettings limit 1");
		$SETTINGS = $db->loadObject();

		jimport('joomla.database.database');
		jimport( 'joomla.database.table' );
		$conf =& JFactory::getConfig();
		// TODO: Cache on the fingerprint of the arguments

		$driver 	= $conf->getValue('config.dbtype');

		$optionDb	= array ( 'driver' => $driver, 'host' => $SETTINGS->OC_Database_host, 'user' => $SETTINGS->OC_Database_Username, 'password' => $SETTINGS->OC_Database_password, 'database' => $SETTINGS->OC_Database_Name, 'prefix' => "" );
		$this->CatsDb =& JDatabase::getInstance( $optionDb );
	}
	/**
	 * Builds the query to select contact items
	 * @param array
	 * @return string
	 * @access protected
	 */
	function _getCatsoneQuery( $jobType )
	{
		$query = "Select joborder.*,extra_field.* from joborder,extra_field where field_name LIKE('Job Orders') AND extra_field.data_item_id = joborder.joborder_id";
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

		$query = "Select joborder.*,extra_field.*,user.user_id,user.last_name,company.company_id,company.name from joborder,extra_field,user,company where field_name LIKE('Job Orders') AND extra_field.data_item_id = joborder.joborder_id and joborder.status = 'active' and joborder.entered_by = user.user_id  and joborder.company_id = company.company_id and joborder.public = '1'";

		if($optionJobType['jobType']!="")
		{
			$query.=" and extra_field.value like '".$optionJobType['jobType']."'";
		}
		if($optionJobType['order']!="")
		{
			$query .= " order by joborder.".$optionJobType['order'];
		}
		$this->CatsDb->setQuery($query,@$options['limitstart'], @$options['limit']);
		$result = $this->CatsDb->loadObjectList();

		return $result;
	}
}
