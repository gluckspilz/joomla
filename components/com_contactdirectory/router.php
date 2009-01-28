<?php
/**
 * @version		$Id: router.php 7380 2007-05-06 21:26:03Z eddieajau $
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

function ContactdirectoryBuildRoute(&$query)
{
	static $items;
	$segments = array();
	$itemid	= null;

	// Break up the contact id into numeric and alias values.
	if (isset($query['id']) && strpos($query['id'], ':')) {
		list($query['id'], $query['alias']) = explode(':', $query['id'], 2);
	}

	// Break up the category id into numeric and alias values.
	if (isset($query['catid']) && strpos($query['catid'], ':')) {
		list($query['catid'], $query['catalias']) = explode(':', $query['catid'], 2);
	}

	// Get the menu items for this component.
	if (!$items) {
		$component	= &JComponentHelper::getComponent('com_contactdirectory');
		$menu		= &JSite::getMenu();
		$items		= $menu->getItems('componentid', $component->id);
	}

	// Search for an appropriate menu item.
	if (is_array($items))
	{
		// If only the option and itemid are specified in the query, return that item.
		if (!isset($query['view']) && !isset($query['id']) && !isset($query['catid']) && isset($query['Itemid'])) {
			$itemid = (int) $query['Itemid'];
		}

		// Search for a specific link based on the critera given.
		if (!$itemid)
		{
			foreach ($items as $item)
			{
				// Check if this menu item links to this view.
				if (isset($item->query['view']) && $item->query['view'] == 'contact'
					&& isset($query['view']) && $query['view'] == 'contact'
					&& isset($item->query['id']) && $item->query['id'] == $query['id'])
				{
					$itemid	= $item->id;
				}
				elseif (isset($item->query['view']) && $item->query['view'] == 'category'
					&& isset($query['view']) && $query['view'] == 'category'
					&& isset($item->query['id']) && $item->query['id'] == $query['catid'])
				{
					$itemid	= $item->id;
				}
			}
		}

		// If no specific link has been found, search for a general one.
		if (!$itemid)
		{
			foreach ($items as $item)
			{
				//echo $item->query['view'].'<br/>';
				if (isset($query['view']) && $query['view'] == 'contact'
					&& isset($item->query['view']) && $item->query['view'] == 'category'
					&& isset($item->query['id']) && isset($query['catid'])
					&& $query['catid'] == $item->query['id'])
				{
					// This menu item links to the contact view but we need to append the contact id to it.
					$itemid	= $item->id;
					$segments[]	= isset($query['catalias']) ? $query['catid'].':'.$query['catalias'] : $query['catid'];
					$segments[]	= isset($query['alias']) ? $query['id'].':'.$query['alias'] : $query['id'];
					break;
				}
				elseif (isset($query['view']) && $query['view'] == 'category'
					&& isset($item->query['view']) && $item->query['view'] == 'category'
					&& isset($item->query['id']) && isset($query['id']) && $item->query['id'] != $query['id'])
				{
					// This menu item links to the category view but we need to append the category id to it.
					$itemid	= $item->id;
					$segments[]	= isset($query['alias']) ? $query['id'].':'.$query['alias'] : $query['id'];
					break;
				}
			}
		}

		// Search for an even more general link.
		if (!$itemid)
		{
			foreach ($items as $item)
			{
				if (isset($query['view']) && $query['view'] == 'contact'
					&& isset($item->query['view']) && $item->query['view'] == 'categories'
					&& isset($query['catid']) && isset($query['id']))
				{
					// This menu item links to the categories view but we need to append the category and contact id to it.
					$itemid	= $item->id;
					$segments[]	= isset($query['catalias']) ? $query['catid'].':'.$query['catalias'] : $query['catid'];
					$segments[]	= isset($query['alias']) ? $query['id'].':'.$query['alias'] : $query['id'];
					break;
				}
				elseif (isset($query['view']) && $query['view'] == 'category'
					&& isset($item->query['view']) && $item->query['view'] == 'categories'
					&& isset($query['catid']))
				{
					// This menu item links to the categories view but we need to append the category id to it.
					$itemid	= $item->id;
					$segments[]	= isset($query['catalias']) ? $query['catid'].':'.$query['catalias'] : $query['catid'];
					break;
				}
			}
		}
	}

	// Check if the router found an appropriate itemid.
	if (!$itemid)
	{
		// Check if a category was specified
		if (isset($query['view']) && $query['view'] == 'category' && isset($query['catid']))
		{
			if (isset($query['catalias'])) {
				$query['catid'] .= ':'.$query['catalias'];
			}

			// Push the catid onto the stack.
			$segments[] = $query['catid'];

			unset($query['view']);
			unset($query['catid']);
			unset($query['catalias']);
		}
		// Check if a id was specified.
		elseif (isset($query['id']))
		{
			if (isset($query['catalias'])) {
				$query['catid'] .= ':'.$query['catalias'];
			}

			// Push the catid onto the stack.
			$segments[] = $query['catid'];

			if (isset($query['alias'])) {
				$query['id'] .= ':'.$query['alias'];
			}

			// Push the id onto the stack.
			$segments[] = $query['id'];
			unset($query['view']);
			unset($query['id']);
			unset($query['alias']);
			unset($query['catid']);
			unset($query['catalias']);
		}
		elseif (isset($query['catid']))
		{
			if (isset($query['alias'])) {
				$query['catid'] .= ':'.$query['catalias'];
			}

			// Push the catid onto the stack.
			$segments[]	= 'category';
			$segments[] = $query['catid'];
			unset($query['view']);
			unset($query['catid']);
			unset($query['catalias']);
			unset($query['alias']);
		}else{
			// Categories view.
			unset($query['view']);
		}
	}else	{
		$query['Itemid'] = $itemid;

		// Remove the unnecessary URL segments.
		unset($query['view']);
		unset($query['id']);
		unset($query['alias']);
		unset($query['catid']);
		unset($query['catalias']);
	}
	return $segments;
}

function ContactdirectoryParseRoute($segments)
{
	$vars	= array();

	// Get the active menu item.
	$menu	= &JSite::getMenu();
	$item	= &$menu->getActive();

	// Check if we have a valid menu item.
	if (is_object($item))
	{
		// Proceed through the possible variations trying to match the most specific one.
		if (isset($item->query['view']) && $item->query['view'] == 'contact' && isset($segments[0]))
		{
			// Break up the contact id into numeric and alias values.
			if (isset($segments[0]) && strpos($segments[0], ':')) {
				list($id, $alias) = explode(':', $segments[0], 2);
			}

			// Contact view.
			$vars['view']	= 'contact';
			$vars['id']		= $id;
		}
		elseif (isset($item->query['view']) && $item->query['view'] == 'category' && count($segments) == 2)
		{
			// Break up the category id into numeric and alias values.
			if (isset($segments[0]) && strpos($segments[0], ':')) {
				list($catid, $catalias) = explode(':', $segments[0], 2);
			}

			// Break up the contact id into numeric and alias values.
			if (isset($segments[1]) && strpos($segments[1], ':')) {
				list($id, $alias) = explode(':', $segments[1], 2);
			}

			// Contact view.
			$vars['view']	= 'contact';
			$vars['id']		= $id;
			$vars['catid']	= $catid;
		}
		elseif (isset($item->query['view']) && $item->query['view'] == 'category' && isset($segments[0]))
		{
			// Break up the category id into numeric and alias values.
			if (isset($segments[0]) && strpos($segments[0], ':')) {
				list($catid, $catalias) = explode(':', $segments[0], 2);
			}

			// Category view.
			$vars['view']	= 'category';
			$vars['catid']	= $catid;
		}
		elseif (isset($item->query['view']) && $item->query['view'] == 'categories' && count($segments) == 2)
		{
			// Break up the category id into numeric and alias values.
			if (isset($segments[0]) && strpos($segments[0], ':')) {
				list($catid, $catalias) = explode(':', $segments[0], 2);
			}

			// Break up the contact id into numeric and alias values.
			if (isset($segments[1]) && strpos($segments[1], ':')) {
				list($id, $alias) = explode(':', $segments[1], 2);
			}

			// Contact view.
			$vars['view']	= 'contact';
			$vars['id']		= $id;
			$vars['catid']	= $catid;
		}
		elseif (isset($item->query['view']) && $item->query['view'] == 'categories' && isset($segments[0]))
		{
			// Break up the category id into numeric and alias values.
			if (isset($segments[0]) && strpos($segments[0], ':')) {
				list($catid, $catalias) = explode(':', $segments[0], 2);
			}

			// Category view.
			$vars['view']	= 'category';
			$vars['catid']	= $catid;
		}
		elseif (isset($item->query['view']) && $item->query['view'] == 'categories')
		{
			// Categories view.
			$vars['view']	= 'categories';
		}
	}
	else
	{
		// Count route segments
		$count = count($segments);

		// Check if there are any route segments to handle.
		if ($count)
		{
			if ($count == 2)
			{
				// We are viewing a contact.
				$vars['view']	= 'contact';
				$vars['catid']	= $segments[$count-2];
				$vars['id']		= $segments[$count-1];
			}
			elseif($count == 1)
			{
				// We are viewing a category.
				$vars['view']	= 'category';
				$vars['catid']	= $segments[$count-1];
			}
			else
			{
				// We are viewing categories.
				$vars['view']	= 'categories';
			}
		}
	}
	return $vars;
}