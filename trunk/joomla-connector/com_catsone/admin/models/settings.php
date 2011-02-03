<?php
/**
 * Hellos Model for Hello World Component
 *
 * @package    Joomla.Tutorials
 * @subpackage Components
 * @link http://docs.joomla.org/Developing_a_Model-View-Controller_Component_-_Part_4
 * @license        GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

/**
 * Hello Model
 *
 * @package    Joomla.Tutorials
 * @subpackage Components
 */
class catsModelSettings extends JModel
{
	/**
	 * Hellos data array
	 *
	 * @var array
	 */
	var $_data;

	/**
	 * Returns the query
	 * @return string The query to be used to retrieve the rows from the database
	 */
	function _buildQuery()
	{
		$query = ' SELECT * '
		. ' FROM #__catsonesettings limit 1';
		return $query;
	}

	function store()
	{
		$settingsTable =& $this->getTable();

		$data = JRequest::get('post');

		// Bind the form fields to the settings table
		if (!$settingsTable->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Make sure the settings record is valid
		if (!$settingsTable->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Store the web link table to the database
		if (!$settingsTable->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Retrieves the settings data
	 * @return array Array of objects containing the data from the database
	 */
	function getData()
	{
		// Lets load the data if it doesn't already exist
		if (empty( $this->_data ))
		{
			$query = $this->_buildQuery();
			$this->_db->setQuery( $query );

			//$this->_data = $this->_getList( $query );
			$this->_data = $this->_db->loadObject();

		}

		return $this->_data;
	}
}

?>