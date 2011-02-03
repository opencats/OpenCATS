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
class CatsControllerDefault extends JController
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

	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function display()
	{
		// loading view for this task
		parent::display();
	}

/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function about()
	{
		// loading view for this task
		//JRequest::setVar( 'view', 'about' );
		parent::display();
	}
	
}//end MaianControllerDefault