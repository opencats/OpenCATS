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
class TableAlbum extends JTable
{
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	var $id = null;
	var $artist = null;	 	 	
	var $name = null; 		 	 	
	var $image = null; 		 	 	
	var $dimensions_height = null; 				 	 	
	var $dimensions_width = null; 			 	 	
	var $artwork = null; 		 	 	
	var $comments = null; 		 	 	
	var $status = null; 		 	 	
	var $addDate = null; 	 	 	
	var $keywords = null; 		 	 	
	var $downloads = null; 	 	
	var $hits = null; 		 	 	
	var $rss_date = null; 		 	 	
	var $cat = null; 	
	var $parent = null; 	 	 	
	var $discount = null;
	var $upc = null; 	 	 	
	var $RM = null;
	var $label = null;

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function TableAlbum(& $db) {
		parent::__construct('#__m15_albums', 'id', $db);
	}
}