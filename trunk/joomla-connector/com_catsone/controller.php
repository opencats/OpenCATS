<?php
/**
 * @version		$Id: controller.php 10094 2008-03-02 04:35:10Z instance $
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

jimport( 'joomla.application.component.controller' );

/**
 * Contact Component Controller
 *
 * @static
 * @package		Joomla
 * @subpackage	Contact
 * @since 1.5
 */
class CatsoneController extends JController
{
	/**
	 * Display the view
	 */
	function display()
	{
		$document =& JFactory::getDocument();
		$viewName	= JRequest::getVar('task', 'catsone', 'default', 'cmd');
		$viewType	= $document->getType();
	
		

		// Set the default view name from the Request
		
		$view = &$this->getView($viewName, $viewType);

		// Push a model into the view
		
		$model	= &$this->getModel( $viewName );
		if (!JError::isError( $model )) {
			$view->setModel( $model, true );
		}

		// Display the view
		$view->assign('error', $this->getError());
		$subpage = JRequest::getVar('subpage');
		if($subpage=="save")
		{
			$view->displaySubpage();
		}
		elseif($subpage=="saveAnswer")
		{
			$view->saveAnswer();
		}
		elseif($subpage=="search")
		{
			$view->search();
		}
		else
		{
			$view->display();		
		}
	}
}