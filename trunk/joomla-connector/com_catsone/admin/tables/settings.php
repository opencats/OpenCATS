<?php
/**
 * Album table class
 * 
 * @package    Joomla.Tutorials
 * @subpackage Components
 * @link http://docs.joomla.org/Developing_a_Model-View-Controller_Component_-_Part_4
 * @license		GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Hello Table class
 *
 * @package    Joomla.Tutorials
 * @subpackage Components
 */
class TableSettings extends JTable
{
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	var $id = null;
	var $OC_Database_Name = null;	 	 	
	var $OC_Database_Username = null; 		 	 	
	var $OC_Database_host = null; 		 	 	
	var $OC_Database_password = null; 	
	var $Cats_install = null;
	var $Cats_local = null;
	var $ftp_host = null;
	var $ftp_user = null;
	var $ftp_password = null;
	var $ftp_path = null;
	var $email = null;
	var $attachment_path = null;
	var $enable_ftp = null;			 	 	

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function TableSettings(& $db) {
		parent::__construct('#__catonesettings', 'id', $db);
	}
}