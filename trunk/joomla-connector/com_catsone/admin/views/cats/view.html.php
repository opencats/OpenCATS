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
class CatsViewCats extends JView
{
	/**
	 * display method of settings view
	 * @return void
	 **/
	function display($tpl = null)
	{
		
		JToolBarHelper::title(   JText::_('OpenCats - About'), 'generic.png' );
		

		parent::display($tpl);
	}
}