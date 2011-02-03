<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$mainframe->registerEvent( 'onSearch', 'plgSearchCatsone' );
$mainframe->registerEvent( 'onSearchAreas', 'plgSearchCatsoneAreas' );

JPlugin::loadLanguage( 'plg_search_weblinks' );

/**
 * @return array An array of search areas
 */
function &plgSearchCatsoneAreas() {
	static $areas = array(
		'catsone' => 'Catsone'
	);
	return $areas;
}

function plgSearchCatsone( $text, $phrase='', $ordering='', $areas=null )
{
	//$db		=& JFactory::getDBO();
	jimport('joomla.database.database');
	jimport( 'joomla.database.table' );
	$conf =& JFactory::getConfig();
	// TODO: Cache on the fingerprint of the arguments
	//$db			=& JFactory::getDBO();
	$host 		= $conf->getValue('config.host');
	$user 		= $conf->getValue('config.user');
	$password 	= $conf->getValue('config.password');
	$database	= $conf->getValue('config.db');
	$prefix 	= $conf->getValue('config.dbprefix');
	$driver 	= $conf->getValue('config.dbtype');
	$debug 		= $conf->getValue('config.debug');

	$optionDb	= array ( 'driver' => $driver, 'host' => $host, 'user' => $user, 'password' => "password, 'database' => $database, 'prefix' => $prefix );
	$db =& JDatabase::getInstance( $optionDb );
	$user	=& JFactory::getUser();
	//require_once(JPATH_SITE.DS.'components'.DS.'com_weblinks'.DS.'helpers'.DS.'route.php');
	if (is_array( $areas )) {
		if (!array_intersect( $areas, array_keys( plgSearchCatsoneAreas() ) )) {
			return array();
		}
	}

	// load plugin params info
 	$plugin =& JPluginHelper::getPlugin('search', 'catsone');
 	$pluginParams = new JParameter( $plugin->params );

	$limit = $pluginParams->def( 'search_limit', 50 );

	$text = trim( $text );
	if ($text == '') {
		return array();
	}
	$section 	= JText::_( 'Catsone' );

	$wheres 	= array();
	switch ($phrase)
	{
		case 'exact':
			$text		= $db->Quote( '%'.$db->getEscaped( $text, true ).'%', false );
			$wheres2 	= array();
			$wheres2[] 	= 'LOWER(a.description) LIKE '.$text;
			$wheres2[] 	= 'LOWER(a.title) LIKE '.$text;
			$where 		= '(' . implode( ') OR (', $wheres2 ) . ')';
			break;

		case 'all':
		case 'any':
		default:
			$words 	= explode( ' ', $text );
			$wheres = array();
			foreach ($words as $word)
			{
				$word		= $db->Quote( '%'.$db->getEscaped( $word, true ).'%', false );
				$wheres2 	= array();
				$wheres2[] 	= 'LOWER(a.description) LIKE '.$word;
				$wheres2[] 	= 'LOWER(a.title) LIKE '.$word;
				$wheres[] 	= implode( ' OR ', $wheres2 );
			}
			$where 	= '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheres ) . ')';
			break;
	}

	switch ( $ordering )
	{
		case 'oldest':
			$order = 'a.joborder_id ASC';
			break;

		case 'popular':
			$order = 'a.joborder_id DESC';
			break;

		case 'alpha':
			$order = 'a.title ASC';
			break;


		case 'newest':
		default:
			$order = 'a.joborder_id DESC';
	}

	$query = 'SELECT a.joborder_id as id, a.title AS title, a.description AS text, a.date_created AS date_created '
	. ' FROM joborder AS a'
	. ' WHERE ('. $where .')'
	. ' AND a.public = 1'
	. ' ORDER BY '. $order
	;

	$db->setQuery( $query, 0, $limit );

	$rows = $db->loadObjectList();
	foreach($rows as $key => $row) {
		//$rows[$key]->href = CatsoneHelperRoute::getCatsoneRoute($row->slug, $row->catslug);
		$rows[$key]->href = "index.php?option=com_catsone&task=details&id=".$row->id;
	}

	return $rows;
}