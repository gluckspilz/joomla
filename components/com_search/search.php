<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Search
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_search'.DS.'helpers'.DS.'search.php' );

// First thing we want to do is set the page title
$mainframe->setPageTitle(JText::_('Search'));

/*
 * This is our main control structure for the component
 *
 * Each view is determined by the $task variable
 */
switch ( JRequest::getVar( 'task' ) )
{
	default:
		SearchController::display();
		break;
}

/**
 * Static class to hold controller functions for the Search component
 *
 * @static
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla
 * @subpackage	Search
 * @since		1.5
 */
class SearchController
{
	function display()
	{
		global $mainframe, $Itemid;

		// Initialize some variables
		$db 	=& JFactory::getDBO();
		$pathway =& $mainframe->getPathWay();

		$error = '';
		$rows  = null;
		$total = 0;

		// Get some request variables
		$searchword 	= JRequest::getVar( 'searchword' );
		$phrase 		= JRequest::getVar( 'searchphrase' );
		$searchphrase 	= JRequest::getVar( 'searchphrase', 'any' );
		$ordering 		= JRequest::getVar( 'ordering', 'newest' );
		$areas 			= JRequest::getVar( 'areas' );
		$limit			= JRequest::getVar( 'limit', $mainframe->getCfg( 'list_limit' ), 'get', 'int' );
		$limitstart 	= JRequest::getVar( 'limitstart', 0, 'get', 'int' );


		// Set the component name in the pathway
		$pathway->setItemName(1, JText::_( 'Search' ) );

		// Get the paramaters of the active menu item
		$menu    =& JSiteHelper::getCurrentMenuItem();
		$params  =& JSiteHelper::getMenuParams();
		$params->def( 'page_title', 1 );
		$params->def( 'pageclass_sfx', '' );
		$params->def( 'header', $menu->name, JText::_( 'Search' ) );

		// built select lists
		$orders = array();
		$orders[] = JHTML::makeOption( 'newest', JText::_( 'Newest first' ) );
		$orders[] = JHTML::makeOption( 'oldest', JText::_( 'Oldest first' ) );
		$orders[] = JHTML::makeOption( 'popular', JText::_( 'Most popular' ) );
		$orders[] = JHTML::makeOption( 'alpha', JText::_( 'Alphabetical' ) );
		$orders[] = JHTML::makeOption( 'category', JText::_( 'Section/Category' ) );
		$ordering = JRequest::getVar( 'ordering', 'newest');
		$lists = array();
		$lists['ordering'] = JHTML::selectList( $orders, 'ordering', 'class="inputbox"', 'value', 'text', $ordering );

		$searchphrases 		= array();
		$searchphrases[] 	= JHTML::makeOption( 'any', JText::_( 'Any words' ) );
		$searchphrases[] 	= JHTML::makeOption( 'all', JText::_( 'All words' ) );
		$searchphrases[] 	= JHTML::makeOption( 'exact', JText::_( 'Exact phrase' ) );
		$lists['searchphrase' ]= JHTML::radioList( $searchphrases, 'searchphrase', '', $searchphrase );

		JPluginHelper::importPlugin( 'search' );
		$lists['areas'] = $mainframe->triggerEvent( 'onSearchAreas' );

		// log the search
		SearchHelper::logSearch( $searchword );

		//limit searchword
		if(SearchHelper::limitSearchWord($searchword)) {
			$error = JText::_( 'SEARCH_MESSAGE' );
		}

		//sanatise searchword
		if(SearchHelper::santiseSearchWord($searchword)) {
			$error = JText::_( 'IGNOREKEYWORD' );
		}

		if (!$searchword && count( JRequest::get('post') ) ) {
			//$error = JText::_( 'Enter a search keyword' );
		}

		if(!$error) {
			$rows  = SearchController::getResults($searchword, $phrase, $ordering, $areas);
			$total = count($rows);
			$rows  = array_splice($rows, $limitstart, $limit);
		}

		require_once (JPATH_COMPONENT.DS.'views'.DS.'search'.DS.'view.php');
		$view = new SearchViewSearch();

		$view->assign('limit'       , $limit);
		$view->assign('limitstart'  , $limitstart);
		$view->assign('ordering'    , $ordering);
		$view->assign('searchword'  , $searchword);
		$view->assign('searchphrase', $searchphrase);
		
		$view->assign('total', $total);
		$view->assign('error', $error);
		
		$view->assignRef('results' , $rows);
		$view->assignRef('lists'   , $lists);
		$view->assignRef('params'  , $params);
		$view->assignRef('request' , $request);
		$view->assignRef('data'    , $data);
		
		$view->display();
	}

	function getResults($searchword, $phrase, $ordering, $areas)
	{
		global $mainframe;

		$results 	= $mainframe->triggerEvent( 'onSearch', array( $searchword, $phrase, $ordering, $areas ) );

		$rows = array();
		for ($i = 0, $n = count( $results); $i < $n; $i++) {
			$rows = array_merge( (array)$rows, (array)$results[$i] );
		}

		require_once (JPATH_SITE . '/components/com_content/helpers/content.php');
		$total = count( $rows );

		for ($i=0; $i < $total; $i++)
		{
			$row = &$rows[$i]->text;
			if ($phrase == 'exact') {
				$searchwords = array($searchword);
				$needle = $searchword;
			} else {
				$searchwords = explode(' ', $searchword);
				$needle = $searchwords[0];
			}

			$row = SearchHelper::prepareSearchContent( $row, 200, $needle );

		  	foreach ($searchwords as $hlword) {
				$hlword = htmlspecialchars( stripslashes( $hlword ) );
				$row = eregi_replace( $hlword, '<span class="highlight">\0</span>', $row );
			}

			if ( strpos( $rows[$i]->href, 'http' ) == false )
			{
				$url = parse_url( $rows[$i]->href );
				if( !empty( $url['query'] ) ) {
					$link = null;
					parse_str( $url['query'], $link );
				} else {
					$link = '';
				}

				// determines Itemid for articles where itemid has not been included
				if ( !empty($link) && @$link['task'] == 'view' && isset($link['id']) && !isset($link['Itemid']) ) {
					$itemid = '';
					if (JContentHelper::getItemid( $link['id'] )) {
						$itemid = '&amp;Itemid='. JContentHelper::getItemid( $link['id'] );
					}
					$rows[$i]->href = $rows[$i]->href . $itemid;
				}
			}
		}

		return $rows;
	}
}
?>