<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Newsfeeds
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * Newsfeeds Component Newsfeeds Model
 *
 * @package		Joomla.Administrator
 * @subpackage	Newsfeeds
 * @since 1.5
 */
class NewsfeedsModelNewsfeeds extends JModel
{
	/**
	 * Category ata array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Category total
	 *
	 * @var integer
	 */
	var $_total = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	var $_pagination = null;

	/**
	 * Filter object
	 *
	 * @var object
	 */
	var $_filter = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();

		global $mainframe, $option;

		// Get the pagination request variables
		$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart	= $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		$filter = new stdClass();
		$filter->order		= $mainframe->getUserStateFromRequest( $option.'filter_order',		'filter_order',		'a.ordering',	'cmd' );
		$filter->order_Dir	= $mainframe->getUserStateFromRequest( $option.'filter_order_Dir',	'filter_order_Dir',	'',				'word' );
		$filter->state		= $mainframe->getUserStateFromRequest( $option.'filter_state',		'filter_state',		'',				'word' );
		$filter->catid		= $mainframe->getUserStateFromRequest( $option.'filter_catid',		'filter_catid',		0,				'int' );
		$filter->search		= $mainframe->getUserStateFromRequest( $option.'search',			'search',			'',				'string' );
		$this->_filter = $filter;
	}

	/**
	 * Method to get newsfeeds item data
	 *
	 * @access public
	 * @return array
	 */
	function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_data;
	}

	/**
	 * Method to get the total number of newsfeed items
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	/**
	 * Method to get a pagination object for the newsfeeds
	 *
	 * @access public
	 * @return integer
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_pagination;
	}

	/**
	 * Method to get filter object for the newsfeeds
	 *
	 * @access public
	 * @return object
	 */
	function getFilter()
	{
		return $this->_filter;
	}

	function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();

		$query = ' SELECT a.*, cc.title AS catname, u.name AS editor '
			. ' FROM #__newsfeeds AS a '
			. ' LEFT JOIN #__categories AS cc ON cc.id = a.catid '
			. ' LEFT JOIN #__users AS u ON u.id = a.checked_out '
			. $where
			. $orderby
		;

		return $query;
	}

	function _buildContentOrderBy()
	{
		global $mainframe, $option;

		if ($this->_filter->order == 'a.ordering'){
			$orderby 	= ' ORDER BY catname, a.ordering '.$this->_filter->order_Dir;
		} else {
			$orderby 	= ' ORDER BY '.$this->_filter->order.' '.$this->_filter->order_Dir.' , catname, a.ordering ';
		}
		return $orderby;
	}

	function _buildContentWhere()
	{
		global $mainframe, $option;

		$search = JString::strtolower( $this->_filter->search );

		$where = array();

		if ($this->_filter->catid > 0) {
			$where[] = 'a.catid = '.(int) $this->_filter->catid;
		}
		if ($search) {
			$where[] = 'LOWER(a.name) LIKE '.$this->_db->Quote('%'.$this->_db->getEscaped( $search, true ).'%', false);
		}
		if ( $this->_filter->state ) {
			if ( $this->_filter->state == 'P' ) {
				$where[] = 'a.published = 1';
			} else if ($this->_filter->state == 'U' ) {
				$where[] = 'a.published = 0';
			}
		}

		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );

		return $where;
	}
}