<?php

/**
 * @package		Joomla
 * @subpackage	com_install
 * @copyright	Copyright (C) Vamba & Matthew Thomson. All rights reserved.
 * @license		Creative Commons.
 * @author 		Arelowo Alao (aretimes.com) & David Bennet (maianscriptworld.co.uk)
 * @based on  	com_ignitegallery
 * @author 		Matthew Thomson (ignitejoomlaextensions.com)
 * Joomla! and Maian Music are free software. You must attribute the work in the manner 
 * specified by the author or licensor (but not in any way that suggests that they endorse you or your use of the work).
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

function com_install() {

	$uri =& JURI::getInstance();

	echo '<div id="maian_content"><img src="'.$uri->root().'/administrator/components/com_catsone/page-Default.gif"></img><br>';

	

}
?>