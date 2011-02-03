<?php
/**
 * Hello World default controller
 *
 * @package    Joomla.Tutorials
 * @subpackage Components
 * @link http://docs.joomla.org/Developing_a_Model-View-Controller_Component_-_Part_4
 * @license		GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Hello World Component Controller
 *
 * @package    Joomla.Tutorials
 * @subpackage Components
 */
class CatsControllerSettings extends CatsControllerDefault
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		//$this->registerTask( 'save' );
	}

	/* save a record (and redirect to main page)
	 * @return void
	 */
	function save()
	{
		$model = $this->getModel('Settings');

		$host = JRequest::getVar( 'db_host');
		$dbname = JRequest::getVar( 'db_name');
		$username = JRequest::getVar( 'username');
		$password = JRequest::getVar( 'password');

		if ($model->store($post)) {

			$msg = JText::_( 'Settings Saved!' );

		}else{
			$msg = JText::_( 'There was an error saving the settings' );
		}

		$this->setRedirect('index.php?option=com_catsone&controller=settings&view=settings', $msg);

	}

	function apply()
	{
		$model = $this->getModel('Settings');

		$host = JRequest::getVar( 'db_host');
		$dbname = JRequest::getVar( 'db_name');
		$username = JRequest::getVar( 'username');
		$password = JRequest::getVar( 'password');

		if ($model->store($post)) {

			$msg = JText::_( 'Settings Saved!' );

		}else{
			$msg = JText::_( 'There was an error saving the settings' );
		}

		$this->setRedirect('index.php?option=com_catsone&controller=settings&view=settings', $msg);
	}
	
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function display()
	{
		parent::display();
	}

}