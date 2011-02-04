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
class catsoneModeldetails extends JModel
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
	function getDetails( $option )
	{

		//Query database
		$query = "Select joborder.*,joborder.city as scity,joborder.state as sstate,joborder.title as titlejob,extra_field.*,user.*,user.last_name,company.company_id,company.name from joborder,extra_field,user,company where extra_field.data_item_id = joborder.joborder_id and joborder.status = 'active' and joborder.entered_by = user.user_id  and joborder.company_id = company.company_id ";
		if($option['joborder_id']!="")
		{
			$query.=" and joborder.joborder_id like '".$option['joborder_id']."'";
		}
		$this->CatsDb->setQuery($query);
		//echo $db->getQuery();
		$result = $this->CatsDb->loadObjectList();
		$result = $result[0];
		return $result;
	}
}