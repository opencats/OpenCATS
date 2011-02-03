<?php
/**
 * @package    Maian Music
 * @subpackage Components
 * @link http://www.aretimes.com
 * @license    GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Load Base Contorller Class

require_once( JPATH_COMPONENT.DS.'controllers'.DS.'defaultController.php' );
//set Globals

// Require specific controller if requested
if($controller = JRequest::getWord('controller')) {

	$path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'Controller.php';

	if (file_exists($path)) {
		require_once $path;
	}else {
		$controller = 'default';
	}

	$classname    = 'CatsController'.$controller;
	$controller   = new $classname( );

}else{
	$controller   = new CatsControllerDefault();
}
// Create the controller

// Perform the Request task
$controller->execute( JRequest::getVar( 'task' ) );

// Redirect if set by the controller
$controller->redirect();
?>