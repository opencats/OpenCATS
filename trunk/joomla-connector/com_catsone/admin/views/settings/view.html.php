<?php
/**
 * Hello View for Hello World Component
 * 
 * @package    Joomla.Tutorials
 * @subpackage Components
 * @link http://docs.joomla.org/Developing_a_Model-View-Controller_Component_-_Part_4
 * @license		GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

/**
 * Settings View
 *
 * @package    Joomla.Tutorials
 * @subpackage Components
 */
class CatsViewSettings extends JView
{
	/**
	 * display method of settings view
	 * @return void
	 **/
	function display($tpl = null)
	{
		//get the data
		$settings =& $this->get('Data');
		
		JToolBarHelper::title('OpenCats - Settings', 'cpanel.png' );
		JToolBarHelper::save();
		JToolBarHelper::apply();
		
		// for existing items the button is renamed `close`
		JToolBarHelper::cancel( 'cancel', 'Close' );

		$this->assignRef('settings', $settings);

		parent::display($tpl);
	}
}