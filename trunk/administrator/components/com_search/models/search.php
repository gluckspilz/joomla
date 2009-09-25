<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Search
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * @package		Joomla.Administrator
 * @subpackage	Search
 */
class SearchModelSearch extends JModel
{

	var $lists = '';

	/**
	 * Overridden constructor
	 * @access	protected
	 */
	function __construct()
	{
		parent::__construct();
	}

	function reset()
	{
		$db = &JFactory::getDbo();
		$db->setQuery('DELETE FROM #__core_log_searches');
		$db->query();
	}

	function getItems()
	{
		$app	= &JFactory::getApplication();
		$db		= &JFactory::getDbo();

		$filter_order		= $app->getUserStateFromRequest('com_search.filter_order',		'filter_order',		'hits', 'cmd');
		$filter_order_Dir	= $app->getUserStateFromRequest('com_search.filter_order_Dir',	'filter_order_Dir',	'',		'word');
		$limit				= $app->getUserStateFromRequest('global.list.limit',				'limit',			$app->getCfg('list_limit'), 'int');
		$limitstart			= $app->getUserStateFromRequest('com_search.limitstart',			'limitstart',		0,		'int');
		$search				= $app->getUserStateFromRequest('com_search.search',				'search',			'',		'string');
		$search				= JString::strtolower($search);
		$showResults		= JRequest::getInt('search_results');

		// table ordering
		if ($filter_order_Dir == 'ASC') {
			$this->lists['order_Dir'] = 'ASC';
		} else {
			$this->lists['order_Dir'] = 'DESC';
		}
		$this->lists['order'] = $filter_order;

		// search filter
		$this->lists['search']= $search;

		$where = array();
		if ($search) {
			$where[] = 'LOWER(search_term) LIKE '.$db->Quote('%'.$db->getEscaped($search, true).'%', false);
		}

		$where 		= (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');
		$orderby 	= ' ORDER BY '. $filter_order .' '. $filter_order_Dir .', hits DESC';

		// get the total number of records
		$query = 'SELECT COUNT(*)'
		. ' FROM #__core_log_searches'
		. $where;
		$db->setQuery($query);
		$total = $db->loadResult();

		jimport('joomla.html.pagination');
		$pageNav = new JPagination($total, $limitstart, $limit);

		$query = ' SELECT * '
		. ' FROM #__core_log_searches '
		. $where
		. $orderby;
		$db->setQuery($query, $pageNav->limitstart, $pageNav->limit);

		$rows = $db->loadObjectList();

		JPluginHelper::importPlugin('search');

		if (!class_exists('JSite'))
		{
			// This fools the routers in the search plugins into thinking it's in the frontend
			require_once(JPATH_COMPONENT.DS.'helpers'.DS.'site.php');
		}

		for ($i=0, $n = count($rows); $i < $n; $i++) {
			// determine if number of results for search item should be calculated
			// by default it is `off` as it is highly query intensive
			if ($showResults) {
				$results = $app->triggerEvent('onSearch', array($rows[$i]->search_term));

				$count = 0;
				for ($j = 0, $n2 = count($results); $j < $n2; $j++) {
					$count += count($results[$j]);
				}

				$rows[$i]->returns = $count;
			} else {
				$rows[$i]->returns = null;
			}
		}

		return $rows;
	}
}